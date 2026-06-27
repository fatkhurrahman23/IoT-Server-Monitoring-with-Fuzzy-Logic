# Dokumentasi Perhitungan Logika Fuzzy Sugeno

## 1. Pendahuluan

### 1.1 Mengapa Fuzzy Logic?

Suhu ruang server dan beban komputasi tidak bisa dikategorikan secara hitam-putih ("panas" vs "tidak panas"). Terdapat area abu-abu: 27°C mungkin "agak panas", 24°C "cukup normal". Logika Fuzzy dipilih karena mampu menangani ketidakpastian (uncertainty) ini melalui derajat keanggotaan (membership degree).

### 1.2 Mengapa Metode Sugeno?

Metode Sugeno (khususnya **Sugeno Orde-Nol**) dipilih karena:
- **Output berupa konstanta numerik** — cocok untuk sistem kontrol (target suhu AC)
- **Komputasi lebih ringan** dibanding Mamdani (tidak perlu centroid area)
- **Cocok untuk optimasi & kontrol** — Sugeno adalah standar di aplikasi kontrol industri

---

## 2. Variabel Input & Output

### 2.1 Input 1: Suhu Ruangan

| Properti | Nilai |
|---|---|
| Domain | 15°C – 40°C |
| Satuan | °C (derajat Celsius) |
| Sumber | Node-RED slider simulator |

**Himpunan Fuzzy:**

| Nama | Range | Fungsi Keanggotaan |
|---|---|---|
| **Dingin** | ≤20°C → µ=1.0, 20-25°C → linier turun | Trapesium-turun |
| **Normal** | 22-25°C → linier naik, 25°C → µ=1.0, 25-28°C → linier turun | Segitiga |
| **Panas** | 25-28°C → linier naik, ≥28°C → µ=1.0 | Trapesium-naik |

**Grafik Fungsi Keanggotaan:**

```
µ(x)
1.0 ┤   Dingin        Normal         Panas
    │   ╲             ╱‾‾‾╲          ╱
    │    ╲           ╱     ╲        ╱
    │     ╲         ╱       ╲      ╱
    │      ╲       ╱         ╲    ╱
    │       ╲     ╱           ╲  ╱
0.0 ┤────────╲───╱─────────────╲╱───────────
    15       20  22   25   28      40     x (°C)
```

**Rumus Fuzzifikasi Suhu:**
```python
# DINGIN
if temp <= 20:       µ_dingin = 1.0
elif 20 < temp <= 25: µ_dingin = (25 - temp) / 5.0
else:                 µ_dingin = 0.0

# NORMAL
if 22 <= temp <= 25:  µ_normal = (temp - 22) / 3.0
elif 25 < temp <= 28: µ_normal = (28 - temp) / 3.0
else:                 µ_normal = 0.0

# PANAS
if 25 <= temp < 28:  µ_panas = (temp - 25) / 3.0
elif temp >= 28:      µ_panas = 1.0
else:                 µ_panas = 0.0
```

---

### 2.2 Input 2: Beban CPU (MAX Aggregation)

| Properti | Nilai |
|---|---|
| Domain | 0% – 100% |
| Satuan | % (persentase utilisasi) |
| Sumber | MAX(Server1, Server2, ..., Server5) dari Node-RED |

**Himpunan Fuzzy:**

| Nama | Range | Fungsi Keanggotaan |
|---|---|---|
| **Rendah** | ≤25% → µ=1.0, 25-40% → linier turun | Trapesium-turun |
| **Sedang** | 30-50% → linier naik, 50-70% → linier turun | Segitiga |
| **Tinggi** | 60-75% → linier naik, ≥75% → µ=1.0 | Trapesium-naik |

**Grafik Fungsi Keanggotaan:**

```
µ(x)
1.0 ┤   Rendah         Sedang         Tinggi
    │   ╲             ╱‾‾‾╲          ╱
    │    ╲           ╱     ╲        ╱
    │     ╲         ╱       ╲      ╱
    │      ╲       ╱         ╲    ╱
    │       ╲     ╱           ╲  ╱
0.0 ┤────────╲───╱─────────────╲╱───────────
     0       25  30    50    60 75    100   x (%)
```

**Rumus Fuzzifikasi CPU:**
```python
# RENDAH
if cpu <= 25:        µ_rendah = 1.0
elif 25 < cpu <= 40: µ_rendah = (40 - cpu) / 15.0
else:                µ_rendah = 0.0

# SEDANG
if 30 <= cpu <= 50:  µ_sedang = (cpu - 30) / 20.0
elif 50 < cpu <= 70: µ_sedang = (70 - cpu) / 20.0
else:                µ_sedang = 0.0

# TINGGI
if 60 <= cpu < 75:   µ_tinggi = (cpu - 60) / 15.0
elif cpu >= 75:       µ_tinggi = 1.0
else:                 µ_tinggi = 0.0
```

---

### 2.3 Output: Target Suhu AC

| Properti | Nilai |
|---|---|
| Domain | 16°C – 28°C |
| Satuan | °C |
| Metode Defuzzifikasi | Weighted Average (Sugeno Orde-Nol) |
| Output ke | MQTT `server/ac_control` + Laravel API |

**Interpretasi Output:**
| Range AC | Status | Makna |
|---|---|---|
| ≥26°C | idle | Hemat energi — server idle, suhu normal |
| 20 – 25.9°C | cooling | Pendinginan normal — server bekerja |
| <20°C | full | Pendinginan maksimal — beban tinggi/overheat |

---

## 3. Rule Base (9 Aturan)

Aturan menggunakan operator **AND** (logika **min**) untuk menggabungkan dua kondisi.

| Rule | Suhu | Beban CPU | Target AC | Logika Dibaliknya |
|---|---|---|---|---|
| **R1** | Dingin | Rendah | **28°C** | Server idle + ruangan dingin → tidak butuh pendinginan |
| **R2** | Dingin | Sedang | **26°C** | Ada beban ringan → sedikit pendinginan |
| **R3** | Dingin | Tinggi | **24°C** | Beban tinggi tapi ruangan dingin → pendinginan sedang |
| **R4** | Normal | Rendah | **26°C** | Suhu normal tapi server idle → sedikit pendinginan |
| **R5** | Normal | Sedang | **22°C** | Kondisi tipikal → pendinginan normal |
| **R6** | Normal | Tinggi | **20°C** | Server kerja keras → pendinginan kuat |
| **R7** | Panas | Rendah | **22°C** | Ruangan panas walau server idle → pendinginan normal |
| **R8** | Panas | Sedang | **18°C** | Panas + beban → pendinginan intensif |
| **R9** | Panas | Tinggi | **16°C** | Kondisi terburuk → pendinginan maksimal |

---

## 4. Evaluasi Aturan (Inferensi)

### 4.1 Fire Strength (α-predikat)

Untuk setiap aturan, fire strength dihitung dengan **operator AND (min)**:

```
α_Ri = min(µ_suhu, µ_cpu)
```

### 4.2 Contoh Perhitungan Numerik

**Skenario:** Suhu = 32°C, Server Loads = [45, 80, 30, 12, 90]

**Step 1: Agregasi Multi-Server**
```
cpu_load = MAX(45, 80, 30, 12, 90) = 90%
```

**Step 2: Fuzzifikasi Suhu (32°C)**
```
µ_dingin  = 0.0     (di atas 25°C = bukan dingin)
µ_normal  = 0.0     (di atas 28°C = bukan normal)
µ_panas   = 1.0     (≥ 28°C = pasti panas)
```

**Step 3: Fuzzifikasi CPU (90%)**
```
µ_rendah  = 0.0     (di atas 40% = bukan rendah)
µ_sedang  = 0.0     (di atas 70% = bukan sedang)
µ_tinggi  = 1.0     (≥ 75% = pasti tinggi)
```

**Step 4: Evaluasi 9 Rules**
```
Hanya rule dengan µ_panas > 0 DAN µ_tinggi > 0 yang aktif.

R1: min(0, 0) = 0    → tidak aktif
R2: min(0, 0) = 0    → tidak aktif
R3: min(0, 1) = 0    → tidak aktif
R4: min(0, 0) = 0    → tidak aktif
R5: min(0, 0) = 0    → tidak aktif
R6: min(0, 1) = 0    → tidak aktif
R7: min(1, 0) = 0    → tidak aktif
R8: min(1, 0) = 0    → tidak aktif
R9: min(1, 1) = 1.0  → α = 1.0, z = 16°C  ← AKTIF
```

**Step 5: Defuzzifikasi (Weighted Average)**
```
z* = Σ(α_i × z_i) / Σ(α_i)
z* = (1.0 × 16) / 1.0
z* = 16.0°C
```

**Kesimpulan:** Pada suhu 32°C + CPU 90%, AC diset ke **16°C** (pendinginan maksimal).

---

### 4.3 Contoh Perhitungan dengan Derajat Parsial

**Skenario:** Suhu = 26°C, Server Loads = [30, 45, 50, 35, 55]

**Fuzzifikasi Suhu:**
```
µ_dingin  = 0.0
µ_normal  = (28 - 26) / 3 = 0.667
µ_panas   = (26 - 25) / 3 = 0.333
```

**Fuzzifikasi CPU (MAX = 55%):**
```
µ_rendah  = 0.0
µ_sedang  = (70 - 55) / 20 = 0.75
µ_tinggi  = 0.0
```

**Evaluasi Rules:**
```
R4: min(0.667, 0.0) = 0         → 0
R5: min(0.667, 0.75) = 0.667    → α=0.667, z=22
R6: min(0.667, 0.0) = 0         → 0
R7: min(0.333, 0.0) = 0         → 0
R8: min(0.333, 0.75) = 0.333    → α=0.333, z=18
R9: min(0.333, 0.0) = 0         → 0
```

**Defuzzifikasi:**
```
z* = (0.667 × 22 + 0.333 × 18) / (0.667 + 0.333)
   = (14.674 + 5.994) / 1.0
   = 20.67°C
```

**Kesimpulan:** Suhu 26°C (normal-panas) + CPU 55% (sedang) → AC ≈ **20.7°C** (pendinginan kuat).

---

## 5. Multi-Server Aggregation Strategy

### 5.1 Mengapa MAX?

Dalam server room dengan 5 server, pendekatan yang digunakan adalah **MAX aggregation**:

```
cpu_load = MAX(Server1, Server2, Server3, Server4, Server5)
```

**Alasan:**
1. **Prinsip Thermal Management:** Cooling harus melayani titik terpanas. Jika satu server di 95% sedangkan 4 lainnya di 10%, kegagalan mendinginkan titik panas tersebut bisa menyebabkan kerusakan hardware.
2. **Safety-First:** Lebih baik over-cooling sedikit daripada under-cooling yang berisiko downtime.
3. **Standar Industri:** Data center BMS (Building Management System) memonitor suhu inlet/outlet tiap rack dan menggunakan suhu tertinggi sebagai acuan.

### 5.2 Alternatif yang Tidak Dipilih

| Strategi | Kelemahan |
|---|---|
| **Rata-rata (AVG)** | Menyamarkan hotspot — 5 server di [10,10,10,10,90] → AVG=26% (terlihat aman padahal ada server di 90%) |
| **Weighted Sum** | Butuh tuning bobot yang subjektif, tidak ada data kalibrasi |

---

## 6. Hysteresis (Anti-Cycling)

### 6.1 Masalah

Tanpa hysteresis, setiap perubahan kecil pada slider (misal suhu 25.1°C → 25.3°C) akan mengubah target AC secara instan. Di dunia nyata, kompresor AC tidak boleh cycling terlalu cepat karena bisa rusak.

### 6.2 Solusi

Hysteresis threshold **0.5°C** diterapkan sebelum AC target di-publish ke MQTT:

```python
if abs(new_ac - last_ac) < 0.5:
    skip_mqtt_publish()   # Jangan kirim perintah AC baru
else:
    publish_to_mqtt()
    last_ac = new_ac
```

**Catatan Penting:** Dashboard Laravel **tetap** menerima data telemetri (suhu, CPU, server loads) meskipun terjadi skip hysteresis. Hysteresis hanya menahan **perintah AC**, bukan logging data.

### 6.3 Force Publish

Untuk mencegah AC stuck terlalu lama, sistem memaksa publish setelah **5 siklus skip** (≈15 detik dengan interval 3 detik Node-RED).

---

## 7. Alert System & AC Status Classification

### 7.1 Alert Threshold

| Kondisi | Threshold | Level | Indikator Dashboard |
|---|---|---|---|
| Normal | temp ≤ 35°C | ✅ AMAN | Banner hijau |
| Warning | temp > 35°C | ⚠️ OVERHEAT | Banner merah |
| Critical | temp > 40°C | 🚨 EMERGENCY | Banner merah + stat danger |

### 7.2 AC Status Classification

AC Status diklasifikasikan berdasarkan output fuzzy sebagai berikut:

| Target AC | Status Internal | Label UI | Warna Stat |
|---|---|---|---|
| ≥ 26°C | `idle` | Hemat Energi | Hijau (success) |
| 20 – 25.9°C | `cooling` | Pendinginan Normal | Kuning (warning) |
| < 20°C | `full` | Pendinginan Maksimal | Biru (info) |

---

## 8. Implementasi Python

Seluruh perhitungan fuzzy logic diimplementasikan di `backend/fuzzy_sugeno.py` menggunakan **Python murni tanpa library eksternal** (sesuai requirement akademik). Hanya library `paho-mqtt` yang digunakan untuk koneksi MQTT.

Fungsi utama:
- `fuzzy_temperature(temp)` → dict `{dingin, normal, panas}`
- `fuzzy_cpu_load(cpu)` → dict `{rendah, sedang, tinggi}`
- `evaluate_rules(temp_fuzzy, cpu_fuzzy)` → list of `(α, z_sugeno)`
- `defuzzify_sugeno(rules)` → float `ac_target`
- `compute_ac_target(temp, cpu_load)` → float (fungsi wrapper utama)

---

## 9. Validasi Sistem

### 9.1 Test Cases

| Test | Suhu | MAX CPU | Expected AC | Status |
|---|---|---|---|---|
| TC1 | 20°C | 10% | 28°C | idle |
| TC2 | 24°C | 40% | ~24°C | cooling |
| TC3 | 25°C | 50% | ~22°C | cooling |
| TC4 | 28°C | 75% | ~18°C | cooling |
| TC5 | 32°C | 90% | 16°C | full |
| TC6 | 38°C | 100% | 16°C | full + ALERT |

### 9.2 Verifikasi Log

Setiap kalkulasi dicetak ke console Python:
```
[INPUT] Temp: 32°C | Server Loads: [45, 80, 30, 12, 90] | MAX CPU: 90%
[FUZZY] Calculated AC Target: 16.0°C
[ALERT] ⚠️ Room temperature 38.0°C exceeds safe threshold (35.0°C)!
[HYSTERESIS] AC delta 0.0°C < 0.5°C. Skipping MQTT publish. (skip 1/5)
[API] Data sent to Laravel. Status: 201
```
