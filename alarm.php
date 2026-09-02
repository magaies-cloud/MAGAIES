<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مصفوفة الرقابة والتنبيهات العالمية Titanium V8
// أقوى منظومة رقابية استراتيجية متكاملة تضم 88 قاعدة تنبيه ورقابة جنائية ومنع تلاعب
// ====================================================================
$page_title = 'مصفوفة الرقابة وإدارة التنبيهات العالمية (88 قاعدة)';
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
                'rule_key' => 'master_selling_below_cost',
                'rule_name' => 'تسجيل سعر بيع افتراضي أقل من التكلفة في بطاقة الصنف',
                'screen_name' => 'البيانات الأساسية وتعاريف الأصناف',
                'screen_file' => 'master-data.php',
                'category' => 'MASTER',
                'alert_preview' => '⚠️ تنبيه تسعير: سعر البيع الافتراضي المسجل في بطاقة الصنف يقل عن سعر التكلفة/الشراء المعتمد!',
                'description' => 'يراقب بطاقات الأصناف عند إدخالها أو تعديلها وينبه فوراً إذا كان سعر البيع أقل من التكلفة لتفادي الخسائر الأولية.',
                'warn_label' => 'الحد الأدنى للتكلفة المسموح به',
                'crit_label' => 'حد الخسارة الحرج بالصنف',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'صنف',
            ],
            [
                'rule_key' => 'master_missing_stock_limits',
                'rule_name' => 'أصناف مسجلة بدون تحديد الحد الأدنى والأعلى وحد الطلب',
                'screen_name' => 'بطاقة الصنف والبيانات الأساسية',
                'screen_file' => 'master-data.php / inventory-report.php',
                'category' => 'MASTER',
                'alert_preview' => '⚠️ تنبيه رقابي: صنف مسجل في النظام دون تحديد حدود الأمان المخزني (الحد الأدنى والأعلى وحد الطلب)!',
                'description' => 'يحصر الأصناف التي تفتقر لإعدادات حدود المخزون لإلزام مسؤول المستودع بضبط مستويات التخزين الآمنة.',
                'warn_label' => 'عدد الأصناف التحذيري بدون حدود',
                'crit_label' => 'الحد الحرج للأصناف غير المعرفة',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 20,
                'unit' => 'صنف',
            ],
            [
                'rule_key' => 'master_duplicate_barcode_name',
                'rule_name' => 'تكرار الباركود أو اسم الصنف التجاري في بطاقات أخرى',
                'screen_name' => 'البيانات الأساسية والباركود',
                'screen_file' => 'master-data.php',
                'category' => 'MASTER',
                'alert_preview' => '🚨 تكرار باركود: هذا الباركود أو الاسم مسجل مسبقاً لصنف آخر ويجب تعديله منعاً للتضارب!',
                'description' => 'منع وتنبيه استخدام نفس الباركود الدولي أو الاسم لأكثر من صنف لتفادي الخلط في الفواتير والجرد.',
                'warn_label' => 'عدد مرات التكرار التحذيرية',
                'crit_label' => 'حد الحظر الفوري التام',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'تكرار',
            ],
            [
                'rule_key' => 'master_cust_no_credit_limit',
                'rule_name' => 'عملاء آجلون مسجلون دون سقف ائتماني أو فترة سماح معتمدة',
                'screen_name' => 'بطاقة العملاء والبيانات الأساسية',
                'screen_file' => 'master-data.php / credit_limits_dashboard.php',
                'category' => 'MASTER',
                'alert_preview' => '⚠️ تنبيه ائتمان: حساب عميل آجل مفتوح دون تحديد سقف مديونية أو فترة سماح معتمدة من الإدارة المالية!',
                'description' => 'ينبه عند إنشاء أو تعديل بطاقة عميل آجل دون اعتماد سقف ائتماني محدد لتفادي فتح مديونيات عشوائية.',
                'warn_label' => 'عدد الحسابات التحذيري',
                'crit_label' => 'الحد الحرج للحسابات المفتوحة',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 10,
                'unit' => 'عميل',
            ],
            [
                'rule_key' => 'master_missing_tax_id',
                'rule_name' => 'غياب الرقم الضريبي للمؤسسات والشركات التجارية (B2B)',
                'screen_name' => 'بيانات العملاء والموردين الأساسية',
                'screen_file' => 'master-data.php',
                'category' => 'MASTER',
                'alert_preview' => '⚠️ تنبيه ضريبي: عميل/مورد تجاري مسجل دون إدخال الرقم الضريبي المعتمد للفوترة الإلكترونية!',
                'description' => 'يراقب اكتمال البيانات الضريبية للشركات لضمان الامتثال للوائح الفوترة الإلكترونية والضريبة المضافة.',
                'warn_label' => 'عدد السجلات بدون رقم ضريبي',
                'crit_label' => 'الحد الحرج للإلزام الضريبي',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 15,
                'unit' => 'سجل',
            ],
            [
                'rule_key' => 'master_safe_unassigned_users',
                'rule_name' => 'خزائن أو بنوك غير مخصصة لأمناء عهدة ومستخدمين محددين',
                'screen_name' => 'تعريفات الخزائن والحسابات المصرفية',
                'screen_file' => 'master-data.php / settings.php',
                'category' => 'MASTER',
                'alert_preview' => '🚨 تنبيه أمني: خزينة أو صندوق نقد غير مربوط بمستخدمين محددين ومتاح للصرف من أي مستخدم!',
                'description' => 'يمنع فتح الصرف من الخزائن لغير الأمناء المعينين رسمياً لتحديد المسؤولية المالية بدقة.',
                'warn_label' => 'عدد الخزائن المفتوحة',
                'crit_label' => 'حد الخطر الأمني الحرج',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'خزينة',
            ],
            [
                'rule_key' => 'master_supplier_missing_bank_info',
                'rule_name' => 'بطاقات موردين بدون بيانات حسابات بنكية أو شروط سداد رسمية',
                'screen_name' => 'بطاقة الموردين والبيانات الأساسية',
                'screen_file' => 'master-data.php / purchase.php',
                'category' => 'MASTER',
                'alert_preview' => '⚠️ تنبيه موردين: بطاقة مورد معتمدة دون توثيق حسابه المصرفي أو شروط السداد المعتمدة!',
                'description' => 'يلزم بتسجيل بيانات الحسابات البنكية للموردين قبل اعتمادهم لمنع الأخطاء في التحويلات المالية.',
                'warn_label' => 'عدد الموردين غير الموثقين',
                'crit_label' => 'الحد الحرج للموردين غير المكتملين',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 10,
                'unit' => 'مورد',
            ],
            [
                'rule_key' => 'master_items_missing_categories_units',
                'rule_name' => 'أصناف مسجلة بدون تصنيف رئيسي أو بدون وحدات قياس معتمدة',
                'screen_name' => 'البيانات الأساسية وشجرة الأصناف',
                'screen_file' => 'master-data.php',
                'category' => 'MASTER',
                'alert_preview' => '⚠️ تنبيه شجرة الأصناف: أصناف مسجلة بدون ربطها بمجموعات تصنيف أو بدون تحديد وحدة القياس!',
                'description' => 'يراقب اكتمال تصنيف الأصناف لضمان دقة التقارير التجميعية والمخزنية.',
                'warn_label' => 'عدد الأصناف غير المصنفة',
                'crit_label' => 'الحد الحرج للأصناف التائهة',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 20,
                'unit' => 'صنف',
            ],
            [
                'rule_key' => 'inv_ai_anomaly_fast_surge',
                'rule_name' => 'سحب وسرعة دوران فجائية غير معتادة على الصنف (سحب مريب)',
                'screen_name' => 'المخزون الذكي وتحليلات حركة الأصناف',
                'screen_file' => 'smart_inventory_report.php / inventory-report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '🚀 تنبيه ذكي: صنف يشهد سحباً فجائياً متسارعاً غير معتاد! يرجى مراجعة صحة التسعير وأمر التوريد.',
                'description' => 'خوارزمية ذكية ترصد الأصناف التي تشهد قفزة غير مبررة في المبيعات لاكتشاف أخطاء التسعير أو طلبات التوريد الاستباقية.',
                'warn_label' => 'نسبة القفزة التحذيرية في السحب',
                'crit_label' => 'نسبة القفزة الحرجة للمراجعة العاجلة',
                'is_enabled' => 1,
                'warning_threshold' => 150,
                'critical_threshold' => 300,
                'unit' => '%',
            ],
            [
                'rule_key' => 'inv_ai_deadstock_capital_freeze',
                'rule_name' => 'ركود البضائع وتجميد رأس المال في المخزن (Deadstock Capital)',
                'screen_name' => 'المخزون الذكي وتحليل رأس المال المجمد',
                'screen_file' => 'smart_inventory_report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه سيولة وركود: بضائع راكدة تجمد رأس مال في المخزن دون حركة بيع وتتطلب تسييلاً عاجلاً!',
                'description' => 'يحسب القيمة المالية المجمدة في الأصناف التي لم تتحرك لأكثر من الفترة المحددة ويقترح عروض ترويجية لتصريفها.',
                'warn_label' => 'أيام الركود التحذيرية',
                'crit_label' => 'أيام الركود الحرجة للتسييل',
                'is_enabled' => 1,
                'warning_threshold' => 60,
                'critical_threshold' => 120,
                'unit' => 'يوم',
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
                'rule_name' => 'تجاوز الحد الأعلى للمخزون وسعة التخزين (Max Overstock)',
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
                'rule_key' => 'inv_drop_in_turnover',
                'rule_name' => 'انخفاض حاد في معدل دوران وسحب الصنف الرئيسي',
                'screen_name' => 'تحليلات دوران المخزون والربحية',
                'screen_file' => 'inventory-report.php / smart_inventory_report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه مبيعات: انخفاض حاد في سحب صنف رئيسي مقارنة بالأشهر السابقة يستوجب مراجعة المنافسين!',
                'description' => 'يرصد هبوط حركة سحب الأصناف الاستراتيجية الأكثر مبيعاً لمراجعة الأسعار والتسويق.',
                'warn_label' => 'نسبة الهبوط التحذيرية في السحب',
                'crit_label' => 'نسبة الهبوط الحرجة في المبيعات',
                'is_enabled' => 1,
                'warning_threshold' => 30,
                'critical_threshold' => 60,
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
                'rule_key' => 'inv_negative_stock_balance',
                'rule_name' => 'وجود أرصدة كميات سالبة في أي عنبر أو مستودع',
                'screen_name' => 'حركة المخزون وتقارير العنابر',
                'screen_file' => 'inventory-report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '🚨 خطر مخزني: رصد كميات سالبة في المستودع تتطلب تسوية جردية فورية لمنع تشويه التكلفة!',
                'description' => 'يكشف أي صنف أصبح رصيده سالباً نتيجة عمليات صرف سابقة دون تسجيل فواتير الشراء المقابلة.',
                'warn_label' => 'الرصيد السالب التحذيري',
                'crit_label' => 'الحد الحرج للإيقاف',
                'is_enabled' => 1,
                'warning_threshold' => 0,
                'critical_threshold' => 0,
                'unit' => 'وحدة',
            ],
            [
                'rule_key' => 'inv_damaged_items_threshold',
                'rule_name' => 'ارتفاع نسبة التوالف والهالك المخزني عن الحد المسموح',
                'screen_name' => 'محاضر التوالف والهالك بالمستودع',
                'screen_file' => 'inventory-report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه توالف: نسبة البضائع التالفة والهالكة في المخزن تجاوزت النسبة المعيارية المعتمدة!',
                'description' => 'يراقب محاضر الهالك والتوالف وينبه عند تجاوزها نسبة الفاقد الطبيعي المعتمدة للنشاط.',
                'warn_label' => 'نسبة التوالف التحذيرية',
                'crit_label' => 'نسبة التوالف الحرجة للتحقيق',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 3,
                'unit' => '%',
            ],
            [
                'rule_key' => 'inv_unsettled_transfer_vouchers',
                'rule_name' => 'سندات مناقلة بضاعة بين المخازن معلقة دون تأكيد الاستلام',
                'screen_name' => 'المناقلات والتحويلات بين الفروع والمخازن',
                'screen_file' => 'inventory-report.php / master-data.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه مناقلة: بضائع تم تحويلها بين المستودعات ولم يتم تأكيد استلامها في المستودع المستلم!',
                'description' => 'يراقب البضائع في الطريق بين الفروع والمستودعات لمنع ضياعها أو تأخر إثباتها دفترياً.',
                'warn_label' => 'أيام التعليق التحذيرية في الطريق',
                'crit_label' => 'أيام التعليق الحرجة للتحقيق',
                'is_enabled' => 1,
                'warning_threshold' => 2,
                'critical_threshold' => 5,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_min_profit_margin_breach',
                'rule_name' => 'انخفاض متوسط هامش ربح الصنف عن النسبة المستهدفة',
                'screen_name' => 'تقارير ربحية الأصناف',
                'screen_file' => 'smart_inventory_report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه ربحية: متوسط هامش الربح المحقق على الصنف أقل من النسبة المستهدفة للنشاط!',
                'description' => 'يحسب ربحية مبيعات كل صنف وينبه عند تدني هامش ربحه الفعلي عن المستهدف.',
                'warn_label' => 'أدنى هامش ربح مقبول',
                'crit_label' => 'هامش الربح الحرج الخاسر',
                'is_enabled' => 1,
                'warning_threshold' => 10,
                'critical_threshold' => 3,
                'unit' => '%',
            ],
            [
                'rule_key' => 'inv_unmoved_seasonal_stock',
                'rule_name' => 'بضائع موسمية لم يتم تصريفها مع قرب انتهاء موسمها',
                'screen_name' => 'المخزون الموسمي والتحليلات',
                'screen_file' => 'smart_inventory_report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '⚠️ تنبيه موسمي: مخزون بضائع موسمية متبقٍ بكميات كبيرة مع اقتراب نهاية الموسم التجاري!',
                'description' => 'تنبيه استباقي لتصريف البضائع الموسمية بخصومات ترويجية قبل انتهاء موسم رواجها.',
                'warn_label' => 'أيام التنبيه قبل نهاية الموسم',
                'crit_label' => 'أيام الخطر الحرج للتكدس الموسمي',
                'is_enabled' => 1,
                'warning_threshold' => 30,
                'critical_threshold' => 15,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'inv_high_shrinkage_anomaly',
                'rule_name' => 'اكتشاف عجز وفاقد مخزني متكرر ومريب في صنف معين',
                'screen_name' => 'تحليلات أمن المستودعات والفاقد',
                'screen_file' => 'inventory-report.php',
                'category' => 'INVENTORY',
                'alert_preview' => '🚨 إنذار فاقد متكرر: تكرار تسجيل عجز في نفس الصنف خلال فترات جرد متقاربة يستوجب التحقيق الأمني!',
                'description' => 'خوارزمية ترصد تكرار العجز في أصناف غالية الثمن وصغيرة الحجم لاكتشاف السرقات الممنهجة.',
                'warn_label' => 'مرات تكرار العجز التحذيري',
                'crit_label' => 'حد الخطر الأمني للتحقيق الفوري',
                'is_enabled' => 1,
                'warning_threshold' => 2,
                'critical_threshold' => 3,
                'unit' => 'مرات',
            ],
            [
                'rule_key' => 'sales_below_cost',
                'rule_name' => 'البيع بأقل من سعر التكلفة المرجحة أو سعر الشراء (WAC)',
                'screen_name' => 'شاشة المبيعات المباشرة وفواتير البيع',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه رقابي في شاشة المبيعات: سعر بيع الصنف أقل من سعر التكلفة/الشراء المعتمد!',
                'description' => 'يراقب سعر البيع المدخل في الفاتورة لحظياً، وإذا كان أقل من متوسط تكلفة الصنف يظهر تنبيه فوري لمنع الخسائر.',
                'warn_label' => 'الحد الأدنى المسموح للتكلفة',
                'crit_label' => 'حد الخسارة الحرج للعمليات',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 3,
                'unit' => 'عملية',
            ],
            [
                'rule_key' => 'sales_price_override',
                'rule_name' => 'تعديل وتغيير سعر البيع المسجل للصنف يدوياً بالفاتورة',
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
                'rule_key' => 'sales_no_sale_drawer_open',
                'rule_name' => 'فتح درج الكاشير يدوياً بدون تسجيل عملية بيع (No-Sale Drawer Kick)',
                'screen_name' => 'نقاط البيع والكاشير والدرج',
                'screen_file' => 'pos-invoice-print.php / direct-sales.php',
                'category' => 'SALES',
                'alert_preview' => '🚨 إنذار كاشير: تم فتح درج النقدية يدوياً بدون تسجيل فاتورة بيع ويستوجب المراقبة!',
                'description' => 'يرصد عدد مرات فتح درج النقدية بدون حفظ فاتورة لكشف ثغرات اختلاس النقد في نقاط البيع.',
                'warn_label' => 'مرات فتح الدرج التحذيرية بدون بيع',
                'crit_label' => 'الحد الحرج لفتح الدرج المشبوه',
                'is_enabled' => 1,
                'warning_threshold' => 2,
                'critical_threshold' => 5,
                'unit' => 'مرات',
            ],
            [
                'rule_key' => 'sales_high_line_void_ratio',
                'rule_name' => 'تكرار إلغاء وحذف أسطر من الفاتورة قبل حفظها (Line Voids)',
                'screen_name' => 'شاشات الكاشير وإدخال الفواتير',
                'screen_file' => 'direct-sales.php / pos-invoice-print.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه تدقيق: تكرار حذف أصناف من الفاتورة بعد مسحها بالباركود يستوجب مراجعة الكاشير!',
                'description' => 'يكشف مسح السلع واستلام النقد من العميل ثم حذف السلعة قبل الضغط على حفظ.',
                'warn_label' => 'نسبة حذف الأسطر التحذيرية',
                'crit_label' => 'نسبة الحذف الحرجة للتحقيق',
                'is_enabled' => 1,
                'warning_threshold' => 10,
                'critical_threshold' => 25,
                'unit' => '%',
            ],
            [
                'rule_key' => 'sales_excessive_cash_refunds',
                'rule_name' => 'ارتفاع غير طبيعي في المرتجعات النقدية لنفس الكاشير في يوم واحد',
                'screen_name' => 'مرتجعات المبيعات والكاشير',
                'screen_file' => 'sales.php / direct-sales.php',
                'category' => 'SALES',
                'alert_preview' => '🚨 تنبيه احتيال: كاشير سجل مبالغ مرتجعات نقدية مرتفعة جداً بشكل استثنائي خلال اليوم!',
                'description' => 'رصد المرتجعات النقدية الوهمية التي يجريها الكاشير لسحب المبالغ نقداً من الصندوق.',
                'warn_label' => 'مبلغ المرتجع التحذيري بالوردية',
                'crit_label' => 'مبلغ المرتجع الحرج للتحقيق',
                'is_enabled' => 1,
                'warning_threshold' => 20000,
                'critical_threshold' => 50000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'sales_aml_large_cash_limit',
                'rule_name' => 'مبيعات نقدية مباشرة بمبالغ ضخمة تتجاوز سقف الامتثال المالي (AML)',
                'screen_name' => 'المبيعات النقدية والفوترة الكبرى',
                'screen_file' => 'sales.php / single-cash-receipt.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه امتثال مالي: فاتورة بيع نقدي بمبلغ ضخم جداً يستوجب التحصيل عبر تحويل أو شيك بنكي!',
                'description' => 'يراقب الالتزام بسقف التحصيل النقدي والامتثال للوائح غسيل الأموال وضبط السيولة.',
                'warn_label' => 'الحد التحذيري للبيع النقدي',
                'crit_label' => 'الحد الأقصى الحرج للإلزام المصرفي',
                'is_enabled' => 1,
                'warning_threshold' => 1000000,
                'critical_threshold' => 3000000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'sales_rapid_duplicate_invoice',
                'rule_name' => 'تكرار إصدار فواتير متطابقة لنفس العميل خلال دقائق معدودة',
                'screen_name' => 'شاشات الفوترة والمبيعات السريعة',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'SALES',
                'alert_preview' => '⚠️ تنبيه تكرار: تم إصدار فاتورتين بنفس القيمة والأصناف لنفس العميل في وقت متقارب جداً!',
                'description' => 'يكشف تكرار إصدار الفاتورة مرتين بالخطأ أو التكرار المريب لنفس المعاملة.',
                'warn_label' => 'الفارق الزمني التحذيري للتكرار',
                'crit_label' => 'الفارق الزمني الحرج للتحقيق',
                'is_enabled' => 1,
                'warning_threshold' => 5,
                'critical_threshold' => 2,
                'unit' => 'دقائق',
            ],
            [
                'rule_key' => 'pur_price_hike_alert',
                'rule_name' => 'ارتفاع سعر الشراء عن آخر توريد مسجل من المورد أو المصنع',
                'screen_name' => 'شاشة المشتريات وفواتير التوريد والمصانع',
                'screen_file' => 'purchase.php / purchase-order.php',
                'category' => 'PURCHASE',
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
                'category' => 'PURCHASE',
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
                'rule_key' => 'supply_supplier_delay',
                'rule_name' => 'تأخر استلام طلبيات الشراء من الموردين والمصانع عن موعدها',
                'screen_name' => 'المشتريات ومتابعة أوامر التوريد',
                'screen_file' => 'purchase.php / purchase-order.php',
                'category' => 'PURCHASE',
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
                'rule_key' => 'pur_unsettled_advances',
                'rule_name' => 'دفعات نقدية مقدمة للموردين معلقة لفترة طويلة دون استلام بضاعة',
                'screen_name' => 'حسابات الموردين والدفعات المقدمة',
                'screen_file' => 'purchase.php / single-cash-payment.php',
                'category' => 'PURCHASE',
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
                'rule_key' => 'pur_monthly_budget_cap',
                'rule_name' => 'تجاوز سقف الموازنة التقديرية الشهرية لمشتريات المنشأة',
                'screen_name' => 'الموازنات التقديرية والمشتريات',
                'screen_file' => 'purchase.php / cashflow_modern.php',
                'category' => 'PURCHASE',
                'alert_preview' => '⚠️ تنبيه موازنة: إجمالي فواتير المشتريات لهذا الشهر اقترب من تجاوز سقف الموازنة المعتمد!',
                'description' => 'يحسب تراكم مشتريات الشهر وينبه الإدارة عند الوصول إلى السقف المالي المحدد للمشتريات.',
                'warn_label' => 'نسبة استهلاك الموازنة التحذيرية',
                'crit_label' => 'نسبة تجاوز الموازنة الحرجة',
                'is_enabled' => 1,
                'warning_threshold' => 85,
                'critical_threshold' => 100,
                'unit' => '%',
            ],
            [
                'rule_key' => 'supplier_overdue_balance',
                'rule_name' => 'مستحقات متأخرة لمورد تجاوزت تاريخ الاستحقاق المعتمد',
                'screen_name' => 'حسابات الموردين والمشتريات والمصانع',
                'screen_file' => 'purchase.php / master-data.php',
                'category' => 'PURCHASE',
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
                'rule_key' => 'pur_three_way_matching_mismatch',
                'rule_name' => 'عدم مطابقة الفاتورة الثلاثية 3-Way Matching (أمر الشراء vs الاستلام vs الفاتورة)',
                'screen_name' => 'المطابقة الثلاثية ومراجعة المشتريات',
                'screen_file' => 'purchase.php / purchase-order.php',
                'category' => 'PURCHASE',
                'alert_preview' => '🚨 تنبيه تدقيق: تباين بين أمر الشراء المعتمد وإذن استلام المخزن الفعلي وفاتورة المورد!',
                'description' => 'أقوى قاعدة رقابية عالمية تفحص السعر والكمية بين مستندات الشراء الثلاثة قبل صرف المستحقات.',
                'warn_label' => 'نسبة التباين التحذيرية المسموحة',
                'crit_label' => 'حد الرفض التام للصرف',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 5,
                'unit' => '%',
            ],
            [
                'rule_key' => 'pur_unvetted_new_vendor',
                'rule_name' => 'إصدار فاتورة شراء لمورد جديد غير معتمد من إدارة المشتريات',
                'screen_name' => 'المشتريات وإدارة الموردين الجدد',
                'screen_file' => 'purchase.php / master-data.php',
                'category' => 'PURCHASE',
                'alert_preview' => '⚠️ تنبيه رقابي: محاولة الشراء من مورد جديد لم يتم اعتماده وتوثيق سجله رسمياً بالنظام!',
                'description' => 'يلزم باعتماد الموردين الجدد من الإدارة قبل تسجيل عمليات شراء منهم لمنع المشتريات الوهمية.',
                'warn_label' => 'عدد الفواتير التحذيري',
                'crit_label' => 'حد الإلزام باعتماد المورد',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'فاتورة',
            ],
            [
                'rule_key' => 'pur_single_vendor_monopoly',
                'rule_name' => 'تركز مشتريات صنف معين على مورد واحد دون عروض أسعار منافسة',
                'screen_name' => 'تحليلات المشتريات والمناقصات',
                'screen_file' => 'purchase.php / smart_inventory_report.php',
                'category' => 'PURCHASE',
                'alert_preview' => '⚠️ تنبيه تركز توريد: أكثر من 80% من مشتريات الصنف محتكرة لدى مورد واحد دون مقارنة أسعار!',
                'description' => 'ينبه الإدارة بضرورة استدراج عروض أسعار بديلة لكسر الاحتكار والحصول على أفضل أسعار.',
                'warn_label' => 'نسبة تركز التوريد التحذيرية',
                'crit_label' => 'نسبة الاحتكار الحرجة للمورد',
                'is_enabled' => 1,
                'warning_threshold' => 75,
                'critical_threshold' => 90,
                'unit' => '%',
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
                'rule_key' => 'fin_unreconciled_bank_items',
                'rule_name' => 'فروقات وعناصر معلقة في مذكرة تسوية البنك لأكثر من فترة السماح',
                'screen_name' => 'التسويات البنكية ومطابقة كشوف الحساب',
                'screen_file' => 'cashflow_modern.php / general_ledger_v2.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ تنبيه تسوية بنكية: توجد عمليات معلقة بكشف حساب البنك لم تطابق الدفاتر لأكثر من 15 يوماً!',
                'description' => 'يراقب الفروقات بين الرصيد الدفتري ورصيد كشف الحساب البنكي الفعلي لسرعة تسويتها.',
                'warn_label' => 'أيام التعليق التحذيرية للتسوية',
                'crit_label' => 'أيام التعليق الحرجة للتحقيق',
                'is_enabled' => 1,
                'warning_threshold' => 15,
                'critical_threshold' => 30,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'fin_structuring_transaction_alert',
                'rule_name' => 'تجزئة المبالغ وسندات الصرف المتتالية للتحايل على سقف الاعتماد (Structuring)',
                'screen_name' => 'سجل التدقيق وسندات الصرف اليومية',
                'screen_file' => 'single-cash-payment.php / vouchers-hub.php',
                'category' => 'FINANCE',
                'alert_preview' => '🚨 إنذار تحايل: رصد إصدار سندات صرف متعددة متتالية بمبالغ صغيرة للتحايل على سقف اعتماد المدير!',
                'description' => 'خوارزمية جنائية تكشف تقسيم المصروفات الكبيرة إلى سندات صغيرة لتفادي اشتراط توقيع المدير.',
                'warn_label' => 'عدد السندات المتتالية التحذيري',
                'crit_label' => 'الحد الحرج لرصد التحايل',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 5,
                'unit' => 'سندات',
            ],
            [
                'rule_key' => 'fin_extreme_cash_volatility',
                'rule_name' => 'تذبذب حاد وسحب استثنائي في رصيد الخزينة خلال يوم واحد',
                'screen_name' => 'حركة الخزينة والسيولة اليومية',
                'screen_file' => 'cashflow_modern.php / single-cash-payment.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ تنبيه سيولة استثنائي: رصيد الخزينة شهد سحباً أو هبوطاً مفاجئاً بأكثر من 50% في يوم واحد!',
                'description' => 'ينبه الإدارة المالية عند حدوث تغير دراماتيكي مفاجئ في حجم النقدية المتاحة بالصندوق.',
                'warn_label' => 'نسبة التذبذب اليومي التحذيرية',
                'crit_label' => 'نسبة الهبوط الحرجة في السيولة',
                'is_enabled' => 1,
                'warning_threshold' => 40,
                'critical_threshold' => 65,
                'unit' => '%',
            ],
            [
                'rule_key' => 'fin_far_dated_cheque_risk',
                'rule_name' => 'استلام شيكات آجلة بتواريخ استحقاق بعيدة جداً تفوق فترة الأمان',
                'screen_name' => 'إدارة الشيكات والائتمان المصرفي',
                'screen_file' => 'vouchers-hub.php / single-cash-receipt.php',
                'category' => 'FINANCE',
                'alert_preview' => '⚠️ مخاطر شيكات: تم استلام شيك بتاريخ استحقاق يتجاوز 6 أشهر مما يعرض الشركة لمخاطر التعثر!',
                'description' => 'يراقب فترات استحقاق الشيكات الواردة لتفادي تجميد السيولة وتقليل مخاطر تعثر محرري الشيكات.',
                'warn_label' => 'أشهر الاستحقاق التحذيرية',
                'crit_label' => 'أشهر الاستحقاق الحرجة للرفض',
                'is_enabled' => 1,
                'warning_threshold' => 4,
                'critical_threshold' => 6,
                'unit' => 'أشهر',
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
                'rule_key' => 'credit_debt_concentration_risk',
                'rule_name' => 'تركز المديونية العالية في عدد محدود جداً من العملاء',
                'screen_name' => 'انكشاف العملاء وعمق الائتمان',
                'screen_file' => 'customer_exposure.php / credit_limits_dashboard.php',
                'category' => 'CREDIT',
                'alert_preview' => '⚠️ تنبيه مخاطر ائتمان: نسبة تركز ديون الشركة في عميل واحد أو عملاء قلائل تجاوزت حد الأمان!',
                'description' => 'يقيس تركز الديون ($Debt Concentration$) لتفادي تعرض المنشأة لأزمة سيولة في حال تعثر عميل رئيسي.',
                'warn_label' => 'نسبة تركز الدين التحذيرية بالعميل',
                'crit_label' => 'نسبة تركز الدين الحرجة للمنشأة',
                'is_enabled' => 1,
                'warning_threshold' => 25,
                'critical_threshold' => 40,
                'unit' => '%',
            ],
            [
                'rule_key' => 'sales_blocked_customer',
                'rule_name' => 'محاولة إصدار فاتورة أو سند لعميل محظور أو موقوف إدارياً',
                'screen_name' => 'شاشات المبيعات والائتمان وإصدار الفواتير',
                'screen_file' => 'direct-sales.php / sales.php',
                'category' => 'CREDIT',
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
                'rule_key' => 'credit_deteriorating_payment_behavior',
                'rule_name' => 'تدهور مفاجئ في منحنى سرعة ومعدل سداد العميل (تباطؤ السداد)',
                'screen_name' => 'تحليلات سلوك سداد العملاء',
                'screen_file' => 'customer_exposure.php / credit_limits_dashboard.php',
                'category' => 'CREDIT',
                'alert_preview' => '⚠️ تنبيه سلوكي: متوسط سرعة سداد العميل تباطأ بشكل ملحوظ مقارنة بسجله التاريخي!',
                'description' => 'خوارزمية ذكية تقارن متوسط أيام السداد التاريخية للعميل مع أدائه الحالي للإنذار المبكر بالتعثر.',
                'warn_label' => 'نسبة التباطؤ التحذيرية في السداد',
                'crit_label' => 'نسبة التباطؤ الحرجة لخفض الائتمان',
                'is_enabled' => 1,
                'warning_threshold' => 40,
                'critical_threshold' => 80,
                'unit' => '%',
            ],
            [
                'rule_key' => 'credit_temp_override_expired',
                'rule_name' => 'انتهاء فترة السماح للائتمان الاستثنائي المؤقت الممنوح للعميل',
                'screen_name' => 'إدارة الاستثناءات الائتمانية',
                'screen_file' => 'credit_limits_dashboard.php',
                'category' => 'CREDIT',
                'alert_preview' => '⚠️ تنبيه استثناء ائتماني: انتهت صلاحية التجاوز الائتماني المؤقت ويجب إعادة الائتمان لسقفه الأصلي!',
                'description' => 'ينبه بانتهاء مهلة الاستثناء المالي الممنوح لعميل للشراء فوق سقف الائتمان.',
                'warn_label' => 'أيام التنبيه قبل الانتهاء',
                'crit_label' => 'أيام انتهاء الصلاحية الحرجة',
                'is_enabled' => 1,
                'warning_threshold' => 2,
                'critical_threshold' => 0,
                'unit' => 'يوم',
            ],
            [
                'rule_key' => 'credit_customer_nsf_history',
                'rule_name' => 'محاولة التعامل أو استلام شيكات من عميل لديه سوابق شيكات بدون رصيد',
                'screen_name' => 'شاشات القبض والمبيعات الآجلة',
                'screen_file' => 'single-cash-receipt.php / customer_exposure.php',
                'category' => 'CREDIT',
                'alert_preview' => '🚨 تحذير مخاطر شيكات: هذا العميل لديه سوابق ارتداد شيكات بدون رصيد ويُمنع استلام شيكات منه!',
                'description' => 'فحص فوري لسجل العميل المصرفي لمنع استلام شيكات جديدة من عملاء تعثرت شيكاتهم سابقاً.',
                'warn_label' => 'عدد الشيكات المرتدة السابقة',
                'crit_label' => 'حد الحظر التام للشيكات',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 2,
                'unit' => 'شيكات سابقة',
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
                'rule_key' => 'audit_linked_delivered_doc_block',
                'rule_name' => 'منع وحظر حذف الفواتير المرتبطة بتسليم/استلام مخزني فعلي',
                'screen_name' => 'شاشات الفواتير والمخازن وسندات الصرف',
                'screen_file' => 'sales.php / purchase.php / inventory-report.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚫 حظر أمني صارم: يُمنع منعاً باتاً حذف هذه الفاتورة لأن بضاعتها سُلمت/استُلمت مخزنياً! يجب عمل مرتجع رسمي.',
                'description' => 'يمنع حذف أي فاتورة بيع أو شراء تم تسليم بضاعتها بالمستودع ويلزم بعمل مردودات رسمية للحفاظ على دقة الأرصدة والقيود.',
                'warn_label' => 'محاولات الحذف الممنوعة',
                'crit_label' => 'حد الحظر التام الصارم',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'محاولة',
            ],
            [
                'rule_key' => 'audit_reprint_invoice_repetition',
                'rule_name' => 'تكرار إعادة طباعة إيصال الفاتورة أكثر من مرة (Duplicate Reprint)',
                'screen_name' => 'طباعة الكاشير ونقاط البيع',
                'screen_file' => 'pos-invoice-print.php / direct-sales.php',
                'category' => 'SECURITY',
                'alert_preview' => '⚠️ تنبيه تدقيق: تم تكرار إعادة طباعة إيصال هذه الفاتورة لعدة مرات لمنع استخدام الإيصال لعميل آخر!',
                'description' => 'يراقب عدد مرات طباعة نفس الإيصال لمنع إعطاء وصل مطبوع مسبقاً لعميل جديد وسرقة المبلغ.',
                'warn_label' => 'عدد مرات إعادة الطباعة التحذيري',
                'crit_label' => 'الحد الحرج للتكرار المريب',
                'is_enabled' => 1,
                'warning_threshold' => 2,
                'critical_threshold' => 4,
                'unit' => 'مرات طباعة',
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
                'rule_name' => 'تعديل أو حذف أي سند مالي أو فاتورة سابقة مرحلة ومقفلة',
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
                'rule_name' => 'السندات والفواتير المعلقة أو المسودة غير المرحلة (> 24 ساعة)',
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
                'rule_name' => 'عدم اتزان القيود المحاسبية (المدين لا يساوي الدائن)',
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
                'rule_key' => 'sec_mass_data_export_dlp',
                'rule_name' => 'تصدير كميات ضخمة من البيانات وقوائم العملاء لملفات Excel/PDF (حماية DLP)',
                'screen_name' => 'شاشات التقارير وتصدير الجداول',
                'screen_file' => 'analytics.php / master-data.php / inventory-report.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚨 إنذار أمن بيانات (DLP): رصد محاولة تصدير كمية ضخمة من سجلات العملاء/الأسعار إلى ملف خارجي!',
                'description' => 'أقوى نظام حماية لمنع تسريب بيانات العملاء وقوائم الأسعار والأسماء للمنافسين عبر التصدير الجماعي.',
                'warn_label' => 'عدد السجلات التحذيري للتصدير',
                'crit_label' => 'الحد الحرج للإغلاق والتحقيق الأمني',
                'is_enabled' => 1,
                'warning_threshold' => 100,
                'critical_threshold' => 500,
                'unit' => 'سجل مصدر',
            ],
            [
                'rule_key' => 'audit_sod_conflict_alert',
                'rule_name' => 'انتهاك مبدأ فصل المهام الرقابي المحاسبي (Maker-Checker Conflict)',
                'screen_name' => 'سجل الحركات والصلاحيات الإدارية',
                'screen_file' => 'settings.php / vouchers-hub.php',
                'category' => 'SECURITY',
                'alert_preview' => '🚨 إنذار رقابة SoD: قام نفس المستخدم بإنشاء المستند المالي واعتماده وصرفه بنفسه دون تدقيق طرف ثانٍ!',
                'description' => 'يطبق معيار الرقابة الدولية بفصل مهام الإدخال عن الاعتماد والصرف لضمان عدم التواطؤ الفردي.',
                'warn_label' => 'مرات الانتهاك التحذيرية',
                'crit_label' => 'حد الحظر الرقابي الصارم',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'عملية',
            ],
            [
                'rule_key' => 'hr_late_checkin',
                'rule_name' => 'تأخر حضور الكاشير أو موظف المبيعات عن موعد بداية الوردية',
                'screen_name' => 'سجل الحضور والورديات ونقاط البيع',
                'screen_file' => 'settings.php / vouchers-hub.php',
                'category' => 'HR',
                'alert_preview' => '⚠️ تنبيه حضور: تأخر موظف الكاشير/المبيعات عن موعد بدء الوردية المحددة بالنظام!',
                'description' => 'يراقب أوقات فتح الورديات وتسجيل الدخول وينبه الإدارة عند تأخر الموظف عن الدوام.',
                'warn_label' => 'دقائق التأخير التحذيرية المسموحة',
                'crit_label' => 'دقائق التأخير الحرجة للخصم',
                'is_enabled' => 1,
                'warning_threshold' => 15,
                'critical_threshold' => 45,
                'unit' => 'دقيقة',
            ],
            [
                'rule_key' => 'hr_unclosed_shift',
                'rule_name' => 'بقاء وردية الكاشير مفتوحة بعد انتهاء ساعات العمل الرسمية',
                'screen_name' => 'إغلاق الورديات اليومية وتقارير الكاشير',
                'screen_file' => 'vouchers-hub.php / single-cash-payment.php',
                'category' => 'HR',
                'alert_preview' => '⚠️ تنبيه وردية معلقة: وردية كاشير ما زالت مفتوحة ولم يتم تسليم الصندوق وإغلاقها بعد انتهاء الدوام!',
                'description' => 'ينبه بضرورة تسليم النقد وإجراء الإغلاق اليومي للدرج في موعده لمنع تداخل العمليات.',
                'warn_label' => 'ساعات التأخر التحذيرية للإغلاق',
                'crit_label' => 'ساعات التأخر الحرجة للتحقيق',
                'is_enabled' => 1,
                'warning_threshold' => 2,
                'critical_threshold' => 6,
                'unit' => 'ساعة',
            ],
            [
                'rule_key' => 'sec_new_device_login',
                'rule_name' => 'تسجيل دخول للمستخدم من جهاز أو متصفح أو IP جديد غير مسجل',
                'screen_name' => 'شاشة تسجيل الدخول وأمان النظام',
                'screen_file' => 'login.php / settings.php',
                'category' => 'HR',
                'alert_preview' => '🚨 تنبيه أمان: محاولة تسجيل دخول لحساب مستخدم من جهاز أو عنوان IP جديد غير معتمد!',
                'description' => 'يرصد أي دخول من أجهزة غير معروفة ويرسل إشعاراً أمنياً للمدير لمنع اختراق الحسابات.',
                'warn_label' => 'عدد الأجهزة الجديدة التحذيري',
                'crit_label' => 'حد الخطر الأمني للإغلاق',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'جهاز',
            ],
            [
                'rule_key' => 'sec_after_hours_transaction',
                'rule_name' => 'إجراء عمليات بيع أو صرف نقدي خارج ساعات الدوام (بعد منتصف الليل)',
                'screen_name' => 'سجل العمليات والرقابة العامة',
                'screen_file' => 'direct-sales.php / single-cash-payment.php',
                'category' => 'HR',
                'alert_preview' => '🚨 إشعار رقابي: تم تسجيل معاملة مالية/بيعية خارج أوقات العمل الرسمية للمنشأة!',
                'description' => 'ينبه الإدارة عند تسجيل فواتير أو سندات صرف في أوقات متأخرة من الليل بعد إغلاق الفروع.',
                'warn_label' => 'عدد المعاملات التحذيرية خارج الدوام',
                'crit_label' => 'حد الخطر الحرج للمراجعة',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'معاملة',
            ],
            [
                'rule_key' => 'fa_fully_depreciated_asset_in_use',
                'rule_name' => 'استمرار تشغيل واستخدام أصل ثابت تم إهلاكه دفترياً بالكامل',
                'screen_name' => 'سجل الأصول الثابتة وجداول الإهلاك',
                'screen_file' => 'master-data.php / analytics.php',
                'category' => 'FIXEDASSETS',
                'alert_preview' => '⚠️ تنبيه أصول ثابتة: أصل ثابت مسجل تم إهلاكه دفترياً بنسبة 100% وما زال يعمل بالخدمة!',
                'description' => 'يحصر الأصول المنتهية دفترياً لإجراء إعادة التقييم المحاسبي أو التخريد وفق المعايير الدولية.',
                'warn_label' => 'عدد الأصول المهلكة بالكامل',
                'crit_label' => 'الحد الحرج لإعادة التقييم',
                'is_enabled' => 1,
                'warning_threshold' => 3,
                'critical_threshold' => 10,
                'unit' => 'أصل',
            ],
            [
                'rule_key' => 'fa_excessive_maintenance_cost',
                'rule_name' => 'تراكم مصاريف صيانة الأصل الثابت بما يقارب قيمته الاستبدالية',
                'screen_name' => 'مصاريف الصيانة والتشغيل للأصول',
                'screen_file' => 'single-cash-payment.php / analytics.php',
                'category' => 'FIXEDASSETS',
                'alert_preview' => '⚠️ تنبيه كفاءة أصول: إجمالي مصاريف صيانة الأصل قاربت تكلفة شراء بديل جديد له!',
                'description' => 'ينبه الإدارة المالية بجدوى استبدال الآلة أو السيارة بدلاً من استنزاف النقدية في صيانة متكررة.',
                'warn_label' => 'نسبة تكاليف الصيانة التحذيرية من قيمة الأصل',
                'crit_label' => 'نسبة الصيانة الحرجة للاستبدال الفوري',
                'is_enabled' => 1,
                'warning_threshold' => 50,
                'critical_threshold' => 80,
                'unit' => '%',
            ],
            [
                'rule_key' => 'tax_vat_return_discrepancy',
                'rule_name' => 'فروقات في احتساب ضريبة القيمة المضافة بين المبيعات والمشتريات',
                'screen_name' => 'الإقرارات الضريبية وضريبة القيمة المضافة',
                'screen_file' => 'analytics.php / general_ledger_v2.php',
                'category' => 'FIXEDASSETS',
                'alert_preview' => '🚨 تنبيه ضريبي: وجود تباين في ضريبة القيمة المضافة المحتسبة يستوجب التدقيق قبل رفع الإقرار!',
                'description' => 'مطابقة فورية آلية بين ضرائب المخرجات والمدخلات لضمان عدم التعرض لغرامات ضريبية.',
                'warn_label' => 'مبلغ التباين الضريبي التحذيري',
                'crit_label' => 'حد الخطر الضريبي الحرج',
                'is_enabled' => 1,
                'warning_threshold' => 1000,
                'critical_threshold' => 10000,
                'unit' => 'SDG',
            ],
            [
                'rule_key' => 'audit_sensitive_report_print',
                'rule_name' => 'طباعة تقارير مالية سرية (قائمة الدخل، الأرباح، الميزانية) خارج الدوام',
                'screen_name' => 'سجل طباعة القوائم المالية والتحليلات',
                'screen_file' => 'analytics.php / income_statement_v2.php',
                'category' => 'FIXEDASSETS',
                'alert_preview' => '🚨 إنذار تدقيق مالي: تم رصد طباعة تقرير مالي حساس/قائمة أرباح في غير أوقات الدوام الرسمي!',
                'description' => 'يسجل كل عملية طباعة للقوائم المالية الحساسة ويرسل إشعاراً أمنياً للإدارة لمنع تسريب الأرقام.',
                'warn_label' => 'مرات الطباعة التحذيرية خارج الدوام',
                'crit_label' => 'حد الخطر الحرج للمراجعة الفورية',
                'is_enabled' => 1,
                'warning_threshold' => 1,
                'critical_threshold' => 1,
                'unit' => 'تقرير',
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
// 2. محرك فحص البيانات الحية والذكاء التحليلي الشامل
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
        
        // 1. فحص تجاوز الحد الائتماني وتركز الديون
        if (isset($settings['sales_credit_limit_exceeded']) || isset($settings['credit_debt_concentration_risk'])) {
            try {
                $warnPct = isset($settings['sales_credit_limit_exceeded']) ? (float)$settings['sales_credit_limit_exceeded']['warning_threshold'] : 80;
                $critPct = isset($settings['sales_credit_limit_exceeded']) ? (float)$settings['sales_credit_limit_exceeded']['critical_threshold'] : 100;
                $stmt = $pdo->prepare("SELECT data FROM customers WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                $totalCompanyDebt = 0;
                $customerList = [];
                while ($row = $stmt->fetch()) {
                    $c = json_decode($row['data'], true);
                    if ($c) {
                        $bal = (float)($c['currentBalance'] ?? 0);
                        if ($bal > 0) $totalCompanyDebt += $bal;
                        $customerList[] = $c;
                    }
                }
                foreach ($customerList as $c) {
                    $name = $c['name'] ?? 'عميل غير معروف';
                    $bal = (float)($c['currentBalance'] ?? 0);
                    $limit = (float)($c['creditLimit'] ?? 0);
                    if ($limit > 0 && isset($settings['sales_credit_limit_exceeded'])) {
                        $pct = ($bal / $limit) * 100;
                        if ($pct >= $critPct) {
                            $msg = "🚨 خطر ائتماني حرج! العميل [" . $name . "] تجاوز الحد الائتماني بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . " / الحد: " . number_format($limit, 2) . ")";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'sales_credit_limit_exceeded', 'RED', ?, 'direct-sales.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        } elseif ($pct >= $warnPct) {
                            $msg = "⚠️ تنبيه ائتماني! العميل [" . $name . "] اقترب من سقف الائتمان المسموح بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . ")";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'sales_credit_limit_exceeded', 'YELLOW', ?, 'direct-sales.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        }
                    }
                    if ($totalCompanyDebt > 0 && isset($settings['credit_debt_concentration_risk'])) {
                        $concPct = ($bal / $totalCompanyDebt) * 100;
                        $concCrit = (float)$settings['credit_debt_concentration_risk']['critical_threshold'];
                        if ($concPct >= $concCrit && $bal > 50000) {
                            $msg = "⚠️ تركز مديونية عالي! العميل [" . $name . "] يستحوذ على " . round($concPct) . "% من إجمالي مديونيات المنشأة (مبلغ: " . number_format($bal, 2) . ")!";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'credit_debt_concentration_risk', 'YELLOW', ?, 'customer_exposure.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
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

        // 3. فحص المخزون والأصناف والذكاء التحليلي (السحب السريع، الرواكد، الحدود)
        if (isset($settings['sales_negative_stock']) || isset($settings['inv_critical_zero_stock']) || isset($settings['inv_low_stock']) || isset($settings['inv_overstock_capacity']) || isset($settings['inv_negative_stock_balance']) || isset($settings['master_selling_below_cost'])) {
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
                        $cost = (float)($item['costPrice'] ?? ($item['avgCost'] ?? ($item['purchasePrice'] ?? 0)));
                        $sellPrice = (float)($item['sellingPrice'] ?? ($item['price'] ?? 0));
                        $minStock = (float)($item['minStock'] ?? $lowWarn);

                        if ($cost > 0 && $sellPrice > 0 && $sellPrice < $cost && isset($settings['master_selling_below_cost'])) {
                            $msg = "⚠️ خلل تسعير بالبيانات الأساسية! الصنف [" . $name . "] سعر بيعه الافتراضي (" . $sellPrice . ") أقل من سعر التكلفة (" . $cost . ")!";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message, screen_file) VALUES (?, 'master_selling_below_cost', 'YELLOW', ?, 'master-data.php')")->execute([$GLOBAL_TENANT_ID, $msg]);
                        }
                        if ($stock < 0 && (isset($settings['sales_negative_stock']) || isset($settings['inv_negative_stock_balance']))) {
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

// تقسيم التنبيهات حسب الأقسام التسعة المعتمدة للقمة العالمية
$sections = [
    'MASTER' => [
        'id' => 'sec-master',
        'category' => 'MASTER',
        'title' => '🏛️ البيانات الأساسية وتعريفات الأصناف والعملاء والخزائن',
        'screens' => 'البيانات الأساسية master-data.php / بطاقات الأصناف والعملاء والموردين',
        'icon' => 'fa-database',
        'color' => '#38bdf8',
        'border' => 'rgba(56, 189, 248, 0.4)',
        'rules' => []
    ],
    'INVENTORY' => [
        'id' => 'sec-inventory',
        'category' => 'INVENTORY',
        'title' => '🧠 المخازن والأصناف والذكاء التحليلي وحركة البضائع',
        'screens' => 'تقرير وإدارة المخزون inventory-report.php / المخزون الذكي والرواكد smart_inventory_report.php / الجرد والعنابر',
        'icon' => 'fa-warehouse',
        'color' => '#a78bfa',
        'border' => 'rgba(167, 139, 250, 0.4)',
        'rules' => []
    ],
    'SALES' => [
        'id' => 'sec-sales',
        'category' => 'SALES',
        'title' => '🛒 شاشات المبيعات وفواتير البيع ونقاط البيع POS والكاشير',
        'screens' => 'المبيعات المباشرة direct-sales.php / فواتير المبيعات sales.php / الفاتورة الاحترافية pro-invoice.php / طباعة الكاشير pos-invoice-print.php',
        'icon' => 'fa-cash-register',
        'color' => '#22d3ee',
        'border' => 'rgba(34, 211, 238, 0.4)',
        'rules' => []
    ],
    'PURCHASE' => [
        'id' => 'sec-purchase',
        'category' => 'PURCHASE',
        'title' => '📦 شاشات المشتريات وأوامر التوريد وسلاسل الإمداد والمصانع',
        'screens' => 'المشتريات purchase.php / أوامر الشراء purchase-order.php / المطابقة الثلاثية 3-Way',
        'icon' => 'fa-truck-ramp-box',
        'color' => '#fb923c',
        'border' => 'rgba(251, 146, 60, 0.4)',
        'rules' => []
    ],
    'FINANCE' => [
        'id' => 'sec-finance',
        'category' => 'FINANCE',
        'title' => '💰 شاشات الخزينة والمصارف وسندات القبض والصرف وإدارة النقد',
        'screens' => 'سند الصرف الفردي single-cash-payment.php / سند الصرف المتعدد multi-cash-payment.php / سند القبض single-cash-receipt.php / مركز السندات vouchers-hub.php / التدفقات cashflow_modern.php',
        'icon' => 'fa-vault',
        'color' => '#34d399',
        'border' => 'rgba(52, 211, 153, 0.4)',
        'rules' => []
    ],
    'CREDIT' => [
        'id' => 'sec-credit',
        'category' => 'CREDIT',
        'title' => '👥 شاشات العملاء والمديونيات وسلوك السداد والتحصيل',
        'screens' => 'لوحة الائتمان credit_limits_dashboard.php / انكشاف العملاء customer_exposure.php / ركود العملاء customer_churn.php',
        'icon' => 'fa-users-gear',
        'color' => '#fbbf24',
        'border' => 'rgba(251, 191, 36, 0.4)',
        'rules' => []
    ],
    'SECURITY' => [
        'id' => 'sec-security',
        'category' => 'SECURITY',
        'title' => '🛡️ شاشات الأمان والرقابة ومنع التلاعب وحماية البيانات DLP وفصل المهام SoD',
        'screens' => 'لوحة التحكم magaiees_unified_dashboard.php / الإعدادات والنسخ الاحتياطي settings.php / دفتر الأستاذ general_ledger_v2.php / سجل التدقيق المحاسبي',
        'icon' => 'fa-shield-halved',
        'color' => '#f87171',
        'border' => 'rgba(248, 113, 113, 0.4)',
        'rules' => []
    ],
    'HR' => [
        'id' => 'sec-hr',
        'category' => 'HR',
        'title' => '⏱️ شاشات المستخدمين والورديات وساعات الحضور والانصراف',
        'screens' => 'إدارة الورديات vouchers-hub.php / شاشة الدخول login.php / إعدادات المستخدمين settings.php',
        'icon' => 'fa-user-clock',
        'color' => '#e879f9',
        'border' => 'rgba(232, 121, 249, 0.4)',
        'rules' => []
    ],
    'FIXEDASSETS' => [
        'id' => 'sec-fixedassets',
        'category' => 'FIXEDASSETS',
        'title' => '🏢 شاشات الأصول الثابتة والإهلاك والضرائب والتقارير السرية',
        'screens' => 'سجل الأصول master-data.php / التقارير المالية analytics.php / قائمة الدخل income_statement_v2.php',
        'icon' => 'fa-building-columns',
        'color' => '#f472b6',
        'border' => 'rgba(244, 114, 182, 0.4)',
        'rules' => []
    ]
];

foreach ($rules as $r) {
    $cat = $r['category'];
    if ($cat === 'SUPPLY') $cat = 'PURCHASE';
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
    <title>مصفوفة الرقابة وإدارة التنبيهات العالمية Titanium (88 قاعدة) | Al-Sajana ERP</title>

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
            padding: 12px 14px;
            border-radius: 18px;
            font-weight: 900;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 4px;
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
                    مصفوفة الرقابة وإدارة التنبيهات العالمية Titanium (88 قاعدة)
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">
                    منظومة رقابة ومكافحة احتيال عالمية تتفوق على أعتى البرامج (DLP، فصل المهام SoD، فتح الدرج، المطابقة الثلاثية 3-Way، حظر الحذف، والذكاء السلوكي).
                </p>
            </div>

            <!-- Quick Bulk Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" onclick="bulkToggle('ALL', 1)" class="px-4 py-2 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/40 font-black rounded-xl text-xs transition flex items-center gap-1.5 shadow-md">
                    <i class="fa-solid fa-check-double"></i> تفعيل الكل (88 قاعدة)
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
                <span class="text-xs text-sky-400 font-bold block">🏢 الأقسام الخاضعة للرقابة</span>
                <span class="text-3xl font-black text-sky-400 mt-2 block">9 قطاعات</span>
                <span class="text-xs text-slate-400 mt-1 block">شاملة الـ DLP والأصول والـ SoD</span>
            </div>
        </div>

        <!-- 🎛️ 10 PILLARS NAVIGATION 🎛️ -->
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-2" id="pillars-container">
            <div class="pillar-card active" style="--p-color:#f43f5e; --p-border:rgba(244,63,94,0.4);" onclick="filterPillar('ALL', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-layer-group text-rose-400 text-base"></i>
                    <span class="text-[10px] bg-rose-500/20 text-rose-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($rules); ?></span>
                </div>
                <div class="font-black text-xs mt-1">🌟 الكل</div>
                <div class="text-[9px] text-slate-400">88 قاعدة</div>
            </div>

            <div class="pillar-card" style="--p-color:#38bdf8; --p-border:rgba(56,189,248,0.3);" onclick="filterPillar('MASTER', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-database text-sky-400 text-base"></i>
                    <span class="text-[10px] bg-sky-500/20 text-sky-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['MASTER']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">🏛️ الأساسية</div>
                <div class="text-[9px] text-slate-400">الباركود والحدود</div>
            </div>

            <div class="pillar-card" style="--p-color:#a78bfa; --p-border:rgba(167,139,250,0.3);" onclick="filterPillar('INVENTORY', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-warehouse text-purple-400 text-base"></i>
                    <span class="text-[10px] bg-purple-500/20 text-purple-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['INVENTORY']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">🧠 المخازن</div>
                <div class="text-[9px] text-slate-400">الذكاء والرواكد</div>
            </div>

            <div class="pillar-card" style="--p-color:#22d3ee; --p-border:rgba(34,211,238,0.3);" onclick="filterPillar('SALES', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-cash-register text-cyan-400 text-base"></i>
                    <span class="text-[10px] bg-cyan-500/20 text-cyan-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['SALES']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">🛒 المبيعات</div>
                <div class="text-[9px] text-slate-400">الكاشير والأسعار</div>
            </div>

            <div class="pillar-card" style="--p-color:#fb923c; --p-border:rgba(251,146,60,0.3);" onclick="filterPillar('PURCHASE', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-truck-ramp-box text-orange-400 text-base"></i>
                    <span class="text-[10px] bg-orange-500/20 text-orange-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['PURCHASE']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">📦 المشتريات</div>
                <div class="text-[9px] text-slate-400">المطابقة 3-Way</div>
            </div>

            <div class="pillar-card" style="--p-color:#34d399; --p-border:rgba(52,211,153,0.3);" onclick="filterPillar('FINANCE', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-vault text-emerald-400 text-base"></i>
                    <span class="text-[10px] bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['FINANCE']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">💰 الخزينة</div>
                <div class="text-[9px] text-slate-400">السيولة والتحايل</div>
            </div>

            <div class="pillar-card" style="--p-color:#fbbf24; --p-border:rgba(251,191,36,0.3);" onclick="filterPillar('CREDIT', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-users-gear text-amber-400 text-base"></i>
                    <span class="text-[10px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['CREDIT']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">👥 العملاء</div>
                <div class="text-[9px] text-slate-400">الائتمان والسلوك</div>
            </div>

            <div class="pillar-card" style="--p-color:#f87171; --p-border:rgba(248,113,113,0.3);" onclick="filterPillar('SECURITY', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-shield-halved text-rose-400 text-base"></i>
                    <span class="text-[10px] bg-rose-500/20 text-rose-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['SECURITY']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">🛡️ الأمان DLP</div>
                <div class="text-[9px] text-slate-400">منع الحذف و SoD</div>
            </div>

            <div class="pillar-card" style="--p-color:#e879f9; --p-border:rgba(232,121,249,0.3);" onclick="filterPillar('HR', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-user-clock text-fuchsia-400 text-base"></i>
                    <span class="text-[10px] bg-fuchsia-500/20 text-fuchsia-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['HR']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">⏱️ الورديات</div>
                <div class="text-[9px] text-slate-400">التأخير والدوام</div>
            </div>

            <div class="pillar-card" style="--p-color:#f472b6; --p-border:rgba(244,114,182,0.3);" onclick="filterPillar('FIXEDASSETS', this)">
                <div class="flex items-center justify-between">
                    <i class="fa-solid fa-building-columns text-pink-400 text-base"></i>
                    <span class="text-[10px] bg-pink-500/20 text-pink-300 px-1.5 py-0.5 rounded-full font-bold"><?php echo count($sections['FIXEDASSETS']['rules']); ?></span>
                </div>
                <div class="font-black text-xs mt-1">🏢 الأصول</div>
                <div class="text-[9px] text-slate-400">الإهلاك والضرائب</div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full">
            <input type="text" id="matrix-search" oninput="searchMatrix(this.value)" placeholder="🔍 ابحث عن أي تنبيه أو شاشة أو كلمة (مثلاً: سعر التكلفة، المخزون، الشيكات، المديونيات، إغلاق الوردية، منع الحذف، فتح الدرج، DLP)..." class="w-full bg-slate-900 border-2 border-slate-700 rounded-2xl px-5 py-3 text-sm font-bold text-slate-100 outline-none focus:border-rose-500 pr-12 shadow-lg">
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
            نظام Al-Sajana Smart ERP 2026 · مصفوفة الرقابة والتنبيهات الموحدة العالمية (Titanium Tier-1)
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