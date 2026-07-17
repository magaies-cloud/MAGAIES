<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
foreach ($raw_data as $row) {
    $cogs = $row['cost_price'] * $row['sold_qty'];
    $avg_inventory = $row['cost_price'] * max($row['stock_qty'], 1);
    $turnover = $avg_inventory > 0 ? round($cogs / $avg_inventory, 2) : 0;
    
    $report_data[] = [
        'item_name' => $row['item_name'],
        'stock_qty' => $row['stock_qty'],
        'cogs' => $cogs,
        'turnover' => $turnover
    ];
}
usort($report_data, function($a, $b) { return $b['turnover'] <=> $a['turnover']; });
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>معدل دوران المخزون - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-emerald-400"><i class="fa-solid fa-sync"></i> تقرير معدل دوران المخزون (Inventory Turnover)</h1>
            <p class="text-slate-500 text-xs mt-1">يقيس عدد مرات بيع واستبدال المخزون خلال الفترة لتقييم كفاءة إدارة البضائع</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-800">العودة للبوابة</a>
    </div>
    
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center">الكمية الحالية</th>
                    <th class="p-4 font-bold text-left">تكلفة المبيعات (COGS)</th>
                    <th class="p-4 font-bold text-center text-emerald-400">معدل الدوران</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono"><?php echo number_format($row['stock_qty']); ?> طن</td>
                        <td class="p-4 text-left font-mono"><?php echo number_format($row['cogs'], 2); ?> SDG</td>
                        <td class="p-4 text-center font-mono font-bold text-emerald-400 bg-emerald-500/5"><?php echo $row['turnover']; ?>x</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- الرسم البياني السفلي -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 mb-6">
        <h3 class="text-xs font-bold text-slate-300 mb-4">📈 مخطط معدل دوران المخزون التفصيلي لكل صنف</h3>
        <div class="h-80">
            <canvas id="turnoverChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('turnoverChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.map(r => r.item_name),
                datasets: [{
                    label: 'معدل دوران المخزون (مرة)',
                    data: reportData.map(r => r.turnover),
                    backgroundColor: '#10b981'
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