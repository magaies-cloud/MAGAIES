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
    
    <style>
        .dashboard-container { display: flex; flex-direction: column; gap: 20px; padding: 20px 30px; box-sizing: border-box; max-width: 1600px; margin: 0 auto; }
        
                /* Unified Nav Tabs Container */
        .nav-tabs { 
            display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none;
            background: rgba(15, 23, 42, 0.7); padding: 12px 15px; border-radius: 16px; 
            border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: inset 0 2px 20px rgba(0,0,0,0.4);
            backdrop-filter: blur(15px);
        }
        .nav-tabs::-webkit-scrollbar { display: none; }
        .nav-tab {
            padding: 12px 25px; border-radius: 12px; text-decoration: none; font-weight: 700;
            color: #94a3b8; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.03);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px; font-size: 0.95rem;
            position: relative; overflow: hidden; white-space: nowrap; flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        /* Kinetic Hover Effect */
        .nav-tab::before { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); transform: skewX(-20deg); transition: 0.5s; }
        .nav-tab:hover::before { left: 150%; }

        /* Button 1: Blue */
        .nav-tab:nth-child(1):hover { background: rgba(59, 130, 246, 0.1); color: #fff; border-color: rgba(59, 130, 246, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(1).active { background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2)); border-color: rgba(59, 130, 246, 0.5); color: #60a5fa; box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }

        /* Button 2: Emerald */
        .nav-tab:nth-child(2):hover { background: rgba(16, 185, 129, 0.1); color: #fff; border-color: rgba(16, 185, 129, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(2).active { background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2)); border-color: rgba(16, 185, 129, 0.5); color: #34d399; box-shadow: 0 0 20px rgba(16, 185, 129, 0.3); }

        /* Button 3: Amber */
        .nav-tab:nth-child(3):hover { background: rgba(245, 158, 11, 0.1); color: #fff; border-color: rgba(245, 158, 11, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(3).active { background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2)); border-color: rgba(245, 158, 11, 0.5); color: #fbbf24; box-shadow: 0 0 20px rgba(245, 158, 11, 0.3); }

        /* Button 4: Purple */
        .nav-tab:nth-child(4):hover { background: rgba(139, 92, 246, 0.1); color: #fff; border-color: rgba(139, 92, 246, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(4).active { background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(109, 40, 217, 0.2)); border-color: rgba(139, 92, 246, 0.5); color: #c084fc; box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); }

        /* Button 5: Rose */
        .nav-tab:nth-child(5):hover { background: rgba(244, 63, 94, 0.1); color: #fff; border-color: rgba(244, 63, 94, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(5).active { background: linear-gradient(135deg, rgba(244, 63, 94, 0.2), rgba(225, 29, 72, 0.2)); border-color: rgba(244, 63, 94, 0.5); color: #fb7185; box-shadow: 0 0 20px rgba(244, 63, 94, 0.3); }

        /* Button 6: Cyan */
        .nav-tab:nth-child(6):hover { background: rgba(6, 182, 212, 0.1); color: #fff; border-color: rgba(6, 182, 212, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(6).active { background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(8, 145, 178, 0.2)); border-color: rgba(6, 182, 212, 0.5); color: #22d3ee; box-shadow: 0 0 20px rgba(6, 182, 212, 0.3); }

        /* Light Mode Styling */
        :root.light-theme .nav-tabs { background: rgba(248, 250, 252, 0.85); border: 1px solid rgba(0,0,0,0.06); box-shadow: inset 0 2px 10px rgba(0,0,0,0.02); }
        :root.light-theme .nav-tab { color: #64748b; background: rgba(255,255,255,0.7); border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        :root.light-theme .nav-tab::before { background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent); }
        
        /* Light Mode active/hover states per button (Neon effect) */
        :root.light-theme .nav-tab:nth-child(1):hover { background: #fff; color: #1e3a8a; border-color: #3b82f6; box-shadow: 0 5px 15px rgba(59, 130, 246, 0.2); transform: translateY(-2px); }
        :root.light-theme .nav-tab:nth-child(1).active { background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1)); color: #2563eb; border-color: #3b82f6; box-shadow: 0 0 20px rgba(59, 130, 246, 0.5), inset 0 0 10px rgba(255,255,255,0.9); }

        :root.light-theme .nav-tab:nth-child(2):hover { background: #fff; color: #064e3b; border-color: #10b981; box-shadow: 0 5px 15px rgba(16, 185, 129, 0.2); transform: translateY(-2px); }
        :root.light-theme .nav-tab:nth-child(2).active { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1)); color: #059669; border-color: #10b981; box-shadow: 0 0 20px rgba(16, 185, 129, 0.5), inset 0 0 10px rgba(255,255,255,0.9); }

        :root.light-theme .nav-tab:nth-child(3):hover { background: #fff; color: #78350f; border-color: #f59e0b; box-shadow: 0 5px 15px rgba(245, 158, 11, 0.2); transform: translateY(-2px); }
        :root.light-theme .nav-tab:nth-child(3).active { background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1)); color: #d97706; border-color: #f59e0b; box-shadow: 0 0 20px rgba(245, 158, 11, 0.5), inset 0 0 10px rgba(255,255,255,0.9); }

        :root.light-theme .nav-tab:nth-child(4):hover { background: #fff; color: #4c1d95; border-color: #8b5cf6; box-shadow: 0 5px 15px rgba(139, 92, 246, 0.2); transform: translateY(-2px); }
        :root.light-theme .nav-tab:nth-child(4).active { background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(109, 40, 217, 0.1)); color: #7c3aed; border-color: #8b5cf6; box-shadow: 0 0 20px rgba(139, 92, 246, 0.5), inset 0 0 10px rgba(255,255,255,0.9); }

        :root.light-theme .nav-tab:nth-child(5):hover { background: #fff; color: #881337; border-color: #f43f5e; box-shadow: 0 5px 15px rgba(244, 63, 94, 0.2); transform: translateY(-2px); }
        :root.light-theme .nav-tab:nth-child(5).active { background: linear-gradient(135deg, rgba(244, 63, 94, 0.1), rgba(225, 29, 72, 0.1)); color: #e11d48; border-color: #f43f5e; box-shadow: 0 0 20px rgba(244, 63, 94, 0.5), inset 0 0 10px rgba(255,255,255,0.9); }

        :root.light-theme .nav-tab:nth-child(6):hover { background: #fff; color: #164e63; border-color: #06b6d4; box-shadow: 0 5px 15px rgba(6, 182, 212, 0.2); transform: translateY(-2px); }
        :root.light-theme .nav-tab:nth-child(6).active { background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(8, 145, 178, 0.1)); color: #0891b2; border-color: #06b6d4; box-shadow: 0 0 20px rgba(6, 182, 212, 0.5), inset 0 0 10px rgba(255,255,255,0.9); }

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

        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body, :root.light-theme .ambient-bg { background: #f8fafc !important; }
        :root.light-theme .table-panel, :root.light-theme .kpi-card, :root.light-theme .filters-bar { background: #ffffff !important; border-color: rgba(0, 0, 0, 0.08) !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important; color: #0f172a !important; }
        :root.light-theme .f-input { background: #f1f5f9 !important; border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .report-table th { background: #f1f5f9 !important; color: #0f172a !important; border-bottom: 2px solid #cbd5e1 !important; }
        :root.light-theme .report-table td { color: #1e293b !important; border-bottom: 1px solid rgba(0,0,0,0.08) !important; }
        :root.light-theme .report-table tr:hover td { background: rgba(0,0,0,0.02) !important; }
        :root.light-theme .kpi-val { color: #0f172a !important; }
        :root.light-theme .kpi-title, :root.light-theme .f-group label { color: #334155 !important; }
    </style>
</head>
<body class="no-page-transition">
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
    


    <div class="dashboard-container">
        <?php include 'includes/header.php'; ?>

        <!-- Navigation and Actions Row -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; flex-wrap: wrap; gap: 15px;">
                <div class="nav-tabs" style="margin-bottom: 0;">
                    <a data-href="inventory-report.php" onclick="window.location.href='inventory-report.php'" class="nav-tab">📦 جرد الأرصدة العام</a>
                    <a data-href="warehouse-inventory.php" onclick="window.location.href='warehouse-inventory.php'" class="nav-tab">🏢 جرد المخزن</a>
                    <a data-href="inventory-summary.php" onclick="window.location.href='inventory-summary.php'" class="nav-tab active">📊 ملخص الحركات</a>
                    <a data-href="inventory-analytics.php" onclick="window.location.href='inventory-analytics.php'" class="nav-tab">🧠 التحليل الاستراتيجي</a>
                    <a data-href="item-ledger.php" onclick="window.location.href='item-ledger.php'" class="nav-tab">📑 حركة الصنف</a>
                    <a data-href="warehouse-ledger.php" onclick="window.location.href='warehouse-ledger.php'" class="nav-tab">🏭 حركة المخزن</a>
                <a data-href="ai_goods_advisor.php" onclick="window.location.href='ai_goods_advisor.php'" class="nav-tab">🤖 مستشار الـ AI للبضائع</a>
                <a data-href="smart_inventory_report.php" onclick="window.location.href='smart_inventory_report.php'" class="nav-tab">📊 تقارير المخازن الذكية</a>
                </div>

                <div class="dash-controls" style="margin: 0; display: flex; gap: 8px;">
                    <button class="btn-action" onclick="openReportPreview('تقرير ملخص حركات المخزون', '#report-table-wrap')" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(13,148,136,0.2);">👁️ معاينة</button>
                    <button class="btn-action btn-print" onclick="printReportDirectly('تقرير ملخص حركات المخزون', '#report-table-wrap')" style="background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--glass-border); padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer;">🖨️ طباعة</button>
                    <button class="btn-action btn-print" onclick="alert('تصدير Excel قيد التطوير')" style="background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--glass-border); padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer;">📥 تصدير</button>
                </div>
            </div>

            <!-- KPIs -->
            <div class="kpi-row">
                <div class="kpi-card c-blue">
                    <div class="kpi-title">عدد الأصناف المتأثرة</div>
                    <div class="kpi-val" id="kpi-count">0 <span>صنف</span></div>
                </div>
                <div class="kpi-card c-emerald">
                    <div class="kpi-title">إجمالي الكميات (رصيد ختامي)</div>
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
                    <label>بحث ذكي</label>
                    <input type="text" class="f-input" id="filter-search" placeholder="اكتب الكود أو الاسم..." onkeyup="applyFilters()">
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
                                <th style="color:#6366f1;">رصيد افتتاحي</th>
                                <th style="color:#10b981;">إجمالي الوارد (+)</th>
                                <th style="color:#ef4444;">إجمالي المنصرف (-)</th>
                                <th style="color:#f59e0b;">الرصيد الختامي</th>
                                <th>قيمة المخزون</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-tbody">
                            <tr><td colspan="9" style="text-align:center; padding:30px; color:var(--text-secondary);">جاري الحساب...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260717"></script>
    <script>
        let invDb = [];
        let itemsDb = [];

        function initInventoryReport() {
            populateWarehouseFilter();
            
            // Set default dates (Start of month to today)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            document.getElementById('filter-date-from').valueAsDate = firstDay;
            document.getElementById('filter-date-to').valueAsDate = today;

            loadData();
            applyFilters();
        }

        if (window.APP_READY) {
            initInventoryReport();
        } else {
            document.addEventListener('DBReady', initInventoryReport);
        }

        function populateWarehouseFilter() {
            const select = document.getElementById('filter-warehouse');
            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            
            if (warehousesDb.length === 0) {
                warehousesDb = [{ id:'w-main', name:'المخزن الرئيسي' }];
            }
            warehousesDb.unshift({ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' });
            warehousesDb.forEach(wh => {
                const opt = document.createElement('option');
                opt.value = wh.name;
                opt.text = wh.name;
                select.appendChild(opt);
            });
        }

        function loadData() {
            itemsDb = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            
            let dateFrom = document.getElementById('filter-date-from').value;
            let dateTo = document.getElementById('filter-date-to').value;
            
            if (!dateFrom || !dateTo) return;

            let startDate = new Date(dateFrom);
            startDate.setHours(0,0,0,0);
            
            let endDate = new Date(dateTo);
            endDate.setHours(23,59,59,999);

            let storedInv = buildSummaryReport(itemsDb, startDate, endDate);
            
            // Ensure all items are listed even if 0
            itemsDb.forEach(item => {
                let hasStock = storedInv.some(inv => inv.itemId === item.id);
                if (!hasStock) {
                    storedInv.push({
                        itemId: item.id,
                        warehouse: 'المخزن الرئيسي',
                        opening: 0,
                        inward: 0,
                        outward: 0,
                        closing: 0,
                        cost: parseFloat(item.costPrice || 0)
                    });
                }
            });
            invDb = storedInv;
        }

        function buildSummaryReport(items, startDate, endDate) {
            let summaryMap = {}; 

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            let getWarehouseName = (id) => {
                if (id === 'PENDING_RECEIPT') return '⏳ بضاعة قيد الاستلام';
                let w = warehousesDb.find(w => w.id === id || w.name === id);
                return w ? w.name : 'المخزن الرئيسي';
            };

            let defaultWhName = warehousesDb.length > 0 ? warehousesDb[0].name : 'المخزن الرئيسي';
            items.forEach(item => {
                let key = item.id + '|' + defaultWhName;
                summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
            });

            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary' && (x.saleType !== 'reserved' || x.deliveryStatus === 'delivered'));

            // 1. Process Purchases
            purchases.forEach(inv => {
                let invDate = new Date(inv.date || inv.invoiceDate || inv.createdAt);
                let whName = getWarehouseName(inv.warehouseId || inv.warehouse);
                
                (inv.items || inv.lines || []).forEach(line => {
                    let key = (line.itemId || line.productId) + '|' + whName;
                    if (!summaryMap[key]) summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    
                    let item = items.find(i => i.id === (line.itemId || line.productId));
                    let baseQty = line.customBaseQty !== undefined ? line.customBaseQty : ((parseFloat(line.quantity) || 0) * (line.customFactor || 1) * (item ? (item.conversionFactor || 1) : 1));
                    
                    if (invDate < startDate) {
                        summaryMap[key].opening += baseQty;
                        summaryMap[key].closing += baseQty;
                    } else if (invDate >= startDate && invDate <= endDate) {
                        summaryMap[key].inward += baseQty;
                        summaryMap[key].closing += baseQty;
                    }
                });
            });

            // 2. Process Sales
            sales.forEach(inv => {
                let invDate = new Date(inv.date || inv.invoiceDate || inv.createdAt);
                let hdrWhName = getWarehouseName(inv.warehouse);
                
                (inv.items || []).forEach(line => {
                    let lineWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
                    let key = line.itemId + '|' + lineWhName;
                    if (!summaryMap[key]) summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    
                    let item = items.find(i => i.id === line.itemId);
                    let baseQty = line.customBaseQty !== undefined ? line.customBaseQty : ((parseFloat(line.quantity) || 0) * (line.customFactor || 1) * (item ? (item.conversionFactor || 1) : 1));
                    
                    if (invDate < startDate) {
                        summaryMap[key].opening -= baseQty;
                        summaryMap[key].closing -= baseQty;
                    } else if (invDate >= startDate && invDate <= endDate) {
                        summaryMap[key].outward += baseQty;
                        summaryMap[key].closing -= baseQty;
                    }
                });
            });

            let generated = [];
            for (let key in summaryMap) {
                let [itemId, warehouse] = key.split('|');
                let itemData = items.find(i => i.id === itemId);
                let cost = itemData && itemData.costPrice ? parseFloat(itemData.costPrice) : 0;
                
                generated.push({
                    itemId: itemId,
                    warehouse: warehouse,
                    opening: summaryMap[key].opening,
                    inward: summaryMap[key].inward,
                    outward: summaryMap[key].outward,
                    closing: summaryMap[key].closing,
                    cost: cost
                });
            }
            return generated;
        }

        function applyFilters() {
            let fWh = document.getElementById('filter-warehouse').value;
            let fSearch = document.getElementById('filter-search').value.toLowerCase().trim();

            let filteredData = invDb.filter(row => {
                let matchWh = (fWh === 'ALL' || row.warehouse === fWh);
                let itemData = itemsDb.find(i => i.id === row.itemId);
                let matchSearch = true;
                if (fSearch && itemData) {
                    let searchStr = `${itemData.code || ''} ${itemData.name || ''} ${itemData.barcode || ''}`.toLowerCase();
                    matchSearch = searchStr.includes(fSearch);
                }
                
                // Hide zero movement rows if everything is 0
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
                tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد حركات في هذه الفترة للبحث المحدد</td></tr>`;
            } else {
                data.forEach((row, index) => {
                    let item = itemsDb.find(i => i.id === row.itemId) || {};
                    let rowVal = row.closing * row.cost;
                    
                    itemCount.add(row.itemId);
                    totalQty += row.closing;
                    totalValue += rowVal;

                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td style="font-weight:700; color:#3b82f6;">${item.code || '-'}</td>
                        <td style="font-weight:700;">${item.name || 'غير معروف'}</td>
                        <td><span style="background:rgba(255,255,255,0.1); padding:3px 8px; border-radius:6px;">${row.warehouse}</span></td>
                        <td style="color:#6366f1; font-weight:800; text-align:center;">${row.opening.toLocaleString()}</td>
                        <td style="color:#10b981; font-weight:800; text-align:center;">${row.inward.toLocaleString()}</td>
                        <td style="color:#ef4444; font-weight:800; text-align:center;">${row.outward.toLocaleString()}</td>
                        <td style="color:#f59e0b; font-weight:800; font-size:1.1rem; text-align:center; background:rgba(245,158,11,0.05);">${row.closing.toLocaleString()}</td>
                        <td style="font-weight:700;">${rowVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // Update KPIs
            document.getElementById('kpi-count').innerHTML = `${itemCount.size} <span>صنف</span>`;
            document.getElementById('kpi-qty').innerHTML = `${totalQty.toLocaleString()} <span>وحدة</span>`;
            document.getElementById('kpi-value').innerHTML = `${totalValue.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})} <span>SDG</span>`;
        }
    </script>
</body>
</html>
