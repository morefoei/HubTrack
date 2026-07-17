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
                            <option value="all" selected>All Tasks</option>
                            <option value="backlog">Backlog Only</option>
                            <option value="open">Open / In Progress</option>
                            <option value="complete">Complete / Closed</option>
                        </select>
                        <button id="btnCreateRootTask" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; white-space: nowrap; width: auto;"><i class="fa-solid fa-plus"></i> New Main Task</button>
                    </div>
                </div>
                <div id="taskManagerContainer" style="background: var(--panel-bg); padding: 1rem; border-radius: 8px; border: 1px solid var(--panel-border); min-height: 200px; backdrop-filter: var(--glass-blur); box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <p style="color: var(--text-muted); text-align: center; margin-top: 2rem;">Pilih Project dan klik Load Tasks untuk melihat daftar Task.</p>
                </div>
            </div>

            <!-- Task Logs Modal -->
            <div id="taskLogsModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; padding: 2rem 1rem; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                <div class="modal-content card" style="width: 100%; max-width: 800px; max-height: 100%; display: flex; flex-direction: column; overflow: hidden; position: relative; padding: 0; margin: 0;">
                    <button class="close-modal" id="closeTaskLogsModal" style="position: absolute; right: 1.5rem; top: 1.5rem; background: transparent; border: none; color: var(--text-muted); font-size: 1.2rem; cursor: pointer; z-index: 10;"><i class="fa-solid fa-xmark"></i></button>
                    <div class="card-header" style="flex-shrink: 0; padding: 1.5rem 3rem 1rem 1.5rem; border-bottom: 1px solid var(--panel-border); margin-bottom: 0; display: flex; flex-direction: column; align-items: flex-start; gap: 0.25rem;">
                        <h3 style="margin: 0;"><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Riwayat Work Log</h3>
                        <p id="taskLogsSubtitle" style="color: var(--text-muted); margin: 0; font-size: 0.9rem; text-align: left;"></p>
                    </div>
                    <div class="card-body" style="padding: 0; overflow-y: auto; flex: 1 1 auto; min-height: 0;">
                        <div id="taskLogsLoading" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fa-solid fa-spinner fa-spin fa-2x" style="margin-bottom: 1rem;"></i>
                            <p>Mengambil data dari Zoho...</p>
                        </div>
                        <div id="taskLogsContent" style="display: none;">
                            <div class="table-responsive" style="margin: 0;">
                                <table class="data-table" style="width: 100%; text-align: left; border-collapse: collapse;">
                                    <thead style="position: sticky; top: 0; background: var(--panel-bg); z-index: 5; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                        <tr>
                                            <th style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--panel-border);">Tanggal</th>
                                            <th style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--panel-border);">Owner</th>
                                            <th style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--panel-border);">Jam</th>
                                            <th style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--panel-border);">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="taskLogsTbody">
                                        <!-- Logs will be inserted here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>