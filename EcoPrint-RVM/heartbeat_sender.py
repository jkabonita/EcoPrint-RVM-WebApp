# --- heartbeat_sender.py ---
#!/usr/bin/env python3
import time
import json
import requests
import socket
import os

STATUS_FILE = "/home/ecoprint/EcoPrint/ecoprint_status.json"
SERVER_URL = "https://ecoprint.cspc.edu.ph/api/machine/heartbeat/EP-RVM-01"
MACHINE_ID = "ECPRNT-RPI-01"

def read_status():
    """Read current machine status from JSON file."""
    try:
        with open(STATUS_FILE) as f:
            return json.load(f).get("status", "down")
    except Exception:
        return "down"

def get_cpu_temp():
    """Read CPU temperature (in °C) from Raspberry Pi thermal sensor."""
    try:
        with open("/sys/class/thermal/thermal_zone0/temp") as f:
            temp_str = f.read().strip()
            return round(int(temp_str) / 1000.0, 1)
    except Exception:
        return None

def get_ip_address():
    """Return local IP address of the machine."""
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))  # doesn't actually send data
        ip = s.getsockname()[0]
        s.close()
        return ip
    except Exception:
        return None

def send_heartbeat(status, cpu_temp, ip_address):
    try:
        payload = {
            "status": status,
            "cpu_temp": cpu_temp,
            "ip_address": ip_address
        }
        r = requests.post(SERVER_URL, json=payload, timeout=10)
        if r.status_code == 200:
            print(f"[Heartbeat] Sent successfully → {payload}")
        else:
            print(f"[Heartbeat] Failed ({r.status_code}): {r.text}")
    except Exception as e:
        print(f"[Heartbeat] Error sending heartbeat: {e}")

def main():
    while True:
        status = read_status()
        cpu_temp = get_cpu_temp()
        ip_address = get_ip_address()
        send_heartbeat(status, cpu_temp, ip_address)
        time.sleep(5)  # send every 30 seconds

if __name__ == "__main__":
    main()