# TrackHub - Universal Zoho Time Sync Dashboard

TrackHub adalah aplikasi web modern berbasis PHP dan JavaScript murni yang berfungsi sebagai jembatan (middleware) antara **Google Sheets** dan **Zoho Projects API**. Aplikasi ini dirancang secara khusus untuk mempermudah, mempercepat, dan mengotomatisasi proses pelaporan jam kerja (Time Logs) tim ke platform manajemen Zoho.

## 🎯 Tujuan Proyek
Dalam operasional harian, tim (terutama developer/engineer) seringkali diwajibkan untuk melaporkan jam kerja (Timesheet) ke dalam dua platform berbeda secara bersamaan: 
1. **Google Sheets** untuk rekap internal/HR.
2. **Zoho Projects** untuk penagihan klien atau manajemen proyek.

Proses input ganda (double-entry) ini sangat memakan waktu, membosankan, dan rawan kesalahan (*human error*). 

**Solusinya:** TrackHub memecahkan masalah ini dengan skema **Input Satu Pintu (Single Source of Truth)**. Pengguna hanya perlu menginput log aktivitas mereka di Google Sheets melalui *user interface* (UI) TrackHub, dan sistem akan mentransfer serta menyinkronkan data tersebut ke server Zoho secara otomatis.

## 🚀 Fitur Utama

1. **Sistem Multi-Profile Terisolasi (Multi-Tenant) dengan Admin Control**
   Setiap pengguna dapat masuk menggunakan username profil masing-masing. Pengguna dapat mewarisi konfigurasi Google Sheet utama secara terpusat, sambil mempertahankan pengaturan Zoho API spesifik individu.
2. **Daily-Track & Fast-Track (Bulk Insert)**
   Fitur pengisian otomatis untuk rentang waktu panjang dengan pengecualian hari libur (terintegrasi API Kalender Google), melompati akhir pekan secara otomatis, serta fitur *Paksa Masuk (Lembur)*.
3. **Sync Manager (The Core Engine)**
   Sistem penarik data pintar yang otomatis mengambil data "Pending" dari Google Sheets, mencocokkan ID Project dan ID Task, dan memasukkan *Time Log* secara otomatis ke server Zoho. Jika Task belum ada, otomatis akan dibuat.
4. **Modul Absensi Terpadu & WA Approval Generator**
   Iframe bawaan untuk form absensi internal dan sistem pembuat teks pengajuan *Approval* via WhatsApp untuk atasan dalam hitungan detik, yang kini dilengkapi dengan fitur manajemen (Edit/Hapus) untuk memodifikasi pengajuan yang telah tersimpan.
5. **Token Auto-Generator Terintegrasi**
   Menukar *Zoho Authorization Code* menjadi *Refresh Token* dengan mudah secara langsung tanpa aplikasi pihak ketiga (seperti Postman).
6. **Analytics Dashboard**
   Visualisasi aktivitas pengguna berbasis grafik dan peringkat top project/task menggunakan *Chart.js*.

## 💻 Stack Teknologi
Aplikasi dirancang agar dapat berjalan pada server *shared hosting* paling standar sekalipun:
- **Frontend**: HTML5, Vanilla JavaScript, CSS3 (Custom Glassmorphism UI), FontAwesome.
- **Backend**: PHP 7.4+ (Native API Engine, cURL, File System).
- **Data Source**: Google Sheets API v4.
- **Target Endpoint**: Zoho Projects API.

---

## 🛠️ Cara Instalasi

Karena aplikasi ini dibangun menggunakan PHP Native tanpa framework kompleks (seperti Laravel), proses instalasinya sangat sederhana.

1. **Clone Repositori**
   Buka terminal/Command Prompt Anda dan jalankan perintah berikut untuk mengunduh kode sumber proyek:
   ```bash
   git clone <URL_REPOSITORY_ANDA>
   cd sync-work
   ```

2. **Pengaturan Direktori (Permission)**
   Aplikasi menggunakan sistem *file-based database* (JSON) untuk menyimpan profil, token, dan pengaturan pengguna (berada di folder internal atau `api/`).
   Pastikan folder sistem memiliki izin tulis (*write permission*). Di lingkungan Linux/Mac, Anda dapat menjalankan perintah:
   ```bash
   chmod -R 775 . 
   # Atau pastikan user web-server (www-data) memiliki hak akses penuh ke direktori aktif.
   ```

3. **Konfigurasi Kredensial API**
   - Aplikasi ini membutuhkan pengaturan **Google Cloud Service Account** (file `.json`) dan kunci dari **Zoho API Console**.
   - Untuk instruksi pembuatan token dan konfigurasi kredensial ini, silakan lihat langsung dari **Panduan internal** yang *embedded* di dalam aplikasi (Menu Utama > **Dokumentasi**).

---

## 🏃 Cara Menjalankan di Komputer Lokal

Anda dapat menjalankan aplikasi ini di komputer lokal dengan sangat mudah menggunakan metode berikut:

### Cara 1: Menggunakan Built-in PHP Web Server (Sangat Disarankan untuk Testing Cepat)
Jika di komputer Anda sudah terinstal PHP (minimal versi 7.4), Anda dapat langsung menggunakan server bawaan dari PHP tanpa perlu menginstal software *stack* tambahan.

1. Buka terminal atau command prompt Anda.
2. Navigasikan ke dalam direktori proyek tempat file di-clone.
   ```bash
   cd path/to/sync-work
   ```
3. Jalankan perintah server lokal PHP:
   ```bash
   php -S localhost:8000
   ```
4. Buka browser (Chrome, Firefox, Safari) dan kunjungi URL berikut:
   `http://localhost:8000`

### Cara 2: Menggunakan Web Server Lokal (XAMPP, MAMP, atau LAMP)
Jika Anda terbiasa menggunakan paket web server lokal yang mencakup Apache/Nginx:

1. Salin atau pindahkan folder `sync-work` ke dalam direktori root/dokumen (Document Root) dari web server Anda.
   - Untuk **XAMPP** di Windows: Pindahkan ke `C:\xampp\htdocs\sync-work`
   - Untuk **MAMP** di Mac: Pindahkan ke `/Applications/MAMP/htdocs/sync-work`
   - Untuk **LAMP** di Linux: Pindahkan ke `/var/www/html/sync-work`
2. Pastikan web server Apache (atau Nginx) sedang berjalan.
3. Buka browser Anda dan kunjungi URL:
   `http://localhost/sync-work`

---
*Dibuat untuk mempermudah dan menyederhanakan manajemen waktu developer.*
