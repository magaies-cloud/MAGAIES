<?php 
$page_title = "التحليل الاستراتيجي للمخزون"; 
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
        
        /* Navigation Tabs (Top) */
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

        /* Sub-Navigation for Analytics */
        .sub-nav { display: flex; gap: 15px; margin-bottom: 25px; }
        .sub-btn { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); color: #94a3b8; padding: 12px 25px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; backdrop-filter: blur(10px); flex: 1; font-size: 1.05rem; }
        .sub-btn:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sub-btn.active { background: linear-gradient(135deg, #8b5cf6, #d946ef); color: #fff; border: none; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4); }

        /* KPIs */
        .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .kpi-card { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); padding: 20px; border-radius: 16px; position: relative; overflow: hidden; backdrop-filter: blur(10px); }
        .kpi-card::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%; }
        .kpi-title { font-size: 0.9rem; color: #94a3b8; font-weight: 700; margin-bottom: 10px; }
        .kpi-val { font-size: 1.8rem; font-weight: 900; color: #f8fafc; }
        .kpi-val span { font-size: 0.9rem; color: #64748b; font-weight: 500; }
        
        .kpi-card.c-purple::before { background: #8b5cf6; }
        .kpi-card.c-emerald::before { background: #10b981; }
        .kpi-card.c-rose::before { background: #f43f5e; }
        .kpi-card.c-blue::before { background: #3b82f6; }
        .kpi-card.c-amber::before { background: #f59e0b; }

        /* Tables */
        .table-panel { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); border-radius: 16px; overflow: hidden; backdrop-filter: blur(10px); margin-bottom: 20px;}
        .table-wrapper { overflow-x: auto; max-height: 60vh; }
        .report-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .report-table th { background: rgba(0,0,0,0.2); padding: 15px; text-align: right; color: #94a3b8; font-weight: 700; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); position: sticky; top: 0; z-index: 10; backdrop-filter: blur(5px); }
        .report-table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; font-size: 0.95rem; vertical-align: middle; }
        .report-table tr:hover td { background: rgba(255,255,255,0.02); }
        
        /* Badges & progress */
        .badge { padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; }
        .badge.A { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .badge.B { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid #3b82f6; }
        .badge.C { background: rgba(100, 116, 139, 0.1); color: #94a3b8; border: 1px solid #94a3b8; }
        
        .badge.active { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .badge.slow { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .badge.dead { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        .progress-bar { height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; margin-top: 5px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #10b981, #3b82f6); }

        /* Light Theme */
        :root.light-theme body, :root.light-theme .ambient-bg { background: #f8fafc !important; }
        :root.light-theme .kpi-card, :root.light-theme .table-panel, :root.light-theme .sub-btn { background: #ffffff !important; border-color: rgba(0, 0, 0, 0.08) !important; color: #0f172a !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important; }
        :root.light-theme .report-table th { background: #f1f5f9 !important; color: #0f172a !important; border-bottom: 2px solid #cbd5e1 !important; }
        :root.light-theme .report-table td { color: #1e293b !important; border-bottom: 1px solid rgba(0,0,0,0.08) !important; }
        :root.light-theme .report-table tr:hover td { background: rgba(0,0,0,0.02) !important; }
        :root.light-theme .kpi-val { color: #0f172a !important; }
        :root.light-theme .kpi-title { color: #334155 !important; }
        :root.light-theme .sub-btn.active { color: #fff !important; }
    </style>
</head>
<body class="no-page-transition">
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
    


    <div class="dashboard-container">
        <?php include 'includes/header.php'; ?>

        <!-- Navigation and Actions Row -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; flex-wrap: wrap; gap: 15px;">
                <div class="nav-tabs" style="margin-bottom: 0;">
                    <a data-href="inventory-report.php" onclick="window.location.href='inventory-report.php'" class="nav-tab">📦 جرد الأرصدة العام</a>
                    <a data-href="warehouse-inventory.php" onclick="window.location.href='warehouse-inventory.php'" class="nav-tab">🏢 جرد المخزن</a>
                    <a data-href="inventory-summary.php" onclick="window.location.href='inventory-summary.php'" class="nav-tab">📊 ملخص الحركات</a>
                    <a data-href="inventory-analytics.php" onclick="window.location.href='inventory-analytics.php'" class="nav-tab active">🧠 التحليل الاستراتيجي</a>
                    <a data-href="item-ledger.php" onclick="window.location.href='item-ledger.php'" class="nav-tab">📑 حركة الصنف</a>
                    <a data-href="warehouse-ledger.php" onclick="window.location.href='warehouse-ledger.php'" class="nav-tab">🏭 حركة المخزن</a>
                <a data-href="ai_goods_advisor.php" onclick="window.location.href='ai_goods_advisor.php'" class="nav-tab">🤖 مستشار الـ AI للبضائع</a>
                <a data-href="smart_inventory_report.php" onclick="window.location.href='smart_inventory_report.php'" class="nav-tab">📊 تقارير المخازن الذكية</a>
                </div>
                <div class="dash-controls" style="margin: 0;">
                    <button class="btn-action" onclick="previewAnalyticsReport()" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(13,148,136,0.2); margin-left: 8px;">👁️ معاينة</button>
                    <button class="btn-action btn-print" onclick="printAnalyticsReportDirectly()" style="background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--glass-border); padding: 8px 15px; border-radius: 10px; font-weight: 700; cursor: pointer;">🖨️ طباعة التقرير</button>
                </div>
            </div>

            <!-- Sub Navigation -->
            <div class="sub-nav">
                <button class="sub-btn active" onclick="switchTab('profitability', this)">💰 تقرير ربحية الأصناف</button>
                <button class="sub-btn" onclick="switchTab('aging', this)">⏳ أعمار المخزون (الراكد)</button>
                <button class="sub-btn" onclick="switchTab('abc', this)">🎯 تحليل ABC العالمي</button>
            </div>

            <!-- Views Container -->
            <div id="view-profitability" class="view-section">
                <div class="kpi-row">
                    <div class="kpi-card c-emerald">
                        <div class="kpi-title">إجمالي المبيعات (كل الأوقات)</div>
                        <div class="kpi-val" id="kpi-prof-sales">0 <span>SDG</span></div>
                    </div>
                    <div class="kpi-card c-rose">
                        <div class="kpi-title">إجمالي التكلفة</div>
                        <div class="kpi-val" id="kpi-prof-cost">0 <span>SDG</span></div>
                    </div>
                    <div class="kpi-card c-purple">
                        <div class="kpi-title">الربح الإجمالي النقي</div>
                        <div class="kpi-val" id="kpi-prof-profit">0 <span>SDG</span></div>
                    </div>
                    <div class="kpi-card c-blue">
                        <div class="kpi-title">متوسط هامش الربح</div>
                        <div class="kpi-val" id="kpi-prof-margin">0 <span>%</span></div>
                    </div>
                </div>
                <div class="table-panel">
                    <div class="table-wrapper">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>كود الصنف</th>
                                    <th>اسم الصنف</th>
                                    <th>الكمية المباعة</th>
                                    <th>إجمالي المبيعات</th>
                                    <th>إجمالي التكلفة</th>
                                    <th style="color:#10b981;">صافي الربح</th>
                                    <th style="color:#3b82f6;">هامش الربح (%)</th>
                                </tr>
                            </thead>
                            <tbody id="tb-profitability"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="view-aging" class="view-section" style="display: none;">
                <div class="kpi-row">
                    <div class="kpi-card c-emerald">
                        <div class="kpi-title">بضاعة نشطة (< 30 يوم)</div>
                        <div class="kpi-val" id="kpi-age-active">0 <span>صنف</span></div>
                    </div>
                    <div class="kpi-card c-amber">
                        <div class="kpi-title">بطيئة الحركة (30 - 90 يوم)</div>
                        <div class="kpi-val" id="kpi-age-slow">0 <span>صنف</span></div>
                    </div>
                    <div class="kpi-card c-rose">
                        <div class="kpi-title">راكدة - Dead Stock (> 90 يوم)</div>
                        <div class="kpi-val" id="kpi-age-dead">0 <span>صنف</span></div>
                    </div>
                    <div class="kpi-card c-purple">
                        <div class="kpi-title">قيمة الأموال المجمدة (في الراكد)</div>
                        <div class="kpi-val" id="kpi-age-frozen">0 <span>SDG</span></div>
                    </div>
                </div>
                <div class="table-panel">
                    <div class="table-wrapper">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>كود الصنف</th>
                                    <th>اسم الصنف</th>
                                    <th>الرصيد الحالي</th>
                                    <th>تاريخ آخر مبيعات</th>
                                    <th>أيام الركود</th>
                                    <th>القيمة المجمدة</th>
                                    <th>حالة الصنف</th>
                                </tr>
                            </thead>
                            <tbody id="tb-aging"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="view-abc" class="view-section" style="display: none;">
                <div class="kpi-row">
                    <div class="kpi-card c-emerald">
                        <div class="kpi-title">الفئة A (80% من الإيرادات)</div>
                        <div class="kpi-val" id="kpi-abc-a">0 <span>صنف</span></div>
                    </div>
                    <div class="kpi-card c-blue">
                        <div class="kpi-title">الفئة B (15% من الإيرادات)</div>
                        <div class="kpi-val" id="kpi-abc-b">0 <span>صنف</span></div>
                    </div>
                    <div class="kpi-card c-rose">
                        <div class="kpi-title">الفئة C (5% من الإيرادات)</div>
                        <div class="kpi-val" id="kpi-abc-c">0 <span>صنف</span></div>
                    </div>
                </div>
                <div class="table-panel">
                    <div class="table-wrapper">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>الترتيب</th>
                                    <th>الصنف</th>
                                    <th>إجمالي إيراداته</th>
                                    <th>نسبة المساهمة %</th>
                                    <th>النسبة التراكمية %</th>
                                    <th>فئة ABC</th>
                                </tr>
                            </thead>
                            <tbody id="tb-abc"></tbody>
                        </table>
                    </div>
                </div>
            </div>

    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260803"></script>
    <script>
        let itemsDb = [];
        let salesDb = [];
        let inventoryDb = []; // To calculate current balances for Aging report
        let lastPurchasePriceMap = {}; // Maps itemId to its last known purchase cost

        function initAnalytics() {
            itemsDb = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            let sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            salesDb = sales.filter(x => !x.deleted && x.saleType !== 'preliminary' && x.status !== 'preliminary');
            
            // Generate basic actual inventory mapping
            let actualStockMap = {}; 
            let purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            purchases = purchases.filter(x => !x.deleted);
            
            // Sort purchases by date ascending so the last one overwrites previous ones
            purchases.sort((a, b) => new Date(a.date || a.createdAt) - new Date(b.date || b.createdAt));
            
            purchases.forEach(inv => {
                const isReturn = inv.isReturn === true;
                
                // Calculate Landed Cost additions for this invoice
                let labor = parseFloat(String(inv.laborCost || 0).replace(/,/g, '')) || 0;
                let transport = parseFloat(String(inv.transportCost || 0).replace(/,/g, '')) || 0;
                let additions = parseFloat(String(inv.additionsCost || 0).replace(/,/g, '')) || 0;
                let tax = parseFloat(String(inv.taxCost || 0).replace(/,/g, '')) || 0;
                let discount = parseFloat(String(inv.discountCost || 0).replace(/,/g, '')) || 0;
                let totalAdditions = labor + transport + additions + tax - discount;
                
                let invSubtotal = parseFloat(String(inv.subTotal || inv.subtotal || 0).replace(/,/g, '')) || 0;
                
                (inv.items || inv.lines || []).forEach(line => {
                    let itemId = String(line.itemId || line.productId);
                    let item = itemsDb.find(i => String(i.id) === itemId);
                    let baseQty = line.customBaseQty !== undefined ? line.customBaseQty : ((parseFloat(line.quantity) || 0) * (line.customFactor || 1) * (item ? (item.conversionFactor || 1) : 1));
                    
                    if (!isReturn) {
                        let linePrice = parseFloat(String(line.price != null ? line.price : 0).replace(/,/g, '')) || 0;
                        let lineTotal = parseFloat(String(line.total != null ? line.total : 0).replace(/,/g, ''));
                        if (isNaN(lineTotal) || lineTotal === 0) {
                            lineTotal = (parseFloat(String(line.quantity || 0).replace(/,/g, '')) || 0) * linePrice;
                        }
                        
                        let safeSubtotal = invSubtotal > 0 ? invSubtotal : (lineTotal > 0 ? lineTotal : 1);
                        let ratio = lineTotal / safeSubtotal;
                        let apportionedExpenses = totalAdditions * ratio;
                        
                        let landedTotal = lineTotal + apportionedExpenses;
                        let unitCost = baseQty > 0 ? (landedTotal / baseQty) : 0;
                        
                        if (unitCost > 0) {
                            lastPurchasePriceMap[itemId] = unitCost; // Track actual landed purchase cost
                        }
                    }
                    
                    if (isReturn) {
                        actualStockMap[itemId] = (actualStockMap[itemId] || 0) - baseQty;
                    } else {
                        actualStockMap[itemId] = (actualStockMap[itemId] || 0) + baseQty;
                    }
                });
            });
            salesDb.forEach(inv => {
                const isReturn = inv.isReturn === true;
                (inv.items || []).forEach(line => {
                    let itemId = String(line.itemId);
                    if (inv.saleType === 'reserved' && inv.deliveryStatus !== 'delivered') {
                        return; // Skip if reserved but not delivered yet
                    }
                    let item = itemsDb.find(i => String(i.id) === itemId);
                    let baseQty = line.customBaseQty !== undefined ? line.customBaseQty : ((parseFloat(line.quantity) || 0) * (line.customFactor || 1) * (item ? (item.conversionFactor || 1) : 1));
                    if (isReturn) {
                        actualStockMap[itemId] = (actualStockMap[itemId] || 0) + baseQty;
                    } else {
                        actualStockMap[itemId] = (actualStockMap[itemId] || 0) - baseQty;
                    }
                });
            });
            inventoryDb = actualStockMap;

            runProfitabilityAnalysis();
            runAgingAnalysis();
            runABCAnalysis();
        }

        if (window.APP_READY) {
            initAnalytics();
        } else {
            document.addEventListener('DBReady', initAnalytics);
        }

        function switchTab(viewId, btn) {
            document.querySelectorAll('.view-section').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.sub-btn').forEach(el => el.classList.remove('active'));
            
            document.getElementById('view-' + viewId).style.display = 'block';
            btn.classList.add('active');
        }

        /* 1. Profitability Analysis */
        function runProfitabilityAnalysis() {
            let itemStats = {};
            itemsDb.forEach(item => {
                itemStats[String(item.id)] = { id: String(item.id), code: item.code, name: item.name, qty: 0, revenue: 0, cost: 0 };
            });

            salesDb.forEach(inv => {
                const isReturn = inv.isReturn === true;
                (inv.items || []).forEach(line => {
                    let itemId = String(line.itemId);
                    if (itemStats[itemId]) {
                        let item = itemsDb.find(i => String(i.id) === itemId);
                        let baseQty = line.customBaseQty !== undefined ? line.customBaseQty : ((parseFloat(line.quantity) || 0) * (line.customFactor || 1) * (item ? (item.conversionFactor || 1) : 1));
                        
                        let unitCost = lastPurchasePriceMap[itemId] || 0;
                        if (unitCost === 0 && item) {
                            let fallbackCost = item.costPrice || item.buyPrice || item.cost;
                            let openingVal = parseFloat(String(item.openingValue || 0).replace(/,/g, ''));
                            let openingQty = parseFloat(String(item.openingQty || 0).replace(/,/g, ''));
                            let sellingPrice = parseFloat(String(item.sellingPrice || 0).replace(/,/g, ''));
                            
                            if (fallbackCost != null && parseFloat(String(fallbackCost).replace(/,/g, '')) > 0) {
                                unitCost = parseFloat(String(fallbackCost).replace(/,/g, ''));
                            } else if (openingQty > 0 && openingVal > 0) {
                                unitCost = openingVal / openingQty;
                            } else if (sellingPrice > 0) {
                                unitCost = sellingPrice * 0.8; // 80% estimate fallback
                            }
                        }
                        
                        let lineRev = parseFloat(String(line.total != null ? line.total : 0).replace(/,/g, ''));
                        if (isNaN(lineRev) || lineRev === 0) {
                            let linePrice = parseFloat(String(line.price != null ? line.price : 0).replace(/,/g, '')) || 0;
                            lineRev = baseQty * linePrice;
                        }
                        
                        if (isReturn) {
                            itemStats[itemId].qty -= baseQty;
                            itemStats[itemId].revenue -= lineRev;
                            itemStats[itemId].cost -= (baseQty * unitCost);
                        } else {
                            itemStats[itemId].qty += baseQty;
                            itemStats[itemId].revenue += lineRev;
                            itemStats[itemId].cost += (baseQty * unitCost);
                        }
                    }
                });
            });

            let statsArray = Object.values(itemStats).filter(i => i.qty > 0);
            // Sort by highest profit
            statsArray.sort((a, b) => (b.revenue - b.cost) - (a.revenue - a.cost));

            let totalRev = 0, totalCost = 0, totalProfit = 0;
            const tbody = document.getElementById('tb-profitability');
            tbody.innerHTML = '';

            statsArray.forEach((row, idx) => {
                let profit = row.revenue - row.cost;
                let margin = row.revenue > 0 ? (profit / row.revenue * 100) : 0;
                
                totalRev += row.revenue;
                totalCost += row.cost;
                totalProfit += profit;

                tbody.innerHTML += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td style="color:#3b82f6; font-weight:bold;">${row.code || '-'}</td>
                        <td style="font-weight:bold;">${row.name}</td>
                        <td style="text-align:center; font-weight:bold;">${row.qty.toLocaleString()}</td>
                        <td style="color:#10b981; font-weight:bold;">${row.revenue.toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                        <td style="color:#ef4444;">${row.cost.toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                        <td style="color:#10b981; font-weight:900; font-size:1.05rem;">${profit.toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                        <td style="color:#3b82f6; font-weight:bold;">
                            ${margin.toFixed(1)}%
                            <div class="progress-bar"><div class="progress-fill" style="width: ${Math.min(margin, 100)}%;"></div></div>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('kpi-prof-sales').innerHTML = `${totalRev.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})} <span>SDG</span>`;
            document.getElementById('kpi-prof-cost').innerHTML = `${totalCost.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})} <span>SDG</span>`;
            document.getElementById('kpi-prof-profit').innerHTML = `${totalProfit.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})} <span>SDG</span>`;
            
            let avgMargin = totalRev > 0 ? (totalProfit / totalRev * 100) : 0;
            document.getElementById('kpi-prof-margin').innerHTML = `${avgMargin.toFixed(2)} <span>%</span>`;
        }

        /* 2. Aging & Dead Stock */
        function runAgingAnalysis() {
            let itemLastSale = {};
            salesDb.forEach(inv => {
                if (inv.isReturn) return; // Returns don't count as a recent sale
                let date = new Date(inv.date || inv.invoiceDate || inv.createdAt);
                (inv.items || []).forEach(line => {
                    let itemId = String(line.itemId);
                    if (!itemLastSale[itemId] || date > itemLastSale[itemId]) {
                        itemLastSale[itemId] = date;
                    }
                });
            });

            const today = new Date();
            let cActive = 0, cSlow = 0, cDead = 0, vFrozen = 0;
            
            let agingArray = [];
            itemsDb.forEach(item => {
                let curStock = inventoryDb[String(item.id)] || 0;
                if (curStock <= 0) return; // Only care about items currently in stock

                let lastDate = itemLastSale[String(item.id)];
                let days = 9999;
                if (lastDate) {
                    days = Math.floor((today - lastDate) / (1000 * 60 * 60 * 24));
                }

                let status = 'dead';
                let frozenVal = 0;
                if (days <= 30) {
                    status = 'active'; cActive++;
                } else if (days <= 90) {
                    status = 'slow'; cSlow++;
                } else {
                    status = 'dead'; cDead++;
                    frozenVal = curStock * (parseFloat(item.costPrice) || 0);
                    vFrozen += frozenVal;
                }

                agingArray.push({
                    id: String(item.id), code: item.code, name: item.name, stock: curStock,
                    lastDate: lastDate, days: days, status: status, frozenVal: frozenVal
                });
            });

            // Sort by days descending (worst first)
            agingArray.sort((a, b) => b.days - a.days);

            const tbody = document.getElementById('tb-aging');
            tbody.innerHTML = '';
            
            agingArray.forEach((row, idx) => {
                let statusHtml = '';
                if(row.status === 'active') statusHtml = `<span class="badge active">نشط (< 30 يوم)</span>`;
                else if(row.status === 'slow') statusHtml = `<span class="badge slow">بطيء (30-90 يوم)</span>`;
                else statusHtml = `<span class="badge dead">راكد (> 90 يوم)</span>`;

                tbody.innerHTML += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td style="color:#3b82f6; font-weight:bold;">${row.code || '-'}</td>
                        <td style="font-weight:bold;">${row.name}</td>
                        <td style="text-align:center; font-weight:bold; font-size:1.1rem;">${row.stock.toLocaleString()}</td>
                        <td>${row.lastDate ? row.lastDate.toLocaleDateString('en-CA') : '<span style="color:#ef4444">لم يباع أبداً</span>'}</td>
                        <td style="color:${row.days > 90 ? '#ef4444' : '#f8fafc'}; font-weight:bold;">
                            ${row.days === 9999 ? '∞' : row.days}
                        </td>
                        <td style="color:#ef4444; font-weight:bold;">${row.frozenVal > 0 ? row.frozenVal.toLocaleString(undefined,{minimumFractionDigits:2}) : '-'}</td>
                        <td>${statusHtml}</td>
                    </tr>
                `;
            });

            document.getElementById('kpi-age-active').innerHTML = `${cActive} <span>صنف</span>`;
            document.getElementById('kpi-age-slow').innerHTML = `${cSlow} <span>صنف</span>`;
            document.getElementById('kpi-age-dead').innerHTML = `${cDead} <span>صنف</span>`;
            document.getElementById('kpi-age-frozen').innerHTML = `${vFrozen.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})} <span>SDG</span>`;
        }

        /* 3. ABC Analysis */
        function runABCAnalysis() {
            let itemRev = {};
            itemsDb.forEach(item => itemRev[String(item.id)] = { id: String(item.id), code: item.code, name: item.name, rev: 0 });

            let totalRev = 0;
            salesDb.forEach(inv => {
                const isReturn = inv.isReturn === true;
                (inv.items || []).forEach(line => {
                    let itemId = String(line.itemId);
                    if (itemRev[itemId]) {
                        let lineRev = parseFloat(String(line.total != null ? line.total : 0).replace(/,/g, ''));
                        if (isNaN(lineRev) || lineRev === 0) {
                            let linePrice = parseFloat(String(line.price != null ? line.price : 0).replace(/,/g, '')) || 0;
                            // Estimate base quantity to recalculate lineRev
                            let item = itemsDb.find(i => String(i.id) === itemId);
                            let baseQty = line.customBaseQty !== undefined ? line.customBaseQty : ((parseFloat(line.quantity) || 0) * (line.customFactor || 1) * (item ? (item.conversionFactor || 1) : 1));
                            lineRev = baseQty * linePrice;
                        }

                        if (isReturn) {
                            itemRev[itemId].rev -= lineRev;
                            totalRev -= lineRev;
                        } else {
                            itemRev[itemId].rev += lineRev;
                            totalRev += lineRev;
                        }
                    }
                });
            });

            let abcArray = Object.values(itemRev).filter(i => i.rev > 0);
            abcArray.sort((a, b) => b.rev - a.rev); // Descending

            let cA = 0, cB = 0, cC = 0;
            let cumulativeRev = 0;
            
            const tbody = document.getElementById('tb-abc');
            tbody.innerHTML = '';

            abcArray.forEach((row, idx) => {
                cumulativeRev += row.rev;
                let pct = (row.rev / totalRev) * 100;
                let cumPct = (cumulativeRev / totalRev) * 100;

                let category = 'C';
                if (cumPct <= 80) { category = 'A'; cA++; }
                else if (cumPct <= 95) { category = 'B'; cB++; }
                else { category = 'C'; cC++; }

                tbody.innerHTML += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td style="font-weight:bold;"><span style="color:#94a3b8; font-size:0.85em; margin-left:8px;">${row.code||'-'}</span> ${row.name}</td>
                        <td style="color:#10b981; font-weight:bold;">${row.rev.toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                        <td>${pct.toFixed(2)}%</td>
                        <td>${cumPct.toFixed(2)}%</td>
                        <td><span class="badge ${category}">فئة ${category}</span></td>
                    </tr>
                `;
            });

            document.getElementById('kpi-abc-a').innerHTML = `${cA} <span>صنف</span>`;
            document.getElementById('kpi-abc-b').innerHTML = `${cB} <span>صنف</span>`;
            document.getElementById('kpi-abc-c').innerHTML = `${cC} <span>صنف</span>`;
        }
        function previewAnalyticsReport() {
            const activeSection = Array.from(document.querySelectorAll('.view-section')).find(el => el.style.display !== 'none');
            if (!activeSection) return;
            
            let title = 'تقرير التحليل الاستراتيجي للمخزن';
            if (activeSection.id === 'view-profitability') title = 'تحليل ربحية الأصناف';
            else if (activeSection.id === 'view-aging') title = 'تحليل أعمار المخزون وركود البضاعة';
            else if (activeSection.id === 'view-abc') title = 'تصنيف ABC لحركة المخزون';
            
            openReportPreview(title, activeSection);
        }
        function printAnalyticsReportDirectly() {
            const activeSection = Array.from(document.querySelectorAll('.view-section')).find(el => el.style.display !== 'none');
            if (!activeSection) return;
            
            let title = 'تقرير التحليل الاستراتيجي للمخزن';
            if (activeSection.id === 'view-profitability') title = 'تحليل ربحية الأصناف';
            else if (activeSection.id === 'view-aging') title = 'تحليل أعمار المخزون وركود البضاعة';
            else if (activeSection.id === 'view-abc') title = 'تصنيف ABC لحركة المخزون';
            
            printReportDirectly(title, activeSection);
        }
    </script>
</body>
</html>
