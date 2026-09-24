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
$fraudCheck = !empty($inputData['fraud_check']);

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

// Function to robustly clean and extract financial amount (handles Arabic numbers, currencies, commas, multiple dots, and regex fallback)
if (!function_exists('cleanFinancialAmount')) {
    function cleanFinancialAmount($val, $rawText = '') {
        $str = '';
        if ($val !== null && $val !== '') {
            $str = (string)$val;
        }

        // Standardize Eastern Arabic (٠-٩) and Persian (۰-۹) digits to Latin (0-9)
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $latin = ['0','1','2','3','4','5','6','7','8','9'];
        $str = str_replace($arabic, $latin, $str);
        $str = str_replace($persian, $latin, $str);

        $parseNumeric = function($raw) {
            $raw = trim($raw);
            // If string contains both dots and commas, determine decimal vs thousands
            if (strpos($raw, '.') !== false && strpos($raw, ',') !== false) {
                if (strrpos($raw, ',') > strrpos($raw, '.')) {
                    // European: 1.500.000,50 -> dot is thousands, comma is decimal
                    $raw = str_replace('.', '', $raw);
                    $raw = str_replace(',', '.', $raw);
                } else {
                    // Anglo-American: 1,500,000.50 -> comma is thousands, dot is decimal
                    $raw = str_replace(',', '', $raw);
                }
            } elseif (substr_count($raw, '.') > 1) {
                // Multiple dots: 1.500.000 or 1.500.000.00 -> thousands separator is dot
                $parts = explode('.', $raw);
                if (strlen(end($parts)) == 2) {
                    $cents = array_pop($parts);
                    $raw = implode('', $parts) . '.' . $cents;
                } else {
                    $raw = implode('', $parts);
                }
            } elseif (substr_count($raw, ',') > 1) {
                // Multiple commas: 1,500,000 -> all commas are thousands separators
                $raw = str_replace(',', '', $raw);
            } elseif (preg_match('/^(\d+)[.,](\d{3})$/', $raw, $m)) {
                // Single dot or comma with 3 digits at the end: 150.000 or 150,000 -> 150000
                $raw = $m[1] . $m[2];
            } elseif (preg_match('/^(\d+),(\d{2})$/', $raw, $m)) {
                // e.g. 1500,50 -> comma is decimal
                $raw = $m[1] . '.' . $m[2];
            } else {
                $raw = str_replace([',', ' '], '', $raw);
            }
            return floatval($raw);
        };

        if (preg_match('/[0-9]/', $str)) {
            if (preg_match('/([0-9][0-9,.\s]*[0-9]|[0-9]+)/', $str, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }
        }

        // Fallback: If amount is 0 or wasn't found in $val, search $rawText with regex
        if (!empty($rawText)) {
            $cleanRaw = str_replace($arabic, $latin, (string)$rawText);
            $cleanRaw = str_replace($persian, $latin, $cleanRaw);

            // Pattern 1: JSON field "amount": "150,000" or "amount": 150000
            if (preg_match('/"amount"\s*:\s*"?([0-9][0-9,.\s]*)/i', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }

            // Pattern 2: Arabic text with various financial labels (handling flexible words in between)
            if (preg_match('/(?:المبلغ|قيمة|بمبلغ|مبلغ|ادفعوا)[^\d:\r\n]{0,35}[:\-]?\s*(?:قدره)?\s*[:\-]?\s*([0-9][0-9,.\s]*[0-9]|[0-9]+)/ui', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }

            // Pattern 3: English labels
            if (preg_match('/(?:Total(?:\s+Amount)?|Amount|Sum|Transfer(?:\s+Amount)?)\s*[:\-]?\s*([0-9][0-9,.\s]*[0-9]|[0-9]+)/ui', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }

            // Pattern 4: Number followed by currency (SDG, ج.س, جنيه, ريال, دولار)
            if (preg_match('/([0-9][0-9,.\s]*[0-9]|[0-9]+)\s*(?:SDG|ج\.س|جنيه|ريال|SAR|USD|دولار)/ui', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }

            // Pattern 5: Currency followed by number (e.g. SDG 50,000 or ج.س 50,000)
            if (preg_match('/(?:SDG|ج\.س|جنيه|ريال|SAR|USD|دولار)\s*[:\-]?\s*([0-9][0-9,.\s]*[0-9]|[0-9]+)/ui', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }
        }

        return 0.0;
    }
}

if ($fraudCheck) {
    // ═════════════════════════════════════════════════════════════
    // Forensic Inspection Prompt (Only when user explicitly clicks "كشف التزوير")
    // ═════════════════════════════════════════════════════════════
    $systemPrompt = "أنت خبير فحص المستندات المالية المصرفية بالرؤية الحاسوبية والتدقيق الجنائي الرقمي (Forensic Image & Document Tampering Inspection).
دقق في صورة المستند المالي بعين الخبير الجنائي للبحث عن أي مؤشرات للتزوير أو التعديل ببرامج تحرير الصور (Photoshop / Mobile Editing) أو توليد الإشعارات الوهمية، مع التركيز الفائق والدقيق على رقم العملية المرجعي (Transaction Reference ID):
1. الفحص الجنائي المتعمق في خانات رقم العملية المرجعي (Transaction Reference Number):
   - افحص كل رقم وخانة في رقم العملية: هل نوع الخط (Font Family)، وزنه أو سماكته (Boldness)، حدة الحواف (Edge Sharpness)، أو المسافات البينية (Letter/Digit Spacing) متطابقة تماماً بين كافة أرقام العملية، أم يوجد رقم مُعدل أو ملصق فوق رقم قديم؟
   - افحص استقامة خط الأساس (Baseline Alignment): هل جميع أرقام العملية مصطفة هندسياً بدقة على خط أفقي واحد، أم هناك رقم مرتفع أو منخفض بشكل شاذ؟
   - افحص بيكسلات الخلفية والضغط (JPEG/Pixel Artifacts): هل تظهر هالة تشويش، مربع ضغط لوني، أو تدرج لوني مختلف حول أرقام العملية؟
   - افحص بنية رقم العملية: إشعارات البنوك (مثل بنك الخرطوم/بنكك أو بنك فيصل/فوري) تتكون أرقام عملياتها من تسلسل رقمي قياسي محدد (مثلاً 10-12 رقماً في بنكك)، فإذا كان الرقم يحتوي على تشويه في عدد الخانات أو أرقام مقحمة، فهذا مؤشر تزوير.
2. التدقيق في خانة المبلغ والتاريخ ورقم الحساب: اختلاف الخطوط أو بيكسلات الضغط أو انزياح الأرقام.
3. في الشيكات الورقية: وجود آثار كشط، حك، تعديل حبر، كتابة فوقية، أو عدم تطابق المبلغ بالأرقام مع المبلغ بالحروف (التفقيط).
4. قدّر نسبة الشك في التزوير (tampering_score) كنسبة مئوية صحيحة من 0 إلى 100:
   - 0 إلى 25%: سليم وأصلي طبيعي جداً (Original Authentic).
   - 26 إلى 45%: اشتباه طفيف قد يكون ناتجاً عن ضغط الكاميرا أو الواتساب.
   - 46 إلى 75%: اشتباه ملحوظ (تعديل محتمل في خط رقم العملية أو المبلغ).
   - 76 إلى 100%: اشتباه تزوير عالي ومؤكد (تعديل فوتوشوب واضح وتلاعب بالبيكسلات وخانات أرقام العملية).
إذا تم رصد أي تلاعب أو اختلاف خط في خانات رقم العملية تحديداً، اجعل نسبة الشك لا تقل عن 70%، واذكر بوضوح في tampering_reasons: 'تلاعب رقمي أو تعديل في خانات رقم العملية المرجعي' مع توضيح التفاصيل.

قم بتفريغ البيانات بصيغة JSON التالية وبدون أي نصوص تمهيدية:
{
  \"is_financial_doc\": true,
  \"is_approved_template\": true,
  \"doc_type\": \"bank_transfer\" | \"cheque\" | \"deposit_slip\" | \"non_financial\",
  \"bank_name\": \"اسم البنك\",
  \"app_name\": \"اسم التطبيق إن وجد\",
  \"transaction_ref\": \"رقم العملية المرجعي / Transaction ID\",
  \"cheque_number\": \"رقم الشيك إن وجد\",
  \"amount\": 0.0,
  \"amount_in_words\": \"المبلغ بالحروف إن وجد\",
  \"currency\": \"SDG\" | \"USD\" | \"SAR\",
  \"date\": \"YYYY-MM-DD\",
  \"due_date\": \"YYYY-MM-DD\",
  \"time\": \"HH:MM\",
  \"sender_name\": \"اسم المحول\",
  \"sender_account\": \"من حساب\",
  \"receiver_name\": \"المستلم / إلى\",
  \"receiver_account\": \"إلى حساب\",
  \"receiver_phone\": \"\",
  \"notes\": \"\",
  \"confidence_score\": 95,
  \"tampering_score\": 0,
  \"is_suspicious\": false,
  \"tampering_reasons\": \"شرح سبب الاشتباه الجنائي في التزوير بالتفصيل وموضعه بالصورة إن وجد أو فارغ تماماً إن كان أصلياً وسليماً\",
  \"summary\": \"ملخص سطر واحد بالعربية\"
}";
    $maxOutputTokens = 2048;
} else {
    // ═════════════════════════════════════════════════════════════
    // Ultra-Fast Turbo Extraction Prompt (Lightweight, Instant Response)
    // ═════════════════════════════════════════════════════════════
    $systemPrompt = "أنت محرك فحص إشعارات التحويلات البنكية والشيكات فائق السرعة (Turbo Fast OCR).
استخرج البيانات من الصورة بدقة وسرعة فائقة بدون أي فحص تزوير.
معالم البنوك:
- بنكك (Bankak - بنك الخرطوم): اسم البنك 'بنك الخرطوم'، والتطبيق 'بنكك'.
- فوري (Fawry - بنك فيصل): اسم البنك 'بنك فيصل الإسلامي'، والتطبيق 'فوري'.
- أوكاش (Ocash - بنك أمدرمان): اسم البنك 'بنك أمدرمان الوطني'، والتطبيق 'أوكاش'.
- أي بنك آخر: استخرج اسم البنك والمبلغ ورقم المعاملة.

أخرج حصراً كود JSON الصالح للتحليل مباشرة بدون أي نصوص تمهيدية:
{
  \"is_financial_doc\": true,
  \"is_approved_template\": true,
  \"doc_type\": \"bank_transfer\" | \"cheque\" | \"deposit_slip\",
  \"bank_name\": \"اسم البنك\",
  \"app_name\": \"اسم التطبيق إن وجد\",
  \"transaction_ref\": \"رقم العملية / Transaction ID / رقم الإشعار\",
  \"cheque_number\": \"رقم الشيك إن وجد\",
  \"amount\": 0.0,
  \"amount_in_words\": \"\",
  \"currency\": \"SDG\" | \"SAR\" | \"USD\",
  \"date\": \"YYYY-MM-DD\",
  \"due_date\": \"YYYY-MM-DD\",
  \"time\": \"\",
  \"sender_name\": \"اسم المحول\",
  \"sender_account\": \"من حساب\",
  \"receiver_name\": \"المستلم / إلى\",
  \"receiver_account\": \"إلى حساب\",
  \"receiver_phone\": \"\",
  \"notes\": \"\",
  \"confidence_score\": 98,
  \"tampering_score\": 0,
  \"is_suspicious\": false,
  \"is_cropped\": false,
  \"tampering_reasons\": \"\",
  \"summary\": \"ملخص سطر واحد\"
}
تنبيهات السرعة القصوى:
1. المبلغ يجب أن يكون رقماً حقيقياً خالصاً في amount (مثلاً 150000 أو 75500.50 بدون فواصل وبدون نصوص عملة).
2. استخرج رقم العملية بالكامل دون نقصان.
3. إذا كان الإشعار مقطوعاً في التصوير (Cropped) أو حوافه مقصوصة ورقم العملية غير مكتمل أو مقطوع، اجعل is_cropped: true وضع في notes 'مقطوع في التصوير'.
4. التواريخ بصيغة YYYY-MM-DD.
5. حساب 'من' في sender_account، وحساب 'إلى' في receiver_account دون عكس بينهما.";
    $maxOutputTokens = 700;
}

$systemPrompt .= $customGuidelineText;

if (!empty($inputData['retry_amount'])) {
    $systemPrompt .= "\n\n⚠️ تنبيه عالي الأهمية (إعادة فحص تلقائي للمبلغ): في المحاولة السابقة ظهر المبلغ صفر (0)؛ دقق في الصورة كاملة واستخرج المبلغ الحقيقي بدقة تامة ولا تتركه صفراً إذا كان موجوداً.";
}

if (!empty($inputData['is_refresh'])) {
    $systemPrompt .= "\n\n⚠️ إعادة تدقيق شاملة بطلب خاص من المحاسب (Manual Refresh): دقق بتركيز فائق واقرأ الأرقام والحروف بأعلى درجات الدقة والتطابق مع الصورة الأصلية.";
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
        "maxOutputTokens" => $maxOutputTokens
    ]
];

// Multi-Model Cascade: automatically fallback across active models if primary hits rate limit (429), spikes (503), or quota exhaustion
$modelsCascade = [
    $model,
    'gemini-3.5-flash',
    'gemini-flash-lite-latest',
    'gemini-3-flash-preview',
    'gemini-flash-latest'
];
$modelsCascade = array_values(array_unique(array_filter($modelsCascade)));

$response = false;
$httpCode = 0;
$curlError = '';
$lastErrorMsg = '';
$usedModel = $modelsCascade[0] ?? $model;

$hadQuotaIssue = false;
foreach ($modelsCascade as $idx => $currentModel) {
    if ($idx > 0) {
        // Brief pause between fallback attempts if first model had issue
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

    if ($httpCode === 429) {
        $hadQuotaIssue = true;
    }

    if ($response !== false) {
        $respJson = json_decode($response, true);
        $lastErrorMsg = $respJson['error']['message'] ?? "HTTP {$httpCode}";
        if (strpos($lastErrorMsg, 'quota') !== false || strpos($lastErrorMsg, 'Resource has been exhausted') !== false) {
            $hadQuotaIssue = true;
        }
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
    $isQuota = ($httpCode === 429 || $hadQuotaIssue || strpos($errorMsg, 'quota') !== false || strpos($errorMsg, 'Resource has been exhausted') !== false);
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
            || !empty($parsedData['receiver_account'])
            || !empty($parsedData['receiver_name']);

        $docType = $parsedData['doc_type'] ?? '';

        // Auto-deduce and standardize bank name and app name
        $bankName = trim((string)($parsedData['bank_name'] ?? ''));
        $appName = trim((string)($parsedData['app_name'] ?? ''));
        $searchPoolLower = mb_strtolower($rawText . ' ' . ($parsedData['notes'] ?? '') . ' ' . ($parsedData['summary'] ?? '') . ' ' . $bankName . ' ' . $appName);

        if (empty($bankName) || in_array($bankName, ['غير محدد', 'غير متوفر', 'N/A', 'null', 'None', '-'])) {
            if (strpos($searchPoolLower, 'بنكك') !== false || strpos($searchPoolLower, 'bankak') !== false || strpos($searchPoolLower, 'الخرطوم') !== false || strpos($searchPoolLower, 'bok') !== false) {
                $parsedData['bank_name'] = 'بنك الخرطوم';
                if (empty($parsedData['app_name'])) $parsedData['app_name'] = 'بنكك';
            } elseif (strpos($searchPoolLower, 'فوري') !== false || strpos($searchPoolLower, 'fawry') !== false || strpos($searchPoolLower, 'فيصل') !== false || strpos($searchPoolLower, 'fib') !== false) {
                $parsedData['bank_name'] = 'بنك فيصل الإسلامي';
                if (empty($parsedData['app_name'])) $parsedData['app_name'] = 'فوري';
            } elseif (strpos($searchPoolLower, 'أوكاش') !== false || strpos($searchPoolLower, 'ocash') !== false || strpos($searchPoolLower, 'أمدرمان') !== false || strpos($searchPoolLower, 'onb') !== false) {
                $parsedData['bank_name'] = 'بنك أمدرمان الوطني';
                if (empty($parsedData['app_name'])) $parsedData['app_name'] = 'أوكاش';
            } elseif (strpos($searchPoolLower, 'النيل') !== false) {
                $parsedData['bank_name'] = 'بنك النيل';
            } elseif (strpos($searchPoolLower, 'البركة') !== false) {
                $parsedData['bank_name'] = 'بنك البركة';
            } elseif (strpos($searchPoolLower, 'تضامن') !== false) {
                $parsedData['bank_name'] = 'بنك التضامن الإسلامي';
            } elseif ($docType !== 'non_financial') {
                $parsedData['bank_name'] = 'بنك الخرطوم';
                if (empty($parsedData['app_name'])) $parsedData['app_name'] = 'بنكك';
            }
        }

        if ($hasValidFinancialSignals || ($docType !== 'non_financial' && $docType !== 'unapproved')) {
            $parsedData['is_financial_doc'] = true;
            $parsedData['is_approved_template'] = true;
            $parsedData['is_non_financial'] = false;
            $parsedData['is_unapproved'] = false;
            $parsedData['allow_save'] = true;
            $parsedData['is_unclear'] = false;
            $parsedData['is_unclear_amount'] = ($amount <= 0.0);
            if ($docType === 'cheque') {
                $parsedData['doc_type'] = 'cheque';
            } else {
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

        // Clean and validate transaction_ref
        if (!empty($parsedData['transaction_ref'])) {
            $parsedData['transaction_ref'] = trim((string)$parsedData['transaction_ref']);
            if (in_array(strtolower($parsedData['transaction_ref']), ['null', 'n/a', 'none', 'غير متوفر', 'لا يوجد', '-'])) {
                $parsedData['transaction_ref'] = '';
            }
        }

        // Smart Multi-Pattern Regex Fallback for transaction_ref if empty
        if (empty($parsedData['transaction_ref'])) {
            $arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            $persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            $latinDigits = ['0','1','2','3','4','5','6','7','8','9'];
            $searchPool = ($parsedData['notes'] ?? '') . "\n" . ($parsedData['summary'] ?? '') . "\n" . $rawText;
            $searchPoolClean = str_replace($arabicDigits, $latinDigits, $searchPool);
            $searchPoolClean = str_replace($persianDigits, $latinDigits, $searchPoolClean);

            // 1. Explicit Arabic/English labels
            $labelPattern = '/(?:رقم\s*(?:العملية|المعاملة|المرجع|الإشعار|التحويل|الحوالة|السند|الحركة|القيد|الإيصال)|كود\s*(?:العملية|المعاملة|التحويل)|رمز\s*(?:العملية|المعاملة|التحويل)|معرف\s*(?:العملية|المعاملة|التحويل)|الرقم\s*المرجعي|Transaction\s*(?:ID|No|Ref|Number)|Txn\s*(?:ID|No)?|Ref\s*(?:No|Number|#)?|Reference\s*(?:No|Number)?)\s*[:\-#]?\s*([A-Za-z0-9\-_]{4,35})/ui';
            if (preg_match($labelPattern, $searchPoolClean, $mRef)) {
                $parsedData['transaction_ref'] = trim($mRef[1]);
            } elseif (preg_match_all('/\b([0-9]{6,16})\b/', $searchPoolClean, $mDigits)) {
                // 2. Standalone 6-16 digit numbers (not amount, not accounts)
                foreach ($mDigits[1] as $cand) {
                    $candTrim = trim($cand);
                    if ($candTrim === ($parsedData['sender_account'] ?? '') || $candTrim === ($parsedData['receiver_account'] ?? '')) continue;
                    if (floatval($candTrim) == ($parsedData['amount'] ?? 0)) continue;
                    $parsedData['transaction_ref'] = $candTrim;
                    break;
                }
            }
        }

        if (!empty($parsedData['cheque_number'])) {
            $parsedData['cheque_number'] = trim((string)$parsedData['cheque_number']);
            if (in_array(strtolower($parsedData['cheque_number']), ['null', 'n/a', 'none', '-'])) {
                $parsedData['cheque_number'] = '';
            }
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
        
        // Process Forensic Tampering Detection Score & Reasons
        $tamperingScore = isset($parsedData['tampering_score']) ? intval($parsedData['tampering_score']) : 0;
        if ($tamperingScore < 0) $tamperingScore = 0;
        if ($tamperingScore > 100) $tamperingScore = 100;
        
        $tamperingReasons = trim((string)($parsedData['tampering_reasons'] ?? ''));

        // Logical Check Discrepancy for Checks (تفقيط الحروف مقابل الأرقام - عند طلب كشف التزوير فقط)
        if ($fraudCheck && !empty($parsedData['amount_in_words']) && !empty($parsedData['amount']) && floatval($parsedData['amount']) > 0) {
            $wordsText = (string)$parsedData['amount_in_words'];
            $amtNum = floatval($parsedData['amount']);
            if (preg_match('/مليون|ملايين/', $wordsText) && $amtNum < 500000) {
                $tamperingScore = max($tamperingScore, 75);
                $tamperingReasons = !empty($tamperingReasons) ? ($tamperingReasons . ' | تعارض بين تفقيط الشيك (بالملايين) والمبلغ بالأرقام') : 'تعارض بين تفقيط الشيك (بالملايين) والمبلغ بالأرقام';
            } elseif (!preg_match('/مليون|ملايين/', $wordsText) && $amtNum >= 1000000) {
                $tamperingScore = max($tamperingScore, 75);
                $tamperingReasons = !empty($tamperingReasons) ? ($tamperingReasons . ' | تفقيط الشيك لا يذكر الملايين رغم أن المبلغ بالأرقام مليون أو أكثر') : 'تفقيط الشيك لا يذكر الملايين رغم أن المبلغ بالأرقام مليون أو أكثر';
            }
        }

        // Forensic Scrutiny on Transaction Reference Number (التحقق الخوارزمي من بنية أرقام العملية)
        if ($fraudCheck && !empty($parsedData['transaction_ref'])) {
            $refStr = trim((string)$parsedData['transaction_ref']);
            $digitsOnly = preg_replace('/\D/', '', $refStr);
            // 1. Repeated identical digits (e.g. 1111111111 or 0000000000)
            if (strlen($digitsOnly) >= 6 && preg_match('/^(\d)\1+$/', $digitsOnly)) {
                $tamperingScore = max($tamperingScore, 85);
                $refFraudNote = 'رقم عملية وهمي أو غير حقيقي (تكرار نفس الرقم بالكامل: ' . $refStr . ')';
                $tamperingReasons = !empty($tamperingReasons) ? ($tamperingReasons . ' | ' . $refFraudNote) : $refFraudNote;
            }
            // 2. Sequential fake patterns (e.g. 123456789 or 987654321)
            elseif (strlen($digitsOnly) >= 6 && (strpos('01234567890123456789', $digitsOnly) !== false || strpos('98765432109876543210', $digitsOnly) !== false)) {
                $tamperingScore = max($tamperingScore, 85);
                $refFraudNote = 'رقم عملية تجريبي أو مصطنع (تسلسل أرقام متتالية وهمية: ' . $refStr . ')';
                $tamperingReasons = !empty($tamperingReasons) ? ($tamperingReasons . ' | ' . $refFraudNote) : $refFraudNote;
            }
        }

        $parsedData['fraud_checked'] = (bool)$fraudCheck;
        $parsedData['tampering_score'] = $fraudCheck ? $tamperingScore : 0;
        $parsedData['is_suspicious'] = $fraudCheck ? ($tamperingScore >= 35 || !empty($parsedData['is_suspicious'])) : false;
        $parsedData['tampering_reasons'] = $fraudCheck ? $tamperingReasons : '';
        $isRefMissing = empty($parsedData['transaction_ref']) || in_array(strtolower(trim($parsedData['transaction_ref'])), ['---', 'null', 'none', 'n/a', '-']);
        $parsedData['is_cropped'] = !empty($parsedData['is_cropped']) || ($isRefMissing && ($parsedData['amount'] ?? 0) > 0);

        echo json_encode([
            'success' => true,
            'data' => $parsedData,
            'model_used' => $usedModel
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        // Resilient fallback: Try regex extraction even if json_decode failed
        $fallbackAmount = cleanFinancialAmount(null, $rawText);
        if ($fallbackAmount > 0) {
            $fallbackRef = '';
            $arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            $persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            $latinDigits = ['0','1','2','3','4','5','6','7','8','9'];
            $cleanRaw = str_replace($arabicDigits, $latinDigits, $rawText);
            $cleanRaw = str_replace($persianDigits, $latinDigits, $cleanRaw);
            if (preg_match('/(?:رقم\s*(?:العملية|المعاملة|المرجع|الإشعار|التحويل|الحوالة)|Transaction\s*(?:ID|No|Ref)|Txn\s*ID|Ref\s*No)[^\d:]*[:\s#]*([0-9A-Za-z-]{5,30})/ui', $cleanRaw, $mRef)) {
                $fallbackRef = trim($mRef[1]);
            }

            $fallbackData = [
                'is_financial_doc' => true,
                'is_approved_template' => true,
                'is_non_financial' => false,
                'is_unapproved' => false,
                'allow_save' => true,
                'is_unclear' => false,
                'is_unclear_amount' => false,
                'doc_type' => 'bank_transfer',
                'bank_name' => 'بنك الخرطوم',
                'app_name' => 'بنكك',
                'transaction_ref' => $fallbackRef,
                'cheque_number' => '',
                'amount' => $fallbackAmount,
                'currency' => 'SDG',
                'date' => date('Y-m-d'),
                'due_date' => date('Y-m-d'),
                'sender_name' => '',
                'receiver_name' => '',
                'rejection_reason' => '',
                'tampering_score' => 0,
                'is_suspicious' => false,
                'is_cropped' => (empty($fallbackRef) && $fallbackAmount > 0),
                'tampering_reasons' => '',
                'summary' => 'تحويل بنكي بمبلغ ' . number_format($fallbackAmount, 2) . ' SDG'
            ];

            echo json_encode([
                'success' => true,
                'data' => $fallbackData,
                'model_used' => $usedModel,
                'is_fallback_regex' => true
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => false,
            'error' => 'فشل تحليل الاستجابة البرمجية للمستند المالي',
            'raw' => $rawText,
            'fallback_amount' => $fallbackAmount
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'لم يتم استخراج بيانات صالحة من المستند']);
