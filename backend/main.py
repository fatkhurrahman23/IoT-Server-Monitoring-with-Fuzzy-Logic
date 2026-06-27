"""
Entry point untuk Python AI Engine.
Menjalankan MQTT loop, menerima telemetri dari Node-RED,
mengeksekusi Fuzzy Sugeno, mempublish hasil kembali ke MQTT
dan mengirim data ke Laravel API (untuk Reverb broadcast).

Enhancements:
  - Multi-server CPU load aggregation (MAX)
  - Hysteresis untuk mencegah AC cycling terlalu cepat
  - Alert threshold jika suhu > batas aman
  - AC Status indicator (idle / cooling / full)
"""

import json
import sys
from fuzzy_sugeno import compute_ac_target
from mqtt_handler import MqttHandler
from api_client import send_to_laravel
from config import HYSTERESIS_THRESHOLD, ALERT_TEMP_THRESHOLD


last_ac_target = None


def classify_ac_status(ac_target: float) -> str:
    """Klasifikasikan status pendinginan berdasarkan target suhu AC."""
    if ac_target >= 26:
        return "idle"
    elif ac_target >= 20:
        return "cooling"
    else:
        return "full"


def on_telemetry(client, userdata, msg):
    """
    Callback saat menerima data dari topik server/telemetry.
    Parse JSON, jalankan Fuzzy Logic, publish & kirim ke Laravel.
    """
    global last_ac_target

    try:
        data = json.loads(msg.payload.decode("utf-8"))
        temp = float(data.get("temp", 25.0))

        servers = data.get("servers", [data.get("cpu_load", 50.0)])
        cpu_load = max(servers)

        print(f"\n[INPUT] Temp: {temp}°C | Server Loads: {servers} | MAX CPU: {cpu_load}%")

        ac_target = compute_ac_target(temp, cpu_load)
        print(f"[FUZZY] Calculated AC Target: {ac_target}°C")

        if last_ac_target is not None and abs(ac_target - last_ac_target) < HYSTERESIS_THRESHOLD:
            print(f"[HYSTERESIS] AC change ({abs(ac_target - last_ac_target):.2f}°C) < threshold ({HYSTERESIS_THRESHOLD}°C). Skipping publish.")
            return

        last_ac_target = ac_target

        status = classify_ac_status(ac_target)
        alert = temp >= ALERT_TEMP_THRESHOLD

        if alert:
            print(f"[ALERT] ⚠️ Room temperature {temp}°C exceeds safe threshold ({ALERT_TEMP_THRESHOLD}°C)!")

        handler.publish_ac_control(ac_target, temp, cpu_load, status, alert)
        send_to_laravel(temp, cpu_load, ac_target, servers, status, alert)

    except json.JSONDecodeError as e:
        print(f"[ERROR] Invalid JSON: {e}")
    except Exception as e:
        print(f"[ERROR] {e}")


if __name__ == "__main__":
    handler = MqttHandler(on_telemetry)
    handler.connect()
    print("=== Python Fuzzy Logic Engine Started ===")
    print("Waiting for telemetry data from Node-RED...")
    try:
        handler.loop_forever()
    except KeyboardInterrupt:
        print("\n[SHUTDOWN] Stopping Fuzzy Engine...")
        sys.exit(0)
