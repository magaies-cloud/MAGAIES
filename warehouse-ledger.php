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

        <!-- Top Navigation Row (Clustered Smart Hub + Action Buttons) -->
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
                <div class="nav-cluster nav-dropdown active">
                    <div class="nav-tab active" title="حركات البضائع">
                        <span>📑</span> <span>حركات البضائع (حركة المخازن)</span>
                        <span style="font-size: 0.7rem; margin-right: 2px;">▼</span>
                    </div>
                    <div class="nav-dropdown-content">
                        <a href="item-ledger.php" class="nav-dropdown-item" title="كشف حركة صنف محدد">
                            <span>📑</span> حركة الأصناف
                        </a>
                        <a href="warehouse-ledger.php" class="nav-dropdown-item active" style="color: #38bdf8; background: rgba(56,189,248,0.1);" title="كشف حركة مخزن محدد">
                            <span>🏭</span> حركة المخازن (الحالية)
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

                <!-- Cluster 4: التحليلات المرئية -->
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
                            <span>🍩</span> مستويات الأمان والتنبيهات
                        </a>
                    </div>
                </div>

            </div>

            <div class="dash-controls" style="margin: 0; display: flex; gap: 8px;">
                <button class="btn-action" onclick="openReportPreview('تقرير حركة المخزن التفصيلي', '#report-table-wrap')" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(13,148,136,0.2);">👁️ معاينة</button>
                <button class="btn-action btn-print" onclick="printReportDirectly('تقرير حركة المخزن التفصيلي', '#report-table-wrap')" style="background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--glass-border); padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer;">🖨️ طباعة</button>
                <button class="btn-icon-nav" onclick="history.back()" title="رجوع" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; color: var(--text-primary); cursor: pointer; transition: 0.3s;">↩</button>
            </div>
        </div>

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

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        function fmtNum(n) {
            if (n === undefined || n === null || isNaN(n)) return '0';
            const num = Number(n);
            const dec = (num % 1 === 0) ? 0 : (num % 0.01 === 0 ? 2 : 3);
            return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: dec});
        }

        function initWarehouseLedger() {
            // Load Warehouses
            const whSelect = document.getElementById('warehouse-select');
            if (whSelect && whSelect.options.length <= 1) {
                whSelect.innerHTML = '<option value="ALL">جميع المخازن (كافة الحركات)</option>';
                let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                const displayWh = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];
                displayWh.forEach(wh => {
                    const opt = document.createElement('option');
                    opt.value = wh.id;
                    opt.text = wh.name;
                    whSelect.appendChild(opt);
                });
            }

            const today = new Date();
            const dateTo = document.getElementById('date-to');
            if (dateTo && !dateTo.value) {
                dateTo.valueAsDate = today;
            }

            // Check URL param ?warehouse=ID or ?wh=ID
            const urlParams = new URLSearchParams(window.location.search);
            const whParam = urlParams.get('warehouse') || urlParams.get('wh');
            if (whParam && whSelect) {
                whSelect.value = whParam;
            }

            loadLedger();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWarehouseLedger);
        } else {
            initWarehouseLedger();
        }
        document.addEventListener('DBReady', initWarehouseLedger);

        function buildRealWarehouseHistory(warehouseId) {
            let history = [];
            
            let itemsDb = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            let getItem = (id) => {
                return itemsDb.find(i => String(i.id) === String(id)) || null;
            };

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
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

            // 1. Get Purchases & Adjustments
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            purchases.forEach(inv => {
                (inv.items || inv.lines || []).forEach(line => {
                    let wh = getWarehouseId(line.warehouseId || inv.warehouseId || inv.warehouse);
                    if (warehouseId === 'ALL' || String(wh) === String(warehouseId)) {
                        let itemId = line.itemId || line.productId;
                        let itemObj = getItem(itemId);
                        let baseQty = 0;
                        if (line.customBaseQty !== undefined) {
                            baseQty = parseFloat(line.customBaseQty) || 0;
                        } else {
                            let factor = parseFloat(line.customFactor || line.factor) || 1;
                            let conv = itemObj ? (itemObj.conversionFactor || 1) : 1;
                            baseQty = (parseFloat(line.quantity) || 0) * factor * conv;
                        }

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
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted && x.status !== 'draft' && x.status !== 'preliminary');
            sales.forEach(inv => {
                let hdrWh = inv.warehouse;
                
                (inv.items || []).forEach(line => {
                    let lineWh = getWarehouseId(inv.deliveryWarehouse || line.warehouseId || hdrWh);
                    
                    if (warehouseId === 'ALL' || String(lineWh) === String(warehouseId)) {
                        if (inv.saleType === 'reserved' && inv.deliveryStatus !== 'delivered') {
                            return;
                        }

                        let itemId = line.itemId;
                        let itemObj = getItem(itemId);
                        let baseQty = 0;
                        if (line.customBaseQty !== undefined) {
                            baseQty = parseFloat(line.customBaseQty) || 0;
                        } else {
                            let factor = parseFloat(line.customFactor || line.factor) || 1;
                            let conv = itemObj ? (itemObj.conversionFactor || 1) : 1;
                            baseQty = (parseFloat(line.quantity) || 0) * factor * conv;
                        }

                        let permitNo = inv.orderNo || inv.soNumber || inv.quotationNo || inv.dispatchOrderNo || inv.permitNo || inv.invoiceNo || '-';
                        let docTarget = `sales-invoice.php?id=${inv.id || inv.invoiceNo}`;

                        history.push({
                            date: new Date(inv.date || Date.now()),
                            typeCode: 'out',
                            typeName: 'فاتورة مبيعات',
                            warehouseName: getWarehouseName(lineWh),
                            ref: inv.invoiceNo || inv.id || '-',
                            permitNo: permitNo,
                            docId: inv.id || inv.invoiceNo,
                            docTarget: docTarget,
                            itemId: itemId,
                            itemName: itemObj ? itemObj.name : (line.name || 'صنف غير معروف'),
                            unit: line.unit || (itemObj ? (itemObj.baseUnit || itemObj.unit) : 'وحدة'),
                            in: 0,
                            out: baseQty,
                            notes: line.notes || inv.notes || ''
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
                let fromWhName = tr.fromWarehouseName || getWarehouseName(fromWh);
                let toWhName = tr.toWarehouseName || getWarehouseName(toWh);

                (tr.items || []).forEach(line => {
                    let itemId = line.itemId;
                    let itemObj = getItem(itemId);
                    let origQty = parseFloat(line.quantity) || 0;
                    let resolvedFactor = parseFloat(line.customFactor || line.factor) || 1;
                    let baseQty = (line.customBaseQty !== undefined) ? parseFloat(line.customBaseQty) : (origQty * resolvedFactor);

                    // If viewing all or viewing fromWh
                    if (warehouseId === 'ALL' || String(fromWh) === String(warehouseId)) {
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

                    // If viewing all or viewing toWh
                    if (warehouseId === 'ALL' || String(toWh) === String(warehouseId)) {
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

        function loadLedger() {
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

                const dStr = row.date.toISOString().split('T')[0];

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

            // Update KPIs
            document.getElementById('kpi-wh-count').innerText = fmtNum(history.length);
            document.getElementById('kpi-wh-in').innerText = fmtNum(sumIn);
            document.getElementById('kpi-wh-out').innerText = fmtNum(sumOut);
            const netFlow = sumIn - sumOut;
            document.getElementById('kpi-wh-net').innerHTML = `<span style="color:${netFlow >= 0 ? '#10b981' : '#ec4899'}">${netFlow >= 0 ? '+' : ''}${fmtNum(netFlow)}</span>`;

            initWarehouseLedgerResizers();
        }

        function initWarehouseLedgerResizers() {
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
    </script>
</body>
</html>

