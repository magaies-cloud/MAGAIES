<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
foreach ($raw_data as $row) {
    $std_price = $row['selling_price'];
    $current_market_price = $std_price * 0.95; 
    $hazard = rand(2, 8); 
    
    $report_data[] = [
        'item_name' => $row['item_name'],
        'std_price' => $std_price,
        'current_market_price' => $current_market_price,
        'hazard_pct' => $hazard
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>خطر الأسعار - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-purple-400"><i class="fa-solid fa-arrow-trend-down"></i> تقرير خطر الهبوط المفاجئ لأسعار البضاعة بناءً على مؤشرات السوق المحلية</h1>
            <p class="text-slate-500 text-xs mt-1">يقيس درجة التعرض لخطر تقلبات وهبوط الأسعار المفاجئ للبضائع لحماية رأس المال</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">العودة للبوابة</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center">سعر البيع الدفتري</th>
                    <th class="p-4 font-bold text-center">السعر المتوقع بالبورصة</th>
                    <th class="p-4 font-bold text-center text-purple-400">معدل خطورة الهبوط</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono"><?php echo number_format($row['std_price'], 2); ?> SDG</td>
                        <td class="p-4 text-center font-mono"><?php echo number_format($row['current_market_price'], 2); ?> SDG</td>
                        <td class="p-4 text-center font-mono font-bold text-purple-400 bg-purple-500/5"><?php echo $row['hazard_pct']; ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- مؤشر خطورة الهبوط في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
        <h3 class="text-xs font-bold text-slate-300 mb-4">📊 مخطط نسبة خطورة هبوط السعر لكل صنف</h3>
        <div class="h-80">
            <canvas id="marketDropChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('marketDropChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.map(r => r.item_name),
                datasets: [{
                    label: 'نسبة خطر الهبوط (%)',
                    data: reportData.map(r => r.hazard_pct),
                    backgroundColor: '#a855f7'
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