<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HubTrack Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- PWA / Android App Integration -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/1356/1356479.png">
    <script>
        // Redirect to login.php if not authenticated
        if (!sessionStorage.getItem('zohoProfile') || !sessionStorage.getItem('zohoPassword')) {
            window.location.href = 'login.php';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <header>
        <div class="header-top">
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span class="logo"><i class="fa-solid fa-rocket" style="-webkit-text-fill-color: initial; color: #f43f5e;"></i> HubTrack</span>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button id="langToggleBtn" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; background: rgba(255,255,255,0.1); border: none; color: white; border-radius: 4px; cursor: pointer;" title="Ubah Bahasa / Change Language">ID</button>
                    <button id="profileDisplay" style="font-size: 0.8rem; margin: 0; padding: 0.3rem 0.8rem; background: transparent; border: 1px solid var(--primary); color: var(--primary); border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 0.4rem;" title="Logout Aplikasi"><i class="fa-solid fa-user"></i> <span id="profileNameDisplay"><script>var up = sessionStorage.getItem('zohoProfile') || 'Profile'; document.write(up);</script></span> <i class="fa-solid fa-sign-out-alt"></i></button>
                </div>
            </div>
            <button id="mobileMenuBtn" style="display: none; background: transparent; border: none; color: var(--text-main); font-size: 1.5rem; cursor: pointer; padding: 0.5rem;"><i class="fa-solid fa-bars"></i></button>
        </div>
        <nav id="mainNav">
            <details class="nav-dropdown" open>
                <summary class="nav-dropdown-summary"><i class="fa-solid fa-cloud-arrow-up"></i> Zoho Sync <i class="fa-solid fa-chevron-down dropdown-icon"></i></summary>
                <div class="dropdown-content">
                    <button class="nav-btn active" data-target="logs-view"><i class="fa-solid fa-pen-to-square"></i> Daily-Track</button>
                    <button class="nav-btn" data-target="bulk-view"><i class="fa-solid fa-calendar-days"></i> Fast-Track</button>
                    <button class="nav-btn" data-target="data-view"><i class="fa-solid fa-table"></i> Data Logs</button>
                    <button class="nav-btn" data-target="tasks-view"><i class="fa-solid fa-list-check"></i> Task Manager</button>
                    <button class="nav-btn" data-target="sync-view"><i class="fa-solid fa-rotate"></i> Sync Manager</button>
                </div>
            </details>
            
            <details class="nav-dropdown">
                <summary class="nav-dropdown-summary"><i class="fa-solid fa-clipboard-user"></i> Absensi <i class="fa-solid fa-chevron-down dropdown-icon"></i></summary>
                <div class="dropdown-content">
                    <button class="nav-btn" data-target="absen-view"><i class="fa-solid fa-user-check"></i> Presence-Track</button>
                    <button class="nav-btn" data-target="wa-approval-view"><i class="fa-brands fa-whatsapp"></i> WA Approval</button>
                </div>
            </details>

            <details class="nav-dropdown">
                <summary class="nav-dropdown-summary"><i class="fa-solid fa-server"></i> <span class="lang-en">System</span><span class="lang-id">Sistem</span> <i class="fa-solid fa-chevron-down dropdown-icon"></i></summary>
                <div class="dropdown-content">
                    <button class="nav-btn" data-target="analytics-view"><i class="fa-solid fa-chart-pie"></i> Analytics</button>
                    <button class="nav-btn" data-target="settings-view"><i class="fa-solid fa-gear"></i> Settings</button>
                    <button class="nav-btn" data-target="guide-view"><i class="fa-solid fa-book"></i> Dokumentasi</button>
                </div>
            </details>
        </nav>
    </header>

    <main>
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
    </main>

    <footer style="text-align: center; padding: 2rem 1rem; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.05); color: var(--text-muted); font-size: 0.85rem;">
        <div style="font-family: 'Inter', sans-serif; opacity: 0.8;">
            &copy; 2026 <span style="font-weight: 800; font-size: 1.1rem; background: linear-gradient(135deg, #f43f5e, #a855f7, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; margin: 0 0.2rem;">FAFA</span> HubTrack. All Rights Reserved.
        </div>
    </footer>

    <div id="toast-container"></div>
    <datalist id="zohoProjectsList"></datalist>

    <script src="assets/js/lang.js?v=<?= time() ?>"></script>
    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>