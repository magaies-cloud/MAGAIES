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
            z-index: 100020;
            pointer-events: none;
            display: none;
            transition: top 0.65s cubic-bezier(0.22, 1, 0.36, 1), left 0.65s cubic-bezier(0.22, 1, 0.36, 1), transform 0.18s ease;
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
            color: #020617;
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
            z-index: 100010;
            pointer-events: none;
            display: none;
            border: 3.5px solid #fbbf24;
            border-radius: 16px;
            box-shadow: 0 0 32px rgba(251, 191, 36, 0.9), inset 0 0 18px rgba(251, 191, 36, 0.35), 0 0 0 9999px rgba(2, 6, 23, 0.36);
            transition: all 0.32s cubic-bezier(0.22, 1, 0.36, 1);
            animation: msgTargetPulse 1.4s infinite ease-in-out;
        }
        @keyframes msgTargetPulse {
            0%, 100% { border-color: #fbbf24; box-shadow: 0 0 28px rgba(251, 191, 36, 0.85), 0 0 0 9999px rgba(2, 6, 23, 0.36); }
            50% { border-color: #38bdf8; box-shadow: 0 0 38px rgba(56, 189, 248, 0.95), 0 0 0 9999px rgba(2, 6, 23, 0.36); }
        }

        /* ── ROYAL COMPACT NON-BLOCKING GUIDE CONTROL POD (أمر حاسم 2: كبسولة Micro-HUD لا تحجب أي شيء وتتبع السمات السبع 100%) ── */
        .msg-live-coach-bar {
            position: fixed;
            bottom: 12px;
            left: 12px;
            z-index: 100060;
            width: auto;
            max-width: min(540px, 95vw);
            background: linear-gradient(160deg, rgba(2, 6, 23, 0.96) 0%, rgba(15, 23, 42, 0.98) 55%, rgba(23, 15, 48, 0.96) 100%);
            color: #ffffff !important;
            border: 2.2px solid rgba(251, 191, 36, 0.9) !important;
            border-radius: 999px;
            padding: 5px 12px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.85), 0 0 18px rgba(251, 191, 36, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.22);
            display: none;
            flex-direction: row;
            align-items: center;
            gap: 6px;
            direction: rtl;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: top 0.28s cubic-bezier(0.22, 1, 0.36, 1), bottom 0.28s cubic-bezier(0.22, 1, 0.36, 1), transform 0.22s ease;
        }
        .msg-live-coach-bar.docked-top {
            bottom: auto !important;
            top: 86px !important;
            left: 12px !important;
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
            background: #ffffff !important;
            color: #000000 !important;
            border: 2px solid #0284c7 !important;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.22) !important;
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
            color: #000000 !important;
        }
        #msg-live-select-mirror {
            position: fixed;
            z-index: 100055;
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

        /* Each 4-Button Pod (2 on top, 2 on bottom inside a glowing royal rectangle — Reduced outer margins & taller inner buttons) */
        .msg-fixed-command-pod {
            width: 223px !important;
            min-width: 223px !important;
            max-width: 223px !important;
            height: 70px !important;
            min-height: 70px !important;
            max-height: 70px !important;
            padding: 3px 4px !important;
            border-radius: 13px !important;
            background: linear-gradient(160deg, rgba(2, 6, 23, 0.96) 0%, rgba(15, 23, 42, 0.98) 55%, rgba(23, 15, 48, 0.96) 100%) !important;
            border: 1.5px solid rgba(251, 191, 36, 0.85) !important;
            box-shadow: 0 7px 21px rgba(0, 0, 0, 0.85), 0 0 16px rgba(251, 191, 36, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.22) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            grid-template-rows: 1fr 1fr !important;
            gap: 3px !important;
            direction: rtl !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            box-sizing: border-box !important;
            flex-shrink: 0 !important;
            animation: msgStarPodGlow 3.5s ease-in-out infinite alternate;
        }
        .msg-fixed-command-pod.pod-smart-guide {
            grid-template-areas:
                "guide tv"
                "mic   back";
        }
        .msg-fixed-command-pod.pod-system-tools {
            grid-template-areas:
                "mobile   fullinst"
                "appinst  themes";
        }
        .msg-fixed-command-pod.pod-utility-quad {
            width: 223px !important;
            min-width: 223px !important;
            max-width: 223px !important;
            grid-template-areas:
                "shield fullscr"
                "search qtheme";
        }

        @keyframes msgStarPodGlow {
            0% {
                border-color: rgba(251, 191, 36, 0.78);
                box-shadow: 0 7px 21px rgba(0, 0, 0, 0.8), 0 0 14px rgba(251, 191, 36, 0.28);
            }
            100% {
                border-color: #fef08a;
                box-shadow: 0 9px 25px rgba(0, 0, 0, 0.88), 0 0 21px rgba(251, 191, 36, 0.58), 0 0 30px rgba(56, 189, 248, 0.28);
            }
        }

        /* Unified Button Styling Across All Pods (Increased height to fill pod & minimize margins) */
        .msg-quartet-btn {
            position: relative !important;
            width: 100% !important;
            height: 100% !important;
            min-height: 29.5px !important;
            max-height: 31px !important;
            padding: 2px 5.5px !important;
            border-radius: 8.5px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 4.5px !important;
            cursor: pointer !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.2s ease, border-color 0.2s ease, filter 0.2s ease !important;
            text-align: center !important;
        }
        .msg-quartet-btn::after {
            content: '';
            position: absolute;
            top: -60%;
            left: -130%;
            width: 70%;
            height: 220%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.38), transparent);
            transform: rotate(25deg);
            animation: msgOpticalSweep 3.8s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes msgOpticalSweep {
            0% { left: -130%; }
            45%, 100% { left: 160%; }
        }
        .msg-quartet-btn:hover {
            transform: translateY(-1px) scale(1.02) !important;
            border-color: #ffffff !important;
            filter: brightness(1.15) !important;
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

        .msg-quartet-mic {
            grid-area: mic;
            background: linear-gradient(135deg, #03261d 0%, #047857 55%, #059669 100%) !important;
            border: 1.5px solid #34d399 !important;
            box-shadow: 0 0 14px rgba(52, 211, 153, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-mic .msg-quartet-icon-box {
            background: linear-gradient(135deg, #10b981, #059669);
            border: 1px solid #a7f3d0;
        }
        .msg-quartet-mic.listening {
            background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%) !important;
            border-color: #fef08a !important;
            animation: msgMicListeningPulse 1s infinite ease-in-out;
        }
        @keyframes msgMicListeningPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 16px rgba(239, 68, 68, 0.8); }
            50% { transform: scale(1.04); box-shadow: 0 0 28px rgba(254, 240, 138, 0.95); }
        }
        .msg-quartet-mic:hover {
            box-shadow: 0 0 24px rgba(52, 211, 153, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
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
            background: linear-gradient(135deg, #2e1065 0%, #6b21a8 55%, #a21caf 100%) !important;
            border: 1.5px solid #e879f9 !important;
            box-shadow: 0 0 14px rgba(232, 121, 249, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .msg-quartet-qtheme .msg-quartet-icon-box {
            background: linear-gradient(135deg, #9333ea, #c026d3);
            border: 1px solid #f5d0fe;
        }
        .msg-quartet-qtheme:hover {
            box-shadow: 0 0 24px rgba(232, 121, 249, 0.95), 0 6px 16px rgba(0, 0, 0, 0.6);
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

        /* Smart Hover Micro-Tooltip Capsule */
        .msg-smart-hover-tooltip {
            position: fixed;
            z-index: 100090;
            max-width: 340px;
            background: linear-gradient(155deg, rgba(2, 6, 23, 0.97), rgba(15, 23, 42, 0.98));
            border: 1.5px solid #38bdf8;
            border-radius: 14px;
            padding: 9px 13px;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.88), 0 0 22px rgba(56, 189, 248, 0.35);
            color: #f8fafc;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            direction: rtl;
            display: none;
            flex-direction: column;
            gap: 6px;
            pointer-events: auto;
            backdrop-filter: blur(14px);
            animation: msgModalPop 0.2s ease-out;
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

    let selectedMaleProfile = localStorage.getItem('msg_male_dialect_v6') || 'egyptian'; // 'egyptian' | 'sudanese' | 'gulf'
    if (!['sudanese', 'egyptian', 'gulf'].includes(selectedMaleProfile)) {
        selectedMaleProfile = 'egyptian';
    }
    let selectedSpeechRate = 1.30;
    try {
        const savedRate = parseFloat(localStorage.getItem('msg_male_speed_v6'));
        if (!isNaN(savedRate) && savedRate >= 0.75 && savedRate <= 1.60 && savedRate !== 0.92) {
            selectedSpeechRate = savedRate;
        } else {
            selectedSpeechRate = 1.30;
            localStorage.setItem('msg_male_speed_v6', '1.30');
        }
    } catch(e) { selectedSpeechRate = 1.30; }
    const decodedBufferCache = new Map();
    const rawArrayBufferCache = new Map();
    const isGenuineNeuralCache = new Map();

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

    function stopCurrentSpeech() {
        currentSpeechRequestId++;
        if (nextStepTimeoutId) {
            clearTimeout(nextStepTimeoutId);
            nextStepTimeoutId = null;
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
        if ('speechSynthesis' in window) {
            try { window.speechSynthesis.cancel(); } catch (e) {}
        }
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
        } else if (dialect === 'gulf') {
            txt = txt
                .replace(/الآن/g, 'الحين')
                .replace(/مباشرة/g, 'سيدة')
                .replace(/القادمة/g, 'الجاية')
                .replace(/تريد/g, 'تبغى')
                .replace(/أردت/g, 'بغيت')
                .replace(/بجانبه/g, 'جنبه')
                .replace(/أمامك/g, 'قدامك')
                .replace(/لإدخال/g, 'عشان تدخل')
                .replace(/لتسليم/g, 'عشان تسلم')
                .replace(/لاستلام/g, 'عشان تستلم');
        }
        return txt;
    }

    // ── أمر حاسم (1): قفل صارم لنطق الصلاة على النبي ﷺ مرة واحدة فقط مع ضغطة المرشد وفتحة الشاشة ومنع تكرارها بين الخانات والنقاط نهائياً ──
    let hasPlayedOpeningInvocation = false;
    window.hasPlayedOpeningInvocation = false;

    function stripAllSalawatPrefixes(rawText) {
        let txt = String(rawText || '').trim();
        if (!txt) return '';
        txt = txt
            .replace(/^اللَّهُمَّ\s+صَلِّ\s+وَسَلِّمْ\s+عَلَى\s+نَبِيِّنَا\s+مُحَمَّدٍ[.،\s]*/g, '')
            .replace(/^اللَّهُمَّ\s+صَلِّ\s+عَلَى\s+مُحَمَّدٍ\s+وَعَلَى\s+آلِ\s+مُحَمَّدٍ[.،\s]*/g, '')
            .replace(/^اللهم\s+صل\s+وسلم\s+على\s+نبينا\s+محمد[.،\s]*/g, '')
            .replace(/^اللهم\s+صل\s+على\s+محمد\s+وعلى\s+آل\s+محمد[.،\s]*/g, '')
            .replace(/^اللهم\s+صل\s+على\s+سيدنا\s+محمد[.،\s]*/g, '')
            .replace(/شرح\s+من\s+الألف\s+(إلى|ل)\s*الياء/g, 'الشرح الشامل')
            .replace(/من\s+الألف\s+(إلى|ل)\s*الياء/g, '')
            .replace(/\s{2,}/g, ' ')
            .trim();
        return txt;
    }

    function ensureLeadingSalawat(rawText, allowOpeningOnce = false) {
        const cleanBody = stripAllSalawatPrefixes(rawText);
        if (!allowOpeningOnce) {
            return cleanBody;
        }
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
            const base = (typeof step.speech === 'function') ? step.speech() : (step.speech || '');
            out = adaptTextToDialect(base, dialect);
        }
        out = stripAllSalawatPrefixes(out);
        if (isFirstStepOfSession) {
            out = `${SHORT_SAHIHAYN_SALAWAT} ${out}`.trim();
        }
        return out;
    }

    async function fetchAndDecodeMaleBuffer(text, dialect = selectedMaleProfile) {
        const cleanTxt = text.trim();
        const cacheKey = `${dialect}|${cleanTxt}`;
        if (decodedBufferCache.has(cacheKey)) {
            return {
                buffer: decodedBufferCache.get(cacheKey),
                isGenuineNeural: isGenuineNeuralCache.get(cacheKey) !== false
            };
        }
        if (inFlightBufferPromises.has(cacheKey)) {
            return await inFlightBufferPromises.get(cacheKey);
        }

        const taskPromise = (async () => {
            let arrayBuf = rawArrayBufferCache.get(cacheKey);
            let isGenuine = isGenuineNeuralCache.get(cacheKey);
            if (!arrayBuf) {
                const url = `api/tts_male.php?dialect=${encodeURIComponent(dialect)}&text=${encodeURIComponent(cleanTxt)}`;
                const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                const tid = ctrl ? setTimeout(() => ctrl.abort(), 1400) : null;
                try {
                    const res = await fetch(url, { signal: ctrl ? ctrl.signal : undefined });
                    if (tid) clearTimeout(tid);
                    if (!res.ok) throw new Error('TTS HTTP Error ' + res.status);
                    const engineHdr = res.headers.get('X-Voice-Engine') || 'Edge-Neural-Male';
                    isGenuine = (engineHdr === 'Edge-Neural-Male');
                    arrayBuf = await res.arrayBuffer();
                    if (arrayBuf && arrayBuf.byteLength > 300) {
                        rawArrayBufferCache.set(cacheKey, arrayBuf.slice(0));
                        isGenuineNeuralCache.set(cacheKey, isGenuine);
                    } else {
                        throw new Error('Empty TTS buffer');
                    }
                } catch (err) {
                    if (tid) clearTimeout(tid);
                    throw err;
                }
            }
            const ctx = getAudioCtx();
            const decoded = await ctx.decodeAudioData(arrayBuf.slice(0));
            decodedBufferCache.set(cacheKey, decoded);
            return { buffer: decoded, isGenuineNeural: isGenuine !== false };
        })();

        inFlightBufferPromises.set(cacheKey, taskPromise);
        try {
            return await taskPromise;
        } finally {
            inFlightBufferPromises.delete(cacheKey);
        }
    }

    let isPrewarmingNow = false;
    // Pre-warm all guide steps sequentially in the background so 100% genuine male voice is cached without socket collisions!
    async function prewarmGuideAudio(guideKey = 'smart-logistics', dialect = selectedMaleProfile) {
        if (isPrewarmingNow) return;
        const data = window.MagaiesSmartGuides && window.MagaiesSmartGuides[guideKey];
        if (!data || !Array.isArray(data.autopilotSteps)) return;
        isPrewarmingNow = true;
        try {
            const allSteps = [...data.autopilotSteps, ...(typeof SALES_ONLY_STEPS !== 'undefined' && guideKey === 'smart-logistics' ? SALES_ONLY_STEPS : [])];
            for (let idx = 0; idx < allSteps.length; idx++) {
                const st = allSteps[idx];
                const txt = resolveStepSpeech(st, dialect, idx === 0);
                const cacheKey = `${dialect}|${txt.trim()}`;
                if (txt && !rawArrayBufferCache.has(cacheKey)) {
                    try {
                        const res = await fetch(`api/tts_male.php?dialect=${encodeURIComponent(dialect)}&text=${encodeURIComponent(txt.trim())}`);
                        if (res.ok) {
                            const engineHdr = res.headers.get('X-Voice-Engine') || 'Edge-Neural-Male';
                            const buf = await res.arrayBuffer();
                            if (buf && buf.byteLength > 300) {
                                rawArrayBufferCache.set(cacheKey, buf);
                                isGenuineNeuralCache.set(cacheKey, engineHdr === 'Edge-Neural-Male');
                            }
                        }
                        await new Promise(r => setTimeout(r, 90));
                    } catch (e) {}
                }
            }
        } finally {
            isPrewarmingNow = false;
        }
    }

    function startBufferAtOffset(offsetSec = 0) {
        if (!activeAudioBuffer) return;
        if (currentAudioSource) {
            try {
                currentAudioSource.onended = null;
                currentAudioSource.stop();
            } catch (e) {}
            currentAudioSource = null;
        }

        const ctx = getAudioCtx();
        const clampedOffset = Math.max(0, Math.min(offsetSec, Math.max(0, activeAudioBuffer.duration - 0.08)));
        activeAudioOffset = clampedOffset;
        activeAudioStartTime = ctx.currentTime;
        isAudioPaused = false;
        isAudioPlaying = true;

        const source = ctx.createBufferSource();
        source.buffer = activeAudioBuffer;

        if (activeAudioIsGenuine) {
            // 🎙️ 4-STAGE STUDIO BARITONE BROADCAST DSP CHAIN (صوت رجالي إذاعي فخيم وقمة في الوضوح)
            const pitchCompCents = Math.round(-1200 * Math.log2(selectedSpeechRate / 0.92)) - 45;
            source.detune.value = Math.max(-480, Math.min(450, pitchCompCents));
            source.playbackRate.value = selectedSpeechRate;

            // 1. Deep Baritone Chest Resonance (فخامة وعمق الصوت الرجالي)
            const warmLow = ctx.createBiquadFilter();
            warmLow.type = 'lowshelf';
            warmLow.frequency.value = 165;
            warmLow.gain.value = 3.8;

            // 2. Anti-Muffle Boxiness Cut (إزالة أي كتمة صوتية)
            const deMud = ctx.createBiquadFilter();
            deMud.type = 'peaking';
            deMud.frequency.value = 340;
            deMud.Q.value = 1.4;
            deMud.gain.value = -2.2;

            // 3. Crystal Arabic Diction & Articulation Presence (قمة وضوح مخارج الحروف)
            const clarity = ctx.createBiquadFilter();
            clarity.type = 'peaking';
            clarity.frequency.value = 3100;
            clarity.Q.value = 0.95;
            clarity.gain.value = 4.2;

            const crispAir = ctx.createBiquadFilter();
            crispAir.type = 'highshelf';
            crispAir.frequency.value = 5800;
            crispAir.gain.value = 2.5;

            // 4. Broadcast Studio Compressor & Clean Makeup Gain
            const comp = ctx.createDynamicsCompressor();
            comp.threshold.value = -20;
            comp.knee.value = 12;
            comp.ratio.value = 3.2;
            comp.attack.value = 0.005;
            comp.release.value = 0.14;

            const outGain = ctx.createGain();
            outGain.gain.value = 1.26;

            source.connect(warmLow);
            warmLow.connect(deMud);
            deMud.connect(clarity);
            clarity.connect(crispAir);
            crispAir.connect(comp);
            comp.connect(outGain);
            outGain.connect(ctx.destination);
        } else {
            source.detune.value = -280;
            source.playbackRate.value = selectedSpeechRate;
            source.connect(ctx.destination);
        }

        currentAudioSource = source;
        source.onended = () => {
            if (currentAudioSource === source && !isAudioPaused) {
                currentAudioSource = null;
                isAudioPlaying = false;
                activeAudioOffset = activeAudioBuffer ? activeAudioBuffer.duration : 0;
                updateAudioPlayerUI();
                stopProgressLoop();
                if (activeAudioOnEnd) activeAudioOnEnd();
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
            activeAudioOffset = getCurrentAudioOffset();
            isAudioPaused = true;
            isAudioPlaying = false;
            if (nextStepTimeoutId) {
                clearTimeout(nextStepTimeoutId);
                nextStepTimeoutId = null;
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

    function selectBestBrowserArabicMaleVoice(dialect = selectedMaleProfile) {
        if (!('speechSynthesis' in window)) return null;
        const voices = window.speechSynthesis.getVoices() || [];
        if (!voices.length) return null;

        const femaleBlacklist = /oda|hoda|salma|zariyah|fatima|amany|laila|mariam|noura|female|أنثى|سلمى|هدى|فاطمة|زارية|أماني/i;
        const malePreferred = /shakir|hamed|hamdan|maged|tarik|naayf|omar|ali|male|شاكر|حامد|حمدان|ماجد|طارق|نايف/i;

        const arVoices = voices.filter(v => /^ar/i.test(v.lang || '') || /arabic|عربي/i.test(v.name || ''));
        const nonFemaleAr = arVoices.filter(v => !femaleBlacklist.test(v.name || ''));

        if (dialect === 'egyptian') {
            const egMale = nonFemaleAr.find(v => /shakir|eg/i.test(v.name + ' ' + v.lang) && malePreferred.test(v.name));
            if (egMale) return egMale;
        }
        const explicitMale = nonFemaleAr.find(v => malePreferred.test(v.name || ''));
        if (explicitMale) return explicitMale;
        if (nonFemaleAr.length > 0) return nonFemaleAr[0];
        return arVoices[0] || null;
    }

    function speakWithBrowserMaleFallback(text, onEndCallback, dialect = selectedMaleProfile, allowOpeningOnce = false) {
        if (!('speechSynthesis' in window)) {
            if (onEndCallback) setTimeout(onEndCallback, 2500);
            return;
        }
        try {
            window.speechSynthesis.cancel();
            window.speechSynthesis.resume();
            const cleanText = ensureLeadingSalawat(text, allowOpeningOnce);
            const utter = new SpeechSynthesisUtterance(cleanText);
            window.__activeGuideUtterance = utter;
            utter.lang = dialect === 'egyptian' ? 'ar-EG' : 'ar-SA';
            const chosenVoice = selectBestBrowserArabicMaleVoice(dialect);
            if (chosenVoice) {
                utter.voice = chosenVoice;
                const isExplicitMale = /shakir|hamed|hamdan|maged|tarik|naayf|male|شاكر|حامد|حمدان|ماجد|طارق|نايف/i.test(chosenVoice.name || '');
                utter.pitch = isExplicitMale ? 0.92 : 0.72;
            } else {
                utter.pitch = 0.74;
            }
            utter.rate = Math.max(0.82, Math.min(1.25, selectedSpeechRate || 0.92));
            utter.volume = 1.0;

            isAudioPlaying = true;
            isAudioPaused = false;
            updateAudioPlayerUI();

            let finished = false;
            const finishOnce = () => {
                if (finished) return;
                finished = true;
                isAudioPlaying = false;
                updateAudioPlayerUI();
                if (onEndCallback) onEndCallback();
            };
            utter.onend = finishOnce;
            utter.onerror = finishOnce;
            window.speechSynthesis.speak(utter);
            setTimeout(() => {
                try {
                    if (window.speechSynthesis.paused) window.speechSynthesis.resume();
                } catch (e) {}
            }, 60);
        } catch (e) {
            if (onEndCallback) setTimeout(onEndCallback, 2500);
        }
    }

    async function speakArabicMaleText(text, onEndCallback, dialect = selectedMaleProfile, allowOpeningOnce = false) {
        stopCurrentSpeech();
        const reqId = currentSpeechRequestId;
        ensureGuideDomElements();

        const finalSpokenText = ensureLeadingSalawat(text, allowOpeningOnce);

        const coachBar = document.getElementById('msg-live-coach-bar');
        if (coachBar) coachBar.style.display = 'flex';
        const pinnedDock = document.getElementById('msg-pinned-audio-dock');
        if (pinnedDock) pinnedDock.style.display = 'inline-flex';
        const speechBox = document.getElementById('msg-coach-speech-text');
        if (speechBox && finalSpokenText) speechBox.textContent = finalSpokenText;

        try {
            const ctx = getAudioCtx();
            if (ctx.state === 'suspended') {
                await ctx.resume();
            }
            const { buffer: audioBuffer, isGenuineNeural } = await fetchAndDecodeMaleBuffer(finalSpokenText, dialect);
            if (reqId !== currentSpeechRequestId) return;
            activeAudioBuffer = audioBuffer;
            activeAudioIsGenuine = isGenuineNeural;
            activeAudioOnEnd = onEndCallback || null;
            startBufferAtOffset(0);
        } catch (err) {
            if (reqId !== currentSpeechRequestId) return;
            speakWithBrowserMaleFallback(finalSpokenText, onEndCallback, dialect, allowOpeningOnce);
        }
    }

    // ── SCREEN GUIDES REGISTRY ───────────────────────────────────────────────
    window.MagaiesSmartGuides = window.MagaiesSmartGuides || {};

    // ============================================================================
    // 📥 1. FULL MASTERCLASS: PURCHASES RECEIPT & SPLIT GATEWAY (GRN)
    // ============================================================================
    const PURCHASES_MASTER_STEPS = [
        {
            badge: 'الخطوة 1️⃣ — ما هي بوابة استلام المشتريات (GRN) وكيف تحميك؟',
            cursorLabel: 'بوابة استلام المشتريات (إضافة للمخزن +) 📥',
            targetSelector: '#sl-tab-purchases',
            speechByDialect: {
                egyptian: () => `وظيفة بوابة استلام المشتريات وتقسيمها ببساطة إن المشتريات لما بتشتري فاتورة من المورد، البضاعة مش بتدخل المخزن لوحدها، لازم تعدي على البوابة الزرقاء دي الأول علشان أمين المخزن يفحصها ويعدّها بنفسه، سواء وصلت كاملة على عربية واحدة أو وصلت متقسمة على كذا دفعة.`,
                sudanese: () => `وظيفة بوابة استلام المشتريات وتقسيمها ببساطة إنو لما نشتري فاتورة من المورد، البضاعة ما بتنزل في المخزن طوالي، لازم تمر على البوابة الزرقاء دي أول حاجة عشان أمين المخزن يفحصها ويعدّها براهو، سواء وصلت كلها في دفعة واحدة أو وصلت مقسمة على دفعات.`,
                gulf:     () => `وظيفة بوابة استلام المشتريات وتقسيمها ببساطة إنه لما نشتري فاتورة من المورد، البضاعة ما تدخل المستودع تلقائياً، لازم تمر على هذي البوابة الزرقاء أول شيء عشان أمين المستودع يفحصها ويعدّها فعلياً، سواء وصلت كاملة بدفعة واحدة أو مقسمة على عدة دفعات.`
            },
            speech: () => `وظيفة بوابة استلام المشتريات وتقسيمها أن فاتورة المشتريات لا تدخل المخزن تلقائياً، بل تمر عبر هذه البوابة الزرقاء ليفحصها أمين المخزن ويستلمها دفعة واحدة أو يقسمها على عدة دفعات.`,
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
                egyptian: () => `في الشريط اللي فوق ده عندك أداتين مهمين جداً: الأولى زرار ربط كاميرا الجوال اللي فتح قدامك دلوقتي، وده بيخليك تمسك موبايلك وتمسح باركود عشر فواتير مشتريات ورا بعض فتستلمهم في ثواني من غير ما تلمس الكيبورد. والتانية هي قائمة حارس الكيو آر، لو خليتها على وضع قفل القسم، هتحميك لو العامل مسح بالغلط أي ورقة خارجية لا تخص استلام المشتريات، فبيرفضها وينبهك فوراً.`,
                sudanese: () => `في الشريط الفوق ده عندك حاجتين مهمات شديد: الأولى زر ربط كاميرا الجوال الفتح قدامك هسي، وده بخليك تمسك تلفونك وتمسح باركود عشرة أذونات مشتريات ورا بعض فتستلمهم في ثواني بدون ما تلمس الكيبورد. والتانية هي قائمة حارس الكيو آر، لو قفلتها على القسم الحالي، بتحميك لو العامل مسح بالغلط أي ورقة ما بتخص المشتريات، فبيرفضها وينبهك طوالي.`,
                gulf:     () => `في الشريط العلوي عندك أداتين في غاية الأهمية: الأولى نافذة ربط كاميرا الجوال اللي انفتحت قدامك الحين، وتخليك تمسح بجوالك عشر فواتير مشتريات ورا بعض وتستلمها بثواني بدون لمس الكيبورد. والثانية قائمة حارس الكيو آر، لو حطيتها على قفل القسم، تحميك لو العامل مسح بالغلط أي ورقة لا تخص استلام المشتريات، فيرفضها وينبهك فوراً.`
            },
            speech: () => `في الشريط العلوي تفتح نافذة ربط كاميرا الجوال لمسح عشرات الأذونات متتالية، وبجانبها قائمة حارس الكيو آر لمنع مسح أي ورقة بالخطأ.`,
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
            targetSelector: '#sl-kpi-card-pending',
            speechByDialect: {
                egyptian: () => `تحت الشريط العلوي هتلاقي أربع كروت ملونة بتلخص لك حال المخزن كله: الكارت الأصفر الأول بيعرض الفواتير الجديدة اللي لسه واصلة ومستنية الاستلام. والكارت الأزرق التاني بيعرض الفواتير اللي استلمنا جزء منها ولسه باقي ليها دفعات تانية عند المورد. والكارت الأخضر التالت للفواتير اللي خلصت بالكامل. والبنفسجي الرابع فيه أرشيف كل أذونات الاستلام الرسمية اللي طبعناها.`,
                sudanese: () => `تحت الشريط الفوق بتلقى أربعة كروت ملونة بتلخص ليك حركة المخزن كلها: الكرت الأصفر الأول بيعرض الفواتير الجديدة الراجية الاستلام. والكرت الأزرق التاني بيعرض الفواتير القسّمناها واستلمنا جزء منها ولسه باقي ليها دفعات عند المورد. والكرت الأخضر التالت للفواتير الاكتملت خلاص. والبنفسجي الرابع فيهو سجل كل أذونات الاستلام الرسمية.`,
                gulf:     () => `تحت الشريط العلوي بتلقى أربع بطاقات ملونة تلخص حالة المستودع بالكامل: البطاقة الصفراء الأولى تعرض الفواتير الجديدة اللي تنتظر الاستلام. والبطاقة الزرقاء الثانية تعرض الفواتير المقسمة اللي استلمنا جزء منها وباقي لها دفعات ثانية. والخضراء الثالثة للفواتير المكتملة، والبنفسجية الرابعة لسجل أذونات الاستلام الرسمية.`
            },
            speech: () => `هذه البطاقات الأربع تصنف الفواتير بوضوح: الصفراء للفواتير الجديدة قيد الاستلام، الزرقاء للفواتير المقسمة على دفعات، الخضراء للمكتملة، والبنفسجية لسجل الأذونات الرسمية.`,
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
                egyptian: () => `هنا في جدول السجل الذكي، تقدر تبحث في أكتر من ألف فاتورة برقم الفاتورة أو اسم المورد أو حتى اسم الصنف. وقدام كل فاتورة فيه زرارين مهمين: لو العربية جابت البضاعة كاملة زي الفاتورة بالظبط، تضغط على زرار اعتماد كامل الأخضر فتدخل المخزن في ثانية واحدة. أما لو البضاعة جاية ناقصة أو عايز تقسمها على دفعات أو توزعها على كذا مخزن، بنضغط على الفاتورة أو زرار تقسيم وفحص فبتنزل معانا تحت.`,
                sudanese: () => `هنا في جدول السجل الذكي، تقدر تفتش في أكتر من ألف فاتورة برقم الفاتورة أو اسم المورد أو اسم الصنف ذاتو. وقدام كل فاتورة في زرين: لو العربية جابت البضاعة كاملة زي الفاتورة بالضبط، تضغط زر اعتماد كامل الأخضر فبتدخل المخزن في ثانية. أما لو البضاعة ناقصة أو داير تقسمها على دفعات أو توزعها على مخازن مختلفة، تضغط على الفاتورة أو زر تقسيم وفحص عشان تفتح معاك تحت.`,
                gulf:     () => `هنا في جدول السجل الذكي، تقدر تبحث في أكثر من ألف فاتورة برقم الفاتورة أو اسم المورد أو اسم الصنف. وقدام كل فاتورة زرين: إذا وصلت البضاعة كاملة نفس الفاتورة بالضبط، تضغط زر اعتماد كامل الأخضر وتدخل المستودع بثانية. أما إذا وصلت على دفعات أو تبغى تقسمها على أكثر من مستودع، تضغط على الفاتورة أو زر تقسيم وفحص وتفتح معك بالأسفل.`
            },
            speech: () => `في هذا السجل يمكنك البحث الفوري في آلاف الفواتير. إذا وصلت البضاعة كاملة اضغط اعتماد كامل، وإذا أردت تقسيمها على دفعات أو مخازن اضغط على الفاتورة لتفتح في بطاقة الفحص بالأسفل.`,
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
                egyptian: () => `أول ما الفاتورة بتفتح معانا في بطاقة الفحص دي، بنختار المستودع الافتراضي من القائمة المنسدلة اللي فتحت قدامك دلوقتي، أو نضغط علامة الزائد اللي جنبها علشان نضيف مخزن جديد في ثانية! وجنبها بنسجل اسم المستلم الفعلي، واسم سائق الشاحنة، ورقم لوحة العربية، وختم وقت دخول وخروج الشاحنة من البوابة علشان كل ده يتطبع في إذن الاستلام الرسمي.`,
                sudanese: () => `أول ما الفاتورة تفتح معاك في بطاقة الفحص دي، بنختار المخزن الأساسي من القائمة المنسدلة الفتحت قدامك هسي، أو نضغط علامة الزائد الجنبها عشان نضيف مخزن جديد طوالي! وجنبها بنكتب اسم المستلم الفعلي، واسم سواق الشاحنة، ورقم اللوحة، وختم زمن دخول وخروج الشاحنة من البوابة عشان تطبع كلها في إذن الاستلام الرسمي.`,
                gulf:     () => `أول ما تفتح الفاتورة في بطاقة الفحص هذي، نختار المستودع الافتراضي من القائمة المنسدلة اللي انفتحت قدامك الحين، أو نضغط زر الزائد بجانبها لإضافة مستودع جديد فوراً! وبجانبها نسجل اسم المستلم الفعلي، واسم سائق الشاحنة، ورقم اللوحة، وختم وقت دخول وخروج الشاحنة من البوابة عشان تنطبع رسمياً في مذكرة الاستلام.`
            },
            speech: () => `في بطاقة الفحص نختار المستودع الافتراضي من القائمة المنسدلة أو نضيف مستودعاً جديداً بزر الزائد، ونسجل اسم المستلم والسائق ورقم اللوحة وختم وقت البوابة.`,
            action: () => {
                const whSel = document.getElementById('sl-master-warehouse');
                if (whSel) showLiveSelectOptionsMirror(whSel, null, '🏢 قائمة المستودعات المتاحة');
            }
        },
        {
            badge: 'الخطوة 6️⃣ — صندوق الكاميرا والتوثيق المصوّر للبضاعة عند البوابة',
            cursorLabel: 'الاعتماد التلقائي بالباركود ⚡ + إرفاق صورة البضاعة 📎',
            targetSelector: '#sl-camera-placeholder',
            speechByDialect: {
                egyptian: () => `وعلى الشمال هنا صندوق الكاميرا والتوثيق: فيه علامة صح اسمها اعتماد فوري متتابع عند المسح، ودي معناها إنك أول ما تمرر الباركود قدام الكاميرا بيعتمد الفاتورة أوتوماتيك من غير ما تضغط ولا زرار. وكمان تحتها زرار إرفاق صورة البضاعة، لو عايز تصور البضاعة وهي على العربية أو تصور ورقة المورد الموقعة وترفقها كدليل مع الإذن.`,
                sudanese: () => `وعلى الشمال هنا صندوق الكاميرا والتوثيق: فيهو خيار اسمو اعتماد فوري متتابع عند المسح، وده معناه أول ما تمرر الباركود قدام الكاميرا بيعتمد الفاتورة طوالي بدون ما تضغط أي زر. وتحتو زر إرفاق صورة البضاعة، لو داير تصور البضاعة في العربية أو تصور ورقة المورد وترفقها كإثبات مع الإذن.`,
                gulf:     () => `وعلى اليسار هنا صندوق الكاميرا والتوثيق: فيه خيار اعتماد فوري متتابع عند المسح، يعني بمجرد تمرير الباركود أمام الكاميرا يعتمد الفاتورة تلقائياً بدون ضغط أي زر. وتحته زر إرفاق صورة البضاعة، لتصوير الحمولة أو مستند المورد وإرفاقه كإثبات رسمي.`
            },
            speech: () => `في هذا الصندوق يمكنك تفعيل الاعتماد الفوري بمجرد مسح الباركود، كما يمكنك إرفاق صورة حية للبضاعة أو المستند الورقي كإثبات رسمي.`,
            action: () => {}
        },
        {
            badge: 'الخطوة 7️⃣ — قارئ الميزان، التقسيم السريع، موقع الرف والتشغيلة، وتوزيع المخازن',
            cursorLabel: '⚖️ نافذة الميزان الإلكتروني + توزيع ذكي للمخازن 🏭 + الرف والتشغيلة 📍',
            targetSelector: '#sl-items-grid',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `ننزل بقى لجدول الفحص والتقسيم الذكي، وده قلب الشاشة! فوق الجدول فيه زرار قارئ الميزان الإلكتروني اللي فتح قدامك دلوقتي علشان تكتب الوزن القائم والوزن الفارغ فيحسب الوزن الصافي للعربية أوتوماتيك، وجنبه زرار التوزيع الذكي للمخازن وأزرار تعبئة الكمية مية في المية أو تقسيمها للنص خمسين في المية بضغطة واحدة. وجوه الجدول تقدر تحدد لكل صنف مخزن مستقل من قائمته المنسدلة، وتكتب رقم الرف ورقم التشغيلة!`,
                sudanese: () => `ننزل هسي لجدول الفحص والتقسيم الذكي، وده قلب الشاشة! فوق الجدول عندك زر قارئ الميزان الإلكتروني الفتح قدامك هسي عشان يحسب الوزن الصافي للشاحنة أوتوماتيك، وجنبو زر التوزيع الذكي للمخازن وأزرار تملأ ليك الباقي مية في المية أو تقسمو للنص خمسين في المية بضغطة واحدة. وجوه الجدول تقدر تختار لكل صنف مخزن براهو من القائمة، وتسجل رقم الرف ورقم التشغيلة!`,
                gulf:     () => `ننزل الحين لجدول الفحص والتقسيم الذكي، وهذا قلب الشاشة! فوق الجدول نافذة قارئ الميزان الإلكتروني اللي انفتحت قدامك الحين لحساب الوزن الصافي للشاحنة تلقائياً، وبجانبها زر التوزيع الذكي للمخازن وأزرار تعبئة الكمية مية بالمية أو تقسيمها للنصف خمسين بالمية. وداخل الجدول تقدر تختار لكل صنف مستودع مستقل من قائمته وتحدد موقع الرف ورقم التشغيلة!`
            },
            speech: () => `في جدول الفحص تفتح نافذة الميزان الإلكتروني لحساب الوزن الصافي تلقائياً، وبجانبها التوزيع الذكي للمخازن وأزرار التعبئة السريعة وتحديد مخزن ورف مستقل لكل صنف.`,
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
                egyptian: () => `تعالى نشوف أهم ميزة في الجدول: الصندوقين اللي جنب بعض قدام كل صنف. تخيل إن الفاتورة مكتوبة بالطن، وكان باقي فيها سبعة طن، والنهارده وصلت خمسة طن بس، أو العامل عدّ البضاعة بالسيخة فلقاهم خمسمية سيخة! إنت مش محتاج آلة حاسبة خالص؛ لو كتبت خمسة في صندوق الطن، صندوق السيخة هيكتب لوحده خمسمية، ولو كتبت في السيخة هيحولها لطن أوتوماتيك، والبرنامج هيحفظ إن باقي لنا اتنين طن للدفعة الجاية!`,
                sudanese: () => `تعال نشوف أهم ميزة في الجدول: الصندوقين الجنب بعض قدام كل صنف. تخيل الفاتورة مكتوبة بالطن، وباقي فيها سبعة طن، والليلة وصلت خمسة طن بس، أو العامل عدّ الحديد بالسيخة لقاهو خمسمية سيخة! ما بتحتاج آلة حاسبة نهائي؛ لو كتبت خمسة في صندوق الطن، صندوق السيخة بيكتب براهو خمسمية، ولو كتبت بالسيخة بحولها لطن أوتوماتيك، والبرنامج بحفظ إنو باقي لينا اتنين طن للدفعة الجاية!`,
                gulf:     () => `خلنا نشوف أهم ميزة بالجدول: الصندوقين المتجاورين قدام كل صنف. تخيل الفاتورة مكتوبة بالطن وباقي فيها سبعة طن، واليوم وصل خمسة طن بس، أو العامل عدّ الحديد بالحبة أو السيخة وطلع خمسمية سيخة! ما تحتاج آلة حاسبة أبداً؛ إذا كتبت خمسة بصندوق الطن يتحول صندوق السيخة تلقائياً إلى خمسمية، والعكس صحيح، ويحفظ البرنامج إنه باقي اثنين طن للدفعة القادمة!`
            },
            speech: () => `أمام كل صنف صندوقان متزامنان: يمكنك كتابة الكمية بالوحدة الأساسية كالطن أو بالوحدة الفرعية كالسيخة والحبة، وسيقوم البرنامج بالتحويل الآلي وحساب المتبقي للدفعة القادمة فوراً.`,
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
                egyptian: () => `بص تحت خالص على الشريط العائم ده، ده الميزان اللي بيطمنك قبل ما تعتمد: بيقولك الفاتورة كلها كانت عشرة، استلمنا قبل كده تلاتة، وبنستلم في الدفعة دي خمسة، وهيتبقى اتنين في ذمة المورد للدفعة الجاية، مع مؤشر حي لنسبة الإنجاز المئوية. يعني كل رقم واضح قدام عينك قبل ما تضغط اعتماد.`,
                sudanese: () => `عاين تحت خالص في الشريط العائم ده، ده الميزان البوريك كل حاجة بوضوح قبل الاعتماد: بيقول ليك إجمالي الفاتورة عشرة، استلمنا قبل كدا تلاتة، وهسي بنستلم في الدفعة دي خمسة، وحيفضل باقي اتنين للدفعة الجاية، مع شريط نسبة الإنجاز المئوية.`,
                gulf:     () => `شوف بالأسفل الشريط العائم هذا، يعطيك ملخص كامل وواضح قبل الاعتماد: إجمالي الفاتورة عشرة، المنفذ سابقاً ثلاثة، والمنفذ في هذي الدفعة خمسة، والمتبقي بعد الدفعة اثنين، مع مؤشر نسبة الإنجاز المئوية.`
            },
            speech: () => `يعرض الشريط العائم بالأسفل معادلة الفاتورة كاملة ونسبة الإنجاز المئوية لحظياً: إجمالي الفاتورة، والمنفذ سابقاً، والدفعة الحالية، والمتبقي.`,
            action: () => {}
        },
        {
            badge: 'الخطوة 🔟 — التوقيع الإلكتروني، إشعار خصم الفروقات، الاعتماد، والطباعة بالـ QR',
            cursorLabel: 'اعتماد 🚀 | التوقيع ✍️ | إشعار فرق ⚠️ | طباعة QR 🖨️ | واتساب 💬 | عكس ↩️',
            targetSelector: '#sl-btn-execute',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `وفي الخطوة الأخيرة، بنضغط على زرار اعتماد الآن، فالبضاعة بتدخل المخزن فوراً وبيطلع لها رقم إذن استلام رسمي. وجنبه نافذة التوقيع الإلكتروني باللمس لأمين المخزن والسائق، ونافذة إشعار خصم الفروقات والتوالف اللي بتفتح قدامك دلوقتي علشان تخصم أي عجز من حساب المورد أوتوماتيك، مع طباعة الإذن الرسمي بالباركود، والمشاركة على الواتساب، وزرار عكس الحركة المخزنية للتراجع الكامل في أي وقت!`,
                sudanese: () => `وفي الخطوة الأخيرة، تضغط على زر اعتماد الآن، فالبضاعة بتنزل في المخزن طوالي وبيطلع ليها رقم إذن استلام رسمي. وجنبو نافذة التوقيع الإلكتروني باللمس، ونافذة إشعار خصم الفروقات والتوالف الفتحت قدامك هسي لو في أي نقص أو تلف عشان يتخصم من حساب المورد طوالي، وطباعة الإذن الرسمي بالباركود، والإرسال بالواتساب، وزر عكس الحركة المخزنية للتراجع الكامل في أي وقت!`,
                gulf:     () => `وفي الخطوة الأخيرة، نضغط على زر اعتماد الآن، فتدخل الكمية للمستودع فوراً ويصدر لها رقم مذكرة استلام رسمية. وبجانبه نافذة التوقيع الإلكتروني باللمس، ونافذة إشعار خصم الفروقات والتوالف اللي انفتحت قدامك الحين لترحيل أي عجز على حساب المورد تلقائياً، وطباعة الإذن الرسمي مع رمز الكيو آر، والمشاركة عبر الواتساب، وعكس الحركة المخزنية للتراجع في أي وقت!`
            },
            speech: () => `وأخيراً نضغط على زر اعتماد الآن لإدخال البضاعة للمخزن فوراً، وبجانبه نافذة التوقيع الإلكتروني باللمس، ونافذة إشعار خصم الفروقات للمورد، وطباعة الإذن الرسمي بالباركود، والمشاركة عبر الواتساب، وعكس الحركة.`,
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
            badge: 'مبيعات 1️⃣ — ما هي بوابة تسليم المبيعات (GDN) وكيف تضبط خروج البضاعة؟',
            cursorLabel: 'بوابة تسليم المبيعات (خصم مخزني - Out) 📤',
            targetSelector: '#sl-tab-sales',
            speechByDialect: {
                egyptian: () => `وظيفة بوابة تسليم المبيعات وتقسيمها، واللي بنسميها البوابة الخضراء، إن العميل لما بيشتري فاتورة من المبيعات، البضاعة مش بتتخصم ولا تخرج من المخزن إلا لما ييجي يستلمها فعلياً هنا، وساعتها نقدر نسلمه الفاتورة كلها مرة واحدة، أو نقسمها له على دفعات وعربيات مختلفة، ونطلع له تصريح خروج رسمي للبوابة.`,
                sudanese: () => `وظيفة بوابة تسليم المبيعات وتقسيمها، البوابة الخضراء، إنو العميل لما يقطع فاتورة مبيعات، البضاعة ما بتمرق من المخزن إلا لما يجي يستلمها فعلياً هنا، ووقتها بنقدر نسلمو الفاتورة كلها مرة واحدة، أو نقسمها ليهو على دفعات، ونطلع ليهو إذن خروج رسمي للبوابة.`,
                gulf:     () => `وظيفة بوابة تسليم المبيعات وتقسيمها، البوابة الخضراء، إن العميل لما يصدر فاتورة مبيعات، البضاعة ما تنخصم ولا تطلع من المستودع إلا عند تسليمها الفعلي هنا، وتقدر تسلمه كامل الفاتورة دفعة واحدة أو تقسمها له على دفعات وتصدر له تصريح خروج رسمي للبوابة.`
            },
            speech: () => `وظيفة بوابة تسليم المبيعات وتقسيمها أنه يتم فيها صرف فواتير العملاء فعلياً من المخزن سواء دفعة واحدة أو مقسمة على دفعات مع إصدار تصريح خروج البوابة.`,
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
                egyptian: () => `في الشريط العلوي عندك ربط كاميرا الجوال اللي فتح قدامك دلوقتي علشان حارس البوابة أو أمين المخزن يمسح أذونات الصرف ورا بعض بكاميرا الموبايل وتتنفذ في ثانية. وجنبها قائمة حارس الكيو آر اللي بتمنع أي لخبطة لو حد مسح ورقة خارجية بالغلط وإحنا واقفين في بوابة تسليم المبيعات.`,
                sudanese: () => `في الشريط الفوق عندك ربط كاميرا الجوال الفتح قدامك هسي عشان أمين المخزن أو البوابة يمسح أذونات الصرف ورا بعض بكاميرا التلفون وتتنفذ طوالي. وجنبها قائمة حارس الكيو آر البمنع أي لخبطة لو زول مسح ورقة خارجية بالغلط وإحنا في بوابة المبيعات.`,
                gulf:     () => `في الشريط العلوي عندك نافذة ربط كاميرا الجوال اللي انفتحت قدامك الحين عشان أمين المستودع يمسح أذونات الصرف متتالية بكاميرا الجوال. وبجانبها قائمة حارس الكيو آر اللي تمنع مسح أي ورقة خارجية بالخطأ أثناء وجودك في بوابة المبيعات.`
            },
            speech: () => `يوفر الشريط العلوي نافذة ربط كاميرا الجوال لمسح أذونات الصرف بسرعة فائقة، مع قائمة حارس الكيو آر لمنع مسح أي مستند غير مطابق لبوابة المبيعات.`,
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
            targetSelector: '#sl-kpi-card-pending',
            speechByDialect: {
                egyptian: () => `الأربع كروت الملونة هنا بتفرز لك فواتير العملاء: الكارت الأصفر الأول فيه الفواتير الجاهزة للصرف اللي العميل لسه ما استلمهاش. والكارت الأزرق التاني فيه الفواتير اللي العميل استلم جزء منها ولسه باقي له بضاعة عندنا، أو واقفة مستنية ختم البوابة النهائي. والأخضر التالت للفواتير اللي اتسلمت بالكامل، والبنفسجي الرابع لسجل أذونات الصرف الرسمية.`,
                sudanese: () => `الأربعة كروت الملونة هنا بتفرز ليك فواتير الزبائن: الكرت الأصفر الأول للفواتير الجاهزة للصرف. والكرت الأزرق التاني للفواتير المقسمة اللي العميل شال جزء منها ولسه باقي ليهو بضاعة في المخزن. والأخضر التالت للفواتير الاتسلمت خلاص، والبنفسجي الرابع لسجل أذونات الصرف الرسمية.`,
                gulf:     () => `البطاقات الأربع هنا تفرز فواتير العملاء: الصفراء للفواتير الجاهزة للصرف، والزرقاء للفواتير المقسمة اللي استلم العميل جزء منها وباقي له دفعات ثانية، والخضراء للمكتملة، والبنفسجية لسجل أذونات الفسح والصرف الرسمية.`
            },
            speech: () => `تفرز هذه البطاقات فواتير العملاء إلى: جاهزة للصرف، ومقسمة على دفعات، ومكتملة التسليم، وسجل أذونات الصرف الرسمية.`,
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
                egyptian: () => `من جدول السجل الذكي، تقدر تبحث باسم العميل أو رقم الفاتورة. لو العميل هيشيل الفاتورة كلها دلوقتي، تضغط على زرار اعتماد كامل الأخضر. أما لو العربية بتاعته صغيرة وهيشيل جزء بس النهارده والباقي بكرة، بنضغط على الفاتورة علشان تفتح معانا في بطاقة الفحص والتقسيم تحت.`,
                sudanese: () => `من جدول السجل الذكي، تقدر تبحث باسم العميل أو رقم الفاتورة. لو العميل شايل الفاتورة كلها هسي، تضغط زر اعتماد كامل الأخضر. أما لو عربيتو صغيرة وحيشيل جزء الليلة والباقي بكرة، تضغط على الفاتورة عشان تفتح معاك في بطاقة الفحص والتقسيم تحت.`,
                gulf:     () => `من جدول السجل الذكي، تقدر تبحث باسم العميل أو رقم الفاتورة. إذا العميل بيستلم الفاتورة كاملة الحين، تضغط زر اعتماد كامل الأخضر. أما إذا سيارته بتشيل جزء اليوم والباقي بعدين، تضغط على الفاتورة عشان تفتح معك في بطاقة الفحص والتقسيم بالأسفل.`
            },
            speech: () => `من السجل الذكي يمكنك صرف الفاتورة بالكامل بضغطة واحدة، أو الضغط عليها لفتحها في بطاقة الفحص وتقسيمها على دفعات.`,
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
                egyptian: () => `هنا في بطاقة بيانات التسليم، بنختار المستودع اللي هنصرف منه من القائمة المنسدلة اللي فتحت قدامك، وتقدر تفتح نافذة الفاتورة المالية الأصلية أو نافذة مسار التوصيل وجدولة الأسطول علشان تسجل اسم السائق ورقم اللوحة ورقم تصريح خروج البوابة وموعد الوصول المتوقع للعميل.`,
                sudanese: () => `هنا في بطاقة بيانات التسليم، بنختار المخزن الحنصرف منو من القائمة المنسدلة الفتحت قدامك هسي، وتقدر تفتح نافذة الفاتورة المالية الأصلية أو نافذة مسار التوصيل وجدولة الأسطول عشان تسجل اسم السواق ورقم اللوحة ورقم تصريح البوابة وزمن التوصيل.`,
                gulf:     () => `هنا في بطاقة بيانات التسليم، نختار المستودع الصارف من القائمة المنسدلة اللي انفتحت قدامك الحين، وتقدر تفتح نافذة الفاتورة المالية الأصلية أو نافذة مسار التوصيل وجدولة الأسطول لتوثيق السائق ورقم اللوحة وتصريح البوابة.`
            },
            speech: () => `في هذه البطاقة نختار المستودع الصارف من القائمة المنسدلة، ونستعرض نافذة الفاتورة المالية الأصلية ونافذة مسار التوصيل وتصريح البوابة لضمان الرقابة الكاملة عند خروج البضاعة.`,
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
                egyptian: () => `في جدول الفحص تحت، البرنامج بيعرض لك قدام كل صنف الرصيد الفعلي الموجود جوه كل مخزن علشان ما تصرفش كمية مش موجودة! وفوق الجدول تقدر تفتح نافذة فحص التحميل بالباركود، ونافذة حاسبة الحمولة والطبليات اللي فتحت قدامك علشان تحسب إجمالي وزن الشحنة وعدد الطبليات المطلوبة ونوع العربية المناسبة قبل التحميل.`,
                sudanese: () => `في جدول الفحص تحت، البرنامج بيعرض ليك قدام كل صنف الرصيد الفعلي الموجود جوه كل مخزن عشان تكون عارف البضاعة المتوفرة! وفوق الجدول تقدر تفتح نافذة فحص التحميل، ونافذة حاسبة الحمولة والطبليات الفتحت قدامك هسي عشان تحسب وزن الشحنة وعدد الطبليات والعربية المناسبة.`,
                gulf:     () => `في جدول الفحص بالأسفل، يعرض لك البرنامج قدام كل صنف الرصيد الفعلي المتوفر داخل كل مستودع! وفوق الجدول تقدر تفتح نافذة فحص التحميل، ونافذة حاسبة الحمولة والطبليات اللي انفتحت قدامك الحين لحساب وزن الشحنة وعدد الطبليات وحجم المركبة المناسبة.`
            },
            speech: () => `يعرض الجدول الرصيد الفعلي المتاح في كل مخزن أمام كل صنف، مع نافذة فحص ومطابقة التحميل ونافذة حاسبة الحمولة والطبليات لاختيار الشاحنة المناسبة.`,
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
                egyptian: () => `وهنا ميزة التقسيم بالوحدتين في المبيعات: الفاتورة دي باقي فيها للعميل سبعة طن، لو العربية بتاعته هتشيل النهارده خمسة طن بس، بنكتب خمسة في صندوق الطن، أو نكتب خمسمية في صندوق السيخة، فالبرنامج بيحولها لوحده وبيحفظ أوتوماتيك إن العميل لسه فاضل له اتنين طن ييجي يستلمهم في الدفعة الجاية!`,
                sudanese: () => `وهنا ميزة التقسيم بالوحدتين في المبيعات: الفاتورة دي باقي فيها للعميل سبعة طن، لو عربيتو بتشيل الليلة خمسة طن بس، نكتب خمسة في صندوق الطن، أو نكتب خمسمية في صندوق السيخة، والبرنامج براهو بيحولها وبيحفظ أوتوماتيك إنو العميل لسه باقي ليهو اتنين طن يستلمهم في الدفعة الجاية!`,
                gulf:     () => `وهنا ميزة التقسيم بالوحدتين في المبيعات: الفاتورة باقي فيها للعميل سبعة طن، إذا سيارته بتشيل اليوم خمسة طن بس، نكتب خمسة بصندوق الطن أو خمسمية بصندوق السيخة، والبرنامج يحولها تلقائياً ويحفظ إن العميل باقي له اثنين طن للدفعة الجاية!`
            },
            speech: () => `نكتب الكمية المسلمة للعميل في هذه الدفعة سواء بالوحدة الأساسية أو الفرعية، فيقوم البرنامج بتحويلها وحفظ الكمية المتبقية للعميل للدفعة القادمة تلقائياً.`,
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
                egyptian: () => `وقبل ما تضغط اعتماد، الشريط العائم اللي تحت ده بيأكد لك الحسبة كلها: الفاتورة عشرة، العميل استلم قبل كده تلاتة، وبيستلم دلوقتي خمسة، وهيتبقى له اتنين في ذمة المخزن.`,
                sudanese: () => `وقبل ما تضغط اعتماد، الشريط العائم التحت ده بيأكد ليك الحساب كلو: الفاتورة عشرة، العميل استلم قبل كدا تلاتة، وهسي بيستلم خمسة، وحيفضل باقي ليهو اتنين.`,
                gulf:     () => `وقبل الاعتماد، الشريط العائم بالأسفل يوضح لك الحسبة كاملة: إجمالي الفاتورة عشرة، المستلم سابقاً ثلاثة، والمستلم بهذه الدفعة خمسة، والمتبقي للعميل اثنين.`
            },
            speech: () => `يوضح الشريط العائم بالأسفل ملخص عملية الصرف بدقة: إجمالي الفاتورة، والمسلم سابقاً، والمسلم في هذه الدفعة، والمتبقي للعميل.`,
            action: () => {}
        },
        {
            badge: 'مبيعات 9️⃣ — التوقيع الإلكتروني، اعتماد الصرف، طباعة تصريح البوابة (QR)، أو عكس الحركة',
            cursorLabel: 'اعتماد الصرف 🚀 | التوقيع ✍️ | طباعة تصريح البوابة 🖨️ | تراجع وعكس ↩️',
            targetSelector: '#sl-btn-execute',
            diagramType: 'screen-transition',
            speechByDialect: {
                egyptian: () => `وفي النهاية بنفتح نافذة التوقيع الإلكتروني باللمس علشان السائق أو العميل يوقع بالاستلام، ونضغط على زرار تأكيد واعتماد التسليم والصرف، فالكمية بتتخصم من المخزن فوراً، وتقدر تطبع تصريح الخروج الرسمي وعليه باركود الكيو آر علشان العميل يسلمه على البوابة، أو تبعته له واتساب، ولو حصل أي تراجع تقدر تضغط زرار إلغاء وعكس الحركة المخزنية فيرجع الرصيد للمخزن فوراً!`,
                sudanese: () => `وفي النهاية بنفتح نافذة التوقيع الإلكتروني باللمس عشان السواق أو العميل يوقع بالاستلام، وتضغط على زر تأكيد واعتماد التسليم والصرف، فالكمية بتتخصم من المخزن طوالي، وتقدر تطبع تصريح الخروج الرسمي بالباركود عشان العميل يسلمو في البوابة، أو ترسلو ليهو واتساب، ولو داير تتراجع في أي وقت تضغط زر إلغاء وعكس الحركة المخزنية فيرجع الرصيد للمخزن طوالي!`,
                gulf:     () => `وفي النهاية نفتح نافذة التوقيع الإلكتروني باللمس لتوقيع السائق أو العميل، ونضغط على زر تأكيد واعتماد التسليم والصرف، فتنخصم الكمية من المستودع فوراً، وتقدر تطبع إذن الفسح الرسمي بالباركود للبوابة أو ترسله واتساب، وبجانبه زر إلغاء وعكس الحركة المخزنية لإرجاع الرصيد في أي وقت!`
            },
            speech: () => `نوثق توقيع المستلم والسائق في نافذة التوقيع الإلكتروني، ثم نضغط على زر تأكيد واعتماد التسليم لخصم الكمية من المخزن وإصدار تصريح البوابة الرسمي بالباركود، مع إمكانية التراجع وعكس الحركة في أي وقت.`,
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
                badge: '2. الشاشة الفرعية الثانية: فتح شاشة العملاء والسقف الائتماني',
                cursorLabel: 'فتح شاشة العملاء 👤',
                screenPath: 'البيانات الأساسية ⬅ ضغط زر (العملاء) ⬅ فتح شاشة بيانات وسقوف العملاء',
                targetSelector: '#cust-name, .md-tab-btn.tab-customers',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'قائمة البيانات الأساسية الجانبية',
                    actionLabel: 'ضغط تبويب (العملاء) يفتح شاشة العملاء:',
                    childItems: ['👤 اسم العميل ورقم الهاتف والواتساب والرقم السري PIN', '🛡️ تحديد السقف الائتماني المسموح للبيع الآجل', '💰 الرصيد الافتتاحي (مدين / دائن) والعملة', '📖 فتح حساب آلي في شجرة الحسابات ودفتر الأستاذ'],
                    effectLabel: 'تم فتح شاشة العملاء الجديدة أمامك الآن!'
                },
                action: () => {
                    const btn = document.querySelector('.md-tab-btn.tab-customers');
                    if (btn) { try { btn.click(); } catch (e) {} }
                },
                speech: () => 'الآن نضغط على زر العملاء لتفتح أمامك شاشة العملاء الجديدة! هنا تسجل اسم العميل ورقم واتسابه وسقفه الائتماني ورصيده الافتتاحي، ويقوم النظام بفتح حساب مالي له تلقائياً في شجرة الحسابات وفواتير المبيعات.'
            },
            {
                badge: '3. الشاشة الفرعية الثالثة: فتح شاشة الموردين',
                cursorLabel: 'فتح شاشة الموردين 🏢',
                screenPath: 'البيانات الأساسية ⬅ ضغط زر (الموردين) ⬅ فتح شاشة سجل وأرصدة الموردين',
                targetSelector: '#sup-name, .md-tab-btn.tab-suppliers',
                diagramType: 'screen-transition',
                diagramMeta: {
                    parentScreen: 'قائمة البيانات الأساسية الجانبية',
                    actionLabel: 'ضغط تبويب (الموردين) يفتح شاشة الموردين:',
                    childItems: ['🏢 اسم المورد أو الشركة الموردة والهاتف', '💵 الرصيد الافتتاحي والعملة المعتمدة', '📲 رقم الواتساب لإرسال الفواتير وكشوف الحساب', '📥 يظهر فوراً في قائمة الموردين بشاشة المشتريات'],
                    effectLabel: 'تم فتح شاشة الموردين الجديدة أمامك الآن!'
                },
                action: () => {
                    const btn = document.querySelector('.md-tab-btn.tab-suppliers');
                    if (btn) { try { btn.click(); } catch (e) {} }
                },
                speech: () => 'وعند الضغط على زر الموردين تفتح أمامك شاشة الموردين؛ لتسجيل الشركات والموردين وأرصدتهم الافتتاحية وأرقام الواتساب، ليظهروا مباشرة في قائمة الموردين داخل فاتورة المشتريات.'
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

    window.explainMasterDataDomElement = function (el) {
        if (!el) return null;
        if (el.closest('.tab-warehouses')) return { title: '🏭 تبويب وشاشة المخازن', desc: 'يفتح شاشة تأسيس وإدارة المستودعات وتحديد أمين كل مخزن ليظهر في فواتير الشراء والبيع والجرد.' };
        if (el.closest('.tab-customers')) return { title: '👤 تبويب وشاشة العملاء', desc: 'يفتح شاشة تسجيل العملاء وأرقام الواتساب والسقف الائتماني والرصيد الافتتاحي.' };
        if (el.closest('.tab-suppliers')) return { title: '🏢 تبويب وشاشة الموردين', desc: 'يفتح شاشة تسجيل الموردين وأرصدتهم الافتتاحية وربطهم بشجرة الحسابات وفواتير المشتريات.' };
        if (el.closest('.tab-banks')) return { title: '🏦 تبويب وشاشة البنوك', desc: 'يفتح شاشة تأسيس الحسابات البنكية والتطبيقات البنكية وأرصدتها.' };
        if (el.closest('.tab-safes')) return { title: '💵 تبويب وشاشة الخزائن', desc: 'يفتح شاشة تأسيس الخزائن النقدية الرئيسية والفرعية.' };
        if (el.closest('.tab-items')) return { title: '📦 تبويب وشاشة الأصناف والوحدات', desc: 'يفتح شاشة تعريف الأصناف والباركود والوحدة الكبرى والفرعية ومعامل التحويل وأسعار البيع والشراء.' };
        if (el.closest('.tab-employees')) return { title: '👨‍💼 تبويب وشاشة الموظفين', desc: 'يفتح شاشة تسجيل الموظفين والرواتب والبدلات وحسابات السلف والعهد.' };
        if (el.closest('.tab-costcenters')) return { title: '🎯 تبويب وشاشة مراكز التكلفة', desc: 'يفتح شاشة تأسيس المشاريع والفروع والشاحنات لربط المصروفات والإيرادات بكل مركز تكلفة.' };

        const fieldRules = [
            { sel: '#warehouse-name', title: '🏢 خانة اسم المخزن', desc: 'اكتب هنا اسم المستودع أو المعرض ليظهر في قوائم فواتير المشتريات والمبيعات والجرد.' },
            { sel: '#warehouse-type', title: '🏷️ نوع المخزن', desc: 'حدد ما إذا كان مستودعاً رئيسياً أو معرض بيع أو مخزناً فرعياً أو مخزن بضاعة تالفة.' },
            { sel: '#warehouse-keeper', title: '👨‍💼 أمين المخزن المسؤول', desc: 'حدد الموظف المسؤول عن استلام وتسليم البضاعة في هذا المستودع.' },
            { sel: '#cust-name', title: '👤 خانة اسم العميل', desc: 'اكتب اسم العميل أو الشركة؛ يقوم النظام تلقائياً بفتح حساب مالي له في شجرة الحسابات وكشف الأستاذ.' },
            { sel: '#cust-statement-pin, #sup-statement-pin', title: '🔐 الرقم السري لكشف الحساب الآلي (PIN)', desc: 'كود سري خاص بالعميل أو المورد لطلب كشف حسابه آلياً عبر بوت الواتساب بأمان تام.' },
            { sel: '#cust-whatsapp, #sup-whatsapp', title: '💬 رقم الواتساب المعتمد', desc: 'رقم الواتساب لإرسال الفواتير والإشعارات وكشوف الحساب الآلية واستقبال الإشعارات المالية.' },
            { sel: '#cust-opening-balance, #sup-opening-balance', title: '💰 الرصيد الافتتاحي', desc: 'الرصيد السابق للعميل أو المورد عند بداية العمل على النظام ليُدرج في كشف الحساب وميزان المراجعة.' },
            { sel: '#cust-limit, #sup-limit', title: '🛑 السقف الائتماني المسموح', desc: 'الحد الأقصى للمديونية الآجلة المسموح بها؛ ينبهك النظام تلقائياً عند تجاوزه في الفواتير.' },
            { sel: '#cust-currency, #sup-currency, #bank-currency, #safe-currency', title: '💱 عملة الحساب', desc: 'تحديد العملة المعتمدة للحساب (جنيه سوداني أو دولار أو غيرها) لتقييم الحركات والأرصدة بدقة.' },
            { sel: '#cust-price-tier', title: '🏷️ شريحة التسعير الافتراضية للعميل', desc: 'تحدد السعر الذي يظهر تلقائياً لهذا العميل في فاتورة المبيعات (نقدي أو آجل أو أقل سعر).' },
            { sel: '#sup-name', title: '🏭 خانة اسم المورد', desc: 'اكتب اسم المورد أو الشركة الموردة ليظهر فوراً في قائمة الموردين بشاشة المشتريات.' },
            { sel: '#bank-name', title: '🏦 خانة اسم البنك أو التطبيق البنكي', desc: 'اكتب اسم البنك (مثل بنك الخرطوم - بنكك أو فوري) ليظهر في سندات الصرف والقبض والتحويلات.' },
            { sel: '#bank-gl-account, #bank-code', title: '🔢 رقم الحساب البنكي', desc: 'رقم الحساب البنكي المعتمد للمطابقة الآلية مع إشعارات التحويل الممسوحة بالذكاء الاصطناعي.' },
            { sel: '#bank-opening-balance, #safe-opening-balance', title: '💵 الرصيد الافتتاحي للسيولة', desc: 'الرصيد الفعلي المتوفر في البنك أو الخزينة عند تأسيس الحساب.' },
            { sel: '#safe-name', title: '💵 خانة اسم الخزينة النقدية', desc: 'اكتب اسم الخزينة (مثل الخزينة الرئيسية أو خزينة المعرض) لربطها بالمقبوضات والمدفوعات النقدية.' },
            { sel: '#item-name-input', title: '📦 خانة اسم الصنف', desc: 'اكتب الاسم التجاري للصنف ليظهر في البحث السريع بفواتير المشتريات والمبيعات وتقارير المخازن.' },
            { sel: '#item-code, #item-barcode', title: '🏷️ كود وباركود الصنف', desc: 'رقم الكود التلقائي أو الباركود المطبوع على العبوة للبيع والجرد السريع بقارئ الباركود.' },
            { sel: '#item-selling-price', title: '💰 سعر البيع الافتراضي للصنف', desc: 'سعر بيع الوحدة الأساسية الذي ينزل تلقائياً في فاتورة المبيعات مع إمكانية تعديله.' },
            { sel: '#item-opening-qty, #item-opening-value', title: '📊 الكمية والتكلفة الافتتاحية للصنف', desc: 'رصيد أول المدة للصنف وتكلفة الوحدة لاحتساب متوسط التكلفة المرجح بدقة.' },
            { sel: '#item-max-limit, #item-min-limit, #item-reorder-level', title: '🚨 حدود المخزون وحد الطلب الذكي', desc: 'ينبهك النظام تلقائياً في مؤشر النواقص عندما ينخفض رصيد الصنف عن حد الطلب أو الحد الأدنى.' },
            { sel: '#item-base-unit, #units-matrix-body', title: '📐 مصفوفة الوحدات الكبرى والفرعية ومعامل التحويل', desc: 'حدد الوحدة الأساسية وأضف الوحدات الكبرى (مثل طن أو كرتونة) ومعامل التحويل وأسعار النقدي والآجل لكل وحدة.' },
            { sel: '#emp-name, #emp-role', title: '👨‍💼 بيانات الموظف والدور الوظيفي', desc: 'تسجيل اسم الموظف وتحديد صلاحياته (مدير، محاسب، كاشير، مبيعات، أو أمين مخزن).' },
            { sel: '#costcenter-name', title: '🎯 خانة اسم مركز التكلفة / المشروع', desc: 'تأسيس مشروع أو فرع أو شاحنة لربط المصروفات والإيرادات بها واستخراج تقرير ربحية كل مركز تكلفة.' }
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
                return { title: `خانة ${lbl}`, desc: `تُستخدم لإدخال وضبط (${lbl}) ضمن بطاقة البيانات الأساسية وحفظها فوراً في قاعدة البيانات.` };
            }
        }
        return window.autoSynthesizeElementExplanation ? window.autoSynthesizeElementExplanation(el) : null;
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 🧠 SELF-UPDATING DYNAMIC DOM INTROSPECTION & AUTO-SYNC ENGINE (تحديث المرشد لنفسه تلقائياً لأي زر أو خانة تُضاف مستقبلاً في أي شاشة)
    // ══════════════════════════════════════════════════════════════════════════
    window.autoSynthesizeElementExplanation = function (el) {
        if (!el || el === document.body || el.closest('#msg-live-coach-bar, #msg-smart-hover-tooltip, #msg-universal-fixed-launcher, #msg-universal-modal')) return null;
        if (typeof window.isElementAllowedForCurrentUserRole === 'function' && !window.isElementAllowedForCurrentUserRole(el)) return null;
        const customTitle = el.getAttribute && el.getAttribute('data-guide-title');
        const customDesc = el.getAttribute && el.getAttribute('data-guide-desc');
        const customRule = (el.getAttribute && el.getAttribute('data-golden-rule')) || 'تأكد من مراجعة البيانات المدخلة أو الخيار المحدد قبل الحفظ لضمان التسميع المحاسبي الدقيق.';
        if (customTitle && customDesc) {
            return {
                title: customTitle,
                desc: `${customDesc} 👑 القاعدة الذهبية لهذا العنصر: ${customRule}`
            };
        }
        const btn = el.closest('button, a.btn, .btn-action, .md-tab-btn, .sl-btn, [onclick]');
        if (btn) {
            const bTitle = btn.getAttribute('data-guide-title') || '';
            const bDesc = btn.getAttribute('data-guide-desc') || '';
            if (bTitle && bDesc) {
                return {
                    title: bTitle,
                    desc: `${bDesc} 👑 القاعدة الذهبية لهذا العنصر: ${btn.getAttribute('data-golden-rule') || customRule}`
                };
            }
            const rawTxt = (btn.innerText || btn.getAttribute('title') || btn.getAttribute('aria-label') || '').replace(/\s+/g, ' ').replace(/F\d+/g, '').trim();
            if (!rawTxt || rawTxt.length < 2) return null;
            const titleHint = btn.getAttribute('title') || '';
            return {
                title: `زر (${rawTxt.slice(0, 42)})`,
                desc: (titleHint && titleHint !== rawTxt
                    ? `${titleHint} — عند الضغط على (${rawTxt}) ينفذ النظام العملية المرتبطة به ويحدّث البيانات فوراً.`
                    : `أمر تفاعلي لتنفيذ (${rawTxt}) مباشرة داخل الشاشة وتحديث الحسابات والسجلات المرتبطة به لحظياً.`) + ` 👑 القاعدة الذهبية لهذا العنصر: ${customRule}`
            };
        }
        const fldWrap = el.closest('.pro-field-card, .input-group, .h-form-group');
        const ctrl = (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') ? el : (fldWrap ? fldWrap.querySelector('input, select, textarea') : null);
        if (ctrl || fldWrap) {
            const lbl = (fldWrap?.querySelector('label, .pro-field-label, .h-label')?.innerText || ctrl?.getAttribute('placeholder') || ctrl?.getAttribute('title') || '').replace(/\s+/g, ' ').trim();
            if (!lbl) return null;
            if (ctrl && ctrl.tagName === 'SELECT') {
                const opts = Array.from(ctrl.options || []).map(o => (o.textContent || '').trim()).filter(Boolean).slice(0, 4).join('، ');
                return {
                    title: `قائمة (${lbl.slice(0, 40)})`,
                    desc: `قائمة منسدلة لاختيار (${lbl})${opts ? ` ومن خياراتها: (${opts})` : ''}، وتسمّع تلقائياً في السجل الحالي. 👑 القاعدة الذهبية لهذا العنصر: اختر التصنيف الصحيح من القائمة المنسدلة لتوجيه القيد والتقرير تلقائياً.`
                };
            }
            return {
                title: `خانة (${lbl.slice(0, 40)})`,
                desc: `تُستخدم لإدخال أو تعديل (${lbl}) مع التسميع اللحظي والحفظ المباشر في قاعدة البيانات. 👑 القاعدة الذهبية لهذا العنصر: ${customRule}`
            };
        }
        if (el.tagName === 'TH') {
            const thTxt = (el.innerText || '').replace(/\s+/g, ' ').trim();
            if (thTxt) return { title: `عمود (${thTxt})`, desc: `يعرض بيانات (${thTxt}) لكل سجل في الجدول مع إمكانية البحث والفرز الفوري. 👑 القاعدة الذهبية: راجع قيم هذا العمود للتأكد من دقة الأرصدة والأسعار المسجلة.` };
        }
        return null;
    };

    window.syncDynamicDomElementsIntoSmartGuide = function (guideKey) {
        const isSlScreen = window.location.pathname.includes('smart-logistics') || !!document.getElementById('sl-top-cockpit-bar');
        const key = guideKey || (isSlScreen ? 'smart-logistics' : (currentGuideKey || 'master-data'));
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
        });
    };

    function buildUniversalScreenGuideIfNeeded() {
        let matchedKey = null;
        if ((window.location.pathname.includes('master-data') || document.querySelector('.md-tabs-sidebar')) && window.MagaiesSmartGuides['master-data']) {
            matchedKey = 'master-data';
        } else if ((window.location.pathname.includes('purchase-receipt.php') || document.getElementById('recent-grns-container')) && window.MagaiesSmartGuides['purchase-receipt']) {
            matchedKey = 'purchase-receipt';
        } else if ((window.location.pathname.includes('purchase.php') || document.getElementById('purchase-editor-view') || document.getElementById('purchase-hub-view')) && window.MagaiesSmartGuides['purchase-direct']) {
            matchedKey = 'purchase-direct';
        } else {
            const splitViewEl = document.getElementById('split-invoice-view');
            if ((window.location.search.includes('action=split') || (splitViewEl && splitViewEl.style.display !== 'none')) && window.MagaiesSmartGuides['sales-split']) {
                matchedKey = 'sales-split';
            } else if ((window.location.pathname.includes('direct-sales.php') || document.getElementById('invoice-editor-view') || document.getElementById('sales-hub-view')) && window.MagaiesSmartGuides['sales-direct']) {
                matchedKey = 'sales-direct';
            } else if (document.getElementById('sl-tab-purchases') || document.getElementById('sl-tab-sales')) {
                matchedKey = 'smart-logistics';
            } else if (document.getElementById('from-account-search') || window.location.pathname.includes('single-cash-payment')) {
                matchedKey = 'single-cash-payment';
            } else if (window.location.pathname.includes('multi-cash-payment') && window.MagaiesSmartGuides['multi-cash-payment']) {
                matchedKey = 'multi-cash-payment';
            }
        }
        if (matchedKey) {
            try { window.syncDynamicDomElementsIntoSmartGuide(matchedKey); } catch (e) {}
            return matchedKey;
        }
        const pageTitle = (document.querySelector('main h1, main h2, main h3, .md-card-title, h1:not(.m7-modal-title), h2:not(.m7-modal-title), .page-title')?.textContent || document.title || 'الشاشة الحالية').trim();
        const visibleEls = Array.from(document.querySelectorAll('button, select, a.btn, .sl-btn, .tab-btn, [role="tab"]')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 40 && r.height > 20 && r.top >= 0 && !b.closest('#msg-universal-fixed-launcher') && !b.closest('#msg-universal-modal') && !b.closest('#master-7-themes-modal') && !b.closest('#magaiees-dashboard-header-stack') && !b.closest('#msg-live-coach-bar');
        }).slice(0, 8);

        window.MagaiesSmartGuides['universal-screen'] = {
            title: `المرشد الذكي: ${pageTitle}`,
            subtitle: 'شرح مصوّر ومدبلج بالصوت الرجالي للأزرار والقوائم والشاشات الفرعية داخل هذه الشاشة',
            autopilotSteps: visibleEls.length ? visibleEls.map((el, i) => {
                const isSelect = el.tagName === 'SELECT';
                const lbl = isSelect
                    ? (el.getAttribute('title') || el.options?.[0]?.text || `القائمة المنسدلة ${i + 1}`).replace(/\s+/g, ' ').trim().slice(0, 45)
                    : (el.innerText || el.getAttribute('title') || `العنصر ${i + 1}`).replace(/\s+/g, ' ').trim().slice(0, 45);
                if (!el.id) el.id = `msg-auto-el-${i}`;
                const selectOpts = isSelect ? Array.from(el.options || []).slice(0, 5).map(o => o.text.trim()).filter(Boolean) : [];
                return {
                    badge: `الخطوة ${i + 1} — ${lbl}`,
                    cursorLabel: lbl,
                    screenPath: `${pageTitle} ⬅ ${isSelect ? 'فتح قائمة' : 'ضغط زر'} (${lbl})`,
                    targetSelector: `#${el.id}`,
                    diagramType: isSelect ? 'dropdown-open' : 'screen-hub',
                    diagramMeta: {
                        parentScreen: pageTitle,
                        actionLabel: isSelect ? `عند الضغط على قائمة (${lbl}) تظهر الخيارات:` : `وظيفة زر (${lbl}) وأثره في الشاشة:`,
                        childItems: selectOpts.length ? selectOpts : [
                            `🎯 العنصر: ${lbl}`,
                            '👆 عند الضغط عليه ينفذ العملية أو يفتح النافذة المرتبطة به',
                            '📊 يحدث البيانات والتقارير المرتبطة في نفس اللحظة'
                        ],
                        effectLabel: isSelect ? 'قائمة الخيارات مفتوحة الآن أمامك على الشاشة!' : 'تنفيذ فوري ومباشر داخل النظام'
                    },
                    speech: () => `${ i === 0 ? SHORT_SAHIHAYN_SALAWAT + ' أهلاً بحضرتك في شاشة ' + pageTitle + '. ' : '' }${isSelect ? `هنا قائمة (${lbl})، وعند الضغط عليها تفتح أمامك قائمة الخيارات كما يظهر الآن على الشاشة لاختيار المطلوب.` : `هذا الزر مخصص لـ (${lbl})، ويمكنك الضغط عليه لتنفيذ العملية أو فتح النافذة المرتبطة به مباشرة.`}`
                };
            }) : [
                {
                    badge: 'الخطوة 1 — نظرة عامة على الشاشة',
                    cursorLabel: pageTitle,
                    screenPath: `${pageTitle} ⬅ جولة شاملة`,
                    targetSelector: 'body',
                    speech: () => `${SHORT_SAHIHAYN_SALAWAT} أهلاً بحضرتك في شاشة ${pageTitle}. يمكنك استخدام وضع المؤشر الحر للضغط على أي زر في الشاشة ليقوم المرشد الذكي بشرحه لك فوراً.`
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

    window.applyUniversalQuartetTheme = function (themeKey, isLight) {
        if (typeof window.applyMasterTheme === 'function') {
            window.applyMasterTheme(themeKey);
        } else {
            document.documentElement.setAttribute('data-theme', themeKey);
            if (document.body) document.body.setAttribute('data-theme', themeKey);
            document.documentElement.classList.toggle('light-theme', !!isLight);
            document.documentElement.classList.toggle('dark-theme', !isLight);
            try {
                localStorage.setItem('magaies_active_theme', themeKey);
                localStorage.setItem('magaiees_global_theme', themeKey);
                localStorage.setItem('ui_theme', themeKey);
                localStorage.setItem('erp_theme', isLight ? 'light' : 'dark');
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

    window.triggerQuartetQuickTheme = function () {
        if (typeof window.toggleTheme === 'function') {
            window.toggleTheme();
        } else {
            const isCurrentlyLight = document.documentElement.classList.contains('light-theme');
            window.applyUniversalQuartetTheme(isCurrentlyLight ? 'midnight_obsidian' : 'warm_cream', !isCurrentlyLight);
        }
    };

    function isMainDashboardScreen() {
        const p = (window.location.pathname || '').toLowerCase();
        if (p.includes('magaiees_unified_dashboard.php') || p.includes('dashboard-pro.php') || p.endsWith('/magaies/') || p.endsWith('/magaies') || p.endsWith('/index.php')) {
            return true;
        }
        if (document.querySelector('.header-theme-dashboard')) {
            return true;
        }
        return false;
    }

    function ensureFixedSquareLauncher() {
        let pod = document.getElementById('msg-universal-fixed-launcher');

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
                ${showDashboardSystemPod ? `
                <!-- ═══ المستطيل الأول (يظهر في لوحة التحكم فقط): الموبايل (تنزيل موبايل) • لابتوب (تثبيت لابتوب) • تثبيت عام • السمات ═══ -->
                <div class="msg-fixed-command-pod pod-system-tools">
                    <!-- 1. أعلى اليمين: الموبايل (هاتف ذكي بداخله سهم تنزيل واضح جداً) -->
                    <button type="button" class="msg-quartet-btn msg-quartet-mobile" onclick="window.triggerQuartetMobileEdition()" title="نسخة وتنزيل الموبايل السريعة">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="5.5" y="1.5" width="13" height="21" rx="2.8" fill="rgba(15,23,42,0.7)" stroke="#ffffff" stroke-width="2.2"/>
                                <line x1="10" y1="4" x2="14" y2="4" stroke="#93c5fd" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M12 6.8V14.2M12 14.2L9.2 11.4M12 14.2L14.8 11.4" stroke="#fde047" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="9" y1="16.8" x2="15" y2="16.8" stroke="#fde047" stroke-width="2.2" stroke-linecap="round"/>
                                <circle cx="12" cy="19.6" r="1.1" fill="#ffffff"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">الموبايل</span>
                    </button>

                    <!-- 2. أسفل اليمين (تحت الموبايل مباشرة): لابتوب (شاشة لابتوب بداخلها سهم تثبيت واضح جداً) -->
                    <button type="button" class="msg-quartet-btn msg-quartet-install-app" onclick="window.triggerQuartetAppInstall()" title="تثبيت تطبيق مقاييس على اللابتوب والكمبيوتر أو الجوال">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="3.5" y="3.5" width="17" height="11.5" rx="2" fill="rgba(15,23,42,0.7)" stroke="#ffffff" stroke-width="2.1"/>
                                <path d="M1.5 18H22.5L20.5 15H3.5L1.5 18Z" fill="#5eead4" stroke="#ffffff" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M12 5.8V12.2M12 12.2L9.4 9.6M12 12.2L14.6 9.6" stroke="#fde047" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">لابتوب</span>
                    </button>

                    <!-- 3. أعلى اليسار: تثبيت عام (تثبيت البرنامج كامل للعمل أوفلاين) -->
                    <button type="button" class="msg-quartet-btn msg-quartet-install-full" onclick="window.triggerQuartetFullOfflineSetup()" title="التثبيت العام للبرنامج بالكامل للعمل أوفلاين بدون إنترنت">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4 6C4 4.34 7.58 3 12 3C16.42 3 20 4.34 20 6V18C20 19.66 16.42 21 12 21C7.58 21 4 19.66 4 18V6Z" fill="rgba(250,204,21,0.22)" stroke="#ffffff" stroke-width="2.1"/>
                                <path d="M4 12C4 13.66 7.58 15 12 15C16.42 15 20 13.66 20 12" stroke="#fde047" stroke-width="2.1"/>
                                <path d="M12 7.2V12.6M9.6 10.2L12 12.6L14.4 10.2" stroke="#fde047" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">تثبيت عام</span>
                    </button>

                    <!-- 4. أسفل اليسار: السمات (السمات السبعة المعتمدة) -->
                    <button type="button" class="msg-quartet-btn msg-quartet-themes" onclick="window.triggerQuartetThemesModal()" title="السمات السبعة المعتمدة والموحدة (7 سمات رئيسية)">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M12 3C7.03 3 3 6.8 3 11.5C3 16.2 7.03 20 12 20C13.66 20 15 18.66 15 17C15 16.2 14.68 15.5 14.18 14.95C13.7 14.42 14.08 13.5 14.8 13.5H16.5C19.54 13.5 22 11.04 22 8C22 5.24 17.52 3 12 3Z" fill="rgba(244,114,182,0.28)" stroke="#ffffff" stroke-width="2.1"/>
                                <circle cx="8" cy="10" r="1.4" fill="#fde047"/>
                                <circle cx="12" cy="7.5" r="1.4" fill="#38bdf8"/>
                                <circle cx="16" cy="9.5" r="1.4" fill="#4ade80"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">السمات <span style="color:#fde047;font-size:11px;">7</span></span>
                    </button>
                </div>
                ` : ''}

                <!-- ═══ المستطيل الثاني (في كل الشاشات): الدرع • البحث • توسيع الشاشة • تبديل المظهر ═══ -->
                <div class="msg-fixed-command-pod pod-utility-quad">
                    <!-- 1. أعلى اليمين: الدرع (درع الحماية والمزامنة) -->
                    <button type="button" id="desktop-sync-shield-btn" class="msg-quartet-btn msg-quartet-shield" onclick="window.triggerQuartetShield()" title="🛡️ درع الحماية وسجل المزامنة التلقائية">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M12 2.5L4.5 5.8V11.5C4.5 16.5 7.7 20.9 12 22.2C16.3 20.9 19.5 16.5 19.5 11.5V5.8L12 2.5Z" fill="rgba(16,185,129,0.3)" stroke="#ffffff" stroke-width="2.1" stroke-linejoin="round"/>
                                <path d="M9 12.2L11.1 14.3L15.4 10" stroke="#fde047" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">الدرع</span>
                        <span id="desktop-sync-shield-badge" style="display:none; position:absolute; top:2px; left:4px; background:#ef4444; color:#fff; font-size:9px; font-weight:900; min-width:14px; height:14px; border-radius:999px; align-items:center; justify-content:center; padding:0 3px; border:1px solid #fff; z-index:5;">0</span>
                    </button>

                    <!-- 2. أسفل اليمين (تحت الدرع): البحث (متعقب المستندات ومن فعل ماذا) -->
                    <button type="button" class="msg-quartet-btn msg-quartet-search" onclick="window.triggerQuartetAuditSearch()" title="🔍 البحث ومتعقب المستندات وسجل المستخدمين (Alt+T)">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="6.8" fill="rgba(56,189,248,0.28)" stroke="#ffffff" stroke-width="2.2"/>
                                <path d="M16.2 16.2L21 21" stroke="#fde047" stroke-width="2.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">البحث</span>
                    </button>

                    <!-- 3. أعلى اليسار: توسيع الشاشة -->
                    <button type="button" class="msg-quartet-btn msg-quartet-fullscreen" onclick="window.triggerQuartetFullscreen()" title="⛶ توسيع وملء الشاشة">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4 9V4H9M20 9V4H15M4 15V20H9M20 15V20H15" stroke="#fde047" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="8" y="8" width="8" height="8" rx="1.5" stroke="#ffffff" stroke-width="1.8"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">الشاشة</span>
                    </button>

                    <!-- 4. أسفل اليسار: تبديل المظهر -->
                    <button type="button" class="msg-quartet-btn msg-quartet-qtheme" onclick="window.triggerQuartetQuickTheme()" title="🌓 تبديل المظهر السريع (فاتح / داكن)">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M20.5 14.5A8.5 8.5 0 019.5 3.5A8.5 8.5 0 1020.5 14.5Z" fill="rgba(253,224,71,0.35)" stroke="#fde047" stroke-width="2.2" stroke-linejoin="round"/>
                                <circle cx="16.5" cy="7.5" r="2.2" fill="#ffffff"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">المظهر</span>
                    </button>
                </div>

                <!-- ═══ المستطيل الثالث (في كل الشاشات): المرشد • المايك • التلفاز • الرجوع ═══ -->
                <div class="msg-fixed-command-pod pod-smart-guide">
                    <!-- 1. أعلى اليمين: المرشد الذكي -->
                    <button type="button" class="msg-quartet-btn msg-quartet-guide" onclick="window.openSmartGuide()" title="المرشد الذكي — شرح الشاشة كلها أو المشتريات أو المبيعات أو أي جزء تضغط عليه">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M12 3L2 8.5L12 14L22 8.5L12 3Z" fill="rgba(254,240,138,0.28)" stroke="#fde047" stroke-width="2.2" stroke-linejoin="round"/>
                                <path d="M6 11.5V16.5C6 18.2 8.7 19.8 12 19.8C15.3 19.8 18 18.2 18 16.5V11.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round"/>
                                <path d="M21 9.5V15.5" stroke="#fde047" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">المرشد</span>
                    </button>

                    <!-- 2. أسفل اليمين (تحت المرشد مباشرة): اسألني بالمايك -->
                    <button type="button" id="msg-fixed-mic-btn" class="msg-quartet-btn msg-quartet-mic" onclick="window.toggleSmartVoiceQA()" title="اسألني بالمايك — تحدث بصوتك أو اختر سؤالك لتظهر الإجابة على الشاشة فوراً">
                        <span class="msg-quartet-icon-box">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="9" y="2.5" width="6" height="11" rx="3" fill="rgba(167,243,208,0.3)" stroke="#ffffff" stroke-width="2.2"/>
                                <path d="M5.5 10.5C5.5 14.09 8.41 17 12 17C15.59 17 18.5 14.09 18.5 10.5" stroke="#fde047" stroke-width="2.2" stroke-linecap="round"/>
                                <path d="M12 17V21M8.5 21H15.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="msg-quartet-label">المايك</span>
                    </button>

                    <!-- 3. أعلى اليسار: التلفاز والقرآن الكريم -->
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

                    <!-- 4. الركن أسفل اليسار: الرجوع للخلف -->
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

        // Keep the 4 buttons accessible when scrolling deep down long tables/reports
        if (!window.__msgQuartetScrollBound) {
            window.__msgQuartetScrollBound = true;
            window.addEventListener('scroll', function () {
                const currentPod = document.getElementById('msg-universal-fixed-launcher');
                if (!currentPod || !currentPod.classList.contains('is-docked-in-header')) return;
                const sy = window.scrollY || window.pageYOffset || 0;
                if (sy > 140) {
                    currentPod.classList.add('is-scroll-floating');
                } else {
                    currentPod.classList.remove('is-scroll-floating');
                }
            }, { passive: true });
        }

        if (typeof window.syncHeaderPopoverUI === 'function') {
            try { window.syncHeaderPopoverUI(); } catch (e) {}
        }
    }

    function formatSpeedPctLabel(rate) {
        const pct = Math.round((rate || 0.92) * 100);
        if (rate <= 0.86) return `🐢 ${pct}%`;
        if (rate <= 0.95) return `✨ ${pct}%`;
        if (rate <= 1.03) return `🎙️ ${pct}%`;
        return `⚡ ${pct}%`;
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
                        <select id="msg-voice-select" class="sl-input" onchange="window.onMsgVoiceChange(this.value)" style="padding:5px 10px !important; font-size:0.78rem !important; font-weight:900 !important;" title="اختر اللهجة والصوت الرجالي">
                            <option value="egyptian">🇪🇬 صوت رجالي باللهجة المصرية الواضحة</option>
                            <option value="sudanese">🇸🇩 صوت رجالي باللهجة السودانية الواضحة</option>
                            <option value="gulf">🇸🇦 صوت رجالي باللهجة الخليجية الواضحة</option>
                        </select>

                        <!-- WORKING SPEED ARROWS IN MODAL HEADER -->
                        <div class="msg-speed-arrows-group" title="أسهم إبطاء وتسريع الصوت (تعمل فوراً)">
                            <button type="button" class="msg-speed-arrow-btn" onclick="window.adjustSpeechSpeedBy(-0.08, true)" title="إبطاء سرعة الصوت">⏬ -</button>
                            <span id="msg-modal-speed-lbl" style="font-size:0.76rem; font-weight:900; color:#fef08a; min-width:64px; text-align:center;">${formatSpeedPctLabel(selectedSpeechRate)}</span>
                            <button type="button" class="msg-speed-arrow-btn" onclick="window.adjustSpeechSpeedBy(0.08, true)" title="تسريع سرعة الصوت">+ ⏫</button>
                        </div>

                        <button type="button" onclick="window.toggleSmartVoiceQA()" class="sl-btn" style="background:linear-gradient(135deg,#059669,#0284c7); border:1.5px solid #34d399; color:#ffffff; font-weight:900; font-size:0.8rem; padding:6px 12px;">
                            🎙️ اسأل بالمايك
                        </button>
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
        coachBar.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:space-between; gap:5px; width:100%;">
                <div style="display:flex; align-items:center; gap:5px; min-width:0;">
                    <span id="msg-coach-mini-svg" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; flex-shrink:0;" title="مخطط بصري خفيف Pure-SVG">
                        <svg viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10" fill="rgba(56,189,248,0.18)" stroke="#fbbf24" stroke-width="2"/><path d="M8 12h8M13 8l4 4-4 4" stroke="#38bdf8" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span id="msg-coach-step-badge" style="background:linear-gradient(135deg,rgba(251,191,36,0.22),rgba(56,189,248,0.18)); border:1px solid #fbbf24; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900; max-width:165px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">🎓 المرشد الذكي</span>
                </div>
                <div style="display:flex; align-items:center; gap:3px; flex-shrink:0;">
                    <select id="msg-coach-voice-select" class="sl-input" onchange="window.onMsgVoiceChange(this.value)" style="padding:1px 4px !important; font-size:0.68rem !important; font-weight:900 !important; height:23px; border-radius:7px !important;" title="اللهجة الرجالية">
                        <option value="egyptian">🇪🇬 مصري</option>
                        <option value="sudanese">🇸🇩 سوداني</option>
                        <option value="gulf">🇸🇦 خليجي</option>
                    </select>
                    <button type="button" onclick="window.adjustSpeechSpeedBy(-0.08, false)" class="sl-btn" style="padding:1px 5px; font-size:0.68rem; border-radius:6px; font-weight:900;" title="إبطاء سرعة الصوت">🐢-</button>
                    <span id="msg-coach-speed-lbl" style="font-size:0.66rem; font-weight:900; min-width:30px; text-align:center;">${formatSpeedPctLabel(selectedSpeechRate)}</span>
                    <button type="button" onclick="window.adjustSpeechSpeedBy(0.08, false)" class="sl-btn" style="padding:1px 5px; font-size:0.68rem; border-radius:6px; font-weight:900;" title="تسريع الصوت">+⚡</button>
                    <button type="button" onclick="if(typeof window.openMdInteractiveImpactMapModal==='function'){window.openMdInteractiveImpactMapModal();}else{var b=document.getElementById('msg-c5-impact-map-banner');if(b)b.style.display=(b.style.display==='none'?'block':'none');}" class="sl-btn" style="padding:2px 5px; font-size:0.68rem; border-radius:6px; font-weight:900;" title="خريطة الترابط الثلاثية والقاعدة الذهبية (3-Stage SVG)">🗺️</button>
                    <button type="button" onclick="window.startQuickMasterDataQuiz()" class="sl-btn" style="padding:2px 5px; font-size:0.68rem; border-radius:6px; font-weight:900;" title="اختبار تفاعلي سريع (Quiz)">🎯</button>
                    <button type="button" id="msg-coach-voice-cmd-btn" onclick="window.startMasterDataVoiceCommand()" class="sl-btn" style="padding:2px 5px; font-size:0.68rem; border-radius:6px; font-weight:900;" title="بحث وتنقل صوتي فوري (Voice-to-Filter)">🎙️</button>
                    <button type="button" onclick="window.stepLiveAutopilot(-1)" class="sl-btn" style="padding:2px 5px; font-size:0.68rem; border-radius:6px;" title="الخطوة السابقة">⏮️</button>
                    <button type="button" id="msg-btn-pause-resume" onclick="window.togglePauseResumeAudio()" class="sl-btn" style="padding:2px 7px; font-size:0.7rem; background:linear-gradient(135deg,#f59e0b,#d97706); color:#020617 !important; border:none; font-weight:900; border-radius:6px;" title="إيقاف مؤقت / استئناف">⏸️</button>
                    <button type="button" onclick="window.stepLiveAutopilot(1)" class="sl-btn" style="padding:2px 5px; font-size:0.68rem; background:#38bdf8; color:#020617 !important; border:none; font-weight:900; border-radius:6px;" title="الخطوة التالية">⏭️</button>
                    <button type="button" onclick="window.stopLiveAutopilotSimulation()" class="sl-btn" style="padding:2px 6px; font-size:0.7rem; background:linear-gradient(135deg,#ef4444,#dc2626); color:#ffffff !important; border:1px solid #fca5a5; font-weight:900; border-radius:6px;" title="إغلاق المرشد">✕</button>
                </div>
            </div>
            <!-- Hidden internal anchors preserved so zero legacy calls ever fail -->
            <div style="display:none !important;">
                <input type="range" id="msg-audio-seek-slider" min="0" max="100" step="0.1" value="0">
                <span id="msg-audio-time-lbl">00:00</span>
                <select id="msg-coach-mode-select"><option value="all">all</option></select>
                <div id="msg-step-pills-wrap"></div>
                <div id="msg-coach-visual-stage"></div>
                <div id="msg-coach-speech-text"></div>
            </div>
        `;
        document.body.appendChild(coachBar);

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
        const txt = formatSpeedPctLabel(selectedSpeechRate);
        const l1 = document.getElementById('msg-modal-speed-lbl');
        const l2 = document.getElementById('msg-dock-speed-lbl');
        const l3 = document.getElementById('msg-coach-speed-lbl');
        if (l1) l1.textContent = txt;
        if (l2) l2.textContent = txt;
        if (l3) l3.textContent = txt;
    }

    function renderStepPillsForActiveList() {
        const pillsWrap = document.getElementById('msg-step-pills-wrap');
        if (!pillsWrap) return;
        const list = activeAutopilotStepsList || [];
        pillsWrap.innerHTML = list.map((_, i) => `
            <button type="button" class="msg-step-pill-btn ${i === autopilotStepIdx ? 'active' : ''}" onclick="window.jumpToAutopilotStep(${i})" title="الخطوة ${i + 1}">${i + 1}</button>
        `).join('');
    }

    window.adjustSpeechSpeedBy = function (delta, playPreviewIfIdle = false) {
        const nextRate = Math.max(0.70, Math.min(1.60, parseFloat((selectedSpeechRate + (parseFloat(delta) || 0)).toFixed(2))));
        window.onMsgSpeedChange(nextRate, playPreviewIfIdle);
    };

    window.onMsgSpeedChange = function (rateStr, playPreviewIfIdle = false) {
        const parsed = parseFloat(rateStr);
        if (isNaN(parsed)) return;

        // Preserve exact audio offset before changing playbackRate so time slider never jumps!
        if (isAudioPlaying && !isAudioPaused && activeAudioBuffer) {
            const curOff = getCurrentAudioOffset();
            activeAudioOffset = curOff;
            activeAudioStartTime = getAudioCtx().currentTime;
        }

        selectedSpeechRate = Math.max(0.70, Math.min(1.60, parsed));
        localStorage.setItem('msg_male_speed_v6', selectedSpeechRate.toFixed(2));
        syncSpeedLabelsUI();

        if (currentAudioSource && currentAudioSource.playbackRate) {
            try {
                currentAudioSource.playbackRate.value = selectedSpeechRate;
                if (activeAudioIsGenuine && currentAudioSource.detune) {
                    const pitchCompCents = Math.round(-1200 * Math.log2(selectedSpeechRate / 0.92));
                    currentAudioSource.detune.value = Math.max(-450, Math.min(450, pitchCompCents));
                }
            } catch (e) {}
        } else if (isAudioPaused && activeAudioBuffer) {
            // If paused, resume immediately at the new speed so the user hears the arrow working!
            startBufferAtOffset(activeAudioOffset);
        } else if (playPreviewIfIdle) {
            const pct = Math.round(selectedSpeechRate * 100);
            const speedMsg = selectedSpeechRate < 0.90
                ? `تم إبطاء سرعة الصوت إلى ${pct} بالمائة ليكون الشرح هادئاً ومتأنياً.`
                : `تم ضبط سرعة الصوت على ${pct} بالمائة.`;
            speakArabicMaleText(speedMsg, null, selectedMaleProfile, false);
        }
    };

    window.onMsgVoiceChange = function (profileId) {
        selectedMaleProfile = profileId;
        localStorage.setItem('msg_male_dialect_v6', profileId);
        const sel = document.getElementById('msg-voice-select');
        if (sel) sel.value = profileId;
        const coachSel = document.getElementById('msg-coach-voice-select');
        if (coachSel) coachSel.value = profileId;

        prewarmGuideAudio(currentGuideKey, profileId);

        if (isAutopilotRunning) {
            runCurrentAutopilotStep();
            return;
        }

        const greetings = {
            egyptian: `يا أهلاً بحضرتك يا فندم، تم تفعيل الصوت الرجالي الهادئ والواضح، باللهجة المصرية.`,
            sudanese: `حبابك عشرة، تم تفعيل الصوت الرجالي الهادئ والواضح، باللهجة السودانية.`,
            gulf:     `يا هلا والله، تم تفعيل الصوت الرجالي الهادئ والواضح، باللهجة الخليجية.`
        };
        speakArabicMaleText(greetings[profileId] || greetings.egyptian, null, profileId, false);
    };

    window.openSmartGuide = function (guideKey) {
        unlockBrowserAudioEnginesSync();
        ensureGuideDomElements();
        const resolvedKey = guideKey || buildUniversalScreenGuideIfNeeded();
        currentGuideKey = resolvedKey;

        // 👑 Direct Instant Start ("طوالي بلا شاشات وسيطة") + Interactive Hover Inspection for master-data.php, purchase.php, direct-sales.php, purchase-receipt.php & smart-logistics.php
        if (
            resolvedKey === 'master-data' ||
            resolvedKey === 'purchase-direct' ||
            resolvedKey === 'sales-direct' ||
            resolvedKey === 'sales-split' ||
            resolvedKey === 'purchase-receipt' ||
            resolvedKey === 'smart-logistics' ||
            window.location.pathname.includes('master-data') ||
            window.location.pathname.includes('purchase.php') ||
            window.location.pathname.includes('direct-sales.php') ||
            window.location.pathname.includes('purchase-receipt') ||
            window.location.pathname.includes('smart-logistics') ||
            document.querySelector('.md-tabs-sidebar') ||
            document.getElementById('purchase-editor-view') ||
            document.getElementById('invoice-editor-view') ||
            document.getElementById('view-editor') ||
            document.getElementById('sl-top-cockpit-bar')
        ) {
            const topGuideBtn = document.querySelector('.msg-quartet-guide');
            if (window.__mdInteractiveGuideActive || isAutopilotRunning) {
                window.__mdInteractiveGuideActive = false;
                if (topGuideBtn) topGuideBtn.style.boxShadow = '';
                window.stopLiveAutopilotSimulation();
                const tip = document.getElementById('msg-smart-hover-tooltip');
                if (tip) tip.style.display = 'none';
                return;
            }
            window.__mdInteractiveGuideActive = true;
            hasPlayedOpeningInvocation = false;
            window.hasPlayedOpeningInvocation = false;
            if (topGuideBtn) {
                topGuideBtn.style.boxShadow = '0 0 18px #fde047, inset 0 0 10px rgba(253,224,71,0.6)';
            }
            const isSmartLogistics = (resolvedKey === 'smart-logistics' || window.location.pathname.includes('smart-logistics') || !!document.getElementById('sl-top-cockpit-bar'));
            const activeSlMode = document.body.getAttribute('data-logistics-mode') || (window.location.search.includes('mode=purchases') ? 'purchases' : 'sales');
            const initialMode = isSmartLogistics ? (activeSlMode === 'purchases' ? 'purchases' : 'sales') : 'all';

            const modeSel = document.getElementById('msg-coach-mode-select');
            if (modeSel) {
                if (isSmartLogistics) {
                    if (activeSlMode === 'purchases') {
                        modeSel.innerHTML = `
                            <option value="purchases">📥 شرح بوابة استلام المشتريات (GRN)</option>
                            <option value="hands-on">🎯 تدريب عملي تفاعلي (3 خطوات)</option>
                            <option value="pick">👆 الشرح بالتأشير على أي زر أو حقل</option>
                        `;
                    } else {
                        modeSel.innerHTML = `
                            <option value="sales">📤 شرح بوابة تسليم المبيعات (GDN)</option>
                            <option value="hands-on">🎯 تدريب عملي تفاعلي (3 خطوات)</option>
                            <option value="pick">👆 الشرح بالتأشير على أي زر أو حقل</option>
                        `;
                    }
                } else if (resolvedKey === 'sales-split') {
                    modeSel.innerHTML = `
                        <option value="all">✂️ شرح شاشة تجزئة الفواتير والشاشات الوليدة</option>
                        <option value="pick">👆 الشرح بالتأشير على أي زر أو حقل</option>
                    `;
                } else {
                    modeSel.innerHTML = `
                        <option value="all">🌐 الملخص التنفيذي والشرح الشامل للشاشة</option>
                        <option value="pick">👆 الشرح بالتأشير على أي زر أو حقل</option>
                    `;
                }
                modeSel.value = initialMode;
            }
            window.startLiveAutopilotSimulation(initialMode);
            return;
        }

        window.stopLiveAutopilotSimulation();
        window.openSmartGuideEncyclopediaModal(resolvedKey);
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

    function placeCursorAndRingOnRect(el, labelText, triggerClickEffect) {
        const cursor = document.getElementById('msg-ghost-cursor');
        const cursorLbl = document.getElementById('msg-ghost-cursor-label');
        const ring = document.getElementById('msg-live-target-ring');
        if (!cursor || !ring || !el) return;

        const rect = el.getBoundingClientRect();
        const pad = 6;

        ring.style.display = 'block';
        ring.style.top = `${Math.max(4, rect.top - pad)}px`;
        ring.style.left = `${Math.max(4, rect.left - pad)}px`;
        ring.style.width = `${rect.width + pad * 2}px`;
        ring.style.height = `${rect.height + pad * 2}px`;

        cursor.style.display = 'block';
        if (cursorLbl) cursorLbl.textContent = labelText || 'انظر هنا';
        cursor.style.top = `${rect.top + Math.min(rect.height / 2, 28)}px`;
        cursor.style.left = `${rect.left + Math.min(rect.width / 2, 80)}px`;

        if (triggerClickEffect) {
            cursor.classList.add('clicking');
            setTimeout(() => cursor.classList.remove('clicking'), 450);
        }

        // Smart Auto-Dodge: if target is in the lower 38% of the viewport, dock the capsule at top-left so it never covers the target!
        const coachBar = document.getElementById('msg-live-coach-bar');
        const posBtn = document.getElementById('msg-coach-pos-btn');
        if (coachBar && coachBar.dataset.userLockedPos !== '1') {
            const viewH = window.innerHeight || document.documentElement.clientHeight;
            if (rect.bottom > viewH - 155) {
                coachBar.classList.add('docked-top');
                if (posBtn) posBtn.textContent = '⬇️ للأسفل';
            } else {
                coachBar.classList.remove('docked-top');
                if (posBtn) posBtn.textContent = '⬆️ للأعلى';
            }
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
        ensureGuideDomElements();
        window.closeSmartGuide();
        isAutopilotRunning = true;

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

    function cleanupGuideTempPopups() {
        dubbedVisualTimers.forEach(t => clearTimeout(t));
        dubbedVisualTimers = [];
        const mirror = document.getElementById('msg-live-select-mirror');
        if (mirror) mirror.style.display = 'none';
        const cdd = document.getElementById('customer-dropdown-list');
        if (cdd) cdd.style.display = 'none';
        const qdd = document.getElementById('quick-item-dropdown-list');
        if (qdd) qdd.style.display = 'none';
        const gm = document.getElementById('grid-settings-modal');
        if (gm) gm.style.display = 'none';
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
            'msg-c5-impact-map-banner'
        ].forEach(id => {
            const m = document.getElementById(id);
            if (m) {
                m.style.display = 'none';
                m.classList.remove('active');
            }
        });
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

    function renderLiveDubbedVisualStage(step) {
        clearC5ImpactPhaseTimers();
        let banner = document.getElementById('msg-c5-impact-map-banner');
        if (!step || step.diagramType !== 'c5-impact-map') {
            if (banner) banner.style.display = 'none';
            return;
        }
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
            screenTitle: 'شاشة تجزئة وتقسيم الفواتير وأذونات التسليم',
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
                <g id="msg-c5-stage-1" style="transition:all 0.3s ease;">
                    <rect x="578" y="6" width="254" height="118" rx="12" fill="${boxBg}" stroke="#f59e0b" stroke-width="3"/>
                    <text x="705" y="28" fill="#f59e0b" font-size="12.5" font-weight="900" text-anchor="middle">1️⃣ من أين تأتي المعلومة؟ (المدخلات)</text>
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
                <g id="msg-c5-stage-2" style="opacity:0.55;transition:all 0.3s ease;">
                    <rect x="278" y="6" width="240" height="118" rx="12" fill="${boxBg}" stroke="#10b981" stroke-width="2.8"/>
                    <text x="398" y="28" fill="#10b981" font-size="12.5" font-weight="900" text-anchor="middle">2️⃣ عمل الشاشة والضبط المحاسبي</text>
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
                <g id="msg-c5-stage-3" style="opacity:0.55;transition:all 0.3s ease;">
                    <rect x="6" y="6" width="212" height="118" rx="12" fill="${boxBg}" stroke="#38bdf8" stroke-width="2.8"/>
                    <text x="112" y="28" fill="#38bdf8" font-size="12" font-weight="900" text-anchor="middle">3️⃣ أين تُسمّع؟ (التقارير والشاشات)</text>
                    <text x="112" y="52" fill="${fgText}" font-size="10.8" font-weight="900" text-anchor="middle">${escapeSvgText(meta.reports[0] || '')}</text>
                    <text x="112" y="76" fill="${fgText}" font-size="10.8" font-weight="900" text-anchor="middle">${escapeSvgText(meta.reports[1] || '')}</text>
                    <text x="112" y="100" fill="${fgText}" font-size="10.8" font-weight="900" text-anchor="middle">${escapeSvgText(meta.screens[0] || '')}</text>
                </g>
            </svg>
            <div id="msg-c5-golden-rule-strip" style="margin-top:7px;padding:7px 11px;border-radius:10px;border:2px solid #f59e0b;background:${boxBg};display:flex;flex-direction:column;gap:3px;font-size:0.8rem;font-weight:900;">
                <div>👑 <span style="color:#f59e0b;">القاعدة الذهبية للشاشة:</span> ${escapeSvgText(meta.goldenRule || 'أسِّس بياناتك وأرصدتك بدقة مرة واحدة ينضبط لك النظام المحاسبي والمخزني بالكامل تلقائياً.')}</div>
                <div style="font-size:0.76rem;color:#10b981;">🛡️ <span>رسالة الطمأنينة للمحاسب:</span> ${escapeSvgText(meta.reassurance || 'لا تخشَ الخطأ أبداً؛ الشاشة محمية بدرع منع الحذف العشوائي وحفظ المسودات والتراجع الفوري.')}</div>
            </div>
        `;
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

    let lastAutopilotStepStartTs = 0;

    function runCurrentAutopilotStep() {
        if (!isAutopilotRunning || !activeAutopilotStepsList.length) return;
        lastAutopilotStepStartTs = Date.now();

        cleanupGuideTempPopups();

        const step = activeAutopilotStepsList[autopilotStepIdx];
        if (!step) return;

        // البند 5 من الدستور الخامس: تخطي الخطوات غير المتاحة لصلاحية المستخدم الحالي تلقائياً
        if (typeof window.isGuideStepAllowedForCurrentRole === 'function' && !window.isGuideStepAllowedForCurrentRole(step)) {
            if (autopilotStepIdx + 1 < activeAutopilotStepsList.length) {
                autopilotStepIdx++;
                runCurrentAutopilotStep();
            } else {
                window.stopLiveAutopilotSimulation();
            }
            return;
        }

        renderStepPillsForActiveList();

        // Pre-decode the NEXT step in advance while the current step is playing!
        const nextStep = activeAutopilotStepsList[autopilotStepIdx + 1];
        if (nextStep) {
            const nextTxt = resolveStepSpeech(nextStep, selectedMaleProfile, false);
            fetchAndDecodeMaleBuffer(nextTxt, selectedMaleProfile).catch(() => {});
        }

        const badge = document.getElementById('msg-coach-step-badge');
        const speechBox = document.getElementById('msg-coach-speech-text');
        const playOpeningOnceNow = (autopilotStepIdx === 0 && !hasPlayedOpeningInvocation);
        if (playOpeningOnceNow) {
            hasPlayedOpeningInvocation = true;
            window.hasPlayedOpeningInvocation = true;
        }
        const spoken = resolveStepSpeech(step, selectedMaleProfile, playOpeningOnceNow);

        if (badge) badge.textContent = `${step.badge} (${autopilotStepIdx + 1}/${activeAutopilotStepsList.length})`;
        if (speechBox) speechBox.textContent = spoken;

        // Render the synchronized Dubbed Visual Diagram & Flowchart Stage!
        renderLiveDubbedVisualStage(step, autopilotStepIdx, activeAutopilotStepsList.length);

        // Execute step DOM action first if needed (opens nested screen, dropdown, or sub-panel), then move cursor smoothly!
        if (typeof step.action === 'function') {
            try { step.action(); } catch (e) {}
        }

        setTimeout(() => {
            if (!isAutopilotRunning) return;
            const el = document.querySelector(step.targetSelector) || document.body;
            moveGhostCursorToElement(el, step.cursorLabel, true);

            // If this step targets a <select> or dropdown-open diagramType (and isn't already showing custom dropdown), display the live options list on screen!
            const cddOpen = document.getElementById('customer-dropdown-list')?.style.display === 'block';
            const qddOpen = document.getElementById('quick-item-dropdown-list')?.style.display === 'block';
            if (!cddOpen && !qddOpen && (el.tagName === 'SELECT' || el.querySelector?.('select') || step.diagramType === 'dropdown-open')) {
                showLiveSelectOptionsMirror(el, step.diagramMeta?.childItems, step.cursorLabel || step.badge);
            }
        }, 70);

        speakArabicMaleText(spoken, () => {
            if (!isAutopilotRunning || isAudioPaused) return;
            nextStepTimeoutId = setTimeout(() => {
                if (isAutopilotRunning && !isAudioPaused) window.stepLiveAutopilot(1);
            }, 220);
        }, selectedMaleProfile, playOpeningOnceNow);
    }

    // ── INTERACTIVE "PICK ANY ELEMENT TO EXPLAIN" MODE (المؤشر الحر لشرح أي جزء) ─
    window.startSmartPickElementMode = function () {
        ensureGuideDomElements();
        window.closeSmartGuide();
        window.stopLiveAutopilotSimulation();
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
        // البند 3 من الدستور الخامس: القفز المباشر بالضغط (Click-to-Jump) أثناء عمل المرشد الذكي
        if (window.__mdInteractiveGuideActive || isAutopilotRunning) {
            if (e.target.closest('#msg-live-coach-bar, #msg-smart-hover-tooltip, #msg-universal-fixed-launcher, #msg-universal-modal, #msg-c5-impact-map-banner')) {
                return;
            }
            const jumpTarget = e.target.closest('button, a, input, select, textarea, .md-tab-btn, .pro-field-card, .input-group, th');
            if (!jumpTarget) return;
            if (typeof window.isElementAllowedForCurrentUserRole === 'function' && !window.isElementAllowedForCurrentUserRole(jumpTarget)) return;
            const info = (typeof window.explainMasterDataDomElement === 'function' && window.explainMasterDataDomElement(jumpTarget))
                || (typeof window.explainDirectSalesDomElement === 'function' && window.explainDirectSalesDomElement(jumpTarget))
                || (typeof window.explainPurchaseDomElement === 'function' && window.explainPurchaseDomElement(jumpTarget))
                || (typeof window.autoSynthesizeElementExplanation === 'function' && window.autoSynthesizeElementExplanation(jumpTarget));
            if (info && info.title) {
                lastAutopilotStepStartTs = Date.now();
                const badge = document.getElementById('msg-coach-step-badge');
                const speechBox = document.getElementById('msg-coach-speech-text');
                const adapted = stripAllSalawatPrefixes(adaptTextToDialect(`${info.title}. ${info.desc}`, selectedMaleProfile));
                if (badge) badge.textContent = `🎯 ${info.title}`;
                if (speechBox) speechBox.textContent = adapted;
                moveGhostCursorToElement(jumpTarget, info.title, true);
                if (jumpTarget.tagName === 'SELECT' || jumpTarget.querySelector?.('select')) {
                    showLiveSelectOptionsMirror(jumpTarget, null, info.title);
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
        if (cursor) cursor.style.display = 'none';
        if (ring) ring.style.display = 'none';
        if (coachBar) coachBar.style.display = 'none';
        if (pinnedDock) pinnedDock.style.display = 'none';

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

    function getActiveQuizStepsList() {
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
    let hoverTooltipTimer = null;
    let hoverHideTimer = null;

    function getOrCreateHoverTooltipEl() {
        let el = document.getElementById('msg-smart-hover-tooltip');
        if (!el) {
            el = document.createElement('div');
            el.id = 'msg-smart-hover-tooltip';
            el.className = 'msg-smart-hover-tooltip';
            el.onmouseenter = () => { if (hoverHideTimer) clearTimeout(hoverHideTimer); };
            el.onmouseleave = () => { el.style.display = 'none'; };
            document.body.appendChild(el);
        }
        return el;
    }

    let lastHoverInspectedEl = null;
    document.addEventListener('mouseover', function (e) {
        // 👑 Interactive Hover Inspection ("الشرح التفاعلي عند التأشير بالمؤشر") when Guide is active on master-data.php, purchase.php, direct-sales.php, purchase-receipt.php & smart-logistics.php
        if (window.__mdInteractiveGuideActive && (typeof window.explainMasterDataDomElement === 'function' || typeof window.explainPurchaseDomElement === 'function' || typeof window.explainDirectSalesDomElement === 'function' || typeof window.explainPurchaseReceiptDomElement === 'function' || typeof window.explainSmartLogisticsDomElement === 'function')) {
            if (isAutopilotRunning && !isPickElementModeActive && (Date.now() - lastAutopilotStepStartTs < 1600)) return;
            if (e.target.closest('#msg-live-coach-bar, #msg-smart-hover-tooltip, #msg-universal-fixed-launcher')) return;
            const inspectTarget = e.target.closest('button, a, input, select, textarea, .md-tab-btn, .pro-field-card, .input-group, .h-form-group, .hub-action-card, .fin-metric-pill, .tot-toggle-btn, .pay-toggle-btn, #customer-financial-card, #fin-strip, #purchase-3way-match-badge, .uiverse-switch, .grn-qc-pill, .grn-nav-pill, .grn-col-origQty, .grn-col-item, .grn-col-unit, .grn-col-qty, .grn-col-pendingBase, .grn-col-baseQty, .grn-progress-wrap, .grn-meta-bar > div, .sl-mode-tab, .sl-kpi-card, .sl-unit-box, th');
            if (!inspectTarget || inspectTarget === lastHoverInspectedEl) return;
            if (isMdQuickQuizActive && checkMdQuickQuizHit(inspectTarget)) return;
            const mdInfo = (typeof window.explainSmartLogisticsDomElement === 'function' && window.explainSmartLogisticsDomElement(inspectTarget))
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
                cleanupGuideTempPopups();
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
        const activeHoverKey = buildUniversalScreenGuideIfNeeded() || 'smart-logistics';
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

    document.addEventListener('mouseout', function (e) {
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

    // Inject the fixed square launcher immediately on DOM ready and pre-warm audio for the current screen!
    function prewarmActiveScreenOnReady() {
        ensureFixedSquareLauncher();
        setTimeout(() => {
            const activeKey = buildUniversalScreenGuideIfNeeded() || 'smart-logistics';
            prewarmGuideAudio(activeKey, selectedMaleProfile);
        }, 450);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', prewarmActiveScreenOnReady);
    } else {
        prewarmActiveScreenOnReady();
    }

    window.addEventListener('load', () => {
        ensureFixedSquareLauncher();
        setTimeout(() => {
            const activeKey = buildUniversalScreenGuideIfNeeded() || 'smart-logistics';
            prewarmGuideAudio(activeKey, selectedMaleProfile);
        }, 600);
    });

    // 🔄 Universal Live DOM Watcher: auto-updates the Smart Guide on ANY screen when new buttons/fields are added
    if (typeof MutationObserver !== 'undefined') {
        let guideSyncTimer = null;
        const guideDomObserver = new MutationObserver((mutations) => {
            let added = false;
            for (const m of mutations) {
                if (m.addedNodes && m.addedNodes.length > 0) { added = true; break; }
            }
            if (!added) return;
            if (guideSyncTimer) clearTimeout(guideSyncTimer);
            guideSyncTimer = setTimeout(() => {
                if (typeof window.syncDynamicDomElementsIntoSmartGuide === 'function') {
                    window.syncDynamicDomElementsIntoSmartGuide();
                }
            }, 250);
        });
        const attachGuideObserver = () => {
            if (document.body) guideDomObserver.observe(document.body, { childList: true, subtree: true });
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', attachGuideObserver);
        else attachGuideObserver();
    }

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
                /* تعميم فواصل شاشة المشتريات (ما بعد الدساتير) المستقلة عن currentColor على كافة الشاشات المشابهة */
                #pos-grid, .pos-grid {
                    border-collapse: collapse !important;
                    border: 1px solid rgba(148, 163, 184, 0.16) !important;
                }
                #pos-grid thead th, .pos-grid thead th {
                    border: 1px solid rgba(148, 163, 184, 0.18) !important;
                    border-bottom: 1.5px solid rgba(148, 163, 184, 0.28) !important;
                    font-family: 'Tajawal', 'Cairo', sans-serif !important;
                    font-weight: 900 !important;
                }
                #pos-grid tbody td, .pos-grid tbody td {
                    border: 1px solid rgba(148, 163, 184, 0.14) !important;
                    font-weight: 800 !important;
                }
                #pos-grid tbody td .g-input,
                #pos-grid tbody td .g-select,
                #pos-grid tbody td .g-readonly,
                #pos-grid tbody td .avail-val,
                #pos-grid tbody td .total-val,
                .pos-grid tbody td .g-input,
                .pos-grid tbody td .g-select {
                    border: 1px solid rgba(148, 163, 184, 0.22) !important;
                    box-shadow: none !important;
                    font-weight: 800 !important;
                }
                #pos-grid tbody td .g-input:focus,
                #pos-grid tbody td .g-select:focus {
                    border-color: rgba(56, 189, 248, 0.55) !important;
                    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.18) !important;
                }
                :root.light-theme #pos-grid,
                :root.light-theme .pos-grid,
                :root.light-theme #pos-grid thead th,
                :root.light-theme #pos-grid tbody td,
                :root.light-theme .pos-grid thead th,
                :root.light-theme .pos-grid tbody td {
                    border: 1px solid rgba(148, 163, 184, 0.26) !important;
                }
                :root.light-theme #pos-grid tbody td .g-input,
                :root.light-theme #pos-grid tbody td .g-select,
                :root.light-theme #pos-grid tbody td .g-readonly,
                :root.light-theme #pos-grid tbody td .avail-val,
                :root.light-theme #pos-grid tbody td .total-val {
                    border: 1px solid #cbd5e1 !important;
                    box-shadow: none !important;
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
            if (!e.ctrlKey || typeof window.setPurchaseProportionalZoom === 'function') return;
            e.preventDefault();
            const cur = getSavedGlobalScale();
            const step = e.deltaY < 0 ? 0.05 : -0.05;
            window.applyUniversalProportionalScaleC15(cur + step);
        }, { passive: false });
    })();
})();
