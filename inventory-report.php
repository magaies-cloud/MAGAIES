<?php $page_title = 'مقاييس (MAGAIEES) - تقارير وجرد المخازن'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مقاييس (MAGAIEES) - تقارير وجرد المخازن</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <style>
        :root {
            /* Theme: Pink & Emerald Dashboard */
            --bg-dark: #0a0e17;
            --panel-bg: rgba(16, 24, 39, 0.7);
            --pink-accent: #ec4899;
            --pink-glow: rgba(236, 72, 153, 0.4);
            --emerald-accent: #10b981;
            --emerald-glow: rgba(16, 185, 129, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Inter', 'Tajawal', sans-serif;
            margin: 0; padding: 0;
            background-image: radial-gradient(circle at top right, rgba(236,72,153,0.05), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(16,185,129,0.05), transparent 40%);
            min-height: 100vh;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }

        .dashboard-container {
            display: flex; flex-direction: column; gap: 20px;
            padding: 20px 30px; box-sizing: border-box; max-width: 1600px; margin: 0 auto;
        }

        /* Nav Tabs */
        .nav-tabs { display: flex; gap: 12px; margin-bottom: 5px; flex-wrap: wrap; }
        .nav-tab {
            padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 700;
            color: var(--text-secondary); background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border);
            transition: 0.3s; display: flex; align-items: center; gap: 8px; font-size: 0.85rem;
        }
        .nav-tab:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-tab.active { background: rgba(236,72,153,0.15); border-color: var(--pink-accent); color: var(--pink-accent); box-shadow: 0 0 15px var(--pink-glow); }

        /* Header Panel */
        .dash-header {
            display: flex; justify-content: space-between; align-items: center; padding: 15px 25px;
            background: var(--panel-bg); backdrop-filter: blur(20px); border-radius: 16px;
            border: 1px solid var(--glass-border); box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .dash-logo { display: flex; align-items: center; gap: 15px; }
        .dash-logo .icon {
            width: 45px; height: 45px; border-radius: 12px; background: linear-gradient(135deg, var(--pink-accent), #f472b6);
            display: flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 4px 15px var(--pink-glow);
        }
        .dash-logo h1 { margin: 0; font-size: 1.2rem; font-weight: 900; color: #fff; }
        .dash-logo p { margin: 0; font-size: 0.75rem; color: var(--text-secondary); }

        /* Controls */
        .dash-controls { display: flex; align-items: center; gap: 12px; }
        .btn-action {
            padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; border: none; transition: 0.3s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-sync { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
        .btn-sync:hover { background: rgba(56, 189, 248, 0.3); box-shadow: 0 0 15px rgba(56, 189, 248, 0.4); }
        .btn-print { background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--glass-border); }
        .btn-print:hover { background: rgba(255,255,255,0.15); }
        .btn-excel { background: linear-gradient(135deg, var(--emerald-accent), #34d399); color: #000; box-shadow: 0 4px 15px var(--emerald-glow); }
        .btn-excel:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--emerald-glow); }
        .btn-icon-nav { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
        .btn-icon-nav:hover { background: rgba(255,255,255,0.15); }

        /* KPI Cards */
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .kpi-card {
            background: var(--panel-bg); backdrop-filter: blur(15px); border-radius: 16px; padding: 20px;
            border: 1px solid var(--glass-border); display: flex; flex-direction: column; justify-content: center;
            position: relative; overflow: hidden; transition: 0.3s;
        }
        .kpi-card:hover { transform: translateY(-5px); }
        .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
        .kpi-card.c-pink::before { background: var(--pink-accent); box-shadow: 0 0 15px var(--pink-glow); }
        .kpi-card.c-emerald::before { background: var(--emerald-accent); box-shadow: 0 0 15px var(--emerald-glow); animation: breathGlowEmerald 3s infinite; }
        .kpi-card.c-blue::before { background: #38bdf8; box-shadow: 0 0 15px rgba(56, 189, 248, 0.4); }
        .kpi-card.c-amber::before { background: #fbbf24; box-shadow: 0 0 15px rgba(251, 191, 36, 0.4); }

        @keyframes breathGlowEmerald { 0%,100% { box-shadow: 0 0 5px var(--emerald-glow); } 50% { box-shadow: 0 0 25px var(--emerald-glow); } }
        @keyframes breathGlowAmber { 0%,100% { opacity: 0.8; } 50% { opacity: 1; text-shadow: 0 0 15px rgba(251,191,36,0.6); } }

        .kpi-title { font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .kpi-val { font-size: 2.2rem; font-weight: 900; margin: 0; display: flex; align-items: baseline; gap: 8px; }
        .kpi-val span { font-size: 1rem; font-weight: 600; color: var(--text-secondary); }
        .kpi-card.c-pink .kpi-val { color: var(--pink-accent); }
        .kpi-card.c-emerald .kpi-val { color: var(--emerald-accent); }
        .kpi-card.c-blue .kpi-val { color: #38bdf8; }
        .kpi-card.c-amber .kpi-val { color: #fbbf24; animation: breathGlowAmber 2s infinite; }

        /* Filters Bar */
        .filters-bar {
            background: var(--panel-bg); backdrop-filter: blur(15px); border-radius: 16px; padding: 20px;
            border: 1px solid var(--glass-border); display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        .f-group { display: flex; flex-direction: column; gap: 8px; }
        .f-group label { font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
        .f-input {
            background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
            color: #fff; padding: 12px 15px; font-size: 0.9rem; outline: none; transition: 0.3s; font-family: 'Inter', sans-serif;
        }
        .f-input:focus { border-color: var(--pink-accent); box-shadow: 0 0 0 3px rgba(236,72,153,0.15); background: rgba(0,0,0,0.5); }

        /* Data Table */
        .table-panel {
            background: var(--panel-bg); backdrop-filter: blur(15px); border-radius: 16px;
            border: 1px solid var(--glass-border); box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            overflow: hidden; display: flex; flex-direction: column;
        }
        .table-wrapper { overflow-x: auto; overflow-y: auto; max-height: 60vh; }
        .dash-table { width: 100%; border-collapse: collapse; min-width: 1100px; }
        .dash-table thead { position: sticky; top: 0; z-index: 10; }
        .dash-table th {
            background: rgba(10, 15, 25, 0.95); padding: 15px; text-align: right;
            font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); border-bottom: 2px solid var(--glass-border);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .dash-table td { padding: 15px; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: middle; transition: background 0.2s; }
        .dash-table tbody tr { animation: fadeSlideIn 0.4s ease-out forwards; opacity: 0; transform: translateY(10px); }
        .dash-table tbody tr:hover td { background: rgba(255,255,255,0.03); }

        @keyframes fadeSlideIn { to { opacity: 1; transform: translateY(0); } }

        /* Staggered animation classes */
        .delay-0 { animation-delay: 0.0s; } .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.1s; } .delay-3 { animation-delay: 0.15s; }
        .delay-4 { animation-delay: 0.2s; } .delay-5 { animation-delay: 0.25s; }
        
        .code-pill { background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px; font-family: monospace; font-size: 0.8rem; border: 1px solid rgba(255,255,255,0.1); }
        .qty-main { font-size: 1.2rem; font-weight: 900; }
        .qty-res { font-size: 0.9rem; font-weight: 800; color: #fbbf24; }
        .val-td { font-size: 1rem; font-weight: 800; color: var(--emerald-accent); direction: ltr; text-align: left; }
        
        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; }
        .status-ok { background: rgba(16, 185, 129, 0.15); color: var(--emerald-accent); border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-warn { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
        .status-danger { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); animation: pulseDanger 2s infinite; }
        @keyframes pulseDanger { 0%,100% { box-shadow: 0 0 0 rgba(239,68,68,0); } 50% { box-shadow: 0 0 10px rgba(239,68,68,0.5); } }

        .btn-link { background: rgba(236,72,153,0.1); color: var(--pink-accent); padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(236,72,153,0.3); transition: 0.3s; display: inline-block; }
        .btn-link:hover { background: var(--pink-accent); color: #fff; box-shadow: 0 0 15px var(--pink-glow); }

        @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .kpi-grid { grid-template-columns: 1fr; } .filters-bar { grid-template-columns: 1fr; } .dash-header { flex-direction: column; gap: 15px; text-align: center; } .dash-controls { flex-wrap: wrap; justify-content: center; } }


        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #fbbf24, #d97706); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 15px rgba(245, 158, 11, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2); }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.2rem; font-weight: 900; background: linear-gradient(to right, #fff, #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }
        .header-controls { display: flex; align-items: center; gap: 12px; }
        
        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); width: 36px; height: 36px; border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; position: relative; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 10px rgba(0,0,0,0.3); }
        .user-info { display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); }
    </style>
    <style>
        :root.light-theme {
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-secondary: #475569;
            --border-color: #e2e8f0;
        }
        :root.light-theme body { background-color: var(--bg-color); color: var(--text-main); }
        :root.light-theme .premium-header {
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(241,245,249,0.9));
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        :root.light-theme .header-center h2 { background: linear-gradient(to right, #0f172a, #334155); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        :root.light-theme .brand-text h1 { color: #0f172a; text-shadow: none; }
        :root.light-theme .brand-text p { color: #475569; text-shadow: none; }
        :root.light-theme .icon-btn, :root.light-theme .user-info, :root.light-theme .clock-container { background: rgba(255,255,255,0.8); color: #0f172a; border-color: rgba(0,0,0,0.1); }
        :root.light-theme .icon-btn:hover { background: #f8fafc; color: #000; box-shadow: 0 5px 10px rgba(0,0,0,0.1); }
        :root.light-theme #header-user-name { color: #0f172a !important; }
        :root.light-theme .online-users { background: rgba(16,185,129,0.1) !important; border-color: rgba(16,185,129,0.2) !important; }
    </style>
    <script>
        if(localStorage.getItem('erp_theme') === 'light') document.documentElement.classList.add('light-theme');
        function toggleTheme() {
            document.documentElement.classList.toggle('light-theme');
            localStorage.setItem('erp_theme', document.documentElement.classList.contains('light-theme') ? 'light' : 'dark');
        }
    </script>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>


    <div class="dashboard-container">

        <!-- Premium Header for Inventory -->
        <?php $header_theme_class = 'header-theme-inventory'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Navigation and Actions Row -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <div class="nav-tabs" style="margin-bottom: 0;">
                <a data-href="inventory-report.php" onclick="window.location.href='inventory-report.php'" class="nav-tab active">📦 جرد الأرصدة العام</a>
                <a data-href="item-ledger.php" onclick="window.location.href='item-ledger.php'" class="nav-tab">📑 حركة الصنف</a>
                <a data-href="warehouse-ledger.php" onclick="window.location.href='warehouse-ledger.php'" class="nav-tab">🏭 حركة المخزن</a>
            </div>

            <div class="dash-controls" style="margin: 0; display: flex; gap: 8px;">
                <button class="btn-action btn-sync" onclick="loadData(); applyFilters();" title="إعادة الحساب ومطابقة المشتريات والمبيعات">🔄 تحديث الأرصدة</button>
                <button class="btn-action btn-print" onclick="previewReport()">👁️ معاينة</button>
                <button class="btn-action btn-print" onclick="window.print()">🖨️ طباعة</button>
                <button class="btn-action btn-print" onclick="window.openExportModal(exportExcel, {element: document.getElementById('report-table-wrap'), filename: 'inventory-report.pdf', orientation: 'landscape'})">📥 تصدير</button>
                <button class="btn-action" onclick="runAIInventory()" style="background: linear-gradient(135deg, #ec4899, #be185d); color: white; border: none; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.4);">🤖 محلل المخزون (AI)</button>
    
                <button class="btn-icon-nav" onclick="history.back()" title="رجوع" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; color: #fff; cursor: pointer; transition: 0.3s;">↩</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <div class="f-group">
                <label>بحث بالاسم أو الكود</label>
                <input type="text" class="f-input" id="filter-search" placeholder="اكتب للبحث السريع..." onkeyup="applyFilters()">
            </div>
            <div class="f-group">
                <label>تصفية بالمخزن</label>
                <select class="f-input" id="filter-warehouse" onchange="applyFilters()">
                    <option value="ALL">جميع المخازن (الكل)</option>
                </select>
            </div>
            <div class="f-group">
                <label>تصفية بالفئة</label>
                <select class="f-input" id="filter-category" onchange="applyFilters()">
                    <option value="ALL">جميع الفئات</option>
                </select>
            </div>
            <div class="f-group">
                <label>حالة الرصيد</label>
                <select class="f-input" id="filter-status" onchange="applyFilters()">
                    <option value="ALL">الكل (بدون تصفية)</option>
                    <option value="OK">متوفر (آمن)</option>
                    <option value="WARNING">تحت حد الطلب</option>
                    <option value="DANGER">نافذ (صفر أو سالب)</option>
                </select>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card c-blue">
                <div class="kpi-title">إجمالي الأصناف بالتقرير <span>📋</span></div>
                <div class="kpi-val" id="kpi-count">0 <span>صنف</span></div>
            </div>
            <div class="kpi-card c-pink">
                <div class="kpi-title">إجمالي كميات المخزون (وحدة) <span>⚖️</span></div>
                <div class="kpi-val" id="kpi-qty">0 <span>وحدة</span></div>
            </div>
            <div class="kpi-card c-emerald">
                <div class="kpi-title">إجمالي القيمة التقديرية الحية <span>💰</span></div>
                <div class="kpi-val" id="kpi-value">0 <span>SDG</span></div>
            </div>
            <div class="kpi-card c-amber">
                <div class="kpi-title">تنبيهات نواقص (تتطلب شراء) <span>⚠️</span></div>
                <div class="kpi-val" id="kpi-alerts">0 <span>تنبيه</span></div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-panel">
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">كود الصنف</th>
                            <th>الاسم والمواصفات</th>
                            <th>الفئة</th>
                            <th>المخزن</th>
                            <th style="text-align: center;">الرصيد الفعلي</th>
                            <th style="text-align: center;">الرصيد المحجوز</th>
                            <th style="text-align: center;">المتاح للبيع</th>
                            <th>متوسط التكلفة/السعر</th>
                            <th>إجمالي القيمة</th>
                            <th>حالة المخزون</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-tbody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054967.79042"></script>
    <script>
        const ITEMS_STORAGE_KEY = 'erp_items_v1';
        const INVENTORY_STORAGE_KEY = 'erp_inventory_v1';

        let itemsDb = [];
        let invDb = [];

        document.addEventListener('DBReady', () => {
            populateWarehouseFilter();
            loadData();
            populateCategoryFilter();
            applyFilters();
        });

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
            itemsDb = JSON.parse(localStorage.getItem(ITEMS_STORAGE_KEY)) || [];
            let storedInv = buildRealInventory(itemsDb);
            
            // Ensure all items are listed even if 0
            itemsDb.forEach(item => {
                let hasStock = storedInv.some(inv => inv.itemId === item.id);
                if (!hasStock) {
                    storedInv.push({
                        itemId: item.id,
                        warehouse: 'المخزن الرئيسي',
                        qty: 0,
                        reservedQuantity: 0,
                        minQty: item.reorderLevel || 10
                    });
                }
            });
            invDb = storedInv;
        }

        // The core accounting & inventory calculation engine
        function buildRealInventory(items) {
            let actualStockMap = {}; 
            let reservedStockMap = {};

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            warehousesDb.unshift({ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' });
            let getWarehouseName = (id) => {
                let w = warehousesDb.find(w => w.id === id || w.name === id);
                return w ? w.name : 'المخزن الرئيسي';
            };

            let defaultWhName = warehousesDb.length > 0 ? warehousesDb[0].name : 'المخزن الرئيسي';
            items.forEach(item => {
                let key = item.id + '|' + defaultWhName;
                actualStockMap[key] = 0;
                reservedStockMap[key] = 0;
            });

            // 1. ADD Purchases (Inward)
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            purchases.forEach(inv => {
                let whName = getWarehouseName(inv.warehouseId || inv.warehouse);
                (inv.items || inv.lines || []).forEach(line => {
                    let key = (line.itemId || line.productId) + '|' + whName;
                    let item = items.find(i => i.id === (line.itemId || line.productId));
                    let baseQty = line.customBaseQty !== undefined ? line.customBaseQty : ((parseFloat(line.quantity) || 0) * (line.customFactor || 1) * (item ? (item.conversionFactor || 1) : 1));
                    actualStockMap[key] = (actualStockMap[key] || 0) + baseQty;
                });
            });

            // 2. SUBTRACT Sales (Outward)
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
            sales.forEach(inv => {
                let hdrWhName = getWarehouseName(inv.warehouse);
                (inv.items || []).forEach(line => {
                    let lineWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
                    let key = line.itemId + '|' + lineWhName;
                    let item = items.find(i => i.id === line.itemId);
                    let baseQty = line.customBaseQty !== undefined ? line.customBaseQty : ((parseFloat(line.quantity) || 0) * (line.customFactor || 1) * (item ? (item.conversionFactor || 1) : 1));

                    if (inv.saleType === 'reserved') {
                        reservedStockMap[key] = (reservedStockMap[key] || 0) + baseQty;
                    } else {
                        actualStockMap[key] = (actualStockMap[key] || 0) - baseQty;
                    }
                });
            });

            let generated = [];
            for (let key in actualStockMap) {
                let [itemId, warehouse] = key.split('|');
                let itemData = items.find(i => i.id === itemId);
                let minQty = itemData && itemData.reorderLevel ? parseFloat(itemData.reorderLevel) : 10;
                
                generated.push({
                    itemId: itemId,
                    warehouse: warehouse,
                    qty: actualStockMap[key],
                    reservedQuantity: reservedStockMap[key] || 0,
                    minQty: minQty
                });
            }
            return generated;
        }

        function populateCategoryFilter() {
            const select = document.getElementById('filter-category');
            const cats = [...new Set(itemsDb.map(i => i.category).filter(c => c))];
            if(cats.length === 0) cats.push('مواد بناء');
            cats.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat; option.text = cat; select.appendChild(option);
            });
        }

        function applyFilters() {
            const fWh = document.getElementById('filter-warehouse').value;
            const fCat = document.getElementById('filter-category').value;
            const fStatus = document.getElementById('filter-status').value;
            const fSearch = document.getElementById('filter-search').value.toLowerCase().trim();

            const tbody = document.getElementById('inventory-tbody');
            tbody.innerHTML = '';

            let totalItems = 0; let totalQty = 0; let totalValue = 0; let totalAlerts = 0;

            let reportData = [];
            invDb.forEach(invRecord => {
                const item = itemsDb.find(i => i.id === invRecord.itemId);
                if(item) {
                    let baseUnit = item.units && item.units.length > 0 ? item.units[0].unitName : (item.unit || 'وحدة');
                    let price = item.units && item.units.length > 0 ? item.units[0].cashPrice : (item.price || 0);
                    reportData.push({
                        ...invRecord, itemName: item.name || 'بدون اسم', category: item.category || 'عام',
                        unit: baseUnit, price: parseFloat((price || 0).toString().replace(/,/g, '')) || 0
                    });
                }
            });

            const filteredData = reportData.filter(row => {
                if (fWh !== 'ALL' && row.warehouse !== fWh) return false;
                if (fCat !== 'ALL' && row.category !== fCat) return false;
                if (fSearch && !row.itemName.toLowerCase().includes(fSearch) && !row.itemId.toLowerCase().includes(fSearch)) return false;
                
                let availQty = (row.qty || 0) - (row.reservedQuantity || 0);
                let statusInfo = getStatusInfo(availQty, row.minQty);
                if (fStatus !== 'ALL' && statusInfo.code !== fStatus) return false;
                
                return true;
            });

            filteredData.forEach((row, index) => {
                let actualQty = row.qty || 0;
                let reservedQty = row.reservedQuantity || 0;
                let availQty = actualQty - reservedQty;
                
                let statusInfo = getStatusInfo(availQty, row.minQty);
                let rowValue = availQty * row.price;

                totalItems++; totalQty += availQty; totalValue += rowValue;
                if (statusInfo.code !== 'OK') totalAlerts++;

                const tr = document.createElement('tr');
                tr.className = `delay-${index % 6}`; // Staggered animation classes
                tr.innerHTML = `
                    <td><span class="code-pill">${row.itemId}</span></td>
                    <td style="font-weight:700; color:#fff;">${row.itemName} <div style="font-size:0.7rem; color:var(--text-secondary); margin-top:4px;">وحدة التقييم: ${row.unit}</div></td>
                    <td>${row.category}</td>
                    <td>${row.warehouse}</td>
                    <td style="text-align:center; color:#38bdf8;" class="qty-main">${actualQty.toLocaleString()}</td>
                    <td style="text-align:center;" class="qty-res">${reservedQty > 0 ? reservedQty.toLocaleString() : '-'}</td>
                    <td style="text-align:center; color:${statusInfo.color};" class="qty-main">${availQty.toLocaleString()}</td>
                    <td>${row.price.toLocaleString()}</td>
                    <td class="val-td">${rowValue.toLocaleString()}</td>
                    <td><span class="status-badge ${statusInfo.cssClass}">${statusInfo.icon} ${statusInfo.text}</span></td>
                    <td><a data-href="item-ledger.php?item=${row.itemId}" onclick="window.location.href='item-ledger.php?item=${row.itemId}'" class="btn-link">حركة الصنف</a></td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('kpi-count').innerHTML = `${totalItems.toLocaleString()} <span>صنف</span>`;
            document.getElementById('kpi-qty').innerHTML = `${totalQty.toLocaleString()} <span>وحدة</span>`;
            document.getElementById('kpi-value').innerHTML = `${totalValue.toLocaleString()} <span>SDG</span>`;
            document.getElementById('kpi-alerts').innerHTML = `${totalAlerts.toLocaleString()} <span>تنبيه</span>`;

            if (filteredData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding:50px; color:var(--text-secondary); font-size:1.1rem;">لم يتم العثور على أرصدة تطابق الفلاتر المختارة</td></tr>`;
            }
        }

        function getStatusInfo(qty, minQty) {
            if (qty <= 0) return { code: 'DANGER', text: 'نافذ / سالب', color: '#ef4444', cssClass: 'status-danger', icon: '🚨' };
            if (qty <= minQty) return { code: 'WARNING', text: 'تحت الطلب', color: '#fbbf24', cssClass: 'status-warn', icon: '⚠️' };
            return { code: 'OK', text: 'رصيد آمن', color: 'var(--emerald-accent)', cssClass: 'status-ok', icon: '✅' };
        }

        function exportExcel() {
            alert('وظيفة تصدير Excel قيد التطوير!');
        }
    </script>

        <!-- AI Inventory Modal -->
        <div id="aiInv-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; direction: rtl; backdrop-filter: blur(8px);">
            <div class="glass-panel" style="width: 95%; max-width: 800px; max-height: 85vh; overflow-y: auto; padding: 30px; position: relative; border: 1px solid rgba(236, 72, 153, 0.4); background: #0f172a; border-radius: 16px;">
                <button onclick="document.getElementById('aiInv-modal').style.display='none'" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #94a3b8; font-size: 1.8rem; cursor: pointer; transition: 0.3s;" onmouseover="this.style.color='#ec4899'" onmouseout="this.style.color='#94a3b8'">&times;</button>
                <h3 style="color: #ec4899; margin-bottom: 25px; font-weight: 900; border-bottom: 1px solid rgba(236, 72, 153, 0.2); padding-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.8rem;">🤖</span> مستشار المخزون الذكي (AI Forecaster)
                </h3>
                <div id="aiInv-content" style="color: #f8fafc; line-height: 1.9; font-size: 1.05rem;">
                    <div style="text-align: center; padding: 40px 0;">
                        <div style="font-size: 3rem; margin-bottom: 15px; animation: pulse 1.5s infinite;">📦</div>
                        <div style="color: #94a3b8;">جاري جرد وتقييم الأصناف... يرجى الانتظار</div>
                    </div>
                </div>
            </div>
        </div>
        <style>@keyframes pulse { 0% { transform: scale(0.95); opacity: 0.8; } 50% { transform: scale(1.05); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.8; } }</style>

    <script>
    function runAIInventory() {
        document.getElementById('aiInv-modal').style.display = 'flex';
        document.getElementById('aiInv-content').innerHTML = `
            <div style="text-align: center; padding: 40px 0;">
                <div style="font-size: 3rem; margin-bottom: 15px; animation: pulse 1.5s infinite;">📦</div>
                <div style="color: #94a3b8;">جاري جرد وتقييم الأصناف لمعرفة التكدس والنواقص... يرجى الانتظار</div>
            </div>`;
        
        let inventoryData = [];
        let rows = document.querySelectorAll('#report-tbody tr');
        let limit = Math.min(rows.length, 100); 
        
        for(let i=0; i<limit; i++) {
            let cols = rows[i].querySelectorAll('td');
            if(cols.length >= 6) {
                let name = cols[1].innerText.trim();
                let qty = cols[2].innerText.trim();
                let cost = cols[3].innerText.trim();
                let totalCost = cols[5].innerText.trim();
                if(name) {
                    inventoryData.push(`الصنف: ${name} | الكمية المتوفرة: ${qty} | سعر التكلفة: ${cost} | الإجمالي: ${totalCost}`);
                }
            }
        }

        if(inventoryData.length === 0) {
            document.getElementById('aiInv-content').innerHTML = '<span style="color:#ef4444">الجدول فارغ أو لا توجد أرصدة للتحليل.</span>';
            return;
        }

        let context = "تقرير جرد المخزون الحالي:\n" + inventoryData.join("\n");

        let promptMsg = "أنت مستشار إدارة مخزون وسلاسل إمداد (Supply Chain & Inventory Advisor). قم بقراءة تقرير الجرد المرفق واستخرج التالي:\n1. ⚠️ الأصناف التي تقترب من النفاذ (أرصدة منخفضة جداً).\n2. 📦 الأصناف المكدسة (أرصدة عالية جداً وقيمتها النقدية مجمدة كبير).\n3. 💡 مقترحات سريعة لتسييل البضاعة المكدسة (عروض ترويجية، تجميع أصناف).\nاكتب التقرير بشكل احترافي، مباشر، ومنسق بنقاط وأيقونات.";

        fetch('api/ai-assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: promptMsg, context: context })
        })
        .then(res => res.json())
        .then(data => {
            if (data.reply) {
                let formatted = data.reply
                    .replace(/\*\*(.*?)\*\*/g, '<strong style="color:#ec4899">$1</strong>')
                    .replace(/⚠️/g, '<span style="font-size:1.2em; color:#f59e0b;">⚠️</span>')
                    .replace(/📦/g, '<span style="font-size:1.2em; color:#3b82f6;">📦</span>')
                    .replace(/💡/g, '<span style="font-size:1.2em; color:#10b981;">💡</span>')
                    .replace(/\n/g, '<br>');
                document.getElementById('aiInv-content').innerHTML = formatted;
            } else {
                document.getElementById('aiInv-content').innerHTML = '<span style="color:#ef4444">حدث خطأ أثناء التحليل.</span>';
            }
        })
        .catch(err => {
            document.getElementById('aiInv-content').innerHTML = '<span style="color:#ef4444">تعذر الاتصال بالخادم.</span>';
        });
    }
    </script>
    \n</body>
</html>

