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
[x] 2026-06-27 - Enhancement: Multi-Server (5 CPU Load) + MAX Aggregation - Selesai
[x] 2026-06-27 - Enhancement: Hysteresis Anti-Cycling AC (threshold 0.5°C) - Selesai
[x] 2026-06-27 - Enhancement: Overheat Alert Threshold (suhu > 35°C) - Selesai
[x] 2026-06-27 - Enhancement: AC Status Indicator (idle / cooling / full) - Selesai
[x] 2026-06-27 - Enhancement: 5-Server Loads Multi-Line Chart Dashboard - Selesai
[x] 2026-06-27 - Dashboard: Alert Banner + AC Status Badge + Collapsible Sidebar - Selesai

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
│   ├── config.py              # Konfigurasi MQTT, API URL, hysteresis, alert
│   ├── fuzzy_sugeno.py        # FIS Sugeno (Fuzzifikasi, 9 Rules, Defuzzifikasi)
│   ├── mqtt_handler.py        # Subscribe/Publish MQTT (+ status, alert)
│   ├── api_client.py          # HTTP POST ke Laravel API (+ servers, status, alert)
│   └── main.py                # Entry point (MAX aggregation, hysteresis, AC status)
├── nodered/                   # Node-RED Simulator
│   ├── flows.json             # Flow (5 server sliders + AC status display)
│   └── README.md              # Panduan setup simulator
└── fe/                        # Laravel Dashboard
    ├── app/
    │   ├── Models/            # TelemetryLog (server_loads JSON, status)
    │   ├── Events/            # TelemetryUpdated (servers, status, alert)
    │   ├── Http/Controllers/Api/
    │   │   └── TelemetryIngestController.php
    │   ├── Filament/
    │   │   ├── Pages/Dashboard.php          # 3-column grid layout
    │   │   └── Widgets/
    │   │       ├── AlertBanner.php           # Warning/Clear banner
    │   │       ├── StatsOverview.php         # 4 stats: Suhu, MAX CPU, AC Target, Status
    │   │       ├── ServerLoadsChart.php      # 5-line chart per server
    │   │       ├── RoomTempChart.php         # Line chart suhu ruangan
    │   │       ├── CpuLoadChart.php          # Line chart MAX CPU load
    │   │       └── AcTargetChart.php         # Line chart target AC
    │   └── Providers/Filament/
    │       └── AdminPanelProvider.php        # Full-width, collapsible sidebar, 3-col
    ├── database/migrations/  # telemetry_logs + server_loads JSON + status
    ├── resources/views/filament/widgets/
    │   └── alert-banner.blade.php            # Template alert/normal banner
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
# Login: admin@trk.local / password
```

## Fitur AI & Otomatisasi

| Fitur | Deskripsi |
|---|---|
| **Fuzzy Sugeno** | 2 input (suhu + MAX CPU), 9 aturan, 1 output (target suhu AC) |
| **MAX Aggregation** | 5 server CPU load → ambil nilai tertinggi → tentukan pendinginan |
| **Hysteresis** | AC hanya berubah jika selisih > 0.5°C (cegah cycling) |
| **Alert Threshold** | Warning jika suhu > 35°C (banner merah di dashboard) |
| **AC Status** | idle (≥26°C) / cooling (20-26°C) / full (<20°C) — badge warna |

## Dashboard Layout

```
┌─────────────────────────────────────────────────────────┐
│  [Alert Banner: Normal / WARNING Overheating]           │
├──────────────┬──────────────┬──────────────┬────────────┤
│  Suhu Ruang  │  MAX CPU     │  Target AC   │  Status AC │
│  (Stats)     │  (Stats)     │  (Stats)     │  (Stats)   │
├──────────────┴──────────────┴──────────────┴────────────┤
│  [Server Loads Chart: 5 line multi-color]               │
├─────────────────────────────────────────────────────────┤
│  [Room Temp Chart]  [CPU Load Chart]  [AC Target Chart] │
│  (3-column grid)                                        │
└─────────────────────────────────────────────────────────┘
```
