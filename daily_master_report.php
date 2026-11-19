<?php
if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); }

// ====================================================================
// Al-Sajana ERP 2026 - التقرير اليومي الشامل والموسع للمؤسسة V3 (Supreme Edition)
// التقرير الرقابي الشامل للحالة المالية (كاش، تحويلات، شيكات، آجل، جملة، وأصناف — مجمع ومفصل)
// ====================================================================

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$page_title = 'التقرير اليومي الشامل وتقفيل اليومية والحالة المالية';
$header_theme_class = 'header-theme-reports';

// جسر البيانات الحي من قاعدة البيانات MySQL لضمان عمل الشاشة 100% حتى قبل مزامنة المتصفح
$db_daily_bridge = [
    'invoices' => [],
    'vouchers' => [],
    'journal'  => [],
    'checks'   => [],
    'items'    => []
];
try {
    if (file_exists(__DIR__ . '/config/db.php')) {
        require_once __DIR__ . '/config/db.php';
        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $stmtInv = $pdo->query("SELECT * FROM invoices ORDER BY id DESC LIMIT 400");
                if ($stmtInv) $db_daily_bridge['invoices'] = $stmtInv->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {}
            try {
                $stmtVch = $pdo->query("SELECT * FROM vouchers ORDER BY id DESC LIMIT 400");
                if ($stmtVch) $db_daily_bridge['vouchers'] = $stmtVch->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {}
            try {
                $stmtJv = $pdo->query("SELECT * FROM journal_entries ORDER BY id DESC LIMIT 400");
                if ($stmtJv) $db_daily_bridge['journal'] = $stmtJv->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {}
        }
    }
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقرير اليومي الشامل | Al-Sajana ERP</title>
    <script>
    (function initDmrMaster13ToneEarly() {
        try {
            var ckM = document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/);
            var savedTone = (ckM ? decodeURIComponent(ckM[1]) : '') || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || 'ivory_default';
            var darkSet = { dark_midnight: 1, pure_oled: 1, mocha_brown: 1, slate_twilight: 1 };
            var root = document.documentElement;
            root.setAttribute('data-light-tone', savedTone);
            if (darkSet[savedTone]) { root.classList.remove('light-theme'); root.classList.add('dark-theme'); root.setAttribute('data-theme', savedTone === 'dark_midnight' ? 'dark' : savedTone); }
            else { root.classList.add('light-theme'); root.classList.remove('dark-theme'); root.setAttribute('data-theme', 'light'); }
        } catch (e) {}
    })();
    </script>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <link rel="stylesheet" href="css/design-system.css?v=<?php echo file_exists('css/design-system.css') ? filemtime('css/design-system.css') : 1; ?>">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo file_exists('css/theme.css') ? filemtime('css/theme.css') : 1; ?>">
    <link rel="stylesheet" href="css/components.css?v=<?php echo file_exists('css/components.css') ? filemtime('css/components.css') : 1; ?>">
    <link rel="stylesheet" href="css/tailwind.min.css?v=<?php echo file_exists('css/tailwind.min.css') ? filemtime('css/tailwind.min.css') : 1; ?>">
    <link rel="stylesheet" href="css/all.min.css?v=2027" media="print" onload="this.media='all'">
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : 1; ?>"></script>
    <script defer src="js/elsajana-universal-ui-2027.js?v=<?php echo file_exists('js/elsajana-universal-ui-2027.js') ? filemtime('js/elsajana-universal-ui-2027.js') : 1; ?>"></script>
    
    <style>
        :root {
            --bg-dark: #070a12;
            --panel-bg: rgba(15, 23, 42, 0.75);
            --border-glass: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
        }

        :root.light-theme {
            --bg-dark: #f8fafc;
            --panel-bg: #ffffff;
            --border-glass: rgba(0, 0, 0, 0.12);
            --text-main: #000000;
            --text-sub: #334155;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', 'Tajawal', sans-serif;
            margin: 0; padding: 0;
            min-height: 100vh;
        }

        .report-master-container {
            max-width: 1650px;
            margin: 0 auto;
            padding: 24px 32px;
            display: flex;
            flex-direction: column;
            gap: 26px;
        }

        .kpi-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .kpi-stat-card {
            background: rgba(15, 23, 42, 0.65);
            border: 2px solid var(--card-border-color, rgba(255, 255, 255, 0.1));
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: transform 0.3s ease, opacity 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        :root.light-theme .kpi-stat-card {
            background: #ffffff !important;
            border: 2.5px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06) !important;
        }

        .kpi-stat-label {
            font-size: 0.85rem;
            color: var(--text-sub);
            font-weight: 700;
        }
        :root.light-theme .kpi-stat-label { color: #475569 !important; font-weight: 800 !important; }

        .kpi-stat-val {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--stat-color, #38bdf8);
        }

        .section-block-card {
            background: rgba(15, 23, 42, 0.7);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }

        :root.light-theme .section-block-card {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06) !important;
        }

        .section-title-bar {
            font-size: 1.3rem;
            font-weight: 900;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-main);
            border-bottom: 2px dashed rgba(255, 255, 255, 0.12);
            padding-bottom: 14px;
        }

        :root.light-theme .section-title-bar {
            color: #000000 !important;
            border-bottom-color: #000000 !important;
        }

        /* Table Styling */
        .master-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .master-data-table th {
            background: rgba(30, 41, 59, 0.85);
            color: #f8fafc;
            padding: 14px 16px;
            text-align: right;
            font-weight: 900;
            border-bottom: 2.5px solid rgba(255, 255, 255, 0.15);
        }

        :root.light-theme .master-data-table th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border-bottom: 3px solid #000000 !important;
        }

        .master-data-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            color: var(--text-main);
            font-weight: 600;
        }

        :root.light-theme .master-data-table td {
            color: #000000 !important;
            border-bottom: 1.5px solid rgba(0,0,0,0.1) !important;
        }

        .master-data-table tr:hover td {
            background: rgba(255, 255, 255, 0.04);
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; color: #000000 !important; }
            .section-block-card { border: 1.5px solid #000000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="has-sidebar no-page-transition">

    <div class="report-master-container" id="dmr-proportional-root">
        
        <?php include 'includes/header.php'; ?>

        <!-- Action & Navigation Bar -->
        <div id="dmr-top-nav-bar" class="flex flex-wrap items-center justify-between gap-3 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-purple-500/30 p-3.5 rounded-2xl shadow-xl">
            <div class="flex items-center gap-4 flex-wrap">
                <a href="analytics.php" onclick="window.location.href='analytics.php'; return false;" class="dmr-btn">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ التقارير
                </a>
                <h1 class="text-xl md:text-2xl font-black flex items-center gap-2.5 m-0">
                    <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i>
                    التقرير اليومي الشامل
                </h1>
            </div>

            <!-- Date & Print Controls -->
            <div class="flex items-center flex-wrap gap-2">
                <div class="flex items-center gap-2 bg-slate-800/80 border border-slate-700 px-2.5 py-1.5 rounded-xl">
                    <label for="report-date-input" class="text-xs font-bold text-slate-300">التاريخ:</label>
                    <input type="date" id="report-date-input" class="bg-slate-900 text-white font-bold text-sm px-2.5 py-1 rounded-lg border border-slate-700 focus:outline-none">
                </div>

                <button type="button" onclick="openDailyAIAuditModal();" class="dmr-btn" style="background:linear-gradient(135deg,#7c3aed,#9333ea)!important;">
                    <span>🤖</span> تدقيق ذكي
                </button>
                <button type="button" onclick="shareDailyReportWhatsApp();" class="dmr-btn" style="background:linear-gradient(135deg,#0d9488,#14b8a6)!important;">
                    <span>💬</span> واتساب
                </button>
                <button type="button" onclick="if(window.ElsajanaForensicAndCharts2027){window.ElsajanaForensicAndCharts2027.printThermal80mmOrA4('A4');}else{window.print();}" class="dmr-btn" style="background:linear-gradient(135deg,#059669,#10b981)!important;">
                    <i class="fa-solid fa-print"></i> طباعة
                </button>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY BAR 📊 -->
        <div class="kpi-summary-grid">
            <div class="kpi-stat-card" style="--card-border-color: rgba(56, 189, 248, 0.4); --stat-color: #38bdf8;">
                <span class="kpi-stat-label">🛒 المبيعات اليومية (صافي)</span>
                <span class="kpi-stat-val" id="kpi-sales">0 SDG</span>
            </div>
            <div class="kpi-stat-card" style="--card-border-color: rgba(245, 158, 11, 0.4); --stat-color: #f59e0b;">
                <span class="kpi-stat-label">🛍️ المشتريات اليومية (صافي)</span>
                <span class="kpi-stat-val" id="kpi-purchases">0 SDG</span>
            </div>
            <div class="kpi-stat-card" style="--card-border-color: rgba(16, 185, 129, 0.4); --stat-color: #10b981;">
                <span class="kpi-stat-label">💵 المقبوضات النقدية الواردة</span>
                <span class="kpi-stat-val" id="kpi-receipts">0 SDG</span>
            </div>
            <div class="kpi-stat-card" style="--card-border-color: rgba(244, 63, 94, 0.4); --stat-color: #f43f5e;">
                <span class="kpi-stat-label">💸 المدفوعات والمنصرفات</span>
                <span class="kpi-stat-val" id="kpi-payments">0 SDG</span>
            </div>
            <div class="kpi-stat-card" style="--card-border-color: rgba(168, 85, 247, 0.4); --stat-color: #c084fc;">
                <span class="kpi-stat-label">⚖️ صافي التدفق المالي اليومي</span>
                <span class="kpi-stat-val" id="kpi-netflow">0 SDG</span>
            </div>
            <div class="kpi-stat-card" style="--card-border-color: rgba(6, 182, 212, 0.4); --stat-color: #06b6d4;">
                <span class="kpi-stat-label">📦 إجمالي حركات المستودعات</span>
                <span class="kpi-stat-val" id="kpi-stock-moves">0 حركة</span>
            </div>
        </div>

        <!-- 📄 SECTION 1: FINANCIAL VOUCHERS, RECEIPTS & EXPENSES 📄 -->
        <div class="section-block-card">
            <div class="section-title-bar">
                <span class="flex items-center gap-2">
                    <span style="color:#10b981;">💵</span> 1. السندات والحركات المالية والقيود والمنصرفات اليومية
                </span>
                <span class="text-xs font-bold text-slate-400 bg-slate-800/60 px-3 py-1 rounded-lg border border-slate-700">سندات القبض + الصرف + المنصرفات + القيود</span>
            </div>
            <div class="overflow-x-auto">
                <table class="master-data-table">
                    <thead>
                        <tr>
                            <th>نوع الحركة / المستند</th>
                            <th>رقم السند/القيد</th>
                            <th>البيان / الوصف والتفاصيل</th>
                            <th>الحساب / العميل / المورد</th>
                            <th>طريقة الدفع / الخزينة / البنك</th>
                            <th>المبلغ الصافي</th>
                        </tr>
                    </thead>
                    <tbody id="financial-vouchers-tbody">
                        <tr><td colspan="6" class="text-center py-4 text-slate-400 font-bold">جاري تحميل حركات اليوم المالية... ⏳</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 🧾 SECTION 2: SALES & PURCHASE INVOICES 🧾 -->
        <div class="section-block-card">
            <div class="section-title-bar">
                <span class="flex items-center gap-2">
                    <span style="color:#38bdf8;">🧾</span> 2. حركات فواتير المبيعات والمشتريات والمرتجعات
                </span>
                <span class="text-xs font-bold text-slate-400 bg-slate-800/60 px-3 py-1 rounded-lg border border-slate-700">مبيعات + مشتريات + مرتجعات</span>
            </div>
            <div class="overflow-x-auto">
                <table class="master-data-table">
                    <thead>
                        <tr>
                            <th>نوع الفاتورة</th>
                            <th>رقم الفاتورة</th>
                            <th>اسم العميل / المورد</th>
                            <th>طريقة السداد</th>
                            <th>تفاصيل الأصناف والكميات المباعة/المشتراة</th>
                            <th>صافي الإجمالي النهائي</th>
                        </tr>
                    </thead>
                    <tbody id="invoices-tbody">
                        <tr><td colspan="6" class="text-center py-4 text-slate-400 font-bold">جاري تحميل فواتير المبيعات والمشتريات والمرتجعات... ⏳</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 💳 SECTION 3: CHECKS & BANK TRANSACTIONS 💳 -->
        <div class="section-block-card">
            <div class="section-title-bar">
                <span class="flex items-center gap-2">
                    <span style="color:#c084fc;">💳</span> 3. حركات محفظة الشيكات والتحويلات والعمليات البنكية
                </span>
                <span class="text-xs font-bold text-slate-400 bg-slate-800/60 px-3 py-1 rounded-lg border border-slate-700">شيكات صادرة/واردة + إيداعات وسحوبات بنكية</span>
            </div>
            <div class="overflow-x-auto">
                <table class="master-data-table">
                    <thead>
                        <tr>
                            <th>نوع العملية</th>
                            <th>رقم الشيك / العملية</th>
                            <th>اسم البنك / الحساب</th>
                            <th>تاريخ الاستحقاق / الإيداع</th>
                            <th>البيان / المستفيد</th>
                            <th>المبلغ</th>
                        </tr>
                    </thead>
                    <tbody id="checks-tbody">
                        <tr><td colspan="6" class="text-center py-4 text-slate-400 font-bold">جاري تحميل حركات الشيكات والعمليات البنكية... ⏳</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📦 SECTION 4: INVENTORY & STOCK MOVEMENTS 📦 -->
        <div class="section-block-card">
            <div class="section-title-bar">
                <span class="flex items-center gap-2">
                    <span style="color:#f59e0b;">📦</span> 4. حركات الأصناف والمستودعات والتسويات المخزنية
                </span>
                <span class="text-xs font-bold text-slate-400 bg-slate-800/60 px-3 py-1 rounded-lg border border-slate-700">صرف + إدخال + نقل بين المستودعات + تسويات</span>
            </div>
            <div class="overflow-x-auto">
                <table class="master-data-table">
                    <thead>
                        <tr>
                            <th>اسم الصنف</th>
                            <th>نوع الحركة المخزنية</th>
                            <th>الكمية التأثيرية</th>
                            <th>المستودع / الفرع</th>
                            <th>المرجع (رقم الفاتورة/السند)</th>
                        </tr>
                    </thead>
                    <tbody id="stock-tbody">
                        <tr><td colspan="5" class="text-center py-4 text-slate-400 font-bold">جاري تحميل حركات الأصناف والمستودعات والتسويات... ⏳</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Master Daily Data Script -->
<script src="js/screens/daily_master_report-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/daily_master_report-j1.js') ?: '2027' ?>"></script>

<!-- ═══ DAILY_MASTER_SUPREME_30_POINTS_START ═══ -->
<script>
window.ERP_DB_DAILY_BRIDGE = <?php echo json_encode($db_daily_bridge, JSON_UNESCAPED_UNICODE); ?>;
</script>

<link rel="stylesheet" id="dmr-supreme-30points-css" href="css/screens/daily_master_report-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/daily_master_report-s1.css') ?: '2027' ?>">

<script id="dmr-supreme-30points-engine" src="js/screens/daily_master_report-j2.js?v=<?= @filemtime(__DIR__ . '/js/screens/daily_master_report-j2.js') ?: '2027' ?>"></script>
<!-- ═══ DAILY_MASTER_SUPREME_30_POINTS_END ═══ -->

</body>
</html>
