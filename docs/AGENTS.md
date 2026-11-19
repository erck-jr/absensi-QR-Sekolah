# Project Guidelines & Agents Rules

## 1. Project Map Priority
- **File Utama:** [project_maps.md](file:///d:/Webproject/absensi_qr_sekolah/docs/project_maps.md) adalah peta arsitektur dan indeks seluruh komponen proyek.
- **Baca Pertama Kali:** Setiap kali AI agent diminta membaca, menganalisa, atau menambah fitur pada proyek ini, lihat dan baca terlebih dahulu file [project_maps.md](file:///d:/Webproject/absensi_qr_sekolah/docs/project_maps.md) untuk menghindari pemindaian ulang folder yang tidak perlu.

## 2. Pembaruan Wajib (Continuous Synchronization)
- Setiap kali Anda melakukan perubahan yang memengaruhi struktur proyek:
  - Penambahan/perubahan/penghapusan **Model** atau skema database
  - Penambahan/perubahan/penghapusan **Controller** atau method aksi
  - Penambahan/perubahan/penghapusan **View Blade** atau komponen
  - Penambahan/perubahan/penghapusan **Services** atau **Imports/Exports** atau **Console** atau **Jobs**
  - Penambahan/perubahan/penghapusan **Helpers**
  - Penambahan/perubahan/penghapusan **Dependencies** (`composer.json` / `package.json`)
  - Penambahan/perubahan **Routes** di `routes/web.php`
- Anda **WAJIB** segera mengupdate [project_maps.md](file:///d:/Webproject/absensi_qr_sekolah/docs/project_maps.md) pada turn/sesi pengerjaan tersebut agar data peta proyek selalu akurat dan mutakhir.

