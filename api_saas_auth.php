<?php
// -------------------------------------------------------
// SaaS Admin Authentication — server-side only
// -------------------------------------------------------
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// تحميل متغيرات البيئة
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
    echo json_encode(['error' => 'كلمة المرور مطلوبة', 'debug_raw' => substr($rawInput, 0, 50)]);
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
