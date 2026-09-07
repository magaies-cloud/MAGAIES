<?php
// =============================================================
// MAGAIEES ERP - Financial OCR Scanner API
// مستخرج بيانات إشعارات التحويلات البنكية والشيكات بالذكاء الاصطناعي
// =============================================================
header('Content-Type: application/json; charset=utf-8');

// Load environment variables safely
if (!function_exists('loadEnvFinancialOCR')) {
    function loadEnvFinancialOCR($path) {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}
loadEnvFinancialOCR(__DIR__ . '/../.env');
loadEnvFinancialOCR(__DIR__ . '/../../.env');

$inputData = json_decode(file_get_contents('php://input'), true);
$imageBase64 = $inputData['image'] ?? null;
$customGuidelines = $inputData['custom_guidelines'] ?? '';
$documentHint = $inputData['doc_hint'] ?? 'auto'; // 'auto', 'transfer', 'cheque'

if (empty($imageBase64)) {
    echo json_encode(['success' => false, 'error' => 'لم يتم إرسال صورة المستند المالي']);
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

$apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';

// Simulation / Mock fallback for offline or unconfigured API key
if (empty($apiKey)) {
    // Return graceful mock fallback with informative error
    echo json_encode([
        'success' => false,
        'error' => 'مفتاح Gemini API Key غير مهيأ في السيرفر (.env). يرجى إضافة GEMINI_API_KEY في ملف .env لتفعيل المسح المباشر.',
        'is_mock_fallback' => true,
        'mock_data' => [
            'doc_type' => ($documentHint === 'cheque') ? 'cheque' : 'bank_transfer',
            'bank_name' => 'بنك الخرطوم (تجريبي)',
            'app_name' => 'بنكك (Bankak)',
            'transaction_ref' => 'TRX-' . rand(10000000, 99999999),
            'cheque_number' => ($documentHint === 'cheque') ? (string)rand(100000, 999999) : '',
            'amount' => 150000.0,
            'currency' => 'SDG',
            'date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'time' => date('H:i'),
            'sender_name' => 'محمد أحمد عبد الله',
            'sender_account' => '284****',
            'receiver_name' => 'مؤسسة السجانة التجارية',
            'receiver_account' => '195****',
            'notes' => 'سداد دفعة نقدية بالتحويل (تجريبي)',
            'confidence_score' => 85,
            'summary' => 'تحويل بنكي تجريبي بمبلغ 150,000 جنيه عبر بنكك'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$customGuidelineText = "";
if (!empty($customGuidelines)) {
    $customGuidelineText = "\nإرشادات وقواعد تدريبية إضافية مقدمة من المستخدم (يرجى مراعاتها بأولوية عالية):\n" . trim($customGuidelines) . "\n";
}

$systemPrompt = "أنت خبير فحص المستندات المالية المصرفية بالرؤية الحاسوبية (AI Financial Document OCR) متخصص في إشعارات التحويلات البنكية والشيكات المصرفية (خاصة البنوك السودانية والعربية مثل بنكك، فوري، أوكاش، بنك أمدرمان، بنك الخرطوم، بنك فيصل، والشيكات المصرفية الورقية).

المطلوب:
افحص صورة المستند المرفقة بدقة بالغة، وحدد ما إذا كانت:
1. إشعار تحويل بنكي (Bank Transfer Receipt): من تطبيق هاتف (بنكك، فوري، أوكاش، بنك النيل، إلخ) أو قسيمة إيداع بنكي.
2. شيك مصرفي (Bank Check): شيك ورقي أو صورة شيك معبأ بالبيانات.
3. غير محدد (Unknown): في حال كانت الصورة غير مالية.

معالم ونماذج الإشعارات الشائعة:
- بنكك (Bankak - بنك الخرطوم): بطاقة خضراء أو بيضاء بعبارة 'إشعار تحويل بنكي' أو 'بنك الخرطوم'، يظهر فيها رقم العملية / Transaction ID (أرقام متسلسلة عادة 7 إلى 10 أرقام)، المبلغ بالجنيه أو الدولار، اسم ورقم حساب المحول (من)، اسم ورقم حساب المستفيد (إلى)، والتاريخ والوقت.
- فوري (Fawry - بنك فيصل الإسلامي): يظهر اسم بنك فيصل الإسلامي أو تطبيق فوري، كود المعاملة أو المرجع، اسم المستفيد، المبلغ والتاريخ.
- أوكاش (Ocash - بنك أمدرمان الوطني): إشعار تحويل أو دفع من بنك أمدرمان الوطني، يتضمن الرقم المرجعي، المبلغ، وبيانات الأطراف.
- قسائم الإيداع البنكية الورقية: ختم البنك، رقم القسيمة، الحساب المودع له، المبلغ.
- الشيكات المصرفية: اسم البنك أعلى الشيك، رقم الشيك (أعلى اليمين أو في شريط الـ MICR السفلي بين علامتي '⑈' أو أرقام مطبوعة)، المبلغ بالأرقام (مسبوقاً برمز # أو =) والمبلغ بالحروف العربية (تفقيط)، اسم المستفيد بعد عبارة (ادفعوا لأمر / Pay to the order of)، تاريخ تحرير الشيك، وتاريخ الاستحقاق إن وجد.

قم باستخراج البيانات بدقة وتفريغها حصراً بصيغة JSON التالية وبدون أي نصوص تمهيدية:
{
  \"doc_type\": \"bank_transfer\" | \"cheque\" | \"deposit_slip\" | \"unknown\",
  \"bank_name\": \"اسم البنك (مثلاً: بنك الخرطوم، بنك فيصل الإسلامي، بنك أمدرمان الوطني، أو كما يظهر في الصورة)\",
  \"app_name\": \"اسم التطبيق إن كان إشعار موبايل (مثلاً: بنكك، فوري، أوكاش، تطبيق بنكي) أو فارغ للشيكات\",
  \"transaction_ref\": \"رقم العملية المرجعي / كود التحويل / رقم الإشعار\",
  \"cheque_number\": \"رقم الشيك فقط إن كان المستند شيكاً أو فارغ\",
  \"amount\": 0.0,
  \"currency\": \"SDG\" | \"USD\" | \"SAR\" | \"AED\",
  \"date\": \"YYYY-MM-DD (تاريخ التحويل أو تاريخ الشيك)\",
  \"due_date\": \"YYYY-MM-DD (تاريخ استحقاق الشيك إن وجد، وإلا نفس تاريخ الشيك)\",
  \"time\": \"HH:MM (وقت التحويل إن وجد)\",
  \"sender_name\": \"اسم المحول أو صاحب الشيك / الساحب\",
  \"sender_account\": \"رقم حساب المحول أو رقم حساب الشيك\",
  \"receiver_name\": \"اسم المستفيد / المستلم\",
  \"receiver_account\": \"رقم حساب المستفيد\",
  \"notes\": \"الغرض من التحويل أو البيان أو الملاحظة إن وجدت\",
  \"confidence_score\": 95,
  \"summary\": \"ملخص سطر واحد بالعربية (مثال: تحويل بنكي عبر بنكك بمبلغ 150,000 جنيه من محمد أحمد إلى مؤسسة السجانة)\"
}
" . $customGuidelineText . "
تنبيهات صارمة:
1. المبلغ يجب أن يكون رقماً حقيقياً (number) خالصاً بدون فواصل أو نصوص (مثلاً 150000 وليس '150,000').
2. استخرج رقم التحويلة أو رقم الشيك بالكامل دون نقصان.
3. التواريخ يجب أن تكون بصيغة YYYY-MM-DD إن أمكن قراءتها.
4. أخرج فقط كود JSON الصالح للتحليل مباشرة.";

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
    
    // Clean markdown json if returned
    if (strpos($rawText, '```json') !== false) {
        $rawText = preg_replace('/^```json\s*/', '', $rawText);
        $rawText = preg_replace('/\s*```$/', '', $rawText);
    } elseif (strpos($rawText, '```') !== false) {
        $rawText = preg_replace('/^```\s*/', '', $rawText);
        $rawText = preg_replace('/\s*```$/', '', $rawText);
    }
    
    $parsedData = json_decode($rawText, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($parsedData)) {
        // Ensure numeric amount
        $amount = isset($parsedData['amount']) ? (float)$parsedData['amount'] : 0;
        $parsedData['amount'] = $amount;
        
        echo json_encode([
            'success' => true,
            'data' => $parsedData
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'فشل تحليل الاستجابة البرمجية للمستند المالي',
            'raw' => $rawText
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'لم يتم استخراج بيانات صالحة من المستند']);
