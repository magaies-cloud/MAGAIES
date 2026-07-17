<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
foreach ($raw_data as $row) {
    $profit_margin = $row['selling_price'] > 0 ? round((($row['selling_price'] - $row['cost_price']) / $row['selling_price']) * 100, 1) : 0;
    
    $report_data[] = [
        'item_name' => $row['item_name'],
        'profit_margin' => $profit_margin,
        'sold_qty' => $row['sold_qty']
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الربحية مقابل الحركة - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-amber-400"><i class="fa-solid fa-chart-line"></i> تقرير ربحية الصنف مقابل سرعة حركته (Margin vs Velocity)</h1>
            <p class="text-slate-500 text-xs mt-1">يقارن بين الهامش الربحي المكتسب من كل صنف وإجمالي الكميات المباعة لتحديد السحب الحقيقي</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">العودة للبوابة</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center text-amber-400">هامش الربح (%)</th>
                    <th class="p-4 font-bold text-center text-cyan-400">الكمية المباعة (سرعة السحب)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono text-amber-400 bg-amber-500/5 font-bold"><?php echo $row['profit_margin']; ?>%</td>
                        <td class="p-4 text-center font-mono text-cyan-400 bg-cyan-500/5 font-bold"><?php echo number_format($row['sold_qty']); ?> طن</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- مخطط بياني ثنائي المحور في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 mb-6">
        <h3 class="text-xs font-bold text-slate-300 mb-4">📈 مقارنة هامش الربح مع سرعة السحب الفعلي لكل صنف</h3>
        <div class="h-80">
            <canvas id="velocityMarginChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('velocityMarginChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.map(r => r.item_name),
                datasets: [
                    { label: 'هامش الربح (%)', data: reportData.map(r => r.profit_margin), backgroundColor: '#fbbf24', yAxisID: 'y' },
                    { label: 'سرعة السحب (طن)', data: reportData.map(r => r.sold_qty), backgroundColor: '#06b6d4', yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                    y: { type: 'linear', display: true, position: 'left', ticks: { color: '#fbbf24' }, grid: { color: '#1e293b' } },
                    y1: { type: 'linear', display: true, position: 'right', ticks: { color: '#06b6d4' }, grid: { drawOnChartArea: false } }
                }
            }
        });
    </script>
</body>
</html>