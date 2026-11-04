<?php $page_title = 'بوابة الاستلام والتسليم الذكية الموحدة — الأيقونة اللوجستية الشاملة (GRN ⇄ GDN)'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
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
            transition: all 0.22s ease;
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
            transition: all 0.2s ease;
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
            transition: all 0.2s ease;
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
            transition: all 0.25s ease;
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
            animation: slPulseGlow 2s infinite ease-in-out;
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
            transition: all 0.2s ease;
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
    </style>
</head>
<body class="has-sidebar" data-logistics-mode="<?php echo (isset($_GET['mode']) && (!isset($_GET['mode']) || $_GET['mode'] === 'purchases')) ? 'purchases' : 'sales'; ?>">

    <div class="toast-container" id="toast-container"></div>
    <script defer src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div id="sl-main-wrapper" style="padding: 6px 0 55px 0; width: calc(100% - 24px); max-width: calc(100% - 24px); margin: 0 auto; display: flex; flex-direction: column; gap: 8px; box-sizing: border-box;">

        <!-- ═══════════════════════════════════════════════════════════════════
             1. TOP COCKPIT BAR: CLEAN NAVIGATION + ACTIVE TOOLS ONLY
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-top-cockpit-bar" class="sl-glass-card" style="background: var(--sl-header-grad); padding: 10px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <div style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #1d3561 0%, #142547 100%); border: 1.5px solid rgba(56, 189, 248, 0.65); padding: 7px 18px; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.45), inset 0 1px 0 rgba(255,255,255,0.12);">
                    <span id="sl-main-title-icon" style="font-size: 1.35rem;"><?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? '📥' : '📤'; ?></span>
                    <h1 id="sl-main-title-text" style="margin: 0; font-size: 1.18rem; font-weight: 900; color: #ffffff; letter-spacing: 0.2px;"><?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? 'بوابة استلام المشتريات' : 'بوابة تسليم المبيعات'; ?></h1>
                </div>
            </div>

            <div id="magaies-quartet-header-slot" style="display:flex; align-items:center;"></div>
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
        <div id="sl-registry-card" class="sl-glass-card" style="padding: 12px 16px;">
            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid rgba(56,189,248,0.22);">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; flex-wrap: nowrap;">
                    <div style="display: flex; align-items: center; gap: 8px; white-space: nowrap; flex-shrink: 0;">
                        <span style="font-size: 1.15rem;">📋</span>
                        <h3 id="sl-registry-title" style="margin: 0; font-size: 1rem; font-weight: 900; color: #ffffff;">
                            سجل فواتير وأذونات المشتريات
                        </h3>
                        <span id="sl-reg-count-badge" class="sl-badge sl-badge-partial">0 مستند</span>
                    </div>

                    <input type="text" id="sl-reg-search" class="sl-input" placeholder="🔍 بحث برقم الفاتورة أو المورد أو الصنف..." oninput="renderSlRegistry()" onkeydown="if(event.key==='Enter'){event.preventDefault(); quickLoadFirstMatch(this.value);}" style="flex: 1; max-width: 520px; min-width: 200px; font-size: 0.84rem; padding: 6px 14px !important;">

                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                        <input type="hidden" id="sl-qr-guard-mode" value="auto">
                        <button type="button" onclick="openSlMobileBridgeModal()" class="sl-btn" style="background: linear-gradient(135deg, #0284c7, #0369a1); border-color: #7dd3fc; color: #ffffff; padding: 6px 14px; font-size: 0.84rem; border-radius: 9px; white-space: nowrap;">
                            <span id="sl-bridge-live-dot" class="sl-pulse-dot" style="background:#fde047;"></span>
                            📱 ربط كاميرا الجوال
                        </button>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <select id="sl-reg-wh-filter" class="sl-input" onchange="renderSlRegistry()" style="font-size: 0.8rem; padding: 6px 10px !important; color: #fcd34d !important;">
                        <option value="all">🏢 كل المخازن</option>
                    </select>

                    <select id="sl-reg-limit" class="sl-input" onchange="renderSlRegistry()" style="font-size: 0.8rem; padding: 6px 10px !important;">
                        <option value="6" selected>6 فواتير</option>
                        <option value="15">15 مستند</option>
                        <option value="50">50 مستند</option>
                        <option value="100">100 مستند</option>
                        <option value="99999">الكل</option>
                    </select>

                    <button type="button" id="sl-filter-btn-pending" onclick="setSlRegistryFilter('pending')" class="sl-btn" style="background: var(--sl-primary); color: #fff; border-color: var(--sl-primary-light); padding: 6px 11px; font-size: 0.78rem;">⏳ قيد الاستلام <span id="sl-kpi-val-pending" class="sl-btn-cnt">0</span></button>
                    <button type="button" id="sl-filter-btn-partial" onclick="setSlRegistryFilter('partial')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">🌗 الجزئي <span id="sl-kpi-val-partial" class="sl-btn-cnt">0</span></button>
                    <button type="button" id="sl-filter-btn-done" onclick="setSlRegistryFilter('done')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">✅ المكتمل <span id="sl-kpi-val-done" class="sl-btn-cnt">0</span></button>
                    <button type="button" id="sl-filter-btn-notes" onclick="setSlRegistryFilter('notes')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">🏷️ الأذونات <span id="sl-kpi-val-notes" class="sl-btn-cnt">0</span></button>
                    <button type="button" id="sl-filter-btn-all" onclick="setSlRegistryFilter('all')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">📋 الكل</button>
                    <button type="button" onclick="refreshAllSlData()" class="sl-btn" style="color:#38bdf8; border-color:rgba(56,189,248,0.45); padding: 6px 11px; font-size: 0.78rem;">🔄 تحديث</button>
                </div>
            </div>

            <div id="sl-registry-scroll-wrap" style="overflow-x: auto; overflow-y: visible !important; max-height: none !important; border-radius: 12px; border: 1.5px solid rgba(56,189,248,0.28);">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th style="text-align: right; width: 13%;">رقم الفاتورة / الإذن</th>
                            <th style="text-align: center; width: 12%;">التاريخ</th>
                            <th id="sl-reg-th-party" style="text-align: right; width: 18%;">🏢 المورد</th>
                            <th style="text-align: right; width: 31%;">الأصناف والكميات</th>
                            <th style="text-align: center; width: 12%;">المخزن والحالة</th>
                            <th style="text-align: center; width: 14%;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="sl-registry-tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             5. WORKSTATION COCKPIT: DOCUMENT CARD + QR CAMERA
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-inspection-cockpit" style="display: grid; grid-template-columns: 1fr 330px; gap: 8px;">

            <!-- RIGHT: DOCUMENT META & LOGISTICS FIELDS -->
            <div id="sl-doc-meta-card" class="sl-glass-card" style="padding: 12px 16px; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; padding-bottom: 8px; border-bottom: 1px solid rgba(56,189,248,0.22);">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.2rem;">📄</span>
                        <h3 id="sl-doc-card-title" style="margin: 0; font-size: 1rem; font-weight: 900; color: #ffffff;">
                            بيانات الفاتورة والاستلام المخزني
                        </h3>
                        <span id="sl-doc-card-sub" style="display:none;"></span>
                    </div>
                    <div id="sl-doc-status-pill" class="sl-badge sl-badge-pending">جاهز للاستلام</div>
                </div>

                <!-- 3 ESSENTIAL INFO BOXES (WITH COMPACT SERIAL, FIXED LEDGER BUTTON & FINANCIAL STRIP) -->
                <div style="display: grid; grid-template-columns: 0.9fr 1.5fr 0.9fr; gap: 8px;">
                    <div class="sl-sub-region" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                        <label class="sl-field-badge" style="margin:0;">📄 الرقم:</label>
                        <div id="sl-lbl-inv-no" style="font-size: 1.58rem; font-weight: 900; color: #38bdf8; font-family: 'Outfit', 'Tajawal', monospace; line-height:1.1;">---</div>
                    </div>
                    <div class="sl-sub-region sl-supplier-region" style="position:relative; padding-left:92px !important;">
                        <label id="sl-lbl-party-title" class="sl-field-badge sl-supplier-badge">🏢 المورد</label>
                        <div id="sl-lbl-party-name" style="font-size: 1.08rem; font-weight: 900; color: #ffffff; margin-top: 2px;">---</div>
                        <button type="button" id="sl-supplier-ledger-btn" onclick="openSlPartyLedger()" title="كشف حساب المورد / العميل (دفتر الأستاذ)">📖 أستاذ</button>
                    </div>
                    <div class="sl-sub-region" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                        <label class="sl-field-badge" style="margin:0;">📅 التاريخ:</label>
                        <div id="sl-lbl-date" style="font-size: 1.38rem; font-weight: 900; color: #e2e8f0; font-family: 'Outfit', 'Tajawal', sans-serif;">---</div>
                        <div id="sl-lbl-hash" style="display:none;">---</div>
                    </div>
                </div>

                <!-- PURCHASE CONSTITUTION FINANCIAL STRIP + CURRENCY TOGGLE (جنيه ⇄ دولار) -->
                <div id="sl-party-fin-strip" class="sl-fin-strip-bar">
                    <button type="button" id="sl-fin-strip-currency-btn" data-cur-mode="SDG" onclick="toggleSlFinStripCurrency()">جنيه ⇄</button>
                    <div class="sl-fin-pill pill-ledger"><span class="fin-lbl">الرصيد:</span><span id="sl-fin-ledger" class="fin-val">0</span></div>
                    <div class="sl-fin-pill pill-debt"><span class="fin-lbl">المديونية:</span><span id="sl-fin-debt" class="fin-val">0</span></div>
                    <div class="sl-fin-pill pill-checks"><span class="fin-lbl">الشيكات:</span><span id="sl-fin-checks" class="fin-val">0</span></div>
                    <div class="sl-fin-pill pill-limit"><span class="fin-lbl">الحد:</span><span id="sl-fin-limit" class="fin-val">0</span></div>
                    <div class="sl-fin-pill pill-rem"><span class="fin-lbl">المتاح:</span><span id="sl-fin-rem" class="fin-val" style="font-size:1.28rem !important;">0</span></div>
                </div>

                <!-- LOGISTICS INPUTS (WAREHOUSE, RECEIVER, DRIVER, PLATE, GATE PASS) -->
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px;">
                    <div class="sl-sub-region">
                        <label id="sl-lbl-wh-select" class="sl-field-badge" style="color: #fcd34d;">🏢 مخزن الاستلام:</label>
                        <select id="sl-master-warehouse" class="sl-input" style="width: 100%; padding: 6px 10px !important; font-size: 0.84rem !important;" onchange="onSlMasterWarehouseChange(this.value)"></select>
                    </div>
                    <div class="sl-sub-region">
                        <label id="sl-lbl-receiver" class="sl-field-badge" style="color: #6ee7b7;">🤝 أمين المخزن المستلم:</label>
                        <input type="text" id="sl-receiver-name" class="sl-input" placeholder="الاسم..." style="width: 100%; padding: 6px 10px !important; font-size: 0.84rem !important;">
                    </div>
                    <div class="sl-sub-region">
                        <label class="sl-field-badge" style="color: #7dd3fc;">🚚 السائق / المندوب:</label>
                        <input type="text" id="sl-driver-name" class="sl-input" placeholder="اسم السائق..." style="width: 100%; padding: 6px 10px !important; font-size: 0.84rem !important;">
                    </div>
                    <div class="sl-sub-region">
                        <label class="sl-field-badge" style="color: #c084fc;">🔢 رقم اللوحة:</label>
                        <input type="text" id="sl-truck-plate" class="sl-input" placeholder="رقم اللوحة..." style="width: 100%; padding: 6px 10px !important; font-size: 0.84rem !important;">
                    </div>
                    <div class="sl-sub-region">
                        <label class="sl-field-badge" style="color: #38bdf8;">🎫 رقم المرجع / الإذن:</label>
                        <input type="text" id="sl-gate-pass-no" class="sl-input" placeholder="تلقائي..." style="width: 100%; padding: 6px 10px !important; font-size: 0.84rem !important;">
                    </div>
                </div>

                <!-- NOTES ROW -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div class="sl-sub-region">
                        <label class="sl-field-badge" style="color: #fbbf24;">📝 بيان وملاحظات الاستلام:</label>
                        <input type="text" id="sl-warehouse-notes" class="sl-input" placeholder="ملاحظات فحص واستلام البضاعة..." style="width: 100%; padding: 6px 10px !important; font-size: 0.84rem !important;">
                    </div>
                    <div class="sl-sub-region">
                        <label class="sl-field-badge" style="color: #cbd5e1;">📌 ملاحظات إضافية:</label>
                        <input type="text" id="sl-personal-notes" class="sl-input" placeholder="ملاحظات اختيارية..." style="width: 100%; padding: 6px 10px !important; font-size: 0.84rem !important;">
                    </div>
                </div>
            </div>

            <!-- LEFT: LASER QR CAMERA & VISUAL PROOF -->
            <div id="sl-qr-camera-card" class="sl-glass-card" style="padding: 12px 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid rgba(56,189,248,0.22);">
                    <h3 style="margin: 0; font-size: 0.96rem; font-weight: 900; color: #ffffff;">📷 المسح الضوئي (QR) والمرفقات</h3>
                    <span id="sl-verify-badge" class="sl-badge sl-badge-done">جاهز</span>
                </div>

                <label style="display:flex; align-items:center; gap:8px; background:rgba(14,165,233,0.14); border:1px solid rgba(56,189,248,0.4); padding:7px 10px; border-radius:10px; font-size:0.78rem; font-weight:800; color:#fff; cursor:pointer;">
                    <input type="checkbox" id="sl-auto-execute-qr" checked style="width:15px; height:15px; accent-color:var(--sl-primary);">
                    <span>⚡ استلام فوري عند مسح الباركود</span>
                </label>

                <div id="sl-qr-reader-wrap" style="display:none; flex-direction:column; gap:8px;">
                    <div id="sl-qr-reader" style="width:100%; border-radius:12px; overflow:hidden; border:2px solid var(--sl-primary-light); aspect-ratio:4/3; background:#000;"></div>
                    <button type="button" onclick="stopSlLocalCamera()" class="sl-btn" style="background:rgba(239,68,68,0.2); border-color:#ef4444; color:#fca5a5;">⏹️ إيقاف الكاميرا</button>
                </div>

                <div id="sl-camera-placeholder" class="sl-sub-region" style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:12px; text-align:center;">
                    <div style="font-size:1.8rem; margin-bottom:4px;">📱⚡</div>
                    <div style="font-size:0.78rem; color:#cbd5e1; font-weight:700; margin-bottom:8px;">
                        مسح باركود الفاتورة بالجوال أو الكاميرا
                    </div>
                    <div style="display:flex; gap:8px; width:100%; flex-wrap:wrap;">
                        <button type="button" onclick="openSlMobileBridgeModal()" class="sl-btn" style="flex:1; background:linear-gradient(135deg,#0284c7,#0369a1); border-color:#38bdf8; color:#fff; font-size:0.8rem;">
                            📱 كاميرا الجوال
                        </button>
                        <button type="button" onclick="startSlLocalCamera()" class="sl-btn" style="flex:1; background:rgba(56,189,248,0.18); border-color:#38bdf8; color:#38bdf8; font-size:0.8rem;">
                            📷 كاميرا الجهاز
                        </button>
                    </div>
                </div>

                <div>
                    <input type="file" id="sl-photo-input" accept="image/*" style="display:none;" onchange="handleSlPhotoUpload(event)">
                    <button type="button" onclick="document.getElementById('sl-photo-input').click()" class="sl-btn" style="width:100%; background:rgba(245,158,11,0.14); border-color:rgba(245,158,11,0.4); color:#fcd34d; font-size:0.8rem;">
                        📎 إرفاق صورة المستند
                    </button>
                    <div id="sl-photo-preview-wrap" style="display:none; margin-top:6px; text-align:center;">
                        <img id="sl-photo-preview-img" src="" style="max-height:60px; border-radius:8px; border:1px solid var(--sl-primary-light);">
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             6. UNIFIED DUAL-UNIT & MULTI-WAREHOUSE SPLIT INSPECTION GRID
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-items-grid-card" class="sl-glass-card" style="padding: 14px 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.3rem;">⚖️</span>
                    <h3 id="sl-grid-title" style="margin: 0; font-size: 1.02rem; font-weight: 900; color: #ffffff;">
                        أصناف الفاتورة والكميات المنفذة
                    </h3>
                </div>

                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <button type="button" onclick="fillAllSlQuantities(1)" class="sl-btn" style="background: rgba(16,185,129,0.2); border-color: #34d399; color: #6ee7b7; padding: 6px 12px; font-size: 0.8rem;">
                        ⚡ كامل الكمية (100%)
                    </button>
                    <button type="button" onclick="fillAllSlQuantities(0.5)" class="sl-btn" style="background: rgba(56,189,248,0.18); border-color: #38bdf8; color: #7dd3fc; padding: 6px 12px; font-size: 0.8rem;">
                        🌗 نصف الكمية (50%)
                    </button>
                    <button type="button" onclick="fillAllSlQuantities(0)" class="sl-btn" style="background: rgba(239,68,68,0.16); border-color: #f87171; color: #fca5a5; padding: 6px 12px; font-size: 0.8rem;">
                        🧹 تصفير (0)
                    </button>
                    <button type="button" id="sl-btn-focus-mode" onclick="toggleSlStorekeeperFocus()" class="sl-btn" style="background: rgba(245,158,11,0.18); border-color: #fbbf24; color: #fde68a; padding: 6px 12px; font-size: 0.8rem;">
                        🎯 تركيز الكميات
                    </button>
                    <button type="button" id="sl-btn-ai-audit" onclick="openSlAIAuditModal()" class="sl-btn" style="background: linear-gradient(180deg, #7e22ce 0%, #6b21a8 52%, #4c1d95 100%); border: 2px solid #c084fc; border-bottom: 3.5px solid #2e1065; color: #ffffff; padding: 6px 13px; font-size: 0.82rem; font-weight: 900;">
                        🤖 الذكاء الاصطناعي للأسعار
                    </button>
                    <button type="button" onclick="openSlFontModal()" class="sl-btn" style="background: linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%); border: 2px solid #7dd3fc; border-bottom: 3.5px solid #082f49; color: #ffffff; padding: 6px 13px; font-size: 0.82rem; font-weight: 900;">
                        🎨 تخصيص الخطوط والألوان
                    </button>
                    <button type="button" onclick="const d=document.getElementById('sl-cols-drawer'); d.style.display=(d.style.display==='none'?'flex':'none');" class="sl-btn" style="background: rgba(168,85,247,0.18); border-color: #c084fc; color: #e9d5ff; padding: 6px 12px; font-size: 0.8rem;">
                        ⚙️ الأعمدة
                    </button>
                </div>
            </div>

            <!-- COLUMN VISIBILITY DRAWER -->
            <div id="sl-cols-drawer" style="display:none; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; background:rgba(2,6,23,0.85); padding:10px 16px; border-radius:12px; border:1.5px solid rgba(168,85,247,0.45); margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; font-size:0.8rem; font-weight:800;">
                    <span style="color:#e9d5ff;">👁️ إظهار/إخفاء الأعمدة:</span>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-wh" checked onchange="applySlColVisibility()"> 🏢 مخزن السطر والرصيد</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-spec" checked onchange="applySlColVisibility()"> ⚖️ المعامل والبيان</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-prev" checked onchange="applySlColVisibility()"> 📊 المنفذ سابقاً والمتبقي</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-price" checked onchange="applySlColVisibility()"> 💰 السعر ورادار الأسعار</label>
                </div>
                <button type="button" onclick="saveSlCustomConfigPermanently(); document.getElementById('sl-cols-drawer').style.display='none';" class="sl-btn" style="padding:4px 12px; font-size:0.78rem; background:#10b981; color:#fff;">💾 حفظ نهائي وإخفاء</button>
            </div>

            <div style="overflow-x: auto; border-radius: 14px; border: 1.5px solid rgba(148,163,184,0.22);">
                <table class="sl-table" id="sl-items-grid" style="min-width: 1280px;">
                    <thead>
                        <tr>
                            <th style="width: 44px; text-align: center;">#</th>
                            <th class="sl-col-wh" style="width: 190px; text-align: right;">🏢 المستودع والرصيد الفعلي</th>
                            <th style="min-width: 210px; text-align: right;">📦 الصنف والكود</th>
                            <th class="sl-col-spec" style="width: 165px; text-align: center;">⚖️ البيان / المعامل</th>
                            <th style="width: 120px; text-align: center;">📊 كمية الفاتورة</th>
                            <th class="sl-col-prev" style="width: 150px; text-align: center;">🔄 المنفذ سابقاً / المتبقي</th>
                            <th class="sl-col-price" style="width: 155px; text-align: center;">💰 السعر والرادار</th>
                            <th id="sl-th-dual-action" style="min-width: 510px; text-align: center; color: var(--sl-primary-light);">
                                ⚡ الكمية المنفذة الآن (بالوحدة الأساسية ⇄ أو بالوحدة الفرعية)
                            </th>
                            <th style="width: 120px; text-align: center;">🚦 حالة السطر</th>
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
        <div class="sl-floating-summary">
            <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="sl-pulse-dot" style="background:var(--sl-primary-light);"></span>
                    <span id="sl-summary-mode-badge" style="font-weight:900; font-size:0.92rem; color:var(--sl-primary-light);">📤 وضع تسليم المبيعات (GDN)</span>
                </div>
                <div style="height:24px; width:1px; background:rgba(148,163,184,0.25);"></div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">
                    الأصناف: <strong id="sl-sum-items-count" style="color:#fff; font-size:0.98rem;">0</strong>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">
                    إجمالي الفاتورة: <strong id="sl-sum-ordered" style="color:#38bdf8; font-size:0.98rem;">0</strong>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">
                    منفذ سابقاً: <strong id="sl-sum-prev" style="color:#a855f7; font-size:0.98rem;">0</strong>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1; background:var(--sl-primary-soft); padding:4px 12px; border-radius:10px; border:1px solid var(--sl-border-glow);">
                    المنفذ في هذه الدفعة: <strong id="sl-sum-now" style="color:var(--sl-primary-light); font-size:1.05rem;">0</strong>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">
                    المتبقي بعد الدفعة: <strong id="sl-sum-rem" style="color:#fbbf24; font-size:0.98rem;">0</strong>
                </div>
                <div id="sl-dual-grand-totals-wrap" style="display:inline-flex; align-items:center; gap:8px; direction:rtl;">
                    <span class="sl-grand-tot-box tot-sdg" id="sl-sum-total-sdg" title="إجمالي القيمة بالعملة المحلية (ج.س)">0 ج.س</span>
                    <span class="sl-grand-tot-box tot-usd" id="sl-sum-total-usd" title="إجمالي القيمة بالدولار ($)">0.00 $</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <button type="button" onclick="shareSlWhatsApp()" class="sl-btn" style="background: rgba(34,197,94,0.18); border-color: rgba(34,197,94,0.45); color: #4ade80;">
                    💬 واتساب
                </button>
                <button type="button" onclick="printSlOfficialNote()" class="sl-btn" style="background: rgba(56,189,248,0.18); border-color: rgba(56,189,248,0.45); color: #38bdf8;">
                    🖨️ طباعة الإذن الرسمي (QR)
                </button>
                <button type="button" id="sl-btn-undo" onclick="undoSlTransaction()" class="sl-btn" style="background: rgba(239,68,68,0.16); border-color: rgba(239,68,68,0.45); color: #fca5a5;">
                    ↩️ إلغاء وعكس الحركة المخزنية
                </button>
                                <button type="button" id="sl-btn-admin-l4" onclick="executeSlAdminClosureL4()" class="sl-btn" style="display:none; background: linear-gradient(135deg, #8b5cf6, #6d28d9); border-color: #c4b5fd; color: #ffffff; box-shadow: 0 6px 18px rgba(139,92,246,0.4);">
                    👑 اعتماد إداري نهائي (L4)
                </button>
                <button type="button" id="sl-btn-execute" onclick="executeSlTransaction()" class="sl-btn" style="padding: 11px 28px; font-size: 1rem; background: linear-gradient(135deg, var(--sl-primary), var(--sl-primary-dark)); border-color: var(--sl-primary-light); color: #ffffff; box-shadow: 0 8px 25px var(--sl-primary-glow);">
                    🚀 تأكيد واعتماد التسليم والصرف الآن
                </button>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 1: MOBILE CAMERA DIRECT BRIDGE (ربط كاميرا الجوال باللابتوب)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-mobile-bridge-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(14px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:640px; border:2px solid var(--sl-primary-light); padding:0;">
            <div style="background:linear-gradient(135deg, #064e3b, #0284c7); padding:16px 22px; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="font-size:1.9rem;">📱</span>
                    <div>
                        <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#fff;">ربط كاميرا الجوال باللابتوب (المسح المتتابع الذكي GRN ⇄ GDN)</h3>
                        <p style="margin:2px 0 0; font-size:0.8rem; color:#a7f3d0; font-weight:700;">مرّر 10 أذونات أو أكثر وراء بعضها أمام كاميرا الجوال — تُعتمد وتُحدث المخزون تلقائياً بدون لمس!</p>
                    </div>
                </div>
                <button type="button" onclick="closeSlMobileBridgeModal()" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(230px, 1fr)); gap:16px; align-items:center;">
                    <div style="background:#ffffff; padding:14px; border-radius:18px; text-align:center; border:3px solid var(--sl-primary);">
                        <img id="sl-bridge-qr-img" src="" alt="Pairing QR" style="width:190px; height:190px; display:block; margin:0 auto;">
                        <div style="color:#0f172a; font-weight:900; font-size:0.82rem; margin-top:6px;">امسح هذا الكود بكاميرا الجوال للربط الفوري</div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:10px; font-size:0.83rem; line-height:1.65;">
                        <div style="background:rgba(15,23,42,0.8); padding:12px; border-radius:12px; border:1px solid rgba(148,163,184,0.2);">
                            <div>✅ <b>التعرف الذكي التلقائي:</b> يميز بين إذن المبيعات وإذن المشتريات فوراً أو يحميك بقفل الأمان.</div>
                            <div>⚡ <b>مسح متتابع بدون توقف:</b> لا تغلق الكاميرا بعد المسحة الأولى؛ استمر بتمرير الأوراق.</div>
                            <div style="color:#fca5a5;">🚫 <b>حماية التكرار:</b> أي إذن مستلم أو مسلم مسبقاً يُرفض فوراً بنغمة تحذير.</div>
                        </div>
                        <a id="sl-bridge-open-link" href="mobile-qr-gun.php" target="_blank" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; text-decoration:none; padding:10px;">
                            🚀 فتح عدسة الجوال المباشرة في صفحة جديدة
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
    <div id="sl-font-modal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.85); backdrop-filter:blur(10px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
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
    <script src="js/magaies-offline-sync.js?v=20261014"></script>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/print-engine.js"></script>
    <script defer src="js/magaies-smart-guide.js?v=<?php echo file_exists('js/magaies-smart-guide.js') ? filemtime('js/magaies-smart-guide.js') : '20261104_v5'; ?>"></script>
    <script defer src="js/smart-logistics.js?v=<?php echo file_exists('js/smart-logistics.js') ? filemtime('js/smart-logistics.js') : '20261104_v5'; ?>"></script>
    <script src="js/global_background_media.js?v=20261022_v23" defer></script>
    <script src="js/magaiees_back.js?v=20261022_v23" defer></script>
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
            box-shadow: 0 12px 32px rgba(0,0,0,0.92), inset 0 1.5px 1px rgba(255,255,255,0.28) !important; animation: purGlassWaveSweep 7.5s infinite !important;
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
            box-shadow: 0 12px 28px rgba(43,17,3,0.14), inset 0 2px 0 #ffffff !important; color: var(--lt-ink, #120500) !important; animation: purGlassWaveSweep 8s infinite !important;
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

        /* 3D JEWEL BUTTONS IN BOTH THEMES */
        :root.light-theme .sl-btn {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important; border: 2px solid #475569 !important; border-bottom: 3px solid #020617 !important;
            color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 900 !important;
            text-shadow: 0 1.5px 2px rgba(0,0,0,0.9) !important; box-shadow: 0 4px 12px rgba(15,23,42,0.25), inset 0 1.5px 1px rgba(255,255,255,0.45) !important;
        }
        :root.light-theme .sl-btn span { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
        :root.light-theme #sl-filter-btn-pending, :root.light-theme #sl-btn-execute {
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 52%, #075985 100%) !important; border: 2px solid #7dd3fc !important; border-bottom: 3.5px solid #082f49 !important;
        }
        .sl-floating-summary { position: static !important; bottom: auto !important; }
        #msg-universal-fixed-launcher, #msg-universal-fixed-launcher.is-scroll-floating, .msg-fixed-command-pod { position: static !important; top: auto !important; left: auto !important; right: auto !important; transform: none !important; }
        .sl-btn-cnt { display: inline-flex; align-items: center; justify-content: center; background: rgba(2, 6, 23, 0.65); color: #fde047 !important; -webkit-text-fill-color: #fde047 !important; font-family: 'Inter', monospace; font-size: 0.78rem; font-weight: 900; padding: 1px 7px; border-radius: 99px; border: 1px solid rgba(250, 204, 21, 0.45); margin-right: 4px; }
        @media (max-width: 1100px) { #sl-inspection-cockpit { grid-template-columns: 1fr !important; } }
    </style>
</body>
</html>