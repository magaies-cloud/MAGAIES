<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); } ?>
<?php $page_title = 'البيانات الأساسية'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="has-sidebar">
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
<head>
        <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>السيطرة (MAGAIEES) - البيانات الأساسية</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
<link rel="stylesheet" href="css/screens/master-data-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/master-data-s1.css') ?: '2027' ?>">

</head>
<body class="has-sidebar">
    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="dash-main" id="md-proportional-root">

        <!-- Unified Premium Header -->
        <?php $header_theme_class = 'header-theme-masterdata'; include 'includes/header.php'; ?>

        <aside class="md-tabs-sidebar">
            <button class="md-tab-btn tab-warehouses active" onclick="openTab(event, 'tab-warehouses')"><span class="md-tab-icon">🏢</span> المخازن</button>
            <button class="md-tab-btn tab-customers" onclick="openTab(event, 'tab-customers')"><span class="md-tab-icon">👥</span> العملاء</button>
            <button class="md-tab-btn tab-suppliers" onclick="openTab(event, 'tab-suppliers')"><span class="md-tab-icon">🏭</span> الموردين</button>
            <button class="md-tab-btn tab-banks" onclick="openTab(event, 'tab-banks')"><span class="md-tab-icon">🏦</span> البنوك</button>
            <button class="md-tab-btn tab-safes" onclick="openTab(event, 'tab-safes')"><span class="md-tab-icon">💰</span> الخزائن</button>
            <button class="md-tab-btn tab-items" onclick="openTab(event, 'tab-items')"><span class="md-tab-icon">📦</span> الأصناف</button>
            <button class="md-tab-btn tab-employees" onclick="openTab(event, 'tab-employees')"><span class="md-tab-icon">👤</span> الموظفين</button>
            <button class="md-tab-btn tab-costcenters" onclick="openTab(event, 'tab-costcenters')">مراكز التكلفة</button>
            <button class="md-tab-btn tab-exit" onclick="goBackOrExit()"><span class="md-tab-icon">🚪</span> خروج</button>
        </aside>

        <main class="md-content-area">
            
            <!-- شريط الأوامر السيادي للدساتير الـ 16 وبوابة زيرو خطأ (تم إخفاؤه بناءً على طلب المستخدم لإزالة ازدحام الأزرار العلوية) -->
            <div id="md-c1-command-bar" style="display:none !important; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; padding:8px 14px; margin-bottom:12px; border-radius:12px; border:1.5px solid rgba(56,189,248,0.28); background:rgba(15,23,42,0.55);">
                <input type="file" id="md-excel-import-input" accept=".csv,.txt" style="display:none;" onchange="handleActiveTabExcelImport(event)">
                <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                    <button type="button" id="btn-md-zoom-out" class="btn-text" style="height:32px; padding:0 9px; border-radius:8px; font-size:0.8rem;" onclick="window.setMdProportionalZoom('step', -0.05)" data-guide-title="تصغير متناسب (6:8)" data-guide-desc="تصغير الشاشة طولاً وعرضاً بنفس النسبة الهندسية (6 إلى 8) مع الحفاظ على وضوح الحقول.">➖</button>
                    <button type="button" id="btn-md-zoom-reset" class="btn-text" style="height:32px; padding:0 9px; border-radius:8px; font-size:0.8rem;" onclick="window.setMdProportionalZoom('reset')" data-guide-title="استعادة المقاس (100%)" data-guide-desc="إعادة أبعاد الشاشة إلى النسبة القياسية 100% (أو عبر Ctrl + رولة الماوس)."><span id="md-zoom-pct-badge">100%</span></button>
                    <button type="button" id="btn-md-zoom-in" class="btn-text" style="height:32px; padding:0 9px; border-radius:8px; font-size:0.8rem;" onclick="window.setMdProportionalZoom('step', 0.05)" data-guide-title="تكبير متناسب (6:8)" data-guide-desc="تكبير الشاشة طولاً وعرضاً بنفس النسبة الهندسية (6 إلى 8).">➕</button>
                    <button type="button" id="btn-md-c1-theme-switch" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem;" onclick="window.cycleMasterDataThemeInstant()" data-guide-title="تبديل السمة اللحظي" data-guide-desc="التبديل الفوري بين السمات السبع مع سريانها تلقائياً على الشاشة والشاشات الوليدة.">🎨 السمة</button>
                    <button type="button" id="btn-md-c2-quick-add" class="btn-add" style="height:32px !important; min-height:32px !important; padding:0 11px !important; border-radius:8px; font-size:0.8rem; background:linear-gradient(135deg,#10b981,#059669);" onclick="window.openUniversalQuickEntityModal()" data-guide-title="الإضافة السريعة الشاملة (+) [Alt+N]" data-guide-desc="فتح شاشة وليدة فورية لإضافة عميل أو مورد أو صنف أو مخزن أو بنك دون ضياع أي بيانات مكتوبة في البطاقة الحالية.">➕ إضافة سريعة</button>
                    <button type="button" id="btn-md-c7-reset-form" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#34d399; border-color:rgba(16,185,129,0.45);" onclick="window.resetActiveMdFormAndNew()" data-guide-title="جديد / تصفير النموذج النشط [Alt+R]" data-guide-desc="تصفير وتفريغ خانات البطاقة المفتوحة حالياً وبدء إدخال سجل جديد فوراً بضغطة واحدة أو عبر الكيبورد.">🧹 جديد</button>
                    <button type="button" id="btn-md-c8-park" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fbbf24; border-color:rgba(245,158,11,0.5);" onclick="window.parkActiveMdCard()" data-guide-title="تعليق مؤقت للبطاقة/الفاتورة بضغطة زر [Alt+P]" data-guide-desc="تعليق البطاقة الحالية في الشريط العلوي المصغر وتفريغ الشاشة فوراً لخدمة العميل المستعجل، مع استرجاعها بضغطة واحدة دون ضياع حرف واحد.">⏸️ تعليق مؤقت <span id="md-c8-park-badge" style="background:rgba(245,158,11,0.25); padding:0 5px; border-radius:6px; font-size:0.7rem;">0</span></button>
                    <button type="button" id="btn-md-c7-undo" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fde047; border-color:rgba(250,204,21,0.45);" onclick="window.triggerMdInstantUndo()" data-guide-title="التراجع الفوري الآمن [Ctrl+Z]" data-guide-desc="استعادة أي سجل أو سطر وحدة حُذف بالخطأ بضغطة واحدة أو عبر اختصار Ctrl+Z.">↩️ تراجع <span id="md-c7-undo-badge" style="background:rgba(250,204,21,0.25); padding:0 5px; border-radius:6px; font-size:0.7rem;">0</span></button>
                    <button type="button" id="btn-md-c2-omni-search" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#38bdf8;" onclick="window.openMdQuickJumpPaletteModal()" data-guide-title="البحث العربي الذكي الشامل [Ctrl+K]" data-guide-desc="بحث فوري في 0ms عبر جميع الأقسام الثمانية مع تسامح كامل مع الحروف العربية (أ=ا، ة=ه، ى=ي).">⚡ بحث شامل</button>
                    <button type="button" id="btn-md-c2-restore-draft" class="btn-text" style="display:none; height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fde047;" onclick="window.restoreMdFormDraft()" data-guide-title="استعادة المسودة المحفوظة تلقائياً" data-guide-desc="استرجاع البيانات التي كتبتها قبل الإغلاق أو التحديث الخاطئ في أضعف إنترنت (2G).">💾 استعادة مسودة</button>
                    <span id="md-c7-conn-pill" onclick="window.openMdCrdtSyncRadarModal()" style="height:30px; padding:0 9px; border-radius:999px; font-size:0.74rem; font-weight:900; display:inline-flex; align-items:center; gap:5px; cursor:pointer; border:2px solid #10b981; background:rgba(16,185,129,0.14); color:#34d399;" data-guide-title="المؤشر الضوئي المطمئن لحالة الاتصال والمزامنة" data-guide-desc="مؤشر حي يطمئن المحاسب بأن الاتصال والمزامنة اللحظية والحفظ المحلي يعملان بأمان تام 100%.">🟢 متصل ومؤمّن</span>
                </div>
                <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                    <button type="button" onclick="openOpeningEquilibriumModal()" style="height:32px; background:rgba(16,185,129,0.16); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:0 10px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <span>⚖️</span> <span>القيد الافتتاحي</span>
                    </button>
                    <button type="button" onclick="openBulkPriceUpdaterModal()" style="height:32px; background:rgba(245,158,11,0.16); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); padding:0 10px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <span>📈</span> <span>الأسعار</span>
                    </button>
                    <button type="button" onclick="openSmartClipboardPasteModal()" style="height:32px; background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:0 10px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <span>📋</span> <span>لصق Excel</span>
                    </button>
                    <button type="button" onclick="exportActiveTabToExcel()" style="height:32px; background:rgba(16,185,129,0.16); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:0 10px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <span>📤</span> <span>تصدير</span>
                    </button>
                    <button type="button" onclick="triggerActiveTabExcelImport()" style="height:32px; background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:0 10px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <span>📥</span> <span>استيراد</span>
                    </button>
                    <button type="button" id="md-btn-rollback-import" onclick="window.rollbackLastMasterImport()" style="display:none; height:32px; background:rgba(239,68,68,0.18); color:#fca5a5; border:1px solid rgba(239,68,68,0.45); padding:0 10px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; align-items:center; gap:5px;">
                        <span>↩️</span> <span>تراجع</span>
                    </button>
                    <button type="button" onclick="openActiveTabPrintAndShareModal()" style="height:32px; background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.45); padding:0 10px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <span>🖨️</span> <span>طباعة</span>
                    </button>
                    <button type="button" id="btn-md-c1-dup-scanner" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem;" onclick="window.openMdHealthAndDuplicateScannerModal()" data-guide-title="فاحص التكرارات والنواقص" data-guide-desc="فحص شامل لكافة الأقسام الثمانية لاكتشاف أي تكرار في الأسماء أو الهواتف أو الأصناف الناقصة.">🔍 فحص</button>
                    <button type="button" id="btn-md-c1-backup-vault" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem;" onclick="window.openMdUnifiedBackupVaultModal()" data-guide-title="خزنة النسخ الاحتياطي" data-guide-desc="حفظ وتصدير نسخة احتياطية كاملة لكافة البيانات الأساسية الثمانية.">📦 نسخ احتياطي</button>
                    <button type="button" id="btn-md-c3-whatsapp-hub" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#34d399; border-color:rgba(16,185,129,0.45);" onclick="window.openMdWhatsAppQuickHubModal()" data-guide-title="مشاركة البطاقات عبر واتساب" data-guide-desc="إرسال بطاقة عميل أو مورد أو حساب بنكي أو قائمة أسعار منسقة عبر واتساب بضغطة واحدة.">💬 واتساب</button>
                    <button type="button" id="btn-md-c4-impact-map" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fde047; border-color:rgba(250,204,21,0.5);" onclick="window.openMdInteractiveImpactMapModal()" data-guide-title="خريطة الأثر الثلاثية والقاعدة الذهبية (3-Stage SVG)" data-guide-desc="عرض ونطق الملخص العام للشاشة (من أين تأتي المعلومة ⬅ عمل الشاشة ⬅ أين تُسمّع في التقارير والشاشات) والقاعدة الذهبية.">🗺️ خريطة الأثر</button>
                    <button type="button" id="btn-md-c5-role-sim" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#c084fc; border-color:rgba(192,132,252,0.5);" onclick="window.openMdRoleSimulatorModal()" data-guide-title="تكيّف الصلاحيات والسرية (Role-Aware UI & Guide)" data-guide-desc="ضبط ومحاكاة صلاحيات المستخدم الحالي ودرجة السرية (1–10)؛ يتخطى الكيرسر والمرشد الذكي أي خانة أو زر غير مصرح به تلقائياً.">🔐 الصلاحيات</button>
                    <button type="button" id="btn-md-c5-golden-catalog" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fbbf24; border-color:rgba(251,191,36,0.5);" onclick="window.openMdGoldenRulesCatalogModal()" data-guide-title="موسوعة القواعد الذهبية والاستشعار الذاتي" data-guide-desc="عرض القاعدة الذهبية لكل قسم وعنصر في الشاشة، وسجل العناصر المكتشفة تلقائياً بمحرك التحديث الذاتي للمرشد الذكي.">👑 القواعد</button>
                    <button type="button" id="btn-md-c6-sync-radar" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#38bdf8; border-color:rgba(56,189,248,0.5);" onclick="window.openMdCrdtSyncRadarModal()" data-guide-title="رادار التزامن الصاروخي (CRDT) واختبار 100 ألف سجل" data-guide-desc="إدارة ومراقبة طابور الأوفلاين، البث النانوي الفوري في كسر من الثانية، الدمج الذكي بدون تضارب بين الجوال واللابتوب (CRDT)، واختبار ثبات السرعة مع 100 ألف سجل.">📡 التزامن</button>
                    <button type="button" id="btn-md-c7-ophthalmic" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#a7f3d0; border-color:rgba(52,211,153,0.5);" onclick="window.openMdOphthalmicAndAbcModal()" data-guide-title="الطب البصري لراحة العين والأبجديات البرمجية (الدستور 7)" data-guide-desc="إدارة توحيد الأرقام والفاصلة، تنظيف المسافات واللصق الذكي، مسطرة منع انزلاق العين (Anti-Eye-Slip)، والمحاذاة الهندسية للأرقام بالمسطرة.">👁️ الطب البصري</button>
                    <button type="button" id="btn-md-c8-cognitive" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fde047; border-color:rgba(250,204,21,0.5);" onclick="window.openMdCognitiveErgonomicsModal()" data-guide-title="الراحة الذهنية، الآلة الحاسبة المدمجة، التفقيط، وحارس الأرقام الشاذة (الدستور 8)" data-guide-desc="إدارة الآلة الحاسبة المدمجة داخل الخانات، التفقيط العربي اللحظي بالحروف، حارس الإرهاق الذكي للأرقام الشاذة، وتعليق البطاقة بضغطة زر.">🧮 الحاسبة والتفقيط</button>
                    <button type="button" id="btn-md-c9-inspection" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#34d399; border-color:rgba(16,185,129,0.5);" onclick="window.openMdInspectionAndPartialReceiptModal()" data-guide-title="شاشة فحص واستلام وفرز البضاعة والاستلام الجزئي (الدستور 9)" data-guide-desc="فرز البضاعة الواردة إلى (سليم يدخل المخزن فوراً / تالف يُحول لمخزن التوالف بتسوية / مرفوض يُرد للمورد مع السبب) ومتابعة الاستلام الجزئي على دفعات.">🔬 فحص واستلام</button>
                    <button type="button" id="btn-md-c9-wh-transfer" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#38bdf8; border-color:rgba(56,189,248,0.5);" onclick="window.openMdWarehouseTransferAndRadarModal()" data-guide-title="التحويل المخزني المتوازن، تعدد الوحدات، ورادار الركود وحد الطلب (الدستور 9)" data-guide-desc="عرض الرصيد المتاح والمحجوز بكل وحدة قياس، تنفيذ إذن تحويل مخزني متوازن بين المخازن، وكشف الأصناف الواصلة لحد الطلب أو الراكدة.">🔄 تحويل ورادار</button>
                    <button type="button" id="btn-md-c10-landed-cost" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fbbf24; border-color:rgba(245,158,11,0.5);" onclick="window.openMdLandedCostAllocationModal()" data-guide-title="توزيع المصروفات المضافة وتكلفة الوحدة الفعلية (Landed Cost — الدستور 10)" data-guide-desc="إدخال مصروفات النقل والعتالة والشحن والرسوم وتوزيعها آلياً (حسب القيمة أو الوزن أو الكمية) لتحديث متوسط تكلفة الوحدة بدقة 100%.">🚚 التكلفة المضافة</button>
                    <button type="button" id="btn-md-c10-doc-lifecycle" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#c084fc; border-color:rgba(192,132,252,0.5);" onclick="window.openMdDocumentLifecycleAndReturnsModal()" data-guide-title="الدورة المستندية بضغطة زر والمرتجعات والقيود العكسية (الدستور 10)" data-guide-desc="تحويل عرض السعر أو الطلب إلى فاتورة وإذن مخزني وسند مالي بضغطة زر، وإدارة المرتجعات الكلية والجزئية والقيود العكسية الآلية.">🔗 الدورة والمرتجعات</button>
                    <button type="button" id="btn-md-c11-cheque-portfolio" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#38bdf8; border-color:rgba(56,189,248,0.5);" onclick="window.openMdChequePortfolioModal()" data-guide-title="حافظة الشيكات ودورة الشيكات الكاملة من الألف للياء ورادار الاستحقاق (الدستور 11)" data-guide-desc="إدارة دورة الشيكات الواردة والصادرة عبر الحساب الوسيط (في الحافظة ⬅ تحت التحصيل ⬅ محصل يغذي البنك فعلياً ⬅ مرتد يعكس القيد ويعيد مديونية العميل ⬅ مظهر لمورد بقيد ثلاثي متوازن) مع رادار مواعيد الاستحقاق وقفل منع تكرار رقم الشيك.">📝 الشيكات</button>
                    <button type="button" id="btn-md-c11-transfer-period-lock" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fde047; border-color:rgba(250,204,21,0.5);" onclick="window.openMdBankTransferAndPeriodLockModal()" data-guide-title="كاشف تكرار الإشعارات البنكية، إقفال الفترات المالية، وقفل سعر الصرف ومراكز التكلفة (الدستور 11)" data-guide-desc="منع استخدام رقم المرجع البنكي مرتين لمنع الاختلاس، توثيق الحساب المحول منه وإليه، قفل سعر الصرف الرسمي، ربط مراكز التكلفة، وإقفال الفترات المالية لمنع التعديل بأثر رجعي.">🔒 التحويلات والإقفال</button>
                    <button type="button" id="btn-md-c12-security-fortress" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#f87171; border-color:rgba(248,113,113,0.55);" onclick="window.openMdSecurityFortressAndPrintLockModal()" data-guide-title="قلعة الحصانة الأمنية، قفل الطباعة وبدل الفاقد، قفل التعديل والحذف، وباركود الصرف وحيد الاستعمال (الدستور 12)" data-guide-desc="منع الطباعة قبل الحفظ، ختم (نسخة أصلية — طبعة رقم 1)، قفل إعادة الطباعة إلا برقم سري للمدير وسبب مع علامة مائية قطرية (بدل فاقد — لا يُصرف بها بضاعة)، قفل التعديل والحذف بعد الطباعة، وفاحص باركود الصرف وحيد الاستعمال.">🔐 الحصانة والطباعة</button>
                    <button type="button" id="btn-md-c12-blind-closing" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#34d399; border-color:rgba(16,185,129,0.5);" onclick="window.openMdBlindCashClosingModal()" data-guide-title="نظام الجرد الأعمى للخزينة والوردية، وإشعار واتساب الرقيب الخارجي للعميل، وفصل الصلاحيات (الدستور 12)" data-guide-desc="إخفاء الرصيد المتوقع عن الكاشير وإلزامه بعدّ النقدية الفعلية أولاً ثم قيد أي عجز على ذمته الشخصية آلياً، وإرسال إشعار واتساب فوري للعميل الآجل لمنع اختلاس النقدية، وفصل البائع عن أمين المخزن بالباركود والمقاس.">🙈 الجرد الأعمى</button>
                    <button type="button" id="btn-md-c13-cyber-blackbox" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#38bdf8; border-color:rgba(56,189,248,0.55);" onclick="window.openMdCyberAndBlackBoxModal()" data-guide-title="التحصين السيبراني (AES-256)، القفل الزمني المشفر للأوفلاين، قفل الخمول السريع (F12)، والصندوق الأسود الرقابي (الدستور 13)" data-guide-desc="حماية ضد حقن الأكواد (SQLi/XSS)، تشفير بيانات الأوفلاين (AES-256)، كشف التلاعب بساعة الجهاز، قفل الخمول التلقائي بعد 3 دقائق أو بضغطة F12 برمز PIN سريع، والصندوق الأسود غير القابل للمسح شاملاً ما كتبه الموظف ثم مسحه قبل الحفظ.">🛡️ الصندوق الأسود</button>
                    <button type="button" id="btn-md-c13-owner-radar" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fb923c; border-color:rgba(251,146,60,0.55);" onclick="window.openMdOwnerSilentAlertRadarModal()" data-guide-title="لوحة التنبيهات الأمنية السرية للمالك بثلاث درجات خطورة (🟡 أصفر / 🟠 برتقالي / 🔴 أحمر — الدستور 13)" data-guide-desc="محرك تحليل سلوكي صامت خاص بالمالك وحده يصنف السلوك المشبوه لحظياً إلى (🟡 ملاحظة رقابية / 🟠 شبهة احتيال / 🔴 محاولة تلاعب صريحة).">🚨 رادار المالك</button>
                    <button type="button" id="btn-md-c14-global-reports" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#a7f3d0; border-color:rgba(52,211,153,0.55);" onclick="window.openMdGlobalReportsAndDrillDownModal()" data-guide-title="التقارير العالمية، الفلاتر اللحظية، التعمق بضغطة زر (Drill-Down)، الطباعة الموزونة (A4/80mm)، وثالوث إيماءات الجوال (الدستور 14)" data-guide-desc="فلاتر برقية بالتاريخ والعميل والمورد والمخزن والصنف والمستخدم مع دقة رصيد أول المدة والرصيد التراكمي 100%، فتح أصل المستند بضغطة زر، طباعة A4 و80mm، وحصر إيماءات الجوال في 3 حركات (سحب الحافة للرجوع / سحب للأسفل للإغلاق / سحب الصف للإجراءات).">📊 التقارير والجوال</button>
                    <button type="button" onclick="openMasterAuditLogModal()" style="height:32px; background:rgba(168,85,247,0.18); color:#e9d5ff; border:1px solid rgba(168,85,247,0.45); padding:0 10px; border-radius:8px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <span>🛡️</span> <span>الرقابة</span>
                        <span id="md-audit-count-badge" style="background:rgba(168,85,247,0.4); color:#fff; padding:1px 6px; border-radius:10px; font-size:0.7rem; font-family:monospace;">0</span>
                    </button>
                    <button type="button" id="btn-md-c15-mastery" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#fde047; border-color:rgba(250,204,21,0.55);" onclick="window.openMdC15FinalMasteryModal()" data-guide-title="الدستور الخامس عشر (15): حفظ مقاس (6:8)، فواصل المشتريات، وتوحيد الخطوط (+2)" data-guide-desc="ضبط وتوريث مقاس الشاشة بنسبة 6:8 تلقائياً عبر جميع الشاشات والشاشات الوليدة، وتثبيت فواصل الجداول المستقلة عن currentColor.">📐 الدستور 15</button>
                    <button type="button" id="btn-md-c16-turbo-pc" class="btn-text" style="height:32px; padding:0 10px; border-radius:8px; font-size:0.8rem; color:#38bdf8; border-color:rgba(56,189,248,0.55);" onclick="window.toggleMdC16LegacyPcTurboMode()" data-guide-title="وضع الخفة الصاروخية للأجهزة القديمة (2010–2012) وإنترنت 2G" data-guide-desc="إيقاف الضبابية الزجاجية الثقيلة لتسريع الرسم اللحظي (0ms Paint) على أجهزة الكمبيوتر واللابتوب القديمة.">⚡ خفة 2010</button>
                    <button type="button" id="btn-md-c1-officer" class="btn-add" style="height:32px !important; min-height:32px !important; padding:0 10px !important; border-radius:8px; font-size:0.8rem;" onclick="window.openMdConstitution1OfficerModal()" data-guide-title="الختم الثلاثي الشامل للدساتير الخمسة عشر (1 إلى 15 من 15)" data-guide-desc="فحص وإلزام ذاتي شامل للدساتير الخمسة عشر معاً.">🛡️ الختم الـ 15</button>
                    <button type="button" id="btn-md-c16-zero-error" class="btn-add" style="height:32px !important; min-height:32px !important; padding:0 12px !important; border-radius:8px; font-size:0.8rem; font-weight:900; background:linear-gradient(135deg,#10b981,#047857); border:1.5px solid #34d399; color:#ffffff; box-shadow:0 4px 12px rgba(16,185,129,0.28);" onclick="window.openMdC16ZeroErrorMasterAuditModal()" data-guide-title="بوابة المراجعة الختامية الشاملة — الدستور 16 (Zero-Error 50x)" data-guide-desc="فحص جراحي مجهري 50 مرة للدساتير الـ 16 لنسخة اللابتوب والكمبيوتر مع ضمان زيرو خطأ (100% PASS).">💎 بوابة الدستور 16 (Zero-Error)</button>
                </div>
            </div>

            <!-- شريط أدوات علوي (تم إخفاؤه لأن كل قسم يحتوي على بحثه وأدواته الخاصة في مكانه الصحيح) -->
            <div id="md-clean-organized-bar" style="display:none !important;"></div>

            <!-- شريط الربط الشامل والذكاء الاصطناعي التنبؤي للبيانات الأساسية 360° (تم إخفاؤه لتطهير الشاشة من الأزرار غير المفهومة) -->
            <div id="md-smart-ai-command-bar" style="display:none !important;">
                <!-- الصف الأول: الإدخال الذكي باللغة الطبيعية صوتاً وكتابةً (0ms Hybrid Edge + Cloud AI) + البوابة المزدوجة والاختصارات (1-4) -->
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; width:100%;">
                    <div style="display:flex; align-items:center; gap:6px; flex:1; min-width:280px; background:rgba(2,6,23,0.85); border:1.5px solid rgba(56,189,248,0.45); border-radius:10px; padding:4px 8px;">
                        <span style="font-size:1rem;" title="الإدخال الذكي باللغة الطبيعية (0ms AI Copilot)">🧠</span>
                        <input type="text" id="md-fc1-nlp-input" onkeydown="if(event.key==='Enter'){event.preventDefault();window.executeMdFc1NaturalLanguageCommand();}" placeholder="اكتب أو انطق أمراً ذكياً: مثلاً «عميل عماد هاتف 0912345678 رصيد 500000» أو «صنف أسمنت سعر 120000 كمية 50»..." style="flex:1; background:transparent !important; border:none !important; color:#ffffff !important; font-size:0.82rem; font-weight:800; outline:none; padding:4px 6px;" data-guide-title="الإدخال الذكي باللغة الطبيعية صوتاً وكتابةً (0ms AI)" data-guide-desc="اكتب أو انطق جملة طبيعية وسيقوم الذكاء الاصطناعي الهجين بفتح القسم المناسب وملء جميع الخانات تلقائياً في جزء من الثانية.">
                        <button type="button" id="btn-md-fc1-voice" onclick="window.startMdFc1VoiceNlpInput()" style="height:28px; padding:0 9px; border-radius:7px; background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.45); font-size:0.76rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" title="إدخال صوتي ذكي باللغة الطبيعية">🎤 صوتي</button>
                        <button type="button" onclick="window.executeMdFc1NaturalLanguageCommand()" style="height:28px; padding:0 11px; border-radius:7px; background:linear-gradient(135deg,#0284c7,#2563eb); color:#fff; border:1px solid #38bdf8; font-size:0.76rem; font-weight:900; cursor:pointer;">⚡ تنفيذ فوري</button>
                    </div>
                    <!-- البوابة المزدوجة (عصرية ↔ كلاسيكية) + مصفوفة الاختصارات (1، 2، 3، 4) + النصفين + الإيماءات -->
                    <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                        <button type="button" id="btn-md-fc1-ui-mode" onclick="window.toggleMdFc1DualUiMode()" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(168,85,247,0.18); color:#e9d5ff; border:1px solid rgba(168,85,247,0.5); font-size:0.78rem; font-weight:900; cursor:pointer;" data-guide-title="البوابة المزدوجة (العصرية العالمية ↔ الكلاسيكية المعتادة)" data-guide-desc="التبديل الفوري في 0ms بين الواجهة العصرية الفاخرة والواجهة الكلاسيكية الدفترية المعتادة.">✨ عصرية ↔ 🏛️ كلاسيكية</button>
                        <button type="button" id="btn-md-fc1-shortcuts" onclick="window.openMdFc1ShortcutsMatrixModal()" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(245,158,11,0.18); color:#fde047; border:1px solid rgba(245,158,11,0.5); font-size:0.78rem; font-weight:900; cursor:pointer;" data-guide-title="مصفوفة أزرار الاختصارات (1، 2، 3، 4) القابلة للتشكيل مع القفل الأمني" data-guide-desc="التبديل بين البرنامج الكامل وأزرار الاختصارات (1، 2، 3، 4) وتحديد ما يظهر بداخلها بعلامة صح (✓) مع قفل حبس الاختصار أمنياً للموظف.">🔢 اختصارات (1-4)</button>
                        <button type="button" id="btn-md-fc1-split-screen" onclick="window.toggleMdFc1SplitScreenStudio()" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(6,182,212,0.18); color:#67e8f9; border:1px solid rgba(6,182,212,0.5); font-size:0.78rem; font-weight:900; cursor:pointer;" data-guide-title="نظام النصفين (يمين وشمال) وهندسة الموبايل بالعرض (100dvh)" data-guide-desc="فتح شاشتين مختلفتين في النصف الأيمن والأيسر تتحدثان حياً فور الحفظ، أو تحويل النصف الثاني لمنطقة جوال حرة (قارئ إشعارات AI، واتساب، بنكك، يوتيوب).">🌗 نظام النصفين</button>
                        <button type="button" id="btn-md-fc1-gestures" onclick="window.openMdFc1GestureControlModal()" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(16,185,129,0.18); color:#6ee7b7; border:1px solid rgba(16,185,129,0.5); font-size:0.78rem; font-weight:900; cursor:pointer;" data-guide-title="مفتاح إيماءات الأصابع وأزرار الصوت (تفعيل/إيقاف للشاشات المرتجة)" data-guide-desc="تفعيل أو إيقاف إيماءات الأصابع وأزرار الصوت لحماية الشاشات ذات اللمس المرتج مع شرح كامل لكل حركة.">🤚 الإيماءات</button>
                        <button type="button" id="btn-md-fc1-sunlight" onclick="window.toggleMdFc1SunlightMode()" style="height:32px; padding:0 9px; border-radius:8px; background:rgba(250,204,21,0.16); color:#fde047; border:1px solid rgba(250,204,21,0.45); font-size:0.78rem; font-weight:900; cursor:pointer;" title="وضع وضوح شمس السوق + تبديل السمة">☀️ شمس السوق</button>
                        <button type="button" id="btn-md-fc1-audit-gate" onclick="window.openMdFc1SupremeConstitutionModal()" style="height:32px; padding:0 10px; border-radius:8px; background:linear-gradient(135deg,#10b981,#047857); color:#ffffff; border:1.5px solid #34d399; font-size:0.76rem; font-weight:900; cursor:pointer; box-shadow:0 3px 10px rgba(16,185,129,0.3);" data-guide-title="بوابة الدستور الختامي الأول (1 من 2 — 10 نقاط جوهرية 100%)" data-guide-desc="فحص وتشغيل النقاط الجوهرية العشر للدستور الختامي الأول بمعيار 50 دورة مراجعة وزيرو خطأ." data-guide-rule="سرعة 0ms وبساطة الإدخال الذكي تمنح المحاسب دقة 100% بأقل مجهود بصري وذهني.">👑 الدستور (1/2)</button>
                        <button type="button" id="btn-md-fc2-audit-gate" onclick="window.openMdFc2SupremeConstitution2Modal()" style="height:32px; padding:0 10px; border-radius:8px; background:linear-gradient(135deg,#f59e0b,#b45309); color:#ffffff; border:1.5px solid #fde047; font-size:0.76rem; font-weight:900; cursor:pointer; box-shadow:0 3px 12px rgba(245,158,11,0.38);" data-guide-title="بوابة الدستور الختامي الثاني (2 من 2 — الذكاء المحاسبي والتحليلي والأمني 10/10)" data-guide-desc="خلاصة الربط الذري 100%، مفسر النتائج والتقارير بالذكاء الاصطناعي، تزامن CRDT، دورة الشيكات وبنكك، رادار المالك الجنائي (أصفر/برتقالي/أحمر)، وأقفال مكافحة الاختلاس." data-guide-rule="كل حركة مالية أو مخزنية تسمع ذرياً في 0ms وتخضع لحراسة الذكاء الاصطناعي الجنائي قبل الحفظ وبعده.">🛡️ الدستور (2/2)</button>
                        <button type="button" id="btn-md-sovereign-10x-gate" onclick="window.openMdSovereign10xMasterAuditModal()" style="height:32px; padding:0 11px; border-radius:8px; background:linear-gradient(135deg,#0284c7,#1d4ed8); color:#ffffff; border:1.5px solid #7dd3fc; font-size:0.76rem; font-weight:900; cursor:pointer; box-shadow:0 3px 12px rgba(2,132,199,0.42);" data-guide-title="بوابة الحسم والفحص الختامي الشامل (النسخة السيادية الموحدة لمقاس جميع الشاشات + تكبير 10x بالرولة + زيرو خطأ)" data-guide-desc="توحيد مقاس جميع الشاشات من اليمين للشمال على قد اللابتوب تماماً، توحيد الخطوط (+2)، التكبير والتصغير بالرولة والكنترول حتى 10 أضعاف (10x)، وتقرير الإصلاحات والـ 10 إضافات الذكية." data-guide-rule="توحيد المسطرة الهندسية والخطوط والتكبير حتى 10 أضعاف عبر كل الشاشات يمنح راحة بصرية مطلقة من أجهزة 2010 إلى ما لا نهاية.">🏆 النسخة السيادية الموحدة (10x + زيرو خطأ)</button>
                    </div>
                </div>
                <!-- الصف الثاني: أدوات الذكاء الاصطناعي والربط المحاسبي الشامل -->
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; width:100%;">
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <button type="button" onclick="window.openMasterDataAIPredictiveModal()" style="height:34px; padding:0 13px; border-radius:9px; background:linear-gradient(135deg,#8b5cf6,#6d28d9); color:#fff !important; border:1px solid rgba(192,132,252,0.55); font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 4px 12px rgba(139,92,246,0.3);">
                        <span>🤖</span> <span>المستشار الذكي والتنبؤي AI</span>
                    </button>
                    <button type="button" onclick="window.openMdFc1AiOcrAndPriceAdvisorModal()" style="height:34px; background:rgba(14,165,233,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.45); padding:0 11px; border-radius:9px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" title="قارئ الصور والإشعارات الذكي AI OCR + مقترح أفضل سعر وبديل للصنف">
                        <span>📷</span> <span>قارئ AI ومقترح الأسعار</span>
                    </button>
                    <button type="button" onclick="openOpeningEquilibriumModal()" style="height:34px; background:rgba(16,185,129,0.16); color:#34d399; border:1px solid rgba(16,185,129,0.45); padding:0 11px; border-radius:9px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" title="فحص وموازنة الأرصدة الافتتاحية وتوليد القيد الافتتاحي">
                        <span>⚖️</span> <span>توازن الافتتاحي</span>
                    </button>
                    <button type="button" onclick="openBulkPriceUpdaterModal()" style="height:34px; background:rgba(245,158,11,0.16); color:#fbbf24; border:1px solid rgba(245,158,11,0.45); padding:0 11px; border-radius:9px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" title="تحديث أسعار الأصناف جماعياً بنسبة أو مبلغ">
                        <span>📈</span> <span>تحديث الأسعار</span>
                    </button>
                    <button type="button" onclick="window.openMdHealthAndDuplicateScannerModal()" style="height:34px; background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); padding:0 11px; border-radius:9px; font-size:0.8rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" title="فحص التكرارات والنواقص في الأقسام الثمانية">
                        <span>🔍</span> <span>فحص التكرارات</span>
                    </button>
                    <button type="button" onclick="openSmartClipboardPasteModal()" style="height:34px; background:rgba(168,85,247,0.15); color:#d8b4fe; border:1px solid rgba(168,85,247,0.4); padding:0 10px; border-radius:9px; font-size:0.79rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" title="التوزيع الذكي للنصوص المنسوخة ولصق Excel">
                        <span>📋</span> <span>توزيع ذكي للنص</span>
                    </button>
                    <button type="button" onclick="exportActiveTabToExcel()" style="height:34px; background:rgba(16,185,129,0.14); color:#34d399; border:1px solid rgba(16,185,129,0.38); padding:0 10px; border-radius:9px; font-size:0.79rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                        <span>📤</span> <span>تصدير Excel</span>
                    </button>
                    <button type="button" onclick="triggerActiveTabExcelImport()" style="height:34px; background:rgba(56,189,248,0.14); color:#38bdf8; border:1px solid rgba(56,189,248,0.38); padding:0 10px; border-radius:9px; font-size:0.79rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                        <span>📥</span> <span>استيراد Excel</span>
                    </button>
                    <button type="button" onclick="openActiveTabPrintAndShareModal()" style="height:34px; background:rgba(148,163,184,0.14); color:#e2e8f0; border:1px solid rgba(148,163,184,0.35); padding:0 10px; border-radius:9px; font-size:0.79rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                        <span>🖨️</span> <span>طباعة</span>
                    </button>
                    <button type="button" onclick="window.openMdQuickJumpPaletteModal()" style="height:34px; background:rgba(56,189,248,0.12); color:#7dd3fc; border:1px solid rgba(56,189,248,0.35); padding:0 10px; border-radius:9px; font-size:0.79rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" title="بحث فوري شامل في كافة البيانات الأساسية (Ctrl+K / F2)">
                        <span>⚡</span> <span>بحث شامل</span>
                    </button>
                </div>
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <span id="md-fc1-copilot-hint" onclick="window.openMasterDataAIPredictiveModal()" style="height:30px; padding:0 10px; border-radius:999px; background:rgba(16,185,129,0.14); color:#34d399; border:1px solid rgba(16,185,129,0.4); font-size:0.75rem; font-weight:900; display:inline-flex; align-items:center; gap:5px; cursor:pointer;" title="مساعد الشاشة الذكي (0ms Screen Copilot)">🤖 المرشد التنبؤي: جاهز (0ms)</span>
                    <a href="balances_modern_hub.php" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(16,185,129,0.14); color:#34d399; border:1px solid rgba(16,185,129,0.35); font-size:0.76rem; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">⚖️ الأرصدة والمقاصة</a>
                    <a href="account-statement.php" onclick="(function(a,e){var cid=(document.getElementById('cust-id')&&document.getElementById('cust-id').value)||'',cn=(document.getElementById('cust-name')&&document.getElementById('cust-name').value)||'',sid=(document.getElementById('sup-id')&&document.getElementById('sup-id').value)||'',sn=(document.getElementById('sup-name')&&document.getElementById('sup-name').value)||'';if(cid||cn){e.preventDefault();window.location.href='account-statement.php?accountId='+encodeURIComponent(cid?'cust-'+cid:'')+'&name='+encodeURIComponent(cn);}else if(sid||sn){e.preventDefault();window.location.href='account-statement.php?accountId='+encodeURIComponent(sid?'supp-'+sid:'')+'&name='+encodeURIComponent(sn);}})(this,event)" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(56,189,248,0.14); color:#38bdf8; border:1px solid rgba(56,189,248,0.35); font-size:0.76rem; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">📑 كشف حساب</a>
                    <a href="customer-portfolio.php" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(168,85,247,0.14); color:#d8b4fe; border:1px solid rgba(168,85,247,0.35); font-size:0.76rem; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">💼 محفظة العملاء</a>
                    <a href="supplier_reliability.php" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(245,158,11,0.14); color:#fbbf24; border:1px solid rgba(245,158,11,0.35); font-size:0.76rem; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">🏭 موثوقية الموردين</a>
                    <a href="inventory-report.php" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(59,130,246,0.14); color:#93c5fd; border:1px solid rgba(59,130,246,0.35); font-size:0.76rem; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">📦 مركز المخازن</a>
                    <a href="general-ledger.php" onclick="(function(a,e){var cid=(document.getElementById('cust-id')&&document.getElementById('cust-id').value)||'',cn=(document.getElementById('cust-name')&&document.getElementById('cust-name').value)||'',sid=(document.getElementById('sup-id')&&document.getElementById('sup-id').value)||'',sn=(document.getElementById('sup-name')&&document.getElementById('sup-name').value)||'';if(cid||cn){e.preventDefault();var cParam=cid?(String(cid).toLowerCase().startsWith('cust-')?cid:'cust-'+cid):'';window.location.href='general-ledger.php?accountId='+encodeURIComponent(cParam)+'&name='+encodeURIComponent(cn);}else if(sid||sn){e.preventDefault();var sParam=sid?(String(sid).toLowerCase().startsWith('supp-')?sid:'supp-'+String(sid).replace(/^sup-/i,'')):'';window.location.href='general-ledger.php?accountId='+encodeURIComponent(sParam)+'&name='+encodeURIComponent(sn);}})(this,event)" style="height:32px; padding:0 10px; border-radius:8px; background:rgba(236,72,153,0.14); color:#f9a8d4; border:1px solid rgba(236,72,153,0.35); font-size:0.76rem; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">📒 دفتر الأستاذ</a>
                </div>
                </div>
                <!-- الصف الثالث: منظومة الدستور الختامي الثاني (2 من 2) — التسميع الذري، مفسر النتائج AI، الشيكات وبنكك، الرادار الجنائي، وأقفال الأمان -->
                <div id="md-fc2-executive-toolbar" style="display:flex; align-items:center; justify-content:space-between; gap:6px; flex-wrap:wrap; width:100%; padding-top:6px; border-top:1px dashed rgba(148,163,184,0.25);">
                    <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                        <button type="button" onclick="window.openMdFc2AtomicSyncAndDrillDownModal()" style="height:31px; padding:0 10px; border-radius:8px; background:rgba(16,185,129,0.18); color:#34d399; border:1px solid rgba(16,185,129,0.5); font-size:0.76rem; font-weight:900; cursor:pointer;" data-guide-title="التسميع الذري اللحظي (0ms) والوصول لأصل المستند (Drill-Down)" data-guide-desc="ربط ذري 100% بين البيانات الأساسية وكشوفات الحساب والمخازن والخزينة والبنوك وميزان المراجعة مع فتح أصل أي حركة بلمسة واحدة." data-guide-rule="أي حركة تُحفظ هنا تُسمّع في 7 تقارير مالية ومخزنية في نفس الجزء من الثانية دون إعادة تحميل.">🔗 التسميع الذري و Drill-Down</button>
                        <button type="button" onclick="window.openMdFc2AiExecutiveInsightsModal()" style="height:31px; padding:0 10px; border-radius:8px; background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.5); font-size:0.76rem; font-weight:900; cursor:pointer;" data-guide-title="مفسر النتائج والتقارير بالذكاء الاصطناعي (AI Executive Insights)" data-guide-desc="تحويل الأرقام الصامتة إلى قرارات ذكية، تفسير أسباب الارتفاع والانخفاض، التنبؤ بنفاد المخزون وتحصيل الديون، وسؤال التقرير باللغة الطبيعية." data-guide-rule="الأرقام الصامتة لا تصنع قراراً؛ التفسير التحليلي الفوري يكشف فرص الربح ومكامن الخطر مبكراً.">📊 مفسر النتائج وسؤال التقرير AI</button>
                        <button type="button" onclick="window.openMdFc2DocumentaryAndLandedCostModal()" style="height:31px; padding:0 10px; border-radius:8px; background:rgba(168,85,247,0.18); color:#d8b4fe; border:1px solid rgba(168,85,247,0.5); font-size:0.76rem; font-weight:900; cursor:pointer;" data-guide-title="توزيع المصروفات المضافة (Landed Cost) والدورة المستندية بلمسة زر" data-guide-desc="توزيع الشحن والعتالة آلياً على تكلفة الأصناف مع تنبيه تآكل هامش الربح، شاشة فحص واستلام البضاعة (سليم/تالف/مرفوض)، وتحويل (عرض سعر ⬅ فاتورة ⬅ إذن مخزن ⬅ سند)." data-guide-rule="حساب التكلفة الحقيقية بعد المصروفات المضافة يحمي هامش ربح المؤسسة من التآكل الخفي.">📦 التكلفة المضافة والدورة المستندية</button>
                        <button type="button" onclick="window.openMdFc2ChequesAndBankakRadarModal()" style="height:31px; padding:0 10px; border-radius:8px; background:rgba(245,158,11,0.18); color:#fde047; border:1px solid rgba(245,158,11,0.5); font-size:0.76rem; font-weight:900; cursor:pointer;" data-guide-title="دورة الشيكات الخماسية وكاشف تزوير إشعارات (بنكك / فوري) بضغط 40KB" data-guide-desc="إدارة مراحل الشيك الخمس (حافظة ⬅ تحت التحصيل ⬅ تحصيل ⬅ مرتد ⬅ تظهير) وفحص إشعارات بنكك وفوري بالذكاء الاصطناعي لمنع التكرار والتزوير." data-guide-rule="لا يُقبل أي إشعار بنكي أو شيك دون مطابقة الرقم المرجعي آلياً عبر قاعدة البيانات بالكامل.">🏦 الشيكات وكاشف تزوير بنكك</button>
                        <button type="button" onclick="window.openMdFc2ProactiveAlertsCenterModal()" style="height:31px; padding:0 10px; border-radius:8px; background:rgba(236,72,153,0.18); color:#f9a8d4; border:1px solid rgba(236,72,153,0.5); font-size:0.76rem; font-weight:900; cursor:pointer;" data-guide-title="منظومة التنبيهات الاستباقية وحارس الأصفار الزائدة والائتمان" data-guide-desc="رادار استحقاق الشيكات، تقييم مخاطر ائتمان العميل، تنبيه الأصناف الراكدة، وحارس الأصفار الزائدة والأسعار الشاذة مع اهتزاز الخانة الخاطئة." data-guide-rule="التنبيه الاستباقي قبل وقوع الخطأ يوفر ساعات من المراجعة والتسويات المحاسبية اللاحقة.">🔔 التنبيهات الاستباقية وحارس الأصفار</button>
                        <button type="button" onclick="window.openMdFc2ForensicOwnerRadarModal()" style="height:31px; padding:0 10px; border-radius:8px; background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.55); font-size:0.76rem; font-weight:900; cursor:pointer;" data-guide-title="رادار الذكاء الاصطناعي الجنائي للمالك (🟡 أصفر / 🟠 برتقالي / 🔴 أحمر) والصندوق الأسود" data-guide-desc="توثيق كل ما كُتب ومُسح قبل الحفظ في الصندوق الأسود المشفر، وتحليل سلوك الموظفين بثلاث درجات (ملاحظة رقابية، شبهة تلاعب، محاولة اختلاس)." data-guide-rule="الصندوق الأسود لا ينسى حرفاً كُتب أو مُسح قبل الحفظ، والرادار الجنائي يحرس أموال المالك بصمت.">🕵️‍♂️ الرادار الجنائي للمالك (🟡🟠🔴)</button>
                        <button type="button" onclick="window.openMdFc2AntiEmbezzlementLocksModal()" style="height:31px; padding:0 10px; border-radius:8px; background:rgba(14,165,233,0.18); color:#7dd3fc; border:1px solid rgba(14,165,233,0.5); font-size:0.76rem; font-weight:900; cursor:pointer;" data-guide-title="أقفال مكافحة الاختلاس، قفل الطبعة (1)، الجرد الأعمى، وإقفال الفترات" data-guide-desc="قفل الطباعة بعد النسخة الأصلية (1) وعلامة بدل فاقد، الجرد الأعمى للخزينة، إشعار واتساب الفوري للعميل، وقفل الفترات المالية المغلقة." data-guide-rule="قفل الطبعة الأولى والجرد الأعمى ورسالة الواتساب الفورية للعميل تغلق كافة ثغرات الاختلاس البشري.">🔒 أقفال الاختلاس والجرد الأعمى</button>
                        <button type="button" onclick="window.openMdFc2CyberBiometricAndPrintStudioModal()" style="height:31px; padding:0 10px; border-radius:8px; background:rgba(20,184,166,0.18); color:#5eead4; border:1px solid rgba(20,184,166,0.5); font-size:0.76rem; font-weight:900; cursor:pointer;" data-guide-title="البصمة البيومترية، القفل الزمني، والطباعة الحرارية 80mm وبطاقة واتساب" data-guide-desc="تفعيل البصمة البيومترية، القفل الزمني ضد إرجاع الساعة، تشفير AES-256، والطباعة الموزونة الحرارية 80mm وA4 وبطاقة إيصال واتساب المصورة." data-guide-rule="الأمن السيبراني الحيوي والقفل الزمني المشفر يمنعان التلاعب بالتواريخ أو تسريب البيانات الحساسة.">🧬 البصمة وطباعة 80mm وواتساب</button>
                    </div>
                    <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                        <div style="display:inline-flex; align-items:center; gap:3px; padding:2px 6px; border-radius:8px; background:rgba(2,6,23,0.85); border:1px solid rgba(56,189,248,0.45);" data-guide-title="محرك التكبير والتصغير الخارق بـ (Ctrl + رولة الماوس) حتى 10 أضعاف (10x) وتوحيد الشاشات" data-guide-desc="تكبير وتصغير متناسب في الطول والعرض من 0.25x وحتى 10 أضعاف (10x = 1000%) مع تزامن فوري عبر جميع شاشات البرنامج (المبيعات، المشتريات، المخازن، التقارير)." data-guide-rule="توحيد المقاس والخطوط عبر كل الشاشات يحمي العين من إعادة التكيف البصري عند التنقل.">
                            <button type="button" onclick="window.setMdProportionalZoom('step', -0.1)" style="height:24px; padding:0 6px; border-radius:5px; background:rgba(255,255,255,0.08); color:#fff; border:none; font-size:0.72rem; font-weight:900; cursor:pointer;" title="تصغير متناسب">➖</button>
                            <span id="md-sov-10x-live-badge" onclick="window.setMdProportionalZoom('reset')" style="font-size:0.73rem; font-weight:900; color:#38bdf8; padding:0 5px; cursor:pointer;" title="اضغط لإعادة الضبط القياسي 100% (1x) على قد اللابتوب من اليمين للشمال">🔍 المقاس الموحد: 100% (1x)</span>
                            <button type="button" onclick="window.setMdProportionalZoom('step', 0.1)" style="height:24px; padding:0 6px; border-radius:5px; background:rgba(255,255,255,0.08); color:#fff; border:none; font-size:0.72rem; font-weight:900; cursor:pointer;" title="تكبير متناسب">➕</button>
                            <button type="button" onclick="window.setMdProportionalZoom(2.0)" style="height:24px; padding:0 5px; border-radius:5px; background:rgba(56,189,248,0.18); color:#7dd3fc; border:none; font-size:0.7rem; font-weight:900; cursor:pointer;">2x</button>
                            <button type="button" onclick="window.setMdProportionalZoom(3.0)" style="height:24px; padding:0 5px; border-radius:5px; background:rgba(56,189,248,0.18); color:#7dd3fc; border:none; font-size:0.7rem; font-weight:900; cursor:pointer;">3x</button>
                            <button type="button" onclick="window.setMdProportionalZoom(5.0)" style="height:24px; padding:0 5px; border-radius:5px; background:rgba(245,158,11,0.2); color:#fde047; border:none; font-size:0.7rem; font-weight:900; cursor:pointer;">5x</button>
                            <button type="button" onclick="window.setMdProportionalZoom(10.0)" style="height:24px; padding:0 6px; border-radius:5px; background:rgba(239,68,68,0.25); color:#fca5a5; border:none; font-size:0.7rem; font-weight:900; cursor:pointer;">10x</button>
                            <span id="md-laser-symmetry-badge" onclick="window.calibrateMdEdgeToEdgeStandard && window.calibrateMdEdgeToEdgeStandard()" style="height:24px; padding:0 7px; border-radius:5px; background:rgba(16,185,129,0.2); color:#34d399; border:1px solid rgba(52,211,153,0.45); font-size:0.7rem; font-weight:900; display:inline-flex; align-items:center; cursor:pointer;" title="قفل التنصيف الليزري المتماثل: الهامش الأيمن === الهامش الأيسر بالبيكسل">🎯 تنصيف ليزري: يمين = يسار</span>
                        </div>
                        <span id="md-fc2-crdt-sync-badge" onclick="window.triggerMdFc2FlashSyncCRDT()" style="height:28px; padding:0 9px; border-radius:999px; background:rgba(16,185,129,0.16); color:#34d399; border:1px solid rgba(16,185,129,0.45); font-size:0.73rem; font-weight:900; display:inline-flex; align-items:center; gap:4px; cursor:pointer;" data-guide-title="التزامن الصاروخي (CRDT Flash Sync) ودرع حماية المدخلات" data-guide-desc="اقتناص أضعف شرارة إنترنت لدمج الحركات تراكمياً بدون تضارب مع حماية المدخلات الجارية عند انطفاء البطارية أو ورود مكالمة.">⚡ تزامن CRDT: متطابق 100% (0ms)</span>
                    </div>
                </div>
                <!-- شريط مفسر النتائج التنفيذي الحي بالذكاء الاصطناعي (0ms AI Live Result Summary Bar) -->
                <div id="md-fc2-live-result-interpreter-bar" style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; padding:7px 12px; border-radius:10px; background:rgba(2,6,23,0.75); border:1px solid rgba(56,189,248,0.35); font-size:0.79rem;">
                    <div id="md-fc2-live-result-summary-text" style="font-weight:800; color:#e2e8f0; flex:1; min-width:240px;">🧠 <strong style="color:#38bdf8;">قراءة النتيجة بالذكاء الاصطناعي (0ms):</strong> جاري تحليل أرصدة ومؤشرات القسم النشط...</div>
                    <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                        <input type="text" id="md-fc2-quick-report-question" onkeydown="if(event.key==='Enter'){event.preventDefault();window.askMdFc2QuickReportQuestion();}" placeholder="اسأل التقرير: «أعلى 5 عملاء»، «سبب زيادة المصروفات»، «الأصناف الحرجة»..." style="width:255px; max-width:100%; height:27px !important; min-height:27px !important; padding:0 8px !important; border-radius:7px; border:1px solid rgba(56,189,248,0.45) !important; background:#000000 !important; color:#ffffff !important; font-size:0.75rem; font-weight:800;">
                        <button type="button" onclick="window.askMdFc2QuickReportQuestion()" style="height:27px; padding:0 9px; border-radius:7px; background:#0284c7; color:#fff; border:none; font-size:0.74rem; font-weight:900; cursor:pointer;">🔍 استخرج فوراً</button>
                    </div>
                </div>
            </div>

            <!-- شريط الفواتير والبطاقات المعلقة المصغر (One-Click Park & Resume — البند 4 من الدستور الثامن) -->
            <div id="md-c8-parked-strip" style="display:none; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:10px; padding:7px 12px; border-radius:11px; background:rgba(245,158,11,0.12); border:2px solid #f59e0b;"></div>

            <div id="tab-warehouses" class="md-tab-content active">
                <div class="md-card">
                    <div class="md-card-header">
                        <h3 class="md-card-title">🏢 المخازن</h3>
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <div style="position:relative; min-width:240px;">
                                <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none;">🔍</span>
                                <input type="text" id="warehouse-search-input" oninput="renderWarehouses(this.value)" placeholder="بحث في المخازن..." style="padding-right:36px !important;">
                            </div>
                            <button type="button" id="md-wh-allow-neg-btn" onclick="window.MagaiesInventoryGuard2027 && window.MagaiesInventoryGuard2027.toggleAllowNegativeStock()" style="height:38px;padding:0 13px;border-radius:10px;font-weight:900;font-size:0.84rem;cursor:pointer;border:2px solid #10b981;background:rgba(16,185,129,0.18);color:#6ee7b7;" title="خيار البيع بالسالب عند وجود بضاعة في الطريق (مرتبط بالإعدادات وشاشة المبيعات)">🚚 البيع بالسالب (بضاعة بالطريق): مفعل ✅</button>
                            <button class="btn-add" onclick="openWarehouseForm()">+ إضافة مخزن <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    
                    <div id="warehouse-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0; color:#38bdf8;">🏢 بيانات المخزن</h4>
                            <button class="btn-c3-cancel" style="display: none;" id="btn-cancel-warehouse" onclick="cancelEditWarehouse()">✖ إلغاء</button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="warehouse-id">
                            <div class="input-group"><label>اسم المخزن *</label><input type="text" id="warehouse-name" placeholder="مثال: مخزن السيطرة الرئيسي"></div>
                            <div class="input-group"><label>النوع</label><select id="warehouse-type"><option value="مستودع رئيسي">مستودع رئيسي</option><option value="معرض (نقطة بيع)">معرض (نقطة بيع)</option><option value="مخزن فرعي">مخزن فرعي</option><option value="مخزن بضاعة تالفة">مخزن بضاعة تالفة</option></select></div>
                            <div class="input-group"><label style="justify-content:space-between;"><span>أمين المخزن</span><button type="button" class="btn-text" style="height:20px !important; min-height:20px !important; padding:0 6px !important; font-size:0.72rem !important;" onclick="quickAddKeeperModal()">➕</button></label><input type="text" id="warehouse-keeper" list="wh-keepers-datalist" placeholder="اسم أمين المخزن"><datalist id="wh-keepers-datalist"></datalist></div>
                            <div class="input-group"><label>الهاتف</label><input type="text" id="warehouse-phone" placeholder="0912345678" style="direction:ltr; text-align:center;"></div>
                            <div class="input-group"><label>العنوان</label><input type="text" id="warehouse-location" placeholder="مثال: المنطقة الصناعية"></div>
                            <div class="input-group"><label>الحالة</label><select id="warehouse-status"><option value="active">🟢 نشط</option><option value="inventory_lock">🟠 جرد</option><option value="inactive">🔴 موقوف</option></select></div>
                            <div class="input-group"><label>البيع بالسالب</label><select id="warehouse-allow-negative"><option value="no">⛔ ممنوع (موجب فقط)</option><option value="yes">✅ مسموح (بالسالب / بضاعة بالطريق)</option></select></div>
                            <div class="input-group"><label>السرية</label><select id="warehouse-security"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                        </div>
                        <div class="md-c3-form-footer">
                            <button type="button" class="btn-c3-save" onclick="saveWarehouse()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-c3-cancel" onclick="cancelEditWarehouse()">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>اسم المخزن</th><th>النوع</th><th>أمين المخزن</th><th>الهاتف</th><th>العنوان</th><th>الحالة</th><th>البيع بالسالب</th><th>السرية</th><th>الإجراء</th></tr></thead>
                            <tbody id="warehouses-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-customers" class="md-tab-content">
                <div class="md-card">
                    <!-- أزرار التحكم المباشر لواتساب العملاء -->
                    <div id="admin-wa-policy-panel-cust" style="margin-bottom: 14px; border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; background: rgba(15, 23, 42, 0.65); padding: 10px 14px;">
                        <div style="display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <button type="button" onclick="bulkSetEntityStatementMode('customer', 'detailed')" data-md-tip-title="📊 كشف للكل (السماح للكل من الواتساب)" data-md-tip="يسمح لجميع العملاء بطلب واستلام كشف حساب مفصل بجميع الحركات والفواتير تلقائياً عبر الواتساب." style="height: 32px; background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">📊 كشف للكل</button>
                            <button type="button" onclick="bulkSetEntityStatementMode('customer', 'balance_only')" data-md-tip-title="💰 رصيد للكل (طلب الرصيد عبر الواتساب)" data-md-tip="يسمح لجميع العملاء بطلب معرفة الرصيد النهائي فقط عبر الواتساب دون إرسال تفاصيل الفواتير." style="height: 32px; background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">💰 رصيد للكل</button>
                            <button type="button" onclick="bulkSetEntityStatementMode('customer', 'disabled')" data-md-tip-title="🔒 إيقاف الكل (إيقاف الرد الآلي بالواتساب)" data-md-tip="يوقف جميع العملاء من طلب الرصيد أو كشف الحساب عبر الواتساب." style="height: 32px; background: rgba(239, 68, 68, 0.14); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🔒 إيقاف الكل</button>
                            <button type="button" onclick="bulkGenerateEntityPins('customer')" data-md-tip-title="🔐 توليد باسورد للكل" data-md-tip="يقوم بتوليد رقم سري (باسورد) فريد تلقائياً لجميع العملاء لاستخدامه عند طلب كشف الحساب أو الرصيد عبر الواتساب." style="height: 32px; background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🔐 توليد باسورد</button>
                            <button type="button" onclick="toggleAdminWaPolicyDrawer()" data-md-tip-title="⚙️ سياسة الصلاحيات العامة للواتساب" data-md-tip="فتح وإغلاق لوحة سياسة الصلاحيات الشاملة (صلاحية الكشف، طريقة اعتماد الإشعارات، البنك الافتراضي، التحقق الأمني، ونطاق الكشف)." style="height: 32px; background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.45); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">⚙️ السياسة</button>
                        </div>

                        <div class="admin-wa-policy-drawer" style="display: none; margin-top: 12px; padding-top: 12px; border-top: 1px dashed rgba(168, 85, 247, 0.3);">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; align-items: end;">
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #e9d5ff; font-size: 0.8rem; font-weight: 800;">صلاحية الكشف</label>
                                    <select class="wa-policy-master-mode" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(168,85,247,0.45);">
                                        <option value="per_account">حسب كل حساب</option>
                                        <option value="force_detailed_all">كشف مفصل للكل</option>
                                        <option value="force_balance_only_all">الرصيد فقط للكل</option>
                                        <option value="force_disabled_all">إيقاف الكل</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #10b981; font-size: 0.8rem; font-weight: 800;">اعتماد الإشعارات</label>
                                    <select class="wa-policy-receipt-approval-mode" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(16,185,129,0.5);">
                                        <option value="per_account">حسب كل حساب</option>
                                        <option value="auto_post_all">اعتماد فوري للكل</option>
                                        <option value="review_first_all">مراجعة بالترحيل للكل</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #fbbf24; font-size: 0.8rem; font-weight: 800;">البنك الافتراضي</label>
                                    <select class="wa-policy-default-bank-id" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(245,158,11,0.5);">
                                        <option value="">تلقائي</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #38bdf8; font-size: 0.8rem; font-weight: 800;">التحقق الأمني</label>
                                    <select class="wa-policy-phone-verification" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(56,189,248,0.4);">
                                        <option value="phone_or_pin">الرقم المعتمد أو الباسورد</option>
                                        <option value="any_phone_with_code">الباسورد لأي رقم</option>
                                        <option value="registered_if_set">مطابقة الرقم أو الباسورد</option>
                                        <option value="strict_registered_only">الرقم المسجل فقط</option>
                                        <option value="pin_always">اشتراط الباسورد دائماً</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #34d399; font-size: 0.8rem; font-weight: 800;">نطاق الكشف</label>
                                    <select class="wa-policy-statement-scope" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(16,185,129,0.4);">
                                        <option value="all">كامل الحركات</option>
                                        <option value="last_30_days">آخر 30 يوماً</option>
                                        <option value="last_20">آخر 20 حركة</option>
                                        <option value="last_50">آخر 50 حركة</option>
                                        <option value="current_month">الشهر الحالي</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap; margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06);">
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #e2e8f0;">
                                    <input type="checkbox" class="wa-policy-include-items" onchange="syncAdminWaPolicyFromUI(this)" checked style="width: 16px; height: 16px; accent-color: #10b981;">
                                    <span>إظهار الأصناف</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #fbbf24;">
                                    <input type="checkbox" class="wa-policy-notify-admin" onchange="syncAdminWaPolicyFromUI(this)" style="width: 16px; height: 16px; accent-color: #f59e0b;">
                                    <span>إشعار المدير</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #38bdf8;">
                                    <input type="checkbox" class="wa-policy-antispam" onchange="syncAdminWaPolicyFromUI(this)" checked style="width: 16px; height: 16px; accent-color: #38bdf8;">
                                    <span>منع التكرار</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="customer-form" class="md-card" style="display: block; margin-bottom: 18px; border: 1px solid rgba(255,255,255,0.1); background: rgba(8, 12, 24, 0.45); padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                            <h4 style="margin: 0; font-size: 1.08rem; color: #38bdf8; display: flex; align-items: center; gap: 8px; font-weight: 900;">
                                <span>👥</span> <span>بيانات العميل</span>
                            </h4>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <input type="hidden" id="cust-id" value="">
                                <input type="hidden" id="cust-allow-statement" value="1">
                                <input type="hidden" id="cust-statement-mode" value="detailed">
                                <input type="hidden" id="cust-receipt-approval-mode" value="auto_post">
                                <input type="hidden" id="cust-receipt-code-num" value="">
                                <input type="hidden" id="cust-receipt-code-word" value="">
                                <input type="hidden" id="cust-phone" value="">
                                <input type="hidden" id="cust-account-no" value="">
                                <input type="hidden" id="cust-opening-debit" value="0">
                                <input type="hidden" id="cust-opening-credit" value="0">
                                <div class="md-entity-seg-group" style="display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.85); border: 2px solid rgba(148, 163, 184, 0.5); border-radius: 10px; padding: 3px; gap: 4px; height: 36px; box-sizing: border-box;">
                                    <button type="button" id="cust-mode-btn-disabled" class="md-entity-seg-btn" onclick="setFormStatementMode('cust', 'disabled', true)" data-md-tip-title="🔒 موقوف نهائياً" data-md-tip="موقوف نهائياً: لا يستطيع هذا العميل طلب الرصيد ولا كشف الحساب عبر الواتساب." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">🔒 موقوف</button>
                                    <button type="button" id="cust-mode-btn-balance_only" class="md-entity-seg-btn" onclick="setFormStatementMode('cust', 'balance_only', true)" data-md-tip-title="💰 الرصيد فقط" data-md-tip="يستطيع هذا العميل أن يطلب معرفة الرصيد فقط عبر الواتساب (بدون كشف حساب مفصل)." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">💰 الرصيد</button>
                                    <button type="button" id="cust-mode-btn-detailed" class="md-entity-seg-btn" onclick="setFormStatementMode('cust', 'detailed', true)" data-md-tip-title="📊 كشف الحساب والرصيد" data-md-tip="يستطيع هذا العميل أن يطلب كشف الحساب المفصل وكذلك الرصيد عبر الواتساب." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">📊 كشف</button>
                                </div>
                                <div class="md-entity-seg-group" style="display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.85); border: 2px solid rgba(16, 185, 129, 0.55); border-radius: 10px; padding: 3px; gap: 4px; height: 36px; box-sizing: border-box;">
                                    <button type="button" id="cust-rcpt-btn-auto_post" class="md-entity-seg-btn" onclick="setFormReceiptApprovalMode('cust', 'auto_post', true)" data-md-tip-title="⚡ نزول تلقائي للإشعارات" data-md-tip="عند إرسال إشعار بنكي من هذا العميل عبر الواتساب ينزل المبلغ في حسابه ورصيده تلقائياً وفوراً." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">⚡ اعتماد فوري</button>
                                    <button type="button" id="cust-rcpt-btn-review_first" class="md-entity-seg-btn" onclick="setFormReceiptApprovalMode('cust', 'review_first', true)" data-md-tip-title="👁️ انتظار المراجعة للإشعارات" data-md-tip="عند إرسال إشعار بنكي من هذا العميل عبر الواتساب لا ينزل في رصيده تلقائياً، بل ينتظر المراجعة والاعتماد اليدوي أولاً." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">👁️ مراجعة</button>
                                </div>
                                <div style="display:inline-flex; align-items:center; gap:4px;">
                                    <select id="cust-default-bank-id" class="entity-default-bank-select" data-md-tip-title="🏦 البنك الافتراضي للعميل" data-md-tip="البنك الافتراضي المخصص لهذا العميل؛ حينما يرسل إشعار تحويل عبر الواتساب يُعتمد ويُودع المبلغ في هذا البنك تلقائياً." style="height: 36px; background: rgba(15, 23, 42, 0.9); color: #fcd34d; border: 2px solid rgba(245, 158, 11, 0.6); border-radius: 10px; padding: 0 10px; font-size: 0.78rem; font-weight: 800; max-width: 175px;">
                                        <option value="">🏦 البنك الافتراضي</option>
                                    </select>
                                    <button type="button" class="btn-text md-hdr-pill-btn" onclick="quickAddBankModal('cust-default-bank-id')" data-md-tip-title="➕ إضافة بنك جديد" data-md-tip="فتح نافذة سريعة لتعريف بنك جديد واختياره كبنك افتراضي للعميل." style="height:36px !important; min-height:36px !important; padding:0 10px !important; border: 2px solid rgba(245, 158, 11, 0.55) !important;">➕</button>
                                </div>
                                <button type="button" class="md-hdr-pill-btn" onclick="openEntityQrCardModal('cust')" data-md-tip-title="🪪 بطاقة QR الذكية للعميل" data-md-tip="تفتح بطاقة تعريف العميل المحتوية على رمز QR والرقم السري وكود الحساب لطباعتها أو مشاركتها مع العميل." style="height: 36px; background: rgba(16, 185, 129, 0.18); color: #34d399; border: 2px solid rgba(16, 185, 129, 0.65); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">🪪 بطاقة QR</button>
                                <button type="button" class="md-hdr-pill-btn" onclick="copyCurrentFormWhatsAppCard('cust')" data-md-tip-title="📋 نسخ البطاقة" data-md-tip="ينسخ بطاقة بيانات العميل شاملة الرقم السري وأوامر طلب الكشف والرصيد جاهزة للصق والإرسال للعميل في الواتساب." style="height: 36px; background: rgba(168, 85, 247, 0.18); color: #e9d5ff; border: 2px solid rgba(168, 85, 247, 0.65); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">📋 نسخ البطاقة</button>
                                <button type="button" onclick="clearCustomerForm()" class="btn-action md-hdr-pill-btn" data-md-tip-title="🔄 عميل جديد (F2)" data-md-tip="تفريغ نموذج البيانات لبدء إضافة عميل جديد." style="height: 36px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 2px solid rgba(56, 189, 248, 0.55); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">🔄 جديد <span class="kbd-hint">F2</span></button>
                            </div>
                        </div>

                        <div class="pro-entity-grid">
                            <div class="pro-field-card accent-sky" style="grid-column: span 2;">
                                <label class="pro-field-label">
                                    <span>👤 اسم العميل *</span>
                                </label>
                                <input type="text" id="cust-name" placeholder="اسم العميل أو الشركة">
                            </div>

                            <div class="pro-field-card accent-purple" data-md-tip-title="🔐 الرقم السري للعميل (باسورد الواتساب)" data-md-tip="الرقم السري الخاص بالعميل (3 أرقام) يُضاف قبله آخر رقمين من رقم الواتساب ليصبح 5 أرقام عند طلب كشف الحساب أو الرصيد من رقم آخر غير مسجل.">
                                <label class="pro-field-label">
                                    <span>🔐 الرقم السري (آخر رقمين من الواتساب + 3 أرقام)</span>
                                    <button type="button" onclick="generateRandomFormPin('cust')" data-md-tip-title="🎲 توليد رقم سري" data-md-tip="توليد رقم سري جديد تلقائياً لهذا العميل." class="pro-field-badge" style="background:rgba(168,85,247,0.32); color:#fff; border:1.8px solid rgba(168,85,247,0.75); cursor:pointer;">🎲 توليد</button>
                                </label>
                                <input type="text" id="cust-statement-pin" class="pro-num-input" maxlength="8" oninput="updateCombinedPinPreview('cust')" placeholder="677">
                            </div>

                            <div class="pro-field-card accent-whatsapp" data-md-tip-title="💬 رقم واتساب العميل الأساسي" data-md-tip="مثال: العميل (محمد) حينما يرسل إشعار تحويل أو يطلب كشف الحساب أو الرصيد من رقم الواتساب المسجل له هنا، يتعرف عليه النظام تلقائياً فيُضاف الإشعار في حسابه ويستلم كشف الحساب أو الرصيد فوراً.">
                                <label class="pro-field-label">
                                    <span>💬 رقم الواتساب</span>
                                </label>
                                <input type="text" id="cust-whatsapp" class="pro-num-input" oninput="document.getElementById('cust-phone').value = this.value; if(typeof updateCombinedPinPreview==='function') updateCombinedPinPreview('cust');" placeholder="00249123000000">
                            </div>

                            <div class="pro-field-card" data-md-tip-title="💰 الرصيد الافتتاحي للعميل" data-md-tip="اضغط السهم العلوي (+) أو (مدين • عليه • لنا) للرصيد الموجب، أو السهم السفلي (−) أو (دائن • له • علينا) للرصيد السالب دون الحاجة للبحث عن علامة السالب في لوحة المفاتيح.">
                                <label class="pro-field-label">
                                    <span>💰 الرصيد الافتتاحي</span>
                                </label>
                                <div class="op-bal-infield-wrap">
                                    <input type="number" id="cust-opening-balance" class="pro-num-input" step="any" placeholder="0.00" oninput="window.syncMdOpBalHint&&window.syncMdOpBalHint(this,'cust',true)">
                                    <div class="op-bal-sign-stepper">
                                        <span role="button" class="op-bal-step-btn step-pos" title="موجب (+): مدين • عليه • لنا" onclick="window.setMdOpBalSign('cust', 1)">▲</span>
                                        <span role="button" class="op-bal-step-btn step-neg" title="سالب (−): دائن • له • علينا" onclick="window.setMdOpBalSign('cust', -1)">▼</span>
                                    </div>
                                </div>
                                <div class="op-bal-infield-hint" id="cust-op-infield-hint">
                                    <span class="op-owe-us" data-sign="pos" onclick="window.setMdOpBalSign('cust', 1)" title="اضغط لجعل المبلغ موجب (+)">(+) مدين • عليه • لنا</span>
                                    <span class="op-sep">|</span>
                                    <span class="op-we-owe" data-sign="neg" onclick="window.setMdOpBalSign('cust', -1)" title="اضغط لجعل المبلغ سالب (−)">(−) دائن • له • علينا</span>
                                </div>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🛑 حد الائتمان</span>
                                </label>
                                <input type="number" id="cust-limit" class="pro-num-input" placeholder="0.00">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>💱 العملة</span>
                                </label>
                                <select id="cust-currency" class="system-currency-select" onchange="updateCustExchangeRate()"></select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🟢 الحالة</span>
                                </label>
                                <select id="cust-status">
                                    <option value="active">🟢 نشط</option>
                                    <option value="vip">🌟 VIP</option>
                                    <option value="blacklisted">🔴 محظور</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>👔 المسؤول</span>
                                </label>
                                <input type="text" id="cust-contact" placeholder="اسم المسؤول">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>📍 العنوان</span>
                                </label>
                                <input type="text" id="cust-address" placeholder="المدينة / الموقع">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🧾 الرقم الضريبي</span>
                                </label>
                                <input type="text" id="cust-tax" placeholder="الرقم الضريبي">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🛡️ السرية</span>
                                </label>
                                <select id="cust-security">
                                    <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🏷️ شريحة التسعير</span>
                                </label>
                                <select id="cust-price-tier">
                                    <option value="cash">💵 قطاعي / نقدي</option>
                                    <option value="semi_wholesale">📦 نصف جملة</option>
                                    <option value="wholesale">🏭 جملة</option>
                                    <option value="credit">📅 آجل</option>
                                    <option value="special">💎 سعر خاص للعميل</option>
                                    <option value="last_sale">🕘 آخر سعر بيع لهذا العميل</option>
                                    <option value="min">🔻 أقل سعر مسموح</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>⏳ فترة السداد (يوم)</span>
                                </label>
                                <input type="number" id="cust-payment-terms" class="pro-num-input" min="0" placeholder="15">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>✉️ البريد الإلكتروني</span>
                                </label>
                                <input type="email" id="cust-email" placeholder="example@email.com" style="direction:ltr; text-align:center;">
                            </div>

                            <div class="pro-field-card accent-sky" id="cust-ex-rate-group" style="display: none;">
                                <label class="pro-field-label" id="cust-ex-rate-label" style="color:#38bdf8 !important;">
                                    <span>💵 سعر الصرف</span>
                                </label>
                                <input type="number" id="cust-ex-rate" class="pro-num-input" step="any" value="1" placeholder="1">
                            </div>

                            <div class="pro-field-card" id="cust-notes-card" style="grid-column: 1 / -1;">
                                <label class="pro-field-label">
                                    <span>📝 ملاحظات</span>
                                </label>
                                <textarea id="cust-notes" placeholder="ملاحظات..."></textarea>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px; margin-top: 6px;">
                            <div style="padding: 10px 14px; border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; background: rgba(15, 23, 42, 0.4);" data-md-tip-title="📱 أرقام المناديب والتابعين للعميل" data-md-tip="كل مندوب يُضاف رقمه هنا يتمتع بنفس صلاحيات حساب العميل تماماً؛ فمثلاً إذا أضفت (المندوب علي) برقم هاتفه هنا، فحينما يرسل (علي) إشعار تحويل أو يطلب كشف حساب أو رصيداً من رقمه المعتمد، يُضاف الإشعار في حساب العميل الأساسي ويصله كشف حساب أو رصيد العميل فوراً.">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                    <h4 style="margin: 0; color: #38bdf8; display:flex; align-items:center; gap:6px; font-size: 0.9rem; font-weight: 800;">
                                        <span>📱</span> <span>أرقام المناديب والتابعين</span>
                                    </h4>
                                    <button type="button" onclick="addCustomerSubPhone()" data-md-tip-title="➕ إضافة رقم مندوب أو تابع" data-md-tip="إضافة رقم واتساب لمندوب أو موظف تابع لهذا العميل؛ أي رسالة أو إشعار أو طلب كشف/رصيد يُرسل من رقم المندوب المعتمد هنا يُعامل كأنه مرسل من العميل نفسه ويُقيد في حسابه مباشرة." style="height: 30px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); padding: 0 12px; border-radius: 8px; cursor: pointer; font-size: 0.78rem; font-weight: 800;">+ إضافة رقم</button>
                                </div>
                                <div id="cust-sub-phones-container" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;"></div>
                            </div>

                            <div style="padding: 10px 14px; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 10px; background: rgba(15, 23, 42, 0.4);" data-md-tip-title="🏦 الحسابات البنكية للعميل" data-md-tip="تسجيل أرقام حسابات العميل البنكية للتعرف التلقائي عليها ومطابقتها عند استلام التحويلات.">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                    <h4 style="margin: 0; color: #34d399; display:flex; align-items:center; gap:6px; font-size: 0.9rem; font-weight: 800;">
                                        <span>🏦</span> <span>الحسابات البنكية</span>
                                    </h4>
                                    <button type="button" onclick="addCustomerBankAccount()" data-md-tip-title="➕ إضافة حساب بنكي للعميل" data-md-tip="إضافة بيانات حساب بنكي خاص بالعميل (اسم البنك، الفرع، ورقم الحساب)." style="height: 30px; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); padding: 0 12px; border-radius: 8px; cursor: pointer; font-size: 0.78rem; font-weight: 800;">+ إضافة حساب</button>
                                </div>
                                <div id="cust-bank-accounts-container" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;"></div>
                            </div>
                        </div>

                        <div class="md-c3-form-footer">
                            <button type="button" class="btn-c3-save" onclick="saveCustomer()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-c3-cancel" onclick="closeCustomerForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div class="input-group" style="width: 100%; max-width: 480px; position: relative; margin-bottom: 0;">
                            <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.1rem; pointer-events: none;">🔍</span>
                            <input type="text" id="customer-search-input" oninput="filterCustomers()" placeholder="بحث بالاسم، الرقم السري، أو الهاتف..." style="padding-right: 42px; border-radius: 10px; border: 1px solid rgba(56,189,248,0.3); background: rgba(0,0,0,0.3); font-size: 0.95rem; color: #fff; width: 100%;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <button type="button" onclick="var f=document.getElementById('customer-form'); if(f){ var show=f.style.display==='none'; if(show && typeof clearCustomerForm==='function') clearCustomerForm(); f.style.display=show?'block':'none'; if(show) document.getElementById('cust-name')?.focus(); }" style="height: 36px; background: linear-gradient(135deg,#10b981,#059669); color: #fff; border: 1.5px solid #6ee7b7; padding: 0 14px; border-radius: 9px; font-weight: 900; font-size: 0.84rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">➕ عميل جديد</button>
                            <button type="button" id="btn-export-customers-csv" onclick="exportMasterEntityCsv('customer')" style="height: 36px; background: rgba(16, 185, 129, 0.14); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 14px; border-radius: 9px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📤 تصدير Excel</button>
                            <button type="button" id="btn-import-customers-csv" onclick="triggerMasterEntityCsvImport('customer')" style="height: 36px; background: rgba(56, 189, 248, 0.14); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 0 14px; border-radius: 9px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📥 استيراد Excel</button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th style="width:7%;text-align:center;">🔐 السري</th><th style="width:16%;text-align:right;">العميل</th><th style="width:10%;text-align:center;">الواتساب</th><th style="width:22%;text-align:center;">كشف الحساب والإشعارات</th><th style="width:6%;text-align:center;">الحالة</th><th style="width:11%;text-align:center;">الرصيد</th><th style="width:7%;text-align:center;">الائتمان</th><th style="width:6%;text-align:center;">المسؤول</th><th style="width:4%;text-align:center;">السرية</th><th style="width:11%;text-align:center;">الإجراء</th></tr></thead>
                            <tbody id="customers-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Suppliers Tab -->
            <div id="tab-suppliers" class="md-tab-content">
                <div class="md-card">
                    <!-- أزرار التحكم المباشر لواتساب الموردين -->
                    <div id="admin-wa-policy-panel-sup" style="margin-bottom: 14px; border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; background: rgba(15, 23, 42, 0.65); padding: 10px 14px;">
                        <div style="display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <button type="button" onclick="bulkSetEntityStatementMode('supplier', 'detailed')" data-md-tip-title="📊 كشف للكل (السماح للكل من الواتساب)" data-md-tip="يسمح لجميع الموردين بطلب واستلام كشف حساب مفصل بجميع الحركات والفواتير تلقائياً عبر الواتساب." style="height: 32px; background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">📊 كشف للكل</button>
                            <button type="button" onclick="bulkSetEntityStatementMode('supplier', 'balance_only')" data-md-tip-title="💰 رصيد للكل (طلب الرصيد عبر الواتساب)" data-md-tip="يسمح لجميع الموردين بطلب معرفة الرصيد النهائي فقط عبر الواتساب دون إرسال تفاصيل الفواتير." style="height: 32px; background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">💰 رصيد للكل</button>
                            <button type="button" onclick="bulkSetEntityStatementMode('supplier', 'disabled')" data-md-tip-title="🔒 إيقاف الكل (إيقاف الرد الآلي بالواتساب)" data-md-tip="يوقف جميع الموردين من طلب الرصيد أو كشف الحساب عبر الواتساب." style="height: 32px; background: rgba(239, 68, 68, 0.14); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🔒 إيقاف الكل</button>
                            <button type="button" onclick="bulkGenerateEntityPins('supplier')" data-md-tip-title="🔐 توليد باسورد للكل" data-md-tip="يقوم بتوليد رقم سري (باسورد) فريد تلقائياً لجميع الموردين لاستخدامه عند طلب كشف الحساب أو الرصيد عبر الواتساب." style="height: 32px; background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">🔐 توليد باسورد</button>
                            <button type="button" onclick="toggleAdminWaPolicyDrawer()" data-md-tip-title="⚙️ سياسة الصلاحيات العامة للواتساب" data-md-tip="فتح وإغلاق لوحة سياسة الصلاحيات الشاملة (صلاحية الكشف، طريقة اعتماد الإشعارات، البنك الافتراضي، التحقق الأمني، ونطاق الكشف)." style="height: 32px; background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.45); padding: 0 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;">⚙️ السياسة</button>
                        </div>

                        <div class="admin-wa-policy-drawer" style="display: none; margin-top: 12px; padding-top: 12px; border-top: 1px dashed rgba(168, 85, 247, 0.3);">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; align-items: end;">
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #e9d5ff; font-size: 0.8rem; font-weight: 800;">صلاحية الكشف</label>
                                    <select class="wa-policy-master-mode" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(168,85,247,0.45);">
                                        <option value="per_account">حسب كل حساب</option>
                                        <option value="force_detailed_all">كشف مفصل للكل</option>
                                        <option value="force_balance_only_all">الرصيد فقط للكل</option>
                                        <option value="force_disabled_all">إيقاف الكل</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #10b981; font-size: 0.8rem; font-weight: 800;">اعتماد الإشعارات</label>
                                    <select class="wa-policy-receipt-approval-mode" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(16,185,129,0.5);">
                                        <option value="per_account">حسب كل حساب</option>
                                        <option value="auto_post_all">اعتماد فوري للكل</option>
                                        <option value="review_first_all">مراجعة بالترحيل للكل</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #fbbf24; font-size: 0.8rem; font-weight: 800;">البنك الافتراضي</label>
                                    <select class="wa-policy-default-bank-id" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(245,158,11,0.5);">
                                        <option value="">تلقائي</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #38bdf8; font-size: 0.8rem; font-weight: 800;">التحقق الأمني</label>
                                    <select class="wa-policy-phone-verification" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(56,189,248,0.4);">
                                        <option value="phone_or_pin">الرقم المعتمد أو الباسورد</option>
                                        <option value="any_phone_with_code">الباسورد لأي رقم</option>
                                        <option value="registered_if_set">مطابقة الرقم أو الباسورد</option>
                                        <option value="strict_registered_only">الرقم المسجل فقط</option>
                                        <option value="pin_always">اشتراط الباسورد دائماً</option>
                                    </select>
                                </div>
                                <div class="input-group" style="margin-bottom: 0;">
                                    <label style="color: #34d399; font-size: 0.8rem; font-weight: 800;">نطاق الكشف</label>
                                    <select class="wa-policy-statement-scope" onchange="syncAdminWaPolicyFromUI(this)" style="font-weight: 800; font-size: 0.85rem; border-color: rgba(16,185,129,0.4);">
                                        <option value="all">كامل الحركات</option>
                                        <option value="last_30_days">آخر 30 يوماً</option>
                                        <option value="last_20">آخر 20 حركة</option>
                                        <option value="last_50">آخر 50 حركة</option>
                                        <option value="current_month">الشهر الحالي</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap; margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06);">
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #e2e8f0;">
                                    <input type="checkbox" class="wa-policy-include-items" onchange="syncAdminWaPolicyFromUI(this)" checked style="width: 16px; height: 16px; accent-color: #10b981;">
                                    <span>إظهار الأصناف</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #fbbf24;">
                                    <input type="checkbox" class="wa-policy-notify-admin" onchange="syncAdminWaPolicyFromUI(this)" style="width: 16px; height: 16px; accent-color: #f59e0b;">
                                    <span>إشعار المدير</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #38bdf8;">
                                    <input type="checkbox" class="wa-policy-antispam" onchange="syncAdminWaPolicyFromUI(this)" checked style="width: 16px; height: 16px; accent-color: #38bdf8;">
                                    <span>منع التكرار</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="supplier-form" class="md-card" style="display: block; margin-bottom: 18px; border: 1px solid rgba(255,255,255,0.1); background: rgba(8, 12, 24, 0.45); padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                            <h4 style="margin: 0; font-size: 1.08rem; color: #c084fc; display: flex; align-items: center; gap: 8px; font-weight: 900;">
                                <span>🏭</span> <span>بيانات المورد</span>
                            </h4>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <input type="hidden" id="sup-id" value="">
                                <input type="hidden" id="sup-allow-statement" value="1">
                                <input type="hidden" id="sup-statement-mode" value="detailed">
                                <input type="hidden" id="sup-receipt-approval-mode" value="auto_post">
                                <input type="hidden" id="sup-receipt-code-num" value="">
                                <input type="hidden" id="sup-receipt-code-word" value="">
                                <input type="hidden" id="sup-phone" value="">
                                <input type="hidden" id="sup-account-no" value="">
                                <input type="hidden" id="sup-opening-debit" value="0">
                                <input type="hidden" id="sup-opening-credit" value="0">
                                <div class="md-entity-seg-group" style="display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.85); border: 2px solid rgba(148, 163, 184, 0.5); border-radius: 10px; padding: 3px; gap: 4px; height: 36px; box-sizing: border-box;">
                                    <button type="button" id="sup-mode-btn-disabled" class="md-entity-seg-btn" onclick="setFormStatementMode('sup', 'disabled', true)" title="إيقاف هذا المورد من التعامل وطلب الرصيد أو الكشف بالواتساب" data-md-tip-title="🔒 موقوف" data-md-tip="إيقاف هذا المورد من التعامل وطلب الرصيد أو كشف الحساب عبر الواتساب." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">🔒 موقوف</button>
                                    <button type="button" id="sup-mode-btn-balance_only" class="md-entity-seg-btn" onclick="setFormStatementMode('sup', 'balance_only', true)" title="يسمح لهذا المورد بطلب الرصيد فقط من الواتساب" data-md-tip-title="💰 الرصيد فقط" data-md-tip="يسمح لهذا المورد بطلب الرصيد الإجمالي فقط من الواتساب دون تفاصيل الحركات." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">💰 الرصيد</button>
                                    <button type="button" id="sup-mode-btn-detailed" class="md-entity-seg-btn" onclick="setFormStatementMode('sup', 'detailed', true)" title="يسمح لهذا المورد بطلب كشف حساب عن طريق الواتساب" data-md-tip-title="📊 كشف حساب" data-md-tip="يسمح لهذا المورد بطلب كشف حساب مفصل عن طريق الواتساب." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">📊 كشف</button>
                                </div>
                                <div class="md-entity-seg-group" style="display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.85); border: 2px solid rgba(16, 185, 129, 0.55); border-radius: 10px; padding: 3px; gap: 4px; height: 36px; box-sizing: border-box;">
                                    <button type="button" id="sup-rcpt-btn-auto_post" class="md-entity-seg-btn" onclick="setFormReceiptApprovalMode('sup', 'auto_post', true)" title="اعتماد الإشعارات البنكية التي يرسلها المورد اعتماداً فورياً" data-md-tip-title="⚡ الاعتماد الفوري للإشعارات" data-md-tip="الإشعارات البنكية التي يرسلها هذا المورد (أو مناديبُه) عبر الواتساب تُعتمد اعتماداً فورياً وتُقيد في حسابه تلقائياً." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">⚡ اعتماد فوري</button>
                                    <button type="button" id="sup-rcpt-btn-review_first" class="md-entity-seg-btn" onclick="setFormReceiptApprovalMode('sup', 'review_first', true)" title="الإشعارات التي يرسلها المورد تُراجع قبل الاعتماد" data-md-tip-title="👁️ مراجعة الإشعارات قبل الاعتماد" data-md-tip="الإشعارات البنكية التي يرسلها هذا المورد عبر الواتساب تُحفظ للمراجعة أولاً قبل اعتمادها وترحيلها في الحساب." style="height: 28px; padding: 0 9px; border-radius: 7px; border: 1.6px solid rgba(148, 163, 184, 0.42); font-size: 0.74rem; font-weight: 800; cursor: pointer;">👁️ مراجعة</button>
                                </div>
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <select id="sup-default-bank-id" class="entity-default-bank-select" title="البنك الافتراضي لهذا المورد؛ حينما يحول الإشعار يُعتمد في هذا البنك" data-md-tip-title="🏦 البنك الافتراضي للمورد" data-md-tip="البنك الافتراضي المخصص لهذا المورد؛ حينما يُرسل إشعار تحويل عبر الواتساب يُعتمد في هذا البنك تلقائياً." style="height: 36px; background: rgba(15, 23, 42, 0.9); color: #fcd34d; border: 2px solid rgba(245, 158, 11, 0.6); border-radius: 10px; padding: 0 10px; font-size: 0.78rem; font-weight: 800; max-width: 175px;">
                                        <option value="">🏦 البنك الافتراضي</option>
                                    </select>
                                    <button type="button" class="md-hdr-pill-btn" onclick="quickAddBankModal('sup-default-bank-id')" title="إضافة بنك جديد سريعاً" data-md-tip-title="➕ إضافة بنك جديد" data-md-tip="فتح نافذة سريعة لتعريف بنك جديد واختياره كبنك افتراضي." style="height: 36px; padding: 0 10px; border-radius: 9px; background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 2px solid rgba(245, 158, 11, 0.55); font-weight: 900; cursor: pointer;">➕</button>
                                </div>
                                <button type="button" class="md-hdr-pill-btn" onclick="openEntityQrCardModal('sup')" title="عرض وطباعة بطاقة QR الخاصة بالمورد" data-md-tip-title="🪪 بطاقة QR الذكية للمورد" data-md-tip="تفتح بطاقة تعريف المورد المحتوية على رمز QR والرقم السري وكود الحساب لطباعتها أو مشاركتها." style="height: 36px; background: rgba(16, 185, 129, 0.18); color: #34d399; border: 2px solid rgba(16, 185, 129, 0.65); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">🪪 بطاقة QR</button>
                                <button type="button" class="md-hdr-pill-btn" onclick="copyCurrentFormWhatsAppCard('sup')" title="نسخ بطاقة بيانات المورد والرقم السري لإرسالها عبر الواتساب" data-md-tip-title="📋 نسخ البطاقة" data-md-tip="ينسخ بطاقة بيانات المورد شاملة الرقم السري وأوامر طلب الكشف والرصيد جاهزة للصق والإرسال في الواتساب." style="height: 36px; background: rgba(168, 85, 247, 0.18); color: #e9d5ff; border: 2px solid rgba(168, 85, 247, 0.65); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">📋 نسخ البطاقة</button>
                                <button type="button" onclick="clearSupplierForm()" class="btn-action md-hdr-pill-btn" title="تفريغ الحقول لإضافة مورد جديد (F2)" data-md-tip-title="🔄 مورد جديد (F2)" data-md-tip="تفريغ نموذج البيانات لبدء إضافة مورد جديد." style="height: 36px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 2px solid rgba(56, 189, 248, 0.55); padding: 0 12px; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 800;">🔄 جديد <span class="kbd-hint">F2</span></button>
                            </div>
                        </div>

                        <div class="pro-entity-grid">
                            <div class="pro-field-card accent-sky" style="grid-column: span 2;">
                                <label class="pro-field-label">
                                    <span>🏭 اسم المورد *</span>
                                </label>
                                <input type="text" id="sup-name" placeholder="اسم المورد أو الشركة">
                            </div>

                            <div class="pro-field-card accent-purple">
                                <label class="pro-field-label">
                                    <span>🔐 الرقم السري (آخر رقمين من الواتساب + 3 أرقام)</span>
                                    <button type="button" onclick="generateRandomFormPin('sup')" class="pro-field-badge" style="background:rgba(168,85,247,0.32); color:#fff; border:1.8px solid rgba(168,85,247,0.75); cursor:pointer;">🎲 توليد</button>
                                </label>
                                <input type="text" id="sup-statement-pin" class="pro-num-input" maxlength="8" oninput="updateCombinedPinPreview('sup')" placeholder="677">
                            </div>

                            <div class="pro-field-card accent-whatsapp">
                                <label class="pro-field-label">
                                    <span>💬 رقم الواتساب</span>
                                </label>
                                <input type="text" id="sup-whatsapp" class="pro-num-input" oninput="document.getElementById('sup-phone').value = this.value; if(typeof updateCombinedPinPreview==='function') updateCombinedPinPreview('sup');" placeholder="00249123000000">
                            </div>

                            <div class="pro-field-card" data-md-tip-title="💰 الرصيد الافتتاحي للمورد" data-md-tip="اضغط السهم العلوي (+) أو (دائن • له • علينا) للرصيد الموجب، أو السهم السفلي (−) أو (مدين • عليه • لنا) للرصيد السالب بضغطة واحدة.">
                                <label class="pro-field-label">
                                    <span>💰 الرصيد الافتتاحي</span>
                                </label>
                                <div class="op-bal-infield-wrap">
                                    <input type="number" id="sup-opening-balance" class="pro-num-input" step="any" placeholder="0.00" oninput="window.syncMdOpBalHint&&window.syncMdOpBalHint(this,'sup',true)">
                                    <div class="op-bal-sign-stepper">
                                        <span role="button" class="op-bal-step-btn step-pos" title="موجب (+): دائن • له • علينا" onclick="window.setMdOpBalSign('sup', 1)">▲</span>
                                        <span role="button" class="op-bal-step-btn step-neg" title="سالب (−): مدين • عليه • لنا" onclick="window.setMdOpBalSign('sup', -1)">▼</span>
                                    </div>
                                </div>
                                <div class="op-bal-infield-hint" id="sup-op-infield-hint">
                                    <span class="op-we-owe" data-sign="pos" onclick="window.setMdOpBalSign('sup', 1)" title="اضغط لجعل المبلغ موجب (+)">(+) دائن • له • علينا</span>
                                    <span class="op-sep">|</span>
                                    <span class="op-owe-us" data-sign="neg" onclick="window.setMdOpBalSign('sup', -1)" title="اضغط لجعل المبلغ سالب (−)">(−) مدين • عليه • لنا</span>
                                </div>
                            </div>
                            <script>
                            window.setMdOpBalSign = function(role, dir) {
                                var el = document.getElementById(role === 'sup' ? 'sup-opening-balance' : 'cust-opening-balance');
                                if (!el) return;
                                var raw = String(el.value || '').trim();
                                var num = parseFloat(raw);
                                if (!isNaN(num) && num !== 0) {
                                    el.value = (dir < 0 ? -Math.abs(num) : Math.abs(num));
                                    el.dataset.pendingSign = '';
                                } else {
                                    el.dataset.pendingSign = (dir < 0 ? 'neg' : 'pos');
                                    el.placeholder = (dir < 0 ? '-0.00' : '0.00');
                                }
                                window.syncMdOpBalHint(el, role, false);
                                el.focus();
                                try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}
                            };
                            window.syncMdOpBalHint = function(el, role, fromInput) {
                                if (!el) return;
                                if (fromInput && el.dataset.pendingSign === 'neg') {
                                    var typed = parseFloat(el.value);
                                    if (!isNaN(typed) && typed > 0) {
                                        el.value = -Math.abs(typed);
                                        el.dataset.pendingSign = '';
                                    }
                                } else if (fromInput && el.dataset.pendingSign === 'pos') {
                                    el.dataset.pendingSign = '';
                                }
                                var box = document.getElementById(role === 'sup' ? 'sup-op-infield-hint' : 'cust-op-infield-hint');
                                if (!box) return;
                                var v = parseFloat(el.value || 0);
                                var effNeg = (v < 0) || ((!v || isNaN(v)) && el.dataset.pendingSign === 'neg');
                                var effPos = (v > 0) || ((!v || isNaN(v)) && el.dataset.pendingSign === 'pos');
                                var posEl = box.querySelector('[data-sign="pos"]'), negEl = box.querySelector('[data-sign="neg"]');
                                if (posEl && negEl) {
                                    posEl.style.opacity = effNeg ? '0.35' : '1';
                                    negEl.style.opacity = effPos ? '0.35' : '1';
                                    posEl.classList.toggle('is-active-sign', effPos);
                                    negEl.classList.toggle('is-active-sign', effNeg);
                                }
                                var wrap = el.closest('.op-bal-infield-wrap');
                                if (wrap) {
                                    var bPos = wrap.querySelector('.step-pos'), bNeg = wrap.querySelector('.step-neg');
                                    if (bPos) bPos.classList.toggle('active', effPos);
                                    if (bNeg) bNeg.classList.toggle('active', effNeg);
                                }
                            };
                            </script>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🛑 حد الائتمان</span>
                                </label>
                                <input type="number" id="sup-limit" class="pro-num-input" placeholder="0.00">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>💱 العملة</span>
                                </label>
                                <select id="sup-currency" class="system-currency-select" onchange="updateSupExchangeRate()"></select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🟢 الحالة</span>
                                </label>
                                <select id="sup-status">
                                    <option value="active">🟢 نشط</option>
                                    <option value="blacklisted">🔴 محظور</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>📍 العنوان</span>
                                </label>
                                <input type="text" id="sup-address" placeholder="المدينة / الموقع">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>✉️ البريد الإلكتروني</span>
                                </label>
                                <input type="email" id="sup-email" placeholder="example@email.com" style="direction:ltr; text-align:center;">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🧾 الرقم الضريبي</span>
                                </label>
                                <input type="text" id="sup-tax" placeholder="الرقم الضريبي">
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>🛡️ السرية</span>
                                </label>
                                <select id="sup-security">
                                    <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option>
                                </select>
                            </div>

                            <div class="pro-field-card">
                                <label class="pro-field-label">
                                    <span>⏳ فترة السداد (يوم)</span>
                                </label>
                                <input type="number" id="sup-payment-terms" class="pro-num-input" min="0" placeholder="30">
                            </div>

                            <div class="pro-field-card accent-sky" id="sup-ex-rate-group" style="display: none;">
                                <label class="pro-field-label" id="sup-ex-rate-label" style="color:#38bdf8 !important;">
                                    <span>💵 سعر الصرف</span>
                                </label>
                                <input type="number" id="sup-ex-rate" class="pro-num-input" step="any" value="1" placeholder="1">
                            </div>

                            <div class="pro-field-card" id="sup-notes-card" style="grid-column: span 3;">
                                <label class="pro-field-label">
                                    <span>📝 ملاحظات</span>
                                </label>
                                <textarea id="sup-notes" placeholder="ملاحظات..."></textarea>
                            </div>
                        </div>

                        <div style="margin-top: 6px; padding: 10px 14px; border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; background: rgba(15, 23, 42, 0.4);" data-md-tip-title="📱 أرقام المناديب والتابعين للمورد" data-md-tip="كل مندوب يُضاف رقمه هنا يتمتع بنفس صلاحيات حساب المورد تماماً؛ فعندما يرسل المندوب إشعاراً أو يطلب كشف حساب أو رصيداً من رقمه المعتمد هنا، يُربط بالحساب الأساسي للمورد ويصله الكشف أو الرصيد فوراً.">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <h4 style="margin: 0; color: #38bdf8; display:flex; align-items:center; gap:6px; font-size: 0.9rem; font-weight: 800;">
                                    <span>📱</span> <span>أرقام المناديب والتابعين</span>
                                </h4>
                                <button type="button" onclick="addSupplierSubPhone()" data-md-tip-title="➕ إضافة رقم مندوب أو تابع" data-md-tip="إضافة رقم واتساب لمندوب تابع لهذا المورد؛ يتمتع المندوب بنفس صلاحية الحساب في إرسال الإشعارات وطلب الكشف والرصيد." style="height: 30px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); padding: 0 12px; border-radius: 8px; cursor: pointer; font-size: 0.78rem; font-weight: 800;">+ إضافة رقم</button>
                            </div>
                            <div id="sup-sub-phones-container" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;"></div>
                        </div>

                        <div class="md-c3-form-footer">
                            <button type="button" class="btn-c3-save" onclick="saveSupplier()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-c3-cancel" onclick="closeSupplierForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div class="input-group" style="width: 100%; max-width: 480px; position: relative; margin-bottom: 0;">
                            <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.1rem; pointer-events: none;">🔍</span>
                            <input type="text" id="supplier-search-input" oninput="filterSuppliers()" placeholder="بحث بالاسم، الرقم السري، أو الهاتف..." style="padding-right: 42px; border-radius: 10px; border: 1px solid rgba(56,189,248,0.3); background: rgba(0,0,0,0.3); font-size: 0.95rem; color: #fff; width: 100%;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <button type="button" onclick="var f=document.getElementById('supplier-form'); if(f){ var show=f.style.display==='none'; if(show && typeof clearSupplierForm==='function') clearSupplierForm(); f.style.display=show?'block':'none'; if(show) document.getElementById('sup-name')?.focus(); }" style="height: 36px; background: linear-gradient(135deg,#10b981,#059669); color: #fff; border: 1.5px solid #6ee7b7; padding: 0 14px; border-radius: 9px; font-weight: 900; font-size: 0.84rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">➕ مورد جديد</button>
                            <button type="button" id="btn-export-suppliers-csv" onclick="exportMasterEntityCsv('supplier')" style="height: 36px; background: rgba(16, 185, 129, 0.14); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 0 14px; border-radius: 9px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📥 تصدير Excel</button>
                            <button type="button" id="btn-import-suppliers-csv" onclick="triggerMasterEntityCsvImport('supplier')" style="height: 36px; background: rgba(168, 85, 247, 0.14); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.4); padding: 0 14px; border-radius: 9px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📤 استيراد Excel</button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="tab-suppliers-table">
                            <thead><tr><th style="width:7%;text-align:center;">🔐 السري</th><th style="width:18%;text-align:right;">المورد</th><th style="width:10%;text-align:center;">الواتساب</th><th style="width:23%;text-align:center;">كشف الحساب والإشعارات</th><th style="width:6%;text-align:center;">الحالة</th><th style="width:12%;text-align:center;">الرصيد</th><th style="width:8%;text-align:center;">الائتمان</th><th style="width:4%;text-align:center;">السرية</th><th style="width:12%;text-align:center;">الإجراء</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Banks Tab -->
            <div id="tab-banks" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header" style="flex-wrap: wrap; gap: 10px;">
                        <h3 class="md-card-title">🏦 البنوك</h3>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <div class="input-group" style="width: 260px; position: relative; margin-bottom: 0;">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none;">🔍</span>
                                <input type="text" id="bank-search-input" oninput="renderBanks()" placeholder="بحث باسم البنك أو الحساب..." style="padding-right: 36px; height: 36px; border-radius: 9px;">
                            </div>
                            <button class="btn-add" onclick="openBankForm()">+ إضافة بنك <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    <div id="bank-form" class="md-card" style="display: block;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="bank-form-title">بيانات البنك</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>الرقم</label><input type="text" id="bank-code" placeholder="رقم الحساب"></div>
                            <div class="input-group"><label>اسم البنك *</label><input type="text" id="bank-name" placeholder="مثال: بنك الخرطوم" oninput="autoUpdateMasterBankIBAN()"></div>
                            <div class="input-group" id="bank-branch-group"><label>الفرع</label><input type="text" id="bank-branch" placeholder="مثال: الفرع الرئيسي"></div>
                            <div class="input-group"><label>الرصيد الافتتاحي</label><input type="number" id="bank-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>تاريخ الرصيد</label><input type="date" id="bank-opening-date"></div>
                            <div class="input-group"><label>رقم الحساب البنكي</label><input type="text" id="bank-gl-account" placeholder="رقم الحساب" oninput="autoUpdateMasterBankIBAN()"></div>
                            <div class="input-group bank-only-field"><label>IBAN</label><input type="text" id="bank-iban" placeholder="IBAN"></div>
                            <div class="input-group bank-only-field"><label>SWIFT</label><input type="text" id="bank-swift" placeholder="SWIFT"></div>
                            
                            <div class="input-group"><label>العملة</label><select id="bank-currency" class="system-currency-select" onchange="updateBankExchangeRate()"></select></div>
                            <div class="input-group" id="bank-ex-rate-group" style="display: none;"><label id="bank-ex-rate-label" style="color:#38bdf8; font-weight:800;">💵 سعر الصرف</label><input type="number" id="bank-ex-rate" step="any" value="1" placeholder="1" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group"><label>السرية</label><select id="bank-confidentiality"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">طرق الدفع</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="cash" checked> كاش</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="pos" checked> شبكة</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="transfer" checked> تحويل</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="check"> شيكات</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="bank-inactive"> إيقاف الحساب</label>
                        </div>
                        <div class="md-c3-form-footer">
                            <button type="button" class="btn-c3-save" onclick="saveBank()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-c3-cancel" onclick="closeBankForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="banks-table">
                            <thead><tr><th>الرقم</th><th>اسم الحساب</th><th>النوع</th><th>العملة</th><th>الرصيد الافتتاحي</th><th>الإجراء</th></tr></thead>
                            <tbody id="banks-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-safes" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header" style="flex-wrap: wrap; gap: 10px;">
                        <h3 class="md-card-title">🏦 الخزن</h3>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <div class="input-group" style="width: 260px; position: relative; margin-bottom: 0;">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none;">🔍</span>
                                <input type="text" id="safe-search-input" oninput="renderSafes()" placeholder="بحث باسم الخزينة أو الرقم..." style="padding-right: 36px; height: 36px; border-radius: 9px;">
                            </div>
                            <button class="btn-add" onclick="openSafeForm()">+ إضافة خزينة <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    <div id="safe-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="safe-form-title">بيانات الخزينة</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>الرقم</label><input type="text" id="safe-code" placeholder="رقم الخزينة"></div>
                            <div class="input-group"><label>الاسم *</label><input type="text" id="safe-name" placeholder="مثال: الخزينة الرئيسية"></div>
                            <div class="input-group"><label>الرصيد الافتتاحي</label><input type="number" id="safe-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>تاريخ الرصيد</label><input type="date" id="safe-opening-date"></div>
                            <div class="input-group"><label>رقم حساب الأستاذ</label><input type="text" id="safe-gl-account" placeholder="110101"></div>
                            <div class="input-group" id="safe-responsible-group">
                                <label style="display:flex; justify-content:space-between; align-items:center;">
                                    <span>المسؤول</span>
                                    <button type="button" onclick="quickAddEmployeeModal('safe-responsible')" style="background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); border-radius:6px; padding:1px 7px; font-size:0.75rem; font-weight:800; cursor:pointer;">➕</button>
                                </label>
                                <select id="safe-responsible"><option value="">-- اختر --</option></select>
                            </div>
                            <div class="input-group"><label>العملة</label><select id="safe-currency" class="system-currency-select" onchange="updateSafeExchangeRate()"></select></div>
                            <div class="input-group" id="safe-ex-rate-group" style="display: none;"><label id="safe-ex-rate-label" style="color:#38bdf8; font-weight:800;">💵 سعر الصرف</label><input type="number" id="safe-ex-rate" step="any" value="1" placeholder="1" style="font-weight:800; color:#38bdf8;"></div>
                            <div class="input-group" id="safe-max-limit-group"><label>الحد الأعلى</label><input type="number" id="safe-max-limit" placeholder="0.00"></div>
                            <div class="input-group" id="safe-min-limit-group"><label>الحد الأدنى</label><input type="number" id="safe-min-limit" placeholder="0.00"></div>
                            <div class="input-group"><label>السرية</label><select id="safe-confidentiality"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">طرق الدفع</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="cash" checked> كاش</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="pos" checked> شبكة</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="transfer" checked> تحويل</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="check"> شيكات</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-inactive"> إيقاف الحساب</label>
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-modifier"> المعدل</label>
                        </div>
                        <div class="md-c3-form-footer">
                            <button type="button" class="btn-c3-save" onclick="saveSafe()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-c3-cancel" onclick="closeSafeForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="safes-table">
                            <thead><tr><th>الرقم</th><th>اسم الخزينة</th><th>العملة</th><th>الرصيد</th><th>الإجراء</th></tr></thead>
                            <tbody id="safes-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Items Tab -->
            <div id="tab-items" class="md-tab-content">
                <div class="md-card">
                    <div id="inner-products" class="inner-tab-content active">
                        <div id="item-form" class="md-card" style="display: none; padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                            <div style="background:linear-gradient(135deg,rgba(56,189,248,0.2),rgba(15,23,42,0.95)); padding:12px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; border-bottom:1px solid rgba(56,189,248,0.3);">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:44px; height:44px; background:linear-gradient(135deg, #38bdf8, #818cf8); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem;">📦</div>
                                    <h3 id="form-mode-indicator" style="margin:0; color:#fff; font-size:1.25rem; font-weight:800;">بيانات الصنف</h3>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; align-items: center;">
                                    <div class="neon-glass-panel neon-border-danger" style="padding: 5px 12px; display: flex; align-items: center; gap: 8px; border-radius: 8px; border: 1px solid rgba(244,63,94,0.3);">
                                        <span>⚠️</span>
                                        <div>
                                            <span style="color:var(--text-secondary); font-size:0.7rem; display: block; line-height: 1; margin-bottom: 2px;">النواقص</span>
                                            <strong id="kpi-low-stock" class="neon-text-danger" style="font-size:0.9rem; font-weight:800; line-height: 1;">0</strong>
                                        </div>
                                    </div>
                                    
                                    <button onclick="openInnerTab(event, 'inner-categories')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:8px 16px; font-size:0.9rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(168,85,247,0.2), rgba(88,28,135,0.6)); border: 1px solid rgba(168,85,247,0.5); color: #fff; cursor: pointer;">
                                        <span>🗂️</span> الفئات
                                    </button>
                                    <button onclick="openInnerTab(event, 'inner-units')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:8px 16px; font-size:0.9rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); color: #fff; cursor: pointer;">
                                        <span>📐</span> الوحدات
                                    </button>
                                    <button onclick="printItemBarcodeFromForm()" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:8px 16px; font-size:0.9rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(52,211,153,0.2), rgba(6,78,59,0.6)); border: 1px solid rgba(52,211,153,0.5); color: #fff; cursor: pointer;">
                                        <span>🖨️</span> باركود
                                    </button>
                                    <button class="btn-add hover-glow" style="background:linear-gradient(135deg, var(--success-color), #059669); padding:8px 20px; font-size: 0.95rem; border-radius: 8px; border: 1px solid rgba(16,185,129,0.5); color: #fff; cursor: pointer;" onclick="saveItem()">💾 حفظ <span class="kbd-hint">F10</span></button>
                                    <button onclick="closeItemForm()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.5); padding:8px 18px; border-radius:8px; cursor:pointer; font-weight:bold;">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                                </div>
                            </div>
                            
                            <div style="padding:20px; background: rgba(0,0,0,0.2);">
                                <div class="split-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
                                    <div class="neon-glass-panel split-panel" style="padding: 18px; border-top: 3px solid #38bdf8; background: linear-gradient(to bottom, rgba(56,189,248,0.05), transparent);">
                                        <div style="display:flex; align-items:center; gap:8px; margin-bottom: 14px;">
                                            <span style="font-size:1.3rem;">📋</span><h4 style="margin:0; color:#38bdf8; font-weight: 800;">بيانات التعريف</h4>
                                        </div>
                                        
                                        <div style="display:flex; gap:16px; align-items:flex-start;">
                                            <div style="display:flex; flex-direction:column; align-items:center; gap:6px; flex-shrink:0;">
                                                <input type="hidden" id="item-image-data" value="">
                                                <input type="file" id="item-image-file-input" accept="image/*" style="display:none;" onchange="handleItemImageUpload(event)">
                                                <div id="item-qr-box" onclick="printItemBarcodeFromForm()" style="width:98px; height:98px; border:2px dashed rgba(56,189,248,0.5); border-radius:14px; display:flex; flex-direction:column; justify-content:center; align-items:center; background:rgba(0,0,0,0.45); cursor:pointer; overflow:hidden; position:relative;">
                                                    <span style="font-size:1.8rem;">📱</span>
                                                    <span style="font-size:0.7rem; color:#38bdf8; margin-top:4px; font-weight:bold;">QR</span>
                                                </div>
                                                <div style="display:flex; gap:4px;">
                                                    <button type="button" onclick="document.getElementById('item-image-file-input').click()" style="background:rgba(56,189,248,0.16); color:#38bdf8; border:1px solid rgba(56,189,248,0.35); border-radius:6px; padding:3px 7px; font-size:0.7rem; font-weight:bold; cursor:pointer;">📷 صورة</button>
                                                    <button type="button" onclick="updateLiveItemQRPreview(true)" style="background:rgba(168,85,247,0.16); color:#d8b4fe; border:1px solid rgba(168,85,247,0.35); border-radius:6px; padding:3px 7px; font-size:0.7rem; font-weight:bold; cursor:pointer;">🔲 QR</button>
                                                </div>
                                            </div>
                                            <div class="form-grid" style="flex:1; grid-template-columns: 1fr; gap: 12px; margin-bottom: 0;">
                                                <div class="input-group"><label style="color:#fff; font-size:0.95rem; font-weight:900;">اسم الصنف *</label><input type="text" id="item-name-input" oninput="updateLiveItemQRPreview()" placeholder="مثال: سيخ 3 لنيه" style="width:100%; min-height:44px; font-size:1.08rem; font-weight:900; padding:8px 12px; border-color: rgba(56,189,248,0.5); background: rgba(0,0,0,0.3);"></div>
                                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                                                    <div class="input-group"><label style="font-size:0.92rem; font-weight:800;">الكود</label><input type="text" id="item-code" oninput="updateLiveItemQRPreview()" placeholder="تلقائي" style="width:100%; min-height:42px; font-size:1.02rem; padding:8px 12px; color:#38bdf8; font-weight:900;"></div>
                                                    <div class="input-group"><label style="font-size:0.92rem; font-weight:800;">الاسم اللاتيني</label><input type="text" id="item-name-latin" placeholder="Rebar" style="width:100%; min-height:42px; font-size:1.02rem; font-weight:800; padding:8px 12px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" id="item-sub-category" value="">
                                        <div class="form-grid" style="margin-top: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 0;">
                                            <div class="input-group"><label style="font-size:0.92rem; font-weight:800;">الباركود</label><div style="display:flex; gap:6px; width:100%;"><input type="text" id="item-barcode" oninput="updateLiveItemQRPreview()" placeholder="الباركود" style="flex:1; min-width:0; width:100%; min-height:44px; font-size:1.04rem; font-weight:800; padding:8px 12px;"><button type="button" class="btn-text" style="color:#38bdf8; background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.35); border-radius:8px; padding:0 12px; font-weight:800; height:44px; flex-shrink:0;" onclick="openWebRtcBarcodeScanner('item-barcode')">📷 مسح</button></div></div>
                                            <div class="input-group"><label style="font-size:0.92rem; font-weight:800;">الماركة</label><input type="text" id="item-brand" placeholder="الماركة / المنشأ" style="width:100%; min-height:44px; font-size:1.04rem; font-weight:800; padding:8px 12px;"></div>
                                            <div class="input-group"><label style="font-size:0.92rem; font-weight:800;">الفئة</label><div style="display:flex; gap:6px; width:100%;"><select id="item-category" style="flex:1; min-width:0; width:100%; min-height:44px; font-size:1.04rem; font-weight:800; padding:6px 12px;"></select><button type="button" class="btn-text" style="color:#a855f7; background:rgba(168,85,247,0.1); border-radius:8px; padding:0 12px; height:44px; flex-shrink:0;" onclick="quickAddCategory()">➕</button></div></div>
                                            <div class="input-group"><label style="font-size:0.92rem; font-weight:800;">الطبيعة</label><select id="item-nature" style="width:100%; min-height:44px; font-size:1.04rem; font-weight:800; padding:6px 12px;"><option value="مخزني">📦 مخزني</option><option value="خدمي">🔧 خدمي</option><option value="تجميعي">🏗️ تجميعي</option></select></div>
                                            <div class="input-group" style="grid-column: 1 / -1;"><label style="font-size:0.92rem; font-weight:800;">الحالة</label><div style="display:flex; align-items:center; gap:10px; min-height:44px; padding:8px 14px; background: rgba(0,0,0,0.2); border-radius: 9px;"><label class="toggle-sw"><input type="checkbox" id="item-active-toggle" checked onchange="updateToggleLabel()"><span class="toggle-sl"></span></label><span id="item-active-label" style="color:var(--success-color); font-weight:800; font-size:0.98rem;">🟢 نشط</span></div></div>
                                        </div>
                                    </div>

                                    <div class="split-panel" style="display: flex; flex-direction: column; gap: 18px;">
                                        <div class="neon-glass-panel" style="padding: 18px; border-top: 3px solid #34d399; background: linear-gradient(to bottom, rgba(52,211,153,0.05), transparent);">
                                            <div style="display:flex; align-items:center; gap:8px; margin-bottom: 12px;">
                                                <span style="font-size:1.3rem;">💰</span><h4 style="margin:0; color:#34d399; font-weight: 800;">التسعير والضرائب</h4>
                                            </div>
                                            <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                                                <div class="input-group"><label>سعر البيع</label><input type="number" id="item-selling-price" placeholder="0.00" style="font-weight:bold; color:var(--success-color); border-color: rgba(52,211,153,0.4); font-size: 1.1rem; background: rgba(52,211,153,0.05);"></div>
                                                <div class="input-group"><label>أقصى خصم %</label><input type="number" id="item-max-discount" placeholder="0" max="100"></div>
                                                <div class="input-group"><label>الضريبة</label><div style="padding:10px; background:rgba(0,0,0,0.2); border-radius:8px;"><label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin:0;"><input type="checkbox" id="item-taxable" onchange="document.getElementById('item-tax-rate').disabled=!this.checked" style="width:18px; height:18px; accent-color:#34d399;"> خاضع</label></div></div>
                                                <div class="input-group"><label>نسبة الضريبة %</label><input type="number" id="item-tax-rate" placeholder="0" disabled></div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                                            <div class="neon-glass-panel" style="padding: 18px; border-top: 3px solid #f59e0b; background: linear-gradient(to bottom, rgba(245,158,11,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:8px; margin-bottom: 12px;">
                                                    <span style="font-size:1.2rem;">📊</span><h5 style="margin:0; color:#f59e0b; font-weight: 800;">التكلفة والرصيد</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px; grid-column: 1 / -1;"><label style="color:#f59e0b;">طريقة التكلفة</label>
                                                    <div style="display:flex; gap:10px; flex-wrap:wrap; background: rgba(0,0,0,0.2); padding: 8px; border-radius: 8px;">
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="lastPurchase" checked style="accent-color:#f59e0b;"> آخر شراء</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="highest" style="accent-color:#f59e0b;"> الأعلى</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="opening" style="accent-color:#f59e0b;"> الافتتاحي</label>
                                                    </div>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>الكمية الافتتاحية</label><input type="number" id="item-opening-qty" placeholder="0"></div>
                                                <div class="input-group"><label>تكلفة الوحدة</label><input type="number" id="item-opening-value" placeholder="0.00"></div>
                                            </div>

                                            <div class="neon-glass-panel" style="padding: 18px; border-top: 3px solid #ef4444; background: linear-gradient(to bottom, rgba(239,68,68,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:8px; margin-bottom: 12px;">
                                                    <span style="font-size:1.2rem;">🏭</span><h5 style="margin:0; color:#ef4444; font-weight: 800;">حدود المخزون</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>موقع الرف</label><input type="text" id="item-bin-location" placeholder="ممر 3"></div>
                                                <div style="display:flex; gap:10px; margin-bottom: 10px;">
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">الحد الأعلى</label><input type="number" id="item-max-limit" placeholder="500"></div>
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">الحد الأدنى</label><input type="number" id="item-min-limit" placeholder="20"></div>
                                                </div>
                                                <div class="input-group"><label style="color:#f59e0b;">حد الطلب</label><input type="number" id="item-reorder-level" placeholder="50"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="neon-glass-panel" style="margin-top: 18px; padding: 20px; border-top: 3px solid #a855f7; background: linear-gradient(to bottom, rgba(168,85,247,0.05), transparent);">
                                    <div style="display:flex; align-items:center; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:1.4rem;">📐</span><h4 style="margin:0; color:#a855f7; font-weight: 800; font-size: 1.15rem;">الوحدات</h4>
                                        </div>
                                        <div style="display:flex; align-items:center; justify-content: flex-start; gap:12px; background: rgba(0,0,0,0.3); padding: 8px 14px; border-radius: 10px; border: 1px solid rgba(168,85,247,0.3); flex: 1; min-width: 280px;">
                                            <label style="color:#a855f7; font-weight:bold; margin:0; font-size: 0.95rem;">الوحدة الأساسية *</label>
                                            <div style="display:flex; align-items:center; gap:5px;">
                                                <select id="item-base-unit" onchange="updateBaseUnitRow()" style="border:none; border-bottom: 2px solid #a855f7; background:transparent; color:#fff; font-weight:bold; font-size: 1.05rem; padding: 4px; outline:none; width: 140px; text-align: center;">
                                                    <option value="">-- اختر --</option>
                                                </select>
                                                <button class="btn-text" style="color:#38bdf8; background:rgba(56,189,248,0.1); border-radius:8px; padding:0 8px; font-size:0.9rem;" onclick="quickAddUnit()">➕</button>
                                            </div>
                                            <div style="width: 1px; height: 22px; background: rgba(168,85,247,0.3);"></div>
                                            <label style="color:#a855f7; font-weight:bold; margin:0; font-size: 0.95rem;">الشيلة</label>
                                            <input type="number" id="item-bundle-factor" placeholder="15" value="1" min="1" style="border:none; border-bottom: 2px solid #a855f7; background:transparent; color:#fff; font-weight:bold; font-size: 1.05rem; padding: 4px; outline:none; width: 70px; text-align: center;">
                                        </div>
                                    </div>
                                    
                                    <div style="background:rgba(15,23,42,0.8); border-radius:12px; border:1px solid rgba(168,85,247,0.2); overflow: hidden;">
                                        <div style="display:flex; justify-content:flex-end; align-items:center; gap:8px; padding:10px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(168,85,247,0.05); flex-wrap:wrap;">
                                            <button type="button" class="btn-text" style="color:#fef08a; font-weight:900; background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.45); padding:7px 14px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:5px;" onclick="window.generateMultiUnitEan13Barcodes()">⚡ توليد باركودات الوحدات (EAN-13)</button>
                                            <button type="button" class="btn-text" style="color:#fff; font-weight:bold; background:linear-gradient(135deg, #a855f7, #9333ea); padding:7px 14px; border-radius:8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 5px;" onclick="addUnitRow()">+ إضافة وحدة</button>
                                        </div>
                                        <div class="table-responsive" style="overflow-x: auto;">
                                            <table class="pro-table" style="width: 100%; min-width: 1200px;">
                                                <thead><tr style="background: rgba(0,0,0,0.2);">
                                                    <th style="padding: 12px; width: 4%; text-align: center;">م</th>
                                                    <th style="padding: 12px; width: 14%;">الوحدة</th>
                                                    <th style="padding: 12px; width: 8%; text-align: center;">المعادل</th>
                                                    <th style="padding: 12px; width: 12%;">الباركود</th>
                                                    <th style="padding: 12px; width: 16%; color: #34d399;">نقدي</th>
                                                    <th style="padding: 12px; width: 16%; color: #60a5fa;">آجل</th>
                                                    <th style="padding: 12px; width: 16%; color: #f87171;">أقل سعر</th>
                                                    <th style="padding: 12px; width: 7%;">الوزن</th>
                                                    <th style="padding: 12px; width: 7%; text-align: center;">إجراء</th>
                                                </tr></thead>
                                                <tbody id="units-matrix-body">
                                                    <tr style="background:rgba(52,211,153,0.05); border-right:4px solid var(--success-color);">
                                                        <td style="text-align: center; font-weight: bold; color: var(--text-secondary);">1</td>
                                                        <td><input type="text" id="base-unit-name-display" value="" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; font-size: 1.05rem; outline: none;"></td>
                                                        <td><input type="number" value="1" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; font-size:1.1rem; outline: none;"></td>
                                                        <td><input type="text" placeholder="الباركود" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:8px; border-radius:8px; outline: none;"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#34d399; padding:8px; border-radius:8px; font-weight:bold; outline: none;"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#60a5fa; padding:8px; border-radius:8px; font-weight:bold; outline: none;"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#f87171; padding:8px; border-radius:8px; font-weight:bold; outline: none;"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:8px; border-radius:8px; outline: none;"></td>
                                                        <td style="text-align: center;"><span style="font-size:0.78rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); border: 1px solid rgba(52,211,153,0.4); padding:5px 10px; border-radius:6px; display: inline-block;">أساسية</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="md-c16-item-enterprise-fields" class="neon-glass-panel" style="margin-top: 16px; padding: 16px; border-top: 3px solid #10b981; background: linear-gradient(to bottom, rgba(16,185,129,0.06), transparent);">
                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom: 12px; flex-wrap:wrap;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:1.1rem;">💎</span>
                                            <h5 style="margin:0; color:#34d399; font-weight: 900;">الضبط المؤسسي والتجاري المتقدم للأصناف</h5>
                                        </div>
                                        <span style="font-size:0.75rem; font-weight:800; color:#a7f3d0; background:rgba(16,185,129,0.14); border:1px solid rgba(16,185,129,0.4); padding:2px 9px; border-radius:999px;">حاسبة مدمجة + قفل عدم البيع بخسارة</span>
                                    </div>
                                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(185px, 1fr)); gap:12px;">
                                        <div class="input-group" style="margin:0;">
                                            <label style="font-size:0.82rem; font-weight:800; color:#cbd5e1;">🏷️ الرمز الجمركي / القياسي (HS Code)</label>
                                            <input type="text" id="item-c16-hs-customs-code" placeholder="مثال: 7214.20.00" data-guide-title="الرمز الجمركي والتصنيف القياسي العالمي (HS Code)" data-guide-desc="كود التنسيق الجمركي والضريبي للصنف لأغراض الاستيراد والفوترة الضريبية المعتمدة." style="width:100%; height:40px; padding:0 10px; border-radius:8px; font-weight:800;">
                                        </div>
                                        <div class="input-group" style="margin:0;">
                                            <label style="font-size:0.82rem; font-weight:800; color:#cbd5e1;">🤝 المورد المفضل الافتراضي</label>
                                            <input type="text" id="item-c16-preferred-supplier" placeholder="اسم أو كود المورد الرئيسي..." data-guide-title="المورد المفضل الافتراضي للصنف" data-guide-desc="يُستخدم لاقتراح المورد آلياً عند وصول الصنف لحد الطلب في رادار النواقص وأوامر الشراء." style="width:100%; height:40px; padding:0 10px; border-radius:8px; font-weight:800;">
                                        </div>
                                        <div class="input-group" style="margin:0;">
                                            <label style="font-size:0.82rem; font-weight:800; color:#cbd5e1;">⏱️ فترة التوريد بالأيام (Lead Time)</label>
                                            <input type="text" inputmode="decimal" id="item-c16-lead-time-days" class="pro-num-input" value="3" placeholder="مثال: 3" data-guide-title="فترة التوريد القياسية بالأيام (Lead Time)" data-guide-desc="عدد الأيام المتوقعة لوصول البضاعة من المورد منذ لحظة إصدار أمر الشراء (يدعم الحاسبة المدمجة)." style="width:100%; height:40px; padding:0 10px; border-radius:8px; font-weight:900; font-variant-numeric:tabular-nums;">
                                        </div>
                                        <div class="input-group" style="margin:0;">
                                            <label style="font-size:0.82rem; font-weight:800; color:#cbd5e1;">🛡️ فترة الضمان / الصلاحية (يوم)</label>
                                            <input type="text" inputmode="decimal" id="item-c16-warranty-shelf-days" class="pro-num-input" value="365" placeholder="مثال: 365" data-guide-title="فترة الصلاحية أو الضمان بالأيام" data-guide-desc="مدة صلاحية الصنف أو فترة الضمان الممنوحة للعميل بالأيام لتنبيه المخزن قبل الانتهاء." style="width:100%; height:40px; padding:0 10px; border-radius:8px; font-weight:900; font-variant-numeric:tabular-nums;">
                                        </div>
                                        <div class="input-group" style="margin:0;">
                                            <label style="font-size:0.82rem; font-weight:800; color:#fde047;">📦 سعر الجملة المعتمد (SDG)</label>
                                            <input type="text" inputmode="decimal" id="item-c16-wholesale-price" class="pro-num-input" placeholder="0.00 (يدعم 12*24)" data-guide-title="سعر الجملة المعتمد للصنف" data-guide-desc="سعر البيع لشريحة تجار الجملة مع دعم كامل للآلة الحاسبة المدمجة والفاصلة العربية." style="width:100%; height:40px; padding:0 10px; border-radius:8px; font-weight:900; font-variant-numeric:tabular-nums;">
                                        </div>
                                        <div class="input-group" style="margin:0;">
                                            <label style="font-size:0.82rem; font-weight:800; color:#f87171;">🔒 الحد الأدنى الصارم لسعر البيع</label>
                                            <input type="text" inputmode="decimal" id="item-c16-min-floor-price" class="pro-num-input" placeholder="قفل عدم البيع بخسارة" data-guide-title="الحد الأدنى الصارم لسعر البيع (Price Floor Lock)" data-guide-desc="يمنع الكاشير أو المندوب في المبيعات من النزول بسعر الصنف تحت هذا الحد إلا بتفويض المدير." style="width:100%; height:40px; padding:0 10px; border-radius:8px; font-weight:900; font-variant-numeric:tabular-nums;">
                                        </div>
                                    </div>
                                </div>

                                <div class="neon-glass-panel" style="margin-top: 16px; padding: 16px; border-top: 3px solid #94a3b8; background: linear-gradient(to bottom, rgba(148,163,184,0.05), transparent);">
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom: 10px;">
                                        <span style="font-size:1.1rem;">📝</span><h5 style="margin:0; color:#94a3b8; font-weight: 800;">ملاحظات</h5>
                                    </div>
                                    <textarea id="item-description" placeholder="ملاحظات..." style="width:100%; min-height:70px; padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); color:#fff; font-family:inherit; resize:vertical; box-sizing:border-box; font-size: 0.95rem; outline: none;"></textarea>
                                </div>
                                <div class="md-c3-form-footer" style="display:flex; justify-content:flex-end; align-items:center; gap:10px; margin-top:14px; padding-top:12px; border-top:1px solid rgba(148,163,184,0.25);">
                                    <button type="button" onclick="openItemForm()" style="background:linear-gradient(135deg, #38bdf8, #2563eb); color:#fff; padding:9px 20px; border-radius:10px; border:none; cursor:pointer; font-weight:900; font-size:0.9rem;">🧹 جديد / تنظيف <span class="kbd-hint">F2</span></button>
                                    <button type="button" onclick="saveItem()" style="background:linear-gradient(135deg, var(--success-color), #059669); color:#fff; padding:9px 26px; border-radius:10px; border:1px solid rgba(16,185,129,0.5); cursor:pointer; font-weight:900; font-size:0.95rem; box-shadow:0 4px 14px rgba(16,185,129,0.35);">💾 حفظ <span class="kbd-hint">F10</span></button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="md-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px; background:rgba(0,0,0,0.3); padding:8px 15px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); flex:1; max-width:400px;">
                                    <span>🔍</span>
                                    <input type="text" id="item-search" oninput="filterItemsTable()" placeholder="بحث بالاسم أو الكود..." style="background:transparent; border:none; color:#fff; width:100%; outline:none;">
                                </div>
                                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                    <span id="kpi-total-items-badge" class="md-hdr-pill-btn" style="background:rgba(56,189,248,0.18); color:#38bdf8; padding:6px 14px; border-radius:20px; font-weight:900; border:2px solid rgba(56,189,248,0.55);">الأصناف: <strong id="kpi-total-items">0</strong></span>
                                    <button type="button" class="md-hdr-pill-btn" onclick="window.shareMasterItemsWhatsApp && window.shareMasterItemsWhatsApp()" style="height: 36px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.38)); color: #6ee7b7; border: 2px solid #10b981; padding: 0 14px; border-radius: 10px; font-weight: 900; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">💬 واتساب الأصناف</button>
                                    <button type="button" class="md-hdr-pill-btn" onclick="openBulkPriceUpdaterModal()" style="height: 36px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(217, 119, 6, 0.35)); color: #fde68a; border: 2px solid #f59e0b; padding: 0 14px; border-radius: 10px; font-weight: 900; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📈 تعديل الأسعار</button>
                                    <button type="button" class="md-hdr-pill-btn" onclick="openSmartClipboardPasteModal('item')" style="height: 36px; background: linear-gradient(135deg, rgba(168, 85, 247, 0.22), rgba(126, 34, 206, 0.35)); color: #e9d5ff; border: 2px solid #a855f7; padding: 0 14px; border-radius: 10px; font-weight: 900; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📋 لصق Excel</button>
                                    <button type="button" id="btn-export-items-csv" class="md-hdr-pill-btn" onclick="exportMasterEntityCsv('item')" style="height: 36px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(4, 120, 87, 0.35)); color: #6ee7b7; border: 2px solid #10b981; padding: 0 14px; border-radius: 10px; font-weight: 900; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📥 تصدير Excel</button>
                                    <button type="button" id="btn-import-items-csv" class="md-hdr-pill-btn" onclick="triggerMasterEntityCsvImport('item')" style="height: 36px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(180, 83, 9, 0.35)); color: #fde68a; border: 2px solid #f59e0b; padding: 0 14px; border-radius: 10px; font-weight: 900; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📤 استيراد Excel</button>
                                    <button class="btn-add hover-glow md-hdr-pill-btn" onclick="openItemForm()" style="background:linear-gradient(135deg, #38bdf8, #2563eb); padding:8px 18px; border-radius:10px; font-weight:900; border:2px solid #7dd3fc; color:#fff; cursor:pointer;">🧹 جديد / تنظيف <span class="kbd-hint">F2</span></button>
                                    <button type="button" class="btn-add hover-glow md-hdr-pill-btn" onclick="saveItem()" style="background:linear-gradient(135deg, var(--success-color), #059669); padding:8px 20px; border-radius:10px; font-weight:900; border:2px solid #6ee7b7; color:#fff; cursor:pointer; box-shadow:0 4px 12px rgba(16,185,129,0.3);">💾 حفظ <span class="kbd-hint">F10</span></button>
                                </div>
                            </div>
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="pro-table" id="main-items-table">
                                    <thead><tr><th>الكود</th><th>الصنف</th><th>الفئة</th><th>الوحدة</th><th>سعر البيع</th><th>الحالة</th><th>الإجراء</th></tr></thead>
                                    <tbody id="items-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="inner-categories" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-purple" style="margin-bottom:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <h4 style="margin:0; color:#a855f7;">الفئات</h4>
                                <button type="button" class="btn-text" style="color:#38bdf8; font-weight:bold;" onclick="openInnerTab(event, 'inner-products')">↩ الأصناف</button>
                            </div>
                            <div class="form-grid">
                                <div class="input-group"><label>اسم الفئة</label><input type="text" id="cat-name-input" placeholder="مثال: أسمنت"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:var(--success-color); padding:10px 20px; height:fit-content;" onclick="saveCategory()">💾 حفظ <span class="kbd-hint">F10</span></button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th>اسم الفئة</th><th>الأصناف</th><th>الإجراء</th></tr></thead>
                                <tbody id="categories-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="inner-units" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-info" style="margin-bottom:20px; border-color: rgba(56,189,248,0.3);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                                <h4 style="margin:0; color:#38bdf8; font-weight:bold; font-size:1.1rem;">📐 الوحدات</h4>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" class="btn-add" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; padding:6px 14px; font-size:0.82rem;" onclick="cleanAndResetUnits()">🧹 استعادة الوحدات</button>
                                    <button type="button" class="btn-text" style="color:#38bdf8; font-weight:bold;" onclick="openInnerTab(event, 'inner-products')">↩ الأصناف</button>
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="input-group"><label>اسم الوحدة</label><input type="text" id="unit-name-input" placeholder="مثال: طن، كرتونة، حبة"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); padding:10px 20px; height:fit-content; font-weight:bold;" onclick="saveGlobalUnit()">💾 حفظ <span class="kbd-hint">F10</span></button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th style="padding:12px 15px;">اسم الوحدة</th><th style="padding:12px 15px; text-align:center; width:180px;">الإجراء</th></tr></thead>
                                <tbody id="units-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employees Tab -->
            <div id="tab-employees" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header" style="flex-wrap: wrap; gap: 10px;">
                        <h3 class="md-card-title">👥 الموظفين</h3>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <div class="input-group" style="width: 240px; position: relative; margin-bottom: 0;">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none;">🔍</span>
                                <input type="text" id="employee-search-input" oninput="renderEmployees()" placeholder="بحث باسم الموظف أو الهاتف..." style="padding-right: 36px; height: 36px; border-radius: 9px;">
                            </div>
                            <button type="button" class="md-hdr-pill-btn" onclick="window.shareMasterEmployeesWhatsApp && window.shareMasterEmployeesWhatsApp()" style="height: 36px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.38)); color: #6ee7b7; border: 2px solid #10b981; padding: 0 14px; border-radius: 10px; font-weight: 900; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">💬 واتساب الموظفين</button>
                            <button type="button" class="md-hdr-pill-btn" onclick="exportActiveTabToExcel()" style="height: 36px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(4, 120, 87, 0.35)); color: #6ee7b7; border: 2px solid #10b981; padding: 0 14px; border-radius: 10px; font-weight: 900; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📥 تصدير Excel</button>
                            <button type="button" class="md-hdr-pill-btn" onclick="triggerActiveTabExcelImport()" style="height: 36px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(180, 83, 9, 0.35)); color: #fde68a; border: 2px solid #f59e0b; padding: 0 14px; border-radius: 10px; font-weight: 900; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">📤 استيراد Excel</button>
                            <button class="btn-add md-hdr-pill-btn" onclick="openNewEmployeeForm()">🧹 جديد / تنظيف <span class="kbd-hint">F2</span></button>
                            <button type="button" class="btn-c3-save md-hdr-pill-btn" onclick="saveEmployee()" style="height:36px; padding:0 18px; border-radius:10px; background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:2px solid #6ee7b7; font-weight:900; cursor:pointer;">💾 حفظ <span class="kbd-hint">F10</span></button>
                        </div>
                    </div>
                    <div id="employee-form" class="md-card" style="display: block;">
                        <h4 style="margin-top:0; color:var(--primary-accent);">👥 بيانات الموظف والواتساب</h4>
                        <div class="form-grid">
                            <div class="input-group"><label>الكود</label><input type="text" id="emp-id" disabled></div>
                            <div class="input-group"><label>الاسم *</label><input type="text" id="emp-name"></div>
                            <div class="input-group"><label>💬 رقم الواتساب / الهاتف</label><input type="text" id="emp-phone" placeholder="00249123000000"></div>
                            <div class="input-group"><label>كلمة المرور</label><input type="text" id="emp-password" placeholder="كلمة المرور"></div>
                            <div class="input-group"><label>الدور</label><select id="emp-role"><option value="admin">مدير</option><option value="accountant">محاسب</option><option value="cashier">كاشير</option><option value="sales">مبيعات</option><option value="storekeeper">أمين مخزن</option></select></div>
                            <div class="input-group"><label style="color:#34d399; font-weight:900;">💰 الراتب الشهري (ينزل يوم 1 كل شهر)</label><input type="number" id="emp-salary" placeholder="مثال: 300000" step="any"></div>
                            <div class="input-group"><label style="color:#fbbf24; font-weight:900;">⚖️ رصيد افتتاحي (+سلفة عليه / -مستحق له)</label><input type="number" id="emp-opening-balance" placeholder="مثال: 100000" step="any"></div>
                            <div class="input-group"><label>السرية</label><select id="emp-security"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select></div>
                            <div class="input-group"><label>الحالة</label><select id="emp-status"><option value="active">نشط</option><option value="inactive">موقوف</option></select></div>
                        </div>
                        <div class="md-c3-form-footer">
                            <button type="button" class="btn-c3-save" onclick="saveEmployee()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-c3-cancel" onclick="window.closeEmployeeForm ? window.closeEmployeeForm() : (document.getElementById('employee-form').style.display='none')">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>الاسم</th><th>الدور</th><th>الراتب الشهري</th><th>الرصيد الحالي</th><th>الهاتف</th><th>الحالة</th><th>الصلاحيات والإجراءات</th></tr></thead>
                            <tbody id="tab-employees-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-costcenters" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header" style="flex-wrap: wrap; gap: 10px;">
                        <h3 class="md-card-title">مراكز التكلفة</h3>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <div class="input-group" style="width: 260px; position: relative; margin-bottom: 0;">
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1rem; pointer-events: none;">🔍</span>
                                <input type="text" id="costcenter-search-input" oninput="renderCostCenters()" placeholder="بحث باسم المركز أو الكود..." style="padding-right: 36px; height: 36px; border-radius: 9px;">
                            </div>
                            <button class="btn-add" onclick="openCostCenterForm()">+ إضافة مركز تكلفة <span class="kbd-hint">F2</span></button>
                        </div>
                    </div>
                    
                    <div id="costcenter-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0;">بيانات مركز التكلفة</h4>
                            <button class="btn-c3-cancel" style="display: none;" id="btn-cancel-costcenter" onclick="closeCostCenterForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="costcenter-id">
                            <div class="input-group"><label>الكود</label><input type="text" id="costcenter-code" placeholder="تلقائي" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;"></div>
                            <div class="input-group"><label>الاسم *</label><input type="text" id="costcenter-name" placeholder="مثال: مشروع الخرطوم"></div>
                            <div class="input-group"><label>ملاحظات</label><input type="text" id="costcenter-desc" placeholder="ملاحظات..."></div>
                        </div>
                        <div class="md-c3-form-footer">
                            <button type="button" class="btn-c3-save" onclick="saveCostCenter()">💾 حفظ <span class="kbd-hint">F10</span></button>
                            <button type="button" class="btn-c3-cancel" onclick="closeCostCenterForm()">✖ إلغاء <span class="kbd-hint">Esc</span></button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>الكود</th><th>الاسم</th><th>الوصف</th><th>الإجراء</th></tr></thead>
                            <tbody id="costcenters-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Universal Master Data Entity Deletion & Voucher Check Modal -->
    <div id="master-data-delete-modal" class="md-child-modal" style="display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(0, 0, 0, 0.82);   justify-content: center; align-items: center; padding: 20px; animation: fadeIn 0.25s ease-out;">
        <div class="neo-card md-child-modal-card" style="background: rgba(18, 15, 25, 0.96); border: 2.5px solid rgba(255, 255, 255, 0.15); border-radius: 22px; width: 100%; max-width: 630px; box-shadow: 0 25px 65px rgba(0,0,0,0.85), 0 0 35px rgba(0,0,0,0.6); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; font-family: 'Tajawal', sans-serif;">
            <div id="md-del-modal-header" style="padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.02);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div id="md-del-modal-icon-wrap" style="width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); box-shadow: 0 0 20px rgba(239,68,68,0.2);">
                        <span id="md-del-modal-icon">🚫</span>
                    </div>
                    <div>
                        <h3 id="md-del-modal-title" style="margin: 0; font-size: 1.3rem; font-weight: 900; color: #fff; letter-spacing: 0.3px;">فحص السندات المرتبطة</h3>
                    </div>
                </div>
                <button type="button" onclick="closeEntityDeleteModal()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #fff; width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">✕</button>
            </div>

            <div style="padding: 24px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 18px;">
                <div style="background: linear-gradient(135deg, rgba(56,189,248,0.08), rgba(99,102,241,0.05)); border: 1px solid rgba(56,189,248,0.25); border-radius: 16px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span id="md-del-target-type-label" style="font-size: 0.8rem; color: #94a3b8; font-weight: 700; display: block; margin-bottom: 2px;">📦 الاسم:</span>
                        <strong id="md-del-target-name" style="font-size: 1.3rem; color: #38bdf8; font-weight: 900;">-</strong>
                    </div>
                    <div style="text-align: left;">
                        <span id="md-del-target-code-label" style="font-size: 0.8rem; color: #94a3b8; font-weight: 700; display: block; margin-bottom: 2px;">🏷️ الكود:</span>
                        <span id="md-del-target-code" style="font-family: 'Outfit', monospace; font-size: 1.15rem; font-weight: 900; color: #f1f5f9; background: rgba(0,0,0,0.3); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">-</span>
                    </div>
                </div>

                <div id="md-del-status-box" style="border-radius: 16px; padding: 18px; border: 1.5px solid transparent;"></div>

                <div id="md-del-vouchers-container" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span id="md-del-vouchers-header-label" style="font-size: 0.95rem; font-weight: 800; color: #f87171; display: flex; align-items: center; gap: 6px;">📋 السندات المرتبطة:</span>
                        <span id="md-del-vouchers-count" style="font-size: 0.85rem; background: rgba(239,68,68,0.25); color: #fca5a5; padding: 3px 10px; border-radius: 8px; font-weight: 900; border: 1px solid rgba(239,68,68,0.4);">0 سند</span>
                    </div>
                    <div id="md-del-vouchers-list" style="max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 10px; display: flex; flex-direction: column; gap: 8px;"></div>
                </div>
            </div>

            <div id="md-del-modal-footer" style="padding: 18px 24px; border-top: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap;"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/master-data-core.js?v=<?php echo file_exists('js/master-data-core.js') ? filemtime('js/master-data-core.js') : '20261030_v10'; ?>"></script>

    <!-- 🛡️ الدستور الأول والثاني (1&2 من 14): المحرك السيادي للسمات، الشاشات الوليدة، تناسب الأبعاد 6:8، الخفة القصوى 2G، الكيرسر، والبحث الذكي -->
<script src="js/screens/master-data-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/master-data-j1.js') ?: '2027' ?>"></script>
    <script src="js/master-data-purchase-constitution-2027.js?v=2027_contrast_borders_v3"></script>
    <script>
    (function initMasterTablesColumnControlAndCompactEngine() {
        var STORAGE_KEY = 'erp_md_hidden_cols_v2';
        var COMPACT_KEYWORDS = ['السرية', 'العنوان', 'الهاتف', 'المسؤول', 'الائتمان', 'الوصف', 'النوع', 'الوزن', 'أقل سعر', 'الدور', 'الفئة'];
        function loadMap() { try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') || {}; } catch (_) { return {}; } }
        function saveMap(m) { try { localStorage.setItem(STORAGE_KEY, JSON.stringify(m)); } catch (_) {} }
        function getTblId(tbl, idx) {
            if (tbl.dataset.mdColTbl) return tbl.dataset.mdColTbl;
            var id = tbl.id || (tbl.querySelector('tbody') && tbl.querySelector('tbody').id) || ('md_tbl_' + idx);
            tbl.dataset.mdColTbl = id;
            return id;
        }
        function getColNames(tbl) {
            return Array.from(tbl.querySelectorAll('thead tr:first-child th')).map(function(th, i) {
                var clone = th.cloneNode(true);
                Array.from(clone.querySelectorAll('.resize-handle-left,.resize-handle-right,button')).forEach(function(el){ el.remove(); });
                return (clone.textContent || '').replace(/\s+/g, ' ').trim() || ('عمود ' + (i + 1));
            });
        }
        function applyDynamicHiddenColsCss() {
            var st = document.getElementById('md-dynamic-hidden-cols-css');
            if (!st) { st = document.createElement('style'); st.id = 'md-dynamic-hidden-cols-css'; document.head.appendChild(st); }
            var map = loadMap(), rules = [
                '.table-responsive table.pro-table { width: 100% !important; max-width: 100% !important; min-width: 0 !important; table-layout: fixed !important; }',
                '.table-responsive table.pro-table th, .table-responsive table.pro-table td { padding: 4px 3px !important; font-size: 0.76rem; box-sizing: border-box; }',
                '.table-responsive table.pro-table th { font-size: 0.74rem !important; white-space: nowrap; }',
                '#auto-charts-global-wrapper, .auto-charts-container, [id*="auto-chart"] { display: none !important; }',
                '.md-col-ctrl-bar { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-bottom:8px; padding:6px 12px; border-radius:10px; background:rgba(15,23,42,0.55); border:1px solid rgba(56,189,248,0.28); }',
                'html.light-theme .md-col-ctrl-bar, html[data-theme="light"] .md-col-ctrl-bar { background:var(--lt-card,#f8fafc) !important; border:1.5px solid var(--lt-border,#cbd5e1) !important; color:#000 !important; }',
                '.md-col-ctrl-btn { height:30px; padding:0 11px; border-radius:8px; font-size:0.78rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px; border:1px solid rgba(56,189,248,0.45); background:rgba(56,189,248,0.15); color:#38bdf8; }',
                'html.light-theme .md-col-ctrl-btn, html[data-theme="light"] .md-col-ctrl-btn { background:#ffffff !important; color:#0f172a !important; border:1.5px solid #64748b !important; }'
            ];
            Object.keys(map).forEach(function(tblId) {
                var arr = Array.isArray(map[tblId]) ? map[tblId] : [];
                arr.forEach(function(colIdx1) {
                    var n = parseInt(colIdx1, 10);
                    if (n >= 1) {
                        rules.push('table[data-md-col-tbl="' + tblId + '"] tr > th:nth-child(' + n + '), table[data-md-col-tbl="' + tblId + '"] tr > td:nth-child(' + n + ') { display: none !important; }');
                    }
                });
            });
            st.textContent = rules.join('\n');
            updateAllToolbars();
        }
        function toggleCol(tblId, colIdx1, forceHide) {
            var map = loadMap(), arr = Array.isArray(map[tblId]) ? map[tblId].slice() : [];
            var tbl = document.querySelector('table[data-md-col-tbl="' + tblId + '"]');
            var totalCols = tbl ? tbl.querySelectorAll('thead tr:first-child th').length : 10;
            var pos = arr.indexOf(colIdx1);
            var shouldHide = (typeof forceHide === 'boolean') ? forceHide : (pos === -1);
            if (shouldHide && pos === -1) {
                if (arr.length >= totalCols - 1) { if (typeof showToast === 'function') showToast('⚠️ يجب إبقاء عمود واحد ظاهر على الأقل', 'warning'); return; }
                arr.push(colIdx1);
            } else if (!shouldHide && pos > -1) {
                arr.splice(pos, 1);
            }
            map[tblId] = arr;
            saveMap(map);
            applyDynamicHiddenColsCss();
        }
        window.toggleMdTableCompactMode = function(tblId) {
            var tbl = document.querySelector('table[data-md-col-tbl="' + tblId + '"]');
            if (!tbl) return;
            var names = getColNames(tbl), map = loadMap(), cur = Array.isArray(map[tblId]) ? map[tblId] : [];
            if (cur.length > 0) {
                map[tblId] = [];
                saveMap(map);
                applyDynamicHiddenColsCss();
                if (typeof showToast === 'function') showToast('🔄 تم إظهار كافة أعمدة الجدول', 'info', 1600);
                return;
            }
            var toHide = [];
            names.forEach(function(nm, idx) {
                var c1 = idx + 1;
                if (c1 === 1 || c1 === names.length) return;
                if (COMPACT_KEYWORDS.some(function(k){ return nm.indexOf(k) !== -1; })) toHide.push(c1);
            });
            if (!toHide.length && names.length > 3) toHide.push(names.length - 1);
            map[tblId] = toHide;
            saveMap(map);
            applyDynamicHiddenColsCss();
            if (typeof showToast === 'function') showToast('⚡ تم اختصار الجدول وإخفاء (' + toHide.length + ') أعمدة ثانوية لاحتواء الشاشة', 'success', 1800);
        };
        window.showAllMdTableCols = function(tblId) {
            var map = loadMap();
            map[tblId] = [];
            saveMap(map);
            var tbl = document.querySelector('table[data-md-col-tbl="' + tblId + '"]');
            if (tbl) {
                tbl.style.width = '100%'; tbl.style.minWidth = '0'; tbl.style.tableLayout = 'auto';
                tbl.querySelectorAll('thead th').forEach(function(th){ th.style.width = ''; th.style.minWidth = ''; th.style.maxWidth = ''; });
            }
            applyDynamicHiddenColsCss();
            if (typeof showToast === 'function') showToast('✅ تم إظهار جميع الأعمدة وضبط مقاس الجدول داخل الشاشة', 'success', 1600);
        };
        function openColPopover(tbl, clientX, clientY, clickedCol1, clickedColName) {
            var tblId = getTblId(tbl, 0), names = getColNames(tbl), map = loadMap(), hidden = Array.isArray(map[tblId]) ? map[tblId] : [];
            var pop = document.getElementById('md-col-context-popover');
            if (!pop) {
                pop = document.createElement('div');
                pop.id = 'md-col-context-popover';
                pop.className = 'md-child-modal-card';
                pop.style.cssText = 'position:fixed;z-index:9999999;min-width:265px;max-width:335px;border-radius:14px;padding:10px 12px;box-shadow:0 18px 45px rgba(0,0,0,0.75);font-family:Tajawal,sans-serif;direction:rtl;';
                document.body.appendChild(pop);
                if (typeof window.__makePopupElementDraggable === 'function') window.__makePopupElementDraggable(pop);
            }
            var isLight = document.documentElement.classList.contains('light-theme') || ['light','warm_cream','ultra_white','exec_gray'].indexOf(document.documentElement.getAttribute('data-theme') || '') > -1;
            pop.style.background = isLight ? 'linear-gradient(145deg, var(--lt-card-1, #fdf6ee) 0%, var(--lt-card-2, #f3e2ce) 100%)' : 'linear-gradient(145deg, var(--dk-card-1, #0f172a) 0%, var(--dk-card-2, #152342) 100%)';
            pop.style.border = isLight ? '2px solid var(--lt-border, #4a2106)' : '2px solid var(--dk-border, #38bdf8)';
            pop.style.color = isLight ? 'var(--lt-ink, #140600)' : '#f8fafc';
            var html = '<div class="popup-drag-handle" style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(148,163,184,0.28);padding-bottom:6px;margin-bottom:8px;cursor:grab;user-select:none;" title="اسحب لتحريك النافذة">' +
                '<strong style="font-size:0.86rem;color:' + (isLight ? 'var(--lt-ink, #140600)' : '#fde047') + ';">✋ 👁️ التحكم في أعمدة الجدول</strong>' +
                '<button type="button" onclick="document.getElementById(\'md-col-context-popover\').style.display=\'none\'" style="background:rgba(239,68,68,0.18);border:1px solid rgba(239,68,68,0.4);border-radius:6px;padding:1px 7px;color:inherit;font-weight:900;cursor:pointer;">✕</button></div>';
            if (clickedCol1 >= 1 && clickedColName) {
                var isColHidden = hidden.indexOf(clickedCol1) > -1;
                html += '<div style="display:flex;gap:5px;margin-bottom:6px;">' +
                    '<button type="button" id="md-ctx-quick-explain-btn" style="flex:1;text-align:center;padding:7px 8px;border-radius:8px;border:1.5px solid #38bdf8;background:linear-gradient(135deg,#0284c7,#2563eb);color:#ffffff !important;-webkit-text-fill-color:#ffffff !important;font-weight:900;font-size:0.8rem;cursor:pointer;">📖 زر الشرح</button>' +
                    '<button type="button" id="md-ctx-quick-hide-btn" style="flex:1.2;text-align:center;padding:7px 8px;border-radius:8px;border:1px solid rgba(244,63,94,0.45);background:rgba(244,63,94,0.15);color:' + (isLight ? '#be123c' : '#fda4af') + ';font-weight:900;font-size:0.8rem;cursor:pointer;">' +
                    (isColHidden ? '👁️ إظهار: ' : '🙈 إخفاء: ') + clickedColName + '</button></div>';
            }
            html += '<div style="display:flex;gap:6px;margin-bottom:8px;">' +
                '<button type="button" onclick="window.toggleMdTableCompactMode(\'' + tblId + '\');document.getElementById(\'md-col-context-popover\').style.display=\'none\';" style="flex:1;padding:5px 8px;border-radius:7px;border:1px solid rgba(245,158,11,0.5);background:rgba(245,158,11,0.16);color:' + (isLight ? '#b45309' : '#fde047') + ';font-weight:900;font-size:0.75rem;cursor:pointer;">⚡ اختصار</button>' +
                '<button type="button" onclick="window.showAllMdTableCols(\'' + tblId + '\');document.getElementById(\'md-col-context-popover\').style.display=\'none\';" style="flex:1;padding:5px 8px;border-radius:7px;border:1px solid rgba(16,185,129,0.5);background:rgba(16,185,129,0.16);color:' + (isLight ? '#047857' : '#34d399') + ';font-weight:900;font-size:0.75rem;cursor:pointer;">🔄 إظهار الكل</button></div>' +
                '<div style="max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:4px;">';
            names.forEach(function(nm, idx) {
                var c1 = idx + 1, vis = hidden.indexOf(c1) === -1;
                html += '<label style="display:flex;align-items:center;gap:8px;padding:4px 7px;border-radius:6px;cursor:pointer;font-size:0.8rem;font-weight:800;background:' + (vis ? 'rgba(56,189,248,0.08)' : 'transparent') + ';">' +
                    '<input type="checkbox" data-col-c1="' + c1 + '" ' + (vis ? 'checked' : '') + ' style="width:15px;height:15px;accent-color:#38bdf8;">' +
                    '<span>' + nm + '</span></label>';
            });
            html += '</div>';
            pop.innerHTML = html;
            pop.style.display = 'block';
            var qBtn = pop.querySelector('#md-ctx-quick-hide-btn');
            if (qBtn) qBtn.onclick = function() { toggleCol(tblId, clickedCol1); pop.style.display = 'none'; };
            var expBtn = pop.querySelector('#md-ctx-quick-explain-btn');
            if (expBtn) expBtn.onclick = function() {
                pop.style.display = 'none';
                var thEl = tbl.querySelector('thead tr th:nth-child(' + clickedCol1 + ')') || tbl;
                if (typeof window.openMdExplanationPanelGlobal === 'function') window.openMdExplanationPanelGlobal(thEl, clientX, clientY);
            };
            pop.querySelectorAll('input[type="checkbox"][data-col-c1]').forEach(function(cb) {
                cb.onchange = function() { toggleCol(tblId, parseInt(cb.getAttribute('data-col-c1'), 10), !cb.checked); };
            });
            var left = Math.min(Math.max(10, clientX), window.innerWidth - 280);
            var top = Math.min(Math.max(10, clientY), window.innerHeight - 320);
            pop.style.left = left + 'px';
            pop.style.top = top + 'px';
        }
        window.openMdTableColContextMenu = function(e, cell) {
            var tbl = cell.closest('table');
            if (!tbl) return;
            var tr = cell.parentElement;
            var colIdx1 = tr ? (Array.from(tr.children).indexOf(cell) + 1) : 0;
            var names = getColNames(tbl);
            openColPopover(tbl, e.clientX, e.clientY, colIdx1, names[colIdx1 - 1] || '');
        };
        function updateAllToolbars() {
            var map = loadMap();
            document.querySelectorAll('table.pro-table').forEach(function(tbl, idx) {
                if (tbl.querySelector('#units-matrix-body')) {
                    var oldWrap = tbl.closest('.table-responsive') || tbl.parentElement;
                    if (oldWrap && oldWrap.previousElementSibling && oldWrap.previousElementSibling.classList.contains('md-col-ctrl-bar')) {
                        oldWrap.previousElementSibling.remove();
                    }
                    return;
                }
                var tblId = getTblId(tbl, idx), names = getColNames(tbl);
                if (names.length < 2) return;
                var hidden = Array.isArray(map[tblId]) ? map[tblId] : [];
                var wrap = tbl.closest('.table-responsive') || tbl.parentElement;
                if (!wrap) return;
                var bar = wrap.previousElementSibling;
                if (!bar || !bar.classList.contains('md-col-ctrl-bar')) {
                    bar = document.createElement('div');
                    bar.className = 'md-col-ctrl-bar';
                    wrap.parentNode.insertBefore(bar, wrap);
                }
                var visCount = Math.max(1, names.length - hidden.length);
                bar.innerHTML = '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">' +
                    '<button type="button" class="md-col-ctrl-btn" data-act="menu">👁️ التحكم في الأعمدة (' + visCount + '/' + names.length + ')</button>' +
                    '<button type="button" class="md-col-ctrl-btn" data-act="compact" style="border-color:rgba(245,158,11,0.5);color:#fbbf24;background:rgba(245,158,11,0.14);">' + (hidden.length > 0 ? '🔄 إلغاء الاختصار (' + hidden.length + ' مخفي)' : '⚡ اختصار الجدول') + '</button>' +
                    (hidden.length > 0 ? '<button type="button" class="md-col-ctrl-btn" data-act="showall" style="border-color:rgba(16,185,129,0.5);color:#34d399;background:rgba(16,185,129,0.14);">👁️ إظهار الكل</button>' : '') +
                    '</div>';
                bar.querySelector('[data-act="menu"]').onclick = function(ev) {
                    var r = ev.currentTarget.getBoundingClientRect();
                    openColPopover(tbl, r.left, r.bottom + 6, 0, '');
                };
                bar.querySelector('[data-act="compact"]').onclick = function() { window.toggleMdTableCompactMode(tblId); };
                var sa = bar.querySelector('[data-act="showall"]');
                if (sa) sa.onclick = function() { window.showAllMdTableCols(tblId); };
            });
        }
        document.addEventListener('mousedown', function(e) {
            var pop = document.getElementById('md-col-context-popover');
            if (pop && pop.style.display === 'block' && !pop.contains(e.target) && !e.target.closest('.md-col-ctrl-btn')) pop.style.display = 'none';
        });
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function(){ setTimeout(applyDynamicHiddenColsCss, 80); });
        else setTimeout(applyDynamicHiddenColsCss, 80);
    })();
    </script>
    <style id="md-light-units-inspection-contrast-2027">
        html.light-theme #item-form div[style*="background"],
        html.light-theme #inner-units div[style*="background"],
        html.light-theme #md-c9-inspection-modal .md-card,
        html.light-theme #md-c9-inspection-modal div[style*="background"] {
            background: var(--lt-bg-card, #ffffff) !important;
            border-color: var(--lt-border, #64748b) !important;
            color: #000000 !important;
        }
        html.light-theme #item-form h5, html.light-theme #item-form label, html.light-theme #item-form span:not(.kbd-hint),
        html.light-theme #item-form th, html.light-theme #item-form td,
        html.light-theme #inner-units h3, html.light-theme #inner-units th, html.light-theme #inner-units td, html.light-theme #inner-units span,
        html.light-theme #md-c9-inspection-modal h3, html.light-theme #md-c9-inspection-modal label,
        html.light-theme #md-c9-inspection-modal th, html.light-theme #md-c9-inspection-modal td,
        html.light-theme #md-c9-inspection-modal div, html.light-theme #md-c9-inspection-modal span {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 800 !important;
        }
        html.light-theme #item-form input, html.light-theme #item-form select, html.light-theme #item-form textarea,
        html.light-theme #inner-units input, html.light-theme #inner-units select,
        html.light-theme #md-c9-inspection-modal input, html.light-theme #md-c9-inspection-modal select {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 1.5px solid #334155 !important;
            font-weight: 900 !important;
        }
        html.light-theme #item-form button[onclick*="autoGenerateMissingUnitBarcodes"],
        html.light-theme #tab-items button[onclick*="openBulkPriceUpdaterModal"],
        html.light-theme #tab-items button[onclick*="openSmartClipboardPasteModal"],
        html.light-theme #tab-items #btn-export-items-csv,
        html.light-theme #tab-items #btn-import-items-csv,
        html.light-theme #kpi-total-items-badge, html.light-theme #kpi-total-items-badge * {
            background: #f1f5f9 !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 1.5px solid #475569 !important;
            font-weight: 900 !important;
        }
        /* Royal 3D Kinetic & Luminous Buttons for Column Control, Customer/Supplier Segmented Pills, Default Bank & Toolbars */
        @keyframes mdBtnLightSweep {
            0% { background-position: -150% 0, 0 0; }
            100% { background-position: 250% 0, 0 0; }
        }
        html:not(.light-theme) .md-col-ctrl-bar {
            padding: 8px 14px !important; border-radius: 13px !important;
            background: linear-gradient(135deg, rgba(15,23,42,0.88), rgba(30,41,59,0.92)) !important;
            border: 2px solid rgba(56,189,248,0.55) !important; box-shadow: 0 6px 18px rgba(0,0,0,0.32) !important;
        }
        html.light-theme .md-col-ctrl-bar {
            padding: 8px 14px !important; border-radius: 13px !important;
            background: var(--lt-bg-strip, #e6d7be) !important;
            border: 2px solid var(--lt-border, #854d0e) !important;
            color: #000000 !important;
        }
        .md-col-ctrl-btn, .md-hdr-pill-btn, .md-entity-seg-btn, .entity-default-bank-select,
        #customer-form > div:first-child > h4, #supplier-form > div:first-child > h4 {
            position: relative !important; height: 37px !important; min-height: 37px !important;
            padding: 0 14px !important; border-radius: 11px !important; font-size: 0.84rem !important; font-weight: 900 !important;
            display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important;
            cursor: pointer !important; transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important, opacity 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important, background-color 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important, border-color 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important, color 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.28), inset 0 2px 2px rgba(255,255,255,0.65), inset 0 -4px 8px rgba(0,0,0,0.25) !important;
        }
        .md-col-ctrl-btn[data-act="menu"] {
            background: linear-gradient(135deg, #0284c7, #0369a1) !important; color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important; border: 2px solid #7dd3fc !important;
        }
        .md-col-ctrl-btn[data-act="compact"] {
            background: linear-gradient(135deg, #d97706, #b45309) !important; color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important; border: 2px solid #fde68a !important;
        }
        .md-col-ctrl-btn[data-act="showall"] {
            background: linear-gradient(135deg, #059669, #047857) !important; color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important; border: 2px solid #6ee7b7 !important;
        }
        html:not(.light-theme) .md-entity-seg-group {
            height: 42px !important; padding: 3px 5px !important; border-radius: 13px !important; gap: 5px !important;
            background: linear-gradient(145deg, rgba(15,23,42,0.95), rgba(30,41,59,0.9)) !important;
            border: 2.2px solid rgba(56,189,248,0.6) !important; box-shadow: 0 4px 14px rgba(0,0,0,0.3) !important;
        }
        html.light-theme .md-entity-seg-group {
            height: 42px !important; padding: 3px 5px !important; border-radius: 13px !important; gap: 5px !important;
            background: #ffffff !important;
            border: 2.2px solid var(--lt-border, #334155) !important;
        }
        .md-entity-seg-btn { height: 32px !important; min-height: 32px !important; padding: 0 11px !important; border-radius: 9px !important; }
        #customer-form > div:first-child > h4, #supplier-form > div:first-child > h4 {
            background: linear-gradient(135deg, #0284c7, #1d4ed8) !important; color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important; border: 2px solid #7dd3fc !important; padding: 0 16px !important;
        }
        .md-col-ctrl-btn:hover, .md-hdr-pill-btn:hover, .md-entity-seg-btn:hover, .entity-default-bank-select:hover {
            transform: none !important;
            text-shadow: none !important;
            box-shadow: 0 8px 20px rgba(56,189,248,0.45), 0 0 14px rgba(251,191,36,0.45), inset 0 2px 3px rgba(255,255,255,0.95) !important;
            filter: brightness(1.08) !important;
        }
        .md-col-ctrl-btn:active, .md-hdr-pill-btn:active, .md-entity-seg-btn:active {
            transform: none !important;
        }
    </style>
    <script src="js/md-delayed-tooltips.js?v=<?= @filemtime(__DIR__ . '/js/md-delayed-tooltips.js') ?: '1' ?>"></script>
</body>
</html>
