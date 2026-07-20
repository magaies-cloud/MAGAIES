<?php
$page_title = 'مصفوفة تكرار الزيارة والولاء';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مصفوفة تكرار الزيارة والولاء
// ====================================================================
require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

$data = getCustomerEngagementData($pdo);
$is_mock_data = false;

// حساب المؤشرات
$total = count($data);
$vip = 0; $regular = 0; $occasional = 0;
foreach ($data as $row) {
    $t = $row['engagement_tier'] ?? '';
    if ($t === 'VIP') $vip++;
    elseif ($t === 'REGULAR') $regular++;
    else $occasional++;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مصفوفة تكرار الزيارة والولاء | Al-Sajana ERP</title>
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
body{font-family:'Cairo',sans-serif;background:#030409;}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    .anim{animation:fadeInUp .6s ease-out backwards}
    </style>
</head>
<body class="text-slate-100 min-h-screen p-6">
    <div class="page-container">
        <?php include "includes/header.php"; ?>

    <div class="max-w-7xl mx-auto">

        <?php if ($is_mock_data): ?>
        <div class="bg-amber-900/30 border border-amber-500/30 text-amber-300 px-4 py-2 rounded-xl text-sm text-center mb-6 font-bold">
            ⚠️ الشاشة تعرض بيانات تجريبية حالياً — سيتم عرض البيانات الحقيقية عند توفر عمليات بيعية في النظام
        </div>
        <?php endif; ?>

        <!-- الهيدر -->
        

        <!-- المؤشرات -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-slate-800" style="animation-delay:.1s">
                <span class="text-xs text-slate-400 font-bold block">إجمالي العملاء</span>
                <span class="text-3xl font-black text-slate-200 mt-1 block"><?php echo $total; ?></span>
            </div>
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-purple-900/40" style="animation-delay:.2s;background:rgba(88,28,135,.12)">
                <span class="text-xs text-purple-300 font-bold block">⭐ VIP</span>
                <span class="text-3xl font-black text-purple-400 mt-1 block"><?php echo $vip; ?></span>
                <span class="text-xs text-purple-400/60">15+ زيارة</span>
            </div>
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-blue-900/40" style="animation-delay:.3s;background:rgba(30,58,138,.12)">
                <span class="text-xs text-blue-300 font-bold block">🔄 مستمر</span>
                <span class="text-3xl font-black text-blue-400 mt-1 block"><?php echo $regular; ?></span>
                <span class="text-xs text-blue-400/60">5-14 زيارة</span>
            </div>
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-slate-700" style="animation-delay:.4s">
                <span class="text-xs text-slate-400 font-bold block">⏱️ عابر</span>
                <span class="text-3xl font-black text-slate-400 mt-1 block"><?php echo $occasional; ?></span>
                <span class="text-xs text-slate-500">أقل من 5 زيارات</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- الجدول -->
            <div class="lg:col-span-2 anim bg-slate-900/60 rounded-2xl border border-slate-800 p-6" style="animation-delay:.5s">
                <h3 class="text-lg font-bold mb-4 text-slate-200"><i class="fa-solid fa-ranking-star ml-2 text-purple-400"></i>تصنيف العملاء حسب الولاء</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-slate-700">
                                <th class="text-right p-3 text-slate-400 font-bold">#</th>
                                <th class="text-right p-3 text-slate-400 font-bold">اسم العميل</th>
                                <th class="text-right p-3 text-slate-400 font-bold">عدد الزيارات</th>
                                <th class="text-right p-3 text-slate-400 font-bold">إجمالي المشتريات</th>
                                <th class="text-right p-3 text-slate-400 font-bold">تصنيف الولاء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $i => $row):
                                $tier = $row['engagement_tier'] ?? 'OCCASIONAL';
                                if ($tier === 'VIP') { $badge_cls = 'bg-purple-900/40 text-purple-300 border-purple-500/30'; $icon = 'fa-star'; $label = '⭐ VIP'; }
                                elseif ($tier === 'REGULAR') { $badge_cls = 'bg-blue-900/40 text-blue-300 border-blue-500/30'; $icon = 'fa-check-circle'; $label = '🔄 مستمر'; }
                                else { $badge_cls = 'bg-slate-800 text-slate-400 border-slate-600'; $icon = 'fa-clock'; $label = '⏱️ عابر'; }
                            ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/30 transition-colors">
                                <td class="p-3 text-slate-500"><?php echo $i+1; ?></td>
                                <td class="p-3 font-bold text-slate-200"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td class="p-3 font-bold text-cyan-400"><?php echo $row['total_visit_count']; ?></td>
                                <td class="p-3 text-slate-300"><?php echo number_format($row['total_spend'], 0); ?> <span class="text-xs text-slate-500"><?php echo $selected_currency; ?></span></td>
                                <td class="p-3"><span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $badge_cls; ?>"><?php echo $label; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- الرسم البياني -->
            <div class="anim bg-slate-900/60 rounded-2xl border border-slate-800 p-6" style="animation-delay:.6s">
                <h3 class="text-lg font-bold mb-4 text-slate-200"><i class="fa-solid fa-chart-bar ml-2 text-purple-400"></i>الزيارات حسب العميل</h3>
                <canvas id="engageChart" height="350"></canvas>
                <div class="flex gap-3 mt-4 text-xs font-bold justify-center">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full inline-block" style="background:#a855f7"></span> VIP</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full inline-block" style="background:#3b82f6"></span> مستمر</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full inline-block" style="background:#64748b"></span> عابر</span>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-600">آخر تحديث: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026</div>
    </div>

    <script>
    new Chart(document.getElementById('engageChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($data, 'customer_name'), JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                label: 'عدد الزيارات',
                data: <?php echo json_encode(array_column($data, 'total_visit_count')); ?>,
                backgroundColor: <?php echo json_encode(array_map(function($r){
                    $t = $r['engagement_tier'] ?? '';
                    return $t === 'VIP' ? 'rgba(168,85,247,0.7)' : ($t === 'REGULAR' ? 'rgba(59,130,246,0.7)' : 'rgba(100,116,139,0.5)');
                }, $data)); ?>,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.03)' } },
                y: { ticks: { color: '#e2e8f0', font: { family: 'Cairo', size: 11, weight: 'bold' } }, grid: { display: false } }
            }
        }
    });
    </script>
    </div>
    <script defer src="js/app.js"></script>
</body>
</html>
