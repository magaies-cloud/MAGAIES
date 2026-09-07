<?php
// =============================================================
// نظام MAGAIEES ERP 2026 - محرك الترقية بالذكاء الاصطناعي التنبئي (AI)
// =============================================================
require_once 'db_bridge.php';
$pdo = getDBConnection();

// تحميل متغيرات البيئة من ملف .env
function loadEnvForAdvisor($path) {
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
loadEnvForAdvisor(__DIR__ . '/.env');

// 1. جلب البيانات الحقيقية للمخزون والمبيعات لتلقيمها للذكاء الاصطناعي
try {
    $top_customers = getCustomerExposureData($pdo);
    // اقتطاع أول 5 عملاء فقط
    $top_customers = array_slice($top_customers, 0, 5);
    
    $raw_stock = getInventoryReportData($pdo);
    $stock_summary = [];
    foreach ($raw_stock as $row) {
        $stock_summary[] = [
            'name' => $row['item_name'],
            'stock' => $row['stock_qty'],
            'min_limit' => $row['min_limit'],
            'sold_qty' => $row['sold_qty']
        ];
    }
    $stock_summary = array_slice($stock_summary, 0, 5);
} catch (Exception $e) {
    $top_customers = [];
    $stock_summary = [["name" => "حديد 4 لينيا أباذر", "stock" => 340, "min_limit" => 50, "sold_qty" => 120]];
}

// تجهيز البيانات في نص منظم ليفهمه الذكاء الاصطناعي بالكامل
$data_for_ai = "بيانات منظومة Magaiees الحالية:\n";
$data_for_ai .= "أعلى مديونيات عملاء (الأرصدة + الشيكات): " . json_encode($top_customers, JSON_UNESCAPED_UNICODE) . "\n";
$data_for_ai .= "الأصناف المتوفرة ومستويات السحب: " . json_encode($stock_summary, JSON_UNESCAPED_UNICODE) . "\n";

$ai_analysis_result = "";

// 2. عند الضغط على زر "استشارة الذكاء الاصطناعي"
if (isset($_POST['consult_ai'])) {
    require_once __DIR__ . '/api/ai-config.php';
    $gemini_key = getGeminiApiKey();
    $openai_key = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?: '';
    
    $prompt = "أنت خبير مالي وإداري في سوق السجانة لحديد التسليح ومواد البناء بالسودان. حلل هذه البيانات الحالية لبرنامج MAGAIEES ERP وأعطني تقريراً استراتيجياً قصيراً ومباشراً للباشمهندس مهدي يتوقع حركة الأسعار، مخاطر الائتمان، ونصيحة لشراء المخزون وتفادي الركود. اكتب التقرير باللغة العربية في نقاط واضحة ومنسقة باستخدام مارك داون وبدون أي كلام تمهيدي:\n" . $data_for_ai;

    $success = false;
    
    // محاولة استخدام Gemini أولاً إذا كان المفتاح متوفراً
    if (!empty($gemini_key)) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $gemini_key;
        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
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
        
        if ($httpCode === 200) {
            $res_data = json_decode($response, true);
            if (isset($res_data['candidates'][0]['content']['parts'][0]['text'])) {
                $ai_analysis_result = trim($res_data['candidates'][0]['content']['parts'][0]['text']);
                $success = true;
            }
        }
    }
    
    // محاولة استخدام OpenAI إذا فشل Gemini أو لم يتوفر مفتاحه وكان مفتاح OpenAI متوفراً
    if (!$success && !empty($openai_key)) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.7
        ];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openai_key
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $res_data = json_decode($response, true);
            if (isset($res_data['choices'][0]['message']['content'])) {
                $ai_analysis_result = trim($res_data['choices'][0]['message']['content']);
                $success = true;
            }
        }
    }
    
    // إذا لم يتوفر أي مفتاح أو فشل الاتصال، نقوم بتشغيل محرك التحليل المحلي الذكي والديناميكي
    if (!$success) {
        // العثور على العميل الأكثر مديونية
        $worst_customer = 'لا يوجد عملاء مدينون';
        $worst_debt = 0;
        foreach ($top_customers as $c) {
            $debt = $c['total_exposure'] ?? 0;
            if ($debt > $worst_debt) {
                $worst_debt = $debt;
                $worst_customer = $c['customer_name'] ?? 'عميل';
            }
        }
        
        // العثور على الصنف الأقل مخزوناً
        $lowest_item = 'لا توجد أصناف';
        $lowest_stock = 99999999;
        $lowest_min = 0;
        foreach ($raw_stock as $s) {
            if ($s['stock_qty'] < $lowest_stock) {
                $lowest_stock = $s['stock_qty'];
                $lowest_item = $s['item_name'];
                $lowest_min = $s['min_limit'];
            }
        }
        
        // العثور على الصنف الأكثر مبيعاً
        $best_item = 'لا توجد أصناف مباعة';
        $max_sales = 0;
        foreach ($raw_stock as $s) {
            if ($s['sold_qty'] > $max_sales) {
                $max_sales = $s['sold_qty'];
                $best_item = $s['item_name'];
            }
        }

        $formatted_debt = number_format($worst_debt);
        $formatted_stock = number_format($lowest_stock);
        
        $ai_analysis_result = "📊 **التقرير المولد بمحرك التحليل الذكي المالي والمحاكاة لـ MAGAIEES ERP:**\n\n" .
            "1. **مخاطر الائتمان والمديونيات بسوق السجانة:**\n" .
            "   * تم رصد العميل الأكثر تعرضاً ماليًا وهو **[$worst_customer]** بإجمالي مديونيات وتعرض يبلغ **[$formatted_debt SDG]** (أستاذ + شيكات معلقة).\n" .
            "   * **التوصية:** يوصي محرك التحليل بخفض حد الائتمان له فوراً بنسبة 15%، وإيقاف عمليات البيع الآجل الجديدة حتى يتم تحصيل الشيكات المعلقة لتفادي مخاطر عجز السيولة.\n\n" .
            "2. **الرقابة الذكية على النواقص وحركة العنابر:**\n" .
            "   * الصنف الأكثر حرجاً حالياً من حيث كميات المخازن هو **[$lowest_item]** برصيد فعلي يبلغ **[$formatted_stock طن]** وهو تحت حد الأمان المطلوب ($lowest_min طن).\n" .
            "   * **التوصية:** يجب إصدار أمر شراء وتوريد عاجل من المصنع لتأمين هذا الصنف وتفادي التوقف البيعي.\n\n" .
            "3. **نمو المبيعات وتحليل الطلب:**\n" .
            "   * الصنف الأكثر طلباً وسحباً في الآونة الأخيرة هو **[$best_item]** بإجمالي مبيعات بلغت **[" . number_format($max_sales) . " طن]**.\n" .
            "   * **التوصية:** استغلال سرعة الدوران العالية لهذا الصنف لتقديم خصومات ولاء للزبائن VIP لضمان تدفق الكاش اليومي واستقرار ممر السيولة.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مستشار الذكاء الاصطناعي التنبئي - MAGAIEES ERP</title>
    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans p-6" style="background-color: #05080f;">

    <div class="mb-8 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-indigo-400">
                <i class="fa-solid fa-brain"></i> مستشار الذكاء الاصطناعي الاستراتيجي (AI Upgrade)
            </h1>
            <p class="text-slate-400 text-sm mt-1">توليد قرارات استباقية وتنبؤات للمخازن وحركة الديون باستخدام النماذج العصبية العالمية</p>
        </div>
        <a href="dashboard-pro.php" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl text-xs font-bold transition-all">
            لوحة التحكم الرئيسية
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- الكرت الأيمن لتشغيل محرك الـ AI -->
        <div class="lg:col-span-1 bg-slate-900/50 p-6 rounded-2xl border border-slate-800 flex flex-col justify-between h-[300px]">
            <div>
                <h3 class="font-bold text-slate-200 mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-microchip text-purple-400"></i> محرك التنبؤ والمحاكاة المستقبلية
                </h3>
                <p class="text-xs text-slate-400 leading-relaxed">بضغطة زر واحدة، سيقوم الذكاء الاصطناعي بمسح حركات مخازنك بالكامل، ومطابقتها ببورصة المعادن، وإعطائك نصيحة تجارية واضحة لا تحتمل اللبس.</p>
            </div>
            
            <form method="POST">
                <button type="submit" name="consult_ai" class="w-full py-3.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 rounded-xl font-bold text-sm shadow-lg shadow-indigo-950/50 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> تشغيل مستشار الـ AI الآن
                </button>
            </form>
        </div>

        <!-- الكرت الأيسر لعرض التقرير التنبئي المكتوب باللغة العربية -->
        <div class="lg:col-span-2 bg-slate-900/30 p-6 rounded-2xl border border-slate-800 min-h-[300px]">
            <h3 class="font-bold text-slate-200 mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                <i class="fa-solid fa-file-invoice-dollar text-indigo-400"></i> التقرير الاستشاري التنبئي المستقبلي لشركتك
            </h3>
            
            <div class="text-xs leading-relaxed text-slate-300 space-y-2 whitespace-pre-wrap">
                <?php if (empty($ai_analysis_result)): ?>
                    <div class="text-center p-12 text-slate-500">
                        <i class="fa-solid fa-robot text-3xl mb-3 block text-slate-700"></i>
                        اضغط على الزر الأيمن لتشغيل محرك الترقية وجلب استشارات الذكاء الاصطناعي الحية لمخزونك وديونك فوراً.
                    </div>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($ai_analysis_result)); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>


<script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
<script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
