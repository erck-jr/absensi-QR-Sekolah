# Peta Arsitektur & Indeks Komponen Proyek (Project Maps)

> **Terakhir Diperbarui:** 19 November 2026 (Fitur Penanda Hari Libur Akhir Pekan / Weekend)

---

## A. AI Agent Notice

> [!IMPORTANT]
> **WAJIB DIBACA SEBELUM BERTINDAK (MANDATORY AGENT DIRECTIVE)**
> 1. **Baca Dokumen Ini Terlebih Dahulu:** Seluruh AI Agent yang bekerja pada repositori ini **WAJIB** membaca dan memahami isi file ini secara menyeluruh sebelum mengeksekusi instruksi pengguna, menjawab pertanyaan teknis, melakukan refactoring, debugging, maupun menambahkan fitur baru. Hal ini bertujuan untuk mencegah pemindaian direktori berulang yang boros konteks.
> 2. **Kewajiban Sinkronisasi Berkelanjutan (Continuous Update):** Setiap kali terjadi modifikasi yang memengaruhi struktur aplikasi—seperti penambahan/perubahan model, migrasi skema database, penambahan endpoint rute, method controller baru, service, console command, helper, job antrian, view blade, maupun dependensi paket—agent **WAJIB** memperbarui file ini pada sesi/turn yang sama.
> 3. **Pembaruan Tanggal:** Pastikan tanggal `Terakhir Diperbarui` di header dokumen ini selalu disesuaikan setiap kali dokumen diperbarui.

---

## B. Ringkasan Project

**Sistem Absensi QR Code Sekolah & WhatsApp Gateway v1.0** adalah platform manajemen presensi berbasis web modern yang dibangun dengan framework **Laravel 12**. Sistem ini memadukan pemindaian kartu identitas digital ber-QR Code dengan notifikasi otomatis melalui gateway WhatsApp secara instan kepada wali murid atau guru.

### Fitur Kunci:
- **Presensi Cepat Dual Mode:** Pemindaian QR Code interaktif menggunakan kamera webcam atau barcode scanner USB untuk Check-In (Masuk) dan Check-Out (Pulang), dilengkapi validasi shift dan deteksi keterlambatan otomatis.
- **Notifikasi WhatsApp Real-time (OneSender API):** Notifikasi otomatis ke nomor orang tua/wali dan staf melalui background job queue dengan dukungan Anti-Ban Spintax, rate limiting (35 pesan/menit), pencegahan duplikasi, dan batas kedaluwarsa toleransi 15 menit.
- **Generator ID Card Otomatis:** Perancangan dan pembuatan kartu pelajar dan kartu pegawai (depan & belakang) menggunakan Intervention Image, lengkap dengan logo sekolah di tengah QR Code, serta fitur download massal dalam format `.zip` per rombel kelas.
- **Laporan & Matriks Kehadiran:** Rekapitulasi presensi harian dan matriks kehadiran bulanan satu layar penuh, dilengkapi fitur ubah presensi inline serta ekspor laporan ke format Microsoft Excel (.xlsx).
- **Manajemen Akademik & Buku Tamu:** Pengelolaan jenjang kelas, promosi kenaikan kelas tahun ajaran baru, pembersihan data presensi lama, pencatatan buku tamu (Guest Book), dan sistem backup/restore database SQL native mandiri.

> 📖 **Dokumentasi Lengkap:** Untuk panduan instalasi, konfigurasi server, panduan queue worker, dan alur operasional lengkap, silakan baca [README.md](file:///d:/Webproject/absensi_qr_sekolah/README.md).

---

## C. Dependencies

### 1. Backend Dependencies (`composer.json`)
*Environment Runtime: PHP ^8.3*

| Paket / Dependensi | Versi | Fungsi & Kegunaan |
| :--- | :--- | :--- |
| `laravel/framework` | `^12.0` | Core framework backend MVC & routing sistem |
| `intervention/image` | `^3.11` | Manipulasi gambar untuk ID Card Generator (Driver GD) |
| `simplesoftwareio/simple-qrcode` | `^4.2` | Generator barcode QR Code unik berbasis SVG/PNG |
| `maatwebsite/excel` | `^3.1` | Import dan export file Excel spreadsheet data siswa & laporan |
| `laravel/tinker` | `^2.10.1` | Shell REPL interaktif untuk testing dan manipulasi Eloquent |

#### Backend Dev Dependencies:
- `laravel/breeze` (`^2.3`): Scaffolding sistem autentikasi.
- `laravel/pint` (`^1.24`): PHP code style fixer & linter.
- `laravel/sail` (`^1.41`): Docker environment management.
- `laravel/pail` (`^1.2.2`): Real-time application log tailing CLI.
- `laravel-lang/common` (`^6.7`): Paket lokalisasi bahasa (Indonesia).
- `fakerphp/faker` (`^1.23`), `mockery/mockery` (`^1.6`), `nunomaduro/collision` (`^8.6`), `phpunit/phpunit` (`^11.5.3`): Framework unit testing dan error handling.

### 2. Frontend Dependencies (`package.json`)
*Bundler: Vite ^7.0.7*

| Paket / Dependensi | Versi | Fungsi & Kegunaan |
| :--- | :--- | :--- |
| `tailwindcss` | `^4.2.0` | Utility-first styling CSS framework |
| `@tailwindcss/vite` | `^4.2.0` | Plugin integrasi resmi Tailwind CSS v4 untuk Vite |
| `flowbite` | `^4.0.1` | Komponen antarmuka interaktif siap pakai berbasis Tailwind |
| `alpinejs` | `^3.4.2` | Framework JavaScript reaktif ringan untuk DOM manipulation & modal |
| `apexcharts` | `^5.6.0` | Library charting interaktif untuk grafik visualisasi kehadiran |
| `simple-datatables` | `^10.2.0` | Library tabel data interaktif dengan pencarian dan pagination |
| `axios` | `^1.11.0` | HTTP Client promise-based untuk AJAX request scanner & generator |
| `concurrently` | `^9.0.1` | Runner simultan multi-command terminal (server, queue, vite) |
| `laravel-vite-plugin` | `^2.0.0` | Integrasi asset bundling Laravel dengan Vite |
| `autoprefixer` (`^10.4.24`), `postcss` (`^8.5.6`) | Tooling post-processing CSS |

---

## D. Database dan Models

### 1. Daftar Tabel & Model Eloquent

#### a. `users` -> [User.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/User.php)
- **Fungsi:** Pengelolaan akun pengguna yang dapat login ke dashboard administratif.
- **Traits:** `HasFactory`, `Notifiable`
- **Kolom Penting:** `id`, `name`, `email` (unique), `password`, `role` (enum/string: `admin` atau `operator`), `email_verified_at`, `remember_token`, `timestamps`.
- **Relasi:** Induk autentikasi.

#### b. `levels` -> [Level.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/Level.php)
- **Fungsi:** Master tingkat/jenjang pendidikan (e.g. Kelas 7, 8, 9 atau X, XI, XII).
- **Traits:** `HasFactory`
- **Kolom Penting:** `id`, `name` (unique), `level_order` (integer urutan jenjang kelas), `timestamps`.
- **Relasi:**
  - `hasMany(SchoolClass::class, 'level_id')` -> Daftar rombel pada jenjang terkait.

#### c. `classes` -> [SchoolClass.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/SchoolClass.php)
- **Fungsi:** Master rombongan belajar / kelas siswa (e.g. "X RPL 1"). Catatan: tabel database bernama `classes`.
- **Traits:** `HasFactory`
- **Kolom Penting:** `id`, `level_id` (foreign key `levels.id`), `name` (unique), `timestamps`.
- **Relasi:**
  - `belongsTo(Level::class, 'level_id')` -> Jenjang dari kelas ini.
  - `hasMany(Student::class, 'class_id')` -> Seluruh siswa terdaftar di kelas ini.

#### d. `students` -> [Student.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/Student.php)
- **Fungsi:** Data induk siswa yang terdaftar dalam sistem presensi.
- **Traits:** `HasFactory`, `SoftDeletes`
- **Kolom Penting:** `id`, `class_id` (foreign key `classes.id`), `nis` (unique), `name`, `gender` (enum `L`,`P`), `photo` (nullable), `phone` (nomor WA wali siswa), `unique_code` (UUID unik untuk QR Code), `deleted_at`, `timestamps`.
- **Relasi:**
  - `belongsTo(SchoolClass::class, 'class_id')` (via `classRoom()`) -> Kelas asal siswa.
  - `hasMany(AttendanceStudent::class, 'student_id')` (via `attendances()`) -> Riwayat presensi siswa.

#### e. `teachers` -> [Teacher.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/Teacher.php)
- **Fungsi:** Data induk guru dan staf kependidikan.
- **Traits:** `HasFactory`
- **Kolom Penting:** `id`, `nuptk` (unique), `name`, `gender` (enum `L`,`P`), `photo` (nullable), `phone` (nomor WA guru), `unique_code` (UUID unik untuk QR Code), `timestamps`.
- **Relasi:**
  - `hasMany(AttendanceTeacher::class, 'teacher_id')` (via `attendances()`) -> Riwayat presensi guru.

#### f. `shifts` -> [Shift.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/Shift.php)
- **Fungsi:** Konfigurasi shift jam kerja/sekolah, toleransi menit keterlambatan, dan jam kepulangan.
- **Traits:** `HasFactory`
- **Kolom Penting:** `id`, `name`, `check_in_time` (time), `check_out_time` (time), `late_check_in_minute` (int), `is_active` (boolean), `timestamps`.
- **Relasi:**
  - `hasMany(AttendanceStudent::class)`
  - `hasMany(AttendanceTeacher::class)`

#### g. `attendance_codes` -> [AttendanceCode.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/AttendanceCode.php)
- **Fungsi:** Master referensi kode kehadiran (Hadir, Sakit, Izin, Alpha).
- **Traits:** `HasFactory`
- **Kolom Penting:** `id`, `name` (unique), `timestamps`.
- **Relasi:**
  - `hasMany(AttendanceStudent::class, 'attendance_id')`
  - `hasMany(AttendanceTeacher::class, 'attendance_id')`

#### h. `attendance_students` -> [AttendanceStudent.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/AttendanceStudent.php)
- **Fungsi:** Transaksi presensi harian siswa (masuk, pulang, status terlambat, catatan).
- **Kolom Penting:** `id`, `student_id` (FK `students`), `shift_id` (FK `shifts`), `attendance_id` (FK `attendance_codes`), `dates` (date), `check_in` (time, nullable), `check_out` (time, nullable), `is_late` (boolean), `note` (text, nullable), `timestamps`.
- **Casts:** `dates => date`, `is_late => boolean`.
- **Relasi:**
  - `belongsTo(Student::class)`
  - `belongsTo(Shift::class)`
  - `belongsTo(AttendanceCode::class, 'attendance_id')`

#### i. `attendance_teachers` -> [AttendanceTeacher.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/AttendanceTeacher.php)
- **Fungsi:** Transaksi presensi harian guru dan staf sekolah.
- **Kolom Penting:** `id`, `teacher_id` (FK `teachers`), `shift_id` (FK `shifts`), `attendance_id` (FK `attendance_codes`), `dates` (date), `check_in` (time, nullable), `check_out` (time, nullable), `is_late` (boolean), `note` (text, nullable), `timestamps`.
- **Casts:** `dates => date`, `is_late => boolean`.
- **Accessor:** `getWorkDurationAttribute()` (menghitung selisih jam check-in dan check-out).
- **Relasi:**
  - `belongsTo(Teacher::class)`
  - `belongsTo(Shift::class)`
  - `belongsTo(AttendanceCode::class, 'attendance_id')`

#### j. `holidays` -> [Holiday.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/Holiday.php)
- **Fungsi:** Kalender hari libur sekolah (single date maupun rentang libur).
- **Kolom Penting:** `id`, `dates` (date), `info` (keterangan libur), `group_id` (nullable, identifier grup rentang libur), `timestamps`.
- **Casts:** `dates => date`.

#### k. `guests` -> [Guest.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/Guest.php)
- **Fungsi:** Pencatatan buku tamu sekolah (pengunjung, instansi, orang yang ditemui, keperluan, check-in, dan check-out).
- **Kolom Penting:** `id`, `name`, `origin` (asal instansi), `meet_with` (bertemu siapa), `necessity` (keperluan), `phone` (no kontak), `check_in` (datetime), `check_out` (datetime, nullable), `timestamps`.
- **Casts:** `check_in => datetime`, `check_out => datetime`.

#### l. `card_templates` -> [CardTemplate.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/CardTemplate.php)
- **Fungsi:** Menyimpan nama file template gambar background ID Card dan opsi caching gambar.
- **Kolom Penting:** `id`, `key` (unique: `student_front`, `teacher_front`, `teacher_back`), `file_name` (nama file di public/templates_card), `cached_idcard` (boolean), `timestamps`.
- **Casts:** `cached_idcard => boolean`.

#### m. `message_templates` -> [MessageTemplate.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/MessageTemplate.php)
- **Fungsi:** Template pesan notifikasi WhatsApp untuk berbagai event absensi.
- **Kolom Penting:** `id`, `key` (unique: `student_checkin`, `student_checkout`, `student_late_checkin`, `student_early_checkout`, `teacher_checkin`, `teacher_checkout`), `content` (text format pesan dengan token dan spintax), `timestamps`.
- **Accessor:** `getLabelAttribute()`.

#### n. `wa_gateways` -> [WaGateway.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/WaGateway.php)
- **Fungsi:** Konfigurasi koneksi WhatsApp Gateway pihak ketiga (OneSender).
- **Kolom Penting:** `id`, `name`, `api_url`, `api_token`, `is_active` (boolean), `timestamps`.
- **Casts:** `is_active => boolean`.
- **Relasi:**
  - `hasMany(WaLog::class, 'gateway_id')`

#### o. `wa_logs` -> [WaLog.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/WaLog.php)
- **Fungsi:** Audit log riwayat pengiriman notifikasi WhatsApp.
- **Kolom Penting:** `id`, `attendance_id` (ID presensi), `attendance_type` (`student`/`teacher`), `gateway_id` (FK `wa_gateways`), `recipient_number`, `message_content` (text), `status` (enum: `pending`, `sent`, `failed`, `expired`), `error_details` (nullable), `timestamps`.
- **Relasi:**
  - `belongsTo(WaGateway::class, 'gateway_id')`

#### p. `settings` -> [Setting.php](file:///d:/Webproject/absensi_qr_sekolah/app/Models/Setting.php)
- **Fungsi:** Pasangan key-value konfigurasi dinamis aplikasi (nama aplikasi, teks sambutan, logo, dll).
- **Kolom Penting:** `id`, `key` (unique), `value` (text nullable), `group` (nullable), `timestamps`.

---

### 2. File Migrasi Database (`database/migrations`)

| Nama File Migrasi | Aksi Skema / Keterangan |
| :--- | :--- |
| `0001_01_01_000000_create_users_table.php` | Membuat tabel `users`, `password_reset_tokens`, dan `sessions` |
| `0001_01_01_000001_create_cache_table.php` | Membuat tabel caching Laravel (`cache`, `cache_locks`) |
| `0001_01_01_000002_create_jobs_table.php` | Membuat tabel background antrian queue (`jobs`, `job_batches`, `failed_jobs`) |
| `2026_02_16_075027_create_settings_table.php` | Membuat tabel pasangan key-value konfigurasi `settings` |
| `2026_02_16_075028_create_levels_table.php` | Membuat tabel master jenjang tingkat `levels` |
| `2026_02_16_075028_create_wa_gateways_table.php` | Membuat tabel gateway WhatsApp `wa_gateways` |
| `2026_02_16_075029_create_classes_table.php` | Membuat tabel master rombel kelas `classes` dengan foreign key ke `levels` |
| `2026_02_16_075030_create_students_table.php` | Membuat tabel master data siswa `students` |
| `2026_02_16_075039_create_shifts_table.php` | Membuat tabel shift presensi `shifts` |
| `2026_02_16_075039_create_teachers_table.php` | Membuat tabel master guru `teachers` |
| `2026_02_16_075040_create_attendance_codes_table.php` | Membuat tabel master kode status kehadiran `attendance_codes` |
| `2026_02_16_075041_create_holidays_table.php` | Membuat tabel kalender hari libur `holidays` |
| `2026_02_16_075042_create_card_templates_table.php` | Membuat tabel referensi background template ID card `card_templates` |
| `2026_02_16_075042_create_message_templates_table.php` | Membuat tabel format pesan WhatsApp `message_templates` |
| `2026_02_16_075054_create_attendance_students_table.php` | Membuat tabel transaksi presensi siswa `attendance_students` |
| `2026_02_16_075055_create_attendance_teachers_table.php` | Membuat tabel transaksi presensi guru `attendance_teachers` |
| `2026_02_16_075056_create_guests_table.php` | Membuat tabel buku tamu `guests` |
| `2026_02_16_075056_create_wa_logs_table.php` | Membuat tabel audit pengiriman WhatsApp `wa_logs` |
| `2026_02_18_024950_add_photo_to_students_and_teachers_table.php` | Menambahkan field foto `photo` pada tabel `students` dan `teachers` |
| `2026_02_18_053148_add_is_active_to_shifts_table.php` | Menambahkan switch `is_active` pada tabel `shifts` |
| `2026_02_18_055000_add_group_id_to_holidays_table.php` | Menambahkan field `group_id` pada `holidays` untuk identifikasi rentang libur |
| `2026_02_18_062333_add_note_to_attendance_tables.php` | Menambahkan kolom `note` (keterangan) pada tabel absensi siswa & guru |
| `2026_02_19_023946_seed_additional_message_templates.php` | Seeding awal 6 template pesan bawaan (check-in/check-out/late/early) |
| `2026_02_19_164223_add_meet_with_to_guests_table.php` | Menambahkan kolom `meet_with` pada tabel `guests` |
| `2026_05_27_000001_add_level_order_to_levels_table.php` | Menambahkan kolom `level_order` pada `levels` untuk urutan jenjang |
| `2026_05_27_000002_add_soft_deletes_to_students_table.php` | Menambahkan kolom `deleted_at` (soft deletes) pada tabel `students` |
| `2026_08_27_085321_add_cached_idcard_to_card_templates_table.php` | Menambahkan boolean `cached_idcard` pada tabel `card_templates` |
| `2026_10_02_131736_make_check_in_nullable_on_attendance_tables.php` | Mengubah kolom `check_in` menjadi nullable pada tabel presensi |

---

### 3. Database Seeders (`database/seeders`)

- [DatabaseSeeder.php](file:///d:/Webproject/absensi_qr_sekolah/database/seeders/DatabaseSeeder.php): Main runner yang mengeksekusi seeder turunan secara berurutan.
- [AdminUserSeeder.php](file:///d:/Webproject/absensi_qr_sekolah/database/seeders/AdminUserSeeder.php): Membuat user awal:
  - Admin: `admin@admin.com` (password: `password`, role: `admin`)
  - Operator: `operator@operator.com` (password: `password`, role: `operator`)
- [AttendanceCodeSeeder.php](file:///d:/Webproject/absensi_qr_sekolah/database/seeders/AttendanceCodeSeeder.php): Mengisi master status kehadiran standar: `Hadir`, `Sakit`, `Izin`, dan `Alpha`.
- [ShiftSeeder.php](file:///d:/Webproject/absensi_qr_sekolah/database/seeders/ShiftSeeder.php): Mengisi shift awal `Pagi Masuk` (07:00:00 - 12:00:00, toleransi 15 menit).
- [SettingSeeder.php](file:///d:/Webproject/absensi_qr_sekolah/database/seeders/SettingSeeder.php): Menginisialisasi pengaturan default aplikasi (`app_name`, `app_description`, `welcome_text`, dll).

---

## E. Controllers (`app/Http/Controllers`)

| Controller | Lokasi File | Deskripsi & Fungsi |
| :--- | :--- | :--- |
| `AcademicYearController` | [AcademicYearController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/AcademicYearController.php) | Mengelola transisi tahun ajaran baru, promosi kenaikan kelas antar jenjang, pembersihan log absensi lama (cleanup), pembuatan backup native MySQL (.sql), unduh backup, restore data buku tamu, dan hapus file backup. |
| `CardGeneratorController` | [CardGeneratorController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/CardGeneratorController.php) | Memfasilitasi antarmuka generator kartu pelajar & guru. Menangani fetch data per kelas via AJAX, generate kartu individual/massal, dan pengemasan kartu siap cetak ke format file `.zip`. |
| `CardTemplateController` | [CardTemplateController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/CardTemplateController.php) | Mengelola upload file template kartu background dan toggle status cache kartu ID. |
| `DashboardController` | [DashboardController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/DashboardController.php) | Menyediakan statistik analitik di halaman dashboard: total siswa/guru hadir, izin, sakit, alpa hari ini (otomatis 0 jika hari ini jatuh pada hari libur akhir pekan atau libur nasional), chart mingguan, dan status koneksi WA Gateway. |
| `GuestController` | [GuestController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/GuestController.php) | Mengelola buku tamu digital (pencatatan kunjungan, nama, instansi, keperluan, orang yang dituju, jam masuk, dan jam keluar). |
| `HolidayController` | [HolidayController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/HolidayController.php) | CRUD kalender hari libur sekolah (single date maupun range dates) agar sistem mengecualikan perhitungan ketidakhadiran siswa pada tanggal tersebut. |
| `LevelController` | [LevelController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/LevelController.php) | CRUD jenjang/tingkat pendidikan dan pengaturan urutan `level_order` untuk kenaikan kelas otomatis. |
| `MessageTemplateController` | [MessageTemplateController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/MessageTemplateController.php) | Mengelola format pesan WhatsApp Gateway untuk berbagai kondisi absensi (masuk, pulang, terlambat, pulang awal) dengan dukungan token dinamis dan spintax. |
| `ProfileController` | [ProfileController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/ProfileController.php) | Mengelola profil akun pengguna yang sedang login (ubah nama, email, ubah password, dan opsi hapus akun). |
| `PublicAttendanceController` | [PublicAttendanceController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/PublicAttendanceController.php) | Menangani pencarian riwayat presensi mandiri oleh siswa atau wali murid di halaman publik tanpa perlu login. Menampilkan status "Libur Akhir Pekan" jika tanggal yang dicek jatuh pada hari weekend yang ditetapkan. |
| `ReportController` | [ReportController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/ReportController.php) | Menampilkan matriks rekap kehadiran bulanan dan harian siswa & guru, validasi hari libur akhir pekan dinamis (Jumat, Sabtu, Minggu), pencegahan akses rekap harian pada hari weekend dengan pesan informatif, fitur inline update absensi, serta ekspor laporan ke file Excel. |
| `ScannerController` | [ScannerController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/ScannerController.php) | Halaman presensi interaktif scanner: menerima scan QR Code siswa/guru, validasi shift, membedakan jam masuk/pulang, mendeteksi keterlambatan, menyimpan transaksi presensi, dan men-dispatch job `SendAttendanceWA`. |
| `SchoolClassController` | [SchoolClassController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/SchoolClassController.php) | CRUD master data rombel kelas serta asosiasinya ke jenjang `Level`. |
| `SettingController` | [SettingController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/SettingController.php) | Mengelola konfigurasi umum aplikasi (nama instansi, logo, deskripsi, favicon, sambutan, serta penanda hari libur akhir pekan / weekend: Jumat & Sabtu). |
| `ShiftController` | [ShiftController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/ShiftController.php) | CRUD jam operasional shift (jam masuk, toleransi keterlambatan, jam pulang) serta tombol toggle aktif/nonaktif. |
| `StudentController` | [StudentController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/StudentController.php) | CRUD data siswa, auto-generate UUID QR Code, unggah foto, download template Excel, proses import Excel, dan generate kartu per siswa. |
| `TeacherController` | [TeacherController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/TeacherController.php) | CRUD data guru & staf, auto-generate UUID QR Code, unggah foto, dan pembuatan kartu pegawai (depan & belakang). |
| `UserController` | [UserController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/UserController.php) | CRUD akun sistem untuk Admin dan Operator beserta manajemen hak akses password. |
| `WaGatewayController` | [WaGatewayController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/WaGatewayController.php) | Konfigurasi endpoint OneSender API, toggle status aktif gateway, pemrosesan antrian manual, retry queue, dan endpoint cron job external. |
| `WaLogController` | [WaLogController.php](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/WaLogController.php) | Menampilkan audit log pengiriman pesan WhatsApp, filter tanggal/status, ekspor log ke Excel, dan pembersihan log lama. |
| *Auth Controllers* | [app/Http/Controllers/Auth/](file:///d:/Webproject/absensi_qr_sekolah/app/Http/Controllers/Auth) | Paket controller autentikasi Breeze: login (`AuthenticatedSessionController`), reset password (`PasswordResetLinkController`, `NewPasswordController`), konfirmasi password (`ConfirmablePasswordController`), update password (`PasswordController`), dan verifikasi email (`VerifyEmailController`). |

---

## F. Services (`app/Services`)

| Service | Lokasi File | Deskripsi & Fungsi |
| :--- | :--- | :--- |
| `BackupService` | [BackupService.php](file:///d:/Webproject/absensi_qr_sekolah/app/Services/BackupService.php) | Service native dump SQL MySQL tanpa dependensi utility luar. Menyediakan method `backup()` (dump struktur DDL & data INSERT), `backupFull()` (backup seluruh database), `backupGuests()` (backup khusus tabel buku tamu), dan `restoreGuests()` (merestore tabel tamu dari file backup SQL). |
| `IdCardService` | [IdCardService.php](file:///d:/Webproject/absensi_qr_sekolah/app/Services/IdCardService.php) | Service manipulasi gambar kartu identitas berbasis `Intervention\Image` dan `SimpleSoftwareIO\QrCode`. Menyediakan method `generateStudentCard()` (merender kartu pelajar), `generateTeacherCard()` (merender kartu depan berfoto & belakang ber-QR untuk guru), `formatShortName()` (pemendekan nama otomatis agar proporsional pada kartu), dan `sanitizeFileName()`. Mendukung bypass rendering jika kartu sudah tercache. |
| `ReportService` | [ReportService.php](file:///d:/Webproject/absensi_qr_sekolah/app/Services/ReportService.php) | Service kalkulasi matriks kehadiran bulanan: memetakan tanggal 1 hingga akhir bulan, mendeteksi hari libur akhir pekan (`is_weekend_day`), mencocokkan kalender libur sekolah (`Holiday`), serta menghitung agregat Hadir (H), Izin (I), Sakit (S), Terlambat (T), dan Alpha (A) tanpa mencatat siswa/guru sebagai alpa pada hari libur akhir pekan. |

> *Catatan:* Untuk seluruh komponen export file spreadsheet Excel laporan dan template, lihat [Section I. Imports dan Exports](file:///d:/Webproject/absensi_qr_sekolah/docs/project_maps.md#i-imports-dan-exports-appimports-dan-appexports).

---

## G. Console (`app/Console`)

| Command / File | Lokasi File | Signature / Perintah | Deskripsi & Fungsi |
| :--- | :--- | :--- | :--- |
| `RetryExpiredWa` | [RetryExpiredWa.php](file:///d:/Webproject/absensi_qr_sekolah/app/Console/Commands/RetryExpiredWa.php) | `php artisan wa:retry-expired {--limit=1000}` | Memproses ulang pengiriman pesan WhatsApp pada `wa_logs` yang berstatus `expired`. Dilengkapi algoritma analisis konten untuk mencegah duplikasi (apabila pesan jenis yang sama sudah pernah berhasil terkirim) dan me-redispatch job `SendAttendanceWA` dengan flag `ignoreExpiration = true`. |
| `routes/console.php` | [console.php](file:///d:/Webproject/absensi_qr_sekolah/routes/console.php) | `php artisan inspire` | Perintah bawaan Laravel untuk menampilkan kutipan motivasi. |

---

## H. Helpers (`app/Helpers`)

| File Helper | Lokasi File | Terdaftar di | Deskripsi & Fungsi |
| :--- | :--- | :--- | :--- |
| `global_helper.php` | [global_helper.php](file:///d:/Webproject/absensi_qr_sekolah/app/Helpers/global_helper.php) | `composer.json` (`autoload.files`) | Menyediakan fungsi helper global:<br>1. `settings($key, $default = null)`: Mengambil konfigurasi dari tabel `settings` dengan caching otomatis 24 jam.<br>2. `get_weekend_days()`: Mengambil daftar hari libur akhir pekan yang aktif (Jumat, Sabtu, dan Minggu).<br>3. `is_weekend_day($date)`: Memeriksa apakah suatu tanggal merupakan hari libur akhir pekan.<br>4. `get_weekend_day_label($date)`: Mengembalikan label huruf kapital hari libur akhir pekan (e.g. `JUMAT`, `SABTU`, `MINGGU`). |

---

## I. Imports dan Exports (`app/Imports` dan `app/Exports`)

### 1. Imports (`app/Imports`)

| Class Import | Lokasi File | Deskripsi & Fungsi |
| :--- | :--- | :--- |
| `StudentImport` | [StudentImport.php](file:///d:/Webproject/absensi_qr_sekolah/app/Imports/StudentImport.php) | Mengimplementasikan `ToModel`, `WithHeadingRow`, dan `WithValidation` (Maatwebsite Excel) untuk import massal data siswa baru dari Excel. Menggunakan method `prepareForValidation()` untuk mengeliminasi baris kosong / ghost row, otomatis memetakan teks nama kelas (`level - nama`) ke `class_id`, membersihkan dan menstandarkan nomor WhatsApp (`formatPhoneNumber`), meng-generate UUID `unique_code` untuk QR Code, serta memvalidasi keunikan NIS siswa. |

### 2. Exports (`app/Exports`)

| Class Export | Lokasi File | Interface / Concerns | Deskripsi & Fungsi |
| :--- | :--- | :--- | :--- |
| `StudentTemplateExport` | [StudentTemplateExport.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/StudentTemplateExport.php) | `WithMultipleSheets` | Export template master file Excel untuk import siswa. Menggabungkan 2 sheet: sheet `Template` ([StudentDataSheet.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/StudentDataSheet.php)) untuk pengisian data dan sheet `Reference` ([ReferenceSheet.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/ReferenceSheet.php)) sebagai sumber data validasi dropdown. |
| `StudentDataSheet` | [StudentDataSheet.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/StudentDataSheet.php) | `WithHeadings`, `WithEvents`, `WithTitle` | Lembar kerja utama template import siswa. Mendefinisikan header kolom (*Nama Lengkap, NIS, Kelas, Jenis Kelamin, No Telepon*) dan menyematkan Excel Data Validation (dropdown list) pada kolom Kelas yang mereferensikan rentang data pada `ReferenceSheet`. |
| `ReferenceSheet` | [ReferenceSheet.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/ReferenceSheet.php) | `FromCollection`, `WithHeadings`, `WithTitle` | Menyediakan sheet referensi berisi daftar seluruh kelas aktif di sekolah (berformat `[Nama Level] - [Nama Kelas]`) yang terikat dengan dropdown validasi pada sheet data import siswa. |
| `StudentDailyExport` | [StudentDailyExport.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/StudentDailyExport.php) | `FromCollection`, `WithHeadings`, `WithMapping`, `ShouldAutoSize` | Mengekspor rekapitulasi presensi harian siswa pada tanggal tertentu ke format Excel, memuat data: No, NIS, Nama Lengkap, Kelas, Jam Masuk, Jam Pulang, Status Kehadiran (Hadir/Izin/Sakit/Alpha/Terlambat), dan Keterangan/Catatan. |
| `StudentMonthlyExport` | [StudentMonthlyExport.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/StudentMonthlyExport.php) | `FromView`, `ShouldAutoSize`, `WithStyles` | Mengekspor matriks rekap kehadiran bulanan siswa satu bulan penuh ke file Excel berbasis render Blade view [student_monthly.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/reports/exports/student_monthly.blade.php). Dilengkapi styling border tabel, penandaan hari Minggu/libur, status harian, dan ringkasan total H/I/S/A/T per siswa. |
| `TeacherDailyExport` | [TeacherDailyExport.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/TeacherDailyExport.php) | `FromCollection`, `WithHeadings`, `WithMapping`, `ShouldAutoSize` | Mengekspor rekapitulasi presensi harian guru & staf kependidikan pada tanggal tertentu ke format Excel, memuat data: No, NUPTK, Nama Guru, Jam Masuk, Jam Pulang, Status Kehadiran, dan Keterangan/Catatan. |
| `TeacherMonthlyExport` | [TeacherMonthlyExport.php](file:///d:/Webproject/absensi_qr_sekolah/app/Exports/TeacherMonthlyExport.php) | `FromView`, `ShouldAutoSize`, `WithStyles` | Mengekspor matriks rekap kehadiran bulanan guru/staf satu bulan penuh ke spreadsheet Excel berbasis render Blade view [teacher_monthly.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/reports/exports/teacher_monthly.blade.php), lengkap dengan styling tabel, kalender kehadiran 1 bulan, dan ringkasan agregat kehadiran. |

---

## J. Jobs (`app/Jobs`)

| Job Class | Lokasi File | Queue / Middleware | Deskripsi & Fungsi |
| :--- | :--- | :--- | :--- |
| `SendAttendanceWA` | [SendAttendanceWA.php](file:///d:/Webproject/absensi_qr_sekolah/app/Jobs/SendAttendanceWA.php) | Queue: Default<br>Tries: 3, Backoff: 5s<br>Middleware: `RateLimited('wa-gateway-limiter')` (35 pesan/menit) | Menangani pengiriman pesan WhatsApp secara asinkron via API OneSender. Mengimplementasikan pembatalan otomatis jika waktu absensi sudah lewat 24 jam, jeda maksimal 15 menit dari jam scan (kecuali mode retry), mapping template pesan (masuk/pulang/terlambat/pulang cepat), random sleep 1.5 - 2.5 detik untuk anti-ban, parser Spintax `{Halo|Hai}`, dan pencatatan audit log ke tabel `wa_logs`. |

---

## K. Views dan Template (`resources/views`)

### 1. Layouts & Komponen Dasar
- [layouts/app.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/layouts/app.blade.php): Master layout utama untuk pengguna terautentikasi (dilengkapi sidebar, header, sweetalert, footer).
- [layouts/guest.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/layouts/guest.blade.php): Layout minimalis untuk halaman autentikasi (login, reset password).
- [layouts/navigation.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/layouts/navigation.blade.php): Bar navigasi atas dan menu responsif mobile.
- `resources/views/components/*`: Kumpulan komponen Blade reusable, seperti `material-card.blade.php` (container bergaya kartu modern), `sweetalert.blade.php` (integrasi SweetAlert2), `modal.blade.php`, `primary-button.blade.php`, `danger-button.blade.php`, `text-input.blade.php`, `input-error.blade.php`, `input-label.blade.php`, `dropdown.blade.php`, dll.

### 2. Halaman Aplikasi & Modul
- [welcome.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/welcome.blade.php): Landing page utama sistem absensi sekolah.
- [dashboard.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/dashboard.blade.php): Dashboard ringkasan kehadiran hari ini, statistik siswa/guru, dan widget status WA Gateway.
- [public/attendance/index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/public/attendance/index.blade.php): Portal publik pengecekan kehadiran mandiri tanpa login.
- [scanner/index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/scanner/index.blade.php): Antarmuka scanner QR Code berbasis kamera / USB barcode scanner (pilihan shift & mode masuk/pulang).
- [guests/index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/guests/index.blade.php): Halaman buku tamu digital untuk check-in dan check-out tamu.
- **Master Data (`resources/views/master/`):**
  - `levels/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/levels/index.blade.php), `create.blade.php`, `edit.blade.php` (Master jenjang/tingkat).
  - `classes/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/classes/index.blade.php), `create.blade.php`, `edit.blade.php` (Master rombel kelas).
  - `students/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/students/index.blade.php), `create.blade.php`, `edit.blade.php`, [show.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/students/show.blade.php), [import.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/students/import.blade.php) (Master data siswa, import Excel, dan detail kartu).
  - `teachers/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/teachers/index.blade.php), `create.blade.php`, `edit.blade.php`, [show.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/teachers/show.blade.php) (Master data guru dan preview kartu).
  - `shifts/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/shifts/index.blade.php), `create.blade.php`, `edit.blade.php` (Master shift presensi).
  - `holidays/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/holidays/index.blade.php), `create.blade.php`, `edit.blade.php` (Master kalender hari libur).
  - `wagateways/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/wagateways/index.blade.php), `create.blade.php`, `edit.blade.php` (Konfigurasi OneSender WA Gateway).
  - `walogs/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/master/walogs/index.blade.php) (Audit log notifikasi WA).
- **Laporan & Ekspor (`resources/views/reports/`):**
  - [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/reports/index.blade.php): Halaman menu utama laporan.
  - [students.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/reports/students.blade.php): Matriks rekap presensi bulanan siswa dengan visualisasi kolom libur akhir pekan dinamis (`JUMAT`, `SABTU`, `MINGGU`) dan update kehadiran inline.
  - [teachers.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/reports/teachers.blade.php): Matriks rekap presensi bulanan guru dengan visualisasi kolom libur akhir pekan.
  - `exports/`: [student_monthly.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/reports/exports/student_monthly.blade.php) & [teacher_monthly.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/reports/exports/teacher_monthly.blade.php) (Template tabel Excel export dengan penanda kolom libur akhir pekan).
- **Pengaturan & Utilitas Admin:**
  - [admin/generator/index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/admin/generator/index.blade.php): Antarmuka generator ID Card siswa/guru perorangan maupun massal ZIP.
  - [settings/index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/settings/index.blade.php): Pengaturan umum instansi, logo, serta formulir pemilihan hari libur akhir pekan (Jumat & Sabtu).
  - [settings/academic_year/index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/settings/academic_year/index.blade.php): Pengaturan tahun ajaran baru, promosi kelas, dan backup database.
  - [settings/cards/index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/settings/cards/index.blade.php): Upload gambar template kartu ID siswa & guru.
  - [settings/messages/index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/settings/messages/index.blade.php): Konfigurasi template pesan WhatsApp.
  - `users/`: [index.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/users/index.blade.php), `create.blade.php`, `edit.blade.php` (Manajemen akun user).
- **Profil & Autentikasi:**
  - [profile/edit.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/profile/edit.blade.php) dan partials (`delete-user-form`, `update-password-form`, `update-profile-information-form`).
  - `auth/*`: [login.blade.php](file:///d:/Webproject/absensi_qr_sekolah/resources/views/auth/login.blade.php), `forgot-password.blade.php`, `reset-password.blade.php`, `confirm-password.blade.php`, `verify-email.blade.php`.

---

## L. Routing Maps

### 1. Rute Publik (Tanpa Autentikasi)
*Definisi: [routes/web.php](file:///d:/Webproject/absensi_qr_sekolah/routes/web.php)*

| Method | URI | Nama Rute | Action Controller | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | `welcome` | `view('welcome')` | Halaman depan publik |
| `GET` | `/cek-kehadiran` | `public.attendance.index` | `PublicAttendanceController@index` | Form pencarian presensi publik |
| `POST` | `/cek-kehadiran` | `public.attendance.check` | `PublicAttendanceController@check` | Proses query presensi publik |
| `GET` | `/cron/process-queue`| - | `WaGatewayController@cronProcessQueue` | Trigger background queue via webhook/cron luar |

### 2. Rute Autentikasi Pengunjung / Tamu (`guest`)
*Definisi: [routes/auth.php](file:///d:/Webproject/absensi_qr_sekolah/routes/auth.php)*

| Method | URI | Nama Rute | Action Controller |
| :--- | :--- | :--- | :--- |
| `GET` | `/login` | `login` | `AuthenticatedSessionController@create` |
| `POST` | `/login` | - | `AuthenticatedSessionController@store` |
| `GET` | `/forgot-password` | `password.request` | `PasswordResetLinkController@create` |
| `POST` | `/forgot-password` | `password.email` | `PasswordResetLinkController@store` |
| `GET` | `/reset-password/{token}` | `password.reset` | `NewPasswordController@create` |
| `POST` | `/reset-password` | `password.store` | `NewPasswordController@store` |

### 3. Rute Terproteksi Pengguna Login (`auth`)
*Definisi: [routes/web.php](file:///d:/Webproject/absensi_qr_sekolah/routes/web.php) & [routes/auth.php](file:///d:/Webproject/absensi_qr_sekolah/routes/auth.php)*

| Method | URI | Nama Rute | Action Controller | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/logout` | `logout` | `AuthenticatedSessionController@destroy` | Keluar sesi login |
| `GET` | `/dashboard` | `dashboard` | `DashboardController@index` | Dashboard utama |
| `GET` | `/scanner` | `scanner.index` | `ScannerController@index` | Tampilan scanner QR |
| `POST` | `/scanner` | `scanner.store` | `ScannerController@store` | Proses simpan presensi scan |
| `GET/POST/PUT` | `/guests[/{guest}]` | `guests.*` | `GuestController` (index, store, update) | Manajemen buku tamu |
| `GET` | `/students/import` | `students.import` | `StudentController@import` | Halaman import siswa |
| `POST` | `/students/import` | `students.import.process` | `StudentController@processImport` | Proses file upload Excel |
| `GET` | `/students/template` | `students.import.template` | `StudentController@downloadTemplate` | Unduh template import |
| `POST` | `/students/{student}/generate-card` | `students.generate-card` | `StudentController@generateCard` | Generate kartu per siswa |
| `RESOURCE` | `/students` | `students.*` | `StudentController` | CRUD master siswa |
| `POST` | `/teachers/{teacher}/generate-card` | `teachers.generate-card` | `TeacherController@generateCard` | Generate kartu per guru |
| `RESOURCE` | `/teachers` | `teachers.*` | `TeacherController` | CRUD master guru |
| `RESOURCE` | `/levels` | `levels.*` | `LevelController` | CRUD master jenjang |
| `RESOURCE` | `/classes` | `classes.*` | `SchoolClassController` | CRUD master rombel kelas |
| `POST` | `/shifts/{shift}/toggle` | `shifts.toggle` | `ShiftController@toggle` | Toggle aktif/nonaktif shift |
| `RESOURCE` | `/shifts` | `shifts.*` | `ShiftController` | CRUD master shift |
| `RESOURCE` | `/holidays` | `holidays.*` | `HolidayController` | CRUD kalender libur |
| `GET` | `/reports/students` | `reports.students` | `ReportController@studentIndex` | Matriks laporan siswa |
| `POST` | `/laporan-siswa/update` | `reports.students.update` | `ReportController@updateStudentAttendance` | Edit absensi siswa inline |
| `GET` | `/laporan-siswa/export` | `reports.students.export` | `ReportController@exportStudentMonthly` | Ekspor bulanan siswa |
| `GET` | `/laporan-siswa/export-daily` | `reports.students.export-daily` | `ReportController@exportStudentDaily` | Ekspor harian siswa |
| `GET` | `/reports/teachers` | `reports.teachers` | `ReportController@teacherIndex` | Matriks laporan guru |
| `POST` | `/laporan-guru/update` | `reports.teachers.update` | `ReportController@updateTeacherAttendance` | Edit absensi guru inline |
| `GET` | `/laporan-guru/export` | `reports.teachers.export` | `ReportController@exportTeacherMonthly` | Ekspor bulanan guru |
| `GET` | `/laporan-guru/export-daily` | `reports.teachers.export-daily` | `ReportController@exportTeacherDaily` | Ekspor harian guru |
| `GET/PATCH/DEL` | `/profile` | `profile.*` | `ProfileController` (edit, update, destroy) | Pengaturan profil user |

### 4. Rute Khusus Administrator (`auth` + `middleware: admin`)
*Definisi: [routes/web.php](file:///d:/Webproject/absensi_qr_sekolah/routes/web.php)*

| Method | URI | Nama Rute | Action Controller | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/settings` | `settings.index` | `SettingController@index` | Form setting umum |
| `POST` | `/settings/update` | `settings.update` | `SettingController@update` | Simpan setting umum |
| `RESOURCE` | `/users` | `users.*` | `UserController` | CRUD pengguna sistem |
| `RESOURCE` | `/message-templates` | `message-templates.*` | `MessageTemplateController` (only: index, update) | Edit template WA |
| `RESOURCE` | `/card-templates` | `card-templates.*` | `CardTemplateController` (only: index, update) | Edit template kartu ID |
| `POST` | `/wagateways/settings` | `wagateways.settings.update` | `WaGatewayController@updateSettings` | Simpan setting WA |
| `POST` | `/wagateways/process-queue` | `wagateways.process_queue` | `WaGatewayController@processQueue` | Eksekusi antrian manual |
| `POST` | `/wagateways/retry-queue` | `wagateways.retry_queue` | `WaGatewayController@RetryQueue` | Retry antrian failed/expired |
| `RESOURCE` | `/wagateways` | `wagateways.*` | `WaGatewayController` | CRUD gateway OneSender |
| `GET` | `/walogs` | `walogs.index` | `WaLogController@index` | Log pesan WhatsApp |
| `GET` | `/walogs/export` | `walogs.export` | `WaLogController@export` | Ekspor log WhatsApp |
| `POST` | `/walogs/clear` | `walogs.clear` | `WaLogController@clear` | Hapus seluruh log WA |
| `GET` | `/academic-year` | `academic.year.index` | `AcademicYearController@index` | Panel tahun ajaran baru |
| `POST` | `/academic-year/promote` | `academic.year.promote` | `AcademicYearController@promote` | Eksekusi kenaikan kelas |
| `POST` | `/academic-year/cleanup` | `academic.year.cleanup` | `AcademicYearController@cleanup` | Bersihkan absensi lama |
| `GET` | `/academic-year/download/{filename}` | `academic.year.download` | `AcademicYearController@downloadBackup` | Unduh file backup SQL |
| `DELETE` | `/academic-year/delete-backup/{filename}` | `academic.year.delete-backup` | `AcademicYearController@deleteBackup` | Hapus file backup SQL |
| `POST` | `/academic-year/restore-guests/{filename}` | `academic.year.restore-guests` | `AcademicYearController@restoreGuests` | Restore tabel buku tamu |
| `GET` | `/generator` | `generator.index` | `CardGeneratorController@index` | Halaman generator ID Card |
| `GET` | `/generator/get-students` | `generator.get-students` | `CardGeneratorController@getStudents` | AJAX data siswa per kelas |
| `GET` | `/generator/get-teachers` | `generator.get-teachers` | `CardGeneratorController@getTeachers` | AJAX data guru |
| `POST` | `/generator/student` | `generator.student` | `CardGeneratorController@generateStudent` | Render kartu siswa |
| `POST` | `/generator/teacher` | `generator.teacher` | `CardGeneratorController@generateTeacher` | Render kartu guru |
| `POST` | `/generator/mass-student` | `generator.mass-student` | `CardGeneratorController@massGenerateStudent` | Render massal kartu siswa |
| `POST` | `/generator/mass-teacher` | `generator.mass-teacher` | `CardGeneratorController@massGenerateTeacher` | Render massal kartu guru |
| `GET` | `/generator/download-zip` | `generator.download-zip` | `CardGeneratorController@downloadZip` | Unduh file ZIP kartu |

---

## M. Rules

### Aturan Pembaruan File `project_maps.md`:
Setiap kali ada penambahan, pengubahan, atau penghapusan komponen pada proyek ini, AI Agent **WAJIB** segera memperbarui bagian terkait di dokumen ini:

1. **Model / Migration:** Catat model baru, field penting, dan relasinya di bagian **Section D**.
2. **Controller:** Tambahkan controller atau method baru di **Section E**.
3. **Services:** Catat class service baru di **Section F**.
4. **Console:** Catat semua command console baru di **Section G**.
5. **Helper:** Catat function helper baru di **Section H** serta pendaftaran di `composer.json`.
6. **Imports / Exports:** Catat semua class import dan export baru di **Section I**.
7. **Jobs:** Catat semua jobs baru di **Section J**.
8. **Views:** Cantumkan view blade baru di **Section K**.
9. **Routes:** Perbarui daftar endpoint di **Section L**.
10. **Dependencies:** Catat jika ada `composer require` atau `npm install` baru di **Section C**.
11. **Perbarui Tanggal:** Selalu perbarui tanggal *Terakhir diperbarui* di header dokumen.

