<?php
// =============================================================
// MAGAIEES ERP - Save Gemini API Key Endpoint
// حفظ وتحديث مفتاح Gemini API في ملف .env بأمان
// =============================================================
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$apiKey = trim($input['api_key'] ?? '');

if (empty($apiKey)) {
    echo json_encode(['success' => false, 'error' => 'يرجى إدخال مفتاح Gemini API صالح']);
    exit;
}

// Basic format validation
if (strlen($apiKey) < 20) {
    echo json_encode(['success' => false, 'error' => 'مفتاح API المدخل قصير جداً أو غير صالح']);
    exit;
}

$envPath = __DIR__ . '/../.env';

// If .env doesn't exist, create it with local defaults and Gemini key
if (!file_exists($envPath)) {
    $content = "DB_HOST=localhost\nDB_PORT=3306\nDB_USER=root\nDB_PASS=\nDB_NAME=magaees\nGEMINI_API_KEY=" . $apiKey . "\n";
    if (file_put_contents($envPath, $content) === false) {
        echo json_encode(['success' => false, 'error' => 'تعذر إنشاء ملف .env']);
        exit;
    }
} else {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES);
    $found = false;
    $newLines = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
            $newLines[] = "GEMINI_API_KEY=" . $apiKey;
            $found = true;
        } else {
            $newLines[] = $line;
        }
    }
    if (!$found) {
        $newLines[] = "GEMINI_API_KEY=" . $apiKey;
    }
    if (file_put_contents($envPath, implode("\n", $newLines) . "\n") === false) {
        echo json_encode(['success' => false, 'error' => 'تعذر الكتابة في ملف .env']);
        exit;
    }
}

// Put in current process environment as well
$_ENV['GEMINI_API_KEY'] = $apiKey;
putenv("GEMINI_API_KEY=" . $apiKey);

echo json_encode([
    'success' => true,
    'message' => 'تم حفظ وتفعيل مفتاح Gemini API بنجاح في السيرفر'
], JSON_UNESCAPED_UNICODE);
