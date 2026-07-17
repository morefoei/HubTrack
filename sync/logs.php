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
                                    <button type="button" class="badge-btn badge-btn-blue btn-add-single-task add-btn-daily" title="Tambah Task baru untuk Proyek ini">+ Task</button>
                                    <button type="button" class="badge-btn badge-btn-green btn-add-single-both add-btn-daily" title="Tambah Baris Kosong Baru">+ Baru</button>
                                    <button type="button" class="refreshProjectsBtn" style="background: none; border: none; color: var(--primary); padding: 0; margin-left: 0.5rem; cursor: pointer; font-size: 0.8rem;" title="Ambil list project dari Zoho"><i class="fa-solid fa-rotate"></i> Load Projects</button>
                                </label>
                                <input type="text" class="singleProjectName" list="zohoProjectsList" placeholder="Ketik atau pilih dari list..." required autocomplete="off">
                            </div>
                            <div class="form-group" style="flex: 1; position: relative;">
                                <label>Task Name
                                    <button type="button" class="badge-btn badge-btn-yellow btn-add-single-project add-btn-daily" title="Tambah Proyek baru untuk Task ini">+ Proyek</button>
                                </label>
                                <input type="text" class="singleTaskName" list="zohoTasksList" autocomplete="off" placeholder="Main Task" required style="width: 100%;">
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