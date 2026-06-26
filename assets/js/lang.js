// English -> Indonesian Dictionary
const dict = {
    "Welcome to HubTrack": "Selamat Datang di HubTrack",
    "Log in or create a new profile": "Masuk atau buat profil baru",
    "Username / Profile Name": "Nama Pengguna / Nama Profil",
    "Password": "Kata Sandi",
    "Login / Register": "Masuk / Daftar",
    
    "Attendance": "Absensi",
    "Documentation": "Dokumentasi",
    "Documentation & HubTrack Setup Guide": "Dokumentasi Instalasi & Penggunaan HubTrack",
    "Settings": "Pengaturan",
    "About": "Tentang",
    "Global Config": "Konfigurasi Global",
    "Users Manager": "Manajer Pengguna",
    "Analytics": "Analitik",

    "Add New Activity": "Tambah Aktivitas Baru",
    "Start Date": "Tanggal Mulai",
    "Start Time": "Waktu Mulai",
    "End Date": "Tanggal Selesai",
    "End Time": "Waktu Selesai",
    "Duration": "Durasi",
    "Project Name": "Nama Proyek",
    "Task Name": "Nama Tugas",
    "Notes": "Catatan",
    "Initial Status": "Status Awal",
    "Final (Ready to Sync)": "Final (Siap Sync)",
    "Pending (Save for later)": "Pending (Simpan dulu)",
    "Add Log Entry": "Tambah Log",
    "Cancel Edit": "Batal Edit",
    
    "Current Logs": "Data Log Saat Ini",
    "Set Status": "Ubah Status",
    "Export CSV": "Ekspor CSV",
    "Delete Selected": "Hapus Terpilih",
    "Date": "Tanggal",
    "Time": "Waktu",
    "Project & Task": "Proyek & Tugas",
    "Status": "Status",
    "Actions": "Aksi",

    "Fast-Track (Multiple Days)": "Fast-Track (Input Banyak Hari)",
    "Start Date": "Start Date (Dari Tanggal)",
    "End Date": "End Date (Sampai Tanggal)",
    "Exclude Weekends": "Lewati Sabtu & Minggu (Exclude Weekends)",
    "Duration (Optional)": "Durasi (Opsional)",
    "Notes / Remarks": "Catatan",
    "Generate Fast-Track": "Buat Data Fast-Track",

    "Sync to Zoho Projects": "Sinkronisasi ke Zoho",
    "Start Sync": "Mulai Sinkronisasi",
    
    "Zoho API Settings": "Pengaturan API Zoho",
    "Save Settings": "Simpan Pengaturan",
    "Google Sheet Tab Name": "Nama Tab Google Sheet",
    "Profile Password": "Kata Sandi Profil"
};

// Create a reverse dictionary (Indonesian -> English)
const reverseDict = {};
for (const key in dict) {
    reverseDict[dict[key]] = key;
}

let currentLang = localStorage.getItem('hubtrack_lang') || 'id';

function applyLanguage(lang) {
    document.body.setAttribute('data-lang', lang);
    const btn = document.getElementById('langToggleBtn');
    if (btn) btn.innerText = lang.toUpperCase();
    
    const isToID = (lang === 'id');
    
    // We walk all text nodes in the body
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
    const nodesToReplace = [];
    let node;
    while (node = walker.nextNode()) {
        // Skip script and style tags
        if (node.parentElement && (node.parentElement.tagName === 'SCRIPT' || node.parentElement.tagName === 'STYLE' || node.parentElement.id === 'syncConsole')) {
            continue;
        }
        
        let text = node.nodeValue;
        let originalText = text.trim();
        if (!originalText) continue;
        
        let replaced = false;
        
        if (isToID) {
            // English to ID
            // If the text matches an English key, replace it with Indonesian
            if (dict[originalText]) {
                text = text.replace(originalText, dict[originalText]);
                replaced = true;
            }
        } else {
            // ID to English
            // If the text matches an Indonesian key (reverseDict), replace it with English
            if (reverseDict[originalText]) {
                text = text.replace(originalText, reverseDict[originalText]);
                replaced = true;
            }
        }
        
        if (replaced) {
            nodesToReplace.push({node, text});
        }
    }
    
    // Apply replacements
    for (const item of nodesToReplace) {
        item.node.nodeValue = item.text;
    }
    
    // Special case for placeholders (EN -> ID)
    const placeholders = {
        "e.g. fadly": "misal: fadly",
        "Enter password (creates new if not exist)": "Masukkan password (otomatis buat baru jika belum ada)",
        "Type or select from list...": "Ketik atau pilih dari list...",
        "What did you work on?": "Apa yang Anda kerjakan?",
        "ID from your Google Sheet URL (e.g. 1RsoGFQok2dk3MP...)": "ID dari URL Google Sheet Anda",
        "e.g. Sheet1 or tasklist": "misal: Sheet1 atau tasklist",
        "Set a password for your profile (keep it safe!)": "Buat password untuk profil Anda (simpan baik-baik!)",
        "e.g. mycompanyportal": "misal: mycompanyportal"
    };
    
    const reversePlaceholders = {};
    for (const key in placeholders) {
        reversePlaceholders[placeholders[key]] = key;
    }
    
    document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(el => {
        let p = el.getAttribute('placeholder');
        if (isToID) {
            if (placeholders[p]) el.setAttribute('placeholder', placeholders[p]);
        } else {
            if (reversePlaceholders[p]) el.setAttribute('placeholder', reversePlaceholders[p]);
            // Handle if HTML initially had Indonesian placeholder:
            if (p === "Ketik atau pilih dari list...") el.setAttribute('placeholder', "Type or select from list...");
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Add event listener to toggle button
    const toggleBtn = document.getElementById('langToggleBtn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            currentLang = (currentLang === 'id') ? 'en' : 'id';
            localStorage.setItem('hubtrack_lang', currentLang);
            applyLanguage(currentLang);
        });
    }
    
    // Initial apply after a short delay to let other DOM render
    setTimeout(() => {
        applyLanguage(currentLang);
    }, 100);
});
