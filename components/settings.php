<section id="settings-view" class="view-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Zoho API Settings</h2>
                    <button id="saveSettingsBtn"><i class="fa-solid fa-floppy-disk"></i> Save Settings</button>
                </div>
                <form id="settingsForm">
                    <div class="form-group">
                        <label>Google Spreadsheet ID</label>
                        <input type="text" id="spreadsheetId" placeholder="ID from your Google Sheet URL (e.g. 1RsoGFQok2dk3MP...)">
                    </div>
                    <div class="form-group">
                        <label>Google Sheet Tab Name</label>
                        <input type="text" id="sheetName" placeholder="e.g. Sheet1 or tasklist" value="Sheet1">
                    </div>

                    <div class="form-group">
                        <label>Shift Google Sheet Tab Name (Optional)</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select id="shiftSheetName" style="flex: 1; background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem; border-radius: 6px;">
                                <option value="Sheet1">Sheet1</option>
                            </select>
                            <button type="button" id="btnSyncSettingsShiftTabs" style="background: rgba(59,130,246,0.1); color: #60a5fa; border: 1px solid #3b82f6; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; white-space: nowrap;"><i class="fa-solid fa-cloud-arrow-down"></i> Lihat Tab</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Google Service Account JSON</label>
                        <textarea id="googleCredentials" rows="4" placeholder="Paste the content of your google-credentials.json here"></textarea>
                        <small style="color: var(--text-muted);">This is stored securely on your local server.</small>
                    </div>

                    <div class="form-group">
                        <label>Profile Password</label>
                        <input type="password" id="profilePassword" placeholder="Set a password for your profile (keep it safe!)" required>
                        <small style="color: var(--text-muted);">Wajib diisi! Password ini melindungi file konfigurasi Anda di server.</small>
                    </div>
                    <hr style="border-color: var(--panel-border); margin: 1.5rem 0;">
                    <div class="form-group">
                        <label>Zoho Client ID</label>
                        <input type="text" id="clientId" placeholder="From Zoho API Console">
                    </div>
                    <div class="form-group">
                        <label>Zoho Client Secret</label>
                        <input type="password" id="clientSecret" placeholder="From Zoho API Console">
                    </div>
                    <div class="form-group">
                        <label>Zoho Refresh Token</label>
                        <input type="password" id="refreshToken" placeholder="Generated from self client">
                    </div>
                    <div class="form-group" style="background: rgba(168, 85, 247, 0.05); padding: 1rem; border: 1px dashed var(--primary); border-radius: 8px; margin-top: 0.5rem; margin-bottom: 1.5rem;">
                        <label style="color: var(--primary);"><i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Generate Refresh Token</label>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.8rem;">Jika Anda belum memiliki Refresh Token, isi lengkap Client ID & Secret di atas. Lalu *paste* <strong>Authorization Code</strong> Anda dari Zoho ke bawah ini, dan klik Generate.</p>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="tempAuthCode" placeholder="Paste kode Authorization Code (misal: 1000.xxxx...)" style="flex: 1; padding: 0.5rem; background: rgba(0,0,0,0.2); border: 1px solid var(--panel-border); color: white; border-radius: 4px;">
                            <button type="button" id="btnGenerateToken" style="background: var(--primary-color); border: none; padding: 0.5rem 1rem; color: white; border-radius: 4px; cursor: pointer; white-space: nowrap;"><i class="fa-solid fa-bolt"></i> Generate</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Zoho Portal Name</label>
                        <input type="text" id="portalName" placeholder="e.g. mycompanyportal">
                    </div>
                    <div class="form-group">
                        <label>Base Accounts URL (Optional)</label>
                        <select id="accountsUrl">
                            <option value="https://accounts.zoho.com">accounts.zoho.com (US/Global)</option>
                            <option value="https://accounts.zoho.eu">accounts.zoho.eu (EU)</option>
                            <option value="https://accounts.zoho.in">accounts.zoho.in (IN)</option>
                            <option value="https://accounts.zoho.com.au">accounts.zoho.com.au (AU)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Base API URL (Optional)</label>
                        <select id="apiUrl">
                            <option value="https://projectsapi.zoho.com">projectsapi.zoho.com (US/Global)</option>
                            <option value="https://projectsapi.zoho.eu">projectsapi.zoho.eu (EU)</option>
                            <option value="https://projectsapi.zoho.in">projectsapi.zoho.in (IN)</option>
                            <option value="https://projectsapi.zoho.com.au">projectsapi.zoho.com.au (AU)</option>
                        </select>
                    </div>
                </form>
            </div>
        </section>