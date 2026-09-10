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

// Save OCR properties endpoint
if ((isset($inputData['action']) && $inputData['action'] === 'save_ocr_props') || (isset($_POST['action']) && $_POST['action'] === 'save_ocr_props')) {
    try {
        require_once __DIR__ . '/../db_bridge.php';
        $pdo = getDBConnection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS ui_preferences (
            tenant_id VARCHAR(50) NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value LONGTEXT,
            PRIMARY KEY (tenant_id, pref_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $val = $inputData['ocr_props'] ?? $_POST['ocr_props'] ?? '{}';
        if (is_array($val)) $val = json_encode($val);
        $stmt = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES ('1', 'ocr_left_col_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
        $stmt->execute([$val, $val]);
        $stmt2 = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES ('default', 'ocr_left_col_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
        $stmt2->execute([$val, $val]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$imageBase64 = $inputData['image'] ?? null;
$customGuidelines = $inputData['custom_guidelines'] ?? '';
$documentHint = $inputData['doc_hint'] ?? 'auto'; // 'auto', 'transfer', 'cheque'

if (empty($imageBase64)) {
    echo json_encode(['success' => false, 'error' => 'لم يتم إرسال صورة المستند المالي']);
    exit;
}

// Payload size safety check (max 8MB base64) to prevent memory exhaustion
if (strlen($imageBase64) > 8 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'حجم الصورة المرسلة كبير جداً، يرجى استخدام صورة بحجم أقل من 6 ميجابايت']);
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
require_once __DIR__ . '/ai-config.php';

$clientKey = trim($inputData['api_key'] ?? '');
$apiKey = getGeminiApiKey($clientKey);
$model = getGeminiModel();

// Simulation / Mock fallback for offline or unconfigured API key
if (empty($apiKey)) {
    echo json_encode([
        'success' => false,
        'error' => 'مفتاح Gemini API Key غير مهيأ في السيرفر (.env). يرجى إضافة GEMINI_API_KEY لتفعيل المسح المباشر.',
        'is_mock_fallback' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . $apiKey;

$serverGuidelinesFile = __DIR__ . '/financial_ocr_guidelines.txt';
$serverTemplatesFile = __DIR__ . '/financial_templates.json';

$allGuidelines = [];
if (!empty($customGuidelines)) {
    $allGuidelines[] = trim($customGuidelines);
}
if (file_exists($serverGuidelinesFile)) {
    $sg = trim(file_get_contents($serverGuidelinesFile));
    if (!empty($sg) && !in_array($sg, $allGuidelines)) {
        $allGuidelines[] = $sg;
    }
}

$templatesText = "";
if (file_exists($serverTemplatesFile)) {
    $savedTemplates = json_decode(file_get_contents($serverTemplatesFile), true);
    if (is_array($savedTemplates) && count($savedTemplates) > 0) {
        $templatesText .= "\nقواعد النماذج المدربة في النظام (يرجى تطبيقها لمطابقة نوع المستند والبنك بدقة):\n";
        foreach ($savedTemplates as $t) {
            $templatesText .= "- [نموذج: " . ($t['name'] ?? '') . " | بنك: " . ($t['bank'] ?? '') . " | نوع: " . ($t['type'] ?? '') . "]: " . ($t['rules'] ?? '') . "\n";
        }
    }
}

$customGuidelineText = "";
if (!empty($allGuidelines)) {
    $customGuidelineText = "\nإرشادات وقواعد تدريبية إضافية مقدمة من المستخدم:\n" . implode("\n", $allGuidelines) . "\n";
}
$customGuidelineText .= $templatesText;

// Function to robustly clean and extract financial amount (handles Arabic numbers, currencies, commas, and regex fallback)
if (!function_exists('cleanFinancialAmount')) {
    function cleanFinancialAmount($val, $rawText = '') {
        $str = '';
        if ($val !== null && $val !== '') {
            $str = (string)$val;
        }

        // Convert Eastern Arabic-Indic numerals (٠-٩) and Persian (۰-۹) to standard Western digits (0-9)
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $latin = ['0','1','2','3','4','5','6','7','8','9'];
        $str = str_replace($arabic, $latin, $str);
        $str = str_replace($persian, $latin, $str);

        if (preg_match('/[0-9]/', $str)) {
            // Find numeric substring
            if (preg_match('/([0-9][0-9,.\s]*[0-9]|[0-9]+)/', $str, $m)) {
                $rawNum = trim($m[1]);
                if (strpos($rawNum, '.') !== false && strpos($rawNum, ',') !== false) {
                    if (strrpos($rawNum, ',') > strrpos($rawNum, '.')) {
                        // European/Sudanese style: 150.000,00 -> comma is decimal
                        $rawNum = str_replace('.', '', $rawNum);
                        $rawNum = str_replace(',', '.', $rawNum);
                    } else {
                        // Standard style: 150,000.00 -> dot is decimal
                        $rawNum = str_replace(',', '', $rawNum);
                    }
                } else {
                    $cleaned = str_replace([' ', ','], '', $rawNum);
                    if (substr_count($cleaned, '.') > 1) {
                        $parts = explode('.', $cleaned);
                        $last = array_pop($parts);
                        $cleaned = implode('', $parts) . '.' . $last;
                    } elseif (preg_match('/^(\d+)\.(\d{3})$/', $cleaned, $mp)) {
                        // 150.000 -> 150000 (thousands separator mistaken as dot)
                        $cleaned = $mp[1] . $mp[2];
                    }
                    $rawNum = $cleaned;
                }
                $num = floatval($rawNum);
                if ($num > 0) {
                    return $num;
                }
            }
        }

        // Fallback: If amount is 0 or wasn't found in $val, search $rawText with regex
        if (!empty($rawText)) {
            $cleanRaw = str_replace($arabic, $latin, (string)$rawText);
            $cleanRaw = str_replace($persian, $latin, $cleanRaw);

            // Pattern 1: JSON field "amount": "150,000" or "amount": 150000
            if (preg_match('/"amount"\s*:\s*"?([0-9][0-9,.\s]*)/i', $cleanRaw, $m)) {
                $cleaned = str_replace([' ', ','], '', $m[1]);
                $num = floatval($cleaned);
                if ($num > 0) return $num;
            }

            // Pattern 2: Arabic text mentioning المبلغ / مبلغ / قيمة followed by numbers
            if (preg_match('/(?:المبلغ|مبلغ|بمبلغ|قيمة|ادفعوا)\s*(?:قدره|:)?\s*([0-9][0-9,.\s]*)/u', $cleanRaw, $m)) {
                $cleaned = str_replace([' ', ','], '', $m[1]);
                $num = floatval($cleaned);
                if ($num > 0) return $num;
            }

            // Pattern 3: Number followed by currency (SDG, ج.س, جنيه, دولار)
            if (preg_match('/([0-9][0-9,.]+)\s*(?:SDG|ج\.س|جنيه|ريال|دولار)/ui', $cleanRaw, $m)) {
                $cleaned = str_replace([' ', ','], '', $m[1]);
                $num = floatval($cleaned);
                if ($num > 0) return $num;
            }
        }

        return 0.0;
    }
}

$systemPrompt = "أنت خبير فحص المستندات المالية المصرفية بالرؤية الحاسوبية (AI Financial Document OCR) متخصص في إشعارات التحويلات البنكية والشيكات المصرفية (خاصة البنوك السودانية والعربية مثل بنكك، فوري، أوكاش، بنك أمدرمان، بنك الخرطوم، بنك فيصل، والشيكات المصرفية الورقية).

المطلوب:
افحص صورة المستند المرفقة بدقة بالغة، وحدد ما إذا كانت:
1. إشعار تحويل بنكي معتمد في النماذج (Approved Bank Transfer Receipt): من تطبيق هاتف معتمد (بنكك، فوري، أوكاش، بنك النيل، إلخ) أو قسيمة إيداع بنكي مطابقة.
2. شيك مصرفي معتمد في النماذج (Approved Bank Check): شيك ورقي أو صورة شيك معبأ بالبيانات ومطابق.
3. صورة أو مستند غير مالي (Non-Financial) أو غير معتمد في النماذج (Unapproved): في حال كانت الصورة لسيارة، مركبة، منظر طبيعي، صورة شخصية، بضاعة، أو أي شيء غير معتمد في النماذج المصرفية المعتمدة.

معالم ونماذج الإشعارات والشيكات:
- بنكك (Bankak - بنك الخرطوم): بطاقة خضراء أو بيضاء بعبارة 'إشعار تحويل بنكي' أو 'بنك الخرطوم'، يظهر فيها رقم العملية / Transaction ID (أرقام متسلسلة عادة 7 إلى 10 أرقام)، المبلغ بالجنيه أو الدولار، اسم ورقم حساب المحول (من)، اسم ورقم حساب المستفيد (إلى)، والتاريخ والوقت.
- فوري (Fawry - بنك فيصل الإسلامي): يظهر اسم بنك فيصل الإسلامي أو تطبيق فوري، كود المعاملة أو المرجع، اسم المستفيد، المبلغ والتاريخ.
- أوكاش (Ocash - بنك أمدرمان الوطني): إشعار تحويل أو دفع من بنك أمدرمان الوطني، يتضمن الرقم المرجعي، المبلغ، وبيانات الأطراف.
- قسائم الإيداع البنكية الورقية: ختم البنك، رقم القسيمة، الحساب المودع له، المبلغ.
- الشيكات المصرفية: اسم البنك أعلى الشيك، رقم الشيك (أعلى اليمين أو في شريط الـ MICR السفلي بين علامتي '⑈' أو أرقام مطبوعة)، المبلغ بالأرقام (مكتوب بين علامتي # أو = أو *) والمبلغ بالحروف العربية (تفقيط: ادفعوا لأمر ... مبلغ وقدره ...)، اسم المستفيد، تاريخ تحرير الشيك، وتاريخ الاستحقاق إن وجد.

قم باستخراج البيانات بدقة وتفريغها حصراً بصيغة JSON التالية وبدون أي نصوص تمهيدية:
{
  \"is_financial_doc\": true,
  \"is_approved_template\": true,
  \"doc_type\": \"bank_transfer\" | \"cheque\" | \"deposit_slip\" | \"non_financial\" | \"unapproved\",
  \"rejection_reason\": \"سبب عدم الاعتماد إذا كانت الصورة لسيارة، بضاعة، مستند شخصي، مستند غير معتمد في النماذج، أو إذا كان المستند غير واضح والمبلغ غير ظاهر (مثال: صورة سيارة، مستند غير معتمد في النماذج، المستند غير واضح والمبلغ غير ظاهر) أو فارغ\",
  \"bank_name\": \"اسم البنك (مثلاً: بنك الخرطوم، بنك فيصل الإسلامي، بنك أمدرمان الوطني، أو كما يظهر في الصورة)\",
  \"app_name\": \"اسم التطبيق إن كان إشعار موبايل (مثلاً: بنكك، فوري، أوكاش، تطبيق بنكي) أو فارغ للشيكات\",
  \"transaction_ref\": \"رقم العملية المرجعي / كود التحويل / رقم الإشعار\",
  \"cheque_number\": \"رقم الشيك فقط إن كان المستند شيكاً أو فارغ\",
  \"amount\": 0.0,
  \"amount_in_words\": \"المبلغ بالحروف العربية تفقيطاً إن وجد في الشيك (مثال: فقط خمسون ألف جنيه سوداني لا غير) أو فارغ\",
  \"currency\": \"SDG\" | \"USD\" | \"SAR\" | \"AED\",
  \"date\": \"YYYY-MM-DD (تاريخ التحويل أو تاريخ الشيك)\",
  \"due_date\": \"YYYY-MM-DD (تاريخ استحقاق الشيك إن وجد، وإلا نفس تاريخ الشيك)\",
  \"time\": \"HH:MM (وقت التحويل إن وجد)\",
  \"sender_name\": \"اسم المحول أو صاحب الشيك / الساحب\",
  \"sender_account\": \"رقم حساب المحول المكتوب في الإشعار بالعربية 'من حساب' أو بالإنجليزية 'From Account'\",
  \"receiver_name\": \"اسم المستفيد / المستلم المكتوب في الإشعار بالعربية 'المستلم' أو 'إلى' أو 'Beneficiary' أو 'المحول إليه' ولا تهمله أبداً\",
  \"receiver_phone\": \"رقم هاتف أو محمول المستلم المكتوب في الإشعار أو اتركه فارغاً تماماً \"\" ولا تكتب N/A أبداً\",
  \"receiver_account\": \"رقم حساب المستفيد إن وجد أو رقم المحمول\",
  \"notes\": \"الغرض من التحويل أو البيان أو الملاحظة إن وجدت أو اتركه فارغاً تماماً \"\" ولا تكتب N/A أبداً\",
  \"confidence_score\": 95,
  \"summary\": \"ملخص سطر واحد بالعربية (مثال: تحويل بنكي عبر بنكك بمبلغ 150,000 جنيه من محمد أحمد إلى مؤسسة السجانة)\"
}
" . $customGuidelineText . "
تنبيهات صارمة:
1. المبلغ الإجمالي للمعاملة يجب أن يكون رقماً حقيقياً خالصاً (number) في حقل \"amount\" (مثلاً 150000 أو 75500.50 بدون فواصل وبدون نصوص عملة). في الشيكات استخرج الرقم من خانة المبلغ بالأرقام ومن التفقيط، وفي الإشعارات من خانة المبلغ. لا تترك المبلغ 0 أبداً إذا كان هناك أي مبلغ مكتوب في المستند!
2. استخرج رقم التحويلة أو رقم الشيك بالكامل دون نقصان.
3. التواريخ يجب أن تكون بصيغة YYYY-MM-DD إن أمكن قراءتها.
4. بالنسبة لبيانات الأطراف والحسابات (تنبيه صارم لمنع عكس الحسابات نهائياً):
- رقم الحساب المكتوب في خانة 'من حساب' أو 'المحول منه' أو 'From Account' ضعه حصراً في \"sender_account\" بنفس ترتيب أرقامه الأصلي من اليسار لليمين دون عكس.
- رقم الحساب المكتوب في خانة 'إلى حساب' أو 'المستفيد' أو 'المستلم' أو 'To Account' أو 'Account Number' ضعه حصراً في \"receiver_account\" بنفس ترتيب أرقامه الأصلي من اليسار لليمين دون عكس.
- إياك نهائياً أن تعكس رقمي الحسابين بين من وإلى، أو تضع حساب المستلم مكان حساب المرسل والعكس!
- انقل اسم المستلم / المستفيد المكتوب في خانة 'المستلم' أو 'إلى' أو 'Beneficiary' أو 'المحول إليه' بالكامل دون إهماله نهائياً إلى receiver_name ولا تتركه فارغاً إذا كان موجوداً في الصورة.
- انقل رقم الهاتف المكتوب في خانة 'المحمول' أو 'Mobile' إلى receiver_phone، وإذا لم يكن موجوداً اتركه فارغاً تماماً ولا تكتب N/A أو null مطلقاً.
- انقل التعليق أو الغرض إن وجد إلى notes، وإذا لم يكن موجوداً اتركه فارغاً تماماً ولا تكتب N/A أو null مطلقاً.
5. أخرج فقط كود JSON الصالح للتحليل مباشرة.";

if (!empty($inputData['retry_amount'])) {
    $systemPrompt .= "\n\n⚠️ تنبيه عالي الأهمية (إعادة فحص تلقائي للمبلغ): في المحاولة السابقة ظهر المبلغ صفر (0)؛ دقق في الصورة كاملة، افحص خانة المبلغ بالأرقام وخانة التفقيط بالحروف وأي رقم متبوع بـ SDG أو جنيه واستخرج المبلغ الحقيقي بدقة تامة ولا تتركه صفراً إذا كان موجوداً.";
}

if (!empty($inputData['is_refresh'])) {
    $systemPrompt .= "\n\n⚠️ إعادة تدقيق شاملة بطلب خاص من المحاسب (Manual Refresh): المحاسب يشك في صحة قراءة سابقة (مثلاً تشابه الأرقام 3 و 9 أو 8 و 0، أو خطأ في رقم العملية أو اسم العميل). دقق بتركيز فائق واقرأ الأرقام والحروف بأعلى درجات الدقة والتطابق مع الصورة الأصلية.";
}

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
        "temperature" => 0.1,
        "maxOutputTokens" => 600
    ]
];

// Multi-Model Cascade: automatically fallback across active models if primary hits rate limit (429), spikes (503), or quota exhaustion
// Each model has its own independent quota, so 429 on one model does NOT mean all others are exhausted
$modelsCascade = [
    'gemini-3.1-flash-lite',
    $model,
    'gemini-3.7-flash',
    'gemini-flash-lite-latest',
    'gemini-3.5-flash-lite',
    'gemini-2.5-flash',
    'gemini-3.5-flash',
    'gemini-3.8-flash',
    'gemini-3-flash-preview'
];
$modelsCascade = array_values(array_unique(array_filter($modelsCascade)));

$response = false;
$httpCode = 0;
$curlError = '';
$lastErrorMsg = '';
$usedModel = $modelsCascade[0] ?? $model;

foreach ($modelsCascade as $idx => $currentModel) {
    if ($idx > 0) {
        // Brief pause between fallback attempts to be respectful to the API
        usleep(300000); // 0.3s
    }
    $usedModel = $currentModel;

    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $currentModel . ":generateContent?key=" . $apiKey;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && $response !== false) {
        // Success with this model!
        break;
    }

    if ($response !== false) {
        $respJson = json_decode($response, true);
        $lastErrorMsg = $respJson['error']['message'] ?? "HTTP {$httpCode}";
    } else {
        $lastErrorMsg = $curlError;
    }

    // On 429, 503, or 404, continue trying next model in cascade (each model has independent quota)
}

if ($response === false) {
    echo json_encode(['success' => false, 'error' => 'تعذر الاتصال بمحرك الذكاء الاصطناعي: ' . $curlError], JSON_UNESCAPED_UNICODE);
    exit;
}

$responseData = json_decode($response, true);

if ($httpCode >= 400) {
    $errorMsg = $responseData['error']['message'] ?? $lastErrorMsg ?? 'خطأ في خدمة الذكاء الاصطناعي';
    $isQuota = ($httpCode === 429 || strpos($errorMsg, 'quota') !== false || strpos($errorMsg, 'Resource has been exhausted') !== false);
    echo json_encode([
        'success' => false,
        'error' => $isQuota ? 'تم استنفاد حد الطلبات لـ Gemini مؤقتاً (Quota / Rate Limit). يرجى الانتظار بضع ثوانٍ ثم إعادة المحاولة أو إضافة مفتاح API خاص بك في زر [المفتاح].' : ('خطأ من Google Gemini: ' . $errorMsg),
        'http_code' => $httpCode,
        'is_quota_exceeded' => $isQuota
    ], JSON_UNESCAPED_UNICODE);
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
        // Robust financial amount extraction and cleaning
        $amount = cleanFinancialAmount($parsedData['amount'] ?? null, $rawText);
        $parsedData['amount'] = $amount;
        
        $hasValidFinancialSignals = ($amount > 0.0) 
            || !empty($parsedData['transaction_ref']) 
            || !empty($parsedData['cheque_number']) 
            || (!empty($parsedData['bank_name']) && $parsedData['bank_name'] !== 'غير محدد')
            || !empty($parsedData['sender_account'])
            || !empty($parsedData['receiver_account']);

        $docType = $parsedData['doc_type'] ?? '';

        if ($hasValidFinancialSignals || ($docType !== 'non_financial' && $docType !== 'unapproved')) {
            $parsedData['is_financial_doc'] = true;
            $parsedData['is_approved_template'] = true;
            $parsedData['is_non_financial'] = false;
            $parsedData['is_unapproved'] = false;
            $parsedData['allow_save'] = true;
            $parsedData['is_unclear'] = false;
            $parsedData['is_unclear_amount'] = ($amount <= 0.0);
            if ($docType === 'non_financial' || $docType === 'unapproved' || empty($docType)) {
                $parsedData['doc_type'] = 'bank_transfer';
            }
            if ($parsedData['amount'] > 0.0) {
                $parsedData['rejection_reason'] = '';
            }
        } else {
            $parsedData['is_financial_doc'] = false;
            $parsedData['is_approved_template'] = false;
            $parsedData['is_non_financial'] = true;
            $parsedData['is_unapproved'] = true;
            $parsedData['allow_save'] = false;
            $parsedData['amount'] = 0.0;
            if (empty($parsedData['rejection_reason'])) {
                $parsedData['rejection_reason'] = 'صورة أو مستند غير مالي (صورة شخصية أو بضاعة أو سيارة)';
            }
        }

        // Handle zero amount ONLY when no valid amount could be parsed from any section
        if ($parsedData['amount'] <= 0.0) {
            $parsedData['is_unclear_amount'] = true;
            $parsedData['is_unclear'] = true;
            if (empty($parsedData['rejection_reason'])) {
                $parsedData['rejection_reason'] = 'المستند غير واضح، المبلغ غير ظاهر (0.00 SDG)';
            }
        }

        // Also ensure transaction_ref and cheque_number are cleanly formatted
        if (!empty($parsedData['transaction_ref'])) {
            $parsedData['transaction_ref'] = trim((string)$parsedData['transaction_ref']);
        }

        // Smart Regex Fallback for transaction_ref if empty
        if (empty($parsedData['transaction_ref'])) {
            $arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            $persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            $latinDigits = ['0','1','2','3','4','5','6','7','8','9'];
            $searchPool = ($parsedData['notes'] ?? '') . "\n" . ($parsedData['summary'] ?? '') . "\n" . $rawText;
            $searchPoolClean = str_replace($arabicDigits, $latinDigits, $searchPool);
            $searchPoolClean = str_replace($persianDigits, $latinDigits, $searchPoolClean);

            if (preg_match('/(?:رقم\s*(?:العملية|المعاملة|المرجع|الإشعار|التحويل|الحوالة)|Transaction\s*(?:ID|No|Ref|Number)|Txn\s*ID|Ref\s*(?:No|Number)?)[^\d:]*[:\s#]*([0-9A-Za-z-]{5,30})/ui', $searchPoolClean, $mRef)) {
                $parsedData['transaction_ref'] = trim($mRef[1]);
            } elseif (preg_match('/\b(202[0-9]{8,14}|[0-9]{9,16})\b/', $searchPoolClean, $mStandalone)) {
                $cand = trim($mStandalone[1]);
                if ($cand !== ($parsedData['sender_account'] ?? '') && $cand !== ($parsedData['receiver_account'] ?? '') && (floatval($cand) != ($parsedData['amount'] ?? 0))) {
                    $parsedData['transaction_ref'] = $cand;
                }
            }
        }

        if (!empty($parsedData['cheque_number'])) {
            $parsedData['cheque_number'] = trim((string)$parsedData['cheque_number']);
        }
        if (!empty($parsedData['sender_account'])) {
            $parsedData['sender_account'] = trim((string)$parsedData['sender_account']);
        }
        if (!empty($parsedData['receiver_account'])) {
            $parsedData['receiver_account'] = trim((string)$parsedData['receiver_account']);
        }

        // Ensure receiver_name is never lost if present in image/text
        if (empty($parsedData['receiver_name'])) {
            $searchPoolName = ($parsedData['notes'] ?? '') . "\n" . ($parsedData['summary'] ?? '') . "\n" . $rawText;
            if (preg_match('/(?:المستلم|المستفيد|إلى|Beneficiary|To|المحول إليه)\s*[:\-]\s*([^\r\n,]{3,60})/ui', $searchPoolName, $mRec)) {
                $candName = trim($mRec[1]);
                if (!preg_match('/^(حساب|Account|\d+)/i', $candName)) {
                    $parsedData['receiver_name'] = $candName;
                }
            }
        }

        // Clean N/A or null from receiver_phone and notes
        if (!empty($parsedData['receiver_phone'])) {
            $parsedData['receiver_phone'] = trim((string)$parsedData['receiver_phone']);
            if (preg_match('/^(n\/?a|null|none|غير\s*متوفر|لا\s*يوجد|-)$/i', $parsedData['receiver_phone'])) {
                $parsedData['receiver_phone'] = '';
            }
        } else {
            $parsedData['receiver_phone'] = '';
        }

        if (!empty($parsedData['notes'])) {
            $parsedData['notes'] = trim((string)$parsedData['notes']);
            if (preg_match('/^(n\/?a|null|none|-)$/i', $parsedData['notes'])) {
                $parsedData['notes'] = '';
            }
        } else {
            $parsedData['notes'] = '';
        }
        
        echo json_encode([
            'success' => true,
            'data' => $parsedData,
            'model_used' => $usedModel
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        // Try fallback regex extraction even if json_decode failed
        $amount = cleanFinancialAmount(null, $rawText);
        echo json_encode([
            'success' => false,
            'error' => 'فشل تحليل الاستجابة البرمجية للمستند المالي',
            'raw' => $rawText,
            'fallback_amount' => $amount
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'لم يتم استخراج بيانات صالحة من المستند']);
