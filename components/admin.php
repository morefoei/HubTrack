<section id="admin-global-view" class="view-section">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header" style="background: linear-gradient(135deg, #ef4444, #991b1b);">
            <h2 class="card-title"><i class="fa-solid fa-user-shield"></i> Super Admin Panel</h2>
        </div>
        <div style="padding: 1.5rem;">
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Panel ini hanya dapat diakses oleh user <strong>superman</strong>. Gunakan alat di bawah ini untuk mengatur sistem secara global dan mengelola profil pengguna.</p>

            <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-bottom: 1rem;">Pengaturan Global</h3>
            <form id="globalSettingsForm">
                <div class="form-group">
                    <label>Shift Schedule Spreadsheet ID</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="text" id="globalShiftSpreadsheetId" class="form-control" style="margin-bottom: 0;" placeholder="ID from your Shift Google Sheet URL">
                        <button type="button" id="btnTestGlobalSpreadsheet" style="background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid var(--success); padding: 0.8rem 1rem; border-radius: 6px; cursor: pointer; white-space: nowrap;"><i class="fa-solid fa-plug-circle-check"></i> Test Sinkronisasi</button>
                    </div>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">Ini akan digunakan sebagai sumber jadwal shift bagi semua pengguna (Menu WA Approval).</small>
                </div>
                <div class="form-group">
                    <label>URL Google Form Absensi</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="url" id="globalFormAbsenUrl" class="form-control" style="margin-bottom: 0;" placeholder="https://docs.google.com/forms/d/e/.../viewform">
                        <button type="button" id="btnTestGlobalForm" style="background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid var(--success); padding: 0.8rem 1rem; border-radius: 6px; cursor: pointer; white-space: nowrap;"><i class="fa-solid fa-link"></i> Test Link</button>
                    </div>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">Ini akan menjadi halaman absensi default bagi semua pengguna (Menu Presence-Track).</small>
                </div>
                <button type="submit" style="background: var(--primary); color: #fff; border: none; padding: 0.8rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 600;"><i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Global</button>
            </form>
        </div>
    </div>
</section>

<section id="admin-users-view" class="view-section">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header" style="background: linear-gradient(135deg, #ef4444, #991b1b);">
            <h2 class="card-title"><i class="fa-solid fa-users-gear"></i> Manajemen Profil Pengguna</h2>
        </div>
        <div style="padding: 1.5rem;">
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Gunakan alat di bawah ini untuk mengatur profil pengguna sistem.</p>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <h3 style="color: var(--primary); margin: 0;">Daftar User</h3>
                <button id="btnLoadUsers" type="button" style="background: rgba(59,130,246,0.1); color: #60a5fa; border: 1px solid #3b82f6; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.9rem;"><i class="fa-solid fa-rotate"></i> Muat Ulang Daftar</button>
            </div>
            
            <div style="overflow-x: auto; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid var(--panel-border);">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.05); border-bottom: 1px solid var(--panel-border);">
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 600;">Username</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 600;">Status Password</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 600;">Terakhir Login</th>
                            <th style="padding: 1rem; color: var(--text-muted); font-weight: 600; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="adminUserTableBody">
                        <tr>
                            <td colspan="4" style="padding: 1.5rem; text-align: center; color: var(--text-muted);">
                                Klik "Muat Ulang Daftar" untuk melihat pengguna.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <small style="display: block; margin-top: 1rem; color: var(--text-muted);">* <strong>Reset Password:</strong> Mengosongkan password agar user bisa login tanpa password dan membuat yang baru.<br>* <strong>Hapus User:</strong> Menghapus seluruh data profil dan pengaturan user (Tidak bisa dikembalikan!).</small>

        </div>
    </div>
</section>
