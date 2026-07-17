<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
foreach ($raw_data as $row) {
    if ($row['stock_qty'] > 0) {
        $last_date = $row['last_movement_date'];
        if ($last_date && $last_date !== 'لا يوجد حركة') {
            $days_dormant = (int)round((time() - strtotime($last_date)) / (60 * 60 * 24));
            if ($days_dormant < 0) $days_dormant = 0;
        } else {
            $days_dormant = 365; // لم يبع أبداً
            $last_date = null;
        }
        $report_data[] = [
            'item_name' => $row['item_name'],
            'stock_qty' => $row['stock_qty'],
            'last_sale_date' => $last_date,
            'days_dormant' => $days_dormant
        ];
    }
}
usort($report_data, function($a, $b) { return $b['days_dormant'] <=> $a['days_dormant']; });
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>البضاعة الراكدة - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-amber-400"><i class="fa-solid fa-snowflake"></i> تقرير مصفوفة البضاعة الراكدة وبطيئة الحركة</h1>
            <p class="text-slate-500 text-xs mt-1">يرصد البضاعة المتراكمة في المخازن وحساب كلفة جمود رأس المال</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-800">العودة للبوابة</a>
    </div>

    <!-- الجدول العلوي -->
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف الراكد</th>
                    <th class="p-4 font-bold text-center">الكمية المجمّدة في العنبر</th>
                    <th class="p-4 font-bold text-center">تاريخ آخر عملية بيع</th>
                    <th class="p-4 font-bold text-center text-amber-400">أيام الركود المستمر</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono font-bold text-amber-400"><?php echo number_format($row['stock_qty']); ?> طن</td>
                        <td class="p-4 text-center font-mono text-slate-400"><?php echo $row['last_sale_date'] ? $row['last_sale_date'] : 'لا يوجد حركات'; ?></td>
                        <td class="p-4 text-center font-mono font-bold bg-amber-500/5 text-amber-300"><?php echo $row['days_dormant']; ?> يوم</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- كعكة دائرية مفرغة في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
        <h3 class="text-xs font-bold text-slate-300 mb-4">🍩 كعكة الركود: توزيع أيام جمود رأس المال للأصناف</h3>
        <div class="h-80 flex justify-center">
            <div class="w-80 h-80">
                <canvas id="dormantDoughnutChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('dormantDoughnutChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: reportData.map(r => r.item_name),
                datasets: [{
                    data: reportData.map(r => r.days_dormant),
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#14b8a6', '#06b6d4', '#e2e8f0']
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