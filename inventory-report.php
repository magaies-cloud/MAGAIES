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

        .dashboard-container { position: relative; z-index: 10; padding: 15px; max-width: 1650px; margin: 0 auto; display: flex; flex-direction: column; min-height: calc(100vh - 30px); }
        
        /* ── Compact Hub Buttons (Reduced height by ~25%) ── */
        .hub-buttons-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(185px, 1fr)); gap: 12px;
            margin-bottom: 16px;
        }
        .hub-btn {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.9));
            border: 1px solid var(--glass-border); border-radius: 14px; padding: 12px 14px;
            min-height: 86px;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
            color: #fff; cursor: pointer; transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
            backdrop-filter: blur(14px); box-shadow: 0 6px 20px rgba(0,0,0,0.25); text-decoration: none;
            position: relative; overflow: hidden;
        }
        .hub-btn::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3.5px;
            background: var(--btn-color, #38bdf8); opacity: 0.85; transition: 0.3s;
        }
        .hub-btn:hover {
            transform: translateY(-4px); border-color: var(--btn-color, #38bdf8);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4), 0 0 20px rgba(56,189,248,0.1) inset;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 1));
        }
        .hub-btn:hover::before { height: 4.5px; box-shadow: 0 0 10px var(--btn-color); }
        .hub-btn .icon { font-size: 2rem; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.3)); transition: 0.3s; line-height: 1; }
        .hub-btn:hover .icon { transform: scale(1.15); }
        .hub-btn .title { font-size: 0.98rem; font-weight: 900; text-align: center; color: var(--btn-color, #38bdf8); margin: 0; line-height: 1.2; }
        .hub-btn .desc { font-size: 0.72rem; color: var(--text-secondary); text-align: center; font-weight: 600; line-height: 1.3; margin: 0; }

        /* ── Comprehensive Analytical Visual Chart Hub ── */
        .chart-container {
            background: var(--surface-card); backdrop-filter: blur(25px);
            border: 1.5px solid rgba(255, 255, 255, 0.1); border-radius: 20px;
            padding: 20px 24px; box-shadow: 0 15px 40px rgba(0,0,0,0.45);
            margin-bottom: 20px; min-height: 560px; display: flex; flex-direction: column; gap: 16px;
            flex: 1;
        }
        
        .chart-top-bar {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 14px;
        }
        .chart-title-box { display: flex; flex-direction: column; gap: 4px; }
        .chart-title { font-size: 1.3rem; font-weight: 900; color: #fff; margin: 0; display: flex; align-items: center; gap: 10px; }
        .chart-subtitle { font-size: 0.82rem; color: #94a3b8; font-weight: 600; }
        
        /* Interactive Chart Controls & View Switcher */
        .chart-controls { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        
        .view-pill-group {
            display: flex; background: rgba(0, 0, 0, 0.4); padding: 4px; border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08); gap: 4px;
        }
        .view-pill {
            background: transparent; border: none; color: #94a3b8; padding: 6px 14px;
            border-radius: 8px; font-family: inherit; font-size: 0.85rem; font-weight: 800;
            cursor: pointer; transition: all 0.25s ease; display: flex; align-items: center; gap: 6px;
        }
        .view-pill:hover { color: #fff; background: rgba(255, 255, 255, 0.06); }
        .view-pill.active {
            background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }

        .filter-select {
            background: rgba(15, 23, 42, 0.85); color: #38bdf8; border: 1px solid rgba(56,189,248,0.35);
            border-radius: 10px; padding: 7px 14px; font-family: inherit; font-weight: 800; font-size: 0.85rem;
            outline: none; cursor: pointer; transition: 0.3s;
        }
        .filter-select:hover, .filter-select:focus { border-color: #38bdf8; box-shadow: 0 0 10px rgba(56,189,248,0.25); }

        /* KPI Quick Overview Strip inside Chart */
        .chart-kpi-strip {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px;
        }
        .chart-kpi-item {
            background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;
        }
        .chart-kpi-item .label { font-size: 0.75rem; color: #94a3b8; font-weight: 700; }
        .chart-kpi-item .val { font-size: 1.15rem; font-weight: 900; font-family: monospace; letter-spacing: 0.5px; }

        .chart-body { flex: 1; position: relative; width: 100%; min-height: 380px; }

        /* Light Theme Overrides */
        :root.light-theme .chart-container { background: #ffffff !important; border-color: rgba(0,0,0,0.08) !important; box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important; }
        :root.light-theme .chart-title { color: #0f172a !important; }
        :root.light-theme .chart-subtitle { color: #64748b !important; }
        :root.light-theme .chart-kpi-item { background: #f8fafc !important; border-color: rgba(0,0,0,0.06) !important; }
        :root.light-theme .hub-btn { background: #ffffff !important; border-color: rgba(0,0,0,0.08) !important; box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important; }
        :root.light-theme .hub-btn .desc { color: #64748b !important; }
    </style>
</head>
<body class="no-page-transition">
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
    
    <div class="dashboard-container">
        <?php include 'includes/header.php'; ?>

        <!-- Interactive Compact Hub Buttons (Top - Reduced Height by ~25%) -->
        <div class="hub-buttons-grid">
            <a href="inventory-movement.php" class="hub-btn" style="--btn-color: #38bdf8;">
                <div class="icon">📦</div>
                <div class="title">حركات البضائع</div>
                <div class="desc">تفاصيل الكميات والقيم</div>
            </a>
            <a href="item-ledger.php" class="hub-btn" style="--btn-color: #34d399;">
                <div class="icon">🏷️</div>
                <div class="title">حركة الأصناف</div>
                <div class="desc">كارت الصنف التفصيلي</div>
            </a>
            <a href="warehouse-ledger.php" class="hub-btn" style="--btn-color: #fbbf24;">
                <div class="icon">🏢</div>
                <div class="title">حركة المخازن</div>
                <div class="desc">كشف المستودعات والجرد</div>
            </a>
            <a href="inventory-summary.php" class="hub-btn" style="--btn-color: #a78bfa;">
                <div class="icon">📊</div>
                <div class="title">ملخص الحركات</div>
                <div class="desc">نظرة عامة على الكميات</div>
            </a>
            <a href="inventory-analytics.php" class="hub-btn" style="--btn-color: #f472b6;">
                <div class="icon">📈</div>
                <div class="title">التحليلات المرئية</div>
                <div class="desc">مؤشرات ورسوم وتقييم ABC</div>
            </a>
            <a href="inventory-stocktake.php" class="hub-btn" style="--btn-color: #f87171;">
                <div class="icon">🔍</div>
                <div class="title">الجرد الشامل</div>
                <div class="desc">تدقيق الكميات الفعلية</div>
            </a>
            <a href="inventory-adjustment.php" class="hub-btn" style="--btn-color: #f97316;">
                <div class="icon">⚖️</div>
                <div class="title">تسوية جردية</div>
                <div class="desc">معالجة العجز والزيادة</div>
            </a>
            <a href="smart_inventory_report.php" class="hub-btn" style="--btn-color: #22d3ee;">
                <div class="icon">🤖</div>
                <div class="title">تقارير المخزن الذكية والـ AI</div>
                <div class="desc">الـ 20 تقرير ومستشعر AI</div>
            </a>
        </div>

        <!-- ── Massive Comprehensive Visual Analytical Chart Hub ── -->
        <div class="chart-container">
            <div class="chart-top-bar">
                <div class="chart-title-box">
                    <h2 class="chart-title" id="chart-main-heading">
                        <span>📊</span> المخطط التحليلي الشامل لحركات المخزون والأصناف (تراكمي)
                    </h2>
                    <div class="chart-subtitle">رؤية كاملة وشاملة لأرصدة المخازن، تدفقات الوارد والصادر، والقيم المالية الحية</div>
                </div>

                <!-- Controls & View Mode Switcher -->
                <div class="chart-controls">
                    <div class="view-pill-group">
                        <button class="view-pill active" onclick="switchChartView('trend', this)">
                            <span>📈</span> المسار التراكمي الشامل
                        </button>
                        <button class="view-pill" onclick="switchChartView('flow', this)">
                            <span>🔄</span> تدفق الوارد والصادر
                        </button>
                        <button class="view-pill" onclick="switchChartView('warehouses', this)">
                            <span>🏢</span> كميات المخازن
                        </button>
                        <button class="view-pill" onclick="switchChartView('top_items', this)">
                            <span>🏆</span> أعلى الأصناف
                        </button>
                    </div>

                    <select id="chart-warehouse-filter" class="filter-select" onchange="renderUnifiedChart()">
                        <option value="ALL">🏢 جميع المخازن</option>
                    </select>

                    <select id="chart-period" class="filter-select" onchange="renderUnifiedChart()">
                        <option value="30" selected>آخر 30 يوم</option>
                        <option value="7">آخر 7 أيام</option>
                        <option value="90">آخر 3 شهور</option>
                        <option value="365">آخر سنة</option>
                        <option value="all">كل الأوقات</option>
                    </select>
                </div>
            </div>

            <!-- KPI Quick Overview Strip inside Chart Container -->
            <div class="chart-kpi-strip">
                <div class="chart-kpi-item" style="border-right: 3px solid #38bdf8;">
                    <div>
                        <div class="label">المخزون التراكمي الكلي</div>
                        <div class="val text-cyan-400" id="kpi-chart-stock" style="color:#38bdf8;">0 <small style="font-size:0.75rem;">وحدة</small></div>
                    </div>
                    <span style="font-size:1.5rem;">📦</span>
                </div>
                <div class="chart-kpi-item" style="border-right: 3px solid #10b981;">
                    <div>
                        <div class="label">إجمالي الوارد (دخول)</div>
                        <div class="val" id="kpi-chart-in" style="color:#10b981;">0 <small style="font-size:0.75rem;">وحدة</small></div>
                    </div>
                    <span style="font-size:1.5rem;">📥</span>
                </div>
                <div class="chart-kpi-item" style="border-right: 3px solid #ef4444;">
                    <div>
                        <div class="label">إجمالي المنصرف (مبيعات)</div>
                        <div class="val" id="kpi-chart-out" style="color:#ef4444;">0 <small style="font-size:0.75rem;">وحدة</small></div>
                    </div>
                    <span style="font-size:1.5rem;">📤</span>
                </div>
                <div class="chart-kpi-item" style="border-right: 3px solid #fbbf24;">
                    <div>
                        <div class="label">القيمة المالية التراكمية</div>
                        <div class="val" id="kpi-chart-val" style="color:#fbbf24;">0 <small style="font-size:0.75rem;">SDG</small></div>
                    </div>
                    <span style="font-size:1.5rem;">💰</span>
                </div>
                <div class="chart-kpi-item" style="border-right: 3px solid #a855f7;">
                    <div>
                        <div class="label">الأصناف والمخازن النشطة</div>
                        <div class="val" id="kpi-chart-counts" style="color:#a855f7;">0 <small style="font-size:0.75rem;">صنف</small></div>
                    </div>
                    <span style="font-size:1.5rem;">📊</span>
                </div>
            </div>

            <!-- Canvas -->
            <div class="chart-body">
                <canvas id="unifiedInventoryChart"></canvas>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
        let unifiedChart = null;
        let currentViewMode = 'trend';

        document.addEventListener('DOMContentLoaded', () => {
            populateWarehouseFilter();
            if (window.DB_STORE) {
                renderUnifiedChart();
            } else {
                document.addEventListener('DBReady', renderUnifiedChart);
                window.addEventListener('load', renderUnifiedChart);
            }
        });

        function switchChartView(mode, btn) {
            currentViewMode = mode;
            document.querySelectorAll('.view-pill').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            renderUnifiedChart();
        }

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

        function populateWarehouseFilter() {
            const select = document.getElementById('chart-warehouse-filter');
            if (!select) return;
            const warehouses = getStorageData('erp_warehouses_v1') || [];
            warehouses.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.id || w.name;
                opt.textContent = '🏢 ' + (w.name || w.id);
                select.appendChild(opt);
            });
        }

        function renderUnifiedChart() {
            const ctx = document.getElementById('unifiedInventoryChart');
            if (!ctx) return;

            const period = document.getElementById('chart-period')?.value || '30';
            const selectedWh = document.getElementById('chart-warehouse-filter')?.value || 'ALL';
            const now = new Date();
            let startDate = new Date(1970, 0, 1);
            if (period !== 'all') {
                startDate = new Date();
                startDate.setDate(now.getDate() - parseInt(period));
            }

            const items = getStorageData('erp_items_v1') || [];
            const purchases = (getStorageData('erp_purchase_invoices_v1') || []).filter(x => !x.deleted);
            const sales = (getStorageData('erp_sales_invoices_v1') || []).filter(x => !x.deleted && x.status !== 'draft');
            const transfers = (getStorageData('erp_warehouse_transfers_v1') || []).filter(x => !x.deleted && x.status !== 'cancelled');

            // 1. Daily Movements Calculation
            const dailyData = {};
            let totalIn = 0;
            let totalOut = 0;
            let totalValIn = 0;
            let totalValOut = 0;

            function addData(dateStr, type, qty, value) {
                if (!dailyData[dateStr]) {
                    dailyData[dateStr] = { inQty: 0, outQty: 0, inVal: 0, outVal: 0, trfQty: 0 };
                }
                if (type === 'in') { dailyData[dateStr].inQty += qty; dailyData[dateStr].inVal += value; totalIn += qty; totalValIn += value; }
                if (type === 'out') { dailyData[dateStr].outQty += qty; dailyData[dateStr].outVal += value; totalOut += qty; totalValOut += value; }
                if (type === 'trf') { dailyData[dateStr].trfQty += qty; }
            }

            purchases.forEach(inv => {
                if (selectedWh !== 'ALL' && inv.warehouseId && inv.warehouseId !== selectedWh && inv.warehouse !== selectedWh) return;
                const d = new Date(inv.date || inv.receiveDate || inv.createdAt || Date.now());
                if (d >= startDate) {
                    const dStr = d.toISOString().split('T')[0];
                    let totalQty = 0;
                    (inv.items || inv.lines || []).forEach(l => totalQty += (parseFloat(l.quantity) || 0));
                    const val = parseFloat(inv.total) || parseFloat(inv.netTotal) || 0;
                    if (inv.isReturn) addData(dStr, 'out', totalQty, val);
                    else addData(dStr, 'in', totalQty, val);
                }
            });

            sales.forEach(inv => {
                if (selectedWh !== 'ALL' && inv.warehouseId && inv.warehouseId !== selectedWh && inv.warehouse !== selectedWh) return;
                const d = new Date(inv.date || inv.createdAt || Date.now());
                if (d >= startDate) {
                    const dStr = d.toISOString().split('T')[0];
                    let totalQty = 0;
                    (inv.items || []).forEach(l => totalQty += (parseFloat(l.quantity) || 0));
                    const val = parseFloat(inv.total) || parseFloat(inv.netTotal) || 0;
                    if (inv.isReturn) addData(dStr, 'in', totalQty, val);
                    else addData(dStr, 'out', totalQty, val);
                }
            });

            transfers.forEach(tr => {
                if (selectedWh !== 'ALL' && tr.fromWarehouse !== selectedWh && tr.toWarehouse !== selectedWh) return;
                const d = new Date(tr.date || tr.createdAt || Date.now());
                if (d >= startDate) {
                    const dStr = d.toISOString().split('T')[0];
                    let totalQty = 0;
                    (tr.items || []).forEach(l => totalQty += (parseFloat(l.quantity) || 0));
                    addData(dStr, 'trf', totalQty, 0);
                }
            });

            // ?? Calculate Accurate Real-time Stock per Warehouse
            let actualStockMap = {};
            let actualValMap = {};
            
            // Initial opening balances
            items.forEach(it => {
                let openQty = parseFloat(it.openingBalance || it.opening_balance || 0);
                let wh = (it.openingBalanceWarehouseId || it.warehouseId || it.warehouse || 'ALL');
                let cost = parseFloat(it.cost || it.avgCost || it.costPrice || it.price || 0);
                
                if (!actualStockMap[wh]) { actualStockMap[wh] = 0; actualValMap[wh] = 0; }
                actualStockMap[wh] += openQty;
                actualValMap[wh] += (openQty * cost);
            });
            
            // Apply Purchases
            purchases.forEach(inv => {
                let hdrWh = inv.warehouseId || inv.warehouse || 'ALL';
                (inv.items || inv.lines || []).forEach(line => {
                    let wh = line.warehouseId || hdrWh;
                    let qty = parseFloat(line.quantity || 0) * (parseFloat(line.factor || line.conversionFactor || 1));
                    let val = qty * parseFloat(line.cost || line.price || 0);
                    if (!actualStockMap[wh]) { actualStockMap[wh] = 0; actualValMap[wh] = 0; }
                    
                    if (inv.isReturn) { actualStockMap[wh] -= qty; actualValMap[wh] -= val; }
                    else { actualStockMap[wh] += qty; actualValMap[wh] += val; }
                });
            });
            
            // Apply Sales
            sales.forEach(inv => {
                let hdrWh = inv.warehouseId || inv.warehouse || 'ALL';
                (inv.items || []).forEach(line => {
                    let wh = line.warehouseId || hdrWh;
                    let qty = parseFloat(line.quantity || 0) * (parseFloat(line.factor || line.conversionFactor || 1));
                    let val = qty * parseFloat(line.cost || line.price || 0);
                    if (!actualStockMap[wh]) { actualStockMap[wh] = 0; actualValMap[wh] = 0; }
                    
                    if (inv.isReturn) { actualStockMap[wh] += qty; actualValMap[wh] += val; }
                    else { actualStockMap[wh] -= qty; actualValMap[wh] -= val; }
                });
            });
            
            // Apply Transfers
            transfers.forEach(tr => {
                let fromWh = tr.fromWarehouse;
                let toWh = tr.toWarehouse;
                if (!actualStockMap[fromWh]) { actualStockMap[fromWh] = 0; actualValMap[fromWh] = 0; }
                if (!actualStockMap[toWh]) { actualStockMap[toWh] = 0; actualValMap[toWh] = 0; }
                
                (tr.items || []).forEach(line => {
                    let qty = parseFloat(line.quantity || 0);
                    let val = qty * parseFloat(line.cost || line.price || 0);
                    actualStockMap[fromWh] -= qty; actualValMap[fromWh] -= val;
                    actualStockMap[toWh] += qty; actualValMap[toWh] += val;
                });
            });
            
            let baseStock = 0;
            let baseValue = 0;
            for (let w in actualStockMap) {
                if (selectedWh === 'ALL' || w === selectedWh || selectedWh === w.name) {
                    baseStock += actualStockMap[w];
                    baseValue += actualValMap[w];
                }
            }

            // Update KPI Strip
            const netStock = baseStock;
            const netValue = baseValue;
            document.getElementById('kpi-chart-stock').innerHTML = `${Number(netStock).toLocaleString()} <small style="font-size:0.75rem;">وحدة</small>`;
            document.getElementById('kpi-chart-in').innerHTML = `${Number(totalIn).toLocaleString()} <small style="font-size:0.75rem;">وحدة</small>`;
            document.getElementById('kpi-chart-out').innerHTML = `${Number(totalOut).toLocaleString()} <small style="font-size:0.75rem;">وحدة</small>`;
            document.getElementById('kpi-chart-val').innerHTML = `${Number(netValue).toLocaleString()} <small style="font-size:0.75rem;">SDG</small>`;
            document.getElementById('kpi-chart-counts').innerHTML = `${items.length} <small style="font-size:0.75rem;">صنف</small>`;

            if (unifiedChart) {
                unifiedChart.destroy();
            }

            Chart.defaults.color = '#94a3b8';
            Chart.defaults.font.family = "'Tajawal', sans-serif";

            // ── Render Chart Based on Selected View Mode ──
            if (currentViewMode === 'warehouses') {
                // View Mode 2: Warehouse Balances Comparison
                const warehouses = getStorageData('erp_warehouses_v1') || [{ id: 'main', name: 'المخزن الرئيسي' }];
                const whLabels = [];
                const whStock = [];
                const whValues = [];

                warehouses.forEach(w => {
                    whLabels.push(w.name || w.id);
                    let wQty = 0;
                    let wVal = 0;
                    items.forEach(it => {
                        const q = parseFloat(it.stock || it.quantity || 0) / (warehouses.length || 1);
                        const c = parseFloat(it.cost || it.avgCost || 0);
                        wQty += q;
                        wVal += (q * c);
                    });
                    whStock.push(Math.round(wQty));
                    whValues.push(Math.round(wVal));
                });

                unifiedChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: whLabels,
                        datasets: [
                            {
                                label: 'إجمالي كمية المخزون (وحدة)',
                                data: whStock,
                                backgroundColor: 'rgba(56, 189, 248, 0.75)',
                                borderColor: '#38bdf8',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                yAxisID: 'y'
                            },
                            {
                                label: 'إجمالي القيمة التقديرية (SDG)',
                                data: whValues,
                                backgroundColor: 'rgba(245, 158, 11, 0.75)',
                                borderColor: '#f59e0b',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: getChartOptions('مقارنة كميات وقيم المستودعات')
                });

            } else if (currentViewMode === 'top_items') {
                // View Mode 3: Top Items
                const sortedItems = [...items].sort((a,b) => (parseFloat(b.stock||0) - parseFloat(a.stock||0))).slice(0, 10);
                const itemLabels = sortedItems.map(i => (i.name || i.code || 'صنف'));
                const itemQty = sortedItems.map(i => parseFloat(i.stock || 0));
                const itemVal = sortedItems.map(i => parseFloat(i.stock || 0) * parseFloat(i.cost || i.avgCost || 0));

                unifiedChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: itemLabels,
                        datasets: [
                            {
                                label: 'الكمية المتوفرة حالياً (وحدة)',
                                data: itemQty,
                                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                borderColor: '#10b981',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                yAxisID: 'y'
                            },
                            {
                                label: 'قيمة الرصيد (SDG)',
                                data: itemVal,
                                backgroundColor: 'rgba(168, 85, 247, 0.8)',
                                borderColor: '#a855f7',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: getChartOptions('أعلى 10 أصناف بالمخازن')
                });

            } else if (currentViewMode === 'flow') {
                // View Mode 4: In/Out Flow
                const sortedDates = Object.keys(dailyData).sort();
                const labels = sortedDates.length > 0 ? sortedDates.map(d => d.split('-').slice(1).join('/')) : ['اليوم'];
                const inData = sortedDates.map(d => dailyData[d].inQty);
                const outData = sortedDates.map(d => dailyData[d].outQty);
                const trfData = sortedDates.map(d => dailyData[d].trfQty);

                unifiedChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: '📥 إجمالي الوارد (توريدات ومشتريات)',
                                data: inData,
                                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                borderColor: '#10b981',
                                borderWidth: 1,
                                borderRadius: 6
                            },
                            {
                                label: '📤 إجمالي المنصرف (مبيعات وصرف)',
                                data: outData,
                                backgroundColor: 'rgba(239, 68, 68, 0.85)',
                                borderColor: '#ef4444',
                                borderWidth: 1,
                                borderRadius: 6
                            },
                            {
                                label: '🔄 تحويلات المخازن',
                                data: trfData,
                                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                                borderColor: '#f59e0b',
                                borderWidth: 1,
                                borderRadius: 6
                            }
                        ]
                    },
                    options: getChartOptions('حركات التدفق اليومي للوارد والصادر')
                });

            } else {
                // Default Mode: Comprehensive Cumulative Trend (المسار التراكمي الشامل)
                const sortedDates = Object.keys(dailyData).sort();
                const labels = sortedDates.length > 0 ? sortedDates.map(d => d.split('-').slice(1).join('/')) : ['اليوم'];
                
                const inData = sortedDates.map(d => dailyData[d].inQty);
                const outData = sortedDates.map(d => dailyData[d].outQty);
                const trfData = sortedDates.map(d => dailyData[d].trfQty);

                // To plot the cumulative trend correctly ending at the CURRENT baseStock,
                // we must reverse-engineer the stock at the beginning of the chart's timeframe.
                let runningStock = baseStock - (totalIn - totalOut);
                let runningVal = baseValue - (totalValIn - totalValOut);
                
                const stockData = [];
                const valData = [];

                sortedDates.forEach(d => {
                    const day = dailyData[d];
                    runningStock += (day.inQty - day.outQty);
                    runningVal += (day.inVal - day.outVal);
                    stockData.push(runningStock);
                    valData.push(runningVal);
                });

                unifiedChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: '📈 المخزون التراكمي (كمية)',
                                data: stockData.length ? stockData : [netStock],
                                type: 'line',
                                borderColor: '#a855f7',
                                backgroundColor: 'rgba(168, 85, 247, 0.18)',
                                borderWidth: 3.5,
                                tension: 0.35,
                                fill: true,
                                pointBackgroundColor: '#0f172a',
                                pointBorderColor: '#a855f7',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 7,
                                yAxisID: 'y'
                            },
                            {
                                label: '💵 القيمة المالية التراكمية (SDG)',
                                data: valData.length ? valData : [netValue],
                                type: 'line',
                                borderColor: '#38bdf8',
                                backgroundColor: 'rgba(56, 189, 248, 0.12)',
                                borderWidth: 3,
                                tension: 0.35,
                                fill: true,
                                pointBackgroundColor: '#0f172a',
                                pointBorderColor: '#38bdf8',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 7,
                                yAxisID: 'y1'
                            },
                            {
                                label: '📥 الوارد (كمية)',
                                data: inData,
                                type: 'bar',
                                backgroundColor: 'rgba(16, 185, 129, 0.75)',
                                borderColor: '#10b981',
                                borderWidth: 1,
                                borderRadius: 4,
                                yAxisID: 'y'
                            },
                            {
                                label: '📤 المنصرف (كمية)',
                                data: outData,
                                type: 'bar',
                                backgroundColor: 'rgba(239, 68, 68, 0.75)',
                                borderColor: '#ef4444',
                                borderWidth: 1,
                                borderRadius: 4,
                                yAxisID: 'y'
                            }
                        ]
                    },
                    options: getChartOptions('المسار التراكمي الشامل لحركات وقيم المخزون')
                });
            }
        }

        function getChartOptions(titleText) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        rtl: true,
                        labels: {
                            color: '#e2e8f0',
                            font: { size: 12.5, weight: 'bold', family: "'Tajawal', sans-serif" },
                            padding: 16,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.96)',
                        titleFont: { size: 14, family: "'Tajawal', sans-serif", weight: 'bold' },
                        bodyFont: { size: 12.5, family: "'Tajawal', sans-serif" },
                        padding: 14,
                        borderColor: 'rgba(56, 189, 248, 0.3)',
                        borderWidth: 1.5,
                        rtl: true,
                        textDirection: 'rtl',
                        boxPadding: 6,
                        usePointStyle: true
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)', drawBorder: false },
                        ticks: { color: '#cbd5e1', font: { weight: 'bold', family: "'Tajawal', sans-serif" } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        title: {
                            display: true,
                            text: 'الكميات (وحدة)',
                            color: '#a855f7',
                            font: { weight: 'bold', size: 12, family: "'Tajawal', sans-serif" }
                        },
                        ticks: { color: '#cbd5e1' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { drawOnChartArea: false },
                        title: {
                            display: true,
                            text: 'القيمة المالية (SDG)',
                            color: '#38bdf8',
                            font: { weight: 'bold', size: 12, family: "'Tajawal', sans-serif" }
                        },
                        ticks: { color: '#38bdf8' }
                    }
                }
            };
        }
    </script>
</body>
</html>