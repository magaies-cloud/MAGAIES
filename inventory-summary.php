<?php 
$page_title = "ملخص حركة المخزون (الشامل)"; 
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
    
    <style>
        .dashboard-container { display: flex; flex-direction: column; gap: 20px; padding: 20px 30px; box-sizing: border-box; max-width: 1600px; margin: 0 auto; }
        
                /* Unified Nav Tabs Container */
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
        /* Kinetic Hover Effect */
        .nav-tab::before { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); transform: skewX(-20deg); transition: 0.5s; }
        .nav-tab:hover::before { left: 150%; }

        /* Button 1: Blue */
        .nav-tab:nth-child(1):hover { background: rgba(59, 130, 246, 0.1); color: #fff; border-color: rgba(59, 130, 246, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(1).active { background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2)); border-color: rgba(59, 130, 246, 0.5); color: #60a5fa; box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }

        /* Button 2: Emerald */
        .nav-tab:nth-child(2):hover { background: rgba(16, 185, 129, 0.1); color: #fff; border-color: rgba(16, 185, 129, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(2).active { background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2)); border-color: rgba(16, 185, 129, 0.5); color: #34d399; box-shadow: 0 0 20px rgba(16, 185, 129, 0.3); }

        /* Button 3: Amber */
        .nav-tab:nth-child(3):hover { background: rgba(245, 158, 11, 0.1); color: #fff; border-color: rgba(245, 158, 11, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(3).active { background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2)); border-color: rgba(245, 158, 11, 0.5); color: #fbbf24; box-shadow: 0 0 20px rgba(245, 158, 11, 0.3); }

        /* Button 4: Purple */
        .nav-tab:nth-child(4):hover { background: rgba(139, 92, 246, 0.1); color: #fff; border-color: rgba(139, 92, 246, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(4).active { background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(109, 40, 217, 0.2)); border-color: rgba(139, 92, 246, 0.5); color: #c084fc; box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); }

        /* Button 5: Rose */
        .nav-tab:nth-child(5):hover { background: rgba(244, 63, 94, 0.1); color: #fff; border-color: rgba(244, 63, 94, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(5).active { background: linear-gradient(135deg, rgba(244, 63, 94, 0.2), rgba(225, 29, 72, 0.2)); border-color: rgba(244, 63, 94, 0.5); color: #fb7185; box-shadow: 0 0 20px rgba(244, 63, 94, 0.3); }

        /* Button 6: Cyan */
        .nav-tab:nth-child(6):hover { background: rgba(6, 182, 212, 0.1); color: #fff; border-color: rgba(6, 182, 212, 0.3); transform: translateY(-2px); }
        .nav-tab:nth-child(6).active { background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(8, 145, 178, 0.2)); border-color: rgba(6, 182, 212, 0.5); color: #22d3ee; box-shadow: 0 0 20px rgba(6, 182, 212, 0.3); }

        /* Light Mode Styling */
        :root.light-theme .nav-tabs { background: rgba(248, 250, 252, 0.85); border: 1px solid rgba(0,0,0,0.06); box-shadow: inset 0 2px 10px rgba(0,0,0,0.02); }
        :root.light-theme .nav-tab { color: #64748b; background: rgba(255,255,255,0.7); border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        :root.light-theme .nav-tab::before { background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent); }
        
        /* Light Mode active/hover states per button (Neon effect) */
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

        .filters-bar { background: rgba(15, 23, 42, 0.6); padding: 20px; border-radius: 16px; border: 1px solid var(--glass-border); display: grid; gap: 15px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); backdrop-filter: blur(10px); }
        .f-group label { display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 8px; font-weight: 700; }
        .f-input { width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fff; font-family: 'Tajawal', sans-serif; transition: 0.3s; }
        .f-input:focus { outline: none; border-color: #38bdf8; background: rgba(56, 189, 248, 0.05); }

        .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .kpi-card { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); padding: 20px; border-radius: 16px; display: flex; flex-direction: column; gap: 10px; position: relative; overflow: hidden; backdrop-filter: blur(10px); }
        .kpi-card::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%; }
        .kpi-card.c-blue::before { background: #3b82f6; }
        .kpi-card.c-emerald::before { background: #10b981; }
        .kpi-card.c-purple::before { background: #8b5cf6; }
        .kpi-card.c-amber::before { background: #f59e0b; }
        .kpi-title { font-size: 0.9rem; color: #94a3b8; font-weight: 700; }
        .kpi-val { font-size: 1.8rem; font-weight: 900; color: #f8fafc; }
        .kpi-val span { font-size: 0.9rem; color: #64748b; font-weight: 500; }

        .table-panel { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); border-radius: 16px; overflow: hidden; backdrop-filter: blur(10px); }
        .table-wrapper { overflow-x: auto; }
        .report-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .report-table th { background: rgba(0,0,0,0.2); padding: 15px; text-align: right; color: #94a3b8; font-weight: 700; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); white-space: nowrap; }
        .report-table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; font-size: 0.95rem; vertical-align: middle; }
        .report-table tr:hover td { background: rgba(255,255,255,0.02); }

        /* Nav Dropdown for Extra Reports */
        .nav-dropdown { position: relative; display: inline-block; }
        .nav-dropdown-content {
            display: none; position: absolute; top: calc(100% + 6px); right: 0; min-width: 250px;
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
        .nav-dropdown.active .nav-dropdown-content { display: block; }
        .nav-dropdown-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: #cbd5e1;
            text-decoration: none; font-size: 0.86rem; font-weight: 700; border-radius: 8px; transition: all 0.2s ease;
        }
        .nav-dropdown-item:hover { background: rgba(56, 189, 248, 0.15); color: #38bdf8; transform: translateX(-3px); }
        .nav-cluster { display: inline-flex; align-items: center; }
        .nav-divider { width: 1px; height: 26px; background: rgba(255,255,255,0.1); margin: 0 4px; }
        .nav-tab-ai { border-color: rgba(168, 85, 247, 0.4) !important; background: linear-gradient(135deg, rgba(168,85,247,0.1), rgba(59,130,246,0.1)) !important; }
        .nav-ai-badge { background: linear-gradient(135deg, #a855f7, #6366f1); color: #fff; font-size: 0.65rem; font-weight: 900; padding: 2px 6px; border-radius: 6px; }
        .btn-action { padding: 8px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; border: none; display: flex; align-items: center; gap: 6px; }

        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body, :root.light-theme .ambient-bg { background: #f8fafc !important; }
        :root.light-theme .table-panel, :root.light-theme .kpi-card, :root.light-theme .filters-bar { background: #ffffff !important; border-color: rgba(0, 0, 0, 0.08) !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important; color: #0f172a !important; }
        :root.light-theme .f-input { background: #f1f5f9 !important; border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .report-table th { background: #f1f5f9 !important; color: #0f172a !important; border-bottom: 2px solid #cbd5e1 !important; }
        :root.light-theme .report-table td { color: #1e293b !important; border-bottom: 1px solid rgba(0,0,0,0.08) !important; }
        :root.light-theme .report-table tr:hover td { background: rgba(0,0,0,0.02) !important; }
        :root.light-theme .kpi-val { color: #0f172a !important; }
        :root.light-theme .kpi-title, :root.light-theme .f-group label { color: #334155 !important; }
        :root.light-theme .nav-dropdown-content { background: #ffffff !important; border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
        :root.light-theme .nav-dropdown-item { color: #334155 !important; }
        :root.light-theme .nav-dropdown-item:hover { background: #f1f5f9 !important; color: #0284c7 !important; }
    </style>
</head>
<body class="no-page-transition">
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
    
    <div class="dashboard-container">
        <?php include 'includes/header.php'; ?>

        <!-- Top Navigation Row (Clustered Smart Hub) -->
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
                        <span>📑</span> <span>حركات البضائع (ملخص الحركات)</span>
                        <span style="font-size: 0.7rem; margin-right: 2px;">▼</span>
                    </div>
                    <div class="nav-dropdown-content">
                        <a href="item-ledger.php" class="nav-dropdown-item" title="كشف حركة صنف محدد">
                            <span>📑</span> حركة الأصناف
                        </a>
                        <a href="warehouse-ledger.php" class="nav-dropdown-item" title="كشف حركة مخزن محدد">
                            <span>🏭</span> حركة المخازن
                        </a>
                        <a href="inventory-summary.php" class="nav-dropdown-item active" style="color: #38bdf8; background: rgba(56,189,248,0.1);" title="ملخص الوارد والمنصرف">
                            <span>📊</span> ملخص الحركات (الحالية)
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
                <button class="btn-action" onclick="openReportPreview('تقرير ملخص حركات المخزون', '#report-table-wrap')" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: white; box-shadow: 0 4px 10px rgba(13,148,136,0.2);">👁️ معاينة</button>
                <button class="btn-action btn-print" onclick="printReportDirectly('تقرير ملخص حركات المخزون', '#report-table-wrap')" style="background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--glass-border);">🖨️ طباعة</button>
            </div>
        </div>

        <!-- KPIs -->
        <div class="kpi-row">
            <div class="kpi-card c-blue">
                <div class="kpi-title">عدد الأصناف المتأثرة بالحركة</div>
                <div class="kpi-val" id="kpi-count">0 <span>صنف</span></div>
            </div>
            <div class="kpi-card c-emerald">
                <div class="kpi-title">إجمالي الكميات (الرصيد الختامي)</div>
                <div class="kpi-val" id="kpi-qty">0 <span>وحدة</span></div>
            </div>
            <div class="kpi-card c-amber">
                <div class="kpi-title">القيمة الإجمالية للمخزون</div>
                <div class="kpi-val" id="kpi-value" style="color: #f59e0b;">0 <span>SDG</span></div>
            </div>
        </div>

        <!-- Filters Grid -->
        <div class="filters-bar" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div class="f-group">
                <label>بحث ذكي (كود أو اسم أو باركود)</label>
                <input type="text" class="f-input" id="filter-search" placeholder="اكتب للبحث..." onkeyup="applyFilters()">
            </div>
            <div class="f-group">
                <label>تصفية بالمخزن</label>
                <select class="f-input" id="filter-warehouse" onchange="applyFilters()">
                    <option value="ALL">جميع المخازن (الكل)</option>
                </select>
            </div>
            <div class="f-group">
                <label>من تاريخ</label>
                <input type="date" class="f-input" id="filter-date-from" onchange="loadData(); applyFilters();">
            </div>
            <div class="f-group">
                <label>إلى تاريخ</label>
                <input type="date" class="f-input" id="filter-date-to" onchange="loadData(); applyFilters();">
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-panel">
            <div class="table-wrapper" id="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>كود الصنف</th>
                            <th>اسم الصنف</th>
                            <th>المخزن</th>
                            <th style="color:#6366f1; text-align:center;">رصيد افتتاحي</th>
                            <th style="color:#10b981; text-align:center;">إجمالي الوارد (+)</th>
                            <th style="color:#ef4444; text-align:center;">إجمالي المنصرف (-)</th>
                            <th style="color:#f59e0b; text-align:center;">الرصيد الختامي</th>
                            <th>قيمة المخزون</th>
                            <th style="text-align:center;">إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-tbody">
                        <tr><td colspan="10" style="text-align:center; padding:30px; color:var(--text-secondary);">جاري الحساب...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        let invDb = [];
        let itemsDb = [];

        function fmtNum(n) {
            if (n === undefined || n === null || isNaN(n)) return '0';
            const num = Number(n);
            const dec = (num % 1 === 0) ? 0 : (num % 0.01 === 0 ? 2 : 3);
            return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: dec});
        }

        function initInventoryReport() {
            populateWarehouseFilter();
            
            // Set default dates (Start of month to today)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const dateFrom = document.getElementById('filter-date-from');
            const dateTo = document.getElementById('filter-date-to');
            if (dateFrom && !dateFrom.value) dateFrom.valueAsDate = firstDay;
            if (dateTo && !dateTo.value) dateTo.valueAsDate = today;

            loadData();
            applyFilters();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initInventoryReport);
        } else {
            initInventoryReport();
        }
        document.addEventListener('DBReady', initInventoryReport);

        function populateWarehouseFilter() {
            const select = document.getElementById('filter-warehouse');
            if (!select || select.options.length > 1) return;
            select.innerHTML = '<option value="ALL">جميع المخازن (الكل)</option>';

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            const displayWh = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];
            displayWh.forEach(wh => {
                const opt = document.createElement('option');
                opt.value = wh.name;
                opt.text = wh.name;
                select.appendChild(opt);
            });
        }

        function loadData() {
            itemsDb = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            
            let dateFrom = document.getElementById('filter-date-from')?.value;
            let dateTo = document.getElementById('filter-date-to')?.value;
            
            if (!dateFrom || !dateTo) return;

            let startDate = new Date(dateFrom);
            startDate.setHours(0,0,0,0);
            
            let endDate = new Date(dateTo);
            endDate.setHours(23,59,59,999);

            invDb = buildSummaryReport(itemsDb, startDate, endDate);
        }

        function buildSummaryReport(items, startDate, endDate) {
            let summaryMap = {}; 

            let warehousesDb = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
            if (warehousesDb.length === 0) warehousesDb = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            const allWarehouses = [{ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام' }, ...warehousesDb];

            let getWarehouseName = (id) => {
                let w = allWarehouses.find(w => String(w.id) === String(id) || w.name === id);
                return w ? w.name : 'المخزن الرئيسي';
            };

            items.forEach(item => {
                allWarehouses.forEach(wh => {
                    let key = String(item.id) + '|' + wh.name;
                    summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                });
            });

            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            sales = sales.filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary' && (x.saleType !== 'reserved' || x.deliveryStatus === 'delivered'));

            // 1. Process Purchases & Adjustments
            purchases.forEach(inv => {
                let invDate = new Date(inv.date || inv.invoiceDate || inv.receiveDate || inv.createdAt);
                let hdrWhName = getWarehouseName(inv.warehouseId || inv.warehouse);
                const isReturn = inv.isReturn === true;
                const isAdjustment = inv.isAdjustment === true;

                (inv.items || inv.lines || []).forEach(line => {
                    let lineWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
                    let itemId = String(line.itemId || line.productId);
                    let key = itemId + '|' + lineWhName;
                    
                    if (!summaryMap[key]) {
                        summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    }

                    let item = items.find(i => String(i.id) === itemId);
                    let baseQty = line.customBaseQty !== undefined ? parseFloat(line.customBaseQty) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1) * (item ? (item.conversionFactor || 1) : 1));
                    
                    let isOutward = isReturn || (isAdjustment && baseQty < 0);
                    let effectiveQty = Math.abs(baseQty);

                    if (invDate < startDate) {
                        if (isOutward) {
                            summaryMap[key].opening -= effectiveQty;
                            summaryMap[key].closing -= effectiveQty;
                        } else {
                            summaryMap[key].opening += effectiveQty;
                            summaryMap[key].closing += effectiveQty;
                        }
                    } else if (invDate >= startDate && invDate <= endDate) {
                        if (isOutward) {
                            summaryMap[key].outward += effectiveQty;
                            summaryMap[key].closing -= effectiveQty;
                        } else {
                            summaryMap[key].inward += effectiveQty;
                            summaryMap[key].closing += effectiveQty;
                        }
                    }
                });
            });

            // 2. Process Sales
            sales.forEach(inv => {
                let invDate = new Date(inv.date || inv.invoiceDate || inv.createdAt);
                let hdrWhName = getWarehouseName(inv.warehouse);
                const isReturn = inv.isReturn === true;
                
                (inv.items || []).forEach(line => {
                    let lineWhName = line.warehouseId ? getWarehouseName(line.warehouseId) : hdrWhName;
                    let itemId = String(line.itemId);
                    let key = itemId + '|' + lineWhName;
                    
                    if (!summaryMap[key]) {
                        summaryMap[key] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    }

                    let item = items.find(i => String(i.id) === itemId);
                    let baseQty = line.customBaseQty !== undefined ? parseFloat(line.customBaseQty) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1) * (item ? (item.conversionFactor || 1) : 1));
                    
                    if (invDate < startDate) {
                        if (isReturn) {
                            summaryMap[key].opening += baseQty;
                            summaryMap[key].closing += baseQty;
                        } else {
                            summaryMap[key].opening -= baseQty;
                            summaryMap[key].closing -= baseQty;
                        }
                    } else if (invDate >= startDate && invDate <= endDate) {
                        if (isReturn) {
                            summaryMap[key].inward += baseQty;
                            summaryMap[key].closing += baseQty;
                        } else {
                            summaryMap[key].outward += baseQty;
                            summaryMap[key].closing -= baseQty;
                        }
                    }
                });
            });

            // 3. Process Warehouse Transfers
            let transfers = JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1')) || [];
            transfers = transfers.filter(x => !x.deleted && x.status !== 'cancelled');
            transfers.forEach(transfer => {
                let transferDate = new Date(transfer.date || transfer.createdAt);
                let fromWhName = getWarehouseName(transfer.fromWarehouse);
                let toWhName = getWarehouseName(transfer.toWarehouse);
                
                (transfer.items || []).forEach(line => {
                    let itemId = String(line.itemId);
                    let fromKey = itemId + '|' + fromWhName;
                    let toKey = itemId + '|' + toWhName;

                    if (!summaryMap[fromKey]) summaryMap[fromKey] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    if (!summaryMap[toKey]) summaryMap[toKey] = { opening: 0, inward: 0, outward: 0, closing: 0 };
                    
                    let item = items.find(i => String(i.id) === itemId);
                    let baseQty = line.customBaseQty !== undefined ? parseFloat(line.customBaseQty) : ((parseFloat(line.quantity) || 0) * (parseFloat(line.customFactor || line.factor) || 1) * (item ? (item.conversionFactor || 1) : 1));
                    
                    if (transferDate < startDate) {
                        summaryMap[fromKey].opening -= baseQty;
                        summaryMap[fromKey].closing -= baseQty;
                        
                        summaryMap[toKey].opening += baseQty;
                        summaryMap[toKey].closing += baseQty;
                    } else if (transferDate >= startDate && transferDate <= endDate) {
                        summaryMap[fromKey].outward += baseQty;
                        summaryMap[fromKey].closing -= baseQty;
                        
                        summaryMap[toKey].inward += baseQty;
                        summaryMap[toKey].closing += baseQty;
                    }
                });
            });

            let generated = [];
            for (let key in summaryMap) {
                let [itemId, warehouse] = key.split('|');
                let itemData = items.find(i => String(i.id) === itemId);
                let cost = itemData && itemData.costPrice ? parseFloat(itemData.costPrice) : 0;
                
                generated.push({
                    itemId: itemId,
                    warehouse: warehouse,
                    opening: summaryMap[key].opening,
                    inward: summaryMap[key].inward,
                    outward: summaryMap[key].outward,
                    closing: summaryMap[key].closing,
                    cost: cost,
                    unit: itemData ? (itemData.baseUnit || itemData.unit || 'وحدة') : 'وحدة'
                });
            }
            return generated;
        }

        function applyFilters() {
            let fWh = document.getElementById('filter-warehouse')?.value || 'ALL';
            let fSearch = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();

            let filteredData = invDb.filter(row => {
                let matchWh = (fWh === 'ALL' || row.warehouse === fWh);
                let itemData = itemsDb.find(i => String(i.id) === String(row.itemId));
                let matchSearch = true;
                if (fSearch && itemData) {
                    let searchStr = `${itemData.code || ''} ${itemData.name || ''} ${itemData.barcode || ''}`.toLowerCase();
                    matchSearch = searchStr.includes(fSearch);
                }
                
                let hasMovement = (row.opening !== 0 || row.inward !== 0 || row.outward !== 0 || row.closing !== 0);
                return matchWh && matchSearch && hasMovement;
            });

            renderTable(filteredData);
        }

        function renderTable(data) {
            const tbody = document.getElementById('inventory-tbody');
            tbody.innerHTML = '';

            let totalQty = 0;
            let totalValue = 0;
            let itemCount = new Set();

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:35px; color:var(--text-secondary); font-size:1.05rem;">لا توجد حركات مطابقة للبحث في هذه الفترة</td></tr>`;
            } else {
                data.forEach((row, index) => {
                    let item = itemsDb.find(i => String(i.id) === String(row.itemId)) || {};
                    let rowVal = row.closing * row.cost;
                    
                    itemCount.add(row.itemId);
                    totalQty += row.closing;
                    totalValue += rowVal;

                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td style="font-weight:700; color:#3b82f6;">${item.code || row.itemId || '-'}</td>
                        <td style="font-weight:700; color:#f8fafc;">${item.name || 'غير معروف'}</td>
                        <td><span style="background:rgba(56,189,248,0.1); color:#38bdf8; border:1px solid rgba(56,189,248,0.25); padding:3px 8px; border-radius:6px; font-weight:700;">${row.warehouse}</span></td>
                        <td style="color:#6366f1; font-weight:800; text-align:center;">${fmtNum(row.opening)}</td>
                        <td style="color:#10b981; font-weight:800; text-align:center; background:rgba(16,185,129,0.02);">${row.inward > 0 ? '+ ' + fmtNum(row.inward) : '-'}</td>
                        <td style="color:#ef4444; font-weight:800; text-align:center; background:rgba(239,68,68,0.02);">${row.outward > 0 ? '- ' + fmtNum(row.outward) : '-'}</td>
                        <td style="color:#f59e0b; font-weight:900; font-size:1.05rem; text-align:center; background:rgba(245,158,11,0.05);">${fmtNum(row.closing)} <small style="font-size:0.75rem; color:#94a3b8;">${row.unit}</small></td>
                        <td style="font-weight:700; color:#cbd5e1;">${fmtNum(rowVal)} SDG</td>
                        <td style="text-align:center;">
                            <a href="item-ledger.php?item=${row.itemId}" class="btn-action" style="padding:3px 8px; font-size:0.75rem; text-decoration:none; display:inline-flex; background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); border-radius:6px; font-weight:700;" title="عرض كارت حركة الصنف التفصيلي">
                                📑 كشف الحركة
                            </a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // Update KPIs
            document.getElementById('kpi-count').innerHTML = `${fmtNum(itemCount.size)} <span>صنف</span>`;
            document.getElementById('kpi-qty').innerHTML = `${fmtNum(totalQty)} <span>وحدة</span>`;
            document.getElementById('kpi-value').innerHTML = `${fmtNum(totalValue)} <span>SDG</span>`;
        }

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
