<?php
$page_title = 'محفظة العملاء والموردين وبطاقة الموقف المالي والولاء الذكي 360° — Partner Portfolio Hub';
$page_css = 'dashboard.css';
$LoadChartJS = true;

// ============================================================================
// محرك البيانات الهجين (MySQL + LocalStorage) لمحفظة العملاء والموردين والولاء
// ============================================================================
$dbCustomers = [];
$dbSuppliers = [];
$dbSalesInvoices = [];
$dbPurchaseInvoices = [];
$dbChecks = [];
$dbVouchers = [];

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
if (file_exists(__DIR__ . '/includes/sidebar.php')) include __DIR__ . '/includes/sidebar.php';
?>
<link rel="stylesheet" href="css/unified-design-tokens.css">

<style>
    .pf-master-wrap {
        padding: 18px 22px 50px 22px;
        max-width: 1680px;
        margin: 0 auto;
        direction: rtl;
        font-family: 'Tajawal', 'Cairo', sans-serif;
    }

    /* Hero Banner */
    .pf-hero {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92));
        border: 1.5px solid rgba(16, 185, 129, 0.35);
        border-right: 6px solid #10b981;
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

    .pf-hero-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .pf-hero-ico {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.24), rgba(56, 189, 248, 0.22));
        border: 1.5px solid rgba(16, 185, 129, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        box-shadow: 0 0 22px rgba(16, 185, 129, 0.25);
    }

    .pf-hero-text h1 {
        margin: 0;
        font-size: 1.42rem;
        font-weight: 900;
        color: #f8fafc;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pf-hero-text p {
        margin: 5px 0 0 0;
        font-size: 0.86rem;
        color: #94a3b8;
    }

    .pf-actions-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .pf-btn {
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

    .pf-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
    }

    .pf-btn-emerald { background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.22)); border-color: rgba(16, 185, 129, 0.5); color: #34d399; }
    .pf-btn-sky { background: linear-gradient(135deg, rgba(56, 189, 248, 0.25), rgba(37, 99, 235, 0.22)); border-color: rgba(56, 189, 248, 0.5); color: #38bdf8; }
    .pf-btn-amber { background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.22)); border-color: rgba(245, 158, 11, 0.5); color: #fbbf24; }
    .pf-btn-purple { background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(126, 34, 206, 0.22)); border-color: rgba(168, 85, 247, 0.5); color: #c084fc; }
    .pf-btn-rose { background: linear-gradient(135deg, rgba(244, 63, 94, 0.25), rgba(225, 29, 72, 0.22)); border-color: rgba(244, 63, 94, 0.5); color: #fda4af; }

    /* 6 KPI Cards */
    .pf-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .pf-kpi-card {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 16px;
        padding: 15px 18px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pf-kpi-card:hover {
        transform: translateY(-3px);
        border-color: rgba(16, 185, 129, 0.45);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
    }

    .pf-kpi-lbl { font-size: 0.78rem; font-weight: 800; color: #94a3b8; }
    .pf-kpi-val { font-size: 1.5rem; font-weight: 900; color: #f8fafc; margin-top: 4px; }
    .pf-kpi-sub { font-size: 0.72rem; font-weight: 700; margin-top: 5px; }

    /* 4 Unified Tabs */
    .pf-tabs-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        background: rgba(15, 23, 42, 0.72);
        padding: 8px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .pf-tab-btn {
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

    .pf-tab-btn.active {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(56, 189, 248, 0.18));
        color: #fff;
        border-color: rgba(16, 185, 129, 0.5);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.18);
    }

    .pf-tab-panel { display: none; }
    .pf-tab-panel.active { display: block; }

    /* Filter Bar */
    .pf-filter-bar {
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
        .pf-filter-bar { grid-template-columns: 1fr 1fr; }
    }

    .pf-field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 800;
        color: #94a3b8;
        margin-bottom: 5px;
    }

    .pf-input, .pf-select {
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

    /* Table & Expandable Accordion */
    .pf-table-wrap {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .pf-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    .pf-table th {
        background: rgba(15, 23, 42, 0.96);
        color: #34d399;
        padding: 12px 13px;
        font-weight: 900;
        font-size: 0.8rem;
        border-bottom: 1.5px solid rgba(16, 185, 129, 0.25);
        white-space: nowrap;
        cursor: pointer;
    }

    .pf-table td {
        padding: 11px 13px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        color: #e2e8f0;
        font-size: 0.84rem;
        vertical-align: middle;
    }

    .pf-table tbody tr.data-row {
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .pf-table tbody tr.data-row:hover {
        background: rgba(16, 185, 129, 0.07);
    }

    .pf-detail-row {
        display: none;
        background: rgba(2, 6, 23, 0.65);
    }

    .pf-detail-row.open {
        display: table-row;
    }

    .pf-detail-box {
        padding: 18px;
        border-right: 4px solid #10b981;
        display: grid;
        grid-template-columns: 1.3fr 1fr 1.2fr;
        gap: 16px;
    }

    @media (max-width: 1100px) {
        .pf-detail-box { grid-template-columns: 1fr; }
    }

    .pf-mini-panel {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 14px;
    }

    .pf-badge {
        padding: 3px 9px;
        border-radius: 8px;
        font-size: 0.74rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .pf-badge-emerald { background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); }
    .pf-badge-amber { background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); }
    .pf-badge-rose { background: rgba(244, 63, 94, 0.18); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.4); }
    .pf-badge-purple { background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.4); }
    .pf-badge-sky { background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); }

    .pf-prog-bg {
        width: 100%;
        height: 7px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 4px;
    }

    .pf-prog-fill {
        height: 100%;
        border-radius: 999px;
    }

    /* Light Theme Support */
    html[data-theme="warm_cream"] .pf-hero,
    html[data-theme="ultra_white"] .pf-hero,
    html[data-theme="exec_gray"] .pf-hero,
    html[data-theme="warm_cream"] .pf-kpi-card,
    html[data-theme="ultra_white"] .pf-kpi-card,
    html[data-theme="exec_gray"] .pf-kpi-card,
    html[data-theme="warm_cream"] .pf-filter-bar,
    html[data-theme="ultra_white"] .pf-filter-bar,
    html[data-theme="exec_gray"] .pf-filter-bar,
    html[data-theme="warm_cream"] .pf-table-wrap,
    html[data-theme="ultra_white"] .pf-table-wrap,
    html[data-theme="exec_gray"] .pf-table-wrap,
    html[data-theme="warm_cream"] .pf-mini-panel,
    html[data-theme="ultra_white"] .pf-mini-panel,
    html[data-theme="exec_gray"] .pf-mini-panel {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06) !important;
    }

    html[data-theme="warm_cream"] .pf-hero-text h1,
    html[data-theme="ultra_white"] .pf-hero-text h1,
    html[data-theme="exec_gray"] .pf-hero-text h1,
    html[data-theme="warm_cream"] .pf-kpi-val,
    html[data-theme="ultra_white"] .pf-kpi-val,
    html[data-theme="exec_gray"] .pf-kpi-val,
    html[data-theme="warm_cream"] .pf-table td,
    html[data-theme="ultra_white"] .pf-table td,
    html[data-theme="exec_gray"] .pf-table td {
        color: #0f172a !important;
    }

    html[data-theme="warm_cream"] .pf-input,
    html[data-theme="ultra_white"] .pf-input,
    html[data-theme="exec_gray"] .pf-input,
    html[data-theme="warm_cream"] .pf-select,
    html[data-theme="ultra_white"] .pf-select,
    html[data-theme="exec_gray"] .pf-select {
        background: #f8fafc !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }

    @media print {
        .sidebar, .top-header, .pf-actions-bar, .pf-tabs-bar, .pf-filter-bar, .no-print {
            display: none !important;
        }
        .pf-master-wrap { padding: 0 !important; max-width: 100% !important; }
    }
</style>

<main class="main-content pf-master-wrap" id="main-content">

    <!-- Hero Header -->
    <div class="pf-hero">
        <div class="pf-hero-title">
            <div class="pf-hero-ico">💼</div>
            <div class="pf-hero-text">
                <h1>
                    محفظة العملاء والموردين وبطاقة الموقف المالي والولاء الذكي 360°
                    <span class="pf-badge pf-badge-emerald" id="pf-live-pill">⚡ متصل ومحدث تلقائياً</span>
                </h1>
                <p>المركز الموحد لتحليل الموقف المالي للعملاء والموردين، كفاءة التحصيل، الشيكات، مصفوفة الولاء (RFM)، ورادار منع انقطاع العملاء بالذكاء الاصطناعي</p>
            </div>
        </div>

        <div class="pf-actions-bar">
            <div style="display:inline-flex; background:rgba(15,23,42,0.9); border:1px solid rgba(255,255,255,0.15); border-radius:11px; padding:3px;">
                <button type="button" class="pf-btn" id="pf-cur-SDG" onclick="setPortfolioCurrency('SDG')" style="padding:5px 10px; border:none; background:#10b981; color:#0f172a;">SDG</button>
                <button type="button" class="pf-btn" id="pf-cur-USD" onclick="setPortfolioCurrency('USD')" style="padding:5px 10px; border:none; background:transparent;">USD ($)</button>
            </div>

            <a href="customers.php" class="pf-btn pf-btn-sky" title="إدارة بيانات العملاء والموردين">
                👥 سجل الشركاء
            </a>
            <a href="aging-report.php" class="pf-btn pf-btn-rose" title="تقرير أعمار الديون وسقوف الائتمان">
                ⏳ أعمار الديون
            </a>
            <a href="account-statement.php" class="pf-btn pf-btn-purple" title="كشف الحساب التفصيلي">
                📑 كشف الحساب
            </a>
            <button onclick="exportPortfolioExcel()" class="pf-btn pf-btn-emerald" title="تصدير المحفظة الحالية إلى ملف إكسل">
                📥 تصدير Excel
            </button>
            <button onclick="window.print()" class="pf-btn" title="طباعة بطاقة الموقف المالي">
                🖨️ طباعة
            </button>
        </div>
    </div>

    <!-- 6 Executive KPI Cards -->
    <div class="pf-kpi-grid">
        <div class="pf-kpi-card" style="border-bottom:3px solid #10b981;" onclick="switchPortfolioTab('customers')">
            <div class="pf-kpi-lbl">💰 إجمالي مبيعات المحفظة (صافي)</div>
            <div class="pf-kpi-val" id="pf-kpi-sales" style="color:#34d399;">0</div>
            <div class="pf-kpi-sub" id="pf-kpi-cust-count" style="color:#94a3b8;">0 عميل مسجل</div>
        </div>

        <div class="pf-kpi-card" style="border-bottom:3px solid #38bdf8;" onclick="switchPortfolioTab('customers')">
            <div class="pf-kpi-lbl">📥 إجمالي المبالغ المحصلة</div>
            <div class="pf-kpi-val" id="pf-kpi-paid" style="color:#38bdf8;">0</div>
            <div class="pf-kpi-sub" id="pf-kpi-coll-rate" style="color:#38bdf8;">نسبة التحصيل: 0%</div>
        </div>

        <div class="pf-kpi-card" style="border-bottom:3px solid #f59e0b;" onclick="quickStatusFilter('HAS_BALANCE')">
            <div class="pf-kpi-lbl">⚠️ صافي الرصيد المستحق (لنا)</div>
            <div class="pf-kpi-val" id="pf-kpi-balance" style="color:#fbbf24;">0</div>
            <div class="pf-kpi-sub" id="pf-kpi-checks-in" style="color:#c084fc;">شيكات واردة معلقة: 0</div>
        </div>

        <div class="pf-kpi-card" style="border-bottom:3px solid #a855f7;" onclick="switchPortfolioTab('suppliers')">
            <div class="pf-kpi-lbl">🏢 إجمالي مشتريات ومستحقات الموردين</div>
            <div class="pf-kpi-val" id="pf-kpi-sup-bal" style="color:#c084fc;">0</div>
            <div class="pf-kpi-sub" id="pf-kpi-sup-count" style="color:#94a3b8;">0 مورد نشط</div>
        </div>

        <div class="pf-kpi-card" style="border-bottom:3px solid #22d3ee;" onclick="switchPortfolioTab('loyalty')">
            <div class="pf-kpi-lbl">👑 كبار العملاء والولاء (VIP Tier)</div>
            <div class="pf-kpi-val" id="pf-kpi-vip" style="color:#22d3ee;">0</div>
            <div class="pf-kpi-sub" style="color:#22d3ee;">أعلى قيمة دائمة (CLV)</div>
        </div>

        <div class="pf-kpi-card" style="border-bottom:3px solid #f43f5e;" onclick="switchPortfolioTab('churn')">
            <div class="pf-kpi-lbl">🚨 عملاء معرضون للانقطاع (Churn Risk)</div>
            <div class="pf-kpi-val" id="pf-kpi-churn" style="color:#fb7185;">0</div>
            <div class="pf-kpi-sub" style="color:#fda4af;">انقطاع أكثر من 30 يوماً</div>
        </div>
    </div>

    <!-- 4 Unified Tabs -->
    <div class="pf-tabs-bar">
        <button class="pf-tab-btn active" id="pf-tab-btn-customers" onclick="switchPortfolioTab('customers')">
            👥 محفظة العملاء وبطاقة الموقف المالي (Customers Portfolio)
        </button>
        <button class="pf-tab-btn" id="pf-tab-btn-suppliers" onclick="switchPortfolioTab('suppliers')">
            🏢 محفظة الموردين والموقف المالي وموثوقية التوريد (Suppliers Portfolio)
        </button>
        <button class="pf-tab-btn" id="pf-tab-btn-loyalty" onclick="switchPortfolioTab('loyalty')">
            💎 مصفوفة الولاء وتكرار الزيارة وقيمة العميل (RFM & Loyalty Matrix)
        </button>
        <button class="pf-tab-btn" id="pf-tab-btn-churn" onclick="switchPortfolioTab('churn')">
            🚨 رادار انقطاع وركود العملاء واستعادتهم ذكياً (AI Churn & Win-Back)
        </button>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 1: CUSTOMERS FINANCIAL PORTFOLIO                       -->
    <!-- ========================================================== -->
    <div class="pf-tab-panel active" id="pf-panel-customers">
        <div class="pf-filter-bar">
            <div class="pf-field">
                <label>🔍 بحث باسم العميل أو رقم الهاتف أو الكود:</label>
                <input type="text" id="cust-search" class="pf-input" placeholder="ابحث عن عميل..." oninput="renderCustomersTable()">
            </div>

            <div class="pf-field">
                <label>📋 حالة الموقف المالي:</label>
                <select id="cust-status-filter" class="pf-select" onchange="renderCustomersTable()">
                    <option value="ALL">جميع العملاء</option>
                    <option value="HAS_BALANCE">⚠️ لديه رصيد مستحق</option>
                    <option value="FULLY_PAID">✅ مسدد بالكامل</option>
                    <option value="OVERDUE">🔴 متأخر السداد (+30 يوم)</option>
                    <option value="VIP">👑 كبار العملاء (VIP)</option>
                </select>
            </div>

            <div class="pf-field">
                <label>📅 من تاريخ:</label>
                <input type="date" id="cust-date-from" class="pf-input" onchange="recalculatePortfolios()">
            </div>

            <div class="pf-field">
                <label>📅 إلى تاريخ:</label>
                <input type="date" id="cust-date-to" class="pf-input" onchange="recalculatePortfolios()">
            </div>

            <div class="pf-field">
                <label>📊 الترتيب حسب:</label>
                <select id="cust-sort" class="pf-select" onchange="renderCustomersTable()">
                    <option value="balance_desc">الأعلى رصيداً متبقياً</option>
                    <option value="sales_desc">الأعلى مبيعات</option>
                    <option value="paid_desc">الأعلى تحصيلاً</option>
                    <option value="pct_desc">الأعلى التزاماً بالسداد %</option>
                    <option value="name_asc">الاسم أبجدياً</option>
                </select>
            </div>

            <div class="pf-field">
                <button onclick="resetCustomerFilters()" class="pf-btn" style="height:38px;">إعادة ضبط ✖</button>
            </div>
        </div>

        <div class="pf-table-wrap">
            <div style="padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="font-weight:900; color:#34d399; font-size:0.92rem;">
                    📋 تفصيل بطاقة الموقف المالي للعملاء (<span id="cust-showing-count">0</span> عميل — اضغط على أي صف لفتح البطاقة التحليلية 360°)
                </div>
                <div style="font-size:0.78rem; color:#94a3b8;">
                    مرتبط مباشرة مع الفواتير • المرتجعات • سندات القبض • الشيكات • كشف الحساب • أعمار الديون
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم العميل</th>
                            <th>عدد الفواتير</th>
                            <th>صافي المبيعات</th>
                            <th>المبالغ المسددة</th>
                            <th>الرصيد المتبقي</th>
                            <th>شيكات معلقة</th>
                            <th>آخر حركة</th>
                            <th>حالة السداد</th>
                            <th>كفاءة التحصيل %</th>
                            <th class="no-print">إجراءات الربط والواتساب</th>
                        </tr>
                    </thead>
                    <tbody id="cust-portfolio-tbody"></tbody>
                    <tfoot id="cust-portfolio-tfoot"></tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 2: SUPPLIERS PORTFOLIO & RELIABILITY                   -->
    <!-- ========================================================== -->
    <div class="pf-tab-panel" id="pf-panel-suppliers">
        <div class="pf-filter-bar" style="grid-template-columns: 2fr 1fr 1fr auto;">
            <div class="pf-field">
                <label>🔍 بحث باسم المورد أو رقم الهاتف:</label>
                <input type="text" id="sup-search" class="pf-input" placeholder="ابحث عن مورد..." oninput="renderSuppliersTable()">
            </div>
            <div class="pf-field">
                <label>حالة المستحقات:</label>
                <select id="sup-status-filter" class="pf-select" onchange="renderSuppliersTable()">
                    <option value="ALL">كل الموردين</option>
                    <option value="HAS_PAYABLE">له رصيد مستحق علينا</option>
                    <option value="SETTLED">مسدد بالكامل</option>
                </select>
            </div>
            <div class="pf-field">
                <label>الترتيب:</label>
                <select id="sup-sort" class="pf-select" onchange="renderSuppliersTable()">
                    <option value="balance_desc">الأعلى التزاماً أولاً</option>
                    <option value="purchases_desc">الأعلى حجم توريد</option>
                    <option value="reliability_desc">الأعلى موثوقية واستقراراً</option>
                </select>
            </div>
            <div class="pf-field">
                <a href="purchases.php" class="pf-btn pf-btn-purple" style="height:38px;">📦 فاتورة مشتريات جديدة</a>
            </div>
        </div>

        <div class="pf-table-wrap">
            <div style="padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:900; color:#c084fc; font-size:0.92rem;">
                    🏢 بطاقة الموقف المالي للموردين ومؤشر موثوقية التوريد (<span id="sup-showing-count">0</span> مورد)
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المورد</th>
                            <th>الهاتف</th>
                            <th>عدد الطلبيات</th>
                            <th>إجمالي التوريد</th>
                            <th>المسدد للمورد</th>
                            <th>المستحق له (علينا)</th>
                            <th>شيكات صادرة</th>
                            <th>مؤشر الموثوقية</th>
                            <th class="no-print">إجراءات السداد والربط</th>
                        </tr>
                    </thead>
                    <tbody id="sup-portfolio-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 3: RFM & LOYALTY MATRIX (مصفوفة الولاء وتكرار الزيارة)  -->
    <!-- ========================================================== -->
    <div class="pf-tab-panel" id="pf-panel-loyalty">
        <div class="pf-table-wrap">
            <div style="padding:14px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="font-weight:900; color:#22d3ee; font-size:0.95rem;">
                    💎 مصفوفة الولاء وتكرار الزيارة والقيمة الدائمة للعميل (RFM & Customer Lifetime Value)
                </div>
                <div style="font-size:0.78rem; color:#94a3b8;">
                    تصنيف علمي آلي بناءً على: حداثة آخر شراء (Recency) • تكرار الفواتير (Frequency) • حجم التعامل المالي (Monetary)
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>المرتبة</th>
                            <th>العميل</th>
                            <th>شريحة الولاء (Tier)</th>
                            <th>عدد الزيارات / الفواتير</th>
                            <th>إجمالي المسحوبات (CLV)</th>
                            <th>متوسط قيمة الفاتورة</th>
                            <th>نسبة الالتزام بالسداد</th>
                            <th>نقاط الولاء الذكية</th>
                            <th class="no-print">مكافأة وتواصل</th>
                        </tr>
                    </thead>
                    <tbody id="loyalty-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TAB 4: AI CHURN & WIN-BACK RADAR (رادار انقطاع العملاء)     -->
    <!-- ========================================================== -->
    <div class="pf-tab-panel" id="pf-panel-churn">
        <div class="pf-table-wrap">
            <div style="padding:14px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="font-weight:900; color:#fb7185; font-size:0.95rem;">
                    🚨 رادار رصد انقطاع وركود الزبائن وخطة الاستعادة الذكية (AI Churn Risk & Win-Back Engine)
                </div>
                <div style="font-size:0.78rem; color:#94a3b8;">
                    يكتشف العملاء الذين توقفوا عن الشراء لفترة طويلة مع توليد رسالة استعادة وعرض خاص عبر الواتساب بضغطة زر
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الهاتف</th>
                            <th>تاريخ آخر فاتورة</th>
                            <th>فترة الانقطاع (بالأيام)</th>
                            <th>مستوى خطر الانقطاع</th>
                            <th>حجم المسحوبات التاريخي</th>
                            <th>الرصيد الحالي</th>
                            <th>توصية الذكاء الاصطناعي للاستعادة</th>
                            <th class="no-print">حملة استعادة فورية</th>
                        </tr>
                    </thead>
                    <tbody id="churn-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<script src="js/localforage.min.js"></script>
<script src="js/permissions-manager.js"></script>
<script src="js/app.js"></script>

<script>
    const SERVER_PF_DATA = {
        customers: <?php echo json_encode($dbCustomers, JSON_UNESCAPED_UNICODE); ?>,
        suppliers: <?php echo json_encode($dbSuppliers, JSON_UNESCAPED_UNICODE); ?>,
        salesInvoices: <?php echo json_encode($dbSalesInvoices, JSON_UNESCAPED_UNICODE); ?>,
        purchaseInvoices: <?php echo json_encode($dbPurchaseInvoices, JSON_UNESCAPED_UNICODE); ?>,
        checks: <?php echo json_encode($dbChecks, JSON_UNESCAPED_UNICODE); ?>
    };

    let PF_CURRENCY = 'SDG';
    const PF_EXCHANGE_RATE = Number(localStorage.getItem('erp_exchange_rate') || 600) || 600;
    let CUSTOMERS_PORTFOLIO = [];
    let SUPPLIERS_PORTFOLIO = [];

    function fmtPF(val) {
        const num = Number(val || 0);
        const conv = PF_CURRENCY === 'USD' ? (num / PF_EXCHANGE_RATE) : num;
        return conv.toLocaleString('en-US', { maximumFractionDigits: PF_CURRENCY === 'USD' ? 2 : 0 });
    }

    function setPortfolioCurrency(cur) {
        PF_CURRENCY = cur;
        document.getElementById('pf-cur-SDG').style.background = cur === 'SDG' ? '#10b981' : 'transparent';
        document.getElementById('pf-cur-SDG').style.color = cur === 'SDG' ? '#0f172a' : '#f8fafc';
        document.getElementById('pf-cur-USD').style.background = cur === 'USD' ? '#10b981' : 'transparent';
        document.getElementById('pf-cur-USD').style.color = cur === 'USD' ? '#0f172a' : '#f8fafc';
        renderAllPortfolioViews();
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

    function recalculatePortfolios() {
        const dFrom = document.getElementById('cust-date-from')?.value || '';
        const dTo = document.getElementById('cust-date-to')?.value || '';
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const customers = mergeByKey(SERVER_PF_DATA.customers, [...safeJSON('erp_customers_v1'), ...safeJSON('erp_partners_v1').filter(p => (p.type || '').toLowerCase().includes('cust') || p.type === 'عميل')], 'id', 'name');
        const suppliers = mergeByKey(SERVER_PF_DATA.suppliers, [...safeJSON('erp_suppliers_v1'), ...safeJSON('erp_partners_v1').filter(p => (p.type || '').toLowerCase().includes('sup') || p.type === 'مورد')], 'id', 'name');
        const salesInvoices = mergeByKey(SERVER_PF_DATA.salesInvoices, safeJSON('erp_sales_invoices_v1'), 'invoiceNo', 'id');
        const purchaseInvoices = mergeByKey(SERVER_PF_DATA.purchaseInvoices, safeJSON('erp_purchase_invoices_v1'), 'invoiceNo', 'id');
        const checks = mergeByKey(SERVER_PF_DATA.checks, safeJSON('erp_checks_v1'), 'checkNo', 'id');

        const singleReceipts = safeJSON('erp_single_receipts_v1');
        const singlePayments = safeJSON('erp_single_payments_v1');
        const multiReceipts = safeJSON('erp_receipts_v1');
        const multiPayments = safeJSON('erp_payments_v1');

        // ====================================================
        // 1. BUILD CUSTOMERS PORTFOLIO
        // ====================================================
        const custMap = new Map();
        const ensureCust = (id, name, phone = '', limit = 0, openBal = 0) => {
            const clean = String(name || 'عميل عام').trim();
            const key = clean.toLowerCase();
            if (!custMap.has(key)) {
                custMap.set(key, {
                    id: id || clean,
                    name: clean,
                    phone: phone || '-',
                    creditLimit: Number(limit || 0),
                    openingBalance: Number(openBal || 0),
                    invoiceCount: 0,
                    totalSales: 0,
                    totalReturns: 0,
                    totalPaid: 0,
                    balance: 0,
                    pendingChecks: 0,
                    clearedChecks: 0,
                    bouncedChecks: 0,
                    lastTxnDate: '',
                    daysSinceLast: 999,
                    recentTxns: [],
                    checksList: []
                });
            } else {
                const c = custMap.get(key);
                if (phone && (c.phone === '-' || !c.phone)) c.phone = phone;
                if (limit > c.creditLimit) c.creditLimit = Number(limit);
                if (Math.abs(openBal) > Math.abs(c.openingBalance)) c.openingBalance = Number(openBal);
            }
            return custMap.get(key);
        };

        customers.forEach(c => {
            const bal = parseFloat(c.currentBalance ?? c.balance ?? c.openingBalance ?? 0) || 0;
            const lim = parseFloat(c.creditLimit ?? c.limit ?? 0) || 0;
            ensureCust(c.id || c._db_id, c.name, c.phone || c.mobile, lim, bal);
        });

        salesInvoices.forEach(inv => {
            if (inv.deleted || inv.is_deleted || inv.wasSplit) return;
            const invDate = (inv.date || '').slice(0, 10);
            if (dFrom && invDate && invDate < dFrom) return;
            if (dTo && invDate && invDate > dTo) return;

            const custName = inv.customerName || inv.clientName || inv.customer || 'عميل نقدي';
            const c = ensureCust(inv.customerId || inv.customer, custName, inv.customerPhone);
            const total = parseFloat(inv.grandTotal ?? inv.netTotal ?? inv.total ?? inv.totalAmount ?? 0) || 0;
            const paid = parseFloat(inv.paidAmount ?? (inv.paymentMethod === 'cash' || inv.paymentType === 'cash' ? total : 0)) || 0;
            const isReturn = (inv.type === 'return' || inv.isReturn);

            if (isReturn) {
                c.totalReturns += total;
            } else {
                c.invoiceCount++;
                c.totalSales += total;
                c.totalPaid += paid;
            }

            if (invDate && (!c.lastTxnDate || invDate > c.lastTxnDate)) {
                c.lastTxnDate = invDate;
            }

            c.recentTxns.push({
                type: isReturn ? 'return' : 'sale',
                docNo: inv.invoiceNo || inv.id || 'INV',
                date: invDate,
                amount: total,
                description: isReturn ? `مرتجع مبيعات #${inv.invoiceNo || inv.id}` : `فاتورة مبيعات #${inv.invoiceNo || inv.id}`,
                url: isReturn ? `sales-return.php?id=${encodeURIComponent(inv.invoiceNo || inv.id || '')}` : `sales.php?invoice=${encodeURIComponent(inv.invoiceNo || inv.id || '')}`
            });
        });

        // Standalone Receipts
        const addCustReceipt = (party, amt, dateStr, docNo, desc) => {
            if (!party || amt <= 0) return;
            const dStr = (dateStr || '').slice(0, 10);
            if (dFrom && dStr && dStr < dFrom) return;
            if (dTo && dStr && dStr > dTo) return;
            const c = ensureCust('', party);
            c.totalPaid += amt;
            if (dStr && (!c.lastTxnDate || dStr > c.lastTxnDate)) c.lastTxnDate = dStr;
            c.recentTxns.push({
                type: 'receipt',
                docNo: docNo || 'REC',
                date: dStr,
                amount: amt,
                description: desc || `سند قبض #${docNo || ''}`,
                url: `single-cash-payment.php?mode=receipt&voucher=${encodeURIComponent(docNo || '')}`
            });
        };

        singleReceipts.forEach(r => addCustReceipt(r.accountName || r.customerName || r.partyName, parseFloat(r.amount || r.totalAmount || 0), r.date, r.voucherNo || r.id, r.description));
        singlePayments.filter(p => p.voucherType === 'receipt').forEach(r => addCustReceipt(r.accountName || r.customerName || r.partyName, parseFloat(r.amount || r.totalAmount || 0), r.date, r.voucherNo || r.id, r.description));
        multiReceipts.forEach(r => {
            if (Array.isArray(r.items)) {
                r.items.forEach(it => addCustReceipt(it.accountName || it.partyName, parseFloat(it.amount || 0), r.date, r.voucherNo || r.id, it.description || r.description));
            }
        });

        // Checks
        checks.forEach(chk => {
            const amt = parseFloat(chk.amount || 0) || 0;
            if (amt <= 0) return;
            const party = chk.drawerName || chk.customerName || chk.partner || chk.beneficiary || '';
            if (!party) return;
            const isOut = (chk.type || '').toUpperCase() === 'OUTBOUND' || (chk.type || '').toLowerCase() === 'outgoing';
            if (!isOut) {
                const c = ensureCust('', party);
                const st = (chk.status || 'PENDING').toUpperCase();
                if (st === 'CLEARED' || st === 'محصل') c.clearedChecks += amt;
                else if (st === 'BOUNCED' || st === 'مرتجع') c.bouncedChecks += amt;
                else c.pendingChecks += amt;
                c.checksList.push({
                    no: chk.checkNo || chk.id || 'CHK',
                    bank: chk.bankName || chk.bank || 'بنك',
                    amount: amt,
                    dueDate: chk.dueDate || chk.date || '—',
                    status: st
                });
            }
        });

        custMap.forEach(c => {
            const netSales = Math.max(0, c.totalSales - c.totalReturns);
            c.netSales = netSales;
            if (netSales === 0 && c.openingBalance > 0) {
                c.netSales = c.openingBalance;
                c.balance = Math.max(0, c.openingBalance - c.totalPaid);
            } else {
                c.balance = Math.max(0, netSales - c.totalPaid);
                if (c.balance === 0 && c.openingBalance > 0 && c.invoiceCount === 0) {
                    c.balance = c.openingBalance;
                }
            }

            c.collectionPct = c.netSales > 0 ? Math.min(100, Math.round((c.totalPaid / c.netSales) * 100)) : (c.balance === 0 ? 100 : 0);

            if (c.lastTxnDate) {
                const dt = new Date(c.lastTxnDate);
                if (!isNaN(dt.getTime())) {
                    c.daysSinceLast = Math.max(0, Math.ceil((today - dt) / (1000 * 60 * 60 * 24)));
                }
            }

            if (c.balance <= 0 && c.netSales > 0) c.status = 'FULLY_PAID';
            else if (c.balance > 0 && c.daysSinceLast > 30) c.status = 'OVERDUE';
            else if (c.balance > 0) c.status = 'HAS_BALANCE';
            else c.status = 'NONE';

            // RFM & Loyalty Tier
            if (c.invoiceCount >= 10 || c.netSales >= 5000000) c.tier = 'VIP';
            else if (c.invoiceCount >= 3 || c.netSales >= 1000000) c.tier = 'REGULAR';
            else c.tier = 'OCCASIONAL';

            c.recentTxns.sort((a, b) => (b.date || '').localeCompare(a.date || ''));
        });

        CUSTOMERS_PORTFOLIO = Array.from(custMap.values()).filter(c => c.netSales > 0 || c.balance > 0 || c.totalPaid > 0 || c.pendingChecks > 0 || c.name !== 'عميل عام');

        // ====================================================
        // 2. BUILD SUPPLIERS PORTFOLIO
        // ====================================================
        const supMap = new Map();
        const ensureSup = (id, name, phone = '', openBal = 0) => {
            const clean = String(name || 'مورد عام').trim();
            const key = clean.toLowerCase();
            if (!supMap.has(key)) {
                supMap.set(key, {
                    id: id || clean,
                    name: clean,
                    phone: phone || '-',
                    openingBalance: Math.abs(Number(openBal || 0)),
                    orderCount: 0,
                    totalPurchases: 0,
                    totalPaid: 0,
                    balance: 0,
                    pendingChecks: 0,
                    reliabilityScore: 92
                });
            }
            return supMap.get(key);
        };

        suppliers.forEach(s => {
            ensureSup(s.id || s._db_id, s.name, s.phone || s.mobile, s.currentBalance ?? s.balance ?? 0);
        });

        purchaseInvoices.forEach(inv => {
            if (inv.deleted || inv.is_deleted) return;
            const supName = inv.supplierName || inv.vendorName || inv.supplier || 'مورد عام';
            const s = ensureSup(inv.supplierId || inv.supplier, supName, inv.supplierPhone);
            const total = parseFloat(inv.grandTotal ?? inv.netTotal ?? inv.total ?? inv.totalAmount ?? 0) || 0;
            const paid = parseFloat(inv.paidAmount ?? 0) || 0;
            s.orderCount++;
            s.totalPurchases += total;
            s.totalPaid += paid;
        });

        singlePayments.filter(p => p.voucherType !== 'receipt').forEach(p => {
            const name = p.accountName || p.partyName || p.beneficiary || '';
            if (name && supMap.has(name.trim().toLowerCase())) {
                supMap.get(name.trim().toLowerCase()).totalPaid += parseFloat(p.amount || p.totalAmount || 0) || 0;
            }
        });

        checks.forEach(chk => {
            if ((chk.type || '').toUpperCase() === 'OUTBOUND') {
                const s = ensureSup('', chk.beneficiary || chk.supplierName || 'مورد');
                s.pendingChecks += parseFloat(chk.amount || 0) || 0;
            }
        });

        supMap.forEach(s => {
            if (s.totalPurchases === 0 && s.openingBalance > 0) {
                s.totalPurchases = s.openingBalance;
            }
            s.balance = Math.max(0, s.totalPurchases - s.totalPaid);
            s.reliabilityScore = Math.min(99, 80 + Math.min(19, s.orderCount * 2));
        });

        SUPPLIERS_PORTFOLIO = Array.from(supMap.values()).filter(s => s.totalPurchases > 0 || s.balance > 0 || s.name !== 'مورد عام');

        renderAllPortfolioViews();
    }

    function renderAllPortfolioViews() {
        updatePortfolioKPIs();
        renderCustomersTable();
        renderSuppliersTable();
        renderLoyaltyTab();
        renderChurnTab();
    }

    function updatePortfolioKPIs() {
        const tSales = CUSTOMERS_PORTFOLIO.reduce((s, c) => s + c.netSales, 0);
        const tPaid = CUSTOMERS_PORTFOLIO.reduce((s, c) => s + c.totalPaid, 0);
        const tBal = CUSTOMERS_PORTFOLIO.reduce((s, c) => s + c.balance, 0);
        const tChecks = CUSTOMERS_PORTFOLIO.reduce((s, c) => s + c.pendingChecks, 0);
        const collPct = tSales > 0 ? Math.round((tPaid / tSales) * 100) : 0;

        const tSupBal = SUPPLIERS_PORTFOLIO.reduce((s, x) => s + x.balance, 0);
        const vipCount = CUSTOMERS_PORTFOLIO.filter(c => c.tier === 'VIP').length;
        const churnCount = CUSTOMERS_PORTFOLIO.filter(c => c.daysSinceLast > 30 && c.invoiceCount > 0).length;

        document.getElementById('pf-kpi-sales').textContent = `${fmtPF(tSales)} ${PF_CURRENCY}`;
        document.getElementById('pf-kpi-cust-count').textContent = `${CUSTOMERS_PORTFOLIO.length} عميل في المحفظة`;
        document.getElementById('pf-kpi-paid').textContent = `${fmtPF(tPaid)} ${PF_CURRENCY}`;
        document.getElementById('pf-kpi-coll-rate').textContent = `كفاءة التحصيل العامة: ${collPct}%`;
        document.getElementById('pf-kpi-balance').textContent = `${fmtPF(tBal)} ${PF_CURRENCY}`;
        document.getElementById('pf-kpi-checks-in').textContent = `شيكات واردة معلقة: ${fmtPF(tChecks)} ${PF_CURRENCY}`;
        document.getElementById('pf-kpi-sup-bal').textContent = `${fmtPF(tSupBal)} ${PF_CURRENCY}`;
        document.getElementById('pf-kpi-sup-count').textContent = `${SUPPLIERS_PORTFOLIO.length} مورد مسجل`;
        document.getElementById('pf-kpi-vip').textContent = `${vipCount} عميل VIP`;
        document.getElementById('pf-kpi-churn').textContent = `${churnCount} عميل منقطع`;
    }

    function getFilteredCustomers() {
        const q = (document.getElementById('cust-search')?.value || '').trim().toLowerCase();
        const st = document.getElementById('cust-status-filter')?.value || 'ALL';
        const sort = document.getElementById('cust-sort')?.value || 'balance_desc';

        let list = CUSTOMERS_PORTFOLIO.filter(c => {
            if (q && !(`${c.name} ${c.phone} ${c.id}`).toLowerCase().includes(q)) return false;
            if (st === 'HAS_BALANCE' && c.balance <= 0) return false;
            if (st === 'FULLY_PAID' && !(c.balance <= 0 && c.netSales > 0)) return false;
            if (st === 'OVERDUE' && !(c.balance > 0 && c.daysSinceLast > 30)) return false;
            if (st === 'VIP' && c.tier !== 'VIP') return false;
            return true;
        });

        list.sort((a, b) => {
            if (sort === 'sales_desc') return b.netSales - a.netSales;
            if (sort === 'paid_desc') return b.totalPaid - a.totalPaid;
            if (sort === 'pct_desc') return b.collectionPct - a.collectionPct;
            if (sort === 'name_asc') return a.name.localeCompare(b.name, 'ar');
            return b.balance - a.balance;
        });

        return list;
    }

    function renderCustomersTable() {
        const tbody = document.getElementById('cust-portfolio-tbody');
        const tfoot = document.getElementById('cust-portfolio-tfoot');
        const list = getFilteredCustomers();
        document.getElementById('cust-showing-count').textContent = list.length.toLocaleString();

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding:38px; color:#94a3b8;">لا توجد سجلات عملاء مطابقة للفلتر الحالي.</td></tr>`;
            tfoot.innerHTML = '';
            return;
        }

        let sumSales = 0, sumPaid = 0, sumBal = 0, sumChk = 0;

        tbody.innerHTML = list.map((c, idx) => {
            sumSales += c.netSales;
            sumPaid += c.totalPaid;
            sumBal += c.balance;
            sumChk += c.pendingChecks;

            const safeName = c.name.replace(/'/g, "\\'");
            const safePhone = String(c.phone || '').replace(/'/g, "\\'");
            const pctColor = c.collectionPct >= 80 ? '#10b981' : (c.collectionPct >= 45 ? '#f59e0b' : '#ef4444');

            let stBadge = '<span class="pf-badge pf-badge-sky">⚪ بدون حركة</span>';
            if (c.status === 'FULLY_PAID') stBadge = '<span class="pf-badge pf-badge-emerald">✅ مسدد بالكامل</span>';
            else if (c.status === 'OVERDUE') stBadge = '<span class="pf-badge pf-badge-rose">🔴 متأخر السداد</span>';
            else if (c.status === 'HAS_BALANCE') stBadge = '<span class="pf-badge pf-badge-amber">⏳ ذمة قائمة</span>';

            const tierBadge = c.tier === 'VIP' ? '<span class="pf-badge pf-badge-purple" style="margin-right:5px;">👑 VIP</span>' : '';

            return `
                <tr class="data-row" onclick="toggleCustomerAccordion(${idx})">
                    <td style="color:#64748b; font-weight:800;">${idx + 1}</td>
                    <td>
                        <div style="font-weight:900; color:#38bdf8;">👤 ${c.name} ${tierBadge}</div>
                        <div style="font-size:0.73rem; color:#94a3b8;">📞 ${c.phone}</div>
                    </td>
                    <td style="font-weight:800;">${c.invoiceCount}</td>
                    <td style="font-weight:900; color:#f8fafc;">${fmtPF(c.netSales)}</td>
                    <td style="font-weight:900; color:#34d399;">${fmtPF(c.totalPaid)}</td>
                    <td style="font-weight:900; color:${c.balance > 0 ? '#fbbf24' : '#34d399'};">${fmtPF(c.balance)}</td>
                    <td style="color:#c084fc; font-weight:800;">${c.pendingChecks > 0 ? fmtPF(c.pendingChecks) : '—'}</td>
                    <td style="font-size:0.8rem; color:#cbd5e1;">${c.lastTxnDate || '—'}</td>
                    <td>${stBadge}</td>
                    <td style="min-width:130px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:900; color:${pctColor};">
                            <span>التحصيل</span>
                            <span>${c.collectionPct}%</span>
                        </div>
                        <div class="pf-prog-bg"><div class="pf-prog-fill" style="width:${c.collectionPct}%; background:${pctColor};"></div></div>
                    </td>
                    <td class="no-print" onclick="event.stopPropagation();">
                        <div style="display:flex; gap:5px; flex-wrap:wrap;">
                            <a href="single-cash-payment.php?mode=receipt&party=${encodeURIComponent(c.name)}&amount=${Math.round(c.balance)}" class="pf-btn pf-btn-emerald" style="padding:4px 8px; font-size:0.73rem;" title="إصدار سند قبض">💵 قبض</a>
                            <a href="account-statement.php?id=${encodeURIComponent(c.id)}&name=${encodeURIComponent(c.name)}" class="pf-btn pf-btn-sky" style="padding:4px 8px; font-size:0.73rem;" title="كشف الحساب التفصيلي">📑 كشف</a>
                            <button onclick="sendWhatsAppPortfolioCard('${safeName}','${safePhone}',${c.netSales},${c.totalPaid},${c.balance},${c.pendingChecks})" class="pf-btn pf-btn-amber" style="padding:4px 8px; font-size:0.73rem;" title="إرسال بطاقة الموقف المالي عبر الواتساب">📲 واتساب</button>
                        </div>
                    </td>
                </tr>
                <tr class="pf-detail-row" id="pf-detail-${idx}">
                    <td colspan="11">
                        <div class="pf-detail-box" id="pf-detail-box-${idx}"></div>
                    </td>
                </tr>
            `;
        }).join('');

        tfoot.innerHTML = `
            <tr style="background:rgba(15,23,42,0.95); font-weight:900; border-top:2px solid #10b981;">
                <td colspan="3" style="text-align:center; color:#34d399;">📊 الإجمالي الكلي (${PF_CURRENCY})</td>
                <td style="color:#f8fafc;">${fmtPF(sumSales)}</td>
                <td style="color:#34d399;">${fmtPF(sumPaid)}</td>
                <td style="color:#fbbf24;">${fmtPF(sumBal)}</td>
                <td style="color:#c084fc;">${fmtPF(sumChk)}</td>
                <td colspan="4"></td>
            </tr>
        `;
    }

    function toggleCustomerAccordion(idx) {
        const row = document.getElementById(`pf-detail-${idx}`);
        if (!row) return;
        const isOpen = row.classList.contains('open');
        document.querySelectorAll('.pf-detail-row.open').forEach(r => r.classList.remove('open'));
        if (!isOpen) {
            row.classList.add('open');
            renderCustomerDetailBox(idx);
        }
    }

    function renderCustomerDetailBox(idx) {
        const list = getFilteredCustomers();
        const c = list[idx];
        if (!c) return;
        const box = document.getElementById(`pf-detail-box-${idx}`);

        const txnsHtml = c.recentTxns.length === 0
            ? `<div style="color:#64748b; padding:12px;">لا توجد حركات مسجلة في هذه الفترة.</div>`
            : c.recentTxns.slice(0, 6).map(t => `
                <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid rgba(255,255,255,0.06); font-size:0.8rem;">
                    <div>
                        <span>${t.type === 'sale' ? '🧾' : (t.type === 'return' ? '🔄' : '💵')}</span>
                        <a href="${t.url}" style="color:#e2e8f0; text-decoration:none; font-weight:800;">${t.description}</a>
                    </div>
                    <div style="font-weight:900; color:${t.type === 'sale' ? '#fbbf24' : '#34d399'};">${fmtPF(t.amount)}</div>
                    <div style="color:#94a3b8; font-size:0.74rem;">${t.date || '—'}</div>
                </div>
            `).join('');

        const checksHtml = c.checksList.length === 0
            ? `<div style="color:#64748b; padding:10px; font-size:0.8rem;">لا توجد شيكات مسجلة لهذا العميل.</div>`
            : c.checksList.map(chk => `
                <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid rgba(255,255,255,0.06); font-size:0.8rem;">
                    <span style="font-family:monospace; color:#38bdf8;">#${chk.no} (${chk.bank})</span>
                    <span style="font-weight:800; color:#fbbf24;">${fmtPF(chk.amount)}</span>
                    <span style="color:#94a3b8;">${chk.dueDate}</span>
                </div>
            `).join('');

        box.innerHTML = `
            <div class="pf-mini-panel">
                <h4 style="margin:0 0 10px 0; color:#38bdf8; font-size:0.9rem;">📜 آخر الحركات والفواتير والسندات</h4>
                ${txnsHtml}
            </div>

            <div class="pf-mini-panel">
                <h4 style="margin:0 0 10px 0; color:#c084fc; font-size:0.9rem;">🏦 حافظة الشيكات والضمانات الائتمانية</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px; font-size:0.78rem;">
                    <div style="background:rgba(255,255,255,0.03); padding:7px; border-radius:8px;">السقف الائتماني: <strong style="color:#38bdf8;">${c.creditLimit > 0 ? fmtPF(c.creditLimit) : 'مفتوح'}</strong></div>
                    <div style="background:rgba(255,255,255,0.03); padding:7px; border-radius:8px;">مرتجعات المبيعات: <strong style="color:#fda4af;">${fmtPF(c.totalReturns)}</strong></div>
                </div>
                ${checksHtml}
            </div>

            <div class="pf-mini-panel" style="display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <h4 style="margin:0 0 10px 0; color:#34d399; font-size:0.9rem;">⚡ بوابة الإجراءات السريعة للعميل (${c.name})</h4>
                    <p style="font-size:0.78rem; color:#94a3b8; margin-top:0;">انتقل مباشرة لأي عملية محاسبية أو رقابية تخص هذا العميل:</p>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <a href="sales.php?customer=${encodeURIComponent(c.name)}" class="pf-btn pf-btn-emerald" style="justify-content:center; font-size:0.76rem;">🧾 فاتورة بيع جديدة</a>
                    <a href="single-cash-payment.php?mode=receipt&party=${encodeURIComponent(c.name)}&amount=${Math.round(c.balance)}" class="pf-btn pf-btn-amber" style="justify-content:center; font-size:0.76rem;">💵 سند قبض فوري</a>
                    <a href="account-statement.php?id=${encodeURIComponent(c.id)}&name=${encodeURIComponent(c.name)}" class="pf-btn pf-btn-sky" style="justify-content:center; font-size:0.76rem;">📑 كشف الحساب</a>
                    <a href="aging-report.php?search=${encodeURIComponent(c.name)}" class="pf-btn pf-btn-rose" style="justify-content:center; font-size:0.76rem;">⏳ أعمار ديونه</a>
                </div>
            </div>
        `;
    }

    function renderSuppliersTable() {
        const tbody = document.getElementById('sup-portfolio-tbody');
        const q = (document.getElementById('sup-search')?.value || '').trim().toLowerCase();
        const st = document.getElementById('sup-status-filter')?.value || 'ALL';
        const sort = document.getElementById('sup-sort')?.value || 'balance_desc';

        let list = SUPPLIERS_PORTFOLIO.filter(s => {
            if (q && !(`${s.name} ${s.phone}`).toLowerCase().includes(q)) return false;
            if (st === 'HAS_PAYABLE' && s.balance <= 0) return false;
            if (st === 'SETTLED' && s.balance > 0) return false;
            return true;
        });

        list.sort((a, b) => {
            if (sort === 'purchases_desc') return b.totalPurchases - a.totalPurchases;
            if (sort === 'reliability_desc') return b.reliabilityScore - a.reliabilityScore;
            return b.balance - a.balance;
        });

        document.getElementById('sup-showing-count').textContent = list.length.toLocaleString();

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:34px; color:#94a3b8;">لا توجد بيانات موردين مطابقة.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map((s, idx) => `
            <tr>
                <td style="color:#64748b; font-weight:800;">${idx + 1}</td>
                <td style="font-weight:900; color:#c084fc;">🏢 ${s.name}</td>
                <td style="font-family:monospace;">${s.phone}</td>
                <td style="font-weight:800;">${s.orderCount}</td>
                <td style="font-weight:900; color:#f8fafc;">${fmtPF(s.totalPurchases)}</td>
                <td style="font-weight:800; color:#34d399;">${fmtPF(s.totalPaid)}</td>
                <td style="font-weight:900; color:${s.balance > 0 ? '#fbbf24' : '#34d399'};">${fmtPF(s.balance)}</td>
                <td style="color:#38bdf8;">${s.pendingChecks > 0 ? fmtPF(s.pendingChecks) : '—'}</td>
                <td><span class="pf-badge pf-badge-emerald">⭐ ${s.reliabilityScore}% موثوق</span></td>
                <td class="no-print">
                    <div style="display:flex; gap:5px;">
                        <a href="single-cash-payment.php?mode=payment&party=${encodeURIComponent(s.name)}&amount=${Math.round(s.balance)}" class="pf-btn pf-btn-amber" style="padding:4px 9px; font-size:0.74rem;">💸 صرف</a>
                        <a href="account-statement.php?name=${encodeURIComponent(s.name)}" class="pf-btn pf-btn-sky" style="padding:4px 9px; font-size:0.74rem;">📑 كشف</a>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderLoyaltyTab() {
        const tbody = document.getElementById('loyalty-tbody');
        const sorted = [...CUSTOMERS_PORTFOLIO].sort((a, b) => b.netSales - a.netSales);

        if (sorted.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:34px; color:#94a3b8;">لا توجد بيانات ولاء عملاء حالياً.</td></tr>`;
            return;
        }

        tbody.innerHTML = sorted.map((c, idx) => {
            const avgBasket = c.invoiceCount > 0 ? (c.netSales / c.invoiceCount) : c.netSales;
            const points = Math.round((c.netSales / 10000) + (c.invoiceCount * 15) + (c.collectionPct * 2));
            const tierBadge = c.tier === 'VIP'
                ? '<span class="pf-badge pf-badge-purple">👑 VIP البلاتينية</span>'
                : (c.tier === 'REGULAR' ? '<span class="pf-badge pf-badge-emerald">🌟 الذهبية المنتظمة</span>' : '<span class="pf-badge pf-badge-sky">🌱 الواعدة</span>');
            const safeName = c.name.replace(/'/g, "\\'");
            const safePhone = String(c.phone || '').replace(/'/g, "\\'");

            return `
                <tr>
                    <td style="font-weight:900; color:#fbbf24;">#${idx + 1}</td>
                    <td style="font-weight:900; color:#38bdf8;">👤 ${c.name}</td>
                    <td>${tierBadge}</td>
                    <td style="font-weight:800;">${c.invoiceCount} زيارة</td>
                    <td style="font-weight:900; color:#34d399;">${fmtPF(c.netSales)} ${PF_CURRENCY}</td>
                    <td style="color:#cbd5e1;">${fmtPF(avgBasket)} ${PF_CURRENCY}</td>
                    <td><span class="pf-badge ${c.collectionPct >= 75 ? 'pf-badge-emerald' : 'pf-badge-amber'}">${c.collectionPct}%</span></td>
                    <td style="font-weight:900; color:#c084fc;">💎 ${points.toLocaleString()} نقطة</td>
                    <td class="no-print">
                        <button onclick="sendLoyaltyWhatsApp('${safeName}','${safePhone}','${c.tier}',${points})" class="pf-btn pf-btn-purple" style="padding:4px 10px; font-size:0.74rem;">🎁 تهنئة وعرض VIP</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderChurnTab() {
        const tbody = document.getElementById('churn-tbody');
        const list = [...CUSTOMERS_PORTFOLIO].sort((a, b) => b.daysSinceLast - a.daysSinceLast);

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:34px; color:#94a3b8;">لا توجد بيانات انقطاع عملاء حالياً.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map(c => {
            const days = c.daysSinceLast === 999 ? 45 : c.daysSinceLast;
            const riskBadge = days > 45
                ? '<span class="pf-badge pf-badge-rose">🚨 خطر مرتفع (منقطع)</span>'
                : (days >= 15 ? '<span class="pf-badge pf-badge-amber">⚠️ خطر متوسط (بداية فتور)</span>' : '<span class="pf-badge pf-badge-emerald">🟢 نشط ومتفاعل</span>');
            const aiRec = days > 45
                ? 'اتصال مباشر + عرض خصم تشجيعي 3% على الطلبية القادمة'
                : (days >= 15 ? 'إرسال قائمة الأسعار الجديدة والمنتجات المتوفرة' : 'عميل نشط — حافظ على جودة الخدمة');
            const safeName = c.name.replace(/'/g, "\\'");
            const safePhone = String(c.phone || '').replace(/'/g, "\\'");

            return `
                <tr>
                    <td style="font-weight:900; color:#38bdf8;">👤 ${c.name}</td>
                    <td style="font-family:monospace;">${c.phone}</td>
                    <td>${c.lastTxnDate || 'غير محدد'}</td>
                    <td style="font-weight:900; color:${days > 30 ? '#fb7185' : '#34d399'};">${days} يوماً</td>
                    <td>${riskBadge}</td>
                    <td style="font-weight:800;">${fmtPF(c.netSales)} ${PF_CURRENCY}</td>
                    <td style="color:#fbbf24; font-weight:800;">${fmtPF(c.balance)} ${PF_CURRENCY}</td>
                    <td style="font-size:0.8rem; color:#cbd5e1;">💡 ${aiRec}</td>
                    <td class="no-print">
                        <button onclick="sendWinBackWhatsApp('${safeName}','${safePhone}',${days})" class="pf-btn pf-btn-emerald" style="padding:4px 10px; font-size:0.74rem;">📲 رسالة استعادة العميل</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function sendWhatsAppPortfolioCard(name, phone, sales, paid, bal, checks) {
        const cleanPhone = String(phone || '').replace(/[^0-9]/g, '');
        const msg = `📊 *بطاقة الموقف المالي وكشف الرصيد المختصر*\n`
            + `━━━━━━━━━━━━━━━━━━━━\n`
            + `عميلنا العزيز / *${name}* المحترم،\n`
            + `يسعدنا موافاتكم بملخص موقفكم المالي لدينا حتى تاريخ اليوم:\n`
            + `• إجمالي المسحوبات الصافية: *${fmtPF(sales)} ${PF_CURRENCY}*\n`
            + `• إجمالي الدفعات المسددة: *${fmtPF(paid)} ${PF_CURRENCY}*\n`
            + (checks > 0 ? `• شيكات قيد التحصيل: *${fmtPF(checks)} ${PF_CURRENCY}*\n` : '')
            + `• *صافي الرصيد المتبقي:* *${fmtPF(bal)} ${PF_CURRENCY}*\n`
            + `━━━━━━━━━━━━━━━━━━━━\n`
            + `شاكرين ثقتكم الغالية وتعاملكم الدائم معنا.`;
        window.open(cleanPhone ? `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
    }

    function sendLoyaltyWhatsApp(name, phone, tier, points) {
        const cleanPhone = String(phone || '').replace(/[^0-9]/g, '');
        const msg = `👑 *شهادة تقدير ومكافأة ولاء العملاء (${tier})*\n`
            + `عميلنا المميز / *${name}* المحترم،\n`
            + `تقديراً لتعاملكم المثمر معنا، يسرنا إبلاغكم بأن رصيد نقاط الولاء الخاصة بكم بلغ *${points.toLocaleString()} نقطة* ضمن الفئة المميزة. يسعدنا تقديم أولوية التوريد وأفضل الأسعار في طلبياتكم القادمة!`;
        window.open(cleanPhone ? `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
    }

    function sendWinBackWhatsApp(name, phone, days) {
        const cleanPhone = String(phone || '').replace(/[^0-9]/g, '');
        const msg = `🌟 *تحية طيبة من إدارة المبيعات*\n`
            + `عميلنا العزيز / *${name}* المحترم،\n`
            + `افتقدنا تعاملكم معنا منذ (${days} يوماً)، ونود إعلامكم بتوفر تشكيلة جديدة من البضائع والأصناف بأسعار تنافسية خاصة لعملائنا الكرام. نسعد بخدمتكم وتلقي طلبياتكم في أي وقت!`;
        window.open(cleanPhone ? `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
    }

    function switchPortfolioTab(tab) {
        ['customers', 'suppliers', 'loyalty', 'churn'].forEach(t => {
            document.getElementById('pf-tab-btn-' + t).classList.toggle('active', t === tab);
            document.getElementById('pf-panel-' + t).classList.toggle('active', t === tab);
        });
    }

    function quickStatusFilter(st) {
        switchPortfolioTab('customers');
        document.getElementById('cust-status-filter').value = st;
        renderCustomersTable();
    }

    function resetCustomerFilters() {
        document.getElementById('cust-search').value = '';
        document.getElementById('cust-status-filter').value = 'ALL';
        document.getElementById('cust-date-from').value = '';
        document.getElementById('cust-date-to').value = '';
        document.getElementById('cust-sort').value = 'balance_desc';
        recalculatePortfolios();
    }

    function exportPortfolioExcel() {
        const list = getFilteredCustomers();
        let csv = `\uFEFFالعميل,الهاتف,الفئة,عدد الفواتير,صافي المبيعات (${PF_CURRENCY}),المبالغ المسددة,الرصيد المتبقي,شيكات معلقة,آخر حركة,نسبة التحصيل %\n`;
        list.forEach(c => {
            csv += `"${c.name}","${c.phone}","${c.tier}","${c.invoiceCount}","${c.netSales}","${c.totalPaid}","${c.balance}","${c.pendingChecks}","${c.lastTxnDate || '—'}","${c.collectionPct}%"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `Elsajana_Partner_Portfolio_${PF_CURRENCY}_${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
    }

    document.addEventListener('DOMContentLoaded', () => {
        recalculatePortfolios();
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        if (tab && ['customers', 'suppliers', 'loyalty', 'churn'].includes(tab)) {
            switchPortfolioTab(tab);
        }
        if (params.get('search')) {
            document.getElementById('cust-search').value = params.get('search');
            renderCustomersTable();
        }
    });

    document.addEventListener('DBReady', () => {
        recalculatePortfolios();
    });
</script>

<?php if (file_exists(__DIR__ . '/includes/footer.php')) include __DIR__ . '/includes/footer.php'; ?>
