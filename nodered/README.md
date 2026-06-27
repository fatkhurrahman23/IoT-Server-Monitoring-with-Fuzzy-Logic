# Panduan Setup & Penggunaan Node-RED Simulator

## Prasyarat

Pastikan sudah terinstall:
- **Node.js** (v16+)
- **Node-RED** — install: `npm install -g node-red`
- **Node-RED Dashboard** — install: `npm install -g node-red-dashboard`
- **Mosquitto MQTT Broker** sudah berjalan (lihat root `docker-compose.yml`)

## Instalasi Node-RED & Dependencies

```bash
npm install -g node-red
npm install -g node-red-dashboard
```

## Import Flow

1. Jalankan Node-RED: `node-red`
2. Buka browser ke: `http://localhost:1880`
3. Klik menu kanan atas (hamburger) → **Import**
4. Pilih file `flows.json` dari folder ini
5. Klik **Import**, lalu **Deploy** (tombol merah di kanan atas)

## Palet Node yang Dibutuhkan

Pastikan palet berikut tersedia (biasanya otomatis terinstall):
- `node-red-dashboard` → UI slider, gauge, chart, text
- `node-red-contrib-mqtt-broker` (mqtt in/out bawaan Node-RED)

## Cara Menggunakan Simulator

1. Buka dashboard UI: `http://localhost:1880/ui`
2. Di panel **Input Simulator**:
   - Geser **Suhu Ruangan (°C)** slider (15 - 40)
   - Geser **Beban CPU (%)** slider (0 - 100)
3. Data otomatis dikirim ke MQTT topic `server/telemetry`
4. Hasil kalkulasi AC Target dari Python akan muncul di panel **Output Monitor**

## MQTT Topics

| Topic | Arah | Keterangan |
|---|---|---|
| `server/telemetry` | Node-RED → Python | Data sensor: suhu & beban CPU |
| `server/ac_control` | Python → Node-RED | Hasil fuzzy: target suhu AC |

## Struktur Flow

```
[Slider: Suhu] ─┐
                 ├─→ [Function: Build JSON] ─→ [MQTT Out: server/telemetry]
[Slider: CPU]  ─┘                              ↓
                                           [UI Text: Display]
                                               
[MQTT In: server/ac_control] ─→ [Function: Parse] ─┬─→ [Gauge: AC Target]
                                                   └─→ [Chart: History]
```
