<?php
session_start();

$DATA_DIR = __DIR__ . '/api/data';
if (!is_dir($DATA_DIR)) {
    mkdir($DATA_DIR, 0755, true);
}

$demoProfile = 'demo_user';
$demoFile = $DATA_DIR . '/settings_' . $demoProfile . '.php';

// Generate a random password for the demo session
$demoPassword = 'demo_' . bin2hex(random_bytes(8));
$hash = password_hash($demoPassword, PASSWORD_DEFAULT);

$demoData = [
    'profile_password' => $hash,
    'sheetName' => 'Demo User',
    'last_login' => time(),
    'clientId' => '1000.DEMO_CLIENT_ID',
    'clientSecret' => 'DEMO_CLIENT_SECRET',
    'refreshToken' => '1000.DEMO_REFRESH_TOKEN',
    'orgId' => '12345678',
    'portalId' => '87654321',
    'botToken' => 'DEMO_BOT_TOKEN',
    'botChatId' => 'DEMO_CHAT_ID'
];

file_put_contents($demoFile, "<?php exit('No direct script access allowed'); ?>\n" . json_encode($demoData, JSON_PRETTY_PRINT));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging into Demo Account...</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f1f5f9; color: #333; }
        .loader { text-align: center; }
        .spinner { border: 4px solid rgba(0,0,0,0.1); width: 36px; height: 36px; border-radius: 50%; border-left-color: #0ea5e9; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>Menyiapkan akun demo...</p>
    </div>
    <script>
        // Set session storage and redirect to dashboard
        sessionStorage.setItem('zohoProfile', '<?= $demoProfile ?>');
        sessionStorage.setItem('zohoPassword', '<?= $demoPassword ?>');
        sessionStorage.setItem('zohoPicture', 'assets/css/img/logo.png');
        
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 800);
    </script>
</body>
</html>
