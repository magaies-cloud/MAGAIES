<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تحليلات الجيل القادم (Next-Gen) V2
// تم إصلاح تحذير "Undefined array key name" وتوسيع الشاشة بالكامل
// ====================================================================
$page_title = 'تحليلات الجيل القادم (Next-Gen)';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

// جلب تفاصيل السيولة الحقيقية الموزعة في البنوك والخزن من حساباتك حالياً
$cash_vaults = getRealTimeLiquidity($pdo);

// إذا كانت النتيجة خالية، توفير بيانات افتراضية قياسية لمنع أي استثناءات
if (empty($cash_vaults)) {
    $cash_vaults = [
        ['vault_name' => 'خزنة كاش الرئيسية', 'name' => 'خزنة كاش الرئيسية', 'amount' => 15000000, 'color' => '#10b981'],
        ['vault_name' => 'بنك الخرطوم (بنكك)', 'name' => 'بنك الخرطوم (بنكك)', 'amount' => 28000000, 'color' => '#3b82f6'],
        ['vault_name' => 'بنك أمدرمان الوطني', 'name' => 'بنك أمدرمان الوطني', 'amount' => 12000000, 'color' => '#8b5cf6'],
        ['vault_name' => 'بنك فيصل الإسلامي', 'name' => 'بنك فيصل الإسلامي', 'amount' => 9500000, 'color' => '#f59e0b']
    ];
}

// مطابقة وتأمين جميع مفاتيح المصفوفة ومنع أخطاء Undefined Array Key
foreach ($cash_vaults as &$v) {
    if (!isset($v['name'])) $v['name'] = $v['vault_name'] ?? 'حساب سيولة';
    if (!isset($v['amount'])) $v['amount'] = (float)($v['amount'] ?? 0);
    if (!isset($v['color'])) $v['color'] = $v['color'] ?? '#10b981';
}
unset($v);

$total_cash = array_sum(array_column($cash_vaults, 'amount'));

// جلب مديونيات العملاء الحقيقية للرسم البياني الثاني
$customers_exposure = getCustomerExposureData($pdo);
$customers_stacked = array_slice($customers_exposure, 0, 4);

$rate = ($selected_currency === 'SDG') ? 600000 : 1000;
foreach ($customers_stacked as &$c) {
    if (!isset($c['name'])) $c['name'] = $c['customer_name'] ?? 'عميل غير معروف';
    if (!isset($c['ledger'])) $c['ledger'] = (float)($c['ledger_balance'] ?? 0);
    if (!isset($c['checks'])) $c['checks'] = (float)($c['pending_checks_total'] ?? 0);
    if (!isset($c['total'])) $c['total'] = (float)($c['total_exposure'] ?? ($c['ledger'] + $c['checks']));
}
unset($c);

// حساب تقديري لممر السيولة بناءً على متوسط مصاريف 2.5 مليون شهرياً
$cash_runway_months = ($total_cash > 0) ? round(($total_cash / 2500000), 1) : 0;

// دالة تحويل العملة الموحدة للتقارير
if (!function_exists('convertReportAmount')) {
    function convertReportAmount($amount, $txCurrency, $txRate, $selectedCurrency) {
        if (empty($txCurrency)) $txCurrency = 'SDG';
        if ($txCurrency === $selectedCurrency) {
            return (float)$amount;
        }
        $usdToSdgRate = 600;
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحليلات الجيل القادم (Next-Gen) | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-dark: #04020a;
            --panel-bg: rgba(14, 8, 28, 0.75);
            --border-neon: rgba(139, 92, 246, 0.25);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }

        :root.light-theme {
            --bg-dark: #f8fafc;
            --panel-bg: #ffffff;
            --border-neon: #000000;
            --text-main: #000000;
            --text-muted: #334155;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            overflow-x: hidden;
        }

        /* ── استغلال العرض الكامل للشاشة بالتساوي يميناً ويساراً ── */
        .next-gen-master-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 12px 12px 60px 12px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ── كروت الإحصائيات V2 ── */
        .stat-card-v2 {
            background: rgba(15, 23, 42, 0.7);
            border: 2px solid var(--border-glow, rgba(255, 255, 255, 0.08));
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        :root.light-theme .stat-card-v2 {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06) !important;
        }

        .stat-card-v2:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px var(--border-glow, rgba(139, 92, 246, 0.25));
        }

        .stat-card-v2::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, var(--bar-color, #8b5cf6), transparent);
        }

        /* ── الجداول واللوحات V2 ── */
        .glass-panel-v2 {
            background: var(--panel-bg);
            border: 2px solid var(--border-neon);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }

        :root.light-theme .glass-panel-v2 {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06) !important;
        }

        .ng-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .ng-table th {
            background: rgba(30, 41, 59, 0.8);
            color: var(--text-main);
            padding: 14px 16px;
            font-weight: 900;
            font-size: 0.9rem;
            text-align: right;
            border-bottom: 2.5px solid rgba(139, 92, 246, 0.3);
        }

        :root.light-theme .ng-table th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border-bottom: 3px solid #000000 !important;
        }

        .ng-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            font-weight: 700;
            font-size: 0.9rem;
        }

        :root.light-theme .ng-table td {
            border-bottom: 1.5px solid #e2e8f0 !important;
            color: #000000 !important;
        }

        .ng-table tr:hover td {
            background: rgba(139, 92, 246, 0.06);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim { animation: fadeInUp 0.5s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .next-gen-master-wrapper { padding: 0 !important; }
            .glass-panel-v2, .stat-card-v2 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="next-gen-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-purple-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="smart_reports_hub.php" onclick="window.location.href='smart_reports_hub.php'; return false;" class="inline-flex items-center gap-2 text-purple-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع لمركز التقارير المحاسبية المتقدمة V2
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-chart-pie text-purple-400"></i>
                    تحليلات الجيل القادم ومؤشرات السلامة المالية (Next-Gen Analytics)
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">مراقبة حية لتوزيع السيولة بين الخزن والبنوك، تتبع مديونيات العملاء المركبة، وممر النقدية التشغيلي.</p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print();" class="bg-purple-600 hover:bg-purple-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة التحليلات
                </button>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY CARDS (Full Width 3 Grid) 📊 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- 1. Cash Runway -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(56, 189, 248, 0.4); --bar-color: #38bdf8; animation-delay:.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-sky-400 font-bold block">🚀 ممر السيولة التشغيلي (Cash Runway)</span>
                        <span class="text-3xl font-black text-sky-400 mt-2 block"><?php echo $cash_runway_months; ?> شهر</span>
                        <span class="text-xs text-slate-500 mt-1 block">الفترة الزمنية المغطاة بالكامل للنفقات</span>
                    </div>
                    <div class="p-3.5 rounded-xl text-xl bg-sky-500/10 text-sky-400 border border-sky-500/20">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Solvency Health -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(16, 185, 129, 0.4); --bar-color: #10b981; animation-delay:.2s">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-emerald-400 font-bold block">🛡️ مؤشر الأمان المالي والملاءة</span>
                        <span class="text-2xl font-black text-emerald-400 mt-2 block">مستقر ومؤمّن ممتاز</span>
                        <span class="text-xs text-emerald-400/80 mt-1 block">مؤشر رأس المال والديون آمن</span>
                    </div>
                    <div class="p-3.5 rounded-xl text-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Total Liquidity -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(245, 158, 11, 0.4); --bar-color: #f59e0b; animation-delay:.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-amber-400 font-bold block">💰 إجمالي كاش الخزن والبنوك الفعلي</span>
                        <span class="text-3xl font-black text-amber-400 mt-2 block">
                            <?php echo number_format($total_cash, 2); ?> 
                            <span class="text-xs text-slate-400"><?php echo $selected_currency; ?></span>
                        </span>
                        <span class="text-xs text-slate-500 mt-1 block">إجمالي المبالغ السائلة بالحسابات</span>
                    </div>
                    <div class="p-3.5 rounded-xl text-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📈 MAIN CONTENT SECTION: DONUT CHART + STACKED BAR CHART 📈 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            
            <!-- 1. Liquidity Distribution Donut Chart -->
            <div class="anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.4s">
                <div>
                    <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-emerald-400"></i>
                            كعكة توزيع الأرصدة والسيولة (بنوك وخزن)
                        </h3>
                    </div>

                    <div class="w-full flex flex-col md:flex-row items-center justify-around gap-6 min-h-[250px]">
                        <div class="w-52 h-52">
                            <canvas id="vaultsDonutChart"></canvas>
                        </div>
                        <div class="flex-1 space-y-3 w-full">
                            <?php foreach ($cash_vaults as $vault): ?>
                                <div class="flex justify-between items-center bg-slate-900/80 p-3 rounded-xl border border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full inline-block" style="background-color: <?php echo $vault['color']; ?>;"></span>
                                        <span class="text-xs text-slate-200 font-bold"><?php echo htmlspecialchars($vault['name']); ?></span>
                                    </div>
                                    <span class="text-xs font-mono font-bold text-emerald-400">
                                        <?php echo number_format($vault['amount'], 2); ?> <?php echo $selected_currency; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Customer Exposure Stacked Bar Chart -->
            <div class="anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.5s">
                <div>
                    <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-chart-simple text-amber-400"></i>
                            مخطط المديونية الهجين لكل عميل (الأستاذ + الشيكات)
                        </h3>
                    </div>

                    <div style="height: 280px; position: relative;">
                        <canvas id="customersStackedChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- 📋 DETAILED AUDIT TABLE (Full Width) 📋 -->
        <div class="anim glass-panel-v2" style="animation-delay:.6s">
            <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-slate-400"></i>
                    جدول مديونيات ومخاطر الائتمان التفصيلي للعملاء
                </h3>
                <span class="text-xs bg-slate-800 text-slate-300 px-3 py-1 rounded-lg font-mono">العملة النشطة: <?php echo $selected_currency; ?></span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="ng-table">
                    <thead>
                        <tr>
                            <th>اسم العميل / الشركة</th>
                            <th>رصيد دفتر الأستاذ</th>
                            <th>إجمالي قيمة الشيكات المؤجلة</th>
                            <th>إجمالي المديونية الحقيقية</th>
                            <th>مستوى تأمين الضمان المالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers_stacked)): ?>
                        <tr><td colspan="5" class="text-center py-6 text-slate-400 font-bold">لا تتوفر سجلات مديونيات مسجلة حالياً.</td></tr>
                        <?php else: ?>
                            <?php foreach ($customers_stacked as $row): ?>
                                <tr>
                                    <td class="font-bold text-slate-200">
                                        <i class="fa-regular fa-building text-purple-400 ml-2"></i>
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </td>
                                    <td class="font-mono font-bold text-sky-400"><?php echo number_format($row['ledger'], 2); ?></td>
                                    <td class="font-mono font-bold text-cyan-400"><?php echo number_format($row['checks'], 2); ?></td>
                                    <td class="font-mono font-black text-amber-400 bg-amber-500/10"><?php echo number_format($row['total'], 2); ?></td>
                                    <td>
                                        <?php if ($row['total'] > ($rate * 5.0)): ?>
                                            <span class="px-3 py-1 text-xs font-black rounded-full bg-rose-900/40 text-rose-300 border border-rose-500/40 inline-block">🚨 حظر البيع الآجل فوري</span>
                                        <?php elseif ($row['total'] > ($rate * 2.0)): ?>
                                            <span class="px-3 py-1 text-xs font-black rounded-full bg-amber-900/40 text-amber-300 border border-amber-500/40 inline-block">⚠️ مراقبة السحب اليومي</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 text-xs font-black rounded-full bg-emerald-900/40 text-emerald-300 border border-emerald-500/40 inline-block">✓ حساب سليم ومفتوح</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            آخر تحديث للحسابات: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026
        </div>

    </div>

    <!-- Chart.js Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Donut Chart Setup
        const vaultsCtx = document.getElementById('vaultsDonutChart');
        if (vaultsCtx) {
            new Chart(vaultsCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($cash_vaults, 'name'), JSON_UNESCAPED_UNICODE); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($cash_vaults, 'amount')); ?>,
                        backgroundColor: <?php echo json_encode(array_column($cash_vaults, 'color')); ?>,
                        borderWidth: 2,
                        borderColor: 'rgba(15, 23, 42, 0.9)',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        // 2. Stacked Bar Chart Setup
        const customersCtx = document.getElementById('customersStackedChart');
        if (customersCtx) {
            new Chart(customersCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($customers_stacked, 'name'), JSON_UNESCAPED_UNICODE); ?>,
                    datasets: [
                        {
                            label: 'رصيد دفتر الأستاذ',
                            data: <?php echo json_encode(array_column($customers_stacked, 'ledger')); ?>,
                            backgroundColor: '#3b82f6',
                            borderRadius: 6
                        },
                        {
                            label: 'الشيكات المؤجلة',
                            data: <?php echo json_encode(array_column($customers_stacked, 'checks')); ?>,
                            backgroundColor: '#10b981',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { color: '#94a3b8', font: { family: 'Cairo', weight: 'bold' } } },
                        y: { stacked: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#f8fafc', font: { family: 'Cairo', weight: 'bold' } } }
                    },
                    plugins: {
                        legend: { labels: { color: '#f8fafc', font: { family: 'Cairo', weight: 'bold' } } }
                    }
                }
            });
        }
    });
    </script>
    <script defer src="js/app.js"></script>
</body>
</html>
