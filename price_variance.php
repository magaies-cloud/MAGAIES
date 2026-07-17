<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - احترافية التسعير وانحراف السعر
// ====================================================================
require_once 'db_bridge.php';
$selected_currency = isset($_GET['currency']) ? $_GET['currency'] : 'SDG';
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
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>احترافية التسعير وانحراف السعر | Al-Sajana ERP</title>
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
                <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-500">
                    <i class="fa-solid fa-chart-line"></i> احترافية التسعير وانحراف السعر
                </h1>
                <p class="text-slate-400 text-sm mt-1">مراقبة الفواتير المباعة بهوامش ربح منخفضة جداً (أقل من 5% من التكلفة)</p>
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
</body>
</html>
