<?php 
$page_title = "الجرد الشامل للمخازن"; 
$header_theme_class = "header-theme-inventory";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - نظام مقاييس</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="css/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/components.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/report-ribbon.css?v=1787182249">
    <link rel="stylesheet" href="css/inventory.css?v=<?php echo time(); ?>">
    
    <style>
        :root {
            --bg-page: #0b1120;
            --surface-card: rgba(15, 23, 42, 0.75);
            --glass-border: rgba(255, 255, 255, 0.08);
            --primary-accent: #38bdf8;
            --emerald-accent: #10b981;
            --amber-accent: #f59e0b;
            --rose-accent: #f43f5e;
            --purple-accent: #8b5cf6;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            margin: 0; padding: 0;
            background-image: radial-gradient(circle at 90% 10%, rgba(56, 189, 248, 0.04), transparent 40%),
                              radial-gradient(circle at 10% 90%, rgba(16, 185, 129, 0.04), transparent 40%);
            min-height: 100vh;
        }

        /* Ambient Orbs */
        .ambient-bg { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: 0; overflow: hidden; }
        .orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.25; animation: floatOrb 18s ease-in-out infinite alternate; }
        .orb-1 { width: 450px; height: 450px; background: #0284c7; top: -80px; left: -80px; }
        .orb-2 { width: 500px; height: 500px; background: #059669; bottom: -100px; right: -100px; animation-duration: 22s; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(60px, 40px) scale(1.08); } }

        .dashboard-container {
            display: flex; flex-direction: column; gap: 10px;
            padding: 10px 20px; box-sizing: border-box; max-width: 1720px; margin: 0 auto;
            position: relative; z-index: 1;
        }

        /* Navigation Bar (Clustered Smart Hub) */
        .nav-tabs { 
            display: flex; gap: 6px; flex-wrap: wrap; overflow: visible;
            background: var(--surface-card); padding: 6px 10px; border-radius: 14px; 
            border: 1px solid var(--glass-border); box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            backdrop-filter: blur(20px); align-items: center; width: 100%; z-index: 100; position: relative;
            min-height: 48px; box-sizing: border-box;
        }
        .nav-cluster { display: flex; align-items: center; gap: 4px; }
        .nav-divider { width: 1px; height: 24px; background: rgba(255, 255, 255, 0.12); margin: 0 4px; flex-shrink: 0; }
        
        .nav-tab {
            padding: 8px 15px; border-radius: 9px; text-decoration: none; font-weight: 700;
            color: var(--text-secondary); background: transparent; border: 1px solid transparent;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 6px; font-size: 0.88rem;
            position: relative; white-space: nowrap; flex-shrink: 0; cursor: pointer; height: 38px; box-sizing: border-box;
        }
        .nav-tab:hover { color: #fff; background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.1); transform: translateY(-1px); }
        .nav-tab.active {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.18), rgba(37, 99, 235, 0.18));
            border-color: rgba(56, 189, 248, 0.45); color: var(--primary-accent);
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.25);
        }
        .nav-tab-ai {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.15), rgba(139, 92, 246, 0.15));
            border: 1px solid rgba(236, 72, 153, 0.4); color: #f472b6 !important;
            box-shadow: 0 0 10px rgba(236, 72, 153, 0.2);
        }
        .nav-tab-ai:hover { background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(139, 92, 246, 0.25)); border-color: rgba(236, 72, 153, 0.6); color: #fff !important; }
        .nav-ai-badge { background: #ec4899; color: #fff; font-size: 0.68rem; padding: 2px 5px; border-radius: 8px; font-weight: 900; }

        /* Nav Dropdown */
        .nav-dropdown { position: relative; display: inline-block; }
        .nav-dropdown-content {
            display: none; position: absolute; top: calc(100% + 6px); right: 0; min-width: 250px;
            background: rgba(15, 23, 42, 0.98); backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border); border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.65); padding: 6px; z-index: 10000;
            animation: dropdownFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-dropdown-content::before { content: ''; position: absolute; top: -10px; left: 0; right: 0; height: 10px; background: transparent; }
        @keyframes dropdownFadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .nav-dropdown:hover .nav-dropdown-content, 
        .nav-dropdown:focus-within .nav-dropdown-content,
        .nav-dropdown.active .nav-dropdown-content { display: flex; flex-direction: column; gap: 4px; }
        .nav-dropdown-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: #cbd5e1;
            text-decoration: none; font-size: 0.86rem; font-weight: 700; border-radius: 8px; transition: all 0.2s ease;
        }
        .nav-dropdown-item:hover { background: rgba(56, 189, 248, 0.15); color: var(--primary-accent); transform: translateX(-3px); }

        /* Action Buttons */
        .btn-action-top {
            padding: 7px 15px; border-radius: 10px; font-weight: 700; cursor: pointer; border: none;
            display: flex; align-items: center; gap: 6px; font-size: 0.85rem; height: 38px; box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .btn-preview-top {
            background: linear-gradient(135deg, #0d9488, #0f766e); color: white;
            box-shadow: 0 4px 10px rgba(13,148,136,0.25);
        }
        .btn-preview-top:hover { transform: translateY(-1px); box-shadow: 0 6px 15px rgba(13,148,136,0.4); }
        .btn-studio-top {
            background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35); border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .btn-studio-top:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5); }
        .btn-icon-nav {
            background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border);
            display: flex; align-items: center; justify-content: center; width: 38px; height: 38px;
            border-radius: 10px; color: var(--text-main); cursor: pointer; transition: 0.25s; font-size: 1.1rem;
        }
        .btn-icon-nav:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateY(-1px); }

        /* Unified Single-Row Filter Hub */
        .filters-hub {
            background: var(--surface-card); backdrop-filter: blur(20px); border-radius: 14px;
            border: 1px solid var(--glass-border); padding: 10px 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.18);
        }
        .filters-main-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .f-search-wrapper { position: relative; flex: 2.0; min-width: 240px; display: flex; align-items: center; }
        .f-search-icon { position: absolute; right: 12px; font-size: 1rem; color: #64748b; pointer-events: none; }
        .f-search-clear {
            position: absolute; left: 10px; font-size: 0.85rem; color: #64748b; cursor: pointer;
            background: rgba(255,255,255,0.08); border-radius: 50%; width: 20px; height: 20px;
            display: none; align-items: center; justify-content: center; transition: 0.2s;
        }
        .f-search-clear:hover { background: rgba(255,255,255,0.2); color: #fff; }
        .f-input {
            background: rgba(0,0,0,0.25); border: 1px solid var(--glass-border); border-radius: 10px;
            color: var(--text-main); padding: 8px 38px 8px 14px; font-size: 0.9rem; outline: none; transition: 0.2s;
            font-family: inherit; width: 100%; box-sizing: border-box; height: 40px; font-weight: 600;
        }
        .f-input:focus { border-color: var(--primary-accent); box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.15); background: rgba(0,0,0,0.4); }
        .f-select {
            padding: 8px 12px; font-size: 0.88rem; font-weight: 700; border-radius: 10px;
            background: rgba(0,0,0,0.25); border: 1px solid var(--glass-border); color: var(--text-main);
            outline: none; cursor: pointer; height: 40px; box-sizing: border-box; flex: 1.2; min-width: 170px;
        }

        /* KPI Row */
        .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 2px; }
        .kpi-card {
            background: var(--surface-card); border: 1px solid var(--glass-border); padding: 14px 18px;
            border-radius: 14px; display: flex; flex-direction: column; gap: 6px; position: relative;
            overflow: hidden; backdrop-filter: blur(15px); box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .kpi-card::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%; }
        .kpi-card.c-blue::before { background: var(--primary-accent); }
        .kpi-card.c-emerald::before { background: var(--emerald-accent); }
        .kpi-card.c-amber::before { background: var(--amber-accent); }
        .kpi-card.c-rose::before { background: var(--rose-accent); }
        .kpi-title { font-size: 0.8rem; color: var(--text-secondary); font-weight: 700; }
        .kpi-val { font-size: 1.4rem; font-weight: 900; color: #fff; }
        .kpi-val span { font-size: 0.8rem; color: var(--text-secondary); font-weight: 500; }

        /* Data Table */
        .table-panel {
            background: var(--surface-card); border: 1px solid var(--glass-border);
            border-radius: 14px; overflow: hidden; backdrop-filter: blur(15px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
        }
        .table-wrapper { overflow-x: auto; max-height: calc(100vh - 350px); }
        .report-table { width: 100%; border-collapse: collapse; min-width: 1100px; text-align: right; }
        .report-table th {
            background: rgba(0,0,0,0.35); padding: 12px 14px; color: var(--text-secondary);
            font-weight: 800; font-size: 0.84rem; border-bottom: 1px solid rgba(255,255,255,0.08);
            position: sticky; top: 0; z-index: 10; white-space: nowrap;
        }
        .report-table td {
            padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.04);
            color: #cbd5e1; font-size: 0.88rem; vertical-align: middle;
        }
        .report-table tr:hover td { background: rgba(255,255,255,0.02); }

        /* Badges */
        .status-badge {
            display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px;
            border-radius: 6px; font-size: 0.76rem; font-weight: 800;
        }
        .status-badge.ok { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-badge.warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-badge.danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* Floating Bottom Action Island */
        .floating-action-island {
            position: sticky; bottom: 12px; z-index: 99;
            background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 14px;
            padding: 8px 16px; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); margin-top: 4px;
        }

        /* Modal Overlay & Panels */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(5, 10, 20, 0.92); backdrop-filter: blur(10px);
            z-index: 12000; display: none; align-items: center; justify-content: center;
        }
        .modal-card {
            background: #0f172a; border: 1px solid var(--glass-border);
            border-radius: 16px; color: #fff; box-shadow: 0 25px 60px rgba(0,0,0,0.7);
            display: flex; flex-direction: column; overflow: hidden;
        }

        /* Light Theme Overrides */
        :root.light-theme body { background-color: #f8fafc; color: #0f172a; }
        :root.light-theme .nav-tabs, :root.light-theme .filters-hub, :root.light-theme .kpi-card, :root.light-theme .table-panel {
            background: #ffffff !important; border-color: rgba(0,0,0,0.08) !important; box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;
        }
        :root.light-theme .f-input, :root.light-theme .f-select { background: #f1f5f9 !important; border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .report-table th { background: #f1f5f9 !important; color: #334155 !important; border-bottom: 2px solid #cbd5e1 !important; }
        :root.light-theme .report-table td { color: #1e293b !important; border-bottom: 1px solid rgba(0,0,0,0.06) !important; }
        :root.light-theme .kpi-val { color: #0f172a !important; }
    </style>
</head>
<body class="no-page-transition">
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
    
    <div class="dashboard-container">
        <?php include 'includes/header.php'; ?>

        <!-- Top Navigation Smart Hub -->
        <!-- Unified Smart Ribbon Toolbar Row -->
        <div class="report-ribbon-bar">
            <div class="ribbon-presets-group">
                <button class="preset-pill-btn active" data-preset="0" onclick="applyReportPreset(0, this)" title="القالب الافتراضي 1">
                    <span class="preset-num">1</span> <span>افتراضي 1</span>
                </button>
                <button class="preset-pill-btn" data-preset="1" onclick="applyReportPreset(1, this)" title="القالب المخصص 2">
                    <span class="preset-num">2</span> <span>تخصيص 2</span>
                </button>
                <button class="preset-pill-btn" data-preset="2" onclick="applyReportPreset(2, this)" title="القالب المخصص 3">
                    <span class="preset-num">3</span> <span>تخصيص 3</span>
                </button>
            </div>

            <div class="ribbon-actions-group">
                <button class="ribbon-btn" onclick="openStudioPreviewPage()" style="--btn-color: #eab308; background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(202, 138, 4, 0.3)); border-color: rgba(234, 179, 8, 0.5); color: #fef08a;">
                    <span>⚙️</span> <span>إعدادات الأعمدة</span>
                </button>
                <button class="ribbon-btn" onclick="openInventoryReportPreview()" style="--btn-color: #0d9488;">
                    <span>👁️</span> <span>معاينة التقرير</span>
                </button>
                <button class="ribbon-btn" onclick="openCustomReportStudio()" style="--btn-color: #6366f1;">
                    <span>🎛️</span> <span>الضبط المتقدم</span>
                </button>
            </div>
        </div>

        <!-- Pro Studio Dropdown Panel & Modals -->
        <!-- Studio panel moved to preview-inventory-report.php -->
        <!-- Studio modals moved to preview-inventory-report.php -->

        <!-- Single-Row Streamlined Filters Hub -->
        <div class="filters-hub">
            <div class="filters-main-row">
                <div class="f-search-wrapper">
                    <span class="f-search-icon">🔍</span>
                    <input type="text" id="filter-search" class="f-input" placeholder="بحث ذكي بالاسم، الكود، الباركود، المواصفات..." oninput="onSearchInput(this)">
                    <span id="search-clear-btn" class="f-search-clear" onclick="clearSearch()">✕</span>
                </div>

                <select id="filter-status" class="f-select" onchange="applyFilters()">
                    <option value="ALL">📦 جميع الحالات (عرض كل الأصناف)</option>
                    <option value="AVAILABLE" selected>✅ المتوفر بالمخزن فقط (الرصيد > 0)</option>
                    <option value="OK">🟢 رصيد آمن (فوق حد الطلب)</option>
                    <option value="WARNING">🟡 تحت حد الطلب (يحتاج توريد)</option>
                    <option value="DANGER">🔴 نواقص / رصيد صفري وعجز</option>
                </select>

                <select id="filter-warehouse" class="f-select" onchange="applyFilters()">
                    <option value="ALL">🏢 جميع المخازن (الجرد العام)</option>
                </select>

                <select id="filter-category" class="f-select" onchange="applyFilters()">
                    <option value="ALL">🏷️ جميع التصنيفات</option>
                </select>

                <div id="filtered-matches-badge" style="font-size: 0.8rem; font-weight: 800; color: #38bdf8; background: rgba(56,189,248,0.1); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(56,189,248,0.25); white-space: nowrap;">
                    مطابقة: 0 صنف
                </div>
            </div>
        </div>

        <!-- KPIs Strip -->
        <div class="kpi-row">
            <div class="kpi-card c-blue">
                <div class="kpi-title">عدد الأصناف المطابقة للجرد</div>
                <div class="kpi-val" id="kpi-count">0 <span>صنف</span></div>
            </div>
            <div class="kpi-card c-emerald">
                <div class="kpi-title">إجمالي الكميات الفعلية الحالية</div>
                <div class="kpi-val" id="kpi-qty">0 <span>وحدة</span></div>
            </div>
            <div class="kpi-card c-amber">
                <div class="kpi-title">القيمة الإجمالية للمخزون (بسعر التكلفة)</div>
                <div class="kpi-val" id="kpi-value" style="color: #f59e0b;">0 <span>SDG</span></div>
            </div>
            <div class="kpi-card c-rose">
                <div class="kpi-title">أصناف تتطلب إعادة طلب أو معالجة عجز</div>
                <div class="kpi-val" id="kpi-alerts" style="color: #fb7185;">0 <span>تنبيه</span></div>
            </div>
        </div>

        <!-- Main Data Table Panel -->
        <div class="table-panel">
            <div class="table-wrapper" id="report-table-wrap">
                <table class="report-table" id="inventory-main-table">
                    <thead id="inventory-thead">
                        <!-- Populated Dynamically via applyFilters() -->
                    </thead>
                    <tbody id="inventory-tbody">
                        <tr><td colspan="15" style="text-align:center; padding:40px; color:var(--text-secondary);">جاري احتساب وتحميل بيانات المخزون...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Floating Action Island -->
        <div class="floating-action-island">
            <div style="display: flex; gap: 8px; align-items: center;">
                <button class="btn-action-top" onclick="openStocktakeModal()" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                    <span>⚖️</span> <span>إجراء تسوية جردية للمخزن</span>
                </button>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button class="btn-action-top" onclick="openStudioPreviewPage()" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: white;">
                    <span>👁️</span> <span>معاينة</span>
                </button>
                <button class="btn-action-top" onclick="printReportDirectly('تقرير جرد المخزون العام', '#report-table-wrap')" style="background: rgba(255,255,255,0.06); color: var(--text-main); border: 1px solid var(--glass-border);">
                    <span>🖨️</span> <span>طباعة</span>
                </button>
                <button class="btn-action-top" onclick="window.openExportModal ? window.openExportModal(exportExcel, 'تقرير جرد المخزون العام') : exportExcel()" style="background: rgba(255,255,255,0.06); color: var(--text-main); border: 1px solid var(--glass-border);">
                    <span>📥</span> <span>تصدير Excel</span>
                </button>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 🎛️ MODAL 1: CUSTOM REPORT STUDIO (الضبط المتقدم)                          -->
    <!-- ========================================================================= -->
    <div id="custom-report-modal" class="modal-overlay">
        <div class="modal-card" style="width: 92%; max-width: 900px; max-height: 90vh;">
            <div style="padding: 16px 22px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">🎛️</span>
                    <div>
                        <h3 style="margin: 0; color: #38bdf8; font-size: 1.15rem; font-weight: 900;">استوديو الضبط المتقدم لتقرير الجرد</h3>
                        <p style="margin: 2px 0 0; font-size: 0.78rem; color: var(--text-secondary);">تخصيص الأعمدة، ترتيب البيانات، وحدات القياس، والخيارات المحاسبية</p>
                    </div>
                </div>
                <button onclick="closeCustomReportStudio()" style="background: none; border: none; color: #94a3b8; font-size: 1.2rem; cursor: pointer;">✕</button>
            </div>

            <div style="padding: 20px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 18px;">
                <!-- Presets Quick Switch -->
                <div>
                    <label style="font-size: 0.85rem; font-weight: 800; color: #38bdf8; margin-bottom: 8px; display: block;">نماذج وتقارير جاهزة بنقرة واحدة:</label>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="btn-action-top" onclick="setStudioPreset('standard')" style="background: rgba(56,189,248,0.15); border: 1px solid #38bdf8; color:#fff;">📋 الافتراضي الشامل</button>
                        <button type="button" class="btn-action-top" onclick="setStudioPreset('financial')" style="background: rgba(245,158,11,0.15); border: 1px solid #f59e0b; color:#fff;">💰 تقرير مالي وقيم المخزون</button>
                        <button type="button" class="btn-action-top" onclick="setStudioPreset('quantities')" style="background: rgba(16,185,129,0.15); border: 1px solid #10b981; color:#fff;">📦 جرد كميات وأرصدة ومتاح</button>
                    </div>
                </div>

                <!-- Columns Selection Grid -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="font-size: 0.85rem; font-weight: 800; color: #38bdf8;">الأعمدة المعروضة في الجدول والطباعة:</label>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="setAllStudioCols(true)" style="background:none; border:none; color:#38bdf8; font-size:0.78rem; font-weight:700; cursor:pointer;">تحديد الكل</button>
                            <span style="color:#64748b;">|</span>
                            <button type="button" onclick="setAllStudioCols(false)" style="background:none; border:none; color:#fb7185; font-size:0.78rem; font-weight:700; cursor:pointer;">إلغاء الكل</button>
                        </div>
                    </div>
                    <div id="cr-cols-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; background: rgba(0,0,0,0.25); padding: 12px; border-radius: 10px; border: 1px solid var(--glass-border);">
                        <!-- Rendered dynamically -->
                    </div>
                </div>

                <!-- Unit & Display Settings -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 800; color: #cbd5e1; margin-bottom: 6px; display: block;">طريقة عرض الكميات متعددة الوحدات:</label>
                        <select id="cr-unit-mode" class="f-input" style="height: 38px;">
                            <option value="compound">📊 وحدات تفصيلية مركبة (طن + سيخ / كرتونة + حبة)</option>
                            <option value="base">📦 الوحدة الأساسية فقط</option>
                            <option value="smallest">🔹 أصغر وحدة قياس فقط</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 800; color: #cbd5e1; margin-bottom: 6px; display: block;">كثافة الأسطر وحجم العرض:</label>
                        <select id="cr-row-density" class="f-input" style="height: 38px;">
                            <option value="compact">مضغوط (Compact - يظهر بيانات أكثر)</option>
                            <option value="normal" selected>عادي (Normal - متوازن)</option>
                            <option value="spacious">مريح (Spacious - خطوط أكبر)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="padding: 14px 22px; border-top: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2);">
                <button type="button" onclick="resetStudioConfig()" style="background: none; border: 1px solid rgba(255,255,255,0.15); color: #94a3b8; padding: 7px 14px; border-radius: 8px; font-weight: 700; cursor: pointer;">إعادة ضبط للمصنع</button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="applyStudioReport()" class="btn-action-top" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff;">تطبيق فوري</button>
                    <button type="button" onclick="saveStudioPreset()" class="btn-action-top" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff;">حفظ كإعداد دائم</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- ⚖️ MODAL 2: PHYSICAL STOCKTAKE (تسوية وجرد المخزون)                        -->
    <!-- ========================================================================= -->
    <div id="stocktake-modal" class="modal-overlay">
        <div class="modal-card" style="width: 95%; max-width: 1100px; height: 90vh;">
            <div style="padding: 16px 22px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">⚖️</span>
                    <div>
                        <h3 style="margin: 0; color: #10b981; font-size: 1.15rem; font-weight: 900;">تسوية وجرد المخزون الفعلي</h3>
                        <p style="margin: 2px 0 0; font-size: 0.78rem; color: var(--text-secondary);">مطابقة الأرصدة الدفترية مع الجرد الفعلي على أرض الواقع وإصدار إذن تسوية تلقائي</p>
                    </div>
                </div>
                <button onclick="closeStocktakeModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.2rem; cursor: pointer;">✕</button>
            </div>

            <div style="padding: 16px 22px; display: flex; gap: 12px; align-items: center; border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);">
                <label style="font-weight: 800; color: #38bdf8; font-size: 0.9rem;">المخزن المستهدف للتسوية:</label>
                <select id="stocktake-warehouse-select" class="f-select" style="max-width: 250px;" onchange="loadStocktakeItems()">
                    <!-- Populated via JS -->
                </select>
                <input type="date" id="stocktake-date" class="f-input" style="max-width: 170px; height: 38px;">
            </div>

            <div style="flex: 1; overflow-y: auto; padding: 16px 22px;">
                <table class="report-table" id="stocktake-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>كود الصنف</th>
                            <th>اسم الصنف</th>
                            <th>الرصيد الدفتري الحالي</th>
                            <th style="color:#10b981;">الرصيد الفعلي المعدود</th>
                            <th>فرق الكمية</th>
                            <th>فرق القيمة (SDG)</th>
                        </tr>
                    </thead>
                    <tbody id="stocktake-tbody">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>

            <div style="padding: 14px 22px; border-top: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.3);">
                <div id="stocktake-summary-badge" style="font-weight: 800; font-size: 0.9rem; color: #cbd5e1;">
                    إجمالي الفروقات: 0.00 SDG
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="closeStocktakeModal()" style="background: none; border: 1px solid rgba(255,255,255,0.15); color: #94a3b8; padding: 7px 14px; border-radius: 8px; font-weight: 700; cursor: pointer;">إلغاء</button>
                    <button type="button" onclick="saveStocktake()" class="btn-action-top" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff;">ترحيل وحفظ التسوية الجردية</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 👁️ MODAL 3: FULLSCREEN REPORT PREVIEW & LIVE DESIGN STUDIO                     -->
    <!-- ========================================================================= -->
    <div id="inventory-report-preview-modal" class="modal-overlay" style="display: none; z-index: 12500;">
        <div class="glass-panel" style="width: 100%; max-width: 100%; height: 100vh; display: flex; flex-direction: column; padding: 18px 22px; border: none; background: #0b1120; border-radius: 0; color: #fff; box-shadow: 0 25px 60px rgba(0,0,0,0.6); overflow: hidden; box-sizing: border-box;">
            
            <!-- Top Toolbar Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 12px;" class="no-print">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">👁️</span>
                    <div>
                        <h3 style="margin: 0; font-weight: 900; color: #38bdf8; font-size: 1.15rem;">معاينة وتخصيص تصميم تقرير الجرد العام</h3>
                        <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: #94a3b8;">استوديو التصميم المتكامل: يمكنك تعديل الألوان، الخطوط، الأعمدة، والتعديل المباشر على النصوص</p>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 6px;">
                    <button type="button" onclick="printInventoryReportSheet()" class="btn-action-top" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff;">
                        <span>🖨️</span> طباعة الآن
                    </button>
                    <button type="button" onclick="closeInventoryReportPreview()" class="btn-icon-nav" style="width:34px; height:34px;">✕</button>
                </div>
            </div>

            <!-- Preview Sheet Scroll Container -->
            <div style="flex: 1; overflow-y: auto; display: flex; justify-content: center; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 12px;">
                <div id="inv-preview-sheet" style="width: 100%; max-width: 1200px; background: #ffffff; color: #0f172a; padding: 30px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.4); box-sizing: border-box; direction: rtl;">
                    <!-- Rendered Dynamically via openInventoryReportPreview() -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>

    <script>
        // =======================================================================
        // 🚀 INVENTORY MASTER ENGINE (MODULAR CLEAN ARCHITECTURE)
        // =======================================================================

        const AVAILABLE_COLUMNS = [
            { id: 'name',           title: 'اسم ومواصفات الصنف',     default: true,  align: 'right', isNum: false },
            { id: 'code',           title: 'كود الصنف',              default: true,  align: 'center', isNum: false },
            { id: 'category',       title: 'التصنيف',                default: true,  align: 'right', isNum: false },
            { id: 'warehouse',      title: 'المخزن',                 default: true,  align: 'right', isNum: false },
            { id: 'actualQty',      title: 'الرصيد الفعلي',          default: true,  align: 'center', isNum: true },
            { id: 'reservedQty',    title: 'الرصيد المحجوز',         default: false, align: 'center', isNum: true },
            { id: 'availQty',       title: 'الرصيد المتاح للبيع',    default: true,  align: 'center', isNum: true },
            { id: 'minQty',         title: 'حد إعادة الطلب',         default: false, align: 'center', isNum: true },
            { id: 'costPrice',      title: 'متوسط سعر التكلفة',       default: true,  align: 'center', isNum: true },
            { id: 'cashPrice',      title: 'سعر البيع نقدي',         default: false, align: 'center', isNum: true },
            { id: 'wholesalePrice', title: 'سعر الجملة',             default: false, align: 'center', isNum: true },
            { id: 'retailPrice',    title: 'سعر التجزئة',            default: false, align: 'center', isNum: true },
            { id: 'totalValue',     title: 'القيمة الإجمالية (SDG)',  default: true,  align: 'center', isNum: true },
            { id: 'status',         title: 'حالة المخزون',           default: true,  align: 'center', isNum: false },
            { id: 'action',         title: 'كشف الحركة',             default: true,  align: 'center', isNum: false }
        ];

        let customReportConfig = {
            cols: ['name', 'code', 'category', 'warehouse', 'actualQty', 'availQty', 'costPrice', 'totalValue', 'status', 'action'],
            unitDisplayMode: 'compound', // compound | base | smallest
            rowDensity: 'normal',        // compact | normal | spacious
            sortBy: 'value_desc'
        };

        let itemsDb = [];
        let invDb = [];

        function fmtNum(n, decimals = 2) {
            if (n === undefined || n === null || isNaN(n)) return '0';
            return Number(n).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: decimals });
        }

        // Universal Storage Reader Supporting Multi-Tenancy & DB_STORE Proxy
        function getAllItemsFromStorage() {
            let items = [];
            if (window.DB_STORE && window.getTenantKey) {
                const tk = window.getTenantKey('erp_items_v1');
                if (window.DB_STORE[tk]) {
                    try {
                        const parsed = typeof window.DB_STORE[tk] === 'string' ? JSON.parse(window.DB_STORE[tk]) : window.DB_STORE[tk];
                        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
                    } catch(e){}
                }
            }
            if (typeof window.getTenantKey === 'function') {
                try {
                    const tk = window.getTenantKey('erp_items_v1');
                    const raw = localStorage.getItem(tk);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
                    }
                } catch(e){}
            }
            const possibleKeys = ['erp_items_v1', 'erp_items', 'items_v1', 'items'];
            for (let k of possibleKeys) {
                try {
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
                        if (parsed && typeof parsed === 'object') {
                            const arr = Array.isArray(parsed.items) ? parsed.items : Object.values(parsed);
                            if (arr.length > 0) return arr;
                        }
                    }
                } catch(e){}
            }
            return [];
        }

        function getStorageList(key) {
            if (window.DB_STORE && window.getTenantKey) {
                const tk = window.getTenantKey(key);
                if (window.DB_STORE[tk]) {
                    try {
                        const parsed = typeof window.DB_STORE[tk] === 'string' ? JSON.parse(window.DB_STORE[tk]) : window.DB_STORE[tk];
                        if (Array.isArray(parsed)) return parsed;
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
                    }
                } catch(e){}
            }
            try {
                const raw = localStorage.getItem(key);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed)) return parsed;
                }
            } catch(e){}
            return [];
        }

        // =======================================================================
        // 🔄 INITIALIZATION & LIFECYCLE
        // =======================================================================

        function initInventoryReport() {
            loadSavedStudioPreset();
            populateWarehouseFilter();
            populateCategoryFilter();

            // Read URL Search params if present
            const urlParams = new URLSearchParams(window.location.search);
            const whParam = urlParams.get('warehouse') || urlParams.get('wh');
            const whSelect = document.getElementById('filter-warehouse');
            if (whParam && whSelect) {
                for (let i = 0; i < whSelect.options.length; i++) {
                    if (whSelect.options[i].value === whParam || whSelect.options[i].text === whParam) {
                        whSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            const searchParam = urlParams.get('search') || urlParams.get('item');
            const searchInput = document.getElementById('filter-search');
            if (searchParam && searchInput) {
                searchInput.value = searchParam;
                onSearchInput(searchInput);
            }

            loadData();
            applyFilters();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initInventoryReport);
        } else {
            initInventoryReport();
        }
        document.addEventListener('DBReady', initInventoryReport);
        window.addEventListener('load', initInventoryReport);

        function loadSavedStudioPreset() {
            try {
                const saved = localStorage.getItem('erp_custom_report_preset_v1');
                if (saved) {
                    const parsed = JSON.parse(saved);
                    customReportConfig = { ...customReportConfig, ...parsed };
                }
            } catch(e){}
        }

        function populateWarehouseFilter() {
            const select = document.getElementById('filter-warehouse');
            if (!select) return;
            select.innerHTML = '<option value="ALL">🏢 جميع المخازن (الجرد العام)</option>';
            let warehousesDb = getStorageList('erp_warehouses_v1');
            if (warehousesDb.length === 0) warehousesDb = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            
            const displayWh = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];
            displayWh.forEach(wh => {
                const opt = document.createElement('option');
                opt.value = wh.name;
                opt.text = wh.name;
                select.appendChild(opt);
            });
        }

        function populateCategoryFilter() {
            const select = document.getElementById('filter-category');
            if (!select) return;
            select.innerHTML = '<option value="ALL">🏷️ جميع التصنيفات</option>';
            const cats = new Set();
            (itemsDb || []).forEach(item => {
                if (item && item.category && item.category.trim()) cats.add(item.category.trim());
            });
            cats.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c;
                opt.text = c;
                select.appendChild(opt);
            });
        }

        function loadData() {
            itemsDb = getAllItemsFromStorage();
            invDb = buildRealInventory(itemsDb);
            populateCategoryFilter();
        }

        // =======================================================================
        // 📊 CORE ACCOUNTING CALCULATION ENGINE
        // =======================================================================

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
                    if (matched && parseFloat(matched.factor) > 0) return parseFloat(matched.factor);
                }
                if (item.conversionFactor && parseFloat(item.conversionFactor) > 0) {
                    return parseFloat(item.conversionFactor);
                }
            }
            return 1;
        }

        function getItemUnitsList(item) {
            let list = [];
            if (!item) return [{ name: 'وحدة', factor: 1 }];

            const baseUnitName = item.baseUnit || item.unit || 'وحدة';
            list.push({ name: baseUnitName, factor: 1, isBase: true });

            if (item.units && Array.isArray(item.units)) {
                item.units.forEach(u => {
                    const uName = u.name || u.unitName;
                    if (uName && uName !== baseUnitName) {
                        list.push({ name: uName, factor: parseFloat(u.factor || u.conversionFactor) || 1 });
                    }
                });
            } else if (item.subUnit && item.conversionFactor) {
                list.push({ name: item.subUnit, factor: parseFloat(item.conversionFactor) || 1 });
            }
            return list;
        }

        function buildRealInventory(items) {
            let actualStockMap = {}; 
            let reservedStockMap = {};

            let warehousesDb = getStorageList('erp_warehouses_v1');
            if (warehousesDb.length === 0) warehousesDb = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            const allWarehouses = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];

            let getWarehouseName = (id) => {
                if (!id) return 'المخزن الرئيسي';
                let w = allWarehouses.find(w => String(w.id) === String(id) || w.name === id);
                return w ? w.name : id;
            };

            // Initialize 0 Stock
            items.forEach(item => {
                allWarehouses.forEach(wh => {
                    let key = String(item.id) + '|' + wh.name;
                    actualStockMap[key] = 0;
                    reservedStockMap[key] = 0;
                });

                // Add Master Opening Balance
                let openQty = parseFloat(item.openingBalance || item.opening_balance || 0);
                if (openQty > 0) {
                    let targetWh = getWarehouseName(item.openingBalanceWarehouseId || item.warehouseId || item.warehouse);
                    let key = String(item.id) + '|' + targetWh;
                    actualStockMap[key] = (actualStockMap[key] || 0) + openQty;
                }
            });

            // 1. Purchases & Adjustments
            let purchases = getStorageList('erp_purchase_invoices_v1').filter(x => !x.deleted);
            purchases.forEach(inv => {
                let hdrWh = getWarehouseName(inv.warehouseId || inv.warehouse);
                (inv.items || inv.lines || []).forEach(line => {
                    let wh = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWh;
                    let itemId = String(line.itemId || line.productId);
                    let itemObj = items.find(i => String(i.id) === itemId);
                    let key = itemId + '|' + wh;

                    const origQty = parseFloat(line.quantity) || 0;
                    const origFactor = resolveUnitFactor(line, itemObj);
                    let baseQty = (line.customBaseQty !== undefined) ? (parseFloat(line.customBaseQty) || 0) : (origQty * origFactor);

                    if (actualStockMap[key] === undefined) actualStockMap[key] = 0;

                    if (inv.isReturn) {
                        actualStockMap[key] -= baseQty;
                    } else if (inv.isAdjustment) {
                        actualStockMap[key] += baseQty;
                    } else {
                        actualStockMap[key] += baseQty;
                    }
                });
            });

            // 2. Sales & Reserved
            let sales = getStorageList('erp_sales_invoices_v1').filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
            sales.forEach(inv => {
                let hdrWh = getWarehouseName(inv.warehouse);
                (inv.items || []).forEach(line => {
                    let wh = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWh;
                    let itemId = String(line.itemId);
                    let itemObj = items.find(i => String(i.id) === itemId);
                    let key = itemId + '|' + wh;

                    const origQty = parseFloat(line.quantity) || 0;
                    const origFactor = resolveUnitFactor(line, itemObj);
                    let baseQty = (line.customBaseQty !== undefined) ? (parseFloat(line.customBaseQty) || 0) : (origQty * origFactor);

                    if (actualStockMap[key] === undefined) actualStockMap[key] = 0;
                    if (reservedStockMap[key] === undefined) reservedStockMap[key] = 0;

                    if (inv.saleType === 'reserved' && inv.deliveryStatus !== 'delivered') {
                        reservedStockMap[key] += baseQty;
                    } else if (inv.isReturn === true) {
                        actualStockMap[key] += baseQty;
                    } else {
                        actualStockMap[key] -= baseQty;
                    }
                });
            });

            // 3. Warehouse Transfers
            let transfers = getStorageList('erp_warehouse_transfers_v1').filter(x => !x.deleted && x.status !== 'cancelled');
            transfers.forEach(tr => {
                let fromWh = getWarehouseName(tr.fromWarehouse);
                let toWh = getWarehouseName(tr.toWarehouse);
                (tr.items || []).forEach(line => {
                    let itemId = String(line.itemId);
                    let itemObj = items.find(i => String(i.id) === itemId);
                    const origQty = parseFloat(line.quantity) || 0;
                    const origFactor = resolveUnitFactor(line, itemObj);
                    let baseQty = (line.customBaseQty !== undefined) ? (parseFloat(line.customBaseQty) || 0) : (origQty * origFactor);

                    let fromKey = itemId + '|' + fromWh;
                    let toKey = itemId + '|' + toWh;

                    if (actualStockMap[fromKey] === undefined) actualStockMap[fromKey] = 0;
                    if (actualStockMap[toKey] === undefined) actualStockMap[toKey] = 0;

                    actualStockMap[fromKey] -= baseQty;
                    actualStockMap[toKey] += baseQty;
                });
            });

            // Flatten generated inventory records
            let result = [];
            for (let key in actualStockMap) {
                let [itemId, warehouse] = key.split('|');
                let rawItem = items.find(i => String(i.id) === itemId);
                if (!rawItem) continue;

                let actual = actualStockMap[key] || 0;
                let reserved = reservedStockMap[key] || 0;
                let avail = actual - reserved;
                let costPrice = parseFloat(rawItem.costPrice || rawItem.buyPrice || rawItem.price) || 0;
                let totalVal = actual * costPrice;

                result.push({
                    itemId: itemId,
                    code: rawItem.code || rawItem.barcode || rawItem.id,
                    name: rawItem.name || 'بدون اسم',
                    category: rawItem.category || 'عام',
                    warehouse: warehouse,
                    actualQty: actual,
                    reservedQty: reserved,
                    availQty: avail,
                    minQty: parseFloat(rawItem.minQuantity || rawItem.minQty || rawItem.reorderLevel || 0),
                    costPrice: costPrice,
                    cashPrice: parseFloat(rawItem.cashPrice || rawItem.price || 0),
                    wholesalePrice: parseFloat(rawItem.wholesalePrice || 0),
                    retailPrice: parseFloat(rawItem.retailPrice || rawItem.price || 0),
                    totalValue: totalVal,
                    rawItem: rawItem
                });
            }
            return result;
        }

        // =======================================================================
        // 🎯 MULTI-UNIT FORMATTING & PRESENTATION
        // =======================================================================

        function renderMultiUnitQty(baseQty, unitsList, themeColor = '#38bdf8') {
            if (baseQty === 0) return `<span style="color:#64748b; font-weight:700;">0</span>`;
            if (customReportConfig.unitDisplayMode === 'base' || !unitsList || unitsList.length <= 1) {
                return `<span style="font-weight:900; color:${themeColor};">${fmtNum(baseQty)} <small style="font-size:0.75rem; color:#94a3b8;">${unitsList[0] ? unitsList[0].name : 'وحدة'}</small></span>`;
            }

            // Compound Units Display (e.g. 2 طن و 15 سيخ)
            let sorted = [...unitsList].sort((a,b) => b.factor - a.factor);
            let rem = Math.abs(baseQty);
            let parts = [];

            for (let i = 0; i < sorted.length; i++) {
                let u = sorted[i];
                if (u.factor <= 0) continue;
                if (i === sorted.length - 1) {
                    let q = rem / u.factor;
                    if (q > 0.001 || parts.length === 0) parts.push(`${fmtNum(q)} ${u.name}`);
                } else {
                    let count = Math.floor(rem / u.factor);
                    if (count > 0) {
                        parts.push(`${count} ${u.name}`);
                        rem -= count * u.factor;
                    }
                }
            }

            let str = (baseQty < 0 ? '- ' : '') + parts.join(' و ');
            return `<span style="font-weight:900; color:${themeColor};">${str}</span>`;
        }

        function getStatusInfo(availQty, minQty) {
            if (availQty <= 0) return { text: 'عجز / نفاد', icon: '🔴', cssClass: 'danger', color: '#f87171' };
            if (minQty > 0 && availQty <= minQty) return { text: 'تحت الطلب', icon: '🟡', cssClass: 'warning', color: '#fbbf24' };
            return { text: 'رصيد آمن', icon: '🟢', cssClass: 'ok', color: '#34d399' };
        }

        // =======================================================================
        // 🔍 FILTERING, SEARCHING & RENDERING
        // =======================================================================

        function onSearchInput(input) {
            const clearBtn = document.getElementById('search-clear-btn');
            if (clearBtn) clearBtn.style.display = input.value.trim() ? 'flex' : 'none';
            applyFilters();
        }

        function clearSearch() {
            const input = document.getElementById('filter-search');
            if (input) { input.value = ''; onSearchInput(input); input.focus(); }
        }

        function applyFilters() {
            const searchVal = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();
            const statusVal = document.getElementById('filter-status')?.value || 'ALL';
            const whVal = document.getElementById('filter-warehouse')?.value || 'ALL';
            const catVal = document.getElementById('filter-category')?.value || 'ALL';

            let filtered = invDb.filter(row => {
                if (whVal !== 'ALL' && row.warehouse !== whVal) return false;
                if (catVal !== 'ALL' && row.category !== catVal) return false;

                // Status Filter
                if (statusVal === 'AVAILABLE' && row.actualQty <= 0) return false;
                if (statusVal === 'OK' && (row.availQty <= row.minQty || row.availQty <= 0)) return false;
                if (statusVal === 'WARNING' && (row.availQty > row.minQty || row.availQty <= 0)) return false;
                if (statusVal === 'DANGER' && row.availQty > 0) return false;

                // Search Filter
                if (searchVal) {
                    const searchStr = `${row.code} ${row.name} ${row.category} ${row.warehouse}`.toLowerCase();
                    if (!searchStr.includes(searchVal)) return false;
                }
                return true;
            });

            // Sorting
            filtered.sort((a,b) => {
                if (customReportConfig.sortBy === 'value_desc') return b.totalValue - a.totalValue;
                if (customReportConfig.sortBy === 'qty_desc') return b.actualQty - a.actualQty;
                if (customReportConfig.sortBy === 'qty_asc') return a.actualQty - b.actualQty;
                if (customReportConfig.sortBy === 'name_asc') return a.name.localeCompare(b.name, 'ar');
                if (customReportConfig.sortBy === 'code_asc') return String(a.code).localeCompare(String(b.code));
                return 0;
            });

            renderTable(filtered);
            updateKPIs(filtered);
        }

        function renderTable(data) {
            const thead = document.getElementById('inventory-thead');
            const tbody = document.getElementById('inventory-tbody');
            if (!thead || !tbody) return;

            // 1. Render Table Headers
            let ths = `<tr>`;
            ths += `<th style="width:45px; text-align:center;">#</th>`;
            AVAILABLE_COLUMNS.forEach(col => {
                if (customReportConfig.cols.includes(col.id)) {
                    ths += `<th style="text-align:${col.align};">${col.title}</th>`;
                }
            });
            ths += `</tr>`;
            thead.innerHTML = ths;

            // 2. Render Rows
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="15" style="text-align:center; padding:45px; color:var(--text-secondary); font-size:1rem; font-weight:700;">لا توجد أصناف مطابقة للبحث المحدد</td></tr>`;
                return;
            }

            let rowsHtml = '';
            data.forEach((row, idx) => {
                const unitsList = getItemUnitsList(row.rawItem);
                const statusInfo = getStatusInfo(row.availQty, row.minQty);

                rowsHtml += `<tr>`;
                rowsHtml += `<td style="text-align:center; font-weight:700; color:#64748b;">${idx + 1}</td>`;

                AVAILABLE_COLUMNS.forEach(col => {
                    if (!customReportConfig.cols.includes(col.id)) return;

                    if (col.id === 'name') {
                        rowsHtml += `<td style="font-weight:800; color:#f8fafc;">${row.name}</td>`;
                    } else if (col.id === 'code') {
                        rowsHtml += `<td style="text-align:center; font-weight:700; color:#38bdf8;">${row.code}</td>`;
                    } else if (col.id === 'category') {
                        rowsHtml += `<td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.8rem;">${row.category}</span></td>`;
                    } else if (col.id === 'warehouse') {
                        rowsHtml += `<td><span style="background:rgba(56,189,248,0.1); color:#38bdf8; border:1px solid rgba(56,189,248,0.25); padding:3px 8px; border-radius:6px; font-weight:700;">${row.warehouse}</span></td>`;
                    } else if (col.id === 'actualQty') {
                        rowsHtml += `<td style="text-align:center;">${renderMultiUnitQty(row.actualQty, unitsList, '#38bdf8')}</td>`;
                    } else if (col.id === 'reservedQty') {
                        rowsHtml += `<td style="text-align:center; color:#f59e0b; font-weight:700;">${row.reservedQty > 0 ? renderMultiUnitQty(row.reservedQty, unitsList, '#f59e0b') : '-'}</td>`;
                    } else if (col.id === 'availQty') {
                        rowsHtml += `<td style="text-align:center;">${renderMultiUnitQty(row.availQty, unitsList, statusInfo.color)}</td>`;
                    } else if (col.id === 'minQty') {
                        rowsHtml += `<td style="text-align:center; color:#64748b;">${fmtNum(row.minQty)}</td>`;
                    } else if (col.id === 'costPrice') {
                        rowsHtml += `<td style="text-align:center; font-weight:700; color:#cbd5e1;">${fmtNum(row.costPrice)} <small style="font-size:0.7rem; color:#64748b;">SDG</small></td>`;
                    } else if (col.id === 'cashPrice') {
                        rowsHtml += `<td style="text-align:center; font-weight:700; color:#38bdf8;">${fmtNum(row.cashPrice)}</td>`;
                    } else if (col.id === 'wholesalePrice') {
                        rowsHtml += `<td style="text-align:center; font-weight:700; color:#10b981;">${fmtNum(row.wholesalePrice)}</td>`;
                    } else if (col.id === 'retailPrice') {
                        rowsHtml += `<td style="text-align:center; font-weight:700; color:#c084fc;">${fmtNum(row.retailPrice)}</td>`;
                    } else if (col.id === 'totalValue') {
                        rowsHtml += `<td style="text-align:center; font-weight:900; color:#f59e0b;">${fmtNum(row.totalValue)} <small style="font-size:0.7rem; color:#94a3b8;">SDG</small></td>`;
                    } else if (col.id === 'status') {
                        rowsHtml += `<td style="text-align:center;"><span class="status-badge ${statusInfo.cssClass}">${statusInfo.icon} ${statusInfo.text}</span></td>`;
                    } else if (col.id === 'action') {
                        rowsHtml += `<td style="text-align:center;">
                            <a href="item-ledger.php?item=${row.itemId}" class="btn-action-top" style="padding:3px 8px; font-size:0.75rem; text-decoration:none; display:inline-flex; background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); border-radius:6px; font-weight:700;" title="عرض كارت حركة الصنف">
                                📑 كشف الحركة
                            </a>
                        </td>`;
                    }
                });
                rowsHtml += `</tr>`;
            });
            tbody.innerHTML = rowsHtml;
        }

        function updateKPIs(data) {
            let totalQty = 0;
            let totalValue = 0;
            let alertCount = 0;
            const uniqueItems = new Set();

            data.forEach(r => {
                uniqueItems.add(r.itemId);
                totalQty += r.actualQty;
                totalValue += r.totalValue;
                if (r.availQty <= r.minQty || r.availQty <= 0) alertCount++;
            });

            document.getElementById('kpi-count').innerHTML = `${fmtNum(uniqueItems.size)} <span>صنف</span>`;
            document.getElementById('kpi-qty').innerHTML = `${fmtNum(totalQty)} <span>وحدة</span>`;
            document.getElementById('kpi-value').innerHTML = `${fmtNum(totalValue)} <span>SDG</span>`;
            document.getElementById('kpi-alerts').innerHTML = `${fmtNum(alertCount)} <span>تنبيه</span>`;
            document.getElementById('filtered-matches-badge').innerText = `مطابقة: ${data.length} سجل (${uniqueItems.size} صنف)`;
        }

        // =======================================================================
        // 🎛️ CUSTOM REPORT STUDIO LOGIC
        // =======================================================================

        function openCustomReportStudio() {
            const modal = document.getElementById('custom-report-modal');
            const container = document.getElementById('cr-cols-checkboxes');
            if (!modal || !container) return;

            container.innerHTML = '';
            AVAILABLE_COLUMNS.forEach(c => {
                const isChecked = customReportConfig.cols.includes(c.id);
                const chip = document.createElement('label');
                chip.style.cssText = 'display:flex; align-items:center; gap:8px; font-size:0.85rem; font-weight:700; color:#cbd5e1; cursor:pointer; padding:4px 6px; border-radius:6px;';
                chip.innerHTML = `<input type="checkbox" value="${c.id}" ${isChecked ? 'checked' : ''} style="cursor:pointer;"> <span>${c.title}</span>`;
                container.appendChild(chip);
            });

            document.getElementById('cr-unit-mode').value = customReportConfig.unitDisplayMode || 'compound';
            document.getElementById('cr-row-density').value = customReportConfig.rowDensity || 'normal';

            modal.style.display = 'flex';
        }

        function closeCustomReportStudio() {
            const modal = document.getElementById('custom-report-modal');
            if (modal) modal.style.display = 'none';
        }

        function setAllStudioCols(checked) {
            document.querySelectorAll('#cr-cols-checkboxes input[type="checkbox"]').forEach(chk => chk.checked = checked);
        }

        function setStudioPreset(preset) {
            if (preset === 'standard') {
                customReportConfig.cols = ['name', 'code', 'category', 'warehouse', 'actualQty', 'availQty', 'costPrice', 'totalValue', 'status', 'action'];
            } else if (preset === 'financial') {
                customReportConfig.cols = ['name', 'code', 'warehouse', 'actualQty', 'costPrice', 'cashPrice', 'wholesalePrice', 'totalValue'];
            } else if (preset === 'quantities') {
                customReportConfig.cols = ['name', 'code', 'category', 'warehouse', 'actualQty', 'reservedQty', 'availQty', 'minQty', 'status'];
            }
            openCustomReportStudio();
        }

        function applyStudioReport() {
            const selectedCols = [];
            document.querySelectorAll('#cr-cols-checkboxes input[type="checkbox"]:checked').forEach(chk => selectedCols.push(chk.value));
            if (selectedCols.length === 0) {
                alert('يرجى اختيار عمود واحد على الأقل للعرض!');
                return;
            }
            customReportConfig.cols = selectedCols;
            customReportConfig.unitDisplayMode = document.getElementById('cr-unit-mode').value;
            customReportConfig.rowDensity = document.getElementById('cr-row-density').value;

            closeCustomReportStudio();
            applyFilters();
        }

        function saveStudioPreset() {
            applyStudioReport();
            localStorage.setItem('erp_custom_report_preset_v1', JSON.stringify(customReportConfig));
            alert('تم حفظ إعدادات تقرير الجرد بنجاح!');
        }

        function resetStudioConfig() {
            customReportConfig = {
                cols: ['name', 'code', 'category', 'warehouse', 'actualQty', 'availQty', 'costPrice', 'totalValue', 'status', 'action'],
                unitDisplayMode: 'compound',
                rowDensity: 'normal',
                sortBy: 'value_desc'
            };
            localStorage.removeItem('erp_custom_report_preset_v1');
            openCustomReportStudio();
            applyFilters();
        }

        // =======================================================================
        // ⚖️ PHYSICAL STOCKTAKE (تسوية وجرد المخزون)
        // =======================================================================

        function openStocktakeModal() {
            const modal = document.getElementById('stocktake-modal');
            const whSelect = document.getElementById('stocktake-warehouse-select');
            if (!modal || !whSelect) return;

            whSelect.innerHTML = '';
            let warehousesDb = getStorageList('erp_warehouses_v1');
            if (warehousesDb.length === 0) warehousesDb = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            warehousesDb.forEach(wh => {
                const opt = document.createElement('option');
                opt.value = wh.name;
                opt.text = wh.name;
                whSelect.appendChild(opt);
            });

            document.getElementById('stocktake-date').valueAsDate = new Date();
            loadStocktakeItems();
            modal.style.display = 'flex';
        }

        function closeStocktakeModal() {
            const modal = document.getElementById('stocktake-modal');
            if (modal) modal.style.display = 'none';
        }

        function loadStocktakeItems() {
            const targetWh = document.getElementById('stocktake-warehouse-select')?.value;
            const tbody = document.getElementById('stocktake-tbody');
            if (!tbody || !targetWh) return;

            const whRecords = invDb.filter(r => r.warehouse === targetWh);
            if (whRecords.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">لا توجد أصناف مسجلة في هذا المخزن</td></tr>`;
                return;
            }

            let html = '';
            whRecords.forEach((r, idx) => {
                html += `<tr>
                    <td style="text-align:center; color:#64748b;">${idx + 1}</td>
                    <td style="color:#38bdf8; font-weight:700;">${r.code}</td>
                    <td style="font-weight:800;">${r.name}</td>
                    <td style="text-align:center; font-weight:800;">${fmtNum(r.actualQty)}</td>
                    <td style="text-align:center;">
                        <input type="number" step="any" class="f-input st-count-input" data-item-id="${r.itemId}" data-book-qty="${r.actualQty}" data-cost="${r.costPrice}" value="${r.actualQty}" oninput="calcStocktakeDiff(this)" style="width:100px; text-align:center; height:32px; font-weight:900; color:#10b981;">
                    </td>
                    <td id="st-diff-qty-${r.itemId}" style="text-align:center; font-weight:800; color:#64748b;">0</td>
                    <td id="st-diff-val-${r.itemId}" style="text-align:center; font-weight:800; color:#64748b;">0.00</td>
                </tr>`;
            });
            tbody.innerHTML = html;
            updateStocktakeSummary();
        }

        function calcStocktakeDiff(input) {
            const itemId = input.dataset.itemId;
            const bookQty = parseFloat(input.dataset.bookQty) || 0;
            const cost = parseFloat(input.dataset.cost) || 0;
            const actualCount = parseFloat(input.value) || 0;
            const diffQty = actualCount - bookQty;
            const diffVal = diffQty * cost;

            const diffQtyEl = document.getElementById(`st-diff-qty-${itemId}`);
            const diffValEl = document.getElementById(`st-diff-val-${itemId}`);

            if (diffQtyEl) {
                diffQtyEl.innerText = diffQty > 0 ? `+${fmtNum(diffQty)}` : fmtNum(diffQty);
                diffQtyEl.style.color = diffQty > 0 ? '#10b981' : (diffQty < 0 ? '#ef4444' : '#64748b');
            }
            if (diffValEl) {
                diffValEl.innerText = `${fmtNum(diffVal)} SDG`;
                diffValEl.style.color = diffVal > 0 ? '#10b981' : (diffVal < 0 ? '#ef4444' : '#64748b');
            }
            updateStocktakeSummary();
        }

        function updateStocktakeSummary() {
            let totalDiffVal = 0;
            document.querySelectorAll('.st-count-input').forEach(input => {
                const bookQty = parseFloat(input.dataset.bookQty) || 0;
                const cost = parseFloat(input.dataset.cost) || 0;
                const actualCount = parseFloat(input.value) || 0;
                totalDiffVal += (actualCount - bookQty) * cost;
            });
            const badge = document.getElementById('stocktake-summary-badge');
            if (badge) {
                badge.innerHTML = `إجمالي صافي الفروقات الجردية: <span style="color:${totalDiffVal >= 0 ? '#10b981' : '#ef4444'}; font-size:1.1rem; font-weight:900;">${fmtNum(totalDiffVal)} SDG</span>`;
            }
        }

        function saveStocktake() {
            const targetWh = document.getElementById('stocktake-warehouse-select')?.value;
            const stDate = document.getElementById('stocktake-date')?.value || new Date().toISOString().split('T')[0];
            const adjustments = [];

            document.querySelectorAll('.st-count-input').forEach(input => {
                const itemId = input.dataset.itemId;
                const bookQty = parseFloat(input.dataset.bookQty) || 0;
                const actualCount = parseFloat(input.value) || 0;
                const diff = actualCount - bookQty;
                if (Math.abs(diff) > 0.0001) {
                    const item = itemsDb.find(i => String(i.id) === String(itemId));
                    adjustments.push({
                        itemId: itemId,
                        quantity: Math.abs(diff),
                        customBaseQty: diff,
                        unit: item ? (item.baseUnit || item.unit || 'وحدة') : 'وحدة',
                        factor: 1,
                        notes: diff > 0 ? `تسوية جردية بالزيادة للمخزن ${targetWh}` : `تسوية جردية بالعجز للمخزن ${targetWh}`
                    });
                }
            });

            if (adjustments.length === 0) {
                alert('لا توجد أي فروقات جردية تستدعي إصدار إذن تسوية!');
                return;
            }

            if (!confirm(`هل أنت متأكد من ترحيل إذن التسوية الجردية لـ (${adjustments.length}) أصناف؟`)) return;

            const adjVoucher = {
                id: `ADJ-${Date.now()}`,
                invoiceNo: `ADJ-${Date.now().toString().slice(-6)}`,
                date: stDate,
                warehouse: targetWh,
                isAdjustment: true,
                items: adjustments,
                notes: `إذن تسوية جردية شاملة لمخزن ${targetWh}`,
                createdAt: new Date().toISOString()
            };

            let purchases = getStorageList('erp_purchase_invoices_v1');
            purchases.unshift(adjVoucher);
            localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(purchases));

            alert('تم ترحيل وحفظ إذن التسوية الجردية بنجاح!');
            closeStocktakeModal();
            loadData();
            applyFilters();
        }

        // =======================================================================
        // 👁️ FULLSCREEN REPORT PREVIEW & LIVE DESIGN STUDIO
        // =======================================================================

        function openInventoryReportPreview() {
            const modal = document.getElementById('inventory-report-preview-modal');
            const sheet = document.getElementById('inv-preview-sheet');
            if (!modal || !sheet) return;

            const dateStr = new Date().toLocaleDateString('ar-EG');
            const filteredWh = document.getElementById('filter-warehouse')?.value || 'ALL';
            const whLabel = filteredWh === 'ALL' ? 'جميع المخازن (الجرد العام)' : filteredWh;

            let sheetHtml = `
                <div style="text-align:center; border-bottom:2px solid #0f172a; padding-bottom:15px; margin-bottom:20px;">
                    <h2 style="margin:0; font-size:1.6rem; color:#0f172a; font-weight:900;">تقرير جرد المخزون العام الشامل</h2>
                    <div style="display:flex; justify-content:space-between; margin-top:10px; font-size:0.88rem; color:#475569; font-weight:700;">
                        <span>🏢 المخزن: <strong>${whLabel}</strong></span>
                        <span>📅 تاريخ التقرير: <strong>${dateStr}</strong></span>
                    </div>
                </div>

                <table style="width:100%; border-collapse:collapse; font-size:0.85rem; text-align:right;">
                    <thead>
                        <tr style="background:#f1f5f9; border-bottom:2px solid #cbd5e1;">
                            <th style="padding:8px 10px; border:1px solid #cbd5e1; text-align:center;">#</th>
                            <th style="padding:8px 10px; border:1px solid #cbd5e1;">كود الصنف</th>
                            <th style="padding:8px 10px; border:1px solid #cbd5e1;">اسم ومواصفات الصنف</th>
                            <th style="padding:8px 10px; border:1px solid #cbd5e1;">المخزن</th>
                            <th style="padding:8px 10px; border:1px solid #cbd5e1; text-align:center;">الرصيد الفعلي</th>
                            <th style="padding:8px 10px; border:1px solid #cbd5e1; text-align:center;">سعر التكلفة</th>
                            <th style="padding:8px 10px; border:1px solid #cbd5e1; text-align:center;">القيمة الإجمالية</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            let sumQty = 0;
            let sumVal = 0;

            invDb.forEach((r, idx) => {
                if (filteredWh !== 'ALL' && r.warehouse !== filteredWh) return;
                sumQty += r.actualQty;
                sumVal += r.totalValue;

                sheetHtml += `
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:7px 10px; border:1px solid #e2e8f0; text-align:center;">${idx + 1}</td>
                        <td style="padding:7px 10px; border:1px solid #e2e8f0; font-weight:700;">${r.code}</td>
                        <td style="padding:7px 10px; border:1px solid #e2e8f0; font-weight:800;">${r.name}</td>
                        <td style="padding:7px 10px; border:1px solid #e2e8f0;">${r.warehouse}</td>
                        <td style="padding:7px 10px; border:1px solid #e2e8f0; text-align:center; font-weight:800;">${fmtNum(r.actualQty)}</td>
                        <td style="padding:7px 10px; border:1px solid #e2e8f0; text-align:center;">${fmtNum(r.costPrice)} SDG</td>
                        <td style="padding:7px 10px; border:1px solid #e2e8f0; text-align:center; font-weight:900;">${fmtNum(r.totalValue)} SDG</td>
                    </tr>
                `;
            });

            sheetHtml += `
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc; font-weight:900; border-top:2px solid #0f172a;">
                            <td colspan="4" style="padding:10px; text-align:center;">الإجمالي العام</td>
                            <td style="padding:10px; text-align:center; color:#0284c7;">${fmtNum(sumQty)}</td>
                            <td style="padding:10px;"></td>
                            <td style="padding:10px; text-align:center; color:#059669;">${fmtNum(sumVal)} SDG</td>
                        </tr>
                    </tfoot>
                </table>
            `;

            sheet.innerHTML = sheetHtml;
            modal.style.display = 'flex';
        }

        function closeInventoryReportPreview() {
            const modal = document.getElementById('inventory-report-preview-modal');
            if (modal) modal.style.display = 'none';
        }

        function printInventoryReportSheet() {
            window.print();
        }

        function exportExcel() {
            let csv = "\uFEFF# , كود الصنف , اسم الصنف , المخزن , الرصيد الفعلي , متوسط التكلفة , القيمة الإجمالية\n";
            invDb.forEach((r, idx) => {
                csv += `"${idx+1}","${r.code}","${r.name}","${r.warehouse}","${r.actualQty}","${r.costPrice}","${r.totalValue}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.setAttribute("download", `inventory_report_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // =======================================================================
        // 🤖 AI SUITE INTEGRATION
        // =======================================================================

        function runAIInventory() {
            alert('جاري تشغيل المستشار الذكي لتحليل المخزون...');
            window.location.href = 'ai_goods_advisor.php';
        }

        // Dropdown Event Delegation
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
<script src="js/universal-report-studio.js?v=<?php echo time(); ?>"></script>
</body>
</html>
