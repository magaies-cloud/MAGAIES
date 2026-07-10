<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
header("Content-Type: application/json; charset=UTF-8");
$allowed_origins = ['https://elsajana.com', 'https://c.elsajana.com', 'http://localhost:8080'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . "/api/db.php";
require_once __DIR__ . "/api/services/EmailService.php";
require_once __DIR__ . "/api/controllers/AuthController.php";

// -------------------------------------------------------
// Utility: Send JSON response and exit
// -------------------------------------------------------
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function is_disposable_email($email) {
    $domain = substr(strrchr($email, "@"), 1);
    $blacklisted = ['10minutemail.com', 'mailinator.com', 'tempmail.com', 'guerrillamail.com', 'yopmail.com', 'throwawaymail.com', 'trashmail.com'];
    return in_array(strtolower($domain), $blacklisted);
}

function normalizePhoneNumber($phone) {
    // Strip all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Strip leading 00
    if (strpos($phone, '00') === 0) {
        $phone = substr($phone, 2);
    }
    
    // Check if it starts with 0
    if (strpos($phone, '0') === 0) {
        if (strpos($phone, '09') === 0) {
            // Sudan (e.g. 0912300105 -> 249912300105)
            $phone = '249' . substr($phone, 1);
        } elseif (strpos($phone, '05') === 0) {
            // Saudi Arabia (e.g. 0501234567 -> 966501234567)
            $phone = '966' . substr($phone, 1);
        } elseif (strpos($phone, '01') === 0) {
            // Egypt (e.g. 01012345678 -> 201012345678)
            $phone = '20' . substr($phone, 1);
        }
    } else {
        // No leading 0. Check if they omitted country code
        if (strpos($phone, '9') === 0 && strlen($phone) === 9) {
            // Sudan without leading 0 (e.g. 912300105 -> 249912300105)
            $phone = '249' . $phone;
        } elseif (strpos($phone, '5') === 0 && strlen($phone) === 9) {
            // Saudi Arabia without leading 0 (e.g. 501234567 -> 966501234567)
            $phone = '966' . $phone;
        }
    }
    
    // Strip redundant leading 0 after country code if any
    if (strpos($phone, '2490') === 0) {
        $phone = '249' . substr($phone, 4);
    } elseif (strpos($phone, '9660') === 0) {
        $phone = '966' . substr($phone, 4);
    } elseif (strpos($phone, '200') === 0) {
        $phone = '20' . substr($phone, 3);
    }
    
    return $phone;
}

// -------------------------------------------------------
// Parse URL segments
// -------------------------------------------------------
$requestUri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$base_index = array_search('api_index.php', $requestUri);
if ($base_index !== false) {
    $resource = isset($requestUri[$base_index + 1]) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $requestUri[$base_index + 1]) : null;
    $id       = isset($requestUri[$base_index + 2]) ? $requestUri[$base_index + 2] : null;
    $action   = isset($requestUri[$base_index + 2]) ? $requestUri[$base_index + 2] : null;
} else {
    // Direct rewrite support
    $resource = isset($requestUri[0]) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $requestUri[0]) : null;
    $id       = isset($requestUri[1]) ? $requestUri[1] : null;
    $action   = isset($requestUri[1]) ? $requestUri[1] : null;
}

// Fallback: support query param routing (?r=resource&a=action&a2=sub-action)
// This is used when the server doesn't pass PATH_INFO segments after .php
if (empty($resource) && isset($_GET['r'])) {
    $resource = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['r'] ?? '');
    $action   = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['a'] ?? '');
    // Rebuild requestUri so sub-action checks like $requestUri[$base_index+3] work
    $base_index = 0;
    $requestUri = ['api_index.php', $resource, $action];
    if (isset($_GET['a2'])) {
        $requestUri[] = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['a2']);
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents("php://input");
$input  = json_decode($rawInput, true);

// -------------------------------------------------------
// Utility: Generate secure token
// -------------------------------------------------------
function generateToken() {
    return bin2hex(random_bytes(32));
}

// ---------------------------------------------------------
// PUBLIC ROUTES (No token required)
// ---------------------------------------------------------

if ($resource === 'public' && empty($action) && $method === 'GET') {
    $data = ['plans' => [], 'partners' => [], 'ads' => [], 'partners_enabled' => true];
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('saas_plans', 'saas_partners', 'saas_ads', 'saas_partners_enabled')");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if ($row['setting_key'] === 'saas_plans') $data['plans'] = json_decode($row['setting_value'], true);
            if ($row['setting_key'] === 'saas_partners') $data['partners'] = json_decode($row['setting_value'], true);
            if ($row['setting_key'] === 'saas_ads') $data['ads'] = json_decode($row['setting_value'], true);
            if ($row['setting_key'] === 'saas_partners_enabled') $data['partners_enabled'] = ($row['setting_value'] === '1');
        }
    } catch (Exception $e) {}
    
    // Add default plans if empty
    if (empty($data['plans'])) {
        $data['plans'] = [
            ['id' => 1, 'name' => 'باقة الأساسية', 'price' => 50, 'maxUsers' => 5, 'storage' => 10],
            ['id' => 2, 'name' => 'باقة المتقدمة', 'price' => 120, 'maxUsers' => 20, 'storage' => 50],
            ['id' => 3, 'name' => 'باقة الشركات (VIP)', 'price' => 300, 'maxUsers' => 100, 'storage' => 200]
        ];
    }
    // Add default partners if empty
    if (empty($data['partners'])) {
        $data['partners'] = [
            ['id' => 1, 'name' => 'مقاييس', 'logo' => 'https://ui-avatars.com/api/?name=%D9%85%D9%82%D8%A7%D9%8A%D9%8A%D8%B3&background=38bdf8&color=fff&size=100', 'link' => '#'],
            ['id' => 2, 'name' => 'مصنع الأسعد', 'logo' => 'https://ui-avatars.com/api/?name=%D9%85%D8%B5%D9%86%D8%B9+%D8%A7%D9%84%D8%A3%D8%B3%D8%B9%D8%AF&background=818cf8&color=fff&size=100', 'link' => '#'],
            ['id' => 3, 'name' => 'أوميغا', 'logo' => 'https://ui-avatars.com/api/?name=%D8%A3%D9%88%D9%85%D9%8A%D8%BA%D8%A7&background=10b981&color=fff&size=100', 'link' => '#'],
            ['id' => 4, 'name' => 'بنك الخرطوم', 'logo' => 'https://ui-avatars.com/api/?name=%D8%A8%D9%86%D9%83+%D8%A7%D9%84%D8%AE%D8%B1%D8%B7%D9%88%D9%85&background=f59e0b&color=fff&size=100', 'link' => '#'],
            ['id' => 5, 'name' => 'بنك المزارع', 'logo' => 'https://ui-avatars.com/api/?name=%D8%A8%D9%86%D9%83+%D8%A7%D9%84%D9%85%D8%B2%D8%A7%D8%B1%D8%B9&background=ef4444&color=fff&size=100', 'link' => '#']
        ];
    }
    jsonResponse($data);
}

// TEMP DEBUG: ?r=debug&token=YOUR_TOKEN
if ($resource === 'debug' && $method === 'GET') {
    $reqToken = $_GET['token'] ?? '';
    $saasDbgSecret = defined('SAAS_ADMIN_TOKEN') ? SAAS_ADMIN_TOKEN : getenv('SAAS_ADMIN_TOKEN');
    if (!$saasDbgSecret) jsonResponse(['error' => 'Debug token not configured in .env'], 403);
    if ($reqToken !== $saasDbgSecret) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    $dbg = [
        'db_connected' => false,
        'tables' => [],
        'tenants_count' => 0,
        'tenants_sample' => [],
        'request_uri' => $_SERVER['REQUEST_URI'],
        'path_info' => $_SERVER['PATH_INFO'] ?? 'N/A',
        'env_db_host' => getenv('DB_HOST') ?: 'NOT SET',
        'env_db_name' => getenv('DB_NAME') ?: 'NOT SET',
        'token_match' => ($reqToken === $saasDbgSecret) ? 'YES' : 'NO',
    ];
    try {
        $dbg['db_connected'] = true;
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $dbg['tables'] = $tables;
        if (in_array('tenants', $tables)) {
            $dbg['tenants_count'] = (int)$pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
            $dbg['tenants_sample'] = $pdo->query("SELECT id, company_name, subdomain, owner_name, status, valid_until FROM tenants LIMIT 5")->fetchAll();
        } else {
            $dbg['tenants_table_exists'] = false;
        }
    } catch (Exception $e) {
        $dbg['db_error'] = $e->getMessage();
    }
    jsonResponse($dbg);
}

// Public: Get exchange rate (no auth needed so it always loads on the page)
if ($resource === 'exchange-rate' && $method === 'GET') {
    $ex_rate = ['rate' => 1.0, 'visible' => false];
    try {
        $stmt_ex = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'exchange_rate'");
        $val = $stmt_ex->fetchColumn();
        if ($val) $ex_rate = json_decode($val, true);
    } catch(Exception $e) {}
    jsonResponse($ex_rate);
}

// Public: Check Uniqueness Globally
if ($resource === 'public' && $action === 'check-unique' && $method === 'GET') {
    $field = $_GET['field'] ?? '';
    $value = $_GET['value'] ?? '';
    if (empty($field) || empty($value)) {
        jsonResponse(['success' => false, 'error' => 'Field and value required'], 400);
    }
    
    $exists = false;
    if ($field === 'username') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$value]);
        $exists = ($stmt->fetchColumn() > 0);
    } elseif ($field === 'subdomain') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE subdomain = ?");
        $stmt->execute([$value]);
        $exists = ($stmt->fetchColumn() > 0);
    } elseif ($field === 'email') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE owner_email = ?");
        $stmt->execute([$value]);
        $exists = ($stmt->fetchColumn() > 0);
    } elseif ($field === 'phone') {
        $normalized = normalizePhoneNumber($value);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE owner_phone = ? OR phone2 = ? OR owner_phone = ? OR phone2 = ?");
        $stmt->execute([$normalized, $normalized, $value, $value]);
        $exists = ($stmt->fetchColumn() > 0);
    } else {
        jsonResponse(['success' => false, 'error' => 'Invalid field'], 400);
    }
    
    jsonResponse(['success' => true, 'exists' => $exists]);
}

// Public: Send WhatsApp Inquiry to Support Number
if ($resource === 'public' && $action === 'send-inquiry' && $method === 'POST') {
    $msgText = $input['message'] ?? '';
    $companyName = $input['company_name'] ?? '';
    $ownerName = $input['owner_name'] ?? '';
    $ownerPhone = $input['owner_phone'] ?? '';
    
    if (empty($msgText)) {
        jsonResponse(['success' => false, 'error' => 'Message is required'], 400);
    }
    
    $supportNumber = '249113981477';
    
    $fullMessage = "🔔 *استفسار جديد من صفحة التسجيل* 🔔\n\n";
    $fullMessage .= "📝 *الرسالة:*\n" . $msgText . "\n\n";
    $fullMessage .= "🏢 *الشركة:* " . ($companyName ?: 'غير محدد') . "\n";
    $fullMessage .= "👤 *المالك:* " . ($ownerName ?: 'غير محدد') . "\n";
    $fullMessage .= "📞 *هاتف المالك:* " . ($ownerPhone ?: 'غير محدد') . "\n";
    
    $payload = json_encode([
        'userId' => 'default-saas-admin',
        'phone' => $supportNumber,
        'message' => $fullMessage
    ]);
    
    startWhatsAppServiceIfNeeded();
    
    $ch = curl_init('http://127.0.0.1:3001/wa/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        jsonResponse(['success' => false, 'error' => 'فشل إرسال الرسالة: ' . $err], 500);
    }
    
    $data = json_decode($response, true);
    if (!empty($data['success'])) {
        jsonResponse(['success' => true]);
    }
    jsonResponse(['success' => false, 'error' => $data['error'] ?? 'فشل إرسال الاستفسار'], 500);
}

function getWhatsAppNodeCommand() {
    $nodeCheck = shell_exec('which node 2>&1');
    if (!empty($nodeCheck) && strpos($nodeCheck, 'not found') === false && strpos($nodeCheck, 'no node') === false) {
        return 'node';
    }
    
    $nodejsCheck = shell_exec('which nodejs 2>&1');
    if (!empty($nodejsCheck) && strpos($nodejsCheck, 'not found') === false && strpos($nodejsCheck, 'no nodejs') === false) {
        return 'nodejs';
    }
    
    if (file_exists('/usr/bin/node')) return '/usr/bin/node';
    if (file_exists('/usr/bin/nodejs')) return '/usr/bin/nodejs';
    if (file_exists('/usr/local/bin/node')) return '/usr/local/bin/node';
    
    return 'node';
}

function startWhatsAppServiceIfNeeded() {
    $connection = @fsockopen('127.0.0.1', 3001, $errno, $errstr, 1);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    
    $cmd = getWhatsAppNodeCommand();
    // Use .mjs (ESM) file — required for Baileys v7 compatibility
    $serviceFile = file_exists(__DIR__ . '/whatsapp-service.mjs') 
        ? 'whatsapp-service.mjs' 
        : 'whatsapp-service.js';
    
    if (stristr(PHP_OS, 'WIN')) {
        pclose(popen("start /B node \"" . __DIR__ . "\\" . $serviceFile . "\" >> \"" . __DIR__ . "\\whatsapp-service.log\" 2>&1", "r"));
    } else {
        shell_exec("$cmd \"" . __DIR__ . "/" . $serviceFile . "\" >> \"" . __DIR__ . "/whatsapp-service.log\" 2>&1 &");
    }
    
    usleep(2500000);
    return false;
}

function sendWhatsAppOTP($phone, $otp) {
    startWhatsAppServiceIfNeeded();
    $message = "رمز التحقق (OTP) الخاص بك للتسجيل في السجّانة ERP هو: *" . $otp . "*\n\nينتهي صلاحية الرمز خلال 10 دقائق. يرجى عدم مشاركته مع أي شخص. 🌸";
    
    // Clean and normalize phone number
    $phone = normalizePhoneNumber($phone);
    
    $payload = json_encode([
        'userId' => 'default-saas-admin',
        'phone' => $phone,
        'message' => $message
    ]);
    
    $ch = curl_init('http://127.0.0.1:3001/wa/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        return ['success' => false, 'error' => $err];
    }
    
    $data = json_decode($response, true);
    if (!empty($data['success'])) {
        return ['success' => true];
    }
    return ['success' => false, 'error' => $data['error'] ?? 'فشل إرسال رسالة الواتساب'];
}

// Public: Send OTP
if ($resource === 'public' && $action === 'send-otp' && $method === 'POST') {
    $email = trim($input['email'] ?? '');
    $phone = normalizePhoneNumber(trim($input['phone'] ?? ''));
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'error' => 'البريد الإلكتروني غير صالح'], 400);
    }
    if (is_disposable_email($email)) {
        jsonResponse(['success' => false, 'error' => 'لا يُسمح باستخدام البريد المؤقت للتسجيل'], 400);
    }
    
    $otp = sprintf("%06d", random_int(1, 999999));
    
    $stmt = $pdo->prepare("INSERT INTO otp_verifications (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
    $stmt->execute([$email, $otp]);
    
    $channel = trim($input['channel'] ?? 'whatsapp');

    $waSuccess = false;
    $waError = '';
    if ($channel === 'whatsapp' && !empty($phone)) {
        $waResult = sendWhatsAppOTP($phone, $otp);
        if ($waResult['success']) {
            $waSuccess = true;
        } else {
            $waError = $waResult['error'];
        }
    }
    
    if ($waSuccess) {
        jsonResponse([
            'success' => true, 
            'message' => 'تم إرسال رمز التحقق (OTP) إلى حساب الواتساب الخاص بك بنجاح!'
        ]);
    } else {
        $result = EmailService::sendOTP($email, $otp);
        if ($result['success']) {
            $msg = 'تم إرسال رمز التحقق إلى بريدك الإلكتروني بنجاح!';
            if ($channel === 'whatsapp' && !empty($waError)) {
                $msg .= ' (فشل الإرسال عبر الواتساب: ' . $waError . ')';
            }
            jsonResponse(['success' => true, 'message' => $msg]);
        } else {
            jsonResponse(['success' => false, 'error' => $result['error']], 500);
        }
    }
}

// ---------------------------------------------------------
// AUTHENTICATION ROUTES (No token required)
// ---------------------------------------------------------
if ($resource === 'auth') {
    $authController = new AuthController($pdo);
    $authController->handleRequest($action, $method, $input);
    exit;
}

// ---------------------------------------------------------
// SECURE ROUTES (Require Token)
// ---------------------------------------------------------
$authHeader = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers    = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? '';
}

$token = '';
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
}

if (empty($token)) {
    jsonResponse(['error' => 'غير مصرح: الرمز مفقود'], 401);
}

$tenant_id = null;

// SAAS SUPER ADMIN BYPASS — رمز المشرف العام
$saasSecret = defined('SAAS_ADMIN_TOKEN') ? SAAS_ADMIN_TOKEN : getenv('SAAS_ADMIN_TOKEN');

if ($token === $saasSecret) {
    if ($resource === 'saas') {
        if ($action === 'wa-lock' || $action === 'wa-unlock') {
            $isLock = ($action === 'wa-lock');
            $file = __DIR__ . '/whatsapp-service.mjs';
            $output = [];
            $retCode = 0;
            
            if ($isLock) {
                @exec("git update-index --skip-worktree " . escapeshellarg($file) . " 2>&1", $output, $retCode);
                @chmod($file, 0444);
                jsonResponse([
                    'success' => true, 
                    'message' => 'تم قفل التحديثات بنجاح (محمي ضد أي تعديل أو رفع جديد).',
                    'git_output' => implode("\n", $output)
                ]);
            } else {
                @exec("git update-index --no-skip-worktree " . escapeshellarg($file) . " 2>&1", $output, $retCode);
                @chmod($file, 0644);
                jsonResponse([
                    'success' => true, 
                    'message' => 'تم فك القفل بنجاح (الملف جاهز لتلقي التحديثات البرمجية الجديدة).',
                    'git_output' => implode("\n", $output)
                ]);
            }
            exit;
        }

        if ($action === 'wa-proxy') {
            startWhatsAppServiceIfNeeded();
            $endpoint = $_GET['a2'] ?? 'status';
            $url = 'http://127.0.0.1:3001/wa/' . $endpoint . '?userId=default-saas-admin';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            
            if ($err) {
                jsonResponse(['success' => false, 'error' => 'فشل الاتصال بخادم الواتساب المحلي: ' . $err], 500);
            }
            
            header('Content-Type: application/json');
            http_response_code($httpCode);
            echo $response;
            exit;
        }

        require_once __DIR__ . '/api/controllers/TenantController.php';
        $tenantController = new TenantController($pdo);
        $tenantController->handleRequest($action, $method, $input, $requestUri, $base_index);
        exit;
    }
} else {
    // Validate normal user token and get tenant_id
    $stmt = $pdo->prepare("SELECT tenant_id, id AS user_id, token_expires_at FROM users WHERE token = ?");
    $stmt->execute([$token]);
    $session = $stmt->fetch();

    if (!$session) {
        jsonResponse(['error' => 'غير مصرح: الرمز غير صالح'], 401);
    }

    // Check token expiry (if column exists)
    if (!empty($session['token_expires_at']) && strtotime($session['token_expires_at']) < time()) {
        jsonResponse(['error' => 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مجدداً'], 401);
    }

    $tenant_id = $session['tenant_id'];
}

// ---------------------------------------------------------
// CLOUD BACKUP & RESTORE
// ---------------------------------------------------------
if ($resource === 'cloud-backup') {
    if ($method === 'POST') {
        $val = json_encode($input['backup'] ?? []);
        $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?")
            ->execute(['tenant_backup_' . $tenant_id, $val, $val]);
        jsonResponse(['success' => true]);
    }
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
        $stmt->execute(['tenant_backup_' . $tenant_id]);
        $val = $stmt->fetchColumn();
        jsonResponse(['success' => true, 'backup' => $val ? json_decode($val, true) : null]);
    }
}

// ---------------------------------------------------------
// PING (ONLINE USERS)
// ---------------------------------------------------------
if ($resource === 'ping' && $method === 'POST') {
    $pdo->prepare("UPDATE users SET last_active = NOW() WHERE token = ?")->execute([$token]);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = ? AND last_active >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)");
    $stmt->execute([$tenant_id]);
    $count = $stmt->fetchColumn();
    
    // Fetch global exchange rate
    $ex_rate = ['rate' => 1.0, 'visible' => false];
    try {
        $stmt_ex = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'exchange_rate'");
        $val = $stmt_ex->fetchColumn();
        if ($val) $ex_rate = json_decode($val, true);
    } catch(Exception $e) {}

    jsonResponse(['success' => true, 'online_count' => $count, 'exchange_rate' => $ex_rate]);
}

// ---------------------------------------------------------
// SYSTEM MESSAGES (BROADCAST)
// ---------------------------------------------------------
if ($resource === 'system' && $method === 'GET' && isset($requestUri[$base_index + 2]) && $requestUri[$base_index + 2] === 'messages' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'active') {
    // Get user id
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE token = ? LIMIT 1");
    $stmtUser->execute([$token]);
    $userId = $stmtUser->fetchColumn();

    if ($userId) {
        $stmt = $pdo->prepare("
            SELECT * FROM broadcast_messages 
            WHERE is_active = 1 
            AND (
                JSON_CONTAINS(target_tenants, '\"all\"') 
                OR JSON_CONTAINS(target_tenants, ?)
                OR JSON_CONTAINS(target_tenants, ?)
            )
            AND id NOT IN (
                SELECT message_id FROM user_message_views WHERE user_id = ?
            )
            ORDER BY created_at ASC
        ");
        $stmt->execute(['"' . $tenant_id . '"', intval($tenant_id), $userId]);
        jsonResponse($stmt->fetchAll());
    } else {
        jsonResponse([]);
    }
}

if ($resource === 'system' && $method === 'POST' && isset($requestUri[$base_index + 2]) && $requestUri[$base_index + 2] === 'messages' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'mark-read') {
    $msgId = intval($input['id'] ?? 0);
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE token = ? LIMIT 1");
    $stmtUser->execute([$token]);
    $userId = $stmtUser->fetchColumn();

    if ($userId && $msgId) {
        $pdo->prepare("INSERT IGNORE INTO user_message_views (user_id, message_id) VALUES (?, ?)")->execute([$userId, $msgId]);
        jsonResponse(['success' => true]);
    }
    jsonResponse(['error' => 'Invalid data'], 400);
}

// ---------------------------------------------------------
// ALLOWED TABLES
// ---------------------------------------------------------
$allowed_tables = [
    'users', 'customers', 'suppliers', 'items', 'inventory',
    'sales_invoices', 'purchase_invoices', 'receipts', 'payments',
    'journal_vouchers', 'settings', 'warehouses', 'banks', 'safes',
    'categories', 'roles',
    // Extended tables (now synced to cloud)
    'checks', 'gl_entries', 'single_receipts', 'single_payments',
    'partners', 'employees', 'payroll', 'reserved_sales',
    'delivery_notes', 'accounts', 'audit_logs', 'financial_periods'
];

// ---------------------------------------------------------
// SYNC ENGINE
// ---------------------------------------------------------
if ($resource === 'sync') {

    if ($method === 'GET' && $action === 'pull') {
        $data_payload = [];
        foreach ($allowed_tables as $table) {
            if ($table === 'settings') continue; // keep skipping settings if not implemented
            try {
                if ($table === 'users') {
                    // Find the actual admin role ID from roles table to prevent dropdown mismatch
                    $stmtRoles = $pdo->prepare("SELECT data FROM `roles` WHERE tenant_id = ?");
                    $stmtRoles->execute([$tenant_id]);
                    $roleRows = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);
                    $adminRoleId = 'role_admin_default';
                    foreach ($roleRows as $roleJson) {
                        $roleObj = json_decode($roleJson, true);
                        if ($roleObj && isset($roleObj['isSystem']) && $roleObj['isSystem'] === true) {
                            $adminRoleId = $roleObj['id'];
                            break;
                        }
                    }

                    $stmt = $pdo->prepare("SELECT id, username, data, role, status FROM `users` WHERE tenant_id = ?");
                    $stmt->execute([$tenant_id]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $parsed_rows = [];
                    foreach ($rows as $r) {
                        if ($r['data']) {
                            $parsed_rows[] = json_decode($r['data'], true);
                        } else {
                            $parsed_rows[] = [
                                'id' => 'u_admin_default',
                                'username' => $r['username'],
                                'password' => '',
                                'fullName' => 'مدير النظام (' . $r['username'] . ')',
                                'roleId' => $adminRoleId,
                                'warehouse' => 'all',
                                'status' => $r['status'] ?? 'active',
                                'isSystem' => ($r['role'] === 'admin')
                            ];
                        }
                    }
                } else {
                    $stmt = $pdo->prepare("SELECT data FROM `$table` WHERE tenant_id = ?");
                    $stmt->execute([$tenant_id]);
                    $rows        = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $parsed_rows = array_map(function ($r) { return json_decode($r, true); }, $rows);
                }
                
                // Map table names back to frontend keys
                $frontendKey = $table;
                if ($table === 'gl_entries') $frontendKey = 'gl';
                if ($table === 'sales_invoices') $frontendKey = 'sales_invoices';
                if ($table === 'purchase_invoices') $frontendKey = 'purchase_invoices';
                $key = "erp_" . $frontendKey . "_v1";
                
                $data_payload[$key] = $parsed_rows;
            } catch (PDOException $e) {
                // Skip if table doesn't exist or doesn't have 'data' column
                continue;
            }
        }
        $stmtTenant = $pdo->prepare("SELECT t.max_users, t.plan_id, p.setting_value as plans_json FROM tenants t LEFT JOIN global_settings p ON p.setting_key = 'saas_plans' WHERE t.id = ?");
        $stmtTenant->execute([$tenant_id]);
        $tData = $stmtTenant->fetch(PDO::FETCH_ASSOC);
        $finalMaxUsers = 5;
        if ($tData) {
            $planLimit = 5;
            $plans = json_decode($tData['plans_json'] ?? '[]', true);
            foreach ($plans as $p) {
                if ($p['id'] == $tData['plan_id']) {
                    $planLimit = (int)($p['maxUsers'] ?? 5);
                    break;
                }
            }
            $finalMaxUsers = ($tData['max_users'] !== null) ? (int)$tData['max_users'] : $planLimit;
        }
        jsonResponse(['success' => true, 'data' => $data_payload, 'max_users' => $finalMaxUsers]);
    }

    if ($method === 'POST' && $action === 'push') {
        if (!$input) {
            jsonResponse(['error' => 'بيانات JSON غير صالحة'], 400);
        }

        // ---------------------------------------------------------
        // STORAGE LIMIT ENFORCEMENT
        // ---------------------------------------------------------
        if ($tenant_id) {
            $stmtTenant = $pdo->prepare("SELECT t.plan_id, t.max_storage, p.setting_value as plans_json FROM tenants t LEFT JOIN global_settings p ON p.setting_key = 'saas_plans' WHERE t.id = ?");
            $stmtTenant->execute([$tenant_id]);
            $tenantData = $stmtTenant->fetch(PDO::FETCH_ASSOC);
            if ($tenantData) {
                $planId = $tenantData['plan_id'] ?: 1;
                $plans = json_decode($tenantData['plans_json'] ?? '[]', true);
                $plan = null;
                foreach ($plans as $p) {
                    if ($p['id'] == $planId) {
                        $plan = $p;
                        break;
                    }
                }
                if (!$plan) {
                    $defaultPlans = [
                        1 => ['maxUsers' => 5, 'storage' => 10],
                        2 => ['maxUsers' => 20, 'storage' => 50],
                        3 => ['maxUsers' => 100, 'storage' => 200]
                    ];
                    $plan = $defaultPlans[$planId] ?? ['maxUsers' => 5, 'storage' => 10];
                }
                
                $allowedStorageGB = ($tenantData['max_storage'] !== null) ? (int)$tenantData['max_storage'] : ($plan['storage'] ?? 10);
                $allowedStorageBytes = $allowedStorageGB * 1024 * 1024 * 1024;
                
                // Calculate current size
                $unionQueries = [];
                foreach ($allowed_tables as $t) {
                    // skip tables that don't have tenant_id
                    if (in_array($t, ['settings', 'global_settings'])) continue;
                    $unionQueries[] = "SELECT IFNULL(SUM(LENGTH(data)), 0) as sz FROM `$t` WHERE tenant_id = " . intval($tenant_id);
                }
                $sql = "SELECT SUM(sz) as total_size FROM (" . implode(" UNION ALL ", $unionQueries) . ") as all_sizes";
                try {
                    $sizeStmt = $pdo->query($sql);
                    $currentSize = (int)$sizeStmt->fetchColumn();
                } catch (Exception $e) {
                    $currentSize = 0;
                }
                
                if ($currentSize > $allowedStorageBytes) {
                    jsonResponse(['error' => 'لقد تجاوزت المساحة المتاحة لباقة اشتراكك (' . $allowedStorageGB . ' جيجابايت). يرجى التواصل مع الإدارة لترقية باقة الاشتراك.'], 403);
                }
            }
        }

        $pdo->beginTransaction();
        try {
            foreach ($input as $key => $records) {
                if (preg_match('/^erp_(.+)_v1$/', $key, $matches)) {
                    $table = $matches[1];
                    // Map frontend keys back to database tables
                    if ($table === 'gl') $table = 'gl_entries';
                    if ($table === 'sales') $table = 'sales_invoices';
                    if ($table === 'purchases') $table = 'purchase_invoices';
                    
                    if (in_array($table, $allowed_tables) && $table !== 'settings' && is_array($records)) {
                        if (!empty($records)) {
                            try {
                                if ($table === 'users') {
                                    // ---------------------------------------------------------
                                    // USER LIMIT ENFORCEMENT
                                    // ---------------------------------------------------------
                                    $allowedUsersLimit = 5;
                                    $stmtTenant = $pdo->prepare("SELECT t.max_users, t.plan_id, p.setting_value as plans_json FROM tenants t LEFT JOIN global_settings p ON p.setting_key = 'saas_plans' WHERE t.id = ?");
                                    $stmtTenant->execute([$tenant_id]);
                                    $tData = $stmtTenant->fetch(PDO::FETCH_ASSOC);
                                    if ($tData) {
                                        $planLimit = 5;
                                        $plans = json_decode($tData['plans_json'] ?? '[]', true);
                                        foreach ($plans as $p) {
                                            if ($p['id'] == $tData['plan_id']) {
                                                $planLimit = (int)($p['maxUsers'] ?? 5);
                                                break;
                                            }
                                        }
                                        $allowedUsersLimit = ($tData['max_users'] !== null) ? (int)$tData['max_users'] : $planLimit;
                                    }

                                    // Count active users in incoming push records
                                    $incomingActiveUsers = 0;
                                    foreach ($records as $record) {
                                        if (empty($record['username'])) continue;
                                        $status = $record['status'] ?? 'active';
                                        if ($status !== 'inactive') {
                                            $incomingActiveUsers++;
                                        }
                                    }

                                    if ($incomingActiveUsers > $allowedUsersLimit) {
                                        jsonResponse(['error' => 'لقد تجاوزت الحد الأقصى للمستخدمين المتاح لشركتك وهو (' . $allowedUsersLimit . ') مستخدمين. يرجى ترقية الاشتراك.'], 403);
                                    }

                                    $stmtCheck = $pdo->prepare("SELECT id, username, password FROM `users` WHERE tenant_id = ?");
                                    $stmtCheck->execute([$tenant_id]);
                                    $existingUsers = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);
                                    $userMap = [];
                                    foreach ($existingUsers as $eu) {
                                        $userMap[$eu['username']] = $eu['id'];
                                    }
                                    
                                    $usersToUpdatePass = [];
                                    $usersToUpdateNoPass = [];
                                    $usersToInsert = [];
                                    
                                    foreach ($records as $record) {
                                        if (empty($record['username'])) continue;
                                        $username = strtolower(trim($record['username']));
                                        $password = $record['password'] ?? '';
                                        $status = $record['status'] ?? 'active';
                                        
                                        if ($username === 'admin') continue;
                                        
                                        $recordData = $record;
                                        unset($recordData['password']);
                                        $jsonData = json_encode($recordData, JSON_UNESCAPED_UNICODE);
                                        
                                        if (isset($userMap[$username])) {
                                            $uId = $userMap[$username];
                                            if (!empty($password)) {
                                                $usersToUpdatePass[$uId] = [password_hash($password, PASSWORD_DEFAULT), $jsonData, $status];
                                            } else {
                                                $usersToUpdateNoPass[$uId] = [$jsonData, $status];
                                            }
                                        } else {
                                            $usersToInsert[] = [$tenant_id, $username, password_hash($password, PASSWORD_DEFAULT), 'employee', $status, $jsonData];
                                        }
                                    }
                                    
                                    if (!empty($usersToUpdatePass)) {
                                        $sql = "UPDATE `users` SET password = CASE id ";
                                        $sqlData = "data = CASE id ";
                                        $sqlStatus = "status = CASE id ";
                                        $ids = [];
                                        foreach ($usersToUpdatePass as $uId => $vals) {
                                            $sql .= "WHEN $uId THEN " . $pdo->quote($vals[0]) . " ";
                                            $sqlData .= "WHEN $uId THEN " . $pdo->quote($vals[1]) . " ";
                                            $sqlStatus .= "WHEN $uId THEN " . $pdo->quote($vals[2]) . " ";
                                            $ids[] = $uId;
                                        }
                                        $sql .= "END, " . $sqlData . "END, " . $sqlStatus . "END WHERE id IN (" . implode(',', $ids) . ")";
                                        $pdo->exec($sql);
                                    }
                                    
                                    if (!empty($usersToUpdateNoPass)) {
                                        $sqlData = "UPDATE `users` SET data = CASE id ";
                                        $sqlStatus = "status = CASE id ";
                                        $ids = [];
                                        foreach ($usersToUpdateNoPass as $uId => $vals) {
                                            $sqlData .= "WHEN $uId THEN " . $pdo->quote($vals[0]) . " ";
                                            $sqlStatus .= "WHEN $uId THEN " . $pdo->quote($vals[1]) . " ";
                                            $ids[] = $uId;
                                        }
                                        $sqlData .= "END, " . $sqlStatus . "END WHERE id IN (" . implode(',', $ids) . ")";
                                        $pdo->exec($sqlData);
                                    }
                                    
                                    if (!empty($usersToInsert)) {
                                        $sql = "INSERT INTO `users` (tenant_id, username, password, role, status, data) VALUES ";
                                        $vals = [];
                                        foreach ($usersToInsert as $u) {
                                            $vals[] = "(" . (int)$u[0] . ", " . $pdo->quote($u[1]) . ", " . $pdo->quote($u[2]) . ", " . $pdo->quote($u[3]) . ", " . $pdo->quote($u[4]) . ", " . $pdo->quote($u[5]) . ")";
                                        }
                                        $pdo->exec($sql . implode(', ', $vals));
                                    }
                                } else {
                                    $isFin = in_array($table, ['sales_invoices', 'purchase_invoices', 'gl_entries', 'receipts', 'payments', 'journal_vouchers', 'single_receipts', 'single_payments']);
                                    if ($isFin) {
                                        $stmtAll = $pdo->prepare("SELECT id, status_val, updated_at, COALESCE(JSON_EXTRACT(data, '$.id'), JSON_EXTRACT(data, '$.invoiceNo'), JSON_EXTRACT(data, '$.receiptNo'), JSON_EXTRACT(data, '$.paymentNo'), JSON_EXTRACT(data, '$.jvNo')) as doc_id FROM `$table` WHERE tenant_id = ?");
                                    } else {
                                        $stmtAll = $pdo->prepare("SELECT id, COALESCE(JSON_EXTRACT(data, '$.id'), JSON_EXTRACT(data, '$.invoiceNo'), JSON_EXTRACT(data, '$.receiptNo'), JSON_EXTRACT(data, '$.paymentNo'), JSON_EXTRACT(data, '$.jvNo')) as doc_id FROM `$table` WHERE tenant_id = ?");
                                    }
                                    $stmtAll->execute([$tenant_id]);
                                    $allRows = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    $existingMap = [];
                                    foreach ($allRows as $row) {
                                        if ($row['doc_id'] !== null) {
                                            $docId = trim($row['doc_id'], '"');
                                            $existingMap[$docId] = $row;
                                        }
                                    }

                                    $closedPeriods = [];
                                    if ($isFin) {
                                        $stmtP = $pdo->prepare("SELECT start_date, end_date FROM financial_periods WHERE tenant_id = ? AND is_closed = 1");
                                        $stmtP->execute([$tenant_id]);
                                        $closedPeriods = $stmtP->fetchAll(PDO::FETCH_ASSOC);
                                    }

                                    $updateSql = "UPDATE `$table` SET data = CASE id ";
                                    $idsToUpdate = [];
                                    $insertValues = [];

                                    foreach ($records as $record) {
                                        // Unified identifier extraction
                                        $recordId = $record['id'] ?? $record['invoiceNo'] ?? $record['receiptNo'] ?? $record['paymentNo'] ?? $record['jvNo'] ?? null;
                                        if (!$recordId) continue;
                                        
                                        // Ensure the JSON always has an 'id' for consistent querying next time
                                        if (!isset($record['id'])) {
                                            $record['id'] = $recordId;
                                        }

                                        // Inject user attribution for accountability
                                        if (!isset($record['createdBy']) && $userId) {
                                            $record['createdBy'] = $userId;
                                        }

                                        $strRecordId = (string)$recordId;
                                        
                                        // Financial rules checks
                                        if (isset($isFin) && $isFin) {
                                            // 1. Fiscal Period Check
                                            $recDateStr = $record['date'] ?? $record['invoiceDate'] ?? $record['receiptDate'] ?? $record['paymentDate'] ?? null;
                                            if ($recDateStr) {
                                                $recDate = substr($recDateStr, 0, 10);
                                                foreach ($closedPeriods as $cp) {
                                                    if ($recDate >= $cp['start_date'] && $recDate <= $cp['end_date']) {
                                                        continue 2; // SKIP processing this record entirely
                                                    }
                                                }
                                            }
                                            
                                            // 2. Immutability & Optimistic Lock Check for existing
                                            if (isset($existingMap[$strRecordId])) {
                                                $dbRow = $existingMap[$strRecordId];
                                                if ($dbRow['status_val'] && in_array(strtolower($dbRow['status_val']), ['posted', 'paid'])) {
                                                    continue; // SKIP modifying posted/paid docs
                                                }
                                            }
                                        }

                                        $jsonData = json_encode($record, JSON_UNESCAPED_UNICODE);

                                        if (isset($existingMap[$strRecordId])) {
                                            $uId = $existingMap[$strRecordId]['id'];
                                            $updateSql .= "WHEN $uId THEN " . $pdo->quote($jsonData) . " ";
                                            $idsToUpdate[] = $uId;
                                        } else {
                                            $insertValues[] = "($tenant_id, " . $pdo->quote($jsonData) . ")";
                                        }
                                    }
                                    
                                    if (!empty($idsToUpdate)) {
                                        $updateSql .= "END WHERE id IN (" . implode(',', $idsToUpdate) . ")";
                                        $pdo->exec($updateSql);
                                    }
                                    if (!empty($insertValues)) {
                                        $pdo->exec("INSERT INTO `$table` (tenant_id, data) VALUES " . implode(", ", $insertValues));
                                    }
                                }
                            } catch (PDOException $e) {
                                // Skip table if it doesn't exist or schema is wrong
                                continue;
                            }
                        }
                    }
                }
            }
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => 'تمت المزامنة بنجاح']);
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Sync push error: ' . $e->getMessage());
            jsonResponse(['error' => 'فشلت المزامنة، يرجى المحاولة لاحقاً'], 500);
        }
    }

    jsonResponse(['error' => 'مسار المزامنة غير موجود'], 404);
}

// ---------------------------------------------------------
// GENERIC CRUD
// ---------------------------------------------------------
if (!in_array($resource, $allowed_tables)) {
    jsonResponse(['error' => 'المورد غير موجود أو غير مسموح به'], 404);
}

try {
    function checkFinancialRules($pdo, $tenant_id, $resource, $input, $existingRow = null) {
        $isFin = in_array($resource, ['sales_invoices', 'purchase_invoices', 'gl_entries', 'receipts', 'payments', 'journal_vouchers', 'single_receipts', 'single_payments']);
        if (!$isFin) return;

        // 1. Fiscal Period Check
        if ($input) {
            $recDateStr = $input['date'] ?? $input['invoiceDate'] ?? $input['receiptDate'] ?? $input['paymentDate'] ?? null;
            if ($recDateStr) {
                $recDate = substr($recDateStr, 0, 10);
                $stmtP = $pdo->prepare("SELECT start_date, end_date FROM financial_periods WHERE tenant_id = ? AND is_closed = 1");
                $stmtP->execute([$tenant_id]);
                $closedPeriods = $stmtP->fetchAll(PDO::FETCH_ASSOC);
                foreach ($closedPeriods as $cp) {
                    if ($recDate >= $cp['start_date'] && $recDate <= $cp['end_date']) {
                        jsonResponse(["error" => "لا يمكن التعديل: التاريخ يقع في فترة مالية مغلقة"], 403);
                    }
                }
            }
        }

        // 2. Immutability Check
        if ($existingRow && isset($existingRow['status_val']) && $existingRow['status_val']) {
            if (in_array(strtolower($existingRow['status_val']), ['posted', 'paid'])) {
                jsonResponse(["error" => "لا يمكن تعديل أو حذف مستند مرحّل أو مدفوع"], 403);
            }
        }
    }

    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM `$resource` WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
                $stmt->execute(['tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
                $result = $stmt->fetch();
            } else {
                $stmt = $pdo->prepare("SELECT * FROM `$resource` WHERE tenant_id = :tid ORDER BY created_at DESC");
                $stmt->execute(['tid' => $tenant_id]);
                $result = $stmt->fetchAll();
            }
            jsonResponse($result ?: []);
            break;

        case 'POST':
            checkFinancialRules($pdo, $tenant_id, $resource, $input);
            
            if (!isset($input['createdBy']) && $userId) {
                $input['createdBy'] = $userId;
            }
            $dataJson = json_encode($input, JSON_UNESCAPED_UNICODE);
            $stmt     = $pdo->prepare("INSERT INTO `$resource` (tenant_id, data) VALUES (:tid, :data)");
            $stmt->execute(['tid' => $tenant_id, 'data' => $dataJson]);
            jsonResponse(["success" => true, "id" => $pdo->lastInsertId(), "data" => $input]);
            break;

        case 'PUT':
            if (!$id) jsonResponse(["error" => "معرّف السجل مطلوب للتعديل"], 400);

            // Fetch existing for immutability check
            $stmtEx = $pdo->prepare("SELECT status_val FROM `$resource` WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
            $stmtEx->execute(['tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
            $existingRow = $stmtEx->fetch(PDO::FETCH_ASSOC) ?: null;
            checkFinancialRules($pdo, $tenant_id, $resource, $input, $existingRow);

            if (!isset($input['createdBy']) && $userId) {
                $input['createdBy'] = $userId;
            }
            $dataJson = json_encode($input, JSON_UNESCAPED_UNICODE);
            $stmt     = $pdo->prepare("UPDATE `$resource` SET data = :data WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
            $stmt->execute(['data' => $dataJson, 'tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
            jsonResponse(["success" => true, "id" => $id, "data" => $input]);
            break;

        case 'DELETE':
            if (!$id) jsonResponse(["error" => "معرّف السجل مطلوب للحذف"], 400);
            // Soft Delete: Inject "deleted": true into JSON
            $stmt = $pdo->prepare("SELECT data, status_val FROM `$resource` WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
            $stmt->execute(['tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                checkFinancialRules($pdo, $tenant_id, $resource, json_decode($row['data'], true), $row);
                $dataArr = json_decode($row['data'], true);
                $dataArr['deleted'] = true;
                $newData = json_encode($dataArr, JSON_UNESCAPED_UNICODE);
                $updateStmt = $pdo->prepare("UPDATE `$resource` SET data = :data WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
                $updateStmt->execute(['data' => $newData, 'tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
            }
            jsonResponse(["success" => true, "id" => $id]);
            break;

        default:
            jsonResponse(['error' => 'الطريقة غير مدعومة'], 405);
    }
} catch (\PDOException $e) {
    error_log('CRUD error on ' . $resource . ': ' . $e->getMessage());
    jsonResponse(["error" => "حدث خطأ في قاعدة البيانات"], 500);
}
