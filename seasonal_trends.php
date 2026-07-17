<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - استهلاك الأصناف الموسمية
// ====================================================================
require_once 'db_bridge.php';
$selected_currency = isset($_GET['currency']) ? $_GET['currency'] : 'SDG';
$pdo = getDBConnection();

$data = getSeasonalTrendsData($pdo);
$is_mock_data = empty($data) || (isset($data[0]['item_name']) && $data[0]['item_name'] === 'حديد تسليح 5 لينيا الأسعد' && count($data) <= 2);

// حساب المؤشرات
$total_items = count($data);
$total_annual = array_sum(array_column($data, 'total_annual_qty'));
$total_autumn = array_sum(array_column($data, 'autumn_rainy_season_qty'));
$total_summer = array_sum(array_column($data, 'summer_dry_season_qty'));
$dominant_season = $total_summer >= $total_autumn ? 'الصيف (الجفاف)' : 'الخريف (المطر)';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استهلاك الأصناف الموسمية | Al-Sajana ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Cairo',sans-serif;background:#030409;}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    .anim{animation:fadeInUp .6s ease-out backwards}
    </style>
</head>
<body class="text-slate-100 min-h-screen p-6">

    <div class="max-w-7xl mx-auto">

        <?php if ($is_mock_data): ?>
        <div class="bg-amber-900/30 border border-amber-500/30 text-amber-300 px-4 py-2 rounded-xl text-sm text-center mb-6 font-bold">
            ⚠️ الشاشة تعرض بيانات تجريبية حالياً — سيتم عرض البيانات الحقيقية عند توفر عمليات بيعية في النظام
        </div>
        <?php endif; ?>

        <!-- الهيدر -->
        <div class="mb-8 border-b border-slate-800 pb-4 flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-cyan-400">
                    <i class="fa-solid fa-cloud-sun-rain"></i> استهلاك الأصناف الموسمية
                </h1>
                <p class="text-slate-400 text-sm mt-1">تحليل سحب الحديد والأسمنت وتأثيره على التدفقات حسب أشهر السنة</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-900 rounded-xl p-1 border border-slate-700">
                    <a href="?currency=SDG" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?php echo $selected_currency === 'SDG' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'; ?>">SDG</a>
                    <a href="?currency=USD" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?php echo $selected_currency === 'USD' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'; ?>">USD</a>
                </div>
                <a href="smart_reports_hub.php?currency=<?php echo $selected_currency; ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-xl text-sm font-bold transition-all">
                    البوابة <i class="fa-solid fa-arrow-left mr-1"></i>
                </a>
            </div>
        </div>

        <!-- المؤشرات -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-slate-800 flex items-center justify-between" style="animation-delay:.1s">
                <div><span class="text-xs text-slate-400 font-bold block">عدد الأصناف المتتبعة</span><span class="text-3xl font-black text-slate-200 mt-1 block"><?php echo $total_items; ?></span></div>
                <div class="p-3 rounded-xl text-lg" style="background:rgba(20,184,166,.15);color:#2dd4bf"><i class="fa-solid fa-boxes-stacked"></i></div>
            </div>
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-slate-800 flex items-center justify-between" style="animation-delay:.2s">
                <div><span class="text-xs text-slate-400 font-bold block">الموسم الأعلى استهلاكاً</span><span class="text-xl font-black text-amber-400 mt-1 block"><?php echo $dominant_season; ?></span></div>
                <div class="p-3 rounded-xl text-lg" style="background:rgba(245,158,11,.15);color:#fbbf24"><i class="fa-solid fa-sun"></i></div>
            </div>
            <div class="anim bg-slate-900/70 p-5 rounded-2xl border border-slate-800 flex items-center justify-between" style="animation-delay:.3s">
                <div><span class="text-xs text-slate-400 font-bold block">إجمالي الكمية السنوية</span><span class="text-3xl font-black text-cyan-400 mt-1 block"><?php echo number_format($total_annual); ?></span></div>
                <div class="p-3 rounded-xl text-lg" style="background:rgba(6,182,212,.15);color:#22d3ee"><i class="fa-solid fa-chart-column"></i></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- الرسم البياني -->
            <div class="anim bg-slate-900/60 rounded-2xl border border-slate-800 p-6" style="animation-delay:.4s">
                <h3 class="text-lg font-bold mb-4 text-slate-200"><i class="fa-solid fa-chart-bar ml-2 text-teal-400"></i>مقارنة الاستهلاك الموسمي</h3>
                <canvas id="seasonChart" height="300"></canvas>
            </div>

            <!-- الجدول -->
            <div class="anim bg-slate-900/60 rounded-2xl border border-slate-800 p-6" style="animation-delay:.5s">
                <h3 class="text-lg font-bold mb-4 text-slate-200"><i class="fa-solid fa-table-list ml-2 text-teal-400"></i>تفصيل الأصناف</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-slate-700">
                                <th class="text-right p-3 text-slate-400 font-bold">اسم الصنف</th>
                                <th class="text-right p-3 text-slate-400 font-bold">🌧️ الخريف</th>
                                <th class="text-right p-3 text-slate-400 font-bold">☀️ الصيف</th>
                                <th class="text-right p-3 text-slate-400 font-bold">الإجمالي</th>
                                <th class="text-right p-3 text-slate-400 font-bold">الفرق</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row):
                                $diff = $row['summer_dry_season_qty'] - $row['autumn_rainy_season_qty'];
                                $diff_color = $diff > 0 ? 'text-emerald-400' : ($diff < 0 ? 'text-red-400' : 'text-slate-400');
                                $dominant = $row['summer_dry_season_qty'] >= $row['autumn_rainy_season_qty'];
                            ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/30 transition-colors">
                                <td class="p-3 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td class="p-3 <?php echo !$dominant ? 'text-cyan-400 font-bold' : 'text-slate-300'; ?>"><?php echo number_format($row['autumn_rainy_season_qty']); ?></td>
                                <td class="p-3 <?php echo $dominant ? 'text-amber-400 font-bold' : 'text-slate-300'; ?>"><?php echo number_format($row['summer_dry_season_qty']); ?></td>
                                <td class="p-3 font-bold text-slate-200"><?php echo number_format($row['total_annual_qty']); ?></td>
                                <td class="p-3 font-bold <?php echo $diff_color; ?>"><?php echo ($diff > 0 ? '+' : '') . number_format($diff); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-600">آخر تحديث: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026</div>
    </div>

    <script>
    new Chart(document.getElementById('seasonChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($data, 'item_name'), JSON_UNESCAPED_UNICODE); ?>,
            datasets: [
                { label: '🌧️ الخريف (المطر)', data: <?php echo json_encode(array_column($data, 'autumn_rainy_season_qty')); ?>, backgroundColor: 'rgba(6,182,212,0.7)', borderRadius: 8 },
                { label: '☀️ الصيف (الجفاف)', data: <?php echo json_encode(array_column($data, 'summer_dry_season_qty')); ?>, backgroundColor: 'rgba(245,158,11,0.7)', borderRadius: 8 }
            ]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'Cairo', size: 12 } } } },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.03)' } },
                y: { ticks: { color: '#e2e8f0', font: { family: 'Cairo', weight: 'bold' } }, grid: { display: false } }
            }
        }
    });
    </script>
</body>
</html>
