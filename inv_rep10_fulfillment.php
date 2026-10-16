<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'db_bridge.php';
$pdo = getDBConnection();

$report_data = [];
try {
    $stmt = $pdo->query("SELECT * FROM sales_invoices WHERE is_deleted = 0 AND tenant_id = '{\}' ORDER BY id DESC LIMIT 10");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sales as $s) {
        $data = json_decode($s['data'], true);
        if ($data) {
            $inv_no = $data['invoiceNo'] ?? 'INV';
            $cust = $data['customerName'] ?? 'ط¹ظ…ظٹظ„';
            $status = $s['status_val'] ?? ($data['status'] ?? 'pending');
            $rate = ($status === 'delivered') ? 100 : 0;
            
            $report_data[] = [
                'invoice_no' => $inv_no,
                'customer_name' => $cust,
                'status' => $status,
                'fulfillment_pct' => $rate
            ];
        }
    }
} catch (Exception $e) {}

// Mock data fallback removed for SaaS environment

// ط­ط³ط§ط¨ ظ…ط¹ط¯ظ„ط§طھ ط§ظ„طھظ„ط¨ظٹط© ط§ظ„ط¥ط¬ظ…ط§ظ„ظٹط©
$delivered_count = 0;
$pending_count = 0;
foreach ($report_data as $row) {
    if ($row['status'] === 'delivered') $delivered_count++;
    else $pending_count++;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ط¯ظ‚ط© ط·ظ„ط¨ط§طھ ط§ظ„طµط±ظپ - MAGAIEES ERP</title>
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
        <?php $page_title = 'ط¯ظ‚ط© ط·ظ„ط¨ط§طھ ط§ظ„طµط±ظپ'; include 'includes/header.php'; ?>
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-emerald-400"><i class="fa-solid fa-circle-check"></i> ط¯ظ‚ط© ط·ظ„ط¨ط§طھ ط§ظ„طµط±ظپ ظˆطھظ„ط¨ظٹط© ط£ط°ظˆظ†ط§طھ ط§ظ„ط¨ط¶ط§ط¦ط¹ (Order Fulfillment Rate)</h1>
            <p class="text-slate-500 text-xs mt-1">ظٹظ‚ظٹط³ ظ†ط³ط¨ط© ط§ظ„ط·ظ„ط¨ط§طھ ط§ظ„طھظٹ طھظ… ط´ط­ظ†ظ‡ط§ ظˆطھظˆطµظٹظ„ظ‡ط§ ظ„ظ„ط²ط¨ط§ط¦ظ† ط¨ظ†ط¬ط§ط­ ط¯ظˆظ† ظ†ظ‚طµ ط£ظˆ ط¹ط¬ط² ظ…ط®ط²ظ†ظٹ</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">ط§ظ„ط¹ظˆط¯ط© ظ„ظ„ط¨ظˆط§ط¨ط©</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">ط±ظ‚ظ… ط§ظ„ظپط§طھظˆط±ط©</th>
                    <th class="p-4 font-bold text-center">ط§ظ„ط¹ظ…ظٹظ„</th>
                    <th class="p-4 font-bold text-center">ط§ظ„ط­ط§ظ„ط©</th>
                    <th class="p-4 font-bold text-center text-emerald-400">ظ†ط³ط¨ط© طھظ„ط¨ظٹط© ط§ظ„ط·ظ„ط¨</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                        <td class="p-4 text-center"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td class="p-4 text-center font-mono">
                            <?php if ($row['status'] === 'delivered'): ?>
                                <span class="text-emerald-400">âœ“ طھظ… ط§ظ„طھط³ظ„ظٹظ…</span>
                            <?php else: ?>
                                <span class="text-amber-400">âڈ³ ظ‚ظٹط¯ ط§ظ„ط§ظ†طھط¸ط§ط±</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center font-mono font-bold <?php echo $row['fulfillment_pct'] == 100 ? 'text-emerald-400 bg-emerald-500/5' : 'text-amber-400 bg-amber-500/5'; ?>"><?php echo $row['fulfillment_pct']; ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ظƒط¹ظƒط© طھظ„ط¨ظٹط© ط§ظ„ط·ظ„ط¨ط§طھ ظپظٹ ط§ظ„ط£ط³ظپظ„ -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
        <h3 class="text-xs font-bold text-slate-300 mb-4">ًںچ© ظƒط¹ظƒط© طھظ„ط¨ظٹط© ظˆطµط±ظپ ط§ظ„ط¨ط¶ط§ط¦ط¹ ط§ظ„ظپظˆط±ظٹط©</h3>
        <div class="h-80 flex justify-center">
            <div class="w-80 h-80">
                <canvas id="fulfillmentChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('fulfillmentChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['طھظ… ط§ظ„طھط³ظ„ظٹظ… ط¨ط§ظ„ظƒط§ظ…ظ„', 'ظ…ط¹ظ„ظ‚ ظ‚ظٹط¯ ط§ظ„ط´ط­ظ†'],
                datasets: [{
                    data: [<?php echo $delivered_count; ?>, <?php echo $pending_count; ?>],
                    backgroundColor: ['#10b981', '#f59e0b']
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


