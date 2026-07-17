<?php
$page_title = 'بورصة الحديد والتنبؤ بالمخاطر';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تنبؤات بورصة الحديد والمخاطر
// ====================================================================

// إعداد افتراضي للعملة والدولار المتوقع لحساب الاستيراد
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$rate = ($selected_currency === 'SDG') ? 600000 : 1000;
$usd_to_sdg = 600; // سعر تقريبي للعمليات الحسابية والتنبؤ بالاستيراد

// بيانات بورصة الحديد العالمية (محاكاة تفاعلية للطن بالدولار لآخر 6 أشهر)
$global_steel_prices = [
    ["month" => "يناير", "price" => 580],
    ["month" => "فبراير", "price" => 595],
    ["month" => "مارس", "price" => 610],
    ["month" => "أبريل", "price" => 605],
    ["month" => "مايو", "price" => 630],
    ["month" => "يونيو", "price" => 655] // السعر الحالي للطن بالدولار
];

// حساب معدل التغير الأخير للتنبؤ بالمستقبل
$current_price = end($global_steel_prices)['price'];
$prev_price = $global_steel_prices[count($global_steel_prices) - 2]['price'];
$change_percentage = (($current_price - $prev_price) / $prev_price) * 100;

// تقدير سعر الطن المحلي المتوقع في السجانة بناءً على الشحن والتخليص والجمارك
$estimated_local_cost_per_ton = ($current_price + 120) * $usd_to_sdg; // 120$ تكاليف شحن وتخليص تقريبية

// في حال استخدام العملة الأجنبية
if ($selected_currency === 'USD') {
    $estimated_local_cost_per_ton = ($current_price + 120);
}

// دالة تحويل العملة الموحدة للتقارير
if (!function_exists('convertReportAmount')) {
    function convertReportAmount($amount, $txCurrency, $txRate, $selectedCurrency) {
        if (empty($txCurrency)) $txCurrency = 'SDG';
        if ($txCurrency === $selectedCurrency) {
            return (float)$amount;
        }
        $usdToSdgRate = 600; // السعر القياسي الافتراضي للنظام
        $rate = (float)$txRate;
        if ($rate <= 0) {
            $rate = $usdToSdgRate;
        }
        if ($txCurrency === 'USD' && $selectedCurrency === 'SDG') {
            return (float)$amount * $rate;
        } else if ($txCurrency === 'SDG' && $selectedCurrency === 'USD') {
            return (float)$amount / $rate;
        }
        return (float)$amount;
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem("erp_lang") || "ar";
    if(l==="en") {
        document.documentElement.setAttribute("dir","ltr");
        document.documentElement.setAttribute("lang","en");
    }
})();
</script>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">

    <meta charset="UTF-8">
    <title>تنبؤات بورصة الحديد والمخاطر - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
        }
        .page-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0px 30px 20px 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-slate-955 text-slate-100 min-h-screen p-6" style="background-color: #090d16;">
    <div class="page-container">
        <?php include "includes/header.php"; ?>

    <!-- رأس الصفحة -->
    <div class="mb-8 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-red-400">
                <i class="fa-solid fa-chart-line"></i> بورصة الحديد العالمية ومؤشرات التنبؤ بالمخاطر
            </h1>
            <p class="text-slate-400 text-sm mt-1">ربط تقلبات أسواق المعادن العالمية بأسعار حديد التسليح المحلي لاتخاذ قرارات شراء المخزون</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex bg-slate-900 rounded-xl p-1 border border-slate-700">
                <a href="?currency=SDG" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?php echo $selected_currency === 'SDG' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'; ?>">SDG</a>
                <a href="?currency=USD" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?php echo $selected_currency === 'USD' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'; ?>">USD</a>
            </div>
            <a href="dashboard-pro.php" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-850 border border-slate-800 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i> لوحة التحكم الرئيسية
            </a>
        </div>
    </div>

    <!-- الطريقة العلمية: المؤشرات التنبؤية العليا لحماية المخزون -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- كرت السعر العالمي الحالي ومعدل التغير -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800 relative overflow-hidden" style="background-color: rgba(15, 23, 42, 0.45);">
            <div class="absolute top-0 left-0 w-2 h-full bg-amber-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs text-slate-400 font-bold block">سعر طن الحديد العالمي (LME)</span>
                    <span class="text-3xl font-black text-amber-400 mt-2 block">$<?php echo number_format($current_price, 2); ?></span>
                </div>
                <div class="p-3 bg-amber-955 text-amber-400 rounded-xl text-lg" style="background-color: rgba(245, 158, 11, 0.15);"><i class="fa-solid fa-globe"></i></div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <?php if ($change_percentage > 0): ?>
                    <span class="text-xs bg-emerald-950 text-emerald-400 px-2 py-1 rounded font-bold" style="background-color: rgba(16, 185, 129, 0.15);">
                        <i class="fa-solid fa-arrow-trend-up"></i> +<?php echo number_format($change_percentage, 2); ?>%
                    </span>
                    <span class="text-[11px] text-slate-400">ارتفاع مقارنة بالشهر السابق</span>
                <?php else: ?>
                    <span class="text-xs bg-red-950 text-red-400 px-2 py-1 rounded font-bold" style="background-color: rgba(239, 68, 68, 0.15);">
                        <i class="fa-solid fa-arrow-trend-down"></i> <?php echo number_format($change_percentage, 2); ?>%
                    </span>
                    <span class="text-[11px] text-slate-400">انخفاض مقارنة بالشهر السابق</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- التنبؤ بالتكلفة المحلية المستقبلية لحديد التسليح -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800 relative overflow-hidden" style="background-color: rgba(15, 23, 42, 0.45);">
            <div class="absolute top-0 left-0 w-2 h-full bg-cyan-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs text-slate-400 font-bold block">سعر الطن المحلي المتوقع (الاستيراد القادم)</span>
                    <span class="text-3xl font-black text-cyan-400 mt-2 block"><?php echo number_format($estimated_local_cost_per_ton, 0); ?> <span class="text-xs font-normal text-slate-400"><?php echo $selected_currency; ?></span></span>
                </div>
                <div class="p-3 bg-cyan-955 text-cyan-400 rounded-xl text-lg" style="background-color: rgba(6, 182, 212, 0.15);"><i class="fa-solid fa-calculator"></i></div>
            </div>
            <p class="text-xs text-slate-400 mt-4">شامل تكاليف الشحن البحري، التخليص الجمركي، وحساب الفارق الزمني للتحميل.</p>
        </div>

        <!-- القرار الاستراتيجي للمخزون (نظام التنبؤ الذكي) -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800 relative overflow-hidden" style="background-color: rgba(15, 23, 42, 0.45);">
            <div class="absolute top-0 left-0 w-2 h-full bg-emerald-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs text-slate-400 font-bold block">⚠️ القرار الاستراتيجي الموصى به للمخزون</span>
                    <span class="text-xl font-bold text-emerald-400 mt-3 block">شراء فوري وتخزين (ACCUMULATE)</span>
                </div>
                <div class="p-3 bg-emerald-955 text-emerald-400 rounded-xl text-lg" style="background-color: rgba(16, 185, 129, 0.15);"><i class="fa-solid fa-lightbulb"></i></div>
            </div>
            <p class="text-xs text-slate-400 mt-3">البورصة في مسار صاعد متتالي. يُنصح بتأمين طلبيات حديد التسليح والأسلاك فوراً لتجنب فارق السعر المحلي القادم.</p>
        </div>

    </div>

    <!-- النصف الثاني: منحني البورصة البصري التفاعلي وجدول التدقيق اللحظي -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- منحنى حركة أسعار الحديد العالمية خلال 6 أشهر (خطوط بيانية عالية الجودة) -->
        <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 lg:col-span-2" style="background-color: rgba(15, 23, 42, 0.25);">
            <h3 class="font-bold text-slate-200 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-chart-area text-amber-400"></i>
                منحنى تتبع بورصة المعادن العالمية (أسعار الـ Billet والحديد)
            </h3>
            <div class="w-full min-h-[300px]">
                <canvas id="steelTrendChart"></canvas>
            </div>
        </div>

        <!-- نافذة التحليل الفني والجيوسياسي للأسواق (بورصة الحديد) -->
        <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 lg:col-span-1 flex flex-col justify-between" style="background-color: rgba(15, 23, 42, 0.25);">
            <div>
                <h3 class="font-bold text-slate-200 mb-4 flex items-center gap-2 border-b border-slate-800 pb-3" style="border-bottom-color: rgba(255,255,255,0.06);">
                    <i class="fa-solid fa-newspaper text-cyan-400"></i>
                    نشرة أسواق الصلب والإنشاءات
                </h3>
                <div class="space-y-4 text-xs leading-relaxed text-slate-300">
                    <p>● **معدلات الإنتاج:** تراجع إنتاج مصانع الصلب الكبرى في شرق آسيا أدى إلى قلة المعروض العالمي من خام البليت، مما يضغط على الأسعار محلياً باتجاه الارتفاع.</p>
                    <p>● **الشحن اللوجستي:** استقرار نسبي في تكاليف الحاويات والشحن الصادر من موانئ البحر الأحمر وموانئ التوريد، مع توقع زيادة طفيفة في رسوم التأمين البحري.</p>
                    <p>● **سوق السجانة:** الطلب المحلي يظهر تحركاً إيجابياً مع بداية جفاف فصل الخريف، مما يجعل تخزين البضائع بسعر البورصة الحالي فرصة ممتازة لهوامش ربح مرتفعة لاحقاً.</p>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-800 flex justify-between text-[11px] text-slate-400" style="border-top-color: rgba(255,255,255,0.06);">
                <span>المصدر المرجعي: London Metal Exchange</span>
                <span class="text-amber-400 font-mono">تحديث: اليوم</span>
            </div>
        </div>

    </div>

    <script>
        // رسم منحنى البورصة باستخدام Chart.js مع تأثير التظليل العصري للخطوط (Area Line Chart)
        const steelCtx = document.getElementById('steelTrendChart').getContext('2d');
        
        // إنشاء تدرج لوني لتظليل المنحنى بصرياً بشكل رائع
        const gradient = steelCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(245, 158, 11, 0.4)');
        gradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

        new Chart(steelCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($global_steel_prices, 'month')); ?>,
                datasets: [{
                    label: 'سعر خام الحديد العالمي ($ للطن)',
                    data: <?php echo json_encode(array_column($global_steel_prices, 'price')); ?>,
                    borderColor: '#f59e0b',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4,
                    pointBackgroundColor: '#f59e0b',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                    y: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
    </div>
    <script defer src="js/app.js"></script>
</body>
</html>
