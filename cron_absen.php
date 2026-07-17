<?php
/**
 * Cron Job Script for Auto Submitting Absensi
 * 
 * You can execute this script via CLI:
 * php /path/to/sync-work/cron_absen.php
 * 
 * Or via web browser/URL (Cron service):
 * http://your-domain.com/cron_absen.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Prevent script timeout

// Set headers for text output if accessed via browser
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

$DATA_DIR = __DIR__ . '/api/data';
if (!is_dir($DATA_DIR)) {
    die("Error: Data directory not found.\n");
}

echo "Starting Cron Absen at " . date('Y-m-d H:i:s') . "\n";

// Helper function to read profile settings
function get_settings($profile) {
    global $DATA_DIR;
    $file = $DATA_DIR . '/settings_' . $profile . '.php';
    if (!file_exists($file)) return [];
    
    $content = file_get_contents($file);
    $startPos = strpos($content, '{');
    if ($startPos !== false) {
        $jsonStr = substr($content, $startPos);
        return json_decode($jsonStr, true) ?: [];
    }
    return [];
}

// Fetch global settings
$globalData = [];
$globalFile = $DATA_DIR . '/global_settings.json';
if (file_exists($globalFile)) {
    $globalData = json_decode(file_get_contents($globalFile), true) ?: [];
}

// Scan all user plans
$planFiles = glob($DATA_DIR . '/absen_plans_*.json');
if (!$planFiles) {
    echo "No plan files found.\n";
    exit;
}

$today = date('Y-m-d');
$totalProcessed = 0;

foreach ($planFiles as $planFile) {
    $basename = basename($planFile, '.json');
    $profile = str_replace('absen_plans_', '', $basename);
    
    echo "\nProcessing profile: $profile\n";
    
    $plans = json_decode(file_get_contents($planFile), true);
    if (!is_array($plans) || empty($plans)) {
        echo "  - No plans available.\n";
        continue;
    }
    
    $settings = get_settings($profile);
    
    // Merge global formAbsenUrl if user didn't set one
    if (empty($settings['formAbsenUrl']) && !empty($globalData['formAbsenUrl'])) {
        $settings['formAbsenUrl'] = $globalData['formAbsenUrl'];
    }
    
    $formUrl = trim($settings['formAbsenUrl'] ?? '');
    $absenName = trim($settings['absenName'] ?? '');
    $absenDivisi = trim($settings['absenDivisi'] ?? '');
    
    if (empty($formUrl)) {
        echo "  - Skipped: Google Form URL is not set.\n";
        continue;
    }
    
    // Ensure URL has http(s) scheme
    if (!preg_match('/^https?:\/\//i', $formUrl)) {
        $formUrl = 'https://' . $formUrl;
    }
    
    // Convert /viewform to /formResponse for submission
    $urlObj = parse_url($formUrl);
    if (isset($urlObj['host']) && $urlObj['host'] === 'docs.google.com' && isset($urlObj['path']) && strpos($urlObj['path'], '/forms/') !== false) {
        $formUrl = str_replace('/viewform', '/formResponse', $formUrl);
    }
    
    $isUpdated = false;
    
    foreach ($plans as &$plan) {
        if (($plan['status'] ?? '') === 'done') {
            continue;
        }
        
        $startDate = $plan['startDate'] ?? '';
        $endDate = $plan['endDate'] ?? '';
        $planType = $plan['planType'] ?? '';
        
        // Auto-submit triggers if today matches or is past the startDate
        if (!empty($startDate) && $today >= $startDate) {
            echo "  - Submitting plan ID: {$plan['id']} (Date: $startDate to $endDate)... ";
            
            $jPengajuan = $planType;
            if (strpos($jPengajuan, 'Overtime') !== false) {
                $jPengajuan = 'Overtime (Di Wajibkan Mengisi Jam Awal & Jam Akhir OT)';
            }
            
            $postData = [
                'entry.2058242752' => $absenName,
                'entry.1155716239' => $absenDivisi,
                'entry.234073371' => $jPengajuan,
                'entry.2130747736' => $startDate,
                'entry.766288703' => $endDate
            ];
            
            $ch = curl_init($formUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // In case of SSL issues on some local setups, disable verify peer
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Google Forms usually returns 200, but can return redirects (302/303) depending on structure
            if ($httpCode == 200 || $httpCode == 302 || $httpCode == 303 || $httpCode == 400) {
                // 400 is sometimes returned by Google Forms when a field is missing, we consider it processed but might want to log it
                if ($httpCode == 400) {
                     echo "Warning (HTTP 400 - Check Form Fields).\n";
                } else {
                     echo "Success (HTTP $httpCode).\n";
                }
                $plan['status'] = 'done';
                $isUpdated = true;
                $totalProcessed++;
            } else if ($httpCode == 401) {
                echo "Failed! HTTP Code 401 (Unauthorized). Form requires Google Login/Permissions.\n";
            } else {
                echo "Failed! HTTP Code: $httpCode\n";
            }
        }
    }
    
    if ($isUpdated) {
        // Save the updated plans back to the file
        file_put_contents($planFile, json_encode($plans, JSON_PRETTY_PRINT));
        echo "  - Saved updated plans for $profile.\n";
    }
}

echo "\nCron Absen completed. Total submitted: $totalProcessed\n";
?>
