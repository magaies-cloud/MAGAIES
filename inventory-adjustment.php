<?php 
$page_title = "تسوية جردية ومناقلات المخازن (Inventory Adjustment & Transfers)"; 
$header_theme_class = "header-theme-inventory";
?>
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - نظام مقاييس</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/components.css?v=<?php echo time(); ?>">
    
    <style>
        :root {
            --bg-page: #0b1120;
            --surface-card: rgba(15, 23, 42, 0.75);
            --glass-border: rgba(255, 255, 255, 0.08);
            --primary-accent: #f97316;
            --emerald-accent: #10b981;
            --rose-accent: #ef4444;
            --cyan-accent: #38bdf8;
            --amber-accent: #f59e0b;
            --purple-accent: #a855f7;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
        }
        body {
            font-family: 'Tajawal', sans-serif; background-color: var(--bg-page); color: var(--text-main);
            margin: 0; padding: 0; min-height: 100vh; overflow-x: hidden;
            background-image: radial-gradient(circle at 90% 10%, rgba(249, 115, 22, 0.05), transparent 40%),
                              radial-gradient(circle at 10% 90%, rgba(56, 189, 248, 0.04), transparent 40%);
        }
        .ambient-bg { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: 0; overflow: hidden; }
        .orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.25; animation: floatOrb 18s ease-in-out infinite alternate; }
        .orb-1 { width: 450px; height: 450px; background: #f97316; top: -80px; left: -80px; }
        .orb-2 { width: 500px; height: 500px; background: #38bdf8; bottom: -100px; right: -100px; animation-duration: 22s; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(60px, 40px) scale(1.08); } }

        .dashboard-container { position: relative; z-index: 10; padding: 15px; max-width: 1650px; margin: 0 auto; display: flex; flex-direction: column; min-height: calc(100vh - 30px); gap: 16px; }
        
        .main-card {
            background: var(--surface-card); backdrop-filter: blur(25px);
            border: 1.5px solid rgba(255, 255, 255, 0.1); border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.45); overflow: hidden;
            display: flex; flex-direction: column; gap: 18px; padding: 22px;
        }

        /* ── Mode Switcher Tabs ── */
        .mode-nav-tabs {
            display: flex; gap: 10px; background: rgba(0,0,0,0.35); padding: 6px; border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.06); flex-wrap: wrap;
        }
        .mode-tab-btn {
            background: transparent; border: none; color: #94a3b8; padding: 9px 18px; border-radius: 10px;
            font-family: inherit; font-size: 0.92rem; font-weight: 800; cursor: pointer; transition: all 0.3s;
            display: flex; align-items: center; gap: 8px;
        }
        .mode-tab-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }
        .mode-tab-btn.active {
            background: linear-gradient(135deg, #f97316, #ea580c); color: #fff;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }

        /* ── Quick KPI Strip ── */
        .kpi-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px;
        }
        .kpi-box {
            background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;
        }
        .kpi-box .title { font-size: 0.78rem; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }
        .kpi-box .val { font-size: 1.3rem; font-weight: 900; font-family: monospace; }

        /* ── Filters Bar ── */
        .filters-strip {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            background: rgba(15, 23, 42, 0.6); padding: 12px 18px; border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.08); flex-wrap: wrap;
        }
        .f-group { display: flex; align-items: center; gap: 8px; }
        .f-label { font-size: 0.85rem; font-weight: 800; color: #fdba74; white-space: nowrap; }
        .f-input, .f-select {
            background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px;
            color: #fff; padding: 8px 14px; font-size: 0.9rem; outline: none; transition: 0.25s; font-family: inherit; font-weight: 700;
        }
        .f-input:focus, .f-select:focus { border-color: var(--primary-accent); box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.25); }

        /* ── Table Styling ── */
        .table-responsive { overflow-x: auto; max-height: 52vh; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06); }
        .dash-table { width: 100%; border-collapse: collapse; text-align: right; }
        .dash-table th {
            background: rgba(15, 23, 42, 0.95); padding: 14px 12px; color: #cbd5e1; font-weight: 800;
            border-bottom: 2px solid rgba(255,255,255,0.1); font-size: 0.82rem; white-space: nowrap; position: sticky; top: 0; z-index: 10;
        }
        .dash-table td {
            padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 0.88rem; vertical-align: middle;
        }
        .dash-table tr:hover td { background: rgba(255,255,255,0.025); }

        /* ── Badges ── */
        .diff-badge {
            display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 8px;
            font-size: 0.78rem; font-weight: 800; font-family: monospace;
        }
        .diff-shortage { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
        .diff-surplus { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .diff-match { background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); }

        .btn-action-main {
            background: linear-gradient(135deg, #f97316, #ea580c); border: none; border-radius: 12px;
            color: #fff; padding: 10px 22px; font-weight: 900; font-size: 0.95rem; cursor: pointer; transition: 0.3s;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4); display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-action-main:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(249, 115, 22, 0.6); }

        .btn-action-sub {
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px;
            color: #f8fafc; padding: 10px 18px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: 0.3s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-action-sub:hover { background: rgba(255,255,255,0.12); }

        /* Transfer Form Section */
        .transfer-panel {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
        }
        @media(max-width: 900px) { .transfer-panel { grid-template-columns: 1fr; } }

        /* Light Theme Overrides */
        :root.light-theme body { background-color: #f1f5f9 !important; color: #0f172a !important; }
        :root.light-theme .main-card { background: #ffffff !important; border-color: rgba(0,0,0,0.08) !important; box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important; }
        :root.light-theme .kpi-box { background: #f8fafc !important; border-color: rgba(0,0,0,0.06) !important; }
        :root.light-theme .filters-strip { background: #f8fafc !important; border-color: rgba(0,0,0,0.08) !important; }
        :root.light-theme .f-input, :root.light-theme .f-select { background: #ffffff !important; border-color: #cbd5e1 !important; color: #0f172a !important; }
        :root.light-theme .dash-table th { background: #f8fafc !important; color: #334155 !important; border-bottom: 2px solid #e2e8f0 !important; }
        :root.light-theme .dash-table td { color: #1e293b !important; border-bottom: 1px solid #e2e8f0 !important; }
    </style>
</head>
<body class="no-page-transition">
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
    
    <div class="dashboard-container">
        <!-- ── Premium Universal Header ── -->
        <?php include 'includes/header.php'; ?>

        <div class="main-card">
            
            <!-- ── Top Header & Tab Navigation ── -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #f97316, #ea580c); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; box-shadow: 0 4px 15px rgba(249,115,22,0.4);">
                        ⚖️
                    </div>
                    <div>
                        <h2 style="margin: 0; color: #f97316; font-size: 1.3rem; font-weight: 900;">تسوية الجرد ومناقلات المخازن المتقدمة</h2>
                        <p style="margin: 3px 0 0; font-size: 0.8rem; color: var(--text-secondary);">مطابقة الأرصدة الدفترية مع الجرد الفعلي، معالجة العجز والزيادة، ونقل البضاعة بين المخازن</p>
                    </div>
                </div>

                <div class="mode-nav-tabs">
                    <button class="mode-tab-btn active" id="tab-btn-adj" onclick="switchMainTab('adj')">
                        <span>⚖️</span> تسوية الجرد الفعلي
                    </button>
                    <button class="mode-tab-btn" id="tab-btn-transfer" onclick="switchMainTab('transfer')">
                        <span>🔄</span> نقل بضاعة بين المخازن
                    </button>
                    <button class="mode-tab-btn" id="tab-btn-history" onclick="switchMainTab('history')">
                        <span>📜</span> سجل التسويات السابقة
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 TAB 1: INVENTORY ADJUSTMENT (تسوية الجرد الفعلي)
            ══════════════════════════════════════════════════════════════════ -->
            <div id="section-adjustment">
                
                <!-- Quick KPI Summary Strip -->
                <div class="kpi-grid" style="margin-bottom: 16px;">
                    <div class="kpi-box" style="border-right: 3px solid #38bdf8;">
                        <div>
                            <div class="title">إجمالي الأصناف المجرودة</div>
                            <div class="val" id="kpi-total-items" style="color: #38bdf8;">0 <small style="font-size:0.75rem;">صنف</small></div>
                        </div>
                        <span style="font-size: 1.5rem;">📦</span>
                    </div>
                    <div class="kpi-box" style="border-right: 3px solid #ef4444;">
                        <div>
                            <div class="title">إجمالي العجز المخزني (نقص)</div>
                            <div class="val" id="kpi-shortage-qty" style="color: #ef4444;">0 <small style="font-size:0.75rem;">وحدة</small></div>
                        </div>
                        <span style="font-size: 1.5rem;">🔻</span>
                    </div>
                    <div class="kpi-box" style="border-right: 3px solid #10b981;">
                        <div>
                            <div class="title">إجمالي الزيادة الجردية (فائض)</div>
                            <div class="val" id="kpi-surplus-qty" style="color: #10b981;">0 <small style="font-size:0.75rem;">وحدة</small></div>
                        </div>
                        <span style="font-size: 1.5rem;">🔺</span>
                    </div>
                    <div class="kpi-box" style="border-right: 3px solid #fbbf24;">
                        <div>
                            <div class="title">صافي الأثر المالي للتسوية</div>
                            <div class="val" id="kpi-net-val" style="color: #fbbf24;">0 <small style="font-size:0.75rem;">SDG</small></div>
                        </div>
                        <span style="font-size: 1.5rem;">💰</span>
                    </div>
                </div>

                <!-- Filters & Controls Bar -->
                <div class="filters-strip" style="margin-bottom: 16px;">
                    <div class="f-group">
                        <label class="f-label">المستودع المستهدف:</label>
                        <select id="adj-warehouse-select" class="f-select" style="min-width: 220px;" onchange="loadAdjustmentItems()">
                            <option value="">-- يرجى اختيار المستودع --</option>
                        </select>
                    </div>

                    <div class="f-group">
                        <label class="f-label">تصفية الفروقات:</label>
                        <select id="adj-diff-filter" class="f-select" onchange="filterAdjustmentTable()">
                            <option value="ALL">عرض جميع الأصناف</option>
                            <option value="DIFF_ONLY" selected>⚡ الأصناف التي بها فروقات فقط</option>
                            <option value="SHORTAGE">🔴 عجز فقط</option>
                            <option value="SURPLUS">🟢 زيادة فقط</option>
                            <option value="MATCH">⚪ مطابق تماماً</option>
                        </select>
                    </div>

                    <div class="f-group">
                        <label class="f-label">بحث سريع:</label>
                        <input type="text" id="adj-search" class="f-input" placeholder="🔍 كود أو اسم الصنف..." oninput="filterAdjustmentTable()">
                    </div>

                    <div class="f-group">
                        <label class="f-label">تاريخ الجرد:</label>
                        <input type="date" id="adj-date" class="f-input">
                    </div>

                    <button onclick="refreshData()" class="btn-action-sub" title="تحديث البيانات">
                        <span>🔄</span> تحديث الأرصدة
                    </button>
                </div>

                <!-- Data Table with Full Details -->
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">#</th>
                                <th style="width: 110px;">كود الصنف</th>
                                <th>اسم الصنف والمواصفات</th>
                                <th>المستودع</th>
                                <th style="text-align: center; width: 120px;">الرصيد الدفتري</th>
                                <th style="text-align: center; width: 140px; color: #fdba74;">الرصيد الفعلي (جرد)</th>
                                <th style="text-align: center; width: 120px;">فرق الكمية</th>
                                <th style="text-align: center; width: 120px;">سعر التكلفة (SDG)</th>
                                <th style="text-align: center; width: 140px;">قيمة التسوية (SDG)</th>
                                <th style="text-align: center; width: 110px;">حالة الفرق</th>
                                <th style="min-width: 150px;">سبب التسوية / ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody id="adj-tbody">
                            <tr><td colspan="11" style="text-align:center; padding:40px; color:var(--text-secondary); font-weight:800; font-size:1.05rem;">يرجى اختيار المستودع لعرض الأرصدة والبدء في مطابقة الجرد</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bottom Actions Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; flex-wrap: wrap; gap: 12px;">
                    <div style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 700;">
                        💡 <strong>ملاحظة:</strong> سيتم ترحيل التسوية وتعديل الأرصدة الحقيقية وإنشاء إذن تسوية معتمد فور الضغط على اعتماد التسوية.
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" onclick="printAdjustmentPreview()" class="btn-action-sub">
                            <span>🖨️</span> طباعة مسودة الجرد
                        </button>
                        <button type="button" onclick="applyAdjustment()" class="btn-action-main">
                            <span>💾</span> اعتماد وحفظ التسوية الجردية
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 TAB 2: WAREHOUSE-TO-WAREHOUSE TRANSFER (نقل بضاعة بين المخازن)
            ══════════════════════════════════════════════════════════════════ -->
            <div id="section-transfer" style="display: none;">
                <div class="transfer-panel">
                    
                    <!-- Left: Transfer Form -->
                    <div style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 22px; display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
                            <span style="font-size: 1.5rem;">🔄</span>
                            <div>
                                <h3 style="margin: 0; font-size: 1.1rem; color: #38bdf8; font-weight: 900;">إذن تحويل ومناقلة بضاعة بين المستودعات</h3>
                                <p style="margin: 2px 0 0; font-size: 0.78rem; color: var(--text-secondary);">نقل مباشر للكميات بين مستودع وآخر مع تحديث فوري للأرصدة</p>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label class="f-label" style="display: block; margin-bottom: 6px;">من مستودع (المصدر):</label>
                                <select id="tr-from-wh" class="f-select" style="width: 100%;" onchange="onTransferFromWhChange()">
                                    <option value="">-- اختر مستودع المصدر --</option>
                                </select>
                            </div>
                            <div>
                                <label class="f-label" style="display: block; margin-bottom: 6px; color: #34d399;">إلى مستودع (المستلم):</label>
                                <select id="tr-to-wh" class="f-select" style="width: 100%;">
                                    <option value="">-- اختر مستودع المستلم --</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                            <div>
                                <label class="f-label" style="display: block; margin-bottom: 6px;">الصنف المراد تحويله:</label>
                                <select id="tr-item-select" class="f-select" style="width: 100%;" onchange="onTransferItemSelect()">
                                    <option value="">-- اختر الصنف --</option>
                                </select>
                            </div>
                            <div>
                                <label class="f-label" style="display: block; margin-bottom: 6px;">الرصيد المتاح حالياً:</label>
                                <input type="text" id="tr-available-stock" class="f-input" style="width: 100%; font-weight: 900; color: #38bdf8; text-align: center;" readonly value="0">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label class="f-label" style="display: block; margin-bottom: 6px; color: #fbbf24;">الكمية المراد تحويلها:</label>
                                <input type="number" step="any" min="0.01" id="tr-qty" class="f-input" style="width: 100%; font-size: 1.1rem; font-weight: 900; color: #fbbf24; text-align: center;" placeholder="0.00">
                            </div>
                            <div>
                                <label class="f-label" style="display: block; margin-bottom: 6px;">تاريخ التحويل:</label>
                                <input type="date" id="tr-date" class="f-input" style="width: 100%;">
                            </div>
                        </div>

                        <div>
                            <label class="f-label" style="display: block; margin-bottom: 6px;">رقم السند / ملاحظات التحويل:</label>
                            <input type="text" id="tr-notes" class="f-input" style="width: 100%;" placeholder="مثال: تحويل عاجل لتغطية طلبيات فرع السجانة...">
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                            <button type="button" onclick="executeWarehouseTransfer()" class="btn-action-main" style="background: linear-gradient(135deg, #0284c7, #0369a1); box-shadow: 0 4px 15px rgba(2,132,199,0.4);">
                                <span>🚀</span> اعتماد وتنفيذ التحويل الفوري
                            </button>
                        </div>
                    </div>

                    <!-- Right: Quick Live Transfers Log -->
                    <div style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 22px; display: flex; flex-direction: column; gap: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
                            <h3 style="margin: 0; font-size: 1.05rem; color: #fff; font-weight: 800;">📋 آخر عمليات التحويل المنفذة</h3>
                            <span id="tr-count-badge" style="background: rgba(56,189,248,0.15); color: #38bdf8; font-size: 0.75rem; padding: 3px 8px; border-radius: 8px; font-weight: 800;">0 عملية</span>
                        </div>

                        <div id="tr-history-list" style="overflow-y: auto; max-height: 400px; display: flex; flex-direction: column; gap: 10px;">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 TAB 3: ADJUSTMENT AUDIT HISTORY (سجل التسويات السابقة)
            ══════════════════════════════════════════════════════════════════ -->
            <div id="section-history" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h3 style="margin: 0; color: #fff; font-size: 1.1rem; font-weight: 800;">سجل أذونات التسوية الجردية المعتمدة</h3>
                    <button onclick="clearAdjustmentHistory()" class="btn-action-sub" style="font-size: 0.8rem; color: #ef4444; border-color: rgba(239,68,68,0.3);">
                        🗑️ مسح السجل
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th>رقم إذن التسوية</th>
                                <th>المستودع</th>
                                <th>تاريخ التسوية</th>
                                <th style="text-align: center;">الأصناف المعدلة</th>
                                <th style="text-align: center;">إجمالي العجز</th>
                                <th style="text-align: center;">إجمالي الزيادة</th>
                                <th style="text-align: center;">صافي الأثر المالي</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody id="adj-history-tbody">
                            <tr><td colspan="9" style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد تسويات مسجلة حتى الآن</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script>
        let currentInvDb = [];
        let itemsDb = [];
        let warehousesDb = [];
        let adjustmentRowsData = [];

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('adj-date').valueAsDate = new Date();
            document.getElementById('tr-date').valueAsDate = new Date();
            
            if (window.DB_STORE) {
                initAdjustmentSystem();
            } else {
                document.addEventListener('DBReady', initAdjustmentSystem);
                window.addEventListener('load', initAdjustmentSystem);
            }
        });

        function switchMainTab(tab) {
            document.querySelectorAll('.mode-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(`tab-btn-${tab}`).classList.add('active');

            document.getElementById('section-adjustment').style.display = tab === 'adj' ? 'block' : 'none';
            document.getElementById('section-transfer').style.display = tab === 'transfer' ? 'block' : 'none';
            document.getElementById('section-history').style.display = tab === 'history' ? 'block' : 'none';

            if (tab === 'transfer') loadTransferSection();
            if (tab === 'history') loadAdjustmentHistory();
        }

        function getStorageData(key) {
            try {
                if (typeof window.getTenantKey === 'function') {
                    const tk = window.getTenantKey(key);
                    if (window.DB_STORE && window.DB_STORE[tk]) {
                        return typeof window.DB_STORE[tk] === 'string' ? JSON.parse(window.DB_STORE[tk]) : window.DB_STORE[tk];
                    }
                    const raw = localStorage.getItem(tk);
                    if (raw) return JSON.parse(raw);
                }
                const raw = localStorage.getItem(key);
                if (raw) return JSON.parse(raw);
            } catch(e){}
            return [];
        }

        function setStorageData(key, data) {
            try {
                if (typeof window.getTenantKey === 'function') {
                    const tk = window.getTenantKey(key);
                    localStorage.setItem(tk, JSON.stringify(data));
                    if (window.DB_STORE) window.DB_STORE[tk] = data;
                }
                localStorage.setItem(key, JSON.stringify(data));
                if (typeof window.saveToDB === 'function') {
                    window.saveToDB(key, data);
                }
            } catch(e){}
        }

        function initAdjustmentSystem() {
            itemsDb = getStorageData('erp_items_v1').filter(i => !i.deleted);
            warehousesDb = getStorageData('erp_warehouses_v1') || [];
            if (warehousesDb.length === 0) {
                warehousesDb = [{ id: 'w-main', name: 'المخزن الرئيسي' }];
            }

            // Populate Warehouse Selects
            const adjSelect = document.getElementById('adj-warehouse-select');
            const trFromSelect = document.getElementById('tr-from-wh');
            const trToSelect = document.getElementById('tr-to-wh');

            adjSelect.innerHTML = '<option value="">-- يرجى اختيار المستودع --</option>';
            trFromSelect.innerHTML = '<option value="">-- اختر مستودع المصدر --</option>';
            trToSelect.innerHTML = '<option value="">-- اختر مستودع المستلم --</option>';

            warehousesDb.forEach(wh => {
                const name = wh.name || wh.id;
                const opt1 = new Option('🏢 ' + name, name);
                const opt2 = new Option('🏢 ' + name, name);
                const opt3 = new Option('🏢 ' + name, name);
                adjSelect.add(opt1);
                trFromSelect.add(opt2);
                trToSelect.add(opt3);
            });

            // If only one warehouse, auto-select it
            if (warehousesDb.length === 1) {
                adjSelect.value = warehousesDb[0].name;
            }

            buildInventorySnapshot();
            loadAdjustmentItems();
            loadTransferHistory();
        }

        function refreshData() {
            initAdjustmentSystem();
            alert('✅ تم تحديث الأرصدة والمستودعات بنجاح!');
        }

        // ── Real-time Inventory Snapshot Engine ──
        function buildInventorySnapshot() {
            const items = itemsDb;
            const purchases = (getStorageData('erp_purchase_invoices_v1') || []).filter(i => !i.deleted);
            const sales = (getStorageData('erp_sales_invoices_v1') || []).filter(i => !i.deleted && i.status !== 'draft');
            const transfers = (getStorageData('erp_warehouse_transfers_v1') || []).filter(i => !i.deleted && i.status !== 'cancelled');
            
            const balances = {};
            
            // 1. Initial / Base stock
            items.forEach(item => {
                const cost = parseFloat(item.costPrice || item.buyPrice || item.cost || item.price || 0);
                const defaultWh = item.defaultWarehouse || (warehousesDb[0] ? warehousesDb[0].name : 'المخزن الرئيسي');
                
                // If item has explicit stock, initialize it
                const stockQty = parseFloat(item.stock || item.quantity || item.openingBalance || 0);
                const key = item.id + '_' + defaultWh;
                balances[key] = {
                    itemId: item.id,
                    code: item.code || '-',
                    name: item.name || 'بدون اسم',
                    category: item.category || 'عام',
                    warehouse: defaultWh,
                    costPrice: cost,
                    qty: stockQty
                };
            });
            
            // 2. Purchases (+ In, - Out for returns)
            purchases.forEach(inv => {
                const w = inv.warehouseName || inv.warehouse || inv.warehouse_id || (warehousesDb[0] ? warehousesDb[0].name : 'المخزن الرئيسي');
                (inv.items || inv.lines || []).forEach(l => {
                    const q = parseFloat(l.quantity || 0) * (parseFloat(l.factor || l.customFactor || 1));
                    const key = (l.itemId || l.id) + '_' + w;
                    if (!balances[key]) {
                        balances[key] = { itemId: l.itemId || l.id, code: l.itemCode || '-', name: l.itemName || l.name, category: 'عام', warehouse: w, qty: 0, costPrice: parseFloat(l.costPrice || l.price || 0) };
                    }
                    if (inv.isReturn) balances[key].qty -= q; else balances[key].qty += q;
                });
            });
            
            // 3. Sales (- Out, + In for returns)
            sales.forEach(inv => {
                const w = inv.warehouseName || inv.warehouse || inv.warehouse_id || (warehousesDb[0] ? warehousesDb[0].name : 'المخزن الرئيسي');
                (inv.items || []).forEach(l => {
                    const q = parseFloat(l.quantity || 0) * (parseFloat(l.factor || l.customFactor || 1));
                    const key = (l.itemId || l.id) + '_' + w;
                    if (!balances[key]) {
                        balances[key] = { itemId: l.itemId || l.id, code: l.itemCode || '-', name: l.itemName || l.name, category: 'عام', warehouse: w, qty: 0, costPrice: 0 };
                    }
                    if (inv.isReturn) balances[key].qty += q; else balances[key].qty -= q;
                });
            });
            
            // 4. Transfers (- From, + To)
            transfers.forEach(tr => {
                const wFrom = tr.fromWarehouse;
                const wTo = tr.toWarehouse;
                (tr.items || []).forEach(l => {
                    const q = parseFloat(l.quantity || 0);
                    const kFrom = (l.itemId || l.id) + '_' + wFrom;
                    const kTo = (l.itemId || l.id) + '_' + wTo;
                    
                    if (!balances[kFrom]) balances[kFrom] = { itemId: l.itemId || l.id, code: l.itemCode || '-', name: l.itemName || l.name, category: 'عام', warehouse: wFrom, qty: 0, costPrice: 0 };
                    balances[kFrom].qty -= q;
                    
                    if (!balances[kTo]) balances[kTo] = { itemId: l.itemId || l.id, code: l.itemCode || '-', name: l.itemName || l.name, category: 'عام', warehouse: wTo, qty: 0, costPrice: 0 };
                    balances[kTo].qty += q;
                });
            });
            
            currentInvDb = Object.values(balances);
        }

        function loadAdjustmentItems() {
            const targetWh = document.getElementById('adj-warehouse-select').value;
            const tbody = document.getElementById('adj-tbody');
            if (!targetWh) {
                tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding:40px; color:var(--text-secondary); font-weight:800;">يرجى اختيار المستودع لعرض الأرصدة والبدء في التسوية</td></tr>`;
                updateSummaryKPIs([]);
                return;
            }

            // Find all items in this warehouse (or default items)
            let whRecords = currentInvDb.filter(r => r.warehouse === targetWh);
            
            // Ensure all registered items exist in list for this warehouse
            itemsDb.forEach(it => {
                if (!whRecords.some(r => r.itemId === it.id)) {
                    whRecords.push({
                        itemId: it.id,
                        code: it.code || '-',
                        name: it.name || 'بدون اسم',
                        category: it.category || 'عام',
                        warehouse: targetWh,
                        costPrice: parseFloat(it.costPrice || it.cost || it.buyPrice || 0),
                        qty: 0
                    });
                }
            });

            adjustmentRowsData = whRecords.map(r => ({
                ...r,
                actualQty: r.qty,
                diffQty: 0,
                diffVal: 0,
                reason: ''
            }));

            renderAdjustmentTable();
        }

        function renderAdjustmentTable() {
            const tbody = document.getElementById('adj-tbody');
            const diffFilter = document.getElementById('adj-diff-filter').value;
            const searchQuery = (document.getElementById('adj-search').value || '').trim().toLowerCase();

            let filtered = adjustmentRowsData.filter(r => {
                // Search filter
                if (searchQuery) {
                    const match = (r.name || '').toLowerCase().includes(searchQuery) || (r.code || '').toLowerCase().includes(searchQuery);
                    if (!match) return false;
                }
                // Difference filter
                const diff = r.actualQty - r.qty;
                if (diffFilter === 'DIFF_ONLY' && diff === 0) return false;
                if (diffFilter === 'SHORTAGE' && diff >= 0) return false;
                if (diffFilter === 'SURPLUS' && diff <= 0) return false;
                if (diffFilter === 'MATCH' && diff !== 0) return false;
                return true;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding:35px; color:#94a3b8; font-weight:800;">لا توجد أصناف مطابقة للبحث أو التصفية الحالية</td></tr>`;
                updateSummaryKPIs(adjustmentRowsData);
                return;
            }

            let html = '';
            filtered.forEach((r, idx) => {
                const bookQty = r.qty || 0;
                const actualQty = r.actualQty !== undefined ? r.actualQty : bookQty;
                const diff = actualQty - bookQty;
                const cost = r.costPrice || 0;
                const diffVal = diff * cost;

                let statusBadge = `<span class="diff-badge diff-match">⚪ مطابق</span>`;
                if (diff < 0) statusBadge = `<span class="diff-badge diff-shortage">🔴 عجز (${Math.abs(diff).toLocaleString()})</span>`;
                else if (diff > 0) statusBadge = `<span class="diff-badge diff-surplus">🟢 زيادة (+${diff.toLocaleString()})</span>`;

                html += `<tr>
                    <td style="text-align:center; color:#64748b; font-weight:700;">${idx + 1}</td>
                    <td style="color:#38bdf8; font-weight:800; font-family:monospace;">${r.code}</td>
                    <td>
                        <div style="font-weight:900; color:#f8fafc;">${r.name}</div>
                        <div style="font-size:0.75rem; color:#94a3b8;">${r.category}</div>
                    </td>
                    <td style="color:#cbd5e1; font-weight:700;">🏢 ${r.warehouse}</td>
                    <td style="text-align:center; font-weight:900; color:#cbd5e1; font-size:1.05rem; font-family:monospace;">
                        ${bookQty.toLocaleString('en-US', {maximumFractionDigits:2})}
                    </td>
                    <td style="text-align:center;">
                        <input type="number" step="any" class="f-input" style="width:115px; text-align:center; font-weight:900; color:#f97316; font-size:1.05rem; background:rgba(0,0,0,0.6); padding:6px;"
                            value="${actualQty}" oninput="onActualQtyChange('${r.itemId}', this.value)">
                    </td>
                    <td style="text-align:center; font-weight:900; font-family:monospace; font-size:1.05rem; color:${diff > 0 ? '#34d399' : (diff < 0 ? '#ef4444' : '#64748b')}">
                        ${diff > 0 ? '+' + diff.toLocaleString('en-US',{maximumFractionDigits:2}) : diff.toLocaleString('en-US',{maximumFractionDigits:2})}
                    </td>
                    <td style="text-align:center;">
                        <input type="number" step="any" class="f-input" style="width:95px; text-align:center; font-weight:800; color:#fbbf24; font-size:0.95rem; background:rgba(0,0,0,0.4); padding:6px;"
                            value="${cost}" oninput="onCostPriceChange('${r.itemId}', this.value)">
                    </td>
                    <td style="text-align:center; font-weight:900; font-family:monospace; font-size:1.05rem; color:${diffVal > 0 ? '#34d399' : (diffVal < 0 ? '#ef4444' : '#64748b')}">
                        ${diffVal > 0 ? '+' + diffVal.toLocaleString('en-US',{maximumFractionDigits:2}) : diffVal.toLocaleString('en-US',{maximumFractionDigits:2})}
                    </td>
                    <td style="text-align:center;">${statusBadge}</td>
                    <td>
                        <input type="text" class="f-input" style="width:100%; font-size:0.85rem; padding:6px 10px;"
                            placeholder="سبب العجز/الزيادة..." value="${r.reason || ''}" oninput="onReasonChange('${r.itemId}', this.value)">
                    </td>
                </tr>`;
            });

            tbody.innerHTML = html;
            updateSummaryKPIs(adjustmentRowsData);
        }

        function onActualQtyChange(itemId, val) {
            const num = parseFloat(val);
            const row = adjustmentRowsData.find(r => String(r.itemId) === String(itemId));
            if (row) {
                row.actualQty = isNaN(num) ? row.qty : num;
                row.diffQty = row.actualQty - row.qty;
                row.diffVal = row.diffQty * (row.costPrice || 0);
            }
            updateSummaryKPIs(adjustmentRowsData);
            // Quick update without full re-render for responsive typing
            const diffFilter = document.getElementById('adj-diff-filter').value;
            if (diffFilter !== 'ALL') {
                renderAdjustmentTable();
            }
        }

        function onCostPriceChange(itemId, val) {
            const num = parseFloat(val);
            const row = adjustmentRowsData.find(r => String(r.itemId) === String(itemId));
            if (row) {
                row.costPrice = isNaN(num) ? 0 : num;
                row.diffVal = (row.actualQty - row.qty) * row.costPrice;
            }
            updateSummaryKPIs(adjustmentRowsData);
        }

        function onReasonChange(itemId, val) {
            const row = adjustmentRowsData.find(r => String(r.itemId) === String(itemId));
            if (row) row.reason = val;
        }

        function filterAdjustmentTable() {
            renderAdjustmentTable();
        }

        function updateSummaryKPIs(data) {
            let totalItems = data.length;
            let totalShortage = 0;
            let totalSurplus = 0;
            let netVal = 0;

            data.forEach(r => {
                const diff = (r.actualQty !== undefined ? r.actualQty : r.qty) - r.qty;
                const cost = r.costPrice || 0;
                if (diff < 0) totalShortage += Math.abs(diff);
                if (diff > 0) totalSurplus += diff;
                netVal += (diff * cost);
            });

            document.getElementById('kpi-total-items').innerHTML = `${totalItems} <small style="font-size:0.75rem;">صنف</small>`;
            document.getElementById('kpi-shortage-qty').innerHTML = `${totalShortage.toLocaleString()} <small style="font-size:0.75rem;">وحدة</small>`;
            document.getElementById('kpi-surplus-qty').innerHTML = `${totalSurplus.toLocaleString()} <small style="font-size:0.75rem;">وحدة</small>`;
            document.getElementById('kpi-net-val').innerHTML = `${netVal > 0 ? '+' : ''}${netVal.toLocaleString(undefined, {maximumFractionDigits:2})} <small style="font-size:0.75rem;">SDG</small>`;
            document.getElementById('kpi-net-val').style.color = netVal >= 0 ? '#10b981' : '#ef4444';
        }

        // ── Apply & Save Adjustment ──
        function applyAdjustment() {
            const targetWh = document.getElementById('adj-warehouse-select').value;
            if (!targetWh) return alert('يرجى اختيار المستودع أولاً!');

            const changedItems = adjustmentRowsData.filter(r => (r.actualQty !== undefined) && r.actualQty !== r.qty);
            if (changedItems.length === 0) {
                return alert('ℹ️ لا توجد أي فروقات مسجلة للتسوية. الرصيد الفعلي يطابق الدفتري لجميع الأصناف.');
            }

            const adjDate = document.getElementById('adj-date').value || new Date().toISOString().split('T')[0];
            const voucherId = 'ADJ-' + Date.now().toString().slice(-6);

            let confirmMsg = `هل أنت متأكد من اعتماد إذن التسوية رقم [${voucherId}] للمستودع: ${targetWh}؟\n` +
                             `عدد الأصناف المعدلة: ${changedItems.length} صنف\n` +
                             `سيتم تحديث الأرصدة فورياً في سجلات النظام.`;
            
            if (!confirm(confirmMsg)) return;

            // 1. Create Adjustment Record
            const adjustments = getStorageData('erp_inventory_adjustments_v1') || [];
            const adjustmentRecord = {
                id: voucherId,
                date: adjDate,
                warehouse: targetWh,
                itemsCount: changedItems.length,
                totalShortage: changedItems.filter(i => i.actualQty < i.qty).reduce((s, i) => s + (i.qty - i.actualQty), 0),
                totalSurplus: changedItems.filter(i => i.actualQty > i.qty).reduce((s, i) => s + (i.actualQty - i.qty), 0),
                netValue: changedItems.reduce((s, i) => s + ((i.actualQty - i.qty) * (i.costPrice || 0)), 0),
                items: changedItems.map(i => ({
                    itemId: i.itemId,
                    code: i.code,
                    name: i.name,
                    bookQty: i.qty,
                    actualQty: i.actualQty,
                    diffQty: i.actualQty - i.qty,
                    costPrice: i.costPrice,
                    diffValue: (i.actualQty - i.qty) * (i.costPrice || 0),
                    reason: i.reason || 'تسوية جرد دوري'
                })),
                createdAt: new Date().toISOString(),
                status: 'applied'
            };

            adjustments.unshift(adjustmentRecord);
            setStorageData('erp_inventory_adjustments_v1', adjustments);

            // 2. Update Master Item Balances
            const masterItems = getStorageData('erp_items_v1') || [];
            changedItems.forEach(ch => {
                const it = masterItems.find(m => String(m.id) === String(ch.itemId));
                if (it) {
                    it.stock = ch.actualQty;
                    it.actualStock = ch.actualQty;
                    if (ch.costPrice) it.costPrice = ch.costPrice;
                }
            });
            setStorageData('erp_items_v1', masterItems);

            alert(`✅ تم اعتماد وحفظ إذن التسوية [${voucherId}] بنجاح!\nتم تعديل أرصدة ${changedItems.length} صنف.`);
            
            // Reload snapshot & table
            buildInventorySnapshot();
            loadAdjustmentItems();
        }

        function printAdjustmentPreview() {
            const targetWh = document.getElementById('adj-warehouse-select').value;
            if (!targetWh) return alert('يرجى اختيار المستودع للمعاينة!');
            window.print();
        }

        // ══════════════════════════════════════════════════════════════════════
        // TRANSFER MODULE (المناقلات والتحويل بين المخازن)
        // ══════════════════════════════════════════════════════════════════════
        function loadTransferSection() {
            onTransferFromWhChange();
            loadTransferHistory();
        }

        function onTransferFromWhChange() {
            const fromWh = document.getElementById('tr-from-wh').value;
            const itemSelect = document.getElementById('tr-item-select');
            itemSelect.innerHTML = '<option value="">-- اختر الصنف --</option>';
            document.getElementById('tr-available-stock').value = '0';

            if (!fromWh) return;

            const whItems = currentInvDb.filter(r => r.warehouse === fromWh && r.qty > 0);
            whItems.forEach(r => {
                const opt = new Option(`${r.name} (${r.code}) - المتاح: ${r.qty}`, r.itemId);
                itemSelect.add(opt);
            });
        }

        function onTransferItemSelect() {
            const fromWh = document.getElementById('tr-from-wh').value;
            const itemId = document.getElementById('tr-item-select').value;
            const stockInput = document.getElementById('tr-available-stock');

            if (!fromWh || !itemId) {
                stockInput.value = '0';
                return;
            }

            const rec = currentInvDb.find(r => r.warehouse === fromWh && String(r.itemId) === String(itemId));
            stockInput.value = rec ? rec.qty.toLocaleString() : '0';
        }

        function executeWarehouseTransfer() {
            const fromWh = document.getElementById('tr-from-wh').value;
            const toWh = document.getElementById('tr-to-wh').value;
            const itemId = document.getElementById('tr-item-select').value;
            const qty = parseFloat(document.getElementById('tr-qty').value);
            const date = document.getElementById('tr-date').value || new Date().toISOString().split('T')[0];
            const notes = document.getElementById('tr-notes').value || 'تحويل مخزني';

            if (!fromWh || !toWh) return alert('الرجاء اختيار مستودع المصدر ومستودع المستلم!');
            if (fromWh === toWh) return alert('لا يمكن التحويل لنفس المستودع! يرجى اختيار مستودعين مختلفين.');
            if (!itemId) return alert('الرجاء اختيار الصنف المراد تحويله!');
            if (isNaN(qty) || qty <= 0) return alert('الرجاء إدخال كمية تحويل صحيحة أكبر من صفر!');

            const srcRec = currentInvDb.find(r => r.warehouse === fromWh && String(r.itemId) === String(itemId));
            const availableStock = srcRec ? srcRec.qty : 0;
            if (qty > availableStock) {
                return alert(`⚠️ الكمية المطلوبة (${qty}) تتجاوز الرصيد المتاح بالمستودع (${availableStock})!`);
            }

            const itemObj = itemsDb.find(i => String(i.id) === String(itemId)) || { name: 'صنف', code: '-' };
            const transferId = 'TRF-' + Date.now().toString().slice(-6);

            const transfers = getStorageData('erp_warehouse_transfers_v1') || [];
            const newTransfer = {
                id: transferId,
                fromWarehouse: fromWh,
                toWarehouse: toWh,
                fromName: fromWh,
                toName: toWh,
                date: date,
                reference: transferId,
                notes: notes,
                items: [{
                    itemId: itemId,
                    itemCode: itemObj.code || '-',
                    itemName: itemObj.name,
                    quantity: qty,
                    costPrice: parseFloat(itemObj.costPrice || itemObj.cost || 0)
                }],
                totalItems: 1,
                totalQty: qty,
                createdAt: new Date().toISOString(),
                status: 'completed'
            };

            transfers.unshift(newTransfer);
            setStorageData('erp_warehouse_transfers_v1', transfers);

            alert(`✅ تم تنفيذ التحويل المخزني بنجاح!\n` +
                  `رقم السند: ${transferId}\n` +
                  `الصنف: ${itemObj.name}\n` +
                  `الكمية: ${qty} وحدة\n` +
                  `من: ${fromWh} ⬅️ إلى: ${toWh}`);

            // Reset form & update snapshots
            document.getElementById('tr-qty').value = '';
            document.getElementById('tr-notes').value = '';
            buildInventorySnapshot();
            onTransferFromWhChange();
            loadTransferHistory();
        }

        function loadTransferHistory() {
            const container = document.getElementById('tr-history-list');
            if (!container) return;
            const transfers = getStorageData('erp_warehouse_transfers_v1') || [];
            document.getElementById('tr-count-badge').innerText = `${transfers.length} عملية`;

            if (transfers.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد عمليات تحويل مسجلة</div>';
                return;
            }

            let html = '';
            transfers.slice(0, 15).forEach(t => {
                const itemName = t.items && t.items[0] ? t.items[0].itemName : 'أصناف متعددة';
                html += `
                <div style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 800; color: #fff; font-size: 0.88rem;">${itemName} (${t.totalQty} وحدة)</div>
                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                            من: <span style="color:#fdba74;">${t.fromWarehouse}</span> ⬅️ إلى: <span style="color:#34d399;">${t.toWarehouse}</span>
                        </div>
                    </div>
                    <div style="text-align: left;">
                        <span style="font-size: 0.72rem; color: #38bdf8; font-weight: 800; font-family: monospace; display: block;">${t.id || t.reference}</span>
                        <span style="font-size: 0.7rem; color: #64748b;">${t.date}</span>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }

        // ══════════════════════════════════════════════════════════════════════
        // ADJUSTMENT HISTORY MODULE
        // ══════════════════════════════════════════════════════════════════════
        function loadAdjustmentHistory() {
            const tbody = document.getElementById('adj-history-tbody');
            const adjustments = getStorageData('erp_inventory_adjustments_v1') || [];

            if (adjustments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:30px; color:var(--text-secondary);">لا توجد تسويات مسجلة حتى الآن</td></tr>';
                return;
            }

            let html = '';
            adjustments.forEach((adj, idx) => {
                const net = adj.netValue || 0;
                html += `<tr>
                    <td style="text-align:center; color:#64748b; font-weight:700;">${idx + 1}</td>
                    <td style="font-family:monospace; font-weight:800; color:#f97316;">${adj.id}</td>
                    <td style="font-weight:700;">🏢 ${adj.warehouse}</td>
                    <td style="font-family:monospace; color:#cbd5e1;">${adj.date}</td>
                    <td style="text-align:center; font-weight:800; color:#38bdf8;">${adj.itemsCount} صنف</td>
                    <td style="text-align:center; color:#ef4444; font-weight:800; font-family:monospace;">${adj.totalShortage || 0}</td>
                    <td style="text-align:center; color:#10b981; font-weight:800; font-family:monospace;">+${adj.totalSurplus || 0}</td>
                    <td style="text-align:center; font-weight:900; font-family:monospace; color:${net >= 0 ? '#10b981' : '#ef4444'}">
                        ${net > 0 ? '+' : ''}${net.toLocaleString(undefined, {maximumFractionDigits:2})} SDG
                    </td>
                    <td style="font-size:0.8rem; color:#94a3b8;">معتمد ومرحل</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function clearAdjustmentHistory() {
            if (confirm('هل أنت متأكد من رغبتك في مسح سجل التسويات؟')) {
                setStorageData('erp_inventory_adjustments_v1', []);
                loadAdjustmentHistory();
            }
        }
    </script>
</body>
</html>