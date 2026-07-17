<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - معدل دوران وانقطاع الزبائن
// ====================================================================
require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

$data = getCustomerChurnData($pdo);
$is_mock_data = empty($data) || (isset($data[0]['customer_name']) && $data[0]['customer_name'] === 'شركة النيل للتجارة والإنشاءات' && count($data) <= 3);

// حساب المؤشرات
$total = count($data);
$active = 0; $medium = 0; $high = 0;
foreach ($data as $row) {
    $s = $row['churn_risk_status'] ?? '';
    if ($s === 'ACTIVE') $active++;
    elseif ($s === 'MEDIUM RISK') $medium++;
    elseif ($s === 'HIGH RISK') $high++;
}
$at_risk = $medium + $high;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معدل دوران وانقطاع الزبائن | Al-Sajana ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Cairo',sans-serif;background:#030409;}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    .anim-card{animation:fadeInUp .6s ease-out backwards}
    .anim-card:nth-child(1){animation-delay:.1s}.anim-card:nth-child(2){animation-delay:.2s}.anim-card:nth-child(3){animation-delay:.3s}
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
                <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-400">
                    <i class="fa-solid fa-person-walking-arrow-right"></i> معدل دوران وانقطاع الزبائن
                </h1>
                <p class="text-slate-400 text-sm mt-1">اكتشاف العملاء المنقطعين وتحديد درجة خطورة فقدانهم بناءً على آخر نشاط بيعي</p>
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
            <div class="anim-card bg-slate-900/70 p-5 rounded-2xl border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-400 font-bold block">إجمالي العملاء</span>
                    <span class="text-3xl font-black text-slate-200 mt-1 block"><?php echo $total; ?></span>
                </div>
                <div class="p-3 rounded-xl text-lg" style="background:rgba(99,102,241,.15);color:#818cf8"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="anim-card bg-slate-900/70 p-5 rounded-2xl border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-400 font-bold block">عملاء نشطون</span>
                    <span class="text-3xl font-black text-emerald-400 mt-1 block"><?php echo $active; ?></span>
                    <span class="text-xs text-emerald-400/70"><?php echo $total > 0 ? round(($active/$total)*100) : 0; ?>% من الإجمالي</span>
                </div>
                <div class="p-3 rounded-xl text-lg" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-heart-pulse"></i></div>
            </div>
            <div class="anim-card bg-slate-900/70 p-5 rounded-2xl border border-red-900/40 flex items-center justify-between" style="background:rgba(127,29,29,.15)">
                <div>
                    <span class="text-xs text-red-300 font-bold block">⚠️ عملاء معرضون للخطر</span>
                    <span class="text-3xl font-black text-red-400 mt-1 block"><?php echo $at_risk; ?></span>
                    <span class="text-xs text-amber-400"><?php echo $medium; ?> متوسط · <?php echo $high; ?> مرتفع</span>
                </div>
                <div class="p-3 rounded-xl text-lg" style="background:rgba(239,68,68,.15);color:#f87171"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- الجدول -->
            <div class="lg:col-span-2 bg-slate-900/60 rounded-2xl border border-slate-800 p-6" style="animation:fadeInUp .7s ease-out .3s backwards">
                <h3 class="text-lg font-bold mb-4 text-slate-200"><i class="fa-solid fa-table-list ml-2 text-orange-400"></i>تفصيل حالة العملاء</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-slate-700">
                                <th class="text-right p-3 text-slate-400 font-bold">#</th>
                                <th class="text-right p-3 text-slate-400 font-bold">اسم العميل</th>
                                <th class="text-right p-3 text-slate-400 font-bold">تاريخ آخر عملية</th>
                                <th class="text-right p-3 text-slate-400 font-bold">عدد الأيام</th>
                                <th class="text-right p-3 text-slate-400 font-bold">حالة الخطورة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $i => $row):
                                $status = $row['churn_risk_status'] ?? 'ACTIVE';
                                $badge_cls = $status === 'ACTIVE' ? 'bg-emerald-900/40 text-emerald-400 border-emerald-500/30' :
                                    ($status === 'MEDIUM RISK' ? 'bg-amber-900/40 text-amber-400 border-amber-500/30' :
                                    'bg-red-900/40 text-red-400 border-red-500/30');
                                $icon = $status === 'ACTIVE' ? 'fa-check-circle' : ($status === 'MEDIUM RISK' ? 'fa-exclamation-triangle' : 'fa-skull-crossbones');
                                $days = $row['days_since_last_purchase'] ?? 'N/A';
                                $days_color = is_numeric($days) ? ($days > 60 ? 'text-red-400' : ($days > 30 ? 'text-amber-400' : 'text-emerald-400')) : 'text-slate-400';
                            ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/30 transition-colors">
                                <td class="p-3 text-slate-500"><?php echo $i+1; ?></td>
                                <td class="p-3 font-bold text-slate-200"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td class="p-3 text-slate-300"><?php echo htmlspecialchars($row['last_purchase_date'] ?? 'غير متوفر'); ?></td>
                                <td class="p-3 font-bold <?php echo $days_color; ?>"><?php echo is_numeric($days) ? $days . ' يوم' : $days; ?></td>
                                <td class="p-3"><span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $badge_cls; ?>"><i class="fa-solid <?php echo $icon; ?> ml-1"></i><?php echo $status; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- الرسم البياني -->
            <div class="bg-slate-900/60 rounded-2xl border border-slate-800 p-6 flex flex-col items-center justify-center" style="animation:fadeInUp .7s ease-out .5s backwards">
                <h3 class="text-lg font-bold mb-4 text-slate-200"><i class="fa-solid fa-chart-pie ml-2 text-orange-400"></i>توزيع المخاطر</h3>
                <canvas id="churnChart" style="max-width:280px;max-height:280px;"></canvas>
                <div class="flex gap-4 mt-4 text-xs font-bold">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> نشط</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> متوسط</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> مرتفع</span>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-600">آخر تحديث: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026</div>
    </div>

    <script>
    new Chart(document.getElementById('churnChart'), {
        type: 'doughnut',
        data: {
            labels: ['نشط', 'متوسط الخطورة', 'مرتفع الخطورة'],
            datasets: [{
                data: [<?php echo "$active, $medium, $high"; ?>],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: { legend: { display: false } }
        }
    });
    </script>
</body>
</html>
