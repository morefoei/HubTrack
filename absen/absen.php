<section id="absen-view" class="view-section">
            <div class="card" style="height: 80vh; display: flex; flex-direction: column;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title"><i class="fa-solid fa-clipboard-user" style="color: var(--primary-color);"></i> Presence-Track (Form HCA)</h2>
                    <a id="absenNewTabBtn" href="#" target="_blank" style="display: none; background: var(--primary-color); color: white; padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Buka di Tab Baru (Disarankan)</a>
                </div>
                <div style="flex: 1; padding: 0;">
                    <iframe id="absenIframe" src="" style="width: 100%; height: 100%; border: none; border-radius: 0 0 8px 8px;"></iframe>
                    <div id="absenEmptyMsg" style="padding: 2rem; text-align: center; color: var(--text-muted); display: none;">
                        <i class="fa-solid fa-link fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>URL Google Form Absensi belum diatur.</p>
                        <p>Silakan masukkan link Google Form dari HCA di menu <strong>Settings</strong> terlebih dahulu.</p>
                    </div>
                </div>
            </div>
        </section>