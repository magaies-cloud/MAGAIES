<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طھظ‚ظٹظٹظ… ط§ظ„ظ…ط®ط²ظˆظ† ط§ظ„ظ…ط§ظ„ظٹ - MAGAIEES ERP</title>
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
        <?php $page_title = 'طھظ‚ظٹظٹظ… ط§ظ„ظ…ط®ط²ظˆظ† ط§ظ„ظ…ط§ظ„ظٹ'; include 'includes/header.php'; ?>
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-purple-400"><i class="fa-solid fa-calculator"></i> طھظ‚ظٹظٹظ… ط§ظ„ظ…ط®ط²ظˆظ† ط§ظ„ظ…ط§ظ„ظٹ ط§ظ„ظ„ط­ط¸ظٹ ظ„ظ„ظƒط§ط´ ط¨ط³ط¹ط± ط§ظ„طھظƒظ„ظپط© ظˆط³ط¹ط± ط§ظ„ط³ظˆظ‚</h1>
            <p class="text-slate-500 text-xs mt-1">ظٹظ‚ظˆظ… ط¨ط¬ط±ط¯ ظ…ط§ظ„ظٹ ظ„ط­ط¸ظٹ ظ„ظ„ط¨ط¶ط§ط¹ط© ط§ظ„ظ…ط®ط²ظ†ط© ظ„ط­ط³ط§ط¨ ط¥ط¬ظ…ط§ظ„ظٹ ط§ظ„ظ‚ظٹظ…ط© ط§ظ„ط´ط±ط§ط¦ظٹط© ظˆط§ظ„ط¨ظٹط¹ظٹط© ط§ظ„ظ…طھظˆظ‚ط¹ط© ظ„ظ‡ط§</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">ط§ظ„ط¹ظˆط¯ط© ظ„ظ„ط¨ظˆط§ط¨ط©</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">ط§ط³ظ… ط§ظ„طµظ†ظپ</th>
                    <th class="p-4 font-bold text-center">ط§ظ„ظƒظ…ظٹط©</th>
                    <th class="p-4 font-bold text-left">ط§ظ„ظ‚ظٹظ…ط© ط§ظ„ظƒظ„ظٹط© ط¨ط³ط¹ط± ط§ظ„طھظƒظ„ظپط©</th>
                    <th class="p-4 font-bold text-left text-purple-400">ط§ظ„ظ‚ظٹظ…ط© ط§ظ„ظƒظ„ظٹط© ط¨ط³ط¹ط± ط§ظ„ط³ظˆظ‚</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td class="p-4 text-center font-mono"><?php echo number_format($row['stock_qty']); ?> ط·ظ†</td>
                        <td class="p-4 text-left font-mono"><?php echo number_format($row['total_cost_value'], 2); ?> SDG</td>
                        <td class="p-4 text-left font-mono text-purple-400 font-bold bg-purple-500/5"><?php echo number_format($row['total_market_value'], 2); ?> SDG</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ظƒط¹ظƒط© ظ…ظ‚ط§ط±ظ†ط© ط§ظ„ظ‚ظٹظ…ط© ط§ظ„ظ…ظپط±ط؛ط© ظپظٹ ط§ظ„ط£ط³ظپظ„ -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
        <h3 class="text-xs font-bold text-slate-300 mb-4">ًںچ© ظƒط¹ظƒط© طھظ‚ظٹظٹظ… ط§ظ„ظ…ط®ط²ظˆظ† ط§ظ„ظ…ط§ظ„ظٹ ط§ظ„ظƒظ„ظٹ ظ„ط¬ظ…ظٹط¹ ط§ظ„ط¹ظ†ط§ط¨ط±</h3>
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
                labels: ['ط§ظ„ظ‚ظٹظ…ط© ط§ظ„ط¥ط¬ظ…ط§ظ„ظٹط© ط¨ط§ظ„طھظƒظ„ظپط© (SDG)', 'ط§ظ„ظ‚ظٹظ…ط© ط§ظ„ط¥ط¬ظ…ط§ظ„ظٹط© ط¨ط§ظ„ط³ظˆظ‚ (SDG)'],
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
    </div> <!-- Close .dashboard-container -->

    <script defer src="js/localforage.min.js?v=20260723_v14"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>

