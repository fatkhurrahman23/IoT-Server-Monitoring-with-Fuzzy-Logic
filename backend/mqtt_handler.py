"""
MQTT Client Handler.
Subscribe untuk menerima data telemetri dari Node-RED,
publish hasil kalkulasi AC target setelah pemrosesan Fuzzy Logic.
"""

import json
import time
import paho.mqtt.client as mqtt
from config import MQTT_BROKER, MQTT_PORT, MQTT_TOPIC_TELEMETRY, MQTT_TOPIC_AC_CONTROL


class MqttHandler:
    def __init__(self, on_message_callback):
        self.client = mqtt.Client(client_id="python_fuzzy_engine")
        self.client.on_connect = self._on_connect
        self.client.on_message = on_message_callback
        self.broker = MQTT_BROKER
        self.port = MQTT_PORT

    def _on_connect(self, client, userdata, flags, rc):
        if rc == 0:
            print(f"[MQTT] Connected to broker at {self.broker}:{self.port}")
            client.subscribe(MQTT_TOPIC_TELEMETRY)
            print(f"[MQTT] Subscribed to topic: {MQTT_TOPIC_TELEMETRY}")
        else:
            print(f"[MQTT] Connection failed with code: {rc}")

    def connect(self):
        try:
            self.client.connect(self.broker, self.port, keepalive=60)
        except Exception as e:
            print(f"[MQTT] Error connecting to broker: {e}")
            raise

    def publish_ac_control(self, ac_temp: float, temp: float, cpu_load: float, status: str = "", alert: bool = False):
        payload = {
            "ac_target": ac_temp,
            "temp": temp,
            "cpu_load": cpu_load,
            "status": status,
            "alert": alert,
            "timestamp": time.strftime("%Y-%m-%d %H:%M:%S")
        }
        self.client.publish(MQTT_TOPIC_AC_CONTROL, json.dumps(payload))
        print(f"[MQTT] Published to {MQTT_TOPIC_AC_CONTROL}: {payload}")

    def loop_forever(self):
        self.client.loop_forever()

    def start(self):
        self.client.loop_start()
