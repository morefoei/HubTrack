<section id="sync-view" class="view-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Sync to Zoho Projects</h2>
                    <button id="startSyncBtn"><i class="fa-solid fa-bolt"></i> Start Sync</button>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 1rem;">
                    This will process all logs marked as <strong>Final</strong> and upload them to Zoho. Successfully synced logs will be marked as <strong>Done</strong>.
                </p>
                <div class="sync-console" id="syncConsole">
                    <div class="log-info">> Ready to sync. Waiting for user action...</div>
                </div>
            </div>
        </section>