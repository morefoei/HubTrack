<?php
$v = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrackHub - Login</title>
    <link rel="icon" type="image/png" href="assets/css/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= $v ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#0f172a">
    <style>
        body {
            background: var(--bg-color);
            min-height: 100vh;
            display: flex;
            flex-direction: row;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .login-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            overflow-y: auto;
        }
        .nav-btn:active, .nav-link:active {
            transform: scale(0.95);
        }
        .view-section {
            display: none;
            width: 100%;
            max-width: 900px;
            animation: fadeSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .view-section.active {
            display: block;
        }
        @keyframes fadeSlideUp {
            0% { opacity: 0; transform: translateY(15px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .top-nav-right {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--panel-border);
            background: var(--panel-bg);
            backdrop-filter: var(--glass-blur);
            position: sticky;
            top: 0;
            z-index: 90;
        }
    </style>
</head>
<body>

    <header>
        <div class="header-top">
            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 1.5rem; width: 100%;">
                <div style="display: flex; justify-content: center; width: 100%; align-items: center;">
                    <span class="logo"><i class="fa-solid fa-rocket" style="-webkit-text-fill-color: initial; color: #f43f5e;"></i> <span class="nav-text">TrackHub</span></span>
                </div>
            </div>
        </div>
        <nav id="mainNav">
            <button class="nav-btn" onclick="switchView('docs-view', this)" title="Dokumentasi"><i class="fa-solid fa-book-open"></i> <span class="nav-text">Dokumentasi</span></button>
        </nav>
        
        <div class="desktop-only" style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--panel-border); display: flex; justify-content: center; width: 100%;">
            <button id="sidebarToggleBtn" style="background: transparent; border: none; border-radius: 0.5rem; color: var(--text-muted); padding: 0.8rem; cursor: pointer; font-size: 1.1rem; transition: all 0.2s; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" title="Toggle Sidebar" onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.color='var(--text-main)';" onmouseout="this.style.background='transparent'; this.style.color='var(--text-muted)';">
                <i class="fa-solid fa-angles-left toggle-icon"></i>
            </button>
        </div>
    </header>

    <main style="flex: 1; display: flex; flex-direction: column; padding: 0; overflow: hidden;">
        <div class="top-nav-right">
            <button class="nav-btn active" onclick="switchView('login-view', this)" style="background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
        </div>

        <div class="login-container">
        
        <!-- LOGIN VIEW -->
        <div id="login-view" class="view-section active" style="max-width: 400px;">
            <div class="card" style="padding: 2.5rem 2rem;">
                <div style="text-align: center; margin-bottom: 2rem; display: flex; flex-direction: column; align-items: center;">
                    <div class="logo" style="font-size: 2.5rem; justify-content: center; margin-bottom: 0.5rem;">
                        <i class="fa-solid fa-rocket" style="-webkit-text-fill-color: initial; color: #f43f5e;"></i> TrackHub
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0;">Log in or create a new profile</p>
                </div>
                <form id="loginForm">
                    <div class="form-group">
                        <label>Username / Profile Name</label>
                        <input type="text" id="loginUsername" placeholder="e.g. udin" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="loginPassword" placeholder="Enter password (creates new if not exist)" required autocomplete="current-password">
                    </div>
                    <button type="submit" id="loginBtn" style="width: 100%; margin-top: 1rem;"><i class="fa-solid fa-right-to-bracket"></i> Login / Register</button>
                    <div style="text-align: center; margin-top: 1rem; color: var(--text-muted); font-size: 0.8rem;">
                        *Jika profile belum ada, akan otomatis dibuat.
                    </div>
                </form>
            </div>
        </div>

        <!-- DOCS VIEW -->
        <div id="docs-view" class="view-section">
            <div class="card" style="line-height: 1.6;">
                <div class="card-header">
                    <h2 class="card-title"><i class="fa-solid fa-book-open" style="color: var(--primary);"></i> Dokumentasi Instalasi & Penggunaan</h2>
                </div>
                <div style="padding: 1rem; max-height: 70vh; overflow-y: auto;">
                    <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-top: 0;">Bagian 1: Pengaturan Google Sheets API</h3>
                    <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                        <li>Buka <strong>Google Cloud Console</strong> (console.cloud.google.com).</li>
                        <li>Buat Project baru (misal: <em>Zoho-Sync-App</em>).</li>
                        <li>Pergi ke menu <strong>APIs & Services &gt; Library</strong>. Cari <strong>Google Sheets API</strong> lalu klik <strong>Enable</strong>.</li>
                        <li>Pergi ke menu <strong>APIs & Services &gt; Credentials</strong>.</li>
                        <li>Klik <strong>Create Credentials &gt; Service Account</strong>. Isi nama bot (misal: <em>zoho-bot</em>) dan klik Done.</li>
                        <li>Klik email Service Account yang baru dibuat, masuk ke tab <strong>Keys</strong>, klik <strong>Add Key &gt; Create New Key</strong>, pilih format <strong>JSON</strong>.</li>
                        <li>File JSON akan ter-download ke komputer Anda. Buka file tersebut dengan Notepad, lalu <em>Copy</em> semua isinya dan <em>Paste</em> ke kolom <strong>Google Service Account JSON</strong> di tab Settings aplikasi kita.</li>
                        <li><strong>SANGAT PENTING:</strong> <em>Copy</em> alamat email Service Account bot Anda. Buka file Google Sheets Anda, klik tombol <strong>Share (Bagikan)</strong>, lalu paste email bot tersebut dan berikan akses sebagai <strong>Editor</strong>.</li>
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
                        <li>Pada kotak <strong>Auto-Generate Refresh Token</strong> di menu Settings, <em>Paste</em> kode Authorization sementara tersebut, lalu klik <strong>⚡ Generate</strong>. Token akan otomatis dibuat dan tersimpan!</li>
                    </ol>

                    <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Bagian 3: Melengkapi Tab Settings</h3>
                    <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                        <li><strong>Google Spreadsheet ID:</strong> Salin ID panjang dari URL file Google Sheets Anda (terletak di antara <code>/d/</code> dan <code>/edit</code>).</li>
                        <li><strong>Google Sheet Tab Name:</strong> Nama Sheet (misal: <code>Sheet1</code>).</li>
                        <li><strong>Zoho Portal Name:</strong> ID organisasi Zoho Anda (contoh: <code>847721722</code>).</li>
                        <li><strong>Profile Password (Wajib):</strong> Buat kata sandi untuk melindungi Token Anda.</li>
                    </ul>
                </div>
            </div>
        </div>

        </div>

    </main>

    <script>
        // Clean up login.php from URL for a cleaner look
        if (window.location.pathname.endsWith('login.php')) {
            const cleanUrl = window.location.pathname.replace(/login\.php$/, 'login') + window.location.search;
            window.history.replaceState(null, '', cleanUrl);
        }

        // Check if already logged in
        if (sessionStorage.getItem('zohoProfile') && sessionStorage.getItem('zohoPassword')) {
            window.location.href = './';
        }

        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', () => {
                document.body.classList.toggle('sidebar-minimized');
                const icon = sidebarToggleBtn.querySelector('.toggle-icon');
                if (document.body.classList.contains('sidebar-minimized')) {
                    icon.classList.remove('fa-angles-left');
                    icon.classList.add('fa-angles-right');
                } else {
                    icon.classList.remove('fa-angles-right');
                    icon.classList.add('fa-angles-left');
                }
            });
        }

        function switchView(viewId, element) {
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.getElementById(viewId).classList.add('active');
            
            document.querySelectorAll('.nav-btn').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const p = document.getElementById('loginUsername').value.trim();
            const pwd = document.getElementById('loginPassword').value;
            const btn = document.getElementById('loginBtn');
            
            if (!p || !pwd) return;


            
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            btn.disabled = true;

            try {
                const res = await fetch(`api/api.php?action=get_settings`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ profile: p, password: pwd })
                });
                
                const data = await res.json();
                
                if (data.success === false) {
                    alert(data.message || 'Gagal login!');
                } else {
                    // Success login
                    sessionStorage.setItem('zohoProfile', p);
                    sessionStorage.setItem('zohoPassword', pwd);
                    window.location.href = './';
                }
            } catch (err) {
                alert('Terjadi kesalahan koneksi ke server.');
            }
            
            btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Login / Register';
            btn.disabled = false;
        });
    </script>
</body>
</html>
