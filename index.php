<?php
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrackHub</title>
    <link rel="icon" type="image/png" href="assets/css/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- PWA / Android App Integration -->
    <link rel="manifest" href="components/pwa/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="apple-touch-icon" href="assets/css/img/logo.png">
    <script>
        const BASE_URL = '<?= $base_url ?>';
        if (!sessionStorage.getItem('zohoProfile') || !sessionStorage.getItem('zohoPassword')) {
            window.location.href = BASE_URL + 'login.php';
        }
        
        const savedTheme = localStorage.getItem('TrackHubTheme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('components/pwa/sw.js', { scope: '/' })
                    .then(registration => {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    }, err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
</head>

<body>

    <header>
        <div class="header-top">
            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 1.5rem; width: 100%;">
                <div style="display: flex; justify-content: center; width: 100%; align-items: center;">
                    <span class="logo"><i class="fa-solid fa-rocket" style="-webkit-text-fill-color: initial; color: #f43f5e;"></i> <span class="nav-text">TrackHub</span></span>
                </div>
            </div>
            <button id="mobileMenuBtn" style="display: none; background: transparent; border: none; color: var(--text-main); font-size: 1.5rem; cursor: pointer; padding: 0.5rem; position: absolute; right: 1rem; top: 1rem;"><i class="fa-solid fa-bars"></i></button>
        </div>
        <nav id="mainNav">
            <details class="nav-dropdown" open>
                <summary class="nav-dropdown-summary" title="Zoho Sync"><i class="fa-solid fa-cloud-arrow-up"></i> <span class="nav-text">Zoho Sync</span> <i class="fa-solid fa-chevron-down dropdown-icon nav-text"></i></summary>
                <div class="dropdown-content">
                    <button class="nav-btn active" data-target="logs-view" title="Daily-Track"><i class="fa-solid fa-pen-to-square"></i> <span class="nav-text">Daily-Track</span></button>
                    <button class="nav-btn" data-target="bulk-view" title="Fast-Track"><i class="fa-solid fa-calendar-days"></i> <span class="nav-text">Fast-Track</span></button>
                    <button class="nav-btn" data-target="data-view" title="Data Logs"><i class="fa-solid fa-table"></i> <span class="nav-text">Data Logs</span></button>
                    <button class="nav-btn" data-target="tasks-view" title="Task Manager"><i class="fa-solid fa-list-check"></i> <span class="nav-text">Task Manager</span></button>
                    <button class="nav-btn" data-target="sync-view" title="Sync Manager"><i class="fa-solid fa-rotate"></i> <span class="nav-text">Sync Manager</span></button>
                </div>
            </details>
            
            <details class="nav-dropdown">
                <summary class="nav-dropdown-summary" title="Absensi"><i class="fa-solid fa-clipboard-user"></i> <span class="nav-text">Absensi</span> <i class="fa-solid fa-chevron-down dropdown-icon nav-text"></i></summary>
                <div class="dropdown-content">
                    <button class="nav-btn" data-target="absen-view" title="Presence-Track"><i class="fa-solid fa-user-check"></i> <span class="nav-text">Presence-Track</span></button>
                    <button class="nav-btn" data-target="wa-approval-view" title="WA Approval"><i class="fa-brands fa-whatsapp"></i> <span class="nav-text">WA Approval</span></button>
                </div>
            </details>

            <details class="nav-dropdown">
                <summary class="nav-dropdown-summary" title="Sistem"><i class="fa-solid fa-server"></i> <span class="nav-text"><span class="lang-en">System</span><span class="lang-id">Sistem</span></span> <i class="fa-solid fa-chevron-down dropdown-icon nav-text"></i></summary>
                <div class="dropdown-content">
                    <button class="nav-btn" data-target="analytics-view" title="Analytics"><i class="fa-solid fa-chart-pie"></i> <span class="nav-text">Analytics</span></button>
                    <button class="nav-btn" data-target="settings-view" title="Pengaturan"><i class="fa-solid fa-gear"></i> <span class="nav-text"><span class="lang-en">Settings</span><span class="lang-id">Pengaturan</span></span></button>
                    <button class="nav-btn" data-target="guide-view" title="Dokumentasi"><i class="fa-solid fa-book"></i> <span class="nav-text">Dokumentasi</span></button>
                    <button class="nav-btn admin-only-btn" data-target="admin-global-view" title="Global Config" style="display: none;"><i class="fa-solid fa-globe"></i> <span class="nav-text">Global Config</span></button>
                    <button class="nav-btn admin-only-btn" data-target="admin-users-view" title="Users Manager" style="display: none;"><i class="fa-solid fa-users-gear"></i> <span class="nav-text">Users Manager</span></button>
                </div>
            </details>
            
            <button class="nav-btn" data-target="about-view" title="About" style="width: 100%; text-align: left; padding: 0.6rem 0.8rem; font-size: 0.95rem; font-weight: 600; gap: 0.5rem;"><i class="fa-solid fa-circle-info"></i> <span class="nav-text"><span class="lang-en">About</span><span class="lang-id">Tentang</span></span></button>
        </nav>
        
        <div class="desktop-only" style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--panel-border); display: flex; justify-content: center; width: 100%;">
            <button id="sidebarToggleBtn" style="background: transparent; border: none; border-radius: 0.5rem; color: var(--text-muted); padding: 0.8rem; cursor: pointer; font-size: 1.1rem; transition: all 0.2s; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" title="Toggle Sidebar" onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.color='var(--text-main)';" onmouseout="this.style.background='transparent'; this.style.color='var(--text-muted)';">
                <i class="fa-solid fa-angles-left toggle-icon"></i>
            </button>
        </div>
    </header>

    <main>
        <div class="top-nav-right">
            <div class="profile-section" style="display: flex; gap: 0.8rem; align-items: center;">
                <button id="themeToggleBtn" class="top-action-btn" title="Ganti Mode Malam/Siang" onclick="toggleTheme()">
                    <i class="fa-solid fa-moon" id="themeIcon"></i>
                </button>
                <button id="profileDisplay" class="top-action-btn profile-btn" title="Logout Aplikasi">
                    <script>
                        var pic = sessionStorage.getItem('zohoPicture');
                        if (pic && pic !== 'undefined' && pic.trim() !== '') {
                            document.write('<img src="' + pic + '" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;" referrerpolicy="no-referrer">');
                        } else {
                            document.write('<i class="fa-solid fa-user"></i>');
                        }
                    </script>
                    <span id="profileNameDisplay"><script>var up = sessionStorage.getItem('zohoProfile') || 'Profile'; document.write(up);</script></span> 
                    <i class="fa-solid fa-sign-out-alt"></i>
                </button>
                <button id="langToggleBtn" class="top-action-btn" title="Ubah Bahasa / Change Language">ID</button>
            </div>
        </div>

        <script>
            function toggleTheme() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('TrackHubTheme', newTheme);
                
                const icon = document.getElementById('themeIcon');
                if (newTheme === 'dark') {
                    icon.className = 'fa-solid fa-sun';
                } else {
                    icon.className = 'fa-solid fa-moon';
                }
            }
            
            // Set initial icon on load
            window.addEventListener('DOMContentLoaded', () => {
                if (document.documentElement.getAttribute('data-theme') === 'dark') {
                    document.getElementById('themeIcon').className = 'fa-solid fa-sun';
                }
            });
        </script>

        <div class="main-content-wrapper">
            <!-- Time Logs View -->
            <?php include 'sync/logs.php'; ?>

            <!-- Data Logs View -->
            <?php include 'sync/data.php'; ?>

            <!-- Task Manager View -->
            <?php include 'sync/tasks.php'; ?>

            <!-- Analytics View -->
            <?php include 'components/analytics.php'; ?>

            <!-- Bulk Input View -->
            <?php include 'sync/bulk.php'; ?>

            <!-- Sync View -->
            <?php include 'sync/sync_manager.php'; ?>

            <!-- Settings View -->
            <?php include 'components/settings.php'; ?>

            <!-- Absensi View -->
            <?php include 'absen/absen.php'; ?>

            <!-- WA Approval View -->
            <?php include 'absen/wa_approval.php'; ?>

            <!-- Guide View -->
            <?php include 'components/guide.php'; ?>

            <!-- About View -->
            <?php include 'components/about.php'; ?>
            
            <!-- Admin View -->
            <?php include 'components/admin.php'; ?>
        </div>
    </main>

    <div id="toast-container"></div>
    <datalist id="zohoProjectsList"></datalist>
    <datalist id="zohoTasksList"></datalist>

    <script src="assets/js/lang.js?v=<?= time() ?>"></script>
    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>