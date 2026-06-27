# Dokumentasi Teknis Sistem — Simulasi Pemantauan & Kendali Otomatis Suhu Ruang Server

## 1. Arsitektur Sistem

```
┌─────────────────┐     MQTT (server/telemetry)     ┌─────────────────┐
│                 │ ───────────────────────────────► │                 │
│   Node-RED      │                                  │   Python AI     │
│   (Simulator)   │ ◄─────────────────────────────── │   Engine        │
│                 │     MQTT (server/ac_control)     │   (Fuzzy Logic) │
└────────┬────────┘                                  └────────┬────────┘
         │                                                     │
         │ HTTP (dashboard UI)                                 │ HTTP POST
         ▼                                                     ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Laravel Dashboard                            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────────┐ │
│  │ Alert    │  │ Stats    │  │ Charts   │  │ Reverb WebSocket     │ │
│  │ Banner   │  │ Overview │  │ History  │  │ (Real-time push)     │ │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────────────┘ │
│                        ┌──────────────┐                              │
│                        │   SQLite /   │                              │
│                        │   MySQL DB   │                              │
│                        └──────────────┘                              │
└─────────────────────────────────────────────────────────────────────┘
```

### 1.1 Alur Data Lengkap

```
Step 1: User menggerakkan slider di Node-RED UI Dashboard
   ↓
Step 2: Node-RED function node membangun JSON:
        {"temp": 32, "servers": [45, 80, 30, 12, 90]}
   ↓
Step 3: MQTT Out node publish ke topic server/telemetry
   ↓  (Mosquitto Broker — port 1883)
Step 4: Python subscribe topic server/telemetry
   ↓
Step 5: Python ekstrak servers[] → cpu_load = MAX(servers)
   ↓
Step 6: Python Fuzzy Sugeno(temp=32, cpu=90) → ac_target = 16°C
   ↓
Step 7a: Python publish server/ac_control → Node-RED gauge update
Step 7b: Python HTTP POST /api/telemetry/ingest → Laravel DB + Reverb
   ↓
Step 8: Laravel simpan ke telemetry_logs + broadcast TelemetryUpdated event
   ↓
Step 9: Dashboard menerima update via polling (1s) atau Reverb WebSocket
```

### 1.2 MQTT Topics

| Topic | Arah | Format |
|---|---|---|
| `server/telemetry` | Node-RED → Python | `{"temp": float, "servers": [float x5], "timestamp": string}` |
| `server/ac_control` | Python → Node-RED | `{"ac_target": float, "temp": float, "cpu_load": float, "status": string, "alert": bool, "timestamp": string}` |

### 1.3 REST API Endpoint

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/telemetry/ingest` | Menerima data telemetri dari Python backend, menyimpan ke DB, dan mem-broadcast event Reverb |

**Request Body:**
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

---

## 2. Spesifikasi Komponen

### 2.1 Node-RED Simulator (`nodered/`)

**Fungsi:** Menghasilkan data sensor simulasi (suhu ruangan + 5 server CPU load) melalui slider UI.

**Nodes:**
- `ui_slider` × 6: Suhu (15-40°C) + Server 1-5 CPU (0-100%)
- `function` (Build JSON): Mengagregasi semua input ke JSON
- `mqtt out`: Publish ke `server/telemetry`
- `mqtt in`: Subscribe `server/ac_control`
- `function` (Parse AC): Ekstrak hasil fuzzy
- `ui_gauge`: Tampilkan target suhu AC
- `ui_chart`: Histori AC target

**Akses Dashboard:** `http://localhost:1880/ui`

---

### 2.2 Python AI Engine (`backend/`)

**Fungsi:** Menerima data telemetri via MQTT, mengeksekusi Logika Fuzzy Sugeno, dan mengirim hasil ke MQTT + Laravel.

**Modul:**
| File | Peran |
|---|---|
| `main.py` | Entry daemon, orchestration, hysteresis, alert logic |
| `fuzzy_sugeno.py` | FIS Sugeno Orde-Nol (fuzzifikasi, inferensi, defuzzifikasi) |
| `mqtt_handler.py` | Subscribe/publish MQTT via paho-mqtt |
| `api_client.py` | HTTP POST ke Laravel API |
| `config.py` | Konfigurasi broker, threshold, range |

**Key Features:**
- Multi-server CPU aggregation (MAX)
- Hysteresis 0.5°C (anti-rapid cycling)
- Force publish setiap 5 siklus skip
- Alert threshold (suhu > 35°C)
- AC Status classification (idle/cooling/full)

---

### 2.3 Laravel Dashboard (`frontend/`)

**Fungsi:** Menyediakan antarmuka pemantauan administratif berbasis web.

**Teknologi:**
- Laravel 13.x (PHP 8.2+)
- Filament UI v5 (panel administratif)
- Laravel Reverb (WebSocket server)
- Livewire (komponen reaktif)
- Chart.js (grafik)
- SQLite (database development) / MySQL (production)

**Komponen Dashboard:**
| Widget | Tipe | Fungsi |
|---|---|---|
| AlertBanner | Widget Kustom | Banner hijau/merah status sistem |
| StatsOverview | Stat Card | Suhu, MAX CPU, AC Target, Status |
| RoomTempChart | Line Chart | Histori suhu ruangan |
| CpuLoadChart | Line Chart | Histori MAX CPU load |
| AcTargetChart | Line Chart | Histori target suhu AC |
| ServerLoadsChart | Line Chart | 5-line detail beban tiap server |

**Akses Dashboard:** `http://localhost:8000/admin`  
**Kredensial Default:** `admin@trk.local` / `password`

---

## 3. Database Schema

### `telemetry_logs`

| Kolom | Tipe | Deskripsi |
|---|---|---|
| `id` | bigint (PK) | Auto-increment |
| `temp` | float | Suhu ruangan (°C) |
| `cpu_load` | float | MAX CPU load dari 5 server (%) |
| `server_loads` | json | Array beban CPU per server `[s1, s2, s3, s4, s5]` |
| `ac_target` | float | Target suhu AC hasil fuzzy (°C) |
| `status` | varchar | idle / cooling / full |
| `created_at` | timestamp | Waktu data diterima |
| `updated_at` | timestamp | Auto-update |

---

## 4. Keamanan & Isolasi Jaringan

Sesuai dokumen `SYSTEM-DESIGN.md`, sistem ini menerapkan prinsip:

| Prinsip | Implementasi |
|---|---|
| **Sentralisasi Gateway** | Semua lalu lintas MQTT melalui Mosquitto broker terpusat |
| **Isolasi Environment** | Node-RED, Python, dan Laravel berjalan di proses/terminal terpisah |
| **Anonymous Access** | MQTT broker menggunakan `allow_anonymous true` untuk development |
| **No External Library** | Fuzzy Logic diimplementasikan murni tanpa library ML black-box |

---

## 5. Deployment

### Development (Lokal)

```bash
# Terminal 1: Docker (MySQL + Mosquitto)
docker-compose up -d

# Terminal 2: Node-RED
node-red

# Terminal 3: Python Backend
cd backend && pip install -r requirements.txt && python main.py

# Terminal 4: Laravel
cd frontend
php artisan serve
# (Terminal 5 opsional)
php artisan reverb:start
```

### Port Mapping

| Service | Port | URL |
|---|---|---|
| Node-RED Editor | 1880 | http://localhost:1880 |
| Node-RED Dashboard | 1880 | http://localhost:1880/ui |
| Mosquitto MQTT | 1883 | (internal) |
| Laravel Web | 8000 | http://localhost:8000 |
| Laravel Reverb | 8080 | ws://localhost:8080 |
| Filament Admin | 8000 | http://localhost:8000/admin |
| MySQL | 3306 | (internal via Docker) |

---

## 6. Panduan Penggunaan

### 6.1 Memulai Simulasi

1. Buka Node-RED Dashboard: `http://localhost:1880/ui`
2. Geser **Suhu Ruangan** slider (15-40°C)
3. Geser **Server 1-5 CPU** slider (0-100%)
4. Data otomatis dikirim ke MQTT setiap slider berubah

### 6.2 Membaca Output

- **Node-RED Output Monitor:** Gauge menunjukkan target suhu AC yang dihitung AI
- **Laravel Dashboard:** Stat card + chart menampilkan metrik secara real-time (polling 1 detik)
- **Alert Banner:** Otomatis berubah merah jika suhu > 35°C

### 6.3 Menguji Skenario

| Skenario | Suhu | Server Loads | AC Target | Status |
|---|---|---|---|---|
| Normal Idle | 22°C | [10, 15, 5, 12, 8] | ~26°C | idle |
| Normal Load | 25°C | [50, 60, 45, 55, 40] | ~22°C | cooling |
| High Load | 28°C | [80, 75, 90, 85, 70] | ~18°C | cooling |
| Overheat | 38°C | [95, 90, 98, 92, 88] | ~16°C | full + ALERT |
| Thermal Emergency | 42°C | [100, 100, 100, 100, 100] | 16°C | full + CRITICAL |

---

## 7. Log Perubahan

| Tanggal | Versi | Deskripsi |
|---|---|---|
| 2026-06-26 | v1.0 | Initial build — MQTT, Fuzzy Sugeno, Node-RED, Laravel Filament |
| 2026-06-27 | v1.1 | Multi-server CPU aggregation (MAX), hysteresis, alert threshold |
| 2026-06-27 | v1.2 | AC status indicator, Server Loads chart, polling optimization |
| 2026-06-27 | v1.3 | Layout fix, Reverb sync, query caching, dokumentasi lengkap |
