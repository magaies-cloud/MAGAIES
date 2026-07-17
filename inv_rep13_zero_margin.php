<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
foreach ($raw_data as $row) {
    if ($row['selling_price'] > 0 && $row['selling_price'] <= $row['cost_price']) {
        $report_data[] = [
            'item_name' => $row['item_name'],
            'cost_price' => $row['cost_price'],
            'selling_price' => $row['selling_price'],
            'loss' => $row['cost_price'] - $row['selling_price']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الأصناف الخاسرة - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-red-400"><i class="fa-solid fa-ban"></i> تقرير الأصناف الخاسرة والبيع دون سعر تكلفة الشراء المرجح</h1>
            <p class="text-slate-500 text-xs mt-1">يكشف المنتجات التي تباع بأسعار تساوى أو تقل عن تكلفة شرائها الفعلي لحظر الخسائر</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">العودة للبوابة</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف الخاسر</th>
                    <th class="p-4 font-bold text-center">تكلفة الشراء</th>
                    <th class="p-4 font-bold text-center">سعر البيع الحالي</th>
                    <th class="p-4 font-bold text-center text-red-400">الخسارة في الوحدة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php if (empty($report_data)): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">✓ ممتاز! لا توجد أصناف خاسرة في الداتابيز الحالية.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($report_data as $row): ?>
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td class="p-4 text-center font-mono"><?php echo number_format($row['cost_price'], 2); ?> SDG</td>
                            <td class="p-4 text-center font-mono"><?php echo number_format($row['selling_price'], 2); ?> SDG</td>
                            <td class="p-4 text-center font-mono font-bold text-red-400 bg-red-500/5"><?php echo number_format($row['loss'], 2); ?> SDG</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- مخطط بياني سفلي للأصناف الخاسرة وقيمة الخسارة -->
    <?php if (!empty($report_data)): ?>
        <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
            <h3 class="text-xs font-bold text-slate-300 mb-4">📉 مخطط الخسائر المالية المتراكمة لكل صنف</h3>
            <div class="h-80">
                <canvas id="lossChart"></canvas>
            </div>
        </div>

        <script>
            const ctx = document.getElementById('lossChart').getContext('2d');
            const reportData = <?php echo json_encode($report_data); ?>;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: reportData.map(r => r.item_name),
                    datasets: [{
                        label: 'قيمة الخسارة في الوحدة (SDG)',
                        data: reportData.map(r => r.loss),
                        backgroundColor: '#ef4444'
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
    <?php endif; ?>
</body>
</html>