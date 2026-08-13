<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - احترافية التسعير وانحراف السعر V2
// تم التحديث لاستغلال العرض الكامل للشاشة باحترافية وتناسق تام
// ====================================================================
$page_title = 'احترافية التسعير وانحراف السعر';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

$data = getPriceVarianceData($pdo);
$is_mock_data = false;

// حساب المؤشرات الأولية من خادم البيانات
$total_flagged = count($data);
$avg_margin = $total_flagged > 0 ? round(array_sum(array_column($data, 'profit_margin_percentage')) / $total_flagged, 2) : 0;
$min_margin_row = !empty($data) ? $data[0] : null;

$critical_count = 0; $warning_count = 0;
foreach ($data as $r) {
    if (($r['profit_margin_percentage'] ?? 0) < 5) $critical_count++;
    else $warning_count++;
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
    <title>احترافية التسعير وانحراف السعر | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-dark: #04020a;
            --panel-bg: rgba(14, 8, 28, 0.75);
            --border-neon: rgba(6, 182, 212, 0.25);
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
        .price-variance-wrapper {
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
            box-shadow: 0 15px 35px var(--border-glow, rgba(6, 182, 212, 0.25));
        }

        .stat-card-v2::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, var(--bar-color, #06b6d4), transparent);
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

        .pv-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .pv-table th {
            background: rgba(30, 41, 59, 0.8);
            color: var(--text-main);
            padding: 14px 16px;
            font-weight: 900;
            font-size: 0.9rem;
            text-align: right;
            border-bottom: 2.5px solid rgba(6, 182, 212, 0.3);
        }

        :root.light-theme .pv-table th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border-bottom: 3px solid #000000 !important;
        }

        .pv-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            font-weight: 700;
            font-size: 0.9rem;
        }

        :root.light-theme .pv-table td {
            border-bottom: 1.5px solid #e2e8f0 !important;
            color: #000000 !important;
        }

        .pv-table tr:hover td {
            background: rgba(6, 182, 212, 0.06);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim { animation: fadeInUp 0.5s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .price-variance-wrapper { padding: 0 !important; }
            .glass-panel-v2, .stat-card-v2 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="price-variance-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-cyan-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="smart_reports_hub.php" onclick="window.location.href='smart_reports_hub.php'; return false;" class="inline-flex items-center gap-2 text-cyan-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع لمركز التقارير المحاسبية المتقدمة V2
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-tags text-cyan-400"></i>
                    تقرير احترافية التسعير وانحراف أسعار البيع والتكلفة
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">مراقبة دقيقة لهوامش ربح الفواتير، تدقيق الفرق بين سعر البيع الفعلي وتكلفة المخزون، وكشف الحالات الحرجة.</p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print();" class="bg-cyan-600 hover:bg-cyan-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة التقرير
                </button>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY CARDS (Full Width 4 Grid) 📊 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- 1. Total Analyzed Transactions -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(6, 182, 212, 0.4); --bar-color: #06b6d4; animation-delay:.1s">
                <span class="text-xs text-cyan-400 font-bold block">🧾 إجمالي العمليات المدققة</span>
                <span class="text-3xl font-black text-cyan-400 mt-2 block" id="kpi-total-trx"><?php echo $total_flagged; ?></span>
                <span class="text-xs text-slate-500 mt-1 block">عمليات بيع وتكلفة مراجعة</span>
            </div>

            <!-- 2. Avg Profit Margin -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(16, 185, 129, 0.4); --bar-color: #10b981; animation-delay:.2s">
                <span class="text-xs text-emerald-400 font-bold block">📈 متوسط هامش الربح المحقق</span>
                <span class="text-3xl font-black text-emerald-400 mt-2 block" id="kpi-avg-margin">%<?php echo $avg_margin; ?></span>
                <span class="text-xs text-emerald-400/80 mt-1 block">هامش الأرباح الإجمالي</span>
            </div>

            <!-- 3. Critical Low Margin Items -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(239, 68, 68, 0.4); --bar-color: #ef4444; animation-delay:.3s">
                <span class="text-xs text-rose-400 font-bold block">🚨 عمليات حادة (هامش أقل من 5%)</span>
                <span class="text-3xl font-black text-rose-400 mt-2 block" id="kpi-critical"><?php echo $critical_count; ?></span>
                <span class="text-xs text-rose-400/80 mt-1 block">تحتاج مراجعة فورية لسياسة السعر</span>
            </div>

            <!-- 4. Lowest Margin Recorded -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(245, 158, 11, 0.4); --bar-color: #f59e0b; animation-delay:.4s">
                <span class="text-xs text-amber-400 font-bold block">⚖️ أدنى هامش مسجل</span>
                <span class="text-3xl font-black text-amber-400 mt-2 block" id="kpi-min-margin">
                    %<?php echo $min_margin_row ? $min_margin_row['profit_margin_percentage'] : '0'; ?>
                </span>
                <span class="text-xs text-slate-500 mt-1 block" id="kpi-min-item">
                    <?php echo $min_margin_row ? htmlspecialchars($min_margin_row['item_name']) : 'لا توجد انحرافات حادة'; ?>
                </span>
            </div>
        </div>

        <!-- 📈 MAIN CONTENT SECTION: TABLE (2/3) + CHART (1/3) FULL STRETCH 📈 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- 1. Price Variance Table (2 Cols Wide) -->
            <div class="lg:col-span-2 anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.5s">
                <div>
                    <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-cyan-400"></i>
                            كشف تدقيق انحراف أسعار الفواتير وهامش التكلفة والربح
                        </h3>
                        <span class="text-xs font-bold text-cyan-400 bg-cyan-500/10 px-3 py-1 rounded-lg border border-cyan-500/20">
                            محدث تلقائياً (DB + Local Storage)
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="pv-table">
                            <thead>
                                <tr>
                                    <th>رقم الفاتورة / المرجع</th>
                                    <th>اسم العميل</th>
                                    <th>اسم الصنف</th>
                                    <th>سعر البيع الفعلي</th>
                                    <th>تكلفة المخزون</th>
                                    <th>هامش الربح الوحدة</th>
                                    <th>نسبة الهامش %</th>
                                </tr>
                            </thead>
                            <tbody id="pv-table-tbody">
                                <!-- Rendered via PHP & Client-side Sync -->
                                <?php if (empty($data)): ?>
                                <tr><td colspan="7" class="text-center py-6 text-slate-400 font-bold">جاري كشف وانحرافات أسعار البيع والتكلفة... ⏳</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data as $row):
                                        $margin_pct = (float)($row['profit_margin_percentage'] ?? 0);
                                        if ($margin_pct < 5) {
                                            $badge_cls = 'bg-rose-900/40 text-rose-300 border-rose-500/40';
                                            $status_icon = '🚨 حرج جداً';
                                        } elseif ($margin_pct < 15) {
                                            $badge_cls = 'bg-amber-900/40 text-amber-300 border-amber-500/40';
                                            $status_icon = '⚠️ منخفض';
                                        } else {
                                            $badge_cls = 'bg-emerald-900/40 text-emerald-300 border-emerald-500/40';
                                            $status_icon = '✓ جيد جداً';
                                        }
                                    ?>
                                    <tr>
                                        <td class="font-mono text-cyan-400 font-bold">#<?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                        <td class="font-bold text-slate-200"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td class="font-bold text-slate-100"><?php echo htmlspecialchars($row['item_name']); ?></td>
                                        <td class="font-mono text-emerald-400 font-bold"><?php echo number_format($row['actual_selling_price'], 2); ?> <span class="text-xs text-slate-400"><?php echo $selected_currency; ?></span></td>
                                        <td class="font-mono text-slate-300"><?php echo number_format($row['stock_cost_price'], 2); ?> <span class="text-xs text-slate-500"><?php echo $selected_currency; ?></span></td>
                                        <td class="font-mono font-bold <?php echo $row['unit_margin'] < 0 ? 'text-rose-400' : 'text-emerald-400'; ?>">
                                            <?php echo number_format($row['unit_margin'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="px-3 py-1 rounded-full text-xs font-black border inline-block <?php echo $badge_cls; ?>">
                                                %<?php echo number_format($margin_pct, 1); ?> (<?php echo $status_icon; ?>)
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

            <!-- 2. Price Variance Distribution Chart (1 Col Wide) -->
            <div class="anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.6s">
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-cyan-400"></i>
                            تحليل توزع هوامش أرباح الأصناف
                        </h3>
                    </div>

                    <div style="height: 380px; position: relative;">
                        <canvas id="pvChart"></canvas>
                    </div>
                </div>

                <div class="flex gap-4 mt-4 text-xs font-bold justify-center border-t border-slate-800 pt-3">
                    <span class="flex items-center gap-1.5 text-emerald-400"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> هامش ممتاز (>15%)</span>
                    <span class="flex items-center gap-1.5 text-amber-400"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> هامش متوسط (5-15%)</span>
                    <span class="flex items-center gap-1.5 text-rose-400"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span> هامش حرج (<5%)</span>
                </div>
            </div>

        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            آخر تحديث للحسابات: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026
        </div>

    </div>

    <!-- Client-side Sync & Chart Setup -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initial PHP Data
        let initialData = <?php echo json_encode($data, JSON_UNESCAPED_UNICODE); ?> || [];

        // Combine DB Data with LocalStorage Sales Invoices Data
        const localSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
        const localItems = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
        
        let localItemCostsMap = {};
        localItems.forEach(it => {
            const cost = parseFloat(it.costPrice || it.buyPrice || it.cost || 0);
            if(it.id && cost > 0) localItemCostsMap[it.id] = cost;
            if(it.name && cost > 0) localItemCostsMap[it.name] = cost;
        });

        let combinedMap = {};
        initialData.forEach(r => {
            let key = (r.transaction_id || '') + '_' + (r.item_name || '');
            combinedMap[key] = r;
        });

        localSales.forEach(inv => {
            if(!inv.deleted) {
                const custName = inv.customerName || inv.customer || 'عميل عام';
                (inv.items || []).forEach(item => {
                    const itemName = item.itemName || item.name || 'صنف غير معروف';
                    const itemId = item.itemId || item.id || '';
                    const price = parseFloat(item.price || 0);
                    let cost = parseFloat(item.cost || item.actualCost || 0);
                    if(cost <= 0 && localItemCostsMap[itemId]) cost = localItemCostsMap[itemId];
                    if(cost <= 0 && localItemCostsMap[itemName]) cost = localItemCostsMap[itemName];
                    if(cost <= 0 && price > 0) cost = price * 0.75;

                    if(price > 0 && cost > 0) {
                        const margin = price - cost;
                        const margin_pct = Math.round((margin / cost) * 1000) / 10;
                        const key = (inv.invoiceNo || inv.id || 'INV') + '_' + itemName;
                        combinedMap[key] = {
                            transaction_id: inv.invoiceNo || inv.id || 'INV-TRX',
                            customer_name: custName,
                            item_name: itemName,
                            actual_selling_price: price,
                            stock_cost_price: cost,
                            unit_margin: margin,
                            profit_margin_percentage: margin_pct
                        };
                    }
                });
            }
        });

        let finalRows = Object.values(combinedMap);
        finalRows.sort((a, b) => a.profit_margin_percentage - b.profit_margin_percentage);

        // Update KPIs dynamically
        let totalCount = finalRows.length;
        let criticalCount = 0;
        let warningCount = 0;
        let sumMarginPct = 0;

        finalRows.forEach(r => {
            sumMarginPct += (r.profit_margin_percentage || 0);
            if (r.profit_margin_percentage < 5) criticalCount++;
            else warningCount++;
        });

        let avgMargin = totalCount > 0 ? (sumMarginPct / totalCount).toFixed(1) : '0';

        document.getElementById('kpi-total-trx').textContent = totalCount;
        document.getElementById('kpi-avg-margin').textContent = '%' + avgMargin;
        document.getElementById('kpi-critical').textContent = criticalCount;

        if(finalRows.length > 0) {
            document.getElementById('kpi-min-margin').textContent = '%' + finalRows[0].profit_margin_percentage;
            document.getElementById('kpi-min-item').textContent = finalRows[0].item_name;
        }

        // Render Table Rows
        if(finalRows.length > 0) {
            let tbodyHtml = '';
            finalRows.forEach(r => {
                let marginPct = parseFloat(r.profit_margin_percentage || 0);
                let badgeCls = 'bg-emerald-900/40 text-emerald-300 border-emerald-500/40';
                let statusIcon = '✓ جيد جداً';

                if (marginPct < 5) {
                    badgeCls = 'bg-rose-900/40 text-rose-300 border-rose-500/40';
                    statusIcon = '🚨 حرج جداً';
                } else if (marginPct < 15) {
                    badgeCls = 'bg-amber-900/40 text-amber-300 border-amber-500/40';
                    statusIcon = '⚠️ منخفض';
                }

                tbodyHtml += `
                    <tr>
                        <td class="font-mono text-cyan-400 font-bold">#${r.transaction_id}</td>
                        <td class="font-bold text-slate-200">${r.customer_name}</td>
                        <td class="font-bold text-slate-100">${r.item_name}</td>
                        <td class="font-mono text-emerald-400 font-bold">${parseFloat(r.actual_selling_price).toLocaleString()} ${"<?php echo $selected_currency; ?>"}</td>
                        <td class="font-mono text-slate-300">${parseFloat(r.stock_cost_price).toLocaleString()} ${"<?php echo $selected_currency; ?>"}</td>
                        <td class="font-mono font-bold ${r.unit_margin < 0 ? 'text-rose-400' : 'text-emerald-400'}">
                            ${parseFloat(r.unit_margin).toLocaleString()}
                        </td>
                        <td>
                            <span class="px-3 py-1 rounded-full text-xs font-black border inline-block ${badgeCls}">
                                %${marginPct.toFixed(1)} (${statusIcon})
                            </span>
                        </td>
                    </tr>`;
            });
            document.getElementById('pv-table-tbody').innerHTML = tbodyHtml;
        } else {
            document.getElementById('pv-table-tbody').innerHTML = `<tr><td colspan="7" class="text-center py-6 text-slate-400 font-bold">لا توجد عمليات بيع أو انحرافات مسجلة بجدول البيانات في الوقت الحالي.</td></tr>`;
        }

        // Render Chart.js
        const ctx = document.getElementById('pvChart');
        if (ctx && finalRows.length > 0) {
            let topItems = finalRows.slice(0, 10);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topItems.map(x => x.item_name),
                    datasets: [{
                        label: 'نسبة هامش الربح %',
                        data: topItems.map(x => x.profit_margin_percentage),
                        backgroundColor: topItems.map(x => x.profit_margin_percentage < 5 ? 'rgba(239, 68, 68, 0.75)' : (x.profit_margin_percentage < 15 ? 'rgba(245, 158, 11, 0.75)' : 'rgba(16, 185, 129, 0.75)')),
                        borderColor: topItems.map(x => x.profit_margin_percentage < 5 ? '#ef4444' : (x.profit_margin_percentage < 15 ? '#f59e0b' : '#10b981')),
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
        }
    });
    </script>
    <script defer src="js/app.js"></script>
</body>
</html>
