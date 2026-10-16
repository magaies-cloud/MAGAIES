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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ط§ظ„ط¨ط¶ط§ط¹ط© ط§ظ„ظ…ظ„طھط²ظ… ط¨ظ‡ط§ ظ„ظ„ط¹ظ…ظ„ط§ط، - MAGAIEES ERP</title>
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14">
    <link rel="stylesheet" href="css/theme.css?v=20260723_v14">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/tailwind.min.css">
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

        /* طھظ†ط³ظٹظ‚ط§طھ ط§ظ„ط¬ط¯ط§ظˆظ„ ط§ظ„ظ…طھظˆط§ظپظ‚ط© ظ…ط¹ ط§ظ„ظˆط¶ط¹ظٹظ† ط§ظ„ظپط§طھط­ ظˆط§ظ„ط¯ط§ظƒظ† */
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

        /* ط£ط²ط±ط§ط± ظˆطھظ†ط¨ظٹظ‡ط§طھ ط§ظ„ط¹ظˆط¯ط© */
        .bg-slate-900 {
            background-color: rgba(30, 41, 59, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        :root.light-theme .bg-slate-900 {
            background-color: #e2e8f0 !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            color: #1e293b !important;
        }

        /* طھط­ط³ظٹظ† ظ…ط¸ظ‡ط± ط§ظ„ظ…ط¯ط®ظ„ط§طھ ظˆط§ظ„ظ…ط®ط·ط·ط§طھ ط§ظ„ط¨ظٹط§ظ†ظٹط© */
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
        <?php $page_title = 'ط§ظ„ط¨ط¶ط§ط¹ط© ط§ظ„ظ…ظ„طھط²ظ… ط¨ظ‡ط§ ظ„ظ„ط¹ظ…ظ„ط§ط،'; include 'includes/header.php'; ?>
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-cyan-400"><i class="fa-solid fa-user-lock"></i> طھظ‚ط±ظٹط± ط§ظ„ط¨ط¶ط§ط¹ط© ط§ظ„ظ…ظ„طھط²ظ… ط¨ظ‡ط§ ظ„ظ„ط¹ظ…ظ„ط§ط، (ط§ظ„ظ…ط­ط¬ظˆط²ط©)</h1>
            <p class="text-slate-500 text-xs mt-1">ظٹط¹ط±ط¶ ظƒظ…ظٹط§طھ ط§ظ„ط£طµظ†ط§ظپ ط§ظ„ظ…ط¨ط§ط¹ط© ظˆط§ظ„طھظٹ ظ„ط§ طھط²ط§ظ„ ظ…ط¹ظ„ظ‚ط© ظپظٹ ط§ظ„ط¹ظ†ط§ط¨ط± ط¨ط§ظ†طھط¸ط§ط± طµط±ظپظ‡ط§ ظˆطھظˆطµظٹظ„ظ‡ط§</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-800">ط§ظ„ط¹ظˆط¯ط© ظ„ظ„ط¨ظˆط§ط¨ط©</a>
    </div>

    <!-- ط§ظ„ط¬ط¯ظˆظ„ ط§ظ„ط¹ظ„ظˆظٹ -->
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">ط§ط³ظ… ط§ظ„طµظ†ظپ</th>
                    <th class="p-4 font-bold text-center">ط§ظ„ط±طµظٹط¯ ط§ظ„ظپط¹ظ„ظٹ ط§ظ„ط¥ط¬ظ…ط§ظ„ظٹ</th>
                    <th class="p-4 font-bold text-center text-cyan-400">ط§ظ„ظƒظ…ظٹط© ط§ظ„ظ…ظ„طھط²ظ… ط¨ظ‡ط§ (ط§ظ„ظ…ط­ط¬ظˆط²ط©)</th>
                    <th class="p-4 font-bold text-center text-emerald-400">ط§ظ„ط±طµظٹط¯ ط§ظ„ط­ط± ط§ظ„ظ…طھط§ط­ ظ„ظ„ط¨ظٹط¹</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php if (empty($report_data)): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">
                            <i class="fa-solid fa-face-smile text-2xl mb-2 block"></i>
                            ظ„ط§ طھظˆط¬ط¯ ط¨ط¶ط§ط¦ط¹ ظ…ظ„طھط²ظ… ط¨ظ‡ط§ ط­ط§ظ„ظٹط§ظ‹طŒ ظƒط§ظپط© ط§ظ„ط¨ط¶ط§ط¦ط¹ ط¨ط§ظ„ط¹ظ†ط§ط¨ط± ط­ط±ط© ظˆظ…طھط§ط­ط© ظ„ظ„ط¨ظٹط¹.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($report_data as $row): ?>
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td class="p-4 text-center font-mono"><?php echo number_format($row['stock_qty']); ?> ط·ظ†</td>
                            <td class="p-4 text-center font-mono text-cyan-400 bg-cyan-500/5 font-bold"><?php echo number_format($row['committed_qty']); ?> ط·ظ†</td>
                            <td class="p-4 text-center font-mono text-emerald-400 bg-emerald-500/5 font-bold"><?php echo number_format($row['available_qty']); ?> ط·ظ†</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ط§ظ„ط±ط³ظ… ط§ظ„ط¨ظٹط§ظ†ظٹ ط§ظ„ظ…ط²ط¯ظˆط¬ ظپظٹ ط§ظ„ط£ط³ظپظ„ -->
    <?php if (!empty($report_data)): ?>
        <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
            <h3 class="text-xs font-bold text-slate-300 mb-4">ًں“ˆ ظ…ظ‚ط§ط±ظ†ط© ط¨ظٹظ† ط§ظ„ط±طµظٹط¯ ط§ظ„ظƒظ„ظٹ ظˆط§ظ„ظ…ظ„طھط²ظ… ط¨ظ‡ ظˆط§ظ„ط­ط± ظ„ظƒظ„ طµظ†ظپ</h3>
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
                        { label: 'ط§ظ„ط±طµظٹط¯ ط§ظ„ظƒظ„ظٹ', data: reportData.map(r => r.stock_qty), backgroundColor: '#64748b' },
                        { label: 'ط§ظ„ظ…ط­ط¬ظˆط² ط§ظ„ظ…ظ„طھط²ظ… ط¨ظ‡', data: reportData.map(r => r.committed_qty), backgroundColor: '#06b6d4' },
                        { label: 'ط§ظ„ط±طµظٹط¯ ط§ظ„ط­ط± ط§ظ„ظ…طھط§ط­', data: reportData.map(r => r.available_qty), backgroundColor: '#10b981' }
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

    <script defer src="js/localforage.min.js?v=20260723_v14"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>

