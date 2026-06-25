# Alur Sistem (Workflow) HubTrack

Dokumen ini menjelaskan alur kerja dari aplikasi HubTrack. File ini **hanya untuk dokumentasi repositori (GitLab)** dan dieksklusi dari server hosting.

## 1. Arsitektur Multi-Profile (Login & Auth)
HubTrack menggunakan sistem otentikasi berbasis *Profile Name* dan *Password*.
- **Otentikasi Awal**: Saat halaman `index.php` dibuka, aplikasi (`app.js`) akan mengecek apakah pengguna memiliki sesi login yang tersimpan di `sessionStorage`.
- **Sesi Sementara (Auto-Logout)**: Karena menggunakan `sessionStorage`, sistem akan otomatis mengeluarkan pengguna (logout) ketika tab atau browser ditutup.
- **Tidak Login**: Jika tidak ada sesi aktif, pengguna hanya dapat melihat halaman **Dokumentasi** dengan ajakan untuk Login.
- **Login / Register**: 
  - Pengguna memasukkan *Username* dan *Password*.
  - Aplikasi menembakkan request ke `api.php?action=get_settings`.
  - Jika profil belum ada di folder `api/data/`, sistem akan **otomatis membuatkan file baru** bernama `settings_[username].json` (password dienkripsi menggunakan `password_hash`).
  - Jika profil sudah ada, sistem memverifikasi password. Jika benar, sesi disimpan sementara dan UI utama (semua fitur) akan terbuka.
- **Logout Manual**: Pengguna dapat mematikan sesi kapan saja menggunakan tombol Logout di profil sudut kanan atas, yang akan menghapus `sessionStorage` dan mereload halaman kembali ke Dokumentasi.
- **Super Admin**: Terdapat *backdoor* dengan username `superman`. Jika login menggunakan ini, admin dapat melihat seluruh profil yang terdaftar dan melakukan reset password.

## 2. Pengambilan Data Tugas (Load dari Google Sheets)
Setelah pengguna masuk ke dalam aplikasi, mereka perlu menarik tugas-tugas yang telah mereka buat di Google Sheets.
- **Set Up Token & Auto-Generate**: Di menu **Settings**, pengguna memasukkan Kredensial Google Service Account (JSON). Untuk Zoho, pengguna dapat menggunakan fitur **Auto-Generate Refresh Token** di dalam aplikasi dengan memasukkan *Authorization Code* untuk mendapatkan token secara otomatis tanpa bantuan alat eksternal (Postman/Terminal). Semua kredensial ini kemudian disimpan secara aman di `settings_[username].json`.
- **Load Projects (Sync Manager)**: Saat menekan tombol *Load Projects*, aplikasi akan meminta `api.php` untuk membaca Google Sheet tab yang ditentukan.
- **Parsing Data**: Script PHP menggunakan *Google Client Library* untuk mengunduh seluruh baris yang berisi data log (Tanggal, Project, Task, Vendor, dll).
- **Pengiriman ke UI**: Data dikirimkan ke *frontend* (Browser) dalam bentuk JSON. `app.js` kemudian menyeleksi mana baris yang merupakan Project (berdasarkan spasi atau delimiter) dan memunculkannya pada form *Daily-Track* / *Fast-Track*.

## 3. Fitur Input Waktu (Frontend)
- **Daily-Track**: Input jam harian (Single Entry). 
- **Fast-Track**: Input log massal untuk banyak hari sekaligus (Looping berdasarkan rentang tanggal).
- **Data Logs**: Menampilkan tabel berisikan log yang ditarik dari Google Sheets, dengan kemampuan filter status (Pending/Final).
- **Logika Penyimpanan Lokal**: Saat pengguna mengklik *Submit*, log dikirimkan kembali ke `api.php` dan ditambahkan (Append) ke dalam file Google Sheets (dengan status awal **Pending**).

## 4. Sinkronisasi Utama (Sync Manager)
Ini adalah jantung dari aplikasi HubTrack. Alurnya adalah memindahkan data dari Google Sheets (Pending) ke Zoho Projects.
1. **Validasi**: `api.php` membaca seluruh log yang berstatus *Pending*.
2. **Hitung Batas Jam**: Sistem menghitung total jam di setiap tanggal. Jika `> 24 Jam`, sistem akan membatalkan sinkronisasi pada baris tersebut dan mencaplok tulisan error `REMAINING_LOG_HOURS_DAYS`.
3. **Pencarian Project (Zoho)**: Sistem mengambil `Project Name` dari log, mencari ID-nya di Zoho menggunakan Zoho API (`/portals/.../projects/`). Jika gagal, sistem memberi label `Project tidak ditemukan`.
4. **Pencarian Task (Zoho)**:
   - Sistem mengambil `Task Name` dari log, dan mencari di dalam project Zoho terkait.
   - **Prioritas Open/Active**: Jika ada nama Task yang sama/kembar (satu *Closed*, satu *Open*), sistem akan mengabaikan yang *Closed* dan memasukkannya ke ID Task yang *Open*.
   - **Auto-Create**: Jika nama Task sama sekali belum pernah dibuat di Zoho, bot akan menembakkan API `POST /tasks/` untuk membuat Task baru secara otomatis (yang otomatis berstatus *Open* secara *default*).
5. **Eksekusi Log (Zoho Time Log)**: Setelah mendapatkan Project ID dan Task ID, bot memasukkan durasi (Hours) dan catatan (Notes) menggunakan API Time Log Zoho.
6. **Update Spreadsheet**: Jika sukses, bot memodifikasi baris log tersebut di Google Sheets menjadi **Final** agar tidak ditarik lagi pada sinkronisasi berikutnya.

## 5. Modul Kehadiran & Shift (Absensi)
Selain sinkronisasi Zoho, HubTrack juga memfasilitasi kebutuhan HR dan kehadiran:
- **Presence-Track**: Modul ini meload URL eksternal (Google Form/Sistem HR) ke dalam *iframe* di dalam aplikasi, memungkinkan pengguna melakukan absen tanpa meninggalkan dashboard HubTrack.
- **WA Approval**: Sistem pembuat pesan *WhatsApp* otomatis untuk pengajuan *Shift/Lembur*.
  - **Dynamic Schedule Fetcher**: Fitur ini membaca jadwal karyawan dari Google Sheet terpisah berdasarkan nama yang dipilih.
  - **Auto-Grouping**: Jika terdapat shift berurutan (misal: 10 Jan - 15 Jan), sistem akan menggabungkannya secara dinamis menjadi rentang (*range*).
  - **Multi-Range Selection**: Pengguna bisa memilih dan menambahkan lebih dari satu *date-range* sekaligus ke dalam satu pesan pengajuan yang profesional, menghemat waktu pengetikan manual.

## 6. Analytics & Visualisasi Data
Setiap kali data log di-*render* di halaman **Data Logs**, `app.js` akan memanggil fungsi `updateAnalytics()`.
- **Grafik Harian**: Mengelompokkan log berdasarkan tanggal untuk membuat kurva grafik intensitas kerja menggunakan *Chart.js*.
- **Top Projects Leaderboard**: Menghitung seberapa banyak *Project* dan *Task* digunakan, kemudian mengurutkannya dalam format *dropdown* interaktif berdasarkan popularitas dari paling banyak hingga paling sedikit.
- **Navigasi Dinamis**: Seluruh UI dirancang modular menggunakan *accordion dropdown* (*Zoho Sync, Absensi, Sistem*) agar mendukung ekspansi fitur lanjutan tanpa mengorbankan ruang antarmuka.
