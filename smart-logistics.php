<?php $page_title = 'بوابة الاستلام والتسليم الذكية الموحدة — الأيقونة اللوجستية الشاملة (GRN ⇄ GDN)'; ?>
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
           ICONIC CYBER-GLASS & KINETIC NEON ENGINE (بوابة الاستلام والتسليم الموحدة)
           Dynamic Chameleon Variables: Switches between Sales (Emerald/Gold) & Purchases (Cyan/Indigo)
           ═══════════════════════════════════════════════════════════════════════════ */
        :root, body[data-logistics-mode="sales"] {
            --sl-primary: #10b981;
            --sl-primary-dark: #059669;
            --sl-primary-light: #34d399;
            --sl-primary-soft: rgba(16, 185, 129, 0.16);
            --sl-primary-glow: rgba(16, 185, 129, 0.52);
            --sl-accent: #fbbf24;
            --sl-accent-soft: rgba(245, 158, 11, 0.16);
            --sl-secondary: #38bdf8;
            --sl-border-glow: rgba(52, 211, 153, 0.55);
            --sl-card-bg: linear-gradient(155deg, rgba(6, 28, 23, 0.88) 0%, rgba(2, 10, 18, 0.94) 100%);
            --sl-header-grad: linear-gradient(135deg, rgba(6, 78, 59, 0.65) 0%, rgba(15, 23, 42, 0.9) 60%, rgba(4, 47, 46, 0.75) 100%);
            --sl-unit-box-bg: linear-gradient(145deg, #06281f 0%, #03130f 100%);
            --sl-grid-font-size: 0.96rem;
            --sl-grid-font-color: #f8fafc;
        }

        body[data-logistics-mode="purchases"] {
            --sl-primary: #0ea5e9;
            --sl-primary-dark: #0284c7;
            --sl-primary-light: #38bdf8;
            --sl-primary-soft: rgba(14, 165, 233, 0.18);
            --sl-primary-glow: rgba(14, 165, 233, 0.55);
            --sl-accent: #a855f7;
            --sl-accent-soft: rgba(168, 85, 247, 0.18);
            --sl-secondary: #818cf8;
            --sl-border-glow: rgba(56, 189, 248, 0.6);
            --sl-card-bg: linear-gradient(155deg, rgba(8, 24, 48, 0.9) 0%, rgba(5, 10, 26, 0.95) 100%);
            --sl-header-grad: linear-gradient(135deg, rgba(12, 74, 110, 0.7) 0%, rgba(15, 23, 42, 0.92) 55%, rgba(49, 46, 129, 0.7) 100%);
            --sl-unit-box-bg: linear-gradient(145deg, #08203e 0%, #040d1f 100%);
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
            background: #020617;
            color: #f8fafc;
            transition: background-color 0.35s ease;
        }

        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(2, 6, 23, 0.85);
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--sl-primary-light), var(--sl-secondary));
            border-radius: 10px;
            border: 2px solid rgba(2, 6, 23, 0.85);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--sl-accent);
        }

        .ambient-bg { display: none !important; }

        /* ── FROSTED GLASS CARDS WITH LUMINOUS EDGES (FREE SCROLLING — NO CLIPPING) ── */
        .sl-glass-card {
            position: relative;
            background: var(--sl-card-bg);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1.5px solid rgba(148, 163, 184, 0.22);
            border-radius: 18px;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.12);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            overflow: visible !important;
        }
        .sl-glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--sl-primary-light), var(--sl-accent), transparent);
            opacity: 0.75;
            pointer-events: none;
        }
        .sl-glass-card:hover {
            border-color: var(--sl-border-glow);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.65), 0 0 22px var(--sl-primary-soft), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        /* ── KINETIC GLASS BUTTONS WITH LASER SHIMMER & ILLUMINATED BORDERS ── */
        .sl-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 12px;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-weight: 800;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 1.5px solid rgba(255, 255, 255, 0.18);
            background: rgba(15, 23, 42, 0.75);
            color: #f8fafc;
            padding: 8px 16px;
            font-size: 0.86rem;
            backdrop-filter: blur(10px);
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.22s ease, border-color 0.22s ease, filter 0.2s ease;
            user-select: none;
        }
        .sl-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -130%;
            width: 65%;
            height: 200%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.32) 50%, rgba(255,255,255,0) 100%);
            transform: rotate(25deg);
            pointer-events: none;
            transition: left 0.52s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .sl-btn:hover:not(:disabled)::after {
            left: 145%;
        }
        .sl-btn:hover:not(:disabled) {
            transform: translateY(-2.5px) scale(1.035);
            filter: brightness(1.12);
            border-color: var(--sl-primary-light) !important;
            box-shadow: 0 0 20px var(--sl-primary-glow), 0 8px 18px rgba(0, 0, 0, 0.45), inset 0 0 12px rgba(255, 255, 255, 0.18);
        }
        .sl-btn:active:not(:disabled) {
            transform: translateY(0) scale(0.96);
        }
        .sl-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        /* ── GIANT DUAL-MODE COCKPIT SWITCHER (المبيعات 📤 ⇄ المشتريات 📥) — COMPACT & LIFTED ── */
        .sl-mode-switcher {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 4px;
            background: rgba(2, 6, 23, 0.85);
            border-radius: 14px;
            border: 1.5px solid rgba(148, 163, 184, 0.25);
            box-shadow: inset 0 4px 14px rgba(0, 0, 0, 0.7);
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
            border: 2px solid transparent;
            background: rgba(15, 23, 42, 0.55);
            transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .sl-mode-tab::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -130%;
            width: 60%;
            height: 200%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.22), transparent);
            transform: rotate(25deg);
            transition: left 0.6s ease;
        }
        .sl-mode-tab:hover::after { left: 140%; }
        .sl-mode-tab.mode-sales.active {
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.92), rgba(4, 120, 87, 0.85));
            border-color: #34d399;
            box-shadow: 0 0 22px rgba(16, 185, 129, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .sl-mode-tab.mode-purchases.active {
            background: linear-gradient(135deg, rgba(12, 74, 110, 0.92), rgba(67, 56, 202, 0.85));
            border-color: #38bdf8;
            box-shadow: 0 0 22px rgba(14, 165, 233, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .sl-mode-tab:not(.active) {
            opacity: 0.68;
        }
        .sl-mode-tab:not(.active):hover {
            opacity: 0.95;
            border-color: rgba(148, 163, 184, 0.4);
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

        /* ── KPI COUNTER CARDS — COMPACT & LIFTED ── */
        .sl-kpi-card {
            padding: 7px 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .sl-kpi-card:hover {
            transform: translateY(-4px) scale(1.015);
        }
        .sl-kpi-card.active-filter {
            box-shadow: 0 0 25px var(--sl-primary-glow), inset 0 0 15px var(--sl-primary-soft) !important;
            border-color: var(--sl-primary-light) !important;
        }

        /* ── INPUTS & SELECTS ── */
        .sl-input {
            background: rgba(2, 6, 23, 0.82) !important;
            border: 1.5px solid rgba(148, 163, 184, 0.28) !important;
            color: #ffffff !important;
            border-radius: 11px !important;
            padding: 9px 14px !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-weight: 800 !important;
            outline: none !important;
            transition: border-color 0.22s ease, box-shadow 0.22s ease, transform 0.18s ease !important;
        }
        .sl-input:focus {
            border-color: var(--sl-primary-light) !important;
            box-shadow: 0 0 16px var(--sl-primary-glow) !important;
        }

        /* ── DUAL-UNIT INTERACTIVE BOXES (استلم هنا بالأساسية ⇄ أو بالفرعية) ── */
        .sl-unit-box {
            position: relative;
            overflow: hidden;
            background: var(--sl-unit-box-bg);
            border: 2px solid rgba(148, 163, 184, 0.3);
            border-radius: 13px;
            padding: 8px 10px;
            width: 236px;
            min-width: 236px;
            transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45);
        }
        .sl-unit-box::after {
            content: '';
            position: absolute;
            top: -60%;
            left: -135%;
            width: 75%;
            height: 220%;
            background: linear-gradient(105deg, transparent 20%, rgba(255,255,255,0.15) 50%, transparent 80%);
            transform: rotate(22deg);
            pointer-events: none;
            transition: left 0.55s ease;
        }
        .sl-unit-box:hover::after { left: 145%; }
        .sl-unit-box:hover, .sl-unit-box.is-active-unit-mode {
            transform: translateY(-2px) scale(1.02);
            border-color: var(--sl-primary-light) !important;
            box-shadow: 0 0 20px var(--sl-primary-glow), 0 8px 18px rgba(0,0,0,0.5);
        }
        .sl-unit-box.base-box.is-active-unit-mode {
            border-color: #818cf8 !important;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.55), 0 8px 18px rgba(0,0,0,0.5);
        }

        /* ── TABLE STYLING & CUSTOM FONT SCALING ── */
        .sl-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .sl-table thead th {
            background: rgba(2, 6, 23, 0.92);
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
            transition: background-color 0.18s ease, transform 0.18s ease;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }
        .sl-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.045) !important;
        }
        .sl-table tbody tr.sl-selected-row {
            background: var(--sl-primary-soft) !important;
            box-shadow: inset 4px 0 0 var(--sl-primary-light);
        }
        .sl-table tbody td {
            padding: 10px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            font-size: var(--sl-grid-font-size);
            color: var(--sl-grid-font-color);
            vertical-align: middle;
        }

        /* ── FLOATING LIVE SUMMARY BAR (شريط الإحصائيات العائم) ── */
        .sl-floating-summary {
            position: sticky;
            bottom: 10px;
            z-index: 900;
            background: linear-gradient(135deg, rgba(2, 6, 23, 0.95), rgba(15, 23, 42, 0.96));
            backdrop-filter: blur(20px);
            border: 2px solid var(--sl-primary-light);
            border-radius: 18px;
            padding: 12px 22px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.8), 0 0 28px var(--sl-primary-glow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            transition: all 0.3s ease;
        }

        /* ── PULSE DOT ── */
        @keyframes slPulseGlow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.65; }
        }
        .sl-pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            animation: slPulseGlow 1.8s infinite ease-in-out;
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
        .sl-badge-pending { background: rgba(245, 158, 11, 0.18); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.45); }
        .sl-badge-partial { background: rgba(56, 189, 248, 0.18); color: #7dd3fc; border: 1px solid rgba(56, 189, 248, 0.45); }
        .sl-badge-done { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.5); }
    </style>
</head>
<body data-logistics-mode="<?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? 'purchases' : 'sales'; ?>">

    <div class="toast-container" id="toast-container"></div>
    <script defer src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div style="padding: 6px 12px 55px 12px; max-width: 99.6vw; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 8px;">

        <!-- ═══════════════════════════════════════════════════════════════════
             1. TOP COCKPIT BAR: NAVIGATION + CLASSIC SYSTEM LINKS + MOBILE QR + ROYAL QUARTET INSIDE
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-top-cockpit-bar" class="sl-glass-card" style="background: var(--sl-header-grad); padding: 6px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <a href="dashboard-pro.php" class="sl-btn" style="text-decoration:none; color:#38bdf8; border-color:rgba(56,189,248,0.4); padding:6px 11px; font-size:0.8rem;">🏠 الرئيسية</a>
                <a id="sl-classic-sales-btn" href="sales-delivery.php" class="sl-btn" style="text-decoration:none; color:#6ee7b7; border-color:rgba(16,185,129,0.4); padding:6px 11px; font-size:0.8rem;" title="النظام الكلاسيكي لتسليم المبيعات">📤 تسليم المبيعات</a>
                <a id="sl-classic-purchases-btn" href="purchase-receipt.php" class="sl-btn" style="text-decoration:none; color:#7dd3fc; border-color:rgba(14,165,233,0.4); padding:6px 11px; font-size:0.8rem;" title="النظام الكلاسيكي لاستلام المشتريات">📥 استلام المشتريات</a>

                <div style="margin-right: 4px;">
                    <h1 style="margin: 0; font-size: 1.05rem; font-weight: 900; color: #ffffff; display: flex; align-items: center; gap: 6px; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
                        <span id="sl-main-title-icon"><?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? '📥' : '📤'; ?></span>
                        <span id="sl-main-title-text"><?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? 'بوابة استلام المشتريات وتقسيمها الذكية (وارد — GRN)' : 'بوابة تسليم المبيعات وتقسيمها الذكية (صادر — GDN)'; ?></span>
                    </h1>
                </div>
            </div>

            <!-- MOBILE QR LASER BRIDGE + SMART LOCK GUARD + ROYAL QUARTET DOCKED HARMONIOUSLY INSIDE -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <div style="display:flex; align-items:center; gap:5px; background:rgba(2,6,23,0.75); padding:4px 9px; border-radius:10px; border:1px solid rgba(148,163,184,0.3);">
                    <span style="font-size:0.74rem; font-weight:900; color:#cbd5e1;">🛡️ حارس الـ QR:</span>
                    <select id="sl-qr-guard-mode" class="sl-input" style="padding:3px 8px !important; font-size:0.74rem !important; height:auto;">
                        <option value="auto">🤖 ذكي تلقائي</option>
                        <option value="lock">🔒 قفل القسم الحالي</option>
                    </select>
                </div>

                <button type="button" onclick="openSlMobileBridgeModal()" class="sl-btn" style="background: linear-gradient(135deg, #059669, #0284c7); border-color: #34d399; color: #ffffff; padding: 6px 12px; font-size: 0.8rem; box-shadow: 0 0 18px rgba(16,185,129,0.35);">
                    <span id="sl-bridge-live-dot" class="sl-pulse-dot" style="background:#fcd34d;"></span>
                    📱 ربط كاميرا الجوال
                </button>

                <button type="button" onclick="openSlFontModal()" class="sl-btn" style="background: rgba(168,85,247,0.18); border-color: rgba(192,132,252,0.5); color: #e9d5ff; padding: 6px 11px; font-size: 0.8rem;">
                    🎨 حجم ولون الخط
                </button>

                <!-- 👑 المخطوطة الرباعية الموحدة داخل الشريط العلوي بتناسق تام -->
                <div id="magaies-quartet-header-slot" style="display:flex; align-items:center;"></div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             2. GIANT CHAMELEON MODE SWITCHER (المبيعات 📤 ⇄ المشتريات 📥) — COMPACT
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="sl-mode-switcher">
            <!-- TAB 1: SALES DELIVERY (GDN - OUTGOING) -->
            <div id="sl-tab-sales" class="sl-mode-tab mode-sales active" onclick="switchLogisticsMode('sales')">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(16,185,129,0.22); border:1.5px solid #34d399; display:flex; align-items:center; justify-content:center; font-size:1.25rem; box-shadow:0 0 14px rgba(16,185,129,0.35);">
                        📤
                    </div>
                    <div>
                        <div style="font-size:0.96rem; font-weight:900; color:#ffffff; display:flex; align-items:center; gap:6px;">
                            منظومة المبيعات وتقسيمها وتسليمها (صادر — GDN)
                            <span class="sl-badge sl-badge-done" style="font-size:0.68rem; padding:2px 8px;">خصم مخزني (- Out)</span>
                        </div>
                        <div style="font-size:0.73rem; color:#a7f3d0; font-weight:700; margin-top:1px;">
                            صرف فواتير المبيعات • فحص الرصيد المتاح • تقسيم الدفعات • تصريح خروج البوابة (Gate Pass)
                        </div>
                    </div>
                </div>
                <div style="text-align:left;">
                    <div id="sl-tab-sales-badge" style="font-size:1.2rem; font-weight:900; color:#34d399; font-family:'Inter',sans-serif; line-height:1.1;">0</div>
                    <div style="font-size:0.68rem; color:#cbd5e1; font-weight:800;">قيد الصرف</div>
                </div>
            </div>

            <!-- TAB 2: PURCHASE RECEIPT (GRN - INCOMING) -->
            <div id="sl-tab-purchases" class="sl-mode-tab mode-purchases" onclick="switchLogisticsMode('purchases')">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(14,165,233,0.22); border:1.5px solid #38bdf8; display:flex; align-items:center; justify-content:center; font-size:1.25rem; box-shadow:0 0 14px rgba(14,165,233,0.35);">
                        📥
                    </div>
                    <div>
                        <div style="font-size:0.96rem; font-weight:900; color:#ffffff; display:flex; align-items:center; gap:6px;">
                            منظومة المشتريات وتقسيمها واستلامها (وارد — GRN)
                            <span class="sl-badge sl-badge-partial" style="font-size:0.68rem; padding:2px 8px;">إضافة مخزنية (+ In)</span>
                        </div>
                        <div style="font-size:0.73rem; color:#bae6fd; font-weight:700; margin-top:1px;">
                            استلام فواتير المشتريات • توزيع الأصناف على عدة مخازن • استلام بالوحدتين • مذكرة استلام (GRN)
                        </div>
                    </div>
                </div>
                <div style="text-align:left;">
                    <div id="sl-tab-purchases-badge" style="font-size:1.2rem; font-weight:900; color:#38bdf8; font-family:'Inter',sans-serif; line-height:1.1;">0</div>
                    <div style="font-size:0.68rem; color:#cbd5e1; font-weight:800;">قيد الاستلام</div>
                </div>
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
             3. LIVE KPI SUMMARY BAR (4 INTERACTIVE CARDS — COMPACT & LIFTED)
             ═══════════════════════════════════════════════════════════════════ -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 8px;">
            <div id="sl-kpi-card-pending" class="sl-glass-card sl-kpi-card active-filter" onclick="setSlRegistryFilter('pending')" style="border-right: 4px solid #f59e0b;">
                <div>
                    <div id="sl-kpi-lbl-pending" style="font-size: 0.78rem; font-weight: 800; color: #fcd34d;">⏳ فواتير جاهزة للصرف (قيد التسليم)</div>
                    <div id="sl-kpi-val-pending" style="font-size: 1.38rem; font-weight: 900; color: #fbbf24; margin-top: 1px; line-height: 1.1; font-family:'Inter',sans-serif;">0</div>
                </div>
                <span style="font-size: 1.6rem;">📦</span>
            </div>

            <div id="sl-kpi-card-partial" class="sl-glass-card sl-kpi-card" onclick="setSlRegistryFilter('partial')" style="border-right: 4px solid #38bdf8;">
                <div>
                    <div id="sl-kpi-lbl-partial" style="font-size: 0.78rem; font-weight: 800; color: #7dd3fc;">🌗 مقسمة على دفعات / بانتظار L4</div>
                    <div id="sl-kpi-val-partial" style="font-size: 1.38rem; font-weight: 900; color: #38bdf8; margin-top: 1px; line-height: 1.1; font-family:'Inter',sans-serif;">0</div>
                </div>
                <span style="font-size: 1.6rem;">✂️</span>
            </div>

            <div id="sl-kpi-card-done" class="sl-glass-card sl-kpi-card" onclick="setSlRegistryFilter('done')" style="border-right: 4px solid #10b981;">
                <div>
                    <div id="sl-kpi-lbl-done" style="font-size: 0.78rem; font-weight: 800; color: #6ee7b7;">✅ مكتملة ومعتمدة بالكامل</div>
                    <div id="sl-kpi-val-done" style="font-size: 1.38rem; font-weight: 900; color: #34d399; margin-top: 1px; line-height: 1.1; font-family:'Inter',sans-serif;">0</div>
                </div>
                <span style="font-size: 1.6rem;">📜</span>
            </div>

            <div id="sl-kpi-card-notes" class="sl-glass-card sl-kpi-card" onclick="setSlRegistryFilter('notes')" style="border-right: 4px solid #c084fc;">
                <div>
                    <div id="sl-kpi-lbl-notes" style="font-size: 0.78rem; font-weight: 800; color: #e9d5ff;">📊 سجل الأذونات الرسمية (GDN / GRN)</div>
                    <div id="sl-kpi-val-notes" style="font-size: 1.38rem; font-weight: 900; color: #c084fc; margin-top: 1px; line-height: 1.1; font-family:'Inter',sans-serif;">0</div>
                </div>
                <span style="font-size: 1.6rem;">🏷️</span>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             4. SMART INTERACTIVE REGISTRY (+1000 INVOICES & INSTANT ACTIONS — SPACIOUS & LIFTED)
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="sl-glass-card" style="padding: 10px 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="font-size: 1.2rem;">📋</span>
                    <h3 id="sl-registry-title" style="margin: 0; font-size: 0.98rem; font-weight: 900; color: #ffffff;">
                        السجل الذكي الفوري لفواتير وأذونات المبيعات (بحث لحظي في +1000 فاتورة، تسليم سريع أو تقسيم)
                    </h3>
                    <span id="sl-reg-count-badge" class="sl-badge sl-badge-partial">0 مستند</span>
                </div>

                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <input type="text" id="sl-reg-search" class="sl-input" placeholder="🔍 ابحث برقم الفاتورة، الإذن، العميل/المورد، أو اسم الصنف..." oninput="renderSlRegistry()" onkeydown="if(event.key==='Enter'){event.preventDefault(); quickLoadFirstMatch(this.value);}" style="min-width: 240px; font-size: 0.8rem; padding: 6px 12px !important;">

                    <select id="sl-reg-wh-filter" class="sl-input" onchange="renderSlRegistry()" style="font-size: 0.78rem; padding: 6px 10px !important; color: #fcd34d !important;">
                        <option value="all">🏢 كل المخازن</option>
                    </select>

                    <select id="sl-reg-limit" class="sl-input" onchange="renderSlRegistry()" style="font-size: 0.78rem; padding: 6px 10px !important;">
                        <option value="6" selected>⚡ 6 فواتير (عرض مريح لكامل الشاشة)</option>
                        <option value="15">⚡ 15 مستند</option>
                        <option value="50">⚡ 50 مستند</option>
                        <option value="100">⚡ 100 مستند</option>
                        <option value="250">⚡ 250 مستند</option>
                        <option value="99999">📋 الكل (+1000)</option>
                    </select>

                    <button type="button" id="sl-filter-btn-pending" onclick="setSlRegistryFilter('pending')" class="sl-btn" style="background: var(--sl-primary); color: #fff; border-color: var(--sl-primary-light); padding: 6px 11px; font-size: 0.78rem;">⏳ قيد الإجراء</button>
                    <button type="button" id="sl-filter-btn-partial" onclick="setSlRegistryFilter('partial')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">🌗 الجزئي / البوابة</button>
                    <button type="button" id="sl-filter-btn-done" onclick="setSlRegistryFilter('done')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">✅ المكتمل</button>
                    <button type="button" id="sl-filter-btn-notes" onclick="setSlRegistryFilter('notes')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">🏷️ سجل الأذونات</button>
                    <button type="button" id="sl-filter-btn-all" onclick="setSlRegistryFilter('all')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">📋 الكل</button>
                    <button type="button" onclick="refreshAllSlData()" class="sl-btn" style="color:#38bdf8; border-color:rgba(56,189,248,0.4); padding: 6px 11px; font-size: 0.78rem;">🔄 تحديث</button>
                </div>
            </div>

            <!-- 🎡 بدون أي حبس للرولر (No inner vertical scroll trap): تتحرك الشاشة كلها بحرية تامة صعوداً وهبوطاً -->
            <div id="sl-registry-scroll-wrap" style="overflow-x: auto; overflow-y: visible !important; max-height: none !important; border-radius: 12px; border: 1px solid rgba(148,163,184,0.22);">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th style="text-align: right; width: 12%;">رقم الفاتورة / الإذن</th>
                            <th style="text-align: center; width: 12%;">التاريخ والوقت</th>
                            <th id="sl-reg-th-party" style="text-align: right; width: 16%;">العميل / المستلم</th>
                            <th style="text-align: right; width: 32%;">الأصناف والوحدات (اضغط على أي صنف لتقسيمه فوراً)</th>
                            <th style="text-align: center; width: 12%;">المخزن والحالة</th>
                            <th style="text-align: center; width: 16%;">إجراءات سريعة</th>
                        </tr>
                    </thead>
                    <tbody id="sl-registry-tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             5. WORKSTATION COCKPIT: DOCUMENT CARD + QR CAMERA & PHOTO PROOF (COMPACT & VISIBLE)
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-inspection-cockpit" style="display: grid; grid-template-columns: 1fr 340px; gap: 12px;">

            <!-- RIGHT: DOCUMENT META & LOGISTICS FIELDS -->
            <div class="sl-glass-card" style="padding: 12px 16px; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.25rem;">📄</span>
                        <div>
                            <h3 id="sl-doc-card-title" style="margin: 0; font-size: 0.98rem; font-weight: 900; color: #ffffff;">
                                بطاقة الفحص والتقسيم والبيانات اللوجستية
                            </h3>
                            <span id="sl-doc-card-sub" style="font-size: 0.74rem; color: #94a3b8; font-weight: 700;">
                                اختر فاتورة من السجل العلوي أو امسح باركود الـ QR لبدء التقسيم أو الاعتماد الفوري
                            </span>
                        </div>
                    </div>
                    <div id="sl-doc-status-pill" class="sl-badge sl-badge-pending">جاهز للاستدعاء</div>
                </div>

                <!-- 4 INFO BOXES -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 8px;">
                    <div style="background: rgba(2,6,23,0.65); padding: 7px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.07);">
                        <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 800; display: block;">📄 رقم المستند / الإذن</label>
                        <div id="sl-lbl-inv-no" style="font-size: 1.05rem; font-weight: 900; color: var(--sl-primary-light); font-family: 'Inter', monospace; margin-top: 1px;">---</div>
                    </div>
                    <div style="background: rgba(2,6,23,0.65); padding: 7px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.07);">
                        <label id="sl-lbl-party-title" style="font-size: 0.72rem; color: #94a3b8; font-weight: 800; display: block;">👤 العميل / المورد صاحب الفاتورة</label>
                        <div id="sl-lbl-party-name" style="font-size: 0.96rem; font-weight: 900; color: #ffffff; margin-top: 1px;">---</div>
                    </div>
                    <div style="background: rgba(2,6,23,0.65); padding: 7px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.07);">
                        <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 800; display: block;">📅 تاريخ المستند</label>
                        <div id="sl-lbl-date" style="font-size: 0.9rem; font-weight: 800; color: #e2e8f0; margin-top: 1px;">---</div>
                    </div>
                    <div style="background: rgba(2,6,23,0.65); padding: 7px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.07);">
                        <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 800; display: block;">🔐 البصمة الأمنية (QR Hash)</label>
                        <div id="sl-lbl-hash" style="font-size: 0.8rem; font-weight: 800; color: #38bdf8; font-family: 'Inter', monospace; margin-top: 2px;">---</div>
                    </div>
                </div>

                <!-- LOGISTICS INPUTS (WAREHOUSE, RECEIVER, DRIVER, PLATE, GATE PASS) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(155px, 1fr)); gap: 8px;">
                    <div>
                        <label id="sl-lbl-wh-select" style="font-size: 0.74rem; font-weight: 800; color: #fcd34d; display: block; margin-bottom: 3px;">🏢 المستودع الافتراضي (للكل):</label>
                        <select id="sl-master-warehouse" class="sl-input" style="width: 100%; padding: 6px 10px !important; font-size: 0.82rem !important;" onchange="onSlMasterWarehouseChange(this.value)"></select>
                    </div>
                    <div>
                        <label id="sl-lbl-receiver" style="font-size: 0.74rem; font-weight: 800; color: #6ee7b7; display: block; margin-bottom: 3px;">🤝 المستلم / المسلم الفعلي:</label>
                        <input type="text" id="sl-receiver-name" class="sl-input" placeholder="الاسم..." style="width: 100%; padding: 6px 10px !important; font-size: 0.82rem !important;">
                    </div>
                    <div>
                        <label style="font-size: 0.74rem; font-weight: 800; color: #7dd3fc; display: block; margin-bottom: 3px;">🚚 اسم السائق / المندوب:</label>
                        <input type="text" id="sl-driver-name" class="sl-input" placeholder="اسم السائق..." style="width: 100%; padding: 6px 10px !important; font-size: 0.82rem !important;">
                    </div>
                    <div>
                        <label style="font-size: 0.74rem; font-weight: 800; color: #c084fc; display: block; margin-bottom: 3px;">🔢 رقم المركبة / اللوحة:</label>
                        <input type="text" id="sl-truck-plate" class="sl-input" placeholder="رقم اللوحة..." style="width: 100%; padding: 6px 10px !important; font-size: 0.82rem !important;">
                    </div>
                    <div>
                        <label style="font-size: 0.74rem; font-weight: 800; color: #38bdf8; display: block; margin-bottom: 3px;">🎫 رقم التصريح / المرجع:</label>
                        <input type="text" id="sl-gate-pass-no" class="sl-input" placeholder="تلقائي..." style="width: 100%; padding: 6px 10px !important; font-size: 0.82rem !important;">
                    </div>
                </div>

                <!-- NOTES ROW -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div>
                        <label style="font-size: 0.74rem; font-weight: 800; color: #fbbf24; display: block; margin-bottom: 3px;">📝 ملاحظات أمين المخزن وفحص البضاعة:</label>
                        <input type="text" id="sl-warehouse-notes" class="sl-input" placeholder="سجل حالة البضاعة أو تفاصيل الدفعة..." style="width: 100%; padding: 6px 10px !important; font-size: 0.82rem !important;">
                    </div>
                    <div>
                        <label style="font-size: 0.74rem; font-weight: 800; color: #94a3b8; display: block; margin-bottom: 3px;">📌 ملاحظات إضافية / البوابة:</label>
                        <input type="text" id="sl-personal-notes" class="sl-input" placeholder="ملاحظات اختيارية..." style="width: 100%; padding: 6px 10px !important; font-size: 0.82rem !important;">
                    </div>
                </div>
            </div>

            <!-- LEFT: LASER QR CAMERA & VISUAL PROOF -->
            <div class="sl-glass-card" style="padding: 12px 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <h3 style="margin: 0; font-size: 0.96rem; font-weight: 900; color: #ffffff;">📷 المسح الضوئي (QR) والتوثيق</h3>
                    <span id="sl-verify-badge" class="sl-badge sl-badge-done">👑 جاهز للاعتماد</span>
                </div>

                <label style="display:flex; align-items:center; gap:8px; background:var(--sl-primary-soft); border:1px solid var(--sl-border-glow); padding:8px 12px; border-radius:10px; font-size:0.78rem; font-weight:900; color:#fff; cursor:pointer;">
                    <input type="checkbox" id="sl-auto-execute-qr" checked style="width:16px; height:16px; accent-color:var(--sl-primary);">
                    <span>⚡ اعتماد فوري متتابع عند المسح بدون ضغط أي زر</span>
                </label>

                <div id="sl-qr-reader-wrap" style="display:none; flex-direction:column; gap:8px;">
                    <div id="sl-qr-reader" style="width:100%; border-radius:12px; overflow:hidden; border:2px solid var(--sl-primary-light); aspect-ratio:4/3; background:#000;"></div>
                    <button type="button" onclick="stopSlLocalCamera()" class="sl-btn" style="background:rgba(239,68,68,0.2); border-color:#ef4444; color:#fca5a5;">⏹️ إيقاف الكاميرا</button>
                </div>

                <div id="sl-camera-placeholder" style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; background:rgba(2,6,23,0.55); border:1.5px dashed rgba(56,189,248,0.35); border-radius:14px; padding:14px; text-align:center;">
                    <div style="font-size:2.1rem; margin-bottom:4px;">📱⚡</div>
                    <div style="font-size:0.78rem; color:#cbd5e1; font-weight:700; margin-bottom:10px;">
                        امسح باركود الفاتورة أو الإذن بالجوال أو الكاميرا للتنفيذ الفوري
                    </div>
                    <div style="display:flex; gap:8px; width:100%; flex-wrap:wrap;">
                        <button type="button" onclick="openSlMobileBridgeModal()" class="sl-btn" style="flex:1; background:linear-gradient(135deg,#059669,#0284c7); border-color:#34d399; color:#fff; font-size:0.8rem;">
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
                        📎 إرفاق صورة البضاعة / المستند
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
        <div class="sl-glass-card" style="padding: 18px 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.5rem;">⚖️</span>
                    <div>
                        <h3 id="sl-grid-title" style="margin: 0; font-size: 1.1rem; font-weight: 900; color: #ffffff;">
                            جدول الفحص والتقسيم الذكي بالوحدتين والمخازن (استلم/سلّم بالأساسية ⇄ أو بالفرعية)
                        </h3>
                        <span style="font-size: 0.79rem; color: #94a3b8; font-weight: 700;">
                            يمكنك تقسيم نفس الفاتورة على دفعات أو اختيار مخزن مستقل لكل صنف في نفس اللحظة
                        </span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <button type="button" onclick="fillAllSlQuantities(1)" class="sl-btn" style="background: rgba(16,185,129,0.2); border-color: #34d399; color: #6ee7b7;">
                        ⚡ تعبئة كامل المتبقي (100%)
                    </button>
                    <button type="button" onclick="fillAllSlQuantities(0.5)" class="sl-btn" style="background: rgba(56,189,248,0.18); border-color: #38bdf8; color: #7dd3fc;">
                        🌗 تقسيم نصف الكمية (50%)
                    </button>
                    <button type="button" onclick="fillAllSlQuantities(0)" class="sl-btn" style="background: rgba(239,68,68,0.16); border-color: #f87171; color: #fca5a5;">
                        🧹 تصفير الخانات (0)
                    </button>
                    <button type="button" onclick="const d=document.getElementById('sl-cols-drawer'); d.style.display=(d.style.display==='none'?'flex':'none');" class="sl-btn" style="background: rgba(168,85,247,0.18); border-color: #c084fc; color: #e9d5ff;">
                        ⚙️ ضبط الأعمدة والعرض
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
    <script defer src="js/magaies-smart-guide.js?v=20261022_v23"></script>
    <script defer src="js/smart-logistics.js?v=20261022_v2"></script>
    <script src="js/global_background_media.js?v=20261022_v23" defer></script>
    <script src="js/magaiees_back.js?v=20261022_v23" defer></script>
</body>
</html>
