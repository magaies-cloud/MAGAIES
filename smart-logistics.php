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
        html[data-theme="warm_cream"] body, body[data-theme="warm_cream"],
        html[data-magaies-theme="warm_cream"] body, body[data-magaies-theme="warm_cream"] {
            background: #f5efe6 !important; color: #2c221e !important;
            --sl-card-bg: linear-gradient(180deg, #fbf7f0 0%, #f3ebde 100%) !important;
            --sl-header-grad: linear-gradient(135deg, #ede3d2 0%, #f7f2e9 60%, #e6d8c3 100%) !important;
            --sl-unit-box-bg: #f7f1e5 !important;
            --sl-grid-font-color: #1e293b !important;
        }
        html[data-theme="ultra_white"] body, body[data-theme="ultra_white"],
        html[data-magaies-theme="ultra_white"] body, body[data-magaies-theme="ultra_white"], body.light-theme {
            background: #f1f5f9 !important; color: #0f172a !important;
            --sl-card-bg: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
            --sl-header-grad: linear-gradient(135deg, #e2e8f0 0%, #ffffff 60%, #f1f5f9 100%) !important;
            --sl-unit-box-bg: #f8fafc !important;
            --sl-grid-font-color: #0f172a !important;
        }
        html[data-theme="exec_gray"] body, body[data-theme="exec_gray"],
        html[data-magaies-theme="exec_gray"] body, body[data-magaies-theme="exec_gray"] {
            background: #e2e8f0 !important; color: #0f172a !important;
            --sl-card-bg: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%) !important;
            --sl-header-grad: linear-gradient(135deg, #cbd5e1 0%, #f1f5f9 60%, #dbeafe 100%) !important;
            --sl-unit-box-bg: #f1f5f9 !important;
            --sl-grid-font-color: #0f172a !important;
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

                <!-- 3 ESSENTIAL INFO BOXES (WITH HIGHLIGHTED SUPPLIER REGION) -->
                <div style="display: grid; grid-template-columns: 1fr 1.4fr 1fr; gap: 8px;">
                    <div class="sl-sub-region">
                        <label class="sl-field-badge">📄 رقم المستند / الفاتورة</label>
                        <div id="sl-lbl-inv-no" style="font-size: 1.05rem; font-weight: 900; color: #38bdf8; font-family: 'Inter', monospace; margin-top: 2px;">---</div>
                    </div>
                    <div class="sl-sub-region sl-supplier-region">
                        <label id="sl-lbl-party-title" class="sl-field-badge sl-supplier-badge">🏢 المورد</label>
                        <div id="sl-lbl-party-name" style="font-size: 1rem; font-weight: 900; color: #ffffff; margin-top: 2px;">---</div>
                    </div>
                    <div class="sl-sub-region">
                        <label class="sl-field-badge">📅 تاريخ المستند</label>
                        <div id="sl-lbl-date" style="font-size: 0.94rem; font-weight: 800; color: #e2e8f0; margin-top: 2px;">---</div>
                        <div id="sl-lbl-hash" style="display:none;">---</div>
                    </div>
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
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-price" onchange="applySlColVisibility()"> 💰 السعر والإجمالي</label>
                </div>
                <button type="button" onclick="document.getElementById('sl-cols-drawer').style.display='none'" class="sl-btn" style="padding:4px 12px; font-size:0.78rem; background:#10b981; color:#fff;">💾 حفظ وإخفاء</button>
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
                            <th class="sl-col-price" style="width: 110px; text-align: center; display:none;">💰 السعر</th>
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
    <div id="sl-font-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.85); backdrop-filter:blur(10px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:440px; padding:22px; border:2px solid #a855f7;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0; font-size:1.1rem; font-weight:900; color:#fff;">🎨 تخصيص حجم ولون الخط في الجداول</h3>
                <button type="button" onclick="document.getElementById('sl-font-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="font-size:0.84rem; font-weight:800; color:#cbd5e1; display:block; margin-bottom:6px;">📏 حجم خط الجداول والأرقام:</label>
                    <input type="range" id="sl-font-size-range" min="13" max="22" value="15" oninput="applySlTypography(this.value, document.getElementById('sl-font-color-picker').value)" style="width:100%; accent-color:#a855f7;">
                </div>
                <div>
                    <label style="font-size:0.84rem; font-weight:800; color:#cbd5e1; display:block; margin-bottom:6px;">🎨 لون النص الأساسي:</label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="color" id="sl-font-color-picker" value="#f8fafc" oninput="applySlTypography(document.getElementById('sl-font-size-range').value, this.value)" style="width:50px; height:38px; border:none; border-radius:8px; cursor:pointer;">
                        <button type="button" onclick="applySlTypography(16, '#ffffff')" class="sl-btn" style="font-size:0.78rem;">أبيض ناصع</button>
                        <button type="button" onclick="applySlTypography(16, '#6ee7b7')" class="sl-btn" style="font-size:0.78rem; color:#6ee7b7;">زمردي</button>
                        <button type="button" onclick="applySlTypography(16, '#bae6fd')" class="sl-btn" style="font-size:0.78rem; color:#bae6fd;">سماوي</button>
                        <button type="button" onclick="applySlTypography(16, '#fef08a')" class="sl-btn" style="font-size:0.78rem; color:#fef08a;">ذهبي</button>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('sl-font-modal').style.display='none'; showSlToast('✅ تم حفظ إعدادات الخطوط والألوان تلقائياً')" class="sl-btn" style="background:linear-gradient(135deg,#a855f7,#6366f1); color:#fff; padding:10px; margin-top:6px;">
                    💾 حفظ وإغلاق
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
    <script defer src="js/magaies-smart-guide.js?v=20261104_clean3"></script>
    <script defer src="js/smart-logistics.js?v=20261104_clean3"></script>
    <script src="js/global_background_media.js?v=20261022_v23" defer></script>
    <script src="js/magaiees_back.js?v=20261022_v23" defer></script>
    <!-- ═══════════════════════════════════════════════════════════════════
         💎 تناغم بصري موحد مع شاشة المشتريات (خلفية سوداء #030712 + مناطق كحلية داكنة متدرجة + إضاءة الحواف عند المرور)
         ═══════════════════════════════════════════════════════════════════ -->
    <style id="sl-purchase-harmony-theme">
        /* 1. الخلفية السوداء العميقة لتباين واضح مع المناطق الداكنة */
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) body {
            background-color: #030712 !important;
            background-image: none !important;
            color: #f8fafc !important;
        }

        /* 2. محاذاة عمودية مستقيمة 100% من اليمين واليسار ومسافة موحدة 8px */
        #sl-main-wrapper {
            width: calc(100% - 24px) !important;
            max-width: calc(100% - 24px) !important;
            margin: 8px auto 60px auto !important;
            padding: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
            box-sizing: border-box !important;
        }
        #sl-main-wrapper > * {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }

        /* 3. لون المناطق الداكن (Dark Navy Region Cards) بتباين واضح عن الخلفية السوداء */
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-glass-card,
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-floating-summary {
            background: linear-gradient(145deg, #152342 0%, #0f1a33 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.52) !important;
            border-radius: 14px !important;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.72), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease !important;
        }

        /* إضاءة حواف المنطقة بالكامل عند القدوم إليها أو التركيز بداخلها */
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-glass-card:hover,
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-glass-card:focus-within,
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-floating-summary:hover {
            background: linear-gradient(145deg, #1a2c54 0%, #132142 100%) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.35), 0 0 24px rgba(56, 189, 248, 0.38), 0 12px 28px rgba(0, 0, 0, 0.8) !important;
        }

        /* 4. الكبسولات الداخلية للحقول (Stepped Sub-Regions) */
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-sub-region {
            background: linear-gradient(135deg, #1d315c 0%, #162649 100%) !important;
            border: 1.5px solid rgba(56, 189, 248, 0.42) !important;
            border-radius: 10px !important;
            padding: 7px 11px !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 3px 8px rgba(0, 0, 0, 0.4) !important;
            transition: all 0.18s ease !important;
        }
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-sub-region:hover,
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-sub-region:focus-within {
            background: linear-gradient(135deg, #253f75 0%, #1c315e 100%) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.32), 0 0 16px rgba(56, 189, 248, 0.35) !important;
        }

        /* 5. تمييز خاص وإضاءة كاملة لمنطقة المورد (Supplier Region Illumination) */
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-supplier-region {
            background: linear-gradient(135deg, #1e3a6e 0%, #172d56 100%) !important;
            border: 2px solid rgba(56, 189, 248, 0.68) !important;
        }
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-supplier-region:hover,
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-supplier-region:focus-within {
            background: linear-gradient(135deg, #274c8f 0%, #1d396e 100%) !important;
            border-color: #7dd3fc !important;
            box-shadow: 0 0 0 2px rgba(125, 211, 252, 0.48), 0 0 26px rgba(56, 189, 248, 0.6) !important;
        }

        /* 6. شارات العناوين داخل الحقول */
        .sl-field-badge {
            display: inline-block;
            font-size: 0.74rem;
            font-weight: 900;
            color: #e2e8f0;
            background: rgba(15, 23, 42, 0.65);
            padding: 2px 8px;
            border-radius: 6px;
            border: 1px solid rgba(148, 163, 184, 0.28);
            margin-bottom: 4px;
        }
        .sl-supplier-badge {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
            border-color: #7dd3fc !important;
        }

        /* 7. حقول الإدخال داخل المناطق */
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-input {
            background: #060d1a !important;
            border: 1.5px solid rgba(56, 189, 248, 0.45) !important;
            color: #ffffff !important;
        }
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-input:hover,
        html:not([data-theme="ultra_white"]):not([data-theme="warm_cream"]):not([data-theme="exec_gray"]) .sl-input:focus {
            background: #09152b !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.4) !important;
        }

        /* 8. إلصاق الشريط السفلي والعلوي والدرع/المرشد في الشاشة لتطلع وتنزل مع حركة الشاشة */
        .sl-floating-summary { position: static !important; bottom: auto !important; }
        #msg-universal-fixed-launcher, #msg-universal-fixed-launcher.is-scroll-floating, .msg-fixed-command-pod {
            position: static !important; top: auto !important; left: auto !important; right: auto !important; transform: none !important;
        }
        .sl-btn-cnt {
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(2, 6, 23, 0.65); color: #fde047; font-family: 'Inter', monospace;
            font-size: 0.78rem; font-weight: 900; padding: 1px 7px; border-radius: 99px;
            border: 1px solid rgba(250, 204, 21, 0.45); margin-right: 4px;
        }

        @media (max-width: 1100px) {
            #sl-inspection-cockpit { grid-template-columns: 1fr !important; }
        }
    </style>
</body>
</html>
