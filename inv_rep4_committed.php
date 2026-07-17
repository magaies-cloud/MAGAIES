<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
foreach ($raw_data as $row) {
    if ($row['committed_qty'] > 0) {
        $report_data[] = [
            'item_name' => $row['item_name'],
            'stock_qty' => $row['stock_qty'],
            'committed_qty' => $row['committed_qty'],
            'available_qty' => $row['stock_qty'] - $row['committed_qty']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>البضاعة الملتزم بها للعملاء - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-cyan-400"><i class="fa-solid fa-user-lock"></i> تقرير البضاعة الملتزم بها للعملاء (المحجوزة)</h1>
            <p class="text-slate-500 text-xs mt-1">يعرض كميات الأصناف المباعة والتي لا تزال معلقة في العنابر بانتظار صرفها وتوصيلها</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-800">العودة للبوابة</a>
    </div>

    <!-- الجدول العلوي -->
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-center">الرصيد الفعلي الإجمالي</th>
                    <th class="p-4 font-bold text-center text-cyan-400">الكمية الملتزم بها (المحجوزة)</th>
                    <th class="p-4 font-bold text-center text-emerald-400">الرصيد الحر المتاح للبيع</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php if (empty($report_data)): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">
                            <i class="fa-solid fa-face-smile text-2xl mb-2 block"></i>
                            لا توجد بضائع ملتزم بها حالياً، كافة البضائع بالعنابر حرة ومتاحة للبيع.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($report_data as $row): ?>
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td class="p-4 text-center font-mono"><?php echo number_format($row['stock_qty']); ?> طن</td>
                            <td class="p-4 text-center font-mono text-cyan-400 bg-cyan-500/5 font-bold"><?php echo number_format($row['committed_qty']); ?> طن</td>
                            <td class="p-4 text-center font-mono text-emerald-400 bg-emerald-500/5 font-bold"><?php echo number_format($row['available_qty']); ?> طن</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- الرسم البياني المزدوج في الأسفل -->
    <?php if (!empty($report_data)): ?>
        <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
            <h3 class="text-xs font-bold text-slate-300 mb-4">📈 مقارنة بين الرصيد الكلي والملتزم به والحر لكل صنف</h3>
            <div class="h-80">
                <canvas id="committedChart"></canvas>
            </div>
        </div>

        <script>
            const ctx = document.getElementById('committedChart').getContext('2d');
            const reportData = <?php echo json_encode($report_data); ?>;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: reportData.map(r => r.item_name),
                    datasets: [
                        { label: 'الرصيد الكلي', data: reportData.map(r => r.stock_qty), backgroundColor: '#64748b' },
                        { label: 'المحجوز الملتزم به', data: reportData.map(r => r.committed_qty), backgroundColor: '#06b6d4' },
                        { label: 'الرصيد الحر المتاح', data: reportData.map(r => r.available_qty), backgroundColor: '#10b981' }
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
    <?php endif; ?>
</body>
</html>