<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$raw_data = getInventoryReportData($pdo);
$report_data = [];
$total_value = 0;
foreach ($raw_data as $row) {
    $val = $row['stock_qty'] * $row['cost_price'];
    $total_value += $val;
    $report_data[] = [
        'item_name' => $row['item_name'],
        'value' => $val
    ];
}
usort($report_data, function($a, $b) { return $b['value'] <=> $a['value']; });

$cumulative = 0;
$class_counts = ['A' => 0, 'B' => 0, 'C' => 0];
foreach ($report_data as $idx => $row) {
    $cumulative += $row['value'];
    $pct = $total_value > 0 ? ($cumulative / $total_value) * 100 : 0;
    
    if ($pct <= 80) {
        $class = 'A (رئيسي استراتيجي)';
        $color = 'text-red-400 bg-red-500/5';
        $class_counts['A'] += $row['value'];
    } elseif ($pct <= 95) {
        $class = 'B (متوسط الأهمية)';
        $color = 'text-amber-400 bg-amber-500/5';
        $class_counts['B'] += $row['value'];
    } else {
        $class = 'C (ثانوي)';
        $color = 'text-emerald-400 bg-emerald-500/5';
        $class_counts['C'] += $row['value'];
    }
    
    $report_data[$idx]['percentage'] = $total_value > 0 ? round(($row['value'] / $total_value) * 100, 1) : 0;
    $report_data[$idx]['class'] = $class;
    $report_data[$idx]['color'] = $color;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تحليل تركيز البضائع ABC - MAGAIEES ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-6" style="background-color: #060911;">
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-purple-400"><i class="fa-solid fa-cubes-stacked"></i> تحليل تركيز القيمة المالية للبضائع (ABC Analysis)</h1>
            <p class="text-slate-500 text-xs mt-1">يصنف المخزون حسب الأهمية الرأسمالية: فئة A (80% من القيمة)، فئة B (15% من القيمة)، فئة C (5% من القيمة)</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">العودة للبوابة</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">اسم الصنف</th>
                    <th class="p-4 font-bold text-left">القيمة الاستثمارية للمخزون</th>
                    <th class="p-4 font-bold text-center">النسبة من الإجمالي (%)</th>
                    <th class="p-4 font-bold text-center text-purple-400">فئة تركيز الأهمية ABC</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-left font-mono"><?php echo number_format($row['value'], 2); ?> SDG</td>
                        <td class="p-4 text-center font-mono"><?php echo $row['percentage']; ?>%</td>
                        <td class="p-4 text-center font-bold <?php echo $row['color']; ?>"><?php echo $row['class']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- كعكة دائرية مفرغة لتوزيع القيمة الرأسمالية حسب فئات ABC في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
        <h3 class="text-xs font-bold text-slate-300 mb-4">🍩 كعكة توزيع القيمة الاستثمارية حسب فئات الأهمية ABC</h3>
        <div class="h-80 flex justify-center">
            <div class="w-80 h-80">
                <canvas id="abcChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('abcChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['الفئة A (استراتيجي)', 'الفئة B (متوسط)', 'الفئة C (ثانوي)'],
                datasets: [{
                    data: [<?php echo $class_counts['A']; ?>, <?php echo $class_counts['B']; ?>, <?php echo $class_counts['C']; ?>],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981']
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