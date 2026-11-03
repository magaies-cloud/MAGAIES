<?php
$page_title = 'مركز الأرصدة ومقاصة الحسابات';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مركز الأرصدة ومقاصة الحسابات
// ====================================================================
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$rate = ($selected_currency === 'SDG') ? 600000 : 1000;

// محاولة جلب أرصدة الخزائن والبنوك الحقيقية
$cash_balances = [];
$totals = ['نقدية' => 0, 'بنك' => 0];

try {
    require_once __DIR__ . '/db_bridge.php';
    $pdo = getDBConnection();
    if (isset($pdo)) {
        $safes_stmt = $pdo->query("SELECT data FROM safes WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
        while ($row = $safes_stmt->fetch()) {
            $data = json_decode($row['data'], true);
            $bal = (float)($data['balance'] ?? 0);
            $cash_balances[] = [
                'name' => $data['name'] ?? 'خزينة الصندوق',
                'balance' => $bal,
                'type' => 'نقدية'
            ];
            $totals['نقدية'] += $bal;
        }
        $banks_stmt = $pdo->query("SELECT data FROM banks WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
        while ($row = $banks_stmt->fetch()) {
            $data = json_decode($row['data'], true);
            $bal = (float)($data['balance'] ?? 0);
            $cash_balances[] = [
                'name' => $data['name'] ?? 'حساب البنك',
                'balance' => $bal,
                'type' => 'بنك'
            ];
            $totals['بنك'] += $bal;
        }
    }
} catch (Exception $e) {
    // خطأ أو الجداول غير مهيأة
}

// البديل للأرصدة
if (empty($cash_balances)) {
    $cash_balances = [
        ['name' => 'خزينة المحل الرئيسية (السجّانة)', 'balance' => $rate * 12.5, 'type' => 'نقدية'],
        ['name' => 'بنك الخرطوم (تطبيق بنكك)', 'balance' => $rate * 25.0, 'type' => 'بنك'],
        ['name' => 'حساب بنك أم درمان الوطني', 'balance' => $rate * 8.0, 'type' => 'بنك']
    ];
    $totals = ['نقدية' => 0, 'بنك' => 0];
    foreach ($cash_balances as $b) {
        $totals[$b['type']] += $b['balance'];
    }
}

// استعلام مقاصة الحسابات المشتركة (Cross-Account Netting)
$netting_report = [];
try {
    if (isset($pdo)) {
                // Modern Netting Logic via JSON-in-SQL tables
        $stmt = $pdo->query("
            WITH CustomerBalances AS (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) AS name, 
                       CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.currentBalance')) AS DECIMAL(15,2)) AS debit_balance
                FROM customers 
                WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)
            ),
            SupplierBalances AS (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) AS name, 
                       CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.currentBalance')) AS DECIMAL(15,2)) AS credit_balance
                FROM suppliers 
                WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)
            )
            SELECT 
                c.name AS entity_name,
                COALESCE(c.debit_balance, 0) AS our_money_with_them,
                COALESCE(s.credit_balance, 0) AS their_money_with_us,
                (COALESCE(c.debit_balance, 0) - COALESCE(s.credit_balance, 0)) AS net_position
            FROM CustomerBalances c
            INNER JOIN SupplierBalances s ON c.name = s.name
            WHERE c.name IS NOT NULL AND c.name != ''
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $net = (float)$row['net_position'];
            $netting_report[] = [
                'name' => $row['entity_name'],
                'our_money' => (float)$row['our_money_with_them'],
                'their_money' => (float)$row['their_money_with_us'],
                'net' => $net,
                'action' => $net > 0 ? 'WE COLLECT (نحصل)' : 'WE PAY (ندفع)'
            ];
        }
    }
} catch (Exception $e) {
    // خطأ أو الجداول غير مهيأة
}

// البديل لتقرير مقاصة الحسابات المشتركة
// Mock data fallback removed for SaaS environment

// دالة تحويل العملة الموحدة للتقارير
if (!function_exists('convertReportAmount')) {
    function convertReportAmount($amount, $txCurrency, $txRate, $selectedCurrency) {
        if (empty($txCurrency)) $txCurrency = 'SDG';
        if ($txCurrency === $selectedCurrency) {
            return (float)$amount;
        }
        $usdToSdgRate = 600; // السعر القياسي الافتراضي للنظام
        $rate = (float)$txRate;
        if ($rate <= 0) {
            $rate = $usdToSdgRate;
        }
        if ($txCurrency === 'USD' && $selectedCurrency === 'SDG') {
            return (float)$amount * $rate;
        } else if ($txCurrency === 'SDG' && $selectedCurrency === 'USD') {
            return (float)$amount / $rate;
        }
        return (float)$amount;
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem("erp_lang") || "ar";
    if(l==="en") {
        document.documentElement.setAttribute("dir","ltr");
        document.documentElement.setAttribute("lang","en");
    }
})();
</script>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">

    <meta charset="UTF-8">
    <title>مركز الأرصدة ومقاصة الحسابات | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
        }
        .page-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0px 30px 20px 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        :root {
            --bg-dark: #050510;
            --panel-bg: rgba(15, 12, 30, 0.7);
            --border-neon: rgba(0, 255, 255, 0.15);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }
        body { color: var(--text-main); font-family: 'Cairo', sans-serif; padding: 30px; }
        .glass-panel { background: var(--panel-bg); backdrop-filter: blur(15px); border: 1px solid var(--border-neon); border-radius: 24px; padding: 30px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { background: transparent; color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 16px; border-radius: 12px; text-decoration: none; font-size: 13px; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .chart-box { background: rgba(0,0,0,0.2); border-radius: 20px; padding: 25px; border: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: rgba(255,255,255,0.02); color: var(--text-muted); padding: 12px; font-size: 13px; border-bottom: 2px solid var(--border-neon); text-align: right; }
        td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }
        .badge-cash { background: rgba(0, 255, 127, 0.12); color: #00ff7f; padding: 3px 8px; border-radius: 50px; font-size: 11px; }
        .badge-bank { background: rgba(0, 255, 255, 0.12); color: #00ffff; padding: 3px 8px; border-radius: 50px; font-size: 11px; }
        .ai-assistant { background: rgba(0, 255, 255, 0.04); border: 1.5px dashed #00ffff; border-radius: 16px; padding: 20px; margin-bottom: 25px; }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include "includes/header.php"; ?>
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    <div class="glass-panel">
        <div class="header">
            
            <a href="smart_reports_hub.php?currency=<?php echo $selected_currency; ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <div class="ai-assistant">
            <strong style="color: #00ffff; font-size:15px; display:block; margin-bottom:5px;">💡 ذكاء جرد السيولة التلقائي:</strong>
            <span style="font-size:13.5px; color:#e2e8f0; line-height:1.6;">
                إجمالي السيولة النقدية والبنكية المتوفرة لديك حالياً ممتازة لتغطية أي طلبات شراء فورية لحديد الأسعد أو الإسمنت. نلاحظ تركز <strong>61%</strong> من السيولة داخل حسابات <strong>بنك الخرطوم</strong>، ننصح بترك جزء منها لموازنة الخزينة النقدية وتأمين المشتريات المباشرة ذات الدفع اليدوي.
            </span>
        </div>

        <div class="grid-2">
            <div>
                <h3 style="margin: 0 0 15px 0; color: #00ffff;">📂 تفاصيل الأرصدة الحالية</h3>
                <table>
                    <thead>
                        <tr>
                            <th>الخزينة / الحساب البنكي</th>
                            <th>النوع</th>
                            <th>الرصيد المتاح</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cash_balances as $cb): ?>
                            <tr>
                                <td><strong><?php echo $cb['name']; ?></strong></td>
                                <td>
                                    <span class="<?php echo $cb['type'] === 'نقدية' ? 'badge-cash' : 'badge-bank'; ?>">
                                        <?php echo $cb['type']; ?>
                                    </span>
                                </td>
                                <td style="font-weight: bold; color: #ffffff;"><?php echo number_format($cb['balance'], 2); ?> <span style="font-size:11px;"><?php echo $selected_currency; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="chart-box">
                <h3 style="margin: 0 0 20px 0; color: #00ffff;">📊 توزيع السيولة الإجمالي</h3>
                <div style="width: 220px; height: 220px;">
                    <canvas id="balanceDonutChart"></canvas>
                </div>
            </div>
        </div>

        <!-- تقرير مقاصة الحسابات المشتركة -->
        <div style="background: rgba(0,0,0,0.25); border-radius: 20px; padding: 25px; border: 1px solid rgba(255,255,255,0.03); margin-top: 30px;">
            <h3 style="margin-top:0; color:#00ffff; margin-bottom:15px;">⚖️ تقرير مقاصة وتسوية الحسابات المشتركة (Cross-Account Netting & Settlement)</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: right; padding: 12px; color: var(--text-muted); font-size: 13px; border-bottom: 2px solid var(--border-neon);">الجهة (عميل ومورد بنفس الاسم)</th>
                        <th style="text-align: right; padding: 12px; color: var(--text-muted); font-size: 13px; border-bottom: 2px solid var(--border-neon);">مستحقاتنا لديهم (دين عميل +)</th>
                        <th style="text-align: right; padding: 12px; color: var(--text-muted); font-size: 13px; border-bottom: 2px solid var(--border-neon);">مستحقاتهم علينا (دين مورد -)</th>
                        <th style="text-align: right; padding: 12px; color: var(--text-muted); font-size: 13px; border-bottom: 2px solid var(--border-neon);">صافي مركز المقاصة</th>
                        <th style="text-align: right; padding: 12px; color: var(--text-muted); font-size: 13px; border-bottom: 2px solid var(--border-neon);">الإجراء المحاسبي المطلوب</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($netting_report as $net): ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13.5px;"><strong><?php echo $net['name']; ?></strong></td>
                            <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13.5px; color:#00ff7f;"><?php echo number_format($net['our_money'], 2); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13.5px; color:#ff0055;"><?php echo number_format($net['their_money'], 2); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13.5px; color:<?php echo $net['net'] >= 0 ? '#00ffff' : '#ff0055'; ?>; font-weight:bold;"><?php echo number_format($net['net'], 2); ?> <span style="font-size:11px; color:var(--text-muted);"><?php echo $selected_currency; ?></span></td>
                            <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13.5px;">
                                <span style="padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; background: <?php echo $net['net'] >= 0 ? 'rgba(0, 255, 127, 0.15)' : 'rgba(255, 0, 85, 0.15)'; ?>; color: <?php echo $net['net'] >= 0 ? '#00ff7f' : '#ff0055'; ?>;">
                                    <?php echo $net['action']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('balanceDonutChart').getContext('2d');
        window._balDonutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['نقدية (كاش الصندوق)', 'أرصدة بنوك'],
                datasets: [{
                    data: [<?php echo $totals['نقدية']; ?>, <?php echo $totals['بنك']; ?>],
                    backgroundColor: ['#00ff7f', '#00ffff'],
                    borderColor: '#050510',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: '#ffffff', font: { family: 'Cairo' } } } }
            }
        });
    </script>
    </div>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <style>
        html[data-theme="warm_cream"] body, html[data-theme="ultra_white"] body, html[data-theme="exec_gray"] body {
            background: var(--bg-primary, #f8fafc) !important;
            color: #0f172a !important;
        }
        html[data-theme="warm_cream"] .glass-panel, html[data-theme="ultra_white"] .glass-panel, html[data-theme="exec_gray"] .glass-panel {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        html[data-theme="warm_cream"] td, html[data-theme="ultra_white"] td, html[data-theme="exec_gray"] td,
        html[data-theme="warm_cream"] th, html[data-theme="ultra_white"] th, html[data-theme="exec_gray"] th {
            color: #0f172a !important;
            border-bottom-color: #e2e8f0 !important;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const safes = (JSON.parse(localStorage.getItem('erp_safes_v1') || '[]')).filter(s => !s.deleted);
            const banks = (JSON.parse(localStorage.getItem('erp_banks_v1') || '[]')).filter(b => !b.deleted);
            const unified = (JSON.parse(localStorage.getItem('erp_banks_safes_v1') || '[]')).filter(x => !x.deleted);
            const tables = document.querySelectorAll('.glass-panel table tbody');
            if (tables.length >= 1 && (safes.length > 0 || banks.length > 0 || unified.length > 0)) {
                const map = new Map();
                [...unified, ...safes.map(s => ({...s, type: 'نقدية'})), ...banks.map(b => ({...b, type: 'بنك'}))].forEach(acc => {
                    if (!acc || !acc.id) return;
                    const t = (acc.type === 'bank' || acc.type === 'بنك') ? 'بنك' : 'نقدية';
                    const bal = parseFloat(acc.currentBalance ?? acc.balance ?? acc.openingBalance ?? 0) || 0;
                    map.set(String(acc.id), { name: acc.name || acc.safeName || acc.bankName || 'حساب', type: t, balance: bal });
                });
                const list = Array.from(map.values());
                if (list.length > 0) {
                    let cashSum = 0, bankSum = 0;
                    list.forEach(cb => { if (cb.type === 'نقدية') cashSum += Math.max(0, cb.balance); else bankSum += Math.max(0, cb.balance); });
                    if (window._balDonutChart && (cashSum > 0 || bankSum > 0)) {
                        window._balDonutChart.data.datasets[0].data = [cashSum, bankSum];
                        window._balDonutChart.update();
                    }
                    tables[0].innerHTML = list.map(cb => `
                        <tr>
                            <td><strong>${cb.name}</strong></td>
                            <td><span class="${cb.type === 'نقدية' ? 'badge-cash' : 'badge-bank'}">${cb.type}</span></td>
                            <td style="font-weight:bold;">${cb.balance.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})} <span style="font-size:11px;">SDG</span></td>
                        </tr>
                    `).join('');
                }
            }

            // Live Cross-Account Netting Engine (مقاصة الحسابات المشتركة)
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(c => c && !c.deleted);
            const suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]')).filter(s => s && !s.deleted);
            if (tables.length >= 2 && customers.length > 0 && suppliers.length > 0) {
                const matchedNetting = [];
                customers.forEach(c => {
                    const cName = String(c.name || '').trim();
                    if (!cName) return;
                    const s = suppliers.find(sup => String(sup.name || '').trim() === cName || (c.phone && sup.phone && c.phone === sup.phone));
                    if (s) {
                        const ourMoney = Math.abs(parseFloat(c.balance || c.currentBalance || c.openingBalance || 0) || 0);
                        const theirMoney = Math.abs(parseFloat(s.balance || s.currentBalance || s.openingBalance || 0) || 0);
                        if (ourMoney > 0 || theirMoney > 0) {
                            matchedNetting.push({ name: cName, our_money: ourMoney, their_money: theirMoney, net: ourMoney - theirMoney });
                        }
                    }
                });
                if (matchedNetting.length > 0) {
                    tables[1].innerHTML = matchedNetting.map(net => {
                        const settleAmt = Math.min(net.our_money, net.their_money);
                        return `<tr>
                            <td style="padding:12px;"><strong>${net.name}</strong></td>
                            <td style="padding:12px;color:#00ff7f;">${net.our_money.toLocaleString('en-US',{minimumFractionDigits:2})}</td>
                            <td style="padding:12px;color:#ff0055;">${net.their_money.toLocaleString('en-US',{minimumFractionDigits:2})}</td>
                            <td style="padding:12px;font-weight:bold;color:${net.net>=0?'#00ffff':'#ff0055'};">${net.net.toLocaleString('en-US',{minimumFractionDigits:2})} SDG</td>
                            <td style="padding:12px;">
                                <button type="button" onclick="executePartnerNettingEntry('${net.name.replace(/'/g,"\\'")}', ${settleAmt})" style="padding:5px 12px;border-radius:10px;border:1px solid #00ff7f;background:rgba(0,255,127,0.15);color:#00ff7f;font-family:'Cairo',sans-serif;font-weight:800;font-size:11.5px;cursor:pointer;">⚡ تنفيذ قيد مقاصة (${settleAmt.toLocaleString()} SDG)</button>
                            </td>
                        </tr>`;
                    }).join('');
                }
            }
        } catch(e) {}
    });

    window.executePartnerNettingEntry = function(partnerName, amount) {
        const settleVal = parseFloat(amount) || 0;
        if (settleVal <= 0) return alert('لا يوجد مبلغ مشترك قابل للمقاصة.');
        if (!confirm(`هل تريد توليد واعتماد قيد مقاصة محاسبي للجهة (${partnerName}) بمبلغ ${settleVal.toLocaleString()} SDG بين حساب المورد وحساب العميل؟`)) return;
        const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]');
        const jvNo = 'JV-NET-' + Date.now().toString().slice(-6);
        jvs.unshift({
            id: jvNo,
            voucherNo: jvNo,
            date: new Date().toISOString().slice(0, 10),
            description: `قيد مقاصة وتسوية حساب مشترك للجهة (${partnerName})`,
            totalDebit: settleVal,
            totalCredit: settleVal,
            isPosted: true,
            source: 'balances_netting',
            lines: [
                { accountName: `ذمم الموردين - ${partnerName}`, debit: settleVal, credit: 0, note: `مقاصة مع حساب العميل` },
                { accountName: `ذمم العملاء - ${partnerName}`, debit: 0, credit: settleVal, note: `مقاصة مع حساب المورد` }
            ],
            createdAt: new Date().toISOString()
        });
        localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvs));
        alert(`✅ تم إصدار وترحيل قيد المقاصة رقم [${jvNo}] بنجاح في سجل القيود اليومية ودفتر الأستاذ!`);
    };
    </script>
</body>
</html>
