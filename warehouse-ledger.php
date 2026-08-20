<?php $page_title = 'مقاييس (MAGAIEES) - كشف المخزن التحليلي'; ?>
<!-- c:\mynewapp\frontend-pwa\warehouse-ledger.html -->
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
    <title>مقاييس (MAGAIEES) - كشف المخزن التحليلي</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/report-ribbon.css?v=1787182249">
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
        .nav-dropdown:focus-within .nav-dropdown-content,
        .nav-dropdown.active .nav-dropdown-content {
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
        .dash-controls { display: flex; align-items: center; gap: 12px; }
        .btn-action {
            padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; border: none; transition: 0.3s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-print { background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--glass-border); }
        .btn-print:hover { background: rgba(255,255,255,0.15); }
        .btn-icon-nav { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-primary); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
        .btn-icon-nav:hover { background: rgba(255,255,255,0.15); }

        
        /* Top Layer Dropdown and Option Styling */
        .filters-bar {
            position: relative !important;
            z-index: 100 !important;
        }
        .filters-bar .input-group {
            position: relative !important;
            z-index: 101 !important;
        }
        #warehouse-select {
            position: relative !important;
            z-index: 102 !important;
            cursor: pointer;
        }
        #warehouse-select option {
            background: #0f172a !important;
            color: #ffffff !important;
            padding: 10px !important;
        }

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

        /* Data Table style matching inventory-report */
        .table-panel {
            background: var(--panel-bg); backdrop-filter: blur(15px); border-radius: 16px;
            border: 1px solid var(--glass-border); box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            overflow: hidden; display: flex; flex-direction: column;
        }
        .table-wrapper { overflow-x: auto; overflow-y: auto; max-height: 60vh; width: 100%; }
        .dash-table { width: 100%; border-collapse: collapse; min-width: 100%; table-layout: auto !important; }
        .dash-table thead { position: sticky; top: 0; z-index: 10; }
        .dash-table th {
            background: rgba(10, 15, 25, 0.95); padding: 15px; text-align: right;
            font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); border-bottom: 2px solid var(--glass-border);
            text-transform: uppercase; letter-spacing: 0.5px;
            position: relative !important;
            white-space: nowrap !important;
            box-sizing: border-box !important;
        }
        .dash-table td {
            padding: 15px; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle; transition: background 0.2s; color: #f8fafc;
            white-space: nowrap !important;
            box-sizing: border-box !important;
        }
        .dash-table tbody tr { transform: none !important; opacity: 1; }
        .dash-table tbody tr:nth-child(even) td { background: rgba(255,255,255,0.06); }
        :root.light-theme .dash-table tbody tr:nth-child(even) td { background: rgba(0,0,0,0.06) !important; }
        .dash-table tbody tr:hover td { background: rgba(255,255,255,0.08) !important; }

        .resizer {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 7px;
            cursor: col-resize;
            user-select: none;
            z-index: 30;
            background: transparent;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .resizer:hover, .resizer.resizing {
            background: #38bdf8 !important;
            box-shadow: 0 0 10px #38bdf8;
        }

        @keyframes fadeSlideIn { to { opacity: 1; transform: translateY(0); } }

        .type-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: bold; }
        .type-in { background: rgba(16, 185, 129, 0.15); color: var(--emerald-accent); }
        .type-out { background: rgba(236, 72, 153, 0.15); color: var(--pink-accent); }

        /* Universal Light Theme Overrides */
        :root.light-theme body { background-color: var(--bg-dark); color: var(--text-primary); }
        :root.light-theme .glass-panel,
        :root.light-theme .premium-card,
        :root.light-theme .filters-bar,
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

        @media print {
            body { background: #fff !important; color: #000 !important; }
            .table-panel, .dash-table th, .dash-table td { background: #fff !important; color: #000 !important; border-color: #ccc !important; }
            .global-sidebar, button, .filters-bar { display: none !important; }
        }
\n</style>
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

        <!-- Unified Smart Ribbon Toolbar Row -->
        <div class="report-ribbon-bar">
            <div class="ribbon-presets-group">
                <button class="preset-pill-btn active" data-preset="0" onclick="applyAndOpenPreview(0, this)" title="القالب الافتراضي 1">
                    <span class="preset-num">1</span> <span>افتراضي 1</span>
                </button>
                <button class="preset-pill-btn" data-preset="1" onclick="applyAndOpenPreview(1, this)" title="القالب المخصص 2">
                    <span class="preset-num">2</span> <span>تخصيص 2</span>
                </button>
                <button class="preset-pill-btn" data-preset="2" onclick="applyAndOpenPreview(2, this)" title="القالب المخصص 3">
                    <span class="preset-num">3</span> <span>تخصيص 3</span>
                </button>
            </div>

            <div class="ribbon-actions-group">
                <button class="ribbon-btn" onclick="openStudioPreviewPage()" style="--btn-color: #eab308; background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(202, 138, 4, 0.3)); border-color: rgba(234, 179, 8, 0.5); color: #fef08a;">
                    <span>⚙️</span> <span>إعدادات الأعمدة</span>
                </button>
                <button class="ribbon-btn" onclick="openWarehouseLedgerPreview()" style="--btn-color: #0d9488;">
                    <span>👁️</span> <span>معاينة وتصميم التقرير</span>
                </button>
                <button class="ribbon-btn" onclick="printWarehouseLedgerSheet()" style="--btn-color: #38bdf8;">
                    <span>🖨️</span> <span>طباعة</span>
                </button>
            </div>
        </div>

        <!-- Pro Studio Dropdown Panel & Modals -->
        <!-- Studio panel moved to preview-inventory-report.php -->
        <!-- Studio modals moved to preview-inventory-report.php -->

        <!-- KPIs Strip -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
            <div style="background: rgba(56,189,248,0.08); border: 1px solid rgba(56,189,248,0.25); border-radius: 12px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">عدد الحركات المسجلة 📋</div>
                <div id="kpi-wh-count" style="font-size: 1.3rem; font-weight: 900; color: #38bdf8; margin-top: 4px;">0</div>
            </div>
            <div style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); border-radius: 12px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">إجمالي الوارد (دخول) ⚖️</div>
                <div id="kpi-wh-in" style="font-size: 1.3rem; font-weight: 900; color: #10b981; margin-top: 4px;">0</div>
            </div>
            <div style="background: rgba(236,72,153,0.08); border: 1px solid rgba(236,72,153,0.25); border-radius: 12px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">إجمالي المنصرف (خروج) 💰</div>
                <div id="kpi-wh-out" style="font-size: 1.3rem; font-weight: 900; color: #ec4899; margin-top: 4px;">0</div>
            </div>
            <div style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); border-radius: 12px; padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">صافي تدفق المخزن 🔄</div>
                <div id="kpi-wh-net" style="font-size: 1.3rem; font-weight: 900; color: #fbbf24; margin-top: 4px;">0</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <div class="input-group" style="grid-column: span 2;">
                <label>المخزن المستهدف</label>
                <select id="warehouse-select" onchange="loadLedger()">
                    <option value="ALL">جميع المخازن (كافة الحركات)</option>
                </select>
            </div>
            <div class="input-group">
                <label>بحث بالصنف / الكود / البيان</label>
                <input type="text" id="search-filter" placeholder="اكتب للبحث السريع..." oninput="loadLedger()">
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

        <!-- Ledger Table -->
        <div class="table-panel">
            <div class="table-wrapper" id="report-table-wrap">
                <table class="dash-table" id="warehouse-ledger-table">
                    <thead>
                        <tr>
                            <th data-col-idx="0">التاريخ<div class="resizer" title="اسحب لتغيير عرض العمود"></div></th>
                            <th data-col-idx="1">نوع الحركة<div class="resizer" title="اسحب لتغيير عرض العمود"></div></th>
                            <th data-col-idx="2">المخزن<div class="resizer" title="اسحب لتغيير عرض العمود"></div></th>
                            <th data-col-idx="3">المستند / الإذن<div class="resizer" title="اسحب لتغيير عرض العمود"></div></th>
                            <th data-col-idx="4">الصنف والبيان<div class="resizer" title="اسحب لتغيير عرض العمود"></div></th>
                            <th data-col-idx="5" style="color:#10b981; background:rgba(16,185,129,0.05); text-align: right;">وارد (دخول)<div class="resizer" title="اسحب لتغيير عرض العمود"></div></th>
                            <th data-col-idx="6" style="color:#ec4899; background:rgba(236,72,153,0.05); text-align: right;">منصرف (خروج)<div class="resizer" title="اسحب لتغيير عرض العمود"></div></th>
                            <th data-col-idx="7">إجراء<div class="resizer" title="اسحب لتغيير عرض العمود"></div></th>
                        </tr>
                    </thead>
                    <tbody id="ledger-tbody">
                        <tr><td colspan="8" style="text-align:center; padding:30px; color:var(--text-secondary);">الرجاء تحديد المخزن والفترة للبحث</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- 🎨 WAREHOUSE LEDGER PREVIEW & DESIGN STUDIO MODAL                -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div id="warehouse-ledger-preview-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 15, 30, 0.92); z-index: 12000; align-items: center; justify-content: center; direction: rtl; backdrop-filter: blur(12px);">
        <div class="glass-panel" style="width: 100%; max-width: 100%; height: 100vh; display: flex; flex-direction: column; padding: 18px 22px; border: none; background: #0b1120; border-radius: 0; color: #fff; box-shadow: 0 25px 60px rgba(0,0,0,0.6); overflow: hidden;">
            
            <!-- Top Toolbar Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 12px;" class="no-print">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">👁️</span>
                    <div>
                        <h3 style="margin: 0; font-weight: 900; color: #38bdf8; font-size: 1.15rem;">معاينة وتخصيص تصميم كشف حركة المخزن</h3>
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
                    <button type="button" onclick="printWarehouseLedgerSheet()" class="btn-action" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 4px 14px; font-size: 0.8rem; font-weight: 800; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(16,185,129,0.3); height: 28px;">
                        <span>🖨️</span> طباعة
                    </button>
                    <!-- Close Button -->
                    <button type="button" onclick="closeWarehouseLedgerPreview()" class="btn-action" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #e2e8f0; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; cursor: pointer; height: 28px;">
                        ✕ إغلاق
                    </button>
                </div>
            </div>

            <!-- Design Studio Toolbar (Collapsible / Active) -->
            <div id="warehouse-ledger-studio-drawer" class="no-print" style="display: flex; flex-direction: column; gap: 6px; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 8px; padding: 6px 10px; margin-bottom: 6px; box-shadow: inset 0 2px 8px rgba(0,0,0,0.3);">
                
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
                            <input type="checkbox" id="chk-show-meta" checked onchange="applyStudioDesign()"> بيانات المخزن
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
                        <select id="item-selected-col-select" class="panel-input" style="color:#38bdf8; font-size:0.8rem; font-weight:900; border-color:#38bdf8; background:#1e293b; padding: 2px 6px; height: 26px;" onchange="selectWarehouseLedgerCol(this.value)">
                            <option value="date" selected>التاريخ</option>
                            <option value="type">نوع الحركة</option>
                            <option value="warehouse">المخزن</option>
                            <option value="doc">المستند / الإذن</option>
                            <option value="item">الصنف والبيان</option>
                            <option value="in">وارد (دخول)</option>
                            <option value="out">منصرف (خروج)</option>
                        </select>
                        <label style="display:flex; align-items:center; gap:2px; cursor:pointer; font-size:0.8rem; font-weight:800;" title="إظهار أو إخفاء العمود">
                            <input type="checkbox" id="warehouse-col-visible-chk" checked onchange="updateSelectedWarehouseLedgerCol('visible', this.checked)">
                            إظهار
                        </label>
                    </div>

                    <div class="studio-pill-divider"></div>

                    <!-- Group B: Header Label & Ordering -->
                    <div class="studio-group-pill">
                        <span style="font-size:0.8rem;">✏️ التسمية:</span>
                        <input type="text" id="warehouse-col-label-input" class="panel-input" style="width:105px; font-size:0.8rem; padding: 2px 6px; background:#1e293b; color:#fff; border-color:#475569; height: 26px;" oninput="updateSelectedWarehouseLedgerCol('label', this.value)">
                    </div>

                    <div class="studio-pill-divider"></div>

                    <!-- Group C: Alignment -->
                    <div class="studio-group-pill">
                        <span style="font-size:0.8rem;">المحاذاة:</span>
                        <select id="warehouse-col-align-select" class="panel-input" style="font-size:0.8rem; padding: 2px 6px; background:#1e293b; color:#fff; border-color:#475569; height: 26px;" onchange="updateSelectedWarehouseLedgerCol('align', this.value)">
                            <option value="right">يمين ➡️</option>
                            <option value="center" selected>وسط 🟢</option>
                            <option value="left">يسار ⬅️</option>
                        </select>
                    </div>

                    <div class="studio-pill-divider"></div>

                    <!-- Group D: Column Width -->
                    <div class="studio-group-pill">
                        <span style="font-size:0.8rem;" title="عرض العمود كنسبة مئوية">📏 العرض:</span>
                        <input type="range" id="warehouse-col-width-range" min="5" max="50" step="1" value="15" style="width:65px;"
                            oninput="document.getElementById('warehouse-col-width-input').value=this.value; updateSelectedWarehouseLedgerCol('width', this.value)">
                        <input type="number" id="warehouse-col-width-input" class="panel-input" style="width:42px; font-size:0.8rem; padding: 2px; background:#1e293b; color:#fff; border-color:#475569; height: 26px;" min="5" max="50" placeholder="%" 
                            oninput="if(document.getElementById('warehouse-col-width-range')) document.getElementById('warehouse-col-width-range').value=this.value; updateSelectedWarehouseLedgerCol('width', this.value)">
                        <button class="ribbon-btn studio-mini-btn" style="font-size:0.75rem; padding: 2px 5px;" 
                            onclick="updateSelectedWarehouseLedgerCol('width', ''); document.getElementById('warehouse-col-width-input').value=''; document.getElementById('warehouse-col-width-range').value=15;" title="ضبط العرض التلقائي">🔄 تلقائي</button>
                    </div>

                    <div class="studio-pill-divider"></div>

                    <!-- Group E: Table Width -->
                    <div class="studio-group-pill">
                        <span style="font-size:0.8rem;">📐 عرض الجدول:</span>
                        <input type="range" id="warehouse-table-width-range" min="60" max="100" step="5" value="100" style="width:65px;"
                            oninput="setItemTableWidth(this.value)">
                        <span id="warehouse-table-width-label" style="font-weight:900; font-size:0.8rem; min-width:32px; text-align:center; color:#38bdf8;">100%</span>
                    </div>

                </div>
            </div>

            <!-- Scrollable Sheet Container -->
            <div style="flex: 1; overflow-y: auto; background: #0f172a; padding: 25px 15px; display: flex; justify-content: center; border-radius: 12px; box-shadow: inset 0 2px 15px rgba(0,0,0,0.5);" class="preview-scroll-container">
                <div id="warehouse-ledger-sheet" style="width: 100%; max-width: 1180px; background: #ffffff; color: #0f172a; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); border-radius: 6px; min-height: fit-content; box-sizing: border-box; overflow-x: auto; transition: all 0.3s ease;">
                    <!-- Dynamically rendered sheet content -->
                </div>
            </div>

        </div>
    </div>

    <!-- Print CSS Injection -->
    <style id="warehouse-ledger-print-style">
        #warehouse-ledger-preview-modal.print-direct-mode { display: none !important; }
        @page { size: landscape; margin: 8mm; }
        @media print {
            #warehouse-ledger-preview-modal.print-direct-mode { display: block !important; }
            body > *:not(#warehouse-ledger-preview-modal) { display: none !important; }
            #warehouse-ledger-preview-modal { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; height: auto !important; background: #fff !important; padding: 0 !important; margin: 0 !important; display: block !important; overflow: visible !important; }
            #warehouse-ledger-preview-modal .glass-panel { width: 100% !important; max-width: 100% !important; height: auto !important; border: none !important; background: #fff !important; padding: 0 !important; box-shadow: none !important; margin: 0 !important; }
            .global-sidebar, .no-print, .btn-action, .dash-controls { display: none !important; }
            #warehouse-ledger-preview-modal .preview-scroll-container { background: #fff !important; padding: 0 !important; overflow: visible !important; }
            #warehouse-ledger-sheet { box-shadow: none !important; padding: 0 !important; margin: 0 !important; border: none !important; width: 100% !important; max-width: 100% !important; }
            #warehouse-ledger-sheet table { width: 100% !important; border-collapse: collapse !important; table-layout: auto !important; }
            #warehouse-ledger-sheet th, #warehouse-ledger-sheet td { border: 1px solid #94a3b8 !important; page-break-inside: avoid; }
        }
    </style>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
                let isLiveEditActive = false;

        function fmtNum(n) {
            if (n === undefined || n === null || isNaN(n)) return '0';
            const num = Number(n);
            const dec = (num % 1 === 0) ? 0 : (num % 0.01 === 0 ? 2 : 3);
            return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: dec});
        }

        function initWarehouseLedger() {
            try {
                const whSelect = document.getElementById("warehouse-select");
                if (whSelect) {
                    const currentVal = whSelect.value;
                    whSelect.innerHTML = '<option value="ALL">جميع المخازن (كافة الحركات)</option>';
                    
                    let warehousesDb = [];
                    // 1. Storage List
                    if (typeof getStorageList === "function") {
                        warehousesDb = getStorageList("erp_warehouses_v1") || [];
                    }
                    
                    // 2. DB_STORE
                    if ((!warehousesDb || warehousesDb.length === 0) && window.DB_STORE) {
                        const tk = window.getTenantKey ? window.getTenantKey("erp_warehouses_v1") : "erp_warehouses_v1";
                        const raw = window.DB_STORE[tk] || window.DB_STORE["erp_warehouses_v1"];
                        if (raw) {
                            try {
                                const p = typeof raw === "string" ? JSON.parse(raw) : raw;
                                warehousesDb = Array.isArray(p) ? p : Object.values(p);
                            } catch(e){}
                        }
                    }

                    // 3. LocalStorage
                    if (!warehousesDb || warehousesDb.length === 0) {
                        try {
                            const tk = window.getTenantKey ? window.getTenantKey("erp_warehouses_v1") : "erp_warehouses_v1";
                            const raw = localStorage.getItem(tk) || localStorage.getItem("erp_warehouses_v1");
                            if (raw) {
                                const p = JSON.parse(raw);
                                warehousesDb = Array.isArray(p) ? p : Object.values(p);
                            }
                        } catch(e){}
                    }

                    // 4. Fallback Default
                    if (!warehousesDb || warehousesDb.length === 0) {
                        warehousesDb = [{ id: "w-main", name: "المخزن الرئيسي" }];
                    }

                    // 5. Scan all invoices / movements to auto-discover any recorded warehouses
                    const purchases = typeof getStorageList === "function" ? getStorageList("erp_purchase_invoices_v1") : [];
                    const sales = typeof getStorageList === "function" ? getStorageList("erp_sales_invoices_v1") : [];
                    const transfers = typeof getStorageList === "function" ? getStorageList("erp_warehouse_transfers_v1") : [];
                    
                    const seenNames = new Set(warehousesDb.map(w => w.name));
                    const addCustomWh = (whName) => {
                        if (whName && typeof whName === "string" && whName.trim() && whName !== "ALL" && whName !== "⏳ بضاعة قيد الاستلام" && !seenNames.has(whName.trim())) {
                            seenNames.add(whName.trim());
                            warehousesDb.push({ id: whName.trim(), name: whName.trim() });
                        }
                    };

                    purchases.forEach(p => {
                        addCustomWh(p.warehouse);
                        (p.items || []).forEach(item => addCustomWh(item.warehouse));
                    });
                    sales.forEach(s => {
                        addCustomWh(s.warehouse);
                        (s.items || []).forEach(item => addCustomWh(item.warehouse));
                    });
                    transfers.forEach(t => {
                        addCustomWh(t.fromWarehouse);
                        addCustomWh(t.toWarehouse);
                    });

                    // Build Options
                    const displayWh = [{ id: "PENDING_RECEIPT", name: "⏳ بضاعة قيد الاستلام" }, ...warehousesDb];
                    displayWh.forEach(wh => {
                        const opt = document.createElement("option");
                        opt.value = wh.id;
                        opt.text = wh.name;
                        opt.style.background = "#0f172a";
                        opt.style.color = "#ffffff";
                        whSelect.appendChild(opt);
                    });

                    if (currentVal && currentVal !== "ALL") {
                        whSelect.value = currentVal;
                    }
                }

                const today = new Date();
                const dateTo = document.getElementById("date-to");
                if (dateTo && !dateTo.value) {
                    dateTo.valueAsDate = today;
                }

                const urlParams = new URLSearchParams(window.location.search);
                const whParam = urlParams.get("warehouse") || urlParams.get("wh");
                if (whParam && whSelect) {
                    for (let i = 0; i < whSelect.options.length; i++) {
                        if (whSelect.options[i].value === whParam || whSelect.options[i].text === whParam || String(whSelect.options[i].value).toLowerCase() === String(whParam).toLowerCase()) {
                            whSelect.selectedIndex = i;
                            break;
                        }
                    }
                }

                loadLedger();
            } catch(err) {
                console.error("Error in initWarehouseLedger:", err);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWarehouseLedger);
        } else {
            initWarehouseLedger();
        }
        document.addEventListener('DBReady', initWarehouseLedger);
        window.addEventListener('cloudDataRestored', initWarehouseLedger);
        window.addEventListener('strictDataReady', initWarehouseLedger);

        function resolveWarehouseUnitFactor(line, itemObj) {
            if (line.customBaseQty !== undefined && parseFloat(line.quantity) > 0) {
                return parseFloat(line.customBaseQty) / parseFloat(line.quantity);
            }
            if (line.customFactor && parseFloat(line.customFactor) > 0) {
                return parseFloat(line.customFactor);
            }
            if (line.factor && parseFloat(line.factor) > 0) {
                return parseFloat(line.factor);
            }
            if (itemObj) {
                const uName = String(line.unitName || line.unit || line.selectedUnit || '').trim();
                if (uName) {
                    const list = (itemObj.units || []).concat(itemObj.unitsArray || []);
                    const matched = list.find(u => (u.unitName || u.name) === uName);
                    if (matched && parseFloat(matched.factor || matched.conversionFactor) > 0) {
                        return parseFloat(matched.factor || matched.conversionFactor);
                    }
                }
                if (itemObj.conversionFactor && parseFloat(itemObj.conversionFactor) > 0) {
                    return parseFloat(itemObj.conversionFactor);
                }
            }
            return 1;
        }

                function getStorageList(key) {
            let res = [];
            if (window.DB_STORE) {
                if (window.getTenantKey) {
                    const tk = window.getTenantKey(key);
                    if (window.DB_STORE[tk]) {
                        try {
                            const parsed = typeof window.DB_STORE[tk] === 'string' ? JSON.parse(window.DB_STORE[tk]) : window.DB_STORE[tk];
                            if (Array.isArray(parsed)) return parsed;
                            if (typeof parsed === 'object' && parsed !== null) return Object.values(parsed);
                        } catch(e){}
                    }
                }
                if (window.DB_STORE[key]) {
                    try {
                        const parsed = typeof window.DB_STORE[key] === 'string' ? JSON.parse(window.DB_STORE[key]) : window.DB_STORE[key];
                        if (Array.isArray(parsed)) return parsed;
                        if (typeof parsed === 'object' && parsed !== null) return Object.values(parsed);
                    } catch(e){}
                }
            }
            if (typeof window.getTenantKey === 'function') {
                try {
                    const tk = window.getTenantKey(key);
                    const raw = localStorage.getItem(tk);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (Array.isArray(parsed)) return parsed;
                        if (typeof parsed === 'object' && parsed !== null) return Object.values(parsed);
                    }
                } catch(e){}
            }
            try {
                const raw = localStorage.getItem(key);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed)) return parsed;
                    if (typeof parsed === 'object' && parsed !== null) return Object.values(parsed);
                }
            } catch(e){}
            return [];
        }

        function buildRealWarehouseHistory(warehouseId) {
            let history = [];
            
            let itemsDb = getStorageList('erp_items_v1');
            let getItem = (id) => {
                return itemsDb.find(i => String(i.id) === String(id)) || null;
            };

            let warehousesDb = getStorageList('erp_warehouses_v1');
            let getWarehouseName = (nameOrId) => {
                if (!nameOrId) return 'مخزن غير محدد';
                const wh = warehousesDb.find(w => String(w.id) === String(nameOrId) || w.name === nameOrId);
                return wh ? wh.name : nameOrId;
            };
            let getWarehouseId = (nameOrId) => {
                if (!nameOrId) return '';
                const wh = warehousesDb.find(w => String(w.id) === String(nameOrId) || w.name === nameOrId);
                return wh ? wh.id : nameOrId;
            };

            let isWarehouseMatch = (targetId, currentWh) => {
                if (!targetId || targetId === 'ALL') return true;
                const cId = getWarehouseId(currentWh);
                const cName = getWarehouseName(currentWh);
                const tName = getWarehouseName(targetId);
                return String(cId) === String(targetId) || cName === targetId || cName === tName || String(currentWh) === String(targetId);
            };

            // 0. Opening Balances
            itemsDb.forEach(itemObj => {
                if (itemObj.openingBalance) {
                    let wh = getWarehouseId(itemObj.openingBalanceWarehouseId || itemObj.warehouseId);
                    if (isWarehouseMatch(warehouseId, wh)) {
                        let baseQty = parseFloat(itemObj.openingBalance) || 0;
                        if (baseQty > 0) {
                            history.push({
                                date: new Date(itemObj.createdAt || 0),
                                typeCode: 'in',
                                typeName: 'رصيد افتتاحي (أول المدة)',
                                warehouseName: getWarehouseName(wh),
                                ref: 'OPENING',
                                permitNo: 'OPENING',
                                docId: itemObj.id,
                                docTarget: `item-ledger.php?item=${itemObj.id}`,
                                itemId: itemObj.id,
                                itemName: itemObj.name,
                                unit: itemObj.baseUnit || itemObj.unit || 'وحدة',
                                in: baseQty,
                                out: 0,
                                notes: 'رصيد أول المدة مسجل في بطاقة الصنف'
                            });
                        }
                    }
                }
            });

            // 1. Get Purchases & Adjustments
            let purchases = getStorageList('erp_purchase_invoices_v1');
            purchases = purchases.filter(x => !x.deleted);
            purchases.forEach(inv => {
                (inv.items || inv.lines || []).forEach(line => {
                    let wh = getWarehouseId(line.warehouseId || inv.warehouseId || inv.warehouse);
                    if (isWarehouseMatch(warehouseId, wh)) {
                        let itemId = line.itemId || line.productId;
                        let itemObj = getItem(itemId);
                        const origQty = parseFloat(line.quantity) || 0;
                        const origFactor = resolveWarehouseUnitFactor(line, itemObj);
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
                        
                        let permitNo = inv.originalInvoiceId || inv.poNumber || inv.orderNo || inv.invoiceNo || inv.id || '-';
                        let docTarget = inv.isGRN ? `purchase-receipt.php?id=${inv.id}` : `purchase.php?id=${inv.id || inv.invoiceNo}`;

                        history.push({
                            date: new Date(inv.date || inv.receiveDate || Date.now()),
                            typeCode: typeCode,
                            typeName: typeName,
                            warehouseName: getWarehouseName(wh),
                            ref: inv.invoiceNo || inv.id || '-',
                            permitNo: permitNo,
                            docId: inv.id || inv.invoiceNo,
                            docTarget: docTarget,
                            itemId: itemId,
                            itemName: itemObj ? itemObj.name : (line.name || 'صنف غير معروف'),
                            unit: line.unit || (itemObj ? (itemObj.baseUnit || itemObj.unit) : 'وحدة'),
                            in: valIn,
                            out: valOut,
                            notes: line.notes || inv.notes || ''
                        });
                    }
                });
            });

            // 2. Get Sales
            let sales = getStorageList('erp_sales_invoices_v1');
            sales = sales.filter(x => !x.deleted && x.status !== 'draft' && x.status !== 'preliminary');
            sales.forEach(inv => {
                let hdrWh = inv.warehouse;
                
                (inv.items || []).forEach(line => {
                    let lineWh = getWarehouseId(inv.deliveryWarehouse || line.warehouseId || hdrWh);
                    
                    if (isWarehouseMatch(warehouseId, lineWh)) {
                        if (inv.saleType === 'reserved' && inv.deliveryStatus !== 'delivered') {
                            return;
                        }

                        let itemId = line.itemId;
                        let itemObj = getItem(itemId);
                        const origQty = parseFloat(line.quantity) || 0;
                        const origFactor = resolveWarehouseUnitFactor(line, itemObj);
                        let baseQty = (line.customBaseQty !== undefined) ? (parseFloat(line.customBaseQty) || 0) : (origQty * origFactor);

                        let permitNo = inv.orderNo || inv.soNumber || inv.quotationNo || inv.dispatchOrderNo || inv.permitNo || inv.invoiceNo || '-';
                        let docTarget = `sales-invoice.php?id=${inv.id || inv.invoiceNo}`;

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
                            warehouseName: getWarehouseName(lineWh),
                            ref: inv.invoiceNo || inv.id || '-',
                            permitNo: permitNo,
                            docId: inv.id || inv.invoiceNo,
                            docTarget: docTarget,
                            itemId: itemId,
                            itemName: itemObj ? itemObj.name : (line.name || 'صنف غير معروف'),
                            unit: line.unit || (itemObj ? (itemObj.baseUnit || itemObj.unit) : 'وحدة'),
                            in: valIn,
                            out: valOut,
                            notes: line.notes || inv.notes || ''
                        });
                    }
                });
            });

            // 3. Get Warehouse Transfers
            let transfers = getStorageList('erp_warehouse_transfers_v1');
            transfers = transfers.filter(x => !x.deleted && x.status !== 'cancelled');
            transfers.forEach(tr => {
                let fromWh = getWarehouseId(tr.fromWarehouse);
                let toWh = getWarehouseId(tr.toWarehouse);
                let fromWhName = tr.fromWarehouseName || getWarehouseName(fromWh);
                let toWhName = tr.toWarehouseName || getWarehouseName(toWh);

                (tr.items || []).forEach(line => {
                    let itemId = line.itemId;
                    let itemObj = getItem(itemId);
                    let origQty = parseFloat(line.quantity) || 0;
                    let resolvedFactor = parseFloat(line.customFactor || line.factor) || 1;
                    let baseQty = (line.customBaseQty !== undefined) ? parseFloat(line.customBaseQty) : (origQty * resolvedFactor);

                    if (warehouseId === 'ALL' || isWarehouseMatch(warehouseId, fromWh)) {
                        history.push({
                            date: new Date(tr.date || tr.createdAt || Date.now()),
                            typeCode: 'out',
                            typeName: `تحويل صادر (إلى ${toWhName})`,
                            warehouseName: fromWhName,
                            ref: tr.transferNo || tr.id || '-',
                            permitNo: tr.transferNo || '-',
                            docId: tr.id,
                            docTarget: `warehouse-transfers.php?id=${tr.id}`,
                            itemId: itemId,
                            itemName: itemObj ? itemObj.name : (line.name || 'صنف غير معروف'),
                            unit: line.unitName || line.unit || (itemObj ? (itemObj.baseUnit || itemObj.unit) : 'وحدة'),
                            in: 0,
                            out: baseQty,
                            notes: tr.notes || line.notes || ''
                        });
                    }

                    if (warehouseId === 'ALL' || isWarehouseMatch(warehouseId, toWh)) {
                        history.push({
                            date: new Date(tr.date || tr.createdAt || Date.now()),
                            typeCode: 'in',
                            typeName: `تحويل وارد (من ${fromWhName})`,
                            warehouseName: toWhName,
                            ref: tr.transferNo || tr.id || '-',
                            permitNo: tr.transferNo || '-',
                            docId: tr.id,
                            docTarget: `warehouse-transfers.php?id=${tr.id}`,
                            itemId: itemId,
                            itemName: itemObj ? itemObj.name : (line.name || 'صنف غير معروف'),
                            unit: line.unitName || line.unit || (itemObj ? (itemObj.baseUnit || itemObj.unit) : 'وحدة'),
                            in: baseQty,
                            out: 0,
                            notes: tr.notes || line.notes || ''
                        });
                    }
                });
            });

            return history.sort((a,b) => a.date - b.date);
        }

        let currentHistory = [];
        let currentSumIn = 0;
        let currentSumOut = 0;

        function loadLedger() {
try {
            const wh = document.getElementById('warehouse-select')?.value || 'ALL';
            const tbody = document.getElementById('ledger-tbody');
            const searchVal = (document.getElementById('search-filter')?.value || '').toLowerCase().trim();
            
            let history = buildRealWarehouseHistory(wh);

            const fromDateStr = document.getElementById('date-from')?.value;
            const toDateStr = document.getElementById('date-to')?.value;
            
            if (fromDateStr) {
                let d = new Date(fromDateStr); d.setHours(0,0,0,0);
                history = history.filter(h => h.date >= d);
            }
            if (toDateStr) {
                let d = new Date(toDateStr); d.setHours(23,59,59,999);
                history = history.filter(h => h.date <= d);
            }

            if (searchVal) {
                history = history.filter(h => {
                    const searchSource = `${h.itemName} ${h.itemId} ${h.ref} ${h.permitNo} ${h.warehouseName} ${h.typeName} ${h.notes}`.toLowerCase();
                    return searchSource.includes(searchVal);
                });
            }

            currentHistory = history;

            tbody.innerHTML = '';
            let sumIn = 0;
            let sumOut = 0;

            if (history.length === 0) {
                 tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:35px; color:var(--text-secondary); font-size:1.05rem;">لا توجد حركات مطابقة لبحثك في هذه الفترة</td></tr>`;
            }

            history.forEach(row => {
                sumIn += row.in;
                sumOut += row.out;

                let typeBadge = row.typeCode === 'in' 
                    ? `<span class="type-badge type-in">${row.typeName}</span>`
                    : `<span class="type-badge type-out">${row.typeName}</span>`;

                const dStr = (row.date instanceof Date && !isNaN(row.date.getTime()) ? row.date.toISOString() : new Date().toISOString()).split('T')[0];

                const tr = document.createElement('tr');
                let refHtml = row.ref;
                if (row.docId && row.docTarget) {
                    refHtml = `
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="font-weight:700;">${row.ref}</span>
                            <button onclick="event.stopPropagation(); window.open('${row.docTarget}', '_blank')" style="background:rgba(236,72,153,0.1); color:#ec4899; border:1px solid rgba(236,72,153,0.3); border-radius:6px; padding:2px 7px; cursor:pointer; font-size:0.75rem; font-weight:bold; display:flex; align-items:center; gap:3px;">
                                🔗 المستند
                            </button>
                        </div>
                    `;
                }

                tr.innerHTML = `
                    <td style="white-space:nowrap;">${dStr}</td>
                    <td>${typeBadge}</td>
                    <td><span style="background:rgba(255,255,255,0.06); padding:3px 8px; border-radius:6px; font-weight:700; color:#38bdf8;">${row.warehouseName}</span></td>
                    <td style="font-weight:600;">${refHtml}</td>
                    <td>
                        <div style="display:flex; flex-direction:column;">
                            <strong style="color:#fbbf24; font-size:0.95rem;">${row.itemName}</strong>
                            <small style="color:#94a3b8;">كود: ${row.itemId} ${row.notes ? ' · 📝 ' + row.notes : ''}</small>
                        </div>
                    </td>
                    <td style="color:#10b981; font-weight:800; text-align:right; background:rgba(16,185,129,0.02);">${row.in > 0 ? '+ ' + fmtNum(row.in) + ' ' + row.unit : '-'}</td>
                    <td style="color:#ec4899; font-weight:800; text-align:right; background:rgba(236,72,153,0.02);">${row.out > 0 ? '- ' + fmtNum(row.out) + ' ' + row.unit : '-'}</td>
                    <td style="text-align:center;">
                        <a href="item-ledger.php?item=${row.itemId}" class="btn-action" style="padding:4px 8px; font-size:0.75rem; text-decoration:none; display:inline-flex; background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); border-radius:6px; font-weight:700;" title="كشف حساب الصنف التفصيلي">
                            📑 كشف الصنف
                        </a>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            currentSumIn = sumIn;
            currentSumOut = sumOut;

            // Update KPIs
            document.getElementById('kpi-wh-count').innerText = fmtNum(history.length);
            document.getElementById('kpi-wh-in').innerText = fmtNum(sumIn);
            document.getElementById('kpi-wh-out').innerText = fmtNum(sumOut);
            const netFlow = sumIn - sumOut;
            document.getElementById('kpi-wh-net').innerHTML = `<span style="color:${netFlow >= 0 ? '#10b981' : '#ec4899'}">${netFlow >= 0 ? '+' : ''}${fmtNum(netFlow)}</span>`;

            initWarehouseLedgerResizers();
        }

        function initWarehouseLedgerResizers() {
            // ... omitting for brevity if not strictly needed, but let's keep the old table resizer logic 
            const table = document.getElementById('warehouse-ledger-table');
            if (!table) return;
            const resizers = table.querySelectorAll('.resizer');

            resizers.forEach(resizer => {
                let startX = 0;
                let startWidth = 0;
                let th = null;

                const mouseDownHandler = function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    th = resizer.closest('th');
                    if (!th) return;
                    startX = e.clientX;
                    const styles = window.getComputedStyle(th);
                    startWidth = parseInt(styles.width, 10) || 120;

                    resizer.classList.add('resizing');
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';

                    const mouseMoveHandler = function(ev) {
                        const dx = (startX - ev.clientX);
                        let newWidth = startWidth + dx;
                        if (newWidth < 50) newWidth = 50;
                        if (newWidth > 800) newWidth = 800;

                        th.style.width = newWidth + 'px';
                        th.style.minWidth = newWidth + 'px';

                        const colIndex = Array.from(th.parentElement.children).indexOf(th);
                        const rows = document.querySelectorAll('#ledger-tbody tr');
                        rows.forEach(tr => {
                            const cell = tr.children[colIndex];
                            if (cell) {
                                cell.style.width = newWidth + 'px';
                                cell.style.minWidth = newWidth + 'px';
                            }
                        });
                    };

                    const mouseUpHandler = function() {
                        resizer.classList.remove('resizing');
                        document.body.style.cursor = '';
                        document.body.style.userSelect = '';

                        document.removeEventListener('mousemove', mouseMoveHandler);
                        document.removeEventListener('mouseup', mouseUpHandler);
                    };

                    document.addEventListener('mousemove', mouseMoveHandler);
                    document.addEventListener('mouseup', mouseUpHandler);
                };

                const dblClickHandler = function(e) {
                    e.stopPropagation();
                    th = resizer.closest('th');
                    if (!th) return;
                    th.style.width = 'auto';
                    th.style.minWidth = 'max-content';
                    const colIndex = Array.from(th.parentElement.children).indexOf(th);
                    const rows = document.querySelectorAll('#ledger-tbody tr');
                    rows.forEach(tr => {
                        const cell = tr.children[colIndex];
                        if (cell) {
                            cell.style.width = 'auto';
                            cell.style.minWidth = 'max-content';
                        }
                    });
                };

                resizer.removeEventListener('mousedown', resizer._mouseDown);
                resizer.removeEventListener('dblclick', resizer._dblClick);
                resizer._mouseDown = mouseDownHandler;
                resizer._dblClick = dblClickHandler;
                resizer.addEventListener('mousedown', mouseDownHandler);
                resizer.addEventListener('dblclick', dblClickHandler);
            });
        }
        document.addEventListener('DOMContentLoaded', initWarehouseLedgerResizers);

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

        // =======================================================================
        // 🎨 PREVIEW STUDIO JS FOR WAREHOUSE LEDGER
        // =======================================================================

        const THEMES_CONFIG = {
            corporate: { primary: '#1e3a8a', headerBg: '#eff6ff', border: '#bfdbfe', bgBadge: '#dbeafe', textBadge: '#1e40af' },
            emerald:   { primary: '#064e3b', headerBg: '#f0fdf4', border: '#a7f3d0', bgBadge: '#d1fae5', textBadge: '#065f46' },
            gold:      { primary: '#78350f', headerBg: '#fffbeb', border: '#fde68a', bgBadge: '#fef3c7', textBadge: '#92400e' },
            cyan:      { primary: '#164e63', headerBg: '#ecfeff', border: '#a5f3fc', bgBadge: '#cffafe', textBadge: '#155e75' },
            purple:    { primary: '#4c1d95', headerBg: '#f5f3ff', border: '#ddd6fe', bgBadge: '#ede9fe', textBadge: '#5b21b6' },
            classic:   { primary: '#1e293b', headerBg: '#f8fafc', border: '#cbd5e1', bgBadge: '#f1f5f9', textBadge: '#334155' }
        };

        function getSavedDesign() {
            try {
                const s = localStorage.getItem('warehouse_ledger_studio_design_v1');
                if (s) return JSON.parse(s);
            } catch (e) {}
            return null;
        }
        function saveStudioDesignPreset() {
            const state = {
                theme: document.getElementById('studio-theme-select')?.value || 'corporate',
                font: document.getElementById('studio-font-select')?.value || "'Tajawal', sans-serif",
                size: document.getElementById('studio-size-select')?.value || 'medium',
                orientation: document.getElementById('studio-orientation-select')?.value || 'landscape',
                showHeader: document.getElementById('chk-show-header')?.checked ?? true,
                showMeta: document.getElementById('chk-show-meta')?.checked ?? true,
                showKPIs: document.getElementById('chk-show-kpis')?.checked ?? true,
                showSignatures: document.getElementById('chk-show-signatures')?.checked ?? true,
                colsWidths: {},
                colsAligns: {},
                colsLabels: {},
                colsVis: {},
                tableWidth: document.getElementById('warehouse-table-width-range')?.value || 100
            };
            const sheet = document.getElementById('warehouse-ledger-sheet');
            if (sheet) {
                const colKeys = ['date', 'type', 'warehouse', 'doc', 'item', 'in', 'out'];
                colKeys.forEach(k => {
                    const firstCell = sheet.querySelector(`.col-${k}`);
                    if (firstCell) {
                        state.colsWidths[k] = firstCell.style.width || 'auto';
                        state.colsAligns[k] = firstCell.style.textAlign || 'center';
                        state.colsVis[k] = firstCell.style.display !== 'none';
                    }
                    const th = sheet.querySelector(`th.col-${k}`);
                    if (th) {
                        state.colsLabels[k] = th.innerText.trim();
                    }
                });
            }
            localStorage.setItem('warehouse_ledger_studio_design_v1', JSON.stringify(state));
            alert('تم حفظ إعدادات التصميم بنجاح.');
        }

        function resetStudioDesignPreset() {
            localStorage.removeItem('warehouse_ledger_studio_design_v1');
            location.reload();
        }

        function loadDesignIntoControls(design) {
            if (!design) return;
            if (document.getElementById('studio-theme-select')) document.getElementById('studio-theme-select').value = design.theme;
            if (document.getElementById('studio-font-select')) document.getElementById('studio-font-select').value = design.font;
            if (document.getElementById('studio-size-select')) document.getElementById('studio-size-select').value = design.size;
            if (document.getElementById('studio-orientation-select')) document.getElementById('studio-orientation-select').value = design.orientation;
            
            if (document.getElementById('chk-show-header')) document.getElementById('chk-show-header').checked = design.showHeader;
            if (document.getElementById('chk-show-meta')) document.getElementById('chk-show-meta').checked = design.showMeta;
            if (document.getElementById('chk-show-kpis')) document.getElementById('chk-show-kpis').checked = design.showKPIs;
            if (document.getElementById('chk-show-signatures')) document.getElementById('chk-show-signatures').checked = design.showSignatures;
            
            if (document.getElementById('warehouse-table-width-range')) {
                document.getElementById('warehouse-table-width-range').value = design.tableWidth;
                document.getElementById('warehouse-table-width-label').innerText = design.tableWidth + '%';
            }

            const sheet = document.getElementById('warehouse-ledger-sheet');
            if (sheet && design.colsVis) {
                Object.keys(design.colsVis).forEach(k => {
                    sheet.querySelectorAll(`.col-${k}`).forEach(el => {
                        el.style.display = design.colsVis[k] ? '' : 'none';
                    });
                });
                Object.keys(design.colsWidths || {}).forEach(k => {
                    sheet.querySelectorAll(`.col-${k}`).forEach(el => el.style.width = design.colsWidths[k]);
                });
                Object.keys(design.colsAligns || {}).forEach(k => {
                    sheet.querySelectorAll(`.col-${k}`).forEach(el => el.style.textAlign = design.colsAligns[k]);
                });
                Object.keys(design.colsLabels || {}).forEach(k => {
                    const th = sheet.querySelector(`th.col-${k}`);
                    if (th) th.innerText = design.colsLabels[k];
                });
            }
        }

        function toggleDesignStudioDrawer() {
            const drawer = document.getElementById('warehouse-ledger-studio-drawer');
            if (drawer) drawer.style.display = (drawer.style.display === 'none') ? 'flex' : 'none';
        }

        function toggleLiveEditMode() {
            isLiveEditActive = !isLiveEditActive;
            const btn = document.getElementById('btn-toggle-live-edit');
            const sheet = document.getElementById('warehouse-ledger-sheet');
            
            if (isLiveEditActive) {
                btn.style.background = 'rgba(245,158,11,0.3)';
                btn.innerHTML = '<span>✅</span> إنهاء التعديل';
                if (sheet) {
                    sheet.querySelectorAll('.editable-text').forEach(el => {
                        el.setAttribute('contenteditable', 'true');
                        el.style.borderBottom = '1px dashed #fbbf24';
                        el.style.outline = 'none';
                    });
                }
            } else {
                btn.style.background = 'rgba(245,158,11,0.15)';
                btn.innerHTML = '<span>✏️</span> تعديل النصوص';
                if (sheet) {
                    sheet.querySelectorAll('.editable-text').forEach(el => {
                        el.removeAttribute('contenteditable');
                        el.style.borderBottom = 'none';
                    });
                }
            }
        }

        function openWarehouseLedgerPreview() {
            const whSelect = document.getElementById('warehouse-select');
            const whName = whSelect && whSelect.options[whSelect.selectedIndex] ? whSelect.options[whSelect.selectedIndex].text : 'جميع المخازن';
            
            let cmpName = localStorage.getItem('erp_company_name') || 'مؤسسة أطياف التقنية للتجارة';
            let cmpReg = localStorage.getItem('erp_company_reg') || '';
            let cmpTax = localStorage.getItem('erp_company_tax') || '';
            let cmpPhone = localStorage.getItem('erp_company_phone') || '0500000000';
            let cmpAddress = localStorage.getItem('erp_company_address') || 'الرياض، المملكة العربية السعودية';
            let cmpLogo = localStorage.getItem('erp_company_logo') || '';
            let printedAt = new Date().toLocaleString('en-US', {hour12: true});
            
            const fromDateStr = document.getElementById('date-from')?.value;
            const toDateStr = document.getElementById('date-to')?.value;
            let periodStr = 'الكل';
            if (fromDateStr && toDateStr) periodStr = `من ${fromDateStr} إلى ${toDateStr}`;
            else if (fromDateStr) periodStr = `من ${fromDateStr}`;
            else if (toDateStr) periodStr = `حتى ${toDateStr}`;

            let rowsHtml = '';
            let runningBalance = 0;

            currentHistory.forEach((row, i) => {
                runningBalance += (row.in - row.out);
                let badgeClass = row.typeCode === 'in' ? 'badge-in' : 'badge-out';
                
                rowsHtml += `
                    <tr>
                        <td class="col-date editable-text" style="padding:8px 6px; border:1px solid #94a3b8; text-align:center;">${(row.date instanceof Date && !isNaN(row.date.getTime()) ? row.date.toISOString() : new Date().toISOString()).split('T')[0]}</td>
                        <td class="col-type editable-text" style="padding:8px 6px; border:1px solid #94a3b8; text-align:center;">
                            <span class="sheet-badge ${badgeClass}">${row.typeName}</span>
                        </td>
                        <td class="col-warehouse editable-text" style="padding:8px 6px; border:1px solid #94a3b8; text-align:center;">${row.warehouseName}</td>
                        <td class="col-doc editable-text" style="padding:8px 6px; border:1px solid #94a3b8; text-align:center; font-weight:700;">${row.ref}</td>
                        <td class="col-item editable-text" style="padding:8px 6px; border:1px solid #94a3b8; text-align:right;">
                            <strong style="color:#0f172a;">${row.itemName}</strong><br>
                            <small style="color:#64748b;">${row.itemId}</small>
                        </td>
                        <td class="col-in editable-text" style="padding:8px 6px; border:1px solid #94a3b8; text-align:center; font-weight:800; color:#15803d; background:#f0fdf4;">${row.in > 0 ? fmtNum(row.in) + ' ' + row.unit : '-'}</td>
                        <td class="col-out editable-text" style="padding:8px 6px; border:1px solid #94a3b8; text-align:center; font-weight:800; color:#be185d; background:#fdf2f8;">${row.out > 0 ? fmtNum(row.out) + ' ' + row.unit : '-'}</td>
                    </tr>
                `;
            });

            if (currentHistory.length === 0) {
                rowsHtml = `<tr><td colspan="7" style="padding:30px; text-align:center; color:#64748b;">لا توجد حركات لهذه الفترة.</td></tr>`;
            }

            const headerHtml = `
                <tr class="ledger-sheet-thead-row" style="background:#1e3a8a; color:#fff;">
                    <th class="col-date editable-text" style="padding:10px 6px; border:1px solid #bfdbfe; font-weight:800; text-align:center;">التاريخ</th>
                    <th class="col-type editable-text" style="padding:10px 6px; border:1px solid #bfdbfe; font-weight:800; text-align:center;">نوع الحركة</th>
                    <th class="col-warehouse editable-text" style="padding:10px 6px; border:1px solid #bfdbfe; font-weight:800; text-align:center;">المخزن</th>
                    <th class="col-doc editable-text" style="padding:10px 6px; border:1px solid #bfdbfe; font-weight:800; text-align:center;">المستند / الإذن</th>
                    <th class="col-item editable-text" style="padding:10px 6px; border:1px solid #bfdbfe; font-weight:800; text-align:right;">الصنف والبيان</th>
                    <th class="col-in editable-text" style="padding:10px 6px; border:1px solid #bfdbfe; font-weight:800; text-align:center;">وارد (دخول)</th>
                    <th class="col-out editable-text" style="padding:10px 6px; border:1px solid #bfdbfe; font-weight:800; text-align:center;">منصرف (خروج)</th>
                </tr>
            `;

            const sheetHtml = `
                <!-- 1. Header Section -->
                <div id="sheet-header-block" style="display:flex; justify-content:space-between; align-items:center; border-bottom:3px double #0f172a; padding-bottom:16px; margin-bottom:20px; direction:rtl;">
                    <div style="display:flex; align-items:center; gap:14px; width:35%;">
                        ${cmpLogo ? \`<img src="${cmpLogo}" style="max-height:65px; max-width:90px; object-fit:contain; border-radius:8px;">\` : ''}
                        <div>
                            <h2 style="margin:0; font-size:1.35rem; font-weight:900; color:#0f172a;" class="editable-text">${cmpName}</h2>
                            <div style="font-size:0.75rem; color:#475569; margin-top:3px;">
                                ${cmpReg ? \`<span>س.ت: ${cmpReg}</span> | \` : ''}
                                ${cmpTax ? \`<span>ر.ض : ${cmpTax}</span>\` : ''}
                            </div>
                            <div style="font-size:0.75rem; color:#475569;">${cmpAddress} | هاتف: ${cmpPhone}</div>
                        </div>
                    </div>

                    <div style="text-align:center; width:30%;">
                        <div class="sheet-title-box" style="border:2px solid #0f172a; padding:8px 20px; border-radius:8px; background:#f8fafc; display:inline-block;">
                            <h3 style="margin:0; font-size:1.2rem; font-weight:900; color:#0f172a; white-space:nowrap;" class="editable-text">📊 كشف حركة المخزن التفصيلي</h3>
                            <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">(مطابقة الداخل والخارج للمخازن)</div>
                        </div>
                    </div>

                    <div style="text-align:left; width:35%; font-size:0.78rem; color:#475569; line-height:1.6;">
                        <div>تاريخ الطباعة: <strong>${printedAt}</strong></div>
                        <div>الفترة المحددة: <strong>${periodStr}</strong></div>
                        <div>المخزن: <strong>${whName}</strong></div>
                    </div>
                </div>

                <!-- 2. Metadata Card -->
                <div id="sheet-meta-block" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:12px 18px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; font-size:0.85rem;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:1.2rem;">🏢</span>
                        <div>
                            <span style="color:#64748b; font-size:0.75rem;">اسم المخزن المحدد:</span><br>
                            <strong style="color:#0f172a; font-size:1rem;">${whName}</strong>
                        </div>
                    </div>
                    <div>
                        <span style="color:#64748b; font-size:0.75rem;">إجمالي الحركات:</span><br>
                        <strong style="color:#0f172a;">${currentHistory.length} حركة</strong>
                    </div>
                    <div>
                        <span style="color:#64748b; font-size:0.75rem;">الفترة المحددة:</span><br>
                        <strong style="color:#0f172a;">${periodStr}</strong>
                    </div>
                </div>

                <!-- 3. KPI Balance Cards -->
                <div id="sheet-kpis-block" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-bottom:20px;">
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; text-align:center;">
                        <div style="font-size:0.75rem; color:#15803d; font-weight:700;">إجمالي الوارد (مشتريات/استلام/تحويل) 📥</div>
                        <div style="font-size:1.05rem; font-weight:900; color:#16a34a; margin-top:4px;">${fmtNum(currentSumIn)}</div>
                    </div>
                    <div style="background:#fdf2f8; border:1px solid #fbcfe8; border-radius:8px; padding:10px 14px; text-align:center;">
                        <div style="font-size:0.75rem; color:#be185d; font-weight:700;">إجمالي المنصرف (مبيعات/تسليم/تحويل) 📤</div>
                        <div style="font-size:1.05rem; font-weight:900; color:#db2777; margin-top:4px;">${fmtNum(currentSumOut)}</div>
                    </div>
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; text-align:center;">
                        <div style="font-size:0.75rem; color:#b45309; font-weight:700;">صافي الحركة ⚖️</div>
                        <div style="font-size:1.05rem; font-weight:900; color:#d97706; margin-top:4px;">${fmtNum(currentSumIn - currentSumOut)}</div>
                    </div>
                </div>

                <!-- 4. Detailed Movement Table -->
                <div id="sheet-table-block" style="margin-bottom:25px;">
                    <table class="ledger-sheet-table" style="width:100%; border-collapse:collapse; text-align:right; font-size:0.85rem; direction:rtl;">
                        <thead>
                            ${headerHtml}
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc; font-weight:900; border-top:2px solid #0f172a;">
                                <td colspan="5" style="padding:10px; text-align:left; border:1px solid #94a3b8;">الإجماليات:</td>
                                <td class="col-in" style="border:1px solid #94a3b8; text-align:center; color:#15803d;">${fmtNum(currentSumIn)}</td>
                                <td class="col-out" style="border:1px solid #94a3b8; text-align:center; color:#be185d;">${fmtNum(currentSumOut)}</td>
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
                        <strong style="color:#0f172a;">📝 المحاسب المختص</strong>
                        <div style="margin-top:28px; border-top:1px solid #cbd5e1; padding-top:4px;">الاسم والتوقيع</div>
                    </div>
                    <div style="border:1px solid #e2e8f0; border-radius:6px; padding:10px; background:#fafafa;">
                        <strong style="color:#0f172a;">🔍 المراجع المالي</strong>
                        <div style="margin-top:28px; border-top:1px solid #cbd5e1; padding-top:4px;">الاسم والتوقيع</div>
                    </div>
                    <div style="border:1px solid #e2e8f0; border-radius:6px; padding:10px; background:#fafafa;">
                        <strong style="color:#0f172a;">✅ مدير الإدارة / الفرع</strong>
                        <div style="margin-top:28px; border-top:1px solid #cbd5e1; padding-top:4px;">توقيع الاعتماد</div>
                    </div>
                </div>

                <!-- 6. Footer Disclaimer -->
                <div style="margin-top:25px; border-top:1px solid #e2e8f0; padding-top:8px; display:flex; justify-content:space-between; font-size:0.72rem; color:#94a3b8; direction:rtl;">
                    <div>تم استخراج هذا الكشف من النظام المحاسبي الآلي بواسطة ERP ماجايس © MAGAIEES CLOUD</div>
                    <div>صفحة 1 من 1</div>
                </div>
`;

            const sheet = document.getElementById('warehouse-ledger-sheet');
            sheet.innerHTML = sheetHtml;

            const design = getSavedDesign();
            loadDesignIntoControls(design);
            applyStudioDesign();

            const modal = document.getElementById('warehouse-ledger-preview-modal');
            if (modal) modal.style.display = 'flex';
        }

        function closeWarehouseLedgerPreview() {
            const modal = document.getElementById('warehouse-ledger-preview-modal');
            if (modal) modal.style.display = 'none';
        }

        function applyStudioDesign() {
            const sheet = document.getElementById('warehouse-ledger-sheet');
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

            sheet.style.fontFamily = font;

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

            if (orientation === 'portrait') {
                sheet.style.maxWidth = '850px';
            } else {
                sheet.style.maxWidth = '1180px';
            }

            const printStyle = document.getElementById('warehouse-ledger-print-style');
            if (printStyle) {
                printStyle.innerHTML = `
                    #warehouse-ledger-preview-modal.print-direct-mode { display: none !important; }
                    @page { size: ${orientation}; margin: 8mm; }
                    @media print {
                        #warehouse-ledger-preview-modal.print-direct-mode { display: block !important; }
                        body > *:not(#warehouse-ledger-preview-modal) { display: none !important; }
                        #warehouse-ledger-preview-modal { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; height: auto !important; background: #fff !important; padding: 0 !important; margin: 0 !important; display: block !important; overflow: visible !important; }
                        #warehouse-ledger-preview-modal .glass-panel { width: 100% !important; max-width: 100% !important; height: auto !important; border: none !important; background: #fff !important; padding: 0 !important; box-shadow: none !important; margin: 0 !important; }
                        .global-sidebar, .no-print, .btn-action, .dash-controls { display: none !important; }
                        #warehouse-ledger-preview-modal .preview-scroll-container { background: #fff !important; padding: 0 !important; overflow: visible !important; }
                        #warehouse-ledger-sheet { box-shadow: none !important; padding: 0 !important; margin: 0 !important; border: none !important; width: 100% !important; max-width: 100% !important; }
                        #warehouse-ledger-sheet table { width: 100% !important; border-collapse: collapse !important; table-layout: auto !important; }
                        #warehouse-ledger-sheet th, #warehouse-ledger-sheet td { border: 1px solid #94a3b8 !important; page-break-inside: avoid; }
                    }
                `;
            }

            const titleBox = sheet.querySelector('.sheet-title-box');
            if (titleBox) {
                titleBox.style.borderColor = theme.primary;
                titleBox.style.background = theme.headerBg;
                const h3 = titleBox.querySelector('h3');
                if (h3) h3.style.color = theme.primary;
            }

            const theadRows = sheet.querySelectorAll('.ledger-sheet-thead-row th');
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

            const hBlock = document.getElementById('sheet-header-block');
            if (hBlock) hBlock.style.display = showHeader ? 'flex' : 'none';

            const kBlock = document.getElementById('sheet-kpis-block');
            if (kBlock) kBlock.style.display = showKPIs ? 'grid' : 'none';

            const sBlock = document.getElementById('sheet-signatures-block');
            if (sBlock) sBlock.style.display = showSignatures ? 'grid' : 'none';

                        // Hook: Sync column visibility with Main Screen presets (ursColumns)
            if (typeof ursColumns !== 'undefined') {
                ursColumns.forEach((col, idx) => {
                    const colKeys = ['date', 'type', 'warehouse', 'doc', 'item', 'in', 'out'];
                    const mappedKey = colKeys[idx];
                    if (mappedKey) {
                        sheet.querySelectorAll(`.col-${mappedKey}`).forEach(el => {
                            el.style.display = col.visible ? '' : 'none';
                        });
                        // also sync the modal checkboxes
                        const chk = document.querySelector(`.col-toggle[data-col="${mappedKey}"]`);
                        if(chk) chk.checked = col.visible;
                    }
                });
            }
            const colToggles = document.querySelectorAll('.col-toggle');
            colToggles.forEach(chk => {
                const colKey = chk.getAttribute('data-col');
                const isChecked = chk.checked;
                sheet.querySelectorAll(`.col-${colKey}`).forEach(el => {
                    el.style.display = isChecked ? '' : 'none';
                });
            });
        }

        let currentSelectedWarehouseLedgerCol = 'date';
        function selectWarehouseLedgerCol(colKey) {
            currentSelectedWarehouseLedgerCol = colKey;
            const sheet = document.getElementById('warehouse-ledger-sheet');
            if (!sheet) return;
            const firstCell = sheet.querySelector(`.col-${colKey}`);
            const isVis = firstCell ? (firstCell.style.display !== 'none') : true;
            const chk = document.getElementById('warehouse-col-visible-chk');
            if (chk) chk.checked = isVis;

            const headerCell = sheet.querySelector(`th.col-${colKey}`);
            const lblInput = document.getElementById('warehouse-col-label-input');
            if (lblInput && headerCell) lblInput.value = headerCell.innerText.trim();
        }

        function updateSelectedWarehouseLedgerCol(prop, val) {
            const sheet = document.getElementById('warehouse-ledger-sheet');
            if (!sheet) return;
            if (prop === 'visible') {
                sheet.querySelectorAll(`.col-${currentSelectedWarehouseLedgerCol}`).forEach(el => {
                    el.style.display = val ? '' : 'none';
                });
            } else if (prop === 'label') {
                const headerCell = sheet.querySelector(`th.col-${currentSelectedWarehouseLedgerCol}`);
                if (headerCell) headerCell.innerText = val;
            } else if (prop === 'align') {
                sheet.querySelectorAll(`.col-${currentSelectedWarehouseLedgerCol}`).forEach(el => {
                    el.style.textAlign = val;
                });
            } else if (prop === 'width') {
                sheet.querySelectorAll(`.col-${currentSelectedWarehouseLedgerCol}`).forEach(el => {
                    el.style.width = val ? (val + '%') : 'auto';
                });
            }
        }

        function setItemTableWidth(val) {
            const sheet = document.getElementById('warehouse-ledger-sheet');
            const tbl = sheet ? sheet.querySelector('table') : null;
            const lbl = document.getElementById('warehouse-table-width-label');
            if (tbl) tbl.style.width = val + '%';
            if (lbl) lbl.textContent = val + '%';
        }

        function printWarehouseLedgerSheet() {
            openWarehouseLedgerPreview();
            if (isLiveEditActive) {
                toggleLiveEditMode();
            }
            setTimeout(() => {
                window.print();
            }, 100);
        }</script>
<script src="js/universal-report-studio.js?v=<?php echo time(); ?>"></script>
<script>
// ── Preset Buttons: Apply preset then open preview page directly ──
function applyAndOpenPreview(index, btn) {
    document.querySelectorAll('.preset-pill-btn').forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    if (typeof window.applyReportPreset === 'function') {
        window.applyReportPreset(index, btn);
    }
    setTimeout(function() {
        if (typeof window.openStudioPreviewPage === 'function') {
            window.openStudioPreviewPage();
        } else {
            try {
                var table = document.querySelector('#report-table-wrap table') || document.querySelector('.dash-table') || document.querySelector('#warehouse-ledger-table');
                if (table) {
                    var clone = table.cloneNode(true);
                    clone.querySelectorAll('input, select, button, .btn-action').forEach(function(el) {
                        if (el.tagName === 'INPUT' || el.tagName === 'SELECT') {
                            var span = document.createElement('span');
                            span.textContent = el.value || '';
                            el.replaceWith(span);
                        } else { el.remove(); }
                    });
                    sessionStorage.setItem('urs_preview_table_html', clone.outerHTML);
                }
                sessionStorage.setItem('urs_preview_source', 'warehouse-ledger');
                var titleEl = document.querySelector('.content-title h2') || document.querySelector('h2');
                sessionStorage.setItem('urs_preview_title', titleEl ? titleEl.textContent.trim() : 'دفتر أستاذ المستودع');
                window.open('preview-inventory-report.php?source=warehouse-ledger&presetIndex=' + index, '_blank');
            } catch(e) { window.open('preview-inventory-report.php', '_blank'); }
        }
    }, 150);
}
</script>
</body>
</html>

