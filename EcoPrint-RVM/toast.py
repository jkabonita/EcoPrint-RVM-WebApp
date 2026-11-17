import tkinter as tk
from PIL import Image, ImageTk
import threading
from queue import Queue, Empty
import os
import traceback
import qrcode  # <-- ADD THIS


class ToastPop:
    """Extended Toast class with QR toast support."""

    def __init__(self):
        self._queue = Queue()
        self._polling_started = False
        self._poll_lock = threading.Lock()
        self._active_toasts = []

    def enqueue(self, root, is_accepted, duration_ms=1500):
        """Thread-safe enqueue for normal toasts."""
        try:
            self._queue.put_nowait(("normal", (is_accepted, duration_ms)))
        except Exception as e:
            print(f"[Toast] Failed to enqueue toast: {e}")

    def enqueue_qr_toast(self, root, url, duration_ms=5000):
        """Thread-safe enqueue for QR toasts."""
        try:
            self._queue.put_nowait(("qr", (url, duration_ms)))
        except Exception as e:
            print(f"[Toast] Failed to enqueue QR toast: {e}")

    def start_poller(self, root, interval_ms=100):
        """Start queue polling."""
        with self._poll_lock:
            if self._polling_started:
                return
            self._polling_started = True

        def _poll():
            try:
                while True:
                    try:
                        toast_type, args = self._queue.get_nowait()
                    except Empty:
                        break

                    try:
                        if toast_type == "normal":
                            is_accepted, duration_ms = args
                            self._show_toast_mainthread(root, is_accepted, duration_ms)
                        elif toast_type == "qr":
                            url, duration_ms = args
                            self._show_qr_toast_mainthread(root, url, duration_ms)
                    except Exception as e:
                        print(f"[Toast] Error creating toast: {e}")
                        traceback.print_exc()
            finally:
                try:
                    root.after(interval_ms, _poll)
                except Exception:
                    pass

        root.after(interval_ms, _poll)

    def _show_toast_mainthread(self, root, is_accepted, duration_ms=1500):
        """Show standard toast message."""
        font_size = 30
        ICON_SIZE = 120
        WINDOW_WIDTH = 600
        WINDOW_HEIGHT = 200
        toast = None

        try:
            if is_accepted == 1:
                icon = "yes_bottle.png"
                message = "Bottle Accepted!"
            elif is_accepted == 0:
                icon = "no_bottle.png"
                message = "Bottle Rejected!"
            else:
                icon = "image_3.png"
                message = "Thank you for Recycling!"
                font_size = 20

            toast = tk.Toplevel(root)
            toast.overrideredirect(True)
            toast.attributes('-topmost', True)
            toast.transient(root)
            toast.lift()

            tk_image = None
            icon_path = f"/home/ecoprint/EcoPrint/assets/frame0/{icon}"
            if os.path.exists(icon_path):
                pil_image = Image.open(icon_path)
                pil_image = pil_image.resize((ICON_SIZE, ICON_SIZE), Image.Resampling.LANCZOS)
                tk_image = ImageTk.PhotoImage(pil_image)

            content_frame = tk.Frame(toast, bg="white")
            content_frame.pack(expand=True, fill="both", padx=10, pady=10)

            if tk_image:
                icon_label = tk.Label(content_frame, image=tk_image, bg="white")
                icon_label.image = tk_image
                icon_label.pack(side=tk.LEFT, padx=5)

            label = tk.Label(
                content_frame,
                text=message,
                bg="white",
                fg="black",
                font=("Arial", font_size, "bold"),
                justify=tk.CENTER,
                wraplength=WINDOW_WIDTH - ICON_SIZE,
            )
            label.pack(side=tk.LEFT, padx=10, fill=tk.Y)

            toast.update_idletasks()
            screen_width = toast.winfo_screenwidth()
            screen_height = toast.winfo_screenheight()
            x = int((screen_width / 2) - (WINDOW_WIDTH / 2))
            y = int((screen_height / 2) - (WINDOW_HEIGHT / 2) - 50)
            toast.geometry(f"{WINDOW_WIDTH}x{WINDOW_HEIGHT}+{x}+{y}")
            toast.attributes("-alpha", 0.9)

            self._active_toasts.append(toast)

            toast.after(duration_ms, lambda: self._safe_destroy(toast))
        except Exception as e:
            print(f"[Toast] Unexpected error: {e}")
            traceback.print_exc()
            if toast:
                toast.destroy()

    def _show_qr_toast_mainthread(self, root, url, duration_ms=5000):
        """Show a larger toast containing a QR code generated from a URL."""
        toast = None
        try:
            # Toast dimensions
            WINDOW_WIDTH = 500
            WINDOW_HEIGHT = 500

            # Generate QR code
            qr_img = qrcode.make(url)
            qr_img = qr_img.resize((400, 400), Image.Resampling.LANCZOS)
            qr_photo = ImageTk.PhotoImage(qr_img)

            # Create the toast window
            toast = tk.Toplevel(root)
            toast.overrideredirect(True)
            toast.attributes('-topmost', True)
            toast.transient(root)
            toast.lift()

            content_frame = tk.Frame(toast, bg="white")
            content_frame.pack(expand=True, fill="both", padx=20, pady=20)

            # Label text above QR
            label = tk.Label(
                content_frame,
                text="User not found\nScan this QR Code to register:",
                bg="white",
                fg="black",
                font=("Arial", 20, "bold"),
            )
            label.pack(pady=(0, 10))

            # QR Image display
            qr_label = tk.Label(content_frame, image=qr_photo, bg="white")
            qr_label.image = qr_photo
            qr_label.pack(pady=10)

            # Position center screen
            toast.update_idletasks()
            screen_width = toast.winfo_screenwidth()
            screen_height = toast.winfo_screenheight()
            x = int((screen_width / 2) - (WINDOW_WIDTH / 2))
            y = int((screen_height / 2) - (WINDOW_HEIGHT / 2))
            toast.geometry(f"{WINDOW_WIDTH}x{WINDOW_HEIGHT}+{x}+{y}")

            toast.attributes("-alpha", 0.95)

            self._active_toasts.append(toast)
            toast.after(duration_ms, lambda: self._safe_destroy(toast))

        except Exception as e:
            print(f"[Toast] QR Toast error: {e}")
            traceback.print_exc()
            if toast:
                toast.destroy()

    def _safe_destroy(self, toast):
        """Safely destroy toast window."""
        try:
            if toast and toast.winfo_exists():
                toast.destroy()
                if toast in self._active_toasts:
                    self._active_toasts.remove(toast)
        except Exception as e:
            print(f"[Toast] Destroy error: {e}")

    def show_toast_threadsafe(self, root, is_accepted, duration_ms=1500):
        """Legacy wrapper."""
        self.enqueue(root, is_accepted, duration_ms)