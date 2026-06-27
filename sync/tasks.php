<section id="tasks-view" class="view-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Zoho Task Manager</h2>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>Select Project</label>
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="text" id="taskManagerProject" list="zohoProjectsList" placeholder="Pilih Project..." autocomplete="off" style="padding-right: 3rem; width: 100%;">
                            <i class="fa-solid fa-circle-xmark" id="clearProjectSearch" style="position: absolute; right: 2.2rem; cursor: pointer; color: var(--text-muted); display: none; font-size: 0.9rem;" title="Hapus nama project"></i>
                        </div>
                    </div>
                    <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: flex-end;">
                        <button id="btnFetchTasks" class="secondary"><i class="fa-solid fa-cloud-arrow-down"></i> Load Tasks</button>
                    </div>
                </div>
                <hr style="border-color: var(--panel-border); margin: 1.5rem 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    <h3>Task Hierarchy</h3>
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; justify-content: flex-end;">
                        <div style="position: relative; display: inline-flex; align-items: center;">
                            <input type="text" id="taskSearchInput" placeholder="Cari task..." style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--panel-border); padding: 0.4rem 2rem 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem; width: auto; min-width: 180px;">
                            <i class="fa-solid fa-circle-xmark" id="clearTaskSearch" style="position: absolute; right: 0.6rem; cursor: pointer; color: var(--text-muted); display: none; font-size: 0.9rem;" title="Hapus pencarian"></i>
                        </div>
                        <select id="taskStatusFilter" style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--panel-border); padding: 0.4rem; border-radius: 4px; font-size: 0.85rem; width: auto;">
                            <option value="backlog" selected>Backlog Only</option>
                            <option value="open">Open / In Progress</option>
                            <option value="complete">Complete / Closed</option>
                        </select>
                        <button id="btnCreateRootTask" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; white-space: nowrap; width: auto;"><i class="fa-solid fa-plus"></i> New Main Task</button>
                    </div>
                </div>
                <div id="taskManagerContainer" style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px; border: 1px solid var(--panel-border); min-height: 200px;">
                    <p style="color: var(--text-muted); text-align: center; margin-top: 2rem;">Pilih Project dan klik Load Tasks untuk melihat daftar Task.</p>
                </div>
            </div>
        </section>