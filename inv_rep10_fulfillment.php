<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'db_bridge.php';
$pdo = getDBConnection();

$report_data = [];
try {
    $stmt = $pdo->query("SELECT * FROM sales_invoices WHERE is_deleted = 0 ORDER BY id DESC LIMIT 10");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sales as $s) {
        $data = json_decode($s['data'], true);
        if ($data) {
            $inv_no = $data['invoiceNo'] ?? 'INV';
            $cust = $data['customerName'] ?? 'عميل';
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

// حساب معدلات التلبية الإجمالية
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
    <title>دقة طلبات الصرف - MAGAIEES ERP</title>
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
        <?php $page_title = 'دقة طلبات الصرف'; include 'includes/header.php'; ?>
    <div class="mb-6 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-emerald-400"><i class="fa-solid fa-circle-check"></i> دقة طلبات الصرف وتلبية أذونات البضائع (Order Fulfillment Rate)</h1>
            <p class="text-slate-500 text-xs mt-1">يقيس نسبة الطلبات التي تم شحنها وتوصيلها للزبائن بنجاح دون نقص أو عجز مخزني</p>
        </div>
        <a href="smart_inventory_report.php" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold">العودة للبوابة</a>
    </div>
    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 overflow-hidden shadow-md mb-8">
        <table class="w-full text-right border-collapse text-sm">
            <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs border-b border-slate-800">
                    <th class="p-4 font-bold">رقم الفاتورة</th>
                    <th class="p-4 font-bold text-center">العميل</th>
                    <th class="p-4 font-bold text-center">الحالة</th>
                    <th class="p-4 font-bold text-center text-emerald-400">نسبة تلبية الطلب</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                        <td class="p-4 text-center"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td class="p-4 text-center font-mono">
                            <?php if ($row['status'] === 'delivered'): ?>
                                <span class="text-emerald-400">✓ تم التسليم</span>
                            <?php else: ?>
                                <span class="text-amber-400">⏳ قيد الانتظار</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center font-mono font-bold <?php echo $row['fulfillment_pct'] == 100 ? 'text-emerald-400 bg-emerald-500/5' : 'text-amber-400 bg-amber-500/5'; ?>"><?php echo $row['fulfillment_pct']; ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- كعكة تلبية الطلبات في الأسفل -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800">
        <h3 class="text-xs font-bold text-slate-300 mb-4">🍩 كعكة تلبية وصرف البضائع الفورية</h3>
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
                labels: ['تم التسليم بالكامل', 'معلق قيد الشحن'],
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
    <script defer src="js/app.js?v=20260723_v14"></script>
</body>
</html>
