# Progress Tracking — Simulasi Pemantauan & Kendali Otomatis Suhu Ruang Server

## Log Progres

[x] 2026-06-26 - Inisiasi Repositori dan Penyusunan System Design - Selesai
[x] 2026-06-26 - Docker Compose + Konfigurasi Mosquitto - Selesai
[x] 2026-06-26 - Backend: Python Fuzzy Logic Sugeno (hardcoded) - Selesai
[x] 2026-06-26 - Backend: MQTT Client + API Client ke Laravel - Selesai
[x] 2026-06-26 - Node-RED: Flow Simulator + Panduan - Selesai
[x] 2026-06-26 - FE: Scaffold Laravel + Install Filament v5 + Reverb - Selesai
[x] 2026-06-26 - FE: Migrasi Database (telemetry_logs, ac_control_logs) - Selesai
[x] 2026-06-26 - FE: API Endpoint /api/telemetry/ingest - Selesai
[x] 2026-06-26 - FE: Dashboard Widgets (Stats + Chart) + Reverb Integration - Selesai

## Menunggu Eksekusi

[ ] TODO - Pengujian End-to-End (Node-RED → Python → Laravel Dashboard)
[ ] TODO - Docker: Pastikan Docker Desktop tersedia untuk MySQL + Mosquitto
[ ] TODO - npm install & build frontend JS untuk Reverb client

## Struktur Proyek

```
automatic-temperature/
├── SYSTEM-DESIGN.md          # Dokumen desain sistem
├── PROGRESS.md               # File progres ini
├── docker-compose.yml        # MySQL + Mosquitto via Docker
├── mosquitto/
│   └── mosquitto.conf        # Konfigurasi MQTT broker
├── backend/                   # Python AI Engine
│   ├── requirements.txt       # Dependensi (paho-mqtt)
│   ├── config.py              # Konfigurasi MQTT, API URL, range suhu
│   ├── fuzzy_sugeno.py        # FIS Sugeno (Fuzzifikasi, 9 Rules, Defuzzifikasi)
│   ├── mqtt_handler.py        # Subscribe/Publish MQTT
│   ├── api_client.py          # HTTP POST ke Laravel API
│   └── main.py                # Entry point daemon
├── nodered/                   # Node-RED Simulator
│   ├── flows.json             # Flow untuk import
│   └── README.md              # Panduan setup simulator
└── fe/                        # Laravel Dashboard
    ├── app/
    │   ├── Models/            # TelemetryLog, AcControlLog
    │   ├── Events/            # TelemetryUpdated (Reverb)
    │   ├── Http/Controllers/Api/
    │   │   └── TelemetryIngestController.php
    │   ├── Filament/Widgets/
    │   │   ├── StatsOverview.php      # Card: Suhu, CPU, AC Target
    │   │   └── TemperatureChart.php   # Line chart histori
    │   └── Providers/Filament/
    │       └── AdminPanelProvider.php  # Panel + Reverb Hook
    ├── database/migrations/  # Tabel telemetry_logs, ac_control_logs
    └── routes/api.php        # POST /api/telemetry/ingest
```

## Cara Menjalankan

### 1. Infrastruktur (Docker)
```bash
docker-compose up -d          # MySQL + Mosquitto
```

### 2. Node-RED Simulator
```bash
npm install -g node-red node-red-dashboard
node-red                       # Buka http://localhost:1880
# Import nodered/flows.json → Deploy
# Buka dashboard: http://localhost:1880/ui
```

### 3. Python Backend
```bash
cd backend
pip install -r requirements.txt
python main.py
```

### 4. Laravel Dashboard
```bash
cd fe
php artisan reverb:start       # (terminal terpisah)
php artisan serve               # Buka http://localhost:8000/admin
```
