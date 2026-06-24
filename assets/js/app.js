document.addEventListener('DOMContentLoaded', () => {
    // Navigation
    const navBtns = document.querySelectorAll('.nav-btn');
    const viewSections = document.querySelectorAll('.view-section');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');

    mobileMenuBtn.addEventListener('click', () => {
        mainNav.classList.toggle('show');
    });

    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            navBtns.forEach(b => b.classList.remove('active'));
            viewSections.forEach(s => s.classList.remove('active'));
            
            btn.classList.add('active');
            const targetId = btn.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
            
            // Close any open dropdowns
            document.querySelectorAll('details.nav-dropdown').forEach(d => d.removeAttribute('open'));
            
            // Close mobile menu
            if (window.innerWidth <= 768) {
                mainNav.classList.remove('show');
            }

            if (targetId === 'absen-view') {
                let formUrl = document.getElementById('formAbsenUrl').value.trim();
                const iframe = document.getElementById('absenIframe');
                const emptyMsg = document.getElementById('absenEmptyMsg');
                
                if (formUrl) {
                    if (!/^https?:\/\//i.test(formUrl)) {
                        formUrl = 'https://' + formUrl;
                    }
                    if (iframe.src !== formUrl) iframe.src = formUrl;
                    iframe.style.display = 'block';
                    emptyMsg.style.display = 'none';
                } else {
                    iframe.style.display = 'none';
                    emptyMsg.style.display = 'block';
                }
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('details.nav-dropdown')) {
            document.querySelectorAll('details.nav-dropdown').forEach(d => d.removeAttribute('open'));
        }
    });

    // Toast Notification System
    const showToast = (message, type = 'info') => {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        if (type === 'warning') icon = 'exclamation-triangle';

        toast.innerHTML = `<i class="fa-solid fa-${icon}"></i> <span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3300);
    };

    // Data Management
    const API_URL = 'api/api.php';
    let currentLogs = [];

    // Profile Management
    let userProfile = localStorage.getItem('zohoProfile') || '';
    let userPassword = localStorage.getItem('zohoPassword') || '';

    const checkAuth = async () => {
        if (!userProfile || !userPassword) {
            document.getElementById('loginOverlay').style.display = 'flex';
            return false;
        }
        
        // Simple ping to check if auth is valid by fetching settings
        try {
            const res = await fetch(`${API_URL}?action=get_settings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ profile: userProfile, password: userPassword })
            });
            const data = await res.json();
            if (data.success === false && data.message && data.message.includes('Password Salah')) {
                showToast(data.message, 'error');
                userPassword = '';
                localStorage.removeItem('zohoPassword');
                document.getElementById('loginOverlay').style.display = 'flex';
                return false;
            }
            // Auth OK
            document.getElementById('loginOverlay').style.display = 'none';
            document.getElementById('profileNameDisplay').innerText = userProfile;
            return true;
        } catch (err) {
            return false;
        }
    };

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const u = document.getElementById('loginUsername').value.trim();
        const p = document.getElementById('loginPassword').value.trim();
        
        if (u === 'superman' && p === 'musikrock1') {
            try {
                const res = await fetch(`${API_URL}?action=get_all_profiles`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ profile: 'superman', password: 'musikrock1' })
                });
                const data = await res.json();
                if (data.success) {
                    const targetUser = prompt('📋 DAFTAR USER YANG TERDAFTAR:\n- ' + data.profiles.join('\n- ') + '\n\nKetik username yang ingin di-RESET passwordnya:');
                    if (targetUser) {
                        const resetRes = await fetch(`${API_URL}?action=reset_password`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ profile: 'superman', password: 'musikrock1', targetUser: targetUser.trim() })
                        });
                        const resetData = await resetRes.json();
                        alert(resetData.message);
                    }
                } else {
                    alert('Akses Admin Ditolak!');
                }
            } catch (err) {
                alert('Gagal mengambil data.');
            }
            return;
        }

        userProfile = u;
        userPassword = p;
        localStorage.setItem('zohoProfile', userProfile);
        localStorage.setItem('zohoPassword', userPassword);
        
        const valid = await checkAuth();
        if (valid) {
            showToast('Login berhasil!', 'success');
            fetchLogs();
            loadSettings();
        }
    });

    document.getElementById('profileDisplay').addEventListener('click', () => {
        userProfile = '';
        userPassword = '';
        localStorage.removeItem('zohoProfile');
        localStorage.removeItem('zohoPassword');
        document.getElementById('loginOverlay').style.display = 'flex';
        document.getElementById('loginUsername').value = '';
        document.getElementById('loginPassword').value = '';
        showToast('Logged out', 'info');
    });

    const attachSettings = (payload = {}) => {
        return { ...payload, profile: userProfile, password: userPassword };
    };

    const fetchLogs = async () => {
        try {
            const res = await fetch(`${API_URL}?action=get_logs`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings())
            });
            const data = await res.json();
            currentLogs = data.logs || [];
            renderLogs(currentLogs);
        } catch (err) {
            showToast('Failed to load logs', 'error');
        }
    };

    const renderLogs = (logs) => {
        const tbody = document.getElementById('logsTableBody');
        tbody.innerHTML = '';
        
        if (logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No logs found. Add one above.</td></tr>';
            return;
        }

        const sanitizeHTML = (str) => {
            if (!str) return '';
            return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        };

        logs.forEach(log => {
            const tr = document.createElement('tr');
            
            let statusClass = 'status-pending';
            if (log.status === 'final') statusClass = 'status-final';
            if (log.status === 'done') statusClass = 'status-done';

            const timeStr = log.duration 
                ? `${sanitizeHTML(log.startTime)} - ${sanitizeHTML(log.endTime)} (${sanitizeHTML(log.duration)})` 
                : `${sanitizeHTML(log.startTime)} - ${sanitizeHTML(log.endTime)}`;

            const safeProject = sanitizeHTML(log.project);
            const safeTask = sanitizeHTML(log.task);
            const safeNotes = sanitizeHTML(log.notes);
            const safeVendor = sanitizeHTML(log.vendor);

            tr.innerHTML = `
                <td style="text-align: center;"><input type="checkbox" class="log-checkbox" data-rowindex="${log.rowIndex}"></td>
                <td>${sanitizeHTML(log.startDate)}</td>
                <td>${timeStr}</td>
                <td>
                    <div style="font-weight: 500;">${safeProject} ${safeVendor ? '<span style="font-size:0.75rem; background: var(--panel-border); padding: 0.1rem 0.4rem; border-radius: 4px;">' + safeVendor + '</span>' : ''}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">${safeTask}</div>
                </td>
                <td><div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${safeNotes}">${safeNotes}</div></td>
                <td>
                    ${log.status === 'done' ? 
                        `<span class="status-badge status-done">done</span>` :
                        `<select class="status-dropdown status-badge ${statusClass}" data-rowindex="${log.rowIndex}" style="cursor: pointer; outline: none; appearance: none; -webkit-appearance: none; padding-right: 1.5rem; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right .4rem top 50%; background-size: .5rem auto;">
                            <option value="pending" ${log.status !== 'final' ? 'selected' : ''} style="background: #1e293b; color: #fcd34d;">pending</option>
                            <option value="final" ${log.status === 'final' ? 'selected' : ''} style="background: #1e293b; color: #93c5fd;">final</option>
                        </select>`
                    }
                </td>
                <td>
                    <button class="action-btn edit-btn" data-rowindex="${log.rowIndex}" title="Edit"><i class="fa-solid fa-pen"></i></button>
                    ${log.status !== 'done' ? `<button class="action-btn delete-btn" data-rowindex="${log.rowIndex}" title="Delete"><i class="fa-solid fa-trash"></i></button>` : ''}
                    ${log.taskUrl ? `<a href="${log.taskUrl}" target="_blank" class="action-btn" title="View in Zoho"><i class="fa-solid fa-external-link-alt"></i></a>` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Attach delete listeners
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const rowIndex = e.currentTarget.getAttribute('data-rowindex');
                if (confirm('Hapus log ini dari Google Sheet?')) {
                    try {
                        const res = await fetch(`${API_URL}?action=delete_log`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(attachSettings({ rowIndex }))
                        });
                        const data = await res.json();
                        if (data.success) {
                            showToast('Log deleted', 'success');
                            fetchLogs();
                        } else {
                            showToast('Error deleting log', 'error');
                        }
                    } catch (err) {
                        showToast('Error', 'error');
                    }
                }
            });
        });
        // Attach edit listeners
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const rowIndex = e.currentTarget.getAttribute('data-rowindex');
                const log = currentLogs.find(l => l.rowIndex == rowIndex);
                if(log) {
                    // Helper to format Date string to YYYY-MM-DD
                    const parseDate = (str) => {
                        if(!str) return '';
                        if(str.match(/^\d{4}-\d{2}-\d{2}$/)) return str;
                        const parts = str.split(/[-/]/);
                        if(parts.length === 3) {
                            let y = parts[2];
                            if(y.length === 2) y = '20' + y;
                            if(parts[0].length === 4) return `${parts[0]}-${parts[1].padStart(2,'0')}-${parts[2].padStart(2,'0')}`;
                            return `${y}-${parts[0].padStart(2,'0')}-${parts[1].padStart(2,'0')}`;
                        }
                        return str;
                    };

                    // Helper to format Time string to HH:MM (24-hour)
                    const parseTime = (str) => {
                        if(!str) return '';
                        const match = str.match(/(\d+):(\d+)/);
                        if(match) {
                            let h = parseInt(match[1], 10);
                            let m = match[2];
                            if(str.toLowerCase().includes('pm') && h < 12) h += 12;
                            if(str.toLowerCase().includes('am') && h === 12) h = 0;
                            return `${String(h).padStart(2,'0')}:${m}`;
                        }
                        return str;
                    };

                    document.getElementById('editRowIndex').value = rowIndex;
                    document.getElementById('logId').value = log.id;
                    document.getElementById('startDate').value = parseDate(log.startDate);
                    document.getElementById('startTime').value = parseTime(log.startTime);
                    document.getElementById('lembur').value = log.lembur;
                    document.getElementById('endDate').value = parseDate(log.endDate);
                    document.getElementById('endTime').value = parseTime(log.endTime);
                    document.getElementById('duration').value = log.duration;
                    document.getElementById('vendor').value = log.vendor;
                    document.getElementById('projectName').value = log.project;
                    document.getElementById('taskName').value = log.task;
                    document.getElementById('notes').value = log.notes;
                    document.getElementById('zohoStatus').value = log.status || 'final';
                    
                    document.querySelector('#submitLogBtn span').innerText = 'Save Changes';
                    document.querySelector('#submitLogBtn i').className = 'fa-solid fa-floppy-disk';
                    document.getElementById('cancelEditBtn').style.display = 'inline-flex';
                    window.scrollTo(0, 0);
                }
            });
        });

        // Attach status change listeners
        document.querySelectorAll('.status-dropdown').forEach(dropdown => {
            dropdown.addEventListener('change', async (e) => {
                const rowIndex = e.target.getAttribute('data-rowindex');
                const newStatus = e.target.value;
                const prevStatus = e.target.getAttribute('data-prev') || 'pending';
                e.target.disabled = true; // disable while saving
                try {
                    const res = await fetch(`${API_URL}?action=update_status`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(attachSettings({ rowIndex, status: newStatus }))
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast(`Status updated to ${newStatus}`, 'success');
                        const log = currentLogs.find(l => l.rowIndex == rowIndex);
                        if(log) log.status = newStatus;
                        e.target.setAttribute('data-prev', newStatus);
                        e.target.className = `status-dropdown status-badge status-${newStatus}`;
                    } else {
                        showToast(data.message || 'Error updating status', 'error');
                        e.target.value = prevStatus; // revert
                    }
                } catch (err) {
                    showToast('Network error', 'error');
                    e.target.value = prevStatus; // revert
                } finally {
                    e.target.disabled = false;
                }
            });
            // store initial state
            dropdown.setAttribute('data-prev', dropdown.value);
        });
    };

    // Cancel Edit
    document.getElementById('cancelEditBtn').addEventListener('click', () => {
        document.getElementById('addLogForm').reset();
        document.getElementById('editRowIndex').value = '';
        document.querySelector('#submitLogBtn span').innerText = 'Add Log Entry';
        document.querySelector('#submitLogBtn i').className = 'fa-solid fa-plus';
        document.getElementById('cancelEditBtn').style.display = 'none';
        document.getElementById('zohoStatus').value = 'final';
        document.getElementById('startDate').valueAsDate = new Date();
    });

    // Add / Edit Log Form
    document.getElementById('addLogForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const rowIndex = document.getElementById('editRowIndex').value;
        const action = rowIndex ? 'edit_log' : 'add_log';

        const payload = {
            id: document.getElementById('logId').value,
            startDate: document.getElementById('startDate').value,
            startTime: document.getElementById('startTime').value,
            lembur: document.getElementById('lembur').value,
            endDate: document.getElementById('endDate').value,
            endTime: document.getElementById('endTime').value,
            duration: document.getElementById('duration').value,
            status: document.getElementById('zohoStatus').value,
            vendor: document.getElementById('vendor').value,
            project: document.getElementById('projectName').value,
            task: document.getElementById('taskName').value,
            notes: document.getElementById('notes').value
        };

        if (rowIndex) {
            payload.rowIndex = rowIndex;
            const existingLog = currentLogs.find(l => l.rowIndex == rowIndex);
            payload.taskUrl = existingLog ? existingLog.taskUrl : '';
        }

        try {
            const res = await fetch(`${API_URL}?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings(payload))
            });
            const data = await res.json();
            if (data.success) {
                showToast(rowIndex ? 'Activity updated successfully' : 'Activity logged successfully', 'success');
                if (rowIndex) {
                    // Mode edit: kembalikan ke default mode add
                    document.getElementById('cancelEditBtn').click(); 
                } else {
                    // Mode add: Jangan bersihkan nama project dan task untuk mempermudah multiple input!
                    // Hanya bersihkan jam, durasi, notes, lembur
                    document.getElementById('startTime').value = '';
                    document.getElementById('endTime').value = '';
                    document.getElementById('duration').value = '';
                    document.getElementById('notes').value = '';
                    document.getElementById('logId').value = '';
                    document.getElementById('lembur').value = '';
                    // Notifikasi khusus
                    showToast('Project & Task dipertahankan untuk input berikutnya', 'info');
                }
                fetchLogs();
            } else {
                showToast(data.message || 'Error saving log', 'error');
            }
        } catch (err) {
            showToast('Network error', 'error');
        }
    });

    // Bulk Log Form Submission
    document.getElementById('bulkLogForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const startDateStr = document.getElementById('bulkStartDate').value;
        const endDateStr = document.getElementById('bulkEndDate').value;
        const excludeWeekends = document.getElementById('bulkExcludeWeekends').checked;
        const startTime = document.getElementById('bulkStartTime').value;
        const endTime = document.getElementById('bulkEndTime').value;
        const duration = document.getElementById('bulkDuration').value;
        const lembur = document.getElementById('bulkLembur').value;
        const vendor = document.getElementById('bulkVendor').value;
        const project = document.getElementById('bulkProjectName').value;
        const task = document.getElementById('bulkTaskName').value;
        const notes = document.getElementById('bulkNotes').value;
        const submitBtn = document.getElementById('submitBulkBtn');
        const progressDiv = document.getElementById('bulkProgress');

        let start = new Date(startDateStr);
        let end = new Date(endDateStr);

        if (start > end) {
            showToast('Start Date harus lebih kecil atau sama dengan End Date', 'error');
            return;
        }

        // Kumpulkan semua tanggal
        const datesToProcess = [];
        let current = new Date(start);
        while (current <= end) {
            const dayOfWeek = current.getDay(); // 0 = Sunday, 6 = Saturday
            const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
            
            if (!excludeWeekends || !isWeekend) {
                // Format YYYY-MM-DD
                const yyyy = current.getFullYear();
                const mm = String(current.getMonth() + 1).padStart(2, '0');
                const dd = String(current.getDate()).padStart(2, '0');
                datesToProcess.push(`${yyyy}-${mm}-${dd}`);
            }
            // Add 1 day
            current.setDate(current.getDate() + 1);
        }

        if (datesToProcess.length === 0) {
            showToast('Tidak ada hari kerja di rentang tanggal yang dipilih!', 'error');
            return;
        }

        if (!confirm(`Anda akan membuat ${datesToProcess.length} log terpisah dari ${datesToProcess[0]} sampai ${datesToProcess[datesToProcess.length-1]}. Lanjutkan?`)) {
            return;
        }

        submitBtn.disabled = true;
        let successCount = 0;
        let errorCount = 0;

        for (let i = 0; i < datesToProcess.length; i++) {
            const dateStr = datesToProcess[i];
            progressDiv.innerText = `Processing ${i+1}/${datesToProcess.length}: Menambahkan log untuk tanggal ${dateStr}...`;
            
            const payload = {
                id: '', // Biarkan backend generate ID kosong
                startDate: dateStr,
                startTime: startTime,
                lembur: lembur,
                endDate: dateStr,
                endTime: endTime,
                duration: duration,
                status: 'pending', // Biarkan pending agar bisa dicek dulu sebelum sync
                vendor: vendor,
                project: project,
                task: task,
                notes: notes
            };

            try {
                const res = await fetch(`${API_URL}?action=add_log`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings(payload))
                });
                const data = await res.json();
                if (data.success) {
                    successCount++;
                } else {
                    errorCount++;
                    console.error(`Error pada tanggal ${dateStr}: ${data.message}`);
                }
            } catch (err) {
                errorCount++;
                console.error(`Network error pada tanggal ${dateStr}`);
            }
        }

        progressDiv.innerText = `Selesai! Berhasil: ${successCount}, Gagal: ${errorCount}.`;
        showToast(`Bulk input selesai!`, 'success');
        submitBtn.disabled = false;
        fetchLogs(); // Reload table
        
        // Cukup clear notes dan tanggal, pertahankan sisanya
        document.getElementById('bulkNotes').value = '';
    });

    // Settings Management
    const loadZohoProjects = async () => {
        try {
            const btn = document.getElementById('refreshProjectsBtn');
            if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            
            const res = await fetch(`${API_URL}?action=get_zoho_projects`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings())
            });
            const data = await res.json();
            if (data.success && data.projects) {
                const datalist = document.getElementById('zohoProjectsList');
                datalist.innerHTML = '';
                data.projects.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p;
                    datalist.appendChild(option);
                });
                showToast('List Project dari Zoho berhasil diperbarui', 'info');
            } else if (!data.success && data.message.includes('Settings missing')) {
                // Ignore if settings are not filled yet
            } else {
                showToast('Gagal memuat list project: ' + (data.message || 'Unknown error'), 'warning');
            }
        } catch (err) {
            console.error('Error loading projects', err);
        } finally {
            const btn = document.getElementById('refreshProjectsBtn');
            if (btn) btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Load Projects';
        }
    };

    const loadSettings = async () => {
        try {
            const res = await fetch(`${API_URL}?action=get_settings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings())
            });
            const data = await res.json();
            if (data.settings) {
                document.getElementById('spreadsheetId').value = data.settings.spreadsheetId || '';
                document.getElementById('sheetName').value = data.settings.sheetName || 'Sheet1';
                document.getElementById('googleCredentials').value = data.settings.googleCredentials || '';
                document.getElementById('formAbsenUrl').value = data.settings.formAbsenUrl || '';
                document.getElementById('profilePassword').value = userPassword || ''; // backend no longer sends password
                document.getElementById('clientId').value = data.settings.clientId || '';
                document.getElementById('clientSecret').value = data.settings.clientSecret || '';
                document.getElementById('refreshToken').value = data.settings.refreshToken || '';
                document.getElementById('portalName').value = data.settings.portalName || '';
                if(data.settings.accountsUrl) document.getElementById('accountsUrl').value = data.settings.accountsUrl;
                if(data.settings.apiUrl) document.getElementById('apiUrl').value = data.settings.apiUrl;
            }
        } catch (err) {
            console.error('Error loading settings', err);
        }
    };

    document.getElementById('saveSettingsBtn').addEventListener('click', async (e) => {
        e.preventDefault();
        const payload = {
            settings: {
                spreadsheetId: document.getElementById('spreadsheetId').value,
                sheetName: document.getElementById('sheetName').value,
                googleCredentials: document.getElementById('googleCredentials').value,
                formAbsenUrl: document.getElementById('formAbsenUrl').value,
                profile_password: document.getElementById('profilePassword').value,
                clientId: document.getElementById('clientId').value,
                clientSecret: document.getElementById('clientSecret').value,
                refreshToken: document.getElementById('refreshToken').value,
                portalName: document.getElementById('portalName').value,
                accountsUrl: document.getElementById('accountsUrl').value,
                apiUrl: document.getElementById('apiUrl').value
            }
        };

        try {
            const res = await fetch(`${API_URL}?action=save_settings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings(payload))
            });
            const data = await res.json();
            if (data.success) {
                showToast('Settings saved securely on server', 'success');
                // Update active session password if they changed it
                userPassword = document.getElementById('profilePassword').value;
                localStorage.setItem('zohoPassword', userPassword);
            } else {
                showToast('Failed to save settings', 'error');
            }
        } catch (err) {
            showToast('Network error', 'error');
        }
    });

    const refreshBtn = document.getElementById('refreshProjectsBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            loadZohoProjects();
        });
    }

    // Sync Process
    const logConsole = (msg, type = 'info') => {
        const consoleEl = document.getElementById('syncConsole');
        const div = document.createElement('div');
        div.className = `log-${type}`;
        div.innerText = `> ${msg}`;
        consoleEl.appendChild(div);
        consoleEl.scrollTop = consoleEl.scrollHeight;
    };

    document.getElementById('startSyncBtn').addEventListener('click', async () => {
        const btn = document.getElementById('startSyncBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Syncing...';
        
        logConsole('Starting sync process...', 'info');

        try {
            const res = await fetch(`${API_URL}?action=sync`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings())
            });
            const data = await res.json();
            
            if (data.logs) {
                data.logs.forEach(logLine => {
                    logConsole(logLine.message, logLine.type);
                });
            }

            if (data.success) {
                showToast('Sync completed successfully', 'success');
            } else {
                showToast('Sync completed with errors', 'warning');
            }
            
        } catch (err) {
            logConsole(`Network error: ${err.message}`, 'error');
            showToast('Sync failed', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-bolt"></i> Start Sync';
            fetchLogs(); // refresh log table
        }
    });

    // Bulk Actions for Data Logs
    document.getElementById('selectAllLogs').addEventListener('change', (e) => {
        const checked = e.target.checked;
        document.querySelectorAll('.log-checkbox').forEach(cb => {
            cb.checked = checked;
        });
    });

    document.getElementById('btnExportCSV').addEventListener('click', () => {
        if (!currentLogs || currentLogs.length === 0) {
            showToast('No logs available to export', 'warning');
            return;
        }

        let csvContent = '\uFEFFDate,Start Time,End Time,Duration,Lembur,Vendor,Project,Task,Notes,Status\n';
        
        const escapeCSV = (str) => {
            if (str === null || str === undefined) return '""';
            let s = String(str);
            
            // Prevent CSV Injection (DDE attack) when opened in Excel
            if (/^[=+\-@\t\r]/.test(s)) {
                s = "'" + s;
            }
            
            if (s.includes(',') || s.includes('"') || s.includes('\n')) {
                return '"' + s.replace(/"/g, '""') + '"';
            }
            return s;
        };

        currentLogs.forEach(log => {
            const row = [
                log.startDate,
                log.startTime,
                log.endTime,
                log.duration,
                log.lembur,
                log.vendor,
                log.project,
                log.task,
                log.notes,
                log.status
            ];
            csvContent += row.map(escapeCSV).join(',') + '\n';
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        const dateStr = new Date().toISOString().split('T')[0];
        link.setAttribute('download', `HubTrack_Export_${dateStr}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        showToast('Export successful!', 'success');
    });

    document.getElementById('btnBulkDelete').addEventListener('click', async () => {
        const checkboxes = document.querySelectorAll('.log-checkbox:checked');
        if (checkboxes.length === 0) {
            showToast('Pilih setidaknya satu log', 'warning');
            return;
        }

        if (!confirm(`Hapus ${checkboxes.length} log terpilih?`)) return;

        const btn = document.getElementById('btnBulkDelete');
        btn.disabled = true;
        btn.innerText = 'Menghapus...';

        // Sort rowIndices descending to prevent row shifting issues
        let rowIndices = Array.from(checkboxes).map(cb => parseInt(cb.getAttribute('data-rowindex')));
        rowIndices.sort((a, b) => b - a);

        let successCount = 0;
        let errorCount = 0;

        for (const rowIndex of rowIndices) {
            try {
                const res = await fetch(`${API_URL}?action=delete_log`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ rowIndex }))
                });
                const data = await res.json();
                if (data.success) successCount++;
                else errorCount++;
            } catch (err) {
                errorCount++;
            }
        }

        showToast(`Selesai! Berhasil hapus: ${successCount}, Gagal: ${errorCount}`, successCount > 0 ? 'success' : 'error');
        document.getElementById('selectAllLogs').checked = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash"></i> Hapus Terpilih';
        fetchLogs();
    });

    document.getElementById('btnBulkStatus').addEventListener('click', async () => {
        const checkboxes = document.querySelectorAll('.log-checkbox:checked');
        if (checkboxes.length === 0) {
            showToast('Pilih setidaknya satu log', 'warning');
            return;
        }

        const newStatus = document.getElementById('bulkStatusSelect').value;
        if (!confirm(`Ubah ${checkboxes.length} log terpilih menjadi status "${newStatus}"?`)) return;

        const btn = document.getElementById('btnBulkStatus');
        btn.disabled = true;
        btn.innerText = 'Memproses...';

        const rowIndices = Array.from(checkboxes).map(cb => cb.getAttribute('data-rowindex'));
        
        let successCount = 0;
        let errorCount = 0;

        for (const rowIndex of rowIndices) {
            try {
                const res = await fetch(`${API_URL}?action=update_status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ rowIndex, status: newStatus }))
                });
                const data = await res.json();
                if (data.success) successCount++;
                else errorCount++;
            } catch (err) {
                errorCount++;
            }
        }

        showToast(`Selesai! Berhasil ubah: ${successCount}, Gagal: ${errorCount}`, successCount > 0 ? 'success' : 'error');
        document.getElementById('selectAllLogs').checked = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check-double"></i> Set Status';
        fetchLogs();
    });

    // Initialization
    // Set default date to today
    document.getElementById('startDate').valueAsDate = new Date();
    
    checkAuth().then(valid => {
        if (valid) {
            fetchLogs();
            loadSettings();
            loadZohoProjects();
        }
    });
});
