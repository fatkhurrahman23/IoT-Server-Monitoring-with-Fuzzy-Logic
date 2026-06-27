# MQTT Broker Configuration
MQTT_BROKER = "localhost"
MQTT_PORT = 1883
MQTT_TOPIC_TELEMETRY = "server/telemetry"
MQTT_TOPIC_AC_CONTROL = "server/ac_control"

# Laravel API Endpoint (for triggering Reverb broadcast)
LARAVEL_API_URL = "http://localhost:8000/api/telemetry/ingest"

# Fuzzy Logic Domain Ranges
TEMP_MIN = 15.0   # °C
TEMP_MAX = 40.0   # °C
CPU_MIN = 0.0     # %
CPU_MAX = 100.0   # %
AC_MIN = 16.0     # °C (max cooling)
AC_MAX = 28.0     # °C (min cooling / idle)

# Hysteresis threshold — AC target only changes if delta exceeds this value
HYSTERESIS_THRESHOLD = 0.5  # °C

# Alert threshold — trigger warning if room temp exceeds this value
ALERT_TEMP_THRESHOLD = 35.0  # °C

