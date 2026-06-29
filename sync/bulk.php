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
                        <div class="form-group" style="display: flex; flex-direction: column; justify-content: flex-end; padding-bottom: 0.5rem; gap: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-main);">
                                <input type="checkbox" id="bulkExcludeWeekends" checked style="width: auto;">
                                Lewati Sabtu & Minggu (Exclude Weekends)
                            </label>
                            <input type="text" id="bulkExcludeDates" placeholder="Exclude Tgl Cuti (Cth: 2024-06-15, 2024-06-17)" style="font-size: 0.85rem; padding: 0.4rem; border: 1px solid var(--panel-border); background: var(--bg-main); color: var(--text-main); border-radius: 4px;">
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
                        <label>Notes / Remarks Umum</label>
                        <textarea id="bulkNotes" rows="2" required></textarea>
                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label>Catatan Khusus per Tanggal <button type="button" id="btnAddSpecificNote" style="padding: 2px 6px; font-size: 0.7rem; margin-left: 5px; background: rgba(59,130,246,0.2); color: #93c5fd; border: none; border-radius: 4px; cursor: pointer;" title="Tambah catatan khusus untuk tanggal tertentu">+ Tambah</button></label>
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