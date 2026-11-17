#!/usr/bin/env python3
"""
EcoPrint Reverse Vending Machine (YOLOv8 PyTorch - Optimized)
- Real-time mode: Continuous detection in background, instant result fetch
- Single capture mode: Fast single-shot inference when bottle detected
- Toggle between modes with DETECTION_MODE variable
"""

# =========================================================================
# === Imports =============================================================
# =========================================================================
import os
import sys
import time
import math
import json
import requests
import serial
import traceback
import serial.tools.list_ports
import re
import socket
import ssl
import threading
from queue import Queue, Empty
from gui import GUIDisplay
from toast import ToastPop
from requests.exceptions import SSLError, ConnectionError, Timeout, RequestException

from RPi import GPIO
from smartcard.System import readers
from smartcard.util import toHexString

try:
    from RPLCD.i2c import CharLCD
    LCD_AVAILABLE = True
except Exception:
    LCD_AVAILABLE = False

try:
    import cv2
    import numpy as np
    OPENCV_AVAILABLE = True
except Exception:
    OPENCV_AVAILABLE = False

try:
    from ultralytics import YOLO
    YOLO_AVAILABLE = True
except Exception:
    YOLO_AVAILABLE = False
    print("[ERROR] Ultralytics not installed. Install with: pip install ultralytics")


# =========================================================================
# === Configuration ========================================================
# =========================================================================

# ---- Detection Mode -----------------------------------------------------
# "realtime" - Continuous background detection, instant fetch (RECOMMENDED)
# "single"   - Single capture on-demand (slower but uses less resources)
DETECTION_MODE = "single"

# ---- API ----------------------------------------------------------------
API_URL = "https://ecoprint.cspc.edu.ph/api/machine/"
STATUS_FILE = "/home/ecoprint/EcoPrint/ecoprint_status.json"


# ---- Paths --------------------------------------------------------------
YOLO_MODEL_PATH = "/home/ecoprint/EcoPrint/best_models/ecoprint640.pt"
CLASSES_FILE = "/home/ecoprint/EcoPrint/best_models/classes.txt"

# ---- Error Log --------------------------------------------------------------
ERROR_PATH = "/home/ecoprint/EcoPrint/logs/error.log"

# ---- Feature Flags ------------------------------------------------------
USE_LCD = True
USE_CAMERA = True
DEBUG_PRINTS = True
DEBUG_SNAPSHOTS = False
SNAPSHOT_DIR = "/home/ecoprint/EcoPrint/captures"

# ---- Detection Settings -------------------------------------------------
DETECTION_CONFIDENCE = 0.3  # Confidence threshold
REALTIME_FPS_TARGET = 10     # FPS for realtime mode (lower = less CPU)
REALTIME_RESULT_TIMEOUT = 2.0  # Max seconds to wait for realtime result

# ---- Camera Settings ----------------------------------------------------
CAMERA_WARMUP_FRAMES = 5     # Frames to flush before single capture
CAMERA_SETTLE_DELAY = 0.3    # Delay between flushes

# ---- LCD (I2C) ----------------------------------------------------------
LCD_ADDRESS = 0x27
LCD_COLS = 20
LCD_ROWS = 4

# ---- GPIO Pins (BCM numbers) --------------------------------------------
SERVO1_PIN = 13
SERVO2_PIN = 19
PROX1_TRIG = 4
PROX1_ECHO = 27
PROX2_TRIG = 22
PROX2_ECHO = 21
PROX3_TRIG = 17
PROX3_ECHO = 16
RFID_SCK = 11
RFID_MOSI = 10
RFID_MISO = 9
RFID_CS = 8
RFID_RST = 25
PUSH_BUTTON_PIN = 23
LED_PIN = 26

# ---- Servo Angles -------------------------------------------------------
SERVO1_CLOSE = 60
SERVO1_OPEN = 0
SERVO2_PET = 25
SERVO2_TRASH = 115
SERVO2_DEFAULT = 65

# ---- Gate timing --------------------------------------------------------
INTAKE_GATE_CLOSE_DELAY = 1.5

# ---- Timeouts -----------------------------------------------------------
RFID_TIMEOUT_SECONDS = 10
BOTTLE_INSERT_TIMEOUT_SECONDS = 10

# ---- Bottle Size Thresholds (cm) ----------------------------------------
BOTTLE_SIZE_SMALL = 20.00
BOTTLE_SIZE_MEDIUM = 17.00
BOTTLE_SIZE_LARGE = 12.00

# ---- Button Settings ----------------------------------------------------
BUTTON_PRESSED_LEVEL = GPIO.HIGH
BUTTON_DEBOUNCE_SECONDS = 0.01
BUTTON_HOLD_SECONDS = 0.01

NEW_BALANCE = 0.00
EARNED_CREDITS = 0.00

# BCM pins validation
VALID_BCM_PINS = {
    2, 3, 4, 17, 27, 22, 10, 9, 11, 0, 5, 6, 13, 19, 26,
    14, 15, 18, 23, 24, 25, 8, 7, 1, 12, 16, 20, 21
}

def ensure_valid_pin(pin: int, name: str):
    if not isinstance(pin, int) or pin not in VALID_BCM_PINS:
        raise ValueError(f"{name}={pin} is not a valid BCM GPIO on Raspberry Pi 4.")


# =========================================================================
# === YOLOv8 Detector (Realtime + Single Capture) =========================
# =========================================================================

class YOLODetector:
    """
    YOLOv8 PyTorch detector with two modes:
    - Realtime: Background thread continuously processes frames
    - Single: On-demand inference when needed
    """
    def __init__(self, model_path, classes_file, conf_threshold=0.65, mode="realtime"):
        self.model_path = model_path
        self.classes_file = classes_file
        self.conf_threshold = conf_threshold
        self.mode = mode
        
        self.model = None
        self.class_names = []
        self.last_inference_time = 0
        
        # Realtime mode variables
        self.realtime_running = False
        self.realtime_thread = None
        self.realtime_result = None
        self.realtime_lock = threading.Lock()
        self.camera_ref = None
        
        # Initialize
        if not self._load_model():
            raise RuntimeError("Failed to load YOLO model")
        self._load_classes()
        
        print(f"[YOLO] Mode: {self.mode.upper()}")
    
    def _load_model(self):
        """Load YOLOv8 PyTorch model"""
        if not YOLO_AVAILABLE:
            print("[YOLO] Ultralytics not available")
            return False
        
        try:
            if not os.path.exists(self.model_path):
                print(f"[YOLO] Model not found: {self.model_path}")
                return False
            
            print(f"[YOLO] Loading model: {self.model_path}")
            self.model = YOLO(self.model_path)
            
            # Warm-up inference
            dummy_img = np.zeros((640, 640, 3), dtype=np.uint8)
            _ = self.model(dummy_img, verbose=False, conf=self.conf_threshold)
            
            print(f"[YOLO] ✓ Model loaded and warmed up")
            return True
            
        except Exception as e:
            print(f"[YOLO] Failed to load model: {e}")
            traceback.print_exc()
            return False
    
    def _load_classes(self):
        """Load class names"""
        try:
            if os.path.exists(self.classes_file):
                with open(self.classes_file, 'r') as f:
                    self.class_names = [line.strip() for line in f.readlines()]
                print(f"[YOLO] ✓ Loaded {len(self.class_names)} classes: {self.class_names}")
            else:
                # Use model's built-in class names
                if self.model and hasattr(self.model, 'names'):
                    self.class_names = list(self.model.names.values())
                    print(f"[YOLO] ✓ Using model classes: {self.class_names}")
                else:
                    self.class_names = ["PET Bottle", "plastic_bottle", "bottle"]
                    print(f"[YOLO] Using fallback classes: {self.class_names}")
        except Exception as e:
            print(f"[YOLO] Error loading classes: {e}")
            self.class_names = ["PET Bottle"]
    
    def detect_single(self, frame):
        """
        Single capture detection - run inference on one frame
        Returns: (is_pet_bottle, detections, inference_time_ms)
        """
        start_time = time.time()
        
        if frame is None:
            return False, [], 0
        
        try:
            # Run inference
            results = self.model(frame, verbose=False, conf=self.conf_threshold)
            
            # Parse results
            detections = []
            is_pet = False
            
            for result in results:
                for box in result.boxes:
                    cls_id = int(box.cls[0])
                    confidence = float(box.conf[0])
                    cls_name = result.names[cls_id] if cls_id in result.names else f"class_{cls_id}"
                    
                    # Get bbox in xyxy format
                    bbox_xyxy = box.xyxy[0].cpu().numpy()
                    
                    detections.append({
                        'class_id': cls_id,
                        'class_name': cls_name,
                        'confidence': confidence,
                        'bbox': bbox_xyxy.tolist()
                    })
                    
                    # Check if PET bottle
                    pet_class_names_lower = {name.lower().strip() for name in self.class_names}
                    if cls_name.lower().strip() in pet_class_names_lower:
                        is_pet = True
            
            inference_time_ms = (time.time() - start_time) * 1000
            self.last_inference_time = inference_time_ms
            
            if DEBUG_PRINTS:
                print(f"[YOLO Single] Inference: {inference_time_ms:.1f}ms, PET={is_pet}, Detections={len(detections)}")
                for det in detections:
                    print(f"  - {det['class_name']}: {det['confidence']:.2f}")
            
            return is_pet, detections, inference_time_ms
            
        except Exception as e:
            if DEBUG_PRINTS:
                print(f"[YOLO] Inference error: {e}")
                traceback.print_exc()
            return False, [], 0
    
    def start_realtime(self, camera):
        """Start realtime detection thread"""
        if self.mode != "realtime":
            print("[YOLO] Not in realtime mode, skipping thread start")
            return
        
        if self.realtime_running:
            print("[YOLO] Realtime already running")
            return
        
        self.camera_ref = camera
        self.realtime_running = True
        self.realtime_thread = threading.Thread(target=self._realtime_loop, daemon=True, name="YOLO-Realtime")
        self.realtime_thread.start()
        print("[YOLO] ✓ Realtime detection started")
    
    def stop_realtime(self):
        """Stop realtime detection thread"""
        if self.realtime_running:
            self.realtime_running = False
            if self.realtime_thread:
                self.realtime_thread.join(timeout=2.0)
            print("[YOLO] Realtime detection stopped")
    
    def _realtime_loop(self):
        """Background thread for continuous detection"""
        print("[YOLO Realtime] Thread started")
        
        frame_delay = 1.0 / REALTIME_FPS_TARGET
        
        while self.realtime_running:
            try:
                if not self.camera_ref or not self.camera_ref.enabled:
                    time.sleep(0.1)
                    continue
                
                # Capture frame
                frame = self.camera_ref.snap()
                if frame is None:
                    time.sleep(0.05)
                    continue
                
                # Run detection
                is_pet, detections, inf_time = self.detect_single(frame)
                
                # Store result (thread-safe)
                with self.realtime_lock:
                    self.realtime_result = {
                        'is_pet': is_pet,
                        'detections': detections,
                        'inference_time': inf_time,
                        'timestamp': time.time()
                    }
                
                # Control FPS
                time.sleep(frame_delay)
                
            except Exception as e:
                if DEBUG_PRINTS:
                    print(f"[YOLO Realtime] Error: {e}")
                time.sleep(0.1)
        
        print("[YOLO Realtime] Thread stopped")
    
    def fetch_realtime_result(self, timeout=2.0):
        """
        Fetch the latest result from realtime detection
        Returns: (is_pet_bottle, detections, inference_time_ms) or (False, [], 0) if timeout
        """
        if self.mode != "realtime":
            print("[YOLO] Not in realtime mode")
            return False, [], 0
        
        start = time.time()
        
        while time.time() - start < timeout:
            with self.realtime_lock:
                if self.realtime_result:
                    result = self.realtime_result
                    age = time.time() - result['timestamp']
                    
                    if age < 1.0:  # Result is fresh (less than 1 second old)
                        if DEBUG_PRINTS:
                            print(f"[YOLO Realtime] Fetched result (age: {age:.2f}s)")
                        return result['is_pet'], result['detections'], result['inference_time']
            
            time.sleep(0.05)
        
        if DEBUG_PRINTS:
            print(f"[YOLO Realtime] Timeout waiting for result")
        return False, [], 0
    
    def detect(self, frame=None):
        """
        Universal detect method - routes to single or realtime based on mode
        Returns: (is_pet_bottle, detections, inference_time_ms)
        """
        if self.mode == "realtime":
            return self.fetch_realtime_result(timeout=REALTIME_RESULT_TIMEOUT)
        else:
            return self.detect_single(frame)


# =========================================================================
# === Display, Camera, RFID ===============================================
# =========================================================================

class Display:
    _last_update = 0
    _lock = threading.Lock()
    _min_interval = 0.3

    def __init__(self, use_lcd=True):
        self.gui = GUIDisplay()
        self.toast = ToastPop()
        self._cmd_queue = Queue()
        self._poller_started = False
        
        try:
            self.toast.start_poller(self.gui.window)
        except Exception as e:
            print(f"[Display] Toast poller failed: {e}")
        
        self._start_cmd_poller()

    def _start_cmd_poller(self):
        if self._poller_started:
            return
        self._poller_started = True
        
        def _poll():
            try:
                while True:
                    try:
                        cmd, args, kwargs = self._cmd_queue.get_nowait()
                        try:
                            cmd(*args, **kwargs)
                        except Exception as e:
                            print(f"[Display] Command '{cmd.__name__}' failed: {e}")
                    except Empty:
                        break
                    except Exception as e:
                        print(f"[Display] Queue processing error: {e}")
            finally:
                try:
                    self.gui.window.after(50, _poll)
                except Exception as e:
                    pass
        
        try:
            self.gui.window.after(50, _poll)
            print("[Display] Command poller started")
        except Exception as e:
            print(f"[Display] Failed to start command poller: {e}")

    def _queue_cmd(self, func, *args, **kwargs):
        try:
            self._cmd_queue.put_nowait((func, args, kwargs))
        except Exception as e:
            print(f"[Display] Failed to queue command: {e}")

    def update_user_info(self, name, current, step):
        self._queue_cmd(self.gui.update_user_info, name, current, step)

    def update_earned_new(self, earned="", new="", delay=0):
        with Display._lock:
            now = time.time()
            if now - Display._last_update < Display._min_interval:
                return
            Display._last_update = now
            self._queue_cmd(self.gui.update_earned_new, earned, new)
            if delay > 0:
                time.sleep(delay)
    
    def update_step(self, step=""):
        self._queue_cmd(self.gui.update_step, step)
    
    def update_timer(self, timer_text=""):
        self._queue_cmd(self.gui.update_timer, timer_text)

    def clear(self):
        self._queue_cmd(self.gui.await_user)
    
    def pop_toast(self, is_accepted, duration_ms=1500):
        try:
            self.toast.enqueue(self.gui.window, is_accepted, duration_ms)
        except Exception as e:
            print(f"[Toast] Failed to enqueue toast: {e}")

    def pop_qr_toast(self, url, duration_ms=5000):
        try:
            self.toast.enqueue_qr_toast(self.gui.window, url, duration_ms)
        except Exception as e:
            print(f"[Toast] Failed to enqueue QR toast: {e}")
    
    def show_fatal_error(self, message):
        self._queue_cmd(self.gui.show_fatal_error, message)


class Camera:
    def __init__(self, enabled=True, index=0):
        self.enabled = enabled and OPENCV_AVAILABLE
        self.cap = None
        if self.enabled:
            try:
                self.cap = cv2.VideoCapture(index)
                if not self.cap or not self.cap.isOpened():
                    print("[Camera] Could not open /dev/video0. Camera disabled.")
                    self.enabled = False
                    self.cap = None
                else:
                    # Set camera properties
                    self.cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
                    self.cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)
                    self.cap.set(cv2.CAP_PROP_FPS, 30)
                    self.cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)  # Minimize buffer for realtime
                    time.sleep(1)
            except Exception as e:
                print(f"[Camera] Init error: {e}. Camera disabled.")
                self.enabled = False
                self.cap = None

    def snap(self):
        """Quick frame grab (for realtime mode)"""
        if not self.enabled or self.cap is None:
            return None
        ok, frame = self.cap.read()
        return frame if ok else None

    def snap_still(self, flush=5, delay=0.1, save_path=None):
        """Stabilized capture (for single capture mode)"""
        if not self.enabled or self.cap is None:
            return None
        
        # Flush old frames
        for _ in range(max(0, int(flush))):
            self.cap.read()
            if delay > 0:
                time.sleep(delay)
        
        # Capture final frame
        ok, frame = self.cap.read()
        if not ok:
            return None
            
        if save_path:
            try:
                os.makedirs(os.path.dirname(save_path), exist_ok=True)
                cv2.imwrite(save_path, frame)
            except Exception as e:
                if DEBUG_PRINTS:
                    print(f"[Camera] Save snapshot failed: {e}")
        return frame

    def close(self):
        if self.cap:
            try:
                self.cap.release()
            except Exception:
                pass


GET_UID = [0xFF, 0xCA, 0x00, 0x00, 0x00]

class PCSCRFID:
    def __init__(self):
        self.reader = None
        self.connection = None
        self._connect_reader()

    @property
    def connected(self):
        return self.reader is not None

    def _connect_reader(self):
        try:
            r = readers()
            if not r:
                if DEBUG_PRINTS:
                    print("[RFID] No PC/SC reader found.")
                self.reader = None
                return
            self.reader = r[0]
            if DEBUG_PRINTS:
                print(f"[RFID] Using reader: {self.reader}")
        except Exception as e:
            print(f"[RFID] Reader connect error: {e}")
            self.reader = None

    def read_card(self, timeout=10):
        if not self.reader:
            self._connect_reader()
            if not self.reader:
                return None

        start = time.time()
        while time.time() - start < timeout:
            try:
                self.connection = self.reader.createConnection()
                self.connection.connect()
                data, sw1, sw2 = self.connection.transmit(GET_UID)
                if sw1 == 0x90 and sw2 == 0x00:
                    uid_hex = toHexString(data).replace(" ", "")
                    if DEBUG_PRINTS:
                        print(f"[RFID] UID: {uid_hex}")
                    self.connection.disconnect()
                    return uid_hex
                else:
                    if DEBUG_PRINTS:
                        print("[RFID] Failed to read UID")
            except Exception as e:
                if DEBUG_PRINTS:
                    print("")
            finally:
                # *** GUARANTEED CLEANUP ***
                if self.connection:
                    try:
                        self.connection.disconnect()
                    except Exception as disconnect_e:
                        if DEBUG_PRINTS:
                            print(f"[RFID] Error during disconnect cleanup: {disconnect_e}")

            time.sleep(0.5)
        return None

    def is_card_present(self):
        return False

    def close(self):
        try:
            if self.connection:
                self.connection.disconnect()
        except Exception:
            pass


# =========================================================================
# === Reverse Vending Machine (YOLOv8 PyTorch) ===========================
# =========================================================================

class ReverseVendingMachine:
    def __init__(self):
        # ---- Display ------------------------------------------------------
        self.display = Display(use_lcd=USE_LCD)

        # ---- GPIO Setup ---------------------------------------------------
        GPIO.setmode(GPIO.BCM)
        mode = GPIO.getmode()
        print(f"Current GPIO mode: {mode}")

        # ---- RFID ---------------------------------------------------------
        self.rfid = PCSCRFID()
        if not self.rfid.connected:
            self.fatal_error("EP-RVM-01", "RFID Error", "Reader not detected.")

        # ---- Camera -------------------------------------------------------
        self.camera = Camera(enabled=USE_CAMERA, index=0)
        if not self.camera.enabled:
            self.fatal_error("EP-RVM-01", "Camera Failure", "Camera not responding.")

        # ---- YOLOv8 Detector ----------------------------------------------
        print(f"[YOLO] Loading detector in {DETECTION_MODE.upper()} mode...")
        try:
            self.detector = YOLODetector(
                model_path=YOLO_MODEL_PATH,
                classes_file=CLASSES_FILE,
                conf_threshold=DETECTION_CONFIDENCE,
                mode=DETECTION_MODE
            )
            print(f"[YOLO] ✓ Detector ready")
        except Exception as e:
            self.fatal_error("EP-RVM-01", "Detector Error", f"Failed to load YOLO detector: {e}")

        # ---- State --------------------------------------------------------
        self.session_active = False
        self.user = None
        self.recycled_bottles = []
        self.bottle_types_data = {}
        self.earned_credits = 0.00
        self.new_balance = 0.00

        # ---- GPIO Setup ---------------------------------------------------
        self._setup_gpio()

    def _setup_gpio(self):
        for pin, name in [
            (SERVO1_PIN, "SERVO1_PIN"),
            (SERVO2_PIN, "SERVO2_PIN"),
            (PROX1_TRIG, "PROX1_TRIG"),
            (PROX1_ECHO, "PROX1_ECHO"),
            (PROX2_TRIG, "PROX2_TRIG"),
            (PROX2_ECHO, "PROX2_ECHO"),
            (PROX3_TRIG, "PROX3_TRIG"),
            (PROX3_ECHO, "PROX3_ECHO"),
            (PUSH_BUTTON_PIN, "PUSH_BUTTON_PIN"),
            (LED_PIN, "LED_PIN"),
        ]:
            ensure_valid_pin(pin, name)

        GPIO.setwarnings(False)

        GPIO.setup(SERVO1_PIN, GPIO.OUT)
        GPIO.setup(SERVO2_PIN, GPIO.OUT)
        self.servo1_pwm = GPIO.PWM(SERVO1_PIN, 50)
        self.servo2_pwm = GPIO.PWM(SERVO2_PIN, 50)
        self.servo1_pwm.start(0)
        self.servo2_pwm.start(0)

        GPIO.setup(PROX1_TRIG, GPIO.OUT)
        GPIO.setup(PROX1_ECHO, GPIO.IN)
        GPIO.setup(PROX2_TRIG, GPIO.OUT)
        GPIO.setup(PROX2_ECHO, GPIO.IN)
        GPIO.setup(PROX3_TRIG, GPIO.OUT)
        GPIO.setup(PROX3_ECHO, GPIO.IN)

        GPIO.setup(PUSH_BUTTON_PIN, GPIO.IN, pull_up_down=GPIO.PUD_UP)

        GPIO.setup(LED_PIN, GPIO.OUT)
        GPIO.output(LED_PIN, GPIO.LOW)

        if DEBUG_PRINTS:
            print("[GPIO] Setup complete (BCM mode)")
    
    def update_status(self, state):
        try:
            with open(STATUS_FILE, "w") as f:
                json.dump({"status": state}, f)
            print(f"[Status] Updated to '{state}'")
        except Exception as e:
            print(f"[Status] Failed to write status file: {e}")

    @staticmethod
    def _angle_to_duty(angle):
        angle = max(0, min(180, int(angle)))
        return angle / 18.0 + 2.5

    def _servo_go(self, pwm, angle, hold=2):
        duty = self._angle_to_duty(angle)
        pwm.ChangeDutyCycle(duty)
        time.sleep(hold)
        pwm.ChangeDutyCycle(0)

    def _distance_cm(self, trig, echo, timeout=0.12):
        try:
            GPIO.output(trig, False)
            time.sleep(0.0002)
            GPIO.output(trig, True)
            time.sleep(0.00001)
            GPIO.output(trig, False)

            start = time.time()
            while GPIO.input(echo) == 0:
                if time.time() - start > timeout:
                    if DEBUG_PRINTS:
                        print(f"[HC-SR04] Timeout waiting for echo HIGH on pin {echo}")
                    return None
            pulse_start = time.time()

            while GPIO.input(echo) == 1:
                if time.time() - pulse_start > timeout:
                    if DEBUG_PRINTS:
                        print(f"[HC-SR04] Timeout waiting for echo LOW on pin {echo}")
                    return None
            pulse_end = time.time()

            duration = pulse_end - pulse_start
            distance = (duration * 34300) / 2
            
            if distance < 2 or distance > 400:
                return None
                
            return round(distance, 2)
            
        except Exception as e:
            if DEBUG_PRINTS:
                print(f"[HC-SR04] Measurement error: {e}")
            return None
    
    def check_storage_full(self, machine_id):
        distance = self._distance_cm(PROX3_TRIG, PROX3_ECHO)
        if distance is None:
            return False
        FULL_THRESHOLD_CM = 13.0
        is_full = distance < FULL_THRESHOLD_CM
        if DEBUG_PRINTS:
            status = "FULL" if is_full else "NOT FULL"
            print(f"[Storage] Distance: {distance:.2f}cm - Status: {status}")
            if is_full:
                full_count = 0
                for i in range(3):
                    counter_distance = self._distance_cm(PROX3_TRIG, PROX3_ECHO)
                    is_full_count = counter_distance < FULL_THRESHOLD_CM
                    if is_full_count:
                        full_count += 1
                    time.sleep(0.2)
                if full_count == 3:
                    print("[Storage] Storage bin is full!")
                    if DETECTION_MODE == "realtime":
                        self.detector.stop_realtime()
                    GPIO.output(LED_PIN, GPIO.LOW)
                    self._servo_go(self.servo1_pwm, SERVO1_CLOSE)
                    self._servo_go(self.servo2_pwm, SERVO2_DEFAULT)
                    if self.camera:
                        self.camera.close()
                    if self.rfid:
                        self.rfid.close()
                    self.servo1_pwm.stop()
                    self.servo2_pwm.stop()
                    GPIO.cleanup()

                    url = f"{API_URL}report-full-storage/{machine_id}"
                    self.update_status("full")
                    response = requests.post(url, timeout=10)
                    if response.status_code == 200:
                        print("[Storage] Reported full storage to API.")
                    else:
                        print(f"[Storage] Failed to report full storage: {response.status_code}")
                    self.display.show_fatal_error("EcoPrint RVM is currently FULL.\nSorry for the inconvenience.")
                    self.running = False
                else:
                    is_full = False
        return is_full

    def run(self):
        self.display.update_step("Initializing...")
        self._servo_go(self.servo1_pwm, SERVO1_CLOSE)
        self._servo_go(self.servo2_pwm, SERVO2_DEFAULT)

        # Start realtime detection if in realtime mode
        if DETECTION_MODE == "realtime":
            self.detector.start_realtime(self.camera)
            print(f"[System] Ready - YOLO realtime detection running in background")
        else:
            print(f"[System] Ready - YOLO single capture mode")

        try:
            while True:
                if not self.session_active:
                    self._await_rfid()
                else:
                    self._session_loop()
        except KeyboardInterrupt:
            self._shutdown("Shutting down...")
        except Exception as e:
            self._shutdown(f"Fatal error: {e}")
            self.fatal_error("EP-RVM-01", "Initialization Error", f"Failed to initialize: {e}")
    
    def _await_rfid(self):
        if not self.rfid.connected:
            self.display.update_step("RFID Error")
            time.sleep(1)
            return

        self.update_status("running")

        # ---- CHECK STORAGE FULL -------------------------------------------
        self.check_storage_full("EP-RVM-01")

        self.display.update_step("Tap ID card...")

        end = time.time() + RFID_TIMEOUT_SECONDS
        uid = None
        while time.time() < end and not uid and not self.session_active:
            uid = self.rfid.read_card(timeout=1)
            if not uid:
                time.sleep(0.05)

        if uid:
            self.display.update_step("Fetching ID...")
            if DEBUG_PRINTS:
                print(f"[RFID] UID: {uid}")
            self._start_session(uid)
        else:
            time.sleep(0.1)

    def _start_session(self, rfid_tag):
        print(f"[MACHINE] Starting session for RFID: {rfid_tag}")
        self.display.update_step("Fetching data...")
        self.update_status("in_use")
        try:
            res = requests.get(f"{API_URL}session-init/{rfid_tag}", timeout=20)
            data = {}
            try:
                data = res.json()
            except Exception:
                pass

            if res.status_code == 200 and data.get("status") == "success":
                self.user = data.get("user", {})
                self.bottle_types_data = data.get("bottle_types", {})
                self.session_active = True
                self.recycled_bottles = []

                first = self.user.get("first_name", "User")
                bal = self.user.get("credit_balance", 0)
                self.new_balance = float(bal)
                self.earned_credits = 0.00

                self.display.update_user_info(first, bal, "Please Wait...")

                # LED strip on
                GPIO.output(LED_PIN, GPIO.HIGH)

                # Camera exposure adjustment with test capture
                self.display.update_step("Adjusting camera...")
                self._adjust_camera_lighting()

                # Open intake gate
                self._servo_go(self.servo1_pwm, SERVO1_OPEN)
            
            elif data.get("status") == "info":
                url = data.get("url")
                self.display.update_step("Scan the qr to register.")
                self.display.pop_qr_toast(url, duration_ms=10000)
                if DEBUG_PRINTS:
                    print(f"[API] session-init info: {url}")
                self.update_status("running")
                self.display.update_step("Tap your ID")
            else:
                msg = (data.get("message") if isinstance(data, dict) else None) or \
                      f"{res.status_code} {res.reason}"
                if res.status_code == 404:
                    self.display.update_step("User not found")
                else:
                    self.display.update_step("Network Error")
                if DEBUG_PRINTS:
                    print(f"[API] session-init failed: {msg}")
                self.update_status("running")
                self.display.update_step("Tap your ID")

        except requests.RequestException as e:
            print(f"[API] session-init network error: {e}")
            self.fatal_error("EP-RVM-01", "Network Error", str(e))
            self.display.update_step("Tap your ID")

    def _session_loop(self):
        first = self.user.get("first_name", "User")
        bal = self.user.get("credit_balance", 0)

        self.display.update_user_info(first, bal, "Insert a bottle")

        start = time.monotonic()
        btn_last = GPIO.input(PUSH_BUTTON_PIN)
        stable_since = time.monotonic()
        pressed_since = None
        last_remaining = None

        while time.monotonic() - start < BOTTLE_INSERT_TIMEOUT_SECONDS:
            now = time.monotonic()
            try:
                remaining = max(0, int(math.ceil(BOTTLE_INSERT_TIMEOUT_SECONDS - (now - start))))
                if last_remaining is None or remaining != last_remaining:
                    self.display.update_timer(f"{remaining}s")
                    last_remaining = remaining
            except Exception as e:
                if DEBUG_PRINTS:
                    print(f"[Display] update_timer failed: {e}")

            btn_now = GPIO.input(PUSH_BUTTON_PIN)

            if btn_now != btn_last:
                stable_since = now
                btn_last = btn_now
            else:
                if now - stable_since >= BUTTON_DEBOUNCE_SECONDS:
                    if btn_now == BUTTON_PRESSED_LEVEL:
                        if pressed_since is None:
                            pressed_since = now
                            if DEBUG_PRINTS:
                                print("[BTN] Debounced press -> starting hold window")
                        elif now - pressed_since >= BUTTON_HOLD_SECONDS:
                            print(f"[BTN] End session (held >= {BUTTON_HOLD_SECONDS:.2f}s).")
                            self._end_session()
                            return
                    else:
                        pressed_since = None

            time.sleep(0.05)

            # Bottle presence detection (Prox #2)
            d2 = self._distance_cm(PROX2_TRIG, PROX2_ECHO)
            if DEBUG_PRINTS:
                print(f"[HC-SR04 #2] Presence: {d2} cm")
            if d2 and d2 < 6.25:
                self.display.update_timer("")
                self.display.update_step("Detecting Bottle...")
                
                # Close intake gate with delay
                if INTAKE_GATE_CLOSE_DELAY > 0:
                    if DEBUG_PRINTS:
                        print(f"[GATE] Delay before close: {INTAKE_GATE_CLOSE_DELAY}s")
                    time.sleep(INTAKE_GATE_CLOSE_DELAY)
                self._servo_go(self.servo1_pwm, SERVO1_CLOSE)
                time.sleep(0.5)  # Brief settling time

                # Process bottle
                self._process_bottle()

                # Wait for bottle removal
                print("Waiting for bottle removal...")
                remove_start = time.time()
                while True:
                    d_check = self._distance_cm(PROX2_TRIG, PROX2_ECHO)
                    if d_check is None or d_check >= 10:
                        break
                    if time.time() - remove_start > 10:
                        break
                    time.sleep(0.1)

                # Reset timer
                start = time.monotonic()
                last_remaining = None

                # Re-open gate
                self._servo_go(self.servo1_pwm, SERVO1_OPEN)

        print("[Session] Timeout waiting for next bottle.")
        self._end_session()

    def _wiggle_servo_until_clear(self, servo_pwm, prox_trig=22, prox_echo=21, threshold_cm=5, 
                              wiggle_angles=[30, 70], wiggle_delay=0.5, max_attempts=10,
                              sensor_retries=3):
        if DEBUG_PRINTS:
            print(f"[WIGGLE] Starting wiggle sequence - threshold: {threshold_cm}cm")
        
        attempts = 0
        consecutive_clear_readings = 0
        required_clear_readings = 2
        
        while attempts < max_attempts:
            distance = None
            for retry in range(sensor_retries):
                distance = self._distance_cm(prox_trig, prox_echo)
                if distance is not None:
                    break
                if DEBUG_PRINTS and retry < sensor_retries - 1:
                    print(f"[WIGGLE] Sensor error, retry {retry + 1}/{sensor_retries}")
                time.sleep(0.05)
            
            if distance is None:
                if DEBUG_PRINTS:
                    print(f"[WIGGLE] Attempt {attempts + 1}: Sensor error - assuming blocked")
                consecutive_clear_readings = 0
                
            elif distance >= threshold_cm:
                consecutive_clear_readings += 1
                if DEBUG_PRINTS:
                    print(f"[WIGGLE] Attempt {attempts + 1}: Distance = {distance}cm (clear {consecutive_clear_readings}/{required_clear_readings})")
                
                if consecutive_clear_readings >= required_clear_readings:
                    if DEBUG_PRINTS:
                        print(f"[WIGGLE] ✓ Object cleared! Final distance: {distance}cm")
                    return True
                    
            else:
                if DEBUG_PRINTS:
                    print(f"[WIGGLE] Attempt {attempts + 1}: Distance = {distance}cm (blocked)")
                consecutive_clear_readings = 0
            
            for angle in wiggle_angles:
                self._servo_go(servo_pwm, angle, hold=wiggle_delay)
                time.sleep(0.1)
            
            attempts += 1
        
        if DEBUG_PRINTS:
            print(f"[WIGGLE] ✗ Max attempts ({max_attempts}) reached")
            self.fatal_error(
                machine_id="EP-RVM-01",
                error_type="Intake Chute Jam",
                error_detail="Servo could not clear object after multiple attempts."
            )
        return False

    def _process_bottle(self):
        """
        Process bottle with YOLOv8 PyTorch detection
        Uses realtime or single capture based on DETECTION_MODE
        """
        self.display.update_step("Analyzing...")
        
        if DETECTION_MODE == "realtime":
            # === REALTIME MODE - Fetch from background thread ===
            if DEBUG_PRINTS:
                print("[YOLO] Fetching result from realtime detection...")
            
            is_pet, detections, inference_time = self.detector.fetch_realtime_result(
                timeout=REALTIME_RESULT_TIMEOUT
            )
            
            if DEBUG_PRINTS:
                print(f"[YOLO Realtime] Result: PET={is_pet}, Detections={len(detections)}, Time={inference_time:.1f}ms")
        
        else:
            # === SINGLE CAPTURE MODE - Capture and analyze ===
            snapshot_path = None
            if DEBUG_SNAPSHOTS:
                ts = time.strftime("%Y%m%d_%H%M%S")
                snapshot_path = os.path.join(SNAPSHOT_DIR, f"bottle_{ts}.jpg")
            
            frame = self.camera.snap_still(
                flush=CAMERA_WARMUP_FRAMES,
                delay=CAMERA_SETTLE_DELAY,
                save_path=snapshot_path
            )
            
            if frame is None:
                self.display.update_step("Camera Error")
                if DEBUG_PRINTS:
                    print("[ERROR] Failed to capture frame")
                return
            
            is_pet, detections, inference_time = self.detector.detect_single(frame)
            
            if DEBUG_PRINTS:
                print(f"[YOLO Single] Inference: {inference_time:.1f}ms, PET={is_pet}")
        
        # === PROCESS RESULT (same for both modes) ===
        if is_pet:
            try:
                self.display.pop_toast(1)
            except Exception as e:
                print(f"[Toast] Error showing accepted toast: {e}")
            
            # Measure bottle size
            if DEBUG_PRINTS:
                print("Measuring bottle size...")
            d1 = self._distance_cm(PROX1_TRIG, PROX1_ECHO)
            if DEBUG_PRINTS:
                print(f"[HC-SR04 #1] Size measurement: {d1} cm")
            
            bottle_type_id = None
            if d1:
                if float(d1) <= float(BOTTLE_SIZE_LARGE):
                    bottle_type_id = 3  # Small
                elif float(d1) <= float(BOTTLE_SIZE_MEDIUM):
                    bottle_type_id = 2  # Medium
                elif float(d1) <= float(BOTTLE_SIZE_SMALL):
                    bottle_type_id = 1  # Large
                else:
                    bottle_type_id = 1
            else:
                bottle_type_id = 1  # Default if measurement fails
            
            if DEBUG_PRINTS:
                print(f"Bottle type determined: {bottle_type_id}")

            # Record bottle and calculate credits
            self.recycled_bottles.append(bottle_type_id)
            bottle_credit_value = 0.00

            for bottle in self.bottle_types_data:
                if int(bottle['bottle_type_id']) == int(bottle_type_id):
                    bottle_credit_value = bottle['credit_value']
                    if DEBUG_PRINTS:
                        print(f"Credit value: {bottle_credit_value}")
                    break

            self.earned_credits += float(bottle_credit_value)
            self.new_balance = float(self.user.get("credit_balance", 0)) + self.earned_credits
            
            # Sort to PET bin
            if DEBUG_PRINTS:
                print("Sorting to PET bin...")
            self._servo_go(self.servo2_pwm, SERVO2_PET, hold=2)
            self.display.update_earned_new(self.earned_credits, self.new_balance)
            
            # Check if stuck
            distance_after_sort = self._distance_cm(PROX2_TRIG, PROX2_ECHO)
            if distance_after_sort and distance_after_sort < 5:
                self.display.update_step("Clearing chute...")
                cleared = self._wiggle_servo_until_clear(
                    self.servo2_pwm, PROX2_TRIG, PROX2_ECHO, 
                    threshold_cm=5, wiggle_angles=[SERVO2_PET - 15, SERVO2_PET + 15], 
                    wiggle_delay=0.3, max_attempts=8
                )
                if not cleared:
                    self.display.update_step("Stuck: Manual removal")
            
            if DEBUG_PRINTS:
                print(f"[SORT] ✓ PET bottle accepted -> type {bottle_type_id}")
                    
        else:
            try:
                self.display.pop_toast(0)
            except Exception as e:
                print(f"[Toast] Error showing rejected toast: {e}")
            
            # Sort to trash
            if DEBUG_PRINTS:
                print("Sorting to trash bin...")
            self._servo_go(self.servo2_pwm, SERVO2_TRASH, hold=2)
            
            # Check if stuck
            distance_after_sort = self._distance_cm(PROX2_TRIG, PROX2_ECHO)
            if distance_after_sort and distance_after_sort < 5:
                self.display.update_step("Clearing chute...")
                cleared = self._wiggle_servo_until_clear(
                    self.servo2_pwm, PROX2_TRIG, PROX2_ECHO,
                    threshold_cm=5, wiggle_angles=[SERVO2_TRASH - 15, SERVO2_TRASH + 15],
                    wiggle_delay=0.3, max_attempts=8
                )
                if not cleared:
                    self.display.update_step("Stuck: Manual removal")
            
            if DEBUG_PRINTS:
                print("[SORT] ✗ Not PET bottle -> Rejected to trash")

        # Return sorting gate to default
        self._servo_go(self.servo2_pwm, SERVO2_DEFAULT)
        self.display.update_step("Insert next bottle")

    def _end_session(self):
        self._servo_go(self.servo1_pwm, SERVO1_CLOSE)

        if not self.recycled_bottles:
            self.display.update_step("No bottles recycled. Goodbye!")
            self.update_status("running")
            self._reset_session_state()
            return

        self.display.update_step("Updating account balance...")
        try:
            payload = {
                "user_id": self.user.get("user_id"),
                "bottles": self.recycled_bottles
            }
            
            headers = {'Content-Type': 'application/json'}
            try:
                test_response = requests.post(f"{API_URL}finalize-session", 
                                            json={"test": "csrf"}, 
                                            timeout=2)
                if test_response.status_code == 403 and 'csrf' in test_response.text.lower():
                    token_response = requests.get(API_URL, timeout=2)
                    if 'csrf_token' in token_response.cookies:
                        headers['X-CSRF-TOKEN'] = token_response.cookies['csrf_token']
                    elif 'csrf' in token_response.cookies:
                        headers['X-CSRF-TOKEN'] = token_response.cookies['csrf']
            except:
                pass
            
            res = requests.post(f"{API_URL}finalize-session", 
                              json=payload, 
                              headers=headers, 
                              timeout=6)
            res.raise_for_status()
            data = res.json()

            self.display.update_step("Session Complete!")
            self.display.pop_toast(2, duration_ms=2500)
            if DEBUG_PRINTS:
                print(f"[API] Earned: {self.earned_credits}, New Balance: {self.new_balance}")
                print(json.dumps(data, indent=2))
            
        except requests.RequestException as e:
            self.display.update_step("API Error")
            print(f"[API] finalizeSession error: {e}")
        except json.JSONDecodeError as e:
            self.display.update_step("Data Error")
            print(f"[API] JSON parse error: {e}")

        
        self.update_status("running")
        self.check_storage_full("EP-RVM-01")
        self._reset_session_state()

    def _reset_session_state(self):
        GPIO.output(LED_PIN, GPIO.LOW)

        self.session_active = False
        self.user = None
        self.recycled_bottles = []
        self.earned_credits = 0.00
        self.new_balance = 0.00

        try:
            self.display.update_timer("")
        except Exception:
            pass

        self.display.clear()
    
    def _adjust_camera_lighting(self):
        """
        Capture test image for camera exposure adjustment.
        Helps camera adapt to current lighting conditions.
        """
        try:
            if DEBUG_PRINTS:
                print("[Camera] Starting lighting adjustment...")
            
            if DETECTION_MODE == "realtime":
                # In realtime mode, just let the background thread run for a bit
                # The continuous processing will auto-adjust exposure
                time.sleep(1.0)  # Give camera time to adjust
                
                if DEBUG_PRINTS:
                    print("[Camera] Realtime mode - auto-adjusting via background thread")
            
            else:
                # In single capture mode, take a test shot
                ts = time.strftime("%Y%m%d_%H%M%S")
                test_path = None
                
                if DEBUG_SNAPSHOTS:
                    test_path = os.path.join(SNAPSHOT_DIR, f"test_exposure_{ts}.jpg")
                
                # Capture test frame with camera warmup
                test_frame = self.camera.snap_still(
                    flush=CAMERA_WARMUP_FRAMES,
                    delay=CAMERA_SETTLE_DELAY,
                    save_path=test_path
                )
                
                if test_frame is not None:
                    if DEBUG_PRINTS:
                        print(f"[Camera] Test capture successful (saved to {test_path})")
                    
                    # Optional: Run a quick detection to verify camera is working
                    # This also warms up the model for faster first detection
                    _, _, inf_time = self.detector.detect_single(test_frame)
                    if DEBUG_PRINTS:
                        print(f"[Camera] Test inference: {inf_time:.1f}ms")
                else:
                    if DEBUG_PRINTS:
                        print("[Camera] Test capture failed")
            
            self.display.update_step("Camera ready!")
            time.sleep(0.5)  # Brief pause to show message
            
        except Exception as e:
            if DEBUG_PRINTS:
                print(f"[Camera] Lighting adjustment error: {e}")
            # Continue anyway - not critical
            time.sleep(0.5)
    
    def fatal_error(self, machine_id, error_type, error_detail):
        try:
            print(f"[FATAL] {error_type}: {error_detail}")
            self.update_status("error")
            # Stop realtime detection if running
            if DETECTION_MODE == "realtime":
                self.detector.stop_realtime()

            image_path = None
            if self.camera.enabled and self.camera.cap:
                self.camera.cap.set(3, 1280)
                self.camera.cap.set(4, 720)
                ts = time.strftime("%Y%m%d_%H%M%S")
                image_path = os.path.join(SNAPSHOT_DIR, f"fatal_{ts}.jpg")
                frame = self.camera.snap_still(flush=5, delay=0.25, save_path=image_path)
                if frame is None:
                    image_path = None
                    print("[FATAL] Failed to capture image.")
            else:
                print("[FATAL] Camera not available, skipping snapshot.")

            log_entry = {
                "timestamp": time.strftime("%Y-%m-%d %H:%M:%S"),
                "machine_id": machine_id,
                "error_type": error_type,
                "error_detail": error_detail,
                "image_path": image_path if image_path else "N/A",
                "api_status": "PENDING" # Will be updated after API attempt
            }

            try:
                os.makedirs(os.path.dirname(self.ERROR_PATH), exist_ok=True)
                with open(self.ERROR_PATH, "a") as f:
                    # Write the initial log entry with PENDING status
                    f.write(json.dumps(log_entry) + "\n") 
                print(f"[FATAL] Local Log: ✓ Error saved to {self.ERROR_PATH}")
                # Store the original log message to update later if needed
                temp_log_entry = log_entry
            except Exception as log_e:
                print(f"[FATAL] Local Log: ✗ CRITICAL failure saving log: {log_e}")
                temp_log_entry = None # Disable updating if initial write failed

            try:
                url = f"{API_URL}report-error/{machine_id}"
                payload = {"error_type": error_type, "error_detail": error_detail}
                files = {}
                if image_path and os.path.exists(image_path):
                    files["error_image"] = open(image_path, "rb")
                response = requests.post(url, data=payload, files=files, timeout=10)
                if files:
                    files["error_image"].close()
                print(f"[FATAL] API Response: {response.status_code} {response.text}")
            except Exception as e:
                print(f"[FATAL] API report failed: {e}")

            GPIO.output(LED_PIN, GPIO.LOW)
            self._servo_go(self.servo1_pwm, SERVO1_CLOSE)
            self._servo_go(self.servo2_pwm, SERVO2_DEFAULT)
            if self.camera:
                self.camera.close()
            if self.rfid:
                self.rfid.close()
            self.servo1_pwm.stop()
            self.servo2_pwm.stop()
            GPIO.cleanup()

            self.display.show_fatal_error("EcoPrint RVM is currently experiencing an ERROR .\nSorry for the inconvenience.")

            print("[FATAL] Machine halted. Waiting for manual restart.")
            while True:
                time.sleep(1)
            
            self.running = False

        except Exception as e:
            print(f"[FATAL] Unhandled fatal error: {e}")
            sys.exit(1)

    def _shutdown(self, msg="Goodbye!"):
        print(f"[SYS] {msg}")
        self.display.update_step("Shutting down..Goodbye!")

        # Stop realtime detection if running
        if DETECTION_MODE == "realtime":
            try:
                self.detector.stop_realtime()
            except Exception:
                pass

        try:
            if self.camera:
                self.camera.close()
        except Exception:
            pass

        try:
            if self.rfid:
                self.rfid.close()
        except Exception:
            pass

        try:
            self.servo1_pwm.stop()
            self.servo2_pwm.stop()
        except Exception:
            pass

        try:
            GPIO.cleanup()
        except Exception:
            pass

        try:
            self.display.clear()
        except Exception:
            pass

        sys.exit(0)


# =========================================================================
# === Main ================================================================
# =========================================================================

if __name__ == "__main__":
    if os.geteuid() == 0:
        print("[WARN] You are running as root. Prefer running as your normal user.")
        print("       Add your user to gpio, i2c, spi, and video groups instead.")

    rvm = None
    try:
        print("\n[SYS] ===== STARTING ECOPRINT RVM (YOLOv8 PyTorch) =====\n")
        print(f"[SYS] Detection Mode: {DETECTION_MODE.upper()}")
        print("[SYS] Initializing ReverseVendingMachine...")
        rvm = ReverseVendingMachine()
        print("[SYS] ✓ RVM initialized successfully\n")
        
        def start_rvm_thread():
            print("[SYS] Starting RVM thread...")
            rvm_thread = threading.Thread(target=rvm.run, daemon=True, name="RVM-Thread")
            rvm_thread.start()
            print("[SYS] ✓ RVM thread started\n")
        
        rvm.display.gui.window.after(500, start_rvm_thread)
        
        print("[SYS] Starting GUI mainloop...")
        print("[SYS] (Press ESC in fullscreen to exit, or close button)\n")
        rvm.display.gui.run()
        
    except KeyboardInterrupt:
        print("\n[SYS] Keyboard interrupt received")
        try:
            rvm._shutdown("User interrupted")
        except:
            pass
        sys.exit(0)
        
    except Exception as e:
        print(f"\n{'='*60}")
        print(f"[SYS] FATAL ERROR DETAILS:")
        print(f"{'='*60}")
        print(f"Error Type: {type(e).__name__}")
        print(f"Error Message: {str(e)}")
        print(f"\nFull Traceback:")
        traceback.print_exc()
        print(f"{'='*60}")
        
        try:
            if 'rvm' in locals():
                rvm._shutdown(f"Fatal error: {e}")
                rvm.fatal_error("EP-RVM-01", type(e).__name__, str(e))
        except:
            print("[SYS] Could not perform graceful shutdown")
            pass
        
        sys.exit(1)