<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$report_data = getInventoryReportData($pdo);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حدود الأمان الأربعة للمخزون - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-cyan-400"><i class="fa-solid fa-shield-halved"></i> تقرير الرقابة الذكية على حدود المخزون الأربعة</h1>
            <p class="text-slate-500 text-xs mt-1">يحلل مطابقة الكميات الحالية بحدود الخطر والطلب الأقصى لضمان استقرار حركة السوق</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-800">العودة لبوابة المخازن</a>
    </div>

    <!-- الجدول التفصيلي -->
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center">الكمية الحالية</th>
                    <th class="p-4 font-bold text-center text-red-400">الحد الأدنى (الخطر)</th>
                    <th class="p-4 font-bold text-center text-amber-400">حد الطلب الذكي</th>
                    <th class="p-4 font-bold text-center text-emerald-400">الحد الأعلى (السقف)</th>
                    <th class="p-4 font-bold text-center">حالة الصنف الحالية</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono font-bold"><?php echo number_format($row['stock_qty']); ?> طن</td>
                        <td class="p-4 text-center font-mono text-red-400 bg-red-500/5"><?php echo number_format($row['min_limit']); ?></td>
                        <td class="p-4 text-center font-mono text-amber-400 bg-amber-500/5"><?php echo number_format($row['demand_limit']); ?></td>
                        <td class="p-4 text-center font-mono text-emerald-400 bg-emerald-500/5"><?php echo number_format($row['max_limit']); ?></td>
                        <td class="p-4 text-center">
                            <?php if ($row['stock_qty'] <= $row['min_limit']): ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded bg-red-950 text-red-400 border border-red-500/20">🚨 نفاد حرج</span>
                            <?php elseif ($row['stock_qty'] <= $row['demand_limit']): ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded bg-amber-950 text-amber-400 border border-amber-500/20">⚠️ شراء فوري</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded bg-emerald-950 text-emerald-400 border border-emerald-500/20">✓ آمن ومستقر</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- المخطط البياني في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 mb-6">
        <h3 class="text-xs font-bold text-slate-300 mb-4">📈 الكشف البياني العرضي لكميات العنابر الحالية مقارنة بحدود الأمان والطلب</h3>
        <div class="h-80">
            <canvas id="limitsChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('limitsChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.map(r => r.item_name),
                datasets: [
                    { label: 'الكمية الحالية', data: reportData.map(r => r.stock_qty), backgroundColor: '#06b6d4' },
                    { label: 'الحد الأدنى (الخطر)', data: reportData.map(r => r.min_limit), backgroundColor: '#ef4444' },
                    { label: 'حد الطلب', data: reportData.map(r => r.demand_limit), backgroundColor: '#f59e0b' }
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