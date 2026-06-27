"""
Entry point untuk Python AI Engine.
Menjalankan MQTT loop, menerima telemetri dari Node-RED,
mengeksekusi Fuzzy Sugeno, mempublish hasil kembali ke MQTT
dan mengirim data ke Laravel API (untuk Reverb broadcast).
"""

import json
import sys
from fuzzy_sugeno import compute_ac_target
from mqtt_handler import MqttHandler
from api_client import send_to_laravel


def on_telemetry(client, userdata, msg):
    """
    Callback saat menerima data dari topik server/telemetry.
    Parse JSON, jalankan Fuzzy Logic, publish & kirim ke Laravel.
    """
    try:
        data = json.loads(msg.payload.decode("utf-8"))
        temp = float(data.get("temp", 25.0))
        cpu_load = float(data.get("cpu_load", 50.0))

        print(f"\n[INPUT] Temp: {temp}°C | CPU Load: {cpu_load}%")

        ac_target = compute_ac_target(temp, cpu_load)
        print(f"[FUZZY] Calculated AC Target: {ac_target}°C")

        handler.publish_ac_control(ac_target, temp, cpu_load)
        send_to_laravel(temp, cpu_load, ac_target)

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
