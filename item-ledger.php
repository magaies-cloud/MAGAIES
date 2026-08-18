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
                <button class="btn-action" onclick="openReportPreview('تقرير حركة الصنف التفصيلي', '#report-table-wrap')" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(13,148,136,0.2);">👁️ معاينة</button>
                <button class="btn-action btn-print" onclick="printReportDirectly('تقرير حركة الصنف التفصيلي', '#report-table-wrap')" style="background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--glass-border); padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer;">🖨️ طباعة</button>
                <button class="btn-icon-nav" onclick="history.back()" title="رجوع" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; color: var(--text-primary); cursor: pointer; transition: 0.3s;">↩</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <div class="input-group">
                <label>اختر الصنف المراد جرده</label>
                <select id="item-select" onchange="loadLedger()">
                    <option value="">-- يرجى اختيار صنف --</option>
                </select>
            </div>
            <div class="input-group">
                <label>المخزن (اختياري)</label>
                <select id="warehouse-select" onchange="loadLedger()">
                    <option value="ALL">جميع المخازن</option>
                </select>
            </div>
            <div class="input-group">
                <label>من تاريخ</label>
                <input type="date" id="date-from" onchange="loadLedger()">
            </div>
            <div class="input-group">
                <label>إلى تاريخ</label>
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
                        </tr>
                    </thead>
                    <tbody id="ledger-tbody">
                        <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-secondary);">يرجى اختيار صنف لعرض حركته التفصيلية</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        const ITEMS_STORAGE_KEY = 'erp_items_v1';
        let itemsDb = [];
        let warehousesDb = [];

        function fmtNum(n) {
            if (n === undefined || n === null || isNaN(n)) return '0';
            return Number(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
        }

        function getWarehouseId(nameOrId) {
            if (!nameOrId) return '';
            const wh = warehousesDb.find(w => w.id === nameOrId || w.name === nameOrId);
            return wh ? wh.id : nameOrId;
        }
        
        document.addEventListener('DBReady', () => {
            // Load Items to Select
            itemsDb = JSON.parse(localStorage.getItem(ITEMS_STORAGE_KEY)) || [];
            const select = document.getElementById('item-select');
            itemsDb.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.text = `[${item.id}] ${item.name} - ${item.category}`;
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
                loadLedger();
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

        function loadLedger() {
            const itemId = document.getElementById('item-select').value;
            const tbody = document.getElementById('ledger-tbody');
            
            if (!itemId) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-secondary);">يرجى اختيار صنف لعرض حركته التفصيلية</td></tr>`;
                document.getElementById('sum-open').innerText = '0';
                document.getElementById('sum-in').innerText = '0';
                document.getElementById('sum-out').innerText = '0';
                document.getElementById('sum-balance').innerText = '0';
                return;
            }

            const item = itemsDb.find(i => i.id === itemId);
            
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

            if (filteredHistory.length === 0) {
                 tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد حركات لهذا الصنف في هذه الفترة</td></tr>`;
            } else {
                 // The "opening balance" of the filtered period is the running balance just before the first filtered row
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

                tr.innerHTML = `
                    <td>${dStr}</td>
                    <td>${typeBadge}</td>
                    <td style="font-weight:700;">${permitHtml}</td>
                    <td style="font-weight:600;">${refHtml}</td>
                    <td style="color:#10b981; font-weight:700; background:rgba(16,185,129,0.02);">${row.in > 0 ? fmtNum(row.in) : '-'}</td>
                    <td style="color:#ec4899; font-weight:700; background:rgba(236,72,153,0.02);">${row.out > 0 ? fmtNum(row.out) : '-'}</td>
                    <td style="color:#fbbf24; font-weight:900; background:rgba(251,191,36,0.02); font-size:1.1rem;">${fmtNum(row.runningBalance)}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('sum-open').innerText = sumOpen.toLocaleString();
            document.getElementById('sum-in').innerText = sumIn.toLocaleString();
            document.getElementById('sum-out').innerText = sumOut.toLocaleString();
            document.getElementById('sum-balance').innerText = actualCurrentStock.toLocaleString();
        }
    </script>
</body>
</html>

