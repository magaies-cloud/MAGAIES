<?php $page_title = 'المحاسبة - سير العمليات'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المحاسبة - سير العمليات</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <style>
        body { margin: 0; padding: 0; background: var(--bg-color); height: 100vh; overflow: hidden; display: flex; flex-direction: column; font-family: var(--font-base); }
        
        /* Top Navbar */
        .top-nav {
            background: var(--surface-color); padding: 10px 20px;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 2px solid var(--primary-accent); box-shadow: var(--shadow-sm); z-index: 10;
        }
        .top-nav .title { font-weight: 900; font-size: 1.2rem; color: var(--primary-accent); }
        .back-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-primary); }

        /* Main Workspace */
        .workspace {
            display: flex; flex: 1; overflow: hidden;
            background: linear-gradient(to bottom, rgba(255,255,255,0.05), transparent);
        }

        /* Side Panels (Reports & Definitions) */
        .side-panel {
            width: 200px; background: var(--surface-color);
            border-left: 1px solid var(--glass-border); border-right: 1px solid var(--glass-border);
            display: flex; flex-direction: column; z-index: 5;
        }
        .panel-header {
            background: var(--primary-accent); color: white; padding: 8px;
            text-align: center; font-weight: bold; font-size: 0.9rem;
        }
        .panel-content { padding: 10px; overflow-y: auto; flex: 1; }
        
        .side-btn {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 10px; margin-bottom: 8px;
            background: var(--bg-color); border: 1px solid var(--glass-border);
            border-radius: 8px; cursor: pointer; text-align: right;
            font-size: 0.85rem; font-weight: bold; color: var(--text-primary);
            transition: all 0.2s;
        }
        .side-btn:hover { background: var(--primary-accent); color: white; transform: translateX(-5px); }
        .side-btn .icon { font-size: 1.2rem; }

        /* Professional Reports Panel styling to match ERP Screenshot */
        .reports-header {
            background: linear-gradient(to bottom, #f8fafc, #e2e8f0);
            color: #1e3a8a; padding: 10px 15px; display: flex; justify-content: space-between;
            align-items: center; font-weight: 900; font-size: 1.1rem; border-bottom: 1px solid #cbd5e1;
        }
        .reports-header .nav-arrow { cursor: pointer; color: #1e3a8a; font-size: 1.2rem; }

        .report-btn {
            display: flex; align-items: center; justify-content: space-between;
            width: 100%; padding: 15px 5px; margin-bottom: 5px;
            background: transparent; border: none; cursor: pointer;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .report-btn:hover { background: rgba(0,0,0,0.03); transform: scale(1.02); }
        .report-btn .label { font-weight: 900; font-size: 0.95rem; color: #1e3a8a; flex: 1; text-align: right; margin-right: 15px; }
        .report-btn .icon-container { 
            font-size: 2.5rem; display: flex; align-items: center; gap: 20px; 
            filter: drop-shadow(0 5px 5px rgba(0,0,0,0.2));
        }
        .report-btn .flow-arrow { font-size: 1.8rem; color: #64748b; opacity: 0.6; }

        /* Center Canvas (Workflow Flowchart) */
        .center-canvas {
            flex: 1; position: relative; padding: 20px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            background-image: radial-gradient(rgba(0,0,0,0.05) 2px, transparent 2px);
            background-size: 30px 30px; overflow: auto;
        }
        .canvas-title {
            position: absolute; top: 10px; right: 20px;
            font-size: 1.2rem; font-weight: 900; color: var(--text-secondary); opacity: 0.5;
        }

        /* Flowchart Nodes */
        .flow-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 40px 60px; align-items: center; justify-items: center;
            position: relative;
        }
        .node {
            background: var(--surface-color); padding: 15px;
            border-radius: 12px; border: 2px solid var(--glass-border);
            box-shadow: var(--shadow-md); text-align: center; cursor: pointer;
            width: 140px; transition: transform 0.3s, border-color 0.3s;
            position: relative; z-index: 2; text-decoration: none; display: block;
        }
        .node:hover { transform: scale(1.05) translateY(-5px); border-color: var(--primary-accent); }
        .node .icon { font-size: 2.5rem; margin-bottom: 10px; display: block; }
        .node .label { font-size: 0.9rem; font-weight: bold; color: var(--text-primary); }

        /* Flowchart Arrows */
        .arrow-right::after, .arrow-left::after, .arrow-down::after, .arrow-up::after {
            content: '➔'; position: absolute; font-size: 2rem; color: var(--primary-accent);
            opacity: 0.5; z-index: 1;
        }
        .arrow-left::after { left: -45px; top: 35%; transform: rotate(180deg); }
        .arrow-right::after { right: -45px; top: 35%; }
        .arrow-down::after { bottom: -35px; left: 45%; transform: rotate(90deg); }
        .arrow-up::after { top: -35px; left: 45%; transform: rotate(-90deg); }

        /* Bottom Module Switcher */
        .bottom-toolbar {
            background: linear-gradient(to right, #1e293b, #0f172a);
            padding: 8px 10px; display: flex; justify-content: center; gap: 5px;
            border-top: 3px solid var(--primary-accent); z-index: 10;
            overflow-x: auto;
        }
        .module-tab {
            padding: 8px 20px; border-radius: 6px 6px 0 0;
            background: rgba(255,255,255,0.1); color: #94a3b8;
            font-weight: bold; cursor: pointer; border: none; font-size: 0.9rem;
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            transition: all 0.3s; min-width: 80px;
        }
        .module-tab:hover { background: rgba(255,255,255,0.2); color: white; }
        .module-tab.active { background: var(--surface-color); color: var(--primary-accent); border-top: 3px solid var(--primary-accent); }
        .module-tab .icon { font-size: 1.4rem; }

        @media(max-width: 900px) {
            .workspace { flex-direction: column; overflow: auto; }
            .side-panel { width: 100%; border: none; border-bottom: 2px solid var(--primary-accent); }
            .flow-grid { grid-template-columns: 1fr; gap: 20px; }
            .arrow-left::after, .arrow-right::after { display: none; } /* Hide horizontal arrows on mobile stack */
        }
    
        
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
\n</style>
</head>
<body onload="applySavedTheme()">

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>


    <nav class="top-nav">
        <button class="back-btn" onclick="window.location.href='index.php'">🏠 الرئيسية</button>
        <div class="title">النظام المحاسبي (Accounting Module)</div>
        <div>
            <button onclick="window.print()" style="background:none; border:none; cursor:pointer; font-size:1.2rem;">🖨️</button>
        </div>
    </nav>

    <div class="workspace">
        
        <!-- Right Panel: Definitions -->
        <div class="side-panel">
            <div class="panel-header">التعريفات الأساسية</div>
            <div class="panel-content">
                <button class="side-btn"><span class="icon">📁</span> دليل الحسابات</button>
                <button class="side-btn"><span class="icon">🏢</span> مراكز التكلفة</button>
                <button class="side-btn"><span class="icon">📅</span> الفترات المالية</button>
                <button class="side-btn"><span class="icon">🏦</span> تعريف البنوك</button>
                <button class="side-btn"><span class="icon">⚙️</span> أنواع السندات</button>
                <button class="side-btn"><span class="icon">💱</span> العملات الأجنبية</button>
            </div>
        </div>

        <!-- Center Canvas: Operations Flowchart -->
        <div class="center-canvas">
            <div class="canvas-title">مخطط العمليات المحاسبية</div>
            
            <div class="flow-grid">
                
                <!-- Row 1 -->
                <a data-href="payment-voucher.php" onclick="window.location.href='payment-voucher.php'" class="node arrow-down">
                    <span class="icon">📤</span>
                    <span class="label">سند صرف</span>
                </a>
                
                <a href="journal-voucher.php" class="node arrow-down arrow-right arrow-left">
                    <span class="icon">⚖️</span>
                    <span class="label">قيد يومية</span>
                </a>

                <a data-href="cash-receipt.php" onclick="window.location.href='cash-receipt.php'" class="node arrow-down">
                    <span class="icon">📥</span>
                    <span class="label">سند قبض</span>
                </a>

                <!-- Row 2 -->
                <a href="#" class="node">
                    <span class="icon">📓</span>
                    <span class="label">يومية الصندوق</span>
                </a>

                <a href="#" class="node arrow-up arrow-down">
                    <span class="icon">🔄</span>
                    <span class="label">الترحيل (Posting)</span>
                </a>

                <a href="#" class="node">
                    <span class="icon">🏦</span>
                    <span class="label">يومية البنك</span>
                </a>

                <!-- Row 3 -->
                <a href="#" class="node">
                    <span class="icon">📊</span>
                    <span class="label">ميزان المراجعة</span>
                </a>

                <a href="#" class="node">
                    <span class="icon">📈</span>
                    <span class="label">الأرباح والخسائر</span>
                </a>

                <a href="#" class="node">
                    <span class="icon">🏛️</span>
                    <span class="label">المركز المالي</span>
                </a>

            </div>
        </div>



    </div>

    <!-- Bottom Module Switcher -->
    <div class="bottom-toolbar">
        <button class="module-tab active"><span class="icon">🧮</span>المحاسبة</button>
        <button class="module-tab" onclick="window.location.href='index.php'"><span class="icon">🏠</span>الرئيسية</button>
        <button class="module-tab" onclick="window.location.href='direct-sales.php'"><span class="icon">🛒</span>المبيعات</button>
        <button class="module-tab" onclick="window.location.href='purchase.php'"><span class="icon">📦</span>المشتريات</button>
        <button class="module-tab" onclick="window.location.href='inventory.php'"><span class="icon">🏭</span>المخزون</button>
        <button class="module-tab" onclick="window.location.href='financial-reports.php'"><span class="icon">📊</span>التقارير</button>
    </div>

    <script>
        function applySavedTheme() {
            const savedTheme = localStorage.getItem('ui_theme');
            if(savedTheme) {
                document.body.classList.add(savedTheme);
            }
        }
    </script>

<script src="js/magaiees_back.js?v=2026_back_v1" defer></script>
</body>
</html>
