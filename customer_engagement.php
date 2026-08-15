<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مصفوفة تكرار الزيارة والولاء V2
// تم التحديث لاستغلال العرض الكامل للشاشة باحترافية وتناسق تام
// ====================================================================
$page_title = 'مصفوفة تكرار الزيارة والولاء';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

$data = getCustomerEngagementData($pdo);
$is_mock_data = false;

// حساب المؤشرات
$total = count($data);
$vip = 0; $regular = 0; $occasional = 0;
foreach ($data as $row) {
    $t = $row['engagement_tier'] ?? '';
    if ($t === 'VIP') $vip++;
    elseif ($t === 'REGULAR') $regular++;
    else $occasional++;
}

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
    <title>مصفوفة تكرار الزيارة والولاء | Al-Sajana ERP</title>
    
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
            --border-neon: rgba(168, 85, 247, 0.2);
            --text-main: #ffffff;
            --text-muted: #94a3b8;
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
        .engagement-master-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 12px 12px 60px 12px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ── كروت الإحصائيات الفخمة V2 ── */
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
            box-shadow: 0 15px 35px var(--border-glow, rgba(168, 85, 247, 0.25));
        }

        .stat-card-v2::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, var(--bar-color, #c084fc), transparent);
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

        .engagement-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .engagement-table th {
            background: rgba(30, 41, 59, 0.8);
            color: var(--text-main);
            padding: 14px 16px;
            font-weight: 900;
            font-size: 0.9rem;
            text-align: right;
            border-bottom: 2.5px solid rgba(168, 85, 247, 0.3);
        }

        :root.light-theme .engagement-table th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border-bottom: 3px solid #000000 !important;
        }

        .engagement-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            font-weight: 700;
            font-size: 0.9rem;
        }

        :root.light-theme .engagement-table td {
            border-bottom: 1.5px solid #e2e8f0 !important;
            color: #000000 !important;
        }

        .engagement-table tr:hover td {
            background: rgba(168, 85, 247, 0.06);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim { animation: fadeInUp 0.5s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .engagement-master-wrapper { padding: 0 !important; }
            .glass-panel-v2, .stat-card-v2 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="engagement-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-purple-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="partners_reports_hub.php" onclick="window.location.href='partners_reports_hub.php'; return false;" class="inline-flex items-center gap-2 text-purple-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع لتقارير العملاء والموردين
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-ranking-star text-purple-400"></i>
                    مصفوفة تكرار الزيارة ومؤشر ولاء العملاء
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">تحليل الذكاء التجاري لتكرار الشراء وتصنيف سلوك العملاء إلى (VIP - مستمر - عابر).</p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print();" class="bg-purple-600 hover:bg-purple-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة المصفوفة
                </button>
            </div>
        </div>

        <?php if ($is_mock_data): ?>
        <div class="bg-amber-900/30 border border-amber-500/30 text-amber-300 px-4 py-2.5 rounded-xl text-sm text-center font-bold">
            ⚠️ الشاشة تعرض بيانات تجريبية حالياً — سيتم عرض البيانات الحقيقية تلقائياً عند توفر عمليات بيعية في النظام.
        </div>
        <?php endif; ?>

        <!-- 📊 KPI SUMMARY CARDS (Full Width 4 Grid) 📊 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- 1. Total Customers -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(56, 189, 248, 0.4); --bar-color: #38bdf8; animation-delay:.1s">
                <span class="text-xs text-slate-400 font-bold block">👥 إجمالي العملاء المسجلين</span>
                <span class="text-3xl font-black text-sky-400 mt-2 block"><?php echo $total; ?></span>
                <span class="text-xs text-slate-500 mt-1 block">قاعدة العملاء الكلية</span>
            </div>

            <!-- 2. VIP Tier -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(168, 85, 247, 0.4); --bar-color: #c084fc; animation-delay:.2s">
                <span class="text-xs text-purple-300 font-bold block">⭐ كبار العملاء (VIP)</span>
                <span class="text-3xl font-black text-purple-400 mt-2 block"><?php echo $vip; ?></span>
                <span class="text-xs text-purple-400/80 mt-1 block">أعلى من 15 زيارة/عملية</span>
            </div>

            <!-- 3. Regular Tier -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(59, 130, 246, 0.4); --bar-color: #3b82f6; animation-delay:.3s">
                <span class="text-xs text-blue-300 font-bold block">🔄 العملاء المستمرون</span>
                <span class="text-3xl font-black text-blue-400 mt-2 block"><?php echo $regular; ?></span>
                <span class="text-xs text-blue-400/80 mt-1 block">بين 5 إلى 14 زيارة</span>
            </div>

            <!-- 4. Occasional Tier -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(148, 163, 184, 0.4); --bar-color: #94a3b8; animation-delay:.4s">
                <span class="text-xs text-slate-400 font-bold block">⏱️ العملاء العابرون</span>
                <span class="text-3xl font-black text-slate-400 mt-2 block"><?php echo $occasional; ?></span>
                <span class="text-xs text-slate-500 mt-1 block">أقل من 5 زيارات</span>
            </div>
        </div>

        <!-- 📈 MAIN CONTENT SECTION: TABLE (2/3) + CHART (1/3) FULL STRETCH 📈 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- 1. Customer Loyalty Table (2 Cols Wide) -->
            <div class="lg:col-span-2 anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.5s">
                <div>
                    <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-ranking-star text-purple-400"></i>
                            تصنيف العملاء وتكرار الزيارات الميدانية/البيعية
                        </h3>
                        <span class="text-xs font-bold text-purple-400 bg-purple-500/10 px-3 py-1 rounded-lg border border-purple-500/20">
                            إجمالي المسجلين: <?php echo count($data); ?>
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="engagement-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم العميل</th>
                                    <th>عدد الزيارات/الفواتير</th>
                                    <th>إجمالي حجم المشتريات</th>
                                    <th>تصنيف درجة الولاء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data)): ?>
                                <tr><td colspan="5" class="text-center py-6 text-slate-400 font-bold">لا تتوفر بيانات عملاء مسجلة في الوقت الحالي.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data as $i => $row):
                                        $tier = $row['engagement_tier'] ?? 'OCCASIONAL';
                                        if ($tier === 'VIP') { 
                                            $badge_cls = 'bg-purple-900/40 text-purple-300 border-purple-500/40'; 
                                            $label = '⭐ VIP (ممتاز)'; 
                                        } elseif ($tier === 'REGULAR') { 
                                            $badge_cls = 'bg-blue-900/40 text-blue-300 border-blue-500/40'; 
                                            $label = '🔄 مستمر (جيد)'; 
                                        } else { 
                                            $badge_cls = 'bg-slate-800 text-slate-400 border-slate-700'; 
                                            $label = '⏱️ عابر (جديد)'; 
                                        }
                                    ?>
                                    <tr>
                                        <td class="text-slate-500 font-mono"><?php echo $i + 1; ?></td>
                                        <td class="font-bold text-slate-200"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td class="font-black text-cyan-400 font-mono"><?php echo $row['total_visit_count']; ?> زيارة</td>
                                        <td class="font-bold font-mono text-emerald-400">
                                            <?php echo number_format($row['total_spend'], 2); ?> 
                                            <span class="text-xs text-slate-400"><?php echo $selected_currency; ?></span>
                                        </td>
                                        <td>
                                            <span class="px-3 py-1 rounded-full text-xs font-black border inline-block <?php echo $badge_cls; ?>">
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

            <!-- 2. Loyalty Breakdown Chart (1 Col Wide) -->
            <div class="anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.6s">
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-chart-bar text-purple-400"></i>
                            تكرار الزيارات حسب العميل
                        </h3>
                    </div>

                    <div style="height: 380px; position: relative;">
                        <canvas id="engageChart"></canvas>
                    </div>
                </div>

                <div class="flex gap-4 mt-4 text-xs font-bold justify-center border-t border-slate-800 pt-3">
                    <span class="flex items-center gap-1.5 text-purple-400"><span class="w-3 h-3 rounded-full bg-purple-500 inline-block"></span> VIP (15+)</span>
                    <span class="flex items-center gap-1.5 text-blue-400"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> مستمر (5-14)</span>
                    <span class="flex items-center gap-1.5 text-slate-400"><span class="w-3 h-3 rounded-full bg-slate-500 inline-block"></span> عابر (<5)</span>
                </div>
            </div>

        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            آخر تحديث للحسابات: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026
        </div>

    </div>

    <!-- Chart setup script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('engageChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($data, 'customer_name'), JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    label: 'عدد الزيارات',
                    data: <?php echo json_encode(array_column($data, 'total_visit_count')); ?>,
                    backgroundColor: <?php echo json_encode(array_map(function($r){
                        $t = $r['engagement_tier'] ?? '';
                        return $t === 'VIP' ? 'rgba(168, 85, 247, 0.75)' : ($t === 'REGULAR' ? 'rgba(59, 130, 246, 0.75)' : 'rgba(148, 163, 184, 0.6)');
                    }, $data)); ?>,
                    borderColor: <?php echo json_encode(array_map(function($r){
                        $t = $r['engagement_tier'] ?? '';
                        return $t === 'VIP' ? '#a855f7' : ($t === 'REGULAR' ? '#3b82f6' : '#94a3b8');
                    }, $data)); ?>,
                    borderWidth: 1.5,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#94a3b8', font: { family: 'Cairo', weight: 'bold' } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#f8fafc', font: { family: 'Cairo', size: 12, weight: 'bold' } }, grid: { display: false } }
                }
            }
        });
    });
    </script>
    <script defer src="js/app.js"></script>
</body>
</html>
