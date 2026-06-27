# 🖥️ IoT Server Room Monitoring with Fuzzy Logic

**Simulasi Pemantauan & Kendali Otomatis Suhu Ruang Server berbasis Logika Fuzzy Sugeno**

Sistem simulasi perangkat lunak untuk memantau infrastruktur kritikal (ruang server) dan mengendalikan sistem pendingin (AC) secara otomatis menggunakan kecerdasan buatan (*Artificial Intelligence*) — Logika Fuzzy Metode Sugeno.

---

## ✨ Fitur Utama

| Fitur | Deskripsi |
|---|---|
| **🧠 Fuzzy Logic Sugeno** | Algoritma AI murni Python (tanpa library ML) — 2 input, 9 aturan, output target suhu AC |
| **🖥️ Multi-Server** | 5 server dengan beban CPU terpisah, agregasi MAX untuk cooling decision |
| **📊 Real-Time Dashboard** | Dashboard Filament dengan polling 1 detik + WebSocket Reverb |
| **⚠️ Alert System** | Overheat warning (suhu > 35°C) + AC status indicator (idle/cooling/full) |
| **🔄 Hysteresis** | Anti-rapid cycling — AC hanya berubah jika selisih > 0.5°C |
| **🔌 MQTT Protocol** | Komunikasi real-time via Eclipse Mosquitto broker |
| **🐳 Docker** | MySQL + Mosquitto dalam container untuk deployment mudah |

---

## 🏗️ Arsitektur

```
┌──────────┐     MQTT      ┌──────────┐    HTTP POST    ┌──────────────┐
│ Node-RED │ ◄──────────► │ Python   │ ──────────────► │ Laravel      │
│(Simulator)│              │(Fuzzy AI)│                 │(Dashboard)   │
└──────────┘              └──────────┘                 └──────┬───────┘
                                                    Reverb WebSocket
                                                    (Real-time Push)
```

---

## 📋 Prasyarat

### Wajib
- **Python 3.9+** — untuk AI Engine
- **PHP 8.2+** + **Composer** — untuk Laravel
- **Node.js 16+** — untuk Node-RED
- **Git** — untuk clone repository

### Opsional
- **Docker Desktop** — untuk MySQL + Mosquitto (jika tidak ingin install manual)

### Library Python
```bash
pip install paho-mqtt
```

---

## 🚀 Panduan Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/fatkhurrahman23/IoT-Server-Monitoring-with-Fuzzy-Logic.git
cd IoT-Server-Monitoring-with-Fuzzy-Logic
```

### 2. Infrastruktur (Docker)

```bash
docker-compose up -d
```

> Menjalankan MySQL (port 3306) + Eclipse Mosquitto MQTT (port 1883, 9001)

### 3. Node-RED Simulator

```bash
npm install -g node-red node-red-dashboard
node-red
```

- Buka http://localhost:1880
- Import `nodered/flows.json` (Menu kanan atas → Import)
- **Deploy** (tombol merah)
- Dashboard UI: http://localhost:1880/ui

### 4. Python AI Engine

```bash
cd backend
pip install -r requirements.txt
python main.py
```

Output console:
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
- Kredensial: **admin@trk.local** / **password**

### 6. Reverb WebSocket (Real-Time Push)

```bash
cd frontend
php artisan reverb:start
```

> Opsional — Dashboard tetap berfungsi via polling tanpa Reverb.

---

## 📂 Struktur Direktori

```
IoT-Server-Monitoring-with-Fuzzy-Logic/
├── README.md                     # File ini
├── DOKUMENTASI-SISTEM.md         # Dokumentasi teknis lengkap
├── FUZZY-LOGIC.md                # Perhitungan detail Logika Fuzzy
├── SYSTEM-DESIGN.md              # Desain awal sistem
├── docker-compose.yml            # MySQL + Mosquitto
├── mosquitto/
│   └── mosquitto.conf            # Konfigurasi MQTT broker
├── backend/                       # Python AI Engine
│   ├── main.py                    # Entry daemon
│   ├── fuzzy_sugeno.py            # FIS Sugeno (hardcoded)
│   ├── mqtt_handler.py            # MQTT pub/sub
│   ├── api_client.py              # HTTP POST ke Laravel
│   ├── config.py                  # Threshold, broker, range
│   └── requirements.txt
├── nodered/                       # Node-RED Simulator
│   ├── flows.json                 # Flow (importable)
│   └── README.md                  # Panduan Node-RED
└── frontend/                      # Laravel Dashboard
    ├── app/
    │   ├── Events/TelemetryUpdated.php
    │   ├── Filament/Pages/Dashboard.php
    │   ├── Filament/Widgets/       # 6 widgets (alert, stats, 4 charts)
    │   ├── Http/Controllers/Api/
    │   ├── Models/TelemetryLog.php #
    │   └── Providers/
    ├── database/migrations/
    └── routes/api.php
```

---

## 🎮 Cara Menggunakan

### Simulasi Normal
1. Buka Node-RED Dashboard: http://localhost:1880/ui
2. Geser slider **Suhu Ruangan** (15–40°C)
3. Geser slider **Server 1-5 CPU** (0–100%)
4. Amati **Output Monitor** di Node-RED dan **Dashboard** Laravel

### Skenario Uji

| Skenario | Suhu | Server CPU | Hasil AC | Status |
|---|---|---|---|---|
| Idle | 22°C | [10,15,5,12,8] | ~26°C | idle |
| Normal | 25°C | [50,60,45,55,40] | ~22°C | cooling |
| Beban Tinggi | 28°C | [80,75,90,85,70] | ~18°C | cooling |
| Overheat | 38°C | [95,90,98,92,88] | ~16°C | full ⚠️ ALERT |

---

## 📡 API Endpoint

### POST `/api/telemetry/ingest`

Menerima data dari Python backend.

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

## 🔧 Konfigurasi

### Python (`backend/config.py`)
```python
MQTT_BROKER = "localhost"
MQTT_PORT = 1883
HYSTERESIS_THRESHOLD = 0.5     # °C
ALERT_TEMP_THRESHOLD = 35.0    # °C
```

### Laravel (`.env`)
```env
DB_CONNECTION=sqlite            # atau mysql
BROADCAST_CONNECTION=reverb
REVERB_HOST="localhost"
REVERB_PORT=8080
```

---

## 📚 Dokumentasi

| File | Isi |
|---|---|
| [`DOKUMENTASI-SISTEM.md`](DOKUMENTASI-SISTEM.md) | Arsitektur, komponen, alur data, API, deployment |
| [`FUZZY-LOGIC.md`](FUZZY-LOGIC.md) | Perhitungan fuzzifikasi, rule base, defuzzifikasi, contoh numerik |
| [`SYSTEM-DESIGN.md`](SYSTEM-DESIGN.md) | Desain awal sistem & requirement |

---

## 🧪 Testing

1. Inject nilai ekstrem di Node-RED:
   - Suhu 40°C + semua Server 100% → AC harus 16°C, status `full`, alert `ON`
2. Verifikasi Console Python menampilkan kalkulasi fuzzy
3. Verifikasi Dashboard Laravel update dalam < 1 detik
4. Verifikasi Alert Banner merah muncul saat suhu > 35°C
5. Verifikasi AC Status berubah (hijau/kuning/biru) sesuai kondisi

---

## 📝 Lisensi

Proyek ini dibuat untuk keperluan akademik — Tugas Besar Mata Kuliah *Special Topic in Industrial Informatics*.

---

## 👤 Author

**Fatkhurrahman** — Politeknik Negeri Semarang (POLINES)  
GitHub: [@fatkhurrahman23](https://github.com/fatkhurrahman23)
