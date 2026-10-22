<?php
// ====================================================================
// Al-Sajana ERP 2026 - التقرير اليومي الشامل والموسع للمؤسسة V2
// التقرير الرقابي الشامل لجميع السندات، الفواتير، القيود، وحركات الأصناف
// ====================================================================

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$page_title = 'التقرير اليومي الشامل للمؤسسة';
$header_theme_class = 'header-theme-reports';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    
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
            transition: all 0.3s ease;
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

<body class="no-page-transition">

    <div class="report-master-container">
        
        <?php include 'includes/header.php'; ?>

        <!-- Action & Navigation Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-purple-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="analytics.php" onclick="window.location.href='analytics.php'; return false;" class="inline-flex items-center gap-2 text-sky-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع لشاشة التقارير الرئيسية
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i>
                    التقرير اليومي الشامل للعمليات والمستودعات
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">كشف رقابي متكامل لجميع السندات المالية، الفواتير، القيود، المنصرفات، وحركات الأصناف اليومية.</p>
            </div>

            <!-- Date & Print Controls -->
            <div class="flex items-center flex-wrap gap-3">
                <div class="flex items-center gap-2 bg-slate-800/80 border border-slate-700 p-2 rounded-xl">
                    <label for="report-date-input" class="text-xs font-bold text-slate-300">التاريخ المحدد:</label>
                    <input type="date" id="report-date-input" class="bg-slate-900 text-white font-bold text-sm px-3 py-1.5 rounded-lg border border-slate-700 focus:outline-none focus:border-sky-400">
                </div>

                <button onclick="window.print();" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-4 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة التقرير الشامل
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('report-date-input');
            const todayStr = new Date().toISOString().split('T')[0];
            dateInput.value = todayStr;

            dateInput.addEventListener('change', function () {
                renderMasterDailyReport(this.value);
            });

            renderMasterDailyReport(todayStr);
        });

        function renderMasterDailyReport(targetDate) {
            try {
                // Fetch ERP Collections from Local Storage & DB Bridge (exclude archived split parents)
                const sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && !x.wasSplit && x.saleType !== 'preliminary' && x.status !== 'preliminary');
                const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN && !x.isReturn);
                const salesReturns = (JSON.parse(localStorage.getItem('erp_sales_returns_v1')) || []).filter(x => !x.deleted);
                const purchaseReturns = (JSON.parse(localStorage.getItem('erp_purchase_returns_v1')) || []).filter(x => !x.deleted);
                
                const items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
                const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                const expenses = JSON.parse(localStorage.getItem('erp_expenses_v1')) || [];
                const multiPayments = JSON.parse(localStorage.getItem('erp_payments_v1')) || [];
                const multiReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
                const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
                const customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                const suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                const warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                const checks = JSON.parse(localStorage.getItem('erp_checks_v1')) || [];

                // Filter Target Date
                const todaySales = sales.filter(x => x.date === targetDate);
                const todayPurchases = purchases.filter(x => x.date === targetDate);
                const todaySalesReturns = salesReturns.filter(x => x.date === targetDate);
                const todayPurchaseReturns = purchaseReturns.filter(x => x.date === targetDate);

                const todayPayments = payments.filter(x => x.date === targetDate && !x.deleted);
                const todayReceipts = receipts.filter(x => x.date === targetDate && !x.deleted);
                const todayExpenses = expenses.filter(x => x.date === targetDate && !x.deleted);
                const todayMultiPayments = multiPayments.filter(x => x.date === targetDate && !x.deleted);
                const todayMultiReceipts = multiReceipts.filter(x => x.date === targetDate && !x.deleted);
                const todayJvs = jvs.filter(x => x.date === targetDate && !x.deleted);
                const todayChecks = checks.filter(x => (x.date === targetDate || x.dueDate === targetDate) && !x.deleted);

                // Calculate KPIs
                const totalSalesAmt = todaySales.reduce((sum, x) => sum + (parseFloat(x.grandTotal) || 0), 0) - todaySalesReturns.reduce((sum, x) => sum + (parseFloat(x.grandTotal) || 0), 0);
                const totalPurchasesAmt = todayPurchases.reduce((sum, x) => sum + (parseFloat(x.grandTotal) || 0), 0) - todayPurchaseReturns.reduce((sum, x) => sum + (parseFloat(x.grandTotal) || 0), 0);
                
                const totalReceiptsAmt = todayReceipts.reduce((sum, x) => sum + (parseFloat(x.amount) || 0), 0) + todayMultiReceipts.reduce((sum, x) => sum + (parseFloat(x.totalAmount) || 0), 0);
                const totalPaymentsAmt = todayPayments.reduce((sum, x) => sum + (parseFloat(x.amount) || 0), 0) + todayMultiPayments.reduce((sum, x) => sum + (parseFloat(x.totalAmount) || 0), 0) + todayExpenses.reduce((sum, x) => sum + (parseFloat(x.amount) || 0), 0);
                
                const netFlow = totalReceiptsAmt - totalPaymentsAmt;

                let stockMoveCount = 0;
                todaySales.forEach(inv => { stockMoveCount += (inv.items || []).length; });
                todayPurchases.forEach(inv => { stockMoveCount += (inv.items || inv.lines || []).length; });
                todaySalesReturns.forEach(inv => { stockMoveCount += (inv.items || []).length; });
                todayPurchaseReturns.forEach(inv => { stockMoveCount += (inv.items || inv.lines || []).length; });

                // Update KPI Labels
                document.getElementById('kpi-sales').textContent = totalSalesAmt.toLocaleString() + ' SDG';
                document.getElementById('kpi-purchases').textContent = totalPurchasesAmt.toLocaleString() + ' SDG';
                document.getElementById('kpi-receipts').textContent = totalReceiptsAmt.toLocaleString() + ' SDG';
                document.getElementById('kpi-payments').textContent = totalPaymentsAmt.toLocaleString() + ' SDG';
                document.getElementById('kpi-netflow').textContent = netFlow.toLocaleString() + ' SDG';
                document.getElementById('kpi-stock-moves').textContent = stockMoveCount + ' حركة';

                // Helper Name Resolvers
                const resolveClientName = (id) => {
                    if(!id) return '-';
                    const c = customers.find(x => x.id == id || x.code == id);
                    if(c) return c.name;
                    const s = suppliers.find(x => x.id == id || x.code == id);
                    if(s) return s.name;
                    return id;
                };

                const resolveWhName = (id) => {
                    if(!id) return 'المستودع الرئيسي';
                    const w = warehouses.find(x => x.id == id || x.code == id);
                    return w ? w.name : id;
                };

                // 1. Render Financial Vouchers & Expenses Table
                let finHtml = '';
                todayReceipts.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند قبض مفرد</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.description || x.notes || 'قبض نقدية'}</td>
                            <td>${resolveClientName(x.customer || x.clientId)}</td>
                            <td>${x.cashBox || x.bank || 'الخزينة الرئيسية'}</td>
                            <td class="text-emerald-400 font-black">+${(parseFloat(x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayMultiReceipts.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند قبض مجمع</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.notes || 'قبض مجمع'}</td>
                            <td>-</td>
                            <td>${x.cashBox || 'الخزينة الرئيسية'}</td>
                            <td class="text-emerald-400 font-black">+${(parseFloat(x.totalAmount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayPayments.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-rose-500/20 text-rose-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند صرف مفرد</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.description || x.notes || 'صرف نقدية'}</td>
                            <td>${resolveClientName(x.customer || x.clientId)}</td>
                            <td>${x.cashBox || x.bank || 'الخزينة الرئيسية'}</td>
                            <td class="text-rose-400 font-black">-${(parseFloat(x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayMultiPayments.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-rose-500/20 text-rose-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند صرف مجمع</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.notes || 'صرف مجمع'}</td>
                            <td>-</td>
                            <td>${x.cashBox || 'الخزينة الرئيسية'}</td>
                            <td class="text-rose-400 font-black">-${(parseFloat(x.totalAmount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayExpenses.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-amber-500/20 text-amber-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند منصرفات</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.description || x.category || 'مصروف عمومي'}</td>
                            <td>حساب المصروفات</td>
                            <td>${x.cashBox || 'الصندوق الرئيسي'}</td>
                            <td class="text-rose-400 font-black">-${(parseFloat(x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayJvs.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-purple-500/20 text-purple-400 px-2.5 py-1 rounded-md text-xs font-extrabold">قيد محاسبي اليومية</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.statement || 'قيد تسوية مزدوج'}</td>
                            <td>-</td>
                            <td>شجرة الحسابات العامة</td>
                            <td class="font-black">${(parseFloat(x.totalAmount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                document.getElementById('financial-vouchers-tbody').innerHTML = finHtml || `<tr><td colspan="6" class="text-center py-5 text-slate-400 font-bold">لا توجد حركات أو سندات مالية مسجلة في هذا التاريخ.</td></tr>`;

                // 2. Render Invoices Table
                let invHtml = '';
                todaySales.forEach(x => {
                    let itemsList = (x.items || []).map(item => `${item.name} (${item.quantity || item.qty} وحدات)`).join('، ');
                    let splitBadge = x.isSplit ? `<div class="text-[11px] text-pink-400 font-bold mt-0.5">✂️ أصل #${x.rootInvoice || x.parentInvoice} • تقسيم #${x.splitBatchNo || 1}</div>` : '';
                    invHtml += `
                        <tr>
                            <td><span class="bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md text-xs font-extrabold">${x.isSplit ? 'فاتورة مقسمة ✂️' : 'فاتورة مبيعات'}</span></td>
                            <td>#${x.invoiceNo || '-'}${splitBadge}</td>
                            <td>${resolveClientName(x.customer || x.customerId)}</td>
                            <td>${x.paymentMethod === 'credit' ? 'آجل 💳' : 'نقداً 💵'}</td>
                            <td class="text-xs text-slate-300">${itemsList || '-'}</td>
                            <td class="text-emerald-400 font-black">${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayPurchases.forEach(x => {
                    let itemsList = (x.items || x.lines || []).map(item => `${item.name || item.itemId} (${item.quantity || item.qty} وحدات)`).join('، ');
                    invHtml += `
                        <tr>
                            <td><span class="bg-amber-500/20 text-amber-400 px-2.5 py-1 rounded-md text-xs font-extrabold">فاتورة مشتريات</span></td>
                            <td>#${x.invoiceNo || '-'}</td>
                            <td>${resolveClientName(x.supplier || x.supplierId || x.customer)}</td>
                            <td>${x.paymentMethod === 'credit' ? 'آجل 💳' : 'نقداً 💵'}</td>
                            <td class="text-xs text-slate-300">${itemsList || '-'}</td>
                            <td class="text-amber-400 font-black">${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todaySalesReturns.forEach(x => {
                    invHtml += `
                        <tr>
                            <td><span class="bg-rose-500/20 text-rose-400 px-2.5 py-1 rounded-md text-xs font-extrabold">مردود مبيعات</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${resolveClientName(x.customer || x.customerId)}</td>
                            <td>مسترد</td>
                            <td class="text-xs text-slate-300">إرجاع صنف مبيعات</td>
                            <td class="text-rose-400 font-black">-${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayPurchaseReturns.forEach(x => {
                    invHtml += `
                        <tr>
                            <td><span class="bg-purple-500/20 text-purple-400 px-2.5 py-1 rounded-md text-xs font-extrabold">مردود مشتريات</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${resolveClientName(x.supplier || x.supplierId)}</td>
                            <td>مسترد</td>
                            <td class="text-xs text-slate-300">إرجاع صنف مشتريات</td>
                            <td class="text-purple-400 font-black">+${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                document.getElementById('invoices-tbody').innerHTML = invHtml || `<tr><td colspan="6" class="text-center py-5 text-slate-400 font-bold">لا توجد فواتير مبيعات أو مشتريات مسجلة في هذا التاريخ.</td></tr>`;

                // 3. Render Checks & Bank Transactions Table
                let checkHtml = '';
                todayChecks.forEach(x => {
                    checkHtml += `
                        <tr>
                            <td><span class="bg-purple-500/20 text-purple-400 px-2.5 py-1 rounded-md text-xs font-extrabold">${x.type === 'incoming' ? 'شيك وارد' : 'شيك صادرة'}</span></td>
                            <td>#${x.checkNumber || x.id || '-'}</td>
                            <td>${x.bankName || 'البنك'}</td>
                            <td>${x.dueDate || x.date || '-'}</td>
                            <td>${resolveClientName(x.payee || x.drawer)} - ${x.notes || ''}</td>
                            <td class="text-purple-400 font-black">${(parseFloat(x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                document.getElementById('checks-tbody').innerHTML = checkHtml || `<tr><td colspan="6" class="text-center py-5 text-slate-400 font-bold">لا توجد حركات شيكات أو تحويلات بنكية في هذا التاريخ.</td></tr>`;

                // 4. Render Stock Movements Table
                let stockHtml = '';
                todaySales.forEach(inv => {
                    (inv.items || []).forEach(line => {
                        stockHtml += `
                            <tr>
                                <td>${line.name}</td>
                                <td><span class="bg-rose-500/20 text-rose-400 px-2.5 py-1 rounded-md text-xs font-extrabold">صرف (بيع)</span></td>
                                <td class="text-rose-400 font-black">-${line.quantity || line.qty}</td>
                                <td>${resolveWhName(inv.deliveryWarehouse || line.warehouseId)}</td>
                                <td>فاتورة مبيعات #${inv.invoiceNo}</td>
                            </tr>`;
                    });
                });
                todayPurchases.forEach(inv => {
                    (inv.items || inv.lines || []).forEach(line => {
                        stockHtml += `
                            <tr>
                                <td>${line.name || line.itemId}</td>
                                <td><span class="bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md text-xs font-extrabold">إدخال (شراء)</span></td>
                                <td class="text-emerald-400 font-black">+${line.quantity || line.qty}</td>
                                <td>${resolveWhName(line.warehouseId || inv.warehouseId)}</td>
                                <td>فاتورة مشتريات #${inv.invoiceNo}</td>
                            </tr>`;
                    });
                });
                document.getElementById('stock-tbody').innerHTML = stockHtml || `<tr><td colspan="5" class="text-center py-5 text-slate-400 font-bold">لا توجد حركات أصناف أو مستودعات مسجلة في هذا التاريخ.</td></tr>`;

            } catch (err) {
                console.error(err);
            }
        }
    </script>
</body>
</html>
