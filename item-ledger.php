<?php $page_title = 'مقاييس (MAGAIEES) - كارت الصنف (حركة الأصناف)'; ?>
<!-- c:\mynewapp\frontend-pwa\item-ledger.html -->
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
    <title>مقاييس (MAGAIEES) - كارت الصنف (حركة الأصناف)</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
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

        :root.light-theme {
            --bg-dark: #f1f5f9;
            --panel-bg: rgba(255, 255, 255, 0.95);
            --text-primary: #000000;
            --text-secondary: #1e293b;
            --glass-border: rgba(0, 0, 0, 0.08);
        }

        html { scroll-behavior: smooth; }
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

        /* Navigation Bar (Ultra-Compact Segmented Clusters) */
        .nav-tabs { 
            display: flex; gap: 6px; flex-wrap: wrap; overflow: visible;
            background: var(--surface-card, rgba(15, 23, 42, 0.7)); padding: 6px 10px; border-radius: 14px; 
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

        /* Nav Dropdown for Extra Reports */
        .nav-dropdown { position: relative; display: inline-block; }
        .nav-dropdown-content {
            display: none; position: absolute; top: calc(100% + 6px); right: 0; min-width: 240px;
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
        .nav-dropdown:focus-within .nav-dropdown-content {
            display: flex; flex-direction: column; gap: 4px;
        }
        .nav-dropdown-item {
            padding: 9px 12px; border-radius: 8px; text-decoration: none;
            color: #cbd5e1; font-size: 0.82rem; font-weight: 700;
            display: flex; align-items: center; gap: 8px; transition: all 0.2s ease;
        }
        .nav-dropdown-item:hover {
            background: rgba(56, 189, 248, 0.12); color: #38bdf8;
            transform: translateX(-3px);
        }
        .nav-dropdown-item.active {
            background: rgba(56, 189, 248, 0.18); color: #38bdf8;
        }

        /* Light Mode Styling for clusters and dropdowns */
        :root.light-theme .nav-tabs { background: rgba(248, 250, 252, 0.95); border: 1px solid rgba(0,0,0,0.08); }
        :root.light-theme .nav-tab { color: #475569; }
        :root.light-theme .nav-tab:hover { color: #0f172a; background: rgba(0,0,0,0.04); }
        :root.light-theme .nav-divider { background: rgba(0, 0, 0, 0.1); }
        :root.light-theme .nav-dropdown-content { background: #ffffff; border: 1px solid #cbd5e1; box-shadow: 0 10px 30px rgba(0,0,0,0.12); }
        :root.light-theme .nav-dropdown-item { color: #334155; }
        :root.light-theme .nav-dropdown-item:hover { background: #f1f5f9; color: #0284c7; }

        /* Controls / Buttons */
        .dash-controls { display: flex; align-items: center; gap: 8px; }
        .btn-action {
            padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; border: none; transition: 0.3s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-print { background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--glass-border); }
        .btn-print:hover { background: rgba(255,255,255,0.15); }
        .btn-icon-nav { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-primary); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
        .btn-icon-nav:hover { background: rgba(255,255,255,0.15); }

        /* Filters Bar style matching inventory-report */
        .filters-bar {
            background: var(--panel-bg); backdrop-filter: blur(15px); border-radius: 16px; padding: 20px;
            border: 1px solid var(--glass-border); display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group select, .input-group input {
            background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
            color: var(--text-primary); padding: 12px 15px; font-size: 0.9rem; outline: none; transition: 0.3s; font-family: 'Inter', 'Tajawal', sans-serif;
        }
        .input-group select:focus, .input-group input:focus { border-color: var(--pink-accent); box-shadow: 0 0 0 3px rgba(236,72,153,0.15); background: rgba(0,0,0,0.5); }

        /* KPI Grid & KPI Cards style matching inventory-report */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
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

        /* Data Table style matching inventory-report */
        .table-panel {
            background: var(--panel-bg); backdrop-filter: blur(15px); border-radius: 16px;
            border: 1px solid var(--glass-border); box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            overflow: hidden; display: flex; flex-direction: column;
        }
        .table-wrapper { overflow-x: auto; overflow-y: auto; max-height: 60vh; }
        .dash-table { width: 100%; border-collapse: collapse; min-width: 100%; }
        .dash-table thead { position: sticky; top: 0; z-index: 10; }
        .dash-table th {
            background: rgba(10, 15, 25, 0.95); padding: 15px; text-align: right;
            font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); border-bottom: 2px solid var(--glass-border);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .dash-table td { padding: 15px; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: middle; transition: background 0.2s; color: #f8fafc; }
        .dash-table tbody tr { animation: fadeSlideIn 0.4s ease-out forwards; opacity: 0; transform: translateY(10px); }
        .dash-table tbody tr:hover td { background: rgba(255,255,255,0.03); }

        @keyframes fadeSlideIn { to { opacity: 1; transform: translateY(0); } }

        .type-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: bold; }
        .type-in { background: rgba(16, 185, 129, 0.15); color: var(--emerald-accent); }
        .type-out { background: rgba(236, 72, 153, 0.15); color: var(--pink-accent); }
        .type-bal { background: rgba(56, 189, 248, 0.15); color: #38bdf8; }

        /* Universal Light Theme Overrides */
        :root.light-theme body { background-color: var(--bg-dark); color: var(--text-primary); }
        :root.light-theme .glass-panel,
        :root.light-theme .premium-card,
        :root.light-theme .filters-bar,
        :root.light-theme .kpi-card,
        :root.light-theme .table-panel {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            color: #000000 !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
        }
        :root.light-theme td, :root.light-theme th, :root.light-theme tr {
            color: #000000 !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .dash-table th {
            background: rgba(0, 0, 0, 0.04) !important;
            color: #000000 !important;
        }
        :root.light-theme .dash-table tbody tr {
            background: rgba(255, 255, 255, 0.95) !important;
        }
        :root.light-theme .dash-table tbody tr:hover td {
            background: rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme input, :root.light-theme select {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid rgba(0, 0, 0, 0.18) !important;
        }
        :root.light-theme .kpi-card.c-pink .kpi-val { color: #be185d !important; }
        :root.light-theme .kpi-card.c-emerald .kpi-val { color: #047857 !important; }
        :root.light-theme .kpi-card.c-blue .kpi-val { color: #1d4ed8 !important; }
        :root.light-theme .kpi-card.c-amber .kpi-val { color: #b45309 !important; }

        @media print {
            body { background: #fff !important; color: #000 !important; }
            .table-panel, .dash-table th, .dash-table td { background: #fff !important; color: #000 !important; border-color: #ccc !important; }
            .global-sidebar, button, .filters-bar { display: none !important; }
        }
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
        <!-- Header -->
        <?php $header_theme_class = 'header-theme-inventory'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Top Navigation Row (Clustered Smart Hub + Preview Action Buttons) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 10px; flex-wrap: nowrap;">
            <div class="nav-tabs" style="margin-bottom: 0; flex: 1;">
                
                <!-- Cluster 1: نظام الجرد -->
                <div class="nav-cluster">
                    <a href="inventory-report.php" class="nav-tab" title="نظام الجرد الشامل بجميع المخازن">
                        <span>📦</span> <span>الجرد الشامل للمخازن</span>
                    </a>
                </div>

                <div class="nav-divider"></div>

                <!-- Cluster 2: حركات البضائع -->
                <div class="nav-cluster nav-dropdown">
                    <div class="nav-tab active" title="حركات البضائع">
                        <span>📑</span> <span>حركات البضائع</span>
                        <span style="font-size: 0.7rem; margin-right: 2px;">▼</span>
                    </div>
                    <div class="nav-dropdown-content">
                        <a href="item-ledger.php" class="nav-dropdown-item active" title="كشف حركة صنف محدد">
                            <span>📑</span> حركة الأصناف (الحالية)
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

            <!-- Far Left Controls: Preview & Action Buttons -->
            <div class="dash-controls" style="margin: 0; display: flex; gap: 8px; flex-shrink: 0;">
                <button class="btn-action" onclick="openItemLedgerPreview()" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: white; border: none; padding: 7px 15px; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(13,148,136,0.25); display: flex; align-items: center; gap: 6px;">
                    <span>👁️</span> <span>معاينة وتصميم التقرير</span>
                </button>
                <button class="btn-action btn-print" onclick="printItemLedgerDirectly()" style="background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--glass-border); padding: 7px 14px; border-radius: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                    <span>🖨️</span> <span>طباعة</span>
                </button>
                <button class="btn-icon-nav" onclick="history.back()" title="رجوع" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; color: var(--text-primary); cursor: pointer; transition: 0.3s;">↩</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 14px; margin-bottom: 8px; flex-wrap: wrap; background: linear-gradient(135deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.9) 100%); backdrop-filter: blur(16px); border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            
            <!-- 1. الصنف (تم إنقاص مساحة القائمة بنسبة 30% وزيادة مساحة حقل البحث) -->
            <div style="display: flex; align-items: center; gap: 6px; flex: 2.0; min-width: 270px;">
                <label style="color: #38bdf8; font-size: 0.85rem; font-weight: 800; white-space: nowrap; margin: 0;">
                    الصنف:
                </label>
                <div style="display: flex; gap: 6px; flex: 1; min-width: 0;">
                    <select id="item-select" onchange="onItemSelected()" style="flex: 1; min-width: 0; font-weight: 800; color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); padding: 7px 10px; font-size: 0.88rem; border-radius: 8px; background: rgba(15, 23, 42, 0.7); box-shadow: inset 0 1px 3px rgba(0,0,0,0.3); outline: none; cursor: pointer;">
                        <option value="">-- يرجى اختيار صنف --</option>
                    </select>
                    <div style="position: relative; width: 140px; flex-shrink: 0;">
                        <input type="text" id="item-quick-filter" placeholder="🔍 بحث عن صنف..." oninput="filterItemDropdown(this.value)" style="width: 100%; padding: 7px 10px; font-size: 0.82rem; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3); background: rgba(15, 23, 42, 0.85); color: #fff; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);" title="اكتب لفلترة قائمة الأصناف سريعاً">
                    </div>
                </div>
            </div>
            
            <!-- 2. الوحدات (مساحة متسعة ومريحة) -->
            <div style="display: flex; align-items: center; gap: 6px; flex: 2.4; min-width: 190px; max-width: 280px;">
                <label style="color: #10b981; font-size: 0.85rem; font-weight: 800; white-space: nowrap; margin: 0;">
                    الوحدات:
                </label>
                <select id="display-unit-select" onchange="loadLedger()" style="width: 100%; min-width: 0; padding: 7px 10px; font-size: 0.88rem; font-weight: 800; color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); background: rgba(16, 185, 129, 0.08); border-radius: 8px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.3); outline: none; cursor: pointer;">
                    <option value="">-- الأساسية --</option>
                </select>
            </div>

            <!-- 3. المخزن -->
            <div style="display: flex; align-items: center; gap: 6px; flex: 1.6; min-width: 150px;">
                <label style="color: #cbd5e1; font-size: 0.85rem; font-weight: 800; white-space: nowrap; margin: 0;">
                    المخزن:
                </label>
                <select id="warehouse-select" onchange="loadLedger()" style="width: 100%; min-width: 0; padding: 7px 10px; font-size: 0.85rem; font-weight: 600; border-radius: 8px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.12); color: #e2e8f0; outline: none; cursor: pointer;">
                    <option value="ALL">جميع المخازن</option>
                </select>
            </div>

            <!-- 4. التاريخ -->
            <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                <label style="color: #cbd5e1; font-size: 0.85rem; font-weight: 800; white-space: nowrap; margin: 0; margin-left: 2px;">
                    التاريخ:
                </label>
                <input type="date" id="date-from" onchange="loadLedger()" style="width: 116px; padding: 6px 4px; font-size: 0.78rem; border-radius: 8px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.12); color: #fff; outline: none; text-align: center;" title="من تاريخ">
                <span style="font-size: 0.76rem; color: #94a3b8; font-weight: 800; padding: 0 1px;">إلى</span>
                <input type="date" id="date-to" onchange="loadLedger()" style="width: 116px; padding: 6px 4px; font-size: 0.78rem; border-radius: 8px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.12); color: #fff; outline: none; text-align: center;" title="إلى تاريخ">
            </div>

        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card c-blue">
                <div class="kpi-title">الرصيد الافتتاحي (أول المدة) <span>📋</span></div>
                <div class="kpi-val" id="sum-open">0</div>
            </div>
            <div class="kpi-card c-emerald">
                <div class="kpi-title">إجمالي الوارد (مشتريات) <span>⚖️</span></div>
                <div class="kpi-val" id="sum-in">0</div>
            </div>
            <div class="kpi-card c-pink">
                <div class="kpi-title">إجمالي المنصرف (مبيعات) <span>💰</span></div>
                <div class="kpi-val" id="sum-out">0</div>
            </div>
            <div class="kpi-card c-amber">
                <div class="kpi-title">الرصيد الفعلي الحالي <span>⚠️</span></div>
                <div class="kpi-val" id="sum-balance">0</div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="table-panel">
            <div class="table-wrapper" id="report-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>نوع الحركة</th>
                            <th style="color:#38bdf8;">رقم الإذن</th>
                            <th>رقم الاستلام</th>
                        <th style="color:#8b5cf6; background:rgba(139,92,246,0.05); text-align:right;">وحدة وكمية الوارد والصادر</th>
                        <th style="color:#10b981; background:rgba(16,185,129,0.05); text-align: right;">الوارد (داخل)</th>
                            <th style="color:#ec4899; background:rgba(236,72,153,0.05); text-align: right;">المنصرف (خارج)</th>
                            <th style="color:#fbbf24; background:rgba(251,191,36,0.05); text-align: right;">الرصيد التراكمي</th>
                            <th style="color:#38bdf8;">ملحوظة الحركة</th>
                        </tr>
                    </thead>
                    <tbody id="ledger-tbody">
                        <tr><td colspan="8" style="text-align:center; padding:30px; color:var(--text-secondary);">يرجى اختيار صنف لعرض حركته التفصيلية</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- 🎨 ITEM LEDGER PREVIEW & DESIGN STUDIO MODAL                -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div id="item-ledger-preview-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 15, 30, 0.92); z-index: 12000; align-items: center; justify-content: center; direction: rtl; backdrop-filter: blur(12px);">
        <div class="glass-panel" style="width: 96%; max-width: 1420px; height: 94vh; display: flex; flex-direction: column; padding: 18px 22px; border: 1px solid rgba(255,255,255,0.12); background: #0b1120; border-radius: 18px; color: #fff; box-shadow: 0 25px 60px rgba(0,0,0,0.6); overflow: hidden;">
            
            <!-- Top Toolbar Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 12px;" class="no-print">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">👁️</span>
                    <div>
                        <h3 style="margin: 0; font-weight: 900; color: #38bdf8; font-size: 1.15rem;">معاينة وتخصيص تصميم كارت حركة الصنف</h3>
                        <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: #94a3b8;">استوديو التصميم: يمكنك تعديل الألوان، الخطوط، الأعمدة، والتعديل المباشر على النصوص</p>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 6px;">
                    <!-- Studio Design Toggle -->
                    <button type="button" id="btn-toggle-design-studio" onclick="toggleDesignStudioDrawer()" class="btn-action" style="background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.4); color: #38bdf8; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; height: 28px;">
                        <span>🎨</span> الضبط
                    </button>
                    <!-- Live Edit Mode Toggle -->
                    <button type="button" id="btn-toggle-live-edit" onclick="toggleLiveEditMode()" class="btn-action" style="background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4); color: #fbbf24; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; height: 28px;">
                        <span>✏️</span> تعديل النصوص
                    </button>
                    <!-- Print Button -->
                    <button type="button" onclick="printItemLedgerSheet()" class="btn-action" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 4px 14px; font-size: 0.8rem; font-weight: 800; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(16,185,129,0.3); height: 28px;">
                        <span>🖨️</span> طباعة
                    </button>
                    <!-- Close Button -->
                    <button type="button" onclick="closeItemLedgerPreview()" class="btn-action" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #e2e8f0; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; cursor: pointer; height: 28px;">
                        ✕ إغلاق
                    </button>
                </div>
            </div>

            <!-- Design Studio Toolbar (Collapsible / Active) -->
            <div id="item-ledger-studio-drawer" class="no-print" style="display: flex; flex-direction: column; gap: 6px; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 8px; padding: 6px 10px; margin-bottom: 6px; box-shadow: inset 0 2px 8px rgba(0,0,0,0.3);">
                
                <!-- Row 1: Global Theme, Font, Size, Paper & Sections -->
                <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 700;">🎨 الثيم:</span>
                        <select id="studio-theme-select" onchange="applyStudioDesign()" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 5px; padding: 2px 6px; font-size: 0.8rem; height: 26px;">
                            <option value="corporate">🔷 أزرق نيلي (Corporate)</option>
                            <option value="emerald">🟢 أخضر زمردي (Emerald)</option>
                            <option value="gold">🟡 ذهبي كلاسيكي (Royal Gold)</option>
                            <option value="cyan">🔵 سيان عصري (Modern Cyan)</option>
                            <option value="purple">🟣 بنفسجي تقني (Purple Tech)</option>
                            <option value="classic">⚫ رمادي أحادي رسمي (Monochrome)</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 700;">🔤 الخط:</span>
                        <select id="studio-font-select" onchange="applyStudioDesign()" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 5px; padding: 2px 6px; font-size: 0.8rem; height: 26px;">
                            <option value="'Tajawal', sans-serif">تجوّل (Tajawal)</option>
                            <option value="'Cairo', sans-serif">القاهرة (Cairo)</option>
                            <option value="'Almarai', sans-serif">المراعي (Almarai)</option>
                            <option value="'Inter', sans-serif">إنتـر المريح (Inter)</option>
                            <option value="'Traditional Arabic', serif">عربي تقليدي (Traditional)</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 700;">📏 الكثافة:</span>
                        <select id="studio-size-select" onchange="applyStudioDesign()" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 5px; padding: 2px 6px; font-size: 0.8rem; height: 26px;">
                            <option value="compact">مدمج صغير (Compact)</option>
                            <option value="medium" selected>متوازن قياسي (Medium)</option>
                            <option value="large">كبير واضح (Large)</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 700;">📑 الاتجاه:</span>
                        <select id="studio-orientation-select" onchange="applyStudioDesign()" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 5px; padding: 2px 6px; font-size: 0.8rem; height: 26px;">
                            <option value="landscape" selected>↔️ أفقي (Landscape)</option>
                            <option value="portrait">↕️ طولي (Portrait)</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 6px;">
                        <label style="font-size: 0.8rem; color: #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 2px;">
                            <input type="checkbox" id="chk-show-header" checked onchange="applyStudioDesign()"> الترويسة
                        </label>
                        <label style="font-size: 0.8rem; color: #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 2px;">
                            <input type="checkbox" id="chk-show-meta" checked onchange="applyStudioDesign()"> بيانات الصنف
                        </label>
                        <label style="font-size: 0.8rem; color: #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 2px;">
                            <input type="checkbox" id="chk-show-kpis" checked onchange="applyStudioDesign()"> ملخص الأرصدة
                        </label>
                        <label style="font-size: 0.8rem; color: #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 2px;">
                            <input type="checkbox" id="chk-show-signatures" checked onchange="applyStudioDesign()"> التوقيعات
                        </label>
                    </div>

                    <div style="display: flex; align-items: center; gap: 4px;">
                        <button type="button" onclick="saveStudioDesignPreset()" style="background: rgba(16,185,129,0.2); border: 1px solid rgba(16,185,129,0.4); color: #34d399; padding: 2px 8px; border-radius: 5px; font-size: 0.78rem; font-weight: 700; cursor: pointer; height: 26px;">
                            💾 حفظ
                        </button>
                        <button type="button" onclick="resetStudioDesignPreset()" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: #cbd5e1; padding: 2px 8px; border-radius: 5px; font-size: 0.78rem; cursor: pointer; height: 26px;">
                            🔄 استعادة
                        </button>
                    </div>
                </div>

                <!-- Row 2: Unified Single-Row Column Super-Toolbar -->
                <div class="panel-card studio-super-row" style="margin-bottom: 0; padding: 4px 8px; gap: 6px; display: flex; align-items: center; justify-content: flex-start; flex-wrap: nowrap; overflow-x: auto; background: rgba(255, 255, 255, 0.08) !important; border: 1px solid rgba(56, 189, 248, 0.3) !important;">
                    
                    <!-- Group A: Column Selection & Visibility -->
                    <div class="studio-group-pill">
                        <span style="color:#38bdf8; font-size:0.8rem; font-weight:900;">🎯 العمود:</span>
                        <select id="item-selected-col-select" class="panel-input" style="color:#38bdf8; font-size:0.8rem; font-weight:900; border-color:#38bdf8; background:#1e293b; padding: 2px 6px; height: 26px;" onchange="selectItemLedgerCol(this.value)">
                            <option value="date" selected>التاريخ</option>
                            <option value="type">نوع الحركة</option>
                            <option value="permit">رقم الإذن</option>
                            <option value="receipt">رقم الاستلام</option>
                            <option value="in">الوارد</option>
                            <option value="out">المنصرف</option>
                            <option value="balance">الرصيد التراكمي</option>
                            <option value="notes">ملحوظة الحركة</option>
                        </select>
                        <label style="display:flex; align-items:center; gap:2px; cursor:pointer; font-size:0.8rem; font-weight:800;" title="إظهار أو إخفاء العمود">
                            <input type="checkbox" id="item-col-visible-chk" checked onchange="updateSelectedItemLedgerCol('visible', this.checked)">
                            إظهار
                        </label>
                    </div>

                    <div class="studio-pill-divider"></div>

                    <!-- Group B: Header Label & Ordering -->
                    <div class="studio-group-pill">
                        <span style="font-size:0.8rem;">✏️ التسمية:</span>
                        <input type="text" id="item-col-label-input" class="panel-input" style="width:105px; font-size:0.8rem; padding: 2px 6px; background:#1e293b; color:#fff; border-color:#475569; height: 26px;" oninput="updateSelectedItemLedgerCol('label', this.value)">
                    </div>

                    <div class="studio-pill-divider"></div>

                    <!-- Group C: Alignment -->
                    <div class="studio-group-pill">
                        <span style="font-size:0.8rem;">المحاذاة:</span>
                        <select id="item-col-align-select" class="panel-input" style="font-size:0.8rem; padding: 2px 6px; background:#1e293b; color:#fff; border-color:#475569; height: 26px;" onchange="updateSelectedItemLedgerCol('align', this.value)">
                            <option value="right">يمين ➡️</option>
                            <option value="center" selected>وسط 🟢</option>
                            <option value="left">يسار ⬅️</option>
                        </select>
                    </div>

                    <div class="studio-pill-divider"></div>

                    <!-- Group D: Column Width -->
                    <div class="studio-group-pill">
                        <span style="font-size:0.8rem;" title="عرض العمود كنسبة مئوية">📏 العرض:</span>
                        <input type="range" id="item-col-width-range" min="5" max="50" step="1" value="15" style="width:65px;"
                            oninput="document.getElementById('item-col-width-input').value=this.value; updateSelectedItemLedgerCol('width', this.value)">
                        <input type="number" id="item-col-width-input" class="panel-input" style="width:42px; font-size:0.8rem; padding: 2px; background:#1e293b; color:#fff; border-color:#475569; height: 26px;" min="5" max="50" placeholder="%" 
                            oninput="if(document.getElementById('item-col-width-range')) document.getElementById('item-col-width-range').value=this.value; updateSelectedInvCol('width', this.value)">
                        <button class="ribbon-btn studio-mini-btn" style="font-size:0.75rem; padding: 2px 5px;" 
                            onclick="updateSelectedItemLedgerCol('width', ''); document.getElementById('item-col-width-input').value=''; document.getElementById('item-col-width-range').value=15;" title="ضبط العرض التلقائي">🔄 تلقائي</button>
                    </div>

                    <div class="studio-pill-divider"></div>

                    <!-- Group E: Table Width -->
                    <div class="studio-group-pill">
                        <span style="font-size:0.8rem;">📐 عرض الجدول:</span>
                        <input type="range" id="item-table-width-range" min="60" max="100" step="5" value="100" style="width:65px;"
                            oninput="setItemTableWidth(this.value)">
                        <span id="item-table-width-label" style="font-weight:900; font-size:0.8rem; min-width:32px; text-align:center; color:#38bdf8;">100%</span>
                    </div>

                </div>
            </div>

            <!-- Scrollable Sheet Container -->
            <div style="flex: 1; overflow-y: auto; background: #0f172a; padding: 25px 15px; display: flex; justify-content: center; border-radius: 12px; box-shadow: inset 0 2px 15px rgba(0,0,0,0.5);" class="preview-scroll-container">
                <div id="item-ledger-sheet" style="width: 100%; max-width: 1180px; background: #ffffff; color: #0f172a; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); border-radius: 6px; min-height: fit-content; box-sizing: border-box; overflow-x: auto; transition: all 0.3s ease;">
                    <!-- Dynamically rendered sheet content -->
                </div>
            </div>

        </div>
    </div>

    <!-- Print CSS Injection -->
    <style id="item-ledger-print-style">
        #item-ledger-preview-modal.print-direct-mode { display: none !important; }
        @page { size: landscape; margin: 8mm; }
        @media print {
            #item-ledger-preview-modal.print-direct-mode { display: block !important; }
            body > *:not(#item-ledger-preview-modal) { display: none !important; }
            #item-ledger-preview-modal { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; height: auto !important; background: #fff !important; padding: 0 !important; margin: 0 !important; display: block !important; overflow: visible !important; }
            #item-ledger-preview-modal .glass-panel { width: 100% !important; max-width: 100% !important; height: auto !important; border: none !important; background: #fff !important; padding: 0 !important; box-shadow: none !important; margin: 0 !important; }
            .global-sidebar, .no-print, .btn-action, .dash-controls { display: none !important; }
            #item-ledger-preview-modal .preview-scroll-container { background: #fff !important; padding: 0 !important; overflow: visible !important; }
            #item-ledger-sheet { box-shadow: none !important; padding: 0 !important; margin: 0 !important; border: none !important; width: 100% !important; max-width: 100% !important; }
            #item-ledger-sheet table { width: 100% !important; border-collapse: collapse !important; table-layout: auto !important; }
            #item-ledger-sheet th, #item-ledger-sheet td { border: 1px solid #94a3b8 !important; page-break-inside: avoid; }
        }
    </style>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        const ITEMS_STORAGE_KEY = 'erp_items_v1';
        let itemsDb = [];
        let warehousesDb = [];
        let isLiveEditActive = false;

        function fmtNum(n, decimals = 2) {
            if (n === undefined || n === null || isNaN(n)) return '0';
            const num = Number(n);
            const dec = (num % 1 === 0) ? 0 : (num % 0.01 === 0 ? 2 : 3);
            return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: dec});
        }

        function getWarehouseId(nameOrId) {
            if (!nameOrId) return '';
            const wh = warehousesDb.find(w => w.id === nameOrId || w.name === nameOrId);
            return wh ? wh.id : nameOrId;
        }

        // ═══════════════════════════════════════════════════════════
        // ⚖️ ITEM UNITS HANDLING & CONVERSION ENGINE
        // ═══════════════════════════════════════════════════════════
        function getSelectedItemObj() {
            const itemId = document.getElementById('item-select')?.value;
            if (!itemId) return null;
            return itemsDb.find(i => String(i.id) === String(itemId)) || null;
        }

        function safeString(val) {
            if (!val) return '';
            if (typeof val === 'string') return val.trim();
            if (val.name) return val.name.trim();
            if (val.unitName) return val.unitName.trim();
            if (val.unit) return safeString(val.unit);
            return String(val).trim();
        }

        function getItemUnitsList(item) {
            if (!item) return [];
            let list = [];
            
            // 1. From item.units matrix
            if (Array.isArray(item.units) && item.units.length > 0) {
                item.units.forEach(u => {
                    if (u) {
                        const uName = safeString(u.unitName || u.name || u.unit || '');
                        if (uName) {
                            list.push({
                                name: uName,
                                factor: parseFloat(u.factor || u.conversionFactor) || 1
                            });
                        }
                    }
                });
            }

            // 2. Base unit fallback
            const base = safeString(item.baseUnit || item.unit || item.selectedUnit || '');
            if (base && !list.some(u => u.name === base)) {
                list.unshift({ name: base, factor: 1 });
            }

            // 3. Secondary / Sub-unit fallback if defined on item
            ['subUnit', 'secondaryUnit', 'unit2', 'thirdUnit', 'unit3', 'fourthUnit', 'unit4'].forEach(key => {
                if (item[key] && typeof item[key] === 'string' && item[key].trim()) {
                    const name = item[key].trim();
                    if (!list.some(u => u.name === name)) {
                        let factor = 1;
                        if (key === 'subUnit' || key === 'secondaryUnit' || key === 'unit2') {
                            factor = parseFloat(item.conversionFactor || item.bundleFactor || item.factor2) || 1;
                        } else if (key === 'thirdUnit' || key === 'unit3') {
                            factor = parseFloat(item.thirdFactor || item.factor3) || 1;
                        } else if (key === 'fourthUnit' || key === 'unit4') {
                            factor = parseFloat(item.fourthFactor || item.factor4) || 1;
                        }
                        list.push({ name, factor });
                    }
                }
            });

            // 4. Scan transactions for any additional unit names used for this item
            try {
                const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
                purchases.forEach(inv => {
                    (inv.items || inv.lines || []).forEach(line => {
                        if (item.id && (String(line.itemId) === String(item.id) || String(line.productId) === String(item.id)) && (line.unit || line.unitName)) {
                            const uName = safeString(line.unit || line.unitName);
                            if (uName && !list.some(u => u.name === uName)) {
                                list.push({ name: uName, factor: parseFloat(line.customFactor || line.factor) || 1 });
                            }
                        }
                    });
                });
                const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
                sales.forEach(inv => {
                    (inv.items || []).forEach(line => {
                        if (line.itemId === item.id && line.unit) {
                            const uName = safeString(line.unit || line.unitName);
                            if (uName && !list.some(u => u.name === uName)) {
                                list.push({ name: uName, factor: parseFloat(line.customFactor || line.factor) || 1 });
                            }
                        }
                    });
                });
            } catch(e) {}

            if (list.length === 0) {
                list.push({ name: 'وحدة أساسية', factor: 1 });
            }

            // Ensure unique unit names
            const uniqueMap = new Map();
            list.forEach(u => {
                if (!uniqueMap.has(u.name)) {
                    uniqueMap.set(u.name, u);
                }
            });
            return Array.from(uniqueMap.values());
        }

        function onItemSelected() {
            const item = getSelectedItemObj();
            const uSelect = document.getElementById('display-unit-select');
            
            if (uSelect) {
                uSelect.innerHTML = '';
                if (!item) {
                    uSelect.innerHTML = '<option value="">-- الوحدة الأساسية --</option>';
                } else {
                    const units = getItemUnitsList(item);
                    console.log("Units for item:", item.name, units);
                    units.forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = u.name;
                        opt.text = u.name;
                        uSelect.appendChild(opt);
                    });
                }
            }
            loadLedger();
        }

        function onUnit1Focus() {
            const item = getSelectedItemObj();
            if (!item) return;
            const units = getItemUnitsList(item);
                    console.log("Units for item:", item.name, units);
            const u1 = document.getElementById('unit-select-1');
            if (!u1) return;
            const curVal = u1.value;
            u1.innerHTML = '';
            units.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.name;
                opt.text = u.name;
                u1.appendChild(opt);
            });
            if (curVal && units.some(u => u.name === curVal)) {
                u1.value = curVal;
            } else if (units.length > 0) {
                u1.value = units[0].name;
            }
        }

        function onUnit2Focus() {
            const item = getSelectedItemObj();
            if (!item) return;
            const units = getItemUnitsList(item);
                    console.log("Units for item:", item.name, units);
            refreshUnit2Dropdown(units, false);
        }

        function onUnit3Focus() {
            const item = getSelectedItemObj();
            if (!item) return;
            const units = getItemUnitsList(item);
                    console.log("Units for item:", item.name, units);
            refreshUnit3Dropdown(units, false);
        }

        function onUnit4Focus() {
            const item = getSelectedItemObj();
            if (!item) return;
            const units = getItemUnitsList(item);
                    console.log("Units for item:", item.name, units);
            refreshUnit4Dropdown(units, false);
        }

        function onUnit1Change() {
            const item = getSelectedItemObj();
            const units = getItemUnitsList(item);
                    console.log("Units for item:", item.name, units);

            refreshUnit2Dropdown(units, true);
            refreshUnit3Dropdown(units, true);
            refreshUnit4Dropdown(units, true);
            loadLedger();
        }

        function onUnit2Change() {
            const item = getSelectedItemObj();
            const units = getItemUnitsList(item);
                    console.log("Units for item:", item.name, units);

            refreshUnit3Dropdown(units, true);
            refreshUnit4Dropdown(units, true);
            loadLedger();
        }

        function onUnit3Change() {
            const item = getSelectedItemObj();
            const units = getItemUnitsList(item);
                    console.log("Units for item:", item.name, units);

            refreshUnit4Dropdown(units, true);
            loadLedger();
        }

        function onUnit4Change() {
            loadLedger();
        }

        function refreshUnit2Dropdown(units, isAutoPick = false) {
            const u1Val = document.getElementById('unit-select-1')?.value || '';
            const u2 = document.getElementById('unit-select-2');
            if (!u2) return;

            const prevU2Val = u2.value;
            u2.innerHTML = '<option value="">(بدون وحدة 2)</option>';

            // Available for Unit 2: All item units EXCEPT Unit 1
            const availableForU2 = units.filter(u => u.name !== u1Val);
            availableForU2.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.name;
                opt.text = u.name;
                u2.appendChild(opt);
            });

            // Keep previous selection if still valid; otherwise pick default or empty
            if (prevU2Val && availableForU2.some(u => u.name === prevU2Val)) {
                u2.value = prevU2Val;
            } else if (isAutoPick && availableForU2.length > 0 && !prevU2Val) {
                u2.value = availableForU2[0].name;
            } else {
                u2.value = '';
            }
        }

        function refreshUnit3Dropdown(units, isAutoPick = false) {
            const u1Val = document.getElementById('unit-select-1')?.value || '';
            const u2Val = document.getElementById('unit-select-2')?.value || '';
            const u3 = document.getElementById('unit-select-3');
            if (!u3) return;

            const prevU3Val = u3.value;
            u3.innerHTML = '<option value="">(بدون وحدة 3)</option>';

            // Available for Unit 3: All item units EXCEPT Unit 1 AND Unit 2
            const availableForU3 = units.filter(u => u.name !== u1Val && u.name !== u2Val);
            availableForU3.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.name;
                opt.text = u.name;
                u3.appendChild(opt);
            });

            // Keep previous selection if still valid; otherwise pick default or empty
            if (prevU3Val && availableForU3.some(u => u.name === prevU3Val)) {
                u3.value = prevU3Val;
            } else if (isAutoPick && availableForU3.length > 0 && !prevU3Val && u2Val) {
                u3.value = availableForU3[0].name;
            } else {
                u3.value = '';
            }
        }

        function refreshUnit4Dropdown(units, isAutoPick = false) {
            const u1Val = document.getElementById('unit-select-1')?.value || '';
            const u2Val = document.getElementById('unit-select-2')?.value || '';
            const u3Val = document.getElementById('unit-select-3')?.value || '';
            const u4 = document.getElementById('unit-select-4');
            if (!u4) return;

            const prevU4Val = u4.value;
            u4.innerHTML = '<option value="">(بدون وحدة 4)</option>';

            // Available for Unit 4: All item units EXCEPT Unit 1, Unit 2, Unit 3
            const availableForU4 = units.filter(u => u.name !== u1Val && u.name !== u2Val && u.name !== u3Val);
            availableForU4.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.name;
                opt.text = u.name;
                u4.appendChild(opt);
            });

            if (prevU4Val && availableForU4.some(u => u.name === prevU4Val)) {
                u4.value = prevU4Val;
            } else if (isAutoPick && availableForU4.length > 0 && !prevU4Val && u3Val) {
                u4.value = availableForU4[0].name;
            } else {
                u4.value = '';
            }
        }

        function getActiveSelectedUnits(item) {
            if (!item) return [];
            const unitsList = getItemUnitsList(item);
            const selectedUnitName = document.getElementById('display-unit-select')?.value;
            
            if (selectedUnitName) {
                const matched = unitsList.find(u => u.name === selectedUnitName);
                if (matched) return [matched];
            }
            return unitsList.length > 0 ? [unitsList[0]] : [{ name: 'وحدة', factor: 1 }];
        }

        function formatQtyInUnit(baseQty, unitObj) {
            if (baseQty === undefined || baseQty === null || isNaN(baseQty) || baseQty === 0) return '-';
            const factor = (unitObj && unitObj.factor > 0) ? unitObj.factor : 1;
            const val = baseQty / factor;
            return fmtNum(val);
        }
        
        function initItemLedger() {
            itemsDb = JSON.parse(localStorage.getItem('erp_items_v1')) || JSON.parse(localStorage.getItem('erp_items')) || [];
            populateItemDropdown();

            // Load Warehouses
            const whSelect = document.getElementById('warehouse-select');
            if (whSelect && whSelect.options.length <= 1) {
                whSelect.innerHTML = '<option value="ALL">🏢 جميع المخازن (الجرد العام)</option>';
                warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                const displayWh = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];
                displayWh.forEach(wh => {
                    const opt = document.createElement('option');
                    opt.value = wh.id;
                    opt.text = wh.name;
                    whSelect.appendChild(opt);
                });
            }

            // Set default dates if empty
            const dateTo = document.getElementById('date-to');
            if (dateTo && !dateTo.value) {
                dateTo.valueAsDate = new Date();
            }

            // Check URL param ?item=ID
            const urlParams = new URLSearchParams(window.location.search);
            const itemId = urlParams.get('item');
            const select = document.getElementById('item-select');
            if (itemId && select) {
                select.value = itemId;
                onItemSelected();
            }
        }

        function populateItemDropdown(filterText = '') {
            const select = document.getElementById('item-select');
            if (!select) return;
            const curVal = select.value;
            select.innerHTML = '<option value="">-- يرجى اختيار صنف --</option>';

            const query = (filterText || '').trim().toLowerCase();
            let count = 0;

            itemsDb.forEach(item => {
                const code = String(item.code || item.id || '');
                const name = String(item.name || '');
                const cat = String(item.category || '');
                const searchStr = `${code} ${name} ${cat}`.toLowerCase();

                if (!query || searchStr.includes(query)) {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.text = name;
                    select.appendChild(opt);
                    count++;
                }
            });

            const countBadge = document.getElementById('item-count-badge');
            if (countBadge) {
                countBadge.textContent = `(${count} صنف)`;
            }

            if (curVal && Array.from(select.options).some(o => String(o.value) === String(curVal))) {
                select.value = curVal;
            }
        }

        function filterItemDropdown(query) {
            populateItemDropdown(query);
            const select = document.getElementById('item-select');
            if (select && select.options.length > 1 && query.trim()) {
                select.selectedIndex = 1;
                onItemSelected();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initItemLedger);
        } else {
            initItemLedger();
        }
        document.addEventListener('DBReady', initItemLedger);
        window.addEventListener('load', initItemLedger);

        function buildRealHistory(itemId) {
            let history = [];
            const itemObj = itemsDb.find(i => String(i.id) === String(itemId));
            const itemBaseUnit = safeString(itemObj ? (itemObj.baseUnit || itemObj.unit || '') : '');

            // 0. Initial Opening Balance (from item record if created with stock)
            if (itemObj) {
                let initBal = parseFloat(itemObj.openingBalance || itemObj.openBalance || itemObj.initialBalance || itemObj.openingQty) || 0;
                if (initBal > 0) {
                    history.push({
                        date: new Date(itemObj.createdAt || '2020-01-01T00:00:00'),
                        typeCode: 'open',
                        typeName: 'رصيد افتتاحي (أول المدة)',
                        permitNo: 'OPEN',
                        permitDocId: '',
                        receiptNo: 'OPEN',
                        receiptDocId: '',
                        isGRN: false,
                        ref: 'رصيد أول المدة',
                        docId: '',
                        warehouse: 'ALL',
                        notes: 'الرصيد الافتتاحي عند تعريف الصنف في النظام',
                        in: initBal,
                        out: 0,
                        origQty: initBal,
                        origUnit: itemBaseUnit,
                        origFactor: 1,
                        origIsSameAsBase: true
                    });
                }
            }

            // Helper to resolve unit factor accurately matching inventory-report.php
            function resolveUnitFactor(line, item) {
                if (line.customBaseQty !== undefined && parseFloat(line.quantity) > 0) {
                    return parseFloat(line.customBaseQty) / parseFloat(line.quantity);
                }
                if (line.customFactor && parseFloat(line.customFactor) > 0) {
                    return parseFloat(line.customFactor);
                }
                if (line.factor && parseFloat(line.factor) > 0) {
                    return parseFloat(line.factor);
                }
                if (item) {
                    const uName = String(line.unitName || line.unit || line.selectedUnit || '').trim();
                    if (uName) {
                        const list = getItemUnitsList(item);
                        const matched = list.find(u => u.name === uName);
                        if (matched && parseFloat(matched.factor) > 0) {
                            return parseFloat(matched.factor);
                        }
                    }
                    if (item.conversionFactor && parseFloat(item.conversionFactor) > 0 && uName !== itemBaseUnit) {
                        return parseFloat(item.conversionFactor);
                    }
                }
                return 1;
            }
            
            // 1. Get Purchases & Adjustments
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            purchases.forEach(inv => {
                (inv.items || inv.lines || []).forEach(line => {
                    if (String(line.itemId) === String(itemId) || String(line.productId) === String(itemId)) {
                        let wh = getWarehouseId(line.warehouseId || inv.warehouseId || inv.warehouse);
                        if (wh === 'PENDING_RECEIPT') {
                            return; // Skip pending items
                        }

                        const origQty    = parseFloat(line.quantity) || 0;
                        const origUnit   = safeString(line.unitName || line.unit || line.selectedUnit || itemBaseUnit || '');
                        const origFactor = resolveUnitFactor(line, itemObj);

                        let baseQty = (line.customBaseQty !== undefined) ? (parseFloat(line.customBaseQty) || 0) : (origQty * origFactor);

                        let typeName = 'فاتورة مشتريات';
                        let typeCode = 'in';
                        let valIn = baseQty;
                        let valOut = 0;

                        if (inv.isAdjustment) {
                            if (baseQty >= 0) {
                                typeName = 'تسوية جردية (إضافة)';
                                typeCode = 'in';
                                valIn = baseQty;
                                valOut = 0;
                            } else {
                                typeName = 'تسوية جردية (عجز)';
                                typeCode = 'out';
                                valIn = 0;
                                valOut = Math.abs(baseQty);
                            }
                        } else if (inv.isReturn) {
                            typeName = 'مردودات مشتريات';
                            typeCode = 'out';
                            valIn = 0;
                            valOut = Math.abs(baseQty);
                        } else if (inv.isGRN) {
                            typeName = 'مذكرة استلام مخزني';
                        }

                        let permitNo = '-';
                        let receiptNo = inv.invoiceNo || inv.id || '-';
                        let permitDocId = '';
                        let receiptDocId = inv.id || inv.invoiceNo || '';

                        if (inv.isGRN) {
                            permitNo = inv.originalInvoiceId || inv.invoiceNo || inv.poNumber || inv.orderNo || '-';
                            receiptNo = inv.id || inv.grnNumber || inv.receiptNo || '-';
                            permitDocId = inv.originalInvoiceId || inv.invoiceNo || '';
                            receiptDocId = inv.id || '';
                        } else {
                            permitNo = inv.originalInvoiceId || inv.poNumber || inv.orderNo || inv.permitNo || inv.invoiceNo || inv.id || '-';
                            receiptNo = inv.receiptNo || inv.invoiceNo || inv.id || '-';
                            permitDocId = permitNo !== '-' ? permitNo : (inv.invoiceNo || inv.id);
                            receiptDocId = inv.invoiceNo || inv.id;
                        }

                        let noteText = '';
                        if (line.notes && line.notes.trim()) {
                            noteText = line.notes.trim();
                        } else if (inv.notes) {
                            let cleanNote = inv.notes.replace(/^مذكرة استلام بضاعة للفاتورة\s+[^\s]+\s*/, '').replace(/^\(|\)$/g, '').trim();
                            if (cleanNote) noteText = cleanNote;
                        }
                        if (!noteText && inv.remarks) noteText = inv.remarks.trim();

                        history.push({
                            date: new Date(inv.date || inv.receiveDate || Date.now()),
                            typeCode: typeCode,
                            typeName: typeName,
                            permitNo: permitNo,
                            permitDocId: permitDocId,
                            receiptNo: receiptNo,
                            receiptDocId: receiptDocId,
                            isGRN: !!inv.isGRN,
                            ref: receiptNo,
                            docId: receiptDocId,
                            warehouse: wh,
                            notes: noteText,
                            in: valIn,
                            out: valOut,
                            origQty: typeCode === 'out' ? -origQty : origQty,
                            origUnit: origUnit,
                            origFactor: origFactor,
                            origIsSameAsBase: (origUnit === itemBaseUnit || origFactor === 1)
                        });
                    }
                });
            });

            // 2. Get Sales
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted && x.status !== 'draft' && x.status !== 'preliminary' && x.status !== 'pending_delivery');
            sales.forEach(inv => {
                let hdrWh = inv.warehouse;
                
                (inv.items || []).forEach(line => {
                    if (String(line.itemId) === String(itemId)) {
                        if (inv.saleType === 'reserved' && inv.deliveryStatus !== 'delivered') {
                            return; // Skip if reserved and not delivered
                        }

                        let lineWh = getWarehouseId(inv.deliveryWarehouse || line.warehouseId || hdrWh);
                        if (lineWh === 'PENDING_RECEIPT') {
                            return;
                        }
                        
                        const origQty    = parseFloat(line.quantity) || 0;
                        const origUnit   = safeString(line.unitName || line.unit || line.selectedUnit || itemBaseUnit || '');
                        const origFactor = resolveUnitFactor(line, itemObj);

                        let baseQty = (line.customBaseQty !== undefined) ? (parseFloat(line.customBaseQty) || 0) : (origQty * origFactor);

                        let permitNo = inv.orderNo || inv.soNumber || inv.quotationNo || inv.dispatchOrderNo || inv.permitNo || inv.invoiceNo || inv.id || '-';
                        let receiptNo = inv.invoiceNo || inv.receiptNo || inv.id || 'INV';
                        let permitDocId = permitNo !== '-' ? permitNo : (inv.invoiceNo || inv.id);
                        let receiptDocId = inv.invoiceNo || inv.id;

                        let noteText = '';
                        if (line.notes && line.notes.trim()) {
                            noteText = line.notes.trim();
                        } else if (inv.warehouseNotes && inv.warehouseNotes.trim()) {
                            noteText = inv.warehouseNotes.trim();
                        } else if (inv.deliveryNotes && inv.deliveryNotes.trim()) {
                            noteText = inv.deliveryNotes.trim();
                        } else if (inv.personalNotes && inv.personalNotes.trim()) {
                            noteText = inv.personalNotes.trim();
                        } else if (inv.notes && inv.notes.trim()) {
                            noteText = inv.notes.trim();
                        } else if (inv.remarks && inv.remarks.trim()) {
                            noteText = inv.remarks.trim();
                        }

                        let typeCode = 'out';
                        let typeName = 'فاتورة مبيعات';
                        let valIn = 0;
                        let valOut = baseQty;

                        if (inv.isReturn === true) {
                            typeCode = 'in';
                            typeName = 'مردودات مبيعات';
                            valIn = baseQty;
                            valOut = 0;
                        }

                        history.push({
                            date: new Date(inv.date || Date.now()),
                            typeCode: typeCode,
                            typeName: typeName,
                            permitNo: permitNo,
                            permitDocId: permitDocId,
                            receiptNo: receiptNo,
                            receiptDocId: receiptDocId,
                            isGRN: false,
                            ref: receiptNo,
                            docId: receiptDocId,
                            warehouse: lineWh,
                            notes: noteText,
                            in: valIn,
                            out: valOut,
                            origQty: typeCode === 'in' ? origQty : -origQty,
                            origUnit: origUnit,
                            origFactor: origFactor,
                            origIsSameAsBase: (origUnit === itemBaseUnit || origFactor === 1)
                        });
                    }
                });
            });

            // 3. Get Warehouse Transfers
            let transfers = JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1')) || [];
            transfers = transfers.filter(x => !x.deleted && x.status !== 'cancelled');
            transfers.forEach(tr => {
                let fromWh = getWarehouseId(tr.fromWarehouse);
                let toWh = getWarehouseId(tr.toWarehouse);
                let fromWhName = tr.fromWarehouseName || tr.fromWarehouse || 'المخزن';
                let toWhName = tr.toWarehouseName || tr.toWarehouse || 'المخزن';
                (tr.items || []).forEach(line => {
                    if (String(line.itemId) === String(itemId)) {
                        const origQty = parseFloat(line.quantity) || 0;
                        const origUnit   = safeString(line.unitName || line.unit || line.selectedUnit || itemBaseUnit || '');
                        let resolvedFactor = resolveUnitFactor(line, itemObj);
                        let baseQty = (line.customBaseQty !== undefined) ? parseFloat(line.customBaseQty) : (origQty * resolvedFactor);

                        // Outward from source warehouse
                        history.push({
                            date: new Date(tr.date || tr.createdAt || Date.now()),
                            typeCode: 'out',
                            typeName: `تحويل مخزني (صادر إلى ${toWhName})`,
                            permitNo: tr.transferNo || tr.id || '-',
                            permitDocId: tr.id || '',
                            receiptNo: tr.transferNo || tr.id || '-',
                            receiptDocId: tr.id || '',
                            isGRN: false,
                            ref: tr.transferNo || tr.id,
                            docId: tr.id,
                            warehouse: fromWh,
                            notes: tr.notes || line.notes || '',
                            in: 0,
                            out: baseQty,
                            origQty: -origQty,
                            origUnit: origUnit,
                            origFactor: resolvedFactor,
                            origIsSameAsBase: (origUnit === itemBaseUnit || resolvedFactor === 1)
                        });

                        // Inward to destination warehouse
                        history.push({
                            date: new Date(tr.date || tr.createdAt || Date.now()),
                            typeCode: 'in',
                            typeName: `تحويل مخزني (وارد من ${fromWhName})`,
                            permitNo: tr.transferNo || tr.id || '-',
                            permitDocId: tr.id || '',
                            receiptNo: tr.transferNo || tr.id || '-',
                            receiptDocId: tr.id || '',
                            isGRN: false,
                            ref: tr.transferNo || tr.id,
                            docId: tr.id,
                            warehouse: toWh,
                            notes: tr.notes || line.notes || '',
                            in: baseQty,
                            out: 0,
                            origQty: origQty,
                            origUnit: origUnit,
                            origFactor: resolvedFactor,
                            origIsSameAsBase: (origUnit === itemBaseUnit || resolvedFactor === 1)
                        });
                    }
                });
            });

            // Sort chronological
            history.sort((a,b) => a.date - b.date);
            return history;
        }

        function renderLedgerTableHeader(activeUnits) {
            const thead = document.querySelector('.dash-table thead');
            if (!thead) return;

            const baseUnitName = activeUnits.length > 0 ? activeUnits[0].name : 'وحدة';
            const numBalCols   = activeUnits.length;

            if (activeUnits.length === 1) {
                thead.innerHTML = `
                    <tr>
                        <th style="white-space:nowrap;">التاريخ</th>
                        <th>نوع الحركة</th>
                        <th style="color:#38bdf8;">رقم الإذن</th>
                        <th>رقم الاستلام</th>
                        <th style="color:#8b5cf6; background:rgba(139,92,246,0.05); text-align:right;">وحدة وكمية الوارد والصادر</th>
                        <th style="color:#10b981; background:rgba(16,185,129,0.07); text-align:right; white-space:nowrap;">
                            الوارد<br><span style="font-size:0.75rem; font-weight:600; opacity:0.75;">(بـ ${baseUnitName})</span>
                        </th>
                        <th style="color:#ec4899; background:rgba(236,72,153,0.07); text-align:right; white-space:nowrap;">
                            المنصرف<br><span style="font-size:0.75rem; font-weight:600; opacity:0.75;">(بـ ${baseUnitName})</span>
                        </th>
                        <th style="color:#fbbf24; background:rgba(251,191,36,0.07); text-align:right; white-space:nowrap;">
                            الرصيد<br><span style="font-size:0.75rem; font-weight:600; opacity:0.75;">(${baseUnitName})</span>
                        </th>
                        <th style="color:#38bdf8;">ملحوظة الحركة</th>
                    </tr>
                `;
            } else {
                let uHeadersBal = activeUnits.map(u => `<th style="color:#fbbf24; background:rgba(251,191,36,0.08); text-align:right; font-size:0.75rem; padding:8px 10px;">${u.name}</th>`).join('');

                thead.innerHTML = `
                    <tr>
                        <th rowspan="2" style="vertical-align:middle; white-space:nowrap;">التاريخ</th>
                        <th rowspan="2" style="vertical-align:middle;">نوع الحركة</th>
                        <th rowspan="2" style="color:#38bdf8; vertical-align:middle;">رقم الإذن</th>
                        <th rowspan="2" style="vertical-align:middle;">رقم الاستلام</th>
                        <th rowspan="2" style="color:#8b5cf6; background:rgba(139,92,246,0.05); vertical-align:middle; text-align:right;">وحدة وكمية الوارد والصادر</th>
                        <th rowspan="2" style="color:#10b981; background:rgba(16,185,129,0.07); vertical-align:middle; text-align:right; white-space:nowrap;">
                            الوارد<br><span style="font-size:0.72rem; opacity:0.75;">(بوحدة الحركة)</span>
                        </th>
                        <th rowspan="2" style="color:#ec4899; background:rgba(236,72,153,0.07); vertical-align:middle; text-align:right; white-space:nowrap;">
                            المنصرف<br><span style="font-size:0.72rem; opacity:0.75;">(بوحدة الحركة)</span>
                        </th>
                        <th colspan="${numBalCols}" style="color:#fbbf24; background:rgba(251,191,36,0.05); text-align:center; border-bottom:1px solid rgba(251,191,36,0.25);">الرصيد التراكمي</th>
                        <th rowspan="2" style="color:#38bdf8; vertical-align:middle;">ملحوظة الحركة</th>
                    </tr>
                    <tr>
                        ${uHeadersBal}
                    </tr>
                `;
            }
        }

        function renderKpis(sumOpen, sumIn, sumOut, actualCurrentStock, activeUnits) {
            const item = getSelectedItemObj();
            const itemUnits = getItemUnitsList(item);
            const selectedUnitName = activeUnits[0] ? activeUnits[0].name : '';
            // Ensure the selected unit is always the primary (first) unit shown in KPI
            const allUnits = [
                ...activeUnits,
                ...itemUnits.filter(u => u.name !== selectedUnitName)
            ];

            function formatKpiDisplay(val, units, themeColor) {
                if (!units || units.length === 0) return '0';
                
                const unitsHtml = units.filter(u => u.factor > 0).map(u => `
                    <div style="display:flex; flex-direction:column; align-items:center; text-align:center; padding: 4px 8px; background: rgba(0,0,0,0.15); border-radius: 8px; flex: 1; min-width: 60px;">
                        <span style="font-size:0.7rem; font-weight:700; color:#cbd5e1; opacity:0.9; margin-bottom: 2px;">${u.name}</span>
                        <strong style="font-size:1.15rem; font-weight:900; color:${themeColor}; font-family: 'Courier New', Courier, monospace; letter-spacing: -0.5px;">${formatQtyInUnit(val, u)}</strong>
                    </div>
                `).join('');

                return `
                    <div style="display:flex; flex-direction:row; flex-wrap:wrap; gap: 8px; width: 100%; justify-content: flex-start; margin-top: 5px;">
                        ${unitsHtml}
                    </div>
                `;
            }

            document.getElementById('sum-open').innerHTML = formatKpiDisplay(sumOpen, allUnits, '#60a5fa');
            document.getElementById('sum-in').innerHTML = formatKpiDisplay(sumIn, allUnits, '#34d399');
            document.getElementById('sum-out').innerHTML = formatKpiDisplay(sumOut, allUnits, '#f472b6');
            document.getElementById('sum-balance').innerHTML = formatKpiDisplay(actualCurrentStock, allUnits, '#fbbf24');
        }

        function loadLedger() {
            const itemId = document.getElementById('item-select').value;
            const tbody = document.getElementById('ledger-tbody');
            
            if (!itemId) {
                tbody.innerHTML = `<tr><td colspan="14" style="text-align:center; padding:30px; color:var(--text-secondary);">يرجى اختيار صنف لعرض حركته التفصيلية</td></tr>`;
                document.getElementById('sum-open').innerText = '0';
                document.getElementById('sum-in').innerText = '0';
                document.getElementById('sum-out').innerText = '0';
                document.getElementById('sum-balance').innerText = '0';
                return;
            }

            const item = itemsDb.find(i => String(i.id) === String(itemId));
            const activeUnits = getActiveSelectedUnits(item);
            renderLedgerTableHeader(activeUnits);
            
            // 1. Build history
            let history = buildRealHistory(itemId);

            // 2. Filter by warehouse
            const filterWh = document.getElementById('warehouse-select').value;
            if (filterWh && filterWh !== 'ALL') {
                history = history.filter(h => getWarehouseId(h.warehouse) === getWarehouseId(filterWh) || h.typeCode === 'open');
            }

            // 3. Sort chronologically
            history.sort((a, b) => new Date(a.date) - new Date(b.date));

            // 4. Calculate true running balances
            let running = 0;
            history.forEach(row => {
                running += (row.in - row.out);
                row.runningBalance = running;
            });
            const actualCurrentStock = running;

            // 5. Apply Date Filters for display
            const fromDateStr = document.getElementById('date-from').value;
            const toDateStr = document.getElementById('date-to').value;
            
            let filteredHistory = history.map(h => ({ ...h, date: new Date(h.date) }));

            if (fromDateStr) {
                let d = new Date(fromDateStr); d.setHours(0,0,0,0);
                filteredHistory = filteredHistory.filter(h => h.date >= d);
            }
            if (toDateStr) {
                let d = new Date(toDateStr); d.setHours(23,59,59,999);
                filteredHistory = filteredHistory.filter(h => h.date <= d);
            }

            tbody.innerHTML = '';
            let sumIn = 0;
            let sumOut = 0;
            let sumOpen = 0;

            const totalCols = 5 + (activeUnits.length * 3);

            if (filteredHistory.length === 0) {
                 tbody.innerHTML = `<tr><td colspan="${totalCols}" style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد حركات لهذا الصنف في هذه الفترة</td></tr>`;
            } else {
                 const firstFilteredIndex = history.findIndex(h => h.date >= new Date(fromDateStr || '1970-01-01'));
                 if (firstFilteredIndex > 0) {
                     sumOpen = history[firstFilteredIndex - 1].runningBalance;
                 } else {
                     sumOpen = 0;
                 }
            }

            filteredHistory.forEach((row) => {
                if (row.typeCode === 'in') sumIn += row.in;
                if (row.typeCode === 'out') sumOut += row.out;

                let typeBadge = '';
                if (row.typeCode === 'in') typeBadge = `<span class="type-badge type-in">${row.typeName}</span>`;
                else if (row.typeCode === 'out') typeBadge = `<span class="type-badge type-out">${row.typeName}</span>`;
                else typeBadge = `<span class="type-badge type-bal">${row.typeName}</span>`;

                const dStr = row.date.toISOString().split('T')[0];

                const tr = document.createElement('tr');

                // Permit rendering
                let permitTarget = '';
                if (row.typeCode === 'out') permitTarget = `sales-invoice.php?id=${row.permitDocId}`;
                else if (row.typeCode === 'in') permitTarget = `purchase.php?id=${row.permitDocId}`;

                let permitHtml = '-';
                if (row.permitNo && row.permitNo !== '-') {
                    if (row.permitDocId && permitTarget) {
                        permitHtml = `
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; padding:3px 8px; border-radius:6px; font-size:0.82rem; font-weight:800; font-family:monospace; display:inline-flex; align-items:center; gap:4px; cursor:pointer;" onclick="event.stopPropagation(); window.open('${permitTarget}', '_blank')" title="انقر لفتح الفاتورة / الإذن الأصلي (${row.permitNo})">
                                    📄 ${row.permitNo}
                                </span>
                            </div>
                        `;
                    } else {
                        permitHtml = `<span style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); color:#38bdf8; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700; font-family:monospace;">📄 ${row.permitNo}</span>`;
                    }
                } else {
                    permitHtml = `<span style="color:#64748b; font-weight:600;">-</span>`;
                }

                // Receipt Note rendering
                let receiptTarget = '';
                if (row.typeCode === 'out') receiptTarget = `sales-invoice.php?id=${row.receiptDocId || row.docId}`;
                else if (row.typeCode === 'in') {
                    if (row.isGRN) receiptTarget = `purchase-receipt.php?id=${row.receiptDocId || row.docId}`;
                    else receiptTarget = `purchase.php?id=${row.receiptDocId || row.docId}`;
                }

                let refHtml = row.ref || row.receiptNo;
                if (row.receiptDocId && receiptTarget) {
                    tr.style.cursor = 'pointer';
                    tr.title = 'انقر لفتح المستند';
                    tr.onclick = () => { window.open(receiptTarget, '_blank'); };
                    tr.onmouseover = () => tr.style.background = 'rgba(236, 72, 153, 0.05)';
                    tr.onmouseout = () => tr.style.background = '';
                    
                    refHtml = `
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700;">${row.ref}</span>
                            <button onclick="event.stopPropagation(); window.open('${receiptTarget}', '_blank')" style="background:rgba(236,72,153,0.1); color:#ec4899; border:1px solid rgba(236,72,153,0.3); border-radius:6px; padding:3px 8px; cursor:pointer; font-size:0.75rem; font-weight:bold; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#ec4899'; this.style.color='#fff';" onmouseout="this.style.background='rgba(236,72,153,0.1)'; this.style.color='#ec4899';">
                                🔗 عرض الأصل
                            </button>
                        </div>
                    `;
                }

                const noteHtml = (row.notes && row.notes !== '-' && row.notes.trim()) ?
                    `<span style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); color:#bae6fd; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:600; display:inline-flex; align-items:center; gap:4px; max-width:240px; white-space:normal; text-align:right;">📝 ${row.notes}</span>` :
                    `<span style="color:#64748b;">-</span>`;

                function buildSimpleQtyCell(qty, direction) {
                    const isIn = direction === 'in';
                    if ((isIn && row.in <= 0) || (!isIn && row.out <= 0)) {
                        return `<td style="${isIn ? 'color:#10b981' : 'color:#ec4899'}; font-weight:700; background:${isIn ? 'rgba(16,185,129,0.02)' : 'rgba(236,72,153,0.02)'}; text-align:right;">-</td>`;
                    }
                    const baseQty = isIn ? row.in : row.out;
                    const baseUnitObj = activeUnits[0] || { name: 'وحدة', factor: 1 };
                    const formatted = formatQtyInUnit(baseQty, baseUnitObj);
                    
                    return `<td style="font-weight:900; color:${isIn ? '#10b981' : '#ec4899'}; background:${isIn ? 'rgba(16,185,129,0.02)' : 'rgba(236,72,153,0.02)'}; text-align:right; vertical-align:middle;">
                        <span style="font-size:1.05rem;">${formatted}</span>
                        <span style="font-size:0.75rem; font-weight:600; opacity:0.8;">${baseUnitObj.name}</span>
                    </td>`;
                }

                // New logic for the specific Original Transaction Quantity column
                const origQtyAbs = Math.abs(row.origQty || (row.in > 0 ? row.in : row.out));
                const origUnitStr = row.origUnit || (activeUnits[0] ? activeUnits[0].name : '');
                const isInTx = (row.in > 0);
                
                let origBadgeHtml = '-';
                if (origQtyAbs > 0) {
                    origBadgeHtml = `
                        <div style="display:inline-flex; align-items:baseline; gap:4px; justify-content:flex-end; width:100%;">
                            <span style="font-size:1.0rem; font-weight:900; color:#8b5cf6;">${fmtNum(origQtyAbs)}</span>
                            <span style="font-size:0.8rem; font-weight:700; color:#8b5cf6; background:rgba(139,92,246,0.1); padding:2px 6px; border-radius:4px; border:1px solid rgba(139,92,246,0.2);">${origUnitStr}</span>
                        </div>
                    `;
                }
                const origCell = `<td style="background:rgba(139,92,246,0.02); vertical-align:middle; text-align:right;">${origBadgeHtml}</td>`;

                const inCell  = buildSimpleQtyCell(row.in, 'in');
                const outCell = buildSimpleQtyCell(row.out, 'out');

                let balCells = '';
                if (activeUnits.length === 1) {
                    const allUnits = getItemUnitsList(item);
                    const otherUnits = allUnits.filter(u => u.name !== activeUnits[0].name && u.factor > 0 );
                    let subBal = '';
                    if (otherUnits.length > 0) {
                        const subs = otherUnits.map(u => `<span style="color:#fbbf24; font-weight:800;">${formatQtyInUnit(row.runningBalance, u)}</span> <span style="opacity:0.8;">${u.name}</span>`).join(' <span style="opacity:0.4;">|</span> ');
                        subBal = `
                            <div style="font-size:0.75rem; color:#fde68a; font-family:monospace; background:rgba(251,191,36,0.08); padding:2px 8px; border-radius:5px; border:1px solid rgba(251,191,36,0.2);">
                                (= ${subs})
                            </div>`;
                    }
                    balCells = `
                        <td style="color:#fbbf24; font-weight:900; background:rgba(251,191,36,0.02); text-align:right; vertical-align:middle;">
                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:3px;">
                                <div style="display:inline-flex; align-items:baseline; gap:4px;">
                                    <span style="font-size:1.05rem; font-weight:900;">${formatQtyInUnit(row.runningBalance, activeUnits[0])}</span>
                                    <span style="font-size:0.8rem; font-weight:700; opacity:0.8;">${activeUnits[0].name}</span>
                                </div>
                                ${subBal}
                            </div>
                        </td>`;
                } else {
                    balCells = activeUnits.map(u => `
                        <td style="color:#fbbf24; font-weight:900; background:rgba(251,191,36,0.02); font-size:1.0rem; text-align:right; vertical-align:middle;">
                            <span style="font-size:1.05rem; font-weight:900;">${formatQtyInUnit(row.runningBalance, u)}</span>
                            <span style="font-size:0.75rem; font-weight:600; opacity:0.7;">${u.name}</span>
                        </td>
                    `).join('');
                }

                tr.innerHTML = `
                    <td style="white-space:nowrap;">${dStr}</td>
                    <td>${typeBadge}</td>
                    <td style="font-weight:700;">${permitHtml}</td>
                    <td style="font-weight:600;">${refHtml}</td>
                    ${origCell}
                    ${inCell}
                    ${outCell}
                    ${balCells}
                    <td>${noteHtml}</td>
                `;
                tbody.appendChild(tr);
            });

            renderKpis(sumOpen, sumIn, sumOut, actualCurrentStock, activeUnits);
        }

        // ═══════════════════════════════════════════════════════════
        // 🎨 ITEM LEDGER PREVIEW & DESIGN STUDIO LOGIC
        // ═══════════════════════════════════════════════════════════
        const DESIGN_STORAGE_KEY = 'item_ledger_preview_design_v2';

        const THEMES_CONFIG = {
            corporate: { primary: '#1e3a8a', secondary: '#0284c7', bgBadge: '#e0f2fe', textBadge: '#0369a1', headerBg: '#f8fafc', rowAlt: '#f8fafc', border: '#cbd5e1' },
            emerald:   { primary: '#065f46', secondary: '#10b981', bgBadge: '#d1fae5', textBadge: '#047857', headerBg: '#f0fdf4', rowAlt: '#f7fee7', border: '#a7f3d0' },
            gold:      { primary: '#854d0e', secondary: '#d97706', bgBadge: '#fef3c7', textBadge: '#b45309', headerBg: '#fffbeb', rowAlt: '#fffdf5', border: '#fde68a' },
            cyan:      { primary: '#0e7490', secondary: '#06b6d4', bgBadge: '#cffafe', textBadge: '#0891b2', headerBg: '#ecfeff', rowAlt: '#f0fdfa', border: '#a5f3fc' },
            purple:    { primary: '#581c87', secondary: '#9333ea', bgBadge: '#f3e8ff', textBadge: '#7e22ce', headerBg: '#faf5ff', rowAlt: '#fbf7ff', border: '#e9d5ff' },
            classic:   { primary: '#0f172a', secondary: '#475569', bgBadge: '#f1f5f9', textBadge: '#334155', headerBg: '#f8fafc', rowAlt: '#ffffff', border: '#94a3b8' }
        };

        function getSavedDesign() {
            try {
                const saved = localStorage.getItem(DESIGN_STORAGE_KEY);
                if (saved) return JSON.parse(saved);
            } catch(e) {}
            return {
                theme: 'corporate',
                font: "'Tajawal', sans-serif",
                size: 'medium',
                orientation: 'landscape',
                showHeader: true,
                showMeta: true,
                showKPIs: true,
                showSignatures: true,
                columns: [true, true, true, true, true, true, true, true]
            };
        }

        function toggleDesignStudioDrawer() {
            const drawer = document.getElementById('item-ledger-studio-drawer');
            if (drawer) {
                drawer.style.display = (drawer.style.display === 'none' ? 'flex' : 'none');
            }
        }

        function toggleLiveEditMode() {
            isLiveEditActive = !isLiveEditActive;
            const btn = document.getElementById('btn-toggle-live-edit');
            const sheet = document.getElementById('item-ledger-sheet');
            
            if (btn) {
                if (isLiveEditActive) {
                    btn.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
                    btn.style.color = '#fff';
                    btn.innerHTML = '<span>✅</span> جاري التعديل (انقر على أي نص)';
                } else {
                    btn.style.background = 'rgba(245,158,11,0.15)';
                    btn.style.color = '#fbbf24';
                    btn.innerHTML = '<span>✏️</span> تعديل النصوص مباشرة';
                }
            }

            if (sheet) {
                const editables = sheet.querySelectorAll('h2, h3, h4, p, span, td, th, div.editable-text');
                editables.forEach(el => {
                    el.contentEditable = isLiveEditActive ? 'true' : 'false';
                    if (isLiveEditActive) {
                        el.classList.add('live-editable-active');
                        el.style.outline = '1px dashed #38bdf8';
                        el.style.padding = '2px 4px';
                        el.style.borderRadius = '3px';
                    } else {
                        el.classList.remove('live-editable-active');
                        el.style.outline = 'none';
                        el.style.padding = '';
                    }
                });
            }
        }

        function saveStudioDesignPreset() {
            const design = {
                theme: document.getElementById('studio-theme-select').value,
                font: document.getElementById('studio-font-select').value,
                size: document.getElementById('studio-size-select').value,
                orientation: document.getElementById('studio-orientation-select').value,
                showHeader: document.getElementById('chk-show-header').checked,
                showMeta: document.getElementById('chk-show-meta').checked,
                showKPIs: document.getElementById('chk-show-kpis').checked,
                showSignatures: document.getElementById('chk-show-signatures').checked,
                columns: Array.from(document.querySelectorAll('.col-toggle')).map(c => c.checked)
            };
            localStorage.setItem(DESIGN_STORAGE_KEY, JSON.stringify(design));
            alert('تم حفظ وتثبيت تصميم التقرير المخصص بنجاح! 🎉');
        }

        function resetStudioDesignPreset() {
            localStorage.removeItem(DESIGN_STORAGE_KEY);
            const defaults = getSavedDesign();
            loadDesignIntoControls(defaults);
            applyStudioDesign();
            alert('تمت استعادة التنسيق الافتراضي بنجاح! 🔄');
        }

        function loadDesignIntoControls(d) {
            if (document.getElementById('studio-theme-select')) document.getElementById('studio-theme-select').value = d.theme || 'corporate';
            if (document.getElementById('studio-font-select')) document.getElementById('studio-font-select').value = d.font || "'Tajawal', sans-serif";
            if (document.getElementById('studio-size-select')) document.getElementById('studio-size-select').value = d.size || 'medium';
            if (document.getElementById('studio-orientation-select')) document.getElementById('studio-orientation-select').value = d.orientation || 'landscape';
            
            if (document.getElementById('chk-show-header')) document.getElementById('chk-show-header').checked = d.showHeader !== false;
            if (document.getElementById('chk-show-meta')) document.getElementById('chk-show-meta').checked = d.showMeta !== false;
            if (document.getElementById('chk-show-kpis')) document.getElementById('chk-show-kpis').checked = d.showKPIs !== false;
            if (document.getElementById('chk-show-signatures')) document.getElementById('chk-show-signatures').checked = d.showSignatures !== false;

            const colToggles = document.querySelectorAll('.col-toggle');
            colToggles.forEach((chk, idx) => {
                if (d.columns && d.columns[idx] !== undefined) {
                    chk.checked = d.columns[idx];
                } else {
                    chk.checked = true;
                }
            });
        }

        function openItemLedgerPreview() {
            const itemId = document.getElementById('item-select').value;
            if (!itemId) {
                alert('يرجى اختيار صنف أولاً لعرض ومعاينة حركته التفصيلية!');
                return;
            }

            const item = itemsDb.find(i => String(i.id) === String(itemId));
            const activeUnits = getActiveSelectedUnits(item);

            const whSelect = document.getElementById('warehouse-select');
            const whName = whSelect.options[whSelect.selectedIndex]?.text || 'جميع المخازن';
            
            const fromDateStr = document.getElementById('date-from').value;
            const toDateStr = document.getElementById('date-to').value;
            const periodStr = (fromDateStr || toDateStr) ? `من: ${fromDateStr || 'البداية'} إلى: ${toDateStr || 'اليوم'}` : 'كلي (جميع الفترات)';

            // Build fresh history data
            let history = buildRealHistory(itemId);
            const filterWh = document.getElementById('warehouse-select').value;
            if (filterWh && filterWh !== 'ALL') {
                history = history.filter(h => getWarehouseId(h.warehouse) === getWarehouseId(filterWh) || h.typeCode === 'open');
            }
            history.sort((a, b) => new Date(a.date) - new Date(b.date));

            let running = 0;
            history.forEach(row => {
                running += (row.in - row.out);
                row.runningBalance = running;
            });
            const actualCurrentStock = running;

            let filteredHistory = history.map(h => ({ ...h, date: new Date(h.date) }));
            if (fromDateStr) {
                let d = new Date(fromDateStr); d.setHours(0,0,0,0);
                filteredHistory = filteredHistory.filter(h => h.date >= d);
            }
            if (toDateStr) {
                let d = new Date(toDateStr); d.setHours(23,59,59,999);
                filteredHistory = filteredHistory.filter(h => h.date <= d);
            }

            let sumIn = 0;
            let sumOut = 0;
            let sumOpen = 0;

            if (filteredHistory.length > 0) {
                const firstFilteredIndex = history.findIndex(h => h.date >= new Date(fromDateStr || '1970-01-01'));
                if (firstFilteredIndex > 0) {
                    sumOpen = history[firstFilteredIndex - 1].runningBalance;
                }
            }

            const itemName = item ? item.name : 'الصنف';
            const itemCode = itemId;
            const itemCat = item ? (item.category || 'عام') : '-';

            // Company info
            const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            const companyName = cfg.companyName || user.company_name || localStorage.getItem('companyName') || 'مؤسسة مقاييس للأعمال';
            const companyPhone = cfg.phone || cfg.whatsapp || user.phone || '—';
            const companyAddress = cfg.address || user.address || '—';
            const companyReg = cfg.regNumber || user.reg_number || '';
            const companyTax = cfg.taxNumber || user.tax_number || user.vat_number || '';
            const logoUrl = cfg.logoBase64 || localStorage.getItem('company_logo_base64') || user.logo || user.logo_url || '';
            const printedAt = new Date().toLocaleString('ar-SD-u-nu-latn');
            const userName = user.fullName || user.username || 'المسؤول';

            // Units display pill
            const unitsPillsHtml = activeUnits.map((u, i) => `<span style="background:rgba(56,189,248,0.12); color:#0284c7; border:1px solid rgba(56,189,248,0.3); padding:2px 8px; border-radius:6px; font-weight:800; font-size:0.78rem;">الوحدة ${i+1}: ${u.name} (معامل: ${u.factor})</span>`).join(' ');

            // KPI multi-unit text
            function getKpiText(val) {
                return activeUnits.map(u => `<strong>${formatQtyInUnit(val, u)}</strong> <small>${u.name}</small>`).join(' | ');
            }

            // Headers
            let tableHeaderHtml = '';
            const baseUnitName = activeUnits.length > 0 ? activeUnits[0].name : 'وحدة';
            if (activeUnits.length === 1) {
                tableHeaderHtml = `
                    <tr class="ledger-sheet-thead-row" style="background:#1e3a8a; color:#fff; font-weight:800; font-size:0.82rem;">
                        <th class="col-date" style="padding:10px; border:1px solid #94a3b8; text-align:center; width:95px;">التاريخ</th>
                        <th class="col-type" style="padding:10px; border:1px solid #94a3b8; text-align:center; width:110px;">نوع الحركة</th>
                        <th class="col-permit" style="padding:10px; border:1px solid #94a3b8; text-align:center; width:110px;">رقم الإذن</th>
                        <th class="col-receipt" style="padding:10px; border:1px solid #94a3b8; text-align:center; width:110px;">رقم الاستلام</th>
                        <th class="col-in" style="padding:10px; border:1px solid #94a3b8; text-align:right;">الوارد</th>
                        <th class="col-out" style="padding:10px; border:1px solid #94a3b8; text-align:right;">المنصرف</th>
                        <th class="col-balance" style="padding:10px; border:1px solid #94a3b8; text-align:right;">الرصيد (${baseUnitName})</th>
                        <th class="col-notes" style="padding:10px; border:1px solid #94a3b8; text-align:right;">ملحوظة الحركة</th>
                    </tr>
                `;
            } else {
                let uHeadersBal = activeUnits.map(u => `<th class="col-balance" style="padding:8px; border:1px solid #94a3b8; text-align:right; font-size:0.75rem;">${u.name}</th>`).join('');
                tableHeaderHtml = `
                    <tr class="ledger-sheet-thead-row" style="background:#1e3a8a; color:#fff; font-weight:800; font-size:0.82rem;">
                        <th rowspan="2" class="col-date" style="padding:10px; border:1px solid #94a3b8; text-align:center; vertical-align:middle; width:95px;">التاريخ</th>
                        <th rowspan="2" class="col-type" style="padding:10px; border:1px solid #94a3b8; text-align:center; vertical-align:middle; width:110px;">نوع الحركة</th>
                        <th rowspan="2" class="col-permit" style="padding:10px; border:1px solid #94a3b8; text-align:center; vertical-align:middle; width:110px;">رقم الإذن</th>
                        <th rowspan="2" class="col-receipt" style="padding:10px; border:1px solid #94a3b8; text-align:center; vertical-align:middle; width:110px;">رقم الاستلام</th>
                        <th rowspan="2" class="col-in" style="padding:10px; border:1px solid #94a3b8; text-align:right; vertical-align:middle;">الوارد</th>
                        <th rowspan="2" class="col-out" style="padding:10px; border:1px solid #94a3b8; text-align:right; vertical-align:middle;">المنصرف</th>
                        <th colspan="${activeUnits.length}" class="col-balance" style="padding:8px; border:1px solid #94a3b8; text-align:center;">الرصيد التراكمي</th>
                        <th rowspan="2" class="col-notes" style="padding:10px; border:1px solid #94a3b8; text-align:right; vertical-align:middle;">ملحوظة الحركة</th>
                    </tr>
                    <tr class="ledger-sheet-thead-subrow" style="background:#1e3a8a; color:#fff; font-weight:800;">
                        ${uHeadersBal}
                    </tr>
                `;
            }

            // Table Rows HTML
            let rowsHtml = '';
            if (filteredHistory.length === 0) {
                rowsHtml = `<tr><td colspan="${7 + activeUnits.length}" style="text-align:center; padding:35px; color:#64748b; font-size:1.05rem;">لا توجد حركات مسجلة لهذا الصنف في هذه الفترة</td></tr>`;
            } else {
                filteredHistory.forEach((row) => {
                    if (row.typeCode === 'in') sumIn += row.in;
                    if (row.typeCode === 'out') sumOut += row.out;

                    const dStr = row.date.toISOString().split('T')[0];
                    const permitDisplay = (row.permitNo && row.permitNo !== '-') ? row.permitNo : '-';
                    const receiptDisplay = row.ref || row.receiptNo || '-';
                    const noteDisplay = (row.notes && row.notes !== '-' && row.notes.trim()) ? row.notes : '-';

                    const baseUnitObj = activeUnits[0] || { name: 'وحدة', factor: 1 };
                    const allUnits = (activeUnits.length > 1) ? activeUnits : getItemUnitsList(item);
                    const otherUnits = allUnits.filter(u => u.name !== baseUnitObj.name && u.factor > 0 );

                    let inDisplay = '-';
                    if (row.in > 0) {
                        const inBase = formatQtyInUnit(row.in, baseUnitObj);
                        if (row.origUnit && !row.origIsSameAsBase && Math.abs(row.origQty || 0) > 0) {
                            inDisplay = `<b>${fmtNum(Math.abs(row.origQty))} ${row.origUnit}</b> <small style="color:#64748b;">(= ${inBase} ${baseUnitObj.name})</small>`;
                        } else if (otherUnits.length > 0) {
                            const subText = otherUnits.map(u => `${formatQtyInUnit(row.in, u)} ${u.name}`).join(' | ');
                            inDisplay = `<b>${inBase} ${baseUnitObj.name}</b> <small style="color:#0284c7;">(= ${subText})</small>`;
                        } else {
                            inDisplay = `${inBase} ${baseUnitObj.name}`;
                        }
                    }

                    let outDisplay = '-';
                    if (row.out > 0) {
                        const outBase = formatQtyInUnit(row.out, baseUnitObj);
                        if (row.origUnit && !row.origIsSameAsBase && Math.abs(row.origQty || 0) > 0) {
                            outDisplay = `<b>${fmtNum(Math.abs(row.origQty))} ${row.origUnit}</b> <small style="color:#64748b;">(= ${outBase} ${baseUnitObj.name})</small>`;
                        } else if (otherUnits.length > 0) {
                            const subText = otherUnits.map(u => `${formatQtyInUnit(row.out, u)} ${u.name}`).join(' | ');
                            outDisplay = `<b>${outBase} ${baseUnitObj.name}</b> <small style="color:#be185d;">(= ${subText})</small>`;
                        } else {
                            outDisplay = `${outBase} ${baseUnitObj.name}`;
                        }
                    }

                    let balCells = '';
                    if (activeUnits.length === 1 && otherUnits.length > 0) {
                        const subBal = otherUnits.map(u => `${formatQtyInUnit(row.runningBalance, u)} ${u.name}`).join(' | ');
                        balCells = `<td class="col-balance" style="text-align:right; font-weight:900; color:#b45309; background:rgba(245,158,11,0.04);"><b>${formatQtyInUnit(row.runningBalance, baseUnitObj)} ${baseUnitObj.name}</b> <small style="font-size:0.75rem; color:#64748b;">(= ${subBal})</small></td>`;
                    } else {
                        balCells = activeUnits.map(u => `<td class="col-balance" style="text-align:right; font-weight:900; color:#b45309; background:rgba(245,158,11,0.04);">${formatQtyInUnit(row.runningBalance, u)} <small style="font-size:0.75rem; color:#64748b;">${u.name}</small></td>`).join('');
                    }

                    rowsHtml += `
                        <tr class="ledger-sheet-row">
                            <td class="col-date" style="font-weight:600; text-align:center;">${dStr}</td>
                            <td class="col-type" style="text-align:center;"><span class="sheet-badge badge-${row.typeCode}">${row.typeName}</span></td>
                            <td class="col-permit" style="font-weight:700; text-align:center; color:#0369a1;">📄 ${permitDisplay}</td>
                            <td class="col-receipt" style="font-weight:700; text-align:center; color:#0f172a;">${receiptDisplay}</td>
                            <td class="col-in" style="text-align:right; font-weight:700; color:#047857; background:rgba(16,185,129,0.03);">${inDisplay}</td>
                            <td class="col-out" style="text-align:right; font-weight:700; color:#be185d; background:rgba(236,72,153,0.03);">${outDisplay}</td>
                            ${balCells}
                            <td class="col-notes" style="text-align:right; color:#334155; font-size:0.88rem;">${noteDisplay !== '-' ? '📝 ' + noteDisplay : '-'}</td>
                        </tr>
                    `;
                });
            }

            // Footer Multi-unit Totals
            let footerInCells = `<td class="col-in" style="padding:10px; text-align:right; color:#047857; border:1px solid #94a3b8; font-weight:900;">${getKpiText(sumIn)}</td>`;
            let footerOutCells = `<td class="col-out" style="padding:10px; text-align:right; color:#be185d; border:1px solid #94a3b8; font-weight:900;">${getKpiText(sumOut)}</td>`;
            let footerBalCells = activeUnits.map(u => `<td class="col-balance" style="padding:10px; text-align:right; color:#b45309; border:1px solid #94a3b8; font-size:1.05rem; font-weight:900;">${formatQtyInUnit(actualCurrentStock, u)} <small>${u.name}</small></td>`).join('');

            // Assemble Full Sheet HTML
            const sheetHtml = `
                <!-- 1. Header Section -->
                <div id="sheet-header-block" style="display:flex; justify-content:space-between; align-items:center; border-bottom:3px double #0f172a; padding-bottom:16px; margin-bottom:20px; direction:rtl;">
                    <div style="display:flex; align-items:center; gap:14px; width:35%;">
                        ${logoUrl ? `<img src="${logoUrl}" style="max-height:65px; max-width:90px; object-fit:contain; border-radius:8px;">` : ''}
                        <div>
                            <h2 style="margin:0; font-size:1.35rem; font-weight:900; color:#0f172a;" class="editable-text">${companyName}</h2>
                            <div style="font-size:0.75rem; color:#475569; margin-top:3px;">
                                ${companyReg ? `<span>س.ت: ${companyReg}</span> · ` : ''}
                                ${companyTax ? `<span>ر.ض: ${companyTax}</span>` : ''}
                            </div>
                            <div style="font-size:0.75rem; color:#475569;">${companyAddress} · هاتف: ${companyPhone}</div>
                        </div>
                    </div>

                    <div style="text-align:center; width:30%;">
                        <div class="sheet-title-box" style="border:2px solid #0f172a; padding:8px 20px; border-radius:8px; background:#f8fafc; display:inline-block;">
                            <h3 style="margin:0; font-size:1.2rem; font-weight:900; color:#0f172a; white-space:nowrap;" class="editable-text">📑 كشف حركة الصنف التفصيلي</h3>
                            <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">(كارت الصنف والمستودع)</div>
                        </div>
                    </div>

                    <div style="text-align:left; width:35%; font-size:0.78rem; color:#475569; line-height:1.6;">
                        <div>تاريخ التقرير: <strong>${printedAt}</strong></div>
                        <div>المستخدم / الطابع: <strong>${userName}</strong></div>
                        <div>الفرع / المستودع: <strong>${whName}</strong></div>
                    </div>
                </div>

                <!-- 2. Item & Filter Metadata Card -->
                <div id="sheet-meta-block" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:12px 18px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; font-size:0.85rem;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:1.2rem;">📦</span>
                        <div>
                            <span style="color:#64748b; font-size:0.75rem;">اسم وكود الصنف:</span><br>
                            <strong style="color:#0f172a; font-size:1rem;">${itemName}</strong>
                        </div>
                    </div>
                    <div>
                        <span style="color:#64748b; font-size:0.75rem;">التصنيف:</span><br>
                        <strong style="color:#0f172a;">${itemCat}</strong>
                    </div>
                    <div>
                        <span style="color:#64748b; font-size:0.75rem;">الوحدات المعتمدة في التقرير:</span><br>
                        <div style="margin-top:2px;">${unitsPillsHtml}</div>
                    </div>
                    <div>
                        <span style="color:#64748b; font-size:0.75rem;">الفترة المحددة:</span><br>
                        <strong style="color:#0f172a;">${periodStr}</strong>
                    </div>
                </div>

                <!-- 3. KPI Balance Cards -->
                <div id="sheet-kpis-block" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px; margin-bottom:20px;">
                    <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:10px 14px; text-align:center;">
                        <div style="font-size:0.75rem; color:#0369a1; font-weight:700;">الرصيد الافتتاحي (أول المدة) 📋</div>
                        <div style="font-size:1.05rem; font-weight:900; color:#0284c7; margin-top:4px;">${getKpiText(sumOpen)}</div>
                    </div>
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; text-align:center;">
                        <div style="font-size:0.75rem; color:#15803d; font-weight:700;">إجمالي الوارد (مشتريات/استلام) ⚖️</div>
                        <div style="font-size:1.05rem; font-weight:900; color:#16a34a; margin-top:4px;">${getKpiText(sumIn)}</div>
                    </div>
                    <div style="background:#fdf2f8; border:1px solid #fbcfe8; border-radius:8px; padding:10px 14px; text-align:center;">
                        <div style="font-size:0.75rem; color:#be185d; font-weight:700;">إجمالي المنصرف (مبيعات/تسليم) 💰</div>
                        <div style="font-size:1.05rem; font-weight:900; color:#db2777; margin-top:4px;">${getKpiText(sumOut)}</div>
                    </div>
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; text-align:center;">
                        <div style="font-size:0.75rem; color:#b45309; font-weight:700;">الرصيد الفعلي الحالي ⚠️</div>
                        <div style="font-size:1.05rem; font-weight:900; color:#d97706; margin-top:4px;">${getKpiText(actualCurrentStock)}</div>
                    </div>
                </div>

                <!-- 4. Detailed Movement Table -->
                <div id="sheet-table-block" style="margin-bottom:25px;">
                    <table class="ledger-sheet-table" style="width:100%; border-collapse:collapse; text-align:right; font-size:0.85rem; direction:rtl;">
                        <thead>
                            ${tableHeaderHtml}
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc; font-weight:900; border-top:2px solid #0f172a;">
                                <td colspan="4" style="padding:10px; text-align:left; border:1px solid #94a3b8;">إجمالي حركة الفترة والرصيد الختامي:</td>
                                ${footerInCells}
                                ${footerOutCells}
                                ${footerBalCells}
                                <td class="col-notes" style="border:1px solid #94a3b8;">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 5. Signatures Section -->
                <div id="sheet-signatures-block" style="margin-top:35px; border-top:1px dashed #cbd5e1; padding-top:20px; display:grid; grid-template-columns: repeat(4, 1fr); gap:15px; text-align:center; font-size:0.82rem; color:#334155;">
                    <div style="border:1px solid #e2e8f0; border-radius:6px; padding:10px; background:#fafafa;">
                        <strong style="color:#0f172a;">✍️ أمين المستودع</strong>
                        <div style="margin-top:28px; border-top:1px solid #cbd5e1; padding-top:4px;">الاسم والتوقيع</div>
                    </div>
                    <div style="border:1px solid #e2e8f0; border-radius:6px; padding:10px; background:#fafafa;">
                        <strong style="color:#0f172a;">🚚 المستلم / المندوب</strong>
                        <div style="margin-top:28px; border-top:1px solid #cbd5e1; padding-top:4px;">الاسم والتوقيع</div>
                    </div>
                    <div style="border:1px solid #e2e8f0; border-radius:6px; padding:10px; background:#fafafa;">
                        <strong style="color:#0f172a;">📊 المحاسب المختص</strong>
                        <div style="margin-top:28px; border-top:1px solid #cbd5e1; padding-top:4px;">الاسم والتوقيع</div>
                    </div>
                    <div style="border:1px solid #e2e8f0; border-radius:6px; padding:10px; background:#fafafa;">
                        <strong style="color:#0f172a;">🏢 اعتماد الإدارة والختم</strong>
                        <div style="margin-top:28px; border-top:1px solid #cbd5e1; padding-top:4px;">الختم الرسمي</div>
                    </div>
                </div>

                <!-- 6. Footer Disclaimer -->
                <div style="margin-top:25px; border-top:1px solid #e2e8f0; padding-top:8px; display:flex; justify-content:space-between; font-size:0.72rem; color:#94a3b8; direction:rtl;">
                    <div>تم استخراج وطباعة هذا التقرير تلقائياً عبر نظام مقاييس ERP المتكامل · MAGAIEES CLOUD</div>
                    <div>صفحة 1 من 1</div>
                </div>
            `;

            const sheet = document.getElementById('item-ledger-sheet');
            sheet.innerHTML = sheetHtml;

            // Load Saved or Default Design
            const design = getSavedDesign();
            loadDesignIntoControls(design);
            applyStudioDesign();

            // Show Modal
            const modal = document.getElementById('item-ledger-preview-modal');
            if (modal) modal.style.display = 'flex';
        }

        function closeItemLedgerPreview() {
            const modal = document.getElementById('item-ledger-preview-modal');
            if (modal) modal.style.display = 'none';
        }

        function applyStudioDesign() {
            const sheet = document.getElementById('item-ledger-sheet');
            if (!sheet) return;

            const themeKey = document.getElementById('studio-theme-select')?.value || 'corporate';
            const theme = THEMES_CONFIG[themeKey] || THEMES_CONFIG.corporate;
            const font = document.getElementById('studio-font-select')?.value || "'Tajawal', sans-serif";
            const size = document.getElementById('studio-size-select')?.value || 'medium';
            const orientation = document.getElementById('studio-orientation-select')?.value || 'landscape';

            const showHeader = document.getElementById('chk-show-header')?.checked;
            const showMeta = document.getElementById('chk-show-meta')?.checked;
            const showKPIs = document.getElementById('chk-show-kpis')?.checked;
            const showSignatures = document.getElementById('chk-show-signatures')?.checked;

            // Apply Font
            sheet.style.fontFamily = font;

            // Apply Size / Density
            if (size === 'compact') {
                sheet.style.fontSize = '0.76rem';
                sheet.querySelectorAll('th, td').forEach(c => c.style.padding = '5px 4px');
            } else if (size === 'large') {
                sheet.style.fontSize = '0.94rem';
                sheet.querySelectorAll('th, td').forEach(c => c.style.padding = '12px 10px');
            } else {
                sheet.style.fontSize = '0.85rem';
                sheet.querySelectorAll('th, td').forEach(c => c.style.padding = '8px 6px');
            }

            // Apply Orientation Style
            if (orientation === 'portrait') {
                sheet.style.maxWidth = '850px';
            } else {
                sheet.style.maxWidth = '1180px';
            }

            // Update Dynamic Print Page Size CSS
            const printStyle = document.getElementById('item-ledger-print-style');
            if (printStyle) {
                printStyle.innerHTML = `
                    #item-ledger-preview-modal.print-direct-mode { display: none !important; }
                    @page { size: ${orientation}; margin: 8mm; }
                    @media print {
                        #item-ledger-preview-modal.print-direct-mode { display: block !important; }
                        body > *:not(#item-ledger-preview-modal) { display: none !important; }
                        #item-ledger-preview-modal { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; height: auto !important; background: #fff !important; padding: 0 !important; margin: 0 !important; display: block !important; overflow: visible !important; }
                        #item-ledger-preview-modal .glass-panel { width: 100% !important; max-width: 100% !important; height: auto !important; border: none !important; background: #fff !important; padding: 0 !important; box-shadow: none !important; margin: 0 !important; }
                        .global-sidebar, .no-print, .btn-action, .dash-controls { display: none !important; }
                        #item-ledger-preview-modal .preview-scroll-container { background: #fff !important; padding: 0 !important; overflow: visible !important; }
                        #item-ledger-sheet { box-shadow: none !important; padding: 0 !important; margin: 0 !important; border: none !important; width: 100% !important; max-width: 100% !important; }
                        #item-ledger-sheet table { width: 100% !important; border-collapse: collapse !important; table-layout: auto !important; }
                        #item-ledger-sheet th, #item-ledger-sheet td { border: 1px solid #94a3b8 !important; page-break-inside: avoid; }
                    }
                `;
            }

            // Apply Theme Palette to Header & Table Elements
            const titleBox = sheet.querySelector('.sheet-title-box');
            if (titleBox) {
                titleBox.style.borderColor = theme.primary;
                titleBox.style.background = theme.headerBg;
                const h3 = titleBox.querySelector('h3');
                if (h3) h3.style.color = theme.primary;
            }

            const theadRows = sheet.querySelectorAll('.ledger-sheet-thead-row th, .ledger-sheet-thead-subrow th');
            theadRows.forEach(th => {
                th.style.background = theme.primary;
                th.style.color = '#ffffff';
                th.style.borderColor = theme.border;
            });

            const badges = sheet.querySelectorAll('.sheet-badge');
            badges.forEach(b => {
                b.style.padding = '3px 8px';
                b.style.borderRadius = '4px';
                b.style.fontSize = '0.78rem';
                b.style.fontWeight = '700';
                b.style.display = 'inline-block';
                if (b.classList.contains('badge-in')) {
                    b.style.background = '#dcfce7';
                    b.style.color = '#15803d';
                    b.style.border = '1px solid #86efac';
                } else if (b.classList.contains('badge-out')) {
                    b.style.background = '#fce7f3';
                    b.style.color = '#be185d';
                    b.style.border = '1px solid #f9a8d4';
                } else {
                    b.style.background = theme.bgBadge;
                    b.style.color = theme.textBadge;
                    b.style.border = `1px solid ${theme.border}`;
                }
            });

            // Toggle Sections
            const hBlock = document.getElementById('sheet-header-block');
            if (hBlock) hBlock.style.display = showHeader ? 'flex' : 'none';

            const mBlock = document.getElementById('sheet-meta-block');
            if (mBlock) mBlock.style.display = showMeta ? 'flex' : 'none';

            const kBlock = document.getElementById('sheet-kpis-block');
            if (kBlock) kBlock.style.display = showKPIs ? 'grid' : 'none';

            const sBlock = document.getElementById('sheet-signatures-block');
            if (sBlock) sBlock.style.display = showSignatures ? 'grid' : 'none';

            // Toggle Columns
            const colToggles = document.querySelectorAll('.col-toggle');
            colToggles.forEach(chk => {
                const colKey = chk.getAttribute('data-col');
                const isChecked = chk.checked;
                sheet.querySelectorAll(`.col-${colKey}`).forEach(el => {
                    el.style.display = isChecked ? '' : 'none';
                });
            });
        }

        let currentSelectedLedgerCol = 'date';
        function selectItemLedgerCol(colKey) {
            currentSelectedLedgerCol = colKey;
            const sheet = document.getElementById('item-ledger-sheet');
            if (!sheet) return;
            const firstCell = sheet.querySelector(`.col-${colKey}`);
            const isVis = firstCell ? (firstCell.style.display !== 'none') : true;
            const chk = document.getElementById('item-col-visible-chk');
            if (chk) chk.checked = isVis;

            const headerCell = sheet.querySelector(`th.col-${colKey}`);
            const lblInput = document.getElementById('item-col-label-input');
            if (lblInput && headerCell) lblInput.value = headerCell.innerText.trim();
        }

        function updateSelectedItemLedgerCol(prop, val) {
            const sheet = document.getElementById('item-ledger-sheet');
            if (!sheet) return;
            if (prop === 'visible') {
                sheet.querySelectorAll(`.col-${currentSelectedLedgerCol}`).forEach(el => {
                    el.style.display = val ? '' : 'none';
                });
            } else if (prop === 'label') {
                const headerCell = sheet.querySelector(`th.col-${currentSelectedLedgerCol}`);
                if (headerCell) headerCell.innerText = val;
            } else if (prop === 'align') {
                sheet.querySelectorAll(`.col-${currentSelectedLedgerCol}`).forEach(el => {
                    el.style.textAlign = val;
                });
            } else if (prop === 'width') {
                sheet.querySelectorAll(`.col-${currentSelectedLedgerCol}`).forEach(el => {
                    el.style.width = val ? (val + '%') : 'auto';
                });
            }
        }

        function setItemTableWidth(val) {
            const sheet = document.getElementById('item-ledger-sheet');
            const tbl = sheet ? sheet.querySelector('table') : null;
            const lbl = document.getElementById('item-table-width-label');
            if (tbl) tbl.style.width = val + '%';
            if (lbl) lbl.textContent = val + '%';
        }

        function printItemLedgerSheet() {
            if (isLiveEditActive) {
                toggleLiveEditMode();
            }
            setTimeout(() => {
                window.print();
            }, 100);
        }

        function printItemLedgerDirectly() {
            openItemLedgerPreview();
            const modal = document.getElementById('item-ledger-preview-modal');
            if (modal) modal.classList.add('print-direct-mode');

            const cleanup = () => {
                if (modal) modal.classList.remove('print-direct-mode');
                closeItemLedgerPreview();
                window.removeEventListener('afterprint', cleanup);
            };
            window.addEventListener('afterprint', cleanup);

            setTimeout(() => {
                window.print();
            }, 200);
        }
    </script>
</body>
</html>


