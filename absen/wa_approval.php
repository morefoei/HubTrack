<section id="wa-approval-view" class="view-section">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> WhatsApp Approval Generator</h2>
        </div>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Buat pesan rekap jadwal kehadiran otomatis untuk meminta approval atasan.</p>

        <div class="tabs" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); flex-wrap: wrap;">
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
                <div class="form-group" style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; padding: 1rem; background: rgba(255,255,255,0.02); border-radius: 8px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="waRegExcludeWeekends" checked style="margin: 0; cursor: pointer; width: 18px; height: 18px;">
                        <label for="waRegExcludeWeekends" style="margin: 0; cursor: pointer;">Exclude Weekends (Hanya Hari Kerja)</label>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="waRegExcludeHolidays" checked style="margin: 0; cursor: pointer; width: 18px; height: 18px;">
                        <label for="waRegExcludeHolidays" style="margin: 0; color: var(--danger); cursor: pointer;"><i class="fa-solid fa-umbrella-beach"></i> Exclude Libur Nasional</label>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.8rem;">Pengecualian Tanggal Cuti (Opsional)</label>
                    <div id="waRegCutiContainer">
                        <!-- Date inputs added dynamically -->
                    </div>
                    <button type="button" id="btnAddCuti" style="background: rgba(16,185,129,0.05); color: #10b981; border: 1px dashed #10b981; padding: 0.8rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.9rem; width: 100%; transition: all 0.2s;"><i class="fa-solid fa-plus"></i> Tambah Tanggal Cuti</button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Atasan / Klien</label>
                        <input type="text" id="waRegName" placeholder="Contoh: Mas Fadly" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor WA Atasan</label>
                        <input type="text" id="waRegPhone" placeholder="Contoh: 08123456789" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Bulan Kehadiran</label>
                        <input type="text" id="waRegMonth" placeholder="Contoh: Juni 2026" required>
                    </div>
                </div>
                <button type="submit" style="margin-top: 1rem;"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate Pesan</button>
            </form>
        </div>

        <div id="wa-shift" class="tab-content" style="display: none;">
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                <button id="btnLoadShiftTabs" style="background: rgba(59,130,246,0.05); color: #60a5fa; border: 1px dashed #3b82f6; padding: 0.8rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.9rem; width: 100%; text-align: center; transition: all 0.2s;"><i class="fa-solid fa-cloud-arrow-down"></i> Sync List Sheet/Tab dari Google Sheet</button>
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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                        <strong style="color: #60a5fa;"><i class="fa-regular fa-calendar-check"></i> Jadwal Shift Ditemukan:</strong>
                        <select id="waShiftMonthFilter" style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.3rem; border-radius: 4px; font-size: 0.8rem; display: none; width: auto;">
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
                <button type="button" id="btnAddWaRange" style="background: rgba(16,185,129,0.05); color: #10b981; border: 1px dashed #10b981; padding: 0.8rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; cursor: pointer; font-size: 0.9rem; width: 100%; transition: all 0.2s;"><i class="fa-solid fa-plus"></i> Tambah Rentang Tanggal</button>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Atasan / Klien</label>
                        <input type="text" id="waShiftBossName" placeholder="Contoh: Mas Muchlis" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor WA Atasan</label>
                        <input type="text" id="waShiftPhone" placeholder="Contoh: 08123456789" required>
                    </div>
                </div>
                <div class="form-row">
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
                <button id="waSaveBtn" style="background: var(--primary); color: #fff; border: none; padding: 0.8rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-bookmark"></i> Simpan ke Daftar</button>
                <button id="waCopyBtn" style="background: transparent; color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 600;"><i class="fa-regular fa-copy"></i> Copy Text</button>
            </div>
        </div>
        
        <div id="waApprovalListContainer" style="margin-top: 3rem; padding-top: 2rem; border-top: 2px dashed var(--border);">
            <h3 style="margin-bottom: 1.5rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Daftar Pesan Tersimpan</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>WAKTU SIMPAN</th>
                            <th>JUDUL / TUJUAN</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="waApprovalTableBody">
                        <!-- Data loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>