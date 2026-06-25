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
                    <button id="profileDisplay" style="font-size: 0.8rem; margin: 0; padding: 0.3rem 0.8rem; background: transparent; border: 1px solid var(--primary); color: var(--primary); border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 0.4rem;" title="Logout Aplikasi"><i class="fa-solid fa-user"></i> <span id="profileNameDisplay">Profile</span> <i class="fa-solid fa-sign-out-alt"></i></button>
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
        <section id="logs-view" class="view-section active">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Add New Activity</h2>
                </div>
                <form id="addLogForm">
                    <input type="hidden" id="editRowIndex" value="">
                    <div class="form-row">
                        <!-- ID disembunyikan karena sudah otomatis -->
                        <div class="form-group" style="display: none;">
                            <label>ID</label>
                            <input type="text" id="logId" placeholder="Auto">
                            <input type="hidden" id="taskUrl" value="">
                        </div>
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" id="startDate" required>
                        </div>
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" id="startTime" required>
                        </div>
                        <div class="form-group">
                            <label>Lembur</label>
                            <input type="text" id="lembur">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" id="endDate">
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" id="endTime" required>
                        </div>
                        <div class="form-group">
                            <label>Duration</label>
                            <input type="text" id="duration" placeholder="HH:MM (e.g. 02:30)" title="Overrides Start/End math if set">
                        </div>
                    </div>
                    <div id="dynamicSingleProjectTaskContainer">
                        <div class="form-row single-project-task-row">
                            <div class="form-group" style="flex: 0 0 20%;">
                                <label>Vendor (Opsional)</label>
                                <input type="text" class="singleVendor">
                            </div>
                            <div class="form-group" style="flex: 1; position: relative;">
                                <label>Project Name 
                                    <button type="button" class="btn-add-single-task add-btn-daily" style="padding: 2px 6px; font-size: 0.7rem; margin-left: 5px; background: rgba(59,130,246,0.2); color: #93c5fd; border: none; border-radius: 4px; cursor: pointer;" title="Tambah Task baru untuk Proyek ini">+ Task</button>
                                    <button type="button" class="btn-add-single-both add-btn-daily" style="padding: 2px 6px; font-size: 0.7rem; margin-left: 2px; background: rgba(16,185,129,0.2); color: #6ee7b7; border: none; border-radius: 4px; cursor: pointer;" title="Tambah Baris Kosong Baru">+ Baru</button>
                                    <button type="button" class="refreshProjectsBtn" style="background: none; border: none; color: var(--primary); padding: 0; margin-left: 0.5rem; cursor: pointer; font-size: 0.8rem;" title="Ambil list project dari Zoho"><i class="fa-solid fa-rotate"></i> Load Projects</button>
                                </label>
                                <input type="text" class="singleProjectName" list="zohoProjectsList" placeholder="Ketik atau pilih dari list..." required autocomplete="off">
                            </div>
                            <div class="form-group" style="flex: 1; position: relative;">
                                <label>Task Name
                                    <button type="button" class="btn-add-single-project add-btn-daily" style="padding: 2px 6px; font-size: 0.7rem; margin-left: 5px; background: rgba(245,158,11,0.2); color: #fcd34d; border: none; border-radius: 4px; cursor: pointer;" title="Tambah Proyek baru untuk Task ini">+ Proyek</button>
                                </label>
                                <input type="text" class="singleTaskName" placeholder="Main Task" required style="width: 100%;">
                            </div>
                            <div class="form-group" style="flex: 1; position: relative;">
                                <label>Subtask <small style="color: var(--text-muted);">(Opsional)</small></label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="text" class="singleSubTaskName" placeholder="Subtask (jika ada)" style="flex: 1;">
                                    <button type="button" class="btn-remove-single-row" style="background: transparent; color: var(--danger); border: none; cursor: pointer; padding: 0 0.5rem; display: none;"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea id="notes" rows="3" placeholder="What did you work on?"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Initial Status</label>
                        <select id="zohoStatus">
                            <option value="final" style="background: #1e293b; color: #93c5fd;">Final (Ready to Sync)</option>
                            <option value="pending" style="background: #1e293b; color: #fcd34d;">Pending (Save for later)</option>
                        </select>
                    </div>
                    <button type="submit" id="submitLogBtn"><i class="fa-solid fa-plus"></i> <span>Add Log Entry</span></button>
                    <button type="button" id="cancelEditBtn" class="secondary" style="display: none;"><i class="fa-solid fa-xmark"></i> Cancel Edit</button>
                </form>
            </div>
        </section>

        <!-- Data Logs View -->
        <section id="data-view" class="view-section">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <h2 class="card-title">Current Logs</h2>
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <select id="filterLogStatus" style="padding: 0.5rem; border-radius: 4px; border: 1px solid var(--panel-border); background: #1e293b; color: var(--text-main);">
                            <option value="all">Semua Status</option>
                            <option value="pending" style="color: #fcd34d;">Pending</option>
                            <option value="final" style="color: #93c5fd;">Final</option>
                            <option value="done" style="color: #10b981;">Done</option>
                        </select>
                        <select id="bulkStatusSelect" style="padding: 0.5rem; border-radius: 4px; border: 1px solid var(--panel-border); background: #1e293b; color: var(--text-main);">
                            <option value="final" style="background: #1e293b; color: #93c5fd;">Final</option>
                            <option value="pending" style="background: #1e293b; color: #fcd34d;">Pending</option>
                        </select>
                        <button id="btnBulkStatus" class="secondary" style="margin: 0; padding: 0.5rem 1rem;"><i class="fa-solid fa-check-double"></i> <span class="lang-en">Set Status</span><span class="lang-id">Ubah Status</span></button>
                        <button id="btnExportCSV" class="secondary" style="margin: 0; padding: 0.5rem 1rem; background: #10b981; border-color: #10b981; color: white;"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
                        <button id="btnBulkDelete" style="margin: 0; padding: 0.5rem 1rem; background-color: #ef4444; border-color: #ef4444;"><i class="fa-solid fa-trash"></i> <span class="lang-en">Delete Selected</span><span class="lang-id">Hapus Terpilih</span></button>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">
                        Tampilkan 
                        <select id="logsPerPage" style="padding: 0.3rem; border-radius: 4px; border: 1px solid var(--panel-border); background: #1e293b; color: var(--text-main);">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        baris
                    </div>
                    <div id="logsPagination" style="display: flex; gap: 0.25rem;">
                        <!-- Pagination buttons will be generated by JS -->
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAllLogs"></th>
                                <th id="sortDateHeader" style="cursor: pointer; user-select: none;" title="Urutkan berdasarkan Tanggal">Date <i id="sortDateIcon" class="fa-solid fa-sort-down" style="margin-left: 5px; color: var(--primary);"></i></th>
                                <th>Time</th>
                                <th>Project & Task</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Task Manager View -->
        <section id="tasks-view" class="view-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Zoho Task Manager</h2>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>Select Project</label>
                        <input type="text" id="taskManagerProject" list="zohoProjectsList" placeholder="Pilih Project..." autocomplete="off">
                    </div>
                    <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: flex-end;">
                        <button id="btnFetchTasks" class="secondary"><i class="fa-solid fa-cloud-arrow-down"></i> Load Tasks</button>
                    </div>
                </div>
                <hr style="border-color: var(--panel-border); margin: 1.5rem 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3>Task Hierarchy</h3>
                    <button id="btnCreateRootTask" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"><i class="fa-solid fa-plus"></i> New Main Task</button>
                </div>
                <div id="taskManagerContainer" style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px; border: 1px solid var(--panel-border); min-height: 200px;">
                    <p style="color: var(--text-muted); text-align: center; margin-top: 2rem;">Pilih Project dan klik Load Tasks untuk melihat daftar Task.</p>
                </div>
            </div>
        </section>

        <!-- Analytics View -->
        <section id="analytics-view" class="view-section">
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: none; padding-bottom: 0;">
                    <h2 class="card-title" style="margin: 0;"><i class="fa-solid fa-chart-line" style="color: var(--primary);"></i> Statistik Input</h2>
                    <div style="display: flex; gap: 0.5rem;">
                        <select id="analyticsMonth" style="padding: 0.5rem; border-radius: 4px; border: 1px solid var(--panel-border); background: #1e293b; color: var(--text-main); width: auto;">
                            <option value="all">Semua Bulan</option>
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        <select id="analyticsYear" style="padding: 0.5rem; border-radius: 4px; border: 1px solid var(--panel-border); background: #1e293b; color: var(--text-main); width: auto;">
                            <option value="all">Semua Tahun</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                </div>
                <div style="height: 300px; width: 100%;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
            <div class="card">
                <h2 class="card-title"><i class="fa-solid fa-trophy" style="color: #fcd34d;"></i> Top Projects Terbanyak</h2>
                <div id="topProjectsList" style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem;">
                    <!-- List will be populated by JS -->
                </div>
            </div>
        </section>

        <!-- Bulk Input View -->
        <section id="bulk-view" class="view-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Fast-Track (Input Banyak Hari)</h2>
                </div>
                <form id="bulkLogForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date (Dari Tanggal)</label>
                            <input type="date" id="bulkStartDate" required>
                        </div>
                        <div class="form-group">
                            <label>End Date (Sampai Tanggal)</label>
                            <input type="date" id="bulkEndDate" required>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-main);">
                                <input type="checkbox" id="bulkExcludeWeekends" checked style="width: auto;">
                                Lewati Sabtu & Minggu (Exclude Weekends)
                            </label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" id="bulkStartTime" required>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" id="bulkEndTime" required>
                        </div>
                        <div class="form-group">
                            <label>Duration (Optional)</label>
                            <input type="text" id="bulkDuration" placeholder="e.g. 09:00">
                        </div>
                        <div class="form-group">
                            <label>Lembur</label>
                            <input type="text" id="bulkLembur">
                        </div>
                    </div>
                    <div id="dynamicProjectTaskContainer">
                        <div class="form-row project-task-row">
                            <div class="form-group" style="flex: 0 0 20%;">
                                <label>Vendor (Opsional)</label>
                                <input type="text" class="bulkVendor">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Project Name 
                                    <button type="button" class="btn-add-task" style="padding: 2px 6px; font-size: 0.7rem; margin-left: 5px; background: rgba(59,130,246,0.2); color: #93c5fd; border: none; border-radius: 4px; cursor: pointer;" title="Tambah Task baru untuk Proyek ini">+ Task</button>
                                    <button type="button" class="btn-add-both" style="padding: 2px 6px; font-size: 0.7rem; margin-left: 2px; background: rgba(16,185,129,0.2); color: #6ee7b7; border: none; border-radius: 4px; cursor: pointer;" title="Tambah Baris Kosong Baru">+ Baru</button>
                                </label>
                                <input type="text" class="bulkProjectName" list="zohoProjectsList" placeholder="Ketik atau pilih dari list..." required autocomplete="off">
                            </div>
                            <div class="form-group" style="flex: 1; position: relative;">
                                <label>Task Name 
                                    <button type="button" class="btn-add-project" style="padding: 2px 6px; font-size: 0.7rem; margin-left: 5px; background: rgba(245,158,11,0.2); color: #fcd34d; border: none; border-radius: 4px; cursor: pointer;" title="Tambah Proyek baru untuk Task ini">+ Proyek</button>
                                </label>
                                <input type="text" class="bulkTaskName" placeholder="Main Task" required style="width: 100%;">
                            </div>
                            <div class="form-group" style="flex: 1; position: relative;">
                                <label>Subtask <small style="color: var(--text-muted);">(Opsional)</small></label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="text" class="bulkSubTaskName" placeholder="Subtask" style="flex: 1;">
                                    <button type="button" class="btn-remove-row" style="background: transparent; color: var(--danger); border: none; cursor: pointer; padding: 0 0.5rem; display: none;"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes / Remarks</label>
                        <textarea id="bulkNotes" rows="2" required></textarea>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" id="submitBulkBtn"><i class="fa-solid fa-layer-group"></i> <span>Generate Fast-Track</span></button>
                    </div>
                    <div id="bulkProgress" style="margin-top: 1rem; color: var(--primary); font-weight: 600;"></div>
                </form>
            </div>
        </section>

        <!-- Sync View -->
        <section id="sync-view" class="view-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Sync to Zoho Projects</h2>
                    <button id="startSyncBtn"><i class="fa-solid fa-bolt"></i> Start Sync</button>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 1rem;">
                    This will process all logs marked as <strong>Final</strong> and upload them to Zoho. Successfully synced logs will be marked as <strong>Done</strong>.
                </p>
                <div class="sync-console" id="syncConsole">
                    <div class="log-info">> Ready to sync. Waiting for user action...</div>
                </div>
            </div>
        </section>

        <!-- Settings View -->
        <section id="settings-view" class="view-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Zoho API Settings</h2>
                    <button id="saveSettingsBtn"><i class="fa-solid fa-floppy-disk"></i> Save Settings</button>
                </div>
                <form id="settingsForm">
                    <div class="form-group">
                        <label>Google Spreadsheet ID</label>
                        <input type="text" id="spreadsheetId" placeholder="ID from your Google Sheet URL (e.g. 1RsoGFQok2dk3MP...)">
                    </div>
                    <div class="form-group">
                        <label>Google Sheet Tab Name</label>
                        <input type="text" id="sheetName" placeholder="e.g. Sheet1 or tasklist" value="Sheet1">
                    </div>
                    <div class="form-group">
                        <label>Shift Schedule Spreadsheet ID (Optional)</label>
                        <input type="text" id="shiftSpreadsheetId" placeholder="ID from your Shift Google Sheet URL (View-Only is fine)">
                        <small style="color: var(--text-muted);">Hanya diisi jika Anda ingin melakukan *sync* jadwal shift. Pastikan link Google Sheet ini diset "Anyone with the link can view".</small>
                    </div>
                    <div class="form-group">
                        <label>Shift Google Sheet Tab Name (Optional)</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select id="shiftSheetName" style="flex: 1; background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem; border-radius: 6px;">
                                <option value="Sheet1">Sheet1</option>
                            </select>
                            <button type="button" id="btnSyncSettingsShiftTabs" style="background: rgba(59,130,246,0.1); color: #60a5fa; border: 1px solid #3b82f6; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; white-space: nowrap;"><i class="fa-solid fa-cloud-arrow-down"></i> Lihat Tab</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Google Service Account JSON</label>
                        <textarea id="googleCredentials" rows="4" placeholder="Paste the content of your google-credentials.json here"></textarea>
                        <small style="color: var(--text-muted);">This is stored securely on your local server.</small>
                    </div>
                    <hr style="border-color: var(--panel-border); margin: 1.5rem 0;">
                    <div class="form-group">
                        <label>URL Google Form Absensi (Opsional)</label>
                        <input type="url" id="formAbsenUrl" placeholder="https://docs.google.com/forms/d/e/.../viewform">
                        <small style="color: var(--text-muted);">Masukkan link Google Form Absen dari HR/HCA di sini agar tampil di menu Absensi.</small>
                    </div>
                    <hr style="border-color: var(--panel-border); margin: 1.5rem 0;">
                    <div class="form-group">
                        <label>Profile Password</label>
                        <input type="password" id="profilePassword" placeholder="Set a password for your profile (keep it safe!)" required>
                        <small style="color: var(--text-muted);">Wajib diisi! Password ini melindungi file konfigurasi Anda di server.</small>
                    </div>
                    <hr style="border-color: var(--panel-border); margin: 1.5rem 0;">
                    <div class="form-group">
                        <label>Zoho Client ID</label>
                        <input type="text" id="clientId" placeholder="From Zoho API Console">
                    </div>
                    <div class="form-group">
                        <label>Zoho Client Secret</label>
                        <input type="password" id="clientSecret" placeholder="From Zoho API Console">
                    </div>
                    <div class="form-group">
                        <label>Zoho Refresh Token</label>
                        <input type="password" id="refreshToken" placeholder="Generated from self client">
                    </div>
                    <div class="form-group" style="background: rgba(168, 85, 247, 0.05); padding: 1rem; border: 1px dashed var(--primary); border-radius: 8px; margin-top: 0.5rem; margin-bottom: 1.5rem;">
                        <label style="color: var(--primary);"><i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Generate Refresh Token</label>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.8rem;">Jika Anda belum memiliki Refresh Token, isi lengkap Client ID & Secret di atas. Lalu *paste* <strong>Authorization Code</strong> Anda dari Zoho ke bawah ini, dan klik Generate.</p>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="tempAuthCode" placeholder="Paste kode Authorization Code (misal: 1000.xxxx...)" style="flex: 1; padding: 0.5rem; background: rgba(0,0,0,0.2); border: 1px solid var(--panel-border); color: white; border-radius: 4px;">
                            <button type="button" id="btnGenerateToken" style="background: var(--primary-color); border: none; padding: 0.5rem 1rem; color: white; border-radius: 4px; cursor: pointer; white-space: nowrap;"><i class="fa-solid fa-bolt"></i> Generate</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Zoho Portal Name</label>
                        <input type="text" id="portalName" placeholder="e.g. mycompanyportal">
                    </div>
                    <div class="form-group">
                        <label>Base Accounts URL (Optional)</label>
                        <select id="accountsUrl">
                            <option value="https://accounts.zoho.com">accounts.zoho.com (US/Global)</option>
                            <option value="https://accounts.zoho.eu">accounts.zoho.eu (EU)</option>
                            <option value="https://accounts.zoho.in">accounts.zoho.in (IN)</option>
                            <option value="https://accounts.zoho.com.au">accounts.zoho.com.au (AU)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Base API URL (Optional)</label>
                        <select id="apiUrl">
                            <option value="https://projectsapi.zoho.com">projectsapi.zoho.com (US/Global)</option>
                            <option value="https://projectsapi.zoho.eu">projectsapi.zoho.eu (EU)</option>
                            <option value="https://projectsapi.zoho.in">projectsapi.zoho.in (IN)</option>
                            <option value="https://projectsapi.zoho.com.au">projectsapi.zoho.com.au (AU)</option>
                        </select>
                    </div>
                </form>
            </div>
        </section>

        <!-- Absensi View -->
        <section id="absen-view" class="view-section">
            <div class="card" style="height: 80vh; display: flex; flex-direction: column;">
                <div class="card-header">
                    <h2 class="card-title"><i class="fa-solid fa-clipboard-user" style="color: var(--primary-color);"></i> Presence-Track (Form HCA)</h2>
                </div>
                <div style="flex: 1; padding: 0;">
                    <iframe id="absenIframe" src="" style="width: 100%; height: 100%; border: none; border-radius: 0 0 8px 8px;"></iframe>
                    <div id="absenEmptyMsg" style="padding: 2rem; text-align: center; color: var(--text-muted); display: none;">
                        <i class="fa-solid fa-link fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>URL Google Form Absensi belum diatur.</p>
                        <p>Silakan masukkan link Google Form dari HCA di menu <strong>Settings</strong> terlebih dahulu.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- WA Approval View -->
        <section id="wa-approval-view" class="view-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> WhatsApp Approval Generator</h2>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Buat pesan rekap jadwal kehadiran otomatis untuk meminta approval atasan.</p>
                
                <div class="tabs" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
                    <button class="tab-btn active" data-tab="wa-reguler" style="background: none; border: none; color: var(--text-main); padding: 0.5rem 1rem; border-bottom: 2px solid var(--primary); cursor: pointer; font-weight: 600;">Reguler / Dedicated</button>
                    <button class="tab-btn" data-tab="wa-shift" style="background: none; border: none; color: var(--text-muted); padding: 0.5rem 1rem; border-bottom: 2px solid transparent; cursor: pointer; font-weight: 600;">Shift / Manual</button>
                </div>

                <div id="wa-reguler" class="tab-content" style="display: block;">
                    <form id="waRegulerForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" id="waRegStartDate" required>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" id="waRegEndDate" required>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" id="waRegExcludeWeekends" checked>
                            <label for="waRegExcludeWeekends" style="margin: 0;">Exclude Weekends (Hanya Hari Kerja)</label>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Atasan / Klien</label>
                                <input type="text" id="waRegName" placeholder="Contoh: Mas Muchlis" required>
                            </div>
                            <div class="form-group">
                                <label>Bulan Kehadiran</label>
                                <input type="text" id="waRegMonth" placeholder="Contoh: Juni 2026" required>
                            </div>
                        </div>
                        <button type="submit" style="margin-top: 1rem;"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate Pesan</button>
                    </form>
                </div>

                <div id="wa-shift" class="tab-content" style="display: none;">
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <button id="btnLoadShiftTabs" style="background: rgba(59,130,246,0.1); color: #60a5fa; border: 1px solid #3b82f6; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-weight: 600; width: 100%; text-align: center;"><i class="fa-solid fa-cloud-arrow-down"></i> Sync List Sheet/Tab dari Google Sheet</button>
                    </div>
                    <form id="waShiftForm">
                        <div class="form-group">
                            <label>Pilih Bulan (Sheet Tab)</label>
                            <select id="waShiftTabSelect" required style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem; border-radius: 6px; width: 100%;">
                                <option value="">-- Silakan klik tombol Sync di atas --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama Karyawan</label>
                            <select id="waShiftNameSelect" required style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem; border-radius: 6px; width: 100%;">
                                <option value="">-- Silakan pilih Sheet Tab di atas --</option>
                            </select>
                        </div>
                        <div id="waShiftScheduleInfo" style="margin-bottom: 1rem; padding: 1rem; background: rgba(59,130,246,0.1); border-left: 4px solid #3b82f6; border-radius: 4px; display: none;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <strong style="color: #60a5fa;"><i class="fa-regular fa-calendar-check"></i> Jadwal Shift Ditemukan:</strong>
                                <select id="waShiftMonthFilter" style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.3rem; border-radius: 4px; font-size: 0.8rem; display: none;">
                                    <option value="all">Semua Bulan</option>
                                    <option value="0">Januari</option>
                                    <option value="1">Februari</option>
                                    <option value="2">Maret</option>
                                    <option value="3">April</option>
                                    <option value="4">Mei</option>
                                    <option value="5">Juni</option>
                                    <option value="6">Juli</option>
                                    <option value="7">Agustus</option>
                                    <option value="8">September</option>
                                    <option value="9">Oktober</option>
                                    <option value="10">November</option>
                                    <option value="11">Desember</option>
                                </select>
                            </div>
                            <p id="waShiftScheduleText" style="margin: 0; font-size: 0.9rem; color: var(--text-main); white-space: pre-wrap; line-height: 1.5;"></p>
                        </div>
                        
                        <div id="waShiftDateRangesContainer">
                            <div class="form-row wa-shift-date-range">
                                <div class="form-group">
                                    <label>Ambil Absen Awal (Start Date)</label>
                                    <input type="date" class="waShiftStartDate" required>
                                </div>
                                <div class="form-group">
                                    <label>Ambil Absen Akhir (End Date)</label>
                                    <input type="date" class="waShiftEndDate" required>
                                </div>
                                <button type="button" class="btn-remove-wa-range" style="background: transparent; color: var(--danger); border: none; cursor: pointer; padding: 0 0.5rem; margin-bottom: 0.8rem; display: none;"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                        <button type="button" id="btnAddWaRange" style="background: rgba(16,185,129,0.1); color: #10b981; border: 1px dashed #10b981; padding: 0.5rem 1rem; border-radius: 4px; margin-bottom: 1.5rem; cursor: pointer; width: 100%;"><i class="fa-solid fa-plus"></i> Tambah Rentang Tanggal</button>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Atasan / Klien</label>
                                <input type="text" id="waShiftBossName" placeholder="Contoh: Mas Muchlis" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Bulan di Chat</label>
                                <input type="text" id="waShiftMonth" placeholder="Contoh: Januari 2026 - Febuari 2026" required>
                            </div>
                        </div>
                        <button type="submit" id="btnGenerateShift" style="margin-top: 1rem;"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate Pesan Shift</button>
                    </form>
                </div>

                <div id="waResultContainer" style="margin-top: 2rem; display: none; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h3 style="margin-bottom: 1rem; color: var(--text-main);">Preview Pesan:</h3>
                    <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;">
                        <p id="waPreviewText" style="white-space: pre-wrap; font-family: monospace; font-size: 14px; line-height: 1.5; color: var(--text-main); margin: 0;"></p>
                    </div>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <button id="waSendBtn" style="background: #25D366; color: #fff; border: none; padding: 0.8rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;"><i class="fa-brands fa-whatsapp" style="font-size: 1.2rem;"></i> Kirim via WhatsApp</button>
                        <button id="waCopyBtn" style="background: transparent; color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 600;"><i class="fa-regular fa-copy"></i> Copy Text</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Guide View -->
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
    </main>

    <footer style="text-align: center; padding: 2rem 1rem; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.05); color: var(--text-muted); font-size: 0.85rem;">
        <div style="font-family: 'Inter', sans-serif; opacity: 0.8;">
            &copy; 2026 <span style="font-weight: 800; font-size: 1.1rem; background: linear-gradient(135deg, #f43f5e, #a855f7, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; margin: 0 0.2rem;">FAFA</span> HubTrack. All Rights Reserved.
        </div>
    </footer>

    <div id="toast-container"></div>
    <datalist id="zohoProjectsList"></datalist>

    <script src="assets/js/lang.js?v=<?= time() ?>"></script>
    <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>

</html>