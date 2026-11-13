<?php
$page_title = 'منظومة اعتمادية وموثوقية الموردين وثبات الأسعار والالتزام التوريدي 360°';
$page_css = 'dashboard.css';
$LoadChartJS = true;

// ============================================================================
// محرك البيانات الهجين (MySQL + LocalStorage) لاعتمادية وموثوقية الموردين 360°
// ============================================================================
$dbSuppliers = [];
$dbPurchaseInvoices = [];
$dbPurchaseReturns = [];
$dbChecks = [];

try {
    require_once __DIR__ . '/db_bridge.php';
    $pdo = getDBConnection();
    if (isset($pdo)) {
        // 1. الموردون
        try {
            $stmt = $pdo->prepare("SELECT id, data FROM suppliers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmt->execute([$GLOBAL_TENANT_ID]);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '', true);
                if (is_array($d)) {
                    $d['_db_id'] = $r['id'];
                    $dbSuppliers[] = $d;
                }
            }
        } catch (Exception $e) {}

        // 2. فواتير المشتريات
        try {
            $stmt = $pdo->prepare("SELECT id, supplier_id_val, date_val, data FROM purchase_invoices WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmt->execute([$GLOBAL_TENANT_ID]);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '', true);
                if (!is_array($d)) $d = [];
                if (empty($d['date']) && !empty($r['date_val'])) $d['date'] = $r['date_val'];
                if (empty($d['supplierId']) && !empty($r['supplier_id_val'])) $d['supplierId'] = $r['supplier_id_val'];
                $dbPurchaseInvoices[] = $d;
            }
        } catch (Exception $e) {}

        // 3. الشيكات الصادرة للموردين
        try {
            $stmt = $pdo->prepare("SELECT id, data FROM `checks` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmt->execute([$GLOBAL_TENANT_ID]);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '', true);
                if (is_array($d)) $dbChecks[] = $d;
            }
        } catch (Exception $e) {}
    }
} catch (Exception $e) {}

include 'includes/header.php';
if (file_exists(__DIR__ . '/includes/sidebar.php')) include __DIR__ . '/includes/sidebar.php';
?>
<link rel="stylesheet" href="css/unified-design-tokens.css">

<style>
    .sr-master-wrap {
        padding: 18px 22px 50px 22px;
        max-width: 1680px;
        margin: 0 auto;
        direction: rtl;
        font-family: 'Tajawal', 'Cairo', sans-serif;
    }

    /* Hero Banner */
    .sr-hero {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92));
        border: 1.5px solid rgba(168, 85, 247, 0.38);
        border-right: 6px solid #a855f7;
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 14px 36px rgba(0, 0, 0, 0.35);
    }

    .sr-hero-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .sr-hero-ico {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(56, 189, 248, 0.22));
        border: 1.5px solid rgba(168, 85, 247, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        box-shadow: 0 0 22px rgba(168, 85, 247, 0.28);
    }

    .sr-hero-text h1 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 900;
        color: #f8fafc;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .sr-hero-text p {
        margin: 5px 0 0 0;
        font-size: 0.86rem;
        color: #94a3b8;
    }

    .sr-actions-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .sr-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 14px;
        border-radius: 12px;
        font-family: 'Tajawal', sans-serif;
        font-size: 0.83rem;
        font-weight: 800;
        cursor: pointer;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(30, 41, 59, 0.88);
        color: #f8fafc;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .sr-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
    }

    .sr-btn-purple { background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(126, 34, 206, 0.22)); border-color: rgba(168, 85, 247, 0.5); color: #c084fc; }
    .sr-btn-emerald { background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.22)); border-color: rgba(16, 185, 129, 0.5); color: #34d399; }
    .sr-btn-sky { background: linear-gradient(135deg, rgba(56, 189, 248, 0.25), rgba(37, 99, 235, 0.22)); border-color: rgba(56, 189, 248, 0.5); color: #38bdf8; }
    .sr-btn-amber { background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.22)); border-color: rgba(245, 158, 11, 0.5); color: #fbbf24; }
    .sr-btn-rose { background: linear-gradient(135deg, rgba(244, 63, 94, 0.25), rgba(225, 29, 72, 0.22)); border-color: rgba(244, 63, 94, 0.5); color: #fda4af; }

    /* 6 KPI Cards */
    .sr-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .sr-kpi-card {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 16px;
        padding: 15px 18px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .sr-kpi-card:hover {
        transform: translateY(-3px);
        border-color: rgba(168, 85, 247, 0.45);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
    }

    .sr-kpi-lbl { font-size: 0.78rem; font-weight: 800; color: #94a3b8; }
    .sr-kpi-val { font-size: 1.5rem; font-weight: 900; color: #f8fafc; margin-top: 4px; }
    .sr-kpi-sub { font-size: 0.72rem; font-weight: 700; margin-top: 5px; }

    /* 10 AI Engines Ribbon */
    .sr-engines-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 8px;
        margin-bottom: 16px;
    }

    .sr-engine-chip {
        background: rgba(15, 23, 42, 0.78);
        border: 1px solid rgba(168, 85, 247, 0.22);
        border-radius: 12px;
        padding: 8px 10px;
        font-size: 0.75rem;
        font-weight: 800;
        color: #e2e8f0;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .sr-engine-chip:hover {
        border-color: #a855f7;
        background: rgba(168, 85, 247, 0.14);
        transform: translateY(-2px);
    }

    /* 4 Unified Tabs */
    .sr-tabs-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        background: rgba(15, 23, 42, 0.72);
        padding: 8px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .sr-tab-btn {
        flex: 1;
        min-width: 215px;
        padding: 11px 14px;
        border-radius: 12px;
        border: 1px solid transparent;
        background: transparent;
        color: #94a3b8;
        font-family: 'Tajawal', sans-serif;
        font-size: 0.88rem;
        font-weight: 800;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .sr-tab-btn.active {
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(56, 189, 248, 0.18));
        color: #fff;
        border-color: rgba(168, 85, 247, 0.5);
        box-shadow: 0 4px 15px rgba(168, 85, 247, 0.2);
    }

    .sr-tab-panel { display: none; }
    .sr-tab-panel.active { display: block; }

    /* Filter Bar */
    .sr-filter-bar {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 16px;
        display: grid;
        grid-template-columns: 2fr repeat(4, 1fr) auto;
        gap: 12px;
        align-items: end;
    }

    @media (max-width: 1200px) {
        .sr-filter-bar { grid-template-columns: 1fr 1fr; }
    }

    .sr-field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 800;
        color: #94a3b8;
        margin-bottom: 5px;
    }

    .sr-input, .sr-select {
        width: 100%;
        padding: 8px 12px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(15, 23, 42, 0.92);
        color: #f8fafc;
        font-family: 'Tajawal', sans-serif;
        font-size: 0.84rem;
        font-weight: 700;
        box-sizing: border-box;
    }

    /* Charts Row */
    .sr-charts-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 1024px) {
        .sr-charts-grid { grid-template-columns: 1fr; }
    }

    .sr-card {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        padding: 16px 18px;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.25);
    }

    /* Table */
    .sr-table-wrap {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .sr-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    .sr-table th {
        background: rgba(15, 23, 42, 0.96);
        color: #c084fc;
        padding: 12px 13px;
        font-weight: 900;
        font-size: 0.8rem;
        border-bottom: 1.5px solid rgba(168, 85, 247, 0.28);
        white-space: nowrap;
    }

    .sr-table td {
        padding: 11px 13px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        color: #e2e8f0;
        font-size: 0.84rem;
        vertical-align: middle;
    }

    .sr-table tbody tr:hover {
        background: rgba(168, 85, 247, 0.07);
    }

    .sr-badge {
        padding: 3px 9px;
        border-radius: 8px;
        font-size: 0.74rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .sr-badge-emerald { background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); }
    .sr-badge-amber { background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); }
    .sr-badge-rose { background: rgba(244, 63, 94, 0.18); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.4); }
    .sr-badge-purple { background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.4); }
    .sr-badge-sky { background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); }

    .sr-prog-bg {
        width: 100%;
        height: 7px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 4px;
    }

    .sr-prog-fill {
        height: 100%;
        border-radius: 999px;
    }

    /* Light Theme Compatibility */
    html[data-theme="warm_cream"] .sr-hero,
    html[data-theme="ultra_white"] .sr-hero,
    html[data-theme="exec_gray"] .sr-hero,
    html[data-theme="warm_cream"] .sr-kpi-card,
    html[data-theme="ultra_white"] .sr-kpi-card,
    html[data-theme="exec_gray"] .sr-kpi-card,
    html[data-theme="warm_cream"] .sr-engine-chip,
    html[data-theme="ultra_white"] .sr-engine-chip,
    html[data-theme="exec_gray"] .sr-engine-chip,
    html[data-theme="warm_cream"] .sr-filter-bar,
    html[data-theme="ultra_white"] .sr-filter-bar,
    html[data-theme="exec_gray"] .sr-filter-bar,
    html[data-theme="warm_cream"] .sr-card,
    html[data-theme="ultra_white"] .sr-card,
    html[data-theme="exec_gray"] .sr-card,
    html[data-theme="warm_cream"] .sr-table-wrap,
    html[data-theme="ultra_white"] .sr-table-wrap,
    html[data-theme="exec_gray"] .sr-table-wrap {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06) !important;
    }

    html[data-theme="warm_cream"] .sr-hero-text h1,
    html[data-theme="ultra_white"] .sr-hero-text h1,
    html[data-theme="exec_gray"] .sr-hero-text h1,
    html[data-theme="warm_cream"] .sr-kpi-val,
    html[data-theme="ultra_white"] .sr-kpi-val,
    html[data-theme="exec_gray"] .sr-kpi-val,
    html[data-theme="warm_cream"] .sr-table td,
    html[data-theme="ultra_white"] .sr-table td,
    html[data-theme="exec_gray"] .sr-table td {
        color: #0f172a !important;
    }

    html[data-theme="warm_cream"] .sr-input,
    html[data-theme="ultra_white"] .sr-input,
    html[data-theme="exec_gray"] .sr-input,
    html[data-theme="warm_cream"] .sr-select,
    html[data-theme="ultra_white"] .sr-select,
    html[data-theme="exec_gray"] .sr-select {
        background: #f8fafc !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }

    @media print {
        .sidebar, .top-header, .sr-actions-bar, .sr-engines-bar, .sr-tabs-bar, .sr-filter-bar, .no-print {
            display: none !important;
        }
        .sr-master-wrap { padding: 0 !important; max-width: 100% !important; }
    }
</style>

<main class="main-content sr-master-wrap" id="main-content">

    <!-- Hero Banner -->
    <div class="sr-hero">
        <div class="sr-hero-title">
            <div class="sr-hero-ico">🤝</div>
            <div class="sr-hero-text">
                <h1>
                    منظومة اعتمادية وموثوقية الموردين وثبات الأسعار والالتزام التوريدي 360°
                    <span class="sr-badge sr-badge-purple" id="sr-status-pill">🤖 10 محركات ذكاء توريدي نشطة</span>
                </h1>
                <p>التحليل العلمي الموحد لاعتمادية الموردين، تذبذب الأسعار، جودة التوريد، أعمار مديونية الموردين، وأفضل مورد لكل صنف بالذكاء الاصطناعي</p>
            </div>
        </div>

        <div class="sr-actions-bar">
            <div style="display:inline-flex; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.15); border-radius:11px; padding:3px;">
                <button type="button" class="sr-btn" id="sr-cur-SDG" onclick="setSupplierCurrency('SDG')" style="padding:5px 10px; border:none; background:#a855f7; color:#fff;">SDG</button>
                <button type="button" class="sr-btn" id="sr-cur-USD" onclick="setSupplierCurrency('USD')" style="padding:5px 10px; border:none; background:transparent;">USD ($)</button>
            </div>

            <a href="purchase.php" class="sr-btn sr-btn-amber" title="إصدار فاتورة مشتريات جديدة">
                🛍️ فاتورة مشتريات
            </a>
            <a href="purchase-receipt.php" class="sr-btn sr-btn-sky" title="بوابة استلام شحنات المشتريات">
                🚛 استلام بضاعة
            </a>
            <a href="aging-report.php?tab=ap" class="sr-btn sr-btn-rose" title="أعمار ديون الموردين والالتزامات">
                ⏳ أعمار ديون الموردين
            </a>
            <a href="ai_goods_advisor.php" class="sr-btn sr-btn-purple" title="مستشار الذكاء الاصطناعي للبضائع والمخازن">
                🤖 مستشار البضائع AI
            </a>
            <button onclick="exportSuppliersExcel()" class="sr-btn sr-btn-emerald" title="تصدير تقرير اعتمادية الموردين إلى إكسل">
                📥 تصدير Excel
            </button>
            <button onclick="window.print()" class="sr-btn" title="طباعة التقرير الرسمي">
                🖨️ طباعة
            </button>
        </div>
    </div>

    <!-- 10 AI Procurement & Reliability Engines Ribbon -->
    <div class="sr-engines-bar no-print">
        <div class="sr-engine-chip" onclick="switchSRTab('reliability')"><span>1️⃣</span> مؤشر الموثوقية المركب</div>
        <div class="sr-engine-chip" onclick="switchSRTab('prices')"><span>2️⃣</span> رادار ثبات وانحراف الأسعار</div>
        <div class="sr-engine-chip" onclick="switchSRTab('reliability')"><span>3️⃣</span> كاشف معدل مرتجعات التوريد</div>
        <div class="sr-engine-chip" onclick="window.location.href='purchase-receipt.php'"><span>4️⃣</span> دقة الاستلام والمطابقة</div>
        <div class="sr-engine-chip" onclick="window.location.href='aging-report.php?tab=ap'"><span>5️⃣</span> المديونية والالتزام المالي</div>
        <div class="sr-engine-chip" onclick="switchSRTab('items')"><span>6️⃣</span> أفضل مورد لكل صنف (AI)</div>
        <div class="sr-engine-chip" onclick="switchSRTab('ai')"><span>7️⃣</span> محاكي التفاوض والخصم المكتسب</div>
        <div class="sr-engine-chip" onclick="switchSRTab('ai')"><span>8️⃣</span> كاشف الاحتكار وتركز التوريد</div>
        <div class="sr-engine-chip" onclick="switchSRTab('reliability')"><span>9️⃣</span> مولد طلبات الأسعار واتساب</div>
        <div class="sr-engine-chip" onclick="window.location.href='price_variance.php'"><span>🔟</span> رقابة هوامش ربح الأصناف</div>
    </div>

    <!-- 6 Executive KPI Cards -->
    <div class="sr-kpi-grid">
        <div class="sr-kpi-card" style="border-bottom:3px solid #a855f7;" onclick="switchSRTab('reliability')">
            <div class="sr-kpi-lbl">🏢 إجمالي الموردين المعتمدين</div>
            <div class="sr-kpi-val" id="sr-kpi-count" style="color:#c084fc;">0</div>
            <div class="sr-kpi-sub" id="sr-kpi-top-tier" style="color:#34d399;">0 مورد فئة ممتازة (A+)</div>
        </div>

        <div class="sr-kpi-card" style="border-bottom:3px solid #38bdf8;" onclick="switchSRTab('reliability')">
            <div class="sr-kpi-lbl">💰 إجمالي حجم التوريد الصافي</div>
            <div class="sr-kpi-val" id="sr-kpi-volume" style="color:#38bdf8;">0</div>
            <div class="sr-kpi-sub" id="sr-kpi-orders" style="color:#94a3b8;">0 طلبية وفاتورة شراء</div>
        </div>

        <div class="sr-kpi-card" style="border-bottom:3px solid #f59e0b;" onclick="window.location.href='aging-report.php?tab=ap'">
            <div class="sr-kpi-lbl">⚖️ إجمالي المستحق للموردين (علينا)</div>
            <div class="sr-kpi-val" id="sr-kpi-payable" style="color:#fbbf24;">0</div>
            <div class="sr-kpi-sub" id="sr-kpi-paid" style="color:#34d399;">المسدد: 0</div>
        </div>

        <div class="sr-kpi-card" style="border-bottom:3px solid #10b981;" onclick="switchSRTab('reliability')">
            <div class="sr-kpi-lbl">⭐ متوسط مؤشر الموثوقية العام</div>
            <div class="sr-kpi-val" id="sr-kpi-avg-rel" style="color:#34d399;">0%</div>
            <div class="sr-kpi-sub" style="color:#34d399;">التزام التوريد وثبات الأسعار</div>
        </div>

        <div class="sr-kpi-card" style="border-bottom:3px solid #f43f5e;" onclick="switchSRTab('prices')">
            <div class="sr-kpi-lbl">📉 معدل تذبذب الأسعار والمرتجعات</div>
            <div class="sr-kpi-val" id="sr-kpi-variance" style="color:#fda4af;">0%</div>
            <div class="sr-kpi-sub" id="sr-kpi-returns" style="color:#fda4af;">مرتجعات مشتريات: 0</div>
        </div>

        <div class="sr-kpi-card" style="border-bottom:3px solid #22d3ee;" onclick="switchSRTab('ai')">
            <div class="sr-kpi-lbl">💡 وفر التفاوض المتوقع بالذكاء الاصطناعي</div>
            <div class="sr-kpi-val" id="sr-kpi-savings" style="color:#22d3ee;">0</div>
            <div class="sr-kpi-sub" style="color:#22d3ee;">عند تطبيق خصم العقود 3.5%</div>
        </div>
    </div>

    <!-- 4 Unified Tabs -->
    <div class="sr-tabs-bar">
        <button class="sr-tab-btn active" id="sr-tab-btn-reliability" onclick="switchSRTab('reliability')">
            🏆 مصفوفة اعتمادية وموثوقية الموردين والمديونية (Supplier Reliability 360°)
        </button>
        <button class="sr-tab-btn" id="sr-tab-btn-prices" onclick="switchSRTab('prices')">
            📊 تحليل ثبات وتذبذب الأسعار لكل مورد (Price Stability & Variance)
        </button>
        <button class="sr-tab-btn" id="sr-tab-btn-items" onclick="switchSRTab('items')">
            🥇 مقارن أفضل مورد لكل صنف بالذكاء الاصطناعي (Best Supplier per Item)
        </button>
        <button class="sr-tab-btn" id="sr-tab-btn-ai" onclick="switchSRTab('ai')">
            🤖 مستشار التفاوض الذكي وكاشف مخاطر تركز التوريد (AI Procurement Advisor)
        </button>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 1: SUPPLIER RELIABILITY & PAYABLES MATRIX              -->
    <!-- ========================================================== -->
    <div class="sr-tab-panel active" id="sr-panel-reliability">

        <div class="sr-charts-grid">
            <div class="sr-card">
                <h3 style="margin:0 0 10px 0; font-size:0.95rem; font-weight:900; color:#f8fafc;">📊 مقارنة حجم التوريد مقابل المستحق للموردين</h3>
                <div style="height:220px; position:relative;">
                    <canvas id="srVolumeChart"></canvas>
                </div>
            </div>
            <div class="sr-card">
                <h3 style="margin:0 0 10px 0; font-size:0.95rem; font-weight:900; color:#f8fafc;">🎯 توزيع الموردين حسب درجة الاعتمادية وثبات الأسعار</h3>
                <div style="height:220px; position:relative;">
                    <canvas id="srTierChart"></canvas>
                </div>
            </div>
        </div>

        <div class="sr-filter-bar">
            <div class="sr-field">
                <label>🔍 بحث باسم المورد أو رقم الهاتف:</label>
                <input type="text" id="sr-search" class="sr-input" placeholder="ابحث عن مورد..." oninput="renderReliabilityTable()">
            </div>

            <div class="sr-field">
                <label>🏆 تصنيف الاعتمادية:</label>
                <select id="sr-tier-filter" class="sr-select" onchange="renderReliabilityTable()">
                    <option value="ALL">جميع الموردين</option>
                    <option value="STABLE">🟢 مورد استراتيجي مستقر (A+)</option>
                    <option value="MODERATE">🟡 تقلب متوسط (B)</option>
                    <option value="VOLATILE">🔴 تقلب أسعار مرتفع (C)</option>
                    <option value="HAS_DEBT">⚖️ له مديونية مستحقة علينا</option>
                </select>
            </div>

            <div class="sr-field">
                <label>📅 من تاريخ:</label>
                <input type="date" id="sr-date-from" class="sr-input" onchange="recalculateSuppliers()">
            </div>

            <div class="sr-field">
                <label>📅 إلى تاريخ:</label>
                <input type="date" id="sr-date-to" class="sr-input" onchange="recalculateSuppliers()">
            </div>

            <div class="sr-field">
                <label>📊 الترتيب حسب:</label>
                <select id="sr-sort" class="sr-select" onchange="renderReliabilityTable()">
                    <option value="volume_desc">الأعلى حجم توريد</option>
                    <option value="score_desc">الأعلى درجة موثوقية %</option>
                    <option value="payable_desc">الأعلى مديونية مستحقة له</option>
                    <option value="variance_asc">الأكثر ثباتاً في الأسعار</option>
                </select>
            </div>

            <div class="sr-field">
                <button onclick="resetSRFilters()" class="sr-btn" style="height:38px;">إعادة ضبط ✖</button>
            </div>
        </div>

        <div class="sr-table-wrap">
            <div style="padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="font-weight:900; color:#c084fc; font-size:0.92rem;">
                    📋 سجل اعتمادية الموردين والموقف المالي التوريدي (<span id="sr-showing-count">0</span> مورد)
                </div>
                <div style="font-size:0.78rem; color:#94a3b8;">
                    مرتبط مباشرة مع فواتير الشراء • استلام البضاعة • مرتجعات المشتريات • سندات الصرف • كشف الحساب
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="sr-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المورد</th>
                            <th>عدد الطلبيات</th>
                            <th>إجمالي التوريد</th>
                            <th>المرتجعات</th>
                            <th>المسدد للمورد</th>
                            <th>المستحق له (علينا)</th>
                            <th>تذبذب الأسعار %</th>
                            <th>مؤشر الاعتمادية AI</th>
                            <th>مستوى الثبات</th>
                            <th class="no-print">إجراءات التوريد والسداد والربط</th>
                        </tr>
                    </thead>
                    <tbody id="sr-main-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 2: PRICE STABILITY & VARIANCE                          -->
    <!-- ========================================================== -->
    <div class="sr-tab-panel" id="sr-panel-prices">
        <div class="sr-table-wrap">
            <div style="padding:14px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="font-weight:900; color:#38bdf8; font-size:0.95rem;">
                    📊 تحليل ثبات وانحراف أسعار التوريد ومقارنة العقود (مدمج مع رقابة انحراف السعر)
                </div>
                <a href="price_variance.php" class="sr-btn sr-btn-sky" style="padding:6px 12px; font-size:0.78rem;">🔍 فتح رادار انحراف أسعار الأصناف التفصيلي ↗</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="sr-table">
                    <thead>
                        <tr>
                            <th>المورد</th>
                            <th>عدد الأصناف الموردة</th>
                            <th>متوسط قيمة الطلبية</th>
                            <th>معامل تذبذب السعر %</th>
                            <th>نسبة المرتجعات %</th>
                            <th>حصة المورد من مشترياتنا %</th>
                            <th>التوصية التعاقدية الذكية</th>
                        </tr>
                    </thead>
                    <tbody id="sr-prices-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 3: BEST SUPPLIER PER ITEM (أفضل مورد لكل صنف)          -->
    <!-- ========================================================== -->
    <div class="sr-tab-panel" id="sr-panel-items">
        <div class="sr-table-wrap">
            <div style="padding:14px 16px; border-bottom:1px solid rgba(255,255,255,0.08);">
                <div style="font-weight:900; color:#34d399; font-size:0.95rem;">
                    🥇 مرشد الشراء الذكي: مقارنة الموردين وتحديد أفضل مورد وأقل سعر لكل صنف (AI Best Source Finder)
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="sr-table">
                    <thead>
                        <tr>
                            <th>اسم الصنف / البضاعة</th>
                            <th>أفضل مورد موصى به (الأقل سعراً)</th>
                            <th>أقل سعر شراء مسجل</th>
                            <th>أعلى سعر شراء مسجل</th>
                            <th>فارق التوفير للوحدة</th>
                            <th>إجمالي الكمية المشتراة</th>
                            <th class="no-print">إصدار طلب شراء</th>
                        </tr>
                    </thead>
                    <tbody id="sr-items-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 4: AI NEGOTIATION & CONCENTRATION RISK                 -->
    <!-- ========================================================== -->
    <div class="sr-tab-panel" id="sr-panel-ai">
        <div class="sr-charts-grid" style="grid-template-columns: 1fr 1fr;">
            <div class="sr-card" style="border-top:3px solid #a855f7;">
                <h3 style="margin:0 0 12px 0; color:#c084fc; font-size:1.02rem;">🛡️ رادار تركز المشتريات ومخاطر الاحتكار التوريدي</h3>
                <div id="sr-concentration-box"></div>
            </div>

            <div class="sr-card" style="border-top:3px solid #10b981;">
                <h3 style="margin:0 0 12px 0; color:#34d399; font-size:1.02rem;">💰 محاكي التفاوض والخصم المكتسب بالذكاء الاصطناعي</h3>
                <div id="sr-negotiation-box"></div>
            </div>
        </div>
    </div>

</main>

<script src="js/localforage.min.js"></script>
<script src="js/permissions-manager.js"></script>
<script src="js/app.js"></script>

<script>
    const SERVER_SR_DATA = {
        suppliers: <?php echo json_encode($dbSuppliers, JSON_UNESCAPED_UNICODE); ?>,
        purchaseInvoices: <?php echo json_encode($dbPurchaseInvoices, JSON_UNESCAPED_UNICODE); ?>,
        checks: <?php echo json_encode($dbChecks, JSON_UNESCAPED_UNICODE); ?>
    };

    let SR_CURRENCY = 'SDG';
    const SR_EXCHANGE_RATE = Number(localStorage.getItem('erp_exchange_rate') || 600) || 600;
    let SUPPLIERS_LIST = [];
    let ITEM_BEST_SUPPLIERS = [];
    let srVolChart = null;
    let srTierChart = null;

    function fmtSR(val) {
        const num = Number(val || 0);
        const conv = SR_CURRENCY === 'USD' ? (num / SR_EXCHANGE_RATE) : num;
        return conv.toLocaleString('en-US', { maximumFractionDigits: SR_CURRENCY === 'USD' ? 2 : 0 });
    }

    function setSupplierCurrency(cur) {
        SR_CURRENCY = cur;
        document.getElementById('sr-cur-SDG').style.background = cur === 'SDG' ? '#a855f7' : 'transparent';
        document.getElementById('sr-cur-USD').style.background = cur === 'USD' ? '#a855f7' : 'transparent';
        renderAllSRViews();
    }

    function safeJSON(key) {
        try {
            const raw = localStorage.getItem(key);
            if (!raw) return [];
            const p = JSON.parse(raw);
            return Array.isArray(p) ? p : [];
        } catch (e) { return []; }
    }

    function mergeByKey(serverArr, localArr, idProp = 'id', nameProp = 'name') {
        const map = new Map();
        [...(serverArr || []), ...(localArr || [])].forEach(item => {
            if (!item || item.deleted || item.is_deleted) return;
            const k = String(item[idProp] || item._db_id || item[nameProp] || Math.random()).trim().toLowerCase();
            if (!k) return;
            const ex = map.get(k) || {};
            map.set(k, { ...ex, ...item });
        });
        return Array.from(map.values());
    }

    function recalculateSuppliers() {
        const dFrom = document.getElementById('sr-date-from')?.value || '';
        const dTo = document.getElementById('sr-date-to')?.value || '';

        const suppliers = mergeByKey(SERVER_SR_DATA.suppliers, [...safeJSON('erp_suppliers_v1'), ...safeJSON('erp_partners_v1').filter(p => (p.type || '').toLowerCase().includes('sup') || p.type === 'مورد')], 'id', 'name');
        const purchaseInvoices = mergeByKey(SERVER_SR_DATA.purchaseInvoices, safeJSON('erp_purchase_invoices_v1'), 'invoiceNo', 'id');
        const singlePayments = safeJSON('erp_single_payments_v1');
        const multiPayments = safeJSON('erp_payments_v1');

        const supMap = new Map();
        const itemMap = new Map();

        const ensureSup = (id, name, phone = '', openBal = 0) => {
            const clean = String(name || 'مورد عام').trim();
            const key = clean.toLowerCase();
            if (!supMap.has(key)) {
                supMap.set(key, {
                    id: id || clean,
                    name: clean,
                    phone: phone || '-',
                    openingBalance: Math.abs(Number(openBal || 0)),
                    orders: 0,
                    volume: 0,
                    returns: 0,
                    paid: 0,
                    payable: 0,
                    itemPrices: [],
                    uniqueItems: new Set(),
                    variancePct: 0,
                    reliabilityScore: 90,
                    tier: 'STABLE'
                });
            } else {
                const s = supMap.get(key);
                if (phone && (s.phone === '-' || !s.phone)) s.phone = phone;
                if (Math.abs(openBal) > s.openingBalance) s.openingBalance = Math.abs(Number(openBal));
            }
            return supMap.get(key);
        };

        suppliers.forEach(s => {
            ensureSup(s.id || s._db_id, s.name, s.phone || s.mobile, s.currentBalance ?? s.balance ?? 0);
        });

        purchaseInvoices.forEach(inv => {
            if (inv.deleted || inv.is_deleted) return;
            const invDate = (inv.date || '').slice(0, 10);
            if (dFrom && invDate && invDate < dFrom) return;
            if (dTo && invDate && invDate > dTo) return;

            const supName = inv.supplierName || inv.vendorName || inv.supplier || 'مورد عام';
            const s = ensureSup(inv.supplierId || inv.supplier, supName, inv.supplierPhone);
            const total = parseFloat(inv.grandTotal ?? inv.netTotal ?? inv.total ?? inv.totalAmount ?? 0) || 0;
            const paid = parseFloat(inv.paidAmount ?? (inv.paymentMethod === 'cash' ? total : 0)) || 0;
            const isReturn = (inv.type === 'return' || inv.isReturn);

            if (isReturn) {
                s.returns += total;
            } else {
                s.orders++;
                s.volume += total;
                s.paid += paid;
            }

            if (Array.isArray(inv.items)) {
                inv.items.forEach(it => {
                    const iName = String(it.name || it.itemName || it.productName || '').trim();
                    const price = parseFloat(it.price ?? it.unitPrice ?? it.cost ?? 0) || 0;
                    const qty = parseFloat(it.qty ?? it.quantity ?? 1) || 1;
                    if (price > 0) {
                        s.itemPrices.push(price);
                        if (iName) {
                            s.uniqueItems.add(iName);
                            const ik = iName.toLowerCase();
                            if (!itemMap.has(ik)) {
                                itemMap.set(ik, { name: iName, minPrice: price, maxPrice: price, bestSupplier: s.name, totalQty: 0 });
                            }
                            const im = itemMap.get(ik);
                            im.totalQty += qty;
                            if (price < im.minPrice) {
                                im.minPrice = price;
                                im.bestSupplier = s.name;
                            }
                            if (price > im.maxPrice) {
                                im.maxPrice = price;
                            }
                        }
                    }
                });
            }
        });

        singlePayments.filter(p => p.voucherType !== 'receipt').forEach(p => {
            const nm = String(p.accountName || p.partyName || p.beneficiary || '').trim().toLowerCase();
            if (nm && supMap.has(nm)) {
                supMap.get(nm).paid += parseFloat(p.amount || p.totalAmount || 0) || 0;
            }
        });

        multiPayments.forEach(mp => {
            if (Array.isArray(mp.items)) {
                mp.items.forEach(it => {
                    const nm = String(it.accountName || it.partyName || '').trim().toLowerCase();
                    if (nm && supMap.has(nm)) {
                        supMap.get(nm).paid += parseFloat(it.amount || 0) || 0;
                    }
                });
            }
        });

        supMap.forEach(s => {
            if (s.volume === 0 && s.openingBalance > 0) {
                s.volume = s.openingBalance;
                if (s.orders === 0) s.orders = 1;
            }
            const netVol = Math.max(0, s.volume - s.returns);
            s.payable = Math.max(0, netVol - s.paid);

            // Compute normalized price coefficient of variation %
            if (s.itemPrices.length > 1) {
                const mean = s.itemPrices.reduce((a, b) => a + b, 0) / s.itemPrices.length;
                if (mean > 0) {
                    const sq = s.itemPrices.reduce((acc, p) => acc + Math.pow(p - mean, 2), 0) / (s.itemPrices.length - 1);
                    s.variancePct = Math.min(45, +(Math.sqrt(sq) / mean * 25).toFixed(1));
                }
            } else {
                s.variancePct = 1.5;
            }

            const returnRate = s.volume > 0 ? (s.returns / s.volume) * 100 : 0;
            s.returnRate = +returnRate.toFixed(1);

            // AI Composite Reliability Score (0 - 100%)
            const baseScore = 96 - (s.variancePct * 0.9) - (s.returnRate * 1.5) + Math.min(4, s.orders * 0.4);
            s.reliabilityScore = Math.max(40, Math.min(99, Math.round(baseScore)));

            if (s.variancePct >= 18 || s.reliabilityScore < 70) s.tier = 'VOLATILE';
            else if (s.variancePct >= 8 || s.reliabilityScore < 85) s.tier = 'MODERATE';
            else s.tier = 'STABLE';
        });

        SUPPLIERS_LIST = Array.from(supMap.values()).filter(s => s.volume > 0 || s.payable > 0 || s.name !== 'مورد عام');
        ITEM_BEST_SUPPLIERS = Array.from(itemMap.values()).sort((a, b) => b.totalQty - a.totalQty);

        renderAllSRViews();
    }

    function renderAllSRViews() {
        updateSRKPIs();
        renderReliabilityTable();
        renderPriceStabilityTab();
        renderBestItemsTab();
        renderAINegotiationTab();
        renderSRCharts();
    }

    function updateSRKPIs() {
        const count = SUPPLIERS_LIST.length;
        const topTier = SUPPLIERS_LIST.filter(s => s.tier === 'STABLE').length;
        const totVol = SUPPLIERS_LIST.reduce((s, x) => s + x.volume, 0);
        const totOrders = SUPPLIERS_LIST.reduce((s, x) => s + x.orders, 0);
        const totPayable = SUPPLIERS_LIST.reduce((s, x) => s + x.payable, 0);
        const totPaid = SUPPLIERS_LIST.reduce((s, x) => s + x.paid, 0);
        const totReturns = SUPPLIERS_LIST.reduce((s, x) => s + x.returns, 0);
        const avgRel = count > 0 ? Math.round(SUPPLIERS_LIST.reduce((s, x) => s + x.reliabilityScore, 0) / count) : 95;
        const avgVar = count > 0 ? +(SUPPLIERS_LIST.reduce((s, x) => s + x.variancePct, 0) / count).toFixed(1) : 0;
        const savings = totVol * 0.035;

        document.getElementById('sr-kpi-count').textContent = `${count} مورد`;
        document.getElementById('sr-kpi-top-tier').textContent = `${topTier} مورد فئة استراتيجية (A+)`;
        document.getElementById('sr-kpi-volume').textContent = `${fmtSR(totVol)} ${SR_CURRENCY}`;
        document.getElementById('sr-kpi-orders').textContent = `${totOrders} طلبية وفاتورة توريد`;
        document.getElementById('sr-kpi-payable').textContent = `${fmtSR(totPayable)} ${SR_CURRENCY}`;
        document.getElementById('sr-kpi-paid').textContent = `المسدد: ${fmtSR(totPaid)} ${SR_CURRENCY}`;
        document.getElementById('sr-kpi-avg-rel').textContent = `${avgRel}%`;
        document.getElementById('sr-kpi-variance').textContent = `${avgVar}%`;
        document.getElementById('sr-kpi-returns').textContent = `مرتجعات: ${fmtSR(totReturns)} ${SR_CURRENCY}`;
        document.getElementById('sr-kpi-savings').textContent = `${fmtSR(savings)} ${SR_CURRENCY}`;
    }

    function getFilteredSuppliers() {
        const q = (document.getElementById('sr-search')?.value || '').trim().toLowerCase();
        const tier = document.getElementById('sr-tier-filter')?.value || 'ALL';
        const sort = document.getElementById('sr-sort')?.value || 'volume_desc';

        let list = SUPPLIERS_LIST.filter(s => {
            if (q && !(`${s.name} ${s.phone}`).toLowerCase().includes(q)) return false;
            if (tier === 'STABLE' && s.tier !== 'STABLE') return false;
            if (tier === 'MODERATE' && s.tier !== 'MODERATE') return false;
            if (tier === 'VOLATILE' && s.tier !== 'VOLATILE') return false;
            if (tier === 'HAS_DEBT' && s.payable <= 0) return false;
            return true;
        });

        list.sort((a, b) => {
            if (sort === 'score_desc') return b.reliabilityScore - a.reliabilityScore;
            if (sort === 'payable_desc') return b.payable - a.payable;
            if (sort === 'variance_asc') return a.variancePct - b.variancePct;
            return b.volume - a.volume;
        });

        return list;
    }

    function renderReliabilityTable() {
        const tbody = document.getElementById('sr-main-tbody');
        const list = getFilteredSuppliers();
        document.getElementById('sr-showing-count').textContent = list.length.toLocaleString();

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding:36px; color:#94a3b8;">لا توجد سجلات موردين مطابقة للفلتر الحالي.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map((s, idx) => {
            const safeName = s.name.replace(/'/g, "\\'");
            const safePhone = String(s.phone || '').replace(/'/g, "\\'");
            const scoreColor = s.reliabilityScore >= 85 ? '#10b981' : (s.reliabilityScore >= 70 ? '#f59e0b' : '#ef4444');

            let tierBadge = '<span class="sr-badge sr-badge-emerald">✓ ثبات عالٍ (A+)</span>';
            if (s.tier === 'VOLATILE') tierBadge = '<span class="sr-badge sr-badge-rose">🚨 تقلب عالٍ (مراجعة العقد)</span>';
            else if (s.tier === 'MODERATE') tierBadge = '<span class="sr-badge sr-badge-amber">⚠️ تقلب متوسط (B)</span>';

            return `
                <tr>
                    <td style="color:#64748b; font-weight:800;">${idx + 1}</td>
                    <td>
                        <div style="font-weight:900; color:#c084fc;">🏢 ${s.name}</div>
                        <div style="font-size:0.73rem; color:#94a3b8;">📞 ${s.phone}</div>
                    </td>
                    <td style="font-weight:800;">${s.orders} طلبية</td>
                    <td style="font-weight:900; color:#f8fafc;">${fmtSR(s.volume)} <small>${SR_CURRENCY}</small></td>
                    <td style="color:#fda4af;">${s.returns > 0 ? fmtSR(s.returns) : '—'}</td>
                    <td style="color:#34d399; font-weight:800;">${fmtSR(s.paid)}</td>
                    <td style="font-weight:900; color:${s.payable > 0 ? '#fbbf24' : '#34d399'};">${fmtSR(s.payable)}</td>
                    <td style="color:#38bdf8; font-weight:800;">±${s.variancePct}%</td>
                    <td style="min-width:130px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:900; color:${scoreColor};">
                            <span>الاعتمادية</span>
                            <span>${s.reliabilityScore}%</span>
                        </div>
                        <div class="sr-prog-bg"><div class="sr-prog-fill" style="width:${s.reliabilityScore}%; background:${scoreColor};"></div></div>
                    </td>
                    <td>${tierBadge}</td>
                    <td class="no-print">
                        <div style="display:flex; gap:5px; flex-wrap:wrap;">
                            <a href="purchase.php?supplier=${encodeURIComponent(s.name)}" class="sr-btn sr-btn-purple" style="padding:4px 8px; font-size:0.73rem;" title="إصدار فاتورة شراء من المورد">🛍️ شراء</a>
                            <a href="single-cash-payment.php?mode=payment&party=${encodeURIComponent(s.name)}&amount=${Math.round(s.payable)}" class="sr-btn sr-btn-amber" style="padding:4px 8px; font-size:0.73rem;" title="إصدار سند صرف للمورد">💸 صرف</a>
                            <a href="account-statement.php?id=${encodeURIComponent(s.id)}&name=${encodeURIComponent(s.name)}" class="sr-btn sr-btn-sky" style="padding:4px 8px; font-size:0.73rem;" title="كشف حساب المورد">📑 كشف</a>
                            <button onclick="sendSupplierWhatsAppRFQ('${safeName}','${safePhone}',${s.payable})" class="sr-btn sr-btn-emerald" style="padding:4px 8px; font-size:0.73rem;" title="طلب عرض سعر أو تواصل عبر الواتساب">📲 واتساب</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderPriceStabilityTab() {
        const tbody = document.getElementById('sr-prices-tbody');
        const totVol = SUPPLIERS_LIST.reduce((s, x) => s + x.volume, 0) || 1;

        if (SUPPLIERS_LIST.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">لا توجد بيانات متاحة.</td></tr>`;
            return;
        }

        tbody.innerHTML = SUPPLIERS_LIST.map(s => {
            const avgOrder = s.orders > 0 ? (s.volume / s.orders) : s.volume;
            const sharePct = Math.round((s.volume / totVol) * 100);
            const rec = s.tier === 'STABLE'
                ? '✅ تثبيت اتفاقية توريد سنوية بخصم كمية 3%'
                : (s.tier === 'MODERATE' ? '⚖️ ربط الشراء بعروض أسعار مسبقة لكل شحنة' : '🚨 البحث عن مورد بديل أو اشتراط سقف سعر ثابت');

            return `
                <tr>
                    <td style="font-weight:900; color:#c084fc;">🏢 ${s.name}</td>
                    <td>${s.uniqueItems.size || 1} صنف</td>
                    <td style="font-weight:800;">${fmtSR(avgOrder)} ${SR_CURRENCY}</td>
                    <td style="color:#38bdf8; font-weight:800;">±${s.variancePct}%</td>
                    <td style="color:${s.returnRate > 5 ? '#f87171' : '#34d399'}; font-weight:800;">${s.returnRate}%</td>
                    <td><span class="sr-badge sr-badge-purple">${sharePct}% من المشتريات</span></td>
                    <td style="font-size:0.82rem; color:#cbd5e1;">${rec}</td>
                </tr>
            `;
        }).join('');
    }

    function renderBestItemsTab() {
        const tbody = document.getElementById('sr-items-tbody');
        if (ITEM_BEST_SUPPLIERS.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:32px; color:#94a3b8;">سيتم عرض مقارنة أسعار الأصناف تلقائياً فور تسجيل فواتير مشتريات تفصيلية.</td></tr>`;
            return;
        }

        tbody.innerHTML = ITEM_BEST_SUPPLIERS.slice(0, 60).map(it => {
            const diff = Math.max(0, it.maxPrice - it.minPrice);
            return `
                <tr>
                    <td style="font-weight:900; color:#f8fafc;">📦 ${it.name}</td>
                    <td><span class="sr-badge sr-badge-emerald">🥇 ${it.bestSupplier}</span></td>
                    <td style="font-weight:900; color:#34d399;">${fmtSR(it.minPrice)} ${SR_CURRENCY}</td>
                    <td style="color:#fda4af;">${fmtSR(it.maxPrice)} ${SR_CURRENCY}</td>
                    <td style="font-weight:800; color:#38bdf8;">وفر ${fmtSR(diff)} / للوحدة</td>
                    <td>${it.totalQty.toLocaleString()}</td>
                    <td class="no-print">
                        <a href="purchase.php?supplier=${encodeURIComponent(it.bestSupplier)}" class="sr-btn sr-btn-emerald" style="padding:4px 9px; font-size:0.74rem;">🛒 طلب من الأرخص</a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderAINegotiationTab() {
        const totVol = SUPPLIERS_LIST.reduce((s, x) => s + x.volume, 0) || 1;
        const sorted = [...SUPPLIERS_LIST].sort((a, b) => b.volume - a.volume);
        const topSup = sorted[0];
        const topShare = topSup ? Math.round((topSup.volume / totVol) * 100) : 0;

        document.getElementById('sr-concentration-box').innerHTML = topSup ? `
            <div style="font-size:0.86rem; line-height:1.7; color:#cbd5e1;">
                • المورد الأول (<strong>${topSup.name}</strong>) يستحوذ على <strong style="color:${topShare > 45 ? '#fb7185' : '#34d399'};">${topShare}%</strong> من إجمالي مشتريات الشركة (${fmtSR(topSup.volume)} ${SR_CURRENCY}).<br>
                • ${topShare > 45 ? '⚠️ <strong>تنبيه تركز توريدي:</strong> الاعتماد على مورد واحد بأكثر من 45% يعرض المخزون لخطر انقطاع الإمداد؛ يُوصى بتوزيع 20% من الطلبيات على المورد الثاني.' : '✅ <strong>توزيع توريدي متوازن وآمن:</strong> لا يوجد احتكار خطير في سلسلة الإمداد.'}
            </div>
        ` : `<div style="color:#94a3b8;">لا توجد بيانات توريد كافية للتحليل.</div>`;

        document.getElementById('sr-negotiation-box').innerHTML = `
            <div style="font-size:0.86rem; line-height:1.7; color:#cbd5e1;">
                • عند التفاوض مع أعلى 3 موردين للحصول على <strong>خصم تعاقدي 3.5%</strong> مقابل انتظام السداد، ستحقق الشركة وفراً صافياً قدره <strong style="color:#34d399;">${fmtSR(totVol * 0.035)} ${SR_CURRENCY}</strong>.<br>
                • كما أن تمديد فترة الائتمان التوريدي بمقدار <strong>15 يوماً إضافية</strong> يوفر سيولة تشغيلية فورية في الخزينة بقيمة <strong style="color:#38bdf8;">${fmtSR(totVol * 0.12)} ${SR_CURRENCY}</strong>.
            </div>
        `;
    }

    function renderSRCharts() {
        if (typeof Chart === 'undefined') return;
        const top8 = [...SUPPLIERS_LIST].sort((a, b) => b.volume - a.volume).slice(0, 8);

        const ctx1 = document.getElementById('srVolumeChart')?.getContext('2d');
        if (ctx1) {
            if (srVolChart) srVolChart.destroy();
            srVolChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: top8.map(s => s.name),
                    datasets: [
                        {
                            label: `إجمالي التوريد (${SR_CURRENCY})`,
                            data: top8.map(s => SR_CURRENCY === 'USD' ? +(s.volume / SR_EXCHANGE_RATE).toFixed(2) : Math.round(s.volume)),
                            backgroundColor: 'rgba(168, 85, 247, 0.75)',
                            borderRadius: 6
                        },
                        {
                            label: `المستحق للمورد (${SR_CURRENCY})`,
                            data: top8.map(s => SR_CURRENCY === 'USD' ? +(s.payable / SR_EXCHANGE_RATE).toFixed(2) : Math.round(s.payable)),
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { color: '#cbd5e1', font: { family: 'Tajawal', weight: 'bold' } } }
                    }
                }
            });
        }

        const ctx2 = document.getElementById('srTierChart')?.getContext('2d');
        if (ctx2) {
            if (srTierChart) srTierChart.destroy();
            const cStable = SUPPLIERS_LIST.filter(s => s.tier === 'STABLE').length;
            const cMod = SUPPLIERS_LIST.filter(s => s.tier === 'MODERATE').length;
            const cVol = SUPPLIERS_LIST.filter(s => s.tier === 'VOLATILE').length;
            srTierChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['ثبات عالٍ (A+)', 'تقلب متوسط (B)', 'تقلب مرتفع (C)'],
                    datasets: [{
                        data: [cStable || 1, cMod, cVol],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 1,
                        borderColor: '#0f172a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { color: '#cbd5e1', font: { family: 'Tajawal' } } } }
                }
            });
        }
    }

    function sendSupplierWhatsAppRFQ(name, phone, payable) {
        const cleanPhone = String(phone || '').replace(/[^0-9]/g, '');
        const msg = `🤝 *تحية طيبة من إدارة المشتريات والتوريد*\n`
            + `━━━━━━━━━━━━━━━━━━━━\n`
            + `السادة / *${name}* المحترمين،\n`
            + `نود طلب عرض أسعار محدث للطلبيات الجديدة، وتأكيد مطابقة الرصيد الحالي المسجل لدينا بقيمة (*${fmtSR(payable)} ${SR_CURRENCY}*).\n`
            + `شاكرين تعاونكم والتزامكم الدائم.`;
        window.open(cleanPhone ? `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
    }

    function switchSRTab(tab) {
        ['reliability', 'prices', 'items', 'ai'].forEach(t => {
            document.getElementById('sr-tab-btn-' + t).classList.toggle('active', t === tab);
            document.getElementById('sr-panel-' + t).classList.toggle('active', t === tab);
        });
    }

    function resetSRFilters() {
        document.getElementById('sr-search').value = '';
        document.getElementById('sr-tier-filter').value = 'ALL';
        document.getElementById('sr-date-from').value = '';
        document.getElementById('sr-date-to').value = '';
        document.getElementById('sr-sort').value = 'volume_desc';
        recalculateSuppliers();
    }

    function exportSuppliersExcel() {
        const list = getFilteredSuppliers();
        let csv = `\uFEFFالمورد,الهاتف,عدد الطلبيات,إجمالي التوريد (${SR_CURRENCY}),المرتجعات,المسدد,المستحق للمورد,تذبذب الأسعار %,مؤشر الاعتمادية AI %\n`;
        list.forEach(s => {
            csv += `"${s.name}","${s.phone}","${s.orders}","${s.volume}","${s.returns}","${s.paid}","${s.payable}","${s.variancePct}%","${s.reliabilityScore}%"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `Elsajana_Supplier_Reliability_${SR_CURRENCY}_${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
    }

    document.addEventListener('DOMContentLoaded', () => {
        recalculateSuppliers();
    });

    document.addEventListener('DBReady', () => {
        recalculateSuppliers();
    });
</script>

<?php if (file_exists(__DIR__ . '/includes/footer.php')) include __DIR__ . '/includes/footer.php'; ?>
