<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$report_data = getSeasonalTrendsData($pdo);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>السحب والطلب الموسمي - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-emerald-400"><i class="fa-solid fa-cloud-sun"></i> تقرير السحب والطلب الموسمي للبضائع</h1>
            <p class="text-slate-500 text-xs mt-1">يقارن حجم المبيعات خلال موسم الأمطار والخريف مقابل موسم الجفاف والصيف</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-800">العودة للبوابة</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center">سحب الخريف والأمطار (طن)</th>
                    <th class="p-4 font-bold text-center">سحب الصيف والجفاف (طن)</th>
                    <th class="p-4 font-bold text-center text-emerald-400">إجمالي السحب السنوي</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono text-cyan-400 bg-cyan-500/5"><?php echo number_format($row['autumn_rainy_season_qty']); ?></td>
                        <td class="p-4 text-center font-mono text-amber-400 bg-amber-500/5"><?php echo number_format($row['summer_dry_season_qty']); ?></td>
                        <td class="p-4 text-center font-mono font-bold text-emerald-400 bg-emerald-500/5"><?php echo number_format($row['total_annual_qty']); ?> طن</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- المخطط البياني في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 mb-6">
        <h3 class="text-xs font-bold text-slate-300 mb-4">📈 مقارنة بيانية للسحب والطلب الموسمي للأصناف</h3>
        <div class="h-80">
            <canvas id="seasonalChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('seasonalChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.map(r => r.item_name),
                datasets: [
                    { label: 'سحب الخريف والأمطار', data: reportData.map(r => r.autumn_rainy_season_qty), backgroundColor: '#06b6d4' },
                    { label: 'سحب الصيف والجفاف', data: reportData.map(r => r.summer_dry_season_qty), backgroundColor: '#fbbf24' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } }
                }
            }
        });
    </script>
</body>
</html>