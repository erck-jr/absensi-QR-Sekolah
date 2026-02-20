# Sistem Absensi QR Code Sekolah & WhatsApp Gateway v1.0

Aplikasi absensi modern berbasis web yang dirancang khusus untuk lingkungan sekolah. Memudahkan pencatatan kehadiran siswa dan guru menggunakan teknologi QR Code dengan integrasi notifikasi WhatsApp secara real-time kepada orang tua.

## 🚀 Fitur Unggulan

### 1. Absensi & Scanning Cepat
- **Scan QR Code**: Siswa dan guru cukup melakukan scan kartu identitas pada perangkat yang disediakan sekolah.
- **Mode Dual (Masuk & Pulang)**: Sistem secara otomatis membedakan waktu check-in (masuk) dan check-out (pulang).
- **Validasi Shift**: Mencegah absensi di luar jam yang ditentukan dan mendeteksi keterlambatan secara otomatis.

### 2. Notifikasi WhatsApp Real-time (OneSender)
- **Pesan Otomatis**: Orang tua langsung menerima pesan WhatsApp saat siswa melakukan scan masuk atau pulang.
- **Informasi Lengkap**: Pesan mencakup Nama, Jam Scan, Status (Tepat Waktu/Terlambat/Pulang Cepat), dan Tanggal.
- **Queue System**: Mengirim pesan di latar belakang (background job) agar proses scanning tetap cepat tanpa loading lama.

### 3. Generator ID Card Otomatis
- **Desain Template**: Menggunakan template kartu yang dapat disesuaikan.
- **Auto-Generate**: Membuat kartu pelajar/guru lengkap dengan Foto, Nama, NIS/NUPTK, dan QR Code unik.
- **Download Massal**: Fitur unduh semua kartu dalam format .ZIP per kelas untuk kemudahan pencetakan.

### 4. Manajemen Data & Laporan
- **Laporan Harian & Bulanan**: Rekapitulasi kehadiran yang detail.
- **Matriks Kehadiran**: Tampilan visual kehadiran satu bulan penuh dalam satu layar.
- **Export Excel**: Unduh laporan kehadiran untuk kebutuhan administrasi sekolah.
- **Manajemen Guru & Siswa**: Import data siswa via Excel dan manajemen data induk yang mudah.

## 🛠 Teknologi yang Digunakan

- **Backend**: Laravel 12 (PHP Framework)
- **Frontend**: Livewire, Blade Templating, Alpine.js
- **Styling**: Tailwind CSS, Flowbite
- **Database**: MySQL
- **Dependensi Utama**:
  - `simplesoftwareio/simple-qrcode`: Generator QR Code.
  - `intervention/image`: Manipulasi gambar untuk ID Card.
  - `maatwebsite/excel`: Import/Export data Excel.
  - `OneSender`: Provider API WhatsApp Gateway.

## 🔄 Alur Kerja Sistem (Business Process)

1.  **Persiapan Data (Admin/Operator)**
    -   Admin menginput data Tahun Ajaran, Kelas, Shift, dan Hari Libur.
    -   Admin menginput/import data Siswa dan Guru.
    -   Admin mengatur API Token WhatsApp Gateway.

2.  **Pencetakan Kartu**
    -   Admin mengakses menu Generator ID Card.
    -   Sistem men-generate kartu ID yang berisi QR Code unik untuk setiap pengguna.
    -   Kartu dicetak dan dibagikan kepada Siswa/Guru.

3.  **Proses Absensi (Harian)**
    -   Admin/Piket membuka halaman **Scanner** di perangkat sekolah (Tablet/PC + Webcam/Scanner).
    -   Siswa/Guru melakukan scan kartu ID ke kamera/alat scan.
    -   **Sistem Memproses**:
        -   Mengecek validitas QR Code.
        -   Mengecek status Shift (Jam Masuk/Pulang).
        -   Mencatat waktu kehadiran.
        -   Menandai status (Tepat Waktu / Terlambat / Pulang Cepat).
    -   **Notifikasi**: Sistem mengirim perintah ke Job Queue untuk mengirim pesan WA ke nomor HP yang terdaftar.

4.  **Pelaporan**
    -   Data kehadiran tersimpan otomatis.
    -   Admin dapat memantau rekap harian di Dashboard.
    -   Wali kelas/Kepala sekolah dapat menarik laporan absensi bulanan dalam format Excel.

## ⚙️ Instalasi & Konfigurasi

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di komputer lokal (Localhost):

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL Database

### Langkah Instalasi

1.  **Clone Repository**
    ```bash
    git clone https://github.com/username/absensi-qr-sekolah.git
    cd absensi-qr-sekolah
    ```

2.  **Install Dependensi PHP & Asset**
    ```bash
    composer install
    npm install
    ```

3.  **Konfigurasi Environment**
    -   Duplikat file `.env.example` menjadi `.env`.
    -   Sesuaikan konfigurasi database:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nama_database_anda
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4.  **Generate Key & Migrasi Database**
    ```bash
    php artisan key:generate
    php artisan migrate --seed
    ```
    *(Gunakan `--seed` untuk mengisi data awal / akun admin default)*

5.  **Link Storage (Wajib untuk Foto & ID Card)**
    ```bash
    php artisan storage:link
    ```

6.  **Build Frontend Asset**
    ```bash
    npm run build
    ```

### Menjalankan Aplikasi

Untuk menjalankan aplikasi secara penuh, Anda perlu menjalankan **dua terminal**:

**Terminal 1 (Server Web):**
```bash
php artisan serve
```

**Terminal 2 (Queue Worker - Untuk WhatsApp):**
Agar notifikasi WhatsApp terkirim, worker harus berjalan.
```bash
php artisan queue:work
```
*Note: Tanpa menjalankan queue work, pesan WA hanya akan antri di database dan tidak terkirim.*

## 🔑 Akun Default (Seeder)
- **Email**: admin@admin.com
- **Password**: password

---
Dibuat menggunakan Laravel.
