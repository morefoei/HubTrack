# Panduan Kontribusi (Contributing Guide)

Terima kasih atas ketertarikan Anda untuk berkontribusi pada TrackHub! Kami sangat menyambut baik setiap kontribusi, baik itu berupa perbaikan *bug*, penambahan fitur baru, optimasi UI/UX, maupun penyempurnaan dokumentasi.

Panduan ini bertujuan untuk mempermudah Anda dalam proses berkontribusi serta menjaga kualitas, keamanan, dan konsistensi kode dalam proyek ini.

## 🛠️ Alur Kerja Kontribusi Standar

Kami menggunakan alur kerja GitHub standar untuk kolaborasi lintas komunitas (*Fork & Pull Request*). Ikuti langkah-langkah di bawah ini untuk memulai:

### 1. Fork Repositori
Mulai dengan melakukan *Fork* repositori TrackHub ke akun GitHub pribadi Anda dengan mengklik tombol **Fork** di pojok kanan atas halaman repositori utama.

### 2. Clone Repositori Fork
Unduh (clone) repositori hasil *fork* tersebut ke mesin lokal Anda melalui terminal:
```bash
git clone https://github.com/USERNAME_ANDA/sync-work.git
cd sync-work
```

### 3. Tambahkan Upstream (Opsional namun Disarankan)
Tambahkan repositori asli sebagai *upstream* untuk mempermudah pembaruan kode dan sinkronisasi dengan *branch* utama yang terbaru jika terdapat pembaruan dari *maintainer*:
```bash
git remote add upstream https://github.com/USERNAME_PEMILIK_ASLI/sync-work.git
git fetch upstream
```

### 4. Buat Branch Baru
Hindari melakukan *commit* langsung ke dalam `main` branch Anda. Selalu buat *branch* baru yang spesifik mewakili fitur atau perbaikan sebelum melakukan perubahan:
```bash
git checkout -b fitur/nama-fitur-baru
# atau
git checkout -b bugfix/deskripsi-bug
```

### 5. Lakukan Perubahan (Coding)
Terapkan kode untuk perbaikan *bug* atau tambahan fitur yang Anda inginkan. Pastikan untuk selalu menguji (*test*) perubahan Anda di komputer lokal untuk memastikan tidak ada fungsionalitas lama yang terganggu (*regression*). 
*(Gunakan `php -S localhost:8000` seperti tertulis pada README).*

### 6. Commit Perubahan
Kami merekomendasikan format [Conventional Commits](https://www.conventionalcommits.org/). Pesan *commit* harus jelas agar *reviewer* paham tentang riwayat apa yang Anda kerjakan:
```bash
git add .
git commit -m "feat: menambahkan modul pwa service worker"
# atau
git commit -m "fix: memperbaiki kegagalan parsing libur bulan ini"
```

### 7. Push ke Repositori Fork Anda
Unggah (push) *branch* pengerjaan Anda tersebut ke GitHub:
```bash
git push origin fitur/nama-fitur-baru
```

### 8. Buat Pull Request (PR)
Kembali ke halaman repositori utama TrackHub (atau *fork* milik Anda) dan klik tombol **Compare & pull request**. Berikan deskripsi PR yang informatif:
- Apa inti dari PR ini (Bug/Fitur baru/Dokumentasi)?
- Mengapa PR ini diperlukan?
- Langkah atau cara menguji (reproduksi) perubahan Anda.

---

## 📝 Aturan Gaya Penulisan Kode (Code Style)

Aplikasi TrackHub secara sengaja tidak bergantung pada framework besar demi performa dan kemudahan instalasi di hosting kecil. Oleh karena itu, kita harus sangat menjaga struktur kode *Native PHP* dan *Vanilla JavaScript* agar tetap teratur (maintainable).

### 🐘 Aturan PHP
1. **Standar Kode**: Usahakan patuhi **PSR-12** (PHP Standard Recommendation).
2. **Penamaan**: Gunakan *camelCase* untuk nama variabel/fungsi (`function fetchZohoTasks()`) dan *PascalCase* untuk nama Class (`class ZohoAPI`).
3. **Respon API**: Seluruh script backend di dalam folder `api/` atau `sync/` sebagian besar berfungsi layaknya API Endpoint. Selalu kembalikan respons menggunakan `json_encode()` dan tambahkan header yang sesuai (`header('Content-Type: application/json');`).
4. **Keamanan (Security)**: Lakukan sanitasi data *input* dan bersihkan respons JSON guna mencegah serangan XSS/Injection.

### 🟨 Aturan JavaScript (Vanilla)
1. **Modern JS**: Gunakan sintaks **ES6+** yang modern (contoh: gunakan `const` dan `let`, *Arrow Functions*, *Template Literals*). Hindari penggunaan `var`.
2. **Asinkron**: Gunakan `async/await` yang dipadukan dengan blok `try-catch` ketika berinteraksi dengan Fetch API atau saat menunggu interaksi elemen antar muka.
3. **Tanpa Data Rahasia**: Jangan pernah memasukkan konfigurasi *secret* API/Token apa pun di *client-side*.

### 🎨 Aturan HTML & CSS
1. **Semantik HTML**: Gunakan tag HTML5 yang memiliki nilai arti (`<header>`, `<nav>`, `<main>`, `<section>`, dll).
2. **Styling**: Proyek ini memiliki antarmuka khusus (Glassmorphism modern). Pastikan elemen baru Anda selaras dengan kelas CSS global yang sudah ada di `assets/css/style.css`.
3. **Hindari Inline Styles**: Jauhkan *styling* langsung di dalam atribut HTML (seperti `<div style="...">`). Buatlah kelas khusus.

---

## 🐞 Melaporkan Bug atau Meminta Fitur
Jika Anda belum siap membuat kode (Pull Request), melaporkan celah keamanan atau bug *UI/UX* juga merupakan kontribusi luar biasa! 
Silakan buat laporan pada tab **Issues**, dan sebisa mungkin cantumkan hal berikut:
- Penjelasan detail mengenai error yang terjadi.
- Langkah-langkah mereproduksi error.
- Versi Browser / OS / dan versi PHP Anda.
- Tangkapan layar (*Screenshot*) dari masalah terkait (atau log console web).

Sekali lagi, terima kasih atas waktu Anda berpartisipasi membuat TrackHub menjadi perangkat lunak internal yang lebih mumpuni!
