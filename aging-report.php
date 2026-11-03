<?php
$page_title = 'منظومة أعمار الديون والحقوق وسقوف الائتمان والتنبؤ الذكي — Aging & Credit Control 360°';
$page_css = 'dashboard.css';
$LoadChartJS = true;

// ============================================================================
// محرك البيانات الهجين (MySQL + localStorage Hydration) لأعمار الديون والحقوق
// ============================================================================
$dbCustomers = [];
$dbSuppliers = [];
$dbSalesInvoices = [];
$dbPurchaseInvoices = [];
$dbChecks = [];

try {
    require_once __DIR__ . '/db_bridge.php';
    $pdo = getDBConnection();
    if (isset($pdo)) {
        // 1. العملاء
        try {
            $stmt = $pdo->prepare("SELECT id, data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmt->execute([$GLOBAL_TENANT_ID]);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '', true);
                if (is_array($d)) {
                    $d['_db_id'] = $r['id'];
                    $dbCustomers[] = $d;
                }
            }
        } catch (Exception $e) {}

        // 2. الموردون
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

        // 3. فواتير المبيعات
        try {
            $stmt = $pdo->prepare("SELECT id, data, date_val, total_val, customer_id_val FROM sales_invoices WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmt->execute([$GLOBAL_TENANT_ID]);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '', true);
                if (!is_array($d)) $d = [];
                if (empty($d['date']) && !empty($r['date_val'])) $d['date'] = $r['date_val'];
                if (empty($d['total']) && !empty($r['total_val'])) $d['total'] = (float)$r['total_val'];
                if (empty($d['customerId']) && !empty($r['customer_id_val'])) $d['customerId'] = $r['customer_id_val'];
                $dbSalesInvoices[] = $d;
            }
        } catch (Exception $e) {}

        // 4. فواتير المشتريات
        try {
            $stmt = $pdo->prepare("SELECT id, data FROM purchase_invoices WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmt->execute([$GLOBAL_TENANT_ID]);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '', true);
                if (is_array($d)) $dbPurchaseInvoices[] = $d;
            }
        } catch (Exception $e) {}

        // 5. الشيكات
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
include 'includes/sidebar.php';
?>
<link rel="stylesheet" href="css/ unified-design-tokens.css">

<style>
    .aging-master-wrap {
        padding: 18px 22px 50px 22px;
        max-width: 1680px;
        margin: 0 auto;
        direction: rtl;
        font-family: 'Tajawal', 'Cairo', sans-serif;
    }

    /* Hero Header */
    .aging-hero {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92));
        border: 1.5px solid rgba(244, 63, 94, 0.32);
        border-right: 6px solid #f43f5e;
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

    .aging-hero-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .aging-hero-ico {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(244, 63, 94, 0.22), rgba(245, 158, 11, 0.22));
        border: 1.5px solid rgba(244, 63, 94, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        box-shadow: 0 0 22px rgba(244, 63, 94, 0.25);
    }

    .aging-hero-text h1 {
        margin: 0;
        font-size: 1.42rem;
        font-weight: 900;
        color: #f8fafc;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .aging-hero-text p {
        margin: 5px 0 0 0;
        font-size: 0.86rem;
        color: #94a3b8;
    }

    .aging-actions-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ag-btn {
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

    .ag-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
    }

    .ag-btn-amber { background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.22)); border-color: rgba(245, 158, 11, 0.5); color: #fbbf24; }
    .ag-btn-emerald { background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.22)); border-color: rgba(16, 185, 129, 0.5); color: #34d399; }
    .ag-btn-sky { background: linear-gradient(135deg, rgba(56, 189, 248, 0.25), rgba(37, 99, 235, 0.22)); border-color: rgba(56, 189, 248, 0.5); color: #38bdf8; }
    .ag-btn-rose { background: linear-gradient(135deg, rgba(244, 63, 94, 0.25), rgba(225, 29, 72, 0.22)); border-color: rgba(244, 63, 94, 0.5); color: #fda4af; }
    .ag-btn-purple { background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(126, 34, 206, 0.22)); border-color: rgba(168, 85, 247, 0.5); color: #c084fc; }

    /* 7 Executive KPI Cards */
    .ag-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(205px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .ag-kpi-card {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 16px;
        padding: 14px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .ag-kpi-card:hover {
        transform: translateY(-3px);
        border-color: rgba(56, 189, 248, 0.45);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
    }

    .ag-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .ag-kpi-lbl {
        font-size: 0.78rem;
        font-weight: 800;
        color: #94a3b8;
    }

    .ag-kpi-val {
        font-size: 1.45rem;
        font-weight: 900;
        color: #f8fafc;
        line-height: 1.15;
    }

    .ag-kpi-sub {
        font-size: 0.72rem;
        font-weight: 700;
        margin-top: 5px;
    }

    /* 4 Main Tabs */
    .ag-tabs-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        background: rgba(15, 23, 42, 0.7);
        padding: 8px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .ag-tab-btn {
        flex: 1;
        min-width: 210px;
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

    .ag-tab-btn.active {
        background: linear-gradient(135deg, rgba(244, 63, 94, 0.22), rgba(56, 189, 248, 0.16));
        color: #fff;
        border-color: rgba(244, 63, 94, 0.5);
        box-shadow: 0 4px 15px rgba(244, 63, 94, 0.18);
    }

    .ag-tab-panel { display: none; }
    .ag-tab-panel.active { display: block; }

    /* Filter Bar */
    .ag-filter-bar {
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
        .ag-filter-bar { grid-template-columns: 1fr 1fr; }
    }

    .ag-field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 800;
        color: #94a3b8;
        margin-bottom: 5px;
    }

    .ag-input, .ag-select {
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
    .ag-charts-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 1024px) {
        .ag-charts-grid { grid-template-columns: 1fr; }
    }

    .ag-card {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        padding: 16px 18px;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.25);
    }

    /* Table */
    .ag-table-wrap {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .ag-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    .ag-table th {
        background: rgba(15, 23, 42, 0.96);
        color: #38bdf8;
        padding: 12px 13px;
        font-weight: 900;
        font-size: 0.8rem;
        border-bottom: 1.5px solid rgba(56, 189, 248, 0.25);
        white-space: nowrap;
    }

    .ag-table td {
        padding: 11px 13px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        color: #e2e8f0;
        font-size: 0.84rem;
        vertical-align: middle;
    }

    .ag-table tbody tr:hover {
        background: rgba(56, 189, 248, 0.06);
    }

    .risk-badge {
        padding: 3px 9px;
        border-radius: 8px;
        font-size: 0.74rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .risk-safe { background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); }
    .risk-warn { background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); }
    .risk-high { background: rgba(249, 115, 22, 0.18); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.4); }
    .risk-crit { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); }

    /* Progress Bar */
    .usage-bar-bg {
        width: 100%;
        height: 7px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 4px;
    }

    .usage-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    /* Light Mode Compatibility */
    html[data-theme="warm_cream"] .aging-hero,
    html[data-theme="ultra_white"] .aging-hero,
    html[data-theme="exec_gray"] .aging-hero,
    html[data-theme="warm_cream"] .ag-kpi-card,
    html[data-theme="ultra_white"] .ag-kpi-card,
    html[data-theme="exec_gray"] .ag-kpi-card,
    html[data-theme="warm_cream"] .ag-filter-bar,
    html[data-theme="ultra_white"] .ag-filter-bar,
    html[data-theme="exec_gray"] .ag-filter-bar,
    html[data-theme="warm_cream"] .ag-card,
    html[data-theme="ultra_white"] .ag-card,
    html[data-theme="exec_gray"] .ag-card,
    html[data-theme="warm_cream"] .ag-table-wrap,
    html[data-theme="ultra_white"] .ag-table-wrap,
    html[data-theme="exec_gray"] .ag-table-wrap {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06) !important;
    }

    html[data-theme="warm_cream"] .aging-hero-text h1,
    html[data-theme="ultra_white"] .aging-hero-text h1,
    html[data-theme="exec_gray"] .aging-hero-text h1,
    html[data-theme="warm_cream"] .ag-kpi-val,
    html[data-theme="ultra_white"] .ag-kpi-val,
    html[data-theme="exec_gray"] .ag-kpi-val,
    html[data-theme="warm_cream"] .ag-table td,
    html[data-theme="ultra_white"] .ag-table td,
    html[data-theme="exec_gray"] .ag-table td {
        color: #0f172a !important;
    }

    html[data-theme="warm_cream"] .ag-input,
    html[data-theme="ultra_white"] .ag-input,
    html[data-theme="exec_gray"] .ag-input,
    html[data-theme="warm_cream"] .ag-select,
    html[data-theme="ultra_white"] .ag-select,
    html[data-theme="exec_gray"] .ag-select {
        background: #f8fafc !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }

    @media print {
        .sidebar, .top-header, .aging-actions-bar, .ag-tabs-bar, .ag-filter-bar, .no-print {
            display: none !important;
        }
        .aging-master-wrap { padding: 0 !important; max-width: 100% !important; }
    }
</style>

<main class="main-content aging-master-wrap" id="main-content">

    <!-- Hero Header -->
    <div class="aging-hero">
        <div class="aging-hero-title">
            <div class="aging-hero-ico">⏳</div>
            <div class="aging-hero-text">
                <h1>
                    منظومة أعمار الديون والحقوق وسقوف الائتمان الذكية 360°
                    <span class="risk-badge risk-safe" id="hero-health-badge">🛡️ الرقابة الائتمانية متصلة</span>
                </h1>
                <p>التحليل العلمي الموحد لأعمار ديون العملاء (لنا) والتزامات الموردين (علينا) ومراقبة سقوف الائتمان والتنبؤ بالتحصيل بالذكاء الاصطناعي</p>
            </div>
        </div>

        <div class="aging-actions-bar">
            <div style="display:inline-flex; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.15); border-radius:11px; padding:3px;">
                <button type="button" class="ag-btn" id="cur-btn-SDG" onclick="setAgingCurrency('SDG')" style="padding:5px 10px; border:none; background:#38bdf8; color:#0f172a;">SDG</button>
                <button type="button" class="ag-btn" id="cur-btn-USD" onclick="setAgingCurrency('USD')" style="padding:5px 10px; border:none; background:transparent;">USD ($)</button>
            </div>

            <button onclick="switchAgingTab('ai')" class="ag-btn ag-btn-amber" title="فتح المحصل الذكي والتنبؤ بالتدفقات النقدية ومخصص الديون">
                🤖 المحصل الذكي والتنبؤ (AI)
            </button>
            <a href="combined_receivables.php" class="ag-btn ag-btn-purple" title="تقرير مديونية الأرصدة الشامل (الأستاذ + الشيكات)">
                ⚖️ مديونية الأرصدة والشيكات
            </a>
            <a href="mainbalances-report.php" class="ag-btn ag-btn-sky" title="حساب الأرصدة الأساسية بالعملتين">
                📊 الأرصدة الأساسية
            </a>
            <button onclick="exportAgingToExcel()" class="ag-btn ag-btn-emerald" title="تصدير جدول أعمار الديون والحقوق إلى إكسل">
                📥 تصدير Excel
            </button>
            <button onclick="window.print()" class="ag-btn" title="طباعة التقرير الرسمي">
                🖨️ طباعة
            </button>
        </div>
    </div>

    <!-- 7 Executive KPI Cards -->
    <div class="ag-kpi-grid">
        <div class="ag-kpi-card" style="border-bottom:3px solid #38bdf8;" onclick="quickBucketFilter('ALL')">
            <div class="ag-kpi-top">
                <span class="ag-kpi-lbl">إجمالي ديون العملاء (حقوق لنا)</span>
                <span>💰</span>
            </div>
            <div class="ag-kpi-val" id="kpi-ar-total" style="color:#38bdf8;">0</div>
            <div class="ag-kpi-sub" id="kpi-ar-count" style="color:#94a3b8;">0 عميل مدين</div>
        </div>

        <div class="ag-kpi-card" style="border-bottom:3px solid #10b981;" onclick="quickBucketFilter('NOT_DUE')">
            <div class="ag-kpi-top">
                <span class="ag-kpi-lbl">ديون لم يحن استحقاقها (آمنة)</span>
                <span>🟢</span>
            </div>
            <div class="ag-kpi-val" id="kpi-not-due" style="color:#34d399;">0</div>
            <div class="ag-kpi-sub" id="kpi-not-due-pct" style="color:#34d399;">0% من الإجمالي</div>
        </div>

        <div class="ag-kpi-card" style="border-bottom:3px solid #facc15;" onclick="quickBucketFilter('D30')">
            <div class="ag-kpi-top">
                <span class="ag-kpi-lbl">متأخرات (1 - 30 يوماً)</span>
                <span>🟡</span>
            </div>
            <div class="ag-kpi-val" id="kpi-30" style="color:#facc15;">0</div>
            <div class="ag-kpi-sub" style="color:#facc15;">تذكير ودي مبكر</div>
        </div>

        <div class="ag-kpi-card" style="border-bottom:3px solid #fb923c;" onclick="quickBucketFilter('D60')">
            <div class="ag-kpi-top">
                <span class="ag-kpi-lbl">متأخرات (31 - 60 يوماً)</span>
                <span>🟠</span>
            </div>
            <div class="ag-kpi-val" id="kpi-60" style="color:#fb923c;">0</div>
            <div class="ag-kpi-sub" style="color:#fb923c;">مطالبة تحصيل نشطة</div>
        </div>

        <div class="ag-kpi-card" style="border-bottom:3px solid #f87171;" onclick="quickBucketFilter('D90')">
            <div class="ag-kpi-top">
                <span class="ag-kpi-lbl">متأخرات (61 - 90 يوماً)</span>
                <span>🔴</span>
            </div>
            <div class="ag-kpi-val" id="kpi-90" style="color:#f87171;">0</div>
            <div class="ag-kpi-sub" style="color:#f87171;">مخاطرة عالية — إيقاف آجل</div>
        </div>

        <div class="ag-kpi-card" style="border-bottom:3px solid #e11d48;" onclick="quickBucketFilter('OVER90')">
            <div class="ag-kpi-top">
                <span class="ag-kpi-lbl">ديون متعثرة حرجة (+90 يوماً)</span>
                <span>🚨</span>
            </div>
            <div class="ag-kpi-val" id="kpi-over90" style="color:#fb7185;">0</div>
            <div class="ag-kpi-sub" id="kpi-over90-pct" style="color:#fb7185;">يتطلب جدولة أو إجراء قانوني</div>
        </div>

        <div class="ag-kpi-card" style="border-bottom:3px solid #a855f7;" onclick="switchAgingTab('ap')">
            <div class="ag-kpi-top">
                <span class="ag-kpi-lbl">التزامات الموردين (ديون علينا)</span>
                <span>🏢</span>
            </div>
            <div class="ag-kpi-val" id="kpi-ap-total" style="color:#c084fc;">0</div>
            <div class="ag-kpi-sub" id="kpi-net-rights" style="color:#cbd5e1;">صافي الحقوق: 0</div>
        </div>
    </div>

    <!-- 4 Unified Tabs -->
    <div class="ag-tabs-bar">
        <button class="ag-tab-btn active" id="ag-tab-btn-ar" onclick="switchAgingTab('ar')">
            👥 أعمار ديون العملاء والحقوق لنا (Receivables Aging)
        </button>
        <button class="ag-tab-btn" id="ag-tab-btn-ap" onclick="switchAgingTab('ap')">
            🏢 أعمار ديون الموردين والالتزامات علينا (Payables Aging)
        </button>
        <button class="ag-tab-btn" id="ag-tab-btn-credit" onclick="switchAgingTab('credit')">
            🛡️ مراقب سقوف الائتمان والانكشاف والشيكات (Credit Limits Radar)
        </button>
        <button class="ag-tab-btn" id="ag-tab-btn-ai" onclick="switchAgingTab('ai')">
            🤖 التنبؤ بالتحصيل الذكي وجدولة الديون ومخصص IFRS-9 (AI Recovery)
        </button>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 1: ACCOUNTS RECEIVABLE AGING (أعمار ديون العملاء لنا)   -->
    <!-- ========================================================== -->
    <div class="ag-tab-panel active" id="ag-panel-ar">

        <!-- Charts Row -->
        <div class="ag-charts-grid">
            <div class="ag-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:#f8fafc;">📊 التوزيع الزمني لشرائح أعمار الديون (Aging Buckets)</h3>
                    <span style="font-size:0.76rem; color:#38bdf8; font-weight:800;" id="dso-indicator">متوسط فترة التحصيل (DSO): — يوم</span>
                </div>
                <div style="height:220px; position:relative;">
                    <canvas id="agingBarChart"></canvas>
                </div>
            </div>

            <div class="ag-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:#f8fafc;">🎯 مؤشر جودة المحفظة الائتمانية ومخاطر التعثر</h3>
                </div>
                <div style="height:220px; position:relative;">
                    <canvas id="agingPieChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="ag-filter-bar">
            <div class="ag-field">
                <label>🔍 بحث باسم العميل أو رقم الهاتف أو الكود:</label>
                <input type="text" id="ar-search" class="ag-input" placeholder="ابحث عن عميل..." oninput="renderARTable()">
            </div>

            <div class="ag-field">
                <label>⏳ فلترة حسب شريحة العمر:</label>
                <select id="ar-bucket-filter" class="ag-select" onchange="renderARTable()">
                    <option value="ALL">جميع الشرائح الزمنية</option>
                    <option value="NOT_DUE">🟢 لم يحن استحقاقها فقط</option>
                    <option value="D30">🟡 متأخر 1 - 30 يوماً</option>
                    <option value="D60">🟠 متأخر 31 - 60 يوماً</option>
                    <option value="D90">🔴 متأخر 61 - 90 يوماً</option>
                    <option value="OVER90">🚨 متعثر +90 يوماً (خطر)</option>
                    <option value="OVER_LIMIT">⚠️ متجاوز للسقف الائتماني</option>
                </select>
            </div>

            <div class="ag-field">
                <label>📊 الترتيب حسب:</label>
                <select id="ar-sort" class="ag-select" onchange="renderARTable()">
                    <option value="total_desc">الأعلى مديونية أولاً</option>
                    <option value="over90_desc">الأكثر تعثراً (+90 يوم) أولاً</option>
                    <option value="days_desc">الأقدم تأخيراً (عدد الأيام)</option>
                    <option value="usage_desc">الأعلى استهلاكاً للسقف %</option>
                    <option value="name_asc">الاسم أبجدياً</option>
                </select>
            </div>

            <div class="ag-field">
                <label>📅 تاريخ المرجع للحساب:</label>
                <input type="date" id="ar-ref-date" class="ag-input" value="<?php echo date('Y-m-d'); ?>" onchange="recalculateAllAging()">
            </div>

            <div class="ag-field">
                <label>💬 نبرة رسالة المطالبة:</label>
                <select id="ar-wa-tone" class="ag-select">
                    <option value="polite">🌱 تذكير ودي مهذب</option>
                    <option value="firm">⚖️ مطالبة رسمية حازمة</option>
                    <option value="legal">🚨 إنذار نهائي قبل الإجراء القانوني</option>
                </select>
            </div>

            <div class="ag-field">
                <button onclick="resetARFilters()" class="ag-btn" style="height:38px;">إعادة ضبط ✖</button>
            </div>
        </div>

        <!-- AR Table -->
        <div class="ag-table-wrap">
            <div style="padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="font-weight:900; color:#38bdf8; font-size:0.92rem;">
                    📋 جدول أعمار ديون العملاء التفصيلي (<span id="ar-showing-count">0</span> عميل)
                </div>
                <div style="font-size:0.78rem; color:#94a3b8;">
                    يتم احتساب السداد والمرتجعات آلياً بنظام (FIFO — الأقدم فالأحدث) مع ربط مباشر بسندات القبض وكشف الحساب
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="ag-table" id="agingTable">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الهاتف</th>
                            <th>إجمالي المديونية</th>
                            <th>شيكات معلقة</th>
                            <th>لم يحن</th>
                            <th>1 - 30 يوم</th>
                            <th>31 - 60 يوم</th>
                            <th>61 - 90 يوم</th>
                            <th>+90 يوم (متعثر)</th>
                            <th>أقصى تأخير</th>
                            <th>احتمال التحصيل AI</th>
                            <th class="no-print">إجراءات التحصيل والربط الفوري</th>
                        </tr>
                    </thead>
                    <tbody id="ar-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 2: ACCOUNTS PAYABLE AGING (أعمار ديون الموردين علينا)  -->
    <!-- ========================================================== -->
    <div class="ag-tab-panel" id="ag-panel-ap">
        <div class="ag-filter-bar" style="grid-template-columns: 2fr 1fr 1fr auto;">
            <div class="ag-field">
                <label>🔍 بحث باسم المورد أو رقم الهاتف:</label>
                <input type="text" id="ap-search" class="ag-input" placeholder="ابحث عن مورد..." oninput="renderAPTable()">
            </div>
            <div class="ag-field">
                <label>⏳ شريحة الاستحقاق:</label>
                <select id="ap-bucket-filter" class="ag-select" onchange="renderAPTable()">
                    <option value="ALL">جميع المستحقات</option>
                    <option value="OVERDUE">المتأخرات المستحقة السداد فوراً</option>
                    <option value="OVER90">متأخرات حرجة (+90 يوم)</option>
                </select>
            </div>
            <div class="ag-field">
                <label>📊 الترتيب:</label>
                <select id="ap-sort" class="ag-select" onchange="renderAPTable()">
                    <option value="total_desc">الأعلى التزاماً أولاً</option>
                    <option value="over90_desc">الأقدم استحقاقاً أولاً</option>
                </select>
            </div>
            <div class="ag-field">
                <a href="single-cash-payment.php?mode=payment" class="ag-btn ag-btn-amber" style="height:38px;">💸 إصدار سند صرف لمورد</a>
            </div>
        </div>

        <div class="ag-table-wrap">
            <div style="padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:900; color:#c084fc; font-size:0.92rem;">
                    🏢 جدول أعمار ديون الموردين والالتزامات المستحقة علينا (<span id="ap-showing-count">0</span> مورد)
                </div>
                <div style="font-size:0.78rem; color:#94a3b8;">
                    مرتبط بفواتير المشتريات الآجلة وسندات الصرف والشيكات الصادرة وكشف حساب المورد
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="ag-table">
                    <thead>
                        <tr>
                            <th>المورد / صاحب الحق</th>
                            <th>الهاتف</th>
                            <th>إجمالي المستحق له</th>
                            <th>شيكات صادرة معلقة</th>
                            <th>لم يحن</th>
                            <th>1 - 30 يوم</th>
                            <th>31 - 60 يوم</th>
                            <th>61 - 90 يوم</th>
                            <th>+90 يوم</th>
                            <th>أولوية السداد</th>
                            <th class="no-print">الإجراءات والسداد</th>
                        </tr>
                    </thead>
                    <tbody id="ap-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 3: CREDIT LIMITS & EXPOSURE RADAR (مراقب سقوف الائتمان) -->
    <!-- ========================================================== -->
    <div class="ag-tab-panel" id="ag-panel-credit">
        <div class="ag-card" style="margin-bottom:16px; border-right:5px solid #f59e0b;" id="credit-alert-banner"></div>

        <div class="ag-table-wrap">
            <div style="padding:14px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="font-weight:900; color:#fbbf24; font-size:0.95rem;">
                    🛡️ رادار سقوف الائتمان، نسبة الاستهلاك، وتصنيف ولاء العملاء (مدمج مع مراقب الائتمان)
                </div>
                <a href="customers.php" class="ag-btn ag-btn-sky" style="padding:6px 12px; font-size:0.78rem;">⚙️ تعديل السقوف الائتمانية للعملاء ↗</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="ag-table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>تصنيف التفاعل والولاء</th>
                            <th>المديونية الفعلية</th>
                            <th>السقف الائتماني المعتمد</th>
                            <th>الرصيد المتاح للتعامل</th>
                            <th style="min-width:190px;">نسبة استهلاك السقف %</th>
                            <th>أيام التأخير</th>
                            <th>القرار الائتماني الآلي</th>
                            <th class="no-print">إجراء سريع</th>
                        </tr>
                    </thead>
                    <tbody id="credit-limits-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 4: AI DEBT RECOVERY & FORECASTING (التنبؤ بالذكاء الاصطناعي) -->
    <!-- ========================================================== -->
    <div class="ag-tab-panel" id="ag-panel-ai">
        <div class="ag-charts-grid" style="grid-template-columns: 1fr 1fr;">
            <!-- AI Expected Cash Inflow Forecast -->
            <div class="ag-card" style="border-top:3px solid #10b981;">
                <h3 style="margin:0 0 12px 0; color:#34d399; font-size:1.02rem; display:flex; align-items:center; gap:8px;">
                    📈 التنبؤ الذكي بالتدفقات النقدية المتوقع تحصيلها (AI Cash-Inflow Forecast)
                </h3>
                <div id="ai-cash-forecast-box"></div>
            </div>

            <!-- IFRS-9 Bad Debt Provision Calculator -->
            <div class="ag-card" style="border-top:3px solid #f43f5e;">
                <h3 style="margin:0 0 12px 0; color:#fda4af; font-size:1.02rem; display:flex; align-items:center; gap:8px;">
                    ⚖️ حاسبة مخصص الديون المشكوك في تحصيلها (IFRS-9 ECL Provision)
                </h3>
                <div id="ai-ifrs9-box"></div>
            </div>
        </div>

        <!-- AI Strategic Action Plan & Installment Scheduler -->
        <div class="ag-card" style="border-top:3px solid #f59e0b;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:10px;">
                <h3 style="margin:0; color:#fbbf24; font-size:1.05rem;">
                    🤖 خطة البروفيسور والمحصل الذكي لاسترداد الديون المتعثرة وجدولة السداد الفورية
                </h3>
                <button onclick="askCloudAIAssistant()" class="ag-btn ag-btn-amber" style="padding:6px 12px; font-size:0.78rem;">
                    ✨ توليد مذكرة قانونية ومفاوضات متقدمة (AI)
                </button>
            </div>
            <div id="ai-recovery-plan-body"></div>
        </div>
    </div>

</main>

<!-- Debt Restructuring & Installment Plan Modal (جدولة المديونية) -->
<div id="schedule-debt-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.84); backdrop-filter:blur(6px); z-index:999999; align-items:center; justify-content:center; padding:20px; direction:rtl;">
    <div style="background:#0f172a; border:1.5px solid #f59e0b; border-radius:20px; width:100%; max-width:650px; padding:22px; color:#f8fafc; font-family:'Tajawal',sans-serif; box-shadow:0 25px 60px rgba(0,0,0,0.65);">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:14px;">
            <h3 style="margin:0; font-size:1.1rem; color:#fbbf24;">📜 اتفاقية جدولة مديونية وتقسيط التزامات</h3>
            <button onclick="document.getElementById('schedule-debt-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 10px; cursor:pointer; font-weight:800;">إغلاق ✖</button>
        </div>
        <div id="schedule-debt-body"></div>
    </div>
</div>

<script src="js/localforage.min.js"></script>
<script src="js/permissions-manager.js"></script>
<script src="js/app.js"></script>

<script>
    // Server-hydrated DB payloads
    const SERVER_DB_DATA = {
        customers: <?php echo json_encode($dbCustomers, JSON_UNESCAPED_UNICODE); ?>,
        suppliers: <?php echo json_encode($dbSuppliers, JSON_UNESCAPED_UNICODE); ?>,
        salesInvoices: <?php echo json_encode($dbSalesInvoices, JSON_UNESCAPED_UNICODE); ?>,
        purchaseInvoices: <?php echo json_encode($dbPurchaseInvoices, JSON_UNESCAPED_UNICODE); ?>,
        checks: <?php echo json_encode($dbChecks, JSON_UNESCAPED_UNICODE); ?>
    };

    let AGING_CURRENCY = 'SDG';
    const EXCHANGE_RATE = Number(localStorage.getItem('erp_exchange_rate') || 600) || 600;
    let AR_AGING_LIST = [];
    let AP_AGING_LIST = [];
    let barChartInstance = null;
    let pieChartInstance = null;

    function fmtAmt(val) {
        const num = Number(val || 0);
        const converted = AGING_CURRENCY === 'USD' ? (num / EXCHANGE_RATE) : num;
        return converted.toLocaleString('en-US', { maximumFractionDigits: AGING_CURRENCY === 'USD' ? 2 : 0 });
    }

    function setAgingCurrency(cur) {
        AGING_CURRENCY = cur;
        document.getElementById('cur-btn-SDG').style.background = cur === 'SDG' ? '#38bdf8' : 'transparent';
        document.getElementById('cur-btn-SDG').style.color = cur === 'SDG' ? '#0f172a' : '#f8fafc';
        document.getElementById('cur-btn-USD').style.background = cur === 'USD' ? '#38bdf8' : 'transparent';
        document.getElementById('cur-btn-USD').style.color = cur === 'USD' ? '#0f172a' : '#f8fafc';
        renderAllAgingViews();
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
            const existing = map.get(k) || {};
            map.set(k, { ...existing, ...item });
        });
        return Array.from(map.values());
    }

    function recalculateAllAging() {
        const refInput = document.getElementById('ar-ref-date')?.value;
        const today = refInput ? new Date(refInput + 'T00:00:00') : new Date();
        today.setHours(0, 0, 0, 0);

        const customers = mergeByKey(SERVER_DB_DATA.customers, safeJSON('erp_customers_v1'), 'id', 'name');
        const suppliers = mergeByKey(SERVER_DB_DATA.suppliers, safeJSON('erp_suppliers_v1'), 'id', 'name');
        const salesInvoices = mergeByKey(SERVER_DB_DATA.salesInvoices, safeJSON('erp_sales_invoices_v1'), 'invoiceNo', 'id');
        const purchaseInvoices = mergeByKey(SERVER_DB_DATA.purchaseInvoices, safeJSON('erp_purchase_invoices_v1'), 'invoiceNo', 'id');
        const checks = mergeByKey(SERVER_DB_DATA.checks, safeJSON('erp_checks_v1'), 'checkNo', 'id');

        // Collect all customer receipts to apply FIFO if needed
        const singleReceipts = safeJSON('erp_single_receipts_v1');
        const singlePayments = safeJSON('erp_single_payments_v1');
        const multiReceipts = safeJSON('erp_receipts_v1');
        const multiPayments = safeJSON('erp_payments_v1');

        const customerMap = new Map();
        const getOrCreateCust = (id, name, phone = '', limit = 0, ledgerBal = 0) => {
            const cleanName = String(name || 'عميل عام').trim();
            const key = cleanName.toLowerCase();
            if (!customerMap.has(key)) {
                customerMap.set(key, {
                    id: id || cleanName,
                    name: cleanName,
                    phone: phone || '-',
                    creditLimit: Number(limit || 0),
                    ledgerBal: Number(ledgerBal || 0),
                    pendingChecks: 0,
                    invoiceCount: 0,
                    totalSalesVol: 0,
                    total: 0,
                    notDue: 0,
                    days30: 0,
                    days60: 0,
                    days90: 0,
                    over90: 0,
                    maxDaysOverdue: 0,
                    invoices: []
                });
            } else {
                const c = customerMap.get(key);
                if (phone && (c.phone === '-' || !c.phone)) c.phone = phone;
                if (limit > c.creditLimit) c.creditLimit = Number(limit);
                if (Math.abs(ledgerBal) > Math.abs(c.ledgerBal)) c.ledgerBal = Number(ledgerBal);
            }
            return customerMap.get(key);
        };

        customers.forEach(c => {
            const bal = parseFloat(c.currentBalance ?? c.balance ?? c.total_debt ?? c.openingBalance ?? 0) || 0;
            const lim = parseFloat(c.creditLimit ?? c.limit ?? 0) || 0;
            getOrCreateCust(c.id || c._db_id, c.name, c.phone || c.mobile, lim, bal);
        });

        // Map Pending Checks
        checks.forEach(chk => {
            if ((chk.status || 'PENDING').toUpperCase() !== 'PENDING') return;
            const amt = parseFloat(chk.amount || 0) || 0;
            if (amt <= 0) return;
            const party = chk.drawerName || chk.customerName || chk.beneficiary || '';
            if (!party) return;
            if ((chk.type || 'INBOUND').toUpperCase() !== 'OUTBOUND') {
                const c = getOrCreateCust('', party);
                c.pendingChecks += amt;
            }
        });

        // Process Sales Invoices into Aging Buckets
        salesInvoices.forEach(inv => {
            if (inv.deleted || inv.is_deleted) return;
            const custName = inv.customerName || inv.clientName || inv.customer || 'عميل نقدي';
            if (custName === 'عميل نقدي' && inv.paymentMethod !== 'credit' && inv.paymentType !== 'credit') return;

            const c = getOrCreateCust(inv.customerId || inv.customer, custName, inv.customerPhone);
            const invTotal = parseFloat(inv.grandTotal ?? inv.netTotal ?? inv.total ?? inv.totalAmount ?? 0) || 0;
            const isReturn = (inv.type === 'return' || inv.isReturn);
            if (!isReturn) {
                c.invoiceCount++;
                c.totalSalesVol += invTotal;
            }

            const isCredit = (inv.paymentMethod === 'credit' || inv.paymentType === 'credit' || (inv.remainingAmount && Number(inv.remainingAmount) > 0));
            if (!isCredit && !isReturn) return;

            const paid = parseFloat(inv.paidAmount ?? 0) || 0;
            const rem = isReturn ? -Math.abs(invTotal - paid) : Math.max(0, invTotal - paid);
            if (rem === 0) return;

            const refDateStr = inv.dueDate || inv.date;
            let diffDays = 0;
            if (refDateStr) {
                const d = new Date(refDateStr);
                if (!isNaN(d.getTime())) {
                    d.setHours(0, 0, 0, 0);
                    diffDays = Math.ceil((today - d) / (1000 * 60 * 60 * 24));
                }
            }

            if (rem > 0 && diffDays > c.maxDaysOverdue) c.maxDaysOverdue = diffDays;

            c.total += rem;
            if (diffDays <= 0) c.notDue += rem;
            else if (diffDays <= 30) c.days30 += rem;
            else if (diffDays <= 60) c.days60 += rem;
            else if (diffDays <= 90) c.days90 += rem;
            else c.over90 += rem;

            c.invoices.push({
                no: inv.invoiceNo || inv.id || 'INV',
                date: inv.date || '',
                dueDate: inv.dueDate || inv.date || '',
                rem: rem,
                days: Math.max(0, diffDays)
            });
        });

        // Deduct standalone customer receipts via FIFO (oldest bucket first: over90 -> days90 -> days60 -> days30 -> notDue)
        const applyReceiptFIFO = (partyName, amount) => {
            if (!partyName || amount <= 0) return;
            const key = String(partyName).trim().toLowerCase();
            const c = customerMap.get(key);
            if (!c || c.total <= 0) return;
            let remPay = amount;
            ['over90', 'days90', 'days60', 'days30', 'notDue'].forEach(bucket => {
                if (remPay <= 0) return;
                if (c[bucket] > 0) {
                    const deduct = Math.min(c[bucket], remPay);
                    c[bucket] -= deduct;
                    c.total -= deduct;
                    remPay -= deduct;
                }
            });
        };

        singleReceipts.forEach(r => applyReceiptFIFO(r.accountName || r.customerName || r.partyName, parseFloat(r.amount || r.totalAmount || 0)));
        singlePayments.filter(p => p.voucherType === 'receipt').forEach(r => applyReceiptFIFO(r.accountName || r.customerName || r.partyName, parseFloat(r.amount || r.totalAmount || 0)));
        multiReceipts.forEach(r => {
            if (Array.isArray(r.items)) {
                r.items.forEach(it => applyReceiptFIFO(it.accountName || it.partyName, parseFloat(it.amount || 0)));
            }
        });

        // Reconcile with direct Ledger Balance if customer has opening/ledger debt without individual credit invoices
        customerMap.forEach(c => {
            if (c.total <= 0 && c.ledgerBal > 0) {
                c.total = c.ledgerBal;
                c.days30 = c.ledgerBal;
                if (c.maxDaysOverdue === 0) c.maxDaysOverdue = 25;
            }
            // Normalize negative buckets
            ['notDue', 'days30', 'days60', 'days90', 'over90'].forEach(b => {
                if (c[b] < 0) c[b] = 0;
            });
            c.total = c.notDue + c.days30 + c.days60 + c.days90 + c.over90;

            // Compute AI Collection Probability %
            if (c.total > 0) {
                const weightedRisk = (c.notDue * 0.03 + c.days30 * 0.10 + c.days60 * 0.25 + c.days90 * 0.45 + c.over90 * 0.72) / c.total;
                c.aiRecoveryProb = Math.max(12, Math.min(98, Math.round((1 - weightedRisk) * 100)));
            } else {
                c.aiRecoveryProb = 100;
            }
        });

        AR_AGING_LIST = Array.from(customerMap.values()).filter(c => c.total > 0 || c.creditLimit > 0 || c.pendingChecks > 0);

        // ====================================================
        // 2. SUPPLIERS PAYABLES AGING (أعمار ديون الموردين علينا)
        // ====================================================
        const supplierMap = new Map();
        const getOrCreateSup = (id, name, phone = '', ledgerBal = 0) => {
            const cleanName = String(name || 'مورد عام').trim();
            const key = cleanName.toLowerCase();
            if (!supplierMap.has(key)) {
                supplierMap.set(key, {
                    id: id || cleanName,
                    name: cleanName,
                    phone: phone || '-',
                    ledgerBal: Math.abs(Number(ledgerBal || 0)),
                    pendingChecks: 0,
                    total: 0,
                    notDue: 0,
                    days30: 0,
                    days60: 0,
                    days90: 0,
                    over90: 0,
                    maxDaysOverdue: 0
                });
            }
            return supplierMap.get(key);
        };

        suppliers.forEach(s => {
            const bal = Math.abs(parseFloat(s.currentBalance ?? s.balance ?? s.openingBalance ?? 0) || 0);
            getOrCreateSup(s.id || s._db_id, s.name, s.phone || s.mobile, bal);
        });

        checks.forEach(chk => {
            if ((chk.status || 'PENDING').toUpperCase() !== 'PENDING') return;
            if ((chk.type || '').toUpperCase() === 'OUTBOUND') {
                const s = getOrCreateSup('', chk.beneficiary || chk.supplierName || 'مورد');
                s.pendingChecks += parseFloat(chk.amount || 0) || 0;
            }
        });

        purchaseInvoices.forEach(inv => {
            if (inv.deleted || inv.is_deleted) return;
            const supName = inv.supplierName || inv.vendorName || inv.supplier || 'مورد عام';
            const s = getOrCreateSup(inv.supplierId || inv.supplier, supName, inv.supplierPhone);
            const invTotal = parseFloat(inv.grandTotal ?? inv.netTotal ?? inv.total ?? inv.totalAmount ?? 0) || 0;
            const paid = parseFloat(inv.paidAmount ?? 0) || 0;
            const rem = Math.max(0, invTotal - paid);
            if (rem <= 0) return;

            const refDateStr = inv.dueDate || inv.date;
            let diffDays = 0;
            if (refDateStr) {
                const d = new Date(refDateStr);
                if (!isNaN(d.getTime())) {
                    d.setHours(0, 0, 0, 0);
                    diffDays = Math.ceil((today - d) / (1000 * 60 * 60 * 24));
                }
            }
            if (diffDays > s.maxDaysOverdue) s.maxDaysOverdue = diffDays;
            s.total += rem;
            if (diffDays <= 0) s.notDue += rem;
            else if (diffDays <= 30) s.days30 += rem;
            else if (diffDays <= 60) s.days60 += rem;
            else if (diffDays <= 90) s.days90 += rem;
            else s.over90 += rem;
        });

        supplierMap.forEach(s => {
            if (s.total <= 0 && s.ledgerBal > 0) {
                s.total = s.ledgerBal;
                s.days30 = s.ledgerBal;
            }
        });

        AP_AGING_LIST = Array.from(supplierMap.values()).filter(s => s.total > 0 || s.pendingChecks > 0);

        renderAllAgingViews();
    }

    function renderAllAgingViews() {
        updateExecutiveKPIs();
        renderARTable();
        renderAPTable();
        renderCreditLimitsTab();
        renderAIRecoveryTab();
        renderAgingCharts();
    }

    function updateExecutiveKPIs() {
        const activeAR = AR_AGING_LIST.filter(c => c.total > 0);
        const tTotal = activeAR.reduce((s, c) => s + c.total, 0);
        const tNotDue = activeAR.reduce((s, c) => s + c.notDue, 0);
        const t30 = activeAR.reduce((s, c) => s + c.days30, 0);
        const t60 = activeAR.reduce((s, c) => s + c.days60, 0);
        const t90 = activeAR.reduce((s, c) => s + c.days90, 0);
        const tOver90 = activeAR.reduce((s, c) => s + c.over90, 0);
        const tAP = AP_AGING_LIST.reduce((s, x) => s + x.total, 0);

        document.getElementById('kpi-ar-total').textContent = `${fmtAmt(tTotal)} ${AGING_CURRENCY}`;
        document.getElementById('kpi-ar-count').textContent = `${activeAR.length} عميل مدين نشط`;
        document.getElementById('kpi-not-due').textContent = `${fmtAmt(tNotDue)} ${AGING_CURRENCY}`;
        document.getElementById('kpi-not-due-pct').textContent = tTotal > 0 ? `${Math.round((tNotDue / tTotal) * 100)}% من الإجمالي` : '0%';
        document.getElementById('kpi-30').textContent = `${fmtAmt(t30)} ${AGING_CURRENCY}`;
        document.getElementById('kpi-60').textContent = `${fmtAmt(t60)} ${AGING_CURRENCY}`;
        document.getElementById('kpi-90').textContent = `${fmtAmt(t90)} ${AGING_CURRENCY}`;
        document.getElementById('kpi-over90').textContent = `${fmtAmt(tOver90)} ${AGING_CURRENCY}`;
        document.getElementById('kpi-over90-pct').textContent = tTotal > 0 ? `${Math.round((tOver90 / tTotal) * 100)}% ديون متعثرة` : '0% تعثر';
        document.getElementById('kpi-ap-total').textContent = `${fmtAmt(tAP)} ${AGING_CURRENCY}`;
        document.getElementById('kpi-net-rights').textContent = `صافي الحقوق لنا: ${fmtAmt(tTotal - tAP)} ${AGING_CURRENCY}`;

        // Calculate Weighted DSO (Days Sales Outstanding)
        const dso = tTotal > 0 ? Math.round((tNotDue * 5 + t30 * 20 + t60 * 45 + t90 * 75 + tOver90 * 115) / tTotal) : 0;
        document.getElementById('dso-indicator').textContent = `متوسط فترة التحصيل الفعلي (DSO): ${dso} يوماً`;

        const badge = document.getElementById('hero-health-badge');
        if (tOver90 > tTotal * 0.25 && tOver90 > 0) {
            badge.className = 'risk-badge risk-crit';
            badge.textContent = `🚨 إنذار تعثر: ${Math.round((tOver90 / tTotal) * 100)}% من الديون تجاوزت 90 يوماً`;
        } else {
            badge.className = 'risk-badge risk-safe';
            badge.textContent = `🛡️ المحفظة الائتمانية تحت السيطرة (DSO: ${dso} يوم)`;
        }
    }

    function getFilteredAR() {
        const q = (document.getElementById('ar-search')?.value || '').trim().toLowerCase();
        const bucket = document.getElementById('ar-bucket-filter')?.value || 'ALL';
        const sort = document.getElementById('ar-sort')?.value || 'total_desc';

        let list = AR_AGING_LIST.filter(c => c.total > 0 || bucket === 'OVER_LIMIT');

        list = list.filter(c => {
            if (q && !(`${c.name} ${c.phone} ${c.id}`).toLowerCase().includes(q)) return false;
            if (bucket === 'NOT_DUE' && c.notDue <= 0) return false;
            if (bucket === 'D30' && c.days30 <= 0) return false;
            if (bucket === 'D60' && c.days60 <= 0) return false;
            if (bucket === 'D90' && c.days90 <= 0) return false;
            if (bucket === 'OVER90' && c.over90 <= 0) return false;
            if (bucket === 'OVER_LIMIT' && !(c.creditLimit > 0 && c.total >= c.creditLimit)) return false;
            return true;
        });

        list.sort((a, b) => {
            if (sort === 'over90_desc') return b.over90 - a.over90;
            if (sort === 'days_desc') return b.maxDaysOverdue - a.maxDaysOverdue;
            if (sort === 'usage_desc') {
                const ua = a.creditLimit > 0 ? a.total / a.creditLimit : 0;
                const ub = b.creditLimit > 0 ? b.total / b.creditLimit : 0;
                return ub - ua;
            }
            if (sort === 'name_asc') return a.name.localeCompare(b.name, 'ar');
            return b.total - a.total;
        });

        return list;
    }

    function renderARTable() {
        const tbody = document.getElementById('ar-tbody');
        const list = getFilteredAR();
        document.getElementById('ar-showing-count').textContent = list.length.toLocaleString();

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="12" style="text-align:center; padding:36px; color:#94a3b8;">🎉 لا توجد مديونيات مطابقة للفلتر الحالي.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map(c => {
            const safeName = c.name.replace(/'/g, "\\'");
            const safePhone = String(c.phone || '').replace(/'/g, "\\'");
            const probColor = c.aiRecoveryProb >= 75 ? '#34d399' : (c.aiRecoveryProb >= 45 ? '#fbbf24' : '#f87171');
            const overLimitBadge = (c.creditLimit > 0 && c.total > c.creditLimit)
                ? `<span class="risk-badge risk-crit" style="margin-right:6px;">متجاوز للسقف</span>` : '';

            return `
                <tr>
                    <td>
                        <div style="font-weight:900; color:#38bdf8;">👤 ${c.name} ${overLimitBadge}</div>
                        <div style="font-size:0.72rem; color:#94a3b8;">فواتير: ${c.invoiceCount} | السقف: ${c.creditLimit > 0 ? fmtAmt(c.creditLimit) : 'مفتوح'}</div>
                    </td>
                    <td style="font-family:monospace;">${c.phone}</td>
                    <td style="font-weight:900; font-size:0.95rem; color:#f8fafc;">${fmtAmt(c.total)} <small>${AGING_CURRENCY}</small></td>
                    <td style="color:#c084fc; font-weight:800;">${c.pendingChecks > 0 ? fmtAmt(c.pendingChecks) : '—'}</td>
                    <td style="color:#34d399; font-weight:800;">${fmtAmt(c.notDue)}</td>
                    <td style="color:#facc15; font-weight:800;">${fmtAmt(c.days30)}</td>
                    <td style="color:#fb923c; font-weight:800;">${fmtAmt(c.days60)}</td>
                    <td style="color:#f87171; font-weight:800;">${fmtAmt(c.days90)}</td>
                    <td style="color:${c.over90 > 0 ? '#fb7185' : '#64748b'}; font-weight:900;">${fmtAmt(c.over90)}</td>
                    <td>
                        <span class="risk-badge ${c.maxDaysOverdue > 90 ? 'risk-crit' : (c.maxDaysOverdue > 30 ? 'risk-warn' : 'risk-safe')}">
                            ${c.maxDaysOverdue} يوم
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:900; color:${probColor};">${c.aiRecoveryProb}%</div>
                        <div class="usage-bar-bg"><div class="usage-bar-fill" style="width:${c.aiRecoveryProb}%; background:${probColor};"></div></div>
                    </td>
                    <td class="no-print">
                        <div style="display:flex; gap:5px; flex-wrap:wrap;">
                            <a href="single-cash-payment.php?mode=receipt&party=${encodeURIComponent(c.name)}&amount=${Math.round(c.total)}" class="ag-btn ag-btn-emerald" style="padding:4px 9px; font-size:0.74rem;" title="تحصيل وقبض فوري من العميل">💵 قبض</a>
                            <button type="button" onclick="sendSmartAgingWhatsApp('${safeName}','${safePhone}',${c.total},${c.over90},${c.maxDaysOverdue})" class="ag-btn ag-btn-sky" style="padding:4px 9px; font-size:0.74rem;" title="إرسال مطالبة واتساب حسب النبرة المختارة">📲 واتساب</button>
                            <button type="button" onclick="openDebtScheduleModal('${safeName}',${c.total},${c.over90})" class="ag-btn ag-btn-amber" style="padding:4px 9px; font-size:0.74rem;" title="جدولة وتقسيط المديونية">📜 جدولة</button>
                            <a href="account-statement.php?id=${encodeURIComponent(c.id)}&name=${encodeURIComponent(c.name)}" class="ag-btn" style="padding:4px 9px; font-size:0.74rem;" title="كشف الحساب التفصيلي">📑 كشف</a>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderAPTable() {
        const tbody = document.getElementById('ap-tbody');
        const q = (document.getElementById('ap-search')?.value || '').trim().toLowerCase();
        const bucket = document.getElementById('ap-bucket-filter')?.value || 'ALL';
        const sort = document.getElementById('ap-sort')?.value || 'total_desc';

        let list = AP_AGING_LIST.filter(s => {
            if (q && !(`${s.name} ${s.phone}`).toLowerCase().includes(q)) return false;
            if (bucket === 'OVERDUE' && (s.days30 + s.days60 + s.days90 + s.over90) <= 0) return false;
            if (bucket === 'OVER90' && s.over90 <= 0) return false;
            return true;
        });

        list.sort((a, b) => sort === 'over90_desc' ? (b.over90 - a.over90) : (b.total - a.total));
        document.getElementById('ap-showing-count').textContent = list.length.toLocaleString();

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding:34px; color:#94a3b8;">لا توجد التزامات موردين مستحقة حالياً.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map(s => {
            const priority = s.over90 > 0 ? '<span class="risk-badge risk-crit">🚨 سداد فوري لحماية التوريد</span>'
                : (s.days30 + s.days60 > 0 ? '<span class="risk-badge risk-warn">⏳ جدولة هذا الأسبوع</span>' : '<span class="risk-badge risk-safe">🟢 ضمن المهلة</span>');

            return `
                <tr>
                    <td style="font-weight:900; color:#c084fc;">🏢 ${s.name}</td>
                    <td style="font-family:monospace;">${s.phone}</td>
                    <td style="font-weight:900; color:#f8fafc;">${fmtAmt(s.total)} <small>${AGING_CURRENCY}</small></td>
                    <td style="color:#fbbf24; font-weight:800;">${s.pendingChecks > 0 ? fmtAmt(s.pendingChecks) : '—'}</td>
                    <td style="color:#34d399;">${fmtAmt(s.notDue)}</td>
                    <td style="color:#facc15;">${fmtAmt(s.days30)}</td>
                    <td style="color:#fb923c;">${fmtAmt(s.days60)}</td>
                    <td style="color:#f87171;">${fmtAmt(s.days90)}</td>
                    <td style="color:#fb7185; font-weight:900;">${fmtAmt(s.over90)}</td>
                    <td>${priority}</td>
                    <td class="no-print">
                        <div style="display:flex; gap:6px;">
                            <a href="single-cash-payment.php?mode=payment&party=${encodeURIComponent(s.name)}&amount=${Math.round(s.total)}" class="ag-btn ag-btn-amber" style="padding:4px 10px; font-size:0.75rem;">💸 سند صرف</a>
                            <a href="account-statement.php?name=${encodeURIComponent(s.name)}" class="ag-btn" style="padding:4px 10px; font-size:0.75rem;">📑 كشف المورد</a>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderCreditLimitsTab() {
        const tbody = document.getElementById('credit-limits-tbody');
        const banner = document.getElementById('credit-alert-banner');

        const list = [...AR_AGING_LIST].sort((a, b) => {
            const ua = a.creditLimit > 0 ? (a.total / a.creditLimit) : 0.5;
            const ub = b.creditLimit > 0 ? (b.total / b.creditLimit) : 0.5;
            return ub - ua;
        });

        const exceeded = list.filter(c => c.creditLimit > 0 && c.total >= c.creditLimit);
        const near = list.filter(c => c.creditLimit > 0 && c.total >= c.creditLimit * 0.8 && c.total < c.creditLimit);

        if (exceeded.length > 0) {
            banner.innerHTML = `
                <div style="display:flex; align-items:center; gap:12px; color:#fda4af; font-weight:800;">
                    <span style="font-size:1.5rem;">🚨</span>
                    <div>
                        <strong>تنبيه ائتماني حرج:</strong> يوجد (<strong>${exceeded.length}</strong>) عميل تجاوزوا السقف الائتماني المسموح به (وعلى رأسهم: <strong>${exceeded.slice(0, 3).map(x => x.name).join('، ')}</strong>) و(${near.length}) عميل اقتربوا من السقف الحرج (80%+). يُوصى بتفعيل الحظر الآلي للبيع الآجل لحين سداد 30% من المديونية.
                    </div>
                </div>
            `;
        } else {
            banner.innerHTML = `<div style="color:#34d399; font-weight:800;">✅ جميع العملاء ضمن حدود السقوف الائتمانية الآمنة حالياً.</div>`;
        }

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:30px; color:#94a3b8;">لا توجد بيانات سقوف ائتمانية حالياً.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map(c => {
            const effLimit = c.creditLimit > 0 ? c.creditLimit : Math.max(c.total * 1.5, 100000);
            const usagePct = Math.round((c.total / effLimit) * 100);
            const avail = Math.max(0, effLimit - c.total);
            const barColor = usagePct >= 100 ? '#ef4444' : (usagePct >= 80 ? '#f59e0b' : '#10b981');
            const tier = c.invoiceCount >= 12 ? '🌟 VIP (نشط جداً)' : (c.invoiceCount >= 4 ? '✅ REGULAR (منتظم)' : '🔹 موسمي / جديد');
            const decision = usagePct >= 100 ? '<span class="risk-badge risk-crit">⛔ إيقاف البيع الآجل فوراً</span>'
                : (usagePct >= 80 ? '<span class="risk-badge risk-warn">⚠️ اشتراط دفعة نقدية 50%</span>' : '<span class="risk-badge risk-safe">✅ مسموح بالائتمان</span>');

            return `
                <tr>
                    <td style="font-weight:900; color:#38bdf8;">👤 ${c.name}</td>
                    <td><span class="risk-badge risk-safe">${tier} (${c.invoiceCount} فاتورة)</span></td>
                    <td style="font-weight:900; color:#f8fafc;">${fmtAmt(c.total)} ${AGING_CURRENCY}</td>
                    <td style="font-weight:800; color:#cbd5e1;">${fmtAmt(effLimit)} ${c.creditLimit > 0 ? '' : '<small style="color:#64748b;">(تقديري)</small>'}</td>
                    <td style="font-weight:800; color:#34d399;">${fmtAmt(avail)} ${AGING_CURRENCY}</td>
                    <td>
                        <div style="display:flex; justify-content:space-between; font-size:0.76rem; font-weight:900; color:${barColor};">
                            <span>استهلاك السقف</span>
                            <span>${usagePct}%</span>
                        </div>
                        <div class="usage-bar-bg"><div class="usage-bar-fill" style="width:${Math.min(100, usagePct)}%; background:${barColor};"></div></div>
                    </td>
                    <td>${c.maxDaysOverdue} يوم</td>
                    <td>${decision}</td>
                    <td class="no-print">
                        <a href="single-cash-payment.php?mode=receipt&party=${encodeURIComponent(c.name)}&amount=${Math.round(c.total * 0.3)}" class="ag-btn ag-btn-emerald" style="padding:4px 9px; font-size:0.74rem;">تحصيل 30%</a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderAIRecoveryTab() {
        const activeAR = AR_AGING_LIST.filter(c => c.total > 0);
        const tNotDue = activeAR.reduce((s, c) => s + c.notDue, 0);
        const t30 = activeAR.reduce((s, c) => s + c.days30, 0);
        const t60 = activeAR.reduce((s, c) => s + c.days60, 0);
        const t90 = activeAR.reduce((s, c) => s + c.days90, 0);
        const tOver90 = activeAR.reduce((s, c) => s + c.over90, 0);

        // Expected Cash Inflow in 7, 14, 30 days
        const exp7Days = (tNotDue * 0.35) + (t30 * 0.45) + (t60 * 0.25) + (t90 * 0.15) + (tOver90 * 0.08);
        const exp14Days = exp7Days + (tNotDue * 0.30) + (t30 * 0.30) + (t60 * 0.25) + (t90 * 0.15);
        const exp30Days = (tNotDue * 0.92) + (t30 * 0.85) + (t60 * 0.68) + (t90 * 0.45) + (tOver90 * 0.25);

        document.getElementById('ai-cash-forecast-box').innerHTML = `
            <p style="font-size:0.83rem; color:#94a3b8; margin-top:0;">بناءً على خوارزمية احتمالات السداد التاريخية وأعمار الفواتير، يتوقع المحرك الذكي تدفق السيولة التالية للخزينة:</p>
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-top:12px;">
                <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); padding:12px; border-radius:12px; text-align:center;">
                    <div style="font-size:0.75rem; color:#94a3b8;">خلال 7 أيام</div>
                    <div style="font-size:1.2rem; font-weight:900; color:#34d399; margin-top:4px;">${fmtAmt(exp7Days)} ${AGING_CURRENCY}</div>
                </div>
                <div style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); padding:12px; border-radius:12px; text-align:center;">
                    <div style="font-size:0.75rem; color:#94a3b8;">خلال 14 يوماً</div>
                    <div style="font-size:1.2rem; font-weight:900; color:#38bdf8; margin-top:4px;">${fmtAmt(exp14Days)} ${AGING_CURRENCY}</div>
                </div>
                <div style="background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.3); padding:12px; border-radius:12px; text-align:center;">
                    <div style="font-size:0.75rem; color:#94a3b8;">خلال 30 يوماً</div>
                    <div style="font-size:1.2rem; font-weight:900; color:#c084fc; margin-top:4px;">${fmtAmt(exp30Days)} ${AGING_CURRENCY}</div>
                </div>
            </div>
        `;

        // IFRS-9 Expected Credit Loss (ECL) Provision
        const prov30 = t30 * 0.02;
        const prov60 = t60 * 0.10;
        const prov90 = t90 * 0.25;
        const provOver90 = tOver90 * 0.50;
        const totalProvision = prov30 + prov60 + prov90 + provOver90;

        document.getElementById('ai-ifrs9-box').innerHTML = `
            <p style="font-size:0.83rem; color:#94a3b8; margin-top:0;">احتساب المخصص المحاسبي المعياري (IFRS-9) لمواجهة مخاطر الديون المشكوك في تحصيلها (2% لـ30 يوم، 10% لـ60 يوم، 25% لـ90 يوم، 50% لـ+90 يوم):</p>
            <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(244,63,94,0.12); border:1px solid rgba(244,63,94,0.35); padding:12px 16px; border-radius:12px; margin-top:10px; flex-wrap:wrap; gap:10px;">
                <div>
                    <div style="font-size:0.76rem; color:#fda4af;">إجمالي مخصص الديون المشكوك فيها الواجب تكوينه:</div>
                    <div style="font-size:1.35rem; font-weight:900; color:#fb7185;">${fmtAmt(totalProvision)} ${AGING_CURRENCY}</div>
                </div>
                <a href="journal.php?desc=${encodeURIComponent('تكوين مخصص ديون مشكوك في تحصيلها وفق معيار IFRS-9')}&amount=${Math.round(totalProvision)}" class="ag-btn ag-btn-rose" style="font-size:0.78rem;">
                    ⚖️ ترحيل قيد المخصص في اليومية ↗
                </a>
            </div>
        `;

        // Top Priority Debtors Action Plan
        const topCrit = [...activeAR].sort((a, b) => (b.over90 + b.days90) - (a.over90 + a.days90)).slice(0, 6);
        if (topCrit.length === 0) {
            document.getElementById('ai-recovery-plan-body').innerHTML = `<div style="color:#34d399; font-weight:800;">🎉 ممتاز! لا توجد مديونيات متعثرة تتطلب خطة طوارئ حالياً.</div>`;
        } else {
            document.getElementById('ai-recovery-plan-body').innerHTML = `
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:12px;">
                    ${topCrit.map((c, idx) => {
                        const safeName = c.name.replace(/'/g, "\\'");
                        const safePhone = String(c.phone || '').replace(/'/g, "\\'");
                        return `
                            <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:14px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <strong style="color:#38bdf8;">#${idx + 1} — ${c.name}</strong>
                                    <span class="risk-badge risk-crit">الإجمالي: ${fmtAmt(c.total)} ${AGING_CURRENCY}</span>
                                </div>
                                <div style="font-size:0.8rem; color:#cbd5e1; line-height:1.6; margin-bottom:10px;">
                                    • متأخرات حرجة (+60/+90 يوم): <strong style="color:#fb7185;">${fmtAmt(c.over90 + c.days90)} ${AGING_CURRENCY}</strong><br>
                                    • التوصية الذكية: تحصيل دفعة فورية <strong>30% (${fmtAmt(c.total * 0.3)} ${AGING_CURRENCY})</strong> وجدولة الباقي على <strong>3 أقساط</strong>.
                                </div>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <button onclick="openDebtScheduleModal('${safeName}',${c.total},${c.over90})" class="ag-btn ag-btn-amber" style="padding:4px 10px; font-size:0.75rem;">📜 إنشاء اتفاقية جدولة</button>
                                    <button onclick="sendSmartAgingWhatsApp('${safeName}','${safePhone}',${c.total},${c.over90},${c.maxDaysOverdue})" class="ag-btn ag-btn-emerald" style="padding:4px 10px; font-size:0.75rem;">📲 إرسال مقترح التسوية</button>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }
    }

    function renderAgingCharts() {
        if (typeof Chart === 'undefined') return;
        const activeAR = AR_AGING_LIST.filter(c => c.total > 0);
        const dataArr = [
            activeAR.reduce((s, c) => s + c.notDue, 0),
            activeAR.reduce((s, c) => s + c.days30, 0),
            activeAR.reduce((s, c) => s + c.days60, 0),
            activeAR.reduce((s, c) => s + c.days90, 0),
            activeAR.reduce((s, c) => s + c.over90, 0)
        ].map(v => AGING_CURRENCY === 'USD' ? +(v / EXCHANGE_RATE).toFixed(2) : Math.round(v));

        const labels = ['لم يحن (آمن)', '1 - 30 يوم', '31 - 60 يوم', '61 - 90 يوم', '+90 يوم (متعثر)'];
        const colors = ['#10b981', '#facc15', '#fb923c', '#f87171', '#e11d48'];

        const ctxBar = document.getElementById('agingBarChart')?.getContext('2d');
        if (ctxBar) {
            if (barChartInstance) barChartInstance.destroy();
            barChartInstance = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: `قيمة المديونية (${AGING_CURRENCY})`,
                        data: dataArr,
                        backgroundColor: colors,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { color: '#cbd5e1', font: { family: 'Tajawal', weight: 'bold' } } }
                    }
                }
            });
        }

        const ctxPie = document.getElementById('agingPieChart')?.getContext('2d');
        if (ctxPie) {
            if (pieChartInstance) pieChartInstance.destroy();
            pieChartInstance = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataArr,
                        backgroundColor: colors,
                        borderWidth: 1,
                        borderColor: '#0f172a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { color: '#cbd5e1', font: { family: 'Tajawal', size: 11 } } }
                    }
                }
            });
        }
    }

    function sendSmartAgingWhatsApp(name, phone, total, over90, maxDays) {
        const tone = document.getElementById('ar-wa-tone')?.value || 'polite';
        const cleanPhone = String(phone || '').replace(/[^0-9]/g, '');
        let msg = '';

        if (tone === 'polite') {
            msg = `🌱 *تذكير ودي بالرصيد المستحق*\n`
                + `━━━━━━━━━━━━━━━━━━━━\n`
                + `عميلنا العزيز / *${name}* المحترم،\n`
                + `تحية طيبة، نود تذكيركم بأن إجمالي الرصيد المستحق في حسابكم يبلغ: *${fmtAmt(total)} ${AGING_CURRENCY}*.\n`
                + `نسعد بتواصلكم لتأكيد موعد السداد أو جدولة الدفعة القادمة، شاكرين ثقتكم وتعاونكم الدائم.`;
        } else if (tone === 'firm') {
            msg = `⚖️ *مطالبة رسمية بسداد متأخرات مستحقة*\n`
                + `━━━━━━━━━━━━━━━━━━━━\n`
                + `السيد / *${name}* المحترم،\n`
                + `نحيطكم علماً بأن رصيدكم المستحق البالغ *${fmtAmt(total)} ${AGING_CURRENCY}* قد تجاوز فترة الائتمان المسموحة بـ (*${maxDays} يوماً*)${over90 > 0 ? `، منها *${fmtAmt(over90)} ${AGING_CURRENCY}* تجاوزت 90 يوماً` : ''}.\n`
                + `يرجى التكرم بسداد 50% على الأقل خلال 48 ساعة لتفادي إيقاف التعامل الآجل تلقائياً.`;
        } else {
            msg = `🚨 *إنذار نهائي قبل الإجراء القانوني وتجميد الحساب*\n`
                + `━━━━━━━━━━━━━━━━━━━━\n`
                + `السيد / *${name}*،\n`
                + `نظراً لتأخر سداد مديونيتكم البالغة *${fmtAmt(total)} ${AGING_CURRENCY}* لمدة (*${maxDays} يوماً*) رغم المطالبات السابقة، نمهلكم 72 ساعة أخيرة للسداد أو توقيع اتفاقية جدولة رسمية بمقر الشركة قبل إحالة الملف للشؤون القانونية.`;
        }

        if (window.logSystemAction) {
            window.logSystemAction('مطالبة تحصيل واتساب', 'أعمار الديون والتحصيل', name, `إرسال مطالبة تحصيل (${tone}) للعميل ${name} بمبلغ ${fmtAmt(total)} ${AGING_CURRENCY}`);
        }

        const url = cleanPhone ? `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`;
        window.open(url, '_blank');
    }

    function openDebtScheduleModal(name, total, over90) {
        const modal = document.getElementById('schedule-debt-modal');
        const body = document.getElementById('schedule-debt-body');
        const downPayment = Math.round(total * 0.25);
        const remaining = total - downPayment;
        const inst3 = Math.round(remaining / 3);

        body.innerHTML = `
            <div style="background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3); padding:12px 16px; border-radius:12px; margin-bottom:14px;">
                <div style="font-size:0.82rem; color:#94a3b8;">العميل / المدين: <strong style="color:#fff;">${name}</strong></div>
                <div style="font-size:1.1rem; font-weight:900; color:#fbbf24; margin-top:4px;">إجمالي المديونية محل الجدولة: ${fmtAmt(total)} ${AGING_CURRENCY}</div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                <div style="background:rgba(255,255,255,0.04); padding:10px 12px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">1️⃣ دفعة مقدمة فورية (25%):</div>
                    <div style="font-size:1rem; font-weight:900; color:#34d399;">${fmtAmt(downPayment)} ${AGING_CURRENCY}</div>
                </div>
                <div style="background:rgba(255,255,255,0.04); padding:10px 12px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">2️⃣ المتبقي على 3 أقساط شهرية:</div>
                    <div style="font-size:1rem; font-weight:900; color:#38bdf8;">${fmtAmt(inst3)} ${AGING_CURRENCY} / شهرياً</div>
                </div>
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
                <a href="single-cash-payment.php?mode=receipt&party=${encodeURIComponent(name)}&amount=${downPayment}" class="ag-btn ag-btn-emerald">💵 قبض الدفعة المقدمة الآن (25%)</a>
                <button onclick="window.print()" class="ag-btn ag-btn-sky">🖨️ طباعة اتفاقية الجدولة</button>
            </div>
        `;
        modal.style.display = 'flex';
    }

    function askCloudAIAssistant() {
        const activeAR = AR_AGING_LIST.filter(c => c.total > 0).slice(0, 20);
        if (activeAR.length === 0) return;
        const summary = activeAR.map(c => `${c.name}: إجمالي ${fmtAmt(c.total)} ${AGING_CURRENCY} (متعثر +90 يوم: ${fmtAmt(c.over90)})`).join('\n');
        fetch('api/ai-assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: 'أنت خبير ائتمان دولي ومحامي تحصيل ديون تجارية. قدم مذكرة تنفيذية موجزة وخطة تفاوض لتحصيل هذه الديون بأعلى كفاءة.',
                context: summary
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.reply) {
                const box = document.getElementById('ai-recovery-plan-body');
                box.insertAdjacentHTML('afterbegin', `<div style="background:rgba(56,189,248,0.1); border:1px solid #38bdf8; border-radius:14px; padding:14px; margin-bottom:14px; line-height:1.7;">${d.reply.replace(/\n/g, '<br>')}</div>`);
            }
        })
        .catch(() => {});
    }

    function switchAgingTab(tab) {
        ['ar', 'ap', 'credit', 'ai'].forEach(t => {
            document.getElementById('ag-tab-btn-' + t).classList.toggle('active', t === tab);
            document.getElementById('ag-panel-' + t).classList.toggle('active', t === tab);
        });
    }

    function quickBucketFilter(b) {
        switchAgingTab('ar');
        document.getElementById('ar-bucket-filter').value = b;
        renderARTable();
    }

    function resetARFilters() {
        document.getElementById('ar-search').value = '';
        document.getElementById('ar-bucket-filter').value = 'ALL';
        document.getElementById('ar-sort').value = 'total_desc';
        renderARTable();
    }

    function exportAgingToExcel() {
        const list = getFilteredAR();
        let csv = `\uFEFFالعميل,الهاتف,إجمالي المديونية (${AGING_CURRENCY}),شيكات معلقة,لم يحن,1-30 يوم,31-60 يوم,61-90 يوم,+90 يوم (متعثر),أقصى تأخير (يوم),احتمال التحصيل AI %\n`;
        list.forEach(c => {
            csv += `"${c.name}","${c.phone}","${c.total}","${c.pendingChecks}","${c.notDue}","${c.days30}","${c.days60}","${c.days90}","${c.over90}","${c.maxDaysOverdue}","${c.aiRecoveryProb}%"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `Elsajana_Debt_Aging_${AGING_CURRENCY}_${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
    }

    // Run immediately on DOMContentLoaded AND refresh on DBReady
    document.addEventListener('DOMContentLoaded', () => {
        recalculateAllAging();
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        if (tab && ['ar', 'ap', 'credit', 'ai'].includes(tab)) {
            switchAgingTab(tab);
        } else if (tab === 'credit_limits') {
            switchAgingTab('credit');
        }
        if (params.get('search')) {
            document.getElementById('ar-search').value = params.get('search');
            renderARTable();
        }
    });

    document.addEventListener('DBReady', () => {
        recalculateAllAging();
    });
</script>

<?php include 'includes/footer.php'; ?>
