# Desain dan Rancangan Sistem: Simulasi Pemantauan & Kendali Otomatis Suhu Ruang Server berbasis Logika Fuzzy

## 1. Deskripsi Proyek

Proyek ini adalah sistem simulasi perangkat lunak (*software-based simulation*) untuk memantau infrastruktur kritikal (ruang server) dan mengendalikan sistem pendingin (AC) secara otomatis menggunakan *Artificial Intelligence* (Logika Fuzzy Sugeno). Sistem dirancang tanpa menggunakan perangkat keras fisik IoT, melainkan menggunakan Node-RED sebagai simulator penghasil data telemetri, Python sebagai *AI logic engine*, MQTT sebagai protokol komunikasi, dan Laravel (Filament UI) sebagai *dashboard* pemantauan terpusat.

## 2. Tujuan Sistem

### Tujuan Non-Teknis

* **Penyelesaian Masalah Nyata:** Mencegah terjadinya kerusakan perangkat keras dan *downtime* layanan akibat *overheating* di ruang server melalui otomatisasi respon pendinginan.
* **Efisiensi Energi:** Menyesuaikan suhu AC secara dinamis berdasarkan beban komputasi dan suhu ruang, menghindari penggunaan AC pada daya maksimal secara terus-menerus saat server dalam keadaan diam (*idle*).
* **Pemenuhan Kualifikasi Akademik:** Menyelesaikan tugas besar dengan fokus pada pemahaman konsep integrasi sistem, implementasi AI secara mandiri, dan presentasi fungsional yang berjalan 100% tanpa risiko kegagalan perangkat keras.

### Tujuan Teknis

* **Implementasi AI Mandiri:** Membangun algoritma Logika Fuzzy (Metode Sugeno) dari nol menggunakan Python murni tanpa bergantung pada *library* *Machine Learning* yang bersifat *black-box*.
* **Integrasi Sistem Real-Time:** Menghubungkan tiga ekosistem berbeda (Node-RED, Python, PHP/Laravel) dalam satu alur komunikasi data berlatensi rendah menggunakan protokol MQTT.
* **Visualisasi Data Administratif:** Menyediakan antarmuka pemantauan kelas *enterprise* yang menampilkan metrik sensor secara interaktif dan log historis yang rapi.

---

## 3. Kebutuhan Sistem (System Requirements)

### Kebutuhan Fungsional (Functional Requirements)

1. **Simulator (Node-RED):** Harus memiliki antarmuka untuk menghasilkan dan memanipulasi data "Suhu Ruangan" (15°C - 40°C) dan "Beban CPU Server" (0% - 100%) secara manual oleh pengguna (simulator).
2. **Engine AI (Python):** Harus mampu membaca data dari *broker* MQTT secara *real-time*, mengeksekusi perhitungan Fuzzifikasi, Evaluasi Aturan (Inferensi), dan Defuzzifikasi Sugeno, lalu menghasilkan satu nilai pasti "Target Suhu AC".
3. **Dashboard Pemantauan (Laravel + Filament):** Harus menampilkan indikator suhu ruangan saat ini, beban server saat ini, status/suhu AC yang diatur oleh AI, serta grafik pergerakan suhu dari waktu ke waktu.
4. **Sistem Log:** Setiap instruksi perubahan suhu dari AI harus dicatat (*logging*) ke dalam *database* beserta *timestamp*-nya.

### Kebutuhan Jaringan & Arsitektur Keamanan (Strict Guidelines)

Untuk menjaga integritas pengembangan dan menyimulasikan keamanan infrastruktur jaringan yang ketat:

* **Sentralisasi Gateway:** Server pusat (yang menjalankan MQTT Broker dan Laravel Web Server) dikonfigurasi sebagai *gateway* utama. Seluruh lalu lintas komunikasi data antar-komponen wajib melewati *gateway* ini.
* **Isolasi Akses Lingkungan (Environment Restriction):** Akses jaringan untuk pengembang dibatasi secara ketat. Pengembang (*developer*) dilarang keras mengakses *site*, *port*, atau lingkungan kerja selain bagiannya sendiri. (Contoh: Pengembang Node-RED tidak memiliki akses ke lingkungan pengembangan Python/Laravel).

### Kebutuhan Teknologi (Tech Stack)

* **Data Generator:** Node-RED.
* **Message Broker:** Eclipse Mosquitto (MQTT).
* **AI Backend:** Python 3.x (Skrip murni, pustaka standar + `paho-mqtt`).
* **Web Dashboard:** PHP, Laravel 11.x, Filament UI v3.
* **Database:** MySQL / SQLite (untuk menyimpan log simulasi).

---

## 4. Prosedur Pengembangan & Alur Kerja (Development Workflow)

Dokumen ini berfungsi sebagai instruksi mutlak bagi *developer* dan **AI Coding Agent**. Setiap perubahan pada basis kode harus mematuhi standar prosedur berikut:

### 4.1. Instruksi Kerja untuk AI Coding Agent

Bagi AI Agent yang membaca dokumen ini, bertindaklah sebagai asisten *engineering* penuh. Saat diminta membuat kode atau modul baru:

1. **Pahami Konteks Batasan:** Jangan pernah menambahkan *library external* untuk algoritma Fuzzy Logic di Python (harus *hardcoded* Sugeno). Gunakan Filament untuk semua elemen UI administratif di Laravel.
2. **Modularitas:** Buat kode yang terisolasi. Skrip Python harus berupa *daemon*/*service* mandiri.
3. **Sertakan Komentar Penjelas:** Karena proyek ini untuk keperluan evaluasi/presentasi akademik, sertakan penjelasan singkat pada fungsi-fungsi matematika Logika Fuzzy agar mudah dijelaskan oleh pengembang utama.

### 4.2. Standar Manajemen Versi (Git Workflow)

Setiap selesai menyelesaikan satu fungsi atau *task*, perubahan wajib disimpan (*commit*) dengan format **Conventional Commits** agar riwayat pengembangan mudah dilacak.

* `feat: [Deskripsi]` -> Untuk penambahan fitur baru (misal: `feat: add sugeno defuzzification function`).
* `fix: [Deskripsi]` -> Untuk perbaikan *bug* (misal: `fix: mqtt connection drop on python script`).
* `docs: [Deskripsi]` -> Untuk pembaruan dokumentasi.
* `chore: [Deskripsi]` -> Untuk konfigurasi sistem, pembaruan dependensi, atau penyesuaian repositori (misal: `chore: update laravel filament dependencies`).

### 4.3. Pembaruan Dokumen Progres (Progress Tracking)

Terdapat satu file khusus bernama `PROGRESS.md` (atau di bagian bawah README). Setiap *commit* besar atau pencapaian (*milestone*) selesai, file progres wajib diperbarui.

**Format Pencatatan Progres:**

* `[x] TANGGAL - NAMA TASK - STATUS`
* *Contoh:* `[x] 2026-06-26 - Inisiasi Repositori dan Penyusunan System Design - Selesai.`
* *Contoh:* `[ ] TODO - Pembuatan fungsi Fuzzifikasi di Python - Menunggu Eksekusi.`



### 4.4. Prosedur Pengujian (Testing)

Sebelum digabungkan (*merge*) atau dianggap selesai, setiap komponen harus diuji secara lokal:

1. Beri injeksi nilai ekstrem pada Node-RED (misal: Suhu 40°C, Beban 100%).
2. Verifikasi *console log* Python memastikan *output* AC menunjukkan nilai batas bawah (misal 16°C).
3. Verifikasi antarmuka Laravel memastikan pembaruan metrik terjadi di bawah 2 detik tanpa harus melakukan *refresh* halaman secara manual (pertimbangkan penggunaan *polling* ringan atau WebSocket/Laravel Reverb jika memungkinkan).