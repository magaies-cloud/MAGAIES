<?php
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', '0');

if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}
loadEnv(__DIR__ . '/../.env');

$inputData = json_decode(file_get_contents('php://input'), true);
$userMessage = trim((string)($inputData['message'] ?? ''));
$systemContext = trim((string)($inputData['context'] ?? ''));
$screenContext = trim((string)($inputData['screen_context'] ?? ''));
$imageBase64 = $inputData['image'] ?? null;

if ($userMessage === '') {
    echo json_encode(['error' => 'الرسالة فارغة'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/ai-config.php';

// Connect to live ERP MySQL DB (non-blocking) to enrich context with real live database figures
$liveDbSummary = '';
try {
    if (!defined('ALLOW_OFFLINE_DB_FALLBACK')) define('ALLOW_OFFLINE_DB_FALLBACK', true);
    if (file_exists(__DIR__ . '/../db_bridge.php')) {
        require_once __DIR__ . '/../db_bridge.php';
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
        $pdo = function_exists('getDBConnection') ? getDBConnection() : null;
        if ($pdo) {
            global $GLOBAL_TENANT_ID;
            $tid = (!empty($GLOBAL_TENANT_ID) && is_numeric($GLOBAL_TENANT_ID) && (int)$GLOBAL_TENANT_ID > 1) ? (int)$GLOBAL_TENANT_ID : 12;
            $dbParts = [];
            try {
                $st = $pdo->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(CASE WHEN balance > 0 THEN balance ELSE 0 END),0) AS debt FROM customers");
                if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                    $dbParts[] = "عدد العملاء بالقاعدة: {$r['cnt']} | إجمالي ذمم العملاء: " . number_format((float)$r['debt'], 0) . " ج.س";
                }
            } catch ( Throwable $e ) {}
            try {
                $st = $pdo->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(balance),0) AS bal FROM suppliers");
                if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                    $dbParts[] = "عدد الموردين: {$r['cnt']} | أرصدة الموردين: " . number_format((float)$r['bal'], 0) . " ج.س";
                }
            } catch ( Throwable $e ) {}
            try {
                $st = $pdo->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(quantity * COALESCE(cost_price, price, 0)),0) AS val FROM items");
                if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                    $dbParts[] = "عدد الأصناف بالمخزون: {$r['cnt']} | القيمة التقديرية للمخزون: " . number_format((float)$r['val'], 0) . " ج.س";
                }
            } catch ( Throwable $e ) {}
            try {
                $st = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS tot FROM inbound_bank_receipts WHERE tenant_id = ? AND status = 'pending'");
                $st->execute([(string)$tid]);
                if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                    $dbParts[] = "إشعارات بنكك الواردة قيد المراجعة: {$r['cnt']} إشعار بقيمة " . number_format((float)$r['tot'], 0) . " ج.س";
                }
            } catch ( Throwable $e ) {}
            if (!empty($dbParts)) {
                $liveDbSummary = implode(" | ", $dbParts);
            }
        }
    }
} catch (Throwable $e) {}

if (session_status() === PHP_SESSION_ACTIVE) {
    @session_write_close();
}

$combinedContext = trim($liveDbSummary . "\n" . $systemContext . "\n" . $screenContext);

$systemPrompt = "أنت 'البروفيسور الذكي والمستشار التنفيذي الأول (Professor AI 360°)' لنظام السجانة (MAGAIEES ERP).
تتحاور مع المستخدم بصوت رجالي عربي فصيح، دافئ، واضح المخارج، وفائق الذكاء كخبير مالي وتجاري وهندسي متمرس جالس معه.

قواعد الذكاء الفائق والحوار المباشر:
1. تحاور كإنسان حقيقي جالس أمام المستخدم: افهم قصده حتى لو كان كلامه عامياً أو مقطوعاً من الإملاء الصوتي، ورد بطبيعية ودفء وذكاء في جملتين إلى أربع جمل قصيرة سهلة النطق، واختم أحياناً بسؤال متابعة ذكي. لا تكرر نفس الصياغة، ولا تستخدم جملاً روبوتية أو قوائم أو رموزاً.
2. إذا سألك عن رأيك التجاري أو المالي أو قارن بين المبيعات والمشتريات والبورصة والسيولة، حلل له الأرقام الحية فوراً وقدم نصيحة تنفيذية ذكية ومحددة.
2ب. إذا سألك عن أي موضوع عام خارج النظام (دين، تاريخ، علوم، نصيحة شخصية) فأجبه بعلم وثقة كخبير موسوعي.
3. إلمامك الكامل بنظام السجانة للإجابة الفورية عند السؤال:
   - الشريط الذهبي (#exec-sticky-command-bar): شريط القيادة العلوي لفلترة الفترات الزمنية (اليوم، أسبوع، شهر، سنة)، ومؤشر السيولة المطمئنة، ووضع التركيز، وتحويل العملة (جنيه/دولار)، وسعر الصرف، ومايك البروفيسور، والإيجاز الذكي، والتراجع.
   - شريط البورصة (#market-broker-ticker-wrap): يعرض عروض البيع بالأخضر وطلبات الشراء بالبرتقالي لحظياً من الواتساب والذكاء البصري للصور مع تدرج شارة جديد ونظام البلاغات.
   - الشريط العشري للمؤشرات الجوهرية (#dash-today-harvest-strip): المبيعات، المشتريات، السيولة، الأرباح، المصروفات، القيمة السوقية، المقبوضات، المدفوعات، مستحقات الموردين، وديون العملاء.
   - غرف القيادة وميزانية المطابقة وشاشات الفواتير والسندات: تطابق الرصيد الدفتري والفعلي لحظياً وتدعم رادار الأسعار واللمبة الثلاثية.
4. أجب دائماً بصيغة JSON صالحة فقط:
{\"reply\": \"ردك الذكي الدافئ والواضح باللغة العربية الفصيحة الميسرة\", \"action\": null}

أنواع action (عند طلب تنفيذ عملية فقط):
- سند قبض: {\"type\":\"draft_receipt\",\"params\":{\"from_name\":\"...\",\"to_name\":\"...\",\"amount\":0,\"notes\":\"...\"}}
- سند صرف: {\"type\":\"draft_payment\",\"params\":{\"from_name\":\"...\",\"to_name\":\"...\",\"amount\":0,\"notes\":\"...\"}}
- تحويل: {\"type\":\"draft_transfer\",\"params\":{\"from_name\":\"...\",\"to_name\":\"...\",\"amount\":0,\"notes\":\"...\"}}
- مبيعات: {\"type\":\"draft_sales_invoice\",\"params\":{\"customer_name\":\"...\",\"notes\":\"...\",\"items\":[{\"name\":\"...\",\"qty\":1,\"price\":0}]}}
- مشتريات: {\"type\":\"draft_purchase_invoice\",\"params\":{\"supplier_name\":\"...\",\"notes\":\"...\",\"items\":[{\"name\":\"...\",\"qty\":1,\"price\":0}]}}

[البيانات الحية للشركة والشاشة الحالية]:
" . $combinedContext;

function parseAiJsonReply($rawText) {
    $txt = trim((string)$rawText);
    if ($txt === '') return null;
    if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $txt, $m)) {
        $txt = trim($m[1]);
    }
    $decoded = json_decode($txt, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded['reply'])) {
        return ['reply' => (string)$decoded['reply'], 'action' => $decoded['action'] ?? null];
    }
    if (preg_match('/\{[\s\S]*"reply"[\s\S]*\}/u', $txt, $m2)) {
        $decoded2 = json_decode($m2[0], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2) && !empty($decoded2['reply'])) {
            return ['reply' => (string)$decoded2['reply'], 'action' => $decoded2['action'] ?? null];
        }
    }
    return ['reply' => $txt, 'action' => null];
}

$finalResult = null;

// STAGE 1: Google Gemini API (if valid AIza... key exists)
$clientKey = trim((string)($inputData['api_key'] ?? ''));
$apiKey = getGeminiApiKey($clientKey);
$model = getGeminiModel();

if (function_exists('isValidGeminiApiKeyFormat') && isValidGeminiApiKeyFormat($apiKey)) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . $apiKey;
    $parts = [["text" => $systemPrompt . "\n\nسؤال المستخدم: " . $userMessage]];
    if ($imageBase64) {
        if (strpos($imageBase64, 'base64,') !== false) {
            $partsArr = explode('base64,', $imageBase64);
            $imageBase64 = $partsArr[1];
        }
        $parts[] = ["inline_data" => ["mime_type" => "image/jpeg", "data" => $imageBase64]];
    }
    $payload = [
        "contents" => [["parts" => $parts]],
        "generationConfig" => ["responseMimeType" => "application/json"]
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 8
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
        $responseData = json_decode($response, true);
        if (!empty($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            $finalResult = parseAiJsonReply($responseData['candidates'][0]['content']['parts'][0]['text']);
        }
    }
}

// STAGE 2: High-Speed Cloud LLM Engine (Zero-Key OpenAI-Compatible Endpoint with Vision Support)
if (!$finalResult) {
    $imgDataUrl = null;
    if (!empty($inputData['image'])) {
        $rawImg = trim((string)$inputData['image']);
        $imgDataUrl = (strpos($rawImg, 'data:image') === 0) ? $rawImg : ('data:image/jpeg;base64,' . $rawImg);
    }
    $userContent = $imgDataUrl ? [
        ["type" => "text", "text" => $userMessage],
        ["type" => "image_url", "image_url" => ["url" => $imgDataUrl]]
    ] : $userMessage;
    $ctxDecoded = json_decode($systemContext, true);
    $historyMsgs = [["role" => "system", "content" => $systemPrompt]];
    if (is_array($ctxDecoded) && !empty($ctxDecoded['recent_dialogue']) && is_array($ctxDecoded['recent_dialogue'])) {
        foreach (array_slice($ctxDecoded['recent_dialogue'], -6) as $turn) {
            if (!empty($turn['text'])) {
                $historyMsgs[] = ["role" => (($turn['role'] ?? '') === 'user' ? 'user' : 'assistant'), "content" => (string)$turn['text']];
            }
        }
    }
    $historyMsgs[] = ["role" => "user", "content" => $userContent];
    $cloudPayload = [
        "model" => "openai",
        "messages" => $historyMsgs,
        "temperature" => 0.7
    ];
    $ch2 = curl_init("https://text.pollinations.ai/openai");
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($cloudPayload, JSON_UNESCAPED_UNICODE),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 11
    ]);
    $resp2 = curl_exec($ch2);
    $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    $err2 = curl_error($ch2);
    curl_close($ch2);
    if (!headers_sent()) header('X-AI-Stage2: ' . (int)$code2 . ' ' . substr(preg_replace('/[^\x20-\x7E]/', '', (string)$err2 . ' ' . substr((string)$resp2, 0, 120)), 0, 200));

    if ($resp2 !== false && $code2 >= 200 && $code2 < 300) {
        $j2 = json_decode($resp2, true);
        $content2 = $j2['choices'][0]['message']['content'] ?? '';
        if (!empty($content2)) {
            $finalResult = parseAiJsonReply($content2);
        }
    }
}

// STAGE 3: Instant High-IQ Professor 360° Analytical Fallback (Clear Eloquent Arabic)
if (!$finalResult || empty($finalResult['reply'])) {
    $q = $userMessage;
    $directReply = "أهلاً بك يا باشمهندس؛ بناءً على مؤشرات النظام الحية أمامنا، الأداء متوازن ويمكننا مراجعة المبيعات أو المشتريات أو السيولة أو البورصة فوراً حسب أولويتك الآن.";
    if (preg_match('/مرحبا|اهلا|أهلا|السلام عليكم|صباح|مساء/u', $q)) {
        $directReply = "أهلاً ومرحباً بك يا باشمهندس! جميع مؤشرات النظام جاهزة ومحدثة أمامك، كيف تحب أن نبدأ جولتنا اليوم؟";
    } elseif (preg_match('/الشريط الذهبي|شريط الاوامر|شريط الأوامر/u', $q)) {
        $directReply = "الشريط الذهبي هو مركز القيادة العلوي المثبت؛ يتيح لك فلترة الفترات الزمنية بلمسة واحدة، ومتابعة نبض السيولة المطمئنة، وتبديل العملة بين الجنيه والدولار، وتفعيل وضع التركيز فوراً.";
    } elseif (preg_match('/بورص|شريط البورص|عروض|طلبات السوق/u', $q)) {
        $directReply = "شريط البورصة يربطك بالسوق لحظياً عبر الواتساب والذكاء البصري للصور، حيث يعرض عروض البيع بالأخضر وطلبات الشراء بالبرتقالي مع تدرج شارة العروض الجديدة ونظام البلاغات.";
    } elseif (preg_match('/سيولة مطمئنة|نبض|سيول|خزين|بنك/u', $q)) {
        $directReply = "مؤشر السيولة يقارن السيولة النقدية والبنكية المتاحة بمستحقات الموردين لحظياً ليمنحك رؤية مالية آمنة وقراراً دقيقاً قبل أي التزام جديد.";
    } elseif (preg_match('/مبيعات|بيع/u', $q)) {
        $directReply = "في قسم المبيعات نتابع حركة الفواتير اليومية وهوامش الربح المحققة ومديونيات العملاء لحظياً، هل نفتح فاتورة مبيعات جديدة أم نراجع تحصيل العملاء؟";
    } elseif (preg_match('/مشتريات|شراء|مورد/u', $q)) {
        $directReply = "في قسم المشتريات نراقب فواتير التوريد وحركة رادار الأسعار ومستحقات الموردين بدقة، هل تود مراجعة أرصدة الموردين أم إدخال فاتورة شراء؟";
    } elseif (preg_match('/رأيك|رايك|ما رأيك|نصيح|تحليل/u', $q)) {
        $directReply = "نصيحتي التنفيذية هي التركيز على تسريع تحصيل ديون العملاء لتعزيز السيولة الجاهزة، واستثمار عروض البورصة ذات الأسعار التنافسية لرفع هامش الربح.";
    }
    $finalResult = [
        'reply' => $directReply,
        'action' => null
    ];
}

echo json_encode($finalResult, JSON_UNESCAPED_UNICODE);
exit;
?>
