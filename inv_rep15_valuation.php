<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
$grand_total_cost = 0;
$grand_total_market = 0;
foreach ($raw_data as $row) {
    if ($row['stock_qty'] > 0) {
        $cost_val = $row['stock_qty'] * $row['cost_price'];
        $market_val = $row['stock_qty'] * $row['selling_price'];
        $grand_total_cost += $cost_val;
        $grand_total_market += $market_val;
        
        $report_data[] = [
            'item_name' => $row['item_name'],
            'stock_qty' => $row['stock_qty'],
            'cost_price' => $row['cost_price'],
            'market_price' => $row['selling_price'],
            'total_cost_value' => $cost_val,
            'total_market_value' => $market_val
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقييم المخزون المالي - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-purple-400"><i class="fa-solid fa-calculator"></i> تقييم المخزون المالي اللحظي للكاش بسعر التكلفة وسعر السوق</h1>
            <p class="text-slate-500 text-xs mt-1">يقوم بجرد مالي لحظي للبضاعة المخزنة لحساب إجمالي القيمة الشرائية والبيعية المتوقعة لها</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">العودة للبوابة</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center">الكمية</th>
                    <th class="p-4 font-bold text-left">القيمة الكلية بسعر التكلفة</th>
                    <th class="p-4 font-bold text-left text-purple-400">القيمة الكلية بسعر السوق</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono"><?php echo number_format($row['stock_qty']); ?> طن</td>
                        <td class="p-4 text-left font-mono"><?php echo number_format($row['total_cost_value'], 2); ?> SDG</td>
                        <td class="p-4 text-left font-mono text-purple-400 font-bold bg-purple-500/5"><?php echo number_format($row['total_market_value'], 2); ?> SDG</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- كعكة مقارنة القيمة المفرغة في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
        <h3 class="text-xs font-bold text-slate-300 mb-4">🍩 كعكة تقييم المخزون المالي الكلي لجميع العنابر</h3>
        <div class="h-80 flex justify-center">
            <div class="w-80 h-80">
                <canvas id="valuationPieChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('valuationPieChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['القيمة الإجمالية بالتكلفة (SDG)', 'القيمة الإجمالية بالسوق (SDG)'],
                datasets: [{
                    data: [<?php echo $grand_total_cost; ?>, <?php echo $grand_total_market; ?>],
                    backgroundColor: ['#64748b', '#a855f7']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8' } } }
            }
        });
    </script>
</body>
</html>