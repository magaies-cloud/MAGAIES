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
                // Handle different element types
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

})();
