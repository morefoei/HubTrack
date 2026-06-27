<?php
header('Content-Type: application/json');

$DATA_DIR = __DIR__ . '/data';
if (!is_dir($DATA_DIR)) {
    mkdir($DATA_DIR, 0755, true);
}
function getSettingsFile() {
    global $input, $DATA_DIR;
    $profile = 'default';
    if (!empty($input['profile'])) {
        $profile = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['profile'], 0, 32))); // Max 32 chars, lowercase
    }
    return $DATA_DIR . '/settings_' . $profile . '.php';
}

function getSettingsFileOld() {
    global $input, $DATA_DIR;
    $profile = 'default';
    if (!empty($input['profile'])) {
        $profile = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['profile'], 0, 32)));
    }
    return $DATA_DIR . '/settings_' . $profile . '.json';
}

function isSheetNameTaken($newSheetName, $currentUserProfile) {
    global $DATA_DIR;
    $targetName = strtolower(trim($newSheetName));
    if (empty($targetName)) {
        $targetName = strtolower(trim($currentUserProfile)); // Fallback if empty
    }

    $files = glob($DATA_DIR . '/settings_*.{php,json}', GLOB_BRACE);
    if (!is_array($files)) return false;

    foreach ($files as $f) {
        $basename = preg_replace('/\.(php|json)$/', '', basename($f));
        $username = str_replace('settings_', '', $basename);
        
        // Skip current user, default profile, and empty
        if ($username === $currentUserProfile || $username === 'default' || $username === '') continue;
        
        $content = file_get_contents($f);
        $startPos = strpos($content, '{');
        $jsonStr = $startPos !== false ? substr($content, $startPos) : '{}';
        $data = json_decode($jsonStr, true) ?: [];
        
        $otherSheetName = trim($data['sheetName'] ?? '');
        if (empty($otherSheetName)) {
            $otherSheetName = $username;
        }
        
        if (strtolower($otherSheetName) === $targetName) {
            return true;
        }
    }
    return false;
}

function getSettings() {
    $file = getSettingsFile();
    $oldFile = getSettingsFileOld();

    // Auto-migrate old .json files to .php
    if (!file_exists($file) && file_exists($oldFile)) {
        $content = file_get_contents($oldFile);
        file_put_contents($file, "<?php exit('No direct script access allowed'); ?>\n" . $content);
        unlink($oldFile);
    }

    $userSettings = [];
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $startPos = strpos($content, '{');
        if ($startPos !== false) {
            $jsonStr = substr($content, $startPos);
            $userSettings = json_decode($jsonStr, true) ?: [];
        }
    }
    
    if (empty($userSettings)) {
        // Fallback if settings not passed
        $userSettings = [
            'spreadsheetId' => '',
            'googleCredentials' => '',
            'clientId' => '',
            'clientSecret' => '',
            'refreshToken' => '',
            'portalName' => '',
            'sheetName' => 'Sheet1',
            'shiftSpreadsheetId' => '',
            'formAbsenUrl' => '',
            'shiftSheetName' => 'Sheet1',
            'accountsUrl' => 'https://accounts.zoho.com',
            'apiUrl' => 'https://projectsapi.zoho.com'
        ];
    }
    
    // Merge with global settings
    global $DATA_DIR;
    $globalFile = $DATA_DIR . '/global_settings.json';
    $globalData = [];
    if (file_exists($globalFile)) {
        $gContent = file_get_contents($globalFile);
        $globalData = json_decode($gContent, true) ?: [];
        if (!empty($globalData['shiftSpreadsheetId'])) {
            $userSettings['shiftSpreadsheetId'] = $globalData['shiftSpreadsheetId'];
        }
        if (!empty($globalData['formAbsenUrl'])) {
            $userSettings['formAbsenUrl'] = $globalData['formAbsenUrl'];
        }
    }

    $userSettings['sheetConfigMode'] = $userSettings['sheetConfigMode'] ?? 'admin';
    global $input;
    $currentProfile = 'default';
    if (!empty($input['profile'])) {
        $currentProfile = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['profile'], 0, 32)));
    }

    if ($userSettings['sheetConfigMode'] === 'admin' && $currentProfile !== 'superman') {
        // Inherit from superman's personal profile
        $adminFile = $DATA_DIR . '/settings_superman.php';
        if (file_exists($adminFile)) {
            $adminContent = file_get_contents($adminFile);
            $adminStart = strpos($adminContent, '{');
            if ($adminStart !== false) {
                $adminData = json_decode(substr($adminContent, $adminStart), true) ?: [];
                $userSettings['spreadsheetId'] = $adminData['spreadsheetId'] ?? '';
                $userSettings['googleCredentials'] = $adminData['googleCredentials'] ?? '';
            }
        }
        
        // Override with Global Data if exists
        if (!empty($globalData['dataSpreadsheetId'])) {
            $userSettings['spreadsheetId'] = $globalData['dataSpreadsheetId'];
        }
        if (!empty($globalData['dataGoogleCredentials'])) {
            $userSettings['googleCredentials'] = $globalData['dataGoogleCredentials'];
        }
    }

    return $userSettings;
}

function getGoogleAccessToken($credentialsJson) {
    if (empty($credentialsJson)) return null;
    $creds = json_decode($credentialsJson, true);
    if (!$creds || empty($creds['client_email']) || empty($creds['private_key'])) return null;

    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $claim = json_encode([
        'iss' => $creds['client_email'],
        'scope' => 'https://www.googleapis.com/auth/spreadsheets',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlClaim = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claim));
    $signatureInput = $base64UrlHeader . '.' . $base64UrlClaim;
    
    openssl_sign($signatureInput, $signature, $creds['private_key'], 'SHA256');
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $signatureInput . '.' . $base64UrlSignature;
    
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);
    return $data['access_token'] ?? null;
}

function createGoogleSheetTab($spreadsheetId, $sheetName, $token) {
    if (!$token || !$spreadsheetId || empty($sheetName)) return false;
    
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId:batchUpdate";
    $payload = [
        'requests' => [
            [
                'addSheet' => [
                    'properties' => [
                        'title' => $sheetName
                    ]
                ]
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $resData = json_decode($res, true);
    $sheetId = $resData['replies'][0]['addSheet']['properties']['sheetId'] ?? null;
    
    if ($httpCode >= 200 && $httpCode < 300 && $sheetId !== null) {
        $headers = ['ID', 'start date', 'start time', 'Lembur', 'end date', 'end time', 'Duration', 'Zoho Stat', 'vendor', 'Project', 'task', 'Notes', 'Task Url'];
        $cellValues = [];
        foreach ($headers as $header) {
            $cellValues[] = [
                'userEnteredValue' => ['stringValue' => $header],
                'userEnteredFormat' => [
                    'backgroundColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 0.0],
                    'textFormat' => ['bold' => true],
                    'borders' => [
                        'top' => ['style' => 'SOLID'],
                        'bottom' => ['style' => 'SOLID'],
                        'left' => ['style' => 'SOLID'],
                        'right' => ['style' => 'SOLID']
                    ]
                ]
            ];
        }
        
        $formatPayload = [
            'requests' => [
                [
                    'updateCells' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'startRowIndex' => 1,
                            'endRowIndex' => 2,
                            'startColumnIndex' => 0,
                            'endColumnIndex' => 13
                        ],
                        'rows' => [
                            ['values' => $cellValues]
                        ],
                        'fields' => 'userEnteredValue,userEnteredFormat(backgroundColor,textFormat,borders)'
                    ]
                ],
                [
                    'updateSheetProperties' => [
                        'properties' => [
                            'sheetId' => $sheetId,
                            'gridProperties' => ['frozenRowCount' => 2]
                        ],
                        'fields' => 'gridProperties.frozenRowCount'
                    ]
                ]
            ]
        ];
        
        $chH = curl_init($url);
        curl_setopt($chH, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chH, CURLOPT_POST, true);
        curl_setopt($chH, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);
        curl_setopt($chH, CURLOPT_POSTFIELDS, json_encode($formatPayload));
        curl_exec($chH);
        curl_close($chH);
        return true;
    }
    return false;
}

function getLogsFromSheet() {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['spreadsheetId'];
    
    if (!$token || !$spreadsheetId) return [];
    
    $sheetName = !empty($settings['sheetName']) ? $settings['sheetName'] : 'Sheet1';
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!A3:M";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $res = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($res, true);
    
    if (isset($data['error']) && $data['error']['code'] == 400 && strpos($data['error']['message'], 'Unable to parse range') !== false) {
        if (createGoogleSheetTab($spreadsheetId, $sheetName, $token)) {
            // Retry fetch
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
            $res = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($res, true);
        }
    }
    
    $rows = $data['values'] ?? [];
    
    $logs = [];
    foreach ($rows as $index => $row) {
        // Skip empty rows
        if (empty($row[1]) && empty($row[9]) && empty($row[10])) continue;
        
        $logs[] = [
            'rowIndex' => $index + 3,
            'id' => $row[0] ?? '',
            'startDate' => $row[1] ?? '',
            'startTime' => $row[2] ?? '',
            'lembur' => $row[3] ?? '',
            'endDate' => $row[4] ?? '',
            'endTime' => $row[5] ?? '',
            'duration' => $row[6] ?? '',
            'status' => $row[7] ?? '',
            'vendor' => $row[8] ?? '',
            'project' => $row[9] ?? '',
            'task' => $row[10] ?? '',
            'notes' => $row[11] ?? '',
            'taskUrl' => $row[12] ?? ''
        ];
    }
    return $logs;
}

function getShiftTabs($reqId = '') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = !empty($reqId) ? $reqId : $settings['shiftSpreadsheetId'];
    
    if (!$token || !$spreadsheetId) return ['success' => false, 'message' => 'Shift Spreadsheet ID atau Credentials tidak valid'];
    
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $res = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($res, true);
    if (!isset($data['sheets'])) {
        return ['success' => false, 'message' => 'Tidak dapat membaca daftar sheet dari Google Sheet', 'google_error' => $data];
    }
    
    $tabs = [];
    foreach ($data['sheets'] as $sheet) {
        $tabs[] = $sheet['properties']['title'];
    }
    return ['success' => true, 'tabs' => $tabs];
}

function getShiftNames($sheetNameReq = '') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['shiftSpreadsheetId'];
    
    if (!$token || !$spreadsheetId) return ['success' => false, 'message' => 'Shift Spreadsheet ID atau Credentials tidak valid'];
    
    $sheetName = !empty($sheetNameReq) ? $sheetNameReq : (!empty($settings['shiftSheetName']) ? $settings['shiftSheetName'] : 'Sheet1');
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!A1:Z10";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $res = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($res, true);
    if (!isset($data['values']) || count($data['values']) === 0) {
        return ['success' => false, 'message' => 'Tidak dapat membaca data dari sheet (A1:Z10 kosong)'];
    }
    
    // Scan dynamically to find the header row (the one containing employee names)
    $headerRow = [];
    $headerRowIndex = 0;
    foreach ($data['values'] as $idx => $row) {
        if (count($row) > 3) { // It has names in column D and beyond
            $headerRow = $row;
            $headerRowIndex = $idx + 1; // 1-indexed
            break;
        }
    }
    
    if (empty($headerRow)) {
        return ['success' => false, 'message' => 'Tidak dapat menemukan baris nama karyawan di 10 baris pertama'];
    }
    
    $names = [];
    // Start reading names from column D (index 3)
    for ($i = 3; $i < count($headerRow); $i++) {
        $n = trim($headerRow[$i] ?? '');
        if (!empty($n) && stripos($n, 'Change Shift') === false && strlen($n) < 50) {
            $names[] = $n;
        }
    }
    return ['success' => true, 'names' => $names, 'raw_row' => $headerRow, 'headerRowIndex' => $headerRowIndex, 'sheetNameFetched' => $sheetName];
}

function getShiftSchedule($name, $sheetNameReq = '') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['shiftSpreadsheetId'];
    
    if (!$token || !$spreadsheetId) return ['success' => false, 'message' => 'Shift Spreadsheet ID tidak valid'];
    
    $sheetName = !empty($sheetNameReq) ? $sheetNameReq : (!empty($settings['shiftSheetName']) ? $settings['shiftSheetName'] : 'Sheet1');
    // Read A1:Z10 to dynamically find the header row
    $urlNames = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!A1:Z10";
    $ch = curl_init($urlNames);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $resNames = curl_exec($ch);
    curl_close($ch);
    
    $dataNames = json_decode($resNames, true);
    if (!isset($dataNames['values']) || count($dataNames['values']) === 0) {
        return ['success' => false, 'message' => 'Sheet kosong atau tidak bisa dibaca'];
    }
    
    $headerRow = [];
    $headerRowIndex = 0;
    foreach ($dataNames['values'] as $idx => $row) {
        if (count($row) > 3) {
            $headerRow = $row;
            $headerRowIndex = $idx + 1; // 1-indexed
            break;
        }
    }
    
    if (empty($headerRow)) {
        return ['success' => false, 'message' => 'Tidak dapat menemukan baris nama karyawan'];
    }
    
    $targetColIndex = -1;
    for ($i = 3; $i < count($headerRow); $i++) {
        if (trim($headerRow[$i]) === trim($name)) {
            $targetColIndex = $i;
            break;
        }
    }
    
    if ($targetColIndex === -1) {
        return ['success' => false, 'message' => 'Nama tidak ditemukan di baris header'];
    }
    
    // Convert targetColIndex to letter (e.g. 3 => D, 4 => E)
    $colLetter = chr(65 + $targetColIndex);
    
    // Data starts exactly after the header row
    $dataStartRow = $headerRowIndex + 1;
    
    // Read Dates (Column C) and Target column
    $urlSchedule = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values:batchGet?ranges=" . urlencode($sheetName) . "!C$dataStartRow:C&ranges=" . urlencode($sheetName) . "!$colLetter$dataStartRow:$colLetter";
    $ch2 = curl_init($urlSchedule);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $resSchedule = curl_exec($ch2);
    curl_close($ch2);
    
    $dataSchedule = json_decode($resSchedule, true);
    
    $datesCol = $dataSchedule['valueRanges'][0]['values'] ?? [];
    $shiftCol = $dataSchedule['valueRanges'][1]['values'] ?? [];
    
    $activeDates = [];
    for ($i = 0; $i < count($datesCol); $i++) {
        $dateStr = $datesCol[$i][0] ?? '';
        $shiftVal = $shiftCol[$i][0] ?? '';
        
        // If they have a shift (1 or 2)
        if (!empty($dateStr) && (trim($shiftVal) === '1' || trim($shiftVal) === '2')) {
            $activeDates[] = $dateStr;
        }
    }
    
    return ['success' => true, 'dates' => $activeDates];
}

function updateRowInSheet($rowIndex, $log, $newStatus, $taskUrl) {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['spreadsheetId'];
    if (!$token || !$spreadsheetId) return false;

    $sheetName = !empty($settings['sheetName']) ? $settings['sheetName'] : 'Sheet1';
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!H$rowIndex:M$rowIndex?valueInputOption=USER_ENTERED";
    
    $values = [
        escapeFormula($newStatus),
        escapeFormula($log['vendor']),
        escapeFormula($log['project']),
        escapeFormula($log['task']),
        escapeFormula($log['notes']),
        escapeFormula($taskUrl)
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['values' => [$values]]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return true;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);

// -- GLOBAL AUTHENTICATION CHECK --
if ($action !== 'manual_login' && $action !== 'google_login' && $action !== 'get_all_profiles' && $action !== 'reset_password' && $action !== 'delete_profile' && $action !== 'test_shift_spreadsheet' && $action !== 'test_form_url' && !empty($action)) {
    $reqProfile = isset($input['profile']) ? strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['profile'], 0, 32))) : 'default';
    $reqPassword = $input['password'] ?? '';
    
    if (empty($reqPassword)) {
        echo json_encode(['success' => false, 'message' => 'Akses Ditolak: Password wajib diisi!']);
        exit;
    }
    
    // Explicitly protect the superman profile
    if ($reqProfile === 'superman') {
        if ($reqPassword !== 'musikrock1') {
            echo json_encode(['success' => false, 'message' => 'Akses Ditolak: Password Superman salah!']);
            exit;
        }
        // Superman is fully authenticated via hardcoded password, skip file-based checks
    } else {
        $checkFile = $DATA_DIR . '/settings_' . $reqProfile . '.php';
        $oldCheckFile = $DATA_DIR . '/settings_' . $reqProfile . '.json';
        
        $fileToRead = file_exists($checkFile) ? $checkFile : (file_exists($oldCheckFile) ? $oldCheckFile : null);
        
        if ($fileToRead) {
            $content = file_get_contents($fileToRead);
            $startPos = strpos($content, '{');
            $jsonStr = $startPos !== false ? substr($content, $startPos) : '{}';
            $existingData = json_decode($jsonStr, true) ?: [];
            
            if (!empty($existingData['profile_password'])) {
                $isMatch = false;
                if (password_verify($reqPassword, $existingData['profile_password'])) {
                    $isMatch = true;
                } elseif (hash_equals($existingData['profile_password'], $reqPassword)) {
                    // Fallback for old plaintext passwords
                    $isMatch = true;
                }
                
                if (!$isMatch) {
                    echo json_encode(['success' => false, 'message' => 'Akses Ditolak: Password Salah untuk user ' . $reqProfile . '!']);
                    exit;
                }
                
                // Track last login time (update at most once every 5 minutes to reduce I/O)
                $now = time();
                $lastLogin = $existingData['last_login'] ?? 0;
                if ($now - $lastLogin > 300) { // 5 minutes
                    $existingData['last_login'] = $now;
                    file_put_contents($fileToRead, "<?php exit('No direct script access allowed'); ?>\n" . json_encode($existingData, JSON_PRETTY_PRINT));
                }

            } else {
                // Profil legacy tanpa password! Otomatis klaim dengan password pertama yang dimasukkan
                $existingData['profile_password'] = password_hash($reqPassword, PASSWORD_DEFAULT);
                $existingData['last_login'] = time();
                file_put_contents($fileToRead, "<?php exit('No direct script access allowed'); ?>\n" . json_encode($existingData, JSON_PRETTY_PRINT));
            }
        } else {
            // Blokir akses jika file profil tidak ada, pendaftaran hanya via Google Login
            echo json_encode(['success' => false, 'message' => 'Akses Ditolak: Hanya "superman" yang diizinkan masuk melalui form ini!']);
            exit;
        }
    }
}
// -- END AUTHENTICATION CHECK --

if ($action === 'google_login' && $method === 'POST') {
    $credential = $input['credential'] ?? '';
    if (empty($credential)) {
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit;
    }
    
    // Verify token
    $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . $credential);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (!$data || isset($data['error']) || empty($data['email'])) {
        echo json_encode(['success' => false, 'message' => 'Token Google tidak valid atau sudah kadaluarsa.']);
        exit;
    }
    
    $email = strtolower($data['email']);
    // Check if domain is allowed
    if (strpos($email, '@itgroupinc.asia') === false && $email !== 'admin@itgroupinc.asia') {
        echo json_encode(['success' => false, 'message' => 'Maaf, aplikasi ini hanya untuk internal @itgroupinc.asia']);
        exit;
    }
    
    // Profile is prefix of email
    $profile = explode('@', $email)[0];
    // Google unique sub as password equivalent
    $googleId = $data['sub']; 
    
    // Check if settings file exists, if not, create it
    $file = $DATA_DIR . '/settings_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $profile) . '.php';
    
    $settings = [];
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $startPos = strpos($content, '{');
        if ($startPos !== false) {
            $jsonStr = substr($content, $startPos);
            $settings = json_decode($jsonStr, true) ?: [];
        }
    }
    
    // We override their password with Google ID hash
    $settings['profile_password'] = password_hash($googleId, PASSWORD_DEFAULT);
    
    $json = json_encode($settings, JSON_PRETTY_PRINT);
    file_put_contents($file, "<?php exit('No direct script access allowed'); ?>\n" . $json);
    
    echo json_encode(['success' => true, 'profile' => $profile, 'password' => $googleId]);
    exit;
}

if ($action === 'manual_login' && $method === 'POST') {
    $reqProfile = isset($input['profile']) ? strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['profile'], 0, 32))) : '';
    $reqPassword = $input['password'] ?? '';
    
    if ($reqProfile === 'superman') {
        if ($reqPassword === 'musikrock1') {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Akses Ditolak: Password Superman salah!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Akses Ditolak: Harap gunakan tombol "Sign in with Google" untuk masuk!']);
    }
    exit;
}

if ($action === 'get_all_profiles' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $files = glob($DATA_DIR . '/settings_*.{php,json}', GLOB_BRACE);
        $profiles = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                $basename = preg_replace('/\.(php|json)$/', '', basename($file));
                $content = file_get_contents($file);
                $startPos = strpos($content, '{');
                $jsonStr = $startPos !== false ? substr($content, $startPos) : '{}';
                $data = json_decode($jsonStr, true) ?: [];
                
                $pass = !empty($data['profile_password']) ? '(Aman)' : '(Tanpa Password)';
                $lastLogin = !empty($data['last_login']) ? $data['last_login'] : 0;
                $sheetName = $data['sheetName'] ?? '';
                
                if ($basename === 'settings_default' || $basename === 'settings') {
                    $profiles[] = 'default | ' . $pass . ' | ' . $lastLogin . ' | ' . $sheetName;
                } else {
                    $username = str_replace('settings_', '', $basename);
                    $profiles[] = $username . ' | ' . $pass . ' | ' . $lastLogin . ' | ' . $sheetName;
                }
            }
        }
        echo json_encode(['success' => true, 'profiles' => $profiles]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'reset_password' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $targetUser = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['targetUser'] ?? '', 0, 32)));
        if (empty($targetUser)) {
            echo json_encode(['success' => false, 'message' => 'Username tidak valid.']);
            exit;
        }
        
        $targetFile = $DATA_DIR . '/settings_' . $targetUser . '.php';
        $oldTargetFile = $DATA_DIR . '/settings_' . $targetUser . '.json';
        
        $fileToEdit = file_exists($targetFile) ? $targetFile : (file_exists($oldTargetFile) ? $oldTargetFile : null);
        
        if ($fileToEdit) {
            $content = file_get_contents($fileToEdit);
            $startPos = strpos($content, '{');
            $jsonStr = $startPos !== false ? substr($content, $startPos) : '{}';
            $data = json_decode($jsonStr, true) ?: [];
            
            $data['profile_password'] = ''; // Kosongkan password saja
            
            // Simpan kembali
            file_put_contents($targetFile, "<?php exit('No direct script access allowed'); ?>\n" . json_encode($data, JSON_PRETTY_PRINT));
            if ($fileToEdit === $oldTargetFile) unlink($oldTargetFile);
            
            echo json_encode(['success' => true, 'message' => "BERHASIL: Password untuk user '$targetUser' telah di-reset!\nSilakan minta dia login dengan password baru. Pengaturan Zoho & Google-nya tetap aman."]);
        } else {
            echo json_encode(['success' => false, 'message' => "GAGAL: User '$targetUser' tidak ditemukan."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'update_user_sheet_name' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $targetUser = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['targetUser'] ?? '', 0, 32)));
        $newSheetName = trim($input['newSheetName'] ?? '');
        
        if (isSheetNameTaken($newSheetName, $targetUser)) {
            echo json_encode(['success' => false, 'message' => "GAGAL: Nama sheet '$newSheetName' sudah digunakan oleh user lain!"]);
            exit;
        }

        $targetFile = $DATA_DIR . '/settings_' . $targetUser . '.php';
        
        if (file_exists($targetFile)) {
            $content = file_get_contents($targetFile);
            $startPos = strpos($content, '{');
            $jsonStr = $startPos !== false ? substr($content, $startPos) : '{}';
            $data = json_decode($jsonStr, true) ?: [];
            
            $data['sheetName'] = $newSheetName;
            
            file_put_contents($targetFile, "<?php exit('No direct script access allowed'); ?>\n" . json_encode($data, JSON_PRETTY_PRINT));
            
            echo json_encode(['success' => true, 'message' => "BERHASIL: Nama Sheet untuk user '$targetUser' telah diubah menjadi '$newSheetName'!"]);
        } else {
            echo json_encode(['success' => false, 'message' => "GAGAL: User '$targetUser' tidak ditemukan."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'delete_profile' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $targetUser = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['targetUser'] ?? '', 0, 32)));
        if (empty($targetUser) || $targetUser === 'superman') {
            echo json_encode(['success' => false, 'message' => 'Username tidak valid.']);
            exit;
        }
        
        $targetFile = $DATA_DIR . '/settings_' . $targetUser . '.php';
        $oldTargetFile = $DATA_DIR . '/settings_' . $targetUser . '.json';
        
        $deleted = false;
        if (file_exists($targetFile)) {
            unlink($targetFile);
            $deleted = true;
        }
        if (file_exists($oldTargetFile)) {
            unlink($oldTargetFile);
            $deleted = true;
        }
        
        if ($deleted) {
            echo json_encode(['success' => true, 'message' => "BERHASIL: User '$targetUser' telah dihapus secara permanen dari sistem!"]);
        } else {
            echo json_encode(['success' => false, 'message' => "GAGAL: User '$targetUser' tidak ditemukan."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'save_global_settings' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        global $DATA_DIR;
        $globalFile = $DATA_DIR . '/global_settings.json';
        $newData = [
            'shiftSpreadsheetId' => $input['settings']['shiftSpreadsheetId'] ?? '',
            'formAbsenUrl' => $input['settings']['formAbsenUrl'] ?? '',
            'dataSpreadsheetId' => $input['settings']['dataSpreadsheetId'] ?? '',
            'dataGoogleCredentials' => $input['settings']['dataGoogleCredentials'] ?? ''
        ];
        file_put_contents($globalFile, json_encode($newData, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'Global settings saved!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'test_shift_spreadsheet' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $spreadsheetId = $input['spreadsheetId'] ?? '';
        if (empty($spreadsheetId)) {
            echo json_encode(['success' => false, 'message' => 'ID Spreadsheet Kosong!']);
            exit;
        }

        // Cari user yang punya google credentials untuk minjam akses
        global $DATA_DIR;
        $files = glob($DATA_DIR . '/settings_*.{php,json}', GLOB_BRACE);
        $validToken = null;
        if (is_array($files)) {
            foreach ($files as $f) {
                if (strpos($f, 'settings_superman') !== false) continue;
                $content = file_get_contents($f);
                $startPos = strpos($content, '{');
                if ($startPos !== false) {
                    $data = json_decode(substr($content, $startPos), true);
                    if (!empty($data['googleCredentials'])) {
                        $token = getGoogleAccessToken($data['googleCredentials']);
                        if ($token) {
                            $validToken = $token;
                            break; // Ketemu token yang valid
                        }
                    }
                }
            }
        }
        
        if (!$validToken) {
            echo json_encode(['success' => false, 'message' => 'Gagal: Belum ada karyawan yang memasukkan Service Account JSON yang valid.']);
            exit;
        }

        // Test API call to Google Sheets
        $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $validToken"]);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $sheetData = json_decode($res, true);
            $sheetTitle = $sheetData['properties']['title'] ?? 'Unknown Sheet';
            echo json_encode(['success' => true, 'message' => "Terhubung dengan sukses ke Sheet: '$sheetTitle'"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Akses Ditolak / ID Salah. Pastikan ID Spreadsheet benar dan Service Account telah diberi akses Viewer/Editor!"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'test_data_spreadsheet' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $spreadsheetId = $input['spreadsheetId'] ?? '';
        $credentials = $input['credentials'] ?? '';
        
        if (empty($spreadsheetId) || empty($credentials)) {
            echo json_encode(['success' => false, 'message' => 'Spreadsheet ID & Service Account JSON tidak boleh kosong!']);
            exit;
        }

        $token = getGoogleAccessToken($credentials);
        if (!$token) {
            echo json_encode(['success' => false, 'message' => 'Gagal mendapatkan token Google. Format JSON Service Account salah!']);
            exit;
        }

        $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $sheetData = json_decode($res, true);
            $sheetTitle = $sheetData['properties']['title'] ?? 'Unknown Sheet';
            
            $tabs = [];
            if (!empty($sheetData['sheets'])) {
                foreach ($sheetData['sheets'] as $s) {
                    $tabs[] = [
                        'title' => $s['properties']['title'],
                        'sheetId' => $s['properties']['sheetId']
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'message' => "Terhubung dengan sukses ke Sheet: '$sheetTitle'", 'tabs' => $tabs]);
        } else {
            echo json_encode(['success' => false, 'message' => "Akses Ditolak / ID Salah. Pastikan ID Spreadsheet benar dan Service Account telah diberi akses Viewer/Editor!"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'rename_sheet_tab' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $spreadsheetId = $input['spreadsheetId'] ?? '';
        $credentials = $input['credentials'] ?? '';
        $sheetId = $input['sheetId'] ?? null;
        $newTitle = $input['newTitle'] ?? '';
        
        if (empty($spreadsheetId) || empty($credentials) || $sheetId === null || empty($newTitle)) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap!']);
            exit;
        }

        $token = getGoogleAccessToken($credentials);
        if (!$token) {
            echo json_encode(['success' => false, 'message' => 'Gagal mendapatkan token Google.']);
            exit;
        }

        $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId:batchUpdate";
        $payload = [
            'requests' => [
                [
                    'updateSheetProperties' => [
                        'properties' => [
                            'sheetId' => (int)$sheetId,
                            'title' => $newTitle
                        ],
                        'fields' => 'title'
                    ]
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            echo json_encode(['success' => true, 'message' => "Nama sheet berhasil diubah!"]);
        } else {
            $err = json_decode($res, true);
            $msg = $err['error']['message'] ?? 'Gagal mengubah nama sheet.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'delete_sheet_tab' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $spreadsheetId = $input['spreadsheetId'] ?? '';
        $credentials = $input['credentials'] ?? '';
        $sheetId = $input['sheetId'] ?? null;
        
        if (empty($spreadsheetId) || empty($credentials) || $sheetId === null) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap!']);
            exit;
        }

        $token = getGoogleAccessToken($credentials);
        if (!$token) {
            echo json_encode(['success' => false, 'message' => 'Gagal mendapatkan token Google.']);
            exit;
        }

        $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId:batchUpdate";
        $payload = [
            'requests' => [
                [
                    'deleteSheet' => [
                        'sheetId' => (int)$sheetId
                    ]
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            echo json_encode(['success' => true, 'message' => "Sheet berhasil dihapus!"]);
        } else {
            $err = json_decode($res, true);
            $msg = $err['error']['message'] ?? 'Gagal menghapus sheet.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'test_form_url' && $method === 'POST') {
    if (isset($input['profile']) && $input['profile'] === 'superman' && isset($input['password']) && $input['password'] === 'musikrock1') {
        $formUrl = $input['formUrl'] ?? '';
        if (empty($formUrl)) {
            echo json_encode(['success' => false, 'message' => 'URL Kosong!']);
            exit;
        }

        // SSRF Protection: Only allow Google Forms URLs
        if (strpos($formUrl, 'https://docs.google.com/forms/') !== 0) {
            echo json_encode(['success' => false, 'message' => 'Hanya URL Google Forms yang diizinkan! (harus diawali dengan https://docs.google.com/forms/)']);
            exit;
        }

        $ch = curl_init($formUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // We just need the headers/status
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            echo json_encode(['success' => true, 'message' => "Terhubung dengan sukses ke URL Form!"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Gagal terhubung ke Form. Status HTTP: $httpCode"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'get_settings' && $method === 'POST') {
    $s = getSettings();
    unset($s['profile_password']); // Secure: Don't send hashed password back to client
    echo json_encode(['settings' => $s]);
    exit;
}

if ($action === 'save_settings' && $method === 'POST') {
    $file = getSettingsFile();
    $settingsToSave = $input['settings'] ?? [];
    $existingSettings = getSettings();
    
    // Check for duplicate sheetName
    global $input;
    $currentUser = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', substr($input['profile'] ?? '', 0, 32)));
    $newSheetName = trim($settingsToSave['sheetName'] ?? '');
    
    if (isSheetNameTaken($newSheetName, $currentUser)) {
        echo json_encode(['success' => false, 'message' => "Nama sheet '$newSheetName' sudah digunakan oleh user lain! Harap pilih nama lain sebelum menyimpan."]);
        exit;
    }

    // Password tidak perlu dicek atau di-hash ulang karena sudah diatur otomatis oleh Google Login.
    unset($settingsToSave['profile_password']);
    
    // SSRF Protection: Whitelist API URLs
    $allowedUrls = [
        'https://accounts.zoho.com', 'https://accounts.zoho.eu', 'https://accounts.zoho.in', 'https://accounts.zoho.com.au',
        'https://projectsapi.zoho.com', 'https://projectsapi.zoho.eu', 'https://projectsapi.zoho.in', 'https://projectsapi.zoho.com.au'
    ];
    if (!empty($settingsToSave['accountsUrl']) && !in_array($settingsToSave['accountsUrl'], $allowedUrls)) {
        $settingsToSave['accountsUrl'] = 'https://accounts.zoho.com';
    }
    if (!empty($settingsToSave['apiUrl']) && !in_array($settingsToSave['apiUrl'], $allowedUrls)) {
        $settingsToSave['apiUrl'] = 'https://projectsapi.zoho.com';
    }
    
    // Password tidak lagi di-hash di sini
    
    // Merge to prevent data loss for backend-only fields (e.g. last_login)
    $finalSettings = array_merge($existingSettings, $settingsToSave);
    
    file_put_contents($file, "<?php exit('No direct script access allowed'); ?>\n" . json_encode($finalSettings, JSON_PRETTY_PRINT), LOCK_EX);
    
    // Proactively initialize the sheet tab if it doesn't exist
    getLogsFromSheet();
    
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'get_logs' && $method === 'POST') {
    echo json_encode(['logs' => array_reverse(getLogsFromSheet())]);
    exit;
}

if ($action === 'delete_log' && $method === 'POST') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['spreadsheetId'];
    
    if (!$token || !$spreadsheetId) {
        echo json_encode(['success' => false, 'message' => 'Settings missing']);
        exit;
    }
    
    $rowIndex = intval($input['rowIndex'] ?? 0);
    if ($rowIndex < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid Row Index']);
        exit;
    }
    
    $sheetName = !empty($settings['sheetName']) ? $settings['sheetName'] : 'Sheet1';
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!A$rowIndex:M$rowIndex:clear";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'API Error: ' . $res]);
    }
    exit;
}

if ($action === 'update_status' && $method === 'POST') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['spreadsheetId'];
    if (!$token || !$spreadsheetId) {
        echo json_encode(['success' => false, 'message' => 'Settings missing']);
        exit;
    }
    $rowIndex = intval($input['rowIndex'] ?? 0);
    $newStatus = $input['status'] ?? '';
    if ($rowIndex < 1 || !$newStatus) {
        echo json_encode(['success' => false, 'message' => 'Missing data or invalid index']);
        exit;
    }
    
    $sheetName = !empty($settings['sheetName']) ? $settings['sheetName'] : 'Sheet1';
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!H$rowIndex?valueInputOption=USER_ENTERED";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['values' => [[escapeFormula($newStatus)]]]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Gagal update status: $res"]);
    }
    exit;
}

if ($action === 'get_shift_tabs' && $method === 'POST') {
    $reqId = $input['settings']['shiftSpreadsheetId'] ?? '';
    echo json_encode(getShiftTabs($reqId));
    exit;
}

if ($action === 'get_shift_names' && $method === 'POST') {
    $sheetNameReq = $input['sheetName'] ?? '';
    echo json_encode(getShiftNames($sheetNameReq));
    exit;
}

if ($action === 'get_shift_schedule' && $method === 'POST') {
    $name = $input['name'] ?? '';
    $sheetNameReq = $input['sheetName'] ?? '';
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Nama diperlukan']);
        exit;
    }
    echo json_encode(getShiftSchedule($name, $sheetNameReq));
    exit;
}

if ($action === 'bulk_update_status' && $method === 'POST') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['spreadsheetId'];
    if (!$token || !$spreadsheetId) {
        echo json_encode(['success' => false, 'message' => 'Settings missing']);
        exit;
    }
    
    $updates = $input['updates'] ?? [];
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No updates provided']);
        exit;
    }

    $sheetName = !empty($settings['sheetName']) ? $settings['sheetName'] : 'Sheet1';
    $safeSheetName = "'" . str_replace("'", "''", $sheetName) . "'";
    
    $data = [];
    foreach ($updates as $upd) {
        $rIdx = intval($upd['rowIndex']);
        if ($rIdx < 1) continue;
        $data[] = [
            'range' => "$safeSheetName!H$rIdx",
            'values' => [[escapeFormula($upd['status'])]]
        ];
    }
    
    if (empty($data)) {
        echo json_encode(['success' => true]);
        exit;
    }
    
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values:batchUpdate";
    $payload = [
        'valueInputOption' => 'USER_ENTERED',
        'data' => $data
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Gagal bulk update status: $res"]);
    }
    exit;
}

if ($action === 'bulk_delete_logs' && $method === 'POST') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['spreadsheetId'];
    if (!$token || !$spreadsheetId) {
        echo json_encode(['success' => false, 'message' => 'Settings missing']);
        exit;
    }
    
    $rowIndices = $input['rowIndices'] ?? [];
    if (empty($rowIndices)) {
        echo json_encode(['success' => false, 'message' => 'No rows provided']);
        exit;
    }

    $sheetName = !empty($settings['sheetName']) ? $settings['sheetName'] : 'Sheet1';
    $safeSheetName = "'" . str_replace("'", "''", $sheetName) . "'";
    
    $ranges = [];
    foreach ($rowIndices as $rIdx) {
        $idx = intval($rIdx);
        if ($idx > 0) {
            $ranges[] = "$safeSheetName!A$idx:M$idx";
        }
    }
    
    if (empty($ranges)) {
        echo json_encode(['success' => true]);
        exit;
    }
    
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values:batchClear";
    $payload = [
        'ranges' => $ranges
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Gagal bulk delete: $res"]);
    }
    exit;
}

// -- HELPER: Mencegah Formula Injection --
function escapeFormula($str) {
    if (is_string($str) && strlen($str) > 0) {
        $firstChar = $str[0];
        if (in_array($firstChar, ['=', '+', '-', '@'])) {
            return "'" . $str;
        }
    }
    return $str;
}

if ($action === 'add_log' && $method === 'POST') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['spreadsheetId'];
    
    if (!$token || !$spreadsheetId) {
        echo json_encode(['success' => false, 'message' => 'Google Sheets settings not configured']);
        exit;
    }
    
    $sheetName = !empty($settings['sheetName']) ? $settings['sheetName'] : 'Sheet1';
    
    // Read first to find the actual empty row (ignoring formatting)
    $getUrl = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!A3:M";
    $chGet = curl_init($getUrl);
    curl_setopt($chGet, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chGet, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $resGet = curl_exec($chGet);
    curl_close($chGet);
    
    $dataGet = json_decode($resGet, true);
    
    if (isset($dataGet['error']) && $dataGet['error']['code'] == 400 && strpos($dataGet['error']['message'], 'Unable to parse range') !== false) {
        if (createGoogleSheetTab($spreadsheetId, $sheetName, $token)) {
            // Retry fetch
            $chGet = curl_init($getUrl);
            curl_setopt($chGet, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chGet, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
            $resGet = curl_exec($chGet);
            curl_close($chGet);
            $dataGet = json_decode($resGet, true);
        }
    }
    
    $rows = $dataGet['values'] ?? [];
    
    $targetRow = 3;
    $existingId = '';
    foreach ($rows as $row) {
        if (empty($row[1]) && empty($row[9]) && empty($row[10])) {
            $existingId = $row[0] ?? '';
            break; // found an empty row
        }
        $targetRow++;
    }
    
    $finalId = !empty($input['id']) ? $input['id'] : (!empty($existingId) ? $existingId : ($targetRow - 2));
    
    // Now PUT exactly at the target row
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!A$targetRow:M$targetRow?valueInputOption=USER_ENTERED";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    
    $values = [
        escapeFormula($finalId),
        escapeFormula($input['startDate'] ?? ''),
        escapeFormula($input['startTime'] ?? ''),
        escapeFormula($input['lembur'] ?? ''),
        escapeFormula($input['endDate'] ?? ''),
        escapeFormula($input['endTime'] ?? ''),
        escapeFormula($input['duration'] ?? ''),
        escapeFormula($input['status'] ?? ''),
        escapeFormula($input['vendor'] ?? ''),
        escapeFormula($input['project'] ?? ''),
        escapeFormula($input['task'] ?? ''),
        escapeFormula($input['notes'] ?? ''),
        '' // task url empty initially
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['values' => [$values]]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Gagal simpan ke Sheet: $res"]);
    }
    exit;
}

if ($action === 'edit_log' && $method === 'POST') {
    $settings = getSettings();
    $token = getGoogleAccessToken($settings['googleCredentials']);
    $spreadsheetId = $settings['spreadsheetId'];
    if (!$token || !$spreadsheetId) {
        echo json_encode(['success' => false, 'message' => 'Settings missing']);
        exit;
    }
    $rowIndex = intval($input['rowIndex'] ?? 0);
    if ($rowIndex < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid Row Index']);
        exit;
    }
    
    $sheetName = !empty($settings['sheetName']) ? $settings['sheetName'] : 'Sheet1';
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "!A$rowIndex:M$rowIndex?valueInputOption=USER_ENTERED";
    
    $values = [
        escapeFormula($input['id'] ?? ''),
        escapeFormula($input['startDate'] ?? ''),
        escapeFormula($input['startTime'] ?? ''),
        escapeFormula($input['lembur'] ?? ''),
        escapeFormula($input['endDate'] ?? ''),
        escapeFormula($input['endTime'] ?? ''),
        escapeFormula($input['duration'] ?? ''),
        escapeFormula($input['status'] ?? ''),
        escapeFormula($input['vendor'] ?? ''),
        escapeFormula($input['project'] ?? ''),
        escapeFormula($input['task'] ?? ''),
        escapeFormula($input['notes'] ?? ''),
        escapeFormula($input['taskUrl'] ?? '')
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['values' => [$values]]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Gagal update Sheet: $res"]);
    }
    exit;
}

if ($action === 'generate_zoho_token' && $method === 'POST') {
    $client_id = $input['client_id'] ?? '';
    $client_secret = $input['client_secret'] ?? '';
    $code = $input['code'] ?? '';
    $accountsUrl = $input['accountsUrl'] ?? 'https://accounts.zoho.com';

    if (!$client_id || !$client_secret || !$code) {
        echo json_encode(['success' => false, 'message' => 'Missing fields']);
        exit;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, rtrim($accountsUrl, '/') . '/oauth/v2/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'authorization_code',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $resData = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300 && isset($resData['refresh_token'])) {
        echo json_encode(['success' => true, 'refresh_token' => $resData['refresh_token']]);
    } else {
        $errMsg = $resData['error'] ?? 'Failed to generate token';
        echo json_encode(['success' => false, 'message' => $errMsg, 'zoho_response' => $resData]);
    }
    exit;
}

if ($action === 'get_zoho_projects' && $method === 'POST') {
    $settings = getSettings();
    if (empty($settings['clientId']) || empty($settings['refreshToken'])) {
        echo json_encode(['success' => false, 'message' => 'Zoho API Settings missing']);
        exit;
    }

    // Refresh Token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $settings['accountsUrl'] . '/oauth/v2/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $settings['clientId'],
        'client_secret' => $settings['clientSecret'],
        'refresh_token' => $settings['refreshToken']
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tokenResponse = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($tokenResponse, true);
    if (empty($tokenData['access_token'])) {
        echo json_encode(['success' => false, 'message' => 'Failed to refresh Zoho token']);
        exit;
    }
    $accessToken = $tokenData['access_token'];

    $portal = $settings['portalName'];
    $apiUrl = $settings['apiUrl'];
    
    // Fetch Projects
    $url = $apiUrl . '/restapi/portal/' . $portal . '/projects/';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        $projData = json_decode($res, true);
        $projects = [];
        if (isset($projData['projects'])) {
            foreach ($projData['projects'] as $p) {
                // Return only names to frontend datalist
                $projects[] = $p['name'];
            }
        }
        echo json_encode(['success' => true, 'projects' => $projects]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch projects from Zoho']);
    }
    exit;
}

if ($action === 'get_project_tasks' && $method === 'POST') {
    set_time_limit(0);
    $settings = getSettings();
    $projectName = $input['projectName'] ?? '';
    if (!$projectName) {
        echo json_encode(['success' => false, 'message' => 'Project Name required']);
        exit;
    }
    
    // Auth
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $settings['accountsUrl'] . '/oauth/v2/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $settings['clientId'],
        'client_secret' => $settings['clientSecret'],
        'refresh_token' => $settings['refreshToken']
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tokenResponse = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($tokenResponse, true);
    if (empty($tokenData['access_token'])) {
        echo json_encode(['success' => false, 'message' => 'Failed to refresh Zoho token']);
        exit;
    }
    $accessToken = $tokenData['access_token'];
    $portal = $settings['portalName'];
    $apiUrl = $settings['apiUrl'];

    function localApiCall($endpoint) {
        global $apiUrl, $portal, $accessToken;
        $url = rtrim($apiUrl, '/') . '/restapi/portal/' . $portal . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }
    
    // Find project ID by name
    $projData = localApiCall('/projects/');
    $pid = null;
    if (isset($projData['projects'])) {
        foreach ($projData['projects'] as $p) {
            if (strtolower($p['name']) === strtolower($projectName)) {
                $pid = $p['id_string'];
                break;
            }
        }
    }
    
    if (!$pid) {
        echo json_encode(['success' => false, 'message' => 'Project not found in Zoho']);
        exit;
    }
    
    // Get All Tasks with Pagination
    $allTasks = [];
    $subtaskUrls = [];
    $index = 0;
    $range = 100;
    
    while (true) {
        $taskRes = localApiCall('/projects/' . $pid . '/tasks/?range=' . $range . '&index=' . $index);
        
        if (!isset($taskRes['tasks']) || count($taskRes['tasks']) === 0) {
            break;
        }

        foreach ($taskRes['tasks'] as $t) {
            $statusRaw = $t['status'] ?? '';
            $statusName = is_array($statusRaw) ? ($statusRaw['name'] ?? '') : $statusRaw;
            $allTasks[] = [
                'id' => $t['id_string'],
                'name' => $t['name'] ?? 'Unknown Task',
                'parent' => null,
                'status' => $statusName,
                'status_id' => is_array($statusRaw) ? ($statusRaw['id'] ?? '') : ''
            ];
            
            // Optimasi: Jangan ambil subtask jika parent-nya sudah Complete/Closed (mencegah PHP timeout)
            $statusLower = strtolower($statusName);
            if (strpos($statusLower, 'complete') === false && strpos($statusLower, 'closed') === false) {
                $subUrl = rtrim($apiUrl, '/') . '/restapi/portal/' . $portal . '/projects/' . $pid . '/tasks/' . $t['id_string'] . '/subtasks/';
                $subtaskUrls[$t['id_string']] = $subUrl;
            }
        }
        
        $index += $range;
    }

        // Jalankan CURL secara paralel jika didukung, atau sequential jika tidak
        if (function_exists('curl_multi_init')) {
            $chunks = array_chunk($subtaskUrls, 10, true);
            foreach ($chunks as $chunk) {
                $mh = curl_multi_init();
                $chArray = [];

                foreach ($chunk as $parentId => $url) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                    curl_multi_add_handle($mh, $ch);
                    $chArray[$parentId] = $ch;
                }

                $running = null;
                do {
                    curl_multi_exec($mh, $running);
                    curl_multi_select($mh);
                } while ($running > 0);

                foreach ($chArray as $parentId => $ch) {
                    $res = curl_multi_getcontent($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_multi_remove_handle($mh, $ch);
                    curl_close($ch);

                    if ($httpCode === 200 && $res) {
                        $subData = json_decode($res, true);
                        if (isset($subData['tasks']) && is_array($subData['tasks'])) {
                            foreach ($subData['tasks'] as $sub) {
                                $subStatusRaw = $sub['status'] ?? '';
                                $allTasks[] = [
                                    'id' => $sub['id_string'],
                                    'name' => $sub['name'] ?? 'Unknown Subtask',
                                    'parent' => (string)$parentId,
                                    'status' => is_array($subStatusRaw) ? ($subStatusRaw['name'] ?? '') : $subStatusRaw,
                                    'status_id' => is_array($subStatusRaw) ? ($subStatusRaw['id'] ?? '') : ''
                                ];
                            }
                        }
                    }
                }
                curl_multi_close($mh);
            }
        } else {
            // Fallback: Sequential CURL jika server hosting mematikan fitur curl_multi_init
            foreach ($subtaskUrls as $parentId => $url) {
                $subData = localApiCall(str_replace(rtrim($apiUrl, '/'), '', $url));
                if (isset($subData['tasks']) && is_array($subData['tasks'])) {
                    foreach ($subData['tasks'] as $sub) {
                        $subStatusRaw = $sub['status'] ?? '';
                        $allTasks[] = [
                            'id' => $sub['id_string'],
                            'name' => $sub['name'] ?? 'Unknown Subtask',
                            'parent' => (string)$parentId,
                            'status' => is_array($subStatusRaw) ? ($subStatusRaw['name'] ?? '') : $subStatusRaw,
                            'status_id' => is_array($subStatusRaw) ? ($subStatusRaw['id'] ?? '') : ''
                        ];
                    }
                }
            }
        }
    
    echo json_encode(['success' => true, 'tasks' => $allTasks, 'projectId' => $pid]);
    exit;
}

if ($action === 'create_project_task' && $method === 'POST') {
    $settings = getSettings();
    $pid = $input['projectId'] ?? '';
    $taskName = $input['taskName'] ?? '';
    $parentId = $input['parentId'] ?? null;
    
    if (!$pid || !$taskName) {
        echo json_encode(['success' => false, 'message' => 'Missing project or task name']);
        exit;
    }
    
    // Auth
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $settings['accountsUrl'] . '/oauth/v2/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $settings['clientId'],
        'client_secret' => $settings['clientSecret'],
        'refresh_token' => $settings['refreshToken']
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tokenResponse = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($tokenResponse, true);
    $accessToken = $tokenData['access_token'] ?? '';
    $portal = $settings['portalName'];
    $apiUrl = $settings['apiUrl'];
    
    $endpoint = '/projects/' . $pid . '/tasks/';
    if ($parentId) {
        $endpoint = '/projects/' . $pid . '/tasks/' . $parentId . '/subtasks/';
    }
    
    $url = rtrim($apiUrl, '/') . '/restapi/portal/' . $portal . $endpoint;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['name' => $taskName]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create task', 'res' => $res]);
    }
    exit;
}

if ($action === 'update_project_task_status' && $method === 'POST') {
    $settings = getSettings();
    $pid = $input['projectId'] ?? '';
    $taskId = $input['taskId'] ?? '';
    $statusId = $input['statusId'] ?? '';
    
    if (!$pid || !$taskId || !$statusId) {
        echo json_encode(['success' => false, 'message' => 'Missing project, task, or status ID']);
        exit;
    }
    
    // Auth
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $settings['accountsUrl'] . '/oauth/v2/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $settings['clientId'],
        'client_secret' => $settings['clientSecret'],
        'refresh_token' => $settings['refreshToken']
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tokenResponse = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($tokenResponse, true);
    $accessToken = $tokenData['access_token'] ?? '';
    $portal = $settings['portalName'];
    $apiUrl = $settings['apiUrl'];
    
    $endpoint = '/projects/' . $pid . '/tasks/' . $taskId . '/';
    $url = rtrim($apiUrl, '/') . '/restapi/portal/' . $portal . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    // Zoho Projects API accepts 'custom_status' for the status ID
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['custom_status' => $statusId]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update task status', 'res' => $res]);
    }
    exit;
}

if ($action === 'sync' && $method === 'POST') {
    $settings = getSettings();
    $logsData = getLogsFromSheet();
    $outputLogs = [];
    
    function logMsg($msg, $type = 'info') {
        global $outputLogs;
        $outputLogs[] = ['message' => $msg, 'type' => $type];
    }
    
    if (empty($settings['clientId']) || empty($settings['refreshToken'])) {
        logMsg('Settings missing. Please configure Client ID and Refresh Token.', 'error');
        echo json_encode(['success' => false, 'logs' => $outputLogs]);
        exit;
    }

    logMsg('Refreshing Zoho Access Token...', 'info');
    
    // 1. Get Access Token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $settings['accountsUrl'] . '/oauth/v2/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $settings['clientId'],
        'client_secret' => $settings['clientSecret'],
        'refresh_token' => $settings['refreshToken']
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tokenResponse = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($tokenResponse, true);
    if (empty($tokenData['access_token'])) {
        logMsg('Failed to refresh token: ' . $tokenResponse, 'error');
        echo json_encode(['success' => false, 'logs' => $outputLogs]);
        exit;
    }
    $accessToken = $tokenData['access_token'];
    logMsg('Access Token acquired.', 'success');

    $portal = $settings['portalName'];
    $apiUrl = $settings['apiUrl'];
    
    function apiCall($endpoint, $method = 'GET', $data = []) {
        global $accessToken, $apiUrl, $portal;
        $url = $apiUrl . '/restapi/portal/' . $portal . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = [
            'Authorization: Bearer ' . $accessToken
        ];
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['code' => $httpCode, 'data' => json_decode($res, true)];
    }

    $projectCache = [];
    $taskCache = [];
    $syncSuccess = true;

    $projRes = apiCall('/projects/');
    if ($projRes['code'] == 200 && isset($projRes['data']['projects'])) {
        foreach ($projRes['data']['projects'] as $p) {
            $projectCache[strtolower($p['name'])] = $p['id_string'];
        }
    } else {
        if ($projRes['code'] == 404) {
            // Check available portals
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl . '/restapi/portals/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
            $portalsRaw = curl_exec($ch);
            curl_close($ch);
            
            $portalsData = json_decode($portalsRaw, true);
            if (!empty($portalsData['portals'])) {
                $available = array_map(function($p) { return $p['id_string']; }, $portalsData['portals']);
                logMsg("Portal '{$portal}' tidak ditemukan! Portal yang tersedia untuk bot ini adalah: " . implode(', ', $available), 'error');
            } else {
                logMsg("Portal '{$portal}' tidak ditemukan (404), dan bot ini belum diundang ke portal mana pun! Silakan invite email bot ke Zoho Projects Anda.", 'error');
            }
        } else {
            logMsg('Failed to fetch projects. Check portal name and token scope. Raw Response: ' . json_encode($projRes), 'error');
        }
        echo json_encode(['success' => false, 'logs' => $outputLogs]);
        exit;
    }

    foreach ($logsData as $log) {
        if ($log['status'] !== 'final') {
            continue;
        }

        $pName = strtolower($log['project']);
        $tName = $log['task'];
        
        logMsg("Processing row {$log['rowIndex']}: [{$log['project']}] -> [{$log['task']}]", 'info');

        if (!isset($projectCache[$pName])) {
            logMsg("Project '{$log['project']}' not found in Zoho. Skipping.", 'error');
            $syncSuccess = false;
            updateRowInSheet($log['rowIndex'], $log, 'pending', '');
            continue;
        }
        $pid = $projectCache[$pName];

        if (!isset($taskCache[$pid])) {
            $taskCache[$pid] = [];
            // Fungsi helper untuk memproses array task dan memasukkan ke cache
            $processTasks = function($tasksList) use (&$taskCache, $pid, &$debugTasks) {
                foreach ($tasksList as $t) {
                    $tNameLower = strtolower($t['name']);
                    
                    $statusName = '';
                    if (isset($t['status'])) {
                        if (is_array($t['status'])) {
                            $statusName = strtolower($t['status']['name'] ?? '');
                        } else {
                            $statusName = strtolower($t['status']);
                        }
                    }

                    $debugTasks[] = $t['name'] . " (" . $statusName . ")";

                    if (!isset($taskCache[$pid][$tNameLower])) {
                        $taskCache[$pid][$tNameLower] = $t['id_string'];
                    } else if (strpos($statusName, 'open') !== false || strpos($statusName, 'buka') !== false || strpos($statusName, 'active') !== false) {
                        $taskCache[$pid][$tNameLower] = $t['id_string'];
                    } else if (strpos($statusName, 'cancel') === false && strpos($statusName, 'closed') === false) {
                        $taskCache[$pid][$tNameLower] = $t['id_string'];
                    }
                }
            };

            // Ambil semua tasks utama
            $taskRes = apiCall('/projects/' . $pid . '/tasks/');
            $debugTasks = [];
            
            if ($taskRes['code'] == 200 && isset($taskRes['data']['tasks'])) {
                $processTasks($taskRes['data']['tasks']);
                
                // Cari tahu apakah ada Subtasks tersembunyi dengan menembak API subtask
                // Untuk menghemat kuota API, kita hanya cek subtask pada task yang masih Aktif/Backlog (bukan cancel)
                foreach ($taskRes['data']['tasks'] as $t) {
                    $statusName = isset($t['status']['name']) ? strtolower($t['status']['name']) : '';
                    if (strpos($statusName, 'cancel') === false && strpos($statusName, 'closed') === false) {
                        // Tembak API subtasks
                        $subRes = apiCall('/projects/' . $pid . '/tasks/' . $t['id_string'] . '/subtasks/');
                        if ($subRes['code'] == 200 && isset($subRes['data']['tasks'])) {
                            $processTasks($subRes['data']['tasks']);
                        }
                    }
                }
                
                logMsg("Tasks & Subtasks found: " . implode(', ', $debugTasks), 'info');
            } else {
                logMsg("Failed to fetch tasks: " . json_encode($taskRes), 'error');
            }
        }

        // Parse task hierarchy (e.g., "Main Task > Subtask > Sub-subtask")
        $taskChain = array_map('trim', explode('>', $tName));
        $tid = null;
        $parentId = null;
        
        foreach ($taskChain as $index => $chainName) {
            $chainNameLower = strtolower($chainName);
            
            if (isset($taskCache[$pid][$chainNameLower])) {
                $tid = $taskCache[$pid][$chainNameLower];
                $parentId = $tid;
                logMsg("Found existing task in chain: {$chainName}", 'success');
            } else {
                logMsg("Task '{$chainName}' not found. Creating...", 'warning');
                
                $createPayload = ['name' => $chainName];
                $createUrl = '/projects/' . $pid . '/tasks/';
                
                // If this is a subtask (we have a parent ID), use the subtasks endpoint
                if ($parentId !== null) {
                    $createUrl = '/projects/' . $pid . '/tasks/' . $parentId . '/subtasks/';
                }
                
                $createRes = apiCall($createUrl, 'POST', $createPayload);
                if ($createRes['code'] == 201 && isset($createRes['data']['tasks'][0])) {
                    $tid = $createRes['data']['tasks'][0]['id_string'];
                    $taskCache[$pid][$chainNameLower] = $tid;
                    $parentId = $tid; // This new task becomes the parent for the next one in chain
                    logMsg("Task '{$chainName}' created successfully.", 'success');
                } else {
                    logMsg("Failed to create task '{$chainName}': " . json_encode($createRes), 'error');
                    $tid = null;
                    break;
                }
            }
        }

        if (!$tid) {
            $syncSuccess = false;
            updateRowInSheet($log['rowIndex'], $log, 'pending', '');
            continue;
        }

        $hoursStr = '';
        if (!empty($log['duration'])) {
            $hoursStr = $log['duration'];
        } else {
            $st = strtotime($log['startDate'] . ' ' . $log['startTime']);
            $et = strtotime($log['startDate'] . ' ' . $log['endTime']);
            if ($et < $st) {
                $et += 86400; // Next day
            }
            $diffMinutes = round(($et - $st) / 60);
            $h = floor($diffMinutes / 60);
            $m = $diffMinutes % 60;
            $hoursStr = sprintf("%02d:%02d", $h, $m);
        }

        $rawDate = trim($log['startDate']);
        $zohoDate = date('m-d-Y'); // fallback
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $rawDate, $m)) {
            $zohoDate = $m[2] . '-' . $m[3] . '-' . $m[1];
        } else {
            $parts = preg_split('/[- \/]/', $rawDate);
            if (count($parts) === 3) {
                $y = $parts[2];
                if (strlen($y) == 2) $y = '20' . $y;
                if (strlen($parts[0]) == 4) {
                    $zohoDate = str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[2], 2, '0', STR_PAD_LEFT) . '-' . $parts[0];
                } else {
                    $zohoDate = str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . $y;
                }
            }
        }
        
        $logPayload = [
            'date' => $zohoDate,
            'hours' => $hoursStr,
            'notes' => $log['notes'],
            'bill_status' => 'Billable'
        ];
        
        // Parsing jam masuk/keluar ke format 12-hour (AM/PM) sesuai standar Zoho
        $startTimeStr = '';
        if (!empty($log['startTime'])) {
            $stTime = strtotime($log['startTime']);
            if ($stTime !== false) $startTimeStr = date('h:i A', $stTime); // e.g., "07:00 AM"
        }
        $endTimeStr = '';
        if (!empty($log['endTime'])) {
            $etTime = strtotime($log['endTime']);
            if ($etTime !== false) $endTimeStr = date('h:i A', $etTime); // e.g., "05:00 PM"
        }
        
        if ($startTimeStr && $endTimeStr) {
            $logPayload['start_time'] = $startTimeStr;
            $logPayload['end_time'] = $endTimeStr;
        }
        
        $logRes = apiCall('/projects/' . $pid . '/tasks/' . $tid . '/logs/', 'POST', $logPayload);
        
        if ($logRes['code'] == 201 || $logRes['code'] == 200) {
            logMsg("Time logged successfully ($hoursStr).", 'success');
            // Format URL baru sesuai dengan struktur portal Zoho Projects
            $taskUrl = "https://projects.zoho.com/portal/{$portal}#zp/projects/{$pid}/tasks/task-detail/{$tid}";
            logMsg("Link URL: " . $taskUrl, 'info'); // Tampilkan URL di console
            updateRowInSheet($log['rowIndex'], $log, 'done', $taskUrl);
        } else {
            logMsg("Failed to log time: " . json_encode($logRes['data']), 'error');
            $syncSuccess = false;
            updateRowInSheet($log['rowIndex'], $log, 'pending', '');
        }
    }

    if ($syncSuccess) {
        logMsg('Sync completed successfully!', 'success');
    } else {
        logMsg('Sync finished with some errors.', 'warning');
    }

    echo json_encode(['success' => $syncSuccess, 'logs' => $outputLogs]);
    exit;
}

echo json_encode(['error' => 'Invalid action']);
