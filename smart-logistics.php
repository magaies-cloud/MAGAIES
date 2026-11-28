<?php
if (!headers_sent()) { header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'); header('Pragma: no-cache'); }
if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); }
 $page_title = 'بوابة الاستلام والتسليم الذكية الموحدة — الأيقونة اللوجستية الشاملة (GRN ⇄ GDN)'; ?>
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
    var getCk = function(n) { var m = document.cookie.match(new RegExp('(?:^|; )' + n + '=([^;]*)')); return m ? decodeURIComponent(m[1]) : ''; };
    var t = localStorage.getItem('magaies_active_theme') || localStorage.getItem('magaiees_global_theme') || getCk('magaies_active_theme') || 'warm_cream';
    var tone = localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || getCk('magaies_light_tone') || 'ivory_default';
    var isLight = ['warm_cream','ultra_white','exec_gray','light'].indexOf(t) !== -1 || localStorage.getItem('erp_theme') === 'light';
    document.documentElement.setAttribute('data-theme', t);
    document.documentElement.setAttribute('data-light-tone', tone);
    document.documentElement.classList.toggle('light-theme', isLight);
    document.documentElement.classList.toggle('dark-theme', !isLight);
})();
</script>
<head>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة الاستلام والتسليم الذكية الموحدة (GRN ⇄ GDN) — ELSAJANA ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/print-invoice.css">
    <style>
        /* ═══════════════════════════════════════════════════════════════════════════
           REFINED MATTE-SATIN LOGISTICS ENGINE (بوابة الاستلام والتسليم الموحدة — بدون انعكاسات مزعجة)
           Solid, rich, coordinated colors with calm borders and zero background light glare
           ═══════════════════════════════════════════════════════════════════════════ */
        :root, body[data-logistics-mode="sales"] {
            --sl-primary: #10b981;
            --sl-primary-dark: #059669;
            --sl-primary-light: #34d399;
            --sl-primary-soft: rgba(16, 185, 129, 0.14);
            --sl-primary-glow: rgba(16, 185, 129, 0.22);
            --sl-accent: #fbbf24;
            --sl-accent-soft: rgba(245, 158, 11, 0.14);
            --sl-secondary: #38bdf8;
            --sl-border-glow: rgba(52, 211, 153, 0.38);
            --sl-card-bg: linear-gradient(180deg, #0b1f1a 0%, #071512 100%);
            --sl-header-grad: linear-gradient(135deg, #0d2922 0%, #091d18 55%, #0b241e 100%);
            --sl-unit-box-bg: #0a221c;
            --sl-grid-font-size: 0.96rem;
            --sl-grid-font-color: #f8fafc;
        }

        body[data-logistics-mode="purchases"] {
            --sl-primary: #0ea5e9;
            --sl-primary-dark: #0284c7;
            --sl-primary-light: #38bdf8;
            --sl-primary-soft: rgba(14, 165, 233, 0.14);
            --sl-primary-glow: rgba(14, 165, 233, 0.20);
            --sl-accent: #a855f7;
            --sl-accent-soft: rgba(168, 85, 247, 0.14);
            --sl-secondary: #818cf8;
            --sl-border-glow: rgba(56, 189, 248, 0.36);
            --sl-card-bg: linear-gradient(180deg, #0f1d32 0%, #0a1424 100%);
            --sl-header-grad: linear-gradient(135deg, #10243e 0%, #0c1b30 55%, #12213a 100%);
            --sl-unit-box-bg: #0d1c31;
        }

        html {
            overflow-y: scroll !important;
            overflow-x: hidden !important;
            height: auto !important;
            min-height: 100% !important;
            overscroll-behavior: auto !important;
            scroll-behavior: auto !important;
        }

        body {
            /* CRITICAL: Keep overflow visible on BOTH axes on body so body never becomes a 0-scroll trap */
            overflow: visible !important;
            overflow-x: visible !important;
            overflow-y: visible !important;
            overscroll-behavior: auto !important;
            height: auto !important;
            min-height: 100vh !important;
            max-height: none !important;
            position: static !important;
            padding-bottom: 140px !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            background-color: #070f1d !important;
            background-image: none !important;
            color: #f8fafc;
            transition: background-color 0.25s ease;
        }

        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        ::-webkit-scrollbar-track {
            background: #070f1d;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--sl-primary-light), var(--sl-secondary));
            border-radius: 10px;
            border: 2px solid #070f1d;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--sl-accent);
        }

        .ambient-bg { display: none !important; }

        /* ── SOLID MATTE-SATIN CARDS (NO TRANSPARENT GLARE OR BACKGROUND REFLECTIONS) ── */
        .sl-glass-card {
            position: relative;
            background: var(--sl-card-bg) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border: 1.5px solid #1e3352;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.42);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            overflow: visible !important;
        }
        .sl-glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--sl-primary), transparent);
            opacity: 0.22;
            pointer-events: none;
        }
        .sl-glass-card:hover {
            border-color: var(--sl-border-glow);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.48);
        }

        /* ── SOLID MATTE BUTTONS (NO GLASS SHIMMER FLASH OR NEON GLARE) ── */
        .sl-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 10px;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-weight: 800;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 1.5px solid #294366;
            background: #13233b;
            color: #f8fafc;
            padding: 8px 16px;
            font-size: 0.86rem;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.28);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
            user-select: none;
        }
        .sl-btn::after {
            display: none !important;
        }
        .sl-btn:hover:not(:disabled) {
            transform: translateY(-1.5px);
            border-color: var(--sl-primary-light) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.38);
        }
        .sl-btn:active:not(:disabled) {
            transform: translateY(0) scale(0.98);
        }
        .sl-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        /* ── GIANT DUAL-MODE COCKPIT SWITCHER (المبيعات 📤 ⇄ المشتريات 📥) — COMPACT & SOLID ── */
        .sl-mode-switcher {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 4px;
            background: #091324;
            border-radius: 14px;
            border: 1.5px solid #1e3352;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
        }
        .sl-mode-tab {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 14px;
            border-radius: 11px;
            cursor: pointer;
            border: 1.5px solid transparent;
            background: #0f1d33;
            transition: transform 0.22s ease, opacity 0.22s ease, background-color 0.22s ease, border-color 0.22s ease, color 0.22s ease;
        }
        .sl-mode-tab::after {
            display: none !important;
        }
        .sl-mode-tab.mode-sales.active {
            background: linear-gradient(135deg, #064e3b, #047857);
            border-color: #34d399;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
        }
        .sl-mode-tab.mode-purchases.active {
            background: linear-gradient(135deg, #0f385a, #1e295b);
            border-color: #38bdf8;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
        }
        .sl-mode-tab:not(.active) {
            opacity: 0.78;
        }
        .sl-mode-tab:not(.active):hover {
            opacity: 1;
            border-color: #334155;
        }

        /* ── STRICT SINGLE-MODE ISOLATION (فصل بوابة المشتريات الزرقاء عن بوابة المبيعات الخضراء) ── */
        body[data-logistics-mode="purchases"] #sl-tab-sales,
        body[data-logistics-mode="purchases"] #sl-classic-sales-btn {
            display: none !important;
        }
        body[data-logistics-mode="sales"] #sl-tab-purchases,
        body[data-logistics-mode="sales"] #sl-classic-purchases-btn {
            display: none !important;
        }
        body[data-logistics-mode="purchases"] .sl-mode-switcher,
        body[data-logistics-mode="sales"] .sl-mode-switcher {
            grid-template-columns: 1fr !important;
        }

        /* ── KPI COUNTER CARDS — COMPACT & SOLID ── */
        .sl-kpi-card {
            padding: 7px 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        .sl-kpi-card:hover {
            transform: translateY(-2px);
        }
        .sl-kpi-card.active-filter {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45) !important;
            border-color: var(--sl-primary-light) !important;
        }

        /* ── INPUTS & SELECTS ── */
        .sl-input {
            background: #060d18 !important;
            border: 1.5px solid #2b4263 !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            padding: 9px 14px !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-weight: 800 !important;
            outline: none !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        .sl-input:focus {
            border-color: var(--sl-primary-light) !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.22) !important;
        }

        /* ── DUAL-UNIT INTERACTIVE BOXES (استلم هنا بالأساسية ⇄ أو بالفرعية) ── */
        .sl-unit-box {
            position: relative;
            overflow: hidden;
            background: var(--sl-unit-box-bg);
            border: 1.5px solid #284060;
            border-radius: 12px;
            padding: 8px 10px;
            width: 236px;
            min-width: 236px;
            transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.35);
        }
        .sl-unit-box::after {
            display: none !important;
        }
        .sl-unit-box:hover, .sl-unit-box.is-active-unit-mode {
            transform: translateY(-1px);
            border-color: var(--sl-primary-light) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.45);
        }
        .sl-unit-box.base-box.is-active-unit-mode {
            border-color: #818cf8 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.45);
        }

        /* ── TABLE STYLING & CUSTOM FONT SCALING ── */
        .sl-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .sl-table thead th {
            background: #081221;
            color: #e2e8f0;
            font-weight: 900;
            font-size: 0.85rem;
            padding: 12px 10px;
            border-bottom: 2px solid var(--sl-primary);
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }
        .sl-table tbody tr {
            transition: background-color 0.15s ease;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        }
        .sl-table tbody tr:hover {
            background: #13253e !important;
        }
        .sl-table tbody tr.sl-selected-row {
            background: #122b47 !important;
            box-shadow: inset 4px 0 0 var(--sl-primary-light);
        }
        .sl-table tbody td {
            padding: 10px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            font-size: var(--sl-grid-font-size);
            color: var(--sl-grid-font-color);
            vertical-align: middle;
        }

        /* ── FLOATING LIVE SUMMARY BAR (شريط الإحصائيات العائم — لون صلب منسق بدون وهج مزعج) ── */
        .sl-floating-summary {
            position: sticky;
            bottom: 10px;
            z-index: 900;
            background: linear-gradient(135deg, #0b1729, #102038) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border: 1.5px solid var(--sl-primary);
            border-radius: 14px;
            padding: 12px 22px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.65);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            transition: transform 0.25s ease, opacity 0.25s ease, background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease;
        }

        /* ── PULSE DOT ── */
        @keyframes slPulseGlow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.75; }
        }
        .sl-pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            animation: none; /* cpu-opt */
        }

        /* ── STATUS BADGES ── */
        .sl-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 11px;
            border-radius: 99px;
            font-size: 0.78rem;
            font-weight: 900;
            white-space: nowrap;
        }
        .sl-badge-pending { background: #3b280c; color: #fcd34d; border: 1px solid #d97706; }
        .sl-badge-partial { background: #0c2b45; color: #7dd3fc; border: 1px solid #0284c7; }
        .sl-badge-done { background: #093529; color: #6ee7b7; border: 1px solid #059669; }
        /* ── 3-LEVEL DEDUCTION & SPLITTING ROLE PILLS (المدير / مدير المخزن / المخزنجي QR) ── */
        .sl-level-bar {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #081222;
            padding: 3px 5px;
            border-radius: 11px;
            border: 1.5px solid #263c5c;
        }
        .sl-level-pill {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 900;
            cursor: pointer;
            border: 1px solid transparent;
            background: transparent;
            color: #cbd5e1;
            transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
        }
        .sl-level-pill:hover { color: #ffffff; background: #162844; }
        .sl-level-pill.active-lvl-1 {
            background: linear-gradient(135deg, #6d28d9, #4338ca);
            color: #ffffff;
            border-color: #a78bfa;
        }
        .sl-level-pill.active-lvl-2 {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            border-color: #38bdf8;
        }
        .sl-level-pill.active-lvl-3 {
            background: linear-gradient(135deg, #059669, #047857);
            color: #ffffff;
            border-color: #34d399;
        }
    
/* SL_GATE_CLEAN_CENTER_2027 */
.sl-btn { justify-content: center !important; text-align: center !important; min-height: 44px !important; }
.sl-input { min-height: 46px !important; }
.sl-table thead th, .sl-table tbody td { min-height: 46px !important; padding-top: 11px !important; padding-bottom: 11px !important; }
</style>
    <style id="global-zero-lag-css">.ambient-bg,.ambient-bg .orb,.preset-ribbon-btn::after,.preset-btn::after,.btn-action-hero::after,.btn-run::after,.action-tab.active::before,.sticky-footer::before{display:none!important;content:none!important}*,*::before,*::after{backdrop-filter:none!important;-webkit-backdrop-filter:none!important;animation:none!important}button,a,input,select,[role="button"]{touch-action:manipulation;-webkit-tap-highlight-color:transparent}button,.btn,.modern-btn,.preset-ribbon-btn,.preset-btn,.btn-action-hero,.btn-run,.action-tab,.fin-card,.pos-grid tbody tr{transition:background-color .08s ease,border-color .08s ease,opacity .08s ease!important}button:hover,.btn:hover,.modern-btn:hover,.preset-ribbon-btn:hover,.preset-btn:hover,.btn-action-hero:hover,.del-btn:hover,.modal-close:hover{transform:none!important}</style>
    <link id="magaies-ophthalmic-ergo-css" rel="stylesheet" href="css/ophthalmic-ergonomics-2027.css?v=2027_gold_v8">
</head>
<body class="has-sidebar" data-logistics-mode="<?php echo (isset($_GET['mode']) && (!isset($_GET['mode']) || $_GET['mode'] === 'purchases')) ? 'purchases' : 'sales'; ?>">

    <div class="toast-container" id="toast-container"></div>
    <script>
    window.ensureHtml5QrCodeLoaded = function(cb) {
        if (window.Html5Qrcode) { if (cb) cb(); return; }
        var s = document.createElement('script');
        s.src = 'js/vendor/html5-qrcode.min.js';
        s.async = true;
        s.onload = function() { if (cb) cb(); };
        document.head.appendChild(s);
    };
    </script>

    <div id="sl-scale-stage" style="width: 100%; min-height: 100vh; display: flex; justify-content: center; align-items: flex-start; overflow-x: hidden; box-sizing: border-box;">
    <div id="sl-main-wrapper" style="padding: 6px 10px 10px 10px; width: 100%; max-width: 100%; min-height: 100vh; margin: 0 auto; display: flex; flex-direction: column; gap: 8px; box-sizing: border-box; transform-origin: center top;">

        <!-- ═══════════════════════════════════════════════════════════════════
             1. TOP COCKPIT BAR: CLEAN NAVIGATION + ACTIVE TOOLS ONLY
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-top-cockpit-bar" class="sl-glass-card" style="background: var(--sl-header-grad); padding: 8px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: nowrap; gap: 10px;">
            <div id="sl-main-title-banner" style="display: inline-flex; align-items: center; background: linear-gradient(135deg, #1d3561 0%, #142547 100%); border: 1.5px solid rgba(56, 189, 248, 0.65); padding: 6px 14px; border-radius: 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.45); flex-shrink: 0;">
                <span id="sl-main-title-icon" style="display:none !important;"></span>
                <h1 id="sl-main-title-text" style="margin: 0; font-size: 0.96rem; font-weight: 900; color: #ffffff; white-space: nowrap;"><?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? 'استلام المشتريات' : 'تسليم وتقسيم مبيعات'; ?></h1>
            </div>

            <input type="text" id="sl-reg-search" class="sl-input" placeholder="🔍 بحث برقم الفاتورة أو المورد أو الصنف..." oninput="renderSlRegistry()" onkeydown="if(event.key==='Enter'){event.preventDefault(); quickLoadFirstMatch(this.value);}" style="flex: 1; min-width: 180px; font-size: 0.84rem; padding: 6px 14px !important; color: #cbd5e1 !important;">

            <div style="display:flex; align-items:center; gap:7px; flex-shrink:0;">
                <button type="button" onclick="refreshAllSlData()" class="sl-btn" style="color:#38bdf8; border-color:rgba(56,189,248,0.45); padding: 6px 11px; font-size: 0.78rem; white-space: nowrap;">تحديث</button>
                <input type="hidden" id="sl-qr-guard-mode" value="auto">
                <button type="button" onclick="openSlMobileBridgeModal()" class="sl-btn" style="background: linear-gradient(135deg, #0284c7, #0369a1); border-color: #7dd3fc; color: #ffffff; padding: 6px 14px; font-size: 0.80rem; border-radius: 9px; white-space: nowrap; font-weight: 900;">
                    <span id="sl-bridge-live-dot" class="sl-pulse-dot" style="background:#fde047;"></span>
                    📷 الماسح الذكي QR (الجهاز + الجوال)
                </button>
                <div id="magaies-quartet-header-slot" style="display:flex; align-items:center;"></div>
            </div>
        </div>

        <!-- Hidden mode elements preserved for JS compatibility without visual clutter -->
        <div class="sl-mode-switcher" style="display:none !important;">
            <div id="sl-tab-sales" class="sl-mode-tab mode-sales" onclick="switchLogisticsMode('sales')">
                <span id="sl-tab-sales-badge">0</span>
            </div>
            <div id="sl-tab-purchases" class="sl-mode-tab mode-purchases active" onclick="switchLogisticsMode('purchases')">
                <span id="sl-tab-purchases-badge">0</span>
            </div>
        </div>

        <!-- LIVE CONTINUOUS QR SCAN FEEDBACK BANNER -->
        <div id="sl-live-qr-banner" style="display:none; padding: 10px 18px; border-radius: 14px; font-weight: 900; font-size: 0.95rem; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; box-shadow: 0 10px 28px rgba(0,0,0,0.6);">
            <div id="sl-live-qr-banner-text" style="display:flex; align-items:center; gap:10px;"></div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span id="sl-live-qr-counter-pill" style="background:rgba(0,0,0,0.4); padding:4px 14px; border-radius:99px; font-size:0.84rem; color:#fff;"></span>
                <button type="button" onclick="document.getElementById('sl-live-qr-banner').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             4. SMART INTERACTIVE REGISTRY (WITH INLINE COUNTER BADGES)
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-filter-bar-card" class="sl-glass-card" style="padding: 8px 14px;">
            <div id="sl-registry-filter-row" style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; width: 100%;">
                <span id="sl-registry-title" style="display:none !important;"></span>
                <span id="sl-reg-count-badge" style="display:none !important;"></span>

                <label for="sl-reg-wh-filter" id="sl-reg-wh-filter-lbl" style="font-size: 0.80rem; font-weight: 900; color: #fcd34d; white-space: nowrap; flex-shrink: 0;">مخزن المشتريات:</label>
                <select id="sl-reg-wh-filter" class="sl-input sl-top-bar-ctl" onchange="renderSlRegistry()" style="font-size: 0.80rem; padding: 0 10px !important; width: 155px !important; min-width: 140px !important; max-width: 168px !important; flex: 0 1 155px !important; color: #fcd34d !important;">
                    <option value="PENDING_RECEIPT" selected>⏳ قيد الاستلام</option>
                    <option value="all">🏢 كل المخازن</option>
                </select>

                <button type="button" id="sl-filter-btn-pending" onclick="setSlRegistryFilter('pending')" class="sl-btn sl-reg-flt-btn sl-flt-active sl-top-bar-ctl" style="background: var(--sl-primary); color: #fff; border-color: var(--sl-primary-light); padding: 0 12px; font-size: 0.80rem; white-space: nowrap; flex: 1.35 1 0 !important;">قيد الاستلام <span id="sl-kpi-val-pending" class="sl-btn-cnt">0</span></button>
                <button type="button" id="sl-filter-btn-partial" onclick="setSlRegistryFilter('partial')" class="sl-btn sl-reg-flt-btn sl-top-bar-ctl" style="padding: 0 10px; font-size: 0.80rem; white-space: nowrap; flex: 1 1 0 !important;">الجزئي <span id="sl-kpi-val-partial" class="sl-btn-cnt">0</span></button>
                <button type="button" id="sl-filter-btn-done" onclick="setSlRegistryFilter('done')" class="sl-btn sl-reg-flt-btn sl-top-bar-ctl" style="padding: 0 10px; font-size: 0.80rem; white-space: nowrap; flex: 1 1 0 !important;">المكتمل <span id="sl-kpi-val-done" class="sl-btn-cnt">0</span></button>
                <button type="button" id="sl-filter-btn-notes" onclick="setSlRegistryFilter('notes')" class="sl-btn sl-reg-flt-btn sl-top-bar-ctl" style="padding: 0 10px; font-size: 0.80rem; white-space: nowrap; flex: 1 1 0 !important;">الأذونات <span id="sl-kpi-val-notes" class="sl-btn-cnt">0</span></button>
                <button type="button" id="sl-filter-btn-all" onclick="setSlRegistryFilter('all')" class="sl-btn sl-reg-flt-btn sl-top-bar-ctl" style="padding: 0 10px; font-size: 0.80rem; white-space: nowrap; flex: 0.85 1 0 !important;">الكل</button>

                <select id="sl-reg-limit" onchange="onSlPageSelectChange(this.value)" style="display:none !important;"><option value="p_1" selected>1</option></select>
                <button type="button" id="sl-reg-cols-btn" onclick="toggleSlRegColsDrawer()" class="sl-btn sl-top-bar-ctl" style="padding: 0 14px; font-size: 0.80rem; font-weight: 900; white-space: nowrap; flex: 0 0 auto; background: linear-gradient(135deg,#7e22ce,#4c1d95); border-color: #c084fc; color: #fff;" title="التحكم في أعمدة الجدول والتكبير والتصغير">الأعمدة</button>
            </div>
        </div>

                <!-- 3. COMPACT HORIZONTAL DOCUMENT & WAREHOUSE RECEIPT CARD (MOVED INTO RECEIVING MODAL) -->
        <div id="sl-inspection-cockpit" style="display: none; width: 100%;">
            <style>
                html, body { max-width: 100% !important; overflow-x: hidden !important; }
                #sl-scale-stage, #sl-main-wrapper { width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; }
                #sl-doc-meta-card.sl-card3-elevated { background: linear-gradient(180deg, #132847 0%, #0c1b33 100%) !important; border: 2px solid rgba(56,189,248,0.6) !important; border-radius: 12px !important; box-shadow: 0 6px 18px rgba(0,0,0,0.35) !important; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated { background: linear-gradient(180deg, var(--lt-card-1, #ffffff) 0%, var(--lt-card-2, #f1f5f9) 100%) !important; border: 2px solid var(--lt-border, #334155) !important; box-shadow: 0 4px 12px rgba(15,23,42,0.08) !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-neat-top-card { display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; gap: 3px; min-height: 54px; padding: 6px 10px; border-radius: 10px; background: rgba(255,255,255,0.06); border: 1.5px solid rgba(56,189,248,0.45); box-shadow: 0 0 8px rgba(56,189,248,0.12); overflow: hidden; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-neat-top-card { background: #ffffff !important; border: 2px solid var(--lt-border, #334155) !important; box-shadow: 0 2px 6px rgba(15,23,42,0.08) !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-row2-band { background: rgba(56,189,248,0.10); border: 1.5px solid rgba(56,189,248,0.42); border-radius: 10px; padding: 7px 12px; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-row2-band { background: var(--lt-sub-2, #e2e8f0) !important; border: 2px solid var(--lt-border, #334155) !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-bg-lbl { font-size: 0.78rem; font-weight: 800; color: #94a3b8; line-height: 1.15; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-bg-lbl { color: #334155 !important; font-weight: 900 !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-bg-val { font-size: 1.02rem; font-weight: 900; color: #f8fafc; font-family: 'Outfit','Cairo',sans-serif; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-bg-val { color: #000000 !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-fld-box { display: flex; flex-direction: column; gap: 3px; background: rgba(15,23,42,0.55); padding: 4px 7px 6px; border-radius: 9px; border: 1px solid rgba(148,163,184,0.28); }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-fld-box { background: var(--lt-sub-2, #e2e8f0) !important; border: 1.5px solid rgba(15,23,42,0.22) !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-fld-lbl { font-size: 0.72rem; font-weight: 900; color: #7dd3fc; text-align: right; line-height: 1.15; white-space: nowrap; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-fld-lbl { color: #0f172a !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-input { min-height: 36px !important; height: 36px !important; max-height: 38px !important; padding: 4px 10px !important; font-size: 0.88rem !important; font-weight: 900 !important; border-radius: 7px !important; border: 2px solid #38bdf8 !important; background: #243f6b !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: inset 0 1px 3px rgba(0,0,0,0.35) !important; transition: border-color 0.15s, background 0.15s !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-input:hover, #sl-doc-meta-card.sl-card3-elevated .sl-input:focus { background: #2d4e82 !important; border-color: #fde047 !important; box-shadow: 0 0 0 2.5px rgba(250,204,21,0.28) !important; outline: none !important; }
                #sl-doc-meta-card.sl-card3-elevated .sl-input::placeholder { color: #94a3b8 !important; -webkit-text-fill-color: #94a3b8 !important; font-weight: 700 !important; opacity: 0.85 !important; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-input { background: #ffffff !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 2px solid var(--lt-border, #1e293b) !important; box-shadow: inset 0 1px 2px rgba(15,23,42,0.08) !important; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-input:hover, html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-input:focus { border-color: #0284c7 !important; box-shadow: 0 0 0 2.5px rgba(2,132,199,0.24) !important; }
                html.light-theme #sl-doc-meta-card.sl-card3-elevated .sl-input::placeholder { color: #64748b !important; -webkit-text-fill-color: #64748b !important; font-weight: 700 !important; opacity: 1 !important; }
                html.light-theme .sl-glass-card { background: var(--lt-card-1, #ffffff) !important; border: 2px solid var(--lt-border, #334155) !important; color: #000000 !important; }
                html.light-theme .sl-glass-card *:not(button):not(.sl-badge):not(select):not(input):not(option) { color: #000000 !important; }
                html.light-theme .sl-table thead th { background: var(--lt-sub-2, #e2e8f0) !important; color: #000000 !important; border-left: 1.5px solid rgba(15,23,42,0.28) !important; border-bottom: 2px solid var(--lt-border, #334155) !important; font-weight: 900 !important; }
                html.light-theme .sl-table tbody tr:nth-child(odd) { background: #ffffff !important; }
                html.light-theme .sl-table tbody tr:nth-child(even) { background: var(--lt-sub-1, #f1f5f9) !important; }
                html.light-theme .sl-table td { border-left: 1.5px solid rgba(15,23,42,0.22) !important; border-bottom: 1.5px solid rgba(15,23,42,0.25) !important; color: #000000 !important; }
                html.light-theme .sl-table td div[style*="rgba(2,6,23"] { background: #e2e8f0 !important; border-color: #94a3b8 !important; }
                html.light-theme .sl-input, html.light-theme #sl-reg-search, html.light-theme #sl-reg-wh-filter, html.light-theme #sl-reg-limit { background: #ffffff !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 2.2px solid var(--lt-border, #2b0f02) !important; }
                html.light-theme #sl-top-cockpit-bar { background: var(--lt-sub-2, #e2e8f0) !important; }
                html.light-theme #sl-main-title-banner { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important; border: 2.5px solid #2b0f02 !important; }
                html.light-theme #sl-main-title-text { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
            </style>
            <div id="sl-doc-meta-card" class="sl-glass-card sl-card3-elevated" style="padding: 8px 12px; display: flex; flex-direction: column; gap: 6px;">
                <!-- السطر الأول: 5 بطاقات مهذبة متناسقة مع السمة النشطة في نفس المستوى -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1.8fr 1.1fr 1fr; gap: 8px; align-items: stretch;">
                    <h3 id="sl-doc-card-title" style="display:none !important;"></h3>
                    <span id="sl-doc-card-sub" style="display:none !important;"></span>
                    <span id="sl-lbl-hash" style="display:none !important;">---</span>
                    <div id="sl-doc-status-pill" style="display:none !important;"></div>
                    <div class="sl-neat-top-card" data-rc-invoice-no="" id="sl-top-inv-box"><span class="sl-bg-lbl">رقم الفاتورة</span><span id="sl-lbl-inv-no" class="sl-bg-val">---</span></div>
                    <div class="sl-neat-top-card"><span class="sl-bg-lbl">تاريخ الفاتورة</span><span id="sl-lbl-date" class="sl-bg-val">---</span></div>
                    <div class="sl-neat-top-card"><span id="sl-lbl-party-title" class="sl-bg-lbl">المورد</span><span id="sl-lbl-party-name" class="sl-bg-val">---</span></div>
                    <div class="sl-neat-top-card"><span id="sl-lbl-rec-no-title" class="sl-bg-lbl">رقم الاستلام</span><span id="sl-lbl-rec-no" class="sl-bg-val">---</span></div>
                    <div class="sl-neat-top-card"><span id="sl-lbl-rec-date-title" class="sl-bg-lbl">تاريخ الاستلام</span><span id="sl-lbl-rec-date" class="sl-bg-val">---</span></div>
                </div>

                <!-- Hidden legacy elements preserved so JS never errors -->
                <div style="display:none !important;">
                    <input type="file" id="sl-photo-input" accept="image/*" onchange="handleSlPhotoUpload(event)">
                    <button type="button" id="sl-supplier-ledger-btn" onclick="openSlPartyLedger()"></button>
                    <div id="sl-party-fin-strip"><button type="button" id="sl-fin-strip-currency-btn" data-cur-mode="SDG"></button><span id="sl-fin-ledger">0</span><span id="sl-fin-debt">0</span><span id="sl-fin-checks">0</span><span id="sl-fin-limit">0</span><span id="sl-fin-rem">0</span></div>
                    <input type="text" id="sl-personal-notes">
                    <span id="sl-verify-badge"></span>
                    <div id="sl-camera-placeholder"></div>
                </div>

                <!-- السطر الثاني بشريط لوني متمايز: على نفس الحذاء -->
                <div class="sl-row2-band" style="display: grid; grid-template-columns: 1fr 1.15fr 1.15fr; gap: 12px; align-items: center;">
                    <div id="sl-from-wh-wrap" style="display:flex; align-items:center; gap:7px; white-space:nowrap;">
                        <span id="sl-lbl-from-wh" class="sl-bg-lbl">من مخزن المشتريات:</span>
                        <strong id="sl-val-from-wh" class="sl-bg-val" style="color:#d97706;">⏳ قيد الاستلام</strong>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span id="sl-lbl-wh-select" class="sl-bg-lbl" style="white-space:nowrap;">إلى المخزن المستلم:</span>
                        <select id="sl-master-warehouse" class="sl-input" style="flex:1; width:100%;" onchange="onSlMasterWarehouseChange(this.value); document.querySelectorAll('.sl-full-rcv-row-wh').forEach(s=>s.value=this.value);"></select>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span id="sl-lbl-receiver" class="sl-bg-lbl" style="white-space:nowrap;">المخزنجي:</span>
                        <input type="text" id="sl-receiver-name" class="sl-input" placeholder="المخزنجي" style="flex:1; width:100%;">
                    </div>
                </div>
                <!-- السطر الثالث والرابع: عناوين ثابتة أعلى كل حقل ومنطقة كتابة متمايزة اللون -->
                <div style="display: grid; grid-template-columns: 1.15fr 1fr 0.9fr 0.9fr 0.75fr; gap: 7px; align-items: stretch;">
                    <div class="sl-fld-box"><label for="sl-driver-name" class="sl-fld-lbl">اسم السائق</label><input type="text" id="sl-driver-name" class="sl-input" placeholder="اكتب اسم السائق" style="width:100%;"></div>
                    <div class="sl-fld-box"><label for="sl-driver-phone" class="sl-fld-lbl">رقم هاتف السائق</label><input type="text" id="sl-driver-phone" class="sl-input" placeholder="رقم الهاتف" style="width:100%;"></div>
                    <div class="sl-fld-box"><label for="sl-truck-plate" class="sl-fld-lbl">رقم اللوحة</label><input type="text" id="sl-truck-plate" class="sl-input" placeholder="رقم اللوحة" style="width:100%;"></div>
                    <div class="sl-fld-box"><label for="sl-driver-receipt-no" class="sl-fld-lbl">رقم الإذن</label><input type="text" id="sl-driver-receipt-no" class="sl-input" placeholder="رقم الإذن" style="width:100%;"></div>
                    <div class="sl-fld-box"><label for="sl-bundles-count" class="sl-fld-lbl">العدد / الطرود</label><input type="text" id="sl-bundles-count" class="sl-input" placeholder="العدد" style="width:100%;"></div>
                </div>
                <div style="display: grid; grid-template-columns: 0.9fr 0.95fr 1fr 0.95fr 2.2fr; gap: 7px; align-items: stretch;">
                    <div class="sl-fld-box"><label for="sl-gate-pass-no" class="sl-fld-lbl">رقم المرجع</label><input type="text" id="sl-gate-pass-no" class="sl-input" placeholder="رقم المرجع" style="width:100%;"></div>
                    <div class="sl-fld-box"><label for="sl-manifesto-no" class="sl-fld-lbl">رقم المنافيستو</label><input type="text" id="sl-manifesto-no" class="sl-input" placeholder="رقم المنافيستو" style="width:100%;"></div>
                    <div class="sl-fld-box"><label for="sl-final-inv-no" class="sl-fld-lbl">الفاتورة النهائية</label><input type="text" id="sl-final-inv-no" class="sl-input" placeholder="رقم الفاتورة النهائية" style="width:100%;"></div>
                    <div class="sl-fld-box"><label for="sl-transit-permit-no" class="sl-fld-lbl">تصريح المرور</label><input type="text" id="sl-transit-permit-no" class="sl-input" placeholder="تصريح المرور" style="width:100%;"></div>
                    <div class="sl-fld-box"><label for="sl-warehouse-notes" class="sl-fld-lbl">ملحوظة الاستلام</label><input type="text" id="sl-warehouse-notes" class="sl-input" placeholder="أي ملاحظات إضافية..." style="width:100%;"></div>
                </div>

                <div id="sl-photo-preview-wrap" style="display:none; text-align:right;">
                    <img id="sl-photo-preview-img" src="" style="max-height:52px; border-radius:8px; border:1px solid var(--sl-primary-light);">
                </div>
            </div>
        </div>

        <div id="sl-registry-card" class="sl-glass-card" style="padding: 8px 14px;">
            <div id="sl-reg-cols-drawer" style="display:none; flex-direction:column; gap:8px; background:rgba(15,23,42,0.94); border:1.8px solid rgba(192,132,252,0.55); border-radius:11px; padding:10px 14px; margin-bottom:10px;"></div>

            <div id="sl-registry-scroll-wrap" style="overflow-x: auto; overflow-y: visible !important; max-height: none !important; border-radius: 12px; border: 1.5px solid rgba(56,189,248,0.28);">
                <table id="sl-main-reg-table" class="sl-table" style="table-layout: fixed; width: 100%;">
                    <thead>
                        <tr id="sl-reg-thead-row">
                            <th data-regcol="date" style="text-align: center; width: 7%; position: relative;">التاريخ</th>
                            <th data-regcol="inv" style="text-align: right; width: 8%; position: relative;">الفاتورة</th>
                            <th data-regcol="recno" style="text-align: center; width: 9%; position: relative; color: #34d399;">رقم الاستلام</th>
                            <th data-regcol="isplit" style="text-align: center; width: 6%; position: relative; color: #c084fc; display: none;">التقسيم</th>
                            <th id="sl-reg-th-party" data-regcol="party" style="text-align: right; width: 13%; position: relative;">المورد</th>
                            <th data-regcol="iname" style="text-align: right; width: 13%; position: relative;">اسم الصنف</th>
                            <th data-regcol="iorig" style="text-align: center; width: 7%; position: relative; color: #38bdf8; display: none;">أصل الفاتورة</th>
                            <th data-regcol="iqty" style="text-align: center; width: 13%; position: relative;">أصل الفاتورة</th>
                            <th data-regcol="irem" style="text-align: center; width: 7%; position: relative; color: #fde047; display: none;">المتبقي</th>
                            <th data-regcol="ibar" style="text-align: center; width: 9%; position: relative;">الشريط</th>
                            <th data-regcol="iprice" style="text-align: center; width: 8%; position: relative;">السعر</th>
                            <th data-regcol="wh" style="text-align: center; width: 9%; position: relative;">المخزن والحالة</th>
                            <th data-regcol="actions" style="text-align: center; width: 13%; position: relative;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="sl-registry-tbody"></tbody>
                </table>
            </div>
            <div id="sl-pagination-bar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; padding:10px 6px 4px;"></div>
            <div id="sl-bottom-print-suite" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:6px; padding:8px 10px; margin-top:4px; border-radius:10px; background:rgba(15,23,42,0.72); border:1.5px solid rgba(56,189,248,0.35);">
                <span style="font-size:0.78rem; font-weight:900; color:#7dd3fc; white-space:nowrap;">🖨️ الطباعة والتدقيق المخزني:</span>
                <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                    <button type="button" onclick="window.printSlRegistryReport('current')" class="sl-btn sl-act-mini" style="padding:5px 10px !important; font-size:0.74rem !important; background:linear-gradient(135deg,#0284c7,#0369a1); border-color:#7dd3fc; color:#fff;" title="طباعة كشف شامل للتبويب المعروض حالياً بكامل تفاصيله">📄 طباعة الكشف (A4)</button>
                    <button type="button" onclick="window.printSlRegistryReport('part1')" class="sl-btn sl-act-mini" style="padding:5px 10px !important; font-size:0.74rem !important; background:linear-gradient(135deg,#0d9488,#0f766e); border-color:#5eead4; color:#fff;" title="طباعة أذونات القسم الأول: الكمية المستلمة / المصروفة (1) برقم التقسيم /1">🖨️ طباعة القسم (1) المستلم</button>
                    <button type="button" onclick="window.printSlRegistryReport('part2')" class="sl-btn sl-act-mini" style="padding:5px 10px !important; font-size:0.74rem !important; background:linear-gradient(135deg,#d97706,#b45309); border-color:#fcd34d; color:#fff;" title="طباعة أذونات القسم الثاني: الكمية المتبقية (2) برقم التقسيم /2">🖨️ طباعة القسم (2) المتبقي</button>
                    <button type="button" onclick="window.printSlRegistryReport('items_summary')" class="sl-btn sl-act-mini" style="padding:5px 10px !important; font-size:0.74rem !important; background:linear-gradient(135deg,#059669,#047857); border-color:#6ee7b7; color:#fff;" title="طباعة تجميع الأصناف ومطابقة الرصيد المخزني وكرت الصنف">📦 تجميع الأصناف والكروت</button>
                    <button type="button" onclick="window.printSlRegistryReport('zero_variance_audit')" class="sl-btn sl-act-mini" style="padding:5px 10px !important; font-size:0.74rem !important; background:linear-gradient(135deg,#dc2626,#991b1b); border-color:#fca5a5; color:#fff;" title="تدقيق انعدام الفروقات (أصل الفاتورة = المستلم + المتبقي) ومطابقة أرصدة المخازن الرقابية">🛡️ تدقيق عدم الفروقات</button>
                    <button type="button" onclick="window.printSlRegistryReport('inspection')" class="sl-btn sl-act-mini" style="padding:5px 10px !important; font-size:0.74rem !important; background:linear-gradient(135deg,#4f46e5,#3730a3); border-color:#a5b4fc; color:#fff;" title="طباعة استمارة الجرد والفحص والعدّ الفعلي لأمين المخزن">📋 استمارة الفحص والعدّ</button>
                    <button type="button" onclick="window.printSlRegistryReport('thermal')" class="sl-btn sl-act-mini" style="padding:5px 10px !important; font-size:0.74rem !important; background:linear-gradient(135deg,#7e22ce,#581c87); border-color:#d8b4fe; color:#fff;" title="طباعة إيصال حراري سريع 80mm للبوابة والمخزن">🧾 حراري 80mm</button>
                    <button type="button" onclick="window.exportSlRegistryCsv()" class="sl-btn sl-act-mini" style="padding:5px 10px !important; font-size:0.74rem !important; background:rgba(16,185,129,0.22); border-color:#34d399; color:#6ee7b7;" title="تصدير كافة التفاصيل إلى ملف Excel">📊 Excel</button>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             5. INTERACTIVE SPLIT & INSPECTION MODAL (تفتح عند الضغط على تقسيم وفحص)
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-split-inspect-modal" style="display:none; position:fixed; top:0; bottom:0; left:0; right:62px; width:calc(100% - 62px); height:100dvh; background:rgba(2,6,23,0.94); z-index:99990; overflow-y:auto; padding:6px 10px; box-sizing:border-box; direction:rtl;">
            <div style="width:100%; max-width:100%; min-height:calc(100vh - 12px); margin:0; display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(15,23,42,0.95); border:1.5px solid var(--sl-primary-light); border-radius:12px; padding:8px 16px;">
                    <span id="sl-split-modal-hdr-title" style="font-size:0.95rem; font-weight:900; color:#7dd3fc;">✂️ شاشة التقسيم الجزئي والفحص وتوزيع المخازن</span>
                    <button type="button" onclick="closeSlSplitInspectModal()" class="sl-btn" style="padding:5px 14px; background:rgba(239,68,68,0.18); border-color:#ef4444; color:#fca5a5;">✖ إغلاق والعودة للسجل</button>
                </div>
                <div id="sl-split-card3-slot"></div>

        <div id="sl-items-grid-card" class="sl-glass-card" style="padding: 10px 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 260px;">
                    <h3 id="sl-grid-title" style="margin: 0; font-size: 0.92rem; font-weight: 900; color: #7dd3fc; white-space: nowrap;">جدول التقسيم المؤتمت</h3>
                    <input type="text" id="sl-split-barcode-scan" class="sl-input" placeholder="🔍 امسح باركود الصنف أو اكتب (الكود*الكمية) ثم Enter للعدّ الآلي بدون خطأ..." onkeydown="if(event.key==='Enter'){event.preventDefault(); window.onSlSplitBarcodeScan(this.value); this.value='';}" style="flex: 1; height: 34px !important; min-height: 34px !important; padding: 4px 12px !important; font-size: 0.82rem !important;">
                </div>

                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <button type="button" id="sl-btn-fill-full" onclick="fillAllSlQuantities(1)" class="sl-btn" style="background: rgba(16,185,129,0.2); border-color: #34d399; color: #6ee7b7; padding: 5px 11px; font-size: 0.78rem;">كامل المتبقي</button>
                    <button type="button" id="sl-btn-fill-zero" onclick="fillAllSlQuantities(0)" class="sl-btn" style="background: rgba(239,68,68,0.16); border-color: #f87171; color: #fca5a5; padding: 5px 11px; font-size: 0.78rem;">تصفير</button>
                    <button type="button" id="sl-btn-ai-audit" onclick="openSlAIAuditModal()" class="sl-btn" style="background: rgba(126,34,206,0.28); border-color: #c084fc; color: #e9d5ff; padding: 5px 11px; font-size: 0.78rem;">فحص الأسعار AI</button>
                    <button type="button" onclick="const d=document.getElementById('sl-cols-drawer'); d.style.display=(d.style.display==='none'?'flex':'none');" class="sl-btn" style="background: rgba(2,132,199,0.22); border-color: #7dd3fc; color: #fff; padding: 5px 12px; font-size: 0.78rem;">الأعمدة</button>
                </div>
            </div>

            <div id="sl-cols-drawer" style="display:none; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; background:rgba(2,6,23,0.88); padding:8px 14px; border-radius:10px; border:1.5px solid rgba(56,189,248,0.45); margin-bottom:10px;">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; font-size:0.78rem; font-weight:800;">
                    <span style="color:#7dd3fc;">إظهار/إخفاء الأعمدة:</span>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-code" checked onchange="applySlColVisibility()"> الكود وكرت الصنف</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-orig" checked onchange="applySlColVisibility()"> <span id="sl-lbl-chk-orig">كمية الشراء</span></label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-prev" checked onchange="applySlColVisibility()"> المتبقي قبل الدفعة</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-spec" checked onchange="applySlColVisibility()"> التعادل</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-exec1" checked onchange="applySlColVisibility()"> <span id="sl-lbl-chk-exec1">الكمية المستلمة (1)</span></label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-rem2" checked onchange="applySlColVisibility()"> الكمية المتبقية (2)</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-base" checked onchange="applySlColVisibility()"> الوحدة الأساسية (1)</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-base2" checked onchange="applySlColVisibility()"> الوحدة الأساسية (2)</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-wh" checked onchange="applySlColVisibility()"> المستودع والتقسيم</label>
                    <input type="checkbox" id="sl-col-chk-price" style="display:none;">
                </div>
                <button type="button" onclick="saveSlCustomConfigPermanently(); document.getElementById('sl-cols-drawer').style.display='none';" class="sl-btn" style="padding:4px 12px; font-size:0.76rem; background:#059669; border-color:#34d399; color:#fff;">حفظ نهائي</button>
            </div>

            <div style="overflow-x: auto; border-radius: 12px; border: 2px solid rgba(56,189,248,0.55);">
                <table class="sl-table" id="sl-items-grid" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="font-size: 0.80rem;">
                            <th style="width: 82px; text-align: center;">رقم التقسيم</th>
                            <th class="sl-col-code" style="width: 98px; text-align: center;">الكود / الكرت</th>
                            <th style="min-width: 160px; text-align: right;">اسم الصنف</th>
                            <th style="width: 72px; text-align: center;">الوحدة</th>
                            <th class="sl-col-orig" id="sl-th-orig-qty" style="width: 86px; text-align: center;">كمية الشراء</th>
                            <th class="sl-col-prev" style="width: 96px; text-align: center;">المتبقي</th>
                            <th class="sl-col-spec" style="width: 92px; text-align: center;">التعادل</th>
                            <th class="sl-col-exec1" id="sl-th-exec-qty-1" style="width: 118px; text-align: center; color: #38bdf8;">الكمية المستلمة (1)</th>
                            <th class="sl-col-rem2" id="sl-th-rem-qty-2" style="width: 118px; text-align: center; color: #fde047;">الكمية المتبقية (2)</th>
                            <th class="sl-col-base" id="sl-th-base-qty-1" style="width: 118px; text-align: center; color: #a5b4fc;">الوحدة الأساسية (1)</th>
                            <th class="sl-col-base2" id="sl-th-base-qty-2" style="width: 118px; text-align: center; color: #f9a8d4;">الوحدة الأساسية (2)</th>
                            <th class="sl-col-wh" style="width: 195px; text-align: center;">المستودع والتقسيم الآلي</th>
                        </tr>
                    </thead>
                    <tbody id="sl-items-tbody">
                        <tr>
                            <td colspan="9" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 800; font-size: 1rem;">
                                👆 اضغط على أي فاتورة من السجل العلوي أو امسح باركود الـ QR لعرض الأصناف وتقسيمها بالوحدتين والمخازن
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             7. FLOATING LIVE SUMMARY & EXECUTION BAR (شريط الملخص والاعتماد العائم)
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="sl-floating-summary" style="justify-content: center;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; width: 100%;">
                <button type="button" id="sl-btn-print-part1" onclick="window.printSlSplitPartFromModal(1)" class="sl-btn" style="background: linear-gradient(135deg, #0284c7, #0369a1); border-color: #7dd3fc; color: #ffffff; padding: 7px 14px; font-size: 0.82rem;" title="طباعة إذن القسم الأول: الكمية المنفذة (1) برقم التقسيم /1">🖨️ طباعة القسم الأول (1) — /1</button>
                <button type="button" id="sl-btn-print-part2" onclick="window.printSlSplitPartFromModal(2)" class="sl-btn" style="background: linear-gradient(135deg, #d97706, #b45309); border-color: #fcd34d; color: #ffffff; padding: 7px 14px; font-size: 0.82rem;" title="طباعة إذن القسم الثاني: الكمية المتبقية (2) برقم التقسيم /2">🖨️ طباعة القسم الثاني (2) — /2</button>
                <button type="button" onclick="printSlOfficialNote()" class="sl-btn" style="background: rgba(56,189,248,0.18); border-color: rgba(56,189,248,0.45); color: #38bdf8; padding: 7px 14px; font-size: 0.82rem;">طباعة الإذن</button>
                <button type="button" onclick="printSlBarcodeStickers()" class="sl-btn" style="background: rgba(124,58,237,0.22); border-color: #c084fc; color: #e9d5ff; padding: 7px 14px; font-size: 0.82rem;">ملصق باركود</button>
                <button type="button" onclick="shareSlWhatsApp()" class="sl-btn" style="background: rgba(34,197,94,0.18); border-color: rgba(34,197,94,0.45); color: #4ade80; padding: 7px 14px; font-size: 0.82rem;">واتساب</button>
                <button type="button" id="sl-btn-undo" onclick="undoSlTransaction()" class="sl-btn" style="background: rgba(239,68,68,0.16); border-color: rgba(239,68,68,0.45); color: #fca5a5; padding: 7px 14px; font-size: 0.82rem;">عكس الحركة</button>
                <button type="button" id="sl-btn-admin-l4" onclick="executeSlAdminClosureL4()" class="sl-btn" style="display:none; background: linear-gradient(135deg, #8b5cf6, #6d28d9); border-color: #c4b5fd; color: #ffffff;">اعتماد L4</button>
                <button type="button" id="sl-btn-execute" onclick="executeSlTransaction()" class="sl-btn" style="padding: 9px 24px; font-size: 0.94rem; background: linear-gradient(135deg, #059669, #047857); border-color: #34d399; color: #ffffff; box-shadow: 0 4px 16px rgba(5,150,105,0.35);">حفظ واعتماد الدفعة</button>
            </div>
        </div>
            </div>
        </div>

    </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 1: MOBILE CAMERA DIRECT BRIDGE (ربط كاميرا الجوال باللابتوب)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-mobile-bridge-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.90); z-index:99999; align-items:center; justify-content:center; padding:14px; overflow-y:auto;">
        <div class="sl-glass-card" style="width:100%; max-width:720px; border:2px solid var(--sl-primary-light); padding:0;">
            <div style="background:linear-gradient(135deg, #064e3b, #0284c7); padding:13px 20px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#fff;">📷 مركز المسح الضوئي الموحد QR (كاميرا الجهاز + كاميرا الجوال + الاعتماد الفوري)</h3>
                </div>
                <button type="button" onclick="stopSlLocalCamera(); closeSlMobileBridgeModal();" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="padding:16px; display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; padding:8px 12px; border-radius:10px; background:rgba(16,185,129,0.14); border:1.5px solid rgba(52,211,153,0.45);">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <input type="checkbox" id="sl-auto-execute-qr" checked style="display:none;">
                        <span style="font-size:0.82rem; font-weight:900; color:#7dd3fc;">وضع مسح الجوال:</span>
                        <button type="button" id="sl-qr-mode-full-btn" onclick="document.getElementById('sl-auto-execute-qr').checked=true; this.style.opacity='1'; document.getElementById('sl-qr-mode-split-btn').style.opacity='0.55';" class="sl-btn sl-act-mini" style="background:linear-gradient(135deg,#059669,#047857); border-color:#6ee7b7; color:#fff; padding:5px 11px !important;">✅ استلام/تسليم كامل فوري 100%</button>
                        <button type="button" id="sl-qr-mode-split-btn" onclick="document.getElementById('sl-auto-execute-qr').checked=false; this.style.opacity='1'; document.getElementById('sl-qr-mode-full-btn').style.opacity='0.55';" class="sl-btn sl-act-mini" style="background:linear-gradient(135deg,#d97706,#b45309); border-color:#fcd34d; color:#fff; padding:5px 11px !important; opacity:0.55;">✂️ فتح للتقسيم الجزئي (/1 و /2)</button>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" onclick="startSlLocalCamera()" class="sl-btn" style="padding:6px 13px; font-size:0.80rem; background:linear-gradient(135deg,#0284c7,#0369a1); border-color:#7dd3fc; color:#fff;">💻 تشغيل كاميرا هذا الجهاز</button>
                        <label class="sl-btn" style="padding:6px 12px; font-size:0.78rem; background:rgba(168,85,247,0.22); border-color:#c084fc; color:#e9d5ff; cursor:pointer; margin:0;">
                            🖼️ قراءة صورة QR
                            <input type="file" accept="image/*" onchange="window.scanSlQrFromImageFile(this)" style="display:none;">
                        </label>
                    </div>
                </div>
                <div id="sl-qr-reader-wrap" style="display:none; flex-direction:column; align-items:center; gap:8px; width:100%; padding:10px; background:rgba(2,6,23,0.92); border:2px solid #38bdf8; border-radius:12px; box-sizing:border-box;">
                    <div id="sl-qr-reader" style="width:100%; max-width:360px; border-radius:10px; overflow:hidden; border:2px solid var(--sl-primary-light); min-height:240px; background:#000;"></div>
                    <div id="sl-local-cam-status" style="font-size:0.78rem; font-weight:800; color:#7dd3fc;">جارٍ تشغيل كاميرا الجهاز...</div>
                    <button type="button" onclick="stopSlLocalCamera()" class="sl-btn" style="background:rgba(239,68,68,0.2); border-color:#ef4444; color:#fca5a5; padding:5px 14px;">إيقاف كاميرا الجهاز</button>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(230px, 1fr)); gap:14px; align-items:center;">
                    <div style="background:#ffffff; padding:12px; border-radius:14px; text-align:center; border:3px solid var(--sl-primary);">
                        <img id="sl-bridge-qr-img" src="" alt="Pairing QR" style="width:175px; height:175px; display:block; margin:0 auto;">
                        <div style="color:#0f172a; font-weight:900; font-size:0.80rem; margin-top:5px;">أو امسح هذا الكود بكاميرا الجوال للربط اللاسلكي</div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:10px; font-size:0.82rem; line-height:1.6;">
                        <div style="background:rgba(15,23,42,0.8); padding:11px; border-radius:11px; border:1px solid rgba(148,163,184,0.2);">
                            <div><b>1. كاميرا الجهاز:</b> اضغط «تشغيل كاميرا هذا الجهاز» بالأعلى لاستخدام كاميرا اللابتوب/التابلت أو قراءة صورة باركود.</div>
                            <div><b>2. كاميرا الجوال:</b> امسح مربع الربط بهاتفك ليتحول الجوال إلى قارئ باركود لاسلكي متتابع.</div>
                            <div style="color:#fca5a5;"><b>3. حارس التكرار:</b> أي إذن مستلم مسبقاً يُرفض تلقائياً بنغمة تحذير.</div>
                        </div>
                        <a id="sl-bridge-open-link" href="mobile-qr-gun.php" target="_blank" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; text-decoration:none; padding:9px;">
                            فتح عدسة الجوال المباشرة في صفحة جديدة
                        </a>
                    </div>
                </div>
                <div style="background:rgba(2,6,23,0.8); border:1px solid rgba(148,163,184,0.25); border-radius:12px; padding:10px 14px; max-height:140px; overflow-y:auto;">
                    <div style="font-size:0.78rem; color:#94a3b8; font-weight:900; margin-bottom:6px;">⚡ سجل المسحات المباشرة من الجوال:</div>
                    <div id="sl-bridge-live-feed" style="font-size:0.84rem; color:#cbd5e1;">بانتظار تمرير أول باركود...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 2: FONT SIZE & COLOR CUSTOMIZER (مأخوذة من شاشة المشتريات ومطورة)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-font-modal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.85);  z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card modal-card" style="width:100%; max-width:480px; padding:22px; border:2px solid #a855f7;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0; font-size:1.1rem; font-weight:900; color:#fff;">🎨 تخصيص حجم ولون الخط والأعمدة (حفظ نهائي)</h3>
                <button type="button" onclick="document.getElementById('sl-font-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="font-size:0.84rem; font-weight:900; color:#cbd5e1; display:block; margin-bottom:6px;">📏 حجم خط الجداول والأرقام (<span id="sl-font-size-lbl">16</span>px):</label>
                    <input type="range" id="sl-font-size-range" min="13" max="24" value="16" oninput="document.getElementById('sl-font-size-lbl').textContent=this.value; applySlTypography(this.value, document.getElementById('sl-font-color-picker').value)" style="width:100%; accent-color:#a855f7;">
                </div>
                <div>
                    <label style="font-size:0.84rem; font-weight:900; color:#cbd5e1; display:block; margin-bottom:6px;">🎨 لون النص الأساسي:</label>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        <input type="color" id="sl-font-color-picker" value="#f8fafc" oninput="document.getElementById('sl-font-color-text').value=this.value; applySlTypography(document.getElementById('sl-font-size-range').value, this.value);" onchange="document.getElementById('sl-font-color-text').value=this.value; applySlTypography(document.getElementById('sl-font-size-range').value, this.value);" style="width:46px; height:36px; border:none; border-radius:8px; cursor:pointer;">
                        <input type="text" id="sl-font-color-text" value="#f8fafc" class="sl-input" oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value.trim())){document.getElementById('sl-font-color-picker').value=this.value.trim(); applySlTypography(document.getElementById('sl-font-size-range').value, this.value.trim());}" style="width:92px; padding:5px 8px !important; text-align:center; font-family:monospace; font-size:0.85rem !important;">
                        <button type="button" onclick="const defC = document.documentElement.classList.contains('light-theme') ? '#120500' : '#ffffff'; document.getElementById('sl-font-color-picker').value=defC; document.getElementById('sl-font-color-text').value=defC; applySlTypography(16, defC);" class="sl-btn" style="font-size:0.78rem;">افتراضي</button>
                        <button type="button" onclick="document.getElementById('sl-font-color-picker').value='#047857'; document.getElementById('sl-font-color-text').value='#047857'; applySlTypography(document.getElementById('sl-font-size-range').value, '#047857')" class="sl-btn" style="font-size:0.78rem; color:#10b981;">زمردي</button>
                        <button type="button" onclick="document.getElementById('sl-font-color-picker').value='#0369a1'; document.getElementById('sl-font-color-text').value='#0369a1'; applySlTypography(document.getElementById('sl-font-size-range').value, '#0369a1')" class="sl-btn" style="font-size:0.78rem; color:#38bdf8;">سماوي</button>
                        <button type="button" onclick="document.getElementById('sl-font-color-picker').value='#b45309'; document.getElementById('sl-font-color-text').value='#b45309'; applySlTypography(document.getElementById('sl-font-size-range').value, '#b45309')" class="sl-btn" style="font-size:0.78rem; color:#f59e0b;">ذهبي</button>
                    </div>
                </div>
                <button type="button" onclick="saveSlCustomConfigPermanently(); document.getElementById('sl-font-modal').style.display='none';" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; padding:10px; margin-top:6px; font-weight:900;">
                    💾 حفظ نهائي في قاعدة البيانات وإغلاق
                </button>
            </div>
        </div>
    </div>

    <script>
    // 🎡 ضمان تحريك الشاشة بالكامل بالرولر (Mouse Wheel Freedom Engine) صعوداً وهبوطاً بحرية تامة
    (function () {
        function unlockScreenScroll() {
            document.documentElement.style.setProperty('overflow-y', 'scroll', 'important');
            document.documentElement.style.setProperty('overflow-x', 'hidden', 'important');
            document.documentElement.style.setProperty('height', 'auto', 'important');
            if (document.body) {
                document.body.style.setProperty('overflow', 'visible', 'important');
                document.body.style.setProperty('overflow-x', 'visible', 'important');
                document.body.style.setProperty('overflow-y', 'visible', 'important');
                document.body.style.setProperty('height', 'auto', 'important');
                document.body.style.setProperty('overscroll-behavior', 'auto', 'important');
            }
        }
        unlockScreenScroll();
        document.addEventListener('DOMContentLoaded', unlockScreenScroll);
        window.addEventListener('load', unlockScreenScroll);

        // Ensure wheel events anywhere on the main screen always scroll the page freely
        window.addEventListener('wheel', function (e) {
            if (!e.deltaY || e.ctrlKey) return;
            const openModal = e.target.closest && e.target.closest('#msg-universal-modal.open, #sl-mobile-bridge-modal[style*="flex"], #sl-font-modal[style*="flex"], #msg-pwa-app-install-modal[style*="flex"], #msg-full-offline-install-modal[style*="flex"], #msg-universal-7themes-modal[style*="flex"]');
            if (openModal) return;

            const beforeY = window.scrollY || document.documentElement.scrollTop || 0;
            requestAnimationFrame(function () {
                const afterY = window.scrollY || document.documentElement.scrollTop || 0;
                if (Math.abs(afterY - beforeY) < 1) {
                    window.scrollBy(0, e.deltaY);
                    if (document.scrollingElement) {
                        document.scrollingElement.scrollTop += e.deltaY;
                    }
                }
            });
        }, { passive: true });
    })();
    </script>
    <script defer src="js/magaies-offline-sync.js?v=20261014"></script>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo filemtime('js/app.js'); ?>"></script>
    <script defer src="js/print-engine.js"></script>
    <script defer src="js/md-delayed-tooltips.js?v=<?php echo file_exists('js/md-delayed-tooltips.js') ? filemtime('js/md-delayed-tooltips.js') : '2027'; ?>"></script>
    <script>(function(){var l=0,q=[],v="<?php echo file_exists('js/magaies-smart-guide.js') ? filemtime('js/magaies-smart-guide.js') : '2027'; ?>";window.loadMagaiesSmartGuide=function(c){if(l&&window.openSmartGuide){if(c)c();return;}if(c)q.push(c);if(l)return;l=1;var s=document.createElement('script');s.src='js/magaies-smart-guide.js?v='+v;s.onload=function(){q.splice(0).forEach(function(fn){try{fn();}catch(_){}});};document.body.appendChild(s);};window.addEventListener('keydown',function(e){if(e.key==='F1'){e.preventDefault();window.loadMagaiesSmartGuide(function(){if(window.openSmartGuide)window.openSmartGuide();});}});})();</script>
    <script defer src="js/full-receipt-confirm-2027.js?v=<?php echo file_exists('js/full-receipt-confirm-2027.js') ? filemtime('js/full-receipt-confirm-2027.js') : '2027'; ?>"></script>
    <script defer src="js/smart-logistics.js?v=<?php echo file_exists('js/smart-logistics.js') ? filemtime('js/smart-logistics.js') : '20261104_v5'; ?>"></script>
    <script>(function(){var l=0,q=[];window.ensureGlobalBgMedia=function(c){if(l&&typeof window.quickHeaderPlay==='function'){if(c)c();return;}if(c)q.push(c);if(document.querySelector('script[data-lazy-bgm]'))return;var s=document.createElement('script');s.src='js/global_background_media.js?v=<?= @filemtime(__DIR__."/js/global_background_media.js")?:2027 ?>';s.dataset.lazyBgm='1';s.onload=function(){l=1;q.splice(0).forEach(function(fn){try{fn();}catch(_){}});};document.body.appendChild(s);};['quickHeaderPlay','setHeaderMediaVolume','quickHeaderStop','openBackgroundPopout','toggleHeaderMediaDropdown','playPersistentMedia'].forEach(function(fn){if(typeof window[fn]!=='function')window[fn]=function(){var a=arguments;window.ensureGlobalBgMedia(function(){if(typeof window[fn]==='function')window[fn].apply(window,a);});};});try{if(localStorage.getItem('erp_bg_media_playing')==='1'||localStorage.getItem('magaies_live_tv_active')==='1')window.addEventListener('DOMContentLoaded',function(){setTimeout(function(){window.ensureGlobalBgMedia();},300);},{once:true});}catch(_){}})();</script>
    <script src="js/magaiees_back.js?v=<?php echo file_exists('js/magaiees_back.js') ? filemtime('js/magaiees_back.js') : '20261126_v24'; ?>" defer></script>
    <script defer src="js/elsajana-universal-ui-2027.js?v=<?php echo file_exists('js/elsajana-universal-ui-2027.js') ? filemtime('js/elsajana-universal-ui-2027.js') : 1; ?>"></script>
    <!-- ═══════════════════════════════════════════════════════════════════
         💎 تناغم بصري موحد مع شاشة المشتريات (خلفية سوداء #030712 + مناطق كحلية داكنة متدرجة + إضاءة الحواف عند المرور)
         ═══════════════════════════════════════════════════════════════════ -->
    <style id="sl-purchase-harmony-theme">
        @keyframes purGlassWaveSweep { 0%, 62% { background-position: 220% 0, 0 0; } 88%, 100% { background-position: -140% 0, 0 0; } }
        :root.light-theme, :root.light-theme[data-light-tone="ivory_default"] { --lt-canvas-1:#efe5d0; --lt-canvas-2:#e5d6ba; --lt-hdr-1:#f5ebd6; --lt-hdr-2:#ecdec2; --lt-hdr-3:#e2cfa9; --lt-card-1:#ffffff; --lt-card-2:#fcf7ee; --lt-sub-1:#ffffff; --lt-sub-2:#fbf4e6; --lt-th-1:#faf3e3; --lt-th-2:#f1e4ca; --lt-row-odd:#ffffff; --lt-row-even:#fefcf7; --lt-border:#5c2808; --lt-border-hover:#b45309; --lt-glow:rgba(245,158,11,0.45); --lt-ink:#120500; }
        :root.light-theme[data-light-tone="royal_bronze"] { --lt-canvas-1:#e6cbb0; --lt-canvas-2:#d9b694; --lt-hdr-1:#eed7be; --lt-hdr-2:#e3c4a2; --lt-hdr-3:#d7b189; --lt-card-1:#fffbf7; --lt-card-2:#f9efe3; --lt-sub-1:#fffdfa; --lt-sub-2:#f6e7d5; --lt-th-1:#f5e5d1; --lt-th-2:#ebd2b7; --lt-row-odd:#ffffff; --lt-row-even:#fcf6ee; --lt-border:#4a2106; --lt-border-hover:#9a3412; --lt-glow:rgba(217,119,6,0.48); --lt-ink:#140600; }
        :root.light-theme[data-light-tone="platinum_gray"] { --lt-canvas-1:#d9e3ee; --lt-canvas-2:#cad7e6; --lt-hdr-1:#e2ebf5; --lt-hdr-2:#d4e1ef; --lt-hdr-3:#c5d6e8; --lt-card-1:#ffffff; --lt-card-2:#f2f7fc; --lt-sub-1:#ffffff; --lt-sub-2:#eef4fb; --lt-th-1:#ecf3fa; --lt-th-2:#dde8f5; --lt-row-odd:#ffffff; --lt-row-even:#f7fafe; --lt-border:#1e293b; --lt-border-hover:#0284c7; --lt-glow:rgba(56,189,248,0.45); --lt-ink:#020617; }
        :root.light-theme[data-light-tone="crystal_silver"] { --lt-canvas-1:#e4e9f0; --lt-canvas-2:#d6dde7; --lt-hdr-1:#ecf0f5; --lt-hdr-2:#e0e6ee; --lt-hdr-3:#d3dbe6; --lt-card-1:#ffffff; --lt-card-2:#f5f7fb; --lt-sub-1:#ffffff; --lt-sub-2:#f1f4f9; --lt-th-1:#eff3f8; --lt-th-2:#e3e8f0; --lt-row-odd:#ffffff; --lt-row-even:#f9fafc; --lt-border:#111827; --lt-border-hover:#2563eb; --lt-glow:rgba(59,130,246,0.45); --lt-ink:#030712; }
        :root.light-theme[data-light-tone="sage_pearl"] { --lt-canvas-1:#d4eee1; --lt-canvas-2:#c1e4d3; --lt-hdr-1:#ddf3e8; --lt-hdr-2:#cbead9; --lt-hdr-3:#b8e0cb; --lt-card-1:#ffffff; --lt-card-2:#eefaf4; --lt-sub-1:#ffffff; --lt-sub-2:#e8f8f0; --lt-th-1:#e5f6ed; --lt-th-2:#d3efe0; --lt-row-odd:#ffffff; --lt-row-even:#f6fdf9; --lt-border:#064e3b; --lt-border-hover:#059669; --lt-glow:rgba(16,185,129,0.45); --lt-ink:#022c22; }
        :root.light-theme[data-light-tone="sky_alabaster"] { --lt-canvas-1:#d3e9fb; --lt-canvas-2:#beddf7; --lt-hdr-1:#dceffd; --lt-hdr-2:#cae4fa; --lt-hdr-3:#b7d9f6; --lt-card-1:#ffffff; --lt-card-2:#eef7fe; --lt-sub-1:#ffffff; --lt-sub-2:#e7f4fd; --lt-th-1:#e4f2fd; --lt-th-2:#d1e9fb; --lt-row-odd:#ffffff; --lt-row-even:#f6fbff; --lt-border:#0c4a6e; --lt-border-hover:#0284c7; --lt-glow:rgba(14,165,233,0.45); --lt-ink:#082f49; }
        :root.light-theme[data-light-tone="warm_mocha_dark"] { --lt-canvas-1:#d2baa1; --lt-canvas-2:#c1a384; --lt-hdr-1:#decab4; --lt-hdr-2:#cfb69b; --lt-hdr-3:#bfa182; --lt-card-1:#fdfaf6; --lt-card-2:#f6ede2; --lt-sub-1:#fefcf9; --lt-sub-2:#f2e6d8; --lt-th-1:#f0e2d2; --lt-th-2:#e4d1bc; --lt-row-odd:#ffffff; --lt-row-even:#faf4ec; --lt-border:#3e1b04; --lt-border-hover:#7c2d12; --lt-glow:rgba(180,83,9,0.48); --lt-ink:#0f0400; }
        :root.light-theme[data-light-tone="deep_slate_dark"] { --lt-canvas-1:#c1cedf; --lt-canvas-2:#aebfd4; --lt-hdr-1:#ced9e8; --lt-hdr-2:#bdcce0; --lt-hdr-3:#acbed6; --lt-card-1:#f9fbfe; --lt-card-2:#eef3fa; --lt-sub-1:#fcfdff; --lt-sub-2:#e8eff8; --lt-th-1:#e4ecf6; --lt-th-2:#d5e1f0; --lt-row-odd:#ffffff; --lt-row-even:#f4f8fc; --lt-border:#0f172a; --lt-border-hover:#1d4ed8; --lt-glow:rgba(37,99,235,0.45); --lt-ink:#020617; }

        #sl-main-wrapper { width: calc(100% - 24px) !important; max-width: calc(100% - 24px) !important; margin: 8px auto 60px auto !important; padding: 0 !important; display: flex !important; flex-direction: column !important; gap: 8px !important; box-sizing: border-box !important; }
        #sl-main-wrapper > * { width: 100% !important; max-width: 100% !important; margin: 0 !important; box-sizing: border-box !important; }
        #sl-main-wrapper svg, .modal-overlay svg { stroke-width: 2.8px !important; }

        /* DARK THEME (دستور المشتريات الليلي) */
        html:not(.light-theme):not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) body { background: #010308 !important; color: #ffffff !important; }
        html:not(.light-theme):not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-glass-card,
        html:not(.light-theme):not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-floating-summary {
            background-image: linear-gradient(115deg, transparent 36%, rgba(255,255,255,0.08) 48%, rgba(56,189,248,0.14) 50%, transparent 64%), linear-gradient(145deg, #1b3159 0%, #132341 100%) !important;
            background-size: 250% 100%, 100% 100% !important; border: 2px solid rgba(56,189,248,0.80) !important; border-radius: 15px !important;
            box-shadow: 0 12px 32px rgba(0,0,0,0.92), inset 0 1.5px 1px rgba(255,255,255,0.28) !important; animation: none; /* cpu-opt */
        }
        html:not(.light-theme):not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-glass-card:hover,
        html:not(.light-theme):not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-glass-card:focus-within {
            border-color: #38bdf8 !important; box-shadow: 0 0 0 2px rgba(56,189,248,0.5), 0 0 24px rgba(56,189,248,0.48), 0 14px 34px rgba(0,0,0,0.94) !important;
        }
        html:not(.light-theme):not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-sub-region {
            background: linear-gradient(135deg, #1d315c 0%, #162649 100%) !important; border: 1.5px solid rgba(56,189,248,0.48) !important; border-radius: 10px !important; padding: 7px 11px !important;
        }
        html:not(.light-theme):not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-supplier-region {
            background: linear-gradient(135deg, #2d4f91 0%, #1f396d 55%, #3b2a14 100%) !important; border: 2px solid #fbbf24 !important;
        }
        html:not(.light-theme):not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-input {
            background: #0b1120 !important; border: 1.5px solid rgba(56,189,248,0.48) !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 900 !important;
        }

        /* LIGHT THEME & 8 TONES (دستور المشتريات النهاري — تفتيح المناطق وتغميق الخطوط 900) */
        :root.light-theme body { background: linear-gradient(180deg, var(--lt-canvas-1, #efe5d0) 0%, var(--lt-canvas-2, #e5d6ba) 100%) !important; color: var(--lt-ink, #120500) !important; }
        :root.light-theme .sl-glass-card, :root.light-theme .sl-floating-summary, :root.light-theme #sl-cols-drawer {
            background-image: linear-gradient(115deg, transparent 36%, rgba(255,255,255,0.88) 48%, rgba(255,253,245,0.96) 50%, transparent 64%), linear-gradient(145deg, var(--lt-card-1, #ffffff) 0%, var(--lt-card-2, #fcf7ee) 100%) !important;
            background-size: 250% 100%, 100% 100% !important; border: 3px solid var(--lt-border, #5c2808) !important; border-radius: 15px !important;
            box-shadow: 0 12px 28px rgba(43,17,3,0.14), inset 0 2px 0 #ffffff !important; color: var(--lt-ink, #120500) !important; animation: none; /* cpu-opt */
        }
        :root.light-theme #sl-top-cockpit-bar {
            background-image: linear-gradient(115deg, transparent 34%, rgba(255,255,255,0.9) 50%, transparent 66%), linear-gradient(160deg, var(--lt-hdr-1, #f5ebd6) 0%, var(--lt-hdr-2, #ecdec2) 52%, var(--lt-hdr-3, #e2cfa9) 100%) !important;
            border: 3px solid var(--lt-border, #5c2808) !important;
        }
        :root.light-theme .sl-sub-region, :root.light-theme .sl-unit-box {
            background: linear-gradient(135deg, var(--lt-sub-1, #ffffff) 0%, var(--lt-sub-2, #fbf4e6) 100%) !important;
            border: 2.2px solid var(--lt-border, #5c2808) !important; border-radius: 11px !important; padding: 7px 11px !important;
            box-shadow: 0 4px 12px rgba(43,17,3,0.10), inset 0 1.5px 0 #ffffff !important; color: var(--lt-ink, #120500) !important;
        }
        :root.light-theme .sl-unit-box > div:nth-child(2) { background: #ffffff !important; border: 2px solid var(--lt-border, #5c2808) !important; }
        :root.light-theme .sl-supplier-region { background: linear-gradient(135deg, #fffbeb 0%, #fde68a 100%) !important; border: 2.5px solid #92400e !important; }
        :root.light-theme .sl-field-badge {
            background: linear-gradient(180deg, var(--lt-th-1, #faf3e3) 0%, var(--lt-th-2, #f1e4ca) 100%) !important;
            border: 2px solid var(--lt-border, #5c2808) !important; color: var(--lt-ink, #120500) !important; -webkit-text-fill-color: var(--lt-ink, #120500) !important; font-weight: 900 !important; font-size: 0.84rem !important;
        }
        :root.light-theme .sl-input, :root.light-theme .sl-input:focus {
            background: #ffffff !important; border: 2px solid var(--lt-border, #5c2808) !important;
            color: var(--lt-ink, #120500) !important; -webkit-text-fill-color: var(--lt-ink, #120500) !important; font-weight: 900 !important;
        }
        :root.light-theme .sl-input::placeholder { color: rgba(100,116,139,0.45) !important; -webkit-text-fill-color: rgba(100,116,139,0.45) !important; font-weight: 600 !important; }
        :root.light-theme #sl-registry-title, :root.light-theme #sl-doc-card-title, :root.light-theme #sl-grid-title,
        :root.light-theme #sl-qr-camera-card h3, :root.light-theme #sl-lbl-party-name, :root.light-theme #sl-lbl-date,
        :root.light-theme #sl-lbl-inv-no, :root.light-theme .sl-floating-summary div, :root.light-theme .sl-floating-summary strong,
        :root.light-theme #sl-cols-drawer span, :root.light-theme #sl-cols-drawer label, :root.light-theme #sl-camera-placeholder div,
        :root.light-theme #sl-qr-camera-card label span, :root.light-theme .sl-unit-box span, :root.light-theme [id^="sl-row-live-eq-"],
        :root.light-theme [id^="sl-row-live-eq-"] strong, :root.light-theme [id^="sl-row-stock-badge-"] {
            color: var(--lt-ink, #120500) !important; -webkit-text-fill-color: var(--lt-ink, #120500) !important; font-weight: 900 !important; text-shadow: none !important;
        }
        :root.light-theme #sl-qr-camera-card label { background: var(--lt-sub-2, #fbf4e6) !important; border: 2px solid var(--lt-border, #5c2808) !important; }
        :root.light-theme .sl-table thead th {
            background-image: linear-gradient(115deg, transparent 26%, rgba(255,255,255,0.78) 48%, rgba(254,240,138,0.45) 50%, transparent 72%), linear-gradient(180deg, var(--lt-th-1, #faf3e3) 0%, var(--lt-th-2, #f1e4ca) 100%) !important;
            background-size: 250% 100%, 100% 100% !important; color: var(--lt-ink, #120500) !important; -webkit-text-fill-color: var(--lt-ink, #120500) !important;
            font-size: 1.02rem !important; font-weight: 900 !important; border-top: 2.5px solid var(--lt-border, #5c2808) !important; border-bottom: 2.5px solid var(--lt-border, #5c2808) !important;
        }
        :root.light-theme .sl-table tbody tr td {
            background: var(--lt-row-odd, #ffffff) !important; color: var(--lt-ink, #120500) !important; border-bottom: 1.8px solid rgba(92,40,8,0.32) !important; font-weight: 900 !important;
        }
        :root.light-theme .sl-table tbody tr:nth-child(even) td { background: var(--lt-row-even, #fefcf7) !important; }
        :root.light-theme .sl-table tbody tr:hover td, :root.light-theme .sl-table tbody tr.sl-selected-row td { background: var(--lt-th-1, #faf3e3) !important; }
        :root.light-theme .sl-table tbody td div, :root.light-theme .sl-table tbody td strong, :root.light-theme .sl-table tbody td span:not(.sl-badge):not(.pur-price-lamp):not(.pur-price-subtxt) {
            color: var(--lt-ink, #120500) !important; -webkit-text-fill-color: var(--lt-ink, #120500) !important; font-weight: 900 !important;
        }
        :root.light-theme #sl-registry-tbody td span[onclick*="loadSlDocument"] {
            background: var(--lt-sub-2, #fbf4e6) !important; border: 1.5px solid var(--lt-border, #5c2808) !important; color: var(--lt-ink, #120500) !important;
        }
        :root.light-theme .sl-badge-pending { background: #fef3c7 !important; color: #78350f !important; border: 2px solid #d97706 !important; }
        :root.light-theme .sl-badge-partial { background: #e0f2fe !important; color: #0c4a6e !important; border: 2px solid #0284c7 !important; }
        :root.light-theme .sl-badge-done { background: #d1fae5 !important; color: #064e3b !important; border: 2px solid #059669 !important; }

        /* FIXED CENTERED "أستاذ" BUTTON (#sl-supplier-ledger-btn) — NEVER JITTERS ON HOVER */
        #sl-supplier-ledger-btn {
            position: absolute !important; left: 8px !important; top: 50% !important; transform: translateY(-50%) !important;
            height: 34px !important; padding: 0 14px !important; border-radius: 8px !important; cursor: pointer !important;
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important; border: 1.8px solid #7dd3fc !important; border-bottom: 3px solid #082f49 !important;
            color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-size: 0.90rem !important; font-weight: 900 !important;
            box-shadow: 0 0 14px rgba(56,189,248,0.55), 0 4px 10px rgba(2,132,199,0.45), inset 0 1.5px 1px rgba(255,255,255,0.85) !important;
        }
        #sl-supplier-ledger-btn:hover {
            left: 8px !important; top: 50% !important; transform: translateY(-50%) scale(1.06) !important; border-color: #fde047 !important;
            box-shadow: 0 0 0 2.5px rgba(250,204,21,0.75), 0 0 20px rgba(56,189,248,0.9), inset 0 2px 2px #ffffff !important;
        }

        /* FINANCIAL STRIP COLORED 3D CAPSULES & "جنيه ⇄ دولار" TOGGLE BUTTON */
        .sl-fin-strip-bar {
            display: flex !important; align-items: center !important; gap: 6px !important; flex-wrap: wrap !important;
            padding: 6px 8px !important; border-radius: 11px !important;
            background: rgba(15,23,42,0.55) !important; border: 1.5px solid rgba(56,189,248,0.42) !important;
        }
        :root.light-theme .sl-fin-strip-bar {
            background: linear-gradient(135deg, var(--lt-sub-1, #ffffff) 0%, var(--lt-sub-2, #fbf4e6) 100%) !important; border: 2.2px solid var(--lt-border, #5c2808) !important;
        }
        #sl-fin-strip-currency-btn {
            height: 32px !important; min-width: 82px !important; padding: 0 12px !important; border-radius: 8px !important; cursor: pointer !important;
            font-size: 0.86rem !important; font-weight: 900 !important; font-family: 'Cairo','Tajawal',sans-serif !important;
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important; border: 1.8px solid #7dd3fc !important; border-bottom: 3px solid #082f49 !important;
            color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; box-shadow: 0 4px 12px rgba(2,132,199,0.48), inset 0 1.5px 1px rgba(255,255,255,0.85) !important;
        }
        #sl-fin-strip-currency-btn[data-cur-mode="USD"] {
            background: linear-gradient(180deg, #10b981 0%, #059669 52%, #047857 100%) !important; border: 1.8px solid #6ee7b7 !important; border-bottom: 3px solid #022c22 !important;
        }
        .sl-fin-pill {
            flex: 1 1 auto !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important;
            height: 32px !important; padding: 0 10px !important; border-radius: 8px !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;
            font-weight: 900 !important; text-shadow: 0 1.5px 3px rgba(0,0,0,0.95) !important; box-shadow: inset 0 1.5px 1px rgba(255,255,255,0.35), 0 2px 8px rgba(0,0,0,0.25) !important;
        }
        .sl-fin-pill span { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 900 !important; }
        .sl-fin-pill.pill-ledger { background: linear-gradient(180deg, #334155 0%, #0f172a 100%) !important; border: 1.5px solid #94a3b8 !important; }
        .sl-fin-pill.pill-debt { background: linear-gradient(180deg, #e11d48 0%, #9f1239 100%) !important; border: 1.5px solid #fda4af !important; }
        .sl-fin-pill.pill-checks { background: linear-gradient(180deg, #d97706 0%, #92400e 100%) !important; border: 1.5px solid #fcd34d !important; }
        .sl-fin-pill.pill-limit { background: linear-gradient(180deg, #0284c7 0%, #075985 100%) !important; border: 1.5px solid #7dd3fc !important; }
        .sl-fin-pill.pill-rem { background: linear-gradient(180deg, #059669 0%, #065f46 100%) !important; border: 1.5px solid #6ee7b7 !important; }

        /* 20PX 3D PRICE RADAR LAMP & RED/GREEN VARIANCE ARROWS (▲ بالأحمر / ▼ بالأخضر) */
        .pur-price-lamp {
            position: absolute !important; left: 8px !important; top: 14px !important; width: 20px !important; height: 20px !important;
            border-radius: 50% !important; display: inline-block !important; cursor: pointer !important; z-index: 25 !important; border: 2.2px solid rgba(255,255,255,0.95) !important;
        }
        .pur-price-lamp.lamp-red { background: radial-gradient(circle at 35% 35%, #fecdd3, #ff1e42 52%, #881337) !important; box-shadow: 0 0 14px #ff1e42, inset 0 2px 3px #ffffff !important; }
        .pur-price-lamp.lamp-amber { background: radial-gradient(circle at 35% 35%, #fef08a, #f59e0b 52%, #92400e) !important; box-shadow: 0 0 13px #fbbf24, inset 0 2px 3px #ffffff !important; }
        .pur-price-lamp.lamp-green { background: radial-gradient(circle at 35% 35%, #a7f3d0, #10b981 52%, #064e3b) !important; box-shadow: 0 0 13px #10b981, inset 0 2px 3px #ffffff !important; }
        .pur-price-lamp.lamp-blue { background: radial-gradient(circle at 35% 35%, #bae6fd, #0ea5e9 52%, #0c4a6e) !important; box-shadow: 0 0 13px #38bdf8, inset 0 2px 3px #ffffff !important; }
        .pur-price-subtxt {
            position: absolute !important; bottom: 2px !important; left: 50% !important; transform: translateX(-50%) !important;
            font-size: 0.82rem !important; font-weight: 900 !important; white-space: nowrap !important; direction: ltr !important; cursor: pointer !important;
        }
        .pur-price-subtxt[data-lamp-state="red"] { color: #ff4d6d !important; -webkit-text-fill-color: #ff4d6d !important; }
        :root.light-theme .pur-price-subtxt[data-lamp-state="red"] { color: #e11d48 !important; -webkit-text-fill-color: #e11d48 !important; }
        .pur-price-subtxt[data-lamp-state="amber"] { color: #fbbf24 !important; -webkit-text-fill-color: #fbbf24 !important; }
        :root.light-theme .pur-price-subtxt[data-lamp-state="amber"] { color: #b45309 !important; -webkit-text-fill-color: #b45309 !important; }
        .pur-price-subtxt[data-lamp-state="green"] { color: #34d399 !important; -webkit-text-fill-color: #34d399 !important; }
        :root.light-theme .pur-price-subtxt[data-lamp-state="green"] { color: #047857 !important; -webkit-text-fill-color: #047857 !important; }

        /* DUAL GRAND TOTAL CAPSULES (ج.س & $) */
        .sl-grand-tot-box {
            display: inline-flex !important; align-items: center !important; justify-content: center !important;
            padding: 5px 14px !important; border-radius: 10px !important; font-size: 1.18rem !important; font-weight: 900 !important;
            font-family: 'Outfit', 'Tajawal', sans-serif !important; box-shadow: 0 4px 12px rgba(0,0,0,0.25), inset 0 1.5px 1px rgba(255,255,255,0.6) !important;
        }
        .sl-grand-tot-box.tot-sdg { background: linear-gradient(180deg, #059669 0%, #065f46 100%) !important; border: 2px solid #6ee7b7 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        .sl-grand-tot-box.tot-usd { background: linear-gradient(180deg, #0284c7 0%, #075985 100%) !important; border: 2px solid #7dd3fc !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }

        /* HIGH-CONTRAST LIGHT & DARK BUTTONS, INPUTS, BORDERS & MODALS (دستور المشتريات 2027) */
        :root.light-theme body .sl-btn, html.light-theme body .sl-btn {
            background: linear-gradient(180deg, #27374d 0%, #0f172a 100%) !important; border: 2.2px solid #1e1b18 !important; border-bottom: 3px solid #020617 !important;
            color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 900 !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.85) !important; box-shadow: 0 3px 8px rgba(15,23,42,0.22) !important;
        }
        :root.light-theme body .sl-btn span:not(.sl-btn-cnt), html.light-theme body .sl-btn span:not(.sl-btn-cnt) { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        :root.light-theme body .sl-reg-flt-btn:not(.sl-flt-active), html.light-theme body .sl-reg-flt-btn:not(.sl-flt-active),
        :root.light-theme body #sl-pagination-bar .sl-btn:not(.sl-page-active), html.light-theme body #sl-pagination-bar .sl-btn:not(.sl-page-active) {
            background: #ffffff !important; border: 2.4px solid var(--lt-border, #3b1403) !important; border-bottom: 3.5px solid #1f0a00 !important;
            color: #120500 !important; -webkit-text-fill-color: #120500 !important; text-shadow: none !important;
        }
        :root.light-theme body .sl-reg-flt-btn.sl-flt-active, html.light-theme body .sl-reg-flt-btn.sl-flt-active,
        :root.light-theme body #sl-pagination-bar .sl-btn.sl-page-active, html.light-theme body #sl-pagination-bar .sl-btn.sl-page-active,
        :root.light-theme body #sl-btn-execute, html.light-theme body #sl-btn-execute {
            background: linear-gradient(180deg, #0284c7 0%, #075985 100%) !important; border: 2.4px solid #082f49 !important; border-bottom: 3.5px solid #031525 !important;
            color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;
        }
        :root.light-theme body .sl-btn[onclick*="Undo"], :root.light-theme body .sl-btn[onclick*="undo"], :root.light-theme body .sl-btn[onclick*="closeSlSplit"], :root.light-theme body .sl-btn[onclick*="fillAllSlQuantities(0)"], :root.light-theme body .sl-btn[onclick*="zero_variance"] {
            background: linear-gradient(180deg, #991b1b 0%, #650a0a 100%) !important; border: 2.2px solid #450a0a !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;
        }
        :root.light-theme body .sl-btn[onclick*="Print"], :root.light-theme body .sl-btn[onclick*="print"], :root.light-theme body .sl-btn[onclick*="WhatsApp"], :root.light-theme body .sl-btn[onclick*="Csv"], :root.light-theme body .sl-btn[onclick*="fillAllSlQuantities(1)"] {
            background: linear-gradient(180deg, #065f46 0%, #022c22 100%) !important; border: 2.2px solid #022c22 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;
        }
        :root.light-theme body .sl-btn[onclick*="EditDoc"], :root.light-theme body .sl-btn[onclick*="part2"], :root.light-theme body .sl-btn[onclick*="Modal(2)"] {
            background: linear-gradient(180deg, #78350f 0%, #451a03 100%) !important; border: 2.2px solid #2b0f02 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;
        }
        :root.light-theme body .sl-btn[onclick*="SplitInspectModal"], :root.light-theme body .sl-btn[onclick*="AIAudit"], :root.light-theme body #sl-reg-cols-btn {
            background: linear-gradient(180deg, #4c1d95 0%, #2e1065 100%) !important; border: 2.2px solid #1e0942 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;
        }
        .sl-floating-summary { position: static !important; bottom: auto !important; }
        #msg-universal-fixed-launcher, #msg-universal-fixed-launcher.is-scroll-floating, .msg-fixed-command-pod { position: static !important; top: auto !important; left: auto !important; right: auto !important; transform: none !important; }
        .sl-btn-cnt { display: inline-flex; align-items: center; justify-content: center; background: #1e0a02; color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; font-family: 'Inter', monospace; font-size: 0.78rem; font-weight: 900; padding: 1px 7px; border-radius: 99px; border: 1.5px solid #f59e0b; margin-right: 4px; }
        .sl-table th, .sl-table td { text-align: center !important; vertical-align: middle !important; }
        #sl-items-grid th, #sl-items-grid td { border-left: 2px solid rgba(125, 211, 252, 0.48) !important; border-bottom: 2px solid rgba(56, 189, 248, 0.52) !important; padding: 6px 7px !important; }
        #sl-items-grid th:last-child, #sl-items-grid td:last-child { border-left: none !important; }
        :root.light-theme #sl-items-grid, html.light-theme #sl-items-grid { border: 2.5px solid #3b1403 !important; }
        :root.light-theme #sl-items-grid th, :root.light-theme #sl-items-grid td { border-left: 2px solid rgba(59, 20, 3, 0.55) !important; border-bottom: 2px solid rgba(59, 20, 3, 0.62) !important; color: #120500 !important; }
        .sl-qty-primary-input, .sl-qty-base-input { width: 100% !important; height: 36px !important; min-height: 36px !important; text-align: center !important; padding: 3px 6px !important; font-size: 1.08rem !important; font-weight: 900 !important; font-family: 'Outfit','Cairo',sans-serif !important; background: #162a4d !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; border: 2px solid #38bdf8 !important; border-radius: 7px !important; }
        :root.light-theme body .sl-qty-primary-input, :root.light-theme body .sl-qty-base-input, html.light-theme body .sl-qty-primary-input, html.light-theme body .sl-qty-base-input { background: #ffffff !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 2.5px solid #3b1403 !important; box-shadow: inset 0 1px 3px rgba(0,0,0,0.12) !important; }
        :root.light-theme body #sl-split-inspect-modal, html.light-theme body #sl-split-inspect-modal { background: linear-gradient(180deg, var(--lt-canvas-1, #efe5d0) 0%, var(--lt-canvas-2, #e5d6ba) 100%) !important; }
        :root.light-theme body #sl-split-inspect-modal > div > div:first-child { background: var(--lt-hdr-2, #ecdec2) !important; border: 2.5px solid #3b1403 !important; }
        :root.light-theme body #sl-split-modal-hdr-title { color: #120500 !important; -webkit-text-fill-color: #120500 !important; }
        #sl-registry-card .sl-table { border-collapse: collapse !important; width: 100% !important; border: 2px solid rgba(56, 189, 248, 0.60) !important; }
        #sl-registry-card .sl-table th, #sl-registry-card .sl-table td { border-left: 2px solid rgba(125, 211, 252, 0.52) !important; border-bottom: 2px solid rgba(56, 189, 248, 0.58) !important; padding: 6px 8px !important; }
        #sl-registry-card .sl-table th:last-child, #sl-registry-card .sl-table td:last-child { border-left: none !important; }
        #sl-registry-card .sl-table tbody tr:nth-child(odd) { background: #070e1d !important; }
        #sl-registry-card .sl-table tbody tr:nth-child(even) { background: #162a47 !important; }
        #sl-registry-card .sl-table tbody tr:hover { background: rgba(14, 165, 233, 0.28) !important; }
        :root.light-theme #sl-registry-card .sl-table { border: 2.8px solid #3b1403 !important; }
        :root.light-theme #sl-registry-card .sl-table th, :root.light-theme #sl-registry-card .sl-table td { border-left: 2px solid rgba(59, 20, 3, 0.52) !important; border-bottom: 2.2px solid rgba(59, 20, 3, 0.58) !important; color: #120500 !important; }
        :root.light-theme #sl-registry-card .sl-table tbody tr:nth-child(odd) { background: #ffffff !important; }
        :root.light-theme #sl-registry-card .sl-table tbody tr:nth-child(even) { background: #ecdcc4 !important; }
        :root.light-theme #sl-registry-card .sl-table td div[style*="rgba(2,6,23"] { background: #fffdf9 !important; border: 1.8px solid #5c2808 !important; }
        :root.light-theme #sl-registry-card .sl-table td span:not(.sl-badge) { color: #120500 !important; -webkit-text-fill-color: #120500 !important; font-weight: 900 !important; }
        :root.light-theme #sl-registry-card .sl-table td[data-regcol="inv"] div { color: #7f1d1d !important; -webkit-text-fill-color: #7f1d1d !important; font-weight: 900 !important; }
        :root.light-theme .sl-badge-done { background: #064e3b !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; border: 1.5px solid #022c22 !important; }
        :root.light-theme .sl-badge-partial { background: #78350f !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; border: 1.5px solid #451a03 !important; }
        :root.light-theme .sl-badge-pending { background: #881337 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; border: 1.5px solid #4c0519 !important; }
        .sl-party-cell-txt { color: #fde68a !important; }
        html.light-theme .sl-party-cell-txt, :root.light-theme .sl-party-cell-txt { color: #3b1403 !important; -webkit-text-fill-color: #3b1403 !important; }
        :root.light-theme #sl-reg-wh-filter-lbl, :root.light-theme #sl-pagination-bar span { color: #120500 !important; -webkit-text-fill-color: #120500 !important; font-weight: 900 !important; }
        .sl-top-bar-ctl { height: 36px !important; min-height: 36px !important; max-height: 36px !important; box-sizing: border-box !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; margin: 0 !important; }
        #sl-reg-wh-filter { width: 155px !important; max-width: 168px !important; min-width: 140px !important; flex: 0 1 155px !important; }
        .sl-reg-flt-btn { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 5px !important; }
        #sl-filter-btn-pending { flex: 1.35 1 0 !important; }
        .sl-act-mini { padding: 3px 7px !important; font-size: 0.71rem !important; font-weight: 900 !important; border-radius: 7px !important; line-height: 1.2 !important; min-height: 26px !important; display: inline-flex !important; align-items: center !important; gap: 3px !important; cursor: pointer !important; white-space: nowrap !important; }
        html.light-theme #sl-reg-cols-drawer { background: var(--lt-sub-1, #ffffff) !important; border: 2.5px solid #3b1403 !important; color: #120500 !important; }
        html.light-theme #sl-reg-cols-drawer div[style*="rgba(30,41,59"] { background: #fbf4e6 !important; border: 1.8px solid #3b1403 !important; color: #120500 !important; }
        html.light-theme #sl-reg-cols-drawer span { color: #120500 !important; -webkit-text-fill-color: #120500 !important; }
        .sl-reg-th-resizer { position: absolute; left: 0; top: 0; bottom: 0; width: 7px; cursor: col-resize; z-index: 5; }
        .sl-reg-th-resizer:hover { background: rgba(56,189,248,0.55); }
        #sl-main-reg-table tbody td[data-regcol="date"], #sl-main-reg-table tbody td[data-regcol="date"] div { font-size: 14px; }
        #sl-main-reg-table tbody td[data-regcol="party"], #sl-main-reg-table tbody td[data-regcol="party"] span { font-size: 13.9px; }
        #sl-main-reg-table tbody td[data-regcol="iname"] span { font-size: 15.4px; }
        html.light-theme #sl-bottom-print-suite { background: var(--lt-sub-2, #f5ebd6) !important; border: 2.5px solid #3b1403 !important; }
        html.light-theme #sl-bottom-print-suite > span { color: #3b1403 !important; -webkit-text-fill-color: #3b1403 !important; }
        #sl-reg-search::placeholder { color: #94a3b8 !important; -webkit-text-fill-color: #94a3b8 !important; opacity: 1 !important; font-weight: 700 !important; }
        :root.light-theme #sl-reg-search::placeholder { color: #57534e !important; -webkit-text-fill-color: #57534e !important; }
        #sl-reg-search { color: #cbd5e1 !important; -webkit-text-fill-color: #cbd5e1 !important; }
        :root.light-theme #sl-reg-search { color: #000000 !important; -webkit-text-fill-color: #000000 !important; font-weight: 900 !important; }
        @media (max-width: 1100px) { #sl-inspection-cockpit { grid-template-columns: 1fr !important; } }
    </style>
</body>
</html>