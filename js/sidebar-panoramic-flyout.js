/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 🌟 MAGAIEES WORLD-CLASS PANORAMIC SIDEBAR MEGA-FLYOUT ENGINE (Royal Crystal Prism Edition v3)
 * نظام القوائم المنسدلة البانورامية الكريستالية فائقة الجمال والانسيابية للقائمة الجانبية
 * ═══════════════════════════════════════════════════════════════════════════
 */
(function () {
    'use strict';

    if (window.__MagaieesPanoramicSidebarLoaded) return;
    window.__MagaieesPanoramicSidebarLoaded = true;

    // ── Helper: Read live count from localStorage for Master Data & Modules ──
    function getLiveCount(storageKey, filterFn) {
        try {
            const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey(storageKey) : storageKey;
            const raw = localStorage.getItem(tk) || localStorage.getItem(storageKey);
            if (!raw) return null;
            let arr = JSON.parse(raw);
            if (!Array.isArray(arr) && arr && typeof arr === 'object') arr = Object.values(arr);
            if (!Array.isArray(arr)) return null;
            arr = arr.filter(x => x && !x.deleted);
            if (typeof filterFn === 'function') arr = arr.filter(filterFn);
            return arr.length;
        } catch (e) {
            return null;
        }
    }

    // ── Master Configuration of Panoramic Sub-Menus for Every Sidebar Item ──
    const PANORAMIC_MENUS = {
        'master-data.php': {
            title: 'البيانات الأساسية والتعريفات',
            subtitle: 'الخزن والبنوك، العملاء، الموردين، المخازن، الأصناف، الموظفين، ومراكز التكلفة',
            badge: '8 مكونات رئيسية',
            icon: '🏛️',
            accent: '#f472b6',
            accentRgb: '244, 114, 182',
            secondaryAccent: '#c084fc',
            items: [
                {
                    title: 'الخزن والنقدية',
                    desc: 'تعريف الخزائن الرئيسية والفرعية وأرصدة السيولة',
                    icon: '🏦',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'master-data.php?tab=safes',
                    samePageTab: 'tab-safes',
                    countKey: 'erp_safes_v1',
                    unit: 'خزنة'
                },
                {
                    title: 'البنوك والمحافظ',
                    desc: 'تعريف الحسابات البنكية والتطبيقات المصرفية',
                    icon: '🏛️',
                    color: '#60a5fa',
                    rgb: '96, 165, 250',
                    href: 'master-data.php?tab=banks',
                    samePageTab: 'tab-banks',
                    countKey: 'erp_banks_v1',
                    unit: 'بنك'
                },
                {
                    title: 'العملاء',
                    desc: 'بطاقات العملاء وحدود الائتمان وأرقام الواتساب',
                    icon: '👥',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'master-data.php?tab=customers',
                    samePageTab: 'tab-customers',
                    countKey: 'erp_customers_v1',
                    unit: 'عميل'
                },
                {
                    title: 'الموردين',
                    desc: 'سجلات الموردين والأرصدة الافتتاحية والتوريد',
                    icon: '🤝',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'master-data.php?tab=suppliers',
                    samePageTab: 'tab-suppliers',
                    countKey: 'erp_suppliers_v1',
                    unit: 'مورد'
                },
                {
                    title: 'المخازن والمستودعات',
                    desc: 'تعريف المخازن والمعارض ومستويات السرية',
                    icon: '🏢',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'master-data.php?tab=warehouses',
                    samePageTab: 'tab-warehouses',
                    countKey: 'erp_warehouses_v1',
                    unit: 'مخزن'
                },
                {
                    title: 'الأصناف والمنتجات',
                    desc: 'بطاقات الأصناف والوحدات والتسعير والباركود',
                    icon: '📦',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'master-data.php?tab=items',
                    samePageTab: 'tab-items',
                    countKey: 'erp_items_v1',
                    unit: 'صنف'
                },
                {
                    title: 'الموظفين',
                    desc: 'بيانات الموظفين والرواتب والعهد المالية',
                    icon: '👨‍💼',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'master-data.php?tab=employees',
                    samePageTab: 'tab-employees',
                    countKey: 'erp_employees_v1',
                    unit: 'موظف'
                },
                {
                    title: 'مركز التكلفة',
                    desc: 'مراكز التكلفة للمشاريع والفروع وتحليل الربحية',
                    icon: '🎯',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'master-data.php?tab=costcenters',
                    samePageTab: 'tab-costcenters',
                    countKey: 'erp_cost_centers_v1',
                    unit: 'مركز'
                }
            ]
        },

        'direct-sales.php': {
            title: 'منظومة المبيعات والفواتير',
            subtitle: 'فاتورة مبيعات، فاتورة تقسيم مبيعات، تسليم مبيعات، وفاتورة مبدئية',
            badge: '8 عمليات مبيعات',
            icon: '🛒',
            accent: '#34d399',
            accentRgb: '52, 211, 153',
            secondaryAccent: '#06b6d4',
            items: [
                {
                    title: 'فاتورة مبيعات',
                    desc: 'إنشاء فاتورة مبيعات جديدة فورية (نقدية أو آجلة)',
                    icon: '➕',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'direct-sales.php?action=new',
                    samePageAction: () => { if (typeof window.openInvoiceEditor === 'function') window.openInvoiceEditor('pending_delivery'); },
                    tag: 'فوري ⚡'
                },
                {
                    title: 'فاتورة تقسيم مبيعات',
                    desc: 'تجزئة وتقسيم فاتورة المبيعات إلى فاتورتين مستقلتين',
                    icon: '✂️',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'direct-sales.php?action=split',
                    samePageAction: () => { if (typeof window.openSplitInvoiceView === 'function') window.openSplitInvoiceView(); },
                    tag: 'تقسيم'
                },
                {
                    title: 'تسليم مبيعات',
                    desc: 'اعتماد تسليم فواتير المبيعات والخصم من المخزون',
                    icon: '🚚',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'direct-sales.php?action=delivery',
                    samePageAction: () => { if (typeof window.openDeliveryHub === 'function') window.openDeliveryHub(); },
                    tag: 'تسليم'
                },
                {
                    title: 'فاتورة مبدئية',
                    desc: 'إصدار عرض أسعار مبدئي غير ملزم للعميل',
                    icon: '📋',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'direct-sales.php?action=preliminary',
                    samePageAction: () => { if (typeof window.openInvoiceEditor === 'function') window.openInvoiceEditor('preliminary'); },
                    tag: 'عرض سعر'
                },
                {
                    title: 'فواتير مبيعات آجلة',
                    desc: 'شاشة المبيعات الآجلة التفصيلية وإدارة الذمم',
                    icon: '🧾',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'sales-invoice.php'
                },
                {
                    title: 'استلام وتسليم مبيعات',
                    desc: 'إدارة أذونات استلام وتسليم المبيعات المخزنية',
                    icon: '📦',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'sales-delivery.php'
                },
                {
                    title: 'محفظة العملاء',
                    desc: 'متابعة أرصدة العملاء والسقف الائتماني والمسحوبات',
                    icon: '💎',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'customer-portfolio.php'
                },
                {
                    title: 'حجوزات المبيعات',
                    desc: 'إدارة البضائع المحجوزة للعملاء وجدولة التسليم',
                    icon: '🔖',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'reserved-sales.php'
                }
            ]
        },

        'purchase.php': {
            title: 'منظومة المشتريات والتوريد',
            subtitle: 'فاتورة مشتريات، استلام بضاعة، استرداد بضاعة، فاتورة مبدئية، ومسح فاتورة',
            badge: '6 عمليات مشتريات',
            icon: '🛍️',
            accent: '#fb923c',
            accentRgb: '251, 146, 60',
            secondaryAccent: '#f59e0b',
            items: [
                {
                    title: 'فاتورة مشتريات',
                    desc: 'تسجيل فاتورة مشتريات جديدة وتحديث المخزون',
                    icon: '💵',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'purchase.php?action=direct',
                    samePageAction: () => { if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('direct'); },
                    tag: 'شراء مباشر'
                },
                {
                    title: 'استلام بضاعة',
                    desc: 'إذن استلام وفحص شحنات البضائع الواردة للمخازن',
                    icon: '🚛',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'purchase-receipt.php',
                    tag: 'استلام مخزني'
                },
                {
                    title: 'استرداد بضاعة',
                    desc: 'مرتجع مشتريات واسترداد بضاعة للموردين',
                    icon: '↩️',
                    color: '#f43f5e',
                    rgb: '244, 63, 94',
                    href: 'purchase-return.php',
                    tag: 'مرتجع'
                },
                {
                    title: 'فاتورة مبدئية',
                    desc: 'إنشاء فاتورة مشتريات مبدئية وأمر شراء (Proforma)',
                    icon: '📋',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'purchase.php?action=proforma',
                    samePageAction: () => { if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('proforma'); },
                    tag: 'مبدئية'
                },
                {
                    title: 'مسح فاتورة مشتريات',
                    desc: 'قراءة وتفريغ الفاتورة تلقائياً بالكاميرا والذكاء',
                    icon: '📷',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'purchase.php?action=ocr',
                    samePageAction: () => { if (typeof window.triggerInvoiceOCR === 'function') window.triggerInvoiceOCR(); },
                    tag: 'ذكاء AI'
                },
                {
                    title: 'اعتمادية الموردين',
                    desc: 'تقييم التزام الموردين والأسعار وجودة التوريد',
                    icon: '🏆',
                    color: '#60a5fa',
                    rgb: '96, 165, 250',
                    href: 'supplier_reliability.php'
                }
            ]
        },

        'analytics.php': {
            title: 'التقارير المالية والمحاسبية الشاملة',
            subtitle: 'الحسابات العامة، مركز الأرصدة والمقاييس، القوائم الختامية، وكشوفات الحساب',
            badge: '8 مراكز تقارير',
            icon: '📊',
            accent: '#38bdf8',
            accentRgb: '56, 189, 248',
            secondaryAccent: '#818cf8',
            items: [
                {
                    title: 'تقرير الحسابات العامة',
                    desc: 'دفتر الأستاذ العام (GL) وحسابات الأرصدة المجمعة',
                    icon: '📓',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'general_accounts_hub.php',
                    tag: 'الحسابات'
                },
                {
                    title: 'مركز الأرصدة والمقاييس',
                    desc: 'أرصدة العملاء والموردين التفاعلية وميزان المقاييس',
                    icon: '⚖️',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'balances_modern_hub.php',
                    tag: 'الأرصدة'
                },
                {
                    title: 'القوائم الختامية والمحاسبية',
                    compactTitle: true,
                    desc: 'ميزان المراجعة وقائمة الأرباح والخسائر والقيود',
                    icon: '🏛️',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'financial-reports.php',
                    tag: 'ختامي'
                },
                {
                    title: 'بوابة التقارير الذكية',
                    desc: 'تحليل التدفقات النقدية ونقطة التعادل والمؤشرات',
                    icon: '🚀',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'smart_reports_hub.php',
                    tag: 'ذكي V2'
                },
                {
                    title: 'تقارير العملاء والموردين',
                    desc: 'الموقف المالي الشامل وكشوفات الحساب للشركاء',
                    icon: '🤝',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'partners_reports_hub.php',
                    tag: 'الشركاء'
                },
                {
                    title: 'كشف حساب تفصيلي',
                    desc: 'كشف حركة ومطابقة حساب عميل أو مورد أو خزنة',
                    icon: '📑',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'account-statement.php'
                },
                {
                    title: 'التقرير اليومي والديون',
                    desc: 'خلاصة حركات اليومية الشاملة وجدولة أعمار الديون',
                    icon: '📅',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'daily_master_report.php'
                },
                {
                    title: 'لوحة التحليلات المالية',
                    desc: 'المؤشرات البيانية لحركة المبيعات والمشتريات',
                    icon: '📈',
                    color: '#60a5fa',
                    rgb: '96, 165, 250',
                    href: 'analytics.php'
                }
            ]
        },

        'vouchers-hub.php': {
            title: 'المعاملات المالية والسندات',
            subtitle: 'حركة مالية، صرف مجمع، قيد يومية، دورة الشيكات، وترحيل الحسابات',
            badge: '8 أوعية مالية',
            icon: '💰',
            accent: '#fbbf24',
            accentRgb: '251, 191, 36',
            secondaryAccent: '#f59e0b',
            items: [
                {
                    title: 'حركة مالية',
                    desc: 'إصدار سند قبض أو سند صرف نقدي وبنكي مفرد',
                    icon: '💸',
                    color: '#f43f5e',
                    rgb: '244, 63, 94',
                    href: 'single-cash-payment.php',
                    tag: 'سند مفرد'
                },
                {
                    title: 'صرف وقبض مجمع',
                    desc: 'سند صرف أو قبض متعدد الجهات لعدة حسابات معاً',
                    icon: '🧾',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'multi-cash-payment.php',
                    tag: 'متعدد الجهات'
                },
                {
                    title: 'قيد يومية',
                    desc: 'تسجيل القيود المحاسبية المزدوجة والتسويات المالية',
                    icon: '📝',
                    color: '#a855f7',
                    rgb: '168, 85, 247',
                    href: 'journal-voucher.php',
                    tag: 'قيود'
                },
                {
                    title: 'دورة الشيكات',
                    desc: 'متابعة الشيكات الواردة والصادرة والتحصيل والتظهير',
                    icon: '💳',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'checks-cycle.php',
                    tag: 'شيكات'
                },
                {
                    title: 'ترحيل الحسابات',
                    desc: 'اعتماد ترحيل السندات وإدارة شجرة الحسابات',
                    icon: '🔄',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'posting-accounts.php',
                    tag: 'ترحيل'
                },
                {
                    title: 'تحويل بين الخزن',
                    desc: 'المناقلة المالية الفورية بين الخزائن والبنوك',
                    icon: '🔁',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'safe-transfer.php'
                },
                {
                    title: 'كشف الخزن والبنوك',
                    desc: 'مراقبة السيولة وحركة الوارد والمنصرف لكل خزنة',
                    icon: '🏦',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'cash-bank-statement.php'
                },
                {
                    title: 'تحليل مالي بالذكاء الاصطناعي',
                    compactTitle: true,
                    desc: 'مستشار الذكاء الاصطناعي للسيولة والتدفقات النقدية',
                    icon: '🤖',
                    color: '#10b981',
                    rgb: '16, 185, 129',
                    href: 'ai_money_advisor.php',
                    tag: 'ذكاء AI'
                }
            ]
        },

        'inventory-report.php': {
            title: 'إدارة المخازن والمستودعات والجرد',
            subtitle: 'حركات البضائع، حركة الأصناف، حركة المخازن، ملخص الحركات، والجرد الشامل',
            badge: '8 شاشات مخزنية',
            icon: '📦',
            accent: '#a78bfa',
            accentRgb: '167, 139, 250',
            secondaryAccent: '#6366f1',
            items: [
                {
                    title: 'حركات البضائع',
                    desc: 'تفاصيل الكميات والقيم لحركات الوارد والمنصرف',
                    icon: '📦',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'inventory-movement.php',
                    tag: 'وارد وصادر'
                },
                {
                    title: 'حركة الأصناف',
                    desc: 'كارت الصنف التفصيلي والرصيد التراكمي لكل صنف',
                    icon: '🏷️',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'item-ledger.php',
                    tag: 'كارت الصنف'
                },
                {
                    title: 'حركة المخازن',
                    desc: 'كشف المستودعات والجرد التفصيلي لكل مخزن',
                    icon: '🏢',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'warehouse-ledger.php',
                    tag: 'المستودعات'
                },
                {
                    title: 'ملخص الحركات',
                    desc: 'نظرة عامة وتجميعية شاملة على كميات المخزون',
                    icon: '📊',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'inventory-summary.php',
                    tag: 'ملخص شامل'
                },
                {
                    title: 'التحليلات المرئية',
                    desc: 'مؤشرات ورسوم بيانية وتقييم الأصناف ABC',
                    icon: '📈',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'inventory-analytics.php'
                },
                {
                    title: 'الجرد الشامل',
                    desc: 'تدقيق ومطابقة الكميات الفعلية بالمخازن',
                    icon: '🔍',
                    color: '#f87171',
                    rgb: '248, 113, 113',
                    href: 'inventory-stocktake.php',
                    tag: 'جرد فعلي'
                },
                {
                    title: 'تسوية جردية',
                    desc: 'معالجة فروقات العجز والزيادة واعتماد التسويات',
                    icon: '⚖️',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'inventory-adjustment.php'
                },
                {
                    title: 'تقارير المخزن الذكية',
                    compactTitle: true,
                    desc: 'الـ 20 تقرير مخزني متقدم ومستشعر الذكاء AI',
                    icon: '🤖',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'smart_inventory_report.php',
                    tag: '20 تقرير'
                }
            ]
        },

        'magaiees_unified_dashboard.php': {
            title: 'لوحة التحكم المركزية وغرفة القيادة',
            subtitle: 'الرؤية البانورامية الشاملة للمؤشرات، المستشار الذكي، الأرصدة، والمهام',
            badge: '6 بوابات قيادة',
            icon: '🎛️',
            accent: '#22d3ee',
            accentRgb: '34, 211, 238',
            secondaryAccent: '#38bdf8',
            items: [
                {
                    title: 'لوحة التحكم الموحدة',
                    desc: 'الشاشة الرئيسية الشاملة لمؤشرات الأداء والوصول السريع',
                    icon: '🎛️',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'magaiees_unified_dashboard.php',
                    tag: 'الرئيسية'
                },
                {
                    title: 'مستشار AI للبضائع',
                    desc: 'توصيات الشراء الذكية وكشف الرواكد وتوقعات الطلب',
                    icon: '🤖',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'ai_goods_advisor.php',
                    tag: 'ذكاء البضائع'
                },
                {
                    title: 'مستشار AI للأموال',
                    desc: 'تحليل التدفقات النقدية والربحية وتحصيل الديون',
                    icon: '💡',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'ai_money_advisor.php',
                    tag: 'ذكاء الأموال'
                },
                {
                    title: 'تقرير الأرصدة الأساسية',
                    desc: 'ملخص فوري لأرصدة الخزن والبنوك والعملاء والموردين',
                    icon: '⚖️',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'mainbalances-report.php'
                },
                {
                    title: 'إدارة المهام والأعمال',
                    desc: 'تنظيم ومتابعة المهام اليومية وتكليفات فريق العمل',
                    icon: '✅',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'jobs.php'
                },
                {
                    title: 'سجل العمليات والرقابة',
                    desc: 'متعقب المستندات وسجل نشاط المستخدمين اللحظي',
                    icon: '🛡️',
                    color: '#f87171',
                    rgb: '248, 113, 113',
                    href: 'audit-log.php'
                }
            ]
        },

        'alarm.php': {
            title: 'منظومة التنبيهات والإنذار المبكر',
            subtitle: 'الرقابة الآلية على الأسعار، حدود الائتمان، نواقص المخزون، والديون المتأخرة',
            badge: '4 رادارات رقابية',
            icon: '🔔',
            accent: '#f87171',
            accentRgb: '248, 113, 113',
            secondaryAccent: '#fb7185',
            items: [
                {
                    title: 'رادار التنبيهات المبكر',
                    desc: 'كشف اختلالات التسعير وتجاوز الائتمان والتنبيهات الفورية',
                    icon: '🚨',
                    color: '#f87171',
                    rgb: '248, 113, 113',
                    href: 'alarm.php',
                    tag: 'فحص حي'
                },
                {
                    title: 'مدير تنبيهات التقارير',
                    desc: 'إدارة وتخصيص قواعد التنبيه والإشعارات الذكية بالنظام',
                    icon: '🔔',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'alerts_manager.php',
                    tag: 'القواعد'
                },
                {
                    title: 'تنبيهات المخازن والنواقص',
                    desc: 'الأصناف التي وصلت لحد الطلب والأصناف الراكدة',
                    icon: '📦',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'smart_inventory_report.php'
                },
                {
                    title: 'تقرير أعمار الديون',
                    desc: 'رصد المديونيات المتأخرة وجدولة المتابعة والتحصيل',
                    icon: '⏳',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'aging-report.php'
                }
            ]
        },

        'engineering_tools.php': {
            title: 'المساعد الذكي والأدوات المساندة',
            subtitle: 'المساعد الذكي التفاعلي، استوديو القرآن الكريم، حاسبة الميراث، والأدوات الهندسية',
            badge: '6 أجنحة ذكية',
            icon: '🤖',
            accent: '#ec4899',
            accentRgb: '236, 72, 153',
            secondaryAccent: '#a855f7',
            items: [
                {
                    title: 'المساعد الذكي التفاعلي',
                    desc: 'محادثة ذكية للإجابة عن الأسئلة وتنفيذ الأوامر الفورية',
                    icon: '🤖',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'engineering_tools.php?action=chatbot',
                    samePageAction: () => {
                        const chatToggle = document.getElementById('chatbot-toggle-btn') || document.querySelector('.chatbot-fab, #ai-assistant-btn, [onclick*="toggleChatbot"]');
                        if (chatToggle) { chatToggle.click(); return true; }
                        return false;
                    },
                    tag: 'مساعد AI'
                },
                {
                    title: 'استوديو القرآن والبث الحي',
                    desc: 'تلاوات كبار القراء وقنوات مكة المكرمة والمدينة المنورة',
                    icon: '🕋',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'engineering_tools.php?tab=quran_mp3',
                    tag: 'بث مباشر'
                },
                {
                    title: 'حاسبة الميراث والزكاة',
                    desc: 'تقسيم التركات والمواريث الشرعية وحساب نصاب الزكاة',
                    icon: '⚖️',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'engineering_tools.php?tab=inheritance',
                    tag: 'المواريث'
                },
                {
                    title: 'الموسوعة الفقهية والفتاوى',
                    desc: 'مكتبة الفتاوى الشرعية وفقه المعاملات المالية والتجارية',
                    icon: '📜',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'engineering_tools.php?tab=fiqh'
                },
                {
                    title: 'الأدوات الهندسية والحاسبات',
                    desc: 'محولات القياس وحسابات الأوزان والمساحات الفنية',
                    icon: '🧰',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'engineering_tools.php'
                },
                {
                    title: 'مستشار الذكاء الاصطناعي',
                    compactTitle: true,
                    desc: 'الانتقال السريع لمستشار البضائع ومستشار الأموال',
                    icon: '🧠',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'ai_goods_advisor.php'
                }
            ]
        },

        'settings.php': {
            title: 'إعدادات النظام والأمان والصلاحيات',
            subtitle: 'ضبط قواعد العمل، إدارة المستخدمين والأدوار، السمات السبعة، والنسخ الاحتياطي',
            badge: '6 إعدادات جوهرية',
            icon: '⚙️',
            accent: '#a3e635',
            accentRgb: '163, 230, 53',
            secondaryAccent: '#10b981',
            items: [
                {
                    title: 'إعدادات النظام العامة',
                    desc: 'بيانات المنشأة والعملات والضرائب وقواعد المخزون',
                    icon: '⚙️',
                    color: '#a3e635',
                    rgb: '163, 230, 53',
                    href: 'settings.php',
                    tag: 'الضبط العام'
                },
                {
                    title: 'المستخدمون والصلاحيات',
                    desc: 'إدارة حسابات الموظفين والأدوار الرقابية والشاشات',
                    icon: '🔐',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'users-roles.php',
                    tag: 'الأمان'
                },
                {
                    title: 'السمات السبعة المعتمدة',
                    desc: 'التبديل الفوري بين المظاهر الفاتحة والداكنة المريحة',
                    icon: '🎨',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'settings.php?action=themes',
                    samePageAction: () => {
                        if (typeof window.openMaster7ThemesModal === 'function') {
                            window.openMaster7ThemesModal();
                            return true;
                        }
                        return false;
                    },
                    tag: '7 سمات'
                },
                {
                    title: 'النسخ الاحتياطي والاستعادة',
                    desc: 'حفظ نسخ احتياطية مشفرة واستعادة قواعد البيانات',
                    icon: '💾',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'backup.php',
                    tag: 'حماية'
                },
                {
                    title: 'سجل الرقابة والتدقيق',
                    desc: 'مراجعة كافة العمليات والتعديلات وحركات الدخول',
                    icon: '🛡️',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'audit-log.php'
                },
                {
                    title: 'تصميم وطباعة الباركود',
                    desc: 'تخصيص ملصقات الباركود للأصناف والطباعة الحرارية',
                    icon: '🖨️',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'barcode-print.php'
                }
            ]
        }
    };

    // Alias dashboard-pro.php to unified dashboard
    PANORAMIC_MENUS['dashboard-pro.php'] = PANORAMIC_MENUS['magaiees_unified_dashboard.php'];

    // ── Inject World-Class Panoramic CSS (v3: Ultra-Wide Unobstructed White Titles, Glitter Prism & Side-Edge Illumination) ──
    function injectPanoramicStyles() {
        if (document.getElementById('magaiees-panoramic-sidebar-css')) return;
        const style = document.createElement('style');
        style.id = 'magaiees-panoramic-sidebar-css';
        style.textContent = `
            /* ── Keep Sidebar Expanded While Flyout is Open ── */
            .global-sidebar.panoramic-flyout-open {
                width: var(--sb-expanded, 240px) !important;
                border-left-color: rgba(56, 189, 248, 0.55) !important;
            }
            .global-sidebar.panoramic-flyout-open .sidebar-logo-text,
            .global-sidebar.panoramic-flyout-open .sidebar-text {
                max-width: 180px !important;
                opacity: 1 !important;
            }

            /* ── Sidebar Item Hover & Active Enhancement ── */
            .global-sidebar .sidebar-item {
                position: relative;
                transition: background 0.25s cubic-bezier(0.16, 1, 0.3, 1), color 0.25s ease, transform 0.25s ease !important;
            }
            .global-sidebar .sidebar-item.panoramic-hovered {
                background: linear-gradient(90deg, rgba(var(--sb-hover-rgb, 56, 189, 248), 0.24) 0%, rgba(var(--sb-hover-rgb, 56, 189, 248), 0.06) 100%) !important;
                color: #ffffff !important;
                box-shadow: inset 0 0 18px rgba(var(--sb-hover-rgb, 56, 189, 248), 0.18);
            }
            .global-sidebar .sidebar-item.panoramic-hovered::after {
                content: '';
                position: absolute;
                left: 0;
                top: 14%;
                height: 72%;
                width: 4px;
                border-radius: 0 6px 6px 0;
                background: var(--sb-hover-color, #38bdf8);
                box-shadow: 0 0 16px var(--sb-hover-color, #38bdf8), 0 0 6px #ffffff;
            }
            .global-sidebar .sidebar-item .sb-panoramic-chevron {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%) translateX(6px);
                font-size: 11px;
                color: rgba(255, 255, 255, 0.65);
                opacity: 0;
                transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
                pointer-events: none;
            }
            .global-sidebar:hover .sidebar-item .sb-panoramic-chevron,
            .global-sidebar.panoramic-flyout-open .sidebar-item .sb-panoramic-chevron {
                opacity: 0.75;
                transform: translateY(-50%) translateX(0);
            }
            .global-sidebar .sidebar-item.panoramic-hovered .sb-panoramic-chevron {
                opacity: 1 !important;
                color: #ffffff !important;
                transform: translateY(-50%) translateX(-5px) scale(1.25) !important;
                filter: drop-shadow(0 0 8px var(--sb-hover-color, #38bdf8));
            }

            /* ═══════════════════════════════════════════════════════════════════
               🌌 THE PANORAMIC HOLOGRAPHIC MEGA-FLYOUT CONTAINER (740px WIDE)
               ═══════════════════════════════════════════════════════════════════ */
            #magaiees-panoramic-flyout {
                position: fixed;
                right: calc(var(--sb-expanded, 240px) + 12px);
                top: 18px;
                width: min(740px, calc(100vw - 264px));
                z-index: 999998;
                direction: rtl;
                font-family: 'Tajawal', 'Cairo', sans-serif;
                border-radius: 26px;
                padding: 20px 22px;
                box-sizing: border-box;
                background:
                    radial-gradient(circle at 90% 10%, rgba(var(--pf-accent-rgb, 56, 189, 248), 0.28) 0%, transparent 52%),
                    radial-gradient(circle at 10% 90%, rgba(var(--pf-accent-rgb, 56, 189, 248), 0.18) 0%, transparent 52%),
                    linear-gradient(145deg, rgba(8, 12, 26, 0.96) 0%, rgba(14, 21, 40, 0.97) 50%, rgba(6, 10, 22, 0.99) 100%);
                backdrop-filter: blur(42px) saturate(225%);
                -webkit-backdrop-filter: blur(42px) saturate(225%);
                border: 1.8px solid rgba(var(--pf-accent-rgb, 56, 189, 248), 0.55);
                border-right: 4.5px solid var(--pf-accent, #38bdf8);
                border-left: 3px solid rgba(var(--pf-accent-rgb, 56, 189, 248), 0.5);
                box-shadow:
                    -28px 32px 90px rgba(0, 0, 0, 0.88),
                    0 0 55px rgba(var(--pf-accent-rgb, 56, 189, 248), 0.28),
                    inset 0 1px 2px rgba(255, 255, 255, 0.28),
                    inset 0 -1px 2px rgba(var(--pf-accent-rgb, 56, 189, 248), 0.2);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateX(20px) scale(0.96);
                transform-origin: right center;
                transition:
                    opacity 0.24s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.34s cubic-bezier(0.16, 1, 0.3, 1),
                    top 0.32s cubic-bezier(0.16, 1, 0.3, 1),
                    border-color 0.28s ease,
                    box-shadow 0.28s ease,
                    visibility 0.24s;
            }

            #magaiees-panoramic-flyout.is-visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(0) scale(1);
            }

            /* Invisible Safe-Hover Bridge between Sidebar and Flyout */
            #magaiees-panoramic-flyout::before {
                content: '';
                position: absolute;
                top: -24px;
                bottom: -24px;
                right: -28px;
                width: 32px;
                background: transparent;
                pointer-events: auto;
            }

            /* Glowing Laser Pointer Notch aligned with the hovered sidebar item */
            #magaiees-panoramic-flyout .pf-laser-pointer {
                position: absolute;
                right: -9px;
                top: var(--pf-pointer-y, 44px);
                width: 16px;
                height: 16px;
                background: var(--pf-accent, #38bdf8);
                border-radius: 4px;
                transform: translateY(-50%) rotate(45deg);
                box-shadow: 0 0 22px var(--pf-accent, #38bdf8), 0 0 8px #ffffff;
                transition: top 0.28s cubic-bezier(0.16, 1, 0.3, 1), background 0.25s ease;
                pointer-events: none;
                z-index: 2;
            }

            /* ── Panoramic Top Header Banner ── */
            .pf-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                padding: 13px 16px;
                margin-bottom: 14px;
                border-radius: 18px;
                background: linear-gradient(135deg, rgba(var(--pf-accent-rgb, 56, 189, 248), 0.24) 0%, rgba(255, 255, 255, 0.05) 100%);
                border: 1px solid rgba(var(--pf-accent-rgb, 56, 189, 248), 0.42);
                box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.22), 0 6px 20px rgba(0, 0, 0, 0.28);
                position: relative;
                overflow: hidden;
            }
            .pf-header::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 55%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.16), transparent);
                transform: skewX(-20deg);
                animation: pfHeaderShine 5.5s infinite ease-in-out;
                pointer-events: none;
            }
            @keyframes pfHeaderShine {
                0%, 100% { left: -100%; }
                45%, 55% { left: 160%; }
            }

            .pf-header-main {
                display: flex;
                align-items: center;
                gap: 14px;
                min-width: 0;
                flex: 1;
            }
            .pf-header-emblem {
                width: 50px;
                height: 50px;
                min-width: 50px;
                border-radius: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.65rem;
                background: linear-gradient(135deg, rgba(var(--pf-accent-rgb, 56, 189, 248), 0.4), rgba(15, 23, 42, 0.9));
                border: 1.5px solid var(--pf-accent, #38bdf8);
                box-shadow: 0 6px 22px rgba(var(--pf-accent-rgb, 56, 189, 248), 0.45), inset 0 1px 3px rgba(255, 255, 255, 0.45);
            }
            .pf-header-titles {
                min-width: 0;
                flex: 1;
            }
            .pf-header-title {
                margin: 0 0 4px 0;
                font-size: 1.2rem;
                font-weight: 900;
                color: #ffffff !important;
                text-shadow: 0 2px 10px rgba(var(--pf-accent-rgb, 56, 189, 248), 0.5);
                letter-spacing: 0.2px;
            }
            .pf-header-subtitle {
                margin: 0;
                font-size: 0.81rem;
                font-weight: 600;
                color: #e2e8f0;
                line-height: 1.35;
            }
            .pf-header-badge {
                flex-shrink: 0;
                padding: 6px 14px;
                border-radius: 999px;
                font-size: 0.78rem;
                font-weight: 900;
                color: #ffffff;
                background: linear-gradient(135deg, rgba(var(--pf-accent-rgb, 56, 189, 248), 0.38), rgba(15, 23, 42, 0.9));
                border: 1.5px solid var(--pf-accent, #38bdf8);
                box-shadow: 0 0 18px rgba(var(--pf-accent-rgb, 56, 189, 248), 0.38);
                white-space: nowrap;
            }

            /* ── Panoramic 2-Column Wide Bento Grid ── */
            .pf-bento-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 13px;
                max-height: calc(100vh - 175px);
                overflow-y: auto;
                overflow-x: visible;
                padding: 8px 6px 14px 6px;
            }
            .pf-bento-grid::-webkit-scrollbar {
                width: 5px;
            }
            .pf-bento-grid::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.22);
                border-radius: 5px;
            }

            /* ── Individual Panoramic Bento Card (Steady, High-Contrast, Deep Black on Hover + Bottom Beam) ── */
            .pf-bento-card {
                position: relative;
                display: flex;
                align-items: center;
                gap: 14px;
                padding: 13px 16px;
                min-height: 76px;
                border-radius: 16px;
                text-decoration: none !important;
                opacity: 1 !important;
                visibility: visible !important;
                background: linear-gradient(180deg, rgba(20, 29, 52, 0.94) 0%, rgba(12, 18, 34, 0.97) 100%) !important;
                border: 1.5px solid rgba(var(--card-rgb, 56, 189, 248), 0.35) !important;
                border-bottom: 3.5px solid rgba(var(--card-rgb, 56, 189, 248), 0.72) !important;
                box-shadow:
                    0 4px 14px rgba(0, 0, 0, 0.45),
                    0 3px 10px -2px rgba(var(--card-rgb, 56, 189, 248), 0.18) !important;
                cursor: pointer;
                overflow: hidden;
                z-index: 1;
                transform: translateY(0) scale(1);
                transition:
                    transform 0.18s ease-out,
                    border-color 0.18s ease-out,
                    background 0.18s ease-out,
                    box-shadow 0.18s ease-out !important;
                animation: none !important;
            }

            /* Bottom Luminous Ray / Beam Underneath the Card (Shines from below, never covers text) */
            .pf-bento-card::before {
                content: '';
                position: absolute;
                bottom: 0;
                left: 8%;
                right: 8%;
                height: 3px;
                border-radius: 999px;
                background: linear-gradient(90deg, transparent, var(--card-color, #38bdf8), #ffffff, var(--card-color, #38bdf8), transparent);
                opacity: 0.35;
                transition: opacity 0.18s ease-out, left 0.18s ease-out, right 0.18s ease-out, box-shadow 0.18s ease-out;
                pointer-events: none;
                z-index: 2;
            }

            /* Subtle Under-Glow Aura strictly at the bottom edge (below the text) */
            .pf-bento-card::after {
                content: '';
                position: absolute;
                left: 0;
                right: 0;
                bottom: -2px;
                height: 22px;
                background: radial-gradient(ellipse at 50% 100%, rgba(var(--card-rgb, 56, 189, 248), 0.28) 0%, transparent 75%);
                opacity: 0.4;
                pointer-events: none;
                z-index: 1;
                transition: opacity 0.18s ease-out;
            }

            /* Hover State: Deep Black Interior + Glowing Edges + Bottom Beam + Gentle 2px Lift */
            .pf-bento-card:hover {
                transform: translateY(-2px) !important;
                z-index: 10 !important;
                background: linear-gradient(180deg, #03050a 0%, #000000 100%) !important;
                border-color: var(--card-color, #38bdf8) !important;
                border-bottom: 3.5px solid var(--card-color, #38bdf8) !important;
                box-shadow:
                    0 10px 22px -3px rgba(0, 0, 0, 0.9),
                    0 8px 20px -2px rgba(var(--card-rgb, 56, 189, 248), 0.7),
                    0 0 12px rgba(var(--card-rgb, 56, 189, 248), 0.45),
                    inset 0 0 0 1px rgba(var(--card-rgb, 56, 189, 248), 0.35) !important;
                animation: none !important;
            }

            .pf-bento-card:hover::before {
                left: 2%;
                right: 2%;
                opacity: 1;
                box-shadow: 0 -2px 12px var(--card-color, #38bdf8), 0 2px 14px var(--card-color, #38bdf8);
            }

            .pf-bento-card:hover::after {
                opacity: 0.95;
            }

            /* 3D Icon Box with Under-Icon Beam */
            .pf-card-icon-wrap {
                position: relative;
                flex-shrink: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                z-index: 3;
            }

            .pf-card-icon-box {
                width: 48px;
                height: 48px;
                min-width: 48px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.45rem;
                background: linear-gradient(180deg, rgba(15, 23, 42, 0.92) 0%, rgba(8, 12, 24, 0.98) 100%);
                border: 1.5px solid rgba(var(--card-rgb, 56, 189, 248), 0.55);
                border-bottom: 3px solid var(--card-color, #38bdf8);
                color: var(--card-color, #38bdf8);
                box-shadow:
                    0 5px 12px -2px rgba(var(--card-rgb, 56, 189, 248), 0.35),
                    inset 0 -4px 8px rgba(var(--card-rgb, 56, 189, 248), 0.18);
                transition: transform 0.18s ease-out, box-shadow 0.18s ease-out, background 0.18s ease-out, border-color 0.18s ease-out;
            }

            .pf-bento-card:hover .pf-card-icon-box {
                transform: translateY(-1px);
                background: #020409;
                border-color: var(--card-color, #38bdf8);
                border-bottom: 3px solid #ffffff;
                box-shadow:
                    0 8px 18px -2px rgba(var(--card-rgb, 56, 189, 248), 0.85),
                    0 3px 8px rgba(var(--card-rgb, 56, 189, 248), 0.55),
                    inset 0 -5px 10px rgba(var(--card-rgb, 56, 189, 248), 0.32);
            }

            /* Card Text Body — 100% Full Unobstructed Width & Zero Shifting */
            .pf-card-body {
                min-width: 0;
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 4px;
                z-index: 3;
            }

            /* PURE SNOW-WHITE FULL TEXT (#ffffff) — RAZOR SHARP ON DEEP BLACK */
            .pf-card-title {
                display: block;
                width: 100%;
                font-size: 1.12rem;
                font-weight: 900;
                color: #ffffff !important;
                line-height: 1.25;
                letter-spacing: 0.2px;
                white-space: nowrap !important;
                overflow: visible !important;
                text-overflow: clip !important;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.9) !important;
                transition: color 0.18s ease-out;
            }

            /* Slightly smaller font option for 4-word titles like "تحليل مالي بالذكاء الاصطناعي" */
            .pf-card-title.is-compact-title {
                font-size: 0.92rem !important;
                letter-spacing: 0px;
            }

            .pf-bento-card:hover .pf-card-title {
                color: #ffffff !important;
                text-shadow: 0 2px 6px #000000 !important;
            }

            .pf-card-meta-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                width: 100%;
            }

            .pf-card-desc {
                font-size: 0.78rem;
                font-weight: 600;
                color: #cbd5e1 !important;
                line-height: 1.32;
                display: -webkit-box;
                -webkit-line-clamp: 1;
                -webkit-box-orient: vertical;
                overflow: hidden;
                flex: 1;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.85) !important;
                transition: color 0.18s ease-out;
            }

            .pf-bento-card:hover .pf-card-desc {
                color: #f8fafc !important;
            }

            .pf-card-pill {
                flex-shrink: 0;
                padding: 1px 8px;
                border-radius: 999px;
                font-size: 0.68rem;
                font-weight: 900;
                background: rgba(var(--card-rgb, 56, 189, 248), 0.2);
                color: #ffffff !important;
                border: 1px solid rgba(var(--card-rgb, 56, 189, 248), 0.55);
                white-space: nowrap;
                transition: background 0.18s ease-out, border-color 0.18s ease-out;
            }

            .pf-bento-card:hover .pf-card-pill {
                background: rgba(var(--card-rgb, 56, 189, 248), 0.32);
                color: #ffffff !important;
                border-color: var(--card-color, #38bdf8);
            }

            .pf-card-arrow {
                font-size: 0.95rem;
                font-weight: 900;
                color: #ffffff !important;
                opacity: 0.5;
                transform: translateX(0);
                transition: opacity 0.18s ease-out, transform 0.18s ease-out, color 0.18s ease-out;
                flex-shrink: 0;
                z-index: 3;
            }

            .pf-bento-card:hover .pf-card-arrow {
                opacity: 1;
                color: var(--card-color, #38bdf8) !important;
                transform: translateX(-3px);
            }

            /* ── Panoramic Footer Strip ── */
            .pf-footer {
                margin-top: 14px;
                padding-top: 12px;
                border-top: 1px solid rgba(255, 255, 255, 0.14);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }

            .pf-footer-open-main {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 18px;
                border-radius: 12px;
                font-size: 0.86rem;
                font-weight: 900;
                color: #ffffff !important;
                text-decoration: none !important;
                background: linear-gradient(135deg, rgba(var(--pf-accent-rgb, 56, 189, 248), 0.42), rgba(var(--pf-accent-rgb, 56, 189, 248), 0.18));
                border: 1.5px solid var(--pf-accent, #38bdf8);
                box-shadow: 0 4px 16px rgba(var(--pf-accent-rgb, 56, 189, 248), 0.28);
                transition: all 0.24s ease;
                cursor: pointer;
            }

            .pf-footer-open-main:hover {
                background: linear-gradient(135deg, rgba(var(--pf-accent-rgb, 56, 189, 248), 0.65), rgba(var(--pf-accent-rgb, 56, 189, 248), 0.35));
                transform: translateX(-4px) scale(1.03);
                border-color: #ffffff;
                box-shadow: 0 6px 24px rgba(var(--pf-accent-rgb, 56, 189, 248), 0.55);
            }

            .pf-footer-hint {
                font-size: 0.76rem;
                font-weight: 700;
                color: #e2e8f0 !important;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            /* Keep pure white text & deep dark hover in Light Theme as well */
            :root.light-theme #magaiees-panoramic-flyout {
                background:
                    radial-gradient(circle at 90% 10%, rgba(var(--pf-accent-rgb, 56, 189, 248), 0.28) 0%, transparent 52%),
                    linear-gradient(145deg, rgba(10, 15, 32, 0.98) 0%, rgba(18, 28, 52, 0.99) 100%) !important;
                border: 2.5px solid var(--pf-accent, #38bdf8) !important;
                border-right: 5px solid var(--pf-accent, #38bdf8) !important;
            }
            :root.light-theme #magaiees-panoramic-flyout .pf-card-title,
            :root.light-theme #magaiees-panoramic-flyout .pf-header-title,
            :root.light-theme #magaiees-panoramic-flyout .pf-header-badge,
            :root.light-theme #magaiees-panoramic-flyout .pf-footer-open-main,
            :root.light-theme #magaiees-panoramic-flyout .pf-footer-open-main span,
            :root.light-theme #magaiees-panoramic-flyout .pf-card-pill {
                color: #ffffff !important;
            }
            :root.light-theme #magaiees-panoramic-flyout .pf-card-desc,
            :root.light-theme #magaiees-panoramic-flyout .pf-header-subtitle,
            :root.light-theme #magaiees-panoramic-flyout .pf-footer-hint {
                color: #cbd5e1 !important;
            }
            :root.light-theme #magaiees-panoramic-flyout .pf-bento-card:hover .pf-card-desc {
                color: #f8fafc !important;
            }
            :root.light-theme #magaiees-panoramic-flyout .pf-card-arrow {
                color: #ffffff !important;
            }
            :root.light-theme #magaiees-panoramic-flyout .pf-bento-card:hover .pf-card-arrow {
                color: var(--card-color, #38bdf8) !important;
            }

            @media print {
                #magaiees-panoramic-flyout { display: none !important; }
            }
            @media (max-width: 820px) {
                #magaiees-panoramic-flyout {
                    width: calc(100vw - 82px);
                    right: 70px;
                }
                .pf-bento-grid {
                    grid-template-columns: 1fr;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // ── Handle Same-Page Instant Navigation or Cross-Page Navigation ──
    function handleSubItemClick(e, itemConfig, parentHref) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        const currentPage = (window.location.pathname.split('/').pop() || '').toLowerCase();
        const targetPage = (parentHref || '').split('?')[0].toLowerCase();

        // 1. Check if it has a custom action (e.g., Themes Modal or Chatbot or Sales/Purchase Editor)
        if (typeof itemConfig.samePageAction === 'function') {
            if (currentPage === targetPage || itemConfig.href.includes('action=themes') || itemConfig.href.includes('action=chatbot')) {
                const handled = itemConfig.samePageAction();
                if (handled !== false) {
                    hidePanoramicFlyout(true);
                    return;
                }
            }
        }

        // 2. Check if we are already on master-data.php and switching tabs
        if (itemConfig.samePageTab && currentPage === 'master-data.php') {
            const targetBtn = document.querySelector(`button[onclick*="'${itemConfig.samePageTab}'"]`);
            if (targetBtn) {
                targetBtn.click();
                try {
                    const tabSlug = itemConfig.samePageTab.replace(/^tab-/, '');
                    window.history.replaceState(null, '', `master-data.php?tab=${tabSlug}`);
                } catch (err) {}
                hidePanoramicFlyout(true);
                return;
            }
        }

        // 3. Otherwise navigate to the target URL
        hidePanoramicFlyout(true);
        window.location.href = itemConfig.href;
    }

    // ── State Variables ──
    let flyoutEl = null;
    let activeSidebarLink = null;
    let activeHrefKey = null;
    let closeTimer = null;

    function ensureFlyoutDOM() {
        if (flyoutEl && document.body.contains(flyoutEl)) return flyoutEl;
        flyoutEl = document.createElement('div');
        flyoutEl.id = 'magaiees-panoramic-flyout';
        flyoutEl.setAttribute('role', 'menu');
        flyoutEl.setAttribute('aria-label', 'القائمة البانورامية المنسدلة');
        flyoutEl.innerHTML = `
            <div class="pf-laser-pointer"></div>
            <div class="pf-header">
                <div class="pf-header-main">
                    <div class="pf-header-emblem" id="pf-emblem">✨</div>
                    <div class="pf-header-titles">
                        <h3 class="pf-header-title" id="pf-title">القسم</h3>
                        <p class="pf-header-subtitle" id="pf-subtitle"></p>
                    </div>
                </div>
                <span class="pf-header-badge" id="pf-badge"></span>
            </div>
            <div class="pf-bento-grid" id="pf-grid"></div>
            <div class="pf-footer">
                <a href="#" class="pf-footer-open-main" id="pf-open-main">
                    <span id="pf-open-main-txt">فتح الشاشة الرئيسية الكاملة</span>
                    <span>⬅</span>
                </a>
                <span class="pf-footer-hint">✨ بانوراما مقاييس الكريستالية • تنقل فوري</span>
            </div>
        `;

        // Keep flyout open when cursor is inside it
        flyoutEl.addEventListener('mouseenter', () => {
            clearTimeout(closeTimer);
            const sb = document.querySelector('.global-sidebar');
            if (sb) sb.classList.add('panoramic-flyout-open');
        });

        flyoutEl.addEventListener('mouseleave', (e) => {
            const sb = document.querySelector('.global-sidebar');
            if (sb && e.relatedTarget && sb.contains(e.relatedTarget)) {
                return;
            }
            scheduleCloseFlyout(150);
        });

        // Mouse-tracking spotlight inside cards
        flyoutEl.addEventListener('mousemove', (e) => {
            const card = e.target.closest('.pf-bento-card');
            if (!card) return;
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
            card.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
        });

        document.body.appendChild(flyoutEl);
        return flyoutEl;
    }

    function renderFlyoutForLink(linkEl) {
        const rawHref = (linkEl.getAttribute('data-href') || linkEl.getAttribute('href') || '').split('?')[0].trim();
        const menuConfig = PANORAMIC_MENUS[rawHref];
        if (!menuConfig) {
            hidePanoramicFlyout(false);
            return;
        }

        clearTimeout(closeTimer);
        const panel = ensureFlyoutDOM();
        const sb = document.querySelector('.global-sidebar');
        if (sb) sb.classList.add('panoramic-flyout-open');

        // Highlight active hovered sidebar item
        document.querySelectorAll('.global-sidebar .sidebar-item').forEach(el => {
            el.classList.remove('panoramic-hovered');
        });
        linkEl.style.setProperty('--sb-hover-color', menuConfig.accent);
        linkEl.style.setProperty('--sb-hover-rgb', menuConfig.accentRgb);
        linkEl.classList.add('panoramic-hovered');
        activeSidebarLink = linkEl;

        // Rebuild cards when switching to a different sidebar category
        if (activeHrefKey !== rawHref) {
            activeHrefKey = rawHref;

            panel.style.setProperty('--pf-accent', menuConfig.accent);
            panel.style.setProperty('--pf-accent-rgb', menuConfig.accentRgb);

            document.getElementById('pf-emblem').textContent = menuConfig.icon;
            document.getElementById('pf-title').textContent = menuConfig.title;
            document.getElementById('pf-subtitle').textContent = menuConfig.subtitle;
            document.getElementById('pf-badge').textContent = menuConfig.badge;

            const openMainBtn = document.getElementById('pf-open-main');
            openMainBtn.href = rawHref;
            openMainBtn.onclick = (e) => {
                e.preventDefault();
                hidePanoramicFlyout(true);
                window.location.href = rawHref;
            };
            document.getElementById('pf-open-main-txt').textContent = `فتح شاشة (${linkEl.querySelector('.sidebar-text')?.textContent || menuConfig.title}) الكاملة`;

            const grid = document.getElementById('pf-grid');
            grid.innerHTML = '';

            menuConfig.items.forEach((item, idx) => {
                const card = document.createElement('a');
                card.href = item.href;
                card.className = 'pf-bento-card';
                card.style.setProperty('--card-color', item.color || menuConfig.accent);
                card.style.setProperty('--card-rgb', item.rgb || menuConfig.accentRgb);
                card.style.setProperty('--card-idx', idx);

                // Resolve live count or tag badge (placed in the meta row so the title has 100% full width!)
                let pillHTML = '';
                if (item.countKey) {
                    const count = getLiveCount(item.countKey);
                    if (count !== null && count >= 0) {
                        pillHTML = `<span class="pf-card-pill">${count} ${item.unit || ''}</span>`;
                    }
                } else if (item.tag) {
                    pillHTML = `<span class="pf-card-pill">${item.tag}</span>`;
                }

                const titleClass = item.compactTitle ? 'pf-card-title is-compact-title' : 'pf-card-title';

                card.innerHTML = `
                    <div class="pf-card-icon-wrap">
                        <div class="pf-card-icon-box">${item.icon}</div>
                    </div>
                    <div class="pf-card-body">
                        <span class="${titleClass}">${item.title}</span>
                        <div class="pf-card-meta-row">
                            <span class="pf-card-desc">${item.desc}</span>
                            ${pillHTML}
                        </div>
                    </div>
                    <span class="pf-card-arrow">⬅</span>
                `;

                card.addEventListener('click', (ev) => handleSubItemClick(ev, item, rawHref));
                grid.appendChild(card);
            });
        }

        // Show & Position smoothly relative to the hovered sidebar item
        panel.classList.add('is-visible');
        positionFlyoutNextToItem(linkEl, panel);
    }

    function positionFlyoutNextToItem(linkEl, panel) {
        const itemRect = linkEl.getBoundingClientRect();
        const itemCenterY = itemRect.top + itemRect.height / 2;
        const panelHeight = panel.offsetHeight || 420;
        const viewportH = window.innerHeight;

        // Center panel gently around the hovered item, clamped inside viewport
        let targetTop = itemCenterY - (panelHeight * 0.35);
        const minTop = 12;
        const maxTop = Math.max(minTop, viewportH - panelHeight - 12);
        targetTop = Math.max(minTop, Math.min(targetTop, maxTop));

        panel.style.top = `${Math.round(targetTop)}px`;

        // Position laser pointer notch to point directly at the hovered sidebar item
        const pointerY = Math.max(28, Math.min(panelHeight - 28, itemCenterY - targetTop));
        panel.style.setProperty('--pf-pointer-y', `${Math.round(pointerY)}px`);
    }

    function scheduleCloseFlyout(delayMs = 150) {
        clearTimeout(closeTimer);
        closeTimer = setTimeout(() => {
            hidePanoramicFlyout(false);
        }, delayMs);
    }

    function hidePanoramicFlyout(immediate = false) {
        clearTimeout(closeTimer);
        if (flyoutEl) {
            flyoutEl.classList.remove('is-visible');
        }
        const sb = document.querySelector('.global-sidebar');
        if (sb) {
            sb.classList.remove('panoramic-flyout-open');
        }
        document.querySelectorAll('.global-sidebar .sidebar-item.panoramic-hovered').forEach(el => {
            el.classList.remove('panoramic-hovered');
        });
        if (immediate) {
            activeHrefKey = null;
        }
        activeSidebarLink = null;
    }

    // ── Bind Events to Sidebar ──
    function attachSidebarListeners() {
        const sb = document.querySelector('.global-sidebar');
        if (!sb || sb.dataset.panoramicBound === '1') return;
        sb.dataset.panoramicBound = '1';

        injectPanoramicStyles();

        const items = sb.querySelectorAll('.sidebar-nav .sidebar-item');
        items.forEach(item => {
            const rawHref = (item.getAttribute('data-href') || item.getAttribute('href') || '').split('?')[0].trim();
            if (PANORAMIC_MENUS[rawHref] && !item.querySelector('.sb-panoramic-chevron')) {
                const chev = document.createElement('span');
                chev.className = 'sb-panoramic-chevron';
                chev.innerHTML = '❮';
                item.appendChild(chev);
            }

            item.addEventListener('mouseenter', () => {
                clearTimeout(closeTimer);
                renderFlyoutForLink(item);
            });
        });

        sb.addEventListener('mouseleave', (e) => {
            if (flyoutEl && e.relatedTarget && flyoutEl.contains(e.relatedTarget)) {
                return;
            }
            scheduleCloseFlyout(160);
        });

        // Close on Escape or outside click
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hidePanoramicFlyout(true);
        });
        document.addEventListener('click', (e) => {
            if (!flyoutEl || !flyoutEl.classList.contains('is-visible')) return;
            if (!flyoutEl.contains(e.target) && !sb.contains(e.target)) {
                hidePanoramicFlyout(true);
            }
        });
    }

    // ── Auto-Execute URL Actions on Target Screens (Sales / Purchase / Tools / Settings) ──
    function handleDeepLinkActionsOnLoad() {
        try {
            const params = new URLSearchParams(window.location.search);
            const action = params.get('action');
            if (!action) return;

            const currentPage = (window.location.pathname.split('/').pop() || '').toLowerCase();

            if (currentPage === 'direct-sales.php') {
                setTimeout(() => {
                    if (action === 'new' && typeof window.openInvoiceEditor === 'function') {
                        window.openInvoiceEditor('pending_delivery');
                    } else if (action === 'split' && typeof window.openSplitInvoiceView === 'function') {
                        window.openSplitInvoiceView();
                    } else if (action === 'delivery' && typeof window.openDeliveryHub === 'function') {
                        window.openDeliveryHub();
                    } else if (action === 'preliminary' && typeof window.openInvoiceEditor === 'function') {
                        window.openInvoiceEditor('preliminary');
                    }
                }, 120);
            } else if (currentPage === 'purchase.php') {
                setTimeout(() => {
                    if (action === 'direct' && typeof window.openPurchaseEditor === 'function') {
                        window.openPurchaseEditor('direct');
                    } else if (action === 'proforma' && typeof window.openPurchaseEditor === 'function') {
                        window.openPurchaseEditor('proforma');
                    } else if (action === 'ocr' && typeof window.triggerInvoiceOCR === 'function') {
                        window.triggerInvoiceOCR();
                    }
                }, 120);
            } else if (currentPage === 'settings.php' && action === 'themes') {
                setTimeout(() => {
                    if (typeof window.openMaster7ThemesModal === 'function') {
                        window.openMaster7ThemesModal();
                    }
                }, 250);
            }
        } catch (e) {}
    }

    // Initialize immediately & observe if sidebar is injected slightly later
    function init() {
        attachSidebarListeners();
        if (!document.querySelector('.global-sidebar')) {
            const obs = new MutationObserver(() => {
                if (document.querySelector('.global-sidebar')) {
                    attachSidebarListeners();
                    obs.disconnect();
                }
            });
            obs.observe(document.documentElement, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            init();
            handleDeepLinkActionsOnLoad();
        });
    } else {
        init();
        handleDeepLinkActionsOnLoad();
    }
})();
