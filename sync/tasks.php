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