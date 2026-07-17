<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
foreach ($raw_data as $row) {
    $std_cost = $row['selling_price'] * 0.75;
    $var = $row['cost_price'] - $std_cost;
    
    $report_data[] = [
        'item_name' => $row['item_name'],
        'std_cost' => $std_cost,
        'actual_cost' => $row['cost_price'],
        'variance' => $var
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>انحراف التكلفة المرجحة - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-amber-400"><i class="fa-solid fa-compass"></i> تقرير انحراف التكلفة المرجحة الفعلي للأصناف (Cost Variance)</h1>
            <p class="text-slate-500 text-xs mt-1">يقارن بين سعر الشراء الفعلي المرجح من فواتير المشتريات والسعر المعياري المستهدف</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">العودة للبوابة</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center">التكلفة المعيارية</th>
                    <th class="p-4 font-bold text-center">التكلفة الفعلية الشراء</th>
                    <th class="p-4 font-bold text-center">الانحراف المالي</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono"><?php echo number_format($row['std_cost'], 2); ?> SDG</td>
                        <td class="p-4 text-center font-mono"><?php echo number_format($row['actual_cost'], 2); ?> SDG</td>
                        <td class="p-4 text-center font-mono font-bold <?php echo $row['variance'] <= 0 ? 'text-emerald-400' : 'text-red-400'; ?>"><?php echo number_format($row['variance'], 2); ?> SDG</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- المخطط البياني في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 mb-6">
        <h3 class="text-xs font-bold text-slate-300 mb-4">📈 مقارنة بيانية بين التكلفة المعيارية والتكلفة الفعلية</h3>
        <div class="h-80">
            <canvas id="costVarianceChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('costVarianceChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.map(r => r.item_name),
                datasets: [
                    { label: 'التكلفة المعيارية', data: reportData.map(r => r.std_cost), backgroundColor: '#94a3b8' },
                    { label: 'التكلفة الفعلية', data: reportData.map(r => r.actual_cost), backgroundColor: '#f59e0b' }
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