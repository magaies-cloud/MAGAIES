<?php
// -------------------------------------------------------
// SaaS Admin Authentication — server-side only
// الكلمة السرية موجودة في .env فقط، لا في JavaScript
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

$input = json_decode(file_get_contents('php://input'), true);
$pass  = $input['pass'] ?? '';

// الكلمة السرية الصحيحة من ملف .env فقط
$correctToken = $_ENV['SAAS_ADMIN_TOKEN'] ?? '';

// التحقق من كلمة المرور (نستخدم SAAS_ADMIN_PASSWORD لو عُرِّفت، وإلا نقارن مع التوكن مباشرة)
$correctPass = $_ENV['SAAS_ADMIN_PASSWORD'] ?? $correctToken;

if (!$pass || !$correctPass) {
    http_response_code(400);
    echo json_encode(['error' => 'بيانات ناقصة']);
    exit;
}

if (strlen($correctPass) === strlen($pass) && hash_equals($correctPass, $pass)) {
    // إرسال التوكن للعميل فقط بعد التحقق الصحيح
    echo json_encode(['success' => true, 'token' => $correctToken]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'كلمة المرور خاطئة']);
}
