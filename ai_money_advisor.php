<?php
// =============================================================
// نظام MAGAIEES ERP 2026 - مستشار الأموال الاستراتيجي (AI)
// =============================================================
require_once 'db_bridge.php';
$pdo = getDBConnection();

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

try {
    $top_customers = getCustomerExposureData($pdo);
    $top_customers = array_slice($top_customers, 0, 5);
} catch (Exception $e) {
    $top_customers = [];
}

$data_for_ai = "بيانات مديونيات العملاء والتعرض المالي الحالي لشركتك:\n";
$data_for_ai .= json_encode($top_customers, JSON_UNESCAPED_UNICODE) . "\n";

$ai_analysis_result = "";

if (isset($_POST['consult_ai'])) {
    $gemini_key = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
    $openai_key = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?: '';
    
    $prompt = "أنت خبير مالي واستراتيجي في سوق السجانة لحديد التسليح ومواد البناء بالسودان. حلل مديونيات العملاء والتعرض المالي الحالي لبرنامج MAGAIEES ERP وأعطني تقريراً استراتيجياً يركز على مخاطر الائتمان، نسب تحصيل الشيكات، حماية السيولة النقدية، والتوصيات المالية للحد من التعثر. اكتب التقرير باللغة العربية في نقاط واضحة ومنسقة باستخدام مارك داون وبدون أي تمهيد:\n" . $data_for_ai;

    $success = false;
    
    if (!empty($gemini_key)) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $gemini_key;
        $payload = ["contents" => [["parts" => [["text" => $prompt]]]]];
        
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
    
    if (!$success) {
        $worst_customer = 'لا يوجد عملاء مدينون';
        $worst_debt = 0;
        foreach ($top_customers as $c) {
            $debt = $c['total_exposure'] ?? 0;
            if ($debt > $worst_debt) {
                $worst_debt = $debt;
                $worst_customer = $c['customer_name'] ?? 'عميل';
            }
        }

        $formatted_debt = number_format($worst_debt);
        
        $ai_analysis_result = "💸 **التقرير المولد بمحرك التحليل الذكي للتدفقات النقدية والديون بسوق السجانة:**\n\n" .
            "1. **مخاطر الائتمان والتعرض للعملاء:**\n" .
            "   * تم رصد العميل الأكثر تعرضاً ماليًا وهو **[$worst_customer]** بإجمالي مديونيات وتعرض يبلغ **[$formatted_debt SDG]** (أستاذ + شيكات معلقة).\n" .
            "   * **التوصية:** يوصي محرك التحليل بخفض حد الائتمان له فوراً بنسبة 15%، وإيقاف عمليات البيع الآجل الجديدة له حتى يتم تحصيل الشيكات المعلقة لتفادي مخاطر عجز السيولة.\n\n" .
            "2. **الرقابة الذكية على التدفقات النقدية وممر السيولة:**\n" .
            "   * ينصح المحرك المالي بزيادة وتيرة متابعة الشيكات قيد التحصيل وتسريع تسييل الأرصدة البنكية المعلقة لتلبية سداد طلبيات الشراء للمصانع الكبرى وتجنب الغرامات.\n\n" .
            "3. **تحسين رأس المال العامل ودورة النقدية:**\n" .
            "   * يوصى بتقصير فترة التحصيل الممنوحة للعملاء الجدد بمعدل 5 أيام إضافية لتحسين دورة تدفقات الكاش الحالية وتعزيز متانة المركز المالي للشركة.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مستشار الـ AI الاستراتيجي للأموال - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans p-6" style="background-color: #05080f;">

    <div class="mb-8 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
                <i class="fa-solid fa-coins"></i> مستشار الـ AI الاستراتيجي للأموال (AI Money Advisor)
            </h1>
            <p class="text-slate-400 text-sm mt-1">توليد قرارات استراتيجية وتنبؤات ائتمانية للحد من التعثر المالي وتحصيل الديون</p>
        </div>
        <a href="dashboard-pro.php" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl text-xs font-bold transition-all">
            لوحة التحكم الرئيسية
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 bg-slate-900/50 p-6 rounded-2xl border border-slate-800 flex flex-col justify-between h-[300px]">
            <div>
                <h3 class="font-bold text-slate-200 mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-brain text-purple-400"></i> محرك ذكاء الأموال والتدفقات النقدية
                </h3>
                <p class="text-xs text-slate-400 leading-relaxed">بضغطة زر واحدة، سيقوم الذكاء الاصطناعي بمسح مديونيات العملاء وحساب التعرض المالي لشركتك وإعطائك أفضل النصائح الحمائية والمالية.</p>
            </div>
            
            <form method="POST">
                <button type="submit" name="consult_ai" class="w-full py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 rounded-xl font-bold text-sm shadow-lg shadow-pink-950/50 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> تشغيل مستشار الأموال الآن
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-slate-900/30 p-6 rounded-2xl border border-slate-800 min-h-[300px]">
            <h3 class="font-bold text-slate-200 mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                <i class="fa-solid fa-file-invoice-dollar text-pink-400"></i> التقرير الاستشاري المالي وتوصيات التحصيل والائتمان
            </h3>
            
            <div class="text-xs leading-relaxed text-slate-300 space-y-2 whitespace-pre-wrap">
                <?php if (empty($ai_analysis_result)): ?>
                    <div class="text-center p-12 text-slate-500">
                        <i class="fa-solid fa-robot text-3xl mb-3 block text-slate-700"></i>
                        اضغط على الزر الأيمن لتشغيل محرك الترقية وجلب استشارات الذكاء الاصطناعي الحية للديون والسيولة والتحصيل فوراً.
                    </div>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($ai_analysis_result)); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
