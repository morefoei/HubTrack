# TrackHub - Universal Zoho Time Sync Dashboard

TrackHub adalah aplikasi web modern berbasis PHP dan JavaScript murni yang berfungsi sebagai jembatan (middleware) antara **Google Sheets** dan **Zoho Projects API**. Aplikasi ini dirancang secara khusus untuk mempermudah, mempercepat, dan mengotomatisasi proses pelaporan jam kerja (Time Logs) tim ke platform manajemen Zoho.

## Latar Belakang & Masalah
Dalam operasional harian, tim (terutama engineer/developer) seringkali diwajibkan untuk melaporkan jam kerja (Timesheet) ke dalam dua platform berbeda secara bersamaan: 
1. **Google Sheets** untuk rekap internal/HR.
2. **Zoho Projects** untuk penagihan klien atau manajemen proyek.

Proses input ganda (double-entry) ini sangat memakan waktu, membosankan, dan rawan kesalahan (*human error*).

## Solusi: TrackHub
TrackHub memecahkan masalah ini dengan skema **Input Satu Pintu (Single Source of Truth)**. Karyawan hanya perlu menginput log aktivitas mereka di Google Sheets melalui *user interface* (UI) TrackHub yang elegan, dan TrackHub akan mengurus sisanya (mentransfer dan menyinkronkan data tersebut ke server Zoho secara otomatis).

## Fitur Utama

1. **Sistem Multi-Profile Terisolasi (Multi-Tenant)**
   Setiap pengguna dapat masuk menggunakan username profil masing-masing. Semua token autentikasi (Google & Zoho) milik pengguna tersebut akan dienkripsi dan disimpan secara terpisah di backend (tanpa database SQL, melainkan menggunakan sistem file PHP tersandi).

2. **Daily-Track & Fast-Track (Bulk Insert)**
   - **Daily-Track**: Mengisi jam kerja untuk satu hari spesifik secara interaktif.
   - **Fast-Track**: Memungkinkan pengguna memasukkan log untuk *range* waktu yang sangat panjang (misal: sebulan penuh) hanya dengan satu kali klik. Dilengkapi fitur AI-like **Exclude Weekends** untuk melompati hari Sabtu & Minggu secara otomatis.

3. **Sync Manager (The Core Engine)**
   Sistem pintar yang akan:
   - Menarik data status "Pending" dari Google Sheets.
   - Mencari ID Project dan ID Task secara otomatis di sistem Zoho (berdasarkan nama).
   - Memasukkan *Time Log* secara otomatis ke server Zoho.
   - Jika Task belum pernah ada di Zoho, TrackHub akan membuatkan Task tersebut secara *on-the-fly* tanpa campur tangan pengguna.

4. **Modul Absensi Terpadu**
   - **Presence-Track**: Iframe bawaan untuk mengakses form absensi internal/HR tanpa perlu membuka tab browser baru.
   - **WA Approval Generator**: Sistem pintar yang mampu mengekstrak jadwal shift karyawan langsung dari Google Sheets (secara dinamis), mengelompokkan hari-hari yang berurutan, dan membantu karyawan men-generate teks pengajuan *Approval* via WhatsApp untuk atasan dalam hitungan detik.

5. **Token Auto-Generator Terintegrasi**
   Tidak perlu lagi menggunakan aplikasi *Postman* atau alat pihak ketiga yang rumit. TrackHub menyediakan modul khusus di halaman **Settings** untuk menukar *Zoho Authorization Code* menjadi *Refresh Token* dengan satu kali klik.

6. **Analytics Dashboard**
   Visualisasi aktivitas pengguna berbasis grafik dan peringkat top project/task yang paling sering dikerjakan, di-render menggunakan *Chart.js*.

## Stack Teknologi
Aplikasi ini dikembangkan untuk dapat berjalan pada server *shared hosting* standar yang paling murah sekalipun:
- **Frontend**: HTML5, Vanilla JavaScript, CSS3 (Custom Glassmorphism UI), FontAwesome.
- **Backend**: PHP 7.4+ (Native API Engine, cURL, File System).
- **Data Source**: Google Sheets API v4.
- **Target Endpoint**: Zoho Projects API.


## Instalasi & Panduan
Aplikasi sudah dilengkapi dengan manual instalasi mendalam (Panduan *Google Cloud Service Account* & *Zoho API Console*) yang terpasang (*embedded*) di dalam sistem. Anda dapat melihat panduan tersebut di halaman depan saat belum login, atau pada menu **Sistem > Dokumentasi**.
