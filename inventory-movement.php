<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() === 0) { @ob_start('ob_gzhandler'); } ?>
<?php $page_title = 'المخازن'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>السيطرة (MAGAIEES) - تقارير وجرد المخازن</title>
    <link rel="stylesheet" href="css/design-system.css?v=20261029_fast">
    <link rel="stylesheet" href="css/theme.css?v=20261029_fast">
    <link rel="stylesheet" href="css/components.css?v=20261029_fast">
    <style>
        :root {
            /* World-Class Executive ERP Theme */
            --bg-dark: #090d16;
            --surface-card: rgba(15, 23, 42, 0.75);
            --surface-card-hover: rgba(30, 41, 59, 0.85);
            --panel-bg: rgba(15, 23, 42, 0.75);
            --cyan-accent: #06b6d4;
            --sky-accent: #38bdf8;
            --pink-accent: #f43f5e;
            --emerald-accent: #10b981;
            --amber-accent: #f59e0b;
            --purple-accent: #8b5cf6;
            --glass-border: rgba(255, 255, 255, 0.09);
            --glass-border-subtle: rgba(255, 255, 255, 0.05);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
        }

        :root.light-theme {
            --bg-dark: #f8fafc;
            --surface-card: #ffffff;
            --surface-card-hover: #f1f5f9;
            --panel-bg: rgba(255, 255, 255, 0.95);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --glass-border: rgba(0, 0, 0, 0.08);
            --glass-border-subtle: rgba(0, 0, 0, 0.04);
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Inter', 'Tajawal', sans-serif;
            margin: 0; padding: 0;
            background-image: radial-gradient(circle at 90% 10%, rgba(56, 189, 248, 0.04), transparent 40%),
                              radial-gradient(circle at 10% 90%, rgba(16, 185, 129, 0.04), transparent 40%);
            min-height: 100vh;
            letter-spacing: -0.01em;
        }

        /* Custom Modern Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.25); }

        .dashboard-container {
            display: flex; flex-direction: column; gap: 8px;
            padding: 8px 18px; box-sizing: border-box; max-width: 1720px; margin: 0 auto;
        }

        /* Navigation Bar (Ultra-Compact Segmented Clusters) */
        .nav-tabs { 
            display: flex; gap: 5px; flex-wrap: wrap; overflow: visible;
            background: var(--surface-card); padding: 4px 6px; border-radius: 12px; 
            border: 1px solid var(--glass-border); box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            backdrop-filter: blur(20px); align-items: center; width: 100%; z-index: 100; position: relative;
        }
        .nav-tabs::-webkit-scrollbar { display: none; }
        .nav-cluster {
            display: flex; align-items: center; gap: 3px;
        }
        .nav-divider {
            width: 1px; height: 18px; background: rgba(255, 255, 255, 0.1); margin: 0 3px; flex-shrink: 0;
        }
        .nav-tab {
            padding: 5px 12px; border-radius: 8px; text-decoration: none; font-weight: 700;
            color: var(--text-secondary); background: transparent; border: 1px solid transparent;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 5px; font-size: 0.82rem;
            position: relative; white-space: nowrap; flex-shrink: 0; cursor: pointer;
        }
        .nav-tab:hover {
            color: #fff; background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.08);
            transform: translateY(-1px);
        }
        .nav-tab.active {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(37, 99, 235, 0.15));
            border-color: rgba(56, 189, 248, 0.4); color: #38bdf8;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.2);
        }

        /* AI Suite Special Pill */
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
            background: #ec4899; color: #fff; font-size: 0.65rem; padding: 1px 4px; border-radius: 8px; font-weight: 900;
        }

        /* Nav Dropdown for Extra Reports */
        .nav-dropdown { position: relative; display: inline-block; }
        .nav-dropdown-content {
            display: none; position: absolute; top: calc(100% + 6px); right: 0; min-width: 240px;
            background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border); border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.65); padding: 5px; z-index: 1000;
            animation: dropdownFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes dropdownFadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .nav-dropdown:hover .nav-dropdown-content, .nav-dropdown:focus-within .nav-dropdown-content { display: block; }
        .nav-dropdown-item {
            display: flex; align-items: center; gap: 8px; padding: 8px 12px; color: #cbd5e1;
            text-decoration: none; font-size: 0.84rem; font-weight: 700; border-radius: 8px; transition: all 0.2s ease;
        }
        .nav-dropdown-item:hover { background: rgba(236, 72, 153, 0.15); color: #f472b6; transform: translateX(-3px); }

        /* Unified Filter Hub (Ultra-Compact Single Unit) */
        .filters-hub {
            background: var(--surface-card); backdrop-filter: blur(20px); border-radius: 12px;
            border: 1px solid var(--glass-border); padding: 8px 12px; display: flex; flex-direction: column; gap: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.18);
        }
        .filters-main-row {
            display: grid; grid-template-columns: 1.8fr 1fr 1fr 1.2fr; gap: 8px; align-items: center;
        }
        .f-search-wrapper { position: relative; width: 100%; display: flex; align-items: center; }
        .f-search-icon { position: absolute; right: 10px; font-size: 0.9rem; color: #64748b; pointer-events: none; }
        .f-search-clear {
            position: absolute; left: 8px; font-size: 0.8rem; color: #64748b; cursor: pointer;
            background: rgba(255,255,255,0.08); border-radius: 50%; width: 18px; height: 18px;
            display: none; align-items: center; justify-content: center; transition: 0.2s;
        }
        .f-search-clear:hover { background: rgba(255,255,255,0.2); color: #fff; }
        .f-input {
            background: rgba(0,0,0,0.25); border: 1px solid var(--glass-border); border-radius: 8px;
            color: var(--text-primary); padding: 8px 34px 8px 12px; font-size: 0.85rem; outline: none; transition: 0.2s;
            font-family: inherit; width: 100%; box-sizing: border-box; height: 38px; font-weight: 600;
        }
        .f-input:focus { border-color: #38bdf8; box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.15); background: rgba(0,0,0,0.4); }
        .f-select {
            padding: 0 14px; cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: left 12px center; background-size: 14px;
        }
        .f-select option { background: #0f172a; color: #f8fafc; }

        /* Quick Filter Pills Row */
        .quick-pills-row {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding-top: 4px;
            border-top: 1px solid var(--glass-border-subtle);
        }
        .quick-pill-label { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
        .quick-pill {
            padding: 4px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; cursor: pointer;
            background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); color: var(--text-secondary);
            transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 5px;
        }
        .quick-pill:hover { background: rgba(255,255,255,0.08); color: #fff; transform: translateY(-1px); }
        .quick-pill.active { background: rgba(56, 189, 248, 0.15); border-color: rgba(56, 189, 248, 0.4); color: #38bdf8; font-weight: 800; }

        /* Executive Data Table (Maximized Visible Height) */
        .table-panel {
            background: var(--surface-card); backdrop-filter: blur(20px); border-radius: 14px;
            border: 1px solid var(--glass-border); box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            overflow: hidden; display: flex; flex-direction: column;
        }
        .table-wrapper { overflow-x: auto; overflow-y: auto; max-height: 64vh; scrollbar-width: thin; }
        .dash-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1100px; }
        .dash-table thead { position: sticky; top: 0; z-index: 10; }
        .dash-table th {
            background: rgba(15, 23, 42, 0.98); padding: 11px 14px; text-align: right;
            font-size: 0.74rem; font-weight: 800; color: var(--text-secondary); border-bottom: 2px solid var(--glass-border);
            text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; backdrop-filter: blur(10px);
        }
        .dash-table td {
            padding: 10px 14px; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border-subtle);
            vertical-align: middle; transition: all 0.15s ease;
        }
        .dash-table tbody tr {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .dash-table tbody tr:hover td {
            background: rgba(56, 189, 248, 0.03);
        }
        .dash-table tbody tr:hover td:first-child {
            box-shadow: inset 3px 0 0 #38bdf8;
        }

        .code-pill {
            background: rgba(255,255,255,0.04); padding: 4px 8px; border-radius: 6px;
            font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; border: 1px solid var(--glass-border);
            color: #94a3b8; font-weight: 700; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 4px;
        }
        .code-pill:hover { background: rgba(56,189,248,0.15); color: #38bdf8; border-color: rgba(56,189,248,0.3); }

        .qty-badge-dual {
            display: inline-flex; flex-direction: column; align-items: center; gap: 3px;
        }
        .qty-val-main { font-size: 1.15rem; font-weight: 900; }
        .qty-sub-unit {
            background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);
            padding: 2px 8px; border-radius: 6px; font-weight: 800; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 4px;
        }

        .val-td { font-size: 0.95rem; font-weight: 900; color: #10b981; direction: ltr; text-align: left; }
        
        .status-badge {
            padding: 5px 12px; border-radius: 20px; font-size: 0.76rem; font-weight: 800;
            display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
        }
        .status-ok { background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); }
        .status-warn { background: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); }
        .status-danger { background: rgba(244, 63, 94, 0.12); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.25); }

        .btn-link {
            background: rgba(255, 255, 255, 0.04); color: #cbd5e1; padding: 6px 12px; border-radius: 8px;
            text-decoration: none; font-size: 0.78rem; font-weight: 700; border: 1px solid var(--glass-border);
            transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-link:hover {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(37, 99, 235, 0.2));
            color: #38bdf8; border-color: rgba(56, 189, 248, 0.4); transform: translateY(-1px);
        }

        /* Glass Action Island (Under Table) */
        .action-island {
            display: flex; justify-content: space-between; align-items: center; background: var(--surface-card);
            border: 1px solid var(--glass-border); border-radius: 14px; padding: 10px 18px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.25); backdrop-filter: blur(20px); flex-wrap: wrap; gap: 10px;
        }
        .btn-action-tool {
            padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 0.84rem; cursor: pointer;
            border: 1px solid transparent; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex; align-items: center; gap: 7px; color: #fff;
        }
        .btn-action-tool:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3); }

        /* Summary Strip Hub (Clean Linear Summary Bar - No Bulky Boxes) */
        .summary-strip-hub {
            background: var(--surface-card); backdrop-filter: blur(20px); border-radius: 14px;
            border: 1px solid var(--glass-border); padding: 12px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px; margin-bottom: 8px;
        }
        .summary-strip-item {
            display: flex; align-items: center; gap: 12px; flex: 1; min-width: 200px; justify-content: center;
        }
        .summary-strip-icon {
            width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; flex-shrink: 0; border: 1px solid var(--glass-border);
        }
        .summary-strip-info {
            display: flex; flex-direction: column; gap: 2px;
        }
        .summary-strip-label {
            font-size: 0.76rem; font-weight: 700; color: var(--text-secondary);
        }
        .summary-strip-value {
            font-size: 1.45rem; font-weight: 900; line-height: 1.1; display: flex; align-items: baseline; gap: 6px;
        }
        .summary-strip-value span {
            font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);
        }
        .summary-strip-divider {
            width: 1px; height: 36px; background: rgba(255, 255, 255, 0.08); flex-shrink: 0;
        }
        :root.light-theme .summary-strip-divider { background: rgba(0, 0, 0, 0.08); }

        @media (max-width: 1200px) {
            .filters-main-row { grid-template-columns: 1fr 1fr; }
            .summary-strip-divider { display: none; }
        }
        @media (max-width: 768px) {
            .filters-main-row { grid-template-columns: 1fr; }
            .action-island { flex-direction: column; align-items: stretch; }
            .summary-strip-hub { flex-direction: column; align-items: stretch; }
        }
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

        :root.light-theme .online-users { background: rgba(16,185,129,0.1) !important; border-color: rgba(16,185,129,0.2) !important; }
    </style>
    
</head>
<body class="no-page-transition">

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

        <!-- Top Navigation Row (Clustered Smart Hub) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; gap: 10px; flex-wrap: nowrap;">
            <div class="nav-tabs" style="margin-bottom: 0;">
                
                <!-- Cluster 1: الجرد وتسوية الأرصدة -->
                <div class="nav-cluster">
                    <a href="inventory-report.php" class="nav-tab active" title="نظام الجرد الشامل بجميع المخازن">
                        <span>📦</span> <span>الجرد الشامل للمخازن</span>
                    </a>
                    <a href="javascript:void(0)" onclick="openStocktakeModal()" class="nav-tab" title="تعديل الأرصدة والتسوية الجردية">
                        <span>⚙️</span> <span>تسوية المخازن</span>
                    </a>
                </div>

                <div class="nav-divider"></div>

                <!-- Cluster 2: حركات البضائع -->
                <div class="nav-cluster">
                    <a href="item-ledger.php" class="nav-tab" title="كشف حركة صنف محدد">
                        <span>📑</span> <span>حركة الأصناف</span>
                    </a>
                    <a href="warehouse-ledger.php" class="nav-tab" title="كشف حركة مخزن محدد">
                        <span>🏭</span> <span>حركة المخازن</span>
                    </a>
                    <a href="inventory-summary.php" class="nav-tab" title="ملخص الوارد والمنصرف">
                        <span>📊</span> <span>ملخص الحركات</span>
                    </a>
                </div>

                <div class="nav-divider"></div>

                <!-- Cluster 3: مركز الذكاء الاصطناعي (AI Suite) -->
                <div class="nav-cluster">
                    <a href="ai_goods_advisor.php" class="nav-tab" title="توصيات الشراء واستراتيجيات التخزين">
                        <span>💡</span> <span>مستشار الـ AI للبضائع</span> <span class="nav-ai-badge" style="background:var(--pink-accent);color:#fff;padding:2px 5px;border-radius:4px;font-size:0.6rem;margin-right:4px;">AI</span>
                    </a>
                    <a href="smart_inventory_report.php" class="nav-tab" title="تقارير تفاعلية ذكية للمخزون">
                        <span>🧠</span> <span>تقارير المخازن الذكية</span>
                    </a>
                    <a href="javascript:void(0)" onclick="runAIInventory()" class="nav-tab" title="تحليل فوري للتكدس والنواقص">
                        <span>⚡</span> <span>محلل المخازن الـ AI</span>
                    </a>
                </div>

            </div>

            <div>
                <button class="btn-icon-nav" onclick="history.back()" title="رجوع" style="background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; color: var(--text-main); cursor: pointer; transition: 0.25s;">↩</button>
            </div>
        </div>

        <!-- Unified Filter Hub (Single Line + Quick Filter Chips) -->
        <div class="filters-hub">
            <div class="filters-main-row">
                <div class="f-search-wrapper">
                    <span class="f-search-icon">🔍</span>
                    <input type="text" class="f-input" id="filter-search" placeholder="بحث بالاسم أو الكود (اكتب للبحث الفوري...)" oninput="onSearchInput(this)">
                    <span class="f-search-clear" id="search-clear-btn" onclick="clearSearch()" title="مسح">✕</span>
                </div>
                <div>
                    <select class="f-input f-select" id="filter-warehouse" onchange="applyFilters()">
                        <option value="ALL">🏢 جميع المخازن (الجرد العام)</option>
                    </select>
                </div>
                <div>
                    <select class="f-input f-select" id="filter-category" onchange="applyFilters()">
                        <option value="ALL">🏷️ تصفية بالفئة (جميع الفئات)</option>
                    </select>
                </div>
                <div>
                    <select class="f-input f-select" id="filter-status" onchange="syncStatusFromSelect()">
                        <option value="ACTIVE" selected>📊 حالة الرصيد: بها بضائع فقط</option>
                        <option value="ALL_WITH_ZERO">📊 حالة الرصيد: عرض الكل (بما فيها الصفرية)</option>
                        <option value="OK">📊 حالة الرصيد: متوفر (رصيد آمن)</option>
                        <option value="WARNING">📊 حالة الرصيد: تحت حد الطلب</option>
                        <option value="DANGER">📊 حالة الرصيد: نافذ / سالب</option>
                    </select>
                </div>
            </div>

            <!-- Quick Status Filter Pills -->
            <div class="quick-pills-row">
                <span class="quick-pill-label"><span>⚡</span> التصفية السريعة:</span>
                <button class="quick-pill" onclick="setQuickFilter('ALL_WITH_ZERO', this)" type="button">عرض الكل (شامل الصفر)</button>
                <button class="quick-pill active" onclick="setQuickFilter('ACTIVE', this)" type="button"><span>📦</span> الأصناف المتوفرة فقط</button>
                <button class="quick-pill" onclick="setQuickFilter('OK', this)" type="button"><span>🟢</span> رصيد آمن</button>
                <button class="quick-pill" onclick="setQuickFilter('WARNING', this)" type="button"><span>⚠️</span> تحت حد الطلب</button>
                <button class="quick-pill" onclick="setQuickFilter('DANGER', this)" type="button"><span>🚨</span> نواقص / نافذ</button>
                <span id="filtered-matches-badge" style="margin-right: auto; font-size: 0.78rem; font-weight: 700; color: var(--text-muted); background: rgba(255,255,255,0.04); padding: 3px 10px; border-radius: 20px; border: 1px solid var(--glass-border);">0 صنف</span>
            </div>
        </div>

        <!-- Dual View Mode Bar: Multi-Unit Balances vs Chronological Goods Movements -->
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button type="button" class="im-mode-btn active" onclick="switchInventoryMovementView('balances', this)" style="padding:9px 18px; border-radius:10px; font-weight:900; font-size:0.86rem; cursor:pointer; border:1px solid #38bdf8; background:linear-gradient(135deg, #0284c7, #2563eb); color:#fff; display:inline-flex; align-items:center; gap:7px;">
                    <span>📦</span> <span>أرصدة وحركات البضائع بالمخازن (متعدد الوحدات)</span>
                </button>
                <button type="button" class="im-mode-btn" onclick="switchInventoryMovementView('log', this)" style="padding:9px 18px; border-radius:10px; font-weight:900; font-size:0.86rem; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1; display:inline-flex; align-items:center; gap:7px;">
                    <span>🔄</span> <span>سجل حركات البضائع التفصيلي (وارد / منصرف / مرتجع / مناقلة / تسوية)</span>
                </button>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <a href="inventory-adjustment.php?tab=transfer" class="btn-link" style="background:rgba(56,189,248,0.12); color:#38bdf8; border-color:rgba(56,189,248,0.3); padding:7px 14px;">🔄 مناقلة بين المخازن</a>
                <a href="inventory-adjustment.php" class="btn-link" style="background:rgba(249,115,22,0.12); color:#fb923c; border-color:rgba(249,115,22,0.3); padding:7px 14px;">⚖️ تسوية جردية شاملة</a>
                <a href="inventory-analytics.php" class="btn-link" style="background:rgba(244,114,182,0.12); color:#f472b6; border-color:rgba(244,114,182,0.3); padding:7px 14px;">📈 التحليلات المرئية و ABC</a>
            </div>
        </div>

        <div id="im-balances-panel">
        <!-- 1. Data Table (Primary Focus - Top of Screen) -->
        <div class="table-panel">
            <div class="table-wrapper" id="report-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th style="width: 110px;">كود الصنف</th>
                            <th>الاسم والمواصفات</th>
                            <th>الفئة</th>
                            <th>المخزن</th>
                            <th style="text-align: center;">الرصيد الفعلي</th>
                            <th style="text-align: center;">الرصيد المحجوز</th>
                            <th style="text-align: center;">المتاح للبيع</th>
                            <th>متوسط السعر</th>
                            <th>إجمالي القيمة</th>
                            <th>حالة المخزون</th>
                            <th style="text-align: center;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-tbody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        </div><!-- /#im-balances-panel -->

        <!-- 1B. Chronological Goods Movement Log Panel -->
        <div id="im-log-panel" style="display:none; margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:8px; background:var(--surface-card); padding:10px 14px; border-radius:12px; border:1px solid var(--glass-border);">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <span style="font-weight:900; color:#38bdf8; font-size:0.9rem;">🔄 تصفية نوع الحركة:</span>
                    <select id="im-log-type-filter" class="f-input f-select" style="min-width:210px;" onchange="renderChronologicalMovementLog()">
                        <option value="ALL">جميع الحركات (وارد + منصرف + مرتجع + تحويل + تسوية)</option>
                        <option value="PURCHASE">📥 وارد مشتريات واستلام مخزني (GRN)</option>
                        <option value="SALE">📤 منصرف مبيعات وتسليم مخزني</option>
                        <option value="RETURN">↩️ مرتجعات مبيعات ومشتريات</option>
                        <option value="TRANSFER">🔄 مناقلات وتحويلات بين المستودعات</option>
                        <option value="ADJUST">⚖️ تسويات وفروقات جردية</option>
                        <option value="OPEN">🏁 أرصدة افتتاحية</option>
                    </select>
                </div>
                <span id="im-log-count-badge" style="font-size:0.8rem; font-weight:800; color:#38bdf8; background:rgba(56,189,248,0.1); padding:4px 12px; border-radius:20px; border:1px solid rgba(56,189,248,0.25);">0 حركة</span>
            </div>
            <div class="table-panel">
                <div class="table-wrapper">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th style="width:50px; text-align:center;">#</th>
                                <th>التاريخ</th>
                                <th>نوع الحركة</th>
                                <th>رقم المستند</th>
                                <th>اسم الصنف</th>
                                <th>المخزن</th>
                                <th style="text-align:center; color:#10b981;">الوارد (+)</th>
                                <th style="text-align:center; color:#f43f5e;">المنصرف (-)</th>
                                <th style="text-align:center;">الوحدة</th>
                                <th style="text-align:center;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="im-log-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. Integrated Summary Strip (Directly Under Table) -->
        <div class="summary-strip-hub">
            <div class="summary-strip-item">
                <div class="summary-strip-icon" style="color: #38bdf8; background: rgba(56, 189, 248, 0.1);">📋</div>
                <div class="summary-strip-info">
                    <span class="summary-strip-label">إجمالي الأصناف بالتقرير</span>
                    <div class="summary-strip-value" style="color: #38bdf8;" id="kpi-count">0 <span>صنف</span></div>
                </div>
            </div>

            <div class="summary-strip-divider"></div>

            <div class="summary-strip-item">
                <div class="summary-strip-icon" style="color: #f43f5e; background: rgba(244, 63, 94, 0.1);">⚖️</div>
                <div class="summary-strip-info">
                    <span class="summary-strip-label">إجمالي كميات المخزون</span>
                    <div class="summary-strip-value" style="color: #f43f5e;" id="kpi-qty">0 <span>وحدة</span></div>
                </div>
            </div>

            <div class="summary-strip-divider"></div>

            <div class="summary-strip-item">
                <div class="summary-strip-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">💰</div>
                <div class="summary-strip-info">
                    <span class="summary-strip-label">إجمالي القيمة النقدية الحية</span>
                    <div class="summary-strip-value" style="color: #10b981;" id="kpi-value">0 <span>SDG</span></div>
                </div>
            </div>

            <div class="summary-strip-divider"></div>

            <div class="summary-strip-item">
                <div class="summary-strip-icon" style="color: #fbbf24; background: rgba(251, 191, 36, 0.1);">⚠️</div>
                <div class="summary-strip-info">
                    <span class="summary-strip-label">تنبيهات ونواقص المخزون</span>
                    <div class="summary-strip-value" style="color: #fbbf24;" id="kpi-alerts">0 <span>تنبيه</span></div>
                </div>
            </div>
        </div>

        <!-- 3. Glass Action Island (Under Summary Strip & Above Charts) -->
        <div class="action-island" style="margin-bottom: 12px; justify-content: center; background: rgba(15, 23, 42, 0.4);">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <button class="btn-action-tool" onclick="openReportPreview('تقرير جرد الأرصدة العام', '#report-table-wrap')" style="background: linear-gradient(135deg, #0d9488, #0f766e); box-shadow: 0 4px 15px rgba(13, 148, 136, 0.35); padding: 10px 24px; font-size: 0.95rem;">
                    <span>👁️</span> معاينة
                </button>
                <button class="btn-action-tool" onclick="printReportDirectly('تقرير جرد الأرصدة العام', '#report-table-wrap')" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border); color: var(--text-primary); padding: 10px 24px; font-size: 0.95rem;">
                    <span>🖨️</span> طباعة
                </button>
                <button class="btn-action-tool" onclick="window.openExportModal(exportExcel, {element: document.getElementById('report-table-wrap'), filename: 'warehouse-inventory', orientation: 'landscape'})" style="background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 4px 15px rgba(5, 150, 105, 0.35); padding: 10px 24px; font-size: 0.95rem;">
                    <span>📥</span> تصدير
                </button>
            </div>
        </div>

        <!-- 4. Visual Analytics Dashboard (Bottom of Page) -->
        <div id="auto-charts-slot" class="auto-charts-slot" style="width: 100%; margin-top: 10px;"></div>

    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script>
        const ITEMS_STORAGE_KEY = 'erp_items_v1';
        const INVENTORY_STORAGE_KEY = 'erp_inventory_v1';

        let itemsDb = [];
        let invDb = [];

        function initInventoryReport() {
            populateWarehouseFilter();
            loadData();
            populateCategoryFilter();
            applyFilters();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initInventoryReport);
        } else {
            initInventoryReport();
        }
        document.addEventListener('DBReady', initInventoryReport);
        window.addEventListener('load', initInventoryReport);

        function populateWarehouseFilter() {
            const select = document.getElementById('filter-warehouse');
            select.innerHTML = '<option value="ALL">🏢 جميع المخازن (الجرد العام)</option>';
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
            invDb = buildRealInventory(itemsDb);
        }

        // The core accounting & inventory calculation engine
        function buildRealInventory(items) {
            let actualStockMap = {}; 
            let reservedStockMap = {};

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehousesDb.length === 0) {
                warehousesDb = [{ id:'w-main', name:'المخزن الرئيسي' }];
            }
            // Include pending receipt so items can have 0 balance there too
            warehousesDb.unshift({ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' });

            let getWarehouseName = (id) => {
                let w = warehousesDb.find(w => w.id === id || w.name === id);
                return w ? w.name : 'المخزن الرئيسي';
            };

            items.forEach(item => {
                warehousesDb.forEach(wh => {
                    let key = String(item.id) + '|' + wh.name;
                    actualStockMap[key] = 0;
                    reservedStockMap[key] = 0;
                });
                let openBal = parseFloat(item.openingBalance || item.opening_balance || 0);
                if (openBal > 0) {
                    let whName = getWarehouseName(item.warehouseId || item.warehouse || 'w-main');
                    let key = String(item.id) + '|' + whName;
                    actualStockMap[key] = (actualStockMap[key] || 0) + openBal;
                }
            });

            // 1. ADD Purchases (Inward) — SUBTRACT if Return (prevent parent+GRN double counting)
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            const grnByParentItem = {};
            const grnParentSet = new Set();
            purchases.forEach(p => {
                if (p && p.isGRN && p.originalInvoiceId) {
                    const pKey = String(p.originalInvoiceId).trim();
                    grnParentSet.add(pKey);
                    (p.items || p.lines || []).forEach(gl => {
                        const ik = pKey + '|' + String(gl.itemId || gl.productId || '');
                        grnByParentItem[ik] = (grnByParentItem[ik] || 0) + (parseFloat(gl.quantity ?? gl.qty) || 0);
                    });
                }
            });
            purchases.forEach(inv => {
                const invNoKey = String(inv.invoiceNo || inv.id || '').trim();
                const hasChildGrn = !inv.isGRN && !inv.isReturn && !inv.isAdjustment && (grnParentSet.has(invNoKey) || grnParentSet.has(String(inv.id || '').trim()));
                const isPendingParent = !inv.isGRN && !inv.isReturn && !inv.isAdjustment && (inv.receiptStatus === 'pending_receipt' || inv.receiptStatus === 'partial_received' || String(inv.warehouseId || inv.warehouse || '') === 'PENDING_RECEIPT');
                if (hasChildGrn && !isPendingParent) return;
                let hdrWhName = isPendingParent ? getWarehouseName('PENDING_RECEIPT') : getWarehouseName(inv.warehouseId || inv.warehouse);
                const isReturn = inv.isReturn === true;
                (inv.items || inv.lines || []).forEach(line => {
                    let lineWhName = isPendingParent ? getWarehouseName('PENDING_RECEIPT') : (line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName);
                    if (isPendingParent && hasChildGrn) {
                        const recSoFar = grnByParentItem[invNoKey + '|' + String(line.itemId || line.productId || '')] || 0;
                        const remPending = Math.max(0, (parseFloat(line.quantity ?? line.qty) || 0) - recSoFar);
                        if (remPending <= 0.0001) return;
                        line = { ...line, quantity: remPending, customBaseQty: undefined };
                    }
                    let key = String(line.itemId || line.productId) + '|' + lineWhName;
                    let item = items.find(i => String(i.id) === String(line.itemId || line.productId));
                    let baseQty;
                    if (line.customBaseQty !== undefined) {
                        // استخدم الكمية المحسوبة مسبقاً (من مذكرات الاستلام الجديدة)
                        baseQty = parseFloat(line.customBaseQty) || 0;
                    } else {
                        // للسجلات القديمة: استخرج عامل التحويل من بيانات الصنف
                        let resolvedFactor = line.customFactor || 1;
                        if (resolvedFactor === 1 && item) {
                            const uName = String(line.unitName || line.unit || '').trim();
                            const unitsList = [];
                            if (Array.isArray(item.units)) unitsList.push(...item.units);
                            if (Array.isArray(item.unitsArray)) unitsList.push(...item.unitsArray);
                            const matchedUnit = unitsList.find(u => {
                                const n = (u.unitName || u.name || '').trim();
                                return n && n === uName;
                            });
                            if (matchedUnit && parseFloat(matchedUnit.factor || matchedUnit.equiv) > 0) {
                                resolvedFactor = parseFloat(matchedUnit.factor || matchedUnit.equiv);
                            } else {
                                resolvedFactor = item.conversionFactor || 1;
                            }
                        }
                        baseQty = (parseFloat(line.quantity) || 0) * resolvedFactor;
                    }
                    if (isReturn) {
                        actualStockMap[key] = (actualStockMap[key] || 0) - baseQty;
                    } else {
                        actualStockMap[key] = (actualStockMap[key] || 0) + baseQty;
                    }
                });
            });

            // 2. SUBTRACT Sales (Outward)
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted && !x.wasSplit && x.saleType !== 'preliminary' && x.status !== 'preliminary' && x.status !== 'draft');
            sales.forEach(inv => {
                let hdrWhName = getWarehouseName(inv.warehouse);
                const isFullDelivered = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Delivered_Pending_Admin' || inv.deliveryStatus === 'Admin_Closed';
                const isReservedOrPending = (inv.saleType === 'reserved' || inv.deliveryStatus === 'pending' || inv.deliveryStatus === 'partial' || String(inv.warehouse || '').includes('حجز'));
                (inv.items || []).forEach(line => {
                    let delWhName = getWarehouseName(line.deliveredWarehouseId || inv.deliveredWarehouse || inv.deliveryWarehouse || line.warehouseId || inv.warehouse);
                    let resWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
                    let key = String(line.itemId) + '|' + delWhName;
                    let resKey = String(line.itemId) + '|' + resWhName;
                    let item = items.find(i => String(i.id) === String(line.itemId));
                    let resolvedFactor = line.customFactor || 1;
                    if (resolvedFactor === 1 && item) {
                        const uName = String(line.unitName || line.unit || '').trim();
                        const unitsList = [...(Array.isArray(item.units) ? item.units : []), ...(Array.isArray(item.unitsArray) ? item.unitsArray : [])];
                        const matchedUnit = unitsList.find(u => { const n = (u.unitName || u.name || '').trim(); return n && n === uName; });
                        resolvedFactor = (matchedUnit && parseFloat(matchedUnit.factor || matchedUnit.equiv) > 0) ? parseFloat(matchedUnit.factor || matchedUnit.equiv) : (item.conversionFactor || 1);
                    }
                    const origQty = parseFloat(line.quantity ?? line.qty) || 0;
                    let baseQty = (line.customBaseQty !== undefined) ? (parseFloat(line.customBaseQty) || 0) : (origQty * resolvedFactor);

                    if (inv.isReturn === true) {
                        actualStockMap[key] = (actualStockMap[key] || 0) + baseQty;
                    } else if (isReservedOrPending) {
                        if (isFullDelivered) {
                            actualStockMap[key] = (actualStockMap[key] || 0) - baseQty;
                        } else {
                            const delBase = Math.min(baseQty, (parseFloat(line.deliveredQty) || 0) * resolvedFactor);
                            if (delBase > 0) actualStockMap[key] = (actualStockMap[key] || 0) - delBase;
                            const remBase = Math.max(0, baseQty - delBase);
                            if (remBase > 0) reservedStockMap[resKey] = (reservedStockMap[resKey] || 0) + remBase;
                        }
                    } else {
                        actualStockMap[key] = (actualStockMap[key] || 0) - baseQty;
                    }
                });
            });

            // 3. Process Warehouse Transfers
            let transfers = JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1')) || [];
            transfers = transfers.filter(x => !x.deleted && x.status !== 'cancelled');
            transfers.forEach(transfer => {
                let fromWhName = getWarehouseName(transfer.fromWarehouse);
                let toWhName = getWarehouseName(transfer.toWarehouse);
                (transfer.items || []).forEach(line => {
                    let fromKey = String(line.itemId) + '|' + fromWhName;
                    let toKey = String(line.itemId) + '|' + toWhName;
                    let item = items.find(i => String(i.id) === String(line.itemId));
                    let baseQty;
                    if (line.customBaseQty !== undefined) {
                        baseQty = parseFloat(line.customBaseQty) || 0;
                    } else {
                        let resolvedFactor = line.customFactor || 1;
                        if (resolvedFactor === 1 && item) {
                            const uName = String(line.unitName || line.unit || '').trim();
                            const unitsList = [];
                            if (Array.isArray(item.units)) unitsList.push(...item.units);
                            if (Array.isArray(item.unitsArray)) unitsList.push(...item.unitsArray);
                            const matchedUnit = unitsList.find(u => { const n = (u.unitName || u.name || '').trim(); return n && n === uName; });
                            if (matchedUnit && parseFloat(matchedUnit.factor || matchedUnit.equiv) > 0) {
                                resolvedFactor = parseFloat(matchedUnit.factor || matchedUnit.equiv);
                            } else {
                                resolvedFactor = item.conversionFactor || 1;
                            }
                        }
                        baseQty = (parseFloat(line.quantity) || 0) * resolvedFactor;
                    }
                    
                    actualStockMap[fromKey] = (actualStockMap[fromKey] || 0) - baseQty;
                    actualStockMap[toKey] = (actualStockMap[toKey] || 0) + baseQty;
                });
            });

            let generated = [];
            for (let key in actualStockMap) {
                let [itemId, warehouse] = key.split('|');
                let itemData = items.find(i => String(i.id) === String(itemId));
                let minQty = itemData && itemData.reorderLevel ? parseFloat(itemData.reorderLevel) : 10;
                
                generated.push({
                    itemId: String(itemId),
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
            select.innerHTML = '<option value="ALL">🏷️ تصفية بالفئة (جميع الفئات)</option>';
            const cats = [...new Set(itemsDb.map(i => i.category).filter(c => c))];
            if(cats.length === 0) cats.push('مواد بناء');
            cats.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat; option.text = cat; select.appendChild(option);
            });
        }

        const LARGE_CONTAINER_UNITS = [
            'طن', 'طن عددي', 'طن وزني', 'شوال', 'جوال', 'لفة', 'لفه', 'كرتونة', 'كرتونه', 'دزينة', 'دزينه', 
            'باكت', 'باكيت', 'صندوق', 'ربطة', 'ربطه', 'طبلية', 'طبليه', 'برميل', 'صفيحة', 'صفيحه', 'دستة', 'دسته', 'طرد', 'كيس'
        ];

        const SMALL_BASE_UNITS = [
            'سيخ', 'سيخة', 'سيخه', 'كيلو', 'كجم', 'متر', 'سم', 'حبة', 'حبه', 'قطعة', 'قطعه', 'لتر', 'مل', 'جرام', 'غرام', 'لوح', 'عدد'
        ];

        function isLargeUnitName(name) {
            if (!name) return false;
            const n = name.trim().toLowerCase();
            return LARGE_CONTAINER_UNITS.some(u => n.includes(u));
        }

        function isSmallUnitName(name) {
            if (!name) return false;
            const n = name.trim().toLowerCase();
            return SMALL_BASE_UNITS.some(u => n.includes(u));
        }

        // Helper: Extract all configured units and their factors for an item
        function getItemUnitsList(item) {
            if (!item) return [{ name: 'وحدة', factor: 1 }];
            let list = [];
            
            // 1. First add base unit
            const baseName = (item.baseUnit || (item.units && item.units[0] ? (item.units[0].unitName || item.units[0].name) : '') || item.unit || '').trim();
            if (baseName) {
                list.push({ name: baseName, factor: 1, isBase: true });
            }

            // 2. Add other units from item.units
            if (Array.isArray(item.units) && item.units.length > 0) {
                item.units.forEach(u => {
                    const name = (u.unitName || u.name || '').trim();
                    const factor = parseFloat(u.factor || u.equiv || u.conversionFactor) || 1;
                    if (name && !list.some(x => x.name === name)) {
                        list.push({ name, factor });
                    }
                });
            }
            
            // 3. Add units from item.unitsArray
            if (Array.isArray(item.unitsArray) && item.unitsArray.length > 0) {
                item.unitsArray.forEach(u => {
                    const name = (u.name || u.unitName || '').trim();
                    const factor = parseFloat(u.factor || u.equiv || u.conversionFactor) || 1;
                    if (name && !list.some(x => x.name === name)) {
                        list.push({ name, factor });
                    }
                });
            }
            
            if (list.length === 0) {
                list.push({ name: 'وحدة', factor: 1, isBase: true });
            }
            
            return list;
        }

        function convertBaseQtyToUnit(baseQty, baseUnitName, targetUnit) {
            const num = Number(baseQty) || 0;
            const factor = parseFloat(targetUnit.factor) || 1;
            if (factor === 0 || factor === 1) return num;

            const baseIsLarge = isLargeUnitName(baseUnitName);
            const targetIsSmall = isSmallUnitName(targetUnit.name);
            const targetIsLarge = isLargeUnitName(targetUnit.name);
            const baseIsSmall = isSmallUnitName(baseUnitName);

            // If Base is Large (e.g. طن, شوال, لفة) and Target is Small (e.g. سيخة, كيلو, متر):
            // 1 طن = 92 سيخة => TargetQty = BaseQty * Factor
            if (baseIsLarge && targetIsSmall) {
                return num * factor;
            }

            // If Base is Small (e.g. سيخة, كيلو, متر) and Target is Large (e.g. طن, شوال, لفة):
            // 1 طن = 92 سيخة => TargetQty = BaseQty / Factor
            if (baseIsSmall && targetIsLarge) {
                return num / factor;
            }

            // Target indicates a sub-unit:
            if (targetIsSmall) {
                return num * factor;
            }
            // Target indicates a package unit:
            if (targetIsLarge) {
                return num / factor;
            }

            // Default fallback
            return num * factor;
        }

        // Helper: Render quantity in all configured units (Base + Secondary Unit Chips)
        function renderMultiUnitQty(baseQty, unitsList, themeColor = '#38bdf8') {
            if (baseQty === undefined || baseQty === null) baseQty = 0;
            const num = Number(baseQty) || 0;
            const baseUnit = (unitsList && unitsList.length > 0) ? unitsList[0] : { name: 'وحدة', factor: 1 };
            
            const formattedBase = parseFloat(num.toFixed(3)).toLocaleString('en-US');
            
            // Single unit item
            if (!unitsList || unitsList.length <= 1) {
                return `<div style="display:inline-flex; align-items:baseline; justify-content:center; gap:4px;">
                    <span style="font-size:1.2rem; font-weight:900; color:${themeColor};">${formattedBase}</span>
                    <span style="font-size:0.8rem; font-weight:800; color:#cbd5e1; background:rgba(255,255,255,0.08); padding:1px 6px; border-radius:4px;">${baseUnit.name}</span>
                </div>`;
            }
            
            // Multi-unit conversions
            const otherUnits = unitsList.slice(1);
            const otherBadges = otherUnits.map(u => {
                const converted = convertBaseQtyToUnit(num, baseUnit.name, u);
                const formatted = parseFloat(converted.toFixed(3)).toLocaleString('en-US');
                return `<span style="background: rgba(56, 189, 248, 0.12); color: ${themeColor}; border: 1px solid rgba(56, 189, 248, 0.25); padding: 3px 8px; border-radius: 6px; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                    <span style="color:#94a3b8; font-size:0.75rem; font-weight:600;">تساوي:</span> <strong>${formatted}</strong> <span>${u.name}</span>
                </span>`;
            }).join(' ');
            
            return `<div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                <div style="display:inline-flex; align-items:baseline; justify-content:center; gap:4px;">
                    <span style="font-size:1.2rem; font-weight:900; color:${themeColor};">${formattedBase}</span>
                    <span style="font-size:0.8rem; font-weight:800; color:#cbd5e1; background:rgba(255,255,255,0.08); padding:1px 6px; border-radius:4px;">${baseUnit.name}</span>
                </div>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4px;">
                    ${otherBadges}
                </div>
            </div>`;
        }

        function onSearchInput(input) {
            const clearBtn = document.getElementById('search-clear-btn');
            if (clearBtn) {
                clearBtn.style.display = input.value.trim().length > 0 ? 'inline-flex' : 'none';
            }
            applyFilters();
        }

        function clearSearch() {
            const input = document.getElementById('filter-search');
            if (input) {
                input.value = '';
                const clearBtn = document.getElementById('search-clear-btn');
                if (clearBtn) clearBtn.style.display = 'none';
                applyFilters();
            }
        }

        function setQuickFilter(statusKey, btn) {
            const statusSelect = document.getElementById('filter-status');
            if (statusSelect) {
                statusSelect.value = statusKey;
            }
            document.querySelectorAll('.quick-pill').forEach(p => p.classList.remove('active'));
            if (btn) btn.classList.add('active');
            applyFilters();
        }

        function syncStatusFromSelect() {
            const statusVal = document.getElementById('filter-status').value;
            document.querySelectorAll('.quick-pill').forEach(p => {
                const clickAttr = p.getAttribute('onclick') || '';
                if (clickAttr.includes(`'${statusVal}'`)) {
                    p.classList.add('active');
                } else {
                    p.classList.remove('active');
                }
            });
            applyFilters();
        }

        function copyItemCode(code, event) {
            if (event) event.stopPropagation();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code).then(() => {
                    if (typeof showToast === 'function') {
                        showToast(`تم نسخ الكود (${code}) إلى الحافظة!`, 'success');
                    }
                });
            }
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
                const item = itemsDb.find(i => String(i.id) === String(invRecord.itemId));
                if(item) {
                    let baseUnit = item.units && item.units.length > 0 ? (item.units[0].unitName || item.units[0].name) : (item.unit || item.baseUnit || 'وحدة');
                    let price = item.units && item.units.length > 0 ? item.units[0].cashPrice : (item.price || 0);
                    reportData.push({
                        ...invRecord, itemName: item.name || 'بدون اسم', category: item.category || 'عام',
                        unit: baseUnit, price: parseFloat((price || 0).toString().replace(/,/g, '')) || 0,
                        rawItem: item
                    });
                }
            });

            const filteredData = reportData.filter(row => {
                if (fWh !== 'ALL' && row.warehouse !== fWh) return false;
                if (fCat !== 'ALL' && row.category !== fCat) return false;
                if (fSearch && !row.itemName.toLowerCase().includes(fSearch) && !row.itemId.toLowerCase().includes(fSearch)) return false;
                
                let actualQty = Number(row.qty) || 0;
                let reservedQty = Number(row.reservedQuantity) || 0;
                let availQty = actualQty - reservedQty;

                // Check if all balances (actual, reserved, available) are zero
                const isZeroStock = (Math.abs(actualQty) < 0.0001 && Math.abs(reservedQty) < 0.0001 && Math.abs(availQty) < 0.0001);

                if (fStatus === 'ALL_WITH_ZERO') {
                    return true;
                } else if (fStatus === 'OK') {
                    let statusInfo = getStatusInfo(availQty, row.minQty);
                    return statusInfo.code === 'OK' && !isZeroStock;
                } else if (fStatus === 'WARNING') {
                    let statusInfo = getStatusInfo(availQty, row.minQty);
                    return statusInfo.code === 'WARNING' && !isZeroStock;
                } else if (fStatus === 'DANGER') {
                    let statusInfo = getStatusInfo(availQty, row.minQty);
                    return statusInfo.code === 'DANGER' && !isZeroStock;
                } else {
                    return !isZeroStock;
                }
            });

            filteredData.forEach((row, index) => {
                let actualQty = Number(row.qty) || 0;
                let reservedQty = Number(row.reservedQuantity) || 0;
                let availQty = actualQty - reservedQty;
                
                let statusInfo = getStatusInfo(availQty, row.minQty);
                let rowValue = availQty * row.price;

                totalItems++; totalQty += availQty; totalValue += rowValue;
                if (statusInfo.code !== 'OK') totalAlerts++;

                const unitsList = getItemUnitsList(row.rawItem);
                let unitsRelationInfo = '';
                if (unitsList.length > 1) {
                    const baseUnitName = unitsList[0].name;
                    const rels = unitsList.slice(1).map(u => {
                        const factor = parseFloat(u.factor) || 1;
                        const baseIsLarge = isLargeUnitName(baseUnitName);
                        const targetIsSmall = isSmallUnitName(u.name);
                        if (baseIsLarge && targetIsSmall) {
                            return `1 ${baseUnitName} ⇄ ${factor} ${u.name}`;
                        } else {
                            return `1 ${u.name} ⇄ ${factor} ${baseUnitName}`;
                        }
                    }).join(' | ');
                    unitsRelationInfo = `<div style="font-size:0.72rem; color:#38bdf8; margin-top:3px; font-weight:700;">⚖️ ${rels}</div>`;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><span class="code-pill" onclick="copyItemCode('${row.itemId}', event)" title="انقر لنسخ الكود">📋 ${row.itemId}</span></td>
                    <td style="font-weight:700; color:var(--text-primary);">
                        <div style="font-size:0.92rem; color:#f8fafc;">${row.itemName}</div>
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">الوحدة الرئيسية: <strong style="color:#cbd5e1;">${unitsList[0].name}</strong></div>
                        ${unitsRelationInfo}
                    </td>
                    <td><span style="background:rgba(255,255,255,0.04); padding:3px 8px; border-radius:6px; font-weight:600; font-size:0.8rem; border:1px solid var(--glass-border);">${row.category}</span></td>
                    <td><span style="font-weight:700; color:#cbd5e1;">${row.warehouse}</span></td>
                    <td style="text-align:center;">${renderMultiUnitQty(actualQty, unitsList, '#38bdf8')}</td>
                    <td style="text-align:center;">${reservedQty > 0 ? renderMultiUnitQty(reservedQty, unitsList, '#fbbf24') : '<span style="color:var(--text-muted); font-weight:700;">-</span>'}</td>
                    <td style="text-align:center;">${renderMultiUnitQty(availQty, unitsList, statusInfo.color)}</td>
                    <td style="font-weight:700; color:#cbd5e1;">${row.price.toLocaleString()}</td>
                    <td class="val-td">${rowValue.toLocaleString()} <span style="font-size:0.75rem; color:#64748b; font-weight:600;">SDG</span></td>
                    <td><span class="status-badge ${statusInfo.cssClass}">${statusInfo.icon} ${statusInfo.text}</span></td>
                    <td style="text-align:center;"><a data-href="item-ledger.php?item=${row.itemId}" onclick="window.location.href='item-ledger.php?item=${row.itemId}'" class="btn-link"><span>📑</span> كشف حركة</a></td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('kpi-count').innerHTML = `${totalItems.toLocaleString()} <span>صنف</span>`;
            document.getElementById('kpi-qty').innerHTML = `${totalQty.toLocaleString()} <span>وحدة</span>`;
            document.getElementById('kpi-value').innerHTML = `${totalValue.toLocaleString()} <span>SDG</span>`;
            document.getElementById('kpi-alerts').innerHTML = `${totalAlerts.toLocaleString()} <span>تنبيه</span>`;

            const matchesBadge = document.getElementById('filtered-matches-badge');
            if (matchesBadge) {
                matchesBadge.textContent = `المطابق: ${filteredData.length} من ${reportData.length} صنف`;
            }

            if (document.getElementById('im-log-panel') && document.getElementById('im-log-panel').style.display !== 'none') { renderChronologicalMovementLog(); }
            if (filteredData.length === 0) {
                const whName = fWh === 'ALL' ? 'جميع المخازن' : fWh;
                tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding:60px 20px; color:var(--text-secondary);">
                    <div style="font-size: 2.5rem; margin-bottom: 10px;">📦</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #f8fafc; margin-bottom: 5px;">لا توجد أصناف مطابقة للبحث أو التصفية</div>
                    <div style="font-size: 0.85rem; color: #64748b;">جرب تغيير خيارات التصفية أو مسح البحث لعرض البضائع في (${whName})</div>
                </td></tr>`;
            }
        }

        function getStatusInfo(qty, minQty) {
            if (qty <= 0) return { code: 'DANGER', text: 'نافذ / سالب', color: '#ef4444', cssClass: 'status-danger', icon: '🚨' };
            if (qty <= minQty) return { code: 'WARNING', text: 'تحت الطلب', color: '#fbbf24', cssClass: 'status-warn', icon: '⚠️' };
            return { code: 'OK', text: 'رصيد آمن', color: 'var(--emerald-accent)', cssClass: 'status-ok', icon: '✅' };
        }

        function exportExcel() {
            const isLogView = document.getElementById('im-log-panel') && document.getElementById('im-log-panel').style.display !== 'none';
            let csv = "\uFEFF";
            if (isLogView) {
                csv += "#,التاريخ,نوع الحركة,رقم المستند,الصنف,المخزن,الوارد (+),المنصرف (-),الوحدة,البيان\n";
                (window._lastMovementLogRows || []).forEach((r, idx) => {
                    csv += `"${idx+1}","${r.dateStr}","${r.typeName}","${r.ref}","${(r.itemName||'').replace(/"/g,'""')}","${r.warehouse}","${r.inQty}","${r.outQty}","${r.unit}","${(r.notes||'').replace(/"/g,'""')}"\n`;
                });
            } else {
                csv += "كود الصنف,اسم الصنف,الفئة,المخزن,الرصيد الفعلي,الرصيد المحجوز,المتاح للبيع,الوحدة,متوسط السعر,إجمالي القيمة (SDG),حالة المخزون\n";
                document.querySelectorAll('#inventory-tbody tr').forEach(tr => {
                    const tds = tr.querySelectorAll('td');
                    if (tds.length >= 10) {
                        const rowVals = [0,1,2,3,4,5,6,7,8,9].map(i => '"' + (tds[i].innerText || '').replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"');
                        csv += rowVals.join(',') + "\n";
                    }
                });
            }
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.setAttribute("download", (isLogView ? "goods_movements_log_" : "warehouse_balances_") + new Date().toISOString().split('T')[0] + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function switchInventoryMovementView(mode, btn) {
            document.querySelectorAll('.im-mode-btn').forEach(b => {
                b.classList.remove('active');
                b.style.background = 'rgba(255,255,255,0.04)';
                b.style.color = '#cbd5e1';
                b.style.borderColor = 'rgba(255,255,255,0.12)';
            });
            if (btn) {
                btn.classList.add('active');
                btn.style.background = 'linear-gradient(135deg, #0284c7, #2563eb)';
                btn.style.color = '#ffffff';
                btn.style.borderColor = '#38bdf8';
            }
            const balPanel = document.getElementById('im-balances-panel');
            const logPanel = document.getElementById('im-log-panel');
            if (mode === 'log') {
                if (balPanel) balPanel.style.display = 'none';
                if (logPanel) logPanel.style.display = 'block';
                renderChronologicalMovementLog();
            } else {
                if (balPanel) balPanel.style.display = 'block';
                if (logPanel) logPanel.style.display = 'none';
            }
        }

        function renderChronologicalMovementLog() {
            const tbody = document.getElementById('im-log-tbody');
            if (!tbody) return;
            const fWh = document.getElementById('filter-warehouse') ? document.getElementById('filter-warehouse').value : 'ALL';
            const fSearch = (document.getElementById('filter-search') ? document.getElementById('filter-search').value : '').toLowerCase().trim();
            const fType = document.getElementById('im-log-type-filter') ? document.getElementById('im-log-type-filter').value : 'ALL';

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehousesDb.length === 0) warehousesDb = [{ id:'w-main', name:'المخزن الرئيسي' }];
            let getWarehouseName = (id) => {
                if (id === 'PENDING_RECEIPT') return '⏳ بضاعة قيد الاستلام';
                let w = warehousesDb.find(w => String(w.id) === String(id) || w.name === id);
                return w ? w.name : (id || 'المخزن الرئيسي');
            };

            let rows = [];
            // 1. Opening balances
            (itemsDb || []).forEach(it => {
                let openBal = parseFloat(it.openingBalance || it.opening_balance || 0);
                if (openBal > 0) {
                    let whName = getWarehouseName(it.warehouseId || it.warehouse || 'w-main');
                    rows.push({
                        date: new Date(it.createdAt || it.date || '2026-01-01'),
                        dateStr: (it.createdAt || it.date || '2026-01-01').toString().slice(0,10),
                        typeCode: 'OPEN',
                        typeName: 'رصيد افتتاحي',
                        badgeColor: '#6366f1',
                        ref: it.code || it.id,
                        docUrl: `item-ledger.php?item=${it.id}`,
                        itemId: String(it.id),
                        itemName: it.name || 'صنف',
                        warehouse: whName,
                        inQty: openBal,
                        outQty: 0,
                        unit: it.baseUnit || it.unit || 'وحدة',
                        notes: 'رصيد أول المدة'
                    });
                }
            });

            // 2. Purchases, Purchase Returns, Stocktake Adjustments (exclude parent invoice once GRNs exist or while pending_receipt)
            let purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted);
            const cardGrnParents = new Set();
            purchases.forEach(p => { if (p && p.isGRN && p.originalInvoiceId) cardGrnParents.add(String(p.originalInvoiceId).trim()); });
            purchases.forEach(inv => {
                if (!inv.isGRN && !inv.isReturn && !inv.isAdjustment) {
                    const ik1 = String(inv.invoiceNo || '').trim(), ik2 = String(inv.id || '').trim();
                    if (inv.receiptStatus === 'pending_receipt' || inv.receiptStatus === 'partial_received' || String(inv.warehouseId || inv.warehouse || '') === 'PENDING_RECEIPT' || (ik1 && cardGrnParents.has(ik1)) || (ik2 && cardGrnParents.has(ik2))) return;
                }
                let hdrWh = getWarehouseName(inv.warehouseId || inv.warehouse);
                let dStr = (inv.date || inv.receiveDate || inv.createdAt || '').toString().slice(0,10);
                (inv.items || inv.lines || []).forEach(line => {
                    let wh = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWh;
                    let itemId = String(line.itemId || line.productId || '');
                    let item = itemsDb.find(i => String(i.id) === itemId) || {};
                    let baseQty = line.customBaseQty !== undefined ? (parseFloat(line.customBaseQty) || 0) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1));
                    let tCode = 'PURCHASE', tName = inv.isGRN ? 'استلام مشتريات (GRN)' : 'فاتورة مشتريات', bColor = '#10b981', inQ = Math.abs(baseQty), outQ = 0;
                    let url = inv.isGRN ? `purchase-receipt.php?id=${inv.id}` : `purchase.php?id=${inv.id || inv.invoiceNo}`;
                    if (inv.isReturn) {
                        tCode = 'RETURN'; tName = 'مرتجع مشتريات'; bColor = '#f43f5e'; inQ = 0; outQ = Math.abs(baseQty);
                        url = `purchase-return.php?id=${inv.id || inv.invoiceNo}`;
                    } else if (inv.isAdjustment) {
                        tCode = 'ADJUST';
                        tName = baseQty >= 0 ? 'تسوية جردية (زيادة)' : 'تسوية جردية (عجز)';
                        bColor = '#f97316';
                        inQ = baseQty >= 0 ? Math.abs(baseQty) : 0;
                        outQ = baseQty < 0 ? Math.abs(baseQty) : 0;
                        url = `inventory-adjustment.php?tab=history`;
                    }
                    rows.push({
                        date: new Date(inv.date || inv.receiveDate || inv.createdAt || Date.now()),
                        dateStr: dStr || '-',
                        typeCode: tCode,
                        typeName: tName,
                        badgeColor: bColor,
                        ref: inv.invoiceNo || inv.receiptNo || inv.id || '-',
                        docUrl: url,
                        itemId: itemId,
                        itemName: item.name || line.itemName || line.name || 'صنف',
                        warehouse: wh,
                        inQty: inQ,
                        outQty: outQ,
                        unit: line.unitName || line.unit || item.baseUnit || item.unit || 'وحدة',
                        notes: line.notes || inv.notes || ''
                    });
                });
            });

            // 3. Sales & Sales Returns
            let sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && !x.wasSplit && x.saleType !== 'preliminary' && x.status !== 'preliminary' && x.status !== 'draft');
            sales.forEach(inv => {
                if (inv.saleType === 'reserved' && inv.deliveryStatus !== 'delivered') return;
                let hdrWh = getWarehouseName(inv.deliveryWarehouse || inv.warehouse);
                let dStr = (inv.date || inv.createdAt || '').toString().slice(0,10);
                (inv.items || []).forEach(line => {
                    let wh = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWh;
                    let itemId = String(line.itemId || '');
                    let item = itemsDb.find(i => String(i.id) === itemId) || {};
                    let baseQty = line.customBaseQty !== undefined ? (parseFloat(line.customBaseQty) || 0) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1));
                    let isRet = inv.isReturn === true;
                    rows.push({
                        date: new Date(inv.date || inv.createdAt || Date.now()),
                        dateStr: dStr || '-',
                        typeCode: isRet ? 'RETURN' : 'SALE',
                        typeName: isRet ? 'مرتجع مبيعات' : (inv.isSplit ? `تسليم مبيعات مقسمة (#${inv.splitBatchNo || 1})` : 'فاتورة مبيعات'),
                        badgeColor: isRet ? '#38bdf8' : '#ef4444',
                        ref: inv.invoiceNo || inv.id || '-',
                        docUrl: isRet ? `sales-invoice.php?id=${inv.id || inv.invoiceNo}` : `direct-sales.php?id=${inv.id || inv.invoiceNo}`,
                        itemId: itemId,
                        itemName: item.name || line.name || 'صنف',
                        warehouse: wh,
                        inQty: isRet ? Math.abs(baseQty) : 0,
                        outQty: isRet ? 0 : Math.abs(baseQty),
                        unit: line.unitName || line.unit || item.baseUnit || item.unit || 'وحدة',
                        notes: line.notes || inv.notes || ''
                    });
                });
            });

            // 4. Warehouse Transfers
            let transfers = (JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1')) || []).filter(x => !x.deleted && x.status !== 'cancelled');
            transfers.forEach(tr => {
                let fromWh = getWarehouseName(tr.fromWarehouse);
                let toWh = getWarehouseName(tr.toWarehouse);
                let dStr = (tr.date || tr.createdAt || '').toString().slice(0,10);
                (tr.items || []).forEach(line => {
                    let itemId = String(line.itemId || '');
                    let item = itemsDb.find(i => String(i.id) === itemId) || {};
                    let baseQty = line.customBaseQty !== undefined ? (parseFloat(line.customBaseQty) || 0) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1));
                    rows.push({
                        date: new Date(tr.date || tr.createdAt || Date.now()),
                        dateStr: dStr || '-',
                        typeCode: 'TRANSFER',
                        typeName: `تحويل صادر (إلى ${toWh})`,
                        badgeColor: '#a855f7',
                        ref: tr.reference || tr.id || '-',
                        docUrl: `inventory-adjustment.php?tab=transfer`,
                        itemId: itemId,
                        itemName: item.name || line.itemName || line.name || 'صنف',
                        warehouse: fromWh,
                        inQty: 0,
                        outQty: Math.abs(baseQty),
                        unit: line.unitName || line.unit || item.baseUnit || item.unit || 'وحدة',
                        notes: tr.notes || ''
                    });
                    rows.push({
                        date: new Date(tr.date || tr.createdAt || Date.now()),
                        dateStr: dStr || '-',
                        typeCode: 'TRANSFER',
                        typeName: `تحويل وارد (من ${fromWh})`,
                        badgeColor: '#22d3ee',
                        ref: tr.reference || tr.id || '-',
                        docUrl: `inventory-adjustment.php?tab=transfer`,
                        itemId: itemId,
                        itemName: item.name || line.itemName || line.name || 'صنف',
                        warehouse: toWh,
                        inQty: Math.abs(baseQty),
                        outQty: 0,
                        unit: line.unitName || line.unit || item.baseUnit || item.unit || 'وحدة',
                        notes: tr.notes || ''
                    });
                });
            });

            rows.sort((a, b) => b.date - a.date);
            const filtered = rows.filter(r => {
                if (fWh !== 'ALL' && r.warehouse !== fWh) return false;
                if (fType !== 'ALL' && r.typeCode !== fType) return false;
                if (fSearch && !r.itemName.toLowerCase().includes(fSearch) && !String(r.ref).toLowerCase().includes(fSearch) && !String(r.itemId).toLowerCase().includes(fSearch)) return false;
                return true;
            });

            window._lastMovementLogRows = filtered;
            const badgeEl = document.getElementById('im-log-count-badge');
            if (badgeEl) badgeEl.textContent = `${filtered.length} حركة مسجلة`;

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:45px; color:var(--text-secondary); font-weight:800;">لا توجد حركات بضائع مطابقة للفلتر المختار</td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.slice(0, 300).map((r, idx) => `
                <tr>
                    <td style="text-align:center; color:#64748b; font-weight:700;">${idx + 1}</td>
                    <td style="font-family:monospace; font-weight:700;">${r.dateStr}</td>
                    <td><span style="background:${r.badgeColor}22; color:${r.badgeColor}; border:1px solid ${r.badgeColor}55; padding:4px 10px; border-radius:20px; font-size:0.76rem; font-weight:800;">${r.typeName}</span></td>
                    <td><a href="${r.docUrl}" class="code-pill" style="text-decoration:none;">#${r.ref}</a></td>
                    <td style="font-weight:800; color:#f8fafc;">${r.itemName}</td>
                    <td style="font-weight:700; color:#cbd5e1;">🏢 ${r.warehouse}</td>
                    <td style="text-align:center; font-weight:900; color:#10b981; font-size:1rem;">${r.inQty > 0 ? '+' + r.inQty.toLocaleString('en-US', {maximumFractionDigits:3}) : '-'}</td>
                    <td style="text-align:center; font-weight:900; color:#f43f5e; font-size:1rem;">${r.outQty > 0 ? '-' + r.outQty.toLocaleString('en-US', {maximumFractionDigits:3}) : '-'}</td>
                    <td style="text-align:center; font-weight:700; color:#94a3b8;">${r.unit}</td>
                    <td style="text-align:center;"><a href="item-ledger.php?item=${r.itemId}" class="btn-link">📑 كارت الصنف</a></td>
                </tr>
            `).join('');
        }

        // Stocktake / Adjustment Functions
        function openStocktakeModal() {
            const filterWh = document.getElementById('filter-warehouse');
            const adjWh = document.getElementById('adj-warehouse');
            adjWh.innerHTML = '';
            
            Array.from(filterWh.options).forEach(opt => {
                if (opt.value !== 'ALL') {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.text = opt.text;
                    adjWh.appendChild(newOpt);
                }
            });

            if (filterWh.value !== 'ALL') {
                adjWh.value = filterWh.value;
            }

            document.getElementById('stocktake-modal').style.display = 'flex';
            loadStocktakeItems();
        }

        function closeStocktakeModal() {
            document.getElementById('stocktake-modal').style.display = 'none';
        }

        let stocktakeData = [];

        function loadStocktakeItems() {
            const wh = document.getElementById('adj-warehouse').value;
            const tbody = document.getElementById('stocktake-tbody');
            tbody.innerHTML = '';

            let currentInv = buildRealInventory(itemsDb);
            let whInv = currentInv.filter(inv => inv.warehouse === wh);

            stocktakeData = [];

            if (whInv.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد أصناف في هذا المخزن حالياً.</td></tr>`;
                document.getElementById('adj-total-val').textContent = '0.00';
                return;
            }

            whInv.forEach((row, index) => {
                const item = itemsDb.find(i => i.id === row.itemId);
                if (!item) return;

                let baseUnit = item.units && item.units.length > 0 ? item.units[0].unitName : (item.unit || 'وحدة');
                let price = item.units && item.units.length > 0 ? item.units[0].cashPrice : (item.price || 0);
                price = parseFloat((price || 0).toString().replace(/,/g, '')) || 0;

                const bookQty = row.qty || 0;
                
                stocktakeData.push({
                    itemId: row.itemId,
                    name: item.name || 'بدون اسم',
                    unit: baseUnit,
                    price: price,
                    bookQty: bookQty
                });

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight:700;">${item.name} <div style="font-size:0.7rem; color:var(--text-secondary); margin-top:2px;">الوحدة: ${baseUnit} | التكلفة: ${price.toLocaleString()}</div></td>
                    <td style="text-align:center; color:#38bdf8; font-weight:700;">${bookQty.toLocaleString()}</td>
                    <td style="text-align:center;">
                        <input type="number" class="f-input" id="phys-${row.itemId}" value="${bookQty}" oninput="calculateStocktakeDiff('${row.itemId}')" style="width:100px; text-align:center; padding:5px; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1); border-radius:6px; color:#fff; font-weight:700;">
                    </td>
                    <td style="text-align:center; font-weight:700;" id="diff-${row.itemId}">0</td>
                    <td style="text-align:center; font-weight:700;" id="val-${row.itemId}">0.00</td>
                `;
                tbody.appendChild(tr);
                calculateStocktakeDiff(row.itemId);
            });
        }

        function calculateStocktakeDiff(itemId) {
            const row = stocktakeData.find(d => d.itemId === itemId);
            if (!row) return;

            const rate = parseFloat(document.getElementById('adj-exchange-rate').value) || 1;

            const physInput = document.getElementById(`phys-${itemId}`);
            const physQty = parseFloat(physInput.value) || 0;
            const diff = physQty - row.bookQty;
            const diffVal = (diff * row.price) / rate;

            const diffTd = document.getElementById(`diff-${itemId}`);
            const valTd = document.getElementById(`val-${itemId}`);

            valTd.textContent = diffVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            if (diff > 0) {
                diffTd.innerHTML = `<span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.3); font-weight: 700; display: inline-block;">+${diff.toLocaleString()}</span>`;
                valTd.style.color = '#10b981';
            } else if (diff < 0) {
                diffTd.innerHTML = `<span style="background: rgba(239, 68, 68, 0.15); color: #ef4444; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(239, 68, 68, 0.3); font-weight: 700; display: inline-block;">${diff.toLocaleString()}</span>`;
                valTd.style.color = '#ef4444';
            } else {
                diffTd.innerHTML = `<span style="color: var(--text-secondary);">${diff.toLocaleString()}</span>`;
                valTd.style.color = 'var(--text-secondary)';
            }

            let totalAdjVal = 0;
            stocktakeData.forEach(d => {
                const pQty = parseFloat(document.getElementById(`phys-${d.itemId}`).value) || 0;
                totalAdjVal += (pQty - d.bookQty) * d.price;
            });

            const totalAdjValForeign = totalAdjVal / rate;

            const totalEl = document.getElementById('adj-total-val');
            totalEl.textContent = totalAdjValForeign.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (totalAdjValForeign > 0) {
                totalEl.style.color = '#10b981';
            } else if (totalAdjValForeign < 0) {
                totalEl.style.color = '#ef4444';
            } else {
                totalEl.style.color = '#fbbf24';
            }
        }

        function saveStocktake() {
            const wh = document.getElementById('adj-warehouse') ? document.getElementById('adj-warehouse').value : '';
            const notes = document.getElementById('adj-notes') ? document.getElementById('adj-notes').value.trim() : '';
            const currency = document.getElementById('adj-currency') ? document.getElementById('adj-currency').value : 'SDG';
            const rate = parseFloat(document.getElementById('adj-exchange-rate') ? document.getElementById('adj-exchange-rate').value : 1) || 1;

            const itemsToAdjust = [];
            let totalAdjVal = 0;

            stocktakeData.forEach(d => {
                const physInput = document.getElementById(`phys-${d.itemId}`);
                const physQty = physInput ? (parseFloat(physInput.value) || 0) : d.bookQty;
                const diff = physQty - d.bookQty;
                if (diff !== 0) {
                    itemsToAdjust.push({
                        itemId: d.itemId,
                        unitId: 'base',
                        unitName: d.unit,
                        quantity: diff,
                        customBaseQty: diff,
                        customFactor: 1,
                        price: d.price / rate,
                        taxRate: 0,
                        total: (diff * d.price) / rate
                    });
                    totalAdjVal += diff * d.price;
                }
            });

            if (itemsToAdjust.length === 0) {
                if (typeof showToast === 'function') {
                    showToast('لم يتم إجراء أي تعديل على الكميات لتسويتها!', 'warning');
                } else {
                    alert('لم يتم إجراء أي تعديل على الكميات لتسويتها!');
                }
                return;
            }

            const totalAdjValForeign = totalAdjVal / rate;

            const confirmMsg = `هل أنت متأكد من ترحيل هذه التسوية؟\nسيتم تعديل أرصدة الأصناف (${itemsToAdjust.length} صنف) في المخازن، وتوليد قيود تسوية الفروقات تلقائياً بقيمة إجمالية ${totalAdjValForeign.toLocaleString()} ${currency}.`;
            if (!confirm(confirmMsg)) return;

            const adjInv = {
                id: 'ADJ-' + Date.now(),
                isAdjustment: true,
                warehouse: wh,
                warehouseId: wh,
                date: new Date().toISOString(),
                notes: notes || 'تسوية مخزنية للفروقات',
                currency: currency,
                exchangeRate: rate,
                items: itemsToAdjust,
                grandTotal: totalAdjValForeign,
                status: 'completed',
                createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown'
            };

            let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            allPurchases.push(adjInv);
            localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));

            let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            itemsToAdjust.forEach(adj => {
                let entry = currentInvData.find(inv => String(inv.itemId) === String(adj.itemId) && inv.warehouse === wh);
                if (entry) {
                    entry.qty = (parseFloat(entry.qty) || 0) + adj.quantity;
                } else {
                    currentInvData.push({
                        itemId: adj.itemId,
                        warehouse: wh,
                        qty: adj.quantity,
                        minQty: 10
                    });
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));
            let adjHistory = JSON.parse(localStorage.getItem('erp_inventory_adjustments_v1') || '[]');
            adjHistory.unshift({
                id: adjInv.id,
                date: new Date().toISOString().split('T')[0],
                warehouse: wh,
                itemsCount: itemsToAdjust.length,
                totalShortage: itemsToAdjust.filter(i => i.quantity < 0).reduce((s, i) => s + Math.abs(i.quantity), 0),
                totalSurplus: itemsToAdjust.filter(i => i.quantity > 0).reduce((s, i) => s + i.quantity, 0),
                netValue: totalAdjVal,
                items: itemsToAdjust,
                createdAt: new Date().toISOString(),
                status: 'applied'
            });
            localStorage.setItem('erp_inventory_adjustments_v1', JSON.stringify(adjHistory));
            try {
                const absVal = Math.abs(totalAdjVal);
                if (absVal > 0) {
                    const jvList = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]');
                    const isSurplus = totalAdjVal >= 0;
                    const jvNo = 'JV-' + adjInv.id;
                    jvList.unshift({
                        id: jvNo,
                        voucherNo: jvNo,
                        date: new Date().toISOString().split('T')[0],
                        description: `قيد تسوية جردية من شاشة حركات البضائع [${adjInv.id}] لمخزن (${wh})`,
                        totalDebit: absVal,
                        totalCredit: absVal,
                        isPosted: true,
                        source: 'inventory_movement_stocktake',
                        lines: [
                            { accountName: isSurplus ? `ح/ مخزون البضاعة - ${wh}` : `ح/ مصروف عجز وتوالف المخزون - ${wh}`, debit: absVal, credit: 0, note: adjInv.notes },
                            { accountName: isSurplus ? `ح/ أرباح وفروقات الجرد المخزني (فائض)` : `ح/ مخزون البضاعة - ${wh}`, debit: 0, credit: absVal, note: adjInv.notes }
                        ],
                        createdAt: new Date().toISOString()
                    });
                    localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvList));
                }
            } catch(eJv) {}
            if (typeof window.rebuildGLEntries === 'function') { try { window.rebuildGLEntries(); } catch(e){} }

            if (typeof showToast === 'function') {
                showToast('تم ترحيل التسوية وتوليد القيود بنجاح!', 'success');
            }
            closeStocktakeModal();

            setTimeout(() => {
                window.location.reload();
            }, 800);
        }

        function onCurrencyChange() {
            const curr = document.getElementById('adj-currency').value;
            const rateGroup = document.getElementById('exchange-rate-group');
            const rateInput = document.getElementById('adj-exchange-rate');
            
            if (curr === 'SDG') {
                rateGroup.style.display = 'none';
                rateInput.value = '1.00';
            } else {
                rateGroup.style.display = 'block';
                const dedicatedUSD = parseFloat(localStorage.getItem('erp_rate_usd')) || 600.00;
                if (curr === 'USD') rateInput.value = dedicatedUSD.toFixed(2);
                else if (curr === 'SAR') rateInput.value = '160.00';
                else if (curr === 'AED') rateInput.value = '163.00';
            }

            const currencyTh = document.getElementById('currency-th-label');
            if (currencyTh) {
                currencyTh.textContent = `قيمة الفرق (${curr})`;
            }

            const totalValLabel = document.getElementById('adj-total-val-label');
            if (totalValLabel) {
                totalValLabel.textContent = curr;
            }

            updateAllDiffs();
        }

        function updateAllDiffs() {
            stocktakeData.forEach(d => {
                calculateStocktakeDiff(d.itemId);
            });
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

        <!-- Stocktake Modal -->
        <div id="stocktake-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; direction: rtl; backdrop-filter: blur(8px);">
            <div class="glass-panel" style="width: 95%; max-width: 1100px; max-height: 90vh; display: flex; flex-direction: column; padding: 25px; position: relative; border: 1px solid rgba(16, 185, 129, 0.4); background: #0f172a; border-radius: 16px;">
                <button onclick="closeStocktakeModal()" style="position: absolute; top: 15px; left: 15px; background: none; border: none; color: #94a3b8; font-size: 1.8rem; cursor: pointer; transition: 0.3s;" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#94a3b8'">&times;</button>
                
                <h3 style="color: #10b981; margin: 0 0 20px 0; font-weight: 900; border-bottom: 1px solid rgba(16, 185, 129, 0.2); padding-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.8rem;">⚙️</span> تسوية وجرد المخزون (Physical Stocktake)
                </h3>
                
                <div style="display: grid; grid-template-columns: 2fr 1.5fr 1.5fr 3fr; gap: 15px; margin-bottom: 20px;">
                    <div class="f-group" style="margin:0;">
                        <label style="color:#94a3b8; font-size:0.75rem; font-weight:700; margin-bottom:5px; display:block;">المخزن المستهدف بالتسوية</label>
                        <select id="adj-warehouse" class="f-input" onchange="loadStocktakeItems()" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:#fff; padding:10px 15px; outline:none;">
                        </select>
                    </div>
                    <div class="f-group" style="margin:0;">
                        <label style="color:#94a3b8; font-size:0.75rem; font-weight:700; margin-bottom:5px; display:block;">العملة</label>
                        <select id="adj-currency" class="f-input" onchange="onCurrencyChange()" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:#fff; padding:10px 15px; outline:none;">
                            <option value="SDG">SDG (العملة المحلية)</option>
                            <option value="USD">USD (الدولار الأمريكي)</option>
                            <option value="SAR">SAR (الريال السعودي)</option>
                            <option value="AED">AED (الدرهم الإماراتي)</option>
                        </select>
                    </div>
                    <div class="f-group" id="exchange-rate-group" style="margin:0; display:none;">
                        <label style="color:#94a3b8; font-size:0.75rem; font-weight:700; margin-bottom:5px; display:block;">سعر الصرف</label>
                        <input type="number" id="adj-exchange-rate" value="1.00" step="0.01" oninput="updateAllDiffs()" style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:#fff; padding:10px 15px; outline:none;">
                    </div>
                    <div class="f-group" style="margin:0;">
                        <label style="color:#94a3b8; font-size:0.75rem; font-weight:700; margin-bottom:5px; display:block;">ملاحظات / سبب التسوية</label>
                        <input type="text" id="adj-notes" placeholder="مثال: تسوية الجرد السنوي للعام 2026..." style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:#fff; padding:10px 15px; outline:none;">
                    </div>
                </div>

                <div style="flex: 1; overflow-y: auto; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.05); border-radius:12px; background:rgba(0,0,0,0.2);">
                    <table class="dash-table" style="width:100%; border-collapse:collapse; min-width:unset !important;">
                        <thead>
                            <tr>
                                <th>اسم الصنف</th>
                                <th style="text-align:center; width:120px;">الرصيد الدفتري</th>
                                <th style="text-align:center; width:140px;">الجرد الفعلي</th>
                                <th style="text-align:center; width:120px; text-shadow:0 0 10px rgba(16,185,129,0.3);">الزيادة / النقصان</th>
                                <th style="text-align:center; width:140px;" id="currency-th-label">قيمة الفرق (SDG)</th>
                            </tr>
                        </thead>
                        <tbody id="stocktake-tbody">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid rgba(255,255,255,0.08); padding-top:15px;">
                    <div style="display:flex; gap:20px; font-weight:700; font-size:1rem; color:#f8fafc;">
                        <div>إجمالي قيمة التعديل: <span id="adj-total-val" style="color:#fbbf24;">0.00</span> <span id="adj-total-val-label">SDG</span></div>
                        <div style="font-size:0.85rem; color:#94a3b8; align-self:center;">(الزيادة باللون الأخضر، والعجز باللون الوردي)</div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button onclick="closeStocktakeModal()" class="btn-action btn-print" style="padding:10px 20px;">إلغاء</button>
                        <button onclick="saveStocktake()" class="btn-action" style="background:linear-gradient(135deg,#10b981,#059669); color:white; border:none; padding:10px 25px; box-shadow:0 4px 15px rgba(16,185,129,0.3);">ترحيل التسوية وتوليد القيود 💾</button>
                    </div>
                </div>
            </div>
        </div>
        <style>@keyframes pulse { 0% { transform: scale(0.95); opacity: 0.8; } 50% { transform: scale(1.05); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.8; } }
        
        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body,
        :root.light-theme .ambient-bg {
            background: #f8fafc !important;
        }
        :root.light-theme .glass-panel,
        :root.light-theme .neo-card,
        :root.light-theme .summary-card,
        :root.light-theme .filter-panel,
        :root.light-theme .chart-card,
        :root.light-theme .kpi-card,
        :root.light-theme .premium-header,
        :root.light-theme .top-header,
        :root.light-theme .header-area {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            color: #0f172a !important;
        }
        :root.light-theme .glass-panel *,
        :root.light-theme .neo-card *,
        :root.light-theme .summary-card * {
            text-shadow: none !important;
        }
        :root.light-theme table.pro-table th,
        :root.light-theme .pos-grid thead th,
        :root.light-theme .ledger-table th,
        :root.light-theme .report-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        :root.light-theme table.pro-table td,
        :root.light-theme .pos-grid tbody td,
        :root.light-theme .ledger-table td,
        :root.light-theme .report-table td {
            color: #1e293b !important;
            border-bottom: 1px solid rgba(0,0,0,0.08) !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td,
        :root.light-theme .pos-grid tbody tr:hover td,
        :root.light-theme .ledger-table tbody tr:hover td,
        :root.light-theme .report-table tbody tr:hover td {
            background: rgba(0,0,0,0.02) !important;
        }
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .tot-lbl,
        :root.light-theme .lbl,
        :root.light-theme label,
        :root.light-theme .table-header {
            color: #334155 !important;
        }
        :root.light-theme .tot-val,
        :root.light-theme .val {
            color: #0f172a !important;
        }
        :root.light-theme .h-form-group,
        :root.light-theme .control-panel {
            background: transparent !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        :root.light-theme .modern-btn {
            color: #1e293b !important;
        }
        :root.light-theme .modern-btn:hover {
            color: #0f172a !important;
        }
        /* Neon text colors correction for light theme */
        :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
        :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
        :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
        :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }
        :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }
    </style>

    <script>
    function runAIInventory() {
        document.getElementById('aiInv-modal').style.display = 'flex';
        document.getElementById('aiInv-content').innerHTML = `
            <div style="text-align: center; padding: 40px 0;">
                <div style="font-size: 3rem; margin-bottom: 15px; animation: pulse 1.5s infinite;">📦</div>
                <div style="color: #94a3b8;">جاري جرد وتقييم الأصناف لمعرفة التكدس والنواقص... يرجى الانتظار</div>
            </div>`;
        
        let inventoryData = [];
        let rows = document.querySelectorAll('#inventory-tbody tr');
        let limit = Math.min(rows.length, 100); 
        
        for(let i=0; i<limit; i++) {
            let cols = rows[i].querySelectorAll('td');
            if(cols.length >= 9) {
                let name = cols[1].innerText.trim();
                let qty = cols[4].innerText.trim();
                let cost = cols[7].innerText.trim();
                let totalCost = cols[8].innerText.trim();
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
</body>
</html>

