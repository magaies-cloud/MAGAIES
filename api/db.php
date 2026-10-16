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


