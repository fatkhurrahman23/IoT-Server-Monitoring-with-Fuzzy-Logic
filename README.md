# 🖥️ IoT Server Room Monitoring with Fuzzy Logic

**Server Room Temperature Monitoring & Automatic Control Simulation based on Sugeno Fuzzy Logic**

A software-based simulation system for monitoring critical infrastructure (server rooms) and controlling the cooling system (AC) automatically using Artificial Intelligence — the Sugeno Fuzzy Logic method.

---

## ✨ Key Features

| Feature | Description |
|---|---|
| **🧠 Fuzzy Logic Sugeno** | Pure Python AI algorithm (no ML libraries) — 2 inputs, 9 rules, AC target temperature output |
| **🖥️ Multi-Server** | 5 servers with separate CPU loads, MAX aggregation for cooling decisions |
| **📊 Real-Time Dashboard** | Filament dashboard with 1-second polling + Reverb WebSocket |
| **⚠️ Alert System** | Overheat warning (temp > 35°C) + AC status indicator (idle/cooling/full) |
| **🔄 Hysteresis** | Anti-rapid cycling — AC only changes when delta > 0.5°C |
| **🔌 MQTT Protocol** | Real-time communication via Eclipse Mosquitto broker |
| **🐳 Docker** | MySQL + Mosquitto in containers for easy deployment |

---

## 🏗️ Architecture

```
┌──────────┐     MQTT      ┌──────────┐    HTTP POST    ┌──────────────┐
│ Node-RED │ ◄──────────► │ Python   │ ──────────────► │ Laravel      │
│(Simulator)│              │(Fuzzy AI)│                 │(Dashboard)   │
└──────────┘              └──────────┘                 └──────┬───────┘
                                                    Reverb WebSocket
                                                    (Real-time Push)
```

---

## 📋 Prerequisites

### Required
- **Python 3.9+** — for AI Engine
- **PHP 8.2+** + **Composer** — for Laravel
- **Node.js 16+** — for Node-RED
- **Git** — for cloning the repository

### Optional
- **Docker Desktop** — for MySQL + Mosquitto (if you don't want to install manually)

### Python Library
```bash
pip install paho-mqtt
```

---

## 🚀 Installation Guide

### 1. Clone Repository

```bash
git clone https://github.com/fatkhurrahman23/IoT-Server-Monitoring-with-Fuzzy-Logic.git
cd IoT-Server-Monitoring-with-Fuzzy-Logic
```

### 2. Infrastructure (Docker)

```bash
docker-compose up -d
```

> Runs MySQL (port 3306) + Eclipse Mosquitto MQTT (ports 1883, 9001)

### 3. Node-RED Simulator

```bash
npm install -g node-red node-red-dashboard
node-red
```

- Open http://localhost:1880
- Import `nodered/flows.json` (top right menu → Import)
- **Deploy** (red button)
- Dashboard UI: http://localhost:1880/ui

### 4. Python AI Engine

```bash
cd backend
pip install -r requirements.txt
python main.py
```

Console output:
```
=== Python Fuzzy Logic Engine Started ===
Waiting for telemetry data from Node-RED...
```

### 5. Laravel Dashboard

```bash
cd frontend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

- Dashboard: http://localhost:8000/admin
- Credentials: **admin@trk.local** / **password**

### 6. Reverb WebSocket (Real-Time Push)

```bash
cd frontend
php artisan reverb:start
```

> Optional — Dashboard still works via polling without Reverb.

---

## 📂 Directory Structure

```
IoT-Server-Monitoring-with-Fuzzy-Logic/
├── README.md                     # This file
├── DOKUMENTASI-SISTEM.md         # Complete technical documentation
├── FUZZY-LOGIC.md                # Detailed Fuzzy Logic calculations
├── SYSTEM-DESIGN.md              # Initial system design
├── docker-compose.yml            # MySQL + Mosquitto
├── mosquitto/
│   └── mosquitto.conf            # MQTT broker configuration
├── backend/                       # Python AI Engine
│   ├── main.py                    # Entry daemon
│   ├── fuzzy_sugeno.py            # FIS Sugeno (hardcoded)
│   ├── mqtt_handler.py            # MQTT pub/sub
│   ├── api_client.py              # HTTP POST to Laravel
│   ├── config.py                  # Thresholds, broker, ranges
│   └── requirements.txt
├── nodered/                       # Node-RED Simulator
│   ├── flows.json                 # Flow (importable)
│   └── README.md                  # Node-RED guide
└── frontend/                      # Laravel Dashboard
    ├── app/
    │   ├── Events/TelemetryUpdated.php
    │   ├── Filament/Pages/Dashboard.php
    │   ├── Filament/Widgets/       # 6 widgets (alert, stats, 4 charts)
    │   ├── Http/Controllers/Api/
    │   ├── Models/TelemetryLog.php
    │   └── Providers/
    ├── database/migrations/
    └── routes/api.php
```

---

## 🎮 How to Use

### Normal Simulation
1. Open Node-RED Dashboard: http://localhost:1880/ui
2. Slide the **Room Temperature** slider (15–40°C)
3. Slide the **Server 1-5 CPU** sliders (0–100%)
4. Observe the **Output Monitor** in Node-RED and the **Dashboard** in Laravel

### Test Scenarios

| Scenario | Temp | Server CPU | AC Output | Status |
|---|---|---|---|---|
| Idle | 22°C | [10,15,5,12,8] | ~26°C | idle |
| Normal | 25°C | [50,60,45,55,40] | ~22°C | cooling |
| High Load | 28°C | [80,75,90,85,70] | ~18°C | cooling |
| Overheat | 38°C | [95,90,98,92,88] | ~16°C | full ⚠️ ALERT |

---

## 📡 API Endpoint

### POST `/api/telemetry/ingest`

Receives data from the Python backend.

**Request:**
```json
{
  "temp": 32.5,
  "cpu_load": 90,
  "ac_target": 16.0,
  "servers": [45, 80, 30, 12, 90],
  "status": "full",
  "alert": false
}
```

**Response:** `201 Created`
```json
{ "status": "ok", "id": 161 }
```

---

## 🔧 Configuration

### Python (`backend/config.py`)
```python
MQTT_BROKER = "localhost"
MQTT_PORT = 1883
HYSTERESIS_THRESHOLD = 0.5     # °C
ALERT_TEMP_THRESHOLD = 35.0    # °C
```

### Laravel (`.env`)
```env
DB_CONNECTION=sqlite            # or mysql
BROADCAST_CONNECTION=reverb
REVERB_HOST="localhost"
REVERB_PORT=8080
```

---

## 📚 Documentation

| File | Contents |
|---|---|
| [`DOKUMENTASI-SISTEM.md`](DOKUMENTASI-SISTEM.md) | Architecture, components, data flow, API, deployment |
| [`FUZZY-LOGIC.md`](FUZZY-LOGIC.md) | Fuzzification, rule base, defuzzification, numerical examples |
| [`SYSTEM-DESIGN.md`](SYSTEM-DESIGN.md) | Initial system design & requirements |

---

## 🧪 Testing

1. Inject extreme values in Node-RED:
   - Temp 40°C + all Servers at 100% → AC should be 16°C, status `full`, alert `ON`
2. Verify Python console displays fuzzy calculations
3. Verify Laravel Dashboard updates within < 1 second
4. Verify Alert Banner turns red when temp > 35°C
5. Verify AC Status changes (green/yellow/blue) according to conditions

---

## 📝 License

This project was created for academic purposes — Capstone Project for the *Special Topic in Industrial Informatics* course.
