<?php 
$page_title = "لوحة معلومات المخازن (Inventory Hub)"; 
$header_theme_class = "header-theme-inventory";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
})();
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - نظام مقاييس</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/components.css?v=<?php echo time(); ?>">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bg-page: #0b1120;
            --surface-card: rgba(15, 23, 42, 0.75);
            --glass-border: rgba(255, 255, 255, 0.08);
            --primary-accent: #38bdf8;
            --emerald-accent: #10b981;
            --amber-accent: #f59e0b;
            --rose-accent: #f43f5e;
            --purple-accent: #8b5cf6;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
        }
        body {
            font-family: 'Tajawal', sans-serif; background-color: var(--bg-page); color: var(--text-main);
            margin: 0; padding: 0; min-height: 100vh; overflow-x: hidden;
            background-image: radial-gradient(circle at 90% 10%, rgba(56, 189, 248, 0.04), transparent 40%),
                              radial-gradient(circle at 10% 90%, rgba(16, 185, 129, 0.04), transparent 40%);
        }
        .ambient-bg { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: 0; overflow: hidden; }
        .orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.25; animation: floatOrb 18s ease-in-out infinite alternate; }
        .orb-1 { width: 450px; height: 450px; background: #0284c7; top: -80px; left: -80px; }
        .orb-2 { width: 500px; height: 500px; background: #059669; bottom: -100px; right: -100px; animation-duration: 22s; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(60px, 40px) scale(1.08); } }

        .dashboard-container { position: relative; z-index: 10; padding: 15px; max-width: 1600px; margin: 0 auto; display: flex; flex-direction: column; min-height: calc(100vh - 30px); }
        
        .chart-container {
            background: var(--surface-card); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 16px;
            padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 20px; height: 48vh; min-height: 400px; display: flex; flex-direction: column;
        }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .chart-title { font-size: 1.3rem; font-weight: 900; color: #fff; margin: 0; display: flex; align-items: center; gap: 10px; }
        .chart-body { flex: 1; position: relative; width: 100%; }

        .hub-buttons-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px;
            margin-bottom: 30px;
        }
        .hub-btn {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.95));
            border: 1px solid var(--glass-border); border-radius: 16px; padding: 25px 15px;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px;
            color: #fff; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); text-decoration: none;
            position: relative; overflow: hidden;
        }
        .hub-btn::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px;
            background: var(--btn-color, #38bdf8); opacity: 0.8;
        }
        .hub-btn:hover {
            transform: translateY(-6px); border-color: var(--btn-color, #38bdf8);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 1));
        }
        .hub-btn .icon { font-size: 2.8rem; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4)); transition: 0.3s; }
        .hub-btn:hover .icon { transform: scale(1.1); }
        .hub-btn .title { font-size: 1.15rem; font-weight: 900; text-align: center; color: var(--btn-color, #38bdf8); }
        .hub-btn .desc { font-size: 0.8rem; color: var(--text-secondary); text-align: center; font-weight: 600; line-height: 1.4; }

    </style>
</head>
<body class="no-page-transition">
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
    
    <div class="dashboard-container">
        <?php include 'includes/header.php'; ?>

        <!-- Interactive Hub Buttons (Top) -->
        <div class="hub-buttons-grid">
            <a href="inventory-movement.php" class="hub-btn" style="--btn-color: #38bdf8;">
                <div class="icon">📦</div>
                <div class="title">حركات البضائع</div>
                <div class="desc">تفاصيل حركة البضاعة بالكميات والقيم</div>
            </a>
            <a href="item-ledger.php" class="hub-btn" style="--btn-color: #34d399;">
                <div class="icon">🏷️</div>
                <div class="title">حركة الأصناف</div>
                <div class="desc">كارت حركة الصنف التفصيلي والتراكمي</div>
            </a>
            <a href="warehouse-ledger.php" class="hub-btn" style="--btn-color: #fbbf24;">
                <div class="icon">🏢</div>
                <div class="title">حركة المخازن</div>
                <div class="desc">كشف حركة المستودعات والجرد الفرعي</div>
            </a>
            <a href="inventory-summary.php" class="hub-btn" style="--btn-color: #a78bfa;">
                <div class="icon">📊</div>
                <div class="title">ملخص الحركات</div>
                <div class="desc">نظرة عامة على الكميات والحركات المجمعة</div>
            </a>
            <a href="inventory-analytics.php" class="hub-btn" style="--btn-color: #f472b6;">
                <div class="icon">📈</div>
                <div class="title">التحليلات المرئية</div>
                <div class="desc">مؤشرات ورسوم بيانية وتقييم ABC</div>
            </a>
            <a href="inventory-stocktake.php" class="hub-btn" style="--btn-color: #f87171;">
                <div class="icon">🔍</div>
                <div class="title">الجرد الشامل</div>
                <div class="desc">مراجعة وتدقيق الكميات الفعلية</div>
            </a>
            <a href="inventory-adjustment.php" class="hub-btn" style="--btn-color: #f97316;">
                <div class="icon">⚖️</div>
                <div class="title">تسوية جردية</div>
                <div class="desc">إجراء تسويات الجرد لمعالجة العجز والزيادة</div>
            </a>
            <a href="inventory-ai.php" class="hub-btn" style="--btn-color: #22d3ee;">
                <div class="icon">🤖</div>
                <div class="title">الذكاء الاصطناعي</div>
                <div class="desc">توقعات الطلب وتحليل راكد المخزون</div>
            </a>
        </div>

        <!-- Massive Unified Chart (Bottom) -->
        <div class="chart-container">
            <div class="chart-header">
                <h2 class="chart-title">📊 المخطط الشامل لحركة المخزون (تراكمي)</h2>
                <div style="display:flex; gap:12px;">
                    <select id="chart-period" style="background:rgba(0,0,0,0.4); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); border-radius:10px; padding:8px 16px; font-family:inherit; font-weight:900; outline:none; cursor:pointer;" onchange="renderUnifiedChart()">
                        <option value="30">آخر 30 يوم</option>
                        <option value="90">آخر 3 شهور</option>
                        <option value="365">آخر سنة</option>
                        <option value="all">كل الأوقات</option>
                    </select>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="unifiedInventoryChart"></canvas>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
        let unifiedChart = null;

        document.addEventListener('DOMContentLoaded', () => {
            if (window.DB_STORE) {
                renderUnifiedChart();
            } else {
                document.addEventListener('DBReady', renderUnifiedChart);
                window.addEventListener('load', renderUnifiedChart);
            }
        });

        function getStorageData(key) {
            try {
                if (typeof window.getTenantKey === 'function') {
                    const tk = window.getTenantKey(key);
                    if (window.DB_STORE && window.DB_STORE[tk]) {
                        return typeof window.DB_STORE[tk] === 'string' ? JSON.parse(window.DB_STORE[tk]) : window.DB_STORE[tk];
                    }
                    const raw = localStorage.getItem(tk);
                    if (raw) return JSON.parse(raw);
                }
                const raw = localStorage.getItem(key);
                if (raw) return JSON.parse(raw);
            } catch(e){}
            return [];
        }

        function renderUnifiedChart() {
            const ctx = document.getElementById('unifiedInventoryChart');
            if (!ctx) return;

            const period = document.getElementById('chart-period').value;
            const now = new Date();
            let startDate = new Date(1970, 0, 1);
            if (period !== 'all') {
                startDate = new Date();
                startDate.setDate(now.getDate() - parseInt(period));
            }

            const purchases = getStorageData('erp_purchase_invoices_v1').filter(x => !x.deleted);
            const sales = getStorageData('erp_sales_invoices_v1').filter(x => !x.deleted && x.status !== 'draft');
            const transfers = getStorageData('erp_warehouse_transfers_v1').filter(x => !x.deleted && x.status !== 'cancelled');

            const dailyData = {};
            
            function addData(dateStr, type, qty, value) {
                if (!dailyData[dateStr]) {
                    dailyData[dateStr] = { inQty: 0, outQty: 0, inVal: 0, outVal: 0, trfQty: 0 };
                }
                if (type === 'in') { dailyData[dateStr].inQty += qty; dailyData[dateStr].inVal += value; }
                if (type === 'out') { dailyData[dateStr].outQty += qty; dailyData[dateStr].outVal += value; }
                if (type === 'trf') { dailyData[dateStr].trfQty += qty; }
            }

            purchases.forEach(inv => {
                const d = new Date(inv.date || inv.receiveDate || inv.createdAt || Date.now());
                if (d >= startDate) {
                    const dStr = d.toISOString().split('T')[0];
                    let totalQty = 0;
                    (inv.items || inv.lines || []).forEach(l => totalQty += parseFloat(l.quantity) || 0);
                    const val = parseFloat(inv.total) || parseFloat(inv.netTotal) || 0;
                    if (inv.isReturn) addData(dStr, 'out', totalQty, val);
                    else addData(dStr, 'in', totalQty, val);
                }
            });

            sales.forEach(inv => {
                const d = new Date(inv.date || inv.createdAt || Date.now());
                if (d >= startDate) {
                    const dStr = d.toISOString().split('T')[0];
                    let totalQty = 0;
                    (inv.items || []).forEach(l => totalQty += parseFloat(l.quantity) || 0);
                    const val = parseFloat(inv.total) || parseFloat(inv.netTotal) || 0;
                    if (inv.isReturn) addData(dStr, 'in', totalQty, val);
                    else addData(dStr, 'out', totalQty, val);
                }
            });

            transfers.forEach(tr => {
                const d = new Date(tr.date || tr.createdAt || Date.now());
                if (d >= startDate) {
                    const dStr = d.toISOString().split('T')[0];
                    let totalQty = 0;
                    (tr.items || []).forEach(l => totalQty += parseFloat(l.quantity) || 0);
                    addData(dStr, 'trf', totalQty, 0);
                }
            });

            const sortedDates = Object.keys(dailyData).sort();
            
            const labels = sortedDates.map(d => {
                const parts = d.split('-');
                return parts[2] + '/' + parts[1];
            });

            const inData = sortedDates.map(d => dailyData[d].inQty);
            const outData = sortedDates.map(d => dailyData[d].outQty);
            const trfData = sortedDates.map(d => dailyData[d].trfQty);
            const valData = sortedDates.map(d => dailyData[d].inVal - dailyData[d].outVal);

            if (unifiedChart) {
                unifiedChart.destroy();
            }

            Chart.defaults.color = '#94a3b8';
            Chart.defaults.font.family = "'Tajawal', sans-serif";

            unifiedChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'الوارد (كمية)',
                            data: inData,
                            type: 'bar',
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'المنصرف (كمية)',
                            data: outData,
                            type: 'bar',
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: '#ef4444',
                            borderWidth: 1,
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'التحويلات (كمية)',
                            data: trfData,
                            type: 'bar',
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            borderColor: '#f59e0b',
                            borderWidth: 1,
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'صافي القيمة المالية (ريال)',
                            data: valData,
                            type: 'line',
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56, 189, 248, 0.15)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#0f172a',
                            pointBorderColor: '#38bdf8',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { color: '#e2e8f0', font: { size: 13, weight: 'bold' }, padding: 20 } },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleFont: { size: 14, family: "'Tajawal', sans-serif", weight: 'bold' },
                            bodyFont: { size: 13, family: "'Tajawal', sans-serif" },
                            padding: 12, borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1, rtl: true, textDirection: 'rtl', intersect: false
                        }
                    },
                    scales: {
                        x: { grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false }, ticks: { color: '#cbd5e1', font: { weight: 'bold' } } },
                        y: { type: 'linear', display: true, position: 'right', grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false }, title: { display: true, text: 'الكميات المتأثرة (وحدة)', color: '#cbd5e1', font: { weight: 'bold' } }, ticks: { color: '#94a3b8' } },
                        y1: { type: 'linear', display: true, position: 'left', grid: { drawOnChartArea: false }, title: { display: true, text: 'حركة القيمة المالية', color: '#38bdf8', font: { weight: 'bold' } }, ticks: { color: '#38bdf8' } }
                    }
                }
            });
        }
    </script>
</body>
</html>
