document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('alarmDate');
    const timeInput = document.getElementById('alarmTime');
    const saveBtn = document.getElementById('saveBtn');
    const testBtn = document.getElementById('testBtn');
    const statusMsg = document.getElementById('statusMsg');
    const HARDCODED_URL = 'https://hubtrack.xo.je/cron_absen.php';

    // Load saved settings
    chrome.storage.local.get(['alarmTime', 'alarmDate'], (data) => {
        if (data.alarmTime) timeInput.value = data.alarmTime;
        if (data.alarmDate) dateInput.value = data.alarmDate;
    });

    function showStatus(msg) {
        statusMsg.textContent = msg;
        statusMsg.className = 'status success';
        setTimeout(() => {
            statusMsg.style.display = 'none';
        }, 3000);
    }

    saveBtn.addEventListener('click', () => {
        const alarmTime = timeInput.value; // format HH:MM
        const alarmDate = dateInput.value; // format 1-31 or empty

        chrome.storage.local.set({ alarmTime, alarmDate }, () => {
            // Schedule the alarm
            const [hours, minutes] = alarmTime.split(':').map(Number);
            
            // Calculate next time
            let now = new Date();
            let nextAlarm = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hours, minutes, 0, 0);
            
            // If the time has already passed today, schedule for tomorrow
            if (now.getTime() >= nextAlarm.getTime()) {
                nextAlarm.setDate(nextAlarm.getDate() + 1);
            }
            
            // Calculate delay in minutes
            const delayInMinutes = (nextAlarm.getTime() - now.getTime()) / 60000;
            
            // Clear existing and create new alarm to repeat daily (every 1440 minutes)
            chrome.alarms.clear('autoAbsenAlarm', () => {
                chrome.alarms.create('autoAbsenAlarm', {
                    delayInMinutes: delayInMinutes,
                    periodInMinutes: 1440 // 24 hours
                });
                showStatus('Pengaturan berhasil disimpan! Alarm aktif.');
            });
        });
    });

    testBtn.addEventListener('click', () => {
        // Simulate the background task exactly
        chrome.tabs.create({ url: HARDCODED_URL, active: false }, (tab) => {
            showStatus('Test Run dijalankan... Tab absen terbuka di belakang layar.');
            setTimeout(() => {
                chrome.tabs.remove(tab.id, () => {
                    alert('Test Run Selesai. Tab telah ditutup.');
                });
            }, 15000);
        });
    });
});
