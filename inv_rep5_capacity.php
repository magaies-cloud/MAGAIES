<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'db_bridge.php';
$pdo = getDBConnection();
$report_data = getWarehouseCapacities($pdo);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الطاقة الاستيعابية للعنابر - MAGAIEES ERP</title>
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14">
    <link rel="stylesheet" href="css/theme.css?v=20260723_v14">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- tessans-table-compat -->
    <style>
        body {
            font-family: 'Inter', 'Tajawal', sans-serif;
            background-color: var(--bg-dark, #050308) !important;
            color: var(--text-primary, #ffffff) !important;
            background-image: radial-gradient(circle at top right, rgba(236,72,153,0.03), transparent 45%),
                              radial-gradient(circle at bottom left, rgba(16,185,129,0.03), transparent 45%) !important;
            margin: 0; padding: 0;
            min-height: 100vh;
        }

        :root.light-theme body {
            background-image: none !important;
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }

        .dashboard-container {
            width: 100%;
            max-width: 98%;
            margin: 0 auto;
            padding: 0px 25px 25px 25px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 25px;
            flex: 1;
        }

        /* تنسيقات الجداول المتوافقة مع الوضعين الفاتح والداكن */
        .bg-slate-900\/40 {
            background-color: rgba(30, 41, 59, 0.2) !important;
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
        }

        :root.light-theme .bg-slate-900\/40 {
            background-color: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05) !important;
        }

        .bg-slate-950 {
            background-color: rgba(15, 23, 42, 0.5) !important;
        }

        :root.light-theme .bg-slate-950 {
            background-color: #f8fafc !important;
            color: #475569 !important;
        }

        .text-slate-200 {
            color: var(--text-primary, #e2e8f0) !important;
        }

        :root.light-theme .text-slate-200 {
            color: #1e293b !important;
        }

        .text-slate-100 {
            color: var(--text-primary, #f1f5f9) !important;
        }

        :root.light-theme .text-slate-100 {
            color: #1e293b !important;
        }

        .border-slate-800 {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        :root.light-theme .border-slate-800 {
            border-color: rgba(0, 0, 0, 0.08) !important;
        }

        .divide-slate-800\/50 > * + * {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        :root.light-theme .divide-slate-800\/50 > * + * {
            border-color: rgba(0, 0, 0, 0.05) !important;
        }

        .text-slate-400 {
            color: #94a3b8 !important;
        }

        :root.light-theme .text-slate-400 {
            color: #475569 !important;
        }

        .text-slate-500 {
            color: #64748b !important;
        }

        :root.light-theme .text-slate-500 {
            color: #475569 !important;
        }

        /* أزرار وتنبيهات العودة */
        .bg-slate-900 {
            background-color: rgba(30, 41, 59, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        :root.light-theme .bg-slate-900 {
            background-color: #e2e8f0 !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            color: #1e293b !important;
        }

        /* تحسين مظهر المدخلات والمخططات البيانية */
        input[type="number"], select {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: var(--text-primary, #ffffff) !important;
        }

        :root.light-theme input[type="number"], :root.light-theme select {
            background-color: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.12) !important;
            color: #1e293b !important;
        }
    </style>
</head>

<body class="text-slate-100 min-h-screen">
    <div class="dashboard-container">
        <?php $page_title = 'الطاقة الاستيعابية للعنابر'; include 'includes/header.php'; ?>
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-cyan-400"><i class="fa-solid fa-circle-dot"></i> تقرير الطاقة الاستيعابية الفعلي لعنابر التخزين</h1>
            <p class="text-slate-500 text-xs mt-1">يراقب نسبة استغلال المساحات التخزينية في المستودعات المختلفة لمنع تكدس البضائع</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-800">العودة للبوابة</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <?php foreach ($report_data as $row): 
            $bar_color = 'from-cyan-500 to-emerald-500';
            $text_color = 'text-emerald-400';
            if ($row['utilization_pct'] >= 85) {
                $bar_color = 'from-red-500 to-rose-500';
                $text_color = 'text-red-400';
            } elseif ($row['utilization_pct'] >= 60) {
                $bar_color = 'from-amber-500 to-yellow-500';
                $text_color = 'text-amber-400';
            }
        ?>
            <div class="bg-slate-900/40 p-5 rounded-2xl border border-slate-800">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-bold text-slate-200"><?php echo htmlspecialchars($row['warehouse_name']); ?></span>
                    <span class="text-xs text-slate-400 font-mono"><?php echo number_format($row['stock_qty']); ?> / <?php echo number_format($row['capacity']); ?> طن</span>
                </div>
                <div class="w-full bg-slate-950 rounded-full h-3 overflow-hidden border border-slate-800">
                    <div class="bg-gradient-to-r <?php echo $bar_color; ?> h-full" style="width: <?php echo min($row['utilization_pct'], 100); ?>%"></div>
                </div>
                <div class="flex justify-between items-center mt-2 text-xs">
                    <span class="text-slate-500">نسبة الاستغلال</span>
                    <span class="font-bold <?php echo $text_color; ?>"><?php echo $row['utilization_pct']; ?>%</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- الجدول والتخطيط البياني السفلي -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- الجدول -->
        <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md">
            <table class="w-full text-right border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                        <th class="p-4 font-bold">اسم المستودع / العنبر</th>
                        <th class="p-4 font-bold text-center">الكمية المخزنة</th>
                        <th class="p-4 font-bold text-center text-emerald-400">حالة الامتلاء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    <?php foreach ($report_data as $row): ?>
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['warehouse_name']); ?></td>
                            <td class="p-4 text-center font-mono"><?php echo number_format($row['stock_qty']); ?> طن</td>
                            <td class="p-4 text-center">
                                <?php if ($row['utilization_pct'] >= 85): ?>
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded bg-red-950 text-red-400 border border-red-500/20">🚨 ممتلئ حرج</span>
                                <?php elseif ($row['utilization_pct'] >= 60): ?>
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded bg-amber-950 text-amber-400 border border-amber-500/20">⚠️ امتلاء متوسط</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded bg-emerald-950 text-emerald-400 border border-emerald-500/20">✓ مساحة كافية</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- الرسم البياني الأفقي -->
        <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 h-80 flex flex-col justify-between">
            <h3 class="text-xs font-bold text-slate-300 mb-2">📊 نسبة إشغال المستودعات مقارنة بالسعة الإجمالية</h3>
            <div class="h-64">
                <canvas id="capacityChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('capacityChart').getContext('2d');
        const reportData = <?php echo json_encode($report_data); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.map(r => r.warehouse_name),
                datasets: [
                    { label: 'الكمية الحالية', data: reportData.map(r => r.stock_qty), backgroundColor: '#06b6d4' },
                    { label: 'السعة الكلية', data: reportData.map(r => r.capacity), backgroundColor: '#1e293b' }
                ]
            },
            options: {
                indexAxis: 'y',
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
    </div> <!-- Close .dashboard-container -->

    <script defer src="js/localforage.min.js?v=20260723_v14"></script>
    <script defer src="js/app.js?v=20260803_v14"></script>
</body>
</html>
