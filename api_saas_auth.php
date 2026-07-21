<?php
// -------------------------------------------------------
// SaaS Admin Authentication — server-side only
// -------------------------------------------------------
header("Content-Type: application/json; charset=UTF-8");
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin)) {
    $allowed = false;
    $exact_allowed = [
        'https://elsajana.com',
        'https://www.elsajana.com',
        'https://c.elsajana.com',
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
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// تحميل متغيرات البيئة
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
loadEnv(__DIR__ . '/.env');

// Read pass from JSON body OR fallback to POST
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$pass = '';
if (!empty($input['pass'])) {
    $pass = trim($input['pass']);
} elseif (!empty($_POST['pass'])) {
    $pass = trim($_POST['pass']);
}

// الكلمة السرية من .env أو القيمة الافتراضية
$correctToken = !empty($_ENV['SAAS_ADMIN_TOKEN']) ? $_ENV['SAAS_ADMIN_TOKEN'] : 'SUPER_ADMIN_SECRET_2026';
$correctPass  = !empty($_ENV['SAAS_ADMIN_PASSWORD']) ? $_ENV['SAAS_ADMIN_PASSWORD'] : $correctToken;

if (empty($pass)) {
    http_response_code(400);
    echo json_encode(['error' => 'كلمة المرور مطلوبة']);
    exit;
}

if (hash_equals($correctPass, $pass)) {
    echo json_encode(['success' => true, 'token' => $correctToken]);
} else {
    http_response_code(401);
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isLocal = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false);
    $errorMsg = 'كلمة المرور خاطئة';
    if ($isLocal) {
        $errorMsg .= ' (تلميح التطوير المحلي: الرمز الصحيح هو ' . $correctPass . ')';
    }
    echo json_encode(['error' => $errorMsg]);
}
