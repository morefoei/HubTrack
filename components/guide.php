<section id="guide-view" class="view-section">
            <div class="card" style="max-width: 900px; margin: 0 auto; line-height: 1.6;">
                <div class="card-header">
                    <h2 class="card-title"><i class="fa-solid fa-book-open" style="color: var(--primary-color);"></i> Dokumentasi Instalasi & Penggunaan HubTrack</h2>
                </div>
                <div style="padding: 1rem; max-height: 70vh; overflow-y: auto;">

                    <div class="lang-id">
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

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 2: Pengaturan Zoho Projects API</h3>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Buka <strong>Zoho API Console</strong> (api-console.zoho.com).</li>
                            <li>Klik <strong>Add Client</strong>, lalu pilih <strong>Self Client</strong>.</li>
                            <li>Klik <strong>Create</strong>. Zoho akan memberikan Anda <strong>Client ID</strong> dan <strong>Client Secret</strong>. <em>Copy</em> keduanya ke tab Settings aplikasi kita.</li>
                            <li>Di Zoho API Console, buka tab <strong>Generate Code</strong>. Masukkan scope berikut secara persis:<br>
<pre style="background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 8px; overflow-x: auto; margin-top: 0.5rem; border: 1px solid rgba(255,255,255,0.05);"><code style="color: #fcd34d;">ZohoProjects.tasks.ALL,ZohoProjects.projects.ALL,ZohoProjects.portals.ALL,ZohoProjects.timelogs.ALL</code></pre>
                            </li>
                            <li>Pilih durasi <strong>10 Minutes</strong> atau lebih, lalu tuliskan deskripsi bebas, dan klik <strong>Create</strong>. Pilih portal organisasi Anda dan tekan <strong>Accept/Terima</strong>.</li>
                            <li>Zoho akan menampilkan kode Authorization sementara (Authorization Code). Segera <em>copy</em> kode tersebut.</li>
                            <li>Buka menu <strong>Settings</strong> di aplikasi HubTrack ini. Pastikan Anda sudah menyalin <strong>Zoho Client ID</strong> dan <strong>Zoho Client Secret</strong>.</li>
                            <li>Pada kotak <strong>Auto-Generate Refresh Token</strong> di menu Settings, <em>Paste</em> kode Authorization sementara tersebut <em>(contoh: <code>1000.e574a13a804f9...</code>)</em>, lalu klik <strong>⚡ Generate</strong>. Token akan otomatis dibuat dan tersimpan!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 3: Melengkapi Tab Settings di Aplikasi</h3>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Google Spreadsheet ID:</strong> Salin ID panjang dari URL file Google Sheets Anda (terletak di antara <code>/d/</code> dan <code>/edit</code>).</li>
                            <li><strong>Google Sheet Tab Name:</strong> Nama Sheet di bagian bawah layar (misal: <code>Sheet1</code> atau <code>tasklist</code>).</li>
                            <li><strong>Zoho Portal Name:</strong> ID organisasi Zoho Anda (contoh: <code>847721722</code>).</li>
                            <li><strong>URL Google Form Absensi (Opsional):</strong> Masukkan link Form absen HR/HCA di sini agar Anda bisa absen langsung di aplikasi.</li>
                            <li><strong>Profile Password (Wajib):</strong> Buat kata sandi agar tidak ada orang lain yang bisa membajak atau melihat Token/Pengaturan rahasia Anda.</li>
                            <li>Setelah semua terisi, klik tombol <strong>Save Settings</strong>.</li>
                        </ul>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 4: Cara Penggunaan & Fitur Canggih</h3>
                        <p>Setelah pengaturan selesai, Anda bisa menginput data jam kerja Anda lewat menu utama:</p>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Daily-Track:</strong> Gunakan menu ini untuk menginput 1 hari kerja. Klik tulisan <em><i class="fa-solid fa-rotate"></i> Load Projects</em> pada kolom Project Name untuk mengambil daftar nama project langsung dari Zoho.</li>
                            <li><strong>Fast-Track & Exclude Weekends:</strong> Fitur ini digunakan jika Anda memiliki jadwal yang sama persis untuk beberapa hari berturut-turut (misal: masuk jam 09:00 - 18:00 selama 1 bulan penuh).
                                <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <li>Pilih rentang <strong>Start Date</strong> (Tanggal Mulai) dan <strong>End Date</strong> (Tanggal Selesai). <em>Contoh: Start Date "01-06-2024" dan End Date "30-06-2024".</em></li>
                                    <li>Isi data proyek, task, dan catatan seperti biasa.</li>
                                    <li>Centang kotak <strong>Lewati Sabtu & Minggu (Exclude Weekends)</strong>. Mesin kalender internal kami akan mendeteksi hari Sabtu & Minggu secara cerdas dan secara otomatis melewati hari tersebut! (Sangat berguna jika Anda tidak bekerja di akhir pekan).</li>
                                    <li>Klik <strong>Tambah Massal</strong>, sistem akan otomatis melakukan loop pengisian tanggal ke Spreadsheet satu per satu.</li>
                                </ul>
                            </li>
                            <li><strong>Presence-Track:</strong> Fitur akses cepat untuk mengisi form absensi kehadiran kantor secara terintegrasi (URL form diatur di halaman Settings).</li>
                            <li><strong>WA Approval (Khusus Shift):</strong> Fitur untuk *generate* pesan permintaan *approval* atasan via WhatsApp. Anda cukup memilih *Tab* bulan dan Nama Anda, lalu sistem akan secara otomatis menarik jadwal *shift* Anda dari Sheet Shift terpisah dan menyajikannya dalam kelompok tanggal. Klik tombol <strong>[+] Gunakan</strong> untuk langsung menyalin jadwal tersebut ke form tanpa mengetik manual.</li>
                            <li><strong>Data Logs:</strong> Di menu ini, Anda bisa melihat semua riwayat input Anda. Anda bisa mencentang kotak <strong>Select All</strong> untuk melakukan <strong>Hapus Terpilih (Bulk Delete)</strong> atau <strong>Set Status (Bulk Status)</strong> secara massal dan aman!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 5: Sinkronisasi ke Zoho</h3>
                        <p>Pastikan semua data di Data Logs berstatus `final`. Buka tab Sync Manager, lalu klik <strong>Start Sync</strong>. Bot akan mengirim semuanya ke Zoho secara otomatis!</p>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 6: Lupa Password & Keamanan Profil</h3>
                        <p style="margin-bottom: 1rem;">Aplikasi ini mendukung sistem banyak profil (multi-user) yang diproteksi kata sandi secara independen. Jika Anda mengalami kendala saat masuk:</p>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Jika Anda <strong>lupa password</strong> profil, Anda tidak akan bisa mengakses <em>dashboard</em> maupun mengubah pengaturan sinkronisasi Anda.</li>
                            <li>Demi alasan keamanan dan privasi data masing-masing anggota tim, tidak ada tombol pemulihan password secara mandiri di halaman depan.</li>
                            <li><strong>Solusi:</strong> Silakan hubungi <strong>Admin</strong> atau koordinator sistem Anda untuk meminta <em>Reset Password</em>. Setelah di-reset oleh Admin, Anda dapat kembali masuk dan mengatur password baru.</li>
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
                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-top: 1rem;">Part 1: Google Sheets API Setup (Google Bot)</h3>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Open <strong>Google Cloud Console</strong> (console.cloud.google.com).</li>
                            <li>Create a new Project (e.g., <em>HubTrack-App</em>).</li>
                            <li>Go to <strong>APIs & Services &gt; Library</strong>. Search for <strong>Google Sheets API</strong> and click <strong>Enable</strong>.</li>
                            <li>Go to <strong>APIs & Services &gt; Credentials</strong>.</li>
                            <li>Click <strong>Create Credentials &gt; Service Account</strong>. Enter a bot name (e.g., <em>zoho-bot</em>) and click Done.</li>
                            <li>Click the newly created Service Account email, go to the <strong>Keys</strong> tab, click <strong>Add Key &gt; Create New Key</strong>, and choose <strong>JSON</strong> format.</li>
                            <li>The JSON file will be downloaded. Open it, <em>Copy</em> all contents, and <em>Paste</em> it into the <strong>Google Service Account JSON</strong> field in the Settings tab.</li>
                            <li><strong>VERY IMPORTANT:</strong> <em>Copy</em> the Service Account email (e.g., <code>zoho-bot@...iam.gserviceaccount.com</code>). Open your Google Sheet, click <strong>Share</strong> in the top right, paste the email, and grant <strong>Editor</strong> access.</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 2: Zoho Projects API Setup</h3>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Open <strong>Zoho API Console</strong> (api-console.zoho.com).</li>
                            <li>Click <strong>Add Client</strong>, then select <strong>Self Client</strong>.</li>
                            <li>Click <strong>Create</strong>. Zoho will provide a <strong>Client ID</strong> and <strong>Client Secret</strong>. <em>Copy</em> both into the Settings tab.</li>
                            <li>In the Zoho API Console, go to the <strong>Generate Code</strong> tab. Enter the following exact scope:<br>
<pre style="background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 8px; overflow-x: auto; margin-top: 0.5rem; border: 1px solid rgba(255,255,255,0.05);"><code style="color: #fcd34d;">ZohoProjects.tasks.ALL,ZohoProjects.projects.ALL,ZohoProjects.portals.ALL,ZohoProjects.timelogs.ALL</code></pre>
                            </li>
                            <li>Select a duration of <strong>10 Minutes</strong> or more, enter any description, and click <strong>Create</strong>. Select your portal and click <strong>Accept</strong>.</li>
                            <li>Zoho will display a temporary Authorization Code. Copy it immediately.</li>
                            <li>Go to the <strong>Settings</strong> menu in this HubTrack app. Make sure you have entered your <strong>Zoho Client ID</strong> and <strong>Zoho Client Secret</strong>.</li>
                            <li>In the <strong>Auto-Generate Refresh Token</strong> box, <em>Paste</em> the temporary Authorization Code <em>(example: <code>1000.e574a13a804f9...</code>)</em> and click <strong>⚡ Generate</strong>. The token will be automatically created and filled!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 3: Completing App Settings</h3>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Google Spreadsheet ID:</strong> Copy the long ID from your Google Sheets URL (located between <code>/d/</code> and <code>/edit</code>).</li>
                            <li><strong>Google Sheet Tab Name:</strong> The name of the sheet tab at the bottom (e.g., <code>Sheet1</code> or <code>tasklist</code>).</li>
                            <li><strong>Zoho Portal Name:</strong> Your Zoho organization ID (e.g., <code>847721722</code>).</li>
                            <li><strong>Google Form Attendance URL (Optional):</strong> Enter the HR/HCA Google Form link here to show it in the Attendance tab.</li>
                            <li><strong>Profile Password (Required):</strong> Set a password to protect your account and tokens from other users on this server.</li>
                            <li>Click <strong>Save Settings</strong> once everything is filled out.</li>
                        </ul>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 4: Usage & Features</h3>
                        <p>After setup is complete, use the main menus to manage your work logs:</p>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Daily-Track:</strong> Use this to input single day logs. Click <em><i class="fa-solid fa-rotate"></i> Load Projects</em> to fetch projects directly from Zoho.</li>
                            <li><strong>Fast-Track & Exclude Weekends:</strong> This feature is used if you have the exact same schedule for consecutive days (e.g., working 09:00 - 18:00 for a full month).
                                <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <li>Select your <strong>Start Date</strong> and <strong>End Date</strong>. <em>Example: Start Date "01-06-2024" and End Date "30-06-2024".</em></li>
                                    <li>Fill in the project, task, and notes data as usual.</li>
                                    <li>Check the <strong>Exclude Weekends</strong> box. Our internal calendar engine will intelligently detect Saturdays & Sundays and automatically skip those days! (Extremely useful if you don't work on weekends).</li>
                                    <li>Click <strong>Generate Fast-Track</strong>, and the bot will instantly generate dozens of log rows into your Google Sheets!</li>
                                </ul>
                            </li>
                            <li><strong>Presence-Track:</strong> Access this tab every morning to fill out your daily attendance. (Requires a Google Form URL in Settings).</li>
                            <li><strong>Data Logs:</strong> View all your input history here. Use the <strong>Select All</strong> checkbox to perform safe <strong>Bulk Delete</strong> or <strong>Bulk Status Updates</strong>!</li>
                            <li><strong>Sync Manager:</strong> Ensure all logs in Data Logs are marked as `final`. Open the Sync Manager tab and click <strong>Start Sync</strong> to push everything to Zoho automatically!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 5: Forgot Password & Security</h3>
                        <p style="margin-bottom: 1rem;">This app supports a multi-user (multi-tenant) system protected by independent passwords. If you have trouble logging in:</p>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>If you <strong>forget your profile password</strong>, you won't be able to access the dashboard or sync settings.</li>
                            <li>For privacy and security reasons, there is no automated self-recovery button on the front page.</li>
                            <li><strong>Solution:</strong> Please contact your <strong>Admin</strong> or system coordinator to request a <em>Password Reset</em>. Afterwards, you can log in and set a new password.</li>
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
        </section>