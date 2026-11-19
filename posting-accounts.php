<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); } ?>
<?php $page_title = 'ترحيل الحسابات'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
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
<script>
(function initScreenMaster13ToneEarly() {
  var ckM = document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/);
  var rawTone = (ckM ? decodeURIComponent(ckM[1]) : '') || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || '';
  var rawTheme = localStorage.getItem('magaies_active_theme') || localStorage.getItem('magaiees_global_theme') || localStorage.getItem('ui_theme') || 'warm_cream';
  var tMap = {dark_midnight:'midnight_obsidian',pure_oled:'pure_oled',mocha_brown:'mocha_brown',slate_twilight:'slate_twilight',ivory_default:'warm_cream',royal_bronze:'warm_cream',platinum_gray:'exec_gray',crystal_silver:'ultra_white',sage_pearl:'warm_cream',sky_alabaster:'ultra_white',rose_blossom:'warm_cream',warm_mocha_dark:'warm_cream',deep_slate_dark:'exec_gray'};
  var activeTone = tMap[rawTone] ? rawTone : (['dark_midnight','pure_oled','mocha_brown','slate_twilight','midnight_obsidian','dark','black','navy'].indexOf(rawTheme) !== -1 ? 'dark_midnight' : 'ivory_default');
  var activeKey = tMap[activeTone] || 'warm_cream';
  var isLight = ['dark_midnight','pure_oled','mocha_brown','slate_twilight'].indexOf(activeTone) === -1;
  document.documentElement.setAttribute('data-light-tone', activeTone);
  document.documentElement.setAttribute('data-theme', activeKey);
  document.documentElement.classList.toggle('light-theme', isLight);
  document.documentElement.classList.toggle('dark-theme', !isLight);
  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) { document.body.setAttribute('data-light-tone', activeTone); document.body.setAttribute('data-theme', activeKey); document.body.classList.toggle('light-theme', isLight); document.body.classList.toggle('dark-theme', !isLight); }
  });
})();
</script>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ترحيل الحسابات | السيطرة (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=<?php echo file_exists('css/components.css') ? filemtime('css/components.css') : 1; ?>">
<link rel="stylesheet" href="css/screens/posting-accounts-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/posting-accounts-s1.css') ?: '2027' ?>">
</head>
<body>

    <div class="toast-container" id="toast-container"></div>

    <!-- Ambient Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="page-wrapper" id="pa-proportional-root">
        <!-- Unified Premium Header -->
        <?php $header_theme_class = 'header-theme-reports'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Filters -->
        <div class="filters-wrapper">
            <div class="filters-top">
                <div class="status-toggle">
                    <button class="status-btn active unposted" id="status-unposted" onclick="setStatus('unposted')">⏳ معلقة</button>
                    <button class="status-btn posted" id="status-posted" onclick="setStatus('posted')">✅ مرحّلة</button>
                </div>
                
                <div class="search-box">
                    <span class="icon">🔍</span>
                    <input type="text" id="search-input" placeholder="بحث ذكي (F2): مرجع، اسم، مبلغ..." oninput="handleFilterChange()">
                </div>
                
                <div class="date-filters">
                    <label style="font-size:0.85rem; font-weight:900;">من:</label>
                    <input type="date" id="date-from" class="date-input" onchange="handleFilterChange()">
                    <label style="font-size:0.85rem; font-weight:900;">إلى:</label>
                    <input type="date" id="date-to" class="date-input" onchange="handleFilterChange()">
                </div>

                <button type="button" onclick="window.openPaQuickAddEntityModal()" style="height:36px; padding:0 12px; border-radius:10px; background:linear-gradient(135deg,#0284c7,#2563eb); border:2.2px solid #fde047; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.84rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>➕</span> <span>إضافة</span>
                </button>
                <button type="button" onclick="if(window.InboundBankakManager){InboundBankakManager.toggleDrawer(true); InboundBankakManager.setFilter('awaiting_approval');}" style="height:36px; padding:0 12px; border-radius:10px; background:linear-gradient(135deg,#059669,#0d9488); border:2.2px solid #34d399; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.84rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>📥</span> <span>إشعارات واتساب</span>
                </button>
                <button type="button" onclick="if(window.openPostingAIAuditModal) window.openPostingAIAuditModal()" style="height:36px; padding:0 12px; border-radius:10px; background:linear-gradient(135deg, #7c3aed, #2563eb); border:2.2px solid #fde047; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.84rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🤖</span> <span>فحص AI</span>
                </button>
                <button type="button" id="pa-c1-appearance-btn" onclick="window.openPaC1TonePopover(this)" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#b45309,#f59e0b); border:2.2px solid #fde047; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.83rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🎨</span> <span>المظهر</span>
                </button>
                <button type="button" id="pa-c1-guide-btn" onclick="window.openPaC1SmartGuide()" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0d9488,#0284c7); border:2.2px solid #fde047; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.83rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🧭</span> <span>المرشد</span>
                </button>
                <button type="button" onclick="window.openPaC1NestedChildModal(1)" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#1e40af,#0f766e); border:2.2px solid #38bdf8; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🪟</span> <span>الشاشات الوليدة</span>
                </button>
                <button type="button" id="pa-c9-inspect-btn" onclick="window.openPaWarehouseInspectAndSortModal()" title="فحص واستلام البضاعة والفرز اللحظي (سليم / تالف / مرفوض) والتحويل المخزني" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0f766e,#0284c7); border:2.2px solid #38bdf8; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🔬</span> <span>فحص واستلام</span>
                </button>
                <button type="button" id="pa-c10-landed-btn" onclick="window.openPaLandedCostModal()" title="توزيع المصروفات المضافة (نقل، شحن، عتالة، رسوم) على تكلفة الوحدة" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#7c3aed,#4f46e5); border:2.2px solid #c4b5fd; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🚢</span> <span>المصروفات المضافة</span>
                </button>
                <button type="button" id="pa-c10-lifecycle-btn" onclick="window.openPaDocLifecycleModal()" title="تحويل المستندات وتوليد إذن المخزن وسند القبض/الصرف بضغطة زر" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0284c7,#0f766e); border:2.2px solid #7dd3fc; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🔄</span> <span>الدورة المستندية</span>
                </button>
                <button type="button" id="pa-c10-return-btn" onclick="window.openPaReturnReverseModal()" title="إدارة المرتجعات الكلية والجزئية وتوليد القيد العكسي الآلي" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#e11d48,#be123c); border:2.2px solid #fda4af; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>↩️</span> <span>مرتجع وقيد عكسي</span>
                </button>
                <button type="button" id="pa-c11-cheque-btn" onclick="window.openPaChequeLifecycleModal()" title="حافظة ودورة الشيكات الكاملة من الألف للياء (في الحافظة / تحت التحصيل / محصل / مرتد / مظهر)" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0f766e,#0284c7); border:2.2px solid #6ee7b7; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🏦</span> <span>دورة الشيكات</span>
                </button>
                <button type="button" id="pa-c11-bankguard-btn" onclick="window.openPaBankGuardAndPeriodModal()" title="رقابة التحويلات البنكية ومنع تكرار رقم الإشعار للاختلاس وإقفال الفترات المالية" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#b45309,#d97706); border:2.2px solid #fde047; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🛡️</span> <span>رقابة البنوك وإقفال الفترة</span>
                </button>
                <button type="button" id="pa-c12-printlock-btn" onclick="window.openPaForensicPrintModal()" title="قفل الطباعة الصارم والعلامة المائية لبدل الفاقد وباركود الصرف ذو الاستعمال الواحد" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#1e3a8a,#3b82f6); border:2.2px solid #93c5fd; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🖨️</span> <span>قفل الطباعة والباركود</span>
                </button>
                <button type="button" id="pa-c12-blindcash-btn" onclick="window.openPaBlindCashClosingModal()" title="نظام الجرد الأعمى عند تسليم الخزينة والوردية وتقييد العجز على الموظف" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#881337,#e11d48); border:2.2px solid #fda4af; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🕵️</span> <span>الجرد الأعمى للوردية</span>
                </button>
                <button type="button" id="pa-c13-blackbox-btn" onclick="window.openPaCyberBlackBoxAndRadarModal()" title="الصندوق الأسود الرقابي غير القابل للمسح ولوحة التنبيهات الأمنية السرية للمالك (🟡/🟠/🔴)" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0f172a,#334155); border:2.2px solid #f43f5e; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>⬛</span> <span>الصندوق الأسود ورادار المالك</span>
                </button>
                <button type="button" id="pa-c13-pinlock-btn" onclick="window.triggerPaQuickPinLock()" title="قفل الخمول السريع بكلمة سر PIN من 4 أرقام لمنع انتحال الشخصية (F12)" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#4c1d95,#7c3aed); border:2.2px solid #ddd6fe; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🔐</span> <span>قفل PIN</span> <kbd class="pa-kbd">F12</kbd>
                </button>
                <button type="button" id="pa-c14-reports-btn" onclick="window.openPaWorldReportAndPrintHubModal()" title="التقارير العالمية، الرصيد التراكمي، التعمق بضغطة زر (Drill-Down)، الطباعة الموزونة (A4/80mm)، والتصدير" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#047857,#0d9488); border:2.2px solid #6ee7b7; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>📊</span> <span>التقارير والطباعة (A4/80mm)</span>
                </button>
                <button type="button" id="pa-c15-master-btn" onclick="window.openPaC15MasterAuditModal()" title="بوابة المراجعة الختامية للدساتير الـ 15، الروابط الذرية المترابطة، ومعاينة أثر ميزان المراجعة" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#9333ea,#d97706); border:2.2px solid #fde047; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🏆</span> <span>بوابة الدساتير الـ 15 والروابط</span>
                </button>
                <button type="button" id="pa-fc1-ai-btn" onclick="window.openPaSupremeAICopilotModal()" title="الذكاء الاصطناعي الهجين الفائق (0ms): الإدخال باللغة الطبيعية صوتاً أو كتابةً، قارئ الإشعارات AI OCR، واقتراح الخطوة التالية" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#6d28d9,#ec4899); border:2.2px solid #f9a8d4; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🧠✨</span> <span>الذكاء الشامل واللغة الطبيعية</span>
                </button>
                <button type="button" id="pa-fc1-dualui-btn" onclick="window.openPaShortcutMatrixModal()" title="البوابة المزدوجة (الواجهة العصرية ↔ الكلاسيكية) ومصفوفة أزرار الاختصارات (1، 2، 3، 4) مع قفل حبس الاختصار" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0369a1,#1d4ed8); border:2.2px solid #93c5fd; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🎛️</span> <span>الواجهتان والاختصارات (1-4)</span>
                </button>
                <button type="button" id="pa-fc1-split-btn" onclick="window.togglePaDualSplitWorkspace()" title="نظام النصفين (يمين وشمال) ووضع ملء الشاشة (100dvh) للموبايل بالعرض" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0f766e,#059669); border:2.2px solid #6ee7b7; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>📱⚖️</span> <span>النصفين (يمين/شمال)</span>
                </button>
                <button type="button" id="pa-fc1-gestures-btn" onclick="window.openPaGestureControlAndGuideModal()" title="مفتاح تفعيل/إيقاف إيماءات الأصابع للشاشات المرتجة وإخفاء الأرصدة (****)" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#b45309,#d97706); border:2.2px solid #fde047; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>✋👁️</span> <span>الإيماءات وإخفاء (****)</span>
                </button>
                <button type="button" id="pa-fc2-insights-btn" onclick="window.openPaAIExecutiveInsightsModal()" title="الذكاء الاصطناعي لتحليل النتائج والتقارير، التنبؤ المستقبلي، والاستعلام الذكي" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0f766e,#1d4ed8); border:2.2px solid #7dd3fc; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>📈🤖</span> <span>المحلل المالي الذكي والتنبؤات</span>
                </button>
                <button type="button" id="pa-fc2-biometric-btn" onclick="window.openPaBiometricAndReceiptCardModal()" title="البصمة البيومترية، ضغط الإشعارات 40KB، حارس تصوير الشاشة، وتوليد بطاقة إيصال مصورة للواتساب" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#312e81,#7c3aed); border:2.2px solid #c4b5fd; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🧬🖼️</span> <span>البصمة وبطاقة الواتساب (40KB)</span>
                </button>
                <button type="button" onclick="window.undoLastPaPostingAction()" title="التراجع الفوري عن آخر ترحيل (Ctrl+Z)" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#475569,#334155); border:2.2px solid #cbd5e1; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>↩️</span> <span>تراجع</span> <kbd class="pa-kbd">Ctrl+Z</kbd>
                </button>
                <button type="button" onclick="window.parkCurrentPaSelection()" title="تعليق المراجعة الحالية مؤقتاً بضغطة زر" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#d97706,#f59e0b); border:2.2px solid #fde047; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>⏸️</span> <span>تعليق مؤقت</span>
                </button>
                <button type="button" onclick="window.resetPaFiltersAndSearch()" title="تصفير الفلاتر والبحث" style="height:36px; padding:0 11px; border-radius:10px; background:linear-gradient(135deg,#0f766e,#047857); border:2.2px solid #6ee7b7; color:#ffffff !important; font-family:'Tajawal',sans-serif; font-size:0.82rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <span>🆕</span> <span>تصفير</span>
                </button>
                <span id="pa-c7-conn-led" style="padding:4px 9px; border-radius:99px; border:2px solid #10b981; background:rgba(16,185,129,0.16); font-size:0.76rem; font-weight:900;">🟢 متصل ومؤمّن</span>
                <div style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:10px; border:2.2px solid #f59e0b; background:rgba(245,158,11,0.14);" title="Ctrl + رولة الماوس للتكبير والتصغير التلسكوبي حتى 10 أضعاف (0.5x إلى 10x)">
                    <span id="pa-c1-zoom-badge" onclick="window.applyPaC1ProportionalZoom(1.0)" style="cursor:pointer; font-size:0.78rem; font-weight:900;">📐 أبعاد (6:8) 100%</span>
                    <button type="button" onclick="window.applyPaC1ProportionalZoom(0.75)" style="padding:2px 6px; border-radius:6px; border:1.5px solid currentColor; background:transparent; color:inherit; font-weight:900; cursor:pointer; font-size:0.74rem;">0.75x</button>
                    <button type="button" onclick="window.applyPaC1ProportionalZoom(1.0)" style="padding:2px 6px; border-radius:6px; border:1.5px solid currentColor; background:transparent; color:inherit; font-weight:900; cursor:pointer; font-size:0.74rem;">1x</button>
                    <button type="button" onclick="window.applyPaC1ProportionalZoom(2.0)" style="padding:2px 6px; border-radius:6px; border:1.5px solid currentColor; background:transparent; color:inherit; font-weight:900; cursor:pointer; font-size:0.74rem;">2x</button>
                    <button type="button" onclick="window.applyPaC1ProportionalZoom(5.0)" style="padding:2px 6px; border-radius:6px; border:1.5px solid currentColor; background:transparent; color:inherit; font-weight:900; cursor:pointer; font-size:0.74rem;">5x</button>
                    <button type="button" onclick="window.applyPaC1ProportionalZoom(10.0)" style="padding:2px 6px; border-radius:6px; border:1.5px solid currentColor; background:transparent; color:inherit; font-weight:900; cursor:pointer; font-size:0.74rem;">10x</button>
                </div>
            </div>
            <div class="type-filters" id="type-filters" style="justify-content:space-between; flex-wrap:wrap;">
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <button class="type-chip active" id="type-all" onclick="setType('all')">الكل</button>
                    <button class="type-chip" id="type-whatsapp" onclick="setType('whatsapp')" style="border-color:rgba(16,185,129,0.45); color:#34d399;">📲 إشعارات واتساب</button>
                    <button class="type-chip" id="type-sales" onclick="setType('sales')">مبيعات</button>
                    <button class="type-chip" id="type-purchases" onclick="setType('purchases')">مشتريات</button>
                    <button class="type-chip" id="type-receipt" onclick="setType('receipt')">سندات قبض</button>
                    <button class="type-chip" id="type-payment" onclick="setType('payment')">سندات صرف</button>
                    <button class="type-chip" id="type-journal" onclick="setType('journal')">قيود يومية</button>
                </div>
                <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                    <a href="vouchers-hub.php" style="text-decoration:none; padding:4px 10px; border-radius:8px; background:rgba(59,130,246,0.14); border:2px solid rgba(59,130,246,0.65); color:#60a5fa; font-size:0.78rem; font-weight:900;">💰 مركز السندات</a>
                    <a href="journal.php" style="text-decoration:none; padding:4px 10px; border-radius:8px; background:rgba(168,85,247,0.14); border:2px solid rgba(168,85,247,0.65); color:#c084fc; font-size:0.78rem; font-weight:900;">📒 القيود اليومية</a>
                    <a href="general-ledger.php" style="text-decoration:none; padding:4px 10px; border-radius:8px; background:rgba(56,189,248,0.14); border:2px solid rgba(56,189,248,0.65); color:#38bdf8; font-size:0.78rem; font-weight:900;">📖 دفتر الأستاذ</a>
                    <a href="checks-cycle.php" style="text-decoration:none; padding:4px 10px; border-radius:8px; background:rgba(245,158,11,0.14); border:2px solid rgba(245,158,11,0.65); color:#fbbf24; font-size:0.78rem; font-weight:900;">💳 دورة الشيكات</a>
                </div>
            </div>
            <!-- شريط رادار توازن الدفعة والملخص المالي اللحظي (إضافة عالمية معتمدة - الدستور 1) -->
            <div id="pa-c1-kpi-strip" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(175px, 1fr)); gap:8px; padding-top:8px; border-top:2px dashed rgba(148,163,184,0.35);">
                <div class="pa-c1-card" style="padding:8px 12px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                    <span>⏳ معلقة للترحيل:</span>
                    <strong id="pa-kpi-unposted-count" style="font-size:1.05rem;">0</strong>
                </div>
                <div class="pa-c1-card" style="padding:8px 12px; border-radius:10px; display:flex; flex-direction:column; gap:2px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span>💰 إجمالي المعلق:</span>
                        <strong id="pa-kpi-unposted-sum" class="pa-c1-no-trunc" style="font-size:1.02rem;">0.00</strong>
                    </div>
                    <div id="pa-kpi-unposted-tafqeet" style="font-size:0.74rem; color:#10b981; font-weight:900;">فقط صفر جنيه سوداني لا غير</div>
                </div>
                <div class="pa-c1-card" style="padding:8px 12px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                    <span>✅ مكتملة الترحيل:</span>
                    <strong id="pa-kpi-posted-count" style="font-size:1.05rem;">0</strong>
                </div>
                <div class="pa-c1-card" style="padding:8px 12px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                    <span>⚖️ فحص القيد المزدوج:</span>
                    <strong id="pa-kpi-balance-seal" style="font-size:0.88rem; color:#10b981;">متوازن 100% (زيرو فرق)</strong>
                </div>
                <div class="pa-c1-card" onclick="window.triggerPaManualFlashSync()" style="padding:8px 12px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; cursor:pointer;" title="اضغط للمزامنة الفورية واختبار محرك 100 ألف سجل">
                    <span>⚡ التزامن (CRDT 100K):</span>
                    <strong id="pa-kpi-crdt-sync" style="font-size:0.84rem; color:#10b981;">متزامن 0ms (جوال↔لابتوب)</strong>
                </div>
            </div>
            <div id="pa-parked-batches-bar" style="display:none; align-items:center; gap:6px; flex-wrap:wrap; padding:6px 10px; border-radius:10px; border:2px dashed #f59e0b; background:rgba(245,158,11,0.12);"></div>
        </div>

        <!-- Split Layout -->
        <div class="split-layout">
            
            <!-- Master Pane (Right) -->
            <div class="master-pane">
                <div class="table-scroll">
                    <table class="erp-table" id="data-table" style="display: none;">
                        <thead>
                            <tr onclick="resetSelection()" style="pointer-events: auto; cursor: pointer;" title="اضغط هنا لإلغاء التحديد وتكبير الجدول">
                                <th style="width: 40px; text-align: center;" onclick="event.stopPropagation()">
                                    <input type="checkbox" id="check-all-rows" onchange="toggleAllRows(this)" onclick="event.stopPropagation()" title="تحديد الكل">
                                </th>
                                <th style="width: 100px;">النوع</th>
                                <th style="width: 100px;">التاريخ</th>
                                <th style="width: 130px;">رقم المرجع</th>
                                <th>العميل / المورد / البيان</th>
                                <th class="extra-column" style="width: 200px;">الصنف</th>
                                <th class="extra-column" style="width: 70px; text-align: center;">الكمية</th>
                                <th class="extra-column" style="width: 100px; text-align: left;">سعر الوحدة</th>
                                <th style="width: 110px;">الاستحقاق</th>
                                <th style="width: 110px;">طريقة الدفع</th>
                                <th style="width: 140px; text-align: left;">المبلغ الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Rows injected via JS -->
                        </tbody>
                    </table>

                    <div id="no-data-msg" style="display: none; text-align: center; padding: 40px 20px; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="font-size: 3rem; margin-bottom: 12px;">📭</div>
                        <h2 style="font-size: 1.2rem; margin: 0 0 14px 0;">لا توجد عمليات مطابقة</h2>
                        <button onclick="injectDummyData()" style="background: rgba(59,130,246,0.18); color: #60a5fa; border: 2.2px solid #3b82f6; padding: 8px 18px; border-radius: 10px; cursor: pointer; font-family: 'Tajawal', sans-serif; font-weight: 900; font-size: 0.9rem;">
                            ➕ بيانات تجريبية
                        </button>
                    </div>
                </div>

                <!-- Sticky Action Bar for Master Pane -->
                <div class="sticky-action-bar" id="sticky-bar">
                    <div style="font-size: 0.95rem; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                        المحدد: <span class="selected-count" id="selected-count">0</span>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <button class="btn-post-batch pa-dna-btn pa-dna-save" onclick="postAllChecked()">
                            <span>✔️</span> <span>ترحيل المحدد</span> <kbd class="pa-kbd">F10</kbd>
                        </button>
                        <button type="button" class="pa-dna-btn pa-dna-delete" onclick="resetSelection()">
                            <span>✖</span> <span>إلغاء التحديد</span> <kbd class="pa-kbd">Esc</kbd>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Detail Pane (Left) -->
            <div class="detail-pane" id="detail-pane">
                <div class="empty-detail" id="empty-detail">
                    <div class="empty-detail-icon">📑</div>
                    <h3 style="margin-bottom:6px; font-size: 1.2rem;">اختر عملية للمراجعة</h3>
                </div>
                
                <div id="detail-content" class="detail-inner" style="display: none;">
                    <!-- Content injected via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ CUSTOM CONFIRM & POSTING ANIMATION MODAL ═══ -->
    <div id="custom-confirm-modal" class="modal-overlay">
        <div class="modal-box">
            <!-- Close Button -->
            <button class="modal-close" onclick="closeConfirmModal()">✖️</button>
            
            <!-- Icon/Title -->
            <div class="modal-header">
                <div class="modal-icon">📋</div>
                <h3 class="modal-title" id="modal-title">تأكيد ترحيل الحسابات</h3>
            </div>
            
            <!-- Body Content -->
            <div class="modal-body" id="modal-body">
                <!-- Filled dynamically -->
            </div>
            
            <!-- Actions -->
            <div class="modal-actions" id="modal-actions" style="display:flex; justify-content:space-between; gap:10px;">
                <button class="modal-btn confirm pa-dna-btn pa-dna-save" id="modal-btn-confirm"><span>✔️</span> <span>تأكيد واعتماد الترحيل</span> <kbd class="pa-kbd">F10</kbd></button>
                <button class="modal-btn cancel pa-dna-btn pa-dna-delete" onclick="closeConfirmModal()"><span>✖</span> <span>تراجع وإلغاء</span> <kbd class="pa-kbd">Esc</kbd></button>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : 1; ?>"></script>
    <script defer src="js/posting-accounts.js?v=<?php echo file_exists('js/posting-accounts.js') ? filemtime('js/posting-accounts.js') : 1; ?>"></script>
    <script defer src="js/elsajana-universal-ui-2027.js?v=<?php echo file_exists('js/elsajana-universal-ui-2027.js') ? filemtime('js/elsajana-universal-ui-2027.js') : 1; ?>"></script>
    <script>
        function injectDummyData() {
            const dummySales = [{
                id: 'INV-1001', invoiceNo: 'INV-1001', date: new Date().toISOString().split('T')[0],
                customerId: 'CUST-001', customerName: 'شركة الآفاق الحديثة',
                totalAmount: 12500, paymentMethod: 'credit', paymentAccountId: '',
                dueDate: '2026-07-15', description: 'مبيعات آجلة - توريد أجهزة مكتبية', isPosted: false,
                items: [
                    { itemName: 'حاسب آلي مكتبي', qty: 2, price: 4000, total: 8000 },
                    { itemName: 'طابعة ليزر', qty: 3, price: 1500, total: 4500 }
                ]
            }];
            const dummyPayment = [{
                id: 'PAY-205', voucherNo: 'PAY-205', date: new Date().toISOString().split('T')[0],
                toAccount: 'exp-rent', fromAccount: 'bank-002',
                amountLocal: 4000, toStatement: 'دفع إيجار المقر بشيك', isPosted: false,
                paymentMethod: 'cheque', dueDate: '2026-06-30',
                description: 'سداد الإيجار الشهري عبر شيك بنكي'
            }];
            localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(dummySales));
            localStorage.setItem('erp_single_payments_v1', JSON.stringify(dummyPayment));
            if (window.showToast) window.showToast('تم حقن الفاتورة والسند التجريبي بنجاح!', 'success');
            setTimeout(() => location.reload(), 600);
        }

        /* محرك الدستور الأول (1 من 14) لشاشة ترحيل الحسابات */
        (function initPostingAccountsConstitution1() {
            if (document.documentElement.dataset.paC1Applied === '1') return;
            document.documentElement.dataset.paC1Applied = '1';

            const PA_TONES = [
                { id: 'dark_midnight', name: 'الداكن الملكي الأصلي', themeKey: 'midnight_obsidian', isDark: true, bg: '#010308', card: '#152342' },
                { id: 'pure_oled', name: 'الأسود الفحمي (OLED)', themeKey: 'pure_oled', isDark: true, bg: '#000000', card: '#0d1322' },
                { id: 'mocha_brown', name: 'البني الموكا الفخم', themeKey: 'mocha_brown', isDark: true, bg: '#1a0f0a', card: '#2b1b12' },
                { id: 'slate_twilight', name: 'الكحلي التنفيذي', themeKey: 'slate_twilight', isDark: true, bg: '#0d1526', card: '#192642' },
                { id: 'ivory_default', name: 'العاجي الذهبي الدافئ', themeKey: 'warm_cream', isDark: false, bg: '#f6f1e7', card: '#fbf8f1' },
                { id: 'royal_bronze', name: 'الرملي البرونزي', themeKey: 'warm_cream', isDark: false, bg: '#ecdec8', card: '#f7efe2' },
                { id: 'platinum_gray', name: 'البلاتينيوم الرمادي', themeKey: 'exec_gray', isDark: false, bg: '#e2e8f0', card: '#f1f5f9' },
                { id: 'crystal_silver', name: 'الفضي الكريستالي', themeKey: 'ultra_white', isDark: false, bg: '#f1f5f9', card: '#ffffff' },
                { id: 'sage_pearl', name: 'اللؤلؤي الأخضر الهادئ', themeKey: 'warm_cream', isDark: false, bg: '#dfece6', card: '#eef6f2' },
                { id: 'sky_alabaster', name: 'الرخامي السماوي', themeKey: 'ultra_white', isDark: false, bg: '#e0eef9', card: '#f0f7fd' },
                { id: 'warm_mocha_dark', name: 'الكاكاو الدافئ', themeKey: 'warm_cream', isDark: false, bg: '#dfd1c5', card: '#efe6de' },
                { id: 'deep_slate_dark', name: 'الرصاصي الداكن المريح', themeKey: 'exec_gray', isDark: false, bg: '#cbd5e1', card: '#e2e8f0' }
            ];
            const bc = typeof BroadcastChannel !== 'undefined' ? new BroadcastChannel('elsajana_global_appearance_viewport_sync') : null;
            if (bc) {
                bc.onmessage = function(ev) {
                    if (!ev || !ev.data) return;
                    if (ev.data.type === 'TONE_SYNC' && ev.data.toneId) window.applyPaC1Tone(ev.data.toneId, true);
                    if (ev.data.type === 'ZOOM_SYNC' && ev.data.scale) window.applyPaC1ProportionalZoom(ev.data.scale, true);
                };
            }
            window.applyPaC1Tone = function(toneId, fromRemote) {
                if (window.ElsajanaUniversalUI2027 && typeof window.ElsajanaUniversalUI2027.applyUnifiedSystemTone === 'function') {
                    window.ElsajanaUniversalUI2027.applyUnifiedSystemTone(toneId, !fromRemote);
                } else {
                    const t = PA_TONES.find(function(x) { return x.id === toneId || x.themeKey === toneId; }) || PA_TONES[0];
                    document.documentElement.setAttribute('data-theme', t.themeKey);
                    document.documentElement.setAttribute('data-light-tone', t.id);
                    document.documentElement.classList.toggle('light-theme', !t.isDark);
                }
                if (!fromRemote && bc) { try { bc.postMessage({ type: 'TONE_SYNC', toneId: toneId }); } catch (e) {} }
            };
            window.openPaC1TonePopover = function(btn) {
                if (window.ElsajanaUniversalUI2027 && typeof window.ElsajanaUniversalUI2027.openUniversalErpTonePopover === 'function') {
                    return window.ElsajanaUniversalUI2027.openUniversalErpTonePopover(btn);
                }
            };
            window.applyPaC1ProportionalZoom = function(rawScale, fromRemote) {
                let s = Math.max(0.5, Math.min(10.0, Math.round((parseFloat(rawScale) || 1.0) * 100) / 100));
                if (window.ElsajanaUniversalUI2027 && typeof window.ElsajanaUniversalUI2027.setTelescopicZoom === 'function') {
                    window.ElsajanaUniversalUI2027.setTelescopicZoom(s);
                } else {
                    const stg = document.getElementById('pa-zoom-stage');
                    if (stg) stg.style.zoom = String(s);
                    const bdg = document.getElementById('pa-c1-zoom-badge');
                    if (bdg) bdg.textContent = '📐 أبعاد (6:8) ' + Math.round(s * 100) + '%';
                }
                try { localStorage.setItem('magaies_telescopic_zoom_scale', String(s)); localStorage.setItem('erp_pa_c15_zoom_scale', String(s)); } catch (e) {}
                if (!fromRemote && bc) { try { bc.postMessage({ type: 'ZOOM_SYNC', scale: s }); } catch (e) {} }
            };
            window.addEventListener('DOMContentLoaded', function() {
                const savedZoom = parseFloat(localStorage.getItem('magaies_telescopic_zoom_scale') || localStorage.getItem('erp_pa_c15_zoom_scale') || '1') || 1.0;
                if (Math.abs(savedZoom - 1.0) >= 0.01) window.applyPaC1ProportionalZoom(savedZoom, true);
            });
        })();
    </script>
    <?php include_once 'includes/inbound-bankak-drawer.php'; ?>
<script src="js/screens/posting-accounts-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/posting-accounts-j1.js') ?: '2027' ?>"></script>
</body>
</html>

