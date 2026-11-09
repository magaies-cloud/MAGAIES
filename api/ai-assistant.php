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

$systemPrompt = "أنت 'المساعد الذكي الشامل والحارس الأمين (Professor AI 360° & Virtual CFO)' لنظام السجانة (MAGAIEES ERP).
أنت خبير عالمي ببواطن هذا البرنامج كوداً كوداً، شاشة شاشة، زراً زراً، وخانة خانة، وخبير مالي ومحاسبي (IFRS) وفقهي ولوجستي.

قواعدك الذهبية:
1. إذا سألك المستخدم عن أي شاشة، زر، خانة، جدول، المرشد الذكي (🎓)، الدبل كليك يمين، أو التسجيل السينمائي والفيديوهات (🎬)، اشرحها له فوراً بوضوح تام وخطوات عملية من واقع [بيانات الشاشة الحالية والنظام] المرفقة.
2. إذا سألك عن أرصدة، عملاء، موردين، مبيعات، مشتريات، مخزون، أو إشعارات بنكك، اعتمد بدقة على [البيانات الحية للشركة] المرفقة.
3. إذا طلب تجهيز سند قبض أو صرف أو تحويل أو فاتورة مبيعات أو مشتريات، قم بتعبئة حقل action بالبيانات المطلوبة.
4. أجب دائماً بصيغة JSON صالحة فقط بدون أي نص خارجها على الشكل التالي:
{
  \"reply\": \"الرد الاحترافي المنظم باللغة العربية مع نقاط واضحة ورموز تعبيرية مناسبة\",
  \"action\": null
}

أنواع action المدعومة عند طلب تنفيذ عملية:
- سند قبض: {\"type\":\"draft_receipt\",\"params\":{\"from_name\":\"...\",\"to_name\":\"...\",\"amount\":0,\"notes\":\"...\"}}
- سند صرف: {\"type\":\"draft_payment\",\"params\":{\"from_name\":\"...\",\"to_name\":\"...\",\"amount\":0,\"notes\":\"...\"}}
- تحويل بين الخزن: {\"type\":\"draft_transfer\",\"params\":{\"from_name\":\"...\",\"to_name\":\"...\",\"amount\":0,\"notes\":\"...\"}}
- فاتورة مبيعات: {\"type\":\"draft_sales_invoice\",\"params\":{\"customer_name\":\"...\",\"notes\":\"...\",\"items\":[{\"name\":\"...\",\"qty\":1,\"price\":0}]}}
- فاتورة مشتريات: {\"type\":\"draft_purchase_invoice\",\"params\":{\"supplier_name\":\"...\",\"notes\":\"...\",\"items\":[{\"name\":\"...\",\"qty\":1,\"price\":0}]}}

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

// STAGE 2: High-Speed Cloud LLM Engine (Zero-Key OpenAI-Compatible Endpoint)
if (!$finalResult) {
    $cloudPayload = [
        "model" => "openai",
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $userMessage]
        ],
        "temperature" => 0.4
    ];
    $ch2 = curl_init("https://text.pollinations.ai/openai");
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($cloudPayload, JSON_UNESCAPED_UNICODE),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 7
    ]);
    $resp2 = curl_exec($ch2);
    $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    if ($resp2 !== false && $code2 >= 200 && $code2 < 300) {
        $j2 = json_decode($resp2, true);
        $content2 = $j2['choices'][0]['message']['content'] ?? '';
        if (!empty($content2)) {
            $finalResult = parseAiJsonReply($content2);
        }
    }
}

// STAGE 3: Instant Local Professor 360° Analytical Engine (Guaranteed 100% Zero-Failure Offline/Online)
if (!$finalResult || empty($finalResult['reply'])) {
    $replyLines = [];
    $replyLines[] = "🤖 **تحليل المساعد الذكي الفوري (Professor 360°):**";
    $replyLines[] = "بخصوص سؤالك: **«" . htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8') . "»**";
    if (!empty($screenContext)) {
        $replyLines[] = "\n📍 **معلومات الشاشة والعناصر المرتبطة:**\n" . $screenContext;
    }
    if (!empty($liveDbSummary)) {
        $replyLines[] = "\n📊 **البيانات الحية من قاعدة البيانات الآن:**\n" . $liveDbSummary;
    }
    if (!empty($systemContext) && $systemContext !== 'لا توجد بيانات حالية') {
        $replyLines[] = "\n💼 **المؤشرات المالية والتشغيلية:**\n" . $systemContext;
    }
    $replyLines[] = "\n💡 **إرشاد سريع:** يمكنك الضغط **دبل كليك يمين** على أي زر أو خانة في الشاشة لسماع شرحها التفصيلي بالصوت، أو الضغط على زر **المرشد الذكي 🎓** أعلى الشاشة لتشغيل الشرح التفاعلي والتسجيل السينمائي 4K.";
    $finalResult = [
        'reply' => implode("\n", $replyLines),
        'action' => null
    ];
}

echo json_encode($finalResult, JSON_UNESCAPED_UNICODE);
exit;
?>
