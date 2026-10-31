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
    // Sync 7-Theme System & Universal Light/Dark Mode Before First Paint & Dynamically (Constitution 1 Order 1)
    window.syncSlUniversalThemeSystem = function() {
        var rawTheme = localStorage.getItem('magaies_active_theme') ||
                       localStorage.getItem('magaiees_global_theme') ||
                       localStorage.getItem('magaies_theme_mode') ||
                       localStorage.getItem('ui_theme') || '';
        var lightThemes = ['warm_cream', 'ultra_white', 'exec_gray', 'light', 'royal-dawn', 'pearl-white', 'warm-alabaster', 'pure-platinum'];
        var isLightMode = lightThemes.includes(rawTheme) || localStorage.getItem('magaies_theme_mode') === 'light';
        if (isLightMode) {
            document.documentElement.classList.add('light-theme');
            document.documentElement.setAttribute('data-magaies-theme-mode', 'light');
            if (document.body) {
                document.body.classList.add('light-theme', 'theme-light');
                document.body.setAttribute('data-theme', 'light');
            }
        } else {
            document.documentElement.classList.remove('light-theme');
            document.documentElement.setAttribute('data-magaies-theme-mode', 'dark');
            if (document.body) {
                document.body.classList.remove('light-theme', 'theme-light');
                document.body.setAttribute('data-theme', 'dark');
            }
        }
        return isLightMode;
    };
    window.syncSlUniversalThemeSystem();
    document.addEventListener('DOMContentLoaded', function() {
        window.syncSlUniversalThemeSystem();
    });
    window.addEventListener('storage', function(e) {
        if (!e || !e.key || /theme/i.test(e.key)) {
            window.syncSlUniversalThemeSystem();
        }
    });
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
        .sl-badge-pending { background: rgba(245, 158, 11, 0.18); color: #fcd34d; border: 1.5px solid rgba(245, 158, 11, 0.55); }
        .sl-badge-partial { background: rgba(56, 189, 248, 0.18); color: #7dd3fc; border: 1.5px solid rgba(56, 189, 248, 0.55); }
        .sl-badge-done { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1.5px solid rgba(16, 185, 129, 0.6); }

        /* ── VISUAL SAVE PULSE (النبض البصري عند الاعتماد والحفظ) ── */
        @keyframes slSavePulseAnim {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.85), 0 0 30px rgba(16, 185, 129, 0.65); border-color: #10b981 !important; }
            50% { box-shadow: 0 0 0 14px rgba(16, 185, 129, 0), 0 0 45px rgba(52, 211, 153, 0.85); border-color: #34d399 !important; }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .sl-save-pulse-active {
            animation: slSavePulseAnim 1.15s cubic-bezier(0.22, 1, 0.36, 1) 2 !important;
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           CONSTITUTION 1 & 4: STRICT FONT CONTRAST LAW, +2PX BORDERS (2.2px), +2 SVG ICON STROKES,
           ZERO COLOR INVERSION ON FOCUS/AUTOFILL, FUTURE-PROOF INHERITANCE,
           AND 100% NESTED CHILD MODAL THEME SUPPORT (DARK = #FFFFFF | LIGHT = #000000)
           ═══════════════════════════════════════════════════════════════════════════ */
        :root {
            --sl-border-thickness: 2.2px;
            --sl-icon-stroke: 2.6px;
        }

        .sl-glass-card,
        .sl-btn,
        .sl-input,
        .sl-unit-box,
        .sl-floating-summary,
        .sl-mode-switcher,
        .sl-badge,
        input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        select,
        textarea,
        button:not(.msg-trigger-btn) {
            border-width: 2.2px !important;
            border-style: solid !important;
            font-weight: 900 !important;
        }

        svg, svg path, svg circle, svg rect, svg line, svg polyline, svg polygon {
            stroke-width: 2.6px !important;
        }

        /* 🌙 DARK MODE: STEPPED SURROUNDING SURFACE (#0F1B33) + PURE BLACK (#000000) INPUTS WITH PURE WHITE (#FFFFFF) BORDERS & TEXT */
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-glass-card {
            background: linear-gradient(155deg, rgba(15, 27, 51, 0.95) 0%, rgba(10, 19, 38, 0.97) 100%) !important;
            border: 2.2px solid rgba(255, 255, 255, 0.85) !important;
            color: #FFFFFF !important;
        }

        /* Prevent input color inversion on focus, active, focus-within, or browser autofill in Dark Mode */
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-input,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-input:focus,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-input:active,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-input:focus-within,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):focus,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):active,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) select,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) select:focus,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) textarea,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) textarea:focus {
            background: #000000 !important;
            background-color: #000000 !important;
            color: #FFFFFF !important;
            -webkit-text-fill-color: #FFFFFF !important;
            border: 2.2px solid #FFFFFF !important;
            caret-color: #FFFFFF !important;
            font-weight: 900 !important;
        }
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-input:-webkit-autofill,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-input:-webkit-autofill:hover,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-input:-webkit-autofill:focus,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) input:-webkit-autofill,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) input:-webkit-autofill:focus {
            -webkit-text-fill-color: #FFFFFF !important;
            -webkit-box-shadow: 0 0 0px 1000px #000000 inset !important;
            transition: background-color 5000s ease-in-out 0s !important;
        }
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-input::placeholder,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) input::placeholder,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) textarea::placeholder {
            color: rgba(255, 255, 255, 0.55) !important;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.55) !important;
            font-weight: 700 !important;
        }

        /* ☀️ LIGHT THEME ("فجر السجناء الملكي" & ALL LIGHT MODES): PITCH-BLACK #000000 TEXT & 2.2PX BLACK BORDERS ON STEPPED SURFACES */
        html[data-magaies-theme-mode="light"] body,
        html.light-theme body,
        body.theme-light,
        body.light-theme,
        body[data-theme="light"],
        body[data-msg-theme="royal-dawn"] {
            background: #E2E8F0 !important;
            color: #000000 !important;
            --sl-grid-font-color: #000000;
            --sl-card-bg: linear-gradient(155deg, #F8FAFC 0%, #F1F5F9 100%);
            --sl-header-grad: linear-gradient(135deg, #E0F2FE 0%, #F1F5F9 55%, #EDE9FE 100%);
            --sl-unit-box-bg: linear-gradient(145deg, #F8FAFC 0%, #EFF6FF 100%);
        }

        html[data-magaies-theme-mode="light"] .sl-glass-card,
        body.theme-light .sl-glass-card,
        body.light-theme .sl-glass-card,
        body[data-theme="light"] .sl-glass-card,
        body[data-msg-theme="royal-dawn"] .sl-glass-card {
            background: #F8FAFC !important;
            border: 2.2px solid #000000 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08), inset 0 1px 0 #FFFFFF !important;
            color: #000000 !important;
        }

        html[data-magaies-theme-mode="light"] #sl-top-cockpit-bar,
        body.theme-light #sl-top-cockpit-bar,
        body.light-theme #sl-top-cockpit-bar,
        body[data-theme="light"] #sl-top-cockpit-bar,
        body[data-msg-theme="royal-dawn"] #sl-top-cockpit-bar {
            background: linear-gradient(135deg, #E0F2FE 0%, #F8FAFC 55%, #EDE9FE 100%) !important;
            border: 2.2px solid #000000 !important;
        }

        /* Force all headings, labels, spans, icons, and table cells in Light Mode to crisp pitch-black #000000 */
        html[data-magaies-theme-mode="light"] h1, html[data-magaies-theme-mode="light"] h2, html[data-magaies-theme-mode="light"] h3, html[data-magaies-theme-mode="light"] h4,
        html[data-magaies-theme-mode="light"] label, html[data-magaies-theme-mode="light"] th, html[data-magaies-theme-mode="light"] td, html[data-magaies-theme-mode="light"] span,
        body.theme-light h1, body.theme-light h2, body.theme-light h3, body.theme-light h4,
        body.theme-light label, body.theme-light th, body.theme-light td, body.theme-light span,
        body.light-theme h1, body.light-theme h2, body.light-theme h3, body.light-theme h4,
        body.light-theme label, body.light-theme th, body.light-theme td,
        body.light-theme #sl-main-title-text,
        body.light-theme #sl-registry-title,
        body.light-theme #sl-doc-card-title,
        body.light-theme #sl-grid-title,
        body.light-theme #sl-lbl-party-name,
        body.light-theme #sl-lbl-date,
        body.light-theme #sl-lbl-inv-no,
        body.light-theme #sl-lbl-hash,
        body[data-theme="light"] h1, body[data-theme="light"] h3, body[data-theme="light"] label, body[data-theme="light"] th, body[data-theme="light"] td,
        body[data-msg-theme="royal-dawn"] h1, body[data-msg-theme="royal-dawn"] h3, body[data-msg-theme="royal-dawn"] label, body[data-msg-theme="royal-dawn"] th, body[data-msg-theme="royal-dawn"] td {
            color: #000000 !important;
            text-shadow: none !important;
            font-weight: 900 !important;
        }

        html[data-magaies-theme-mode="light"] svg,
        html[data-magaies-theme-mode="light"] svg *,
        body.theme-light svg,
        body.theme-light svg *,
        body.light-theme svg,
        body.light-theme svg * {
            stroke: #000000 !important;
        }

        html[data-magaies-theme-mode="light"] .sl-input,
        html[data-magaies-theme-mode="light"] .sl-input:focus,
        html[data-magaies-theme-mode="light"] .sl-input:active,
        html[data-magaies-theme-mode="light"] input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        html[data-magaies-theme-mode="light"] input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):focus,
        html[data-magaies-theme-mode="light"] select,
        html[data-magaies-theme-mode="light"] select:focus,
        html[data-magaies-theme-mode="light"] textarea,
        html[data-magaies-theme-mode="light"] textarea:focus,
        body.theme-light .sl-input,
        body.theme-light .sl-input:focus,
        body.theme-light input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        body.theme-light select,
        body.theme-light textarea,
        body.light-theme .sl-input,
        body.light-theme .sl-input:focus,
        body.light-theme .sl-input:active,
        body[data-theme="light"] .sl-input,
        body[data-msg-theme="royal-dawn"] .sl-input {
            background: #FFFFFF !important;
            background-color: #FFFFFF !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: 2.2px solid #000000 !important;
            caret-color: #000000 !important;
            font-weight: 900 !important;
            box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.06) !important;
        }
        html[data-magaies-theme-mode="light"] .sl-input::placeholder,
        html[data-magaies-theme-mode="light"] input::placeholder,
        body.theme-light .sl-input::placeholder,
        body.light-theme .sl-input::placeholder,
        body[data-theme="light"] .sl-input::placeholder,
        body[data-msg-theme="royal-dawn"] .sl-input::placeholder {
            color: rgba(0, 0, 0, 0.55) !important;
            -webkit-text-fill-color: rgba(0, 0, 0, 0.55) !important;
            font-weight: 700 !important;
        }
        html[data-magaies-theme-mode="light"] .sl-input:-webkit-autofill,
        html[data-magaies-theme-mode="light"] input:-webkit-autofill,
        body.theme-light .sl-input:-webkit-autofill,
        body.light-theme .sl-input:-webkit-autofill,
        body[data-theme="light"] .sl-input:-webkit-autofill,
        body[data-msg-theme="royal-dawn"] .sl-input:-webkit-autofill {
            -webkit-text-fill-color: #000000 !important;
            -webkit-box-shadow: 0 0 0px 1000px #FFFFFF inset !important;
        }

        html[data-magaies-theme-mode="light"] .sl-btn,
        body.theme-light .sl-btn,
        body.light-theme .sl-btn,
        body[data-theme="light"] .sl-btn,
        body[data-msg-theme="royal-dawn"] .sl-btn {
            background: #FFFFFF !important;
            color: #000000 !important;
            border: 2.2px solid #000000 !important;
            font-weight: 900 !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08) !important;
        }

        html[data-magaies-theme-mode="light"] .sl-table thead th,
        body.theme-light .sl-table thead th,
        body.light-theme .sl-table thead th,
        body[data-theme="light"] .sl-table thead th,
        body[data-msg-theme="royal-dawn"] .sl-table thead th {
            background: #E2E8F0 !important;
            color: #000000 !important;
            border-bottom: 2.5px solid #000000 !important;
        }
        html[data-magaies-theme-mode="light"] .sl-table tbody tr:hover,
        body.theme-light .sl-table tbody tr:hover,
        body.light-theme .sl-table tbody tr:hover,
        body[data-theme="light"] .sl-table tbody tr:hover,
        body[data-msg-theme="royal-dawn"] .sl-table tbody tr:hover {
            background: #EFF6FF !important;
        }
        html[data-magaies-theme-mode="light"] .sl-unit-box,
        body.theme-light .sl-unit-box,
        body.light-theme .sl-unit-box,
        body[data-theme="light"] .sl-unit-box,
        body[data-msg-theme="royal-dawn"] .sl-unit-box {
            background: #F8FAFC !important;
            border: 2.2px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06) !important;
        }
        html[data-magaies-theme-mode="light"] .sl-floating-summary,
        body.theme-light .sl-floating-summary,
        body.light-theme .sl-floating-summary,
        body[data-theme="light"] .sl-floating-summary,
        body[data-msg-theme="royal-dawn"] .sl-floating-summary {
            background: linear-gradient(135deg, #FFFFFF, #F1F5F9) !important;
            border: 2.5px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16) !important;
        }
        html[data-magaies-theme-mode="light"] .sl-floating-summary span,
        html[data-magaies-theme-mode="light"] .sl-floating-summary strong,
        html[data-magaies-theme-mode="light"] .sl-floating-summary div,
        body.theme-light .sl-floating-summary span,
        body.theme-light .sl-floating-summary strong,
        body.light-theme .sl-floating-summary span,
        body.light-theme .sl-floating-summary strong,
        body.light-theme .sl-floating-summary div,
        body[data-theme="light"] .sl-floating-summary span,
        body[data-theme="light"] .sl-floating-summary strong,
        body[data-msg-theme="royal-dawn"] .sl-floating-summary span,
        body[data-msg-theme="royal-dawn"] .sl-floating-summary strong {
            color: #000000 !important;
        }
        html[data-magaies-theme-mode="light"] .sl-badge-pending, body.theme-light .sl-badge-pending, body.light-theme .sl-badge-pending { background: #FEF3C7 !important; color: #000000 !important; border: 2.2px solid #000000 !important; }
        html[data-magaies-theme-mode="light"] .sl-badge-partial, body.theme-light .sl-badge-partial, body.light-theme .sl-badge-partial { background: #E0F2FE !important; color: #000000 !important; border: 2.2px solid #000000 !important; }
        html[data-magaies-theme-mode="light"] .sl-badge-done, body.theme-light .sl-badge-done, body.light-theme .sl-badge-done { background: #D1FAE5 !important; color: #000000 !important; border: 2.2px solid #000000 !important; }

        /* Nested Child Modals in Light Mode (الشاشات الوليدة المتداخلة — شاشة داخل شاشة داخل شاشة) */
        html[data-magaies-theme-mode="light"] #sl-mobile-bridge-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-font-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-weighbridge-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-signature-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-debit-note-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-quick-warehouse-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-confirm-undo-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-drilldown-invoice-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-cols-drawer,
        body.theme-light #sl-mobile-bridge-modal .sl-glass-card,
        body.theme-light #sl-font-modal .sl-glass-card,
        body.theme-light #sl-weighbridge-modal .sl-glass-card,
        body.theme-light #sl-signature-modal .sl-glass-card,
        body.theme-light #sl-debit-note-modal .sl-glass-card,
        body.theme-light #sl-quick-warehouse-modal .sl-glass-card,
        body.theme-light #sl-confirm-undo-modal .sl-glass-card,
        body.theme-light #sl-drilldown-invoice-modal .sl-glass-card,
        body.theme-light #sl-cols-drawer,
        body.light-theme #sl-mobile-bridge-modal .sl-glass-card,
        body.light-theme #sl-font-modal .sl-glass-card,
        body.light-theme #sl-weighbridge-modal .sl-glass-card,
        body.light-theme #sl-signature-modal .sl-glass-card,
        body.light-theme #sl-debit-note-modal .sl-glass-card,
        body.light-theme #sl-quick-warehouse-modal .sl-glass-card,
        body.light-theme #sl-confirm-undo-modal .sl-glass-card,
        body.light-theme #sl-drilldown-invoice-modal .sl-glass-card,
        body.light-theme #sl-cols-drawer {
            background: #F8FAFC !important;
            color: #000000 !important;
            border: 2.5px solid #000000 !important;
        }
        html[data-magaies-theme-mode="light"] #sl-mobile-bridge-modal div,
        html[data-magaies-theme-mode="light"] #sl-font-modal div,
        html[data-magaies-theme-mode="light"] #sl-weighbridge-modal div,
        html[data-magaies-theme-mode="light"] #sl-signature-modal div,
        html[data-magaies-theme-mode="light"] #sl-debit-note-modal div,
        html[data-magaies-theme-mode="light"] #sl-quick-warehouse-modal div,
        html[data-magaies-theme-mode="light"] #sl-confirm-undo-modal div,
        html[data-magaies-theme-mode="light"] #sl-drilldown-invoice-modal div,
        html[data-magaies-theme-mode="light"] #sl-drilldown-invoice-modal span,
        html[data-magaies-theme-mode="light"] #sl-cols-drawer span,
        html[data-magaies-theme-mode="light"] #sl-cols-drawer label,
        body.theme-light #sl-mobile-bridge-modal div,
        body.theme-light #sl-font-modal div,
        body.theme-light #sl-weighbridge-modal div,
        body.theme-light #sl-signature-modal div,
        body.theme-light #sl-debit-note-modal div,
        body.theme-light #sl-quick-warehouse-modal div,
        body.theme-light #sl-confirm-undo-modal div,
        body.theme-light #sl-drilldown-invoice-modal div,
        body.theme-light #sl-drilldown-invoice-modal span,
        body.light-theme #sl-mobile-bridge-modal div,
        body.light-theme #sl-font-modal div,
        body.light-theme #sl-weighbridge-modal div,
        body.light-theme #sl-signature-modal div,
        body.light-theme #sl-debit-note-modal div,
        body.light-theme #sl-quick-warehouse-modal div,
        body.light-theme #sl-confirm-undo-modal div,
        body.light-theme #sl-drilldown-invoice-modal div,
        body.light-theme #sl-drilldown-invoice-modal span,
        body.light-theme #sl-cols-drawer span,
        body.light-theme #sl-cols-drawer label {
            color: #000000 !important;
        }
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) select option {
            background: #000000 !important;
            background-color: #000000 !important;
            color: #FFFFFF !important;
            font-weight: 900 !important;
        }
        html[data-magaies-theme-mode="light"] select option,
        body.theme-light select option,
        body.light-theme select option,
        body[data-theme="light"] select option,
        body[data-msg-theme="royal-dawn"] select option {
            background: #FFFFFF !important;
            background-color: #FFFFFF !important;
            color: #000000 !important;
            font-weight: 900 !important;
        }

        /* Universal Future-Proof Nested Child Modals Rule (أمر 5 و6: سريان السُمك وثبات الألوان والتوريث التلقائي على كل الشاشات الوليدة) */
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) [id^="sl-"][id$="-modal"] .sl-glass-card {
            background: #0F1B33 !important;
            color: #FFFFFF !important;
            border: 2.5px solid #FFFFFF !important;
        }
        html[data-magaies-theme-mode="light"] [id^="sl-"][id$="-modal"] .sl-glass-card,
        body.theme-light [id^="sl-"][id$="-modal"] .sl-glass-card,
        body.light-theme [id^="sl-"][id$="-modal"] .sl-glass-card,
        body[data-theme="light"] [id^="sl-"][id$="-modal"] .sl-glass-card,
        body[data-msg-theme="royal-dawn"] [id^="sl-"][id$="-modal"] .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-batch-ocr-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-bin-heatmap-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-fleet-dispatch-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-load-verifier-modal .sl-glass-card,
        html[data-magaies-theme-mode="light"] #sl-cargo-estimator-modal .sl-glass-card,
        body.theme-light #sl-batch-ocr-modal .sl-glass-card,
        body.theme-light #sl-bin-heatmap-modal .sl-glass-card,
        body.theme-light #sl-fleet-dispatch-modal .sl-glass-card,
        body.theme-light #sl-load-verifier-modal .sl-glass-card,
        body.theme-light #sl-cargo-estimator-modal .sl-glass-card,
        body.light-theme #sl-batch-ocr-modal .sl-glass-card,
        body.light-theme #sl-bin-heatmap-modal .sl-glass-card,
        body.light-theme #sl-fleet-dispatch-modal .sl-glass-card,
        body.light-theme #sl-load-verifier-modal .sl-glass-card,
        body.light-theme #sl-cargo-estimator-modal .sl-glass-card {
            background: #F8FAFC !important;
            color: #000000 !important;
            border: 2.5px solid #000000 !important;
        }
        html[data-magaies-theme-mode="light"] [id^="sl-"][id$="-modal"] div,
        html[data-magaies-theme-mode="light"] [id^="sl-"][id$="-modal"] span,
        html[data-magaies-theme-mode="light"] [id^="sl-"][id$="-modal"] h3,
        html[data-magaies-theme-mode="light"] [id^="sl-"][id$="-modal"] label,
        body.theme-light [id^="sl-"][id$="-modal"] div,
        body.theme-light [id^="sl-"][id$="-modal"] span,
        body.theme-light [id^="sl-"][id$="-modal"] h3,
        body.light-theme [id^="sl-"][id$="-modal"] div,
        body.light-theme [id^="sl-"][id$="-modal"] span,
        body.light-theme [id^="sl-"][id$="-modal"] h3,
        html[data-magaies-theme-mode="light"] #sl-batch-ocr-modal div,
        html[data-magaies-theme-mode="light"] #sl-bin-heatmap-modal div,
        html[data-magaies-theme-mode="light"] #sl-fleet-dispatch-modal div,
        html[data-magaies-theme-mode="light"] #sl-load-verifier-modal div,
        html[data-magaies-theme-mode="light"] #sl-cargo-estimator-modal div,
        body.theme-light #sl-batch-ocr-modal div,
        body.theme-light #sl-bin-heatmap-modal div,
        body.theme-light #sl-fleet-dispatch-modal div,
        body.theme-light #sl-load-verifier-modal div,
        body.theme-light #sl-cargo-estimator-modal div,
        body.light-theme #sl-batch-ocr-modal div,
        body.light-theme #sl-bin-heatmap-modal div,
        body.light-theme #sl-fleet-dispatch-modal div,
        body.light-theme #sl-load-verifier-modal div,
        body.light-theme #sl-cargo-estimator-modal div {
            color: #000000 !important;
        }

        /* ☀️ PROPOSAL 1: OUTDOOR SUNLIGHT ULTRA-CONTRAST MODE (وضع الرؤية الفائقة تحت أشعة الشمس المباشرة) */
        body.sl-sunlight-mode {
            --sl-grid-font-size: 1.06rem !important;
            filter: contrast(1.12);
        }
        body.sl-sunlight-mode .sl-glass-card,
        body.sl-sunlight-mode .sl-btn,
        body.sl-sunlight-mode .sl-input,
        body.sl-sunlight-mode .sl-unit-box,
        body.sl-sunlight-mode .sl-floating-summary,
        body.sl-sunlight-mode input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        body.sl-sunlight-mode select,
        body.sl-sunlight-mode textarea {
            border-width: 3px !important;
            font-weight: 900 !important;
        }

        /* 🔍 CONSTITUTION 4 PROPOSAL 1: WAREHOUSE BIG-SCREEN DOCK DISPLAY MODE (وضع القراءة المكبّرة الفائقة عن بُعد لساحات التحميل) */
        body.sl-dock-bigscreen-mode {
            --sl-grid-font-size: 1.15rem !important;
        }
        body.sl-dock-bigscreen-mode .sl-qty-primary-input,
        body.sl-dock-bigscreen-mode .sl-qty-base-input,
        body.sl-dock-bigscreen-mode #sl-truck-plate,
        body.sl-dock-bigscreen-mode #sl-driver-name,
        body.sl-dock-bigscreen-mode #sl-gate-pass-no,
        body.sl-dock-bigscreen-mode #sl-lbl-inv-no,
        body.sl-dock-bigscreen-mode #sl-lbl-party-name {
            font-size: 1.25rem !important;
            padding: 8px 12px !important;
            border-width: 2.8px !important;
        }
        body.sl-dock-bigscreen-mode .sl-kpi-val {
            font-size: 1.7rem !important;
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           CONSTITUTION 1 (1 OF 14): IDEMPOTENT +2 BORDERS (2.2PX), STRICT DARK (#FFFFFF) /
           LIGHT (#000000) CONTRAST LOCK, ZERO INPUT COLOR INVERSION, 6:8 GEOMETRIC ZOOM,
           AND ZERO-TRUNCATION AUTO-FIT ACROSS MAIN SCREEN & ALL 16+ NESTED CHILD MODALS
           ═══════════════════════════════════════════════════════════════════════════ */
        #sl-proportional-root {
            transform-origin: top center !important;
            margin-left: auto !important;
            margin-right: auto !important;
            transition: transform 0.14s cubic-bezier(0.22, 1, 0.36, 1), width 0.14s ease;
        }

        /* Enforce +2 Border Thickness (2.2px Idempotent Lock) on all tables, boxes, and child modal containers */
        #sl-registry-scroll-wrap,
        #sl-workflow-stepper,
        #sl-wf-step-1, #sl-wf-step-2, #sl-wf-step-3,
        #sl-camera-placeholder,
        #sl-cols-drawer,
        .sl-unit-box,
        .sl-unit-box-inner,
        .sl-kpi-card,
        .sl-table thead th,
        .sl-table tbody td,
        [id^="sl-"][id$="-modal"] .sl-glass-card {
            border-width: 2.2px !important;
            border-style: solid !important;
        }
        #sl-camera-placeholder {
            border-style: dashed !important;
        }

        /* Zero-Truncation & Auto-Fit (Order 5): Ensure long names & large numbers never clip */
        .sl-table tbody td,
        #sl-lbl-party-name,
        #sl-lbl-inv-no,
        #sl-lbl-hash,
        #sl-sum-ordered,
        #sl-sum-prev,
        #sl-sum-now,
        #sl-sum-rem {
            overflow: visible !important;
            text-overflow: clip !important;
            word-break: break-word;
        }
        .sl-qty-primary-input,
        .sl-qty-base-input {
            min-width: 88px !important;
            width: auto !important;
            max-width: 155px !important;
            overflow: visible !important;
        }
        .sl-unit-box {
            width: auto !important;
            min-width: 242px !important;
        }
        .sl-unit-label-span {
            max-width: none !important;
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: nowrap !important;
        }

        /* 🌙 DARK THEME STRICT CONTRAST LAW: #FFFFFF Text/Borders + #000000 Input Box + Stepped Surrounding Surface */
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) h1,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) h2,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) h3,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) h4,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) label,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-table thead th,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) .sl-table tbody td,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) [id^="sl-"][id$="-modal"] h3,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) [id^="sl-"][id$="-modal"] label,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) [id^="sl-"][id$="-modal"] td,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) [id^="sl-"][id$="-modal"] th {
            color: #FFFFFF !important;
            font-weight: 900 !important;
        }
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) svg,
        html:not([data-magaies-theme-mode="light"]):not(.light-theme) body:not(.light-theme):not(.theme-light):not([data-theme="light"]):not([data-msg-theme="royal-dawn"]) svg * {
            stroke: #FFFFFF !important;
        }

        /* ☀️ LIGHT THEME STRICT CONTRAST LAW: Pitch-Black #000000 Text, Borders & Icons Everywhere */
        html[data-magaies-theme-mode="light"] .sl-unit-box-inner,
        body.theme-light .sl-unit-box-inner,
        body.light-theme .sl-unit-box-inner,
        body[data-theme="light"] .sl-unit-box-inner,
        body[data-msg-theme="royal-dawn"] .sl-unit-box-inner,
        html[data-magaies-theme-mode="light"] #sl-workflow-stepper,
        body.theme-light #sl-workflow-stepper,
        body.light-theme #sl-workflow-stepper,
        html[data-magaies-theme-mode="light"] #sl-camera-placeholder,
        body.theme-light #sl-camera-placeholder,
        body.light-theme #sl-camera-placeholder {
            background: #F1F5F9 !important;
            color: #000000 !important;
            border: 2.2px solid #000000 !important;
        }
        html[data-magaies-theme-mode="light"] .sl-glass-card *,
        body.theme-light .sl-glass-card *,
        body.light-theme .sl-glass-card *,
        body[data-theme="light"] .sl-glass-card *,
        body[data-msg-theme="royal-dawn"] .sl-glass-card *,
        html[data-magaies-theme-mode="light"] [id^="sl-"][id$="-modal"] *,
        body.theme-light [id^="sl-"][id$="-modal"] *,
        body.light-theme [id^="sl-"][id$="-modal"] * {
            color: #000000 !important;
            text-shadow: none !important;
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           CONSTITUTION 3 (3 OF 14): UNIFIED BUTTON DNA, MICRO-SCALE (1.03) & BOTTOM
           UNDER-GLOW, 0MS GPU GLASSMORPHISM SHEEN, STANDARDIZED TOASTS & SHAKE EFFECT
           ═══════════════════════════════════════════════════════════════════════════ */
        :root {
            --sl-c3-font: 'Tajawal', 'Cairo', sans-serif;
            --sl-c3-btn-min-h: 35px;
            --sl-c3-input-min-h: 35px;
        }
        body, button, input, select, textarea, .sl-btn, .sl-input {
            font-family: var(--sl-c3-font) !important;
        }
        .sl-btn {
            min-height: var(--sl-c3-btn-min-h);
            border-radius: 11px !important;
            transform-origin: center center !important;
            will-change: transform, box-shadow;
        }
        /* Bottom Under-Glow Bar on every button (Order 3) */
        .sl-btn::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 12%;
            right: 12%;
            height: 2.5px;
            border-radius: 99px;
            background: linear-gradient(90deg, transparent, var(--sl-primary-light), var(--sl-accent), transparent);
            opacity: 0;
            transform: scaleX(0.4);
            transition: opacity 0.2s ease, transform 0.22s ease;
            pointer-events: none;
        }
        .sl-btn:hover:not(:disabled) {
            transform: scale(1.03) !important;
            box-shadow: 0 8px 20px -4px var(--sl-primary-glow), 0 0 14px var(--sl-border-glow), inset 0 1px 0 rgba(255, 255, 255, 0.28) !important;
        }
        .sl-btn:hover:not(:disabled)::before {
            opacity: 1;
            transform: scaleX(1);
        }
        @keyframes slTactilePulseAnim {
            0% { transform: scale(1.03); }
            40% { transform: scale(0.96); }
            100% { transform: scale(1); }
        }
        .sl-btn.sl-btn-tactile-pulse {
            animation: slTactilePulseAnim 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important;
        }

        /* 0ms Pure GPU-Accelerated Glassmorphism Sheen on Cards & Section Headers (Order 4) */
        @keyframes slGpuGlassSheen {
            0%, 72% { transform: translate3d(-140%, 0, 0) skewX(-22deg); opacity: 0; }
            78% { opacity: 0.55; }
            100% { transform: translate3d(240%, 0, 0) skewX(-22deg); opacity: 0; }
        }
        .sl-glass-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 38%;
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.09) 50%, rgba(255,255,255,0) 100%);
            transform: translate3d(-140%, 0, 0) skewX(-22deg);
            will-change: transform, opacity;
            pointer-events: none;
            animation: slGpuGlassSheen 9.5s infinite ease-in-out;
            border-radius: inherit;
        }

        /* Mini Keyboard Shortcut Badge on Main Buttons (Order 2) */
        .sl-kbd-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1px 5px;
            font-size: 0.68rem;
            font-weight: 900;
            font-family: 'Inter', monospace !important;
            border-radius: 5px;
            background: rgba(2, 6, 23, 0.48);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #ffffff;
            line-height: 1.1;
        }

        /* Standardized Floating Toasts & Horizontal Field Shake Effect (Order 5) */
        #toast-container .toast {
            border-radius: 13px !important;
            padding: 10px 16px !important;
            font-weight: 900 !important;
            font-size: 0.88rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 9px !important;
            backdrop-filter: blur(14px) !important;
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.65) !important;
        }
        #toast-container .toast.success {
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.96), rgba(4, 120, 87, 0.94)) !important;
            border: 2.2px solid #34d399 !important;
            color: #FFFFFF !important;
            box-shadow: 0 10px 26px rgba(16, 185, 129, 0.45) !important;
        }
        #toast-container .toast.warning {
            background: linear-gradient(135deg, rgba(120, 53, 15, 0.96), rgba(180, 83, 9, 0.94)) !important;
            border: 2.2px solid #fbbf24 !important;
            color: #FFFFFF !important;
            box-shadow: 0 10px 26px rgba(245, 158, 11, 0.45) !important;
        }
        #toast-container .toast.error {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.96), rgba(185, 28, 28, 0.94)) !important;
            border: 2.2px solid #f87171 !important;
            color: #FFFFFF !important;
            box-shadow: 0 10px 26px rgba(239, 68, 68, 0.5) !important;
        }
        @keyframes slFieldShakeAnim {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-7px); }
            40%, 80% { transform: translateX(7px); }
        }
        .sl-field-shake-error {
            animation: slFieldShakeAnim 0.42s cubic-bezier(0.36, 0.07, 0.19, 0.97) both !important;
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.55), 0 0 18px rgba(239, 68, 68, 0.45) !important;
        }
    </style>
</head>
<body data-logistics-mode="<?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? 'purchases' : 'sales'; ?>">

    <div class="toast-container" id="toast-container"></div>
    <script defer src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div id="sl-proportional-root" style="padding: 6px 12px 55px 12px; max-width: 99.6vw; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 8px;">

        <!-- ═══════════════════════════════════════════════════════════════════
             1. TOP COCKPIT BAR: NAVIGATION + CLASSIC SYSTEM LINKS + MOBILE QR + ROYAL QUARTET INSIDE
             ═══════════════════════════════════════════════════════════════════ -->
        <div id="sl-top-cockpit-bar" class="sl-glass-card" style="background: var(--sl-header-grad); padding: 6px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <a href="dashboard-pro.php" class="sl-btn" style="text-decoration:none; color:#38bdf8; border-color:rgba(56,189,248,0.4); padding:6px 11px; font-size:0.8rem;">🏠 الرئيسية</a>
                <a id="sl-classic-sales-btn" href="sales-delivery.php" class="sl-btn" style="text-decoration:none; color:#6ee7b7; border-color:rgba(16,185,129,0.4); padding:6px 11px; font-size:0.8rem;" title="النظام الكلاسيكي لتسليم المبيعات">📤 تسليم المبيعات</a>
                <a id="sl-classic-purchases-btn" href="purchase-receipt.php" class="sl-btn" style="text-decoration:none; color:#7dd3fc; border-color:rgba(14,165,233,0.4); padding:6px 11px; font-size:0.8rem;" title="النظام الكلاسيكي لاستلام المشتريات">📥 استلام المشتريات</a>

                <div style="margin-right: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h1 style="margin: 0; font-size: 1.05rem; font-weight: 900; color: #ffffff; display: flex; align-items: center; gap: 6px; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
                        <span id="sl-main-title-icon"><?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? '📥' : '📤'; ?></span>
                        <span id="sl-main-title-text"><?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? 'بوابة استلام المشتريات (GRN)' : 'بوابة تسليم المبيعات (GDN)'; ?></span>
                    </h1>
                    <span id="sl-live-sync-status-pill" class="sl-badge sl-badge-done" style="font-size: 0.72rem; padding: 3px 10px;" title="مزامنة فورية في جزء من الثانية بين الموبايل واللابتوب وأوفلاين">⚡ متزامن لحظياً</span>
                </div>
            </div>

            <!-- MOBILE QR LASER BRIDGE + SMART LOCK GUARD + ROYAL QUARTET DOCKED HARMONIOUSLY INSIDE -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <!-- 🔍 إضافة 1 (الدستور 1): شريط التحجيم الهندسي المتناسب (6:8 Aspect-Ratio Zoom Bar) -->
                <div id="sl-c1-zoom-control-pill" style="display:inline-flex; align-items:center; gap:4px; background:rgba(2,6,23,0.78); padding:3px 7px; border-radius:10px; border:2.2px solid rgba(56,189,248,0.55);" title="تحجيم هندسي متناسب للأبعاد (6:8) للشاشة والشاشات الوليدة (Ctrl + رولة الماوس)">
                    <button type="button" onclick="window.stepSlProportionalZoom && window.stepSlProportionalZoom(-0.05)" class="sl-btn" style="padding:2px 7px; font-size:0.78rem; min-width:26px;" title="تصغير متناسب (6:8)">−</button>
                    <span id="sl-c1-zoom-pct-badge" onclick="window.setSlProportionalZoom && window.setSlProportionalZoom(1)" style="font-size:0.75rem; font-weight:900; color:#38bdf8; cursor:pointer; padding:0 4px; font-family:'Inter',monospace;" title="اضغط لإعادة الضبط 100%">100%</span>
                    <button type="button" onclick="window.stepSlProportionalZoom && window.stepSlProportionalZoom(0.05)" class="sl-btn" style="padding:2px 7px; font-size:0.78rem; min-width:26px;" title="تكبير متناسب (6:8)">+</button>
                </div>

                <!-- 🌓 إضافة 2 (الدستور 1): مبدل السمات اللحظي وسريانها على الشاشات الوليدة -->
                <button type="button" id="sl-btn-c1-theme-cycler" onclick="window.cycleSlConstitution1Theme && window.cycleSlConstitution1Theme()" class="sl-btn" style="background:rgba(14,165,233,0.2); border-color:#38bdf8; color:#7dd3fc; padding:6px 10px; font-size:0.78rem;" title="تبديل فوري بين السمات الغامقة والفاتحة مع سريان 100% على كل الشاشات الوليدة">
                    🌓 تبديل السمة
                </button>

                <!-- 🛡️ إضافة 3 (الدستور 1): لوحة فحص وإثبات الدستور الأول (1 من 14) -->
                <button type="button" id="sl-btn-c1-master-audit" onclick="window.openSlConstitution1MasterModal && window.openSlConstitution1MasterModal()" class="sl-btn" style="background:rgba(16,185,129,0.22); border-color:#34d399; color:#6ee7b7; padding:6px 10px; font-size:0.78rem;" title="لوحة فحص وإثبات الدستور الأول (السمات، الشاشات الوليدة، سُمك +2، منع انقلاب اللون، وتناسب 6:8)">
                    🛡️ الدستور 1 (100%)
                </button>

                <div style="display:flex; align-items:center; gap:5px; background:rgba(2,6,23,0.75); padding:4px 9px; border-radius:10px; border:2.2px solid rgba(148,163,184,0.3);">
                    <span style="font-size:0.74rem; font-weight:900; color:#cbd5e1;">🛡️ QR:</span>
                    <select id="sl-qr-guard-mode" class="sl-input" style="padding:3px 8px !important; font-size:0.74rem !important; height:auto;">
                        <option value="auto">🤖 تلقائي</option>
                        <option value="lock">🔒 قفل القسم</option>
                    </select>
                </div>

                <button type="button" onclick="openSlMobileBridgeModal()" class="sl-btn" style="background: linear-gradient(135deg, #059669, #0284c7); border-color: #34d399; color: #ffffff; padding: 6px 12px; font-size: 0.8rem; box-shadow: 0 0 18px rgba(16,185,129,0.35);">
                    <span id="sl-bridge-live-dot" class="sl-pulse-dot" style="background:#fcd34d;"></span>
                    📱 كاميرا الجوال
                </button>

                <button type="button" id="sl-btn-sunlight-mode" onclick="toggleSlSunlightMode()" class="sl-btn" style="background: rgba(245,158,11,0.22); border-color: #fbbf24; color: #fde047; padding: 6px 11px; font-size: 0.8rem;" title="تفعيل وضع السطوع والحدود العريضة 3px للساحات الخارجية تحت أشعة الشمس">
                    ☀️ وضع الشمس
                </button>

                <button type="button" onclick="openSlFontModal()" class="sl-btn" style="background: rgba(168,85,247,0.18); border-color: rgba(192,132,252,0.5); color: #e9d5ff; padding: 6px 11px; font-size: 0.8rem;">
                    🎨 الخط والألوان
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
            <div id="sl-tab-sales" class="sl-mode-tab mode-sales <?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? '' : 'active'; ?>" onclick="switchLogisticsMode('sales')">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(16,185,129,0.22); border:1.5px solid #34d399; display:flex; align-items:center; justify-content:center; font-size:1.25rem; box-shadow:0 0 14px rgba(16,185,129,0.35);">
                        📤
                    </div>
                    <div style="font-size:0.96rem; font-weight:900; color:#ffffff; display:flex; align-items:center; gap:6px;">
                        بوابة تسليم المبيعات (GDN)
                        <span class="sl-badge sl-badge-done" style="font-size:0.68rem; padding:2px 8px;">صادر</span>
                    </div>
                </div>
                <div style="text-align:left;">
                    <div id="sl-tab-sales-badge" style="font-size:1.2rem; font-weight:900; color:#34d399; font-family:'Inter',sans-serif; line-height:1.1;">0</div>
                </div>
            </div>

            <!-- TAB 2: PURCHASE RECEIPT (GRN - INCOMING) -->
            <div id="sl-tab-purchases" class="sl-mode-tab mode-purchases <?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? 'active' : ''; ?>" onclick="switchLogisticsMode('purchases')">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(14,165,233,0.22); border:1.5px solid #38bdf8; display:flex; align-items:center; justify-content:center; font-size:1.25rem; box-shadow:0 0 14px rgba(14,165,233,0.35);">
                        📥
                    </div>
                    <div style="font-size:0.96rem; font-weight:900; color:#ffffff; display:flex; align-items:center; gap:6px;">
                        بوابة استلام المشتريات (GRN)
                        <span class="sl-badge sl-badge-partial" style="font-size:0.68rem; padding:2px 8px;">وارد</span>
                    </div>
                </div>
                <div style="text-align:left;">
                    <div id="sl-tab-purchases-badge" style="font-size:1.2rem; font-weight:900; color:#38bdf8; font-family:'Inter',sans-serif; line-height:1.1;">0</div>
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
                    <div id="sl-kpi-lbl-pending" style="font-size: 0.78rem; font-weight: 800; color: #fcd34d;">⏳ قيد التنفيذ</div>
                    <div id="sl-kpi-val-pending" style="font-size: 1.38rem; font-weight: 900; color: #fbbf24; margin-top: 1px; line-height: 1.1; font-family:'Inter',sans-serif;">0</div>
                </div>
                <span style="font-size: 1.6rem;">📦</span>
            </div>

            <div id="sl-kpi-card-partial" class="sl-glass-card sl-kpi-card" onclick="setSlRegistryFilter('partial')" style="border-right: 4px solid #38bdf8;">
                <div>
                    <div id="sl-kpi-lbl-partial" style="font-size: 0.78rem; font-weight: 800; color: #7dd3fc;">🌗 منفذة جزئياً</div>
                    <div id="sl-kpi-val-partial" style="font-size: 1.38rem; font-weight: 900; color: #38bdf8; margin-top: 1px; line-height: 1.1; font-family:'Inter',sans-serif;">0</div>
                </div>
                <span style="font-size: 1.6rem;">✂️</span>
            </div>

            <div id="sl-kpi-card-done" class="sl-glass-card sl-kpi-card" onclick="setSlRegistryFilter('done')" style="border-right: 4px solid #10b981;">
                <div>
                    <div id="sl-kpi-lbl-done" style="font-size: 0.78rem; font-weight: 800; color: #6ee7b7;">✅ مكتملة</div>
                    <div id="sl-kpi-val-done" style="font-size: 1.38rem; font-weight: 900; color: #34d399; margin-top: 1px; line-height: 1.1; font-family:'Inter',sans-serif;">0</div>
                </div>
                <span style="font-size: 1.6rem;">📜</span>
            </div>

            <div id="sl-kpi-card-notes" class="sl-glass-card sl-kpi-card" onclick="setSlRegistryFilter('notes')" style="border-right: 4px solid #c084fc;">
                <div>
                    <div id="sl-kpi-lbl-notes" style="font-size: 0.78rem; font-weight: 800; color: #e9d5ff;">📊 سجل الأذونات</div>
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
                        سجل الفواتير والأذونات
                    </h3>
                    <span id="sl-reg-count-badge" class="sl-badge sl-badge-partial">0 مستند</span>
                </div>

                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <input type="text" id="sl-reg-search" class="sl-input" placeholder="🔍 بحث برقم الفاتورة، الإذن، الاسم، أو الصنف..." oninput="renderSlRegistry()" onkeydown="if(event.key==='Enter'){event.preventDefault(); quickLoadFirstMatch(this.value);}" style="min-width: 210px; font-size: 0.8rem; padding: 6px 12px !important;">

                    <input type="date" id="sl-reg-date-from" class="sl-input" onchange="renderSlRegistry()" title="من تاريخ" style="font-size: 0.76rem; padding: 5px 8px !important; width: 132px;">
                    <input type="date" id="sl-reg-date-to" class="sl-input" onchange="renderSlRegistry()" title="إلى تاريخ" style="font-size: 0.76rem; padding: 5px 8px !important; width: 132px;">

                    <select id="sl-reg-wh-filter" class="sl-input" onchange="renderSlRegistry()" style="font-size: 0.78rem; padding: 6px 10px !important;">
                        <option value="all">🏢 كل المخازن</option>
                    </select>

                    <select id="sl-reg-limit" class="sl-input" onchange="renderSlRegistry()" style="font-size: 0.78rem; padding: 6px 10px !important;">
                        <option value="6" selected>⚡ 6 مستندات</option>
                        <option value="15">⚡ 15 مستند</option>
                        <option value="50">⚡ 50 مستند</option>
                        <option value="100">⚡ 100 مستند</option>
                        <option value="250">⚡ 250 مستند</option>
                        <option value="99999">📋 الكل</option>
                    </select>

                    <button type="button" id="sl-filter-btn-pending" onclick="setSlRegistryFilter('pending')" class="sl-btn" style="background: var(--sl-primary); color: #fff; border-color: var(--sl-primary-light); padding: 6px 11px; font-size: 0.78rem;">⏳ قيد الإجراء</button>
                    <button type="button" id="sl-filter-btn-partial" onclick="setSlRegistryFilter('partial')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">🌗 الجزئي</button>
                    <button type="button" id="sl-filter-btn-done" onclick="setSlRegistryFilter('done')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">✅ المكتمل</button>
                    <button type="button" id="sl-filter-btn-notes" onclick="setSlRegistryFilter('notes')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">🏷️ الأذونات</button>
                    <button type="button" id="sl-filter-btn-all" onclick="setSlRegistryFilter('all')" class="sl-btn" style="padding: 6px 11px; font-size: 0.78rem;">📋 الكل</button>
                    <button type="button" id="sl-btn-export-excel" onclick="exportSlRegistryToExcel()" class="sl-btn" style="background:rgba(16,185,129,0.2); color:#6ee7b7; border-color:rgba(16,185,129,0.5); padding: 6px 11px; font-size: 0.78rem;">📊 إكسل</button>
                    <button type="button" id="sl-btn-export-pdf" onclick="exportSlRegistryToPdf()" class="sl-btn" style="background:rgba(239,68,68,0.2); color:#fca5a5; border-color:rgba(239,68,68,0.5); padding: 6px 11px; font-size: 0.78rem;">📄 PDF</button>
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
                            <th style="text-align: right; width: 32%;">الأصناف والوحدات</th>
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

            <!-- 👑 المخطوطة الثلاثية للخطوات الحية (Visual Workflow Stepper) + بطاقة الفحص والتقسيم -->
            <div class="sl-glass-card" style="padding: 12px 16px; display: flex; flex-direction: column; gap: 10px;">
                <!-- LIVE 3-STAGE WORKFLOW STEPPER -->
                <div id="sl-workflow-stepper" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; background:rgba(2,6,23,0.78); padding:7px 10px; border-radius:12px; border:1px solid rgba(56,189,248,0.28);">
                    <div id="sl-wf-step-1" onclick="document.getElementById('sl-reg-search')?.focus()" style="display:flex; align-items:center; gap:8px; padding:5px 10px; border-radius:9px; background:rgba(56,189,248,0.16); border:1.5px solid #38bdf8; cursor:pointer; transition:all 0.25s ease;">
                        <span style="width:24px; height:24px; border-radius:50%; background:#38bdf8; color:#020617; font-weight:900; font-size:0.8rem; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">1</span>
                        <div style="font-size:0.78rem; font-weight:900; color:#ffffff;">اختيار المستند</div>
                    </div>
                    <div id="sl-wf-step-2" onclick="const inp=document.querySelector('.sl-qty-primary-input'); if(inp){inp.scrollIntoView({behavior:'smooth',block:'center'}); inp.focus();}" style="display:flex; align-items:center; gap:8px; padding:5px 10px; border-radius:9px; background:rgba(15,23,42,0.65); border:1.5px solid rgba(148,163,184,0.22); opacity:0.65; cursor:pointer; transition:all 0.25s ease;">
                        <span style="width:24px; height:24px; border-radius:50%; background:rgba(148,163,184,0.25); color:#e2e8f0; font-weight:900; font-size:0.8rem; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">2</span>
                        <div style="font-size:0.78rem; font-weight:900; color:#ffffff;">فحص وتقسيم الكميات</div>
                    </div>
                    <div id="sl-wf-step-3" onclick="document.getElementById('sl-btn-execute')?.scrollIntoView({behavior:'smooth',block:'center'})" style="display:flex; align-items:center; gap:8px; padding:5px 10px; border-radius:9px; background:rgba(15,23,42,0.65); border:1.5px solid rgba(148,163,184,0.22); opacity:0.65; cursor:pointer; transition:all 0.25s ease;">
                        <span style="width:24px; height:24px; border-radius:50%; background:rgba(148,163,184,0.25); color:#e2e8f0; font-weight:900; font-size:0.8rem; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">3</span>
                        <div style="font-size:0.78rem; font-weight:900; color:#ffffff;">الاعتماد والطباعة</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 1.25rem;">📄</span>
                        <h3 id="sl-doc-card-title" style="margin: 0; font-size: 0.98rem; font-weight: 900; color: #ffffff;">
                            بيانات المستند والتوريد
                        </h3>
                        <span id="sl-c2-speed-badge" class="sl-badge sl-badge-done" style="font-size:0.7rem; padding:2px 8px;" title="زمن استجابة الواجهة اللحظي 0ms — يعمل بأقصى خفة على أضعف إنترنت 2G وأوفلاين">⚡ 0ms | 2G</span>
                        <span id="sl-c2-draft-badge" class="sl-badge sl-badge-partial" style="display:none; font-size:0.7rem; padding:2px 8px;" title="حارس المسودة التلقائي ضد الإغلاق الخاطئ">💾 مسودة محفوظة</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <button type="button" id="sl-btn-quick-add-party" onclick="window.openSlQuickEntityModal && window.openSlQuickEntityModal('party')" class="sl-btn" style="padding: 4px 10px; font-size: 0.76rem; background: rgba(16,185,129,0.22); border-color: #34d399; color: #6ee7b7;" title="إضافة مورد أو عميل جديد فوراً دون مغادرة الشاشة">
                            ➕ طرف جديد
                        </button>
                        <button type="button" id="sl-btn-quick-add-item-top" onclick="window.openSlQuickEntityModal && window.openSlQuickEntityModal('item')" class="sl-btn" style="padding: 4px 10px; font-size: 0.76rem; background: rgba(168,85,247,0.22); border-color: #c084fc; color: #e9d5ff;" title="إضافة صنف جديد فوراً للمستند والمخزون دون ضياع البيانات">
                            ➕ صنف سريع
                        </button>
                        <button type="button" id="sl-btn-drilldown-inv" onclick="openSlOriginalInvoiceDrilldownModal()" class="sl-btn" style="padding: 4px 11px; font-size: 0.76rem; background: rgba(56,189,248,0.18); border-color: #38bdf8; color: #7dd3fc;">
                            🔍 أصل الفاتورة
                        </button>
                        <div id="sl-doc-status-pill" class="sl-badge sl-badge-pending">جاهز</div>
                    </div>
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
                        <div id="sl-lbl-party-balance" style="font-size: 0.73rem; font-weight: 800; color: #6ee7b7; margin-top: 2px;">الرصيد المالي: ---</div>
                        <div id="sl-lbl-party-sla" style="font-size: 0.7rem; font-weight: 800; color: #38bdf8; margin-top: 2px;">⭐ كفاءة التوريد والبوابة: ---</div>
                    </div>
                    <div style="background: rgba(2,6,23,0.65); padding: 7px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.07);">
                        <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 800; display: block;">📅 تاريخ المستند</label>
                        <div id="sl-lbl-date" style="font-size: 0.9rem; font-weight: 800; color: #e2e8f0; margin-top: 1px;">---</div>
                    </div>
                    <div style="background: rgba(2,6,23,0.65); padding: 7px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.07);">
                        <label style="font-size: 0.72rem; color: #94a3b8; font-weight: 800; display: block;">🔐 البصمة الأمنية والرقابية</label>
                        <div id="sl-lbl-hash" style="font-size: 0.8rem; font-weight: 800; color: #38bdf8; font-family: 'Inter', monospace; margin-top: 2px;">---</div>
                        <div id="sl-lbl-audit-stamp" style="font-size: 0.7rem; font-weight: 800; color: #fcd34d; margin-top: 2px;">👤 المستخدم: المسؤول | 💻 محلي</div>
                    </div>
                </div>

                <!-- LOGISTICS INPUTS (WAREHOUSE, RECEIVER, DRIVER, PLATE, GATE PASS) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(155px, 1fr)); gap: 8px;">
                    <div>
                        <label id="sl-lbl-wh-select" style="font-size: 0.74rem; font-weight: 800; color: #fcd34d; display: block; margin-bottom: 3px;">🏢 المستودع الافتراضي (للكل):</label>
                        <div style="display:flex; gap:5px; align-items:center;">
                            <select id="sl-master-warehouse" class="sl-input" style="flex:1; padding: 6px 10px !important; font-size: 0.82rem !important;" onchange="onSlMasterWarehouseChange(this.value)"></select>
                            <button type="button" id="sl-btn-quick-add-wh" onclick="openSlQuickWarehouseModal()" class="sl-btn" style="padding: 6px 10px; font-size: 0.82rem; background: rgba(16,185,129,0.22); border-color: #34d399; color: #6ee7b7;" title="إضافة مستودع جديد فوراً">+</button>
                        </div>
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
                    <div>
                        <label style="font-size: 0.74rem; font-weight: 800; color: #34d399; display: flex; align-items:center; justify-content:space-between; margin-bottom: 3px;">
                            <span>⏱️ ختم وقت البوابة:</span>
                            <span id="sl-gate-duration-badge" style="font-size:0.68rem; color:#fde047;">0 د</span>
                        </label>
                        <div style="display:flex; gap:4px; align-items:center;">
                            <input type="text" id="sl-gate-time-in" class="sl-input" placeholder="دخول..." title="وقت دخول الشاحنة للبوابة" style="flex:1; padding: 6px 6px !important; font-size: 0.76rem !important; text-align:center;">
                            <input type="text" id="sl-gate-time-out" class="sl-input" placeholder="خروج..." title="وقت خروج الشاحنة من البوابة" style="flex:1; padding: 6px 6px !important; font-size: 0.76rem !important; text-align:center;">
                            <button type="button" id="sl-btn-gate-stamp" onclick="stampSlGateTimeNow()" class="sl-btn" style="padding: 6px 8px; font-size: 0.78rem; background: rgba(56,189,248,0.2); border-color: #38bdf8; color: #7dd3fc;" title="ختم وقت الدخول/الخروج اللحظي">⏱️</button>
                        </div>
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
                    <h3 style="margin: 0; font-size: 0.96rem; font-weight: 900; color: #ffffff;">📷 المسح الضوئي (QR)</h3>
                    <span id="sl-verify-badge" class="sl-badge sl-badge-done">👑 جاهز</span>
                </div>

                <label style="display:flex; align-items:center; gap:8px; background:var(--sl-primary-soft); border:1px solid var(--sl-border-glow); padding:8px 12px; border-radius:10px; font-size:0.78rem; font-weight:900; color:#fff; cursor:pointer;">
                    <input type="checkbox" id="sl-auto-execute-qr" checked style="width:16px; height:16px; accent-color:var(--sl-primary);">
                    <span>⚡ اعتماد فوري عند المسح</span>
                </label>

                <div id="sl-qr-reader-wrap" style="display:none; flex-direction:column; gap:8px;">
                    <div id="sl-qr-reader" style="width:100%; border-radius:12px; overflow:hidden; border:2px solid var(--sl-primary-light); aspect-ratio:4/3; background:#000;"></div>
                    <button type="button" onclick="stopSlLocalCamera()" class="sl-btn" style="background:rgba(239,68,68,0.2); border-color:#ef4444; color:#fca5a5;">⏹️ إيقاف الكاميرا</button>
                </div>

                <div id="sl-camera-placeholder" style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; background:rgba(2,6,23,0.55); border:1.5px dashed rgba(56,189,248,0.35); border-radius:14px; padding:14px; text-align:center;">
                    <div style="font-size:2.1rem; margin-bottom:8px;">📱⚡</div>
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
                        📎 إرفاق صورة
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
                    <h3 id="sl-grid-title" style="margin: 0; font-size: 1.05rem; font-weight: 900; color: #ffffff;">
                        جدول الفحص والتقسيم بالوحدتين والمخازن
                    </h3>
                </div>

                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <button type="button" id="sl-btn-quick-add-item" onclick="window.openSlQuickEntityModal && window.openSlQuickEntityModal('item')" class="sl-btn" style="background: rgba(16,185,129,0.24); border-color: #34d399; color: #6ee7b7; padding: 6px 11px; font-size: 0.78rem;" title="إضافة صنف جديد مباشرة لجدول الفحص والمخزون دون ضياع البيانات">
                        ➕ صنف
                    </button>
                    <button type="button" onclick="fillAllSlQuantities(1)" class="sl-btn" style="background: rgba(16,185,129,0.2); border-color: #34d399; color: #6ee7b7; padding: 6px 11px; font-size: 0.78rem;">
                        ⚡ الكل (100%)
                    </button>
                    <button type="button" onclick="fillAllSlQuantities(0.5)" class="sl-btn" style="background: rgba(56,189,248,0.18); border-color: #38bdf8; color: #7dd3fc; padding: 6px 11px; font-size: 0.78rem;">
                        🌗 النصف (50%)
                    </button>
                    <button type="button" onclick="fillAllSlQuantities(0)" class="sl-btn" style="background: rgba(239,68,68,0.16); border-color: #f87171; color: #fca5a5; padding: 6px 11px; font-size: 0.78rem;">
                        🧹 تصفير
                    </button>
                    <button type="button" id="sl-btn-auto-allocate" onclick="autoAllocateSlAcrossWarehouses()" class="sl-btn" style="background: rgba(14,165,233,0.22); border-color: #38bdf8; color: #7dd3fc; padding: 6px 11px; font-size: 0.78rem;">
                        🏭 توزيع المخازن
                    </button>
                    <button type="button" onclick="openSlWeighbridgeModal()" class="sl-btn" style="background: rgba(245,158,11,0.2); border-color: #fbbf24; color: #fde047; padding: 6px 11px; font-size: 0.78rem;">
                        ⚖️ الميزان
                    </button>
                    <button type="button" id="sl-btn-dispatch-presets" onclick="window.openSlDispatchPresetsModal && window.openSlDispatchPresetsModal()" class="sl-btn" style="background: rgba(168,85,247,0.22); border-color: #c084fc; color: #e9d5ff; padding: 6px 11px; font-size: 0.78rem;" title="الحافظة الذكية لقوالب السائقين والمركبات والمستودعات المتكررة بضغطة واحدة">
                        ⚡ قوالب السائقين
                    </button>
                    <button type="button" onclick="const t=document.getElementById('sl-extra-tools-drawer'); t.style.display=(t.style.display==='none'?'flex':'none');" class="sl-btn" style="background: rgba(56,189,248,0.18); border-color: #38bdf8; color: #7dd3fc; padding: 6px 11px; font-size: 0.78rem;" title="إظهار أو إخفاء الأدوات المتقدمة للبوابة في نظرة واحدة">
                        🧰 أدوات البوابة
                    </button>
                    <button type="button" onclick="const d=document.getElementById('sl-cols-drawer'); d.style.display=(d.style.display==='none'?'flex':'none');" class="sl-btn" style="background: rgba(168,85,247,0.18); border-color: #c084fc; color: #e9d5ff; padding: 6px 11px; font-size: 0.78rem;">
                        ⚙️ الأعمدة
                    </button>
                </div>
            </div>

            <!-- COLLAPSIBLE ADVANCED GATE TOOLS DRAWER (نظرة واحدة صافية بدون حشو — Zero-Scroll) -->
            <div id="sl-extra-tools-drawer" style="display:none; align-items:center; gap:6px; flex-wrap:wrap; background:rgba(2,6,23,0.82); padding:8px 12px; border-radius:12px; border:2.2px solid rgba(56,189,248,0.4); margin-bottom:10px;">
                <button type="button" id="sl-btn-voice-dictate" onclick="startSlVoiceDictation()" class="sl-btn" style="background: rgba(236,72,153,0.2); border-color: #f472b6; color: #fbcfe8; padding:5px 10px; font-size:0.76rem;">🎙️ إملاء</button>
                <button type="button" id="sl-btn-voice-commander" onclick="window.openSlVoiceGateCommanderModal && window.openSlVoiceGateCommanderModal()" class="sl-btn" style="background: rgba(14,165,233,0.22); border-color: #38bdf8; color: #7dd3fc; padding:5px 10px; font-size:0.76rem;">🎤 أوامر صوتية</button>
                <button type="button" id="sl-btn-pa-announcer" onclick="window.announceSlDriverLoadingPA && window.announceSlDriverLoadingPA()" class="sl-btn" style="background: rgba(245,158,11,0.22); border-color: #fbbf24; color: #fde047; padding:5px 10px; font-size:0.76rem;">📢 نداء السائق</button>
                <button type="button" id="sl-btn-batch-ocr" onclick="openSlBatchOcrScannerModal()" class="sl-btn" style="background: rgba(16,185,129,0.2); border-color: #34d399; color: #6ee7b7; padding:5px 10px; font-size:0.76rem;">📷 التشغيلة</button>
                <button type="button" id="sl-btn-bin-heatmap" onclick="openSlBinHeatmapModal()" class="sl-btn" style="background: rgba(99,102,241,0.22); border-color: #818cf8; color: #c7d2fe; padding:5px 10px; font-size:0.76rem;">🗺️ الأرفف</button>
                <button type="button" id="sl-btn-fleet-dispatch" onclick="window.openSlFleetDispatchModal && window.openSlFleetDispatchModal()" class="sl-btn" style="background: rgba(16,185,129,0.22); border-color: #34d399; color: #6ee7b7; padding:5px 10px; font-size:0.76rem;">🚚 المسار</button>
                <button type="button" id="sl-btn-load-verifier" onclick="window.openSlLoadingVerifierModal && window.openSlLoadingVerifierModal()" class="sl-btn" style="background: rgba(56,189,248,0.22); border-color: #38bdf8; color: #7dd3fc; padding:5px 10px; font-size:0.76rem;">🎯 مطابقة التحميل</button>
                <button type="button" id="sl-btn-cargo-estimator" onclick="window.openSlCargoEstimatorModal && window.openSlCargoEstimatorModal()" class="sl-btn" style="background: rgba(245,158,11,0.22); border-color: #fbbf24; color: #fde047; padding:5px 10px; font-size:0.76rem;">📦 الحمولة</button>
                <button type="button" id="sl-btn-pending-aging" onclick="window.openSlPendingDeliveryAgingModal && window.openSlPendingDeliveryAgingModal()" class="sl-btn" style="background: rgba(14,165,233,0.22); border-color: #38bdf8; color: #7dd3fc; padding:5px 10px; font-size:0.76rem;">📊 المتبقيات</button>
                <button type="button" id="sl-btn-gate-credit-note" onclick="window.openSlGateCreditNoteModal && window.openSlGateCreditNoteModal()" class="sl-btn" style="background: rgba(236,72,153,0.22); border-color: #f472b6; color: #fbcfe8; padding:5px 10px; font-size:0.76rem;">↩️ إشعار دائن</button>
                <button type="button" id="sl-btn-gate-certificate" onclick="window.printSlGateClearanceCertificate && window.printSlGateClearanceCertificate()" class="sl-btn" style="background: rgba(16,185,129,0.22); border-color: #34d399; color: #6ee7b7; padding:5px 10px; font-size:0.76rem;">🛡️ شهادة إخلاء</button>
                <button type="button" id="sl-btn-dock-zoom" onclick="window.toggleSlDockBigScreenMode && window.toggleSlDockBigScreenMode()" class="sl-btn" style="background: rgba(245,158,11,0.22); border-color: #fbbf24; color: #fde047; padding:5px 10px; font-size:0.76rem;">🔍 شاشة الرصيف</button>
                <button type="button" id="sl-btn-contrast-inspector" onclick="window.openSlContrastInspectorModal && window.openSlContrastInspectorModal()" class="sl-btn" style="background: rgba(56,189,248,0.22); border-color: #38bdf8; color: #7dd3fc; padding:5px 10px; font-size:0.76rem;">🛡️ فحص التباين</button>
                <button type="button" id="sl-btn-gdn-quiz" onclick="window.openSlGdnMasteryQuizModal && window.openSlGdnMasteryQuizModal()" class="sl-btn" style="background: rgba(168,85,247,0.22); border-color: #c084fc; color: #e9d5ff; padding:5px 10px; font-size:0.76rem;">🎓 اختبار</button>
            </div>

            <!-- COLUMN VISIBILITY DRAWER -->
            <div id="sl-cols-drawer" style="display:none; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; background:rgba(2,6,23,0.85); padding:10px 16px; border-radius:12px; border:1.5px solid rgba(168,85,247,0.45); margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; font-size:0.8rem; font-weight:800;">
                    <span style="color:#e9d5ff;">👁️ الأعمدة:</span>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-wh" checked onchange="applySlColVisibility()"> 🏢 المستودع والرصيد</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-bin" checked onchange="applySlColVisibility()"> 📍 الرف والتشغيلة</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-spec" checked onchange="applySlColVisibility()"> ⚖️ المعامل</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-prev" checked onchange="applySlColVisibility()"> 📊 المنفذ والمتبقي</label>
                    <label style="cursor:pointer;"><input type="checkbox" id="sl-col-chk-price" onchange="applySlColVisibility()"> 💰 السعر</label>
                </div>
                <button type="button" onclick="document.getElementById('sl-cols-drawer').style.display='none'" class="sl-btn" style="padding:4px 12px; font-size:0.78rem; background:#10b981; color:#fff;">💾 إغلاق</button>
            </div>

            <div style="overflow-x: auto; border-radius: 14px; border: 1.5px solid rgba(148,163,184,0.22);">
                <table class="sl-table" id="sl-items-grid" style="min-width: 1360px;">
                    <thead>
                        <tr>
                            <th style="width: 44px; text-align: center;">#</th>
                            <th class="sl-col-wh" style="width: 190px; text-align: right;">🏢 المستودع والرصيد</th>
                            <th style="min-width: 200px; text-align: right;">📦 الصنف والكود</th>
                            <th class="sl-col-bin" style="width: 155px; text-align: center;">📍 الرف / التشغيلة</th>
                            <th class="sl-col-spec" style="width: 155px; text-align: center;">⚖️ المعامل</th>
                            <th style="width: 115px; text-align: center;">📊 كمية الفاتورة</th>
                            <th class="sl-col-prev" style="width: 145px; text-align: center;">🔄 المنفذ / المتبقي</th>
                            <th class="sl-col-price" style="width: 105px; text-align: center; display:none;">💰 السعر</th>
                            <th id="sl-th-dual-action" style="min-width: 495px; text-align: center; color: var(--sl-primary-light);">
                                ⚡ الكمية المنفذة الآن (أساسية ⇄ فرعية)
                            </th>
                            <th style="width: 110px; text-align: center;">🚦 الحالة</th>
                        </tr>
                    </thead>
                    <tbody id="sl-items-tbody">
                        <tr>
                            <td colspan="10" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 800; font-size: 1rem;">
                                👆 اختر مستنداً من السجل أو امسح رمز QR
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
                    <span id="sl-summary-mode-badge" style="font-weight:900; font-size:0.92rem; color:var(--sl-primary-light);"><?php echo (isset($_GET['mode']) && $_GET['mode'] === 'purchases') ? '📥 استلام المشتريات (GRN)' : '📤 تسليم المبيعات (GDN)'; ?></span>
                </div>
                <div style="height:24px; width:1px; background:rgba(148,163,184,0.25);"></div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">
                    الأصناف: <strong id="sl-sum-items-count" style="color:#fff; font-size:0.98rem;">0</strong>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">
                    الفاتورة: <strong id="sl-sum-ordered" style="color:#38bdf8; font-size:0.98rem;">0</strong>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">
                    منفذ سابقاً: <strong id="sl-sum-prev" style="color:#a855f7; font-size:0.98rem;">0</strong>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1; background:var(--sl-primary-soft); padding:4px 12px; border-radius:10px; border:1px solid var(--sl-border-glow);">
                    الدفعة الحالية: <strong id="sl-sum-now" style="color:var(--sl-primary-light); font-size:1.05rem;">0</strong>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:#cbd5e1;">
                    المتبقي: <strong id="sl-sum-rem" style="color:#fbbf24; font-size:0.98rem;">0</strong>
                </div>
                <div style="display:flex; align-items:center; gap:8px; background:rgba(15,23,42,0.85); padding:4px 10px; border-radius:10px; border:1.5px solid rgba(56,189,248,0.4); min-width:135px;">
                    <span style="font-size:0.76rem; font-weight:900; color:#7dd3fc;">الإنجاز:</span>
                    <div style="flex:1; height:8px; background:rgba(148,163,184,0.25); border-radius:99px; overflow:hidden; min-width:48px;">
                        <div id="sl-sum-progress-bar" style="width:0%; height:100%; background:linear-gradient(90deg,#0ea5e9,#10b981); border-radius:99px; transition:width 0.3s ease;"></div>
                    </div>
                    <strong id="sl-sum-pct-badge" style="font-size:0.82rem; font-weight:900; color:#34d399; font-family:'Inter',sans-serif;">0%</strong>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <!-- 🔴 زر الحذف/الإلغاء معزول مكانياً في الطرف البعيد وبلون أحمر تحذيري موحد مع تأكيد مدمج سريع (Order 2) -->
                <button type="button" id="sl-btn-undo" onclick="window.requestSlSafeIsolatedUndo ? window.requestSlSafeIsolatedUndo(this) : undoSlTransaction()" class="sl-btn" style="background: rgba(239,68,68,0.22); border-color: #ef4444; color: #fca5a5; margin-inline-end: 8px;" title="إلغاء وعكس الحركة المخزنية (معزول مكانياً لمنع الضغط بالخطأ)">
                    🗑️ حذف / عكس <kbd class="sl-kbd-badge">Del</kbd>
                </button>
                <div style="height:24px; width:2px; background:rgba(239,68,68,0.35); margin-inline-end: 4px;"></div>

                <button type="button" id="sl-btn-voice-summary" onclick="speakSlReceiptSummaryBeforeSave()" class="sl-btn" style="background: rgba(14,165,233,0.2); border-color: rgba(56,189,248,0.5); color: #7dd3fc;">
                    🔊 الملخص
                </button>
                <button type="button" onclick="openSlSignatureModal()" class="sl-btn" style="background: rgba(168,85,247,0.18); border-color: rgba(192,132,252,0.45); color: #e9d5ff;">
                    ✍️ التوقيع
                </button>
                <button type="button" id="sl-btn-debit-note" onclick="openSlShortageDebitNoteModal()" class="sl-btn" style="background: rgba(245,158,11,0.18); border-color: rgba(251,191,36,0.45); color: #fde047;">
                    ⚠️ إشعار فرق
                </button>
                <button type="button" id="sl-btn-whatsapp-dispatch" onclick="shareSlWhatsApp()" class="sl-btn" style="background: rgba(34,197,94,0.22); border-color: #22c55e; color: #4ade80;" title="تجهيز وإرسال إشعار واتساب مفهرس ومنسق بفواصل الآلاف بضغطة واحدة">
                    💬 واتساب
                </button>
                <button type="button" onclick="printSlOfficialNote()" class="sl-btn" style="background: rgba(56,189,248,0.18); border-color: rgba(56,189,248,0.45); color: #38bdf8;">
                    🖨️ طباعة A4
                </button>
                <button type="button" id="sl-btn-print-80mm" onclick="printSlThermalReceipt80mm()" class="sl-btn" style="background: rgba(14,165,233,0.18); border-color: rgba(14,165,233,0.45); color: #7dd3fc;">
                    🧾 80mm
                </button>
                <button type="button" id="sl-btn-print-labels" onclick="printSlBinBatchLabels()" class="sl-btn" style="background: rgba(168,85,247,0.2); border-color: rgba(192,132,252,0.5); color: #e9d5ff;">
                    🏷️ الملصقات
                </button>

                <!-- ✏️ زر التعديل القياسي الموحد مكاناً ولوناً (Order 2) -->
                <button type="button" id="sl-btn-edit-doc" onclick="window.enableSlDocumentEditMode && window.enableSlDocumentEditMode()" class="sl-btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); border-color: #fbbf24; color: #ffffff;" title="تعديل وفحص كميات وأرفف المستند الحالي (F4)">
                    ✏️ تعديل <kbd class="sl-kbd-badge">F4</kbd>
                </button>

                <!-- 💾 زر الحفظ والاعتماد القياسي الموحد مكاناً ولوناً مع قفل الحفظ مرة واحدة فقط (Single-Save Lock — Order 2) -->
                <button type="button" id="sl-btn-execute" onclick="window.executeSlTransactionSingleLock ? window.executeSlTransactionSingleLock(this) : executeSlTransaction()" class="sl-btn" style="padding: 10px 22px; font-size: 0.96rem; background: linear-gradient(135deg, var(--sl-primary), var(--sl-primary-dark)); border-color: var(--sl-primary-light); color: #ffffff; box-shadow: 0 8px 25px var(--sl-primary-glow);">
                    💾 حفظ واعتماد <kbd class="sl-kbd-badge">F10</kbd>
                </button>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 1: MOBILE CAMERA DIRECT BRIDGE (ربط كاميرا الجوال باللابتوب)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-mobile-bridge-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(14px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:600px; border:2px solid var(--sl-primary-light); padding:0;">
            <div style="background:linear-gradient(135deg, #064e3b, #0284c7); padding:14px 20px; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:1.6rem;">📱</span>
                    <h3 style="margin:0; font-size:1.08rem; font-weight:900; color:#fff;">ربط كاميرا الجوال باللابتوب (GRN ⇄ GDN)</h3>
                </div>
                <button type="button" onclick="closeSlMobileBridgeModal()" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="padding:18px; display:flex; flex-direction:column; gap:14px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; align-items:center;">
                    <div style="background:#ffffff; padding:12px; border-radius:16px; text-align:center; border:3px solid var(--sl-primary);">
                        <img id="sl-bridge-qr-img" src="" alt="Pairing QR" style="width:180px; height:180px; display:block; margin:0 auto;">
                        <div style="color:#000000; font-weight:900; font-size:0.82rem; margin-top:6px;">امسح الكود بكاميرا الجوال</div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; flex-direction:column; gap:7px;">
                            <span class="sl-badge sl-badge-done" style="justify-content:center; padding:7px 12px;">🤖 تعرف تلقائي (GRN / GDN)</span>
                            <span class="sl-badge sl-badge-partial" style="justify-content:center; padding:7px 12px;">⚡ مسح متتابع فوري</span>
                            <span class="sl-badge sl-badge-pending" style="justify-content:center; padding:7px 12px;">🚫 حماية آلية ضد التكرار</span>
                        </div>
                        <a id="sl-bridge-open-link" href="mobile-qr-gun.php" target="_blank" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; text-decoration:none; padding:10px;">
                            🚀 فتح عدسة الجوال المباشرة
                        </a>
                    </div>
                </div>
                <div style="background:rgba(2,6,23,0.8); border:1px solid rgba(148,163,184,0.25); border-radius:12px; padding:10px 14px; max-height:130px; overflow-y:auto;">
                    <div style="font-size:0.78rem; color:#94a3b8; font-weight:900; margin-bottom:4px;">⚡ سجل المسحات المباشرة:</div>
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

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 3: WEIGHBRIDGE / ELECTRONIC SCALE INTEGRATION (ربط الميزان الإلكتروني)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-weighbridge-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:520px; padding:20px; border:2px solid #fbbf24;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid rgba(251,191,36,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.08rem; font-weight:900; color:#fde047;">⚖️ الميزان الإلكتروني (القائم − الفارغ = الصافي)</h3>
                <button type="button" onclick="document.getElementById('sl-weighbridge-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:800; color:#cbd5e1; display:block; margin-bottom:4px;">📦 الصنف المستهدف:</label>
                        <select id="sl-wb-item-select" class="sl-input" style="width:100%; padding:6px 10px !important; font-size:0.84rem !important;"></select>
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:800; color:#cbd5e1; display:block; margin-bottom:4px;">⚖️ وحدة قراءة الميزان:</label>
                        <select id="sl-wb-unit-mode" class="sl-input" onchange="calcSlWeighbridgeNet()" style="width:100%; padding:6px 10px !important; font-size:0.84rem !important;">
                            <option value="ton">الطن (Ton)</option>
                            <option value="kg">الكيلوجرام (KG ÷ 1000 = طن)</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:800; color:#7dd3fc; display:block; margin-bottom:4px;">🚛 الوزن القائم (Gross):</label>
                        <input type="number" step="any" id="sl-wb-gross" class="sl-input" placeholder="0.00" oninput="calcSlWeighbridgeNet()" style="width:100%; text-align:center;">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:800; color:#fca5a5; display:block; margin-bottom:4px;">🛻 وزن الفارغ (Tare):</label>
                        <input type="number" step="any" id="sl-wb-tare" class="sl-input" placeholder="0.00" oninput="calcSlWeighbridgeNet()" style="width:100%; text-align:center;">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:800; color:#6ee7b7; display:block; margin-bottom:4px;">✅ الصافي المعتمد (Net):</label>
                        <input type="text" id="sl-wb-net" class="sl-input" readonly value="0" style="width:100%; text-align:center; color:#34d399 !important; border-color:#10b981 !important;">
                    </div>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <input type="text" id="sl-wb-ticket-no" class="sl-input" placeholder="🎫 رقم تذكرة الميزان (اختياري)..." style="flex:1; padding:6px 10px !important; font-size:0.82rem !important;">
                    <button type="button" onclick="readSlSerialWeighbridge()" class="sl-btn" style="background:rgba(56,189,248,0.18); border-color:#38bdf8; color:#7dd3fc; font-size:0.8rem;">🔌 قراءة منفذ الميزان</button>
                </div>
                <button type="button" onclick="applySlWeighbridgeToRow()" class="sl-btn" style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#020617; font-weight:900; padding:10px; border:none;">
                    ⚡ اعتماد الوزن الصافي في سطر الصنف
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 4: TOUCH SIGNATURE PAD (التوقيع الإلكتروني الحي للسائق وأمين المخزن)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-signature-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:660px; padding:20px; border:2px solid #c084fc;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid rgba(192,132,252,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.08rem; font-weight:900; color:#e9d5ff;">✍️ التوقيع الإلكتروني الحي للمذكرة الرسمية</h3>
                <button type="button" onclick="document.getElementById('sl-signature-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(270px, 1fr)); gap:14px;">
                <div style="background:rgba(15,23,42,0.85); padding:10px; border-radius:12px; border:1px solid rgba(148,163,184,0.25); text-align:center;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <span style="font-size:0.82rem; font-weight:900; color:#6ee7b7;">👨‍💼 توقيع أمين المخزن</span>
                        <button type="button" onclick="clearSlSignatureCanvas('sl-sig-keeper')" class="sl-btn" style="padding:2px 8px; font-size:0.72rem; color:#fca5a5;">مسح</button>
                    </div>
                    <canvas id="sl-sig-keeper" width="270" height="130" style="background:#ffffff; border-radius:8px; cursor:crosshair; touch-action:none; width:100%; height:130px;"></canvas>
                </div>
                <div style="background:rgba(15,23,42,0.85); padding:10px; border-radius:12px; border:1px solid rgba(148,163,184,0.25); text-align:center;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <span style="font-size:0.82rem; font-weight:900; color:#7dd3fc;">🚚 توقيع السائق / المستلم</span>
                        <button type="button" onclick="clearSlSignatureCanvas('sl-sig-driver')" class="sl-btn" style="padding:2px 8px; font-size:0.72rem; color:#fca5a5;">مسح</button>
                    </div>
                    <canvas id="sl-sig-driver" width="270" height="130" style="background:#ffffff; border-radius:8px; cursor:crosshair; touch-action:none; width:100%; height:130px;"></canvas>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:14px;">
                <button type="button" onclick="saveSlSignatures()" class="sl-btn" style="background:linear-gradient(135deg,#a855f7,#6366f1); color:#fff; padding:9px 22px; border:none;">
                    💾 حفظ واعتماد التوقيعات للطباعة
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 5: AUTO-DEBIT NOTE ON SHORTAGE / DAMAGE (إشعار خصم آلي للفروقات والتوالف)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-debit-note-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:580px; padding:20px; border:2px solid #f59e0b;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(245,158,11,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.08rem; font-weight:900; color:#fde047;">⚠️ إشعار خصم ومطالبة بالفروقات / التوالف</h3>
                <button type="button" onclick="document.getElementById('sl-debit-note-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-dn-party-summary" style="font-size:0.86rem; font-weight:800; color:#cbd5e1; margin-bottom:8px;"></div>
            <div id="sl-debit-note-body" style="display:flex; flex-direction:column; gap:10px; max-height:320px; overflow-y:auto;">
                <table class="sl-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>كمية الفاتورة</th>
                            <th>المنفذ</th>
                            <th>كمية النقص/التلف</th>
                            <th>السعر</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody id="sl-dn-items-tbody"></tbody>
                </table>
            </div>
            <div style="margin-top:10px;">
                <input type="text" id="sl-dn-reason" class="sl-input" placeholder="📝 سبب الخصم / المطالبة..." style="width:100%; padding:7px 12px !important; font-size:0.84rem !important;">
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.1);">
                <div style="font-size:0.92rem; font-weight:900; color:#fde047;">
                    إجمالي قيمة المطالبة: <span id="sl-dn-total-amount" style="color:#34d399; font-size:1.1rem;">0.00</span>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="postSlSupplierShortageClaim(true)" class="sl-btn" style="background:rgba(56,189,248,0.2); border-color:#38bdf8; color:#7dd3fc;">🖨️ اعتماد وطباعة الإشعار</button>
                    <button type="button" onclick="postSlSupplierShortageClaim(false)" class="sl-btn" style="background:linear-gradient(135deg,#f59e0b,#dc2626); color:#fff; border:none;">⚡ ترحيل خصم على حساب الطرف</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 6: QUICK-ADD WAREHOUSE (+) WITHOUT LEAVING SCREEN (الإضافة السريعة للمستودع)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-quick-warehouse-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:440px; padding:20px; border:2px solid #10b981;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid rgba(16,185,129,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#6ee7b7;">🏢 إضافة مستودع جديد فوراً</h3>
                <button type="button" onclick="document.getElementById('sl-quick-warehouse-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div>
                    <label style="font-size:0.8rem; font-weight:800; color:#cbd5e1; display:block; margin-bottom:4px;">🏢 اسم المستودع الجديد:</label>
                    <input type="text" id="sl-new-wh-name" class="sl-input" placeholder="مثال: مستودع الفرع الشمالي..." style="width:100%;" onkeydown="if(event.key==='Enter'){event.preventDefault(); document.getElementById('sl-new-wh-loc')?.focus();}">
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:800; color:#cbd5e1; display:block; margin-bottom:4px;">📍 الموقع / أمين المستودع (اختياري):</label>
                    <input type="text" id="sl-new-wh-loc" class="sl-input" placeholder="الموقع أو المسؤول..." style="width:100%;" onkeydown="if(event.key==='Enter'){event.preventDefault(); saveSlQuickWarehouse();}">
                </div>
                <button type="button" onclick="saveSlQuickWarehouse()" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; padding:10px; border:none;">
                    💾 حفظ واختيار المستودع فوراً
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 7: NON-BLOCKING CUSTOM UNDO CONFIRMATION MODAL (بديل confirm الجاهزة)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-confirm-undo-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:460px; padding:22px; border:2px solid #ef4444;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(239,68,68,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#fca5a5;">↩️ تأكيد عكس وإلغاء الحركة المخزنية</h3>
                <button type="button" onclick="document.getElementById('sl-confirm-undo-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-confirm-undo-msg" style="font-size:0.92rem; font-weight:800; color:#f8fafc; line-height:1.65; margin-bottom:12px;"></div>
            <div style="background:rgba(15,23,42,0.75); padding:10px 12px; border-radius:10px; border:1px solid rgba(239,68,68,0.35); margin-bottom:14px;">
                <label style="font-size:0.78rem; font-weight:900; color:#fca5a5; display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span>🔐 رمز الأمان الإداري لعكس الحركة (PIN):</span>
                    <button type="button" onclick="document.getElementById('sl-undo-pin-input').value = (localStorage.getItem('erp_admin_pin') || '1234');" class="sl-btn" style="padding:2px 8px; font-size:0.7rem; background:rgba(56,189,248,0.18); border-color:#38bdf8; color:#7dd3fc;">⚡ تعبئة سريعة (1234)</button>
                </label>
                <input type="password" id="sl-undo-pin-input" class="sl-input" placeholder="أدخل رمز الحماية (الافتراضي: 1234)..." style="width:100%; text-align:center; letter-spacing:4px; font-size:1rem !important;" onkeydown="if(event.key==='Enter'){event.preventDefault(); confirmSlUndoExecution();}">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('sl-confirm-undo-modal').style.display='none'" class="sl-btn" style="padding:8px 18px;">تراجع</button>
                <button type="button" id="sl-confirm-undo-yes-btn" onclick="confirmSlUndoExecution()" class="sl-btn" style="background:linear-gradient(135deg,#ef4444,#b91c1c); color:#fff; border:none; padding:8px 20px;">
                    ↩️ نعم، اعكس الحركة الآن
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 8: DRILL-DOWN ORIGINAL INVOICE VIEWER (شاشة وليدة لعرض أصل الفاتورة المالي والرقابي)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-drilldown-invoice-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:760px; padding:20px; border:2px solid #38bdf8;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(56,189,248,0.25); padding-bottom:10px;">
                <h3 id="sl-drilldown-title" style="margin:0; font-size:1.08rem; font-weight:900; color:#38bdf8;">🔍 أصل المستند المالي والرقابي (Drill-Down)</h3>
                <button type="button" onclick="document.getElementById('sl-drilldown-invoice-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-drilldown-body" style="display:flex; flex-direction:column; gap:12px; max-height:430px; overflow-y:auto;">
                <div id="sl-drilldown-hdr-summary" style="display:flex; flex-wrap:wrap; gap:14px; justify-content:space-between; background:rgba(15,23,42,0.75); padding:10px 14px; border-radius:10px; border:1px solid rgba(56,189,248,0.25); font-size:0.84rem; font-weight:800;"></div>
                <div style="overflow-x:auto; border-radius:10px; border:1px solid rgba(148,163,184,0.25);">
                    <table class="sl-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الصنف</th>
                                <th>الوحدة والمعامل</th>
                                <th>كمية الفاتورة</th>
                                <th>المنفذ</th>
                                <th>المتبقي</th>
                                <th>السعر</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody id="sl-drilldown-items-tbody"></tbody>
                    </table>
                </div>
                <div id="sl-drilldown-financial-footer" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(145px, 1fr)); gap:8px;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-top:14px; padding-top:10px; border-top:1px solid rgba(148,163,184,0.2);">
                <a id="sl-drilldown-open-source-link" href="purchase.php" target="_blank" class="sl-btn" style="background:rgba(168,85,247,0.2); border-color:#c084fc; color:#e9d5ff; text-decoration:none;">🔗 فتح في شاشة الفواتير</a>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="printSlOfficialNote()" class="sl-btn" style="background:rgba(56,189,248,0.2); border-color:#38bdf8; color:#7dd3fc;">🖨️ طباعة A4</button>
                    <button type="button" onclick="printSlThermalReceipt80mm()" class="sl-btn" style="background:rgba(16,185,129,0.2); border-color:#34d399; color:#6ee7b7;">🧾 إيصال 80mm</button>
                    <button type="button" onclick="document.getElementById('sl-drilldown-invoice-modal').style.display='none'" class="sl-btn" style="padding:8px 18px;">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 9: AI CAMERA BATCH & EXPIRY OCR SCANNER (الماسح البصري لتواريخ الصلاحية وأرقام التشغيلات)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-batch-ocr-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:540px; padding:20px; border:2.2px solid #34d399;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(52,211,153,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#6ee7b7;">📷 الماسح البصري الذكي للتشغيلة وتاريخ الصلاحية (Batch/EXP OCR)</h3>
                <button type="button" onclick="document.getElementById('sl-batch-ocr-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:900; color:#cbd5e1; display:block; margin-bottom:4px;">📦 الصنف المستهدف:</label>
                        <select id="sl-ocr-item-select" class="sl-input" style="width:100%; padding:6px 10px !important; font-size:0.84rem !important;"></select>
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:900; color:#cbd5e1; display:block; margin-bottom:4px;">📸 التقاط / رفع ملصق الكرتون:</label>
                        <input type="file" id="sl-ocr-file-input" accept="image/*" capture="environment" style="display:none;" onchange="handleSlBatchOcrImage(event)">
                        <button type="button" onclick="document.getElementById('sl-ocr-file-input').click()" class="sl-btn" style="width:100%; background:rgba(16,185,129,0.2); border-color:#34d399; color:#6ee7b7; padding:7px;">📸 مسح ملصق التشغيلة</button>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:900; color:#7dd3fc; display:block; margin-bottom:4px;">🏷️ رقم التشغيلة (LOT / Batch):</label>
                        <input type="text" id="sl-ocr-batch-code" class="sl-input" placeholder="مثال: LOT-2026-X9" style="width:100%; text-align:center;">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:900; color:#fcd34d; display:block; margin-bottom:4px;">📅 تاريخ الانتهاء (EXP Date):</label>
                        <input type="date" id="sl-ocr-exp-date" class="sl-input" style="width:100%; text-align:center;">
                    </div>
                </div>
                <div style="display:flex; gap:8px; justify-content:space-between; flex-wrap:wrap;">
                    <button type="button" onclick="autoGenerateSlSmartBatchExp()" class="sl-btn" style="background:rgba(56,189,248,0.18); border-color:#38bdf8; color:#7dd3fc; font-size:0.8rem;">🤖 توليد ذكي فوري للتشغيلة</button>
                    <div style="display:flex; gap:8px;">
                        <button type="button" onclick="applySlBatchOcrToRow(false)" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none;">✅ تطبيق على الصنف</button>
                        <button type="button" onclick="applySlBatchOcrToRow(true)" class="sl-btn" style="background:linear-gradient(135deg,#0284c7,#2563eb); color:#fff; border:none;">⚡ تطبيق على كل الأصناف</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 10: VISUAL WAREHOUSE BIN HEATMAP (خريطة المستودع والأرفف التفاعلية)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-bin-heatmap-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:680px; padding:20px; border:2.2px solid #818cf8;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(129,140,248,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#c7d2fe;">🗺️ خريطة المستودع التفاعلية وتسكين الأرفف (Visual Bin Heatmap)</h3>
                <button type="button" onclick="document.getElementById('sl-bin-heatmap-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <label style="font-size:0.8rem; font-weight:900;">📦 الصنف المستهدف للتسكين:</label>
                        <select id="sl-heatmap-item-select" class="sl-input" style="padding:5px 10px !important; font-size:0.82rem !important;"></select>
                    </div>
                    <div style="display:flex; gap:8px; font-size:0.75rem; font-weight:900;">
                        <span class="sl-badge sl-badge-done">🟢 متاح للتسكين</span>
                        <span class="sl-badge sl-badge-pending">🟡 إشغال جزئي</span>
                        <span class="sl-badge" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:2px solid #ef4444;">🔴 ممتلئ</span>
                    </div>
                </div>
                <div id="sl-bin-heatmap-grid" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:10px; max-height:320px; overflow-y:auto; padding:4px;"></div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 11: SMART FLEET & DELIVERY ROUTE DISPATCHER (مُخطّط مسار التوصيل وتتبع المناديب)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-fleet-dispatch-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:560px; padding:20px; border:2.2px solid #34d399;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(52,211,153,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#6ee7b7;">🚚 مُخطّط مسار التوصيل وتتبع أسطول المناديب (Fleet Dispatcher)</h3>
                <button type="button" onclick="document.getElementById('sl-fleet-dispatch-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:900; display:block; margin-bottom:4px;">🚚 اسم السائق / المندوب:</label>
                        <input type="text" id="sl-fd-driver" class="sl-input" placeholder="اسم المندوب..." style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:900; display:block; margin-bottom:4px;">📱 هاتف السائق (واتساب):</label>
                        <input type="text" id="sl-fd-phone" class="sl-input" placeholder="2499..." style="width:100%;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:900; display:block; margin-bottom:4px;">📍 منطقة / عنوان التوصيل:</label>
                        <input type="text" id="sl-fd-zone" class="sl-input" placeholder="المنطقة / الحي / الشارع..." style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:900; display:block; margin-bottom:4px;">⏱️ وقت الوصول المتوقع (ETA):</label>
                        <input type="text" id="sl-fd-eta" class="sl-input" placeholder="مثال: خلال 45 دقيقة" value="خلال 45 دقيقة" style="width:100%;">
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; gap:8px; margin-top:6px; flex-wrap:wrap;">
                    <button type="button" onclick="window.sendSlFleetDispatchWhatsApp()" class="sl-btn" style="background:rgba(34,197,94,0.22); border-color:#4ade80; color:#4ade80;">💬 إرسال بوليصة المسار واتساب</button>
                    <button type="button" onclick="window.saveSlFleetDispatchPlan()" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none;">✅ اعتماد خطة التوصيل</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 12: ZERO-ERROR LOADING VERIFIER (فاحص مطابقة التحميل بالباركود)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-load-verifier-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:620px; padding:20px; border:2.2px solid #38bdf8;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(56,189,248,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#7dd3fc;">🎯 فاحص مطابقة التحميل بالباركود المتصل (Zero-Error Loading Verifier)</h3>
                <button type="button" onclick="document.getElementById('sl-load-verifier-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; gap:8px;">
                    <input type="text" id="sl-load-verify-input" class="sl-input" placeholder="امسح باركود الكرتون أو اكتب اسم الصنف ثم اضغط Enter..." style="flex:1;" onkeydown="if(event.key==='Enter'){event.preventDefault(); window.processSlLoadingVerifyScan(this.value); this.value='';}">
                    <button type="button" onclick="const i=document.getElementById('sl-load-verify-input'); window.processSlLoadingVerifyScan(i.value); i.value=''; i.focus();" class="sl-btn" style="background:linear-gradient(135deg,#0284c7,#0369a1); color:#fff;">➕ تحقق</button>
                </div>
                <div id="sl-load-verify-table-wrap" style="max-height:240px; overflow-y:auto; border-radius:10px; border:1.5px solid rgba(148,163,184,0.25); padding:8px;"></div>
                <div style="display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                    <button type="button" onclick="window.verifyAllSlLoadingItems()" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff;">⚡ مطابقة وتأكيد تحميل الكل (100%)</button>
                    <button type="button" onclick="document.getElementById('sl-load-verifier-modal').style.display='none'" class="sl-btn">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MODAL 13: SMART CARGO VOLUME & PALLET ESTIMATOR (حاسبة الطبليات والحمولة)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-cargo-estimator-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:560px; padding:20px; border:2.2px solid #fbbf24;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(251,191,36,0.25); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#fde047;">📦 حاسبة حجم ووزن الشحنة وعدد الطبليات (Cargo & Pallet Estimator)</h3>
                <button type="button" onclick="document.getElementById('sl-cargo-estimator-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-cargo-estimator-body" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;"></div>
            <div style="display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                <button type="button" onclick="window.applySlCargoEstimateToNotes()" class="sl-btn" style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff;">📌 تدوين الحمولة والمركبة في الملاحظات</button>
                <button type="button" onclick="document.getElementById('sl-cargo-estimator-modal').style.display='none'" class="sl-btn">إغلاق</button>
            </div>
        </div>
    </div>

    <script>
    // Proposal 1, 2, 3 Controllers for Smart Logistics (Sales Delivery GDN & Purchases GRN)
    window.openSlFleetDispatchModal = function() {
        const modal = document.getElementById('sl-fleet-dispatch-modal');
        if (!modal) return;
        const drvInp = document.getElementById('sl-driver-name');
        const fdDrv = document.getElementById('sl-fd-driver');
        if (drvInp && fdDrv && drvInp.value) fdDrv.value = drvInp.value;
        modal.style.display = 'flex';
        setTimeout(() => document.getElementById('sl-fd-driver')?.focus(), 40);
    };

    window.saveSlFleetDispatchPlan = function() {
        const drv = (document.getElementById('sl-fd-driver')?.value || '').trim();
        const ph = (document.getElementById('sl-fd-phone')?.value || '').trim();
        const zone = (document.getElementById('sl-fd-zone')?.value || '').trim();
        const eta = (document.getElementById('sl-fd-eta')?.value || '').trim();
        if (drv && document.getElementById('sl-driver-name')) {
            document.getElementById('sl-driver-name').value = drv;
        }
        const pNotes = document.getElementById('sl-personal-notes');
        if (pNotes) {
            pNotes.value = `🚚 مسار التوصيل: ${zone || 'العنوان المعتمد'} | السائق: ${drv || '---'} (${ph || '---'}) | الوصول: ${eta || '45 د'}`;
        }
        document.getElementById('sl-fleet-dispatch-modal').style.display = 'none';
        if (typeof showSlToast === 'function') showSlToast('🚚 تم اعتماد خطة مسار التوصيل وبيانات المندوب بنجاح', 'success');
    };

    window.sendSlFleetDispatchWhatsApp = function() {
        const drv = (document.getElementById('sl-fd-driver')?.value || document.getElementById('sl-driver-name')?.value || 'المندوب').trim();
        const ph = (document.getElementById('sl-fd-phone')?.value || '').replace(/[^0-9]/g, '');
        const zone = (document.getElementById('sl-fd-zone')?.value || 'عنوان العميل').trim();
        const eta = (document.getElementById('sl-fd-eta')?.value || 'خلال 45 دقيقة').trim();
        const invNo = document.getElementById('sl-lbl-inv-no')?.textContent || '---';
        const party = document.getElementById('sl-lbl-party-name')?.textContent || 'العميل';
        const msg = `🚚 *أمر تحرك وتوصيل شحنة (GDN)*\n📄 المستند: ${invNo}\n👤 العميل: ${party}\n📍 المنطقة/العنوان: ${zone}\n👨‍✈️ السائق: ${drv}\n⏱️ الوصول المتوقع: ${eta}`;
        window.open(`https://wa.me/${ph}?text=${encodeURIComponent(msg)}`, '_blank');
    };

    window._slVerifiedCounts = {};
    window.openSlLoadingVerifierModal = function() {
        const modal = document.getElementById('sl-load-verifier-modal');
        if (!modal) return;
        window.renderSlLoadingVerifierTable();
        modal.style.display = 'flex';
        setTimeout(() => document.getElementById('sl-load-verify-input')?.focus(), 40);
    };

    window.renderSlLoadingVerifierTable = function() {
        const wrap = document.getElementById('sl-load-verify-table-wrap');
        if (!wrap) return;
        const doc = typeof slCurrentDoc !== 'undefined' ? slCurrentDoc : null;
        if (!doc || !Array.isArray(doc.items) || !doc.items.length) {
            wrap.innerHTML = `<div style="padding:16px;text-align:center;font-weight:900;">⚠️ اختر مستنداً من السجل أولاً لبدء فحص التحميل بالباركود</div>`;
            return;
        }
        wrap.innerHTML = `
            <table class="sl-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="text-align:right;">الصنف</th>
                        <th style="text-align:center;">المطلوب</th>
                        <th style="text-align:center;">المحمل فعلياً</th>
                        <th style="text-align:center;">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    ${doc.items.map((it, idx) => {
                        const m = typeof resolveSlItemMeta === 'function' ? resolveSlItemMeta(it) : { name: it.name || 'صنف', qty: it.qty || 1, unit: it.unit || 'وحدة' };
                        const req = parseFloat(it.remainingQty ?? m.qty) || m.qty;
                        const ver = parseFloat(window._slVerifiedCounts[idx] || 0);
                        const ok = ver >= req;
                        return `
                            <tr>
                                <td style="font-weight:900;">📦 ${m.name}</td>
                                <td style="text-align:center;font-weight:900;">${req} ${m.unit}</td>
                                <td style="text-align:center;font-weight:900;color:#38bdf8;">${ver}</td>
                                <td style="text-align:center;">${ok ? '<span class="sl-badge sl-badge-done">✅ مطابق 100%</span>' : '<span class="sl-badge sl-badge-pending">⏳ قيد التحميل</span>'}</td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;
    };

    window.processSlLoadingVerifyScan = function(rawVal) {
        const q = String(rawVal || '').trim().toLowerCase();
        if (!q) return;
        const doc = typeof slCurrentDoc !== 'undefined' ? slCurrentDoc : null;
        if (!doc || !Array.isArray(doc.items)) return;
        let matchedIdx = -1;
        doc.items.forEach((it, idx) => {
            const name = String(it.name || it.itemName || it.id || '').toLowerCase();
            if (name.includes(q) || q.includes(name) || String(idx + 1) === q) matchedIdx = idx;
        });
        if (matchedIdx === -1) {
            if (typeof showSlToast === 'function') showSlToast(`⛔ تحذير: الباركود/الصنف (${rawVal}) غير مدرج في هذه الفاتورة!`, 'error');
            return;
        }
        window._slVerifiedCounts[matchedIdx] = (parseFloat(window._slVerifiedCounts[matchedIdx] || 0) || 0) + 1;
        window.renderSlLoadingVerifierTable();
        if (typeof showSlToast === 'function') showSlToast('✅ تم التحقق من الصنف وإضافته لعداد التحميل', 'success');
    };

    window.verifyAllSlLoadingItems = function() {
        const doc = typeof slCurrentDoc !== 'undefined' ? slCurrentDoc : null;
        if (doc && Array.isArray(doc.items)) {
            doc.items.forEach((it, idx) => {
                const req = parseFloat(it.remainingQty ?? it.qty ?? 1) || 1;
                window._slVerifiedCounts[idx] = req;
            });
        }
        if (typeof fillAllSlQuantities === 'function') fillAllSlQuantities(1);
        window.renderSlLoadingVerifierTable();
        document.getElementById('sl-load-verifier-modal').style.display = 'none';
        if (typeof showSlToast === 'function') showSlToast('🎯 تمت مطابقة وتأكيد تحميل جميع البنود بنسبة 100%', 'success');
    };

    window.openSlCargoEstimatorModal = function() {
        const modal = document.getElementById('sl-cargo-estimator-modal');
        if (!modal) return;
        const priInputs = document.querySelectorAll('.sl-qty-primary-input');
        let totalUnits = 0;
        priInputs.forEach(inp => { totalUnits += (parseFloat(inp.value) || 0); });
        if (totalUnits <= 0 && typeof slCurrentDoc !== 'undefined' && slCurrentDoc && Array.isArray(slCurrentDoc.items)) {
            slCurrentDoc.items.forEach(it => { totalUnits += (parseFloat(it.qty || it.quantity || 1) || 1); });
        }
        const estWeightKg = Math.round(totalUnits * 25);
        const estVolumeM3 = +(totalUnits * 0.045).toFixed(2);
        const estPallets = Math.max(1, Math.ceil(estWeightKg / 850));
        const vehicle = estWeightKg <= 1500
            ? '🛻 بيك أب خفيف (1.5 طن)'
            : (estWeightKg <= 4500 ? '🚚 دينا متوسطة (4.5 طن)' : '🚛 شاحنة ثقيلة / تريلا (25 طن)');
        window._lastSlCargoSummary = `📦 الحمولة: ${estWeightKg} كجم | الحجم: ${estVolumeM3} م³ | الطبليات: ${estPallets} طبلية | المركبة: ${vehicle}`;
        const body = document.getElementById('sl-cargo-estimator-body');
        if (body) {
            body.innerHTML = `
                <div style="padding:12px;border-radius:12px;background:rgba(15,23,42,0.65);border:1.8px solid rgba(56,189,248,0.4);text-align:center;">
                    <div style="font-size:0.78rem;">⚖️ الوزن التقديري الإجمالي</div>
                    <div style="font-size:1.25rem;font-weight:900;color:#38bdf8;margin-top:4px;">${estWeightKg.toLocaleString('en-US')} كجم</div>
                </div>
                <div style="padding:12px;border-radius:12px;background:rgba(15,23,42,0.65);border:1.8px solid rgba(16,185,129,0.4);text-align:center;">
                    <div style="font-size:0.78rem;">📐 الحجم التكعيبي (CBM)</div>
                    <div style="font-size:1.25rem;font-weight:900;color:#34d399;margin-top:4px;">${estVolumeM3} م³</div>
                </div>
                <div style="padding:12px;border-radius:12px;background:rgba(15,23,42,0.65);border:1.8px solid rgba(251,191,36,0.4);text-align:center;">
                    <div style="font-size:0.78rem;">🧱 عدد الطبليات القياسية</div>
                    <div style="font-size:1.25rem;font-weight:900;color:#fbbf24;margin-top:4px;">${estPallets} طبلية</div>
                </div>
                <div style="padding:12px;border-radius:12px;background:rgba(15,23,42,0.65);border:1.8px solid rgba(192,132,252,0.4);text-align:center;">
                    <div style="font-size:0.78rem;">🚛 المركبة الموصى بها</div>
                    <div style="font-size:0.98rem;font-weight:900;color:#c084fc;margin-top:6px;">${vehicle}</div>
                </div>
            `;
        }
        modal.style.display = 'flex';
    };

    window.applySlCargoEstimateToNotes = function() {
        const wNotes = document.getElementById('sl-warehouse-notes');
        if (wNotes && window._lastSlCargoSummary) {
            wNotes.value = (wNotes.value ? wNotes.value + ' | ' : '') + window._lastSlCargoSummary;
        }
        document.getElementById('sl-cargo-estimator-modal').style.display = 'none';
        if (typeof showSlToast === 'function') showSlToast('📦 تم تدوين تقدير الحمولة والطبليات والمركبة في الملاحظات', 'success');
    };
    </script>

    <!-- ═══════════════════════════════════════════════════════════════════
         CONSTITUTION 2 APPROVED PROPOSALS MODALS:
         1. المساعد الصوتي التفاعلي لبوابة الصرف (Voice Gate Commander)
         2. النداء الصوتي الآلي للسائقين واللوحات (Driver & Plate PA Announcer)
         3. بطاقة الاختبار التفاعلي السريع لأمين الصرف (60-Second GDN Mastery Quiz)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-voice-commander-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:560px; padding:20px; border:2.2px solid #38bdf8; background:var(--sl-bg-card); color:var(--sl-text-primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid rgba(56,189,248,0.3); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#38bdf8;">🎤 المساعد الصوتي التفاعلي لبوابة الصرف (Voice Gate Commander)</h3>
                <button type="button" onclick="document.getElementById('sl-voice-commander-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:12px;">
                <input type="text" id="sl-voice-cmd-input" class="sl-input" placeholder="انطق أو اكتب الأمر (مثال: اقرأ الملخص، نصف الكمية، افتح مسار التوصيل، اعتمد الصرف)..." style="flex:1;" onkeydown="if(event.key==='Enter'){event.preventDefault(); window.executeSlVoiceCommand(this.value);}">
                <button type="button" onclick="window.listenSlVoiceGateCommand()" class="sl-btn" style="background:linear-gradient(135deg,#0284c7,#2563eb); color:#fff; border:none;">🎙️ تحدث بالمايك</button>
                <button type="button" onclick="window.executeSlVoiceCommand(document.getElementById('sl-voice-cmd-input').value)" class="sl-btn" style="background:rgba(16,185,129,0.22); border-color:#34d399; color:#34d399;">⚡ تنفيذ</button>
            </div>
            <div id="sl-voice-cmd-status" style="font-size:0.84rem; font-weight:800; padding:10px 12px; border-radius:10px; background:rgba(15,23,42,0.55); border:1.5px solid rgba(56,189,248,0.3); margin-bottom:12px;">
                🔊 جاهز لاستقبال أوامرك الصوتية أو الضغط السريع بالأسفل...
            </div>
            <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:8px;">
                <button type="button" onclick="window.executeSlVoiceCommand('اقرأ الملخص')" class="sl-btn" style="justify-content:flex-start; background:rgba(56,189,248,0.14); border-color:#38bdf8;">🔊 "اقرأ الملخص الصوتي"</button>
                <button type="button" onclick="window.executeSlVoiceCommand('تعبئة الكل')" class="sl-btn" style="justify-content:flex-start; background:rgba(16,185,129,0.14); border-color:#34d399;">⚡ "تعبئة الكل 100%"</button>
                <button type="button" onclick="window.executeSlVoiceCommand('نصف الكمية')" class="sl-btn" style="justify-content:flex-start; background:rgba(245,158,11,0.14); border-color:#fbbf24;">🌗 "نصف الكمية 50%"</button>
                <button type="button" onclick="window.executeSlVoiceCommand('مسار التوصيل')" class="sl-btn" style="justify-content:flex-start; background:rgba(168,85,247,0.14); border-color:#c084fc;">🚚 "افتح مسار التوصيل"</button>
                <button type="button" onclick="window.executeSlVoiceCommand('نداء السائق')" class="sl-btn" style="justify-content:flex-start; background:rgba(236,72,153,0.14); border-color:#f472b6;">📢 "نداء السائق واللوحة"</button>
                <button type="button" onclick="window.executeSlVoiceCommand('اعتمد الصرف')" class="sl-btn" style="justify-content:flex-start; background:linear-gradient(135deg,rgba(16,185,129,0.28),rgba(5,150,105,0.28)); border-color:#10b981;">🚀 "اعتمد الصرف الآن"</button>
            </div>
        </div>
    </div>

    <div id="sl-gdn-quiz-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:600px; padding:20px; border:2.2px solid #c084fc; background:var(--sl-bg-card); color:var(--sl-text-primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid rgba(192,132,252,0.3); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#c084fc;">🎓 اختبار الإتقان التفاعلي السريع لأمين صرف المبيعات (GDN Quiz)</h3>
                <button type="button" onclick="document.getElementById('sl-gdn-quiz-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-gdn-quiz-body" style="display:flex; flex-direction:column; gap:12px; max-height:380px; overflow-y:auto;"></div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; padding-top:10px; border-top:1px solid rgba(148,163,184,0.25);">
                <div id="sl-gdn-quiz-score" style="font-size:0.95rem; font-weight:900; color:#34d399;">النتيجة: 0 / 3</div>
                <button type="button" onclick="window.openSlGdnMasteryQuizModal(true)" class="sl-btn" style="background:rgba(168,85,247,0.2); border-color:#c084fc; color:#e9d5ff;">🔄 إعادة الاختبار</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         CONSTITUTION 3 APPROVED PROPOSALS MODALS:
         1. تقرير كشف التسليمات المتبقية وأعمار ذمم البضاعة للعملاء (Pending Delivery & Aging Report)
         2. محرك التسوية المالية والمخزنية الذكي للمرتجعات عند البوابة (Gate Instant Partial Return & Credit Note)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-pending-aging-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:760px; padding:20px; border:2.2px solid #38bdf8; background:var(--sl-bg-card); color:var(--sl-text-primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(56,189,248,0.3); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#38bdf8;">📊 تقرير كشف التسليمات المتبقية وأعمار ذمم البضاعة للعملاء</h3>
                <button type="button" onclick="document.getElementById('sl-pending-aging-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-pending-aging-kpis" style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:12px;"></div>
            <div style="max-height:320px; overflow-y:auto; border-radius:10px; border:1px solid rgba(148,163,184,0.25);">
                <table class="sl-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>العميل</th>
                            <th>تاريخ الفاتورة</th>
                            <th>عمر الذمة (أيام)</th>
                            <th>الكميات المتبقية للتسليم</th>
                            <th>القيمة المتبقية</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="sl-pending-aging-tbody"></tbody>
                </table>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:14px;">
                <button type="button" onclick="window.printSlPendingAgingReport()" class="sl-btn" style="background:linear-gradient(135deg,#0284c7,#2563eb); color:#fff; border:none;">🖨️ طباعة كشف ذمم البضاعة (A4)</button>
            </div>
        </div>
    </div>

    <div id="sl-gate-credit-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:600px; padding:20px; border:2.2px solid #f472b6; background:var(--sl-bg-card); color:var(--sl-text-primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(244,114,182,0.3); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#f472b6;">↩️ تسوية مرتجع بوابة فوري وإصدار إشعار دائن للعميل (Credit Note)</h3>
                <button type="button" onclick="document.getElementById('sl-gate-credit-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-gate-cn-header" style="font-size:0.86rem; font-weight:800; margin-bottom:8px;"></div>
            <div style="max-height:280px; overflow-y:auto;">
                <table class="sl-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>كمية الفاتورة</th>
                            <th>كمية المرتجع عند البوابة</th>
                            <th>السعر</th>
                            <th>قيمة المرتجع</th>
                        </tr>
                    </thead>
                    <tbody id="sl-gate-cn-tbody"></tbody>
                </table>
            </div>
            <div style="margin-top:10px;">
                <input type="text" id="sl-gate-cn-reason" class="sl-input" placeholder="📝 سبب المرتجع عند البوابة (مثال: فائض عن سعة مركبة العميل / استبدال مقاس)..." style="width:100%;">
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; padding-top:10px; border-top:1px solid rgba(148,163,184,0.25);">
                <div style="font-weight:900;">إجمالي الإشعار الدائن: <span id="sl-gate-cn-total" style="color:#34d399; font-size:1.1rem;">0.00</span></div>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="window.postSlGateCustomerCreditNote(true)" class="sl-btn" style="background:rgba(56,189,248,0.2); border-color:#38bdf8; color:#7dd3fc;">🖨️ اعتماد وطباعة الإشعار الدائن</button>
                    <button type="button" onclick="window.postSlGateCustomerCreditNote(false)" class="sl-btn" style="background:linear-gradient(135deg,#ec4899,#be185d); color:#fff; border:none;">⚡ ترحيل الإشعار الدائن وإعادة الرصيد للمخزن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         CONSTITUTION 4 APPROVED PROPOSALS MODALS:
         1. فاحص سلامة التباين والسمات والتوريث الآلي (Live Theme & Contrast Integrity Inspector)
         2. الحافظة الذكية للقوالب اللوجستية المتكررة (1-Click Logistics Dispatch Presets)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-contrast-inspector-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:580px; padding:20px; border:2.5px solid #38bdf8; background:var(--sl-bg-card); color:var(--sl-text-primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(56,189,248,0.3); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#38bdf8;">🛡️ فاحص سلامة التباين وسُمك الحواف (+2) والتوريث التلقائي</h3>
                <button type="button" onclick="document.getElementById('sl-contrast-inspector-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-contrast-audit-body" style="display:flex; flex-direction:column; gap:10px;"></div>
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:14px;">
                <button type="button" onclick="window.runSlContrastIntegrityAudit(true)" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none;">⚡ تعزيز وتأكيد التوريث التلقائي على كل العناصر (100%)</button>
            </div>
        </div>
    </div>

    <div id="sl-dispatch-presets-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:600px; padding:20px; border:2.5px solid #c084fc; background:var(--sl-bg-card); color:var(--sl-text-primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(192,132,252,0.3); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#c084fc;">⚡ الحافظة الذكية لقوالب السائقين والمركبات السريعة (1-Click Presets)</h3>
                <button type="button" onclick="document.getElementById('sl-dispatch-presets-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:8px; margin-bottom:12px;">
                <input type="text" id="sl-preset-driver" class="sl-input" placeholder="🚚 اسم السائق...">
                <input type="text" id="sl-preset-plate" class="sl-input" placeholder="🔢 رقم اللوحة...">
                <input type="text" id="sl-preset-receiver" class="sl-input" placeholder="🤝 المستلم / المندوب...">
                <button type="button" onclick="window.saveSlCurrentDispatchPreset()" class="sl-btn" style="background:linear-gradient(135deg,#a855f7,#6366f1); color:#fff; border:none;">➕ حفظ قالب</button>
            </div>
            <div id="sl-dispatch-presets-list" style="display:flex; flex-direction:column; gap:8px; max-height:280px; overflow-y:auto;"></div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         CONSTITUTION 1 (1 OF 14) MASTER AUDIT & CONTROL CHILD MODAL:
         لوحة فحص وإثبات الدستور الأول (السمات، الشاشات الوليدة، سُمك +2، منع انقلاب اللون، وتناسب 6:8)
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-constitution1-master-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:640px; padding:20px; border:2.5px solid #10b981;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:2.2px solid rgba(16,185,129,0.35); padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.06rem; font-weight:900; color:#34d399;">🛡️ لوحة فحص وإثبات الدستور الأول (1 من 14) — يعمل 100%</h3>
                <button type="button" onclick="document.getElementById('sl-constitution1-master-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div id="sl-constitution1-master-body" style="display:flex; flex-direction:column; gap:10px;"></div>
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-top:14px; padding-top:10px; border-top:2.2px solid rgba(148,163,184,0.25);">
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <button type="button" onclick="window.setSlProportionalZoom && window.setSlProportionalZoom(0.9)" class="sl-btn" style="padding:5px 10px; font-size:0.78rem;">🔍 90% (6:8)</button>
                    <button type="button" onclick="window.setSlProportionalZoom && window.setSlProportionalZoom(1.0)" class="sl-btn" style="padding:5px 10px; font-size:0.78rem;">🎯 100% قياسي</button>
                    <button type="button" onclick="window.setSlProportionalZoom && window.setSlProportionalZoom(1.1)" class="sl-btn" style="padding:5px 10px; font-size:0.78rem;">🔍 110% (6:8)</button>
                    <button type="button" onclick="window.cycleSlConstitution1Theme && window.cycleSlConstitution1Theme()" class="sl-btn" style="padding:5px 10px; font-size:0.78rem; background:rgba(56,189,248,0.2); border-color:#38bdf8;">🌓 تبديل السمة حياً</button>
                </div>
                <button type="button" onclick="window.runSlConstitution1FullAudit && window.runSlConstitution1FullAudit(true)" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none;">⚡ فحص وتأكيد الأوامر السبعة (7/7 — 100%)</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         CONSTITUTION 2 (2 OF 14) QUICK-ADD PARTY & ITEM CHILD MODAL (+):
         إضافة سريعة لمورد أو عميل أو صنف جديد من داخل الشاشة دون ضياع البيانات
         ═══════════════════════════════════════════════════════════════════ -->
    <div id="sl-quick-entity-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div class="sl-glass-card" style="width:100%; max-width:520px; padding:20px; border:2.5px solid #34d399;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:2.2px solid rgba(52,211,153,0.35); padding-bottom:10px;">
                <h3 id="sl-quick-entity-title" style="margin:0; font-size:1.05rem; font-weight:900; color:#34d399;">➕ إضافة سريعة فورية (دون مغادرة البوابة)</h3>
                <button type="button" onclick="document.getElementById('sl-quick-entity-modal').style.display='none'" class="sl-btn" style="padding:4px 10px;">✕</button>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:12px;">
                <button type="button" id="sl-qe-tab-party" onclick="window.switchSlQuickEntityTab('party')" class="sl-btn" style="flex:1; background:rgba(16,185,129,0.22); border-color:#34d399;">👤 مورد / عميل جديد</button>
                <button type="button" id="sl-qe-tab-item" onclick="window.switchSlQuickEntityTab('item')" class="sl-btn" style="flex:1;">📦 صنف جديد للمستند</button>
            </div>
            <div id="sl-qe-body-party" style="display:flex; flex-direction:column; gap:10px;">
                <div>
                    <label style="font-size:0.8rem; font-weight:900; display:block; margin-bottom:4px;">👤 اسم المورد / العميل:</label>
                    <input type="text" id="sl-qe-party-name" class="sl-input" placeholder="اكتب الاسم الكامل..." style="width:100%;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:900; display:block; margin-bottom:4px;">📱 رقم الهاتف / واتساب:</label>
                        <input type="text" id="sl-qe-party-phone" class="sl-input" placeholder="09... / 249..." style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:900; display:block; margin-bottom:4px;">📍 المدينة / العنوان:</label>
                        <input type="text" id="sl-qe-party-city" class="sl-input" placeholder="المدينة..." style="width:100%;">
                    </div>
                </div>
                <button type="button" onclick="window.saveSlQuickPartyEntity()" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; padding:10px;">💾 حفظ وربط بالمستند فوراً</button>
            </div>
            <div id="sl-qe-body-item" style="display:none; flex-direction:column; gap:10px;">
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:8px;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:900; display:block; margin-bottom:4px;">📦 اسم الصنف الجديد:</label>
                        <input type="text" id="sl-qe-item-name" class="sl-input" placeholder="اسم الصنف..." style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:900; display:block; margin-bottom:4px;">🔢 الكود / الباركود:</label>
                        <input type="text" id="sl-qe-item-code" class="sl-input" placeholder="تلقائي..." style="width:100%;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:900; display:block; margin-bottom:4px;">📦 الوحدة الكبرى:</label>
                        <input type="text" id="sl-qe-item-unit" class="sl-input" value="كرتون" style="width:100%; text-align:center;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:900; display:block; margin-bottom:4px;">⚖️ المعامل (العبوة):</label>
                        <input type="number" id="sl-qe-item-factor" class="sl-input" value="1" min="1" step="any" style="width:100%; text-align:center;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:900; display:block; margin-bottom:4px;">⚡ الكمية الواردة/الصادرة:</label>
                        <input type="number" id="sl-qe-item-qty" class="sl-input" value="10" min="0.01" step="any" style="width:100%; text-align:center;">
                    </div>
                </div>
                <button type="button" onclick="window.saveSlQuickItemEntity()" class="sl-btn" style="background:linear-gradient(135deg,#0284c7,#2563eb); color:#fff; border:none; padding:10px;">💾 حفظ الصنف وإدراجه في جدول الفحص فوراً</button>
            </div>
        </div>
    </div>

    <script>
    // 🎡 ضمان تحريك الشاشة بالكامل بالرولر + محرك التناسب الهندسي الثابت للأبعاد (6:8 Aspect-Ratio Lock — Ctrl + Wheel)
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

        // Ensure wheel events anywhere on the main screen always scroll the page freely, while Ctrl+Wheel triggers 6:8 proportional scaling
        window.addEventListener('wheel', function (e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                var delta = e.deltaY < 0 ? 0.05 : -0.05;
                if (typeof window.stepSlProportionalZoom === 'function') {
                    window.stepSlProportionalZoom(delta);
                }
                return;
            }
            if (!e.deltaY) return;
            const openModal = e.target.closest && e.target.closest('#msg-universal-modal.open, [id^="sl-"][id$="-modal"][style*="flex"], #msg-pwa-app-install-modal[style*="flex"], #msg-full-offline-install-modal[style*="flex"], #msg-universal-7themes-modal[style*="flex"]');
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
        }, { passive: false });
    })();
    </script>
    <script src="js/magaies-offline-sync.js?v=20261014"></script>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/print-engine.js"></script>
    <script defer src="js/magaies-smart-guide.js?v=<?php echo @filemtime(__DIR__ . '/js/magaies-smart-guide.js') ?: '20261030_v35'; ?>"></script>
    <script defer src="js/smart-logistics.js?v=<?php echo @filemtime(__DIR__ . '/js/smart-logistics.js') ?: '20261030_v10'; ?>"></script>
    <script src="js/global_background_media.js?v=20261022_v23" defer></script>
    <script src="js/magaiees_back.js?v=20261022_v23" defer></script>
</body>
</html>
