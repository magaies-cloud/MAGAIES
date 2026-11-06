/**
 * MAGAIEES ERP — Internationalization (i18n) Engine
 * Supports: Arabic (ar) and English (en)
 * 
 * Usage:
 *   - t('key') → returns translated string for current language
 *   - setLang('en') → switches to English, reloads page
 *   - getLang() → returns current language code ('ar' or 'en')
 *   - Add data-i18n="key" attribute to HTML elements for auto-translation
 */

(function () {
    'use strict';

    // ─── Translation Dictionary ─────────────────────────────────
    const translations = {

        // ══════════════════════════════════════════════
        //  SIDEBAR (القائمة الجانبية)
        // ══════════════════════════════════════════════
        'sidebar.dashboard': { ar: 'لوحة التحكم', en: 'Dashboard' },
        'sidebar.sales': { ar: 'المبيعات', en: 'Sales' },
        'sidebar.purchases': { ar: 'المشتريات', en: 'Purchases' },
        'sidebar.inventory': { ar: 'تقارير المخزون والجرد', en: 'Inventory & Stock Reports' },
        'sidebar.finance': { ar: 'المعاملات المالية', en: 'Financial Transactions' },
        'sidebar.masterData': { ar: 'البيانات الأساسية', en: 'Master Data' },
        'sidebar.reports': { ar: 'تقارير مالية', en: 'Financial Reports' },
        'sidebar.alerts': { ar: 'التنبيهات', en: 'Alerts' },
        'sidebar.settings': { ar: 'الإعدادات', en: 'Settings' },
        'sidebar.auditLog': { ar: 'الصندوق الأسود', en: 'Audit Log' },
        'sidebar.logout': { ar: 'خروج', en: 'Logout' },
        'sidebar.brand': { ar: 'مقاييس (MAGAIEES)', en: 'MAGAIEES (ERP)' },

        // ══════════════════════════════════════════════
        //  HEADER (الترويسة)
        // ══════════════════════════════════════════════
        'header.systemName': { ar: 'نظام إدارة الأعمال', en: 'Business Management System' },
        'header.loading': { ar: 'جاري التحميل...', en: 'Loading...' },
        'header.sysAdmin': { ar: 'مدير النظام', en: 'System Admin' },
        'header.onlineNow': { ar: 'متصل الآن:', en: 'Online:' },
        'header.exchangeRate': { ar: 'سعر الصرف:', en: 'Exchange Rate:' },
        'header.fullscreen': { ar: 'توسيع الشاشة', en: 'Toggle Fullscreen' },
        'header.toggleTheme': { ar: 'تبديل المظهر', en: 'Toggle Theme' },
        'header.back': { ar: 'رجوع', en: 'Back' },

        // ══════════════════════════════════════════════
        //  PAGE TITLES (عناوين الصفحات)
        // ══════════════════════════════════════════════
        'page.dashboard': { ar: 'لوحة التحكم', en: 'Dashboard' },
        'page.sales': { ar: 'المبيعات المباشرة', en: 'Direct Sales' },
        'page.purchases': { ar: 'المشتريات', en: 'Purchases' },
        'page.inventory': { ar: 'تقارير المخزون', en: 'Inventory Reports' },
        'page.finance': { ar: 'المعاملات المالية', en: 'Financial Transactions' },
        'page.masterData': { ar: 'البيانات الأساسية', en: 'Master Data' },
        'page.reports': { ar: 'التقارير والتحليلات', en: 'Reports & Analytics' },
        'page.alerts': { ar: 'التنبيهات', en: 'Alerts' },
        'page.settings': { ar: 'الإعدادات', en: 'Settings' },
        'page.auditLog': { ar: 'الصندوق الأسود', en: 'Audit Log' },

        // ══════════════════════════════════════════════
        //  SETTINGS (الإعدادات)
        // ══════════════════════════════════════════════
        'settings.companyData': { ar: '🏢 بيانات الشركة', en: '🏢 Company Data' },
        'settings.financeTax': { ar: '💰 المالية والضرائب', en: '💰 Finance & Tax' },
        'settings.warehouses': { ar: '📦 المخازن', en: '📦 Warehouses' },
        'settings.printing': { ar: '🖨️ إعدادات الطباعة', en: '🖨️ Print Settings' },
        'settings.smtp': { ar: '📧 إعدادات البريد SMTP', en: '📧 SMTP Email Settings' },
        'settings.whatsapp': { ar: '💬 ربط الواتساب', en: '💬 WhatsApp Integration' },
        'settings.users': { ar: '🛡️ المستخدمين', en: '🛡️ Users' },
        'settings.langUI': { ar: '🎨 اللغة والواجهة', en: '🎨 Language & Interface' },
        'settings.system': { ar: '💾 النظام والنسخ', en: '💾 System & Backup' },
        'settings.langTitle': { ar: '🌐 اللغة', en: '🌐 Language' },
        'settings.selectLang': { ar: 'اختر لغة واجهة النظام', en: 'Select system interface language' },
        'settings.uiTitle': { ar: '🎨 واجهة المستخدم والمظهر', en: '🎨 User Interface & Appearance' },
        'settings.fontSize': { ar: 'حجم الخط الرئيسي للنظام (Font Size)', en: 'Main System Font Size' },
        'settings.fontVerySmall': { ar: 'صغير جداً (14px)', en: 'Very Small (14px)' },
        'settings.fontMedium': { ar: 'متوسط (16px - افتراضي)', en: 'Medium (16px - Default)' },
        'settings.fontLarge': { ar: 'كبير (18px)', en: 'Large (18px)' },
        'settings.fontVeryLarge': { ar: 'كبير جداً (20px)', en: 'Very Large (20px)' },
        'settings.autoBackup': { ar: 'النسخ الاحتياطي التلقائي اليومي', en: 'Daily Auto Backup' },
        'settings.autoDark': { ar: 'تفعيل الوضع التلقائي الغامق', en: 'Enable Auto Dark Mode' },
        'settings.showShapes': { ar: 'إظهار الأشكال في الترويسة العليا', en: 'Show Shapes in Top Header' },
        'settings.autoLogout': { ar: 'مدة تسجيل الخروج التلقائي عند عدم النشاط', en: 'Auto Logout Duration on Inactivity' },
        'settings.logoutDisabled': { ar: 'بدون (معطل)', en: 'None (Disabled)' },
        'settings.logout30s': { ar: '30 ثانية', en: '30 Seconds' },
        'settings.logout1m': { ar: '1 دقيقة', en: '1 Minute' },
        'settings.logout5m': { ar: '5 دقائق', en: '5 Minutes' },
        'settings.logout10m': { ar: '10 دقائق', en: '10 Minutes' },
        'settings.logout30m': { ar: '30 دقيقة', en: '30 Minutes' },
        'settings.companyTitle': { ar: '🏢 بيانات الشركة الأساسية', en: '🏢 Basic Company Data' },
        'settings.companyName': { ar: 'اسم الشركة / المحل', en: 'Company / Store Name' },
        'settings.companyNamePlaceholder': { ar: 'أدخل اسم الشركة أو المحل', en: 'Enter company or store name' },
        'settings.companyPhone': { ar: 'رقم الهاتف', en: 'Phone Number' },
        'settings.companyPhonePlaceholder': { ar: 'رقم الهاتف', en: 'Phone Number' },
        'settings.companyEmail': { ar: 'البريد الإلكتروني', en: 'Email' },
        'settings.companyEmailPlaceholder': { ar: 'البريد الإلكتروني', en: 'Email Address' },
        'settings.companyAddress': { ar: 'عنوان الشركة', en: 'Company Address' },
        'settings.companyAddressPlaceholder': { ar: 'عنوان الشركة الكامل', en: 'Full company address' },
        'settings.companyCR': { ar: 'رقم السجل التجاري', en: 'Commercial Registration No.' },
        'settings.companyCRPlaceholder': { ar: 'رقم السجل التجاري', en: 'Commercial Registration No.' },
        'settings.companyTaxNo': { ar: 'الرقم الضريبي', en: 'Tax Number' },
        'settings.companyTaxNoPlaceholder': { ar: 'الرقم الضريبي', en: 'Tax Number' },
        'settings.companyLogo': { ar: 'شعار الشركة', en: 'Company Logo' },
        'settings.uploadLogo': { ar: '📁 رفع شعار جديد', en: '📁 Upload New Logo' },
        'settings.saveCompany': { ar: '💾 حفظ بيانات الشركة', en: '💾 Save Company Data' },
        'settings.financeTitle': { ar: '💰 المالية والضرائب', en: '💰 Finance & Tax' },
        'settings.mainCurrency': { ar: 'العملة الرئيسية', en: 'Main Currency' },
        'settings.secondCurrency': { ar: 'العملة الثانوية (اختياري)', en: 'Secondary Currency (Optional)' },
        'settings.exchangeRate': { ar: 'سعر صرف العملة الثانوية', en: 'Secondary Currency Exchange Rate' },
        'settings.taxRate': { ar: 'نسبة ضريبة القيمة المضافة (%)', en: 'VAT Rate (%)' },
        'settings.taxIncluded': { ar: 'الأسعار شاملة الضريبة افتراضياً', en: 'Prices Include Tax by Default' },
        'settings.enableTax': { ar: 'تفعيل الضريبة على الفواتير', en: 'Enable Tax on Invoices' },
        'settings.saveFinance': { ar: '💾 حفظ إعدادات المالية', en: '💾 Save Finance Settings' },
        'settings.warehouseRules': { ar: '📦 قواعد المخازن', en: '📦 Warehouse Rules' },
        'settings.allowNegativeStock': { ar: 'السماح بالبيع بالسالب (تجاوز رصيد المخزون)', en: 'Allow Negative Stock (Sell below stock balance)' },
        'settings.confirmSave': { ar: 'هل تريد حفظ التغييرات؟', en: 'Do you want to save changes?' },
        'settings.saved': { ar: 'تم الحفظ بنجاح', en: 'Saved successfully' },
        'settings.systemBackup': { ar: '💾 النظام والنسخ الاحتياطي', en: '💾 System & Backup' },
        'settings.backupDesc': { ar: 'تنزيل نسخة احتياطية آمنة من جميع بيانات التخزين المحلي (JSON) لحمايتها من الفقدان.', en: 'Download a secure backup of all local storage data (JSON) to protect against data loss.' },
        'settings.downloadBackup': { ar: '⬇️ تنزيل نسخة احتياطية للبيانات', en: '⬇️ Download Data Backup' },
        'settings.restoreBackup': { ar: '⬆️ استعادة من ملف احتياطي سابق', en: '⬆️ Restore from Previous Backup' },
        'settings.dangerZone': { ar: '⚠️ منطقة الخطر', en: '⚠️ Danger Zone' },
        'settings.dangerDesc': { ar: 'القيام بتهيئة وإفراغ جميع جداول قواعد البيانات لمسح الحركات التجريبية والبدء من جديد.', en: 'Reset and clear all database tables to erase test transactions and start fresh.' },
        'settings.resetData': { ar: '🗑️ تصفير بيانات النظام', en: '🗑️ Reset System Data' },
        'settings.printTitle': { ar: '🖨️ إعدادات الطباعة', en: '🖨️ Print Settings' },
        'settings.printSales': { ar: 'المبيعات', en: 'Sales' },
        'settings.printPurchases': { ar: 'المشتريات', en: 'Purchases' },

        // ══════════════════════════════════════════════
        //  DASHBOARD (لوحة التحكم)
        // ══════════════════════════════════════════════
        'dash.totalSales': { ar: 'إجمالي المبيعات', en: 'Total Sales' },
        'dash.totalPurchases': { ar: 'إجمالي المشتريات', en: 'Total Purchases' },
        'dash.netProfit': { ar: 'صافي الربح', en: 'Net Profit' },
        'dash.totalExpenses': { ar: 'إجمالي المصروفات', en: 'Total Expenses' },
        'dash.totalReceipts': { ar: 'إجمالي المقبوضات', en: 'Total Receipts' },
        'dash.totalPayments': { ar: 'إجمالي المدفوعات', en: 'Total Payments' },
        'dash.todaySales': { ar: 'مبيعات اليوم', en: "Today's Sales" },
        'dash.todayPurchases': { ar: 'مشتريات اليوم', en: "Today's Purchases" },
        'dash.lowStockItems': { ar: 'أصناف تحت الحد', en: 'Low Stock Items' },
        'dash.totalCustomers': { ar: 'إجمالي العملاء', en: 'Total Customers' },
        'dash.totalSuppliers': { ar: 'إجمالي الموردين', en: 'Total Suppliers' },
        'dash.totalItems': { ar: 'إجمالي الأصناف', en: 'Total Items' },
        'dash.salesChart': { ar: 'مخطط المبيعات', en: 'Sales Chart' },
        'dash.purchasesChart': { ar: 'مخطط المشتريات', en: 'Purchases Chart' },
        'dash.profitChart': { ar: 'مخطط الأرباح', en: 'Profit Chart' },
        'dash.topItems': { ar: 'الأصناف الأكثر مبيعاً', en: 'Top Selling Items' },
        'dash.topCustomers': { ar: 'أفضل العملاء', en: 'Top Customers' },
        'dash.recentSales': { ar: 'آخر عمليات البيع', en: 'Recent Sales' },
        'dash.recentPurchases': { ar: 'آخر عمليات الشراء', en: 'Recent Purchases' },
        'dash.quickActions': { ar: 'إجراءات سريعة', en: 'Quick Actions' },
        'dash.newSale': { ar: 'فاتورة مبيعات جديدة', en: 'New Sales Invoice' },
        'dash.newPurchase': { ar: 'فاتورة مشتريات جديدة', en: 'New Purchase Invoice' },
        'dash.addItem': { ar: 'إضافة صنف جديد', en: 'Add New Item' },
        'dash.addCustomer': { ar: 'إضافة عميل جديد', en: 'Add New Customer' },
        'dash.customerDebts': { ar: 'ديون العملاء', en: 'Customer Debts' },
        'dash.supplierDebts': { ar: 'ديون الموردين', en: 'Supplier Debts' },
        'dash.cashBalance': { ar: 'رصيد الصندوق', en: 'Cash Balance' },
        'dash.bankBalance': { ar: 'رصيد البنك', en: 'Bank Balance' },

        // ══════════════════════════════════════════════
        //  SALES (المبيعات)
        // ══════════════════════════════════════════════
        'sales.newInvoice': { ar: 'فاتورة جديدة', en: 'New Invoice' },
        'sales.splitInvoice': { ar: 'تفصيل مبيعات', en: 'Sales Detail' },
        'sales.delivery': { ar: 'تسليم', en: 'Delivery' },
        'sales.proformaInvoice': { ar: 'فاتورة مبدئية', en: 'Proforma Invoice' },
        'sales.recentTransactions': { ar: '📋 عمليات المبيعات الأخيرة', en: '📋 Recent Sales Transactions' },
        'sales.search': { ar: 'بحث', en: 'Search' },
        'sales.searchPlaceholder': { ar: 'بحث عن صنف...', en: 'Search for item...' },
        'sales.createInvoice': { ar: 'عمل الفاتورة', en: 'Create Invoice' },
        'sales.refresh': { ar: 'تحديث', en: 'Refresh' },
        'sales.customer': { ar: 'العميل', en: 'Customer' },
        'sales.selectCustomer': { ar: 'اختر العميل', en: 'Select Customer' },
        'sales.cashCustomer': { ar: 'عميل نقدي', en: 'Cash Customer' },
        'sales.warehouse': { ar: 'المخزن', en: 'Warehouse' },
        'sales.selectWarehouse': { ar: 'اختر المخزن', en: 'Select Warehouse' },
        'sales.invoiceDate': { ar: 'تاريخ الفاتورة', en: 'Invoice Date' },
        'sales.paymentMethod': { ar: 'طريقة الدفع', en: 'Payment Method' },
        'sales.cash': { ar: 'نقدي', en: 'Cash' },
        'sales.credit': { ar: 'آجل', en: 'Credit' },
        'sales.bankTransfer': { ar: 'تحويل بنكي', en: 'Bank Transfer' },
        'sales.itemCode': { ar: 'كود الصنف', en: 'Item Code' },
        'sales.itemName': { ar: 'اسم الصنف', en: 'Item Name' },
        'sales.quantity': { ar: 'الكمية', en: 'Qty' },
        'sales.unit': { ar: 'الوحدة', en: 'Unit' },
        'sales.price': { ar: 'السعر', en: 'Price' },
        'sales.discount': { ar: 'الخصم', en: 'Discount' },
        'sales.tax': { ar: 'الضريبة', en: 'Tax' },
        'sales.total': { ar: 'الإجمالي', en: 'Total' },
        'sales.subtotal': { ar: 'المجموع الفرعي', en: 'Subtotal' },
        'sales.grandTotal': { ar: 'الإجمالي النهائي', en: 'Grand Total' },
        'sales.totalDiscount': { ar: 'إجمالي الخصم', en: 'Total Discount' },
        'sales.totalTax': { ar: 'إجمالي الضريبة', en: 'Total Tax' },
        'sales.notes': { ar: 'ملاحظات', en: 'Notes' },
        'sales.print': { ar: 'طباعة', en: 'Print' },
        'sales.save': { ar: 'حفظ', en: 'Save' },
        'sales.cancel': { ar: 'إلغاء', en: 'Cancel' },
        'sales.delete': { ar: 'حذف', en: 'Delete' },
        'sales.edit': { ar: 'تعديل', en: 'Edit' },
        'sales.invoiceNo': { ar: 'رقم الفاتورة', en: 'Invoice No.' },
        'sales.date': { ar: 'التاريخ', en: 'Date' },
        'sales.status': { ar: 'الحالة', en: 'Status' },
        'sales.actions': { ar: 'إجراءات', en: 'Actions' },
        'sales.paid': { ar: 'مدفوعة', en: 'Paid' },
        'sales.unpaid': { ar: 'غير مدفوعة', en: 'Unpaid' },
        'sales.partial': { ar: 'مدفوعة جزئياً', en: 'Partially Paid' },
        'sales.returned': { ar: 'مرتجعة', en: 'Returned' },
        'sales.paidAmount': { ar: 'المبلغ المدفوع', en: 'Paid Amount' },
        'sales.remaining': { ar: 'المتبقي', en: 'Remaining' },
        'sales.currency': { ar: 'العملة', en: 'Currency' },

        // ══════════════════════════════════════════════
        //  PURCHASES (المشتريات)
        // ══════════════════════════════════════════════
        'purchase.newInvoice': { ar: 'فاتورة مشتريات', en: 'Purchase Invoice' },
        'purchase.receiveGoods': { ar: 'استلام بضاعة', en: 'Receive Goods' },
        'purchase.returnGoods': { ar: 'استرداد بضاعة', en: 'Return Goods' },
        'purchase.proformaInvoice': { ar: 'فاتورة مبدئية', en: 'Proforma Invoice' },
        'purchase.supplier': { ar: 'المورد', en: 'Supplier' },
        'purchase.selectSupplier': { ar: 'اختر المورد', en: 'Select Supplier' },
        'purchase.cashSupplier': { ar: 'مورد نقدي', en: 'Cash Supplier' },
        'purchase.recentTransactions': { ar: 'عمليات المشتريات الأخيرة', en: 'Recent Purchase Transactions' },

        // ══════════════════════════════════════════════
        //  INVENTORY (المخزون)
        // ══════════════════════════════════════════════
        'inv.stockReport': { ar: 'تقرير المخزون', en: 'Stock Report' },
        'inv.itemLedger': { ar: 'كشف حساب صنف', en: 'Item Ledger' },
        'inv.warehouseLedger': { ar: 'كشف حركة مخزن', en: 'Warehouse Ledger' },
        'inv.stockTaking': { ar: 'جرد المخزون', en: 'Stock Taking' },
        'inv.currentStock': { ar: 'الرصيد الحالي', en: 'Current Stock' },
        'inv.itemName': { ar: 'اسم الصنف', en: 'Item Name' },
        'inv.category': { ar: 'التصنيف', en: 'Category' },
        'inv.costPrice': { ar: 'سعر التكلفة', en: 'Cost Price' },
        'inv.sellPrice': { ar: 'سعر البيع', en: 'Sell Price' },
        'inv.minStock': { ar: 'الحد الأدنى', en: 'Min Stock' },

        // ══════════════════════════════════════════════
        //  FINANCE (المعاملات المالية)
        // ══════════════════════════════════════════════
        'fin.cashReceipt': { ar: 'سند قبض', en: 'Cash Receipt' },
        'fin.cashPayment': { ar: 'سند صرف', en: 'Cash Payment' },
        'fin.journalEntry': { ar: 'قيد يومية', en: 'Journal Entry' },
        'fin.bankTransfer': { ar: 'تحويل بنكي', en: 'Bank Transfer' },
        'fin.safeTransfer': { ar: 'تحويل خزنة', en: 'Safe Transfer' },
        'fin.checksManagement': { ar: 'إدارة الشيكات', en: 'Check Management' },
        'fin.paymentVoucher': { ar: 'سند دفع', en: 'Payment Voucher' },
        'fin.receiptVoucher': { ar: 'سند استلام', en: 'Receipt Voucher' },
        'fin.amount': { ar: 'المبلغ', en: 'Amount' },
        'fin.description': { ar: 'البيان', en: 'Description' },
        'fin.fromAccount': { ar: 'من حساب', en: 'From Account' },
        'fin.toAccount': { ar: 'إلى حساب', en: 'To Account' },

        // ══════════════════════════════════════════════
        //  MASTER DATA (البيانات الأساسية)
        // ══════════════════════════════════════════════
        'master.items': { ar: 'الأصناف', en: 'Items' },
        'master.customers': { ar: 'العملاء', en: 'Customers' },
        'master.suppliers': { ar: 'الموردين', en: 'Suppliers' },
        'master.accounts': { ar: 'دليل الحسابات', en: 'Chart of Accounts' },
        'master.warehouses': { ar: 'المخازن', en: 'Warehouses' },
        'master.categories': { ar: 'التصنيفات', en: 'Categories' },
        'master.units': { ar: 'الوحدات', en: 'Units' },
        'master.paymentTerms': { ar: 'شروط الدفع', en: 'Payment Terms' },
        'master.addNew': { ar: 'إضافة جديد', en: 'Add New' },
        'master.edit': { ar: 'تعديل', en: 'Edit' },
        'master.delete': { ar: 'حذف', en: 'Delete' },
        'master.save': { ar: 'حفظ', en: 'Save' },
        'master.close': { ar: 'إغلاق', en: 'Close' },
        'master.name': { ar: 'الاسم', en: 'Name' },
        'master.code': { ar: 'الكود', en: 'Code' },
        'master.phone': { ar: 'الهاتف', en: 'Phone' },
        'master.email': { ar: 'البريد الإلكتروني', en: 'Email' },
        'master.address': { ar: 'العنوان', en: 'Address' },
        'master.balance': { ar: 'الرصيد', en: 'Balance' },

        // ══════════════════════════════════════════════
        //  REPORTS (التقارير)
        // ══════════════════════════════════════════════
        'report.generalLedger': { ar: 'دفتر الأستاذ العام', en: 'General Ledger' },
        'report.trialBalance': { ar: 'ميزان المراجعة', en: 'Trial Balance' },
        'report.profitLoss': { ar: 'الأرباح والخسائر', en: 'Profit & Loss' },
        'report.balanceSheet': { ar: 'الميزانية العمومية', en: 'Balance Sheet' },
        'report.accountStatement': { ar: 'كشف حساب', en: 'Account Statement' },
        'report.agingReport': { ar: 'تقرير أعمار الديون', en: 'Aging Report' },
        'report.cashFlow': { ar: 'التدفقات النقدية', en: 'Cash Flow' },
        'report.postingAccounts': { ar: 'ترحيل الحسابات', en: 'Post Accounts' },
        'report.balancesReport': { ar: 'حسابات الأرصدة', en: 'Balance Accounts' },
        'report.reportsAndLists': { ar: 'التقارير والقوائم', en: 'Reports & Lists' },
        'report.aiAnalyst': { ar: 'المحلل المالي الذكي', en: 'AI Financial Analyst' },
        'report.from': { ar: 'من', en: 'From' },
        'report.to': { ar: 'إلى', en: 'To' },
        'report.filter': { ar: 'تصفية', en: 'Filter' },
        'report.export': { ar: 'تصدير', en: 'Export' },
        'report.print': { ar: 'طباعة', en: 'Print' },

        // ══════════════════════════════════════════════
        //  ALERTS (التنبيهات)
        // ══════════════════════════════════════════════
        'alert.title': { ar: 'التنبيهات', en: 'Alerts' },
        'alert.lowStock': { ar: 'أصناف تحت الحد الأدنى', en: 'Items Below Minimum Stock' },
        'alert.overdue': { ar: 'فواتير متأخرة', en: 'Overdue Invoices' },
        'alert.expiringSoon': { ar: 'أصناف قاربت على الانتهاء', en: 'Items Expiring Soon' },

        // ══════════════════════════════════════════════
        //  AUDIT LOG (الصندوق الأسود)
        // ══════════════════════════════════════════════
        'audit.title': { ar: 'سجل تدقيق النظام', en: 'System Audit Log' },
        'audit.user': { ar: 'المستخدم', en: 'User' },
        'audit.action': { ar: 'الإجراء', en: 'Action' },
        'audit.details': { ar: 'التفاصيل', en: 'Details' },
        'audit.timestamp': { ar: 'التاريخ والوقت', en: 'Date & Time' },
        'audit.module': { ar: 'القسم', en: 'Module' },

        // ══════════════════════════════════════════════
        //  COMMON (عام)
        // ══════════════════════════════════════════════
        'common.save': { ar: 'حفظ', en: 'Save' },
        'common.cancel': { ar: 'إلغاء', en: 'Cancel' },
        'common.delete': { ar: 'حذف', en: 'Delete' },
        'common.edit': { ar: 'تعديل', en: 'Edit' },
        'common.add': { ar: 'إضافة', en: 'Add' },
        'common.search': { ar: 'بحث', en: 'Search' },
        'common.filter': { ar: 'تصفية', en: 'Filter' },
        'common.export': { ar: 'تصدير', en: 'Export' },
        'common.import': { ar: 'استيراد', en: 'Import' },
        'common.print': { ar: 'طباعة', en: 'Print' },
        'common.close': { ar: 'إغلاق', en: 'Close' },
        'common.confirm': { ar: 'تأكيد', en: 'Confirm' },
        'common.yes': { ar: 'نعم', en: 'Yes' },
        'common.no': { ar: 'لا', en: 'No' },
        'common.ok': { ar: 'حسناً', en: 'OK' },
        'common.loading': { ar: 'جاري التحميل...', en: 'Loading...' },
        'common.noData': { ar: 'لا توجد بيانات', en: 'No data available' },
        'common.total': { ar: 'الإجمالي', en: 'Total' },
        'common.date': { ar: 'التاريخ', en: 'Date' },
        'common.time': { ar: 'الوقت', en: 'Time' },
        'common.status': { ar: 'الحالة', en: 'Status' },
        'common.actions': { ar: 'إجراءات', en: 'Actions' },
        'common.all': { ar: 'الكل', en: 'All' },
        'common.reset': { ar: 'إعادة تعيين', en: 'Reset' },
        'common.back': { ar: 'رجوع', en: 'Back' },
        'common.next': { ar: 'التالي', en: 'Next' },
        'common.previous': { ar: 'السابق', en: 'Previous' },
        'common.details': { ar: 'التفاصيل', en: 'Details' },
        'common.view': { ar: 'عرض', en: 'View' },
        'common.download': { ar: 'تنزيل', en: 'Download' },
        'common.upload': { ar: 'رفع', en: 'Upload' },
        'common.refresh': { ar: 'تحديث', en: 'Refresh' },
        'common.success': { ar: 'نجاح', en: 'Success' },
        'common.error': { ar: 'خطأ', en: 'Error' },
        'common.warning': { ar: 'تحذير', en: 'Warning' },
        'common.info': { ar: 'معلومة', en: 'Info' },
        'common.required': { ar: 'مطلوب', en: 'Required' },
        'common.optional': { ar: 'اختياري', en: 'Optional' },
        'common.from': { ar: 'من', en: 'From' },
        'common.to': { ar: 'إلى', en: 'To' },

        // ══════════════════════════════════════════════
        //  VOUCHERS HUB (المعاملات المالية)
        // ══════════════════════════════════════════════
        'voucher.title': { ar: 'مركز المعاملات المالية', en: 'Financial Transactions Hub' },
        'voucher.singleReceipt': { ar: 'سند قبض مفرد', en: 'Single Receipt' },
        'voucher.singlePayment': { ar: 'سند صرف مفرد', en: 'Single Payment' },
        'voucher.multiPayment': { ar: 'سند صرف متعدد', en: 'Multi Payment' },
        'voucher.comprehensiveReceipt': { ar: 'سند قبض شامل', en: 'Comprehensive Receipt' },
        'voucher.comprehensivePayment': { ar: 'سند صرف شامل', en: 'Comprehensive Payment' },
        'voucher.journalVoucher': { ar: 'قيد يومية', en: 'Journal Entry' },
        'voucher.bankTransfer': { ar: 'تحويل بنكي', en: 'Bank Transfer' },
        'voucher.safeTransfer': { ar: 'تحويل خزنة', en: 'Safe Transfer' },
        'voucher.checksManagement': { ar: 'إدارة الشيكات', en: 'Check Management' },
        'voucher.payroll': { ar: 'كشف الرواتب', en: 'Payroll' },

    };

    // ─── Core Functions ────────────────────────────────────────

    /** Get current language */
    function getLang() {
        return localStorage.getItem('erp_lang') || 'ar';
    }

    /** Translate key */
    function t(key, fallback) {
        const lang = getLang();
        const entry = translations[key];
        if (entry) return entry[lang] || entry['ar'] || fallback || key;
        return fallback || key;
    }

    /** Set language and reload */
    function setLang(lang) {
        localStorage.setItem('erp_lang', lang);
        applyDirection(lang);
        applyTranslations();
        // Dispatch event so other JS can react
        window.dispatchEvent(new CustomEvent('languageChanged', { detail: { lang: lang } }));
    }

    /** Apply text direction based on language */
    function applyDirection(lang) {
        const html = document.documentElement;
        if (lang === 'en') {
            html.setAttribute('dir', 'ltr');
            html.setAttribute('lang', 'en');
        } else {
            html.setAttribute('dir', 'rtl');
            html.setAttribute('lang', 'ar');
        }
    }

    /** Scan DOM for data-i18n attributes and apply translations */
    function applyTranslations() {
        const lang = getLang();

        // Translate elements with data-i18n attribute
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            const entry = translations[key];
            if (entry) {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    if (el.getAttribute('placeholder')) {
                        el.setAttribute('placeholder', entry[lang] || entry['ar']);
                    } else {
                        el.value = entry[lang] || entry['ar'];
                    }
                } else if (el.tagName === 'OPTION') {
                    el.textContent = entry[lang] || entry['ar'];
                } else {
                    el.textContent = entry[lang] || entry['ar'];
                }
            }
        });

        // Translate elements with data-i18n-placeholder attribute
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            const entry = translations[key];
            if (entry) {
                el.setAttribute('placeholder', entry[lang] || entry['ar']);
            }
        });

        // Translate elements with data-i18n-title attribute
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            const entry = translations[key];
            if (entry) {
                el.setAttribute('title', entry[lang] || entry['ar']);
            }
        });

        // Update page title if data-i18n-page is on <title> tag
        const titleEl = document.querySelector('title[data-i18n]');
        if (titleEl) {
            const key = titleEl.getAttribute('data-i18n');
            const entry = translations[key];
            if (entry) {
                document.title = entry[lang] || entry['ar'];
            }
        }

        // Run the dynamic Arabic -> English DOM replacement engine if language is English
        if (lang === 'en') {
            autoTranslatePage();
        }
    }

    /** Walk DOM and translate all Arabic elements dynamically */
    function autoTranslatePage() {
        const dict = {
            // Sidebar / Nav
            'لوحة التحكم': 'Dashboard',
            'المبيعات': 'Sales',
            'المبيعات المباشرة': 'Direct Sales',
            'المشتريات': 'Purchases',
            'المخازن': 'Inventory',
            'تقارير المخزون والجرد': 'Inventory & Stock Reports',
            'المعاملات المالية': 'Financial Transactions',
            'البيانات الأساسية': 'Master Data',
            'تقارير مالية': 'Financial Reports',
            'التنبيهات': 'Alerts',
            'الإعدادات': 'Settings',
            'أدوات مساعدة': 'Helper Tools',
            'الصندوق الأسود': 'Audit Log',
            'خروج': 'Logout',
            'مقاييس (MAGAIEES)': 'MAGAIEES (ERP)',
            'نظام إدارة الأعمال': 'Business Management System',
            'متصل الآن:': 'Online Now:',
            'سعر الصرف:': 'Exchange Rate:',
            'توسيع الشاشة': 'Toggle Fullscreen',
            'تبديل المظهر': 'Toggle Theme',
            'رجوع': 'Back',
            'تحديث': 'Refresh',

            // Common labels
            'الاسم': 'Name',
            'النوع': 'Type',
            'رقم الهاتف': 'Phone Number',
            'رقم السجل التجاري': 'CR Number',
            'الرقم الضريبي': 'Tax Number',
            'حفظ': 'Save',
            'إلغاء': 'Cancel',
            'حذف': 'Delete',
            'تعديل': 'Edit',
            'عرض': 'View',
            'إضافة': 'Add',
            'بحث': 'Search',
            'الكل': 'All',
            'العملاء': 'Customers',
            'الموردون': 'Suppliers',
            'الموظفون': 'Employees',
            'التفاصيل': 'Details',
            'الحالة': 'Status',
            'التاريخ': 'Date',
            'العملة': 'Currency',
            'الإجمالي': 'Total',
            'المجموع': 'Total',
            'المجموع الفرعي': 'Subtotal',
            'الخصم': 'Discount',
            'الضريبة': 'Tax',
            'الإجمالي النهائي': 'Grand Total',
            'المدفوع': 'Paid',
            'المتبقي': 'Remaining',
            'ملاحظات': 'Notes',
            'طباعة': 'Print',
            'تصدير': 'Export',
            'جاري التحميل...': 'Loading...',
            'لا توجد بيانات': 'No data found',
            'نعم': 'Yes',
            'لا': 'No',
            'حسناً': 'OK',
            'تأكيد': 'Confirm',
            'تم الحفظ بنجاح': 'Saved successfully',
            'تمت الإضافة بنجاح': 'Added successfully',
            'قيمة الضريبة': 'Tax Amount',
            'الخصم الإضافي': 'Additional Discount',
            'تكلفة العمل': 'Labor Cost',
            'تكلفة النقل': 'Transport Cost',
            'المبلغ المدفوع': 'Paid Amount',
            'ملاحظات الفاتورة': 'Invoice Notes',
            'حفظ الفاتورة': 'Save Invoice',
            'طباعة الفاتورة': 'Print Invoice',

            // Settings Screen
            'بيانات الشركة': 'Company Data',
            'المالية والضرائب': 'Finance & Tax',
            'قواعد المخازن': 'Warehouse Rules',
            'إعدادات الطباعة': 'Print Settings',
            'إعدادات البريد SMTP': 'SMTP Settings',
            'ربط الواتساب': 'WhatsApp Integration',
            'المخدمين': 'Servers',
            'اللغة والواجهة': 'Language & Interface',
            'النظام والنسخ': 'System & Backup',
            'اللغة': 'Language',
            'اختر لغة واجهة النظام': 'Select interface language',
            'واجهة المستخدم والمظهر': 'User Interface & Appearance',
            'حجم الخط الرئيسي للنظام (Font Size)': 'Main System Font Size',
            'صغير جداً (14px)': 'Very Small (14px)',
            'متوسط (16px - افتراضي)': 'Medium (16px - Default)',
            'كبير (18px)': 'Large (18px)',
            'كبير جداً (20px)': 'Very Large (20px)',
            'النسخ الاحتياطي التلقائي اليومي': 'Daily Auto Backup',
            'تفعيل الوضع التلقائي الغامق': 'Enable Auto Dark Mode',
            'إظهار الأشكال في الترويسة العليا': 'Show Shapes in Top Header',
            'مدة تسجيل الخروج التلقائي عند عدم النشاط': 'Auto Logout on Inactivity',
            'بدون (معطل)': 'None (Disabled)',
            '30 ثانية': '30 Seconds',
            '1 دقيقة': '1 Minute',
            '5 دقائق': '5 Minutes',
            '10 دقائق': '10 Minutes',
            '30 دقيقة': '30 Minutes',
            'بيانات الشركة الأساسية': 'Basic Company Data',
            'اسم الشركة / المحل': 'Company / Store Name',
            'البريد الإلكتروني': 'Email Address',
            'عنوان الشركة': 'Company Address',
            'شعار الشركة': 'Company Logo',
            'رفع شعار جديد': 'Upload New Logo',
            'حفظ بيانات الشركة': 'Save Company Data',
            'العملة الرئيسية': 'Main Currency',
            'العملة الثانوية (اختياري)': 'Secondary Currency (Optional)',
            'سعر صرف العملة الثانوية': 'Secondary Exchange Rate',
            'نسبة ضريبة القيمة المضافة (%)': 'VAT Rate (%)',
            'الأسعار شاملة الضريبة افتراضياً': 'Prices Include Tax by Default',
            'تفعيل الضريبة على الفواتير': 'Enable Tax on Invoices',
            'حفظ إعدادات المالية': 'Save Finance Settings',
            'السماح بالبيع بالسالب (تجاوز رصيد المخزون)': 'Allow Negative Stock',
            'تنزيل نسخة احتياطية للبيانات': 'Download Data Backup',
            'استعادة من ملف احتياطي سابق': 'Restore from Backup',
            'منطقة الخطر': 'Danger Zone',
            'تصفير بيانات النظام': 'Reset System Data',

            // Partners Screen
            'إضافة شريك جديد': 'Add New Partner',
            'حفظ الشريك': 'Save Partner',
            'عميل (مبيعات)': 'Customer (Sales)',
            'مورد (مشتريات/مصروفات)': 'Supplier (Purchases/Expenses)',
            'موظف': 'Employee',
            'العملاء': 'Customers',
            'الموردون': 'Suppliers',
            'الرصيد:': 'Balance:',
            'إجمالي:': 'Total:',
            'شريك': 'Partner',
            'تعديل الاسم:': 'Edit Name:',
            'تعديل رقم الهاتف:': 'Edit Phone:',
            'هل تريد حذف هذا الشريك نهائياً؟': 'Are you sure you want to delete this partner permanently?',
            'يوجد شريك بنفس الاسم بالفعل!': 'A partner with this name already exists!',
            'الاسم لا يمكن أن يكون فارغاً': 'Name cannot be empty',
            '📱 لا يوجد هاتف': '📱 No phone number',
            'عميل': 'Customer',
            'مورد': 'Supplier',
            'الرصيد': 'Balance',
            
            // Warehouse & Transfers
            'تحويلات المستودعات': 'Warehouse Transfers',
            'تحويل جديد بين المستودعات': 'New Warehouse Transfer',
            'من المستودع': 'From Warehouse',
            'إلى المستودع': 'To Warehouse',
            'رقم المرجع': 'Reference No.',
            'تأكيد تحويل المخزون': 'Confirm Stock Transfer',
            'سجل التحويلات السابقة': 'Previous Transfers Log',
            'الصنف': 'Item',
            'المتاح': 'Available',
            'الكمية المحولة': 'Transferred Qty',
            '-- اختر المستودع --': '-- Select Warehouse --',
            '-- اختر الصنف --': '-- Select Item --',
            'إضافة صنف': 'Add Item',
            'الكمية الإجمالية': 'Total Qty',
            'صنف': 'Item',
            'مستودع': 'Warehouse',
            'تأكيد عملية التحويل': 'Confirm Stock Transfer',
            
            // Purchases & Sales Invoices
            'فاتورة جديدة': 'New Invoice',
            'فاتورة مشتريات': 'Purchase Invoice',
            'استلام بضاعة': 'Receive Goods',
            'استرداد بضاعة': 'Return Goods',
            'عمل الفاتورة': 'Create Invoice',
            'بحث عن صنف...': 'Search for item...',
            'العميل': 'Customer',
            'المورد': 'Supplier',
            'اختر العميل': 'Select Customer',
            'اختر المورد': 'Select Supplier',
            'عميل نقدي': 'Cash Customer',
            'مورد نقدي': 'Cash Supplier',
            'تاريخ الفاتورة': 'Invoice Date',
            'نقدي': 'Cash',
            'آجل': 'Credit',
            'تحويل بنكي': 'Bank Transfer',
            'شيك': 'Cheque',
            'كود الصنف': 'Item Code',
            'اسم الصنف': 'Item Name',
            'الكمية': 'Quantity',
            'السعر': 'Price',
            'الخصم': 'Discount',
            'الضريبة': 'Tax',
            
            // Financial Hub & Vouchers
            'مركز المعاملات المالية': 'Financial Hub',
            'سند قبض مفرد': 'Single Receipt',
            'سند صرف مفرد': 'Single Payment',
            'سند صرف متعدد': 'Multi Payment',
            'سند قبض شامل': 'Comprehensive Receipt',
            'سند صرف شامل': 'Comprehensive Payment',
            'تحويل بنكي': 'Bank Transfer',
            'تحويل خزنة': 'Safe Transfer',
            'إدارة الشيكات': 'Check Management',
            'كشف الرواتب': 'Payroll',
            'من حساب': 'From Account',
            'إلى حساب': 'To Account',
            'المبلغ': 'Amount',
            'البيان': 'Description',
            'تاريخ التحويل': 'Transfer Date',
            'رقم العملية / الشيك / التحويلة': 'Ref / Cheque / Transfer No.',
            'تأكيد عملية التحويل البنكي': 'Confirm Bank Transfer',
            'تأكيد عملية التحويل بين الخزن': 'Confirm Safe Transfer',
            'البيان / الملاحظات': 'Description / Notes',
            'ابحث عن البنك المحول منه...': 'Search source bank...',
            'ابحث عن البنك المحول إليه...': 'Search destination bank...',
            'ابحث عن الخزنة المحول منها...': 'Search source safe...',
            'ابحث عن الخزنة المحول إليها...': 'Search destination safe...',
            
            // Reports Hub
            'دفتر الأستاذ العام': 'General Ledger',
            'ميزان المراجعة': 'Trial Balance',
            'الأرباح والخسائر': 'Profit & Loss',
            'الميزانية العمومية': 'Balance Sheet',
            'كشف حساب': 'Account Statement',
            'تقرير أعمار الديون': 'Aging Report',
            'التدفقات النقدية': 'Cash Flow',
            'ترحيل الحسابات': 'Post Accounts',
            'حسابات الأرصدة': 'Balance Accounts',
            'التقارير والقوائم': 'Reports & Lists',
            'المحلل المالي الذكي': 'Smart Financial Analyst',
            'مستشار AI للأموال': 'AI Money Advisor',
            'بوابة الزكاة': 'Zakat Portal',
            
            // Inventory & Analytics
            'جرد المخازن': 'Stock Taking',
            'الجرد العام': 'General Inventory',
            'منخفض الحركة': 'Slow Moving Items',
            'تحليل الاستراتيجي': 'Strategic Analysis',
            'حركة المخزون': 'Stock Movement',
            'معدل الدوران': 'Turnover Rate',
            'تقرير الجرد الذكي': 'Smart Inventory Report',
            'كشف حركة مخزن': 'Warehouse Movement Log',

            // Table headers & standard terms
            'اسم الحساب': 'Account Name',
            'عليه عملة': 'Debit (Foreign)',
            'له عملة': 'Credit (Foreign)',
            'ملحوظة': 'Note',
            'مدين': 'Debit',
            'دائن': 'Credit',
            'الرمز': 'Code',
            'الاسم العربي': 'Arabic Name',
            'الاسم الإنجليزي': 'English Name',
            'المجموعة': 'Group',
            'الرصيد الافتتاحي': 'Opening Balance',
            'الرصيد الحالي': 'Current Balance',
            'العمليات': 'Actions',
            'سعر التكلفة': 'Cost Price',
            'المستودع الافتراضي': 'Default Warehouse',
            'رقم العملية': 'Transaction No.',
            'الوصف / البيان': 'Description',
            'نوع المستند': 'Doc Type',
            'الرقم': 'No.',
            'سند قبض': 'Receipt Voucher',
            'سند صرف': 'Payment Voucher',
            'قيد يومية': 'Journal Entry',
            'تحويل بنكي': 'Bank Transfer',
            'تحويل خزنة': 'Safe Transfer'
        };

        function translateText(text) {
            const trimmed = text.trim();
            if (!trimmed) return null;
            if (dict[trimmed]) return dict[trimmed];
            return null;
        }

        function walkDOM(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                const translation = translateText(node.nodeValue);
                if (translation !== null) {
                    const leadingSpace = node.nodeValue.match(/^\s*/)[0];
                    const trailingSpace = node.nodeValue.match(/\s*$/)[0];
                    node.nodeValue = leadingSpace + translation + trailingSpace;
                }
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                const tag = node.tagName.toLowerCase();
                if (tag !== 'script' && tag !== 'style') {
                    if (node.hasAttribute('placeholder')) {
                        const placeholder = node.getAttribute('placeholder');
                        const translation = translateText(placeholder);
                        if (translation !== null) {
                            node.setAttribute('placeholder', translation);
                        }
                    }
                    if (node.tagName === 'INPUT' && (node.type === 'button' || node.type === 'submit')) {
                        const val = node.value;
                        const translation = translateText(val);
                        if (translation !== null) {
                            node.value = translation;
                        }
                    }
                    for (let i = 0; i < node.childNodes.length; i++) {
                        walkDOM(node.childNodes[i]);
                    }
                }
            }
        }

        walkDOM(document.body);

        const observer = new MutationObserver((mutations) => {
            observer.disconnect();
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    walkDOM(node);
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    // ─── Auto-initialize on page load ─────────────────────────
    function init() {
        const lang = getLang();
        applyDirection(lang);
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                applyTranslations();
            });
        } else {
            applyTranslations();
        }
    }

    init();

    // ─── Expose to global scope ───────────────────────────────
    window.i18n = {
        t: t,
        getLang: getLang,
        setLang: setLang,
        applyTranslations: applyTranslations,
        translations: translations
    };
    // Shortcut
    window.t = t;
    window.getLang = getLang;
    window.setLang = setLang;

    /** Global Arabic Character Normalizer */
    window.normalizeArabic = function normalizeArabic(s) {
        return String(s || '').toLowerCase()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي');
    };

    /** Global Search Dropdown Text Highlighting Engine */
    window.highlightSearchMatches = function highlightSearchMatches(text, query) {
        if (!text || !query) return text || '';
        const textStr = String(text);
        const rawQuery = String(query).trim();
        if (!rawQuery) return textStr;

        const normText = window.normalizeArabic(textStr);
        const normQuery = window.normalizeArabic(rawQuery);

        const tokens = normQuery.split(/\s+/).filter(Boolean);
        if (tokens.length === 0) return textStr;

        let matches = [];
        tokens.forEach(tok => {
            let pos = 0;
            while ((pos = normText.indexOf(tok, pos)) !== -1) {
                matches.push({ start: pos, end: pos + tok.length });
                pos += Math.max(1, tok.length);
            }
        });

        if (matches.length === 0) return textStr;

        matches.sort((a, b) => a.start - b.start);
        let merged = [matches[0]];
        for (let i = 1; i < matches.length; i++) {
            let last = merged[merged.length - 1];
            let curr = matches[i];
            if (curr.start <= last.end) {
                last.end = Math.max(last.end, curr.end);
            } else {
                merged.push(curr);
            }
        }

        let result = '';
        let lastIndex = 0;
        merged.forEach(m => {
            result += textStr.substring(lastIndex, m.start);
            const matchText = textStr.substring(m.start, m.end);
            result += `<span class="search-highlight-match" style="color: #ff6600 !important; font-weight: 900 !important; font-size: 1.05em !important; background: transparent !important; box-shadow: none !important; border: none !important;">${matchText}</span>`;
            lastIndex = m.end;
        });
        result += textStr.substring(lastIndex);
        return result;
    };

})();

// 🛡️ AUTO-BOOTSTRAP ENTERPRISE PERMISSIONS MANAGER ACROSS ALL I18N SCREENS
(function() {
    if (typeof window.MagaieesPermissions === 'undefined') {
        const existing = document.querySelector('script[src*="permissions-manager"]');
        if (!existing) {
            const ps = document.createElement('script');
            ps.src = 'js/permissions-manager.js?v=' + (window.MAGAIEES_VERSION || '2026_enterprise_v2');
            ps.defer = false;
            if (document.head) {
                document.head.appendChild(ps);
            } else {
                document.addEventListener('DOMContentLoaded', () => document.head.appendChild(ps));
            }
        }
    }
})();

// 🛡️ AUTO-BOOTSTRAP UNIVERSAL BACK BUTTON ACROSS ALL SCREENS
(function() {
    if (typeof window.magaieesGoBack === 'undefined') {
        const existing = document.querySelector('script[src*="magaiees_back"]');
        if (!existing) {
            const bs = document.createElement('script');
            bs.src = 'js/magaiees_back.js?v=' + (window.MAGAIEES_VERSION || '2026_back_universal');
            bs.defer = true;
            if (document.head) {
                document.head.appendChild(bs);
            } else {
                document.addEventListener('DOMContentLoaded', () => document.head.appendChild(bs));
            }
        }
    }
})();



// 🔤 AUTO-BOOTSTRAP 7-LEVEL OPHTHALMIC & PSYCHOLOGICAL TYPOGRAPHY ENGINE ACROSS ALL SCREENS
(function() {
    if (window.MagaieesTypography) return;

    const FONT_KEY_STORAGE = 'magaiees_font_family';
    const SCALE_KEY_STORAGE = 'magaiees_font_scale';
    const WEIGHT_KEY_STORAGE = 'magaiees_font_weight';
    const HIERARCHY_KEY_STORAGE = 'magaiees_typography_hierarchy_v1';
    const PRESET_KEY_STORAGE = 'magaiees_typography_preset';

    const VALID_FONTS = ['default', 'ibm_plex', 'readex_pro', 'almarai'];
    const VALID_LEVEL_FONTS = ['inherit', 'default', 'ibm_plex', 'readex_pro', 'almarai'];
    const VALID_WEIGHTS = ['auto', '400', '500', '600', '700', '800'];
    const LEVELS = ['sidebar', 'flyout', 'titles', 'modals', 'details', 'tables', 'mobile'];
    const BASE_PX_MAP = { sidebar: 15, flyout: 14, titles: 22, modals: 16, details: 15, tables: 15, mobile: 16 };
    const FONT_CSS_MAP = {
        'default': "'Tajawal', 'Cairo', sans-serif",
        'ibm_plex': "'IBM Plex Sans Arabic', 'Inter', sans-serif",
        'readex_pro': "'Readex Pro', 'Inter', sans-serif",
        'almarai': "'Almarai', 'Inter', sans-serif"
    };

    function ensureGoogleFontsAndFallbackCSS() {
        const inject = () => {
            if (!document.head) return;
            if (!document.getElementById('magaiees-google-fonts-pack')) {
                const link = document.createElement('link');
                link.id = 'magaiees-google-fonts-pack';
                link.rel = 'stylesheet';
                link.href = 'https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Readex+Pro:wght@300;400;500;600;700&display=swap';
                document.head.appendChild(link);
            }
            if (!document.getElementById('magaiees-typography-universal-style')) {
                const st = document.createElement('style');
                st.id = 'magaiees-typography-universal-style';
                st.textContent = `
                    html[data-magaiees-font="ibm_plex"] { --magaiees-active-font: 'IBM Plex Sans Arabic', 'Inter', sans-serif; }
                    html[data-magaiees-font="readex_pro"] { --magaiees-active-font: 'Readex Pro', 'Inter', sans-serif; }
                    html[data-magaiees-font="almarai"] { --magaiees-active-font: 'Almarai', 'Inter', sans-serif; }
                    html[data-magaiees-font="ibm_plex"] body, html[data-magaiees-font="readex_pro"] body, html[data-magaiees-font="almarai"] body,
                    html[data-magaiees-font="ibm_plex"] body :not(.fa):not(.fas):not(.far):not(.fab):not(.fa-solid):not(.fa-regular):not(.fa-brands):not([class*="fa-"]):not(.material-icons):not(i):not(.font-preview-sample),
                    html[data-magaiees-font="readex_pro"] body :not(.fa):not(.fas):not(.far):not(.fab):not(.fa-solid):not(.fa-regular):not(.fa-brands):not([class*="fa-"]):not(.material-icons):not(i):not(.font-preview-sample),
                    html[data-magaiees-font="almarai"] body :not(.fa):not(.fas):not(.far):not(.fab):not(.fa-solid):not(.fa-regular):not(.fa-brands):not([class*="fa-"]):not(.material-icons):not(i):not(.font-preview-sample) {
                        font-family: var(--magaiees-active-font) !important;
                        -webkit-font-smoothing: antialiased !important;
                        -moz-osx-font-smoothing: grayscale !important;
                    }
                    html[data-magaiees-scaled="true"] { font-size: calc(16px * var(--magaiees-font-scale, 1)) !important; }
                    html[data-mg-master-weight] body, html[data-mg-master-weight] body td, html[data-mg-master-weight] body p, html[data-mg-master-weight] body span:not(.badge) { font-weight: var(--mg-master-weight) !important; }
                    html[data-mg-lvl-sidebar="1"] .global-sidebar .sidebar-text { font-size: calc(0.95rem * var(--mg-lvl-sidebar-scale, 1)) !important; font-weight: var(--mg-lvl-sidebar-weight, 700) !important; font-family: var(--mg-lvl-sidebar-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-sidebar="1"] .global-sidebar .sidebar-logo-text { font-size: calc(1.05rem * var(--mg-lvl-sidebar-scale, 1)) !important; font-weight: var(--mg-lvl-sidebar-weight, 800) !important; font-family: var(--mg-lvl-sidebar-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-flyout="1"] #magaiees-panoramic-flyout .pf-header-title { font-size: calc(0.92rem * var(--mg-lvl-flyout-scale, 1)) !important; font-weight: var(--mg-lvl-flyout-weight, 800) !important; font-family: var(--mg-lvl-flyout-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-flyout="1"] #magaiees-panoramic-flyout .pf-card-title { font-size: calc(0.85rem * var(--mg-lvl-flyout-scale, 1)) !important; font-weight: var(--mg-lvl-flyout-weight, 800) !important; font-family: var(--mg-lvl-flyout-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-flyout="1"] #magaiees-panoramic-flyout .pf-card-sub { font-size: calc(0.72rem * var(--mg-lvl-flyout-scale, 1)) !important; font-weight: var(--mg-lvl-flyout-weight-sub, 700) !important; font-family: var(--mg-lvl-flyout-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-titles="1"] body h1, html[data-mg-lvl-titles="1"] body .page-title, html[data-mg-lvl-titles="1"] body .magical-glass-header h1, html[data-mg-lvl-titles="1"] body .magical-glass-header h2 { font-size: calc(1.45rem * var(--mg-lvl-titles-scale, 1)) !important; font-weight: var(--mg-lvl-titles-weight, 800) !important; font-family: var(--mg-lvl-titles-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-titles="1"] body h2, html[data-mg-lvl-titles="1"] body h3, html[data-mg-lvl-titles="1"] body .md-card-title, html[data-mg-lvl-titles="1"] body .setting-title, html[data-mg-lvl-titles="1"] body .panel-title, html[data-mg-lvl-titles="1"] body .section-title, html[data-mg-lvl-titles="1"] body .card-title { font-size: calc(1.2rem * var(--mg-lvl-titles-scale, 1)) !important; font-weight: var(--mg-lvl-titles-weight, 800) !important; font-family: var(--mg-lvl-titles-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-titles="1"] body .md-tab-btn, html[data-mg-lvl-titles="1"] body .tab-btn, html[data-mg-lvl-titles="1"] body .sub-tab-btn { font-size: calc(0.95rem * var(--mg-lvl-titles-scale, 1)) !important; font-weight: var(--mg-lvl-titles-weight, 800) !important; font-family: var(--mg-lvl-titles-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-modals="1"] body .modal-title, html[data-mg-lvl-modals="1"] body .modal-header h2, html[data-mg-lvl-modals="1"] body .modal-header h3, html[data-mg-lvl-modals="1"] body [id*="Modal"] h3, html[data-mg-lvl-modals="1"] body [id*="modal"] h3, html[data-mg-lvl-modals="1"] body .swal2-title { font-size: calc(1.22rem * var(--mg-lvl-modals-scale, 1)) !important; font-weight: var(--mg-lvl-modals-weight, 800) !important; font-family: var(--mg-lvl-modals-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-modals="1"] body .modal-content, html[data-mg-lvl-modals="1"] body .modal-body, html[data-mg-lvl-modals="1"] body .modal-body label, html[data-mg-lvl-modals="1"] body .modal-body input, html[data-mg-lvl-modals="1"] body .modal-body select, html[data-mg-lvl-modals="1"] body .modal-body button, html[data-mg-lvl-modals="1"] body [id*="Modal"] label, html[data-mg-lvl-modals="1"] body [id*="Modal"] input, html[data-mg-lvl-modals="1"] body [id*="Modal"] select { font-size: calc(0.95rem * var(--mg-lvl-modals-scale, 1)) !important; font-weight: var(--mg-lvl-modals-weight, 600) !important; font-family: var(--mg-lvl-modals-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-details="1"] body label, html[data-mg-lvl-details="1"] body .input-group label, html[data-mg-lvl-details="1"] body .form-group label, html[data-mg-lvl-details="1"] body .field-label { font-size: calc(0.9rem * var(--mg-lvl-details-scale, 1)) !important; font-weight: var(--mg-lvl-details-weight, 700) !important; font-family: var(--mg-lvl-details-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-details="1"] body input:not([type="checkbox"]):not([type="radio"]):not([type="range"]), html[data-mg-lvl-details="1"] body select, html[data-mg-lvl-details="1"] body textarea, html[data-mg-lvl-details="1"] body .form-control, html[data-mg-lvl-details="1"] body p, html[data-mg-lvl-details="1"] body .btn, html[data-mg-lvl-details="1"] body .action-btn, html[data-mg-lvl-details="1"] body .btn-add { font-size: calc(0.95rem * var(--mg-lvl-details-scale, 1)) !important; font-weight: var(--mg-lvl-details-weight, 600) !important; font-family: var(--mg-lvl-details-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-tables="1"] body table th, html[data-mg-lvl-tables="1"] body thead td, html[data-mg-lvl-tables="1"] body .pro-table th, html[data-mg-lvl-tables="1"] body .data-table th { font-size: calc(0.94rem * var(--mg-lvl-tables-scale, 1)) !important; font-weight: var(--mg-lvl-tables-weight, 800) !important; font-family: var(--mg-lvl-tables-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-tables="1"] body table tbody td, html[data-mg-lvl-tables="1"] body .pro-table td, html[data-mg-lvl-tables="1"] body .data-table td, html[data-mg-lvl-tables="1"] body .num, html[data-mg-lvl-tables="1"] body [data-num] { font-size: calc(0.95rem * var(--mg-lvl-tables-scale, 1)) !important; font-weight: var(--mg-lvl-tables-weight, 600) !important; font-family: var(--mg-lvl-tables-font, var(--magaiees-active-font, inherit)) !important; }
                    html[data-mg-lvl-tables="1"] body table tfoot td, html[data-mg-lvl-tables="1"] body table tfoot th, html[data-mg-lvl-tables="1"] body .total-row td { font-size: calc(1.02rem * var(--mg-lvl-tables-scale, 1)) !important; font-weight: var(--mg-lvl-tables-weight, 800) !important; font-family: var(--mg-lvl-tables-font, var(--magaiees-active-font, inherit)) !important; }
                `;
                document.head.appendChild(st);
            }
        };
        if (document.head) inject();
        else document.addEventListener('DOMContentLoaded', inject, { once: true });
    }

    function getSavedFont() {
        try {
            const f = localStorage.getItem(FONT_KEY_STORAGE) || 'default';
            return VALID_FONTS.includes(f) ? f : 'default';
        } catch (e) { return 'default'; }
    }

    function getSavedScale() {
        try {
            const s = parseInt(localStorage.getItem(SCALE_KEY_STORAGE) || '100', 10);
            return isNaN(s) ? 100 : Math.min(150, Math.max(80, s));
        } catch (e) { return 100; }
    }

    function getSavedWeight() {
        try {
            const w = localStorage.getItem(WEIGHT_KEY_STORAGE) || 'auto';
            return VALID_WEIGHTS.includes(w) ? w : 'auto';
        } catch (e) { return 'auto'; }
    }

    function getDefaultHierarchy() {
        const obj = {};
        LEVELS.forEach(k => { obj[k] = { scale: 100, weight: 'auto', font: 'inherit' }; });
        return obj;
    }

    function getSavedHierarchy() {
        const base = getDefaultHierarchy();
        try {
            const raw = JSON.parse(localStorage.getItem(HIERARCHY_KEY_STORAGE) || '{}');
            LEVELS.forEach(k => {
                if (raw && raw[k]) {
                    const sc = parseInt(raw[k].scale, 10);
                    base[k].scale = isNaN(sc) ? 100 : Math.min(150, Math.max(75, sc));
                    base[k].weight = VALID_WEIGHTS.includes(String(raw[k].weight)) ? String(raw[k].weight) : 'auto';
                    base[k].font = VALID_LEVEL_FONTS.includes(String(raw[k].font)) ? String(raw[k].font) : 'inherit';
                }
            });
        } catch (e) {}
        return base;
    }

    function saveHierarchy(h) {
        try {
            localStorage.setItem(HIERARCHY_KEY_STORAGE, JSON.stringify(h));
            if (h.mobile && h.mobile.scale) {
                const effMob = Math.min(130, Math.max(85, Math.round((getSavedScale() * h.mobile.scale) / 100)));
                localStorage.setItem('magaies_mobile_font_scale_pct', String(effMob));
            }
        } catch (e) {}
    }

    function applyToDOM(fontKey, scalePct, masterWeight, hierarchyObj) {
        const root = document.documentElement;
        if (!root) return;

        // 1. Global Font Family
        if (!fontKey || fontKey === 'default') {
            root.removeAttribute('data-magaiees-font');
            root.style.removeProperty('--magaiees-active-font');
        } else {
            root.setAttribute('data-magaiees-font', fontKey);
            if (FONT_CSS_MAP[fontKey]) root.style.setProperty('--magaiees-active-font', FONT_CSS_MAP[fontKey]);
        }

        // 2. Global Scale
        const pct = Math.min(150, Math.max(80, parseInt(scalePct, 10) || 100));
        const ratio = (pct / 100).toFixed(3);
        const masterPx = (15 * (pct / 100)).toFixed(1) + 'px';
        root.style.setProperty('--magaiees-font-scale', ratio);
        root.style.setProperty('--global-master-font-px', masterPx);
        try { localStorage.setItem('magaies_global_font_px', String(Math.round(15 * (pct / 100)))); } catch (e) {}
        if (pct === 100) {
            root.removeAttribute('data-magaiees-scaled');
            root.style.fontSize = '';
        } else {
            root.setAttribute('data-magaiees-scaled', 'true');
            root.style.fontSize = (16 * (pct / 100)).toFixed(2) + 'px';
        }

        // 3. Global Master Weight
        const mw = masterWeight || getSavedWeight();
        if (mw && mw !== 'auto') {
            root.setAttribute('data-mg-master-weight', mw);
            root.style.setProperty('--mg-master-weight', mw);
        } else {
            root.removeAttribute('data-mg-master-weight');
            root.style.removeProperty('--mg-master-weight');
        }

        // 4. 7 Architectural Levels
        const h = hierarchyObj || getSavedHierarchy();
        LEVELS.forEach(lvl => {
            const cfg = h[lvl] || { scale: 100, weight: 'auto', font: 'inherit' };
            const sc = Math.min(150, Math.max(75, parseInt(cfg.scale, 10) || 100));
            const wt = cfg.weight || 'auto';
            const fn = cfg.font || 'inherit';

            if (sc !== 100 || wt !== 'auto' || fn !== 'inherit') {
                root.setAttribute('data-mg-lvl-' + lvl, '1');
                root.style.setProperty('--mg-lvl-' + lvl + '-scale', (sc / 100).toFixed(3));
                if (wt !== 'auto') {
                    root.style.setProperty('--mg-lvl-' + lvl + '-weight', wt);
                    root.style.setProperty('--mg-lvl-' + lvl + '-weight-sub', wt === '800' ? '700' : wt);
                } else {
                    root.style.removeProperty('--mg-lvl-' + lvl + '-weight');
                    root.style.removeProperty('--mg-lvl-' + lvl + '-weight-sub');
                }
                if (fn !== 'inherit' && FONT_CSS_MAP[fn]) {
                    root.style.setProperty('--mg-lvl-' + lvl + '-font', FONT_CSS_MAP[fn]);
                } else {
                    root.style.removeProperty('--mg-lvl-' + lvl + '-font');
                }
            } else {
                root.removeAttribute('data-mg-lvl-' + lvl);
                root.style.removeProperty('--mg-lvl-' + lvl + '-scale');
                root.style.removeProperty('--mg-lvl-' + lvl + '-weight');
                root.style.removeProperty('--mg-lvl-' + lvl + '-weight-sub');
                root.style.removeProperty('--mg-lvl-' + lvl + '-font');
            }
        });

        updateLivePreviewSimulator(fontKey, pct, mw, h);
    }

    function updateLivePreviewSimulator(fontKey, masterScale, masterWeight, h) {
        const simRoot = document.getElementById('mg-live-hierarchy-sim');
        if (!simRoot) return;
        const globalFam = FONT_CSS_MAP[fontKey || getSavedFont()] || FONT_CSS_MAP['default'];
        const mRatio = (masterScale || 100) / 100;

        LEVELS.forEach(lvl => {
            const cfg = (h && h[lvl]) ? h[lvl] : { scale: 100, weight: 'auto', font: 'inherit' };
            const lRatio = (cfg.scale || 100) / 100;
            const fam = (cfg.font && cfg.font !== 'inherit' && FONT_CSS_MAP[cfg.font]) ? FONT_CSS_MAP[cfg.font] : globalFam;
            const wt = (cfg.weight && cfg.weight !== 'auto') ? cfg.weight : (masterWeight && masterWeight !== 'auto' ? masterWeight : (lvl === 'titles' || lvl === 'flyout' ? '800' : (lvl === 'sidebar' || lvl === 'modals' ? '700' : '600')));
            const basePx = BASE_PX_MAP[lvl] || 15;
            const finalPx = (basePx * mRatio * lRatio).toFixed(1) + 'px';

            document.querySelectorAll('[data-mg-sim-zone="' + lvl + '"]').forEach(el => {
                el.style.fontFamily = fam;
                el.style.fontSize = finalPx;
                el.style.fontWeight = wt;
            });
        });
    }

    function syncUIControls() {
        const currentFont = getSavedFont();
        const currentScale = getSavedScale();
        const currentWeight = getSavedWeight();
        const h = getSavedHierarchy();
        const activePreset = localStorage.getItem(PRESET_KEY_STORAGE) || '';

        // 1. Update 4 Font Cards
        document.querySelectorAll('[data-magaiees-font-card]').forEach(card => {
            const key = card.getAttribute('data-magaiees-font-card');
            const isActive = (key === currentFont);
            card.classList.toggle('active-font-card', isActive);
            card.style.borderColor = isActive ? '#38bdf8' : 'rgba(255,255,255,0.12)';
            card.style.boxShadow = isActive ? '0 0 25px rgba(56, 189, 248, 0.28), inset 0 0 15px rgba(56, 189, 248, 0.1)' : 'none';
            const badge = card.querySelector('.font-active-badge');
            if (badge) badge.style.display = isActive ? 'inline-flex' : 'none';
            const radio = card.querySelector('.font-radio-indicator');
            if (radio) {
                radio.style.background = isActive ? '#38bdf8' : 'transparent';
                radio.style.borderColor = isActive ? '#38bdf8' : '#64748b';
            }
        });

        // 2. Update Master Scale Slider & Labels
        const slider = document.getElementById('magaiees-font-scale-slider');
        if (slider && parseInt(slider.value, 10) !== currentScale) slider.value = currentScale;
        const badgeVal = document.getElementById('magaiees-font-scale-val');
        if (badgeVal) badgeVal.textContent = currentScale + '%';
        const pxLabel = document.getElementById('magaiees-font-scale-px');
        if (pxLabel) pxLabel.textContent = (16 * (currentScale / 100)).toFixed(1) + 'px';

        // 3. Update Master Weight Buttons
        document.querySelectorAll('[data-mg-master-weight-btn]').forEach(btn => {
            const w = btn.getAttribute('data-mg-master-weight-btn');
            const isAct = (w === currentWeight);
            btn.style.background = isAct ? 'linear-gradient(135deg, #0284c7, #2563eb)' : 'rgba(15, 23, 42, 0.78)';
            btn.style.borderColor = isAct ? '#38bdf8' : 'rgba(255,255,255,0.14)';
            btn.style.color = isAct ? '#ffffff' : '#cbd5e1';
        });

        // 4. Update Quick Scale Presets (90%, 100%, 115%, 130%)
        document.querySelectorAll('[data-magaiees-scale-preset]').forEach(btn => {
            const p = parseInt(btn.getAttribute('data-magaiees-scale-preset'), 10);
            const isMatch = (p === currentScale);
            btn.style.background = isMatch ? 'linear-gradient(135deg, #0284c7, #2563eb)' : 'rgba(15, 23, 42, 0.75)';
            btn.style.borderColor = isMatch ? '#38bdf8' : 'rgba(255,255,255,0.15)';
            btn.style.color = isMatch ? '#ffffff' : '#cbd5e1';
            btn.style.boxShadow = isMatch ? '0 4px 14px rgba(56, 189, 248, 0.35)' : 'none';
        });

        // 5. Update Professor Clinical Preset Cards
        document.querySelectorAll('[data-mg-clinical-preset]').forEach(card => {
            const pid = card.getAttribute('data-mg-clinical-preset');
            const isAct = (pid === activePreset);
            card.style.borderColor = isAct ? '#34d399' : 'rgba(255,255,255,0.12)';
            card.style.boxShadow = isAct ? '0 0 22px rgba(16, 185, 129, 0.28)' : 'none';
        });

        // 6. Sync all 7 Architectural Level Cards
        LEVELS.forEach(lvl => {
            const cfg = h[lvl] || { scale: 100, weight: 'auto', font: 'inherit' };
            const scSlider = document.getElementById('mg-lvl-slider-' + lvl);
            if (scSlider && parseInt(scSlider.value, 10) !== cfg.scale) scSlider.value = cfg.scale;

            const scBadge = document.getElementById('mg-lvl-val-' + lvl);
            if (scBadge) scBadge.textContent = cfg.scale + '%';

            const pxBadge = document.getElementById('mg-lvl-px-' + lvl);
            if (pxBadge) {
                const effPx = ((BASE_PX_MAP[lvl] || 15) * (currentScale / 100) * (cfg.scale / 100)).toFixed(1);
                pxBadge.textContent = effPx + 'px';
            }

            const fontSel = document.getElementById('mg-lvl-font-' + lvl);
            if (fontSel && fontSel.value !== cfg.font) fontSel.value = cfg.font;

            document.querySelectorAll('[data-mg-lvl-weight-btn="' + lvl + '"]').forEach(btn => {
                const w = btn.getAttribute('data-weight');
                const isAct = (w === cfg.weight);
                btn.style.background = isAct ? 'linear-gradient(135deg, #0ea5e9, #2563eb)' : 'rgba(2, 6, 23, 0.65)';
                btn.style.borderColor = isAct ? '#38bdf8' : 'rgba(255,255,255,0.14)';
                btn.style.color = isAct ? '#ffffff' : '#94a3b8';
            });
        });

        updateLivePreviewSimulator(currentFont, currentScale, currentWeight, h);
    }

    const CLINICAL_PRESETS = {
        'standard_golden': {
            title: 'التوازن الهرمي الذهبي القياسي (100%)',
            font: 'default', scale: 100, weight: 'auto',
            levels: {
                sidebar: { scale: 100, weight: '700', font: 'inherit' },
                flyout:  { scale: 100, weight: '800', font: 'inherit' },
                titles:  { scale: 105, weight: '800', font: 'inherit' },
                modals:  { scale: 100, weight: '600', font: 'inherit' },
                details: { scale: 100, weight: '600', font: 'inherit' },
                tables:  { scale: 100, weight: '600', font: 'inherit' },
                mobile:  { scale: 100, weight: 'auto', font: 'inherit' }
            }
        },
        'ophthalmic_comfort': {
            title: 'الروشتة الطبية لراحة العين وعدم الإجهاد (Readex Pro + IBM Plex)',
            font: 'readex_pro', scale: 105, weight: '500',
            levels: {
                sidebar: { scale: 104, weight: '600', font: 'readex_pro' },
                flyout:  { scale: 105, weight: '700', font: 'readex_pro' },
                titles:  { scale: 108, weight: '700', font: 'readex_pro' },
                modals:  { scale: 105, weight: '500', font: 'readex_pro' },
                details: { scale: 106, weight: '500', font: 'readex_pro' },
                tables:  { scale: 108, weight: '600', font: 'ibm_plex' },
                mobile:  { scale: 106, weight: '500', font: 'readex_pro' }
            }
        },
        'accounting_bold': {
            title: 'التباين المحاسبي المكثف والتخين بدون نظارة (Almarai + IBM Plex Bold)',
            font: 'almarai', scale: 112, weight: '700',
            levels: {
                sidebar: { scale: 110, weight: '800', font: 'almarai' },
                flyout:  { scale: 112, weight: '800', font: 'almarai' },
                titles:  { scale: 115, weight: '800', font: 'almarai' },
                modals:  { scale: 110, weight: '700', font: 'almarai' },
                details: { scale: 112, weight: '700', font: 'ibm_plex' },
                tables:  { scale: 114, weight: '700', font: 'ibm_plex' },
                mobile:  { scale: 112, weight: '700', font: 'almarai' }
            }
        },
        'laptop_compact': {
            title: 'العرض البانورامي المدمج لشاشات اللابتوب (IBM Plex Crisp)',
            font: 'ibm_plex', scale: 96, weight: '500',
            levels: {
                sidebar: { scale: 95,  weight: '600', font: 'ibm_plex' },
                flyout:  { scale: 96,  weight: '700', font: 'ibm_plex' },
                titles:  { scale: 100, weight: '700', font: 'ibm_plex' },
                modals:  { scale: 98,  weight: '600', font: 'ibm_plex' },
                details: { scale: 97,  weight: '500', font: 'ibm_plex' },
                tables:  { scale: 96,  weight: '600', font: 'ibm_plex' },
                mobile:  { scale: 100, weight: '600', font: 'ibm_plex' }
            }
        }
    };

    window.MagaieesTypography = {
        setFont: function(fontKey, silent) {
            const valid = VALID_FONTS.includes(fontKey) ? fontKey : 'default';
            try {
                localStorage.setItem(FONT_KEY_STORAGE, valid);
                localStorage.setItem('magaies_mobile_font_family', valid);
            } catch (e) {}
            applyToDOM(valid, getSavedScale(), getSavedWeight(), getSavedHierarchy());
            syncUIControls();
            if (!silent && typeof window.showToast === 'function') {
                const names = {
                    'default': 'الخط الافتراضي (Tajawal / Cairo)',
                    'ibm_plex': 'خط IBM Plex Sans Arabic المؤسسي',
                    'readex_pro': 'خط Readex Pro الطبي المريح للعين',
                    'almarai': 'خط Almarai (المراعي) فائق الوضوح'
                };
                window.showToast('تم تفعيل ' + (names[valid] || valid) + ' وتعميمه على كافة الشاشات', 'success');
            }
        },
        setScale: function(scalePct, silent) {
            const pct = Math.min(150, Math.max(80, parseInt(scalePct, 10) || 100));
            try {
                localStorage.setItem(SCALE_KEY_STORAGE, String(pct));
                localStorage.setItem('magaies_mobile_font_scale_pct', String(Math.min(130, Math.max(85, pct))));
            } catch (e) {}
            applyToDOM(getSavedFont(), pct, getSavedWeight(), getSavedHierarchy());
            syncUIControls();
            if (!silent && typeof window.showToast === 'function') {
                window.showToast('تم ضبط مقياس الخط العام على ' + pct + '% في كافة الشاشات', 'info');
            }
        },
        stepScale: function(delta) {
            const next = Math.min(150, Math.max(80, getSavedScale() + (parseInt(delta, 10) || 0)));
            this.setScale(next, false);
        },
        setMasterWeight: function(weight, silent) {
            const w = VALID_WEIGHTS.includes(String(weight)) ? String(weight) : 'auto';
            try { localStorage.setItem(WEIGHT_KEY_STORAGE, w); } catch (e) {}
            applyToDOM(getSavedFont(), getSavedScale(), w, getSavedHierarchy());
            syncUIControls();
            if (!silent && typeof window.showToast === 'function') {
                const wLabels = { 'auto': 'تلقائي ذكي', '400': 'عادي (Regular)', '500': 'متوسط مريح (Medium)', '600': 'شبه تخين (Semi-Bold)', '700': 'تخين (Bold)', '800': 'تخين جداً (Extra-Bold)' };
                window.showToast('تم ضبط سماكة الخط العام: ' + (wLabels[w] || w), 'success');
            }
        },
        setLevelScale: function(levelKey, scalePct, silent) {
            if (!LEVELS.includes(levelKey)) return;
            const h = getSavedHierarchy();
            h[levelKey].scale = Math.min(150, Math.max(75, parseInt(scalePct, 10) || 100));
            saveHierarchy(h);
            applyToDOM(getSavedFont(), getSavedScale(), getSavedWeight(), h);
            syncUIControls();
            if (!silent && typeof window.showToast === 'function') {
                window.showToast('تم تحديث مقاس المستوى إلى ' + h[levelKey].scale + '%', 'info');
            }
        },
        stepLevelScale: function(levelKey, delta) {
            if (!LEVELS.includes(levelKey)) return;
            const h = getSavedHierarchy();
            const next = Math.min(150, Math.max(75, (h[levelKey].scale || 100) + (parseInt(delta, 10) || 0)));
            this.setLevelScale(levelKey, next, false);
        },
        setLevelWeight: function(levelKey, weight, silent) {
            if (!LEVELS.includes(levelKey)) return;
            const h = getSavedHierarchy();
            h[levelKey].weight = VALID_WEIGHTS.includes(String(weight)) ? String(weight) : 'auto';
            saveHierarchy(h);
            applyToDOM(getSavedFont(), getSavedScale(), getSavedWeight(), h);
            syncUIControls();
            if (!silent && typeof window.showToast === 'function') {
                const wLabels = { 'auto': 'تلقائي', '400': 'عادي', '500': 'متوسط مريح', '600': 'شبه تخين', '700': 'تخين (Bold)', '800': 'تخين جداً' };
                window.showToast('تم ضبط سماكة المستوى: ' + (wLabels[h[levelKey].weight] || h[levelKey].weight), 'success');
            }
        },
        setLevelFont: function(levelKey, fontKey, silent) {
            if (!LEVELS.includes(levelKey)) return;
            const h = getSavedHierarchy();
            h[levelKey].font = VALID_LEVEL_FONTS.includes(String(fontKey)) ? String(fontKey) : 'inherit';
            saveHierarchy(h);
            applyToDOM(getSavedFont(), getSavedScale(), getSavedWeight(), h);
            syncUIControls();
            if (!silent && typeof window.showToast === 'function') {
                window.showToast('تم تخصيص نوع الخط لهذا المستوى بنجاح', 'success');
            }
        },
        resetLevel: function(levelKey, silent) {
            if (!LEVELS.includes(levelKey)) return;
            const h = getSavedHierarchy();
            h[levelKey] = { scale: 100, weight: 'auto', font: 'inherit' };
            saveHierarchy(h);
            applyToDOM(getSavedFont(), getSavedScale(), getSavedWeight(), h);
            syncUIControls();
            if (!silent && typeof window.showToast === 'function') {
                window.showToast('تمت إعادة ضبط هذا المستوى إلى الوضع القياسي (100%)', 'info');
            }
        },
        applyClinicalPreset: function(presetId) {
            const p = CLINICAL_PRESETS[presetId];
            if (!p) return;
            try {
                localStorage.setItem(PRESET_KEY_STORAGE, presetId);
                localStorage.setItem(FONT_KEY_STORAGE, p.font);
                localStorage.setItem('magaies_mobile_font_family', p.font);
                localStorage.setItem(SCALE_KEY_STORAGE, String(p.scale));
                localStorage.setItem('magaies_mobile_font_scale_pct', String(Math.min(130, Math.max(85, p.scale))));
                localStorage.setItem(WEIGHT_KEY_STORAGE, p.weight);
                saveHierarchy(p.levels);
            } catch (e) {}
            applyToDOM(p.font, p.scale, p.weight, p.levels);
            syncUIControls();
            if (typeof window.showToast === 'function') {
                window.showToast('🩺 تم تطبيق: ' + p.title, 'success');
            }
        },
        resetDefaults: function() {
            const defH = getDefaultHierarchy();
            try {
                localStorage.setItem(FONT_KEY_STORAGE, 'default');
                localStorage.setItem('magaies_mobile_font_family', 'default');
                localStorage.setItem(SCALE_KEY_STORAGE, '100');
                localStorage.setItem('magaies_mobile_font_scale_pct', '100');
                localStorage.setItem(WEIGHT_KEY_STORAGE, 'auto');
                localStorage.removeItem(PRESET_KEY_STORAGE);
                saveHierarchy(defH);
            } catch (e) {}
            applyToDOM('default', 100, 'auto', defH);
            syncUIControls();
            if (typeof window.showToast === 'function') {
                window.showToast('تمت إعادة ضبط جميع الخطوط والمستويات السبعة إلى الوضع الافتراضي الأصلي (100%)', 'success');
            }
        },
        getState: function() {
            return { font: getSavedFont(), scale: getSavedScale(), weight: getSavedWeight(), hierarchy: getSavedHierarchy() };
        },
        syncUI: syncUIControls
    };

    ensureGoogleFontsAndFallbackCSS();
    applyToDOM(getSavedFont(), getSavedScale(), getSavedWeight(), getSavedHierarchy());

    document.addEventListener('DOMContentLoaded', function() {
        ensureGoogleFontsAndFallbackCSS();
        applyToDOM(getSavedFont(), getSavedScale(), getSavedWeight(), getSavedHierarchy());
        syncUIControls();
    });

    window.addEventListener('storage', function(ev) {
        if ([FONT_KEY_STORAGE, SCALE_KEY_STORAGE, WEIGHT_KEY_STORAGE, HIERARCHY_KEY_STORAGE].includes(ev.key)) {
            applyToDOM(getSavedFont(), getSavedScale(), getSavedWeight(), getSavedHierarchy());
            syncUIControls();
        }
    });
})();
