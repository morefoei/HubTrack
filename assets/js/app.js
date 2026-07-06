document.addEventListener('DOMContentLoaded', () => {
    // Clean up index.php from URL for a cleaner look
    if (window.location.pathname.endsWith('index.php')) {
        const cleanUrl = window.location.pathname.replace(/index\.php$/, '') + window.location.search;
        window.history.replaceState(null, '', cleanUrl);
    }

    // Navigation
    const navBtns = document.querySelectorAll('.nav-btn');
    const viewSections = document.querySelectorAll('.view-section');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');

    mobileMenuBtn.addEventListener('click', () => {
        mainNav.classList.toggle('show');
    });

    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-minimized');
            // Toggle icon
            const icon = sidebarToggleBtn.querySelector('.toggle-icon');
            if (document.body.classList.contains('sidebar-minimized')) {
                icon.classList.remove('fa-angles-left');
                icon.classList.add('fa-angles-right');
            } else {
                icon.classList.remove('fa-angles-right');
                icon.classList.add('fa-angles-left');
            }
        });
    }

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
                loadAbsenPlans();
            } else if (targetId === 'settings-view') {
                loadSettings();
            } else if (targetId === 'logs-view' || targetId === 'data-view') {
                loadProjects();
            }

            // --- SPA ROUTING: Update URL dynamically ---
            const routeName = targetId.replace('-view', ''); // 'logs-view' -> 'logs'
            // Only push if it's not already the current path to avoid duplicate history states
            const currentPath = window.location.pathname.replace(/^\/|\/$/g, '');
            if (currentPath !== routeName) {
                // Determine base path to avoid breaking if app is in a subfolder
                let pathPrefix = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
                if (!pathPrefix) pathPrefix = '';
                window.history.pushState({ target: targetId }, '', `${pathPrefix}/${routeName}`);
            }
        });
    });

    // --- SPA ROUTING: Read URL on initial load ---
    const initRouting = () => {
        let path = window.location.pathname.substring(window.location.pathname.lastIndexOf('/') + 1).replace('.php', '');
        if (path && path !== 'index' && path !== 'login') {
            const targetViewId = path + '-view';
            const targetBtn = document.querySelector(`.nav-btn[data-target="${targetViewId}"]`);
            if (targetBtn) {
                targetBtn.click();
                // Open parent details dropdown if necessary
                const parentDetails = targetBtn.closest('details.nav-dropdown');
                if (parentDetails) parentDetails.setAttribute('open', 'true');
            }
        } else {
            // Default load (e.g. Absen View)
            const defaultBtn = document.querySelector('.nav-btn[data-target="absen-view"]');
            if(defaultBtn) defaultBtn.click();
        }
    };
    initRouting();

    // Handle Browser Back/Forward buttons
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.target) {
            const targetBtn = document.querySelector(`.nav-btn[data-target="${e.state.target}"]`);
            if (targetBtn) targetBtn.click();
        } else {
            // Fallback for default route
            initRouting();
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('details.nav-dropdown')) {
            document.querySelectorAll('details.nav-dropdown').forEach(d => d.removeAttribute('open'));
        }
    });

    // Exclusive dropdown accordion behavior
    const navDropdowns = document.querySelectorAll('details.nav-dropdown');
    navDropdowns.forEach(dropdown => {
        dropdown.addEventListener('toggle', () => {
            if (dropdown.open) {
                navDropdowns.forEach(other => {
                    if (other !== dropdown && other.open) {
                        other.removeAttribute('open');
                    }
                });
            }
        });
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
    
    if (userProfile === 'superman') {
        const adminBtns = document.querySelectorAll('.admin-only-btn');
        adminBtns.forEach(btn => btn.style.display = 'flex');
        
        // Sembunyikan menu lain untuk superman
        document.querySelectorAll('details.nav-dropdown').forEach((el) => {
            el.style.display = 'none';
        });
        
        if (adminBtns.length > 0) {
            const parentDetails = adminBtns[0].closest('details.nav-dropdown');
            if (parentDetails) {
                parentDetails.style.display = 'block';
                const summary = parentDetails.querySelector('.nav-dropdown-summary');
                if (summary) {
                    summary.innerHTML = '<i class="fa-solid fa-user-shield"></i> <span class="nav-text">Administration</span>';
                }
                
                parentDetails.querySelectorAll('.nav-btn').forEach(btn => {
                    if (!btn.classList.contains('admin-only-btn')) btn.style.display = 'none';
                });
            }
            // Paksa masuk ke admin global view
            setTimeout(() => {
                adminBtns[0].click();
            }, 100);
        }
    }

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
            const loginOverlay = document.getElementById('loginOverlay');
            if (loginOverlay) loginOverlay.style.display = 'none';
            const header = document.querySelector('header');
            if (header) header.style.display = '';
            const banner = document.getElementById('guideLoginBanner');
            if (banner) banner.style.display = 'none';
            
            // If guide view was forced due to no login, reset back to logs view
            if(document.getElementById('guide-view').classList.contains('active') && !document.querySelector('.nav-btn[data-target="guide-view"]')?.classList.contains('active')) {
                document.getElementById('guide-view').classList.remove('active');
                document.getElementById('logs-view').classList.add('active');
            }

            document.getElementById('profileNameDisplay').innerText = userProfile || 'Profile';
            return true;
        } catch (err) {
            return false;
        }
    };



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
                        taskListHTML += `<li style="margin-bottom: 0.2rem;">${sanitizeHTML(t)} <span style="color: #94a3b8; font-size: 0.75rem;">(${tasks[t]}x)</span></li>`;
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
                                <span style="color: var(--text-main); font-weight: 500;">${sanitizeHTML(proj)}</span>
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
        const monthFilter = document.getElementById('filterLogMonth') ? document.getElementById('filterLogMonth').value : 'all';
        
        let filteredLogs = [...logs]; // make a shallow copy to sort safely
        if (statusFilter !== 'all') {
            filteredLogs = filteredLogs.filter(l => l.status === statusFilter);
        }
        
        if (monthFilter !== 'all') {
            filteredLogs = filteredLogs.filter(l => {
                if (!l.startDate) return false;
                
                // parseDate logic similar to edit logic to ensure robust month extraction
                let str = l.startDate;
                let m = null;
                if(str.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    m = str.split('-')[1];
                } else {
                    const parts = str.split(/[-/]/);
                    if(parts.length === 3) {
                        if(parts[0].length === 4) {
                            m = parts[1].padStart(2, '0');
                        } else {
                            m = parts[0].padStart(2, '0');
                        }
                    }
                }
                return m === monthFilter;
            });
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
                        `<select class="status-dropdown status-badge status-done" data-rowindex="${log.rowIndex}" data-prev="done" style="cursor: pointer; outline: none; appearance: none; -webkit-appearance: none; padding-right: 1.5rem; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right .4rem top 50%; background-size: .5rem auto;">
                            <option value="done" selected style="background: #1e293b; color: #34d399;">done</option>
                            <option value="pending" style="background: #1e293b; color: #fcd34d;">pending</option>
                            <option value="final" style="background: #1e293b; color: #93c5fd;">final</option>
                        </select>` :
                        `<select class="status-dropdown status-badge ${statusClass}" data-prev="${sanitizeHTML(log.status)}" data-rowindex="${log.rowIndex}" style="cursor: pointer; outline: none; appearance: none; -webkit-appearance: none; padding-right: 1.5rem; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right .4rem top 50%; background-size: .5rem auto;">
                            <option value="pending" ${log.status !== 'final' ? 'selected' : ''} style="background: #1e293b; color: #fcd34d;">pending</option>
                            <option value="final" ${log.status === 'final' ? 'selected' : ''} style="background: #1e293b; color: #93c5fd;">final</option>
                            <option value="done" style="background: #1e293b; color: #34d399;">done</option>
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
                try {
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
                    document.getElementById('taskUrl').value = log.taskUrl || '';
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
                        const parts = (log.task || '').split(' > ');
                    firstRow.querySelector('.singleTaskName').value = parts[0]?.trim() || '';
                    const singleSub = firstRow.querySelector('.singleSubTaskName');
                    if (singleSub) {
                        singleSub.value = parts.slice(1).join(' > ').trim();
                    }
                    }

                    document.querySelectorAll('.add-btn-daily').forEach(btn => btn.style.display = 'none');
                    
                    document.getElementById('notes').value = log.notes;
                    document.getElementById('zohoStatus').value = log.status || 'final';
                    
                    document.querySelector('#submitLogBtn span').innerText = 'Save Changes';
                    document.querySelector('#submitLogBtn i').className = 'fa-solid fa-floppy-disk';
                    document.getElementById('cancelEditBtn').style.display = 'inline-flex';
                    
                    // Switch to Daily-Track tab where the form is located
                    const dailyTrackBtn = document.querySelector('button[data-target="logs-view"]');
                    if (dailyTrackBtn) dailyTrackBtn.click();
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    alert('Log tidak ditemukan di memory (currentLogs). RowIndex: ' + rowIndex);
                }
                } catch(err) {
                    alert("Error saat klik edit: " + err.message + "\n\nStack: " + err.stack);
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
            let task = row.querySelector('.singleTaskName').value;
            const singleSub = row.querySelector('.singleSubTaskName');
            if (singleSub && singleSub.value.trim()) {
                task += ' > ' + singleSub.value.trim();
            }
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
            notes: document.getElementById('notes').value,
            taskUrl: document.getElementById('taskUrl').value
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
        taskInput.value = '';
        const singleSub = newRow.querySelector('.singleSubTaskName');
        if (singleSub) singleSub.value = '';
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
        taskInput.value = '';
        const bulkSub = newRow.querySelector('.bulkSubTaskName');
        if (bulkSub) bulkSub.value = '';
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

    const btnAddExcludeDate = document.getElementById('btnAddExcludeDate');
    const dynamicExcludeDatesContainer = document.getElementById('dynamicExcludeDatesContainer');
    if (btnAddExcludeDate && dynamicExcludeDatesContainer) {
        btnAddExcludeDate.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'exclude-date-row';
            row.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
            row.innerHTML = `
                <input type="date" class="excludeDateInput" style="flex: 1; font-size: 0.85rem; padding: 0.4rem; height: 35px;" required>
                <button type="button" class="btn-remove-exclude-date" style="background: transparent; color: var(--danger); border: none; cursor: pointer; padding: 0 0.5rem;"><i class="fa-solid fa-xmark"></i></button>
            `;
            row.querySelector('.btn-remove-exclude-date').addEventListener('click', () => {
                row.remove();
            });
            dynamicExcludeDatesContainer.appendChild(row);
        });
    }

    const btnAddIncludeDate = document.getElementById('btnAddIncludeDate');
    const dynamicIncludeDatesContainer = document.getElementById('dynamicIncludeDatesContainer');
    if (btnAddIncludeDate && dynamicIncludeDatesContainer) {
        btnAddIncludeDate.addEventListener('click', () => {
            const row = document.createElement('div');
            row.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
            row.innerHTML = `
                <input type="date" class="includeDateInput" style="flex: 1; font-size: 0.85rem; padding: 0.4rem; height: 35px;" required>
                <button type="button" class="btn-remove-include-date" style="background: transparent; color: var(--danger); border: none; cursor: pointer; padding: 0 0.5rem;"><i class="fa-solid fa-xmark"></i></button>
            `;
            row.querySelector('.btn-remove-include-date').addEventListener('click', () => {
                row.remove();
            });
            dynamicIncludeDatesContainer.appendChild(row);
        });
    }

    const btnAddSpecificNote = document.getElementById('btnAddSpecificNote');
    const dynamicSpecificNotesContainer = document.getElementById('dynamicSpecificNotesContainer');
    if (btnAddSpecificNote && dynamicSpecificNotesContainer) {
        btnAddSpecificNote.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'form-row specific-note-row';
            row.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
            row.innerHTML = `
                <input type="date" class="specificNoteDate" style="flex: 0 0 130px; font-size: 0.85rem; padding: 0.4rem; height: 35px;" required>
                <input type="text" class="specificNoteText" placeholder="Catatan tambahan di tanggal ini..." style="flex: 1; font-size: 0.85rem; padding: 0.4rem; height: 35px;" required>
                <button type="button" class="btn-remove-specific-note" style="background: transparent; color: var(--danger); border: none; cursor: pointer; padding: 0 0.5rem;"><i class="fa-solid fa-trash"></i></button>
            `;
            row.querySelector('.btn-remove-specific-note').addEventListener('click', () => {
                row.remove();
            });
            dynamicSpecificNotesContainer.appendChild(row);
        });
    }

    // Bulk Log Form Submission
    document.getElementById('bulkLogForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const startDateStr = document.getElementById('bulkStartDate').value;
        const endDateStr = document.getElementById('bulkEndDate').value;
        const excludeWeekends = document.getElementById('bulkExcludeWeekends').checked;
        const excludeHolidaysCheckbox = document.getElementById('bulkExcludeHolidays');
        const excludeHolidays = excludeHolidaysCheckbox ? excludeHolidaysCheckbox.checked : false;
        
        const excludeDatesArr = [];
        document.querySelectorAll('.excludeDateInput').forEach(input => {
            if (input.value) excludeDatesArr.push(input.value);
        });
        
        const includeDatesArr = [];
        document.querySelectorAll('.includeDateInput').forEach(input => {
            if (input.value) includeDatesArr.push(input.value);
        });
        
        const startTime = document.getElementById('bulkStartTime').value;
        const endTime = document.getElementById('bulkEndTime').value;
        const duration = document.getElementById('bulkDuration').value;
        const lembur = document.getElementById('bulkLembur').value;
        const notes = document.getElementById('bulkNotes').value;
        const submitBtn = document.getElementById('submitBulkBtn');
        const progressDiv = document.getElementById('bulkProgress');

        const specificNotesMap = {};
        document.querySelectorAll('.specific-note-row').forEach(row => {
            const dateVal = row.querySelector('.specificNoteDate').value;
            const textVal = row.querySelector('.specificNoteText').value;
            if (dateVal && textVal) {
                specificNotesMap[dateVal] = specificNotesMap[dateVal] ? specificNotesMap[dateVal] + '\n- ' + textVal : '- ' + textVal;
            }
        });

        const taskCombinations = [];
        const rows = document.querySelectorAll('.project-task-row');
        rows.forEach(row => {
            const vendor = row.querySelector('.bulkVendor').value;
            const project = row.querySelector('.bulkProjectName').value;
            let task = row.querySelector('.bulkTaskName').value;
            const bulkSub = row.querySelector('.bulkSubTaskName');
            if (bulkSub && bulkSub.value.trim()) {
                task += ' > ' + bulkSub.value.trim();
            }
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

        submitBtn.disabled = true;

        if (excludeHolidays) {
            progressDiv.innerText = `Mengambil data Libur Nasional...`;
            try {
                const res = await fetch(`${API_URL}?action=get_indonesian_holidays`, { method: 'POST' });
                const data = await res.json();
                if (data.success && data.holidays) {
                    data.holidays.forEach(holiday => {
                        if (!excludeDatesArr.includes(holiday.date)) {
                            excludeDatesArr.push(holiday.date);
                        }
                    });
                }
            } catch (err) {
                console.error("Gagal mengambil libur nasional", err);
            }
            progressDiv.innerText = '';
        }

        // Kumpulkan semua tanggal
        const datesToProcess = [];
        let current = new Date(start);
        while (current <= end) {
            const dayOfWeek = current.getDay(); // 0 = Sunday, 6 = Saturday
            const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
            
            // Format YYYY-MM-DD
            const yyyy = current.getFullYear();
            const mm = String(current.getMonth() + 1).padStart(2, '0');
            const dd = String(current.getDate()).padStart(2, '0');
            const currentDateStr = `${yyyy}-${mm}-${dd}`;
            
            if (includeDatesArr.includes(currentDateStr)) {
                // If forcefully included, bypass all exclusions
                datesToProcess.push(currentDateStr);
            } else if (!excludeWeekends || !isWeekend) {
                if (!excludeDatesArr.includes(currentDateStr)) {
                    datesToProcess.push(currentDateStr);
                }
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
            
            let finalNotes = notes;
            if (specificNotesMap[dateStr]) {
                finalNotes = finalNotes ? finalNotes + '\n' + specificNotesMap[dateStr] : specificNotesMap[dateStr];
            }

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
                    notes: finalNotes
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
                    const bulkSub = firstRow.querySelector('.bulkSubTaskName');
                    if (bulkSub) bulkSub.value = '';
                    firstRow.querySelector('.bulkVendor').value = '';
                }
                document.getElementById('bulkNotes').value = '';
                document.getElementById('bulkStartDate').value = '';
                document.getElementById('bulkEndDate').value = '';
                const excludeContainer = document.getElementById('dynamicExcludeDatesContainer');
                if (excludeContainer) excludeContainer.innerHTML = '';
                const specificContainer = document.getElementById('dynamicSpecificNotesContainer');
                if (specificContainer) specificContainer.innerHTML = '';
                showToast('Fast-Track berhasil, form dibersihkan', 'success');
            } else {
                // Cukup clear notes dan tanggal, pertahankan sisanya
                document.getElementById('bulkNotes').value = '';
                document.getElementById('bulkStartDate').value = '';
                document.getElementById('bulkEndDate').value = '';
                const excludeContainer2 = document.getElementById('dynamicExcludeDatesContainer');
                if (excludeContainer2) excludeContainer2.innerHTML = '';
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
                alert('Gagal memuat list project: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            console.error('Error loading projects', err);
            alert('Koneksi ke server gagal saat memuat project: ' + err.message);
        } finally {
            const btn = document.getElementById('refreshProjectsBtn');
            if (btn) btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Load Projects';
        }
    };

    const toggleManualSheetSettings = (mode) => {
        const container = document.getElementById('manualSheetSettingsContainer');
        const grpId = document.getElementById('groupSpreadsheetId');
        const grpName = document.getElementById('groupSheetName');
        const grpCred = document.getElementById('groupGoogleCredentials');
        
        if (container) {
            container.style.display = 'block';
            
            if (mode === 'admin') {
                if (grpId) grpId.style.display = 'none';
                if (grpCred) grpCred.style.display = 'none';
            } else {
                if (grpId) grpId.style.display = 'block';
                if (grpCred) grpCred.style.display = 'block';
            }
        }
    };

    if (document.getElementById('sheetConfigMode')) {
        document.getElementById('sheetConfigMode').addEventListener('change', (e) => {
            toggleManualSheetSettings(e.target.value);
        });
    }

    const loadAbsenPlans = async () => {
        try {
            const res = await fetch(`${API_URL}?action=get_absen_plans`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings())
            });
            const data = await res.json();
            const tbody = document.getElementById('absenPlansTableBody');
            if (!tbody) return;
            
            if (data.success && data.data && data.data.length > 0) {
                tbody.innerHTML = '';
                data.data.forEach(plan => {
                    const tr = document.createElement('tr');
                    let tglDisplay = plan.startDate;
                    if (plan.startDate !== plan.endDate) {
                        tglDisplay = `${plan.startDate} - ${plan.endDate}`;
                    }
                    
                    tr.innerHTML = `
                        <td>${tglDisplay}</td>
                        <td>
                            <select class="select-edit-plan-type" data-id="${plan.id}" style="background: rgba(168,85,247,0.1); color: #c084fc; border: 1px solid rgba(168,85,247,0.3); padding: 0.3rem 0.6rem; border-radius: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; outline: none; transition: all 0.2s;">
                                <option value="Sakit" ${plan.planType === 'Sakit' ? 'selected' : ''}>Sakit</option>
                                <option value="Izin" ${plan.planType === 'Izin' ? 'selected' : ''}>Izin</option>
                                <option value="Cuti Tahunan" ${plan.planType === 'Cuti Tahunan' ? 'selected' : ''}>Cuti Tahunan</option>
                                <option value="Cuti Khusus" ${plan.planType === 'Cuti Khusus' ? 'selected' : ''}>Cuti Khusus</option>
                                <option value="Hadir" ${plan.planType === 'Hadir' ? 'selected' : ''}>Hadir</option>
                                <option value="Overtime (Di Wajibkan Mengisi Jam Awal & Jam Akhir OT)" ${plan.planType === 'Overtime (Di Wajibkan Mengisi Jam Awal & Jam Akhir OT)' ? 'selected' : ''}>Overtime (Di Wajibkan Mengisi Jam Awal & Jam Akhir OT)</option>
                            </select>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center; white-space: nowrap;">
                                <button class="btn-open-absen" data-id="${plan.id}" style="background: linear-gradient(90deg, rgba(16,185,129,0.15), rgba(16,185,129,0.05)); color: #34d399; border: 1px solid rgba(16,185,129,0.3); padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600; transition: all 0.2s;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Form</button>
                                <button class="btn-del-absen" data-id="${plan.id}" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); padding: 0.4rem 0.6rem; border-radius: 6px; cursor: pointer; transition: all 0.2s;"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Attach events
                document.querySelectorAll('.select-edit-plan-type').forEach(sel => {
                    sel.addEventListener('change', async (e) => {
                        const id = e.target.dataset.id;
                        const newType = e.target.value;
                        const origBg = e.target.style.background;
                        
                        e.target.style.background = 'rgba(255,255,255,0.1)';
                        e.target.disabled = true;
                        
                        try {
                            const res = await fetch(`${API_URL}?action=edit_absen_plan`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(attachSettings({ id: id, planType: newType }))
                            });
                            const dt = await res.json();
                            if(!dt.success) {
                                alert('Gagal mengedit tipe absen.');
                            } else {
                                // Update local state so Buka Form uses the new type
                                const planObj = data.data.find(p => p.id == id);
                                if (planObj) planObj.planType = newType;
                            }
                        } catch(err) {
                            console.error(err);
                        }
                        
                        e.target.style.background = origBg;
                        e.target.disabled = false;
                    });
                });
                
                document.querySelectorAll('.btn-open-absen').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        const id = e.target.closest('button').dataset.id;
                        const plan = data.data.find(p => p.id == id);
                        if(plan) {
                            // Get settings first for Name and Divisi
                            let formUrl = '';
                            let absenName = '';
                            let absenDivisi = '';
                            try {
                                const sRes = await fetch(`${API_URL}?action=get_settings`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(attachSettings())
                                });
                                const sData = await sRes.json();
                                if (sData.settings && sData.settings.formAbsenUrl) {
                                    formUrl = sData.settings.formAbsenUrl.trim();
                                    absenName = sData.settings.absenName || '';
                                    absenDivisi = sData.settings.absenDivisi || '';
                                } else {
                                    alert('URL Google Form belum diatur di Settings!');
                                    return;
                                }
                            } catch(err) {
                                alert('Gagal memuat pengaturan.'); return;
                            }
                            
                            if (formUrl && !/^https?:\/\//i.test(formUrl)) formUrl = 'https://' + formUrl;
                            
                            try {
                                const urlObj = new URL(formUrl);
                                if (urlObj.hostname === 'docs.google.com' && urlObj.pathname.includes('/forms/')) {
                                    if (absenName) urlObj.searchParams.set('entry.2058242752', absenName);
                                    if (absenDivisi) urlObj.searchParams.set('entry.1155716239', absenDivisi);
                                    
                                    // Parse Jenis Pengajuan
                                    let jPengajuan = plan.planType;
                                    if(jPengajuan.includes('Overtime')) jPengajuan = 'Overtime (Di Wajibkan Mengisi Jam Awal & Jam Akhir OT)';
                                    urlObj.searchParams.set('entry.234073371', jPengajuan); 
                                    
                                    urlObj.searchParams.set('entry.2130747736', plan.startDate);
                                    urlObj.searchParams.set('entry.766288703', plan.endDate);
                                    
                                    let finalUrl = urlObj.toString();
                                    const iframe = document.getElementById('absenIframe');
                                    const emptyMsg = document.getElementById('absenEmptyMsg');
                                    const newTabBtn = document.getElementById('absenNewTabBtn');
                                    
                                    if (iframe) {
                                        iframe.src = finalUrl;
                                        if(emptyMsg) emptyMsg.style.display = 'none';
                                        iframe.style.display = 'block';
                                    }
                                    if (newTabBtn) {
                                        newTabBtn.href = finalUrl;
                                        newTabBtn.style.display = 'inline-block';
                                    }
                                } else {
                                    window.open(formUrl, '_blank');
                                }
                            } catch(err) {
                                window.open(formUrl, '_blank');
                            }
                        }
                    });
                });

                document.querySelectorAll('.btn-del-absen').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        if (!confirm('Hapus rencana ini?')) return;
                        const id = e.target.closest('button').dataset.id;
                        try {
                            const res = await fetch(`${API_URL}?action=delete_absen_plan`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(attachSettings({ id: id }))
                            });
                            const delData = await res.json();
                            if (delData.success) {
                                loadAbsenPlans();
                            } else {
                                alert('Gagal menghapus: ' + delData.error);
                            }
                        } catch(err) {}
                    });
                });

            } else {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color: var(--text-muted); padding: 2rem;">Belum ada rencana absensi tersimpan.</td></tr>';
            }
        } catch (err) {
            console.error(err);
        }
    };

    // Shift Toggle Logic
    const absenGenModeBulk = document.getElementById('absenGenModeBulk');
    const absenGenModeShift = document.getElementById('absenGenModeShift');
    const absenShiftPanel = document.getElementById('absenShiftPanel');
    const absenDateRangePanel = document.getElementById('absenDateRangePanel');
    
    if (absenGenModeBulk && absenGenModeShift) {
        absenGenModeBulk.addEventListener('change', () => {
            if (absenGenModeBulk.checked) {
                absenShiftPanel.style.display = 'none';
                if(absenDateRangePanel) absenDateRangePanel.style.display = 'block';
            }
        });
        absenGenModeShift.addEventListener('change', () => {
            if (absenGenModeShift.checked) {
                absenShiftPanel.style.display = 'block';
                if(absenDateRangePanel) absenDateRangePanel.style.display = 'none';
            }
        });
    }

    // Shift Data Sync Logic
    const btnAbsenSyncShiftTabs = document.getElementById('btnAbsenSyncShiftTabs');
    const absenShiftTabSelect = document.getElementById('absenShiftTabSelect');
    const absenShiftNameSelect = document.getElementById('absenShiftNameSelect');

    if (btnAbsenSyncShiftTabs) {
        btnAbsenSyncShiftTabs.addEventListener('click', async () => {
            const originalText = btnAbsenSyncShiftTabs.innerHTML;
            btnAbsenSyncShiftTabs.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Syncing...';
            btnAbsenSyncShiftTabs.disabled = true;

            try {
                const res = await fetch(`${API_URL}?action=get_shift_tabs`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings())
                });
                const data = await res.json();

                if (data.success && data.tabs && data.tabs.length > 0) {
                    absenShiftTabSelect.innerHTML = '<option value="">-- Pilih Sheet Tab --</option>';
                    data.tabs.forEach(tab => {
                        const opt = document.createElement('option');
                        opt.value = tab;
                        opt.textContent = tab;
                        absenShiftTabSelect.appendChild(opt);
                    });
                } else {
                    alert('Gagal mengambil daftar sheet tab atau sheet kosong.');
                }
            } catch (e) {
                alert('Terjadi kesalahan jaringan.');
            }
            btnAbsenSyncShiftTabs.innerHTML = originalText;
            btnAbsenSyncShiftTabs.disabled = false;
        });
    }

    if (absenShiftTabSelect) {
        absenShiftTabSelect.addEventListener('change', async () => {
            const tabName = absenShiftTabSelect.value;
            if (!tabName) return;

            absenShiftNameSelect.innerHTML = '<option value="">Loading...</option>';
            absenShiftNameSelect.disabled = true;

            try {
                const res = await fetch(`${API_URL}?action=get_shift_names`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ sheetName: tabName }))
                });
                const data = await res.json();

                if (data.success && data.names && data.names.length > 0) {
                    absenShiftNameSelect.innerHTML = '<option value="">-- Pilih Nama --</option>';
                    data.names.forEach(name => {
                        const opt = document.createElement('option');
                        opt.value = name;
                        opt.textContent = name;
                        absenShiftNameSelect.appendChild(opt);
                    });
                } else {
                    absenShiftNameSelect.innerHTML = '<option value="">Gagal memuat nama</option>';
                }
            } catch (e) {
                absenShiftNameSelect.innerHTML = '<option value="">Error jaringan</option>';
            }
            absenShiftNameSelect.disabled = false;
        });
    }

    if (absenShiftNameSelect) {
        absenShiftNameSelect.addEventListener('change', async (e) => {
            const name = e.target.value;
            const sheetName = absenShiftTabSelect.value;
            const infoDiv = document.getElementById('absenShiftScheduleInfo');
            const infoText = document.getElementById('absenShiftScheduleText');
            
            if (!name) {
                if(infoDiv) infoDiv.style.display = 'none';
                return;
            }
            
            if(infoDiv) {
                infoDiv.style.display = 'block';
                infoText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat jadwal absen...';
            }
            
            try {
                const res = await fetch(`${API_URL}?action=get_shift_schedule`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ name, sheetName }))
                });
                const data = await res.json();
                if (data.success && data.dates) {
                    if (data.dates.length === 0) {
                        infoText.innerHTML = 'Tidak ada jadwal shift (angka 1 atau 2) yang ditemukan untuk nama ini.';
                    } else {
                        // Apply month filter logic if present
                        const monthFilterSelect = document.getElementById('absenShiftMonthFilter');
                        let filteredDates = data.dates;
                        if (monthFilterSelect) {
                            monthFilterSelect.onchange = () => {
                                const selectedMonth = monthFilterSelect.value;
                                if (selectedMonth === 'all') {
                                    // Just use all dates
                                }
                                
                                let validDays = [];
                                data.dates.forEach(dStr => {
                                    let dt = new Date(dStr);
                                    if (isNaN(dt.getTime())) {
                                        const parts = dStr.split(/[-/]/);
                                        if (parts.length === 3) dt = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
                                    }
                                    if (!isNaN(dt.getTime())) {
                                        dt.setHours(0,0,0,0);
                                        if (selectedMonth === 'all' || dt.getMonth() === parseInt(selectedMonth)) {
                                            validDays.push(dt);
                                        }
                                    }
                                });
                                
                                validDays.sort((a,b) => a.getTime() - b.getTime());
                                
                                if (validDays.length === 0) {
                                    infoText.innerHTML = 'Tidak ada jadwal di bulan ini.';
                                    return;
                                }
                                
                                let blocks = [];
                                let blockStart = validDays[0];
                                let blockEnd = validDays[0];
                                
                                for (let i = 1; i < validDays.length; i++) {
                                    const prevDay = validDays[i-1];
                                    const currDay = validDays[i];
                                    const daysDiff = Math.round((currDay.getTime() - prevDay.getTime()) / (1000 * 3600 * 24));
                                    
                                    if (daysDiff === 1 && currDay.getDay() !== 1) {
                                        blockEnd = currDay;
                                    } else {
                                        blocks.push({ start: blockStart, end: blockEnd });
                                        blockStart = currDay;
                                        blockEnd = currDay;
                                    }
                                }
                                blocks.push({ start: blockStart, end: blockEnd });
                                
                                const formatDDisplay = (d) => {
                                    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                                    return `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${String(d.getFullYear()).slice(-2)}`;
                                };
                                const formatD = (d) => {
                                    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
                                };
                                
                                let html = '<div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.8rem;">';
                                blocks.forEach((blk) => {
                                    const sStrDisplay = formatDDisplay(blk.start);
                                    const eStrDisplay = formatDDisplay(blk.end);
                                    const sStr = formatD(blk.start);
                                    const eStr = formatD(blk.end);
                                    let title = sStrDisplay;
                                    if (sStr !== eStr) title += ' - ' + eStrDisplay;
                                    
                                    html += `
                                    <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.03); padding: 0.6rem 0.8rem; border-radius: 4px; border: 1px solid rgba(255,255,255,0.05);">
                                        <span style="font-size: 0.85rem;">${title}</span>
                                        <button type="button" class="btn-use-shift-block" data-start="${sStr}" data-end="${eStr}" style="background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.3); padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; cursor: pointer; transition: all 0.2s;">
                                            <i class="fa-solid fa-plus"></i> Gunakan
                                        </button>
                                    </div>
                                    `;
                                });
                                html += '</div>';
                                infoText.innerHTML = html;
                                
                                // Attach listeners
                                const infoDivEl = document.getElementById('absenShiftScheduleInfo');
                                const btns = infoDivEl.querySelectorAll('.btn-use-shift-block');
                                btns.forEach(btn => {
                                    btn.addEventListener('click', async (e) => {
                                        const tgt = e.currentTarget;
                                        const bs = tgt.getAttribute('data-start');
                                        const be = tgt.getAttribute('data-end');
                                        const planType = document.getElementById('absenPlanType').value;
                                        
                                        tgt.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                                        tgt.disabled = true;
                                        
                                        const payload = {
                                            profile: document.getElementById('profileSelector') ? document.getElementById('profileSelector').value : 'default',
                                            plans: [{ planType: planType, startDate: bs, endDate: be }]
                                        };
                                        
                                        try {
                                            const r = await fetch(`${API_URL}?action=save_absen_plan_bulk`, {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json' },
                                                body: JSON.stringify(attachSettings(payload))
                                            });
                                            const d = await r.json();
                                            if (d.success) {
                                                tgt.innerHTML = '<i class="fa-solid fa-check"></i>';
                                                tgt.style.background = '#10b981';
                                                tgt.style.color = '#fff';
                                                if(typeof loadAbsenPlans === 'function') loadAbsenPlans();
                                            } else {
                                                alert('Gagal: ' + d.message);
                                                tgt.innerHTML = '<i class="fa-solid fa-plus"></i> Gunakan';
                                                tgt.disabled = false;
                                            }
                                        } catch(err) {
                                            alert('Error jaringan');
                                            tgt.innerHTML = '<i class="fa-solid fa-plus"></i> Gunakan';
                                            tgt.disabled = false;
                                        }
                                    });
                                });
                            };
                            // Trigger initial render
                            monthFilterSelect.dispatchEvent(new Event('change'));
                        } else {
                            // Fallback
                            infoText.innerHTML = data.dates.join(', ');
                        }
                    }
                } else {
                    infoText.innerHTML = 'Gagal memuat jadwal: ' + (data.message || 'Error');
                }
            } catch (err) {
                infoText.innerHTML = 'Error jaringan saat memuat jadwal.';
            }
        });
    }

    if (document.getElementById('btnSaveAbsenPlan')) {
        document.getElementById('btnSaveAbsenPlan').addEventListener('click', async () => {
            const planType = document.getElementById('absenPlanType').value;
            const startDate = document.getElementById('absenPlanStartDate').value;
            const endDate = document.getElementById('absenPlanEndDate').value;
            const genMode = document.querySelector('input[name="absenGenMode"]:checked')?.value || 'bulk';
            
            if (genMode === 'bulk' && (!startDate || !endDate)) {
                alert('Tanggal awal dan akhir harus diisi!');
                return;
            }
            
            const btn = document.getElementById('btnSaveAbsenPlan');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
            btn.disabled = true;

            try {
                let finalPlans = [];
                
                if (genMode === 'shift') {
                    const sheetName = absenShiftTabSelect.value;
                    const name = absenShiftNameSelect.value;
                    if (!sheetName || !name) {
                        alert('Silakan pilih Sheet Tab dan Nama Karyawan terlebih dahulu!');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        return;
                    }
                    
                    let activeDates = [];
                    try {
                        const res = await fetch(`${API_URL}?action=get_shift_schedule`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(attachSettings({ sheetName: sheetName, name: name }))
                        });
                        const data = await res.json();
                        if (data.success && data.dates) {
                            activeDates = data.dates;
                        }
                    } catch(e) {
                        console.error(e);
                    }
                    
                    if (activeDates.length === 0) {
                        alert('Tidak ada jadwal shift yang ditemukan atau gagal memuat jadwal.');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        return;
                    }
                    
                    const monthFilterSelect = document.getElementById('absenShiftMonthFilter');
                    const selectedMonth = monthFilterSelect ? monthFilterSelect.value : 'all';
                    
                    let validDays = [];
                    activeDates.forEach(dStr => {
                        let dt = new Date(dStr);
                        if (isNaN(dt.getTime())) {
                            const parts = dStr.split(/[-/]/);
                            if (parts.length === 3) dt = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
                        }
                        
                        if (!isNaN(dt.getTime())) {
                            dt.setHours(0,0,0,0);
                            if (selectedMonth === 'all' || dt.getMonth() === parseInt(selectedMonth)) {
                                validDays.push(dt);
                            }
                        }
                    });
                    
                    // Sort validDays ascending
                    validDays.sort((a,b) => a.getTime() - b.getTime());
                    
                    const formatD = (d) => {
                        const yyyy = d.getFullYear();
                        const mm = String(d.getMonth() + 1).padStart(2, '0');
                        const dd = String(d.getDate()).padStart(2, '0');
                        return `${yyyy}-${mm}-${dd}`;
                    };
                    
                    if (validDays.length > 0) {
                        let blockStart = validDays[0];
                        let blockEnd = validDays[0];
                        
                        for (let i = 1; i < validDays.length; i++) {
                            const prevDay = validDays[i-1];
                            const currDay = validDays[i];
                            const daysDiff = Math.round((currDay.getTime() - prevDay.getTime()) / (1000 * 3600 * 24));
                            
                            // For shift, we just group contiguous days. 
                            // If they want it split per week, we can also break on Monday.
                            if (daysDiff === 1 && currDay.getDay() !== 1) {
                                blockEnd = currDay;
                            } else {
                                finalPlans.push({
                                    planType: planType,
                                    startDate: formatD(blockStart),
                                    endDate: formatD(blockEnd)
                                });
                                blockStart = currDay;
                                blockEnd = currDay;
                            }
                        }
                        finalPlans.push({
                            planType: planType,
                            startDate: formatD(blockStart),
                            endDate: formatD(blockEnd)
                        });
                    }
                    
                } else if (genMode === 'bulk' && startDate !== endDate) {
                    // Fetch holidays unconditionally for bulk generate
                    let holidays = [];
                    try {
                        const hRes = await fetch(`${API_URL}?action=get_indonesian_holidays`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(attachSettings())
                        });
                        const hData = await hRes.json();
                        if (hData.success && hData.holidays) {
                            holidays = hData.holidays.map(h => h.date);
                        }
                    } catch(e) {
                        console.error('Failed fetching holidays', e);
                    }
                    
                    let curr = new Date(startDate);
                    let end = new Date(endDate);
                    let validDays = [];
                    
                    while(curr <= end) {
                        const dayOfWeek = curr.getDay();
                        const yyyy = curr.getFullYear();
                        const mm = String(curr.getMonth() + 1).padStart(2, '0');
                        const dd = String(curr.getDate()).padStart(2, '0');
                        const dateStr = `${yyyy}-${mm}-${dd}`;
                        
                        let skip = false;
                        if (dayOfWeek === 0 || dayOfWeek === 6 || holidays.includes(dateStr)) {
                            skip = true;
                        }
                        
                        if (!skip) {
                            validDays.push(new Date(curr.getTime()));
                        }
                        curr.setDate(curr.getDate() + 1);
                    }
                    
                    const formatD = (d) => {
                        const yyyy = d.getFullYear();
                        const mm = String(d.getMonth() + 1).padStart(2, '0');
                        const dd = String(d.getDate()).padStart(2, '0');
                        return `${yyyy}-${mm}-${dd}`;
                    };
                    
                    if (validDays.length > 0) {
                        let blockStart = validDays[0];
                        let blockEnd = validDays[0];
                        
                        for (let i = 1; i < validDays.length; i++) {
                            const prevDay = validDays[i-1];
                            const currDay = validDays[i];
                            
                            const daysDiff = Math.round((currDay.getTime() - prevDay.getTime()) / (1000 * 3600 * 24));
                            
                            // Break block if not contiguous OR if currDay is Monday (1)
                            if (daysDiff === 1 && currDay.getDay() !== 1) {
                                blockEnd = currDay; // Extend block
                            } else {
                                // Break block
                                finalPlans.push({
                                    planType: planType,
                                    startDate: formatD(blockStart),
                                    endDate: formatD(blockEnd)
                                });
                                blockStart = currDay;
                                blockEnd = currDay;
                            }
                        }
                        // Push last block
                        finalPlans.push({
                            planType: planType,
                            startDate: formatD(blockStart),
                            endDate: formatD(blockEnd)
                        });
                    }
                } else {
                    finalPlans.push({
                        planType: planType,
                        startDate: startDate,
                        endDate: endDate
                    });
                }
                
                if(finalPlans.length === 0) {
                    alert('Tidak ada hari kerja yang bisa di-generate pada rentang tanggal tersebut.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }

                const res = await fetch(`${API_URL}?action=save_absen_plan_bulk`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ plans: finalPlans }))
                });
                const data = await res.json();
                
                if (data.success) {
                    document.getElementById('absenPlanStartDate').value = '';
                    document.getElementById('absenPlanEndDate').value = '';
                    loadAbsenPlans();
                } else {
                    alert('Gagal menyimpan: ' + data.error);
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan.');
            }
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    const loadSettings = async () => {
        try {
            const res = await fetch(`${API_URL}?action=get_settings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings())
            });
            const data = await res.json();

            if (data.success === false) {
                alert('Gagal memuat pengaturan: ' + (data.message || 'Unknown error'));
            } else if (data.settings) {
                const sheetConfigMode = data.settings.sheetConfigMode || 'admin';
                if (document.getElementById('sheetConfigMode')) {
                    document.getElementById('sheetConfigMode').value = sheetConfigMode;
                    if (userProfile !== 'superman') {
                        document.getElementById('sheetConfigModeContainer').style.display = 'block';
                    }
                    toggleManualSheetSettings(userProfile === 'superman' ? 'manual' : sheetConfigMode);
                }
                document.getElementById('spreadsheetId').value = data.settings.spreadsheetId || '';
                document.getElementById('sheetName').value = data.settings.sheetName || 'Sheet1';
                document.getElementById('googleCredentials').value = data.settings.googleCredentials || '';
                document.getElementById('profilePassword').value = userPassword || ''; // backend no longer sends password
                
                if (document.getElementById('absenName')) document.getElementById('absenName').value = data.settings.absenName || '';
                if (document.getElementById('absenDivisi')) document.getElementById('absenDivisi').value = data.settings.absenDivisi || '';
                
                document.getElementById('clientId').value = data.settings.clientId || '';
                document.getElementById('clientSecret').value = data.settings.clientSecret || '';
                document.getElementById('refreshToken').value = data.settings.refreshToken || '';
                document.getElementById('portalName').value = data.settings.portalName || '';
                if(data.settings.accountsUrl) document.getElementById('accountsUrl').value = data.settings.accountsUrl;
                if(data.settings.apiUrl) document.getElementById('apiUrl').value = data.settings.apiUrl;
            }
        } catch (err) {
            alert('Fatal error loading settings: ' + err.message);
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

    const extractSpreadsheetId = (val) => {
        if (!val) return val;
        const match = val.match(/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/);
        if (match && match[1]) return match[1];
        return val;
    };

    document.getElementById('saveSettingsBtn').addEventListener('click', async (e) => {
        e.preventDefault();
        const payload = {
            settings: {
                sheetConfigMode: document.getElementById('sheetConfigMode') ? document.getElementById('sheetConfigMode').value : 'manual',
                spreadsheetId: extractSpreadsheetId(document.getElementById('spreadsheetId').value),
                sheetName: document.getElementById('sheetName').value,
                googleCredentials: document.getElementById('googleCredentials').value,
                profile_password: document.getElementById('profilePassword').value,
                clientId: document.getElementById('clientId').value,
                clientSecret: document.getElementById('clientSecret').value,
                refreshToken: document.getElementById('refreshToken').value,
                portalName: document.getElementById('portalName').value,
                accountsUrl: document.getElementById('accountsUrl').value,
                apiUrl: document.getElementById('apiUrl').value,
                absenName: document.getElementById('absenName') ? document.getElementById('absenName').value : '',
                absenDivisi: document.getElementById('absenDivisi') ? document.getElementById('absenDivisi').value : ''
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
                // Update active session password if they changed it (only for superman)
                const passFieldVal = document.getElementById('profilePassword').value;
                if (passFieldVal) {
                    userPassword = passFieldVal;
                    sessionStorage.setItem('zohoPassword', userPassword);
                }
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

    const filterLogMonth = document.getElementById('filterLogMonth');
    if (filterLogMonth) {
        filterLogMonth.addEventListener('change', () => {
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
        link.setAttribute('download', `TrackHub_Export_${dateStr}.csv`);
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
    
    // WA Approval Tab Switching
    const waTabs = document.querySelectorAll('#wa-approval-view .tab-btn');
    const waContents = document.querySelectorAll('#wa-approval-view .tab-content');
    
    waTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            waTabs.forEach(t => t.classList.remove('active'));
            waTabs.forEach(t => {
                t.style.fontWeight = '600';
                t.style.borderBottomColor = 'transparent';
                t.style.color = 'var(--text-muted)';
            });
            
            tab.classList.add('active');
            tab.style.borderBottomColor = 'var(--primary)';
            tab.style.color = 'var(--text-main)';
            
            waContents.forEach(c => c.style.display = 'none');
            document.getElementById(tab.getAttribute('data-tab')).style.display = 'block';
            document.getElementById('waResultContainer').style.display = 'none';
        });
    });

    let generatedWAMessage = "";
    let generatedWAPhone = "";
    
    function showWAPreview(message, phone = "") {
        generatedWAMessage = message;
        generatedWAPhone = "";
        
        if (phone) {
            let cleanPhone = phone.replace(/[^0-9]/g, '');
            if (cleanPhone.startsWith('0')) {
                cleanPhone = '62' + cleanPhone.substring(1);
            }
            generatedWAPhone = cleanPhone;
        }

        document.getElementById('waPreviewText').innerText = message;
        document.getElementById('waResultContainer').style.display = 'block';
    }

    // Reguler WA Form
    const btnAddCuti = document.getElementById('btnAddCuti');
    const cutiContainer = document.getElementById('waRegCutiContainer');
    if (btnAddCuti && cutiContainer) {
        btnAddCuti.addEventListener('click', () => {
            const div = document.createElement('div');
            div.style = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;';
            div.innerHTML = `
                <input type="date" class="waRegCutiDate" style="flex: 1; background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem; border-radius: 6px;" required>
                <button type="button" onclick="this.parentElement.remove()" style="background: transparent; color: var(--danger); border: none; cursor: pointer; padding: 0.5rem;"><i class="fa-solid fa-trash"></i></button>
            `;
            cutiContainer.appendChild(div);
        });
    }

    const waRegForm = document.getElementById('waRegulerForm');
    if (waRegForm) {
        waRegForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btnSubmit = waRegForm.querySelector('button[type="submit"]');
            const originalBtnText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
            btnSubmit.disabled = true;

            const startDateStr = document.getElementById('waRegStartDate').value;
            const endDateStr = document.getElementById('waRegEndDate').value;
            const excludeWeekends = document.getElementById('waRegExcludeWeekends').checked;
            const excludeHolidaysCheckbox = document.getElementById('waRegExcludeHolidays');
            const excludeHolidays = excludeHolidaysCheckbox ? excludeHolidaysCheckbox.checked : false;
            
            const cutiInputs = document.querySelectorAll('.waRegCutiDate');
            const cutiDates = Array.from(cutiInputs).map(inp => inp.value).filter(val => val !== '');
            
            const bossName = document.getElementById('waRegName').value;
            const monthName = document.getElementById('waRegMonth').value;
            const bossPhone = document.getElementById('waRegPhone').value;
            
            let start = new Date(startDateStr);
            let end = new Date(endDateStr);
            
            if (start > end) {
                showToast('Start Date harus lebih kecil dari End Date', 'error');
                btnSubmit.innerHTML = originalBtnText;
                btnSubmit.disabled = false;
                return;
            }

            let holidaysSet = new Set();
            if (excludeHolidays) {
                try {
                    const res = await fetch(`${API_URL}?action=get_indonesian_holidays`, { method: 'POST' });
                    const data = await res.json();
                    if (data.success && data.holidays) {
                        data.holidays.forEach(h => holidaysSet.add(h.date));
                    }
                } catch(err) {
                    console.error("Gagal memuat hari libur", err);
                }
            }
            
            let cutiSet = new Set(cutiDates);
            
            let activeDates = [];
            let current = new Date(start);
            while (current <= end) {
                const dayOfWeek = current.getDay();
                const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                
                const m = current.getMonth() + 1;
                const d = current.getDate();
                const dateString = `${current.getFullYear()}-${m.toString().padStart(2, '0')}-${d.toString().padStart(2, '0')}`;
                
                const isHoliday = holidaysSet.has(dateString);
                const isCuti = cutiSet.has(dateString);

                let isExcluded = false;
                if (excludeWeekends && isWeekend) isExcluded = true;
                if (excludeHolidays && isHoliday) isExcluded = true;
                if (isCuti) isExcluded = true;

                if (!isExcluded) {
                    activeDates.push(new Date(current));
                }
                current.setDate(current.getDate() + 1);
            }
            
            let finalLines = [];
            if (activeDates.length > 0) {
                const monthsIndo = ["januari", "februari", "maret", "april", "mei", "juni", "juli", "agustus", "september", "oktober", "november", "desember"];
                
                let rangeStart = activeDates[0];
                let rangeEnd = activeDates[0];
                
                const formatRange = (startDt, endDt) => {
                    let sDay = startDt.getDate();
                    let sMonth = monthsIndo[startDt.getMonth()];
                    let sYear = startDt.getFullYear();
                    
                    let eDay = endDt.getDate();
                    let eMonth = monthsIndo[endDt.getMonth()];
                    let eYear = endDt.getFullYear();
                    
                    if (startDt.getTime() === endDt.getTime()) {
                        return `${sDay} ${sMonth} ${sYear}`;
                    }
                    
                    if (sMonth === eMonth && sYear === eYear) {
                        return `${sDay} - ${eDay} ${sMonth} ${sYear}`;
                    } else if (sYear === eYear) {
                        return `${sDay} ${sMonth} - ${eDay} ${eMonth} ${sYear}`;
                    } else {
                        return `${sDay} ${sMonth} ${sYear} - ${eDay} ${eMonth} ${eYear}`;
                    }
                };

                for (let i = 1; i < activeDates.length; i++) {
                    const curr = activeDates[i];
                    const diffTime = curr.getTime() - rangeEnd.getTime();
                    const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays === 1) {
                        rangeEnd = curr;
                    } else {
                        finalLines.push(formatRange(rangeStart, rangeEnd));
                        rangeStart = curr;
                        rangeEnd = curr;
                    }
                }
                finalLines.push(formatRange(rangeStart, rangeEnd));
            }
            
            const msg = `selamat Pagi ${bossName}, mohon izin untuk minta approval kehadiran di bulan ${monthName}, berikut jadwal saya masuk:\n\n${finalLines.join('\n')}\n\nTerima Kasih`;
            showWAPreview(msg, bossPhone);
            
            btnSubmit.innerHTML = originalBtnText;
            btnSubmit.disabled = false;
        });
    }

    // Shift WA Form (Sync Version)
    const btnLoadShiftTabs = document.getElementById('btnLoadShiftTabs');
    const waShiftTabSelect = document.getElementById('waShiftTabSelect');
    const waShiftNameSelect = document.getElementById('waShiftNameSelect');
    
    if (btnLoadShiftTabs) {
        btnLoadShiftTabs.addEventListener('click', async (e) => {
            e.preventDefault();
            const originalText = btnLoadShiftTabs.innerHTML;
            btnLoadShiftTabs.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading Tabs...';
            btnLoadShiftTabs.disabled = true;
            
            try {
                const res = await fetch(`${API_URL}?action=get_shift_tabs`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings())
                });
                const data = await res.json();
                if (data.success && data.tabs) {
                    waShiftTabSelect.innerHTML = '<option value="">-- Pilih Bulan (Sheet Tab) --</option>';
                    data.tabs.forEach(n => {
                        const opt = document.createElement('option');
                        opt.value = n;
                        opt.innerText = n;
                        waShiftTabSelect.appendChild(opt);
                    });
                    showToast('Berhasil memuat daftar sheet tab', 'success');
                } else {
                    let errMsg = data.message || 'Error';
                    if (data.google_error) {
                        console.error('Google Error (WA Approval):', data.google_error);
                        if (data.google_error.error && data.google_error.error.message) {
                            errMsg += ' | ' + data.google_error.error.message;
                        }
                    }
                    showToast('Gagal memuat tabs: ' + errMsg, 'error');
                }
            } catch (err) {
                showToast('Kesalahan jaringan saat memuat tabs', 'error');
            }
            
            btnLoadShiftTabs.innerHTML = originalText;
            btnLoadShiftTabs.disabled = false;
        });
    }

    if (waShiftTabSelect) {
        waShiftTabSelect.addEventListener('change', async (e) => {
            const sheetName = e.target.value;
            waShiftNameSelect.innerHTML = '<option value="">-- Loading... --</option>';
            if (!sheetName) {
                waShiftNameSelect.innerHTML = '<option value="">-- Silakan pilih Sheet Tab di atas --</option>';
                return;
            }
            try {
                const res = await fetch(`${API_URL}?action=get_shift_names`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ sheetName }))
                });
                const data = await res.json();
                if (data.success && data.names) {
                    if (data.names.length === 0) {
                        console.error("RAW ROW:", data.raw_row);
                        showToast("Tidak ada nama ditemukan pada baris pertama Google Sheet", "error");
                    }
                    waShiftNameSelect.innerHTML = '<option value="">-- Pilih Nama Karyawan --</option>';
                    data.names.forEach(n => {
                        const opt = document.createElement('option');
                        opt.value = n;
                        opt.innerText = n;
                        waShiftNameSelect.appendChild(opt);
                    });
                } else {
                    waShiftNameSelect.innerHTML = '<option value="">-- Gagal memuat nama --</option>';
                    showToast('Gagal memuat nama: ' + (data.message || 'Error'), 'error');
                }
            } catch (err) {
                waShiftNameSelect.innerHTML = '<option value="">-- Error jaringan --</option>';
            }
        });
    }

    if (waShiftNameSelect) {
        waShiftNameSelect.addEventListener('change', async (e) => {
            const name = e.target.value;
            const sheetName = waShiftTabSelect.value;
            const infoDiv = document.getElementById('waShiftScheduleInfo');
            const infoText = document.getElementById('waShiftScheduleText');
            if (!name) {
                infoDiv.style.display = 'none';
                return;
            }
            
            infoDiv.style.display = 'block';
            infoText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memuat jadwal absen...';
            
            try {
                const res = await fetch(`${API_URL}?action=get_shift_schedule`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ name, sheetName }))
                });
                const data = await res.json();
                if (data.success && data.dates) {
                    if (data.dates.length === 0) {
                        infoText.innerHTML = 'Tidak ada jadwal shift (angka 1 atau 2) yang ditemukan untuk nama ini.';
                    } else {
                        // Parse dates
                        const indonesianMonths = {
                            'jan': 0, 'januari': 0, 'january': 0,
                            'feb': 1, 'februari': 1, 'february': 1,
                            'mar': 2, 'maret': 2, 'march': 2,
                            'apr': 3, 'april': 3,
                            'mei': 4, 'may': 4,
                            'jun': 5, 'juni': 5, 'june': 5,
                            'jul': 6, 'juli': 6, 'july': 6,
                            'ags': 7, 'agt': 7, 'agustus': 7, 'aug': 7, 'august': 7,
                            'sep': 8, 'sept': 8, 'september': 8,
                            'okt': 9, 'oct': 9, 'oktober': 9, 'october': 9,
                            'nov': 10, 'november': 10,
                            'des': 11, 'dec': 11, 'desember': 11, 'december': 11
                        };
                        const parsedDates = [];
                        data.dates.forEach(dStr => {
                            const parts = dStr.split('-');
                            if (parts.length === 3) {
                                let day = parseInt(parts[0]);
                                let monStr = parts[1].toLowerCase();
                                let year = parseInt(parts[2]);
                                if (year < 100) year += 2000;
                                
                                let mon = indonesianMonths[monStr];
                                if (mon === undefined) {
                                    // Fallback: loop all keys and check if monStr starts with any key
                                    for (let key in indonesianMonths) {
                                        if (monStr.startsWith(key)) {
                                            mon = indonesianMonths[key];
                                            break;
                                        }
                                    }
                                }
                                if (mon === undefined) {
                                    // Final fallback for missing exact matches
                                    mon = -1;
                                }
                                
                                if (mon !== -1) {
                                    parsedDates.push(new Date(year, mon, day));
                                }
                            }
                        });
                        
                        // Group dates
                        parsedDates.sort((a,b) => a - b);
                        let ranges = [];
                        if (parsedDates.length > 0) {
                            let startDt = parsedDates[0];
                            let endDt = parsedDates[0];
                            for (let i = 1; i < parsedDates.length; i++) {
                                const curr = parsedDates[i];
                                const diffDays = Math.round((curr - endDt) / (1000 * 60 * 60 * 24));
                                if (diffDays === 1) {
                                    endDt = curr;
                                } else {
                                    ranges.push({start: startDt, end: endDt});
                                    startDt = curr;
                                    endDt = curr;
                                }
                            }
                            ranges.push({start: startDt, end: endDt});
                        }
                        
                        const shortMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                        const formatShort = (dt) => {
                            return `${String(dt.getDate()).padStart(2, '0')} ${shortMonthNames[dt.getMonth()]} ${String(dt.getFullYear()).slice(-2)}`;
                        };
                        const formatDateInput = (dt) => {
                            return `${dt.getFullYear()}-${String(dt.getMonth()+1).padStart(2, '0')}-${String(dt.getDate()).padStart(2, '0')}`;
                        };
                        
                        window.renderScheduleRanges = function() {
                            const filterVal = document.getElementById('waShiftMonthFilter').value;
                            let html = '<ul style="list-style:none; padding:0; margin:0.5rem 0 0 0;">';
                            
                            let visibleRanges = 0;
                            ranges.forEach(rng => {
                                // Filter logic: check if the range touches the selected month
                                if (filterVal !== 'all') {
                                    const m = parseInt(filterVal);
                                    if (rng.start.getMonth() !== m && rng.end.getMonth() !== m) return;
                                }
                                
                                visibleRanges++;
                                let label = formatShort(rng.start);
                                if (rng.start.getTime() !== rng.end.getTime()) {
                                    label += ' - ' + formatShort(rng.end);
                                }
                                html += `<li style="margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.05); padding: 0.5rem; border-radius: 4px;">
                                    <span>${label}</span>
                                    <button type="button" class="btn-use-schedule-range" data-start="${formatDateInput(rng.start)}" data-end="${formatDateInput(rng.end)}" style="background: rgba(16,185,129,0.2); color: #10b981; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;"><i class="fa-solid fa-plus"></i> Gunakan</button>
                                </li>`;
                            });
                            
                            if (visibleRanges === 0) {
                                html += '<li style="color: var(--text-muted); font-size: 0.9rem;">Tidak ada jadwal di bulan ini.</li>';
                            }
                            
                            html += '</ul>';
                            infoText.innerHTML = html;
                            
                            // Bind buttons
                            infoText.querySelectorAll('.btn-use-schedule-range').forEach(btn => {
                                btn.addEventListener('click', (ev) => {
                                    const s = ev.currentTarget.getAttribute('data-start');
                                    const e = ev.currentTarget.getAttribute('data-end');
                                    
                                    // Cek baris pertama, kalau kosong pakai baris pertama
                                    const firstRow = waShiftDateRangesContainer.querySelector('.wa-shift-date-range');
                                    const firstStart = firstRow.querySelector('.waShiftStartDate').value;
                                    const firstEnd = firstRow.querySelector('.waShiftEndDate').value;
                                    
                                    if (!firstStart && !firstEnd && waShiftDateRangesContainer.children.length === 1) {
                                        firstRow.querySelector('.waShiftStartDate').value = s;
                                        firstRow.querySelector('.waShiftEndDate').value = e;
                                    } else {
                                        // Bikin baris baru
                                        const rowToCopy = waShiftDateRangesContainer.querySelector('.wa-shift-date-range');
                                        const newRow = rowToCopy.cloneNode(true);
                                        newRow.querySelector('.waShiftStartDate').value = s;
                                        newRow.querySelector('.waShiftEndDate').value = e;
                                        newRow.querySelector('.btn-remove-wa-range').style.display = 'inline-block';
                                        
                                        newRow.querySelector('.btn-remove-wa-range').addEventListener('click', (event) => {
                                            event.currentTarget.closest('.wa-shift-date-range').remove();
                                            updateWaRangeRemoveButtons();
                                        });
                                        waShiftDateRangesContainer.appendChild(newRow);
                                        updateWaRangeRemoveButtons();
                                    }
                                    showToast('Rentang tanggal ditambahkan ke form!', 'success');
                                });
                            });
                        };
                        
                        const filterSelect = document.getElementById('waShiftMonthFilter');
                        if (filterSelect) {
                            filterSelect.style.display = 'inline-block';
                            filterSelect.value = 'all'; // Reset default
                            filterSelect.onchange = () => {
                                window.renderScheduleRanges();
                            };
                        }
                        
                        window.renderScheduleRanges();
                    }
                } else {
                    infoText.innerHTML = 'Gagal memuat jadwal: ' + (data.message || 'Error');
                }
            } catch (err) {
                infoText.innerHTML = 'Error jaringan saat memuat jadwal.';
            }
        });
    }

    // Handle Add Range Button
    const btnAddWaRange = document.getElementById('btnAddWaRange');
    const waShiftDateRangesContainer = document.getElementById('waShiftDateRangesContainer');
    
    if (btnAddWaRange && waShiftDateRangesContainer) {
        btnAddWaRange.addEventListener('click', () => {
            const rowToCopy = waShiftDateRangesContainer.querySelector('.wa-shift-date-range');
            const newRow = rowToCopy.cloneNode(true);
            newRow.querySelector('.waShiftStartDate').value = '';
            newRow.querySelector('.waShiftEndDate').value = '';
            newRow.querySelector('.btn-remove-wa-range').style.display = 'inline-block';
            
            newRow.querySelector('.btn-remove-wa-range').addEventListener('click', (e) => {
                e.currentTarget.closest('.wa-shift-date-range').remove();
                updateWaRangeRemoveButtons();
            });
            
            waShiftDateRangesContainer.appendChild(newRow);
            updateWaRangeRemoveButtons();
        });
    }

    function updateWaRangeRemoveButtons() {
        if (!waShiftDateRangesContainer) return;
        const rows = waShiftDateRangesContainer.querySelectorAll('.wa-shift-date-range');
        rows.forEach((r, idx) => {
            const btn = r.querySelector('.btn-remove-wa-range');
            if (btn) {
                btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
                if (!btn.hasAttribute('data-listener')) {
                    btn.addEventListener('click', (e) => {
                        e.currentTarget.closest('.wa-shift-date-range').remove();
                        updateWaRangeRemoveButtons();
                    });
                    btn.setAttribute('data-listener', 'true');
                }
            }
        });
    }
    updateWaRangeRemoveButtons();

    const waShiftForm = document.getElementById('waShiftForm');
    if (waShiftForm) {
        waShiftForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = waShiftNameSelect.value;
            const sheetName = waShiftTabSelect.value;
            
            const rangeRows = document.querySelectorAll('.wa-shift-date-range');
            const dateRanges = [];
            let valid = true;
            
            rangeRows.forEach(r => {
                const s = r.querySelector('.waShiftStartDate').value;
                const e = r.querySelector('.waShiftEndDate').value;
                if (s && e) {
                    const sParts = s.split('-');
                    const eParts = e.split('-');
                    const startDt = new Date(sParts[0], sParts[1] - 1, sParts[2]);
                    const endDt = new Date(eParts[0], eParts[1] - 1, eParts[2]);
                    if (startDt > endDt) {
                        showToast('Start Date tidak boleh lebih besar dari End Date', 'error');
                        valid = false;
                    }
                    dateRanges.push({ start: startDt, end: endDt });
                }
            });
            
            if (!valid || dateRanges.length === 0) return;
            
            const bossName = document.getElementById('waShiftBossName').value;
            const chatMonth = document.getElementById('waShiftMonth').value;
            const bossPhone = document.getElementById('waShiftPhone').value;
            
            if (!sheetName || !name) {
                showToast('Pilih Sheet Tab dan Nama karyawan terlebih dahulu', 'warning');
                return;
            }

            const btnGenerate = document.getElementById('btnGenerateShift');
            const originalText = btnGenerate.innerHTML;
            btnGenerate.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengambil Jadwal...';
            btnGenerate.disabled = true;

            try {
                const res = await fetch(`${API_URL}?action=get_shift_schedule`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ name, sheetName }))
                });
                const data = await res.json();
                if (data.success && data.dates) {
                    // Filter dates between start and end
                    const rawDates = data.dates;
                    let activeDates = [];
                    const indonesianMonths = {
                        'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'Mei': 4, 'Jun': 5,
                        'Jul': 6, 'Ags': 7, 'Sep': 8, 'Okt': 9, 'Nov': 10, 'Des': 11
                    };
                    
                    rawDates.forEach(dStr => {
                        // Format is typically 20-Mei-26 or similar
                        // Let's parse it safely
                        const parts = dStr.split('-');
                        if (parts.length === 3) {
                            let day = parseInt(parts[0]);
                            let monStr = parts[1];
                            let year = parseInt(parts[2]);
                            if (year < 100) year += 2000;
                            
                            let mon = indonesianMonths[monStr];
                            if (mon === undefined) {
                                // fallback logic for full names
                                const mNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                const enNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                                mon = mNames.findIndex(m => m.toLowerCase().startsWith(monStr.toLowerCase()));
                                if (mon === -1) {
                                    mon = enNames.findIndex(m => m.toLowerCase().startsWith(monStr.toLowerCase()));
                                }
                            }
                            if (mon !== -1) {
                                const dt = new Date(year, mon, day);
                                
                                // Cek apakah tanggal masuk dalam salah satu rentang
                                let inRange = false;
                                for (const rng of dateRanges) {
                                    if (dt >= rng.start && dt <= rng.end) {
                                        inRange = true;
                                        break;
                                    }
                                }
                                
                                if (inRange) {
                                    activeDates.push(dt);
                                }
                            }
                        }
                    });

                    if (activeDates.length === 0) {
                        showToast(`Tidak ada shift untuk ${name} pada rentang tanggal tersebut.`, 'warning');
                        btnGenerate.innerHTML = originalText;
                        btnGenerate.disabled = false;
                        return;
                    }

                    // Sort dates
                    activeDates.sort((a,b) => a - b);
                    
                    // Format function to `20 Jan 26`
                    const shortMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    const formatShort = (dt) => {
                        return `${String(dt.getDate()).padStart(2, '0')} ${shortMonthNames[dt.getMonth()]} ${String(dt.getFullYear()).slice(-2)}`;
                    };

                    let finalLines = [];
                    let startDt = activeDates[0];
                    let endDt = activeDates[0];
                    
                    for (let i = 1; i < activeDates.length; i++) {
                        const curr = activeDates[i];
                        // Check if consecutive
                        const diffDays = Math.round((curr - endDt) / (1000 * 60 * 60 * 24));
                        if (diffDays === 1) {
                            endDt = curr;
                        } else {
                            if (startDt.getTime() === endDt.getTime()) {
                                finalLines.push(`${formatShort(startDt)}`);
                            } else {
                                finalLines.push(`${formatShort(startDt)} - ${formatShort(endDt)}`);
                            }
                            startDt = curr;
                            endDt = curr;
                        }
                    }
                    if (startDt.getTime() === endDt.getTime()) {
                        finalLines.push(`${formatShort(startDt)}`);
                    } else {
                        finalLines.push(`${formatShort(startDt)} - ${formatShort(endDt)}`);
                    }
                    
                    const msg = `selamat Pagi ${bossName}, mohon izin untuk minta approval kehadiran di bulan ${chatMonth}, berikut jadwal saya masuk:\n\n${finalLines.join('\n')}\n\nTerima Kasih`;
                    showWAPreview(msg, bossPhone);
                } else {
                    showToast('Gagal memuat jadwal: ' + (data.message || 'Error'), 'error');
                }
            } catch (err) {
                showToast('Kesalahan jaringan saat memuat jadwal', 'error');
            }
            
            btnGenerate.innerHTML = originalText;
            btnGenerate.disabled = false;
        });
    }

    const waSendBtn = document.getElementById('waSendBtn');
    if (waSendBtn) {
        waSendBtn.addEventListener('click', () => {
            if (!generatedWAMessage) return;
            const encoded = encodeURIComponent(generatedWAMessage);
            let url = `https://wa.me/?text=${encoded}`;
            if (generatedWAPhone) {
                url = `https://wa.me/${generatedWAPhone}?text=${encoded}`;
            }
            window.open(url, '_blank');
        });
    }

    const waCopyBtn = document.getElementById('waCopyBtn');
    if (waCopyBtn) {
        waCopyBtn.addEventListener('click', () => {
            if (!generatedWAMessage) return;
            navigator.clipboard.writeText(generatedWAMessage).then(() => {
                showToast('Teks berhasil disalin!', 'success');
            }).catch(() => {
                showToast('Gagal menyalin teks', 'error');
            });
        });
    }

    const waSaveBtn = document.getElementById('waSaveBtn');
    if (waSaveBtn) {
        waSaveBtn.addEventListener('click', async () => {
            if (!generatedWAMessage) return;
            const originalText = waSaveBtn.innerHTML;
            waSaveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
            waSaveBtn.disabled = true;
            
            try {
                // Determine title based on active tab
                let title = "WA Approval Reguler";
                const isShift = document.getElementById('wa-shift').style.display === 'block';
                if (isShift) {
                    const bossName = document.getElementById('waShiftBossName').value;
                    const chatMonth = document.getElementById('waShiftMonth').value;
                    title = `Shift - ${bossName} (${chatMonth})`;
                } else {
                    const bossName = document.getElementById('waRegName').value;
                    const monthName = document.getElementById('waRegMonth').value;
                    title = `Reguler - ${bossName} (${monthName})`;
                }

                const payload = attachSettings({
                    title: title,
                    phone: generatedWAPhone,
                    message: generatedWAMessage
                });
                
                const res = await fetch(`${API_URL}?action=save_wa_approval`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                
                if (data.success) {
                    showToast('Berhasil disimpan ke daftar!', 'success');
                    loadWaApprovals();
                } else {
                    showToast(data.error || 'Gagal menyimpan', 'error');
                }
            } catch (err) {
                showToast('Kesalahan jaringan saat menyimpan', 'error');
            }
            waSaveBtn.innerHTML = originalText;
            waSaveBtn.disabled = false;
        });
    }

    let waApprovalsData = [];

    const waSortOrder = document.getElementById('waSortOrder');
    if (waSortOrder) {
        waSortOrder.addEventListener('change', () => {
            renderWaApprovals();
        });
    }

    async function loadWaApprovals() {
        const tbody = document.getElementById('waApprovalTableBody');
        if (!tbody) return;
        
        try {
            const res = await fetch(`${API_URL}?action=get_wa_approvals`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings({}))
            });
            const data = await res.json();
            
            if (data.success && data.data) {
                waApprovalsData = data.data;
                renderWaApprovals();
            }
        } catch (err) {
            console.error('Error loading WA approvals', err);
        }
    }

    function renderWaApprovals() {
        const tbody = document.getElementById('waApprovalTableBody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        if (waApprovalsData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--text-muted);">Belum ada daftar WA Approval tersimpan</td></tr>';
            return;
        }

        const order = document.getElementById('waSortOrder') ? document.getElementById('waSortOrder').value : 'desc';
        
        const sortedData = [...waApprovalsData].sort((a, b) => {
            const tA = new Date(a.created_at).getTime();
            const tB = new Date(b.created_at).getTime();
            return order === 'desc' ? tB - tA : tA - tB;
        });
        
        sortedData.forEach(item => {
            const tr = document.createElement('tr');
            
            const dt = new Date(item.created_at);
            const formattedDate = `${dt.getFullYear()}-${String(dt.getMonth()+1).padStart(2, '0')}-${String(dt.getDate()).padStart(2, '0')} ${String(dt.getHours()).padStart(2, '0')}:${String(dt.getMinutes()).padStart(2, '0')}`;
            
            let url = `https://wa.me/?text=${encodeURIComponent(item.message)}`;
            if (item.phone) {
                url = `https://wa.me/${item.phone}?text=${encodeURIComponent(item.message)}`;
            }

            const safeMsg = item.message.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n');
            const safeTitle = item.title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const safePhone = (item.phone || '').replace(/'/g, "\\'");

            tr.innerHTML = `
                <td><span style="font-size: 0.85rem; color: var(--text-muted);">${formattedDate}</span></td>
                <td>
                    <strong style="display: block; color: var(--text-main);">${item.title}</strong>
                    <small style="color: var(--primary);">${item.phone || 'Tanpa Nomor'}</small>
                </td>
                <td><span class="status-ready" style="font-size: 0.75rem; background: rgba(16,185,129,0.1); color: #10b981; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: bold;">Tersimpan</span></td>
                <td>
                    <a href="${url}" target="_blank" style="background:#25D366; color:#fff; padding:0.4rem 0.8rem; border-radius:4px; text-decoration:none; display:inline-block; font-size:0.8rem; font-weight:bold; margin-bottom: 0.2rem;"><i class="fa-brands fa-whatsapp"></i> Kirim WA</a>
                    <button onclick="openEditWaModal('${item.id}', '${safeTitle}', '${safePhone}', '${safeMsg}')" style="background:transparent; border:none; color:var(--primary); cursor:pointer; margin-left:5px;" title="Edit"><i class="fa-solid fa-pen"></i></button>
                    <button onclick="deleteWaApproval('${item.id}')" style="background:transparent; border:none; color:var(--danger); cursor:pointer; margin-left:5px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    window.deleteWaApproval = async function(id) {
        if (!confirm('Hapus item ini dari daftar?')) return;
        
        try {
            const res = await fetch(`${API_URL}?action=delete_wa_approval`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings({ id: id }))
            });
            const data = await res.json();
            if (data.success) {
                showToast('Item berhasil dihapus', 'success');
                loadWaApprovals();
            } else {
                showToast(data.error || 'Gagal menghapus', 'error');
            }
        } catch (err) {
            showToast('Kesalahan jaringan saat menghapus', 'error');
        }
    }

    window.openEditWaModal = function(id, title, phone, message) {
        document.getElementById('waEditId').value = id;
        document.getElementById('waEditTitle').value = title;
        document.getElementById('waEditPhone').value = phone;
        document.getElementById('waEditMessage').value = message;
        document.getElementById('waEditModal').style.display = 'flex';
    }

    const btnCancelWaEdit = document.getElementById('btnCancelWaEdit');
    if (btnCancelWaEdit) {
        btnCancelWaEdit.addEventListener('click', () => {
            document.getElementById('waEditModal').style.display = 'none';
        });
    }

    const btnSaveWaEdit = document.getElementById('btnSaveWaEdit');
    if (btnSaveWaEdit) {
        btnSaveWaEdit.addEventListener('click', async () => {
            const id = document.getElementById('waEditId').value;
            const title = document.getElementById('waEditTitle').value;
            const phone = document.getElementById('waEditPhone').value;
            const message = document.getElementById('waEditMessage').value;

            if (!title || !phone || !message) {
                showToast('Harap lengkapi semua field', 'warning');
                return;
            }

            const originalText = btnSaveWaEdit.innerHTML;
            btnSaveWaEdit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
            btnSaveWaEdit.disabled = true;

            try {
                const res = await fetch(`${API_URL}?action=edit_wa_approval`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ id, title, phone, message }))
                });
                const data = await res.json();
                
                if (data.success) {
                    showToast('Perubahan berhasil disimpan!', 'success');
                    document.getElementById('waEditModal').style.display = 'none';
                    loadWaApprovals();
                } else {
                    showToast(data.error || 'Gagal menyimpan perubahan', 'error');
                }
            } catch (err) {
                showToast('Kesalahan jaringan saat menyimpan', 'error');
            }
            
            btnSaveWaEdit.innerHTML = originalText;
            btnSaveWaEdit.disabled = false;
        });
    }

    
    checkAuth().then(async valid => {
        if (valid) {
            await loadSettings();
            await fetchLogs();
            await loadZohoProjects();
            await loadWaApprovals();
        }
    });

    // Task Manager Logic
    const btnFetchTasks = document.getElementById('btnFetchTasks');
    const taskManagerProject = document.getElementById('taskManagerProject');
    const clearProjectSearch = document.getElementById('clearProjectSearch');
    const taskManagerContainer = document.getElementById('taskManagerContainer');
    const btnCreateRootTask = document.getElementById('btnCreateRootTask');
    let currentTasks = [];
    let currentProjectId = null;
    
    if (taskManagerProject && clearProjectSearch) {
        taskManagerProject.addEventListener('input', () => {
            clearProjectSearch.style.display = taskManagerProject.value.length > 0 ? 'block' : 'none';
        });
        clearProjectSearch.addEventListener('click', () => {
            taskManagerProject.value = '';
            clearProjectSearch.style.display = 'none';
        });
    }
    
    const taskStatusFilter = document.getElementById('taskStatusFilter');
    if (taskStatusFilter) {
        taskStatusFilter.addEventListener('change', () => {
            if (currentTasks.length > 0) {
                renderTaskManager();
            }
        });
    }

    const taskSearchInput = document.getElementById('taskSearchInput');
    const clearTaskSearch = document.getElementById('clearTaskSearch');
    if (taskSearchInput) {
        taskSearchInput.addEventListener('input', () => {
            if (clearTaskSearch) {
                clearTaskSearch.style.display = taskSearchInput.value.length > 0 ? 'block' : 'none';
            }
            if (currentTasks.length > 0) {
                renderTaskManager();
            }
        });
    }

    if (clearTaskSearch) {
        clearTaskSearch.addEventListener('click', () => {
            if (taskSearchInput) {
                taskSearchInput.value = '';
                clearTaskSearch.style.display = 'none';
                if (currentTasks.length > 0) {
                    renderTaskManager();
                }
            }
        });
    }

    if (btnFetchTasks) {
        btnFetchTasks.addEventListener('click', async () => {
            const projectName = taskManagerProject.value;
            if (!projectName) {
                showToast('Pilih project terlebih dahulu', 'warning');
                return;
            }
            
            btnFetchTasks.disabled = true;
            btnFetchTasks.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
            taskManagerContainer.innerHTML = '<div style="text-align: center; color: var(--primary); padding: 2rem;"><i class="fa-solid fa-circle-notch fa-spin fa-2x"></i><p style="margin-top: 1rem;">Mengambil hirarki task dari Zoho...</p></div>';
            
            try {
                const res = await fetch(`${API_URL}?action=get_project_tasks`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(attachSettings({ projectName }))
                });
                const resClone = res.clone();
                let data;
                try {
                    data = await res.json();
                } catch (jsonErr) {
                    const text = await resClone.text();
                    console.error("JSON Parse Error. Server returned:", text);
                    throw new Error("Server tidak mengembalikan JSON yang valid. Silakan cek console (F12) untuk melihat error PHP.");
                }
                
                if (data.success) {
                    currentTasks = data.tasks;
                    currentProjectId = data.projectId;
                    renderTaskManager();
                    showToast('Tasks berhasil dimuat', 'success');
                } else {
                    taskManagerContainer.innerHTML = `<p style="color: var(--danger); text-align: center;">Error: ${data.message}</p>`;
                }
            } catch (err) {
                console.error("Fetch tasks error:", err);
                taskManagerContainer.innerHTML = `<p style="color: var(--danger); text-align: center;">Koneksi gagal: ${err.message}</p>`;
            }
            
            btnFetchTasks.disabled = false;
            btnFetchTasks.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> Load Tasks';
        });
    }

    if (btnCreateRootTask) {
        btnCreateRootTask.addEventListener('click', () => {
            if (!currentProjectId) {
                showToast('Load task project terlebih dahulu', 'warning');
                return;
            }
            const taskName = prompt('Nama Main Task Baru:');
            if (taskName) {
                createZohoTask(taskName, null, '');
            }
        });
    }

    async function createZohoTask(taskName, parentId, parentPath) {
        showToast('Sedang membuat task...', 'info');
        try {
            const res = await fetch(`${API_URL}?action=create_project_task`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(attachSettings({ projectId: currentProjectId, taskName, parentId }))
            });
            const data = await res.json();
            if (data.success) {
                showToast('Task berhasil dibuat!', 'success');
                
                // Auto Redirect ke Form Daily-Track!
                const finalPath = parentPath ? `${parentPath} > ${taskName}` : taskName;
                const parts = finalPath.split(' > ');
                document.querySelector('.singleProjectName').value = taskManagerProject.value;
                document.querySelector('.singleTaskName').value = parts[0]?.trim() || '';
                
                const singleSub = document.querySelector('.singleSubTaskName');
                if (singleSub) {
                    singleSub.value = parts.slice(1).join(' > ').trim();
                }
                
                const dailyTrackBtn = document.querySelector('button[data-target="logs-view"]');
                if (dailyTrackBtn) dailyTrackBtn.click();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Refresh list di background
                btnFetchTasks.click();
            } else {
                alert('Gagal membuat task. Pesan dari Zoho: ' + (data.res || data.message));
                console.error("Zoho Error:", data.res);
            }
        } catch (err) {
            showToast('Koneksi gagal saat membuat task', 'error');
        }
    }

    function renderTaskManager() {
        if (!currentTasks || currentTasks.length === 0) {
            taskManagerContainer.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Tidak ada task di project ini.</p>';
            return;
        }

        const taskMap = {};
        const roots = [];

        currentTasks.forEach(t => {
            taskMap[t.id] = { ...t, children: [] };
        });

        currentTasks.forEach(t => {
            if (t.parent && taskMap[t.parent]) {
                taskMap[t.parent].children.push(taskMap[t.id]);
            } else {
                roots.push(taskMap[t.id]);
            }
        });

        const uniqueStatuses = {};
        currentTasks.forEach(t => {
            if (t.status_id) {
                uniqueStatuses[t.status_id] = t.status;
            }
        });

        const buildStatusSelect = (node) => {
            const safeStatus = sanitizeHTML(node.status);
            if (!node.status_id || Object.keys(uniqueStatuses).length === 0) {
                return `<span style="font-size: 0.7rem; background: var(--panel-bg); padding: 0.1rem 0.4rem; border-radius: 4px; color: ${node.status.toLowerCase().includes('closed') ? 'var(--danger)' : 'var(--success)'};">${safeStatus}</span>`;
            }
            let opts = '';
            for (let sid in uniqueStatuses) {
                opts += `<option value="${sid}" ${sid === node.status_id ? 'selected' : ''}>${sanitizeHTML(uniqueStatuses[sid])}</option>`;
            }
            return `<select class="status-select" data-project-id="${node.project_id || currentProjectId}" data-id="${node.id}" style="font-size: 0.7rem; background: var(--panel-bg); color: ${node.status.toLowerCase().includes('closed') ? 'var(--danger)' : 'var(--success)'}; border: 1px solid var(--panel-border); border-radius: 4px; padding: 0.1rem; cursor: pointer;">${opts}</select>`;
        };

        const filterVal = document.getElementById('taskStatusFilter') ? document.getElementById('taskStatusFilter').value : 'all';
        const searchVal = document.getElementById('taskSearchInput') ? document.getElementById('taskSearchInput').value.toLowerCase().trim() : '';

        function buildHtml(node, depth = 0, currentPath = '') {
            const nodePath = currentPath ? `${currentPath} > ${node.name}` : node.name;
            const statusLower = (node.status || '').toLowerCase();
            const nameLower = (node.name || '').toLowerCase();

            let childrenHtml = '';
            let hasVisibleChild = false;
            if (node.children.length > 0) {
                node.children.forEach(child => {
                    const childResult = buildHtml(child, depth + 1, nodePath);
                    if (childResult.isVisible) {
                        hasVisibleChild = true;
                        childrenHtml += childResult.html;
                    }
                });
            }

            let matchesFilter = true;
            
            // Jika ada teks pencarian, hiraukan dropdown filter agar task complete bisa tetap muncul jika dicari
            if (searchVal) {
                if (!nameLower.includes(searchVal)) {
                    matchesFilter = false;
                }
            } else {
                if (filterVal !== 'all') {
                    if (filterVal === 'open') {
                        matchesFilter = !statusLower.includes('complete') && !statusLower.includes('closed') && !statusLower.includes('backlog') && !statusLower.includes('cancel');
                    } else if (filterVal === 'backlog') {
                        matchesFilter = statusLower.includes('backlog');
                    } else if (filterVal === 'complete') {
                        matchesFilter = statusLower.includes('complete') || statusLower.includes('closed');
                    }
                }
            }

            if (!matchesFilter && !hasVisibleChild) {
                return { isVisible: false, html: '' };
            }

            let html = `
                <div style="margin-left: ${depth * 20}px; border-left: ${depth > 0 ? '1px dashed var(--panel-border)' : 'none'}; padding-left: ${depth > 0 ? '15px' : '0'}; margin-bottom: 0.6rem; position: relative;">
                    ${depth > 0 ? '<div style="position: absolute; left: 0; top: 18px; width: 10px; border-top: 1px dashed var(--panel-border);"></div>' : ''}
                    <div class="task-row">
                        <div class="task-row-content">
                            <i class="task-row-icon fa-solid ${node.children.length > 0 ? 'fa-folder-open' : 'fa-list-check'}" style="color: ${depth === 0 ? 'var(--primary)' : 'var(--text-muted)'};"></i>
                            <span class="task-row-title" style="font-weight: ${depth === 0 ? '600' : '400'};">${sanitizeHTML(node.name)}</span>
                            ${buildStatusSelect(node)}
                        </div>
                        <div class="task-actions">
                            <button class="task-btn task-btn-primary use-task-btn" data-path="${sanitizeHTML(nodePath)}"><i class="fa-solid fa-pen"></i> Log</button>
                            ${depth === 0 ? `<button class="task-btn task-btn-success fetch-subtasks-btn" data-id="${node.id}" data-path="${sanitizeHTML(nodePath)}"><i class="fa-solid fa-plus"></i> Subtasks</button>` : ''}
                            <button class="task-btn task-btn-outline add-subtask-btn" data-id="${node.id}" data-path="${sanitizeHTML(nodePath)}"><i class="fa-solid fa-plus"></i> New Sub</button>
                        </div>
                    </div>
                    <div class="lazy-subtasks-container" id="subtasks-container-${node.id}" style="margin-top: 0.5rem;">
            `;
            if (childrenHtml) {
                html += childrenHtml;
            }
            html += '</div></div>';
            return { isVisible: true, html };
        }

        let finalHtml = '';
        roots.forEach(root => {
            const res = buildHtml(root);
            if (res.isVisible) {
                finalHtml += res.html;
            }
        });

        if (!finalHtml) {
            finalHtml = '<p style="color: var(--text-muted); text-align: center; margin-top: 2rem;">Tidak ada task di project ini yang sesuai dengan filter.<br><br><span style="font-size: 0.85rem;">Jika task sudah Complete/Closed, silakan gunakan kotak pencarian atau ubah filter untuk menampilkannya.</span></p>';
        }

        taskManagerContainer.innerHTML = finalHtml;

        taskManagerContainer.querySelectorAll('.add-subtask-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const parentId = e.currentTarget.getAttribute('data-id');
                const parentPath = e.currentTarget.getAttribute('data-path');
                const nodeProjectId = e.currentTarget.getAttribute('data-project-id') || currentProjectId;
                const nodeProjectName = e.currentTarget.getAttribute('data-project-name') || taskManagerProject.value;
                const taskName = prompt('Nama Subtask Baru:');
                if (taskName) {
                    createZohoTask(taskName, parentId, parentPath, nodeProjectId, nodeProjectName);
                }
            });
        });
        
        taskManagerContainer.querySelectorAll('.use-task-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const path = e.currentTarget.getAttribute('data-path');
                const parts = path.split(' > ');
                document.querySelector('.singleProjectName').value = e.currentTarget.getAttribute('data-project-name') || taskManagerProject.value;
                document.querySelector('.singleTaskName').value = parts[0]?.trim() || '';
                
                const singleSub = document.querySelector('.singleSubTaskName');
                if (singleSub) {
                    singleSub.value = parts.slice(1).join(' > ').trim();
                }
                
                const dailyTrackBtn = document.querySelector('button[data-target="logs-view"]');
                if (dailyTrackBtn) dailyTrackBtn.click();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        // Lazy Loading Subtasks Feature
        taskManagerContainer.querySelectorAll('.fetch-subtasks-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const taskId = e.currentTarget.getAttribute('data-id');
                const taskPath = e.currentTarget.getAttribute('data-path');
                const container = document.getElementById(`subtasks-container-${taskId}`);
                const icon = e.currentTarget.querySelector('i');
                
                if (container.getAttribute('data-loaded') === 'true') {
                    // Toggle visibility if already loaded
                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        icon.className = 'fa-solid fa-minus';
                    } else {
                        container.style.display = 'none';
                        icon.className = 'fa-solid fa-plus';
                    }
                    return;
                }
                
                // Fetch from API
                icon.className = 'fa-solid fa-spinner fa-spin';
                try {
                    const nodeProjectId = e.currentTarget.getAttribute('data-project-id') || currentProjectId;
                    const res = await fetch(`${API_URL}?action=get_subtasks`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(attachSettings({ projectId: nodeProjectId, taskId }))
                    });
                    
                    const data = await res.json();
                    if (!data.success) throw new Error(data.message);
                    
                    if (data.subtasks && data.subtasks.length > 0) {
                        // Append to currentTasks array so other functions can use it if needed
                        currentTasks.push(...data.subtasks);
                        
                        let subHtml = '';
                        data.subtasks.forEach(sub => {
                            // Quick manual build for depth=1 subtasks to avoid full tree re-render
                            sub.children = [];
                            const subResult = buildHtml(sub, 1, taskPath);
                            if (subResult.isVisible) subHtml += subResult.html;
                        });
                        
                        container.innerHTML = subHtml;
                        
                        // Re-attach events for the newly injected buttons inside the container
                        attachTaskEvents(container);
                        
                        container.setAttribute('data-loaded', 'true');
                        icon.className = 'fa-solid fa-minus';
                    } else {
                        container.innerHTML = '<div style="margin-left:20px; font-size:0.8rem; color:var(--text-muted);">Tidak ada subtask.</div>';
                        container.setAttribute('data-loaded', 'true');
                        icon.className = 'fa-solid fa-minus';
                    }
                } catch (err) {
                    showToast('Gagal memuat subtask: ' + err.message, 'error');
                    icon.className = 'fa-solid fa-plus';
                }
            });
        });

        // Attach events to dynamic elements
        const attachTaskEvents = (rootElement) => {
            rootElement.querySelectorAll('.add-subtask-btn').forEach(btn => {
                // remove old listeners if any by cloning (simple way)
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                newBtn.addEventListener('click', (e) => {
                    const parentId = e.currentTarget.getAttribute('data-id');
                    const parentPath = e.currentTarget.getAttribute('data-path');
                    const nodeProjectId = e.currentTarget.getAttribute('data-project-id') || currentProjectId;
                    const nodeProjectName = e.currentTarget.getAttribute('data-project-name') || taskManagerProject.value;
                    const taskName = prompt('Nama Subtask Baru:');
                    if (taskName) {
                        createZohoTask(taskName, parentId, parentPath, nodeProjectId, nodeProjectName);
                    }
                });
            });
            
            rootElement.querySelectorAll('.use-task-btn').forEach(btn => {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                newBtn.addEventListener('click', (e) => {
                    const path = e.currentTarget.getAttribute('data-path');
                    const parts = path.split(' > ');
                    document.querySelector('.singleProjectName').value = e.currentTarget.getAttribute('data-project-name') || taskManagerProject.value;
                    document.querySelector('.singleTaskName').value = parts[0]?.trim() || '';
                    
                    const singleSub = document.querySelector('.singleSubTaskName');
                    if (singleSub) {
                        singleSub.value = parts.slice(1).join(' > ').trim();
                    }
                    
                    const dailyTrackBtn = document.querySelector('button[data-target="logs-view"]');
                    if (dailyTrackBtn) dailyTrackBtn.click();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
            
            rootElement.querySelectorAll('.status-select').forEach(sel => {
                const newSel = sel.cloneNode(true);
                sel.parentNode.replaceChild(newSel, sel);
                newSel.addEventListener('change', async (e) => {
                    const taskId = e.currentTarget.getAttribute('data-id');
                    const statusId = e.currentTarget.value;
                    const nodeProjectId = e.currentTarget.getAttribute('data-project-id') || currentProjectId;
                    showToast('Mengubah status task...', 'info');
                    try {
                        const res = await fetch(`${API_URL}?action=update_project_task_status`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(attachSettings({ projectId: nodeProjectId, taskId, statusId }))
                        });
                        const data = await res.json();
                        if (data.success) {
                            showToast('Status berhasil diubah!', 'success');
                            btnFetchTasks.click();
                        } else {
                            alert('Gagal mengubah status. Pesan: ' + (data.message || 'Unknown error'));
                        }
                    } catch (err) {
                        showToast('Koneksi gagal saat mengubah status', 'error');
                    }
                });
            });
        };

        // Attach events to initial load
        attachTaskEvents(taskManagerContainer);
    };

    const sanitizeHTML = (str) => {
        if (!str) return '';
        return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    };

    // --- Admin Panel Logic ---
    if (userProfile === 'superman') {
        // Add global functions for inline buttons
        window.editUserSheetName = async (targetUser, currentSheetName) => {
            const newName = prompt(`Ubah Nama Sheet untuk user '${targetUser}':`, currentSheetName);
            if (newName !== null) {
                try {
                    const res = await fetch(`${API_URL}?action=update_user_sheet_name`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ profile: userProfile, password: userPassword, targetUser, newSheetName: newName.trim() })
                    });
                    const data = await res.json();
                    alert(data.message);
                    const adminUsersBtn = document.querySelector('.nav-btn[data-target="admin-users-view"]');
                    if (adminUsersBtn) adminUsersBtn.click();
                } catch (err) {
                    alert('Koneksi bermasalah');
                }
            }
        };

        window.renameGoogleSheetTab = async (sheetId, currentTitle) => {
            const newTitle = prompt(`Ubah nama sheet '${currentTitle}' menjadi:`, currentTitle);
            if (newTitle !== null && newTitle.trim() !== currentTitle) {
                const sheetIdInput = document.getElementById('globalDataSpreadsheetId');
                const credsInput = document.getElementById('globalDataGoogleCredentials');
                if (!sheetIdInput.value || !credsInput.value) {
                    alert('ID Spreadsheet dan JSON Service Account harus diisi!');
                    return;
                }
                
                try {
                    const res = await fetch(`${API_URL}?action=rename_sheet_tab`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            profile: userProfile, 
                            password: userPassword, 
                            spreadsheetId: extractSpreadsheetId(sheetIdInput.value),
                            credentials: credsInput.value.trim(),
                            sheetId: sheetId,
                            newTitle: newTitle.trim()
                        })
                    });
                    const data = await res.json();
                    alert(data.message);
                    if (data.success) {
                        document.getElementById('btnTestDataSpreadsheet').click();
                    }
                } catch (err) {
                    alert('Koneksi bermasalah');
                }
            }
        };

        window.deleteGoogleSheetTab = async (sheetId, currentTitle) => {
            if (confirm(`PERINGATAN!\n\nYakin ingin MENGHAPUS sheet '${currentTitle}' dari Google Spreadsheet?\n\nIni akan menghapus seluruh data di dalamnya dan tidak dapat diurungkan!`)) {
                const sheetIdInput = document.getElementById('globalDataSpreadsheetId');
                const credsInput = document.getElementById('globalDataGoogleCredentials');
                if (!sheetIdInput.value || !credsInput.value) {
                    alert('ID Spreadsheet dan JSON Service Account harus diisi!');
                    return;
                }

                try {
                    const res = await fetch(`${API_URL}?action=delete_sheet_tab`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            profile: userProfile, 
                            password: userPassword, 
                            spreadsheetId: extractSpreadsheetId(sheetIdInput.value),
                            credentials: credsInput.value.trim(),
                            sheetId: sheetId
                        })
                    });
                    const data = await res.json();
                    alert(data.message);
                    if (data.success) {
                        document.getElementById('btnTestDataSpreadsheet').click();
                    }
                } catch (err) {
                    alert('Koneksi bermasalah');
                }
            }
        };
        window.resetUserPassword = async (targetUser) => {
            if (confirm(`Yakin ingin mereset password untuk user '${targetUser}'?\n\nPassword akan dihapus sehingga user bisa login tanpa password dan membuat yang baru.`)) {
                try {
                    const res = await fetch(`${API_URL}?action=reset_password`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ profile: userProfile, password: userPassword, targetUser })
                    });
                    const data = await res.json();
                    alert(data.message);
                    const adminUsersBtn = document.querySelector('.nav-btn[data-target="admin-users-view"]');
                    if (adminUsersBtn) adminUsersBtn.click();
                } catch (err) {
                    alert('Koneksi bermasalah');
                }
            }
        };

        window.deleteUserProfile = async (targetUser) => {
            if (targetUser === 'superman') {
                alert('Tidak dapat menghapus Super Admin!');
                return;
            }
            if (confirm(`PERINGATAN KERAS!\n\nYakin ingin MENGHAPUS user '${targetUser}' secara permanen?\n\nSemua pengaturan dan profil mereka akan hilang dan tidak dapat dikembalikan!`)) {
                try {
                    const res = await fetch(`${API_URL}?action=delete_profile`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ profile: userProfile, password: userPassword, targetUser })
                    });
                    const data = await res.json();
                    alert(data.message);
                    const adminUsersBtn = document.querySelector('.nav-btn[data-target="admin-users-view"]');
                    if (adminUsersBtn) adminUsersBtn.click();
                } catch (err) {
                    alert('Koneksi bermasalah');
                }
            }
        };

        const fetchAdminProfiles = async () => {
            try {
                const res = await fetch(`${API_URL}?action=get_all_profiles`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ profile: userProfile, password: userPassword })
                });
                const data = await res.json();
                if (data.success) {
                    const tbody = document.getElementById('adminUserTableBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        if (data.profiles.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="4" style="padding: 1.5rem; text-align: center; color: var(--text-muted);">Belum ada user terdaftar.</td></tr>`;
                        } else {
                            data.profiles.forEach(p => {
                                const parts = p.split(' | ');
                                const username = parts[0];
                                const status = parts[1] ? parts[1].replace('(', '').replace(')', '') : 'Unknown';
                                const lastLoginTimestamp = parts[2] ? parseInt(parts[2], 10) : 0;
                                
                                let lastLoginStr = '<span style="color: var(--text-muted); font-style: italic;">Belum Pernah</span>';
                                if (lastLoginTimestamp > 0) {
                                    const d = new Date(lastLoginTimestamp * 1000);
                                    lastLoginStr = d.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                                }
                                
                                const sheetName = parts[3] ? parts[3] : '<em style="color:var(--text-muted)">Default</em>';

                                let statusColor = 'var(--text-main)';
                                if (status.includes('Aman')) statusColor = 'var(--success)';
                                else if (status.includes('Kosong')) statusColor = 'var(--warning)';

                                const tr = document.createElement('tr');
                                tr.style.borderBottom = '1px solid var(--panel-border)';
                                
                                tr.innerHTML = `
                                    <td style="padding: 1rem; color: var(--text-main); font-weight: 500;">
                                        <i class="fa-solid ${username === 'superman' ? 'fa-user-shield' : 'fa-user'}" style="color: ${username === 'superman' ? 'var(--danger)' : 'var(--primary)'}; margin-right: 0.5rem;"></i>
                                        ${username}
                                    </td>
                                    <td style="padding: 1rem; color: var(--text-main); font-size: 0.9rem;">
                                        ${sheetName}
                                    </td>
                                    <td style="padding: 1rem; color: ${statusColor}; font-size: 0.9rem;">
                                        ${status}
                                    </td>
                                    <td style="padding: 1rem; color: var(--text-main); font-size: 0.9rem;">
                                        ${lastLoginStr}
                                    </td>
                                    <td style="padding: 1rem; text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <button onclick="window.editUserSheetName('${username}', '${parts[3] ? parts[3].replace(/'/g, "\\'") : ''}')" style="background: rgba(16,185,129,0.1); color: var(--success); border: 1px solid var(--success); padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem;" title="Edit Sheet Name"><i class="fa-solid fa-pen-to-square"></i> Edit Sheet</button>
                                        <button onclick="window.resetUserPassword('${username}')" style="background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid #f59e0b; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem;" title="Reset Password"><i class="fa-solid fa-unlock-keyhole"></i> Reset</button>
                                        <button onclick="window.deleteUserProfile('${username}')" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid #ef4444; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem;" title="Hapus User" ${username === 'superman' ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });
                        }
                    }
                }
            } catch (err) {}
        };

        const fetchGlobalSettings = async () => {
            try {
                const res = await fetch(`${API_URL}?action=get_settings`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ profile: userProfile, password: userPassword })
                });
                const data = await res.json();
                if (data.settings) {
                    document.getElementById('globalShiftSpreadsheetId').value = data.settings.shiftSpreadsheetId || '';
                    document.getElementById('globalFormAbsenUrl').value = data.settings.formAbsenUrl || '';
                    document.getElementById('globalDataSpreadsheetId').value = data.settings.dataSpreadsheetId || '';
                    document.getElementById('globalDataGoogleCredentials').value = data.settings.dataGoogleCredentials || '';
                }
            } catch(err) {}
        };

        const adminGlobalBtn = document.querySelector('.nav-btn[data-target="admin-global-view"]');
        if (adminGlobalBtn) {
            adminGlobalBtn.addEventListener('click', fetchGlobalSettings);
        }
        
        const adminUsersBtn = document.querySelector('.nav-btn[data-target="admin-users-view"]');
        if (adminUsersBtn) {
            adminUsersBtn.addEventListener('click', fetchAdminProfiles);
        }
        
        const btnLoadUsers = document.getElementById('btnLoadUsers');
        if (btnLoadUsers) {
            btnLoadUsers.addEventListener('click', fetchAdminProfiles);
        }
        
        const btnTestDataSpreadsheet = document.getElementById('btnTestDataSpreadsheet');
        if (btnTestDataSpreadsheet) {
            btnTestDataSpreadsheet.addEventListener('click', async () => {
                const sheetIdInput = document.getElementById('globalDataSpreadsheetId');
                const credsInput = document.getElementById('globalDataGoogleCredentials');
                const rawId = sheetIdInput.value;
                const sheetId = extractSpreadsheetId(rawId);
                const creds = credsInput.value.trim();
                
                if (!sheetId) {
                    alert('Global Spreadsheet ID kosong!');
                    return;
                }
                if (!creds) {
                    alert('Global Service Account JSON kosong!');
                    return;
                }
                
                if (rawId !== sheetId) {
                    sheetIdInput.value = sheetId;
                }
                
                const btn = btnTestDataSpreadsheet;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengetes...';
                btn.disabled = true;
                
                const resultDiv = document.getElementById('testDataSpreadsheetResult');
                resultDiv.style.display = 'none';
                
                try {
                    const res = await fetch(`${API_URL}?action=test_data_spreadsheet`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ profile: userProfile, password: userPassword, spreadsheetId: sheetId, credentials: creds })
                    });
                    const data = await res.json();
                    
                    resultDiv.style.display = 'block';
                    if (data.success) {
                        let html = `<div style="color: var(--success); font-weight: 600; margin-bottom: 0.5rem;"><i class="fa-solid fa-check-circle"></i> ${data.message}</div>`;
                        if (data.tabs && data.tabs.length > 0) {
                            html += `<div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">`;
                            data.tabs.forEach(t => {
                                html += `<div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 0.5rem 1rem; border-radius: 4px; border: 1px solid var(--panel-border);">
                                    <strong style="color: var(--text-main); font-size: 1rem;">${sanitizeHTML(t.title)}</strong>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="window.renameGoogleSheetTab(${t.sheetId}, '${sanitizeHTML(t.title).replace(/'/g, "\\'")}')" style="background: rgba(16,185,129,0.1); color: var(--success); border: 1px solid var(--success); padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;" title="Rename Sheet"><i class="fa-solid fa-pen"></i> Edit</button>
                                        <button onclick="window.deleteGoogleSheetTab(${t.sheetId}, '${sanitizeHTML(t.title).replace(/'/g, "\\'")}')" style="background: rgba(239,68,68,0.1); color: var(--danger); border: 1px solid var(--danger); padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;" title="Hapus Sheet"><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </div>
                                </div>`;
                            });
                            html += `</div>`;
                        } else {
                            html += `<div style="color: var(--text-muted);">Tidak ada tab yang ditemukan.</div>`;
                        }
                        resultDiv.innerHTML = html;
                        resultDiv.style.background = 'rgba(16, 185, 129, 0.1)';
                        resultDiv.style.border = '1px solid var(--success)';
                    } else {
                        resultDiv.innerHTML = `<div style="color: var(--danger); font-weight: 600;"><i class="fa-solid fa-triangle-exclamation"></i> ERROR</div><div style="color: var(--text-main); margin-top: 0.5rem;">${data.message}</div>`;
                        resultDiv.style.background = 'rgba(239, 68, 68, 0.1)';
                        resultDiv.style.border = '1px solid var(--danger)';
                    }
                } catch (err) {
                    resultDiv.style.display = 'block';
                    resultDiv.innerHTML = `<div style="color: var(--danger); font-weight: 600;"><i class="fa-solid fa-triangle-exclamation"></i> KONEKSI GAGAL</div>`;
                    resultDiv.style.background = 'rgba(239, 68, 68, 0.1)';
                    resultDiv.style.border = '1px solid var(--danger)';
                } finally {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            });
        }
        
        const btnTestGlobalSpreadsheet = document.getElementById('btnTestGlobalSpreadsheet');
        if (btnTestGlobalSpreadsheet) {
            btnTestGlobalSpreadsheet.addEventListener('click', async () => {
                const sheetIdInput = document.getElementById('globalShiftSpreadsheetId');
                const rawId = sheetIdInput.value;
                const sheetId = extractSpreadsheetId(rawId);
                
                if (!sheetId) {
                    alert('ID Spreadsheet kosong!');
                    return;
                }
                
                // If user pasted a full URL, let's auto-clean it
                if (rawId !== sheetId) {
                    sheetIdInput.value = sheetId;
                }
                
                const btn = btnTestGlobalSpreadsheet;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengetes...';
                btn.disabled = true;
                
                try {
                    const res = await fetch(`${API_URL}?action=test_shift_spreadsheet`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ profile: userProfile, password: userPassword, spreadsheetId: sheetId })
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert('ERROR:\n' + data.message);
                    }
                } catch (err) {
                    alert('Gagal menghubungi server untuk mengetes koneksi.');
                } finally {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            });
        }

        const btnTestGlobalForm = document.getElementById('btnTestGlobalForm');
        if (btnTestGlobalForm) {
            btnTestGlobalForm.addEventListener('click', async () => {
                let url = document.getElementById('globalFormAbsenUrl').value.trim();
                if (!url) {
                    alert('URL Form kosong!');
                    return;
                }
                if (!/^https?:\/\//i.test(url)) {
                    url = 'https://' + url;
                    document.getElementById('globalFormAbsenUrl').value = url;
                }
                
                const btn = btnTestGlobalForm;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengetes...';
                btn.disabled = true;
                
                try {
                    // Try to fetch via our backend to avoid CORS
                    const res = await fetch(`${API_URL}?action=test_form_url`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ profile: userProfile, password: userPassword, formUrl: url })
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert('ERROR:\n' + data.message);
                    }
                } catch (err) {
                    alert('Gagal menghubungi server untuk mengetes koneksi.');
                } finally {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            });
        }

        const globalSettingsForm = document.getElementById('globalSettingsForm');
        if (globalSettingsForm) {
            globalSettingsForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    profile: userProfile,
                    password: userPassword,
                    settings: {
                        shiftSpreadsheetId: extractSpreadsheetId(document.getElementById('globalShiftSpreadsheetId').value),
                        formAbsenUrl: document.getElementById('globalFormAbsenUrl').value,
                        dataSpreadsheetId: extractSpreadsheetId(document.getElementById('globalDataSpreadsheetId').value),
                        dataGoogleCredentials: document.getElementById('globalDataGoogleCredentials').value
                    }
                };
                try {
                    const res = await fetch(`${API_URL}?action=save_global_settings`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast('Pengaturan global berhasil disimpan!', 'success');
                    } else {
                        showToast('Gagal: ' + data.message, 'error');
                    }
                } catch (err) {
                    showToast('Network error saat menyimpan pengaturan', 'error');
                }
            });
        }
    }
});
