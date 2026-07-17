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
            </div>
            <div class="form-row exclusion-box">
                <div style="flex: 1; display: flex; flex-direction: column; gap: 0.8rem;">
                    <label style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; margin: 0;">Opsi Pengecualian (Exclude)</label>
                    <label class="checkbox-label">
                        <input type="checkbox" id="bulkExcludeWeekends" checked>
                        Lewati Sabtu & Minggu (Exclude Weekends)
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" id="bulkExcludeHolidays" checked>
                        Lewati Libur Nasional (Auto-Fetch Tanggal Merah)
                    </label>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column; gap: 0.5rem; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label style="font-size: 0.85rem; color: var(--text-muted); margin: 0; font-weight: 600;">Exclude Tanggal Cuti Tambahan</label>
                        <button type="button" id="btnAddExcludeDate" class="badge-btn badge-btn-red">+ Tambah Tanggal</button>
                    </div>
                    <div id="dynamicExcludeDatesContainer" style="display: flex; flex-direction: column; gap: 0.4rem;"></div>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column; gap: 0.5rem; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label style="font-size: 0.85rem; color: var(--text-muted); margin: 0; font-weight: 600;">Lembur Hari Libur</label>
                        <button type="button" id="btnAddIncludeDate" class="badge-btn badge-btn-green">+ Tambah Tanggal</button>
                    </div>
                    <div id="dynamicIncludeDatesContainer" style="display: flex; flex-direction: column; gap: 0.4rem;"></div>
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
                            <button type="button" class="badge-btn badge-btn-blue btn-add-task" title="Tambah Task baru untuk Proyek ini">+ Task</button>
                            <button type="button" class="badge-btn badge-btn-green btn-add-both" title="Tambah Baris Kosong Baru">+ Baru</button>
                        </label>
                        <input type="text" class="bulkProjectName" list="zohoProjectsList" placeholder="Ketik atau pilih dari list..." required autocomplete="off">
                    </div>
                    <div class="form-group" style="flex: 1; position: relative;">
                        <label>Task Name
                            <button type="button" class="badge-btn badge-btn-yellow btn-add-project" title="Tambah Proyek baru untuk Task ini">+ Proyek</button>
                        </label>
                        <input type="text" class="bulkTaskName" list="zohoTasksList" autocomplete="off" placeholder="Main Task" required style="width: 100%;">
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
                <label>Notes / Remarks Umum</label>
                <textarea id="bulkNotes" rows="2" required></textarea>
            </div>
            <div class="form-group" style="margin-top: 1rem;">
                <label>Catatan Khusus per Tanggal <button type="button" id="btnAddSpecificNote" class="badge-btn badge-btn-blue" title="Tambah catatan khusus untuk tanggal tertentu">+ Tambah</button></label>
                <div id="dynamicSpecificNotesContainer" style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" id="submitBulkBtn"><i class="fa-solid fa-layer-group"></i> <span>Generate Fast-Track</span></button>
            </div>
            <div id="bulkProgress" style="margin-top: 1rem; color: var(--primary); font-weight: 600;"></div>
        </form>
    </div>
</section>