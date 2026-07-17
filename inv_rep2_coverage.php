<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$report_data = getInventoryReportData($pdo);
usort($report_data, function($a, $b) { return $a['days_coverage'] <=> $b['days_coverage']; });
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>أيام تغطية المخزون الفعلي - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-emerald-400"><i class="fa-solid fa-hourglass-half"></i> تقرير أيام التغطية المتبقية للمخزون الفعلي</h1>
            <p class="text-slate-500 text-xs mt-1">مؤشر زمني يكشف كم يوماً تكفي البضاعة الحالية في المخزن قبل النفاد</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-800">العودة للبوابة</a>
    </div>

    <!-- الجدول العلوي -->
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center">الكمية الحالية</th>
                    <th class="p-4 font-bold text-center">معدل السحب اليومي (طن)</th>
                    <th class="p-4 font-bold text-center text-emerald-400">أيام التغطية المتبقية</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono"><?php echo number_format($row['stock_qty']); ?> طن</td>
                        <td class="p-4 text-center font-mono text-slate-400"><?php echo number_format($row['avg_daily_sales'], 2); ?></td>
                        <td class="p-4 text-center font-mono font-bold text-emerald-400 bg-emerald-500/5">
                            <?php echo $row['days_coverage'] == 999 ? 'مخزون راكد / لا سحب' : $row['days_coverage'] . ' يوم'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- المخطط البياني السفلي -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
        <h3 class="text-xs font-bold text-slate-300 mb-4">📊 مخطط أيام التغطية الزمنية لكل صنف</h3>
        <div class="h-80">
            <canvas id="coverageChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('coverageChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.map(r => r.item_name),
                datasets: [{
                    label: 'أيام التغطية المتبقية (يوم)',
                    data: reportData.map(r => r.days_coverage == 999 ? 100 : r.days_coverage),
                    backgroundColor: reportData.map(r => r.days_coverage <= 30 ? '#ef4444' : (r.days_coverage <= 90 ? '#f59e0b' : '#10b981'))
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } }
                }
            }
        });
    </script>
</body>
</html>