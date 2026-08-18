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
    <title>مقاييس (MAGAIEES) - تقارير وجرد المخازن</title>
    <link rel="stylesheet" href="css/design-system.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/components.css?v=<?php echo time(); ?>">
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
            display: flex; gap: 6px; flex-wrap: wrap; overflow: visible;
            background: var(--surface-card); padding: 6px 10px; border-radius: 14px; 
            border: 1px solid var(--glass-border); box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            backdrop-filter: blur(20px); align-items: center; width: 100%; z-index: 100; position: relative;
            min-height: 48px; box-sizing: border-box;
        }
        .nav-tabs::-webkit-scrollbar { display: none; }
        .nav-cluster {
            display: flex; align-items: center; gap: 4px;
        }
        .nav-divider {
            width: 1px; height: 24px; background: rgba(255, 255, 255, 0.12); margin: 0 4px; flex-shrink: 0;
        }
        .nav-tab {
            padding: 8px 15px; border-radius: 9px; text-decoration: none; font-weight: 700;
            color: var(--text-secondary); background: transparent; border: 1px solid transparent;
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
            background: #ec4899; color: #fff; font-size: 0.68rem; padding: 2px 5px; border-radius: 8px; font-weight: 900;
        }

        /* Top Studio Button on the Far Left */
        .btn-studio-top {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 8px 18px; border-radius: 10px; color: #fff; font-weight: 800; font-size: 0.88rem;
            cursor: pointer; display: inline-flex; align-items: center; gap: 7px; height: 38px; box-sizing: border-box;
            transition: all 0.25s ease; white-space: nowrap; text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        .btn-studio-top:hover {
            transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            border-color: rgba(255, 255, 255, 0.4);
        }
        .btn-icon-nav {
            background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border);
            display: flex; align-items: center; justify-content: center; width: 38px; height: 38px;
            border-radius: 10px; color: var(--text-main); cursor: pointer; transition: 0.25s; font-size: 1.1rem;
        }
        .btn-icon-nav:hover {
            background: rgba(255,255,255,0.1); color: #fff; transform: translateY(-1px);
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

        /* Unified Filter Hub (Streamlined Single Row) */
        .filters-hub {
            background: var(--surface-card); backdrop-filter: blur(20px); border-radius: 14px;
            border: 1px solid var(--glass-border); padding: 10px 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.18);
        }
        .filters-main-row {
            display: flex; gap: 10px; align-items: center; flex-wrap: wrap;
        }
        .f-search-wrapper { position: relative; flex: 1.8; min-width: 220px; display: flex; align-items: center; }
        .f-search-icon { position: absolute; right: 12px; font-size: 1rem; color: #64748b; pointer-events: none; }
        .f-search-clear {
            position: absolute; left: 10px; font-size: 0.85rem; color: #64748b; cursor: pointer;
            background: rgba(255,255,255,0.08); border-radius: 50%; width: 20px; height: 20px;
            display: none; align-items: center; justify-content: center; transition: 0.2s;
        }
        .f-search-clear:hover { background: rgba(255,255,255,0.2); color: #fff; }
        .f-input {
            background: rgba(0,0,0,0.25); border: 1px solid var(--glass-border); border-radius: 10px;
            color: var(--text-primary); padding: 10px 38px 10px 14px; font-size: 0.9rem; outline: none; transition: 0.2s;
            font-family: inherit; width: 100%; box-sizing: border-box; height: 42px; font-weight: 600;
        }
        .f-input:focus { border-color: #38bdf8; box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.15); background: rgba(0,0,0,0.4); }
        .f-select {
            padding: 0 32px 0 14px; cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: left 12px center; background-size: 14px;
        }
        .f-select option { background: #0f172a; color: #f8fafc; padding: 8px; }
        .f-select-quick {
            border-color: rgba(56, 189, 248, 0.4); background-color: rgba(56, 189, 248, 0.05);
            color: #38bdf8; font-weight: 700;
        }
        .f-select-quick:focus {
            border-color: #38bdf8; box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
        }
        .filter-badge-pill {
            font-size: 0.88rem; font-weight: 800; color: #38bdf8; background: rgba(56,189,248,0.08);
            padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(56,189,248,0.25);
            white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; height: 42px; box-sizing: border-box;
        }

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
            padding: 12px 26px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; cursor: pointer;
            border: 1px solid transparent; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex; align-items: center; gap: 8px; color: #fff; height: 46px; box-sizing: border-box;
        }
        .btn-action-tool:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.35); }

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

        /* Custom Report Studio Styles */
        .studio-card-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;
        }
        .studio-chip {
            background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 10px;
            padding: 9px 12px; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;
            color: #cbd5e1; user-select: none; font-size: 0.85rem; font-weight: 700;
        }
        .studio-chip:hover {
            background: rgba(255,255,255,0.07); border-color: rgba(99, 102, 241, 0.4); color: #fff;
        }
        .studio-chip input[type="checkbox"], .studio-chip input[type="radio"] {
            accent-color: #8b5cf6; width: 16px; height: 16px; cursor: pointer;
        }
        .studio-chip.checked {
            background: rgba(99, 102, 241, 0.15); border-color: rgba(139, 92, 246, 0.5); color: #c084fc;
        }
        .studio-section-title {
            color: #c084fc; font-weight: 800; font-size: 0.95rem; margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(139, 92, 246, 0.2);
            padding-bottom: 6px;
        }
        .studio-tab-btn {
            padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer;
            background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border); color: var(--text-secondary);
            transition: 0.2s;
        }
        .studio-tab-btn.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border-color: transparent;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
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

        <!-- Top Navigation Row (Clustered Smart Hub + Studio Button on Far Left) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; gap: 10px; flex-wrap: nowrap;">
            <div class="nav-tabs" style="margin-bottom: 0; flex: 1;">
                
                <!-- Cluster 1: نظام الجرد -->
                <div class="nav-cluster">
                    <a href="inventory-report.php" class="nav-tab active" title="نظام الجرد الشامل بجميع المخازن">
                        <span>📦</span> <span>الجرد الشامل للمخازن</span>
                    </a>
                </div>

                <div class="nav-divider"></div>

                <!-- Cluster 2: حركات البضائع -->
                <div class="nav-cluster nav-dropdown">
                    <div class="nav-tab" title="حركات البضائع">
                        <span>📑</span> <span>حركات البضائع</span>
                        <span style="font-size: 0.7rem; margin-right: 2px;">▼</span>
                    </div>
                    <div class="nav-dropdown-content">
                        <a href="item-ledger.php" class="nav-dropdown-item" title="كشف حركة صنف محدد">
                            <span>📑</span> حركة الأصناف
                        </a>
                        <a href="warehouse-ledger.php" class="nav-dropdown-item" title="كشف حركة مخزن محدد">
                            <span>🏭</span> حركة المخازن
                        </a>
                        <a href="inventory-summary.php" class="nav-dropdown-item" title="ملخص الوارد والمنصرف">
                            <span>📊</span> ملخص الحركات
                        </a>
                    </div>
                </div>

                <div class="nav-divider"></div>

                <!-- Cluster 3: مركز الذكاء الاصطناعي (AI Suite) -->
                <div class="nav-cluster nav-dropdown">
                    <div class="nav-tab nav-tab-ai" title="أدوات الذكاء الاصطناعي">
                        <span>🤖</span> <span>أدوات الذكاء الاصطناعي</span>
                        <span class="nav-ai-badge">AI</span>
                        <span style="font-size: 0.7rem; margin-right: 2px;">▼</span>
                    </div>
                    <div class="nav-dropdown-content">
                        <a href="ai_goods_advisor.php" class="nav-dropdown-item" title="توصيات الشراء واستراتيجيات التخزين">
                            <span>💡</span> مستشار الـ AI للبضائع
                        </a>
                        <a href="smart_inventory_report.php" class="nav-dropdown-item" title="تقارير تفاعلية ذكية للمخزون">
                            <span>🧠</span> تقارير المخازن الذكية
                        </a>
                        <a href="javascript:void(0)" onclick="runAIInventory()" class="nav-dropdown-item" title="تحليل فوري للتكدس والنواقص">
                            <span>⚡</span> محلل المخازن الـ AI
                        </a>
                    </div>
                </div>

                <div class="nav-divider"></div>

                <!-- Cluster 4: التحليلات المرئية (Charts & Dashboards) -->
                <div class="nav-cluster nav-dropdown">
                    <div class="nav-tab" title="المخططات والرسوم البيانية">
                        <span>📈</span> <span style="font-weight: 800; color: #f8fafc;">التحليلات المرئية</span>
                        <span style="font-size: 0.7rem; margin-right: 2px;">▼</span>
                    </div>
                    <div class="nav-dropdown-content">
                        <a href="visual-analytics.php#pie" class="nav-dropdown-item" title="النسب المئوية لتوزيع البضائع في المخازن">
                            <span>🍕</span> توزيع المخزون (مخطط دائري)
                        </a>
                        <a href="visual-analytics.php#donut" class="nav-dropdown-item" title="حالة الأرصدة (آمن، نواقص، الخ)">
                            <span>🍩</span> حالة الأرصدة (كعكة مفرغة)
                        </a>
                        <a href="visual-analytics.php#bar" class="nav-dropdown-item" title="إجمالي القيمة المالية لكل صنف ومخزن">
                            <span>📊</span> قيمة المخزون (أعمدة مصمتة)
                        </a>
                    </div>
                </div>

            </div>

            <!-- Far Left Controls: Studio Button + Back Button -->
            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                <button class="btn-studio-top" onclick="openCustomReportStudio()" title="تخصيص وتصميم تقارير الجرد المتقدمة بالكامل">
                    <span>🎛️</span> <span>مصمم التقارير المخصص</span>
                </button>
                <button class="btn-icon-nav" onclick="history.back()" title="رجوع">↩</button>
            </div>
        </div>

        <!-- Unified Filter Hub (Single Line Cohesive Bar) -->
        <div class="filters-hub">
            <div class="filters-main-row">
                <div class="f-search-wrapper">
                    <span class="f-search-icon">🔍</span>
                    <input type="text" class="f-input" id="filter-search" placeholder="بحث بالاسم أو الكود (اكتب للبحث الفوري...)" oninput="onSearchInput(this)">
                    <span class="f-search-clear" id="search-clear-btn" onclick="clearSearch()" title="مسح">✕</span>
                </div>
                <div style="flex: 1.3; min-width: 200px;">
                    <select class="f-input f-select f-select-quick" id="filter-status" onchange="applyFilters()" title="التصفية السريعة لحالة الرصيد">
                        <option value="ACTIVE" selected>⚡ الأصناف المتوفرة فقط</option>
                        <option value="ALL_WITH_ZERO">🌐 عرض الكل (شامل الصفر)</option>
                        <option value="OK">🟢 رصيد آمن (فوق الحد)</option>
                        <option value="WARNING">⚠️ تحت حد الطلب (منخفض)</option>
                        <option value="DANGER">🚨 توفير النواقص (نافذ / سالب)</option>
                    </select>
                </div>
                <div style="flex: 1.1; min-width: 165px;">
                    <select class="f-input f-select" id="filter-warehouse" onchange="applyFilters()">
                        <option value="ALL">🏢 جميع المخازن (الجرد العام)</option>
                    </select>
                </div>
                <div style="flex: 1.1; min-width: 155px;">
                    <select class="f-input f-select" id="filter-category" onchange="applyFilters()">
                        <option value="ALL">🏷️ تصفية بالفئة (جميع الفئات)</option>
                    </select>
                </div>
                <div style="margin-right: auto;">
                    <span id="filtered-matches-badge" class="filter-badge-pill">0 صنف</span>
                </div>
            </div>
        </div>

        <!-- 1. Data Table (Primary Focus - Top of Screen) -->
        <div class="table-panel">
            <div class="table-wrapper" id="report-table-wrap">
                <table class="dash-table" id="inventory-main-table">
                    <thead id="inventory-thead">
                        <tr>
                            <th>الاسم والمواصفات</th>
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
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: center;">
                <button class="btn-action-tool" onclick="openStocktakeModal()" title="تعديل الأرصدة وتسوية الفروقات الجردية" style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25);">
                    <span>⚙️</span> إجراء تسوية جردية
                </button>
                <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.1); margin: 0 4px;"></div>
                <button class="btn-action-tool" onclick="openReportPreview('تقرير جرد الأرصدة العام', '#report-table-wrap')" style="background: linear-gradient(135deg, #0d9488, #0f766e); box-shadow: 0 4px 15px rgba(13, 148, 136, 0.35);">
                    <span>👁️</span> معاينة
                </button>
                <button class="btn-action-tool" onclick="printReportDirectly('تقرير جرد الأرصدة العام', '#report-table-wrap')" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border); color: var(--text-primary);">
                    <span>🖨️</span> طباعة
                </button>
                <button class="btn-action-tool" onclick="window.openExportModal(exportExcel, {element: document.getElementById('report-table-wrap'), filename: 'inventory-report.pdf', orientation: 'landscape'})" style="background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 4px 15px rgba(5, 150, 105, 0.35);">
                    <span>📥</span> تصدير
                </button>
            </div>
        </div>

        <!-- 4. Visual Analytics Dashboard (Bottom of Page) -->
        <div id="auto-charts-slot" class="auto-charts-slot" style="width: 100%; margin-top: 10px;"></div>

    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
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
            });

            // 1. ADD Purchases (Inward) — SUBTRACT if Return
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            purchases.forEach(inv => {
                let hdrWhName = getWarehouseName(inv.warehouseId || inv.warehouse);
                const isReturn = inv.isReturn === true;
                (inv.items || inv.lines || []).forEach(line => {
                    let lineWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
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
            sales = sales.filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
            sales.forEach(inv => {
                let hdrWhName = getWarehouseName(inv.warehouse);
                (inv.items || []).forEach(line => {
                    let lineWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
                    let key = String(line.itemId) + '|' + lineWhName;
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

                    if (inv.isReturn === true) {
                        // Sales return: add back to stock
                        actualStockMap[key] = (actualStockMap[key] || 0) + baseQty;
                    } else if (inv.saleType === 'reserved') {
                        if (inv.deliveryStatus === 'delivered') {
                            actualStockMap[key] = (actualStockMap[key] || 0) - baseQty;
                        } else {
                            reservedStockMap[key] = (reservedStockMap[key] || 0) + baseQty;
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

        // =========================================================================
        // Custom Report Studio Configuration & State
        // =========================================================================
        const COLUMN_DEFS = {
            item: { title: 'الاسم والمواصفات', align: 'right' },
            code: { title: 'كود الصنف', align: 'center' },
            category: { title: 'فئة الصنف', align: 'center' },
            warehouse: { title: 'المخزن', align: 'right' },
            actualQty: { title: 'الرصيد الفعلي', align: 'center' },
            reservedQty: { title: 'الرصيد المحجوز', align: 'center' },
            availQty: { title: 'المتاح للبيع', align: 'center' },
            minQty: { title: 'حد الطلب', align: 'center' },
            costPrice: { title: 'سعر التكلفة', align: 'right' },
            cashPrice: { title: 'سعر نقدي', align: 'right' },
            wholesalePrice: { title: 'سعر جملة', align: 'right' },
            retailPrice: { title: 'سعر قطاعي', align: 'right' },
            totalValue: { title: 'إجمالي القيمة', align: 'right' },
            status: { title: 'حالة المخزون', align: 'center' },
            actions: { title: 'إجراءات', align: 'center' }
        };

        let customReportConfig = {
            cols: {
                item: true,
                code: true,
                category: true,
                warehouse: true,
                actualQty: true,
                reservedQty: true,
                availQty: true,
                minQty: false,
                costPrice: true,
                cashPrice: false,
                wholesalePrice: false,
                retailPrice: false,
                totalValue: true,
                status: true,
                actions: true
            },
            unitMode: 'compound', // 'compound', 'base', 'smallest'
            showUnitRelations: true,
            qtyDecimals: 2,
            priceDecimals: 2,
            showCurrencySymbol: true,
            fontSize: '14px',
            rowDensity: 'normal', // 'compact', 'normal', 'spacious'
            highlightColor: '#38bdf8',
            sortBy: 'default'
        };

        // Load saved custom preset if available
        try {
            const savedPreset = localStorage.getItem('erp_custom_report_preset_v1');
            if (savedPreset) {
                const parsed = JSON.parse(savedPreset);
                customReportConfig = { ...customReportConfig, ...parsed, cols: { ...customReportConfig.cols, ...(parsed.cols || {}) } };
            }
        } catch(e) {}

        // Helper: Render quantity in all configured units (Compound Hierarchical / Single Unit Display)
        function renderMultiUnitQty(baseQty, unitsList, themeColor = '#38bdf8') {
            if (baseQty === undefined || baseQty === null) baseQty = 0;
            const num = Number(baseQty) || 0;
            const baseUnit = (unitsList && unitsList.length > 0) ? unitsList[0] : { name: 'وحدة', factor: 1 };
            const qDec = (typeof customReportConfig !== 'undefined' && customReportConfig.qtyDecimals !== undefined) ? customReportConfig.qtyDecimals : 2;
            const unitMode = (typeof customReportConfig !== 'undefined' && customReportConfig.unitMode) ? customReportConfig.unitMode : 'compound';
            
            const isNegative = num < 0;
            const minusSign = isNegative ? `<span style="color:#ef4444; font-weight:900; font-size:1.2rem; margin-left: 2px;">-</span>` : '';
            const absNum = Math.abs(num);
            const formattedBase = absNum.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: qDec });

            // 1. Base Unit Only Mode
            if (unitMode === 'base' || !unitsList || unitsList.length <= 1) {
                return `<div style="display:inline-flex; align-items:baseline; justify-content:center; gap:4px;" title="إجمالي الرصيد: ${formattedBase} ${baseUnit.name}">
                    ${minusSign}<span style="font-size:1.15rem; font-weight:900; color:${themeColor};">${formattedBase}</span>
                    <span style="font-size:0.75rem; font-weight:800; color:#cbd5e1; background:rgba(255,255,255,0.08); padding:2px 6px; border-radius:4px;">${baseUnit.name}</span>
                </div>`;
            }

            // 2. Smallest Unit Only Mode
            if (unitMode === 'smallest') {
                const smallestUnit = unitsList[unitsList.length - 1];
                const smallestFactor = parseFloat(smallestUnit.factor) || 1;
                const totalInSmallest = absNum * smallestFactor;
                const formattedSmallest = totalInSmallest.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: qDec });
                return `<div style="display:inline-flex; align-items:baseline; justify-content:center; gap:4px;" title="الرصيد بوحدة التجزئة: ${formattedSmallest} ${smallestUnit.name}">
                    ${minusSign}<span style="font-size:1.15rem; font-weight:900; color:${themeColor};">${formattedSmallest}</span>
                    <span style="font-size:0.75rem; font-weight:800; color:#cbd5e1; background:rgba(255,255,255,0.08); padding:2px 6px; border-radius:4px;">${smallestUnit.name}</span>
                </div>`;
            }

            // 3. Smart Compound Units Logic
            const baseIsLarge = isLargeUnitName(baseUnit.name);
            const baseIsSmall = isSmallUnitName(baseUnit.name);
            
            const isContinuous = unitsList.some(u => {
                const n = (u.name || '').trim();
                return n.includes('وزن') || n.includes('كيلو') || n.includes('كجم') || n.includes('جرام') || n.includes('لتر') || n.includes('متر') || n === 'طن';
            });
            
            let normalized = unitsList.map(u => {
                let factor = parseFloat(u.factor) || 1;
                if (u.isBase) return { ...u, multiplier: 1 };
                
                const uIsLarge = isLargeUnitName(u.name);
                const uIsSmall = isSmallUnitName(u.name);
                
                let multiplier = 1;
                if (baseIsLarge && uIsSmall) multiplier = 1 / factor;
                else if (baseIsSmall && uIsLarge) multiplier = factor;
                else if (uIsSmall) multiplier = 1 / factor; 
                else if (uIsLarge) multiplier = factor;
                else multiplier = factor;
                return { ...u, multiplier };
            });

            const minMultiplier = Math.min(...normalized.map(u => u.multiplier));
            normalized = normalized.map(u => ({ ...u, atomicMultiplier: u.multiplier / minMultiplier }));
            normalized.sort((a, b) => b.atomicMultiplier - a.atomicMultiplier);

            const baseUnitNormalized = normalized.find(u => u.isBase) || normalized[0];
            let badges = [];

            if (isContinuous) {
                // Stacked Independent Display for Weights/Measurements
                let stackHtml = '';
                normalized.forEach((unit, i) => {
                    let totalInThisUnit = (absNum * baseUnitNormalized.atomicMultiplier) / unit.atomicMultiplier;
                    let formattedQty = totalInThisUnit.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: qDec });
                    
                    if (i === 0) {
                        stackHtml += `<div style="font-size: 1.15rem; font-weight: 900; color: ${themeColor}; line-height: 1.1; text-shadow: 0 0 10px ${themeColor}40;">
                            ${minusSign}${formattedQty} <span style="font-size: 0.8rem; color: #cbd5e1; font-weight: 800;">${unit.name}</span>
                        </div>`;
                    } else {
                        stackHtml += `<div style="font-size: 0.95rem; font-weight: 800; color: #94a3b8; margin-top: 4px; padding-top: 4px; border-top: 1px dashed rgba(255,255,255,0.08);">
                            ${minusSign}${formattedQty} <span style="font-size: 0.75rem; color: #64748b;">${unit.name}</span>
                        </div>`;
                    }
                });

                return `<div style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: rgba(0,0,0,0.15); padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.04); min-width: 90px;" title="كميات موازية (وزن / قياس متصل)">
                    ${stackHtml}
                </div>`;
            } else {
                // Compound Discrete Display (e.g. 2 tons AND 16 rebars)
                let remainingAtomicQty = absNum * baseUnitNormalized.atomicMultiplier;
                for (let i = 0; i < normalized.length; i++) {
                    let unit = normalized[i];
                    let qty;
                    if (i === normalized.length - 1) {
                        qty = remainingAtomicQty / unit.atomicMultiplier;
                        remainingAtomicQty = 0;
                    } else {
                        qty = Math.floor(remainingAtomicQty / unit.atomicMultiplier);
                        remainingAtomicQty -= qty * unit.atomicMultiplier;
                    }
                    
                    if (qty > 0.0001 || (i === normalized.length - 1 && badges.length === 0)) {
                        let formattedQty = qty.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: qDec });
                        badges.push(`<span style="background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.12); padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: baseline; gap: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            <strong style="color:${themeColor}; font-size:1.05rem; text-shadow: 0 0 10px ${themeColor}40;">${formattedQty}</strong>
                            <span style="color:#cbd5e1; font-size:0.75rem; font-weight:700;">${unit.name}</span>
                        </span>`);
                    }
                }
                return `<div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 4px;" title="إجمالي الرصيد: ${formattedBase} ${baseUnit.name}">
                    ${minusSign}
                    ${badges.join(' ')}
                </div>`;
            }
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

        // =========================================================================
        // Dynamic Executive Data Table Rendering
        // =========================================================================
        function applyFilters() {
            const fWh = document.getElementById('filter-warehouse') ? document.getElementById('filter-warehouse').value : 'ALL';
            const fCat = document.getElementById('filter-category') ? document.getElementById('filter-category').value : 'ALL';
            const fStatus = document.getElementById('filter-status') ? document.getElementById('filter-status').value : 'ACTIVE';
            const fSearch = document.getElementById('filter-search') ? document.getElementById('filter-search').value.toLowerCase().trim() : '';

            const mainTable = document.getElementById('inventory-main-table') || document.querySelector('.dash-table');
            const thead = document.getElementById('inventory-thead') || (mainTable ? mainTable.querySelector('thead') : null);
            const tbody = document.getElementById('inventory-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';

            // Apply font size and row density
            if (mainTable) {
                mainTable.style.fontSize = customReportConfig.fontSize || '14px';
                if (customReportConfig.rowDensity === 'compact') {
                    mainTable.style.setProperty('--row-padding', '5px 8px');
                } else if (customReportConfig.rowDensity === 'spacious') {
                    mainTable.style.setProperty('--row-padding', '14px 16px');
                } else {
                    mainTable.style.setProperty('--row-padding', '10px 14px');
                }
            }

            // Dynamically Render THEAD based on customReportConfig.cols
            let visibleColCount = 0;
            if (thead) {
                let thsHtml = '';
                for (const [colKey, isVisible] of Object.entries(customReportConfig.cols)) {
                    if (isVisible && COLUMN_DEFS[colKey]) {
                        visibleColCount++;
                        const col = COLUMN_DEFS[colKey];
                        const alignStyle = col.align === 'center' ? 'text-align: center;' : (col.align === 'left' ? 'text-align: left;' : 'text-align: right;');
                        thsHtml += `<th style="${alignStyle} padding: ${customReportConfig.rowDensity === 'compact' ? '7px 10px' : '11px 14px'};">${col.title}</th>`;
                    }
                }
                thead.innerHTML = `<tr>${thsHtml}</tr>`;
            }

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

            let filteredData = reportData.filter(row => {
                if (fWh !== 'ALL' && row.warehouse !== fWh) return false;
                if (fCat !== 'ALL' && row.category !== fCat) return false;
                if (fSearch && !row.itemName.toLowerCase().includes(fSearch) && !row.itemId.toLowerCase().includes(fSearch)) return false;
                
                let actualQty = Number(row.qty) || 0;
                let reservedQty = Number(row.reservedQuantity) || 0;
                let availQty = actualQty - reservedQty;

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
                    return statusInfo.code === 'DANGER' || isZeroStock || availQty <= 0;
                } else {
                    return !isZeroStock;
                }
            });

            // Sorting
            if (customReportConfig.sortBy === 'value_desc') {
                filteredData.sort((a, b) => ((b.qty - (b.reservedQuantity || 0)) * b.price) - ((a.qty - (a.reservedQuantity || 0)) * a.price));
            } else if (customReportConfig.sortBy === 'qty_desc') {
                filteredData.sort((a, b) => (Number(b.qty) || 0) - (Number(a.qty) || 0));
            } else if (customReportConfig.sortBy === 'qty_asc') {
                filteredData.sort((a, b) => (Number(a.qty) || 0) - (Number(b.qty) || 0));
            } else if (customReportConfig.sortBy === 'name_asc') {
                filteredData.sort((a, b) => (a.itemName || '').localeCompare(b.itemName || '', 'ar'));
            } else if (customReportConfig.sortBy === 'code_asc') {
                filteredData.sort((a, b) => (a.itemId || '').localeCompare(b.itemId || ''));
            }

            const pDec = customReportConfig.priceDecimals !== undefined ? customReportConfig.priceDecimals : 2;
            const qDec = customReportConfig.qtyDecimals !== undefined ? customReportConfig.qtyDecimals : 2;
            const activeHighlightColor = customReportConfig.highlightColor || '#38bdf8';
            const cellPad = customReportConfig.rowDensity === 'compact' ? 'padding: 6px 10px;' : (customReportConfig.rowDensity === 'spacious' ? 'padding: 14px 16px;' : 'padding: 10px 14px;');

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
                    unitsRelationInfo = `<div style="font-size:0.72rem; color:${activeHighlightColor}; margin-top:3px; font-weight:700;">⚖️ ${rels}</div>`;
                }

                let tdsHtml = '';
                for (const [colKey, isVisible] of Object.entries(customReportConfig.cols)) {
                    if (!isVisible) continue;

                    if (colKey === 'item') {
                        tdsHtml += `<td style="${cellPad} font-weight:700; color:var(--text-primary);">
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                                ${!customReportConfig.cols.code ? `<span class="code-pill" onclick="copyItemCode('${row.itemId}', event)" title="انقر لنسخ الكود" style="font-size: 0.7rem; padding: 2px 6px;">📋 ${row.itemId}</span>` : ''}
                                ${!customReportConfig.cols.category ? `<span style="background:rgba(255,255,255,0.04); padding:2px 6px; border-radius:6px; font-weight:600; font-size:0.7rem; border:1px solid var(--glass-border); color: #94a3b8;">${row.category}</span>` : ''}
                            </div>
                            <div style="font-size:0.95rem; color:#f8fafc;">${row.itemName}</div>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">الوحدة الرئيسية: <strong style="color:#cbd5e1;">${unitsList[0].name}</strong></div>
                            ${customReportConfig.showUnitRelations ? unitsRelationInfo : ''}
                        </td>`;
                    } else if (colKey === 'code') {
                        tdsHtml += `<td style="${cellPad} text-align:center;"><span class="code-pill" onclick="copyItemCode('${row.itemId}', event)" title="انقر لنسخ الكود">📋 ${row.itemId}</span></td>`;
                    } else if (colKey === 'category') {
                        tdsHtml += `<td style="${cellPad} text-align:center;"><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-weight:700; font-size:0.78rem; border:1px solid var(--glass-border); color:#94a3b8;">${row.category}</span></td>`;
                    } else if (colKey === 'warehouse') {
                        tdsHtml += `<td style="${cellPad}"><span style="font-weight:700; color:#cbd5e1;">${row.warehouse}</span></td>`;
                    } else if (colKey === 'actualQty') {
                        tdsHtml += `<td style="${cellPad} text-align:center;">${renderMultiUnitQty(actualQty, unitsList, '#38bdf8')}</td>`;
                    } else if (colKey === 'reservedQty') {
                        tdsHtml += `<td style="${cellPad} text-align:center;">${reservedQty > 0 ? renderMultiUnitQty(reservedQty, unitsList, '#fbbf24') : '<span style="color:var(--text-muted); font-weight:700;">-</span>'}</td>`;
                    } else if (colKey === 'availQty') {
                        tdsHtml += `<td style="${cellPad} text-align:center;">${renderMultiUnitQty(availQty, unitsList, statusInfo.color)}</td>`;
                    } else if (colKey === 'minQty') {
                        const minFormatted = (row.minQty || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: qDec });
                        tdsHtml += `<td style="${cellPad} text-align:center; font-weight:700; color:#94a3b8;">${minFormatted} <span style="font-size:0.75rem;">${unitsList[0].name}</span></td>`;
                    } else if (colKey === 'costPrice') {
                        const costFormatted = (row.price || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: pDec });
                        tdsHtml += `<td style="${cellPad} font-weight:700; color:#cbd5e1; direction:ltr; text-align:left;">${costFormatted}</td>`;
                    } else if (colKey === 'cashPrice') {
                        const cp = (row.rawItem && row.rawItem.cashPrice) ? Number(row.rawItem.cashPrice) : (row.price || 0);
                        const cpFormatted = cp.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: pDec });
                        tdsHtml += `<td style="${cellPad} font-weight:700; color:#38bdf8; direction:ltr; text-align:left;">${cpFormatted}</td>`;
                    } else if (colKey === 'wholesalePrice') {
                        const wp = (row.rawItem && row.rawItem.wholesalePrice) ? Number(row.rawItem.wholesalePrice) : 0;
                        const wpFormatted = wp.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: pDec });
                        tdsHtml += `<td style="${cellPad} font-weight:700; color:#a78bfa; direction:ltr; text-align:left;">${wpFormatted}</td>`;
                    } else if (colKey === 'retailPrice') {
                        const rp = (row.rawItem && row.rawItem.retailPrice) ? Number(row.rawItem.retailPrice) : 0;
                        const rpFormatted = rp.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: pDec });
                        tdsHtml += `<td style="${cellPad} font-weight:700; color:#f472b6; direction:ltr; text-align:left;">${rpFormatted}</td>`;
                    } else if (colKey === 'totalValue') {
                        const curr = customReportConfig.showCurrencySymbol ? '<span style="font-size:0.75rem; color:#64748b; font-weight:600;">SDG</span>' : '';
                        const valFormatted = rowValue.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: pDec });
                        tdsHtml += `<td style="${cellPad}" class="val-td">${valFormatted} ${curr}</td>`;
                    } else if (colKey === 'status') {
                        tdsHtml += `<td style="${cellPad} text-align:center;"><span class="status-badge ${statusInfo.cssClass}">${statusInfo.icon} ${statusInfo.text}</span></td>`;
                    } else if (colKey === 'actions') {
                        tdsHtml += `<td style="${cellPad} text-align:center;"><a data-href="item-ledger.php?item=${row.itemId}" onclick="window.location.href='item-ledger.php?item=${row.itemId}'" class="btn-link"><span>📑</span> كشف حركة</a></td>`;
                    }
                }

                const tr = document.createElement('tr');
                tr.innerHTML = tdsHtml;
                tbody.appendChild(tr);
            });

            // Update Summary KPI Strip
            const kpiCount = document.getElementById('kpi-count');
            if (kpiCount) kpiCount.innerHTML = `${totalItems.toLocaleString()} <span>صنف</span>`;
            const kpiQty = document.getElementById('kpi-qty');
            if (kpiQty) kpiQty.innerHTML = `${totalQty.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: qDec })} <span>وحدة</span>`;
            const kpiValue = document.getElementById('kpi-value');
            if (kpiValue) kpiValue.innerHTML = `${totalValue.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: pDec })} <span>SDG</span>`;
            const kpiAlerts = document.getElementById('kpi-alerts');
            if (kpiAlerts) kpiAlerts.innerHTML = `${totalAlerts.toLocaleString()} <span>تنبيه</span>`;

            const matchesBadge = document.getElementById('filtered-matches-badge');
            if (matchesBadge) {
                matchesBadge.textContent = `المطابق: ${filteredData.length} من ${reportData.length} صنف`;
            }

            if (filteredData.length === 0) {
                const whName = fWh === 'ALL' ? 'جميع المخازن' : fWh;
                tbody.innerHTML = `<tr><td colspan="${visibleColCount || 9}" style="text-align:center; padding:60px 20px; color:var(--text-secondary);">
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

        // =========================================================================
        // Custom Report Studio Modal Logic & Actions
        // =========================================================================
        function toggleChipClass(input) {
            if (!input) return;
            const parent = input.closest('.studio-chip');
            if (parent) {
                if (input.checked) parent.classList.add('checked');
                else parent.classList.remove('checked');
            }
        }

        function openCustomReportStudio() {
            // Populate Warehouses
            const crWh = document.getElementById('cr-warehouse');
            const srcWh = document.getElementById('filter-warehouse');
            if (crWh && srcWh) {
                crWh.innerHTML = srcWh.innerHTML;
                crWh.value = srcWh.value;
            }

            // Populate Categories
            const crCat = document.getElementById('cr-category');
            const srcCat = document.getElementById('filter-category');
            if (crCat && srcCat) {
                crCat.innerHTML = srcCat.innerHTML;
                crCat.value = srcCat.value;
            }

            // Sync Status
            const crStatus = document.getElementById('cr-status');
            const srcStatus = document.getElementById('filter-status');
            if (crStatus && srcStatus) {
                crStatus.value = srcStatus.value;
            }

            // Sync Column Checkboxes
            for (const [colKey, isVisible] of Object.entries(customReportConfig.cols)) {
                const el = document.getElementById(`col-${colKey}`);
                if (el) {
                    el.checked = isVisible;
                    toggleChipClass(el);
                }
            }

            // Sync Unit Mode
            const unitRadio = document.querySelector(`input[name="cr-unit-mode"][value="${customReportConfig.unitMode}"]`);
            if (unitRadio) {
                unitRadio.checked = true;
                document.querySelectorAll('input[name="cr-unit-mode"]').forEach(r => toggleChipClass(r));
            }

            // Sync other options
            const showRels = document.getElementById('cr-show-unit-rels');
            if (showRels) { showRels.checked = customReportConfig.showUnitRelations; toggleChipClass(showRels); }

            const qDec = document.getElementById('cr-qty-decimals');
            if (qDec) qDec.value = customReportConfig.qtyDecimals;

            const pDec = document.getElementById('cr-price-decimals');
            if (pDec) pDec.value = customReportConfig.priceDecimals;

            const showCurr = document.getElementById('cr-show-currency');
            if (showCurr) { showCurr.checked = customReportConfig.showCurrencySymbol; toggleChipClass(showCurr); }

            const fontSize = document.getElementById('cr-font-size');
            if (fontSize) fontSize.value = customReportConfig.fontSize;

            const density = document.getElementById('cr-row-density');
            if (density) density.value = customReportConfig.rowDensity;

            const color = document.getElementById('cr-highlight-color');
            if (color) color.value = customReportConfig.highlightColor;

            const sortEl = document.getElementById('cr-sort');
            if (sortEl) sortEl.value = customReportConfig.sortBy || 'default';

            document.getElementById('custom-report-modal').style.display = 'flex';
        }

        function closeCustomReportStudio() {
            document.getElementById('custom-report-modal').style.display = 'none';
        }

        function setAllStudioCols(checked) {
            for (const colKey of Object.keys(COLUMN_DEFS)) {
                const el = document.getElementById(`col-${colKey}`);
                if (el) {
                    el.checked = checked;
                    toggleChipClass(el);
                }
            }
        }

        function setStudioPreset(preset) {
            const presets = {
                standard: { item: true, code: true, category: true, warehouse: true, actualQty: true, reservedQty: true, availQty: true, minQty: false, costPrice: true, cashPrice: false, wholesalePrice: false, retailPrice: false, totalValue: true, status: true, actions: true },
                financial: { item: true, code: true, category: true, warehouse: true, actualQty: false, reservedQty: false, availQty: true, minQty: false, costPrice: true, cashPrice: true, wholesalePrice: true, retailPrice: false, totalValue: true, status: false, actions: true },
                quantities: { item: true, code: true, category: false, warehouse: true, actualQty: true, reservedQty: true, availQty: true, minQty: true, costPrice: false, cashPrice: false, wholesalePrice: false, retailPrice: false, totalValue: false, status: true, actions: true }
            };

            const selectedPreset = presets[preset] || presets.standard;
            for (const [colKey, isVisible] of Object.entries(selectedPreset)) {
                const el = document.getElementById(`col-${colKey}`);
                if (el) {
                    el.checked = isVisible;
                    toggleChipClass(el);
                }
            }
        }

        function readStudioConfigFromModal() {
            // Read Columns
            for (const colKey of Object.keys(COLUMN_DEFS)) {
                const el = document.getElementById(`col-${colKey}`);
                if (el) {
                    customReportConfig.cols[colKey] = el.checked;
                }
            }

            // Read Unit Mode
            const activeUnitRadio = document.querySelector('input[name="cr-unit-mode"]:checked');
            if (activeUnitRadio) customReportConfig.unitMode = activeUnitRadio.value;

            // Read options
            const showRels = document.getElementById('cr-show-unit-rels');
            if (showRels) customReportConfig.showUnitRelations = showRels.checked;

            const qDec = document.getElementById('cr-qty-decimals');
            if (qDec) customReportConfig.qtyDecimals = parseInt(qDec.value, 10);

            const pDec = document.getElementById('cr-price-decimals');
            if (pDec) customReportConfig.priceDecimals = parseInt(pDec.value, 10);

            const showCurr = document.getElementById('cr-show-currency');
            if (showCurr) customReportConfig.showCurrencySymbol = showCurr.checked;

            const fontSize = document.getElementById('cr-font-size');
            if (fontSize) customReportConfig.fontSize = fontSize.value;

            const density = document.getElementById('cr-row-density');
            if (density) customReportConfig.rowDensity = density.value;

            const color = document.getElementById('cr-highlight-color');
            if (color) customReportConfig.highlightColor = color.value;

            const sortEl = document.getElementById('cr-sort');
            if (sortEl) customReportConfig.sortBy = sortEl.value;

            // Sync Scope Filters to main bar
            const crWh = document.getElementById('cr-warehouse');
            const srcWh = document.getElementById('filter-warehouse');
            if (crWh && srcWh && crWh.value !== srcWh.value) {
                srcWh.value = crWh.value;
            }

            const crCat = document.getElementById('cr-category');
            const srcCat = document.getElementById('filter-category');
            if (crCat && srcCat && crCat.value !== srcCat.value) {
                srcCat.value = crCat.value;
            }

            const crStatus = document.getElementById('cr-status');
            const srcStatus = document.getElementById('filter-status');
            if (crStatus && srcStatus && crStatus.value !== srcStatus.value) {
                srcStatus.value = crStatus.value;
            }
        }

        function applyStudioReport() {
            readStudioConfigFromModal();
            applyFilters();
            closeCustomReportStudio();
            if (typeof showToast === 'function') {
                showToast('تم تطبيق وتوليد التقرير المخصص بنجاح! 🚀', 'success');
            }
        }

        function saveStudioPreset() {
            readStudioConfigFromModal();
            localStorage.setItem('erp_custom_report_preset_v1', JSON.stringify(customReportConfig));
            if (typeof showToast === 'function') {
                showToast('تم حفظ قالب التقرير المخصص بنجاح كافتراضي! 💾', 'success');
            }
        }

        function resetStudioConfig() {
            customReportConfig = {
                cols: {
                    item: true, code: true, category: true, warehouse: true,
                    actualQty: true, reservedQty: true, availQty: true, minQty: false,
                    costPrice: true, cashPrice: false, wholesalePrice: false, retailPrice: false,
                    totalValue: true, status: true, actions: true
                },
                unitMode: 'compound',
                showUnitRelations: true,
                qtyDecimals: 2,
                priceDecimals: 2,
                showCurrencySymbol: true,
                fontSize: '14px',
                rowDensity: 'normal',
                highlightColor: '#38bdf8',
                sortBy: 'default'
            };
            localStorage.removeItem('erp_custom_report_preset_v1');
            openCustomReportStudio();
            applyFilters();
            if (typeof showToast === 'function') {
                showToast('تم استعادة الإعدادات الافتراضية للتقرير 🔄', 'info');
            }
        }

        // =========================================================================
        // Custom Report Printing & Excel Export
        // =========================================================================
        function printStudioReport() {
            readStudioConfigFromModal();
            applyFilters();

            const table = document.getElementById('inventory-main-table');
            if (!table) return;

            const printWindow = window.open('', '_blank');
            const now = new Date();
            const dateStr = now.toLocaleDateString('ar-EG', { year:'numeric', month:'long', day:'numeric' });
            const timeStr = now.toLocaleTimeString('ar-EG', { hour:'2-digit', minute:'2-digit' });
            const whVal = document.getElementById('filter-warehouse') ? document.getElementById('filter-warehouse').selectedOptions[0].text : 'جميع المخازن';
            const statusVal = document.getElementById('filter-status') ? document.getElementById('filter-status').selectedOptions[0].text : 'عام';

            const printHtml = `
            <!DOCTYPE html>
            <html dir="rtl" lang="ar">
            <head>
                <meta charset="UTF-8">
                <title>تقرير المخزون المخصص - ELSAJANA</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff; color: #000; padding: 20px; direction: rtl; }
                    .print-header { border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
                    .print-title { font-size: 1.4rem; font-weight: 900; margin: 0 0 5px; }
                    .print-meta { font-size: 0.85rem; color: #555; display: flex; gap: 15px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: ${customReportConfig.fontSize || '12px'}; }
                    th { background: #f1f5f9; color: #000; border: 1px solid #cbd5e1; padding: 8px 10px; font-weight: 800; }
                    td { border: 1px solid #e2e8f0; padding: 8px 10px; }
                    .val-td { direction: ltr; text-align: left; font-weight: 800; }
                    .code-pill { font-family: monospace; font-weight: 700; background: #eee; padding: 2px 5px; border-radius: 4px; }
                    .status-badge { font-weight: 700; }
                    .btn-link { display: none; }
                    @media print {
                        body { padding: 0; }
                        @page { margin: 1.5cm; size: landscape; }
                    }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <div>
                        <h1 class="print-title">🏢 شركة السجانة العالمية للتجارة والخدمات</h1>
                        <div style="font-size: 1.1rem; font-weight: 800; color: #1e293b;">تقرير جرد وتقييم المخزون المخصص</div>
                    </div>
                    <div style="text-align: left;">
                        <div class="print-meta"><span>📅 التاريخ: ${dateStr}</span> <span>⏰ الوقت: ${timeStr}</span></div>
                        <div style="font-size: 0.82rem; color: #475569; margin-top: 4px;">النطاق: <strong>${whVal}</strong> | الحالة: <strong>${statusVal}</strong></div>
                    </div>
                </div>
                ${table.outerHTML}
                <div style="margin-top: 20px; display: flex; justify-content: space-between; font-size: 0.85rem; border-top: 1px solid #ccc; padding-top: 10px;">
                    <div>توقيع أمين المخزن: ..............................</div>
                    <div>توقيع المدير المالي: ..............................</div>
                    <div>الختم الرسمي: ..............................</div>
                </div>
                <script>
                    window.onload = function() { window.print(); };
                <\/script>
            </body>
            </html>`;

            printWindow.document.write(printHtml);
            printWindow.document.close();
        }

        function exportStudioExcel() {
            readStudioConfigFromModal();
            applyFilters();

            const table = document.getElementById('inventory-main-table');
            if (!table) return;

            let html = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head><meta charset="utf-8" /><style>table, th, td { border: 1px solid #000; font-family: Segoe UI, Tahoma; text-align: right; } th { background-color: #f2f2f2; }</style></head>
            <body dir="rtl">
            <h2>تقرير جرد المخزون المخصص - شركة السجانة</h2>
            <p>تاريخ الاستخراج: ${new Date().toLocaleString('ar-EG')}</p>
            ${table.outerHTML}
            </body></html>`;

            const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `تقرير_المخزون_المخصص_${new Date().toISOString().slice(0,10)}.xls`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            if (typeof showToast === 'function') {
                showToast('تم تصدير ملف Excel بنجاح! 📥', 'success');
            }
        }

        function exportExcel() {
            exportStudioExcel();
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
            const wh = document.getElementById('adj-warehouse').value;
            const notes = document.getElementById('adj-notes').value.trim();

            const itemsToAdjust = [];
            let totalAdjVal = 0;

            stocktakeData.forEach(d => {
                const physQty = parseFloat(document.getElementById(`phys-${d.itemId}`).value) || 0;
                const diff = physQty - d.bookQty;
                if (diff !== 0) {
                    itemsToAdjust.push({
                        itemId: d.itemId,
                        unitId: 'base',
                        unitName: d.unit,
                        quantity: diff,
                        price: d.price / rate,
                        taxRate: 0,
                        total: (diff * d.price) / rate
                    });
                    totalAdjVal += diff * d.price;
                }
            });

            if (itemsToAdjust.length === 0) {
                showToast('لم يتم إجراء أي تعديل على الكميات لتسويتها!', 'warning');
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
                let entry = currentInvData.find(inv => inv.itemId === adj.itemId && inv.warehouse === wh);
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

            showToast('تم ترحيل التسوية وتوليد القيود بنجاح!', 'success');
            closeStocktakeModal();

            setTimeout(() => {
                window.location.reload();
            }, 1000);
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
                if (curr === 'USD') rateInput.value = '600.00';
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

        <!-- Custom Report Studio Modal (مصمم تقارير المخزون المخصص) -->
        <div id="custom-report-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; direction: rtl; backdrop-filter: blur(8px);">
            <div class="glass-panel" style="width: 95%; max-width: 1050px; max-height: 92vh; display: flex; flex-direction: column; padding: 25px; position: relative; border: 1px solid rgba(139, 92, 246, 0.4); background: #0f172a; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.7);">
                
                <!-- Modal Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid rgba(139, 92, 246, 0.2); padding-bottom: 12px; margin-bottom: 15px;">
                    <div>
                        <h3 style="color: #c084fc; margin: 0 0 4px 0; font-weight: 900; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.6rem;">🎛️</span> مصمم ومخصص تقارير المخزون الشامل (Report Studio)
                        </h3>
                        <p style="margin: 0; font-size: 0.82rem; color: #94a3b8;">
                            تحكم كامل في أعمدة التقرير، وحدات القياس، الأسعار، الفواصل العشرية، ونمط العرض والطباعة والتصدير
                        </p>
                    </div>
                    <button onclick="closeCustomReportStudio()" style="background: none; border: none; color: #94a3b8; font-size: 1.8rem; cursor: pointer; transition: 0.3s; line-height: 1;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">&times;</button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div style="flex: 1; overflow-y: auto; padding-right: 5px; display: flex; flex-direction: column; gap: 16px;">
                    
                    <!-- Section 1: نطاق التقرير والترتيب -->
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 14px;">
                        <div class="studio-section-title">
                            <span>🎯</span> 1. نطاق بيانات التقرير والترتيب (Scope & Sorting)
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <div>
                                <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 4px;">تحديد المخزن</label>
                                <select id="cr-warehouse" class="f-input f-select" style="height: 36px;">
                                    <option value="ALL">🏢 جميع المخازن (جرد عام)</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 4px;">تحديد الفئة</label>
                                <select id="cr-category" class="f-input f-select" style="height: 36px;">
                                    <option value="ALL">🏷️ جميع الفئات</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 4px;">تصفية حالة الرصيد</label>
                                <select id="cr-status" class="f-input f-select" style="height: 36px;">
                                    <option value="ACTIVE" selected>⚡ الأصناف المتوفرة فقط</option>
                                    <option value="ALL_WITH_ZERO">🌐 شامل الصفر (الكل)</option>
                                    <option value="OK">🟢 رصيد آمن فقط</option>
                                    <option value="WARNING">⚠️ تحت حد الطلب</option>
                                    <option value="DANGER">🚨 توفير النواقص (نافذ / صفر)</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 4px;">ترتيب الأصناف بالتقرير</label>
                                <select id="cr-sort" class="f-input f-select" style="height: 36px;">
                                    <option value="default">الترتيب الافتراضي</option>
                                    <option value="value_desc">الأعلى قيمة مالية أولاً 💰</option>
                                    <option value="qty_desc">الأكثر كمية متوفرة 📦</option>
                                    <option value="qty_asc">الأقل كمية (الأقرب للنفاذ) ⚠️</option>
                                    <option value="name_asc">أبجدياً حسب اسم الصنف (أ-ي)</option>
                                    <option value="code_asc">حسب كود الصنف</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: إظهار وإخفاء الأعمدة -->
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div class="studio-section-title" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
                                <span>👁️</span> 2. اختيار وإظهار/إخفاء الأعمدة (Columns Visibility)
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <button type="button" onclick="setAllStudioCols(true)" style="background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border); border-radius: 6px; padding: 3px 8px; font-size: 0.72rem; color: #38bdf8; cursor: pointer;">تحديد الكل</button>
                                <button type="button" onclick="setStudioPreset('standard')" style="background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border); border-radius: 6px; padding: 3px 8px; font-size: 0.72rem; color: #cbd5e1; cursor: pointer;">الافتراضي</button>
                                <button type="button" onclick="setStudioPreset('financial')" style="background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border); border-radius: 6px; padding: 3px 8px; font-size: 0.72rem; color: #10b981; cursor: pointer;">موجز مالي</button>
                                <button type="button" onclick="setStudioPreset('quantities')" style="background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border); border-radius: 6px; padding: 3px 8px; font-size: 0.72rem; color: #fbbf24; cursor: pointer;">جرد كميات</button>
                            </div>
                        </div>
                        <div class="studio-card-grid" id="studio-cols-grid">
                            <label class="studio-chip checked"><input type="checkbox" id="col-item" onchange="toggleChipClass(this)" checked> <span>📦 اسم الصنف</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-code" onchange="toggleChipClass(this)" checked> <span>📋 كود الصنف</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-category" onchange="toggleChipClass(this)" checked> <span>🏷️ فئة الصنف</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-warehouse" onchange="toggleChipClass(this)" checked> <span>🏢 المخزن</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-actualQty" onchange="toggleChipClass(this)" checked> <span>📊 الرصيد الفعلي</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-reservedQty" onchange="toggleChipClass(this)" checked> <span>🔒 الرصيد المحجوز</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-availQty" onchange="toggleChipClass(this)" checked> <span>🟢 الرصيد المتاح للبيع</span></label>
                            <label class="studio-chip"><input type="checkbox" id="col-minQty" onchange="toggleChipClass(this)"> <span>⚠️ حد الطلب الأدنى</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-costPrice" onchange="toggleChipClass(this)" checked> <span>💵 سعر التكلفة/المتوسط</span></label>
                            <label class="studio-chip"><input type="checkbox" id="col-cashPrice" onchange="toggleChipClass(this)"> <span>🏷️ سعر البيع النقدي</span></label>
                            <label class="studio-chip"><input type="checkbox" id="col-wholesalePrice" onchange="toggleChipClass(this)"> <span>💼 سعر البيع جملة</span></label>
                            <label class="studio-chip"><input type="checkbox" id="col-retailPrice" onchange="toggleChipClass(this)"> <span>🛍️ سعر البيع قطاعي</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-totalValue" onchange="toggleChipClass(this)" checked> <span>💰 إجمالي القيمة</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-status" onchange="toggleChipClass(this)" checked> <span>🚦 حالة المخزون</span></label>
                            <label class="studio-chip checked"><input type="checkbox" id="col-actions" onchange="toggleChipClass(this)" checked> <span>📑 زر كشف الحركة</span></label>
                        </div>
                    </div>

                    <!-- Section 3: التحكم في الوحدات + الفواصل العشرية + المظهر -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                        
                        <!-- Units Control -->
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 14px;">
                            <div class="studio-section-title">
                                <span>⚖️</span> 3. طريقة عرض الوحدات
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <label class="studio-chip checked" style="padding: 6px 10px;">
                                    <input type="radio" name="cr-unit-mode" value="compound" checked>
                                    <span>🌟 عرض مركب ذكي (طن+سيخ / كرتونة+حبة)</span>
                                </label>
                                <label class="studio-chip" style="padding: 6px 10px;">
                                    <input type="radio" name="cr-unit-mode" value="base">
                                    <span>📦 الوحدة الأساسية الكبرى فقط</span>
                                </label>
                                <label class="studio-chip" style="padding: 6px 10px;">
                                    <input type="radio" name="cr-unit-mode" value="smallest">
                                    <span>🔹 الوحدة الصغرى (التجزئة) فقط</span>
                                </label>
                                <label class="studio-chip checked" style="padding: 6px 10px; margin-top: 4px;">
                                    <input type="checkbox" id="cr-show-unit-rels" onchange="toggleChipClass(this)" checked>
                                    <span style="font-size: 0.78rem;">إظهار معادلات التحويل أسفل الصنف</span>
                                </label>
                            </div>
                        </div>

                        <!-- Decimals & Precision -->
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 14px;">
                            <div class="studio-section-title">
                                <span>🔢</span> 4. الفاصلة العشرية والعملة
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div>
                                    <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 3px;">خانات الكميات العشرية</label>
                                    <select id="cr-qty-decimals" class="f-input f-select" style="height: 34px;">
                                        <option value="0">0 (أرقام صحيحة بدون كسور)</option>
                                        <option value="1">1 (مثال: 12.5)</option>
                                        <option value="2" selected>2 (مثال: 12.50)</option>
                                        <option value="3">3 (للأوزان والأطنان: 12.550)</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 3px;">خانات الأسعار والمبالغ</label>
                                    <select id="cr-price-decimals" class="f-input f-select" style="height: 34px;">
                                        <option value="0">0 (بدون كسور: 50,000)</option>
                                        <option value="2" selected>2 (خانتي سنت: 50,000.00)</option>
                                        <option value="3">3 (ثلاث خانات)</option>
                                    </select>
                                </div>
                                <label class="studio-chip checked" style="padding: 6px 10px;">
                                    <input type="checkbox" id="cr-show-currency" onchange="toggleChipClass(this)" checked>
                                    <span style="font-size: 0.78rem;">إظهار رمز العملة (SDG)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Appearance & Sizing -->
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 14px;">
                            <div class="studio-section-title">
                                <span>🎨</span> 5. مقاسات الخطوط والمظهر
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div>
                                    <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 3px;">مقاس خط الجدول</label>
                                    <select id="cr-font-size" class="f-input f-select" style="height: 34px;">
                                        <option value="12px">صغير مدمج (12px)</option>
                                        <option value="14px" selected>قياسي متناسق (14px)</option>
                                        <option value="16px">كبير مريح (16px)</option>
                                        <option value="18px">بارز عريض (18px)</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 3px;">كثافة تباعد الأسطر</label>
                                    <select id="cr-row-density" class="f-input f-select" style="height: 34px;">
                                        <option value="compact">مضغوط Compact (أكبر عدد بيانات)</option>
                                        <option value="normal" selected>قياسي مريح Normal</option>
                                        <option value="spacious">واسع Spacious</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 3px;">لون تمييز الرصيد المتاح</label>
                                    <select id="cr-highlight-color" class="f-input f-select" style="height: 34px;">
                                        <option value="#38bdf8" selected>🔵 أزرق سماوي نيون (Sky Blue)</option>
                                        <option value="#10b981">🟢 أخضر زمردي (Emerald Green)</option>
                                        <option value="#8b5cf6">🟣 بنفسجي ملكي (Purple)</option>
                                        <option value="#fbbf24">🟡 ذهبي كلاسيكي (Gold)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Modal Footer Actions -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(139, 92, 246, 0.2); padding-top: 15px; margin-top: 15px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="resetStudioConfig()" class="btn-action" style="background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border); color: #94a3b8; padding: 8px 16px;">🔄 استعادة الافتراضي</button>
                        <button type="button" onclick="saveStudioPreset()" class="btn-action" style="background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.4); color: #c084fc; padding: 8px 16px;">💾 حفظ كقالب دائم</button>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportStudioExcel()" class="btn-action" style="background: linear-gradient(135deg, #059669, #047857); color: #fff; border: none; padding: 9px 20px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);">
                            <span>📥</span> تصدير Excel مخصص
                        </button>
                        <button type="button" onclick="printStudioReport()" class="btn-action" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; border: none; padding: 9px 20px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);">
                            <span>🖨️</span> طباعة التقرير المخصص (A4)
                        </button>
                        <button type="button" onclick="applyStudioReport()" class="btn-action" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border: none; padding: 9px 24px; font-weight: 800; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);">
                            <span>⚡</span> تطبيق التقرير على الشاشة الآن
                        </button>
                    </div>
                </div>

            </div>
        </div>

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
</body>
</html>

