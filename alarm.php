<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مصفوفة الرقابة والتنبيهات الشاملة V4.2
// نظام عالمي متكامل يغطي كافة شاشات وأقسام النظام بجداول تفصيلية وتحكم On/Off
// ====================================================================
$page_title = 'مصفوفة الرقابة وإدارة التنبيهات العالمية';
$header_theme_class = 'header-theme-alarm';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

// -------------------------------------------------------------
// 1. التحرّي وتأكيد وجود جداول التنبيهات وزرع الـ 34 قاعدة الشاملة
// -------------------------------------------------------------
function ensureAlertTablesExist($pdo, $tenant_id) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS alert_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id VARCHAR(100) NOT NULL,
            rule_key VARCHAR(100) NOT NULL,
            rule_name VARCHAR(255) NOT NULL,
            description VARCHAR(255) DEFAULT '',
            category VARCHAR(50) DEFAULT 'GENERAL',
            screen_name VARCHAR(100) DEFAULT '',
            screen_file VARCHAR(100) DEFAULT '',
            is_enabled TINYINT(1) DEFAULT 1,
            warning_threshold FLOAT DEFAULT 80,
            critical_threshold FLOAT DEFAULT 100,
            unit VARCHAR(20) DEFAULT '',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY tenant_rule (tenant_id, rule_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS alert_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id VARCHAR(100) NOT NULL,
            rule_key VARCHAR(100) NOT NULL,
            severity VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            screen_file VARCHAR(100) DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // باقة الـ 34 قاعدة رقابية العالمية الشاملة
        $allRules = [
            [
                'rule_key' => 'sales_below_cost',
                'rule_name' => 'البيع بأقل من سعر التكلفة',
                'description' => 'تنبيه فوري عند محاولة بيع أي صنف بسعر أقل من متوسط التكلفة المرجحة (WAC) لمنع الخسائر التجارية.',
                'category' => 'SALES',
                'screen_name' => 'المبيعات المباشرة وفواتير البيع',
                'screen_file' => 'direct-sales.php / sales.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 3,
                'unit' => 'عملية',
            ],
            [
                'rule_key' => 'sales_qty_exceeds_stock',
                'rule_name' => 'البيع بكمية تتجاوز الرصيد المتاح بالمستودع',
                'description' => 'تحذير عند إدخال كمية بيع أكبر من الرصيد الفعلي المتوفر في عنبر ومخزن الصنف.',
                'category' => 'SALES',
                'screen_name' => 'المبيعات المباشرة ونقاط البيع',
                'screen_file' => 'direct-sales.php / sales.php',
                'is_enabled' => 1,
                'warning_threshold' => 0,
                'critical_threshold' => 0,
                'unit' => 'قطعة',
            ],
            [
                'rule_key' => 'sales_negative_stock',
                'rule_name' => 'إصدار فاتورة بيع برصيد مخزني سالب',
                'description' => 'منع وتحذير حرج عند بيع صنف رصيده صفر أو سالب بالمستودع لتفادي تشويه تكلفة المبيعات.',
                'category' => 'SALES',
                'screen_name' => 'المبيعات وفواتير الكاشير',
                'screen_file' => 'direct-sales.php / pro-invoice.php',
                'is_enabled' => 1,
                'warning_threshold' => 0,
                'critical_threshold' => 0,
                'unit' => 'وحدة',
            ],
            [
                'rule_key' => 'sales_credit_limit_exceeded',
                'rule_name' => 'تجاوز الحد الائتماني للعميل عند البيع الآجل',
                'description' => 'تنبيه البائع عند تجاوز العميل لسقف المديونية والائتمان المسموح به في بطاقة حسابه.',
                'category' => 'SALES',
                'screen_name' => 'المبيعات الآجلة والعقود',
                'screen_file' => 'direct-sales.php / sales.php',
                'is_enabled' => 1,
                'warning_threshold' => 80,
                'critical_threshold' => 100,
                'unit' => '%',
            ],
            [
                'rule_key' => 'sales_overdue_debt_block',
                'rule_name' => 'وجود فواتير مبيعات سابقة متأخرة السداد',
                'description' => 'إنذار عند إصدار فاتورة جديدة لعميل لديه فواتير آجلة سابقة تجاوزت فترة السماح.',
                'category' => 'SALES',
                'screen_name' => 'المبيعات المباشرة',
                'screen_file' => 'direct-sales.php',
                'is_enabled' => 1,
                'warning_threshold' => 30,
                'critical_threshold' => 60,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'sales_discount_limit_breach',
                'rule_name' => 'تجاوز نسبة الخصم المسموحة للبائع',
                'description' => 'تنبيه عند قيام موظف المبيعات بمنح خصم يتجاوز صلاحيته المحددة دون تصريح الإدارة.',
                'category' => 'SALES',
                'screen_name' => 'شاشات المبيعات والكاشير',
                'screen_file' => 'direct-sales.php / pos-invoice-print.php',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 10,
                'unit' => '%',
            ],
            [
                'rule_key' => 'sales_blocked_customer',
                'rule_name' => 'محاولة البيع لعميل محظور أو موقوف',
                'description' => 'حظر وإنذار فوري عند اختيار عميل تم تعليق حسابه أو حظره من قبل الإدارة المالية.',
                'category' => 'SALES',
                'screen_name' => 'شاشات المبيعات وإصدار الفواتير',
                'screen_file' => 'direct-sales.php / sales.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'محاولة',
            ],
            [
                'rule_key' => 'sales_margin_danger',
                'rule_name' => 'تدني هامش الربح الإجمالي للفاتورة',
                'description' => 'تحذير عند انخفاض نسبة ربحية الفاتورة عن الحد الأدنى الآمن للربحية التجارية.',
                'category' => 'SALES',
                'screen_name' => 'المبيعات والفوترة المتقدمة',
                'screen_file' => 'pro-invoice.php / sales.php',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 2,
                'unit' => '%',
            ],
            [
                'rule_key' => 'inv_low_stock',
                'rule_name' => 'وصول الصنف لحد الطلب الأدنى (Reorder Point)',
                'description' => 'إشعار مبكر ومستمر للأصناف التي وصل رصيدها المخزني لحد إعادة الطلب.',
                'category' => 'INVENTORY',
                'screen_name' => 'تقرير وإدارة المخزون',
                'screen_file' => 'inventory-report.php / smart_inventory_report.php',
                'is_enabled' => 1,
                'warning_threshold' => 10,
                'critical_threshold' => 5,
                'unit' => 'كمية',
            ],
            [
                'rule_key' => 'inv_critical_zero_stock',
                'rule_name' => 'النفاد التام لكمية الصنف بالمستودع',
                'description' => 'إنذار حرج بنفاد رصيد الصنف في جميع العنابر والمستودعات لمنع تعطيل المبيعات.',
                'category' => 'INVENTORY',
                'screen_name' => 'حركة المخزون والعنابر',
                'screen_file' => 'inventory-report.php',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 0,
                'unit' => 'كمية',
            ],
            [
                'rule_key' => 'inv_deadstock',
                'rule_name' => 'ركود البضاعة وجمود الأصناف (Deadstock)',
                'description' => 'تنبيه بوجود بضائع مخزنة لفترات طويلة دون أي حركة سحب بيعية لتفادي تجميد السيولة.',
                'category' => 'INVENTORY',
                'screen_name' => 'المخزون الذكي والرواكد',
                'screen_file' => 'smart_inventory_report.php',
                'is_enabled' => 1,
                'warning_threshold' => 60,
                'critical_threshold' => 120,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_fast_moving_surge',
                'rule_name' => 'سحب وسرعة دوران عالية غير معتادة على الصنف',
                'description' => 'تنبيه ذكي بأن صنفاً معيناً يشهد إقبالاً وسحباً سريعاً غير مسبوق لطلب توريد عاجل.',
                'category' => 'INVENTORY',
                'screen_name' => 'حركة المخازن والتحليلات',
                'screen_file' => 'inventory-report.php',
                'is_enabled' => 1,
                'warning_threshold' => 150,
                'critical_threshold' => 250,
                'unit' => '%',
            ],
            [
                'rule_key' => 'inv_slow_moving',
                'rule_name' => 'أصناف بطيئة الحركة وضعيفة السحب',
                'description' => 'تنبيه بالأصناف التي انخفض معدل دورانها التجاري بنسبة ملحوظة لعمل عروض ترويجية.',
                'category' => 'INVENTORY',
                'screen_name' => 'تحليل المخزون والرواكد',
                'screen_file' => 'smart_inventory_report.php',
                'is_enabled' => 1,
                'warning_threshold' => 45,
                'critical_threshold' => 90,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_expiring_soon',
                'rule_name' => 'قرب انتهاء الصلاحية للمنتجات ذات الصلاحية',
                'description' => 'إنذار مبكر للأصناف التي تقترب فترة صلاحيتها من الانتهاء لإعادتها للمورد أو تصريفها.',
                'category' => 'INVENTORY',
                'screen_name' => 'إدارة الأصناف والمخازن',
                'screen_file' => 'inventory-report.php',
                'is_enabled' => 1,
                'warning_threshold' => 60,
                'critical_threshold' => 30,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_stocktake_variance',
                'rule_name' => 'وجود فروقات وعجز في الجرد المخزني',
                'description' => 'تنبيه بوجود تباين أو عجز غير مطابق بين الجرد الفعلي على الرفوف والرصيد الدفتري.',
                'category' => 'INVENTORY',
                'screen_name' => 'تسويات وجرد المخازن',
                'screen_file' => 'inventory-report.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 5,
                'unit' => '%',
            ],
            [
                'rule_key' => 'supply_supplier_delay',
                'rule_name' => 'تأخر استلام طلبيات الشراء من الموردين والمصانع',
                'description' => 'مراقبة أوامر الشراء المعلقة التي تجاوزت فترة التوريد المتوقعة دون استلام البضاعة.',
                'category' => 'SUPPLY',
                'screen_name' => 'المشتريات وأوامر التوريد',
                'screen_file' => 'purchase.php / purchase-order.php',
                'is_enabled' => 1,
                'warning_threshold' => 7,
                'critical_threshold' => 14,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'fin_negative_cash_balance',
                'rule_name' => 'الرصيد السالب للخزائن والصناديق والبنوك',
                'description' => 'إنذار فوري عند صرف مبالغ تجعل رصيد أي خزينة أو حساب بنكي سالباً.',
                'category' => 'FINANCE',
                'screen_name' => 'سندات الصرف الفردية والمتعددة',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'is_enabled' => 1,
                'warning_threshold' => 0,
                'critical_threshold' => 0,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'fin_min_liquidity_threshold',
                'rule_name' => 'انخفاض رصيد السيولة عن حد الأمان التشغيلي',
                'description' => 'تنبيه الإدارة المالية عند هبوط النقد المتوفر بالخزينة عن الحد الأدنى للالتزامات اليومية.',
                'category' => 'FINANCE',
                'screen_name' => 'الخزينة والسيولة والتدفقات',
                'screen_file' => 'cashflow_modern.php / vouchers-hub.php',
                'is_enabled' => 1,
                'warning_threshold' => 50000,
                'critical_threshold' => 10000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'fin_duplicate_transfer_no',
                'rule_name' => 'تكرار رقم التحويلة البنكية في سندات أخرى',
                'description' => 'منع وتنبيه تكرار تسجيل نفس رقم إشعار التحويل البنكي في أكثر من سند صرف أو قبض.',
                'category' => 'FINANCE',
                'screen_name' => 'سندات الصرف والقبض البنكية',
                'screen_file' => 'single-cash-payment.php / single-cash-receipt.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 2,
                'unit' => 'تكرار',
            ],
            [
                'rule_key' => 'fin_duplicate_cheque_no',
                'rule_name' => 'تكرار رقم الشيك البنكي في المعاملات المالية',
                'description' => 'إنذار عند إدخال رقم شيك تم استخدامه مسبقاً في سند أو حساب آخر لتفادي التلاعب.',
                'category' => 'FINANCE',
                'screen_name' => 'سندات الشيكات والمدفوعات',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 2,
                'unit' => 'تكرار',
            ],
            [
                'rule_key' => 'fin_overdue_checks',
                'rule_name' => 'تأخر تحصيل أو صرف الشيكات المستحقة',
                'description' => 'مراقبة الشيكات الصادرة والواردة المعلقة التي حان أو فات موعد استحقاقها البنكي.',
                'category' => 'FINANCE',
                'screen_name' => 'إدارة الشيكات والسندات',
                'screen_file' => 'vouchers-hub.php / alarm.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 3,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'fin_bounced_cheques',
                'rule_name' => 'شيكات مرتدة أو مرفوضة من المصرف',
                'description' => 'إشعار فوري بالشيكات المرتدة من البنك لاتخاذ الإجراء القانوني وتجميد حساب العميل.',
                'category' => 'FINANCE',
                'screen_name' => 'متابعة الشيكات والتحصيل',
                'screen_file' => 'vouchers-hub.php / customer_exposure.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'شيك',
            ],
            [
                'rule_key' => 'fin_liquidity_deficit_forecast',
                'rule_name' => 'توقع عجز سيولة نقدية (الالتزامات > السيولة)',
                'description' => 'تحليل تنبؤي بوجود عجز سيولة إذا كانت الشيكات والالتزامات المستحقة قريباً تفوق الرصيد المتاح.',
                'category' => 'FINANCE',
                'screen_name' => 'التدفقات النقدية والموازنة',
                'screen_file' => 'cashflow_modern.php',
                'is_enabled' => 1,
                'warning_threshold' => 7,
                'critical_threshold' => 3,
                'unit' => 'أيام مقبلة',
            ],
            [
                'rule_key' => 'fin_fx_rate_discrepancy',
                'rule_name' => 'تباين كبير في سعر الصرف المستخدم بالسند',
                'description' => 'تنبيه عند تسجيل سند مالي بسعر صرف يختلف بنسبة تتجاوز الحد المسموح عن سعر النظام العام.',
                'category' => 'FINANCE',
                'screen_name' => 'سندات العملات الأجنبية والصرف',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 6,
                'unit' => '%',
            ],
            [
                'rule_key' => 'fin_large_cash_outflow',
                'rule_name' => 'سندات صرف ذات مبالغ نقدية ضخمة واستثنائية',
                'description' => 'إشعار فوري لمدير النظام عند إصدار سند صرف نقدي يتجاوز السقف العادي للمصروفات اليومية.',
                'category' => 'FINANCE',
                'screen_name' => 'سندات الصرف المالي',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'is_enabled' => 1,
                'warning_threshold' => 500000,
                'critical_threshold' => 1000000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'credit_aging_danger',
                'rule_name' => 'تراكم وأعمار الديون غير المسددة للعملاء',
                'description' => 'إنذار بأرصدة العملاء المدينة التي تجاوزت فترات ائتمان حرجة دون سداد لتفادي الديون الهالكة.',
                'category' => 'CREDIT',
                'screen_name' => 'أعمار الديون والائتمان',
                'screen_file' => 'credit_limits_dashboard.php / customer_exposure.php',
                'is_enabled' => 1,
                'warning_threshold' => 60,
                'critical_threshold' => 90,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'customer_inactive_churn',
                'rule_name' => 'انقطاع وركود العملاء الدائمين (VIP Churn)',
                'description' => 'تنبيه إدارة المبيعات بأن عميلاً رئيسياً توقف عن الشراء منذ فترة غير معتادة لمتابعته.',
                'category' => 'SALES',
                'screen_name' => 'تحليل ولاء وركود العملاء',
                'screen_file' => 'customer_churn.php / customer_exposure.php',
                'is_enabled' => 1,
                'warning_threshold' => 30,
                'critical_threshold' => 60,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'customer_high_returns',
                'rule_name' => 'ارتفاع غير طبيعي في نسبة مرتجعات العميل',
                'description' => 'تنبيه عند تجاوز نسبة مرتجعات مبيعات عميل معين للحد المقبول لمراجعة جودة التوريد.',
                'category' => 'SALES',
                'screen_name' => 'مرتجعات المبيعات والعملاء',
                'screen_file' => 'sales.php / direct-sales.php',
                'is_enabled' => 1,
                'warning_threshold' => 10,
                'critical_threshold' => 20,
                'unit' => '%',
            ],
            [
                'rule_key' => 'supplier_overdue_balance',
                'rule_name' => 'مستحقات متأخرة لمورد تجاوزت تاريخ الاستحقاق',
                'description' => 'تنبيه بالأرصدة الدائنة للموردين التي تجاوزت فترة السداد المتفق عليها لتفادي وقف التوريد.',
                'category' => 'SUPPLY',
                'screen_name' => 'حسابات الموردين والمشتريات',
                'screen_file' => 'purchase.php / master-data.php',
                'is_enabled' => 1,
                'warning_threshold' => 30,
                'critical_threshold' => 60,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'sec_voucher_audit_edit_delete',
                'rule_name' => 'تعديل أو حذف أي سند مالي أو فاتورة سابقة',
                'description' => 'إشعار رقابي فوري للإدارة عند تعديل أو إلغاء أي مستند مالي بعد ترحيله واعتماده.',
                'category' => 'SECURITY',
                'screen_name' => 'سجل التدقيق المحاسبي وسندات الصرف والقبض',
                'screen_file' => 'vouchers-hub.php / single-cash-payment.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'عملية',
            ],
            [
                'rule_key' => 'sec_unposted_draft_vouchers',
                'rule_name' => 'السندات والفواتير المعلقة أو المسودة غير المرحلة',
                'description' => 'مراقبة المستندات المالية التي ظلت بحالة مسودة لأكثر من 24 ساعة دون ترحيل للقوائم المالية.',
                'category' => 'SECURITY',
                'screen_name' => 'السندات المعلقة والفواتير',
                'screen_file' => 'vouchers-hub.php / sales.php',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 10,
                'unit' => 'سند معلق',
            ],
            [
                'rule_key' => 'sec_missed_db_backup',
                'rule_name' => 'تأخر النسخ الاحتياطي لقاعدة البيانات (> 24 ساعة)',
                'description' => 'إنذار أمني حرج في لوحة التحكم عند عدم تنفيذ نسخة احتياطية لقاعدة البيانات خلال آخر 24 ساعة.',
                'category' => 'SECURITY',
                'screen_name' => 'لوحة التحكم وإدارة النسخ الاحتياطي',
                'screen_file' => 'settings.php / magaiees_unified_dashboard.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 2,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'sec_unbalanced_entries',
                'rule_name' => 'عدم اتزان القيود المحاسبية (المدين != الدائن)',
                'description' => 'فحص فوري لأي قيد محاسبي أو سند غير متوازن في ميزان المراجعة ودفتر الأستاذ العام.',
                'category' => 'SECURITY',
                'screen_name' => 'دفتر الأستاذ العام وميزان المراجعة',
                'screen_file' => 'general_ledger_v2.php / analytics.php',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'قيد غير متزن',
            ],
            [
                'rule_key' => 'sec_suspicious_login',
                'rule_name' => 'محاولات دخول فاشلة أو غير مصرح بها للنظام',
                'description' => 'تنبيه أمني عند تكرار محاولات تسجيل الدخول بكلمات سر خاطئة لتفادي محاولات الاختراق.',
                'category' => 'SECURITY',
                'screen_name' => 'شاشة تسجيل الدخول وحماية النظام',
                'screen_file' => 'login.php / settings.php',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 5,
                'unit' => 'محاولات',
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO alert_settings (tenant_id, rule_key, rule_name, description, category, screen_name, screen_file, is_enabled, warning_threshold, critical_threshold, unit) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE 
                                    rule_name = VALUES(rule_name), 
                                    description = VALUES(description), 
                                    category = VALUES(category),
                                    screen_name = VALUES(screen_name),
                                    screen_file = VALUES(screen_file),
                                    unit = VALUES(unit)");
        foreach ($allRules as $r) {
            $stmt->execute([
                $tenant_id, 
                $r['rule_key'], 
                $r['rule_name'], 
                $r['description'], 
                $r['category'], 
                $r['screen_name'], 
                $r['screen_file'], 
                $r['is_enabled'], 
                $r['warning_threshold'], 
                $r['critical_threshold'], 
                $r['unit']
            ]);
        }
    } catch (Exception $e) {
        error_log("Error initializing alert tables: " . $e->getMessage());
    }
}

ensureAlertTablesExist($pdo, $GLOBAL_TENANT_ID);

// -------------------------------------------------------------
// 2. محرك فحص البيانات الحية وتوليد الإنذارات والتنبيهات
// -------------------------------------------------------------
function runAlertEngine($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        $pdo->prepare("DELETE FROM alert_logs WHERE tenant_id = ?")->execute([$GLOBAL_TENANT_ID]);
        
        $stmtSet = $pdo->prepare("SELECT rule_key, is_enabled FROM alert_settings WHERE is_enabled = 1 AND tenant_id = ?");
        $stmtSet->execute([$GLOBAL_TENANT_ID]);
        $settings = $stmtSet->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // 1. فحص تجاوز الحد الائتماني للعملاء
        if (isset($settings['sales_credit_limit_exceeded']) || isset($settings['credit_limit_breach'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM customers WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $c = json_decode($row['data'], true);
                    if ($c && (float)($c['creditLimit'] ?? 0) > 0) {
                        $name = $c['name'] ?? 'عميل غير معروف';
                        $bal = (float)($c['currentBalance'] ?? 0);
                        $limit = (float)($c['creditLimit'] ?? 0);
                        $pct = ($bal / $limit) * 100;
                        if ($pct >= 100) {
                            $msg = "🚨 خطر ائتماني حرج! العميل [" . $name . "] تجاوز الحد الائتماني بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . " / الحد: " . number_format($limit, 2) . ")";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'sales_credit_limit_exceeded', 'RED', ?, 'direct-sales.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($pct >= 80) {
                            $msg = "⚠️ تنبيه ائتماني! العميل [" . $name . "] اقترب من سقف الائتمان المسموح بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . ")";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'sales_credit_limit_exceeded', 'YELLOW', ?, 'direct-sales.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        }
                    }
                }
            } catch (Exception $ex) {}
        }

        // 2. فحص الرصيد السالب للخزائن والبنوك
        if (isset($settings['fin_negative_cash_balance'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM safes WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $safe = json_decode($row['data'], true);
                    if ($safe) {
                        $name = $safe['name'] ?? 'خزينة';
                        $bal = (float)($safe['balance'] ?? 0);
                        if ($bal < 0) {
                            $msg = "🚨 عجز نقدي حرج! رصيد الخزينة/الحساب [" . $name . "] أصبح سالباً بمبلغ (" . number_format($bal, 2) . ")!";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'fin_negative_cash_balance', 'RED', ?, 'single-cash-payment.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        }
                    }
                }
            } catch (Exception $ex) {}
        }

        // 3. فحص الرصيد السالب للأصناف ونقص المخزون
        if (isset($settings['sales_negative_stock']) || isset($settings['inv_critical_zero_stock']) || isset($settings['inv_low_stock'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $item = json_decode($row['data'], true);
                    if ($item) {
                        $name = $item['name'] ?? 'صنف';
                        $stock = (float)($item['stockQuantity'] ?? ($item['quantity'] ?? ($item['qty'] ?? 0)));
                        $minStock = (float)($item['minStock'] ?? 10);
                        if ($stock < 0 && isset($settings['sales_negative_stock'])) {
                            $msg = "🚨 رصيد مخزني سالب! الصنف [" . $name . "] رصيده سالب (" . $stock . " وحدة) في المستودع ويجب تسوية الجرد فوراً!";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'sales_negative_stock', 'RED', ?, 'inventory-report.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($stock == 0 && isset($settings['inv_critical_zero_stock'])) {
                            $msg = "🚨 نفاد مخزون كامل! الصنف [" . $name . "] رصيده الحالي بالمخزن صفر (0 وحدة).";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'inv_critical_zero_stock', 'RED', ?, 'inventory-report.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($stock <= $minStock && $stock > 0 && isset($settings['inv_low_stock'])) {
                            $msg = "⚠️ حد إعادة الطلب! الصنف [" . $name . "] وصل لحد الطلب الأدنى (المتبقي: " . $stock . " وحدة).";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'inv_low_stock', 'YELLOW', ?, 'inventory-report.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        }
                    }
                }
            } catch (Exception $ex) {}
        }

        // 4. فحص الشيكات المتأخرة والمرتدة
        if (isset($settings['fin_overdue_checks']) || isset($settings['fin_bounced_cheques'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM `checks` WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $ch = json_decode($row['data'], true);
                    if ($ch) {
                        $no = $ch['checkNo'] ?? ($ch['no'] ?? '---');
                        $amt = (float)($ch['amount'] ?? 0);
                        $status = strtoupper($ch['status'] ?? '');
                        if (in_array($status, ['BOUNCED', 'REJECTED', 'RETURNED']) && isset($settings['fin_bounced_cheques'])) {
                            $msg = "🚨 شيك مرتد! الشيك رقم [" . $no . "] بمبلغ (" . number_format($amt, 2) . ") تم رفضه من البنك!";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'fin_bounced_cheques', 'RED', ?, 'vouchers-hub.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($status === 'PENDING' && isset($settings['fin_overdue_checks'])) {
                            $due = $ch['dueDate'] ?? ($ch['due_date'] ?? '');
                            if ($due && $due < date('Y-m-d')) {
                                $msg = "⚠️ شيك متأخر! الشيك رقم [" . $no . "] بمبلغ (" . number_format($amt, 2) . ") تجاوز تاريخ استحقاقه (" . $due . ").";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'fin_overdue_checks', 'YELLOW', ?, 'vouchers-hub.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                            }
                        }
                    }
                }
            } catch (Exception $ex) {}
        }

        // 5. فحص تكرار أرقام التحويلات والشيكات
        if (isset($settings['fin_duplicate_transfer_no'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM vouchers WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                $transferMap = [];
                while ($row = $stmt->fetch()) {
                    $v = json_decode($row['data'], true);
                    if ($v) {
                        $transfers = $v['transfers'] ?? [];
                        if (empty($transfers) && !empty($v['mobileTransferNo'])) {
                            $transfers = [['transferNo' => $v['mobileTransferNo']]];
                        }
                        foreach ($transfers as $tr) {
                            $tNo = trim($tr['transferNo'] ?? '');
                            if ($tNo !== '' && $tNo !== '0') {
                                $transferMap[$tNo] = ($transferMap[$tNo] ?? 0) + 1;
                            }
                        }
                    }
                }
                foreach ($transferMap as $tNo => $cnt) {
                    if ($cnt > 1) {
                        $msg = "🚨 تكرار رقم مستند مالي! رقم التحويلة البنكية [" . $tNo . "] مكرر في (" . $cnt . ") سندات مختلفة!";
                        $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'fin_duplicate_transfer_no', 'RED', ?, 'single-cash-payment.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                    }
                }
            } catch (Exception $ex) {}
        }

        // 6. فحص النسخ الاحتياطي لقاعدة البيانات
        if (isset($settings['sec_missed_db_backup'])) {
            $backupDir = __DIR__ . '/backups';
            $hasRecentBackup = false;
            if (is_dir($backupDir)) {
                $files = glob($backupDir . '/*.sql');
                if (!empty($files)) {
                    $latest = max(array_map('filemtime', $files));
                    if ((time() - $latest) <= 86400) {
                        $hasRecentBackup = true;
                    }
                }
            }
            if (!$hasRecentBackup) {
                $msg = "🚨 تنبيه أمان حرج! لم يتم إنشاء نسخة احتياطية لقاعدة البيانات خلال آخر 24 ساعة.";
                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'sec_missed_db_backup', 'RED', ?, 'settings.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
            }
        }

    } catch (Exception $e) {
        error_log("Error running alert engine: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 3. معالجة طلبات AJAX و POST
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // AJAX Toggle Rule
    if ($action === 'toggle_rule_ajax') {
        header('Content-Type: application/json');
        $rule_key = $_POST['rule_key'] ?? '';
        $is_enabled = !empty($_POST['is_enabled']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE alert_settings SET is_enabled = ? WHERE tenant_id = ? AND rule_key = ?");
        $stmt->execute([$is_enabled, $GLOBAL_TENANT_ID, $rule_key]);
        
        runAlertEngine($pdo);
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM alert_settings WHERE is_enabled = 1 AND tenant_id = ?");
        $stmtCount->execute([$GLOBAL_TENANT_ID]);
        $enabledCount = $stmtCount->fetchColumn();

        $stmtLogs = $pdo->prepare("SELECT * FROM alert_logs WHERE tenant_id = ? ORDER BY severity DESC, id DESC");
        $stmtLogs->execute([$GLOBAL_TENANT_ID]);
        $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        $red = 0; $yellow = 0;
        foreach ($logs as $l) {
            if (($l['severity'] ?? '') === 'RED') $red++; else $yellow++;
        }

        echo json_encode([
            'success' => true,
            'is_enabled' => $is_enabled,
            'enabled_count' => (int)$enabledCount,
            'red_count' => $red,
            'yellow_count' => $yellow,
            'logs' => $logs
        ]);
        exit;
    }

    // AJAX Toggle All (Enable All / Disable All / By Category)
    if ($action === 'bulk_toggle_ajax') {
        header('Content-Type: application/json');
        $enable_state = !empty($_POST['is_enabled']) ? 1 : 0;
        $category = $_POST['category'] ?? 'ALL';

        if ($category === 'ALL') {
            $stmt = $pdo->prepare("UPDATE alert_settings SET is_enabled = ? WHERE tenant_id = ?");
            $stmt->execute([$enable_state, $GLOBAL_TENANT_ID]);
        } else {
            $stmt = $pdo->prepare("UPDATE alert_settings SET is_enabled = ? WHERE tenant_id = ? AND category = ?");
            $stmt->execute([$enable_state, $GLOBAL_TENANT_ID, $category]);
        }

        runAlertEngine($pdo);

        echo json_encode(['success' => true, 'message' => 'تم تحديث حالة القواعد بنجاح']);
        exit;
    }

    // AJAX Update Thresholds
    if ($action === 'update_thresholds_ajax') {
        header('Content-Type: application/json');
        $rule_key = $_POST['rule_key'] ?? '';
        $warn = (float)($_POST['warning_threshold'] ?? 0);
        $crit = (float)($_POST['critical_threshold'] ?? 0);

        $stmt = $pdo->prepare("UPDATE alert_settings SET warning_threshold = ?, critical_threshold = ? WHERE tenant_id = ? AND rule_key = ?");
        $stmt->execute([$warn, $crit, $GLOBAL_TENANT_ID, $rule_key]);

        runAlertEngine($pdo);

        echo json_encode(['success' => true, 'message' => 'تم حفظ الشروط بنجاح']);
        exit;
    }
}

// -------------------------------------------------------------
// 4. معالجة طلب جلب التنبيهات والقواعد بصيغة JSON
// -------------------------------------------------------------
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'get_live_alerts_json') {
        header('Content-Type: application/json');
        runAlertEngine($pdo);
        
        $stmtL = $pdo->prepare("SELECT * FROM alert_logs WHERE tenant_id = ? ORDER BY severity DESC, id DESC");
        $stmtL->execute([$GLOBAL_TENANT_ID]);
        $logs = $stmtL->fetchAll(PDO::FETCH_ASSOC);
        
        $red = 0; $yellow = 0;
        foreach ($logs as $l) {
            if (($l['severity'] ?? '') === 'RED') $red++; else $yellow++;
        }
        
        echo json_encode([
            'success' => true,
            'total' => count($logs),
            'red_count' => $red,
            'yellow_count' => $yellow,
            'alerts' => $logs
        ]);
        exit;
    }

    if ($_GET['action'] === 'get_rules_json') {
        header('Content-Type: application/json');
        $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? ORDER BY category ASC, id ASC");
        $stmtR->execute([$GLOBAL_TENANT_ID]);
        $rules = $stmtR->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'rules' => $rules
        ]);
        exit;
    }
}

// تشغيل المحرك لجلب البيانات للعرض الأولي
runAlertEngine($pdo);

$rules = [];
$logs = [];
try {
    $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? ORDER BY category ASC, id ASC");
    $stmtR->execute([$GLOBAL_TENANT_ID]);
    $rules = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    $stmtL = $pdo->prepare("SELECT * FROM alert_logs WHERE tenant_id = ? ORDER BY severity DESC, id DESC");
    $stmtL->execute([$GLOBAL_TENANT_ID]);
    $logs = $stmtL->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$red_count = 0; $yellow_count = 0; $enabled_rules = 0;
foreach ($logs as $l) {
    if (($l['severity'] ?? '') === 'RED') $red_count++;
    else $yellow_count++;
}
foreach ($rules as $r) {
    if ($r['is_enabled']) $enabled_rules++;
}

// تقسيم التنبيهات حسب الأقسام الخمسة
$sections = [
    'SALES' => [
        'id' => 'sec-sales',
        'category' => 'SALES',
        'title' => '🛒 شاشات المبيعات وفواتير البيع ونقاط البيع POS',
        'screens' => 'المبيعات المباشرة direct-sales.php / فواتير المبيعات sales.php / الفاتورة الاحترافية pro-invoice.php / طباعة الكاشير pos-invoice-print.php',
        'icon' => 'fa-cash-register',
        'color' => '#38bdf8',
        'border' => 'rgba(56, 189, 248, 0.4)',
        'rules' => []
    ],
    'INVENTORY' => [
        'id' => 'sec-inventory',
        'category' => 'INVENTORY',
        'title' => '📦 شاشات المخازن والمستودعات والتوريد والمصانع',
        'screens' => 'تقرير وإدارة المخزون inventory-report.php / المخزون الذكي والرواكد smart_inventory_report.php / المشتريات purchase.php / أوامر الشراء purchase-order.php',
        'icon' => 'fa-warehouse',
        'color' => '#a78bfa',
        'border' => 'rgba(167, 139, 250, 0.4)',
        'rules' => []
    ],
    'FINANCE' => [
        'id' => 'sec-finance',
        'category' => 'FINANCE',
        'title' => '💰 شاشات الخزينة وسندات القبض والصرف والمصارف',
        'screens' => 'سند الصرف الفردي single-cash-payment.php / سند الصرف المتعدد multi-cash-payment.php / سند القبض single-cash-receipt.php / مركز السندات vouchers-hub.php / التدفقات cashflow_modern.php',
        'icon' => 'fa-vault',
        'color' => '#34d399',
        'border' => 'rgba(52, 211, 153, 0.4)',
        'rules' => []
    ],
    'CREDIT' => [
        'id' => 'sec-credit',
        'category' => 'CREDIT',
        'title' => '👥 شاشات العملاء والائتمان والتحصيل والموردين',
        'screens' => 'لوحة الائتمان credit_limits_dashboard.php / انكشاف العملاء customer_exposure.php / ركود العملاء customer_churn.php / البيانات الأساسية master-data.php',
        'icon' => 'fa-users-gear',
        'color' => '#fbbf24',
        'border' => 'rgba(251, 191, 36, 0.4)',
        'rules' => []
    ],
    'SECURITY' => [
        'id' => 'sec-security',
        'category' => 'SECURITY',
        'title' => '🛡️ شاشات الأمان والرقابة والتدقيق المحاسبي وحماية النظام',
        'screens' => 'لوحة التحكم magaiees_unified_dashboard.php / الإعدادات والنسخ الاحتياطي settings.php / دفتر الأستاذ general_ledger_v2.php / التقارير المالية analytics.php',
        'icon' => 'fa-shield-halved',
        'color' => '#f87171',
        'border' => 'rgba(248, 113, 113, 0.4)',
        'rules' => []
    ]
];

foreach ($rules as $r) {
    $cat = $r['category'];
    if ($cat === 'SUPPLY') $cat = 'INVENTORY';
    if (isset($sections[$cat])) {
        $sections[$cat]['rules'][] = $r;
    } else {
        $sections['SECURITY']['rules'][] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem("erp_lang") || "ar";
    if(l==="en") {
        document.documentElement.setAttribute("dir","ltr");
        document.documentElement.setAttribute("lang","en");
    }
})();
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مصفوفة الرقابة وإدارة التنبيهات العالمية | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-color: #050308;
            --surface: rgba(14, 10, 24, 0.85);
            --border-glass: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-muted: #94a3b8;
            --theme-danger: #f43f5e;
            --theme-warning: #f59e0b;
            --theme-success: #10b981;
            --theme-info: #38bdf8;
        }

        :root.light-theme {
            --bg-color: #f8fafc;
            --surface: #ffffff;
            --border-glass: #000000;
            --text-main: #000000;
            --text-muted: #334155;
        }

        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0;
            min-height: 100vh;
            font-family: 'Cairo', 'Tajawal', sans-serif;
            color: var(--text-main);
            overflow-x: hidden;
        }

        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }

        .alarm-master-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 10px 15px 60px 15px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stat-card-v4 {
            background: var(--surface);
            border: 2px solid var(--border-glow, rgba(255, 255, 255, 0.1));
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(20px);
        }

        .stat-card-v4:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px var(--border-glow, rgba(239, 68, 68, 0.3));
        }

        .stat-card-v4::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3.5px;
            background: linear-gradient(90deg, transparent, var(--bar-color, #ef4444), transparent);
        }

        .cat-tab-btn {
            padding: 11px 18px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            color: #94a3b8;
            white-space: nowrap;
        }

        .cat-tab-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateY(-2px);
        }

        .cat-tab-btn.active {
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.3), rgba(239, 68, 68, 0.45)) !important;
            border-color: #f43f5e !important;
            color: #ffffff !important;
            box-shadow: 0 0 20px rgba(244, 63, 94, 0.4);
        }

        .section-block {
            background: var(--surface);
            border: 2px solid var(--border-color, rgba(255, 255, 255, 0.1));
            border-radius: 22px;
            padding: 22px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(20px);
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .section-block:hover {
            border-color: var(--border-hover, #38bdf8);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.5);
        }

        .matrix-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .matrix-table th {
            padding: 12px 16px;
            font-size: 0.85rem;
            font-weight: 900;
            color: #94a3b8;
            text-align: right;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            background: transparent;
        }

        .matrix-row {
            background: rgba(15, 23, 42, 0.75);
            transition: all 0.25s ease;
            border-radius: 14px;
        }

        .matrix-row td {
            padding: 14px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .matrix-row td:first-child {
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
        }

        .matrix-row td:last-child {
            border-left: 1px solid rgba(255, 255, 255, 0.06);
            border-top-left-radius: 14px;
            border-bottom-left-radius: 14px;
        }

        .matrix-row:hover {
            background: rgba(30, 41, 59, 0.9) !important;
            transform: scale(1.002);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .ios-switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
            flex-shrink: 0;
            margin: 0;
        }

        .ios-switch input { opacity: 0; width: 0; height: 0; }

        .ios-slider {
            position: absolute; cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #334155;
            transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 34px;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .ios-slider:before {
            position: absolute; content: "";
            height: 20px; width: 20px;
            left: 4px; bottom: 3px;
            background-color: white;
            transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        }

        input:checked + .ios-slider {
            background-color: #10b981;
            border-color: #10b981;
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.6);
        }

        input:checked + .ios-slider:before {
            transform: translateX(24px);
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="ambient-bg"></div>

    <div class="alarm-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/85 border-2 border-rose-500/40 p-5 rounded-2xl shadow-2xl backdrop-blur-2xl">
            <div>
                <a href="magaiees_unified_dashboard.php" class="inline-flex items-center gap-2 text-rose-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع للوحة القيادة الموحدة (Unified Dashboard)
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                    مصفوفة الرقابة وإدارة التنبيهات والإنذارات العالمية
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">
                    جداول رقابية تفصيلية مقسمة لكل شاشة وقسم بالنظام مع تحكم كامل للمدير والمستشار (On / Off) لضبط القرارات بدقة عالمية.
                </p>
            </div>

            <!-- Quick Bulk Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" onclick="bulkToggle('ALL', 1)" class="px-4 py-2 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/40 font-black rounded-xl text-xs transition flex items-center gap-1.5 shadow-md">
                    <i class="fa-solid fa-check-double"></i> تفعيل الكل (34 قاعدة)
                </button>
                <button type="button" onclick="bulkToggle('ALL', 0)" class="px-4 py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-400 border border-rose-500/40 font-black rounded-xl text-xs transition flex items-center gap-1.5 shadow-md">
                    <i class="fa-solid fa-ban"></i> تعطيل الكل
                </button>
                <button type="button" onclick="window.print();" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-black rounded-xl text-xs transition flex items-center gap-1.5 border border-slate-700 shadow-md">
                    <i class="fa-solid fa-print"></i> طباعة المصفوفة
                </button>
                <button type="button" onclick="refreshLiveEngine()" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-black rounded-xl text-xs transition shadow-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate"></i> فحص حي الآن
                </button>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY CARDS (4 Grid) 📊 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="stat-card-v4" style="--border-glow: rgba(239, 68, 68, 0.4); --bar-color: #ef4444;">
                <span class="text-xs text-rose-400 font-bold block">🚨 الإنذارات الحرجـة النشطة</span>
                <span class="text-3xl font-black text-rose-400 mt-2 block" id="kpi-red-count"><?php echo $red_count; ?></span>
                <span class="text-xs text-rose-400/80 mt-1 block">مخاطر تتطلب معالجة فورية</span>
            </div>

            <div class="stat-card-v4" style="--border-glow: rgba(245, 158, 11, 0.4); --bar-color: #f59e0b;">
                <span class="text-xs text-amber-400 font-bold block">⚠️ التحذيرات والتنبيهات المتوسطة</span>
                <span class="text-3xl font-black text-amber-400 mt-2 block" id="kpi-yellow-count"><?php echo $yellow_count; ?></span>
                <span class="text-xs text-amber-400/80 mt-1 block">تنبيهات للمتابعة الدورية</span>
            </div>

            <div class="stat-card-v4" style="--border-glow: rgba(16, 185, 129, 0.4); --bar-color: #10b981;">
                <span class="text-xs text-emerald-400 font-bold block">⚙️ القواعد المفعلة بالنظام</span>
                <span class="text-3xl font-black text-emerald-400 mt-2 block" id="kpi-enabled-rules"><?php echo $enabled_rules; ?> / <?php echo count($rules); ?></span>
                <span class="text-xs text-emerald-400/80 mt-1 block">قواعد رقابية نشطة تفحص تلقائياً</span>
            </div>

            <div class="stat-card-v4" style="--border-glow: rgba(56, 189, 248, 0.4); --bar-color: #38bdf8;">
                <span class="text-xs text-sky-400 font-bold block">🏢 الشاشات الخاضعة للرقابة</span>
                <span class="text-3xl font-black text-sky-400 mt-2 block">12 شاشة</span>
                <span class="text-xs text-slate-400 mt-1 block">المبيعات والمخازن والخزائن والأمان</span>
            </div>
        </div>

        <!-- 🎛️ DEPARTMENT & SCREEN TABS + SEARCH BAR 🎛️ -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-slate-900/70 rounded-2xl border border-slate-800">
            <div class="flex flex-wrap items-center gap-2" id="category-tabs">
                <button type="button" class="cat-tab-btn active" onclick="filterSectionTab('ALL', this)">
                    <i class="fa-solid fa-layer-group"></i> 🌟 عرض كل الجداول (34 قاعدة)
                </button>
                <button type="button" class="cat-tab-btn" onclick="filterSectionTab('SALES', this)">
                    <i class="fa-solid fa-cash-register text-sky-400"></i> 🛒 شاشات المبيعات والفوترة (8)
                </button>
                <button type="button" class="cat-tab-btn" onclick="filterSectionTab('INVENTORY', this)">
                    <i class="fa-solid fa-warehouse text-purple-400"></i> 📦 المخازن والمستودعات (8)
                </button>
                <button type="button" class="cat-tab-btn" onclick="filterSectionTab('FINANCE', this)">
                    <i class="fa-solid fa-vault text-emerald-400"></i> 💰 الخزينة وسندات الصرف والقبض (9)
                </button>
                <button type="button" class="cat-tab-btn" onclick="filterSectionTab('CREDIT', this)">
                    <i class="fa-solid fa-users-gear text-amber-400"></i> 👥 العملاء والائتمان والتحصيل (4)
                </button>
                <button type="button" class="cat-tab-btn" onclick="filterSectionTab('SECURITY', this)">
                    <i class="fa-solid fa-shield-halved text-rose-400"></i> 🛡️ الأمان والرقابة المحاسبية (5)
                </button>
            </div>

            <!-- Search box -->
            <div class="relative min-w-[260px]">
                <input type="text" id="matrix-search" oninput="searchMatrix(this.value)" placeholder="🔍 ابحث بالشاشة، الاسم، أو الوصف..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs font-bold text-slate-200 outline-none focus:border-rose-500 pr-9">
                <i class="fa-solid fa-search absolute right-3 top-3 text-slate-500 text-xs"></i>
            </div>
        </div>

        <!-- 📋 5 INDEPENDENT SECTION TABLES 📋 -->
        <?php foreach ($sections as $secKey => $sec): ?>
            <div class="section-block" id="<?php echo $sec['id']; ?>" data-sec-category="<?php echo $sec['category']; ?>" style="--border-color: <?php echo $sec['border']; ?>; --border-hover: <?php echo $sec['color']; ?>;">
                
                <!-- Section Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3.5 mb-4">
                    <div>
                        <h2 class="text-lg md:text-xl font-black flex items-center gap-2.5" style="color: <?php echo $sec['color']; ?>;">
                            <i class="fa-solid <?php echo $sec['icon']; ?>"></i>
                            <?php echo $sec['title']; ?>
                            <span class="text-xs bg-white/10 px-2.5 py-0.5 rounded-full text-white font-bold font-mono">
                                <?php echo count($sec['rules']); ?> تنبيهات
                            </span>
                        </h2>
                        <div class="text-[11px] font-mono text-slate-400 mt-1 font-bold">
                            🏢 الشاشات المرتبطة: <span class="text-slate-300"><?php echo htmlspecialchars($sec['screens']); ?></span>
                        </div>
                    </div>

                    <!-- Section Quick Actions -->
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="bulkToggle('<?php echo $sec['category']; ?>', 1)" class="px-3 py-1.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 rounded-lg text-xs font-black transition">
                            ✓ تفعيل القسم
                        </button>
                        <button type="button" onclick="bulkToggle('<?php echo $sec['category']; ?>', 0)" class="px-3 py-1.5 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 rounded-lg text-xs font-black transition">
                            ✕ تعطيل القسم
                        </button>
                    </div>
                </div>

                <!-- Section Table -->
                <div class="overflow-x-auto">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;">#</th>
                                <th style="width: 200px;">الشاشة المرتبطة والملف</th>
                                <th style="width: 220px;">اسم التنبيه الرقابي</th>
                                <th>شرح التنبيه والهدف الرقابي لحماية المنشأة</th>
                                <th style="width: 185px;">الشروط والحدود الرقمية</th>
                                <th style="width: 110px; text-align:center;">الحالة الحية</th>
                                <th style="width: 100px; text-align:center;">التحكم On/Off</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sIdx = 1;
                            foreach ($sec['rules'] as $rule): 
                                $isViolated = false;
                                $violSeverity = '';
                                foreach ($logs as $l) {
                                    if (($l['rule_key'] ?? '') === $rule['rule_key']) {
                                        $isViolated = true;
                                        $violSeverity = $l['severity'];
                                        break;
                                    }
                                }
                            ?>
                                <tr class="matrix-row" data-search="<?php echo htmlspecialchars(strtolower($rule['screen_name'] . ' ' . $rule['screen_file'] . ' ' . $rule['rule_name'] . ' ' . $rule['description'])); ?>">
                                    <td class="font-bold text-slate-500 text-xs"><?php echo $sIdx++; ?></td>
                                    
                                    <!-- Screen Info -->
                                    <td>
                                        <div class="font-black text-slate-100 text-xs md:text-sm">
                                            <?php echo htmlspecialchars($rule['screen_name'] ?? ''); ?>
                                        </div>
                                        <a href="<?php echo explode(' ', $rule['screen_file'])[0]; ?>" class="inline-block text-[11px] font-mono font-bold mt-1 text-sky-400 hover:underline">
                                            📄 <?php echo htmlspecialchars($rule['screen_file'] ?? ''); ?> ↗
                                        </a>
                                    </td>

                                    <!-- Alert Name -->
                                    <td>
                                        <div class="font-extrabold text-slate-100 text-xs md:text-sm"><?php echo htmlspecialchars($rule['rule_name']); ?></div>
                                        <span class="inline-block text-[10px] font-mono font-bold text-slate-400 mt-1">#<?php echo $rule['rule_key']; ?></span>
                                    </td>

                                    <!-- Description & Auditing Purpose -->
                                    <td>
                                        <p class="text-xs text-slate-300 font-bold leading-relaxed"><?php echo htmlspecialchars($rule['description'] ?? ''); ?></p>
                                    </td>

                                    <!-- Threshold Inputs -->
                                    <td>
                                        <div class="flex items-center gap-1 text-xs bg-slate-950/80 p-1 rounded-xl border border-slate-800">
                                            <span class="text-amber-400 font-bold text-[11px]">⚠️:</span>
                                            <input type="number" step="any" id="twarn-<?php echo $rule['rule_key']; ?>" value="<?php echo (float)$rule['warning_threshold']; ?>" class="w-12 bg-slate-900 border border-slate-700 text-center py-0.5 rounded text-amber-400 font-black text-xs outline-none focus:border-amber-400">
                                            
                                            <span class="text-rose-400 font-bold text-[11px] mr-1">🚨:</span>
                                            <input type="number" step="any" id="tcrit-<?php echo $rule['rule_key']; ?>" value="<?php echo (float)$rule['critical_threshold']; ?>" class="w-12 bg-slate-900 border border-slate-700 text-center py-0.5 rounded text-rose-400 font-black text-xs outline-none focus:border-rose-400">
                                            
                                            <button type="button" onclick="saveThresholdsTable('<?php echo $rule['rule_key']; ?>')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded text-[10px] font-black transition border border-slate-700" title="حفظ الشروط">💾</button>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block mt-1 font-bold">الوحدة: <?php echo htmlspecialchars($rule['unit'] ?? ''); ?></span>
                                    </td>

                                    <!-- Live Status -->
                                    <td style="text-align:center;">
                                        <?php if (!$rule['is_enabled']): ?>
                                            <span class="text-[10px] font-black px-2 py-1 rounded bg-slate-800 text-slate-400 border border-slate-700 block">معطّل ✕</span>
                                        <?php elseif ($isViolated && $violSeverity === 'RED'): ?>
                                            <span class="text-[10px] font-black px-2 py-1 rounded bg-rose-950/70 text-rose-300 border border-rose-500/50 block animate-pulse">🚨 خطر نشط!</span>
                                        <?php elseif ($isViolated && $violSeverity === 'YELLOW'): ?>
                                            <span class="text-[10px] font-black px-2 py-1 rounded bg-amber-950/70 text-amber-300 border border-amber-500/50 block">⚠️ تنبيه نشط</span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-black px-2 py-1 rounded bg-emerald-950/60 text-emerald-300 border border-emerald-500/40 block">🟢 آمن تماماً</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- On/Off Switch -->
                                    <td style="text-align:center;">
                                        <div class="flex flex-col items-center gap-1">
                                            <label class="ios-switch">
                                                <input type="checkbox" id="tcheck-<?php echo $rule['rule_key']; ?>" <?php echo $rule['is_enabled'] ? 'checked' : ''; ?> onchange="toggleRuleTable('<?php echo $rule['rule_key']; ?>', this.checked)">
                                                <span class="ios-slider"></span>
                                            </label>
                                            <span class="text-[10px] font-bold" id="tstatus-<?php echo $rule['rule_key']; ?>" style="color: <?php echo $rule['is_enabled'] ? '#34d399' : '#64748b'; ?>;">
                                                <?php echo $rule['is_enabled'] ? 'مفعّل' : 'معطّل'; ?>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        <?php endforeach; ?>

        <!-- 🚨 LIVE VIOLATIONS PANEL 🚨 -->
        <div class="section-block" style="--border-color: rgba(244, 63, 94, 0.4);">
            <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3 flex-wrap gap-2">
                <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                    سجل الإنذارات الحية المكتشفة في قاعدة البيانات (Live Violations)
                </h3>
                <span class="text-xs font-bold text-rose-400 bg-rose-500/10 px-3 py-1 rounded-lg border border-rose-500/20" id="live-violations-badge">
                    <?php echo count($logs); ?> إنذار نشط
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="live-violations-grid">
                <?php if (empty($logs)): ?>
                    <div class="col-span-2 text-center p-8 text-slate-400 text-xs border border-dashed border-slate-800 rounded-2xl">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-3xl mb-2 block"></i>
                        جميع العمليات والشاشات آمنة ومطابقة 100% للشروط الرقابية الحالية.
                    </div>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        $isRed = ($log['severity'] ?? '') === 'RED';
                        $bgCls = $isRed ? 'bg-rose-950/40 border-rose-500/40 text-rose-200' : 'bg-amber-950/40 border-amber-500/40 text-amber-200';
                    ?>
                        <div class="p-3.5 rounded-2xl border text-xs font-bold leading-relaxed <?php echo $bgCls; ?> flex justify-between items-center gap-3">
                            <div class="flex-1">
                                <?php echo nl2br(htmlspecialchars($log['message'])); ?>
                            </div>
                            <?php if (!empty($log['screen_file'])): ?>
                                <a href="<?php echo htmlspecialchars($log['screen_file']); ?>" class="px-3 py-1.5 bg-slate-900/80 hover:bg-slate-800 text-white rounded-xl text-[11px] font-black transition border border-white/10 whitespace-nowrap flex items-center gap-1">
                                    معالجة ↗
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            نظام Al-Sajana Smart ERP 2026 · مصفوفة الرقابة والتنبيهات الموحدة العالمية
        </div>

    </div>

    <!-- JavaScript Controllers -->
    <script>
    function toggleRuleTable(ruleKey, isChecked) {
        const formData = new FormData();
        formData.append('action', 'toggle_rule_ajax');
        formData.append('rule_key', ruleKey);
        formData.append('is_enabled', isChecked ? 1 : 0);

        fetch('alarm.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const statusTxt = document.getElementById('tstatus-' + ruleKey);
                if (statusTxt) {
                    statusTxt.textContent = isChecked ? 'مفعّل' : 'معطّل';
                    statusTxt.style.color = isChecked ? '#34d399' : '#64748b';
                }
                updateCounters(res);
                renderLiveViolations(res.logs);
                if (window.ERPAlertsGuard) window.ERPAlertsGuard.loadRules();
            }
        });
    }

    function bulkToggle(category, state) {
        const formData = new FormData();
        formData.append('action', 'bulk_toggle_ajax');
        formData.append('is_enabled', state);
        formData.append('category', category);

        fetch('alarm.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                window.location.reload();
            }
        });
    }

    function saveThresholdsTable(ruleKey) {
        const warn = document.getElementById('twarn-' + ruleKey).value;
        const crit = document.getElementById('tcrit-' + ruleKey).value;

        const formData = new FormData();
        formData.append('action', 'update_thresholds_ajax');
        formData.append('rule_key', ruleKey);
        formData.append('warning_threshold', warn);
        formData.append('critical_threshold', crit);

        fetch('alarm.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const btn = event.target;
                const orig = btn.textContent;
                btn.textContent = '✓';
                btn.classList.add('bg-emerald-600');
                setTimeout(() => {
                    btn.textContent = orig;
                    btn.classList.remove('bg-emerald-600');
                }, 1000);
                refreshLiveEngine();
                if (window.ERPAlertsGuard) window.ERPAlertsGuard.loadRules();
            }
        });
    }

    function filterSectionTab(cat, btn) {
        document.querySelectorAll('#category-tabs .cat-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const sections = document.querySelectorAll('.section-block[data-sec-category]');
        sections.forEach(sec => {
            if (cat === 'ALL' || sec.getAttribute('data-sec-category') === cat) {
                sec.style.display = 'block';
            } else {
                sec.style.display = 'none';
            }
        });
    }

    function searchMatrix(q) {
        const query = q.trim().toLowerCase();
        const rows = document.querySelectorAll('.matrix-row');
        rows.forEach(r => {
            const text = r.getAttribute('data-search') || '';
            r.style.display = (query === '' || text.includes(query)) ? 'table-row' : 'none';
        });
    }

    function refreshLiveEngine() {
        fetch('alarm.php?action=get_live_alerts_json')
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const kpiRed = document.getElementById('kpi-red-count');
                if (kpiRed) kpiRed.textContent = res.red_count;

                const kpiYellow = document.getElementById('kpi-yellow-count');
                if (kpiYellow) kpiYellow.textContent = res.yellow_count;

                const liveBadge = document.getElementById('live-violations-badge');
                if (liveBadge) liveBadge.textContent = res.total + ' إنذار نشط';

                renderLiveViolations(res.alerts);
            }
        });
    }

    function updateCounters(res) {
        const kpiRed = document.getElementById('kpi-red-count');
        if (kpiRed) kpiRed.textContent = res.red_count;

        const kpiYellow = document.getElementById('kpi-yellow-count');
        if (kpiYellow) kpiYellow.textContent = res.yellow_count;

        const kpiEnabled = document.getElementById('kpi-enabled-rules');
        if (kpiEnabled) kpiEnabled.textContent = res.enabled_count + ' / <?php echo count($rules); ?>';

        const liveBadge = document.getElementById('live-violations-badge');
        if (liveBadge) liveBadge.textContent = (res.red_count + res.yellow_count) + ' إنذار نشط';
    }

    function renderLiveViolations(logs) {
        const container = document.getElementById('live-violations-grid');
        if (!container) return;

        if (!logs || logs.length === 0) {
            container.innerHTML = '<div class="col-span-2 text-center p-8 text-slate-400 text-xs border border-dashed border-slate-800 rounded-2xl"><i class="fa-solid fa-circle-check text-emerald-400 text-3xl mb-2 block"></i>جميع العمليات والشاشات آمنة ومطابقة 100% للشروط الرقابية الحالية.</div>';
            return;
        }

        let html = '';
        logs.forEach(l => {
            const isRed = l.severity === 'RED';
            const bgCls = isRed ? 'bg-rose-950/40 border-rose-500/40 text-rose-200' : 'bg-amber-950/40 border-amber-500/40 text-amber-200';
            const linkBtn = l.screen_file ? '<a href="' + l.screen_file + '" class="px-3 py-1.5 bg-slate-900/80 hover:bg-slate-800 text-white rounded-xl text-[11px] font-black transition border border-white/10 whitespace-nowrap flex items-center gap-1">معالجة ↗</a>' : '';
            html += '<div class="p-3.5 rounded-2xl border text-xs font-bold leading-relaxed ' + bgCls + ' flex justify-between items-center gap-3"><div class="flex-1">' + l.message + '</div>' + linkBtn + '</div>';
        });
        container.innerHTML = html;
    }
    </script>
    <script defer src="js/app.js"></script>
</body>
</html>