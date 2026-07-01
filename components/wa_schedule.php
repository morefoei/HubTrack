<section id="wa-schedule-view" class="view-section">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title"><i class="fa-solid fa-clock" style="color: #f39c12;"></i> WA Scheduler</h2>
        </div>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Buat pengingat untuk pesan WhatsApp yang akan dikirim di masa depan secara semi-otomatis.</p>

        <form id="waScheduleForm" style="margin-bottom: 2rem; background: var(--bg-card); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
            <div class="form-row">
                <div class="form-group">
                    <label>Nomor Tujuan</label>
                    <input type="text" id="waScheduleNumber" placeholder="Contoh: 08123456789" style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem; border-radius: 6px; width: 100%;">
                    <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="waScheduleIsGroup">
                        <label for="waScheduleIsGroup" style="font-size: 0.85rem; margin: 0; color: var(--text-muted);">Kirim ke Grup / Pilih manual di WA</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Waktu Kirim</label>
                    <input type="datetime-local" id="waScheduleTime" required style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem; border-radius: 6px; width: 100%;">
                </div>
            </div>
            <div class="form-group">
                <label>Isi Pesan</label>
                <textarea id="waScheduleMessage" rows="3" required placeholder="Tulis pesan Anda di sini..." style="background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border); padding: 0.8rem; border-radius: 6px; width: 100%;"></textarea>
            </div>
            <button type="submit" id="btnWaScheduleSubmit" style="background: #25D366; border-color: #25D366;"><i class="fa-solid fa-calendar-check"></i> Jadwalkan Pesan</button>
        </form>

        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="fa-solid fa-list-check"></i> Daftar Jadwal Anda</h3>
        <div class="table-container">
            <table class="data-table" id="waScheduleTable">
                <thead>
                    <tr>
                        <th>Waktu Kirim</th>
                        <th>Tujuan</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="waScheduleTableBody">
                    <tr><td colspan="5" style="text-align: center;">Memuat jadwal...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
    .status-pending { color: #f39c12; font-weight: bold; }
    .status-terkirim { color: #2ecc71; font-weight: bold; }
    .status-ready { color: #e74c3c; font-weight: bold; animation: waBlink 1s linear infinite; }
    @keyframes waBlink { 50% { opacity: 0; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const waScheduleForm = document.getElementById('waScheduleForm');
    const waScheduleTableBody = document.getElementById('waScheduleTableBody');
    const btnSubmit = document.getElementById('btnWaScheduleSubmit');
    const isGroupCheckbox = document.getElementById('waScheduleIsGroup');
    const numberInput = document.getElementById('waScheduleNumber');
    let notifiedSchedules = new Set();
    
    // Toggle input nomor jika ke grup
    isGroupCheckbox.addEventListener('change', (e) => {
        if(e.target.checked) {
            numberInput.value = '';
            numberInput.disabled = true;
            numberInput.placeholder = "Pilih grup nanti saat mengirim";
        } else {
            numberInput.disabled = false;
            numberInput.placeholder = "Contoh: 08123456789";
        }
    });

    // Auto format input nomor (hapus karakter selain angka dan +)
    numberInput.addEventListener('input', (e) => {
        let val = e.target.value;
        val = val.replace(/[^0-9+]/g, '');
        e.target.value = val;
    });

    // Muat jadwal setiap kali tab/menu diklik dan minta izin notifikasi
    document.querySelectorAll('.nav-btn[data-target="wa-schedule-view"]').forEach(btn => {
        btn.addEventListener('click', () => {
            if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }
            loadWaSchedules();
        });
    });

    waScheduleForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const isGroup = isGroupCheckbox.checked;
        const no_tujuan = isGroup ? 'GROUP' : numberInput.value;
        const jadwal = document.getElementById('waScheduleTime').value;
        const pesan = document.getElementById('waScheduleMessage').value;
        const profile = sessionStorage.getItem('zohoProfile');
        
        if (!profile) {
            alert('Sesi habis. Silakan login kembali.');
            return;
        }

        if (!isGroup && !no_tujuan) {
            alert('Mohon isi nomor tujuan atau centang opsi "Kirim ke Grup".');
            return;
        }

        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        btnSubmit.disabled = true;

        try {
            const password = sessionStorage.getItem('zohoPassword');
            const payload = {
                profile: profile,
                password: password,
                no_tujuan: no_tujuan,
                jadwal: jadwal,
                pesan: pesan
            };

            const res = await fetch('api/api.php?action=save_wa_schedule', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if(data.success) {
                alert('Jadwal berhasil disimpan!');
                waScheduleForm.reset();
                numberInput.disabled = false;
                numberInput.placeholder = "Contoh: 08123456789";
                loadWaSchedules();
            } else {
                alert('Error: ' + (data.error || data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error(error);
            alert('Gagal menyimpan jadwal.');
        } finally {
            btnSubmit.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Jadwalkan Pesan';
            btnSubmit.disabled = false;
        }
    });

    async function loadWaSchedules() {
        const profile = sessionStorage.getItem('zohoProfile');
        if (!profile) return;
        
        try {
            const password = sessionStorage.getItem('zohoPassword');
            const payload = { profile: profile, password: password };

            const res = await fetch('api/api.php?action=get_wa_schedules', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if(data.success) {
                renderWaSchedules(data.data, data.server_time);
            }
        } catch(err) {
            console.error(err);
            waScheduleTableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color: red;">Gagal memuat data.</td></tr>';
        }
    }

    function renderWaSchedules(schedules, serverTimeStr) {
        waScheduleTableBody.innerHTML = '';
        if (schedules.length === 0) {
            waScheduleTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Belum ada jadwal pesan.</td></tr>';
            return;
        }

        schedules.forEach(s => {
            const tr = document.createElement('tr');
            
            const scheduleTime = new Date(s.jadwal).getTime();
            const currentTime = new Date().getTime(); 
            
            const isReady = (scheduleTime <= currentTime && s.status === 'pending');
            const encodedText = encodeURIComponent(s.pesan);
            
            // Logika untuk Grup atau Personal
            let waLink = '';
            let tujuanLabel = s.no_tujuan;
            if (s.no_tujuan === 'GROUP') {
                waLink = `https://wa.me/?text=${encodedText}`;
                tujuanLabel = '<span style="background: rgba(59,130,246,0.1); color: #3b82f6; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><i class="fa-solid fa-users"></i> Pilih Manual (Grup)</span>';
            } else {
                waLink = `https://wa.me/${s.no_tujuan}?text=${encodedText}`;
            }

            let statusHtml = '';
            let actionHtml = '';

            if (s.status === 'terkirim') {
                statusHtml = '<span class="status-terkirim">Terkirim</span>';
                actionHtml = `<button onclick="deleteWaSchedule('${s.id}')" style="background:transparent; border:none; color:var(--danger); cursor:pointer;" title="Hapus"><i class="fa-solid fa-trash"></i></button>`;
            } else {
                if (isReady) {
                    // Tampilkan notifikasi desktop jika belum
                    if (!notifiedSchedules.has(s.id)) {
                        notifiedSchedules.add(s.id);
                        if ("Notification" in window && Notification.permission === "granted") {
                            new Notification("TrackHub WA", {
                                body: "Waktunya kirim pesan WA ke " + (s.no_tujuan === 'GROUP' ? 'Grup' : s.no_tujuan) + "!",
                                icon: "assets/css/img/logo.png"
                            });
                        }
                    }

                    statusHtml = '<span class="status-ready">WAKTUNYA KIRIM!</span>';
                    actionHtml = `
                        <a href="${waLink}" target="_blank" onclick="updateWaStatus('${s.id}')" style="background:#25D366; color:#fff; padding:0.4rem 0.8rem; border-radius:4px; text-decoration:none; display:inline-block; font-size:0.8rem; font-weight:bold;">
                            <i class="fa-brands fa-whatsapp"></i> Kirim WA Saya
                        </a>
                        <button onclick="deleteWaSchedule('${s.id}')" style="background:transparent; border:none; color:var(--danger); cursor:pointer; margin-left:10px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    `;
                } else {
                    statusHtml = '<span class="status-pending">Menunggu</span>';
                    actionHtml = `<button onclick="deleteWaSchedule('${s.id}')" style="background:transparent; border:none; color:var(--danger); cursor:pointer;" title="Hapus"><i class="fa-solid fa-trash"></i></button>`;
                }
            }

            tr.innerHTML = `
                <td>${s.jadwal.replace('T', ' ')}</td>
                <td>${tujuanLabel}</td>
                <td>${s.pesan.length > 30 ? s.pesan.substring(0, 30) + '...' : s.pesan}</td>
                <td>${statusHtml}</td>
                <td>${actionHtml}</td>
            `;
            waScheduleTableBody.appendChild(tr);
        });
    }

    window.updateWaStatus = async function(id) {
        const profile = sessionStorage.getItem('zohoProfile');
        try {
            const password = sessionStorage.getItem('zohoPassword');
            const payload = {
                profile: profile,
                password: password,
                id: id,
                status: 'terkirim'
            };

            await fetch('api/api.php?action=update_wa_schedule_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            setTimeout(loadWaSchedules, 1000);
        } catch(err) {
            console.error(err);
        }
    }

    window.deleteWaSchedule = async function(id) {
        if(!confirm('Hapus jadwal ini?')) return;
        const profile = sessionStorage.getItem('zohoProfile');
        try {
            const password = sessionStorage.getItem('zohoPassword');
            const payload = {
                profile: profile,
                password: password,
                id: id
            };

            await fetch('api/api.php?action=delete_wa_schedule', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            loadWaSchedules();
        } catch(err) {
            console.error(err);
        }
    }

    // Cek jadwal secara berkala meskipun sedang membuka tab lain
    setInterval(() => {
        loadWaSchedules();
    }, 30000);
});
</script>
