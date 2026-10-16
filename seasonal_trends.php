<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - استهلاك الأصناف الموسمية V2
// تم التحديث لاستغلال العرض الكامل للشاشة باحترافية وتناسق تام
// ====================================================================
$page_title = 'استهلاك الأصناف الموسمية';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

$data = getSeasonalTrendsData($pdo);
$is_mock_data = false;

// حساب المؤشرات الأولية من خادم البيانات
$total_items = count($data);
$total_annual = array_sum(array_column($data, 'total_annual_qty'));
$total_autumn = array_sum(array_column($data, 'autumn_rainy_season_qty'));
$total_summer = array_sum(array_column($data, 'summer_dry_season_qty'));
$dominant_season = $total_summer >= $total_autumn ? 'الصيف (الجفاف)' : 'الخريف (المطر)';

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
    <title>استهلاك الأصناف الموسمية | Al-Sajana ERP</title>

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
            --border-neon: rgba(16, 185, 129, 0.25);
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
        .seasonal-master-wrapper {
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
            box-shadow: 0 15px 35px var(--border-glow, rgba(16, 185, 129, 0.25));
        }

        .stat-card-v2::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, var(--bar-color, #10b981), transparent);
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

        .seasonal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .seasonal-table th {
            background: rgba(30, 41, 59, 0.8);
            color: var(--text-main);
            padding: 14px 16px;
            font-weight: 900;
            font-size: 0.9rem;
            text-align: right;
            border-bottom: 2.5px solid rgba(16, 185, 129, 0.3);
        }

        :root.light-theme .seasonal-table th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border-bottom: 3px solid #000000 !important;
        }

        .seasonal-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            font-weight: 700;
            font-size: 0.9rem;
        }

        :root.light-theme .seasonal-table td {
            border-bottom: 1.5px solid #e2e8f0 !important;
            color: #000000 !important;
        }

        .seasonal-table tr:hover td {
            background: rgba(16, 185, 129, 0.06);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim { animation: fadeInUp 0.5s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .seasonal-master-wrapper { padding: 0 !important; }
            .glass-panel-v2, .stat-card-v2 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="seasonal-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-emerald-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="smart_reports_hub.php" onclick="window.location.href='smart_reports_hub.php'; return false;" class="inline-flex items-center gap-2 text-emerald-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع لمركز التقارير المحاسبية المتقدمة V2
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-cloud-sun text-emerald-400"></i>
                    تقرير استهلاك الأصناف والطلب الموسمي (Seasonal Trends)
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">تحليل نمط استهلاك المواد ومبيعات البضائع عبر فصول السنة (فصل الصيف/الجفاف وفصل الخريف/الأمطار).</p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print();" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة التقرير
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
            <!-- 1. Total Analyzed Items -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(56, 189, 248, 0.4); --bar-color: #38bdf8; animation-delay:.1s">
                <span class="text-xs text-sky-400 font-bold block">📦 الأصناف المحللة موسمياً</span>
                <span class="text-3xl font-black text-sky-400 mt-2 block" id="kpi-total-items"><?php echo $total_items; ?> صنف</span>
                <span class="text-xs text-slate-500 mt-1 block">توزيع الاستهلاك السنوي</span>
            </div>

            <!-- 2. Summer / Dry Season Qty -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(245, 158, 11, 0.4); --bar-color: #f59e0b; animation-delay:.2s">
                <span class="text-xs text-amber-400 font-bold block">☀️ استهلاك فصل الصيف (الجفاف)</span>
                <span class="text-3xl font-black text-amber-400 mt-2 block" id="kpi-summer-qty"><?php echo number_format($total_summer); ?> وحدة</span>
                <span class="text-xs text-amber-400/80 mt-1 block">أشهر الجفاف والبناء</span>
            </div>

            <!-- 3. Autumn / Rainy Season Qty -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(59, 130, 246, 0.4); --bar-color: #3b82f6; animation-delay:.3s">
                <span class="text-xs text-blue-400 font-bold block">🌧️ استهلاك فصل الخريف (الأمطار)</span>
                <span class="text-3xl font-black text-blue-400 mt-2 block" id="kpi-autumn-qty"><?php echo number_format($total_autumn); ?> وحدة</span>
                <span class="text-xs text-blue-400/80 mt-1 block">أشهر الأمطار والركود</span>
            </div>

            <!-- 4. Dominant Season -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(16, 185, 129, 0.4); --bar-color: #10b981; animation-delay:.4s">
                <span class="text-xs text-emerald-400 font-bold block">📊 الفصل الأكثر طلباً وإقبالاً</span>
                <span class="text-2xl font-black text-emerald-400 mt-2 block" id="kpi-dominant"><?php echo $dominant_season; ?></span>
                <span class="text-xs text-emerald-400/80 mt-1 block">أعلى معدل سحب واستمرار</span>
            </div>
        </div>

        <!-- 📈 MAIN CONTENT SECTION: TABLE (2/3) + CHART (1/3) FULL STRETCH 📈 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- 1. Seasonal Trends Table (2 Cols Wide) -->
            <div class="lg:col-span-2 anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.5s">
                <div>
                    <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-emerald-400"></i>
                            جدول الاستهلاك الموسمي للأصناف والكميات السنوية
                        </h3>
                        <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20">
                            محدث تلقائياً (DB + Local Storage)
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="seasonal-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الصنف</th>
                                    <th>استهلاك الصيف (الجفاف)</th>
                                    <th>استهلاك الخريف (الأمطار)</th>
                                    <th>إجمالي الاستهلاك السنوي</th>
                                    <th>الموسم السائد</th>
                                </tr>
                            </thead>
                            <tbody id="seasonal-table-tbody">
                                <?php if (empty($data)): ?>
                                <tr><td colspan="6" class="text-center py-6 text-slate-400 font-bold">جاري تحميل وتحليل كميات الطلب الموسمي... ⏳</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data as $i => $row):
                                        $s_qty = (float)($row['summer_dry_season_qty'] ?? 0);
                                        $a_qty = (float)($row['autumn_rainy_season_qty'] ?? 0);
                                        $tot_qty = (float)($row['total_annual_qty'] ?? 0);
                                        $is_summer = $s_qty >= $a_qty;
                                        $badge_cls = $is_summer ? 'bg-amber-900/40 text-amber-300 border-amber-500/40' : 'bg-blue-900/40 text-blue-300 border-blue-500/40';
                                        $label = $is_summer ? '☀️ الصيف' : '🌧️ الخريف';
                                    ?>
                                    <tr>
                                        <td class="text-slate-500 font-mono"><?php echo $i + 1; ?></td>
                                        <td class="font-bold text-slate-200"><?php echo htmlspecialchars($row['item_name']); ?></td>
                                        <td class="font-mono font-bold text-amber-400"><?php echo number_format($s_qty); ?> وحدة</td>
                                        <td class="font-mono font-bold text-blue-400"><?php echo number_format($a_qty); ?> وحدة</td>
                                        <td class="font-mono font-black text-emerald-400"><?php echo number_format($tot_qty); ?> وحدة</td>
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

            <!-- 2. Seasonal Comparison Chart (1 Col Wide) -->
            <div class="anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.6s">
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-emerald-400"></i>
                            مقارنة الطلب الموسمي للأصناف
                        </h3>
                    </div>

                    <div style="height: 380px; position: relative;">
                        <canvas id="seasonalChart"></canvas>
                    </div>
                </div>

                <div class="flex gap-4 mt-4 text-xs font-bold justify-center border-t border-slate-800 pt-3">
                    <span class="flex items-center gap-1.5 text-amber-400"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> استهلاك الصيف</span>
                    <span class="flex items-center gap-1.5 text-blue-400"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> استهلاك الخريف</span>
                </div>
            </div>

        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            آخر تحديث للحسابات: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026
        </div>

    </div>

    <!-- Client-side Sync & Chart Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let initialData = <?php echo json_encode($data, JSON_UNESCAPED_UNICODE); ?> || [];

        // Sync with LocalStorage sales
        const localSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
        let itemMap = {};

        initialData.forEach(r => {
            itemMap[r.item_name] = {
                item_name: r.item_name,
                summer_dry_season_qty: parseFloat(r.summer_dry_season_qty || 0),
                autumn_rainy_season_qty: parseFloat(r.autumn_rainy_season_qty || 0),
                total_annual_qty: parseFloat(r.total_annual_qty || 0)
            };
        });

        localSales.forEach(inv => {
            if (!inv.deleted) {
                const dateStr = inv.date || '';
                const month = dateStr ? new Date(dateStr).getMonth() + 1 : 0;
                (inv.items || []).forEach(line => {
                    const name = line.itemName || line.name || 'صنف غير معروف';
                    const qty = parseFloat(line.quantity || line.qty || 0);

                    if (!itemMap[name]) {
                        itemMap[name] = {
                            item_name: name,
                            summer_dry_season_qty: 0,
                            autumn_rainy_season_qty: 0,
                            total_annual_qty: 0
                        };
                    }

                    if ([6, 7, 8, 9].includes(month)) {
                        itemMap[name].autumn_rainy_season_qty += qty;
                    } else {
                        itemMap[name].summer_dry_season_qty += qty;
                    }
                    itemMap[name].total_annual_qty += qty;
                });
            }
        });

        let finalRows = Object.values(itemMap);
        finalRows.sort((a, b) => b.total_annual_qty - a.total_annual_qty);

        // Update KPIs
        let totalItems = finalRows.length;
        let totalSummer = finalRows.reduce((sum, x) => sum + x.summer_dry_season_qty, 0);
        let totalAutumn = finalRows.reduce((sum, x) => sum + x.autumn_rainy_season_qty, 0);
        let dominant = totalSummer >= totalAutumn ? 'الصيف (الجفاف)' : 'الخريف (المطر)';

        document.getElementById('kpi-total-items').textContent = totalItems + ' صنف';
        document.getElementById('kpi-summer-qty').textContent = totalSummer.toLocaleString() + ' وحدة';
        document.getElementById('kpi-autumn-qty').textContent = totalAutumn.toLocaleString() + ' وحدة';
        document.getElementById('kpi-dominant').textContent = dominant;

        // Render Table
        if (finalRows.length > 0) {
            let tbodyHtml = '';
            finalRows.forEach((r, idx) => {
                let isSummer = r.summer_dry_season_qty >= r.autumn_rainy_season_qty;
                let badgeCls = isSummer ? 'bg-amber-900/40 text-amber-300 border-amber-500/40' : 'bg-blue-900/40 text-blue-300 border-blue-500/40';
                let label = isSummer ? '☀️ الصيف' : '🌧️ الخريف';

                tbodyHtml += `
                    <tr>
                        <td class="text-slate-500 font-mono">${idx + 1}</td>
                        <td class="font-bold text-slate-200">${r.item_name}</td>
                        <td class="font-mono font-bold text-amber-400">${r.summer_dry_season_qty.toLocaleString()} وحدة</td>
                        <td class="font-mono font-bold text-blue-400">${r.autumn_rainy_season_qty.toLocaleString()} وحدة</td>
                        <td class="font-mono font-black text-emerald-400">${r.total_annual_qty.toLocaleString()} وحدة</td>
                        <td>
                            <span class="px-3 py-1 rounded-full text-xs font-black border inline-block ${badgeCls}">
                                ${label}
                            </span>
                        </td>
                    </tr>`;
            });
            document.getElementById('seasonal-table-tbody').innerHTML = tbodyHtml;
        } else {
            document.getElementById('seasonal-table-tbody').innerHTML = `<tr><td colspan="6" class="text-center py-6 text-slate-400 font-bold">لا توجد حركات استهلاك موسمية مسجلة في الوقت الحالي.</td></tr>`;
        }

        // Render Chart.js
        const ctx = document.getElementById('seasonalChart');
        if (ctx && finalRows.length > 0) {
            let topItems = finalRows.slice(0, 8);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topItems.map(x => x.item_name),
                    datasets: [
                        {
                            label: 'استهلاك الصيف (الجفاف)',
                            data: topItems.map(x => x.summer_dry_season_qty),
                            backgroundColor: 'rgba(245, 158, 11, 0.75)',
                            borderColor: '#f59e0b',
                            borderWidth: 1.5,
                            borderRadius: 6
                        },
                        {
                            label: 'استهلاك الخريف (الأمطار)',
                            data: topItems.map(x => x.autumn_rainy_season_qty),
                            backgroundColor: 'rgba(59, 130, 246, 0.75)',
                            borderColor: '#3b82f6',
                            borderWidth: 1.5,
                            borderRadius: 6
                        }
                    ]
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
        }
    });
    </script>
    <script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>
