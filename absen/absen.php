<section id="absen-view" class="view-section">
    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <!-- Form Buat Rencana -->
        <div style="flex: 1; min-width: 300px;">
            <div class="card" style="position: sticky; top: 1.5rem; background: linear-gradient(180deg, var(--panel-bg) 0%, rgba(30,41,59,0.5) 100%); border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="card-header" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                    <h2 class="card-title" style="font-weight: 700; background: linear-gradient(90deg, #60a5fa, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="fa-solid fa-plus-circle" style="color: #60a5fa; -webkit-text-fill-color: initial;"></i> Buat Rencana Absensi</h2>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>Jenis Pengajuan</label>
                    <select id="absenPlanType" style="width: 100%; padding: 0.8rem; border-radius: 4px; border: 1px solid var(--border); background: var(--input-bg); color: var(--text-main);">
                        <option value="Sakit">Sakit</option>
                        <option value="Izin">Izin</option>
                        <option value="Cuti Tahunan">Cuti Tahunan</option>
                        <option value="Cuti Khusus">Cuti Khusus</option>
                        <option value="Hadir" selected>Hadir</option>
                        <option value="Overtime (Di Wajibkan Mengisi Jam Awal & Jam Akhir OT)">Overtime (Di Wajibkan Mengisi Jam Awal & Jam Akhir OT)</option>
                    </select>
                </div>
                <div id="absenDateRangePanel">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Tanggal Awal</label>
                        <input type="date" id="absenPlanStartDate" style="width: 100%; padding: 0.8rem; border-radius: 4px; border: 1px solid var(--border); background: var(--input-bg); color: var(--text-main);">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Tanggal Akhir</label>
                        <input type="date" id="absenPlanEndDate" style="width: 100%; padding: 0.8rem; border-radius: 4px; border: 1px solid var(--border); background: var(--input-bg); color: var(--text-main);">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 2rem; background: linear-gradient(145deg, rgba(168, 85, 247, 0.08), rgba(168, 85, 247, 0.02)); padding: 1.2rem; border-radius: 8px; border: 1px solid rgba(168, 85, 247, 0.2); box-shadow: inset 0 2px 10px rgba(0,0,0,0.1);">
                    <label style="display: block; cursor: pointer; margin-bottom: 1rem;">
                        <div style="display: flex; gap: 10px;">
                            <input type="radio" name="absenGenMode" id="absenGenModeBulk" value="bulk" checked style="width: 18px; height: 18px; margin-top: 2px; padding: 0; transform: none; box-shadow: none;">
                            <div>
                                <strong style="color: var(--primary);">Pecah otomatis per minggu (Reguler)</strong><br>
                                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">Memecah rentang waktu panjang menjadi per-minggu dan membuang akhir pekan/libur.</span>
                            </div>
                        </div>
                    </label>
                    <label style="display: block; cursor: pointer; margin-bottom: 0;">
                        <div style="display: flex; gap: 10px;">
                            <input type="radio" name="absenGenMode" id="absenGenModeShift" value="shift" style="width: 18px; height: 18px; margin-top: 2px; padding: 0; transform: none; box-shadow: none;">
                            <div>
                                <strong style="color: #10b981;">Berdasarkan Jadwal Shift (Otomatis)</strong><br>
                                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">Mengambil jadwal absen murni dari jadwal kerja Anda di Google Sheet.</span>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Shift Panel (Hidden by default) -->
                <div id="absenShiftPanel" style="display: none; margin-bottom: 2rem; padding: 1.2rem; background: linear-gradient(145deg, rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0.02)); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; box-shadow: inset 0 2px 10px rgba(0,0,0,0.1);">
                    <button type="button" id="btnAbsenSyncShiftTabs" style="background: linear-gradient(90deg, rgba(59,130,246,0.1), rgba(168,85,247,0.1)); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); padding: 0.8rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.9rem; font-weight: 600; width: 100%; text-align: center; margin-bottom: 1.5rem; transition: all 0.2s;"><i class="fa-solid fa-cloud-arrow-down"></i> Sync Data Shift</button>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="font-size: 0.85rem;">Pilih Bulan (Sheet Tab)</label>
                        <select id="absenShiftTabSelect" style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.6rem; border-radius: 4px; width: 100%; font-size: 0.9rem;">
                            <option value="">-- Klik Sync terlebih dahulu --</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="font-size: 0.85rem;">Nama Karyawan</label>
                        <select id="absenShiftNameSelect" style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.6rem; border-radius: 4px; width: 100%; font-size: 0.9rem;">
                            <option value="">-- Pilih Sheet Tab dahulu --</option>
                        </select>
                    </div>
                    <div id="absenShiftScheduleInfo" style="padding: 0.8rem; background: rgba(59,130,246,0.1); border-left: 3px solid #3b82f6; border-radius: 4px; display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                            <strong style="color: #60a5fa; font-size: 0.85rem;"><i class="fa-regular fa-calendar-check"></i> Jadwal Shift Ditemukan:</strong>
                            <select id="absenShiftMonthFilter" style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.3rem; border-radius: 4px; font-size: 0.8rem; width: auto; max-width: 150px;">
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
                        <p id="absenShiftScheduleText" style="margin: 0; font-size: 0.85rem; color: var(--text-main); white-space: pre-wrap; line-height: 1.5;"></p>
                    </div>
                </div>
                <button id="btnSaveAbsenPlan" style="width: 100%; background: linear-gradient(90deg, #3b82f6, #8b5cf6); color: #fff; border: none; padding: 1rem; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1rem; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); transition: transform 0.2s, box-shadow 0.2s;">
                    <i class="fa-solid fa-paper-plane"></i> Simpan Rencana
                </button>
            </div>
        </div>

        <!-- Tabel Daftar Rencana -->
        <div style="flex: 2; min-width: 400px;">
            <div class="card" style="background: linear-gradient(180deg, var(--panel-bg) 0%, rgba(30,41,59,0.5) 100%); border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                    <h3 style="color: var(--text-main); display: flex; align-items: center; gap: 0.5rem; margin: 0; font-weight: 700;"><i class="fa-solid fa-list-check" style="color: #a855f7;"></i> Daftar Rencana Absensi</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>TANGGAL PENGAJUAN</th>
                                <th>JENIS PENGAJUAN</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="absenPlansTableBody">
                            <!-- Data loaded via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Google Form -->
    <div class="card" style="margin-top: 1.5rem; height: 600px; display: flex; flex-direction: column; background: rgba(30,41,59,0.3); border: 1px solid rgba(255,255,255,0.05);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1rem; margin-bottom: 1rem;">
            <h3 style="color: var(--text-main); margin: 0; font-weight: 700;"><i class="fa-brands fa-google" style="color: #60a5fa;"></i> Preview Google Form</h3>
            <a id="absenNewTabBtn" href="#" target="_blank" style="display: none; background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.2s;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Buka di Tab Baru</a>
        </div>
        <div style="flex: 1; padding: 0; position: relative;">
            <iframe id="absenIframe" src="" style="width: 100%; height: 100%; border: none; border-radius: 0 0 8px 8px; display: none;"></iframe>
            <div id="absenEmptyMsg" style="padding: 4rem 2rem; text-align: center; color: var(--text-muted); display: block;">
                <i class="fa-solid fa-file-contract fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>Silakan buat rencana absensi dan klik <strong>"Buka Form"</strong> untuk melihat preview di sini.</p>
            </div>
        </div>
    </div>
</section>