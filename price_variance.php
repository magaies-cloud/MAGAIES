<?php
$page_title = 'احترافية التسعير وانحراف السعر';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - احترافية التسعير وانحراف السعر
// ====================================================================
require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

$data = getPriceVarianceData($pdo);
$is_mock_data = empty($data) || (isset($data[0]['transaction_id']) && $data[0]['transaction_id'] === 'INV-2026-92' && count($data) <= 2);

// حساب المؤشرات
$total_flagged = count($data);
$avg_margin = $total_flagged > 0 ? round(array_sum(array_column($data, 'profit_margin_percentage')) / $total_flagged, 2) : 0;
$min_margin_row = !empty($data) ? $data[0] : null; // البيانات مرتبة تصاعدياً حسب الهامش
$critical_count = 0; $warning_count = 0;
foreach ($data as $r) {
    if ($r['profit_margin_percentage'] < 2) $critical_count++;
    else $warning_count++;
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
    <title>احترافية التسعير وانحراف السعر | Al-Sajana ERP</title>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-slate-800" style="animation-delay:.1s">
                <span class="text-xs text-slate-400 font-bold block">عمليات مُنبَّه عليها</span>
                <span class="text-3xl font-black text-slate-200 mt-1 block"><?php echo $total_flagged; ?></span>
            </div>
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-slate-800" style="animation-delay:.2s">
                <span class="text-xs text-slate-400 font-bold block">متوسط الهامش %</span>
                <span class="text-3xl font-black <?php echo $avg_margin < 2 ? 'text-red-400' : 'text-amber-400'; ?> mt-1 block"><?php echo $avg_margin; ?>%</span>
            </div>
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-red-900/40" style="animation-delay:.3s;background:rgba(127,29,29,.1)">
                <span class="text-xs text-red-300 font-bold block">🔴 حرج (أقل من 2%)</span>
                <span class="text-3xl font-black text-red-400 mt-1 block"><?php echo $critical_count; ?></span>
            </div>
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-amber-900/40" style="animation-delay:.4s;background:rgba(120,53,15,.1)">
                <span class="text-xs text-amber-300 font-bold block">🟡 تحذير (2-5%)</span>
                <span class="text-3xl font-black text-amber-400 mt-1 block"><?php echo $warning_count; ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- الجدول -->
            <div class="lg:col-span-2 anim bg-slate-900/60 rounded-2xl border border-slate-800 p-6" style="animation-delay:.5s">
                <h3 class="text-lg font-bold mb-4 text-slate-200"><i class="fa-solid fa-magnifying-glass-dollar ml-2 text-amber-400"></i>الفواتير ذات الهوامش المنخفضة</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-slate-700">
                                <th class="text-right p-3 text-slate-400 font-bold">الفاتورة</th>
                                <th class="text-right p-3 text-slate-400 font-bold">العميل</th>
                                <th class="text-right p-3 text-slate-400 font-bold">الصنف</th>
                                <th class="text-right p-3 text-slate-400 font-bold">سعر البيع</th>
                                <th class="text-right p-3 text-slate-400 font-bold">التكلفة</th>
                                <th class="text-right p-3 text-slate-400 font-bold">الهامش</th>
                                <th class="text-right p-3 text-slate-400 font-bold">النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row):
                                $pct = $row['profit_margin_percentage'];
                                $badge = $pct < 2 ? 'bg-red-900/40 text-red-400 border-red-500/30' : 'bg-amber-900/40 text-amber-400 border-amber-500/30';
                            ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/30 transition-colors">
                                <td class="p-3 text-indigo-400 font-bold"><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                <td class="p-3 text-slate-200 font-bold"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td class="p-3 text-slate-300"><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td class="p-3 text-slate-300"><?php echo number_format($row['actual_selling_price'], 0); ?></td>
                                <td class="p-3 text-slate-300"><?php echo number_format($row['stock_cost_price'], 0); ?></td>
                                <td class="p-3 font-bold <?php echo $pct < 2 ? 'text-red-400' : 'text-amber-400'; ?>"><?php echo number_format($row['unit_margin'], 0); ?></td>
                                <td class="p-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold border <?php echo $badge; ?>"><?php echo $pct; ?>%</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- الرسم البياني -->
            <div class="anim bg-slate-900/60 rounded-2xl border border-slate-800 p-6" style="animation-delay:.6s">
                <h3 class="text-lg font-bold mb-4 text-slate-200"><i class="fa-solid fa-chart-bar ml-2 text-amber-400"></i>هوامش الربح</h3>
                <canvas id="varianceChart" height="350"></canvas>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-600">آخر تحديث: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026</div>
    </div>

    <script>
    new Chart(document.getElementById('varianceChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_map(function($r){ return $r['item_name'] . ' (' . $r['transaction_id'] . ')'; }, $data), JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                label: 'هامش الربح %',
                data: <?php echo json_encode(array_column($data, 'profit_margin_percentage')); ?>,
                backgroundColor: <?php echo json_encode(array_map(function($r){ return $r['profit_margin_percentage'] < 2 ? 'rgba(239,68,68,0.7)' : 'rgba(245,158,11,0.7)'; }, $data)); ?>,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                annotation: {}
            },
            scales: {
                x: { ticks: { color: '#64748b', callback: v => v + '%' }, grid: { color: 'rgba(255,255,255,0.03)' } },
                y: { ticks: { color: '#e2e8f0', font: { family: 'Cairo', size: 11 } }, grid: { display: false } }
            }
        }
    });
    </script>
    </div>
    <script defer src="js/app.js"></script>
</body>
</html>
