<?php 
$page_title = "ملخص حركة المخزون (الشامل)"; 
$header_theme_class = "header-theme-inventory";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - نظام مقاييس</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="css/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/components.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/report-ribbon.css?v=1787182249">
    
    <style>
        .dashboard-container { display: flex; flex-direction: column; gap: 20px; padding: 20px 30px; box-sizing: border-box; max-width: 1600px; margin: 0 auto; }
        
        /* Navigation Bar (Ultra-Compact Segmented Clusters) */
        .nav-tabs { 
            display: flex; gap: 6px; flex-wrap: wrap; overflow: visible;
            background: var(--surface-card, rgba(15, 23, 42, 0.7)); padding: 6px 10px; border-radius: 14px; 
            border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1)); box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            backdrop-filter: blur(20px); align-items: center; width: 100%; z-index: 100; position: relative;
            min-height: 48px; box-sizing: border-box;
        }
        .nav-tabs::-webkit-scrollbar { display: none; }
        .nav-cluster { display: flex; align-items: center; gap: 4px; }
        .nav-divider { width: 1px; height: 24px; background: rgba(255, 255, 255, 0.12); margin: 0 4px; flex-shrink: 0; }
        .nav-tab {
            padding: 8px 15px; border-radius: 9px; text-decoration: none; font-weight: 700;
            color: var(--text-secondary, #94a3b8); background: transparent; border: 1px solid transparent;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 6px; font-size: 0.88rem;
            position: relative; white-space: nowrap; flex-shrink: 0; cursor: pointer; height: 38px; box-sizing: border-box;
        }
        .nav-tab:hover {
            color: #fff; background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        .nav-tab.active {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.18), rgba(37, 99, 235, 0.18));
            border-color: rgba(56, 189, 248, 0.45); color: #38bdf8;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.25);
        }
        .nav-tab-ai {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.15), rgba(139, 92, 246, 0.15));
            border: 1px solid rgba(236, 72, 153, 0.4); color: #f472b6 !important;
            box-shadow: 0 0 10px rgba(236, 72, 153, 0.2);
        }
        .nav-tab-ai:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(139, 92, 246, 0.25));
            border-color: rgba(236, 72, 153, 0.6); color: #fff !important;
            box-shadow: 0 0 15px rgba(236, 72, 153, 0.35);
        }
        .nav-ai-badge {
            background: #ec4899; color: #fff; font-size: 0.68rem; padding: 2px 5px; border-radius: 8px; font-weight: 900;
        }

        .filters-bar { background: rgba(15, 23, 42, 0.6); padding: 20px; border-radius: 16px; border: 1px solid var(--glass-border); display: grid; gap: 15px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); backdrop-filter: blur(10px); }
        .f-group label { display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 8px; font-weight: 700; }
        .f-input { width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fff; font-family: 'Tajawal', sans-serif; transition: 0.3s; }
        .f-input:focus { outline: none; border-color: #38bdf8; background: rgba(56, 189, 248, 0.05); }

        .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .kpi-card { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); padding: 20px; border-radius: 16px; display: flex; flex-direction: column; gap: 10px; position: relative; overflow: hidden; backdrop-filter: blur(10px); }
        .kpi-card::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%; }
        .kpi-card.c-blue::before { background: #3b82f6; }
        .kpi-card.c-emerald::before { background: #10b981; }
        .kpi-card.c-purple::before { background: #8b5cf6; }
        .kpi-card.c-amber::before { background: #f59e0b; }
        .kpi-title { font-size: 0.9rem; color: #94a3b8; font-weight: 700; }
        .kpi-val { font-size: 1.8rem; font-weight: 900; color: #f8fafc; }
        .kpi-val span { font-size: 0.9rem; color: #64748b; font-weight: 500; }

        .table-panel { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); border-radius: 16px; overflow: hidden; backdrop-filter: blur(10px); }
        .table-wrapper { overflow-x: auto; }
        .report-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .report-table th { background: rgba(0,0,0,0.2); padding: 15px; text-align: right; color: #94a3b8; font-weight: 700; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); white-space: nowrap; }
        .report-table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; font-size: 0.95rem; vertical-align: middle; }
        .report-table tr:hover td { background: rgba(255,255,255,0.02); }

        /* Nav Dropdown for Extra Reports */
        .nav-dropdown { position: relative; display: inline-block; }
        .nav-dropdown-content {
            display: none; position: absolute; top: calc(100% + 6px); right: 0; min-width: 250px;
            background: rgba(15, 23, 42, 0.98); backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border); border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.65); padding: 6px; z-index: 10000;
            animation: dropdownFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-dropdown-content::before {
            content: ''; position: absolute; top: -10px; left: 0; right: 0; height: 10px; background: transparent;
        }
        @keyframes dropdownFadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .nav-dropdown:hover .nav-dropdown-content, 
        .nav-dropdown:focus-within .nav-dropdown-content,
        .nav-dropdown.active .nav-dropdown-content { display: flex; flex-direction: column; gap: 4px; }
        .nav-dropdown-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: #cbd5e1;
            text-decoration: none; font-size: 0.86rem; font-weight: 700; border-radius: 8px; transition: all 0.2s ease;
        }
        .nav-dropdown-item:hover { background: rgba(56, 189, 248, 0.15); color: #38bdf8; transform: translateX(-3px); }
        .btn-action { padding: 8px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; border: none; display: flex; align-items: center; gap: 6px; }

        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body, :root.light-theme .ambient-bg { background: #f8fafc !important; }
        :root.light-theme .table-panel, :root.light-theme .kpi-card, :root.light-theme .filters-bar { background: #ffffff !important; border-color: rgba(0, 0, 0, 0.08) !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important; color: #0f172a !important; }
        :root.light-theme .f-input { background: #f1f5f9 !important; border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .report-table th { background: #f1f5f9 !important; color: #0f172a !important; border-bottom: 2px solid #cbd5e1 !important; }
        :root.light-theme .report-table td { color: #1e293b !important; border-bottom: 1px solid rgba(0,0,0,0.08) !important; }
        :root.light-theme .report-table tr:hover td { background: rgba(0,0,0,0.02) !important; }
        :root.light-theme .kpi-val { color: #0f172a !important; }
        :root.light-theme .kpi-title, :root.light-theme .f-group label { color: #334155 !important; }
        :root.light-theme .nav-dropdown-content { background: #ffffff !important; border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
        :root.light-theme .nav-dropdown-item { color: #334155 !important; }
        :root.light-theme .nav-dropdown-item:hover { background: #f1f5f9 !important; color: #0284c7 !important; }
    </style>
</head>
<body class="no-page-transition">
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
    
    <div class="dashboard-container">
        <?php include 'includes/header.php'; ?>

        <!-- Unified Smart Ribbon Toolbar Row -->
        <div class="report-ribbon-bar">
            <div class="ribbon-presets-group">
                <button class="preset-pill-btn active" data-preset="0" onclick="applyReportPreset(0, this)" title="القالب الافتراضي 1">
                    <span class="preset-num">1</span> <span>افتراضي 1</span>
                </button>
                <button class="preset-pill-btn" data-preset="1" onclick="applyReportPreset(1, this)" title="القالب المخصص 2">
                    <span class="preset-num">2</span> <span>تخصيص 2</span>
                </button>
                <button class="preset-pill-btn" data-preset="2" onclick="applyReportPreset(2, this)" title="القالب المخصص 3">
                    <span class="preset-num">3</span> <span>تخصيص 3</span>
                </button>
            </div>

            <div class="ribbon-actions-group">
                <button class="ribbon-btn" onclick="openStudioPreviewPage()" style="--btn-color: #eab308; background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(202, 138, 4, 0.3)); border-color: rgba(234, 179, 8, 0.5); color: #fef08a;">
                    <span>⚙️</span> <span>إعدادات الأعمدة</span>
                </button>
                <button class="ribbon-btn" onclick="openStudioPreviewPage()" style="--btn-color: #0d9488;">
                    <span>👁️</span> <span>معاينة</span>
                </button>
                <button class="ribbon-btn" onclick="printReportDirectly('تقرير ملخص حركات المخزون', '#report-table-wrap')" style="--btn-color: #38bdf8;">
                    <span>🖨️</span> <span>طباعة</span>
                </button>
            </div>
        </div>

        <!-- Pro Studio Dropdown Panel & Modals -->
        <!-- Studio panel moved to preview-inventory-report.php -->
        <!-- Studio modals moved to preview-inventory-report.php -->

        <!-- KPIs -->
        <div class="kpi-row">
            <div class="kpi-card c-blue">
                <div class="kpi-title">عدد الأصناف المتأثرة بالحركة</div>
                <div class="kpi-val" id="kpi-count">0 <span>صنف</span></div>
            </div>
            <div class="kpi-card c-emerald">
                <div class="kpi-title">إجمالي الكميات (الرصيد الختامي)</div>
                <div class="kpi-val" id="kpi-qty">0 <span>وحدة</span></div>
            </div>
            <div class="kpi-card c-amber">
                <div class="kpi-title">القيمة الإجمالية للمخزون</div>
                <div class="kpi-val" id="kpi-value" style="color: #f59e0b;">0 <span>SDG</span></div>
            </div>
        </div>

        <!-- Filters Grid -->
        <div class="filters-bar" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div class="f-group">
                <label>بحث ذكي (كود أو اسم أو باركود)</label>
                <input type="text" class="f-input" id="filter-search" placeholder="اكتب للبحث..." onkeyup="applyFilters()">
            </div>
            <div class="f-group">
                <label>تصفية بالمخزن</label>
                <select class="f-input" id="filter-warehouse" onchange="applyFilters()">
                    <option value="ALL">جميع المخازن (الكل)</option>
                </select>
            </div>
            <div class="f-group">
                <label>من تاريخ</label>
                <input type="date" class="f-input" id="filter-date-from" onchange="loadData(); applyFilters();">
            </div>
            <div class="f-group">
                <label>إلى تاريخ</label>
                <input type="date" class="f-input" id="filter-date-to" onchange="loadData(); applyFilters();">
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-panel">
            <div class="table-wrapper" id="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>كود الصنف</th>
                            <th>اسم الصنف</th>
                            <th>المخزن</th>
                            <th style="color:#6366f1; text-align:center;">رصيد افتتاحي</th>
                            <th style="color:#10b981; text-align:center;">إجمالي الوارد (+)</th>
                            <th style="color:#ef4444; text-align:center;">إجمالي المنصرف (-)</th>
                            <th style="color:#f59e0b; text-align:center;">الرصيد الختامي</th>
                            <th>قيمة المخزون</th>
                            <th style="text-align:center;">إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-tbody">
                        <tr><td colspan="10" style="text-align:center; padding:30px; color:var(--text-secondary);">جاري الحساب...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script>
        let invDb = [];
        let itemsDb = [];

        function fmtNum(n) {
            if (n === undefined || n === null || isNaN(n)) return '0';
            const num = Number(n);
            const dec = (num % 1 === 0) ? 0 : (num % 0.01 === 0 ? 2 : 3);
            return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: dec});
        }

        function initInventoryReport() {
            populateWarehouseFilter();
            
            // Set default dates (Start of month to today)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const dateFrom = document.getElementById('filter-date-from');
            const dateTo = document.getElementById('filter-date-to');
            if (dateFrom && !dateFrom.value) dateFrom.valueAsDate = firstDay;
            if (dateTo && !dateTo.value) dateTo.valueAsDate = today;

            // Check URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const whParam = urlParams.get('warehouse') || urlParams.get('wh');
            const whSelect = document.getElementById('filter-warehouse');
            if (whParam && whSelect) {
                for (let i = 0; i < whSelect.options.length; i++) {
                    if (whSelect.options[i].value === whParam || whSelect.options[i].text === whParam) {
                        whSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            const itemParam = urlParams.get('item') || urlParams.get('search');
            const searchInput = document.getElementById('filter-search');
            if (itemParam && searchInput) {
                searchInput.value = itemParam;
            }

            loadData();
            applyFilters();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initInventoryReport);
        } else {
            initInventoryReport();
        }
        document.addEventListener('DBReady', initInventoryReport);

        function populateWarehouseFilter() {
            const select = document.getElementById('filter-warehouse');
            if (!select || select.options.length > 1) return;
            select.innerHTML = '<option value="ALL">جميع المخازن (الكل)</option>';

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            const displayWh = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];
            displayWh.forEach(wh => {
                const opt = document.createElement('option');
                opt.value = wh.name;
                opt.text = wh.name;
                select.appendChild(opt);
            });
        }

        function loadData() {
            itemsDb = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            
            let dateFrom = document.getElementById('filter-date-from')?.value;
            let dateTo = document.getElementById('filter-date-to')?.value;
            
            if (!dateFrom || !dateTo) return;

            let startDate = new Date(dateFrom);
            startDate.setHours(0,0,0,0);
            
            let endDate = new Date(dateTo);
            endDate.setHours(23,59,59,999);

            invDb = buildSummaryReport(itemsDb, startDate, endDate);
        }

        function buildSummaryReport(items, startDate, endDate) {
            let summaryMap = {}; 

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehousesDb.length === 0) warehousesDb = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            const allWarehouses = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];

            let getWarehouseName = (id) => {
                if (!id) return 'المخزن الرئيسي';
                let w = allWarehouses.find(w => String(w.id) === String(id) || w.name === id);
                return w ? w.name : id;
            };

            items.forEach(item => {
                allWarehouses.forEach(wh => {
                    let key = String(item.id) + '|' + wh.name;
                    summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                });

                // Add Master Data Opening Balance from Item Record
                let openQty = parseFloat(item.openingBalance || item.opening_balance || 0);
                if (openQty > 0) {
                    let targetWhName = getWarehouseName(item.openingBalanceWarehouseId || item.warehouseId || item.warehouse);
                    let key = String(item.id) + '|' + targetWhName;
                    if (!summaryMap[key]) {
                        summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    }
                    let itemDate = new Date(item.createdAt || item.date || 0);
                    if (itemDate <= endDate) {
                        summaryMap[key].opening += openQty;
                        summaryMap[key].closing += openQty;
                    }
                }
            });

            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary' && (x.saleType !== 'reserved' || x.deliveryStatus === 'delivered'));

            // 1. Process Purchases & Adjustments
            purchases.forEach(inv => {
                let invDate = new Date(inv.date || inv.invoiceDate || inv.receiveDate || inv.createdAt);
                let hdrWhName = getWarehouseName(inv.warehouseId || inv.warehouse);
                const isReturn = inv.isReturn === true;
                const isAdjustment = inv.isAdjustment === true;

                (inv.items || inv.lines || []).forEach(line => {
                    let lineWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
                    let itemId = String(line.itemId || line.productId);
                    let key = itemId + '|' + lineWhName;
                    
                    if (!summaryMap[key]) {
                        summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    }

                    let item = items.find(i => String(i.id) === itemId);
                    let baseQty = line.customBaseQty !== undefined ? parseFloat(line.customBaseQty) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1));
                    
                    let isOutward = isReturn || (isAdjustment && baseQty < 0);
                    let effectiveQty = Math.abs(baseQty);

                    if (invDate < startDate) {
                        if (isOutward) {
                            summaryMap[key].opening -= effectiveQty;
                            summaryMap[key].closing -= effectiveQty;
                        } else {
                            summaryMap[key].opening += effectiveQty;
                            summaryMap[key].closing += effectiveQty;
                        }
                    } else if (invDate >= startDate && invDate <= endDate) {
                        if (isOutward) {
                            summaryMap[key].outward += effectiveQty;
                            summaryMap[key].closing -= effectiveQty;
                        } else {
                            summaryMap[key].inward += effectiveQty;
                            summaryMap[key].closing += effectiveQty;
                        }
                    }
                });
            });

            // 2. Process Sales
            sales.forEach(inv => {
                let invDate = new Date(inv.date || inv.invoiceDate || inv.createdAt);
                let hdrWhName = getWarehouseName(inv.warehouse);
                const isReturn = inv.isReturn === true;
                
                (inv.items || []).forEach(line => {
                    let lineWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
                    let itemId = String(line.itemId);
                    let key = itemId + '|' + lineWhName;
                    
                    if (!summaryMap[key]) {
                        summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    }

                    let item = items.find(i => String(i.id) === itemId);
                    let baseQty = line.customBaseQty !== undefined ? parseFloat(line.customBaseQty) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1));
                    
                    if (invDate < startDate) {
                        if (isReturn) {
                            summaryMap[key].opening += baseQty;
                            summaryMap[key].closing += baseQty;
                        } else {
                            summaryMap[key].opening -= baseQty;
                            summaryMap[key].closing -= baseQty;
                        }
                    } else if (invDate >= startDate && invDate <= endDate) {
                        if (isReturn) {
                            summaryMap[key].inward += baseQty;
                            summaryMap[key].closing += baseQty;
                        } else {
                            summaryMap[key].outward += baseQty;
                            summaryMap[key].closing -= baseQty;
                        }
                    }
                });
            });

            // 3. Process Warehouse Transfers
            let transfers = JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1')) || [];
            transfers = transfers.filter(x => !x.deleted && x.status !== 'cancelled');
            transfers.forEach(transfer => {
                let transferDate = new Date(transfer.date || transfer.createdAt);
                let fromWhName = getWarehouseName(transfer.fromWarehouse);
                let toWhName = getWarehouseName(transfer.toWarehouse);
                
                (transfer.items || []).forEach(line => {
                    let itemId = String(line.itemId);
                    let fromKey = itemId + '|' + fromWhName;
                    let toKey = itemId + '|' + toWhName;

                    if (!summaryMap[fromKey]) summaryMap[fromKey] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    if (!summaryMap[toKey]) summaryMap[toKey] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    
                    let item = items.find(i => String(i.id) === itemId);
                    let baseQty = line.customBaseQty !== undefined ? parseFloat(line.customBaseQty) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1));
                    
                    if (transferDate < startDate) {
                        summaryMap[fromKey].opening -= baseQty;
                        summaryMap[fromKey].closing -= baseQty;
                        
                        summaryMap[toKey].opening += baseQty;
                        summaryMap[toKey].closing += baseQty;
                    } else if (transferDate >= startDate && transferDate <= endDate) {
                        summaryMap[fromKey].outward += baseQty;
                        summaryMap[fromKey].closing -= baseQty;
                        
                        summaryMap[toKey].inward += baseQty;
                        summaryMap[toKey].closing += baseQty;
                    }
                });
            });

            let generated = [];
            for (let key in summaryMap) {
                let [itemId, warehouse] = key.split('|');
                let itemData = items.find(i => String(i.id) === itemId);
                let cost = itemData ? (parseFloat(itemData.costPrice || itemData.buyPrice || itemData.price) || 0) : 0;
                
                generated.push({
                    itemId: itemId,
                    warehouse: warehouse,
                    opening: summaryMap[key].opening,
                    inward: summaryMap[key].inward,
                    outward: summaryMap[key].outward,
                    closing: summaryMap[key].closing,
                    cost: cost,
                    unit: itemData ? (itemData.baseUnit || itemData.unit || 'وحدة') : 'وحدة'
                });
            }
            return generated;
        }

        function applyFilters() {
            let fWh = document.getElementById('filter-warehouse')?.value || 'ALL';
            let fSearch = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();

            let filteredData = invDb.filter(row => {
                let matchWh = (fWh === 'ALL' || row.warehouse === fWh);
                let itemData = itemsDb.find(i => String(i.id) === String(row.itemId));
                let matchSearch = true;
                if (fSearch && itemData) {
                    let searchStr = `${itemData.code || ''} ${itemData.name || ''} ${itemData.barcode || ''}`.toLowerCase();
                    matchSearch = searchStr.includes(fSearch);
                }
                
                let hasMovement = (row.opening !== 0 || row.inward !== 0 || row.outward !== 0 || row.closing !== 0);
                return matchWh && matchSearch && hasMovement;
            });

            renderTable(filteredData);
        }

        function renderTable(data) {
            const tbody = document.getElementById('inventory-tbody');
            tbody.innerHTML = '';

            let totalQty = 0;
            let totalValue = 0;
            let itemCount = new Set();

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:35px; color:var(--text-secondary); font-size:1.05rem;">لا توجد حركات مطابقة للبحث في هذه الفترة</td></tr>`;
            } else {
                data.forEach((row, index) => {
                    let item = itemsDb.find(i => String(i.id) === String(row.itemId)) || {};
                    let rowVal = row.closing * row.cost;
                    
                    itemCount.add(row.itemId);
                    totalQty += row.closing;
                    totalValue += rowVal;

                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td style="font-weight:700; color:#3b82f6;">${item.code || row.itemId || '-'}</td>
                        <td style="font-weight:700; color:#f8fafc;">${item.name || 'غير معروف'}</td>
                        <td><span style="background:rgba(56,189,248,0.1); color:#38bdf8; border:1px solid rgba(56,189,248,0.25); padding:3px 8px; border-radius:6px; font-weight:700;">${row.warehouse}</span></td>
                        <td style="color:#6366f1; font-weight:800; text-align:center;">${fmtNum(row.opening)}</td>
                        <td style="color:#10b981; font-weight:800; text-align:center; background:rgba(16,185,129,0.02);">${row.inward > 0 ? '+ ' + fmtNum(row.inward) : '-'}</td>
                        <td style="color:#ef4444; font-weight:800; text-align:center; background:rgba(239,68,68,0.02);">${row.outward > 0 ? '- ' + fmtNum(row.outward) : '-'}</td>
                        <td style="color:#f59e0b; font-weight:900; font-size:1.05rem; text-align:center; background:rgba(245,158,11,0.05);">${fmtNum(row.closing)} <small style="font-size:0.75rem; color:#94a3b8;">${row.unit}</small></td>
                        <td style="font-weight:700; color:#cbd5e1;">${fmtNum(rowVal)} SDG</td>
                        <td style="text-align:center;">
                            <a href="item-ledger.php?item=${row.itemId}" class="btn-action" style="padding:3px 8px; font-size:0.75rem; text-decoration:none; display:inline-flex; background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); border-radius:6px; font-weight:700;" title="عرض كارت حركة الصنف التفصيلي">
                                📑 كشف الحركة
                            </a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // Update KPIs
            document.getElementById('kpi-count').innerHTML = `${fmtNum(itemCount.size)} <span>صنف</span>`;
            document.getElementById('kpi-qty').innerHTML = `${fmtNum(totalQty)} <span>وحدة</span>`;
            document.getElementById('kpi-value').innerHTML = `${fmtNum(totalValue)} <span>SDG</span>`;
        }

        // Nav Dropdowns Click & Touch Toggle
        document.addEventListener('click', function(e) {
            const clickedDropdown = e.target.closest('.nav-dropdown');
            document.querySelectorAll('.nav-dropdown').forEach(dd => {
                if (dd !== clickedDropdown) dd.classList.remove('active');
            });
            if (clickedDropdown && e.target.closest('.nav-tab')) {
                clickedDropdown.classList.toggle('active');
            }
        });
    </script>
<script src="js/universal-report-studio.js?v=<?php echo time(); ?>"></script>
</body>
</html>
