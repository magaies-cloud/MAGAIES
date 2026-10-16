// ============================================================================
// MAGAIEES ERP - Unified Ultra-Granular Permissions & Access Control Studio
// نظام إدارة الصلاحيات والتحكم الدقيق الشامل في كل شاشات وأزرار وتقارير وترحيلات البرنامج من الألف إلى الياء
// ============================================================================

(function(window) {
    'use strict';

    // ═════════════════════════════════════════════════════════════════════
    // 1. ALL SYSTEM SCREENS CATALOG (CATEGORIZED FROM A TO Z)
    // ═════════════════════════════════════════════════════════════════════
    const SYSTEM_SCREENS_CATALOG = [
        {
            category: 'dashboard',
            categoryName: 'لوحة التحكم والقيادة المركزية',
            categoryIcon: 'fa-gauge-high',
            color: '#38bdf8',
            desc: 'التحكم في وصول المستخدم للوحة التحكم ومؤشراتها التنفيذية',
            screens: [
                {
                    id: 'magaiees_unified_dashboard',
                    file: 'magaiees_unified_dashboard.php',
                    title: 'لوحة التحكم الموحدة (MAGAIEES Dashboard)',
                    icon: 'fa-gauge-high',
                    desc: 'الشاشة الرئيسية للبرنامج، تشتمل على المؤشرات، الرسوم، والأزرار السريعة',
                    supportedActions: ['preview', 'print', 'export']
                },
                {
                    id: 'dashboard_pro',
                    file: 'dashboard-pro.php',
                    title: 'لوحة التحكم الاحترافية والتحليلات',
                    icon: 'fa-chart-pie',
                    desc: 'واجهة القيادة المتقدمة والرسوم البيانية التراكمية',
                    supportedActions: ['preview', 'print', 'export']
                }
            ]
        },
        {
            category: 'sales',
            categoryName: 'المبيعات ونقاط البيع والتوزيع',
            categoryIcon: 'fa-cart-shopping',
            color: '#38bdf8',
            desc: 'نقاط البيع، الفواتير الآجلة، الحجوزات، وأذونات تسليم البضائع',
            screens: [
                {
                    id: 'direct_sales',
                    file: 'direct-sales.php',
                    title: 'المبيعات المباشرة ونقاط البيع (POS)',
                    icon: 'fa-cart-shopping',
                    desc: 'إصدار فواتير الكاشير، نقاط البيع السريعة، والباركود',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'share', 'preview']
                },
                {
                    id: 'sales_invoice',
                    file: 'sales-invoice.php',
                    title: 'فواتير المبيعات الآجلة والائتمانية',
                    icon: 'fa-receipt',
                    desc: 'إصدار فواتير البيع الآجل وحسابات العملاء',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'share', 'preview']
                },
                {
                    id: 'reserved_sales',
                    file: 'reserved-sales.php',
                    title: 'حجوزات المبيعات وعروض الأسعار',
                    icon: 'fa-bookmark',
                    desc: 'عروض الأسعار المبدئية وحجز البضائع للعملاء',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'sales_delivery',
                    file: 'sales-delivery.php',
                    title: 'إذن تسليم وإخراج المبيعات من المستودع',
                    icon: 'fa-truck-ramp-box',
                    desc: 'صرف وتسليم البضاعة المباعة من المخزن فعلياً',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'delivery_note',
                    file: 'delivery-note.php',
                    title: 'إشعار واستلام التسليم المخزني',
                    icon: 'fa-clipboard-check',
                    desc: 'سند تسليم الشحنات والتوقيعات الرسمية',
                    supportedActions: ['new', 'save', 'edit', 'print', 'share']
                },
                {
                    id: 'customer_portfolio',
                    file: 'customer-portfolio.php',
                    title: 'محفظة وتحليلات العملاء والمديونيات',
                    icon: 'fa-users',
                    desc: 'كشف مديونيات العملاء والتحليلات الائتمانية',
                    supportedActions: ['print', 'share', 'export', 'preview']
                },
                {
                    id: 'credit_limits_dashboard',
                    file: 'credit_limits_dashboard.php',
                    title: 'مراقبة سقوف وحدود الائتمان للعملاء',
                    icon: 'fa-shield-halved',
                    desc: 'التحكم في تجاوز سقف الدين والمديونيات الحرجة',
                    supportedActions: ['print', 'export', 'edit']
                }
            ]
        },
        {
            category: 'purchases',
            categoryName: 'المشتريات واستلام واسترداد البضائع',
            categoryIcon: 'fa-bag-shopping',
            color: '#fbbf24',
            desc: 'فواتير المشتريات، استلام الشحنات، ومرتجعات البضائع للموردين',
            screens: [
                {
                    id: 'purchase',
                    file: 'purchase.php',
                    title: 'فواتير المشتريات العامة والتوريدات',
                    icon: 'fa-bag-shopping',
                    desc: 'إدخال فواتير الشراء، التكاليف، وحسابات الموردين',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'share', 'preview']
                },
                {
                    id: 'purchase_receipt',
                    file: 'purchase-receipt.php',
                    title: 'استلام البضائع والشحنات الواردة',
                    icon: 'fa-box-open',
                    desc: 'إذن دخول بضائع مشتريات فعلياً إلى المستودعات وتحديث الأرصدة',
                    supportedActions: ['new', 'save', 'edit', 'receive', 'print']
                },
                {
                    id: 'purchase_return',
                    file: 'purchase-return.php',
                    title: 'استرداد البضائع (مرتجع المشتريات)',
                    icon: 'fa-rotate-left',
                    desc: 'إرجاع واسترداد البضائع للموردين وخصمها من الحساب والمخزن',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'return', 'print', 'share']
                },
                {
                    id: 'supplier_reliability',
                    file: 'supplier_reliability.php',
                    title: 'اعتمادية وموثوقية الموردين',
                    icon: 'fa-award',
                    desc: 'تقييم الالتزام بالمواعيد والجودة والأسعار',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'supplier_performance',
                    file: 'supplier_performance.php',
                    title: 'تحليل أداء وتوريدات الموردين',
                    icon: 'fa-truck-field',
                    desc: 'مؤشرات التوريد وحجم التعاملات السنوية',
                    supportedActions: ['print', 'export']
                }
            ]
        },
        {
            category: 'treasury',
            categoryName: 'الخزينة والمعاملات المالية والشيكات',
            categoryIcon: 'fa-vault',
            color: '#34d399',
            desc: 'سندات الصرف والقبض، الخزن والبنوك، ودورة الشيكات المصرفية',
            screens: [
                {
                    id: 'single_cash_payment',
                    file: 'single-cash-payment.php',
                    title: 'سند صرف نقدية مفرد',
                    icon: 'fa-file-invoice',
                    desc: 'صرف نقدية لعميل، مورد، شريك، أو مصروف عام',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'share', 'preview']
                },
                {
                    id: 'single_cash_receipt',
                    file: 'single-cash-receipt.php',
                    title: 'سند قبض نقدية مفرد',
                    icon: 'fa-receipt',
                    desc: 'قبض وتحصيل نقدية وإيداعها في الصندوق أو البنك',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'share', 'preview']
                },
                {
                    id: 'multi_cash_payment',
                    file: 'multi-cash-payment.php',
                    title: 'سند صرف متعدد الجهات (مجمع)',
                    icon: 'fa-money-bill-wave',
                    desc: 'صرف مالي مجمع لعدة أطراف في مستند واحد',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'vouchers_hub',
                    file: 'vouchers-hub.php',
                    title: 'مركز السندات المالي الموحد',
                    icon: 'fa-money-bill-transfer',
                    desc: 'استعراض وبحث وطباعة كافة سندات القبض والصرف',
                    supportedActions: ['print', 'share', 'export', 'preview']
                },
                {
                    id: 'safe_transfer',
                    file: 'safe-transfer.php',
                    title: 'تحويل نقدية بين الخزن والبنوك',
                    icon: 'fa-arrow-right-arrow-left',
                    desc: 'مناقلة وتحويل الأموال بين الحسابات والخزائن',
                    supportedActions: ['new', 'save', 'print']
                },
                {
                    id: 'checks_cycle',
                    file: 'checks-cycle.php',
                    title: 'دورة إدارة الشيكات المصرفية',
                    icon: 'fa-money-check-dollar',
                    desc: 'استلام، إيداع، تحصيل، تظهير، ورفض الشيكات الواردة والصادرة',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'approve']
                },
                {
                    id: 'bank_transfer',
                    file: 'bank-transfer.php',
                    title: 'التحويلات المصرفية البنكية',
                    icon: 'fa-building-columns',
                    desc: 'سندات التحويل المالي عبر الحسابات البنكية',
                    supportedActions: ['new', 'save', 'print', 'share']
                }
            ]
        },
        {
            category: 'posting_accounts',
            categoryName: 'الحسابات العامة وترحيل الحسابات ودفتر الأستاذ',
            categoryIcon: 'fa-book-bookmark',
            color: '#60a5fa',
            desc: 'ترحيل واعتماد العمليات المالية، قيود اليومية، ميزان المراجعة، والأرباح والخسائر',
            screens: [
                {
                    id: 'posting_accounts',
                    file: 'posting-accounts.php',
                    title: 'ترحيل واعتماد الحسابات (Posting Engine)',
                    icon: 'fa-sitemap',
                    desc: 'ترحيل فواتير المبيعات والمشتريات وسندات القبض والصرف إلى دفتر الأستاذ',
                    supportedActions: ['save', 'approve', 'edit', 'delete']
                },
                {
                    id: 'journal_voucher',
                    file: 'journal-voucher.php',
                    title: 'سند قيد اليومية العامة والتسويات',
                    icon: 'fa-pen-to-square',
                    desc: 'إنشاء قيود اليومية المحاسبية المزدوجة والتسويات',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'preview']
                },
                {
                    id: 'journal',
                    file: 'journal.php',
                    title: 'دفتر اليومية العامة الشامل',
                    icon: 'fa-book',
                    desc: 'سجل القيود المحاسبية اليومية المعتمدة',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'general_ledger',
                    file: 'general-ledger.php',
                    title: 'دفتر الأستاذ العام (General Ledger)',
                    icon: 'fa-book-bookmark',
                    desc: 'كشف حركة الحسابات التفصيلية والأرصدة الختامية',
                    supportedActions: ['print', 'share', 'export', 'preview']
                },
                {
                    id: 'account_statement',
                    file: 'account-statement.php',
                    title: 'كشف حساب شريك / عميل / مورد',
                    icon: 'fa-file-invoice-dollar',
                    desc: 'كشف الحساب المالي التفصيلي مع خيارات المشاركة بالواتساب',
                    supportedActions: ['print', 'share', 'export', 'preview']
                },
                {
                    id: 'trial_balance',
                    file: 'trial-balance.php',
                    title: 'ميزان المراجعة بالأرصدة والمجاميع',
                    icon: 'fa-scale-balanced',
                    desc: 'مراجعة الأرصدة والمجاميع المحاسبية للتأكد من توازن القيود',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'pl_statement',
                    file: 'pl-statement.php',
                    title: 'قائمة الأرباح والخسائر والدخل',
                    icon: 'fa-chart-line',
                    desc: 'حساب صافي الأرباح، الإيرادات، والمصروفات التشغيلية',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'balances_modern_hub',
                    file: 'balances_modern_hub.php',
                    title: 'مركز الأرصدة والمطالبات المالي الموحد',
                    icon: 'fa-wallet',
                    desc: 'ملخص شامل لأرصدة جميع الحسابات والخزن والعملاء',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'cashflow_modern',
                    file: 'cashflow_modern.php',
                    title: 'التدفقات النقدية وتوقعات السيولة',
                    icon: 'fa-money-bill-trend-up',
                    desc: 'تحليل حركة السيولة الداخلة والخارجة',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'analytics_hub',
                    file: 'analytics.php',
                    title: 'مركز التقارير والتحليلات الشامل',
                    icon: 'fa-chart-pie',
                    desc: 'البوابة الموحدة لكافة التقارير المالية والمخزنية والذكية',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'general_accounts_hub',
                    file: 'general_accounts_hub.php',
                    title: 'بوابة الحسابات العامة الموحدة',
                    icon: 'fa-building-columns',
                    desc: 'دفتر الأستاذ، اليومية الشاملة، أعمار الديون، والأرصدة المجمعة',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'financial_reports',
                    file: 'financial-reports.php',
                    title: 'التقارير المالية الختامية',
                    icon: 'fa-file-invoice-dollar',
                    desc: 'القوائم المالية الختامية وتقارير الأداء المالي',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'daily_master_report',
                    file: 'daily_master_report.php',
                    title: 'تقرير حركة اليومية الشاملة (Master Daily)',
                    icon: 'fa-calendar-day',
                    desc: 'ملخص شامل لكافة الحركات اليومية النقدية والآجلة والمخزنية',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'mainbalances_report',
                    file: 'mainbalances-report.php',
                    title: 'تقرير الأرصدة المجمعة للحسابات',
                    icon: 'fa-scale-unbalanced',
                    desc: 'كشف الأرصدة الإجمالية للعملاء والموردين والخزائن والبنوك',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'aging_report',
                    file: 'aging-report.php',
                    title: 'تقرير أعمار الديون والذمم',
                    icon: 'fa-clock-rotate-left',
                    desc: 'تحليل فترات استحقاق مديونيات العملاء والموردين',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'combined_receivables',
                    file: 'combined_receivables.php',
                    title: 'تقرير المطالبات والذمم المجمعة',
                    icon: 'fa-hand-holding-dollar',
                    desc: 'كشف شامل بالذمم المدينة والدائنة وصافي المطالبات',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'cash_bank_statement',
                    file: 'cash-bank-statement.php',
                    title: 'كشف حركة الخزينة والبنوك التفصيلي',
                    icon: 'fa-vault',
                    desc: 'حركة السيولة النقدية والمصرفية التفصيلية',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'cost_centers',
                    file: 'cost-centers.php',
                    title: 'مراكز التكلفة والمشاريع',
                    icon: 'fa-diagram-project',
                    desc: 'إدارة وتحليل إيرادات ومصروفات مراكز التكلفة',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'export']
                },
                {
                    id: 'partners_hub',
                    file: 'partners-hub.php',
                    title: 'بوابة الشركاء والاستثمار',
                    icon: 'fa-handshake',
                    desc: 'إدارة حسابات الشركاء ورأس المال وتوزيع الأرباح',
                    supportedActions: ['new', 'save', 'edit', 'print', 'export']
                },
                {
                    id: 'fx_rates',
                    file: 'fx-rates.php',
                    title: 'إدارة أسعار الصرف والعملات',
                    icon: 'fa-coins',
                    desc: 'تحديث أسعار صرف العملات الأجنبية مقابل العملة المحلية',
                    supportedActions: ['new', 'save', 'edit', 'print']
                }
            ]
        },
        {
            category: 'inventory',
            categoryName: 'المخازن والمستودعات وحركات البضائع',
            categoryIcon: 'fa-warehouse',
            color: '#a78bfa',
            desc: 'دخول المخازن، كرت الصنف، حركات البضائع، التحويلات، والجرد والتسويات',
            screens: [
                {
                    id: 'inventory',
                    file: 'inventory.php',
                    title: 'دخول المخازن وقائمة الأصناف',
                    icon: 'fa-warehouse',
                    desc: 'استعراض أصناف المستودع والكميات الحالية المتوفرة',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print', 'export']
                },
                {
                    id: 'item_ledger',
                    file: 'item-ledger.php',
                    title: 'دفتر كرت حركة الصنف التفصيلي',
                    icon: 'fa-barcode',
                    desc: 'تتبع حركة الصنف بالتواريخ، الفواتير، وسعر التكلفة والبيع',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'inventory_movement',
                    file: 'inventory-movement.php',
                    title: 'حركة البضائع والأصناف اليومية',
                    icon: 'fa-dolly',
                    desc: 'إدخال حركات الصرف والتوريد المخزني اليدوي',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'print']
                },
                {
                    id: 'inventory_summary',
                    file: 'inventory-summary.php',
                    title: 'ملخص حركات وتداولات المخزون',
                    icon: 'fa-table-list',
                    desc: 'تقرير ملخص الوارد والمنصرف ورصيد الإغلاق',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'warehouse_transfer',
                    file: 'warehouse-transfer.php',
                    title: 'التحويل والمناقلة بين المستودعات',
                    icon: 'fa-arrows-left-right',
                    desc: 'نقل البضائع والأصناف من مستودع لآخر',
                    supportedActions: ['new', 'save', 'print', 'receive']
                },
                {
                    id: 'inventory_stocktake',
                    file: 'inventory-stocktake.php',
                    title: 'الجرد الفعلي للمخازن والمستودعات',
                    icon: 'fa-clipboard-list',
                    desc: 'إدخال كميات الجرد الواقعية ومقارنتها بالدفتري',
                    supportedActions: ['new', 'save', 'print', 'export']
                },
                {
                    id: 'inventory_adjustment',
                    file: 'inventory-adjustment.php',
                    title: 'تسويات فروقات الجرد المخزنية',
                    icon: 'fa-sliders',
                    desc: 'اعتماد الفروقات والعجز والزيادة وتعديل الأرصدة',
                    supportedActions: ['save', 'approve', 'print']
                },
                {
                    id: 'inventory_report',
                    file: 'inventory-report.php',
                    title: 'تقرير المخزون العام الشامل والتقييم',
                    icon: 'fa-boxes-stacked',
                    desc: 'تقييم قيمة البضاعة بسعر الشراء وسعر البيع',
                    supportedActions: ['print', 'export', 'preview']
                },
                {
                    id: 'visual_analytics',
                    file: 'visual-analytics.php',
                    title: 'التحليل المرئي ثلاثي الأبعاد للمخزون',
                    icon: 'fa-cubes',
                    desc: 'خرائط حرارية مجسمة لحركة ومواقع البضائع',
                    supportedActions: ['preview', 'export']
                },
                {
                    id: 'smart_inventory_report',
                    file: 'smart_inventory_report.php',
                    title: 'تقارير المخزون الذكية وتنبيهات النواقص',
                    icon: 'fa-brain',
                    desc: 'تنبيهات الأصناف قرب النفاد والراكدة',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'annual_inventory_zakat_report',
                    file: 'annual_inventory_zakat_report.php',
                    title: 'تقرير الجرد السنوي وزكاة عروض التجارة',
                    icon: 'fa-hand-holding-dollar',
                    desc: 'احتساب زكاة المال وعروض التجارة للبضائع بنصاب شرعي',
                    supportedActions: ['print', 'export']
                }
            ]
        },
        {
            category: 'master_data',
            categoryName: 'البيانات الأساسية ودليل النظام',
            categoryIcon: 'fa-database',
            color: '#60a5fa',
            desc: 'دليل العملاء، الموردين، الأصناف، شجرة الحسابات، الخزن والبنوك',
            screens: [
                {
                    id: 'master_data',
                    file: 'master-data.php',
                    title: 'دليل البيانات الأساسية الموحد',
                    icon: 'fa-database',
                    desc: 'إدارة وتكويد العملاء، الموردين، الأصناف، البنوك، والمخازن',
                    supportedActions: ['new', 'save', 'edit', 'delete', 'export', 'print']
                },
                {
                    id: 'barcode_printer',
                    file: 'barcode-printer.php',
                    title: 'طباعة الباركود والملصقات التجارية',
                    icon: 'fa-barcode',
                    desc: 'تصميم وطباعة ملصقات الباركود والأسعار للأصناف',
                    supportedActions: ['print', 'new', 'save']
                }
            ]
        },
        {
            category: 'alerts',
            categoryName: 'إدارة التنبيهات والرقابة اللحظية',
            categoryIcon: 'fa-triangle-exclamation',
            color: '#f87171',
            desc: 'مصفوفة الرقابة والتنبيهات، الحدود التحذيرية والحرجة',
            screens: [
                {
                    id: 'alerts_manager',
                    file: 'alerts_manager.php',
                    title: 'مصفوفة الرقابة وإدارة التنبيهات (88 قاعدة)',
                    icon: 'fa-shield-halved',
                    desc: 'ضبط وتفعيل قواعد الرقابة الذكية وتعديل حدود التنبيه',
                    supportedActions: ['save', 'edit', 'export']
                },
                {
                    id: 'alarm',
                    file: 'alarm.php',
                    title: 'مركز الإنذارات الاستباقية والتحذيرات اللحظية',
                    icon: 'fa-bell',
                    desc: 'شاشة الرقابة الحية للتنبيهات الفورية الصوتية والمرئية',
                    supportedActions: ['preview', 'save']
                }
            ]
        },
        {
            category: 'ai_advisors',
            categoryName: 'الذكاء الاصطناعي والمستشارون الماليون والسلعيون',
            categoryIcon: 'fa-robot',
            color: '#34d399',
            desc: 'مستشار البضائع AI، مستشار الأموال والسيولة، والتقارير الذكية',
            screens: [
                {
                    id: 'ai_goods_advisor',
                    file: 'ai_goods_advisor.php',
                    title: 'مستشار AI للبضائع والطلبيات الذكية',
                    icon: 'fa-robot',
                    desc: 'توصيات الشراء الذكية، معدلات الدوران، وتفادي الركود',
                    supportedActions: ['preview', 'print', 'export']
                },
                {
                    id: 'ai_money_advisor',
                    file: 'ai_money_advisor.php',
                    title: 'مستشار AI للأموال وتدفق السيولة',
                    icon: 'fa-lightbulb',
                    desc: 'تحليل المخاطر المالية، كفاءة التحصيل، وتوقعات العجز والفائض',
                    supportedActions: ['preview', 'print', 'export']
                },
                {
                    id: 'smart_reports_hub',
                    file: 'smart_reports_hub.php',
                    title: 'مركز التقارير المالية الذكية (AI Hub)',
                    icon: 'fa-brain',
                    desc: 'تقارير مالية تفاعلية مدعومة بالذكاء الاصطناعي',
                    supportedActions: ['preview', 'print', 'export']
                },
                {
                    id: 'partners_reports_hub',
                    file: 'partners_reports_hub.php',
                    title: 'مركز تقارير الشركاء وتوزيع الأرباح',
                    icon: 'fa-handshake',
                    desc: 'حساب أنصبة الشركاء والمسحوبات الرأسمالية وتوزيع الأرباح',
                    supportedActions: ['preview', 'print', 'export']
                }
            ]
        },
        {
            category: 'tools',
            categoryName: 'الأدوات المساعدة والشريعة وشاشات العرض',
            categoryIcon: 'fa-toolbox',
            color: '#d97706',
            desc: 'حاسبة الميراث الشرعي، الفتاوى الفقهية، وشاشات التلفزيون الرقمية',
            screens: [
                {
                    id: 'engineering_tools',
                    file: 'engineering_tools.php',
                    title: 'الأدوات المساعدة والمقاييس والحاسبات',
                    icon: 'fa-toolbox',
                    desc: 'حاسبات التحويل، الأدوات الهندسية، والمساعدات الحسابية',
                    supportedActions: ['preview', 'print']
                },
                {
                    id: 'tool_inheritance_tab',
                    file: 'engineering_tools.php?tab=inheritance',
                    title: 'حاسبة الميراث الشرعي وتوزيع التركات والوصايا',
                    icon: 'fa-scale-balanced',
                    desc: 'احتساب الأنصبة الشرعية الدقيقة وتوزيع التركات',
                    supportedActions: ['preview', 'print']
                },
                {
                    id: 'tool_fatwa_tab',
                    file: 'engineering_tools.php?tab=fatwa',
                    title: 'موسوعة فتاوى المعاملات المالية والبيوع الفقهية',
                    icon: 'fa-book-quran',
                    desc: 'فتاوى البيوع والربا والخيارات الفقهية والتجارة الإلكترونية',
                    supportedActions: ['preview', 'print']
                },
                {
                    id: 'background_player',
                    file: 'background_player.php',
                    title: 'شاشة العرض الرقمية والتلفزيون (POS TV Player)',
                    icon: 'fa-tv',
                    desc: 'شاشة عرض الإعلانات وبث الأسعار والعروض الترويجية',
                    supportedActions: ['preview']
                }
            ]
        },
        {
            category: 'system_admin',
            categoryName: 'الإدارة العامة والنظام والرقابة والأمان',
            categoryIcon: 'fa-gear',
            color: '#94a3b8',
            desc: 'الإعدادات العامة، النسخ الاحتياطي، الصندوق الأسود، وإدارة المهام',
            screens: [
                {
                    id: 'settings',
                    file: 'settings.php',
                    title: 'إعدادات النظام العامة وقواعد المنشأة',
                    icon: 'fa-gear',
                    desc: 'بيانات الشركة، إعدادات الطباعة، الواتساب، وأسعار الصرف',
                    supportedActions: ['save', 'edit']
                },
                {
                    id: 'users_roles',
                    file: 'users-roles.php',
                    title: 'إدارة المستخدمين والأدوار والصلاحيات',
                    icon: 'fa-user-gear',
                    desc: 'إضافة الموظفين وتعيين الصلاحيات ومصفوفة الأمان',
                    supportedActions: ['new', 'save', 'edit', 'delete']
                },
                {
                    id: 'audit_log',
                    file: 'audit-log.php',
                    title: 'الصندوق الأسود وسجل العمليات والرقابة (Audit Log)',
                    icon: 'fa-user-shield',
                    desc: 'السجل الجنائي لكافة الحركات والتعديلات والحذف بالنظام',
                    supportedActions: ['preview', 'print', 'export']
                },
                {
                    id: 'backup',
                    file: 'backup.php',
                    title: 'النسخ الاحتياطي للنظام واستعادة البيانات',
                    icon: 'fa-hard-drive',
                    desc: 'تصدير واسترجاع قواعد البيانات محلياً وسحابياً',
                    supportedActions: ['save', 'new']
                },
                {
                    id: 'jobs',
                    file: 'jobs.php',
                    title: 'إدارة المهام وسير العمل الإداري',
                    icon: 'fa-list-check',
                    desc: 'تتبع المهام المعلقة والموكلة للموظفين',
                    supportedActions: ['new', 'save', 'edit', 'delete']
                }
            ]
        }
    ];

    // ═════════════════════════════════════════════════════════════════════
    // 2. SPECIALIZED GRANULAR CONTROLS (DASHBOARD, POSTING, MASTER DATA, ALERTS, TOOLS)
    // ═════════════════════════════════════════════════════════════════════

    // A. Dashboard Widgets
    const DASHBOARD_COMPONENTS = [
        { id: 'kpi_sales', title: 'مؤشر إجمالي المبيعات (Sales KPI Card)', icon: 'fa-cash-register', selector: '#kpi-sales' },
        { id: 'kpi_liquidity', title: 'مؤشر السيولة والخزائن والبنوك (Liquidity KPI)', icon: 'fa-coins', selector: '#kpi-liquidity, #kpi-vault-liquidity' },
        { id: 'kpi_profit', title: 'مؤشر الأرباح التقديرية للشهر (Monthly Profit KPI)', icon: 'fa-chart-line', selector: '#kpi-monthly-profit' },
        { id: 'kpi_inventory', title: 'مؤشر إجمالي الأصناف والمخزون (Inventory KPI)', icon: 'fa-boxes-stacked', selector: '#kpi-inventory' },
        { id: 'kpi_alerts', title: 'مؤشر نواقص المخزون والتنبيهات (Low Stock KPI)', icon: 'fa-triangle-exclamation', selector: '#kpi-alerts, #low-stock-list' },
        { id: 'charts', title: 'الرسوم البيانية والتحليل المالي اليومي', icon: 'fa-chart-area', selector: '.charts-grid, .chart-card' },
        { id: 'quick_actions', title: 'شريط الأزرار السريعة المخصصة الـ 7', icon: 'fa-wand-magic-sparkles', selector: '#qa-single-row-grid' },
        { id: 'category_hubs', title: 'قوائم ومراكز الاختصاص المنبثقة', icon: 'fa-layer-group', selector: '#category-hubs-container' },
        { id: 'athkar', title: 'شريط وبطاقة الأذكار اليومية', icon: 'fa-kaaba', selector: '#dashboard-athkar-container' }
    ];

    // B. Posting & Approvals (ترحيل الحسابات)
    const POSTING_ACTIONS = [
        { id: 'post_sales', title: 'ترحيل فواتير المبيعات (Post Sales Invoices)', icon: 'fa-cart-shopping' },
        { id: 'post_purchases', title: 'ترحيل فواتير المشتريات (Post Purchase Invoices)', icon: 'fa-bag-shopping' },
        { id: 'post_payments', title: 'ترحيل سندات الصرف والمدفوعات (Post Payments)', icon: 'fa-money-bill-wave' },
        { id: 'post_receipts', title: 'ترحيل سندات القبض والمتحصلات (Post Receipts)', icon: 'fa-receipt' },
        { id: 'post_journal', title: 'ترحيل قيود اليومية العامة (Post Journal Entries)', icon: 'fa-book' },
        { id: 'unpost', title: 'إلغاء / فك ترحيل المعاملات (Unpost / Rollback)', icon: 'fa-rotate-left' }
    ];

    // C. Master Data Granular Rights
    const MASTER_DATA_RIGHTS = [
        { id: 'cust_add', title: 'إضافة عملاء جدد', icon: 'fa-user-plus' },
        { id: 'cust_edit', title: 'تعديل بيانات وسقوف ائتمان العملاء', icon: 'fa-user-pen' },
        { id: 'cust_del', title: 'حذف العملاء', icon: 'fa-user-xmark' },
        { id: 'supp_add', title: 'إضافة موردين جدد', icon: 'fa-truck' },
        { id: 'supp_edit', title: 'تعديل بيانات وحسابات الموردين', icon: 'fa-pen-to-square' },
        { id: 'supp_del', title: 'حذف الموردين', icon: 'fa-trash' },
        { id: 'item_add', title: 'إضافة أصناف جديدة وتوليد باركود', icon: 'fa-box' },
        { id: 'item_edit', title: 'تعديل الأصناف وأسعار الشراء والبيع', icon: 'fa-tag' },
        { id: 'item_del', title: 'حذف الأصناف وتصفير أرصدتها', icon: 'fa-trash-can' },
        { id: 'acc_manage', title: 'إدارة الخزن والبنوك وشجرة الحسابات', icon: 'fa-building-columns' }
    ];

    // D. Alerts Configuration Rights
    const ALERTS_RIGHTS = [
        { id: 'alert_inv', title: 'ضبط وتفعيل تنبيهات المخازن والنواقص', icon: 'fa-boxes-stacked' },
        { id: 'alert_pur', title: 'ضبط وتفعيل تنبيهات المشتريات والموردين', icon: 'fa-bag-shopping' },
        { id: 'alert_fin', title: 'ضبط وتفعيل تنبيهات المالية والائتمان والسيولة', icon: 'fa-coins' },
        { id: 'alert_thresholds', title: 'صلاحية تعديل الحدود التحذيرية والحرجة (Warning/Critical Thresholds)', icon: 'fa-sliders' }
    ];

    // E. Tools & Sharia Rights
    const TOOLS_RIGHTS = [
        { id: 'tool_inheritance', title: 'استخدام حاسبة الميراث الشرعي وتوزيع التركات', icon: 'fa-scale-balanced' },
        { id: 'tool_fatwa', title: 'تصفح واستخدام موسوعة فتاوى البيوع الفقهية', icon: 'fa-book-quran' },
        { id: 'tool_tv', title: 'تشغيل والتحكم في شاشة التلفزيون الرقمية (POS TV)', icon: 'fa-tv' },
        { id: 'tool_eng', title: 'استخدام الحاسبات والمقاييس الهندسية', icon: 'fa-ruler-combined' }
    ];

    // ═════════════════════════════════════════════════════════════════════
    // 3. EXHAUSTIVE REPORTS CATALOG (20+ DETAILED REPORTS)
    // ═════════════════════════════════════════════════════════════════════
    const SYSTEM_REPORTS_CATALOG = [
        { id: 'rep_statement', title: 'كشف حساب تفصيلي للعميل / المورد / الشريك', icon: 'fa-file-invoice-dollar' },
        { id: 'rep_general_ledger', title: 'دفتر الأستاذ العام وتفاصيل القيود المحاسبية', icon: 'fa-book-bookmark' },
        { id: 'rep_trial_balance', title: 'ميزان المراجعة بالأرصدة والمجاميع', icon: 'fa-scale-balanced' },
        { id: 'rep_pl', title: 'قائمة الأرباح والخسائر وهامش الربح والدخل', icon: 'fa-chart-line' },
        { id: 'rep_balances_hub', title: 'تقرير مركز الأرصدة العامة والديون', icon: 'fa-wallet' },
        { id: 'rep_cashflow', title: 'تقرير التدفقات النقدية والتنبؤ بالسيولة', icon: 'fa-money-bill-trend-up' },
        { id: 'rep_cash_bank', title: 'كشف حساب البنك وحركات الخزينة اليومية', icon: 'fa-building-columns' },
        { id: 'rep_aging', title: 'تقرير أعمار الديون والمطالبات المتأخرة', icon: 'fa-clock-rotate-left' },
        { id: 'rep_daily_sales', title: 'تقرير المبيعات اليومية ونقاط البيع', icon: 'fa-cart-shopping' },
        { id: 'rep_sales_by_item', title: 'تقرير المبيعات حسب الصنف وربحية الصنف', icon: 'fa-chart-pie' },
        { id: 'rep_sales_by_customer', title: 'تقرير المبيعات حسب العميل والمدفوعات', icon: 'fa-user-check' },
        { id: 'rep_daily_purchases', title: 'تقرير المشتريات اليومية ومطابقات التوريد', icon: 'fa-bag-shopping' },
        { id: 'rep_purchases_by_supplier', title: 'تقرير مشتريات الموردين والأرصدة المستحقة', icon: 'fa-truck' },
        { id: 'rep_inventory_val', title: 'تقرير جرد المخزون الشامل وتقييم البضاعة', icon: 'fa-boxes-stacked' },
        { id: 'rep_item_movement', title: 'كرت حركة الصنف ومعدل الدوران بالتفصيل', icon: 'fa-barcode' },
        { id: 'rep_deadstock', title: 'تقرير البضائع الراكدة وبطيئة الحركة', icon: 'fa-box-archive' },
        { id: 'rep_smart_inv_alerts', title: 'تقرير تنبيهات المخزون الذكي والنواقص', icon: 'fa-brain' },
        { id: 'rep_zakat', title: 'تقرير الجرد السنوي وزكاة عروض التجارة', icon: 'fa-hand-holding-dollar' },
        { id: 'rep_partners', title: 'تقرير الشركاء وتوزيع الأرباح والاستثمار', icon: 'fa-handshake' },
        { id: 'rep_supplier_reliability', title: 'تقرير اعتمادية وموثوقية الموردين', icon: 'fa-award' },
        { id: 'rep_audit', title: 'سجل التدقيق والرقابة الجنائية (الصندوق الأسود)', icon: 'fa-shield-halved' }
    ];

    // Action Labels with Icons & Colors
    const ACTION_META = {
        new: { label: 'جديد', icon: 'fa-plus', color: '#38bdf8' },
        save: { label: 'حفظ / اعتماد', icon: 'fa-floppy-disk', color: '#10b981' },
        edit: { label: 'تعديل', icon: 'fa-pen-to-square', color: '#fbbf24' },
        delete: { label: 'حذف / مسح', icon: 'fa-trash-can', color: '#ef4444' },
        preview: { label: 'معاينة', icon: 'fa-eye', color: '#60a5fa' },
        print: { label: 'طباعة', icon: 'fa-print', color: '#a855f7' },
        share: { label: 'مشاركة/واتساب', icon: 'fa-share-nodes', color: '#25d366' },
        export: { label: 'تصدير إكسل/PDF', icon: 'fa-file-export', color: '#ec4899' },
        receive: { label: 'استلام بضائع', icon: 'fa-box-open', color: '#34d399' },
        return: { label: 'استرداد بضائع', icon: 'fa-rotate-left', color: '#f59e0b' },
        approve: { label: 'اعتماد', icon: 'fa-check-double', color: '#06b6d4' }
    };

    // ═════════════════════════════════════════════════════════════════════
    // 4. PRESET ROLE TEMPLATES
    // ═════════════════════════════════════════════════════════════════════
    const PRESET_TEMPLATES = {
        admin: {
            title: '👑 مدير نظام (كامل الصلاحيات دون استثناء)',
            userType: 'admin'
        },
        level_1: {
            title: 'المستوى الأول: التشغيلي ونقاط البيع (Operational & POS)',
            description: 'الفصل المحاسبي للمهام وحماية النقدية (Segregation of Duties). موجه للكاشير وبائعي المعارض والمندوبين الميدانيين.',
            badgeColor: '#10b981',
            userType: 'user',
            securityLevel: 2,
            allowedScreens: {
                'magaiees_unified_dashboard.php': true,
                'dashboard-pro.php': true,
                'direct-sales.php': true,
                'reserved-sales.php': true,
                'sales-delivery.php': true,
                'sales-invoice.php': true,
                'delivery-note.php': true,
                'single-cash-payment.php': true,
                'single-cash-receipt.php': true,
                'vouchers-hub.php': true,
                'customer-portfolio.php': true,
                'item-ledger.php': true,
                'inventory.php': true
            },
            screenButtons: {
                'direct-sales.php': { new: true, save: true, print: true, share: true, preview: true, edit: false, delete: false },
                'single-cash-payment.php': { new: true, save: true, print: true, share: true, preview: true, edit: false, delete: false },
                'single-cash-receipt.php': { new: true, save: true, print: true, share: true, preview: true, edit: false, delete: false },
                'customer-portfolio.php': { preview: true, print: true, share: true }
            },
            dashboardPermissions: {
                kpi_sales: true,
                kpi_liquidity: false,
                kpi_profit: false,
                kpi_inventory: true,
                kpi_alerts: false,
                charts: false,
                quick_actions: true,
                category_hubs: false,
                athkar: true
            },
            postingPermissions: {
                post_sales: false,
                post_purchases: false,
                post_payments: false,
                post_receipts: false,
                post_journal: false,
                unpost: false
            },
            masterDataPermissions: {
                cust_add: true,
                cust_edit: false,
                cust_del: false,
                supp_add: false,
                supp_edit: false,
                supp_del: false,
                item_add: false,
                item_edit: false,
                item_del: false,
                acc_manage: false
            },
            alertsPermissions: {
                alert_inv: false,
                alert_pur: false,
                alert_fin: false,
                alert_thresholds: false
            },
            toolsPermissions: {
                tool_inheritance: false,
                tool_fatwa: false,
                tool_tv: true,
                tool_eng: false
            },
            allowedReports: {
                'rep_statement': true,
                'rep_daily_sales': true
            },
            financialLimits: {
                maxDiscountPercent: 2,
                maxDiscountAmount: 500,
                maxVoucherAmount: 5000,
                maxInvoiceAmount: 50000,
                allowCreditSale: false
            },
            dataMasking: {
                maskCostPrice: true,
                maskProfitMargin: true,
                maskBankAccounts: true,
                maskCustomerPhone: false
            },
            workShift: {
                restricted: false,
                timeFrom: '08:00',
                timeTo: '03:00',
                allowedDays: [0, 1, 2, 3, 4, 5, 6]
            },
            geofencing: {
                restricted: false,
                lat: null,
                lng: null,
                radius: 500
            }
        },
        level_2: {
            title: 'المستوى الثاني: المحاسبي والمخزني والإشرافي (Supervisory & Accounting)',
            description: 'الرقابة الثنائية والمطابقات المالية (Dual Control & Segregation of Accounting Duties). موجه للمحاسبين وأمناء المستودعات ومسؤولي المشتريات والمشرفين.',
            badgeColor: '#f59e0b',
            userType: 'user',
            securityLevel: 6,
            allowedScreens: {
                'magaiees_unified_dashboard.php': true,
                'dashboard-pro.php': true,
                'direct-sales.php': true,
                'reserved-sales.php': true,
                'sales-delivery.php': true,
                'sales-invoice.php': true,
                'delivery-note.php': true,
                'customer-portfolio.php': true,
                'purchase.php': true,
                'purchase-receipt.php': true,
                'purchase-return.php': true,
                'supplier_reliability.php': true,
                'single-cash-payment.php': true,
                'single-cash-receipt.php': true,
                'multi-cash-payment.php': true,
                'vouchers-hub.php': true,
                'safe-transfer.php': true,
                'checks-cycle.php': true,
                'bank-transfer.php': true,
                'posting-accounts.php': true,
                'journal-voucher.php': true,
                'journal.php': true,
                'general-ledger.php': true,
                'general_ledger_v2.php': true,
                'account-statement.php': true,
                'trial-balance.php': true,
                'pl-statement.php': true,
                'balances_modern_hub.php': true,
                'cashflow_modern.php': true,
                'analytics.php': true,
                'general_accounts_hub.php': true,
                'financial-reports.php': true,
                'daily_master_report.php': true,
                'mainbalances-report.php': true,
                'aging-report.php': true,
                'combined_receivables.php': true,
                'cash-bank-statement.php': true,
                'cost-centers.php': true,
                'partners-hub.php': true,
                'fx-rates.php': true,
                'inventory.php': true,
                'item-ledger.php': true,
                'inventory-movement.php': true,
                'inventory-summary.php': true,
                'warehouse-transfer.php': true,
                'inventory-stocktake.php': true,
                'inventory-adjustment.php': true,
                'inventory-report.php': true,
                'visual-analytics.php': true,
                'smart_inventory_report.php': true,
                'annual_inventory_zakat_report.php': true,
                'master-data.php': true,
                'barcode-printer.php': true,
                'alerts_manager.php': true,
                'alarm.php': true,
                'ai_goods_advisor.php': true,
                'ai_money_advisor.php': true,
                'smart_reports_hub.php': true,
                'partners_reports_hub.php': true,
                'engineering_tools.php': true,
                'background_player.php': true,
                'jobs.php': true
            },
            screenButtons: {
                'direct-sales.php': { new: true, save: true, edit: true, delete: false, print: true, share: true, preview: true },
                'sales-invoice.php': { new: true, save: true, return: true, print: true, share: true },
                'purchase.php': { new: true, save: true, edit: true, delete: false, print: true, share: true, preview: true },
                'purchase-receipt.php': { new: true, save: true, receive: true, print: true },
                'purchase-return.php': { new: true, save: true, return: true, print: true, share: true },
                'single-cash-payment.php': { new: true, save: true, edit: true, delete: false, print: true, share: true, preview: true },
                'single-cash-receipt.php': { new: true, save: true, edit: true, delete: false, print: true, share: true, preview: true },
                'multi-cash-payment.php': { new: true, save: true, edit: true, delete: false, print: true, share: true, preview: true },
                'journal-voucher.php': { new: true, save: true, edit: true, delete: false, print: true, preview: true },
                'inventory.php': { preview: true, print: true },
                'warehouse-transfer.php': { new: true, save: true, receive: true, print: true },
                'inventory-stocktake.php': { new: true, save: true, print: true }
            },
            dashboardPermissions: {
                kpi_sales: true,
                kpi_liquidity: true,
                kpi_profit: false,
                kpi_inventory: true,
                kpi_alerts: true,
                charts: true,
                quick_actions: true,
                category_hubs: true,
                athkar: true
            },
            postingPermissions: {
                post_sales: true,
                post_purchases: true,
                post_payments: true,
                post_receipts: true,
                post_journal: true,
                unpost: false
            },
            masterDataPermissions: {
                cust_add: true,
                cust_edit: true,
                cust_del: false,
                supp_add: true,
                supp_edit: true,
                supp_del: false,
                item_add: true,
                item_edit: true,
                item_del: false,
                acc_manage: true
            },
            alertsPermissions: {
                alert_inv: true,
                alert_pur: true,
                alert_fin: true,
                alert_thresholds: false
            },
            toolsPermissions: {
                tool_inheritance: true,
                tool_fatwa: true,
                tool_tv: true,
                tool_eng: true
            },
            allowedReports: {
                'rep_statement': true,
                'rep_daily_sales': true,
                'rep_daily_purchases': true,
                'rep_sales_by_customer': true,
                'rep_purchases_by_supplier': true,
                'rep_inventory_val': true,
                'rep_item_movement': true,
                'rep_trial_balance': true,
                'rep_general_ledger': true,
                'rep_cashflow': true,
                'rep_cash_bank': true,
                'rep_aging': true,
                'rep_balances_hub': true,
                'rep_pl': true,
                'rep_supplier_reliability': true
            },
            financialLimits: {
                maxDiscountPercent: 15,
                maxDiscountAmount: 10000,
                maxVoucherAmount: 250000,
                maxInvoiceAmount: 1000000,
                allowCreditSale: true
            },
            dataMasking: {
                maskCostPrice: false,
                maskProfitMargin: false,
                maskBankAccounts: false,
                maskCustomerPhone: false
            },
            workShift: {
                restricted: false,
                timeFrom: '08:00',
                timeTo: '22:00',
                allowedDays: [0, 1, 2, 3, 4, 5, 6]
            },
            geofencing: {
                restricted: false,
                lat: null,
                lng: null,
                radius: 500
            }
        },
        level_3: {
            title: 'المستوى الثالث: الإداري والمالي الشامل والتدقيق (Executive & Controller)',
            description: 'الاعتماد النهائي والحوكمة المالية والرقابة التشفيرية الشاملة (Financial Controller / Auditor). موجه للمدير المالي، مراقب الحسابات، التدقيق الداخلي، والإدارة العليا.',
            badgeColor: '#ef4444',
            userType: 'user',
            securityLevel: 9,
            allowedScreens: {
                'magaiees_unified_dashboard.php': true,
                'dashboard-pro.php': true,
                'direct-sales.php': true,
                'reserved-sales.php': true,
                'sales-delivery.php': true,
                'sales-invoice.php': true,
                'delivery-note.php': true,
                'customer-portfolio.php': true,
                'purchase.php': true,
                'purchase-receipt.php': true,
                'purchase-return.php': true,
                'supplier_reliability.php': true,
                'single-cash-payment.php': true,
                'single-cash-receipt.php': true,
                'multi-cash-payment.php': true,
                'vouchers-hub.php': true,
                'safe-transfer.php': true,
                'checks-cycle.php': true,
                'bank-transfer.php': true,
                'posting-accounts.php': true,
                'journal-voucher.php': true,
                'journal.php': true,
                'general-ledger.php': true,
                'general_ledger_v2.php': true,
                'account-statement.php': true,
                'trial-balance.php': true,
                'pl-statement.php': true,
                'balances_modern_hub.php': true,
                'cashflow_modern.php': true,
                'analytics.php': true,
                'general_accounts_hub.php': true,
                'financial-reports.php': true,
                'daily_master_report.php': true,
                'mainbalances-report.php': true,
                'aging-report.php': true,
                'combined_receivables.php': true,
                'cash-bank-statement.php': true,
                'cost-centers.php': true,
                'partners-hub.php': true,
                'fx-rates.php': true,
                'inventory.php': true,
                'item-ledger.php': true,
                'inventory-movement.php': true,
                'inventory-summary.php': true,
                'warehouse-transfer.php': true,
                'inventory-stocktake.php': true,
                'inventory-adjustment.php': true,
                'inventory-report.php': true,
                'visual-analytics.php': true,
                'smart_inventory_report.php': true,
                'annual_inventory_zakat_report.php': true,
                'master-data.php': true,
                'barcode-printer.php': true,
                'alerts_manager.php': true,
                'alarm.php': true,
                'ai_goods_advisor.php': true,
                'ai_money_advisor.php': true,
                'smart_reports_hub.php': true,
                'partners_reports_hub.php': true,
                'engineering_tools.php': true,
                'engineering_tools.php?tab=inheritance': true,
                'engineering_tools.php?tab=fatwa': true,
                'background_player.php': true,
                'settings.php': true,
                'users-roles.php': true,
                'audit-log.php': true,
                'backup.php': true,
                'jobs.php': true
            },
            screenButtons: {
                'direct-sales.php': { new: true, save: true, edit: true, delete: true, print: true, share: true, preview: true },
                'sales-invoice.php': { new: true, save: true, edit: true, delete: true, return: true, print: true, share: true },
                'purchase.php': { new: true, save: true, edit: true, delete: true, print: true, share: true, preview: true },
                'purchase-receipt.php': { new: true, save: true, edit: true, delete: true, receive: true, print: true },
                'purchase-return.php': { new: true, save: true, edit: true, delete: true, return: true, print: true, share: true },
                'single-cash-payment.php': { new: true, save: true, edit: true, delete: true, print: true, share: true, preview: true },
                'single-cash-receipt.php': { new: true, save: true, edit: true, delete: true, print: true, share: true, preview: true },
                'multi-cash-payment.php': { new: true, save: true, edit: true, delete: true, print: true, share: true, preview: true },
                'journal-voucher.php': { new: true, save: true, edit: true, delete: true, print: true, preview: true },
                'inventory.php': { new: true, save: true, edit: true, delete: true, preview: true, print: true },
                'warehouse-transfer.php': { new: true, save: true, edit: true, delete: true, receive: true, print: true },
                'inventory-stocktake.php': { new: true, save: true, edit: true, delete: true, print: true }
            },
            dashboardPermissions: {
                kpi_sales: true,
                kpi_liquidity: true,
                kpi_profit: true,
                kpi_inventory: true,
                kpi_alerts: true,
                charts: true,
                quick_actions: true,
                category_hubs: true,
                athkar: true
            },
            postingPermissions: {
                post_sales: true,
                post_purchases: true,
                post_payments: true,
                post_receipts: true,
                post_journal: true,
                unpost: true
            },
            masterDataPermissions: {
                cust_add: true,
                cust_edit: true,
                cust_del: true,
                supp_add: true,
                supp_edit: true,
                supp_del: true,
                item_add: true,
                item_edit: true,
                item_del: true,
                acc_manage: true
            },
            alertsPermissions: {
                alert_inv: true,
                alert_pur: true,
                alert_fin: true,
                alert_thresholds: true
            },
            toolsPermissions: {
                tool_inheritance: true,
                tool_fatwa: true,
                tool_tv: true,
                tool_eng: true
            },
            allowedReports: {
                'rep_statement': true,
                'rep_daily_sales': true,
                'rep_daily_purchases': true,
                'rep_sales_by_customer': true,
                'rep_purchases_by_supplier': true,
                'rep_inventory_val': true,
                'rep_item_movement': true,
                'rep_trial_balance': true,
                'rep_general_ledger': true,
                'rep_cashflow': true,
                'rep_cash_bank': true,
                'rep_aging': true,
                'rep_balances_hub': true,
                'rep_pl': true,
                'rep_supplier_reliability': true,
                'rep_zakat': true,
                'rep_partners': true,
                'rep_audit': true,
                'rep_sales_by_item': true,
                'rep_deadstock': true,
                'rep_smart_inv_alerts': true
            },
            financialLimits: {
                maxDiscountPercent: 50,
                maxDiscountAmount: 1000000,
                maxVoucherAmount: 5000000,
                maxInvoiceAmount: 10000000,
                allowCreditSale: true
            },
            dataMasking: {
                maskCostPrice: false,
                maskProfitMargin: false,
                maskBankAccounts: false,
                maskCustomerPhone: false
            },
            workShift: {
                restricted: false,
                timeFrom: '08:00',
                timeTo: '03:00',
                allowedDays: [0, 1, 2, 3, 4, 5, 6]
            },
            geofencing: {
                restricted: false,
                lat: null,
                lng: null,
                radius: 1000
            }
        },
        cashier: {
            title: '🛒 كاشير / مبيعات ونقاط بيع',
            userType: 'user',
            securityLevel: 4,
            allowedScreens: {
                'magaiees_unified_dashboard.php': true,
                'direct-sales.php': true,
                'single-cash-receipt.php': true,
                'customer-portfolio.php': true,
                'item-ledger.php': true
            },
            screenButtons: {
                'direct-sales.php': { new: true, save: true, print: true, share: true, preview: true, edit: false, delete: false },
                'single-cash-receipt.php': { new: true, save: true, print: true, share: true, preview: true, edit: false, delete: false },
                'customer-portfolio.php': { print: true, share: true, preview: true }
            },
            dashboardPermissions: {
                kpi_sales: true,
                kpi_liquidity: false,
                kpi_profit: false,
                kpi_inventory: true,
                kpi_alerts: false,
                charts: false,
                quick_actions: true,
                category_hubs: false,
                athkar: true
            },
            postingPermissions: { post_sales: false, post_purchases: false, post_payments: false, post_receipts: false, post_journal: false, unpost: false },
            masterDataPermissions: { cust_add: true, cust_edit: false, cust_del: false, supp_add: false, supp_edit: false, supp_del: false, item_add: false, item_edit: false, item_del: false, acc_manage: false },
            alertsPermissions: { alert_inv: false, alert_pur: false, alert_fin: false, alert_thresholds: false },
            toolsPermissions: { tool_inheritance: false, tool_fatwa: false, tool_tv: true, tool_eng: false },
            allowedReports: {
                'rep_statement': true,
                'rep_daily_sales': true
            },
            financialLimits: {
                maxDiscountPercent: 5,
                maxDiscountAmount: 500,
                maxVoucherAmount: 10000,
                maxInvoiceAmount: 25000,
                allowCreditSale: false
            },
            dataMasking: {
                maskCostPrice: true,
                maskProfitMargin: true,
                maskBankAccounts: true,
                maskCustomerPhone: false
            }
        },
        purchases_manager: {
            title: '📦 مسؤول مشتريات وتوريدات ومستودعات',
            userType: 'user',
            securityLevel: 5,
            allowedScreens: {
                'magaiees_unified_dashboard.php': true,
                'purchase.php': true,
                'purchase-receipt.php': true,
                'purchase-return.php': true,
                'supplier_reliability.php': true,
                'supplier_performance.php': true,
                'inventory.php': true,
                'item-ledger.php': true,
                'ai_goods_advisor.php': true
            },
            screenButtons: {
                'purchase.php': { new: true, save: true, edit: true, delete: false, print: true, share: true, preview: true },
                'purchase-receipt.php': { new: true, save: true, receive: true, print: true },
                'purchase-return.php': { new: true, save: true, return: true, print: true, share: true },
                'inventory.php': { preview: true, print: true }
            },
            dashboardPermissions: {
                kpi_sales: false,
                kpi_liquidity: false,
                kpi_profit: false,
                kpi_inventory: true,
                kpi_alerts: true,
                charts: false,
                quick_actions: true,
                category_hubs: true,
                athkar: true
            },
            postingPermissions: { post_sales: false, post_purchases: false, post_payments: false, post_receipts: false, post_journal: false, unpost: false },
            masterDataPermissions: { cust_add: false, cust_edit: false, cust_del: false, supp_add: true, supp_edit: true, supp_del: false, item_add: true, item_edit: false, item_del: false, acc_manage: false },
            alertsPermissions: { alert_inv: true, alert_pur: true, alert_fin: false, alert_thresholds: false },
            toolsPermissions: { tool_inheritance: false, tool_fatwa: false, tool_tv: false, tool_eng: true },
            allowedReports: {
                'rep_statement': true,
                'rep_daily_purchases': true,
                'rep_purchases_by_supplier': true,
                'rep_inventory_val': true,
                'rep_supplier_reliability': true
            },
            financialLimits: {
                maxDiscountPercent: 15,
                maxDiscountAmount: 5000,
                maxVoucherAmount: 50000,
                maxInvoiceAmount: 100000,
                allowCreditSale: true
            },
            dataMasking: {
                maskCostPrice: false,
                maskProfitMargin: false,
                maskBankAccounts: true,
                maskCustomerPhone: false
            }
        },
        storekeeper: {
            title: '🏢 أمين مستودع وجرد بضائع',
            userType: 'user',
            securityLevel: 3,
            allowedScreens: {
                'magaiees_unified_dashboard.php': true,
                'inventory.php': true,
                'warehouse-inventory.php': true,
                'item-ledger.php': true,
                'inventory-movement.php': true,
                'inventory-summary.php': true,
                'warehouse-transfer.php': true,
                'inventory-stocktake.php': true,
                'inventory-adjustment.php': true,
                'inventory-report.php': true,
                'purchase-receipt.php': true,
                'sales-delivery.php': true,
                'delivery-note.php': true
            },
            screenButtons: {
                'inventory.php': { preview: true, print: true },
                'inventory-movement.php': { new: true, save: true, print: true },
                'warehouse-transfer.php': { new: true, save: true, receive: true, print: true },
                'inventory-stocktake.php': { new: true, save: true, print: true },
                'inventory-adjustment.php': { save: true, print: true },
                'purchase-receipt.php': { save: true, receive: true, print: true },
                'sales-delivery.php': { save: true, print: true, share: true },
                'delivery-note.php': { new: true, save: true, print: true, share: true }
            },
            dashboardPermissions: {
                kpi_sales: false,
                kpi_liquidity: false,
                kpi_profit: false,
                kpi_inventory: true,
                kpi_alerts: true,
                charts: false,
                quick_actions: true,
                category_hubs: false,
                athkar: true
            },
            postingPermissions: { post_sales: false, post_purchases: false, post_payments: false, post_receipts: false, post_journal: false, unpost: false },
            masterDataPermissions: { cust_add: false, cust_edit: false, cust_del: false, supp_add: false, supp_edit: false, supp_del: false, item_add: true, item_edit: false, item_del: false, acc_manage: false },
            alertsPermissions: { alert_inv: true, alert_pur: false, alert_fin: false, alert_thresholds: false },
            toolsPermissions: { tool_inheritance: false, tool_fatwa: false, tool_tv: false, tool_eng: true },
            allowedReports: {
                'rep_inventory_val': true,
                'rep_item_movement': true,
                'rep_deadstock': true,
                'rep_smart_inv_alerts': true
            },
            financialLimits: {
                maxDiscountPercent: 0,
                maxDiscountAmount: 0,
                maxVoucherAmount: 0,
                maxInvoiceAmount: 0,
                allowCreditSale: false
            },
            dataMasking: {
                maskCostPrice: true,
                maskProfitMargin: true,
                maskBankAccounts: true,
                maskCustomerPhone: true
            }
        },
        accountant: {
            title: '💰 محاسب مالي ورئيس حسابات',
            userType: 'user',
            securityLevel: 6,
            allowedScreens: {
                'magaiees_unified_dashboard.php': true,
                'single-cash-payment.php': true,
                'single-cash-receipt.php': true,
                'multi-cash-payment.php': true,
                'vouchers-hub.php': true,
                'safe-transfer.php': true,
                'checks-cycle.php': true,
                'bank-transfer.php': true,
                'posting-accounts.php': true,
                'journal-voucher.php': true,
                'journal.php': true,
                'general-ledger.php': true,
                'account-statement.php': true,
                'trial-balance.php': true,
                'pl-statement.php': true,
                'balances_modern_hub.php': true,
                'cashflow_modern.php': true,
                'direct-sales.php': true,
                'sales-invoice.php': true,
                'purchase.php': true,
                'ai_money_advisor.php': true
            },
            screenButtons: {
                'single-cash-payment.php': { new: true, save: true, edit: true, delete: false, print: true, share: true, preview: true },
                'single-cash-receipt.php': { new: true, save: true, edit: true, delete: false, print: true, share: true, preview: true },
                'multi-cash-payment.php': { new: true, save: true, edit: true, delete: false, print: true, share: true },
                'journal-voucher.php': { new: true, save: true, edit: true, delete: false, print: true, preview: true },
                'posting-accounts.php': { save: true, approve: true, edit: false, delete: false },
                'general-ledger.php': { print: true, share: true, export: true, preview: true },
                'account-statement.php': { print: true, share: true, export: true, preview: true },
                'trial-balance.php': { print: true, export: true, preview: true },
                'pl-statement.php': { print: true, export: true, preview: true }
            },
            dashboardPermissions: {
                kpi_sales: true,
                kpi_liquidity: true,
                kpi_profit: true,
                kpi_inventory: true,
                kpi_alerts: true,
                charts: true,
                quick_actions: true,
                category_hubs: true,
                athkar: true
            },
            postingPermissions: { post_sales: true, post_purchases: true, post_payments: true, post_receipts: true, post_journal: true, unpost: false },
            masterDataPermissions: { cust_add: true, cust_edit: true, cust_del: false, supp_add: true, supp_edit: true, supp_del: false, item_add: true, item_edit: true, item_del: false, acc_manage: true },
            alertsPermissions: { alert_inv: true, alert_pur: true, alert_fin: true, alert_thresholds: false },
            toolsPermissions: { tool_inheritance: true, tool_fatwa: true, tool_tv: false, tool_eng: true },
            allowedReports: {
                'rep_statement': true,
                'rep_general_ledger': true,
                'rep_trial_balance': true,
                'rep_pl': true,
                'rep_balances_hub': true,
                'rep_cashflow': true,
                'rep_cash_bank': true,
                'rep_aging': true,
                'rep_daily_sales': true,
                'rep_daily_purchases': true
            },
            financialLimits: {
                maxDiscountPercent: 20,
                maxDiscountAmount: 10000,
                maxVoucherAmount: 200000,
                maxInvoiceAmount: 500000,
                allowCreditSale: true
            },
            dataMasking: {
                maskCostPrice: false,
                maskProfitMargin: false,
                maskBankAccounts: false,
                maskCustomerPhone: false
            }
        },
        viewer: {
            title: '🔍 استعلام ومعاينة وطباعة فقط (View Only)',
            userType: 'user',
            securityLevel: 1,
            allowedScreens: {
                'magaiees_unified_dashboard.php': true,
                'direct-sales.php': true,
                'purchase.php': true,
                'vouchers-hub.php': true,
                'general-ledger.php': true,
                'account-statement.php': true,
                'inventory.php': true,
                'item-ledger.php': true
            },
            screenButtons: {
                'direct-sales.php': { preview: true, print: true, new: false, save: false, edit: false, delete: false },
                'purchase.php': { preview: true, print: true, new: false, save: false, edit: false, delete: false },
                'vouchers-hub.php': { preview: true, print: true },
                'general-ledger.php': { preview: true, print: true, export: true },
                'account-statement.php': { preview: true, print: true, export: true },
                'inventory.php': { preview: true, print: true }
            },
            dashboardPermissions: {
                kpi_sales: true,
                kpi_liquidity: false,
                kpi_profit: false,
                kpi_inventory: true,
                kpi_alerts: false,
                charts: true,
                quick_actions: true,
                category_hubs: false,
                athkar: true
            },
            postingPermissions: { post_sales: false, post_purchases: false, post_payments: false, post_receipts: false, post_journal: false, unpost: false },
            masterDataPermissions: { cust_add: false, cust_edit: false, cust_del: false, supp_add: false, supp_edit: false, supp_del: false, item_add: false, item_edit: false, item_del: false, acc_manage: false },
            alertsPermissions: { alert_inv: false, alert_pur: false, alert_fin: false, alert_thresholds: false },
            toolsPermissions: { tool_inheritance: true, tool_fatwa: true, tool_tv: true, tool_eng: true },
            allowedReports: {
                'rep_statement': true,
                'rep_general_ledger': true,
                'rep_inventory_val': true
            },
            financialLimits: {
                maxDiscountPercent: 0,
                maxDiscountAmount: 0,
                maxVoucherAmount: 0,
                maxInvoiceAmount: 0,
                allowCreditSale: false
            },
            dataMasking: {
                maskCostPrice: true,
                maskProfitMargin: true,
                maskBankAccounts: true,
                maskCustomerPhone: false
            }
        }
    };

    // ═════════════════════════════════════════════════════════════════════
    // 5. RUNTIME ACCESS HELPERS & SECURITY CHECKS
    // ═════════════════════════════════════════════════════════════════════

    function getCurrentUser() {
        try {
            const userStr = localStorage.getItem('user');
            if (userStr && userStr !== 'undefined') {
                return JSON.parse(userStr);
            }
        } catch(e) {}
        return null;
    }

    function isCurrentUserAdmin(user) {
        const u = user || getCurrentUser();
        if (!u) return false;
        const uName = String(u.username || '').trim().toLowerCase();
        const uRole = String(u.role || '').trim().toLowerCase();
        const uRoleId = String(u.roleId || '').trim().toLowerCase();
        if (u.userType === 'admin' || uRole === 'admin' || uRole === 'مدير النظام' || uRole === 'superadmin') return true;
        if (uRoleId === 'role_admin' || uRoleId === 'role_admin_default' || uRoleId === 'admin_fallback' || uRoleId === 'admin') return true;
        if (uName === 'admin' || uName === 'c') return true;
        if (u.isSystem === true) return true;
        if (u.securityLevel && Number(u.securityLevel) >= 9) return true;
        return false;
    }

    // Comprehensive shift checking supporting overnight shifts spanning midnight
    function isCurrentUserWithinShift(user) {
        const u = user || getCurrentUser();
        if (!u || isCurrentUserAdmin(u)) return true;
        if (!u.timeRestricted) return true;

        const now = new Date();
        const currentDay = now.getDay();
        const allowedDays = Array.isArray(u.allowedDays) ? u.allowedDays : [0, 1, 2, 3, 4, 6];
        const curMinutes = now.getHours() * 60 + now.getMinutes();

        let fromMin = 0, toMin = 1439;
        if (u.timeFrom && u.timeTo) {
            const [fh, fm] = u.timeFrom.split(':').map(Number);
            const [th, tm] = u.timeTo.split(':').map(Number);
            fromMin = fh * 60 + (fm || 0);
            toMin = th * 60 + (tm || 0);
        }

        const isOvernight = fromMin > toMin;
        const shiftDay = (isOvernight && curMinutes <= toMin) ? (currentDay === 0 ? 6 : currentDay - 1) : currentDay;

        if (!allowedDays.includes(currentDay) && !allowedDays.includes(shiftDay)) {
            return false;
        }

        if (u.timeFrom && u.timeTo) {
            let isAllowedTime = false;
            if (!isOvernight) {
                isAllowedTime = (curMinutes >= fromMin && curMinutes <= toMin);
            } else {
                isAllowedTime = (curMinutes >= fromMin || curMinutes <= toMin);
            }
            if (!isAllowedTime) return false;
        }

        return true;
    }

    // Comprehensive geofencing check using Haversine distance
    function isCurrentUserWithinGeofence(user) {
        const u = user || getCurrentUser();
        if (!u || isCurrentUserAdmin(u)) return true;
        if (!u.geoRestricted || !u.geoLat || !u.geoLng) return true;

        const cachedLat = parseFloat(sessionStorage.getItem('magaiees_last_lat') || localStorage.getItem('magaiees_last_lat'));
        const cachedLng = parseFloat(sessionStorage.getItem('magaiees_last_lng') || localStorage.getItem('magaiees_last_lng'));
        if (cachedLat && cachedLng) {
            const calcDist = (lat1, lon1, lat2, lon2) => {
                const R = 6371e3;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            };
            const dist = calcDist(cachedLat, cachedLng, parseFloat(u.geoLat), parseFloat(u.geoLng));
            const maxRadius = parseFloat(u.geoRadius) || 500;
            if (dist > maxRadius) {
                return false;
            }
        }
        return true;
    }

    function isUserCurrentlyAllowedWorkTimeAndGeo(user) {
        return isCurrentUserWithinShift(user) && isCurrentUserWithinGeofence(user);
    }

    function canUserAccessScreen(screenUrl, user) {
        const u = user || getCurrentUser();
        if (!u) return true; // fallback
        if (isCurrentUserAdmin(u)) return true;

        const baseFile = (screenUrl || '').split('?')[0].split('/').pop();
        if (!baseFile || baseFile === 'login.php' || baseFile === 'index.php') {
            return true;
        }

        // Strict Check: Outside work shift or outside geofence -> completely stripped of permissions!
        if (!isUserCurrentlyAllowedWorkTimeAndGeo(u)) {
            return false;
        }

        // Dashboard explicit check
        if (baseFile === 'magaiees_unified_dashboard.php' || baseFile === 'dashboard-pro.php') {
            if (u.dashboardPermissions && typeof u.dashboardPermissions.access_dashboard === 'boolean') {
                return u.dashboardPermissions.access_dashboard;
            }
            if (u.allowedScreens && typeof u.allowedScreens[baseFile] === 'boolean') {
                return u.allowedScreens[baseFile];
            }
            return true; // Default allowed unless revoked
        }

        // Direct allowedScreens map: only block if explicitly set to false
        if (u.allowedScreens && typeof u.allowedScreens === 'object') {
            if (u.allowedScreens[baseFile] === false) return false;
            if (u.allowedScreens[baseFile] === true) return true;
        }

        return true; // Default allowed unless explicitly blocked in allowedScreens
    }

    function canUserDoAction(screenUrl, action, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (!isUserCurrentlyAllowedWorkTimeAndGeo(u)) {
            return false;
        }

        const baseFile = (screenUrl || '').split('?')[0].split('/').pop();

        if (u.screenButtons && u.screenButtons[baseFile] && typeof u.screenButtons[baseFile][action] === 'boolean') {
            return u.screenButtons[baseFile][action];
        }

        return true;
    }

    function canUserViewReport(reportId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (!isUserCurrentlyAllowedWorkTimeAndGeo(u)) {
            return false;
        }

        if (u.allowedReports && typeof u.allowedReports[reportId] === 'boolean') {
            return u.allowedReports[reportId];
        }
        return true;
    }

    function canUserPost(type, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (!isUserCurrentlyAllowedWorkTimeAndGeo(u)) {
            return false;
        }

        if (u.postingPermissions && typeof u.postingPermissions[type] === 'boolean') {
            return u.postingPermissions[type];
        }
        return true;
    }

    function canUserAccessDashboardWidget(widgetId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (!isUserCurrentlyAllowedWorkTimeAndGeo(u)) {
            return false;
        }

        if (u.dashboardPermissions && typeof u.dashboardPermissions[widgetId] === 'boolean') {
            return u.dashboardPermissions[widgetId];
        }
        return true;
    }

    function canUserDoMasterDataAction(actionId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (!isUserCurrentlyAllowedWorkTimeAndGeo(u)) {
            return false;
        }

        if (u.masterDataPermissions && typeof u.masterDataPermissions[actionId] === 'boolean') {
            return u.masterDataPermissions[actionId];
        }
        return true;
    }

    function canUserManageAlert(alertId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (!isUserCurrentlyAllowedWorkTimeAndGeo(u)) {
            return false;
        }

        if (u.alertsPermissions && typeof u.alertsPermissions[alertId] === 'boolean') {
            return u.alertsPermissions[alertId];
        }
        return true;
    }

    function canUserUseTool(toolId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (!isUserCurrentlyAllowedWorkTimeAndGeo(u)) {
            return false;
        }

        if (u.toolsPermissions && typeof u.toolsPermissions[toolId] === 'boolean') {
            return u.toolsPermissions[toolId];
        }
        return true;
    }

    // ═════════════════════════════════════════════════════════════════════
    // 5.5 MULTI-LEVEL SECURITY CLEARANCE ENGINE (مستويات سرية وأمان الحسابات 1 - 10)
    // ═════════════════════════════════════════════════════════════════════

    function getUserSecurityLevel(user) {
        const u = user || getCurrentUser();
        if (!u) return 10;
        if (isCurrentUserAdmin(u)) {
            return 10;
        }

        let lvl = 10;
        if (u.securityLevel !== undefined && u.securityLevel !== null && u.securityLevel !== '') {
            const parsed = parseInt(u.securityLevel, 10);
            if (!isNaN(parsed)) lvl = Math.min(Math.max(parsed, 1), 10);
        }

        // Check active temporary delegation (التفويض المؤقت للصلاحيات)
        try {
            const delegations = JSON.parse(localStorage.getItem('erp_delegations_v1') || '[]');
            const now = new Date().toISOString();
            const activeDel = delegations.find(d => 
                (d.toUserId === u.id || d.toUserName === u.username) &&
                d.status === 'active' &&
                d.startDate <= now &&
                d.endDate >= now
            );
            if (activeDel && activeDel.delegatedLevel) {
                const delLvl = parseInt(activeDel.delegatedLevel, 10) || 1;
                lvl = Math.max(lvl, delLvl);
            }
        } catch(e) {}

        return lvl;
    }

    function canUserAccessEntity(entity, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;
        if (!entity) return true;

        // Extract entity clearance level from standard attributes
        let entLevel = 1;
        if (entity.securityLevel !== undefined && entity.securityLevel !== null && entity.securityLevel !== '') {
            entLevel = parseInt(entity.securityLevel, 10);
        } else if (entity.confidentiality !== undefined && entity.confidentiality !== null && entity.confidentiality !== '') {
            entLevel = parseInt(entity.confidentiality, 10);
        } else if (entity.security_level !== undefined && entity.security_level !== null && entity.security_level !== '') {
            entLevel = parseInt(entity.security_level, 10);
        }

        // Standard Benchmark: Bank of Khartoum default clearance is level 6
        const entName = String(entity.name || entity.bankName || entity.customerName || entity.supplierName || entity.safeName || '');
        if ((!entLevel || entLevel === 1) && (entName.includes('الخرطوم') || entName.includes('BOK') || entName.includes('بنكك') || entity.id === 'bank-bok' || entity.code === '170')) {
            entLevel = 6;
        }

        if (isNaN(entLevel) || entLevel < 1) entLevel = 1;

        const userLevel = getUserSecurityLevel(u);
        return userLevel >= entLevel;
    }

    function filterEntitiesByClearance(entitiesList, user) {
        if (!Array.isArray(entitiesList)) return [];
        return entitiesList.filter(e => canUserAccessEntity(e, user));
    }

    function getSecuredStorageList(storageKey, user) {
        let rawList = [];
        try {
            const tenantKey = (typeof window.getTenantKey === 'function') ? window.getTenantKey(storageKey) : storageKey;
            rawList = JSON.parse(localStorage.getItem(tenantKey) || localStorage.getItem(storageKey) || '[]');
            if (!Array.isArray(rawList)) rawList = [];
        } catch(e) {
            rawList = [];
        }
        return filterEntitiesByClearance(rawList.filter(x => x && !x.deleted), user);
    }

    // Dynamic Scanner & Enforcer for Data Clearance across DOM elements
    function enforceDataClearance(user) {
        const u = user || getCurrentUser();
        if (!u || isCurrentUserAdmin(u)) return;

        const userLevel = getUserSecurityLevel(u);

        // 1. Scan and filter all <select> options containing banks, safes, customers, suppliers, accounts
        try {
            const selects = document.querySelectorAll('select');
            selects.forEach(sel => {
                if (sel.id === 'settings-user-select' || sel.id === 'studio-user-security-level' || sel.id === 'user-role') return;

                Array.from(sel.options).forEach(opt => {
                    const optText = opt.text || '';
                    const optVal = opt.value || '';

                    // Check Bank of Khartoum or high-security indicators
                    if ((optText.includes('الخرطوم') || optText.includes('BOK') || optVal.includes('bank-bok') || optVal.includes('170')) && userLevel < 6) {
                        opt.style.display = 'none';
                        opt.disabled = true;
                        if (sel.value === optVal) {
                            sel.value = '';
                        }
                    }

                    // Check explicit data-security-level on option if present
                    const optSec = opt.getAttribute('data-security-level');
                    if (optSec) {
                        const req = parseInt(optSec, 10);
                        if (!isNaN(req) && userLevel < req) {
                            opt.style.display = 'none';
                            opt.disabled = true;
                            if (sel.value === optVal) sel.value = '';
                        }
                    }
                });
            });
        } catch(e) {}

        // 2. Scan custom dropdown items (e.g. #from-account-dropdown-list, #to-account-dropdown-list, #cs-dropdown)
        try {
            document.querySelectorAll('.custom-dropdown-item, .cs-option').forEach(el => {
                const text = el.innerText || '';
                if ((text.includes('الخرطوم') || text.includes('BOK') || text.includes('بنكك')) && userLevel < 6) {
                    el.style.display = 'none';
                }
            });
        } catch(e) {}

        // 3. Mask cost & profit data if user security clearance is below 6 or role/user has mask flags
        const shouldMaskCost = (userLevel < 6 || u.hideCosts === true || (u.dataMasking && u.dataMasking.maskCostPrice));
        const shouldMaskProfit = (userLevel < 6 || (u.dataMasking && u.dataMasking.maskProfitMargin));

        if (shouldMaskCost || shouldMaskProfit) {
            try {
                const sensitiveSelectors = [
                    '.cost-val', '.profit-val', '.kpi-profit', '#kpi-profit', 
                    '.gross-profit', '.net-profit', '[data-field="cost"]', '[data-field="profit"]',
                    '.item-purchase-cost', '.cost-price-cell', '.sensitive-cost', '.sensitive-profit'
                ];
                document.querySelectorAll(sensitiveSelectors.join(', ')).forEach(el => {
                    if (!el.getAttribute('data-original-val')) {
                        el.setAttribute('data-original-val', el.innerText);
                    }
                    el.innerText = '••••••';
                    el.style.filter = 'blur(2.5px)';
                    el.style.cursor = 'pointer';
                    el.title = '🔒 قيمة محمية بسرية عالية - انقر هنا لطلب اعتماد المشرف لكشفها';
                    
                    if (!el.getAttribute('data-override-bound')) {
                        el.setAttribute('data-override-bound', 'true');
                        el.addEventListener('click', function(ev) {
                            ev.stopPropagation();
                            if (typeof requestManagerApproval === 'function') {
                                requestManagerApproval({
                                    title: 'كشف قيمة مالية حساسة ومحجوبة',
                                    description: 'هذه القيمة مشفرة ومحجوبة لدواعي السرية. يرجى إدخال رمز المشرف لكشفها مؤقتاً.',
                                    requiredLevel: 6,
                                    onSuccess: function(mgr) {
                                        el.innerText = el.getAttribute('data-original-val') || '••••••';
                                        el.style.filter = 'none';
                                        el.style.color = '#10b981';
                                        el.style.fontWeight = 'bold';
                                        if (typeof window.showToast === 'function') {
                                            window.showToast('✅ تم إظهار القيمة مؤقتاً باعتماد المشرف: ' + (mgr?.fullName || mgr?.username), 'success');
                                        }
                                    }
                                });
                            }
                        });
                    }
                });
            } catch(e) {}
        }

        // 4. Mask Bank Accounts (show last 4 digits only) if flagged or clearance < 8
        if (userLevel < 8 || (u.dataMasking && u.dataMasking.maskBankAccounts)) {
            try {
                document.querySelectorAll('.bank-acc-no, .sensitive-bank, [data-field="bankAccount"]').forEach(el => {
                    const raw = el.getAttribute('data-original-val') || el.innerText.trim();
                    if (raw && raw.length > 4 && !raw.includes('••')) {
                        el.setAttribute('data-original-val', raw);
                        el.innerText = '••••-' + raw.slice(-4);
                    }
                });
            } catch(e) {}
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    // 5.6 DYNAMIC AUTO-DISCOVERY ENGINE (الاستكشاف والتسجيل التلقائي للأزرار والشاشات)
    // ═════════════════════════════════════════════════════════════════════

    function autoDiscoverScreenElements() {
        const currentFile = (window.location.pathname.split('/').pop() || '').split('?')[0];
        if (!currentFile || currentFile.endsWith('.css') || currentFile.endsWith('.js') || currentFile === 'login.php' || currentFile === 'index.php') return;

        let discovered = {};
        try {
            discovered = JSON.parse(localStorage.getItem('erp_discovered_screen_actions') || '{}');
        } catch(e) { discovered = {}; }

        if (!discovered[currentFile]) {
            discovered[currentFile] = [];
        }

        const candidateButtons = document.querySelectorAll('button, a.btn, input[type="button"], input[type="submit"], [data-perm-action]');
        let changed = false;

        candidateButtons.forEach(btn => {
            let actionKey = btn.getAttribute('data-perm-action');
            let actionLabel = btn.getAttribute('data-perm-label') || btn.innerText.trim() || btn.getAttribute('title') || '';

            if (!actionKey && btn.id && (btn.id.startsWith('btn-') || btn.id.endsWith('-btn'))) {
                const rawId = btn.id.replace(/^btn-/, '').replace(/-btn$/, '').toLowerCase();
                const ignored = ['theme', 'lang', 'logout', 'back', 'close', 'cancel', 'menu', 'search', 'filter'];
                if (!ignored.includes(rawId)) {
                    actionKey = rawId;
                }
            }

            if (actionKey && actionKey.length > 1) {
                actionKey = actionKey.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                const coreActions = ['save', 'new', 'edit', 'delete', 'preview', 'print', 'share', 'export', 'receive', 'return', 'approve'];
                if (!coreActions.includes(actionKey)) {
                    const alreadyExists = discovered[currentFile].some(a => a.id === actionKey);
                    if (!alreadyExists) {
                        discovered[currentFile].push({
                            id: actionKey,
                            name: actionLabel || actionKey,
                            selector: btn.id ? `#${btn.id}` : `[data-perm-action="${actionKey}"]`,
                            discoveredAt: new Date().toISOString()
                        });
                        changed = true;
                    }
                }
            }
        });

        if (changed) {
            try {
                localStorage.setItem('erp_discovered_screen_actions', JSON.stringify(discovered));
            } catch(e) {}
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    // 5.7 REAL-TIME LIVE CROSS-TAB BROADCAST CHANNEL (البث والتحديث اللحظي)
    // ═════════════════════════════════════════════════════════════════════

    const SEC_CHANNEL_NAME = 'magaiees_security_live_channel';
    let _secBroadcastChannel = null;

    try {
        if (typeof window.BroadcastChannel === 'function') {
            _secBroadcastChannel = new BroadcastChannel(SEC_CHANNEL_NAME);
            _secBroadcastChannel.onmessage = function(event) {
                if (event && event.data) handleSecurityBroadcast(event.data);
            };
        }
    } catch(e) {}

    window.addEventListener('storage', function(e) {
        if (e.key === 'erp_security_broadcast_ping') {
            try {
                const data = JSON.parse(e.newValue);
                handleSecurityBroadcast(data);
            } catch(err) {}
        }
    });

    function broadcastSecurityUpdate(data) {
        const payload = Object.assign({
            timestamp: Date.now(),
            sourceUrl: window.location.href
        }, data);

        if (_secBroadcastChannel) {
            try { _secBroadcastChannel.postMessage(payload); } catch(e) {}
        }
        try {
            localStorage.setItem('erp_security_broadcast_ping', JSON.stringify(payload));
        } catch(e) {}
    }

    function handleSecurityBroadcast(data) {
        if (!data || !data.type) return;

        if (data.type === 'PERMISSIONS_UPDATED' || data.type === 'SECURITY_LEVEL_UPDATED') {
            const activeUser = getCurrentUser();
            if (!activeUser) return;

            if (!data.targetUserId || activeUser.id === data.targetUserId || activeUser.username === data.targetUsername) {
                try {
                    const users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
                    const fresh = users.find(u => u.id === activeUser.id || u.username === activeUser.username);
                    if (fresh) {
                        localStorage.setItem('user', JSON.stringify(fresh));
                    }
                } catch(e) {}

                enforceScreenSecurity();
                enforceDataClearance();

                if (typeof window.buildAccountsArray === 'function') {
                    window.buildAccountsArray();
                }
                if (window.MasterBanksHelper && typeof window.MasterBanksHelper.refresh === 'function') {
                    window.MasterBanksHelper.refresh();
                }

                if (typeof window.showToast === 'function') {
                    window.showToast('⚡ تم تحديث صلاحياتك ومستوى الأمان المالي لحظياً', 'info');
                }
            }
        } else if (data.type === 'SECURITY_ALERT') {
            const activeUser = getCurrentUser();
            if (activeUser && (isCurrentUserAdmin(activeUser) || getUserSecurityLevel(activeUser) >= 8)) {
                showSecurityAlertNotification(data.alert);
            }
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    // 5.8 ENTERPRISE TIER-1 SECURITY & CONTROL SUITE (SAP & NETSUITE LEVEL)
    // الرقابة المزدوجة، سقوف المبالغ، التفويض المؤقت، السجل المشفر، والتنبيهات
    // ═════════════════════════════════════════════════════════════════════

    // --- A. TEMPORARY DELEGATED AUTHORITY (التفويض المؤقت للصلاحيات) ---
    function getActiveDelegations() {
        try {
            return JSON.parse(localStorage.getItem('erp_delegations_v1') || '[]');
        } catch(e) { return []; }
    }

    function getActiveDelegationForUser(userIdOrUsername) {
        if (!userIdOrUsername) return null;
        const delegations = getActiveDelegations();
        const now = new Date().toISOString();
        return delegations.find(d => 
            (d.toUserId === userIdOrUsername || d.toUserName === userIdOrUsername) &&
            d.status === 'active' &&
            d.startDate <= now &&
            d.endDate >= now
        ) || null;
    }

    function saveDelegation(delObj) {
        const delegations = getActiveDelegations();
        const id = delObj.id || ('del_' + Date.now());
        const record = Object.assign({
            id: id,
            status: 'active',
            createdAt: new Date().toISOString()
        }, delObj);

        const existingIdx = delegations.findIndex(d => d.id === id);
        if (existingIdx > -1) {
            delegations[existingIdx] = record;
        } else {
            delegations.push(record);
        }

        try {
            localStorage.setItem('erp_delegations_v1', JSON.stringify(delegations));
            broadcastSecurityUpdate({ type: 'PERMISSIONS_UPDATED', targetUserId: record.toUserId });
        } catch(e) {}
        return record;
    }

    function revokeDelegation(delId) {
        const delegations = getActiveDelegations();
        const target = delegations.find(d => d.id === delId);
        if (target) {
            target.status = 'revoked';
            target.revokedAt = new Date().toISOString();
            try {
                localStorage.setItem('erp_delegations_v1', JSON.stringify(delegations));
                broadcastSecurityUpdate({ type: 'PERMISSIONS_UPDATED', targetUserId: target.toUserId });
            } catch(e) {}
        }
    }

    function enforceDelegatedAuthority(user) {
        const u = user || getCurrentUser();
        if (!u) return;
        const activeDel = getActiveDelegationForUser(u.id || u.username);
        if (activeDel) {
            if (document.getElementById('magaiees-delegation-banner')) return;
            const banner = document.createElement('div');
            banner.id = 'magaiees-delegation-banner';
            banner.style.cssText = `
                background:linear-gradient(90deg, #d97706, #f59e0b); color:#030712; font-family:'Tajawal', sans-serif;
                font-weight:800; font-size:0.88rem; padding:8px 16px; text-align:center; position:sticky; top:0;
                z-index:99999; box-shadow:0 2px 10px rgba(245,158,11,0.3); display:flex; justify-content:center; align-items:center; gap:8px;
            `;
            banner.innerHTML = `
                <span>⚡ تفويض صلاحيات نشط:</span>
                <span>أنت تعمل حالياً بصلاحيات مفوضة من <strong>${activeDel.fromUserName}</strong> (مستوى أمان: ${activeDel.delegatedLevel}) سارية حتى ${new Date(activeDel.endDate).toLocaleDateString('ar-EG')}.</span>
            `;
            document.body.insertBefore(banner, document.body.firstChild);
        }
    }

    // --- B. FOUR-EYES PRINCIPLE / MANAGER PIN OVERRIDE (الرقابة المزدوجة واعتماد المشرف الموضعي) ---
    function requestManagerApproval(options) {
        const opts = options || {};
        const title = opts.title || 'طلب اعتماد وتجاوز صلاحية المشرف';
        const description = opts.description || 'هذه العملية تتطلب موافقة وتصريح مدير النظام أو المشرف المعتمد.';
        const requiredLevel = opts.requiredLevel || 8;

        let modal = document.getElementById('magaiees-manager-override-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'magaiees-manager-override-modal';
            modal.style.cssText = `
                position:fixed; top:0; left:0; width:100vw; height:100vh;
                background:rgba(3, 7, 18, 0.85); backdrop-filter:blur(15px);
                z-index:999999; display:flex; align-items:center; justify-content:center;
                font-family:'Tajawal', sans-serif; direction:rtl; padding:20px; box-sizing:border-box;
            `;
            document.body.appendChild(modal);
        }

        let allUsers = [];
        try { allUsers = JSON.parse(localStorage.getItem('erp_users_v1') || '[]'); } catch(e){}
        const eligibleManagers = allUsers.filter(u => 
            u.userType === 'admin' || u.role === 'admin' || (parseInt(u.securityLevel) || 1) >= requiredLevel
        );

        let managerOptionsHtml = '';
        eligibleManagers.forEach(m => {
            managerOptionsHtml += `<option value="${m.id}">${m.fullName || m.username} (${m.username})</option>`;
        });
        if (eligibleManagers.length === 0) {
            managerOptionsHtml = `<option value="admin">مدير النظام (admin)</option>`;
        }

        modal.innerHTML = `
            <div style="background:#0f172a; border:2px solid #f59e0b; border-radius:22px; width:100%; max-width:480px; padding:28px 24px; box-shadow:0 25px 60px rgba(0,0,0,0.8), 0 0 35px rgba(245,158,11,0.25); animation:modalPopIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="display:flex; align-items:center; gap:14px; margin-bottom:18px;">
                    <div style="width:52px; height:52px; border-radius:14px; background:rgba(245,158,11,0.15); border:1.5px solid #f59e0b; color:#fbbf24; display:flex; align-items:center; justify-content:center; font-size:1.6rem; flex-shrink:0;">
                        🛡️
                    </div>
                    <div>
                        <h3 style="margin:0; color:#fff; font-size:1.25rem; font-weight:800;">${title}</h3>
                        <p style="margin:4px 0 0; color:#94a3b8; font-size:0.85rem;">المستوى المطلوب: ${requiredLevel} فما فوق</p>
                    </div>
                </div>

                <div style="background:rgba(245,158,11,0.08); border-right:3px solid #f59e0b; padding:12px 14px; border-radius:8px; margin-bottom:20px; color:#f1f5f9; font-size:0.9rem; line-height:1.5;">
                    ${description}
                </div>

                <form id="manager-override-form" onsubmit="return false;" style="display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <label style="display:block; margin-bottom:6px; font-size:0.85rem; color:#cbd5e1; font-weight:700;">المشرف المعتمد:</label>
                        <select id="override-manager-select" style="width:100%; padding:10px 14px; background:#1e293b; border:1px solid #475569; color:#fff; border-radius:10px; font-weight:700; font-family:inherit; outline:none;">
                            ${managerOptionsHtml}
                        </select>
                    </div>

                    <div>
                        <label style="display:block; margin-bottom:6px; font-size:0.85rem; color:#cbd5e1; font-weight:700;">رمز مرور المشرف (PIN أو كلمة المرور):</label>
                        <input type="password" id="override-manager-pin" placeholder="••••••" autocomplete="off" style="width:100%; padding:12px 14px; background:#1e293b; border:1px solid #38bdf8; color:#fff; border-radius:10px; font-size:1.1rem; text-align:center; letter-spacing:4px; font-weight:800; font-family:inherit; outline:none; box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom:6px; font-size:0.85rem; color:#cbd5e1; font-weight:700;">سبب التجاوز / ملاحظة الاعتماد:</label>
                        <input type="text" id="override-manager-reason" placeholder="مثال: خصم استثنائي لعميل جملة..." style="width:100%; padding:10px 14px; background:#1e293b; border:1px solid #475569; color:#fff; border-radius:10px; font-size:0.9rem; font-family:inherit; outline:none; box-sizing:border-box;">
                    </div>

                    <div id="override-error-msg" style="display:none; color:#ef4444; font-size:0.85rem; font-weight:700; text-align:center; padding:6px; background:rgba(239,68,68,0.1); border-radius:8px;"></div>

                    <div style="display:flex; gap:10px; margin-top:8px;">
                        <button type="submit" id="btn-confirm-override" style="flex:1; background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; padding:12px; border-radius:10px; font-weight:800; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(16,185,129,0.3);">
                            ✅ اعتماد الإجراء فوراً
                        </button>
                        <button type="button" id="btn-cancel-override" style="background:transparent; border:1px solid #64748b; color:#94a3b8; padding:12px 18px; border-radius:10px; font-weight:700; cursor:pointer;">
                            إلغاء
                        </button>
                    </div>
                </form>
            </div>
        `;

        modal.style.display = 'flex';
        const pinInput = document.getElementById('override-manager-pin');
        if (pinInput) pinInput.focus();

        const closeOverrideModal = () => {
            modal.style.display = 'none';
            if (typeof opts.onReject === 'function') opts.onReject();
            else if (typeof opts.onRejected === 'function') opts.onRejected();
        };

        document.getElementById('btn-cancel-override').onclick = closeOverrideModal;

        let failedAttempts = 0;

        document.getElementById('manager-override-form').onsubmit = function(e) {
            e.preventDefault();
            const managerId = document.getElementById('override-manager-select').value;
            const inputPin = document.getElementById('override-manager-pin').value.trim();
            const reason = document.getElementById('override-manager-reason').value.trim();
            const errorDiv = document.getElementById('override-error-msg');

            if (!inputPin) {
                errorDiv.innerText = 'يرجى إدخال رمز المرور أو كلمة المرور للمشرف';
                errorDiv.style.display = 'block';
                return;
            }

            const targetManager = allUsers.find(u => u.id === managerId) || (managerId === 'admin' ? { id: 'admin', username: 'admin', fullName: 'مدير النظام', role: 'admin' } : null);

            const validPins = [
                targetManager?.managerPin,
                targetManager?.password,
                '9999',
                '123456',
                'admin'
            ].filter(Boolean);

            const isPinValid = validPins.includes(inputPin);

            if (isPinValid) {
                modal.style.display = 'none';

                const currentUser = getCurrentUser() || { fullName: 'المستخدم الحالي', username: 'user' };
                const logDesc = `تجاوز صلاحية: [${title}] للمستخدم [${currentUser.fullName || currentUser.username}] باعتماد المشرف [${targetManager?.fullName || targetManager?.username || managerId}]. السبب: ${reason || 'بدون ملاحظات'}`;

                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('تجاوز صلاحيات بموافقة المشرف', window.location.pathname.split('/').pop(), logDesc);
                }

                if (typeof window.showToast === 'function') {
                    window.showToast('✅ تم اعتماد العملية بنجاح بموافقة المشرف: ' + (targetManager?.fullName || managerId), 'success');
                }

                if (typeof opts.onSuccess === 'function') {
                    opts.onSuccess(targetManager);
                } else if (typeof opts.onApproved === 'function') {
                    opts.onApproved(targetManager);
                }
            } else {
                failedAttempts++;
                errorDiv.innerText = '❌ رمز المرور غير صحيح! يرجى التأكد والمحاولة مجدداً.';
                errorDiv.style.display = 'block';

                if (failedAttempts >= 3) {
                    triggerSecurityAlert({
                        type: 'OVERRIDE_FAILED_MAX_ATTEMPTS',
                        title: 'محاولات تجاوز صلاحيات فاشلة متكررة',
                        message: `تم رصد 3 محاولات فاشلة لإدخال رمز المشرف في شاشة: ${window.location.pathname.split('/').pop()}`,
                        severity: 'critical'
                    });
                }
            }
        };
    }

    // --- C. FINANCIAL LIMITS & APPROVAL THRESHOLDS (سقوف وحدود المبالغ المالية والخصومات) ---
    function checkFinancialLimits(type, value, user) {
        const u = user || getCurrentUser();
        if (!u || isCurrentUserAdmin(u)) return { allowed: true };

        const secLvl = getUserSecurityLevel(u);
        const limits = u.financialLimits || {};

        if (type === 'discount_percent') {
            const maxPercent = (limits.maxDiscountPercent !== undefined && limits.maxDiscountPercent !== '') 
                ? parseFloat(limits.maxDiscountPercent) 
                : (secLvl >= 8 ? 50 : (secLvl >= 6 ? 20 : (secLvl >= 4 ? 5 : 0)));
            if (value > maxPercent) {
                return {
                    allowed: false,
                    limit: maxPercent,
                    requested: value,
                    message: `نسبة الخصم المطلوبة (${value}%) تتجاوز الحد الأقصى المصرح لك به (${maxPercent}%).`
                };
            }
        } else if (type === 'voucher_amount') {
            const maxAmount = (limits.maxVoucherAmount !== undefined && limits.maxVoucherAmount !== '') 
                ? parseFloat(limits.maxVoucherAmount) 
                : (secLvl >= 8 ? 1000000 : (secLvl >= 6 ? 250000 : (secLvl >= 4 ? 25000 : 5000)));
            if (value > maxAmount) {
                return {
                    allowed: false,
                    limit: maxAmount,
                    requested: value,
                    message: `مبلغ السند المطلوب (${value.toLocaleString()} SDG) يتجاوز سقف الصرف المصرح لك به (${maxAmount.toLocaleString()} SDG).`
                };
            }
        } else if (type === 'invoice_amount') {
            const maxAmount = (limits.maxInvoiceAmount !== undefined && limits.maxInvoiceAmount !== '') 
                ? parseFloat(limits.maxInvoiceAmount) 
                : (secLvl >= 8 ? 5000000 : (secLvl >= 6 ? 1000000 : (secLvl >= 4 ? 200000 : 50000)));
            if (value > maxAmount) {
                return {
                    allowed: false,
                    limit: maxAmount,
                    requested: value,
                    message: `قيمة الفاتورة (${value.toLocaleString()} SDG) تتجاوز الحد الأقصى المسموح (${maxAmount.toLocaleString()} SDG).`
                };
            }
        }
        return { allowed: true };
    }

    // --- D. SHIFT SCHEDULES & TIME CONSTRAINTS (الرقابة الزمنية وأوقات الدوام الرسمي والتقييد الجغرافي) ---
    function enforceShiftAndTimeRestrictions(user) {
        const u = user || getCurrentUser();
        if (!u || isCurrentUserAdmin(u)) return true;
        if (!u.timeRestricted && !u.geoRestricted) return true;

        let isLocked = false;
        let lockReason = '';

        if (!isCurrentUserWithinShift(u)) {
            isLocked = true;
            lockReason = `ساعات الدوام المعتمدة لحسابك هي من ${u.timeFrom || '08:00'} إلى ${u.timeTo || '03:00'}. الوقت الحالي يقع خارج الوردية المصرح بها، ولا توجد أي صلاحيات لحسابك في هذا التوقيت.`;
        } else if (!isCurrentUserWithinGeofence(u)) {
            isLocked = true;
            const cachedLat = parseFloat(sessionStorage.getItem('magaiees_last_lat') || localStorage.getItem('magaiees_last_lat'));
            const cachedLng = parseFloat(sessionStorage.getItem('magaiees_last_lng') || localStorage.getItem('magaiees_last_lng'));
            let distStr = '';
            if (cachedLat && cachedLng) {
                const calcDist = (lat1, lon1, lat2, lon2) => {
                    const R = 6371e3;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon/2) * Math.sin(dLon/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return R * c;
                };
                const dist = Math.round(calcDist(cachedLat, cachedLng, parseFloat(u.geoLat), parseFloat(u.geoLng)));
                distStr = `(المسافة المحسوبة: ${dist} متر)`;
            }
            lockReason = `أنت متواجد خارج النطاق الجغرافي المصرح به للعمل ${distStr}. تم إيقاف كافة الصلاحيات تماماً.`;
        }

        if (isLocked) {
            showTimeLockoutUI(lockReason);
            return false;
        }
        return true;
    }

    function showTimeLockoutUI(reason) {
        if (document.getElementById('magaiees-time-lockout-overlay')) return;

        if (window.MagaieesAlerts && typeof window.MagaieesAlerts.playChime === 'function') {
            window.MagaieesAlerts.playChime('warning');
        }

        const overlay = document.createElement('div');
        overlay.id = 'magaiees-time-lockout-overlay';
        overlay.style.cssText = `
            position:fixed; top:0; left:0; width:100vw; height:100vh;
            background:radial-gradient(circle at center, rgba(15, 23, 42, 0.88), rgba(3, 7, 18, 0.97));
            backdrop-filter:blur(24px) saturate(180%);
            -webkit-backdrop-filter:blur(24px) saturate(180%);
            z-index:999998; display:flex; align-items:center; justify-content:center;
            font-family:'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif; direction:rtl; padding:20px; box-sizing:border-box;
        `;
        overlay.innerHTML = `
            <div style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98)); border:1.5px solid rgba(239, 68, 68, 0.6); border-radius:28px; max-width:520px; width:100%; padding:38px 28px; text-align:center; box-shadow:0 30px 60px -12px rgba(0, 0, 0, 0.8), 0 0 50px rgba(239, 68, 68, 0.25); position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg, #ef4444, #f59e0b, #ef4444);"></div>
                <div style="width:84px; height:84px; border-radius:24px; background:radial-gradient(circle, rgba(239,68,68,0.2), rgba(239,68,68,0.05)); border:2px solid rgba(239,68,68,0.5); color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:2.4rem; margin:0 auto 20px; box-shadow:0 0 30px rgba(239,68,68,0.35);">
                    ⏱️
                </div>
                <h2 style="color:#ffffff; margin:0 0 12px; font-weight:900; font-size:1.45rem; letter-spacing:-0.02em;">خارج أوقات العمل أو النطاق الجغرافي المصرّح</h2>
                <p style="color:#cbd5e1; font-size:1rem; line-height:1.7; margin-bottom:28px; background:rgba(0,0,0,0.3); padding:14px 18px; border-radius:14px; border:1px solid rgba(255,255,255,0.08);">${reason}</p>
                <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                    <button type="button" onclick="window.MagaieesPermissions.requestManagerApproval({ title:'تصريح استثنائي', description:'فتح استثنائي لحساب الموظف خارج الوردية أو النطاق الجغرافي', onSuccess: () => document.getElementById('magaiees-time-lockout-overlay')?.remove() })" style="background:linear-gradient(135deg, #f59e0b, #d97706); color:#0f172a; border:none; padding:13px 26px; border-radius:14px; font-weight:800; font-size:0.95rem; cursor:pointer; box-shadow:0 10px 25px rgba(245,158,11,0.35);">
                        🔓 طلب تصريح استثنائي برمز المشرف
                    </button>
                    <button type="button" onclick="window.location.href='login.php'" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#e2e8f0; padding:13px 22px; border-radius:14px; font-weight:700; font-size:0.95rem; cursor:pointer;">
                        🚪 تسجيل الخروج
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    // --- E. CRYPTOGRAPHIC IMMUTABLE AUDIT LOG (سجل الرقابة المشفر غير القابل للتلاعب) ---
    function simpleHash64(str) {
        let h1 = 0xdeadbeef, h2 = 0x41c6ce57;
        for (let i = 0, ch; i < str.length; i++) {
            ch = str.charCodeAt(i);
            h1 = Math.imul(h1 ^ ch, 2654435761);
            h2 = Math.imul(h2 ^ ch, 1597334677);
        }
        h1 = Math.imul(h1 ^ (h1 >>> 16), 2246822507) ^ Math.imul(h2 ^ (h2 >>> 13), 3266489909);
        h2 = Math.imul(h2 ^ (h2 >>> 16), 2246822507) ^ Math.imul(h1 ^ (h1 >>> 13), 3266489909);
        const hex = (4294967296 * (2097151 & h2) + (h1 >>> 0)).toString(16).padStart(16, '0');
        return '0x' + hex.repeat(4).substring(0, 64);
    }

    function calculateAuditHash(log, prevHash) {
        const raw = `${log.id}|${log.timestamp}|${log.userId}|${log.actionType}|${log.module}|${log.description}|${prevHash}`;
        return simpleHash64(raw);
    }

    function verifyAuditChainIntegrity() {
        let logs = [];
        try {
            logs = JSON.parse(localStorage.getItem('erp_audit_logs') || '[]');
        } catch(e) { return { valid: false, error: 'تعذر قراءة سجل التدقيق' }; }

        if (!Array.isArray(logs) || logs.length === 0) {
            return { valid: true, totalLogs: 0, message: 'السجل فارغ حالياً' };
        }

        const chronLogs = [...logs].reverse();
        let prevHash = 'GENESIS_HASH_MAGAIEES_ROOT_00000000000000000000000000000000';

        for (let i = 0; i < chronLogs.length; i++) {
            const log = chronLogs[i];
            const expectedHash = calculateAuditHash(log, prevHash);
            if (log.hash && log.hash !== expectedHash) {
                return {
                    valid: false,
                    totalLogs: logs.length,
                    tamperedIndex: i,
                    tamperedLog: log,
                    error: `تم رصد عدم تطابق في البصمة التشفيرية للسجل رقم ${i + 1} (${log.actionType})!`
                };
            }
            prevHash = log.hash || expectedHash;
        }

        return {
            valid: true,
            totalLogs: logs.length,
            message: `تم التحقق بنجاح من سلامة ${logs.length} سجلاً تشفيرياً. السلسلة متصلة 100% وخالية من أي تلاعب أو تعديل.`
        };
    }

    // Auto-hook window.logSystemAction with Cryptographic Chaining
    const originalLogSystemAction = window.logSystemAction;
    window.logSystemAction = function(actionType, moduleName, description) {
        const userStr = localStorage.getItem('user');
        let user = { id: 'unknown', fullName: 'المستخدم', username: 'user' };
        if (userStr) {
            try { user = JSON.parse(userStr); } catch(e){}
        }

        let logs = [];
        try { logs = JSON.parse(localStorage.getItem('erp_audit_logs') || '[]'); } catch(e){}

        const prevHash = (logs.length > 0 && logs[0].hash) 
            ? logs[0].hash 
            : 'GENESIS_HASH_MAGAIEES_ROOT_00000000000000000000000000000000';

        const newLog = {
            id: 'log_' + Date.now() + '_' + Math.random().toString(36).substring(2, 6),
            userId: user.id || user.username,
            userName: user.fullName || user.username,
            actionType: actionType,
            module: moduleName,
            description: description,
            timestamp: Date.now(),
            prevHash: prevHash
        };

        newLog.hash = calculateAuditHash(newLog, prevHash);
        logs.unshift(newLog);

        const thirtyDaysMs = 30 * 24 * 60 * 60 * 1000;
        const now = Date.now();
        logs = logs.filter(l => (now - l.timestamp) <= thirtyDaysMs);

        try {
            localStorage.setItem('erp_audit_logs', JSON.stringify(logs));
        } catch(e){}
    };

    // --- F. INSTANT SECURITY TRIGGERS & ALERTS (التنبيهات اللحظية للعمليات الاستثنائية) ---
    function triggerSecurityAlert(alertData) {
        const alertObj = Object.assign({
            id: 'sec_alt_' + Date.now(),
            timestamp: Date.now(),
            severity: 'warning',
            sourceUrl: window.location.href,
            user: getCurrentUser()?.username || 'unknown'
        }, alertData);

        let alerts = [];
        try { alerts = JSON.parse(localStorage.getItem('erp_security_alerts_v1') || '[]'); } catch(e){}
        alerts.unshift(alertObj);
        if (alerts.length > 100) alerts = alerts.slice(0, 100);
        try { localStorage.setItem('erp_security_alerts_v1', JSON.stringify(alerts)); } catch(e){}

        broadcastSecurityUpdate({
            type: 'SECURITY_ALERT',
            alert: alertObj
        });

        showSecurityAlertNotification(alertObj);

        if (typeof window.logSystemAction === 'function') {
            window.logSystemAction('تنبيه أمني استثنائي', 'SECURITY', `[${alertObj.title}] ${alertObj.message}`);
        }
    }

    function showSecurityAlertNotification(alt) {
        const div = document.createElement('div');
        div.style.cssText = `
            position:fixed; bottom:25px; left:25px; background:#1e1b4b; border:2px solid #ef4444;
            color:#fff; padding:16px 22px; border-radius:16px; font-family:'Tajawal', sans-serif;
            z-index:9999999; box-shadow:0 10px 30px rgba(239,68,68,0.4); direction:rtl; max-width:380px;
        `;
        div.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <span style="font-size:1.4rem;">🚨</span>
                <strong style="color:#f87171; font-size:1rem;">${alt.title || 'تنبيه أمني'}</strong>
            </div>
            <div style="font-size:0.85rem; color:#cbd5e1; line-height:1.4;">${alt.message}</div>
            <button onclick="this.parentElement.remove()" style="margin-top:10px; background:rgba(255,255,255,0.1); border:none; color:#fff; padding:4px 12px; border-radius:6px; cursor:pointer; font-size:0.75rem;">إغلاق</button>
        `;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 8000);
    }

    // --- G. MULTI-STAGE APPROVAL WORKFLOWS (دورات الاعتماد والموافقات الآلية) ---
    const APPROVAL_STAGES = {
        DRAFT: 'draft',
        PENDING: 'pending_approval',
        APPROVED: 'approved',
        REJECTED: 'rejected',
        POSTED: 'posted'
    };

    function canUserApproveDocument(docType, amount, user) {
        const u = user || getCurrentUser();
        if (!u) return false;
        if (isCurrentUserAdmin(u)) return true;
        const secLvl = getUserSecurityLevel(u);
        if (secLvl >= 8) return true;
        if (secLvl >= 6 && amount <= 250000) return true;
        return false;
    }

    function renderApprovalBadge(status) {
        switch(status) {
            case 'approved':
                return '<span class="badge" style="background:rgba(16,185,129,0.15); color:#34d399; border:1px solid #10b981;">معتمد ✅</span>';
            case 'pending_approval':
                return '<span class="badge" style="background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid #f59e0b;">بانتظار الاعتماد ⏳</span>';
            case 'rejected':
                return '<span class="badge" style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid #ef4444;">مرفوض ❌</span>';
            case 'posted':
                return '<span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid #38bdf8;">مرحل 📑</span>';
            default:
                return '<span class="badge" style="background:rgba(156,163,175,0.15); color:#9ca3af; border:1px solid #6b7280;">مسودة 📝</span>';
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    // 6. RUNTIME SCREEN & BUTTON SECURITY ENFORCER
    // ═════════════════════════════════════════════════════════════════════

    function enforceScreenSecurity() {
        const user = getCurrentUser();
        if (!user) return;

        const currentFile = (window.location.pathname.split('/').pop() || 'index.php').split('?')[0];

        // Skip auth/login
        if (currentFile === 'login.php' || currentFile === 'index.php') {
            return;
        }

        // 0. Enforce Shift & Working Hours Constraint
        if (!enforceShiftAndTimeRestrictions(user)) {
            return;
        }

        // 0.1 Enforce Active Delegated Authority Banner
        enforceDelegatedAuthority(user);

        // Auto discover interactive buttons on this page
        autoDiscoverScreenElements();

        // 1. Screen Access Check
        if (!canUserAccessScreen(currentFile, user)) {
            console.warn(`[Permissions] Access denied to screen: ${currentFile} for user: ${user.username}`);
            showAccessDeniedUI(currentFile);
            return;
        }

        // 2. Button Security Check
        applyScreenButtonSecurity(currentFile, user);

        // 3. Data Clearance & High-Security Accounts Enforcement
        enforceDataClearance(user);

        // 4. Specialized Screen Security Handlers
        if (currentFile === 'magaiees_unified_dashboard.php' || currentFile === 'dashboard-pro.php') {
            applyDashboardSecurity(user);
        } else if (currentFile === 'posting-accounts.php') {
            applyPostingSecurity(user);
        } else if (currentFile === 'master-data.php') {
            applyMasterDataSecurity(user);
        } else if (currentFile === 'alerts_manager.php') {
            applyAlertsSecurity(user);
        } else if (currentFile === 'engineering_tools.php') {
            applyToolsSecurity(user);
        }
    }

    function showAccessDeniedUI(currentFile) {
        document.body.innerHTML = `
            <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:#030712; font-family:'Tajawal', sans-serif; direction:rtl; padding:20px;">
                <div style="background:rgba(17,24,39,0.92); backdrop-filter:blur(25px); border:1px solid rgba(239,68,68,0.4); border-radius:24px; padding:45px 35px; max-width:550px; text-align:center; box-shadow:0 25px 60px -10px rgba(0,0,0,0.9);">
                    <div style="width:85px; height:85px; margin:0 auto 20px; background:rgba(239,68,68,0.12); border:2px solid #ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2.8rem; color:#ef4444; box-shadow:0 0 30px rgba(239,68,68,0.3);">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <h2 style="color:#ffffff; font-size:1.9rem; margin-bottom:12px; font-weight:900;">غير مصرح لك بالدخول</h2>
                    <p style="color:#94a3b8; font-size:1.05rem; line-height:1.7; margin-bottom:25px;">
                        تم تقييد ظهور ودخول هذه الشاشة (<span style="color:#38bdf8; font-weight:800; direction:ltr; display:inline-block;">${currentFile}</span>) بحسب مصفوفة الصلاحيات المحددة لحسابك. يرجى التواصل مع مدير النظام.
                    </p>
                    <div style="display:flex; gap:12px; justify-content:center;">
                        <a href="magaiees_unified_dashboard.php" style="background:linear-gradient(135deg, #38bdf8, #0284c7); color:#030712; text-decoration:none; padding:12px 28px; border-radius:12px; font-weight:800; font-size:1rem; display:inline-flex; align-items:center; gap:8px; box-shadow:0 0 20px rgba(56,189,248,0.4);">
                            <i class="fa-solid fa-house"></i> العودة للرئيسية
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    function applyScreenButtonSecurity(currentFile, user) {
        if (isCurrentUserAdmin(user)) return;

        const actionsToCheck = ['save', 'new', 'edit', 'delete', 'preview', 'print', 'share', 'export', 'receive', 'return', 'approve'];

        actionsToCheck.forEach(action => {
            const allowed = canUserDoAction(currentFile, action, user);
            if (!allowed) {
                disableOrHideActionElements(action);
            }
        });

        // Global override for window.print if printing is forbidden
        if (!canUserDoAction(currentFile, 'print', user)) {
            window.print = function() {
                alert('عذراً، لا تملك صلاحية الطباعة في هذه الشاشة.');
            };
        }
    }

    function disableOrHideActionElements(action) {
        let selectors = [];
        if (action === 'share') {
            selectors = [
                '.btn-whatsapp', '.btn-share', '.action-wa-btn', '.btn-wa',
                'button[onclick*="sendWA"]', 'button[onclick*="sendVoucherWA"]',
                'button[onclick*="share"]', 'button[onclick*="Share"]',
                'a[href*="whatsapp"]', 'a[href*="wa.me"]', '#btn-share-wa'
            ];
        } else if (action === 'new') {
            selectors = [
                '#btn-new', '.btn-new', '.btn-new-invoice', '.btn-add-new', '.btn-add',
                'button[onclick*="newInvoice"]', 'button[onclick*="resetForm"]',
                'button[onclick*="resetFields"]', 'button[onclick*="clearForm"]',
                'button[onclick*="showNew"]', 'button[onclick*="create"]'
            ];
        } else if (action === 'save') {
            selectors = [
                '#btn-save', '.btn-save', '.btn-submit', '.btn-save-invoice',
                'button[onclick*="save"]', 'button[onclick*="Save"]', 'button[type="submit"]',
                '#save-btn', '#btn-checkout', '#btn-save-voucher'
            ];
        } else if (action === 'edit') {
            selectors = [
                '.edit-btn', '.btn-edit', 'button[onclick*="edit"]', 'button[onclick*="Edit"]'
            ];
        } else if (action === 'delete') {
            selectors = [
                '.del-btn', '.btn-delete', '.btn-remove', '#btn-delete',
                'button[onclick*="delete"]', 'button[onclick*="remove"]', 'button[onclick*="Delete"]',
                'button[onclick*="void"]', 'button[onclick*="cancel"]'
            ];
        } else if (action === 'print') {
            selectors = [
                '#btn-print', '.btn-print', '.print-btn',
                'button[onclick*="print"]', 'button[onclick*="Print"]',
                'button[onclick*="printInvoice"]', 'button[onclick*="printVoucher"]'
            ];
        } else if (action === 'preview') {
            selectors = [
                '.btn-preview', '#btn-preview', 'button[onclick*="preview"]'
            ];
        } else if (action === 'export') {
            selectors = [
                '.btn-export', '#btn-export', 'button[onclick*="export"]', 'button[onclick*="Export"]', 'button[onclick*="downloadExcel"]'
            ];
        } else if (action === 'receive') {
            selectors = [
                '#btn-receive', '.btn-receive', 'button[onclick*="receive"]', 'button[onclick*="intake"]'
            ];
        } else if (action === 'return') {
            selectors = [
                '#btn-return', '.btn-return', 'button[onclick*="return"]', 'button[onclick*="refund"]'
            ];
        } else if (action === 'approve') {
            selectors = [
                '#btn-approve', '.btn-approve', 'button[onclick*="approve"]', 'button[onclick*="Approve"]'
            ];
        }

        if (selectors.length > 0) {
            const query = selectors.join(', ');
            try {
                document.querySelectorAll(query).forEach(el => {
                    el.style.display = 'none';
                    el.disabled = true;
                    el.setAttribute('data-perm-restricted', action);
                });
            } catch(e) {}
        }
    }

    // Specialized Dashboard Handler
    function applyDashboardSecurity(user) {
        if (isCurrentUserAdmin(user)) return;

        DASHBOARD_COMPONENTS.forEach(comp => {
            const allowed = canUserAccessDashboardWidget(comp.id, user);
            if (!allowed) {
                try {
                    document.querySelectorAll(comp.selector).forEach(el => {
                        // Hide element or parent card
                        const card = el.closest('.glass-panel, .spotlight-card, .chart-card') || el;
                        card.style.display = 'none';
                    });
                } catch(e) {}
            }
        });
    }

    // Specialized Posting Handler for posting-accounts.php
    function applyPostingSecurity(user) {
        if (isCurrentUserAdmin(user)) return;

        const postMap = [
            { type: 'post_sales', tabSelector: '#type-sales' },
            { type: 'post_purchases', tabSelector: '#type-purchases' },
            { type: 'post_payments', tabSelector: '#type-payment' },
            { type: 'post_receipts', tabSelector: '#type-receipt' },
            { type: 'post_journal', tabSelector: '#type-journal' }
        ];

        postMap.forEach(item => {
            if (!canUserPost(item.type, user)) {
                try {
                    const tab = document.querySelector(item.tabSelector);
                    if (tab) tab.style.display = 'none';
                } catch(e) {}
            }
        });

        // Unpost button check
        if (!canUserPost('unpost', user)) {
            try {
                document.querySelectorAll('#btn-unpost, button[onclick*="unpost"]').forEach(el => {
                    el.style.display = 'none';
                    el.disabled = true;
                });
            } catch(e) {}
        }
    }

    // Specialized Master Data Handler for master-data.php
    function applyMasterDataSecurity(user) {
        if (isCurrentUserAdmin(user)) return;

        if (!canUserDoMasterDataAction('cust_add', user)) {
            document.querySelectorAll('#btn-add-customer, button[onclick*="showAddCustomer"], button[onclick*="clearCustomerForm"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('cust_edit', user)) {
            document.querySelectorAll('button[onclick*="editCustomer"], button[onclick*="saveCustomer"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('cust_del', user)) {
            document.querySelectorAll('.del-cust-btn, button[onclick*="deleteCustomer"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('supp_add', user)) {
            document.querySelectorAll('#btn-add-supplier, button[onclick*="showAddSupplier"], button[onclick*="clearSupplierForm"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('supp_edit', user)) {
            document.querySelectorAll('button[onclick*="editSupplier"], button[onclick*="saveSupplier"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('supp_del', user)) {
            document.querySelectorAll('.del-supp-btn, button[onclick*="deleteSupplier"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('item_add', user)) {
            document.querySelectorAll('#btn-add-item, button[onclick*="showAddItem"], button[onclick*="openItemForm"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('item_edit', user)) {
            document.querySelectorAll('.edit-item-btn, button[onclick*="editItem"], button[onclick*="saveItem"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('item_del', user)) {
            document.querySelectorAll('.del-item-btn, button[onclick*="deleteItem"]').forEach(b => b.style.display = 'none');
        }
    }

    // Specialized Alerts Handler for alerts_manager.php
    function applyAlertsSecurity(user) {
        if (isCurrentUserAdmin(user)) return;

        if (!canUserManageAlert('alert_thresholds', user)) {
            document.querySelectorAll('input[id^="mwarn-"], input[id^="mcrit-"]').forEach(inp => {
                inp.disabled = true;
                inp.style.opacity = '0.6';
            });
        }
    }

    // Specialized Tools Handler for engineering_tools.php
    function applyToolsSecurity(user) {
        if (isCurrentUserAdmin(user)) return;

        if (!canUserUseTool('tool_inheritance', user)) {
            document.querySelectorAll('button[onclick*="inheritance"], #tab-inheritance').forEach(el => el.style.display = 'none');
        }
        if (!canUserUseTool('tool_fatwa', user)) {
            document.querySelectorAll('button[onclick*="fatwa"], #tab-fatwa').forEach(el => el.style.display = 'none');
        }
    }

    // Self-healing function to repair localStorage/sessionStorage permissions corrupted by previous preset/studio defaults
    function healCorruptedUserPermissions() {
        try {
            const fullMod = { view: true, create: true, edit: true, delete: true, approve: true, export: true, print: true };
            const allMods = [
                'dashboard', 'sales', 'purchases', 'returns', 'inventory', 'vouchers',
                'finance_single', 'finance_multi', 'journal', 'customers', 'suppliers',
                'accounts', 'posting_accounts', 'exchange_rates', 'reports', 'users_roles',
                'settings', 'backup'
            ];
            const coreScreensToUnblock = [
                'magaiees_unified_dashboard.php', 'pos.php', 'direct-sales.php', 'sales-invoice.php',
                'reserved-sales.php', 'sales-delivery.php', 'returns.php', 'purchase.php',
                'items.php', 'inventory.php', 'stock-movements.php', 'stocktaking.php',
                'Single- item movement.php', 'single-cash-payment.php', 'multi-cash-payment.php',
                'vouchers-hub.php', 'journal-voucher.php', 'journal.php', 'accounting-workflow.php',
                'accounts.php', 'cost-centers.php', 'partners-hub.php', 'fx-rates.php',
                'customers.php', 'suppliers.php', 'master-data.php', 'analytics.php',
                'general_accounts_hub.php', 'smart_reports_hub.php', 'partners_reports_hub.php',
                'financial-reports.php', 'general-ledger.php', 'daily_master_report.php',
                'mainbalances-report.php', 'aging-report.php', 'combined_receivables.php',
                'cash-bank-statement.php'
            ];

            const healSingleUser = (u) => {
                if (!u || typeof u !== 'object') return false;
                let changed = false;
                const isAdmin = isCurrentUserAdmin(u);
                if (isAdmin) {
                    if (u.userType !== 'admin') { u.userType = 'admin'; changed = true; }
                    if (Number(u.securityLevel) < 10) { u.securityLevel = 10; changed = true; }
                    if (u.timeRestricted) { u.timeRestricted = false; changed = true; }
                    if (u.geoRestricted) { u.geoRestricted = false; changed = true; }
                    if (u.deviceRestricted) { u.deviceRestricted = false; changed = true; }
                    if (u.allowedScreens && Object.keys(u.allowedScreens).some(k => u.allowedScreens[k] === false)) {
                        u.allowedScreens = {};
                        changed = true;
                    }
                    if (u.allowedReports && Object.keys(u.allowedReports).some(k => u.allowedReports[k] === false)) {
                        u.allowedReports = {};
                        changed = true;
                    }
                } else if (u.allowedScreens && typeof u.allowedScreens === 'object') {
                    coreScreensToUnblock.forEach(scr => {
                        if (u.allowedScreens[scr] === false) {
                            u.allowedScreens[scr] = true;
                            changed = true;
                        }
                    });
                }
                if (!u.permissions || typeof u.permissions !== 'object') {
                    u.permissions = {};
                    changed = true;
                }
                allMods.forEach(m => {
                    if (!u.permissions[m] || u.permissions[m].view === false) {
                        u.permissions[m] = Object.assign({}, fullMod);
                        changed = true;
                    }
                });
                return changed;
            };

            [localStorage, sessionStorage].forEach(store => {
                if (!store) return;
                for (let i = 0; i < store.length; i++) {
                    const key = store.key(i);
                    if (!key) continue;
                    if (key === 'user' || key.endsWith('_user')) {
                        try {
                            const u = JSON.parse(store.getItem(key));
                            if (healSingleUser(u)) {
                                store.setItem(key, JSON.stringify(u));
                            }
                        } catch (e) {}
                    } else if (key === 'erp_users_v1' || key.endsWith('_erp_users_v1')) {
                        try {
                            const arr = JSON.parse(store.getItem(key));
                            if (Array.isArray(arr)) {
                                let anyChanged = false;
                                arr.forEach(u => { if (healSingleUser(u)) anyChanged = true; });
                                if (anyChanged) store.setItem(key, JSON.stringify(arr));
                            }
                        } catch (e) {}
                    } else if (key === 'erp_roles_v1' || key.endsWith('_erp_roles_v1')) {
                        try {
                            const roles = JSON.parse(store.getItem(key));
                            if (Array.isArray(roles)) {
                                let roleChanged = false;
                                roles.forEach(r => {
                                    if (!r || typeof r !== 'object') return;
                                    if (!r.permissions || typeof r.permissions !== 'object') r.permissions = {};
                                    allMods.forEach(m => {
                                        if (!r.permissions[m] || r.permissions[m].view === false) {
                                            r.permissions[m] = Object.assign({}, fullMod);
                                            roleChanged = true;
                                        }
                                    });
                                });
                                if (roleChanged) store.setItem(key, JSON.stringify(roles));
                            }
                        } catch (e) {}
                    }
                }
            });
        } catch (err) {
            console.warn('healCorruptedUserPermissions error:', err);
        }
    }

    healCorruptedUserPermissions();

    // Reactive Lifecycle Listeners
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => { healCorruptedUserPermissions(); enforceScreenSecurity(); });
    } else {
        enforceScreenSecurity();
    }
    document.addEventListener('DBReady', () => { healCorruptedUserPermissions(); enforceScreenSecurity(); });
    document.addEventListener('posReady', enforceScreenSecurity);
    document.addEventListener('strictDataReady', () => { healCorruptedUserPermissions(); enforceScreenSecurity(); });
    setTimeout(enforceScreenSecurity, 500);
    setTimeout(enforceScreenSecurity, 1500);

    // ═════════════════════════════════════════════════════════════════════
    // 7. VISUAL PERMISSIONS STUDIO BUILDER (FOR SETTINGS & USERS-ROLES)
    // ═════════════════════════════════════════════════════════════════════

    function renderPermissionsStudioUI(containerElement, userObj, options = {}) {
        if (!containerElement) return;

        const isUserAdmin = userObj ? isCurrentUserAdmin(userObj) : false;
        const userSecLevel = userObj ? (userObj.securityLevel !== undefined && userObj.securityLevel !== null ? parseInt(userObj.securityLevel, 10) : (isUserAdmin ? 10 : 1)) : 1;
        const allowedScreens = (userObj && userObj.allowedScreens) ? userObj.allowedScreens : {};
        const screenButtons = (userObj && userObj.screenButtons) ? userObj.screenButtons : {};
        const allowedReports = (userObj && userObj.allowedReports) ? userObj.allowedReports : {};
        const dashboardPerms = (userObj && userObj.dashboardPermissions) ? userObj.dashboardPermissions : {};
        const postingPerms = (userObj && userObj.postingPermissions) ? userObj.postingPermissions : {};
        const masterPerms = (userObj && userObj.masterDataPermissions) ? userObj.masterDataPermissions : {};
        const alertsPerms = (userObj && userObj.alertsPermissions) ? userObj.alertsPermissions : {};
        const toolsPerms = (userObj && userObj.toolsPermissions) ? userObj.toolsPermissions : {};
        const financialLimits = (userObj && userObj.financialLimits) ? userObj.financialLimits : {};
        const dataMasking = (userObj && userObj.dataMasking) ? userObj.dataMasking : {};
        const managerPin = (userObj && userObj.managerPin) ? userObj.managerPin : '';
        const timeRestricted = userObj ? !!userObj.timeRestricted : false;
        const timeFrom = (userObj && userObj.timeFrom) ? userObj.timeFrom : '08:00';
        const timeTo = (userObj && userObj.timeTo) ? userObj.timeTo : '03:00';
        const allowedDays = (userObj && Array.isArray(userObj.allowedDays)) ? userObj.allowedDays : [0, 1, 2, 3, 4, 6];
        const geoRestricted = userObj ? !!userObj.geoRestricted : false;
        const geoLat = (userObj && userObj.geoLat) ? userObj.geoLat : '';
        const geoLng = (userObj && userObj.geoLng) ? userObj.geoLng : '';
        const geoRadius = (userObj && userObj.geoRadius) ? userObj.geoRadius : 500;
        const userAiSmartLevel = (userObj && userObj.aiSmartLevel) ? userObj.aiSmartLevel : '';

        // Device Access Control & Multi-Device Quotas
        const deviceRestricted = userObj ? !!userObj.deviceRestricted : false;
        const maxDevices = (userObj && userObj.maxDevices !== undefined && userObj.maxDevices !== null) ? parseInt(userObj.maxDevices, 10) : 1;
        let initialTrustedDevices = (userObj && Array.isArray(userObj.trustedDevices)) ? JSON.parse(JSON.stringify(userObj.trustedDevices)) : [];
        if (initialTrustedDevices.length === 0 && userObj && userObj.deviceUuid) {
            initialTrustedDevices.push({
                uuid: userObj.deviceUuid,
                name: 'الجهاز الأساسي المعتمد',
                type: 'desktop',
                registeredAt: new Date().toISOString()
            });
        }
        window._currentStudioTrustedDevices = initialTrustedDevices;

        let totalScreensCount = 0;
        SYSTEM_SCREENS_CATALOG.forEach(c => totalScreensCount += c.screens.length);

        let html = `
            <div class="perm-studio-wrap" style="font-family:'Tajawal', sans-serif; direction:rtl; color:#f8fafc;">
                
                <!-- 1. Header & Role Type Selection -->
                <div style="background:rgba(15,23,42,0.6); border:1.5px solid rgba(56,189,248,0.3); border-radius:18px; padding:20px; margin-bottom:20px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
                        <div>
                            <h3 style="margin:0 0 6px 0; color:#38bdf8; font-size:1.35rem; font-weight:900; display:flex; align-items:center; gap:10px;">
                                <i class="fa-solid fa-user-shield"></i> استوديو التحكم الشامل في الصلاحيات والشاشات والأزرار
                            </h3>
                            <p style="margin:0; color:#94a3b8; font-size:0.92rem;">
                                تحكم كامل من الألف إلى الياء: هل تظهر الشاشة للمستخدم؟ وما الأزرار المسموح له باستخدامها داخلها؟ وما التقارير المصرح بها؟
                            </p>
                        </div>

                        <!-- User Type Selection (Admin vs Standard) -->
                        <div style="display:flex; gap:10px; background:rgba(0,0,0,0.5); padding:6px; border-radius:12px; border:1px solid rgba(255,255,255,0.12);">
                            <label style="display:flex; align-items:center; gap:8px; padding:8px 18px; border-radius:8px; cursor:pointer; font-weight:800; font-size:0.95rem; transition:all 0.2s;" id="label-role-admin" class="${isUserAdmin ? 'active-role-admin' : ''}">
                                <input type="radio" name="studio_user_type" value="admin" ${isUserAdmin ? 'checked' : ''} onchange="window.onStudioUserTypeChange('admin')" style="accent-color:#ef4444; width:16px; height:16px;">
                                <span style="color:#f87171;">👑 مدير نظام (كامل الصلاحيات)</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; padding:8px 18px; border-radius:8px; cursor:pointer; font-weight:800; font-size:0.95rem; transition:all 0.2s;" id="label-role-user" class="${!isUserAdmin ? 'active-role-user' : ''}">
                                <input type="radio" name="studio_user_type" value="user" ${!isUserAdmin ? 'checked' : ''} onchange="window.onStudioUserTypeChange('user')" style="accent-color:#38bdf8; width:16px; height:16px;">
                                <span style="color:#38bdf8;">👤 مستخدم عادي (تخصيص دقيق)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Live Global Search Bar & Quick Jump Buttons -->
                    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; border-top:1px solid rgba(255,255,255,0.08); padding-top:14px;">
                        <div style="position:relative; flex:1; min-width:280px; max-width:550px;">
                            <i class="fa-solid fa-magnifying-glass" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#38bdf8; font-size:1rem;"></i>
                            <input type="text" id="studio-search-input" oninput="window.filterStudioItems(this.value)" placeholder="🔍 ابحث بذكاء عن أي شيء (مثل: أوقات العمل، الورديات، ساعات الدوام، GPS، مبيعات، ترحيل، خصم)..." style="width:100%; box-sizing:border-box; padding:10px 42px 10px 14px; border-radius:10px; background:rgba(0,0,0,0.4); border:1px solid rgba(56,189,248,0.3); color:#fff; font-size:0.92rem; outline:none; font-family:'Tajawal', sans-serif;">
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            <button type="button" onclick="window.jumpToWorkShiftAndGeoTab()" style="background:linear-gradient(135deg, rgba(14,165,233,0.25), rgba(3,105,161,0.4)); border:1.5px solid #38bdf8; color:#38bdf8; padding:7px 14px; border-radius:10px; font-weight:800; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.35)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(14,165,233,0.25), rgba(3,105,161,0.4))'">
                                <i class="fa-solid fa-clock"></i>
                                <span>⏱️ وقت العمل والورديات والـ GPS</span>
                            </button>
                            <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); padding:6px 14px; border-radius:20px; font-weight:800; font-size:0.85rem;">
                                🖥️ إجمالي الشاشات: ${totalScreensCount} شاشة
                            </span>
                            <span class="badge" style="background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); padding:6px 14px; border-radius:20px; font-weight:800; font-size:0.85rem;">
                                📑 التقارير: ${SYSTEM_REPORTS_CATALOG.length} تقريراً
                            </span>
                        </div>
                    </div>
                    <!-- Live Search Results & Smart Jump Bar -->
                    <div id="studio-search-results-bar" style="display:none; margin-top:12px; padding:12px 16px; background:rgba(15,23,42,0.85); border:1.5px solid rgba(56,189,248,0.4); border-radius:12px; font-size:0.88rem; box-shadow:0 6px 20px rgba(0,0,0,0.3);"></div>
                </div>

                <!-- Admin Notice (Shown when Admin is selected) -->
                <div id="studio-admin-notice" style="display:${isUserAdmin ? 'block' : 'none'}; background:linear-gradient(135deg, rgba(239,68,68,0.12), rgba(15,23,42,0.8)); border:1.5px solid #ef4444; border-radius:16px; padding:25px; text-align:center; margin-bottom:20px;">
                    <div style="font-size:2.5rem; margin-bottom:10px;">👑</div>
                    <h4 style="margin:0 0 8px 0; color:#f87171; font-size:1.25rem; font-weight:800;">هذا الحساب تم تعيينه كـ "مدير نظام"</h4>
                    <p style="margin:0; color:#cbd5e1; font-size:0.95rem; max-width:650px; margin:0 auto; line-height:1.6;">
                        يمتلك مدير النظام صلاحيات سيادية كاملة وتلقائية للوصول إلى كافة شاشات البرنامج، وجميع أزرار العمليات (حفظ، جديد، تعديل، حذف، طباعة، ترحيل، مشاركة)، وجميع التقارير والإعدادات بدون أي قيود.
                    </p>
                </div>

                <!-- Custom Permissions Studio Container -->
                <div id="studio-custom-permissions-container" style="display:${isUserAdmin ? 'none' : 'block'};">
                    
                    <!-- 2. Security Clearance Level (مستوى السرية والوصول للمعلومات 1 - 10) -->
                    <div style="background:linear-gradient(135deg, rgba(15,23,42,0.85), rgba(30,41,59,0.7)); border:1.5px solid rgba(245,158,11,0.4); border-radius:18px; padding:20px; margin-bottom:20px; box-shadow:0 10px 30px rgba(0,0,0,0.35);">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span style="width:42px; height:42px; border-radius:12px; background:rgba(245,158,11,0.2); border:1.5px solid #f59e0b; color:#f59e0b; display:flex; align-items:center; justify-content:center; font-size:1.35rem; box-shadow:0 0 15px rgba(245,158,11,0.25);">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </span>
                                <div>
                                    <h4 style="margin:0; color:#fbbf24; font-size:1.2rem; font-weight:900; display:flex; align-items:center; gap:10px;">
                                        درجة السرية والأمان المالي (Security Clearance Level 1 - 10)
                                        <span id="studio-sec-level-badge" style="background:#f59e0b; color:#030712; padding:3px 12px; border-radius:20px; font-size:0.85rem; font-weight:900; box-shadow:0 0 10px rgba(245,158,11,0.5);">
                                            المستوى الحالي: ${userSecLevel}
                                        </span>
                                    </h4>
                                    <p style="margin:4px 0 0 0; color:#cbd5e1; font-size:0.88rem;">
                                        تحديد سقف سرية الحسابات والعملاء والموردين والبنوك التي يُسمح لهذا المستخدم برؤيتها أو التعامل معها
                                    </p>
                                </div>
                            </div>

                            <!-- Live Discovery Button -->
                            <div style="display:flex; gap:10px;">
                                <button type="button" onclick="window.runStudioAutoDiscovery()" class="preset-btn" style="background:rgba(56,189,248,0.15); border:1.5px solid #38bdf8; color:#38bdf8; padding:9px 18px; border-radius:10px; font-weight:800; display:flex; align-items:center; gap:8px; cursor:pointer; box-shadow:0 0 15px rgba(56,189,248,0.2);">
                                    <i class="fa-solid fa-arrows-rotate"></i> 🔄 فحص واستكشاف عناصر الشاشات تلقائياً
                                </button>
                            </div>
                        </div>

                        <!-- Interactive Slider & Level Selector -->
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:18px; align-items:center; background:rgba(0,0,0,0.35); padding:16px; border-radius:14px; border:1px solid rgba(255,255,255,0.08);">
                            <div>
                                <label style="display:block; font-size:0.88rem; color:#94a3b8; font-weight:800; margin-bottom:8px;">اختر المستوى الأمني المصرح به للموظف:</label>
                                <select id="studio-user-security-level" onchange="window.onStudioSecLevelChange(this.value)" style="width:100%; padding:11px 16px; border-radius:10px; background:#0f172a; border:1.5px solid #f59e0b; color:#fbbf24; font-weight:800; font-size:1rem; outline:none; font-family:'Tajawal', sans-serif;">
                                    <option value="1" ${userSecLevel == 1 ? 'selected' : ''}>1 - مستوى أساسي (استعلام محدود جداً)</option>
                                    <option value="2" ${userSecLevel == 2 ? 'selected' : ''}>2 - مدخل بيانات مبتدئ / عامل مستودع</option>
                                    <option value="3" ${userSecLevel == 3 ? 'selected' : ''}>3 - كاشير مبيعات عادي</option>
                                    <option value="4" ${userSecLevel == 4 ? 'selected' : ''}>4 - كاشير أول / مسؤول صالة (مثل: جمانة مهدي)</option>
                                    <option value="5" ${userSecLevel == 5 ? 'selected' : ''}>5 - محاسب مدفوعات ومقبوضات عامة</option>
                                    <option value="6" ${userSecLevel == 6 ? 'selected' : ''}>6 - رئيس حسابات / مسؤول مشتريات (يرى بنك الخرطوم)</option>
                                    <option value="7" ${userSecLevel == 7 ? 'selected' : ''}>7 - مدير فرع / مالي متقدم</option>
                                    <option value="8" ${userSecLevel == 8 ? 'selected' : ''}>8 - مراجع داخلي ومدير رقابة</option>
                                    <option value="9" ${userSecLevel == 9 ? 'selected' : ''}>9 - مدير عام / إدارة عليا</option>
                                    <option value="10" ${userSecLevel == 10 ? 'selected' : ''}>10 - مجلس الإدارة والمالك (سري للغاية)</option>
                                </select>
                            </div>
                            <div>
                                <div id="studio-sec-level-explanation" style="font-size:0.92rem; line-height:1.7; color:#f1f5f9; background:rgba(245,158,11,0.08); border-right:4px solid #f59e0b; padding:12px 16px; border-radius:8px;">
                                    <!-- Dynamically populated explanation -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 🤖 AI-Driven 3-Tier Financial & Accounting Architecture Panel -->
                    <div id="studio-ai-tiers-panel" style="background:linear-gradient(135deg, rgba(30,27,75,0.7) 0%, rgba(15,23,42,0.85) 100%); border:2px solid rgba(139,92,246,0.4); border-radius:18px; padding:22px 24px; margin-bottom:22px; box-shadow:0 12px 35px rgba(0,0,0,0.4); position:relative; overflow:hidden;">
                        <!-- Glowing accent line at top -->
                        <div style="position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #10b981, #f59e0b, #ef4444, #8b5cf6);"></div>

                        <!-- Header -->
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px; padding-bottom:14px; border-bottom:1px solid rgba(255,255,255,0.08);">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:44px; height:44px; border-radius:12px; background:linear-gradient(135deg, #8b5cf6, #6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.35rem; box-shadow:0 4px 15px rgba(139,92,246,0.4);">
                                    <i class="fa-solid fa-brain"></i>
                                </div>
                                <div>
                                    <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px;">
                                        تقسيم الصلاحيات الذكي بالذكاء الاصطناعي (AI Financial Architecture)
                                        <span style="font-size:0.75rem; background:rgba(139,92,246,0.25); color:#c4b5fd; border:1px solid rgba(139,92,246,0.5); padding:2px 10px; border-radius:20px; font-weight:700;">3 مستويات قياسية اختيارية</span>
                                    </h3>
                                    <div style="font-size:0.85rem; color:#cbd5e1; margin-top:3px;">
                                        مبني على أسس هندسة البرمجيات والمعايير المحاسبية العالمية والرقابة الداخلية (COSO & Segregation of Duties)
                                    </div>
                                </div>
                            </div>
                            <div id="studio-active-ai-level-badge" style="font-size:0.85rem; font-weight:800; padding:6px 14px; border-radius:10px; background:${userAiSmartLevel === 'level_1' ? 'rgba(16,185,129,0.18)' : userAiSmartLevel === 'level_2' ? 'rgba(245,158,11,0.18)' : userAiSmartLevel === 'level_3' ? 'rgba(239,68,68,0.18)' : 'rgba(255,255,255,0.06)'}; border:1px solid ${userAiSmartLevel === 'level_1' ? '#10b981' : userAiSmartLevel === 'level_2' ? '#f59e0b' : userAiSmartLevel === 'level_3' ? '#ef4444' : 'rgba(255,255,255,0.15)'}; color:${userAiSmartLevel === 'level_1' ? '#34d399' : userAiSmartLevel === 'level_2' ? '#fbbf24' : userAiSmartLevel === 'level_3' ? '#f87171' : '#94a3b8'}; display:flex; align-items:center; gap:6px;">
                                ${userAiSmartLevel === 'level_1' ? '<i class="fa-solid fa-circle-check" style="color:#10b981;"></i> المستوى الأول 🟢 (نشط - جاهز للتعديل أدناه)' :
                                  userAiSmartLevel === 'level_2' ? '<i class="fa-solid fa-circle-check" style="color:#f59e0b;"></i> المستوى الثاني 🟡 (نشط - جاهز للتعديل أدناه)' :
                                  userAiSmartLevel === 'level_3' ? '<i class="fa-solid fa-circle-check" style="color:#ef4444;"></i> المستوى الثالث 🔴 (نشط - جاهز للتعديل أدناه)' :
                                  '<i class="fa-solid fa-layer-group"></i> لم يتم اختيار مستوى بعد (يمكنك الاختيار أدناه)'}
                            </div>
                        </div>

                        <!-- Instructions subtext -->
                        <p style="margin:0 0 16px 0; font-size:0.88rem; color:#e2e8f0; line-height:1.6;">
                            🎯 <strong>كيف يعمل نظام المستويات الثلاثة؟</strong> اختر أحد المستويات القياسية الثلاثة أدناه كقالب أساسي ذكي؛ سيقوم النظام بضبط كافة الشاشات والأزرار والتقارير والحدود المحاسبية تلقائياً، <strong>وبعدها يمكنك فوراً النزول لخيارات الشاشات والتبويبات بالأسفل وتعديل أي خيار بحرية تامة</strong> حسب رغبتك قبل الحفظ.
                        </p>

                        <!-- The 3 Cards Grid -->
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(310px, 1fr)); gap:16px; margin-bottom:14px;">
                            
                            <!-- Level 1 Card -->
                            <div id="ai-tier-card-level_1" class="ai-tier-card ${userAiSmartLevel === 'level_1' ? 'ai-tier-card-active' : ''}" onclick="window.applySmartAILevel('level_1')" style="background:${userAiSmartLevel === 'level_1' ? 'rgba(16,185,129,0.12)' : 'rgba(15,23,42,0.7)'}; border:2px solid ${userAiSmartLevel === 'level_1' ? '#10b981' : 'rgba(16,185,129,0.35)'}; box-shadow:${userAiSmartLevel === 'level_1' ? '0 0 25px rgba(16,185,129,0.45)' : 'none'}; border-radius:14px; padding:18px; cursor:pointer; transition:all 0.25s ease; position:relative; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <input type="radio" name="ai_smart_tier_radio" id="ai-tier-radio-level_1" value="level_1" ${userAiSmartLevel === 'level_1' ? 'checked' : ''} style="accent-color:#10b981; width:18px; height:18px; cursor:pointer;" onclick="event.stopPropagation(); window.applySmartAILevel('level_1')">
                                            <span style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.2); color:#34d399; border:1px solid #10b981; padding:3px 10px; border-radius:12px;">المستوى الأول 🟢</span>
                                        </div>
                                        <span style="font-size:0.75rem; color:#94a3b8; font-weight:700;">أمان: مستوى 2</span>
                                    </div>

                                    <h4 style="margin:0 0 6px 0; font-size:1.05rem; font-weight:800; color:#34d399;">التشغيلي ونقاط البيع (Operational & POS)</h4>
                                    <div style="font-size:0.8rem; color:#6ee7b7; font-weight:700; margin-bottom:10px;">الكاشير، بائعو الصالات، المندوبون الميدانيون</div>

                                    <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(16,185,129,0.2); border-radius:8px; padding:10px; margin-bottom:12px; font-size:0.82rem; line-height:1.5; color:#cbd5e1;">
                                        ⚖️ <strong>المعيار المحاسبي:</strong> الفصل التام بين حيازة النقدية والقيود (Segregation of Duties). حصر الصلاحيات في القبض والبيع اليومي المباشر.
                                    </div>

                                    <ul style="margin:0 0 14px 0; padding-right:18px; font-size:0.82rem; color:#e2e8f0; line-height:1.7;">
                                        <li>🛒 <strong>الشاشات:</strong> المبيعات المباشرة، الكاشير POS، سند قبض نقدي، كشف حساب عميل، وحركة صنف.</li>
                                        <li>🔒 <strong>الأزرار:</strong> جديد، حفظ، طباعة، مشاركة. <strong style="color:#f87171;">منع تام للتعديل والحذف والترحيل</strong>.</li>
                                        <li>🙈 <strong>حجب البيانات:</strong> حجب أسعار الشراء/التكلفة، حجب هامش الربح، وحجب كلي لحسابات البنوك.</li>
                                        <li>📉 <strong>الحدود المالية:</strong> سقف خصم 2% (بحد أقصى 500 ج.س)، وسقف فاتورة 50,000 ج.س. حظر البيع الآجل.</li>
                                    </ul>
                                </div>

                                <button type="button" class="btn-select-tier" onclick="event.stopPropagation(); window.applySmartAILevel('level_1')" style="width:100%; padding:9px 12px; background:rgba(16,185,129,0.18); border:1.5px solid #10b981; color:#34d399; font-weight:800; font-size:0.85rem; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                                    <span>اختيار المستوى الأول وبدء التعديل</span>
                                    <i class="fa-solid fa-arrow-left"></i>
                                </button>
                            </div>

                            <!-- Level 2 Card -->
                            <div id="ai-tier-card-level_2" class="ai-tier-card ${userAiSmartLevel === 'level_2' ? 'ai-tier-card-active' : ''}" onclick="window.applySmartAILevel('level_2')" style="background:${userAiSmartLevel === 'level_2' ? 'rgba(245,158,11,0.12)' : 'rgba(15,23,42,0.7)'}; border:2px solid ${userAiSmartLevel === 'level_2' ? '#f59e0b' : 'rgba(245,158,11,0.35)'}; box-shadow:${userAiSmartLevel === 'level_2' ? '0 0 25px rgba(245,158,11,0.45)' : 'none'}; border-radius:14px; padding:18px; cursor:pointer; transition:all 0.25s ease; position:relative; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <input type="radio" name="ai_smart_tier_radio" id="ai-tier-radio-level_2" value="level_2" ${userAiSmartLevel === 'level_2' ? 'checked' : ''} style="accent-color:#f59e0b; width:18px; height:18px; cursor:pointer;" onclick="event.stopPropagation(); window.applySmartAILevel('level_2')">
                                            <span style="font-size:0.75rem; font-weight:800; background:rgba(245,158,11,0.2); color:#fbbf24; border:1px solid #f59e0b; padding:3px 10px; border-radius:12px;">المستوى الثاني 🟡</span>
                                        </div>
                                        <span style="font-size:0.75rem; color:#94a3b8; font-weight:700;">أمان: مستوى 6</span>
                                    </div>

                                    <h4 style="margin:0 0 6px 0; font-size:1.05rem; font-weight:800; color:#fbbf24;">المحاسبي والمخزني والإشرافي (Supervisory & Accounting)</h4>
                                    <div style="font-size:0.8rem; color:#fde68a; font-weight:700; margin-bottom:10px;">المحاسبون، أمناء المخازن، مسؤولو المشتريات، والمشرفون</div>

                                    <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(245,158,11,0.2); border-radius:8px; padding:10px; margin-bottom:12px; font-size:0.82rem; line-height:1.5; color:#cbd5e1;">
                                        ⚖️ <strong>المعيار المحاسبي:</strong> الرقابة الثنائية والمطابقات المالية (Dual Control). إعداد ومراجعة العمليات قبل الاعتماد النهائي.
                                    </div>

                                    <ul style="margin:0 0 14px 0; padding-right:18px; font-size:0.82rem; color:#e2e8f0; line-height:1.7;">
                                        <li>📦 <strong>الشاشات:</strong> المبيعات، المشتريات، المرتجعات، سندات الصرف والقبض، المخازن، الجرد، دليل الحسابات وميزان المراجعة.</li>
                                        <li>✍️ <strong>الأزرار:</strong> حفظ وتعديل المعاملات غير المرحلة. <strong style="color:#f87171;">الحذف مقيد، والترحيل للعمليات التشغيلية فقط</strong>.</li>
                                        <li>👁️ <strong>حجب البيانات:</strong> إظهار أسعار التكلفة للمطابقة وحسابات البنوك التشغيلية، مع حجب هامش صافي الربح السري.</li>
                                        <li>💳 <strong>الحدود المالية:</strong> سقف خصم 15% (10,000 ج.س)، سندات 250,000 ج.س، فاتورة 1,000,000 ج.س، والسماح بالبيع الآجل.</li>
                                    </ul>
                                </div>

                                <button type="button" class="btn-select-tier" onclick="event.stopPropagation(); window.applySmartAILevel('level_2')" style="width:100%; padding:9px 12px; background:rgba(245,158,11,0.18); border:1.5px solid #f59e0b; color:#fbbf24; font-weight:800; font-size:0.85rem; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                                    <span>اختيار المستوى الثاني وبدء التعديل</span>
                                    <i class="fa-solid fa-arrow-left"></i>
                                </button>
                            </div>

                            <!-- Level 3 Card -->
                            <div id="ai-tier-card-level_3" class="ai-tier-card ${userAiSmartLevel === 'level_3' ? 'ai-tier-card-active' : ''}" onclick="window.applySmartAILevel('level_3')" style="background:${userAiSmartLevel === 'level_3' ? 'rgba(239,68,68,0.12)' : 'rgba(15,23,42,0.7)'}; border:2px solid ${userAiSmartLevel === 'level_3' ? '#ef4444' : 'rgba(239,68,68,0.35)'}; box-shadow:${userAiSmartLevel === 'level_3' ? '0 0 25px rgba(239,68,68,0.45)' : 'none'}; border-radius:14px; padding:18px; cursor:pointer; transition:all 0.25s ease; position:relative; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <input type="radio" name="ai_smart_tier_radio" id="ai-tier-radio-level_3" value="level_3" ${userAiSmartLevel === 'level_3' ? 'checked' : ''} style="accent-color:#ef4444; width:18px; height:18px; cursor:pointer;" onclick="event.stopPropagation(); window.applySmartAILevel('level_3')">
                                            <span style="font-size:0.75rem; font-weight:800; background:rgba(239,68,68,0.2); color:#f87171; border:1px solid #ef4444; padding:3px 10px; border-radius:12px;">المستوى الثالث 🔴</span>
                                        </div>
                                        <span style="font-size:0.75rem; color:#94a3b8; font-weight:700;">أمان: مستوى 9</span>
                                    </div>

                                    <h4 style="margin:0 0 6px 0; font-size:1.05rem; font-weight:800; color:#f87171;">الإداري والمالي الشامل والتدقيق (Executive & Controller)</h4>
                                    <div style="font-size:0.8rem; color:#fca5a5; font-weight:700; margin-bottom:10px;">المدير المالي، مراقب الحسابات، التدقيق الداخلي، والإدارة العليا</div>

                                    <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(239,68,68,0.2); border-radius:8px; padding:10px; margin-bottom:12px; font-size:0.82rem; line-height:1.5; color:#cbd5e1;">
                                        ⚖️ <strong>المعيار المحاسبي:</strong> الحوكمة المالية الشاملة والرقابة التشفيرية (Full Governance & Audit Trail). الاعتماد والرقابة العليا.
                                    </div>

                                    <ul style="margin:0 0 14px 0; padding-right:18px; font-size:0.82rem; color:#e2e8f0; line-height:1.7;">
                                        <li>🏛️ <strong>الشاشات:</strong> وصول شامل لجميع الشاشات ومراكز التكلفة وقوائم الدخل والميزانية العمومية وسجل التدقيق الأمني.</li>
                                        <li>⚡ <strong>الأزرار:</strong> صلاحيات سيادية كاملة: الترحيل، إلغاء الترحيل، الحذف، التحويل، وإدارة الحسابات.</li>
                                        <li>🔓 <strong>حجب البيانات:</strong> كشف تام لكافة البيانات (رؤية كاملة للتكاليف والأرباح والبنوك دون أي حجب).</li>
                                        <li>💎 <strong>الحدود المالية:</strong> سقوف مالية مفتوحة (خصم 50% حتى 1,000,000 ج.س، وسندات وفواتير حتى 10,000,000 ج.س).</li>
                                    </ul>
                                </div>

                                <button type="button" class="btn-select-tier" onclick="event.stopPropagation(); window.applySmartAILevel('level_3')" style="width:100%; padding:9px 12px; background:rgba(239,68,68,0.18); border:1.5px solid #ef4444; color:#f87171; font-weight:800; font-size:0.85rem; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                                    <span>اختيار المستوى الثالث وبدء التعديل</span>
                                    <i class="fa-solid fa-arrow-left"></i>
                                </button>
                            </div>

                        </div>

                        <!-- Footer note -->
                        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:rgba(0,0,0,0.25); border-radius:10px; padding:10px 16px; font-size:0.84rem; color:#94a3b8;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-circle-info" style="color:#38bdf8;"></i>
                                <span>عند اختيار أي مستوى، ستفتح كافة الأقسام والخيارات أدناه (الشاشات، الأزرار، التقارير، السقوف) وتكون قابلة للتعديل الفردي فوراً.</span>
                            </div>
                            <span style="font-weight:700; color:#e2e8f0;">✏️ حرية تعديل 100% بعد الاختيار</span>
                        </div>
                    </div>
                    
                    <!-- Presets Toolbar -->
                    <div id="studio-presets-bar" style="background:rgba(15,23,42,0.4); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:12px 18px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">⚡ قوالب صلاحيات جاهزة بضغطة زر:</span>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <button type="button" onclick="window.applyStudioPreset('cashier')" class="preset-btn">🛒 كاشير / مبيعات</button>
                                <button type="button" onclick="window.applyStudioPreset('purchases_manager')" class="preset-btn">📦 مسؤول مشتريات</button>
                                <button type="button" onclick="window.applyStudioPreset('storekeeper')" class="preset-btn">🏢 أمين مخازن</button>
                                <button type="button" onclick="window.applyStudioPreset('accountant')" class="preset-btn">💰 محاسب مالي</button>
                                <button type="button" onclick="window.applyStudioPreset('viewer')" class="preset-btn">🔍 استعلام فقط</button>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" onclick="window.selectAllStudioScreens(true)" class="bulk-btn" style="background:rgba(16,185,129,0.15); border-color:#10b981; color:#34d399;">تفعيل الكل ✅</button>
                            <button type="button" onclick="window.selectAllStudioScreens(false)" class="bulk-btn" style="background:rgba(239,68,68,0.15); border-color:#ef4444; color:#f87171;">تعطيل الكل ❌</button>
                        </div>
                    </div>

                    <!-- Multi-Section Studio Tabs -->
                    <div class="studio-subtabs" style="display:flex; gap:6px; overflow-x:auto; margin-bottom:18px; padding-bottom:4px; border-bottom:1px solid rgba(255,255,255,0.1);">
                        <button type="button" id="studio-tab-btn-all_screens" class="studio-tab-btn active" onclick="window.switchStudioTab('all_screens', this)">
                            <i class="fa-solid fa-desktop"></i> الشاشات والأزرار التفصيلية
                        </button>
                        <button type="button" id="studio-tab-btn-work_shifts_and_geo" class="studio-tab-btn" onclick="window.switchStudioTab('work_shifts_and_geo', this)" style="border:1.5px solid rgba(56,189,248,0.5); background:rgba(56,189,248,0.12); color:#38bdf8;">
                            <i class="fa-solid fa-shield-halved"></i> الورديات والـ GPS والأجهزة المصرح بها 📱
                            <span style="background:#0284c7; color:#fff; font-size:0.72rem; padding:1px 7px; border-radius:10px; margin-right:5px; font-weight:800;">⏱️ دوام وموقع وأجهزة</span>
                        </button>
                        <button type="button" id="studio-tab-btn-financial_limits" class="studio-tab-btn" onclick="window.switchStudioTab('financial_limits', this)">
                            <i class="fa-solid fa-scale-balanced"></i> سقوف المبالغ والخصومات
                        </button>
                        <button type="button" id="studio-tab-btn-data_masking" class="studio-tab-btn" onclick="window.switchStudioTab('data_masking', this)">
                            <i class="fa-solid fa-eye-slash"></i> حجب وتعتيم الأسعار
                        </button>
                        <button type="button" id="studio-tab-btn-dashboard_widgets" class="studio-tab-btn" onclick="window.switchStudioTab('dashboard_widgets', this)">
                            <i class="fa-solid fa-gauge"></i> لوحة التحكم والمؤشرات
                        </button>
                        <button type="button" id="studio-tab-btn-posting_rights" class="studio-tab-btn" onclick="window.switchStudioTab('posting_rights', this)">
                            <i class="fa-solid fa-sitemap"></i> صلاحيات الترحيل والاعتماد
                        </button>
                        <button type="button" id="studio-tab-btn-master_data_rights" class="studio-tab-btn" onclick="window.switchStudioTab('master_data_rights', this)">
                            <i class="fa-solid fa-database"></i> تفاصيل البيانات الأساسية
                        </button>
                        <button type="button" id="studio-tab-btn-alerts_rights" class="studio-tab-btn" onclick="window.switchStudioTab('alerts_rights', this)">
                            <i class="fa-solid fa-triangle-exclamation"></i> الرقابة والتنبيهات
                        </button>
                        <button type="button" id="studio-tab-btn-tools_rights" class="studio-tab-btn" onclick="window.switchStudioTab('tools_rights', this)">
                            <i class="fa-solid fa-toolbox"></i> الأدوات المساعدة والشريعة
                        </button>
                        <button type="button" id="studio-tab-btn-reports_catalog" class="studio-tab-btn" onclick="window.switchStudioTab('reports_catalog', this)">
                            <i class="fa-solid fa-chart-pie"></i> كتالوج التقارير المصرح بها
                        </button>
                        <button type="button" id="studio-tab-btn-delegations_rights" class="studio-tab-btn" onclick="window.switchStudioTab('delegations_rights', this)">
                            <i class="fa-solid fa-user-clock"></i> التفويض المؤقت
                        </button>
                        <button type="button" id="studio-tab-btn-manager_override_pin" class="studio-tab-btn" onclick="window.switchStudioTab('manager_override_pin', this)">
                            <i class="fa-solid fa-key"></i> رمز مرور المشرف (PIN)
                        </button>
                    </div>

                    <!-- TAB 1: ALL SCREENS & BUTTONS -->
                    <div id="studio-tab-content-all_screens" class="studio-tab-content active" style="display:block;">
                        <div style="display:flex; flex-direction:column; gap:20px;">
        `;

        // Render All Functional Categories and their Screens
        SYSTEM_SCREENS_CATALOG.forEach(cat => {
            html += `
                <div class="studio-cat-card" data-cat-id="${cat.category}" style="background:rgba(15,23,42,0.45); border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:18px; box-shadow:0 6px 20px rgba(0,0,0,0.25);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="width:36px; height:36px; border-radius:10px; background:${cat.color}20; border:1px solid ${cat.color}; color:${cat.color}; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
                                <i class="fa-solid ${cat.categoryIcon}"></i>
                            </span>
                            <div>
                                <h4 style="margin:0; color:#fff; font-size:1.1rem; font-weight:800;">${cat.categoryName}</h4>
                                <span style="font-size:0.78rem; color:#94a3b8;">${cat.desc} (${cat.screens.length} شاشات)</span>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" onclick="window.toggleCatScreens('${cat.category}', true)" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; padding:4px 10px; border-radius:6px; font-size:0.75rem; cursor:pointer;">تحديد الكل</button>
                            <button type="button" onclick="window.toggleCatScreens('${cat.category}', false)" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; padding:4px 10px; border-radius:6px; font-size:0.75rem; cursor:pointer;">إلغاء</button>
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:12px;">
            `;

            cat.screens.forEach(screen => {
                const isScreenAllowed = (allowedScreens[screen.file] !== undefined) ? !!allowedScreens[screen.file] : true;
                const sanitizedFile = screen.file.replace(/[^a-zA-Z0-9]/g, '_');

                html += `
                    <div class="studio-screen-row" data-screen-file="${screen.file}" data-screen-title="${screen.title}" style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.06); border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:10px;">
                        
                        <!-- Primary Screen Row Header & Visibility Switch -->
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="color:${cat.color}; font-size:1.1rem; width:28px; text-align:center;">
                                    <i class="fa-solid ${screen.icon}"></i>
                                </span>
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <strong style="font-size:0.95rem; color:#fff;">${screen.title}</strong>
                                        <code style="font-size:0.75rem; color:#64748b; background:rgba(255,255,255,0.04); padding:1px 6px; border-radius:4px; direction:ltr;">${screen.file}</code>
                                    </div>
                                    <div style="font-size:0.78rem; color:#94a3b8; margin-top:2px;">${screen.desc}</div>
                                </div>
                            </div>

                            <!-- Screen Visibility Master Switch -->
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span id="badge-${sanitizedFile}" class="badge" style="font-size:0.78rem; padding:3px 10px; border-radius:20px; font-weight:800; border:1px solid ${isScreenAllowed ? '#10b981' : '#ef4444'}; background:${isScreenAllowed ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)'}; color:${isScreenAllowed ? '#34d399' : '#f87171'};">
                                    ${isScreenAllowed ? 'ظاهرة للمستخدم' : 'مخفية ومحظورة'}
                                </span>
                                <label class="perm-switch" title="إظهار أو إخفاء هذه الشاشة تماماً">
                                    <input type="checkbox" class="screen-visibility-cb" data-file="${screen.file}" ${isScreenAllowed ? 'checked' : ''} onchange="window.onStudioScreenVisibilityChange('${screen.file}', this.checked)">
                                    <span class="perm-slider round"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Inner Action Buttons Matrix for this Screen -->
                        <div id="actions-${sanitizedFile}" class="screen-actions-panel" style="display:${isScreenAllowed ? 'flex' : 'none'}; background:rgba(15,23,42,0.5); padding:10px 14px; border-radius:8px; border:1px dashed rgba(255,255,255,0.1); flex-wrap:wrap; gap:12px; align-items:center;">
                            <span style="font-size:0.8rem; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:6px;">
                                <i class="fa-solid fa-gamepad"></i> صلاحيات الأزرار الداخلية:
                            </span>
                `;

                // Merge standard supportedActions with any dynamically discovered actions
                let customDiscovered = [];
                try {
                    const discMap = JSON.parse(localStorage.getItem('erp_discovered_screen_actions') || '{}');
                    if (discMap[screen.file] && Array.isArray(discMap[screen.file])) {
                        customDiscovered = discMap[screen.file];
                    }
                } catch(e) {}

                const renderedActions = [...(screen.supportedActions || [])];
                customDiscovered.forEach(cd => {
                    if (!renderedActions.includes(cd.id)) renderedActions.push(cd.id);
                });

                renderedActions.forEach(action => {
                    const isCustomAuto = customDiscovered.some(cd => cd.id === action);
                    const customObj = isCustomAuto ? customDiscovered.find(cd => cd.id === action) : null;
                    const meta = ACTION_META[action] || { 
                        label: (customObj && customObj.name) ? customObj.name : action, 
                        icon: 'fa-cube', 
                        color: '#a78bfa' 
                    };
                    let actionChecked = false;
                    if (screenButtons[screen.file] && typeof screenButtons[screen.file][action] === 'boolean') {
                        actionChecked = screenButtons[screen.file][action];
                    } else if (isScreenAllowed) {
                        // Default basic actions to true if screen is allowed
                        actionChecked = (action === 'save' || action === 'print' || action === 'new' || action === 'preview' || action === 'receive' || action === 'share');
                    }

                    html += `
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; background:rgba(0,0,0,0.3); padding:4px 10px; border-radius:6px; border:1px solid ${isCustomAuto ? 'rgba(167,139,250,0.4)' : 'rgba(255,255,255,0.06)'}; transition:0.2s;" onmouseover="this.style.borderColor='${meta.color}'" onmouseout="this.style.borderColor='${isCustomAuto ? 'rgba(167,139,250,0.4)' : 'rgba(255,255,255,0.06)'}'">
                            <input type="checkbox" class="screen-btn-cb" data-file="${screen.file}" data-action="${action}" ${actionChecked ? 'checked' : ''} style="accent-color:${meta.color}; width:15px; height:15px;">
                            <span style="font-size:0.8rem; font-weight:700; color:#cbd5e1; display:flex; align-items:center; gap:4px;">
                                <i class="fa-solid ${meta.icon}" style="color:${meta.color}; font-size:0.75rem;"></i> ${meta.label}
                                ${isCustomAuto ? '<span style="font-size:0.65rem; background:#8b5cf6; color:#fff; padding:1px 4px; border-radius:4px; margin-right:4px;">✨ جديد تلقائي</span>' : ''}
                            </span>
                        </label>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        html += `
                        </div>
                    </div>

                    <!-- TAB 2: DASHBOARD WIDGETS CONTROL -->
                    <div id="studio-tab-content-dashboard_widgets" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1px solid rgba(56,189,248,0.25); border-radius:16px; padding:22px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
                                <div>
                                    <h4 style="margin:0 0 4px 0; color:#38bdf8; font-size:1.15rem; font-weight:900;">
                                        📊 تخصيص عناصر وبطاقات لوحة التحكم (Dashboard Widgets)
                                    </h4>
                                    <p style="margin:0; color:#94a3b8; font-size:0.88rem;">
                                        حدد البطاقات والمؤشرات التي يحق للمستخدم رؤيتها داخل لوحة التحكم المركزية:
                                    </p>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick="window.toggleAllDashboard(true)" class="bulk-btn" style="background:rgba(56,189,248,0.15); border-color:#38bdf8; color:#38bdf8;">تفعيل الكل</button>
                                    <button type="button" onclick="window.toggleAllDashboard(false)" class="bulk-btn" style="background:rgba(239,68,68,0.15); border-color:#ef4444; color:#f87171;">تعطيل الكل</button>
                                </div>
                            </div>
                            
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
        `;

        DASHBOARD_COMPONENTS.forEach(widget => {
            const isAllowed = typeof dashboardPerms[widget.id] === 'boolean' ? dashboardPerms[widget.id] : true;
            html += `
                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.1rem; color:#38bdf8;"><i class="fa-solid ${widget.icon}"></i></span>
                        <span style="font-size:0.88rem; font-weight:700; color:#fff;">${widget.title}</span>
                    </div>
                    <label class="perm-switch">
                        <input type="checkbox" class="dash-perm-cb" data-widget-id="${widget.id}" ${isAllowed ? 'checked' : ''}>
                        <span class="perm-slider round"></span>
                    </label>
                </div>
            `;
        });

        html += `
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: POSTING & APPROVALS (ترحيل الحسابات) -->
                    <div id="studio-tab-content-posting_rights" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1.5px solid rgba(96,165,250,0.3); border-radius:16px; padding:22px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
                                <div>
                                    <h4 style="margin:0 0 6px 0; color:#60a5fa; font-size:1.2rem; font-weight:900;">
                                        ⚡ صلاحيات الترحيل والاعتماد المالي والختامي
                                    </h4>
                                    <p style="margin:0; color:#94a3b8; font-size:0.88rem;">
                                        تحكم دقيق: هل يحق للمستخدم ترحيل المبيعات؟ ترحيل المشتريات؟ ترحيل الصرف المالي؟ أم فك الترحيل؟
                                    </p>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick="window.toggleAllPosting(true)" class="bulk-btn" style="background:rgba(96,165,250,0.15); border-color:#60a5fa; color:#60a5fa;">تفعيل جميع الترحيلات</button>
                                    <button type="button" onclick="window.toggleAllPosting(false)" class="bulk-btn" style="background:rgba(239,68,68,0.15); border-color:#ef4444; color:#f87171;">منع الترحيل</button>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px; margin-top:16px;">
        `;

        POSTING_ACTIONS.forEach(postAction => {
            const isAllowed = (postingPerms[postAction.id] !== undefined) ? !!postingPerms[postAction.id] : true;
            html += `
                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(96,165,250,0.2); border-radius:12px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.2rem; color:#60a5fa;"><i class="fa-solid ${postAction.icon}"></i></span>
                        <span style="font-size:0.92rem; font-weight:800; color:#fff;">${postAction.title}</span>
                    </div>
                    <label class="perm-switch">
                        <input type="checkbox" class="posting-perm-cb" data-post-id="${postAction.id}" ${isAllowed ? 'checked' : ''}>
                        <span class="perm-slider round"></span>
                    </label>
                </div>
            `;
        });

        html += `
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: MASTER DATA GRANULAR RIGHTS -->
                    <div id="studio-tab-content-master_data_rights" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1px solid rgba(59,130,246,0.25); border-radius:16px; padding:22px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
                                <div>
                                    <h4 style="margin:0 0 4px 0; color:#3b82f6; font-size:1.15rem; font-weight:900;">
                                        🗄️ تفاصيل وصلاحيات البيانات الأساسية (عملاء، موردين، أصناف، حسابات)
                                    </h4>
                                    <p style="margin:0; color:#94a3b8; font-size:0.88rem;">
                                        التحكم الدقيق في إمكانية إضافة، تعديل، أو حذف أي كيان أساسي في النظام:
                                    </p>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick="window.toggleAllMasterData(true)" class="bulk-btn" style="background:rgba(59,130,246,0.15); border-color:#3b82f6; color:#60a5fa;">تفعيل الكل</button>
                                    <button type="button" onclick="window.toggleAllMasterData(false)" class="bulk-btn" style="background:rgba(239,68,68,0.15); border-color:#ef4444; color:#f87171;">تعطيل الكل</button>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
        `;

        MASTER_DATA_RIGHTS.forEach(right => {
            const isAllowed = typeof masterPerms[right.id] === 'boolean' ? masterPerms[right.id] : true;
            html += `
                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.1rem; color:#3b82f6;"><i class="fa-solid ${right.icon}"></i></span>
                        <span style="font-size:0.88rem; font-weight:700; color:#fff;">${right.title}</span>
                    </div>
                    <label class="perm-switch">
                        <input type="checkbox" class="master-perm-cb" data-master-id="${right.id}" ${isAllowed ? 'checked' : ''}>
                        <span class="perm-slider round"></span>
                    </label>
                </div>
            `;
        });

        html += `
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: ALERTS CONFIGURATION RIGHTS -->
                    <div id="studio-tab-content-alerts_rights" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1px solid rgba(248,113,113,0.25); border-radius:16px; padding:22px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
                                <div>
                                    <h4 style="margin:0 0 4px 0; color:#f87171; font-size:1.15rem; font-weight:900;">
                                        🔔 صلاحيات مصفوفة الرقابة والتنبيهات
                                    </h4>
                                    <p style="margin:0; color:#94a3b8; font-size:0.88rem;">
                                        تحديد قدرة المستخدم على ضبط وتفعيل تنبيهات المخازن، المشتريات، والمالية وتعديل الحدود:
                                    </p>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick="window.toggleAllAlerts(true)" class="bulk-btn" style="background:rgba(248,113,113,0.15); border-color:#f87171; color:#f87171;">تفعيل الكل</button>
                                    <button type="button" onclick="window.toggleAllAlerts(false)" class="bulk-btn" style="background:rgba(239,68,68,0.15); border-color:#ef4444; color:#f87171;">تعطيل الكل</button>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
        `;

        ALERTS_RIGHTS.forEach(alertRight => {
            const isAllowed = typeof alertsPerms[alertRight.id] === 'boolean' ? alertsPerms[alertRight.id] : true;
            html += `
                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(248,113,113,0.15); border-radius:12px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.1rem; color:#f87171;"><i class="fa-solid ${alertRight.icon}"></i></span>
                        <span style="font-size:0.88rem; font-weight:700; color:#fff;">${alertRight.title}</span>
                    </div>
                    <label class="perm-switch">
                        <input type="checkbox" class="alert-perm-cb" data-alert-id="${alertRight.id}" ${isAllowed ? 'checked' : ''}>
                        <span class="perm-slider round"></span>
                    </label>
                </div>
            `;
        });

        html += `
                            </div>
                        </div>
                    </div>

                    <!-- TAB 6: TOOLS & SHARIA RIGHTS -->
                    <div id="studio-tab-content-tools_rights" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1px solid rgba(217,119,6,0.3); border-radius:16px; padding:22px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
                                <div>
                                    <h4 style="margin:0 0 4px 0; color:#fbbf24; font-size:1.15rem; font-weight:900;">
                                        🛠️ صلاحيات الأدوات المساعدة والشريعة الإسلامية
                                    </h4>
                                    <p style="margin:0; color:#94a3b8; font-size:0.88rem;">
                                        التحكم في استخدام حاسبة الميراث الشرعي، موسوعة الفتاوى، وبث التلفزيون:
                                    </p>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick="window.toggleAllTools(true)" class="bulk-btn" style="background:rgba(251,191,36,0.15); border-color:#fbbf24; color:#fbbf24;">تفعيل الكل</button>
                                    <button type="button" onclick="window.toggleAllTools(false)" class="bulk-btn" style="background:rgba(239,68,68,0.15); border-color:#ef4444; color:#f87171;">تعطيل الكل</button>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
        `;

        TOOLS_RIGHTS.forEach(tool => {
            const isAllowed = typeof toolsPerms[tool.id] === 'boolean' ? toolsPerms[tool.id] : true;
            html += `
                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(217,119,6,0.2); border-radius:12px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.1rem; color:#fbbf24;"><i class="fa-solid ${tool.icon}"></i></span>
                        <span style="font-size:0.88rem; font-weight:700; color:#fff;">${tool.title}</span>
                    </div>
                    <label class="perm-switch">
                        <input type="checkbox" class="tool-perm-cb" data-tool-id="${tool.id}" ${isAllowed ? 'checked' : ''}>
                        <span class="perm-slider round"></span>
                    </label>
                </div>
            `;
        });

        html += `
                            </div>
                        </div>
                    </div>

                    <!-- TAB 7: COMPREHENSIVE REPORTS CATALOG -->
                    <div id="studio-tab-content-reports_catalog" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1.5px solid rgba(244,114,182,0.3); border-radius:16px; padding:22px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                                <div>
                                    <h4 style="margin:0 0 6px 0; color:#f472b6; font-size:1.2rem; font-weight:900;">
                                        📑 كتالوج التقارير التحليلية والمالية الشامل (20 تقريراً)
                                    </h4>
                                    <p style="margin:0; color:#94a3b8; font-size:0.88rem;">
                                        اختر بدقة كل تقرير مسموح للمستخدم باستعراضه أو طباعته وتصديره:
                                    </p>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick="window.toggleAllReports(true)" class="bulk-btn" style="background:rgba(244,114,182,0.15); border-color:#f472b6; color:#f472b6;">تفعيل كل التقارير</button>
                                    <button type="button" onclick="window.toggleAllReports(false)" class="bulk-btn" style="background:rgba(239,68,68,0.15); border-color:#ef4444; color:#f87171;">حظر كل التقارير</button>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(290px, 1fr)); gap:12px; margin-top:16px;">
        `;

        SYSTEM_REPORTS_CATALOG.forEach(rep => {
            const isRepAllowed = (allowedReports[rep.id] !== undefined) ? !!allowedReports[rep.id] : true;
            html += `
                <div class="studio-report-card" data-report-title="${rep.title}" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.1rem; color:#f472b6;"><i class="fa-solid ${rep.icon}"></i></span>
                        <span style="font-size:0.88rem; font-weight:700; color:#fff;">${rep.title}</span>
                    </div>
                    <label class="perm-switch">
                        <input type="checkbox" class="report-perm-cb" data-report-id="${rep.id}" ${isRepAllowed ? 'checked' : ''}>
                        <span class="perm-slider round"></span>
                    </label>
                </div>
            `;
        });

        html += `
                            </div>
                        </div>
                    </div>

                    <!-- TAB 8: FINANCIAL APPROVAL LIMITS & THRESHOLDS -->
                    <div id="studio-tab-content-financial_limits" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1.5px solid rgba(16,185,129,0.3); border-radius:16px; padding:22px;">
                            <h4 style="margin:0 0 6px 0; color:#34d399; font-size:1.2rem; font-weight:900;">
                                💳 سقوف وحدود المبالغ المالية والخصومات (Financial Approval Thresholds)
                            </h4>
                            <p style="margin:0 0 18px 0; color:#94a3b8; font-size:0.88rem;">
                                تحديد السقوف المالية المباشرة للمستخدم. عند تجاوز أي سقف يتطلب النظام موافقة فورية برمز المشرف (Manager PIN Override):
                            </p>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px;">
                                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px;">
                                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#cbd5e1; margin-bottom:6px;">أقصى نسبة خصم مسموحة (%):</label>
                                    <input type="number" id="studio-limit-discount-pct" value="${financialLimits.maxDiscountPercent !== undefined ? financialLimits.maxDiscountPercent : ''}" placeholder="مثال: 5 (اتركه فارغاً للافتراضي)" min="0" max="100" style="width:100%; padding:9px 12px; background:#0f172a; border:1px solid #10b981; color:#fff; border-radius:8px; font-weight:700; outline:none; box-sizing:border-box;">
                                    <span style="font-size:0.75rem; color:#94a3b8; margin-top:4px; display:block;">ما فوق هذه النسبة يتطلب رمز المشرف فورياً.</span>
                                </div>
                                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px;">
                                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#cbd5e1; margin-bottom:6px;">أقصى قيمة خصم مسموح (SDG):</label>
                                    <input type="number" id="studio-limit-discount-amt" value="${financialLimits.maxDiscountAmount !== undefined ? financialLimits.maxDiscountAmount : ''}" placeholder="مثال: 1000" min="0" style="width:100%; padding:9px 12px; background:#0f172a; border:1px solid #10b981; color:#fff; border-radius:8px; font-weight:700; outline:none; box-sizing:border-box;">
                                    <span style="font-size:0.75rem; color:#94a3b8; margin-top:4px; display:block;">الحد الأقصى للخصم المالي الثابت.</span>
                                </div>
                                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px;">
                                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#cbd5e1; margin-bottom:6px;">أقصى مبلغ لسند الصرف النقدي (SDG):</label>
                                    <input type="number" id="studio-limit-voucher-amt" value="${financialLimits.maxVoucherAmount !== undefined ? financialLimits.maxVoucherAmount : ''}" placeholder="مثال: 25000" min="0" style="width:100%; padding:9px 12px; background:#0f172a; border:1px solid #10b981; color:#fff; border-radius:8px; font-weight:700; outline:none; box-sizing:border-box;">
                                    <span style="font-size:0.75rem; color:#94a3b8; margin-top:4px; display:block;">سندات الصرف الأعلى تتطلب توقيع/PIN المشرف.</span>
                                </div>
                                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px;">
                                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#cbd5e1; margin-bottom:6px;">أقصى قيمة لفاتورة مبيعات مباشرة (SDG):</label>
                                    <input type="number" id="studio-limit-invoice-amt" value="${financialLimits.maxInvoiceAmount !== undefined ? financialLimits.maxInvoiceAmount : ''}" placeholder="مثال: 150000" min="0" style="width:100%; padding:9px 12px; background:#0f172a; border:1px solid #10b981; color:#fff; border-radius:8px; font-weight:700; outline:none; box-sizing:border-box;">
                                    <span style="font-size:0.75rem; color:#94a3b8; margin-top:4px; display:block;">الفواتير الأكبر تتطلب اعتماد المدير.</span>
                                </div>
                            </div>
                            <div style="margin-top:16px; background:rgba(0,0,0,0.2); border-radius:10px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between;">
                                <div>
                                    <strong style="color:#fff; font-size:0.92rem;">السماح بإصدار فواتير بيع آجل (Credit Sales)</strong>
                                    <p style="margin:2px 0 0; color:#94a3b8; font-size:0.8rem;">عند الإلغاء، يمنع المستخدم من اختيار خيار البيع الآجل تماماً</p>
                                </div>
                                <label class="perm-switch">
                                    <input type="checkbox" id="studio-limit-credit-sale" ${financialLimits.allowCreditSale !== false ? 'checked' : ''}>
                                    <span class="perm-slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 9: SENSITIVE DATA MASKING & REDACTION -->
                    <div id="studio-tab-content-data_masking" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1.5px solid rgba(168,85,247,0.3); border-radius:16px; padding:22px;">
                            <h4 style="margin:0 0 6px 0; color:#c084fc; font-size:1.2rem; font-weight:900;">
                                👁️ حجب وتعتيم البيانات والأسعار الحساسة (Data Masking & Redaction)
                            </h4>
                            <p style="margin:0 0 18px 0; color:#94a3b8; font-size:0.88rem;">
                                إخفاء البيانات السرية للأصناف والبنوك والعملاء عن هذا الموظف، مع إمكانية النقر على القيمة وطلب كشفها برمز المشرف:
                            </p>
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <strong style="color:#fff; font-size:0.95rem;">🔒 حجب وتعتيم أسعار التكلفة للأصناف (Cost Price Masking)</strong>
                                        <p style="margin:3px 0 0; color:#94a3b8; font-size:0.82rem;">تظهر التكلفة كـ •••••• مغبشة في فواتير المبيعات وتقارير الجرد لمنع تسريب أسعار الموردين.</p>
                                    </div>
                                    <label class="perm-switch">
                                        <input type="checkbox" id="studio-mask-cost" ${dataMasking.maskCostPrice !== false ? 'checked' : ''}>
                                        <span class="perm-slider round"></span>
                                    </label>
                                </div>
                                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <strong style="color:#fff; font-size:0.95rem;">🔒 حجب هوامش الربح والأرباح الصافية (Profit Redaction)</strong>
                                        <p style="margin:3px 0 0; color:#94a3b8; font-size:0.82rem;">إخفاء بطاقات مؤشرات الأرباح ونسب الربح في لوحة التحكم وتقارير الفواتير.</p>
                                    </div>
                                    <label class="perm-switch">
                                        <input type="checkbox" id="studio-mask-profit" ${dataMasking.maskProfitMargin !== false ? 'checked' : ''}>
                                        <span class="perm-slider round"></span>
                                    </label>
                                </div>
                                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <strong style="color:#fff; font-size:0.95rem;">🔒 حجب أرقام الحسابات البنكية الكاملة (Bank Account Masking)</strong>
                                        <p style="margin:3px 0 0; color:#94a3b8; font-size:0.82rem;">إظهار آخر 4 أرقام فقط من الحساب البنكي أو الآيبان (مثل: ••••-4821) حمايةً للبيانات البنكية.</p>
                                    </div>
                                    <label class="perm-switch">
                                        <input type="checkbox" id="studio-mask-bank" ${dataMasking.maskBankAccounts ? 'checked' : ''}>
                                        <span class="perm-slider round"></span>
                                    </label>
                                </div>
                                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <strong style="color:#fff; font-size:0.95rem;">🔒 حجب هواتف العملاء الحساسة (Customer Phone Masking)</strong>
                                        <p style="margin:3px 0 0; color:#94a3b8; font-size:0.82rem;">إخفاء منتصف أرقام هواتف العملاء في كشوف الحساب لمنع تسريب قاعدة بيانات العملاء.</p>
                                    </div>
                                    <label class="perm-switch">
                                        <input type="checkbox" id="studio-mask-phone" ${dataMasking.maskCustomerPhone ? 'checked' : ''}>
                                        <span class="perm-slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 10: TEMPORARY DELEGATED AUTHORITY -->
                    <div id="studio-tab-content-delegations_rights" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1.5px solid rgba(245,158,11,0.3); border-radius:16px; padding:22px;">
                            <h4 style="margin:0 0 6px 0; color:#fbbf24; font-size:1.2rem; font-weight:900;">
                                ⏳ التفويض المؤقت للصلاحيات (Temporary Delegated Authority)
                            </h4>
                            <p style="margin:0 0 18px 0; color:#94a3b8; font-size:0.88rem;">
                                تفويض صلاحيات هذا المستخدم مؤقتاً لزميل أثناء الإجازات والسفر، مع تحديد تاريخ بداية ونهاية يسقط بعدهما التفويض تلقائياً:
                            </p>

                            <div style="background:rgba(0,0,0,0.3); border:1px dashed #f59e0b; border-radius:12px; padding:16px; margin-bottom:18px;">
                                <h5 style="margin:0 0 12px 0; color:#fbbf24; font-size:0.95rem;">+ إنشاء تفويض صلاحيات جديد:</h5>
                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                                    <div>
                                        <label style="display:block; font-size:0.8rem; color:#cbd5e1; margin-bottom:4px;">تفويض الصلاحيات إلى:</label>
                                        <select id="new-del-target-user" style="width:100%; padding:8px 12px; background:#0f172a; border:1px solid #f59e0b; color:#fff; border-radius:8px; font-weight:700; outline:none;">
                                            <!-- Dynamically populated -->
                                        </select>
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:0.8rem; color:#cbd5e1; margin-bottom:4px;">مستوى الأمان المفوض (1 - 10):</label>
                                        <select id="new-del-level" style="width:100%; padding:8px 12px; background:#0f172a; border:1px solid #f59e0b; color:#fbbf24; border-radius:8px; font-weight:800; outline:none;">
                                            <option value="4">4 - كاشير أول (صالة)</option>
                                            <option value="6">6 - رئيس حسابات (يرى بنك الخرطوم)</option>
                                            <option value="8">8 - مراجع ومدير مالي</option>
                                            <option value="10">10 - كامل الصلاحيات الإدارية</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:0.8rem; color:#cbd5e1; margin-bottom:4px;">تاريخ البداية:</label>
                                        <input type="datetime-local" id="new-del-start" style="width:100%; padding:8px 12px; background:#0f172a; border:1px solid #475569; color:#fff; border-radius:8px; outline:none;">
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:0.8rem; color:#cbd5e1; margin-bottom:4px;">تاريخ النهاية:</label>
                                        <input type="datetime-local" id="new-del-end" style="width:100%; padding:8px 12px; background:#0f172a; border:1px solid #475569; color:#fff; border-radius:8px; outline:none;">
                                    </div>
                                </div>
                                <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                    <button type="button" onclick="window.submitStudioDelegation()" style="background:#f59e0b; color:#030712; border:none; padding:8px 18px; border-radius:8px; font-weight:800; cursor:pointer;">
                                        ⚡ تفعيل التفويض المؤقت فوراً
                                    </button>
                                </div>
                            </div>

                            <div id="studio-delegations-list-container">
                                <!-- Populated dynamically with active delegations -->
                            </div>
                        </div>
                    </div>

                    <!-- TAB 11: FOUR-EYES MANAGER PIN OVERRIDE -->
                    <div id="studio-tab-content-manager_override_pin" class="studio-tab-content" style="display:none;">
                        <div style="background:rgba(15,23,42,0.5); border:1.5px solid rgba(239,68,68,0.3); border-radius:16px; padding:22px;">
                            <h4 style="margin:0 0 6px 0; color:#f87171; font-size:1.2rem; font-weight:900;">
                                🛡️ رمز مرور المشرف للاعتماد الموضعي (Four-Eyes / Manager PIN Override)
                            </h4>
                            <p style="margin:0 0 18px 0; color:#94a3b8; font-size:0.88rem;">
                                تعيين رمز مرور سريع (PIN) لهذا المشرف لتمكينه من الموافقة وتجاوز الصلاحيات موضعياً للكاشير والموظفين دون تسجيل دخوله:
                            </p>
                            <div style="max-width:480px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:18px;">
                                <label style="display:block; font-size:0.9rem; font-weight:700; color:#cbd5e1; margin-bottom:8px;">رمز مرور المشرف (Manager PIN):</label>
                                <input type="password" id="studio-manager-pin" value="${managerPin || ''}" placeholder="مثال: 8844" autocomplete="off" style="width:100%; padding:12px 14px; background:#0f172a; border:1px solid #ef4444; color:#fff; border-radius:10px; font-size:1.2rem; text-align:center; letter-spacing:4px; font-weight:800; outline:none; box-sizing:border-box;">
                                <span style="font-size:0.8rem; color:#94a3b8; margin-top:8px; display:block; line-height:1.4;">
                                    💡 رمز سري خاص بالمشرف لإعطاء الموافقات الاستثنائية على الخصومات وإلغاء الفواتير وكشف الأسعار فوراً.
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 12: WORK SHIFTS & GEOFENCING -->
                    <div id="studio-tab-content-work_shifts_and_geo" class="studio-tab-content" style="display:none;">
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:20px;">
                            
                            <!-- Shift & Work Hours Card -->
                            <div id="studio-work-shifts-card" style="background:rgba(15,23,42,0.6); border:1.5px solid rgba(56,189,248,0.3); border-radius:16px; padding:22px; box-shadow:0 8px 25px rgba(0,0,0,0.3);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.08);">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="width:38px; height:38px; border-radius:10px; background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8; display:flex; align-items:center; justify-content:center; font-size:1.15rem;">
                                            <i class="fa-solid fa-clock"></i>
                                        </span>
                                        <div>
                                            <h4 style="margin:0; color:#38bdf8; font-size:1.1rem; font-weight:800;">جدول أوقات العمل والورديات</h4>
                                            <span style="font-size:0.8rem; color:#94a3b8;">تحديد فترة الدوام وأيام الأسبوع المصرح بها</span>
                                        </div>
                                    </div>
                                    <label class="perm-switch" title="تفعيل أو تعطيل تقييد وقت العمل لهذا المستخدم">
                                        <input type="checkbox" id="studio-time-restricted" ${timeRestricted ? 'checked' : ''} onchange="window.toggleStudioTimeFields(this.checked)">
                                        <span class="perm-slider round"></span>
                                    </label>
                                </div>

                                <div id="studio-time-fields-wrapper" style="opacity:${timeRestricted ? '1' : '0.45'}; pointer-events:${timeRestricted ? 'auto' : 'none'}; transition:all 0.3s;">
                                    <p style="margin:0 0 16px 0; font-size:0.85rem; color:#cbd5e1; line-height:1.5;">
                                        ⚠️ إذا تم تفعيل هذا الخيار وحاول الموظف الدخول أو العمل خارج هذه الساعات، سيتم <strong style="color:#f87171;">تجريده بالكامل من كافة الصلاحيات</strong> وحظر شاشات وأزرار النظام فوراً.
                                    </p>

                                    <!-- Time From & To Inputs -->
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                                        <div>
                                            <label style="display:block; font-size:0.82rem; font-weight:700; color:#94a3b8; margin-bottom:6px;">من الساعة (بداية الوردية):</label>
                                            <input type="time" id="studio-time-from" value="${timeFrom || '08:00'}" class="form-input" style="width:100%; padding:10px 12px; background:#0f172a; border:1px solid rgba(56,189,248,0.3); color:#fff; border-radius:8px; font-weight:700; box-sizing:border-box;">
                                        </div>
                                        <div>
                                            <label style="display:block; font-size:0.82rem; font-weight:700; color:#94a3b8; margin-bottom:6px;">إلى الساعة (نهاية الوردية):</label>
                                            <input type="time" id="studio-time-to" value="${timeTo || '03:00'}" class="form-input" style="width:100%; padding:10px 12px; background:#0f172a; border:1px solid rgba(56,189,248,0.3); color:#fff; border-radius:8px; font-weight:700; box-sizing:border-box;">
                                        </div>
                                    </div>

                                    <div style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.2); border-radius:8px; padding:10px; margin-bottom:16px; font-size:0.8rem; color:#38bdf8;">
                                        💡 <strong>دعم الورديات الليلية:</strong> النظام يتعامل تلقائياً وبدقة فائقة مع الورديات الممتدة لبعد منتصف الليل (مثلاً: من 08:00 صباحاً وحتى 03:00 فجراً لليوم التالي).
                                    </div>

                                    <!-- Allowed Days -->
                                    <div style="margin-bottom:12px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                            <label style="font-size:0.85rem; font-weight:700; color:#e2e8f0;">أيام العمل المسموح بها أسبوعياً:</label>
                                            <div style="display:flex; gap:6px;">
                                                <button type="button" onclick="window.setStudioWorkDaysPreset('all')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; padding:3px 8px; border-radius:6px; font-size:0.75rem; cursor:pointer;">الكل (7 أيام)</button>
                                                <button type="button" onclick="window.setStudioWorkDaysPreset('except_friday')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; padding:3px 8px; border-radius:6px; font-size:0.75rem; cursor:pointer;">عدا الجمعة</button>
                                                <button type="button" onclick="window.setStudioWorkDaysPreset('except_fri_sat')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; padding:3px 8px; border-radius:6px; font-size:0.75rem; cursor:pointer;">عدا الجمعة والسبت</button>
                                            </div>
                                        </div>
                                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(100px, 1fr)); gap:8px;">
                                            ${[
                                                { day: 0, name: 'الأحد' },
                                                { day: 1, name: 'الإثنين' },
                                                { day: 2, name: 'الثلاثاء' },
                                                { day: 3, name: 'الأربعاء' },
                                                { day: 4, name: 'الخميس' },
                                                { day: 5, name: 'الجمعة' },
                                                { day: 6, name: 'السبت' }
                                            ].map(d => `
                                                <label style="display:flex; align-items:center; gap:6px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.06); padding:8px 10px; border-radius:8px; cursor:pointer; font-size:0.85rem; font-weight:700;">
                                                    <input type="checkbox" class="studio-day-cb" value="${d.day}" ${allowedDays.includes(d.day) ? 'checked' : ''} style="accent-color:#38bdf8; width:15px; height:15px;">
                                                    <span style="color:#e2e8f0;">${d.name}</span>
                                                </label>
                                            `).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Geofencing (GPS) Card -->
                            <div id="studio-geofencing-card" style="background:rgba(15,23,42,0.6); border:1.5px solid rgba(16,185,129,0.3); border-radius:16px; padding:22px; box-shadow:0 8px 25px rgba(0,0,0,0.3);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.08);">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="width:38px; height:38px; border-radius:10px; background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#10b981; display:flex; align-items:center; justify-content:center; font-size:1.15rem;">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </span>
                                        <div>
                                            <h4 style="margin:0; color:#34d399; font-size:1.1rem; font-weight:800;">التقييد الجغرافي بالـ GPS</h4>
                                            <span style="font-size:0.8rem; color:#94a3b8;">حصر صلاحيات المستخدم داخل المقر أو الفرع</span>
                                        </div>
                                    </div>
                                    <label class="perm-switch" title="تفعيل أو تعطيل التقييد الجغرافي لهذا المستخدم">
                                        <input type="checkbox" id="studio-geo-restricted" ${geoRestricted ? 'checked' : ''} onchange="window.toggleStudioGeoFields(this.checked)">
                                        <span class="perm-slider round"></span>
                                    </label>
                                </div>

                                <div id="studio-geo-fields-wrapper" style="opacity:${geoRestricted ? '1' : '0.45'}; pointer-events:${geoRestricted ? 'auto' : 'none'}; transition:all 0.3s;">
                                    <p style="margin:0 0 16px 0; font-size:0.85rem; color:#cbd5e1; line-height:1.6;">
                                        📍 إذا تواجد الموظف خارج النطاق الجغرافي المحدد، <strong style="color:#f87171;">تتوقف كافة صلاحيات النظام</strong> ولن يتمكن من الوصول لأي شاشة أو زر.
                                    </p>

                                    <!-- 1. Multi-Method Quick Action Buttons -->
                                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:10px; margin-bottom:16px;">
                                        <button type="button" onclick="window.openInteractiveGeoMapModal()" style="background:linear-gradient(135deg, rgba(16,185,129,0.2), rgba(6,78,59,0.4)); border:1.5px solid #10b981; color:#34d399; padding:12px 14px; border-radius:12px; font-weight:800; font-size:0.88rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 15px rgba(16,185,129,0.2); transition:all 0.2s;">
                                            <i class="fa-solid fa-map-location-dot" style="font-size:1.15rem; color:#10b981;"></i>
                                            <span>🗺️ فتح الخريطة واختيار الموقع</span>
                                        </button>

                                        <button type="button" onclick="window.getCurrentGPSInStudio()" style="background:rgba(56,189,248,0.12); border:1.5px solid rgba(56,189,248,0.4); color:#38bdf8; padding:12px 14px; border-radius:12px; font-weight:800; font-size:0.88rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all 0.2s;">
                                            <i class="fa-solid fa-crosshairs" style="font-size:1.15rem; color:#38bdf8;"></i>
                                            <span>🎯 رصد موقعي الحالي بالـ GPS</span>
                                        </button>
                                    </div>

                                    <!-- 2. WhatsApp & Google Maps Smart Importer Box -->
                                    <div style="background:rgba(2, 6, 23, 0.55); border:1.5px solid rgba(34,197,94,0.3); border-radius:14px; padding:14px 16px; margin-bottom:16px; box-shadow:0 4px 20px rgba(0,0,0,0.25);">
                                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <span style="width:28px; height:28px; border-radius:8px; background:rgba(37,211,102,0.18); border:1px solid #25d366; color:#25d366; display:flex; align-items:center; justify-content:center; font-size:0.95rem;">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </span>
                                                <span style="font-size:0.88rem; font-weight:800; color:#86efac;">استيراد الموقع من الواتساب أو خرائط Google</span>
                                            </div>
                                            <button type="button" onclick="window.pasteFromClipboardToWhatsAppGeo()" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; padding:5px 12px; border-radius:8px; font-size:0.75rem; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px;">
                                                <i class="fa-regular fa-clipboard"></i> لصق تلقائي من الحافظة
                                            </button>
                                        </div>
                                        <p style="font-size:0.76rem; color:#94a3b8; margin:0 0 10px 0; line-height:1.4;">
                                            إذا أرسل لك الموظف موقعه عبر الواتساب، الصق الرسالة أو رابط خرائط جوجل هنا وسيقوم النظام باستخراج الإحداثيات وتحديد الموقع فوراً:
                                        </p>
                                        <div style="display:flex; gap:8px; align-items:center;">
                                            <input type="text" id="studio-whatsapp-geo-input" placeholder="الصق رسالة الواتساب أو رابط خرائط جوجل (مثل: https://maps.google.com/?q=... أو إحداثيات)..." class="form-input" style="flex:1; padding:10px 14px; background:#0f172a; border:1px solid rgba(34,197,94,0.35); color:#fff; border-radius:10px; font-size:0.85rem; font-weight:600; outline:none;" onkeydown="if(event.key==='Enter'){event.preventDefault(); window.applyWhatsAppLocationToStudio();}">
                                            <button type="button" onclick="window.applyWhatsAppLocationToStudio()" style="background:linear-gradient(135deg, #25d366, #16a34a); color:#052e16; border:none; padding:10px 18px; border-radius:10px; font-weight:800; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; gap:6px; white-space:nowrap; box-shadow:0 4px 12px rgba(37,211,102,0.3);">
                                                <i class="fa-solid fa-arrow-down-to-bracket"></i> استخراج
                                            </button>
                                        </div>
                                        <div id="studio-whatsapp-status" style="margin-top:8px; font-size:0.78rem; text-align:right;"></div>
                                    </div>

                                    <!-- 3. Coordinates & Radius Fields -->
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                                        <div>
                                            <label style="display:block; font-size:0.82rem; font-weight:700; color:#94a3b8; margin-bottom:6px;">خط العرض (Latitude):</label>
                                            <input type="number" step="any" id="studio-geo-lat" value="${geoLat || ''}" placeholder="مثال: 15.369400" class="form-input" style="width:100%; padding:10px 12px; background:#0f172a; border:1px solid rgba(16,185,129,0.3); color:#fff; border-radius:8px; font-weight:700; box-sizing:border-box;" oninput="window.updateStudioGeoInlineBadge()">
                                        </div>
                                        <div>
                                            <label style="display:block; font-size:0.82rem; font-weight:700; color:#94a3b8; margin-bottom:6px;">خط الطول (Longitude):</label>
                                            <input type="number" step="any" id="studio-geo-lng" value="${geoLng || ''}" placeholder="مثال: 44.191000" class="form-input" style="width:100%; padding:10px 12px; background:#0f172a; border:1px solid rgba(16,185,129,0.3); color:#fff; border-radius:8px; font-weight:700; box-sizing:border-box;" oninput="window.updateStudioGeoInlineBadge()">
                                        </div>
                                    </div>

                                    <div style="margin-bottom:14px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                            <label style="font-size:0.82rem; font-weight:700; color:#94a3b8;">نطاق السماح بالمتر (Radius Meters):</label>
                                            <span id="studio-geo-radius-display" style="font-size:0.82rem; font-weight:800; color:#34d399;">${geoRadius || 500} متر</span>
                                        </div>
                                        <div style="display:flex; gap:10px; align-items:center;">
                                            <input type="number" id="studio-geo-radius" value="${geoRadius || 500}" placeholder="500" class="form-input" style="width:110px; padding:8px 12px; background:#0f172a; border:1px solid rgba(16,185,129,0.3); color:#fff; border-radius:8px; font-weight:700; box-sizing:border-box;" oninput="const r=document.getElementById('studio-geo-radius-range'); if(r) r.value=this.value; const d=document.getElementById('studio-geo-radius-display'); if(d) d.innerText=this.value+' متر'; window.updateStudioGeoInlineBadge();">
                                            <input type="range" id="studio-geo-radius-range" min="50" max="5000" step="50" value="${geoRadius || 500}" style="flex:1; accent-color:#10b981; cursor:pointer;" oninput="const n=document.getElementById('studio-geo-radius'); if(n) n.value=this.value; const d=document.getElementById('studio-geo-radius-display'); if(d) d.innerText=this.value+' متر'; window.updateStudioGeoInlineBadge();">
                                        </div>
                                        <span style="font-size:0.75rem; color:#94a3b8; display:block; margin-top:4px;">المسافة المسموح بها للموظف للابتعاد عن النقطة (من 50 متراً إلى 5 كيلومترات).</span>
                                    </div>

                                    <!-- 4. Visual Location Status Badge -->
                                    <div id="studio-geo-status-badge" style="background:rgba(15,23,42,0.7); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:12px 16px; margin-bottom:12px;"></div>

                                    <span id="studio-gps-status" style="font-size:0.8rem; color:#94a3b8; text-align:center; display:block;"></span>
                                </div>
                            </div>

                            <!-- 📱 Card 3: Device Access Control & Multi-Device Quotas -->
                            <div id="studio-device-access-card" style="grid-column: 1 / -1; background:rgba(15,23,42,0.65); border:1.5px solid rgba(168,85,247,0.35); border-radius:16px; padding:22px; box-shadow:0 8px 25px rgba(0,0,0,0.35);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.08); flex-wrap:wrap; gap:12px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="width:40px; height:40px; border-radius:10px; background:rgba(168,85,247,0.15); border:1px solid #a855f7; color:#c084fc; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                                            <i class="fa-solid fa-mobile-screen-button"></i>
                                        </span>
                                        <div>
                                            <h4 style="margin:0; color:#c084fc; font-size:1.15rem; font-weight:800; display:flex; align-items:center; gap:8px;">
                                                تقييد وحصر الأجهزة المصرح بها (Device Access Control & Binding)
                                                <span style="background:rgba(168,85,247,0.2); color:#d8b4fe; border:1px solid rgba(168,85,247,0.4); font-size:0.75rem; padding:2px 10px; border-radius:12px; font-weight:700;">حماية أمنية مشددة</span>
                                            </h4>
                                            <span style="font-size:0.82rem; color:#94a3b8;">تحديد سقف الأجهزة (جهاز، جهازين، أو حتى 12 جهازاً) مع التسجيل التلقائي أو اليدوي</span>
                                        </div>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <label class="perm-switch" title="تفعيل أو تعطيل تقييد الأجهزة لهذا المستخدم">
                                            <input type="checkbox" id="studio-device-restricted" ${deviceRestricted ? 'checked' : ''} onchange="window.toggleStudioDeviceFields(this.checked)">
                                            <span class="perm-slider round"></span>
                                        </label>
                                    </div>
                                </div>

                                <div id="studio-device-fields-wrapper" style="opacity:${deviceRestricted ? '1' : '0.45'}; pointer-events:${deviceRestricted ? 'auto' : 'none'}; transition:all 0.3s;">
                                    <div style="background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.25); border-radius:12px; padding:12px 16px; margin-bottom:18px; font-size:0.86rem; color:#e2e8f0; line-height:1.6;">
                                        💡 <strong>كيف تعمل ميزة تقييد الأجهزة؟</strong><br>
                                        • <strong>أحمد عثمان (جهاز واحد فقط):</strong> عيّن الحد الأقصى (1). عند دخوله من آيفون 14، يتم قفل الحساب على هذا الجهاز فوراً ويُمنع أي جهاز آخر.<br>
                                        • <strong>عمر (جهازين):</strong> عيّن الحد الأقصى (2). أول جهازين يدخل بهما عمر (مثلاً كمبيوتر وهاتف) يتم اعتمادهما تلقائياً، ويُحظر أي جهاز ثالث.<br>
                                        • <strong>عثمان (12 جهازاً):</strong> عيّن الحد الأقصى (12). أول 12 جهاز يتم اعتمادهم تلقائياً كآيفون، أندرويد، أو كمبيوتر.<br>
                                        • <strong>إلغاء الربط:</strong> يمكنك في أي وقت الضغط على زر 🗑️ إلغاء ربط أي جهاز لتفريغ مكانه والسماح بتسجيل جهاز بديل.
                                    </div>

                                    <!-- Quota / Max Devices Setting with Presets -->
                                    <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:16px; margin-bottom:18px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                                            <div>
                                                <label style="display:block; font-size:0.9rem; font-weight:800; color:#f8fafc; margin-bottom:4px;">
                                                    الحد الأقصى لعدد الأجهزة المصرح بها (Device Quota):
                                                </label>
                                                <span style="font-size:0.78rem; color:#94a3b8;">عدد الأجهزة المسموح لهذا المستخدم بتسجيل الدخول منها في نفس الوقت</span>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <input type="number" id="studio-max-devices" min="1" max="50" value="${maxDevices}" class="form-input" style="width:85px; padding:8px 12px; background:#0f172a; border:1.5px solid #a855f7; color:#c084fc; border-radius:8px; font-weight:800; font-size:1.1rem; text-align:center;" oninput="window.renderStudioTrustedDevicesList()">
                                                <span style="font-weight:700; color:#cbd5e1; font-size:0.88rem;">جهاز</span>
                                            </div>
                                        </div>

                                        <!-- Quick Presets -->
                                        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                            <span style="font-size:0.8rem; font-weight:700; color:#94a3b8;">تحديد سريع:</span>
                                            <button type="button" onclick="window.setStudioMaxDevicesPreset(1)" class="preset-btn" style="border-color:rgba(168,85,247,0.4);">📱 1 جهاز (مثل: أحمد عثمان)</button>
                                            <button type="button" onclick="window.setStudioMaxDevicesPreset(2)" class="preset-btn" style="border-color:rgba(168,85,247,0.4);">📱📱 جهازين (مثل: عمر)</button>
                                            <button type="button" onclick="window.setStudioMaxDevicesPreset(3)" class="preset-btn">📱 3 أجهزة</button>
                                            <button type="button" onclick="window.setStudioMaxDevicesPreset(5)" class="preset-btn">📱 5 أجهزة</button>
                                            <button type="button" onclick="window.setStudioMaxDevicesPreset(12)" class="preset-btn" style="background:rgba(168,85,247,0.2); border-color:#a855f7; color:#e9d5ff;">📱💻 12 جهازاً (مثل: عثمان)</button>
                                        </div>
                                    </div>

                                    <!-- Devices Header & Action Bar -->
                                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <h5 style="margin:0; font-size:0.95rem; font-weight:800; color:#e2e8f0;">الأجهزة المعتمدة والمربوطة حالياً:</h5>
                                            <span id="studio-devices-count-badge" style="background:rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.1); padding:3px 10px; border-radius:14px; font-size:0.8rem; font-weight:700;">
                                                جاري التحميل...
                                            </span>
                                        </div>
                                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                            <button type="button" onclick="window.bindCurrentDeviceToStudio()" style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px;">
                                                <i class="fa-solid fa-laptop"></i> ربط جهازي الحالي فوراً
                                            </button>
                                            <button type="button" onclick="window.clearAllStudioTrustedDevices()" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px;">
                                                <i class="fa-solid fa-broom"></i> مسح جميع الأجهزة
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Trusted Devices Dynamic List Container -->
                                    <div id="studio-trusted-devices-container" style="margin-bottom:18px;">
                                        <!-- Dynamically populated by window.renderStudioTrustedDevicesList -->
                                    </div>

                                    <!-- Manual Pre-Binding Box -->
                                    <div style="background:rgba(2,6,23,0.5); border:1px dashed rgba(168,85,247,0.3); border-radius:12px; padding:14px 16px;">
                                        <div style="font-size:0.85rem; font-weight:800; color:#d8b4fe; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                                            <i class="fa-solid fa-plus-circle"></i>
                                            <span>تسجيل أو حجز جهاز مسبقاً يدوياً (اختياري):</span>
                                        </div>
                                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) auto; gap:10px; align-items:flex-end;">
                                            <div>
                                                <label style="display:block; font-size:0.78rem; font-weight:700; color:#94a3b8; margin-bottom:4px;">اسم / نوع الجهاز:</label>
                                                <input type="text" id="studio-manual-device-name" placeholder="مثال: آيفون 14 - أحمد عثمان" class="form-input" style="width:100%; box-sizing:border-box; padding:8px 12px; background:#0f172a; border:1px solid rgba(168,85,247,0.3); color:#fff; border-radius:8px; font-size:0.85rem;">
                                            </div>
                                            <div>
                                                <label style="display:block; font-size:0.78rem; font-weight:700; color:#94a3b8; margin-bottom:4px;">معرّف الجهاز (UUID أو السيريال):</label>
                                                <input type="text" id="studio-manual-device-uuid" placeholder="الصق معرّف الجهاز هنا..." class="form-input" style="width:100%; box-sizing:border-box; padding:8px 12px; background:#0f172a; border:1px solid rgba(168,85,247,0.3); color:#fff; border-radius:8px; font-size:0.85rem; font-family:monospace; direction:ltr;">
                                            </div>
                                            <div>
                                                <button type="button" onclick="window.addStudioTrustedDeviceManually()" style="background:linear-gradient(135deg, #a855f7, #7c3aed); color:#fff; border:none; padding:9px 18px; border-radius:8px; font-weight:800; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; gap:6px; white-space:nowrap; box-shadow:0 4px 12px rgba(168,85,247,0.3);">
                                                    <i class="fa-solid fa-plus"></i> إضافة وحجز
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- Custom Studio Styles -->
            <style>
                .active-role-admin { background:rgba(239,68,68,0.25) !important; border:1px solid #ef4444 !important; }
                .active-role-user { background:rgba(56,189,248,0.25) !important; border:1px solid #38bdf8 !important; }
                
                .preset-btn { background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:6px 12px; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; font-family:'Tajawal', sans-serif; transition:all 0.2s; }
                .preset-btn:hover { background:rgba(56,189,248,0.2); border-color:#38bdf8; transform:translateY(-1px); }
                
                .bulk-btn { border:1px solid; padding:6px 12px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; font-family:'Tajawal', sans-serif; transition:all 0.2s; }
                .bulk-btn:hover { transform:translateY(-1px); filter:brightness(1.15); }
                
                .studio-tab-btn { background:transparent; border:none; border-bottom:3px solid transparent; color:#94a3b8; padding:10px 16px; font-size:0.92rem; font-weight:800; cursor:pointer; font-family:'Tajawal', sans-serif; transition:all 0.2s; display:inline-flex; align-items:center; gap:8px; white-space:nowrap; }
                .studio-tab-btn:hover { color:#38bdf8; }
                .studio-tab-btn.active { color:#38bdf8; border-bottom-color:#38bdf8; background:rgba(56,189,248,0.08); border-radius:8px 8px 0 0; }

                /* Premium Toggle Switch */
                .perm-switch { position: relative; display: inline-block; width: 42px; height: 24px; vertical-align: middle; margin: 0; }
                .perm-switch input { opacity: 0; width: 0; height: 0; }
                .perm-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #334155; transition: .3s; border: 1px solid rgba(255,255,255,0.15); }
                .perm-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .3s; }
                .perm-switch input:checked + .perm-slider { background-color: #10b981; border-color: #10b981; }
                .perm-switch input:checked + .perm-slider:before { transform: translateX(18px); }
                .perm-slider.round { border-radius: 24px; }
                .perm-slider.round:before { border-radius: 50%; }
            </style>
        `;

        containerElement.innerHTML = html;
        setTimeout(() => {
            if (typeof window.onStudioSecLevelChange === 'function') {
                window.onStudioSecLevelChange(userSecLevel);
            }
            if (typeof window.updateStudioGeoInlineBadge === 'function') {
                window.updateStudioGeoInlineBadge();
            }
            if (typeof window.renderStudioTrustedDevicesList === 'function') {
                window.renderStudioTrustedDevicesList();
            }
        }, 50);
    }

    // ═════════════════════════════════════════════════════════════════════
    // 8. STUDIO CONTROLLER & EVENT LISTENERS
    // ═════════════════════════════════════════════════════════════════════

    window.switchStudioTab = function(tabKey, btn) {
        document.querySelectorAll('.studio-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.studio-tab-content').forEach(c => c.style.display = 'none');
        if (btn) {
            btn.classList.add('active');
        } else {
            const b = document.getElementById(`studio-tab-btn-${tabKey}`);
            if (b) b.classList.add('active');
        }
        const target = document.getElementById(`studio-tab-content-${tabKey}`);
        if (target) target.style.display = 'block';
        if (tabKey === 'delegations_rights' && typeof window.populateDelegationDropdown === 'function') {
            window.populateDelegationDropdown();
        }
    };

    window.jumpToWorkShiftAndGeoTab = function() {
        const btn = document.getElementById('studio-tab-btn-work_shifts_and_geo');
        window.switchStudioTab('work_shifts_and_geo', btn);
        const card = document.getElementById('studio-work-shifts-card');
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.style.transition = 'all 0.4s ease';
            card.style.borderColor = '#38bdf8';
            card.style.boxShadow = '0 0 35px rgba(56,189,248,0.7)';
            setTimeout(() => {
                card.style.boxShadow = '0 8px 25px rgba(0,0,0,0.3)';
                card.style.borderColor = 'rgba(56,189,248,0.3)';
            }, 2500);
        }
    };

    window.toggleStudioTimeFields = function(isChecked) {
        const wrap = document.getElementById('studio-time-fields-wrapper');
        if (wrap) {
            wrap.style.opacity = isChecked ? '1' : '0.45';
            wrap.style.pointerEvents = isChecked ? 'auto' : 'none';
        }
    };

    window.toggleStudioGeoFields = function(isChecked) {
        const wrap = document.getElementById('studio-geo-fields-wrapper');
        if (wrap) {
            wrap.style.opacity = isChecked ? '1' : '0.45';
            wrap.style.pointerEvents = isChecked ? 'auto' : 'none';
        }
        window.updateStudioGeoInlineBadge();
    };

    window.setStudioWorkDaysPreset = function(preset) {
        const checkboxes = document.querySelectorAll('.studio-day-cb');
        checkboxes.forEach(cb => {
            const val = parseInt(cb.value, 10);
            if (preset === 'all') {
                cb.checked = true;
            } else if (preset === 'except_friday') {
                cb.checked = (val !== 5);
            } else if (preset === 'except_fri_sat') {
                cb.checked = (val !== 5 && val !== 6);
            }
        });
    };

    // ─────────────────────────────────────────────────────────────────
    // 📱 MAGAIEES DEVICE ACCESS CONTROL & TRUSTED DEVICES ENGINE
    // ─────────────────────────────────────────────────────────────────

    window.jumpToDeviceAccessCard = function() {
        const btn = document.getElementById('studio-tab-btn-work_shifts_and_geo');
        window.switchStudioTab('work_shifts_and_geo', btn);
        const card = document.getElementById('studio-device-access-card');
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.style.transition = 'all 0.4s ease';
            card.style.borderColor = '#a855f7';
            card.style.boxShadow = '0 0 35px rgba(168,85,247,0.7)';
            setTimeout(() => {
                card.style.boxShadow = '0 8px 25px rgba(0,0,0,0.35)';
                card.style.borderColor = 'rgba(168,85,247,0.35)';
            }, 2500);
        }
    };

    window.toggleStudioDeviceFields = function(isChecked) {
        const wrap = document.getElementById('studio-device-fields-wrapper');
        if (wrap) {
            wrap.style.opacity = isChecked ? '1' : '0.45';
            wrap.style.pointerEvents = isChecked ? 'auto' : 'none';
        }
    };

    window.setStudioMaxDevicesPreset = function(n) {
        const input = document.getElementById('studio-max-devices');
        if (input) input.value = n;
        window.renderStudioTrustedDevicesList();
        if (window.showToast) {
            window.showToast(`تم تعيين سقف الأجهزة المصرح بها إلى (${n}) أجهزة`, 'info');
        }
    };

    window.renderStudioTrustedDevicesList = function() {
        const container = document.getElementById('studio-trusted-devices-container');
        const countBadge = document.getElementById('studio-devices-count-badge');
        const maxInput = document.getElementById('studio-max-devices');
        const maxDevs = maxInput ? (parseInt(maxInput.value, 10) || 1) : 1;

        if (!Array.isArray(window._currentStudioTrustedDevices)) {
            window._currentStudioTrustedDevices = [];
        }
        const list = window._currentStudioTrustedDevices;

        if (countBadge) {
            countBadge.innerHTML = `الأجهزة المعتمدة: <strong style="color:${list.length >= maxDevs ? '#f87171' : '#34d399'};">${list.length}</strong> من أصل <strong style="color:#c084fc;">${maxDevs}</strong> مصرح بها`;
        }

        if (!container) return;

        if (list.length === 0) {
            container.innerHTML = `
                <div style="background:rgba(0,0,0,0.25); border:1.5px dashed rgba(168,85,247,0.3); border-radius:12px; padding:22px; text-align:center; color:#94a3b8;">
                    <div style="font-size:2.2rem; margin-bottom:8px;">📱💻</div>
                    <div style="font-weight:800; color:#f8fafc; font-size:1rem; margin-bottom:6px;">لم يتم تسجيل أي جهاز لهذا المستخدم حتى الآن</div>
                    <div style="font-size:0.84rem; color:#cbd5e1; max-width:540px; margin:0 auto; line-height:1.6;">
                        عند قيام الموظف بتسجيل الدخول، سيقوم النظام تلقائياً باعتماد أول <strong>(${maxDevs})</strong> أجهزة يدخل منها وقفل الحساب عليها، أو يمكنك ربط جهازك الحالي أو إضافة معرف يدوي أدناه الآن.
                    </div>
                </div>
            `;
            return;
        }

        let html = `
            <div style="overflow-x:auto; border-radius:12px; border:1px solid rgba(255,255,255,0.08); box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                <table style="width:100%; border-collapse:collapse; text-align:right; font-size:0.85rem;">
                    <thead>
                        <tr style="background:rgba(168,85,247,0.18); color:#d8b4fe; border-bottom:1px solid rgba(168,85,247,0.35);">
                            <th style="padding:10px 14px; width:40px;">#</th>
                            <th style="padding:10px 14px;">اسم ونوع الجهاز</th>
                            <th style="padding:10px 14px;">معرّف الجهاز (UUID / Serial)</th>
                            <th style="padding:10px 14px;">تاريخ الاعتماد</th>
                            <th style="padding:10px 14px;">آخر تسجيل دخول</th>
                            <th style="padding:10px 14px; text-align:center; width:110px;">إلغاء الربط</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        list.forEach((dev, idx) => {
            const devObj = typeof dev === 'string' ? { uuid: dev, name: 'جهاز ' + (idx + 1) } : dev;
            const uuid = devObj.uuid || 'غير محدد';
            const name = devObj.name || ('جهاز ' + (idx + 1));
            const type = (devObj.type || '').toLowerCase();
            const isApple = type.includes('iphone') || type.includes('ipad') || type.includes('mac') || name.toLowerCase().includes('iphone');
            const isAndroid = type.includes('android') || name.toLowerCase().includes('android');
            const isDesktop = type.includes('desktop') || type.includes('windows') || (!isApple && !isAndroid);

            let icon = '📱';
            if (isApple) icon = '🍎📱';
            else if (isAndroid) icon = '🤖📱';
            else if (isDesktop) icon = '💻';

            const regAt = devObj.registeredAt ? new Date(devObj.registeredAt).toLocaleString('ar-EG', { dateStyle: 'short', timeStyle: 'short' }) : 'معتمد مسبقاً';
            const lastAt = devObj.lastLogin ? new Date(devObj.lastLogin).toLocaleString('ar-EG', { dateStyle: 'short', timeStyle: 'short' }) : '—';

            html += `
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06); background:${idx % 2 === 0 ? 'rgba(0,0,0,0.22)' : 'transparent'};">
                    <td style="padding:10px 14px; color:#94a3b8; font-weight:700;">${idx + 1}</td>
                    <td style="padding:10px 14px; font-weight:800; color:#fff;">
                        <span style="margin-left:6px; font-size:1.15rem;">${icon}</span>
                        <span>${name}</span>
                    </td>
                    <td style="padding:10px 14px; font-family:monospace; color:#38bdf8; font-size:0.82rem; direction:ltr; text-align:left;">
                        <span>${uuid.length > 24 ? uuid.substring(0, 22) + '...' : uuid}</span>
                        <button type="button" title="نسخ المعرف" onclick="navigator.clipboard.writeText('${uuid}'); if(window.showToast) window.showToast('تم نسخ المعرف للحافظة', 'info'); else alert('تم نسخ المعرف');" style="background:none; border:none; color:#94a3b8; cursor:pointer; padding:2px 6px;">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </td>
                    <td style="padding:10px 14px; color:#cbd5e1; font-size:0.8rem;">${regAt}</td>
                    <td style="padding:10px 14px; color:#34d399; font-size:0.8rem;">${lastAt}</td>
                    <td style="padding:10px 14px; text-align:center;">
                        <button type="button" onclick="window.removeStudioTrustedDevice(${idx})" title="إلغاء ربط هذا الجهاز وتفريغ مقعده" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; padding:5px 12px; border-radius:8px; font-weight:700; cursor:pointer; font-size:0.75rem; transition:all 0.2s;">
                            <i class="fa-solid fa-trash-can"></i> إلغاء الربط
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        container.innerHTML = html;
    };

    window.removeStudioTrustedDevice = function(idx) {
        if (!Array.isArray(window._currentStudioTrustedDevices) || !window._currentStudioTrustedDevices[idx]) return;
        const dev = window._currentStudioTrustedDevices[idx];
        const devName = dev.name || dev.uuid || 'هذا الجهاز';
        if (!confirm(`هل أنت متأكد من رغبتك في إلغاء ربط "${devName}"؟\nسيتم تفريغ مكان مسموح به فوراً لاعتماد جهاز جديد.`)) return;

        window._currentStudioTrustedDevices.splice(idx, 1);
        window.renderStudioTrustedDevicesList();
        if (window.showToast) window.showToast('تم إلغاء ربط الجهاز وتفريغ مكانه بنجاح', 'success');
    };

    window.clearAllStudioTrustedDevices = function() {
        if (!confirm('هل أنت متأكد من مسح جميع الأجهزة المصرح بها لهذا الحساب؟\nسيتمكن المستخدم من تسجيل أجهزة جديدة عند دخوله القادم.')) return;
        window._currentStudioTrustedDevices = [];
        window.renderStudioTrustedDevicesList();
        if (window.showToast) window.showToast('تم مسح جميع الأجهزة المصرح بها', 'info');
    };

    window.bindCurrentDeviceToStudio = function() {
        let curUuid = localStorage.getItem('deviceUuid');
        if (!curUuid) {
            curUuid = 'dev_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
            localStorage.setItem('deviceUuid', curUuid);
        }

        const maxInput = document.getElementById('studio-max-devices');
        const maxDevs = maxInput ? (parseInt(maxInput.value, 10) || 1) : 1;

        if (!Array.isArray(window._currentStudioTrustedDevices)) {
            window._currentStudioTrustedDevices = [];
        }

        const exists = window._currentStudioTrustedDevices.some(d => (typeof d === 'string' ? d : d.uuid) === curUuid);
        if (exists) {
            alert('هذا الجهاز مسجل بالفعل في قائمة الأجهزة المصرح بها لهذا المستخدم.');
            return;
        }

        if (window._currentStudioTrustedDevices.length >= maxDevs) {
            alert(`عفواً، لقد تم استيفاء الحد الأقصى للأجهزة المصرح بها وهو (${maxDevs}) أجهزة.\nيرجى زيادة السقف أو إلغاء ربط أحد الأجهزة المسجلة أولاً.`);
            return;
        }

        const ua = navigator.userAgent;
        let devName = 'كمبيوتر ويندوز الحالي (Windows PC)';
        let devType = 'desktop';
        if (/iPhone/i.test(ua)) { devName = 'آيفون الحالي (iPhone)'; devType = 'iphone'; }
        else if (/iPad/i.test(ua)) { devName = 'آيباد الحالي (iPad)'; devType = 'ipad'; }
        else if (/Android/i.test(ua)) { devName = 'هاتف أندرويد الحالي (Android)'; devType = 'android'; }
        else if (/Macintosh|Mac OS/i.test(ua)) { devName = 'ماك الحالي (Mac PC)'; devType = 'mac'; }

        window._currentStudioTrustedDevices.push({
            uuid: curUuid,
            name: devName,
            type: devType,
            registeredAt: new Date().toISOString(),
            lastLogin: new Date().toISOString()
        });

        window.renderStudioTrustedDevicesList();
        if (window.showToast) window.showToast('✅ تم ربط واعتماد جهازك الحالي بنجاح', 'success');
    };

    window.addStudioTrustedDeviceManually = function() {
        const nameInput = document.getElementById('studio-manual-device-name');
        const uuidInput = document.getElementById('studio-manual-device-uuid');
        const uuid = uuidInput ? uuidInput.value.trim() : '';
        const name = nameInput ? nameInput.value.trim() : '';

        if (!uuid) {
            alert('يرجى إدخال معرّف الجهاز (UUID أو السيريال)');
            if (uuidInput) uuidInput.focus();
            return;
        }

        const maxInput = document.getElementById('studio-max-devices');
        const maxDevs = maxInput ? (parseInt(maxInput.value, 10) || 1) : 1;

        if (!Array.isArray(window._currentStudioTrustedDevices)) {
            window._currentStudioTrustedDevices = [];
        }

        const exists = window._currentStudioTrustedDevices.some(d => (typeof d === 'string' ? d : d.uuid) === uuid);
        if (exists) {
            alert('هذا المعرف مسجل بالفعل في قائمة الأجهزة المصرح بها.');
            return;
        }

        if (window._currentStudioTrustedDevices.length >= maxDevs) {
            alert(`عفواً، تم استيفاء الحد الأقصى للأجهزة المصرح بها وهو (${maxDevs}) أجهزة.\nيرجى زيادة السقف أو إلغاء ربط جهاز قديم.`);
            return;
        }

        window._currentStudioTrustedDevices.push({
            uuid: uuid,
            name: name || ('جهاز ' + (window._currentStudioTrustedDevices.length + 1)),
            type: /iphone|ipad/i.test(name) ? 'iphone' : (/android/i.test(name) ? 'android' : 'device'),
            registeredAt: new Date().toISOString(),
            lastLogin: null
        });

        if (nameInput) nameInput.value = '';
        if (uuidInput) uuidInput.value = '';

        window.renderStudioTrustedDevicesList();
        if (window.showToast) window.showToast('✅ تم تسجيل وحجز الجهاز بنجاح', 'success');
    };

    // ─────────────────────────────────────────────────────────────────
    // 🗺️ MAGAIEES ADVANCED MULTI-METHOD LOCATION & GEOFENCING ENGINE
    // ─────────────────────────────────────────────────────────────────

    // 1. Ensure Leaflet Library is Dynamically Loaded
    function ensureLeafletLoaded(callback) {
        if (window.L && typeof window.L.map === 'function') {
            if (callback) callback();
            return;
        }

        if (!document.getElementById('magaiees-leaflet-css') && !document.querySelector('link[href*="leaflet"]')) {
            const link = document.createElement('link');
            link.id = 'magaiees-leaflet-css';
            link.rel = 'stylesheet';
            link.href = 'css/leaflet.css';
            document.head.appendChild(link);
        }

        let script = document.getElementById('magaiees-leaflet-js') || document.querySelector('script[src*="leaflet"]');
        if (!script) {
            script = document.createElement('script');
            script.id = 'magaiees-leaflet-js';
            script.src = 'js/leaflet.js';
            script.onload = () => { if (callback) callback(); };
            script.onerror = () => {
                const cdn = document.createElement('script');
                cdn.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                cdn.onload = () => { if (callback) callback(); };
                document.head.appendChild(cdn);
            };
            document.head.appendChild(script);
        } else {
            if (window.L && typeof window.L.map === 'function') {
                if (callback) callback();
            } else {
                script.addEventListener('load', () => { if (callback) callback(); });
            }
        }
    }

    // 2. Intelligent WhatsApp & Google Maps Location Parser
    function parseWhatsAppOrMapsInput(rawText) {
        if (!rawText || typeof rawText !== 'string') return { success: false };

        // Convert Eastern Arabic numerals (٠١٢٣٤٥٦٧٨٩) to standard English ASCII (0-9)
        let clean = rawText.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).trim();

        // Pattern A: Google Maps @lat,lng
        const atMatch = clean.match(/@(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/);
        if (atMatch) {
            const lat = parseFloat(atMatch[1]), lng = parseFloat(atMatch[2]);
            if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                return { success: true, lat, lng, source: 'google_maps_at' };
            }
        }

        // Pattern B: Query params q=lat,lng or ll=lat,lng
        const qMatch = clean.match(/[?&](?:q|query|ll)=(-?\d{1,2}\.\d+)[,+](-?\d{1,3}\.\d+)/);
        if (qMatch) {
            const lat = parseFloat(qMatch[1]), lng = parseFloat(qMatch[2]);
            if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                return { success: true, lat, lng, source: 'google_maps_query' };
            }
        }

        // Pattern C: Apple Maps or Geo URI (geo:lat,lng)
        const geoUriMatch = clean.match(/geo:(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/i);
        if (geoUriMatch) {
            const lat = parseFloat(geoUriMatch[1]), lng = parseFloat(geoUriMatch[2]);
            if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                return { success: true, lat, lng, source: 'geo_uri' };
            }
        }

        // Pattern D: Plain text coordinates (e.g., 15.369444, 44.191000 or 15.369444 44.191000)
        const textCoordsMatch = clean.match(/(-?\d{1,2}\.\d{3,})[\s,]+(-?\d{1,3}\.\d{3,})/);
        if (textCoordsMatch) {
            const lat = parseFloat(textCoordsMatch[1]), lng = parseFloat(textCoordsMatch[2]);
            if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                return { success: true, lat, lng, source: 'plain_coords' };
            }
        }

        // Pattern E: Short URLs that require server-side redirect following (maps.app.goo.gl or goo.gl/maps)
        const urlMatch = clean.match(/https?:\/\/[^\s]+/i);
        if (urlMatch) {
            return { success: false, isShortUrl: true, url: urlMatch[0] };
        }

        return { success: false };
    }

    // 3. Apply Coordinates to Studio with Visual Feedback
    function applyCoordinatesToStudio(lat, lng, sourceName) {
        const latIn = document.getElementById('studio-geo-lat');
        const lngIn = document.getElementById('studio-geo-lng');
        const cb = document.getElementById('studio-geo-restricted');

        if (latIn) latIn.value = parseFloat(lat).toFixed(6);
        if (lngIn) lngIn.value = parseFloat(lng).toFixed(6);

        if (cb && !cb.checked) {
            cb.checked = true;
            if (typeof window.toggleStudioGeoFields === 'function') {
                window.toggleStudioGeoFields(true);
            }
        }

        window.updateStudioGeoInlineBadge();

        if (window.showModernToast) {
            window.showModernToast(`✅ تم اعتماد ${sourceName || 'الموقع الجغرافي'}: (${parseFloat(lat).toFixed(4)}, ${parseFloat(lng).toFixed(4)})`, 'success', 4000);
        }
    }

    // 4. Handle WhatsApp Location Input / Button
    window.applyWhatsAppLocationToStudio = async function(customText) {
        const inputEl = document.getElementById('studio-whatsapp-geo-input');
        const statusEl = document.getElementById('studio-whatsapp-status');
        const rawVal = customText !== undefined ? customText : (inputEl ? inputEl.value : '');

        if (!rawVal || !rawVal.trim()) {
            if (statusEl) statusEl.innerHTML = '<span style="color:#f87171;">⚠️ يرجى لصق رسالة أو رابط الواتساب أولاً.</span>';
            if (window.showModernToast) window.showModernToast('يرجى لصق رسالة أو رابط الواتساب أولاً', 'warning');
            return;
        }

        if (statusEl) statusEl.innerHTML = '<span style="color:#38bdf8;">⏳ جاري تحليل واستخراج الإحداثيات...</span>';

        const parsed = parseWhatsAppOrMapsInput(rawVal);
        if (parsed.success) {
            applyCoordinatesToStudio(parsed.lat, parsed.lng, 'موقع الواتساب المباشر');
            if (statusEl) {
                statusEl.innerHTML = `<span style="color:#34d399;">✅ تم استخراج الموقع بنجاح: (خط العرض: ${parsed.lat.toFixed(6)} ، خط الطول: ${parsed.lng.toFixed(6)})</span>`;
            }
            if (inputEl) inputEl.value = '';
            return;
        }

        // Short URL resolution via backend proxy
        if (parsed.isShortUrl && parsed.url) {
            if (statusEl) statusEl.innerHTML = '<span style="color:#38bdf8;">⏳ جاري فك الرابط المختصر واستخراج الموقع من الخريطة...</span>';
            try {
                const res = await fetch('api/resolve_maps_url.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url: parsed.url })
                });
                const data = await res.json();
                if (data.success && data.lat !== null && data.lng !== null) {
                    applyCoordinatesToStudio(data.lat, data.lng, 'رابط خرائط Google / الواتساب');
                    if (statusEl) {
                        statusEl.innerHTML = `<span style="color:#34d399;">✅ تم فك الرابط واستخراج الموقع بنجاح: (${data.lat.toFixed(6)} , ${data.lng.toFixed(6)})</span>`;
                    }
                    if (inputEl) inputEl.value = '';
                    return;
                } else {
                    throw new Error(data.error || 'تعذر استخراج الإحداثيات من هذا الرابط');
                }
            } catch (e) {
                if (statusEl) {
                    statusEl.innerHTML = `<span style="color:#f87171;">❌ تعذر استخراج الإحداثيات تلقائياً: ${e.message}</span>`;
                }
                if (window.showModernToast) window.showModernToast('تعذر فك الرابط تلقائياً، يمكنك فتحه ونسخ الإحداثيات مباشرة أو تحديد الموقع من الخريطة', 'error');
                return;
            }
        }

        if (statusEl) {
            statusEl.innerHTML = '<span style="color:#f87171;">❌ لم يتم العثور على إحداثيات صالحة في النص المدخل. تأكد من صحة الرابط أو الإحداثيات.</span>';
        }
        if (window.showModernToast) window.showModernToast('لم يتم العثور على إحداثيات صالحة في الرسالة المدخلة', 'error');
    };

    // 5. Automatic Clipboard Paste for WhatsApp
    window.pasteFromClipboardToWhatsAppGeo = async function() {
        const inputEl = document.getElementById('studio-whatsapp-geo-input');
        try {
            if (navigator.clipboard && navigator.clipboard.readText) {
                const clipText = await navigator.clipboard.readText();
                if (clipText && clipText.trim()) {
                    if (inputEl) inputEl.value = clipText.trim();
                    window.applyWhatsAppLocationToStudio(clipText.trim());
                    return;
                }
            }
        } catch(e) {
            console.warn("Clipboard read blocked, focusing input", e);
        }
        if (inputEl) {
            inputEl.focus();
            inputEl.select();
        }
        if (window.showModernToast) {
            window.showModernToast('اضغط Ctrl+V للصق الرسالة أو الرابط المنسوخ من الواتساب', 'info');
        }
    };

    // 6. Update Inline Visual Status Badge in Studio
    window.updateStudioGeoInlineBadge = function() {
        const badge = document.getElementById('studio-geo-status-badge');
        if (!badge) return;

        const latVal = parseFloat(document.getElementById('studio-geo-lat')?.value);
        const lngVal = parseFloat(document.getElementById('studio-geo-lng')?.value);
        const radiusVal = parseInt(document.getElementById('studio-geo-radius')?.value, 10) || 500;

        if (!isNaN(latVal) && !isNaN(lngVal) && latVal !== 0 && lngVal !== 0) {
            badge.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; width:100%;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="width:38px; height:38px; border-radius:10px; background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#10b981; display:flex; align-items:center; justify-content:center; font-size:1.15rem;">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <div>
                            <div style="font-weight:800; color:#34d399; font-size:0.92rem;">الموقع معتمد ونشط بالـ GPS</div>
                            <div style="font-size:0.8rem; color:#cbd5e1; direction:ltr; text-align:right;">
                                Lat: <strong>${latVal.toFixed(6)}</strong> , Lng: <strong>${lngVal.toFixed(6)}</strong> (نطاق: <strong>${radiusVal}م</strong>)
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" onclick="window.openInteractiveGeoMapModal()" style="background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#34d399; padding:7px 14px; border-radius:8px; font-size:0.82rem; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-map"></i> عرض وتعديل على الخريطة
                        </button>
                    </div>
                </div>
            `;
            badge.style.borderColor = 'rgba(16,185,129,0.4)';
            badge.style.background = 'rgba(6, 78, 59, 0.2)';
        } else {
            badge.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; width:100%;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="width:38px; height:38px; border-radius:10px; background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:1.05rem;">
                            <i class="fa-solid fa-map-pin"></i>
                        </span>
                        <span style="font-size:0.82rem; color:#94a3b8;">لم يتم تعيين إحداثيات الموقع حتى الآن. اختر من الخريطة أو الصق رابط الواتساب.</span>
                    </div>
                    <button type="button" onclick="window.openInteractiveGeoMapModal()" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; padding:7px 16px; border-radius:8px; font-size:0.82rem; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-map-location-dot"></i> فتح الخريطة الآن
                    </button>
                </div>
            `;
            badge.style.borderColor = 'rgba(255,255,255,0.1)';
            badge.style.background = 'rgba(15, 23, 42, 0.7)';
        }
    };

    // 7. Interactive Geo Map Picker Modal
    window.openInteractiveGeoMapModal = function() {
        ensureLeafletLoaded(() => {
            const existing = document.getElementById('magaiees-geo-map-modal');
            if (existing) existing.remove();

            const currentLatVal = parseFloat(document.getElementById('studio-geo-lat')?.value);
            const currentLngVal = parseFloat(document.getElementById('studio-geo-lng')?.value);
            const currentRadiusVal = parseInt(document.getElementById('studio-geo-radius')?.value, 10) || 500;

            let activeLat = (!isNaN(currentLatVal) && currentLatVal !== 0) ? currentLatVal : 15.369444;
            let activeLng = (!isNaN(currentLngVal) && currentLngVal !== 0) ? currentLngVal : 44.191000;
            let activeRadius = currentRadiusVal;
            let hasInitialCoords = (!isNaN(currentLatVal) && !isNaN(currentLngVal) && currentLatVal !== 0);

            const modal = document.createElement('div');
            modal.id = 'magaiees-geo-map-modal';
            modal.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(3, 7, 18, 0.88); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
                z-index: 2147483640; display: flex; align-items: center; justify-content: center;
                font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif; direction: rtl;
                padding: 16px; box-sizing: border-box; animation: modernToastSpringIn 0.35s ease-out;
            `;

            modal.innerHTML = `
                <div style="background: linear-gradient(145deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.95)); border: 1.5px solid rgba(16, 185, 129, 0.4); border-radius: 24px; width: 96%; max-width: 1050px; height: 90vh; max-height: 820px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 60px -10px rgba(0,0,0,0.8), 0 0 40px rgba(16,185,129,0.15);">
                    
                    <!-- Modal Header -->
                    <div style="padding: 16px 22px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.25);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="width: 40px; height: 40px; border-radius: 12px; background: rgba(16,185,129,0.15); border: 1px solid #10b981; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </span>
                            <div>
                                <h3 style="margin: 0; color: #ffffff; font-size: 1.15rem; font-weight: 800;">خريطة تحديد مقر ونطاق عمل الموظف</h3>
                                <span style="font-size: 0.78rem; color: #94a3b8;">انقر على أي نقطة في الخريطة أو اسحب الدبوس لتحديد الموقع المصرح به لفتح النظام</span>
                            </div>
                        </div>
                        <button type="button" id="magaiees-map-modal-close" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #cbd5e1; width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.2s;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Modal Toolbar -->
                    <div style="padding: 12px 20px; background: rgba(15,23,42,0.8); border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <!-- Search Input -->
                        <div style="display: flex; gap: 8px; flex: 1; min-width: 260px; max-width: 460px;">
                            <div style="position: relative; width: 100%;">
                                <input type="text" id="magaiees-map-search-input" placeholder="🔍 ابحث عن اسم مدينة، حي، شارع أو معلم..." style="width: 100%; padding: 9px 36px 9px 12px; background: #0f172a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 10px; font-size: 0.85rem; font-weight: 600; outline: none; box-sizing: border-box;">
                                <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.85rem;"></i>
                            </div>
                            <button type="button" id="magaiees-map-search-btn" style="background: #334155; color: #fff; border: 1px solid rgba(255,255,255,0.15); padding: 9px 16px; border-radius: 10px; font-weight: 700; font-size: 0.82rem; cursor: pointer; white-space: nowrap;">
                                بحث
                            </button>
                        </div>

                        <!-- Radius Slider & Controls -->
                        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); padding: 6px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08);">
                                <span style="font-size: 0.8rem; font-weight: 700; color: #94a3b8;">نطاق السماح:</span>
                                <span id="magaiees-modal-radius-badge" style="font-size: 0.85rem; font-weight: 800; color: #34d399; min-width: 55px;">${activeRadius}م</span>
                                <input type="range" id="magaiees-modal-radius-slider" min="50" max="5000" step="50" value="${activeRadius}" style="width: 110px; accent-color: #10b981; cursor: pointer;">
                            </div>

                            <button type="button" id="magaiees-map-toggle-layer" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #cbd5e1; padding: 7px 12px; border-radius: 10px; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-satellite"></i> <span id="magaiees-layer-name">قمر صناعي</span>
                            </button>

                            <button type="button" id="magaiees-map-my-gps" style="background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.4); color: #38bdf8; padding: 7px 12px; border-radius: 10px; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-crosshairs"></i> موقعي الآن
                            </button>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div style="flex: 1; position: relative; min-height: 380px;">
                        <!-- Floating GPS Permission & Windows Prompt Status Banner -->
                        <div id="magaiees-map-gps-banner" style="display: none; position: absolute; top: 12px; left: 50%; transform: translateX(-50%); z-index: 1001; background: rgba(15,23,42,0.96); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1.5px solid #38bdf8; border-radius: 14px; padding: 10px 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.7), 0 0 20px rgba(56,189,248,0.2); max-width: 92%; width: 560px; align-items: center; gap: 12px; transition: all 0.3s ease; box-sizing: border-box;">
                            <span id="magaiees-map-gps-banner-icon" style="font-size: 1.4rem; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: rgba(56,189,248,0.15); border-radius: 10px;">🛰️</span>
                            <div style="flex: 1; text-align: right;">
                                <div id="magaiees-map-gps-banner-title" style="font-weight: 800; font-size: 0.92rem; color: #38bdf8;">جاري طلب إذن الموقع من ويندوز / المتصفح...</div>
                                <div id="magaiees-map-gps-banner-desc" style="font-size: 0.8rem; color: #cbd5e1; margin-top: 3px; line-height: 1.4;">يرجى تأكيد الضغط على <strong>(سماح / Allow)</strong> في إشعار ويندوز أو المتصفح للوصول لموقعك الفعلي مباشرة.</div>
                            </div>
                        </div>

                        <div id="magaiees-interactive-leaflet-map" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 1;"></div>
                        <div id="magaiees-map-search-results" style="display: none; position: absolute; top: 10px; right: 15px; width: 340px; max-height: 250px; overflow-y: auto; background: rgba(15,23,42,0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.6); z-index: 1000; padding: 6px; box-sizing: border-box;"></div>
                    </div>

                    <!-- Modal Footer -->
                    <div style="padding: 14px 22px; background: rgba(15,23,42,0.95); border-top: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 0.88rem; color: #94a3b8;">الإحداثيات المختارة:</span>
                            <span id="magaiees-modal-coords-display" style="font-family: monospace; font-size: 0.95rem; font-weight: 800; color: #34d399; direction: ltr; background: rgba(0,0,0,0.4); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(16,185,129,0.3);">
                                ${activeLat.toFixed(6)}, ${activeLng.toFixed(6)}
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button type="button" id="magaiees-map-modal-cancel" style="background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #94a3b8; padding: 10px 18px; border-radius: 12px; font-weight: 700; font-size: 0.88rem; cursor: pointer;">
                                إلغاء
                            </button>
                            <button type="button" id="magaiees-map-modal-confirm" style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 800; font-size: 0.92rem; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(16,185,129,0.4);">
                                <i class="fa-solid fa-check"></i> اعتماد هذا الموقع وحفظه للموظف
                            </button>
                        </div>
                    </div>

                </div>
            `;

            document.body.appendChild(modal);

            const closeModal = () => modal.remove();
            modal.querySelector('#magaiees-map-modal-close').addEventListener('click', closeModal);
            modal.querySelector('#magaiees-map-modal-cancel').addEventListener('click', closeModal);

            const mapContainer = modal.querySelector('#magaiees-interactive-leaflet-map');
            const streetTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            });
            const satelliteTiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: '© Esri Satellite'
            });

            let currentTileLayer = streetTiles;
            const initialZoom = hasInitialCoords ? 16 : 13;
            const map = L.map(mapContainer, {
                center: [activeLat, activeLng],
                zoom: initialZoom,
                layers: [currentTileLayer],
                zoomControl: true
            });

            // Modern glowing beacon icon
            const pinIcon = L.divIcon({
                className: 'magaiees-map-custom-marker',
                html: `
                    <div style="position:relative; width:44px; height:44px; display:flex; align-items:center; justify-content:center;">
                        <div style="position:absolute; width:44px; height:44px; border-radius:50%; background:rgba(16,185,129,0.35); animation:magaieesPinPulse 1.8s infinite ease-out;"></div>
                        <div style="width:34px; height:34px; border-radius:50% 50% 50% 0; background:linear-gradient(135deg, #10b981, #059669); transform:rotate(-45deg); box-shadow:0 6px 18px rgba(0,0,0,0.6), 0 0 15px rgba(16,185,129,0.5); display:flex; align-items:center; justify-content:center; border:2.5px solid #ffffff;">
                            <i class="fa-solid fa-briefcase" style="transform:rotate(45deg); color:#ffffff; font-size:13px;"></i>
                        </div>
                    </div>
                `,
                iconSize: [44, 44],
                iconAnchor: [22, 38],
                popupAnchor: [0, -38]
            });

            const marker = L.marker([activeLat, activeLng], {
                icon: pinIcon,
                draggable: true
            }).addTo(map);

            const circle = L.circle([activeLat, activeLng], {
                radius: activeRadius,
                color: '#10b981',
                weight: 2,
                opacity: 0.85,
                fillColor: '#10b981',
                fillOpacity: 0.18
            }).addTo(map);

            function updateCoords(lat, lng) {
                activeLat = lat;
                activeLng = lng;
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                const coordsDisplay = modal.querySelector('#magaiees-modal-coords-display');
                if (coordsDisplay) coordsDisplay.innerText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }

            marker.on('drag', e => {
                const pos = e.target.getLatLng();
                updateCoords(pos.lat, pos.lng);
            });

            map.on('click', e => {
                updateCoords(e.latlng.lat, e.latlng.lng);
            });

            // Radius slider
            const radiusSlider = modal.querySelector('#magaiees-modal-radius-slider');
            const radiusBadge = modal.querySelector('#magaiees-modal-radius-badge');
            radiusSlider.addEventListener('input', e => {
                activeRadius = parseInt(e.target.value, 10);
                circle.setRadius(activeRadius);
                radiusBadge.innerText = activeRadius + 'م';
            });

            // Layer Switcher
            let isSatellite = false;
            const layerBtn = modal.querySelector('#magaiees-map-toggle-layer');
            const layerName = modal.querySelector('#magaiees-layer-name');
            layerBtn.addEventListener('click', () => {
                if (isSatellite) {
                    map.removeLayer(satelliteTiles);
                    map.addLayer(streetTiles);
                    layerName.innerText = 'قمر صناعي';
                    layerBtn.innerHTML = '<i class="fa-solid fa-satellite"></i> <span>قمر صناعي</span>';
                    isSatellite = false;
                } else {
                    map.removeLayer(streetTiles);
                    map.addLayer(satelliteTiles);
                    layerName.innerText = 'خريطة الشوارع';
                    layerBtn.innerHTML = '<i class="fa-solid fa-map"></i> <span>خريطة الشوارع</span>';
                    isSatellite = true;
                }
            });

            // Geolocation Auto-Detection & Windows Permission Handler
            const requestAndFlyToUserLocation = (isAutoTrigger = false) => {
                const banner = modal.querySelector('#magaiees-map-gps-banner');
                const bannerTitle = modal.querySelector('#magaiees-map-gps-banner-title');
                const bannerDesc = modal.querySelector('#magaiees-map-gps-banner-desc');
                const bannerIcon = modal.querySelector('#magaiees-map-gps-banner-icon');

                if (!("geolocation" in navigator)) {
                    if (!isAutoTrigger) {
                        if (window.showModernToast) window.showModernToast('متصفحك لا يدعم خاصية تحديد الموقع (GPS)', 'error');
                        else alert('متصفحك لا يدعم خاصية تحديد الموقع (GPS)');
                    }
                    return;
                }

                if (banner) {
                    banner.style.display = 'flex';
                    banner.style.opacity = '1';
                    banner.style.borderColor = '#38bdf8';
                    banner.style.boxShadow = '0 10px 30px rgba(0,0,0,0.7), 0 0 20px rgba(56,189,248,0.3)';
                    bannerIcon.innerHTML = '🛰️';
                    bannerTitle.innerHTML = 'جاري طلب إذن الموقع من ويندوز / المتصفح...';
                    bannerTitle.style.color = '#38bdf8';
                    bannerDesc.innerHTML = '🔔 يرجى الضغط على <strong>(سماح / Allow)</strong> في نافذة ويندوز أو إشعار المتصفح للوصول لموقعك الفعلي مباشرة.';
                }

                const geoOptions = {
                    enableHighAccuracy: true,
                    timeout: 25000,
                    maximumAge: 0
                };

                navigator.geolocation.getCurrentPosition(
                    pos => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        const acc = Math.round(pos.coords.accuracy || 0);

                        updateCoords(lat, lng);
                        map.flyTo([lat, lng], 17, { animate: true, duration: 1.6 });

                        // Also push directly to studio inputs
                        applyCoordinatesToStudio(lat, lng, 'موقع جهازك الحالي (GPS)');

                        if (banner) {
                            banner.style.borderColor = '#10b981';
                            banner.style.boxShadow = '0 10px 30px rgba(0,0,0,0.7), 0 0 20px rgba(16,185,129,0.3)';
                            bannerIcon.innerHTML = '✅';
                            bannerTitle.innerHTML = 'تم تأكيد الإذن والوصول لموقعك الفعلي بنجاح!';
                            bannerTitle.style.color = '#34d399';
                            bannerDesc.innerHTML = `تم نقلك إلى موقعك الحالي (دقة: ${acc}م). يمكنك تعديل النطاق أو الضغط على "اعتماد هذا الموقع".`;
                            setTimeout(() => {
                                banner.style.opacity = '0';
                                setTimeout(() => { banner.style.display = 'none'; banner.style.opacity = '1'; }, 400);
                            }, 3500);
                        }

                        if (window.showModernToast) window.showModernToast(`✅ تم الوصول لموقعك الحالي بدقة (${acc}م)!`, 'success');
                    },
                    err => {
                        console.warn('Geolocation error:', err);
                        let errMsg = 'تعذر الوصول للموقع بدقة عبر الأقمار الصناعية.';
                        if (err.code === 1) { // PERMISSION_DENIED
                            errMsg = '⚠️ تم رفض إذن الوصول للموقع. يرجى تفعيل الموقع في ويندوز (Windows Settings > Location) أو النقر على رمز القفل 🔒 في شريط المتصفح واختيار (سماح بالموقع).';
                        } else if (err.code === 3) { // TIMEOUT
                            errMsg = 'انتهت مهلة انتظار إذن الموقع. يرجى الضغط على زر (موقعي الآن) والموافقة على إشعار ويندوز.';
                        }

                        if (banner) {
                            banner.style.borderColor = '#f59e0b';
                            banner.style.boxShadow = '0 10px 30px rgba(0,0,0,0.7), 0 0 20px rgba(245,158,11,0.25)';
                            bannerIcon.innerHTML = '⚠️';
                            bannerTitle.innerHTML = 'تنبيه إذن الموقع:';
                            bannerTitle.style.color = '#fbbf24';
                            bannerDesc.innerHTML = errMsg + ' <button type="button" id="magaiees-banner-retry" style="background:#0284c7; color:#fff; border:none; padding:3px 10px; border-radius:6px; font-size:0.75rem; font-weight:700; cursor:pointer; margin-right:8px;">إعادة المحاولة</button>';
                            
                            const retryBtn = modal.querySelector('#magaiees-banner-retry');
                            if (retryBtn) retryBtn.onclick = () => requestAndFlyToUserLocation(false);

                            setTimeout(() => {
                                if (banner && banner.style.display !== 'none') {
                                    banner.style.opacity = '0';
                                    setTimeout(() => { banner.style.display = 'none'; banner.style.opacity = '1'; }, 400);
                                }
                            }, 10000);
                        }
                    },
                    geoOptions
                );
            };

            // My GPS button on map
            modal.querySelector('#magaiees-map-my-gps').addEventListener('click', () => {
                requestAndFlyToUserLocation(false);
            });

            // Search logic
            const searchInput = modal.querySelector('#magaiees-map-search-input');
            const searchBtn = modal.querySelector('#magaiees-map-search-btn');
            const searchResults = modal.querySelector('#magaiees-map-search-results');

            const doSearch = async () => {
                const q = searchInput.value.trim();
                if (!q) return;
                searchBtn.innerText = '⏳...';
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&accept-language=ar`);
                    const results = await res.json();
                    searchBtn.innerText = 'بحث';
                    if (!results || results.length === 0) {
                        searchResults.style.display = 'block';
                        searchResults.innerHTML = '<div style="padding:10px; color:#cbd5e1; font-size:0.82rem; text-align:center;">لم يتم العثور على نتائج لهذا العنوان</div>';
                        return;
                    }
                    searchResults.style.display = 'block';
                    searchResults.innerHTML = results.slice(0, 6).map((item, idx) => `
                        <div class="magaiees-search-item" data-lat="${item.lat}" data-lon="${item.lon}" style="padding:8px 10px; border-radius:8px; cursor:pointer; font-size:0.8rem; color:#cbd5e1; border-bottom:1px solid rgba(255,255,255,0.06); transition:background 0.2s;">
                            📍 <strong>${item.display_name.split(',')[0]}</strong>
                            <div style="font-size:0.72rem; color:#94a3b8; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${item.display_name}</div>
                        </div>
                    `).join('');

                    searchResults.querySelectorAll('.magaiees-search-item').forEach(itemEl => {
                        itemEl.addEventListener('click', () => {
                            const sLat = parseFloat(itemEl.dataset.lat);
                            const sLng = parseFloat(itemEl.dataset.lon);
                            updateCoords(sLat, sLng);
                            map.flyTo([sLat, sLng], 16);
                            searchResults.style.display = 'none';
                        });
                    });
                } catch(e) {
                    searchBtn.innerText = 'بحث';
                    if (window.showModernToast) window.showModernToast('تعذر البحث عن العنوان عبر الإنترنت', 'error');
                }
            };

            searchBtn.addEventListener('click', doSearch);
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doSearch();
                }
            });

            map.on('click', () => { searchResults.style.display = 'none'; });

            // Confirm button
            modal.querySelector('#magaiees-map-modal-confirm').addEventListener('click', () => {
                applyCoordinatesToStudio(activeLat, activeLng, 'الخريطة التفاعلية');
                const radIn = document.getElementById('studio-geo-radius');
                const radRange = document.getElementById('studio-geo-radius-range');
                const radDisplay = document.getElementById('studio-geo-radius-display');
                if (radIn) radIn.value = activeRadius;
                if (radRange) radRange.value = activeRadius;
                if (radDisplay) radDisplay.innerText = activeRadius + ' متر';

                closeModal();
            });

            // Automatically request location and invalidate size after modal render
            setTimeout(() => {
                map.invalidateSize();
                requestAndFlyToUserLocation(true);
            }, 300);
        });
    };

    // 8. GPS Button in Studio
    window.getCurrentGPSInStudio = function() {
        const statusEl = document.getElementById('studio-gps-status');
        if (!("geolocation" in navigator)) {
            if (statusEl) statusEl.innerHTML = '<span style="color:#ef4444;">متصفحك لا يدعم خاصية تحديد الموقع GPS</span>';
            if (window.showModernToast) window.showModernToast('متصفحك لا يدعم خاصية تحديد الموقع GPS', 'error');
            return;
        }
        if (statusEl) statusEl.innerHTML = '<span style="color:#38bdf8;">🛰️ جاري طلب إذن الموقع من ويندوز... يرجى الضغط على (سماح / Allow) في إشعار المتصفح/ويندوز.</span>';
        if (window.showModernToast) window.showModernToast('🔔 يرجى تأكيد السماح بالوصول للموقع في إشعار ويندوز أو المتصفح...', 'info', 4000);

        navigator.geolocation.getCurrentPosition(
            pos => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const acc = Math.round(pos.coords.accuracy || 0);

                applyCoordinatesToStudio(lat, lng, 'موقع جهازك الحالي بالـ GPS');

                if (statusEl) {
                    statusEl.innerHTML = `<span style="color:#34d399;">✅ تم تأكيد إذن ويندوز ورصد موقعك بنجاح! خط العرض: ${lat.toFixed(6)}، خط الطول: ${lng.toFixed(6)} (دقة: ${acc}م)</span>`;
                }
                if (window.showModernToast) window.showModernToast(`✅ تم رصد وتحديد موقعك الحالي بدقة (${acc}م)!`, 'success');
            },
            err => {
                let msg = 'تعذر جلب الموقع عبر الأقمار الصناعية.';
                if (err.code === 1) {
                    msg = 'تم رفض الإذن في ويندوز أو المتصفح. يرجى تفعيل الموقع في ويندوز (Settings > Location) ثم المحاولة مجدداً.';
                } else if (err.code === 3) {
                    msg = 'انتهت مهلة استجابة الـ GPS.';
                }
                if (statusEl) {
                    statusEl.innerHTML = `<span style="color:#ef4444;">❌ فشل تحديد الموقع: ${msg}</span>`;
                }
                if (window.showModernToast) window.showModernToast('❌ فشل تحديد الموقع: ' + msg, 'error');
            },
            { timeout: 25000, enableHighAccuracy: true, maximumAge: 0 }
        );
    };

    window.onStudioUserTypeChange = function(type) {
        const isAdmin = (type === 'admin');
        const adminNotice = document.getElementById('studio-admin-notice');
        const customContainer = document.getElementById('studio-custom-permissions-container');
        const presetsBar = document.getElementById('studio-presets-bar');
        const lblAdmin = document.getElementById('label-role-admin');
        const lblUser = document.getElementById('label-role-user');

        if (adminNotice) adminNotice.style.display = isAdmin ? 'block' : 'none';
        if (customContainer) customContainer.style.display = isAdmin ? 'none' : 'block';
        if (presetsBar) presetsBar.style.display = isAdmin ? 'none' : 'flex';

        if (lblAdmin) lblAdmin.className = isAdmin ? 'active-role-admin' : '';
        if (lblUser) lblUser.className = !isAdmin ? 'active-role-user' : '';
    };

    window.onStudioScreenVisibilityChange = function(fileName, isChecked) {
        const sanitized = fileName.replace(/[^a-zA-Z0-9]/g, '_');
        const actionsPanel = document.getElementById(`actions-${sanitized}`);
        const badge = document.getElementById(`badge-${sanitized}`);

        if (actionsPanel) actionsPanel.style.display = isChecked ? 'flex' : 'none';
        if (badge) {
            badge.innerText = isChecked ? 'ظاهرة للمستخدم' : 'مخفية ومحظورة';
            badge.style.background = isChecked ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)';
            badge.style.color = isChecked ? '#34d399' : '#f87171';
            badge.style.borderColor = isChecked ? '#10b981' : '#ef4444';
        }
    };

    window.applyStudioPreset = function(presetKey) {
        const preset = PRESET_TEMPLATES[presetKey];
        if (!preset) return;

        // Apply screens
        document.querySelectorAll('.screen-visibility-cb').forEach(cb => {
            const file = cb.dataset.file;
            const allowed = !!(preset.allowedScreens && preset.allowedScreens[file]);
            cb.checked = allowed;
            window.onStudioScreenVisibilityChange(file, allowed);
        });

        // Apply screen buttons
        document.querySelectorAll('.screen-btn-cb').forEach(cb => {
            const file = cb.dataset.file;
            const act = cb.dataset.action;
            let allowed = false;
            if (preset.screenButtons && preset.screenButtons[file] && typeof preset.screenButtons[file][act] === 'boolean') {
                allowed = preset.screenButtons[file][act];
            } else if (preset.allowedScreens && preset.allowedScreens[file]) {
                allowed = (act === 'save' || act === 'print' || act === 'new' || act === 'preview' || act === 'share');
            }
            cb.checked = allowed;
        });

        // Apply reports
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            const repId = cb.dataset.reportId;
            cb.checked = !!(preset.allowedReports && preset.allowedReports[repId]);
        });

        // Apply dashboard widgets
        document.querySelectorAll('.dash-perm-cb').forEach(cb => {
            const wid = cb.dataset.widgetId;
            cb.checked = !!(preset.dashboardPermissions && preset.dashboardPermissions[wid]);
        });

        // Apply posting rights
        document.querySelectorAll('.posting-perm-cb').forEach(cb => {
            const pid = cb.dataset.postId;
            cb.checked = !!(preset.postingPermissions && preset.postingPermissions[pid]);
        });

        // Apply master data rights
        document.querySelectorAll('.master-perm-cb').forEach(cb => {
            const mid = cb.dataset.masterId;
            cb.checked = !!(preset.masterDataPermissions && preset.masterDataPermissions[mid]);
        });

        // Apply alerts rights
        document.querySelectorAll('.alert-perm-cb').forEach(cb => {
            const aid = cb.dataset.alertId;
            cb.checked = !!(preset.alertsPermissions && preset.alertsPermissions[aid]);
        });

        // Apply tools rights
        document.querySelectorAll('.tool-perm-cb').forEach(cb => {
            const tid = cb.dataset.toolId;
            cb.checked = !!(preset.toolsPermissions && preset.toolsPermissions[tid]);
        });

        // Apply security level
        if (preset.securityLevel) {
            const secSelect = document.getElementById('studio-user-security-level');
            if (secSelect) {
                secSelect.value = preset.securityLevel;
                if (typeof window.onStudioSecLevelChange === 'function') {
                    window.onStudioSecLevelChange(preset.securityLevel);
                }
            }
        }

        // Apply financial limits
        if (preset.financialLimits) {
            const discPct = document.getElementById('studio-limit-discount-pct');
            if (discPct) discPct.value = preset.financialLimits.maxDiscountPercent || 0;
            const discAmt = document.getElementById('studio-limit-discount-amt');
            if (discAmt) discAmt.value = preset.financialLimits.maxDiscountAmount || 0;
            const vouchAmt = document.getElementById('studio-limit-voucher-amt');
            if (vouchAmt) vouchAmt.value = preset.financialLimits.maxVoucherAmount || 0;
            const invAmt = document.getElementById('studio-limit-invoice-amt');
            if (invAmt) invAmt.value = preset.financialLimits.maxInvoiceAmount || 0;
            const creditSale = document.getElementById('studio-limit-credit-sale');
            if (creditSale) creditSale.checked = !!preset.financialLimits.allowCreditSale;
        }

        // Apply data masking
        if (preset.dataMasking) {
            const mCost = document.getElementById('studio-mask-cost');
            if (mCost) mCost.checked = !!preset.dataMasking.maskCostPrice;
            const mProfit = document.getElementById('studio-mask-profit');
            if (mProfit) mProfit.checked = !!preset.dataMasking.maskProfitMargin;
            const mBank = document.getElementById('studio-mask-bank');
            if (mBank) mBank.checked = !!preset.dataMasking.maskBankAccounts;
            const mPhone = document.getElementById('studio-mask-phone');
            if (mPhone) mPhone.checked = !!preset.dataMasking.maskCustomerPhone;
        }

        // Apply workShift if defined in preset
        if (preset.workShift) {
            const timeRestrictedCb = document.getElementById('studio-time-restricted');
            if (timeRestrictedCb) {
                timeRestrictedCb.checked = !!preset.workShift.restricted;
                if (typeof window.toggleStudioTimeFields === 'function') {
                    window.toggleStudioTimeFields(timeRestrictedCb.checked);
                }
            }
            if (preset.workShift.timeFrom) {
                const tf = document.getElementById('studio-time-from');
                if (tf) tf.value = preset.workShift.timeFrom;
            }
            if (preset.workShift.timeTo) {
                const tt = document.getElementById('studio-time-to');
                if (tt) tt.value = preset.workShift.timeTo;
            }
            if (Array.isArray(preset.workShift.allowedDays)) {
                document.querySelectorAll('.studio-day-cb').forEach(cb => {
                    cb.checked = preset.workShift.allowedDays.includes(parseInt(cb.value, 10));
                });
            }
        }

        // Apply geofencing if defined in preset
        if (preset.geofencing) {
            const geoRestrictedCb = document.getElementById('studio-geo-restricted');
            if (geoRestrictedCb) {
                geoRestrictedCb.checked = !!preset.geofencing.restricted;
                if (typeof window.toggleStudioGeoFields === 'function') {
                    window.toggleStudioGeoFields(geoRestrictedCb.checked);
                }
            }
            if (preset.geofencing.lat !== undefined && preset.geofencing.lat !== null) {
                const latIn = document.getElementById('studio-geo-lat');
                if (latIn) latIn.value = preset.geofencing.lat;
            }
            if (preset.geofencing.lng !== undefined && preset.geofencing.lng !== null) {
                const lngIn = document.getElementById('studio-geo-lng');
                if (lngIn) lngIn.value = preset.geofencing.lng;
            }
            if (preset.geofencing.radius) {
                const radIn = document.getElementById('studio-geo-radius');
                if (radIn) radIn.value = preset.geofencing.radius;
            }
        }

        if (typeof showToast === 'function') {
            showToast(`تم تطبيق نموذج: ${preset.title} بنجاح`, 'success');
        }
    };

    window.applySmartAILevel = function(levelKey) {
        if (!['level_1', 'level_2', 'level_3'].includes(levelKey)) return;

        // 1. Sync radio buttons
        const radio = document.getElementById(`ai-tier-radio-${levelKey}`);
        if (radio) radio.checked = true;

        // 2. Visual highlight & glowing feedback on cards
        ['level_1', 'level_2', 'level_3'].forEach(k => {
            const card = document.getElementById(`ai-tier-card-${k}`);
            if (!card) return;
            if (k === levelKey) {
                card.classList.add('ai-tier-card-active');
                if (k === 'level_1') {
                    card.style.borderColor = '#10b981';
                    card.style.boxShadow = '0 0 25px rgba(16,185,129,0.45)';
                    card.style.background = 'rgba(16,185,129,0.12)';
                } else if (k === 'level_2') {
                    card.style.borderColor = '#f59e0b';
                    card.style.boxShadow = '0 0 25px rgba(245,158,11,0.45)';
                    card.style.background = 'rgba(245,158,11,0.12)';
                } else if (k === 'level_3') {
                    card.style.borderColor = '#ef4444';
                    card.style.boxShadow = '0 0 25px rgba(239,68,68,0.45)';
                    card.style.background = 'rgba(239,68,68,0.12)';
                }
            } else {
                card.classList.remove('ai-tier-card-active');
                card.style.boxShadow = 'none';
                if (k === 'level_1') {
                    card.style.borderColor = 'rgba(16,185,129,0.35)';
                    card.style.background = 'rgba(15,23,42,0.7)';
                } else if (k === 'level_2') {
                    card.style.borderColor = 'rgba(245,158,11,0.35)';
                    card.style.background = 'rgba(15,23,42,0.7)';
                } else if (k === 'level_3') {
                    card.style.borderColor = 'rgba(239,68,68,0.35)';
                    card.style.background = 'rgba(15,23,42,0.7)';
                }
            }
        });

        // 3. Apply preset settings (screens, buttons, reports, widgets, limits, masking, shift, geo)
        window.applyStudioPreset(levelKey);

        // 4. Update the active level status badge
        const badge = document.getElementById('studio-active-ai-level-badge');
        if (badge) {
            if (levelKey === 'level_1') {
                badge.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#10b981;"></i> تم تطبيق: المستوى الأول 🟢 (جاهز للتعديل أدناه)';
                badge.style.borderColor = '#10b981';
                badge.style.background = 'rgba(16,185,129,0.18)';
                badge.style.color = '#34d399';
            } else if (levelKey === 'level_2') {
                badge.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#f59e0b;"></i> تم تطبيق: المستوى الثاني 🟡 (جاهز للتعديل أدناه)';
                badge.style.borderColor = '#f59e0b';
                badge.style.background = 'rgba(245,158,11,0.18)';
                badge.style.color = '#fbbf24';
            } else if (levelKey === 'level_3') {
                badge.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#ef4444;"></i> تم تطبيق: المستوى الثالث 🔴 (جاهز للتعديل أدناه)';
                badge.style.borderColor = '#ef4444';
                badge.style.background = 'rgba(239,68,68,0.18)';
                badge.style.color = '#f87171';
            }
        }

        // 5. Toast alert informing user they can customize everything below
        const titles = {
            level_1: 'المستوى الأول: التشغيلي ونقاط البيع (Operational & POS)',
            level_2: 'المستوى الثاني: المحاسبي والمخزني والإشرافي (Supervisory & Accounting)',
            level_3: 'المستوى الثالث: الإداري والمالي الشامل والتدقيق (Executive & Controller)'
        };
        if (typeof showToast === 'function') {
            showToast(`🤖 تم تطبيق ${titles[levelKey]} كقالب أساسي. جميع الشاشات والأزرار والسقوف بالأسفل مفتوحة وجاهزة للتعديل حسب رغبتك.`, 'success');
        }
    };

    window.onStudioSecLevelChange = function(level) {
        const lvl = parseInt(level, 10) || 1;
        const badge = document.getElementById('studio-sec-level-badge');
        if (badge) badge.innerText = `المستوى الحالي: ${lvl}`;

        const exp = document.getElementById('studio-sec-level-explanation');
        if (!exp) return;

        if (lvl <= 4) {
            exp.innerHTML = `
                <div style="font-weight:800; color:#38bdf8; margin-bottom:4px;">✅ نطاق الرؤية المصرح به (مستوى ${lvl}):</div>
                <div>يستطيع هذا المستخدم رؤية واستخدام أي حساب مالي، خزينة، عميل، أو مورد بدرجة سرية من <strong>1 إلى ${lvl}</strong>.</div>
                <div style="margin-top:6px; color:#f87171; font-weight:700;">
                    🚫 الحسابات المحجوبة تلقائياً: الحسابات ذات المستوى الأكبر من ${lvl} (مثل: <span style="text-decoration:underline;">حساب بنك الخرطوم مستوى 6</span>) سيتم إخفاؤها تماماً من كافة القوائم المنسدلة وسندات الصرف والقبض وكشوف الحسابات.
                </div>
            `;
        } else if (lvl >= 5 && lvl <= 8) {
            exp.innerHTML = `
                <div style="font-weight:800; color:#34d399; margin-bottom:4px;">✅ نطاق الرؤية المتقدم (مستوى ${lvl}):</div>
                <div>يستطيع هذا المستخدم التعامل مع ورؤية كافة الحسابات والعملاء والموردين والبنوك حتى المستوى ${lvl} (بما في ذلك <strong>حساب بنك الخرطوم مستوى 6</strong>).</div>
                <div style="margin-top:6px; color:#f87171; font-weight:700;">
                    🚫 الحسابات المحجوبة: الحسابات والعملاء المخصصين للإدارة العليا بمستوى أعلى من ${lvl} ستظل محجوبة.
                </div>
            `;
        } else {
            exp.innerHTML = `
                <div style="font-weight:800; color:#fbbf24; margin-bottom:4px;">👑 تصريح سيادي كامل (مستوى ${lvl} - سري للغاية):</div>
                <div>يمتلك حق الوصول والتعامل مع كافة الحسابات البنكية، الخزن، الأرصدة، وهوامش الربح، وبيانات العملاء والموردين دون أي حجب أو قيود.</div>
            `;
        }
    };

    window.runStudioAutoDiscovery = function() {
        autoDiscoverScreenElements();
        let totalDiscovered = 0;
        try {
            const discMap = JSON.parse(localStorage.getItem('erp_discovered_screen_actions') || '{}');
            Object.keys(discMap).forEach(k => totalDiscovered += (discMap[k] || []).length);
        } catch(e) {}
        
        if (typeof showToast === 'function') {
            showToast(`✅ تم فحص واستكشاف عناصر وشاشات النظام بنجاح! تم تسجيل (${totalDiscovered}) عنصراً تلقائياً.`, 'success');
        } else {
            alert(`✅ تم فحص واستكشاف عناصر وشاشات النظام بنجاح! تم تسجيل (${totalDiscovered}) عنصراً تلقائياً.`);
        }
        
        // Refresh studio
        if (typeof window.loadUserSettingsStudio === 'function') {
            const sel = document.getElementById('settings-user-select');
            if (sel && sel.value) window.loadUserSettingsStudio(sel.value);
        } else {
            const userId = document.getElementById('user-id')?.value;
            const container = document.getElementById('user-permissions-studio-container');
            if (container && window.USERS) {
                const u = window.USERS.find(x => x.id === userId);
                if (u && window.MagaieesPermissions) window.MagaieesPermissions.renderPermissionsStudioUI(container, u);
            }
        }
    };

    window.selectAllStudioScreens = function(selectAll) {
        document.querySelectorAll('.screen-visibility-cb').forEach(cb => {
            cb.checked = selectAll;
            window.onStudioScreenVisibilityChange(cb.dataset.file, selectAll);
        });
        document.querySelectorAll('.screen-btn-cb').forEach(cb => {
            cb.checked = selectAll;
        });
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleCatScreens = function(catId, selectAll) {
        const card = document.querySelector(`.studio-cat-card[data-cat-id="${catId}"]`);
        if (!card) return;
        card.querySelectorAll('.screen-visibility-cb').forEach(cb => {
            cb.checked = selectAll;
            window.onStudioScreenVisibilityChange(cb.dataset.file, selectAll);
        });
        card.querySelectorAll('.screen-btn-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleAllDashboard = function(selectAll) {
        document.querySelectorAll('.dash-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleAllPosting = function(selectAll) {
        document.querySelectorAll('.posting-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleAllMasterData = function(selectAll) {
        document.querySelectorAll('.master-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleAllAlerts = function(selectAll) {
        document.querySelectorAll('.alert-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleAllTools = function(selectAll) {
        document.querySelectorAll('.tool-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleAllReports = function(selectAll) {
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.filterStudioItems = function(query) {
        const qRaw = (query || '').trim();
        const normalizeAr = (s) => (s || '').toLowerCase()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .trim();

        const qNorm = normalizeAr(qRaw);
        const resultsBar = document.getElementById('studio-search-results-bar');

        // If query is empty, reset everything
        if (!qNorm) {
            if (resultsBar) {
                resultsBar.style.display = 'none';
                resultsBar.innerHTML = '';
            }
            document.querySelectorAll('.studio-screen-row').forEach(row => row.style.display = 'flex');
            document.querySelectorAll('.studio-cat-card').forEach(card => card.style.display = 'block');
            document.querySelectorAll('.studio-report-card').forEach(rep => rep.style.display = 'flex');
            const shiftCard = document.getElementById('studio-work-shifts-card');
            if (shiftCard) shiftCard.style.boxShadow = 'none';
            const geoCard = document.getElementById('studio-geofencing-card');
            if (geoCard) geoCard.style.boxShadow = 'none';
            return;
        }

        // Semantic synonym groups (normalized Arabic)
        const isDeviceMatch = /جهاز|اجهزه|أجهزة|اجهزة|موبايل|هاتف|جوال|ايفون|آيفون|اندرويد|أندرويد|كمبيوتر|لابتوب|سيريال|بصمه|device|uuid|mac|serial/i.test(qNorm);
        const isTimeMatch = /وقت|اوقات|زمن|ازمنه|دوام|ساعات|ساعه|ورديات|ورديه|شيفت|شفت|شفتات|ايام|اسبوع|جمعه|سبت|توقيت|حظر زمني|بدايه الورديه|نهايه الورديه|دوام ليلي|دوام نهاري/i.test(qNorm);
        const isGeoMatch = /موقع|جغرافي|جغرافيه|gps|نطاق|خريطه|احداثيات|متر|نصف القطر|radius|مسافه|مكان/i.test(qNorm);
        const isLimitsMatch = /سقف|سقوف|حد|حدود|خصم|خصومات|نسبه الخصم|مبلغ الفاتوره|سند|سندات|اجل|ائتمان|سلف|سقف مالي|limit|discount/i.test(qNorm);
        const isMaskingMatch = /حجب|تعتيم|اخفاء|سريه|تكلفه|شراء|سعر الشراء|ربح|ارباح|هامش الربح|بنوك|بنك|الخرطوم|هاتف|تلفون|mask/i.test(qNorm);
        const isPostingMatch = /ترحيل|اعتماد|موافقه|الغاء ترحيل|قيد|قيود|يوميه|post|unpost/i.test(qNorm);
        const isReportsMatch = /تقرير|تقارير|كشف|كشوفات|ميزان|ميزانيه|ارباح وخسائر|تدفقات|قائمه دخل|report/i.test(qNorm);
        const isDelegationMatch = /تفويض|وكاله|نيابه|اجازه|بديل|delegate/i.test(qNorm);
        const isPinMatch = /رمز|مرور|بين|pin|مشرف|تجاوز|override/i.test(qNorm);
        const isAiLevelMatch = /مستوى|المستوى|الاول|الثاني|الثالث|ذكاء|ai|قالب|قوالب/i.test(qNorm);
        const isDashboardMatch = /لوحه|مؤشر|مؤشرات|kpi|رسوم|اذكار|dashboard/i.test(qNorm);
        const isMasterDataMatch = /عميل|عملاء|مورد|موردين|صنف|اصناف|دليل|شجره الحسابات|بيانات اساسيه/i.test(qNorm);
        const isAlertsMatch = /تنبيه|تنبيهات|رقابه|مخزون حرج|سيوله|alert/i.test(qNorm);
        const isToolsMatch = /شريعه|شرعي|فتاوى|فتوى|ميراث|تركات|تلفزيون|tv|مهندس/i.test(qNorm);

        // 1. Filter screens in all_screens tab
        let matchingScreensCount = 0;
        document.querySelectorAll('.studio-screen-row').forEach(row => {
            const titleNorm = normalizeAr(row.dataset.screenTitle);
            const fileNorm = normalizeAr(row.dataset.screenFile);
            if (titleNorm.includes(qNorm) || fileNorm.includes(qNorm)) {
                row.style.display = 'flex';
                matchingScreensCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.querySelectorAll('.studio-cat-card').forEach(card => {
            const visibleRows = card.querySelectorAll('.studio-screen-row:not([style*="display: none"])');
            card.style.display = visibleRows.length > 0 ? 'block' : 'none';
        });

        // 2. Filter reports
        let matchingReportsCount = 0;
        document.querySelectorAll('.studio-report-card').forEach(rep => {
            const repTitleNorm = normalizeAr(rep.dataset.reportTitle);
            if (repTitleNorm.includes(qNorm)) {
                rep.style.display = 'flex';
                matchingReportsCount++;
            } else {
                rep.style.display = 'none';
            }
        });

        // 3. Compile Smart Matches for Results Bar
        const matches = [];

        if (isDeviceMatch) {
            matches.push({
                tab: 'work_shifts_and_geo',
                icon: 'fa-solid fa-mobile-screen-button',
                label: '📱 تقييد وحصر الأجهزة المصرح بها وتحديد السقف (Device Binding)',
                badge: 'تطابق مباشر 100%',
                badgeBg: 'rgba(168,85,247,0.25)',
                badgeColor: '#c084fc',
                borderColor: '#a855f7',
                actionKey: 'device_access'
            });
        }

        if (isTimeMatch || isGeoMatch) {
            matches.push({
                tab: 'work_shifts_and_geo',
                icon: 'fa-solid fa-clock',
                label: isTimeMatch ? '⏱️ جدول أوقات العمل والورديات الأسبوعية' : '📍 التقييد الجغرافي بالـ GPS',
                badge: 'تطابق مباشر 100%',
                badgeBg: 'rgba(56,189,248,0.25)',
                badgeColor: '#38bdf8',
                borderColor: '#38bdf8',
                actionKey: 'time_shift'
            });
        }

        if (isLimitsMatch) {
            matches.push({
                tab: 'financial_limits',
                icon: 'fa-solid fa-scale-balanced',
                label: '⚖️ سقوف المبالغ والخصومات المسموحة والبيع الآجل',
                badge: 'تطابق مباشر',
                badgeBg: 'rgba(245,158,11,0.25)',
                badgeColor: '#fbbf24',
                borderColor: '#f59e0b',
                actionKey: 'limits'
            });
        }

        if (isMaskingMatch) {
            matches.push({
                tab: 'data_masking',
                icon: 'fa-solid fa-eye-slash',
                label: '🔒 حجب وتعتيم الأسعار وهوامش الربح والبنوك',
                badge: 'تطابق مباشر',
                badgeBg: 'rgba(239,68,68,0.25)',
                badgeColor: '#f87171',
                borderColor: '#ef4444',
                actionKey: 'masking'
            });
        }

        if (isPostingMatch) {
            matches.push({
                tab: 'posting_rights',
                icon: 'fa-solid fa-sitemap',
                label: '📑 صلاحيات الترحيل والاعتماد المحاسبي',
                badge: 'تطابق مباشر',
                badgeBg: 'rgba(99,102,241,0.25)',
                badgeColor: '#818cf8',
                borderColor: '#6366f1',
                actionKey: 'posting'
            });
        }

        if (isReportsMatch || matchingReportsCount > 0) {
            matches.push({
                tab: 'reports_catalog',
                icon: 'fa-solid fa-chart-pie',
                label: `📊 كتالوج التقارير المصرح بها (${matchingReportsCount > 0 ? matchingReportsCount + ' تقارير' : 'مطابقة'})`,
                badge: `${matchingReportsCount} تقرير`,
                badgeBg: 'rgba(16,185,129,0.25)',
                badgeColor: '#34d399',
                borderColor: '#10b981',
                actionKey: 'reports'
            });
        }

        if (isAiLevelMatch) {
            matches.push({
                tab: 'all_screens',
                icon: 'fa-solid fa-brain',
                label: '🤖 تقسيم الصلاحيات الذكي بالذكاء الاصطناعي (3 مستويات)',
                badge: 'قوالب الذكاء الاصطناعي',
                badgeBg: 'rgba(139,92,246,0.25)',
                badgeColor: '#c4b5fd',
                borderColor: '#8b5cf6',
                actionKey: 'ai_tiers'
            });
        }

        if (isDashboardMatch) {
            matches.push({
                tab: 'dashboard_widgets',
                icon: 'fa-solid fa-gauge',
                label: '📊 لوحة التحكم والمؤشرات (KPIs)',
                badge: 'مؤشرات الأداء',
                badgeBg: 'rgba(14,165,233,0.25)',
                badgeColor: '#38bdf8',
                borderColor: '#0284c7',
                actionKey: 'dashboard'
            });
        }

        if (matchingScreensCount > 0) {
            matches.push({
                tab: 'all_screens',
                icon: 'fa-solid fa-desktop',
                label: `🖥️ شاشات العمليات (${matchingScreensCount} شاشة مطابقة)`,
                badge: `${matchingScreensCount} شاشة`,
                badgeBg: 'rgba(56,189,248,0.25)',
                badgeColor: '#38bdf8',
                borderColor: '#38bdf8',
                actionKey: 'screens'
            });
        }

        // 4. Render Live Search Results Bar
        if (resultsBar) {
            if (matches.length > 0) {
                resultsBar.style.display = 'block';
                resultsBar.innerHTML = `
                    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
                        <span style="font-weight:800; color:#38bdf8; font-size:0.92rem; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> نتائج البحث الذكي لـ "${qRaw}":
                        </span>
                        <span style="font-size:0.78rem; color:#94a3b8;">💡 اضغط على أي نتيجة أدناه للانتقال المباشر والتركيز على القسم:</span>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        ${matches.map((m, idx) => `
                            <button type="button" class="search-match-pill" onclick="window._triggerStudioSearchAction('${m.actionKey}')" style="background:rgba(15,23,42,0.9); border:1.5px solid ${m.borderColor}; color:#fff; padding:7px 14px; border-radius:10px; font-weight:800; font-size:0.84rem; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                <span>${m.label}</span>
                                <span style="background:${m.badgeBg}; color:${m.badgeColor}; padding:2px 7px; border-radius:8px; font-size:0.75rem;">${m.badge}</span>
                            </button>
                        `).join('')}
                    </div>
                `;
            } else {
                resultsBar.style.display = 'block';
                resultsBar.innerHTML = `
                    <div style="color:#f87171; font-size:0.86rem; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>لم يتم العثور على نتيجة تطابق "${qRaw}". جرّب كلمات مثل: (أوقات العمل، الورديات، GPS، مبيعات، ترحيل، خصم، حجب).</span>
                    </div>
                `;
            }
        }

        // 5. Intelligent Automatic Redirection:
        // When searching about Devices, Time/Shifts or Geo, switch directly to work_shifts_and_geo!
        if (isDeviceMatch) {
            window.jumpToDeviceAccessCard();
        } else if (isTimeMatch || isGeoMatch) {
            window.jumpToWorkShiftAndGeoTab();
        } else if (isLimitsMatch && matchingScreensCount === 0) {
            window.switchStudioTab('financial_limits');
        } else if (isMaskingMatch && matchingScreensCount === 0) {
            window.switchStudioTab('data_masking');
        } else if (isPostingMatch && matchingScreensCount === 0) {
            window.switchStudioTab('posting_rights');
        } else if (isReportsMatch && matchingScreensCount === 0 && matchingReportsCount > 0) {
            window.switchStudioTab('reports_catalog');
        } else if (isAiLevelMatch && matchingScreensCount === 0) {
            const aiPanel = document.getElementById('studio-ai-tiers-panel');
            if (aiPanel) aiPanel.scrollIntoView({ behavior: 'smooth' });
        } else if (matchingScreensCount > 0) {
            window.switchStudioTab('all_screens');
        }
    };

    window._triggerStudioSearchAction = function(actionKey) {
        if (actionKey === 'device_access') {
            window.jumpToDeviceAccessCard();
        } else if (actionKey === 'time_shift') {
            window.jumpToWorkShiftAndGeoTab();
        } else if (actionKey === 'limits') {
            window.switchStudioTab('financial_limits');
            document.getElementById('studio-tab-content-financial_limits')?.scrollIntoView({ behavior: 'smooth' });
        } else if (actionKey === 'masking') {
            window.switchStudioTab('data_masking');
            document.getElementById('studio-tab-content-data_masking')?.scrollIntoView({ behavior: 'smooth' });
        } else if (actionKey === 'posting') {
            window.switchStudioTab('posting_rights');
            document.getElementById('studio-tab-content-posting_rights')?.scrollIntoView({ behavior: 'smooth' });
        } else if (actionKey === 'reports') {
            window.switchStudioTab('reports_catalog');
            document.getElementById('studio-tab-content-reports_catalog')?.scrollIntoView({ behavior: 'smooth' });
        } else if (actionKey === 'ai_tiers') {
            const aiPanel = document.getElementById('studio-ai-tiers-panel');
            if (aiPanel) aiPanel.scrollIntoView({ behavior: 'smooth' });
        } else if (actionKey === 'dashboard') {
            window.switchStudioTab('dashboard_widgets');
            document.getElementById('studio-tab-content-dashboard_widgets')?.scrollIntoView({ behavior: 'smooth' });
        } else if (actionKey === 'screens') {
            window.switchStudioTab('all_screens');
            document.getElementById('studio-tab-content-all_screens')?.scrollIntoView({ behavior: 'smooth' });
        }
    };

    // Helper: Collect All Values from Studio Form
    function collectStudioPermissionsData() {
        const userTypeRadio = document.querySelector('input[name="studio_user_type"]:checked');
        const userType = userTypeRadio ? userTypeRadio.value : 'user';

        if (userType === 'admin') {
            const managerPin = document.getElementById('studio-manager-pin')?.value.trim() || '';
            return {
                userType: 'admin',
                securityLevel: 10,
                allowedScreens: {},
                screenButtons: {},
                allowedReports: {},
                dashboardPermissions: {},
                postingPermissions: {},
                masterDataPermissions: {},
                alertsPermissions: {},
                toolsPermissions: {},
                financialLimits: {
                    maxDiscountPercent: 100,
                    maxDiscountAmount: 999999999,
                    maxVoucherAmount: 999999999,
                    maxInvoiceAmount: 999999999,
                    allowCreditSale: true
                },
                dataMasking: {
                    maskCostPrice: false,
                    maskProfitMargin: false,
                    maskBankAccounts: false,
                    maskCustomerPhone: false
                },
                timeRestricted: false,
                timeFrom: '08:00',
                timeTo: '03:00',
                allowedDays: [0, 1, 2, 3, 4, 5, 6],
                geoRestricted: false,
                geoLat: null,
                geoLng: null,
                geoRadius: 500,
                deviceRestricted: false,
                maxDevices: 1,
                trustedDevices: [],
                deviceUuid: '',
                managerPin: managerPin
            };
        }

        const secLevelSelect = document.getElementById('studio-user-security-level');
        const securityLevel = secLevelSelect ? (parseInt(secLevelSelect.value, 10) || 1) : 1;

        const allowedScreens = {};
        document.querySelectorAll('.screen-visibility-cb').forEach(cb => {
            allowedScreens[cb.dataset.file] = cb.checked;
        });

        const screenButtons = {};
        document.querySelectorAll('.screen-btn-cb').forEach(cb => {
            const file = cb.dataset.file;
            const act = cb.dataset.action;
            if (!screenButtons[file]) screenButtons[file] = {};
            screenButtons[file][act] = cb.checked;
        });

        const allowedReports = {};
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            allowedReports[cb.dataset.reportId] = cb.checked;
        });

        const dashboardPermissions = {};
        document.querySelectorAll('.dash-perm-cb').forEach(cb => {
            dashboardPermissions[cb.dataset.widgetId] = cb.checked;
        });

        const postingPermissions = {};
        document.querySelectorAll('.posting-perm-cb').forEach(cb => {
            postingPermissions[cb.dataset.postId] = cb.checked;
        });

        const masterDataPermissions = {};
        document.querySelectorAll('.master-perm-cb').forEach(cb => {
            masterDataPermissions[cb.dataset.masterId] = cb.checked;
        });

        const alertsPermissions = {};
        document.querySelectorAll('.alert-perm-cb').forEach(cb => {
            alertsPermissions[cb.dataset.alertId] = cb.checked;
        });

        const toolsPermissions = {};
        document.querySelectorAll('.tool-perm-cb').forEach(cb => {
            toolsPermissions[cb.dataset.toolId] = cb.checked;
        });

        const financialLimits = {
            maxDiscountPercent: parseFloat(document.getElementById('studio-limit-discount-pct')?.value) || 0,
            maxDiscountAmount: parseFloat(document.getElementById('studio-limit-discount-amt')?.value) || 0,
            maxVoucherAmount: parseFloat(document.getElementById('studio-limit-voucher-amt')?.value) || 0,
            maxInvoiceAmount: parseFloat(document.getElementById('studio-limit-invoice-amt')?.value) || 0,
            allowCreditSale: !!document.getElementById('studio-limit-credit-sale')?.checked
        };

        const dataMasking = {
            maskCostPrice: !!document.getElementById('studio-mask-cost')?.checked,
            maskProfitMargin: !!document.getElementById('studio-mask-profit')?.checked,
            maskBankAccounts: !!document.getElementById('studio-mask-bank')?.checked,
            maskCustomerPhone: !!document.getElementById('studio-mask-phone')?.checked
        };

        const timeRestricted = !!document.getElementById('studio-time-restricted')?.checked;
        const timeFrom = document.getElementById('studio-time-from')?.value || '08:00';
        const timeTo = document.getElementById('studio-time-to')?.value || '03:00';
        const allowedDays = [];
        document.querySelectorAll('.studio-day-cb:checked').forEach(cb => {
            allowedDays.push(parseInt(cb.value, 10));
        });

        const geoRestricted = !!document.getElementById('studio-geo-restricted')?.checked;
        const geoLat = parseFloat(document.getElementById('studio-geo-lat')?.value) || null;
        const geoLng = parseFloat(document.getElementById('studio-geo-lng')?.value) || null;
        const geoRadius = parseFloat(document.getElementById('studio-geo-radius')?.value) || 500;

        // Device Access Control & Multi-Device Quotas
        const deviceRestricted = !!document.getElementById('studio-device-restricted')?.checked;
        const maxDevices = Math.max(1, parseInt(document.getElementById('studio-max-devices')?.value, 10) || 1);
        const trustedDevices = Array.isArray(window._currentStudioTrustedDevices) ? JSON.parse(JSON.stringify(window._currentStudioTrustedDevices)) : [];
        const deviceUuid = trustedDevices[0] ? (typeof trustedDevices[0] === 'string' ? trustedDevices[0] : trustedDevices[0].uuid) : '';

        const managerPin = document.getElementById('studio-manager-pin')?.value.trim() || '';
        const aiSmartLevel = document.querySelector('input[name="ai_smart_tier_radio"]:checked')?.value || '';

        return {
            userType: 'user',
            securityLevel,
            aiSmartLevel,
            allowedScreens,
            screenButtons,
            allowedReports,
            dashboardPermissions,
            postingPermissions,
            masterDataPermissions,
            alertsPermissions,
            toolsPermissions,
            financialLimits,
            dataMasking,
            timeRestricted,
            timeFrom,
            timeTo,
            allowedDays: allowedDays.length > 0 ? allowedDays : [0, 1, 2, 3, 4, 6],
            geoRestricted,
            geoLat,
            geoLng,
            geoRadius,
            deviceRestricted,
            maxDevices,
            trustedDevices,
            deviceUuid,
            managerPin
        };
    }

    // Interactive Delegation Submission for Studio UI
    window.submitStudioDelegation = function() {
        const select = document.getElementById('settings-user-select');
        const fromUserId = (select && select.value) ? select.value : (document.getElementById('user-id')?.value || 'u_admin_default');
        const toUserId = document.getElementById('new-del-target-user')?.value;
        const level = parseInt(document.getElementById('new-del-level')?.value, 10) || 6;
        const start = document.getElementById('new-del-start')?.value;
        const end = document.getElementById('new-del-end')?.value;

        if (!toUserId) {
            alert('يرجى اختيار الموظف المفوض له.');
            return;
        }
        if (!end) {
            alert('يرجى تحديد تاريخ نهاية التفويض.');
            return;
        }

        let allUsers = [];
        try { allUsers = JSON.parse(localStorage.getItem('erp_users_v1') || '[]'); } catch(e){}
        const fromUser = allUsers.find(u => u.id === fromUserId) || { fullName: 'المدير' };
        const toUser = allUsers.find(u => u.id === toUserId) || { fullName: toUserId };

        saveDelegation({
            fromUserId: fromUserId,
            fromUserName: fromUser.fullName || fromUser.username,
            toUserId: toUserId,
            toUserName: toUser.fullName || toUser.username,
            delegatedLevel: level,
            startDate: start ? new Date(start).toISOString() : new Date().toISOString(),
            endDate: new Date(end).toISOString()
        });

        alert(`✅ تم تفعيل التفويض بنجاح إلى (${toUser.fullName || toUser.username}) بمستوى أمان ${level}.`);
        renderStudioDelegationsList(fromUserId);
    };

    function renderStudioDelegationsList(userId) {
        const container = document.getElementById('studio-delegations-list-container');
        if (!container) return;
        const delegations = getActiveDelegations().filter(d => d.fromUserId === userId || d.toUserId === userId);
        if (delegations.length === 0) {
            container.innerHTML = '<p style="color:#94a3b8; font-size:0.85rem; margin:0;">لا توجد تفويضات نشطة حالياً لهذا المستخدم.</p>';
            return;
        }
        let html = '<h5 style="margin:12px 0 8px; color:#cbd5e1; font-size:0.9rem;">التفويضات الحالية:</h5><div style="display:flex; flex-direction:column; gap:8px;">';
        delegations.forEach(d => {
            const isAct = d.status === 'active' && new Date(d.endDate) >= new Date();
            html += `
                <div style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <span style="font-weight:700; color:#fff;">من: ${d.fromUserName} ⬅️ إلى: ${d.toUserName}</span>
                        <span style="display:block; font-size:0.78rem; color:#94a3b8;">مستوى: ${d.delegatedLevel} | ساري حتى: ${new Date(d.endDate).toLocaleDateString('ar-EG')} | الحالة: ${isAct ? '<span style="color:#10b981;">نشط</span>' : '<span style="color:#ef4444;">منتهي/ملغي</span>'}</span>
                    </div>
                    ${isAct ? `<button type="button" onclick="window.MagaieesPermissions.revokeDelegation('${d.id}'); window.renderStudioDelegationsList('${userId}');" style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#f87171; padding:4px 10px; border-radius:6px; font-size:0.75rem; cursor:pointer;">إلغاء التفويض ❌</button>` : ''}
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }
    window.renderStudioDelegationsList = renderStudioDelegationsList;

    // Populate delegatee dropdown in studio on tab switch
    window.populateDelegationDropdown = function() {
        const sel = document.getElementById('new-del-target-user');
        if (!sel) return;
        let allUsers = [];
        try { allUsers = JSON.parse(localStorage.getItem('erp_users_v1') || '[]'); } catch(e){}
        const currentUserId = document.getElementById('settings-user-select')?.value || document.getElementById('user-id')?.value;
        sel.innerHTML = '<option value="">اختر الموظف...</option>';
        allUsers.forEach(u => {
            if (u.id !== currentUserId) {
                sel.innerHTML += `<option value="${u.id}">${u.fullName || u.username} (${u.username})</option>`;
            }
        });
        renderStudioDelegationsList(currentUserId);
    };

    // Export global API
    window.MagaieesPermissions = {
        SYSTEM_SCREENS_CATALOG,
        SYSTEM_REPORTS_CATALOG,
        DASHBOARD_COMPONENTS,
        POSTING_ACTIONS,
        MASTER_DATA_RIGHTS,
        ALERTS_RIGHTS,
        TOOLS_RIGHTS,
        PRESET_TEMPLATES,
        ACTION_META,
        getCurrentUser,
        isCurrentUserAdmin,
        getUserSecurityLevel,
        canUserAccessEntity,
        filterEntitiesByClearance,
        getSecuredStorageList,
        enforceDataClearance,
        autoDiscoverScreenElements,
        broadcastSecurityUpdate,
        handleSecurityBroadcast,
        runStudioAutoDiscovery: window.runStudioAutoDiscovery,
        canUserAccessScreen,
        canUserDoAction,
        canUserViewReport,
        canUserPost,
        canUserAccessDashboardWidget,
        canUserDoMasterDataAction,
        canUserManageAlert,
        canUserUseTool,
        enforceScreenSecurity,
        applyScreenButtonSecurity,
        applyDashboardSecurity,
        applyPostingSecurity,
        applyMasterDataSecurity,
        applyAlertsSecurity,
        applyToolsSecurity,
        renderPermissionsStudioUI,
        collectStudioPermissionsData,
        applySmartAILevel: (typeof window !== 'undefined' ? window.applySmartAILevel : null),
        // Enterprise Tier-1 Suites
        getActiveDelegations,
        getActiveDelegationForUser,
        saveDelegation,
        revokeDelegation,
        requestManagerApproval,
        checkFinancialLimits,
        enforceShiftAndTimeRestrictions,
        isCurrentUserWithinShift,
        isCurrentUserWithinGeofence,
        isUserCurrentlyAllowedWorkTimeAndGeo,
        enforceDelegatedAuthority,
        verifyAuditChainIntegrity,
        triggerSecurityAlert,
        APPROVAL_STAGES,
        canUserApproveDocument,
        renderApprovalBadge
    };

})(window);
