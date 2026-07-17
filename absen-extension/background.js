// This event is fired when the extension is first installed or updated
chrome.runtime.onInstalled.addListener(() => {
    console.log("Auto Absen Extension installed.");
});

// Listen for alarms
chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === "autoAbsenAlarm") {
        console.log("Alarm triggered at " + new Date().toLocaleTimeString());
        
        chrome.storage.local.get(['alarmDate'], async (data) => {
            let HARDCODED_URL = 'https://hubtrack.xo.je/cron_absen.php';
            
            if (data.alarmDate) {
                const now = new Date();
                const today = now.getDate();
                const year = now.getFullYear();
                const month = now.getMonth();
                
                // Fetch holidays
                let holidays = [];
                try {
                    const fd = new FormData();
                    fd.append('year', year);
                    const response = await fetch('https://hubtrack.xo.je/api/api.php?action=get_indonesian_holidays', {
                        method: 'POST',
                        body: fd
                    });
                    const resData = await response.json();
                    if (resData.success && resData.holidays) {
                        holidays = resData.holidays.map(h => h.date);
                    }
                } catch(e) { console.error('Failed to fetch holidays', e); }

                let effectiveDate = parseInt(data.alarmDate);
                while (effectiveDate > 0) {
                    let checkDate = new Date(year, month, effectiveDate);
                    let dayOfWeek = checkDate.getDay();
                    let dateString = `${year}-${String(month+1).padStart(2,'0')}-${String(effectiveDate).padStart(2,'0')}`;
                    
                    let isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                    let isHoliday = holidays.includes(dateString);
                    
                    if (isWeekend || isHoliday) {
                        effectiveDate--; // Move to previous day
                    } else {
                        break; // Found a valid workday!
                    }
                }
                
                if (today != effectiveDate) {
                    console.log(`Today is ${today}, but effective alarm date is ${effectiveDate}. Skipping.`);
                    return; // Skip if date doesn't match
                }
                HARDCODED_URL += '?mode=monthly'; // Tell the server to submit everything for this month
            }
            
            // Open the URL in an inactive tab so it doesn't disturb the user
            chrome.tabs.create({ url: HARDCODED_URL, active: false }, (tab) => {
                // Wait for 15 seconds to allow the page to fully load and bypass any security checks
                setTimeout(() => {
                    chrome.tabs.remove(tab.id, () => {
                        console.log("Closed the background tab successfully.");
                    });
                }, 15000);
            });
        });
    }
});
