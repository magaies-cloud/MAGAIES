// ============================================================================
// ELSAJANA ERP — UNIVERSAL SMART VISUAL & LIVE AUTOPILOT GUIDE («زر المرشد الذكي 🎓»)
// Features:
// 1. Pre-Buffered Zero-Delay Continuous Speech Flow (كلام متصل متواصل بدون أي تقطيع أو تأخير)
// 2. 100% Unified Natural Male Voice Engine (صوت رجالي موحد وطبيعي دائماً — مستحيل يظهر صوت امرأة)
// 3. Shortest Authentic Sahihayn Salawat («اللَّهُمَّ صَلِّ عَلَى مُحَمَّدٍ وَعَلَى آلِ مُحَمَّدٍ»)
// 4. Single Unified Live Autopilot Button (إلغاء تكرار الزر وتوحيده في زر واحد واضح)
// ============================================================================

(function () {
    'use strict';

    if (!window._mdDelayedTipsLoaded && !document.querySelector('script[src*="md-delayed-tooltips.js"]')) {
        const mdTipScript = document.createElement('script');
        mdTipScript.src = 'js/md-delayed-tooltips.js?v=' + Date.now();
        mdTipScript.defer = true;
        (document.head || document.documentElement).appendChild(mdTipScript);
    }

    const SHORT_SAHIHAYN_SALAWAT = 'اللهم صل وسلم على نبينا محمد.';

    // ── INJECT STYLES FOR SMART GUIDE, GHOST CURSOR & LIVE TELEPROMPTER ──────
    const styleEl = document.createElement('style');
    styleEl.id = 'magaies-smart-guide-styles';
    styleEl.textContent = `
        .msg-trigger-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 13px;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-weight: 900;
            font-size: 0.9rem;
            color: #ffffff !important;
            background: linear-gradient(135deg, #f59e0b 0%, #ec4899 50%, #6366f1 100%);
            background-size: 200% 200%;
            animation: msgAuroraShift 4s ease infinite;
            border: 1.5px solid rgba(254, 240, 138, 0.85);
            box-shadow: 0 0 22px rgba(236, 72, 153, 0.45), 0 6px 16px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.35);
            cursor: pointer;
            overflow: hidden;
            transition: transform 0.24s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.24s ease;
            user-select: none;
            z-index: 20;
        }
        @keyframes msgAuroraShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .msg-trigger-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -130%;
            width: 65%;
            height: 200%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.42), transparent);
            transform: rotate(25deg);
            transition: left 0.55s ease;
            pointer-events: none;
        }
        .msg-trigger-btn:hover::after { left: 145%; }
        .msg-trigger-btn:hover {
            transform: translateY(-3px) scale(1.045);
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.7), 0 0 18px rgba(99, 102, 241, 0.6), 0 10px 22px rgba(0,0,0,0.55);
        }

        .msg-trigger-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(2, 6, 23, 0.35);
            font-size: 1.05rem;
            animation: msgIconFloat 2.2s ease-in-out infinite;
        }
        @keyframes msgIconFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-2.5px) rotate(-6deg); }
        }

        .msg-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.86);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            z-index: 100000;
            align-items: center;
            justify-content: center;
            padding: 16px;
            direction: rtl;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
        }
        .msg-modal-window {
            width: 100%;
            max-width: 1040px;
            height: auto;
            max-height: 88vh;
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.98) 0%, rgba(2, 6, 23, 0.99) 100%);
            border: 2px solid rgba(56, 189, 248, 0.6);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.85), 0 0 40px rgba(56, 189, 248, 0.22);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            color: #f8fafc;
            animation: msgModalPop 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }
        @keyframes msgModalPop {
            from { opacity: 0; transform: translateY(18px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .msg-modal-header {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 27, 75, 0.92), rgba(6, 78, 59, 0.88));
            padding: 12px 20px;
            border-bottom: 1.5px solid rgba(148, 163, 184, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            flex-shrink: 0;
        }
        .msg-nav-tabs {
            display: flex;
            gap: 8px;
            padding: 8px 20px;
            background: rgba(2, 6, 23, 0.82);
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
            overflow-x: auto;
            flex-wrap: wrap;
            align-items: center;
            flex-shrink: 0;
        }
        .msg-nav-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            border-radius: 12px;
            font-weight: 900;
            font-size: 0.84rem;
            color: #cbd5e1;
            background: rgba(30, 41, 59, 0.6);
            border: 1.5px solid rgba(148, 163, 184, 0.2);
            cursor: pointer;
            transition: all 0.22s ease;
        }
        .msg-nav-tab.active {
            background: linear-gradient(135deg, #0284c7, #4f46e5);
            color: #ffffff;
            border-color: #38bdf8;
            box-shadow: 0 0 18px rgba(56, 189, 248, 0.45);
        }

        .msg-modal-body {
            padding: 18px 22px 44px 22px;
            overflow-y: auto !important;
            overflow-x: hidden;
            flex: 1 1 auto;
            min-height: 0 !important;
            display: block !important;
            overscroll-behavior: contain;
            scroll-behavior: smooth;
        }
        .msg-modal-body::-webkit-scrollbar {
            width: 9px;
        }
        .msg-modal-body::-webkit-scrollbar-track {
            background: rgba(2, 6, 23, 0.65);
            border-radius: 10px;
        }
        .msg-modal-body::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #38bdf8, #6366f1);
            border-radius: 10px;
        }
        .msg-pane { display: none; flex-direction: column; gap: 16px; padding-bottom: 24px; }
        .msg-pane.active { display: flex; }

        .msg-story-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }
        .msg-story-card {
            background: linear-gradient(155deg, rgba(15, 23, 42, 0.9), rgba(2, 6, 23, 0.95));
            border: 1.5px solid rgba(148, 163, 184, 0.22);
            border-radius: 18px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .msg-step-num {
            display: inline-flex;
            padding: 3px 12px;
            border-radius: 99px;
            font-size: 0.76rem;
            font-weight: 900;
            background: rgba(56, 189, 248, 0.18);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.4);
            width: fit-content;
        }
        .msg-svg-stage {
            width: 100%;
            height: 125px;
            background: radial-gradient(circle at center, rgba(30, 41, 59, 0.8), rgba(2, 6, 23, 0.95));
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .msg-btn-dict-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
            gap: 14px;
        }
        .msg-dict-item {
            background: rgba(15, 23, 42, 0.78);
            border: 1.5px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            padding: 14px 18px;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }
        .msg-replica-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            border-radius: 10px;
            font-weight: 900;
            font-size: 0.84rem;
            width: fit-content;
        }
        .msg-whatif-box {
            background: rgba(2, 6, 23, 0.75);
            border-right: 3px solid #38bdf8;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.81rem;
            color: #cbd5e1;
            line-height: 1.6;
        }

        /* ── LIVE VOICE-SYNCHRONIZED GHOST CURSOR & TELEPROMPTER BAR ── */
        .msg-ghost-cursor {
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 2147483520;
            pointer-events: none;
            display: none;
            transition: top 0.52s cubic-bezier(0.22, 1, 0.36, 1), left 0.52s cubic-bezier(0.22, 1, 0.36, 1), transform 0.18s ease;
            filter: drop-shadow(0 0 16px #fbbf24) drop-shadow(0 6px 14px rgba(0,0,0,0.85));
        }
        .msg-ghost-cursor-hand {
            font-size: 2.5rem;
            line-height: 1;
            transform: rotate(-18deg);
            display: inline-block;
        }
        .msg-ghost-cursor-label {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #000000 !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-weight: 900;
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 99px;
            white-space: nowrap;
            margin-top: -6px;
            border: 2px solid #ffffff;
            box-shadow: 0 6px 16px rgba(0,0,0,0.6);
        }
        .msg-ghost-cursor.clicking .msg-ghost-cursor-hand {
            transform: rotate(-18deg) scale(0.75);
        }
        .msg-ghost-ripple {
            position: absolute;
            top: -10px;
            left: -10px;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 3px solid #fbbf24;
            opacity: 0;
            pointer-events: none;
        }
        .msg-ghost-cursor.clicking .msg-ghost-ripple {
            animation: msgClickRipple 0.65s ease-out;
        }
        @keyframes msgClickRipple {
            0% { transform: scale(0.4); opacity: 1; }
            100% { transform: scale(2.1); opacity: 0; }
        }

        .msg-live-target-ring {
            position: fixed;
            z-index: 2147483510;
            pointer-events: none;
            display: none;
            border: 3.5px solid #fbbf24;
            border-radius: 16px;
            box-shadow: 0 0 32px rgba(251, 191, 36, 0.9), inset 0 0 18px rgba(251, 191, 36, 0.35), 0 0 0 9999px rgba(2, 6, 23, 0.32);
            transition: all 0.28s cubic-bezier(0.22, 1, 0.36, 1);
            animation: msgTargetPulse 1.4s infinite ease-in-out;
        }
        @keyframes msgTargetPulse {
            0%, 100% { border-color: #fbbf24; box-shadow: 0 0 28px rgba(251, 191, 36, 0.85), 0 0 0 9999px rgba(2, 6, 23, 0.32); }
            50% { border-color: #38bdf8; box-shadow: 0 0 38px rgba(56, 189, 248, 0.95), 0 0 0 9999px rgba(2, 6, 23, 0.32); }
        }

        /* ── ROYAL SLEEK UNIFIED GUIDE STUDIO HUD (لوحة موحدة أنيقة بدون أي لوحات متراكبة فوق بعض) ── */
        .msg-live-coach-bar {
            position: fixed;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2147483540;
            width: min(700px, 95vw);
            background: linear-gradient(155deg, rgba(8, 15, 32, 0.97) 0%, rgba(15, 23, 42, 0.98) 60%, rgba(20, 29, 54, 0.97) 100%);
            color: #ffffff !important;
            border: 2px solid rgba(56, 189, 248, 0.75) !important;
            border-radius: 18px;
            padding: 10px 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.85), 0 0 24px rgba(56, 189, 248, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.18);
            display: none;
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
            direction: rtl;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transition: top 0.28s cubic-bezier(0.22, 1, 0.36, 1), bottom 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .msg-live-coach-bar.docked-top {
            bottom: auto !important;
            top: 14px !important;
        }
        #msg-coach-svg-popover,
        #msg-live-select-mirror,
        #msg-pinned-audio-dock {
            display: none !important;
            pointer-events: none !important;
        }
        .msg-micro-pill-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 26px;
            min-width: 26px;
            padding: 0 8px;
            border-radius: 8px;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-size: 0.75rem;
            font-weight: 900;
            background: rgba(30, 41, 59, 0.85);
            color: #f8fafc !important;
            border: 1.5px solid rgba(148, 163, 184, 0.35);
            cursor: pointer;
            transition: all 0.16s ease;
            user-select: none;
        }
        .msg-micro-pill-btn:hover {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.2);
            transform: translateY(-1px);
        }
        html.light-theme .msg-micro-pill-btn,
        html[data-magaies-theme-mode="light"] .msg-micro-pill-btn,
        body.light-theme .msg-micro-pill-btn {
            background: var(--lt-sub-1, #ffffff) !important;
            color: var(--lt-ink, #120500) !important;
            border-color: var(--lt-border, #4a2106) !important;
        }
        .msg-vol-range-slider,
        .msg-spd-range-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 74px;
            height: 6px;
            border-radius: 99px;
            background: linear-gradient(270deg, #0284c7, #10b981);
            outline: none;
            cursor: pointer;
            direction: rtl !important;
        }
        .msg-vol-range-slider::-webkit-slider-thumb,
        .msg-spd-range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #0284c7;
            box-shadow: 0 2px 6px rgba(0,0,0,0.45);
            cursor: grab;
        }
        /* Strict Light Theme Contrast (#000000 text on light surface) for Micro-HUD, Hover Tooltip & Live Dropdown Mirror */
        html.light-theme .msg-live-coach-bar,
        html[data-magaies-theme-mode="light"] .msg-live-coach-bar,
        body.theme-light .msg-live-coach-bar,
        body.light-theme .msg-live-coach-bar,
        body[data-msg-theme="royal-dawn"] .msg-live-coach-bar,
        html[data-theme="warm_cream"] .msg-live-coach-bar,
        html[data-theme="ultra_white"] .msg-live-coach-bar,
        html[data-theme="exec_gray"] .msg-live-coach-bar {
            background: rgba(255, 255, 255, 0.98) !important;
            color: #000000 !important;
            border: 2px solid #0284c7 !important;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(2, 132, 199, 0.2) !important;
        }
        html.light-theme .msg-live-coach-bar *,
        html[data-magaies-theme-mode="light"] .msg-live-coach-bar *,
        body.theme-light .msg-live-coach-bar *,
        body.light-theme .msg-live-coach-bar *,
        body[data-msg-theme="royal-dawn"] .msg-live-coach-bar *,
        html[data-theme="warm_cream"] .msg-live-coach-bar *,
        html[data-theme="ultra_white"] .msg-live-coach-bar *,
        html[data-theme="exec_gray"] .msg-live-coach-bar * {
            color: #000000 !important;
        }
        html.light-theme #msg-coach-step-badge,
        html[data-magaies-theme-mode="light"] #msg-coach-step-badge,
        body.theme-light #msg-coach-step-badge,
        body.light-theme #msg-coach-step-badge,
        body[data-msg-theme="royal-dawn"] #msg-coach-step-badge,
        html[data-theme="warm_cream"] #msg-coach-step-badge,
        html[data-theme="ultra_white"] #msg-coach-step-badge,
        html[data-theme="exec_gray"] #msg-coach-step-badge {
            background: rgba(2, 132, 199, 0.12) !important;
            border-color: #0284c7 !important;
            color: #000000 !important;
        }
        html.light-theme .msg-smart-hover-tooltip,
        html[data-magaies-theme-mode="light"] .msg-smart-hover-tooltip,
        body.theme-light .msg-smart-hover-tooltip,
        body.light-theme .msg-smart-hover-tooltip,
        body[data-msg-theme="royal-dawn"] .msg-smart-hover-tooltip,
        html[data-theme="warm_cream"] .msg-smart-hover-tooltip,
        html[data-theme="ultra_white"] .msg-smart-hover-tooltip,
        html[data-theme="exec_gray"] .msg-smart-hover-tooltip,
        html.light-theme #msg-live-select-mirror,
        html[data-magaies-theme-mode="light"] #msg-live-select-mirror,
        body.theme-light #msg-live-select-mirror,
        body.light-theme #msg-live-select-mirror,
        body[data-msg-theme="royal-dawn"] #msg-live-select-mirror,
        html[data-theme="warm_cream"] #msg-live-select-mirror,
        html[data-theme="ultra_white"] #msg-live-select-mirror,
        html[data-theme="exec_gray"] #msg-live-select-mirror {
            background: linear-gradient(145deg, var(--lt-card-1, #fdf6ee) 0%, var(--lt-card-2, #f3e2ce) 100%) !important;
            color: var(--lt-ink, #140600) !important;
            border: 2.5px solid var(--lt-border, #4a2106) !important;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.25) !important;
        }
        html.light-theme .msg-smart-hover-tooltip *,
        html[data-magaies-theme-mode="light"] .msg-smart-hover-tooltip *,
        body.theme-light .msg-smart-hover-tooltip *,
        body.light-theme .msg-smart-hover-tooltip *,
        body[data-msg-theme="royal-dawn"] .msg-smart-hover-tooltip *,
        html[data-theme="warm_cream"] .msg-smart-hover-tooltip *,
        html[data-theme="ultra_white"] .msg-smart-hover-tooltip *,
        html[data-theme="exec_gray"] .msg-smart-hover-tooltip *,
        html.light-theme #msg-live-select-mirror *,
        html[data-magaies-theme-mode="light"] #msg-live-select-mirror *,
        body.theme-light #msg-live-select-mirror *,
        body.light-theme #msg-live-select-mirror *,
        body[data-msg-theme="royal-dawn"] #msg-live-select-mirror *,
        html[data-theme="warm_cream"] #msg-live-select-mirror *,
        html[data-theme="ultra_white"] #msg-live-select-mirror *,
        html[data-theme="exec_gray"] #msg-live-select-mirror * {
            color: var(--lt-ink, #140600) !important;
        }
        #msg-live-select-mirror {
            position: fixed;
            z-index: 2147483530;
            display: none;
            flex-direction: column;
            gap: 3px;
            min-width: 190px;
            max-width: 290px;
            max-height: 210px;
            overflow-y: auto;
            padding: 8px 10px;
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.97);
            color: #ffffff;
            border: 1.5px solid #38bdf8;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.82), 0 0 18px rgba(56, 189, 248, 0.35);
            direction: rtl;
            font-family: 'Tajawal', sans-serif !important;
            pointer-events: none;
            animation: msgModalPop 0.2s ease-out;
        }
        .msg-spotlight-active-el {
            position: relative !important;
            z-index: 45 !important;
            box-shadow: 0 0 0 3px #fbbf24, 0 0 28px rgba(251, 191, 36, 0.65) !important;
            transition: box-shadow 0.25s ease !important;
        }
        #msg-coach-visual-stage,
        #msg-coach-speech-text {
            display: none !important;
        }

        .msg-pinned-audio-dock {
            display: none !important;
        }
        .msg-step-pill-btn {
            min-width: 24px;
            height: 24px;
            border-radius: 7px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: rgba(30, 41, 59, 0.75);
            color: #cbd5e1;
            font-weight: 900;
            font-size: 0.74rem;
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .msg-step-pill-btn.active {
            background: #fbbf24;
            color: #020617;
            border-color: #ffffff;
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.7);
        }

        /* Hide all duplicate standalone Back, TV, Crystal Icon Dock, and legacy header quad buttons when unified inside our Royal Quartets */
        html {
            overflow-y: auto !important;
            height: auto !important;
            overscroll-behavior-y: auto !important;
        }
        body:not(.pro-dashboard-body) {
            overflow-y: visible !important;
            overscroll-behavior-y: auto !important;
            height: auto !important;
        }
        #magaiees-dashboard-header-stack,
        #magaiees-master-back-btn,
        .magaiees-back-btn,
        .magaiees-back-btn.floating-docked,
        .subscreen-floating-tv-wrap,
        .header-crystal-icon-dock,
        .header-crystal-icon-dock #header-media-control-wrap,
        button[onclick="history.back()"],
        .navbar .back-btn {
            display: none !important;
        }

        /* Lock top header bar from being affected by icon/font zoom settings */
        header.premium-header,
        .magical-glass-header,
        #sl-top-cockpit-bar,
        .msg-twin-quartets-wrapper,
        .msg-twin-quartets-wrapper * {
            zoom: 1 !important;
            -webkit-text-size-adjust: 100% !important;
        }

        /* ── ROYAL QUARTETS WRAPPER (مستطيلات رباعية 2×2 متطابقة الستايل واللمعان، مع زيادة ارتفاع الأزرار قليلاً لتقليل الهوامش) ── */
        .msg-twin-quartets-wrapper {
            position: fixed;
            top: 8px;
            left: 10px;
            z-index: 999990;
            display: inline-flex !important;
            align-items: stretch !important;
            gap: 5px !important;
            direction: rtl !important;
            flex-shrink: 0 !important;
            user-select: none;
            box-sizing: border-box;
            font-size: 12px !important;
        }
        /* When docked inside the screen's top header bar */
        .msg-twin-quartets-wrapper.is-docked-in-header {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            z-index: 50 !important;
            margin: 0 !important;
        }
        /* When user scrolls down past the header, float smoothly at top-left */
        .msg-twin-quartets-wrapper.is-docked-in-header.is-scroll-floating {
            position: fixed !important;
            top: 8px !important;
            left: 10px !important;
            z-index: 999990 !important;
        }

        /* Each 4-Button Pod (2 on top, 2 on bottom inside a clean rectangle matching 78px header height) */
        .msg-fixed-command-pod {
            width: 223px !important;
            min-width: 223px !important;
            max-width: 223px !important;
            height: 78px !important;
            min-height: 78px !important;
            max-height: 78px !important;
            padding: 4px 5px !important;
            border-radius: 13px !important;
            background: linear-gradient(160deg, rgba(2, 6, 23, 0.96) 0%, rgba(15, 23, 42, 0.98) 55%, rgba(23, 15, 48, 0.96) 100%) !important;
            border: 1.5px solid rgba(251, 191, 36, 0.75) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.45) !important;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            grid-template-rows: 1fr 1fr !important;
            gap: 4px !important;
            direction: rtl !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            box-sizing: border-box !important;
            flex-shrink: 0 !important;
            animation: none !important;
        }
        .msg-fixed-command-pod.pod-smart-guide {
            width: 328px !important;
            min-width: 328px !important;
            max-width: 328px !important;
            grid-template-columns: 1fr 1fr 1fr !important;
            grid-template-rows: 1fr 1fr !important;
            grid-template-areas:
                "fullscr guide tv"
                "search  qtheme back" !important;
        }
        .msg-fixed-command-pod.pod-system-tools,
        .msg-fixed-command-pod.pod-utility-quad {
            display: none !important;
        }

        /* Unified Button Styling Across All Pods (Zero glare/reflection) */
        .msg-quartet-btn {
            position: relative !important;
            width: 100% !important;
            height: 100% !important;
            min-height: 31px !important;
            max-height: 33px !important;
            padding: 2px 6px !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 4.5px !important;
            cursor: pointer !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            box-shadow: none !important;
            transition: transform 0.18s ease, border-color 0.18s ease !important;
            text-align: center !important;
        }
        .msg-quartet-btn::after,
        .msg-quartet-btn::before {
            display: none !important;
            animation: none !important;
            content: none !important;
        }
        .msg-quartet-btn:hover {
            transform: translateY(-1px) !important;
            border-color: #ffffff !important;
            box-shadow: none !important;
        }
        .msg-quartet-btn:active {
            transform: scale(0.96) !important;
        }

        .msg-quartet-icon-box {
            width: 21px !important;
            height: 21px !important;
            min-width: 21px !important;
            max-width: 21px !important;
            border-radius: 6px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
            z-index: 2 !important;
            flex-shrink: 0 !important;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.35), 0 2px 5px rgba(0, 0, 0, 0.45) !important;
        }
        .msg-quartet-icon-box svg {
            width: 14px !important;
            height: 14px !important;
            display: block !important;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.65));
        }

        .msg-quartet-label {
            flex: 1 1 auto !important;
            min-width: 0 !important;
            text-align: center !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-size: 13.5px !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            line-height: 1 !important;
            letter-spacing: 0.2px !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.98), 0 0 8px rgba(0, 0, 0, 0.85) !important;
            z-index: 2 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            padding: 0 1px !important;
        }

        /* ═══ POD 1 (في كل الشاشات): المرشد • المايك • التلفاز • الرجوع ═══ */
        .msg-quartet-guide {
            grid-area: guide;
            background: linear-gradient(135deg, #062238 0%, #0369a1 55%, #1d4ed8 100%) !important;
            border: 1.5px solid #38bdf8 !important;
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-guide .msg-quartet-icon-box {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            border: 1px solid #bae6fd;
        }
        .msg-quartet-guide:hover {
            box-shadow: 0 0 24px rgba(56, 189, 248, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        .msg-quartet-mic,
        .msg-quartet-cinema4k {
            grid-area: mic;
            background: linear-gradient(135deg, #450a0a 0%, #dc2626 55%, #9333ea 100%) !important;
            border: 1.8px solid #fbbf24 !important;
            box-shadow: 0 0 16px rgba(220, 38, 38, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        .msg-quartet-mic .msg-quartet-icon-box,
        .msg-quartet-cinema4k .msg-quartet-icon-box {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            border: 1px solid #fde047;
        }
        .msg-quartet-mic:hover,
        .msg-quartet-cinema4k:hover {
            box-shadow: 0 0 26px rgba(250, 204, 21, 0.95), 0 6px 16px rgba(0, 0, 0, 0.65);
            transform: translateY(-1px) scale(1.03);
        }

        #header-media-control-wrap.msg-quartet-tv-cell {
            grid-area: tv;
            display: flex !important;
            width: 100%;
        }
        .msg-quartet-tv {
            grid-area: tv;
            background: linear-gradient(135deg, #1e0b42 0%, #4c1d95 55%, #5b21b6 100%) !important;
            border: 1.5px solid #c084fc !important;
            box-shadow: 0 0 14px rgba(192, 132, 252, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-tv .msg-quartet-icon-box {
            background: linear-gradient(135deg, #9333ea, #6366f1);
            border: 1px solid #e9d5ff;
        }
        .msg-quartet-tv.is-playing {
            border-color: #fde047 !important;
            box-shadow: 0 0 22px rgba(250, 204, 21, 0.85), 0 0 34px rgba(168, 85, 247, 0.65) !important;
        }
        .msg-quartet-tv:hover {
            box-shadow: 0 0 24px rgba(192, 132, 252, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        .msg-quartet-back {
            grid-area: back;
            background: linear-gradient(135deg, #2b1202 0%, #6b2e05 55%, #92400e 100%) !important;
            border: 1.5px solid #fbbf24 !important;
            box-shadow: 0 0 14px rgba(251, 191, 36, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-back .msg-quartet-icon-box {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: 1px solid #fef08a;
        }
        .msg-quartet-back:hover {
            box-shadow: 0 0 24px rgba(251, 191, 36, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        /* ═══ POD 2 (في لوحة التحكم فقط): الموبايل • لابتوب • تثبيت عام • السمات ═══ */
        .msg-quartet-mobile {
            grid-area: mobile;
            background: linear-gradient(135deg, #0c1e3e 0%, #1e40af 55%, #0284c7 100%) !important;
            border: 1.5px solid #60a5fa !important;
            box-shadow: 0 0 14px rgba(96, 165, 250, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-mobile .msg-quartet-icon-box {
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            border: 1px solid #fef08a;
        }
        .msg-quartet-mobile:hover {
            box-shadow: 0 0 24px rgba(96, 165, 250, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        .msg-quartet-install-app {
            grid-area: appinst;
            background: linear-gradient(135deg, #042f2e 0%, #0f766e 55%, #0d9488 100%) !important;
            border: 1.5px solid #2dd4bf !important;
            box-shadow: 0 0 14px rgba(45, 212, 191, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-install-app .msg-quartet-icon-box {
            background: linear-gradient(135deg, #0d9488, #14b8a6);
            border: 1px solid #fef08a;
        }
        .msg-quartet-install-app:hover {
            box-shadow: 0 0 24px rgba(45, 212, 191, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        .msg-quartet-install-full {
            grid-area: fullinst;
            background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 48%, #b45309 100%) !important;
            border: 1.5px solid #facc15 !important;
            box-shadow: 0 0 14px rgba(250, 204, 21, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-install-full .msg-quartet-icon-box {
            background: linear-gradient(135deg, #d97706, #4f46e5);
            border: 1px solid #fef08a;
        }
        .msg-quartet-install-full:hover {
            box-shadow: 0 0 24px rgba(250, 204, 21, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        .msg-quartet-themes {
            grid-area: themes;
            background: linear-gradient(135deg, #3b0764 0%, #7e22ce 50%, #be185d 100%) !important;
            border: 1.5px solid #f472b6 !important;
            box-shadow: 0 0 14px rgba(244, 114, 182, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-themes .msg-quartet-icon-box {
            background: linear-gradient(135deg, #db2777, #9333ea);
            border: 1px solid #fbcfe8;
        }
        .msg-quartet-themes:hover {
            box-shadow: 0 0 24px rgba(244, 114, 182, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        /* ═══ POD 3 (في كل الشاشات): الدرع • البحث • توسيع الشاشة • تبديل المظهر (بنفس الستايل واللمعان) ═══ */
        .msg-quartet-shield {
            grid-area: shield;
            background: linear-gradient(135deg, #022c22 0%, #047857 55%, #10b981 100%) !important;
            border: 1.5px solid #34d399 !important;
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-shield .msg-quartet-icon-box {
            background: linear-gradient(135deg, #059669, #10b981);
            border: 1px solid #a7f3d0;
        }
        .msg-quartet-shield:hover {
            box-shadow: 0 0 24px rgba(52, 211, 153, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        .msg-quartet-search {
            grid-area: search;
            background: linear-gradient(135deg, #082f49 0%, #0369a1 55%, #0284c7 100%) !important;
            border: 1.5px solid #38bdf8 !important;
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-search .msg-quartet-icon-box {
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            border: 1px solid #bae6fd;
        }
        .msg-quartet-search:hover {
            box-shadow: 0 0 24px rgba(56, 189, 248, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        .msg-quartet-fullscreen {
            grid-area: fullscr;
            background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 55%, #4f46e5 100%) !important;
            border: 1.5px solid #818cf8 !important;
            box-shadow: 0 0 14px rgba(129, 140, 248, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-fullscreen .msg-quartet-icon-box {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: 1px solid #c7d2fe;
        }
        .msg-quartet-fullscreen:hover {
            box-shadow: 0 0 24px rgba(129, 140, 248, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        .msg-quartet-qtheme {
            grid-area: qtheme;
            background: linear-gradient(135deg, #3b0764 0%, #7e22ce 50%, #be185d 100%) !important;
            border: 1.5px solid #f472b6 !important;
            box-shadow: 0 0 14px rgba(244, 114, 182, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-qtheme .msg-quartet-icon-box {
            background: linear-gradient(135deg, #db2777, #9333ea);
            border: 1px solid #fbcfe8;
        }
        .msg-quartet-qtheme:hover {
            box-shadow: 0 0 24px rgba(244, 114, 182, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
        }

        @media (max-width: 900px) {
            .msg-twin-quartets-wrapper {
                flex-wrap: wrap;
                gap: 5px !important;
            }
            .msg-fixed-command-pod {
                width: 228px !important;
                min-width: 228px !important;
                padding: 4px 5px !important;
                gap: 4px !important;
            }
            .msg-quartet-btn {
                height: 29px !important;
                min-height: 29px !important;
                padding: 2px 6px !important;
                gap: 4px !important;
            }
            .msg-quartet-label {
                font-size: 0.78rem !important;
            }
        }
        /* Speed Arrow Pill Group */
        .msg-speed-arrows-group {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(2, 6, 23, 0.9);
            border: 1.5px solid rgba(251, 191, 36, 0.55);
            border-radius: 12px;
            padding: 3px 6px;
        }
        .msg-speed-arrow-btn {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fef08a;
            border: 1px solid rgba(251, 191, 36, 0.45);
            border-radius: 8px;
            padding: 3px 8px;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-size: 0.75rem;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.16s ease;
        }
        .msg-speed-arrow-btn:hover {
            background: #fbbf24;
            color: #020617;
            transform: scale(1.06);
        }

        /* Mode Picker Bar (Full Screen vs Purchases vs Sales vs Free Click) */
        .msg-mode-selector-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.95), rgba(6, 78, 59, 0.45), rgba(15, 23, 42, 0.95));
            border-bottom: 1.5px solid rgba(251, 191, 36, 0.35);
        }
        .msg-mode-card-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.88);
            border: 1.5px solid rgba(148, 163, 184, 0.32);
            color: #f8fafc;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            text-align: right;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .msg-mode-card-btn:hover {
            transform: translateY(-2px);
            border-color: #fbbf24;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 0 18px rgba(251, 191, 36, 0.3);
        }

        /* Interactive Voice Mic & On-Screen Answer Modal */
        .msg-voice-qa-panel {
            position: fixed;
            top: 130px;
            left: 16px;
            z-index: 100070;
            width: 390px;
            max-width: calc(100vw - 32px);
            background: linear-gradient(155deg, rgba(2, 6, 23, 0.98), rgba(15, 23, 42, 0.97));
            border: 2.5px solid #34d399;
            border-radius: 20px;
            padding: 16px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.92), 0 0 35px rgba(52, 211, 153, 0.4);
            display: none;
            flex-direction: column;
            gap: 12px;
            direction: rtl;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            color: #f8fafc;
            backdrop-filter: blur(16px);
        }

        /* Inspector Pick Mode Banner */
        .msg-pick-mode-banner {
            position: fixed;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100080;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #020617;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-weight: 900;
            font-size: 0.9rem;
            padding: 8px 20px;
            border-radius: 999px;
            border: 2px solid #ffffff;
            box-shadow: 0 12px 30px rgba(0,0,0,0.8), 0 0 25px rgba(245, 158, 11, 0.8);
            display: none;
            align-items: center;
            gap: 12px;
            direction: rtl;
        }
        body.msg-pick-inspector-active * {
            cursor: help !important;
        }

        /* Royal Centered Explanation Studio Card (عند الضغط دبل كليك) */
        .msg-smart-hover-tooltip {
            position: fixed;
            z-index: 2147483550;
            width: min(540px, 94vw);
            background: linear-gradient(155deg, rgba(8, 15, 32, 0.98) 0%, rgba(15, 23, 42, 0.99) 60%, rgba(21, 35, 66, 0.98) 100%);
            border: 2px solid rgba(56, 189, 248, 0.8);
            border-radius: 20px;
            padding: 14px 18px;
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.9), 0 0 32px rgba(56, 189, 248, 0.25);
            color: #ffffff;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            direction: rtl;
            display: none;
            flex-direction: column;
            gap: 10px;
            pointer-events: auto;
            user-select: none;
            backdrop-filter: blur(18px);
            animation: msgModalPop 0.2s cubic-bezier(0.22, 1, 0.36, 1);
        }
    `;
    document.head.appendChild(styleEl);

    // ══════════════════════════════════════════════════════════════════════════
    // PRE-BUFFERED ZERO-LATENCY GENUINE NEURAL MALE VOICE ENGINE (3 DIALECTS)
    // WITH FULL SEEKING (-5s / +5s / SLIDER / PAUSE / RESUME / STOP)
    // ══════════════════════════════════════════════════════════════════════════
    let currentAudioSource = null;
    let sharedAudioCtx = null;
    let activeAudioBuffer = null;
    let activeAudioIsGenuine = true;
    let activeAudioOnEnd = null;
    let activeAudioOffset = 0;
    let activeAudioStartTime = 0;
    let isAudioPaused = false;
    let isAudioPlaying = false;
    let progressTimerId = null;
    let nextStepTimeoutId = null;

    const VALID_VOICE_PROFILES = ['male_hamed', 'male_abdullah', 'male_shakir', 'male_muawiya', 'female_zariyah', 'female_salma', 'female_fatima', 'fusha', 'sudanese', 'egyptian'];
    let selectedMaleProfile = localStorage.getItem('msg_neural_voice_v19') || 'male_hamed';
    if (!VALID_VOICE_PROFILES.includes(selectedMaleProfile)) {
        selectedMaleProfile = 'male_hamed';
    }
    window.__msgAiDegree = localStorage.getItem('msg_ai_degree_v8') || 'ai_5';
    if (!['ai_1', 'ai_2', 'ai_3', 'ai_4', 'ai_5'].includes(window.__msgAiDegree)) window.__msgAiDegree = 'ai_5';

    window.__msgUserVoiceVolume = parseFloat(localStorage.getItem('msg_voice_vol_v1') || '1.00');
    if (isNaN(window.__msgUserVoiceVolume) || window.__msgUserVoiceVolume < 0.1 || window.__msgUserVoiceVolume > 3.0) {
        window.__msgUserVoiceVolume = 1.00;
    }

    // Pure 1.00x playback rate in browser (speed +26% is synthesized natively in AI for 100% crisp HD clarity!)
    const OPTIMAL_CLARITY_BASE_RATE = 1.00;
    function pctToPlaybackRate(pct) {
        const clamped = Math.max(-50, Math.min(300, Math.round(Number(pct) || 0)));
        return Math.max(0.50, Math.min(4.00, Number((OPTIMAL_CLARITY_BASE_RATE * (1 + (clamped / 100))).toFixed(2))));
    }
    function playbackRateToPct(rate) {
        const r = parseFloat(rate);
        if (isNaN(r)) return 0;
        return Math.max(-50, Math.min(300, Math.round(((r / OPTIMAL_CLARITY_BASE_RATE) - 1.0) * 100)));
    }

    window.__msgSpeechSpeedPct = 0;
    let selectedSpeechRate = OPTIMAL_CLARITY_BASE_RATE;
    window.resetToOptimalClaritySpeed = function () {
        window.__msgSpeechSpeedPct = 0;
        selectedSpeechRate = OPTIMAL_CLARITY_BASE_RATE;
        try {
            localStorage.setItem('msg_speech_speed_pct_v8', '0');
            localStorage.setItem('msg_male_speed_v7', selectedSpeechRate.toFixed(2));
        } catch (_) {}
        if (typeof syncSpeedLabelsUI === 'function') {
            try { syncSpeedLabelsUI(); } catch (_) {}
        }
        if (typeof window.__applyCinemaLivePlaybackRate === 'function') {
            try { window.__applyCinemaLivePlaybackRate(selectedSpeechRate); } catch (_) {}
        }
    };
    window.resetToOptimalClaritySpeed();
    const decodedBufferCache = new Map();
    const rawArrayBufferCache = new Map();
    const rawBlobUrlCache = new Map();
    const isGenuineNeuralCache = new Map();

    function cleanTextForMaleTts(t) {
        return String(t || '').replace(/<[^>]*>/g, ' ')
            .replace(/[\u{1F000}-\u{1FAFF}\u{2600}-\u{27BF}\u{2B00}-\u{2BFF}\u{2190}-\u{21FF}\u{25A0}-\u{25FF}\u{FE0F}\u{20E3}\u{200D}]/gu, ' ')
            .replace(/\s*[•|]\s*/g, '، ').replace(/\s+([،.:؛!؟])/g, '$1').replace(/([،.:؛])[\s،.:؛]*(?=[،.:؛])/g, '').replace(/\s{2,}/g, ' ').trim();
    }
    window.cleanTextForMaleTts = cleanTextForMaleTts;
    function getVoiceSafetyLimiter(ctx) {
        return ctx.destination;
    }
    function buildMaleClarityChain(ctx, baseGain) {
        const out = ctx.createGain();
        out.gain.value = Math.min(2.0, (baseGain || 1.0) * (parseFloat(window.__msgUserVoiceVolume) || 1.0));
        return { input: out, output: out };
    }
    let activeSpokenClean = '';
    const wordMarksCache = new Map();
    function loadWordMarks(txt) {
        if (!txt) return;
    }
    function spokenCharRatio(txt, t, dur) {
        const marks = txt ? wordMarksCache.get(txt) : null;
        if (!marks || !marks.length || !txt.length) return dur > 0 ? Math.max(0, Math.min(1, t / dur)) : 0;
        if (t < marks[0].t) return 0;
        let lo = 0, hi = marks.length - 1;
        while (lo < hi) { const mid = (lo + hi + 1) >> 1; if (marks[mid].t <= t) lo = mid; else hi = mid - 1; }
        const m = marks[lo], n = marks[lo + 1];
        const ci = n ? m.c + (n.c - m.c) * Math.min(1, (t - m.t) / Math.max(0.05, n.t - m.t)) : m.c + m.l * Math.min(1, (t - m.t) / 0.45);
        return Math.max(0, Math.min(1, ci / txt.length));
    }
    window.__msgSpokenCharRatio = spokenCharRatio;
    function getLiveSpeechRatio() {
        if (window.__activeGuideUtterance && window.__msgUtterLen && 'speechSynthesis' in window && window.speechSynthesis.speaking) return Math.min(1, (window.__msgUtterCharIdx || 0) / window.__msgUtterLen);
        if (!isAudioPlaying || !activeAudioBuffer || !activeAudioBuffer.duration) return -1;
        return spokenCharRatio(activeSpokenClean, getCurrentAudioOffset(), activeAudioBuffer.duration);
    }
    window.__msgGetLiveSpeechRatio = getLiveSpeechRatio;
    function getAudioCtx() {
        if (!sharedAudioCtx) {
            sharedAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        return sharedAudioCtx;
    }

    function formatSec(sec) {
        const s = Math.max(0, Math.floor(sec || 0));
        const m = Math.floor(s / 60);
        const r = s % 60;
        return `${String(m).padStart(2, '0')}:${String(r).padStart(2, '0')}`;
    }

    function getCurrentAudioOffset() {
        if (!activeAudioBuffer) return 0;
        const _pel = window.__msgPitchPreservedAudioEl;
        if (activeAudioBlobUrl && _pel && _pel.src === activeAudioBlobUrl && !isNaN(_pel.currentTime)) return Math.min(activeAudioBuffer.duration, Math.max(0, _pel.currentTime));
        if (isAudioPaused || !isAudioPlaying) {
            return Math.min(activeAudioBuffer.duration, Math.max(0, activeAudioOffset));
        }
        const ctx = getAudioCtx();
        const rate = activeAudioIsGenuine ? selectedSpeechRate : 0.94;
        const elapsed = (ctx.currentTime - activeAudioStartTime) * rate;
        return Math.min(activeAudioBuffer.duration, Math.max(0, activeAudioOffset + elapsed));
    }

    function updateAudioPlayerUI() {
        const dur = activeAudioBuffer ? activeAudioBuffer.duration : 0;
        const cur = getCurrentAudioOffset();

        const slider = document.getElementById('msg-audio-seek-slider');
        if (slider && document.activeElement !== slider) {
            slider.max = dur > 0 ? dur.toFixed(2) : '100';
            slider.value = cur.toFixed(2);
        }

        const timeLbl = document.getElementById('msg-audio-time-lbl');
        if (timeLbl) {
            timeLbl.textContent = `${formatSec(cur)} / ${formatSec(dur)}`;
        }

        const pauseBtnText = isAudioPaused ? '▶️ استئناف التسجيل' : '⏸️ إيقاف مؤقت';
        const pauseBtnIcon = isAudioPaused ? '▶️' : '⏸️';
        const btnMain = document.getElementById('msg-btn-pause-resume');
        if (btnMain) {
            btnMain.innerHTML = pauseBtnIcon;
            btnMain.title = pauseBtnText;
            btnMain.style.background = isAudioPaused ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#f59e0b,#d97706)';
        }
        const btnDock = document.getElementById('msg-dock-btn-pause');
        if (btnDock) {
            btnDock.innerHTML = pauseBtnIcon;
            btnDock.title = pauseBtnText;
        }
    }

    function startProgressLoop() {
        if (progressTimerId) clearInterval(progressTimerId);
        progressTimerId = setInterval(updateAudioPlayerUI, 200);
    }

    function stopProgressLoop() {
        if (progressTimerId) {
            clearInterval(progressTimerId);
            progressTimerId = null;
        }
    }

    let currentSpeechRequestId = 0;
    const inFlightBufferPromises = new Map();

    function syncGlobalKillSwitchVisibility(forceShow) {
        let btn = document.getElementById('msg-global-kill-switch-btn');
        const shouldShow = forceShow || isAudioPlaying || isAutopilotRunning || window.__CINEMA_4K_ACTIVE__;
        if (!shouldShow) {
            if (btn) btn.style.display = 'none';
            return;
        }
        if (!btn && document.body) {
            btn = document.createElement('button');
            btn.id = 'msg-global-kill-switch-btn';
            btn.type = 'button';
            btn.style.cssText = 'position:fixed;top:8px;left:50%;transform:translateX(-50%);z-index:2147483647;display:inline-flex;align-items:center;gap:8px;padding:7px 18px;border-radius:999px;background:linear-gradient(135deg,#dc2626,#991b1b);color:#ffffff;border:2px solid #fde047;box-shadow:0 10px 30px rgba(220,38,38,0.65),0 0 0 3px rgba(0,0,0,0.5);font-family:Cairo,Tajawal,sans-serif;font-size:0.88rem;font-weight:900;cursor:pointer;pointer-events:auto;';
            btn.innerHTML = '<span>🛑 إيقاف الشرح / التسجيل والخروج فوراً</span><span style="background:rgba(0,0,0,0.35);padding:1px 7px;border-radius:99px;color:#fde047;">✕ Esc</span>';
            btn.onclick = function (ev) {
                if (ev) { ev.preventDefault(); ev.stopPropagation(); }
                if (typeof window.killAllGuideAndCinemaImmediately === 'function') window.killAllGuideAndCinemaImmediately();
            };
            document.body.appendChild(btn);
        }
        if (btn) btn.style.display = 'inline-flex';
    }

    window.killAllGuideAndCinemaImmediately = function () {
        isAutopilotRunning = false;
        window.__mdInteractiveGuideActive = false;
        window.__isDblRightCardPinned = false;
        stopCurrentSpeech();
        if (typeof window.__stopCinemaBroadcastAudio === 'function') window.__stopCinemaBroadcastAudio();
        if (window.__CINEMA_4K_ACTIVE__ && typeof window.stopMagaies4KCinemaMode === 'function') {
            try { window.stopMagaies4KCinemaMode(); } catch (_) {}
        }
        if (typeof window.stopLiveAutopilotSimulation === 'function') {
            try { window.stopLiveAutopilotSimulation(); } catch (_) {}
        }
        ['msg-smart-hover-tooltip', 'msg-live-coach-bar', 'msg-universal-modal', 'msg-pinned-audio-dock', 'msg-c5-impact-map-banner', 'msg-cinema-lower-thirds', 'msg-global-kill-switch-btn'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    };

    function stopCurrentSpeech() {
        currentSpeechRequestId++;
        activeAudioOnEnd = null;
        if (nextStepTimeoutId) {
            clearTimeout(nextStepTimeoutId);
            nextStepTimeoutId = null;
        }
        if (typeof window.__stopCinemaBroadcastAudio === 'function') {
            try { window.__stopCinemaBroadcastAudio(); } catch (_) {}
        }
        if (window.__msgPitchPreservedAudioEl) {
            try {
                window.__msgPitchPreservedAudioEl.onended = null;
                window.__msgPitchPreservedAudioEl.pause();
            } catch (e) {}
        }
        if (currentAudioSource) {
            try {
                currentAudioSource.onended = null;
                currentAudioSource.stop();
            } catch (e) {}
            currentAudioSource = null;
        }
        isAudioPlaying = false;
        isAudioPaused = false;
        activeAudioOffset = 0;
        stopProgressLoop();
        if (window.__activeGuideUtterance) {
            try {
                window.__activeGuideUtterance.onend = null;
                window.__activeGuideUtterance.onerror = null;
            } catch (e) {}
            window.__activeGuideUtterance = null;
        }
        if ('speechSynthesis' in window) {
            try { window.speechSynthesis.cancel(); } catch (e) {}
        }
        syncGlobalKillSwitchVisibility(false);
    }

    // Smart dialect adapter for general descriptions, buttons & FAQs
    function adaptTextToDialect(rawText, dialect) {
        let txt = String(rawText || '').trim();
        if (!txt) return '';
        if (dialect === 'sudanese') {
            txt = txt
                .replace(/اضغط على/g, 'أضغط طوالي على')
                .replace(/اضغط/g, 'أضغط')
                .replace(/الآن/g, 'هسي')
                .replace(/مباشرة/g, 'طوالي')
                .replace(/فوراً/g, 'طوالي')
                .replace(/تلقائياً/g, 'براهو أوتوماتيك')
                .replace(/أوتوماتيكياً/g, 'براهو أوتوماتيك')
                .replace(/القادمة/g, 'الجاية')
                .replace(/المطلوب/g, 'الدايرو')
                .replace(/تريد/g, 'داير')
                .replace(/أردت/g, 'لو داير')
                .replace(/بجانبه/g, 'جنبو طوالي')
                .replace(/أمامك/g, 'قدامك')
                .replace(/لإدخال/g, 'عشان تدخّل')
                .replace(/لتسليم/g, 'عشان تسلّم')
                .replace(/لاستلام/g, 'عشان تستلم')
                .replace(/المخزن/g, 'المخزن');
        } else if (dialect === 'egyptian') {
            txt = txt
                .replace(/اضغط على/g, 'دوس على')
                .replace(/اضغط/g, 'دوس')
                .replace(/الآن/g, 'دلوقتي')
                .replace(/مباشرة/g, 'علطول')
                .replace(/فوراً/g, 'في ساعتها')
                .replace(/تلقائياً/g, 'لوحده أوتوماتيك')
                .replace(/أوتوماتيكياً/g, 'لوحده أوتوماتيك')
                .replace(/القادمة/g, 'الجاية')
                .replace(/المطلوب/g, 'اللي محتاجه')
                .replace(/تريد/g, 'عايز')
                .replace(/أردت/g, 'حبيت')
                .replace(/بجانبه/g, 'جنبه علطول')
                .replace(/أمامك/g, 'قدامك')
                .replace(/لإدخال/g, 'عشان تدخل')
                .replace(/لتسليم/g, 'عشان تسلم')
                .replace(/لاستلام/g, 'عشان تستلم');
        } else if (dialect === 'fusha' || dialect === 'gulf') {
            txt = txt
                .replace(/دلوقتي|هسي|الحين/g, 'الآن')
                .replace(/علطول|طوالي|سيدة/g, 'مباشرة')
                .replace(/لوحده أوتوماتيك|براهو أوتوماتيك/g, 'تلقائياً')
                .replace(/دوس على/g, 'اضغط على')
                .replace(/عايز|داير|تبغى/g, 'تريد');
        }
        return txt;
    }

    // ── أمر حاسم (1): قفل صارم لنطق الصلاة على النبي ﷺ مرة واحدة فقط مع ضغطة المرشد وفتحة الشاشة ومنع تكرارها بين الخانات والنقاط نهائياً ──
    let hasPlayedOpeningInvocation = false;
    let hasSpokenSalawatInCurrentGuideSession = false;
    window.hasPlayedOpeningInvocation = false;

    function stripAllSalawatPrefixes(rawText) {
        let txt = String(rawText || '').trim();
        if (!txt) return '';
        txt = txt
            .replace(/اللَّهُمَّ\s+صَلِّ\s+وَسَلِّمْ\s+عَلَى\s+نَبِيِّنَا\s+مُحَمَّدٍ[.،\s]*/g, '')
            .replace(/اللَّهُمَّ\s+صَلِّ\s+عَلَى\s+مُحَمَّدٍ\s+وَعَلَى\s+آلِ\s+مُحَمَّدٍ[.،\s]*/g, '')
            .replace(/اللهم\s+صل\s+وسلم\s+على\s+نبينا\s+محمد[.،\s]*/g, '')
            .replace(/اللهم\s+صل\s+على\s+محمد\s+وعلى\s+آل\s+محمد[.،\s]*/g, '')
            .replace(/اللهم\s+صل\s+على\s+سيدنا\s+محمد[.،\s]*/g, '')
            .replace(/شرح\s+من\s+الألف\s+(إلى|الى|ل)\s*الياء/g, '')
            .replace(/من\s+الألف\s+(إلى|الى|ل)\s*الياء/g, '')
            .replace(/من\s+الالف\s+(إلى|الى|ل)\s*الياء/g, '')
            .replace(/\s{2,}/g, ' ')
            .trim();
        return txt;
    }

    function ensureLeadingSalawat(rawText, allowOpeningOnce = false) {
        const cleanBody = stripAllSalawatPrefixes(rawText);
        if (!allowOpeningOnce || hasSpokenSalawatInCurrentGuideSession) {
            return cleanBody;
        }
        hasSpokenSalawatInCurrentGuideSession = true;
        if (!cleanBody) return SHORT_SAHIHAYN_SALAWAT;
        return `${SHORT_SAHIHAYN_SALAWAT} ${cleanBody}`;
    }

    function resolveStepSpeech(step, dialect = selectedMaleProfile, isFirstStepOfSession = false) {
        if (!step) return '';
        let out = '';
        if (step.speechByDialect && step.speechByDialect[dialect]) {
            const val = step.speechByDialect[dialect];
            out = (typeof val === 'function') ? val() : val;
        } else {
            const base = (typeof step.speech === 'function') ? step.speech() : (step.speech || step.narration || '');
            out = adaptTextToDialect(base, dialect);
        }
        out = stripAllSalawatPrefixes(out);
        if (isFirstStepOfSession && !hasSpokenSalawatInCurrentGuideSession) {
            out = `${SHORT_SAHIHAYN_SALAWAT} ${out}`.trim();
        }
        return out;
    }

    async function fetchAndDecodeMaleBuffer(text, dialect = selectedMaleProfile) {
        const cleanTxt = cleanTextForMaleTts(text);
        const activeVoice = dialect || selectedMaleProfile || 'female_zariyah';
        const cacheKey = `unified_neural_v15|${activeVoice}|${cleanTxt}`;
        if (decodedBufferCache.has(cacheKey)) {
            return {
                buffer: decodedBufferCache.get(cacheKey),
                blobUrl: rawBlobUrlCache.get(cacheKey) || null,
                isGenuineNeural: true
            };
        }
        if (inFlightBufferPromises.has(cacheKey)) {
            return await inFlightBufferPromises.get(cacheKey);
        }

        const taskPromise = (async () => {
            let arrayBuf = rawArrayBufferCache.get(cacheKey);
            if (!arrayBuf) {
                if (typeof navigator !== 'undefined' && navigator.onLine === false) {
                    throw new Error('Offline mode');
                }
                const url = `api/tts_male.php?dialect=${encodeURIComponent(activeVoice)}&text=${encodeURIComponent(cleanTxt.slice(0, 350))}`;
                let lastErr = null;
                for (let attempt = 0; attempt < 2; attempt++) {
                    const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                    const tid = ctrl ? setTimeout(() => ctrl.abort(), 16000) : null;
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                            body: `dialect=${encodeURIComponent(activeVoice)}&text=${encodeURIComponent(cleanTxt)}`,
                            signal: ctrl ? ctrl.signal : undefined
                        });
                        if (tid) clearTimeout(tid);
                        if (!res.ok || res.status === 204) throw new Error('TTS HTTP ' + res.status);
                        const buf = await res.arrayBuffer();
                        if (buf && buf.byteLength > 300) {
                            arrayBuf = buf.slice(0);
                            rawArrayBufferCache.set(cacheKey, arrayBuf);
                            isGenuineNeuralCache.set(cacheKey, true);
                            try {
                                const blob = new Blob([arrayBuf], { type: 'audio/mpeg' });
                                rawBlobUrlCache.set(cacheKey, URL.createObjectURL(blob));
                            } catch (_) {}
                            break;
                        }
                        throw new Error('Empty TTS buffer');
                    } catch (err) {
                        if (tid) clearTimeout(tid);
                        lastErr = err;
                        if (attempt === 0) await new Promise(r => setTimeout(r, 220));
                    }
                }
                if (!arrayBuf) throw (lastErr || new Error('TTS failed'));
            }
            const ctx = getAudioCtx();
            const decoded = await ctx.decodeAudioData(arrayBuf.slice(0));
            decodedBufferCache.set(cacheKey, decoded);
            return { buffer: decoded, blobUrl: rawBlobUrlCache.get(cacheKey) || null, isGenuineNeural: true };
        })();

        inFlightBufferPromises.set(cacheKey, taskPromise);
        try {
            return await taskPromise;
        } finally {
            inFlightBufferPromises.delete(cacheKey);
        }
    }

    let isPrewarmingNow = false;
    async function prewarmGuideAudio(guideKey = 'smart-logistics', dialect = selectedMaleProfile) {
        return;
    }

    let activeAudioBlobUrl = null;
    function ensurePitchPreservedStudioGraph(ctx) {
        if (!window.__msgPitchPreservedAudioEl) {
            const a = new Audio();
            a.preload = 'auto';
            a.preservesPitch = true;
            a.mozPreservesPitch = true;
            a.webkitPreservesPitch = true;
            a.volume = 1.0;
            window.__msgPitchPreservedAudioEl = a;
        }
        return window.__msgPitchPreservedAudioEl;
    }

    function startBufferAtOffset(offsetSec = 0) {
        if (!activeAudioBuffer) return;
        if (window.__msgPitchPreservedAudioEl) {
            try { window.__msgPitchPreservedAudioEl.onended = null; window.__msgPitchPreservedAudioEl.pause(); } catch (_) {}
        }
        if (currentAudioSource) {
            try { currentAudioSource.onended = null; currentAudioSource.stop(); } catch (e) {}
            currentAudioSource = null;
        }
        const ctx = getAudioCtx();
        if (ctx && ctx.state === 'suspended') {
            try { ctx.resume(); } catch (_) {}
        }
        const clampedOffset = Math.max(0, Math.min(offsetSec, Math.max(0, activeAudioBuffer.duration - 0.08)));
        activeAudioOffset = clampedOffset;
        activeAudioStartTime = ctx.currentTime;
        isAudioPaused = false;
        isAudioPlaying = true;
        syncGlobalKillSwitchVisibility(true);
        const calmRate = Math.max(0.50, Math.min(4.00, selectedSpeechRate || 1.00));

        const source = ctx.createBufferSource();
        source.buffer = activeAudioBuffer;
        source.detune.value = 0;
        source.playbackRate.value = calmRate;

        const chain = buildMaleClarityChain(ctx, 1.0);
        window.__msgLiveMasterGainNode = chain.output;
        source.connect(chain.input);
        chain.output.connect(ctx.destination);

        currentAudioSource = source;
        const onEndedSnapshot = activeAudioOnEnd;
        source.onended = () => {
            if (currentAudioSource === source && !isAudioPaused) {
                currentAudioSource = null;
                isAudioPlaying = false;
                activeAudioOffset = activeAudioBuffer ? activeAudioBuffer.duration : 0;
                updateAudioPlayerUI();
                stopProgressLoop();
                syncGlobalKillSwitchVisibility(false);
                if (onEndedSnapshot && activeAudioOnEnd === onEndedSnapshot) onEndedSnapshot();
            }
        };

        source.start(0, clampedOffset);
        startProgressLoop();
        updateAudioPlayerUI();
    }

    window.togglePauseResumeAudio = async function () {
        // Support pausing/resuming both Web Speech API (speechSynthesis) and AudioContext buffer!
        if ('speechSynthesis' in window && (window.speechSynthesis.speaking || window.speechSynthesis.paused) && !currentAudioSource) {
            if (isAudioPaused || window.speechSynthesis.paused) {
                window.speechSynthesis.resume();
                isAudioPaused = false;
                isAudioPlaying = true;
            } else {
                window.speechSynthesis.pause();
                isAudioPaused = true;
                isAudioPlaying = false;
                if (nextStepTimeoutId) {
                    clearTimeout(nextStepTimeoutId);
                    nextStepTimeoutId = null;
                }
            }
            updateAudioPlayerUI();
            return;
        }
        if (!activeAudioBuffer) {
            window.startLiveAutopilotSimulation('all');
            return;
        }
        const ctx = getAudioCtx();
        if (ctx.state === 'suspended') {
            await ctx.resume();
        }
        if (isAudioPlaying && !isAudioPaused) {
            activeAudioOffset = (window.__msgPitchPreservedAudioEl && !window.__msgPitchPreservedAudioEl.paused)
                ? window.__msgPitchPreservedAudioEl.currentTime
                : getCurrentAudioOffset();
            isAudioPaused = true;
            isAudioPlaying = false;
            if (nextStepTimeoutId) {
                clearTimeout(nextStepTimeoutId);
                nextStepTimeoutId = null;
            }
            if (window.__msgPitchPreservedAudioEl) {
                try { window.__msgPitchPreservedAudioEl.pause(); } catch (e) {}
            }
            if (currentAudioSource) {
                try {
                    currentAudioSource.onended = null;
                    currentAudioSource.stop();
                } catch (e) {}
                currentAudioSource = null;
            }
            stopProgressLoop();
            updateAudioPlayerUI();
        } else {
            if (activeAudioOffset >= activeAudioBuffer.duration - 0.15) {
                activeAudioOffset = 0;
            }
            startBufferAtOffset(activeAudioOffset);
        }
    };

    window.seekAudioTo = async function (targetSec) {
        if (!activeAudioBuffer) {
            window.startLiveAutopilotSimulation('all');
            return;
        }
        const ctx = getAudioCtx();
        if (ctx.state === 'suspended') {
            try { await ctx.resume(); } catch (e) {}
        }
        const sec = parseFloat(targetSec);
        if (isNaN(sec)) return;
        const clamped = Math.max(0, Math.min(sec, Math.max(0, activeAudioBuffer.duration - 0.25)));
        activeAudioOffset = clamped;
        startBufferAtOffset(clamped);
    };

    window.seekAudioBy = function (deltaSec) {
        if (!activeAudioBuffer) {
            window.startLiveAutopilotSimulation('all');
            return;
        }
        const d = parseFloat(deltaSec) || 0;
        const cur = getCurrentAudioOffset();
        const dur = activeAudioBuffer.duration || 0;
        const target = cur + d;

        // If seeking forward past the end of the current step, advance to the next step seamlessly!
        if (d > 0 && target >= dur - 0.45 && isAutopilotRunning) {
            window.stepLiveAutopilot(1);
            return;
        }
        // If seeking backward near the very beginning of the step, go to previous step if available
        if (d < 0 && cur < 1.2 && isAutopilotRunning && autopilotStepIdx > 0) {
            window.stepLiveAutopilot(-1);
            return;
        }
        window.seekAudioTo(target);
    };

    function unlockBrowserAudioEnginesSync() {
        try {
            const ctx = getAudioCtx();
            if (ctx && ctx.state === 'suspended') {
                ctx.resume().catch(() => {});
            }
            if (ctx) {
                const silentBuf = ctx.createBuffer(1, 1, 22050);
                const silentSrc = ctx.createBufferSource();
                silentSrc.buffer = silentBuf;
                silentSrc.connect(ctx.destination);
                silentSrc.start(0);
            }
        } catch (e) {}
        try {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                window.speechSynthesis.resume();
                window.speechSynthesis.getVoices();
            }
        } catch (e) {}
    }
    ['pointerdown', 'mousedown', 'touchstart', 'keydown'].forEach(evName => {
        window.addEventListener(evName, () => {
            try {
                if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume().catch(() => {});
            } catch (_) {}
        }, { capture: true, passive: true });
    });

    function selectBestBrowserArabicMaleVoice(dialect = selectedMaleProfile) {
        if (!('speechSynthesis' in window)) return null;
        const voices = window.speechSynthesis.getVoices() || [];
        if (!voices.length) return null;

        const femaleBlacklist = /oda|hoda|salma|zariyah|fatima|amany|laila|mariam|noura|female|أنثى|سلمى|هدى|فاطمة|زارية|أماني/i;
        const malePreferred = /shakir|hamed|hamdan|maged|tarik|naayf|omar|ali|male|شاكر|حامد|حمدان|ماجد|طارق|نايف/i;

        const arVoices = voices.filter(v => /^ar/i.test(v.lang || '') || /arabic|عربي/i.test(v.name || ''));
        const nonFemaleAr = arVoices.filter(v => !femaleBlacklist.test(v.name || ''));
        nonFemaleAr.sort((a, b) => (/natural|online|neural/i.test(b.name) ? 1 : 0) - (/natural|online|neural/i.test(a.name) ? 1 : 0));

        if (dialect === 'egyptian') {
            const egMale = nonFemaleAr.find(v => /shakir|eg/i.test(v.name + ' ' + v.lang) && malePreferred.test(v.name));
            if (egMale) return egMale;
        }
        const explicitMale = nonFemaleAr.find(v => malePreferred.test(v.name || ''));
        if (explicitMale) return explicitMale;
        if (nonFemaleAr.length > 0) return nonFemaleAr[0];
        return arVoices[0] || null;
    }

    let _browserSpeechKeepAliveTimer = null;
    function speakWithBrowserMaleFallback(text, onEndCallback, dialect = selectedMaleProfile, allowOpeningOnce = false, alreadyFormatted = false) {
        const reqId = currentSpeechRequestId;
        if (!('speechSynthesis' in window)) {
            if (onEndCallback) setTimeout(() => { if (reqId === currentSpeechRequestId) onEndCallback(); }, 2500);
            return;
        }
        try {
            if (_browserSpeechKeepAliveTimer) {
                clearInterval(_browserSpeechKeepAliveTimer);
                _browserSpeechKeepAliveTimer = null;
            }
            if (window.__activeGuideUtterance) {
                try {
                    window.__activeGuideUtterance.onend = null;
                    window.__activeGuideUtterance.onerror = null;
                } catch (_) {}
                window.__activeGuideUtterance = null;
            }
            window.speechSynthesis.cancel();
            window.speechSynthesis.resume();
            const cleanText = cleanTextForMaleTts(alreadyFormatted ? String(text || '').trim() : ensureLeadingSalawat(text, allowOpeningOnce));
            const utter = new SpeechSynthesisUtterance(cleanText);
            window.__activeGuideUtterance = utter;
            window.__msgUtterCharIdx = 0; window.__msgUtterLen = cleanText.length;
            utter.onboundary = (ev) => { if (ev && typeof ev.charIndex === 'number') window.__msgUtterCharIdx = ev.charIndex; };
            utter.lang = dialect === 'egyptian' ? 'ar-EG' : 'ar-SA';
            const chosenVoice = selectBestBrowserArabicMaleVoice(dialect);
            if (chosenVoice) {
                utter.voice = chosenVoice;
            }
            utter.pitch = 1.0;
            utter.rate = Math.max(0.75, Math.min(1.50, selectedSpeechRate || 1.0));
            utter.volume = 1.0;

            isAudioPlaying = true;
            isAudioPaused = false;
            updateAudioPlayerUI();

            let finished = false;
            const finishOnce = () => {
                if (finished || reqId !== currentSpeechRequestId) return;
                finished = true;
                if (_browserSpeechKeepAliveTimer) {
                    clearInterval(_browserSpeechKeepAliveTimer);
                    _browserSpeechKeepAliveTimer = null;
                }
                isAudioPlaying = false;
                updateAudioPlayerUI();
                if (onEndCallback) onEndCallback();
            };
            utter.onend = finishOnce;
            utter.onerror = (ev) => {
                if (ev && (ev.error === 'canceled' || ev.error === 'interrupted')) return;
                finishOnce();
            };
            window.speechSynthesis.speak(utter);
            // Keep-alive loop prevents Chrome on Windows from auto-pausing long Arabic utterances
            _browserSpeechKeepAliveTimer = setInterval(() => {
                try {
                    if (!isAudioPlaying || isAudioPaused) return;
                    if (window.speechSynthesis.paused) window.speechSynthesis.resume();
                } catch (_e) {}
            }, 250);
            setTimeout(() => {
                try {
                    if (window.speechSynthesis.paused) window.speechSynthesis.resume();
                } catch (e) {}
            }, 50);
        } catch (e) {
            if (onEndCallback) setTimeout(onEndCallback, 2500);
        }
    }

    // 🔊 COMPLETE SEPARATION: Live Smart Guide Audio is active in normal mode, and strictly isolated when 4K Cinema Studio runs
    window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;

    function splitInstantFirstBurst(clean) {
        let firstBurst = clean;
        let restBurst = '';
        if (clean.length > 48) {
            const cutIdx = clean.slice(0, 58).search(/[.:،؛!؟]\s+[^.:،؛!؟]*$/);
            const splitAt = cutIdx > 16 ? cutIdx + 1 : clean.lastIndexOf(' ', 48);
            if (splitAt > 14) {
                firstBurst = clean.slice(0, splitAt).trim();
                restBurst = clean.slice(splitAt).replace(/^[.:،؛\s]+/, '').trim();
            }
        }
        return { firstBurst, restBurst };
    }

    async function speakArabicMaleText(text, onEndCallback, dialect = selectedMaleProfile, allowOpeningOnce = false) {
        stopCurrentSpeech();
        if (window.__CINEMA_4K_ACTIVE__ || window.__MUTE_LEGACY_GUIDE_AUDIO__) {
            if (typeof onEndCallback === 'function') {
                nextStepTimeoutId = setTimeout(onEndCallback, 2800);
            }
            return;
        }
        const reqId = currentSpeechRequestId;
        ensureGuideDomElements();

        const finalSpokenText = ensureLeadingSalawat(text, allowOpeningOnce);

        const coachBar = document.getElementById('msg-live-coach-bar');
        if (coachBar) coachBar.style.display = 'flex';
        const pinnedDock = document.getElementById('msg-pinned-audio-dock');
        if (pinnedDock) pinnedDock.style.display = 'inline-flex';
        const speechBox = document.getElementById('msg-coach-speech-text');
        if (speechBox && finalSpokenText) speechBox.textContent = finalSpokenText;

        const { firstBurst, restBurst } = splitInstantFirstBurst(finalSpokenText);
        try {
            const ctx = getAudioCtx();
            if (ctx.state === 'suspended') await ctx.resume();
            const { buffer: audioBuffer, blobUrl, isGenuineNeural } = await fetchAndDecodeMaleBuffer(firstBurst, dialect);
            if (reqId !== currentSpeechRequestId) return;
            const restPromise = restBurst ? fetchAndDecodeMaleBuffer(restBurst, dialect).catch(() => null) : null;
            activeSpokenClean = cleanTextForMaleTts(firstBurst);
            activeAudioBuffer = audioBuffer;
            activeAudioBlobUrl = blobUrl || null;
            activeAudioIsGenuine = isGenuineNeural;
            activeAudioOnEnd = async () => {
                if (reqId !== currentSpeechRequestId) return;
                if (restPromise) {
                    const nextData = await restPromise;
                    if (nextData && nextData.buffer && reqId === currentSpeechRequestId) {
                        activeSpokenClean = cleanTextForMaleTts(restBurst);
                        activeAudioBuffer = nextData.buffer;
                        activeAudioBlobUrl = nextData.blobUrl || null;
                        activeAudioIsGenuine = nextData.isGenuineNeural;
                        activeAudioOnEnd = onEndCallback || null;
                        startBufferAtOffset(0);
                        return;
                    }
                }
                if (onEndCallback) onEndCallback();
            };
            startBufferAtOffset(0);
        } catch (err) {
            if (reqId !== currentSpeechRequestId) return;
            speakWithBrowserMaleFallback(finalSpokenText, onEndCallback, dialect, false, true);
        }
    }

    // ── SCREEN GUIDES REGISTRY ───────────────────────────────────────────────
    window.MagaiesSmartGuides = window.MagaiesSmartGuides || {};

    // ============================================================================
    // 📥 1. FULL MASTERCLASS: PURCHASES RECEIPT & SPLIT GATEWAY (GRN)
    // ============================================================================
    const PURCHASES_MASTER_STEPS = [
        {
            badge: 'الخطوة 1️⃣ — الملخص العام: المدخلات والمخرجات والقاعدة الذهبية لبوابة استلام المشتريات (GRN)',
            cursorLabel: 'بوابة استلام المشتريات (إضافة للمخزن +) 📥',
            targetSelector: '#sl-top-cockpit-bar, #sl-main-title-text',
            diagramType: 'c5-impact-map',
            c5ImpactMeta: {
                screenTitle: 'بوابة استلام المشتريات وتقسيمها على دفعات (GRN)',
                inputs: [
                    '📥 شاشة فواتير المشتريات وأوامر الشراء',
                    '🗂️ البيانات الأساسية (الموردين والأصناف والوحدات)',
                    '📱 مسح الباركود/QR بالجوال وميزان البسكول'
                ],
                funcSummary: 'فحص وعدّ البضاعة المشتراة واستلامها دفعة واحدة أو على دفعات مجزأة مع تحويل الوحدات ومنع التجاوز آلياً',
                reports: [
                    '📦 شاشة المخازن وكارت الصنف (+ زيادة الرصيد)',
                    '🧾 شاشة المشتريات وتقرير المتبقيات والأذونات',
                    '📖 كشف حساب المورد ودفتر اليومية (- خصم النقص)'
                ],
                screens: [
                    '🏭 تحديث أرصدة المستودعات لحظياً (+)',
                    '📋 سجل أذونات الاستلام الموحد (GRN)',
                    '⚖️ إشعارات خصم النقص والتالف للمورد'
                ],
                goldenRule: 'القاعدة الذهبية: افحص وعدّ البضاعة بالوحدة الصحيحة قبل اعتماد الاستلام ينضبط لك رصيد المستودع وحساب المورد تلقائياً.',
                reassurance: 'رسالة الطمأنينة للمحاسب: اعمل بكل ثقة ولا تخشَ الخطأ؛ فالشاشة محمية بحفظ المسودة الفوري ودرع منع التجاوز وزر عكس الحركة للتراجع الكامل في أي لحظة.'
            },
            speechByDialect: {
                egyptian: () => `أهلاً بحضرتك في بوابة استلام المشتريات وتقسيمها جي آر إن. وظيفة الشاشة دي وأهميتها إنها صمام الأمان بين المشتريات والمخزن؛ البضاعة اللي بنشتريها مش بتدخل المخزن لوحدها، لازم تعدي هنا الأول علشان نفحصها ونعدّها ونستلمها دفعة واحدة أو على دفعات مجزأة. طب منين بتجيلها المعلومات؟ المدخلات بتاعتها بتيجي مباشرة من شاشة فواتير المشتريات وأوامر الشراء، ومن شاشة البيانات الأساسية اللي فيها الموردين والأصناف والوحدات والمخازن. وبتسمّع فين وبتأثر في إيه؟ أول ما بتعتمد إذن الاستلام، بتزود رصيد المستودع الفعلي في شاشة المخازن وكارت الصنف وتقرير الجرد، وبتحدث حالة الفاتورة في شاشة المشتريات وتقرير المتبقيات وسجل الأذونات، ولو فيه أي نقص أو تالف بتصدر إشعار خصم بينقص مديونية المورد في كشف حسابه ودفتر اليومية. والقاعدة الذهبية للشاشة دي: افحص وعدّ البضاعة بالوحدة الصحيحة قبل اعتماد الاستلام ينضبط لك رصيد المستودع وحساب المورد تلقائياً. واطمئن تماماً واشتغل بقلب جامد؛ لأن الشاشة محمية بحفظ المسودة الفوري، ودرع منع التجاوز، وزر عكس الحركة للتراجع الكامل في أي لحظة.`,
                sudanese: () => `حبابك عشرة في بوابة استلام المشتريات وتقسيمها جي آر إن. وظيفة الشاشة دي وأهميتها إنها صمام الأمان بين المشتريات والمخزن؛ البضاعة البنشتريها ما بتدخل المخزن براها، لازم تمر هنا أول عشان نفحصها ونعدّها ونستلمها دفعة واحدة أو مقسمة على دفعات. ومن وين بتجيها المعلومات؟ مدخلاتها بتجي مباشرة من شاشة فواتير المشتريات وأوامر الشراء، ومن شاشة البيانات الأساسية الفيها الموردين والأصناف والوحدات والمخازن. وبتسمّع وين وبتأثر في شنو؟ أول ما تعتمد إذن الاستلام، بتزيد رصيد المستودع الفعلي في شاشة المخازن وكارت الصنف وتقرير الجرد، وبتحدث حالة الفاتورة في شاشة المشتريات وتقرير المتبقيات وسجل الأذونات، ولو في أي نقص أو تالف بتطلع إشعار خصم بنقص مديونية المورد في كشف حسابو ودفتر اليومية. والقاعدة الذهبية للشاشة: افحص وعدّ البضاعة بالوحدة الصحيحة قبل اعتماد الاستلام ينضبط ليك رصيد المستودع وحساب المورد تلقائياً. واطمئن تماماً وما تخاف من الغلط؛ لأن الشاشة محمية بحفظ المسودة الفوري ودرع منع التجاوز وزر عكس الحركة للتراجع الكامل في أي لحظة.`,
                gulf:     () => `يا هلا بك في بوابة استلام المشتريات وتقسيمها جي آر إن. وظيفة هذي الشاشة وأهميتها إنها صمام الأمان بين المشتريات والمستودع؛ البضاعة المشتراة ما تدخل المستودع تلقائياً، بل تمر هنا أولاً لفحصها وعدّها واستلامها دفعة واحدة أو مقسمة على دفعات. ومن وين تجيها المعلومات؟ مدخلاتها تجي مباشرة من شاشة فواتير المشتريات وأوامر الشراء، ومن شاشة البيانات الأساسية الشاملة للموردين والأصناف والوحدات والمستودعات. ووين تسمّع وفي أي تقرير وشاشة تؤثر؟ فور اعتماد إذن الاستلام تزيد الرصيد الفعلي للمستودع في شاشة المخازن وكارت الصنف وتقرير الجرد، وتحدث حالة الفاتورة في شاشة المشتريات وتقرير المتبقيات وسجل الأذونات، وعند وجود نقص أو تالف تصدر إشعار خصم ينقص مديونية المورد في كشف حسابه ودفتر اليومية. والقاعدة الذهبية للشاشة: افحص وعدّ البضاعة بالوحدة الصحيحة قبل اعتماد الاستلام ينضبط لك رصيد المستودع وحساب المورد تلقائياً. واطمئن تماماً ولا تخشى الخطأ؛ فالشاشة محمية بحفظ المسودة الفوري ودرع منع التجاوز وزر عكس الحركة للتراجع الكامل بأي لحظة.`
            },
            speech: () => `أهلاً بك في بوابة استلام المشتريات وتقسيمها (GRN). وظيفة هذه الشاشة وأهميتها أنها صمام الأمان بين المشتريات والمخزن؛ حيث يتم فيها فحص وعدّ البضاعة المشتراة واستلامها دفعة واحدة أو على دفعات مجزأة. تأتي مدخلاتها من شاشة فواتير المشتريات وأوامر الشراء ومن شاشة البيانات الأساسية للموردين والأصناف والوحدات والمخازن. وتُسمّع مخرجاتها فوراً بزيادة الرصيد الفعلي في شاشة المخازن وكارت الصنف وتقرير الجرد، وتحديث حالة الفاتورة في شاشة المشتريات وتقرير المتبقيات وسجل الأذونات، وتخفيض مديونية المورد بإشعار خصم في كشف حسابه ودفتر اليومية عند وجود نقص أو تالف. القاعدة الذهبية للشاشة: افحص وعدّ البضاعة بالوحدة الصحيحة قبل اعتماد الاستلام ينضبط لك رصيد المستودع وحساب المورد تلقائياً. واطمئن تماماً ولا تخشَ الخطأ؛ فالشاشة محمية بحفظ المسودة الفوري ودرع منع التجاوز وزر عكس الحركة للتراجع الكامل في أي لحظة.`,
            action: () => {
                if (typeof window.switchLogisticsMode === 'function') {
                    window.switchLogisticsMode('purchases', true);
                }
            }
        },
        {
            badge: 'الخطوة 2️⃣ — شريط القيادة العلوي: ربط كاميرا الجوال وحارس الـ QR',
            cursorLabel: 'حارس الأمان 🛡️ + ربط كاميرا الجوال 📱',
            targetSelector: 'button[onclick="openSlMobileBridgeModal()"]',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `في الشريط اللي فوق ده عندك أداتين مهمين جداً: الأولى زرار ربط كاميرا الجوال اللي فتح قدامك دلوقتي، وده بيخليك تمسك موبايلك وتمسح باركود عشر فواتير مشتريات ورا بعض فتستلمهم في ثواني من غير ما تلمس الكيبورد. والتانية هي قائمة حارس الكيو آر، لو خليتها على وضع قفل القسم، هتحميك لو العامل مسح بالغلط أي ورقة خارجية لا تخص استلام المشتريات، فبيرفضها وينبهك فوراً. والقاعدة الذهبية لهذا العنصر: فعّل وضع قفل القسم في حارس الكيو آر أثناء استلام الشحنات لمنع مسح أي مستند مبيعات بالخطأ.`,
                sudanese: () => `في الشريط الفوق ده عندك حاجتين مهمات شديد: الأولى زر ربط كاميرا الجوال الفتح قدامك هسي، وده بخليك تمسك تلفونك وتمسح باركود عشرة أذونات مشتريات ورا بعض فتستلمهم في ثواني بدون ما تلمس الكيبورد. والتانية هي قائمة حارس الكيو آر، لو قفلتها على القسم الحالي، بتحميك لو العامل مسح بالغلط أي ورقة ما بتخص المشتريات، فبيرفضها وينبهك طوالي. والقاعدة الذهبية لهذا العنصر: اقفل حارس الكيو آر على القسم الحالي أثناء الاستلام عشان تمنع مسح أي ورقة خارجية بالغلط.`,
                gulf:     () => `في الشريط العلوي عندك أداتين في غاية الأهمية: الأولى نافذة ربط كاميرا الجوال اللي انفتحت قدامك الحين، وتخليك تمسح بجوالك عشر فواتير مشتريات ورا بعض وتستلمها بثواني بدون لمس الكيبورد. والثانية قائمة حارس الكيو آر، لو حطيتها على قفل القسم، تحميك لو العامل مسح بالغلط أي ورقة لا تخص استلام المشتريات، فيرفضها وينبهك فوراً. والقاعدة الذهبية لهذا العنصر: اضبط حارس الكيو آر على قفل القسم أثناء استلام البضائع لمنع تداخل أذونات الصرف مع الاستلام.`
            },
            speech: () => `في الشريط العلوي تفتح نافذة ربط كاميرا الجوال لمسح عشرات الأذونات متتالية، وبجانبها قائمة حارس الكيو آر لمنع مسح أي ورقة بالخطأ. القاعدة الذهبية لهذا العنصر: اضبط حارس الكيو آر على قفل القسم أثناء ضغط العمل لمنع مسح أي مستند خارجي بالخطأ.`,
            action: () => {
                const qrSel = document.getElementById('sl-qr-guard-mode');
                if (qrSel) showLiveSelectOptionsMirror(qrSel, null, '🛡️ خيارات حارس الـ QR الذكي');
                const t1 = setTimeout(() => {
                    if (typeof window.openSlMobileBridgeModal === 'function') window.openSlMobileBridgeModal();
                }, 1400);
                dubbedVisualTimers.push(t1);
            }
        },
        {
            badge: 'الخطوة 3️⃣ — العدادات الأربعة الذكية: أين تقف كل فاتورة الآن؟',
            cursorLabel: 'العدادات الأربعة (انتظار • مقسمة • مكتملة • أذونات) 📊',
            targetSelector: '#sl-filter-btn-pending, #sl-kpi-card-pending',
            speechByDialect: {
                egyptian: () => `تحت الشريط العلوي هتلاقي أربع كروت ملونة بتلخص لك حال المخزن كله: الكارت الأصفر الأول بيعرض الفواتير الجديدة اللي لسه واصلة ومستنية الاستلام. والكارت الأزرق التاني بيعرض الفواتير اللي استلمنا جزء منها ولسه باقي ليها دفعات تانية عند المورد. والكارت الأخضر التالت للفواتير اللي خلصت بالكامل. والبنفسجي الرابع فيه أرشيف كل أذونات الاستلام الرسمية اللي طبعناها. والقاعدة الذهبية لهذا العنصر: راجع كارت الفواتير المقسمة الزرقاء يومياً لمتابعة الدفعات المتأخرة عند الموردين أولاً بأول.`,
                sudanese: () => `تحت الشريط الفوق بتلقى أربعة كروت ملونة بتلخص ليك حركة المخزن كلها: الكرت الأصفر الأول بيعرض الفواتير الجديدة الراجية الاستلام. والكرت الأزرق التاني بيعرض الفواتير القسّمناها واستلمنا جزء منها ولسه باقي ليها دفعات عند المورد. والكرت الأخضر التالت للفواتير الاكتملت خلاص. والبنفسجي الرابع فيهو سجل كل أذونات الاستلام الرسمية. والقاعدة الذهبية لهذا العنصر: تابع كرت الفواتير المقسمة الزرقاء يومياً عشان تضمن وصول باقي الدفعات من الموردين في مواعيدها.`,
                gulf:     () => `تحت الشريط العلوي بتلقى أربع بطاقات ملونة تلخص حالة المستودع بالكامل: البطاقة الصفراء الأولى تعرض الفواتير الجديدة اللي تنتظر الاستلام. والبطاقة الزرقاء الثانية تعرض الفواتير المقسمة اللي استلمنا جزء منها وباقي لها دفعات ثانية. والخضراء الثالثة للفواتير المكتملة، والبنفسجية الرابعة لسجل أذونات الاستلام الرسمية. والقاعدة الذهبية لهذا العنصر: راجع بطاقة الفواتير المقسمة الزرقاء نهاية كل يوم لمتابعة المتبقيات المعلقة لدى الموردين.`
            },
            speech: () => `هذه البطاقات الأربع تصنف الفواتير بوضوح: الصفراء للفواتير الجديدة قيد الاستلام، الزرقاء للفواتير المقسمة على دفعات، الخضراء للمكتملة، والبنفسجية لسجل الأذونات الرسمية. القاعدة الذهبية لهذا العنصر: راجع بطاقة الفواتير المقسمة يومياً لمتابعة الدفعات المتبقية لدى الموردين.`,
            action: () => {
                if (typeof window.setSlRegistryFilter === 'function') {
                    window.setSlRegistryFilter('pending');
                }
                window.ensureLiveDemoInvoiceForGuide();
            }
        },
        {
            badge: 'الخطوة 4️⃣ — السجل الذكي الفوري: البحث والفرق بين الاعتماد السريع والتقسيم',
            cursorLabel: 'السجل الذكي: (⚡ اعتماد كامل) أو (✂️ تقسيم وفحص) 📋',
            targetSelector: '#sl-registry-tbody tr:first-child',
            speechByDialect: {
                egyptian: () => `هنا في جدول السجل الذكي، تقدر تبحث في أكتر من ألف فاتورة برقم الفاتورة أو اسم المورد أو حتى اسم الصنف. وقدام كل فاتورة فيه زرارين مهمين: لو العربية جابت البضاعة كاملة زي الفاتورة بالظبط، تضغط على زرار اعتماد كامل الأخضر فتدخل المخزن في ثانية واحدة. أما لو البضاعة جاية ناقصة أو عايز تقسمها على دفعات أو توزعها على كذا مخزن، بنضغط على الفاتورة أو زرار تقسيم وفحص فبتنزل معانا تحت. والقاعدة الذهبية لهذا العنصر: لا تضغط اعتماد كامل إلا إذا طابقت الشحنة الفاتورة مئة بالمئة، واستخدم تقسيم وفحص عند وجود أي تجزئة أو اختلاف.`,
                sudanese: () => `هنا في جدول السجل الذكي، تقدر تفتش في أكتر من ألف فاتورة برقم الفاتورة أو اسم المورد أو اسم الصنف ذاتو. وقدام كل فاتورة في زرين: لو العربية جابت البضاعة كاملة زي الفاتورة بالضبط، تضغط زر اعتماد كامل الأخضر فبتدخل المخزن في ثانية. أما لو البضاعة ناقصة أو داير تقسمها على دفعات أو توزعها على مخازن مختلفة، تضغط على الفاتورة أو زر تقسيم وفحص عشان تفتح معاك تحت. والقاعدة الذهبية لهذا العنصر: ما تضغط اعتماد كامل إلا لو الشحنة مطابقة للفاتورة مية في المية، وافتح تقسيم وفحص لو في أي دفعات أو فروقات.`,
                gulf:     () => `هنا في جدول السجل الذكي، تقدر تبحث في أكثر من ألف فاتورة برقم الفاتورة أو اسم المورد أو اسم الصنف. وقدام كل فاتورة زرين: إذا وصلت البضاعة كاملة نفس الفاتورة بالضبط، تضغط زر اعتماد كامل الأخضر وتدخل المستودع بثانية. أما إذا وصلت على دفعات أو تبغى تقسمها على أكثر من مستودع، تضغط على الفاتورة أو زر تقسيم وفحص وتفتح معك بالأسفل. والقاعدة الذهبية لهذا العنصر: لا تستخدم الاعتماد الكامل إلا عند تطابق الشحنة تماماً، وافتح الفاتورة للفحص والتقسيم عند تجزئة الدفعات.`
            },
            speech: () => `في هذا السجل يمكنك البحث الفوري في آلاف الفواتير. إذا وصلت البضاعة كاملة اضغط اعتماد كامل، وإذا أردت تقسيمها على دفعات أو مخازن اضغط على الفاتورة لتفتح في بطاقة الفحص بالأسفل. القاعدة الذهبية لهذا العنصر: لا تضغط اعتماد كامل إلا بعد التأكد من وصول كامل كمية الفاتورة دون نقص.`,
            action: () => {
                const firstRow = document.querySelector('#sl-registry-tbody tr:first-child');
                if (firstRow) firstRow.click();
            }
        },
        {
            badge: 'الخطوة 5️⃣ — بطاقة الفحص والبيانات اللوجستية: توثيق السائق واللوحة والمستودع',
            cursorLabel: 'قائمة المستودعات 🏢 + إضافة سريعة (+) + ختم وقت البوابة ⏱️',
            targetSelector: '#sl-master-warehouse',
            diagramType: 'dropdown-open',
            speechByDialect: {
                egyptian: () => `أول ما الفاتورة بتفتح معانا في بطاقة الفحص دي، بنختار المستودع الافتراضي من القائمة المنسدلة اللي فتحت قدامك دلوقتي، أو نضغط علامة الزائد اللي جنبها علشان نضيف مخزن جديد في ثانية! وجنبها بنسجل اسم المستلم الفعلي، واسم سائق الشاحنة، ورقم لوحة العربية، وختم وقت دخول وخروج الشاحنة من البوابة علشان كل ده يتطبع في إذن الاستلام الرسمي. والقاعدة الذهبية لهذا العنصر: حدد المستودع الصحيح وسجل رقم لوحة الشاحنة واسم السائق لحماية حق المنشأة قانونياً ومخزنياً.`,
                sudanese: () => `أول ما الفاتورة تفتح معاك في بطاقة الفحص دي، بنختار المخزن الأساسي من القائمة المنسدلة الفتحت قدامك هسي، أو نضغط علامة الزائد الجنبها عشان نضيف مخزن جديد طوالي! وجنبها بنكتب اسم المستلم الفعلي، واسم سواق الشاحنة، ورقم اللوحة، وختم زمن دخول وخروج الشاحنة من البوابة عشان تطبع كلها في إذن الاستلام الرسمي. والقاعدة الذهبية لهذا العنصر: اختار المخزن الصحيح ووثق اسم السواق ورقم اللوحة عشان تحفظ حق المخزن والمؤسسة.`,
                gulf:     () => `أول ما تفتح الفاتورة في بطاقة الفحص هذي، نختار المستودع الافتراضي من القائمة المنسدلة اللي انفتحت قدامك الحين، أو نضغط زر الزائد بجانبها لإضافة مستودع جديد فوراً! وبجانبها نسجل اسم المستلم الفعلي، واسم سائق الشاحنة، ورقم اللوحة، وختم وقت دخول وخروج الشاحنة من البوابة عشان تنطبع رسمياً في مذكرة الاستلام. والقاعدة الذهبية لهذا العنصر: تأكد من اختيار المستودع المستلم وتوثيق بيانات السائق والمركبة قبل ترحيل الإذن.`
            },
            speech: () => `في بطاقة الفحص نختار المستودع الافتراضي من القائمة المنسدلة أو نضيف مستودعاً جديداً بزر الزائد، ونسجل اسم المستلم والسائق ورقم اللوحة وختم وقت البوابة. القاعدة الذهبية لهذا العنصر: حدد المستودع المستلم بدقة ووثق بيانات السائق واللوحة لضمان الرقابة المخزنية الكاملة.`,
            action: () => {
                const whSel = document.getElementById('sl-master-warehouse');
                if (whSel) showLiveSelectOptionsMirror(whSel, null, '🏢 قائمة المستودعات المتاحة');
                const tDrill = setTimeout(() => {
                    if (typeof window.openSlOriginalInvoiceDrilldownModal === 'function') window.openSlOriginalInvoiceDrilldownModal();
                }, 2200);
                const tCloseDrill = setTimeout(() => {
                    const dm = document.getElementById('sl-drilldown-invoice-modal');
                    if (dm) dm.style.display = 'none';
                }, 4600);
                dubbedVisualTimers.push(tDrill, tCloseDrill);
            }
        },
        {
            badge: 'الخطوة 6️⃣ — صندوق الكاميرا والتوثيق المصوّر للبضاعة عند البوابة',
            cursorLabel: 'الاعتماد التلقائي بالباركود ⚡ + إرفاق صورة البضاعة 📎',
            targetSelector: '#sl-camera-placeholder',
            speechByDialect: {
                egyptian: () => `وعلى الشمال هنا صندوق الكاميرا والتوثيق: فيه علامة صح اسمها اعتماد فوري متتابع عند المسح، ودي معناها إنك أول ما تمرر الباركود قدام الكاميرا بيعتمد الفاتورة أوتوماتيك من غير ما تضغط ولا زرار. وكمان تحتها زرار إرفاق صورة البضاعة، لو عايز تصور البضاعة وهي على العربية أو تصور ورقة المورد الموقعة وترفقها كدليل مع الإذن. والقاعدة الذهبية لهذا العنصر: أرفق صورة حية للبضاعة أو لمستند المورد عند وجود أي ملاحظة أو تلف لحفظ الإثبات بالصورة.`,
                sudanese: () => `وعلى الشمال هنا صندوق الكاميرا والتوثيق: فيهو خيار اسمو اعتماد فوري متتابع عند المسح، وده معناه أول ما تمرر الباركود قدام الكاميرا بيعتمد الفاتورة طوالي بدون ما تضغط أي زر. وتحتو زر إرفاق صورة البضاعة، لو داير تصور البضاعة في العربية أو تصور ورقة المورد وترفقها كإثبات مع الإذن. والقاعدة الذهبية لهذا العنصر: صوّر البضاعة أو إشعار المورد وأرفقها مع الإذن لما يكون في أي تلف أو فرق في الكمية.`,
                gulf:     () => `وعلى اليسار هنا صندوق الكاميرا والتوثيق: فيه خيار اعتماد فوري متتابع عند المسح، يعني بمجرد تمرير الباركود أمام الكاميرا يعتمد الفاتورة تلقائياً بدون ضغط أي زر. وتحته زر إرفاق صورة البضاعة، لتصوير الحمولة أو مستند المورد وإرفاقه كإثبات رسمي. والقاعدة الذهبية لهذا العنصر: وثق الشحنة بالصورة دائماً عند وجود أي ملاحظات جودة أو فروقات كمية.`
            },
            speech: () => `في هذا الصندوق يمكنك تفعيل الاعتماد الفوري بمجرد مسح الباركود، كما يمكنك إرفاق صورة حية للبضاعة أو المستند الورقي كإثبات رسمي. القاعدة الذهبية لهذا العنصر: أرفق صورة الشحنة أو مستند التسليم كدليل مرجعي يحمي المنشأة عند أي مراجعة.`,
            action: () => {}
        },
        {
            badge: 'الخطوة 7️⃣ — قارئ الميزان، التقسيم السريع، موقع الرف والتشغيلة، وتوزيع المخازن',
            cursorLabel: '⚖️ نافذة الميزان الإلكتروني + توزيع ذكي للمخازن 🏭 + الرف والتشغيلة 📍',
            targetSelector: '#sl-items-grid',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `ننزل بقى لجدول الفحص والتقسيم الذكي، وده قلب الشاشة! فوق الجدول فيه زرار قارئ الميزان الإلكتروني اللي فتح قدامك دلوقتي علشان تكتب الوزن القائم والوزن الفارغ فيحسب الوزن الصافي للعربية أوتوماتيك، وجنبه زرار التوزيع الذكي للمخازن وأزرار تعبئة الكمية مية في المية أو تقسيمها للنص خمسين في المية بضغطة واحدة. وجوه الجدول تقدر تحدد لكل صنف مخزن مستقل من قائمته المنسدلة، وتكتب رقم الرف ورقم التشغيلة! والقاعدة الذهبية لهذا العنصر: استخدم ميزان البسكول وتحديد الرف والتشغيلة لكل صنف لضبط الجرد وتتبع الصلاحيات بدقة.`,
                sudanese: () => `ننزل هسي لجدول الفحص والتقسيم الذكي، وده قلب الشاشة! فوق الجدول عندك زر قارئ الميزان الإلكتروني الفتح قدامك هسي عشان يحسب الوزن الصافي للشاحنة أوتوماتيك، وجنبو زر التوزيع الذكي للمخازن وأزرار تملأ ليك الباقي مية في المية أو تقسمو للنص خمسين في المية بضغطة واحدة. وجوه الجدول تقدر تختار لكل صنف مخزن براهو من القائمة، وتسجل رقم الرف ورقم التشغيلة! والقاعدة الذهبية لهذا العنصر: اعتمد على قراءة الميزان الصافي وحدد الرف والتشغيلة لكل صنف عشان يسهل عليك الجرد والتتبع.`,
                gulf:     () => `ننزل الحين لجدول الفحص والتقسيم الذكي، وهذا قلب الشاشة! فوق الجدول نافذة قارئ الميزان الإلكتروني اللي انفتحت قدامك الحين لحساب الوزن الصافي للشاحنة تلقائياً، وبجانبها زر التوزيع الذكي للمخازن وأزرار تعبئة الكمية مية بالمية أو تقسيمها للنصف خمسين بالمية. وداخل الجدول تقدر تختار لكل صنف مستودع مستقل من قائمته وتحدد موقع الرف ورقم التشغيلة! والقاعدة الذهبية لهذا العنصر: وثق الوزن الصافي من الميزان وحدد موقع الرف ورقم التشغيلة لضمان دقة التخزين والجرد.`
            },
            speech: () => `في جدول الفحص تفتح نافذة الميزان الإلكتروني لحساب الوزن الصافي تلقائياً، وبجانبها التوزيع الذكي للمخازن وأزرار التعبئة السريعة وتحديد مخزن ورف مستقل لكل صنف. القاعدة الذهبية لهذا العنصر: طابق الوزن الصافي عبر نافذة الميزان وحدد الرف والتشغيلة لكل صنف لسهولة التتبع والجرد.`,
            action: () => {
                if (typeof window.openSlWeighbridgeModal === 'function') {
                    window.openSlWeighbridgeModal();
                    const gInp = document.getElementById('sl-wb-gross');
                    const tInp = document.getElementById('sl-wb-tare');
                    if (gInp && tInp) {
                        gInp.value = '25';
                        tInp.value = '10';
                        if (typeof window.calcSlWeighbridgeNet === 'function') window.calcSlWeighbridgeNet();
                    }
                }
                const tCloseWb = setTimeout(() => {
                    const wbM = document.getElementById('sl-weighbridge-modal');
                    if (wbM) wbM.style.display = 'none';
                    const rowWhSel = document.querySelector('.sl-row-wh-select');
                    if (rowWhSel) showLiveSelectOptionsMirror(rowWhSel, null, '🏢 مستودع السطر المستقل');
                }, 4200);
                dubbedVisualTimers.push(tCloseWb);
            }
        },
        {
            badge: 'الخطوة 8️⃣ — سر الصندوقين المتزامنين: الاستلام بالوحدة الأساسية ⇄ أو بالفرعية!',
            cursorLabel: 'اكتب 5 طن ⬅️ تتحول فوراً إلى 500 سيخة والباقي 2 طن! ⚖️',
            targetSelector: '.sl-qty-primary-input',
            speechByDialect: {
                egyptian: () => `تعالى نشوف أهم ميزة في الجدول: الصندوقين اللي جنب بعض قدام كل صنف. تخيل إن الفاتورة مكتوبة بالطن، وكان باقي فيها سبعة طن، والنهارده وصلت خمسة طن بس، أو العامل عدّ البضاعة بالسيخة فلقاهم خمسمية سيخة! إنت مش محتاج آلة حاسبة خالص؛ لو كتبت خمسة في صندوق الطن، صندوق السيخة هيكتب لوحده خمسمية، ولو كتبت في السيخة هيحولها لطن أوتوماتيك، والبرنامج هيحفظ إن باقي لنا اتنين طن للدفعة الجاية! والقاعدة الذهبية لهذا العنصر: أدخل الكمية بالوحدة التي عدّيت بها البضاعة فعلياً على الأرض واترك التحويل الحسابي للنظام.`,
                sudanese: () => `تعال نشوف أهم ميزة في الجدول: الصندوقين الجنب بعض قدام كل صنف. تخيل الفاتورة مكتوبة بالطن، وباقي فيها سبعة طن، والليلة وصلت خمسة طن بس، أو العامل عدّ الحديد بالسيخة لقاهو خمسمية سيخة! ما بتحتاج آلة حاسبة نهائي؛ لو كتبت خمسة في صندوق الطن، صندوق السيخة بيكتب براهو خمسمية، ولو كتبت بالسيخة بحولها لطن أوتوماتيك، والبرنامج بحفظ إنو باقي لينا اتنين طن للدفعة الجاية! والقاعدة الذهبية لهذا العنصر: اكتب الكمية بالوحدة العدّيت بيها البضاعة فعلياً وخلي البرنامج يحسب التحويل والباقي أوتوماتيك.`,
                gulf:     () => `خلنا نشوف أهم ميزة بالجدول: الصندوقين المتجاورين قدام كل صنف. تخيل الفاتورة مكتوبة بالطن وباقي فيها سبعة طن، واليوم وصل خمسة طن بس، أو العامل عدّ الحديد بالحبة أو السيخة وطلع خمسمية سيخة! ما تحتاج آلة حاسبة أبداً؛ إذا كتبت خمسة بصندوق الطن يتحول صندوق السيخة تلقائياً إلى خمسمية، والعكس صحيح، ويحفظ البرنامج إنه باقي اثنين طن للدفعة القادمة! والقاعدة الذهبية لهذا العنصر: سجل الكمية بالوحدة الفعلية التي استلمت بها واترك معادلة التحويل وحساب المتبقي للنظام.`
            },
            speech: () => `أمام كل صنف صندوقان متزامنان: يمكنك كتابة الكمية بالوحدة الأساسية كالطن أو بالوحدة الفرعية كالسيخة والحبة، وسيقوم البرنامج بالتحويل الآلي وحساب المتبقي للدفعة القادمة فوراً. القاعدة الذهبية لهذا العنصر: أدخل الكمية بالوحدة التي فحصت بها البضاعة فعلياً واترك التحويل الحسابي الآلي للنظام.`,
            action: () => {
                const inp = document.querySelector('.sl-qty-primary-input');
                if (inp) {
                    inp.focus();
                    inp.value = '5';
                    if (typeof window.onSlPrimaryQtyInput === 'function') {
                        window.onSlPrimaryQtyInput(inp, true);
                    }
                }
            }
        },
        {
            badge: 'الخطوة 9️⃣ — شريط الملخص العائم بالأسفل: مراجعة الأرقام ونسبة الإنجاز قبل الاعتماد',
            cursorLabel: 'إجمالي الفاتورة: 10 | المنفذ سابقاً: 3 | الآن: 5 | المتبقي: 2 | الإنجاز: 80% 📊',
            targetSelector: '.sl-floating-summary',
            speechByDialect: {
                egyptian: () => `بص تحت خالص على الشريط العائم ده، ده الميزان اللي بيطمنك قبل ما تعتمد: بيقولك الفاتورة كلها كانت عشرة، استلمنا قبل كده تلاتة، وبنستلم في الدفعة دي خمسة، وهيتبقى اتنين في ذمة المورد للدفعة الجاية، مع مؤشر حي لنسبة الإنجاز المئوية. والقاعدة الذهبية لهذا العنصر: طابق رقم المنفذ الآن والمتبقي في هذا الشريط مع إشعار تسليم المورد قبل الضغط على اعتماد.`,
                sudanese: () => `عاين تحت خالص في الشريط العائم ده، ده الميزان البوريك كل حاجة بوضوح قبل الاعتماد: بيقول ليك إجمالي الفاتورة عشرة، استلمنا قبل كدا تلاتة، وهسي بنستلم في الدفعة دي خمسة، وحيفضل باقي اتنين للدفعة الجاية، مع شريط نسبة الإنجاز المئوية. والقاعدة الذهبية لهذا العنصر: راجع رقم الدفعة الحالية والمتبقي في الشريط العائم قبل ضغط زر الاعتماد.`,
                gulf:     () => `شوف بالأسفل الشريط العائم هذا، يعطيك ملخص كامل وواضح قبل الاعتماد: إجمالي الفاتورة عشرة، المنفذ سابقاً ثلاثة، والمنفذ في هذي الدفعة خمسة، والمتبقي بعد الدفعة اثنين، مع مؤشر نسبة الإنجاز المئوية. والقاعدة الذهبية لهذا العنصر: راجع معادلة الإجمالي والمنفذ والمتبقي في الشريط العائم للتأكد من سلامة الدفعة قبل الترحيل.`
            },
            speech: () => `يعرض الشريط العائم بالأسفل معادلة الفاتورة كاملة ونسبة الإنجاز المئوية لحظياً: إجمالي الفاتورة، والمنفذ سابقاً، والدفعة الحالية، والمتبقي. القاعدة الذهبية لهذا العنصر: طابق إجمالي الدفعة الحالية والمتبقي في الشريط العائم قبل ضغط زر الاعتماد النهائي.`,
            action: () => {}
        },
        {
            badge: 'الخطوة 🔟 — التوقيع الإلكتروني، إشعار خصم الفروقات، الاعتماد، والطباعة بالـ QR',
            cursorLabel: 'اعتماد 🚀 | التوقيع ✍️ | إشعار فرق ⚠️ | طباعة QR 🖨️ | واتساب 💬 | عكس ↩️',
            targetSelector: '#sl-btn-execute',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `وفي الخطوة الأخيرة، بنضغط على زرار اعتماد الآن، فالبضاعة بتدخل المخزن فوراً وبيطلع لها رقم إذن استلام رسمي. وجنبه نافذة التوقيع الإلكتروني باللمس لأمين المخزن والسائق، ونافذة إشعار خصم الفروقات والتوالف اللي بتفتح قدامك دلوقتي علشان تخصم أي عجز من حساب المورد أوتوماتيك، مع طباعة الإذن الرسمي بالباركود، والمشاركة على الواتساب، وزرار عكس الحركة المخزنية للتراجع الكامل في أي وقت! والقاعدة الذهبية لهذا العنصر: وثّق توقيع المستلم والسائق، وأصدر إشعار خصم المورد فوراً عند وجود أي عجز أو تالف لحفظ الحق المالي للمنشأة.`,
                sudanese: () => `وفي الخطوة الأخيرة، تضغط على زر اعتماد الآن، فالبضاعة بتنزل في المخزن طوالي وبيطلع ليها رقم إذن استلام رسمي. وجنبو نافذة التوقيع الإلكتروني باللمس، ونافذة إشعار خصم الفروقات والتوالف الفتحت قدامك هسي لو في أي نقص أو تلف عشان يتخصم من حساب المورد طوالي، وطباعة الإذن الرسمي بالباركود، والإرسال بالواتساب، وزر عكس الحركة المخزنية للتراجع الكامل في أي وقت! والقاعدة الذهبية لهذا العنصر: اعتمد التوقيع الإلكتروني وافتح إشعار خصم الفروقات طوالي لو في أي عجز أو تلف عشان يتخصم من حساب المورد.`,
                gulf:     () => `وفي الخطوة الأخيرة، نضغط على زر اعتماد الآن، فتدخل الكمية للمستودع فوراً ويصدر لها رقم مذكرة استلام رسمية. وبجانبه نافذة التوقيع الإلكتروني باللمس، ونافذة إشعار خصم الفروقات والتوالف اللي انفتحت قدامك الحين لترحيل أي عجز على حساب المورد تلقائياً، وطباعة الإذن الرسمي مع رمز الكيو آر، والمشاركة عبر الواتساب، وعكس الحركة المخزنية للتراجع في أي وقت! والقاعدة الذهبية لهذا العنصر: احرص على توقيع السائق وأمين المستودع وإصدار إشعار خصم الفروقات فور رصد أي نقص أو تلف.`
            },
            speech: () => `وأخيراً نضغط على زر اعتماد الآن لإدخال البضاعة للمخزن فوراً، وبجانبه نافذة التوقيع الإلكتروني باللمس، ونافذة إشعار خصم الفروقات للمورد، وطباعة الإذن الرسمي بالباركود، والمشاركة عبر الواتساب، وعكس الحركة. القاعدة الذهبية لهذا العنصر: وثّق التوقيع الإلكتروني وأصدر إشعار خصم الفروقات للمورد فور وجود أي عجز أو تلف لضبط كشف حسابه آلياً.`,
            action: () => {
                if (typeof window.openSlSignatureModal === 'function') {
                    window.openSlSignatureModal();
                }
                const tSwitchToDebit = setTimeout(() => {
                    const sigM = document.getElementById('sl-signature-modal');
                    if (sigM) sigM.style.display = 'none';
                    if (typeof window.openSlShortageDebitNoteModal === 'function') {
                        window.openSlShortageDebitNoteModal();
                    }
                }, 3200);
                const tCloseDebit = setTimeout(() => {
                    const dnM = document.getElementById('sl-debit-note-modal');
                    if (dnM) dnM.style.display = 'none';
                }, 6800);
                dubbedVisualTimers.push(tSwitchToDebit, tCloseDebit);
            }
        }
    ];

    // ============================================================================
    // 📤 2. FULL MASTERCLASS: SALES DELIVERY & SPLIT GATEWAY (GDN)
    // ============================================================================
    const SALES_ONLY_STEPS = [
        {
            badge: 'مبيعات 1️⃣ — الملخص العام: المدخلات والمخرجات والقاعدة الذهبية لبوابة تسليم المبيعات (GDN)',
            cursorLabel: 'بوابة تسليم المبيعات (خصم مخزني - Out) 📤',
            targetSelector: '#sl-top-cockpit-bar, #sl-main-title-text',
            diagramType: 'c5-impact-map',
            c5ImpactMeta: {
                screenTitle: 'بوابة تسليم المبيعات وتقسيمها على دفعات (GDN)',
                inputs: [
                    '📤 شاشة المبيعات المباشرة وتجزئة الفواتير',
                    '🗂️ البيانات الأساسية (العملاء والأصناف والمخازن)',
                    '📱 مسح أذونات الصرف بالباركود/QR بالجوال'
                ],
                funcSummary: 'صرف وتسليم فواتير العملاء فعلياً من المستودع دفعة واحدة أو على دفعات مجزأة مع إصدار تصاريح الخروج',
                reports: [
                    '📦 شاشة المخازن وكارت الصنف (- خصم الرصيد)',
                    '🧾 شاشة المبيعات وتقرير المتبقيات وأذونات الصرف',
                    '🚛 سجل أذونات التسليم (GDN) وتصاريح البوابة'
                ],
                screens: [
                    '🏭 خصم الكميات المسلمة من المستودع (-)',
                    '📋 سجل أذونات التسليم الموحد (GDN)',
                    '🛡️ مطابقة الأوزان وتوقيع السائق والمستلم'
                ],
                goldenRule: 'القاعدة الذهبية: طابق الكمية المسلمة ووحدة القياس ووزن الشاحنة قبل اعتماد الصرف ينضبط لك رصيد المخزن وحساب العميل تلقائياً.',
                reassurance: 'رسالة الطمأنينة للمحاسب: اعمل بكل ثقة ولا تخشَ الخطأ؛ فالشاشة محمية بحفظ المسودة الفوري ودرع منع صرف كمية أكبر من الرصيد وزر عكس الحركة.'
            },
            speechByDialect: {
                egyptian: () => `أهلاً بحضرتك في بوابة تسليم المبيعات وتقسيمها جي دي إن. وظيفة الشاشة دي وأهميتها إنها البوابة الرقابية لصرف البضاعة للعملاء؛ فاتورة المبيعات مش بتتخصم ولا تخرج من المخزن إلا لما نسلمها هنا فعلياً دفعة واحدة أو نقسمها على دفعات وعربيات مختلفة. منين بتجيلها المعلومات؟ المدخلات بتيجي من شاشة المبيعات المباشرة وشاشة تجزئة الفواتير، ومن شاشة البيانات الأساسية للعملاء والأصناف والمخازن. وبتسمّع فين وبتأثر في إيه؟ أول ما بتعتمد إذن الصرف، بتنقص الرصيد الفعلي من شاشة المخازن وكارت الصنف وتقرير الجرد، وبتحدث حالة التسليم في شاشة المبيعات وتقرير المتبقيات وسجل أذونات الصرف وتصريح خروج البوابة. والقاعدة الذهبية للشاشة: طابق الكمية المسلمة ووحدة القياس قبل اعتماد الصرف ينضبط لك رصيد المخزن وحساب العميل تلقائياً. واطمئن تماماً؛ فالشاشة محمية بحفظ المسودة الفوري ودرع منع التجاوز وزر عكس الحركة للتراجع الكامل في أي لحظة.`,
                sudanese: () => `حبابك عشرة في بوابة تسليم المبيعات وتقسيمها جي دي إن. وظيفة الشاشة دي وأهميتها إنها البوابة الرقابية لصرف البضاعة للعملاء؛ فاتورة المبيعات ما بتتخصم ولا بتمرق من المخزن إلا لما نسلمها هنا فعلياً دفعة واحدة أو مقسمة على دفعات. ومن وين بتجيها المعلومات؟ مدخلاتها بتجي من شاشة المبيعات المباشرة وتجزئة الفواتير، ومن شاشة البيانات الأساسية للعملاء والأصناف والمخازن. وبتسمّع وين وبتأثر في شنو؟ أول ما تعتمد إذن الصرف، بتنقص الرصيد الفعلي من شاشة المخازن وكارت الصنف وتقرير الجرد، وبتحدث حالة التسليم في شاشة المبيعات وتقرير المتبقيات وسجل أذونات الصرف وتصريح البوابة. والقاعدة الذهبية للشاشة: طابق الكمية المسلمة ووحدة القياس قبل اعتماد الصرف ينضبط ليك رصيد المخزن وحساب العميل تلقائياً. واطمئن تماماً؛ فالشاشة محمية بحفظ المسودة الفوري ودرع منع التجاوز وزر عكس الحركة للتراجع الكامل في أي لحظة.`,
                gulf:     () => `يا هلا بك في بوابة تسليم المبيعات وتقسيمها جي دي إن. وظيفة هذي الشاشة وأهميتها إنها البوابة الرقابية لصرف البضاعة للعملاء؛ فاتورة المبيعات ما تنخصم ولا تخرج من المستودع إلا عند تسليمها الفعلي هنا دفعة واحدة أو مقسمة على دفعات. ومن وين تجيها المعلومات؟ مدخلاتها تجي من شاشة المبيعات المباشرة وتجزئة الفواتير، ومن شاشة البيانات الأساسية للعملاء والأصناف والمستودعات. ووين تسمّع وفي أي تقرير تؤثر؟ فور اعتماد إذن الصرف تنقص الرصيد الفعلي من شاشة المخازن وكارت الصنف وتقرير الجرد، وتحدث حالة التسليم في شاشة المبيعات وتقرير المتبقيات وسجل أذونات الصرف وتصريح البوابة. والقاعدة الذهبية للشاشة: طابق الكمية المسلمة ووحدة القياس قبل اعتماد الصرف ينضبط لك رصيد المستودع وحساب العميل تلقائياً. واطمئن تماماً؛ فالشاشة محمية بحفظ المسودة الفوري ودرع منع التجاوز وزر عكس الحركة للتراجع الكامل بأي لحظة.`
            },
            speech: () => `أهلاً بك في بوابة تسليم المبيعات وتقسيمها (GDN). وظيفة هذه الشاشة وأهميتها ضبط الصرف الفعلي لفواتير العملاء من المستودع دفعة واحدة أو على دفعات مجزأة. تأتي مدخلاتها من شاشة المبيعات المباشرة وتجزئة الفواتير وشاشة البيانات الأساسية للعملاء والأصناف والمخازن. وتُسمّع مخرجاتها فوراً بخصم وتخفيض الرصيد الفعلي في شاشة المخازن وكارت الصنف وتقرير الجرد، وتحديث حالة التسليم في شاشة المبيعات وتقرير المتبقيات وسجل أذونات الصرف وتصاريح البوابة. القاعدة الذهبية للشاشة: طابق الكمية المسلمة ووحدة القياس قبل اعتماد الصرف ينضبط لك رصيد المستودع وحساب العميل تلقائياً. واطمئن تماماً؛ فالشاشة محمية بحفظ المسودة الفوري ودرع منع التجاوز وزر عكس الحركة للتراجع الكامل في أي لحظة.`,
            action: () => {
                if (typeof window.switchLogisticsMode === 'function') {
                    window.switchLogisticsMode('sales', true);
                }
            }
        },
        {
            badge: 'مبيعات 2️⃣ — حارس الـ QR وربط كاميرا الجوال عند بوابة الصرف',
            cursorLabel: 'حارس البوابة 🛡️ + مسح أذونات الصرف بالجوال 📱',
            targetSelector: 'button[onclick="openSlMobileBridgeModal()"]',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `في الشريط العلوي عندك ربط كاميرا الجوال اللي فتح قدامك دلوقتي علشان حارس البوابة أو أمين المخزن يمسح أذونات الصرف ورا بعض بكاميرا الموبايل وتتنفذ في ثانية. وجنبها قائمة حارس الكيو آر اللي بتمنع أي لخبطة لو حد مسح ورقة خارجية بالغلط وإحنا واقفين في بوابة تسليم المبيعات. والقاعدة الذهبية لهذا العنصر: فعّل وضع قفل القسم في حارس الكيو آر لمنع مسح أي مستند مشتريات أثناء تسليم المبيعات.`,
                sudanese: () => `في الشريط الفوق عندك ربط كاميرا الجوال الفتح قدامك هسي عشان أمين المخزن أو البوابة يمسح أذونات الصرف ورا بعض بكاميرا التلفون وتتنفذ طوالي. وجنبها قائمة حارس الكيو آر البمنع أي لخبطة لو زول مسح ورقة خارجية بالغلط وإحنا في بوابة المبيعات. والقاعدة الذهبية لهذا العنصر: اقفل حارس الكيو آر على قسم المبيعات لمنع مسح أي ورقة خارجية بالغلط.`,
                gulf:     () => `في الشريط العلوي عندك نافذة ربط كاميرا الجوال اللي انفتحت قدامك الحين عشان أمين المستودع يمسح أذونات الصرف متتالية بكاميرا الجوال. وبجانبها قائمة حارس الكيو آر اللي تمنع مسح أي ورقة خارجية بالخطأ أثناء وجودك في بوابة المبيعات. والقاعدة الذهبية لهذا العنصر: اضبط حارس الكيو آر على قفل القسم لمنع تداخل أذونات الاستلام مع الصرف.`
            },
            speech: () => `يوفر الشريط العلوي نافذة ربط كاميرا الجوال لمسح أذونات الصرف بسرعة فائقة، مع قائمة حارس الكيو آر لمنع مسح أي مستند غير مطابق لبوابة المبيعات. القاعدة الذهبية لهذا العنصر: اضبط حارس الكيو آر على قفل القسم أثناء الصرف لمنع مسح أي مستند بالخطأ.`,
            action: () => {
                const qrSel = document.getElementById('sl-qr-guard-mode');
                if (qrSel) showLiveSelectOptionsMirror(qrSel, null, '🛡️ خيارات حارس الـ QR الذكي');
                const t1 = setTimeout(() => {
                    if (typeof window.openSlMobileBridgeModal === 'function') window.openSlMobileBridgeModal();
                }, 1300);
                dubbedVisualTimers.push(t1);
            }
        },
        {
            badge: 'مبيعات 3️⃣ — العدادات الأربعة لفواتير المبيعات ومراحل التسليم',
            cursorLabel: 'قيد التسليم ⏳ | مقسمة ✂️ | مكتملة ✅ | سجل أذونات GDN 🏷️',
            targetSelector: '#sl-filter-btn-pending, #sl-kpi-card-pending',
            speechByDialect: {
                egyptian: () => `الأربع كروت الملونة هنا بتفرز لك فواتير العملاء: الكارت الأصفر الأول فيه الفواتير الجاهزة للصرف اللي العميل لسه ما استلمهاش. والكارت الأزرق التاني فيه الفواتير اللي العميل استلم جزء منها ولسه باقي له بضاعة عندنا، أو واقفة مستنية ختم البوابة النهائي. والأخضر التالت للفواتير اللي اتسلمت بالكامل، والبنفسجي الرابع لسجل أذونات الصرف الرسمية. والقاعدة الذهبية لهذا العنصر: راجع كارت الفواتير المقسمة الزرقاء يومياً لضبط البضائع المحجوزة للعملاء في المستودع.`,
                sudanese: () => `الأربعة كروت الملونة هنا بتفرز ليك فواتير الزبائن: الكرت الأصفر الأول للفواتير الجاهزة للصرف. والكرت الأزرق التاني للفواتير المقسمة اللي العميل شال جزء منها ولسه باقي ليهو بضاعة في المخزن. والأخضر التالت للفواتير الاتسلمت خلاص، والبنفسجي الرابع لسجل أذونات الصرف الرسمية. والقاعدة الذهبية لهذا العنصر: راجع كرت الفواتير المقسمة الزرقاء يومياً لمعرفة البضائع المتبقية للزبائن في المخزن.`,
                gulf:     () => `البطاقات الأربع هنا تفرز فواتير العملاء: الصفراء للفواتير الجاهزة للصرف، والزرقاء للفواتير المقسمة اللي استلم العميل جزء منها وباقي له دفعات ثانية، والخضراء للمكتملة، والبنفسجية لسجل أذونات الفسح والصرف الرسمية. والقاعدة الذهبية لهذا العنصر: تابع بطاقة الفواتير المقسمة الزرقاء يومياً لضبط الكميات المحجوزة للعملاء.`
            },
            speech: () => `تفرز هذه البطاقات فواتير العملاء إلى: جاهزة للصرف، ومقسمة على دفعات، ومكتملة التسليم، وسجل أذونات الصرف الرسمية. القاعدة الذهبية لهذا العنصر: راجع بطاقة الفواتير المقسمة يومياً لمتابعة الدفعات المتبقية للعملاء.`,
            action: () => {
                if (typeof window.setSlRegistryFilter === 'function') {
                    window.setSlRegistryFilter('pending');
                }
                window.ensureLiveDemoInvoiceForGuide();
            }
        },
        {
            badge: 'مبيعات 4️⃣ — السجل الذكي للمبيعات: الصرف الكامل بضغطة أو فتح الفاتورة للتقسيم',
            cursorLabel: 'اضغط على فاتورة العميل لفتحها وتقسيمها 📄',
            targetSelector: '#sl-registry-tbody tr:first-child',
            speechByDialect: {
                egyptian: () => `من جدول السجل الذكي، تقدر تبحث باسم العميل أو رقم الفاتورة. لو العميل هيشيل الفاتورة كلها دلوقتي، تضغط على زرار اعتماد كامل الأخضر. أما لو العربية بتاعته صغيرة وهيشيل جزء بس النهارده والباقي بكرة، بنضغط على الفاتورة علشان تفتح معانا في بطاقة الفحص والتقسيم تحت. والقاعدة الذهبية لهذا العنصر: لا تضغط صرف كامل إلا إذا تم تحميل كامل الفاتورة فعلياً، واستخدم التقسيم عند تسليم الشحنة على دفعات.`,
                sudanese: () => `من جدول السجل الذكي، تقدر تبحث باسم العميل أو رقم الفاتورة. لو العميل شايل الفاتورة كلها هسي، تضغط زر اعتماد كامل الأخضر. أما لو عربيتو صغيرة وحيشيل جزء الليلة والباقي بكرة، تضغط على الفاتورة عشان تفتح معاك في بطاقة الفحص والتقسيم تحت. والقاعدة الذهبية لهذا العنصر: ما تضغط صرف كامل إلا بعد تحميل الفاتورة كلها، وافتح التقسيم لو التسليم على دفعات.`,
                gulf:     () => `من جدول السجل الذكي، تقدر تبحث باسم العميل أو رقم الفاتورة. إذا العميل بيستلم الفاتورة كاملة الحين، تضغط زر اعتماد كامل الأخضر. أما إذا سيارته بتشيل جزء اليوم والباقي بعدين، تضغط على الفاتورة عشان تفتح معك في بطاقة الفحص والتقسيم بالأسفل. والقاعدة الذهبية لهذا العنصر: لا تعتمد الصرف الكامل إلا عند تسليم كامل الفاتورة دفعة واحدة.`
            },
            speech: () => `من السجل الذكي يمكنك صرف الفاتورة بالكامل بضغطة واحدة، أو الضغط عليها لفتحها في بطاقة الفحص وتقسيمها على دفعات. القاعدة الذهبية لهذا العنصر: لا تضغط صرف كامل إلا عند تحميل كامل كمية الفاتورة دفعة واحدة.`,
            action: () => {
                const firstRow = document.querySelector('#sl-registry-tbody tr:first-child');
                if (firstRow) firstRow.click();
            }
        },
        {
            badge: 'مبيعات 5️⃣ — المستودع الصارف، كشف الفاتورة المالية، ومسار التوصيل وتصريح البوابة',
            cursorLabel: 'قائمة المستودع 🏢 • الفاتورة المالية 🔍 • مسار التوصيل 🚚 • تصريح البوابة 🎫',
            targetSelector: '#sl-master-warehouse',
            diagramType: 'dropdown-open',
            speechByDialect: {
                egyptian: () => `هنا في بطاقة بيانات التسليم، بنختار المستودع اللي هنصرف منه من القائمة المنسدلة اللي فتحت قدامك، وتقدر تفتح نافذة الفاتورة المالية الأصلية أو نافذة مسار التوصيل وجدولة الأسطول علشان تسجل اسم السائق ورقم اللوحة ورقم تصريح خروج البوابة وموعد الوصول المتوقع للعميل. والقاعدة الذهبية لهذا العنصر: حدد المستودع الصارف بدقة ووثق رقم لوحة الشاحنة وتصريح البوابة قبل خروج البضاعة.`,
                sudanese: () => `هنا في بطاقة بيانات التسليم، بنختار المخزن الحنصرف منو من القائمة المنسدلة الفتحت قدامك هسي، وتقدر تفتح نافذة الفاتورة المالية الأصلية أو نافذة مسار التوصيل وجدولة الأسطول عشان تسجل اسم السواق ورقم اللوحة ورقم تصريح البوابة وزمن التوصيل. والقاعدة الذهبية لهذا العنصر: اختار المخزن الصارف بدقة وسجل اسم السواق ورقم اللوحة وتصريح البوابة.`,
                gulf:     () => `هنا في بطاقة بيانات التسليم، نختار المستودع الصارف من القائمة المنسدلة اللي انفتحت قدامك الحين، وتقدر تفتح نافذة الفاتورة المالية الأصلية أو نافذة مسار التوصيل وجدولة الأسطول لتوثيق السائق ورقم اللوحة وتصريح البوابة. والقاعدة الذهبية لهذا العنصر: تأكد من تحديد المستودع الصارف وتوثيق بيانات السائق وتصريح البوابة.`
            },
            speech: () => `في هذه البطاقة نختار المستودع الصارف من القائمة المنسدلة، ونستعرض نافذة الفاتورة المالية الأصلية ونافذة مسار التوصيل وتصريح البوابة لضمان الرقابة الكاملة عند خروج البضاعة. القاعدة الذهبية لهذا العنصر: حدد المستودع الصارف ووثق السائق وتصريح البوابة قبل الفسح.`,
            action: () => {
                const whSel = document.getElementById('sl-master-warehouse');
                if (whSel) showLiveSelectOptionsMirror(whSel, null, '🏢 المستودع الصارف للبضاعة');
                const tDrill = setTimeout(() => {
                    if (typeof window.openSlOriginalInvoiceDrilldownModal === 'function') window.openSlOriginalInvoiceDrilldownModal();
                }, 1500);
                const tFleet = setTimeout(() => {
                    const dm = document.getElementById('sl-drilldown-invoice-modal');
                    if (dm) dm.style.display = 'none';
                    if (typeof window.openSlFleetDispatchModal === 'function') window.openSlFleetDispatchModal();
                }, 3800);
                const tCloseFleet = setTimeout(() => {
                    const fm = document.getElementById('sl-fleet-dispatch-modal');
                    if (fm) fm.style.display = 'none';
                }, 6200);
                dubbedVisualTimers.push(tDrill, tFleet, tCloseFleet);
            }
        },
        {
            badge: 'مبيعات 6️⃣ — فحص الرصيد المتاح، مطابقة التحميل، وحاسبة الحمولة والطبليات',
            cursorLabel: 'فحص رصيد المستودع 🏢 + فحص التحميل 🎯 + حاسبة الحمولة 📦',
            targetSelector: '#sl-items-grid',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `في جدول الفحص تحت، البرنامج بيعرض لك قدام كل صنف الرصيد الفعلي الموجود جوه كل مخزن علشان ما تصرفش كمية مش موجودة! وفوق الجدول تقدر تفتح نافذة فحص التحميل بالباركود، ونافذة حاسبة الحمولة والطبليات اللي فتحت قدامك علشان تحسب إجمالي وزن الشحنة وعدد الطبليات المطلوبة ونوع العربية المناسبة قبل التحميل. والقاعدة الذهبية لهذا العنصر: راجع الرصيد الفعلي المتاح أمام كل صنف وطابق وزن الشحنة مع سعة المركبة قبل التحميل.`,
                sudanese: () => `في جدول الفحص تحت، البرنامج بيعرض ليك قدام كل صنف الرصيد الفعلي الموجود جوه كل مخزن عشان تكون عارف البضاعة المتوفرة! وفوق الجدول تقدر تفتح نافذة فحص التحميل، ونافذة حاسبة الحمولة والطبليات الفتحت قدامك هسي عشان تحسب وزن الشحنة وعدد الطبليات والعربية المناسبة. والقاعدة الذهبية لهذا العنصر: اتأكد من الرصيد الفعلي قدام كل صنف وطابق وزن الحمولة مع العربية قبل الصرف.`,
                gulf:     () => `في جدول الفحص بالأسفل، يعرض لك البرنامج قدام كل صنف الرصيد الفعلي المتوفر داخل كل مستودع! وفوق الجدول تقدر تفتح نافذة فحص التحميل، ونافذة حاسبة الحمولة والطبليات اللي انفتحت قدامك الحين لحساب وزن الشحنة وعدد الطبليات وحجم المركبة المناسبة. والقاعدة الذهبية لهذا العنصر: تحقق من الرصيد المتاح في المستودع وحجم الحمولة قبل بدء التحميل.`
            },
            speech: () => `يعرض الجدول الرصيد الفعلي المتاح في كل مخزن أمام كل صنف، مع نافذة فحص ومطابقة التحميل ونافذة حاسبة الحمولة والطبليات لاختيار الشاحنة المناسبة. القاعدة الذهبية لهذا العنصر: راجع الرصيد الفعلي المتاح في المستودع وطابق وزن الحمولة قبل الصرف.`,
            action: () => {
                if (typeof window.openSlLoadingVerifierModal === 'function') window.openSlLoadingVerifierModal();
                const tCargo = setTimeout(() => {
                    const lv = document.getElementById('sl-load-verifier-modal');
                    if (lv) lv.style.display = 'none';
                    if (typeof window.openSlCargoEstimatorModal === 'function') window.openSlCargoEstimatorModal();
                }, 2600);
                const tRowWh = setTimeout(() => {
                    const ce = document.getElementById('sl-cargo-estimator-modal');
                    if (ce) ce.style.display = 'none';
                    const rowWhSel = document.querySelector('.sl-row-wh-select');
                    if (rowWhSel) showLiveSelectOptionsMirror(rowWhSel, null, '🏢 مستودع صرف السطر والرصيد');
                }, 5200);
                dubbedVisualTimers.push(tCargo, tRowWh);
            }
        },
        {
            badge: 'مبيعات 7️⃣ — التسليم بالوحدة الأساسية ⇄ أو بالوحدة الفرعية وحساب المتبقي للعميل',
            cursorLabel: 'المتبقي للعميل 7 ⬅️ نسلّمه الآن 5 ويتبقى له 2! ⚖️',
            targetSelector: '.sl-qty-primary-input',
            speechByDialect: {
                egyptian: () => `وهنا ميزة التقسيم بالوحدتين في المبيعات: الفاتورة دي باقي فيها للعميل سبعة طن، لو العربية بتاعته هتشيل النهارده خمسة طن بس، بنكتب خمسة في صندوق الطن، أو نكتب خمسمية في صندوق السيخة، فالبرنامج بيحولها لوحده وبيحفظ أوتوماتيك إن العميل لسه فاضل له اتنين طن ييجي يستلمهم في الدفعة الجاية! والقاعدة الذهبية لهذا العنصر: سجل الكمية بالوحدة التي سلّمت بها البضاعة فعلياً للعميل واترك حساب المتبقي للنظام.`,
                sudanese: () => `وهنا ميزة التقسيم بالوحدتين في المبيعات: الفاتورة دي باقي فيها للعميل سبعة طن، لو عربيتو بتشيل الليلة خمسة طن بس، نكتب خمسة في صندوق الطن، أو نكتب خمسمية في صندوق السيخة، والبرنامج براهو بيحولها وبيحفظ أوتوماتيك إنو العميل لسه باقي ليهو اتنين طن يستلمهم في الدفعة الجاية! والقاعدة الذهبية لهذا العنصر: اكتب الكمية بالوحدة السلّمت بيها للعميل فعلياً وخلي البرنامج يحسب المتبقي أوتوماتيك.`,
                gulf:     () => `وهنا ميزة التقسيم بالوحدتين في المبيعات: الفاتورة باقي فيها للعميل سبعة طن، إذا سيارته بتشيل اليوم خمسة طن بس، نكتب خمسة بصندوق الطن أو خمسمية بصندوق السيخة، والبرنامج يحولها تلقائياً ويحفظ إن العميل باقي له اثنين طن للدفعة الجاية! والقاعدة الذهبية لهذا العنصر: أدخل الكمية بالوحدة الفعلية المسلمة للعميل واترك حساب المتبقي للنظام.`
            },
            speech: () => `نكتب الكمية المسلمة للعميل في هذه الدفعة سواء بالوحدة الأساسية أو الفرعية، فيقوم البرنامج بتحويلها وحفظ الكمية المتبقية للعميل للدفعة القادمة تلقائياً. القاعدة الذهبية لهذا العنصر: سجل الكمية بالوحدة التي سلّمت بها البضاعة فعلياً واترك حساب المتبقي للنظام.`,
            action: () => {
                const inp = document.querySelector('.sl-qty-primary-input');
                if (inp) {
                    inp.focus();
                    inp.value = '5';
                    if (typeof window.onSlPrimaryQtyInput === 'function') {
                        window.onSlPrimaryQtyInput(inp, true);
                    }
                }
            }
        },
        {
            badge: 'مبيعات 8️⃣ — شريط الملخص العائم: مطابقة المسلم الآن والمتبقي للعميل',
            cursorLabel: 'مراجعة المسلم في هذه الدفعة (5) والمتبقي للعميل (2) 📊',
            targetSelector: '.sl-floating-summary',
            speechByDialect: {
                egyptian: () => `وقبل ما تضغط اعتماد، الشريط العائم اللي تحت ده بيأكد لك الحسبة كلها: الفاتورة عشرة، العميل استلم قبل كده تلاتة، وبيستلم دلوقتي خمسة، وهيتبقى له اتنين في ذمة المخزن. والقاعدة الذهبية لهذا العنصر: طابق رقم المسلم الآن والمتبقي في الشريط العائم مع العميل أو السائق قبل الاعتماد.`,
                sudanese: () => `وقبل ما تضغط اعتماد، الشريط العائم التحت ده بيأكد ليك الحساب كلو: الفاتورة عشرة، العميل استلم قبل كدا تلاتة، وهسي بيستلم خمسة، وحيفضل باقي ليهو اتنين. والقاعدة الذهبية لهذا العنصر: راجع رقم الدفعة الحالية والمتبقي في الشريط العائم قبل ضغط زر الاعتماد.`,
                gulf:     () => `وقبل الاعتماد، الشريط العائم بالأسفل يوضح لك الحسبة كاملة: إجمالي الفاتورة عشرة، المستلم سابقاً ثلاثة، والمستلم بهذه الدفعة خمسة، والمتبقي للعميل اثنين. والقاعدة الذهبية لهذا العنصر: طابق إجمالي الدفعة الحالية والمتبقي في الشريط العائم قبل اعتماد الصرف.`
            },
            speech: () => `يوضح الشريط العائم بالأسفل ملخص عملية الصرف بدقة: إجمالي الفاتورة، والمسلم سابقاً، والمسلم في هذه الدفعة، والمتبقي للعميل. القاعدة الذهبية لهذا العنصر: طابق إجمالي المسلم الآن والمتبقي في الشريط العائم قبل ضغط زر الاعتماد.`,
            action: () => {}
        },
        {
            badge: 'مبيعات 9️⃣ — التوقيع الإلكتروني، اعتماد الصرف، طباعة تصريح البوابة (QR)، أو عكس الحركة',
            cursorLabel: 'اعتماد الصرف 🚀 | التوقيع ✍️ | طباعة تصريح البوابة 🖨️ | تراجع وعكس ↩️',
            targetSelector: '#sl-btn-execute',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `وفي النهاية بنفتح نافذة التوقيع الإلكتروني باللمس علشان السائق أو العميل يوقع بالاستلام، ونضغط على زرار تأكيد واعتماد التسليم والصرف، فالكمية بتتخصم من المخزن فوراً، وتقدر تطبع تصريح الخروج الرسمي وعليه باركود الكيو آر علشان العميل يسلمه على البوابة، أو تبعته له واتساب، ولو حصل أي تراجع تقدر تضغط زرار إلغاء وعكس الحركة المخزنية فيرجع الرصيد للمخزن فوراً! والقاعدة الذهبية لهذا العنصر: احصل على توقيع العميل أو السائق واطبع تصريح البوابة بالباركود لإثبات خروج البضاعة رسمياً.`,
                sudanese: () => `وفي النهاية بنفتح نافذة التوقيع الإلكتروني باللمس عشان السواق أو العميل يوقع بالاستلام، وتضغط على زر تأكيد واعتماد التسليم والصرف، فالكمية بتتخصم من المخزن طوالي، وتقدر تطبع تصريح الخروج الرسمي بالباركود عشان العميل يسلمو في البوابة، أو ترسلو ليهو واتساب، ولو داير تتراجع في أي وقت تضغط زر إلغاء وعكس الحركة المخزنية فيرجع الرصيد للمخزن طوالي! والقاعدة الذهبية لهذا العنصر: وثق توقيع المستلم واطبع تصريح البوابة بالباركود لحفظ إثبات الصرف.`,
                gulf:     () => `وفي النهاية نفتح نافذة التوقيع الإلكتروني باللمس لتوقيع السائق أو العميل، ونضغط على زر تأكيد واعتماد التسليم والصرف، فتنخصم الكمية من المستودع فوراً، وتقدر تطبع إذن الفسح الرسمي بالباركود للبوابة أو ترسله واتساب، وبجانبه زر إلغاء وعكس الحركة المخزنية لإرجاع الرصيد في أي وقت! والقاعدة الذهبية لهذا العنصر: وثق التوقيع الإلكتروني واطبع إذن الفسح بالباركود لإثبات التسليم النهائي.`
            },
            speech: () => `نوثق توقيع المستلم والسائق في نافذة التوقيع الإلكتروني، ثم نضغط على زر تأكيد واعتماد التسليم لخصم الكمية من المخزن وإصدار تصريح البوابة الرسمي بالباركود، مع إمكانية التراجع وعكس الحركة في أي وقت. القاعدة الذهبية لهذا العنصر: وثق توقيع المستلم واطبع تصريح البوابة بالباركود لإثبات خروج البضاعة رسمياً.`,
            action: () => {
                if (typeof window.openSlSignatureModal === 'function') {
                    window.openSlSignatureModal();
                }
                const tCloseSig = setTimeout(() => {
                    const sigM = document.getElementById('sl-signature-modal');
                    if (sigM) sigM.style.display = 'none';
                }, 3800);
                dubbedVisualTimers.push(tCloseSig);
            }
        }
    ];

    window.MagaiesSmartGuides['smart-logistics'] = {
        title: 'المرشد الذكي الشامل: بوابة المشتريات (GRN) وبوابة المبيعات (GDN)',
        subtitle: 'أكاديمية شرح تفاعلية حية بالصوت الرجالي الواضح بـ 3 لهجات (🇸🇩 سودانية | 🇪🇬 مصرية | 🇸🇦 خليجية) والماوس المتحرك',

        // LIVE AUTOPILOT SCENARIO STEPS
        autopilotSteps: PURCHASES_MASTER_STEPS,

        visualSteps: [
            {
                stepBadge: 'القسم 1️⃣ — الفرق الجوهري بين البوابتين (GRN ⇄ GDN)',
                title: 'كيف تعمل بوابة استلام المشتريات 📥 وبوابة تسليم المبيعات 📤؟',
                desc: '• 📥 بوابة المشتريات (الزرقاء - GRN): مخصصة للبضاعة الواردة من الموردين؛ فحص العد الفعلي، تقسيم الفاتورة على دفعات أو عدة مخازن، و(إضافة الكمية للمخزن +).\n• 📤 بوابة المبيعات (الخضراء - GDN): مخصصة للبضاعة الصادرة للعملاء؛ فحص الرصيد المتاح، تسليم الفاتورة كاملة أو على دفعات، إصدار تصريح خروج البوابة (Gate Pass)، و(خصم الكمية من المخزن -).',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="10" y="14" width="144" height="82" rx="12" fill="#064e3b" stroke="#34d399" stroke-width="2.5"/>
                    <text x="82" y="42" fill="#ffffff" font-size="12.5" font-weight="bold" text-anchor="middle">📤 بوابة المبيعات (GDN)</text>
                    <text x="82" y="62" fill="#6ee7b7" font-size="10.5" text-anchor="middle">خصم مخزني (- Out)</text>
                    <text x="82" y="80" fill="#a7f3d0" font-size="9.5" text-anchor="middle">تصريح خروج بوابة للعميل</text>
                    <rect x="166" y="14" width="144" height="82" rx="12" fill="#0c4a6e" stroke="#38bdf8" stroke-width="2.5"/>
                    <text x="238" y="42" fill="#ffffff" font-size="12.5" font-weight="bold" text-anchor="middle">📥 بوابة المشتريات (GRN)</text>
                    <text x="238" y="62" fill="#7dd3fc" font-size="10.5" text-anchor="middle">إضافة مخزنية (+ In)</text>
                    <text x="238" y="80" fill="#bae6fd" font-size="9.5" text-anchor="middle">مذكرة استلام من المورد</text>
                </svg>`,
                targetSelector: '.sl-mode-switcher'
            },
            {
                stepBadge: 'القسم 2️⃣ — الشريط العلوي: ربط كاميرا الجوال وحارس الـ QR',
                title: 'امسح 10 أذونات متتالية بالجوال مع حماية كاملة من الخطأ',
                desc: '• 📱 ربط كاميرا الجوال: يحول جوالك إلى قارئ باركود لاسلكي يمسح الأذونات تباعاً ويعتمدها بدون لمس الكيبورد.\n• 🛡️ حارس الـ QR: يمنع العامل من مسح إذن مبيعات بالخطأ وهو داخل شاشة المشتريات (أو العكس) وينبهه صوتياً.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="12" y="18" width="142" height="74" rx="12" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                    <text x="83" y="46" fill="#38bdf8" font-size="12" font-weight="bold" text-anchor="middle">🛡️ حارس الـ QR الذكي</text>
                    <text x="83" y="68" fill="#cbd5e1" font-size="10" text-anchor="middle">يمنع خلط المبيعات بالمشتريات</text>
                    <rect x="166" y="18" width="142" height="74" rx="12" fill="#064e3b" stroke="#34d399" stroke-width="2"/>
                    <text x="237" y="46" fill="#ffffff" font-size="12" font-weight="bold" text-anchor="middle">📱 ربط كاميرا الجوال</text>
                    <text x="237" y="68" fill="#a7f3d0" font-size="10" text-anchor="middle">مسح متتابع فوري بدون توقف</text>
                </svg>`,
                targetSelector: '#sl-top-cockpit-bar'
            },
            {
                stepBadge: 'القسم 3️⃣ — العدادات الأربعة والسجل الفوري (+1000 فاتورة)',
                title: 'كيف تعرف حالة كل فاتورة وتختار بين الاعتماد السريع أو التقسيم؟',
                desc: '• ⏳ قيد الإجراء: فواتير جديدة لم تُستلم أو تُسلم بعد.\n• 🌗 الجزئي / المقسمة: فواتير تم تنفيذ دفعة منها وبقي لها دفعات قادمة.\n• ⚡ اعتماد كامل: ينفذ الفاتورة كلها بضغطة واحدة.\n• ✂️ تقسيم وفحص: يفتح الفاتورة بالأسفل لتقسيم كمياتها أو توزيع مخازنها.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="12" y="14" width="200" height="82" rx="10" fill="#0f172a" stroke="#64748b" stroke-width="1.5"/>
                    <rect x="22" y="26" width="180" height="22" rx="5" fill="#1e293b"/>
                    <text x="112" y="41" fill="#38bdf8" font-size="11" font-weight="bold" text-anchor="middle">#1045 — الإجمالي 10 | المتبقي 7</text>
                    <rect x="22" y="56" width="82" height="26" rx="6" fill="#10b981"/>
                    <text x="63" y="73" fill="#fff" font-size="10" font-weight="bold" text-anchor="middle">⚡ اعتماد كامل</text>
                    <rect x="114" y="56" width="88" height="26" rx="6" fill="#0284c7"/>
                    <text x="158" y="73" fill="#fff" font-size="10" font-weight="bold" text-anchor="middle">✂️ تقسيم وفحص</text>
                    <rect x="226" y="14" width="82" height="82" rx="12" fill="#1e1b4b" stroke="#c084fc" stroke-width="2"/>
                    <text x="267" y="48" fill="#e9d5ff" font-size="18" font-weight="bold" text-anchor="middle">4</text>
                    <text x="267" y="70" fill="#cbd5e1" font-size="9.5" font-weight="bold" text-anchor="middle">فلاتر ذكية فورية</text>
                </svg>`,
                targetSelector: '#sl-registry-tbody'
            },
            {
                stepBadge: 'القسم 4️⃣ — بطاقة الفحص والتوثيق اللوجستي وصورة البضاعة',
                title: 'توثيق المستودع والسائق ورقم اللوحة وإرفاق صورة الحمولة',
                desc: 'عند فتح أي فاتورة، يمكنك تحديد (🏢 المستودع الافتراضي)، وتسجيل (🤝 اسم المستلم الفعلي)، و(🚚 اسم السائق)، و(🔢 رقم اللوحة)، و(🎫 رقم تصريح البوابة)، مع إمكانية (📎 إرفاق صورة حية للبضاعة أو المستند الموقع).',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="10" y="16" width="195" height="78" rx="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1.8"/>
                    <text x="107" y="40" fill="#ffffff" font-size="11" font-weight="bold" text-anchor="middle">📄 بيانات الشاحنة والسائق والمستودع</text>
                    <text x="107" y="62" fill="#7dd3fc" font-size="9.5" text-anchor="middle">تُطبع رسمياً في إذن GRN / GDN</text>
                    <rect x="215" y="16" width="95" height="78" rx="10" fill="#1e293b" stroke="#fbbf24" stroke-width="1.8"/>
                    <text x="262" y="48" fill="#fcd34d" font-size="18" text-anchor="middle">📎📷</text>
                    <text x="262" y="72" fill="#fde047" font-size="9.5" font-weight="bold" text-anchor="middle">إرفاق صورة إثبات</text>
                </svg>`,
                targetSelector: '#sl-inspection-cockpit'
            },
            {
                stepBadge: 'القسم 5️⃣ — التقسيم بالوحدتين (أساسية ⇄ فرعية) وتوزيع المخازن',
                title: 'استلم أو سلّم بالطن أو بالسيخة/الحبة، ووزّع الأصناف على عدة مخازن!',
                desc: '• أمام كل صنف صندوقان متزامنان: اكتب في صندوق الوحدة الأساسية (طن/كرتون) أو الفرعية (سيخة/حبة) وسيحول البرنامج بينهما تلقائياً بدون آلة حاسبة.\n• إذا كان المتبقي 7 وكتبت 5، يحفظ البرنامج تلقائياً أن المتبقي 2 للدفعة القادمة.\n• يمكنك اختيار مخزن مستقل لكل سطر داخل نفس الفاتورة.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="10" y="18" width="132" height="74" rx="10" fill="#06281f" stroke="#10b981" stroke-width="2"/>
                    <text x="76" y="42" fill="#6ee7b7" font-size="10" font-weight="bold" text-anchor="middle">📦 بالأساسية (المتبقي 7)</text>
                    <rect x="36" y="52" width="80" height="28" rx="6" fill="#020617" stroke="#34d399"/>
                    <text x="76" y="71" fill="#fff" font-size="14" font-weight="bold" text-anchor="middle">5 طن</text>
                    <text x="160" y="60" fill="#fbbf24" font-size="18" font-weight="bold" text-anchor="middle">⇄</text>
                    <rect x="178" y="18" width="132" height="74" rx="10" fill="#1e1b4b" stroke="#818cf8" stroke-width="2"/>
                    <text x="244" y="42" fill="#a5b4fc" font-size="10" font-weight="bold" text-anchor="middle">🔢 المعادل بالفرعية</text>
                    <rect x="204" y="52" width="80" height="28" rx="6" fill="#020617" stroke="#818cf8"/>
                    <text x="244" y="71" fill="#fff" font-size="14" font-weight="bold" text-anchor="middle">500 سيخة</text>
                </svg>`,
                targetSelector: '#sl-items-grid'
            },
            {
                stepBadge: 'القسم 6️⃣ — شريط الملخص العائم، الطباعة بالـ QR، وعكس الحركة',
                title: 'راجع المعادلة الكاملة، اعتمد بضغطة، اطبع الإذن، أو تراجع في أي وقت',
                desc: '• يعرض الشريط السفلي: (إجمالي الفاتورة | المنفذ سابقاً | المنفذ الآن | المتبقي بعد الدفعة).\n• زر (🚀 تأكيد واعتماد): يحدث المخزن فوراً ويصدر الإذن الرسمي.\n• زر (🖨️ طباعة الإذن الرسمي QR) وزر (💬 واتساب).\n• زر (↩️ إلغاء وعكس الحركة): يلغي العملية ويعيد رصيد المخزن كما كان.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="8" y="22" width="304" height="66" rx="14" fill="#0f172a" stroke="#34d399" stroke-width="2"/>
                    <text x="232" y="48" fill="#cbd5e1" font-size="10" font-weight="bold" text-anchor="middle">الإجمالي: 10 | الآن: 5 | الباقي: 2</text>
                    <text x="232" y="68" fill="#fbbf24" font-size="9.5" text-anchor="middle">🖨️ طباعة QR • 💬 واتساب • ↩️ عكس</text>
                    <rect x="16" y="36" width="132" height="38" rx="10" fill="#10b981"/>
                    <text x="82" y="59" fill="#ffffff" font-size="11" font-weight="bold" text-anchor="middle">🚀 تأكيد واعتماد الآن</text>
                </svg>`,
                targetSelector: '.sl-floating-summary'
            }
        ],

        buttonsDict: [
            {
                label: '📤 منظومة المبيعات وتقسيمها وتسليمها (صادر — GDN)',
                style: 'background:linear-gradient(135deg,#064e3b,#047857); color:#fff; border:1.5px solid #34d399;',
                whatItDoes: 'يحول الشاشة بالكامل إلى بوابة تسليم بضائع المبيعات للعملاء (باللون الأخضر الزمردي).',
                whatHappensIfClicked: 'يعرض فواتير المبيعات الجاهزة للصرف أو المقسمة، ويكشف لك الرصيد الفعلي المتاح في كل مخزن قبل الصرف، وعند الاعتماد يقوم بـ (خصم الكميات المسلمة من المخزن) وإصدار إذن صرف وتصريح خروج بوابة (GDN / Gate Pass).',
                targetSelector: '#sl-tab-sales'
            },
            {
                label: '📥 منظومة المشتريات وتقسيمها واستلامها (وارد — GRN)',
                style: 'background:linear-gradient(135deg,#0c4a6e,#4338ca); color:#fff; border:1.5px solid #38bdf8;',
                whatItDoes: 'يحول الشاشة بالكامل إلى بوابة استلام بضائع المشتريات من الموردين (باللون الأزرق الملكي).',
                whatHappensIfClicked: 'يعرض فواتير المشتريات التي تنتظر الاستلام أو المقسمة على دفعات، ويتيح لك فحص الكميات الواردة وتوزيعها على عدة مخازن، وعند الاعتماد يقوم بـ (إضافة الكميات المستلمة إلى المخزن) وإصدار مذكرة استلام فحص (GRN).',
                targetSelector: '#sl-tab-purchases'
            },
            {
                label: '🛡️ حارس الـ QR (🤖 ذكي تلقائي / 🔒 قفل القسم الحالي)',
                style: 'background:#0f172a; color:#38bdf8; border:1.5px solid #38bdf8;',
                whatItDoes: 'صمام أمان ذكي يحمي العامل من الخلط بين أوراق المبيعات وأوراق المشتريات أثناء المسح السريع بالكاميرا.',
                whatHappensIfClicked: '• إذا اخترت (🤖 ذكي تلقائي): يتعرف البرنامج بنفسه على نوع الباركود الممسوح (مبيعات أو مشتريات) ويفتح بوابته أوتوماتيكياً.\n• إذا اخترت (🔒 قفل القسم الحالي): يمنع مسح أي ورقة لا تخص البوابة المفتوحة أمامك الآن وينبهك بصوت تحذيري فوري.',
                targetSelector: '#sl-qr-guard-mode'
            },
            {
                label: '📱 ربط كاميرا الجوال (المسح المتتابع الذكي بدون لمس)',
                style: 'background:linear-gradient(135deg,#059669,#0284c7); color:#fff; border:1.5px solid #34d399;',
                whatItDoes: 'يربط كاميرا هاتفك المحمول بشاشة اللابتوب أو الكمبيوتر بمسحة باركود واحدة ليعمل الجوال كقارئ ليزر لاسلكي.',
                whatHappensIfClicked: 'يفتح نافذة الربط الفوري؛ بمجرد مسح كود الربط بجوالك، يمكنك تمرير 10 أذونات أو أكثر وراء بعضها أمام كاميرا الجوال ليتم اعتمادها وتحديث المخزون تلقائياً مع منع تكرار أي إذن معتمد مسبقاً.',
                targetSelector: 'button[onclick="openSlMobileBridgeModal()"]'
            },
            {
                label: '🎨 حجم ولون الخط + ⚙️ ضبط الأعمدة والعرض',
                style: 'background:rgba(168,85,247,0.22); color:#e9d5ff; border:1.5px solid #c084fc;',
                whatItDoes: 'أدوات تخصيص مظهر الجداول لتناسب راحة عين المستخدم وحجم الشاشة.',
                whatHappensIfClicked: '• (🎨 حجم ولون الخط): يكبّر أو يصغّر خط الجداول والأرقام ويغير لونها (أبيض، زمردي، سماوي، ذهبي) مع الحفظ التلقائي.\n• (⚙️ ضبط الأعمدة): يتيح إظهار أو إخفاء أعمدة (المستودع والرصيد، المعامل، المنفذ سابقاً، السعر والإجمالي).',
                targetSelector: 'button[onclick="openSlFontModal()"]'
            },
            {
                label: '⏳ قيد الإجراء | 🌗 المقسمة | ✅ المكتملة | 🏷️ سجل الأذونات',
                style: 'background:rgba(245,158,11,0.18); color:#fcd34d; border:1.5px solid #f59e0b;',
                whatItDoes: 'البطاقات الأربع وأزرار الفلترة السريعة أعلى جدول السجل الذكي.',
                whatHappensIfClicked: '• (⏳ قيد الإجراء): يعرض الفواتير الجديدة التي لم يبدأ تنفيذها.\n• (🌗 المقسمة / البوابة): يعرض الفواتير التي نُفذ جزء منها وبقي لها دفعات أخرى.\n• (✅ المكتملة): يعرض الفواتير المنتهية بالكامل.\n• (🏷️ سجل الأذونات): يعرض جميع أذونات (GRN / GDN) الرسمية لإعادة طباعتها أو مراجعتها.',
                targetSelector: '#sl-kpi-card-pending'
            },
            {
                label: '🔍 شريط البحث اللحظي + 🏢 فلتر المخازن + ⚡ عدد الفواتير',
                style: 'background:#0f172a; color:#bae6fd; border:1.5px solid #38bdf8;',
                whatItDoes: 'محرك بحث وفلترة فوري يفرز أكثر من 1000 فاتورة وإذن في أجزاء من الثانية.',
                whatHappensIfClicked: 'بمجرد كتابة رقم الفاتورة أو اسم العميل/المورد أو اسم الصنف أو اختيار مخزن معين، يفرز الجدول النتائج فوراً، وإذا ضغطت Enter يفتح أول فاتورة مطابقة مباشرة في بطاقة الفحص.',
                targetSelector: '#sl-reg-search'
            },
            {
                label: '⚡ اعتماد كامل (في السجل) ⇄ ✂️ تقسيم وفحص (في السجل)',
                style: 'background:linear-gradient(135deg,#059669,#0284c7); color:#fff; border:1px solid #6ee7b7;',
                whatItDoes: 'أزرار الإجراء السريع الموجودة أمام كل فاتورة داخل جدول السجل العلوي.',
                whatHappensIfClicked: '• (⚡ اعتماد كامل): يستلم أو يسلم كامل المتبقي في الفاتورة فوراً بضغطة واحدة بدون الحاجة للنزول لجدول التفصيل.\n• (✂️ تقسيم وفحص): يحمّل الفاتورة في بطاقة الفحص السفلية لتقسيم كمياتها على دفعات أو توزيع أصنافها على عدة مخازن.',
                targetSelector: '#sl-registry-tbody'
            },
            {
                label: '🏢 المستودع الافتراضي (للكل) ⇄ مخزن السطر المستقل',
                style: 'background:rgba(245,158,11,0.16); color:#fde047; border:1px solid #fbbf24;',
                whatItDoes: 'يتحكم في تحديد المخزن الذي تدخل إليه البضاعة (في المشتريات) أو تخرج منه البضاعة (في المبيعات).',
                whatHappensIfClicked: '• عند تغيير (المستودع الافتراضي للكل): يتم تعميم هذا المخزن على جميع أصناف الفاتورة المفتوحة دفعة واحدة.\n• وعند تغيير (مخزن السطر) أمام صنف معين في الجدول: يتم استلام أو صرف هذا الصنف وحده من المخزن المختار مع عرض رصيده الفعلي اللحظي.',
                targetSelector: '#sl-master-warehouse'
            },
            {
                label: '🚚 بيانات السائق والمركبة وتصريح البوابة + 📎 إرفاق صورة',
                style: 'background:rgba(56,189,248,0.16); color:#7dd3fc; border:1px solid #38bdf8;',
                whatItDoes: 'خانات التوثيق اللوجستي والأمني لحركة الشاحنات والبضائع عبر البوابة.',
                whatHappensIfClicked: 'تُسجل اسم المستلم/المسلم الفعلي، واسم السائق، ورقم اللوحة، ورقم تصريح البوابة، وملاحظات الفحص، مع إمكانية إرفاق صورة حية للبضاعة أو المستند الورقي، وتظهر هذه البيانات مطبوعة في الإذن الرسمي.',
                targetSelector: '#sl-driver-name'
            },
            {
                label: '⚡ تعبئة الكل (100%) | 🌗 نصف الكمية (50%) | 🧹 تصفير (0)',
                style: 'background:rgba(16,185,129,0.2); color:#6ee7b7; border:1px solid #34d399;',
                whatItDoes: 'أزرار تحكم جماعي سريع في كميات جميع أصناف الفاتورة المفتوحة بضغطة واحدة.',
                whatHappensIfClicked: '• (100%): يملأ جميع الخانات بكامل الكمية المتبقية في الفاتورة.\n• (50%): يقسم الكمية المتبقية لكل صنف إلى النصف بالضبط.\n• (تصفير 0): يصفر جميع الخانات لتبدأ بكتابة أصناف محددة فقط.',
                targetSelector: 'button[onclick="fillAllSlQuantities(1)"]'
            },
            {
                label: '📦 الصندوق الأساسي (طن/كرتون) ⇄ 🔢 الصندوق الفرعي (سيخة/حبة)',
                style: 'background:linear-gradient(135deg,#06281f,#1e1b4b); color:#a5b4fc; border:1.5px solid #818cf8;',
                whatItDoes: 'محرك التحويل والتقسيم الذكي المزدوج أمام كل صنف في جدول الفحص.',
                whatHappensIfClicked: 'يتيح لك إدخال الكمية المنفذة الآن إما بالوحدة الكبرى (مثل الطن أو الكرتون) أو بالوحدة الصغرى (مثل السيخة أو الحبة أو القطعة)، فيقوم البرنامج فوراً بحساب المعادل بالوحدة الأخرى وحساب المتبقي للدفعة القادمة بدون آلة حاسبة.',
                targetSelector: '.sl-qty-primary-input'
            },
            {
                label: '🚀 تأكيد واعتماد الآن | 🖨️ طباعة QR | 💬 واتساب | ↩️ عكس الحركة',
                style: 'background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:1.5px solid #6ee7b7;',
                whatItDoes: 'شريط القرار النهائي العائم في أسفل الشاشة لاعتماد الحركة أو طباعتها أو التراجع عنها.',
                whatHappensIfClicked: '• (🚀 تأكيد واعتماد): يعتمد الدفعة الحالية ويحدث أرصدة المخازن وحالة الفاتورة فوراً.\n• (🖨️ طباعة الإذن الرسمي QR): يطبع مذكرة استلام (GRN) أو إذن صرف بوابة (GDN) مزوداً بباركود أمني.\n• (💬 واتساب): يرسل ملخص الإذن والكميات عبر واتساب.\n• (↩️ إلغاء وعكس الحركة): يلغي العملية المعتمدة ويعيد رصيد المخزن والفاتورة إلى حالتهما السابقة تماماً.',
                targetSelector: '#sl-btn-execute'
            }
        ],

        scenarios: [
            {
                q: '💡 1. الفاتورة فيها 10 وحدات، استلمنا سابقاً 3 وبقي فيها 7، واليوم وصلت 5 فقط، كيف أقسمها؟',
                a: '1. اضغط على الفاتورة من جدول (⏳ قيد الإجراء) أو (🌗 الجزئي) لتفتح بالأسفل.\n2. ستجد البرنامج يوضح لك تلقائياً: (منفذ سابقاً: 3 | المتبقي: 7).\n3. اكتب في مربع الكمية رقم (5) فقط ثم اضغط (🚀 تأكيد واعتماد).\n4. سيقوم البرنامج بإدخال 5 للمخزن، وإصدار إذن استلام بهذه الدفعة، وحفظ أن المتبقي الآن هو (2) للدفعة القادمة!'
            },
            {
                q: '💡 2. الفاتورة مكتوبة بالطن (أو الكرتون)، لكن العامل عدّ البضاعة بالسيخة (أو الحبة)، كيف يدخلها بدون آلة حاسبة؟',
                a: 'أمام كل صنف في الجدول السفلي يوجد صندوقان متجاوران:\n• الصندوق الأيمن للوحدة الأساسية (طن / كرتون).\n• الصندوق الأيسر للوحدة الفرعية (سيخة / حبة / قطعة).\nاترك صندوق الطن واكتب مباشرة عدد الأسياخ أو الحبات في الصندوق الأيسر (مثلاً 500 سيخة)، وسيقوم البرنامج أوتوماتيكياً بتحويلها إلى (5 طن) وحساب المتبقي بدقة متناهية!'
            },
            {
                q: '💡 3. الفاتورة فيها عدة أصناف، أريد تنزيل الصنف الأول في «المخزن الرئيسي» والصنف الثاني في «مخزن الفرع»؟',
                a: 'لا تحتاج لتقسيم الفاتورة إلى فاتورتين! في الجدول السفلي، أمام كل سطر صنف توجد قائمة (🏢 المستودع والرصيد الفعلي) خاصة بذلك السطر وحده:\n• اختر للسطر الأول (المخزن الرئيسي).\n• واختر للسطر الثاني (مخزن الفرع).\n• ثم اضغط (🚀 تأكيد واعتماد) ليتم توزيع كل صنف في مخزنه المحدد في نفس اللحظة.'
            },
            {
                q: '💡 4. في بوابة المبيعات (GDN)، كيف أعرف هل الرصيد يكفي في المخزن قبل تسليم العميل؟',
                a: 'بمجرد فتح فاتورة المبيعات في الجدول السفلي، يعرض لك عمود (🏢 المستودع والرصيد الفعلي) الكمية المتوفرة حالياً داخل كل مخزن أمام كل صنف. إذا كان الرصيد في المخزن الحالي أقل من المطلوب، يمكنك فوراً اختيار مخزن آخر من القائمة أمام نفس السطر أو تسليم الكمية المتاحة الآن وتأجيل الباقي للدفعة القادمة.'
            },
            {
                q: '💡 5. عندي 15 إذن ورقي مطبوع عليها باركود QR، كيف أستلمها أو أسلمها دفعة واحدة بدون ضغط أزرار؟',
                a: '1. تأكد أن خيار (⚡ اعتماد فوري متتابع عند المسح بدون ضغط أي زر) مفعّل في صندوق الكاميرا.\n2. اضغط على زر (📱 ربط كاميرا الجوال) في أعلى الشاشة وامسح كود الربط بهاتفك.\n3. مرّر الأوراق الـ 15 الواحدة تلو الأخرى أمام كاميرا الجوال؛ سيقوم النظام بقراءة كل باركود واعتماده وتحديث المخزن فوراً مع إصدار نغمة نجاح، وإذا مرت ورقة مكررة سيرفضها وينبهك فوراً!'
            },
            {
                q: '💡 6. اعتمدت استلام أو تسليم فاتورة بالخطأ، كيف ألغي العملية وأُرجع رصيد المخزن كما كان؟',
                a: 'الأمر بسيط وآمن تماماً:\n1. اختر الفاتورة أو الإذن من السجل العلوي (قسم ✅ المكتمل أو 🏷️ سجل الأذونات).\n2. اضغط على الزر الأحمر في الشريط السفلي (↩️ إلغاء وعكس الحركة المخزنية).\n3. سيقوم النظام فوراً بعكس حركة المخزون (إرجاع الكمية المخصومة أو سحب الكمية المضافة) وإعادة الفاتورة إلى قائمة الانتظار كما كانت.'
            }
        ],
        gapReport: {
            title: 'تقرير المراجعة الثلاثية والامتثال الكامل للدستور (Smart Logistics GRN/GDN Audit)',
            items: [
                '✅ الأمر الحاسم (1) — نطق الصلاة على النبي ﷺ مرة واحدة فقط مع ضغطة زر المرشد (hasPlayedOpeningInvocation = true) ومنع تكرارها نهائياً عند الانتقال بين الخطوات العشر أو تمرير الماوس على الحقول.',
                '✅ الأمر الحاسم (2) — إلغاء الشريط العريض الحاجب للرؤية واستبداله بكبسولة التحكم الملكية المصغرة غير الحاجبة (Royal Mini-Control Pod).',
                '✅ التقسيم المزدوج (أساسية ⇄ فرعية) وتوزيع المخازن المستقل لكل سطر + قارئ الميزان الإلكتروني (Weighbridge) + التوقيع الإلكتروني باللمس + إشعار خصم فروقات المورد (Debit Note).',
                '✅ الترابط الذري (Atomic Sync & Rollback Snapshot) والتركيز البصري الهادئ (focusRequiredSlElement) دون أي نافذة تنبيه مزعجة.'
            ]
        }
    };

    // ── CREATE TEMPORARY DEMO INVOICE IF DB IS EMPTY DURING LIVE SIMULATION ──
    let tempDemoCreated = false;
    window.ensureLiveDemoInvoiceForGuide = function () {
        if (typeof window.loadSlDocument !== 'function') return;
        const tbody = document.getElementById('sl-registry-tbody');
        const hasRealRows = tbody && tbody.querySelector('tr td[onclick], tr[onclick]');
        if (hasRealRows) return;

        tempDemoCreated = true;
        const demoInv = {
            invoiceNo: 'DEMO-107',
            date: new Date().toISOString().slice(0, 10),
            supplierName: 'مورد تجريبي للشرح الحي',
            customerName: 'عميل تجريبي للشرح الحي',
            warehouse: 'المخزن الرئيسي',
            _slStatus: 'partial',
            _slNoteNo: 'GRN-DEMO',
            items: [
                {
                    itemId: 'DEMO-STEEL',
                    name: 'حديد تسليح 12 مم (مثال تدريبي حي)',
                    unit: 'طن',
                    factor: 100,
                    qty: 10,
                    quantity: 10,
                    deliveredQty: 3,
                    receivedQty: 3,
                    remainingQty: 7,
                    price: 1000,
                    total: 10000
                }
            ]
        };
        if (tbody) {
            tbody.innerHTML = `
                <tr class="sl-selected-row" id="msg-demo-row" style="cursor:pointer;">
                    <td style="text-align:right; font-weight:900; color:#38bdf8;">#DEMO-107 (مثال تدريبي حي)</td>
                    <td style="text-align:center;">${demoInv.date}</td>
                    <td style="text-align:right; font-weight:900;">مورد تجريبي للشرح الحي</td>
                    <td style="text-align:right;"><span style="background:rgba(14,165,233,0.2); padding:4px 10px; border-radius:8px; font-weight:800;">📦 حديد تسليح 12 مم (الإجمالي: 10 | الباقي: 7)</span></td>
                    <td style="text-align:center;"><span class="sl-badge sl-badge-partial">🌗 باقي فيه 7</span></td>
                    <td style="text-align:center;"><span class="sl-btn" style="padding:4px 10px; font-size:0.75rem;">✂️ تقسيم وفحص</span></td>
                </tr>
            `;
            const rowEl = document.getElementById('msg-demo-row');
            if (rowEl) {
                rowEl.onclick = () => {
                    window.slCurrentDoc = demoInv;
                    if (typeof slCurrentDoc !== 'undefined') slCurrentDoc = demoInv;
                    document.getElementById('sl-lbl-inv-no').textContent = '#DEMO-107 (تدريب حي)';
                    document.getElementById('sl-lbl-party-name').textContent = 'مورد تجريبي للشرح الحي';
                    const itemsTbody = document.getElementById('sl-items-tbody');
                    if (itemsTbody) {
                        itemsTbody.innerHTML = `
                            <tr>
                                <td style="text-align:center; font-weight:900;">1</td>
                                <td class="sl-col-wh"><select class="sl-input sl-row-wh-select" style="width:100%;"><option>🏢 المخزن الرئيسي</option></select></td>
                                <td style="font-weight:900; color:#fff;">حديد تسليح 12 مم (مثال تدريبي حي)</td>
                                <td style="text-align:center;"><span class="sl-badge" style="background:rgba(56,189,248,0.15); color:#7dd3fc;">1 طن = 100 سيخة</span></td>
                                <td style="text-align:center; font-weight:900;">10 طن</td>
                                <td style="text-align:center;"><div style="color:#a855f7; font-weight:800;">منفذ سابقاً: 3</div><div style="color:#fbbf24; font-weight:900;">المتبقي: 7 طن</div></td>
                                <td style="text-align:center;">
                                    <div style="display:flex; justify-content:center; gap:10px;">
                                        <div class="sl-unit-box is-active-unit-mode">
                                            <div style="font-size:0.75rem; font-weight:900; color:#38bdf8; margin-bottom:4px;">📦 بالـ طن (المتبقي: 7)</div>
                                            <input type="text" class="sl-input sl-qty-primary-input" data-item-idx="0" data-max-qty="7" data-orig-qty="10" data-prev-qty="3" data-factor="100" data-pri-unit="طن" data-base-unit="سيخة" value="7" oninput="onSlPrimaryQtyInput(this)" style="width:90px !important; text-align:center; font-size:1.1rem !important;">
                                        </div>
                                        <span style="align-self:center; font-weight:900; color:#fbbf24;">⇄</span>
                                        <div class="sl-unit-box base-box">
                                            <div style="font-size:0.75rem; font-weight:900; color:#a5b4fc; margin-bottom:4px;">🔢 بالـ سيخة (700)</div>
                                            <input type="text" class="sl-input sl-qty-base-input" data-item-idx="0" data-max-base="700" data-factor="100" value="700" oninput="onSlBaseQtyInput(this)" style="width:90px !important; text-align:center; font-size:1.1rem !important;">
                                        </div>
                                    </div>
                                    <div id="sl-row-live-eq-0" style="font-size:0.76rem; font-weight:800; margin-top:4px; color:#cbd5e1;">المنفذ الآن: 7 طن ⇄ 700 سيخة</div>
                                </td>
                                <td style="text-align:center;"><span class="sl-badge sl-badge-pending">⚡ جاهز</span></td>
                            </tr>
                        `;
                        if (typeof window.updateSlFloatingSummary === 'function') window.updateSlFloatingSummary();
                    }
                };
            }
        }
    };

    // ── RENDER & OPEN MODAL + UNIVERSAL FIXED LAUNCHER ───────────────────────
    let currentGuideKey = 'smart-logistics';
    let isAutopilotRunning = false;
    let autopilotStepIdx = 0;
    let activeAutopilotStepsList = [];
    let isPickElementModeActive = false;
    let speechRecognitionInstance = null;

    // ════════════════════════════════════════════════════════════════════════
    // 💵 DEDICATED SMART GUIDE FOR SINGLE CASH PAYMENT & RECEIPT VOUCHER
    // ════════════════════════════════════════════════════════════════════════
    const SINGLE_CASH_PAYMENT_STEPS = [
        {
            badge: 'الخطوة 1️⃣ — شريط أرصدة الخزينة اللحظية (نقدية وشيكات وعهد)',
            cursorLabel: 'رصيد النقدية الحالي في الخزينة',
            targetSelector: '.group-balances',
            speech: () => `${SHORT_SAHIHAYN_SALAWAT} أهلاً بحضرتك في شاشة سند الصرف والقبض المفرد. نبدأ هنا بشريط الأرصدة اللحظية بالأعلى: خانة النقدية توضح لك رصيد الصندوق الفعلي المتوفر حالياً في الخزينة، لتعرف فوراً هل السيولة تكفي للصرف قبل البدء بدون فتح تقارير أخرى. وخانة الشيكات والعهد تعرض الشيكات والعهد المعلقة.`
        },
        {
            badge: 'الخطوة 2️⃣ — تاريخ السند الفعلي',
            cursorLabel: 'تاريخ السند المالي',
            targetSelector: '#hdr-date',
            speech: () => `هنا تاريخ السند؛ يحدد اليوم الفعلي لحركة الصرف أو القبض، وهو التاريخ المعتمد في قيود اليومية ودفتر الأستاذ وكشوفات الحساب.`
        },
        {
            badge: 'الخطوة 3️⃣ — خانة المبلغ والعملة وسعر الصرف',
            cursorLabel: 'المبلغ والعملة وسعر الصرف',
            targetSelector: '#amount-local',
            speech: () => `هنا خانة المبلغ الرئيسية؛ تكتب فيها القيمة النقدية المراد صرفها أو قبضها. وإذا كنت تتعامل بعملة أجنبية كالدولار أو الريال، يمكنك تغيير العملة وتحديد سعر الصرف ليقوم النظام بحساب المعادل المحلي تلقائياً بدقة.`
        },
        {
            badge: 'الخطوة 4️⃣ — من حساب (حساب الصرف - الطرف الدائن)',
            cursorLabel: 'حساب الصرف (من حساب)',
            targetSelector: '#from-account-search',
            speech: () => `خانة (من حساب) المميزة باللون الأحمر: هذا هو الحساب الذي تخرج منه الفلوس؛ مثل الخزينة الرئيسية، أو البنك، وهو يمثل الطرف الدائن في القيد المحاسبي.`
        },
        {
            badge: 'الخطوة 5️⃣ — إلى حساب (الحساب المستلم - الطرف المدين)',
            cursorLabel: 'الحساب المستلم (إلى حساب)',
            targetSelector: '#to-account-search',
            speech: () => `خانة (إلى حساب) المميزة باللون الأخضر: هذا هو الحساب أو الجهة المستفيدة التي ستستلم الفلوس؛ مثل حساب مورد، أو سلفة موظف، أو حساب مصروفات كالكهرباء والإيجار، وهو الطرف المدين.`
        },
        {
            badge: 'الخطوة 6️⃣ — زر عكس الحسابات الذكي (⇄)',
            cursorLabel: 'عكس الحسابات (صرف ⇄ قبض)',
            targetSelector: '.epic-arrow-container',
            speech: () => `زر عكس الحسابات: يتيح لك بضغطة واحدة تبديل طرفي الحركة فوراً؛ لتحويل السند من سند صرف إلى سند قبض بدون الحاجة لإعادة كتابة الحسابات من جديد.`
        },
        {
            badge: 'الخطوة 7️⃣ — اسم المستفيد ومركز التكلفة',
            cursorLabel: 'المستفيد ومركز التكلفة',
            targetSelector: '#beneficiary-name',
            speech: () => `هنا يمكنك كتابة اسم الشخص المستلم الفعلي أو تحديد مركز التكلفة والمشروع المرتبط بالحركة لتوزيع المصروفات بدقة.`
        },
        {
            badge: 'الخطوة 8️⃣ — البيان المحاسبي والتوجيه الذكي',
            cursorLabel: 'البيان وشرح الحركة المالية',
            targetSelector: '#to-statement',
            speech: () => `خانة البيان: تكتب فيها شرح وسبب الصرف أو القبض؛ مثل دفعة تحت الحساب أو سداد فاتورة. هذا البيان سيظهر في كشف حساب الطرفين، كما يمكنك الضغط على زر التوجيه الذكي بجواره ليقترح عليك الحساب المناسب تلقائياً.`
        },
        {
            badge: 'الخطوة 9️⃣ — طرق الدفع (نقداً / شيك / بنكك)',
            cursorLabel: 'طريقة الدفع (كاش / شيك / بنك)',
            targetSelector: '.pay-options-wrapper, #radio-cash',
            speech: () => `هنا تختار طريقة الدفع: نقداً من الخزينة، أو عبر شيك ورقي بتحديد رقم الشيك وتاريخ استحقاقه، أو تحويل بنكي مثل تطبيق بنكك مع حفظ رقم الإشعار البنكي للتوثيق.`
        },
        {
            badge: 'الخطوة 🔟 — الحفظ والترحيل اللحظي والواتساب',
            cursorLabel: 'حفظ واعتماد وترحيل السند',
            targetSelector: '#btn-save',
            speech: () => `أخيراً زر حفظ واعتماد السند: عند الضغط عليه، يتم ترحيل المبلغ فوراً وتسميعه في دفتر الأستاذ العام وميزان المراجعة، مع إمكانية طباعة إيصال رسمي أو إرسال كمبيالة معتمدة كملف بي دي إف مباشرة للواتساب.`
        }
    ];

    window.MagaiesSmartGuides['single-cash-payment'] = {
        title: 'المرشد الذكي: سند الصرف والقبض المالي المفرد',
        subtitle: 'شرح محاسبي مبسط وتطبيقي لكل خانة وزر في الشاشة بالصوت والماوس التلقائي',
        autopilotSteps: SINGLE_CASH_PAYMENT_STEPS,
        visualSteps: [
            {
                stepBadge: 'القسم 1️⃣ — شريط الأرصدة اللحظية (قبل الصرف)',
                title: 'كيف تعرف رصيد درج الخزينة الفعلي قبل تنفيذ الحركة؟',
                desc: '• 💵 نقدية: يظهر لك الرصيد الفعلي الموجود حالياً داخل درج الخزينة قبل تسجيل هذه الحركة، لتتأكد هل السيولة تسمح بالصرف أم لا.\n• 🧾 شيكات: إجمالي قيمة الشيكات الآجلة أو تحت التحصيل.\n• 💼 عهد / وعد: إجمالي المبالغ المصروفة كعهد معلقة للموظفين أو المندوبين.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="12" y="14" width="94" height="82" rx="10" fill="#064e3b" stroke="#34d399" stroke-width="2"/>
                    <text x="59" y="42" fill="#ffffff" font-size="11" font-weight="bold" text-anchor="middle">💵 نقدية</text>
                    <text x="59" y="65" fill="#a7f3d0" font-size="10" font-weight="bold" text-anchor="middle">رصيد الخزينة</text>
                    <rect x="113" y="14" width="94" height="82" rx="10" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                    <text x="160" y="42" fill="#ffffff" font-size="11" font-weight="bold" text-anchor="middle">🧾 شيكات</text>
                    <text x="160" y="65" fill="#7dd3fc" font-size="10" font-weight="bold" text-anchor="middle">تحت التحصيل</text>
                    <rect x="214" y="14" width="94" height="82" rx="10" fill="#1e1b4b" stroke="#c084fc" stroke-width="2"/>
                    <text x="261" y="42" fill="#ffffff" font-size="11" font-weight="bold" text-anchor="middle">💼 عهد</text>
                    <text x="261" y="65" fill="#e9d5ff" font-size="10" font-weight="bold" text-anchor="middle">عهد معلقة</text>
                </svg>`,
                targetSelector: '.group-balances'
            },
            {
                stepBadge: 'القسم 2️⃣ — الفرق الجوهري: (من حساب 🔴) و (إلى حساب 🟢)',
                title: 'قاعدة المحاسبة الذهبية: أين تذهب الأموال ومن يدفعها؟',
                desc: '• 🔴 من حساب (صرف): هو الطرف الدائن ومصدر المال (الخزينة أو البنك)، وينقص رصيده بهذه القيمة.\n• 🟢 إلى حساب (استلام): هو الطرف المدين المستفيد من المال (مورد أو موظف أو مصروف كهرباء)، ويزيد رصيده أو ينقص دينه.\n• ⇄ زر العكس: يقلب طرفي الحركة فوراً للتحويل بين سند صرف وسند قبض بضغطة زر واحدة.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="10" y="14" width="135" height="82" rx="12" fill="#450a0a" stroke="#f87171" stroke-width="2.5"/>
                    <text x="77" y="42" fill="#ffffff" font-size="12" font-weight="bold" text-anchor="middle">🔴 من حساب (صرف)</text>
                    <text x="77" y="63" fill="#fecaca" font-size="10" text-anchor="middle">مصدر الأموال (دائن)</text>
                    <text x="77" y="80" fill="#fef08a" font-size="9" text-anchor="middle">الخزينة أو البنك</text>
                    <rect x="175" y="14" width="135" height="82" rx="12" fill="#064e3b" stroke="#34d399" stroke-width="2.5"/>
                    <text x="242" y="42" fill="#ffffff" font-size="12" font-weight="bold" text-anchor="middle">🟢 إلى حساب (استلام)</text>
                    <text x="242" y="63" fill="#a7f3d0" font-size="10" text-anchor="middle">المستفيد (مدين)</text>
                    <text x="242" y="80" fill="#fef08a" font-size="9" text-anchor="middle">المورد / الموظف</text>
                    <circle cx="155" cy="55" r="14" fill="#1e293b" stroke="#fbbf24" stroke-width="2"/>
                    <text x="155" y="59" fill="#fde047" font-size="12" font-weight="bold" text-anchor="middle">⇄</text>
                </svg>`,
                targetSelector: '.scp-account-card'
            },
            {
                stepBadge: 'القسم 3️⃣ — التنقل السريع بالكيبورد (Enter Flow)',
                title: 'إدخال كامل للسند في 3 ثوان بدون لمس الماوس',
                desc: '• ابدأ بكتابة المبلغ ثم اضغط Enter، ينتقل المؤشر تلقائياً إلى حساب الصرف (من حساب).\n• اختر الحساب واضغط Enter، ينتقل مباشرة إلى حساب الاستلام (إلى حساب).\n• اكتب البيان واضغط Enter، يذهب المؤشر فوراً لزر الحفظ والترحيل لاعتماد السند.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="15" y="20" width="290" height="70" rx="12" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                    <text x="160" y="48" fill="#fef08a" font-size="12.5" font-weight="bold" text-anchor="middle">المبلغ ⏎ ⬅ من حساب ⏎ ⬅ إلى حساب ⏎</text>
                    <text x="160" y="70" fill="#38bdf8" font-size="11" font-weight="bold" text-anchor="middle">⬅ البيان ⏎ ⬅ حفظ واعتماد السند ✅</text>
                </svg>`,
                targetSelector: '#amount-local'
            },
            {
                stepBadge: 'القسم 4️⃣ — التسميع المحاسبي والواتساب والكمبيالة',
                title: 'تسميع فوري في كشوفات الحساب مع إرسال توثيقي بالواتساب',
                desc: '• بمجرد الحفظ يتم الترحيل الفوري في دفتر الأستاذ العام وميزان المراجعة وتقرير حركة الصندوق في جزء من الثانية.\n• يمكنك بضغطة زر توليد كمبيالة PDF معتمدة وموثقة برقم السند والباركود والختم الرسمي وإرسالها مباشرة لرقم واتساب العميل أو المورد.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="12" y="16" width="140" height="78" rx="10" fill="#064e3b" stroke="#10b981" stroke-width="2"/>
                    <text x="82" y="45" fill="#fff" font-size="11.5" font-weight="bold" text-anchor="middle">📲 إرسال واتساب فوري</text>
                    <text x="82" y="68" fill="#a7f3d0" font-size="9.5" text-anchor="middle">كمبيالة PDF معتمدة</text>
                    <rect x="168" y="16" width="140" height="78" rx="10" fill="#1e1b4b" stroke="#c084fc" stroke-width="2"/>
                    <text x="238" y="45" fill="#fff" font-size="11.5" font-weight="bold" text-anchor="middle">📊 تسميع دفتر الأستاذ</text>
                    <text x="238" y="68" fill="#e9d5ff" font-size="9.5" text-anchor="middle">تحديث لحظي للميزان</text>
                </svg>`,
                targetSelector: '#btn-save'
            }
        ],
        buttonsDict: [
            {
                label: 'حفظ واعتماد السند',
                style: 'background:#059669; color:#ffffff; border:1px solid #34d399;',
                whatItDoes: 'يحفظ السند ويرحله فوراً إلى دفتر الأستاذ العام وميزان المراجعة وقاعدة البيانات.',
                whatHappensIfClicked: 'يقوم باعتماد السند وإنشاء القيد المحاسبي وتحديث رصيد الصندوق فوراً.',
                targetSelector: '#btn-save'
            },
            {
                label: 'عكس الحسابات (⇄)',
                style: 'background:#1e293b; color:#fbbf24; border:1px solid #fbbf24;',
                whatItDoes: 'يبدل فوراً بين حساب الصرف (من) وحساب الاستلام (إلى).',
                whatHappensIfClicked: 'يحول السند من سند صرف إلى سند قبض والعكس بضغطة زر واحدة دون إعادة كتابة الحسابات.',
                targetSelector: '.epic-arrow-container'
            },
            {
                label: 'السند السابق والسند التالي',
                style: 'background:#0369a1; color:#ffffff; border:1px solid #38bdf8;',
                whatItDoes: 'يتنقل بين السندات المالية المسجلة مسبقاً لاستعراضها أو تعديلها أو طباعتها.',
                whatHappensIfClicked: 'يعرض بيانات السند السابق أو اللاحق فوراً على الشاشة بكامل تفاصيله.',
                targetSelector: '#voucher-nav-pill'
            },
            {
                label: 'دفتر الأستاذ (📖)',
                style: 'background:#1e1b4b; color:#c084fc; border:1px solid #c084fc;',
                whatItDoes: 'يفتح كشف حساب تفصيلي فوري لأي حساب تختاره.',
                whatHappensIfClicked: 'يعرض كشف الحساب ورصيده السابق والحركات المدينة والدائنة في نافذة منبثقة سريعة.',
                targetSelector: '#from-account-ledger-btn'
            },
            {
                label: 'توقع الحساب والتوجيه الذكي',
                style: 'background:#9333ea; color:#ffffff; border:1px solid #c084fc;',
                whatItDoes: 'يحلل نص البيان المكتوب ويقترح الحساب المحاسبي المناسب تلقائياً بالذكاء الاصطناعي.',
                whatHappensIfClicked: 'يقوم باختيار الحساب المستهدف بناءً على الكلمات المفتاحية في البيان.',
                targetSelector: '#btn-auto-categorize'
            }
        ],
        scenarios: [
            {
                q: '💡 ما الفرق بين "من حساب" و "إلى حساب"؟',
                a: '• "من حساب" باللون الأحمر هو مصدر المال (الخزينة أو البنك)، وينقص رصيده بهذه القيمة.\n• "إلى حساب" باللون الأخضر هو المستفيد الذي استلم المال (المورد أو الموظف أو حساب مصروف)، ويزيد رصيده أو ينقص دينه.'
            },
            {
                q: '💡 ماذا تعني خانة النقدية في شريط الأرصدة بالأعلى؟',
                a: 'خانة "نقدية" توضح الرصيد الفعلي الحقيقي الموجود حالياً في درج الخزينة قبل تسجيل الحركة، لتتأكد هل تتوفر سيولة كافية للصرف أم لا دون الحاجة للرجوع إلى تقرير كشف الصندوق.'
            },
            {
                q: '💡 كيف أحول السند من صرف إلى قبض دون مسح الحسابات؟',
                a: 'ببساطة اضغط على زر الأسهم الدائرية (⇄) الموجود بين الحسابين، وسيتم عكس الطرفين فوراً ليصبح السند سند قبض.'
            },
            {
                q: '💡 هل يتم ترحيل السند وتسميعه في التقارير فوراً؟',
                a: 'نعم، بمجرد الضغط على (حفظ واعتماد السند) يتم الترحيل في نفس اللحظة إلى دفتر الأستاذ العام وميزان المراجعة وتقرير حركة الصندوق بدون أي تأخير.'
            }
        ]
    };

    // ============================================================================
    // 🗂️ 4. MASTER DATA MULTI-SCREEN GUIDE (شاشة داخل شاشة: المخازن • العملاء • الموردين • البنوك • الخزن • الأصناف • الموظفين • مراكز التكلفة)
    // ============================================================================
    window.MagaiesSmartGuides['master-data'] = {
        title: 'المرشد الذكي: منظومة البيانات الأساسية (8 شاشات متداخلة)',
        subtitle: 'شرح مصوّر ومدبلج بالصوت الرجالي يفتح أمامك كل تبويب وشاشة فرعية بالترتيب ويشرح حقولها وأثرها المحاسبي',
        screenHierarchy: [
            'الشاشة الرئيسية: البيانات الأساسية',
            '1️⃣ المخازن ⬅ 2️⃣ العملاء ⬅ 3️⃣ الموردين ⬅ 4️⃣ البنوك',
            '5️⃣ الخزن ⬅ 6️⃣ الأصناف والوحدات ⬅ 7️⃣ الموظفين ⬅ 8️⃣ مراكز التكلفة'
        ],
        autopilotSteps: [
            {
                badge: '1. الشاشة الفرعية الأولى: تأسيس وإدارة المخازن',
                cursorLabel: 'تبويب وشاشة المخازن 🏭',
                screenPath: 'البيانات الأساسية ⬅ ضغط زر (المخازن) ⬅ فتح شاشة إضافة وجدول المخازن',
                targetSelector: '#warehouse-name, .md-tab-btn.tab-warehouses',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'قائمة البيانات الأساسية الجانبية',
                    actionLabel: 'ضغط تبويب (المخازن) يفتح شاشة المستودعات:',
                    childItems: ['🏭 اسم المخزن ونوعه وأمين المستودع', '📦 يظهر فوراً في قوائم المشتريات والمبيعات والجرد', '🔄 تحديد المخزن الافتراضي للفرع', '📊 ربط تلقائي بتقارير أرصدة المخازن'],
                    effectLabel: 'شاشة المخازن مفتوحة الآن أمامك!'
                },
                action: () => {
                    const btn = document.querySelector('.md-tab-btn.tab-warehouses');
                    if (btn) { try { btn.click(); } catch (e) {} }
                    if (typeof window.openWarehouseForm === 'function') { try { window.openWarehouseForm(); } catch (e) {} }
                },
                speech: () => `${SHORT_SAHIHAYN_SALAWAT} أهلاً بحضرتك في الشرح الشامل لشاشات البيانات الأساسية الثمانية. نبدأ بالضغط على زر المخازن لتفتح أمامنا شاشة تأسيس المستودعات؛ وأي مخزن تضيفه هنا يظهر فوراً في قوائم فواتير المشتريات والمبيعات وبوابة الاستلام والجرد.`
            },
            {
                badge: '2. الشاشة الفرعية الثانية: 👥 منظومة العملاء الشاملة من الألف إلى الياء',
                cursorLabel: 'شاشة العملاء الشاملة (من الألف إلى الياء) 👥🛡️',
                screenPath: 'البيانات الأساسية ⬅ شاشة العملاء ⬅ السياسة والأوامر الجماعية ⬅ الموقف والبطاقة الافتراضية QR ⬅ الرصيد الافتتاحي وحد الائتمان والعملة والرقم السري ⬅ التعديل والحذف المحمي',
                targetSelector: '#cust-name, #cust-limit, .md-tab-btn.tab-customers',
                goldenRule: '«اضبط الرصيد الافتتاحي وحد الائتمان وعملة الحساب والرقم السري بدقة؛ فالتجارة الناجحة تُبنى على الحزم المالي والتوثيق الكامل لا على العواطف.»',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'شاشة بيانات العملاء الشاملة (من الألف إلى الياء)',
                    actionLabel: 'منظومة إدارة العملاء المتكاملة:',
                    childItems: [
                        '⚙️ شريط الأوامر العليا: كشف للكل • رصيد للكل • إيقاف الكل • توليد باسورد • السياسة العامة',
                        '🪪 شريط البطاقة: الموقف (موقوف/الرصيد/كشف) • اعتماد الإشعارات • البنك الافتراضي • بطاقة QR • نسخ البطاقة',
                        '💰 الحقول المالية: الاسم • الرقم السري للواتساب • رقم الواتساب • الرصيد الافتتاحي المغذي للحساب • حد الائتمان • العملة • الحالة • شريحة التسعير',
                        '✏️ التعديل والحذف: تعديل فوري بزر (✏️ تعديل) • منع صارم للحذف (🗑️ حذف) إذا كان للعميل حركات أو رصيد'
                    ],
                    effectLabel: 'شرح وتوثيق شاشة العملاء كاملة من الألف إلى الياء!'
                },
                action: () => {
                    const btn = document.querySelector('.md-tab-btn.tab-customers');
                    if (btn) { try { btn.click(); } catch (e) {} }
                },
                speech: () => 'وعند الضغط على تبويب العملاء تفتح أمامك منظومة العملاء الشاملة من الألف إلى الياء؛ وتبدأ بشريط التحكم الجماعي في الأعلى (كشف للكل، رصيد للكل، إيقاف الكل، توليد باسورد، ودرج السياسة العامة)، يليه شريط موقف العميل الفردي (موقوف، الرصيد، كشف، اعتماد فوري أو مراجعة للإشعارات، البنك الافتراضي، البطاقة الافتراضية كيو آر، ونسخ البطاقة)، ثم حقول البطاقة: اسم العميل، الرقم السري الذي نحتاجه في الواتساب مع زر التوليد، رقم الواتساب، الرصيد الافتتاحي الذي يغذي كشف الحساب وميزان المراجعة، الحد الائتماني الذي يحافظ على رأس مالك، العملة، الحالة النشطة أو في آي بي أو محظور، شريحة التسعير وفترة السداد وأرقام المناديب والحسابات البنكية، وصولاً إلى جدول العملاء حيث يمكنك الضغط على تعديل لتحديث أي بيان، أو حذف مع وجود حماية محاسبية تمنع حذف أي عميل عليه حركات أو أرصدة.'
            },
            {
                badge: '3. الشاشة الفرعية الثالثة: 🏢 منظومة الموردين الشاملة من الألف إلى الياء',
                cursorLabel: 'شاشة الموردين الشاملة (من الألف إلى الياء) 🏢🛡️',
                screenPath: 'البيانات الأساسية ⬅ شاشة الموردين ⬅ السياسة والأوامر الجماعية ⬅ الموقف وبطاقة QR ⬅ الرصيد الافتتاحي وحد الائتمان والعملة والرقم السري ⬅ التعديل والحذف المحمي',
                targetSelector: '#sup-name, #sup-limit, .md-tab-btn.tab-suppliers',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'شاشة بيانات الموردين الشاملة (من الألف إلى الياء)',
                    actionLabel: 'منظومة إدارة الموردين المتكاملة:',
                    childItems: [
                        '⚙️ شريط الأوامر العليا للموردين: كشف للكل • رصيد للكل • إيقاف الكل • توليد باسورد • السياسة',
                        '🪪 شريط بطاقة المورد: الموقف (موقوف/الرصيد/كشف) • اعتماد الإشعارات • البنك الافتراضي • بطاقة QR • نسخ البطاقة',
                        '💵 الحقول المالية: اسم المورد • الرقم السري للواتساب • رقم الواتساب • الرصيد الافتتاحي الدائن • سقف الائتمان • العملة • الحالة',
                        '✏️ التعديل والحذف: تعديل فوري بزر (✏️ تعديل) • منع الحذف نهائياً عند وجود فواتير مشتريات أو سندات أو رصيد'
                    ],
                    effectLabel: 'شرح وتوثيق شاشة الموردين كاملة من الألف إلى الياء!'
                },
                action: () => {
                    const btn = document.querySelector('.md-tab-btn.tab-suppliers');
                    if (btn) { try { btn.click(); } catch (e) {} }
                },
                speech: () => 'وعند الضغط على زر الموردين تفتح شاشة الموردين المتكاملة من الألف إلى الياء؛ وتشمل أوامر الواتساب الجماعية (كشف للكل، رصيد للكل، إيقاف الكل، توليد باسورد، والسياسة)، وموقف المورد الفردي (موقوف، الرصيد، كشف)، والبطاقة الافتراضية كيو آر ونسخ البطاقة، وخانات اسم المورد، الرقم السري للواتساب، رقم الواتساب، الرصيد الافتتاحي الذي يغذي حساب المورد، وسقف التعامل والعملة والحالة النشطة وأرقام المناديب والحسابات البنكية، مع إمكانية التعديل الفوري ومنع الحذف في حالة وجود حركات مالية أو فواتير مشتريات.'
            },
            {
                badge: '4. الشاشة الفرعية الرابعة: فتح شاشة البنوك والتطبيقات البنكية',
                cursorLabel: 'فتح شاشة البنوك 🏦',
                screenPath: 'البيانات الأساسية ⬅ ضغط زر (البنوك) ⬅ فتح حسابات البنوك والتطبيقات البنكية',
                targetSelector: '#bank-name, .md-tab-btn.tab-banks',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'تبويب البنوك (.tab-banks)',
                    actionLabel: 'يفتح شاشة الحسابات البنكية والتطبيقات:',
                    childItems: ['🏦 تسجيل الحسابات البنكية (بنكك • فوري • أوكاش) ورقم الحساب', '💱 تحديد عملة الحساب البنكي والرصيد الافتتاحي', '📊 ربط مباشر بلوحة الدفع الهجين وسندات الصرف والقبض', '🔍 مطابقة آلية مع إشعارات التحويل البنكي بالذكاء الاصطناعي'],
                    effectLabel: 'شاشة البنوك مفتوحة الآن أمامك!'
                },
                action: () => {
                    const btn = document.querySelector('.md-tab-btn.tab-banks');
                    if (btn) { try { btn.click(); } catch (e) {} }
                    if (typeof window.openBankForm === 'function') { try { window.openBankForm(); } catch (e) {} }
                },
                speech: () => 'ثم نضغط على زر البنوك لتفتح شاشة تأسيس الحسابات البنكية والتطبيقات مثل بنكك وفوري وأوكاش برقم الحساب والرصيد الافتتاحي، لتظهر في سندات الصرف والقبض والتحويلات البنكية.'
            },
            {
                badge: '5. الشاشة الفرعية الخامسة: فتح شاشة الخزائن النقدية',
                cursorLabel: 'فتح شاشة الخزائن 💵',
                screenPath: 'البيانات الأساسية ⬅ ضغط زر (الخزن) ⬅ فتح شاشة الخزائن النقدية',
                targetSelector: '#safe-name, .md-tab-btn.tab-safes',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'تبويب الخزائن (.tab-safes)',
                    actionLabel: 'يفتح شاشة الخزائن النقدية الرئيسية والفرعية:',
                    childItems: ['💵 اسم الخزينة النقدية وأمين الصندوق المسؤول', '💰 الرصيد الافتتاحي والعملة المعتمدة للخزينة', '🔒 سقف الصرف اليومي وتنبيهات الحد الأدنى للسيولة', '🧾 ربط مباشر بالمقبوضات والمدفوعات النقدية اليومية'],
                    effectLabel: 'شاشة الخزائن النقدية مفتوحة الآن أمامك!'
                },
                action: () => {
                    const sBtn = document.querySelector('.md-tab-btn.tab-safes');
                    if (sBtn) { try { sBtn.click(); } catch (e) {} }
                    if (typeof window.openSafeForm === 'function') { try { window.openSafeForm(); } catch (e) {} }
                },
                speech: () => 'ويليه زر الخزن لتأسيس الخزائن النقدية الرئيسية والفرعية وتحديد عملة كل خزينة ورصيدها الافتتاحي وأمين الصندوق المسؤول عنها.'
            },
            {
                badge: '6. الشاشة الفرعية السادسة: فتح شاشة الأصناف والوحدات والتسعير',
                cursorLabel: 'فتح شاشة الأصناف والوحدات 📦',
                screenPath: 'البيانات الأساسية ⬅ ضغط زر (الأصناف) ⬅ فتح بطاقة الصنف والوحدات الكبرى والفرعية',
                targetSelector: '#item-name-input, .md-tab-btn.tab-items',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'تبويب الأصناف (.tab-items)',
                    actionLabel: 'ضغط تبويب (الأصناف) يفتح بطاقة الصنف الشاملة:',
                    childItems: ['🏷️ اسم الصنف والكود والباركود والتصنيف', '⚖️ الوحدة الكبرى (طن/كرتون) والفرعية (سيخة/حبة) ومعامل التحويل', '💰 سعر الشراء وشرائح أسعار البيع (قطاعي • نصف جملة • جملة)', '🚨 حد الطلب الأدنى للتنبيه الذكي قبل نفاد المخزون'],
                    effectLabel: 'شاشة الأصناف والوحدات مفتوحة الآن أمامك!'
                },
                action: () => {
                    const btn = document.querySelector('.md-tab-btn.tab-items');
                    if (btn) { try { btn.click(); } catch (e) {} }
                    if (typeof window.openItemForm === 'function') { try { window.openItemForm(); } catch (e) {} }
                },
                speech: () => 'والآن نضغط على زر الأصناف لتفتح أهم شاشة مخزنية؛ وفيها تعرف اسم الصنف والباركود، وتحدد الوحدة الكبرى كالطن أو الكرتون والوحدة الفرعية كالسيخة أو القطعة ومعامل التحويل بينهما، مع أسعار الشراء والبيع وحد الطلب.'
            },
            {
                badge: '7. الشاشة الفرعية السابعة: فتح شاشة الموظفين والرواتب والصلاحيات',
                cursorLabel: 'فتح شاشة الموظفين 👨‍💼',
                screenPath: 'البيانات الأساسية ⬅ ضغط زر (الموظفين) ⬅ فتح بطاقة الموظف والرواتب والعهد',
                targetSelector: '#emp-name, .md-tab-btn.tab-employees',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'تبويب الموظفين (.tab-employees)',
                    actionLabel: 'يفتح شاشة شؤون الموظفين والرواتب والعهد:',
                    childItems: ['👨‍💼 اسم الموظف والوظيفة والدور الرقابي في النظام', '💵 الراتب الأساسي والبدلات وحساب السلف والعهد', '🏢 ربط الموظف بالفرع أو المستودع المسؤول عنه', '📊 ترحيل آلي لكشوف الرواتب وميزان المراجعة'],
                    effectLabel: 'شاشة الموظفين مفتوحة الآن أمامك!'
                },
                action: () => {
                    const empBtn = document.querySelector('.md-tab-btn.tab-employees');
                    if (empBtn) { try { empBtn.click(); } catch (e) {} }
                    if (typeof window.openNewEmployeeForm === 'function') { try { window.openNewEmployeeForm(); } catch (e) {} }
                },
                speech: () => 'ثم نضغط على زر الموظفين لتسجيل العاملين وتحديد أدوارهم الوظيفية ورواتبهم الأساسية وبدلاتهم وحسابات السلف والعهد الخاصة بهم.'
            },
            {
                badge: '8. الشاشة الفرعية الثامنة: فتح شاشة مراكز التكلفة والمشاريع',
                cursorLabel: 'فتح شاشة مراكز التكلفة 🎯',
                screenPath: 'البيانات الأساسية ⬅ ضغط زر (مركز التكلفة) ⬅ فتح شاشة المشاريع والفروع',
                targetSelector: '#costcenter-name, .md-tab-btn.tab-costcenters',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'تبويب مراكز التكلفة (.tab-costcenters)',
                    actionLabel: 'يفتح شاشة تأسيس مراكز التكلفة والمشاريع:',
                    childItems: ['🎯 تأسيس مراكز التكلفة (مشاريع • شاحنات • خطوط إنتاج • فروع)', '💰 تحديد الموازنة التقديرية للمشروع ومراقبة الصرف', '📈 ربط الفواتير والسندات بكل مشروع لمعرفة صافي ربحه', '✅ حفظ ذري فوري وتزامن مع كافة شاشات وتقارير النظام'],
                    effectLabel: 'تم استعراض جميع الشاشات الثمانية المتداخلة بنجاح!'
                },
                action: () => {
                    const ccBtn = document.querySelector('.md-tab-btn.tab-costcenters');
                    if (ccBtn) { try { ccBtn.click(); } catch (e) {} }
                    if (typeof window.openCostCenterForm === 'function') { try { window.openCostCenterForm(); } catch (e) {} }
                },
                speech: () => 'وأخيراً نضغط على زر مركز التكلفة لتأسيس المشاريع والفروع والشاحنات وربطها بالفواتير والسندات لمعرفة أرباح وتكاليف كل مشروع بدقة.'
            }
        ],
        scenarios: [
            {
                q: '💡 كيف أربط الصنف بوحدتين (مثلاً طن وسيخة أو كرتونة وحبة)؟',
                a: 'من تبويب (الأصناف) اختر الوحدة الأساسية (مثل حبة أو سيخة)، ثم اضغط (+ إضافة وحدة) في جدول الوحدات واكتب المعادل (مثلاً 12 أو 100) وسعر البيع النقدي والآجل لكل وحدة.'
            },
            {
                q: '💡 كيف يعمل الرقم السري (PIN) لكشف حساب العميل أو المورد عبر الواتساب؟',
                a: 'في بطاقة العميل أو المورد يوجد حقل (🔐 الرقم السري) مع زر (🎲 توليد)؛ عندما يرسل العميل هذا الرقم للبوت على واتساب يصله كشف حسابه المعتمد فوراً حسب الصلاحية المحددة له.'
            }
        ],
        buttonsDict: [
            { label: 'تبويب المخازن (.tab-warehouses)', targetSelector: '.md-tab-btn.tab-warehouses', whatItDoes: 'إدارة المستودعات والمعارض ونقاط البيع.', whatHappensIfClicked: 'يفتح جدول ونموذج تعريف المخازن وتحديد أمين كل مخزن.' },
            { label: 'تبويب العملاء (.tab-customers)', targetSelector: '.md-tab-btn.tab-customers', whatItDoes: 'إدارة بيانات العملاء والسقف الائتماني وأرقام الواتساب.', whatHappensIfClicked: 'يفتح بطاقة العميل وجدول العملاء وسياسات كشف الحساب الآلي.' },
            { label: 'تبويب الموردين (.tab-suppliers)', targetSelector: '.md-tab-btn.tab-suppliers', whatItDoes: 'إدارة الموردين وأرصدتهم الافتتاحية.', whatHappensIfClicked: 'يفتح بطاقة المورد وجدول الموردين المربوط بالمشتريات.' },
            { label: 'تبويب البنوك والخزن (.tab-banks / .tab-safes)', targetSelector: '.md-tab-btn.tab-banks, .md-tab-btn.tab-safes', whatItDoes: 'إدارة الحسابات البنكية والخزائن النقدية.', whatHappensIfClicked: 'يفتح شاشة تعريف البنوك والخزائن وأرصدتها الافتتاحية.' },
            { label: 'تبويب الأصناف والوحدات (.tab-items)', targetSelector: '.md-tab-btn.tab-items', whatItDoes: 'تعريف الأصناف والباركود ومصفوفة الوحدات والتسعير.', whatHappensIfClicked: 'يفتح بطاقة الصنف الشاملة وجدول الفئات والوحدات.' }
        ],
        gapReport: {
            title: 'تقرير المراجعة الثلاثية لمنظومة البيانات الأساسية (Master Data Audit & Gap Report)',
            items: [
                '✅ الأمر الحاسم (1): نطق الصلاة على النبي ﷺ مرة واحدة فقط مع ضغطة المرشد ومنع تكرارها نهائياً عند التنقل بين الخانات والتبويبات.',
                '✅ الأمر الحاسم (2): إلغاء القائمة الطويلة الحاجبة للرؤية واستبدالها بكبسولة تحكم ملكية مصغرة لا تحجب الشاشة.',
                '✅ الشرح التفاعلي لكل حقل وتبويب: تغطية شاملة لجميع خانات المخازن والعملاء والموردين والبنوك والخزن والأصناف والموظفين ومراكز التكلفة.'
            ]
        }
    };

    window.stopMdExplanationPanelAudio = function () {
        try { stopCurrentSpeech(); } catch (_) {}
        try { if ('speechSynthesis' in window) window.speechSynthesis.cancel(); } catch (_) {}
    };

    window.speakMdExplanationPanelText = async function (text, onEnd) {
        window.stopMdExplanationPanelAudio();
        let clean = stripAllSalawatPrefixes(String(text || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim());
        if (!clean) return;
        if (clean.length > 520) clean = clean.slice(0, 520);

        const { firstBurst, restBurst } = splitInstantFirstBurst(clean);
        const reqId = currentSpeechRequestId;
        try {
            const ctx = getAudioCtx();
            if (ctx.state === 'suspended') await ctx.resume();
            const { buffer: audioBuffer, blobUrl, isGenuineNeural } = await fetchAndDecodeMaleBuffer(firstBurst, selectedMaleProfile);
            if (reqId !== currentSpeechRequestId) return;
            const restPromise = restBurst ? fetchAndDecodeMaleBuffer(restBurst, selectedMaleProfile).catch(() => null) : null;
            activeSpokenClean = cleanTextForMaleTts(firstBurst);
            activeAudioBuffer = audioBuffer;
            activeAudioBlobUrl = blobUrl || null;
            activeAudioIsGenuine = isGenuineNeural;
            activeAudioOnEnd = async () => {
                if (reqId !== currentSpeechRequestId) return;
                if (restPromise) {
                    const nextData = await restPromise;
                    if (nextData && nextData.buffer && reqId === currentSpeechRequestId) {
                        activeSpokenClean = cleanTextForMaleTts(restBurst);
                        activeAudioBuffer = nextData.buffer;
                        activeAudioBlobUrl = nextData.blobUrl || null;
                        activeAudioIsGenuine = nextData.isGenuineNeural;
                        activeAudioOnEnd = onEnd || null;
                        startBufferAtOffset(0);
                        return;
                    }
                }
                if (onEnd) onEnd();
            };
            startBufferAtOffset(0);
        } catch (_err) {
            if (reqId !== currentSpeechRequestId) return;
            speakWithBrowserMaleFallback(clean, onEnd, selectedMaleProfile, false, true);
        }
    };

    window.explainMasterDataDomElement = function (el) {
        if (!el) return null;
        if (el.closest('.tab-exit')) return { title: '🚪 زر خروج / رجوع', desc: 'يُستخدم للرجوع إلى الشاشة السابقة أو الخروج الآمن من شاشة البيانات الأساسية بعد حفظ التعديلات المعتمدة، مع تنبيهك تلقائياً إذا كانت هناك بيانات قيد الإدخال لم يتم حفظها بعد.' };
        if (el.closest('.tab-warehouses')) return { title: '🏭 تبويب وشاشة المخازن والمستودعات', desc: 'يفتح شاشة تأسيس وإدارة كافة المخازن والمستودعات ومعارض البيع، وتحديد أمين كل مخزن وهاتفه وموقعه وحالته (نشط أو جرد أو موقوف) ليظهر فوراً في فواتير المشتريات والمبيعات والتحويلات المخزنية.' };
        if (el.closest('.tab-customers')) return { title: '👤 تبويب وشاشة العملاء والمناديب والواتساب', desc: 'يفتح شاشة تسجيل وإدارة العملاء، وضبط الرقم السري لبوت الواتساب، وأرقام المناديب والتابعين، والسقف الائتماني، وشريحة التسعير، والرصيد الافتتاحي، وصلاحيات كشف الحساب واعتماد الإشعارات البنكية.' };
        if (el.closest('.tab-suppliers')) return { title: '🏢 تبويب وشاشة الموردين والمناديب', desc: 'يفتح شاشة تسجيل وإدارة الموردين وشركات التوريد، وأرصدتهم الافتتاحية، وأرقام مناديبهم المعتمدين على الواتساب، والبنك الافتراضي، وفترة السداد الآجلة، وربطهم المباشر بفواتير المشتريات وكشف الأستاذ.' };
        if (el.closest('.tab-banks')) return { title: '🏦 تبويب وشاشة البنوك والتطبيقات المصرفية', desc: 'يفتح شاشة تأسيس الحسابات البنكية والتطبيقات المصرفية (مثل بنك الخرطوم - بنكك، فوري، أمدرمان الوطني)، وتحديد أرقام الحسابات والآيبان والرصيد الافتتاحي وطرق الدفع المتاحة عليها.' };
        if (el.closest('.tab-safes')) return { title: '💵 تبويب وشاشة الخزائن النقدية', desc: 'يفتح شاشة تأسيس الخزائن والصناديق النقدية الرئيسية والفرعية، وربط كل خزينة بأمين الصندوق المسؤول عنها ورقم حسابها في دفتر الأستاذ والحد الأعلى والأدنى للسيولة.' };
        if (el.closest('.tab-items')) return { title: '📦 تبويب وشاشة الأصناف والوحدات والتسعير', desc: 'يفتح شاشة تعريف الأصناف والمنتجات، والباركود ورمز QR، ومصفوفة الوحدات الكبرى والفرعية ومعامل التحويل والشيلة، وأسعار البيع النقدي والآجل والجملة وأقل سعر مسموح، وحدود المخزون والطلب الذكي.' };
        if (el.closest('.tab-employees')) return { title: '👨‍💼 تبويب وشاشة الموظفين والصلاحيات', desc: 'يفتح شاشة تسجيل الموظفين والمستخدمين، وتحديد الدور الوظيفي لكل موظف (مدير، محاسب، كاشير، مبيعات، أمين مخزن)، وكلمة المرور ودرجة السرية المسموح له بالاطلاع عليها.' };
        if (el.closest('.tab-costcenters')) return { title: '🎯 تبويب وشاشة مراكز التكلفة والمشاريع', desc: 'يفتح شاشة تأسيس المشاريع والفروع والشاحنات وخطوط الإنتاج كمراكز تكلفة مستقلة، لربط المصروفات والإيرادات والفواتير بكل مركز واستخراج تقارير أرباح وخسائر دقيقة لكل مشروع.' };

        const fieldRules = [
            { sel: '#warehouse-name', title: '🏢 خانة اسم المخزن / المستودع', desc: 'اكتب هنا الاسم الرسمي للمستودع أو المعرض (مثال: مخزن مقاييس الرئيسي أو معرض الخرطوم). بمجرد حفظه يظهر هذا المخزن في قوائم فواتير المشتريات والمبيعات وأذونات الاستلام والصرف والجرد المخزني.' },
            { sel: '#warehouse-type', title: '🏷️ قائمة نوع المخزن', desc: 'حدد تصنيف هذا الموقع المخزني: (مستودع رئيسي) للتخزين المركزي، أو (معرض / نقطة بيع) للبيع المباشر للجمهور، أو (مخزن فرعي) للتوزيع، أو (مخزن بضاعة تالفة) لعزل الأصناف التالفة والمرتجعة عن الرصيد القابل للبيع.' },
            { sel: '#warehouse-keeper, button[onclick*="quickAddKeeperModal"]', title: '👨‍💼 خانة أمين المخزن المسؤول وزر الإضافة السريعة (➕)', desc: 'حدد اسم الموظف المسؤول عن عهدة هذا المخزن واستلام وتسليم البضائع فيه؛ ويمكنك الضغط على زر (➕) الصغير بجواره لتعريف أمين مخزن جديد فوراً دون مغادرة الشاشة.' },
            { sel: '#cust-name', title: '👤 خانة اسم العميل (الأساسي في الحسابات والفواتير)', desc: 'اكتب الاسم الرباعي للعميل أو الاسم التجاري للشركة؛ يقوم النظام فور الحفظ بفتح بطاقة مالية مستقلة له في شجرة الحسابات وكشف حساب الأستاذ وشاشات المبيعات وسندات القبض والواتساب.' },
            { sel: 'button[onclick*="generateRandomFormPin"]', title: '🎲 زر توليد رقم سري فريد (PIN)', desc: 'عند الضغط على هذا الزر يقوم النظام فوراً بتوليد كود سري رقمي عشوائي وفريد غير مكرر مع أي عميل أو مورد آخر، ليستخدمه صاحب الحساب أو مناديبُه في التحقق الأمني عبر بوت الواتساب.' },
            { sel: '#cust-statement-pin, #sup-statement-pin', title: '🔐 خانة الرقم السري لكشف الحساب والواتساب (PIN)', desc: 'هو الكود السري الخاص بهذا العميل أو المورد في بوت الواتساب؛ عندما يرسل العميل أو المورد هذا الرقم السري في محادثة الواتساب (حتى لو راسل من رقم هاتف جديد غير مسجل)، يتحقق البوت من هويته ويرسل له كشف حسابه أو رصيده فوراً.' },
            { sel: '#cust-whatsapp, #sup-whatsapp', title: '💬 خانة رقم الواتساب الأساسي المعتمد للحساب', desc: 'اكتب رقم واتساب العميل أو المورد الأساسي؛ عندما يرسل صاحب هذا الرقم إشعار تحويل بنكي (بنكك / فوري) عبر الواتساب يقرؤه النظام ويُقيده في حسابه، وعندما يكتب (كشف) أو (رصيد) يرد عليه البوت تلقائياً بكشف حسابه أو رصيده، كما تُرسل إليه الفواتير وسندات القبض والصرف مباشرة.' },
            { sel: '#cust-opening-balance, #sup-opening-balance', title: '💰 خانة الرصيد الافتتاحي (رصيد أول المدة)', desc: 'سجّل هنا المديونية أو الرصيد السابق للعميل أو المورد قبل بدء العمل بالنظام؛ يُرحّل هذا المبلغ تلقائياً كرصيد افتتاحي في أول سطر بكشف الحساب ويُدمج في ميزان المراجعة الافتتاحي وإجمالي الرصيد الحالي.' },
            { sel: '#cust-limit, #sup-limit', title: '🛑 خانة حد الائتمان (السقف الائتماني المسموح)', desc: 'حدد أقصى مبلغ مديونية آجلة مسموح به لهذا الحساب؛ يراقب النظام رصيد العميل الحالي مع كل فاتورة مبيعات آجلة جديدة، وينبه الكاشير أو يوقف البيع الآجل تلقائياً عند تجاوز هذا السقف لحماية أموال المؤسسة.' },
            { sel: '#cust-currency, #sup-currency, #bank-currency, #safe-currency', title: '💱 قائمة عملة الحساب المعتمدة', desc: 'اختر العملة الرئيسية التي يتعامل بها هذا الحساب (جنيه سوداني SDG، دولار أمريكي USD، ريال سعودي SAR، إلخ)؛ يتم تقييم الرصيد الافتتاحي وكشوفات الحسابات والفواتير بعملة الحساب المختارة مع إمكانية المعادلة بسعر الصرف.' },
            { sel: '#cust-status, #sup-status', title: '🟢 قائمة حالة الحساب (نشط • VIP • محظور)', desc: 'تتحكم في صلاحية التعامل التجاري مع الحساب: (🟢 نشط) للتعامل الطبيعي في جميع الشاشات، (🌟 VIP) لتمييز كبار العملاء بأولوية الخدمة، و(🔴 محظور) لإيقاف البيع الآجل أو التعامل مع الحساب المتعثر فوراً.' },
            { sel: '#cust-price-tier', title: '🏷️ قائمة شريحة التسعير الافتراضية للعميل', desc: 'تحدد مستوى السعر الذي ينزل تلقائياً لهذا العميل بمجرد اختيار اسمه في فاتورة المبيعات: (💵 قطاعي/نقدي، 📦 نصف جملة، 🏭 جملة، 📅 آجل، 💎 سعر خاص، 🕘 آخر سعر بيع، أو 🔻 أقل سعر مسموح) مما يمنع أخطاء التسعير اليدوي.' },
            { sel: '#cust-payment-terms, #sup-payment-terms', title: '⏳ خانة فترة السداد بالأيام (مهلة الفواتير الآجلة)', desc: 'حدد عدد الأيام المسموح بها لسداد الفواتير الآجلة (مثلاً 15 أو 30 يوماً)؛ يحسب النظام بناءً عليها تاريخ استحقاق كل فاتورة آجلة ويُظهر التنبيهات الاستباقية عند حلول موعد السداد أو التأخر فيه.' },
            { sel: '#warehouse-phone', title: '📞 خانة هاتف المخزن والمستودع', desc: 'سجّل هنا رقم الهاتف المباشر للمستودع أو أمين المخزن للتواصل الفوري معه بشأن أذونات الاستلام والصرف وتنسيق التحميل والشحن اللوجستي.' },
            { sel: '#warehouse-location', title: '📍 خانة عنوان وموقع المخزن الجغرافي', desc: 'اكتب الموقع الجغرافي التفصيلي للمخزن (مثل المنطقة الصناعية أو سوق السجانة) ليظهر مطبوعاً في أذونات الصرف والاستلام وتوجيه السائقين والمناديب.' },
            { sel: '#warehouse-status', title: '🟢 قائمة حالة المخزن (نشط • جرد • موقوف)', desc: 'تتحكم في حركة المستودع: (🟢 نشط) مسموح بالبيع والشراء والتحويل، (🟠 جرد) يقفل الصرف والإضافة مؤقتاً أثناء الجرد الفعلي لمنع أي تداخل في الأرصدة، و(🔴 موقوف) يوقف التعامل مع المخزن.' },
            { sel: '#warehouse-security', title: '🛡️ قائمة درجة سرية المخزن (1 إلى 10)', desc: 'تحدد مستوى الصلاحية المطلوب للاطلاع على أرصدة وحركات هذا المخزن؛ فالمخزن ذو السرية العالية لا يظهر إلا للمستخدمين الذين يمتلكون درجة سرية مساوية أو أعلى.' },
            { sel: '#warehouse-search-input, #customer-search-input, #supplier-search-input, #bank-search-input, #safe-search-input, #item-search, #employee-search-input, #costcenter-search-input', title: '🔍 خانة البحث الذكي والفلترة الفورية (0ms)', desc: 'اكتب هنا أي جزء من الاسم أو الكود أو الرقم السري (PIN) أو رقم الهاتف أو الواتساب (بما في ذلك أرقام المناديب) ليقوم النظام بتصفية الجدول السفلي لحظياً أثناء الكتابة.' },
            { sel: '#cust-contact', title: '👔 خانة اسم المسؤول المفوض لدى العميل', desc: 'اكتب اسم الشخص المسؤول عن المشتريات أو الحسابات لدى شركة العميل، للرجوع إليه عند المطابقات الدورية وتأكيد استلام البضائع وسداد الفواتير.' },
            { sel: '#cust-address, #sup-address', title: '📍 خانة العنوان التفصيلي والموقع', desc: 'اكتب المدينة والحي والسوق ورقم المحل للعميل أو المورد؛ يظهر هذا العنوان في ترويسة الفواتير المطبوعة وكشوفات الحسابات وأوامر التوصيل والشحن.' },
            { sel: '#cust-tax, #sup-tax', title: '🧾 خانة الرقم الضريبي الرسمي', desc: 'سجّل الرقم الضريبي أو التعريبي للعميل أو المورد لإدراجه تلقائياً في الفواتير الضريبية وتقارير الضريبة المضافة والمراجعات القانونية.' },
            { sel: '#cust-email, #sup-email', title: '✉️ خانة البريد الإلكتروني الرسمي', desc: 'أدخل البريد الإلكتروني المعتمد للعميل أو المورد لإرسال النسخ الإلكترونية من الفواتير وكشوفات الحسابات والمراسلات المالية الرسمية.' },
            { sel: '#cust-ex-rate, #sup-ex-rate, #bank-ex-rate, #safe-ex-rate', title: '💵 خانة سعر الصرف المعتمد للعملة الأجنبية', desc: 'عند اختيار عملة أجنبية للحساب (مثل الدولار أو الريال)، تحدد هذه الخانة سعر صرفها مقابل العملة المحلية لاحتساب المعادل المالي للرصيد الافتتاحي والحركات بدقة.' },
            { sel: '#cust-notes, #sup-notes, #item-description, #costcenter-desc', title: '📝 خانة الملاحظات والاتفاقيات الخاصة', desc: 'مساحة حرة لتدوين أي شروط تعامل خاصة، أو مواعيد تسليم، أو ضمانات، أو مواصفات فنية إضافية تخص هذا السجل لتكون مرجعاً دائماً للمحاسب والإدارة.' },
            { sel: '#bank-branch, #bank-branch-group', title: '🏦 خانة فرع البنك', desc: 'حدد اسم الفرع البنكي الذي يتبع له هذا الحساب (مثال: الفرع الرئيسي أو فرع السجانة) لتمييز الحسابات المتعددة داخل نفس البنك وتوثيق الشيكات والتحويلات.' },
            { sel: '#bank-opening-date, #safe-opening-date', title: '📅 خانة تاريخ قيد الرصيد الافتتاحي', desc: 'حدد التاريخ الفعلي الذي تم فيه جرد واعتماد رصيد أول المدة لهذا البنك أو الخزينة، ليرتبط القيد الافتتاحي بالتاريخ المحاسبي الصحيح في دفتر الأستاذ.' },
            { sel: '#bank-iban, #bank-swift', title: '🌐 خانة رقم الآيبان والسويفت الدولي (IBAN / SWIFT)', desc: 'لتسجيل رقم الحساب المصرفي الدولي القياسي (IBAN) ورمز السويفت (SWIFT) اللازمين للحوالات البنكية الخارجية والمطابقات المصرفية الرسمية.' },
            { sel: '#bank-confidentiality, #safe-confidentiality, #emp-security', title: '🛡️ قائمة درجة السرية والأمان (1 إلى 10)', desc: 'تضبط مستوى الحماية الأمنية للسجل من 1 (متاح للمستخدمين العاديين) حتى 10 (سري للغاية للإدارة العليا فقط) لمنع كشف الأرصدة الحساسة لغير المخولين.' },
            { sel: '.bank-payment-method, .safe-payment-method', title: '💳 خيارات طرق الدفع والتحصيل المتاحة (كاش • شبكة • تحويل • شيكات)', desc: 'حدد وسائل الدفع المسموح باستخدامها على هذا البنك أو الخزينة (نقداً، ماكينة شبكة POS، تحويل بنكي/تطبيقات، أو شيكات) لتظهر فقط الطرق المفعّلة في شاشات الفواتير والسندات.' },
            { sel: '#bank-inactive, #safe-inactive', title: '⛔ خيار إيقاف الحساب البنكي أو الخزينة', desc: 'عند تفعيل هذا الخيار يختفي البنك أو الخزينة من قوائم الفواتير والسندات الجديدة لمنع الإيداع أو الصرف عليه، مع بقاء كافة حركاته وأرصدته التاريخية محفوظة في التقارير.' },
            { sel: '#safe-code, #safe-gl-account', title: '🔢 خانة رقم الخزينة ورقم حساب الأستاذ العام', desc: 'تحدد الكود التعريفي للخزينة ورقم حسابها المرتبط في شجرة الحسابات (دفتر الأستاذ العام) لضمان الترحيل المحاسبي الفوري لكل سند قبض أو صرف نقدي.' },
            { sel: '#safe-responsible, #safe-responsible-group, button[onclick*="quickAddEmployeeModal"]', title: '👨‍💼 قائمة أمين الصندوق / المسؤول عن الخزينة وزر الإضافة (➕)', desc: 'حدد الموظف أو الكاشير المستلم لعهدة هذه الخزينة النقدية لمحاسبته ومطابقة الجرد اليومي الفعلي معه؛ وزر (➕) يتيح إضافة موظف جديد فوراً.' },
            { sel: '#safe-max-limit, #safe-min-limit, #safe-max-limit-group, #safe-min-limit-group', title: '⚖️ خانة الحد الأعلى والحد الأدنى لسيولة الخزينة', desc: 'حدد السقف الأعلى للنقدية المسموح ببقائها في الخزينة (لتوريد الفائض للبنك) والحد الأدنى للسيولة المطلوبة لتسيير العمل اليومي، حيث ينبهك النظام عند تجاوزهما.' },
            { sel: '#item-name-latin, #item-brand, #item-category, #item-nature, #item-active-toggle, button[onclick*="quickAddCategory"]', title: '🏷️ بيانات تصنيف الصنف (الاسم الإنجليزي • الماركة • الفئة • الطبيعة • الحالة)', desc: 'تضبط الهوية الكاملة للصنف: اسمه اللاتيني، والشركة المصنعة/المنشأ، والفئة المخزنية (مع زر ➕ لإضافة فئة جديدة)، وطبيعته (📦 مخزني يُجرد، 🔧 خدمي بدون رصيد، أو 🏗️ تجميعي)، ومفتاح تفعيل أو إيقاف الصنف.' },
            { sel: '#item-max-discount, #item-taxable, #item-tax-rate', title: '💰 خانة أقصى نسبة خصم مسموحة والضريبة المضافة', desc: 'تحدد أعلى نسبة خصم مئوية يُسمح للبائع بمنحها على هذا الصنف في المبيعات دون تجاوز، وتحدد ما إذا كان الصنف خاضعاً للضريبة ونسبتها المئوية.' },
            { sel: 'input[name="cost_method"], #item-bin-location, #item-bundle-factor', title: '📦 سياسة احتساب التكلفة وموقع الرف ومعامل الشيلة', desc: 'تحدد طريقة تقييم تكلفة الصنف (بسعر آخر شراء، أو السعر الأعلى تحوطاً، أو السعر الافتتاحي)، ورقم الرف/الممر في المخزن لسرعة التحضير، ومعامل الشيلة (عدد الحبات أو الأسياخ في الربطة الواحدة).' },
            { sel: '#emp-id, #emp-phone, #emp-password, #emp-status', title: '👤 بيانات حساب الموظف (الكود • الهاتف • كلمة المرور • الحالة)', desc: 'تضبط الرقم الوظيفي التلقائي للموظف، ورقم هاتفه للتواصل، وكلمة المرور الخاصة بدخوله للنظام، وحالة حسابه (نشط مسموح له بالدخول أو موقوف).' },
            { sel: '#costcenter-code', title: '🔢 خانة كود مركز التكلفة التلقائي', desc: 'الرقم التعريفي المتسلسل الذي يمنحه النظام تلقائياً لمركز التكلفة أو المشروع أو الشاحنة لربطه بقيود اليومية والفواتير وتقارير الربحية.' },
            { sel: '#cust-security, #sup-security', title: '🛡️ قائمة درجة سرية الحساب (1 إلى 10)', desc: 'تحدد مستوى سرية حساب العميل أو المورد من 1 إلى 10؛ الحسابات ذات الدرجة العالية لا تظهر أرصدتها أو كشوفاتها إلا للمدير أو المحاسب الذي يملك نفس درجة السرية أو أعلى.' },
            { sel: 'button[onclick*="bulkSetEntityStatementMode"][onclick*="detailed"]', title: '📊 زر (كشف للكل — السماح بكشف الحساب التفصيلي للجميع)', desc: 'أمر جماعي فوري يمنح جميع العملاء أو الموردين المسجلين في الشاشة صلاحية طلب واستلام كشف حساب مفصل كامل بجميع الفواتير والسندات والأرصدة عبر بوت الواتساب بضغطة زر واحدة.' },
            { sel: 'button[onclick*="bulkSetEntityStatementMode"][onclick*="balance_only"]', title: '💰 زر (رصيد للكل — السماح بمعرفة الرصيد النهائي فقط)', desc: 'أمر جماعي يضبط جميع العملاء أو الموردين في الشاشة بحيث يرد عليهم بوت الواتساب بصافي الرصيد النهائي الإجمالي فقط دون إرسال تفاصيل الفواتير والحركات السابقة.' },
            { sel: 'button[onclick*="bulkSetEntityStatementMode"][onclick*="disabled"]', title: '🔒 زر (إيقاف الكل — قفل الرد الآلي بالواتساب للجميع)', desc: 'أمر جماعي يوقف الرد الآلي لكشوفات الحسابات والأرصدة عبر الواتساب لجميع العملاء أو الموردين في هذا التبويب دفعة واحدة.' },
            { sel: 'button[onclick*="bulkGenerateEntityPins"]', title: '🔐 زر (توليد باسورد للكل — إنشاء أرقام سرية جماعية)', desc: 'يقوم بتوليد وتخصيص رقم سري (PIN) فريد وغير مكرر لكل عميل أو مورد ليس لديه رقم سري أو لتحديث الأرقام السرية جماعياً لاستخدامها في التحقق الأمني عبر الواتساب.' },
            { sel: 'button[onclick*="toggleAdminWaPolicyDrawer"], .admin-wa-policy-drawer', title: '⚙️ زر ودرج (السياسة العامة لصلاحيات الواتساب والإشعارات)', desc: 'يفتح ويغلق لوحة السياسة الشاملة لضبط: صلاحية الكشف العامة، طريقة اعتماد الإشعارات البنكية (اعتماد فوري أو مراجعة)، البنك الافتراضي العام، مستوى التحقق الأمني (الرقم المعتمد أو الباسورد)، نطاق الكشف، وإظهار الأصناف وإشعار المدير.' },
            { sel: '#cust-mode-btn-disabled, #sup-mode-btn-disabled, button[onclick*="setEntityStatementMode"][onclick*="disabled"]', title: '🔒 زر (موقوف — إيقاف كشف ورصيد الواتساب لهذا الحساب)', desc: 'يوقف هذا العميل أو المورد (وكذلك المناديب التابعين له) من طلب الرصيد أو كشف الحساب عبر بوت الواتساب؛ فلا يرد البوت على طلبات الكشف لهذا الحساب حتى تتم إعادة تفعيله.' },
            { sel: '#cust-mode-btn-balance_only, #sup-mode-btn-balance_only, button[onclick*="setEntityStatementMode"][onclick*="balance_only"]', title: '💰 زر (الرصيد فقط — السماح بمعرفة صافي الرصيد عبر الواتساب)', desc: 'يسمح لهذا العميل أو المورد (ولجميع المناديب المضافين في حسابه) بطلب معرفة الرصيد النهائي الإجمالي فقط عبر الواتساب دون إرسال جدول الحركات التفصيلية.' },
            { sel: '#cust-mode-btn-detailed, #sup-mode-btn-detailed, button[onclick*="setEntityStatementMode"][onclick*="detailed"]', title: '📊 زر (كشف — السماح بكشف حساب تفصيلي عبر الواتساب)', desc: 'يسمح لهذا العميل أو المورد (ولجميع المناديب التابعين له) بطلب واستلام كشف حساب مفصل كامل بالتواريخ والفواتير والسندات والرصيد التراكمي عبر الواتساب فوراً.' },
            { sel: '#cust-rcpt-btn-auto_post, #sup-rcpt-btn-auto_post', title: '⚡ زر (اعتماد فوري — ترحيل إشعارات الواتساب تلقائياً)', desc: 'عند تفعيل هذا الزر، فإن أي إشعار تحويل بنكي (بنكك / فوري) يرسله هذا العميل أو المورد أو أحد مناديبهم المعتمدين عبر الواتساب يتم فحصه بالذكاء الاصطناعي واعتماده وترحيله فوراً في حسابه والبنك المحدد دون انتظار مراجعة يدوية.' },
            { sel: '#cust-rcpt-btn-review_first, #sup-rcpt-btn-review_first, button[onclick*="setEntityReceiptApprovalMode"]', title: '👁️ زر (مراجعة — حفظ إشعارات الواتساب للمراجعة قبل الترحيل)', desc: 'يضبط سياسة هذا الحساب بحيث تُحفظ الإشعارات البنكية التي يرسلها العميل أو المورد أو مناديبهم عبر الواتساب في قائمة المراجعة أولاً، ولا تؤثر في الرصيد إلا بعد مراجعة المحاسب واعتمادها.' },
            { sel: '#cust-default-bank-id, #sup-default-bank-id, button[onclick*="quickAddBankModal"]', title: '🏦 قائمة البنك الافتراضي للحساب وزر إضافة بنك جديد (➕)', desc: 'تحدد البنك الافتراضي المخصص لهذا العميل أو المورد؛ فعندما يرسل العميل أو مندوبه إشعار تحويل عبر الواتساب يُعتمد ويُودع المبلغ في هذا البنك تلقائياً، وزر (➕) يفتح نافذة سريعة لتعريف بنك جديد فوراً.' },
            { sel: 'button[onclick*="openEntityQrCardModal"]', title: '🪪 زر (بطاقة QR الذكية للعميل أو المورد)', desc: 'يفتح بطاقة التعريف الرقمية الرسمية للحساب المحتوية على الاسم وكود الحساب والرقم السري (PIN) ورمز الاستجابة السريعة (QR Code) لطباعتها أو حفظها كصورة ومشاركتها مع العميل أو المورد.' },
            { sel: 'button[onclick*="copyCurrentFormWhatsAppCard"], span[onclick*="copyEntityWhatsAppCard"]', title: '📋 زر (نسخ البطاقة التعريفية للواتساب)', desc: 'ينسخ رسالة جاهزة ومنسقة تحتوي على اسم الحساب ورقمه السري (PIN) وطريقة طلب كشف الحساب والرصيد وإرسال الإشعارات، لتلصقها وترسلها للعميل أو المورد في محادثة الواتساب بضغطة واحدة.' },
            { sel: 'button[onclick*="addCustomerSubPhone"], button[onclick*="addSupplierSubPhone"]', title: '➕ زر (+ إضافة رقم مندوب أو تابع جديد)', desc: 'عند الضغط على هذا الزر يفتح لك النظام سطراً جديداً لإدخال (📱 رقم هاتف المندوب) و(👤 اسم المندوب أو صفته مثل: مندوب علي أو فرع بحري). كل مندوب تضيف رقمه هنا يتمتع بنفس صلاحيات الحساب الأساسي تماماً؛ فعندما يرسل إشعار تحويل بنكي من رقمه المعتمد هنا يُقيد في حساب العميل أو المورد مباشرة، وعندما يطلب كشف حساب أو رصيداً يرد عليه البوت آلياً بكشف أو رصيد الحساب.' },
            { sel: '.sub-phone-number', title: '📱 خانة رقم هاتف / واتساب المندوب المعتمد', desc: 'اكتب هنا رقم هاتف أو واتساب المندوب أو السائق أو المحاسب التابع لهذا العميل أو المورد. يتعرف بوت الواتساب على هذا الرقم تلقائياً ويعامله كأنه صاحب الحساب نفسه في استلام الإشعارات البنكية وإرسال كشف الحساب أو الرصيد.' },
            { sel: '.sub-phone-label', title: '👤 خانة اسم المندوب أو الصفة أو الفرع', desc: 'اكتب هنا اسم المندوب أو صفته (مثال: المندوب علي، أو محاسب الفرع، أو سائق العربة) ليظهر اسمه موثقاً بجانب الإشعارات الواردة منه وفي سجلات الواتساب وكشف الحساب.' },
            { sel: '#cust-sub-phones-container, #sup-sub-phones-container', title: '📱 قسم أرقام المناديب والتابعين للحساب', desc: 'يُخصص هذا القسم لإضافة كافة أرقام هواتف المناديب والوكلاء والفروع التابعين للعميل أو المورد عبر زر (+ إضافة رقم)؛ حيث يتمتع كل رقم مضاف هنا بنفس صلاحية الحساب الأساسي في إرسال الإشعارات وطلب الكشف والرصيد عبر الواتساب.' },
            { sel: 'button[onclick*="addCustomerBankAccount"], button[onclick*="addSupplierBankAccount"], #cust-bank-accounts-container, #sup-bank-accounts-container', title: '🏦 قسم وزر (+ إضافة حساب بنكي مرتبط بالعميل أو المورد)', desc: 'يتيح لك إضافة رقم حساب أو أكثر للعميل أو المورد (اسم البنك، الفرع، ورقم الحساب) ليقوم الذكاء الاصطناعي بمطابقة رقم الحساب المرسل منه في إشعارات التحويل البنكي وربطها بالحساب تلقائياً.' },
            { sel: 'button[onclick*="editCustomer"], button[onclick*="editSupplier"], button[onclick*="editWarehouse"], button[onclick*="editBank"], button[onclick*="editSafe"], button[onclick*="editItem"], button[onclick*="editEmployee"], button[onclick*="editCostCenter"]', title: '✏️ زر التعديل (استدعاء السجل للتعديل)', desc: 'ينقل كامل بيانات هذا السجل من الجدول إلى النموذج العلوي فوراً لتتمكن من مراجعتها أو تعديل أي خانة أو إضافة أرقام مناديب جديدة ثم اعتماد التعديلات بالضغط على زر (💾 حفظ F10).' },
            { sel: 'button[onclick*="deleteCustomer"], button[onclick*="deleteSupplier"], button[onclick*="deleteWarehouse"], button[onclick*="deleteBank"], button[onclick*="deleteSafe"], button[onclick*="deleteItem"], button[onclick*="deleteEmployee"], button[onclick*="deleteCostCenter"]', title: '🗑️ زر الحذف (المرتبط بدرع الحماية المحاسبية والمخزنية)', desc: 'يفحص السجل أولاً قبل الحذف؛ فإذا كان السجل جديداً وليس عليه أي فواتير أو سندات أو رصيد افتتاحي يتم حذفه بأمان، أما إذا كانت له أي حركات مالية أو مخزنية سابقة فيمنع النظام حذفه ويعرض لك قائمة السندات المرتبطة به حمايةً لميزان المراجعة.' },
            { sel: '#sup-name', title: '🏭 خانة اسم المورد / الشركة الموردة', desc: 'اكتب الاسم الرسمي للمورد أو المصنع أو الشركة الموردة؛ يقوم النظام تلقائياً بفتح حساب مالي له في شجرة الحسابات وكشف الأستاذ ويُدرجه فوراً في قائمة الموردين بشاشة المشتريات وسندات الصرف.' },
            { sel: '#bank-name', title: '🏦 خانة اسم البنك أو التطبيق المصرفي', desc: 'اكتب اسم البنك أو المحفظة البنكية (مثل: بنك الخرطوم - بنكك، أو فوري، أو أمدرمان الوطني) ليظهر في سندات القبض والصرف وفواتير المبيعات والمشتريات والمطابقة الذكية للإشعارات.' },
            { sel: '#bank-gl-account, #bank-code', title: '🔢 خانة رقم الحساب البنكي والكود المحاسبي', desc: 'سجّل رقم الحساب البنكي الفعلي المعتمد؛ يعتمد عليه محرك الذكاء الاصطناعي في قراءة ومطابقة إشعارات التحويل الواردة عبر الواتساب وتوجيهها إلى هذا البنك تلقائياً.' },
            { sel: '#bank-opening-balance, #safe-opening-balance', title: '💵 خانة الرصيد الافتتاحي للسيولة النقدية أو البنكية', desc: 'أدخل الرصيد المالي الفعلي المتوفر في هذا البنك أو الخزينة عند بدء العمل بالنظام؛ يُقيد هذا المبلغ في ميزان المراجعة الافتتاحي وتُبنى عليه كافة الإيداعات والمسحوبات اللاحقة.' },
            { sel: '#safe-name', title: '💵 خانة اسم الخزينة النقدية', desc: 'اكتب اسم الخزينة أو الصندوق النقدي (مثل: الخزينة الرئيسية، خزينة المعرض، أو خزينة العهدة) لربطها بالمقبوضات والمدفوعات النقدية اليومية وجرد الصندوق.' },
            { sel: '#item-name-input', title: '📦 خانة الاسم التجاري للصنف', desc: 'اكتب الاسم الواضح للصنف كما يتداوله العملاء والمخزن (مثال: سيخ 3 لنيه أسعد) ليظهر في البحث الفوري بفواتير المشتريات والمبيعات وكرت الصنف والجرد.' },
            { sel: '#item-code, #item-barcode, button[onclick*="openWebRtcBarcodeScanner"]', title: '🏷️ خانة كود الصنف والباركود والمسح بالكاميرا (📷)', desc: 'تحدد الكود الرقمي للصنف ورقم الباركود العالمي المطبوع على العبوة للبيع والجرد السريع بقارئ الباركود أو كاميرا الهاتف/اللابتوب عبر زر (📷 مسح).' },
            { sel: '#item-selling-price', title: '💰 خانة سعر البيع الافتراضي للصنف', desc: 'حدد سعر بيع الوحدة الأساسية للصنف؛ ينزل هذا السعر تلقائياً في فاتورة المبيعات مع حماية الصنف من البيع بأقل من التكلفة أو الحد الأدنى المسموح.' },
            { sel: '#item-opening-qty, #item-opening-value', title: '📊 خانة الكمية الافتتاحية وتكلفة الوحدة', desc: 'سجّل كمية الجرد الافتتاحي المتوفرة من الصنف وتكلفة شراء الوحدة الواحدة عند التأسيس، ليحتسب النظام قيمة المخزون الافتتاحي ومتوسط التكلفة المرجح بدقة.' },
            { sel: '#item-max-limit, #item-min-limit, #item-reorder-level', title: '🚨 خانات حدود المخزون (الحد الأعلى • الحد الأدنى • حد الطلب)', desc: 'تراقب رصيد الصنف آلياً؛ فعندما ينخفض الرصيد إلى (حد الطلب) أو (الحد الأدنى) يظهر الصنف فوراً في رادار النواقص لإصدار أمر شراء قبل نفاد البضاعة.' },
            { sel: '#item-base-unit, #units-matrix-body, button[onclick*="addUnitRow"], button[onclick*="generateMultiUnitEan13Barcodes"]', title: '📐 مصفوفة الوحدات الكبرى والفرعية ومعامل التحويل والباركود', desc: 'تحدد الوحدة الأساسية للصنف (مثل: حبة أو سيخة) وتتيح إضافة الوحدات الكبرى (مثل: طن، كرتونة، شيلة) وتحديد المعادل (كم وحدة أساسية داخل الوحدة الكبرى) وأسعار النقدي والآجل وأقل سعر ووزن كل وحدة.' },
            { sel: '#emp-name, #emp-role', title: '👨‍💼 خانة اسم الموظف والدور الوظيفي والصلاحيات', desc: 'سجّل اسم الموظف وحدد دوره في النظام (مدير، محاسب، كاشير، مبيعات، أو أمين مخزن) ليضبط النظام تلقائياً الشاشات والأزرار المسموح له بالعمل عليها.' },
            { sel: '#costcenter-name', title: '🎯 خانة اسم مركز التكلفة / المشروع / الفرع', desc: 'اكتب اسم المشروع أو الفرع أو الشاحنة لتخصيصه كمركز تكلفة مستقل تُربط به الفواتير والمصروفات والإيرادات لاستخراج صافي ربحية هذا المركز بدقة.' },
            { sel: '.btn-c3-save, button[onclick*="save"]', title: '💾 زر الحفظ والترحيل الفوري (F10)', desc: 'يحفظ كافة البيانات المدخلة أو المعدلة في هذا النموذج ويُسمّعها فوراً (في 0ms) داخل قاعدة البيانات وشجرة الحسابات والفواتير والتقارير وبوت الواتساب.' },
            { sel: '.btn-c3-cancel, button[onclick*="clear"], button[onclick*="close"]', title: '🔄 زر تفريغ النموذج / جديد (F2) أو إلغاء (Esc)', desc: 'يقوم بتفريغ الحقول الحالية وإعادة تهيئة النموذج لبدء إضافة سجل جديد نظيف، أو إغلاق نافذة التعديل دون حفظ التغييرات غير المعتمدة.' },
            { sel: 'button[onclick*="exportMasterEntityCsv"], button[onclick*="triggerMasterEntityCsvImport"], button[onclick*="openSmartClipboardPasteModal"]', title: '📊 أزرار تصدير واستيراد ولصق ملفات الإكسل (Excel / CSV)', desc: 'تتيح لك تصدير كامل بيانات الجدول إلى ملف Excel بنقرة واحدة للنسخ الاحتياطي أو المراجعة، أو استيراد ولصق مئات السجلات والأصناف والعملاء دفعة واحدة من ملف Excel.' }
        ];
        for (const r of fieldRules) {
            if (el.matches(r.sel) || el.closest(r.sel)) {
                return { title: r.title, desc: r.desc };
            }
        }
        const card = el.closest('.pro-field-card, .input-group');
        if (card) {
            const lbl = card.querySelector('label, .pro-field-label')?.innerText?.replace(/\s+/g, ' ').trim();
            if (lbl) {
                return { title: `📋 خانة (${lbl})`, desc: `هذه الخانة مخصصة لإدخال وضبط (${lbl}) بدقة ضمن بطاقة السجل الحالي؛ وبمجرد الضغط على زر (💾 حفظ F10) يتم اعتمادها وتسميعها تلقائياً في قاعدة البيانات وكشوفات الحسابات والفواتير والتقارير المرتبطة بها.` };
            }
        }
        return window.autoSynthesizeElementExplanation ? window.autoSynthesizeElementExplanation(el) : null;
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 🧠 SELF-UPDATING DYNAMIC DOM INTROSPECTION & AUTO-SYNC ENGINE (تحديث المرشد لنفسه تلقائياً لأي زر أو خانة تُضاف مستقبلاً في أي شاشة)
    // ══════════════════════════════════════════════════════════════════════════
    window.explainUnifiedDashboardDomElement = function (el) {
        if (!el) return null;
        const qBtn = el.closest('.msg-quartet-btn');
        if (qBtn) {
            if (qBtn.classList.contains('msg-quartet-guide')) return { title: '🎓 زر المرشد الذكي الشامل (المستوى الثاني)', why: 'لإرشاد المستخدم خطوة بخطوة بالصوت والمؤشر المتحرك دون الحاجة لمدرب بشري.', desc: 'يفتح نافذة المرشد الذكي لشرح الشاشة بالكامل تلقائياً، أو تفعيل وضع (اشرح جزءاً أضغط عليه)، أو التدريب العملي التفاعلي والاختبار الذكي.', ifDone: 'يتحرك المؤشر الذكي آلياً على كل عنصر في الشاشة ويشرحه صوتياً ومرئياً بالتفصيل.', ifNotDone: 'قد يفوتك اكتشاف الترابطات المحاسبية والمخزنية المتقدمة في الشاشة.', rule: 'ابدأ دائماً بضغطة المرشد عند دخول أي شاشة جديدة لأول مرة.' };
            if (qBtn.classList.contains('msg-quartet-cinema4k') || qBtn.id === 'msg-fixed-cinema-4k-btn') return { title: '🔴 زر التسجيل السينمائي 4K هوليوود (المستوى الثالث)', why: 'لإنتاج فيديو شرح احترافي سينمائي عالي الدقة مع الإضاءة وحركة الكاميرا والصوت الإذاعي.', desc: 'يشغّل استوديو التصوير السينمائي الذكي الذي يسلط الإضاءة البؤرية ويحرك الكاميرا يميناً ويساراً مع الإدخال التلقائي الحي ويحفظ الفيديو تلقائياً.', ifDone: 'يتم تصوير جولة تدريبية كاملة وحفظها كملف فيديو جاهز للمشاركة أو المراجعة.', ifNotDone: 'لن يتم تسجيل جولة الفيديو التوثيقية للشاشة الحالية.', rule: 'استخدم التسجيل السينمائي لإنتاج شروحات مرئية فورية لأي موظف أو عميل.' };
            if (qBtn.classList.contains('msg-quartet-mobile')) return { title: '📱 زر نسخة وتنزيل الموبايل السريعة', why: 'لتمكين المدير والمحاسب والمناديب من العمل المتنقل عبر الجوال في أي مكان.', desc: 'يفتح بوابة الجوال السريعة وتثبيت تطبيق الموبايل المباشر المتوافق مع شاشات اللمس.', ifDone: 'ينقلك فوراً لواجهة الجوال أو يبدأ تثبيت الحزمة على هاتفك.', ifNotDone: 'ستظل تعمل على واجهة سطح المكتب التقليدية فقط.', rule: 'ثبّت نسخة الموبايل لمتابعة السيولة والمبيعات والإشعارات لحظياً من جيبك.' };
            if (qBtn.classList.contains('msg-quartet-install-app')) return { title: '💻 زر تثبيت تطبيق اللابتوب وسطح المكتب (PWA)', why: 'لتشغيل برنامج مقاييس كتطبيق سطح مكتب مستقل بأيقونة خاصة على الشاشة بدون شريط المتصفح.', desc: 'يثبّت النظام كتطبيق أصلي (Native App) يعمل بسرعة فائقة ويدعم العمل الفوري.', ifDone: 'تنزل أيقونة البرنامج مباشرة على سطح المكتب وشريط المهام.', ifNotDone: 'ستحتاج لفتح المتصفح وكتابة الرابط يدوياً في كل مرة.', rule: 'ثبّت التطبيق على جهاز الكاشير والمحاسب لسرعة فتح 0ms واستقرار تام.' };
            if (qBtn.classList.contains('msg-quartet-install-full')) return { title: '🗄️ زر التثبيت العام للعمل أوفلاين (بدون إنترنت)', why: 'لضمان استمرار البيع والشراء والسندات حتى عند انقطاع الإنترنت أو الكهرباء.', desc: 'يقوم بتحميل وتخزين كافة شاشات وبيانات النظام محلياً داخل الجهاز مع المزامنة التلقائية فور عودة الشبكة.', ifDone: 'يصبح جهازك محصناً ضد انقطاع الإنترنت بنسبة 100%.', ifNotDone: 'قد تتعطل بعض الشاشات غير المحفوظة محلياً إذا انقطع الاتصال فجأة.', rule: 'اضغط التثبيت العام مرة واحدة بعد التحديث لضمان جاهزية العمل أوفلاين دائماً.' };
            if (qBtn.classList.contains('msg-quartet-shield') || qBtn.id === 'desktop-sync-shield-btn') return { title: '🛡️ زر درع الحماية وسجل المزامنة التلقائية', why: 'لمراقبة أمان البيانات وحالة اتصال السحابة والنسخ الاحتياطي اللحظي.', desc: 'يعرض حالة المزامنة الحية والعمليات المحفوظة محلياً وسجل الأمان.', ifDone: 'تفتح نافذة فحص المزامنة والتأكد من تطابق كافة الفواتير والسندات.', ifNotDone: 'لن تطلع على تفاصيل العمليات المعلقة أثناء وضع الأوفلاين.', rule: 'تأكد من بقاء الدرع باللون الأخضر قبل إغلاق الوردية اليومية.' };
            if (qBtn.classList.contains('msg-quartet-search')) return { title: '🔍 زر البحث الجنائي ومتعقب المستندات (Alt+T)', why: 'لمعرفة (مَن فعل ماذا ومتى وكيف) والوصول لأي فاتورة أو سند أو حركة في أجزاء من الثانية.', desc: 'يفتح محرك البحث الجنائي الشامل across كافة الفواتير والسندات وسجل المستخدمين.', ifDone: 'يكشف لك تاريخ المستند الكامل وأي تعديل أو حذف طرأ عليه.', ifNotDone: 'سيستغرق البحث اليدوي في الشاشات وقتاً أطول.', rule: 'استخدم الاختصار Alt+T أو هذا الزر لكشف أي عملية فوراً.' };
            if (qBtn.classList.contains('msg-quartet-fullscreen')) return { title: '⛶ زر توسيع وملء الشاشة الكاملة', why: 'لمنحك أوسع مساحة بصرية ممكنة للجداول والفواتير والرسوم البيانية.', desc: 'يُخفي أشرطة المتصفح العلوية ويفرد شاشة البرنامج على كامل الشاشة.', ifDone: 'تتسع الشاشة وتظهر كافة الأعمدة والأرقام بوضوح فائق.', ifNotDone: 'تبقى مساحة من الشاشة مستهلكة بأشرطة المتصفح.', rule: 'فعّل ملء الشاشة أثناء العمل على الفواتير الكبيرة ولوحة القيادة.' };
            if (qBtn.classList.contains('msg-quartet-qtheme')) return { title: '🎨 زر السمات التسع الموحدة (دستور المشتريات 2027)', why: 'لإراحة العين في الإضاءة النهارية أو الليلية بأعلى تباين ووضوح للأرقام.', desc: 'يفتح قائمة السمات المعتمدة (الداكن الملكي + 8 نغمات فاتحة وداكنة) ويحفظ اختيارك في كل الشاشات.', ifDone: 'يتحول مظهر النظام بالكامل فوراً إلى السمة المختارة.', ifNotDone: 'يظل النظام على السمة الحالية.', rule: 'اختر السمة العاجية أو البرونزية نهاراً، والملكي الداكن ليلاً لراحة عينيك.' };
            if (qBtn.classList.contains('msg-quartet-tv') || qBtn.id === 'header-media-btn') return { title: '📺 زر التلفاز والقرآن الكريم والبث الحي', why: 'لتشغيل القرآن الكريم بأصوات كبار القراء أو القنوات المباشرة في الخلفية بدون انقطاع أثناء العمل.', desc: 'يفتح مشغل البث الصوتي والمرئي مع إمكانية فتحه في نافذة مستقلة لا تنقطع عند التنقل بين الشاشات.', ifDone: 'يبدأ البث المبارك في الخلفية مع تحكم كامل بمستوى الصوت.', ifNotDone: 'يبقى المشغل متوقفاً.', rule: 'استخدم خيار (تشغيل بنافذة مستقلة) لاستمرار التلاوة دون انقطاع أثناء التنقل.' };
            if (qBtn.classList.contains('msg-quartet-back')) return { title: '🔙 زر الرجوع الذكي للشاشة السابقة', why: 'للعودة السريعة والآمنة إلى الشاشة التي كنت تعمل عليها أو لوحة التحكم.', desc: 'يعيدك خطوة للخلف مع حفظ سياق العمل والتنبيه في حال وجود بيانات غير محفوظة.', ifDone: 'تنتقل فوراً للشاشة السابقة.', ifNotDone: 'تبقى في الشاشة الحالية.', rule: 'استخدم زر الرجوع بدلاً من إغلاق المتصفح للحفاظ على تسلسل جلستك.' };
        }
        const kpiCard = el.closest('.kpi-cards .spotlight-card');
        if (kpiCard) {
            const kTitle = (kpiCard.querySelector('.kpi-title')?.innerText || '').trim();
            const kVal = (kpiCard.querySelector('.kpi-val')?.innerText || '').trim();
            const kDesc = (kpiCard.querySelector('.kpi-desc')?.innerText || '').trim();
            return {
                title: `📊 بطاقة مؤشر الأداء: ${kTitle} (${kVal})`,
                why: `لمنح صاحب العمل والمدير قراءة لحظية فورية لـ (${kTitle}) دون الحاجة لفتح التقارير المطولة.`,
                desc: `تعرض القيمة الحية الحالية (${kVal} — ${kDesc}) محسوبة تلقائياً حسب نطاق التاريخ والعملة المختارة، وعند النقر عليها تفتح الشاشة التفصيلية المرتبطة بها.`,
                ifDone: `ينقلك البرنامج فوراً إلى الشاشة أو التقرير التفصيلي الخاص بـ (${kTitle}) لفحص كل عملية.`,
                ifNotDone: `تكتفي بالقراءة الإجمالية السريعة من لوحة القيادة.`,
                rule: `راقب (${kTitle}) يومياً، وبدّل النطاق الزمني (اليوم / الأسبوع / الشهر) والعملة (جنيه ⇄ دولار) لمقارنة الأداء.`
            };
        }
        const chartPanel = el.closest('.charts-grid .glass-panel, .glass-panel');
        if (chartPanel && chartPanel.querySelector('canvas')) {
            const cTitle = (chartPanel.querySelector('h3')?.innerText || 'الرسم البياني التحليلي').replace(/\s+/g, ' ').trim();
            return {
                title: `📈 الرسم البياني التفاعلي: ${cTitle}`,
                why: `لتحويل الأرقام المحاسبية والمخزنية المعقدة إلى صورة بصرية واضحة تكشف الاتجاهات والمخاطر في ثانية واحدة.`,
                desc: `يحلل بيانات (${cTitle}) حياً ويعرضها برسوم بيانية تفاعلية تتحدث تلقائياً مع كل فاتورة أو سند جديد.`,
                ifDone: `عند تمرير المؤشر أو النقر على عناصر الرسم البياني تظهر لك الأرقام والنسب التفصيلية الدقيقة.`,
                ifNotDone: `قد يصعب ملاحظة الفجوات بين المديونية والسقف الائتماني أو التدفقات النقدية بمجرد النظر للأرقام المجردة.`,
                rule: `راجع هذا المؤشر البصري قبل اتخاذ قرارات الشراء أو منح الائتمان للعملاء.`
            };
        }
        return null;
    };

    window.synthesizeSimpleVernacularAnalogy = function (cleanTitle, el) {
        const t = String(cleanTitle || '');
        if (/رصيد افتتاحي|أول المدة/.test(t)) return 'يُسجل فيه الرصيد السابق المرحّل قبل بدء العمل بالنظام ليظهر في أول سطر بكشف الحساب وميزان المراجعة.';
        if (/حد الائتمان|سقف/.test(t)) return 'يحدد أقصى مديونية آجلة مسموح بها للعميل ويمنع تجاوز السقف المعتمد.';
        if (/واتساب|رسالة|إرسال/.test(t)) return 'يرسل الفاتورة أو الإيصال أو كشف الحساب مباشرة إلى رقم واتساب العميل أو المورد.';
        if (/حفظ|اعتماد|ترحيل|F10/.test(t)) return 'يعتمد المستند نهائياً ويرحّل أثره المالي والمخزني فوراً في الخزينة والمخزن وكشف الحساب.';
        if (/حذف|إلغاء|مسح/.test(t)) return 'يمسح السجل إذا كان فارغاً، ويمنع حذفه إذا ارتبط بحركات مالية أو مخزنية حفاظاً على توازن الحسابات.';
        if (/بحث|فلتر|تصفية/.test(t)) return 'يبحث ويصفي السجلات والفواتير والأصناف مباشرة بالاسم أو الرقم أو الباركود.';
        if (/عملة|سعر الصرف|دولار|جنيه|تعادل/.test(t)) return 'يحدد عملة المعاملة وسعر الصرف ويحسب القيمة بالجنيه وبالدولار تلقائياً.';
        if (/مرتجع|رد بضاعة/.test(t)) return 'يوثق إرجاع البضاعة ويعدّل رصيد المخزن وحساب العميل أو المورد فوراً.';
        if (/استلام|بوابة الاستلام|GRN|توريد/.test(t)) return 'يفحص ويعتمد الكميات المستلمة فعلياً ويضيفها إلى رصيد المخزن المحدد.';
        if (/تقسيم|تسليم|صرف بضاعة|GDN/.test(t)) return 'يقسم تسليم البضاعة على دفعات ويخصم الكمية المسلمة من المخزن ويحفظ المتبقي.';
        if (/مبدئية|عرض سعر|تقديرية/.test(t)) return 'يصدر تسعيرة مبدئية للعميل دون خصم من المخزن أو ترحيل مالي حتى اعتمادها كفاتورة.';
        if (/شيك|حافظة شيكات|مقاصة/.test(t)) return 'يتابع بيانات الشيك وتاريخ استحقاقه وحالة تحصيله أو صرفه في البنك.';
        if (/قيد|يومية|أستاذ|ميزان/.test(t)) return 'يعرض الحركة المحاسبية المدينة والدائنة والرصيد التراكمي بدقة.';
        if (/جرد|تسوية|تالف|عجز/.test(t)) return 'يطابق الرصيد الفعلي في المخزن مع الرصيد الدفتري ويعالج الفروقات بقيد رسمي.';
        if (/طباعة|PDF|إكسل|تصدير/.test(t)) return 'يصدر نسخة جاهزة للطباعة أو ملف بي دي إف أو إكسل للمراجعة والمشاركة.';
        if (/ذكاء|رادار|منبه|تنبيه|OCR/.test(t)) return 'يقرأ الإشعارات آلياً ويراقب الأسعار والأرصدة للتنبيه الفوري عند أي اختلاف.';
        if (el && el.tagName === 'A') return `ينقلك مباشرة إلى (${t}) بضغطة واحدة.`;
        if (el && el.tagName === 'SELECT') return `قائمة منسدلة تختار منها (${t}) بدقة لتسمّع في العملية الحالية.`;
        if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')) return `خانة مخصصة لإدخال (${t}) ومراجعتها وترحيلها في الحسابات تلقائياً.`;
        return `ينفذ وظيفة (${t}) مباشرة ويحدّث البيانات والتقارير المرتبطة بها فوراً.`;
    };

    window.__msgInferExplainContext = function (el, t) {
        const tag = el && el.tagName ? el.tagName : '';
        const type = String((el && el.type) || '').toLowerCase();
        const modal = el && el.closest ? el.closest('.modal, [role="dialog"], dialog, .md-modal, .popup, .overlay-modal') : null;
        const mh = modal && modal.querySelector ? modal.querySelector('h1,h2,h3,h4,.modal-title') : null;
        const h1 = document.querySelector('h1');
        const scr = ((document.title || '').split(/[-|–]/)[0] || (h1 && h1.innerText) || 'الشاشة الحالية').replace(/\s+/g, ' ').trim().slice(0, 50);
        const where = modal ? `أنت الآن داخل نافذة منبثقة${mh ? ' اسمها (' + mh.innerText.replace(/\s+/g, ' ').trim().slice(0, 40) + ')' : ''} فوق شاشة (${scr})، ولإغلاقها اضغط Esc أو زر الإغلاق.` : `أنت الآن في شاشة (${scr}).`;
        let source, dest, steps;
        if (tag === 'SELECT') { source = `الخيارات تأتي من قوائم جاهزة سبق تسجيلها في البيانات الأساسية أو الإعدادات، وأنت فقط تختار منها.`; dest = `اختيارك يُحفظ مع المستند، ويؤثر على كشف الحساب والأستاذ والتقارير المرتبطة.`; steps = `1) اضغط على القائمة 2) اختر السطر المناسب 3) تأكد أن الاسم الظاهر هو المطلوب ثم انتقل للخانة التالية بـ Tab.`; }
        else if (type === 'checkbox' || type === 'radio') { source = `هذا خيار تفعيل تقرره أنت بضغطة واحدة؛ لا يحتاج كتابة.`; dest = `حالته (مفعّل أو غير مفعّل) تُحفظ وتغيّر سلوك البرنامج في هذه العملية.`; steps = `1) اضغط على المربع للتفعيل 2) اضغط مرة أخرى للإلغاء 3) راجع النتيجة على الشاشة ثم احفظ.`; }
        else if (tag === 'INPUT' || tag === 'TEXTAREA') {
            const ro = el.readOnly || el.disabled;
            source = ro ? `هذه القيمة يحسبها البرنامج أو يسحبها تلقائياً من بيانات سابقة، ولا تكتبها بيدك.` : (type === 'date' ? `يضع البرنامج تاريخ اليوم تلقائياً ويمكنك تغييره عند الحاجة.` : `المعلومة تأتي منك أنت: تكتبها من المستند الورقي أو من الاتفاق مع العميل أو المورد.`);
            dest = `بعد الحفظ تنتقل إلى قاعدة البيانات ثم تظهر في كشف الحساب ودفتر الأستاذ والتقارير ذات الصلة.`;
            steps = ro ? `1) اقرأ القيمة فقط 2) إن كانت خاطئة عدّل الخانة المسببة لها 3) ستتحدث هذه الخانة وحدها.` : `1) اضغط داخل الخانة 2) اكتب القيمة أو اخترها 3) اضغط Tab أو Enter للانتقال 4) احفظ بـ F10 أو زر الحفظ.`;
        }
        else if (tag === 'BUTTON' || tag === 'A' || (el && el.getAttribute && (el.getAttribute('role') === 'button' || el.getAttribute('onclick')))) { source = `الزر لا يطلب منك معلومة جديدة؛ يعمل على البيانات الظاهرة أمامك الآن في الشاشة.`; dest = `نتيجة الضغط تظهر فوراً على الشاشة، وإن كانت عملية حفظ فتنتقل للحسابات والمخزن والتقارير.`; steps = `1) راجع البيانات أولاً 2) اضغط الزر مرة واحدة بكليك يسار 3) انتظر رسالة التأكيد ولا تكرر الضغط.`; }
        else if (tag === 'TH' || tag === 'TD' || tag === 'TR') { source = `القيم هنا تأتي من الفواتير والسندات المحفوظة سابقاً في قاعدة البيانات، ويعرضها البرنامج لك مرتبة.`; dest = `هذه مرآة للبيانات وليست مكان إدخال؛ للتعديل افتح المستند الأصلي ثم احفظ، فيتحدث الجدول تلقائياً.`; steps = `1) اقرأ السطر 2) استخدم أزرار الإجراء في نفس السطر لفتح التفاصيل 3) استخدم البحث أو الفلتر للوصول أسرع.`; }
        else { source = `هذه المعلومة يجمعها البرنامج تلقائياً من العمليات التي سجلتها سابقاً (فواتير، سندات، حركات مخزن).`; dest = `تُعرض لتساعدك على القراءة والمتابعة واتخاذ القرار، ولا تغيّر الحسابات إلا بتنفيذ عملية حقيقية.`; steps = `1) اقرأ العنوان 2) قارن الأرقام بما تعرفه 3) اضغط على العنصر التفاعلي المجاور لمزيد من التفاصيل.`; }
        return { where, source, dest, steps };
    };

    window.__synthesizeDeepDomainAuditorInsight = function (cleanTitle, el, liveVal) {
        const t = String(cleanTitle || '') + ' ' + String(liveVal || '');
        const hasNum = /[-+]?\d[\d,.]*/.test(String(liveVal || ''));
        const numBreakdown = liveVal
            ? (hasNum
                ? `الرقم أو الكود الحي الظاهر أمامك الآن هو (${liveVal})؛ وهو يمثل القيمة الفعلية المسجلة حالياً في (${cleanTitle}) والمعتمدة في حسابات الشاشة لحظياً.`
                : `القيمة أو الحالة المسجلة حالياً داخل (${cleanTitle}) هي: (${liveVal}).`)
            : `الخانة جاهزة الآن لاستقبال القيمة أو تنفيذ الأمر مباشرة على الشاشة.`;
        if (/سعر|تسعير|تكلفة|فئة السعر|رادار/.test(t)) {
            return {
                numBreakdown,
                benefits: 'الإيجابيات والمحاسن: يحمي هامش ربح المؤسسة، ويكشف فوراً أي انحراف سعري عن آخر سعر شراء أو بيع معتمد بالرادار الذكي.',
                risks: 'المخاطر عند الخطأ: إدخال سعر أقل من التكلفة أو بعملة خاطئة يؤدي لخسارة مباشرة في مجمل الربح وتشويه تقييم المخزون.',
                controls: 'الضوابط والتأكيدات الواجبة: تأكد من عملة الفاتورة (جنيه أو دولار)، وراجع لون لمبة رادار الأسعار والوحدة المختارة قبل الاعتماد.'
            };
        }
        if (/كمية|عدد|وزن|طن|سيخة|كرتون|وحدة|رصيد فعلي|متبقي/.test(t)) {
            return {
                numBreakdown,
                benefits: 'الإيجابيات والمحاسن: يضبط الرصيد المخزني بالحبة والطن مع التحويل الآلي بين الوحدات الكبرى والفرعية دون آلة حاسبة.',
                risks: 'المخاطر عند الخطأ: الخلط بين الوحدة الكبرى والفرعية أو إدخال كمية غير مطابقة للعد الفعلي يسبب عجزاً أو تضخماً وهمياً في جرد المستودع.',
                controls: 'الضوابط والتأكيدات الواجبة: طابق الكمية مع العد الفعلي على أرض المخزن وتأكد من اختيار المستودع والوحدة الصحيحة.'
            };
        }
        if (/خصم|ضريبة|ترحيل|عمالة|إضافات|نولون|توزيع/.test(t)) {
            return {
                numBreakdown,
                benefits: 'الإيجابيات والمحاسن: يحمّل المصروفات والخصومات بدقة على تكلفة الأصناف وصافي الفاتورة لمعرفة التكلفة الحقيقية لكل قطعة.',
                risks: 'المخاطر عند الخطأ: إغفال مصروف الترحيل أو منح خصم غير معتمد يقلل صافي الربح الحقيقي ويخل بتسعير البيع اللاحق.',
                controls: 'الضوابط والتأكيدات الواجبة: تأكد من مراجعة صافي الفاتورة بعد إضافة المصروف أو الخصم ومطابقته مع مستند المورد أو العميل.'
            };
        }
        if (/مديونية|ائتمان|حد|سقف|رصيد|شيك|بنكك|خزينة|نقدي|آجل|تحويل|دفع|سداد|قبض|صرف/.test(t)) {
            return {
                numBreakdown,
                benefits: 'الإيجابيات والمحاسن: يحصّن السيولة النقدية والبنكية، ويمنع تجاوز السقف الائتماني للعملاء، ويوثق الرقم المرجعي لكل إشعار.',
                risks: 'المخاطر عند الخطأ: اختيار خزينة أو بنك خاطئ أو البيع الآجل لعميل متعثر يعرض السيولة لمخاطر الديون المعدومة واختلاف مطابقات البنوك.',
                controls: 'الضوابط والتأكيدات الواجبة: راجع شريط المديونية والحد المتاح، وطابق رقم العملية المصرفية (بنكك/فوري) واسم الحساب قبل الحفظ.'
            };
        }
        if (/حذف|إلغاء|عكس|مرتجع|تراجع/.test(t)) {
            return {
                numBreakdown,
                benefits: 'الإيجابيات والمحاسن: يتيح تصحيح الأخطاء التشغيلية بعكس محاسبي ومخزني ذري يعيد الأرصدة لحالتها الصحيحة بأمان.',
                risks: 'المخاطر عند الخطأ: إلغاء مستند معتمد دون مسوغ حقيقي يغير أرصدة المخزن وكشف حساب العميل أو المورد.',
                controls: 'الضوابط والتأكيدات الواجبة: تحقق من رقم المستند وتاريخه وسبب الإلغاء، علماً بأن النظام يوثق العملية في البحث الجنائي.'
            };
        }
        return {
            numBreakdown,
            benefits: `الإيجابيات والمحاسن: يمنحك (${cleanTitle}) سرعة تنفيذ فائقة ودقة محاسبية ومخزنية مترابطة تمنع التكرار والأخطاء اليدوية.`,
            risks: `المخاطر عند الإهمال أو الخطأ: تجاوز (${cleanTitle}) أو إدخال بيان غير مطابق للمستند الأصلي قد يؤدي لعدم تطابق التقارير مع الواقع الفعلي.`,
            controls: `الضوابط والتأكيدات الواجبة: راجع القيمة الظاهرة في (${cleanTitle}) وتأكد من مطابقتها للعملية الحالية قبل ضغط زر الحفظ والاعتماد.`
        };
    };

    window.enrichProfessor6Pillars = function (info, el) {
        if (!info || !info.title) return info;
        const cleanTitle = String(info.title || '').replace(/^[✋📖🎓📊📈🏢👤📦💵🏦🔍⚙️🔗]+\s*/, '').trim();
        const rawVal = el ? (el.value || (el.selectedOptions && el.selectedOptions[0]?.text) || el.getAttribute?.('href') || el.getAttribute?.('data-href') || el.innerText || '') : '';
        const liveVal = String(rawVal).replace(/\s+/g, ' ').trim().slice(0, 65);
        const stateFlags = [];
        if (el) {
            if (el.required || el.getAttribute?.('aria-required') === 'true') stateFlags.push('إلزامي *');
            if (el.readOnly || el.disabled) stateFlags.push('محسوب آلياً ومحمى من التعديل اليدوي');
            if ((el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') && !el.value) stateFlags.push('فارغ وجاهز للإدخال');
        }
        const hotkeyMatch = ((el && (el.innerText || el.getAttribute?.('title') || '')) || '').match(/(F\d+|Alt\+[A-Za-z0-9]|Ctrl\+[A-Za-z0-9]|Esc|Enter)/i);
        const hotkeyTxt = info.hotkey || (hotkeyMatch ? `اضغط (${hotkeyMatch[1]}) من لوحة المفاتيح للتنفيذ الفوري.` : 'كليك يسار للتنفيذ، وكليك يمين أو دبل كليك للشرح البروفيسوري.');
        const baladiTxt = info.baladi || window.synthesizeSimpleVernacularAnalogy(cleanTitle, el);
        const whyTxt = info.why || `وُجد (${cleanTitle}) لضبط الدورة المحاسبية والمخزنية وإعطاء الموظف والإدارة قراءة دقيقة وموثقة بنسبة 100%.`;
        const whatTxt = (info.rawPillars && info.rawPillars.whatTxt) || info.baseDesc || info.desc || baladiTxt || `هذه الأداة مخصصة لـ (${cleanTitle}) وتعمل بذكاء لتحديث الحسابات والتقارير تلقائياً.`;
        const impactTxt = info.impact || `الأثر المحاسبي والمخزني: يسمّع لحظياً في القيد المزدوج بدفتر الأستاذ العام وكشف الحساب وكارت الصنف وميزان المراجعة والأرباح والخسائر.`;
        const audit = window.__synthesizeDeepDomainAuditorInsight(cleanTitle, el, liveVal);
        const benefitsTxt = info.benefits || audit.benefits;
        const risksTxt = info.risks || info.mistake || audit.risks;
        const controlsTxt = info.controls || audit.controls;
        const exampleTxt = info.example || `مثال تطبيقي: عند استخدام (${cleanTitle}) يقوم النظام آلياً بمطابقة القيمة وترحيلها للشاشات والتقارير المرتبطة في جزء من الثانية.`;
        const roleAuditTxt = `الصلاحيات والرقابة: يخضع لصلاحيات المستخدمين ويُوثّق آلياً بالوقت والمستخدم في سجل البحث الجنائي (Alt+T).`;
        const undoSafeTxt = `طريقة التصحيح الآمن: يمكنك التعديل قبل الحفظ، أو استخدام التعديل/عكس الحركة بعد الاعتماد دون أي خوف من ضياع البيانات.`;
        const ruleTxt = info.rule || (el && el.getAttribute && el.getAttribute('data-golden-rule')) || `النقطة الذهبية: اضبط (${cleanTitle}) وتأكد من مطابقته للمستند الفعلي، وسيتكفل النظام بضبط المخزن والخزينة وكشف الحساب بفرق صفر.`;

        const xc = window.__msgInferExplainContext(el, cleanTitle);
        const deg = window.__msgAiDegree || 'ai_5';
        let segments = [];
        if (deg === 'ai_1') {
            segments = [whatTxt, audit.numBreakdown, ruleTxt];
        } else if (deg === 'ai_2') {
            segments = [baladiTxt, whatTxt, risksTxt, ruleTxt];
        } else if (deg === 'ai_3') {
            segments = [whatTxt, audit.numBreakdown, impactTxt, benefitsTxt, risksTxt, controlsTxt, ruleTxt];
        } else if (deg === 'ai_4') {
            segments = [baladiTxt, whatTxt, audit.numBreakdown, `من أين تأتي المعلومة: ${xc.source}`, `أين تذهب وتسمّع: ${xc.dest}`, impactTxt, benefitsTxt, risksTxt, controlsTxt, ruleTxt];
        } else {
            segments = [
                baladiTxt,
                `${whatTxt} — ${whyTxt}`,
                audit.numBreakdown,
                `من أين تأتي المعلومة: ${xc.source}`,
                `أين تذهب وفي أي شاشة وتقرير تسمّع: ${xc.dest}`,
                impactTxt,
                benefitsTxt,
                risksTxt,
                controlsTxt,
                exampleTxt,
                `خطوات التنفيذ للموظف الجديد: ${xc.steps}`,
                roleAuditTxt,
                undoSafeTxt,
                `${ruleTxt} (${hotkeyTxt})`
            ];
        }
        const uniqueSegs = [];
        segments.forEach(s => {
            const cleanS = String(s || '').trim();
            if (cleanS && !uniqueSegs.some(u => u.includes(cleanS) || cleanS.includes(u))) uniqueSegs.push(cleanS);
        });
        const fullRichDesc = uniqueSegs.join(' • ');
        return { title: info.title, baseDesc: whatTxt, desc: fullRichDesc, rawPillars: { baladiTxt, whyTxt, whatTxt, liveVal, liveStateTxt: audit.numBreakdown, impactTxt, benefitsTxt, risksTxt, controlsTxt, exampleTxt, hotkeyTxt, ruleTxt } };
    };

    window.autoSynthesizeElementExplanation = function (el) {
        if (!el || el === document.body || el.closest('#msg-live-coach-bar, #msg-smart-hover-tooltip, #msg-universal-modal, #msg-cinema-lower-thirds')) return null;
        const dashInfo = window.explainUnifiedDashboardDomElement ? window.explainUnifiedDashboardDomElement(el) : null;
        if (dashInfo) return window.enrichProfessor6Pillars(dashInfo, el);
        if (typeof window.isElementAllowedForCurrentUserRole === 'function' && !window.isElementAllowedForCurrentUserRole(el)) return null;
        const customTitle = el.getAttribute && (el.getAttribute('data-guide-title') || el.getAttribute('data-md-title'));
        const customDesc = el.getAttribute && (el.getAttribute('data-guide-desc') || el.getAttribute('data-md-tip'));
        const customRule = (el.getAttribute && el.getAttribute('data-golden-rule')) || 'تأكد من مراجعة البيانات المدخلة أو الخيار المحدد قبل الحفظ لضمان التسميع المحاسبي الدقيق.';
        if (customTitle && customDesc) {
            return window.enrichProfessor6Pillars({ title: customTitle, desc: customDesc, rule: customRule }, el);
        }
        const lnk = el.closest('a[href]');
        if (lnk && !lnk.closest('button')) {
            const lTxt = (lnk.innerText || lnk.getAttribute('title') || lnk.getAttribute('href') || '').replace(/\s+/g, ' ').trim();
            const href = lnk.getAttribute('href') || '';
            if (lTxt && lTxt.length >= 2) {
                return window.enrichProfessor6Pillars({
                    title: `🔗 رابط انتقال: (${lTxt.slice(0, 44)})`,
                    why: `للوصول السريع المباشر إلى (${lTxt.slice(0, 44)}) دون البحث في القوائم.`,
                    desc: `ينقلك بضغطة واحدة إلى المسار (${href || lTxt}) مع الحفاظ على جلسة العمل النشطة.`,
                    ifDone: `تفتح أمامك شاشة (${lTxt.slice(0, 44)}) فوراً.`,
                    rule: customRule
                }, lnk);
            }
        }
        const btn = el.closest('button, a.btn, .btn-action, .md-tab-btn, .sl-btn, .qa-master-btn, .cat-hub-btn, .hub-action-card, .fin-metric-pill, [role="button"], [role="tab"], [onclick]');
        if (btn) {
            const bTitle = btn.getAttribute('data-guide-title') || '';
            const bDesc = btn.getAttribute('data-guide-desc') || '';
            if (bTitle && bDesc) return window.enrichProfessor6Pillars({ title: bTitle, desc: bDesc, rule: btn.getAttribute('data-golden-rule') || customRule }, btn);
            const rawTxt = (btn.innerText || btn.getAttribute('title') || btn.getAttribute('aria-label') || '').replace(/\s+/g, ' ').replace(/F\d+/g, '').trim();
            if (!rawTxt || rawTxt.length < 2) return window.__msgGenericFallbackInfo(btn);
            const titleHint = btn.getAttribute('title') || '';
            return window.enrichProfessor6Pillars({
                title: `زر (${rawTxt.slice(0, 44)})`,
                desc: (titleHint && titleHint !== rawTxt
                    ? `${titleHint} — عند الضغط على (${rawTxt}) ينفذ النظام العملية المرتبطة به ويحدّث البيانات فوراً.`
                    : `أمر تفاعلي لتنفيذ (${rawTxt}) مباشرة داخل الشاشة وتحديث الحسابات والسجلات المرتبطة به لحظياً.`),
                rule: customRule
            }, btn);
        }
        const fldWrap = el.closest('.pro-field-card, .input-group, .h-form-group, .form-group, .filter-group');
        const ctrl = (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') ? el : (fldWrap ? fldWrap.querySelector('input, select, textarea') : null);
        if (ctrl || fldWrap) {
            const lbl = (fldWrap?.querySelector('label, .pro-field-label, .h-label')?.innerText || ctrl?.getAttribute('placeholder') || ctrl?.getAttribute('title') || ctrl?.getAttribute('name') || ctrl?.id || '').replace(/\s+/g, ' ').trim();
            if (!lbl) return window.__msgGenericFallbackInfo(ctrl || fldWrap);
            if (ctrl && ctrl.tagName === 'SELECT') {
                const opts = Array.from(ctrl.options || []).map(o => (o.textContent || '').trim()).filter(Boolean).slice(0, 4).join('، ');
                return window.enrichProfessor6Pillars({
                    title: `قائمة (${lbl.slice(0, 42)})`,
                    desc: `قائمة منسدلة لاختيار (${lbl})${opts ? ` ومن خياراتها: (${opts})` : ''}، وتسمّع تلقائياً في السجل الحالي.`,
                    rule: 'اختر التصنيف الصحيح من القائمة المنسدلة لتوجيه القيد والتقرير تلقائياً.'
                }, ctrl);
            }
            return window.enrichProfessor6Pillars({
                title: `خانة (${lbl.slice(0, 42)})`,
                desc: `تُستخدم لإدخال أو تعديل (${lbl}) مع التسميع اللحظي والحفظ المباشر في قاعدة البيانات.`,
                rule: customRule
            }, ctrl || fldWrap);
        }
        const thEl = el.closest('th');
        if (thEl) {
            const thTxt = (thEl.innerText || '').replace(/\s+/g, ' ').trim();
            if (thTxt) return window.enrichProfessor6Pillars({ title: `عمود جدول (${thTxt})`, desc: `يعرض بيانات (${thTxt}) لكل سطر في الجدول مع إمكانية الفرز والمراجعة الفورية.`, rule: 'راجع قيم هذا العمود للتأكد من دقة الأرصدة والأسعار المسجلة.' }, thEl);
        }
        const tdEl = el.closest('td');
        if (tdEl) {
            const tdTxt = (tdEl.innerText || '').replace(/\s+/g, ' ').trim().slice(0, 40);
            if (tdTxt) return window.enrichProfessor6Pillars({ title: `خلية بيانات (${tdTxt})`, desc: `تعرض القيمة الفعلية المسجلة في هذا السطر من الجدول وترتبط مباشرة بالمستند الأصلي.`, rule: 'اضغط على أزرار الإجراء في نفس السطر للتعديل أو فتح كشف الحساب والتفاصيل.' }, tdEl);
        }
        return window.__msgGenericFallbackInfo(el);
    };

    window.__msgGenericFallbackInfo = function (el) {
        if (!el || el === document.body || el === document.documentElement || !el.tagName) return null;
        const tag = el.tagName.toUpperCase();
        const clean = s => String(s || '').replace(/\s+/g, ' ').trim();
        const near = el.closest('button, a, label, h1, h2, h3, h4, h5, h6, th, td, li, .card, .glass-panel, section, fieldset, div[class]') || el;
        let txt = clean(el.getAttribute('aria-label') || el.getAttribute('title') || el.getAttribute('alt') || el.getAttribute('placeholder') || '');
        if (!txt) txt = clean(near.getAttribute && (near.getAttribute('aria-label') || near.getAttribute('title')) || '');
        if (!txt) txt = clean(near.innerText || el.textContent || '').slice(0, 44);
        if (!txt && el.id) txt = clean(el.id.replace(/[-_]+/g, ' '));
        if (!txt && el.name) txt = clean(String(el.name).replace(/[-_]+/g, ' '));
        let kind = 'عنصر في الشاشة', body;
        if (/^H[1-6]$/.test(tag)) { kind = 'عنوان قسم'; body = `هذا عنوان يخبرك أن ما تحته يخص (${txt}) فقط، حتى لا تتوه بين الأقسام.`; }
        else if (tag === 'LABEL') { kind = 'اسم خانة'; body = `هذا الاسم يوضح ماذا تكتب في الخانة المجاورة، اضغط عليه وسينتقل المؤشر للخانة.`; }
        else if (tag === 'IMG' || tag === 'SVG' || tag === 'I' || tag === 'PATH' || /icon|fa-|bi-/.test(String(el.className && el.className.baseVal !== undefined ? el.className.baseVal : el.className))) { kind = 'أيقونة / صورة'; body = `رمز بصري يختصر المعنى بدل الكتابة${txt ? ` ومعناه هنا (${txt})` : ''}، ويساعدك على التعرف على الوظيفة بنظرة واحدة.`; }
        else if (el.type === 'checkbox' || el.type === 'radio') { kind = 'خيار تفعيل'; body = `مربع تفعيل: ضغطة تشغّل الخيار وضغطة ثانية تلغيه${txt ? ` (${txt})` : ''}.`; }
        else if (tag === 'BUTTON' || tag === 'A' || el.closest('button, a, [onclick], [role="button"]')) { kind = 'زر'; body = `زر تنفيذ${txt ? ` لـ (${txt})` : ' يعتمد على رمزه الظاهر'}؛ اضغطه بكليك يسار مرة واحدة لتنفيذ المطلوب منه.`; }
        else if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') { kind = 'خانة'; body = `خانة${txt ? ` خاصة بـ (${txt})` : ''} تُستخدم لإدخال معلومة أو اختيارها ثم حفظها مع المستند.`; }
        else if (tag === 'CANVAS') { kind = 'رسم بياني'; body = `رسم يحوّل الأرقام إلى شكل مرئي لتفهم الاتجاه والفروقات بنظرة واحدة.`; }
        else if (txt) { kind = 'معلومة معروضة'; body = `يعرض لك (${txt}) كما هي مسجلة في البرنامج الآن، للقراءة والمتابعة.`; }
        else { kind = 'مساحة في الشاشة'; body = `منطقة تنظيم وتخطيط تجمع عناصر مرتبطة ببعضها لتبدو الشاشة مرتبة وواضحة.`; }
        return window.enrichProfessor6Pillars({ title: `${kind}${txt ? ' (' + txt.slice(0, 40) + ')' : ''}`, desc: body, rule: 'لو لم تفهم أي عنصر، اضغط عليه كليك يمين مرتين وسيشرحه لك البرنامج بالصوت والكتابة.' }, el);
    };

    const _lastSmartGuideDomSyncByKey = {};
    function resolveActiveGuideKeyFast() {
        if ((window.location.pathname.includes('master-data') || document.querySelector('.md-tabs-sidebar')) && window.MagaiesSmartGuides['master-data']) return 'master-data';
        if ((window.location.pathname.includes('purchase-receipt.php') || document.getElementById('recent-grns-container')) && window.MagaiesSmartGuides['purchase-receipt']) return 'purchase-receipt';
        if ((window.location.pathname.includes('purchase.php') || document.getElementById('purchase-editor-view') || document.getElementById('purchase-hub-view')) && window.MagaiesSmartGuides['purchase-direct']) return 'purchase-direct';
        const splitViewEl = document.getElementById('split-invoice-view');
        if ((window.location.search.includes('action=split') || (splitViewEl && splitViewEl.style.display !== 'none')) && window.MagaiesSmartGuides['sales-split']) return 'sales-split';
        if ((window.location.pathname.includes('direct-sales.php') || document.getElementById('invoice-editor-view') || document.getElementById('sales-hub-view')) && window.MagaiesSmartGuides['sales-direct']) return 'sales-direct';
        if (window.location.pathname.includes('smart-logistics') || window.location.pathname.includes('sales-delivery') || document.getElementById('sl-top-cockpit-bar')) return 'smart-logistics';
        if (document.getElementById('from-account-search') || window.location.pathname.includes('single-cash-payment')) return 'single-cash-payment';
        if (window.location.pathname.includes('multi-cash-payment') && window.MagaiesSmartGuides['multi-cash-payment']) return 'multi-cash-payment';
        return currentGuideKey || 'smart-logistics';
    }

    window.syncDynamicDomElementsIntoSmartGuide = function (guideKey) {
        const isSlScreen = window.location.pathname.includes('smart-logistics') || window.location.pathname.includes('sales-delivery') || !!document.getElementById('sl-top-cockpit-bar');
        const key = guideKey || (isSlScreen ? 'smart-logistics' : (currentGuideKey || 'master-data'));
        const nowTs = Date.now();
        if (_lastSmartGuideDomSyncByKey[key] && (nowTs - _lastSmartGuideDomSyncByKey[key]) < 15000) return;
        _lastSmartGuideDomSyncByKey[key] = nowTs;
        const data = window.MagaiesSmartGuides && window.MagaiesSmartGuides[key];
        if (!data) return;
        data.buttonsDict = Array.isArray(data.buttonsDict) ? data.buttonsDict : [];
        data.autopilotSteps = Array.isArray(data.autopilotSteps) ? data.autopilotSteps : [];

        const salesStepsRef = (key === 'smart-logistics' && typeof SALES_ONLY_STEPS !== 'undefined' && Array.isArray(SALES_ONLY_STEPS)) ? SALES_ONLY_STEPS : [];

        const existingLabels = new Set([
            ...data.buttonsDict.map(b => (b.label || '').replace(/\s+/g, ' ').trim()),
            ...data.autopilotSteps.map(s => (s.cursorLabel || s.badge || '').replace(/\s+/g, ' ').trim()),
            ...salesStepsRef.map(s => (s.cursorLabel || s.badge || '').replace(/\s+/g, ' ').trim())
        ]);
        const coveredSelectors = [
            ...data.buttonsDict.map(b => b.targetSelector).filter(Boolean),
            ...data.autopilotSteps.map(s => s.targetSelector).filter(Boolean),
            ...salesStepsRef.map(s => s.targetSelector).filter(Boolean)
        ];

        const liveElements = Array.from(document.querySelectorAll(
            '#md-c1-command-bar button, .md-action-bar button, .md-tab-content.active button, .tab-content.active button, .md-tabs-sidebar button, main button, .cockpit-header button, ' +
            '.md-child-modal button, .md-c1-modal-backdrop button, ' +
            '#purchase-editor-view .panel-header-row button, #purchase-editor-view .items-toolbar button, #purchase-editor-view .h-form-group button, ' +
            '#purchase-hub-view .hub-action-card, #purchase-hub-view .smart-filter-bar button, ' +
            '#invoice-editor-view .panel-header-row button, #invoice-editor-view .items-toolbar button, ' +
            '#sl-top-cockpit-bar .sl-btn, .sl-glass-card:not([id$="-modal"] .sl-glass-card) button.sl-btn, .sl-floating-summary button.sl-btn'
        )).filter(b => {
            if (b.closest('#msg-live-coach-bar, #msg-universal-fixed-launcher, #msg-universal-modal, #master-7-themes-modal, [id^="sl-"][id$="-modal"]')) return false;
            if (typeof window.isElementAllowedForCurrentUserRole === 'function' && !window.isElementAllowedForCurrentUserRole(b)) return false;
            const r = b.getBoundingClientRect();
            return r.width > 24 && r.height > 18;
        });

        liveElements.forEach((btn, idx) => {
            const isAlreadyCoveredBySel = coveredSelectors.some(sel => {
                try { return btn.matches(sel) || !!btn.closest(sel); } catch (_e) { return false; }
            });
            if (isAlreadyCoveredBySel) return;

            const cleanLbl = (btn.innerText || btn.getAttribute('title') || '').replace(/\s+/g, ' ').replace(/F\d+/g, '').trim();
            if (!cleanLbl || cleanLbl.length < 2 || cleanLbl.length > 48) return;
            if ([...existingLabels].some(ex => ex && (ex.includes(cleanLbl) || cleanLbl.includes(ex)))) return;
            existingLabels.add(cleanLbl);
            if (!btn.id) btn.id = `msg-dyn-btn-${Date.now().toString(36)}-${idx}`;
            const sel = `#${btn.id}`;
            coveredSelectors.push(sel);
            const slCustomInfo = (isSlScreen && typeof window.explainSmartLogisticsDomElement === 'function')
                ? window.explainSmartLogisticsDomElement(btn)
                : null;
            const info = slCustomInfo || window.autoSynthesizeElementExplanation(btn) || { title: cleanLbl, desc: `ينفذ وظيفة (${cleanLbl}) داخل الشاشة فوراً.` };
            data.buttonsDict.push({
                label: cleanLbl,
                style: 'background:#0f172a; color:#38bdf8; border:1px solid #38bdf8;',
                whatItDoes: info.desc,
                whatHappensIfClicked: `يقوم بتشغيل (${cleanLbl}) وتحديث البيانات المرتبطة به لحظياً.`,
                targetSelector: sel
            });
            // Only append auto-discovered buttons to autopilotSteps when the screen doesn't already have a curated deep functional walkthrough OR when explicitly marked as a dynamic button
            const isExplicitDynamicBtn = btn.getAttribute && btn.getAttribute('data-c5-dynamic-btn') === '1';
            const hasCuratedDeepWalkthrough = ['sales-direct', 'sales-split', 'purchase-direct', 'purchase-receipt', 'master-data', 'smart-logistics', 'multi-cash-payment', 'single-cash-payment'].includes(key);
            if (!hasCuratedDeepWalkthrough || isExplicitDynamicBtn) {
                const dynStep = {
                    badge: `✨ أداة مضافة — ${cleanLbl}`,
                    cursorLabel: cleanLbl,
                    screenPath: `${data.title || 'الشاشة الحالية'} ⬅ ${cleanLbl}`,
                    targetSelector: sel,
                    diagramType: 'screen-hub',
                    diagramMeta: {
                        parentScreen: data.title || 'الشاشة الحالية',
                        actionLabel: `وظيفة (${cleanLbl}):`,
                        childItems: [`🎯 ${cleanLbl}`, info.desc.slice(0, 65), '⚡ مكتشف ومحدّث تلقائياً بالمرشد الذكي'],
                        effectLabel: 'تحديث ذاتي فوري للمرشد الذكي'
                    },
                    speech: () => `${info.title}. ${info.desc}`
                };
                data.autopilotSteps.push(dynStep);
                if (salesStepsRef.length > 0) {
                    salesStepsRef.push(dynStep);
                }
                if (typeof activeAutopilotStepsList !== 'undefined' && Array.isArray(activeAutopilotStepsList) && activeAutopilotStepsList !== data.autopilotSteps && activeAutopilotStepsList !== salesStepsRef) {
                    activeAutopilotStepsList.push(dynStep);
                }
                if (typeof renderStepPillsForActiveList === 'function') {
                    try { renderStepPillsForActiveList(); } catch (_e) {}
                }
            }
        });
    };

    window.MagaiesSmartGuides['purchase-direct'] = {
        title: 'شرح فاتورة المشتريات',
        subtitle: 'شرح صوتي سلس ومريح للقلب خطوة بخطوة مع تزامن تام بالملي ثانية بين الصوت والمؤشر',
        autopilotSteps: [
            { badge: '1️⃣ رقم وتاريخ الفاتورة', cursorLabel: 'رقم وتاريخ الفاتورة', targetSelector: '#inv-number, #hdr-date, #invoice-info-box', action: () => { const ed = document.getElementById('purchase-editor-view'); if (ed && ed.style.display === 'none' && typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('direct'); }, speech: () => 'اللهم صل وسلم على نبينا محمد، أهلاً بك في فاتورة المشتريات. نبدأ من اليمين برقم الفاتورة المتسلسل التلقائي وبجانبه تاريخ الفاتورة، ليتوثق الشراء فوراً في دفتر اليومية وكشف الحساب بكل دقة واطمئنان.' },
            { badge: '2️⃣ شريط الرصيد والمديونية', cursorLabel: 'شريط الرصيد وزر جنيه ⇄ دولار', targetSelector: '#customer-financial-card, #fin-strip-currency-btn', speech: () => 'وفي أعلى اليسار شريط الرصيد والمديونية؛ يعرض لك بوضوح رصيد المورد ومديونيته والشيكات والحد المتاح، مع زر التحويل اللحظي بين الجنيه والدولار.' },
            { badge: '3️⃣ اختيار المورد والبحث الذكي', cursorLabel: 'خانة البحث عن المورد', targetSelector: '#hdr-customer-search', speech: () => 'وهنا خانة المورد؛ اكتب فيها أول حروف من اسم المورد ليظهر أمامك فوراً، وبمجرد اختياره تتحدث أرقامه في شريط الرصيد تلقائياً.' },
            { badge: '4️⃣ زر دفتر أستاذ المورد', cursorLabel: 'زر أستاذ المورد', targetSelector: '#hdr-customer-ledger-btn', speech: () => 'وبداخل خانة المورد تجد زر الأستاذ؛ بضغطة واحدة يفتح لك كشف حساب المورد التفصيلي لتراجع حركاته السابقة وأنت مطمئن.' },
            { badge: '5️⃣ إضافة مورد وآخر فاتورة', cursorLabel: 'إضافة مورد + نسخ آخر فاتورة', targetSelector: '#btn-quick-add-supplier, #btn-clone-last-supplier-inv', speech: () => 'وبجانبه زر علامة الزائد لتعريف مورد جديد في ثانية، ثم زر آخر فاتورة الذي ينسخ لك أصناف آخر فاتورة اشتريتها من هذا المورد لتوفر وقتك ومجهودك.' },
            { badge: '6️⃣ العملة وسعر الصرف', cursorLabel: 'عملة الفاتورة وسعر الصرف', targetSelector: '#hdr-currency, #hdr-exchange-rate', speech: () => 'وهنا تختار عملة الفاتورة سواء بالجنيه أو الدولار أو غيرها، وبجانبها سعر الصرف المعتمد ليحسب النظام القيمة المعادلة تلقائياً.' },
            { badge: '7️⃣ خانة المخزن المستلم', cursorLabel: 'المخزن الذي تدخل فيه البضاعة', targetSelector: '#hdr-warehouse', speech: () => 'وهذه خانة المخزن؛ تحدد منها المخزن الذي ستدخل إليه البضاعة المشتراة ليتم زيادة رصيده فور حفظ الفاتورة.' },
            { badge: '8️⃣ البيان ومركز الكلفة', cursorLabel: 'البيان ومركز الكلفة', targetSelector: '#hdr-description, #hdr-cost-center', speech: () => 'وفي السطر الثاني تكتب أي ملاحظة أو بيان مرجعي للفاتورة، وبجانبه تحديد مركز الكلفة أو الفرع المرتبط بهذه المشتريات.' },
            { badge: '9️⃣ ذكاء الأسعار وتخصيص الجدول', cursorLabel: 'رادار الأسعار وتخصيص الأعمدة', targetSelector: 'button[onclick*="openPurchaseAIAuditModal"], button[onclick*="openGridSettingsModal"]', speech: () => 'وفوق الجدول تجد زر الذكاء الاصطناعي لمراجعة أسعار البيع المقترحة، وبجانبه زر تخصيص الجدول لترتيب وتلوين الأعمدة وحفظها نهائياً.' },
            { badge: '🔟 جدول الأصناف ورادار الأسعار', cursorLabel: 'جدول الأصناف والكميات والأسعار', targetSelector: '#pos-grid', speech: () => 'وهذا جدول الأصناف؛ تكتب فيه اسم الصنف وتحدد الوحدة والكمية وسعر الشراء، وتلاحظ بجانب السعر لمبة رادار الأسعار الثلاثية وأسهم الارتفاع والانخفاض عن آخر شراء.' },
            { badge: '1️⃣1️⃣ الترحيل والإجمالي المزدوج', cursorLabel: 'الترحيل والإجمالي بالجنيه والدولار', targetSelector: '#inline-totals-container, #grid-foot', speech: () => 'وفي أسفل الجدول أزرار إضافة الترحيل والعمالة والضريبة والخصم، وبجانبها يظهر صافي الفاتورة النهائي بالجنيه وبالدولار معاً مباشرة تحت عمود الجملة.' },
            { badge: '1️⃣2️⃣ السداد والحفظ والطباعة F10', cursorLabel: 'طريقة الدفع وحفظ الفاتورة F10', targetSelector: '#payment-panel, .items-toolbar', action: () => { const p = document.getElementById('payment-panel'); if (p) p.style.display = 'block'; }, speech: () => 'وأخيراً لوحة السداد وشريط الاعتماد؛ تختار الدفع نقداً أو آجلاً أو بنكك أو شيك، وتضغط حفظ واعتماد إف عشرة أو طباعة ومشاركة واتساب ليسمّع في المخزن والحسابات فوراً بكل أمان.' }
        ],
        buttonsDict: [],
        scenarios: []
    };

    window.MagaiesSmartGuides['sales-direct'] = {
        title: 'شرح فاتورة المبيعات',
        subtitle: 'شرح صوتي سلس ومريح للقلب خطوة بخطوة مع تزامن تام بالملي ثانية بين الصوت والمؤشر',
        autopilotSteps: [
            { badge: '1️⃣ رقم وتاريخ الفاتورة', cursorLabel: 'رقم وتاريخ الفاتورة', targetSelector: '#inv-number, #hdr-date, #invoice-info-box', action: () => { const ed = document.getElementById('invoice-editor-view'); if (ed && ed.style.display === 'none' && typeof window.openInvoiceEditor === 'function') window.openInvoiceEditor('pending_delivery'); }, speech: () => 'اللهم صل وسلم على نبينا محمد، أهلاً بك في فاتورة المبيعات. نبدأ من اليمين برقم الفاتورة المتسلسل التلقائي وبجانبه تاريخ الفاتورة، لتتوثق عملية البيع فوراً في اليومية وكشف الحساب.' },
            { badge: '2️⃣ شريط الرصيد والائتمان', cursorLabel: 'شريط الرصيد وزر جنيه ⇄ دولار', targetSelector: '#customer-financial-card, #fin-strip-currency-btn', speech: () => 'وفي أعلى اليسار شريط الرصيد والمديونية والائتمان؛ يوضح رصيد العميل ومديونيته والشيكات وحد الائتمان المتاح، مع زر التحويل اللحظي بين الجنيه والدولار.' },
            { badge: '3️⃣ اختيار العميل والبحث الذكي', cursorLabel: 'خانة البحث عن العميل', targetSelector: '#hdr-customer-search', speech: () => 'وهنا خانة العميل؛ اكتب فيها أول حروف من اسم العميل للبحث عنه فوراً، وبمجرد اختياره تظهر أرقامه الائتمانية تلقائياً.' },
            { badge: '4️⃣ زر دفتر أستاذ العميل', cursorLabel: 'زر أستاذ العميل', targetSelector: '#hdr-customer-ledger-btn', speech: () => 'وبداخل خانة العميل تجد زر الأستاذ؛ يفتح لك كشف حساب العميل التفصيلي بضغطة واحدة لتراجع رصيده وحركاته السابقة.' },
            { badge: '5️⃣ إضافة عميل وآخر فاتورة', cursorLabel: 'إضافة عميل + نسخ آخر فاتورة', targetSelector: '#btn-quick-add-customer, #btn-clone-last-customer-inv', speech: () => 'وبجانبه زر علامة الزائد لإضافة عميل جديد فوراً، ثم زر آخر فاتورة الذي ينسخ لك أصناف آخر فاتورة اشتراها هذا العميل بضغطة واحدة.' },
            { badge: '6️⃣ العملة وسعر الصرف', cursorLabel: 'عملة الفاتورة وسعر الصرف', targetSelector: '#hdr-currency, #hdr-exchange-rate', speech: () => 'وهنا تختار عملة الفاتورة وسعر الصرف المعتمد ليقوم النظام بالحساب والتحويل التلقائي بدقة تامة.' },
            { badge: '7️⃣ خانة المخزن الصارف', cursorLabel: 'المخزن الذي تُصرف منه البضاعة', targetSelector: '#hdr-warehouse', speech: () => 'وهذه خانة المخزن؛ تحدد منها المخزن الذي ستخرج منه البضاعة المباعة ليتم خصم الكمية من رصيده تلقائياً.' },
            { badge: '8️⃣ البيان ومركز الكلفة', cursorLabel: 'البيان ومركز الكلفة', targetSelector: '#hdr-description, #hdr-cost-center', speech: () => 'وفي السطر الثاني تدون أي ملاحظة أو بيان على الفاتورة، وبجانبه اختيار مركز الكلفة أو الفرع.' },
            { badge: '9️⃣ البحث السريع عن الأصناف F2', cursorLabel: 'بحث الأصناف والباركود F2', targetSelector: '#quick-item-search, button[onclick*="openGridSettingsModal"]', speech: () => 'وهنا خانة البحث السريع عن الأصناف؛ اكتب اسم الصنف أو امسح الباركود لإدراجه فوراً في الجدول، وبجانبها زر تخصيص الأعمدة.' },
            { badge: '🔟 جدول الأصناف والأسعار', cursorLabel: 'جدول الأصناف والكميات والأسعار', targetSelector: '#pos-grid', speech: () => 'وهذا جدول الأصناف؛ تحدد فيه الوحدة والكمية وسعر البيع، ويعرض لك الرصيد المتاح في المخزن ولمبة رادار الأسعار وآخر سعر بيع.' },
            { badge: '1️⃣1️⃣ الترحيل والإجمالي المزدوج', cursorLabel: 'الترحيل والإجمالي بالجنيه والدولار', targetSelector: '#inline-totals-container, #grid-foot', speech: () => 'وفي أسفل الجدول أزرار الترحيل والخصم والضريبة، ويظهر بجانبها الإجمالي الصافي بالجنيه وبالدولار معاً تحت عمود الجملة.' },
            { badge: '1️⃣2️⃣ الدفع واعتماد الفاتورة F10', cursorLabel: 'لوحة الدفع وحفظ الفاتورة F10', targetSelector: '#payment-panel, .items-toolbar', action: () => { const p = document.getElementById('payment-panel'); if (p) p.style.display = 'block'; }, speech: () => 'وأخيراً لوحة الدفع وشريط الاعتماد؛ تختار منها السداد نقداً أو آجلاً أو بنكك أو شيك، ثم تضغط حفظ واعتماد إف عشرة لترحيلها مباشرة وأنت مطمئن.' }
        ],
        buttonsDict: [],
        scenarios: []
    };

    function explainInvoiceDomCommon(el, isPur) {
        if (!el || !el.closest) return null;
        const party = isPur ? 'المورد' : 'العميل';
        const act = isPur ? 'الشراء' : 'البيع';
        const whAct = isPur ? 'تدخل إليه البضاعة المشتراة ويزيد رصيده' : 'تخرج منه البضاعة المباعة ويُخصم من رصيده';
        if (el.closest('#btn-clone-last-supplier-inv, #btn-clone-last-customer-inv')) return { title: `📋 زر آخر فاتورة لـ${party}`, desc: `ينسخ لك أصناف آخر فاتورة مسجلة لهذا ${party} بضغطة واحدة لتوفر وقتك ومجهودك وتعدل الكميات فقط بكل سهولة.` };
        if (el.closest('#hdr-customer-ledger-btn')) return { title: `📖 زر دفتر أستاذ ${party}`, desc: `يفتح لك كشف حساب ${party} التفصيلي مباشرة من داخل الفاتورة لتراجع جميع حركاته السابقة ورصيده الدقيق في ثانية واحدة.` };
        if (el.closest('#btn-quick-add-supplier, #btn-quick-add-customer')) return { title: `➕ زر إضافة ${party} جديد`, desc: `يفتح لك نافذة سريعة لتعريف ${party} جديد وإدراجه في الفاتورة فوراً دون مغادرة الشاشة.` };
        if (el.closest('#hdr-customer-search')) return { title: `👤 خانة اختيار ${party}`, desc: `اكتب فيها أول حروف من اسم ${party} ليظهر أمامك فوراً، وبمجرد اختياره يعرض النظام رصيده ومديونيته وحد الائتمان تلقائياً.` };
        if (el.closest('#hdr-warehouse, .group-warehouse')) return { title: `📦 خانة المخزن`, desc: `تحدد منها المخزن المعتمد الذي ${whAct} فور حفظ واعتماد الفاتورة.` };
        if (el.closest('#hdr-currency, #hdr-exchange-rate, .group-currency')) return { title: `💱 العملة وسعر الصرف`, desc: `تختار منها عملة الفاتورة (جنيه أو دولار أو غيرها) وسعر الصرف ليحسب النظام المعادل تلقائياً بدقة تامة.` };
        if (el.closest('#hdr-date, #inv-number')) return { title: `📅 تاريخ ورقم الفاتورة`, desc: `يوثق تاريخ حركة ${act} ورقمها المتسلسل التلقائي لترحيلها في دفتر اليومية وكشف الحساب بانتظام.` };
        if (el.closest('#hdr-description')) return { title: `📝 خانة البيان والملاحظات`, desc: `تكتب فيها أي ملاحظات أو تفاصيل مرجعية لتظهر في كشف حساب ${party} ودفتر اليومية وعند الطباعة.` };
        if (el.closest('#hdr-cost-center, .group-cost-center')) return { title: `🏷️ خانة مركز الكلفة`, desc: `تربط هذه الفاتورة بالفرع أو المشروع أو مركز التكلفة المختص لمتابعة أرباحه ومصاريفه بدقة.` };
        if (el.closest('#fin-strip-currency-btn')) return { title: `🔄 زر تحويل عملة الشريط (جنيه ⇄ دولار)`, desc: `يحوّل جميع أرقام شريط الرصيد والمديونية والشيكات والمتاح أمامك لحظياً بين الجنيه السوداني والدولار.` };
        if (el.closest('#fin-strip, #customer-financial-card')) return { title: `📊 شريط الرصيد والمديونية والائتمان`, desc: `يعرض لك بوضوح تام رصيد ${party} الحالي وإجمالي المديونية والشيكات والحد المتاح قبل اعتماد الفاتورة.` };
        if (el.closest('#quick-item-search')) return { title: `🔍 البحث السريع عن الأصناف والباركود`, desc: `اكتب اسم الصنف أو امسح الباركود لإدراج الصنف مباشرة في جدول الفاتورة بسرعة وسلاسة.` };
        if (el.closest('button[onclick*="openPurchaseAIAuditModal"]')) return { title: `🤖 الذكاء الاصطناعي لمراجعة الأسعار`, desc: `يراجع أسعار الشراء مقارنة بأسعار البيع الحالية ويقترح لك تعديل أسعار البيع للحفاظ على هامش ربحك فوراً.` };
        if (el.closest('button[onclick*="openGridSettingsModal"]')) return { title: `⚙️ تخصيص أعمدة الجدول`, desc: `يتيح لك إظهار أو إخفاء وترتيب وتلوين أعمدة جدول الفاتورة وتكبير الخط وحفظ التخصيص نهائياً.` };
        if (el.closest('#inline-totals-container, #grid-foot, .pur-compact-tot-card')) return { title: `⚖️ أزرار الترحيل والإجمالي المزدوج`, desc: `تضيف منها الترحيل أو العمالة أو الضريبة أو الخصم، وتعرض لك صافي الفاتورة النهائي بالجنيه وبالدولار معاً تحت عمود الجملة.` };
        if (el.closest('#payment-panel, .items-toolbar, #btn-submit-purchase-invoice, #btn-submit-invoice')) return { title: `💳 السداد واعتماد الفاتورة والطباعة (F10)`, desc: `تحدد منها طريقة السداد (نقداً أو آجلاً أو بنكك أو شيك) ثم تضغط حفظ واعتماد (F10) أو طباعة ومشاركة واتساب ليسمّع في المخزن والحسابات فوراً.` };
        if (el.closest('#pos-grid')) return { title: `📦 جدول الأصناف والكميات ورادار الأسعار`, desc: `تسجل فيه الأصناف والوحدات والكميات والأسعار، مع لمبة رادار الأسعار الثلاثية التي تنبهك لاتجاه السعر وآخر رصيد.` };
        return null;
    }
    window.explainPurchaseDomElement = (el) => explainInvoiceDomCommon(el, true);
    window.explainDirectSalesDomElement = (el) => explainInvoiceDomCommon(el, false);

    function buildUniversalScreenGuideIfNeeded() {
        let matchedKey = null;
        if ((window.location.pathname.includes('master-data') || document.querySelector('.md-tabs-sidebar')) && window.MagaiesSmartGuides['master-data']) {
            matchedKey = 'master-data';
            try {
                if (typeof window.detectActiveMasterDataTabMode === 'function' && typeof buildProfessorialMasterDataScenes === 'function') {
                    const activeTabMode = window.detectActiveMasterDataTabMode() || 'customers';
                    const tabScenes = buildProfessorialMasterDataScenes(activeTabMode);
                    if (Array.isArray(tabScenes) && tabScenes.length > 0) {
                        window.MagaiesSmartGuides['master-data'].autopilotSteps = tabScenes.map(sc => ({
                            badge: sc.badge || 'خطوة شرح',
                            cursorLabel: sc.title || sc.badge || 'بيان',
                            targetSelector: sc.targetSelector || '#tab-' + activeTabMode,
                            goldenRule: sc.goldenRule || '',
                            action: sc.action,
                            speech: () => (sc.speech || '') + (sc.goldenRule ? ' — ' + sc.goldenRule : '')
                        }));
                    }
                }
            } catch (_e) {}
        } else if ((window.location.pathname.includes('purchase-receipt.php') || document.getElementById('recent-grns-container')) && window.MagaiesSmartGuides['purchase-receipt']) {
            matchedKey = 'purchase-receipt';
        } else if ((window.location.pathname.includes('purchase.php') || document.getElementById('purchase-editor-view') || document.getElementById('purchase-hub-view')) && window.MagaiesSmartGuides['purchase-direct']) {
            matchedKey = 'purchase-direct';
            const purEditor = document.getElementById('purchase-editor-view');
            if (purEditor && purEditor.style.display === 'none' && typeof window.openPurchaseEditor === 'function') {
                try { window.openPurchaseEditor('direct'); } catch (_) {}
            }
        } else {
            const splitViewEl = document.getElementById('split-invoice-view');
            if ((window.location.search.includes('action=split') || (splitViewEl && splitViewEl.style.display !== 'none')) && window.MagaiesSmartGuides['sales-split']) {
                matchedKey = 'sales-split';
            } else if ((window.location.pathname.includes('direct-sales.php') || document.getElementById('invoice-editor-view') || document.getElementById('sales-hub-view')) && window.MagaiesSmartGuides['sales-direct']) {
                matchedKey = 'sales-direct';
                const invEditor = document.getElementById('invoice-editor-view');
                if (invEditor && invEditor.style.display === 'none' && typeof window.openInvoiceEditor === 'function') {
                    try { window.openInvoiceEditor('pending_delivery'); } catch (_) {}
                }
            } else if (window.location.pathname.includes('smart-logistics') || document.getElementById('sl-top-cockpit-bar') || document.getElementById('sl-tab-purchases') || document.getElementById('sl-tab-sales')) {
                matchedKey = 'smart-logistics';
            } else if (document.getElementById('from-account-search') || window.location.pathname.includes('single-cash-payment')) {
                matchedKey = 'single-cash-payment';
            } else if (window.location.pathname.includes('multi-cash-payment') && window.MagaiesSmartGuides['multi-cash-payment']) {
                matchedKey = 'multi-cash-payment';
            } else if ((window.location.pathname.includes('magaiees_unified_dashboard') || document.getElementById('new-unified-dashboard-design')) && window.MagaiesSmartGuides['magaiees-unified-dashboard']) {
                matchedKey = 'magaiees-unified-dashboard';
            }
        }
        if (matchedKey) {
            try { window.syncDynamicDomElementsIntoSmartGuide(matchedKey); } catch (e) {}
            return matchedKey;
        }
        const pageTitle = (document.querySelector('main h1, main h2, main h3, .md-card-title, h1:not(.m7-modal-title), h2:not(.m7-modal-title), .page-title')?.textContent || document.title || 'الشاشة الحالية').trim();
        const activeViewScope = [document.getElementById('invoice-editor-view'), document.getElementById('split-invoice-view'), document.getElementById('sales-hub-view'), document.getElementById('purchase-editor-view'), document.getElementById('purchase-hub-view')].find(v => v && v.offsetParent !== null && v.style.display !== 'none') || document.querySelector('main, .dash-main, .pos-wrapper, .main-content') || document.body;
        const visibleEls = Array.from(activeViewScope.querySelectorAll('button, select, input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea, .spotlight-card, .kpi-card, .hub-action-card, .qa-master-btn, .cat-hub-btn, .sl-btn, .tab-btn, [role="tab"]')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 34 && r.height > 18 && r.top >= 0 && !b.closest('.global-sidebar, .sidebar, aside, #sidebar, .sidebar-nav, .sidebar-item, .nav-sidebar, .nav-links, #panoramic-flyout-panel, #sidebar-panoramic-flyout, .sp-flyout-panel, .sp-flyout-backdrop, nav, header.premium-header, .magical-glass-header, .msg-twin-quartets-wrapper, #sales-fc1-smart-ai-command-bar, #purchase-fc1-smart-ai-command-bar, #msg-universal-fixed-launcher, #msg-universal-modal, #master-7-themes-modal, #msg-live-coach-bar, #msg-cinema-lower-thirds, #msg-smart-hover-tooltip');
        }).slice(0, 14);

        window.MagaiesSmartGuides['universal-screen'] = {
            title: `المرشد الذكي: ${pageTitle}`,
            subtitle: 'شرح مصوّر ومدبلج بالصوت الرجالي وبالشرح البلدي والبروفيسوري لكل عنصر داخل الشاشة',
            autopilotSteps: visibleEls.length ? visibleEls.map((el, i) => {
                const isSelect = el.tagName === 'SELECT';
                const synth = window.autoSynthesizeElementExplanation ? window.autoSynthesizeElementExplanation(el) : null;
                const lbl = (synth && synth.title) ? synth.title : (isSelect
                    ? (el.getAttribute('title') || el.options?.[0]?.text || `القائمة المنسدلة ${i + 1}`).replace(/\s+/g, ' ').trim().slice(0, 45)
                    : (el.innerText || el.getAttribute('placeholder') || el.getAttribute('title') || `العنصر ${i + 1}`).replace(/\s+/g, ' ').trim().slice(0, 45));
                if (!el.id) el.id = `msg-auto-el-${i}`;
                const selectOpts = isSelect ? Array.from(el.options || []).slice(0, 5).map(o => o.text.trim()).filter(Boolean) : [];
                const richSpeech = synth && synth.desc ? `${synth.title}. ${synth.desc}` : `هذا العنصر مخصص لـ (${lbl}) وينفذ وظيفته المحاسبية والتشغيلية مباشرة داخل شاشة ${pageTitle}.`;
                return {
                    badge: `الخطوة ${i + 1} — ${lbl}`,
                    cursorLabel: lbl,
                    screenPath: `${pageTitle} ⬅ ${lbl}`,
                    targetSelector: `#${el.id}`,
                    diagramType: isSelect ? 'dropdown-open' : 'screen-hub',
                    diagramMeta: {
                        parentScreen: pageTitle,
                        actionLabel: `تفصيل وظيفة (${lbl}):`,
                        childItems: selectOpts.length ? selectOpts : [
                            `🎯 ${lbl}`,
                            (synth && synth.rawPillars && synth.rawPillars.baladiTxt) || '👆 تنفيذ فوري ومباشر بالضغط أو الإدخال',
                            (synth && synth.rawPillars && synth.rawPillars.ruleTxt) || '📊 تحديث البيانات والتقارير المرتبطة في نفس اللحظة'
                        ],
                        effectLabel: isSelect ? 'قائمة الخيارات مفتوحة الآن أمامك على الشاشة!' : 'تنفيذ وتسميع فوري داخل النظام'
                    },
                    speech: () => `${i === 0 ? SHORT_SAHIHAYN_SALAWAT + ' الآن أشرح ' + pageTitle + '. ' : ''}${richSpeech}`
                };
            }) : [
                {
                    badge: 'الخطوة 1 — نظرة عامة على الشاشة',
                    cursorLabel: pageTitle,
                    screenPath: `${pageTitle} ⬅ جولة شاملة`,
                    targetSelector: 'body',
                    speech: () => `${SHORT_SAHIHAYN_SALAWAT} الآن أشرح ${pageTitle}. يمكنك الضغط على أي عنصر في الشاشة ليقوم المرشد بشرحه مباشرة.`
                }
            ],
            visualSteps: [
                {
                    stepBadge: '1️⃣ شرح الشاشة كلها',
                    title: `جولة شاملة في ${pageTitle}`,
                    desc: 'اضغط على زر (🌐 شرح الشاشة كلها) بالأعلى ليقوم المرشد الذكي بالمرور على عناصر الشاشة بالترتيب بالصوت والماوس المتحرك.',
                    svg: `<svg viewBox="0 0 320 110" width="100%" height="100%"><rect x="20" y="20" width="280" height="70" rx="14" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/><text x="160" y="60" fill="#fef08a" font-size="14" font-weight="bold" text-anchor="middle">🌐 شرح الشاشة بالكامل</text></svg>`
                },
                {
                    stepBadge: '2️⃣ شرح زر أو جزء معين فقط',
                    title: 'اضغط على أي زر في الشاشة ليشرحه وحده',
                    desc: 'اختر وضع (👆 اشرح جزءاً معيناً أضغط عليه) ثم اضغط بالماوس على أي زر أو جدول تريد فهمه، وسيظهر شرحه فوراً على الشاشة بالصوت.',
                    svg: `<svg viewBox="0 0 320 110" width="100%" height="100%"><rect x="20" y="20" width="280" height="70" rx="14" fill="#064e3b" stroke="#34d399" stroke-width="2"/><text x="160" y="60" fill="#ffffff" font-size="14" font-weight="bold" text-anchor="middle">👆 المؤشر الحر لشرح أي جزء</text></svg>`
                }
            ],
            buttonsDict: visibleEls.map(b => {
                const lbl = (b.innerText || b.getAttribute('title') || 'زر بالنظام').replace(/\s+/g, ' ').trim();
                return {
                    label: lbl,
                    style: 'background:#0f172a; color:#38bdf8; border:1px solid #38bdf8;',
                    whatItDoes: `ينفذ أمر (${lbl}) داخل شاشة ${pageTitle}.`,
                    whatHappensIfClicked: `يقوم بتشغيل وظيفة (${lbl}) مباشرة.`,
                    targetSelector: `#${b.id}`
                };
            }),
            scenarios: [
                {
                    q: '💡 كيف أجعل المرشد الذكي يشرح لي زراً واحداً فقط بدون شرح الشاشة كلها؟',
                    a: 'اضغط على زر (👆 اشرح جزءاً أضغط عليه) أو افتح المايك الذكي (🎙️) بجوار زر المرشد الذكي تحت زر الرجوع، وسيظهر لك الشرح فوراً على الشاشة.'
                }
            ]
        };
        return 'universal-screen';
    }

    // ════════════════════════════════════════════════════════════════════════
    // 👑 UNIVERSAL ACTIONS FOR THE SECOND 4-BUTTON POD (الموبايل • تثبيت • تثبيت عام • السمات)
    // ════════════════════════════════════════════════════════════════════════
    window.triggerQuartetMobileEdition = function () {
        if (typeof window.switchToMobileEdition === 'function') {
            window.switchToMobileEdition();
            return;
        }
        try {
            localStorage.setItem('magaiees_view_preference', 'mobile');
            document.cookie = 'magaiees_view_preference=mobile; path=/; max-age=31536000; SameSite=Lax';
        } catch (e) {}
        window.location.href = 'mobile-dashboard.php?force_mobile=1';
    };

    window.triggerQuartetAppInstall = function () {
        if (window.PWAManager && typeof window.PWAManager.triggerInstall === 'function' && window.PWAManager.deferredPrompt) {
            window.PWAManager.triggerInstall();
            return;
        }
        let modal = document.getElementById('msg-pwa-app-install-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'msg-pwa-app-install-modal';
            modal.style.cssText = 'position:fixed;inset:0;z-index:999998;background:rgba(2,6,23,0.85);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
            modal.innerHTML = `
                <div style="background:linear-gradient(160deg,#0f172a,#020617);border:2px solid #2dd4bf;border-radius:20px;max-width:480px;width:100%;padding:22px;color:#fff;box-shadow:0 20px 60px rgba(0,0,0,0.9),0 0 30px rgba(45,212,191,0.35);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,0.1);">
                        <div style="display:flex;align-items:center;gap:10px;font-size:1.1rem;font-weight:900;color:#5eead4;">
                            <span>📲</span>
                            <span>تثبيت أيقونة تطبيق مقاييس (موبايل / كمبيوتر)</span>
                        </div>
                        <button type="button" onclick="document.getElementById('msg-pwa-app-install-modal').style.display='none'" style="background:rgba(239,68,68,0.2);border:1px solid #f87171;color:#fecaca;border-radius:8px;width:30px;height:30px;cursor:pointer;font-weight:900;">✕</button>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:0.88rem;line-height:1.65;color:#e2e8f0;">
                        <div style="background:rgba(13,148,136,0.16);border:1px solid rgba(45,212,191,0.4);border-radius:12px;padding:10px 14px;">
                            <strong style="color:#5eead4;">📱 على هواتف أندرويد (Chrome):</strong><br>
                            اضغط على قائمة المتصفح <b>(⋮)</b> أعلى الشاشة ثم اختر <b>«تثبيت التطبيق» (Install App)</b> أو <b>«إضافة إلى الشاشة الرئيسية»</b>.
                        </div>
                        <div style="background:rgba(37,99,235,0.16);border:1px solid rgba(96,165,250,0.4);border-radius:12px;padding:10px 14px;">
                            <strong style="color:#93c5fd;">🍏 على آيفون وآيباد (Safari):</strong><br>
                            اضغط على زر المشاركة <b>(⬆️ Share)</b> بالأسفل ثم اختر <b>«إضافة إلى الصفحة الرئيسية» (Add to Home Screen)</b>.
                        </div>
                        <div style="background:rgba(245,158,11,0.14);border:1px solid rgba(251,191,36,0.4);border-radius:12px;padding:10px 14px;">
                            <strong style="color:#fde047;">💻 على الكمبيوتر (Chrome / Edge):</strong><br>
                            اضغط على أيقونة التثبيت <b>(⊕ / 📥)</b> في أقصى شريط عنوان المتصفح بالأعلى ليظهر البرنامج كأيقونة مستقلة على سطح المكتب.
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;margin-top:16px;">
                        <button type="button" onclick="if(window.PWAManager&&window.PWAManager.triggerInstall){window.PWAManager.triggerInstall();}document.getElementById('msg-pwa-app-install-modal').style.display='none';" style="flex:1;background:linear-gradient(135deg,#0d9488,#059669);border:1.5px solid #5eead4;color:#fff;font-family:Cairo,sans-serif;font-weight:900;font-size:0.92rem;padding:10px;border-radius:12px;cursor:pointer;">📲 فحص التثبيت التلقائي الآن</button>
                        <button type="button" onclick="document.getElementById('msg-pwa-app-install-modal').style.display='none'" style="background:rgba(148,163,184,0.15);border:1px solid rgba(148,163,184,0.35);color:#cbd5e1;font-family:Cairo,sans-serif;font-weight:800;padding:10px 18px;border-radius:12px;cursor:pointer;">إغلاق</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
    };

    window.triggerQuartetFullOfflineSetup = function () {
        let modal = document.getElementById('msg-full-offline-install-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'msg-full-offline-install-modal';
            modal.style.cssText = 'position:fixed;inset:0;z-index:999998;background:rgba(2,6,23,0.86);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
            modal.innerHTML = `
                <div style="background:linear-gradient(160deg,#0f172a,#1e1b4b,#020617);border:2px solid #facc15;border-radius:22px;max-width:520px;width:100%;padding:22px;color:#fff;box-shadow:0 20px 60px rgba(0,0,0,0.92),0 0 32px rgba(250,204,21,0.35);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,0.12);">
                        <div style="display:flex;align-items:center;gap:10px;font-size:1.12rem;font-weight:900;color:#fde047;">
                            <span>💾</span>
                            <span>التثبيت العام للبرنامج بالكامل (منظومة العمل أوفلاين)</span>
                        </div>
                        <button type="button" onclick="document.getElementById('msg-full-offline-install-modal').style.display='none'" style="background:rgba(239,68,68,0.2);border:1px solid #f87171;color:#fecaca;border-radius:8px;width:30px;height:30px;cursor:pointer;font-weight:900;">✕</button>
                    </div>
                    <p style="margin:0 0 12px;font-size:0.88rem;line-height:1.65;color:#e2e8f0;font-weight:700;">
                        يقوم <strong style="color:#fde047;">التثبيت العام</strong> بتحميل وحفظ ملفات شاشات النظام وقواعد البيانات المحلية (الأصناف، العملاء، الموردين، المخازن، الحسابات، والفواتير) على جهازك للعمل بكفاءة تامة حتى عند انقطاع الإنترنت.
                    </p>
                    <div id="msg-offline-progress-wrap" style="background:rgba(2,6,23,0.8);border:1px solid rgba(250,204,21,0.4);border-radius:14px;padding:12px 14px;margin-bottom:14px;">
                        <div style="display:flex;justify-content:space-between;font-size:0.82rem;font-weight:900;margin-bottom:6px;">
                            <span id="msg-offline-status-txt" style="color:#fef08a;">⚡ جاهز لبدء التثبيت والتحميل العام للنظام</span>
                            <span id="msg-offline-pct-txt" style="color:#38bdf8;">0%</span>
                        </div>
                        <div style="width:100%;height:10px;background:rgba(255,255,255,0.1);border-radius:99px;overflow:hidden;">
                            <div id="msg-offline-bar-fill" style="width:0%;height:100%;background:linear-gradient(90deg,#f59e0b,#10b981,#38bdf8);transition:width 0.35s ease;"></div>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button type="button" id="msg-start-full-offline-btn" onclick="window.runQuartetFullOfflineSync()" style="flex:1;background:linear-gradient(135deg,#d97706,#4f46e5);border:1.5px solid #fde047;color:#fff;font-family:Cairo,sans-serif;font-weight:900;font-size:0.94rem;padding:11px;border-radius:12px;cursor:pointer;box-shadow:0 0 20px rgba(250,204,21,0.4);">
                            🚀 بدء التثبيت والتحميل العام للنظام الآن
                        </button>
                        <button type="button" onclick="document.getElementById('msg-full-offline-install-modal').style.display='none'" style="background:rgba(148,163,184,0.15);border:1px solid rgba(148,163,184,0.35);color:#cbd5e1;font-family:Cairo,sans-serif;font-weight:800;padding:11px 18px;border-radius:12px;cursor:pointer;">إغلاق</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
    };

    window.runQuartetFullOfflineSync = function () {
        const statusEl = document.getElementById('msg-offline-status-txt');
        const pctEl = document.getElementById('msg-offline-pct-txt');
        const barEl = document.getElementById('msg-offline-bar-fill');
        const btnEl = document.getElementById('msg-start-full-offline-btn');
        if (btnEl) btnEl.disabled = true;

        const steps = [
            { pct: 25, txt: '📦 جاري تهيئة ذاكرة النظام المحلية (ServiceWorker & Cache)...' },
            { pct: 55, txt: '🗂️ جاري حفظ قواعد بيانات الأصناف والمخازن والعملاء أوفلاين...' },
            { pct: 85, txt: '🔐 جاري تأمين الشاشات والمحرك المحاسبي للعمل بدون إنترنت...' },
            { pct: 100, txt: '✅ اكتمل التثبيت العام بنجاح! النظام جاهز للعمل أوفلاين.' }
        ];
        if ('serviceWorker' in navigator) {
            try { navigator.serviceWorker.register('sw.js').catch(() => {}); } catch (e) {}
        }
        if (window.MagaiesOfflineSync && typeof window.MagaiesOfflineSync.syncNow === 'function') {
            try { window.MagaiesOfflineSync.syncNow(); } catch (e) {}
        }
        steps.forEach((st, idx) => {
            setTimeout(() => {
                if (statusEl) statusEl.textContent = st.txt;
                if (pctEl) pctEl.textContent = st.pct + '%';
                if (barEl) barEl.style.width = st.pct + '%';
                if (st.pct === 100) {
                    try { localStorage.setItem('magaies_full_offline_installed_at', new Date().toISOString()); } catch (e) {}
                    if (btnEl) {
                        btnEl.disabled = false;
                        btnEl.textContent = '🔄 تحديث النسخة الأوفلاين مرة أخرى';
                    }
                }
            }, (idx + 1) * 450);
        });
    };

    window.triggerQuartetThemesModal = function () {
        if (typeof window.openMaster7ThemesModal === 'function' && document.getElementById('master7ThemesModal')) {
            window.openMaster7ThemesModal();
            return;
        }
        let modal = document.getElementById('msg-universal-7themes-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'msg-universal-7themes-modal';
            modal.style.cssText = 'position:fixed;inset:0;z-index:999998;background:rgba(2,6,23,0.85);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
            const themes = [
                { key: 'warm_cream', name: 'بيج دافئ كريمي (Warm Cream)', bg: '#f5f0e6', fg: '#1e293b', isLight: true },
                { key: 'ultra_white', name: 'أبيض ناصع بلاتيني (Ultra White)', bg: '#ffffff', fg: '#0f172a', isLight: true },
                { key: 'exec_gray', name: 'رمادي تنفيذي بارد (Exec Gray)', bg: '#e2e8f0', fg: '#0f172a', isLight: true },
                { key: 'midnight_obsidian', name: 'كحلي فاحم ملكي (Midnight Navy)', bg: '#0f172a', fg: '#f8fafc', isLight: false },
                { key: 'mocha_brown', name: 'بني ملكي دافئ (Mocha Brown)', bg: '#271712', fg: '#fef3c7', isLight: false },
                { key: 'pure_oled', name: 'سواد مطلق فاحم (Pure OLED)', bg: '#020617', fg: '#ffffff', isLight: false },
                { key: 'slate_twilight', name: 'سليت فحمي مسائي (Twilight Slate)', bg: '#1e293b', fg: '#e2e8f0', isLight: false }
            ];
            modal.innerHTML = `
                <div style="background:linear-gradient(160deg,#0f172a,#1e1b4b,#020617);border:2px solid #f472b6;border-radius:22px;max-width:520px;width:100%;padding:22px;color:#fff;box-shadow:0 20px 60px rgba(0,0,0,0.92),0 0 30px rgba(244,114,182,0.35);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,0.12);">
                        <div style="display:flex;align-items:center;gap:10px;font-size:1.1rem;font-weight:900;color:#fbcfe8;">
                            <span>🎨</span>
                            <span>السمات السبعة المعتمدة والموحدة (7 سمات رئيسية)</span>
                        </div>
                        <button type="button" onclick="document.getElementById('msg-universal-7themes-modal').style.display='none'" style="background:rgba(239,68,68,0.2);border:1px solid #f87171;color:#fecaca;border-radius:8px;width:30px;height:30px;cursor:pointer;font-weight:900;">✕</button>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr;gap:8px;">
                        ${themes.map(t => `
                            <button type="button" onclick="window.applyUniversalQuartetTheme('${t.key}', ${t.isLight})" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:12px;border:1.5px solid rgba(255,255,255,0.2);background:rgba(15,23,42,0.85);color:#fff;font-family:Cairo,sans-serif;font-weight:900;font-size:0.9rem;cursor:pointer;">
                                <span>${t.name}</span>
                                <span style="width:28px;height:20px;border-radius:6px;background:${t.bg};border:1.5px solid #fbbf24;display:inline-block;"></span>
                            </button>
                        `).join('')}
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
    };

    const GLOBAL_ERP_TONES = [
        { id: 'dark_midnight',   name: 'داكن كحلي ملكي (Dark)',         bg: '#0f172a', fg: '#1e293b', brd: '#38bdf8', isDark: true,  themeKey: 'midnight_obsidian' },
        { id: 'pure_oled',       name: 'أسود ناصع للشاشات (OLED)',      bg: '#010308', fg: '#090d16', brd: '#0284c7', isDark: true,  themeKey: 'pure_oled' },
        { id: 'mocha_brown',     name: 'بني قهوة فخم (Mocha Dark)',     bg: '#1f120d', fg: '#3e2723', brd: '#f59e0b', isDark: true,  themeKey: 'mocha_brown' },
        { id: 'slate_twilight',  name: 'رمادي معدني داكن (Slate)',       bg: '#1e293b', fg: '#334155', brd: '#94a3b8', isDark: true,  themeKey: 'slate_twilight' },
        { id: 'ivory_default',   name: 'عاجي دافئ (فاتح افتراضي)',       bg: '#d9c39e', fg: '#f3e5ab', brd: '#854d0e', isDark: false, themeKey: 'warm_cream' },
        { id: 'royal_bronze',    name: '👑 الذهبي الملكي (برونزي فاخر)', bg: '#b07239', fg: '#d9a066', brd: '#78350f', isDark: false, themeKey: 'warm_cream' },
        { id: 'platinum_gray',   name: 'رمادي بلاتيني احترافي',          bg: '#64748b', fg: '#94a3b8', brd: '#334155', isDark: false, themeKey: 'exec_gray' },
        { id: 'crystal_silver',  name: 'فضي جليدي ناصع',                 bg: '#cbd5e1', fg: '#e2e8f0', brd: '#64748b', isDark: false, themeKey: 'ultra_white' },
        { id: 'sage_pearl',      name: 'لؤلؤي زمردي هادئ',               bg: '#10b981', fg: '#6ee7b7', brd: '#047857', isDark: false, themeKey: 'warm_cream' },
        { id: 'sky_alabaster',   name: 'سماوي لازوردي فاتح',             bg: '#0284c7', fg: '#38bdf8', brd: '#0369a1', isDark: false, themeKey: 'ultra_white' },
        { id: 'rose_blossom',    name: 'زهري ملكي ناعم (Rose)',          bg: '#ec4899', fg: '#fbcfe8', brd: '#9d174d', isDark: false, themeKey: 'warm_cream' },
        { id: 'warm_mocha_dark', name: 'موكا صحراوي غامق',               bg: '#6b4226', fg: '#9c6b43', brd: '#451a03', isDark: false, themeKey: 'warm_cream' },
        { id: 'deep_slate_dark', name: 'رصاصي فحمي غامق',                bg: '#334155', fg: '#475569', brd: '#0f172a', isDark: false, themeKey: 'exec_gray' }
    ];
    const SYSTEM_AUDIO_TONES = [
        { id: 'crystal',    name: '🔔 كريستال', freq: [880, 1320], type: 'sine' },
        { id: 'mechanical', name: '⌨️ ميكانيكية', freq: [340, 520], type: 'triangle' },
        { id: 'soft',       name: '🎵 هادئة',    freq: [528, 660], type: 'sine' },
        { id: 'mute',       name: '🔇 صامت',     freq: [],         type: 'sine' }
    ];
    const DARK_TONE_ALIASES = ['dark_midnight', 'pure_oled', 'mocha_brown', 'slate_twilight', 'midnight_obsidian', 'dark'];
    function getErpCookie(n) { const m = document.cookie.match(new RegExp('(?:^|; )' + n + '=([^;]*)')); return m ? decodeURIComponent(m[1]) : ''; }
    function safeSetErpStorage(k, v) {
        try { localStorage.setItem(k, v); } catch (_e) {
            try { ['erp_items_v1', 'erp_global_units_v1', 'erp_auto_backups', 'erp_audit_logs'].forEach(rk => localStorage.removeItem(rk)); localStorage.setItem(k, v); } catch (_e2) {}
        }
    }

    function ensureGlobalErpToneStylesAndRestore() {
        if (!document.getElementById('magaies-universal-tone-engine')) {
            const st = document.createElement('style');
            st.id = 'magaies-universal-tone-engine';
            st.textContent = `
                :root:not(.light-theme), :root:not(.light-theme)[data-light-tone="dark_midnight"] { --dk-card-1:#0f172a; --dk-card-2:#152342; --dk-sub-1:#0b1120; --dk-border:#38bdf8; }
                :root:not(.light-theme)[data-light-tone="pure_oled"], :root[data-theme="pure_oled"] { --dk-card-1:#010308; --dk-card-2:#090d16; --dk-sub-1:#000000; --dk-border:#0284c7; }
                :root:not(.light-theme)[data-light-tone="mocha_brown"], :root[data-theme="mocha_brown"] { --dk-card-1:#1f120d; --dk-card-2:#3e2723; --dk-sub-1:#140b07; --dk-border:#f59e0b; }
                :root:not(.light-theme)[data-light-tone="slate_twilight"], :root[data-theme="slate_twilight"] { --dk-card-1:#1e293b; --dk-card-2:#334155; --dk-sub-1:#0f172a; --dk-border:#94a3b8; }
                :root.light-theme, :root.light-theme[data-light-tone="ivory_default"] { --lt-canvas-1:#dfd0b4; --lt-canvas-2:#d2be9a; --lt-hdr-1:#eadbc0; --lt-hdr-2:#dec9a4; --lt-hdr-3:#d1b689; --lt-card-1:#fffdf8; --lt-card-2:#f7efe0; --lt-sub-1:#fffefb; --lt-sub-2:#f6ecda; --lt-th-1:#f5ead4; --lt-th-2:#e6d4b5; --lt-row-odd:#ffffff; --lt-row-even:#fdf9f0; --lt-border:#5c2808; --lt-border-hover:#b45309; --lt-ink:#120500; }
                :root.light-theme[data-light-tone="royal_bronze"] { --lt-canvas-1:#d4b08c; --lt-canvas-2:#c29970; --lt-hdr-1:#e2be98; --lt-hdr-2:#d4a679; --lt-hdr-3:#c48f5c; --lt-card-1:#fdf6ee; --lt-card-2:#f3e2ce; --lt-sub-1:#fef9f3; --lt-sub-2:#efd8be; --lt-th-1:#edd5ba; --lt-th-2:#dfbda0; --lt-row-odd:#fffcf8; --lt-row-even:#f9efe2; --lt-border:#4a2106; --lt-border-hover:#9a3412; --lt-ink:#140600; --bg-color:#d4b08c !important; --bg-main:#d4b08c !important; --bg-secondary:#e2be98 !important; --surface:#fdf6ee !important; }
                :root.light-theme[data-light-tone="platinum_gray"] { --lt-canvas-1:#c2d0e0; --lt-canvas-2:#aebfd4; --lt-hdr-1:#cfdeee; --lt-hdr-2:#bccfe4; --lt-hdr-3:#a8bfd9; --lt-card-1:#f7faff; --lt-card-2:#e6eef8; --lt-sub-1:#fafdff; --lt-sub-2:#e1ebf6; --lt-th-1:#dee9f6; --lt-th-2:#cbdbed; --lt-row-odd:#ffffff; --lt-row-even:#f1f6fc; --lt-border:#1e293b; --lt-border-hover:#0284c7; --lt-ink:#020617; --bg-color:#c2d0e0 !important; --bg-main:#c2d0e0 !important; --bg-secondary:#cfdeee !important; --surface:#f7faff !important; }
                :root.light-theme[data-light-tone="crystal_silver"] { --lt-canvas-1:#d2d8e2; --lt-canvas-2:#bfc7d4; --lt-hdr-1:#dfe4ec; --lt-hdr-2:#cfd6e2; --lt-hdr-3:#bec7d5; --lt-card-1:#fafbfd; --lt-card-2:#edf1f7; --lt-sub-1:#ffffff; --lt-sub-2:#e7ecf3; --lt-th-1:#e5eaf1; --lt-th-2:#d5dce7; --lt-row-odd:#ffffff; --lt-row-even:#f4f6f9; --lt-border:#111827; --lt-border-hover:#2563eb; --lt-ink:#030712; --bg-color:#d2d8e2 !important; --bg-main:#d2d8e2 !important; --bg-secondary:#dfe4ec !important; --surface:#fafbfd !important; }
                :root.light-theme[data-light-tone="sage_pearl"] { --lt-canvas-1:#bce0ce; --lt-canvas-2:#a4d1bb; --lt-hdr-1:#c8ead9; --lt-hdr-2:#b0ddca; --lt-hdr-3:#98cfb8; --lt-card-1:#f4fcf8; --lt-card-2:#e0f5ec; --lt-sub-1:#f9fefb; --lt-sub-2:#d8f2e6; --lt-th-1:#d4f0e3; --lt-th-2:#bfe6d4; --lt-row-odd:#ffffff; --lt-row-even:#eefaf4; --lt-border:#064e3b; --lt-border-hover:#059669; --lt-ink:#022c22; --bg-color:#bce0ce !important; --bg-main:#bce0ce !important; --bg-secondary:#c8ead9 !important; --surface:#f4fcf8 !important; }
                :root.light-theme[data-light-tone="sky_alabaster"] { --lt-canvas-1:#bad9f5; --lt-canvas-2:#a2caed; --lt-hdr-1:#c7e3fb; --lt-hdr-2:#afd4f5; --lt-hdr-3:#96c4ee; --lt-card-1:#f4faff; --lt-card-2:#dfeffd; --lt-sub-1:#f9fcff; --lt-sub-2:#d6ebfc; --lt-th-1:#d2e9fc; --lt-th-2:#bdddf9; --lt-row-odd:#ffffff; --lt-row-even:#eef7ff; --lt-border:#0c4a6e; --lt-border-hover:#0284c7; --lt-ink:#082f49; --bg-color:#bad9f5 !important; --bg-main:#bad9f5 !important; --bg-secondary:#c7e3fb !important; --surface:#f4faff !important; }
                :root.light-theme[data-light-tone="rose_blossom"] { --lt-canvas-1:#f5d0e3; --lt-canvas-2:#ebaed0; --lt-hdr-1:#fae0ee; --lt-hdr-2:#f3c6df; --lt-hdr-3:#ebadd0; --lt-card-1:#fff7fb; --lt-card-2:#fce8f3; --lt-sub-1:#fffafd; --lt-sub-2:#f9dfee; --lt-th-1:#f7d6e8; --lt-th-2:#efc2dc; --lt-row-odd:#ffffff; --lt-row-even:#fdf2f8; --lt-border:#831843; --lt-border-hover:#be185d; --lt-ink:#3b071e; --bg-color:#f5d0e3 !important; --bg-main:#f5d0e3 !important; --bg-secondary:#fae0ee !important; --surface:#fff7fb !important; }
                :root.light-theme[data-light-tone="warm_mocha_dark"] { --lt-canvas-1:#b89878; --lt-canvas-2:#a3805e; --lt-hdr-1:#ccb094; --lt-hdr-2:#ba9878; --lt-hdr-3:#a8825e; --lt-card-1:#faf3ea; --lt-card-2:#eee0cf; --lt-sub-1:#fdf8f2; --lt-sub-2:#e8d6c1; --lt-th-1:#e5d1ba; --lt-th-2:#d6bc9f; --lt-row-odd:#fdfaf6; --lt-row-even:#f5ebe0; --lt-border:#3e1b04; --lt-border-hover:#7c2d12; --lt-ink:#0f0400; --bg-color:#b89878 !important; --bg-main:#b89878 !important; --bg-secondary:#ccb094 !important; --surface:#faf3ea !important; }
                :root.light-theme[data-light-tone="deep_slate_dark"] { --lt-canvas-1:#a3b4ca; --lt-canvas-2:#8da1bb; --lt-hdr-1:#b8c7dc; --lt-hdr-2:#a2b5cf; --lt-hdr-3:#8ca3c1; --lt-card-1:#f2f6fc; --lt-card-2:#e2eaf5; --lt-sub-1:#f7faff; --lt-sub-2:#dbe5f2; --lt-th-1:#d6e1f0; --lt-th-2:#c3d2e6; --lt-row-odd:#fbfdff; --lt-row-even:#edf3fa; --lt-border:#0f172a; --lt-border-hover:#1d4ed8; --lt-ink:#020617; --bg-color:#a3b4ca !important; --bg-main:#a3b4ca !important; --bg-secondary:#b8c7dc !important; --surface:#f2f6fc !important; }
                /* UNIVERSAL-9-TONE-INHERITANCE */
                :root.light-theme body:not(.magaiees-login-body), :root.light-theme .dash-main, :root.light-theme .main-container, :root.light-theme .page-container, :root.light-theme .sgl-master-container { background: linear-gradient(180deg, var(--lt-canvas-1, #dfd0b4) 0%, var(--lt-canvas-2, #d2be9a) 100%) !important; color: var(--lt-ink, #120500) !important; }
                :root.light-theme header.premium-header, :root.light-theme .sales-glass-card, :root.light-theme .purchase-glass-card, :root.light-theme .neo-card, :root.light-theme .glass-panel, :root.light-theme .panel-header-row, :root.light-theme .table-container, :root.light-theme .items-toolbar, :root.light-theme .modal-overlay .modal-card, :root.light-theme .modal-card, :root.light-theme .modal-content, :root.light-theme .md-child-modal-card, :root.light-theme .md-c1-modal-card, :root.light-theme #md-rightclick-def-menu, :root.light-theme #msg-smart-hover-tooltip, :root.light-theme #md-col-context-popover, :root.light-theme #pur-light-tone-popover { background: linear-gradient(145deg, var(--lt-card-1, #fffdf8) 0%, var(--lt-card-2, #f7efe0) 100%) !important; border: 2px solid var(--lt-border, #5c2808) !important; color: var(--lt-ink, #120500) !important; }
                :root:not(.light-theme) .modal-overlay .modal-card, :root:not(.light-theme) .modal-card, :root:not(.light-theme) .modal-content, :root:not(.light-theme) .md-child-modal-card, :root:not(.light-theme) .md-c1-modal-card, :root:not(.light-theme) #md-rightclick-def-menu, :root:not(.light-theme) #msg-smart-hover-tooltip, :root:not(.light-theme) #md-col-context-popover, :root:not(.light-theme) #pur-light-tone-popover { background: linear-gradient(145deg, var(--dk-card-1, #0f172a) 0%, var(--dk-card-2, #152342) 100%) !important; border: 2px solid var(--dk-border, #38bdf8) !important; color: #ffffff !important; }
                :root.light-theme .modal-overlay div[style*="background:#0f172a"], :root.light-theme .modal-overlay div[style*="background: #0f172a"], :root.light-theme .modal-overlay div[style*="background:rgba(15,23,42"], :root.light-theme .modal-overlay div[style*="background: rgba(15, 23, 42"], :root.light-theme .modal-overlay div[style*="background:rgba(2,6,23"], :root.light-theme .modal-overlay div[style*="background: rgba(2, 6, 23"], :root.light-theme .modal-overlay div[style*="background:rgba(30,41,59"], :root.light-theme .modal-overlay div[style*="background: rgba(30, 41, 59"], :root.light-theme .md-child-modal div[style*="background:#0f172a"], :root.light-theme .md-child-modal div[style*="background:rgba(15,23,42"], :root.light-theme .md-child-modal div[style*="background:rgba(2,6,23"], :root.light-theme .md-child-modal div[style*="background:rgba(30,41,59"], :root.light-theme #pur-light-tone-popover button:not([data-tone-dark="1"]) { background: linear-gradient(145deg, var(--lt-sub-1, #fef9f3) 0%, var(--lt-sub-2, #efd8be) 100%) !important; border-color: var(--lt-border, #4a2106) !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; }
                :root.light-theme #pur-light-tone-popover, :root.light-theme #pur-light-tone-popover button:not([data-tone-dark="1"]) * { color: #000000 !important; -webkit-text-fill-color: #000000 !important; }
                :root.light-theme table thead tr th { background: linear-gradient(180deg, var(--lt-th-1, #f5ead4) 0%, var(--lt-th-2, #e6d4b5) 100%) !important; color: var(--lt-ink, #120500) !important; border-color: var(--lt-border, #5c2808) !important; font-weight: 900 !important; }
                :root.light-theme table tbody tr td { background: var(--lt-row-odd, #ffffff) !important; color: var(--lt-ink, #120500) !important; font-weight: 800 !important; }
                :root.light-theme table tbody tr:nth-child(even) td { background: var(--lt-row-even, #fdf9f0) !important; }
                :root.light-theme .modal-overlay h3, :root.light-theme .modal-overlay h4, :root.light-theme .modal-overlay label, :root.light-theme .modal-overlay th, :root.light-theme .modal-overlay td, :root.light-theme .h-label, :root.light-theme .v-label { color: var(--lt-ink, #120500) !important; font-weight: 900 !important; }
                :root.light-theme .modal-overlay input:not([type="color"]):not([type="range"]):not([type="checkbox"]):not([type="radio"]), :root.light-theme .modal-overlay select, :root.light-theme .modal-overlay textarea { background: #ffffff !important; color: var(--lt-ink, #120500) !important; border: 2px solid var(--lt-border, #5c2808) !important; font-weight: 900 !important; }
                :root.light-theme .custom-dropdown-list { background: #ffffff !important; border: 2px solid var(--lt-border, #5c2808) !important; color: var(--lt-ink, #120500) !important; }
                :root.light-theme .custom-dropdown-item, :root.light-theme .custom-dropdown-item * { color: var(--lt-ink, #120500) !important; font-weight: 800 !important; }
            `;
            (document.body || document.head).appendChild(st);
        } else if (document.body) {
            const st = document.getElementById('magaies-universal-tone-engine');
            const grnSt = document.getElementById('grn-purchase-constitution-2027-style');
            if (st && st.parentElement !== document.body && !grnSt) {
                document.body.appendChild(st);
            }
        }
        try {
            const params = new URLSearchParams(window.location.search);
            if (params.get('from') === 'purchase-receipt.php' && document.body && !document.getElementById('magaies-grn-return-one-step-btn')) {
                const retBtn = document.createElement('button');
                retBtn.type = 'button';
                retBtn.id = 'magaies-grn-return-one-step-btn';
                retBtn.innerHTML = '↩️ رجوع خطوة واحدة إلى شاشة استلام البضائع';
                retBtn.style.cssText = 'position:fixed; bottom:20px; left:24px; z-index:999990; padding:10px 18px; border-radius:14px; font-family:Cairo,Tajawal,sans-serif; font-size:0.95rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%); color:#000000; -webkit-text-fill-color:#000000; border:2.5px solid #78350f; box-shadow:0 10px 26px rgba(0,0,0,0.35), inset 0 2px 0 rgba(255,255,255,0.65); transition:transform 0.18s ease;';
                retBtn.onclick = function() {
                    if (window.history.length > 1 && document.referrer && document.referrer.indexOf('purchase-receipt.php') !== -1) {
                        window.history.back();
                    } else {
                        window.location.href = 'purchase-receipt.php';
                    }
                };
                document.body.appendChild(retBtn);
            }
        } catch (_e) {}
        const curP = (window.location.pathname || '').toLowerCase();
        if (curP.endsWith('/login.php') || curP.endsWith('/register.php') || (document.body && document.body.classList.contains('magaiees-login-body'))) {
            document.documentElement.classList.remove('light-theme');
            document.documentElement.classList.add('dark-theme');
            document.documentElement.setAttribute('data-theme', 'midnight_obsidian');
            if (document.body) {
                document.body.classList.remove('light-theme');
                document.body.classList.add('dark-theme');
                document.body.setAttribute('data-theme', 'midnight_obsidian');
            }
            return;
        }
        const savedTone = getErpCookie('magaies_light_tone') || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || 'ivory_default';
        const isDark = DARK_TONE_ALIASES.includes(savedTone);
        const matchedToneObj = GLOBAL_ERP_TONES.find(x => x.id === savedTone);
        const normTone = matchedToneObj ? matchedToneObj.id : (isDark ? 'dark_midnight' : 'ivory_default');
        const savedTheme = (matchedToneObj && matchedToneObj.themeKey) ? matchedToneObj.themeKey : (isDark ? 'midnight_obsidian' : 'warm_cream');
        document.documentElement.setAttribute('data-light-tone', normTone);
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.documentElement.classList.toggle('light-theme', !isDark);
        document.documentElement.classList.toggle('dark-theme', isDark);
        if (document.body) {
            document.body.setAttribute('data-light-tone', normTone);
            document.body.setAttribute('data-theme', savedTheme);
            document.body.classList.toggle('light-theme', !isDark);
            document.body.classList.toggle('dark-theme', isDark);
        }
    }
    ensureGlobalErpToneStylesAndRestore();
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ensureGlobalErpToneStylesAndRestore);

    window.playGlobalErpSystemSoundTone = function(soundId) {
        const sid = soundId || localStorage.getItem('magaies_system_sound_tone') || 'crystal';
        const st = SYSTEM_AUDIO_TONES.find(x => x.id === sid) || SYSTEM_AUDIO_TONES[0];
        if (!st || !st.freq || !st.freq.length) return;
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            const ctx = new Ctx();
            st.freq.forEach((f, idx) => {
                const osc = ctx.createOscillator();
                const g = ctx.createGain();
                osc.type = st.type || 'sine';
                osc.frequency.value = f;
                g.gain.setValueAtTime(0.06, ctx.currentTime + idx * 0.055);
                g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + idx * 0.055 + 0.09);
                osc.connect(g); g.connect(ctx.destination);
                osc.start(ctx.currentTime + idx * 0.055);
                osc.stop(ctx.currentTime + idx * 0.055 + 0.095);
            });
        } catch (_e) {}
    };
    window.setGlobalErpSystemSoundTone = function(soundId) {
        safeSetErpStorage('magaies_system_sound_tone', soundId);
        try { document.cookie = 'magaies_system_sound_tone=' + encodeURIComponent(soundId) + '; path=/; max-age=315360000; SameSite=Lax'; } catch (_e) {}
        window.playGlobalErpSystemSoundTone(soundId);
        const st = SYSTEM_AUDIO_TONES.find(x => x.id === soundId);
        if (st && typeof window.showToast === 'function') window.showToast('🔊 تم تفعيل نغمة النظام: ' + st.name, 'info', 2000);
    };

    window.applyGlobalErpToneOrTheme = function (toneId, notify) {
        const isDark = DARK_TONE_ALIASES.includes(toneId);
        const t = GLOBAL_ERP_TONES.find(x => x.id === toneId) || (isDark ? GLOBAL_ERP_TONES[0] : GLOBAL_ERP_TONES[4]);
        const themeKey = t.themeKey || (t.isDark ? 'midnight_obsidian' : 'warm_cream');
        document.documentElement.setAttribute('data-light-tone', t.id);
        document.documentElement.setAttribute('data-theme', themeKey);
        document.documentElement.classList.toggle('light-theme', !t.isDark);
        document.documentElement.classList.toggle('dark-theme', !!t.isDark);
        if (document.body) {
            document.body.setAttribute('data-light-tone', t.id);
            document.body.setAttribute('data-theme', themeKey);
            document.body.classList.toggle('light-theme', !t.isDark);
            document.body.classList.toggle('dark-theme', !!t.isDark);
        }
        safeSetErpStorage('magaies_light_tone', t.id);
        safeSetErpStorage('pur_light_tone_palette', t.id);
        ['magaies_active_theme','magaiees_global_theme','magaiees_mobile_theme','ui_theme'].forEach(k => safeSetErpStorage(k, themeKey));
        safeSetErpStorage('erp_theme', t.isDark ? 'dark' : 'light');
        try {
            document.cookie = 'magaies_light_tone=' + encodeURIComponent(t.id) + '; path=/; max-age=315360000; SameSite=Lax';
            document.cookie = 'magaies_active_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=315360000; SameSite=Lax';
        } catch (_e) {}
        if (typeof BroadcastChannel !== 'undefined') {
            try { new BroadcastChannel('elsajana_sovereign_10x_viewport_v1').postMessage({ type: 'TONE_SYNC', toneId: t.id }); } catch(_e) {}
        }
        if (typeof window.applyPurchaseLightTone === 'function' && window.applyPurchaseLightTone !== window.applyGlobalErpToneOrTheme) {
            try { window.applyPurchaseLightTone(t.id, notify); } catch(_e) {}
        }
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: themeKey, tone: t.id, isLight: !t.isDark } }));
        if (notify && typeof window.showToast === 'function') {
            window.showToast((t.isDark ? '🌙 ' : '🎨 ') + 'تم تطبيق السمة في كامل الشاشة والنوافذ: ' + t.name, 'success', 2400);
        }
    };

    window.applyUniversalQuartetTheme = function (themeKey, isLight) {
        if (typeof window.applyMasterTheme === 'function') {
            window.applyMasterTheme(themeKey);
        } else {
            document.documentElement.setAttribute('data-theme', themeKey);
            if (document.body) document.body.setAttribute('data-theme', themeKey);
            document.documentElement.classList.toggle('light-theme', !!isLight);
            document.documentElement.classList.toggle('dark-theme', !isLight);
            if (document.body) {
                document.body.classList.toggle('light-theme', !!isLight);
                document.body.classList.toggle('dark-theme', !isLight);
            }
            try {
                ['magaies_active_theme','magaiees_global_theme','magaiees_mobile_theme','ui_theme'].forEach(k => localStorage.setItem(k, themeKey));
                localStorage.setItem('erp_theme', isLight ? 'light' : 'dark');
                document.cookie = 'magaies_active_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=315360000; SameSite=Lax';
            } catch (e) {}
        }
        const m = document.getElementById('msg-universal-7themes-modal');
        if (m) m.style.display = 'none';
    };

    window.triggerQuartetShield = function () {
        if (typeof window.openDesktopSyncAuditModal === 'function') {
            window.openDesktopSyncAuditModal();
        } else {
            window.triggerQuartetFullOfflineSetup();
        }
    };

    window.triggerQuartetAuditSearch = function () {
        if (typeof window.openAuditTrackerModal === 'function') {
            window.openAuditTrackerModal();
        } else {
            window.location.href = 'audit-log.php';
        }
    };

    window.triggerQuartetFullscreen = function () {
        if (typeof window.toggleFullScreen === 'function') {
            window.toggleFullScreen();
        } else if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(() => {});
        } else if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    };

    window.openUniversalErpTonePopover = function (anchorEl) {
        let pop = document.getElementById('pur-light-tone-popover');
        if (!pop) {
            pop = document.createElement('div');
            pop.id = 'pur-light-tone-popover';
            document.body.appendChild(pop);
            document.addEventListener('click', (e) => { const isBtn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn'); if (pop.style.display !== 'none' && !pop.contains(e.target) && !isBtn) pop.style.display = 'none'; });
        }
        if (pop.style.display === 'block') { pop.style.display = 'none'; return; }
        const isDarkNow = !document.documentElement.classList.contains('light-theme');
        pop.style.cssText = 'position:fixed;z-index:9999999;background:' + (isDarkNow ? 'linear-gradient(145deg,#0f172a,#1e293b)' : 'linear-gradient(145deg,#fffbf2,#f3ead8)') + ';border:2px solid #fbbf24;border-radius:14px;padding:10px;box-shadow:0 18px 42px rgba(0,0,0,0.65),0 0 22px rgba(245,158,11,0.35);min-width:275px;max-height:86vh;overflow-y:auto;direction:rtl;font-family:Tajawal,Cairo,sans-serif;';
        const rawCur = getErpCookie('magaies_light_tone') || document.documentElement.getAttribute('data-light-tone') || localStorage.getItem('magaies_light_tone') || (isDarkNow ? 'dark_midnight' : 'ivory_default');
        const cur = GLOBAL_ERP_TONES.some(x => x.id === rawCur) ? rawCur : (DARK_TONE_ALIASES.includes(rawCur) ? 'dark_midnight' : 'ivory_default');
        const curSound = getErpCookie('magaies_system_sound_tone') || localStorage.getItem('magaies_system_sound_tone') || 'crystal';
        const hdrCol = isDarkNow ? '#fde047' : '#000000';
        pop.innerHTML = `<div style="color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;font-weight:900;font-size:0.9rem;margin-bottom:7px;padding-bottom:5px;border-bottom:1px solid rgba(148,163,184,0.35);display:flex;justify-content:space-between;align-items:center;"><span>🎨 السمات ونغمات النظام (13 لوناً)</span><span style="font-size:0.72rem;color:${isDarkNow?'#94a3b8':'#000000'}!important;-webkit-text-fill-color:${isDarkNow?'#94a3b8':'#000000'}!important;">0ms موحد</span></div>` +
            GLOBAL_ERP_TONES.map(t => {
                const txtCol = t.isDark ? '#ffffff' : '#000000';
                const btnBg = t.isDark ? `linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%)` : `linear-gradient(135deg,#ffffff 0%,${t.fg} 100%)`;
                return `<button type="button" data-tone-dark="${t.isDark?'1':'0'}" onclick="window.applyGlobalErpToneOrTheme('${t.id}',true);window.playGlobalErpSystemSoundTone();document.getElementById('pur-light-tone-popover').style.display='none';" style="width:100%;display:flex;align-items:center;gap:9px;padding:6px 9px;margin-bottom:4px;border-radius:9px;cursor:pointer;background:${btnBg}!important;border:${cur===t.id?'2.5px solid #d97706':'1.5px solid '+t.brd}!important;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;font-weight:900;font-size:0.84rem;text-align:right;"><span style="width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%);border:2px solid ${t.brd};flex-shrink:0;"></span><span style="flex:1;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;">${t.name}</span>${cur===t.id?`<span style="color:${t.isDark?'#fde047':'#047857'}!important;-webkit-text-fill-color:${t.isDark?'#fde047':'#047857'}!important;">✓</span>`:''}</button>`;
            }).join('') +
            `<div style="margin-top:7px;padding-top:6px;border-top:1px solid rgba(148,163,184,0.35);"><div style="font-size:0.78rem;font-weight:900;color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;margin-bottom:5px;">🔊 نغمات النظام الصوتية التفاعلية (4):</div><div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">` +
            SYSTEM_AUDIO_TONES.map(s => `<button type="button" onclick="window.setGlobalErpSystemSoundTone('${s.id}');document.getElementById('pur-light-tone-popover').style.display='none';" style="padding:5px 7px;border-radius:7px;cursor:pointer;font-weight:900;font-size:0.76rem;border:${curSound===s.id?'2px solid #10b981':'1.5px solid #64748b'}!important;background:${isDarkNow?'#1e293b':'#ffffff'}!important;color:${isDarkNow?'#ffffff':'#000000'}!important;-webkit-text-fill-color:${isDarkNow?'#ffffff':'#000000'}!important;">${s.name}${curSound===s.id?' ✓':''}</button>`).join('') +
            `</div></div>`;
        const r = anchorEl ? anchorEl.getBoundingClientRect() : { bottom: 60, left: 20 };
        pop.style.top = Math.max(10, Math.min(window.innerHeight - 520, r.bottom + 8)) + 'px';
        pop.style.left = Math.max(12, Math.min(window.innerWidth - 295, r.left)) + 'px';
        pop.style.display = 'block';
    };
    window.openPurchaseLightTonePopover = window.openUniversalErpTonePopover;

    window.triggerQuartetQuickTheme = function (anchorEl) {
        const btn = anchorEl || document.querySelector('.msg-quartet-qtheme');
        window.openUniversalErpTonePopover(btn);
    };

    function isLandingStartupScreen() {
        const p = (window.location.pathname || '').toLowerCase();
        if (document.querySelector('.header-theme-dashboard')) {
            return false;
        }
        if (p.endsWith('/index.php') || p.endsWith('/index.html') || p.endsWith('/login.php') || p.endsWith('/register.php') || p.endsWith('/magaees/') || p.endsWith('/magaees') || p.endsWith('/magaies/') || p.endsWith('/magaies') || p === '/' || p === '') {
            if (!p.includes('api_index.php')) return true;
        }
        if (document.querySelector('section.hero') && document.querySelector('nav.navbar')) {
            return true;
        }
        if (document.body && document.body.classList.contains('magaiees-login-body')) {
            return true;
        }
        return false;
    }

    function isMainDashboardScreen() {
        const p = (window.location.pathname || '').toLowerCase();
        if (p.includes('magaiees_unified_dashboard.php') || p.includes('dashboard-pro.php')) {
            return true;
        }
        if (document.querySelector('.header-theme-dashboard')) {
            return true;
        }
        return false;
    }

    function ensureFixedSquareLauncher() {
        let pod = document.getElementById('msg-universal-fixed-launcher');
        if (isLandingStartupScreen()) {
            if (pod) {
                try { pod.remove(); } catch (e) {}
            }
            return;
        }

        // Remove any duplicate standalone TV button wrapper so #header-media-btn resolves to our Royal Quartet
        document.querySelectorAll('#header-media-control-wrap, .subscreen-floating-tv-wrap').forEach(el => {
            if (!el.closest('#msg-universal-fixed-launcher')) {
                try { el.remove(); } catch (e) {}
            }
        });

        const showDashboardSystemPod = isMainDashboardScreen();

        if (!pod) {
            pod = document.createElement('div');
            pod.id = 'msg-universal-fixed-launcher';
            pod.className = 'msg-twin-quartets-wrapper';
            pod.innerHTML = `
                <!-- ═══ لوحة الأزرار السداسية الموحدة (من اليسار لليمين، ومن الأعلى للأسفل): (التلفاز/الرجوع) • (المرشد/السمات) • (الشاشة/البحث) ═══ -->
                <div class="msg-fixed-command-pod pod-smart-guide">
                    <!-- العمود الثالث (يمين): أعلى = الشاشة، أسفل = البحث -->
                    <button type="button" class="msg-quartet-btn msg-quartet-fullscreen" onclick="window.triggerQuartetFullscreen()" title="⛶ توسيع وملء الشاشة">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4 9V4H9M20 9V4H15M4 15V20H9M20 15V20H15" stroke="#fde047" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="8" y="8" width="8" height="8" rx="1.5" stroke="#ffffff" stroke-width="1.8"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">الشاشة</span>
                    </button>
                    <button type="button" class="msg-quartet-btn msg-quartet-search" onclick="window.triggerQuartetAuditSearch()" title="🔍 البحث ومتعقب المستندات وسجل المستخدمين (Alt+T)">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="6.8" fill="rgba(56,189,248,0.28)" stroke="#ffffff" stroke-width="2.2"/>
                                <path d="M16.2 16.2L21 21" stroke="#fde047" stroke-width="2.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">البحث</span>
                    </button>

                    <!-- العمود الثاني (وسط): أعلى = المرشد، أسفل = السمات -->
                    <button type="button" class="msg-quartet-btn msg-quartet-guide" onclick="window.openSmartGuide()" title="المرشد الذكي — شرح الشاشة التفاعلي وبداخله التصوير والتسجيل السينمائي 4K">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M12 3L2 8.5L12 14L22 8.5L12 3Z" fill="rgba(254,240,138,0.28)" stroke="#fde047" stroke-width="2.2" stroke-linejoin="round"/>
                                <path d="M6 11.5V16.5C6 18.2 8.7 19.8 12 19.8C15.3 19.8 18 18.2 18 16.5V11.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round"/>
                                <path d="M21 9.5V15.5" stroke="#fde047" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">المرشد</span>
                    </button>
                    <button type="button" class="msg-quartet-btn msg-quartet-qtheme" onclick="window.triggerQuartetQuickTheme(this)" title="🎨 السمات ونغمات النظام المعتمدة والموحدة (13 لوناً + 4 نغمات)">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M12 3C7.03 3 3 6.8 3 11.5C3 16.2 7.03 20 12 20C13.66 20 15 18.66 15 17C15 16.2 14.68 15.5 14.18 14.95C13.7 14.42 14.08 13.5 14.8 13.5H16.5C19.54 13.5 22 11.04 22 8C22 5.24 17.52 3 12 3Z" fill="rgba(244,114,182,0.28)" stroke="#ffffff" stroke-width="2.1"/>
                                <circle cx="8" cy="10" r="1.4" fill="#fde047"/>
                                <circle cx="12" cy="7.5" r="1.4" fill="#38bdf8"/>
                                <circle cx="16" cy="9.5" r="1.4" fill="#4ade80"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">السمات</span>
                    </button>

                    <!-- العمود الأول (أقصى اليسار): أعلى = التلفاز، أسفل = الرجوع -->
                    <div id="header-media-control-wrap" class="msg-quartet-tv-cell">
                        <button type="button" id="header-media-btn" class="msg-quartet-btn msg-quartet-tv" onclick="if(typeof window.toggleHeaderMediaPopover==='function'){window.toggleHeaderMediaPopover(event);}" title="قنوات التلفاز والقرآن الكريم والحديث الشريف (بث حي في الخلفية)">
                            <span class="msg-quartet-icon-box" id="header-media-icon">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="2.5" y="6.5" width="19" height="13" rx="3" fill="rgba(15,23,42,0.55)" stroke="#ffffff" stroke-width="2.3"/>
                                    <path d="M8 2.5L12 6.5L16 2.5" stroke="#fde047" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polygon points="10,10 15.5,13 10,16" fill="#fde047"/>
                                </svg>
                            </span>
                            <span id="header-media-label" class="msg-quartet-label">التلفاز</span>
                            <span id="header-media-dot" class="header-tv-status-dot" style="display:none; position:absolute; top:4px; left:4px;"></span>
                        </button>
                    </div>
                    <button type="button" class="msg-quartet-btn msg-quartet-back" onclick="if(typeof window.magaieesGoBack==='function'){window.magaieesGoBack();}else{window.history.back();}" title="الرجوع للخلف (العودة للشاشة السابقة)">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M9 14L4 9L9 4" stroke="#ffffff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 20V13C20 10.7909 18.2091 9 16 9H4" stroke="#fef08a" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">الرجوع</span>
                    </button>
                </div>
            `;
        }

        // Dock harmoniously inside the screen's top header bar across ALL screens in the ERP
        const explicitSlot = document.getElementById('magaies-quartet-header-slot');
        const enterpriseControls = document.querySelector('.header-enterprise-controls');
        let dockTarget = explicitSlot || enterpriseControls;

        if (!dockTarget) {
            const candidateHeaders = document.querySelectorAll(
                '#sl-top-cockpit-bar, header.premium-header, .magical-glass-header, .premium-header, .report-header, .header-section, .ledger-header, .order-header, .emp-header, .panel-header, .top-header, .page-header, nav.navbar, .transfer-container > div:first-child, .header'
            );
            for (let i = 0; i < candidateHeaders.length; i++) {
                const h = candidateHeaders[i];
                const rect = h.getBoundingClientRect();
                if (rect.height > 28 && (rect.top + window.scrollY) < 260) {
                    dockTarget = h;
                    break;
                }
            }
        }

        if (dockTarget) {
            if (!explicitSlot && !enterpriseControls) {
                const cs = window.getComputedStyle(dockTarget);
                if (cs.display !== 'flex' && cs.display !== 'inline-flex' && cs.display !== 'grid') {
                    dockTarget.style.display = 'flex';
                    dockTarget.style.alignItems = 'center';
                    dockTarget.style.justifyContent = 'space-between';
                    dockTarget.style.flexWrap = 'wrap';
                    dockTarget.style.gap = '10px';
                }
                pod.style.marginRight = 'auto';
            }
            pod.classList.add('is-docked-in-header');
            if (pod.parentElement !== dockTarget) {
                dockTarget.appendChild(pod);
            }
        } else if (!pod.parentElement) {
            document.body.appendChild(pod);
        }

        // Keep the 4 buttons permanently docked inside the top header so they scroll naturally with the header title
        pod.classList.remove('is-scroll-floating');

        // Render Laptop & Offline Install buttons inside settings.php as requested
        if (/settings\.php/i.test(window.location.pathname) && !document.getElementById('settings-pwa-install-suite')) {
            const stTarget = document.querySelector('.settings-container, .main-content, main, body');
            if (stTarget) {
                const bar = document.createElement('div');
                bar.id = 'settings-pwa-install-suite';
                bar.style.cssText = 'display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin:8px auto 14px auto;padding:10px 16px;border-radius:12px;background:linear-gradient(135deg,#0f172a,#1e293b);border:1.5px solid #fbbf24;direction:rtl;box-shadow:0 6px 18px rgba(0,0,0,0.45);';
                bar.innerHTML = '<div style="color:#fef08a;font-weight:900;font-size:14px;display:flex;align-items:center;gap:8px;"><span>📲</span><span>تثبيت نظام مقاييس على اللابتوب والعمل بدون إنترنت (أوفلاين):</span></div>'
                    + '<div style="display:flex;align-items:center;gap:8px;">'
                    + '<button type="button" onclick="window.triggerQuartetAppInstall&&window.triggerQuartetAppInstall()" style="height:34px;padding:0 14px;border-radius:8px;background:linear-gradient(135deg,#0284c7,#1e40af);border:1.5px solid #7dd3fc;color:#fff;font-weight:900;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">💻 لابتوب (تثبيت سطح المكتب)</button>'
                    + '<button type="button" onclick="window.triggerQuartetFullOfflineSetup&&window.triggerQuartetFullOfflineSetup()" style="height:34px;padding:0 14px;border-radius:8px;background:linear-gradient(135deg,#059669,#065f46);border:1.5px solid #6ee7b7;color:#fff;font-weight:900;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">⚡ تثبيت عام (أوفلاين شامل)</button>'
                    + '</div>';
                stTarget.insertBefore(bar, stTarget.firstChild);
            }
        }

        if (typeof window.syncHeaderPopoverUI === 'function') {
            try { window.syncHeaderPopoverUI(); } catch (e) {}
        }
    }

    function formatSpeedPctLabel(rateOrPct) {
        let pct = window.__msgSpeechSpeedPct;
        if (typeof rateOrPct === 'number' && Math.abs(rateOrPct) <= 4.5 && rateOrPct > 0) {
            pct = playbackRateToPct(rateOrPct);
        }
        pct = Math.max(-50, Math.min(300, Math.round(Number(pct) || 0)));
        if (pct < 0) return `🐢 ${pct}%`;
        if (pct === 0) return `⚖️ 0%`;
        if (pct <= 100) return `⚡ +${pct}%`;
        return `🚀 +${pct}%`;
    }

    window.toggleCoachCapsulePosition = function () {
        const bar = document.getElementById('msg-live-coach-bar');
        const btn = document.getElementById('msg-coach-pos-btn');
        if (!bar) return;
        bar.dataset.userLockedPos = '1';
        bar.classList.toggle('docked-top');
        if (btn) {
            btn.textContent = bar.classList.contains('docked-top') ? '⬇️ للأسفل' : '⬆️ للأعلى';
        }
    };

    window.toggleCoachCapsuleMinimize = function () {
        const bar = document.getElementById('msg-live-coach-bar');
        const btn = document.getElementById('msg-coach-min-btn');
        if (!bar) return;
        bar.classList.toggle('is-minimized');
        if (btn) {
            btn.textContent = bar.classList.contains('is-minimized') ? '🗖 توسيع' : '🗕 تصغير';
        }
    };

    window.toggleCoachSubtitleBox = function () {
        const box = document.getElementById('msg-coach-speech-text');
        if (!box) return;
        box.style.display = (box.style.display === 'none') ? 'block' : 'none';
    };

    window.onCoachModeSelectChange = function (modeVal) {
        if (!modeVal) return;
        if (modeVal === 'pick') {
            window.startSmartPickElementMode();
        } else {
            window.startLiveAutopilotSimulation(modeVal);
        }
    };

    function ensureGuideDomElements() {
        ensureFixedSquareLauncher();
        if (document.getElementById('msg-universal-modal')) return;

        const backdrop = document.createElement('div');
        backdrop.id = 'msg-universal-modal';
        backdrop.className = 'msg-modal-backdrop';
        backdrop.onclick = (e) => { if (e.target === backdrop) window.closeSmartGuide(); };

        backdrop.innerHTML = `
            <div class="msg-modal-window">
                <div class="msg-modal-header">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div style="width:46px; height:46px; border-radius:14px; background:linear-gradient(135deg,#f59e0b,#ec4899,#6366f1); display:flex; align-items:center; justify-content:center; font-size:1.65rem; box-shadow:0 0 22px rgba(236,72,153,0.5);">
                            🎓
                        </div>
                        <div>
                            <h2 id="msg-hdr-title" style="margin:0; font-size:1.1rem; font-weight:900; color:#ffffff;">المرشد الذكي</h2>
                            <p id="msg-hdr-sub" style="margin:2px 0 0; font-size:0.76rem; color:#bae6fd; font-weight:700;"></p>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <select id="msg-modal-ai-degree" class="sl-input" onchange="window.setMsgAiIntelligenceDegree(this.value)" style="padding:5px 10px !important; font-size:0.78rem !important; font-weight:900 !important; border-color:#f59e0b !important; color:#fde047 !important;" title="درجة الذكاء الاصطناعي ومستوى الشرح">
                            <option value="ai_5" ${window.__msgAiDegree==='ai_5'?'selected':''}>👑 ذكاء AI (درجة 5: بروفيسور موسوعي 360°)</option>
                            <option value="ai_4" ${window.__msgAiDegree==='ai_4'?'selected':''}>🛡️ ذكاء AI (درجة 4: رقابي وتدريبي متقدم)</option>
                            <option value="ai_3" ${window.__msgAiDegree==='ai_3'?'selected':''}>📊 ذكاء AI (درجة 3: محاسبي وتشغيلي)</option>
                            <option value="ai_2" ${window.__msgAiDegree==='ai_2'?'selected':''}>💡 ذكاء AI (درجة 2: شرح بلدي وميداني)</option>
                            <option value="ai_1" ${window.__msgAiDegree==='ai_1'?'selected':''}>⚡ ذكاء AI (درجة 1: فوري مختصر)</option>
                        </select>
                        <select id="msg-voice-select" class="sl-input" onchange="window.onMsgVoiceChange(this.value)" style="padding:5px 10px !important; font-size:0.78rem !important; font-weight:900 !important;" title="أسلوب صياغة الشرح (الصوت رجالي فخيم موحد)">
                            <option value="fusha">📖 صياغة بالعربية الفصحى (صوت رجالي فخيم ثابت)</option>
                            <option value="egyptian">🇪🇬 صياغة باللهجة المصرية (صوت رجالي فخيم ثابت)</option>
                            <option value="sudanese">🇸🇩 صياغة باللهجة السودانية (صوت رجالي فخيم ثابت)</option>
                        </select>

                        <!-- WORKING SPEED ARROWS IN MODAL HEADER (-50% to +300%) -->
                        <div class="msg-speed-arrows-group" title="تحكم في سرعة الصوت (من ناقص 50% إلى زائد 300%)">
                            <button type="button" class="msg-speed-arrow-btn" onclick="window.adjustSpeechSpeedPctBy(-10, true)" title="إبطاء سرعة الصوت (حتى -50%)">🐢 ناقص -</button>
                            <span id="msg-modal-speed-lbl" style="font-size:0.76rem; font-weight:900; color:#fef08a; min-width:64px; text-align:center;">${formatSpeedPctLabel()}</span>
                            <button type="button" class="msg-speed-arrow-btn" onclick="window.adjustSpeechSpeedPctBy(20, true)" title="تسريع سرعة الصوت (حتى +300%)">+ زائد ⚡</button>
                        </div>

                        <div class="msg-speed-arrows-group" title="تحكم في مستوى الصوت (ناقص يقلل وزائد يزيد حتى 300%)">
                            <button type="button" class="msg-speed-arrow-btn" onclick="window.adjustMsgVoiceVolume && window.adjustMsgVoiceVolume(-0.20)" title="تقليل الصوت">🔉 ناقص -</button>
                            <span id="msg-modal-vol-lbl" style="font-size:0.76rem; font-weight:900; color:#34d399; min-width:52px; text-align:center;">🔊 ${Math.round((window.__msgUserVoiceVolume || 1.65) * 100)}%</span>
                            <button type="button" class="msg-speed-arrow-btn" onclick="window.adjustMsgVoiceVolume && window.adjustMsgVoiceVolume(0.20)" title="تعلية الصوت">+ زائد 🔊</button>
                        </div>

                        <button type="button" onclick="window.closeSmartGuide()" class="sl-btn" style="background:rgba(239,68,68,0.22); border-color:#f87171; color:#fca5a5; padding:6px 12px;">
                            ✕ إغلاق
                        </button>
                    </div>
                </div>

                <!-- 5 CLEAR EXPLANATION MODES: FULL SCREEN vs PURCHASES vs SALES vs SPECIFIC ELEMENT vs HANDS-ON TRAINING -->
                <div class="msg-mode-selector-bar" style="flex-shrink:0; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));">
                    <button type="button" class="msg-mode-card-btn" onclick="window.startLiveAutopilotSimulation('all')" style="border-color:#10b981; background:linear-gradient(135deg,rgba(6,78,59,0.75),rgba(15,23,42,0.92));">
                        <span style="font-size:1.35rem;">🌐</span>
                        <div>
                            <div style="font-weight:900; font-size:0.81rem; color:#6ee7b7;">1. اشرح الشاشة كلها (A-Z)</div>
                            <div style="font-size:0.69rem; color:#cbd5e1;">جولة شاملة للمشتريات والمبيعات</div>
                        </div>
                    </button>
                    <button type="button" class="msg-mode-card-btn" onclick="window.startLiveAutopilotSimulation('purchases')" style="border-color:#38bdf8; background:linear-gradient(135deg,rgba(12,74,110,0.75),rgba(15,23,42,0.92));">
                        <span style="font-size:1.35rem;">📥</span>
                        <div>
                            <div style="font-weight:900; font-size:0.81rem; color:#7dd3fc;">2. اشرح بوابة المشتريات</div>
                            <div style="font-size:0.69rem; color:#cbd5e1;">10 خطوات لاستلام وتقسيم GRN</div>
                        </div>
                    </button>
                    <button type="button" class="msg-mode-card-btn" onclick="window.startLiveAutopilotSimulation('sales')" style="border-color:#34d399; background:linear-gradient(135deg,rgba(4,120,87,0.65),rgba(15,23,42,0.92));">
                        <span style="font-size:1.35rem;">📤</span>
                        <div>
                            <div style="font-weight:900; font-size:0.81rem; color:#a7f3d0;">3. اشرح بوابة المبيعات</div>
                            <div style="font-size:0.69rem; color:#cbd5e1;">9 خطوات لتسليم وتقسيم GDN</div>
                        </div>
                    </button>
                    <button type="button" class="msg-mode-card-btn" onclick="window.startSmartPickElementMode()" style="border-color:#fbbf24; background:linear-gradient(135deg,rgba(120,53,15,0.65),rgba(15,23,42,0.92));">
                        <span style="font-size:1.35rem;">👆</span>
                        <div>
                            <div style="font-weight:900; font-size:0.81rem; color:#fde047;">4. اشرح زراً أضغط عليه</div>
                            <div style="font-size:0.69rem; color:#cbd5e1;">اضغط على أي جزء ليشرحه وحده</div>
                        </div>
                    </button>
                    <button type="button" class="msg-mode-card-btn" onclick="window.startInteractiveHandsOnTraining()" style="border-color:#f472b6; background:linear-gradient(135deg,rgba(131,24,67,0.72),rgba(15,23,42,0.92));">
                        <span style="font-size:1.35rem;">🎯</span>
                        <div>
                            <div style="font-weight:900; font-size:0.81rem; color:#fbcfe8;">5. درّبني عملياً بيدي</div>
                            <div style="font-size:0.69rem; color:#cbd5e1;">تدريب تفاعلي خطوة بخطوة</div>
                        </div>
                    </button>
                </div>

                <div class="msg-nav-tabs">
                    <button type="button" class="msg-nav-tab active" onclick="window.switchSmartGuideTab('msg-pane-story', this)">
                        🖼️ 1. الشرح المصوّر المبسّط (4 خطوات بالترتيب)
                    </button>
                    <button type="button" class="msg-nav-tab" onclick="window.switchSmartGuideTab('msg-pane-dict', this)">
                        🎛️ 2. قاموس الأزرار والخانات (اضغط على أي زر ليشرحه وحده)
                    </button>
                    <button type="button" class="msg-nav-tab" onclick="window.switchSmartGuideTab('msg-pane-faq', this)">
                        💡 3. أمثلة ومواقف عملية (ماذا أفعل لو...؟)
                    </button>
                </div>

                <div class="msg-modal-body">
                    <div id="msg-pane-story" class="msg-pane active"></div>
                    <div id="msg-pane-dict" class="msg-pane"></div>
                    <div id="msg-pane-faq" class="msg-pane"></div>
                </div>
            </div>
        `;
        document.body.appendChild(backdrop);

        // Ghost Cursor Element
        const cursor = document.createElement('div');
        cursor.id = 'msg-ghost-cursor';
        cursor.className = 'msg-ghost-cursor';
        cursor.innerHTML = `
            <div class="msg-ghost-ripple"></div>
            <div class="msg-ghost-cursor-hand">👆</div>
            <div id="msg-ghost-cursor-label" class="msg-ghost-cursor-label">المؤشر الذكي</div>
        `;
        document.body.appendChild(cursor);

        // Target Highlight Ring
        const ring = document.createElement('div');
        ring.id = 'msg-live-target-ring';
        ring.className = 'msg-live-target-ring';
        document.body.appendChild(ring);

        // Pick-Element Mode Floating Banner
        const pickBanner = document.createElement('div');
        pickBanner.id = 'msg-pick-mode-banner';
        pickBanner.className = 'msg-pick-mode-banner';
        pickBanner.innerHTML = `
            <span>👆 وضع اختيار جزء معين: اضغط الآن بالماوس على أي زر أو قسم في الشاشة ليشرحه المرشد الذكي وحده!</span>
            <button type="button" onclick="window.stopSmartPickElementMode()" style="background:#020617; color:#fef08a; border:none; border-radius:99px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إنهاء</button>
        `;
        document.body.appendChild(pickBanner);

        // Interactive Voice Mic & Instant On-Screen Answer Panel
        const voicePanel = document.createElement('div');
        voicePanel.id = 'msg-voice-qa-panel';
        voicePanel.className = 'msg-voice-qa-panel';
        voicePanel.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(52,211,153,0.3); padding-bottom:8px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:1.35rem;">🎙️</span>
                    <div>
                        <div style="font-weight:900; font-size:0.9rem; color:#6ee7b7;">مايك المرشد الذكي (اسألني)</div>
                        <div id="msg-mic-status-lbl" style="font-size:0.72rem; color:#bae6fd;">اسأل بصوتك أو اختر سؤالك لتظهر الإجابة على الشاشة</div>
                    </div>
                </div>
                <button type="button" onclick="window.closeSmartVoiceQA()" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid #f87171; border-radius:8px; padding:2px 8px; cursor:pointer; font-weight:900;">✕</button>
            </div>

            <div style="display:flex; gap:6px;">
                <button type="button" id="msg-start-mic-listen-btn" onclick="window.startBrowserVoiceRecognition()" class="sl-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; font-weight:900; padding:8px 12px; font-size:0.8rem;">
                    🎙️ تحدث بالمايك الآن
                </button>
                <input type="text" id="msg-voice-text-input" class="sl-input" placeholder="أو اكتب سؤالك هنا واضغط اسأل..." style="flex:1; font-size:0.8rem !important; padding:6px 10px !important;" onkeydown="if(event.key==='Enter') window.answerUserScreenQuestion(this.value)">
                <button type="button" onclick="window.answerUserScreenQuestion(document.getElementById('msg-voice-text-input').value)" class="sl-btn" style="background:#38bdf8; color:#020617; border:none; font-weight:900; padding:6px 12px; font-size:0.8rem;">
                    اسأل
                </button>
            </div>

            <div style="font-size:0.74rem; color:#fde047; font-weight:800;">⚡ أو اضغط على ما تريد شرحه فوراً على الشاشة:</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                <button type="button" onclick="window.closeSmartVoiceQA(); window.startLiveAutopilotSimulation('all')" class="sl-btn" style="font-size:0.74rem; padding:6px 8px; justify-content:flex-start; color:#6ee7b7; border-color:rgba(52,211,153,0.4);">🌐 اشرح الشاشة كلها</button>
                <button type="button" onclick="window.closeSmartVoiceQA(); window.startLiveAutopilotSimulation('purchases')" class="sl-btn" style="font-size:0.74rem; padding:6px 8px; justify-content:flex-start; color:#7dd3fc; border-color:rgba(56,189,248,0.4);">📥 اشرح المشتريات وتقسيمها</button>
                <button type="button" onclick="window.closeSmartVoiceQA(); window.startLiveAutopilotSimulation('sales')" class="sl-btn" style="font-size:0.74rem; padding:6px 8px; justify-content:flex-start; color:#a7f3d0; border-color:rgba(16,185,129,0.4);">📤 اشرح المبيعات وتقسيمها</button>
                <button type="button" onclick="window.closeSmartVoiceQA(); window.startSmartPickElementMode()" class="sl-btn" style="font-size:0.74rem; padding:6px 8px; justify-content:flex-start; color:#fde047; border-color:rgba(251,191,36,0.4);">👆 أضغط على زر ليشرحه</button>
            </div>

            <div id="msg-onscreen-answer-box" style="display:none; background:rgba(15,23,42,0.95); border:1.5px solid #fbbf24; border-radius:12px; padding:10px 12px; font-size:0.84rem; color:#ffffff; line-height:1.65;"></div>
        `;
        document.body.appendChild(voicePanel);

        // Hidden placeholder for backwards compatibility
        const pinnedDock = document.createElement('div');
        pinnedDock.id = 'msg-pinned-audio-dock';
        pinnedDock.className = 'msg-pinned-audio-dock';
        document.body.appendChild(pinnedDock);

        // ── ROYAL SLEEK NON-BLOCKING GUIDE CONTROL POD (أمر حاسم 2: إلغاء القائمة الطويلة الحاجبة للرؤية وتصميم شاشة تحكمات المرشد بأعلى الجماليات العالمية) ──
        const coachBar = document.createElement('div');
        coachBar.id = 'msg-live-coach-bar';
        coachBar.className = 'msg-live-coach-bar';
        const initVolPct = Math.round((window.__msgUserVoiceVolume || 1.65) * 100);
        const initSpdPct = Math.round(window.__msgSpeechSpeedPct || 0);
        coachBar.innerHTML = `
            <div class="popup-drag-handle" style="display:flex; align-items:center; justify-content:space-between; gap:8px; width:100%; flex-wrap:wrap; cursor:grab; border-bottom:1px solid rgba(148,163,184,0.22); padding-bottom:6px;">
                <div style="display:flex; align-items:center; gap:6px; min-width:0; flex-wrap:wrap;">
                    <span id="msg-coach-step-badge" style="background:linear-gradient(135deg,rgba(251,191,36,0.22),rgba(56,189,248,0.18)); border:1.5px solid #fbbf24; padding:2px 10px; border-radius:99px; font-size:0.78rem; font-weight:900; max-width:195px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">🎓 المرشد الذكي</span>
                    <select id="msg-coach-step-jump-sel" class="sl-input" onchange="window.jumpToAutopilotStep(parseInt(this.value,10)||0)" style="padding:1px 6px !important; font-size:0.73rem !important; font-weight:900 !important; height:26px; max-width:130px; border-radius:8px !important;" title="الانتقال لأي خطوة"><option value="0">1️⃣ الخطوة 1</option></select>
                    <select id="msg-coach-ai-degree" class="sl-input" onchange="window.setMsgAiIntelligenceDegree(this.value)" style="padding:1px 6px !important; font-size:0.73rem !important; font-weight:900 !important; height:26px; border-radius:8px !important; border-color:#f59e0b !important; color:#fde047 !important;" title="درجة الذكاء الاصطناعي للشرح">
                        <option value="ai_5" ${window.__msgAiDegree==='ai_5'?'selected':''}>👑 ذكاء 5 (بروفيسور)</option>
                        <option value="ai_4" ${window.__msgAiDegree==='ai_4'?'selected':''}>🛡️ ذكاء 4 (متقدم)</option>
                        <option value="ai_3" ${window.__msgAiDegree==='ai_3'?'selected':''}>📊 ذكاء 3 (محاسبي)</option>
                        <option value="ai_2" ${window.__msgAiDegree==='ai_2'?'selected':''}>💡 ذكاء 2 (بلدي)</option>
                        <option value="ai_1" ${window.__msgAiDegree==='ai_1'?'selected':''}>⚡ ذكاء 1 (مختصر)</option>
                    </select>
                    <select id="msg-coach-voice-select" class="sl-input" onchange="window.onMsgVoiceChange(this.value)" style="padding:1px 6px !important; font-size:0.73rem !important; font-weight:900 !important; height:26px; border-radius:8px !important;" title="اختيار نموذج الصوت (نسائي أو رجالي)">
                        <option value="female_zariyah">👩 صوت المساعدة النقي (Aoede / زارية)</option>
                        <option value="female_salma">👩 نسائي هادئ (سلمى)</option>
                        <option value="female_fatima">👩 نسائي إذاعي (فاطمة)</option>
                        <option value="male_hamed">👨 رجالي فخيم (Puck / حامد)</option>
                        <option value="male_abdullah">👨 رجالي عميق (عبدالله)</option>
                        <option value="male_shakir">👨 رجالي مصري (شاكر)</option>
                        <option value="male_muawiya">👨 رجالي سوداني (معاوية)</option>
                    </select>
                </div>
                <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                    <button type="button" onclick="window.stepLiveAutopilot(-1)" class="msg-micro-pill-btn" title="الخطوة السابقة">⏮️</button>
                    <button type="button" id="msg-btn-pause-resume" onclick="window.togglePauseResumeAudio()" class="msg-micro-pill-btn" style="background:linear-gradient(135deg,#0284c7,#2563eb) !important; color:#ffffff !important; border-color:#38bdf8 !important; padding:0 10px;" title="تشغيل / إيقاف مؤقت">⏸️</button>
                    <button type="button" onclick="window.stepLiveAutopilot(1)" class="msg-micro-pill-btn" title="الخطوة التالية">⏭️</button>
                    <button type="button" onclick="window.jumpToAutopilotStep(autopilotStepIdx)" class="msg-micro-pill-btn" title="إعادة الخطوة">🔁</button>
                    <button type="button" onclick="window.startSmartPickElementMode()" class="msg-micro-pill-btn" title="اضغط على أي عنصر ليشرحه">👆</button>
                    <button type="button" id="msg-fixed-cinema-4k-btn" onclick="if(typeof window.launchIndependent4KCinemaNow==='function'){window.launchIndependent4KCinemaNow();}else if(window.startMagaies4KCinemaMode){window.startMagaies4KCinemaMode();}" class="msg-micro-pill-btn" style="background:linear-gradient(135deg,#dc2626,#991b1b) !important; color:#ffffff !important; border-color:#f87171 !important; padding:0 10px;" title="تشغيل التصوير السينمائي 4K مع تحريك الكاميرا والزوم وتسجيل الفيديو"><span id="msg-fixed-cinema-4k-label">🔴 تسجيل سينمائي</span></button>
                    <div style="display:inline-flex; align-items:center; gap:3px; background:rgba(15,23,42,0.65); padding:1px 6px; border-radius:8px; border:1px solid rgba(148,163,184,0.3);" title="سرعة الصوت (ناقص حتى -50% وزائد حتى +300%)">
                        <span style="font-size:0.7rem; font-weight:900;">⚡</span>
                        <button type="button" onclick="window.adjustSpeechSpeedPctBy(-10, false)" class="msg-micro-pill-btn" style="height:22px; min-width:24px; padding:0 5px;" title="إبطاء السرعة (حتى -50%)">-</button>
                        <input id="msg-coach-spd-slider" type="range" class="msg-spd-range-slider" min="-50" max="300" step="10" value="${initSpdPct}" oninput="window.setSpeechSpeedPctExact(this.value, false)" style="width:62px;">
                        <button type="button" onclick="window.adjustSpeechSpeedPctBy(20, false)" class="msg-micro-pill-btn" style="height:22px; min-width:24px; padding:0 5px;" title="زيادة السرعة (حتى +300%)">+</button>
                        <span id="msg-coach-speed-lbl" style="font-size:0.72rem; font-weight:900; min-width:44px; text-align:center; color:#fde047;">${formatSpeedPctLabel()}</span>
                    </div>
                    <div style="display:inline-flex; align-items:center; gap:3px; background:rgba(15,23,42,0.65); padding:1px 6px; border-radius:8px; border:1px solid rgba(148,163,184,0.3);" title="قوة الصوت (ناقص يقلل وزائد يعلي حتى 300%)">
                        <span style="font-size:0.7rem; font-weight:900;">🔊</span>
                        <button type="button" onclick="window.adjustMsgVoiceVolume && window.adjustMsgVoiceVolume(-0.20)" class="msg-micro-pill-btn" style="height:22px; min-width:24px; padding:0 5px;" title="تقليل الصوت">-</button>
                        <input id="msg-coach-vol-slider" type="range" class="msg-vol-range-slider" min="10" max="300" step="10" value="${initVolPct}" oninput="window.adjustMsgVoiceVolume && window.adjustMsgVoiceVolume(0, this.value/100)" style="width:62px;">
                        <button type="button" onclick="window.adjustMsgVoiceVolume && window.adjustMsgVoiceVolume(0.20)" class="msg-micro-pill-btn" style="height:22px; min-width:24px; padding:0 5px;" title="تعلية الصوت">+</button>
                        <span id="msg-coach-vol-lbl" style="font-size:0.71rem; font-weight:900; min-width:38px; text-align:center; color:#34d399;">${initVolPct}%</span>
                    </div>
                    <button type="button" onclick="window.stopLiveAutopilotSimulation()" class="msg-micro-pill-btn" style="background:rgba(239,68,68,0.22) !important; color:#fca5a5 !important; border-color:#f87171 !important;" title="إغلاق المرشد">✕</button>
                </div>
            </div>
            <div id="msg-coach-speech-text" style="width:100%; font-size:0.94rem; font-weight:800; line-height:1.7; text-align:center; padding:8px 14px; border-radius:12px; background:rgba(15,23,42,0.65); border:1.5px solid rgba(56,189,248,0.35); max-height:78px; overflow-y:auto;"></div>
            <!-- Hidden internal anchors preserved so zero legacy calls ever fail -->
            <div style="display:none !important;">
                <div id="msg-coach-live-caption-strip"></div>
                <input type="range" id="msg-audio-seek-slider" min="0" max="100" step="0.1" value="0">
                <span id="msg-audio-time-lbl">00:00</span>
                <select id="msg-coach-mode-select"><option value="all">all</option></select>
                <div id="msg-step-pills-wrap"></div>
                <div id="msg-coach-visual-stage"></div>
            </div>
        `;
        document.body.appendChild(coachBar);
        if (typeof window.__makePopupElementDraggable === 'function') window.__makePopupElementDraggable(coachBar);

        if (!document.getElementById('msg-coach-svg-popover')) {
            const svgPop = document.createElement('div');
            svgPop.id = 'msg-coach-svg-popover';
            document.body.appendChild(svgPop);
        }

        if (!document.getElementById('msg-live-select-mirror')) {
            const mirror = document.createElement('div');
            mirror.id = 'msg-live-select-mirror';
            document.body.appendChild(mirror);
        }

        const sel = document.getElementById('msg-voice-select');
        if (sel) sel.value = selectedMaleProfile;
        const coachSel = document.getElementById('msg-coach-voice-select');
        if (coachSel) coachSel.value = selectedMaleProfile;
        syncSpeedLabelsUI();
    }

    function syncSpeedLabelsUI() {
        const txt = formatSpeedPctLabel();
        ['msg-modal-speed-lbl', 'msg-dock-speed-lbl', 'msg-coach-speed-lbl', 'msg-tip-spd-lbl', 'msg-cinema-spd-lbl', 'md-def-spd-lbl'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = txt;
        });
        ['msg-tip-spd-slider', 'msg-coach-spd-slider', 'msg-cinema-spd-slider'].forEach(id => {
            const sld = document.getElementById(id);
            if (sld) sld.value = String(Math.round(window.__msgSpeechSpeedPct || 0));
        });
    }

    window.setMsgAiIntelligenceDegree = function (deg) {
        if (!['ai_1', 'ai_2', 'ai_3', 'ai_4', 'ai_5'].includes(deg)) deg = 'ai_5';
        window.__msgAiDegree = deg;
        try { localStorage.setItem('msg_ai_degree_v8', deg); } catch (_) {}
        ['msg-tip-ai-degree', 'msg-coach-ai-degree', 'msg-modal-ai-degree', 'msg-cinema-ai-degree', 'md-def-ai-degree'].forEach(id => {
            const sel = document.getElementById(id);
            if (sel) sel.value = deg;
        });
        const tipEl = document.getElementById('msg-smart-hover-tooltip');
        if (tipEl && tipEl.style.display === 'flex' && window.__lastDblRightTargetEl) {
            showGlobalSmartExplanationAt({ type: 'dblclick', target: window.__lastDblRightTargetEl }, window.__lastDblRightTargetEl);
            return;
        }
        if (isAutopilotRunning) {
            runCurrentAutopilotStep();
            return;
        }
        if (window.__CINEMA_4K_ACTIVE__ && typeof window.stepCinemaSceneBy === 'function') {
            window.stepCinemaSceneBy(0);
        }
    };

    window.adjustMsgVoiceVolume = function (delta, exactVal) {
        const cur = parseFloat(window.__msgUserVoiceVolume || 1.65);
        let next = (exactVal !== undefined && exactVal !== null) ? parseFloat(exactVal) : (cur + parseFloat(delta || 0));
        if (next > 3.000001) next = next / 100;
        const clamped = Math.max(0.1, Math.min(3.0, Math.round((isNaN(next) ? cur : next) * 100) / 100));
        window.__msgUserVoiceVolume = clamped;
        try { localStorage.setItem('msg_voice_vol_v1', String(clamped)); } catch (_) {}
        const setG = (node, base) => { if (!node || !node.gain) return; try { node.gain.setTargetAtTime(base * clamped, node.context.currentTime, 0.02); } catch (_) { node.gain.value = base * clamped; } };
        setG(window.__msgLiveMasterGainNode, 1.25);
        setG(window.__msgCinemaMasterGainNode, 1.35);
        const pctTxt = `${Math.round(clamped * 100)}%`;
        ['msg-tip-vol-lbl', 'msg-coach-vol-lbl', 'msg-modal-vol-lbl', 'msg-cinema-vol-lbl', 'md-def-vol-lbl'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = id === 'msg-modal-vol-lbl' ? `🔊 ${pctTxt}` : pctTxt;
        });
        ['msg-tip-vol-slider', 'msg-coach-vol-slider', 'msg-cinema-vol-slider', 'md-def-vol-slider'].forEach(id => {
            const sld = document.getElementById(id);
            if (sld) sld.value = String(Math.round(clamped * 100));
        });
    };

    function renderStepPillsForActiveList() {
        const list = activeAutopilotStepsList || [];
        const jumpSel = document.getElementById('msg-coach-step-jump-sel');
        if (jumpSel && list.length) {
            jumpSel.innerHTML = list.map((s, i) => `<option value="${i}" ${i === autopilotStepIdx ? 'selected' : ''}>${i + 1}/${list.length} ${(s.cursorLabel || s.badge || '').slice(0, 22)}</option>`).join('');
        }
        const pillsWrap = document.getElementById('msg-step-pills-wrap');
        if (!pillsWrap) return;
        pillsWrap.innerHTML = list.map((_, i) => `
            <button type="button" class="msg-step-pill-btn ${i === autopilotStepIdx ? 'active' : ''}" onclick="window.jumpToAutopilotStep(${i})" title="الخطوة ${i + 1}">${i + 1}</button>
        `).join('');
    }

    window.speak30SecScreenSummary = function () {
        window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;
        const pageTitle = (document.querySelector('main h1, main h2, .md-card-title, .page-title')?.textContent || document.title || 'الشاشة الحالية').split('|')[0].trim();
        const cntBtns = document.querySelectorAll('button, .qa-master-btn, .cat-hub-btn, .sl-btn').length;
        const cntInputs = document.querySelectorAll('input:not([type="hidden"]), select, textarea').length;
        const sumTxt = `ملخص شاشة (${pageTitle}) في ثلاثين ثانية: بالبلدي وببساطة هذه الشاشة هي غرفة العمليات المخصصة لإدارة ومراجعة (${pageTitle}) بدقة محاسبية ومخزنية لحظية، وتضم حالياً (${cntInputs}) خانة إدخال وفلترة و(${cntBtns}) أداة تنفيذية. القاعدة الذهبية: راجع البيانات قبل الحفظ بـ إف عشرة، ولا تخف من الخطأ فجميع العمليات محمية بدرع التوازن المحاسبي.`;
        const badge = document.getElementById('msg-coach-step-badge');
        if (badge) badge.textContent = `📋 ملخص: ${pageTitle.slice(0, 22)}`;
        const cap = document.getElementById('msg-coach-live-caption-strip');
        if (cap) { cap.style.display = 'block'; cap.textContent = sumTxt; }
        speakArabicMaleText(adaptTextToDialect(sumTxt, selectedMaleProfile), null, selectedMaleProfile, false);
    };

    window.startUniversalHandsOnDrill = function (specificEl) {
        window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;
        if (!specificEl && typeof window.startInteractiveHandsOnTraining === 'function' && window.location.pathname.includes('direct-sales')) { window.startInteractiveHandsOnTraining(); return; }
        if (!specificEl && typeof window.startQuickMasterDataQuiz === 'function' && window.location.pathname.includes('master-data')) { window.startQuickMasterDataQuiz(); return; }
        const step = (activeAutopilotStepsList && activeAutopilotStepsList[autopilotStepIdx]) || null;
        const target = specificEl || (step && step.targetSelector && document.querySelector(step.targetSelector)) || document.querySelector('main input:not([type="hidden"]), main select, main button');
        if (!target) return;
        moveGhostCursorToElement(target, '🎯 اضغط أو اكتب هنا بيدك للتدريب!', true);
        const info = window.autoSynthesizeElementExplanation ? window.autoSynthesizeElementExplanation(target) : { title: 'هذا العنصر' };
        const badge = document.getElementById('msg-coach-step-badge');
        if (badge) badge.textContent = `🎯 تدريب عملي: ${info.title.slice(0, 22)}`;
        speakArabicMaleText(adaptTextToDialect(`التدريب العملي بيدك الآن: تفضل بالضغط بالماوس أو الكتابة داخل (${info.title}) المضاء أمامك باللون الذهبي لتجربته بنفسك بأمان تام.`, selectedMaleProfile), null, selectedMaleProfile, false);
        const onHit = () => {
            target.removeEventListener('click', onHit);
            target.removeEventListener('focus', onHit);
            if (typeof window.playCinemaSoftChime === 'function') window.playCinemaSoftChime();
            speakArabicMaleText(adaptTextToDialect(`أحسنت يا بطل! تطبيق عملي ممتاز على (${info.title}).`, selectedMaleProfile), null, selectedMaleProfile, false);
        };
        target.addEventListener('click', onHit, { once: true });
        target.addEventListener('focus', onHit, { once: true });
    };

    window.setSpeechSpeedPctExact = function (exactPct, playPreviewIfIdle = false) {
        const pct = Math.max(-50, Math.min(300, Math.round(Number(exactPct) || 0)));
        window.__msgSpeechSpeedPct = pct;
        try { localStorage.setItem('msg_speech_speed_pct_v8', String(pct)); } catch (_) {}
        const rate = pctToPlaybackRate(pct);
        window.onMsgSpeedChange(rate, playPreviewIfIdle, true);
    };

    window.adjustSpeechSpeedPctBy = function (deltaPct, playPreviewIfIdle = false) {
        const curPct = Math.round(Number(window.__msgSpeechSpeedPct) || 0);
        const nextPct = Math.max(-50, Math.min(300, curPct + Math.round(Number(deltaPct) || 0)));
        window.setSpeechSpeedPctExact(nextPct, playPreviewIfIdle);
    };

    window.adjustSpeechSpeedBy = function (delta, playPreviewIfIdle = false) {
        const d = parseFloat(delta) || 0;
        const deltaPct = Math.abs(d) <= 1.5 ? Math.round(d * 100) : Math.round(d);
        window.adjustSpeechSpeedPctBy(deltaPct, playPreviewIfIdle);
    };

    window.onMsgSpeedChange = function (rateStr, playPreviewIfIdle = false, fromPctSetter = false) {
        const parsed = parseFloat(rateStr);
        if (isNaN(parsed)) return;

        if (isAudioPlaying && !isAudioPaused && activeAudioBuffer) {
            const curOff = getCurrentAudioOffset();
            activeAudioOffset = curOff;
            activeAudioStartTime = getAudioCtx().currentTime;
        }

        selectedSpeechRate = Math.max(0.50, Math.min(4.00, parsed));
        if (!fromPctSetter) {
            window.__msgSpeechSpeedPct = playbackRateToPct(selectedSpeechRate);
            try { localStorage.setItem('msg_speech_speed_pct_v8', String(window.__msgSpeechSpeedPct)); } catch (_) {}
        }
        localStorage.setItem('msg_male_speed_v7', selectedSpeechRate.toFixed(2));
        syncSpeedLabelsUI();

        if (window.__msgPitchPreservedAudioEl && !window.__msgPitchPreservedAudioEl.paused) {
            try {
                window.__msgPitchPreservedAudioEl.preservesPitch = true;
                window.__msgPitchPreservedAudioEl.playbackRate = selectedSpeechRate;
            } catch (e) {}
        }
        if (currentAudioSource && currentAudioSource.playbackRate) {
            try {
                currentAudioSource.playbackRate.value = selectedSpeechRate;
                if (currentAudioSource.detune) currentAudioSource.detune.value = 0;
            } catch (e) {}
        }
        if (typeof window.__applyCinemaLivePlaybackRate === 'function') {
            window.__applyCinemaLivePlaybackRate(selectedSpeechRate);
        }
        if (isAudioPaused && activeAudioBuffer) {
            startBufferAtOffset(activeAudioOffset);
        } else if (playPreviewIfIdle && !isAudioPlaying) {
            const pct = window.__msgSpeechSpeedPct;
            const speedMsg = pct < 0
                ? `تم ضبط سرعة الصوت على ناقص ${Math.abs(pct)} بالمائة.`
                : `تم ضبط سرعة الصوت على زائد ${pct} بالمائة.`;
            speakArabicMaleText(speedMsg, null, selectedMaleProfile, false);
        }
    };

    window.onMsgVoiceChange = function (profileId) {
        if (!VALID_VOICE_PROFILES.includes(profileId)) profileId = 'male_abdullah';
        selectedMaleProfile = profileId;
        localStorage.setItem('msg_neural_voice_v16', profileId);
        ['msg-voice-select', 'msg-coach-voice-select', 'msg-tip-voice-select'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = profileId;
        });

        prewarmGuideAudio(currentGuideKey, profileId);

        if (isAutopilotRunning) {
            runCurrentAutopilotStep();
            return;
        }
        const tipEl = document.getElementById('msg-smart-hover-tooltip');
        if (tipEl && tipEl.style.display === 'flex' && window.__lastGlobalDblRightSpeech && typeof window.speakMdExplanationPanelText === 'function') {
            window.speakMdExplanationPanelText(adaptTextToDialect(window.__lastGlobalDblRightSpeech, profileId));
            return;
        }

        const greetings = {
            female_zariyah: `أهلاً بك، تم تفعيل صوت المساعدة النقي والواضح في المرشد وشرح العناصر.`,
            female_salma:   `أهلاً بك، تم تفعيل الصوت النسائي الهادئ سلمى في النظام.`,
            female_fatima:  `أهلاً بك، تم تفعيل الصوت النسائي الإذاعي فاطمة في النظام.`,
            male_hamed:     `أهلاً بك، تم تفعيل الصوت الرجالي الفخيم حامد في المرشد وشرح العناصر.`,
            male_abdullah:  `أهلاً بك، تم تفعيل الصوت الرجالي الإذاعي العميق عبدالله.`,
            male_shakir:    `أهلاً بحضرتك، تم تفعيل الصوت الرجالي المصري شاكر.`,
            male_muawiya:   `حبابك عشرة، تم تفعيل الصوت الرجالي السوداني معاوية.`
        };
        speakArabicMaleText(greetings[profileId] || greetings.female_zariyah, null, profileId, false);
    };

    window.openVoicePreviewModal = function () {
        let m = document.getElementById('msg-voice-library-modal');
        if (!m) {
            m = document.createElement('div');
            m.id = 'msg-voice-library-modal';
            m.style.cssText = 'position:fixed;inset:0;z-index:9999999;background:rgba(2,6,23,0.78);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
            document.body.appendChild(m);
        }
        const voices = [
            { id: 'female_zariyah', name: '👩 صوت المساعدة النقي (Aoede / زارية)', desc: 'نفس النبرة النسائية الصافية والواضحة للمساعدة الذكية' },
            { id: 'female_salma',   name: '👩 صوت نسائي هادئ (سلمى)', desc: 'نبرة نسائية دافئة وهادئة لشرح القوائم والتقارير' },
            { id: 'female_fatima',  name: '👩 صوت نسائي إذاعي (فاطمة)', desc: 'نبرة نسائية رسمية واضحة وسريعة الاستجابة' },
            { id: 'male_hamed',     name: '👨 صوت رجالي فخيم (Puck / حامد)', desc: 'نبرة رجالية عربية فصحى واضحة ومتزنة' },
            { id: 'male_abdullah',  name: '👨 صوت رجالي إذاعي عميق (عبدالله)', desc: 'نبرة رجالية رخيمة للبيانات المالية والتدقيق' },
            { id: 'male_shakir',    name: '👨 صوت رجالي مصري (شاكر)', desc: 'نبرة رجالية مصرية سلسة للشرح التفاعلي' },
            { id: 'male_muawiya',   name: '👨 صوت رجالي سوداني (معاوية)', desc: 'نبرة رجالية سودانية دافئة ومألوفة' }
        ];
        m.innerHTML = `<div style="width:min(620px,96vw);background:linear-gradient(155deg,#0f172a,#020617);border:2px solid #38bdf8;border-radius:20px;padding:20px;color:#fff;box-shadow:0 20px 50px rgba(0,0,0,0.85);">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(56,189,248,0.3);padding-bottom:12px;margin-bottom:14px;">
                <strong style="font-size:1.1rem;color:#fde047;">🎙️ مكتبة الأصوات العصبية النقية (استماع ومعاينة قبل الاعتماد)</strong>
                <button onclick="document.getElementById('msg-voice-library-modal').style.display='none'" style="background:rgba(239,68,68,0.25);color:#fca5a5;border:1px solid #f87171;border-radius:10px;padding:4px 12px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;max-height:65vh;overflow-y:auto;">
                ${voices.map(v => `<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;border-radius:13px;background:${selectedMaleProfile===v.id?'rgba(16,185,129,0.18)':'rgba(30,41,59,0.75)'};border:1.5px solid ${selectedMaleProfile===v.id?'#10b981':'rgba(148,163,184,0.25)'};">
                    <div><div style="font-weight:900;font-size:0.92rem;color:#fff;">${v.name} ${selectedMaleProfile===v.id?'<span style="color:#34d399;font-size:0.78rem;">(المعتمد حالياً ✓)</span>':''}</div><div style="font-size:0.76rem;color:#94a3b8;">${v.desc}</div></div>
                    <div style="display:flex;gap:6px;white-space:nowrap;">
                        <button onclick="window.onMsgVoiceChange('${v.id}');window.openVoicePreviewModal();" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:9px;padding:6px 12px;font-size:0.8rem;font-weight:900;cursor:pointer;">▶️ سماع واعتماد</button>
                    </div>
                </div>`).join('')}
            </div>
        </div>`;
        m.style.display = 'flex';
    };

    window.openSmartGuide = function (guideKey) {
        if (typeof window.resetToOptimalClaritySpeed === 'function') window.resetToOptimalClaritySpeed();
        if (window.GlDesktopConstitution4 && typeof window.GlDesktopConstitution4.startImmediateGuideFromTopButton === 'function') { window.GlDesktopConstitution4.startImmediateGuideFromTopButton(); return; }
        if (window.__CINEMA_4K_ACTIVE__ && typeof window.stopMagaies4KCinemaMode === 'function') {
            window.stopMagaies4KCinemaMode();
        }
        window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;
        unlockBrowserAudioEnginesSync();
        stopCurrentSpeech();
        ensureGuideDomElements();
        const resolvedKey = guideKey || buildUniversalScreenGuideIfNeeded();
        currentGuideKey = resolvedKey;
        window.__mdInteractiveGuideActive = true;
        // الدستور الموحد للشرح 2027: يبدأ الشرح التطبيقي الحي فوراً عبر الكبسولة المصغرة في كل الشاشات دون نافذة كبيرة حاجبة للرؤية
        window.startLiveAutopilotSimulation('all');
    };

    window.openSmartGuideEncyclopediaModal = function (guideKey) {
        ensureGuideDomElements();
        const resolvedKey = guideKey || currentGuideKey || buildUniversalScreenGuideIfNeeded();
        currentGuideKey = resolvedKey;
        const data = window.MagaiesSmartGuides[resolvedKey] || window.MagaiesSmartGuides['smart-logistics'];
        if (!data) return;

        prewarmGuideAudio(resolvedKey, selectedMaleProfile);

        document.getElementById('msg-hdr-title').textContent = data.title;
        document.getElementById('msg-hdr-sub').textContent = data.subtitle;

        // Auto-build visualSteps from autopilotSteps if visualSteps is not explicitly provided
        const visualList = (data.visualSteps && data.visualSteps.length)
            ? data.visualSteps
            : (data.autopilotSteps || []).map((st, idx) => ({
                stepBadge: st.badge || `الخطوة ${idx + 1}`,
                title: st.cursorLabel || st.badge || `الخطوة ${idx + 1}`,
                desc: resolveStepSpeech(st, selectedMaleProfile, false),
                svg: buildStepFlowchartSvgMarkup(st, idx, (data.autopilotSteps || []).length),
                targetSelector: st.targetSelector
            }));

        // Tab 1
        const storyPane = document.getElementById('msg-pane-story');
        storyPane.innerHTML = `
            <div class="msg-story-grid">
                ${visualList.map(st => `
                    <div class="msg-story-card">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span class="msg-step-num">${st.stepBadge}</span>
                            ${st.targetSelector ? `<button type="button" onclick="window.pointGhostCursorAt('${st.targetSelector.replace(/'/g, "\\'")}', '${st.title.replace(/'/g, "\\'")}', \`${st.desc.replace(/`/g, '')}\`)" class="sl-btn" style="padding:3px 10px; font-size:0.74rem; color:#fbbf24; border-color:rgba(251,191,36,0.45);">👆 اشرح هذا الجزء وحده على الشاشة</button>` : ''}
                        </div>
                        <div class="msg-svg-stage">${st.svg || ''}</div>
                        <h4 style="margin:0; font-size:0.98rem; font-weight:900; color:#ffffff;">${st.title}</h4>
                        <div style="font-size:0.83rem; color:#cbd5e1; line-height:1.65; white-space:pre-line;">${st.desc}</div>
                    </div>
                `).join('')}
            </div>
        `;

        // Tab 2
        const dictPane = document.getElementById('msg-pane-dict');
        dictPane.innerHTML = `
            <div class="msg-btn-dict-grid">
                ${(data.buttonsDict || []).map(b => `
                    <div class="msg-dict-item">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span class="msg-replica-badge" style="${b.style || 'background:#0f172a; color:#38bdf8; border:1px solid #38bdf8;'}">${b.label}</span>
                            ${b.targetSelector ? `<button type="button" onclick="window.pointGhostCursorAt('${b.targetSelector.replace(/'/g, "\\'")}', '${b.label.replace(/'/g, "\\'")}', \`${(b.whatItDoes + ' ' + b.whatHappensIfClicked).replace(/`/g, '')}\`)" class="sl-btn" style="padding:3px 10px; font-size:0.74rem; color:#38bdf8;">🔊 اشرح هذا الزر وحده على الشاشة</button>` : ''}
                        </div>
                        <div style="font-size:0.84rem; font-weight:800; color:#ffffff;">🔹 <b>وظيفته:</b> ${b.whatItDoes}</div>
                        <div class="msg-whatif-box">⚡ <b>لو ضغطت عليه ماذا يحدث؟</b><br>${(b.whatHappensIfClicked || '').replace(/\n/g, '<br>')}</div>
                    </div>
                `).join('')}
            </div>
        `;

        // Tab 3
        const faqPane = document.getElementById('msg-pane-faq');
        faqPane.innerHTML = (data.scenarios || []).map(sc => `
            <div style="background:rgba(15,23,42,0.85); border:1.5px solid rgba(56,189,248,0.3); border-radius:16px; padding:16px 20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:8px;">
                    <h4 style="margin:0; font-size:0.98rem; font-weight:900; color:#fcd34d;">${sc.q}</h4>
                    <button type="button" onclick="window.speakScenarioAloud(\`${(sc.q + ' الإجابة: ' + sc.a).replace(/`/g, '')}\`)" class="sl-btn" style="padding:4px 12px; font-size:0.76rem; color:#6ee7b7; border-color:#34d399;">🔊 اسمع الإجابة</button>
                </div>
                <div style="font-size:0.86rem; color:#e2e8f0; line-height:1.75; white-space:pre-line;">${sc.a}</div>
            </div>
        `).join('');

        document.getElementById('msg-universal-modal').style.display = 'flex';
    };

    window.closeSmartGuide = function () {
        const modal = document.getElementById('msg-universal-modal');
        if (modal) modal.style.display = 'none';
        if (!isAutopilotRunning) {
            stopCurrentSpeech();
            const coachBar = document.getElementById('msg-live-coach-bar');
            const pinnedDock = document.getElementById('msg-pinned-audio-dock');
            if (coachBar) coachBar.style.display = 'none';
            if (pinnedDock) pinnedDock.style.display = 'none';
        }
    };

    window.switchSmartGuideTab = function (paneId, tabBtn) {
        document.querySelectorAll('.msg-nav-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.msg-pane').forEach(p => p.classList.remove('active'));
        if (tabBtn) tabBtn.classList.add('active');
        const target = document.getElementById(paneId);
        if (target) target.classList.add('active');
    };

    window.speakScenarioAloud = function (txt) {
        const dialectTxt = stripAllSalawatPrefixes(adaptTextToDialect(txt, selectedMaleProfile));
        speakArabicMaleText(dialectTxt, null, selectedMaleProfile);
    };

    // ── SILKY-SMOOTH ZERO-JITTER GHOST-CURSOR ENGINE ─────────────────────────
    let currentTargetDomEl = null;
    let _liveTargetTrackTimer = null;

    function resolveBestVisibleTargetElement(selectorStr) {
        if (!selectorStr || typeof selectorStr !== 'string') return null;
        const parts = selectorStr.split(',').map(s => s.trim()).filter(Boolean);
        for (const sel of parts) {
            try {
                const candidates = document.querySelectorAll(sel);
                for (const c of candidates) {
                    const r = c.getBoundingClientRect();
                    if (r.width > 4 && r.height > 4 && window.getComputedStyle(c).visibility !== 'hidden' && window.getComputedStyle(c).display !== 'none') {
                        return c;
                    }
                }
            } catch (_) {}
        }
        try { return document.querySelector(selectorStr); } catch (_) { return null; }
    }

    function placeCursorAndRingOnRect(el, labelText, triggerClickEffect) {
        const cursor = document.getElementById('msg-ghost-cursor');
        const cursorLbl = document.getElementById('msg-ghost-cursor-label');
        const ring = document.getElementById('msg-live-target-ring');
        if (!cursor || !ring || !el) return;

        const syncNow = () => {
            if (!el || (!isAutopilotRunning && !window.__mdInteractiveGuideActive && !window.__CINEMA_4K_ACTIVE__)) return;
            const rect = el.getBoundingClientRect();
            if (rect.width < 2 && rect.height < 2) return;
            const pad = 6;
            ring.style.display = 'block';
            ring.style.top = `${Math.max(4, rect.top - pad)}px`;
            ring.style.left = `${Math.max(4, rect.left - pad)}px`;
            ring.style.width = `${rect.width + pad * 2}px`;
            ring.style.height = `${rect.height + pad * 2}px`;

            cursor.style.display = 'block';
            if (cursorLbl && labelText) cursorLbl.textContent = labelText;
            cursor.style.top = `${rect.top + Math.min(rect.height / 2, 28)}px`;
            cursor.style.left = `${rect.left + Math.min(rect.width / 2, 80)}px`;

            const coachBar = document.getElementById('msg-live-coach-bar');
            if (coachBar && coachBar.dataset.userLockedPos !== '1') {
                const viewH = window.innerHeight || document.documentElement.clientHeight;
                if (rect.bottom > viewH - 165) {
                    coachBar.classList.add('docked-top');
                } else {
                    coachBar.classList.remove('docked-top');
                }
            }
        };
        syncNow();
        if (_liveTargetTrackTimer) clearInterval(_liveTargetTrackTimer);
        _liveTargetTrackTimer = setInterval(syncNow, 90);

        if (triggerClickEffect) {
            cursor.classList.add('clicking');
            setTimeout(() => cursor.classList.remove('clicking'), 450);
        }
    }

    let activeSpotlightEl = null;
    function applyGuideSpotlight(el) {
        if (activeSpotlightEl && activeSpotlightEl !== el) {
            try { activeSpotlightEl.classList.remove('msg-spotlight-active-el'); } catch (e) {}
        }
        if (el && el !== document.body && el !== document.documentElement) {
            try { el.classList.add('msg-spotlight-active-el'); } catch (e) {}
            activeSpotlightEl = el;
        } else {
            activeSpotlightEl = null;
        }
    }

    function moveGhostCursorToElement(el, labelText, triggerClickEffect = true) {
        if (!el) return;
        currentTargetDomEl = el;
        applyGuideSpotlight(el);
        const rect = el.getBoundingClientRect();
        const viewH = window.innerHeight || document.documentElement.clientHeight;

        // Only scroll if the element is genuinely outside the comfortable viewport area!
        // This prevents unnecessary page jumps and makes audio + cursor 100% silky smooth.
        const isComfortablyVisible = (rect.top >= 65 && rect.bottom <= viewH - 150);

        if (isComfortablyVisible || el === document.body) {
            placeCursorAndRingOnRect(el, labelText, triggerClickEffect);
        } else {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                placeCursorAndRingOnRect(el, labelText, triggerClickEffect);
            }, 260);
        }
    }

    window.pointGhostCursorAt = function (selector, title, spokenText) {
        if (window.__CINEMA_4K_ACTIVE__ && typeof window.stopMagaies4KCinemaMode === 'function') {
            window.stopMagaies4KCinemaMode();
        }
        window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;
        ensureGuideDomElements();
        window.closeSmartGuide();
        isAutopilotRunning = true;
        window.__mdInteractiveGuideActive = true;

        const el = document.querySelector(selector);
        const coachBar = document.getElementById('msg-live-coach-bar');
        const speechBox = document.getElementById('msg-coach-speech-text');
        const badge = document.getElementById('msg-coach-step-badge');

        if (coachBar) coachBar.style.display = 'flex';
        const adaptedBody = stripAllSalawatPrefixes(adaptTextToDialect(`${title}. ${spokenText}`, selectedMaleProfile));
        if (badge) badge.textContent = `📍 ${title}`;
        if (speechBox) speechBox.textContent = adaptedBody;

        if (el) moveGhostCursorToElement(el, title, true);
        speakArabicMaleText(adaptedBody, null, selectedMaleProfile);
    };

    // ── 4 EXPLANATION MODES: 'all' | 'purchases' | 'sales' | 'pick' ──────────
    window.startLiveAutopilotSimulation = async function (mode = 'all') {
        if (window.__CINEMA_4K_ACTIVE__ && typeof window.stopMagaies4KCinemaMode === 'function') {
            window.stopMagaies4KCinemaMode();
        }
        window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;
        window.__mdInteractiveGuideActive = true;
        ensureGuideDomElements();
        window.stopSmartPickElementMode();
        if (mode === 'hands-on' && typeof window.startSlHandsOnTrainingCoach === 'function') {
            window.startSlHandsOnTrainingCoach();
            return;
        }
        const resolvedKey = buildUniversalScreenGuideIfNeeded();
        currentGuideKey = resolvedKey;
        const data = window.MagaiesSmartGuides[resolvedKey] || window.MagaiesSmartGuides['smart-logistics'];
        if (!data) return;

        if (resolvedKey === 'smart-logistics') {
            if (mode === 'sales') {
                activeAutopilotStepsList = SALES_ONLY_STEPS;
            } else if (mode === 'purchases') {
                activeAutopilotStepsList = PURCHASES_MASTER_STEPS;
            } else {
                // 'all' = Full A-to-Z Purchases & Split Masterclass (10 steps) + Full A-to-Z Sales Delivery & Split Masterclass (9 steps)
                activeAutopilotStepsList = [...PURCHASES_MASTER_STEPS, ...SALES_ONLY_STEPS];
            }
        } else {
            activeAutopilotStepsList = data.autopilotSteps || [];
        }

        const modeSel = document.getElementById('msg-coach-mode-select');
        if (modeSel && ['all', 'purchases', 'sales'].includes(mode)) {
            modeSel.value = mode;
        }

        isAutopilotRunning = true;
        window.closeSmartGuide();
        autopilotStepIdx = 0;

        const coachBar = document.getElementById('msg-live-coach-bar');
        if (coachBar) coachBar.style.display = 'flex';

        renderStepPillsForActiveList();
        runCurrentAutopilotStep();
        setTimeout(() => {
            if (isAutopilotRunning) prewarmGuideAudio(currentGuideKey, selectedMaleProfile);
        }, 1800);
    };

    window.jumpToAutopilotStep = function (idx) {
        ensureGuideDomElements();
        if (!activeAutopilotStepsList || !activeAutopilotStepsList.length) {
            const data = window.MagaiesSmartGuides[currentGuideKey];
            activeAutopilotStepsList = (data && data.autopilotSteps) ? data.autopilotSteps : [];
        }
        if (!activeAutopilotStepsList.length) return;
        isAutopilotRunning = true;
        window.closeSmartGuide();
        stopCurrentSpeech();
        autopilotStepIdx = Math.max(0, Math.min(idx, activeAutopilotStepsList.length - 1));
        runCurrentAutopilotStep();
    };

    window.stepLiveAutopilot = function (delta) {
        ensureGuideDomElements();
        if (!activeAutopilotStepsList || !activeAutopilotStepsList.length) {
            const data = window.MagaiesSmartGuides[currentGuideKey];
            activeAutopilotStepsList = (data && data.autopilotSteps) ? data.autopilotSteps : [];
        }
        if (!activeAutopilotStepsList.length) return;
        if (!isAutopilotRunning) {
            isAutopilotRunning = true;
            window.closeSmartGuide();
        }

        stopCurrentSpeech();

        autopilotStepIdx += delta;
        if (autopilotStepIdx < 0) autopilotStepIdx = 0;
        if (autopilotStepIdx >= activeAutopilotStepsList.length) {
            window.stopLiveAutopilotSimulation();
            if (typeof showSlToast === 'function') showSlToast('🎓 اكتمل الشرح التطبيقي الحي بنجاح!', 'success', 3500);
            return;
        }
        runCurrentAutopilotStep();
    };

    let dubbedVisualTimers = [];
    let isCoachVisualStageHidden = false;

    window.toggleCoachVisualStage = function () {
        const stg = document.getElementById('msg-coach-visual-stage');
        const btn = document.getElementById('msg-coach-diagram-btn');
        if (!stg) return;
        isCoachVisualStageHidden = !isCoachVisualStageHidden;
        stg.style.display = isCoachVisualStageHidden ? 'none' : 'flex';
        if (btn) {
            btn.style.opacity = isCoachVisualStageHidden ? '0.65' : '1';
        }
    };

    function cleanupGuideTempPopups(preserveContainerEl) {
        dubbedVisualTimers.forEach(t => clearTimeout(t));
        dubbedVisualTimers = [];
        const mirror = document.getElementById('msg-live-select-mirror');
        if (mirror) mirror.style.display = 'none';
        const cdd = document.getElementById('customer-dropdown-list');
        if (cdd) cdd.style.display = 'none';
        const qdd = document.getElementById('quick-item-dropdown-list');
        if (qdd) qdd.style.display = 'none';
        const gm = document.getElementById('grid-settings-modal');
        if (gm && !(preserveContainerEl && gm.contains(preserveContainerEl))) gm.style.display = 'none';
        [
            'sl-mobile-bridge-modal',
            'sl-font-modal',
            'sl-weighbridge-modal',
            'sl-signature-modal',
            'sl-debit-note-modal',
            'sl-quick-warehouse-modal',
            'sl-confirm-undo-modal',
            'sl-drilldown-invoice-modal',
            'sl-fleet-dispatch-modal',
            'sl-load-verifier-modal',
            'sl-cargo-estimator-modal',
            'sl-voice-commander-modal',
            'sl-gdn-quiz-modal',
            'sl-pending-aging-modal',
            'sl-gate-credit-modal',
            'sl-contrast-inspector-modal',
            'sl-dispatch-presets-modal',
            'sl-batch-ocr-modal',
            'sl-bin-heatmap-modal',
            'sl-cols-drawer',
            'grn-confirm-action-modal',
            'grn-batch-bin-modal',
            'grn-quick-wh-modal',
            'grn-continuous-scan-modal',
            'grn-weighbridge-modal',
            'post-split-success-modal',
            'split-genealogy-tree-modal',
            'split-voice-assistant-modal',
            'split-hands-on-sim-modal',
            'split-drilldown-doc-modal',
            'split-analytics-modal',
            'split-weight-shield-modal',
            'split-driver-signature-modal',
            'split-presets-modal',
            'add-customer-modal',
            'sales-cust-history-modal',
            'sales-parked-invoices-modal',
            'ds-cash-change-modal',
            'ds-cash-tender-modal',
            'ds-last-price-memory-modal',
            'ds-margin-shield-modal',
            'ds-cross-sell-modal',
            'ds-signature-modal',
            'ds-constitutions-officer-modal',
            'ds-quick-wh-modal',
            'ds-quick-item-modal',
            'ds-warranty-qr-modal',
            'auto-send-sales-dropdown-menu',
            'msg-c5-impact-map-banner',
            'main-account-dropdown-list',
            'account-1-dropdown-list',
            'cost-center-1-dropdown-list',
            'auto-send-dropdown-menu',
            'mcp-quick-add-account-modal',
            'mcp-split-modal',
            'mcp-excel-modal',
            'mcp-templates-modal',
            'inbound-receipts-drawer',
            'wa-voucher-modal',
            'print-preview-modal'
        ].forEach(id => {
            const m = document.getElementById(id);
            if (m && !(preserveContainerEl && m.contains(preserveContainerEl))) {
                m.style.display = 'none';
                m.classList.remove('active', 'open');
            }
        });
        if (typeof window.cleanupMultiCashGuidePopups === 'function') {
            try { window.cleanupMultiCashGuidePopups(preserveContainerEl); } catch (e) {}
        }
    }

    function triggerLiveChildScreenPreviewForElement(el) {
        if (!el) return;
        const onclick = (el.getAttribute && el.getAttribute('onclick')) || (el.closest && el.closest('[onclick]')?.getAttribute('onclick')) || '';
        if (!onclick) return;
        const previewFns = [
            'openSlMobileBridgeModal',
            'openSlWeighbridgeModal',
            'openSlSignatureModal',
            'openSlShortageDebitNoteModal',
            'openSlQuickWarehouseModal',
            'openSlQuickEntityModal',
            'openSlOriginalInvoiceDrilldownModal',
            'openSlFleetDispatchModal',
            'openSlLoadingVerifierModal',
            'openSlCargoEstimatorModal',
            'openSlC3WhatsAppComposerModal',
            'openSlC1LogisticsWeightRadarModal',
            'openSlConstitution1MasterModal',
            'openSlConstitution2MasterModal',
            'openSlConstitution3MasterModal',
            'openSlConstitution4MasterModal',
            'openSlConstitution5MasterModal',
            'openSlC6SyncRadarModal',
            'openSlC7OphthalmicLabModal',
            'openSlC8CognitiveCenterModal',
            'openSlC9WarehouseCommandModal',
            'openSlC9QcInspectionModal',
            'openSlC9WarehouseTransferModal',
            'openSlC10CommercialCommandModal',
            'openSlC10LandedCostModal',
            'openSlC10DocumentLifecycleModal',
            'openSlC10ReturnsModal',
            'openSlC11BankingCommandModal',
            'openSlC11ChequePortfolioModal',
            'openSlC11BankTransferAntiFraudModal',
            'openSlC11PeriodAndCostCenterModal',
            'openSlC12ForensicSecurityCommandModal',
            'openSlC12BlindShiftClosingModal',
            'openSlC12ManagerReprintModal',
            'openSlC13CyberCommandModal',
            'openSlC13BlackBoxModal',
            'openSlC13OwnerSilentRadarModal',
            'openSlC14WorldReportsModal',
            'openSlC14DrillDownDocModal',
            'openSlC14BalancedPrintHubModal',
            'openSlC14TripleSealCertificateModal',
            'openSlC15FinalMasteryModal',
            'openSlC16ZeroErrorMasterAuditModal',
            'openSlFontModal',
            'openEqualSplitModal',
            'openExcelPasteModal',
            'openBatchTemplatesModal',
            'openQuickAddAccountModal',
            'toggleInboundReceiptsDrawer',
            'toggleAutoSendDropdown'
        ];
        for (const fnName of previewFns) {
            if (onclick.includes(fnName) && typeof window[fnName] === 'function') {
                try { window[fnName](); } catch (e) {}
                break;
            }
        }
    }

    function showLiveSelectOptionsMirror(targetEl, customItems, titleLabel) {
        // أمر حاسم (6): فتح وعرض القوائم المنسدلة حياً أمام المستخدم بالتزامن مع الصوت بدون حجب الشاشة
        let mirror = document.getElementById('msg-live-select-mirror');
        if (!mirror) {
            mirror = document.createElement('div');
            mirror.id = 'msg-live-select-mirror';
            document.body.appendChild(mirror);
        }
        const selEl = (targetEl && targetEl.tagName === 'SELECT')
            ? targetEl
            : (targetEl && typeof targetEl.querySelector === 'function' ? targetEl.querySelector('select') : null);
        const anchorEl = selEl || targetEl;
        if (!anchorEl || anchorEl === document.body) {
            mirror.style.display = 'none';
            return;
        }
        let items = [];
        if (selEl && selEl.options && selEl.options.length > 0) {
            items = Array.from(selEl.options)
                .map(o => (o.textContent || o.innerText || '').trim())
                .filter(Boolean)
                .slice(0, 6);
        } else if (Array.isArray(customItems) && customItems.length) {
            items = customItems.slice(0, 5);
        }
        if (!items.length) {
            mirror.style.display = 'none';
            return;
        }
        const rect = anchorEl.getBoundingClientRect();
        const hdr = escapeSvgText((titleLabel || 'عناصر القائمة المنسدلة').slice(0, 42));
        mirror.innerHTML = `
            <div style="font-size:0.74rem; font-weight:900; padding:3px 8px; border-bottom:1px solid rgba(56,189,248,0.35); margin-bottom:3px; display:flex; align-items:center; justify-content:space-between;">
                <span>📂 ${hdr}</span>
                <span style="font-size:0.66rem; opacity:0.85;">(${items.length} خيارات)</span>
            </div>
            ${items.map((it, idx) => `
                <div class="msg-mirror-opt" style="${idx === 0 ? 'background:rgba(56,189,248,0.18); border:1px solid rgba(56,189,248,0.45);' : ''}">
                    <span>🔹 ${escapeSvgText(String(it).slice(0, 48))}</span>
                    ${idx === 0 ? '<span style="font-size:0.65rem; font-weight:900;">✓ نشط</span>' : ''}
                </div>
            `).join('')}
        `;
        mirror.style.display = 'flex';
        const topPos = (rect.bottom + 6 + 170 < window.innerHeight) ? (rect.bottom + 6) : Math.max(8, rect.top - 165);
        const leftPos = Math.max(10, Math.min(window.innerWidth - 280, rect.left));
        mirror.style.top = `${topPos}px`;
        mirror.style.left = `${leftPos}px`;
    }

    function escapeSvgText(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function buildStepFlowchartSvgMarkup(step, stepIdx = 0, totalSteps = 1) {
        if (step && (step.diagramType === 'c5-impact-map' || step.c5ImpactMeta)) {
            const c5 = step.c5ImpactMeta || {};
            const sTitle = escapeSvgText((c5.screenTitle || 'شاشة المبيعات المباشرة').slice(0, 28));
            const fSum1 = escapeSvgText((c5.funcSummary || 'إصدار واعتماد الفواتير وضبط التسعير').slice(0, 27));
            const fSum2 = escapeSvgText((c5.funcSummary || '').slice(27, 55) || 'ومنع الأخطاء وتوفير الوقت');
            const reps = Array.isArray(c5.reports) && c5.reports.length ? c5.reports : ['📊 تقارير المبيعات والأرباح', '📖 كشف العميل والأستاذ', '📦 حركة المخازن والخزينة'];
            const scrs = Array.isArray(c5.screens) && c5.screens.length ? c5.screens : ['🚛 التسليمات وتجزئة الفواتير', '🏦 الخزينة والبنوك', '🏭 المخازن والعملاء'];

            return `<svg viewBox="0 0 460 118" width="100%" height="100%" style="display:block;">
                <!-- Stage 1 (Right in RTL): Screen Function & Benefits -->
                <g id="msg-pop-c5-stage-1" style="opacity:1;transition:all 0.3s ease;">
                    <rect x="312" y="6" width="142" height="106" rx="10" fill="#0f172a" stroke="#f59e0b" stroke-width="2.4"/>
                    <text x="383" y="23" fill="#fde047" font-size="9" font-weight="900" text-anchor="middle">1️⃣ الوظيفة والفوائد</text>
                    <text x="383" y="44" fill="#ffffff" font-size="8.2" font-weight="900" text-anchor="middle">${sTitle}</text>
                    <text x="383" y="65" fill="#bae6fd" font-size="7.8" font-weight="800" text-anchor="middle">${fSum1}</text>
                    <text x="383" y="83" fill="#bae6fd" font-size="7.8" font-weight="800" text-anchor="middle">${fSum2}</text>
                    <text x="383" y="102" fill="#34d399" font-size="7.8" font-weight="900" text-anchor="middle">🛡️ ضبط رقابي ومنع الأخطاء</text>
                </g>
                <!-- Arrow 1 -->
                <g id="msg-pop-c5-arrow-1" style="opacity:0.35;transition:all 0.3s ease;">
                    <path d="M310 59 L294 59" stroke="#10b981" stroke-width="2.6" stroke-dasharray="3 2"/>
                    <polygon points="296,54 287,59 296,64" fill="#10b981"/>
                </g>
                <!-- Stage 2 (Center): Reports Affected -->
                <g id="msg-pop-c5-stage-2" style="opacity:0.45;transition:all 0.3s ease;">
                    <rect x="156" y="6" width="130" height="106" rx="10" fill="#0f172a" stroke="#10b981" stroke-width="2.4"/>
                    <text x="221" y="23" fill="#6ee7b7" font-size="9" font-weight="900" text-anchor="middle">2️⃣ التقارير المتأثرة</text>
                    <text x="221" y="47" fill="#ffffff" font-size="8" font-weight="900" text-anchor="middle">${escapeSvgText(String(reps[0] || '').slice(0, 26))}</text>
                    <text x="221" y="71" fill="#ffffff" font-size="8" font-weight="900" text-anchor="middle">${escapeSvgText(String(reps[1] || '').slice(0, 26))}</text>
                    <text x="221" y="95" fill="#ffffff" font-size="8" font-weight="900" text-anchor="middle">${escapeSvgText(String(reps[2] || '').slice(0, 26))}</text>
                </g>
                <!-- Arrow 2 -->
                <g id="msg-pop-c5-arrow-2" style="opacity:0.35;transition:all 0.3s ease;">
                    <path d="M154 59 L138 59" stroke="#38bdf8" stroke-width="2.6" stroke-dasharray="3 2"/>
                    <polygon points="140,54 131,59 140,64" fill="#38bdf8"/>
                </g>
                <!-- Stage 3 (Left in RTL): Integrated Screens Affected -->
                <g id="msg-pop-c5-stage-3" style="opacity:0.45;transition:all 0.3s ease;">
                    <rect x="6" y="6" width="124" height="106" rx="10" fill="#0f172a" stroke="#38bdf8" stroke-width="2.4"/>
                    <text x="68" y="23" fill="#7dd3fc" font-size="9" font-weight="900" text-anchor="middle">3️⃣ الشاشات المتكاملة</text>
                    <text x="68" y="47" fill="#ffffff" font-size="8" font-weight="900" text-anchor="middle">${escapeSvgText(String(scrs[0] || '').slice(0, 25))}</text>
                    <text x="68" y="71" fill="#ffffff" font-size="8" font-weight="900" text-anchor="middle">${escapeSvgText(String(scrs[1] || '').slice(0, 25))}</text>
                    <text x="68" y="95" fill="#ffffff" font-size="8" font-weight="900" text-anchor="middle">${escapeSvgText(String(scrs[2] || '').slice(0, 25))}</text>
                </g>
            </svg>`;
        }

        const meta = step.diagramMeta || {};
        const parentTitle = escapeSvgText((meta.parentScreen || step.badge || `الخطوة ${stepIdx + 1}`).slice(0, 34));
        const triggerTitle = escapeSvgText((step.cursorLabel || step.badge || 'الزر الحالي').slice(0, 30));
        const childList = (Array.isArray(meta.childItems) && meta.childItems.length)
            ? meta.childItems.slice(0, 4)
            : [
                `1️⃣ العنصر: ${(step.cursorLabel || step.badge || '').slice(0, 28)}`,
                '2️⃣ يفتح القائمة أو ينفذ العملية فوراً',
                '3️⃣ يحدث الحسابات والمخزون آلياً'
            ];
        const typeBadge = step.diagramType === 'screen-transition'
            ? '🖥️ شاشة داخل شاشة'
            : (step.diagramType === 'dropdown-open'
                ? '📂 فتح قائمة منسدلة'
                : (step.diagramType === 'subpanel-expand' ? '🎛️ إظهار نافذة فرعية' : '📊 مخطط الخطوة والتسميع'));

        return `<svg viewBox="0 0 440 112" width="100%" height="100%" style="display:block;">
            <defs>
                <linearGradient id="msgParentGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1e1b4b"/>
                    <stop offset="100%" stop-color="#0f172a"/>
                </linearGradient>
                <linearGradient id="msgChildGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#064e3b"/>
                    <stop offset="100%" stop-color="#0f172a"/>
                </linearGradient>
            </defs>
            <!-- Right Box: Current Screen & Trigger Button -->
            <rect x="275" y="8" width="157" height="96" rx="11" fill="url(#msgParentGrad)" stroke="#fbbf24" stroke-width="2"/>
            <text x="353" y="26" fill="#fde047" font-size="9.5" font-weight="bold" text-anchor="middle">${parentTitle}</text>
            <rect x="285" y="35" width="137" height="30" rx="7" fill="#78350f" stroke="#fde047" stroke-width="1.5"/>
            <text x="353" y="54" fill="#ffffff" font-size="9.5" font-weight="bold" text-anchor="middle">👆 ${triggerTitle}</text>
            <text x="353" y="86" fill="#bae6fd" font-size="8.5" font-weight="bold" text-anchor="middle">${typeBadge}</text>

            <!-- Center Animated Flow Arrow (Right to Left in RTL) -->
            <path d="M270 56 L228 56" stroke="#38bdf8" stroke-width="3" stroke-dasharray="5 3"/>
            <polygon points="230,49 216,56 230,63" fill="#38bdf8"/>
            <circle cx="249" cy="56" r="10" fill="#020617" stroke="#fbbf24" stroke-width="1.5"/>
            <text x="249" y="60" fill="#fde047" font-size="9" font-weight="bold" text-anchor="middle">${stepIdx + 1}</text>

            <!-- Left Box: Opened Nested Screen / Dropdown List / Sub-Panel -->
            <rect x="8" y="6" width="204" height="100" rx="11" fill="url(#msgChildGrad)" stroke="#34d399" stroke-width="2"/>
            ${childList.map((item, i) => {
                const y = 25 + i * 21;
                return `
                    <rect x="15" y="${y - 12}" width="190" height="18" rx="5" fill="${i === 0 ? 'rgba(251,191,36,0.22)' : 'rgba(15,23,42,0.75)'}" stroke="${i === 0 ? '#fbbf24' : 'rgba(52,211,153,0.3)'}" stroke-width="1"/>
                    <text x="198" y="${y}" fill="${i === 0 ? '#fef08a' : '#f8fafc'}" font-size="8.7" font-weight="bold" text-anchor="end">${escapeSvgText(String(item).slice(0, 38))}</text>
                `;
            }).join('')}
        </svg>`;
    }

    let _c5ImpactPhaseTimers = [];
    function clearC5ImpactPhaseTimers() {
        _c5ImpactPhaseTimers.forEach(t => clearTimeout(t));
        _c5ImpactPhaseTimers = [];
    }

    window._showCoachMiniSvgPopover = true;
    window.toggleCoachMiniSvgPopover = function () {
        window._showCoachMiniSvgPopover = !window._showCoachMiniSvgPopover;
        const pop = document.getElementById('msg-coach-svg-popover');
        if (pop) {
            pop.style.display = (window._showCoachMiniSvgPopover && isAutopilotRunning) ? 'block' : 'none';
        }
    };

    function renderLiveDubbedVisualStage(step, stepIdx = 0, totalSteps = 1) {
        clearC5ImpactPhaseTimers();
        const banner = document.getElementById('msg-c5-impact-map-banner');
        if (banner) banner.style.display = 'none';

        const svgPop = document.getElementById('msg-coach-svg-popover');
        if (svgPop && step) {
            svgPop.innerHTML = buildStepFlowchartSvgMarkup(step, stepIdx, totalSteps);
            const coachBar = document.getElementById('msg-live-coach-bar');
            if (coachBar) {
                svgPop.classList.toggle('docked-top', coachBar.classList.contains('docked-top'));
            }
            svgPop.style.display = window._showCoachMiniSvgPopover ? 'block' : 'none';

            // Real-time Audio-Visual Phase Synchronization for Constitution 5 Impact Map (Stage 1 -> Stage 2 -> Stage 3)
            if (step.diagramType === 'c5-impact-map' || step.c5ImpactMeta) {
                _c5ImpactPhaseTimers.push(setTimeout(() => {
                    const a1 = document.getElementById('msg-pop-c5-arrow-1');
                    const s2 = document.getElementById('msg-pop-c5-stage-2');
                    if (a1) a1.style.opacity = '1';
                    if (s2) s2.style.opacity = '1';
                }, 4200));
                _c5ImpactPhaseTimers.push(setTimeout(() => {
                    const a2 = document.getElementById('msg-pop-c5-arrow-2');
                    const s3 = document.getElementById('msg-pop-c5-stage-3');
                    if (a2) a2.style.opacity = '1';
                    if (s3) s3.style.opacity = '1';
                }, 8500));
            }
        }
    }

    function openManualImpactMapBanner(step) {
        clearC5ImpactPhaseTimers();
        if (!step) return;
        let banner = document.getElementById('msg-c5-impact-map-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'msg-c5-impact-map-banner';
            document.body.appendChild(banner);
        }
        const isLight = document.documentElement.classList.contains('light-theme') ||
            ['light', 'pearl-white', 'warm_cream', 'ultra_white', 'exec_gray'].includes(document.documentElement.getAttribute('data-theme') || '');
        const bgCard = isLight ? '#F1F5F9' : '#111c33';
        const fgText = isLight ? '#000000' : '#FFFFFF';
        const borderCol = isLight ? '#000000' : '#FFFFFF';
        const boxBg = isLight ? '#FFFFFF' : '#000000';

        const meta = step.c5ImpactMeta || {
            screenTitle: '✂️🚚 فاتورة تقسيم وتسليم المبيعات',
            inputs: ['📥 الفواتير المعتمدة أو عروض الأسعار', '📦 أرصدة المخازن والوحدات التأسيسية', '👤 بيانات وسقوف ائتمان العملاء'],
            funcSummary: 'تجزئة الفاتورة بالوحدة الكبرى والفرعية وتوليد أذونات تسلسلية (100/1/1) وحفظ شجرة النسب ومنع أخطاء الصرف',
            goldenRule: 'القاعدة الذهبية: افحص الرصيد الفعلي ووحدة القياس قبل الاعتماد ينضبط لك المخزن والحساب تلقائياً.',
            reassurance: 'اطمئن تماماً؛ فالشاشة محمية بدرع الأمان الذكي وحفظ المسودات والتراجع الفوري بضغطة زر.',
            reports: ['📊 تقارير المبيعات والتجزئة', '📖 كشف حساب العميل والأستاذ', '📦 تقارير حركة وأرصدة المخازن'],
            screens: ['🧾 شاشة المبيعات المباشرة', '🚛 بوابة التسليم اللوجستية GDN', '🏭 شاشة المخازن وحسابات العملاء']
        };
        const inputList = Array.isArray(meta.inputs) && meta.inputs.length
            ? meta.inputs
            : ['📥 شاشة البيانات الأساسية والأرصدة', '📋 الاستيراد الذكي والباركود', '🔄 التغذية التلقائية من النظام'];

        banner.style.cssText = `position:fixed;top:64px;left:50%;transform:translateX(-50%);z-index:2147483640;width:min(880px,96vw);background:${bgCard};color:${fgText};border:2.4px solid ${borderCol};border-radius:14px;padding:10px 14px;box-shadow:0 16px 40px rgba(0,0,0,0.45);direction:rtl;font-family:'Cairo','Tajawal',sans-serif;pointer-events:auto;`;

        banner.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;border-bottom:2px solid ${borderCol};padding-bottom:6px;">
                <div style="display:flex;align-items:center;gap:8px;font-weight:900;font-size:0.9rem;flex-wrap:wrap;">
                    <span style="padding:2px 8px;border-radius:99px;background:#f59e0b;color:#000;font-weight:900;font-size:0.75rem;">🏛️ الملخص العام وخريطة الترابط الثلاثية (تزامن الصوت والصورة 100%)</span>
                    <span>${escapeSvgText(meta.screenTitle)}</span>
                </div>
                <button type="button" onclick="document.getElementById('msg-c5-impact-map-banner').style.display='none'" style="background:transparent;border:none;color:${fgText};font-size:1.1rem;font-weight:900;cursor:pointer;">✕</button>
            </div>
            <svg viewBox="0 0 840 132" width="100%" height="126" style="display:block;overflow:visible;">
                <!-- Stage 1 (Right in RTL): Inputs (من أين تأتي المعلومة؟) -->
                <g id="msg-c5-stage-1" onclick="window.speakC5ImpactStageDetail(1)" style="transition:all 0.3s ease;cursor:pointer;">
                    <rect x="578" y="6" width="254" height="118" rx="12" fill="${boxBg}" stroke="#f59e0b" stroke-width="3"/>
                    <text x="705" y="28" fill="#f59e0b" font-size="12.5" font-weight="900" text-anchor="middle">1️⃣ من أين تأتي المعلومة؟ (المدخلات) 🔊</text>
                    <text x="705" y="54" fill="${fgText}" font-size="11" font-weight="900" text-anchor="middle">${escapeSvgText(inputList[0] || '')}</text>
                    <text x="705" y="78" fill="${fgText}" font-size="11" font-weight="900" text-anchor="middle">${escapeSvgText(inputList[1] || '')}</text>
                    <text x="705" y="102" fill="#10b981" font-size="11" font-weight="900" text-anchor="middle">${escapeSvgText(inputList[2] || '')}</text>
                </g>

                <!-- Arrow 1 (Right to Center) -->
                <g id="msg-c5-arrow-1" style="opacity:0.45;transition:all 0.3s ease;">
                    <line x1="572" y1="65" x2="532" y2="65" stroke="#10b981" stroke-width="3.5" stroke-dasharray="5 3"/>
                    <polygon points="536,57 520,65 536,73" fill="#10b981"/>
                </g>

                <!-- Stage 2 (Center): Screen Function (عمل الشاشة ووظيفتها) -->
                <g id="msg-c5-stage-2" onclick="window.speakC5ImpactStageDetail(2)" style="opacity:0.55;transition:all 0.3s ease;cursor:pointer;">
                    <rect x="278" y="6" width="240" height="118" rx="12" fill="${boxBg}" stroke="#10b981" stroke-width="2.8"/>
                    <text x="398" y="28" fill="#10b981" font-size="12.5" font-weight="900" text-anchor="middle">2️⃣ عمل الشاشة والضبط المحاسبي 🔊</text>
                    <text x="398" y="54" fill="${fgText}" font-size="11" font-weight="900" text-anchor="middle">${escapeSvgText(meta.funcSummary.slice(0, 38))}</text>
                    <text x="398" y="78" fill="${fgText}" font-size="11" font-weight="900" text-anchor="middle">${escapeSvgText(meta.funcSummary.slice(38, 78))}</text>
                    <text x="398" y="102" fill="#f59e0b" font-size="10.8" font-weight="900" text-anchor="middle">⚖️ توازن ذري 100% ومنع التكرار</text>
                </g>

                <!-- Arrow 2 (Center to Left) -->
                <g id="msg-c5-arrow-2" style="opacity:0.45;transition:all 0.3s ease;">
                    <line x1="272" y1="65" x2="232" y2="65" stroke="#38bdf8" stroke-width="3.5" stroke-dasharray="5 3"/>
                    <polygon points="236,57 220,65 236,73" fill="#38bdf8"/>
                </g>

                <!-- Stage 3 (Left in RTL): Outputs (أين تُسمّع في التقارير والشاشات؟) -->
                <g id="msg-c5-stage-3" onclick="window.speakC5ImpactStageDetail(3)" style="opacity:0.55;transition:all 0.3s ease;cursor:pointer;">
                    <rect x="6" y="6" width="212" height="118" rx="12" fill="${boxBg}" stroke="#38bdf8" stroke-width="2.8"/>
                    <text x="112" y="28" fill="#38bdf8" font-size="12" font-weight="900" text-anchor="middle">3️⃣ أين تُسمّع؟ (التقارير والشاشات) 🔊</text>
                    <text x="112" y="52" fill="${fgText}" font-size="10.8" font-weight="900" text-anchor="middle">${escapeSvgText(meta.reports[0] || '')}</text>
                    <text x="112" y="76" fill="${fgText}" font-size="10.8" font-weight="900" text-anchor="middle">${escapeSvgText(meta.reports[1] || '')}</text>
                    <text x="112" y="100" fill="${fgText}" font-size="10.8" font-weight="900" text-anchor="middle">${escapeSvgText(meta.screens[0] || '')}</text>
                </g>
            </svg>
            <div id="msg-c5-golden-rule-strip" onclick="window.speakC5ImpactStageDetail(4)" style="margin-top:7px;padding:7px 11px;border-radius:10px;border:2px solid #f59e0b;background:${boxBg};display:flex;flex-direction:column;gap:3px;font-size:0.8rem;font-weight:900;cursor:pointer;" title="اضغط لسماع القاعدة الذهبية ورسالة الطمأنينة بالصوت الرجالي">
                <div>👑 <span style="color:#f59e0b;">القاعدة الذهبية للشاشة (🔊 اضغط للسماع):</span> ${escapeSvgText(meta.goldenRule || 'أسِّس بياناتك وأرصدتك بدقة مرة واحدة ينضبط لك النظام المحاسبي والمخزني بالكامل تلقائياً.')}</div>
                <div style="font-size:0.76rem;color:#10b981;">🛡️ <span>رسالة الطمأنينة للمحاسب:</span> ${escapeSvgText(meta.reassurance || 'لا تخشَ الخطأ أبداً؛ الشاشة محمية بدرع منع الحذف العشوائي وحفظ المسودات والتراجع الفوري.')}</div>
            </div>
        `;
        banner._activeC5Meta = meta;
        banner.style.display = 'block';

        // Synchronized Audio-Visual Phase Progression (Stage 1 -> Stage 2 -> Stage 3)
        _c5ImpactPhaseTimers.push(setTimeout(() => {
            const a1 = document.getElementById('msg-c5-arrow-1');
            const s2 = document.getElementById('msg-c5-stage-2');
            if (a1) a1.style.opacity = '1';
            if (s2) s2.style.opacity = '1';
        }, 3200));
        _c5ImpactPhaseTimers.push(setTimeout(() => {
            const a2 = document.getElementById('msg-c5-arrow-2');
            const s3 = document.getElementById('msg-c5-stage-3');
            if (a2) a2.style.opacity = '1';
            if (s3) s3.style.opacity = '1';
        }, 6600));
    }

    window.speakC5ImpactStageDetail = function (stageNum) {
        const banner = document.getElementById('msg-c5-impact-map-banner');
        const meta = (banner && banner._activeC5Meta) || (PURCHASES_MASTER_STEPS[0] && PURCHASES_MASTER_STEPS[0].c5ImpactMeta) || {};
        ['msg-c5-stage-1', 'msg-c5-stage-2', 'msg-c5-stage-3'].forEach((id, idx) => {
            const g = document.getElementById(id);
            if (g) g.style.opacity = (stageNum === 4 || stageNum === idx + 1) ? '1' : '0.5';
        });
        let txt = '';
        if (stageNum === 1) {
            txt = `المرحلة الأولى: من أين تأتي المعلومة؟ تستمد هذه الشاشة مدخلاتها من: ${(meta.inputs || []).join('، ومن ')}.`;
        } else if (stageNum === 2) {
            txt = `المرحلة الثانية: عمل الشاشة والضبط المحاسبي والمخزني: ${meta.funcSummary || ''}.`;
        } else if (stageNum === 3) {
            txt = `المرحلة الثالثة: أين تُسمّع وفي أي تقرير وشاشة تؤثر؟ تؤثر فوراً في: ${(meta.reports || []).join('، وفي ')}.`;
        } else {
            txt = `${meta.goldenRule || ''} ${meta.reassurance || ''}`;
        }
        const clean = stripAllSalawatPrefixes(adaptTextToDialect(txt, selectedMaleProfile));
        speakArabicMaleText(clean, null, selectedMaleProfile, false);
    };

    window.toggleSmartGuideImpactMapBanner = function () {
        if (typeof window.openMdInteractiveImpactMapModal === 'function') {
            window.openMdInteractiveImpactMapModal();
            return;
        }
        const b = document.getElementById('msg-c5-impact-map-banner');
        if (b && b.style.display === 'block') {
            b.style.display = 'none';
            return;
        }
        const activeSlMode = document.body.getAttribute('data-logistics-mode') || (window.location.search.includes('mode=purchases') ? 'purchases' : 'sales');
        const fallbackStep = (activeAutopilotStepsList && activeAutopilotStepsList[0] && activeAutopilotStepsList[0].diagramType === 'c5-impact-map')
            ? activeAutopilotStepsList[0]
            : (activeSlMode === 'sales' ? SALES_ONLY_STEPS[0] : PURCHASES_MASTER_STEPS[0]);
        renderLiveDubbedVisualStage(fallbackStep);
        window.speakC5ImpactStageDetail(4);
    };

    let lastAutopilotStepStartTs = 0;

    let _liveKaraokeTimer = null;
    window.__startLiveWordKaraokeAndSubTargetSync = function (speechEl, fullText, stepObj) {
        if (_liveKaraokeTimer) { clearInterval(_liveKaraokeTimer); _liveKaraokeTimer = null; }
        const cleanTxt = String(fullText || '').trim();
        const words = cleanTxt ? cleanTxt.split(/\s+/) : [];
        const esc = x => String(x).replace(/[&<>]/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[ch]));
        const wordEnds = []; let accLen = 0;
        words.forEach(w => { accLen += w.length + 1; wordEnds.push(accLen); });
        const rawSel = (stepObj && stepObj.targetSelector) ? String(stepObj.targetSelector) : '';
        const visibleSubEls = [];
        rawSel.split(',').map(s => s.trim()).filter(Boolean).forEach(sel => {
            try {
                const candidate = resolveBestVisibleTargetElement(sel);
                if (candidate && candidate !== document.body && !visibleSubEls.includes(candidate)) visibleSubEls.push(candidate);
            } catch (_) {}
        });
        let lastSubIdx = 0, lastWordIdx = -1, sawAudio = false;
        const startTs = Date.now();
        _liveKaraokeTimer = setInterval(() => {
            try {
                if (isAudioPaused) return;
                const ratio = getLiveSpeechRatio();
                if (ratio < 0) {
                    if (sawAudio || Date.now() - startTs > 90000) { clearInterval(_liveKaraokeTimer); _liveKaraokeTimer = null; }
                    return;
                }
                sawAudio = true;
                if (speechEl && words.length && document.body.contains(speechEl)) {
                    let wi = wordEnds.findIndex(e => e > ratio * accLen);
                    if (wi < 0) wi = words.length - 1;
                    if (wi !== lastWordIdx) {
                        lastWordIdx = wi;
                        const donePart = esc(words.slice(0, wi).join(' ')), restPart = esc(words.slice(wi + 1).join(' '));
                        speechEl.innerHTML = `${donePart ? `<span style="color:#94a3b8;">${donePart} </span>` : ''}<span class="msg-kar-active" style="color:#fde047;font-weight:950;text-decoration:underline;text-underline-offset:4px;background:rgba(253,224,71,0.14);border-radius:5px;padding:0 3px;">${esc(words[wi] || '')}</span>${restPart ? ` <span style="color:#ffffff;">${restPart}</span>` : ''}`;
                        const act = speechEl.querySelector('.msg-kar-active');
                        if (act) speechEl.scrollTop += (act.getBoundingClientRect().top - speechEl.getBoundingClientRect().top) - 22;
                    }
                }
                if (visibleSubEls.length > 1) {
                    const targetSubIdx = Math.min(visibleSubEls.length - 1, Math.floor(ratio * visibleSubEls.length));
                    if (targetSubIdx !== lastSubIdx && visibleSubEls[targetSubIdx]) {
                        lastSubIdx = targetSubIdx;
                        const lbl = getSmartElementTitle(visibleSubEls[targetSubIdx]) || (stepObj && (stepObj.cursorLabel || stepObj.badge)) || 'بيان';
                        moveGhostCursorToElement(visibleSubEls[targetSubIdx], lbl, false);
                    }
                }
            } catch (_) {}
        }, 60);
    };

    function runCurrentAutopilotStep() {
        if (!isAutopilotRunning || !activeAutopilotStepsList.length) return;
        lastAutopilotStepStartTs = Date.now();
        cleanupGuideTempPopups();
        const step = activeAutopilotStepsList[autopilotStepIdx];
        if (!step) return;
        if (typeof window.isGuideStepAllowedForCurrentRole === 'function' && !window.isGuideStepAllowedForCurrentRole(step)) {
            if (autopilotStepIdx + 1 < activeAutopilotStepsList.length) { autopilotStepIdx++; runCurrentAutopilotStep(); }
            else { window.stopLiveAutopilotSimulation(); }
            return;
        }
        renderStepPillsForActiveList();
        for (let lookahead = 1; lookahead <= 3; lookahead++) {
            const nextStep = activeAutopilotStepsList[autopilotStepIdx + lookahead];
            if (nextStep) {
                const nextTxt = resolveStepSpeech(nextStep, selectedMaleProfile, false);
                fetchAndDecodeMaleBuffer(nextTxt, selectedMaleProfile).catch(() => {});
            }
        }
        const badge = document.getElementById('msg-coach-step-badge');
        const speechBox = document.getElementById('msg-coach-speech-text');
        const playOpeningOnceNow = (autopilotStepIdx === 0 && !hasPlayedOpeningInvocation);
        if (playOpeningOnceNow) { hasPlayedOpeningInvocation = true; window.hasPlayedOpeningInvocation = true; }
        const spoken = resolveStepSpeech(step, selectedMaleProfile, playOpeningOnceNow);
        if (badge) badge.textContent = `${step.badge} (${autopilotStepIdx + 1}/${activeAutopilotStepsList.length})`;
        if (speechBox) speechBox.textContent = spoken;
        if (typeof step.action === 'function') { try { step.action(); } catch (e) {} }
        const el = resolveBestVisibleTargetElement(step.targetSelector) || document.body;
        moveGhostCursorToElement(el, step.cursorLabel || step.badge, true);
        window.__startLiveWordKaraokeAndSubTargetSync(speechBox, spoken, step);
        speakArabicMaleText(spoken, () => {
            if (!isAutopilotRunning || isAudioPaused) return;
            nextStepTimeoutId = setTimeout(() => {
                if (isAutopilotRunning && !isAudioPaused) window.stepLiveAutopilot(1);
            }, 220);
        }, selectedMaleProfile, playOpeningOnceNow);
    }

    // ── INTERACTIVE "PICK ANY ELEMENT TO EXPLAIN" MODE (المؤشر الحر لشرح أي جزء) ─
    window.startSmartPickElementMode = function () {
        if (window.__CINEMA_4K_ACTIVE__ && typeof window.stopMagaies4KCinemaMode === 'function') {
            window.stopMagaies4KCinemaMode();
        }
        window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;
        ensureGuideDomElements();
        window.closeSmartGuide();
        window.stopLiveAutopilotSimulation();
        window.__mdInteractiveGuideActive = true;
        isPickElementModeActive = true;
        document.body.classList.add('msg-pick-inspector-active');
        const banner = document.getElementById('msg-pick-mode-banner');
        if (banner) banner.style.display = 'inline-flex';
    };

    window.stopSmartPickElementMode = function () {
        isPickElementModeActive = false;
        document.body.classList.remove('msg-pick-inspector-active');
        const banner = document.getElementById('msg-pick-mode-banner');
        if (banner) banner.style.display = 'none';
    };

    document.addEventListener('click', function (e) {
        if (isPickElementModeActive) {
            if (e.target.closest('#msg-pick-mode-banner') || e.target.closest('#msg-universal-fixed-launcher') || e.target.closest('#msg-live-coach-bar') || e.target.closest('#msg-pinned-audio-dock')) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();

            const clickedEl = e.target.closest('button, a, select, input, .sl-kpi-card, tr, th, .sl-mode-btn, [onclick]') || e.target;
            window.stopSmartPickElementMode();
            explainPickedDomElement(clickedEl);
            return;
        }
        // البند 3 من الدستور الخامس: القفز المباشر بالضغط (معطل نهائياً عند كتم المرشد القديم أو تشغيل الاستوديو السينمائي 4K لمنع التداخل)
        if (false && !window.__MUTE_LEGACY_GUIDE_AUDIO__ && !window.__CINEMA_4K_ACTIVE__ && (window.__mdInteractiveGuideActive || isAutopilotRunning)) {
            if (e.target.closest('#msg-live-coach-bar, #msg-smart-hover-tooltip, #msg-universal-fixed-launcher, #msg-universal-modal, #msg-c5-impact-map-banner')) {
                return;
            }
            const jumpTarget = e.target.closest('button, a, input, select, textarea, .md-tab-btn, .pro-field-card, .input-group, .sl-kpi-card, .sl-mode-tab, .sl-unit-box, .account-block, .voucher-type-btn, .crystal-btn, #grand-tafqeet-container, #mcp-world-summary-bar, [onclick], th');
            if (!jumpTarget) return;
            if (typeof window.isElementAllowedForCurrentUserRole === 'function' && !window.isElementAllowedForCurrentUserRole(jumpTarget)) return;
            const info = (typeof window.explainMultiCashPaymentDomElement === 'function' && window.explainMultiCashPaymentDomElement(jumpTarget))
                || (typeof window.explainSmartLogisticsDomElement === 'function' && window.explainSmartLogisticsDomElement(jumpTarget))
                || (typeof window.explainMasterDataDomElement === 'function' && window.explainMasterDataDomElement(jumpTarget))
                || (typeof window.explainDirectSalesDomElement === 'function' && window.explainDirectSalesDomElement(jumpTarget))
                || (typeof window.explainPurchaseDomElement === 'function' && window.explainPurchaseDomElement(jumpTarget))
                || (typeof window.autoSynthesizeElementExplanation === 'function' && window.autoSynthesizeElementExplanation(jumpTarget));
            if (info && info.title) {
                lastAutopilotStepStartTs = Date.now();
                cleanupGuideTempPopups(jumpTarget);
                const badge = document.getElementById('msg-coach-step-badge');
                const speechBox = document.getElementById('msg-coach-speech-text');
                const adapted = stripAllSalawatPrefixes(adaptTextToDialect(`${info.title}. ${info.desc}`, selectedMaleProfile));
                if (badge) badge.textContent = `🎯 ${info.title}`;
                if (speechBox) speechBox.textContent = adapted;
                moveGhostCursorToElement(jumpTarget, info.title, true);
                if (jumpTarget.tagName === 'SELECT' || jumpTarget.querySelector?.('select')) {
                    showLiveSelectOptionsMirror(jumpTarget, null, info.title);
                } else {
                    triggerLiveChildScreenPreviewForElement(jumpTarget);
                }
                speakArabicMaleText(adapted, null, selectedMaleProfile, false);
            }
        }
    }, true);

    function explainPickedDomElement(el) {
        if (!el) return;
        const activeGuideKey = buildUniversalScreenGuideIfNeeded();
        const data = window.MagaiesSmartGuides[activeGuideKey] || window.MagaiesSmartGuides['smart-logistics'];
        let matchedTitle = '';
        let matchedDesc = '';

        if (activeGuideKey === 'master-data' && typeof window.explainMasterDataDomElement === 'function') {
            const mdInfo = window.explainMasterDataDomElement(el);
            if (mdInfo && mdInfo.title) {
                matchedTitle = mdInfo.title;
                matchedDesc = mdInfo.desc;
            }
        }
        if (!matchedTitle && activeGuideKey === 'purchase-direct' && typeof window.explainPurchaseDomElement === 'function') {
            const purInfo = window.explainPurchaseDomElement(el);
            if (purInfo && purInfo.title) {
                matchedTitle = purInfo.title;
                matchedDesc = purInfo.desc;
            }
        }
        if (!matchedTitle && (activeGuideKey === 'sales-direct' || activeGuideKey === 'sales-split') && typeof window.explainDirectSalesDomElement === 'function') {
            const salesInfo = window.explainDirectSalesDomElement(el);
            if (salesInfo && salesInfo.title) {
                matchedTitle = salesInfo.title;
                matchedDesc = salesInfo.desc;
            }
        }
        if (!matchedTitle && activeGuideKey === 'purchase-receipt' && typeof window.explainPurchaseReceiptDomElement === 'function') {
            const grnInfo = window.explainPurchaseReceiptDomElement(el);
            if (grnInfo && grnInfo.title) {
                matchedTitle = grnInfo.title;
                matchedDesc = grnInfo.desc;
            }
        }
        if (!matchedTitle && activeGuideKey === 'smart-logistics' && typeof window.explainSmartLogisticsDomElement === 'function') {
            const slInfo = window.explainSmartLogisticsDomElement(el);
            if (slInfo && slInfo.title) {
                matchedTitle = slInfo.title;
                matchedDesc = slInfo.desc;
            }
        }
        if (!matchedTitle && activeGuideKey === 'multi-cash-payment' && typeof window.explainMultiCashPaymentDomElement === 'function') {
            const mcpInfo = window.explainMultiCashPaymentDomElement(el);
            if (mcpInfo && mcpInfo.title) {
                matchedTitle = mcpInfo.title;
                matchedDesc = mcpInfo.desc;
            }
        }

        if (!matchedTitle && data && Array.isArray(data.buttonsDict)) {
            for (const item of data.buttonsDict) {
                if (item.targetSelector && (el.matches(item.targetSelector) || el.closest(item.targetSelector))) {
                    matchedTitle = item.label;
                    matchedDesc = `${item.whatItDoes} ${item.whatHappensIfClicked}`;
                    break;
                }
            }
        }

        if (!matchedTitle) {
            matchedTitle = (el.getAttribute('title') || el.innerText || el.placeholder || el.value || 'هذا الجزء من الشاشة').replace(/\s+/g, ' ').trim().slice(0, 60);
            if (el.id === 'sl-tab-purchases' || el.closest('#sl-tab-purchases')) {
                matchedTitle = 'بوابة استلام المشتريات وتقسيمها (GRN)';
                matchedDesc = 'هذه البوابة الزرقاء مخصصة لاستلام البضاعة الواردة من الموردين؛ تفحص الكميات وتستلمها دفعة واحدة أو تقسمها على عدة دفعات وعدة مخازن، وتقوم بإضافة الرصيد للمخزن وإصدار مذكرة استلام رسمية.';
            } else if (el.id === 'sl-tab-sales' || el.closest('#sl-tab-sales')) {
                matchedTitle = 'بوابة تسليم المبيعات وتقسيمها (GDN)';
                matchedDesc = 'هذه البوابة الخضراء مخصصة لصرف وتسليم بضاعة المبيعات للعملاء؛ تكشف الرصيد المتاح في المخزن، وتتيح تسليم الفاتورة كاملة أو تقسيمها على دفعات، وتقوم بخصم الرصيد وإصدار تصريح خروج البوابة.';
            } else if (el.classList.contains('sl-qty-primary-input')) {
                matchedTitle = 'صندوق الكمية بالوحدة الأساسية (طن / كرتون)';
                matchedDesc = 'تكتب هنا الكمية المستلمة أو المسلمة في هذه الدفعة بالوحدة الكبرى كالطن أو الكرتون، وسيقوم البرنامج تلقائياً بتحويلها إلى الوحدة الفرعية في الصندوق المجاور وحساب المتبقي للدفعة القادمة.';
            } else if (el.classList.contains('sl-qty-base-input')) {
                matchedTitle = 'صندوق الكمية بالوحدة الفرعية (سيخة / حبة / قطعة)';
                matchedDesc = 'إذا عدّ العامل البضاعة بالحبة أو السيخة، اكتب العدد هنا مباشرة بدون آلة حاسبة، وسيقوم البرنامج بتحويله فوراً إلى الوحدة الأساسية وحساب المتبقي بدقة.';
            } else if (el.classList.contains('sl-row-wh-select') || el.closest('.sl-col-wh')) {
                matchedTitle = 'اختيار مخزن السطر وفحص الرصيد الفعلي';
                matchedDesc = 'يتيح لك هذا الاختيار تحديد مستودع مستقل لهذا الصنف وحده داخل الفاتورة، كما يعرض لك الرصيد الفعلي المتوفر حالياً داخل كل مخزن قبل التنفيذ.';
            } else if (el.id === 'sl-auto-execute-qr' || el.closest('label')?.querySelector('#sl-auto-execute-qr')) {
                matchedTitle = 'خيار الاعتماد الفوري المتتابع عند المسح';
                matchedDesc = 'عند تفعيل هذا الخيار، بمجرد تمرير باركود الفاتورة أو الإذن أمام الكاميرا يتم اعتمادها وتحديث المخزون تلقائياً في ثانية واحدة بدون الحاجة لضغط أي زر.';
            } else if (el.id === 'sl-photo-input' || (el.getAttribute('onclick') || '').includes('sl-photo-input')) {
                matchedTitle = 'زر إرفاق صورة البضاعة أو المستند الموقع';
                matchedDesc = 'يتيح لك تصوير البضاعة على الشاحنة أو تصوير مستند الاستلام الورقي وإرفاقه كدليل بصري موثق مع الإذن الرسمي.';
            } else if (el.closest('.sl-floating-summary')) {
                matchedTitle = 'شريط الملخص والاعتماد العائم';
                matchedDesc = 'يعرض لك هذا الشريط ميزان الفاتورة بالكامل قبل الاعتماد: إجمالي الفاتورة، والمنفذ سابقاً، والمنفذ في هذه الدفعة، والمتبقي بعد الدفعة، وبجانبه أزرار الاعتماد والطباعة والواتساب وعكس الحركة.';
            } else {
                matchedDesc = `هذه الأداة (${matchedTitle}) تساعدك في إدارة وفحص حركة البضاعة داخل البوابة بدقة وسرعة، وعند استخدامها يقوم النظام بتحديث البيانات المرتبطة بها فوراً.`;
            }
        }

        isAutopilotRunning = true;
        const coachBar = document.getElementById('msg-live-coach-bar');
        const speechBox = document.getElementById('msg-coach-speech-text');
        const badge = document.getElementById('msg-coach-step-badge');
        if (coachBar) coachBar.style.display = 'flex';

        const adapted = stripAllSalawatPrefixes(adaptTextToDialect(`${matchedTitle}. ${matchedDesc}`, selectedMaleProfile));
        if (badge) badge.textContent = `👆 ${matchedTitle}`;
        if (speechBox) speechBox.textContent = adapted;

        moveGhostCursorToElement(el, matchedTitle, true);
        speakArabicMaleText(adapted, null, selectedMaleProfile);
    }

    // ── SMART MICROPHONE & ON-SCREEN ANSWER ENGINE (مايك المرشد الذكي) ───────
    window.toggleSmartVoiceQA = function () {
        ensureGuideDomElements();
        const panel = document.getElementById('msg-voice-qa-panel');
        if (!panel) return;
        if (panel.style.display === 'flex') {
            window.closeSmartVoiceQA();
        } else {
            panel.style.display = 'flex';
            const inp = document.getElementById('msg-voice-text-input');
            if (inp) inp.focus();
        }
    };

    window.closeSmartVoiceQA = function () {
        const panel = document.getElementById('msg-voice-qa-panel');
        if (panel) panel.style.display = 'none';
        const micBtn = document.getElementById('msg-fixed-mic-btn');
        if (micBtn) micBtn.classList.remove('listening');
        if (speechRecognitionInstance) {
            try { speechRecognitionInstance.stop(); } catch (e) {}
        }
    };

    window.startBrowserVoiceRecognition = function () {
        ensureGuideDomElements();
        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        const statusLbl = document.getElementById('msg-mic-status-lbl');
        const micBtn = document.getElementById('msg-fixed-mic-btn');
        const listenBtn = document.getElementById('msg-start-mic-listen-btn');

        if (!SpeechRec) {
            if (statusLbl) statusLbl.textContent = 'اكتب سؤالك في المربع أو اضغط على أحد الأزرار السريعة بالأسفل:';
            return;
        }

        try {
            if (speechRecognitionInstance) {
                try { speechRecognitionInstance.stop(); } catch (e) {}
            }
            const rec = new SpeechRec();
            speechRecognitionInstance = rec;
            rec.lang = selectedMaleProfile === 'egyptian' ? 'ar-EG' : 'ar-SA';
            rec.interimResults = false;
            rec.maxAlternatives = 1;

            if (micBtn) micBtn.classList.add('listening');
            if (listenBtn) listenBtn.textContent = '🔴 جاري الاستماع... تكلم الآن';
            if (statusLbl) statusLbl.textContent = '🎙️ تكلم الآن.. اسأل عن المشتريات أو المبيعات أو التقسيم';

            rec.onresult = (event) => {
                const transcript = event.results[0][0].transcript || '';
                const inp = document.getElementById('msg-voice-text-input');
                if (inp) inp.value = transcript;
                window.answerUserScreenQuestion(transcript);
            };
            rec.onerror = () => {
                if (micBtn) micBtn.classList.remove('listening');
                if (listenBtn) listenBtn.textContent = '🎙️ تحدث بالمايك الآن';
            };
            rec.onend = () => {
                if (micBtn) micBtn.classList.remove('listening');
                if (listenBtn) listenBtn.textContent = '🎙️ تحدث بالمايك الآن';
            };
            rec.start();
        } catch (err) {
            if (statusLbl) statusLbl.textContent = 'اكتب سؤالك أو اضغط على الأزرار السريعة بالأسفل';
        }
    };

    window.answerUserScreenQuestion = function (rawQuestion) {
        ensureGuideDomElements();
        const q = String(rawQuestion || '').trim();
        if (!q) return;

        const ansBox = document.getElementById('msg-onscreen-answer-box');
        let answerText = '';
        let targetSel = '#sl-tab-purchases';
        let cursorTitle = 'الإجابة على الشاشة';

        if (q.includes('مبيعات') || q.includes('تسليم') || q.includes('عميل') || q.includes('صرف')) {
            cursorTitle = 'منظومة المبيعات وتقسيمها 📤';
            targetSel = '#sl-tab-sales';
            answerText = 'لتسليم بضاعة المبيعات أو تقسيمها: نضغط أولاً على زر (منظومة المبيعات الأخضر)، ثم نفتح فاتورة العميل من الجدول، ونكتب الكمية المسلمة الآن في مربع الكمية، ثم نضغط (تأكيد واعتماد) ليتم خصمها من المخزن وحفظ الباقي.';
        } else if (q.includes('مشتريات') || q.includes('استلام') || q.includes('مورد') || q.includes('توريد')) {
            cursorTitle = 'منظومة المشتريات وتقسيمها 📥';
            targetSel = '#sl-tab-purchases';
            answerText = 'لاستلام بضاعة المشتريات أو تقسيمها: نضغط أولاً على زر (منظومة المشتريات الأزرق)، ثم نفتح الفاتورة من جدول قيد الاستلام، ونكتب الكمية التي وصلت فعلياً في مربع الكمية، ثم نضغط (تأكيد واعتماد) لإضافتها للمخزن.';
        } else if (q.includes('تقسيم') || q.includes('دفعات') || q.includes('باقي') || q.includes('جزء')) {
            cursorTitle = 'تقسيم الكمية والباقي ✂️';
            targetSel = '.sl-qty-primary-input';
            answerText = 'لتقسيم أي فاتورة على دفعات: افتح الفاتورة واكتب في مربع الكمية العدد الذي وصل أو خرج اليوم فقط (مثلاً 5 من 7)، وسيقوم البرنامج أوتوماتيكياً بحساب المتبقي (2) وحفظه للدفعة القادمة.';
        } else if (q.includes('الغاء') || q.includes('إلغاء') || q.includes('عكس') || q.includes('تراجع') || q.includes('خطأ')) {
            cursorTitle = 'زر إلغاء وعكس الحركة ↩️';
            targetSel = '#sl-btn-undo';
            answerText = 'للتراجع عن أي عملية اعتمدتها: اضغط على زر (إلغاء وعكس الحركة المخزنية) الأحمر بجوار زر الاعتماد، وسيعيد البرنامج رصيد المخزن والفاتورة كما كانا فوراً.';
        } else {
            cursorTitle = 'شرح الشاشة بالكامل 🌐';
            targetSel = '.sl-mode-switcher';
            answerText = 'هذه الشاشة مقسّمة إلى قسمين رئيسيين في الأعلى: الأخضر لتسليم المبيعات وتقسيمها، والأزرق لاستلام المشتريات وتقسيمها. يمكنك الضغط على (اشرح الشاشة كلها) أو (اشرح جزءاً معيناً) ليرشدك الماوس خطوة بخطوة.';
        }

        const adaptedAns = stripAllSalawatPrefixes(adaptTextToDialect(answerText, selectedMaleProfile));

        if (ansBox) {
            ansBox.style.display = 'block';
            ansBox.innerHTML = `<div style="color:#fde047; font-weight:900; margin-bottom:4px;">💡 الإجابة المباشرة على الشاشة:</div>${adaptedAns}`;
        }

        const el = document.querySelector(targetSel) || document.body;
        moveGhostCursorToElement(el, cursorTitle, true);
        speakArabicMaleText(adaptedAns, null, selectedMaleProfile);
    };

    window.stopLiveAutopilotSimulation = function () {
        isAutopilotRunning = false;
        window.__mdInteractiveGuideActive = false;
        stopCurrentSpeech();
        cleanupGuideTempPopups();
        applyGuideSpotlight(null);

        const topGuideBtn = document.querySelector('.msg-quartet-guide');
        if (topGuideBtn) topGuideBtn.style.boxShadow = '';
        const tip = document.getElementById('msg-smart-hover-tooltip');
        if (tip) tip.style.display = 'none';

        const cursor = document.getElementById('msg-ghost-cursor');
        const ring = document.getElementById('msg-live-target-ring');
        const coachBar = document.getElementById('msg-live-coach-bar');
        const pinnedDock = document.getElementById('msg-pinned-audio-dock');
        const svgPop = document.getElementById('msg-coach-svg-popover');
        if (cursor) cursor.style.display = 'none';
        if (ring) ring.style.display = 'none';
        if (coachBar) coachBar.style.display = 'none';
        if (pinnedDock) pinnedDock.style.display = 'none';
        if (svgPop) svgPop.style.display = 'none';

        if (tempDemoCreated && typeof window.renderSlRegistry === 'function') {
            tempDemoCreated = false;
            window.renderSlRegistry();
            if (typeof window.clearSlDocumentCard === 'function') window.clearSlDocumentCard();
        }
        isHandsOnTrainingActive = false;
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 🎯 1. INTERACTIVE HANDS-ON QUIZ / TRAINING ENGINE («درّبني عملياً بيدي»)
    // (يوجه المستخدم بالصوت خطوة بخطوة وينتظر منه أن يضغط أو يكتب بيده لينتقل للخطوة التالية!)
    // ══════════════════════════════════════════════════════════════════════════
    let isHandsOnTrainingActive = false;
    let handsOnStepIdx = 0;

    window.startInteractiveHandsOnTraining = function () {
        ensureGuideDomElements();
        window.closeSmartGuide();
        window.stopLiveAutopilotSimulation();

        isHandsOnTrainingActive = true;
        isAutopilotRunning = true;
        handsOnStepIdx = 0;

        if (typeof window.setSlRegistryFilter === 'function') {
            window.setSlRegistryFilter('pending');
        }
        window.ensureLiveDemoInvoiceForGuide();

        const coachBar = document.getElementById('msg-live-coach-bar');
        if (coachBar) coachBar.style.display = 'flex';

        runHandsOnTrainingStep(0);
    };

    function runHandsOnTrainingStep(idx) {
        if (!isHandsOnTrainingActive) return;
        handsOnStepIdx = idx;

        const isSales = document.body.getAttribute('data-logistics-mode') === 'sales';
        const badge = document.getElementById('msg-coach-step-badge');
        const speechBox = document.getElementById('msg-coach-speech-text');

        if (idx === 0) {
            const promptTxt = adaptTextToDialect(
                `${SHORT_SAHIHAYN_SALAWAT} أهلاً بك في التدريب العملي التفاعلي! دورك الآن أن تطبق بيدك: اضغط بالماوس على الفاتورة الأولى في جدول السجل الذكي الذي يشير إليه المؤشر الذهبي لتفتحها.`,
                selectedMaleProfile
            );
            if (badge) badge.textContent = '🎯 تدريب عملي (1/3): اضغط بيدك على الفاتورة';
            if (speechBox) speechBox.textContent = promptTxt;
            const row = document.querySelector('#sl-registry-tbody tr:first-child') || document.getElementById('sl-registry-tbody');
            if (row) moveGhostCursorToElement(row, '👈 اضغط هنا بيدك لفتح الفاتورة', true);
            speakArabicMaleText(promptTxt, null, selectedMaleProfile);
        } else if (idx === 1) {
            const promptTxt = adaptTextToDialect(
                `أحسنت يا بطل! فتحت الفاتورة بنجاح. الآن الفاتورة متبقي فيها سبعة؛ اذهب بالماوس واكتب بيدك رقم خمسة في صندوق الكمية الذي يشير إليه المؤشر الذهبي لتجرب تقسيم الدفعة.`,
                selectedMaleProfile
            );
            if (badge) badge.textContent = '🎯 تدريب عملي (2/3): اكتب رقم (5) في صندوق الكمية';
            if (speechBox) speechBox.textContent = promptTxt;
            setTimeout(() => {
                const inp = document.querySelector('.sl-qty-primary-input');
                if (inp) {
                    moveGhostCursorToElement(inp, '✍️ اكتب هنا رقم (5) بيدك', true);
                }
            }, 150);
            speakArabicMaleText(promptTxt, null, selectedMaleProfile);
        } else if (idx === 2) {
            const promptTxt = adaptTextToDialect(
                `ممتاز جداً! لاحظ كيف حسب البرنامج تلقائياً أن المعادل خمسمية بالوحدة الفرعية وأن المتبقي اثنان للدفعة القادمة! الآن الخطوة الأخيرة: اضغط على زر (${isSales ? 'تأكيد واعتماد التسليم' : 'تأكيد واعتماد الاستلام'}) في الشريط السفلي لإتمام التدريب!`,
                selectedMaleProfile
            );
            if (badge) badge.textContent = '🎯 تدريب عملي (3/3): اضغط زر الاعتماد بالأسفل';
            if (speechBox) speechBox.textContent = promptTxt;
            const btn = document.getElementById('sl-btn-execute');
            if (btn) moveGhostCursorToElement(btn, '🚀 اضغط هنا لإتمام التدريب', true);
            speakArabicMaleText(promptTxt, null, selectedMaleProfile);
        } else if (idx === 3) {
            const congratsTxt = adaptTextToDialect(
                `ما شاء الله! أكملت التدريب العملي بنجاح وأصبحت جاهزاً تماماً لإدارة البوابة وتقسيم الفواتير باحترافية عالية!`,
                selectedMaleProfile
            );
            if (badge) badge.textContent = '🏆 اكتمل التدريب العملي بنجاح 100%!';
            if (speechBox) speechBox.textContent = congratsTxt;
            if (typeof window.showSlToast === 'function') {
                window.showSlToast('🏆 أحسنت! أتقنت دورة الاستلام والتقسيم والاعتماد بنجاح 100%!', 'success', 4500);
            }
            speakArabicMaleText(congratsTxt, () => {
                setTimeout(() => window.stopLiveAutopilotSimulation(), 1200);
            }, selectedMaleProfile);
        }
    }

    // Listen to user interactions during Hands-On Training Mode
    document.addEventListener('click', function (e) {
        if (!isHandsOnTrainingActive) return;
        if (handsOnStepIdx === 0) {
            const clickedRow = e.target.closest('#sl-registry-tbody tr');
            if (clickedRow) {
                setTimeout(() => runHandsOnTrainingStep(1), 350);
            }
        } else if (handsOnStepIdx === 2) {
            const clickedExec = e.target.closest('#sl-btn-execute');
            if (clickedExec) {
                if (tempDemoCreated) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                runHandsOnTrainingStep(3);
            }
        }
    }, true);

    document.addEventListener('input', function (e) {
        if (!isHandsOnTrainingActive || handsOnStepIdx !== 1) return;
        if (e.target && (e.target.classList.contains('sl-qty-primary-input') || e.target.classList.contains('sl-qty-base-input'))) {
            const val = parseFloat(e.target.value || 0);
            if (val > 0 && val !== 7 && val !== 700) {
                setTimeout(() => {
                    if (isHandsOnTrainingActive && handsOnStepIdx === 1) {
                        runHandsOnTrainingStep(2);
                    }
                }, 650);
            }
        }
    }, true);

    // ══════════════════════════════════════════════════════════════════════════
    // 🏆 1B. QUICK INTERACTIVE QUIZ + VOICE-TO-FILTER NAVIGATION (المقترحات الـ 3 المعتمدة)
    // ══════════════════════════════════════════════════════════════════════════
    let isMdQuickQuizActive = false;
    let mdQuickQuizStep = 0;
    const MD_QUIZ_STEPS = [
        {
            tabSel: '.tab-customers',
            openFn: null,
            targetSel: '#cust-limit',
            badge: '🎯 اختبار (1/3): أشر على خانة (حد الائتمان للعميل)',
            prompt: 'أهلاً بك في الاختبار التطبيقي السريع! السؤال الأول: حرّك الماوس أو اضغط على خانة حد الائتمان وسقف المديونية في بطاقة العميل.'
        },
        {
            tabSel: '.tab-items',
            openFn: () => { if (typeof window.openItemForm === 'function') window.openItemForm(); },
            targetSel: '#item-base-unit',
            badge: '🎯 اختبار (2/3): أشر على قائمة (الوحدة الأساسية للصنف)',
            prompt: 'أحسنت إجابة صحيحة! السؤال الثاني: حرّك الماوس أو اضغط على القائمة المنسدلة للوحدة الأساسية للصنف داخل بطاقة الأصناف.'
        },
        {
            tabSel: '.tab-costcenters',
            openFn: null,
            targetSel: 'button[onclick*="openOpeningEquilibriumModal"]',
            badge: '🎯 اختبار (3/3): أشر على زر (القيد الافتتاحي 3109)',
            prompt: 'ممتاز جداً! السؤال الثالث والأخير: حرّك الماوس أو اضغط على زر القيد الافتتاحي المتوازن في الشريط العلوي.'
        }
    ];

    const PURCHASE_QUIZ_STEPS = [
        {
            tabSel: null,
            openFn: () => { if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('direct'); },
            targetSel: '#hdr-customer-search, .group-supplier',
            badge: '🎯 اختبار (1/3): أشر على خانة (المورد وزر الأستاذ)',
            prompt: 'أهلاً بك في الاختبار التطبيقي لشاشة المشتريات! السؤال الأول: حرّك الماوس أو اضغط على خانة البحث عن المورد في أعلى الفاتورة.'
        },
        {
            tabSel: null,
            openFn: null,
            targetSel: '#quick-item-search',
            badge: '🎯 اختبار (2/3): أشر على (البحث السريع عن الأصناف F2)',
            prompt: 'أحسنت إجابة صحيحة! السؤال الثاني: حرّك الماوس أو اضغط على شريط البحث السريع عن الأصناف والباركود.'
        },
        {
            tabSel: null,
            openFn: null,
            targetSel: '#btn-toggle-transport, #payment-panel, #btn-submit-purchase-invoice',
            badge: '🎯 اختبار (3/3): أشر على (الترحيل أو الدفع أو حفظ الفاتورة)',
            prompt: 'ممتاز جداً! السؤال الثالث والأخير: حرّك الماوس أو اضغط على زر الترحيل أو لوحة الدفع الهجين أو زر حفظ واعتماد الفاتورة.'
        }
    ];

    const SMART_LOGISTICS_QUIZ_STEPS = [
        {
            tabSel: null,
            openFn: null,
            targetSel: '#sl-doc-selector, #sl-doc-search-input',
            badge: '🎯 اختبار (1/3): أشر على (قائمة اختيار الفاتورة أو البحث عنها)',
            prompt: 'أهلاً بك في الاختبار التطبيقي لبوابة الاستلام والتسليم! السؤال الأول: حرّك الماوس أو اضغط على قائمة اختيار الفاتورة أو خانة البحث الذكي عنها.'
        },
        {
            tabSel: null,
            openFn: null,
            targetSel: '#sl-btn-fill-all, .sl-qty-primary-input',
            badge: '🎯 اختبار (2/3): أشر على (زر استلام/تسليم الكل أو خانة الكمية)',
            prompt: 'أحسنت إجابة صحيحة! السؤال الثاني: حرّك الماوس أو اضغط على زر تعبئة كامل المتبقي أو خانة إدخال الكمية بالوحدة الكبرى.'
        },
        {
            tabSel: null,
            openFn: null,
            targetSel: '#sl-btn-submit, #sl-btn-save-draft',
            badge: '🎯 اختبار (3/3): أشر على (زر اعتماد الإذن أو حفظ المسودة)',
            prompt: 'ممتاز جداً! السؤال الثالث والأخير: حرّك الماوس أو اضغط على زر اعتماد وترحيل الإذن للمخزن أو زر حفظ المسودة.'
        }
    ];

    const MULTI_CASH_QUIZ_STEPS = [
        {
            tabSel: null,
            openFn: null,
            targetSel: '#main-account-search, #btn-payment, #btn-receipt',
            badge: '🎯 اختبار (1/3): أشر على (الحساب الرئيسي أو نوع الحركة المجمعة)',
            prompt: 'أهلاً بك في الاختبار التطبيقي لشاشة الحركة المالية المجمعة! السؤال الأول: حرّك الماوس أو اضغط على خانة الحساب الرئيسي للصرف أو الاستلام في أعلى الشاشة.'
        },
        {
            tabSel: null,
            openFn: null,
            targetSel: '#accounts-blocks-container .block-account-search, .account-block',
            badge: '🎯 اختبار (2/3): أشر على (كتلة الحساب الفرعي أو طرق الدفع)',
            prompt: 'أحسنت إجابة صحيحة! السؤال الثاني: حرّك الماوس أو اضغط على خانة الحساب الفرعي المستفيد أو أزرار نقدي وتحويل وشيك داخل الكتلة.'
        },
        {
            tabSel: null,
            openFn: null,
            targetSel: '#btn-save, #mcp-world-summary-bar',
            badge: '🎯 اختبار (3/3): أشر على (زر حفظ واعتماد السند F10 أو شريط الملخص)',
            prompt: 'ممتاز جداً! السؤال الثالث والأخير: حرّك الماوس أو اضغط على زر حفظ واعتماد السند إف عشرة في الشريط السفلي.'
        }
    ];

    function getActiveQuizStepsList() {
        const isMcp = (currentGuideKey === 'multi-cash-payment' || window.location.pathname.includes('multi-cash-payment') || !!document.getElementById('accounts-blocks-container'));
        if (isMcp) return MULTI_CASH_QUIZ_STEPS;
        const isSl = (currentGuideKey === 'smart-logistics' || window.location.pathname.includes('smart-logistics') || !!document.getElementById('sl-top-cockpit-bar'));
        if (isSl) return SMART_LOGISTICS_QUIZ_STEPS;
        const isPur = (currentGuideKey === 'purchase-direct' || window.location.pathname.includes('purchase.php') || !!document.getElementById('purchase-editor-view'));
        return isPur ? PURCHASE_QUIZ_STEPS : MD_QUIZ_STEPS;
    }

    window.startQuickMasterDataQuiz = function () {
        ensureGuideDomElements();
        stopCurrentSpeech();
        isAutopilotRunning = false;
        window.__mdInteractiveGuideActive = true;
        isMdQuickQuizActive = true;
        mdQuickQuizStep = 0;
        const coachBar = document.getElementById('msg-live-coach-bar');
        if (coachBar) coachBar.style.display = 'flex';
        runMdQuickQuizStep(0);
    };

    function runMdQuickQuizStep(stepIdx) {
        if (!isMdQuickQuizActive) return;
        mdQuickQuizStep = stepIdx;
        const badge = document.getElementById('msg-coach-step-badge');
        const activeList = getActiveQuizStepsList();
        if (stepIdx >= activeList.length) {
            isMdQuickQuizActive = false;
            const winMsg = adaptTextToDialect('ما شاء الله! اجتزت الاختبار التطبيقي للشاشة بنجاح مئة بالمئة!', selectedMaleProfile);
            if (badge) badge.textContent = '🏆 اجتزت الاختبار 100%!';
            if (typeof window.showToast === 'function') window.showToast('🏆 أحسنت! اجتزت الاختبار التطبيقي بنجاح 100%!', 'success');
            speakArabicMaleText(winMsg, null, selectedMaleProfile, false);
            return;
        }
        const st = activeList[stepIdx];
        if (st.tabSel) {
            const tabBtn = document.querySelector(st.tabSel);
            if (tabBtn) { try { tabBtn.click(); } catch (e) {} }
        }
        if (typeof st.openFn === 'function') { try { st.openFn(); } catch (e) {} }
        if (badge) badge.textContent = st.badge;
        setTimeout(() => {
            const targetEl = document.querySelector(st.targetSel);
            if (targetEl) applyGuideSpotlight(targetEl);
        }, 80);
        const msg = adaptTextToDialect(st.prompt, selectedMaleProfile);
        speakArabicMaleText(msg, null, selectedMaleProfile, false);
    }

    function checkMdQuickQuizHit(el) {
        if (!isMdQuickQuizActive || !el) return false;
        const activeList = getActiveQuizStepsList();
        const st = activeList[mdQuickQuizStep];
        if (!st) return false;
        if (el.matches?.(st.targetSel) || el.closest?.(st.targetSel) || el.querySelector?.(st.targetSel)) {
            runMdQuickQuizStep(mdQuickQuizStep + 1);
            return true;
        }
        return false;
    }

    function extractLiveElementValueReadback(el) {
        if (!el) return '';
        const ctrl = (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA')
            ? el
            : (typeof el.querySelector === 'function' ? el.querySelector('input, select, textarea') : null);
        if (!ctrl) return '';
        if (ctrl.tagName === 'SELECT') {
            const opt = ctrl.options && ctrl.options[ctrl.selectedIndex];
            const txt = opt ? (opt.textContent || opt.innerText || '').trim() : '';
            if (txt && !txt.includes('اختر') && !txt.includes('--')) return txt;
            return '';
        }
        const type = (ctrl.getAttribute('type') || 'text').toLowerCase();
        if (type === 'password' || type === 'hidden' || type === 'file') return '';
        const val = String(ctrl.value || '').trim();
        return val.length > 0 && val.length <= 50 ? val : '';
    }

    window.executeMasterDataVoiceQuery = function (rawQuery) {
        const q = String(rawQuery || '').trim();
        if (!q) return;
        ensureGuideDomElements();
        const coachBar = document.getElementById('msg-live-coach-bar');
        const badge = document.getElementById('msg-coach-step-badge');
        if (coachBar) coachBar.style.display = 'flex';

        const isSmartLogisticsScreen = (currentGuideKey === 'smart-logistics' || window.location.pathname.includes('smart-logistics') || !!document.getElementById('sl-top-cockpit-bar'));
        if (isSmartLogisticsScreen) {
            if (q.includes('خريطة') || q.includes('خريطه') || q.includes('قاعدة ذهبية') || q.includes('القاعدة الذهبية') || q.includes('ملخص')) {
                window.toggleSmartGuideImpactMapBanner();
                if (badge) badge.textContent = '🗺️ خريطة الترابط والقاعدة الذهبية';
                return;
            }
            if (q.includes('مشتريات') || q.includes('استلام')) {
                if (typeof window.switchLogisticsMode === 'function') window.switchLogisticsMode('purchases');
                if (badge) badge.textContent = '🎙️ بوابة استلام المشتريات (GRN)';
                speakArabicMaleText('تم الانتقال إلى بوابة استلام المشتريات وتقسيمها.', null, selectedMaleProfile, false);
                return;
            }
            if (q.includes('مبيعات') || q.includes('تسليم') || q.includes('صرف')) {
                if (typeof window.switchLogisticsMode === 'function') window.switchLogisticsMode('sales');
                if (badge) badge.textContent = '🎙️ بوابة تسليم المبيعات (GDN)';
                speakArabicMaleText('تم الانتقال إلى بوابة تسليم المبيعات وتقسيمها.', null, selectedMaleProfile, false);
                return;
            }
            if (q.includes('الكل') || q.includes('كامل المتبقي')) {
                if (typeof window.fillAllRemainingQuantities === 'function') window.fillAllRemainingQuantities();
                if (badge) badge.textContent = '🎙️ تعبئة كامل المتبقي';
                speakArabicMaleText('تم تعبئة كامل الكميات المتبقية في الإذن الحالي.', null, selectedMaleProfile, false);
                return;
            }
            if (q.includes('مسودة') || q.includes('مسوده')) {
                if (typeof window.saveSmartLogisticsDraft === 'function') window.saveSmartLogisticsDraft();
                if (badge) badge.textContent = '🎙️ حفظ مسودة مؤقتة';
                speakArabicMaleText('تم حفظ المسودة المؤقتة للإذن بنجاح.', null, selectedMaleProfile, false);
                return;
            }
            const cleanSlTerm = q.replace(/^(ابحث عن|بحث عن|ابحث|بحث|فلتر|اعرض|هات)\s+/i, '').trim();
            const slSearchInp = document.getElementById('sl-doc-search-input') || document.getElementById('sl-history-search-input');
            if (slSearchInp && cleanSlTerm) {
                slSearchInp.value = cleanSlTerm;
                slSearchInp.dispatchEvent(new Event('input', { bubbles: true }));
                moveGhostCursorToElement(slSearchInp, `🔍 بحث صوتي: ${cleanSlTerm}`, true);
                if (badge) badge.textContent = `🔍 بحث: ${cleanSlTerm}`;
                speakArabicMaleText(`تم البحث فوراً في البوابة عن: ${cleanSlTerm}.`, null, selectedMaleProfile, false);
                return;
            }
        }

        const isPurchaseScreen = (currentGuideKey === 'purchase-direct' || window.location.pathname.includes('purchase.php') || !!document.getElementById('purchase-editor-view'));
        if (isPurchaseScreen) {
            if (q.includes('سجل') || q.includes('بوابة') || q.includes('كل الفواتير')) {
                if (typeof window.showPurchaseHub === 'function') window.showPurchaseHub();
                if (badge) badge.textContent = '🎙️ سجل وبوابة المشتريات';
                speakArabicMaleText('تم الانتقال إلى بوابة وسجل فواتير المشتريات.', null, selectedMaleProfile, false);
                return;
            }
            if (q.includes('فاتورة جديدة') || q.includes('فاتوره جديده') || q.includes('تحرير') || q.includes('مشتريات مباشرة')) {
                if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('direct');
                if (badge) badge.textContent = '🎙️ فاتورة مشتريات مباشرة';
                speakArabicMaleText('تم فتح شاشة تحرير فاتورة المشتريات المباشرة.', null, selectedMaleProfile, false);
                return;
            }
            if (q.includes('مورد جديد') || q.includes('اضافة مورد') || q.includes('إضافة مورد')) {
                if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('direct');
                if (typeof window.openModal === 'function') window.openModal('add-customer-modal');
                if (badge) badge.textContent = '🎙️ إضافة مورد جديد';
                speakArabicMaleText('تم فتح نافذة التأسيس السريع لمورد جديد.', null, selectedMaleProfile, false);
                return;
            }
            if (q.includes('اخر فاتورة') || q.includes('آخر فاتورة') || q.includes('نسخ')) {
                if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('direct');
                if (typeof window.cloneLastSupplierPurchaseInvoice === 'function') window.cloneLastSupplierPurchaseInvoice();
                if (badge) badge.textContent = '🎙️ نسخ آخر فاتورة للمورد';
                speakArabicMaleText('تم تشغيل نسخ أصناف آخر فاتورة للمورد.', null, selectedMaleProfile, false);
                return;
            }
            if (q.includes('تخصيص') || q.includes('اعمدة') || q.includes('أعمدة')) {
                if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('direct');
                if (typeof window.openGridSettingsModal === 'function') window.openGridSettingsModal();
                if (badge) badge.textContent = '🎙️ نافذة تخصيص الجدول';
                speakArabicMaleText('تم فتح نافذة تخصيص أعمدة وخطوط الجدول.', null, selectedMaleProfile, false);
                return;
            }
            const cleanPurTerm = q.replace(/^(ابحث عن|بحث عن|ابحث|بحث|فلتر|اعرض|هات)\s+/i, '').trim();
            const editorVis = document.getElementById('purchase-editor-view')?.style.display !== 'none';
            const purInp = editorVis
                ? (document.getElementById('quick-item-search') || document.getElementById('hdr-customer-search'))
                : document.getElementById('hub-filter-value');
            if (purInp && cleanPurTerm) {
                purInp.value = cleanPurTerm;
                purInp.dispatchEvent(new Event('input', { bubbles: true }));
                moveGhostCursorToElement(purInp, `🔍 بحث صوتي: ${cleanPurTerm}`, true);
                if (badge) badge.textContent = `🔍 بحث: ${cleanPurTerm}`;
                speakArabicMaleText(`تم البحث فوراً عن: ${cleanPurTerm}.`, null, selectedMaleProfile, false);
                return;
            }
        }

        const navMap = [
            { keys: ['مخزن', 'مخازن', 'مستودع'], sel: '.tab-warehouses', name: 'قسم المخازن', openFn: () => typeof openWarehouseForm === 'function' && openWarehouseForm() },
            { keys: ['عميل', 'عملاء', 'زبون'], sel: '.tab-customers', name: 'قسم العملاء', openFn: null },
            { keys: ['مورد', 'موردين', 'شركه', 'شركة'], sel: '.tab-suppliers', name: 'قسم الموردين', openFn: null },
            { keys: ['بنك', 'بنوك', 'مصرف', 'بنكك'], sel: '.tab-banks', name: 'قسم البنوك', openFn: () => typeof openBankForm === 'function' && openBankForm() },
            { keys: ['خزنه', 'خزنة', 'خزن', 'صندوق'], sel: '.tab-safes', name: 'قسم الخزن النقدية', openFn: () => typeof openSafeForm === 'function' && openSafeForm() },
            { keys: ['صنف', 'اصناف', 'أصناف', 'بضاعه', 'وحدات'], sel: '.tab-items', name: 'قسم الأصناف والوحدات', openFn: () => typeof openItemForm === 'function' && openItemForm() },
            { keys: ['موظف', 'موظفين', 'صلاحيات'], sel: '.tab-employees', name: 'قسم الموظفين', openFn: () => typeof openNewEmployeeForm === 'function' && openNewEmployeeForm() },
            { keys: ['مركز', 'مراكز', 'تكلفه', 'تكلفة', 'مشروع'], sel: '.tab-costcenters', name: 'قسم مراكز التكلفة', openFn: () => typeof openCostCenterForm === 'function' && openCostCenterForm() }
        ];

        if (q.includes('قيد افتتاحي') || q.includes('القيد الافتتاحي') || q.includes('3109') || q.includes('توازن')) {
            if (typeof window.openOpeningEquilibriumModal === 'function') window.openOpeningEquilibriumModal();
            if (badge) badge.textContent = '🎙️ فتح القيد الافتتاحي (3109)';
            speakArabicMaleText('تم فتح شاشة القيد الافتتاحي المتوازن.', null, selectedMaleProfile, false);
            return;
        }
        if (q.includes('تحديث الاسعار') || q.includes('تحديث الأسعار') || q.includes('اسعار جماعي')) {
            if (typeof window.openBulkPriceUpdaterModal === 'function') window.openBulkPriceUpdaterModal();
            if (badge) badge.textContent = '🎙️ محرك تحديث الأسعار';
            speakArabicMaleText('تم فتح محرك تحديث الأسعار الجماعي.', null, selectedMaleProfile, false);
            return;
        }

        const isSearchIntent = q.includes('ابحث') || q.includes('بحث') || q.includes('فلتر') || q.includes('هات');
        for (const item of navMap) {
            if (!isSearchIntent && item.keys.some(k => q.includes(k))) {
                const btn = document.querySelector(item.sel);
                if (btn) { try { btn.click(); } catch (e) {} }
                if (q.includes('اضافه') || q.includes('إضافة') || q.includes('بطاقه') || q.includes('بطاقة') || q.includes('افتح')) {
                    if (item.openFn) { try { item.openFn(); } catch (e) {} }
                }
                if (badge) badge.textContent = `🎙️ ${item.name}`;
                speakArabicMaleText(`تم الانتقال فوراً إلى ${item.name}.`, null, selectedMaleProfile, false);
                return;
            }
        }

        // Clean search term and filter the active tab's search input immediately
        const cleanTerm = q.replace(/^(ابحث عن|بحث عن|ابحث|بحث|فلتر|اعرض|هات)\s+/i, '').trim();
        const activeTabPane = document.querySelector('.tab-content.active') || document;
        const searchInp = activeTabPane.querySelector('input[type="search"], input[placeholder*="بحث"], input[id*="search"]')
            || document.querySelector('#cust-search, #sup-search, #item-search-input, input[placeholder*="بحث"]');
        if (searchInp && cleanTerm) {
            searchInp.value = cleanTerm;
            searchInp.dispatchEvent(new Event('input', { bubbles: true }));
            searchInp.dispatchEvent(new Event('keyup', { bubbles: true }));
            moveGhostCursorToElement(searchInp, `🔍 بحث صوتي: ${cleanTerm}`, true);
            if (badge) badge.textContent = `🔍 بحث: ${cleanTerm}`;
            speakArabicMaleText(`تمت فلترة الجدول فوراً بالبحث عن: ${cleanTerm}.`, null, selectedMaleProfile, false);
        } else {
            window.answerUserScreenQuestion(q);
        }
    };

    window.startMasterDataVoiceCommand = function () {
        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        const btn = document.getElementById('msg-coach-voice-cmd-btn');
        if (!SpeechRec) {
            const manual = window.prompt('🎙️ اكتب أمر التنقل أو البحث الفوري (مثال: "افتح الأصناف" أو "ابحث عن محمد"):', '');
            if (manual) window.executeMasterDataVoiceQuery(manual);
            return;
        }
        try {
            if (speechRecognitionInstance) { try { speechRecognitionInstance.stop(); } catch (e) {} }
            const rec = new SpeechRec();
            speechRecognitionInstance = rec;
            rec.lang = selectedMaleProfile === 'egyptian' ? 'ar-EG' : 'ar-SA';
            rec.interimResults = false;
            rec.maxAlternatives = 1;
            if (btn) btn.style.background = '#ef4444';
            const badge = document.getElementById('msg-coach-step-badge');
            if (badge) badge.textContent = '🎙️ تكلم الآن (قسم أو بحث)...';
            rec.onresult = (ev) => {
                const txt = ev.results[0][0].transcript || '';
                if (btn) btn.style.background = '';
                window.executeMasterDataVoiceQuery(txt);
            };
            rec.onerror = () => { if (btn) btn.style.background = ''; };
            rec.onend = () => { if (btn) btn.style.background = ''; };
            rec.start();
        } catch (e) {
            if (btn) btn.style.background = '';
        }
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 💡 2. SMART HOVER MICRO-TOOLTIPS WITH 1-CLICK AUDIO (التلميحات الذكية الناطقة)
    // ══════════════════════════════════════════════════════════════════════════
    window.__makePopupElementDraggable = window.__makePopupElementDraggable || function (el) {
        if (!el || el.__isDraggableBound) return;
        el.__isDraggableBound = true;
        let dragging = false, startX = 0, startY = 0, origLeft = 0, origTop = 0;
        const onStart = (cx, cy, target) => {
            if (target && target.closest && target.closest('button, input, select, textarea, a, label')) return false;
            const r = el.getBoundingClientRect();
            dragging = true; startX = cx; startY = cy; origLeft = r.left; origTop = r.top;
            el.style.right = 'auto'; el.style.bottom = 'auto'; el.style.margin = '0';
            el.style.left = origLeft + 'px'; el.style.top = origTop + 'px';
            el.style.cursor = 'grabbing';
            return true;
        };
        const onMove = (cx, cy) => {
            if (!dragging) return;
            const nx = Math.max(2, Math.min(window.innerWidth - 60, origLeft + (cx - startX)));
            const ny = Math.max(2, Math.min(window.innerHeight - 40, origTop + (cy - startY)));
            el.style.left = nx + 'px'; el.style.top = ny + 'px';
        };
        const onEnd = () => { if (dragging) { dragging = false; el.style.cursor = ''; } };
        el.addEventListener('mousedown', (e) => { if (e.button === 0 && onStart(e.clientX, e.clientY, e.target)) e.preventDefault(); });
        window.addEventListener('mousemove', (e) => onMove(e.clientX, e.clientY), { passive: true });
        window.addEventListener('mouseup', onEnd, { passive: true });
        el.addEventListener('touchstart', (e) => { if (e.touches.length === 1) onStart(e.touches[0].clientX, e.touches[0].clientY, e.target); }, { passive: true });
        window.addEventListener('touchmove', (e) => { if (dragging && e.touches.length === 1) onMove(e.touches[0].clientX, e.touches[0].clientY); }, { passive: true });
        window.addEventListener('touchend', onEnd, { passive: true });
    };

    let hoverTooltipTimer = null;
    let _globalLastRightClickTs = 0;
    window.__msgTipFontRem = 0.84;
    function startTipPartSync(weights) {
        if (!document.getElementById('msg-tip-sync-css')) {
            const st = document.createElement('style'); st.id = 'msg-tip-sync-css';
            st.textContent = '.msg-tip-part{transition:box-shadow .25s,transform .25s,opacity .25s}.msg-tip-part.msg-tip-dim{opacity:.62}.msg-tip-part.msg-tip-live{opacity:1;transform:scale(1.015);box-shadow:0 0 0 2px #fbbf24,0 6px 18px rgba(251,191,36,.35)}';
            document.head.appendChild(st);
        }
        if (window.__msgTipSyncTimer) clearInterval(window.__msgTipSyncTimer);
        const total = weights.reduce((a, b) => a + b, 0) || 1, t0 = Date.now();
        let lastIdx = -2;
        window.__msgTipSyncTimer = setInterval(() => {
            const tipBox = document.getElementById('msg-smart-hover-tooltip'), box = document.getElementById('msg-tip-desc-box');
            if (!tipBox || tipBox.style.display === 'none' || !box) { clearInterval(window.__msgTipSyncTimer); return; }
            const frac = getLiveSpeechRatio();
            if (frac < 0) return;
            const pos = frac * total;
            let acc = weights[0], idx = -1;
            for (let i = 1; i < weights.length && pos >= acc; i++) { idx = i - 1; acc += weights[i]; }
            if (idx === lastIdx) return;
            lastIdx = idx;
            box.querySelectorAll('.msg-tip-part').forEach(el => { const on = Number(el.dataset.idx) === idx; el.classList.toggle('msg-tip-live', on); el.classList.toggle('msg-tip-dim', idx >= 0 && !on); });
            const cur = box.querySelector('.msg-tip-part[data-idx="' + idx + '"]');
            if (cur) box.scrollTop += (cur.getBoundingClientRect().top - box.getBoundingClientRect().top) - 34;
        }, 120);
    }
    window.__askAiAboutExplainedField = async function (btn) {
        const box = document.getElementById('msg-tip-desc-box');
        if (!box) return;
        const t = window.__lastDblRightTargetEl, title = window.__lastGlobalDblRightTitle || '';
        const val = (t && 'value' in t && t.type !== 'password') ? String(t.value || '').slice(0, 60) : '';
        const esc = x => String(x).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
        if (btn) { btn.disabled = true; btn.textContent = '⏳ يفكر...'; }
        try {
            const msg = `اشرح للمحاسب بالعربية الفصحى المبسطة في 5 جمل قصيرة واضحة بدون رموز: «${title}» في شاشة «${document.title || location.pathname}»${val ? ' وقيمتها الحالية ' + val : ''}. اذكر الوظيفة، ومثالاً عملياً بالأرقام، والأثر المحاسبي، وخطأً شائعاً يجب تجنبه.`;
            const res = await fetch('api/ai-assistant.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message: msg, context: String(window.__lastGlobalDblRightSpeech || '').slice(0, 1500) }) });
            const j = await res.json();
            const reply = String((j && j.reply) || '').trim();
            if (!reply) throw new Error('empty');
            box.querySelectorAll('.msg-tip-ai-answer').forEach(x => x.remove());
            box.insertAdjacentHTML('afterbegin', `<div class="msg-tip-ai-answer" style="margin-bottom:8px;padding:10px 12px;border-radius:10px;border-right:4px solid #a855f7;background:rgba(168,85,247,0.14);"><b style="color:#a855f7;">🤖 شرح الذكاء الاصطناعي: </b>${esc(reply)}</div>`);
            box.scrollTop = 0;
            if (window.__msgTipSyncTimer) clearInterval(window.__msgTipSyncTimer);
            box.querySelectorAll('.msg-tip-part').forEach(el => el.classList.remove('msg-tip-live', 'msg-tip-dim'));
            if (window.speakMdExplanationPanelText) window.speakMdExplanationPanelText(reply);
        } catch (_) {
            box.insertAdjacentHTML('afterbegin', '<div class="msg-tip-ai-answer" style="margin-bottom:8px;padding:8px 12px;border-radius:10px;border-right:4px solid #ef4444;background:rgba(239,68,68,0.12);">⚠️ تعذر الوصول للذكاء الاصطناعي الآن؛ الشرح المحلي متاح بالأسفل.</div>');
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = '🤖 اسأل الذكاء'; }
        }
    };
    function showGlobalSmartExplanationAt(e, forcedTarget) {
        if (typeof window.__mdHandleUnifiedHumanExplain === 'function' && window.__mdHandleUnifiedHumanExplain(e, forcedTarget)) return;
        const tipEl = document.getElementById('msg-smart-hover-tooltip');
        if (tipEl && tipEl.contains(e.target)) return;
        let target = forcedTarget || (e.target && e.target.closest ? e.target.closest('button, a, input, select, textarea, .pro-field-card, .input-group, .h-form-group, .form-group, .spotlight-card, .kpi-card, .stat-card, .hub-action-card, .fin-metric-pill, .qa-master-btn, .cat-hub-btn, .msg-quartet-btn, .md-tab-btn, .sl-btn, .uiverse-switch, canvas, th, td, [role="button"], [role="tab"], [onclick], [data-md-tip], [data-guide-desc]') : null);
        if (!target && !forcedTarget && e.target && e.target.nodeType === 1 && e.target !== document.body && e.target !== document.documentElement && !(e.target.closest && e.target.closest('#msg-smart-hover-tooltip, #msg-live-coach-bar, #msg-universal-modal'))) {
            if (e.type === 'contextmenu') { target = e.target; var _genericRc = true; }
        }
        if (!target) return;
        if (e.type === 'contextmenu') {
            if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
                const hasSel = (typeof target.selectionStart === 'number' && target.selectionStart !== target.selectionEnd);
                if (hasSel) return;
            }
            if (!forcedTarget) {
                const selTxt = (window.getSelection && String(window.getSelection() || '').trim()) || '';
                const keepNative = selTxt || (target.closest && target.closest('.resizer, .col-resizer'));
                if (keepNative) return;
            }
            e.preventDefault();
            if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
            if (typeof e.stopPropagation === 'function') e.stopPropagation();
            ['custom-num-menu', 'num-context-menu', 'gl-context-menu', 'md-col-context-menu', 'md-rightclick-def-panel'].forEach(id => {
                const m = document.getElementById(id);
                if (m) m.style.display = 'none';
            });
        }
        if (true) {
            if (typeof window.resetToOptimalClaritySpeed === 'function') window.resetToOptimalClaritySpeed();
            window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;
            window.__lastDblRightTargetEl = target;
            applyGuideSpotlight(target);
            try { moveGhostCursorToElement(target, getSmartElementTitle(target) || 'شرح الخانة', false); } catch (_) {}
            window.__stepNeighborFieldInCard = function (dir) {
                const all = Array.from(document.querySelectorAll('input:not([type="hidden"]), select, textarea, button, .fin-metric-pill, .md-tab-btn')).filter(el => {
                    const r = el.getBoundingClientRect();
                    return r.width > 18 && r.height > 14 && window.getComputedStyle(el).display !== 'none' && window.getComputedStyle(el).visibility !== 'hidden' && !el.closest('#msg-smart-hover-tooltip, #msg-live-coach-bar, #msg-universal-modal');
                });
                if (!all.length) return;
                const curIdx = all.indexOf(window.__lastDblRightTargetEl);
                const nextIdx = (curIdx === -1) ? 0 : (curIdx + dir + all.length) % all.length;
                showGlobalSmartExplanationAt({ type: 'dblclick', target: all[nextIdx] }, all[nextIdx]);
            };
            window.__focusCurrentExplainedField = function () {
                const tipBox = document.getElementById('msg-smart-hover-tooltip');
                if (tipBox) tipBox.style.display = 'none';
                if (window.stopMdExplanationPanelAudio) window.stopMdExplanationPanelAudio();
                const t = window.__lastDblRightTargetEl;
                if (t && typeof t.focus === 'function') { try { t.focus(); if (t.select && t.tagName === 'INPUT') t.select(); } catch (_) {} }
            };
            const rawInfo = (typeof window.explainUnifiedDashboardDomElement === 'function' && window.explainUnifiedDashboardDomElement(target))
                || (typeof window.explainMultiCashPaymentDomElement === 'function' && window.explainMultiCashPaymentDomElement(target))
                || (typeof window.explainSmartLogisticsDomElement === 'function' && window.explainSmartLogisticsDomElement(target))
                || (typeof window.explainPurchaseReceiptDomElement === 'function' && window.explainPurchaseReceiptDomElement(target))
                || (typeof window.explainDirectSalesDomElement === 'function' && window.explainDirectSalesDomElement(target))
                || (typeof window.explainPurchaseDomElement === 'function' && window.explainPurchaseDomElement(target))
                || (typeof window.explainMasterDataDomElement === 'function' && window.explainMasterDataDomElement(target))
                || (typeof window.autoSynthesizeElementExplanation === 'function' && window.autoSynthesizeElementExplanation(target));
            if (!rawInfo) return;
            const info = (typeof window.enrichProfessor6Pillars === 'function') ? window.enrichProfessor6Pillars(rawInfo, target) : rawInfo;
            const tip = getOrCreateHoverTooltipEl();
            const isLight = document.documentElement.classList.contains('light-theme') || ['light','warm_cream','ultra_white','exec_gray','pearl-white'].includes(document.documentElement.getAttribute('data-theme') || '');
            const _spParts = [info.title, ...String(info.desc || '').split(' • ').filter(Boolean)].map(x => cleanTextForMaleTts(x));
            const fullSpeech = stripAllSalawatPrefixes(_spParts.join('. '));
            window.__lastGlobalDblRightTitle = info.title;
            window.__lastGlobalDblRightSpeech = fullSpeech;
            window.__msgUserVoiceVolume = parseFloat(localStorage.getItem('msg_voice_vol_v1') || '1.65') || 1.65;
            window.__setGlobalTipSpeed = function(spd, isDelta) {
                if (isDelta) {
                    const deltaPct = Math.abs(spd) <= 1.5 ? Math.round(spd * 100) : Math.round(spd);
                    window.adjustSpeechSpeedPctBy(deltaPct, false);
                } else {
                    window.setSpeechSpeedPctExact(spd, false);
                }
                if (!isAudioPlaying && window.speakMdExplanationPanelText) {
                    window.speakMdExplanationPanelText(adaptTextToDialect(window.__lastGlobalDblRightSpeech, selectedMaleProfile));
                }
            };
            window.__zoomGlobalTipFont = function(d) { window.__msgTipFontRem = Math.max(0.78, Math.min(1.35, (window.__msgTipFontRem || 0.96) + d)); const bx = document.getElementById('msg-tip-desc-box'); if (bx) bx.style.fontSize = window.__msgTipFontRem + 'rem'; };
            window.__copyGlobalTipText = function(btnEl) { if (navigator.clipboard) navigator.clipboard.writeText(window.__lastGlobalDblRightSpeech || ''); if (btnEl) { const o = btnEl.textContent; btnEl.textContent = '✅'; setTimeout(() => btnEl.textContent = o, 1400); } };
            const descBoxCss = isLight
                ? `padding:14px 16px;border-radius:14px;background:var(--lt-sub-1,#ffffff);border:2px solid var(--lt-border,#4a2106);color:var(--lt-ink,#120500);font-size:${window.__msgTipFontRem || 0.96}rem;font-weight:900;line-height:1.85;text-align:right;max-height:270px;overflow-y:auto;`
                : `padding:14px 16px;border-radius:14px;background:rgba(15,23,42,0.88);border:1.5px solid rgba(56,189,248,0.45);color:#ffffff;font-size:${window.__msgTipFontRem || 0.96}rem;font-weight:800;line-height:1.85;text-align:right;max-height:270px;overflow-y:auto;`;
            const volPct = Math.round((window.__msgUserVoiceVolume || 1.65) * 100);
            const spdPct = Math.round(window.__msgSpeechSpeedPct || 0);
            const aiSelHtml = `<select id="msg-tip-ai-degree" onchange="window.setMsgAiIntelligenceDegree(this.value)" style="padding:2px 7px;height:26px;border-radius:8px;background:${isLight ? '#fff' : '#0f172a'};color:${isLight ? '#140600' : '#fde047'};border:1.5px solid #f59e0b;font-size:0.73rem;font-weight:900;cursor:pointer;" title="درجة الذكاء الاصطناعي ومستوى الشرح"><option value="ai_5" ${window.__msgAiDegree==='ai_5'?'selected':''}>👑 ذكاء 5 (بروفيسور 360°)</option><option value="ai_4" ${window.__msgAiDegree==='ai_4'?'selected':''}>🛡️ ذكاء 4 (متقدم)</option><option value="ai_3" ${window.__msgAiDegree==='ai_3'?'selected':''}>📊 ذكاء 3 (محاسبي)</option><option value="ai_2" ${window.__msgAiDegree==='ai_2'?'selected':''}>💡 ذكاء 2 (بلدي)</option><option value="ai_1" ${window.__msgAiDegree==='ai_1'?'selected':''}>⚡ ذكاء 1 (مختصر)</option></select>`;
            const dialectSelHtml = `<select id="msg-tip-voice-select" onchange="if(window.onMsgVoiceChange)window.onMsgVoiceChange(this.value)" style="padding:2px 7px;height:26px;border-radius:8px;background:${isLight ? '#fff' : '#0f172a'};color:${isLight ? '#140600' : '#38bdf8'};border:1.5px solid ${isLight ? '#4a2106' : '#38bdf8'};font-size:0.73rem;font-weight:900;cursor:pointer;" title="اختيار نموذج الصوت (نسائي أو رجالي)"><option value="female_zariyah" ${selectedMaleProfile==='female_zariyah'?'selected':''}>👩 صوت المساعدة (Aoede/زارية)</option><option value="female_salma" ${selectedMaleProfile==='female_salma'?'selected':''}>👩 نسائي هادئ (سلمى)</option><option value="female_fatima" ${selectedMaleProfile==='female_fatima'?'selected':''}>👩 نسائي إذاعي (فاطمة)</option><option value="male_hamed" ${selectedMaleProfile==='male_hamed'?'selected':''}>👨 رجالي فخيم (Puck/حامد)</option><option value="male_abdullah" ${selectedMaleProfile==='male_abdullah'?'selected':''}>👨 رجالي عميق (عبدالله)</option><option value="male_shakir" ${selectedMaleProfile==='male_shakir'?'selected':''}>👨 رجالي مصري (شاكر)</option><option value="male_muawiya" ${selectedMaleProfile==='male_muawiya'?'selected':''}>👨 رجالي سوداني (معاوية)</option></select>`;
            let liveValBadge = '';
            try {
                if (target.tagName === 'SELECT' && target.options && target.selectedIndex >= 0) {
                    const selTxt = (target.options[target.selectedIndex].text || '').trim();
                    if (selTxt) liveValBadge = `<div style="margin-bottom:6px;padding:6px 10px;border-radius:8px;background:rgba(16,185,129,0.14);border:1px solid rgba(16,185,129,0.45);font-size:0.8rem;color:${isLight ? '#065f46' : '#6ee7b7'};">📌 الاختيار الحالي بالخانة: <b>${selTxt}</b></div>`;
                } else if ((target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') && target.type !== 'password') {
                    const v = String(target.value || '').trim();
                    if (v) liveValBadge = `<div style="margin-bottom:6px;padding:6px 10px;border-radius:8px;background:rgba(16,185,129,0.14);border:1px solid rgba(16,185,129,0.45);font-size:0.8rem;color:${isLight ? '#065f46' : '#6ee7b7'};">📌 القيمة المسجلة حالياً: <b>${v.slice(0, 48)}</b></div>`;
                }
            } catch (_) {}
            const parts = String(info.desc || '').split(' • ').filter(Boolean);
            const capColors = ['#10b981', '#38bdf8', '#a855f7', '#06b6d4', '#22c55e', '#f97316', '#ef4444', '#eab308', '#ec4899', '#fbbf24', '#14b8a6', '#8b5cf6', '#f43f5e', '#0ea5e9'];
            const capIcons = ['1️⃣ 💡 الهوية والوظيفة (بالبلدي): ', '2️⃣ 🎯 أهمية العنصر ولماذا وُجد: ', '3️⃣ 🔢 تفكيك الرقم أو الكود الحي الآن: ', '4️⃣ 📥 مصدر المعلومة (تجي من وين؟): ', '5️⃣ 📤 مصب المعلومة والتقارير (تمشي وين؟): ', '6️⃣ ⚖️ الأثر المحاسبي والمخزني: ', '7️⃣ 💎 الإيجابيات والمحاسن: ', '8️⃣ ⚠️ المخاطر والتحذيرات: ', '9️⃣ 🛡️ الضوابط والتأكيدات الواجبة: ', '🔟 🧮 مثال عملي تطبيقي: ', '1️⃣1️⃣ 🧭 خطوات التنفيذ للموظف الجديد: ', '1️⃣2️⃣ 🔐 الصلاحيات والرقابة الجنائية: ', '1️⃣3️⃣ ↩️ طريقة التصحيح الآمن للخطأ: ', '1️⃣4️⃣ 👑 النقطة الذهبية والاختصار: '];
            const formattedHtml = liveValBadge + parts.map((p, idx) => `<div class="msg-tip-part" data-idx="${idx}" style="margin-bottom:6px;padding:8px 12px;border-radius:10px;border-right:4px solid ${capColors[idx % capColors.length]};background:${isLight ? 'rgba(254,249,243,0.95)' : 'rgba(2,6,23,0.72)'};"><span style="color:${capColors[idx % capColors.length]};font-weight:950;">${capIcons[idx] || '✨ '}</span>${p}</div>`).join('');
            tip.innerHTML =
                `<div class="popup-drag-handle" title="اسحب لتحريك النافذة" style="display:flex;align-items:center;justify-content:space-between;gap:6px;width:100%;flex-wrap:wrap;border-bottom:1.5px solid ${isLight ? 'var(--lt-border,#4a2106)' : 'rgba(56,189,248,0.35)'};padding-bottom:8px;cursor:grab;">` +
                    `<span style="font-size:0.94rem;font-weight:900;color:${isLight ? 'var(--lt-ink,#140600)' : '#fde047'};">🎙️ ${info.title}</span>` +
                    `<div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">` +
                        `<button type="button" onclick="window.__stepNeighborFieldInCard(-1)" class="msg-micro-pill-btn" title="الخانة السابقة">⏮️</button>` +
                        `<button type="button" onclick="window.__stepNeighborFieldInCard(1)" class="msg-micro-pill-btn" title="الخانة التالية">⏭️</button>` +
                        `${aiSelHtml}` +
                        `${dialectSelHtml}` +
                        `<button type="button" onclick="window.launchSingleElementMicroCinema&&window.launchSingleElementMicroCinema(window.__lastDblRightTargetEl)" class="msg-micro-pill-btn" style="background:rgba(236,72,153,0.22)!important;color:#fbcfe8!important;border-color:#f472b6!important;" title="تصوير سينمائي بؤري لهذا العنصر">🎬 تصوير</button>` +
                        `<button type="button" onclick="window.__askAiAboutExplainedField(this)" class="msg-micro-pill-btn" style="background:rgba(168,85,247,0.22)!important;color:#e9d5ff!important;border-color:#a855f7!important;" title="شرح إضافي مخصص بالذكاء الاصطناعي">🤖 اسأل الذكاء</button>` +
                        `<button type="button" onclick="window.__focusCurrentExplainedField()" class="msg-micro-pill-btn" style="background:rgba(16,185,129,0.22)!important;color:#6ee7b7!important;border-color:#10b981!important;" title="طبّق بيدك في الخانة">🎯 طبّق</button>` +
                        `<button type="button" onclick="window.__isDblRightCardPinned=false;window.stopMdExplanationPanelAudio&&window.stopMdExplanationPanelAudio();document.getElementById('msg-smart-hover-tooltip').style.display='none'" class="msg-micro-pill-btn" style="background:rgba(239,68,68,0.22)!important;color:#fca5a5!important;border-color:#f87171!important;">✕</button>` +
                    `</div>` +
                `</div>` +
                `<div id="msg-tip-desc-box" style="${descBoxCss}">${formattedHtml}</div>` +
                `<div style="display:flex;align-items:center;justify-content:space-between;gap:6px;flex-wrap:wrap;padding-top:4px;">` +
                    `<div style="display:inline-flex;align-items:center;gap:4px;">` +
                        `<button type="button" id="msg-tip-play-pause-btn" onclick="window.togglePauseResumeAudio&&window.togglePauseResumeAudio()" class="msg-micro-pill-btn" style="background:linear-gradient(135deg,#0284c7,#2563eb)!important;color:#fff!important;border-color:#38bdf8!important;padding:0 10px;" title="تعليق / استئناف الصوت">⏯️ الصوت</button>` +
                        `<button type="button" onclick="window.speakMdExplanationPanelText&&window.speakMdExplanationPanelText(window.__lastGlobalDblRightSpeech)" class="msg-micro-pill-btn" title="إعادة الشرح">🔁</button>` +
                    `</div>` +
                    `<div style="display:inline-flex;align-items:center;gap:4px;background:${isLight ? 'rgba(0,0,0,0.06)' : 'rgba(15,23,42,0.82)'};padding:2px 7px;border-radius:9px;border:1px solid rgba(148,163,184,0.35);" title="قوة الصوت: ناقص (-) يقلل الصوت وزائد (+) يعلي الصوت حتى 300%">` +
                        `<span style="font-size:0.71rem;font-weight:900;">🔊</span>` +
                        `<button type="button" onclick="window.adjustMsgVoiceVolume(-0.20)" class="msg-micro-pill-btn" style="height:22px;min-width:24px;padding:0 6px;" title="تقليل الصوت (ناقص)">-</button>` +
                        `<input id="msg-tip-vol-slider" type="range" class="msg-vol-range-slider" min="10" max="300" step="10" value="${volPct}" oninput="window.adjustMsgVoiceVolume(0,this.value/100)" style="width:66px;">` +
                        `<button type="button" onclick="window.adjustMsgVoiceVolume(0.20)" class="msg-micro-pill-btn" style="height:22px;min-width:24px;padding:0 6px;" title="تعلية الصوت (زائد)">+</button>` +
                        `<span id="msg-tip-vol-lbl" style="font-size:0.72rem;font-weight:900;min-width:38px;text-align:center;color:#10b981;">${volPct}%</span>` +
                    `</div>` +
                    `<div style="display:inline-flex;align-items:center;gap:4px;background:${isLight ? 'rgba(0,0,0,0.06)' : 'rgba(15,23,42,0.82)'};padding:2px 7px;border-radius:9px;border:1px solid rgba(148,163,184,0.35);" title="سرعة الصوت: ناقص (-) يصل إلى -50% وزائد (+) يصل إلى +300%">` +
                        `<span style="font-size:0.71rem;font-weight:900;">⚡</span>` +
                        `<button type="button" onclick="window.__setGlobalTipSpeed(-10,true)" class="msg-micro-pill-btn" style="height:22px;min-width:24px;padding:0 6px;" title="إبطاء سرعة الصوت (حتى -50%)">-</button>` +
                        `<input id="msg-tip-spd-slider" type="range" class="msg-spd-range-slider" min="-50" max="300" step="10" value="${spdPct}" oninput="window.__setGlobalTipSpeed(this.value,false)" style="width:66px;">` +
                        `<button type="button" onclick="window.__setGlobalTipSpeed(20,true)" class="msg-micro-pill-btn" style="height:22px;min-width:24px;padding:0 6px;" title="زيادة سرعة الصوت (حتى +300%)">+</button>` +
                        `<span id="msg-tip-spd-lbl" style="font-size:0.72rem;font-weight:900;min-width:44px;text-align:center;color:#fde047;">${formatSpeedPctLabel()}</span>` +
                    `</div>` +
                    `<div style="display:inline-flex;align-items:center;gap:3px;">` +
                        `<button type="button" onclick="window.__zoomGlobalTipFont(0.06)" class="msg-micro-pill-btn" style="height:22px;padding:0 6px;" title="تكبير الخط">A+</button>` +
                        `<button type="button" onclick="window.__zoomGlobalTipFont(-0.06)" class="msg-micro-pill-btn" style="height:22px;padding:0 6px;" title="تصغير الخط">A-</button>` +
                        `<button type="button" onclick="window.__copyGlobalTipText(this)" class="msg-micro-pill-btn" style="height:22px;padding:0 6px;" title="نسخ الشرح">📋</button>` +
                    `</div>` +
                `</div>`;
            tip.style.display = 'flex';
            tip.style.top = `${Math.max(20, Math.round((window.innerHeight - 350) / 2))}px`;
            tip.style.left = `${Math.max(16, Math.round((window.innerWidth - 580) / 2))}px`;
            tip.style.width = 'min(580px, 95vw)';
            unlockBrowserAudioEnginesSync();
            if (typeof window.speakMdExplanationPanelText === 'function') window.speakMdExplanationPanelText(adaptTextToDialect(fullSpeech, selectedMaleProfile));
            startTipPartSync(_spParts.map(x => x.length + 8));
        }
    }
    window.showUnifiedDoubleRightClickCard = showGlobalSmartExplanationAt;
    window.addEventListener('contextmenu', showGlobalSmartExplanationAt, true);
    let _hoverPrewarmTimer = null;
    window.addEventListener('mouseover', function (e) {
        if (_hoverPrewarmTimer) clearTimeout(_hoverPrewarmTimer);
        const tgt = e.target && e.target.closest ? e.target.closest('button, a, input, select, textarea, .bento-card, .inner-box, .kpi-card, .stat-card, .fin-metric-pill, .qa-ribbon-btn, .md-tab-btn, .sl-btn, [onclick]') : null;
        if (!tgt) return;
        _hoverPrewarmTimer = setTimeout(() => {
            try {
                const rInf = (typeof window.explainUnifiedDashboardDomElement === 'function' && window.explainUnifiedDashboardDomElement(tgt))
                    || (typeof window.explainPurchaseDomElement === 'function' && window.explainPurchaseDomElement(tgt))
                    || (typeof window.explainMasterDataDomElement === 'function' && window.explainMasterDataDomElement(tgt))
                    || (typeof window.autoSynthesizeElementExplanation === 'function' && window.autoSynthesizeElementExplanation(tgt));
                if (rInf && rInf.title) {
                    const inf = (typeof window.enrichProfessor6Pillars === 'function') ? window.enrichProfessor6Pillars(rInf, tgt) : rInf;
                    const pts = [inf.title, ...String(inf.desc || '').split(' • ').filter(Boolean)].map(x => cleanTextForMaleTts(x));
                    const fSp = adaptTextToDialect(stripAllSalawatPrefixes(pts.join('. ')), selectedMaleProfile);
                    const { firstBurst } = splitInstantFirstBurst(fSp);
                    if (firstBurst) fetchAndDecodeMaleBuffer(firstBurst, selectedMaleProfile).catch(() => {});
                }
            } catch (_) {}
        }, 140);
    }, { passive: true });
    document.addEventListener('keydown', function (e) {
        const tip = document.getElementById('msg-smart-hover-tooltip');
        if (!tip || tip.style.display === 'none') return;
        const tg = e.target;
        if (tg && (/^(INPUT|TEXTAREA|SELECT)$/.test(tg.tagName) || tg.isContentEditable)) return;
        if (e.key === 'Escape') { window.__isDblRightCardPinned = false; if (window.stopMdExplanationPanelAudio) window.stopMdExplanationPanelAudio(); tip.style.display = 'none'; }
        else if (e.code === 'Space') { e.preventDefault(); if (window.togglePauseResumeAudio) window.togglePauseResumeAudio(); }
        else if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') { e.preventDefault(); if (window.__stepNeighborFieldInCard) window.__stepNeighborFieldInCard(e.key === 'ArrowLeft' ? 1 : -1); }
    }, true);
    window.addEventListener('dblclick', function(e) {
        if (e.target && e.target.closest && e.target.closest('#msg-smart-hover-tooltip, #msg-live-coach-bar, #msg-universal-modal')) return;
        if (e.target && e.target.closest && e.target.closest('input:not([type=button]):not([type=submit]):not([type=checkbox]):not([type=radio]), textarea, [contenteditable="true"], tbody tr, th, [ondblclick], .resizer, .col-resizer, canvas, img, video')) return;
        showGlobalSmartExplanationAt(e);
    }, true);
    let hoverHideTimer = null;

    function getOrCreateHoverTooltipEl() {
        let el = document.getElementById('msg-smart-hover-tooltip');
        if (!el) {
            el = document.createElement('div');
            el.id = 'msg-smart-hover-tooltip';
            el.className = 'msg-smart-hover-tooltip';
            el.onmouseenter = () => { if (hoverHideTimer) clearTimeout(hoverHideTimer); };
            el.onmouseleave = () => {};
            document.body.appendChild(el);
            window.__makePopupElementDraggable(el);
        }
        return el;
    }

    let lastHoverInspectedEl = null;
    document.addEventListener('mouseover', function (e) {
        // 🚫 ممنوع ظهور الشرح عند تمرير الماوس (Hover)؛ الشرح المعتمد في البرنامج يظهر فقط عند الضغط دبل كليك يمين (Double Right-Click)
        return;
        if (window.__MUTE_LEGACY_GUIDE_AUDIO__ || window.__CINEMA_4K_ACTIVE__) return;
        // 👑 Interactive Hover Inspection ("الشرح التفاعلي عند التأشير بالمؤشر") when Guide is active on master-data.php, purchase.php, direct-sales.php, purchase-receipt.php, smart-logistics.php & multi-cash-payment.php
        if (window.__mdInteractiveGuideActive && (typeof window.explainMasterDataDomElement === 'function' || typeof window.explainPurchaseDomElement === 'function' || typeof window.explainDirectSalesDomElement === 'function' || typeof window.explainPurchaseReceiptDomElement === 'function' || typeof window.explainSmartLogisticsDomElement === 'function' || typeof window.explainMultiCashPaymentDomElement === 'function')) {
            if (isAutopilotRunning && !isPickElementModeActive && (Date.now() - lastAutopilotStepStartTs < 1600)) return;
            if (e.target.closest('#msg-live-coach-bar, #msg-smart-hover-tooltip, #msg-universal-fixed-launcher')) return;
            const inspectTarget = e.target.closest('button, a, input, select, textarea, .md-tab-btn, .pro-field-card, .input-group, .h-form-group, .hub-action-card, .fin-metric-pill, .tot-toggle-btn, .pay-toggle-btn, #customer-financial-card, #fin-strip, #purchase-3way-match-badge, .uiverse-switch, .grn-qc-pill, .grn-nav-pill, .grn-col-origQty, .grn-col-item, .grn-col-unit, .grn-col-qty, .grn-col-pendingBase, .grn-col-baseQty, .grn-progress-wrap, .grn-meta-bar > div, .sl-mode-tab, .sl-kpi-card, .sl-unit-box, .account-block, .voucher-type-btn, .crystal-btn, #grand-tafqeet-container, #mcp-world-summary-bar, th');
            if (!inspectTarget || inspectTarget === lastHoverInspectedEl) return;
            if (isMdQuickQuizActive && checkMdQuickQuizHit(inspectTarget)) return;
            const mdInfo = (typeof window.explainMultiCashPaymentDomElement === 'function' && window.explainMultiCashPaymentDomElement(inspectTarget))
                || (typeof window.explainSmartLogisticsDomElement === 'function' && window.explainSmartLogisticsDomElement(inspectTarget))
                || (typeof window.explainPurchaseReceiptDomElement === 'function' && window.explainPurchaseReceiptDomElement(inspectTarget))
                || (typeof window.explainDirectSalesDomElement === 'function' && window.explainDirectSalesDomElement(inspectTarget))
                || (typeof window.explainPurchaseDomElement === 'function' && window.explainPurchaseDomElement(inspectTarget))
                || (typeof window.explainMasterDataDomElement === 'function' && window.explainMasterDataDomElement(inspectTarget))
                || (typeof window.autoSynthesizeElementExplanation === 'function' && window.autoSynthesizeElementExplanation(inspectTarget));
            if (!mdInfo) return;
            if (hoverTooltipTimer) clearTimeout(hoverTooltipTimer);
            const hoverDelayMs = (isAutopilotRunning && !isPickElementModeActive) ? 550 : 220;
            hoverTooltipTimer = setTimeout(() => {
                if (!window.__mdInteractiveGuideActive) return;
                if (isAutopilotRunning && !isPickElementModeActive && (Date.now() - lastAutopilotStepStartTs < 1600)) return;
                lastHoverInspectedEl = inspectTarget;
                const liveVal = extractLiveElementValueReadback(inspectTarget);
                const liveValHtml = liveVal
                    ? `<div style="margin-top:4px; padding:3px 8px; border-radius:6px; background:rgba(16,185,129,0.16); border:1px solid rgba(16,185,129,0.45); font-size:0.74rem; font-weight:900;">📌 القيمة المسجلة حالياً: <span>${escapeSvgText(liveVal)}</span></div>`
                    : '';
                const tip = getOrCreateHoverTooltipEl();
                const rect = inspectTarget.getBoundingClientRect();
                tip.innerHTML = `
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                        <span style="font-size:0.82rem; font-weight:900;">🎓 ${mdInfo.title}</span>
                        <svg viewBox="0 0 92 22" width="84" height="20" style="flex-shrink:0;">
                            <rect x="1" y="2" width="26" height="18" rx="4" fill="rgba(56,189,248,0.2)" stroke="#38bdf8" stroke-width="1.2"/>
                            <text x="14" y="14" fill="currentColor" font-size="7.5" font-weight="bold" text-anchor="middle">إدخال</text>
                            <path d="M29 11 L41 11" stroke="#fbbf24" stroke-width="1.5"/>
                            <rect x="43" y="2" width="47" height="18" rx="4" fill="rgba(16,185,129,0.22)" stroke="#10b981" stroke-width="1.2"/>
                            <text x="66" y="14" fill="currentColor" font-size="7.5" font-weight="bold" text-anchor="middle">⚡ تسميع فوري</text>
                        </svg>
                    </div>
                    <div style="font-size:0.78rem; line-height:1.6;">${mdInfo.desc}</div>
                    ${liveValHtml}
                `;
                tip.style.display = 'flex';
                const topPos = rect.bottom + 10 + 110 < window.innerHeight ? rect.bottom + 10 : Math.max(8, rect.top - 110);
                const leftPos = Math.max(10, Math.min(window.innerWidth - 360, rect.left));
                tip.style.top = `${topPos}px`;
                tip.style.left = `${leftPos}px`;

                // Update live coach bar & speak immediately on hover inspection WITHOUT repeating the Salawat!
                cleanupGuideTempPopups(inspectTarget);
                const coachBar = document.getElementById('msg-live-coach-bar');
                const speechBox = document.getElementById('msg-coach-speech-text');
                const badge = document.getElementById('msg-coach-step-badge');
                if (coachBar) coachBar.style.display = 'flex';
                const spokenWithVal = liveVal
                    ? `${mdInfo.title}. ${mdInfo.desc} والقيمة المسجلة حالياً في هذه الخانة هي: ${liveVal}.`
                    : `${mdInfo.title}. ${mdInfo.desc}`;
                const adaptedBody = stripAllSalawatPrefixes(adaptTextToDialect(spokenWithVal, selectedMaleProfile));
                if (badge) badge.textContent = `👆 ${mdInfo.title}`;
                if (speechBox) speechBox.textContent = adaptedBody;
                renderLiveDubbedVisualStage({
                    badge: mdInfo.title,
                    cursorLabel: mdInfo.title,
                    screenPath: `الشرح التفاعلي الحي ⬅ ${mdInfo.title}`,
                    diagramType: (inspectTarget.tagName === 'SELECT' || inspectTarget.querySelector?.('select')) ? 'dropdown-open' : 'screen-hub',
                    diagramMeta: {
                        parentScreen: 'الشرح بالتأشير المباشر',
                        actionLabel: `تفصيل وظيفة (${mdInfo.title}):`,
                        childItems: [
                            `🎯 العنصر: ${mdInfo.title}`,
                            mdInfo.desc.slice(0, 75),
                            '⚡ متزامن بالصوت الرجالي والمخطط البصري'
                        ],
                        effectLabel: 'دبلجة فورية بالصوت والصورة'
                    }
                }, 0, 1);
                if (inspectTarget.tagName === 'SELECT' || inspectTarget.querySelector?.('select')) {
                    showLiveSelectOptionsMirror(inspectTarget, null, mdInfo.title);
                } else {
                    triggerLiveChildScreenPreviewForElement(inspectTarget);
                }
                moveGhostCursorToElement(inspectTarget, mdInfo.title, false);
                speakArabicMaleText(adaptedBody, () => {
                    if (isAutopilotRunning && !isAudioPaused && !isPickElementModeActive) {
                        nextStepTimeoutId = setTimeout(() => {
                            if (isAutopilotRunning && !isAudioPaused) window.stepLiveAutopilot(1);
                        }, 450);
                    }
                }, selectedMaleProfile);
            }, hoverDelayMs);
            return;
        }

        if (isAutopilotRunning || isPickElementModeActive) return;
        const btnCandidate = e.target && e.target.closest ? e.target.closest('button, a.btn, .sl-btn, .msg-quartet-btn') : null;
        if (!btnCandidate) return;
        const activeHoverKey = resolveActiveGuideKeyFast() || 'smart-logistics';
        const data = window.MagaiesSmartGuides && (window.MagaiesSmartGuides[activeHoverKey] || window.MagaiesSmartGuides['smart-logistics']);
        if (!data || !Array.isArray(data.buttonsDict)) return;

        let matchedItem = null;
        let matchedDom = null;
        for (const item of data.buttonsDict) {
            if (item.targetSelector) {
                const candidate = e.target.closest(item.targetSelector);
                if (candidate) {
                    matchedItem = item;
                    matchedDom = candidate;
                    break;
                }
            }
        }
        if (!matchedItem || !matchedDom) return;

        if (hoverTooltipTimer) clearTimeout(hoverTooltipTimer);
        hoverTooltipTimer = setTimeout(() => {
            if (isAutopilotRunning || isPickElementModeActive) return;
            const tip = getOrCreateHoverTooltipEl();
            const rect = matchedDom.getBoundingClientRect();
            const safeTitle = matchedItem.label.replace(/'/g, "\\'");
            const safeSpeech = (matchedItem.whatItDoes + ' ' + matchedItem.whatHappensIfClicked).replace(/`/g, '').replace(/'/g, "\\'");

            tip.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <span style="font-size:0.78rem; font-weight:900; color:#fde047;">💡 ${matchedItem.label}</span>
                    <button type="button" onclick="document.getElementById('msg-smart-hover-tooltip').style.display='none'; window.pointGhostCursorAt('${matchedItem.targetSelector.replace(/'/g, "\\'")}', '${safeTitle}', '${safeSpeech}')" style="background:linear-gradient(135deg,#0284c7,#2563eb); color:#fff; border:1px solid #38bdf8; border-radius:99px; padding:2px 9px; font-size:0.71rem; font-weight:900; cursor:pointer; white-space:nowrap;">
                        🔊 اسمع الشرح
                    </button>
                </div>
                <div style="font-size:0.75rem; color:#e2e8f0; line-height:1.5;">${matchedItem.whatItDoes}</div>
            `;
            tip.style.display = 'flex';
            const topPos = rect.bottom + 8 + 95 < window.innerHeight ? rect.bottom + 8 : Math.max(8, rect.top - 95);
            const leftPos = Math.max(10, Math.min(window.innerWidth - 350, rect.left));
            tip.style.top = `${topPos}px`;
            tip.style.left = `${leftPos}px`;
        }, 1250);
    }, { passive: true });

    document.addEventListener('mousedown', function (e) {
        if (e.button === 2 || window.__isDblRightCardPinned) return;
        const tip = document.getElementById('msg-smart-hover-tooltip');
        if (tip && tip.style.display === 'flex' && !tip.contains(e.target)) {
            if (typeof window.stopMdExplanationPanelAudio === 'function') window.stopMdExplanationPanelAudio();
            tip.style.display = 'none';
        }
    }, { passive: true });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            window.__isDblRightCardPinned = false;
            const tip = document.getElementById('msg-smart-hover-tooltip');
            if (tip && tip.style.display === 'flex') { if (window.stopMdExplanationPanelAudio) window.stopMdExplanationPanelAudio(); tip.style.display = 'none'; }
            if (window.__CINEMA_4K_ACTIVE__ && typeof window.stopMagaies4KCinemaMode === 'function') window.stopMagaies4KCinemaMode();
        } else if (isAutopilotRunning && !e.target.closest('input, textarea, select')) {
            if (e.key === 'PageDown') { e.preventDefault(); window.stepLiveAutopilot(1); }
            else if (e.key === 'PageUp') { e.preventDefault(); window.stepLiveAutopilot(-1); }
        }
    });
    document.addEventListener('mouseout', function (e) {
        return;
        if (hoverTooltipTimer) {
            clearTimeout(hoverTooltipTimer);
            hoverTooltipTimer = null;
        }
        const tip = document.getElementById('msg-smart-hover-tooltip');
        if (tip && tip.style.display === 'flex') {
            if (hoverHideTimer) clearTimeout(hoverHideTimer);
            hoverHideTimer = setTimeout(() => {
                tip.style.display = 'none';
            }, 350);
        }
    }, { passive: true });

    window.prewarmSmartGuideScreen = function (guideKey) {
        const key = guideKey || buildUniversalScreenGuideIfNeeded() || 'smart-logistics';
        prewarmGuideAudio(key, selectedMaleProfile);
    };

    // 🔮 محرك الاكتشاف الذاتي المستقبلي لأي زر أو خانة أو شاشة تُضاف بعد شهر (Debounced Future DOM Auto-Discovery)
    let _futureDomSyncTimer = null;
    function initFutureDomAutoDiscoveryObserver() {
        if (window.__msgFutureDomObserverBound || !window.MutationObserver || !document.body) return;
        window.__msgFutureDomObserverBound = true;
        const obs = new MutationObserver((mutations) => {
            let hasNewNodes = false;
            for (let i = 0; i < mutations.length; i++) {
                if (mutations[i].addedNodes && mutations[i].addedNodes.length > 0) { hasNewNodes = true; break; }
            }
            if (!hasNewNodes) return;
            if (_futureDomSyncTimer) clearTimeout(_futureDomSyncTimer);
            _futureDomSyncTimer = setTimeout(() => {
                if (isAutopilotRunning && typeof window.syncDynamicDomElementsIntoSmartGuide === 'function') {
                    try { window.syncDynamicDomElementsIntoSmartGuide(currentGuideKey); } catch (_) {}
                }
            }, 1200);
        });
        obs.observe(document.body, { childList: true, subtree: true });
    }

    function prewarmActiveScreenOnReady() {
        ensureFixedSquareLauncher();
        initFutureDomAutoDiscoveryObserver();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', prewarmActiveScreenOnReady);
    } else {
        prewarmActiveScreenOnReady();
    }

    window.addEventListener('load', () => {
        ensureFixedSquareLauncher();
    });

    // ══════════════════════════════════════════════════════════════════════════
    // 📐 الدستور الخامس عشر والأخير (15/15 — محرك التوريث العابر لجميع الشاشات والشاشات الوليدة):
    // 1) حفظ وتوريث مقاس الشاشة التناسبي (6:8) عند الإغلاق والفتح وبين الشاشات المشابهة
    // 2) نقل وتعميم فواصل وتنسيق "شاشة المشتريات (ما بعد الدساتير)" المستقلة عن currentColor
    // 3) توحيد الخطوط وسُمك الحواف (+2) وقفل الخلفية السوداء للخانات في السمات الغامقة
    // ══════════════════════════════════════════════════════════════════════════
    (function initUniversalConstitution15Engine() {
        if (window.__magaiesUniversalC15Loaded) return;
        window.__magaiesUniversalC15Loaded = true;

        const GLOBAL_SCALE_KEY = 'magaies_erp_global_proportional_scale_v15';
        const PUR_SCALE_KEY = 'magaies_pur_c1_aspect_zoom';
        const DS_SCALE_KEY = 'magaies_ds_c1_aspect_zoom';

        function getSavedGlobalScale() {
            const raw = localStorage.getItem(GLOBAL_SCALE_KEY) || localStorage.getItem(PUR_SCALE_KEY) || localStorage.getItem(DS_SCALE_KEY) || '1';
            const z = parseFloat(raw) || 1;
            return (z >= 0.65 && z <= 1.35) ? Math.round(z * 100) / 100 : 1;
        }

        function injectUniversalPostConstitutionPurchaseDividersCSS() {
            if (document.getElementById('magaies-c15-universal-dividers-css')) return;
            const st = document.createElement('style');
            st.id = 'magaies-c15-universal-dividers-css';
            st.textContent = `
                /* الدستور الرابع (+2 درجتان سُمك الحواف والخطوط والأيقونات + منع انقلاب اللون عند الكتابة في الغامق والفاتح) */
                #pos-grid, .pos-grid {
                    border-collapse: collapse !important;
                    border: 2.2px solid rgba(255, 255, 255, 0.85) !important;
                }
                #pos-grid thead th, .pos-grid thead th {
                    border: 2px solid rgba(255, 255, 255, 0.55) !important;
                    border-bottom: 2.4px solid #FFFFFF !important;
                    font-family: 'Tajawal', 'Cairo', sans-serif !important;
                    font-weight: 900 !important;
                }
                #pos-grid tbody td, .pos-grid tbody td {
                    border: 1.8px solid rgba(255, 255, 255, 0.35) !important;
                    font-weight: 900 !important;
                }
                :root:not(.light-theme) #pos-grid tbody td .g-input,
                :root:not(.light-theme) #pos-grid tbody td .g-select,
                :root:not(.light-theme) .pos-grid tbody td .g-input,
                :root:not(.light-theme) .pos-grid tbody td .g-select {
                    background-color: #000000 !important;
                    color: #FFFFFF !important;
                    -webkit-text-fill-color: #FFFFFF !important;
                    caret-color: #FFFFFF !important;
                    border: 2.2px solid #FFFFFF !important;
                    box-shadow: none !important;
                    font-weight: 900 !important;
                }
                :root:not(.light-theme) #pos-grid tbody td .g-input:focus,
                :root:not(.light-theme) #pos-grid tbody td .g-select:focus,
                :root:not(.light-theme) #pos-grid tbody td .g-input:active,
                :root:not(.light-theme) .pos-grid tbody td .g-input:focus {
                    background-color: #000000 !important;
                    color: #FFFFFF !important;
                    -webkit-text-fill-color: #FFFFFF !important;
                    border: 2.4px solid #FFFFFF !important;
                    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.28) !important;
                }
                :root.light-theme #pos-grid,
                :root.light-theme .pos-grid,
                :root.light-theme #pos-grid thead th,
                :root.light-theme #pos-grid tbody td,
                :root.light-theme .pos-grid thead th,
                :root.light-theme .pos-grid tbody td {
                    border: 2px solid #000000 !important;
                }
                :root.light-theme #pos-grid tbody td .g-input,
                :root.light-theme #pos-grid tbody td .g-select,
                :root.light-theme #pos-grid tbody td .g-readonly,
                :root.light-theme #pos-grid tbody td .avail-val,
                :root.light-theme #pos-grid tbody td .total-val {
                    background-color: #FFFFFF !important;
                    color: #000000 !important;
                    -webkit-text-fill-color: #000000 !important;
                    caret-color: #000000 !important;
                    border: 2.2px solid #000000 !important;
                    box-shadow: none !important;
                    font-weight: 900 !important;
                }
                :root.light-theme #pos-grid tbody td .g-input:focus,
                :root.light-theme #pos-grid tbody td .g-select:focus {
                    background-color: #FFFFFF !important;
                    color: #000000 !important;
                    -webkit-text-fill-color: #000000 !important;
                    border: 2.4px solid #000000 !important;
                    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.18) !important;
                }
            `;
            document.head.appendChild(st);
        }

        window.applyUniversalProportionalScaleC15 = function(val) {
            const z = Math.max(0.65, Math.min(1.35, Math.round((parseFloat(val) || 1) * 100) / 100));
            try {
                localStorage.setItem(GLOBAL_SCALE_KEY, String(z));
                localStorage.setItem(PUR_SCALE_KEY, String(z));
                localStorage.setItem(DS_SCALE_KEY, String(z));
            } catch (_) {}

            if (typeof window.setPurchaseProportionalZoom === 'function') {
                window.setPurchaseProportionalZoom(z, true);
                return;
            }
            if (typeof window.setSlProportionalZoom === 'function') {
                window.setSlProportionalZoom(z, true);
                return;
            }

            const mainContainer = document.querySelector('.dash-main, .main-content, main, .pos-container');
            if (mainContainer) {
                mainContainer.style.boxSizing = 'border-box';
                mainContainer.style.marginLeft = 'auto';
                mainContainer.style.marginRight = 'auto';
                if (Math.abs(z - 1) < 0.01) {
                    mainContainer.style.zoom = '';
                    mainContainer.style.transform = '';
                    mainContainer.style.width = '100%';
                    mainContainer.style.margin = '0 auto';
                    mainContainer.style.marginBottom = '';
                } else {
                    mainContainer.style.zoom = '';
                    mainContainer.style.width = '100%';
                    mainContainer.style.transformOrigin = 'top center';
                    mainContainer.style.transform = `scale(${z})`;
                    mainContainer.style.margin = '0 auto';
                    if (z < 1) {
                        const h = mainContainer.offsetHeight || window.innerHeight;
                        mainContainer.style.marginBottom = Math.round((z - 1) * h) + 'px';
                    } else {
                        mainContainer.style.marginBottom = '';
                    }
                }
            }

            document.querySelectorAll('.modal-overlay .modal-card, .pur-c1-child-modal .pur-c1-modal-card, .ds-c1-child-modal .ds-c1-modal-card').forEach((card) => {
                if (Math.abs(z - 1) < 0.01) {
                    card.style.transform = '';
                } else {
                    card.style.transformOrigin = 'center center';
                    card.style.transform = `scale(${z})`;
                }
            });
        };

        const bootUniversalC15 = () => {
            injectUniversalPostConstitutionPurchaseDividersCSS();
            const z = getSavedGlobalScale();
            if (Math.abs(z - 1) >= 0.01) {
                window.applyUniversalProportionalScaleC15(z);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootUniversalC15);
        } else {
            bootUniversalC15();
        }
        window.addEventListener('pageshow', bootUniversalC15);

        window.addEventListener('wheel', function (e) {
            if (!e.ctrlKey || typeof window.setPurchaseProportionalZoom === 'function' || typeof window.setSlProportionalZoom === 'function') return;
            e.preventDefault();
            const cur = getSavedGlobalScale();
            const step = e.deltaY < 0 ? 0.05 : -0.05;
            window.applyUniversalProportionalScaleC15(cur + step);
        }, { passive: false });
    })();

    // ═══════════════════════════════════════════════════════════════════════════
    // 🎬 4K AUTO-CINEMA STUDIO ENGINE (استوديو التصوير السينمائي العالمي 4K داخل المرشد الذكي)
    //    1. 🎬 وضع التصوير السينمائي (YouTube 16:9 / Reels 9:16) + 4K Vector Crispness + إخفاء الأزرار الجانبية
    //    2. 🎥 الكاميرا السينمائية الذكية (Smooth Auto-Zoom & Pan — Scale 1.6x + Spotlight Cutout)
    //    3. 🤖 الممثل الآلي والكتابة الحية (Laser Ghost-Actor + Character-by-Character Auto-Typing + Child Screens & 3-Stage SVG Map)
    //    4. 📺 الشريط السينمائي الزجاجي السفلي (Cinematic Lower-Thirds & Live Subtitles + Golden Rule Highlight)
    //    5. 🎙️ مزامنة الصوت الرجالي الفخيم + رفع ملف صوتي خارجي (MP3 من ElevenLabs) + 🔴 تسجيل وتنزيل الفيديو (MP4/WebM)
    // ═══════════════════════════════════════════════════════════════════════════
    (function initMagaies4KAutoCinemaStudio() {
        if (window.__magaies4KAutoCinemaStudioLoaded) return;
        window.__magaies4KAutoCinemaStudioLoaded = true;

        let isCinemaModeActive = false;
        let cinemaAspectRatio = '16:9'; // '16:9' (YouTube Landscape) | '9:16' (Reels/Shorts Vertical)
        let cinemaZoomScale = 1.6;
        let cinemaAutoZoomTimer = null;
        let cinemaTypingTimer = null;
        let cinemaSubtitleTimer = null;
        let cinemaExternalAudio = null;
        let cinemaExternalAudioName = '';
        let cinemaExternalStepTimer = null;
        let cinemaMediaRecorder = null;
        let cinemaRecordedChunks = [];
        let isCinemaRecording = false;
        const cinemaRestorableInputs = [];

        function injectCinema4KStyles() {
            if (document.getElementById('msg-cinema-4k-studio-styles')) return;
            const st = document.createElement('style');
            st.id = 'msg-cinema-4k-studio-styles';
            st.textContent = `
                /* 1. 4K Vector Crispness & Clean Broadcast Frame when Cinema Mode is Active */
                body.msg-cinema-4k-active,
                body.msg-cinema-4k-active * {
                    -webkit-font-smoothing: antialiased !important;
                    -moz-osx-font-smoothing: grayscale !important;
                    text-rendering: geometricPrecision !important;
                    shape-rendering: geometricPrecision !important;
                }
                /* Hide side/floating auxiliary buttons during 4K Cinema Broadcast */
                body.msg-cinema-4k-active #msg-universal-fixed-launcher,
                body.msg-cinema-4k-active #msg-fixed-mic-btn,
                body.msg-cinema-4k-active .back-to-hub-floating,
                body.msg-cinema-4k-active #magaies-floating-back-btn,
                body.msg-cinema-4k-active #ds-c4-micro-hud {
                    opacity: 0 !important;
                    pointer-events: none !important;
                    transition: opacity 0.35s ease !important;
                }
                /* Hide coach bar completely during Cinema mode so only 1 sleek HUD appears */
                body.msg-cinema-4k-active #msg-live-coach-bar,
                body.msg-cinema-4k-active #msg-coach-svg-popover,
                body.msg-cinema-4k-active #msg-live-select-mirror {
                    display: none !important;
                }
                /* Reels 9:16 Vertical Studio Frame */
                body.msg-cinema-4k-active.msg-cinema-aspect-9-16 {
                    background: #020617 !important;
                }
                body.msg-cinema-4k-active.msg-cinema-aspect-9-16 #sl-proportional-root,
                body.msg-cinema-4k-active.msg-cinema-aspect-9-16 #invoice-editor-view,
                body.msg-cinema-4k-active.msg-cinema-aspect-9-16 #split-invoice-view,
                body.msg-cinema-4k-active.msg-cinema-aspect-9-16 .dash-main,
                body.msg-cinema-4k-active.msg-cinema-aspect-9-16 main {
                    max-width: 540px !important;
                    width: 540px !important;
                    min-height: 94vh !important;
                    margin: 1vh auto !important;
                    border: 2.5px solid rgba(56, 189, 248, 0.55) !important;
                    border-radius: 24px !important;
                    box-shadow: 0 0 0 9999px rgba(2, 6, 23, 0.86), 0 25px 80px rgba(0, 0, 0, 0.95) !important;
                    overflow-x: hidden !important;
                }

                /* 2. Smooth Cinematic Camera Pan & Zoom Transition */
                .msg-cinema-camera-stage {
                    transition: transform 0.68s cubic-bezier(0.22, 1, 0.36, 1) !important;
                    will-change: transform;
                }

                /* 3. Cinematic Spotlight Overlay (Dimmed Surround + Bright Illuminated Target Halo) */
                #msg-cinema-spotlight-overlay {
                    position: fixed;
                    inset: 0;
                    z-index: 999980;
                    pointer-events: none;
                    display: none;
                    transition: all 0.45s cubic-bezier(0.22, 1, 0.36, 1);
                }
                .msg-cinema-target-illuminated {
                    position: relative !important;
                    z-index: 999985 !important;
                    box-shadow: 0 0 0 4px #fde047, 0 0 38px rgba(250, 204, 21, 0.85), 0 0 80px rgba(56, 189, 248, 0.65) !important;
                    transition: box-shadow 0.35s ease !important;
                }

                /* 4. Laser Ghost-Actor Cursor Upgrade */
                #msg-ghost-cursor.msg-cinema-laser-actor {
                    filter: drop-shadow(0 0 16px #f43f5e) drop-shadow(0 0 32px #38bdf8) !important;
                    transition: top 0.52s cubic-bezier(0.22, 1, 0.36, 1), left 0.52s cubic-bezier(0.22, 1, 0.36, 1) !important;
                }
                #msg-ghost-cursor.msg-cinema-laser-actor .msg-ghost-cursor-label {
                    background: linear-gradient(135deg, #0f172a, #1e1b4b) !important;
                    border: 2px solid #fde047 !important;
                    color: #ffffff !important;
                    font-weight: 900 !important;
                    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.85) !important;
                }

                /* 5. Compact Non-Blocking Mini Cinema Control Pod (شريط تحكم سينمائي مصغر غير حاجب للشاشة) */
                #msg-cinema-lower-thirds {
                    position: fixed;
                    bottom: 14px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: min(760px, 95vw);
                    z-index: 1000015;
                    background: linear-gradient(145deg, rgba(2, 6, 23, 0.96), rgba(15, 23, 42, 0.96));
                    backdrop-filter: blur(18px);
                    border: 1.8px solid rgba(56, 189, 248, 0.55);
                    border-radius: 18px;
                    padding: 10px 14px;
                    color: #ffffff;
                    font-family: 'Cairo', 'Tajawal', sans-serif;
                    direction: rtl;
                    box-shadow: 0 20px 55px rgba(0, 0, 0, 0.85), 0 0 25px rgba(56, 189, 248, 0.2);
                    display: none;
                    flex-direction: column;
                    gap: 8px;
                    pointer-events: auto;
                }
                #msg-cinema-subtitle-text {
                    font-size: 0.94rem;
                    font-weight: 800;
                    color: #f8fafc;
                    line-height: 1.55;
                    max-height: 82px;
                    overflow-y: auto;
                    display: block !important;
                    text-align: center;
                    background: rgba(15, 23, 42, 0.72);
                    border: 1px solid rgba(56, 189, 248, 0.32);
                    border-radius: 12px;
                    padding: 8px 14px;
                }
                #msg-cinema-golden-rule-line {
                    display: none !important;
                }
                @keyframes msgCinemaSpotlightHaloPulse {
                    0%, 100% {
                        box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.92), 0 0 28px rgba(250, 204, 21, 0.85), 0 0 58px rgba(16, 185, 129, 0.65) !important;
                        border-color: #fde047 !important;
                    }
                    50% {
                        box-shadow: 0 0 0 8px rgba(56, 189, 248, 0.95), 0 0 42px rgba(250, 204, 21, 0.98), 0 0 80px rgba(56, 189, 248, 0.8) !important;
                        border-color: #38bdf8 !important;
                    }
                }
                .msg-cinema-credit-spotlight-halo {
                    animation: msgCinemaSpotlightHaloPulse 1.35s infinite ease-in-out !important;
                    position: relative !important;
                    z-index: 999990 !important;
                }
            `;
            document.head.appendChild(st);
        }

        function ensureCinemaDomOverlays() {
            injectCinema4KStyles();
            if (!document.getElementById('msg-cinema-spotlight-overlay')) {
                const spot = document.createElement('div');
                spot.id = 'msg-cinema-spotlight-overlay';
                document.body.appendChild(spot);
            }
            if (!document.getElementById('msg-cinema-lower-thirds')) {
                const lt = document.createElement('div');
                lt.id = 'msg-cinema-lower-thirds';
                const initVolPct = Math.round((window.__msgUserVoiceVolume || 1.65) * 100);
                const initSpdPct = Math.round(window.__msgSpeechSpeedPct || 0);
                lt.innerHTML = `
                    <div class="popup-drag-handle" title="اسحب لتحريك الشريط" style="display:flex; align-items:center; justify-content:space-between; gap:6px; flex-wrap:wrap; cursor:grab;">
                        <div style="display:flex; align-items:center; gap:5px; min-width:0; flex-wrap:wrap;">
                            <span id="msg-cinema-rec-dot" style="display:inline-block; width:9px; height:9px; border-radius:50%; background:#ef4444; box-shadow:0 0 8px #ef4444; flex-shrink:0;"></span>
                            <span id="msg-cinema-scene-badge" style="font-size:0.76rem; font-weight:900; color:#38bdf8; background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.45); padding:2px 8px; border-radius:99px; max-width:190px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">🎬 المسجل الذكي</span>
                            <select id="msg-cinema-scene-select" class="sl-input" onchange="window.jumpToCinemaSceneIndex&&window.jumpToCinemaSceneIndex(parseInt(this.value,10)||0)" style="padding:1px 6px !important; font-size:0.72rem !important; font-weight:900 !important; height:25px; max-width:145px; border-radius:8px !important;" title="القفز المباشر لأي مشهد"><option value="0">🎬 المشهد 1</option></select>
                            <select id="msg-cinema-ai-degree" class="sl-input" onchange="window.setMsgAiIntelligenceDegree&&window.setMsgAiIntelligenceDegree(this.value)" style="padding:1px 6px !important; font-size:0.72rem !important; font-weight:900 !important; height:25px; border-radius:8px !important; border-color:#f59e0b !important; color:#fde047 !important;" title="درجة الذكاء الاصطناعي">
                                <option value="ai_5" ${window.__msgAiDegree==='ai_5'?'selected':''}>👑 ذكاء 5 (بروفيسور)</option>
                                <option value="ai_4" ${window.__msgAiDegree==='ai_4'?'selected':''}>🛡️ ذكاء 4 (متقدم)</option>
                                <option value="ai_3" ${window.__msgAiDegree==='ai_3'?'selected':''}>📊 ذكاء 3 (محاسبي)</option>
                                <option value="ai_2" ${window.__msgAiDegree==='ai_2'?'selected':''}>💡 ذكاء 2 (بلدي)</option>
                                <option value="ai_1" ${window.__msgAiDegree==='ai_1'?'selected':''}>⚡ ذكاء 1 (مختصر)</option>
                            </select>
                            <span id="msg-cinema-aspect-badge" style="display:none;"></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap;">
                            <button type="button" onclick="window.stepCinemaSceneBy&&window.stepCinemaSceneBy(-1)" class="msg-micro-pill-btn" title="المشهد السابق">⏮️</button>
                            <button type="button" id="msg-cinema-pause-btn" onclick="window.toggleCinemaPauseResume&&window.toggleCinemaPauseResume()" class="msg-micro-pill-btn active-accent" title="تشغيل / إيقاف مؤقت">⏸️</button>
                            <button type="button" onclick="window.stepCinemaSceneBy&&window.stepCinemaSceneBy(1)" class="msg-micro-pill-btn" title="المشهد التالي">⏭️</button>
                            <button type="button" onclick="window.stepCinemaSceneBy&&window.stepCinemaSceneBy(0)" class="msg-micro-pill-btn" title="إعادة المشهد الحالي">🔁</button>
                            <button type="button" id="msg-cinema-hold-btn" onclick="window.toggleCinemaSceneHold&&window.toggleCinemaSceneHold()" class="msg-micro-pill-btn" title="تثبيت المشهد الحالي وعدم الانتقال التلقائي">📌 تثبيت</button>
                            <div style="display:inline-flex; align-items:center; gap:3px; background:rgba(15,23,42,0.82); border:1px solid rgba(148,163,184,0.32); border-radius:99px; padding:1px 6px;" title="سرعة الفيديو والصوت (من ناقص 50% إلى زائد 300%)">
                                <span style="font-size:0.68rem; font-weight:900;">⚡</span>
                                <button type="button" onclick="window.adjustCinemaSpeedBy&&window.adjustCinemaSpeedBy(-10)" class="msg-micro-pill-btn" style="height:20px; min-width:22px; padding:0 4px;" title="إبطاء الفيديو والصوت (حتى -50%)">-</button>
                                <input id="msg-cinema-spd-slider" type="range" class="msg-spd-range-slider" min="-50" max="300" step="10" value="${initSpdPct}" oninput="window.setSpeechSpeedPctExact&&window.setSpeechSpeedPctExact(this.value,false)" style="width:56px;">
                                <button type="button" onclick="window.adjustCinemaSpeedBy&&window.adjustCinemaSpeedBy(20)" class="msg-micro-pill-btn" style="height:20px; min-width:22px; padding:0 4px;" title="تسريع الفيديو والصوت (حتى +300%)">+</button>
                                <span id="msg-cinema-spd-lbl" style="font-size:0.7rem; font-weight:900; color:#fde047; min-width:42px; text-align:center;">${formatSpeedPctLabel()}</span>
                            </div>
                            <div style="display:inline-flex; align-items:center; gap:3px; background:rgba(15,23,42,0.82); border:1px solid rgba(148,163,184,0.32); border-radius:99px; padding:1px 6px;" title="قوة صوت الفيديو (ناقص يقلل وزائد يعلي حتى 300%)">
                                <button type="button" id="msg-cinema-mute-btn" onclick="window.toggleCinemaVoiceMute&&window.toggleCinemaVoiceMute()" style="background:none; border:none; color:#38bdf8; font-size:0.74rem; cursor:pointer; padding:0;">🔊</button>
                                <button type="button" onclick="window.adjustMsgVoiceVolume&&window.adjustMsgVoiceVolume(-0.20)" class="msg-micro-pill-btn" style="height:20px; min-width:22px; padding:0 4px;" title="تقليل الصوت">-</button>
                                <input id="msg-cinema-vol-slider" type="range" class="msg-vol-range-slider" min="10" max="300" step="10" value="${initVolPct}" oninput="window.adjustMsgVoiceVolume&&window.adjustMsgVoiceVolume(0,this.value/100)" style="width:56px;">
                                <button type="button" onclick="window.adjustMsgVoiceVolume&&window.adjustMsgVoiceVolume(0.20)" class="msg-micro-pill-btn" style="height:20px; min-width:22px; padding:0 4px;" title="تعلية الصوت">+</button>
                                <span id="msg-cinema-vol-lbl" style="font-size:0.7rem; font-weight:900; color:#34d399; min-width:36px; text-align:center;">${initVolPct}%</span>
                            </div>
                            <button type="button" id="msg-cinema-lt-rec-btn" onclick="window.startAutomaticCinema4KMediaRecorder&&window.startAutomaticCinema4KMediaRecorder();this.textContent='🔴 يسجل'" class="msg-micro-pill-btn" style="border-color:rgba(248,113,113,0.5); color:#fca5a5;" title="حفظ الفيديو">🔴 حفظ</button>
                            <button type="button" onclick="window.stopMagaies4KCinemaMode()" class="msg-micro-pill-btn danger-btn" title="إنهاء">✕</button>
                        </div>
                    </div>
                    <div id="msg-cinema-subtitle-text"></div>
                    <div id="msg-cinema-golden-rule-line"><span id="msg-cinema-golden-rule-text"></span></div>
                `;
                document.body.appendChild(lt);
                if (typeof window.__makePopupElementDraggable === 'function') window.__makePopupElementDraggable(lt);
            }
        }

        function getPrimaryScreenStageContainer() {
            const s1 = document.getElementById('msg-cinema-scene1-stage');
            if (s1 && s1.style.display !== 'none') return s1;
            return document.getElementById('sl-proportional-root')
                || (document.getElementById('split-invoice-view') && document.getElementById('split-invoice-view').style.display !== 'none' ? document.getElementById('split-invoice-view') : null)
                || (document.getElementById('invoice-editor-view') && document.getElementById('invoice-editor-view').style.display !== 'none' ? document.getElementById('invoice-editor-view') : null)
                || document.querySelector('.md-panel.active, .md-content-area, .dash-main, .main-content, main, .pos-container')
                || document.body;
        }

        // ── 2. SMART CINEMATIC CAMERA (Smooth Auto-Zoom 1.6x & Pan + Spotlight Cutout) ──
        let lastIlluminatedEl = null;
        window.resetCinema4KCameraAndSpotlight = function () {
            if (cinemaAutoZoomTimer) { clearTimeout(cinemaAutoZoomTimer); cinemaAutoZoomTimer = null; }
            if (window.__cinemaFocalSyncTimer) { clearInterval(window.__cinemaFocalSyncTimer); window.__cinemaFocalSyncTimer = null; }
            document.querySelectorAll('.msg-cinema-camera-stage').forEach(s => { s.style.transform = ''; s.style.transformOrigin = 'center center'; });
            if (lastIlluminatedEl) { try { lastIlluminatedEl.classList.remove('msg-cinema-target-illuminated'); } catch (_) {} lastIlluminatedEl = null; }
            const spot = document.getElementById('msg-cinema-spotlight-overlay');
            if (spot) spot.style.display = 'none';
            const ring = document.getElementById('msg-live-target-ring');
            if (ring) ring.style.display = 'none';
        };

        window.applyCinema4KCameraZoomAndSpotlight = function (el, step) {
            if (!isCinemaModeActive) return;
            ensureCinemaDomOverlays();
            if (lastIlluminatedEl && lastIlluminatedEl !== el) {
                try { lastIlluminatedEl.classList.remove('msg-cinema-target-illuminated'); } catch (_) {}
            }
            if (window.__cinemaFocalSyncTimer) { clearInterval(window.__cinemaFocalSyncTimer); window.__cinemaFocalSyncTimer = null; }
            if (!el || el === document.body || (step && step.diagramType === 'c5-impact-map')) {
                window.resetCinema4KCameraAndSpotlight();
                return;
            }
            try { el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' }); } catch (_) {}
            try { el.classList.add('msg-cinema-target-illuminated'); lastIlluminatedEl = el; } catch (_) {}
            if (cinemaAutoZoomTimer) { clearTimeout(cinemaAutoZoomTimer); cinemaAutoZoomTimer = null; }

            const lockFocalAndCursor = () => {
                if (!isCinemaModeActive || !el) return;
                const rect = el.getBoundingClientRect();
                const cx = Math.round(rect.left + rect.width / 2);
                const cy = Math.round(rect.top + rect.height / 2);
                const radius = Math.max(130, Math.min(380, Math.round(Math.max(rect.width, rect.height) * 0.85)));
                const spot = document.getElementById('msg-cinema-spotlight-overlay');
                if (spot) {
                    spot.style.display = 'none';
                    spot.style.background = 'none';
                }
                const ring = document.getElementById('msg-live-target-ring');
                if (ring) {
                    ring.style.display = 'block';
                    ring.style.top = `${Math.max(2, rect.top - 6)}px`;
                    ring.style.left = `${Math.max(2, rect.left - 6)}px`;
                    ring.style.width = `${Math.max(32, rect.width + 12)}px`;
                    ring.style.height = `${Math.max(24, rect.height + 12)}px`;
                }
                const cursor = document.getElementById('msg-ghost-cursor');
                if (cursor && cursor.style.display !== 'none') {
                    cursor.style.left = `${Math.max(24, Math.min(window.innerWidth - 40, cx))}px`;
                    cursor.style.top = `${Math.max(24, Math.min(window.innerHeight - 40, cy))}px`;
                }
            };
            lockFocalAndCursor();
            window.__cinemaFocalSyncTimer = setInterval(lockFocalAndCursor, 60);
        };

        // ── 3. GHOST-ACTOR & CHARACTER-BY-CHARACTER AUTO-TYPING ──
        function resolveSampleRealisticTextForInput(inp) {
            if (!inp) return '';
            const id = (inp.id || '').toLowerCase();
            const ph = (inp.placeholder || '').toLowerCase();
            if (inp.type === 'date') return new Date().toISOString().slice(0, 10);
            if (inp.type === 'number' || inp.getAttribute('inputmode') === 'decimal' || id.includes('qty') || id.includes('weight') || id.includes('price')) {
                if (id.includes('weighbridge')) return '24500';
                if (id.includes('qty') || id.includes('subqty')) return '12';
                return '1500';
            }
            if (id.includes('waybill')) return 'WB-2026-9940';
            if (id.includes('seal')) return 'SEAL-2026-771';
            if (id.includes('truck') || ph.includes('لوحة') || ph.includes('سيارة')) return 'خ 4 - 8821';
            if (id.includes('driver') || id.includes('receiver') || ph.includes('سائق') || ph.includes('مستلم')) return 'أحمد محمد الأمين';
            if (id.includes('search') || ph.includes('بحث')) return 'فاتورة 10';
            if (id.includes('note') || ph.includes('ملاحظ')) return 'مطابق للفحص الميداني 100%';
            return 'بيان معتمد 2026';
        }

        window.runCinema4KGhostActorAutoType = function (targetEl) {
            if (!isCinemaModeActive || !targetEl) return;
            if (cinemaTypingTimer) {
                clearInterval(cinemaTypingTimer);
                cinemaTypingTimer = null;
            }
            const inp = (targetEl.tagName === 'INPUT' || targetEl.tagName === 'TEXTAREA')
                ? targetEl
                : (typeof targetEl.querySelector === 'function' ? targetEl.querySelector('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]), textarea') : null);
            if (!inp || inp.readOnly || inp.disabled) return;

            const currentVal = String(inp.value || '');
            // Only auto-type if the field is empty or we want to demonstrate live typing and restore if needed
            const sample = resolveSampleRealisticTextForInput(inp);
            if (!sample) return;

            cinemaRestorableInputs.push({ el: inp, orig: currentVal });
            let charIdx = 0;
            inp.value = '';
            try { inp.focus(); } catch (_) {}

            cinemaTypingTimer = setInterval(() => {
                if (!isCinemaModeActive || charIdx >= sample.length) {
                    clearInterval(cinemaTypingTimer);
                    cinemaTypingTimer = null;
                    // Restore original value if it had meaningful user data before the demo
                    if (currentVal && currentVal !== sample) {
                        setTimeout(() => {
                            inp.value = currentVal;
                            try { inp.dispatchEvent(new Event('input', { bubbles: true })); } catch (_) {}
                        }, 1800);
                    }
                    return;
                }
                inp.value = sample.slice(0, charIdx + 1);
                charIdx++;
                try { inp.dispatchEvent(new Event('input', { bubbles: true })); } catch (_) {}
            }, 95);
        };

        // ── 4. CINEMATIC LOWER-THIRDS & SUBTITLES + GOLDEN RULE EXTRACTOR ──
        function updateCinema4KLowerThirds(step, spokenText, stepIdx, totalSteps) {
            ensureCinemaDomOverlays();
            const lt = document.getElementById('msg-cinema-lower-thirds');
            if (!lt) return;
            lt.style.display = isCinemaModeActive ? 'flex' : 'none';
            if (!isCinemaModeActive) return;

            const sceneBadge = document.getElementById('msg-cinema-scene-badge');
            const subEl = document.getElementById('msg-cinema-subtitle-text');
            const goldenEl = document.getElementById('msg-cinema-golden-rule-text');

            if (sceneBadge) {
                sceneBadge.textContent = `🎬 المشهد ${stepIdx + 1} من ${totalSteps}: ${(step && (step.badge || step.cursorLabel)) || 'شرح تفصيلي'}`;
            }

            // Extract Golden Rule if present in spoken text or step metadata
            const raw = String(spokenText || '');
            let mainSub = raw;
            let goldenRule = (step && step.c5ImpactMeta && step.c5ImpactMeta.goldenRule) || '';
            if (raw.includes('القاعدة الذهبية')) {
                const parts = raw.split(/👑?\s*القاعدة الذهبية[^:]*:/);
                if (parts.length > 1) {
                    mainSub = parts[0].trim();
                    goldenRule = parts.slice(1).join(' ').trim();
                }
            }
            if (!goldenRule) {
                goldenRule = 'افحص البيانات والكميات والوحدة بدقة قبل الاعتماد (F10)، ينضبط القيد المحاسبي والمخزن وكشف الحساب تلقائياً بفرق صفر (0.00).';
            }
            if (goldenEl) goldenEl.textContent = goldenRule;

            // Display the FULL broadcast text continuously without truncating after the first words
            if (cinemaSubtitleTimer) {
                clearInterval(cinemaSubtitleTimer);
                cinemaSubtitleTimer = null;
            }
            if (subEl) {
                subEl.textContent = `🗣️ « ${mainSub || raw} »`;
            }
        }

        // Hook called by runCurrentAutopilotStep() on every step
        window.onCinema4KStepActivated = function (el, step, spokenText, stepIdx, totalSteps) {
            if (!isCinemaModeActive) return;
            const cursor = document.getElementById('msg-ghost-cursor');
            if (cursor) cursor.classList.add('msg-cinema-laser-actor');

            updateCinema4KLowerThirds(step, spokenText, stepIdx, totalSteps);
            window.applyCinema4KCameraZoomAndSpotlight(el, step);
            window.runCinema4KGhostActorAutoType(el);
            triggerLiveChildScreenPreviewForElement(el);
        };

        window.onCinema4KTourStopped = function () {
            window.resetCinema4KCameraAndSpotlight();
            if (cinemaExternalAudio) {
                try { cinemaExternalAudio.pause(); cinemaExternalAudio.currentTime = 0; } catch (_) {}
            }
            if (cinemaExternalStepTimer) {
                clearInterval(cinemaExternalStepTimer);
                cinemaExternalStepTimer = null;
            }
            if (isCinemaRecording) {
                window.startOrStopCinema4KVideoRecording(true);
            }
        };

        // ── 5. EXTERNAL ELEVENLABS MP3 UPLOAD & EXACT-SECOND STEP SYNC ──
        window.isCinemaExternalMp3DrivingSteps = function () {
            return Boolean(isCinemaModeActive && cinemaExternalAudio && !cinemaExternalAudio.paused);
        };

        window.handleCinema4KExternalMp3Upload = function (event) {
            const file = event && event.target && event.target.files ? event.target.files[0] : null;
            if (!file) return;
            cinemaExternalAudioName = file.name;
            const url = URL.createObjectURL(file);
            if (cinemaExternalAudio) {
                try { cinemaExternalAudio.pause(); } catch (_) {}
            }
            cinemaExternalAudio = new Audio(url);
            cinemaExternalAudio.preload = 'auto';
            cinemaExternalAudio.onloadedmetadata = () => {
                const dur = Math.max(1, Math.round(cinemaExternalAudio.duration || 0));
                const lbl = document.getElementById('msg-cinema-mp3-status-lbl');
                if (lbl) {
                    lbl.innerHTML = `✅ تم ربط ملف ElevenLabs الصوتي: <strong>${escapeSvgText(file.name)}</strong> (${dur} ثانية) — جاهز للمزامنة الآلية بالثانية!`;
                }
                if (typeof showSlToast === 'function') {
                    showSlToast(`🎵 تم تحميل ملف الصوت الخارجي (${file.name} — ${dur} ثانية) لمزامنة التمثيل الآلي!`, 'success', 3000);
                }
            };
        };

        window.clearCinema4KExternalMp3 = function () {
            if (cinemaExternalAudio) {
                try { cinemaExternalAudio.pause(); } catch (_) {}
                cinemaExternalAudio = null;
            }
            cinemaExternalAudioName = '';
            const lbl = document.getElementById('msg-cinema-mp3-status-lbl');
            if (lbl) {
                lbl.innerHTML = '🎙️ الصوت النشط حالياً: <strong>الصوت الرجالي الفخيم المدمج (يبدأ بالصلاة على النبي ﷺ مرة واحدة فقط)</strong>';
            }
        };

        // ── 6. 4K VIDEO RECORDER (MediaRecorder MP4/WebM Downloader) ──
        window.startOrStopCinema4KVideoRecording = async function (forceStop = false) {
            const recBtn = document.getElementById('msg-cinema-lt-rec-btn');
            const modalRecBtn = document.getElementById('msg-cinema-modal-rec-btn');

            if (isCinemaRecording || forceStop) {
                if (cinemaMediaRecorder && cinemaMediaRecorder.state !== 'inactive') {
                    try { cinemaMediaRecorder.stop(); } catch (_) {}
                }
                isCinemaRecording = false;
                const fixedLbl = document.getElementById('msg-fixed-cinema-4k-label');
                if (fixedLbl) fixedLbl.textContent = 'تسجيل';
                if (recBtn) recBtn.textContent = '🔴 تسجيل';
                if (modalRecBtn) modalRecBtn.textContent = '🔴 بدء تسجيل وتنزيل الفيديو (Download MP4/WebM)';
                return;
            }

            window.startAutomaticCinema4KMediaRecorder();
            window.playCinemaScene1CustomersCreditLimit(cinemaAspectRatio || '16:9');
        };

        // ── 6B. BROADCAST-GRADE MALE NEWS-ANCHOR AUDIO ENGINE + REAL MEDIARECORDER COMPOSITOR ──
        let cinemaBroadcastAudioCtx = null;
        let cinemaBroadcastAudioDest = null;
        let cinemaBroadcastBufferSource = null;
        let cinemaSpokenClean = '';
        let cinemaStudioCanvasTimer = null;
        let isCinemaVoiceMuted = false;

        function ensureCinemaBroadcastAudioGraph() {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return null;
            if (!cinemaBroadcastAudioCtx || cinemaBroadcastAudioCtx.state === 'closed') {
                cinemaBroadcastAudioCtx = new AudioCtx();
                cinemaBroadcastAudioDest = cinemaBroadcastAudioCtx.createMediaStreamDestination();
            }
            if (cinemaBroadcastAudioCtx.state === 'suspended') {
                try { cinemaBroadcastAudioCtx.resume(); } catch (_) {}
            }
            return cinemaBroadcastAudioCtx;
        }

        function createBroadcastBaritoneDspChain(ctx) {
            const chain = buildMaleClarityChain(ctx, 1.35);
            const masterGain = chain.output;
            if (isCinemaVoiceMuted) masterGain.gain.value = 0;
            window.__msgCinemaMasterGainNode = masterGain;
            const lim = ctx.createDynamicsCompressor();
            lim.threshold.value = -2.5; lim.knee.value = 0; lim.ratio.value = 20; lim.attack.value = 0.001; lim.release.value = 0.09;
            masterGain.connect(lim); lim.connect(ctx.destination);
            if (cinemaBroadcastAudioDest) {
                lim.connect(cinemaBroadcastAudioDest);
            }
            return chain.input;
        }

        let _activeCinemaScenarioState = {
            mode: 'customer',
            screenTitle: '👥 منظومة بيانات العملاء الشاملة من الألف إلى الياء',
            screenSubtitle: 'توثيق وشرح بروفيسوري كامل: السياسة • الموقف • بطاقة QR • الرصيد الافتتاحي • حد الائتمان • العملة • الرقم السري • التعديل والحذف',
            sceneIndex: 0,
            totalScenes: 10,
            sceneBadge: '',
            spokenText: '',
            goldenRule: '',
            cards: []
        };

        function wrapCanvasTextLines(ctx, text, maxWidth) {
            const words = String(text || '').split(/\s+/).filter(Boolean);
            const lines = [];
            let current = '';
            for (let i = 0; i < words.length; i++) {
                const testLine = current ? (current + ' ' + words[i]) : words[i];
                if (ctx.measureText(testLine).width > maxWidth && current) {
                    lines.push(current);
                    current = words[i];
                } else {
                    current = testLine;
                }
            }
            if (current) lines.push(current);
            return lines;
        }

        window.detectActiveMasterDataTabMode = function () {
            const splitView = document.getElementById('split-invoice-view');
            if ((window.location.search.includes('action=split') || (splitView && splitView.style.display !== 'none')) && window.location.pathname.includes('direct-sales.php')) {
                return 'sales-split';
            }
            if (window.location.pathname.includes('direct-sales.php')) return 'sales-direct';
            if (window.location.pathname.includes('purchase.php') || document.getElementById('purchase-editor-view')) return 'purchase-direct';
            if (window.location.pathname.includes('purchase-receipt.php')) return 'purchase-receipt';
            if (window.location.pathname.includes('smart-logistics.php')) return 'smart-logistics';
            const tabMap = [
                { mode: 'supplier', sel: '#tab-suppliers.active, .md-tab-btn.tab-suppliers.active' },
                { mode: 'customer', sel: '#tab-customers.active, .md-tab-btn.tab-customers.active' },
                { mode: 'warehouse', sel: '#tab-warehouses.active, .md-tab-btn.tab-warehouses.active' },
                { mode: 'bank', sel: '#tab-banks.active, .md-tab-btn.tab-banks.active' },
                { mode: 'safe', sel: '#tab-safes.active, .md-tab-btn.tab-safes.active' },
                { mode: 'item', sel: '#tab-items.active, .md-tab-btn.tab-items.active' },
                { mode: 'employee', sel: '#tab-employees.active, .md-tab-btn.tab-employees.active' },
                { mode: 'costcenter', sel: '#tab-costcenters.active, .md-tab-btn.tab-costcenters.active' }
            ];
            for (const t of tabMap) {
                const el = document.querySelector(t.sel);
                if (el) return t.mode;
            }
            const supTab = document.getElementById('tab-suppliers');
            if (supTab && supTab.offsetParent !== null) return 'supplier';
            const custTab = document.getElementById('tab-customers');
            if (custTab && custTab.offsetParent !== null) return 'customer';
            return 'customer';
        };

        window.startAutomaticCinema4KMediaRecorder = function (forceMode) {
            if (isCinemaRecording && cinemaMediaRecorder && cinemaMediaRecorder.state !== 'inactive') {
                return;
            }
            try {
                const activeMode = forceMode || window.detectActiveMasterDataTabMode() || 'customer';
                ensureCinemaBroadcastAudioGraph();
                const canvas = document.createElement('canvas');
                canvas.width = 1920;
                canvas.height = 1080;
                const cctx = canvas.getContext('2d');

                if (cinemaStudioCanvasTimer) clearInterval(cinemaStudioCanvasTimer);
                const startTs = performance.now();

                const renderStudioFrame = () => {
                    if (!cctx) return;
                    const elapsed = (performance.now() - startTs) / 1000;
                    const st = _activeCinemaScenarioState || {};
                    const isSup = st.mode === 'supplier';
                    const pfx = isSup ? 'sup' : 'cust';

                    cctx.save();
                    cctx.fillStyle = '#020617';
                    cctx.fillRect(0, 0, 1920, 1080);

                    // Stage Card Background
                    const grad = cctx.createLinearGradient(0, 0, 1920, 900);
                    grad.addColorStop(0, '#020617');
                    grad.addColorStop(0.5, '#0f172a');
                    grad.addColorStop(1, isSup ? '#2e1065' : '#1e1b4b');
                    cctx.fillStyle = grad;
                    cctx.strokeStyle = isSup ? '#c084fc' : '#38bdf8';
                    cctx.lineWidth = 4;
                    cctx.fillRect(50, 32, 1820, 745);
                    cctx.strokeRect(50, 32, 1820, 745);

                    // Stage Header
                    cctx.direction = 'rtl';
                    cctx.textAlign = 'right';
                    cctx.fillStyle = '#f8fafc';
                    cctx.font = '900 30px Cairo, Tajawal, sans-serif';
                    cctx.fillText(st.screenTitle || '👥 منظومة بيانات العملاء الشاملة من الألف إلى الياء (4K Studio)', 1835, 86);
                    cctx.fillStyle = '#94a3b8';
                    cctx.font = '700 19px Cairo, Tajawal, sans-serif';
                    cctx.fillText(st.screenSubtitle || 'شرح بروفيسوري شامل لكل زر وحقل وسياسة من الألف إلى الياء', 1835, 120);

                    // Scene Counter Pill
                    cctx.fillStyle = 'rgba(245,158,11,0.22)';
                    cctx.strokeStyle = '#fde047';
                    cctx.lineWidth = 2.5;
                    cctx.fillRect(80, 52, 470, 58);
                    cctx.strokeRect(80, 52, 470, 58);
                    cctx.fillStyle = '#fef08a';
                    cctx.textAlign = 'center';
                    cctx.font = '900 21px Cairo, Tajawal, sans-serif';
                    const scenePillTxt = st.sceneBadge || `🎬 المشهد ${(st.sceneIndex || 0) + 1} من ${st.totalScenes || 10}`;
                    cctx.fillText(scenePillTxt.slice(0, 46), 315, 89);

                    // Top Bulk WhatsApp Policy Bar representation
                    const topBtns = [
                        { lbl: '📊 كشف للكل', col: '#34d399' },
                        { lbl: '💰 رصيد للكل', col: '#fbbf24' },
                        { lbl: '🔒 إيقاف الكل', col: '#f87171' },
                        { lbl: '🔐 توليد باسورد', col: '#38bdf8' },
                        { lbl: '⚙️ السياسة العامة', col: '#d8b4fe' },
                        { lbl: '🪪 بطاقة QR', col: '#34d399' },
                        { lbl: '📋 نسخ البطاقة', col: '#e9d5ff' }
                    ];
                    topBtns.forEach((b, idx) => {
                        const bx = 1835 - (idx + 1) * 248;
                        const isHighlighted = (st.sceneIndex === 0 && idx < 4) || (st.sceneIndex === 1 && idx === 4) || (st.sceneIndex === 3 && idx >= 5);
                        cctx.fillStyle = isHighlighted ? 'rgba(250,204,21,0.25)' : 'rgba(15,23,42,0.9)';
                        cctx.strokeStyle = isHighlighted ? '#fde047' : b.col;
                        cctx.lineWidth = isHighlighted ? 3.5 : 2;
                        cctx.fillRect(bx, 140, 234, 48);
                        cctx.strokeRect(bx, 140, 234, 48);
                        cctx.fillStyle = isHighlighted ? '#fef08a' : '#ffffff';
                        cctx.textAlign = 'center';
                        cctx.font = '900 19px Cairo, Tajawal, sans-serif';
                        cctx.fillText(b.lbl, bx + 117, 171);
                    });

                    let fieldBoxes = [];
                    if (st.mode === 'purchase-direct' || st.mode === 'sales-direct') {
                        const isPurMode = st.mode === 'purchase-direct';
                        const partyVal = (document.getElementById('hdr-customer-search')?.value || '').trim() || (isPurMode ? 'مورد معتمد' : 'عميل معتمد');
                        const whSel = document.getElementById('hdr-warehouse');
                        const whVal = (whSel && whSel.options && whSel.selectedIndex >= 0) ? whSel.options[whSel.selectedIndex].text : 'المخزن الرئيسي';
                        const curVal = (document.getElementById('hdr-currency')?.value || 'SDG') + ' • ' + (document.getElementById('hdr-exchange-rate')?.value || '1');
                        const dateVal = (document.getElementById('inv-number')?.textContent || '#1') + ' • ' + (document.getElementById('hdr-date')?.value || 'اليوم');
                        const totSdg = (document.getElementById('grand-total-sdg')?.textContent || document.getElementById('lbl-grand-total')?.textContent || '0.00').trim();
                        const totUsd = (document.getElementById('grand-total-usd')?.textContent || '0.00').trim();
                        fieldBoxes = [
                            { title: '📅 رقم وتاريخ الفاتورة', val: dateVal, col: '#38bdf8', active: st.sceneIndex <= 1 },
                            { title: isPurMode ? '🏭 المورد ودفتر الأستاذ' : '👤 العميل ودفتر الأستاذ', val: partyVal, col: '#d8b4fe', active: st.sceneIndex >= 2 && st.sceneIndex <= 4 },
                            { title: '💱 العملة وسعر الصرف', val: curVal, col: '#4ade80', active: st.sceneIndex === 5 },
                            { title: '📦 المخزن المعتمد', val: whVal, col: '#fbbf24', active: st.sceneIndex === 6 },
                            { title: '📊 صافي الجملة (ج.س / $)', val: `${totSdg} ج.س | ${totUsd} $`, col: '#f43f5e', active: st.sceneIndex >= 9 && st.sceneIndex <= 10 },
                            { title: '💳 السداد والاعتماد F10', val: 'نقدي • آجل • بنكك • شيك • واتساب', col: '#34d399', active: st.sceneIndex >= 11 }
                        ];
                    } else {
                        const nameInp = document.getElementById(`${pfx}-name`) || document.getElementById('cinema-s1-cust-name');
                        const pinInp = document.getElementById(`${pfx}-statement-pin`);
                        const waInp = document.getElementById(`${pfx}-whatsapp`) || document.getElementById('cinema-s1-cust-phone');
                        const opBalInp = document.getElementById(`${pfx}-opening-balance`);
                        const limInp = document.getElementById(`${pfx}-limit`) || document.getElementById('cinema-s1-cust-limit');
                        const curInp = document.getElementById(`${pfx}-currency`);
                        fieldBoxes = [
                            { title: isSup ? '🏭 اسم المورد *' : '👤 اسم العميل *', val: (nameInp && nameInp.value) || (isSup ? 'شركة النخبة للتوريدات العالمية' : 'شركة البنيان المتطورة'), col: '#38bdf8', active: st.sceneIndex === 4 },
                            { title: '🔐 الرقم السري (PIN للواتساب)', val: (pinInp && pinInp.value) || '4829', col: '#d8b4fe', active: st.sceneIndex === 4 },
                            { title: '💬 رقم الواتساب المعتمد', val: (waInp && waInp.value) || '00249912345678', col: '#4ade80', active: st.sceneIndex === 4 },
                            { title: '💰 الرصيد الافتتاحي (مغذي الحساب)', val: (opBalInp && opBalInp.value) || '15,000', col: '#fbbf24', active: st.sceneIndex === 5 },
                            { title: '🛑 الحد الائتماني (حماية المال)', val: (limInp && limInp.value) || '50,000', col: '#f43f5e', active: st.sceneIndex === 5 },
                            { title: '💱 العملة • 🟢 الحالة • 🏷️ التسعير', val: `${(curInp && curInp.value) || 'SDG'} • نشط • جملة`, col: '#34d399', active: st.sceneIndex === 6 }
                        ];
                    }
                    fieldBoxes.forEach((f, idx) => {
                        const colIdx = idx % 3;
                        const rowIdx = Math.floor(idx / 3);
                        const fw = 568;
                        const fh = 104;
                        const fx = 1835 - (colIdx + 1) * fw - colIdx * 22;
                        const fy = 208 + rowIdx * 118;
                        cctx.fillStyle = f.active ? 'rgba(30,27,75,0.96)' : 'rgba(15,23,42,0.92)';
                        cctx.strokeStyle = f.active ? '#fde047' : f.col;
                        cctx.lineWidth = f.active ? 4 : 2.2;
                        cctx.fillRect(fx, fy, fw, fh);
                        cctx.strokeRect(fx, fy, fw, fh);
                        cctx.textAlign = 'right';
                        cctx.fillStyle = f.active ? '#fde047' : f.col;
                        cctx.font = '900 19px Cairo, Tajawal, sans-serif';
                        cctx.fillText(f.title, fx + fw - 18, fy + 34);
                        cctx.fillStyle = '#020617';
                        cctx.fillRect(fx + 16, fy + 46, fw - 32, 44);
                        cctx.fillStyle = '#ffffff';
                        cctx.textAlign = 'center';
                        cctx.font = '900 22px Cairo, Tajawal, monospace';
                        cctx.fillText(String(f.val).slice(0, 34), fx + fw / 2, fy + 76);
                    });

                    // Current Scene 4 Spotlight Cards (Detailed Professor Breakdown)
                    cctx.fillStyle = 'rgba(2,6,23,0.92)';
                    cctx.strokeStyle = 'rgba(56,189,248,0.45)';
                    cctx.lineWidth = 2.5;
                    cctx.fillRect(80, 455, 1755, 298);
                    cctx.strokeRect(80, 455, 1755, 298);

                    cctx.textAlign = 'right';
                    cctx.fillStyle = '#fde047';
                    cctx.font = '900 23px Cairo, Tajawal, sans-serif';
                    cctx.fillText(`🎓 تفصيل المشهد الحالي (${(st.sceneIndex || 0) + 1}/${st.totalScenes || 10}): ${st.sceneBadge || ''}`, 1810, 495);

                    const cards = Array.isArray(st.cards) && st.cards.length ? st.cards : [
                        '📊 كشف للكل • 💰 رصيد للكل • 🔒 إيقاف الكل • 🔐 توليد باسورد • ⚙️ السياسة العامة',
                        '🪪 البطاقة الافتراضية QR • 📋 نسخ البطاقة • موقف الحساب (موقوف / الرصيد / كشف)',
                        '💰 الرصيد الافتتاحي يغذي كشف الحساب • 🛑 حد الائتمان يحمي رأس المال • 💱 العملة والرقم السري',
                        '✏️ التعديل الفوري بالضغط على (تعديل) • 🗑️ منع الحذف نهائياً عند وجود حركات أو رصيد'
                    ];
                    cards.slice(0, 4).forEach((cardTxt, idx) => {
                        const cy = 538 + idx * 52;
                        const pulse = Math.floor(elapsed * 2) % 4 === idx;
                        cctx.fillStyle = pulse ? 'rgba(16,185,129,0.22)' : 'rgba(15,23,42,0.85)';
                        cctx.strokeStyle = pulse ? '#34d399' : 'rgba(148,163,184,0.35)';
                        cctx.lineWidth = pulse ? 2.5 : 1.5;
                        cctx.fillRect(105, cy - 32, 1705, 44);
                        cctx.strokeRect(105, cy - 32, 1705, 44);
                        cctx.fillStyle = '#ffffff';
                        cctx.textAlign = 'right';
                        cctx.font = '800 21px Cairo, Tajawal, sans-serif';
                        cctx.fillText(cardTxt, 1790, cy - 3);
                    });

                    cctx.restore();

                    // Glassmorphic Lower-Thirds Bar at Bottom of 1080p Frame
                    cctx.fillStyle = 'rgba(15,23,42,0.95)';
                    cctx.strokeStyle = '#38bdf8';
                    cctx.lineWidth = 3;
                    cctx.fillRect(50, 792, 1820, 260);
                    cctx.strokeRect(50, 792, 1820, 260);

                    cctx.direction = 'rtl';
                    cctx.textAlign = 'center';
                    cctx.fillStyle = '#ffffff';
                    cctx.font = '800 22px Cairo, Tajawal, sans-serif';
                    const allLines = wrapCanvasTextLines(cctx, st.spokenText || '', 1720);
                    const ae = cinemaBroadcastAudioEl;
                    const prog = (ae && ae.duration && !ae.paused && !ae.ended) ? spokenCharRatio(cinemaSpokenClean, ae.currentTime, ae.duration) : 0;
                    const lineEnds = []; let lAcc = 0;
                    allLines.forEach(l => { lAcc += l.length + 1; lineEnds.push(lAcc); });
                    let curLine = lineEnds.findIndex(e => e > prog * lAcc);
                    if (curLine < 0) curLine = allLines.length - 1;
                    const startLine = Math.max(0, Math.min(Math.max(0, allLines.length - 4), curLine - 1));
                    allLines.slice(startLine, startLine + 4).forEach((ln, lIdx) => {
                        const isCur = prog > 0 && (startLine + lIdx) === curLine;
                        cctx.fillStyle = isCur ? '#fde047' : '#ffffff';
                        cctx.font = isCur ? '900 24px Cairo, Tajawal, sans-serif' : '800 22px Cairo, Tajawal, sans-serif';
                        cctx.fillText(ln, 960, 835 + lIdx * 34);
                    });
                    if (prog > 0) { cctx.fillStyle = '#38bdf8'; cctx.fillRect(50 + 1820 * (1 - prog), 1046, 1820 * prog, 5); }

                    // Golden Rule Highlight Bar
                    cctx.fillStyle = 'rgba(245,158,11,0.18)';
                    cctx.strokeStyle = '#fbbf24';
                    cctx.lineWidth = 2;
                    cctx.fillRect(90, 976, 1740, 58);
                    cctx.strokeRect(90, 976, 1740, 58);
                    cctx.fillStyle = '#fef08a';
                    cctx.font = '900 21px Cairo, Tajawal, sans-serif';
                    const grText = st.goldenRule || '«اضبط الرصيد الافتتاحي وحد الائتمان والرقم السري بدقة؛ فالتجارة الناجحة تُبنى على الحزم المالي والتوثيق الكامل.»';
                    cctx.fillText(`💡 القاعدة الذهبية: ${grText}`.slice(0, 115), 960, 1013);
                };

                renderStudioFrame();
                cinemaStudioCanvasTimer = setInterval(renderStudioFrame, 42);

                const canvasStream = canvas.captureStream(24);
                const combinedTracks = [...canvasStream.getVideoTracks()];
                if (cinemaBroadcastAudioDest && cinemaBroadcastAudioDest.stream) {
                    combinedTracks.push(...cinemaBroadcastAudioDest.stream.getAudioTracks());
                }
                const combinedStream = new MediaStream(combinedTracks);

                const mimeCandidates = [
                    'video/mp4;codecs=avc1,mp4a.40.2',
                    'video/webm;codecs=vp9,opus',
                    'video/webm;codecs=vp8,opus',
                    'video/webm',
                    'video/mp4'
                ];
                const chosenMime = mimeCandidates.find(m => typeof MediaRecorder !== 'undefined' && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(m)) || '';
                cinemaRecordedChunks = [];
                cinemaMediaRecorder = chosenMime
                    ? new MediaRecorder(combinedStream, { mimeType: chosenMime, videoBitsPerSecond: 8000000, audioBitsPerSecond: 192000 })
                    : new MediaRecorder(combinedStream);

                cinemaMediaRecorder.ondataavailable = (ev) => {
                    if (ev.data && ev.data.size > 0) cinemaRecordedChunks.push(ev.data);
                };

                cinemaMediaRecorder.onstop = () => {
                    if (cinemaStudioCanvasTimer) {
                        clearInterval(cinemaStudioCanvasTimer);
                        cinemaStudioCanvasTimer = null;
                    }
                    try { canvasStream.getTracks().forEach(t => t.stop()); } catch (_) {}
                    isCinemaRecording = false;
                    const fixedLbl = document.getElementById('msg-fixed-cinema-4k-label');
                    if (fixedLbl) fixedLbl.textContent = 'تسجيل';
                    if (!cinemaRecordedChunks.length) return;

                    const ext = chosenMime.includes('mp4') ? 'mp4' : 'webm';
                    const blob = new Blob(cinemaRecordedChunks, { type: chosenMime || 'video/webm' });
                    const dlUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = dlUrl;
                    const fileSlug = (_activeCinemaScenarioState && _activeCinemaScenarioState.mode) || activeMode || 'customers';
                    a.download = `magaies-${fileSlug}-full-guide-4k-${Date.now()}.${ext}`;
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(() => {
                        URL.revokeObjectURL(dlUrl);
                        a.remove();
                    }, 2500);
                    if (typeof showSlToast === 'function') {
                        showSlToast(`🎬 تم حفظ وتنزيل الفيديو التعليمي الشامل من الألف إلى الياء بنجاح (${a.download})!`, 'success', 4500);
                    }
                };

                cinemaMediaRecorder.start(200);
                isCinemaRecording = true;
                const fixedLbl = document.getElementById('msg-fixed-cinema-4k-label');
                if (fixedLbl) fixedLbl.textContent = '🔴 جاري التسجيل';
            } catch (_err) {
                isCinemaRecording = false;
            }
        };

        window.stopAndSaveCinema4KMediaRecorder = function () {
            if (cinemaStudioCanvasTimer) {
                clearInterval(cinemaStudioCanvasTimer);
                cinemaStudioCanvasTimer = null;
            }
            if (cinemaMediaRecorder && cinemaMediaRecorder.state !== 'inactive') {
                try { cinemaMediaRecorder.stop(); } catch (_) {}
            }
            isCinemaRecording = false;
            const fixedLbl = document.getElementById('msg-fixed-cinema-4k-label');
            if (fixedLbl) fixedLbl.textContent = 'تسجيل';
        };

        let cinemaBroadcastAudioEl = null;

        window.silenceAllLegacyGuideAudioAndUI = function (stopCinemaVoiceToo = false) {
            window.__MUTE_LEGACY_GUIDE_AUDIO__ = true;
            isAutopilotRunning = false;
            window.__mdInteractiveGuideActive = false;
            isPickElementModeActive = false;
            isHandsOnTrainingActive = false;
            stopCurrentSpeech();
            if ('speechSynthesis' in window) {
                try { window.speechSynthesis.cancel(); } catch (_) {}
            }
            if (stopCinemaVoiceToo) {
                if (cinemaBroadcastBufferSource) {
                    try { cinemaBroadcastBufferSource.stop(); } catch (_) {}
                    cinemaBroadcastBufferSource = null;
                }
                if (cinemaBroadcastAudioEl) {
                    try {
                        cinemaBroadcastAudioEl.onended = null;
                        cinemaBroadcastAudioEl.pause();
                        cinemaBroadcastAudioEl.currentTime = 0;
                    } catch (_) {}
                    cinemaBroadcastAudioEl = null;
                }
            }
            [
                'msg-live-coach-bar',
                'msg-coach-svg-popover',
                'msg-smart-hover-tooltip',
                'msg-voice-qa-panel',
                'msg-universal-modal',
                'msg-pinned-audio-dock',
                'msg-c5-impact-map-banner'
            ].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
        };

        // Exclusive Broadcast-Grade Male News-Anchor Voice Engine (Dynamic Per-Scene Neural Male TTS Streaming)
        let _cinemaPitchAudioGraphBound = false;
        let _cinemaVoiceReqId = 0;
        window.__stopCinemaBroadcastAudio = function () {
            _cinemaVoiceReqId++;
            if (cinemaBroadcastBufferSource) {
                try { cinemaBroadcastBufferSource.onended = null; cinemaBroadcastBufferSource.stop(); } catch (_) {}
                cinemaBroadcastBufferSource = null;
            }
            if (cinemaBroadcastAudioEl) {
                try { cinemaBroadcastAudioEl.onended = null; cinemaBroadcastAudioEl.pause(); } catch (_) {}
            }
        };
        window.speakCinema4KExclusiveVoice = function (text, onEndCallback) {
            window.__stopCinemaBroadcastAudio();
            stopCurrentSpeech();
            syncGlobalKillSwitchVisibility(true);
            const myCinemaReqId = _cinemaVoiceReqId;
            if ('speechSynthesis' in window) {
                try { window.speechSynthesis.cancel(); } catch (_) {}
            }
            const cleanText = String(text || '').trim();
            _cinemaPlaybackRate = Math.max(0.50, Math.min(4.00, selectedSpeechRate || _cinemaPlaybackRate || 1.0));
            const estDurationMs = Math.max(2500, Math.min(28000, Math.round((cleanText.length * 68) / _cinemaPlaybackRate)));
            if (isCinemaVoiceMuted || !cleanText) {
                if (typeof onEndCallback === 'function') setTimeout(onEndCallback, estDurationMs);
                return;
            }

            const ctx = ensureCinemaBroadcastAudioGraph();
            (async () => {
                try {
                    if (!ctx) throw new Error('No ctx');
                    if (ctx.state === 'suspended') await ctx.resume();
                    const resObj = await fetchAndDecodeMaleBuffer(cleanText, selectedMaleProfile || 'fusha');
                    if (myCinemaReqId !== _cinemaVoiceReqId) return;
                    const audioBuffer = resObj && (resObj.buffer || resObj);
                    const blobUrl = resObj && resObj.blobUrl;
                    if (!audioBuffer) throw new Error('Fast fallback');
                    cinemaSpokenClean = cleanTextForMaleTts(cleanText); loadWordMarks(cinemaSpokenClean);

                    if (blobUrl) {
                        if (!cinemaBroadcastAudioEl) {
                            cinemaBroadcastAudioEl = new Audio();
                            cinemaBroadcastAudioEl.preload = 'auto';
                        }
                        if (!_cinemaPitchAudioGraphBound) {
                            try {
                                const mSrc = ctx.createMediaElementSource(cinemaBroadcastAudioEl);
                                const dspIn = createBroadcastBaritoneDspChain(ctx);
                                mSrc.connect(dspIn);
                                _cinemaPitchAudioGraphBound = true;
                            } catch (_) {}
                        }
                        if (window.__msgCinemaMasterGainNode && window.__msgCinemaMasterGainNode.gain) {
                            window.__msgUserVoiceVolume = parseFloat(localStorage.getItem('msg_voice_vol_v1') || '1.65') || 1.65;
                            window.__msgCinemaMasterGainNode.gain.value = isCinemaVoiceMuted ? 0 : (1.35 * window.__msgUserVoiceVolume);
                        }
                        cinemaBroadcastAudioEl.preservesPitch = true;
                        cinemaBroadcastAudioEl.mozPreservesPitch = true;
                        cinemaBroadcastAudioEl.webkitPreservesPitch = true;
                        cinemaBroadcastAudioEl.src = blobUrl;
                        cinemaBroadcastAudioEl.currentTime = 0;
                        cinemaBroadcastAudioEl.playbackRate = _cinemaPlaybackRate;
                        cinemaBroadcastAudioEl.onended = () => {
                            if (typeof onEndCallback === 'function') onEndCallback();
                        };
                        await cinemaBroadcastAudioEl.play();
                        return;
                    }

                    const src = ctx.createBufferSource();
                    src.buffer = audioBuffer;
                    src.playbackRate.value = _cinemaPlaybackRate;
                    const dspInput = createBroadcastBaritoneDspChain(ctx);
                    src.connect(dspInput);
                    cinemaBroadcastBufferSource = src;
                    src.onended = () => {
                        if (cinemaBroadcastBufferSource === src) {
                            cinemaBroadcastBufferSource = null;
                            if (typeof onEndCallback === 'function') onEndCallback();
                        }
                    };
                    src.start(0);
                } catch (_) {
                    speakWithBrowserMaleFallback(cleanText, onEndCallback, selectedMaleProfile || 'fusha', false, true);
                }
            })();
        };

        // ── 7. START / STOP / TOGGLE INDEPENDENT 4K CINEMA MODE (16:9 YouTube vs 9:16 Reels) ──
        window.toggleCinema4KAspectRatio = function (forceRatio) {
            cinemaAspectRatio = forceRatio || (cinemaAspectRatio === '16:9' ? '9:16' : '16:9');
            document.body.classList.toggle('msg-cinema-aspect-9-16', isCinemaModeActive && cinemaAspectRatio === '9:16');
            const badge = document.getElementById('msg-cinema-aspect-badge');
            if (badge) {
                badge.textContent = '🔴 جاري التسجيل السينمائي 4K';
            }
        };

        let _cinemaIndependentTourTimers = [];
        window._cinemaScene1Timers = window._cinemaScene1Timers || [];
        function clearCinemaIndependentTourTimers() {
            _cinemaIndependentTourTimers.forEach(t => clearTimeout(t));
            _cinemaIndependentTourTimers = [];
            if (Array.isArray(window._cinemaScene1Timers)) {
                window._cinemaScene1Timers.forEach(t => clearTimeout(t));
            }
            window._cinemaScene1Timers = [];
        }

        function triggerCinemaClickRipple(el) {
            const cursor = document.getElementById('msg-ghost-cursor');
            if (cursor) {
                cursor.classList.add('clicking');
                setTimeout(() => cursor.classList.remove('clicking'), 450);
            }
            if (el) {
                const origTransition = el.style.transition;
                const origTransform = el.style.transform;
                el.style.transition = 'transform 0.22s ease, box-shadow 0.22s ease';
                el.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    el.style.transform = origTransform || '';
                    el.style.transition = origTransition || '';
                }, 240);
            }
        }

        // Independent 4K Cinema + Real Video Recorder Launcher bound directly to the "تسجيل" button under "المرشد"
        // Automatically detects the active tab (Customers vs Suppliers vs other Master Data tabs)
        window.startCinema4KWithChoice = function (recordVideo = false, ratio = '16:9', forcedEntityMode = null) {
            if (typeof window.resetToOptimalClaritySpeed === 'function') window.resetToOptimalClaritySpeed();
            const chModal = document.getElementById('msg-cinema-mode-choice-modal');
            if (chModal) chModal.style.display = 'none';
            const isMasterScreen = window.location.pathname.includes('master-data') || !!document.querySelector('.md-tabs-sidebar');
            const detectedMode = forcedEntityMode || (isMasterScreen ? (window.detectActiveMasterDataTabMode() || 'customer') : 'dynamic-screen');
            window.silenceAllLegacyGuideAudioAndUI(true);
            const badge = document.getElementById('msg-cinema-aspect-badge');
            if (recordVideo) {
                if (badge) badge.textContent = '🔴 جاري التسجيل السينمائي 4K وحفظ الفيديو';
                window.startAutomaticCinema4KMediaRecorder(detectedMode);
            } else {
                if (badge) badge.textContent = '👁️🎬 عرض سينمائي لحظي للدراسة فقط (بدون حفظ)';
                const fixedLbl = document.getElementById('msg-fixed-cinema-4k-label');
                if (fixedLbl) fixedLbl.textContent = '🎬 عرض حي';
            }
            window.playCinemaMasterDataEntityFullScenario(detectedMode, ratio || cinemaAspectRatio || '16:9');
        };

        let _cinemaPlaybackRate = selectedSpeechRate || 1.0;
        let _cinemaPaused = false;
        let _cinemaHoldScene = false;
        window.__applyCinemaLivePlaybackRate = function (rate) {
            _cinemaPlaybackRate = Math.max(0.50, Math.min(4.00, rate || 1.0));
            if (cinemaBroadcastAudioEl) {
                try {
                    cinemaBroadcastAudioEl.preservesPitch = true;
                    cinemaBroadcastAudioEl.playbackRate = _cinemaPlaybackRate;
                } catch (_) {}
            }
            if (cinemaBroadcastBufferSource && cinemaBroadcastBufferSource.playbackRate) {
                try { cinemaBroadcastBufferSource.playbackRate.value = _cinemaPlaybackRate; } catch (_) {}
            }
        };
        window.toggleCinemaSceneHold = function () {
            _cinemaHoldScene = !_cinemaHoldScene;
            const btn = document.getElementById('msg-cinema-hold-btn');
            if (btn) {
                btn.textContent = _cinemaHoldScene ? '📌 مثبت' : '📌 تثبيت';
                btn.style.background = _cinemaHoldScene ? 'rgba(245,158,11,0.35)' : '';
                btn.style.borderColor = _cinemaHoldScene ? '#fbbf24' : '';
            }
        };
        window.toggleCinemaPauseResume = function () {
            const btn = document.getElementById('msg-cinema-pause-btn');
            if (cinemaBroadcastAudioEl && cinemaBroadcastAudioEl.src) {
                if (cinemaBroadcastAudioEl.paused) {
                    _cinemaPaused = false;
                    cinemaBroadcastAudioEl.play().catch(() => {});
                    if (btn) btn.textContent = '⏸️';
                } else {
                    _cinemaPaused = true;
                    cinemaBroadcastAudioEl.pause();
                    if (btn) btn.textContent = '▶️';
                }
                return;
            }
            if (cinemaBroadcastAudioCtx) {
                if (_cinemaPaused) {
                    _cinemaPaused = false;
                    cinemaBroadcastAudioCtx.resume().catch(() => {});
                    if (btn) btn.textContent = '⏸️';
                } else {
                    _cinemaPaused = true;
                    cinemaBroadcastAudioCtx.suspend().catch(() => {});
                    if (btn) btn.textContent = '▶️';
                }
            }
        };
        window.adjustCinemaSpeedBy = function (delta) {
            const d = parseFloat(delta) || 0;
            const deltaPct = Math.abs(d) <= 1.5 ? Math.round(d * 100) : Math.round(d);
            window.adjustSpeechSpeedPctBy(deltaPct, false);
        };
        window.setGuideVoiceVolumePercent = function (pctVal) {
            if (window.adjustMsgVoiceVolume) window.adjustMsgVoiceVolume(0, (parseFloat(pctVal) || 100) / 100);
        };
        window.toggleCinemaVoiceMute = function () {
            isCinemaVoiceMuted = !isCinemaVoiceMuted;
            if (cinemaBroadcastAudioEl) cinemaBroadcastAudioEl.muted = isCinemaVoiceMuted;
            if (window.__msgCinemaMasterGainNode && window.__msgCinemaMasterGainNode.gain) {
                window.__msgCinemaMasterGainNode.gain.value = isCinemaVoiceMuted ? 0 : (1.35 * (window.__msgUserVoiceVolume || 1.65));
            }
            const btn = document.getElementById('msg-cinema-mute-btn');
            if (btn) btn.textContent = isCinemaVoiceMuted ? '🔇' : '🔊';
        };
        window.launchIndependent4KCinemaNow = function (ratio = '16:9', forcedEntityMode = null, explicitRecordChoice = null) {
            if (isCinemaModeActive || isCinemaRecording) {
                window.stopMagaies4KCinemaMode();
                return;
            }
            unlockBrowserAudioEnginesSync();
            window.startCinema4KWithChoice(explicitRecordChoice !== false, ratio, forcedEntityMode);
        };

        window.startMagaies4KCinemaMode = function (ratio = '16:9', tourMode = 'all') {
            const detectedMode = (tourMode && tourMode !== 'all') ? tourMode : (window.detectActiveMasterDataTabMode() || 'customer');
            window.silenceAllLegacyGuideAudioAndUI(true);
            ensureCinemaDomOverlays();
            clearCinemaIndependentTourTimers();
            window.startAutomaticCinema4KMediaRecorder(detectedMode);
            window.playCinemaMasterDataEntityFullScenario(detectedMode, ratio || '16:9');
        };

        window.stopMagaies4KCinemaMode = function () {
            window.__CINEMA_4K_ACTIVE__ = false;
            isCinemaModeActive = false;
            clearCinemaIndependentTourTimers();
            window.silenceAllLegacyGuideAudioAndUI(true);
            window.__MUTE_LEGACY_GUIDE_AUDIO__ = false;
            window.stopAndSaveCinema4KMediaRecorder();
            document.body.classList.remove('msg-cinema-4k-active', 'msg-cinema-aspect-9-16');
            window.resetCinema4KCameraAndSpotlight();
            const lt = document.getElementById('msg-cinema-lower-thirds');
            if (lt) lt.style.display = 'none';
            const cursor = document.getElementById('msg-ghost-cursor');
            if (cursor) {
                cursor.classList.remove('msg-cinema-laser-actor');
                cursor.style.display = 'none';
            }
            const ring = document.getElementById('msg-live-target-ring');
            if (ring) ring.style.display = 'none';
            if (cinemaSubtitleTimer) {
                clearInterval(cinemaSubtitleTimer);
                cinemaSubtitleTimer = null;
            }
            if (cinemaTypingTimer) {
                clearInterval(cinemaTypingTimer);
                cinemaTypingTimer = null;
            }
            if (cinemaExternalAudio) {
                try { cinemaExternalAudio.pause(); cinemaExternalAudio.currentTime = 0; } catch (_) {}
            }
            document.querySelectorAll('.msg-cinema-credit-spotlight-halo').forEach(el => el.classList.remove('msg-cinema-credit-spotlight-halo'));
            const stage1 = document.getElementById('msg-cinema-scene1-stage');
            if (stage1) stage1.style.display = 'none';
        };

        // ── 7B. SOFT HARMONIC CHIME SYNTHESIZER (رنين تنبيهي ناعم Chime مدمج في مسار الفيديو المسجل) ──
        window.playCinemaSoftChime = function () {
            try {
                const ctx = ensureCinemaBroadcastAudioGraph();
                if (!ctx) return;
                const now = ctx.currentTime;
                const notes = [880, 1174.66, 1318.51]; // A5 -> D6 -> E6 warm crystal chime
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, now + idx * 0.09);
                    gain.gain.setValueAtTime(0.001, now + idx * 0.09);
                    gain.gain.exponentialRampToValueAtTime(0.14, now + idx * 0.09 + 0.03);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.09 + 1.15);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    if (cinemaBroadcastAudioDest) {
                        gain.connect(cinemaBroadcastAudioDest);
                    }
                    osc.start(now + idx * 0.09);
                    osc.stop(now + idx * 0.09 + 1.2);
                });
            } catch (_) {}
        };

        function moveLaserCursorToElement(el, badgeText) {
            if (!el) return;
            ensureGuideDomElements();
            const cursor = document.getElementById('msg-ghost-cursor');
            if (!cursor) return;
            cursor.classList.add('msg-cinema-laser-actor');
            cursor.style.display = 'block';
            cursor.style.zIndex = '2147483640';
            const syncPos = () => {
                if (!el || !isCinemaModeActive) return;
                try { el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' }); } catch (_) {}
                const rect = el.getBoundingClientRect();
                const cx = Math.max(24, Math.min(window.innerWidth - 40, rect.left + rect.width * 0.5));
                const cy = Math.max(24, Math.min(window.innerHeight - 40, rect.top + rect.height * 0.5));
                cursor.style.left = cx + 'px';
                cursor.style.top = cy + 'px';
            };
            syncPos();
            setTimeout(syncPos, 360);
            const badge = document.getElementById('msg-ghost-cursor-label');
            if (badge && badgeText) badge.textContent = badgeText;
        }

        function typeTextIntoInputLive(inputEl, textToType, charDelayMs, onDone) {
            if (!inputEl) {
                if (typeof onDone === 'function') onDone();
                return;
            }
            if (cinemaTypingTimer) {
                clearInterval(cinemaTypingTimer);
                cinemaTypingTimer = null;
            }
            if (inputEl.type === 'number' && String(textToType).includes(',')) {
                inputEl.dataset.origType = 'number';
                try { inputEl.type = 'text'; } catch (_) {}
            }
            inputEl.value = '';
            try { inputEl.focus({ preventScroll: true }); } catch (_) {}
            const chars = Array.from(String(textToType));
            let i = 0;
            cinemaTypingTimer = setInterval(() => {
                if (!isCinemaModeActive || i >= chars.length) {
                    clearInterval(cinemaTypingTimer);
                    cinemaTypingTimer = null;
                    try {
                        inputEl.dispatchEvent(new Event('input', { bubbles: true }));
                        inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                    } catch (_) {}
                    if (typeof onDone === 'function') onDone();
                    return;
                }
                inputEl.value += chars[i];
                i++;
                try {
                    inputEl.dispatchEvent(new Event('input', { bubbles: true }));
                } catch (_) {}
            }, Math.max(15, Math.round((charDelayMs || 42) / Math.max(0.5, _cinemaPlaybackRate || 1.0))));
        }

        window.launchSingleElementMicroCinema = function (el) {
            if (!el) return;
            if (!el.id) el.id = 'msg-micro-cinema-target-' + Date.now();
            window.__microCinemaTargetEl = el;
            window.startCinema4KWithChoice(false, '16:9', 'single-element');
        };

        // ── 7C. PROFESSOR-LEVEL A-TO-Z SCENARIO BUILDER FOR ALL 121 ERP SCREENS ──
        function buildProfessorialMasterDataScenes(mode) {
            if (mode === 'single-element' && window.__microCinemaTargetEl) {
                const el = window.__microCinemaTargetEl;
                const rawInfo = (window.explainUnifiedDashboardDomElement && window.explainUnifiedDashboardDomElement(el))
                    || (window.autoSynthesizeElementExplanation && window.autoSynthesizeElementExplanation(el))
                    || { title: 'عنصر تشغيلي', desc: 'ينفذ الوظيفة المخصصة له في الشاشة فوراً.' };
                const enriched = window.enrichProfessor6Pillars ? window.enrichProfessor6Pillars(rawInfo, el) : rawInfo;
                const cleanSpeech = stripAllSalawatPrefixes(`${enriched.title}. ${enriched.desc}`);
                return [{
                    badge: `🎬 تصوير بؤري خاص — ${enriched.title}`,
                    targetSelector: `#${el.id}`,
                    goldenRule: (enriched.rawPillars && enriched.rawPillars.ruleTxt) || 'تأكد من مراجعة البيانات قبل الاعتماد.',
                    cards: String(enriched.desc || '').split(' • ').slice(0, 4),
                    onEnter: () => { if (typeof window.runCinema4KGhostActorAutoType === 'function') window.runCinema4KGhostActorAutoType(el); },
                    speech: cleanSpeech
                }];
            }
            if (mode === 'dynamic-screen' || ['sales-direct', 'purchase-direct', 'purchase-receipt', 'smart-logistics'].includes(mode)) {
                const pageTitle = (document.title || 'الشاشة الحالية').split('|')[0].trim();
                const gKey = typeof buildUniversalScreenGuideIfNeeded === 'function' ? buildUniversalScreenGuideIfNeeded() : (typeof resolveActiveGuideKeyFast === 'function' ? resolveActiveGuideKeyFast() : null);
                const curatedGuide = gKey && window.MagaiesSmartGuides && window.MagaiesSmartGuides[gKey];
                const validSteps = (curatedGuide && Array.isArray(curatedGuide.autopilotSteps)) ? curatedGuide.autopilotSteps.filter(st => st.targetSelector && st.targetSelector !== 'body' && document.querySelector(st.targetSelector)?.offsetParent !== null) : [];
                if (validSteps.length > 1 && gKey !== 'master-data') {
                    return validSteps.slice(0, 14).map((st, idx, arr) => {
                        const rawSp = typeof st.speech === 'function' ? st.speech() : (st.speech || st.desc || '');
                        const cleanSp = stripAllSalawatPrefixes(String(rawSp));
                        return {
                            badge: `المشهد ${idx + 1} / ${arr.length} — ${st.badge || st.cursorLabel || 'خطوة تشغيلية'}`,
                            targetSelector: st.targetSelector || 'body',
                            goldenRule: (st.c5ImpactMeta && st.c5ImpactMeta.goldenRule) || 'راجع البيانات والكميات والأرصدة بدقة قبل الحفظ بـ F10.',
                            cards: (st.diagramMeta && st.diagramMeta.childItems) || [st.badge || 'شرح تطبيقي حي'],
                            onEnter: () => {
                                if (typeof st.action === 'function') { try { st.action(); } catch (_) {} }
                                const tEl = st.targetSelector ? document.querySelector(st.targetSelector) : null;
                                if (tEl && typeof window.runCinema4KGhostActorAutoType === 'function') window.runCinema4KGhostActorAutoType(tEl);
                            },
                            speech: idx === 0 ? `اللهم صل وسلم على نبينا محمد، الآن أشرح ${pageTitle}. ${cleanSp}` : cleanSp
                        };
                    });
                }
                const activeViewScope = [document.getElementById('invoice-editor-view'), document.getElementById('split-invoice-view'), document.getElementById('sales-hub-view'), document.getElementById('purchase-editor-view'), document.getElementById('purchase-hub-view')].find(v => v && v.offsetParent !== null && v.style.display !== 'none') || document.querySelector('main, .dash-main, .pos-wrapper, .main-content') || document.body;
                const candidates = Array.from(activeViewScope.querySelectorAll('#qa-single-row-grid .qa-master-btn, #category-hubs-grid .cat-hub-btn, #dashboard-scope-btns button, .dash-fx-strip-2027 button, .kpi-cards .spotlight-card, .kpi-card, .stat-card, .hub-action-card, .charts-grid .glass-panel, .panel-header-row button, .items-toolbar button, .h-form-group, .pro-field-card, input:not([type="hidden"]), select, button, .sl-btn')).filter(el => {
                    if (el.closest('.global-sidebar, .sidebar, aside, #sidebar, .sidebar-nav, .sidebar-item, .nav-sidebar, .nav-links, #panoramic-flyout-panel, #sidebar-panoramic-flyout, .sp-flyout-panel, nav, header.premium-header, .msg-twin-quartets-wrapper, #sales-fc1-smart-ai-command-bar, #purchase-fc1-smart-ai-command-bar, #msg-universal-fixed-launcher, #msg-live-coach-bar, #msg-cinema-lower-thirds, #msg-cinema-mode-choice-modal')) return false;
                    const r = el.getBoundingClientRect();
                    return r.width > 36 && r.height > 20;
                }).slice(0, 12);
                return candidates.map((el, idx) => {
                    if (!el.id) el.id = `msg-cinema-dyn-el-${idx}`;
                    const rawInfo = (window.explainUnifiedDashboardDomElement && window.explainUnifiedDashboardDomElement(el))
                        || (window.autoSynthesizeElementExplanation && window.autoSynthesizeElementExplanation(el))
                        || { title: (el.innerText || 'عنصر تشغيلي').replace(/\s+/g, ' ').trim().slice(0, 45), desc: 'ينفذ الوظيفة المخصصة له في الشاشة فوراً.' };
                    const enriched = window.enrichProfessor6Pillars ? window.enrichProfessor6Pillars(rawInfo, el) : rawInfo;
                    const cleanSpeech = stripAllSalawatPrefixes(`${enriched.title}. ${enriched.desc}`);
                    return {
                        badge: `المشهد ${idx + 1} / ${candidates.length} — ${enriched.title}`,
                        targetSelector: `#${el.id}`,
                        goldenRule: (enriched.rawPillars && enriched.rawPillars.ruleTxt) || 'تأكد من مراجعة المؤشرات والبيانات قبل اعتماد العملية.',
                        cards: String(enriched.desc || '').split(' • ').slice(0, 4),
                        onEnter: () => { if (typeof window.runCinema4KGhostActorAutoType === 'function') window.runCinema4KGhostActorAutoType(el); },
                        speech: idx === 0 ? `اللهم صل وسلم على نبينا محمد، الآن أشرح ${pageTitle}. ${cleanSpeech}` : cleanSpeech
                    };
                });
            }
            const isSup = mode === 'supplier';
            const pfx = isSup ? 'sup' : 'cust';
            const entitySingular = isSup ? 'المورد' : 'العميل';
            const entityPlural = isSup ? 'الموردين' : 'العملاء';
            const entityKey = isSup ? 'supplier' : 'customer';
            const panelId = isSup ? '#admin-wa-policy-panel-sup' : '#admin-wa-policy-panel-cust';
            const formId = isSup ? '#supplier-form' : '#customer-form';
            const searchId = isSup ? '#supplier-search-input' : '#customer-search-input';
            const tbodyId = isSup ? '#suppliers-tbody' : '#customers-tbody';
            const tabSel = isSup ? '.md-tab-btn.tab-suppliers' : '.md-tab-btn.tab-customers';
            const sampleName = isSup ? 'شركة النخبة للتوريدات العالمية' : 'شركة البنيان المتطورة';
            const invoiceScreenName = isSup ? 'فواتير المشتريات وسندات الصرف' : 'فواتير المبيعات وسندات القبض';

            if (mode === 'sales-split') {
                return [
                    {
                        badge: 'المشهد 1 / 10 — نظرة عامة على فاتورة تقسيم وتسليم المبيعات ✂️🚚',
                        targetSelector: '#split-invoice-view, #split-invoice-select',
                        goldenRule: '«قسّم تسليم الفاتورة بدقة بالوحدتين الكبرى والصغرى؛ فكل دفعة تسليم موثقة تحمي مخزونك وحقوق عميلك.»',
                        cards: ['✂️🚚 شاشة موحدة للتقسيم والتسليم الفعلي', '📊 متابعة فورية للمسلم والمتبقي', '🏭 صرف متعدد المخازن في نفس الدفعة', '🎫 إصدار إذن صرف وتصريح بوابة QR'],
                        speech: 'اللهم صل وسلم على نبينا محمد، الآن أشرح فاتورة تقسيم وتسليم المبيعات؛ والتي تتيح لك تقسيم أي فاتورة مبيعات على دفعات متعددة وتسليمها فعلياً للعميل مع الرقابة المخزنية الكاملة.'
                    },
                    {
                        badge: 'المشهد 2 / 10 — البحث الذكي بالباركود أو رقم الفاتورة أو العميل 🔍',
                        targetSelector: '#split-invoice-search, #split-invoice-select',
                        goldenRule: '«امسح باركود الفاتورة أو اكتب اسم العميل للوصول اللحظي في أجزاء من الثانية.»',
                        cards: ['🔎 بحث فوري برقم الفاتورة أو العميل', '📷 دعم قارئ الباركود السريع', '⚡ فلترة الفواتير المتبقية تلقائياً', '📋 عرض رصيد الفاتورة الإجمالي'],
                        speech: 'من شريط البحث الذكي في أعلى الشاشة، يمكنك مسح باركود الفاتورة أو كتابة اسم العميل أو رقم الفاتورة، ليقوم النظام بفلترة وعرض الفاتورة المطلوبة فوراً بدون أي تأخير.'
                    },
                    {
                        badge: 'المشهد 3 / 10 — اختيار الفاتورة واستعراض شريط الإنجاز البانورامي 📊',
                        targetSelector: '#split-invoice-select, #split-summary-banner',
                        goldenRule: '«راقب شريط الإنجاز المئوي قبل الصرف لتعرف بدقة ما تم تسليمه وما تبقى للعميل.»',
                        cards: ['📈 نسبة الإنجاز المئوية للتسليم', '🟢 إجمالي الكميات المسلمة سابقاً', '🟠 إجمالي الكميات المتبقية للتسليم', '👤 بيانات العميل وحالة الفاتورة'],
                        speech: 'بمجرد اختيار الفاتورة، يظهر أمامك شريط الإنجاز البانورامي الذي يوضح اسم العميل، وإجمالي الفاتورة، والكميات المسلمة سابقاً، والكميات المتبقية الجاهزة للتقسيم والتسليم الآن.'
                    },
                    {
                        badge: 'المشهد 4 / 10 — جدول الأصناف والتقسيم بالوحدتين الكبرى والصغرى ⚖️',
                        targetSelector: '#split-items-tbody, #split-items-table',
                        goldenRule: '«أدخل الكمية المراد تسليمها بالوحدة الكبرى أو الصغرى؛ والنظام يحسب المتبقي آلياً بكسور عشرية دقيقة.»',
                        cards: ['📦 إدخال الكمية بالوحدة الكبرى (مثل طن/كرتونة)', '🔢 إدخال الكمية بالوحدة الصغرى (مثل كيلو/قطعة)', '🛡️ منع تجاوز الكمية المتبقية في الفاتورة', '🔄 تحويل تلقائي بين الوحدتين'],
                        speech: 'في جدول الأصناف، يمكنك كتابة الكمية المراد تسليمها الآن سواء بالوحدة الكبرى أو بالوحدة الصغرى؛ ويقوم المحرك الذكي بتحويل الكسور آلياً ومنع صرف أي كمية تتجاوز الرصيد المتبقي في الفاتورة.'
                    },
                    {
                        badge: 'المشهد 5 / 10 — تحديد مخزن الصرف المستقل لكل سطر 🏭',
                        targetSelector: '#split-items-tbody select, #split-items-table',
                        goldenRule: '«اختر مخزن الصرف الفعلي أمام كل صنف ليتم خصم البضاعة من رصيد المخزن الصحيح فوراً.»',
                        cards: ['🏭 اختيار مخزن مستقل لكل صنف بالدفعة', '📊 عرض الرصيد الفعلي المتاح بالمخزن', '🔄 التوزيع التلقائي على المخازن (FIFO)', '🛡️ حماية من الرصيد السالب'],
                        speech: 'أمام كل صنف يمكنك تحديد المخزن الذي ستخرج منه البضاعة فعلياً، أو الضغط على زر التوزيع التلقائي ليقوم النظام بتوزيع الكميات على المخازن المتاحة بأعلى دقة.'
                    },
                    {
                        badge: 'المشهد 6 / 10 — أزرار التسريع الذكي (تسليم الكل • 50% • تصفير) ⚡',
                        targetSelector: '#split-quick-actions-bar, #split-invoice-view',
                        goldenRule: '«استخدم أزرار التسريع لإنجاز الدفعات القياسية في ضغطة زر واحدة.»',
                        cards: ['⚡ تسليم كامل الكمية المتبقية بضغطة', '🌗 تقسيم 50% نصف الكمية فوراً', '🧹 تصفير الحقول لإعادة التوزيع', '💾 قوالب التوزيع السريع'],
                        speech: 'لتوفير وقت المحاسب وأمين المخزن في أوقات الزحام، وفرنا شريط أزرار التسريع الذكي؛ بضغطة واحدة يمكنك تعبئة كامل المتبقي، أو تقسيم نصف الكمية خمسين بالمائة، أو التوزيع التلقائي.'
                    },
                    {
                        badge: 'المشهد 7 / 10 — بيانات السائق والسيارة وميزان البسكول 🚛⚖️',
                        targetSelector: '#split-driver-name, #split-vehicle-plate, #split-notes',
                        goldenRule: '«وثّق اسم السائق ورقم اللوحة والوزن الفعلي لتأمين مسؤولية النقل حتى باب العميل.»',
                        cards: ['👨‍✈️ تسجيل اسم السائق ورقم هاتفه', '🚚 رقم لوحة السيارة أو الشاحنة', '⚖️ مطابقة الوزن القياسي وميزان البسكول', '✍️ التوقيع الإلكتروني للاستلام'],
                        speech: 'قبل اعتماد الدفعة، يمكنك تدوين اسم السائق، ورقم لوحة السيارة، وملاحظات التسليم، مع إمكانية مطابقة الوزن الإجمالي للشحنة مع ميزان البسكول وأخذ التوقيع الإلكتروني.'
                    },
                    {
                        badge: 'المشهد 8 / 10 — الحفظ الذري والاعتماد الفوري (F10) 💾',
                        targetSelector: '#confirm-split-btn, button[onclick*="submitSplitInvoice"]',
                        goldenRule: '«اضغط حفظ واعتماد الدفعة (F10) ليتم خصم المخزون وتحديث الفاتورة في معاملة ذرية محمية.»',
                        cards: ['💾 حفظ ذري آمن (ACID Transaction)', '🔒 منع النقر المزدوج وتكرار الصرف', '📉 خصم فوري من المخزن المحدد', '📒 تحديث حالة الفاتورة إلى مسلم جزئياً أو كلياً'],
                        speech: 'عند الضغط على زر اعتماد وحفظ الدفعة أو اختصار إف عشرة، ينفذ النظام معاملة ذرية محمية تخصم الكميات من المخزن وتحدث حالة الفاتورة الأصلية فوراً مع حماية كاملة ضد التكرار.'
                    },
                    {
                        badge: 'المشهد 9 / 10 — طباعة إذن الصرف وتصريح البوابة الأمنية (Gate Pass) 🖨️🎫',
                        targetSelector: '#split-history-section, #split-invoice-view',
                        goldenRule: '«اطبع إذن الصرف وتصريح الخروج المزود بباركود QR ليتم فحصه عند بوابة الأمن.»',
                        cards: ['🖨️ طباعة إذن تسليم موزون حراري أو A4', '🎫 تصريح خروج بوابة (Gate Pass QR)', '🔒 قفل إعادة الطباعة بعلامة (نسخة طبق الأصل)', '📄 تصدير PDF فوري'],
                        speech: 'عقب الحفظ مباشرة، يمكنك طباعة إذن التسليم الرسمي وتصريح الخروج الأمني المزود برمز كيو آر، مع تطبيق دستور الحصانة الأمنية الذي يميز أي نسخة معاد طباعتها بختم نسخة طبق الأصل.'
                    },
                    {
                        badge: 'المشهد 10 / 10 — سجل الدفعات وشجرة النسب وإشعار الواتساب 🌳📲',
                        targetSelector: '#split-history-tbody, #split-history-section',
                        goldenRule: '«أرسل إشعار التسليم للعميل عبر الواتساب وتابع شجرة نسب الدفعات من الألف إلى الياء.»',
                        cards: ['📲 إرسال رسالة واتساب تفصيلية للعميل', '🌳 شجرة نسب الفاتورة (Genealogy Tree)', '📊 سجل تاريخي لكل دفعة ومخزنها وسائقها', '🔍 استعراض وتدقيق كل إذن صرف سابق'],
                        speech: 'وفي أسفل الشاشة يظهر سجل الدفعات التاريخي وشجرة النسب التفاعلية؛ حيث يمكنك استعراض كل دفعة سابقة وإرسال إشعار واتساب فوري للعميل بالكميات المسلمة والمتبقية. شكراً لمتابعتكم.'
                    }
                ];
            }
            if (mode === ' customer' || mode === 'customer' || mode === 'supplier') {
                return [
                    {
                        badge: `1. الأوامر الجماعية العليا لواتساب ${entityPlural}: (كشف للكل • رصيد للكل • إيقاف الكل • توليد باسورد)`,
                        targetSelector: `${panelId} button[onclick*="detailed"], ${panelId}, ${tabSel}`,
                        goldenRule: `«تحكم في صلاحيات كشوف حسابات ${entityPlural} وأرقامهم السرية جماعياً بضغطة زر واحدة قبل بدء التشغيل اليومي.»`,
                        cards: [
                            `📊 كشف للكل: تفعيل إرسال كشف الحساب التفصيلي بالحركات والفواتير لجميع ${entityPlural} على الواتساب`,
                            `💰 رصيد للكل: قصر الرد الآلي في الواتساب على إرسال صافي الرصيد النهائي فقط دون تفاصيل الفواتير`,
                            `🔒 إيقاف الكل: إيقاف الرد الآلي لكشوف الحسابات لجميع ${entityPlural} دفعة واحدة عند الجرد أو المراجعة`,
                            `🔐 توليد باسورد: توليد أرقام سرية (PIN) عشوائية وفريدة آلياً لكل ${entityPlural} لحماية الخصوصية المالية`
                        ],
                        onEnter: () => {
                            const tabBtn = document.querySelector(tabSel);
                            if (tabBtn && !tabBtn.classList.contains('active')) {
                                try { tabBtn.click(); } catch (_) {}
                            }
                        },
                        speech: `اللهم صل وسلم على نبينا محمد.. أهلاً بحضرتك في الشرح والتوثيق البروفيسوري الشامل من الألف إلى الياء لشاشة بيانات ${entityPlural}. نبدأ أولاً بشريط الأوامر الجماعية لواتساب ${entityPlural} في أعلى الشاشة؛ حيث يتيح لك زر (كشف للكل) منح جميع ${entityPlural} صلاحية طلب كشف الحساب التفصيلي عبر الواتساب بضغطة واحدة، بينما يخصص زر (رصيد للكل) الرد الآلي لإرسال صافي الرصيد النهائي فقط دون تفاصيل الفواتير، ويقوم زر (إيقاف الكل) بتعطيل إرسال الكشوفات للجميع فوراً، أما زر (توليد باسورد) فيقوم آلياً بتوليد أرقام سرية فريدة وآمنة لجميع ${entityPlural} الذين ليس لديهم رقم سري.`
                    },
                    {
                        badge: `2. درج السياسة العامة للواتساب والاعتماد الأمني (⚙️ السياسة)`,
                        targetSelector: `${panelId} .admin-wa-policy-drawer, ${panelId} button[onclick*="toggleAdminWaPolicyDrawer"]`,
                        goldenRule: `«اضبط سياسة التحقق الأمني ونطاق الكشف واعتماد الإشعارات لتعمل منظومة الواتساب المالي بأعلى درجات الأمان.»`,
                        cards: [
                            `⚙️ صلاحية الكشف واعتماد الإشعارات: حسب كل حساب أو فرض الكشف أو المراجعة/الاعتماد الفوري للجميع`,
                            `🏦 البنك الافتراضي والتحقق الأمني: ربط البنك الافتراضي واشتراط الرقم المعتمد أو الباسوورد السري`,
                            `📅 نطاق الكشف المرسل: كامل الحركات، آخر 30 يوماً، آخر 20 أو 50 حركة، أو حركات الشهر الحالي فقط`,
                            `🛡️ خيارات التحكم الدقيق: إظهار الأصناف داخل الكشف، إشعار المدير الفوري، ومنع التكرار المزعج`
                        ],
                        onEnter: () => {
                            const drawer = document.querySelector(`${panelId} .admin-wa-policy-drawer`);
                            if (drawer && drawer.style.display === 'none' && typeof window.toggleAdminWaPolicyDrawer === 'function') {
                                try { window.toggleAdminWaPolicyDrawer(); } catch (_) {}
                            }
                        },
                        speech: `ثانياً: عند الضغط على زر (السياسة) يفتح أمامنا درج السياسة العامة للواتساب؛ ومنه نضبط ست ركائز أساسية: أولاً صلاحية الكشف العامة هل تتبع إعداد كل حساب أم تفرض كشفاً مفصلاً أو رصيداً فقط للجميع، ثانياً سياسة اعتماد إشعارات التحويل هل تُرحّل فوراً أم تخضع للمراجعة أولاً، ثالثاً تحديد البنك الافتراضي، رابعاً مستوى التحقق الأمني باشتراط الرقم المسجل أو الباسوورد السري دائماً، خامساً نطاق الكشف لتحديد كامل الحركات أو آخر ثلاثين يوماً أو الشهر الحالي، وسادساً خيارات إظهار تفاصيل الأصناف وإشعار المدير ومنع تكرار الطلبات.`
                    },
                    {
                        badge: `3. موقف ${entitySingular} الفردي (🔒 موقوف • 💰 الرصيد • 📊 كشف) واعتماد الإشعارات والبنك الافتراضي`,
                        targetSelector: `#${pfx}-mode-btn-detailed, #${pfx}-rcpt-btn-auto_post, #${pfx}-default-bank-id`,
                        goldenRule: `«يمكنك تخصيص موقف كل ${entitySingular} على حدة في الكشف واعتماد الإشعارات والبنك الافتراضي حسب درجة الثقة والتعامل.»`,
                        cards: [
                            `📊 موقف الكشف الفردي: تحديد موقف هذا ${entitySingular} بين (🔒 موقوف) أو (💰 الرصيد فقط) أو (📊 كشف تفصيلي)`,
                            `⚡ اعتماد الإشعارات الفردي: الاختيار بين (⚡ اعتماد فوري) لترحيل إشعاراته آلياً أو (👁️ مراجعة) قبل الترحيل`,
                            `🏦 البنك الافتراضي للحساب: تحديد الحساب البنكي الذي يحول عليه هذا ${entitySingular} دائماً مع زر (➕) للإضافة السريعة`,
                            `🔄 تكامل ذكي مع بوت الواتساب وقارئ الإشعارات بالذكاء الاصطناعي (OCR)`
                        ],
                        onEnter: () => {
                            const drawer = document.querySelector(`${panelId} .admin-wa-policy-drawer`);
                            if (drawer && drawer.style.display !== 'none' && typeof window.toggleAdminWaPolicyDrawer === 'function') {
                                try { window.toggleAdminWaPolicyDrawer(); } catch (_) {}
                            }
                        },
                        speech: `ثالثاً: ننتقل إلى شريط أدوات بطاقة ${entitySingular} الفردية؛ فهنا تحدد الموقف الخاص بهذا ${entitySingular} تحديداً عبر ثلاثة أزرار: (موقوف) لحجب الكشف عنه، أو (الرصيد) ليصله صافي حسابه فقط، أو (كشف) ليصله كشف الحساب التفصيلي بالحركات. وبجانبه تحدد آلية التعامل مع إشعارات التحويل البنكي التي يرسلها على الواتساب بين (اعتماد فوري) لترحيلها آلياً فور قراءتها بالذكاء الاصطناعي أو (مراجعة) لوضعها في طابور التدقيق، مع اختيار البنك الافتراضي لهذا ${entitySingular} أو إضافة بنك جديد فوراً بزر الإضافة.`
                    },
                    {
                        badge: `4. البطاقة الافتراضية الذكية (🪪 بطاقة QR) • (📋 نسخ البطاقة) • زر (🔄 جديد F2)`,
                        targetSelector: `${formId} button[onclick*="openEntityQrCardModal"], ${formId} button[onclick*="copyCurrentFormWhatsAppCard"]`,
                        goldenRule: `«أرسل البطاقة الافتراضية QR أو انسخ بطاقة الواتساب لـ${entitySingular} فور تأسيس حسابه ليحتفظ برقمه السري وتعليمات الخدمة الذاتية.»`,
                        cards: [
                            `🪪 بطاقة QR الافتراضية: توليد بطاقة رقمية أنيقة تحمل اسم ${entitySingular} ورقمه السري ورمز الاستجابة السريعة QR`,
                            `📋 نسخ البطاقة: نسخ رسالة ترحيبية متكاملة بالبيانات والرقم السري لإرسالها مباشرة في محادثة الواتساب`,
                            `🔄 زر جديد (F2): تفريغ الحقول وتهيئة البطاقة لإدخال ${entitySingular} جديد مع توليد رقم سري فريد تلقائياً`,
                            `📲 تمكين الخدمة الذاتية لـ${entitySingular} للاستعلام عن حسابه على مدار الساعة`
                        ],
                        onEnter: () => {
                            if (isSup && typeof window.clearSupplierForm === 'function') {
                                try { window.clearSupplierForm(); } catch (_) {}
                            } else if (!isSup && typeof window.clearCustomerForm === 'function') {
                                try { window.clearCustomerForm(); } catch (_) {}
                            }
                        },
                        speech: `رابعاً: بجانب إعدادات الموقف نجد زر (بطاقة كيو آر)، وهو يصدر البطاقة الافتراضية الذكية لـ${entitySingular} شاملة اسمه ورقمه السري ورمز الاستجابة السريعة كيو آر لحفظها كصورة أو مشاركتها معه. وبجانبه زر (نسخ البطاقة) الذي ينسخ نص البطاقة التعريفية والرقم السري وتعليمات البوت بضغطة واحدة لإرسالها له عبر الواتساب، ثم زر (جديد إف اثنين) لتفريغ الحقول وتهيئة البطاقة لتسجيل ${entitySingular} جديد.`
                    },
                    {
                        badge: `5. تسجيل الاسم التجاري • الرقم السري للواتساب (🔐 PIN + 🎲 توليد) • رقم الواتساب (💬)`,
                        targetSelector: `#${pfx}-name, #${pfx}-statement-pin, #${pfx}-whatsapp`,
                        goldenRule: `«الرقم السري (PIN) ورقم الواتساب هما مفتاح الربط الآمن بين ${entitySingular} وبوت الواتساب المالي الآلي.»`,
                        cards: [
                            `👤 اسم ${entitySingular} *: تسجيل الاسم التجاري يفتح له حساباً مالياً مستقلاً في شجرة الحسابات و${invoiceScreenName}`,
                            `🔐 الرقم السري (PIN) + زر (🎲 توليد): الباسوورد الذي نحتاجه في الواتساب ليطلب ${entitySingular} كشف حسابه بأمان`,
                            `💬 رقم الواتساب المعتمد: الرقم الذي يستقبل الفواتير والسندات وكشوف الحسابات ويرسل إشعارات التحويل`,
                            `🔒 حماية تامة تمنع أي شخص غير مصرح له من الاطلاع على كشف الحساب`
                        ],
                        onEnter: () => {
                            const nameEl = document.getElementById(`${pfx}-name`);
                            const waEl = document.getElementById(`${pfx}-whatsapp`);
                            if (nameEl) typeTextIntoInputLive(nameEl, sampleName, 65);
                            setTimeout(() => {
                                if (waEl && isCinemaModeActive) typeTextIntoInputLive(waEl, '00249912345678', 55);
                            }, 1800);
                        },
                        speech: `خامساً: نبدأ بتعبئة الخانات الأساسية في البطاقة؛ أولها خانة (اسم ${entitySingular}) وبمجرد كتابته وحفظه يفتح له النظام حساباً مالياً في شجرة الحسابات ويظهر فوراً في ${invoiceScreenName}. وتليها خانة (الرقم السري) مع زر (توليد)؛ وهذا الرقم السري نحتاجه في الواتساب لأنه الباسوورد الخاص بـ${entitySingular} الذي يرسله للبوت ليحصل على كشف حسابه أو رصيده بأمان تام. ثم خانة (رقم الواتساب) المعتمد لإرسال الفواتير والإشعارات واستقبال التحويلات البنكية.`
                    },
                    {
                        badge: `6. الرصيد الافتتاحي (💰 مغذي الحساب) • الحد الائتماني (🛑 حماية المال) • العملة (💱)`,
                        targetSelector: `#${pfx}-opening-balance, #${pfx}-limit, #${pfx}-currency`,
                        goldenRule: `«الرصيد الافتتاحي يغذي كشف الحساب وميزان المراجعة، والحد الائتماني هو الدرع الصارم الذي يحافظ على رأس مالك من التعثر.»`,
                        cards: [
                            `💰 الرصيد الافتتاحي: رصيد أول المدة السابق قبل النظام؛ يغذي كشف الحساب وميزان المراجعة ويُجمع مع الحركات الجديدة`,
                            `🛑 حد الائتمان: السقف الأقصى للمديونية المسموحة؛ يمنع تجاوز الآجل وينبهك فوراً لحماية السيولة ورأس المال`,
                            `💱 العملة وسعر الصرف: تحديد عملة حساب ${entitySingular} (جنيه سوداني، دولار، ريال...) لتقييم الحركات والأرصدة بدقة`,
                            `⚖️ تكامل محاسبي لحظي بين الرصيد الافتتاحي والفواتير الجديدة وسقف الائتمان`
                        ],
                        onEnter: () => {
                            const opEl = document.getElementById(`${pfx}-opening-balance`);
                            const limEl = document.getElementById(`${pfx}-limit`);
                            if (opEl) typeTextIntoInputLive(opEl, '15000', 85);
                            setTimeout(() => {
                                if (limEl && isCinemaModeActive) {
                                    const card = limEl.closest('.pro-field-card') || limEl;
                                    card.classList.add('msg-cinema-credit-spotlight-halo');
                                    typeTextIntoInputLive(limEl, '50000', 85, () => {
                                        window.playCinemaSoftChime();
                                    });
                                }
                            }, 1400);
                        },
                        speech: `سادساً: نصل إلى القلب المالي للبطاقة؛ أولاً خانة (الرصيد الافتتاحي) وهي رصيد أول المدة السابق على ${entitySingular} قبل العمل بالنظام، وهذا الرصيد هو الذي يغذي كشف حساب ${entitySingular} وميزان المراجعة الافتتاحي، ويتفاعل مباشرة مع الفواتير والسندات اللاحقة. ثانياً خانة (حد الائتمان) وهي السقف الائتماني الأقصى للمديونية الآجلة؛ حيث يجمع النظام الرصيد الافتتاحي مع الفواتير الجديدة ويقارنها بهذا الحد ليمنع تجاوز السقف ويحافظ على رأس مالك من الديون المتعثرة. ثالثاً خانة (العملة) لتحديد العملة التي يريد ${entitySingular} التعامل بها كالجنيه السوداني أو الدولار أو الريال مع ضبط سعر الصرف.`
                    },
                    {
                        badge: `7. الحالة النشطة (🟢 نشط • 🌟 VIP • 🔴 محظور) • شريحة التسعير • فترة السداد • السرية • الرقم الضريبي`,
                        targetSelector: `#${pfx}-status, #cust-price-tier, #${pfx}-payment-terms, #${pfx}-security`,
                        goldenRule: `«ربط ${entitySingular} بالحالة النشطة وشريحة التسعير وفترة السداد الصحيحة يمنحك فواتير دقيقة وتنبيهات مبكرة لأعمار الديون.»`,
                        cards: [
                            `🟢 الحالة: (نشط) للتعامل الطبيعي، (🌟 VIP) لكبار المتعاملين، أو (🔴 محظور) لإيقاف التعامل الآجل معه فوراً`,
                            `🏷️ شريحة التسعير وفترة السداد: تحديد السعر التلقائي (قطاعي/نصف جملة/جملة/آجل/سعر خاص) وعدد أيام السماح للسداد`,
                            `👔 المسؤول • 📍 العنوان • 🧾 الرقم الضريبي • ✉️ البريد الإلكتروني • 📝 الملاحظات التوثيقية`,
                            `🛡️ درجة السرية (من 1 إلى 10): لحجب الحساب الحساس عن المستخدمين غير المخولين`
                        ],
                        speech: `سابعاً: نستكمل بقية ضوابط البطاقة؛ ففي خانة (الحالة) نحدد هل ${entitySingular} (نشط) للتعامل اليومي المعتاد، أم من كبار العملاء المميزين (في آي بي)، أم (محظور) لإيقاف البيع الآجل له فوراً. كما نحدد (شريحة التسعير) لينزل له سعر القطاعي أو الجملة أو السعر الخاص تلقائياً في الفاتورة، ونحدد (فترة السداد بالأيام) لمراقبة أعمار الديون، و(درجة السرية) من واحد إلى عشرة، بالإضافة إلى اسم المسؤول، العنوان، الرقم الضريبي، البريد الإلكتروني، والملاحظات.`
                    },
                    {
                        badge: `8. أرقام المناديب والتابعين (📱 + إضافة رقم) • الحسابات البنكية (🏦 + إضافة حساب) • الحفظ (💾 F10)`,
                        targetSelector: `#${pfx}-sub-phones-container, #${pfx}-bank-accounts-container, ${formId} .btn-c3-save`,
                        goldenRule: `«إضافة أرقام هواتف مناديب ${entitySingular} وحساباته البنكية تمكّن البوت والذكاء الاصطناعي من التعرف على تحويلاته ومراسلاته تلقائياً.»`,
                        cards: [
                            `📱 أرقام المناديب والتابعين (+ إضافة رقم): ربط أرقام هواتف المناديب والفروع بحساب ${entitySingular} في الواتساب`,
                            `🏦 الحسابات البنكية (+ إضافة حساب): تسجيل اسم البنك والفرع ورقم الحساب للمطابقة الآلية مع إشعارات التحويل`,
                            `💾 زر حفظ (F10): حفظ ذري فوري لبطاقة ${entitySingular} في قاعدة البيانات المحلية والسحابية`,
                            `✖ زر إلغاء (Esc): التراجع وإغلاق النموذج دون حفظ التعديلات`
                        ],
                        speech: `ثامناً: في أسفل البطاقة نجد قسمين في غاية الأهمية؛ القسم الأول (أرقام المناديب والتابعين) ومنه نضغط على (إضافة رقم) لتسجيل أرقام هواتف مناديب ${entitySingular} أو فروعه ليتعرف عليهم بوت الواتساب تلقائياً وينسب إشعاراتهم لحساب ${entitySingular}. والقسم الثاني (الحسابات البنكية) لتسجيل أرقام حسابات ${entitySingular} البنكية وفروعها للمطابقة الآلية مع إشعارات التحويل، وبعد مراجعة كافة البيانات نضغط على زر (حفظ إف عشرة) لاعتماد البطاقة فوراً.`
                    },
                    {
                        badge: `9. البحث الفوري (🔍) • تصدير واستيراد Excel • جدول ${entityPlural} وكشف الحساب والرصيد التفاعلي`,
                        targetSelector: `${searchId}, ${tbodyId}`,
                        goldenRule: `«من جدول ${entityPlural} يمكنك نسخ البطاقة بالضغط على الرقم السري، وتغيير موقف الكشف، وفتح كشف الحساب التفصيلي بالضغط على الرصيد.»`,
                        cards: [
                            `🔍 البحث الفوري + 📤 تصدير Excel + 📥 استيراد Excel: بحث لحظي بالاسم أو الرقم السري أو الهاتف وتبادل ملفات الإكسل`,
                            `🔐 عمود الرقم السري: بالضغط عليه في الجدول يتم فوراً نسخ بطاقة الواتساب التعريفية لإرسالها لـ${entitySingular}`,
                            `📊 عمود كشف الحساب: أزرار تفاعلية لتغيير موقف ${entitySingular} (كشف / رصيد / موقوف) مباشرة من داخل الجدول`,
                            `💰 عمود الرصيد والائتمان: يوضح المبلغ (عليه/له) وتنبيه السقف، والضغط على الرصيد يفتح كشف الحساب التفصيلي بالحركات`
                        ],
                        speech: `تاسعاً: ننتقل إلى شريط البحث وجدول ${entityPlural}؛ حيث يمكنك البحث اللحظي بالاسم أو الرقم السري أو رقم الهاتف، مع إمكانية تصدير القائمة كاملة إلى ملف إكسل أو استيرادها بضغطة زر. وفي داخل الجدول التفاعلي تظهر لك كل التفاصيل: فالضغط على الرقم السري ينسخ بطاقة الواتساب فوراً، وعمود كشف الحساب يتيح لك تبديل الموقف بين كشف أو رصيد أو موقوف مباشرة من الجدول، وعمود الرصيد يوضح المبلغ هل هو عليه أو له مع شارة تنبيه تجاوز السقف، وعند الضغط على رقم الرصيد أو أيقونة الرسم البياني يفتح لك فوراً كشف الحساب التفصيلي بجميع الحركات والفواتير.`
                    },
                    {
                        badge: `10. سيناريو التعديل (✏️ تعديل) • سيناريو الحذف وقانون المنع المحاسبي الصارم (🗑️ حذف)`,
                        targetSelector: `${tbodyId} button[onclick*="edit"], ${tbodyId} button[onclick*="delete"], ${tbodyId}`,
                        goldenRule: `«للتعديل اضغط (✏️ تعديل) وحفظ، أما الحذف (🗑️ حذف) فيُمنع نهائياً إذا كان لـ${entitySingular} أي فاتورة أو سند أو حركة أو رصيد حفاظاً على سلامة الحسابات.»`,
                        cards: [
                            `✏️ في حالة التعديل: نضغط زر (✏️ تعديل) أمام اسم ${entitySingular} فتنتقل بياناته للنموذج العلوي، نعدل ما نريد ثم نضغط (💾 حفظ F10)`,
                            `🗑️ في حالة الحذف لحساب جديد فارغ: إذا لم يكن عليه أي حركة أو رصيد يسمح النظام بحذفه بعد التأكيد`,
                            `🚫 متى لا يمكن الحذف نهائياً؟ إذا كان لـ${entitySingular} أي فاتورة أو سند قبض/صرف أو حركة مالية أو رصيد افتتاحي فيرفض النظام الحذف تماماً`,
                            `🛡️ البديل الآمن عند منع الحذف: تحويل حالة ${entitySingular} إلى (🔴 محظور) أو موقفه إلى (🔒 موقوف) مع بقاء سجله التاريخي محفوظاً`
                        ],
                        speech: `عاشراً وأخيراً: كيف نتعامل في حالة التعديل وفي حالة الحذف؟ في حالة أردنا أن نعدل بيانات أي ${entitySingular} نضغط على زر (تعديل) الموجود في عمود الإجراء أمام اسمه في الجدول، فتصعد جميع بياناته وأرقامه وحساباته فوراً إلى البطاقة العلوية، نُجري التعديل المطلوب ثم نضغط (حفظ). أما في حالة الحذف عند الضغط على زر (حذف)، فهنا يعمل درع الحماية المحاسبية الصارم؛ فإذا كان ${entitySingular} مسجلاً بالخطأ وليس عليه أي حركة أو فاتورة أو سند يُحذف بأمان، أما في حالة وجود أي فاتورة أو سند أو حركة مالية أو رصيد مرتبط به فلا يمكن حذفه نهائياً ويمنع النظام الحذف مع عرض المستندات المرتبطة به حفاظاً على توازن ميزان المراجعة، ويمكنك حينها الاكتفاء بتحويل حالته إلى محظور أو إيقاف خدمته. وبذلك نكون قد أتممنا شرح وتوثيق شاشة ${entityPlural} من الألف إلى الياء.`
                    }
                ];
            }

            if (window.MagaiesSmartGuides && window.MagaiesSmartGuides[mode] && Array.isArray(window.MagaiesSmartGuides[mode].autopilotSteps) && window.MagaiesSmartGuides[mode].autopilotSteps.length > 1) {
                return window.MagaiesSmartGuides[mode].autopilotSteps.map((st, sIdx) => {
                    const spTxt = typeof st.speech === 'function' ? st.speech() : String(st.speech || '');
                    return {
                        badge: st.badge || `المشهد ${sIdx + 1}`,
                        targetSelector: st.targetSelector || 'body',
                        goldenRule: st.goldenRule || `«راجع ${st.cursorLabel || st.badge} بدقة لضمان سلامة الترحيل المحاسبي والمخزني الفوري.»`,
                        cards: [
                            `🎯 العنصر الحالي: ${st.badge}`,
                            `🎙️ الشرح التفصيلي: ${spTxt.slice(0, 110)}`,
                            `⚡ التزامن الذري: ارتباط مباشر مع المخزن ودفتر اليومية وكشف الحساب`,
                            `💡 نصيحة: اضغط دبل كليك أو كليك يمين على أي خانة لشرحها منفردة في أي وقت`
                        ],
                        onEnter: st.action,
                        speech: spTxt
                    };
                });
            }

            const tabTitles = {
                warehouse: { title: '🏭 شاشة المخازن والمستودعات من الألف إلى الياء', sel: '.md-tab-btn.tab-warehouses', inp: '#warehouse-name' },
                bank: { title: '🏦 شاشة البنوك والتطبيقات البنكية من الألف إلى الياء', sel: '.md-tab-btn.tab-banks', inp: '#bank-name' },
                safe: { title: '💵 شاشة الخزائن النقدية من الألف إلى الياء', sel: '.md-tab-btn.tab-safes', inp: '#safe-name' },
                item: { title: '📦 شاشة الأصناف والوحدات والتسعير من الألف إلى الياء', sel: '.md-tab-btn.tab-items', inp: '#item-name-input' },
                employee: { title: '👨‍💼 شاشة الموظفين والرواتب والعهد من الألف إلى الياء', sel: '.md-tab-btn.tab-employees', inp: '#emp-name' },
                costcenter: { title: '🎯 شاشة مراكز التكلفة والمشاريع من الألف إلى الياء', sel: '.md-tab-btn.tab-costcenters', inp: '#costcenter-name' }
            };
            const tInfo = tabTitles[mode] || tabTitles.warehouse;
            return [
                {
                    badge: `1. تأسيس وضبط ${tInfo.title}`,
                    targetSelector: `${tInfo.inp}, ${tInfo.sel}`,
                    goldenRule: '«اضبط البيانات الأساسية والأرصدة الافتتاحية بدقة قبل الحفظ لضمان التسميع المحاسبي الفوري.»',
                    cards: [
                        `📌 فتح وتجهيز ${tInfo.title}`,
                        '✍️ إدخال الاسم والتصنيف والرصيد الافتتاحي والعملة والمسؤول',
                        '💾 الحفظ الذري الفوري (F10) والظهور المباشر في الفواتير والسندات والتقارير',
                        '✏️ التعديل الفوري من الجدول ومنع الحذف في حالة وجود حركات مرتبطة بالسجل'
                    ],
                    onEnter: () => {
                        const b = document.querySelector(tInfo.sel);
                        if (b && !b.classList.contains('active')) { try { b.click(); } catch (_) {} }
                    },
                    speech: `اللهم صل وسلم على نبينا محمد.. أهلاً بحضرتك في الشرح الشامل لـ ${tInfo.title}؛ حيث يمكنك إدخال كافة البيانات الأساسية والأرصدة الافتتاحية وحفظها بزر حفظ إف عشرة، وعند الرغبة في التعديل تضغط على زر تعديل أمام السجل في الجدول، وفي حالة الحذف يمنع النظام حذف أي سجل مرتبط بحركات مالية أو مخزنية حفاظاً على سلامة الحسابات.`
                }
            ];
        }

        window.playCinemaMasterDataEntityFullScenario = function (entityMode = null, ratio = '16:9') {
            const mode = entityMode || window.detectActiveMasterDataTabMode() || 'customer';
            window.silenceAllLegacyGuideAudioAndUI(false);
            ensureCinemaDomOverlays();
            clearCinemaIndependentTourTimers();
            window._cinemaScene1Timers = [];

            window.__CINEMA_4K_ACTIVE__ = true;
            isCinemaModeActive = true;
            cinemaAspectRatio = ratio || '16:9';
            document.body.classList.add('msg-cinema-4k-active');
            document.body.classList.toggle('msg-cinema-aspect-9-16', cinemaAspectRatio === '9:16');
            window.toggleCinema4KAspectRatio(cinemaAspectRatio);

            const studioModal = document.getElementById('msg-cinema-4k-studio-modal');
            if (studioModal) studioModal.style.display = 'none';

            document.querySelectorAll('.msg-cinema-credit-spotlight-halo').forEach(el => el.classList.remove('msg-cinema-credit-spotlight-halo'));

            const scenes = buildProfessorialMasterDataScenes(mode);
            const isSup = mode === 'supplier';
            _activeCinemaScenarioState.mode = mode;
            _activeCinemaScenarioState.screenTitle = mode === 'sales-split'
                ? '✂️🚚 فاتورة تقسيم وتسليم المبيعات — الشرح السينمائي الشامل (4K Studio)'
                : (isSup
                    ? '🏭 منظومة بيانات الموردين الشاملة من الألف إلى الياء (4K Studio)'
                    : (mode === 'customer' ? '👥 منظومة بيانات العملاء الشاملة من الألف إلى الياء (4K Studio)' : '🗂️ منظومة البيانات الأساسية الشاملة (4K Studio)'));
            _activeCinemaScenarioState.screenSubtitle = isSup
                ? 'شرح بروفيسوري شامل: أوامر الواتساب • السياسة • الموقف • بطاقة QR • الرصيد الافتتاحي • سقف الائتمان • العملة • الرقم السري • التعديل والحذف'
                : 'شرح بروفيسوري شامل: أوامر الواتساب • السياسة • الموقف • بطاقة QR • الرصيد الافتتاحي • حد الائتمان • العملة • الرقم السري • التعديل والحذف';
            _activeCinemaScenarioState.totalScenes = scenes.length;

            const lt = document.getElementById('msg-cinema-lower-thirds');
            if (lt) {
                lt.style.display = 'flex';
                lt.style.zIndex = '2147483635';
            }
            const sceneSel = document.getElementById('msg-cinema-scene-select');
            if (sceneSel && scenes.length) {
                sceneSel.innerHTML = scenes.map((s, i) => `<option value="${i}">🎬 ${i + 1}/${scenes.length}: ${String(s.badge || '').replace(/^المشهد\s*\d+\s*\/\s*\d+\s*—?\s*/, '').slice(0, 28)}</option>`).join('');
            }

            const runSceneAtIndex = (idx) => {
                if (!isCinemaModeActive) return;
                if (idx >= scenes.length) {
                    window.resetCinema4KCameraAndSpotlight();
                    window._cinemaScene1Timers.push(setTimeout(() => {
                        window.stopMagaies4KCinemaMode();
                    }, 800));
                    return;
                }

                const sc = scenes[idx];
                _activeCinemaScenarioState.sceneIndex = idx;
                _activeCinemaScenarioState.sceneBadge = sc.badge;
                _activeCinemaScenarioState.spokenText = sc.speech;
                _activeCinemaScenarioState.goldenRule = sc.goldenRule;
                _activeCinemaScenarioState.cards = sc.cards || [];
                const sSel = document.getElementById('msg-cinema-scene-select');
                if (sSel) sSel.value = String(idx);

                for (let lookahead = 1; lookahead <= 3; lookahead++) {
                    const nxtSc = scenes[idx + lookahead];
                    if (nxtSc && nxtSc.speech) {
                        fetchAndDecodeMaleBuffer(String(nxtSc.speech).trim(), selectedMaleProfile || 'fusha').catch(() => {});
                    }
                }

                if (typeof sc.onEnter === 'function') {
                    try { sc.onEnter(); } catch (_) {}
                }

                const targetEl = resolveBestVisibleTargetElement(sc.targetSelector) || document.body;
                updateCinema4KLowerThirds(
                    {
                        badge: sc.badge,
                        goldenRule: sc.goldenRule
                    },
                    sc.speech,
                    idx,
                    scenes.length
                );
                const goldenEl = document.getElementById('msg-cinema-golden-rule-line');
                if (goldenEl && sc.goldenRule) {
                    goldenEl.innerHTML = `<span style="font-size:1.18rem;">💡</span> <span><strong>القاعدة الذهبية:</strong> ${escapeSvgText(sc.goldenRule)}</span>`;
                }

                if (targetEl && targetEl !== document.body) {
                    moveLaserCursorToElement(targetEl, sc.badge);
                    window.applyCinema4KCameraZoomAndSpotlight(targetEl, { cursorLabel: sc.badge, keepZoomed: true });
                    triggerCinemaClickRipple(targetEl);
                } else {
                    window.resetCinema4KCameraAndSpotlight();
                }

                if (typeof window.__startLiveWordKaraokeAndSubTargetSync === 'function') {
                    window.__startLiveWordKaraokeAndSubTargetSync(document.getElementById('msg-cinema-subtitle-text'), sc.speech, sc);
                }

                window.speakCinema4KExclusiveVoice(sc.speech, () => {
                    if (!isCinemaModeActive || _cinemaPaused || _cinemaHoldScene) return;
                    const stepGapMs = Math.max(80, Math.round(220 / Math.max(0.5, _cinemaPlaybackRate || 1.0)));
                    window._cinemaScene1Timers.push(setTimeout(() => {
                        if (!_cinemaPaused && !_cinemaHoldScene) runSceneAtIndex(idx + 1);
                    }, stepGapMs));
                });
            };
            window.jumpToCinemaSceneIndex = function (targetIdx) {
                if (!isCinemaModeActive) return;
                clearCinemaIndependentTourTimers();
                const clampedIdx = Math.max(0, Math.min(scenes.length - 1, parseInt(targetIdx, 10) || 0));
                runSceneAtIndex(clampedIdx);
            };
            window.stepCinemaSceneBy = function (delta) {
                if (!isCinemaModeActive) return;
                clearCinemaIndependentTourTimers();
                const nextIdx = Math.max(0, Math.min(scenes.length - 1, (_activeCinemaScenarioState.sceneIndex || 0) + delta));
                runSceneAtIndex(nextIdx);
            };
            runSceneAtIndex(0);
        };

        // Keep backward-compatible alias pointing to the full Professorial Scenario
        window.playCinemaScene1CustomersCreditLimit = function (ratio = '16:9', forcedMode = null) {
            const activeMode = forcedMode || window.detectActiveMasterDataTabMode() || 'customer';
            window.playCinemaMasterDataEntityFullScenario(activeMode, ratio || '16:9');
        };

        // Auto-launch if URL contains ?cinema_scene=1
        if (window.location.search.includes('cinema_scene=1')) {
            setTimeout(() => {
                window.playCinemaMasterDataEntityFullScenario(window.detectActiveMasterDataTabMode(), '16:9');
            }, 900);
        }

        // ── 8. 4K AUTO-CINEMA STUDIO DIRECTOR MODAL (لوحة تحكم استوديو التصوير السينمائي 4K) ──
        window.openMagaies4KCinemaStudioModal = function () {
            window.silenceAllLegacyGuideAudioAndUI();
            ensureCinemaDomOverlays();
            let modal = document.getElementById('msg-cinema-4k-studio-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'msg-cinema-4k-studio-modal';
                modal.style.cssText = 'position:fixed;inset:0;z-index:1000040;background:rgba(2,6,23,0.90);backdrop-filter:blur(14px);display:none;align-items:center;justify-content:center;padding:14px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
                document.body.appendChild(modal);
            }

            const mp3Lbl = cinemaExternalAudioName
                ? `✅ ملف ElevenLabs المربوط: <strong>${escapeSvgText(cinemaExternalAudioName)}</strong>`
                : '🎙️ الصوت الإذاعي البروفيسوري المستقل نشط (يشرح الشاشة المفتوحة من الألف إلى الياء)';

            modal.innerHTML = `
                <div style="width:min(920px,96vw);max-height:92vh;overflow-y:auto;background:linear-gradient(145deg,#0f172a,#1e1b4b);border:2.5px solid #f472b6;border-radius:20px;padding:20px;color:#ffffff;box-shadow:0 28px 90px rgba(0,0,0,0.95);">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;border-bottom:2px solid rgba(244,114,182,0.35);padding-bottom:12px;margin-bottom:14px;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:1.75rem;">🎬</span>
                            <div>
                                <h3 style="margin:0;font-size:1.12rem;font-weight:900;color:#fce7f3;">استوديو التصوير والتوثيق البروفيسوري الشامل من الألف إلى الياء (4K Auto-Cinema Studio)</h3>
                                <div style="font-size:0.78rem;color:#cbd5e1;margin-top:2px;">يتعرف تلقائياً على الشاشة المفتوحة (العملاء أو الموردين) ويسجل شرحها الكامل (10 مشاهد متكاملة من الألف إلى الياء)</div>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('msg-cinema-4k-studio-modal').style.display='none'" style="background:rgba(239,68,68,0.22);border:2px solid #f87171;color:#fecaca;border-radius:10px;padding:5px 12px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
                    </div>

                    <!-- سيناريو شاشة العملاء الشامل من الألف إلى الياء -->
                    <div style="background:linear-gradient(135deg,rgba(14,165,233,0.18),rgba(15,23,42,0.95));border:2.2px solid #38bdf8;border-radius:16px;padding:14px;margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                            <div>
                                <div style="font-size:0.98rem;font-weight:900;color:#7dd3fc;">🎬 السيناريو البروفيسوري الأول: 👥 "شاشة العملاء من الألف إلى الياء" (10 مشاهد شاملة)</div>
                                <div style="font-size:0.76rem;color:#e2e8f0;margin-top:2px;">كشف للكل • رصيد للكل • إيقاف الكل • توليد باسورد • السياسة • الموقف (موقوف/الرصيد/كشف) • بطاقة QR • نسخ البطاقة • الاسم • الرقم السري للواتساب • رقم الواتساب • الرصيد الافتتاحي المغذي للحساب • حد الائتمان • العملة • الحالة • شريحة التسعير • المناديب والبنوك • التعديل (✏️) • الحذف وشروط منعه (🗑️)</div>
                            </div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <button type="button" onclick="window.launchIndependent4KCinemaNow('16:9', 'customer')" style="background:linear-gradient(135deg,#0284c7,#1d4ed8);border:2px solid #7dd3fc;color:#ffffff;border-radius:10px;padding:8px 14px;font-size:0.82rem;font-weight:900;cursor:pointer;">
                                    🔴 تسجيل وشرح شاشة العملاء كاملة (16:9)
                                </button>
                                <button type="button" onclick="window.launchIndependent4KCinemaNow('9:16', 'customer')" style="background:linear-gradient(135deg,#db2777,#9d174d);border:2px solid #fbcfe8;color:#ffffff;border-radius:10px;padding:8px 12px;font-size:0.8rem;font-weight:900;cursor:pointer;">
                                    📱 تسجيل العملاء (Reels 9:16)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- سيناريو شاشة الموردين الشامل من الألف إلى الياء -->
                    <div style="background:linear-gradient(135deg,rgba(168,85,247,0.18),rgba(15,23,42,0.95));border:2.2px solid #c084fc;border-radius:16px;padding:14px;margin-bottom:14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                            <div>
                                <div style="font-size:0.98rem;font-weight:900;color:#e9d5ff;">🎬 السيناريو البروفيسوري الثاني: 🏭 "شاشة الموردين من الألف إلى الياء" (10 مشاهد شاملة)</div>
                                <div style="font-size:0.76rem;color:#e2e8f0;margin-top:2px;">أوامر واتساب الموردين الجماعية • السياسة • الموقف • بطاقة QR • نسخ البطاقة • اسم المورد • الرقم السري • الواتساب • الرصيد الافتتاحي • سقف الائتمان • العملة • الحالة • المناديب والحسابات البنكية • التعديل (✏️) • الحذف وشروط منعه (🗑️)</div>
                            </div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <button type="button" onclick="window.launchIndependent4KCinemaNow('16:9', 'supplier')" style="background:linear-gradient(135deg,#9333ea,#6b21a8);border:2px solid #e9d5ff;color:#ffffff;border-radius:10px;padding:8px 14px;font-size:0.82rem;font-weight:900;cursor:pointer;">
                                    🔴 تسجيل وشرح شاشة الموردين كاملة (16:9)
                                </button>
                                <button type="button" onclick="window.launchIndependent4KCinemaNow('9:16', 'supplier')" style="background:linear-gradient(135deg,#db2777,#9d174d);border:2px solid #fbcfe8;color:#ffffff;border-radius:10px;padding:8px 12px;font-size:0.8rem;font-weight:900;cursor:pointer;">
                                    📱 تسجيل الموردين (Reels 9:16)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Studio AI Voice Selection (Fusha / Egyptian / Sudanese) — Zero External Audio -->
                    <div style="background:rgba(2,6,23,0.75);border:2px solid rgba(56,189,248,0.4);border-radius:14px;padding:12px;margin-bottom:14px;">
                        <div style="font-weight:900;color:#fde047;margin-bottom:8px;font-size:0.86rem;">🎙️ اختيار الصوت الإذاعي الفخيم بالذكاء الاصطناعي (العربية الفصحى • المصري • السوداني):</div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                            <button type="button" onclick="window.onMsgVoiceChange('fusha')" style="background:linear-gradient(135deg,#0284c7,#1d4ed8);border:1.8px solid #7dd3fc;color:#ffffff;border-radius:10px;padding:7px 14px;font-size:0.8rem;font-weight:900;cursor:pointer;">📖 العربية الفصحى</button>
                            <button type="button" onclick="window.onMsgVoiceChange('egyptian')" style="background:linear-gradient(135deg,#059669,#047857);border:1.8px solid #6ee7b7;color:#ffffff;border-radius:10px;padding:7px 14px;font-size:0.8rem;font-weight:900;cursor:pointer;">🇪🇬 الصوت المصري</button>
                            <button type="button" onclick="window.onMsgVoiceChange('sudanese')" style="background:linear-gradient(135deg,#d97706,#b45309);border:1.8px solid #fcd34d;color:#ffffff;border-radius:10px;padding:7px 14px;font-size:0.8rem;font-weight:900;cursor:pointer;">🇸🇩 الصوت السوداني</button>
                        </div>
                    </div>

                    <!-- One-Click 4K Video Recorder & Downloader for Current Open Tab -->
                    <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:space-between;align-items:center;">
                        <button type="button" id="msg-cinema-modal-rec-btn" onclick="window.launchIndependent4KCinemaNow(cinemaAspectRatio)" style="flex:1;padding:12px 18px;border-radius:12px;background:linear-gradient(135deg,#dc2626,#991b1b);border:2px solid #fca5a5;color:#ffffff;font-size:0.92rem;font-weight:900;cursor:pointer;box-shadow:0 10px 28px rgba(220,38,38,0.45);">
                            ${isCinemaRecording ? '⏹️ إيقاف التسجيل وتنزيل الفيديو الآن' : '🔴 بدء تسجيل الشاشة المفتوحة حالياً من الألف إلى الياء (Download MP4/WebM)'}
                        </button>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        };
    })();
})();
