<?php
$v = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HubTrack - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= $v ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#0f172a">
    <style>
        body {
            background: var(--bg-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }
        .login-header {
            padding: 1rem 2rem;
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--panel-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .login-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .nav-menu {
            display: flex;
            gap: 1rem;
        }
        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            font-weight: 500;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .view-section {
            display: none;
            width: 100%;
            max-width: 900px;
            animation: fadeIn 0.3s ease;
        }
        .view-section.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <header class="login-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span class="logo"><i class="fa-solid fa-rocket" style="-webkit-text-fill-color: initial; color: #f43f5e;"></i> HubTrack</span>
        </div>
        <div class="nav-menu">
            <div class="nav-link active" onclick="switchView('login-view', this)"><i class="fa-solid fa-right-to-bracket"></i> Login</div>
            <div class="nav-link" onclick="switchView('docs-view', this)"><i class="fa-solid fa-book-open"></i> Dokumentasi</div>
        </div>
    </header>

    <div class="login-container">
        
        <!-- LOGIN VIEW -->
        <div id="login-view" class="view-section active" style="max-width: 400px;">
            <div class="card" style="padding: 2.5rem 2rem;">
                <div style="text-align: center; margin-bottom: 2rem; display: flex; flex-direction: column; align-items: center;">
                    <div class="logo" style="font-size: 2.5rem; justify-content: center; margin-bottom: 0.5rem;">
                        <i class="fa-solid fa-rocket" style="-webkit-text-fill-color: initial; color: #f43f5e;"></i> HubTrack
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

    <script>
        // Check if already logged in
        if (sessionStorage.getItem('zohoProfile') && sessionStorage.getItem('zohoPassword')) {
            window.location.href = 'index.php';
        }

        function switchView(viewId, element) {
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.getElementById(viewId).classList.add('active');
            
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const p = document.getElementById('loginUsername').value.trim();
            const pwd = document.getElementById('loginPassword').value;
            const btn = document.getElementById('loginBtn');
            
            if (!p || !pwd) return;

            if (p === 'superman' && pwd === 'musikrock1') {
                try {
                    const res = await fetch(`api/api.php?action=get_all_profiles`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ profile: 'superman', password: 'musikrock1' })
                    });
                    const data = await res.json();
                    if (data.success) {
                        const targetUser = prompt('📋 DAFTAR USER YANG TERDAFTAR:\n- ' + data.profiles.join('\n- ') + '\n\nKetik username yang ingin di-RESET passwordnya:');
                        if (targetUser) {
                            const resetRes = await fetch(`api/api.php?action=reset_password`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ profile: 'superman', password: 'musikrock1', targetUser: targetUser.trim() })
                            });
                            const resetData = await resetRes.json();
                            alert(resetData.message);
                        }
                    } else {
                        alert('Akses Admin Ditolak!');
                    }
                } catch (err) {
                    alert('Gagal mengambil data.');
                }
                return;
            }
            
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
                    window.location.href = 'index.php';
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
