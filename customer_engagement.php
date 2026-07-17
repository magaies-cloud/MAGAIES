<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مصفوفة تكرار الزيارة والولاء
// ====================================================================
require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

$data = getCustomerEngagementData($pdo);
$is_mock_data = empty($data) || (isset($data[0]['customer_name']) && $data[0]['customer_name'] === 'شركة النيل للتجارة' && count($data) <= 2);

// حساب المؤشرات
$total = count($data);
$vip = 0; $regular = 0; $occasional = 0;
foreach ($data as $row) {
    $t = $row['engagement_tier'] ?? '';
    if ($t === 'VIP') $vip++;
    elseif ($t === 'REGULAR') $regular++;
    else $occasional++;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مصفوفة تكرار الزيارة والولاء | Al-Sajana ERP</title>
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
                <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">
                    <i class="fa-solid fa-gem"></i> مصفوفة تكرار الزيارة والولاء
                </h1>
                <p class="text-slate-400 text-sm mt-1">تصنيف الزبائن (VIP، مستمر، عابر) بناءً على حجم التعامل وعدد الزيارات</p>
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
</body>
</html>
