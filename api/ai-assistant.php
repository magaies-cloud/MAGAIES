<?php
header('Content-Type: application/json; charset=utf-8');

// Helper to load environment variables from .env
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

// 1. Get JSON Payload
$inputData = json_decode(file_get_contents('php://input'), true);
$userMessage = $inputData['message'] ?? '';
$systemContext = $inputData['context'] ?? '';
$imageBase64 = $inputData['image'] ?? null;

if (empty($userMessage)) {
    echo json_encode(['error' => 'الرسالة فارغة']);
    exit;
}

// 2. Setup API Key and Endpoint
$apiKey = getenv('GEMINI_API_KEY') ?: '';
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

// 3. Construct System Prompt
$systemPrompt = "أنت مساعد محاسبي ومحلل مالي خبير ضمن نظام ERP محلي للشركة. وظيفتك هي الإجابة عن أسئلة المستخدم بدقة واحترافية.
إذا سألك المستخدم عن معلومات متعلقة بالشركة (مثل السيولة، المبيعات، المخزون، أو رصيد عميل معين)، فاستخدم المعلومات الحقيقية المرفقة لك في الأسفل للإجابة بدقة. لا تقم باختراع أرقام من عندك إطلاقاً.
إذا كان السؤال عاماً في المحاسبة فأجب بشرح علمي دقيق ومرتب.

[مهم جداً - تنسيق الإجابة]:
يجب أن تكون إجابتك دائماً بصيغة JSON نظيف بالكامل ولا تحتوى على أي نصوص خارج الـ JSON. الهيكل المطلوب للإجابة هو:
{
  \"reply\": \"كتابة الرد للمستخدم باللغة العربية هنا، استخدم التنسيق اللطيف والرموز التعبيرية إذا لزم الأمر.\",
  \"action\": null
}

إذا طلب منك المستخدم إجراء عملية أو حركة مالية (مثل سند قبض، سند صرف، تحويل بين الخزائن، فاتورة مبيعات، أو فاتورة مشتريات)، يجب تعبئة حقل \"action\" بالبيانات المناسبة المستخرجة من كلام المستخدم كالتالي:

1. لسند القبض (مثال: قبضت 5000 من أحمد لخزينة النقدية):
{
  \"reply\": \"لقد قمت بتجهيز سند القبض بقيمة 5,000 ريال من العميل أحمد...\",
  \"action\": {
    \"type\": \"draft_receipt\",
    \"params\": {
      \"from_name\": \"أحمد\",
      \"to_name\": \"خزينة النقدية\",
      \"amount\": 5000,
      \"notes\": \"بيان السند\"
    }
  }
}

2. لسند الصرف (مثال: صرفت 2000 للمورد خالد من بنك الخرطوم):
{
  \"reply\": \"لقد قمت بتجهيز سند الصرف بقيمة 2,000 ريال للمورد خالد...\",
  \"action\": {
    \"type\": \"draft_payment\",
    \"params\": {
      \"from_name\": \"بنك الخرطوم\",
      \"to_name\": \"خالد\",
      \"amount\": 2000,
      \"notes\": \"بيان السند\"
    }
  }
}

3. للتحويل المالي بين الخزن (مثال: حول 3000 من الخزينة الرئيسية إلى بنك الخرطوم):
{
  \"reply\": \"لقد قمت بتجهيز تحويل مالي بقيمة 3,000 ريال...\",
  \"action\": {
    \"type\": \"draft_transfer\",
    \"params\": {
      \"from_name\": \"الخزينة الرئيسية\",
      \"to_name\": \"بنك الخرطوم\",
      \"amount\": 3000,
      \"notes\": \"تحويل مالي\"
    }
  }
}

4. لفاتورة المبيعات (مثال: اعمل فاتورة مبيعات للعميل أحمد بها صنف شاي الكمية 5 بسعر 120 وصنف سكر الكمية 2 بسعر 80):
{
  \"reply\": \"لقد قمت بتجهيز مسودة فاتورة المبيعات للعميل أحمد...\",
  \"action\": {
    \"type\": \"draft_sales_invoice\",
    \"params\": {
      \"customer_name\": \"أحمد\",
      \"notes\": \"شرح الفاتورة إن وجد\",
      \"items\": [
        { \"name\": \"شاي\", \"qty\": 5, \"price\": 120 },
        { \"name\": \"سكر\", \"qty\": 2, \"price\": 80 }
      ]
    }
  }
}

5. لفاتورة المشتريات (مثال: جهز فاتورة مشتريات من المورد شركة النيل بها صنف زيت الكمية 10 بسعر 400):
{
  \"reply\": \"لقد قمت بتجهيز مسودة فاتورة المشتريات من المورد شركة النيل...\",
  \"action\": {
    \"type\": \"draft_purchase_invoice\",
    \"params\": {
      \"supplier_name\": \"شركة النيل\",
      \"notes\": \"شرح الفاتورة إن وجد\",
      \"items\": [
        { \"name\": \"زيت\", \"qty\": 10, \"price\": 400 }
      ]
    }
  }
}

تنبيه: يجب إخراج كود JSON صالح تماماً.

[البيانات الحالية للشركة]:
" . $systemContext;

// 4. Prepare Payload for Gemini
$parts = [
    ["text" => $systemPrompt . "\n\nسؤال المستخدم: " . $userMessage]
];

// If an image is provided, append it to the parts
if ($imageBase64) {
    // Basic extraction if data:image/jpeg;base64, is included
    if (strpos($imageBase64, 'base64,') !== false) {
        $partsArr = explode('base64,', $imageBase64);
        $imageBase64 = $partsArr[1];
    }
    
    $parts[] = [
        "inline_data" => [
            "mime_type" => "image/jpeg",
            "data" => $imageBase64
        ]
    ];
}

$payload = [
    "contents" => [
        [
            "parts" => $parts
        ]
    ],
    "generationConfig" => [
        "responseMimeType" => "application/json"
    ]
];

// 5. Send Request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local dev

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlClose = curl_close($ch);

if ($response === false) {
    echo json_encode(['error' => 'cURL Error: ' . $curlError]);
    exit;
}

// 6. Parse and Return Response
$responseData = json_decode($response, true);

if ($httpCode >= 400) {
    // If Google API returns an error
    $errorMsg = $responseData['error']['message'] ?? 'حدث خطأ غير معروف في الاتصال بجوجل';
    echo json_encode(['error' => 'Google API Error: ' . $errorMsg]);
    exit;
}

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiReply = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
    
    // Clean potential markdown code blocks
    if (preg_match('/^```(?:json)?\s*(.*)\s*```$/s', $aiReply, $m)) {
        $aiReply = trim($m[1]);
    }
    
    $decoded = json_decode($aiReply, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['reply' => $aiReply, 'action' => null], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(['error' => 'استجابة غير متوقعة من الموديل', 'raw' => $responseData]);
}
?>
