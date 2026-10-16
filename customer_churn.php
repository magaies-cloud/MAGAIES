<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - معدل دوران وانقطاع الزبائن V2
// تم التحديث لاستغلال العرض الكامل للشاشة باحترافية وتناسق تام
// ====================================================================
$page_title = 'معدل دوران وانقطاع الزبائن';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

$data = getCustomerChurnData($pdo);
$is_mock_data = false;

// حساب المؤشرات
$total = count($data);
$active = 0; $medium = 0; $high = 0;
foreach ($data as $row) {
    $s = $row['churn_risk_status'] ?? '';
    if ($s === 'ACTIVE') $active++;
    elseif ($s === 'MEDIUM RISK') $medium++;
    elseif ($s === 'HIGH RISK') $high++;
}
$at_risk = $medium + $high;

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
    <title>معدل دوران وانقطاع الزبائن | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">

    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-dark: #04020a;
            --panel-bg: rgba(14, 8, 28, 0.75);
            --border-neon: rgba(245, 158, 11, 0.2);
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
        .churn-master-wrapper {
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
            box-shadow: 0 15px 35px var(--border-glow, rgba(245, 158, 11, 0.25));
        }

        .stat-card-v2::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, var(--bar-color, #f59e0b), transparent);
        }

        /* ── الجداول الفخمة V2 ── */
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

        .churn-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .churn-table th {
            background: rgba(30, 41, 59, 0.8);
            color: var(--text-main);
            padding: 14px 16px;
            font-weight: 900;
            font-size: 0.9rem;
            text-align: right;
            border-bottom: 2.5px solid rgba(245, 158, 11, 0.3);
        }

        :root.light-theme .churn-table th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border-bottom: 3px solid #000000 !important;
        }

        .churn-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            font-weight: 700;
            font-size: 0.9rem;
        }

        :root.light-theme .churn-table td {
            border-bottom: 1.5px solid #e2e8f0 !important;
            color: #000000 !important;
        }

        .churn-table tr:hover td {
            background: rgba(245, 158, 11, 0.06);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim { animation: fadeInUp 0.5s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .churn-master-wrapper { padding: 0 !important; }
            .glass-panel-v2, .stat-card-v2 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="churn-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-amber-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="partners_reports_hub.php" onclick="window.location.href='partners_reports_hub.php'; return false;" class="inline-flex items-center gap-2 text-amber-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع لتقارير العملاء والموردين
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-user-slash text-amber-400"></i>
                    تقرير معدل دوران وانقطاع العملاء (Customer Churn)
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">مراقبة التوقف والانقطاع عن الشراء، وتحليل فترات الركود لتحديد العملاء المعرضين للفقدان.</p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print();" class="bg-amber-600 hover:bg-amber-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة التقرير
                </button>
            </div>
        </div>

        <?php if ($is_mock_data): ?>
        <div class="bg-amber-900/30 border border-amber-500/30 text-amber-300 px-4 py-2.5 rounded-xl text-sm text-center font-bold">
            ⚠️ الشاشة تعرض بيانات تجريبية حالياً — سيتم عرض البيانات الحقيقية تلقائياً عند توفر عمليات بيعية في النظام.
        </div>
        <?php endif; ?>

        <!-- 📊 KPI SUMMARY CARDS (Full Width Grid) 📊 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- 1. Total Customers -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(99, 102, 241, 0.4); --bar-color: #818cf8; animation-delay:.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 font-bold block">👥 إجمالي العملاء</span>
                        <span class="text-3xl font-black text-slate-200 mt-2 block"><?php echo $total; ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl text-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Active Customers -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(16, 185, 129, 0.4); --bar-color: #10b981; animation-delay:.2s">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-emerald-400 font-bold block">💚 عملاء نشطون مستمرون</span>
                        <span class="text-3xl font-black text-emerald-400 mt-2 block"><?php echo $active; ?></span>
                        <span class="text-xs text-emerald-400/80 mt-1 block"><?php echo $total > 0 ? round(($active/$total)*100) : 0; ?>% من إجمالي العملاء</span>
                    </div>
                    <div class="p-3.5 rounded-xl text-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                </div>
            </div>

            <!-- 3. At-Risk Customers -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(239, 68, 68, 0.4); --bar-color: #ef4444; animation-delay:.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-rose-400 font-bold block">⚠️ عملاء معرضون لخطر الانقطاع</span>
                        <span class="text-3xl font-black text-rose-400 mt-2 block"><?php echo $at_risk; ?></span>
                        <span class="text-xs text-amber-400 mt-1 block"><?php echo $medium; ?> متوسط الخطورة · <?php echo $high; ?> مرتفع الخطورة</span>
                    </div>
                    <div class="p-3.5 rounded-xl text-xl bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📈 MAIN CONTENT SECTION: TABLE (2/3) + CHART (1/3) FULL STRETCH 📈 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- 1. Customer Retention Table (2 Cols Wide) -->
            <div class="lg:col-span-2 anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.4s">
                <div>
                    <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-table-list text-amber-400"></i>
                            تفاصيل حالة انقطاع وركود العمليات للعملاء
                        </h3>
                        <span class="text-xs font-bold text-amber-400 bg-amber-500/10 px-3 py-1 rounded-lg border border-amber-500/20">
                            إجمالي السجلات: <?php echo count($data); ?>
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="churn-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم العميل</th>
                                    <th>تاريخ آخر عملية شراء</th>
                                    <th>عدد أيام الركود</th>
                                    <th>درجة خطورة الانقطاع</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data)): ?>
                                <tr><td colspan="5" class="text-center py-6 text-slate-400 font-bold">لا تتوفر بيانات عملاء مسجلة في الوقت الحالي.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data as $i => $row):
                                        $status = $row['churn_risk_status'] ?? 'ACTIVE';
                                        if ($status === 'ACTIVE') {
                                            $badge_cls = 'bg-emerald-900/40 text-emerald-300 border-emerald-500/40';
                                            $icon = 'fa-check-circle';
                                            $label = '💚 نشط (مستمر)';
                                        } elseif ($status === 'MEDIUM RISK') {
                                            $badge_cls = 'bg-amber-900/40 text-amber-300 border-amber-500/40';
                                            $icon = 'fa-exclamation-triangle';
                                            $label = '⚠️ متوسط الخطورة';
                                        } else {
                                            $badge_cls = 'bg-rose-900/40 text-rose-300 border-rose-500/40';
                                            $icon = 'fa-skull-crossbones';
                                            $label = '🚨 مرتفع الخطورة (منقطع)';
                                        }
                                        $days = $row['days_since_last_purchase'] ?? 'N/A';
                                        $days_color = is_numeric($days) ? ($days > 60 ? 'text-rose-400' : ($days > 30 ? 'text-amber-400' : 'text-emerald-400')) : 'text-slate-400';
                                    ?>
                                    <tr>
                                        <td class="text-slate-500 font-mono"><?php echo $i + 1; ?></td>
                                        <td class="font-bold text-slate-200"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td class="font-mono text-slate-300"><?php echo htmlspecialchars($row['last_purchase_date'] ?? 'غير متوفر'); ?></td>
                                        <td class="font-black font-mono <?php echo $days_color; ?>">
                                            <?php echo is_numeric($days) ? $days . ' يوم' : $days; ?>
                                        </td>
                                        <td>
                                            <span class="px-3 py-1 rounded-full text-xs font-black border inline-flex items-center gap-1.5 <?php echo $badge_cls; ?>">
                                                <i class="fa-solid <?php echo $icon; ?>"></i>
                                                <?php echo $label; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. Risk Distribution Doughnut Chart (1 Col Wide) -->
            <div class="anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.5s">
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-amber-400"></i>
                            توزيع مخاطر انقطاع العملاء
                        </h3>
                    </div>

                    <div style="height: 320px; position: relative;" class="flex items-center justify-center">
                        <canvas id="churnChart"></canvas>
                    </div>
                </div>

                <div class="flex gap-4 mt-4 text-xs font-bold justify-center border-t border-slate-800 pt-3">
                    <span class="flex items-center gap-1.5 text-emerald-400"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> نشط (<?php echo $active; ?>)</span>
                    <span class="flex items-center gap-1.5 text-amber-400"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> متوسط (<?php echo $medium; ?>)</span>
                    <span class="flex items-center gap-1.5 text-rose-400"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span> مرتفع (<?php echo $high; ?>)</span>
                </div>
            </div>

        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            آخر تحديث للحسابات: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026
        </div>

    </div>

    <!-- Doughnut Chart script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('churnChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['عملاء نشطون', 'متوسط الخطورة', 'مرتفع الخطورة (منقطع)'],
                datasets: [{
                    data: [<?php echo "$active, $medium, $high"; ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 2,
                    borderColor: 'rgba(15, 23, 42, 0.9)',
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { display: false } }
            }
        });
    });
    </script>
    <script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>
