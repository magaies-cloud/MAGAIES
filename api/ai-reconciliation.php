<?php
// ====================================================================
// MAGAIEES ERP - AI Accounting Reconciliation & Comparison Engine
// محرك المقارنات والتطابق المحاسبي الذكي بالذكاء الاصطناعي (Gemini)
// ====================================================================
header('Content-Type: application/json; charset=utf-8');

// 1. Safe Environment Loader
if (!function_exists('loadEnvReconcile')) {
    function loadEnvReconcile($path) {
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
loadEnvReconcile(__DIR__ . '/../.env');
loadEnvReconcile(__DIR__ . '/../../.env');

require_once __DIR__ . '/ai-config.php';

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

$action = $data['action'] ?? 'reconcile';
$apiKey = getGeminiApiKey();

// ────────────────────────────────────────────────────────────────────
// A. MULTIMODAL DOCUMENT & VISION OCR EXTRACTION (أي صيغة: صور، PDF، Word، نص)
// ────────────────────────────────────────────────────────────────────
if ($action === 'extract_document') {
    $fileBase64 = $data['file_base64'] ?? '';
    $mimeType = $data['mime_type'] ?? 'image/jpeg';
    $textContent = $data['text_content'] ?? '';
    $fileName = $data['file_name'] ?? 'مستند مالي';

    if (empty($fileBase64) && empty($textContent)) {
        echo json_encode(['success' => false, 'error' => 'لم يتم إرسال صورة أو ملف أو نص لتحليله'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!empty($fileBase64)) {
        if (preg_match('/^data:([^;]+);base64,(.+)$/', $fileBase64, $matches)) {
            $mimeType = $matches[1];
            $fileBase64 = $matches[2];
        }
    }

    $ocrPrompt = <<<PROMPT
أنت خبير محاسبي جنائي عالمي ومتخصص في قراءة وتدقيق المستندات البنكية وكشوفات الحسابات والفواتير (World-Class AI Forensic Accountant & Financial OCR Specialist).
المطلوب منك: فحص وتدقيق المستند أو الصورة المرفقة، واستخراج جدول كافة الحركات المالية بدقة محاسبية متناهية.

ملاحظات بالغة الأهمية:
1. اقبل المستند أياً كان نوعه: صورة ملتقطة بكاميرا الجوال، كشف حساب PDF رسمي لبنك (مثل بنكك أو بنك فيصل أو الراجحي أو الأهلي)، إشعار سداد، شيك، فاتورة، رسائل واتساب، جدول Word أو Excel أو نص منسوخ.
2. حتى لو كانت الصورة باهتة، أو الإضاءة خافتة، أو الزاوية مائلة، أو الأرقام مهتزة قليلاً، استعن بالمنطق المحاسبي وتطابق الأرصدة (الرصيد السابق + المدين - الدائن = الرصيد الحالي) لإعادة استنتاج وقراءة الأرقام الحقيقية بدقة تامة.
3. فكك كل حركة مالية إلى:
   - date: تاريخ العملية بصيغة (YYYY-MM-DD) أو كما هو مدون في الكشف.
   - ref: رقم السند أو رقم التحويلة البنكية أو رقم الشيك أو رقم الفاتورة أو آخر 6 أرقام ظاهرة.
   - desc: البيان والشرح المالي والتفاصيل واسم الطرف الآخر أو تفاصيل التحويل.
   - debit: المبلغ المدين (لنا / قبض / توريد / إضافة). إذا لم يتوفر ضع 0.
   - credit: المبلغ الدائن (علينا / صرف / سداد / خصم). إذا لم يتوفر ضع 0.
   - balance: الرصيد الختامي بعد العملية إذا كان مدوناً، وإلا ضع 0.

المطلوب إخراج النتيجة بصيغة JSON محضة دون أي نص تمهيدي أو علامات Markdown:
{
  "account_name": "اسم الحساب أو المصنع أو الجهة الظاهرة في الكشف",
  "currency": "SDG أو USD أو العملة المكتشفة",
  "opening_balance": 0.00,
  "closing_balance": 0.00,
  "rows": [
    {
      "date": "2026-06-01",
      "ref": "88014567",
      "desc": "تحويل بنكك توريد دفعة أولى",
      "debit": 5000000,
      "credit": 0,
      "balance": 5000000
    }
  ]
}
PROMPT;

    $parts = [];
    if (!empty($fileBase64)) {
        $parts[] = [
            'inline_data' => [
                'mime_type' => $mimeType,
                'data' => $fileBase64
            ]
        ];
    }
    if (!empty($textContent)) {
        $ocrPrompt .= "\n\nالنص المرفق المستخرج أو المنسوخ من المستند:\n" . $textContent;
    }
    $parts[] = ['text' => $ocrPrompt];

    $modelsCascade = ['gemini-2.5-flash', 'gemini-3.5-flash-lite', 'gemini-3.5-flash'];
    $response = false;
    $httpCode = 0;
    $matchedModel = $modelsCascade[0];

    foreach ($modelsCascade as $idx => $curModel) {
        if ($idx > 0) usleep(300000);
        $matchedModel = $curModel;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $curModel . ":generateContent?key=" . $apiKey;

        $payload = [
            'contents' => [
                ['parts' => $parts]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 4000
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response !== false) {
            break;
        }
    }

    if ($httpCode === 200 && $response !== false) {
        $resJson = json_decode($response, true);
        $text = $resJson['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (strpos($text, '```json') !== false) {
            $text = preg_replace('/^```json\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
        } elseif (strpos($text, '```') !== false) {
            $text = preg_replace('/^```\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
        }
        $parsed = json_decode(trim($text), true);
        if ($parsed && isset($parsed['rows']) && is_array($parsed['rows'])) {
            echo json_encode([
                'success' => true,
                'source' => 'gemini_vision_ocr',
                'model' => $matchedModel,
                'data' => $parsed
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    echo json_encode([
        'success' => false,
        'error' => 'تعذر تحليل المستند بالذكاء الاصطناعي، يرجى المحاولة مرة أخرى أو التأكد من وضوح الصورة.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ────────────────────────────────────────────────────────────────────
// B. FORENSIC AUDIT & DISCREPANCY RECONCILIATION
// ────────────────────────────────────────────────────────────────────
$summaryA = $data['summary_a'] ?? [];
$summaryB = $data['summary_b'] ?? [];
$netVariance = floatval($data['net_variance'] ?? 0);
$unmatchedA = array_slice($data['unmatched_a'] ?? [], 0, 40);
$unmatchedB = array_slice($data['unmatched_b'] ?? [], 0, 40);
$variances = array_slice($data['variances'] ?? [], 0, 30);
$matchedCount = intval($data['matched_count'] ?? 0);
$totalA = intval($data['total_rows_a'] ?? 0);
$totalB = intval($data['total_rows_b'] ?? 0);

// Pre-calculate algorithmic fallback analysis in case of network/quota issues
function generateAlgorithmicAudit($summaryA, $summaryB, $netVariance, $unmatchedA, $unmatchedB, $variances) {
    $causes = [];
    $totalMissingA = 0;
    foreach ($unmatchedA as $row) {
        $amt = floatval($row['amount'] ?? ($row['debit'] ?? $row['credit'] ?? 0));
        $totalMissingA += $amt;
        $causes[] = [
            'type' => 'missing_in_factory',
            'title' => 'حركة مسجلة في حساباتك وغير مقيدة لدى المصنع',
            'amount' => $amt,
            'ref' => $row['ref'] ?? '-',
            'date' => $row['date'] ?? '-',
            'desc' => $row['desc'] ?? 'حركة مالية',
            'explanation' => "العملية بقيمة " . number_format($amt, 2) . " بتاريخ " . ($row['date'] ?? '-') . " (مرجع: " . ($row['ref'] ?? '-') . ") مسجلة في ملفك ولكنها لم تظهر في كشف المصنع."
        ];
    }

    $totalMissingB = 0;
    foreach ($unmatchedB as $row) {
        $amt = floatval($row['amount'] ?? ($row['debit'] ?? $row['credit'] ?? 0));
        $totalMissingB += $amt;
        $causes[] = [
            'type' => 'missing_in_my_records',
            'title' => 'حركة قيدها المصنع وغير مسجلة في دفاتر حساباتك',
            'amount' => $amt,
            'ref' => $row['ref'] ?? '-',
            'date' => $row['date'] ?? '-',
            'desc' => $row['desc'] ?? 'حركة مصنع',
            'explanation' => "المصنع قيد حركة بمبلغ " . number_format($amt, 2) . " بتاريخ " . ($row['date'] ?? '-') . " (مرجع: " . ($row['ref'] ?? '-') . ") لم يتم قيدها في دفاتر حساباتك (قد تكون عمولة أو فاتورة أو تسوية)."
        ];
    }

    foreach ($variances as $row) {
        $diff = floatval($row['diff'] ?? 0);
        $causes[] = [
            'type' => 'amount_variance',
            'title' => 'اختلاف في مبلغ العملية لنفس المرجع أو التاريخ',
            'amount' => $diff,
            'ref' => $row['ref'] ?? '-',
            'date' => $row['date'] ?? '-',
            'desc' => $row['desc'] ?? 'فارق تقييم',
            'explanation' => "يوجد تباين بمقدار " . number_format(abs($diff), 2) . " بين قيدك وقيد المصنع لنفس العملية (مسجل عندك: " . number_format($row['amt_a'] ?? 0, 2) . " | لدى المصنع: " . number_format($row['amt_b'] ?? 0, 2) . ")."
        ];
    }

    $nameA = $summaryA['name'] ?? 'حساباتي الخاصة';
    $nameB = $summaryB['name'] ?? 'كشف حساب المصنع';
    $balA = floatval($summaryA['closingBalance'] ?? 0);
    $balB = floatval($summaryB['closingBalance'] ?? 0);

    $execSummary = "بناءً على التدقيق الحسابي الآلي لكشف ($nameA) مقابل ($nameB)، تبيّن وجود فرق صافٍ إجمالي قدره (" . number_format(abs($netVariance), 2) . "). يرجع هذا الخلل الرئيسي إلى وجود " . count($unmatchedA) . " حركة مقيدة لديك لم يقيدها المصنع بإجمالي (" . number_format($totalMissingA, 2) . ")، و " . count($unmatchedB) . " حركة قيدها المصنع ولم تسجلها بإجمالي (" . number_format($totalMissingB, 2) . ")، إضافة إلى " . count($variances) . " حركة فيها اختلاف في المبالغ أو سعر الصرف.";

    $actions = [
        "طلب كشف حساب بنكي للمطابقة المباشرة للتحويلات المعلقة المذكورة أعلاه.",
        "مطابقة أرقام المراجع المتشابهة في آخر 4 إلى 6 أرقام وتأكيد قيدها لدى المصنع.",
        "إجراء قيد تسوية للمصروفات أو الفواتير المعتمدة التي سجلها المصنع ولم تسجلها.",
        "توحيد سعر الصرف المتفق عليه للتحويلات المقومة بالدولار لليوم المعني."
    ];

    $whatsapp = "السلام عليكم ورحمة الله وبركاته،\nالأخوة الكرام في إدارة حسابات " . $nameB . "،\nبعد إجراء المطابقة المحاسبية لكشف الحساب، تبين وجود فرق قدره " . number_format(abs($netVariance), 2) . ".\nنرجو التكرم بمراجعة الحركات المعلقة التالية:\n";
    $i = 1;
    foreach (array_slice($causes, 0, 6) as $c) {
        $whatsapp .= "{$i}) {$c['title']}: مبلغ " . number_format(abs($c['amount']), 2) . " بتاريخ " . ($c['date'] ?? '-') . " (مرجع: " . ($c['ref'] ?? '-') . ")\n";
        $i++;
    }
    $whatsapp .= "\nشاكرين ومقدرين حسن تعاونكم لحسم المطابقة وتصفية الرصيد.";

    return [
        'executive_summary' => $execSummary,
        'net_difference_explained' => "الرصيد في ملفك: " . number_format($balA, 2) . " | الرصيد في ملف المصنع: " . number_format($balB, 2) . " | صافي الفرق المطلوب حسمه: " . number_format(abs($netVariance), 2),
        'causes' => $causes,
        'corrective_actions' => $actions,
        'whatsapp_letter' => $whatsapp,
        'engine' => 'Algorithmic Forensic Engine (Fallback / Offline)'
    ];
}

$apiKey = getGeminiApiKey();
$model = getGeminiModel();

// If no API key, return the algorithmic forensic analysis immediately
if (empty($apiKey)) {
    $fallbackResult = generateAlgorithmicAudit($summaryA, $summaryB, $netVariance, $unmatchedA, $unmatchedB, $variances);
    echo json_encode([
        'success' => true,
        'data' => $fallbackResult,
        'is_mock_fallback' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Prepare comprehensive prompt for Gemini
$prompt = <<<PROMPT
أنت خبير تدقيق محاسبي جنائي ومحاسب قانوني معتمد (CPA & Forensic Auditor) متخصص في تسوية الحسابات ومطابقة كشوف الإكسيل بين الشركات والمصانع والموردين.
المهمة:
تم استلام ملفين للمطابقة:
1. الطرف الأول (حساباتي الخاصة / برنامج المحاسبة): {$summaryA['name']}
   - إجمالي السطور: {$summaryA['totalRows']}
   - إجمالي المدين: {$summaryA['totalDebit']}
   - إجمالي الدائن: {$summaryA['totalCredit']}
   - الرصيد الختامي: {$summaryA['closingBalance']}

2. الطرف الثاني (كشف حسابات المصنع / الشريك): {$summaryB['name']}
   - إجمالي السطور: {$summaryB['totalRows']}
   - إجمالي المدين: {$summaryB['totalDebit']}
   - إجمالي الدائن: {$summaryB['totalCredit']}
   - الرصيد الختامي: {$summaryB['closingBalance']}

الفرق الصافي بين الرصيدين: {$netVariance}

قواعد المطابقة المهمة التي يجب مراعاتها والتركيز عليها:
1. تاريخ العملية (مع التسامح في تأخير يوم إلى 3 أيام للتسوية البنكية).
2. رقم التحويلة أو الشيك: أحياناً يكتب أحد الطرفين آخر 4 إلى 6 أرقام فقط بينما يكتب المصنع الرقم كاملاً بـ 12 رقماً (Suffix Matching). إذا تطابقت أواخر الأرقام لنفس المبلغ، فهذه نفس العملية بالتأكيد.
3. المبالغ بالعملة المحلية مقابل تعادل الدولار والعملة الأجنبية مع سعر الصرف لذلك اليوم.
4. اكتشاف أين الخلل بالضبط وتفكيك الفرق الإجمالي.

قائمة الحركات غير المتطابقة والتباينات:
- حركات موجودة عندي وغير مقيدة لدى المصنع (أول 40 حركة):
PROMPT;

$prompt .= json_encode($unmatchedA, JSON_UNESCAPED_UNICODE) . "\n\n";
$prompt .= "- حركات قيدها المصنع وغير موجودة عندي (أول 40 حركة):\n";
$prompt .= json_encode($unmatchedB, JSON_UNESCAPED_UNICODE) . "\n\n";
$prompt .= "- حركات متطابقة مع وجود فارق في المبلغ أو التاريخ أو سعر الصرف:\n";
$prompt .= json_encode($variances, JSON_UNESCAPED_UNICODE) . "\n\n";

$prompt .= <<<FORMAT
المطلوب إخراج استجابتك بصيغة JSON حصراً بدون أي نصوص تمهيدية أو تنسيق Markdown، بالشكل التالي:
{
  "executive_summary": "تقرير محاسبي تنفيذي دقيق ومحكم يشرح حالة التطابق وسبب الفرق بالأرقام",
  "net_difference_explained": "شرح بالأرقام لرصيد الطرفين وصافي الفرق ومن هو المدين ومن الدائن",
  "causes": [
    {
      "type": "missing_in_factory | missing_in_my_records | amount_variance | fx_rate_diff",
      "title": "عنوان الخلل",
      "amount": 0.00,
      "ref": "رقم التحويلة أو الشيك",
      "date": "تاريخ الحركة",
      "desc": "بيان الحركة",
      "explanation": "الشرح المحاسبي الدقيق للخلل"
    }
  ],
  "ref_matching_insights": "ملاحظات حول أرقام التحويلات التي تطابقت بأواخر الأرقام (آخر 5 أو 6 أرقام) للتأكيد للمصنع",
  "corrective_actions": [
    "خطوة تصحيحية 1",
    "خطوة تصحيحية 2"
  ],
  "whatsapp_letter": "نص رسالة رسمية مهذبة ومفصلة ترسل للمصنع عبر واتساب أو إيميل لحسم الفروقات وتصفية الحساب"
}
FORMAT;

$modelsCascade = [
    $model,
    'gemini-3.5-flash-lite',
    'gemini-2.5-flash',
    'gemini-3.5-flash',
    'gemini-flash-latest'
];
$modelsCascade = array_values(array_unique(array_filter($modelsCascade)));

$response = false;
$httpCode = 0;
$usedModel = $modelsCascade[0];

foreach ($modelsCascade as $idx => $curModel) {
    if ($idx > 0) usleep(250000);
    $usedModel = $curModel;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $curModel . ":generateContent?key=" . $apiKey;

    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.1,
            'maxOutputTokens' => 3000
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
    curl_close($ch);

    if ($httpCode === 200 && $response !== false) {
        break;
    }
}

if ($httpCode === 200 && $response !== false) {
    $resJson = json_decode($response, true);
    $text = $resJson['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // Clean markdown
    if (strpos($text, '```json') !== false) {
        $text = preg_replace('/^```json\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
    } elseif (strpos($text, '```') !== false) {
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
    }

    $parsed = json_decode(trim($text), true);
    if (is_array($parsed) && !empty($parsed['executive_summary'])) {
        $parsed['engine'] = "Google Gemini AI ({$usedModel})";
        echo json_encode([
            'success' => true,
            'data' => $parsed
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Fallback to deterministic forensic analysis if Gemini response couldn't be parsed
$fallbackResult = generateAlgorithmicAudit($summaryA, $summaryB, $netVariance, $unmatchedA, $unmatchedB, $variances);
echo json_encode([
    'success' => true,
    'data' => $fallbackResult,
    'is_mock_fallback' => true
], JSON_UNESCAPED_UNICODE);
