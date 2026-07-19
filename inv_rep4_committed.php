<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

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
    <link rel="stylesheet" href="css/design-system.css?v=20260719_v1">
    <link rel="stylesheet" href="css/theme.css?v=20260719_v1">
    <link rel="stylesheet" href="css/components.css?v=20260719_v1">
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
        <?php $page_title = 'البضاعة الملتزم بها للعملاء'; include 'includes/header.php'; ?>
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
    </div> <!-- Close .dashboard-container -->

    <script defer src="js/localforage.min.js?v=20260719_v1"></script>
    <script defer src="js/app.js?v=20260719_v1"></script>
</body>
</html>