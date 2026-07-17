<section id="guide-view" class="view-section">
            <div class="card" style="max-width: 900px; margin: 0 auto; line-height: 1.6;">
                <div class="card-header">
                    <h2 class="card-title"><i class="fa-solid fa-book-open" style="color: var(--primary-color);"></i> Dokumentasi Instalasi & Penggunaan TrackHub</h2>
                </div>
                <div style="padding: 1rem; max-height: 70vh; overflow-y: auto;">
                    
                    <style>
                        .guide-mode-toggle { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
                        .guide-mode-btn { flex: 1; background: var(--panel-bg); border: 1px solid var(--panel-border); border-radius: 12px; padding: 1rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 1rem; position: relative; overflow: hidden; }
                        .guide-mode-btn:hover { background: rgba(255,255,255,0.05); transform: translateY(-2px); }
                        .guide-mode-btn.active { background: rgba(168, 85, 247, 0.08); border-color: #a855f7; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
                        .guide-mode-btn.active::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: #a855f7; }
                        .guide-mode-btn.manual.active { background: rgba(59, 130, 246, 0.08); border-color: #3b82f6; }
                        .guide-mode-btn.manual.active::before { background: #3b82f6; }
                        .guide-mode-icon { font-size: 1.5rem; color: var(--text-muted); background: rgba(0,0,0,0.2); width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all 0.3s ease; }
                        .guide-mode-btn.active .guide-mode-icon { color: #c084fc; background: rgba(168, 85, 247, 0.2); }
                        .guide-mode-btn.manual.active .guide-mode-icon { color: #60a5fa; background: rgba(59, 130, 246, 0.2); }
                        .guide-mode-text h4 { margin: 0 0 0.2rem 0; font-size: 1rem; color: var(--text-main); font-weight: 600; }
                        .guide-mode-text p { margin: 0; font-size: 0.8rem; color: var(--text-muted); }
                        .guide-mode-btn.active .guide-mode-text h4 { color: #c084fc; }
                        .guide-mode-btn.manual.active .guide-mode-text h4 { color: #60a5fa; }
                        @media (max-width: 600px) { .guide-mode-toggle { flex-direction: column; } }
                    </style>

                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 500; color: var(--text-main); margin-bottom: 1rem; font-size: 1.05rem;"><i class="fa-solid fa-sliders" style="color: var(--primary); margin-right: 0.5rem;"></i> Sesuaikan Panduan dengan Mode Anda:</label>
                        <div class="guide-mode-toggle">
                            <div class="guide-mode-btn active" data-mode="auto">
                                <div class="guide-mode-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                                <div class="guide-mode-text">
                                    <h4>Otomatis (Ikuti Admin)</h4>
                                    <p>Sangat Disarankan, Praktis</p>
                                </div>
                            </div>
                            <div class="guide-mode-btn manual" data-mode="manual">
                                <div class="guide-mode-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                                <div class="guide-mode-text">
                                    <h4>Manual Settings</h4>
                                    <p>Gunakan Google Sheet Sendiri</p>
                                </div>
                            </div>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); background: rgba(255, 255, 255, 0.03); padding: 0.75rem 1rem; border-radius: 6px; border-left: 3px solid var(--text-muted);">
                            <i class="fa-solid fa-circle-info" style="margin-right: 0.3rem;"></i> <strong>Panduan Cerdas:</strong> Tampilan di bawah ini otomatis disesuaikan dengan tombol yang Anda klik di atas!
                        </div>
                        <input type="hidden" id="guideModeSelector" value="auto">
                    </div>

                    <div class="lang-id">
                        <div class="guide-manual-only">
                            <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-top: 1rem;">Bagian 1: Pengaturan Google Sheets API (Bot Google)</h3>
                            <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                                <li>Buka <strong>Google Cloud Console</strong> (console.cloud.google.com).</li>
                                <li>Buat Project baru (misal: <em>Zoho-Sync-App</em>).</li>
                                <li>Pergi ke menu <strong>APIs & Services &gt; Library</strong>. Cari <strong>Google Sheets API</strong> lalu klik <strong>Enable</strong>.</li>
                                <li>Pergi ke menu <strong>APIs & Services &gt; Credentials</strong>.</li>
                                <li>Klik <strong>Create Credentials &gt; Service Account</strong>. Isi nama bot (misal: <em>zoho-bot</em>) dan klik Done.</li>
                                <li>Klik email Service Account yang baru dibuat tersebut, masuk ke tab <strong>Keys</strong>, klik <strong>Add Key &gt; Create New Key</strong>, lalu pilih format <strong>JSON</strong>.</li>
                                <li>File JSON akan ter-download ke komputer Anda. Buka file tersebut dengan Notepad, lalu <em>Copy</em> semua isinya dan <em>Paste</em> ke kolom <strong>Google Service Account JSON</strong> di tab Settings aplikasi kita.</li>
                                <li><strong>SANGAT PENTING:</strong> <em>Copy</em> alamat email Service Account bot Anda (contoh: <code>zoho-bot@...iam.gserviceaccount.com</code>). Buka file Google Sheets Anda, klik tombol <strong>Share (Bagikan)</strong> di pojok kanan atas, lalu paste email bot tersebut dan berikan akses sebagai <strong>Editor</strong>.</li>
                            </ol>
                        </div>

                        <h3 id="bagian-2" style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-top: 1rem;">Bagian 2: Pengaturan Zoho Projects API</h3>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Buka <strong>Zoho API Console</strong> (api-console.zoho.com).</li>
                            <li>Klik <strong>Add Client</strong>, lalu pilih <strong>Self Client</strong>.</li>
                            <li>Klik <strong>Create</strong>. Zoho akan memberikan Anda <strong>Client ID</strong> dan <strong>Client Secret</strong>. <em>Copy</em> keduanya ke tab Settings aplikasi kita.</li>
                            <li>Di Zoho API Console, buka tab <strong>Generate Code</strong>. Lengkapi form yang muncul persis seperti ilustrasi di bawah ini:<br>
<div style="background: #ffffff; color: #333; padding: 1.5rem; border-radius: 8px; margin: 1rem 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 650px; position: relative;">
    <div style="position: absolute; top: 0; right: 0; background: #fbbf24; color: #854d0e; font-size: 0.7rem; font-weight: bold; padding: 0.2rem 0.6rem; border-bottom-left-radius: 8px; border-top-right-radius: 8px;">ILUSTRASI ZOHO API CONSOLE</div>
    <!-- Scope Field -->
    <div style="margin-bottom: 1.5rem;">
        <label style="display: block; font-size: 0.85rem; color: #4b5563; margin-bottom: 0.4rem; font-weight: 600;">Scope</label>
        <div style="position: relative;">
            <textarea readonly style="width: 100%; border: 1px solid #3b82f6; border-radius: 2px; padding: 0.5rem; font-family: monospace; font-size: 0.85rem; color: #1f2937; background: #fff; resize: none; outline: none; height: 65px;">ZohoProjects.tasks.ALL,ZohoProjects.projects.ALL,ZohoProjects.portals.ALL,ZohoProjects.timelogs.ALL</textarea>
            <button onclick="navigator.clipboard.writeText('ZohoProjects.tasks.ALL,ZohoProjects.projects.ALL,ZohoProjects.portals.ALL,ZohoProjects.timelogs.ALL'); const icon = this.querySelector('i'); icon.className='fa-solid fa-check'; this.style.color='#10b981'; setTimeout(() => { icon.className='fa-regular fa-copy'; this.style.color=''; }, 2000);" style="position: absolute; bottom: 0.5rem; right: 0.5rem; background: #f3f4f6; border: 1px solid #d1d5db; color: #4b5563; cursor: pointer; padding: 0.3rem 0.6rem; border-radius: 4px; transition: all 0.2s;" title="Copy to clipboard" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                <i class="fa-regular fa-copy"></i> Copy
            </button>
        </div>
    </div>
    
    <!-- Duration Field -->
    <div style="margin-bottom: 1.5rem;">
        <label style="display: block; font-size: 0.85rem; color: #4b5563; margin-bottom: 0.4rem; font-weight: 600;">Code expiry duration</label>
        <div style="border-bottom: 1px solid #d1d5db; padding: 0.3rem 0; color: #1f2937; display: flex; justify-content: space-between; font-size: 0.95rem;">
            <span>10 minutes</span>
            <i class="fa-solid fa-chevron-down" style="color: #9ca3af; font-size: 0.8rem; margin-top: 4px;"></i>
        </div>
    </div>
    
    <!-- Description Field -->
    <div style="margin-bottom: 0.5rem;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
            <label style="font-size: 0.85rem; color: #4b5563; font-weight: 600;">Description</label>
            <span style="font-size: 0.75rem; color: #3b82f6;">Character count : 0 / 250</span>
        </div>
        <textarea readonly placeholder="Isi deskripsi bebas (contoh: TrackHub Sync)" style="width: 100%; border: 1px solid #d1d5db; border-radius: 2px; padding: 0.5rem; font-family: inherit; font-size: 0.95rem; color: #4b5563; background: #fff; resize: none; outline: none; height: 65px;"></textarea>
    </div>
</div>
                            </li>
                            <li>Setelah form terisi persis seperti di atas, klik tombol hijau <strong>Create</strong>. Pilih portal organisasi Anda dan tekan <strong>Accept/Terima</strong>.</li>
                            <li>Zoho akan menampilkan kode Authorization sementara (Authorization Code). Segera <em>copy</em> kode tersebut.</li>
                            <li>Buka menu <strong>Settings</strong> di aplikasi TrackHub ini. Pastikan Anda sudah menyalin <strong>Zoho Client ID</strong> dan <strong>Zoho Client Secret</strong>.</li>
                            <li>Pada kotak <strong>Auto-Generate Refresh Token</strong> di menu Settings, <em>Paste</em> kode Authorization sementara tersebut <em>(contoh: <code>1000.e574a13a804f9...</code>)</em>, lalu klik <strong>⚡ Generate</strong>. Token akan otomatis dibuat dan tersimpan!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 3: Melengkapi Tab Settings di Aplikasi</h3>
                        
                        <div class="guide-auto-only" style="background: rgba(168,85,247,0.05); border: 1px solid rgba(168,85,247,0.3); border-left: 4px solid #a855f7; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                            <h4 style="color: #c084fc; margin-bottom: 0.5rem;"><i class="fa-solid fa-wand-magic-sparkles"></i> Mode: Ikuti Pengaturan Admin (Otomatis)</h4>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">Karena Anda memilih mode Otomatis, pengaturannya sangat mudah!</p>
                            <ul style="margin-left: 1.5rem; margin-bottom: 0; color: var(--text-main);">
                                <li><strong>Default Google Form Absensi:</strong> Masukkan <strong>Nama Default Absensi</strong> dan pilih <strong>Divisi Default Absensi</strong> Anda. Dengan mengatur ini, saat Anda menggunakan fitur Rencana Absensi nanti, Google Form akan secara otomatis terisi (<em>auto-prefill</em>) tanpa perlu Anda ketik ulang!</li>
                                <li><strong>Zoho Portal Name:</strong> Masukkan ID organisasi Zoho Anda (contoh: <code>847721722</code>).</li>
                                <li style="color: #34d399; margin-top: 0.5rem; list-style-type: none; margin-left: -1.5rem;"><i class="fa-solid fa-check-circle"></i> <em>Selesai! Anda tidak perlu mengisi Spreadsheet ID, Sheet Name, ataupun Service Account JSON. Semuanya sudah dikendalikan secara otomatis oleh sistem/admin!</em></li>
                            </ul>
                        </div>

                        <div class="guide-manual-only" style="background: rgba(59,130,246,0.05); border: 1px solid rgba(59,130,246,0.3); border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                            <h4 style="color: #60a5fa; margin-bottom: 0.5rem;"><i class="fa-solid fa-screwdriver-wrench"></i> Mode: Manual / Advanced Settings (Atur Sendiri)</h4>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">Anda memilih untuk menyimpan data secara manual ke file Spreadsheet milik Anda pribadi.</p>
                            <ul style="margin-left: 1.5rem; margin-bottom: 0; color: var(--text-main);">
                                <li><strong>Google Spreadsheet ID:</strong> Masukkan ID unik dari link URL Google Sheet pribadi Anda.</li>
                                <li><strong>Google Sheet Tab Name:</strong> Nama Sheet tempat Anda akan menyimpan data Zoho (misal: <code>Sheet1</code> atau <code>tasklist</code>).</li>
                                <li><strong>Google Service Account JSON:</strong> Paste isi file JSON bot Google yang Anda buat di Bagian 1 tadi.</li>
                                <li><strong>Default Google Form Absensi:</strong> Masukkan Nama dan pilih Divisi Default Anda untuk fitur auto-prefill absen.</li>
                                <li><strong>Zoho Portal Name:</strong> Masukkan ID organisasi Zoho Anda.</li>
                            </ul>
                        </div>
                        <p style="margin-bottom: 2rem;">Setelah semua terisi (termasuk Token Zoho di Bagian 2), klik tombol <strong><i class="fa-solid fa-floppy-disk"></i> Save Settings</strong>.</p>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 4: Cara Penggunaan & Fitur Canggih</h3>
                        <p>Setelah pengaturan selesai, Anda bisa menginput data jam kerja Anda lewat menu utama:</p>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Daily-Track:</strong> Gunakan menu ini untuk menginput 1 hari kerja. Klik tulisan <em><i class="fa-solid fa-rotate"></i> Load Projects</em> pada kolom Project Name untuk mengambil daftar nama project langsung dari Zoho.</li>
                            <li><strong>Fast-Track & Exclude Weekends:</strong> Fitur ini digunakan jika Anda memiliki jadwal yang sama persis untuk beberapa hari berturut-turut (misal: masuk jam 09:00 - 18:00 selama 1 bulan penuh).
                                <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <li>Pilih rentang <strong>Start Date</strong> (Tanggal Mulai) dan <strong>End Date</strong> (Tanggal Selesai). <em>Contoh: Start Date "01-06-2024" dan End Date "30-06-2024".</em></li>
                                    <li>Isi data proyek, task, dan catatan seperti biasa.</li>
                                    <li><strong>Smart Exclusion:</strong> Centang kotak <strong>Lewati Sabtu & Minggu</strong> atau <strong>Lewati Libur Nasional</strong> agar sistem otomatis melompati hari-hari tersebut. Anda juga bisa memasukkan tanggal cuti manual di kolom <strong>Exclude Tanggal Cuti Tambahan</strong>.</li>
                                    <li><strong>Force Include:</strong> Jika ada hari libur (Sabtu/Minggu/Merah) di mana Anda tetap masuk bekerja (lembur), masukkan tanggal tersebut ke kolom <strong>Paksa Masuk (Lembur Hari Libur)</strong> agar sistem tetap menghitungnya.</li>
                                    <li>Klik <strong>Generate Fast-Track</strong>, sistem akan otomatis melakukan pengisian massal ke Spreadsheet Anda.</li>
                                </ul>
                            </li>
                            <li><strong>Absensi - Presence-Track (Rencana Absensi):</strong>
                                <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <li>Buka menu <strong>Sistem > Settings</strong> dan masukkan link Google Form absensi perusahaan Anda di kolom <strong>URL Google Form Absensi</strong>.</li>
                                    <li>Setelah disimpan, klik menu <strong>Absensi > Presence-Track</strong>. Anda akan melihat form <strong>Buat Rencana Absensi</strong>, tabel daftar rencana, dan *preview* Google Form di sebelah kanan.</li>
                                    <li>Anda dapat membuat rencana absen (misal: "Hadir", "Sakit", "Cuti Tahunan") untuk rentang tanggal tertentu secara massal (Bulk) maupun berdasarkan Jadwal Shift dari Google Sheets.</li>
                                    <li>Setelah rencana dibuat, data akan muncul di <strong>Daftar Rencana Absensi</strong>. Anda bisa mengubah jenis pengajuan langsung di tabel (*Inline Edit*).</li>
                                    <li>Klik tombol <strong><i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Form</strong> pada tabel. Sistem akan secara otomatis mengisi (<em>auto-prefill</em>) Google Form di sebelah kanan sesuai dengan tanggal dan jenis pengajuan yang Anda pilih! Anda tinggal menekan tombol Submit di Google Form tersebut.</li>
                                </ul>
                            </li>
                            <li><strong>Absensi - WA Approval (Pembuat Pesan Izin):</strong>
                                <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <li>Fitur ini digunakan untuk membuat pesan teks *request approval* (minta izin kehadiran) ke atasan via WhatsApp secara otomatis.</li>
                                    <li><strong>Mode Reguler / Dedicated:</strong> Digunakan jika jadwal Anda bersifat tetap/normal (Senin - Jumat). Cukup pilih rentang <em>Start Date</em> dan <em>End Date</em>. Biarkan kotak <strong>Exclude Weekends</strong> tercentang agar hari Sabtu & Minggu tidak dimasukkan ke dalam teks pesan.</li>
                                    <li><strong>Mode Shift / Manual:</strong> Digunakan jika jadwal Anda fleksibel dan ditarik langsung dari Google Sheets.
                                        <ol style="margin-top: 0.3rem; margin-left: 1.2rem;">
                                            <li>Klik tombol <strong><i class="fa-solid fa-cloud-arrow-down"></i> Sync List Sheet/Tab</strong> agar sistem mengambil daftar Sheet dari file Google Sheets Anda.</li>
                                            <li>Pilih <strong>Bulan (Sheet Tab)</strong> dan <strong>Nama Karyawan</strong> Anda. Jadwal shift Anda di bulan tersebut akan langsung ditampilkan.</li>
                                            <li>Klik <strong>[+] Tambah Rentang Tanggal</strong>, lalu masukkan <em>Start Date</em> dan <em>End Date</em> sesuai jadwal Anda.</li>
                                        </ol>
                                    </li>
                                    <li>Klik tombol <strong>Generate Pesan</strong>. Sistem akan menyusun tanggal-tanggal yang Anda pilih menjadi format pengelompokan yang sangat rapi dan mudah dibaca (contoh: <code>20 - 24 Mei 2026</code>, <code>27 - 30 Mei 2026</code>).</li>
                                    <li>Klik tombol hijau <strong>Kirim via WhatsApp</strong> untuk mengirimkannya langsung!</li>
                                </ul>
                            </li>
                            <li><strong>Data Logs:</strong> Di menu ini, Anda bisa melihat semua riwayat input Anda. Anda bisa mencentang kotak <strong>Select All</strong> untuk melakukan <strong>Hapus Terpilih (Bulk Delete)</strong> atau <strong>Set Status (Bulk Status)</strong> secara massal dan aman!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 5: Sinkronisasi ke Zoho</h3>
                        <p>Pastikan semua data di Data Logs berstatus `final`. Buka tab Sync Manager, lalu klik <strong>Start Sync</strong>. Bot akan mengirim semuanya ke Zoho secara otomatis!</p>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 6: Keamanan Akses (SSO)</h3>
                        <p style="margin-bottom: 1rem;">Aplikasi ini menggunakan sistem <strong>Google Single Sign-On (SSO)</strong> yang terintegrasi secara ketat.</p>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Hanya email dengan domain resmi perusahaan (<strong>ITG Indonesia</strong>) yang dizinkan untuk login.</li>
                            <li>Karena menggunakan Google SSO, Anda <strong>tidak perlu</strong> mengingat atau membuat *password* khusus untuk aplikasi ini.</li>
                            <li>Jika ada anggota tim yang *resign* atau emailnya dinonaktifkan oleh Admin IT, aksesnya ke aplikasi ini juga akan otomatis terputus.</li>
                        </ul>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">6. Troubleshooting Error</h3>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Error: <code>REMAINING_LOG_HOURS_DAYS</code>:</strong> Anda sudah melampaui batas input maksimal (24 jam) di satu tanggal.</li>
                            <li><strong>Error: <code>Project tidak ditemukan</code>:</strong> Nama project salah ketik. Gunakan fitur <em>Load Projects</em> untuk memastikannya sama persis.</li>
                            <li><strong>Task tidak ditemukan (Otomatis Dibuat):</strong> Jika nama task yang Anda ketik belum ada di project Zoho tersebut, bot akan <strong>otomatis membuatkan (create) task baru</strong>. Task baru ini akan secara default memiliki status <strong>Open/Active</strong>.</li>
                            <li><strong>Log nyasar ke task yang salah:</strong> Jika Anda memiliki beberapa task dengan nama kembar (misal satu berstatus <em>Closed</em> dan lainnya <em>Open</em>), bot akan selalu memprioritaskan untuk mengisi log ke task yang masih <strong>Open / Active</strong> agar tidak salah masuk ke task yang sudah lama/ditutup.</li>
                        </ul>
                    </div>

                    <div class="lang-en">
                        <div class="guide-manual-only">
                            <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-top: 1rem;">Part 1: Google Sheets API Setup (Google Bot)</h3>
                            <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                                <li>Open <strong>Google Cloud Console</strong> (console.cloud.google.com).</li>
                                <li>Create a new Project (e.g., <em>TrackHub-App</em>).</li>
                                <li>Go to <strong>APIs & Services &gt; Library</strong>. Search for <strong>Google Sheets API</strong> and click <strong>Enable</strong>.</li>
                                <li>Go to <strong>APIs & Services &gt; Credentials</strong>.</li>
                                <li>Click <strong>Create Credentials &gt; Service Account</strong>. Enter a bot name (e.g., <em>zoho-bot</em>) and click Done.</li>
                                <li>Click the newly created Service Account email, go to the <strong>Keys</strong> tab, click <strong>Add Key &gt; Create New Key</strong>, and choose <strong>JSON</strong> format.</li>
                                <li>The JSON file will be downloaded. Open it, <em>Copy</em> all contents, and <em>Paste</em> it into the <strong>Google Service Account JSON</strong> field in the Settings tab.</li>
                                <li><strong>VERY IMPORTANT:</strong> <em>Copy</em> the Service Account email (e.g., <code>zoho-bot@...iam.gserviceaccount.com</code>). Open your Google Sheet, click <strong>Share</strong> in the top right, paste the email, and grant <strong>Editor</strong> access.</li>
                            </ol>
                        </div>

                        <h3 id="part-2" style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-top: 1rem;">Part 2: Zoho Projects API Setup</h3>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Open <strong>Zoho API Console</strong> (api-console.zoho.com).</li>
                            <li>Click <strong>Add Client</strong>, then select <strong>Self Client</strong>.</li>
                            <li>Click <strong>Create</strong>. Zoho will provide a <strong>Client ID</strong> and <strong>Client Secret</strong>. <em>Copy</em> both into the Settings tab.</li>
                            <li>In the Zoho API Console, go to the <strong>Generate Code</strong> tab. Fill out the form exactly like the illustration below:<br>
<div style="background: #ffffff; color: #333; padding: 1.5rem; border-radius: 8px; margin: 1rem 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 650px; position: relative;">
    <div style="position: absolute; top: 0; right: 0; background: #fbbf24; color: #854d0e; font-size: 0.7rem; font-weight: bold; padding: 0.2rem 0.6rem; border-bottom-left-radius: 8px; border-top-right-radius: 8px;">ZOHO API CONSOLE ILLUSTRATION</div>
    <!-- Scope Field -->
    <div style="margin-bottom: 1.5rem;">
        <label style="display: block; font-size: 0.85rem; color: #4b5563; margin-bottom: 0.4rem; font-weight: 600;">Scope</label>
        <div style="position: relative;">
            <textarea readonly style="width: 100%; border: 1px solid #3b82f6; border-radius: 2px; padding: 0.5rem; font-family: monospace; font-size: 0.85rem; color: #1f2937; background: #fff; resize: none; outline: none; height: 65px;">ZohoProjects.tasks.ALL,ZohoProjects.projects.ALL,ZohoProjects.portals.ALL,ZohoProjects.timelogs.ALL</textarea>
            <button onclick="navigator.clipboard.writeText('ZohoProjects.tasks.ALL,ZohoProjects.projects.ALL,ZohoProjects.portals.ALL,ZohoProjects.timelogs.ALL'); const icon = this.querySelector('i'); icon.className='fa-solid fa-check'; this.style.color='#10b981'; setTimeout(() => { icon.className='fa-regular fa-copy'; this.style.color=''; }, 2000);" style="position: absolute; bottom: 0.5rem; right: 0.5rem; background: #f3f4f6; border: 1px solid #d1d5db; color: #4b5563; cursor: pointer; padding: 0.3rem 0.6rem; border-radius: 4px; transition: all 0.2s;" title="Copy to clipboard" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                <i class="fa-regular fa-copy"></i> Copy
            </button>
        </div>
    </div>
    
    <!-- Duration Field -->
    <div style="margin-bottom: 1.5rem;">
        <label style="display: block; font-size: 0.85rem; color: #4b5563; margin-bottom: 0.4rem; font-weight: 600;">Code expiry duration</label>
        <div style="border-bottom: 1px solid #d1d5db; padding: 0.3rem 0; color: #1f2937; display: flex; justify-content: space-between; font-size: 0.95rem;">
            <span>10 minutes</span>
            <i class="fa-solid fa-chevron-down" style="color: #9ca3af; font-size: 0.8rem; margin-top: 4px;"></i>
        </div>
    </div>
    
    <!-- Description Field -->
    <div style="margin-bottom: 0.5rem;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
            <label style="font-size: 0.85rem; color: #4b5563; font-weight: 600;">Description</label>
            <span style="font-size: 0.75rem; color: #3b82f6;">Character count : 0 / 250</span>
        </div>
        <textarea readonly placeholder="Describe what the access token is for." style="width: 100%; border: 1px solid #d1d5db; border-radius: 2px; padding: 0.5rem; font-family: inherit; font-size: 0.95rem; color: #4b5563; background: #fff; resize: none; outline: none; height: 65px;"></textarea>
    </div>
</div>
                            </li>
                            <li>Once the form is filled out exactly as shown above, click the green <strong>Create</strong> button. Select your portal and click <strong>Accept</strong>.</li>
                            <li>Zoho will display a temporary Authorization Code. Copy it immediately.</li>
                            <li>Go to the <strong>Settings</strong> menu in this TrackHub app. Make sure you have entered your <strong>Zoho Client ID</strong> and <strong>Zoho Client Secret</strong>.</li>
                            <li>In the <strong>Auto-Generate Refresh Token</strong> box, <em>Paste</em> the temporary Authorization Code <em>(example: <code>1000.e574a13a804f9...</code>)</em> and click <strong>⚡ Generate</strong>. The token will be automatically created and filled!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 3: Completing App Settings</h3>
                        
                        <div class="guide-auto-only" style="background: rgba(168,85,247,0.05); border: 1px solid rgba(168,85,247,0.3); border-left: 4px solid #a855f7; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                            <h4 style="color: #c084fc; margin-bottom: 0.5rem;"><i class="fa-solid fa-wand-magic-sparkles"></i> Mode: Follow Admin Settings (Automatic)</h4>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">Since you chose Automatic mode, setup is incredibly simple!</p>
                            <ul style="margin-left: 1.5rem; margin-bottom: 0; color: var(--text-main);">
                                <li><strong>Default Google Form Attendance:</strong> Enter your <strong>Default Attendance Name</strong> and select your <strong>Default Attendance Division</strong>. When using the Attendance Plan feature, the Google Form will magically auto-prefill these fields for you!</li>
                                <li><strong>Zoho Portal Name:</strong> Enter your Zoho organization ID (e.g., <code>847721722</code>).</li>
                                <li style="color: #34d399; margin-top: 0.5rem; list-style-type: none; margin-left: -1.5rem;"><i class="fa-solid fa-check-circle"></i> <em>Done! You do NOT need to fill in the Spreadsheet ID, Sheet Name, or Service Account JSON. The system manages them centrally!</em></li>
                            </ul>
                        </div>

                        <div class="guide-manual-only" style="background: rgba(59,130,246,0.05); border: 1px solid rgba(59,130,246,0.3); border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                            <h4 style="color: #60a5fa; margin-bottom: 0.5rem;"><i class="fa-solid fa-screwdriver-wrench"></i> Mode: Manual / Advanced Settings</h4>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">You chose to store data in your own personal Spreadsheet separately.</p>
                            <ul style="margin-left: 1.5rem; margin-bottom: 0; color: var(--text-main);">
                                <li><strong>Google Spreadsheet ID:</strong> Enter the unique ID from your Google Sheet URL.</li>
                                <li><strong>Google Sheet Tab Name:</strong> The name of the sheet tab for your Zoho data (e.g., <code>Sheet1</code> or <code>tasklist</code>).</li>
                                <li><strong>Google Service Account JSON:</strong> Paste the JSON content of your Google bot created in Part 1.</li>
                                <li><strong>Default Google Form Attendance:</strong> Enter your Name and Division for the auto-prefill feature.</li>
                                <li><strong>Zoho Portal Name:</strong> Enter your Zoho organization ID.</li>
                            </ul>
                        </div>
                        <p style="margin-bottom: 2rem;">Click the <strong><i class="fa-solid fa-floppy-disk"></i> Save Settings</strong> button once everything (including Zoho tokens from Part 2) is filled out.</p>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 4: Usage & Features</h3>
                        <p>After setup is complete, use the main menus to manage your work logs:</p>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Daily-Track:</strong> Use this to input single day logs. Click <em><i class="fa-solid fa-rotate"></i> Load Projects</em> to fetch projects directly from Zoho.</li>
                            <li><strong>Fast-Track & Exclude Weekends:</strong> This feature is used if you have the exact same schedule for consecutive days (e.g., working 09:00 - 18:00 for a full month).
                                <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <li>Select your <strong>Start Date</strong> and <strong>End Date</strong>. <em>Example: Start Date "01-06-2024" and End Date "30-06-2024".</em></li>
                                    <li>Fill in the project, task, and notes data as usual.</li>
                                    <li><strong>Smart Exclusion:</strong> Check the <strong>Exclude Weekends</strong> and/or <strong>Exclude National Holidays</strong> boxes to automatically skip those days. You can also add specific leave days in the <strong>Exclude Specific Dates</strong> field.</li>
                                    <li><strong>Force Include:</strong> If you work overtime on a weekend or a holiday, add that date to the <strong>Force Include (Holiday Overtime)</strong> field to guarantee it gets logged.</li>
                                    <li>Click <strong>Generate Fast-Track</strong>, and the bot will instantly generate dozens of log rows into your Google Sheets!</li>
                                </ul>
                            </li>
                            <li><strong>Attendance - Presence-Track (Attendance Plan):</strong>
                                <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <li>Open the <strong>System > Settings</strong> menu and enter your company's Google Form link into the <strong>Google Form Attendance URL</strong> field.</li>
                                    <li>Once saved, click the <strong>Attendance > Presence-Track</strong> menu. You will see the <strong>Create Attendance Plan</strong> form, a list table, and a Google Form preview on the right.</li>
                                    <li>You can create an attendance plan (e.g., "Present", "Sick", "Annual Leave") for a specific date range in Bulk or by pulling directly from your Google Sheets Shift Schedule.</li>
                                    <li>Once a plan is created, it will appear in the <strong>Attendance Plan List</strong> table. You can edit the plan type directly on the table (*Inline Edit*).</li>
                                    <li>Click the <strong><i class="fa-solid fa-arrow-up-right-from-square"></i> Open Form</strong> button on the table. The system will magically auto-prefill the Google Form on the right with your selected date and plan type! You just need to press the Submit button inside the Google Form.</li>
                                </ul>
                            </li>
                            <li><strong>Attendance - WA Approval Generator:</strong>
                                <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <li>This feature generates a formatted WhatsApp text message to request attendance approval from your manager.</li>
                                    <li><strong>Regular / Dedicated Mode:</strong> Use this if you have a fixed schedule (Monday - Friday). Simply pick a <em>Start Date</em> and <em>End Date</em>. Keep the <strong>Exclude Weekends</strong> box checked so that Saturdays and Sundays are skipped automatically.</li>
                                    <li><strong>Shift / Manual Mode:</strong> Use this if your schedule is flexible and managed via Google Sheets.
                                        <ol style="margin-top: 0.3rem; margin-left: 1.2rem;">
                                            <li>Click <strong><i class="fa-solid fa-cloud-arrow-down"></i> Sync List Sheet/Tab</strong> to load available tabs from your Google Sheet.</li>
                                            <li>Select the <strong>Month (Sheet Tab)</strong> and your <strong>Name</strong>. Your shift schedule will be displayed on-screen.</li>
                                            <li>Click <strong>[+] Add Date Range</strong> and input the dates corresponding to your shifts.</li>
                                        </ol>
                                    </li>
                                    <li>Click <strong>Generate Message</strong>. The system will intelligently group consecutive dates into a highly readable format (e.g., <code>20 - 24 May 2026</code>).</li>
                                    <li>Click the green <strong>Send via WhatsApp</strong> button to dispatch it instantly!</li>
                                </ul>
                            </li>
                            <li><strong>Data Logs:</strong> View all your input history here. Use the <strong>Select All</strong> checkbox to perform safe <strong>Bulk Delete</strong> or <strong>Bulk Status Updates</strong>!</li>
                            <li><strong>Sync Manager:</strong> Ensure all logs in Data Logs are marked as `final`. Open the Sync Manager tab and click <strong>Start Sync</strong> to push everything to Zoho automatically!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 5: Access Security (SSO)</h3>
                        <p style="margin-bottom: 1rem;">This app uses a strictly integrated <strong>Google Single Sign-On (SSO)</strong> system.</p>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Only official company emails (<strong>@itgroupinc.asia</strong>) are allowed to log in.</li>
                            <li>Because it uses Google SSO, you <strong>do not need</strong> to remember or create a specific password for this application.</li>
                            <li>If a team member resigns or their email is disabled by IT, their access to this app will be automatically revoked.</li>
                        </ul>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">6. Troubleshooting</h3>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Error: <code>REMAINING_LOG_HOURS_DAYS</code>:</strong> You exceeded the maximum 24-hour log limit for a single day.</li>
                            <li><strong>Error: <code>Project not found</code>:</strong> Project name is typed incorrectly. Use <em>Load Projects</em> to ensure an exact match.</li>
                            <li><strong>Task not found (Auto-Created):</strong> If the task name you entered doesn't exist in the Zoho project, the bot will <strong>automatically create a new task</strong>. This newly created task will default to an <strong>Open/Active</strong> status.</li>
                            <li><strong>Log went to an old task:</strong> If you have multiple tasks with the exact same name (e.g., one is <em>Closed</em> and another is <em>Open</em>), the bot smartly prioritizes logging time to the one that is still <strong>Open / Active</strong> to prevent logging into a closed task.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const selector = document.getElementById('guideModeSelector');
                    const modeBtns = document.querySelectorAll('.guide-mode-btn');
                    
                    if (selector && modeBtns.length > 0) {
                        
                        // Handle button clicks
                        modeBtns.forEach(btn => {
                            btn.addEventListener('click', () => {
                                // Update hidden selector value
                                selector.value = btn.getAttribute('data-mode');
                                
                                // Update button UI
                                modeBtns.forEach(b => b.classList.remove('active'));
                                btn.classList.add('active');
                                
                                // Trigger change event
                                selector.dispatchEvent(new Event('change'));
                            });
                        });
                        
                        // Handle change event to toggle content visibility
                        selector.addEventListener('change', (e) => {
                            const mode = e.target.value;
                            const autoElements = document.querySelectorAll('.guide-auto-only');
                            const manualElements = document.querySelectorAll('.guide-manual-only');
                            
                            if (mode === 'auto') {
                                autoElements.forEach(el => el.style.display = '');
                                manualElements.forEach(el => el.style.display = 'none');
                            } else {
                                autoElements.forEach(el => el.style.display = 'none');
                                manualElements.forEach(el => el.style.display = '');
                            }
                        });
                        
                        // Sync with actual settings if possible
                        const currentSettings = localStorage.getItem('trackhub_settings');
                        if (currentSettings) {
                            try {
                                const parsed = JSON.parse(currentSettings);
                                if (parsed.googleSheetMode) {
                                    const targetBtn = document.querySelector(`.guide-mode-btn[data-mode="${parsed.googleSheetMode}"]`);
                                    if(targetBtn) targetBtn.click();
                                } else {
                                    selector.dispatchEvent(new Event('change'));
                                }
                            } catch(err) {
                                selector.dispatchEvent(new Event('change'));
                            }
                        } else {
                            selector.dispatchEvent(new Event('change'));
                        }
                    }
                });
            </script>
        </section>