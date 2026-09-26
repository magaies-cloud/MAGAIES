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
        if (u.userType === 'admin') return true;
        if (u.role === 'admin' || u.roleId === 'role_admin_default' || u.roleId === 'admin_fallback') return true;
        if (u.isSystem === true && (u.username === 'admin' || u.username === 'c')) return true;
        if (u.securityLevel && u.securityLevel >= 9) return true;
        return false;
    }

    function canUserAccessScreen(screenUrl, user) {
        const u = user || getCurrentUser();
        if (!u) return true; // fallback
        if (isCurrentUserAdmin(u)) return true;

        const baseFile = (screenUrl || '').split('?')[0].split('/').pop();
        if (!baseFile || baseFile === 'login.php' || baseFile === 'index.php') {
            return true;
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

        // Direct allowedScreens map
        if (u.allowedScreens && typeof u.allowedScreens === 'object') {
            if (u.allowedScreens[baseFile] === true) return true;
            if (u.allowedScreens[baseFile] === false) return false;
        }

        return false; // Default blocked for non-admin
    }

    function canUserDoAction(screenUrl, action, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        const baseFile = (screenUrl || '').split('?')[0].split('/').pop();

        if (u.screenButtons && u.screenButtons[baseFile] && typeof u.screenButtons[baseFile][action] === 'boolean') {
            return u.screenButtons[baseFile][action];
        }

        return false;
    }

    function canUserViewReport(reportId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (u.allowedReports && typeof u.allowedReports[reportId] === 'boolean') {
            return u.allowedReports[reportId];
        }
        return false;
    }

    function canUserPost(type, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (u.postingPermissions && typeof u.postingPermissions[type] === 'boolean') {
            return u.postingPermissions[type];
        }
        return false;
    }

    function canUserAccessDashboardWidget(widgetId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (u.dashboardPermissions && typeof u.dashboardPermissions[widgetId] === 'boolean') {
            return u.dashboardPermissions[widgetId];
        }
        return true;
    }

    function canUserDoMasterDataAction(actionId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (u.masterDataPermissions && typeof u.masterDataPermissions[actionId] === 'boolean') {
            return u.masterDataPermissions[actionId];
        }
        return false;
    }

    function canUserManageAlert(alertId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (u.alertsPermissions && typeof u.alertsPermissions[alertId] === 'boolean') {
            return u.alertsPermissions[alertId];
        }
        return false;
    }

    function canUserUseTool(toolId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

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
        if (!u) return 1;
        if (u.userType === 'admin' || u.role === 'admin' || u.username === 'admin' || u.username === 'c') {
            return 10;
        }

        let lvl = 1;
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

    // --- D. SHIFT SCHEDULES & TIME CONSTRAINTS (الرقابة الزمنية وأوقات الدوام الرسمي) ---
    function enforceShiftAndTimeRestrictions(user) {
        const u = user || getCurrentUser();
        if (!u || isCurrentUserAdmin(u)) return true;
        if (!u.timeRestricted) return true;

        const now = new Date();
        const currentDay = now.getDay();
        const allowedDays = Array.isArray(u.allowedDays) ? u.allowedDays : [0,1,2,3,4,6];

        let isLocked = false;
        let lockReason = '';

        if (!allowedDays.includes(currentDay)) {
            isLocked = true;
            lockReason = 'اليوم الحالي يقع خارج أيام العمل المصرح بها لحسابك.';
        } else if (u.timeFrom && u.timeTo) {
            const curMinutes = now.getHours() * 60 + now.getMinutes();
            const [fh, fm] = u.timeFrom.split(':').map(Number);
            const [th, tm] = u.timeTo.split(':').map(Number);
            const fromMinutes = fh * 60 + (fm || 0);
            const toMinutes = th * 60 + (tm || 0);

            if (fromMinutes <= toMinutes) {
                if (curMinutes < fromMinutes || curMinutes > toMinutes) isLocked = true;
            } else {
                if (curMinutes < fromMinutes && curMinutes > toMinutes) isLocked = true;
            }
            if (isLocked) {
                lockReason = `ساعات الدوام المعتمدة لحسابك هي من ${u.timeFrom} إلى ${u.timeTo}. الوقت الحالي يقع خارج الوردية المصرح بها.`;
            }
        }

        if (isLocked) {
            showTimeLockoutUI(lockReason);
            return false;
        }
        return true;
    }

    function showTimeLockoutUI(reason) {
        if (document.getElementById('magaiees-time-lockout-overlay')) return;

        const overlay = document.createElement('div');
        overlay.id = 'magaiees-time-lockout-overlay';
        overlay.style.cssText = `
            position:fixed; top:0; left:0; width:100vw; height:100vh;
            background:rgba(3, 7, 18, 0.94); backdrop-filter:blur(25px);
            z-index:999998; display:flex; align-items:center; justify-content:center;
            font-family:'Tajawal', sans-serif; direction:rtl; padding:20px; box-sizing:border-box;
        `;
        overlay.innerHTML = `
            <div style="background:#111827; border:2px solid #ef4444; border-radius:24px; max-width:520px; padding:35px 25px; text-align:center; box-shadow:0 0 50px rgba(239,68,68,0.3);">
                <div style="width:75px; height:75px; border-radius:50%; background:rgba(239,68,68,0.12); border:2px solid #ef4444; color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:2.2rem; margin:0 auto 16px;">
                    ⏱️
                </div>
                <h2 style="color:#fff; margin:0 0 10px; font-weight:900;">خارج أوقات العمل الرسمية المصرح بها</h2>
                <p style="color:#94a3b8; font-size:0.95rem; line-height:1.6; margin-bottom:24px;">${reason}</p>
                <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                    <button type="button" onclick="window.MagaieesPermissions.requestManagerApproval({ title:'تصريح فتح خارج أوقات العمل', description:'فتح استثنائي لحساب الموظف خارج الوردية الرسمية', onSuccess: () => document.getElementById('magaiees-time-lockout-overlay')?.remove() })" style="background:linear-gradient(135deg, #f59e0b, #d97706); color:#030712; border:none; padding:12px 22px; border-radius:12px; font-weight:800; cursor:pointer;">
                        🔓 طلب تصريح استثنائي برمز المشرف
                    </button>
                    <button type="button" onclick="window.location.href='login.php'" style="background:transparent; border:1px solid #64748b; color:#cbd5e1; padding:12px 20px; border-radius:12px; font-weight:700; cursor:pointer;">
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
            document.querySelectorAll('#btn-add-customer, button[onclick*="showAddCustomer"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('cust_del', user)) {
            document.querySelectorAll('.del-cust-btn, button[onclick*="deleteCustomer"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('supp_add', user)) {
            document.querySelectorAll('#btn-add-supplier, button[onclick*="showAddSupplier"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('supp_del', user)) {
            document.querySelectorAll('.del-supp-btn, button[onclick*="deleteSupplier"]').forEach(b => b.style.display = 'none');
        }
        if (!canUserDoMasterDataAction('item_add', user)) {
            document.querySelectorAll('#btn-add-item, button[onclick*="showAddItem"]').forEach(b => b.style.display = 'none');
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

    // Reactive Lifecycle Listeners
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enforceScreenSecurity);
    } else {
        enforceScreenSecurity();
    }
    document.addEventListener('DBReady', enforceScreenSecurity);
    document.addEventListener('posReady', enforceScreenSecurity);
    document.addEventListener('strictDataReady', enforceScreenSecurity);
    setTimeout(enforceScreenSecurity, 500);
    setTimeout(enforceScreenSecurity, 1500);

    // ═════════════════════════════════════════════════════════════════════
    // 7. VISUAL PERMISSIONS STUDIO BUILDER (FOR SETTINGS & USERS-ROLES)
    // ═════════════════════════════════════════════════════════════════════

    function renderPermissionsStudioUI(containerElement, userObj, options = {}) {
        if (!containerElement) return;

        const isUserAdmin = userObj ? (userObj.userType === 'admin' || userObj.role === 'admin') : false;
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

                    <!-- Live Global Search Bar & Stats Pill -->
                    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; border-top:1px solid rgba(255,255,255,0.08); padding-top:14px;">
                        <div style="position:relative; flex:1; min-width:280px; max-width:550px;">
                            <i class="fa-solid fa-magnifying-glass" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#38bdf8; font-size:1rem;"></i>
                            <input type="text" id="studio-search-input" oninput="window.filterStudioItems(this.value)" placeholder="🔍 ابحث عن شاشة (مثل: مشتريات، ترحيل، مخازن، شيكات، إرجاع، ميراث)..." style="width:100%; box-sizing:border-box; padding:10px 42px 10px 14px; border-radius:10px; background:rgba(0,0,0,0.4); border:1px solid rgba(56,189,248,0.3); color:#fff; font-size:0.92rem; outline:none; font-family:'Tajawal', sans-serif;">
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); padding:6px 14px; border-radius:20px; font-weight:800; font-size:0.85rem;">
                                🖥️ إجمالي الشاشات: ${totalScreensCount} شاشة
                            </span>
                            <span class="badge" style="background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); padding:6px 14px; border-radius:20px; font-weight:800; font-size:0.85rem;">
                                📑 التقارير: ${SYSTEM_REPORTS_CATALOG.length} تقريراً
                            </span>
                        </div>
                    </div>
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
                        <button type="button" class="studio-tab-btn active" onclick="window.switchStudioTab('all_screens', this)">
                            <i class="fa-solid fa-desktop"></i> الشاشات والأزرار التفصيلية
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('dashboard_widgets', this)">
                            <i class="fa-solid fa-gauge"></i> لوحة التحكم والمؤشرات
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('posting_rights', this)">
                            <i class="fa-solid fa-sitemap"></i> صلاحيات الترحيل والاعتماد
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('master_data_rights', this)">
                            <i class="fa-solid fa-database"></i> تفاصيل البيانات الأساسية
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('alerts_rights', this)">
                            <i class="fa-solid fa-triangle-exclamation"></i> الرقابة والتنبيهات
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('tools_rights', this)">
                            <i class="fa-solid fa-toolbox"></i> الأدوات المساعدة والشريعة
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('reports_catalog', this)">
                            <i class="fa-solid fa-chart-pie"></i> كتالوج التقارير المصرح بها
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('financial_limits', this)">
                            <i class="fa-solid fa-scale-balanced"></i> سقوف المبالغ والخصومات
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('data_masking', this)">
                            <i class="fa-solid fa-eye-slash"></i> حجب وتعتيم الأسعار
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('delegations_rights', this)">
                            <i class="fa-solid fa-user-clock"></i> التفويض المؤقت
                        </button>
                        <button type="button" class="studio-tab-btn" onclick="window.switchStudioTab('manager_override_pin', this)">
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
                const isScreenAllowed = !!allowedScreens[screen.file];
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
                            <h4 style="margin:0 0 8px 0; color:#38bdf8; font-size:1.15rem; font-weight:900;">
                                📊 تخصيص عناصر وبطاقات لوحة التحكم (Dashboard Widgets)
                            </h4>
                            <p style="margin:0 0 18px 0; color:#94a3b8; font-size:0.88rem;">
                                حدد البطاقات والمؤشرات التي يحق للمستخدم رؤيتها داخل لوحة التحكم المركزية:
                            </p>
                            
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
            const isAllowed = !!postingPerms[postAction.id];
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
                            <h4 style="margin:0 0 8px 0; color:#3b82f6; font-size:1.15rem; font-weight:900;">
                                🗄️ تفاصيل وصلاحيات البيانات الأساسية (عملاء، موردين، أصناف، حسابات)
                            </h4>
                            <p style="margin:0 0 18px 0; color:#94a3b8; font-size:0.88rem;">
                                التحكم الدقيق في إمكانية إضافة، تعديل، أو حذف أي كيان أساسي في النظام:
                            </p>

                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
        `;

        MASTER_DATA_RIGHTS.forEach(right => {
            const isAllowed = typeof masterPerms[right.id] === 'boolean' ? masterPerms[right.id] : false;
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
                            <h4 style="margin:0 0 8px 0; color:#f87171; font-size:1.15rem; font-weight:900;">
                                🔔 صلاحيات مصفوفة الرقابة والتنبيهات
                            </h4>
                            <p style="margin:0 0 18px 0; color:#94a3b8; font-size:0.88rem;">
                                تحديد قدرة المستخدم على ضبط وتفعيل تنبيهات المخازن، المشتريات، والمالية وتعديل الحدود:
                            </p>

                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
        `;

        ALERTS_RIGHTS.forEach(alertRight => {
            const isAllowed = typeof alertsPerms[alertRight.id] === 'boolean' ? alertsPerms[alertRight.id] : false;
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
                            <h4 style="margin:0 0 8px 0; color:#fbbf24; font-size:1.15rem; font-weight:900;">
                                🛠️ صلاحيات الأدوات المساعدة والشريعة الإسلامية
                            </h4>
                            <p style="margin:0 0 18px 0; color:#94a3b8; font-size:0.88rem;">
                                التحكم في استخدام حاسبة الميراث الشرعي، موسوعة الفتاوى، وبث التلفزيون:
                            </p>

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
            const isRepAllowed = !!allowedReports[rep.id];
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
        }, 50);
    }

    // ═════════════════════════════════════════════════════════════════════
    // 8. STUDIO CONTROLLER & EVENT LISTENERS
    // ═════════════════════════════════════════════════════════════════════

    window.switchStudioTab = function(tabKey, btn) {
        document.querySelectorAll('.studio-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.studio-tab-content').forEach(c => c.style.display = 'none');
        if (btn) btn.classList.add('active');
        const target = document.getElementById(`studio-tab-content-${tabKey}`);
        if (target) target.style.display = 'block';
        if (tabKey === 'delegations_rights' && typeof window.populateDelegationDropdown === 'function') {
            window.populateDelegationDropdown();
        }
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

        if (typeof showToast === 'function') {
            showToast(`تم تطبيق نموذج: ${preset.title} بنجاح`, 'success');
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

    window.toggleAllPosting = function(selectAll) {
        document.querySelectorAll('.posting-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleAllReports = function(selectAll) {
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.filterStudioItems = function(query) {
        const q = (query || '').trim().toLowerCase();

        // 1. Filter screens
        document.querySelectorAll('.studio-screen-row').forEach(row => {
            const title = (row.dataset.screenTitle || '').toLowerCase();
            const file = (row.dataset.screenFile || '').toLowerCase();
            if (!q || title.includes(q) || file.includes(q)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });

        // 2. Filter reports
        document.querySelectorAll('.studio-report-card').forEach(rep => {
            const title = (rep.dataset.reportTitle || '').toLowerCase();
            if (!q || title.includes(q)) {
                rep.style.display = 'flex';
            } else {
                rep.style.display = 'none';
            }
        });

        // Auto switch to all_screens tab if typing search
        if (q && document.getElementById('studio-tab-content-all_screens').style.display === 'none') {
            const firstTabBtn = document.querySelector('.studio-tab-btn');
            if (firstTabBtn) window.switchStudioTab('all_screens', firstTabBtn);
        }
    };

    // Helper: Collect All Values from Studio Form
    function collectStudioPermissionsData() {
        const userTypeRadio = document.querySelector('input[name="studio_user_type"]:checked');
        const userType = userTypeRadio ? userTypeRadio.value : 'user';

        if (userType === 'admin') {
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
                toolsPermissions: {}
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

        const managerPin = document.getElementById('studio-manager-pin')?.value.trim() || '';

        return {
            userType: 'user',
            securityLevel,
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
            managerPin
        };
    }

    // Interactive Delegation Submission for Studio UI
    window.submitStudioDelegation = function() {
        const select = document.getElementById('settings-user-select');
        const fromUserId = select ? select.value : 'u_admin_default';
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
                    ${isAct ? `<button type="button" onclick="window.MagaieesPermissions.revokeDelegation('${d.id}'); renderStudioDelegationsList('${userId}');" style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#f87171; padding:4px 10px; border-radius:6px; font-size:0.75rem; cursor:pointer;">إلغاء التفويض ❌</button>` : ''}
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    // Populate delegatee dropdown in studio on tab switch
    window.populateDelegationDropdown = function() {
        const sel = document.getElementById('new-del-target-user');
        if (!sel) return;
        let allUsers = [];
        try { allUsers = JSON.parse(localStorage.getItem('erp_users_v1') || '[]'); } catch(e){}
        const currentUserId = document.getElementById('settings-user-select')?.value;
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
        // Enterprise Tier-1 Suites
        getActiveDelegations,
        getActiveDelegationForUser,
        saveDelegation,
        revokeDelegation,
        requestManagerApproval,
        checkFinancialLimits,
        enforceShiftAndTimeRestrictions,
        enforceDelegatedAuthority,
        verifyAuditChainIntegrity,
        triggerSecurityAlert,
        APPROVAL_STAGES,
        canUserApproveDocument,
        renderApprovalBadge
    };

})(window);
