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
    let currentPage = 1;
    let itemsPerPage = 10;
    let currentSortOrder = 'desc';

    // Profile Management
    let userProfile = sessionStorage.getItem('zohoProfile') || '';
    let userPassword = sessionStorage.getItem('zohoPassword') || '';

    const checkAuth = async () => {
        if (!userProfile || !userPassword) {
            document.querySelector('header').style.display = 'none';
            document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
            document.getElementById('guide-view').classList.add('active');
            document.getElementById('guideLoginBanner').style.display = 'block';
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
                sessionStorage.removeItem('zohoPassword');
                document.querySelector('header').style.display = 'none';
                document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
                document.getElementById('guide-view').classList.add('active');
                document.getElementById('guideLoginBanner').style.display = 'block';
                return false;
            }
            // Auth OK
            document.getElementById('loginOverlay').style.display = 'none';
            document.querySelector('header').style.display = '';
            const banner = document.getElementById('guideLoginBanner');
            if (banner) banner.style.display = 'none';
            
            // If guide view was forced due to no login, reset back to logs view
            if(document.getElementById('guide-view').classList.contains('active') && !document.querySelector('.nav-btn[data-target="guide-view"]')?.classList.contains('active')) {
                document.getElementById('guide-view').classList.remove('active');
                document.getElementById('logs-view').classList.add('active');
            }

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
        sessionStorage.setItem('zohoProfile', userProfile);
        sessionStorage.setItem('zohoPassword', userPassword);
        
        const valid = await checkAuth();
        if (valid) {
            showToast('Login berhasil!', 'success');
            fetchLogs();
            loadSettings();
        }
    });

    document.getElementById('profileDisplay').addEventListener('click', () => {
        if (!confirm('Apakah Anda yakin ingin Logout?')) return;
        userProfile = '';
        userPassword = '';
        sessionStorage.removeItem('zohoProfile');
        sessionStorage.removeItem('zohoPassword');
        window.location.reload();
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
            renderLogs();
        } catch (err) {
            showToast('Failed to load logs', 'error');
        }
    };

    let monthlyChartInstance = null;

    document.getElementById('analyticsMonth')?.addEventListener('change', () => {
        if (typeof currentLogs !== 'undefined' && currentLogs) updateAnalytics(currentLogs);
    });
    document.getElementById('analyticsYear')?.addEventListener('change', () => {
        if (typeof currentLogs !== 'undefined' && currentLogs) updateAnalytics(currentLogs);
    });

    const initAnalyticsFilters = () => {
        const today = new Date();
        const m = document.getElementById('analyticsMonth');
        const y = document.getElementById('analyticsYear');
        if (m && m.value === 'all') m.value = String(today.getMonth() + 1).padStart(2, '0');
        if (y && y.value === 'all') y.value = String(today.getFullYear());
    };
    initAnalyticsFilters();

    const updateAnalytics = (logs) => {
        const selMonth = document.getElementById('analyticsMonth')?.value || 'all';
        const selYear = document.getElementById('analyticsYear')?.value || 'all';

        const dailyCounts = {};
        const projectCounts = {};
        const projectTaskCounts = {};

        logs.forEach(log => {
            const dateStr = log.startDate; 
            if (!dateStr) return;
            
            let logM = '', logY = '';
            if (dateStr.includes('-')) {
                const parts = dateStr.split('-');
                if (parts[0].length === 4) { 
                    logY = parts[0];
                    logM = parts[1];
                } else { 
                    logY = parts[2];
                    logM = parts[1];
                }
            }

            if (selMonth !== 'all' && logM !== selMonth) return;
            if (selYear !== 'all' && logY !== selYear) return;

            dailyCounts[dateStr] = (dailyCounts[dateStr] || 0) + 1;
            const proj = log.project || 'Unknown';
            const task = log.task || 'Unknown';
            
            projectCounts[proj] = (projectCounts[proj] || 0) + 1;
            
            if (!projectTaskCounts[proj]) projectTaskCounts[proj] = {};
            projectTaskCounts[proj][task] = (projectTaskCounts[proj][task] || 0) + 1;
        });

        const sortedDates = Object.keys(dailyCounts).sort((a, b) => {
            const da = new Date(a.split(/[-/]/).reverse().join('-'));
            const db = new Date(b.split(/[-/]/).reverse().join('-'));
            return da - db;
        });

        const chartLabels = sortedDates;
        const chartData = sortedDates.map(d => dailyCounts[d]);

        const ctx = document.getElementById('monthlyChart');
        if (ctx) {
            if (monthlyChartInstance) {
                monthlyChartInstance.destroy();
            }
            if (window.Chart) {
                monthlyChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Jumlah Input Harian',
                            data: chartData,
                            borderColor: '#a855f7',
                            backgroundColor: 'rgba(168, 85, 247, 0.2)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#f43f5e',
                            pointBorderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                            x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                        },
                        plugins: {
                            legend: { labels: { color: '#f8fafc', font: { family: 'Inter' } } }
                        }
                    }
                });
            }
        }

        const sortedProjects = Object.keys(projectCounts).sort((a, b) => projectCounts[b] - projectCounts[a]).slice(0, 10);
        const topProjectsContainer = document.getElementById('topProjectsList');
        if (topProjectsContainer) {
            topProjectsContainer.innerHTML = '';
            if (sortedProjects.length === 0) {
                topProjectsContainer.innerHTML = '<div style="color: var(--text-muted); text-align: center; padding: 1rem;">Belum ada project yang diinput.</div>';
            } else {
                sortedProjects.forEach((proj, index) => {
                    const count = projectCounts[proj];
                    const tasks = projectTaskCounts[proj];
                    
                    let taskListHTML = '<ul style="margin: 0.5rem 0 0 2rem; padding: 0; color: var(--text-muted); font-size: 0.85rem; list-style-type: disc;">';
                    const sortedTasks = Object.keys(tasks).sort((a, b) => tasks[b] - tasks[a]);
                    sortedTasks.forEach(t => {
                        taskListHTML += `<li style="margin-bottom: 0.2rem;">${t} <span style="color: #94a3b8; font-size: 0.75rem;">(${tasks[t]}x)</span></li>`;
                    });
                    taskListHTML += '</ul>';

                    const item = document.createElement('details');
                    item.style.cssText = 'background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 0.5rem; cursor: pointer;';
                    
                    let rankColor = 'var(--text-muted)';
                    if (index === 0) rankColor = '#fcd34d'; // Gold
                    else if (index === 1) rankColor = '#94a3b8'; // Silver
                    else if (index === 2) rankColor = '#b45309'; // Bronze
                    
                    item.innerHTML = `
                        <summary style="display: flex; justify-content: space-between; padding: 0.75rem 1rem; align-items: center; list-style: none;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="font-weight: bold; color: ${rankColor}; font-size: 1.2rem; min-width: 25px;">#${index + 1}</span>
                                <span style="color: var(--text-main); font-weight: 500;">${proj}</span>
                            </div>
                            <span style="background: linear-gradient(135deg, #f43f5e, #a855f7); color: white; padding: 0.2rem 0.8rem; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">${count} <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; margin-left: 0.3rem;"></i></span>
                        </summary>
                        <div style="padding: 0 1rem 1rem 1rem; border-top: 1px solid rgba(255,255,255,0.05);">
                            <div style="font-size: 0.8rem; font-weight: bold; color: var(--text-main); margin-top: 0.5rem;">Task yang paling sering diinput:</div>
                            ${taskListHTML}
                        </div>
                    `;
                    topProjectsContainer.appendChild(item);
                });
            }
        }
    };

    const renderLogs = () => {
        const logs = currentLogs;
        updateAnalytics(logs);
        const tbody = document.getElementById('logsTableBody');
        tbody.innerHTML = '';
        
        const statusFilter = document.getElementById('filterLogStatus') ? document.getElementById('filterLogStatus').value : 'all';
        let filteredLogs = [...logs]; // make a shallow copy to sort safely
        if (statusFilter !== 'all') {
            filteredLogs = filteredLogs.filter(l => l.status === statusFilter);
        }

        filteredLogs.sort((a, b) => {
            if (currentSortOrder === 'asc') {
                return a.rowIndex - b.rowIndex;
            } else {
                return b.rowIndex - a.rowIndex;
            }
        });

        if (filteredLogs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No logs found.</td></tr>';
            const paginationContainer = document.getElementById('logsPagination');
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(filteredLogs.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedLogs = filteredLogs.slice(startIndex, endIndex);

        const paginationContainer = document.getElementById('logsPagination');
        if (paginationContainer) {
            let pHtml = '';
            pHtml += `<button class="secondary action-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''} style="padding: 0.2rem 0.8rem; display: flex; align-items: center; gap: 0.3rem;"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
            
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }
            for (let i = startPage; i <= endPage; i++) {
                pHtml += `<button class="action-btn ${i === currentPage ? 'active' : 'secondary'}" data-page="${i}" style="padding: 0.2rem 0.6rem; ${i === currentPage ? 'background: var(--primary); border-color: var(--primary); color: white;' : ''}">${i}</button>`;
            }
            pHtml += `<button class="secondary action-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''} style="padding: 0.2rem 0.8rem; display: flex; align-items: center; gap: 0.3rem;">Next <i class="fa-solid fa-chevron-right"></i></button>`;
            
            paginationContainer.innerHTML = pHtml;
            paginationContainer.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (e.currentTarget.disabled) return;
                    currentPage = parseInt(e.currentTarget.getAttribute('data-page'));
                    renderLogs();
                });
            });
        }

        const sanitizeHTML = (str) => {
            if (!str) return '';
            return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        };

        paginatedLogs.forEach(log => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-status', log.status);
            
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
                    
                    const singleContainer = document.getElementById('dynamicSingleProjectTaskContainer');
                    const extraRows = singleContainer.querySelectorAll('.single-project-task-row:not(:first-child)');
                    extraRows.forEach(r => r.remove());
                    if (typeof updateSingleRemoveButtons === 'function') updateSingleRemoveButtons();
                    
                    const firstRow = singleContainer.querySelector('.single-project-task-row');
                    if (firstRow) {
                        firstRow.querySelector('.singleVendor').value = log.vendor || '';
                        firstRow.querySelector('.singleProjectName').value = log.project || '';
                        firstRow.querySelector('.singleTaskName').value = log.task || '';
                    }

                    document.querySelectorAll('.add-btn-daily').forEach(btn => btn.style.display = 'none');
                    
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
        document.querySelectorAll('.add-btn-daily').forEach(btn => btn.style.display = 'inline-block');
        
        const singleContainer = document.getElementById('dynamicSingleProjectTaskContainer');
        const extraRows = singleContainer.querySelectorAll('.single-project-task-row:not(:first-child)');
        extraRows.forEach(r => r.remove());
        if (typeof updateSingleRemoveButtons === 'function') updateSingleRemoveButtons();
    });

    // Add / Edit Log Form
    document.getElementById('addLogForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const rowIndex = document.getElementById('editRowIndex').value;
        const action = rowIndex ? 'edit_log' : 'add_log';

        const taskCombinations = [];
        const rows = document.querySelectorAll('.single-project-task-row');
        rows.forEach(row => {
            const vendor = row.querySelector('.singleVendor').value;
            const project = row.querySelector('.singleProjectName').value;
            const task = row.querySelector('.singleTaskName').value;
            if (project && task) {
                taskCombinations.push({ vendor, project, task });
            }
        });

        if (taskCombinations.length === 0) {
            showToast('Silakan isi setidaknya satu kombinasi Project & Task', 'error');
            return;
        }

        const basePayload = {
            id: document.getElementById('logId').value,
            startDate: document.getElementById('startDate').value,
            startTime: document.getElementById('startTime').value,
            lembur: document.getElementById('lembur').value,
            endDate: document.getElementById('endDate').value,
            endTime: document.getElementById('endTime').value,
            duration: document.getElementById('duration').value,
            status: document.getElementById('zohoStatus').value,
            notes: document.getElementById('notes').value
        };

        const submitBtn = document.querySelector('#submitLogBtn');
        submitBtn.disabled = true;
        let successCount = 0;
        let errorCount = 0;

        for(let i=0; i<taskCombinations.length; i++) {
            const combo = taskCombinations[i];
            const payload = { ...basePayload, vendor: combo.vendor, project: combo.project, task: combo.task };

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
                    successCount++;
                } else {
                    errorCount++;
                    console.error(`Error on task ${combo.task}: ${data.message}`);
                }
            } catch (err) {
                errorCount++;
                console.error(`Network error on task ${combo.task}`);
            }
        }

        submitBtn.disabled = false;

        if (successCount > 0) {
            showToast(rowIndex ? 'Activity updated successfully' : `Berhasil menyimpan ${successCount} log`, 'success');
            if (rowIndex) {
                document.getElementById('cancelEditBtn').click(); 
            } else {
                document.getElementById('startTime').value = '';
                document.getElementById('endTime').value = '';
                document.getElementById('duration').value = '';
                document.getElementById('notes').value = '';
                document.getElementById('logId').value = '';
                document.getElementById('lembur').value = '';
                showToast('Project & Task dipertahankan untuk input berikutnya', 'info');
            }
            fetchLogs();
        } else {
            showToast('Gagal menyimpan data log', 'error');
        }
    });

    // Dynamic Single Project & Task Rows (Daily Track)
    const singleContainer = document.getElementById('dynamicSingleProjectTaskContainer');
    
    function attachSingleRowListeners(row) {
        const btnAddTask = row.querySelector('.btn-add-single-task');
        const btnAddProject = row.querySelector('.btn-add-single-project');
        const btnAddBoth = row.querySelector('.btn-add-single-both');
        const btnRemove = row.querySelector('.btn-remove-single-row');

        if (btnAddTask) {
            btnAddTask.addEventListener('click', () => {
                duplicateSingleRow(row, { keepProject: true, keepTask: false, keepVendor: true });
            });
        }
        if (btnAddProject) {
            btnAddProject.addEventListener('click', () => {
                duplicateSingleRow(row, { keepProject: false, keepTask: true, keepVendor: true });
            });
        }
        if (btnAddBoth) {
            btnAddBoth.addEventListener('click', () => {
                duplicateSingleRow(row, { keepProject: false, keepTask: false, keepVendor: false });
            });
        }
        if (btnRemove) {
            btnRemove.addEventListener('click', () => {
                if (singleContainer.querySelectorAll('.single-project-task-row').length > 1) {
                    row.remove();
                    updateSingleRemoveButtons();
                }
            });
        }
    }

    function duplicateSingleRow(sourceRow, options) {
        const newRow = sourceRow.cloneNode(true);
        const vendorInput = newRow.querySelector('.singleVendor');
        const projectInput = newRow.querySelector('.singleProjectName');
        const taskInput = newRow.querySelector('.singleTaskName');
        if (!options.keepVendor) vendorInput.value = '';
        if (!options.keepProject) projectInput.value = '';
        if (!options.keepTask) taskInput.value = '';
        singleContainer.appendChild(newRow);
        attachSingleRowListeners(newRow);
        updateSingleRemoveButtons();
    }

    function updateSingleRemoveButtons() {
        if (!singleContainer) return;
        const rows = singleContainer.querySelectorAll('.single-project-task-row');
        rows.forEach(row => {
            const btnRemove = row.querySelector('.btn-remove-single-row');
            if (rows.length > 1) btnRemove.style.display = 'block';
            else btnRemove.style.display = 'none';
        });
    }

    if (singleContainer) {
        const firstRow = singleContainer.querySelector('.single-project-task-row');
        if (firstRow) attachSingleRowListeners(firstRow);
    }

    // Dynamic Project & Task Rows
    const dynamicContainer = document.getElementById('dynamicProjectTaskContainer');
    
    function attachDynamicRowListeners(row) {
        const btnAddTask = row.querySelector('.btn-add-task');
        const btnAddProject = row.querySelector('.btn-add-project');
        const btnAddBoth = row.querySelector('.btn-add-both');
        const btnRemove = row.querySelector('.btn-remove-row');

        if (btnAddTask) {
            btnAddTask.addEventListener('click', () => {
                duplicateRow(row, { keepProject: true, keepTask: false, keepVendor: true });
            });
        }
        if (btnAddProject) {
            btnAddProject.addEventListener('click', () => {
                duplicateRow(row, { keepProject: false, keepTask: true, keepVendor: true });
            });
        }
        if (btnAddBoth) {
            btnAddBoth.addEventListener('click', () => {
                duplicateRow(row, { keepProject: false, keepTask: false, keepVendor: false });
            });
        }
        if (btnRemove) {
            btnRemove.addEventListener('click', () => {
                if (dynamicContainer.querySelectorAll('.project-task-row').length > 1) {
                    row.remove();
                    updateRemoveButtons();
                }
            });
        }
    }

    function duplicateRow(sourceRow, options) {
        const newRow = sourceRow.cloneNode(true);
        
        const vendorInput = newRow.querySelector('.bulkVendor');
        const projectInput = newRow.querySelector('.bulkProjectName');
        const taskInput = newRow.querySelector('.bulkTaskName');
        
        if (!options.keepVendor) vendorInput.value = '';
        if (!options.keepProject) projectInput.value = '';
        if (!options.keepTask) taskInput.value = '';
        
        dynamicContainer.appendChild(newRow);
        attachDynamicRowListeners(newRow);
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const rows = dynamicContainer.querySelectorAll('.project-task-row');
        rows.forEach(row => {
            const btnRemove = row.querySelector('.btn-remove-row');
            if (rows.length > 1) {
                btnRemove.style.display = 'block';
            } else {
                btnRemove.style.display = 'none';
            }
        });
    }

    // Initialize first row
    if (dynamicContainer) {
        const firstRow = dynamicContainer.querySelector('.project-task-row');
        if (firstRow) attachDynamicRowListeners(firstRow);
    }

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
        const notes = document.getElementById('bulkNotes').value;
        const submitBtn = document.getElementById('submitBulkBtn');
        const progressDiv = document.getElementById('bulkProgress');

        const taskCombinations = [];
        const rows = document.querySelectorAll('.project-task-row');
        rows.forEach(row => {
            const vendor = row.querySelector('.bulkVendor').value;
            const project = row.querySelector('.bulkProjectName').value;
            const task = row.querySelector('.bulkTaskName').value;
            if (project && task) {
                taskCombinations.push({ vendor, project, task });
            }
        });

        if (taskCombinations.length === 0) {
            showToast('Silakan isi setidaknya satu kombinasi Project & Task', 'error');
            return;
        }

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

        const totalLogsToCreate = datesToProcess.length * taskCombinations.length;
        if (!confirm(`Anda akan membuat ${totalLogsToCreate} log terpisah (${taskCombinations.length} tugas x ${datesToProcess.length} hari). Lanjutkan?`)) {
            return;
        }

        submitBtn.disabled = true;
        let successCount = 0;
        let errorCount = 0;
        let processed = 0;

        for (let i = 0; i < datesToProcess.length; i++) {
            const dateStr = datesToProcess[i];
            for (let j = 0; j < taskCombinations.length; j++) {
                processed++;
                const combo = taskCombinations[j];
                progressDiv.innerText = `Processing ${processed}/${totalLogsToCreate}: Menambahkan ${combo.task} (${dateStr})...`;
                
                const payload = {
                    id: '', // Biarkan backend generate ID kosong
                    startDate: dateStr,
                    startTime: startTime,
                    lembur: lembur,
                    endDate: dateStr,
                    endTime: endTime,
                    duration: duration,
                    status: 'pending', // Biarkan pending agar bisa dicek dulu sebelum sync
                    vendor: combo.vendor,
                    project: combo.project,
                    task: combo.task,
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
                        console.error(`Error pada tanggal ${dateStr} - Task: ${combo.task}: ${data.message}`);
                    }
                } catch (err) {
                    errorCount++;
                    console.error(`Network error pada tanggal ${dateStr} - Task: ${combo.task}`);
                }
            }
        }

        progressDiv.innerText = `Selesai! Berhasil: ${successCount}, Gagal: ${errorCount}`;
        setTimeout(() => {
            progressDiv.innerText = '';
        }, 3000);
        submitBtn.disabled = false;
        
        if (successCount > 0) {
            const keepValues = document.getElementById('keepValues') ? document.getElementById('keepValues').checked : false;
            if (!keepValues) {
                // Clear the first row task combinations, and remove the rest
                const rows = document.querySelectorAll('.project-task-row');
                for (let i = 1; i < rows.length; i++) {
                    rows[i].remove();
                }
                updateRemoveButtons();
                const firstRow = document.querySelector('.project-task-row');
                if (firstRow) {
                    firstRow.querySelector('.bulkProjectName').value = '';
                    firstRow.querySelector('.bulkTaskName').value = '';
                    firstRow.querySelector('.bulkVendor').value = '';
                }
                document.getElementById('bulkNotes').value = '';
                document.getElementById('bulkStartDate').value = '';
                document.getElementById('bulkEndDate').value = '';
                showToast('Fast-Track berhasil, form dibersihkan', 'success');
            } else {
                // Cukup clear notes dan tanggal, pertahankan sisanya
                document.getElementById('bulkNotes').value = '';
                document.getElementById('bulkStartDate').value = '';
                document.getElementById('bulkEndDate').value = '';
                showToast('Fast-Track berhasil, nilai form dipertahankan', 'info');
            }
            fetchLogs();
        }
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

    const btnGenerateToken = document.getElementById('btnGenerateToken');
    if (btnGenerateToken) {
        btnGenerateToken.addEventListener('click', async () => {
            const cid = document.getElementById('clientId').value.trim();
            const sec = document.getElementById('clientSecret').value.trim();
            const code = document.getElementById('tempAuthCode').value.trim();
            const accUrl = document.getElementById('accountsUrl').value;

            if (!cid || !sec || !code) {
                showToast('Client ID, Client Secret, dan Auth Code harus diisi!', 'warning');
                return;
            }

            const originalText = btnGenerateToken.innerHTML;
            btnGenerateToken.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
            btnGenerateToken.disabled = true;

            try {
                const res = await fetch(`${API_URL}?action=generate_zoho_token`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        profile: userProfile,
                        password: userPassword,
                        client_id: cid,
                        client_secret: sec,
                        code: code,
                        accountsUrl: accUrl
                    })
                });
                const data = await res.json();
                if (data.success && data.refresh_token) {
                    document.getElementById('refreshToken').value = data.refresh_token;
                    document.getElementById('tempAuthCode').value = '';
                    showToast('Refresh Token berhasil digenerate!', 'success');
                } else {
                    showToast('Gagal: ' + (data.message || 'Kode salah/kadaluarsa'), 'error');
                }
            } catch (err) {
                showToast('Error generating token', 'error');
            }
            
            btnGenerateToken.innerHTML = originalText;
            btnGenerateToken.disabled = false;
        });
    }

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
                const newPass = data.new_password;
                if (newPass) {
                    userPassword = newPass;
                    sessionStorage.setItem('zohoPassword', userPassword);
                    showToast('Password updated successfully!', 'success');
                }
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
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = checked;
            }
        });
    });

    // Filter & Sort Logic
    const filterLogStatus = document.getElementById('filterLogStatus');
    if (filterLogStatus) {
        filterLogStatus.addEventListener('change', () => {
            currentPage = 1;
            document.getElementById('selectAllLogs').checked = false;
            renderLogs();
        });
    }

    const sortDateHeader = document.getElementById('sortDateHeader');
    if (sortDateHeader) {
        sortDateHeader.addEventListener('click', () => {
            currentSortOrder = currentSortOrder === 'desc' ? 'asc' : 'desc';
            const icon = document.getElementById('sortDateIcon');
            if (icon) {
                icon.className = currentSortOrder === 'desc' ? 'fa-solid fa-sort-down' : 'fa-solid fa-sort-up';
            }
            currentPage = 1;
            renderLogs();
        });
    }

    const logsPerPageSelect = document.getElementById('logsPerPage');
    if (logsPerPageSelect) {
        logsPerPageSelect.addEventListener('change', (e) => {
            itemsPerPage = parseInt(e.target.value);
            currentPage = 1;
            renderLogs();
        });
    }

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

        try {
            const res = await fetch(`${API_URL}?action=bulk_delete_logs`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings({ rowIndices }))
            });
            const data = await res.json();
            if (data.success) {
                showToast(`Selesai! Berhasil hapus ${rowIndices.length} log.`, 'success');
            } else {
                showToast(`Gagal: ${data.message || 'Error'}`, 'error');
            }
        } catch (err) {
            showToast('Kesalahan jaringan', 'error');
        }
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
        
        const updates = rowIndices.map(rowIndex => ({ rowIndex, status: newStatus }));

        try {
            const res = await fetch(`${API_URL}?action=bulk_update_status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings({ updates }))
            });
            const data = await res.json();
            if (data.success) {
                showToast(`Selesai! Berhasil ubah status ${updates.length} log.`, 'success');
            } else {
                showToast(`Gagal: ${data.message || 'Error'}`, 'error');
            }
        } catch (err) {
            showToast('Kesalahan jaringan', 'error');
        }
        document.getElementById('selectAllLogs').checked = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check-double"></i> Set Status';
        fetchLogs();
    });

    // Auto-calculate Duration
    const calculateDuration = (startId, endId, durationId) => {
        const start = document.getElementById(startId).value;
        const end = document.getElementById(endId).value;
        const durationInput = document.getElementById(durationId);

        if (start && end) {
            const [startHours, startMinutes] = start.split(':').map(Number);
            const [endHours, endMinutes] = end.split(':').map(Number);
            
            let startMins = startHours * 60 + startMinutes;
            let endMins = endHours * 60 + endMinutes;
            
            if (endMins < startMins) {
                endMins += 24 * 60; // Assume crosses midnight
            }
            
            const diffMins = endMins - startMins;
            const hours = Math.floor(diffMins / 60);
            const minutes = diffMins % 60;
            
            durationInput.value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        }
    };

    ['startTime', 'endTime'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', () => calculateDuration('startTime', 'endTime', 'duration'));
    });

    ['bulkStartTime', 'bulkEndTime'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', () => calculateDuration('bulkStartTime', 'bulkEndTime', 'bulkDuration'));
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
