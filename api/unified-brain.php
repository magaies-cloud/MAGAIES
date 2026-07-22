<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Helper to load environment variables from .env
if (!function_exists('loadEnvForBrain')) {
    function loadEnvForBrain($path) {
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
loadEnvForBrain(__DIR__ . '/../.env');

require_once __DIR__ . '/../db_bridge.php';

// Initialize PDO connection and tenant context
// getDBConnection() may call die() with HTML output if session is invalid,
// so we use output buffering to intercept and return a proper JSON error.
ob_start();
try {
    $pdo = getDBConnection();
    ob_end_clean(); // Discard any output from getDBConnection
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()]);
    exit;
}

// If getDBConnection() called die(), the script would have stopped inside ob_start.
// If we reach here, the connection succeeded.
if (!isset($pdo) || !$pdo) {
    echo json_encode(['success' => false, 'error' => 'لم يتم إنشاء اتصال بقاعدة البيانات.']);
    exit;
}

try {
    $brainData = getUnifiedBrainData($pdo);
    $health = calculateBusinessHealthScore($brainData);
    
    // Calculate department statuses
    // inventory
    $inv_status = 'good';
    $inv_total = $brainData['inventory']['total_items'] ?? 1;
    if ($inv_total == 0) $inv_total = 1;
    $inv_below = $brainData['inventory']['items_below_safety'] ?? 0;
    $inv_ratio = $inv_below / $inv_total;
    if ($inv_ratio > 0.25) $inv_status = 'critical';
    elseif ($inv_ratio > 0.1) $inv_status = 'warning';
    
    // treasury
    $treasury_status = 'good';
    $liquidity = $brainData['treasury']['total_liquidity'] ?? 0;
    $debt = $brainData['customers']['total_debt'] ?? 0;
    if ($debt > 0) {
        if ($liquidity < $debt * 0.1) $treasury_status = 'critical';
        elseif ($liquidity < $debt * 0.3) $treasury_status = 'warning';
    }
    
    // sales
    $sales_status = 'critical';
    $today_sales = $brainData['sales']['today_total'] ?? 0;
    $month_sales = $brainData['sales']['month_total'] ?? 0;
    if ($today_sales > 0) $sales_status = 'good';
    elseif ($month_sales > 0) $sales_status = 'warning';
    
    // customers
    $cust_status = 'good';
    $top3_conc = $brainData['debt_concentration']['top3_concentration_pct'] ?? 0;
    if ($top3_conc > 70) $cust_status = 'critical';
    elseif ($top3_conc > 50) $cust_status = 'warning';
    
    $departments = [
        'inventory' => [
            'status' => $inv_status,
            'summary' => "{$inv_below} أصناف تحت حد الأمان من أصل {$inv_total}"
        ],
        'treasury' => [
            'status' => $treasury_status,
            'summary' => "السيولة " . number_format($liquidity) . " SDG مقابل مديونيات " . number_format($debt) . " SDG"
        ],
        'sales' => [
            'status' => $sales_status,
            'summary' => "مبيعات اليوم " . number_format($today_sales) . " SDG"
        ],
        'customers' => [
            'status' => $cust_status,
            'summary' => "تركز المديونيات: أعلى 3 عملاء = {$top3_conc}%"
        ]
    ];
    
    // Only call AI if it's a POST request (for full analysis)
    $ai_recommendations = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Construct the prompt
        $prompt = "أنت المدير العام ومراجع الحسابات والمستشار الاستراتيجي لشركة حديد تسليح ومواد بناء في سوق السجانة بالسودان.
مهمتك تحليل بيانات جميع أقسام الشركة معاً في نفس اللحظة واكتشاف المخاطر العابرة للأقسام.

📦 المخازن:
- إجمالي الأصناف: " . ($brainData['inventory']['total_items'] ?? 0) . "
- أصناف تحت حد الأمان: " . ($brainData['inventory']['items_below_safety'] ?? 0) . " صنف
- النواقص: " . json_encode($brainData['inventory']['low_stock'] ?? [], JSON_UNESCAPED_UNICODE) . "
- البضاعة الراكدة: " . json_encode($brainData['inventory']['deadstock'] ?? [], JSON_UNESCAPED_UNICODE) . "
- الأكثر مبيعاً: " . json_encode($brainData['inventory']['top_selling'] ?? [], JSON_UNESCAPED_UNICODE) . "

💰 الخزينة والسيولة:
- كاش في الصناديق: " . ($brainData['treasury']['total_cash'] ?? 0) . " SDG
- أرصدة البنوك: " . ($brainData['treasury']['total_bank'] ?? 0) . " SDG
- إجمالي السيولة المتاحة: " . ($brainData['treasury']['total_liquidity'] ?? 0) . " SDG

📊 المبيعات:
- مبيعات اليوم: " . ($brainData['sales']['today_total'] ?? 0) . " SDG (" . ($brainData['sales']['today_count'] ?? 0) . " فاتورة)
- مبيعات الشهر: " . ($brainData['sales']['month_total'] ?? 0) . " SDG (" . ($brainData['sales']['month_count'] ?? 0) . " فاتورة)

🏦 المشتريات:
- مشتريات الشهر: " . ($brainData['purchases']['month_total'] ?? 0) . " SDG (" . ($brainData['purchases']['month_count'] ?? 0) . " فاتورة)

👥 مديونيات العملاء:
- إجمالي المديونيات: " . ($brainData['customers']['total_debt'] ?? 0) . " SDG
- شيكات معلقة: " . ($brainData['customers']['total_pending_checks'] ?? 0) . " SDG
- تركز أعلى 3 عملاء: " . ($brainData['debt_concentration']['top3_concentration_pct'] ?? 0) . "%
- أعلى 5 مدينين: " . json_encode($brainData['debt_concentration']['top_concentrations'] ?? [], JSON_UNESCAPED_UNICODE) . "

🔗 المطلوب بالتحديد:
1. اكشف أي قرار يبدو صحيحاً في قسم واحد لكنه يسبب مشكلة في قسم آخر
2. اقترح حلولاً عابرة للأقسام ترفع كفاءة البزنس (مثل: بدل شراء مخزون ← صفِّ الراكد لتوفير كاش)
3. رتب التوصيات حسب الخطورة (🔴 حرج / 🟡 تحذير / 🟢 فرصة)
4. اكتب بأسلوب مباشر وعملي بدون مقدمات، باستخدام نقاط مختصرة";

        // Call Gemini
        $apiKey = getenv('GEMINI_API_KEY') ?: '';
        if ($apiKey) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
            
            $payload = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.7
                ]
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($response !== false && $httpCode < 400) {
                $responseData = json_decode($response, true);
                if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                    $ai_recommendations = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
                }
            } else {
                // OpenAI fallback or Local fallback
                $ai_recommendations = generateLocalFallbackAnalysis($brainData, $health);
            }
        } else {
            $ai_recommendations = generateLocalFallbackAnalysis($brainData, $health);
        }
    }
    
    echo json_encode([
        'success' => true,
        'health_score' => $health['score'],
        'health_label' => $health['label'],
        'health_factors' => $health['factors'],
        'departments' => $departments,
        'ai_recommendations' => $ai_recommendations,
        'raw_data' => $brainData
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function generateLocalFallbackAnalysis($data, $health) {
    $recs = "🔴 **تحليل النظام المحلي (الذكاء الاصطناعي غير متوفر حالياً)**\n\n";
    if (in_array('سيولة حرجة جداً', $health['factors'])) {
        $recs .= "- 🔴 **حرج**: السيولة منخفضة جداً مقارنة بالمديونيات. يجب تحصيل ديون بأسرع وقت أو إيقاف البيع الآجل.\n";
    }
    if (in_array('أكثر من نصف الأصناف تحت حد الأمان', $health['factors'])) {
        $recs .= "- 🔴 **حرج**: يوجد نقص حاد في المخزون قد يؤدي لضياع فرص بيعية. راجع خطط المشتريات فوراً.\n";
    }
    if (in_array('تركز المديونيات خطير', $health['factors'])) {
        $recs .= "- 🔴 **حرج**: نسبة عالية من الديون مركزة عند عدد قليل من العملاء. هذا يمثل خطر كبير، لا تعطهم بضاعة آجلة إضافية قبل السداد.\n";
    }
    if (in_array('بضاعة راكدة كثيرة', $health['factors'])) {
        $recs .= "- 🟡 **تحذير**: لديك بضاعة راكدة تستهلك مساحة الخزن والسيولة. اصنع عروض لتصريفها.\n";
    }
    if (in_array('لا مبيعات اليوم', $health['factors'])) {
        $recs .= "- 🟡 **تحذير**: لا توجد مبيعات مسجلة اليوم، راجع المندوبين.\n";
    }
    if (count($health['factors']) == 0) {
        $recs .= "- 🟢 الأمور تبدو مستقرة وجيدة بشكل عام، استمر في مراقبة الأداء.\n";
    }
    return $recs;
}
?>
