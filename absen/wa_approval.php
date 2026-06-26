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