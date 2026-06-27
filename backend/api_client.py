"""
HTTP client untuk mengirim data telemetri ke Laravel API.
Laravel kemudian mem-broadcast event via Reverb WebSocket ke dashboard.
"""

import json
import urllib.request
import urllib.error
from config import LARAVEL_API_URL


def send_to_laravel(temp: float, cpu_load: float, ac_target: float, servers: list = None):
    payload = json.dumps({
        "temp": temp,
        "cpu_load": cpu_load,
        "ac_target": ac_target,
        "servers": servers if servers else [cpu_load],
    }).encode("utf-8")

    req = urllib.request.Request(
        LARAVEL_API_URL,
        data=payload,
        headers={"Content-Type": "application/json"},
        method="POST"
    )

    try:
        with urllib.request.urlopen(req, timeout=3) as response:
            print(f"[API] Data sent to Laravel. Status: {response.status}")
    except urllib.error.URLError as e:
        print(f"[API] Failed to reach Laravel: {e}")
    except Exception as e:
        print(f"[API] Error: {e}")
