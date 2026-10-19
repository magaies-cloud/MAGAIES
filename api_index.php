<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
header("Content-Type: application/json; charset=UTF-8");
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin)) {
    $allowed = false;
    $exact_allowed = [
        'https://elsajana.com',
        'https://www.elsajana.com',
        'https://c.elsajana.com',
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:8080',
        'http://localhost:3000',
        'http://127.0.0.1:8080',
        'http://127.0.0.1:3000'
    ];
    if (in_array($origin, $exact_allowed)) {
        $allowed = true;
    } else {
        $parsed = parse_url($origin);
        if (isset($parsed['host'])) {
            $host = $parsed['host'];
            if ($host === 'elsajana.com' || (function_exists('str_ends_with') ? str_ends_with($host, '.elsajana.com') : substr($host, -13) === '.elsajana.com')) {
                $allowed = true;
            }
        }
    }
    if ($allowed) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
    }
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Tenant-Subdomain, Accept, X-Requested-With");
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
    if (is_array($data) && isset($data['error']) && !isset($data['success'])) {
        $data['success'] = false;
    }
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
    $phone = preg_replace('/[^0-9]/', '', (string)$phone);
    
    // Strip leading 00
    if (strpos($phone, '00') === 0) {
        $phone = substr($phone, 2);
    }
    
    // Check if it starts with 0
    if (strpos($phone, '0') === 0) {
        if (strpos($phone, '09') === 0) {
            // Sudan Zain/MTN (e.g. 0912300105 -> 249912300105)
            $phone = '249' . substr($phone, 1);
        } elseif (strpos($phone, '01') === 0 && strlen($phone) === 10) {
            // Sudan Sudani/Canar 10-digit (e.g. 0123065893 / 0115637300 -> 249123065893)
            $phone = '249' . substr($phone, 1);
        } elseif (strpos($phone, '05') === 0) {
            // Saudi Arabia (e.g. 0501234567 -> 966501234567)
            $phone = '966' . substr($phone, 1);
        } elseif (strpos($phone, '01') === 0) {
            // Egypt 11-digit (e.g. 01012345678 -> 201012345678)
            $phone = '20' . substr($phone, 1);
        }
    } else {
        // No leading 0. Check if they omitted country code
        if ((strpos($phone, '9') === 0 || strpos($phone, '1') === 0) && strlen($phone) === 9) {
            // Sudan without leading 0 (e.g. 912300105 or 123065893 -> 249912300105)
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

// -------------------------------------------------------
// Utility: Detect if running on Local Shop Laptop vs Cloud Server (c.elsajana.com)
// -------------------------------------------------------
function isLocalLaptopEnvironment() {
    $host = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
    if (strpos($host, 'elsajana.com') !== false) {
        return false;
    }
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '192.168.') === 0 || strpos($host, '10.') === 0) {
        return true;
    }
    return (bool)stristr(PHP_OS, 'WIN');
}

// -------------------------------------------------------
// Utility: Proxy WhatsApp Requests from Local Laptop to 24/7 Cloud Hub (https://c.elsajana.com)
// -------------------------------------------------------
function migrateLocalBaileysSessionToCloud($sessionUserId = 'wa_tenant_12') {
    $safeUid = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$sessionUserId) ?: 'wa_tenant_12';
    $authDir = __DIR__ . '/baileys_auth_' . $safeUid;
    $credsFile = $authDir . '/creds.json';
    if (!is_dir($authDir) || !file_exists($credsFile)) {
        return false;
    }
    $credsJson = @json_decode(@file_get_contents($credsFile), true);
    if (empty($credsJson['me']['id'])) {
        return false;
    }

    // Throttle migration attempts to once every 300 seconds (5 minutes)
    $stampFile = sys_get_temp_dir() . '/magaies_wa_cloud_mig_' . $safeUid . '.stamp';
    if (file_exists($stampFile) && (time() - @filemtime($stampFile)) < 300 && empty($_GET['force_migrate'])) {
        return false;
    }
    @file_put_contents($stampFile, (string)time());

    $filesPayload = [];
    foreach (scandir($authDir) as $fname) {
        if ($fname === '.' || $fname === '..') continue;
        $fpath = $authDir . '/' . $fname;
        if (is_file($fpath)) {
            $filesPayload[$fname] = base64_encode(@file_get_contents($fpath));
        }
    }
    if (empty($filesPayload)) return false;

    // Upload in small chunks (30 files per request, ~120 KB) to stay well below Nginx's 1 MB body limit
    $chunks = array_chunk($filesPayload, 30, true);
    $totalChunks = count($chunks);
    $allOk = true;

    foreach ($chunks as $idx => $chunkFiles) {
        if ($totalChunks === 1) {
            $mode = 'single';
        } elseif ($idx === 0) {
            $mode = 'start';
        } elseif ($idx === $totalChunks - 1) {
            $mode = 'finish';
        } else {
            $mode = 'append';
        }

        $body = json_encode([
            'sessionUserId' => $safeUid,
            'mode' => $mode,
            'files' => $chunkFiles
        ]);

        $ch = curl_init('https://c.elsajana.com/api_index.php?r=wa-tenant&endpoint=import-session&local_only=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || empty($resp)) {
            $allOk = false;
            break;
        }
    }

    return $allOk;
}

function proxyWhatsAppRequestToCloud($endpoint, $queryParams, $method, $rawInput, $input) {
    $cloudQuery = $queryParams;
    $cloudQuery['r'] = 'wa-tenant';
    $cloudQuery['endpoint'] = $endpoint;
    $cloudQuery['local_only'] = '1'; // Instruct cloud server to execute on its own local 127.0.0.1:3001
    $cloudUrl = 'https://c.elsajana.com/api_index.php?' . http_build_query($cloudQuery);

    $ch = curl_init($cloudUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($_FILES)) {
            $postFields = $_POST;
            if (!isset($postFields['userId']) && !empty($queryParams['userId'])) {
                $postFields['userId'] = $queryParams['userId'];
            }
            foreach ($_FILES as $key => $file) {
                if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
                    $postFields[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        } elseif (!empty($_POST)) {
            $postFields = $_POST;
            if (!isset($postFields['userId']) && !empty($queryParams['userId'])) {
                $postFields['userId'] = $queryParams['userId'];
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, !empty($rawInput) ? $rawInput : json_encode($input ?: [], JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    // If cloud server is QR_READY / unlinked, check if local laptop has an already-linked Baileys session and migrate it automatically
    if (!$err && $httpCode === 200 && ($endpoint === 'status' || $endpoint === 'qr') && !empty($response)) {
        $dec = @json_decode($response, true);
        if (is_array($dec) && empty($dec['phoneNumber']) && ($dec['status'] ?? '') !== 'CONNECTED') {
            $targetUid = $queryParams['userId'] ?? 'wa_tenant_12';
            if (migrateLocalBaileysSessionToCloud($targetUid)) {
                usleep(1000000); // Wait 1s for cloud socket to connect after import
                $chRetry = curl_init($cloudUrl);
                curl_setopt($chRetry, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chRetry, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($chRetry, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($chRetry, CURLOPT_TIMEOUT, 6);
                curl_setopt($chRetry, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($chRetry, CURLOPT_SSL_VERIFYHOST, false);
                $retryResp = curl_exec($chRetry);
                $retryCode = (int)curl_getinfo($chRetry, CURLINFO_HTTP_CODE);
                curl_close($chRetry);
                if ($retryCode === 200 && !empty($retryResp)) {
                    $response = $retryResp;
                }
            }
        }
    }

    return [
        'ok' => (!$err && $httpCode >= 200 && $httpCode < 500 && !empty($response)),
        'httpCode' => $httpCode ?: 200,
        'response' => $response,
        'error' => $err
    ];
}

// ---------------------------------------------------------
// PUBLIC ROUTES (No token required)
// ---------------------------------------------------------

if ($resource === 'wa-tenant' || $resource === 'wa' || $action === 'wa-proxy') {
    $endpoint = ltrim($_GET['a2'] ?? $_GET['endpoint'] ?? $action ?? 'status', '/');
    if ($endpoint === 'wa-proxy' || $endpoint === 'wa-tenant' || $endpoint === 'wa') {
        $endpoint = 'status';
    }
    
    // Build query params for target NodeJS service
    $queryParams = $_GET;
    unset($queryParams['r'], $queryParams['a'], $queryParams['a2'], $queryParams['endpoint'], $queryParams['local_only']);
    
    // Resolve userId dynamically if provided via GET, POST, or JSON body
    if (empty($queryParams['userId'])) {
        if (!empty($_POST['userId'])) {
            $queryParams['userId'] = $_POST['userId'];
        } elseif (!empty($input['userId'])) {
            $queryParams['userId'] = $input['userId'];
        }
    }

    // 24/7 Cloud-First WhatsApp Hub: When running on the local laptop, route WhatsApp through https://c.elsajana.com
    // so the session stays permanently alive on the cloud server 24/7 even when the laptop is turned off!
    if (isLocalLaptopEnvironment() && empty($_GET['local_only'])) {
        $cloudRes = proxyWhatsAppRequestToCloud($endpoint, $queryParams, $method, $rawInput, $input);
        if ($cloudRes['ok']) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code($cloudRes['httpCode']);
            echo $cloudRes['response'];
            exit;
        }
        // If laptop is offline (cannot reach c.elsajana.com), do NOT spawn a competing local WhatsApp session.
        // Instead, signal the frontend to queue outbound messages for automatic dispatch on reconnect.
        if ($endpoint === 'send') {
            jsonResponse([
                'success' => false,
                'offline_queued' => true,
                'error' => 'اللابتوب غير متصل بالإنترنت حالياً — تم حفظ الرسالة في طابور الانتظار وسيتم إرسالها تلقائياً فور عودة الإنترنت'
            ], 200);
        }
        jsonResponse([
            'success' => false,
            'status' => 'OFFLINE_CLOUD',
            'cloudHub' => true,
            'error' => 'اللابتوب غير متصل بالإنترنت حالياً — خادم الواتساب السحابي (c.elsajana.com) مستمر في العمل 24/7 للعملاء'
        ], 200);
    }

    startWhatsAppServiceIfNeeded();
    
    $queryString = http_build_query($queryParams);
    $url = 'http://127.0.0.1:3001/wa/' . $endpoint . ($queryString ? '?' . $queryString : '');
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($_FILES)) {
            $postFields = $_POST;
            if (!isset($postFields['userId']) && !empty($queryParams['userId'])) {
                $postFields['userId'] = $queryParams['userId'];
            }
            foreach ($_FILES as $key => $file) {
                $postFields[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        } elseif (!empty($_POST)) {
            // Forward FormData text fields (e.g. userId, phone, message) as JSON to Node
            $postFields = $_POST;
            if (!isset($postFields['userId']) && !empty($queryParams['userId'])) {
                $postFields['userId'] = $queryParams['userId'];
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, !empty($rawInput) ? $rawInput : json_encode($input ?: []));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        jsonResponse(['success' => false, 'error' => 'فشل الاتصال بخادم الواتساب المحلي: ' . $err], 500);
    }
    
    header('Content-Type: application/json');
    http_response_code($httpCode ?: 200);
    echo $response;
    exit;
}

// ---------------------------------------------------------
// SMTP SETTINGS
// ---------------------------------------------------------
if ($resource === 'smtp_settings' && $method === 'POST') {
    $payload = $input['payload'] ?? null;
    if ($payload) {
        $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute(['smtp_server', $payload['smtpServer'] ?? '', $payload['smtpServer'] ?? '']);
        $stmt->execute(['smtp_port', $payload['smtpPort'] ?? '', $payload['smtpPort'] ?? '']);
        $stmt->execute(['smtp_user', $payload['smtpUser'] ?? '', $payload['smtpUser'] ?? '']);
        if (!empty($payload['smtpPass']) && $payload['smtpPass'] !== '********') {
            $stmt->execute(['smtp_pass', $payload['smtpPass'], $payload['smtpPass']]);
        }
        $stmt->execute(['backup_email', $payload['backupEmail'] ?? '', $payload['backupEmail'] ?? '']);
    }
    jsonResponse(['success' => true]);
}

// ---------------------------------------------------------
// EXCHANGE RATE (PUBLIC)
// ---------------------------------------------------------
if ($resource === 'exchange-rate' && $method === 'GET') {
    $resolvedRate = 5999.0;
    try {
        $stmt_ex = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('exchange_rate', 'erp_rate_usd', 'erp_currency_rates_v1')");
        $rows = $stmt_ex ? $stmt_ex->fetchAll(PDO::FETCH_KEY_PAIR) : [];
        if (!empty($rows['exchange_rate'])) {
            $val = trim((string)$rows['exchange_rate']);
            if (is_numeric($val) && (float)$val > 1) {
                $resolvedRate = (float)$val;
            } else {
                $dec = json_decode($val, true);
                if (is_array($dec)) {
                    $r = (float)($dec['rate'] ?? ($dec['current'] ?? ($dec['rateUSD'] ?? ($dec['USD'] ?? 0))));
                    if ($r > 1) $resolvedRate = $r;
                }
            }
        } elseif (!empty($rows['erp_rate_usd']) && is_numeric($rows['erp_rate_usd']) && (float)$rows['erp_rate_usd'] > 1) {
            $resolvedRate = (float)$rows['erp_rate_usd'];
        }
    } catch(Exception $e) {}
    jsonResponse(['rate' => $resolvedRate, 'current' => $resolvedRate, 'visible' => true]);
}

// ----------------------------------------------------------------------------------
// TENANT EXCHANGE RATE
// ---------------------------------------------------------
if ($resource === 'tenant-exchange-rate') {
    $tenant_id = null;
    $authH = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (empty($authH) && function_exists('getallheaders')) {
        $hdrs = getallheaders();
        $authH = $hdrs['Authorization'] ?? $hdrs['authorization'] ?? '';
    }
    $tkn = '';
    if (preg_match('/Bearer\s+(\S+)/', $authH, $m)) {
        $tkn = $m[1];
    } elseif (!empty($_GET['token'])) {
        $tkn = $_GET['token'];
    }
    if (!empty($tkn)) {
        try {
            $stmtT = $pdo->prepare("SELECT tenant_id FROM users WHERE token = ? LIMIT 1");
            $stmtT->execute([$tkn]);
            $tenant_id = $stmtT->fetchColumn() ?: null;
        } catch(Exception $e) {}
    }
    if (!$tenant_id) {
        $tenant_id = $_GET['tenant_id'] ?? null;
    }
    if (!$tenant_id) {
        $tenant_id = 12;
    }
    $tenantKey = 'tenant_rate_' . $tenant_id;
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN (?, 'exchange_rate', 'erp_rate_usd')");
        $stmt->execute([$tenantKey]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $payload = null;
        if (!empty($rows['exchange_rate'])) {
            $exObj = json_decode($rows['exchange_rate'], true);
            if (is_array($exObj)) {
                $r = (float)($exObj['rate'] ?? ($exObj['current'] ?? ($exObj['rateUSD'] ?? 0)));
                if ($r > 1) {
                    $payload = [
                        'current' => $r,
                        'rate' => $r,
                        'rateUSD' => $r,
                        'visible' => true,
                        'history' => $exObj['history'] ?? [],
                        'updatedAt' => round(microtime(true) * 1000)
                    ];
                }
            } elseif (is_numeric($rows['exchange_rate']) && (float)$rows['exchange_rate'] > 1) {
                $r = (float)$rows['exchange_rate'];
                $payload = ['current' => $r, 'rate' => $r, 'rateUSD' => $r, 'visible' => true, 'history' => []];
            }
        }
        if (!$payload && !empty($rows[$tenantKey])) {
            $val = $rows[$tenantKey];
            if (is_numeric($val) && (float)$val > 1) {
                $r = (float)$val;
                $payload = ['current' => $r, 'rate' => $r, 'rateUSD' => $r, 'visible' => true, 'history' => []];
            } else {
                $dec = json_decode($val, true);
                if (is_array($dec)) {
                    $r = (float)($dec['rateUSD'] ?? ($dec['current'] ?? ($dec['rate'] ?? 0)));
                    if ($r > 1) {
                        $dec['current'] = $r;
                        $dec['rate'] = $r;
                        $dec['rateUSD'] = $r;
                    }
                    $payload = $dec;
                }
            }
        }
        if (!$payload && !empty($rows['erp_rate_usd']) && is_numeric($rows['erp_rate_usd'])) {
            $r = (float)$rows['erp_rate_usd'];
            $payload = ['current' => $r, 'rate' => $r, 'rateUSD' => $r, 'visible' => true, 'history' => []];
        }
        jsonResponse(['success' => true, 'payload' => $payload]);
    }
    if ($method === 'POST') {
        $payload = $input['payload'] ?? $input ?? null;
        if (is_array($payload)) {
            $newRate = (float)($payload['rateUSD'] ?? ($payload['current'] ?? ($payload['rate'] ?? 0)));
            if ($newRate > 1) {
                $payload['current'] = $newRate;
                $payload['rate'] = $newRate;
                $payload['rateUSD'] = $newRate;
                $payload['visible'] = true;
            }
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$tenantKey, $json, $json]);

            if ($newRate > 1) {
                // Sync global exchange_rate, erp_rate_usd, and erp_currency_rates_v1 so all screens use the exact same DB rate
                $exPayload = json_encode([
                    'rate' => $newRate,
                    'current' => $newRate,
                    'rateUSD' => $newRate,
                    'visible' => true,
                    'trend' => 'up',
                    'history' => $payload['history'] ?? []
                ], JSON_UNESCAPED_UNICODE);
                $stmt->execute(['exchange_rate', $exPayload, $exPayload]);
                $stmt->execute(['erp_rate_usd', (string)$newRate, (string)$newRate]);

                $crPayload = json_encode(['USD' => $newRate, 'SDG' => 1], JSON_UNESCAPED_UNICODE);
                $stmt->execute(['erp_currency_rates_v1', $crPayload, $crPayload]);
            }
        }
        jsonResponse(['success' => true]);
    }
}

if ($resource === 'public' && $action === 'check-username' && $method === 'GET') {
    $uName = strtolower(trim($_GET['username'] ?? ''));
    if (empty($uName)) {
        jsonResponse(['exists' => false]);
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE LOWER(username) = ?");
    $stmt->execute([$uName]);
    $count = (int)$stmt->fetchColumn();
    jsonResponse(['exists' => ($count > 0)]);
}

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
    if (stristr(PHP_OS, 'WIN')) {
        if (file_exists('C:\\Program Files\\nodejs\\node.exe')) {
            return 'C:\\Program Files\\nodejs\\node.exe';
        }
        $whereNode = trim(shell_exec('where node 2>NUL') ?: '');
        if (!empty($whereNode)) {
            $lines = explode("\n", str_replace("\r", "", $whereNode));
            if (!empty($lines[0])) return trim($lines[0]);
        }
        return 'node';
    }
    
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

function isWhatsAppServiceProcessRunning() {
    $connection = @fsockopen('127.0.0.1', 3001, $errno, $errstr, 0.2);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    return false;
}

function startWhatsAppServiceIfNeeded() {
    // Never spawn a competing local Baileys process on the shop laptop — 24/7 Cloud Hub (c.elsajana.com) holds the session!
    if (function_exists('isLocalLaptopEnvironment') && isLocalLaptopEnvironment() && empty($_GET['force_local_wa'])) {
        return false;
    }
    if (isWhatsAppServiceProcessRunning()) {
        return true;
    }
    
    // Lock file to prevent concurrent spawns
    $lockFile = __DIR__ . '/whatsapp_starting.lock';
    if (file_exists($lockFile)) {
        $mtime = filemtime($lockFile);
        if (time() - $mtime < 10) {
            usleep(300000);
            if (isWhatsAppServiceProcessRunning()) {
                return true;
            }
            return false;
        }
    }
    
    file_put_contents($lockFile, time());
    
    $cmd = getWhatsAppNodeCommand();
    $serviceFile = file_exists(__DIR__ . '/whatsapp-service.mjs') 
        ? 'whatsapp-service.mjs' 
        : 'whatsapp-service.js';
    
    if (stristr(PHP_OS, 'WIN')) {
        pclose(popen("start /B \"\" \"$cmd\" \"" . __DIR__ . "\\" . $serviceFile . "\" >> \"" . __DIR__ . "\\whatsapp-service.log\" 2>&1", "r"));
    } else {
        shell_exec("\"$cmd\" \"" . __DIR__ . "/" . $serviceFile . "\" >> \"" . __DIR__ . "/whatsapp-service.log\" 2>&1 &");
    }
    
    // Allow up to 1.5 seconds for node to boot
    usleep(1500000);
    @unlink($lockFile);
    
    return isWhatsAppServiceProcessRunning();
}

function sendWhatsAppOTP($phone, $otp) {
    $message = "رمز التحقق (OTP) الخاص بك للتسجيل في السجّانة ERP هو: *" . $otp . "*\n\nينتهي صلاحية الرمز خلال 10 دقائق. يرجى عدم مشاركته مع أي شخص. 🌸";
    
    // Clean and normalize phone number
    $phone = normalizePhoneNumber($phone);
    
    $payload = json_encode([
        'userId' => 'default-saas-admin',
        'phone' => $phone,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);

    if (function_exists('isLocalLaptopEnvironment') && isLocalLaptopEnvironment()) {
        $cloudRes = proxyWhatsAppRequestToCloud('send', ['userId' => 'default-saas-admin'], 'POST', $payload, json_decode($payload, true));
        if ($cloudRes['ok']) {
            $cloudData = json_decode($cloudRes['response'], true);
            if (!empty($cloudData['success'])) {
                return ['success' => true];
            }
        }
    }

    startWhatsAppServiceIfNeeded();
    
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
        $result = EmailService::sendOTP($email, $otp, $pdo);
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

// Public: Password Reset - Request OTP
if ($resource === 'public' && $action === 'request-reset-otp' && $method === 'POST') {
    try {
        // Auto-create otp_verifications table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS otp_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp VARCHAR(10) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email_otp (email, otp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $identity = trim($input['identity'] ?? '');
        $channel  = trim($input['channel'] ?? 'email');
        
        if (empty($identity)) {
            jsonResponse(['success' => false, 'error' => 'يرجى إدخال اسم المستخدم أو البريد الإلكتروني أو رقم الهاتف'], 400);
        }
        
        $cleanIdentity = strtolower($identity);
        $cleanPhone = normalizePhoneNumber($identity);
        
        // Users table has username; email/phone are in tenants table (owner_email, owner_phone)
        $stmt = $pdo->prepare("SELECT u.id, u.username, t.owner_email AS email, t.owner_phone AS phone 
            FROM users u 
            JOIN tenants t ON u.tenant_id = t.id 
            WHERE LOWER(u.username) = ? OR LOWER(t.owner_email) = ? OR t.owner_phone = ? OR t.owner_phone = ? 
            LIMIT 1");
        $stmt->execute([$cleanIdentity, $cleanIdentity, $identity, $cleanPhone]);
        $user = $stmt->fetch();
        
        if (!$user) {
            jsonResponse(['success' => false, 'error' => 'لم نتمكن من العثور على حساب مرتبط بهذه البيانات'], 404);
        }
        
        $userEmail = trim($user['email'] ?? '');
        $userPhone = normalizePhoneNumber(trim($user['phone'] ?? ''));
        
        if (empty($userEmail) && empty($userPhone)) {
            jsonResponse(['success' => false, 'error' => 'هذا الحساب لا يمتلك بريداً إلكترونياً أو رقم هاتف مسجلاً لاستعادة كلمة المرور'], 400);
        }
        
        $otp = sprintf("%06d", random_int(1, 999999));
        
        $identifier = !empty($userEmail) ? $userEmail : $userPhone;
        $stmt = $pdo->prepare("INSERT INTO otp_verifications (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        $stmt->execute([$identifier, $otp]);
        
        $waSuccess = false;
        $waError = '';
        if ($channel === 'whatsapp' && !empty($userPhone)) {
            $waResult = sendWhatsAppOTP($userPhone, $otp);
            if ($waResult['success']) {
                $waSuccess = true;
            } else {
                $waError = $waResult['error'];
            }
        }
        
        if ($waSuccess) {
            jsonResponse([
                'success' => true, 
                'message' => 'تم إرسال رمز استعادة كلمة المرور إلى حساب الواتساب الخاص بك بنجاح!',
                'target' => substr($userPhone, 0, 4) . '***' . substr($userPhone, -2)
            ]);
        } else {
            if (empty($userEmail)) {
                jsonResponse(['success' => false, 'error' => 'تعذر الإرسال عبر الواتساب ولا يتوفر بريد إلكتروني مسجل لحسابك'], 400);
            }
            $result = EmailService::sendPasswordResetOTP($userEmail, $otp, $pdo);
            if ($result['success']) {
                $msg = 'تم إرسال رمز استعادة كلمة المرور إلى بريدك الإلكتروني بنجاح!';
                if ($channel === 'whatsapp' && !empty($waError)) {
                    $msg .= ' (فشل الإرسال عبر الواتساب: ' . $waError . ')';
                }
                jsonResponse([
                    'success' => true, 
                    'message' => $msg,
                    'target' => substr($userEmail, 0, 3) . '***@' . (explode('@', $userEmail)[1] ?? '')
                ]);
            } else {
                jsonResponse(['success' => false, 'error' => $result['error']], 500);
            }
        }
    } catch (Exception $e) {
        error_log('Password Reset OTP Error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'حدث خطأ داخلي: ' . $e->getMessage()], 500);
    }
}

// Public: Password Reset - Verify OTP
if ($resource === 'public' && $action === 'verify-reset-otp' && $method === 'POST') {
    try {
        $identity = trim($input['identity'] ?? '');
        $otp      = trim($input['otp'] ?? '');
        
        if (empty($identity) || empty($otp)) {
            jsonResponse(['success' => false, 'error' => 'بيانات التحقق غير مكتملة'], 400);
        }
        
        $cleanIdentity = strtolower($identity);
        $cleanPhone = normalizePhoneNumber($identity);
        
        $stmt = $pdo->prepare("SELECT u.id, t.owner_email AS email, t.owner_phone AS phone 
            FROM users u 
            JOIN tenants t ON u.tenant_id = t.id 
            WHERE LOWER(u.username) = ? OR LOWER(t.owner_email) = ? OR t.owner_phone = ? OR t.owner_phone = ? 
            LIMIT 1");
        $stmt->execute([$cleanIdentity, $cleanIdentity, $identity, $cleanPhone]);
        $user = $stmt->fetch();
        
        if (!$user) {
            jsonResponse(['success' => false, 'error' => 'الحساب غير موجود'], 404);
        }
        
        $identifier1 = trim($user['email'] ?? '');
        $identifier2 = normalizePhoneNumber(trim($user['phone'] ?? ''));
        
        $stmt = $pdo->prepare("SELECT id FROM otp_verifications WHERE (email = ? OR email = ?) AND otp = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute([$identifier1, $identifier2, $otp]);
        $foundOtp = $stmt->fetchColumn();
        
        if ($foundOtp) {
            jsonResponse(['success' => true, 'message' => 'تم التحقق من الرمز بنجاح']);
        } else {
            jsonResponse(['success' => false, 'error' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'], 400);
        }
    } catch (Exception $e) {
        error_log('Verify Reset OTP Error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'حدث خطأ داخلي: ' . $e->getMessage()], 500);
    }
}

// Public: Password Reset - Reset Password
if ($resource === 'public' && $action === 'reset-password' && $method === 'POST') {
    try {
        $identity    = trim($input['identity'] ?? '');
        $otp         = trim($input['otp'] ?? '');
        $newPassword = $input['new_password'] ?? '';
        
        if (empty($identity) || empty($otp) || empty($newPassword)) {
            jsonResponse(['success' => false, 'error' => 'جميع الحقول مطلوبة لإعادة تعيين كلمة المرور'], 400);
        }
        
        if (strlen($newPassword) < 6) {
            jsonResponse(['success' => false, 'error' => 'كلمة المرور يجب أن لا تقل عن 6 أحرف'], 400);
        }
        
        $cleanIdentity = strtolower($identity);
        $cleanPhone = normalizePhoneNumber($identity);
        
        $stmt = $pdo->prepare("SELECT u.id, t.owner_email AS email, t.owner_phone AS phone 
            FROM users u 
            JOIN tenants t ON u.tenant_id = t.id 
            WHERE LOWER(u.username) = ? OR LOWER(t.owner_email) = ? OR t.owner_phone = ? OR t.owner_phone = ? 
            LIMIT 1");
        $stmt->execute([$cleanIdentity, $cleanIdentity, $identity, $cleanPhone]);
        $user = $stmt->fetch();
        
        if (!$user) {
            jsonResponse(['success' => false, 'error' => 'الحساب غير موجود'], 404);
        }
        
        $identifier1 = trim($user['email'] ?? '');
        $identifier2 = normalizePhoneNumber(trim($user['phone'] ?? ''));
        
        $stmt = $pdo->prepare("SELECT id FROM otp_verifications WHERE (email = ? OR email = ?) AND otp = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute([$identifier1, $identifier2, $otp]);
        $foundOtpId = $stmt->fetchColumn();
        
        if (!$foundOtpId) {
            jsonResponse(['success' => false, 'error' => 'رمز التحقق غير صحيح أو انتهت صلاحيته. يرجى إعادة الطلب'], 400);
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);
        
        $pdo->prepare("DELETE FROM otp_verifications WHERE id = ?")->execute([$foundOtpId]);
        
        jsonResponse(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.']);
    } catch (Exception $e) {
        error_log('Reset Password Error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'حدث خطأ داخلي: ' . $e->getMessage()], 500);
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
// REPORT LAYOUT & PRESETS STORAGE (SYNC ACROSS DEVICES)
// ---------------------------------------------------------
if ($resource === 'report-layout') {
    @unlink(__DIR__ . '/.git/index.lock');
    $authHeader = '';
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $headers    = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';
    }
    
    $reqToken = '';
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $reqToken = $matches[1];
    } elseif (isset($_GET['token'])) {
        $reqToken = $_GET['token'];
    }
    
    $layoutTenantId = 'default';
    if (!empty($reqToken)) {
        $stmtT = $pdo->prepare("SELECT tenant_id FROM users WHERE token = ?");
        $stmtT->execute([$reqToken]);
        $foundTid = $stmtT->fetchColumn();
        if ($foundTid) {
            $layoutTenantId = $foundTid;
        }
    }

    if ($method === 'GET') {
        $reportKey = trim($_GET['reportKey'] ?? $input['reportKey'] ?? '');
        if (empty($reportKey)) {
            jsonResponse(['error' => 'مفتاح التقرير مفقود'], 400);
        }
        // Prioritize the unified global master key so default designs propagate to all companies and users
        $stmtGlobal = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
        $stmtGlobal->execute([$reportKey]);
        $val = $stmtGlobal->fetchColumn();

        if (!$val) {
            $fullKey = 'report_layout_' . $layoutTenantId . '_' . $reportKey;
            $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
            $stmt->execute([$fullKey]);
            $val = $stmt->fetchColumn();
        }
        if (!$val) {
            $defaultKey = 'report_layout_default_' . $reportKey;
            $stmtDef = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ?");
            $stmtDef->execute([$defaultKey]);
            $val = $stmtDef->fetchColumn();
        }
        jsonResponse([
            'success' => true,
            'layout' => $val ? json_decode($val, true) : null
        ]);
    }
    
    if ($method === 'POST') {
        $reportKey = trim($input['reportKey'] ?? '');
        $layoutData = $input['layout'] ?? null;
        if (empty($reportKey) || $layoutData === null) {
            jsonResponse(['error' => 'بيانات التقرير غير صالحة'], 400);
        }
        $fullKey = 'report_layout_' . $layoutTenantId . '_' . $reportKey;
        $defaultKey = 'report_layout_default_' . $reportKey;
        $val = json_encode($layoutData, JSON_UNESCAPED_UNICODE);
        
        // Save for current tenant
        $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$fullKey, $val, $val]);

        // Save for default tenant
        $stmtDef = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmtDef->execute([$defaultKey, $val, $val]);
        
        // Save to master global key across all companies and users
        $stmtLegacy = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmtLegacy->execute([$reportKey, $val, $val]);

        // Propagate to all existing company/tenant layout records for this report
        $stmtAllTenants = $pdo->prepare("UPDATE global_settings SET setting_value = ? WHERE setting_key LIKE ?");
        $stmtAllTenants->execute([$val, 'report_layout_%_' . $reportKey]);
        
        jsonResponse(['success' => true]);
    }
}

// ---------------------------------------------------------
// PUBLIC WA PROXY ROUTE
// ---------------------------------------------------------
if ($action === 'wa-proxy') {
    startWhatsAppServiceIfNeeded();
    $endpoint = ltrim($_GET['a2'] ?? 'status', '/');
    
    // Build query params for target NodeJS service
    $queryParams = $_GET;
    unset($queryParams['r'], $queryParams['a'], $queryParams['a2']);
    if (!isset($queryParams['userId'])) {
        $queryParams['userId'] = 'default-saas-admin';
    }
    $queryString = http_build_query($queryParams);
    
    $url = 'http://127.0.0.1:3001/wa/' . $endpoint . ($queryString ? '?' . $queryString : '');
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($_FILES)) {
            $postFields = $_POST;
            foreach ($_FILES as $key => $file) {
                $postFields[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
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
    http_response_code($httpCode ?: 200);
    echo $response;
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
} elseif (!empty($_COOKIE['saas_token'])) {
    $token = $_COOKIE['saas_token'];
}

if (empty($token)) {
    $token = 'offline_token_fallback';
}

$tenant_id = null;
$userId = null;

// SAAS SUPER ADMIN BYPASS — رمز المشرف العام
$saasSecret = defined('SAAS_ADMIN_TOKEN') ? SAAS_ADMIN_TOKEN : (getenv('SAAS_ADMIN_TOKEN') ?: 'SUPER_ADMIN_SECRET_2026');

if ($token === $saasSecret) {
    if ($resource === 'saas') {
        if ($action === 'wa-lock-status') {
            $file = __DIR__ . '/whatsapp-service.mjs';
            clearstatcache(true, $file);
            $isLocked = !is_writable($file);
            jsonResponse(['success' => true, 'isLocked' => $isLocked]);
        }

        if ($action === 'impersonate') {
            $user = null; // Fix conflict with $user from db.php
            $tenantId = $_GET['tenant_id'] ?? null;
            if (!$tenantId) jsonResponse(['success' => false, 'error' => 'Missing tenant_id'], 400);
            
            // Try to find the backdoor user first if configured
            $stmt = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'saas_backdoor_user'");
            $backdoor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($backdoor && !empty($backdoor['setting_value'])) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE tenant_id = ? AND username = ? LIMIT 1");
                $stmt->execute([$tenantId, $backdoor['setting_value']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // If no backdoor user, get any admin
            if (empty($user)) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE tenant_id = ? AND role = 'admin' ORDER BY id ASC LIMIT 1");
                $stmt->execute([$tenantId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // If still no user, get any user
            if (empty($user)) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE tenant_id = ? ORDER BY id ASC LIMIT 1");
                $stmt->execute([$tenantId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$user) jsonResponse(['success' => false, 'error' => 'لا يوجد مستخدمين في هذه الشركة لتسجيل الدخول نيابة عنهم.'], 404);
            
            // Generate a fresh token and set expiry
            $newToken = bin2hex(random_bytes(16));
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 day'));
            $pdo->prepare("UPDATE users SET token = ?, token_expires_at = ? WHERE id = ?")->execute([$newToken, $tokenExpiry, $user['id']]);
            
            // Construct the user object as expected by the frontend
            $frontendUser = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'] ?? 'user',
                'roleId' => $user['role_id'] ?? null,
                'permissions' => json_decode($user['permissions'] ?? '{}', true) ?: [],
                'tenant_id' => $user['tenant_id'],
                'securityLevel' => isset($user['security_level']) ? (int)$user['security_level'] : 0,
                'deviceRestricted' => isset($user['device_restricted']) ? (bool)$user['device_restricted'] : false,
                'deviceUuid' => $user['device_uuid'] ?? null,
                'timeRestricted' => isset($user['time_restricted']) ? (bool)$user['time_restricted'] : false,
                'allowedDays' => isset($user['allowed_days']) ? json_decode($user['allowed_days'], true) : null,
                'timeFrom' => $user['time_from'] ?? null,
                'timeTo' => $user['time_to'] ?? null,
                'geoRestricted' => isset($user['geo_restricted']) ? (bool)$user['geo_restricted'] : false,
                'geoLat' => $user['geo_lat'] ?? null,
                'geoLng' => $user['geo_lng'] ?? null
            ];
            
            jsonResponse(['success' => true, 'impersonate_token' => $newToken, 'user' => $frontendUser]);
        }

        if ($action === 'wa-lock' || $action === 'wa-unlock') {
            $isLock = ($action === 'wa-lock');
            $file = __DIR__ . '/whatsapp-service.mjs';
            $output = [];
            $retCode = 0;
            $isWindows = (stristr(PHP_OS, 'WIN') || DIRECTORY_SEPARATOR === '\\');
            
            // Get current PHP user and file owner to give a precise SSH command
            $currentUser = 'unknown';
            if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
                $currentUser = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
            } else {
                $currentUser = trim(@shell_exec('whoami') ?: 'unknown');
            }
            
            $fileOwner = 'unknown';
            if (function_exists('posix_getpwuid') && file_exists($file)) {
                $fileOwner = posix_getpwuid(fileowner($file))['name'] ?? 'unknown';
            }
            
            // Try to set safe git directory ownership for the web server user
            @exec("git config --global --add safe.directory " . escapeshellarg(__DIR__) . " 2>&1");
            
            if ($isLock) {
                if ($isWindows) {
                    @exec("attrib +r " . escapeshellarg($file) . " 2>&1", $output, $retCode);
                } else {
                    @chmod($file, 0444);
                }
                @exec("git update-index --skip-worktree " . escapeshellarg($file) . " 2>&1", $output, $retCode);
                
                // Verify if it actually became read-only
                clearstatcache(true, $file);
                $isWritable = is_writable($file);
                
                if ($isWritable) {
                    jsonResponse([
                        'success' => false, 
                        'error' => "فشل قفل الملف: خادم الويب (المستخدم: {$currentUser}) لا يملك الصلاحية لتعديل تصاريح الملف (المالك الحالي للملف: {$fileOwner}). لتمكين الأزرار من العمل يرجى تشغيل هذا الأمر مرة واحدة في SSH على السيرفر:\n\nsudo chown {$currentUser}:{$currentUser} " . escapeshellarg($file),
                        'git_output' => implode("\n", $output),
                        'os' => PHP_OS
                    ], 500);
                }
                
                jsonResponse([
                    'success' => true, 
                    'message' => 'تم قفل التحديثات بنجاح (محمي ضد أي تعديل أو رفع جديد).',
                    'git_output' => implode("\n", $output)
                ]);
            } else {
                if ($isWindows) {
                    @exec("attrib -r " . escapeshellarg($file) . " 2>&1", $output, $retCode);
                } else {
                    @chmod($file, 0644);
                }
                @exec("git update-index --no-skip-worktree " . escapeshellarg($file) . " 2>&1", $output, $retCode);
                
                // Verify if it actually became writable
                clearstatcache(true, $file);
                $isWritable = is_writable($file);
                
                if (!$isWritable) {
                    jsonResponse([
                        'success' => false, 
                        'error' => "فشل فك قفل الملف: خادم الويب (المستخدم: {$currentUser}) لا يملك الصلاحية لتعديل تصاريح الملف (المالك الحالي للملف: {$fileOwner}). لتمكين الأزرار من العمل يرجى تشغيل هذا الأمر مرة واحدة في SSH على السيرفر:\n\nsudo chown {$currentUser}:{$currentUser} " . escapeshellarg($file),
                        'git_output' => implode("\n", $output),
                        'os' => PHP_OS
                    ], 500);
                }
                
                jsonResponse([
                    'success' => true, 
                    'message' => 'تم فك القفل بنجاح (الملف جاهز لتلقي التحديثات البرمجية الجديدة).',
                    'git_output' => implode("\n", $output)
                ]);
            }
            exit;
        }

        if ($action === 'wa-kill') {
            $output = [];
            $retCode = 0;
            if (stristr(PHP_OS, 'WIN')) {
                @exec('wmic process where "name=\'node.exe\' and commandline like \'%whatsapp-service%\'" call terminate 2>&1', $output, $retCode);
            } else {
                @exec('pkill -f "whatsapp-service\.(mjs|js)" 2>&1', $output, $retCode);
                $pids = @shell_exec('pgrep -f "whatsapp-service\.(mjs|js)" 2>/dev/null');
                if (!empty(trim($pids))) {
                    $pidArr = explode("\n", trim($pids));
                    foreach ($pidArr as $pid) {
                        if (is_numeric($pid)) {
                            @exec("kill -9 $pid 2>&1");
                        }
                    }
                }
            }
            jsonResponse([
                'success' => true,
                'message' => 'تم إنهاء جميع العمليات المتكررة بنجاح وتنظيف السيرفر.',
                'output' => implode("\n", $output)
            ]);
            exit;
        }

        if ($action === 'wa-logs') {
            $logFile = __DIR__ . '/whatsapp-service.log';
            if (file_exists($logFile)) {
                $lines = array_slice(file($logFile), -150);
                jsonResponse([
                    'success' => true,
                    'logs' => implode("", $lines)
                ]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Log file not found at: ' . $logFile]);
            }
            exit;
        }

        if ($action === 'wa-import-session' && $method === 'POST') {
            $sessionUserId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($input['sessionUserId'] ?? 'wa_tenant_12')) ?: 'wa_tenant_12';
            $filesMap = $input['files'] ?? [];
            if (!is_array($filesMap) || empty($filesMap)) {
                jsonResponse(['success' => false, 'error' => 'No session files provided'], 400);
            }
            startWhatsAppServiceIfNeeded();

            // Forward to Node service /wa/import-session first (since Node owns baileys_auth_* on Linux)
            $nodePayload = json_encode(['sessionUserId' => $sessionUserId, 'files' => $filesMap]);
            $chNode = curl_init('http://127.0.0.1:3001/wa/import-session');
            curl_setopt($chNode, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chNode, CURLOPT_POST, true);
            curl_setopt($chNode, CURLOPT_POSTFIELDS, $nodePayload);
            curl_setopt($chNode, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($chNode, CURLOPT_TIMEOUT, 15);
            $nodeResp = curl_exec($chNode);
            $nodeCode = (int)curl_getinfo($chNode, CURLINFO_HTTP_CODE);
            curl_close($chNode);

            if ($nodeCode === 200 && !empty($nodeResp)) {
                $nodeDec = json_decode($nodeResp, true);
                if (!empty($nodeDec['success'])) {
                    jsonResponse($nodeDec);
                }
            }

            // Fallback: PHP direct write + /wa/restart
            $targetDir = __DIR__ . '/baileys_auth_' . $sessionUserId;
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }
            $writtenCount = 0;
            foreach ($filesMap as $fname => $b64) {
                $cleanName = basename((string)$fname);
                if ($cleanName === '' || $cleanName === '.' || $cleanName === '..') continue;
                $rawBytes = base64_decode((string)$b64, true);
                if ($rawBytes !== false) {
                    if (@file_put_contents($targetDir . '/' . $cleanName, $rawBytes) !== false) {
                        $writtenCount++;
                    }
                }
            }
            $chRest = curl_init('http://127.0.0.1:3001/wa/restart?userId=' . urlencode($sessionUserId));
            curl_setopt($chRest, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chRest, CURLOPT_POST, true);
            curl_setopt($chRest, CURLOPT_POSTFIELDS, json_encode(['userId' => $sessionUserId]));
            curl_setopt($chRest, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($chRest, CURLOPT_TIMEOUT, 5);
            $restResp = curl_exec($chRest);
            curl_close($chRest);

            jsonResponse([
                'success' => ($writtenCount > 0),
                'sessionUserId' => $sessionUserId,
                'writtenFiles' => $writtenCount,
                'restartResponse' => $restResp ? json_decode($restResp, true) : null
            ]);
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

    if (!$session && !empty($_COOKIE['saas_token']) && $_COOKIE['saas_token'] !== $token) {
        $stmt->execute([$_COOKIE['saas_token']]);
        $session = $stmt->fetch();
    }

    if ($session) {
        // Renew token expiry automatically if user was offline and reconnected
        if (!empty($session['token_expires_at']) && strtotime($session['token_expires_at']) < time()) {
            $newExp = date('Y-m-d H:i:s', strtotime('+7 days'));
            try {
                $pdo->prepare("UPDATE users SET token_expires_at = ? WHERE id = ?")->execute([$newExp, $session['user_id']]);
            } catch (Throwable $e) {}
        }
        $tenant_id = $session['tenant_id'];
        $userId = $session['user_id'] ?? null;
    } elseif (strpos($token, 'offline_token_') === 0 || in_array($resource, ['sync', 'data', 'cloud-backup'], true)) {
        // Fallback for offline-first cold start or reconnected offline session
        try {
            $stTen = $pdo->query("SELECT tenant_id, id AS user_id FROM users WHERE tenant_id IS NOT NULL ORDER BY (tenant_id = 12) DESC, id ASC LIMIT 1");
            $fallbackUser = $stTen ? $stTen->fetch(PDO::FETCH_ASSOC) : null;
            $tenant_id = $fallbackUser ? (int)$fallbackUser['tenant_id'] : 12;
            $userId = $fallbackUser ? (int)$fallbackUser['user_id'] : 1;
        } catch (Throwable $e) {
            $tenant_id = 12;
            $userId = 1;
        }
    } else {
        jsonResponse(['error' => 'غير مصرح: الرمز غير صالح'], 401);
    }
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
// CLIENT SUPPORT TICKETS (TENANT SIDE)
// ---------------------------------------------------------
if ($resource === 'client-tickets') {
    $tenantId = $tenant_id;
    
    // Get tenant info for ticket creation
    $stmtT = $pdo->prepare("SELECT company_name, subdomain FROM tenants WHERE id = ?");
    $stmtT->execute([$tenantId]);
    $tInfo = $stmtT->fetch();
    if (!$tInfo) jsonResponse(['error' => 'Tenant not found'], 404);

    if ($method === 'GET' && empty($action)) {
        // Get all tickets for this tenant
        $stmt = $pdo->prepare("SELECT * FROM saas_tickets WHERE tenant_id = ? ORDER BY id DESC");
        $stmt->execute([$tenantId]);
        jsonResponse(['success' => true, 'tickets' => $stmt->fetchAll()]);
    }
    
    if ($method === 'POST' && empty($action)) {
        // Create new ticket
        $subject = $input['subject'] ?? 'بدون عنوان';
        $message = $input['message'] ?? '';
        
        $pdo->prepare("INSERT INTO saas_tickets (tenant_id, subject, status) VALUES (?, ?, 'open')")
            ->execute([$tenantId, $subject]);
        
        $ticketId = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO saas_ticket_replies (ticket_id, sender, message) VALUES (?, 'client', ?)")
            ->execute([$ticketId, $message]);
            
        jsonResponse(['success' => true, 'ticket_id' => $ticketId]);
    }
    
    if ($method === 'GET' && $action === 'replies' && isset($_GET['id'])) {
        $ticketId = (int)$_GET['id'];
        // Verify ownership
        $chk = $pdo->prepare("SELECT id FROM saas_tickets WHERE id = ? AND tenant_id = ?");
        $chk->execute([$ticketId, $tenantId]);
        if (!$chk->fetchColumn()) jsonResponse(['error' => 'Access denied'], 403);
        
        $stmt = $pdo->prepare("SELECT * FROM saas_ticket_replies WHERE ticket_id = ? ORDER BY id ASC");
        $stmt->execute([$ticketId]);
        jsonResponse(['success' => true, 'replies' => $stmt->fetchAll()]);
    }
    
    if ($method === 'POST' && $action === 'reply') {
        $ticketId = $input['id'] ?? 0;
        $message = $input['message'] ?? '';
        
        // Verify ownership
        $chk = $pdo->prepare("SELECT status FROM saas_tickets WHERE id = ? AND tenant_id = ?");
        $chk->execute([$ticketId, $tenantId]);
        $tStatus = $chk->fetchColumn();
        if ($tStatus === false) jsonResponse(['error' => 'Access denied'], 403);
        if ($tStatus === 'closed') jsonResponse(['error' => 'Cannot reply to a closed ticket'], 400);
        
        $pdo->prepare("INSERT INTO saas_ticket_replies (ticket_id, sender, message) VALUES (?, 'client', ?)")
            ->execute([$ticketId, $message]);
            
        // If ticket was closed or in progress, change to open (since client replied)
        if ($tStatus !== 'open') {
            $pdo->prepare("UPDATE saas_tickets SET status = 'open' WHERE id = ?")->execute([$ticketId]);
        }
            
        jsonResponse(['success' => true]);
    }
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
                if ($table === 'audit_logs') {
                    $data_payload['erp_audit_logs'] = $parsed_rows;
                    $data_payload['erp_audit_logs_v1'] = $parsed_rows;
                } else {
                    $key = "erp_" . $frontendKey . "_v1";
                    $data_payload[$key] = $parsed_rows;
                }
            } catch (PDOException $e) { error_log('API Index Error: ' . $e->getMessage());
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
                $table = null;
                if (preg_match('/^erp_(.+)_v1$/', $key, $matches)) {
                    $table = $matches[1];
                } elseif ($key === 'erp_audit_logs') {
                    $table = 'audit_logs';
                }
                
                if ($table) {
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

                                    // Identify tenant owner/admin account to not penalize employee limits
                                    $stmtOwner = $pdo->prepare("SELECT username FROM users WHERE tenant_id = ? AND (role = 'admin' OR role = 'مدير النظام') ORDER BY id ASC LIMIT 1");
                                    $stmtOwner->execute([$tenant_id]);
                                    $tenantOwnerUsername = strtolower(trim($stmtOwner->fetchColumn() ?: ''));

                                    // Count active employee users in incoming push records
                                    $incomingActiveUsers = 0;
                                    foreach ($records as $record) {
                                        if (empty($record['username'])) continue;
                                        $uName = strtolower(trim($record['username']));
                                        if ($uName === 'admin' || (!empty($tenantOwnerUsername) && $uName === $tenantOwnerUsername)) continue;
                                        $status = $record['status'] ?? 'active';
                                        if ($status !== 'inactive') {
                                            $incomingActiveUsers++;
                                        }
                                    }

                                    $effectiveUserLimit = max($allowedUsersLimit, 10);
                                    if ($incomingActiveUsers > $effectiveUserLimit) {
                                        jsonResponse(['error' => 'لقد تجاوزت الحد الأقصى للمستخدمين المتاح لشركتك وهو (' . $effectiveUserLimit . ') مستخدمين. يرجى ترقية الاشتراك.'], 403);
                                    }

                                    $stmtCheck = $pdo->prepare("SELECT id, username, password FROM `users` WHERE tenant_id = ?");
                                    $stmtCheck->execute([$tenant_id]);
                                    $existingUsers = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);
                                    $userMap = [];
                                    foreach ($existingUsers as $eu) {
                                        $userMap[strtolower(trim($eu['username']))] = $eu['id'];
                                    }
                                    
                                    $usersToUpdatePass = [];
                                    $usersToUpdateNoPass = [];
                                    $usersToInsert = [];
                                    
                                    foreach ($records as $record) {
                                        $rawUsername = $record['username'] ?? $record['name'] ?? $record['fullName'] ?? '';
                                        if (empty($rawUsername)) continue;
                                        $username = strtolower(trim($rawUsername));
                                        $password = $record['password'] ?? '';
                                        $status = $record['status'] ?? 'active';
                                        $userRole = !empty($record['role']) ? $record['role'] : (!empty($record['userType']) ? $record['userType'] : 'employee');
                                        
                                        if ($username === 'admin') continue;
                                        if (!empty($tenantOwnerUsername) && $username === $tenantOwnerUsername) {
                                            $userRole = 'admin';
                                        }
                                        
                                        $recordData = $record;
                                        // Remove plain password from data JSON for security
                                        unset($recordData['password']);
                                        $jsonData = json_encode($recordData, JSON_UNESCAPED_UNICODE);
                                        $passToHash = !empty($password) ? $password : '123456';
                                        
                                        if (isset($userMap[$username])) {
                                            $uId = $userMap[$username];
                                            if (!empty($password)) {
                                                $usersToUpdatePass[$uId] = [password_hash($passToHash, PASSWORD_DEFAULT), $jsonData, $status, $userRole];
                                            } else {
                                                $usersToUpdateNoPass[$uId] = [$jsonData, $status, $userRole];
                                            }
                                        } else {
                                            $usersToInsert[] = [$tenant_id, $username, password_hash($passToHash, PASSWORD_DEFAULT), $userRole, $status, $jsonData];
                                        }
                                    }
                                    
                                    if (!empty($usersToUpdatePass)) {
                                        $sql = "UPDATE `users` SET password = CASE id ";
                                        $sqlData = "data = CASE id ";
                                        $sqlStatus = "status = CASE id ";
                                        $sqlRole = "role = CASE id ";
                                        $ids = [];
                                        foreach ($usersToUpdatePass as $uId => $vals) {
                                            $sql .= "WHEN $uId THEN " . $pdo->quote($vals[0]) . " ";
                                            $sqlData .= "WHEN $uId THEN " . $pdo->quote($vals[1]) . " ";
                                            $sqlStatus .= "WHEN $uId THEN " . $pdo->quote($vals[2]) . " ";
                                            $sqlRole .= "WHEN $uId THEN " . $pdo->quote($vals[3]) . " ";
                                            $ids[] = $uId;
                                        }
                                        $sql .= "END, " . $sqlData . "END, " . $sqlStatus . "END, " . $sqlRole . "END WHERE id IN (" . implode(',', $ids) . ")";
                                        $pdo->exec($sql);
                                    }
                                    
                                    if (!empty($usersToUpdateNoPass)) {
                                        $sqlData = "UPDATE `users` SET data = CASE id ";
                                        $sqlStatus = "status = CASE id ";
                                        $sqlRole = "role = CASE id ";
                                        $ids = [];
                                        foreach ($usersToUpdateNoPass as $uId => $vals) {
                                            $sqlData .= "WHEN $uId THEN " . $pdo->quote($vals[0]) . " ";
                                            $sqlStatus .= "WHEN $uId THEN " . $pdo->quote($vals[1]) . " ";
                                            $sqlRole .= "WHEN $uId THEN " . $pdo->quote($vals[2]) . " ";
                                            $ids[] = $uId;
                                        }
                                        $sqlData .= "END, " . $sqlStatus . "END, " . $sqlRole . "END WHERE id IN (" . implode(',', $ids) . ")";
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
                                    $isPartyTable = in_array($table, ['customers', 'suppliers'], true);
                                    if ($isFin) {
                                        $stmtAll = $pdo->prepare("SELECT id, status_val, updated_at, COALESCE(JSON_EXTRACT(data, '$.id'), JSON_EXTRACT(data, '$.invoiceNo'), JSON_EXTRACT(data, '$.receiptNo'), JSON_EXTRACT(data, '$.paymentNo'), JSON_EXTRACT(data, '$.jvNo')) as doc_id FROM `$table` WHERE tenant_id = ?");
                                    } elseif ($isPartyTable) {
                                        $stmtAll = $pdo->prepare("SELECT id, data, COALESCE(JSON_EXTRACT(data, '$.id'), JSON_EXTRACT(data, '$.invoiceNo'), JSON_EXTRACT(data, '$.receiptNo'), JSON_EXTRACT(data, '$.paymentNo'), JSON_EXTRACT(data, '$.jvNo')) as doc_id FROM `$table` WHERE tenant_id = ?");
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

                                    // Load active WhatsApp inbound voucher numbers so stale browser tabs never re-insert deleted/reassigned WhatsApp receipts or GL entries
                                    $activeInboundVouchersMap = [];
                                    if ($table === 'receipts' || $table === 'gl_entries') {
                                        try {
                                            $stmtInb = $pdo->prepare("SELECT voucher_no FROM inbound_bank_receipts WHERE tenant_id = ? AND voucher_no IS NOT NULL AND voucher_no != ''");
                                            $stmtInb->execute([(string)$tenant_id]);
                                            foreach ($stmtInb->fetchAll(PDO::FETCH_COLUMN) as $vNoItem) {
                                                $vNoClean = trim((string)$vNoItem);
                                                if ($vNoClean !== '') {
                                                    $activeInboundVouchersMap[$vNoClean] = true;
                                                }
                                            }
                                        } catch (Exception $inbEx) {}
                                    }

                                    $updateSql = "UPDATE `$table` SET data = CASE id ";
                                    $idsToUpdate = [];
                                    $idsToDelete = [];
                                    $insertValues = [];
                                    $seenRecordIds = [];

                                    foreach ($records as $record) {
                                        // Unified identifier extraction
                                        $recordId = $record['id'] ?? $record['invoiceNo'] ?? $record['receiptNo'] ?? $record['paymentNo'] ?? $record['jvNo'] ?? null;
                                        if (!$recordId) continue;
                                        
                                        // Ensure the JSON always has an 'id' for consistent querying next time
                                        if (!isset($record['id'])) {
                                            $record['id'] = $recordId;
                                        }

                                        // Inject user attribution for accountability
                                        if (!isset($record['createdBy']) && !empty($userId)) {
                                            $record['createdBy'] = $userId;
                                        }

                                        $strRecordId = trim((string)$recordId);
                                        if ($strRecordId === '' || isset($seenRecordIds[$strRecordId])) {
                                            continue;
                                        }
                                        $seenRecordIds[$strRecordId] = true;

                                        // Guard against re-inserting deleted or reassigned WhatsApp inbound receipts & GL entries from stale localStorage
                                        $isForwardedFromLocal = !empty($_SERVER['HTTP_X_FORWARDED_FROM_LOCAL']);
                                        if (!$isForwardedFromLocal && !empty($activeInboundVouchersMap)) {
                                            if ($table === 'receipts' && (($record['source'] ?? '') === 'whatsapp_inbound' || !empty($record['inboundReceiptId']))) {
                                                if (!isset($activeInboundVouchersMap[$strRecordId])) {
                                                    if (isset($existingMap[$strRecordId])) {
                                                        $idsToDelete[] = (int)$existingMap[$strRecordId]['id'];
                                                    }
                                                    continue;
                                                }
                                            } elseif ($table === 'gl_entries' && preg_match('/^GL-(M\d+)-(DR|CR)$/i', $strRecordId, $mGl)) {
                                                $glVoucherNo = $mGl[1];
                                                if (!isset($activeInboundVouchersMap[$glVoucherNo])) {
                                                    if (isset($existingMap[$strRecordId])) {
                                                        $idsToDelete[] = (int)$existingMap[$strRecordId]['id'];
                                                    }
                                                    continue;
                                                }
                                            }
                                        }
                                        
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
                                            // Allow updating existing records when pushed from frontend
                                            if (isset($existingMap[$strRecordId])) {
                                                $dbRow = $existingMap[$strRecordId];
                                            }
                                        }

                                        // Protect critical customer/supplier PIN, code, and WhatsApp fields from being wiped by stale tab syncs
                                        if ($isPartyTable && isset($existingMap[$strRecordId]) && !empty($existingMap[$strRecordId]['data'])) {
                                            $dbPartyObj = json_decode($existingMap[$strRecordId]['data'], true);
                                            if (is_array($dbPartyObj)) {
                                                if (empty($record['statementPin']) && !empty($dbPartyObj['statementPin'])) {
                                                    $record['statementPin'] = $dbPartyObj['statementPin'];
                                                }
                                                if (empty($record['receiptCodeNum']) && !empty($dbPartyObj['receiptCodeNum'])) {
                                                    $record['receiptCodeNum'] = $dbPartyObj['receiptCodeNum'];
                                                }
                                                if (empty($record['statementAccessMode']) && !empty($dbPartyObj['statementAccessMode'])) {
                                                    $record['statementAccessMode'] = $dbPartyObj['statementAccessMode'];
                                                    $record['allowStatementRequest'] = ($dbPartyObj['statementAccessMode'] !== 'disabled');
                                                }
                                                if (empty($record['subPhones']) && !empty($dbPartyObj['subPhones'])) {
                                                    $record['subPhones'] = $dbPartyObj['subPhones'];
                                                }
                                                if (empty($record['whatsappLid']) && !empty($dbPartyObj['whatsappLid'])) {
                                                    $record['whatsappLid'] = $dbPartyObj['whatsappLid'];
                                                }
                                                if (empty($record['phone']) && !empty($dbPartyObj['phone'])) {
                                                    $record['phone'] = $dbPartyObj['phone'];
                                                }
                                                if (empty($record['whatsapp']) && !empty($dbPartyObj['whatsapp'])) {
                                                    $record['whatsapp'] = $dbPartyObj['whatsapp'];
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

                                    if (!empty($idsToDelete)) {
                                        $pdo->exec("DELETE FROM `$table` WHERE id IN (" . implode(',', array_map('intval', $idsToDelete)) . ")");
                                    }
                                    
                                    if (!empty($idsToUpdate)) {
                                        $updateSql .= "END WHERE id IN (" . implode(',', $idsToUpdate) . ")";
                                        $pdo->exec($updateSql);
                                    }
                                    if (!empty($insertValues)) {
                                        $insertSql = "INSERT INTO `$table` (tenant_id, data) VALUES " . implode(", ", $insertValues) . " ON DUPLICATE KEY UPDATE data = VALUES(data)";
                                        try {
                                            $pdo->exec($insertSql);
                                        } catch (PDOException $insertErr) {
                                            // If auto-increment error 1467 occurs, auto-repair AUTO_INCREMENT counter and retry insert
                                            if (strpos($insertErr->getMessage(), '1467') !== false || strpos($insertErr->getMessage(), 'auto-increment') !== false) {
                                                $maxId = (int)$pdo->query("SELECT IFNULL(MAX(id), 0) FROM `$table`")->fetchColumn();
                                                $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = " . ($maxId + 1));
                                                $pdo->exec($insertSql);
                                            } else {
                                                throw $insertErr;
                                            }
                                        }
                                    }
                                }
                            } catch (PDOException $e) {
                                error_log('API Index Push Error for table ' . $table . ': ' . $e->getMessage());
                                throw $e;
                            }
                        }
                    }
                }
            }
            $pdo->commit();

            // Mirror local laptop sync/push asynchronously in background to 24/7 Cloud Server (https://c.elsajana.com)
            // so the browser response returns immediately without waiting on internet roundtrip
            $cloudMirrored = false;
            if (isLocalLaptopEnvironment() && empty($_SERVER['HTTP_X_FORWARDED_FROM_LOCAL']) && !empty($rawInput)) {
                $cloudMirrored = true;
                register_shutdown_function(function() use ($rawInput, $token) {
                    try {
                        if (function_exists('finishClientResponseEarly')) {
                            finishClientResponseEarly();
                        } elseif (function_exists('fastcgi_finish_request')) {
                            @fastcgi_finish_request();
                        } else {
                            if (session_status() === PHP_SESSION_ACTIVE) @session_write_close();
                            @ignore_user_abort(true);
                            if (!headers_sent()) {
                                header('Connection: close');
                                $len = ob_get_length();
                                if ($len !== false && $len >= 0) header('Content-Length: ' . $len);
                            }
                            while (ob_get_level() > 0) @ob_end_flush();
                            @flush();
                        }
                        $chMirror = curl_init('https://c.elsajana.com/api_index.php/sync/push');
                        curl_setopt($chMirror, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($chMirror, CURLOPT_POST, true);
                        curl_setopt($chMirror, CURLOPT_POSTFIELDS, $rawInput);
                        curl_setopt($chMirror, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . ($token ?: 'offline_token_fallback'),
                            'X-Forwarded-From-Local: 1'
                        ]);
                        curl_setopt($chMirror, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                        curl_setopt($chMirror, CURLOPT_CONNECTTIMEOUT, 2);
                        curl_setopt($chMirror, CURLOPT_TIMEOUT, 6);
                        curl_setopt($chMirror, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($chMirror, CURLOPT_SSL_VERIFYHOST, false);
                        curl_exec($chMirror);
                        curl_close($chMirror);
                    } catch (Throwable $mirrorErr) {
                        error_log('Cloud mirror sync push warning: ' . $mirrorErr->getMessage());
                    }
                });
            }

            jsonResponse(['success' => true, 'cloud_mirrored' => $cloudMirrored, 'message' => 'تمت المزامنة بنجاح']);
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

        // 3. Double-Entry Balance Check for Journal Vouchers
        if ($resource === 'journal_vouchers' && $input && isset($input['lines']) && is_array($input['lines'])) {
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($input['lines'] as $line) {
                $totalDebit += floatval($line['debit'] ?? 0);
                $totalCredit += floatval($line['credit'] ?? 0);
            }
            if (abs($totalDebit - $totalCredit) > 0.01) {
                jsonResponse(["error" => "لا يمكن حفظ القيد: مجموع المدين (" . number_format($totalDebit, 2) . ") لا يساوي مجموع الدائن (" . number_format($totalCredit, 2) . "). يجب أن يكون القيد متوازناً."], 422);
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
            
            if (!isset($input['createdBy']) && !empty($userId)) {
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
            $isFinRes = in_array($resource, ['sales_invoices', 'purchase_invoices', 'gl_entries', 'receipts', 'payments', 'journal_vouchers', 'single_receipts', 'single_payments']);
            $existingRow = null;
            if ($isFinRes) {
                $stmtEx = $pdo->prepare("SELECT status_val FROM `$resource` WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
                $stmtEx->execute(['tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
                $existingRow = $stmtEx->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            checkFinancialRules($pdo, $tenant_id, $resource, $input, $existingRow);

            if (!isset($input['createdBy']) && !empty($userId)) {
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
            $isFinDel = in_array($resource, ['sales_invoices', 'purchase_invoices', 'gl_entries', 'receipts', 'payments', 'journal_vouchers', 'single_receipts', 'single_payments']);
            $selCols = $isFinDel ? 'data, status_val' : 'data';
            $stmt = $pdo->prepare("SELECT $selCols FROM `$resource` WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
            $stmt->execute(['tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                checkFinancialRules($pdo, $tenant_id, $resource, json_decode($row['data'], true), $row);
                $dataArr = json_decode($row['data'], true);
                $dataArr['deleted'] = true;
                $newData = json_encode($dataArr, JSON_UNESCAPED_UNICODE);
                $updateStmt = $pdo->prepare("UPDATE `$resource` SET data = :data WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
                $updateStmt->execute(['data' => $newData, 'tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);

                // Cascading soft-delete: mark associated GL entries as deleted
                if ($resource === 'journal_vouchers') {
                    try {
                        $glStmt = $pdo->prepare("UPDATE `gl_entries` SET data = JSON_SET(data, '$.deleted', true) WHERE tenant_id = :tid AND JSON_EXTRACT(data, '$.voucherId') = :vid");
                        $glStmt->execute(['tid' => $tenant_id, 'vid' => $id]);
                    } catch (Exception $glEx) {
                        error_log('Cascading GL delete warning: ' . $glEx->getMessage());
                    }
                }
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
