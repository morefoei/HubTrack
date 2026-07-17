// This event is fired when the extension is first installed or updated
chrome.runtime.onInstalled.addListener(() => {
    console.log("Auto Absen Extension installed.");
});

// Listen for alarms
chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === "autoAbsenAlarm") {
        console.log("Alarm triggered at " + new Date().toLocaleTimeString());
        
        chrome.storage.local.get(['alarmDate'], (data) => {
            if (data.alarmDate) {
                const today = new Date().getDate();
                if (today != data.alarmDate) {
                    console.log(`Today is ${today}, but alarm is set for ${data.alarmDate}. Skipping.`);
                    return; // Skip if date doesn't match
                }
            }
            
            const HARDCODED_URL = 'https://hubtrack.xo.je/cron_absen.php';
            
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
