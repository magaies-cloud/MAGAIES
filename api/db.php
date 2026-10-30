<?php
// -------------------------------------------------------
// توافقية دوال mbstring للخوادم التي لا تفعل إضافة mbstring افتراضياً
// -------------------------------------------------------
if (!function_exists('mb_strpos')) {
    function mb_strpos($haystack, $needle, $offset = 0, $encoding = null) {
        return strpos((string)$haystack, (string)$needle, (int)$offset);
    }
}
if (!function_exists('mb_stripos')) {
    function mb_stripos($haystack, $needle, $offset = 0, $encoding = null) {
        return stripos((string)$haystack, (string)$needle, (int)$offset);
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen($str, $encoding = null) {
        return preg_match_all('/./us', (string)$str, $m);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = null, $encoding = null) {
        preg_match_all('/./us', (string)$str, $ar);
        $slice = ($length === null) ? array_slice($ar[0], (int)$start) : array_slice($ar[0], (int)$start, (int)$length);
        return implode('', $slice);
    }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str, $encoding = null) {
        return strtolower((string)$str);
    }
}
if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper($str, $encoding = null) {
        return strtoupper((string)$str);
    }
}
if (!function_exists('mb_convert_encoding')) {
    function mb_convert_encoding($str, $to_encoding, $from_encoding = null) {
        return $str;
    }
}

// -------------------------------------------------------
// تحميل متغيرات البيئة من ملف .env
// -------------------------------------------------------
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

loadEnv(__DIR__ . '/../.env');

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
if ($host === 'localhost') $host = '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$db   = $_ENV['DB_NAME'] ?? 'magaees';
$ssl_ca = $_ENV['DB_SSL_CA'] ?? '';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
    PDO::ATTR_TIMEOUT            => 2,
];

// إضافة SSL إذا توفر مسار الشهادة
if ($ssl_ca && file_exists($ssl_ca)) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $ssl_ca;
    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
} else {
    // إذا لم تتوفر الشهادة، اتصل بدون تحقق SSL
    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}

$__scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? ''));
$__scriptBase = basename($__scriptPath);
$__isApiEndpoint = (strpos($__scriptPath, '/api/') !== false)
    || ($__scriptBase === 'api_index.php')
    || (strpos($__scriptBase, 'api_') === 0);
$__allowUiFallback = (defined('ALLOW_OFFLINE_DB_FALLBACK') && ALLOW_OFFLINE_DB_FALLBACK) || !$__isApiEndpoint;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // إذا فشل الاتصال الدائم (Persistent Socket)، نعيد المحاولة عبر اتصال مباشر جديد بدون التأثير على المستخدم
    if (!empty($options[PDO::ATTR_PERSISTENT])) {
        unset($options[PDO::ATTR_PERSISTENT]);
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e2) {
            error_log('DB Connection Error: ' . $e2->getMessage());
            if ($__allowUiFallback) {
                $pdo = null;
            } else {
                if (!headers_sent()) {
                    http_response_code(503);
                    header('Content-Type: application/json; charset=UTF-8');
                    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                }
                echo json_encode(['success' => false, 'error' => 'فشل الاتصال بقاعدة البيانات']);
                exit;
            }
        }
    } else {
        error_log('DB Connection Error: ' . $e->getMessage());
        if ($__allowUiFallback) {
            $pdo = null;
        } else {
            if (!headers_sent()) {
                http_response_code(503);
                header('Content-Type: application/json; charset=UTF-8');
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            }
            echo json_encode(['success' => false, 'error' => 'فشل الاتصال بقاعدة البيانات']);
            exit;
        }
    }
}



// -------------------------------------------------------
// دالة مركزية موحدة لتحديد رقم الشركة النشطة (Tenant Isolation)
// تعطي الأولوية القصوى للتوكن الموثق لمنع أي تداخل بين الشركات
// -------------------------------------------------------
if (!function_exists('resolveCurrentTenantId')) {
    function resolveCurrentTenantId($pdoConn = null, $inputData = null) {
        global $pdo, $GLOBAL_TENANT_ID;
        if ($pdoConn === null) {
            $pdoConn = $pdo;
        }
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start(['read_and_close' => true]);
        }

        // 1. استخراج التوكن الموثق (Authorization Header / Cookie / Payload)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $reqHeaders = apache_request_headers();
            $authHeader = $reqHeaders['Authorization'] ?? $reqHeaders['authorization'] ?? '';
        }
        $token = '';
        if (!empty($authHeader) && stripos($authHeader, 'Bearer ') === 0) {
            $token = trim(substr($authHeader, 7));
        }
        if (empty($token) && is_array($inputData) && !empty($inputData['auth_token'])) {
            $token = trim((string)$inputData['auth_token']);
        }
        if (empty($token)) {
            $token = trim((string)($_COOKIE['saas_token'] ?? $_COOKIE['authToken'] ?? $_COOKIE['mg_device_auth'] ?? ''));
        }

        if ($token !== '') {
            if (strpos($token, 'MGD1.') === 0) {
                $parts = explode('.', $token);
                if (count($parts) === 3) {
                    $secret = getenv('MAGAIEES_AUTH_HMAC_KEY') ?: 'ELSAJANA_MAGAIEES_PERSISTENT_MOBILE_SYNC_KEY_2026';
                    if (hash_equals(hash_hmac('sha256', $parts[1], $secret), $parts[2])) {
                        $dec = json_decode(base64_decode(strtr($parts[1], '-_', '+/')) ?: '', true);
                        if (!empty($dec['t']) && (int)$dec['t'] > 0) {
                            return (int)$dec['t'];
                        }
                    }
                }
            } elseif ($pdoConn) {
                try {
                    $stmt = $pdoConn->prepare("SELECT tenant_id FROM users WHERE token = ? AND (token_expires_at IS NULL OR token_expires_at > NOW()) AND (status = 'active' OR status IS NULL) LIMIT 1");
                    $stmt->execute([$token]);
                    $tokTenant = (int)$stmt->fetchColumn();
                    if ($tokTenant > 0) {
                        return $tokTenant;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 2. فحص الجلسة الموثقة (Session)
        if (!empty($_SESSION['mg_tenant_id']) && (int)$_SESSION['mg_tenant_id'] > 0) {
            return (int)$_SESSION['mg_tenant_id'];
        }
        if (!empty($_SESSION['user']['tenant_id']) && (int)$_SESSION['user']['tenant_id'] > 0) {
            return (int)$_SESSION['user']['tenant_id'];
        }
        if (!empty($_SESSION['tenant_id']) && (int)$_SESSION['tenant_id'] > 0) {
            return (int)$_SESSION['tenant_id'];
        }

        // 3. فحص المعاملات الصريحة أو الكوكيز أو النطاق الفرعي
        $candidates = [
            $_SERVER['HTTP_X_TENANT_ID'] ?? null,
            is_array($inputData) ? ($inputData['tenant_id'] ?? null) : null,
            $_GET['tenant_id'] ?? null,
            $_POST['tenant_id'] ?? null,
            $_COOKIE['currentTenantId'] ?? null,
            $_COOKIE['mg_tenant_id'] ?? null,
            $GLOBAL_TENANT_ID ?? null
        ];
        foreach ($candidates as $cand) {
            if ($cand !== null && $cand !== '' && $cand !== 'default' && is_numeric($cand) && (int)$cand > 0) {
                return (int)$cand;
            }
        }

        // 4. فحص النطاق الفرعي (Subdomain) أو باراميتر ?tenant= / ?company=
        if ($pdoConn) {
            $subParam = strtolower(trim((string)($_GET['tenant'] ?? $_GET['company'] ?? $_GET['sub'] ?? '')));
            if ($subParam === '') {
                $host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''))[0]);
                if (preg_match('/^([a-z0-9_-]+)\.elsajana\.com$/i', $host, $m)) {
                    $subParam = strtolower($m[1]);
                } elseif (preg_match('/^([a-z0-9_-]+)\.localhost$/i', $host, $m)) {
                    $subParam = strtolower($m[1]);
                }
            }
            if ($subParam !== '' && !in_array($subParam, ['www', 'app', 'erp', 'localhost'], true)) {
                try {
                    $stSub = $pdoConn->prepare("SELECT id FROM tenants WHERE LOWER(TRIM(subdomain)) = ? LIMIT 1");
                    $stSub->execute([$subParam]);
                    $subTid = (int)$stSub->fetchColumn();
                    if ($subTid > 0) {
                        return $subTid;
                    }
                } catch (\Throwable $e) {}
            }
        }

        return 1;
    }
}
