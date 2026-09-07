<?php
// =============================================================
// MAGAIEES ERP - OCR Invoice Scanner API
// مستخرج بيانات فواتير المشتريات الورقية بالذكاء الاصطناعي
// =============================================================
header('Content-Type: application/json; charset=utf-8');

// Load environment variables
if (!function_exists('loadEnvOCR')) {
    function loadEnvOCR($path) {
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
loadEnvOCR(__DIR__ . '/../.env');

$inputData = json_decode(file_get_contents('php://input'), true);
$imageBase64 = $inputData['image'] ?? null;
$knownItems = $inputData['known_items'] ?? []; // Optional list of known system items for better matching

if (empty($imageBase64)) {
    echo json_encode(['success' => false, 'error' => 'لم يتم إرسال صورة الفاتورة']);
    exit;
}

// Clean base64 header if present
$mimeType = 'image/jpeg';
if (strpos($imageBase64, 'data:') === 0) {
    if (preg_match('/^data:(image\/[a-zA-Z0-9\+\-\.]+);base64,/', $imageBase64, $m)) {
        $mimeType = $m[1];
    }
    $partsArr = explode('base64,', $imageBase64);
    $imageBase64 = $partsArr[1] ?? $imageBase64;
}

$clientKey = trim($inputData['api_key'] ?? '');
$apiKey = !empty($clientKey) ? $clientKey : ($_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '');

if (empty($apiKey)) {
    // If no Gemini key is provided, return an informative response
    echo json_encode([
        'success' => false,
        'error' => 'مفتاح Gemini API Key غير مهيأ في السيرفر (.env). يرجى إضافة GEMINI_API_KEY للاستفادة من ميزة الرؤية الحاسوبية.',
        'is_mock_fallback' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$knownItemsContext = "";
if (!empty($knownItems) && is_array($knownItems)) {
    $sliceItems = array_slice($knownItems, 0, 50);
    $knownItemsContext = "\nقائمة الأصناف المعرفة في نظامنا (حاول مطابقة اسم الصنف المكتوب في الفاتورة مع أقرب صنف من هذه القائمة إن أمكن):\n" . implode("، ", $sliceItems) . "\n";
}

$systemPrompt = "أنت خبير فحص وقراءة فواتير المشتريات الورقية بالرؤية الحاسوبية (OCR) في نظام ERP تجاري.
قم بقراءة صورة الفاتورة المرفقة بدقة شديدة واستخراج جميع البيانات المحاسبية وتفريغها في هيكل JSON حصري ودقيق بدون أي نصوص تمهيدية.

الهيكل المطلوب حصراً:
{
  \"supplier_name\": \"اسم المورد أو اسم الشركة البائعة كما هو مكتوب في الفاتورة أو فارغ\",
  \"invoice_number\": \"رقم الفاتورة الورقية إن وجد\",
  \"invoice_date\": \"تاريخ الفاتورة بصيغة YYYY-MM-DD إن أمكن\",
  \"currency\": \"SDG أو USD أو العملة المكتوبة\",
  \"total_amount\": 0.0,
  \"discount\": 0.0,
  \"tax\": 0.0,
  \"paid_amount\": 0.0,
  \"items\": [
    {
      \"name\": \"اسم الصنف كما هو مكتوب في الفاتورة\",
      \"quantity\": 1.0,
      \"unit\": \"قطعة أو طن أو كيس أو الوحدة المكتوبة\",
      \"price\": 0.0,
      \"total\": 0.0
    }
  ],
  \"confidence_score\": 95
}
" . $knownItemsContext . "
تنبيهات هامة:
1. استخرج كل الأسطر الموجودة في جدول الفاتورة.
2. إذا كانت هناك كميات أو أسعار غير واضحة، حاول تقديرها بدقة من مجموع السطر.
3. تأكد أن كل الأرقام أرقام حقيقية (numbers) وليست نصوصاً.
4. أخرج فقط كود JSON صالح تماماً.";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $systemPrompt],
                [
                    "inline_data" => [
                        "mime_type" => $mimeType,
                        "data" => $imageBase64
                    ]
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "responseMimeType" => "application/json",
        "temperature" => 0.1
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['success' => false, 'error' => 'تعذر الاتصال بمحرك الذكاء الاصطناعي: ' . $curlError]);
    exit;
}

$responseData = json_decode($response, true);

if ($httpCode >= 400) {
    $errorMsg = $responseData['error']['message'] ?? 'خطأ في خدمة الذكاء الاصطناعي';
    echo json_encode(['success' => false, 'error' => 'خطأ من Google Gemini: ' . $errorMsg]);
    exit;
}

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $rawText = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
    if (preg_match('/^```(?:json)?\s*(.*)\s*```$/s', $rawText, $m)) {
        $rawText = trim($m[1]);
    }
    
    $parsedData = json_decode($rawText, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode([
            'success' => true,
            'data' => $parsedData
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'تعذر فك ترميز النتيجة المستخرجة',
            'raw' => $rawText
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'لم يستطع الذكاء الاصطناعي قراءة بيانات الفاتورة',
        'raw' => $responseData
    ]);
}
