<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مصفوفة الرقابة والتنبيهات الشاملة V6
// منظومة رقابية متكاملة من 46 قاعدة تنبيه تفصيلية لكافة شاشات النظام
// ====================================================================
$page_title = 'مصفوفة الرقابة وإدارة التنبيهات العالمية';
$header_theme_class = 'header-theme-alarm';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

// -------------------------------------------------------------
// 1. التحرّي وتأكيد وجود جداول التنبيهات وترقية الأعمدة تلقائياً
// -------------------------------------------------------------
function ensureAlertTablesExist($pdo, $tenant_id) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS alert_settings (
            tenant_id INT(11) NOT NULL,
            rule_key VARCHAR(50) NOT NULL,
            rule_name VARCHAR(100) NOT NULL,
            description TEXT,
            is_enabled TINYINT(1) DEFAULT 1,
            warning_threshold DECIMAL(15,2) DEFAULT 0,
            critical_threshold DECIMAL(15,2) DEFAULT 0,
            category VARCHAR(50) DEFAULT 'GENERAL',
            screen_name VARCHAR(100) DEFAULT '',
            screen_file VARCHAR(100) DEFAULT '',
            warn_label VARCHAR(100) DEFAULT '',
            crit_label VARCHAR(100) DEFAULT '',
            unit VARCHAR(20) DEFAULT '',
            alert_preview VARCHAR(255) DEFAULT '',
            PRIMARY KEY (tenant_id, rule_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $cols = $pdo->query("DESCRIBE alert_settings")->fetchAll(PDO::FETCH_COLUMN);
        $neededCols = [
            'category' => "VARCHAR(50) DEFAULT 'GENERAL'",
            'screen_name' => "VARCHAR(100) DEFAULT ''",
            'screen_file' => "VARCHAR(100) DEFAULT ''",
            'warn_label' => "VARCHAR(100) DEFAULT ''",
            'crit_label' => "VARCHAR(100) DEFAULT ''",
            'unit' => "VARCHAR(20) DEFAULT ''",
            'alert_preview' => "VARCHAR(255) DEFAULT ''"
        ];
        foreach ($neededCols as $col => $type) {
            if (!in_array($col, $cols)) {
                $pdo->exec("ALTER TABLE alert_settings ADD COLUMN {$col} {$type}");
            }
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS alert_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT(11) NOT NULL,
            rule_key VARCHAR(50) NOT NULL,
            severity VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            screen_file VARCHAR(100) DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $allRules = [
            [
                'rule_key' => 'sales_below_cost',
                'rule_name' => 'البيع بأقل من سعر التكلفة أو سعر الشراء (WAC)',
                'screen_name' => 'شاشة المبيعات المباشرة وفواتير البيع',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه رقابي في شاشة المبيعات: سعر بيع الصنف أقل من سعر التكلفة/الشراء المعتمد!',
                'description' => 'يراقب سعر البيع المدخل في الفاتورة لحظياً، وإذا كان أقل من متوسط تكلفة الصنف أو سعر شرائه يظهر تنبيه فوري لمنع الخسائر.',
                'warn_label' => 'الحد الأدنى المسموح للتكلفة',
                'crit_label' => 'حد الخسارة الحرج للعمليات',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 3,
                'unit' => 'عملية',
            ],
            [
                'rule_key' => 'sales_price_override',
                'rule_name' => 'تعديل وتغيير سعر البيع المسجل للصنف يدوياً',
                'screen_name' => 'شاشات المبيعات والكاشير ونقاط البيع',
                'screen_file' => 'direct-sales.php / pos-invoice-print.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه تسعير: تم تعديل السعر الافتراضي المعتمد للصنف يدوياً أثناء إصدار الفاتورة!',
                'description' => 'يراقب أي تغيير يدوي يقوم به البائع أو الكاشير على السعر الرسمي للصنف المحدد في قائمة الأسعار.',
                'warn_label' => 'نسبة التغيير التحذيرية المسموحة',
                'crit_label' => 'نسبة التغيير الحرجة للإيقاف',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 15,
                'unit' => '%',
            ],
            [
                'rule_key' => 'sales_below_wholesale',
                'rule_name' => 'البيع بأقل من سعر الجملة المعتمد للكميات الكبيرة',
                'screen_name' => 'المبيعات المباشرة وفواتير الجملة',
                'screen_file' => 'sales.php / pro-invoice.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه جملة: السعر المدخل يقل عن الحد الأدنى لسعر بيع الجملة المعتمد للكمية المطلوبة!',
                'description' => 'ينبه عند بيع كميات تجارية كبيرة بسعر يقل عن تسعيرة الجملة الرسمية لحماية هوامش الأرباح.',
                'warn_label' => 'الكمية الدنيا لاعتبارها جملة',
                'crit_label' => 'الحد الحرج للخصم من سعر الجملة',
                'is_enabled' => 1,
                'warning_threshold' => 50,
                'critical_threshold' => 5,
                'unit' => 'قطعة',
            ],
            [
                'rule_key' => 'sales_qty_exceeds_stock',
                'rule_name' => 'البيع بكمية تتجاوز الرصيد المتوفر بالمستودع',
                'screen_name' => 'شاشة المبيعات المباشرة ونقاط البيع POS',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه مخزني في شاشة المبيعات: الكمية المدخلة تتجاوز الرصيد المتوفر فعلياً بالمستودع!',
                'description' => 'يقارن الكمية المطلوبة بالرصيد الفعلي المتوفر على الرفوف والعنابر وينبه البائع فوراً قبل حفظ الفاتورة لمنع العجز.',
                'warn_label' => 'حد العجز التحذيري المسموح',
                'crit_label' => 'حد العجز الحرج للإيقاف',
                'is_enabled' => 1,
                'warning_threshold' => 0,
                'critical_threshold' => 0,
                'unit' => 'وحدة',
            ],
            [
                'rule_key' => 'sales_negative_stock',
                'rule_name' => 'إصدار فاتورة بيع برصيد مخزني سالب',
                'screen_name' => 'شاشات المبيعات وفواتير الكاشير',
                'screen_file' => 'direct-sales.php / pro-invoice.php',
                'category' => 'SALES',
                'alert_preview' => '🚨 خطر مخزني حرج: رصيد الصنف صفر أو سالب بالمستودع ويُمنع البيع منعاً لتشويه التكلفة!',
                'description' => 'يمنع بيع أي صنف رصيده صفر أو أقل من الصفر لمنع حدوث أرصدة سالبة وتشويه حساب تكلفة المبيعات في القوائم المالية.',
                'warn_label' => 'الرصيد السالب التحذيري',
                'crit_label' => 'حد الإيقاف التام للبيع',
                'is_enabled' => 1,
                'warning_threshold' => 0,
                'critical_threshold' => 0,
                'unit' => 'وحدة',
            ],
            [
                'rule_key' => 'sales_credit_limit_exceeded',
                'rule_name' => 'تجاوز الحد والسقف الائتماني للعميل الآجل',
                'screen_name' => 'المبيعات الآجلة والعقود والفوترة',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'SALES',
                'alert_preview' => '🚨 تنبيه ائتماني في شاشة المبيعات: العميل تجاوز السقف الائتماني المسموح به للمديونية!',
                'description' => 'يحسب إجمالي مديونية العميل مضافاً إليها مبلغ الفاتورة الحالية، وينبه إذا تجاوز سقف الائتمان المعتمد بحسابه.',
                'warn_label' => 'نسبة استهلاك الائتمان التحذيرية',
                'crit_label' => 'نسبة الإيقاف التام للبيع الآجل',
                'is_enabled' => 1,
                'warning_threshold' => 80,
                'critical_threshold' => 100,
                'unit' => '%',
            ],
            [
                'rule_key' => 'sales_overdue_debt_block',
                'rule_name' => 'حظر البيع للعملاء الذين لديهم فواتير سابقة متأخرة',
                'screen_name' => 'شاشة المبيعات المباشرة',
                'screen_file' => 'direct-sales.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه تحصيل: العميل لديه فواتير آجلة سابقة تجاوزت فترة السماح ولم يتم سدادها!',
                'description' => 'يكشف فواتير العميل السابقة غير المسددة، وينبه إذا تجاوزت تاريخ الاستحقاق بأكثر من فترة السماح المحددة.',
                'warn_label' => 'أيام التأخر التحذيرية المسموحة',
                'crit_label' => 'أيام التأخر الحرجة لإيقاف العميل',
                'is_enabled' => 1,
                'warning_threshold' => 30,
                'critical_threshold' => 60,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'sales_discount_limit_breach',
                'rule_name' => 'تجاوز نسبة الخصم التجاري المسموحة للبائع',
                'screen_name' => 'شاشات المبيعات وفواتير الكاشير',
                'screen_file' => 'direct-sales.php / pos-invoice-print.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه الصلاحيات: نسبة الخصم الممنوحة تتجاوز الحد الأقصى المسموح لموظف المبيعات!',
                'description' => 'يراقب نسبة الخصم المدخلة على الفاتورة، وينبه عند تجاوزها الصلاحية المحددة للبائع دون إذن الإدارة.',
                'warn_label' => 'الحد الأقصى للخصم التحذيري',
                'crit_label' => 'سقف الخصم الحرج الممنوع',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 10,
                'unit' => '%',
            ],
            [
                'rule_key' => 'sales_missing_rep',
                'rule_name' => 'إصدار فاتورة بيع دون تحديد مندوب المبيعات أو البائع',
                'screen_name' => 'شاشات المبيعات وإصدار الفواتير',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه إداري: لم يتم تحديد مندوب المبيعات أو البائع المسؤول عن الفاتورة لاحتساب العمولات!',
                'description' => 'ينبه بضرورة ربط كل عملية بيع بمندوب أو بائع لضمان صحة تقارير الأداء وحساب العمولات بدقة.',
                'warn_label' => 'عدد الفواتير غير المربوطة التحذيري',
                'crit_label' => 'حد الإلزام الفوري',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'فاتورة',
            ],
            [
                'rule_key' => 'sales_blocked_customer',
                'rule_name' => 'محاولة البيع لعميل محظور أو موقوف إدارياً',
                'screen_name' => 'شاشات المبيعات وإصدار الفواتير',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'SALES',
                'alert_preview' => '🚫 حظر مبيعات: هذا العميل موقوف ومحظور التعامل معه بموجب قرار الإدارة المالية!',
                'description' => 'يحظر إصدار أي فاتورة أو سند بيع لعميل تم وضع علامة إيقاف أو تجميد في بطاقة حسابه.',
                'warn_label' => 'عدد محاولات الاختراق',
                'crit_label' => 'حد الحظر الفوري التام',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'محاولة',
            ],
            [
                'rule_key' => 'sales_margin_danger',
                'rule_name' => 'تدني هامش الربح الإجمالي للفاتورة عن الحد الآمن',
                'screen_name' => 'المبيعات والفوترة المتقدمة',
                'screen_file' => 'pro-invoice.php / sales.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه الربحية: هامش ربح الفاتورة الإجمالي متدنٍ جداً ويقترب من نقطة التعادل/الخسارة!',
                'description' => 'يحسب نسبة الربح الصافي للفاتورة ككل وينبه إذا كانت أقل من النسبة الآمنة للنشاط التجاري.',
                'warn_label' => 'أدنى نسبة ربح مقبولة',
                'crit_label' => 'نسبة الربح الحرجة الخاسرة',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 2,
                'unit' => '%',
            ],
            [
                'rule_key' => 'inv_low_stock',
                'rule_name' => 'الحد الأدنى للأصناف ونقطة إعادة الطلب (Reorder Point)',
                'screen_name' => 'تقرير وإدارة المخزون والمستودعات',
                'screen_file' => 'inventory-report.php / smart_inventory_report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه مخزني: رصيد الصنف وصل إلى حد الطلب الأدنى ويجب بدء إجراءات التوريد!',
                'description' => 'إشعار مبكر ومستمر للأصناف التي اقترب رصيدها من النفاد لإصدار طلبات شراء قبل توقف المبيعات.',
                'warn_label' => 'الحد الأدنى الآمن للطلب',
                'crit_label' => 'الحد الأدنى الحرج وشيك النفاد',
                'is_enabled' => 1,
                'warning_threshold' => 10,
                'critical_threshold' => 5,
                'unit' => 'كمية',
            ],
            [
                'rule_key' => 'inv_critical_zero_stock',
                'rule_name' => 'النفاد التام لرصيد الصنف بالمستودع (Zero Stock)',
                'screen_name' => 'حركة المخزون والعنابر',
                'screen_file' => 'inventory-report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '🚨 إنذار نفاد كامل: رصيد الصنف أصبح صفراً (0 وحدة) في جميع العنابر والمستودعات!',
                'description' => 'إنذار حرج يعلن نفاد الصنف تماماً لمنع ضياع فرص البيع وتوجيه مسؤولي التوريد بسرعة تأمينه.',
                'warn_label' => 'حد الخطر الوشيك للكمية',
                'crit_label' => 'حد النفاد التام الحرج (صفر)',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 0,
                'unit' => 'كمية',
            ],
            [
                'rule_key' => 'inv_overstock_capacity',
                'rule_name' => 'تجاوز الحد الأعلى للمخزون (Max Stock / Overstock)',
                'screen_name' => 'المخزون الذكي ومراقبة السعة',
                'screen_file' => 'smart_inventory_report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه فائض مخزني: كمية الصنف تجاوزت السقف الأعلى للتخزين وتتسبب في تجميد السيولة!',
                'description' => 'ينبه عند تكدس كميات كبيرة من الصنف تفوق الطاقة الاستيعابية للتخزين أو تؤدي لتجميد السيولة النقدية.',
                'warn_label' => 'الحد الأعلى لسعة التخزين',
                'crit_label' => 'الحد الأقصى الحرج للتكدس',
                'is_enabled' => 1,
                'warning_threshold' => 300,
                'critical_threshold' => 500,
                'unit' => 'وحدة',
            ],
            [
                'rule_key' => 'pur_price_hike_alert',
                'rule_name' => 'ارتفاع سعر الشراء عن آخر توريد مسجل من المورد',
                'screen_name' => 'شاشة المشتريات وفواتير التوريد والمصانع',
                'screen_file' => 'purchase.php / purchase-order.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه تكلفة: سعر شراء الصنف في هذه الفاتورة أعلى من آخر سعر شراء سابق مسجل بالنظام!',
                'description' => 'يقارن سعر الشراء المدخل بآخر سعر شراء مسجل لنفس الصنف وينبه الإدارة بنسبة الزيادة السعرية.',
                'warn_label' => 'نسبة الزيادة السعرية التحذيرية',
                'crit_label' => 'نسبة الزيادة الحرجة غير المقبولة',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 15,
                'unit' => '%',
            ],
            [
                'rule_key' => 'pur_order_qty_mismatch',
                'rule_name' => 'فروقات الكميات المستلمة بين أمر الشراء وفاتورة التوريد',
                'screen_name' => 'استلام البضائع وأوامر الشراء',
                'screen_file' => 'purchase-order.php / purchase.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه استلام: الكمية المستلمة في فاتورة الشراء تختلف عن الكمية المتفق عليها بأمر الشراء!',
                'description' => 'يكشف أي تباين بالزيادة أو النقصان بين الكميات المعتمدة بأمر الشراء والكمية الفعلية الموردة.',
                'warn_label' => 'نسبة التباين التحذيرية المسموحة',
                'crit_label' => 'نسبة التباين الحرجة للإيقاف',
                'is_enabled' => 1,
                'warning_threshold' => 2,
                'critical_threshold' => 10,
                'unit' => '%',
            ],
            [
                'rule_key' => 'pur_unsettled_advances',
                'rule_name' => 'دفعات مقدمة للموردين معلقة لفترة طويلة دون استلام بضاعة',
                'screen_name' => 'حسابات الموردين والدفعات المقدمة',
                'screen_file' => 'purchase.php / single-cash-payment.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه مالي: توجد مبالغ مدفوعة مقدماً لموردين تجاوزت المدة المتفق عليها دون توريد بضاعة!',
                'description' => 'يراقب الدفعات النقدية المقدمة للمصانع والموردين وينبه إذا تأخر توريد البضاعة المقابلة لها.',
                'warn_label' => 'أيام التعليق التحذيرية',
                'crit_label' => 'أيام التعليق الحرجة للمطالبة',
                'is_enabled' => 1,
                'warning_threshold' => 15,
                'critical_threshold' => 30,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_deadstock',
                'rule_name' => 'ركود البضاعة وجمود حركة الأصناف (Deadstock)',
                'screen_name' => 'المخزون الذكي والأصناف الراكدة',
                'screen_file' => 'smart_inventory_report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه ركود: بضاعة راكدة في المخزن لفترة طويلة دون حركة بيع وتتطلب تصريفاً عاجلاً!',
                'description' => 'يكشف الأصناف التي ظلت مخزنة لفترات طويلة دون أي حركة سحب بيعية لتفادي تلفها وتقادمها.',
                'warn_label' => 'أيام الركود التحذيرية',
                'crit_label' => 'أيام الركود الحرجة للتصريف',
                'is_enabled' => 1,
                'warning_threshold' => 60,
                'critical_threshold' => 120,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_fast_moving_surge',
                'rule_name' => 'سحب وسرعة دوران عالية غير معتادة على الصنف (سحب سريع)',
                'screen_name' => 'تحليلات حركة المخازن وسرعة الدوران',
                'screen_file' => 'inventory-report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '🚀 تنبيه سحب سريع: الصنف يشهد إقبالاً وسحباً متسارعاً غير مسبوق ويتطلب طلباً استباقياً!',
                'description' => 'خوارزمية ذكية ترصد الأصناف ذات السحب البيعي الفجائي المتسارع لإرسال طلب توريد قبل نفادها المفاجئ.',
                'warn_label' => 'نسبة تسارع السحب التحذيرية',
                'crit_label' => 'نسبة السحب القصوى الاستثنائية',
                'is_enabled' => 1,
                'warning_threshold' => 150,
                'critical_threshold' => 250,
                'unit' => '%',
            ],
            [
                'rule_key' => 'inv_slow_moving',
                'rule_name' => 'أصناف بطيئة الحركة وضعيفة السحب التجاري',
                'screen_name' => 'تحليل المخزون ومعدل الدوران',
                'screen_file' => 'smart_inventory_report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه بطء دوران: معدل سحب الصنف ضعيف جداً ويتطلب عروض ترويجية أو مراجعة التسعير!',
                'description' => 'ينبه بالأصناف التي انخفض معدل دورانها التجاري لمراجعة تسعيرها وتنشيط مبيعاتها.',
                'warn_label' => 'أيام ضعف السحب التحذيرية',
                'crit_label' => 'أيام الجمود التجاري الحرجة',
                'is_enabled' => 1,
                'warning_threshold' => 45,
                'critical_threshold' => 90,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_expiring_soon',
                'rule_name' => 'قرب انتهاء تاريخ الصلاحية للأصناف المخزنة',
                'screen_name' => 'إدارة تواريخ الصلاحية والمخازن',
                'screen_file' => 'inventory-report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '🚨 إنذار صلاحية: أصناف تقترب فترة صلاحيتها من الانتهاء ويجب إعادتها للمورد أو تصريفها!',
                'description' => 'متابعة دورية لتفادي تلف المنتجات ذات الصلاحية بإنذار مبكر قبل تاريخ الانتهاء بمدة كافية.',
                'warn_label' => 'أيام الصلاحية التحذيرية المتبقية',
                'crit_label' => 'أيام الصلاحية الحرجة للوقف',
                'is_enabled' => 1,
                'warning_threshold' => 60,
                'critical_threshold' => 30,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_stocktake_variance',
                'rule_name' => 'وجود فروقات وعجز بين الجرد الفعلي والدفتري',
                'screen_name' => 'تسويات وجرد المخازن السنوية والدورية',
                'screen_file' => 'inventory-report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '🚨 تنبيه جرد: تباين غير مطابق بين الجرد الفعلي على الرفوف والرصيد الدفتري المسجل!',
                'description' => 'يكشف التباين والعجز بين كميات الجرد الفعلي بالمخازن والرصيد المحاسبي بالنظام لاتخاذ إجراء فوري.',
                'warn_label' => 'نسبة التباين التحذيرية المقبولة',
                'crit_label' => 'نسبة العجز الحرجة للتحقيق',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 5,
                'unit' => '%',
            ],
            [
                'rule_key' => 'supply_supplier_delay',
                'rule_name' => 'تأخر استلام طلبيات الشراء من الموردين والمصانع',
                'screen_name' => 'المشتريات ومتابعة أوامر التوريد',
                'screen_file' => 'purchase.php / purchase-order.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه توريد: أمر الشراء تجاوز فترة التوريد المتوقعة دون وصول البضاعة من المصنع!',
                'description' => 'مراقبة أوامر الشراء المعلقة التي تجاوزت موعد التسليم المحدد ($Lead Time$) دون توريد.',
                'warn_label' => 'أيام التأخر التحذيرية',
                'crit_label' => 'أيام التأخر الحرجة للمورد',
                'is_enabled' => 1,
                'warning_threshold' => 7,
                'critical_threshold' => 14,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'fin_negative_cash_balance',
                'rule_name' => 'الرصيد السالب للخزائن والصناديق والحسابات البنكية',
                'screen_name' => 'سندات الصرف الفردية والمتعددة والخزينة',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'category' => 'FINANCE',
                'alert_preview' => '🚨 عجز نقدي حرج: رصيد الخزينة/الحساب البنكي أصبح سالباً ويُمنع صرف السند!',
                'description' => 'إنذار فوري لمنع صرف مبالغ تتسبب في جعل رصيد الخزينة أو الحساب البنكي سالباً.',
                'warn_label' => 'الرصيد التحذيري الأدنى',
                'crit_label' => 'حد العجز الحرج للإيقاف',
                'is_enabled' => 1,
                'warning_threshold' => 0,
                'critical_threshold' => 0,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'fin_cashier_shift_variance',
                'rule_name' => 'فروقات وعجز إغلاق وردية الكاشير وجرد الصندوق (Z-Report)',
                'screen_name' => 'إغلاق الوردية وجرد الصناديق اليومي',
                'screen_file' => 'vouchers-hub.php / single-cash-payment.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ تنبيه عجز صندوق: وجود فرق مالي بين النقد الفعلي بالدرج وإجمالي مبيعات الوردية المسجلة!',
                'description' => 'يكشف العجز أو الزيادة النقدية عند تسليم وردية الكاشير ومطابقة النقدية مع تقرير الـ Z-Report.',
                'warn_label' => 'مبلغ الفارق التحذيري المسموح',
                'crit_label' => 'حد العجز الحرج للتحقيق الفوري',
                'is_enabled' => 1,
                'warning_threshold' => 100,
                'critical_threshold' => 1000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'fin_missing_voucher_attachment',
                'rule_name' => 'سندات صرف مالي بدون إرفاق مستند أو صورة فاتورة المصروف',
                'screen_name' => 'سندات الصرف المالي والعهدة',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ تنبيه تدقيق: سند الصرف تم حفظه دون إرفاق المستند المؤيد أو صورة فاتورة المصروف!',
                'description' => 'يراقب سندات الصرف للمصروفات والعهد ويلزم الموظف بإرفاق صورة المستند لإحكام الرقابة.',
                'warn_label' => 'المبلغ الذي يوجب الإرفاق التحذيري',
                'crit_label' => 'المبلغ الحرج للإلزام التام',
                'is_enabled' => 1,
                'warning_threshold' => 5000,
                'critical_threshold' => 50000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'fin_recurring_payable_due',
                'rule_name' => 'اقتراب موعد سداد التزام شهري دوري (إيجار، أقساط، رواتب)',
                'screen_name' => 'التدفقات النقدية والمصروفات الدورية',
                'screen_file' => 'cashflow_modern.php / vouchers-hub.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ تنبيه التزامات: اقترب موعد سداد التزام دوري مستحق (إيجار مقرات، أقساط، مستحقات رواتب)!',
                'description' => 'تذكير مبكر قبل موعد سداد الالتزامات الدورية الثابتة لتجهيز السيولة النقدية الكافية.',
                'warn_label' => 'أيام التنبيه المسبق قبل الموعد',
                'crit_label' => 'أيام الإنذار الحرج الوشيك',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 2,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'fin_min_liquidity_threshold',
                'rule_name' => 'الحد الأدنى للسيولة النقدية التشغيلية (Safety Cash)',
                'screen_name' => 'إدارة الخزينة والسيولة والتدفقات النقدية',
                'screen_file' => 'cashflow_modern.php / vouchers-hub.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ تنبيه سيولة: رصيد النقد بالخزينة الرئيسية هبط عن حد الأمان للمصروفات اليومية!',
                'description' => 'تنبيه للإدارة المالية عند هبوط النقد المتوفر بالخزينة عن الحد الأدنى اللازم للالتزامات التشغيلية.',
                'warn_label' => 'حد أمان السيولة التحذيري',
                'crit_label' => 'حد الخطر النقدي الحرج',
                'is_enabled' => 1,
                'warning_threshold' => 50000,
                'critical_threshold' => 10000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'fin_duplicate_transfer_no',
                'rule_name' => 'تكرار رقم التحويلة البنكية في سندات أخرى',
                'screen_name' => 'سندات الصرف والقبض والتحويلات البنكية',
                'screen_file' => 'single-cash-payment.php / single-cash-receipt.php',
                'category' => 'FINANCE',
                'alert_preview' => '🚨 تحذير مالي: رقم التحويلة البنكية مسجل مسبقاً في سند آخر لمنع الازدواجية والتلاعب!',
                'description' => 'فحص فوري يمنع تكرار استخدام نفس رقم إشعار التحويل البنكي في أكثر من سند مالي.',
                'warn_label' => 'مرات التكرار التحذيرية',
                'crit_label' => 'حد الحظر الفوري المباشر',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 2,
                'unit' => 'تكرار',
            ],
            [
                'rule_key' => 'fin_duplicate_cheque_no',
                'rule_name' => 'تكرار رقم الشيك البنكي في المعاملات المالية',
                'screen_name' => 'سندات الشيكات والمدفوعات والمقبوضات',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'category' => 'FINANCE',
                'alert_preview' => '🚨 تحذير مصرفي: رقم الشيك تم استخدامه مسبقاً في معاملة أخرى لمنع الازدواجية!',
                'description' => 'إنذار فوري عند إدخال رقم شيك تم استخدامه مسبقاً في سند أو حساب مصرفي آخر.',
                'warn_label' => 'مرات التكرار التحذيرية',
                'crit_label' => 'حد الحظر الفوري المباشر',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 2,
                'unit' => 'تكرار',
            ],
            [
                'rule_key' => 'fin_overdue_checks',
                'rule_name' => 'تأخر تحصيل أو صرف الشيكات المستحقة (الشيكات الراكدة)',
                'screen_name' => 'إدارة الشيكات والسندات البنكية',
                'screen_file' => 'vouchers-hub.php / alarm.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ تنبيه شيكات: شيكات حان أو تجاوزت تاريخ استحقاقها البنكي دون تحصيل أو صرف!',
                'description' => 'مراقبة الشيكات الصادرة والواردة المعلقة وتنبيه الإدارة المالية بمواعيد استحقاقها وتأخرها.',
                'warn_label' => 'أيام التنبيه قبل الاستحقاق',
                'crit_label' => 'أيام التأخر الحرجة بعد الاستحقاق',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 3,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'fin_bounced_cheques',
                'rule_name' => 'الشيكات المرتدة أو المرفوضة من المصارف',
                'screen_name' => 'متابعة الشيكات والتحصيل ومخاطر العملاء',
                'screen_file' => 'vouchers-hub.php / customer_exposure.php',
                'category' => 'FINANCE',
                'alert_preview' => '🚨 إنذار حرج: شيك مرتد من البنك! يجب اتخاذ الإجراء القانوني وتجميد حساب العميل فوراً!',
                'description' => 'إشعار فوري بالشيكات المرتدة من البنك لاتخاذ الإجراء القانوني الفوري وحظر حساب العميل.',
                'warn_label' => 'عدد الشيكات المرتدة التحذيري',
                'crit_label' => 'حد الإيقاف الفوري التام',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'شيك',
            ],
            [
                'rule_key' => 'fin_liquidity_deficit_forecast',
                'rule_name' => 'توقع عجز السيولة النقدية (الالتزامات > السيولة المتوفرة)',
                'screen_name' => 'التدفقات النقدية والموازنة التقديرية',
                'screen_file' => 'cashflow_modern.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ إنذار تنبؤي: عجز سيولة متوقع خلال الأيام القادمة (الشيكات والالتزامات تفوق الرصيد المتاح)!',
                'description' => 'تحليل تنبؤي بوجود عجز سيولة إذا كانت الشيكات والالتزامات المستحقة قريباً تفوق الرصيد المتوفر.',
                'warn_label' => 'أفق التوقع التحذيري',
                'crit_label' => 'أفق التوقع الحرج الوشيك',
                'is_enabled' => 1,
                'warning_threshold' => 7,
                'critical_threshold' => 3,
                'unit' => 'أيام مقبلة',
            ],
            [
                'rule_key' => 'fin_fx_rate_discrepancy',
                'rule_name' => 'تباين سعر الصرف المستخدم بالسند عن سعر النظام',
                'screen_name' => 'سندات العملات الأجنبية والصرف المالي',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ تنبيه صرف: سعر الصرف المدخل بالسند يختلف بنسبة غير طبيعية عن سعر الصرف العام للنظام!',
                'description' => 'تنبيه عند تسجيل سند مالي بسعر صرف يختلف بنسبة تتجاوز الحد المسموح عن سعر النظام المعتمد.',
                'warn_label' => 'نسبة التباين التحذيرية المسموحة',
                'crit_label' => 'نسبة التباين الحرجة للإيقاف',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 6,
                'unit' => '%',
            ],
            [
                'rule_key' => 'fin_large_cash_outflow',
                'rule_name' => 'سندات صرف ذات مبالغ نقدية ضخمة واستثنائية',
                'screen_name' => 'سندات الصرف المالي والمدفوعات',
                'screen_file' => 'single-cash-payment.php / multi-cash-payment.php',
                'category' => 'FINANCE',
                'alert_preview' => 'ℹ️ إشعار تدقيق: سند صرف بمبلغ ضخم غير معتاد ويتطلب اعتماداً خاصاً من المدير المالي!',
                'description' => 'إشعار فوري لمدير النظام عند إصدار سند صرف نقدي يتجاوز السقف العادي للمصروفات اليومية.',
                'warn_label' => 'الحد التحذيري للمبلغ',
                'crit_label' => 'الحد الحرج للمبلغ الاستثنائي',
                'is_enabled' => 1,
                'warning_threshold' => 500000,
                'critical_threshold' => 1000000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'credit_aging_danger',
                'rule_name' => 'تراكم وأعمار الديون غير المسددة للعملاء (Aging Debts)',
                'screen_name' => 'أعمار الديون والائتمان والتحصيل',
                'screen_file' => 'credit_limits_dashboard.php / customer_exposure.php',
                'category' => 'CREDIT',
                'alert_preview' => '⚠️ إنذار ديون: مديونيات متراكمة لعملاء تجاوزت فترات ائتمان حرجة دون سداد لتفادي الديون الهالكة!',
                'description' => 'إنذار بأرصدة العملاء المدينة التي تجاوزت فترات ائتمان حرجة دون سداد لتفادي الديون الهالكة.',
                'warn_label' => 'عمر الدين التحذيري',
                'crit_label' => 'عمر الدين الحرج الهالك',
                'is_enabled' => 1,
                'warning_threshold' => 60,
                'critical_threshold' => 90,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'customer_inactive_churn',
                'rule_name' => 'انقطاع وركود العملاء الدائمين والمميزين (VIP Churn)',
                'screen_name' => 'تحليل ولاء وركود العملاء الدائمين',
                'screen_file' => 'customer_churn.php / customer_exposure.php',
                'category' => 'CREDIT',
                'alert_preview' => '⚠️ تنبيه عملاء: عميل دائم/رئيسي انقطع عن الشراء منذ فترة غير معتادة ويتطلب متابعة فورية!',
                'description' => 'تنبيه إدارة المبيعات بأن عميلاً رئيسياً توقف عن الشراء منذ فترة غير معتادة لمتابعته وإعادته.',
                'warn_label' => 'أيام الانقطاع التحذيرية',
                'crit_label' => 'أيام الركود الحرجة للعميل',
                'is_enabled' => 1,
                'warning_threshold' => 30,
                'critical_threshold' => 60,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'customer_high_returns',
                'rule_name' => 'ارتفاع غير طبيعي في نسبة مرتجعات العميل',
                'screen_name' => 'مرتجعات المبيعات وتحليل جودة العملاء',
                'screen_file' => 'sales.php / direct-sales.php',
                'category' => 'CREDIT',
                'alert_preview' => '⚠️ تنبيه مرتجعات: نسبة مرتجعات هذا العميل مرتفعة جداً وتتطلب مراجعة الجودة والتسعير!',
                'description' => 'تنبيه عند تجاوز نسبة مرتجعات مبيعات عميل معين للحد المقبول لمراجعة جودة التوريد والأسعار.',
                'warn_label' => 'نسبة المرتجعات التحذيرية',
                'crit_label' => 'نسبة المرتجعات الحرجة للمراجعة',
                'is_enabled' => 1,
                'warning_threshold' => 10,
                'critical_threshold' => 20,
                'unit' => '%',
            ],
            [
                'rule_key' => 'supplier_overdue_balance',
                'rule_name' => 'مستحقات متأخرة لمورد تجاوزت تاريخ الاستحقاق',
                'screen_name' => 'حسابات الموردين والمشتريات والمصانع',
                'screen_file' => 'purchase.php / master-data.php',
                'category' => 'CREDIT',
                'alert_preview' => '⚠️ تنبيه موردين: مستحقات متأخرة لمورد تجاوزت تاريخ السداد المعتمد وتستوجب الصرف!',
                'description' => 'تنبيه بالأرصدة الدائنة للموردين التي تجاوزت فترة السداد المتفق عليها لتفادي وقف التوريد والمصانع.',
                'warn_label' => 'أيام التأخر التحذيرية للسداد',
                'crit_label' => 'أيام التأخر الحرجة لوقف التوريد',
                'is_enabled' => 1,
                'warning_threshold' => 30,
                'critical_threshold' => 60,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'audit_void_after_print',
                'rule_name' => 'إلغاء أو حذف فاتورة مبيعات بعد طباعتها للعميل (Post-Print Void)',
                'screen_name' => 'سجل الرقابة ونقاط البيع POS',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚨 إنذار احتيال حرج: تم إلغاء فاتورة مبيعات بعد طباعة الإيصال للعميل ويستوجب التحقيق!',
                'description' => 'يرصد أي محاولة لحذف أو إلغاء فاتورة بعد طباعتها لمنع التلاعب بالنقدية المستلمة من العملاء.',
                'warn_label' => 'عدد محاولات الإلغاء التحذيري',
                'crit_label' => 'حد الخطر الحرج للتحقيق الفوري',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'محاولة',
            ],
            [
                'rule_key' => 'audit_manual_jv_on_cash',
                'rule_name' => 'إجراء قيد محاسبي يدوي مباشر على حسابات الخزينة أو البنك',
                'screen_name' => 'دفتر القيود اليومية والأستاذ العام',
                'screen_file' => 'general_ledger_v2.php / vouchers-hub.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚨 تنبيه تدقيق: محاولة إجراء قيد يومية يدوي مباشر على حساب نقدي دون استخدام سند صرف/قبض رسمي!',
                'description' => 'يلزم المحاسبين بإجراء حركات النقدية عبر سندات رسمية ويمنع تجاوز الدورة المحاسبية عبر قيود التسوية.',
                'warn_label' => 'عدد القيود اليدوية المسموحة',
                'crit_label' => 'حد الإلزام بالسندات الرسمية',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'قيد',
            ],
            [
                'rule_key' => 'sec_voucher_audit_edit_delete',
                'rule_name' => 'تعديل أو حذف أي سند مالي أو فاتورة سابقة مرحلة',
                'screen_name' => 'سجل التدقيق المحاسبي وسندات الصرف والقبض',
                'screen_file' => 'vouchers-hub.php / single-cash-payment.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚨 إشعار تدقيق رقابي: تم تعديل أو حذف مستند مالي بعد ترحيله واعتماده دفترياً!',
                'description' => 'إشعار رقابي فوري للإدارة عند تعديل أو إلغاء أي مستند مالي بعد ترحيله واعتماده دفترياً.',
                'warn_label' => 'عدد التعديلات المسموح بها',
                'crit_label' => 'حد الخطر الفوري التام',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'عملية',
            ],
            [
                'rule_key' => 'sec_unposted_draft_vouchers',
                'rule_name' => 'السندات والفواتير المعلقة أو المسودة غير المرحلة',
                'screen_name' => 'السندات المعلقة والفواتير غير المرحلة',
                'screen_file' => 'vouchers-hub.php / sales.php',
                'category' => 'SECURITY',
                'alert_preview' => '⚠️ تنبيه ترحيل: مستندات مالية وفواتير ظلت بحالة مسودة لأكثر من 24 ساعة دون ترحيل!',
                'description' => 'مراقبة المستندات المالية التي ظلت بحالة مسودة لأكثر من 24 ساعة دون ترحيل للقوائم المالية.',
                'warn_label' => 'عدد السندات المعلقة التحذيري',
                'crit_label' => 'الحد الحرج للسندات غير المرحلة',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 10,
                'unit' => 'سند معلق',
            ],
            [
                'rule_key' => 'sec_missed_db_backup',
                'rule_name' => 'تأخر النسخ الاحتياطي لقاعدة البيانات (> 24 ساعة)',
                'screen_name' => 'لوحة التحكم وإدارة النسخ الاحتياطي للأمان',
                'screen_file' => 'settings.php / magaiees_unified_dashboard.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚨 إنذار أمان حرج: لم يتم إنشاء نسخة احتياطية لقاعدة البيانات خلال آخر 24 ساعة!',
                'description' => 'إنذار أمني حرج في لوحة التحكم عند عدم تنفيذ نسخة احتياطية لقاعدة البيانات خلال آخر 24 ساعة.',
                'warn_label' => 'أيام التأخر التحذيرية للنسخ',
                'crit_label' => 'أيام التأخر الحرجة للنسخ الاحتياطي',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 2,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'sec_unbalanced_entries',
                'rule_name' => 'عدم اتزان القيود المحاسبية (المدين != الدائن)',
                'screen_name' => 'دفتر الأستاذ العام وميزان المراجعة المحاسبي',
                'screen_file' => 'general_ledger_v2.php / analytics.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚨 إنذار محاسبي حرج: وجود قيود غير متزنة (المدين لا يساوي الدائن) في القوائم!',
                'description' => 'فحص فوري لأي قيد محاسبي أو سند غير متوازن في ميزان المراجعة ودفتر الأستاذ العام.',
                'warn_label' => 'عدد القيود غير المتزنة التحذيري',
                'crit_label' => 'حد الخطر المحاسبي الحرج',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'قيد غير متزن',
            ],
            [
                'rule_key' => 'sec_suspicious_login',
                'rule_name' => 'محاولات دخول فاشلة أو غير مصرح بها للنظام',
                'screen_name' => 'شاشة تسجيل الدخول وحماية النظام',
                'screen_file' => 'login.php / settings.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚨 تنبيه أمني: تكرار محاولات تسجيل دخول غير مصرح بها بكلمات مرور خاطئة!',
                'description' => 'تنبيه أمني عند تكرار محاولات تسجيل الدخول بكلمات سر خاطئة لتفادي محاولات الاختراق.',
                'warn_label' => 'محاولات الدخول التحذيرية',
                'crit_label' => 'حد الإغلاق والحظر الحرج للحساب',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 5,
                'unit' => 'محاولة',
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO alert_settings (tenant_id, rule_key, rule_name, description, alert_preview, category, screen_name, screen_file, warn_label, crit_label, is_enabled, warning_threshold, critical_threshold, unit) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE 
                                    rule_name = VALUES(rule_name), 
                                    description = VALUES(description), 
                                    alert_preview = VALUES(alert_preview),
                                    category = VALUES(category),
                                    screen_name = VALUES(screen_name),
                                    screen_file = VALUES(screen_file),
                                    warn_label = VALUES(warn_label),
                                    crit_label = VALUES(crit_label),
                                    unit = VALUES(unit)");
        foreach ($allRules as $r) {
            $stmt->execute([
                $tenant_id, 
                $r['rule_key'], 
                $r['rule_name'], 
                $r['description'], 
                $r['alert_preview'] ?? '', 
                $r['category'], 
                $r['screen_name'], 
                $r['screen_file'], 
                $r['warn_label'] ?? '', 
                $r['crit_label'] ?? '', 
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
        
        $stmtSet = $pdo->prepare("SELECT rule_key, is_enabled, warning_threshold, critical_threshold FROM alert_settings WHERE is_enabled = 1 AND tenant_id = ?");
        $stmtSet->execute([$GLOBAL_TENANT_ID]);
        $settings = [];
        while ($row = $stmtSet->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['rule_key']] = $row;
        }
        
        // 1. فحص تجاوز الحد الائتماني للعملاء
        if (isset($settings['sales_credit_limit_exceeded'])) {
            try {
                $warnPct = (float)$settings['sales_credit_limit_exceeded']['warning_threshold'];
                $critPct = (float)$settings['sales_credit_limit_exceeded']['critical_threshold'];
                $stmt = $pdo->prepare("SELECT data FROM customers WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $c = json_decode($row['data'], true);
                    if ($c && (float)($c['creditLimit'] ?? 0) > 0) {
                        $name = $c['name'] ?? 'عميل غير معروف';
                        $bal = (float)($c['currentBalance'] ?? 0);
                        $limit = (float)($c['creditLimit'] ?? 0);
                        $pct = ($bal / $limit) * 100;
                        if ($pct >= $critPct) {
                            $msg = "🚨 خطر ائتماني حرج! العميل [" . $name . "] تجاوز الحد الائتماني بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . " / الحد: " . number_format($limit, 2) . ")";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'sales_credit_limit_exceeded', 'RED', ?, 'direct-sales.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($pct >= $warnPct) {
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

        // 3. فحص الرصيد السالب للأصناف ونقص المخزون وتجاوز الحد الأعلى
        if (isset($settings['sales_negative_stock']) || isset($settings['inv_critical_zero_stock']) || isset($settings['inv_low_stock']) || isset($settings['inv_overstock_capacity'])) {
            try {
                $lowWarn = isset($settings['inv_low_stock']) ? (float)$settings['inv_low_stock']['warning_threshold'] : 10;
                $lowCrit = isset($settings['inv_low_stock']) ? (float)$settings['inv_low_stock']['critical_threshold'] : 5;
                $overCrit = isset($settings['inv_overstock_capacity']) ? (float)$settings['inv_overstock_capacity']['critical_threshold'] : 500;

                $stmt = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $item = json_decode($row['data'], true);
                    if ($item) {
                        $name = $item['name'] ?? 'صنف';
                        $stock = (float)($item['stockQuantity'] ?? ($item['quantity'] ?? ($item['qty'] ?? 0)));
                        $minStock = (float)($item['minStock'] ?? $lowWarn);
                        if ($stock < 0 && isset($settings['sales_negative_stock'])) {
                            $msg = "🚨 رصيد مخزني سالب! الصنف [" . $name . "] رصيده سالب (" . $stock . " وحدة) في المستودع ويجب تسوية الجرد فوراً!";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'sales_negative_stock', 'RED', ?, 'inventory-report.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($stock == 0 && isset($settings['inv_critical_zero_stock'])) {
                            $msg = "🚨 نفاد مخزون كامل! الصنف [" . $name . "] رصيده الحالي بالمخزن صفر (0 وحدة).";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'inv_critical_zero_stock', 'RED', ?, 'inventory-report.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($stock <= $lowCrit && $stock > 0 && isset($settings['inv_low_stock'])) {
                            $msg = "🚨 خطر نفاد وشيك! الصنف [" . $name . "] وصل لمستوى حرج جداً (المتبقي: " . $stock . " وحدة).";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'inv_low_stock', 'RED', ?, 'inventory-report.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($stock <= $minStock && $stock > 0 && isset($settings['inv_low_stock'])) {
                            $msg = "⚠️ حد إعادة الطلب! الصنف [" . $name . "] وصل لحد الطلب الأدنى (المتبقي: " . $stock . " وحدة).";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'inv_low_stock', 'YELLOW', ?, 'inventory-report.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($stock >= $overCrit && isset($settings['inv_overstock_capacity'])) {
                            $msg = "⚠️ تكدس وتجاوز الحد الأعلى! الصنف [" . $name . "] رصيده (" . $stock . " وحدة) يتجاوز سقف التخزين الآمن (" . $overCrit . " وحدة).";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'inv_overstock_capacity', 'YELLOW', ?, 'smart_inventory_report.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
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
        $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? ORDER BY category ASC, rule_key ASC");
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
    $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? ORDER BY category ASC, rule_key ASC");
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
        'title' => '📦 شاشات المخازن والمستودعات والتوريد والمصانع والمشتريات',
        'screens' => 'تقرير وإدارة المخزون inventory-report.php / المخزون الذكي والرواكد smart_inventory_report.php / المشتريات purchase.php / أوامر الشراء purchase-order.php',
        'icon' => 'fa-warehouse',
        'color' => '#a78bfa',
        'border' => 'rgba(167, 139, 250, 0.4)',
        'rules' => []
    ],
    'FINANCE' => [
        'id' => 'sec-finance',
        'category' => 'FINANCE',
        'title' => '💰 شاشات الخزينة وسندات القبض والصرف والمصارف والشيكات',
        'screens' => 'سند الصرف الفردي single-cash-payment.php / سند الصرف المتعدد multi-cash-payment.php / سند القبض single-cash-receipt.php / مركز السندات vouchers-hub.php / التدفقات cashflow_modern.php',
        'icon' => 'fa-vault',
        'color' => '#34d399',
        'border' => 'rgba(52, 211, 153, 0.4)',
        'rules' => []
    ],
    'CREDIT' => [
        'id' => 'sec-credit',
        'category' => 'CREDIT',
        'title' => '👥 شاشات العملاء والمديونيات والائتمان والتحصيل والموردين',
        'screens' => 'لوحة الائتمان credit_limits_dashboard.php / انكشاف العملاء customer_exposure.php / ركود العملاء customer_churn.php / البيانات الأساسية master-data.php',
        'icon' => 'fa-users-gear',
        'color' => '#fbbf24',
        'border' => 'rgba(251, 191, 36, 0.4)',
        'rules' => []
    ],
    'SECURITY' => [
        'id' => 'sec-security',
        'category' => 'SECURITY',
        'title' => '🛡️ شاشات الأمان والرقابة والتدقيق ومنع الاحتيال المحاسبي',
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

        /* ── أزرار التبويبات الفخمة ── */
        .pillar-card {
            padding: 16px 20px;
            border-radius: 18px;
            font-weight: 900;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 6px;
            border: 2px solid var(--p-border, rgba(255, 255, 255, 0.1));
            background: var(--p-bg, rgba(255, 255, 255, 0.03));
            color: #ffffff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            text-align: right;
        }

        .pillar-card:hover {
            transform: translateY(-3px);
            border-color: var(--p-color, #38bdf8);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }

        .pillar-card.active {
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.25), rgba(239, 68, 68, 0.35)) !important;
            border-color: #f43f5e !important;
            box-shadow: 0 0 25px rgba(244, 63, 94, 0.4) !important;
        }

        /* ── كروت التنبيهات التفصيلية الفردية (واحدة تلو الأخرى) ── */
        .alert-detail-card {
            background: rgba(15, 23, 42, 0.8);
            border: 2px solid var(--card-border, rgba(255, 255, 255, 0.08));
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.25s ease;
            backdrop-filter: blur(15px);
        }

        .alert-detail-card:hover {
            border-color: var(--card-color, #38bdf8);
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);
        }

        .alert-preview-box {
            background: rgba(0, 0, 0, 0.45);
            border: 1.5px dashed var(--card-color, #38bdf8);
            border-radius: 14px;
            padding: 12px 16px;
            margin: 12px 0;
            font-size: 0.88rem;
            font-weight: 800;
            line-height: 1.5;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ios-switch {
            position: relative;
            display: inline-block;
            width: 56px;
            height: 30px;
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
            border: 1.5px solid rgba(255, 255, 255, 0.2);
        }

        .ios-slider:before {
            position: absolute; content: "";
            height: 22px; width: 22px;
            left: 4px; bottom: 2.5px;
            background-color: white;
            transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        }

        input:checked + .ios-slider {
            background-color: #10b981;
            border-color: #10b981;
            box-shadow: 0 0 16px rgba(16, 185, 129, 0.7);
        }

        input:checked + .ios-slider:before {
            transform: translateX(26px);
        }

        .section-block {
            background: var(--surface);
            border: 2px solid var(--border-color, rgba(255, 255, 255, 0.1));
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(20px);
            margin-bottom: 26px;
            transition: all 0.3s ease;
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
                    منظومة رقابية متقدمة تضم 46 تنبيهاً تشغيلياً ومحاسبياً مفصلاً في بطاقات واضحة تمكنك من التحكم الكامل والتفعيل والتجاوز الفوري (On / Off).
                </p>
            </div>

            <!-- Quick Bulk Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" onclick="bulkToggle('ALL', 1)" class="px-4 py-2 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/40 font-black rounded-xl text-xs transition flex items-center gap-1.5 shadow-md">
                    <i class="fa-solid fa-check-double"></i> تفعيل الكل (46 قاعدة)
                </button>
                <button type="button" onclick="bulkToggle('ALL', 0)" class="px-4 py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-400 border border-rose-500/40 font-black rounded-xl text-xs transition flex items-center gap-1.5 shadow-md">
                    <i class="fa-solid fa-ban"></i> تعطيل وتجاوز الكل
                </button>
                <button type="button" onclick="window.print();" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-black rounded-xl text-xs transition flex items-center gap-1.5 border border-slate-700 shadow-md">
                    <i class="fa-solid fa-print"></i> طباعة المصفوفة
                </button>
                <button type="button" onclick="refreshLiveEngine()" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-black rounded-xl text-xs transition shadow-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate"></i> فحص حي الآن
                </button>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY CARDS 📊 -->
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
                <span class="text-xs text-emerald-400 font-bold block">⚙️ التنبيهات المفعلة بالنظام</span>
                <span class="text-3xl font-black text-emerald-400 mt-2 block" id="kpi-enabled-rules"><?php echo $enabled_rules; ?> / <?php echo count($rules); ?></span>
                <span class="text-xs text-emerald-400/80 mt-1 block">تنبيهات نشطة تعمل في الشاشات</span>
            </div>

            <div class="stat-card-v4" style="--border-glow: rgba(56, 189, 248, 0.4); --bar-color: #38bdf8;">
                <span class="text-xs text-sky-400 font-bold block">🏢 الشاشات الخاضعة للرقابة</span>
                <span class="text-3xl font-black text-sky-400 mt-2 block">12 شاشة</span>
                <span class="text-xs text-slate-400 mt-1 block">المبيعات والمخازن والخزائن والأمان</span>
            </div>
        </div>

        <!-- 🎛️ PILLARS NAVIGATION 🎛️ -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3" id="pillars-container">
            <div class="pillar-card active" style="--p-color:#f43f5e; --p-border:rgba(244,63,94,0.4);" onclick="filterPillar('ALL', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-layer-group text-rose-400 text-lg"></i>
                    <span class="text-xs bg-rose-500/20 text-rose-300 px-2 py-0.5 rounded-full font-bold"><?php echo count($rules); ?> تنبيه</span>
                </div>
                <div class="font-black text-sm mt-1">🌟 كل الأقسام</div>
                <div class="text-[11px] text-slate-400">عرض جميع التنبيهات الـ 46</div>
            </div>

            <div class="pillar-card" style="--p-color:#38bdf8; --p-border:rgba(56,189,248,0.3);" onclick="filterPillar('SALES', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-cash-register text-sky-400 text-lg"></i>
                    <span class="text-xs bg-sky-500/20 text-sky-300 px-2 py-0.5 rounded-full font-bold"><?php echo count($sections['SALES']['rules']); ?> تنبيهات</span>
                </div>
                <div class="font-black text-sm mt-1">🛒 المبيعات والفوترة</div>
                <div class="text-[11px] text-slate-400">التكلفة والأسعار والائتمان</div>
            </div>

            <div class="pillar-card" style="--p-color:#a78bfa; --p-border:rgba(167,139,250,0.3);" onclick="filterPillar('INVENTORY', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-warehouse text-purple-400 text-lg"></i>
                    <span class="text-xs bg-purple-500/20 text-purple-300 px-2 py-0.5 rounded-full font-bold"><?php echo count($sections['INVENTORY']['rules']); ?> تنبيهات</span>
                </div>
                <div class="font-black text-sm mt-1">📦 المخازن والمشتريات</div>
                <div class="text-[11px] text-slate-400">الأصناف والتوريد والمصانع</div>
            </div>

            <div class="pillar-card" style="--p-color:#34d399; --p-border:rgba(52,211,153,0.3);" onclick="filterPillar('FINANCE', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-vault text-emerald-400 text-lg"></i>
                    <span class="text-xs bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded-full font-bold"><?php echo count($sections['FINANCE']['rules']); ?> تنبيهات</span>
                </div>
                <div class="font-black text-sm mt-1">💰 الخزينة والشيكات</div>
                <div class="text-[11px] text-slate-400">السيولة وإغلاق الصندوق</div>
            </div>

            <div class="pillar-card" style="--p-color:#fbbf24; --p-border:rgba(251,191,36,0.3);" onclick="filterPillar('CREDIT', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-users-gear text-amber-400 text-lg"></i>
                    <span class="text-xs bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded-full font-bold"><?php echo count($sections['CREDIT']['rules']); ?> تنبيهات</span>
                </div>
                <div class="font-black text-sm mt-1">👥 العملاء والمديونيات</div>
                <div class="text-[11px] text-slate-400">أعمار الديون والركود</div>
            </div>

            <div class="pillar-card" style="--p-color:#f87171; --p-border:rgba(248,113,113,0.3);" onclick="filterPillar('SECURITY', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-shield-halved text-rose-400 text-lg"></i>
                    <span class="text-xs bg-rose-500/20 text-rose-300 px-2 py-0.5 rounded-full font-bold"><?php echo count($sections['SECURITY']['rules']); ?> تنبيهات</span>
                </div>
                <div class="font-black text-sm mt-1">🛡️ الأمان ومنع الاحتيال</div>
                <div class="text-[11px] text-slate-400">إلغاء الفواتير والتدقيق</div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full">
            <input type="text" id="matrix-search" oninput="searchMatrix(this.value)" placeholder="🔍 ابحث عن أي تنبيه أو شاشة أو كلمة (مثلاً: سعر التكلفة، المخزون، الشيكات، المديونيات، إغلاق الوردية)..." class="w-full bg-slate-900 border-2 border-slate-700 rounded-2xl px-5 py-3 text-sm font-bold text-slate-100 outline-none focus:border-rose-500 pr-12 shadow-lg">
            <i class="fa-solid fa-search absolute right-4 top-4 text-slate-400 text-base"></i>
        </div>

        <!-- 📋 DETAIL SECTIONS WITH CARDS ONE BY ONE 📋 -->
        <?php foreach ($sections as $secKey => $sec): ?>
            <div class="section-block" id="<?php echo $sec['id']; ?>" data-sec-category="<?php echo $sec['category']; ?>" style="--border-color: <?php echo $sec['border']; ?>;">
                
                <!-- Section Title Banner -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4 mb-5">
                    <div>
                        <h2 class="text-xl md:text-2xl font-black flex items-center gap-3" style="color: <?php echo $sec['color']; ?>;">
                            <i class="fa-solid <?php echo $sec['icon']; ?>"></i>
                            <?php echo $sec['title']; ?>
                            <span class="text-xs bg-white/10 px-3 py-1 rounded-full text-white font-mono font-bold">
                                <?php echo count($sec['rules']); ?> تنبيهات
                            </span>
                        </h2>
                        <div class="text-xs font-bold text-slate-400 mt-1">
                            🏢 الشاشات المرتبطة: <span class="text-slate-200"><?php echo htmlspecialchars($sec['screens']); ?></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="bulkToggle('<?php echo $sec['category']; ?>', 1)" class="px-3.5 py-1.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 rounded-xl text-xs font-black transition">
                            ✓ تفعيل تنبيهات هذا القسم (<?php echo count($sec['rules']); ?>)
                        </button>
                        <button type="button" onclick="bulkToggle('<?php echo $sec['category']; ?>', 0)" class="px-3.5 py-1.5 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/40 rounded-xl text-xs font-black transition">
                            ✕ تعطيل وتجاوز هذا القسم
                        </button>
                    </div>
                </div>

                <!-- Alert Cards (One by One) -->
                <div class="flex flex-col gap-4">
                    <?php 
                    $cardIdx = 1;
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
                        $warnLabel = !empty($rule['warn_label']) ? $rule['warn_label'] : 'الحد الأدنى / التحذيري';
                        $critLabel = !empty($rule['crit_label']) ? $rule['crit_label'] : 'الحد الأعلى / الحرج';
                        $previewText = !empty($rule['alert_preview']) ? $rule['alert_preview'] : ('⚠️ تنبيه رقابي في ' . $rule['screen_name'] . ': ' . $rule['rule_name']);
                    ?>
                        <div class="alert-detail-card" data-search="<?php echo htmlspecialchars(strtolower($rule['screen_name'] . ' ' . $rule['screen_file'] . ' ' . $rule['rule_name'] . ' ' . $rule['description'] . ' ' . $previewText)); ?>" style="--card-color: <?php echo $sec['color']; ?>; --card-border: <?php echo $sec['border']; ?>;">
                            
                            <!-- Card Header: Title + Screen Link + On/Off Switch -->
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="w-7 h-7 rounded-full bg-white/10 text-white font-black text-xs flex items-center justify-center font-mono">
                                            <?php echo $cardIdx++; ?>
                                        </span>
                                        <h3 class="text-base md:text-lg font-black text-white">
                                            <?php echo htmlspecialchars($rule['rule_name']); ?>
                                        </h3>
                                        <span class="text-[11px] font-mono px-2.5 py-0.5 rounded-lg font-bold" style="background: <?php echo $sec['border']; ?>; color: <?php echo $sec['color']; ?>;">
                                            🏢 <?php echo htmlspecialchars($rule['screen_name']); ?>
                                        </span>
                                    </div>
                                    <a href="<?php echo explode(' ', $rule['screen_file'])[0]; ?>" class="inline-block text-xs font-mono font-bold text-sky-400 hover:underline mt-1 mr-9">
                                        📄 الملف البرمجي: <?php echo htmlspecialchars($rule['screen_file']); ?> ↗
                                    </a>
                                </div>

                                <!-- Big Clear On/Off Switch Button -->
                                <div class="flex items-center gap-3 bg-slate-950/80 px-4 py-2 rounded-2xl border border-slate-800">
                                    <div class="text-left">
                                        <span class="text-[10px] text-slate-400 block font-bold">حالة التنبيه:</span>
                                        <span class="text-xs font-black" id="tstatus-<?php echo $rule['rule_key']; ?>" style="color: <?php echo $rule['is_enabled'] ? '#34d399' : '#94a3b8'; ?>;">
                                            <?php echo $rule['is_enabled'] ? '🟢 مفعّل ويعمل بالشاشة' : '⚫ معطّل ويتم تجاوزه'; ?>
                                        </span>
                                    </div>
                                    <label class="ios-switch">
                                        <input type="checkbox" id="tcheck-<?php echo $rule['rule_key']; ?>" <?php echo $rule['is_enabled'] ? 'checked' : ''; ?> onchange="toggleRuleDetail('<?php echo $rule['rule_key']; ?>', this.checked)">
                                        <span class="ios-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Alert Preview Box (Exactly what user sees in the screen) -->
                            <div class="alert-preview-box text-amber-300">
                                <span class="text-xl">🔔</span>
                                <div>
                                    <span class="text-[11px] text-slate-400 block font-bold">نص رسالة التنبيه التي تظهر للمستخدم في الشاشة:</span>
                                    <span class="font-black text-sm"><?php echo htmlspecialchars($previewText); ?></span>
                                </div>
                            </div>

                            <!-- Description & Control Purpose -->
                            <p class="text-xs md:text-sm text-slate-300 font-bold leading-relaxed mt-1">
                                <i class="fa-solid fa-circle-info text-sky-400 ml-1"></i>
                                <?php echo htmlspecialchars($rule['description']); ?>
                            </p>

                            <!-- Thresholds Config Row -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4 pt-3 border-t border-slate-800/80 items-center">
                                
                                <!-- ⚠️ Min/Warn Threshold Input -->
                                <div class="flex items-center justify-between gap-2 bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                                    <span class="text-xs text-amber-400 font-black">
                                        ⚠️ <?php echo htmlspecialchars($warnLabel); ?>:
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <input type="number" step="any" id="twarn-<?php echo $rule['rule_key']; ?>" value="<?php echo (float)$rule['warning_threshold']; ?>" class="w-16 bg-slate-900 border-2 border-amber-500/40 text-center py-1 rounded-lg text-amber-400 font-black text-xs outline-none focus:border-amber-400">
                                        <span class="text-xs text-slate-400 font-mono font-bold"><?php echo htmlspecialchars($rule['unit'] ?? ''); ?></span>
                                    </div>
                                </div>

                                <!-- 🚨 Max/Crit Threshold Input -->
                                <div class="flex items-center justify-between gap-2 bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                                    <span class="text-xs text-rose-400 font-black">
                                        🚨 <?php echo htmlspecialchars($critLabel); ?>:
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <input type="number" step="any" id="tcrit-<?php echo $rule['rule_key']; ?>" value="<?php echo (float)$rule['critical_threshold']; ?>" class="w-16 bg-slate-900 border-2 border-rose-500/40 text-center py-1 rounded-lg text-rose-400 font-black text-xs outline-none focus:border-rose-400">
                                        <span class="text-xs text-slate-400 font-mono font-bold"><?php echo htmlspecialchars($rule['unit'] ?? ''); ?></span>
                                    </div>
                                </div>

                                <!-- Save Button + Live Status Badge -->
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="saveThresholdsDetail('<?php echo $rule['rule_key']; ?>')" class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-black transition border border-slate-700 flex items-center justify-center gap-1.5 shadow-md">
                                        💾 حفظ الشروط
                                    </button>
                                    
                                    <?php if (!$rule['is_enabled']): ?>
                                        <span class="text-xs font-black px-3 py-1.5 rounded-xl bg-slate-800 text-slate-400 border border-slate-700 whitespace-nowrap">معطّل ✕</span>
                                    <?php elseif ($isViolated && $violSeverity === 'RED'): ?>
                                        <span class="text-xs font-black px-3 py-1.5 rounded-xl bg-rose-950/80 text-rose-300 border border-rose-500/50 whitespace-nowrap animate-pulse">🚨 خطر نشط!</span>
                                    <?php elseif ($isViolated && $violSeverity === 'YELLOW'): ?>
                                        <span class="text-xs font-black px-3 py-1.5 rounded-xl bg-amber-950/80 text-amber-300 border border-amber-500/50 whitespace-nowrap">⚠️ تنبيه نشط</span>
                                    <?php else: ?>
                                        <span class="text-xs font-black px-3 py-1.5 rounded-xl bg-emerald-950/60 text-emerald-300 border border-emerald-500/40 whitespace-nowrap">🟢 آمن تماماً</span>
                                    <?php endif; ?>
                                </div>

                            </div>

                        </div>
                    <?php endforeach; ?>
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
    function toggleRuleDetail(ruleKey, isChecked) {
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
                    statusTxt.textContent = isChecked ? '🟢 مفعّل ويعمل بالشاشة' : '⚫ معطّل ويتم تجاوزه';
                    statusTxt.style.color = isChecked ? '#34d399' : '#94a3b8';
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

    function saveThresholdsDetail(ruleKey) {
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
                const orig = btn.innerHTML;
                btn.innerHTML = '✓ تم الحفظ بنجاح';
                btn.classList.add('bg-emerald-600');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.classList.remove('bg-emerald-600');
                }, 1200);
                refreshLiveEngine();
                if (window.ERPAlertsGuard) window.ERPAlertsGuard.loadRules();
            }
        });
    }

    function filterPillar(cat, el) {
        document.querySelectorAll('#pillars-container .pillar-card').forEach(p => p.classList.remove('active'));
        el.classList.add('active');

        const sections = document.querySelectorAll('.section-block[data-sec-category]');
        sections.forEach(sec => {
            if (cat === 'ALL' || sec.getAttribute('data-sec-category') === cat) {
                sec.style.display = 'block';
            } else {
                sec.style.display = 'none';
            }
        });
        
        if (cat !== 'ALL') {
            const targetSec = document.querySelector('.section-block[data-sec-category="' + cat + '"]');
            if (targetSec) {
                targetSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    function searchMatrix(q) {
        const query = q.trim().toLowerCase();
        const cards = document.querySelectorAll('.alert-detail-card');
        cards.forEach(c => {
            const text = c.getAttribute('data-search') || '';
            c.style.display = (query === '' || text.includes(query)) ? 'block' : 'none';
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