#!/usr/bin/env python3
"""
Qalcuity ERP — Raspberry Pi IoT Agent
Kirim data sensor ke ERP secara otomatis

Install dependencies:
    pip install requests RPi.GPIO Adafruit-DHT schedule

Jalankan sebagai service:
    sudo nano /etc/systemd/system/qalcuity-iot.service
    [Unit]
    Description=Qalcuity IoT Agent
    After=network.target

    [Service]
    ExecStart=/usr/bin/python3 /home/pi/qalcuity_iot.py
    Restart=always
    User=pi

    [Install]
    WantedBy=multi-user.target

    sudo systemctl enable qalcuity-iot
    sudo systemctl start qalcuity-iot
"""

import requests
import schedule
import time
import logging
from datetime import datetime, timezone

# ── Konfigurasi ───────────────────────────────────────────────
DEVICE_TOKEN  = "GANTI_DENGAN_TOKEN_DARI_ERP"
ERP_BASE_URL  = "https://erp.domain.com"
SEND_INTERVAL = 30  # detik

logging.basicConfig(level=logging.INFO, format='%(asctime)s %(levelname)s %(message)s')
log = logging.getLogger(__name__)

# ── HTTP Helper ───────────────────────────────────────────────
def post_to_erp(endpoint: str, payload: dict) -> dict:
    url = f"{ERP_BASE_URL}/api/webhooks/iot/{endpoint}"
    try:
        resp = requests.post(url,
            headers={
                "X-Device-Token": DEVICE_TOKEN,
                "Content-Type": "application/json",
            },
            json=payload,
            timeout=10
        )
        return resp.json()
    except Exception as e:
        log.error(f"Gagal kirim ke ERP: {e}")
        return {}

# ── Baca Sensor ───────────────────────────────────────────────
def read_sensors() -> list:
    """
    Ganti fungsi ini sesuai sensor yang dipakai.
    Contoh: DHT22, DS18B20, pH meter, load cell, dll.
    """
    sensors = []

    # Contoh: DHT22 via Adafruit library
    try:
        import Adafruit_DHT
        humidity, temperature = Adafruit_DHT.read_retry(Adafruit_DHT.DHT22, 4)
        if temperature is not None:
            sensors.append({"type": "temperature", "value": round(temperature, 2), "unit": "C"})
        if humidity is not None:
            sensors.append({"type": "humidity", "value": round(humidity, 2), "unit": "%"})
    except ImportError:
        # Fallback: simulasi data jika library tidak ada
        import random
        sensors.append({"type": "temperature", "value": round(25 + random.uniform(-2, 2), 2), "unit": "C"})
        sensors.append({"type": "humidity",    "value": round(70 + random.uniform(-5, 5), 2), "unit": "%"})

    return sensors

# ── Kirim Telemetry ───────────────────────────────────────────
def send_telemetry():
    sensors = read_sensors()
    if not sensors:
        log.warning("Tidak ada data sensor")
        return

    result = post_to_erp("telemetry", {
        "sensors": sensors,
        "recorded_at": datetime.now(timezone.utc).isoformat(),
        "firmware": "rpi-agent-v1.0.0",
    })

    if result.get("success"):
        log.info(f"Terkirim {len(sensors)} sensor: {[s['type'] for s in sensors]}")
    else:
        log.warning(f"ERP response: {result}")

# ── Heartbeat ─────────────────────────────────────────────────
def send_heartbeat():
    result = post_to_erp("heartbeat", {})
    if result.get("success"):
        log.debug("Heartbeat OK")

# ── Main ──────────────────────────────────────────────────────
if __name__ == "__main__":
    log.info("Qalcuity IoT Agent started")

    schedule.every(SEND_INTERVAL).seconds.do(send_telemetry)
    schedule.every(5).minutes.do(send_heartbeat)

    # Kirim langsung saat start
    send_telemetry()
    send_heartbeat()

    while True:
        schedule.run_pending()
        time.sleep(1)
