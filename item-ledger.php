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
        <div class="filters-bar" style="grid-template-columns: 1.3fr 1.5fr 1.1fr 1fr 1fr; align-items: end; gap: 12px; padding: 14px 18px; margin-bottom: 6px;">
            <div class="input-group">
                <label style="display: flex; justify-content: space-between; align-items: center;">
                    <span>1. اختر الصنف المراد جرده</span>
                    <span id="item-count-badge" style="font-size: 0.72rem; color: #38bdf8; font-weight: 800;">(0 صنف)</span>
                </label>
                <div style="display: flex; gap: 6px;">
                    <div style="position: relative; flex: 1;">
                        <select id="item-select" onchange="onItemSelected()" style="width: 100%; font-weight: 800; color: #38bdf8; border-color: rgba(56,189,248,0.4); padding: 9px 12px; font-size: 0.88rem;">
                            <option value="">-- يرجى اختيار صنف --</option>
                        </select>
                    </div>
                    <input type="text" id="item-quick-filter" placeholder="🔍 بحث..." oninput="filterItemDropdown(this.value)" style="width: 80px; padding: 8px 8px; font-size: 0.8rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.3); color: #fff;" title="اكتب لفلترة قائمة الأصناف سريعاً">
                </div>
            </div>
            <div class="input-group">
                <label style="color:#38bdf8; display:flex; justify-content:space-between;">
                    <span>2. وحدات التقرير (حتى 4 وحدات)</span>
                    <span style="font-size:0.7rem; opacity:0.8;">⚖️ متعدد الوحدات</span>
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 5px;">
                    <select id="unit-select-1" onfocus="onUnit1Focus()" onchange="onUnit1Change()" title="الوحدة الأولى (الأساسية)" style="padding: 8px 4px; font-size: 0.78rem; font-weight: 700; border-color:rgba(56,189,248,0.3);">
                        <option value="">الوحدة 1 (الأساسية)</option>
                    </select>
                    <select id="unit-select-2" onfocus="onUnit2Focus()" onchange="onUnit2Change()" title="الوحدة الثانية (اختياري)" style="padding: 8px 4px; font-size: 0.78rem; font-weight: 700; border-color:rgba(56,189,248,0.2);">
                        <option value="">الوحدة 2 (اختياري)</option>
                    </select>
                    <select id="unit-select-3" onfocus="onUnit3Focus()" onchange="onUnit3Change()" title="الوحدة الثالثة (اختياري)" style="padding: 8px 4px; font-size: 0.78rem; font-weight: 700; border-color:rgba(56,189,248,0.2);">
                        <option value="">الوحدة 3 (اختياري)</option>
                    </select>
                    <select id="unit-select-4" onfocus="onUnit4Focus()" onchange="onUnit4Change()" title="الوحدة الرابعة (اختياري)" style="padding: 8px 4px; font-size: 0.78rem; font-weight: 700; border-color:rgba(56,189,248,0.2);">
                        <option value="">الوحدة 4 (اختياري)</option>
                    </select>
                </div>
            </div>
            <div class="input-group">
                <label>3. المخزن (اختياري)</label>
                <select id="warehouse-select" onchange="loadLedger()" style="padding: 9px 12px; font-size: 0.88rem;">
                    <option value="ALL">🏢 جميع المخازن</option>
                </select>
            </div>
            <div class="input-group">
                <label>4. من تاريخ</label>
                <input type="date" id="date-from" onchange="loadLedger()">
            </div>
            <div class="input-group">
                <label>5. إلى تاريخ</label>
                <input type="date" id="date-to" onchange="loadLedger()">
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
                        if ((line.itemId === item.id || line.productId === item.id) && line.unit) {
                            const uName = safeString(line.unit);
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
                            const uName = safeString(line.unit);
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
            const u1 = document.getElementById('unit-select-1');
            const u2 = document.getElementById('unit-select-2');
            const u3 = document.getElementById('unit-select-3');
            const u4 = document.getElementById('unit-select-4');

            if (!item) {
                if (u1) u1.innerHTML = '<option value="">الوحدة 1 (الأساسية)</option>';
                if (u2) u2.innerHTML = '<option value="">(بدون وحدة 2)</option>';
                if (u3) u3.innerHTML = '<option value="">(بدون وحدة 3)</option>';
                if (u4) u4.innerHTML = '<option value="">(بدون وحدة 4)</option>';
                loadLedger();
                return;
            }

            const units = getItemUnitsList(item);

            // 1. Populate Unit 1 with all units
            if (u1) {
                u1.innerHTML = '';
                units.forEach((u) => {
                    const opt = document.createElement('option');
                    opt.value = u.name;
                    opt.text = u.name;
                    u1.appendChild(opt);
                });

                // Default Unit 1 to the first unit (base unit)
                if (units.length > 0) {
                    u1.value = units[0].name;
                }
            }

            // 2. Cascade down to populate Unit 2, Unit 3 & Unit 4
            refreshUnit2Dropdown(units, true);
            refreshUnit3Dropdown(units, true);
            refreshUnit4Dropdown(units, true);

            loadLedger();
        }

        function onUnit1Focus() {
            const item = getSelectedItemObj();
            if (!item) return;
            const units = getItemUnitsList(item);
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
            refreshUnit2Dropdown(units, false);
        }

        function onUnit3Focus() {
            const item = getSelectedItemObj();
            if (!item) return;
            const units = getItemUnitsList(item);
            refreshUnit3Dropdown(units, false);
        }

        function onUnit4Focus() {
            const item = getSelectedItemObj();
            if (!item) return;
            const units = getItemUnitsList(item);
            refreshUnit4Dropdown(units, false);
        }

        function onUnit1Change() {
            const item = getSelectedItemObj();
            const units = getItemUnitsList(item);

            refreshUnit2Dropdown(units, true);
            refreshUnit3Dropdown(units, true);
            refreshUnit4Dropdown(units, true);
            loadLedger();
        }

        function onUnit2Change() {
            const item = getSelectedItemObj();
            const units = getItemUnitsList(item);

            refreshUnit3Dropdown(units, true);
            refreshUnit4Dropdown(units, true);
            loadLedger();
        }

        function onUnit3Change() {
            const item = getSelectedItemObj();
            const units = getItemUnitsList(item);

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
            const unitsList = getItemUnitsList(item);
            const u1Val = document.getElementById('unit-select-1')?.value;
            const u2Val = document.getElementById('unit-select-2')?.value;
            const u3Val = document.getElementById('unit-select-3')?.value;
            const u4Val = document.getElementById('unit-select-4')?.value;

            let selected = [];
            if (u1Val) {
                const obj = unitsList.find(u => u.name === u1Val) || { name: u1Val, factor: 1 };
                selected.push(obj);
            }
            if (u2Val && u2Val !== u1Val) {
                const obj = unitsList.find(u => u.name === u2Val) || { name: u2Val, factor: 1 };
                selected.push(obj);
            }
            if (u3Val && u3Val !== u1Val && u3Val !== u2Val) {
                const obj = unitsList.find(u => u.name === u3Val) || { name: u3Val, factor: 1 };
                selected.push(obj);
            }
            if (u4Val && u4Val !== u1Val && u4Val !== u2Val && u4Val !== u3Val) {
                const obj = unitsList.find(u => u.name === u4Val) || { name: u4Val, factor: 1 };
                selected.push(obj);
            }

            if (selected.length === 0) {
                selected.push(unitsList[0] || { name: 'وحدة', factor: 1 });
            }
            return selected.slice(0, 4); // Up to 4 units
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
                    opt.text = `[${code}] ${name}${cat ? ' (' + cat + ')' : ''}`;
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
                        const origUnit   = safeString(line.unit || line.selectedUnit || itemBaseUnit || '');
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
                        const origUnit   = safeString(line.unit || line.selectedUnit || itemBaseUnit || '');
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
                        const origUnit = safeString(line.unitName || line.unit || itemBaseUnit || '');
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
                        <th style="color:#10b981; background:rgba(16,185,129,0.07); text-align:right; white-space:nowrap;">
                            الوارد<br><span style="font-size:0.75rem; font-weight:600; opacity:0.75;">(بوحدة الحركة)</span>
                        </th>
                        <th style="color:#ec4899; background:rgba(236,72,153,0.07); text-align:right; white-space:nowrap;">
                            المنصرف<br><span style="font-size:0.75rem; font-weight:600; opacity:0.75;">(بوحدة الحركة)</span>
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
            const allUnits = (activeUnits && activeUnits.length > 1) ? activeUnits : getItemUnitsList(item);

            function formatKpiDisplay(val, units) {
                if (!units || units.length === 0) return '0';
                const main = `<span style="font-size:1.25rem; font-weight:900;">${formatQtyInUnit(val, units[0])}</span> <span style="font-size:0.85rem; font-weight:700; opacity:0.85;">${units[0].name}</span>`;
                const otherUnits = units.filter((u, i) => i > 0 && u.factor > 0 && u.factor !== units[0].factor);
                if (otherUnits.length > 0) {
                    const subs = otherUnits.map(u => `<strong style="color:#38bdf8; font-weight:800;">${formatQtyInUnit(val, u)}</strong> <small style="opacity:0.85;">${u.name}</small>`).join(' <span style="opacity:0.4;">|</span> ');
                    return `<div style="display:flex; flex-direction:column; align-items:baseline;"><div>${main}</div><div style="font-size:0.78rem; font-weight:800; opacity:0.9; margin-top:4px; color:#38bdf8; font-family:monospace; background:rgba(255,255,255,0.05); padding:2px 8px; border-radius:6px; border:1px solid rgba(255,255,255,0.08);">${subs}</div></div>`;
                }
                return main;
            }

            document.getElementById('sum-open').innerHTML = formatKpiDisplay(sumOpen, allUnits);
            document.getElementById('sum-in').innerHTML = formatKpiDisplay(sumIn, allUnits);
            document.getElementById('sum-out').innerHTML = formatKpiDisplay(sumOut, allUnits);
            document.getElementById('sum-balance').innerHTML = formatKpiDisplay(actualCurrentStock, allUnits);
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

                function buildOrigQtyCell(qty, direction) {
                    const isIn = direction === 'in';
                    if ((isIn && row.in <= 0) || (!isIn && row.out <= 0)) {
                        return `<td style="${isIn ? 'color:#10b981' : 'color:#ec4899'}; font-weight:700; background:${isIn ? 'rgba(16,185,129,0.02)' : 'rgba(236,72,153,0.02)'}; text-align:right;">-</td>`;
                    }

                    const baseQty     = isIn ? row.in : row.out;
                    const origQtyAbs  = Math.abs(row.origQty || 0);
                    const origUnit    = row.origUnit || '';
                    const isSameBase  = row.origIsSameAsBase;
                    const baseUnitObj = activeUnits[0] || { name: 'وحدة', factor: 1 };
                    const baseFormatted = formatQtyInUnit(baseQty, baseUnitObj);

                    // Determine other available units to calculate conversion equivalent
                    const allUnits = (activeUnits.length > 1) ? activeUnits : getItemUnitsList(item);
                    const otherUnits = allUnits.filter(u => u.name !== baseUnitObj.name && u.factor > 0 && u.factor !== 1);

                    let html = '';
                    if (origUnit && !isSameBase && origQtyAbs > 0) {
                        // Movement came in non-base unit (e.g. 5 طن)
                        let subText = `(= ${baseFormatted} ${baseUnitObj.name})`;
                        const extraUnits = otherUnits.filter(u => u.name !== origUnit);
                        if (extraUnits.length > 0) {
                            const extraFormatted = extraUnits.map(u => `${formatQtyInUnit(baseQty, u)} ${u.name}`).join(' | ');
                            subText += ` · ${extraFormatted}`;
                        }

                        html = `
                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:3px;">
                                <div style="display:inline-flex; align-items:baseline; gap:4px;">
                                    <span style="font-size:1.05rem; font-weight:900; letter-spacing:-0.3px; color:${isIn ? '#10b981' : '#ec4899'};">${fmtNum(origQtyAbs)}</span>
                                    <span style="font-size:0.82rem; font-weight:800; color:${isIn ? '#34d399' : '#f472b6'}; background:${isIn ? 'rgba(16,185,129,0.12)' : 'rgba(236,72,153,0.12)'}; padding:1px 6px; border-radius:4px;">${origUnit}</span>
                                </div>
                                <div style="font-size:0.75rem; color:#94a3b8; font-family:monospace; background:rgba(255,255,255,0.04); padding:2px 8px; border-radius:5px; border:1px solid rgba(255,255,255,0.07);">
                                    ${subText}
                                </div>
                            </div>`;
                    } else {
                        // Movement came in base unit (e.g. 667 سيخة)
                        let subHtml = '';
                        if (otherUnits.length > 0) {
                            const subs = otherUnits.map(u => `<span style="color:#38bdf8; font-weight:800;">${formatQtyInUnit(baseQty, u)}</span> <span style="opacity:0.8;">${u.name}</span>`).join(' <span style="opacity:0.4;">|</span> ');
                            subHtml = `
                                <div style="font-size:0.75rem; color:#bae6fd; font-family:monospace; background:rgba(56,189,248,0.08); padding:2px 8px; border-radius:5px; border:1px solid rgba(56,189,248,0.2);">
                                    (= ${subs})
                                </div>`;
                        }

                        html = `
                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:3px;">
                                <div style="display:inline-flex; align-items:baseline; gap:4px;">
                                    <span style="font-size:1.05rem; font-weight:900; letter-spacing:-0.3px; color:${isIn ? '#10b981' : '#ec4899'};">${baseFormatted}</span>
                                    <span style="font-size:0.82rem; font-weight:800; opacity:0.85;">${baseUnitObj.name}</span>
                                </div>
                                ${subHtml}
                            </div>`;
                    }

                    return `<td style="font-weight:700; background:${isIn ? 'rgba(16,185,129,0.02)' : 'rgba(236,72,153,0.02)'}; text-align:right; vertical-align:middle;">${html}</td>`;
                }

                const inCell  = buildOrigQtyCell(row.in, 'in');
                const outCell = buildOrigQtyCell(row.out, 'out');

                let balCells = '';
                if (activeUnits.length === 1) {
                    const allUnits = getItemUnitsList(item);
                    const otherUnits = allUnits.filter(u => u.name !== activeUnits[0].name && u.factor > 0 && u.factor !== 1);
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
        // 🎨 ITEM LEDGER PRO STUDIO OPENER (Cloned from General Ledger)
        // ═══════════════════════════════════════════════════════════
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

            let filteredHistory = history.map(h => ({ ...h, date: new Date(h.date) }));
            if (fromDateStr) {
                let d = new Date(fromDateStr); d.setHours(0,0,0,0);
                filteredHistory = filteredHistory.filter(h => h.date >= d);
            }
            if (toDateStr) {
                let d = new Date(toDateStr); d.setHours(23,59,59,999);
                filteredHistory = filteredHistory.filter(h => h.date <= d);
            }

            const payload = {
                item: item,
                history: filteredHistory,
                activeUnits: activeUnits,
                whName: whName,
                fromDateStr: fromDateStr,
                toDateStr: toDateStr,
                actualCurrentStock: running
            };

            localStorage.setItem('preview_item_ledger_payload', JSON.stringify(payload));
            window.open('preview-item-ledger.php', '_blank');
        }

        function printItemLedgerDirectly() {
            openItemLedgerPreview();
        };
            window.addEventListener('afterprint', cleanup);

            setTimeout(() => {
                window.print();
            }, 200);
        }
    </script>
</body>
</html>


