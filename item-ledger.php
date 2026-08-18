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

        /* Nav Tabs */
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
        .nav-tab::before { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); transform: skewX(-20deg); transition: 0.5s; }
        .nav-tab:hover::before { left: 150%; }

        /* Button hover/active colors */
        .nav-tab:nth-child(1):hover { background: rgba(59, 130, 246, 0.1); color: #fff; border-color: rgba(59, 130, 246, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(1).active { background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2)); border-color: rgba(59, 130, 246, 0.5); color: #60a5fa; box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
        .nav-tab:nth-child(2):hover { background: rgba(16, 185, 129, 0.1); color: #fff; border-color: rgba(16, 185, 129, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(2).active { background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2)); border-color: rgba(16, 185, 129, 0.5); color: #34d399; box-shadow: 0 0 20px rgba(16, 185, 129, 0.3); }
        .nav-tab:nth-child(3):hover { background: rgba(245, 158, 11, 0.1); color: #fff; border-color: rgba(245, 158, 11, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(3).active { background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2)); border-color: rgba(245, 158, 11, 0.5); color: #fbbf24; box-shadow: 0 0 20px rgba(245, 158, 11, 0.3); }
        .nav-tab:nth-child(4):hover { background: rgba(139, 92, 246, 0.1); color: #fff; border-color: rgba(139, 92, 246, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(4).active { background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(109, 40, 217, 0.2)); border-color: rgba(139, 92, 246, 0.5); color: #c084fc; box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); }
        .nav-tab:nth-child(5):hover { background: rgba(244, 63, 94, 0.1); color: #fff; border-color: rgba(244, 63, 94, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(5).active { background: linear-gradient(135deg, rgba(244, 63, 94, 0.2), rgba(225, 29, 72, 0.2)); border-color: rgba(244, 63, 94, 0.5); color: #fb7185; box-shadow: 0 0 20px rgba(244, 63, 94, 0.3); }
        .nav-tab:nth-child(6):hover { background: rgba(6, 182, 212, 0.1); color: #fff; border-color: rgba(6, 182, 212, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(6).active { background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(8, 145, 178, 0.2)); border-color: rgba(6, 182, 212, 0.5); color: #22d3ee; box-shadow: 0 0 20px rgba(6, 182, 212, 0.3); }

        /* Light Mode Styling for tabs */
        :root.light-theme .nav-tabs { background: rgba(248, 250, 252, 0.85); border: 1px solid rgba(0,0,0,0.06); box-shadow: inset 0 2px 10px rgba(0,0,0,0.02); }
        :root.light-theme .nav-tab { color: #64748b; background: rgba(255,255,255,0.7); border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        :root.light-theme .nav-tab::before { background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent); }

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

        /* Controls / Buttons */
        .dash-controls { display: flex; align-items: center; gap: 12px; }
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

        <!-- Navigation and Actions Row -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; flex-wrap: wrap; gap: 15px;">
            <div class="nav-tabs" style="margin-bottom: 0;">
                <a data-href="inventory-report.php" onclick="window.location.href='inventory-report.php'" class="nav-tab">📦 جرد الأرصدة العام</a>
                <a data-href="warehouse-inventory.php" onclick="window.location.href='warehouse-inventory.php'" class="nav-tab">🏢 جرد المخزن</a>
                <a data-href="inventory-summary.php" onclick="window.location.href='inventory-summary.php'" class="nav-tab">📊 ملخص الحركات</a>
                <a data-href="inventory-analytics.php" onclick="window.location.href='inventory-analytics.php'" class="nav-tab">🧠 التحليل الاستراتيجي</a>
                <a data-href="item-ledger.php" onclick="window.location.href='item-ledger.php'" class="nav-tab active">📑 حركة الصنف</a>
                <a data-href="warehouse-ledger.php" onclick="window.location.href='warehouse-ledger.php'" class="nav-tab">🏭 حركة المخزن</a>
                <a data-href="ai_goods_advisor.php" onclick="window.location.href='ai_goods_advisor.php'" class="nav-tab">🤖 مستشار الـ AI للبضائع</a>
                <a data-href="smart_inventory_report.php" onclick="window.location.href='smart_inventory_report.php'" class="nav-tab">📊 تقارير المخازن الذكية</a>
            </div>

            <div class="dash-controls" style="margin: 0; display: flex; gap: 8px;">
                <button class="btn-action" onclick="openItemLedgerPreview()" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(13,148,136,0.2);">👁️ معاينة وتصميم التقرير</button>
                <button class="btn-action btn-print" onclick="printItemLedgerDirectly()" style="background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--glass-border); padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer;">🖨️ طباعة</button>
                <button class="btn-icon-nav" onclick="history.back()" title="رجوع" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; color: var(--text-primary); cursor: pointer; transition: 0.3s;">↩</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar" style="grid-template-columns: 1.2fr 1.6fr 1.1fr 1fr 1fr; align-items: end; gap: 15px;">
            <div class="input-group">
                <label>1. اختر الصنف المراد جرده</label>
                <select id="item-select" onchange="onItemSelected()">
                    <option value="">-- يرجى اختيار صنف --</option>
                </select>
            </div>
            <div class="input-group">
                <label style="color:#38bdf8; display:flex; justify-content:space-between;">
                    <span>2. وحدات التقرير (أقصى حد 3 وحدات)</span>
                    <span style="font-size:0.7rem; opacity:0.8;">⚖️ متعدد الوحدات</span>
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                    <select id="unit-select-1" onchange="onUnit1Change()" title="الوحدة الأولى (الرئيسية)" style="padding: 10px 6px; font-size: 0.8rem; font-weight: 700; border-color:rgba(56,189,248,0.3);">
                        <option value="">الوحدة 1 (الأساسية)</option>
                    </select>
                    <select id="unit-select-2" onchange="onUnit2Change()" title="الوحدة الثانية (اختياري)" style="padding: 10px 6px; font-size: 0.8rem; font-weight: 700; border-color:rgba(56,189,248,0.2);">
                        <option value="">الوحدة 2 (اختياري)</option>
                    </select>
                    <select id="unit-select-3" onchange="onUnit3Change()" title="الوحدة الثالثة (اختياري)" style="padding: 10px 6px; font-size: 0.8rem; font-weight: 700; border-color:rgba(56,189,248,0.2);">
                        <option value="">الوحدة 3 (اختياري)</option>
                    </select>
                </div>
            </div>
            <div class="input-group">
                <label>3. المخزن (اختياري)</label>
                <select id="warehouse-select" onchange="loadLedger()">
                    <option value="ALL">جميع المخازن</option>
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
                        <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: #94a3b8;">استوديو التصميم: يمكنك تعديل الألوان، الخطوط، وحدات التقرير، والتعديل المباشر على النصوص</p>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- Studio Design Toggle -->
                    <button type="button" id="btn-toggle-design-studio" onclick="toggleDesignStudioDrawer()" class="btn-action" style="background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.4); color: #38bdf8; padding: 7px 14px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <span>🎨</span> تخصيص التصميم
                    </button>
                    <!-- Live Edit Mode Toggle -->
                    <button type="button" id="btn-toggle-live-edit" onclick="toggleLiveEditMode()" class="btn-action" style="background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4); color: #fbbf24; padding: 7px 14px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <span>✏️</span> تعديل النصوص مباشرة
                    </button>
                    <!-- Print Button -->
                    <button type="button" onclick="printItemLedgerSheet()" class="btn-action" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 7px 18px; font-weight: 800; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                        <span>🖨️</span> طباعة التقرير
                    </button>
                    <!-- Close Button -->
                    <button type="button" onclick="closeItemLedgerPreview()" class="btn-action" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #e2e8f0; padding: 7px 14px; border-radius: 8px; cursor: pointer;">
                        ✕ إغلاق
                    </button>
                </div>
            </div>

            <!-- Design Studio Toolbar (Collapsible / Active) -->
            <div id="item-ledger-studio-drawer" class="no-print" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 12px; padding: 12px 18px; margin-bottom: 14px; box-shadow: inset 0 2px 10px rgba(0,0,0,0.3);">
                <!-- 1. Color Palette / Theme -->
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 700;">🎨 نمط الألوان والثيم:</label>
                    <select id="studio-theme-select" onchange="applyStudioDesign()" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 5px 10px; font-size: 0.8rem;">
                        <option value="corporate">🔷 أزرق نيلي شركات (Corporate)</option>
                        <option value="emerald">🟢 أخضر زمردي مخزني (Emerald)</option>
                        <option value="gold">🟡 ذهبي كلاسيكي فاخر (Royal Gold)</option>
                        <option value="cyan">🔵 سيان عصري (Modern Cyan)</option>
                        <option value="purple">🟣 بنفسجي تقني (Purple Tech)</option>
                        <option value="classic">⚫ رمادي أحادي رسمي (Monochrome)</option>
                    </select>
                </div>

                <!-- 2. Font Family -->
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 700;">🔤 نوع الخط العربي:</label>
                    <select id="studio-font-select" onchange="applyStudioDesign()" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 5px 10px; font-size: 0.8rem;">
                        <option value="'Tajawal', sans-serif">تجوّل (Tajawal)</option>
                        <option value="'Cairo', sans-serif">القاهرة (Cairo)</option>
                        <option value="'Almarai', sans-serif">المراعي (Almarai)</option>
                        <option value="'Traditional Arabic', 'Segoe UI', serif">عربي تقليدي (Traditional)</option>
                        <option value="'Segoe UI', Tahoma, sans-serif">عصري نظامي (Segoe UI)</option>
                    </select>
                </div>

                <!-- 3. Font Size -->
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 700;">📏 حجم الخط والجدول:</label>
                    <select id="studio-size-select" onchange="applyStudioDesign()" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 5px 10px; font-size: 0.8rem;">
                        <option value="compact">مدمج صغير (Compact - 11px)</option>
                        <option value="medium" selected>متوازن قياسي (Medium - 13px)</option>
                        <option value="large">كبير واضح (Large - 15px)</option>
                    </select>
                </div>

                <!-- 4. Paper Orientation -->
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 700;">📑 اتجاه الورقة (A4):</label>
                    <select id="studio-orientation-select" onchange="applyStudioDesign()" style="background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 6px; padding: 5px 10px; font-size: 0.8rem;">
                        <option value="landscape" selected>↔️ أفقي (Landscape - مناسب للجداول)</option>
                        <option value="portrait">↕️ طولي (Portrait)</option>
                    </select>
                </div>

                <!-- 5. Sections Toggles -->
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 700;">👁️ إظهار الأقسام:</label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <label style="font-size: 0.75rem; color: #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 3px;">
                            <input type="checkbox" id="chk-show-header" checked onchange="applyStudioDesign()"> الترويسة
                        </label>
                        <label style="font-size: 0.75rem; color: #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 3px;">
                            <input type="checkbox" id="chk-show-meta" checked onchange="applyStudioDesign()"> بيانات الصنف
                        </label>
                        <label style="font-size: 0.75rem; color: #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 3px;">
                            <input type="checkbox" id="chk-show-kpis" checked onchange="applyStudioDesign()"> ملخص الأرصدة
                        </label>
                        <label style="font-size: 0.75rem; color: #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 3px;">
                            <input type="checkbox" id="chk-show-signatures" checked onchange="applyStudioDesign()"> التوقيعات
                        </label>
                    </div>
                </div>

                <!-- 6. Columns Customizer Checkboxes -->
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 700;">📋 تحكم الأعمدة الأساسية:</label>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <label style="font-size: 0.72rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; cursor: pointer;"><input type="checkbox" class="col-toggle" data-col="date" checked onchange="applyStudioDesign()"> التاريخ</label>
                        <label style="font-size: 0.72rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; cursor: pointer;"><input type="checkbox" class="col-toggle" data-col="type" checked onchange="applyStudioDesign()"> نوع الحركة</label>
                        <label style="font-size: 0.72rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; cursor: pointer;"><input type="checkbox" class="col-toggle" data-col="permit" checked onchange="applyStudioDesign()"> رقم الإذن</label>
                        <label style="font-size: 0.72rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; cursor: pointer;"><input type="checkbox" class="col-toggle" data-col="receipt" checked onchange="applyStudioDesign()"> رقم الاستلام</label>
                        <label style="font-size: 0.72rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; cursor: pointer;"><input type="checkbox" class="col-toggle" data-col="in" checked onchange="applyStudioDesign()"> الوارد</label>
                        <label style="font-size: 0.72rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; cursor: pointer;"><input type="checkbox" class="col-toggle" data-col="out" checked onchange="applyStudioDesign()"> المنصرف</label>
                        <label style="font-size: 0.72rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; cursor: pointer;"><input type="checkbox" class="col-toggle" data-col="balance" checked onchange="applyStudioDesign()"> الرصيد</label>
                        <label style="font-size: 0.72rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; cursor: pointer;"><input type="checkbox" class="col-toggle" data-col="notes" checked onchange="applyStudioDesign()"> ملحوظة الحركة</label>
                    </div>
                </div>

                <!-- 7. Save & Reset Presets -->
                <div style="display: flex; align-items: flex-end; gap: 6px; margin-right: auto;">
                    <button type="button" onclick="saveStudioDesignPreset()" style="background: rgba(16,185,129,0.2); border: 1px solid rgba(16,185,129,0.4); color: #34d399; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
                        💾 حفظ التصميم
                    </button>
                    <button type="button" onclick="resetStudioDesignPreset()" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: #cbd5e1; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; cursor: pointer;">
                        🔄 استعادة
                    </button>
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
        #item-ledger-preview-modal.print-direct-mode {
            display: none !important;
        }
        @media print {
            #item-ledger-preview-modal.print-direct-mode {
                display: block !important;
            }
            body > *:not(#item-ledger-preview-modal) {
                display: none !important;
            }
            #item-ledger-preview-modal {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: auto !important;
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                overflow: visible !important;
                z-index: 9999999 !important;
            }
            #item-ledger-preview-modal .glass-panel {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                border: none !important;
                background: #fff !important;
                padding: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                overflow: visible !important;
            }
            .global-sidebar, .no-print, .btn-action, .dash-controls {
                display: none !important;
            }
            #item-ledger-preview-modal .preview-scroll-container {
                background: #fff !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            #item-ledger-sheet {
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            /* Clean table vector styling for paper */
            #item-ledger-sheet table {
                width: 100% !important;
                border-collapse: collapse !important;
                table-layout: auto !important;
            }
            #item-ledger-sheet th, #item-ledger-sheet td {
                border: 1px solid #cbd5e1 !important;
                page-break-inside: avoid;
            }
            .live-editable-active {
                outline: none !important;
                border-color: inherit !important;
            }
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
        function getItemUnitsList(item) {
            if (!item) return [];
            let list = [];
            
            // 1. From item.units matrix
            if (Array.isArray(item.units) && item.units.length > 0) {
                item.units.forEach(u => {
                    if (u && (u.unitName || u.name)) {
                        list.push({
                            name: (u.unitName || u.name).trim(),
                            factor: parseFloat(u.factor) || 1
                        });
                    }
                });
            }

            // 2. Base unit fallback
            const base = (item.baseUnit || item.unit || item.selectedUnit || '').trim();
            if (base && !list.some(u => u.name === base)) {
                list.unshift({ name: base, factor: 1 });
            }

            // 3. Secondary / Sub-unit fallback if defined on item
            if (item.subUnit && !list.some(u => u.name === item.subUnit.trim())) {
                list.push({ name: item.subUnit.trim(), factor: parseFloat(item.conversionFactor || item.bundleFactor) || 1 });
            }
            if (item.thirdUnit && !list.some(u => u.name === item.thirdUnit.trim())) {
                list.push({ name: item.thirdUnit.trim(), factor: parseFloat(item.thirdFactor) || 1 });
            }

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
            const itemId = document.getElementById('item-select').value;
            const item = itemsDb.find(i => i.id === itemId);
            const units = getItemUnitsList(item);

            const u1 = document.getElementById('unit-select-1');
            if (!u1) return;

            // 1. Populate Unit 1 with all available units of the item
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

            // 2. Cascade down to populate Unit 2 & Unit 3
            refreshUnit2Dropdown(units);
            refreshUnit3Dropdown(units);

            loadLedger();
        }

        function onUnit1Change() {
            const itemId = document.getElementById('item-select').value;
            const item = itemsDb.find(i => i.id === itemId);
            const units = getItemUnitsList(item);

            refreshUnit2Dropdown(units);
            refreshUnit3Dropdown(units);
            loadLedger();
        }

        function onUnit2Change() {
            const itemId = document.getElementById('item-select').value;
            const item = itemsDb.find(i => i.id === itemId);
            const units = getItemUnitsList(item);

            refreshUnit3Dropdown(units);
            loadLedger();
        }

        function onUnit3Change() {
            loadLedger();
        }

        function refreshUnit2Dropdown(units) {
            const u1Val = document.getElementById('unit-select-1')?.value;
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

            // Keep previous selection if still valid; otherwise pick first available or empty
            if (prevU2Val && availableForU2.some(u => u.name === prevU2Val)) {
                u2.value = prevU2Val;
            } else if (availableForU2.length > 0 && !prevU2Val) {
                u2.value = availableForU2[0].name;
            } else {
                u2.value = '';
            }
        }

        function refreshUnit3Dropdown(units) {
            const u1Val = document.getElementById('unit-select-1')?.value;
            const u2Val = document.getElementById('unit-select-2')?.value;
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

            // Keep previous selection if still valid; otherwise pick first available if u2 was set
            if (prevU3Val && availableForU3.some(u => u.name === prevU3Val)) {
                u3.value = prevU3Val;
            } else if (availableForU3.length > 0 && !prevU3Val && u2Val) {
                u3.value = availableForU3[0].name;
            } else {
                u3.value = '';
            }
        }

        function getActiveSelectedUnits(item) {
            const unitsList = getItemUnitsList(item);
            const u1Val = document.getElementById('unit-select-1')?.value;
            const u2Val = document.getElementById('unit-select-2')?.value;
            const u3Val = document.getElementById('unit-select-3')?.value;

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

            if (selected.length === 0) {
                selected.push(unitsList[0] || { name: 'وحدة', factor: 1 });
            }
            return selected.slice(0, 3); // Maximum 3 units
        }

        function formatQtyInUnit(baseQty, unitObj) {
            if (baseQty === undefined || baseQty === null || isNaN(baseQty) || baseQty === 0) return '-';
            const factor = (unitObj && unitObj.factor > 0) ? unitObj.factor : 1;
            const val = baseQty / factor;
            return fmtNum(val);
        }
        
        document.addEventListener('DBReady', () => {
            // Load Items to Select - Show Item Name ONLY to maximize UI space
            itemsDb = JSON.parse(localStorage.getItem(ITEMS_STORAGE_KEY)) || [];
            const select = document.getElementById('item-select');
            itemsDb.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.text = item.name;
                select.appendChild(opt);
            });

            // Load Warehouses
            const whSelect = document.getElementById('warehouse-select');
            warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            const displayWh = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];
            displayWh.forEach(wh => {
                const opt = document.createElement('option');
                opt.value = wh.id;
                opt.text = wh.name;
                whSelect.appendChild(opt);
            });

            // Set default dates (1st of month to today)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            document.getElementById('date-to').valueAsDate = today;

            // Check if URL has ?item=ID
            const urlParams = new URLSearchParams(window.location.search);
            const itemId = urlParams.get('item');
            if (itemId) {
                select.value = itemId;
                onItemSelected();
            }
        });

        function buildRealHistory(itemId) {
            let history = [];
            
            // 1. Get Purchases
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            purchases.forEach(inv => {
                (inv.items || inv.lines || []).forEach(line => {
                    if (line.itemId === itemId || line.productId === itemId) {
                        let wh = getWarehouseId(line.warehouseId || inv.warehouseId || inv.warehouse);
                        if (wh === 'PENDING_RECEIPT') {
                            return; // Skip pending items (not in actual warehouse yet)
                        }

                        let baseQty = 0;
                        if (line.customBaseQty !== undefined) {
                            baseQty = line.customBaseQty;
                        } else {
                            let item = itemsDb.find(i => i.id === itemId);
                            let factor = line.customFactor !== undefined ? line.customFactor : 1;
                            let conv = item ? (item.conversionFactor || 1) : 1;
                            baseQty = (parseFloat(line.quantity) || 0) * factor * conv;
                        }

                        let typeName = 'فاتورة مشتريات';
                        let typeCode = 'in';
                        let valIn = baseQty;
                        let valOut = 0;

                        if (inv.isReturn) {
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
                            // مذكرة استلام بضاعة: رقم الإذن = الفاتورة الأصلية (e.g. 26080015)
                            permitNo = inv.originalInvoiceId || inv.invoiceNo || inv.poNumber || inv.orderNo || '-';
                            receiptNo = inv.id || inv.grnNumber || inv.receiptNo || '-';
                            permitDocId = inv.originalInvoiceId || inv.invoiceNo || '';
                            receiptDocId = inv.id || '';
                        } else {
                            // فاتورة مشتريات عادية: رقم الإذن / الفاتورة = 26080015
                            permitNo = inv.originalInvoiceId || inv.poNumber || inv.orderNo || inv.permitNo || inv.invoiceNo || inv.id || '-';
                            receiptNo = inv.receiptNo || inv.invoiceNo || inv.id || '-';
                            permitDocId = permitNo !== '-' ? permitNo : (inv.invoiceNo || inv.id);
                            receiptDocId = inv.invoiceNo || inv.id;
                        }

                        // Capture Note for Goods Receipt / Purchase
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
                            out: valOut
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
                    if (line.itemId === itemId) {
                        if (inv.saleType === 'reserved' && inv.deliveryStatus !== 'delivered') {
                            return; // Skip if reserved but not delivered yet
                        }

                        let lineWh = getWarehouseId(inv.deliveryWarehouse || line.warehouseId || hdrWh);
                        if (lineWh === 'PENDING_RECEIPT') {
                            return; // Skip pending delivery items
                        }
                        
                        let baseQty = 0;
                        if (line.customBaseQty !== undefined) {
                            baseQty = line.customBaseQty;
                        } else {
                            let item = itemsDb.find(i => i.id === itemId);
                            let factor = line.customFactor !== undefined ? line.customFactor : 1;
                            let conv = item ? (item.conversionFactor || 1) : 1;
                            baseQty = (parseFloat(line.quantity) || 0) * factor * conv;
                        }

                        let permitNo = inv.orderNo || inv.soNumber || inv.quotationNo || inv.dispatchOrderNo || inv.permitNo || inv.invoiceNo || inv.id || '-';
                        let receiptNo = inv.invoiceNo || inv.receiptNo || inv.id || 'INV';
                        let permitDocId = permitNo !== '-' ? permitNo : (inv.invoiceNo || inv.id);
                        let receiptDocId = inv.invoiceNo || inv.id;

                        // Capture Note for Sales / Delivery Note
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

                        history.push({
                            date: new Date(inv.date || Date.now()),
                            typeCode: 'out',
                            typeName: 'فاتورة مبيعات',
                            permitNo: permitNo,
                            permitDocId: permitDocId,
                            receiptNo: receiptNo,
                            receiptDocId: receiptDocId,
                            isGRN: false,
                            ref: receiptNo,
                            docId: receiptDocId,
                            warehouse: lineWh,
                            notes: noteText,
                            in: 0,
                            out: baseQty
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

            if (activeUnits.length === 1) {
                const uName = activeUnits[0].name;
                thead.innerHTML = `
                    <tr>
                        <th>التاريخ</th>
                        <th>نوع الحركة</th>
                        <th style="color:#38bdf8;">رقم الإذن</th>
                        <th>رقم الاستلام</th>
                        <th style="color:#10b981; background:rgba(16,185,129,0.05); text-align: right;">الوارد (${uName})</th>
                        <th style="color:#ec4899; background:rgba(236,72,153,0.05); text-align: right;">المنصرف (${uName})</th>
                        <th style="color:#fbbf24; background:rgba(251,191,36,0.05); text-align: right;">الرصيد (${uName})</th>
                        <th style="color:#38bdf8;">ملحوظة الحركة</th>
                    </tr>
                `;
            } else {
                const numCols = activeUnits.length;
                let uHeadersIn = activeUnits.map(u => `<th style="color:#10b981; background:rgba(16,185,129,0.08); text-align:right; font-size:0.75rem; padding:8px 10px;">${u.name}</th>`).join('');
                let uHeadersOut = activeUnits.map(u => `<th style="color:#ec4899; background:rgba(236,72,153,0.08); text-align:right; font-size:0.75rem; padding:8px 10px;">${u.name}</th>`).join('');
                let uHeadersBal = activeUnits.map(u => `<th style="color:#fbbf24; background:rgba(251,191,36,0.08); text-align:right; font-size:0.75rem; padding:8px 10px;">${u.name}</th>`).join('');

                thead.innerHTML = `
                    <tr>
                        <th rowspan="2" style="vertical-align:middle;">التاريخ</th>
                        <th rowspan="2" style="vertical-align:middle;">نوع الحركة</th>
                        <th rowspan="2" style="color:#38bdf8; vertical-align:middle;">رقم الإذن</th>
                        <th rowspan="2" style="vertical-align:middle;">رقم الاستلام</th>
                        <th colspan="${numCols}" style="color:#10b981; background:rgba(16,185,129,0.05); text-align: center; border-bottom:1px solid rgba(16,185,129,0.2);">الوارد (داخل)</th>
                        <th colspan="${numCols}" style="color:#ec4899; background:rgba(236,72,153,0.05); text-align: center; border-bottom:1px solid rgba(236,72,153,0.2);">المنصرف (خارج)</th>
                        <th colspan="${numCols}" style="color:#fbbf24; background:rgba(251,191,36,0.05); text-align: center; border-bottom:1px solid rgba(251,191,36,0.2);">الرصيد التراكمي</th>
                        <th rowspan="2" style="color:#38bdf8; vertical-align:middle;">ملحوظة الحركة</th>
                    </tr>
                    <tr>
                        ${uHeadersIn}
                        ${uHeadersOut}
                        ${uHeadersBal}
                    </tr>
                `;
            }
        }

        function renderKpis(sumOpen, sumIn, sumOut, actualCurrentStock, activeUnits) {
            function formatKpiDisplay(val, units) {
                if (!units || units.length === 0) return '0';
                const main = `${formatQtyInUnit(val, units[0])} <span style="font-size:0.85rem; font-weight:700; opacity:0.85;">${units[0].name}</span>`;
                if (units.length > 1) {
                    const subs = units.slice(1).map(u => `${formatQtyInUnit(val, u)} ${u.name}`).join(' | ');
                    return `<div style="display:flex; flex-direction:column; align-items:baseline;"><div>${main}</div><div style="font-size:0.78rem; font-weight:800; opacity:0.85; margin-top:3px; color:#38bdf8; font-family:monospace; background:rgba(255,255,255,0.05); padding:2px 8px; border-radius:6px;">${subs}</div></div>`;
                }
                return main;
            }

            document.getElementById('sum-open').innerHTML = formatKpiDisplay(sumOpen, activeUnits);
            document.getElementById('sum-in').innerHTML = formatKpiDisplay(sumIn, activeUnits);
            document.getElementById('sum-out').innerHTML = formatKpiDisplay(sumOut, activeUnits);
            document.getElementById('sum-balance').innerHTML = formatKpiDisplay(actualCurrentStock, activeUnits);
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

            const item = itemsDb.find(i => i.id === itemId);
            const activeUnits = getActiveSelectedUnits(item);
            renderLedgerTableHeader(activeUnits);
            
            // 1. Build history
            let history = buildRealHistory(itemId);

            // 2. Filter by warehouse first to calculate the correct running balance for this warehouse
            const filterWh = document.getElementById('warehouse-select').value;
            if (filterWh && filterWh !== 'ALL') {
                history = history.filter(h => getWarehouseId(h.warehouse) === getWarehouseId(filterWh) || h.typeCode === 'open');
            }

            // 3. Sort chronologically
            history.sort((a, b) => new Date(a.date) - new Date(b.date));

            // 4. Calculate true running balances chronologically (unfiltered by date)
            let running = 0;
            history.forEach(row => {
                running += (row.in - row.out);
                row.runningBalance = running;
            });

            // Keep track of the final overall balance (actual current stock)
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

            filteredHistory.forEach((row, index) => {
                if (row.typeCode === 'in') sumIn += row.in;
                if (row.typeCode === 'out') sumOut += row.out;

                let typeBadge = '';
                if (row.typeCode === 'in') typeBadge = `<span class="type-badge type-in">${row.typeName}</span>`;
                else if (row.typeCode === 'out') typeBadge = `<span class="type-badge type-out">${row.typeName}</span>`;
                else typeBadge = `<span class="type-badge type-bal">${row.typeName}</span>`;

                const dStr = row.date.toISOString().split('T')[0];

                const tr = document.createElement('tr');

                // Permit (Original Invoice / Order) rendering
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

                // Receipt Note / Document rendering
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

                // Multi-unit cells
                let inCells = activeUnits.map(u => `<td style="color:#10b981; font-weight:700; background:rgba(16,185,129,0.02); text-align:right;">${row.in > 0 ? formatQtyInUnit(row.in, u) : '-'}</td>`).join('');
                let outCells = activeUnits.map(u => `<td style="color:#ec4899; font-weight:700; background:rgba(236,72,153,0.02); text-align:right;">${row.out > 0 ? formatQtyInUnit(row.out, u) : '-'}</td>`).join('');
                let balCells = activeUnits.map(u => `<td style="color:#fbbf24; font-weight:900; background:rgba(251,191,36,0.02); font-size:1.05rem; text-align:right;">${formatQtyInUnit(row.runningBalance, u)}</td>`).join('');

                tr.innerHTML = `
                    <td>${dStr}</td>
                    <td>${typeBadge}</td>
                    <td style="font-weight:700;">${permitHtml}</td>
                    <td style="font-weight:600;">${refHtml}</td>
                    ${inCells}
                    ${outCells}
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

            const item = itemsDb.find(i => i.id === itemId);
            const itemName = item ? item.name : 'الصنف';
            const itemCode = itemId;
            const itemCat = item ? (item.category || 'عام') : '-';
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

            // Generate Preview Table Header with Multi-Units
            let tableHeaderHtml = '';
            const numUnits = activeUnits.length;
            if (numUnits === 1) {
                const uName = activeUnits[0].name;
                tableHeaderHtml = `
                    <tr class="ledger-sheet-thead-row">
                        <th class="col-date" style="padding:10px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8; width:95px;">التاريخ</th>
                        <th class="col-type" style="padding:10px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8; width:130px;">نوع الحركة</th>
                        <th class="col-permit" style="padding:10px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8; width:120px;">رقم الإذن</th>
                        <th class="col-receipt" style="padding:10px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8; width:120px;">رقم الاستلام</th>
                        <th class="col-in" style="padding:10px 8px; text-align:right; font-weight:800; border:1px solid #94a3b8; width:110px;">الوارد (${uName})</th>
                        <th class="col-out" style="padding:10px 8px; text-align:right; font-weight:800; border:1px solid #94a3b8; width:110px;">المنصرف (${uName})</th>
                        <th class="col-balance" style="padding:10px 8px; text-align:right; font-weight:900; border:1px solid #94a3b8; width:120px;">الرصيد (${uName})</th>
                        <th class="col-notes" style="padding:10px 8px; text-align:right; font-weight:800; border:1px solid #94a3b8;">ملحوظة الحركة</th>
                    </tr>
                `;
            } else {
                let uHeadersIn = activeUnits.map(u => `<th class="col-in" style="padding:7px 8px; text-align:right; font-size:0.75rem; border:1px solid #94a3b8;">${u.name}</th>`).join('');
                let uHeadersOut = activeUnits.map(u => `<th class="col-out" style="padding:7px 8px; text-align:right; font-size:0.75rem; border:1px solid #94a3b8;">${u.name}</th>`).join('');
                let uHeadersBal = activeUnits.map(u => `<th class="col-balance" style="padding:7px 8px; text-align:right; font-size:0.75rem; border:1px solid #94a3b8;">${u.name}</th>`).join('');

                tableHeaderHtml = `
                    <tr class="ledger-sheet-thead-row">
                        <th class="col-date" rowspan="2" style="padding:10px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8; width:95px; vertical-align:middle;">التاريخ</th>
                        <th class="col-type" rowspan="2" style="padding:10px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8; width:125px; vertical-align:middle;">نوع الحركة</th>
                        <th class="col-permit" rowspan="2" style="padding:10px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8; width:115px; vertical-align:middle;">رقم الإذن</th>
                        <th class="col-receipt" rowspan="2" style="padding:10px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8; width:115px; vertical-align:middle;">رقم الاستلام</th>
                        <th class="col-in" colspan="${numUnits}" style="padding:7px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8;">الوارد (داخل)</th>
                        <th class="col-out" colspan="${numUnits}" style="padding:7px 8px; text-align:center; font-weight:800; border:1px solid #94a3b8;">المنصرف (خارج)</th>
                        <th class="col-balance" colspan="${numUnits}" style="padding:7px 8px; text-align:center; font-weight:900; border:1px solid #94a3b8;">الرصيد التراكمي</th>
                        <th class="col-notes" rowspan="2" style="padding:10px 8px; text-align:right; font-weight:800; border:1px solid #94a3b8; vertical-align:middle;">ملحوظة الحركة</th>
                    </tr>
                    <tr class="ledger-sheet-thead-subrow">
                        ${uHeadersIn}
                        ${uHeadersOut}
                        ${uHeadersBal}
                    </tr>
                `;
            }

            // Generate Table Rows HTML
            let rowsHtml = '';
            const totalCols = 5 + (activeUnits.length * 3);
            if (filteredHistory.length === 0) {
                rowsHtml = `<tr><td colspan="${totalCols}" style="text-align:center; padding:35px; color:#64748b; font-size:1.05rem;">لا توجد حركات مسجلة لهذا الصنف في هذه الفترة</td></tr>`;
            } else {
                filteredHistory.forEach((row) => {
                    if (row.typeCode === 'in') sumIn += row.in;
                    if (row.typeCode === 'out') sumOut += row.out;

                    const dStr = row.date.toISOString().split('T')[0];
                    const permitDisplay = (row.permitNo && row.permitNo !== '-') ? row.permitNo : '-';
                    const receiptDisplay = row.ref || row.receiptNo || '-';
                    const noteDisplay = (row.notes && row.notes !== '-' && row.notes.trim()) ? row.notes : '-';

                    let inCells = activeUnits.map(u => `<td class="col-in" style="text-align:right; font-weight:700; color:#047857; background:rgba(16,185,129,0.03);">${row.in > 0 ? formatQtyInUnit(row.in, u) : '-'}</td>`).join('');
                    let outCells = activeUnits.map(u => `<td class="col-out" style="text-align:right; font-weight:700; color:#be185d; background:rgba(236,72,153,0.03);">${row.out > 0 ? formatQtyInUnit(row.out, u) : '-'}</td>`).join('');
                    let balCells = activeUnits.map(u => `<td class="col-balance" style="text-align:right; font-weight:900; color:#b45309; background:rgba(245,158,11,0.04); font-size:1.05rem;">${formatQtyInUnit(row.runningBalance, u)}</td>`).join('');

                    rowsHtml += `
                        <tr class="ledger-sheet-row">
                            <td class="col-date" style="font-weight:600; text-align:center;">${dStr}</td>
                            <td class="col-type" style="text-align:center;"><span class="sheet-badge badge-${row.typeCode}">${row.typeName}</span></td>
                            <td class="col-permit" style="font-weight:700; text-align:center; color:#0369a1;">📄 ${permitDisplay}</td>
                            <td class="col-receipt" style="font-weight:700; text-align:center; color:#0f172a;">${receiptDisplay}</td>
                            ${inCells}
                            ${outCells}
                            ${balCells}
                            <td class="col-notes" style="text-align:right; color:#334155; font-size:0.88rem;">${noteDisplay !== '-' ? '📝 ' + noteDisplay : '-'}</td>
                        </tr>
                    `;
                });
            }

            // Footer Multi-unit Totals
            let footerInCells = activeUnits.map(u => `<td class="col-in" style="padding:10px; text-align:right; color:#047857; border:1px solid #94a3b8; font-weight:900;">${formatQtyInUnit(sumIn, u)}</td>`).join('');
            let footerOutCells = activeUnits.map(u => `<td class="col-out" style="padding:10px; text-align:right; color:#be185d; border:1px solid #94a3b8; font-weight:900;">${formatQtyInUnit(sumOut, u)}</td>`).join('');
            let footerBalCells = activeUnits.map(u => `<td class="col-balance" style="padding:10px; text-align:right; color:#b45309; border:1px solid #94a3b8; font-size:1.05rem; font-weight:900;">${formatQtyInUnit(actualCurrentStock, u)}</td>`).join('');

            // Units display pill
            const unitsPillsHtml = activeUnits.map((u, i) => `<span style="background:rgba(56,189,248,0.12); color:#0284c7; border:1px solid rgba(56,189,248,0.3); padding:2px 8px; border-radius:6px; font-weight:800; font-size:0.78rem;">الوحدة ${i+1}: ${u.name} (معامل: ${u.factor})</span>`).join(' ');

            // KPI multi-unit text
            function getKpiText(val) {
                return activeUnits.map(u => `<strong>${formatQtyInUnit(val, u)}</strong> <small>${u.name}</small>`).join(' | ');
            }

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
                            <strong style="color:#0f172a; font-size:1rem;">[${itemCode}] ${itemName}</strong>
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

        function printItemLedgerSheet() {
            // If live editing is active, disable outlines before printing
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


