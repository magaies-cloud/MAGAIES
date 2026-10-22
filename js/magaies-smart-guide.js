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

    const SHORT_SAHIHAYN_SALAWAT = 'اللَّهُمَّ صَلِّ عَلَى مُحَمَّدٍ وَعَلَى آلِ مُحَمَّدٍ.';

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
            box-shadow: 0 0 32px rgba(251, 191, 36, 0.85), inset 0 0 18px rgba(251, 191, 36, 0.35);
            transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
            animation: msgTargetPulse 1.4s infinite ease-in-out;
        }
        @keyframes msgTargetPulse {
            0%, 100% { border-color: #fbbf24; box-shadow: 0 0 28px rgba(251, 191, 36, 0.85); }
            50% { border-color: #38bdf8; box-shadow: 0 0 38px rgba(56, 189, 248, 0.95); }
        }

        /* ── SLEEK COMPACT NON-BLOCKING FLOATING COACH CAPSULE ── */
        .msg-live-coach-bar {
            position: fixed;
            bottom: 12px;
            left: 14px;
            z-index: 100060;
            width: auto;
            max-width: min(760px, 95vw);
            background: linear-gradient(145deg, rgba(2, 6, 23, 0.94), rgba(15, 23, 42, 0.96));
            border: 2px solid rgba(251, 191, 36, 0.85);
            border-radius: 20px;
            padding: 8px 14px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.88), 0 0 24px rgba(251, 191, 36, 0.35);
            display: none;
            flex-direction: column;
            gap: 6px;
            direction: rtl;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: top 0.32s cubic-bezier(0.22, 1, 0.36, 1), bottom 0.32s cubic-bezier(0.22, 1, 0.36, 1), transform 0.25s ease;
        }
        .msg-live-coach-bar.docked-top {
            bottom: auto !important;
            top: 128px !important;
            left: 14px !important;
        }
        .msg-live-coach-bar.is-minimized .msg-coach-full-body {
            display: none !important;
        }
        .msg-live-coach-bar.is-minimized {
            padding: 6px 12px !important;
            border-radius: 999px !important;
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
            width: 196px !important;
            min-width: 196px !important;
            max-width: 196px !important;
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
            font-size: 11.6px !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            line-height: 1 !important;
            letter-spacing: 0px !important;
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
    let selectedSpeechRate = parseFloat(localStorage.getItem('msg_male_speed_v6') || '0.92');
    if (isNaN(selectedSpeechRate) || selectedSpeechRate < 0.75 || selectedSpeechRate > 1.15) {
        selectedSpeechRate = 0.92;
    }
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
            btnMain.innerHTML = pauseBtnText;
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

    function stopCurrentSpeech() {
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

    function resolveStepSpeech(step, dialect = selectedMaleProfile) {
        if (!step) return '';
        if (step.speechByDialect && step.speechByDialect[dialect]) {
            const val = step.speechByDialect[dialect];
            return (typeof val === 'function') ? val() : val;
        }
        const base = (typeof step.speech === 'function') ? step.speech() : (step.speech || '');
        return adaptTextToDialect(base, dialect);
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
        let arrayBuf = rawArrayBufferCache.get(cacheKey);
        let isGenuine = isGenuineNeuralCache.get(cacheKey);
        if (!arrayBuf) {
            const url = `api/tts_male.php?dialect=${encodeURIComponent(dialect)}&text=${encodeURIComponent(cleanTxt)}`;
            const res = await fetch(url);
            if (!res.ok) throw new Error('TTS HTTP Error');
            const engineHdr = res.headers.get('X-Voice-Engine') || 'Edge-Neural-Male';
            isGenuine = (engineHdr === 'Edge-Neural-Male');
            arrayBuf = await res.arrayBuffer();
            rawArrayBufferCache.set(cacheKey, arrayBuf.slice(0));
            isGenuineNeuralCache.set(cacheKey, isGenuine);
        }
        const ctx = getAudioCtx();
        const decoded = await ctx.decodeAudioData(arrayBuf.slice(0));
        decodedBufferCache.set(cacheKey, decoded);
        return { buffer: decoded, isGenuineNeural: isGenuine !== false };
    }

    // Pre-warm all guide steps in the background so playback is 100% instant and seamless!
    async function prewarmGuideAudio(guideKey = 'smart-logistics', dialect = selectedMaleProfile) {
        const data = window.MagaiesSmartGuides && window.MagaiesSmartGuides[guideKey];
        if (!data || !Array.isArray(data.autopilotSteps)) return;
        const allSteps = [...data.autopilotSteps, ...(typeof SALES_ONLY_STEPS !== 'undefined' ? SALES_ONLY_STEPS : [])];
        for (const st of allSteps) {
            const txt = resolveStepSpeech(st, dialect);
            const cacheKey = `${dialect}|${txt.trim()}`;
            if (txt && !rawArrayBufferCache.has(cacheKey)) {
                try {
                    const res = await fetch(`api/tts_male.php?dialect=${encodeURIComponent(dialect)}&text=${encodeURIComponent(txt.trim())}`);
                    if (res.ok) {
                        const engineHdr = res.headers.get('X-Voice-Engine') || 'Edge-Neural-Male';
                        const buf = await res.arrayBuffer();
                        rawArrayBufferCache.set(cacheKey, buf);
                        isGenuineNeuralCache.set(cacheKey, engineHdr === 'Edge-Neural-Male');
                    }
                } catch (e) {}
            }
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
            // Pitch-compensate so speeding up or slowing down keeps the deep natural male voice!
            const pitchCompCents = Math.round(-1200 * Math.log2(selectedSpeechRate / 0.92));
            source.detune.value = Math.max(-450, Math.min(450, pitchCompCents));
            source.playbackRate.value = selectedSpeechRate;

            const clarity = ctx.createBiquadFilter();
            clarity.type = 'peaking';
            clarity.frequency.value = 2850;
            clarity.Q.value = 1.0;
            clarity.gain.value = 2.6;

            source.connect(clarity);
            clarity.connect(ctx.destination);
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
        if (!activeAudioBuffer) {
            window.startLiveAutopilotSimulation('all');
            return;
        }
        const ctx = getAudioCtx();
        if (ctx.state === 'suspended') {
            await ctx.resume();
        }
        if (isAudioPlaying && !isAudioPaused) {
            // Pause at exact current second
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
            // Resume from exact paused second (or restart if at end)
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

    async function speakArabicMaleText(text, onEndCallback, dialect = selectedMaleProfile) {
        stopCurrentSpeech();
        ensureGuideDomElements();

        // Always show the fixed audio controller bar and pinned top-left dock as soon as any recording starts!
        const coachBar = document.getElementById('msg-live-coach-bar');
        if (coachBar) coachBar.style.display = 'flex';
        const pinnedDock = document.getElementById('msg-pinned-audio-dock');
        if (pinnedDock) pinnedDock.style.display = 'inline-flex';
        const speechBox = document.getElementById('msg-coach-speech-text');
        if (speechBox && text) speechBox.textContent = text;

        try {
            const ctx = getAudioCtx();
            if (ctx.state === 'suspended') {
                await ctx.resume();
            }
            const { buffer: audioBuffer, isGenuineNeural } = await fetchAndDecodeMaleBuffer(text, dialect);
            activeAudioBuffer = audioBuffer;
            activeAudioIsGenuine = isGenuineNeural;
            activeAudioOnEnd = onEndCallback || null;
            startBufferAtOffset(0);
        } catch (err) {
            if (onEndCallback) setTimeout(onEndCallback, 1500);
        }
    }

    // ── SCREEN GUIDES REGISTRY ───────────────────────────────────────────────
    window.MagaiesSmartGuides = window.MagaiesSmartGuides || {};

    window.MagaiesSmartGuides['smart-logistics'] = {
        title: 'المرشد الذكي الشامل: بوابة الاستلام والتسليم الموحدة (GRN ⇄ GDN)',
        subtitle: 'شرح تطبيقي حي بصوت رجالي واضح بـ 3 لهجات (🇸🇩 سودانية | 🇪🇬 مصرية | 🇸🇦 خليجية) والماوس المتحرك',

        // LIVE AUTOPILOT SCENARIO STEPS (بـ 3 لهجات رجالية واضحة: سودانية، مصرية، وخليجية)
        autopilotSteps: [
            {
                badge: 'الخطوة 1️⃣ — اختيار قسم المشتريات أو المبيعات',
                cursorLabel: 'نضغط هنا لقسم المشتريات 📥',
                targetSelector: '#sl-tab-purchases',
                speechByDialect: {
                    egyptian: () => `${SHORT_SAHIHAYN_SALAWAT} يا أهلاً بحضرتك يا فندم. علشان نستلم بضاعة جديدة وندخّلها المخزن، بنروح الأول على زرار منظومة المشتريات الأزرق اللي فوق ده، ونضغط عليه، فالشاشة بتفتح معانا على طول، قسم استلام البضاعة.`,
                    sudanese: () => `${SHORT_SAHIHAYN_SALAWAT} حبابك عشرة. عشان تستلم بضاعة جديدة وتدخلها المخزن، أول حاجة تضغط فوق على زر منظومة المشتريات الأزرق ده، وطوالي الشاشة بتفتح ليك، وضع استلام البضاعة.`,
                    gulf:     () => `${SHORT_SAHIHAYN_SALAWAT} يا هلا والله. عشان تستلم بضاعة جديدة وتدخلها المستودع، نتوجه أول شيء لزر منظومة المشتريات الأزرق في أعلى الشاشة، ونضغط عليه، وتتحول الشاشة مباشرة، لوضع إدخال البضاعة.`
                },
                speech: () => `${SHORT_SAHIHAYN_SALAWAT} لاستلام بضاعة جديدة، نتجه أولاً إلى زر منظومة المشتريات الأزرق في أعلى الشاشة ونضغط عليه، فتتحول الشاشة إلى وضع إدخال البضاعة للمخزن.`,
                action: () => {
                    if (typeof window.switchLogisticsMode === 'function') {
                        window.switchLogisticsMode('purchases', true);
                    }
                }
            },
            {
                badge: 'الخطوة 2️⃣ — عرض الأذونات غير المستلمة',
                cursorLabel: 'الأذونات غير المستلمة ⏳',
                targetSelector: '#sl-kpi-card-pending',
                speechByDialect: {
                    egyptian: () => `وبعد كده على طول، بنضغط على كارت الأذونات اللي لسه ما استلمناهاش، علشان تفتح قدامنا كل الفواتير، اللي مستنية الاستلام.`,
                    sudanese: () => `وبعد داك، تضغط طوالي على بطاقة الأذونات الما مستلمة، عشان تظهر قدامك كل الفواتير، الراجية الاستلام.`,
                    gulf:     () => `ومباشرة، ننتقل إلى بطاقة الأذونات غير المستلمة ونضغط عليها، عشان تطلع قدامنا الفواتير، اللي تنتظر الاستلام.`
                },
                speech: () => `ومباشرةً ننتقل إلى بطاقة الأذونات غير المستلمة ونضغط عليها، لتظهر أمامنا الفواتير التي تنتظر الاستلام.`,
                action: () => {
                    if (typeof window.setSlRegistryFilter === 'function') {
                        window.setSlRegistryFilter('pending');
                    }
                    window.ensureLiveDemoInvoiceForGuide();
                }
            },
            {
                badge: 'الخطوة 3️⃣ — اختيار الإذن وفتحه',
                cursorLabel: 'نضغط على الإذن لفتحه 📄',
                targetSelector: '#sl-registry-tbody tr:first-child',
                speechByDialect: {
                    egyptian: () => `ودلوقتي يا فندم، بنضغط بالماوس على الإذن اللي إحنا عايزينه من الجدول، فبيفتح معانا تحت على طول، بكل الأصناف اللي موجودة جواه.`,
                    sudanese: () => `وهسي، تضغط على الإذن الدايرو من الجدول، وبيفتح معاك طوالي في الكرت التحت، بكامل الأصناف الفيها.`,
                    gulf:     () => `والحين، نضغط على الإذن المطلوب من الجدول، ويفتح معنا فوراً في البطاقة السفلية، بكامل أصنافه.`
                },
                speech: () => `نضغط الآن على الإذن المطلوب من الجدول، فينفتح فوراً في البطاقة السفلية بكامل أصنافه.`,
                action: () => {
                    const firstRow = document.querySelector('#sl-registry-tbody tr:first-child');
                    if (firstRow) firstRow.click();
                }
            },
            {
                badge: 'الخطوة 4️⃣ — إدخال الكمية المستلمة من الباقي',
                cursorLabel: 'الباقي 7 ⬅️ نكتب المستلم الآن (5) ✍️',
                targetSelector: '.sl-qty-primary-input',
                speechByDialect: {
                    egyptian: () => `حضرتك شايف هنا، الإذن ده باقي فيه سبعة، فلو استلمنا النهارده خمسة بس، بنكتب رقم خمسة في مربع الكمية ده، والبرنامج بيحسب لوحده، إن فاضل اتنين للدفعة الجاية.`,
                    sudanese: () => `شوف هنا، الإذن ده باقي فيهو سبعة، لو استلمنا الليلة خمسة بس، بنكتب رقم خمسة في مربع الكمية، والبرنامج براهو بيحسب ليك، إنو الباقي اتنين للدفعة الجاية.`,
                    gulf:     () => `هذا الإذن باقي فيه سبعة، فإذا استلمنا اليوم خمسة بس، نكتب رقم خمسة في مربع الكمية، والبرنامج يحسب تلقائياً، إنه باقي اثنين للدفعة الجاية.`
                },
                speech: () => `هذا الإذن باقي فيه سبعة، فإذا استلمنا خمسة فقط، نكتب رقم خمسة في مربع الكمية، ويحسب البرنامج تلقائياً أنه تبقى اثنان للدفعة القادمة.`,
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
                badge: 'الخطوة 5️⃣ — الاعتماد النهائي وحفظ الحركة',
                cursorLabel: 'زر الاعتماد 🚀 وبجانبه زر التراجع ↩️',
                targetSelector: '#sl-btn-execute',
                speechByDialect: {
                    egyptian: () => `وفي الآخر خالص، بنضغط على زرار تأكيد واعتماد، علشان الكمية تدخل المخزن، وجنبه دايماً زرار إلغاء وعكس الحركة، لو حبيت ترجع في أي وقت.`,
                    sudanese: () => `وفي النهاية، تضغط على زر تأكيد واعتماد، عشان الكمية تنزل في المخزن طوالي، وجنبو دايماً زر إلغاء وعكس الحركة، لو داير تتراجع في أي وقت.`,
                    gulf:     () => `وبالأخير، نضغط على زر تأكيد واعتماد، لإدخال الكمية إلى المستودع فوراً، وبجانبه دائماً زر إلغاء وعكس الحركة، للتراجع في أي وقت.`
                },
                speech: () => `وأخيراً نضغط على زر تأكيد واعتماد لإدخال الكمية إلى المخزن، وبجانبه دائماً زر إلغاء وعكس الحركة للتراجع في أي وقت.`,
                action: () => {}
            }
        ],

        visualSteps: [
            {
                stepBadge: 'الخطوة 1️⃣ — اختر نوع العملية أولاً',
                title: 'حدد هل البضاعة «خارجة للمبيعات 📤» أم «داخلة للمشتريات 📥»؟',
                desc: '• اضغط (📤 المبيعات - أخضر) لتسليم بضاعة لعميل وخصمها من المخزن.\n• اضغط (📥 المشتريات - أزرق) لاستلام بضاعة من مورد وإدخالها للمخزن.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="10" y="18" width="142" height="74" rx="12" fill="#064e3b" stroke="#34d399" stroke-width="2.5"/>
                    <text x="81" y="48" fill="#ffffff" font-size="13" font-weight="bold" text-anchor="middle">📤 تسليم المبيعات</text>
                    <text x="81" y="70" fill="#6ee7b7" font-size="11" text-anchor="middle">خصم من المخزن (- Out)</text>
                    <rect x="168" y="18" width="142" height="74" rx="12" fill="#0c4a6e" stroke="#38bdf8" stroke-width="2.5"/>
                    <text x="239" y="48" fill="#ffffff" font-size="13" font-weight="bold" text-anchor="middle">📥 استلام المشتريات</text>
                    <text x="239" y="70" fill="#7dd3fc" font-size="11" text-anchor="middle">إضافة للمخزن (+ In)</text>
                </svg>`,
                targetSelector: '.sl-mode-switcher'
            },
            {
                stepBadge: 'الخطوة 2️⃣ — اختر الفاتورة أو امسح الباركود',
                title: 'اذهب للأذونات غير المستلمة واضغط على الإذن المطلوب',
                desc: 'اضغط على بطاقة (⏳ قيد الاستلام/التسليم) ثم اضغط بالماوس على الإذن من الجدول، أو مرر كاميرا الجوال على ورقة الإذن ليقرأ الباركود ويفتحه لك فوراً.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="12" y="14" width="200" height="82" rx="10" fill="#0f172a" stroke="#64748b" stroke-width="1.5"/>
                    <rect x="22" y="26" width="180" height="22" rx="5" fill="#1e293b"/>
                    <text x="112" y="41" fill="#38bdf8" font-size="11" font-weight="bold" text-anchor="middle">#1045 — باقي فيه 7 وحدات</text>
                    <rect x="22" y="56" width="82" height="26" rx="6" fill="#10b981"/>
                    <text x="63" y="73" fill="#fff" font-size="10" font-weight="bold" text-anchor="middle">⚡ اعتماد كامل</text>
                    <rect x="114" y="56" width="88" height="26" rx="6" fill="#0284c7"/>
                    <text x="158" y="73" fill="#fff" font-size="10" font-weight="bold" text-anchor="middle">✂️ تقسيم وفحص</text>
                    <rect x="226" y="14" width="82" height="82" rx="12" fill="#064e3b" stroke="#34d399" stroke-width="2"/>
                    <text x="267" y="52" fill="#fff" font-size="22" text-anchor="middle">📱</text>
                    <text x="267" y="76" fill="#a7f3d0" font-size="10" font-weight="bold" text-anchor="middle">مسح QR فوري</text>
                </svg>`,
                targetSelector: '#sl-registry-tbody'
            },
            {
                stepBadge: 'الخطوة 3️⃣ — اكتب الكمية المستلمة بالوحدة الأساسية أو الفرعية',
                title: 'الإذن باقي فيه 7؟ اكتب 5 أو 7 في المربع وسيحسب الباقي تلقائياً!',
                desc: 'أمام كل صنف مربعان متجاوران:\n• المربع الأيمن للوحدة الأساسية (طن / كرتون).\n• المربع الأيسر للوحدة الفرعية (سيخة / حبة).\nاكتب الكمية التي وصلت الآن، وسيحسب البرنامج المتبقي للدفعة القادمة أوتوماتيكياً.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="10" y="18" width="132" height="74" rx="10" fill="#06281f" stroke="#10b981" stroke-width="2"/>
                    <text x="76" y="42" fill="#6ee7b7" font-size="10" font-weight="bold" text-anchor="middle">📦 المتبقي 7 (نستلم 5)</text>
                    <rect x="36" y="52" width="80" height="28" rx="6" fill="#020617" stroke="#34d399"/>
                    <text x="76" y="71" fill="#fff" font-size="14" font-weight="bold" text-anchor="middle">5</text>
                    <text x="160" y="60" fill="#fbbf24" font-size="18" font-weight="bold" text-anchor="middle">⇄</text>
                    <rect x="178" y="18" width="132" height="74" rx="10" fill="#1e1b4b" stroke="#818cf8" stroke-width="2"/>
                    <text x="244" y="42" fill="#a5b4fc" font-size="10" font-weight="bold" text-anchor="middle">🔢 المعادل بالفرعية</text>
                    <rect x="204" y="52" width="80" height="28" rx="6" fill="#020617" stroke="#818cf8"/>
                    <text x="244" y="71" fill="#fff" font-size="14" font-weight="bold" text-anchor="middle">500</text>
                </svg>`,
                targetSelector: '#sl-items-grid'
            },
            {
                stepBadge: 'الخطوة 4️⃣ — اضغط اعتماد أو تراجع في أي وقت',
                title: 'اعتمد العملية بضغطة زر واحدة',
                desc: 'اضغط زر (🚀 تأكيد واعتماد الآن) ليتم تحديث المخزن فوراً.\nوإذا أردت التراجع، اضغط زر (↩️ إلغاء وعكس الحركة) بجانبه ليُرجع كل شيء كما كان.',
                svg: `<svg viewBox="0 0 320 110" width="100%" height="100%">
                    <rect x="8" y="25" width="304" height="60" rx="14" fill="#0f172a" stroke="#34d399" stroke-width="2"/>
                    <text x="235" y="52" fill="#cbd5e1" font-size="10" font-weight="bold" text-anchor="middle">المنفذ الآن: 5 | المتبقي: 2</text>
                    <text x="235" y="70" fill="#fbbf24" font-size="9" text-anchor="middle">تحديث لحظي</text>
                    <rect x="18" y="37" width="135" height="36" rx="10" fill="#10b981"/>
                    <text x="85" y="60" fill="#ffffff" font-size="11" font-weight="bold" text-anchor="middle">🚀 تأكيد واعتماد الآن</text>
                </svg>`,
                targetSelector: '.sl-floating-summary'
            }
        ],

        buttonsDict: [
            {
                label: '📤 منظومة المبيعات وتقسيمها وتسليمها (GDN)',
                style: 'background:linear-gradient(135deg,#064e3b,#047857); color:#fff; border:1.5px solid #34d399;',
                whatItDoes: 'يحول الشاشة إلى قسم تسليم بضائع المبيعات للعملاء (باللون الأخضر الزمردي).',
                whatHappensIfClicked: 'يعرض فواتير المبيعات فقط، وعند الاعتماد يقوم بـ (خصم الكميات من المخزن) وإصدار إذن صرف بوابة (GDN).',
                targetSelector: '#sl-tab-sales'
            },
            {
                label: '📥 منظومة المشتريات وتقسيمها واستلامها (GRN)',
                style: 'background:linear-gradient(135deg,#0c4a6e,#4338ca); color:#fff; border:1.5px solid #38bdf8;',
                whatItDoes: 'يحول الشاشة إلى قسم استلام بضائع المشتريات من الموردين (باللون الأزرق الملكي).',
                whatHappensIfClicked: 'يعرض فواتير المشتريات فقط، وعند الاعتماد يقوم بـ (إضافة الكميات إلى المخزن) وإصدار مذكرة استلام (GRN).',
                targetSelector: '#sl-tab-purchases'
            },
            {
                label: '🛡️ حارس الـ QR (ذكي تلقائي / قفل صارم)',
                style: 'background:#0f172a; color:#38bdf8; border:1.5px solid #38bdf8;',
                whatItDoes: 'يحمي العامل من الخلط بين أوراق المبيعات وأوراق المشتريات أثناء المسح بالكاميرا.',
                whatHappensIfClicked: '• (🤖 ذكي تلقائي): يتعرف على نوع الباركود ويفتح قسمه أوتوماتيكياً.\n• (🔒 قفل صارم): يرفض مسح أي ورقة لا تخص القسم المفتوح حالياً وينبهك بصوت تحذير.',
                targetSelector: '#sl-qr-guard-mode'
            },
            {
                label: '📱 ربط كاميرا الجوال (المسح المتتابع لـ 10+ أذونات)',
                style: 'background:linear-gradient(135deg,#059669,#0284c7); color:#fff; border:1.5px solid #34d399;',
                whatItDoes: 'يربط كاميرا جوالك بالكمبيوتر بمسحة واحدة لتعمل كقارئ باركود ليزري سريع.',
                whatHappensIfClicked: 'تمرر 10 أذونات وراء بعضها أمام كاميرا الجوال فيتم استلامها أو تسليمها فوراً بدون ضغط أي زر.',
                targetSelector: 'button[onclick="openSlMobileBridgeModal()"]'
            },
            {
                label: '🌗 تقسيم نصف الكمية (50%) / ⚡ تعبئة الكل (100%) / 🧹 تصفير',
                style: 'background:rgba(56,189,248,0.2); color:#7dd3fc; border:1px solid #38bdf8;',
                whatItDoes: 'أزرار تحكم سريع في كميات جميع أصناف الفاتورة المفتوحة بضغطة واحدة.',
                whatHappensIfClicked: '• (100%): يملأ كل الخانات بكامل المتبقي.\n• (50%): يقسم الكميات إلى النصف.\n• (تصفير): يصفر جميع الخانات.',
                targetSelector: 'button[onclick="fillAllSlQuantities(0.5)"]'
            },
            {
                label: '↩️ إلغاء وعكس الحركة المخزنية',
                style: 'background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid #f87171;',
                whatItDoes: 'يلغي العملية التي اعتمدتها ويعيد المخزن والفاتورة إلى حالتهما السابقة تماماً.',
                whatHappensIfClicked: 'بضغطة واحدة على هذا الزر يتم التراجع عن الصرف أو الاستلام وإرجاع الرصيد فوراً.',
                targetSelector: '#sl-btn-undo'
            }
        ],

        scenarios: [
            {
                q: '💡 الفاتورة فيها 10 وحدات، استلمنا سابقاً 3 وبقي فيها 7، واليوم وصلت 5 فقط، كيف أتصرف؟',
                a: '1. اضغط على الفاتورة من جدول (⏳ قيد الإجراء).\n2. ستجد مكتوباً أمامك: (منفذ سابقاً: 3 | المتبقي: 7).\n3. اكتب في مربع الكمية رقم (5) ثم اضغط (🚀 تأكيد واعتماد).\n4. سيضيف البرنامج 5 للمخزن، ويحفظ أن المتبقي الآن (2) للمرة القادمة.'
            },
            {
                q: '💡 الفاتورة مكتوبة بالطن، لكن العامل عدّ البضاعة بالسيخة (أو الحبة)، كيف يدخلها بدون آلة حاسبة؟',
                a: 'بجانب مربع الطن يوجد مربع ثانٍ مكتوب عليه (🔢 بالـ سيخة / الحبة). اكتب عدد الأسياخ أو الحبات فيه مباشرة، وسيقوم البرنامج بتحويلها إلى الطن تلقائياً.'
            },
            {
                q: '💡 الفاتورة فيها صنفان، أريد وضع الصنف الأول في «المخزن الرئيسي» والصنف الثاني في «مخزن الفرع»؟',
                a: 'في الجدول السفلي، أمام كل سطر توجد قائمة (🏢 المستودع) خاصة بذلك السطر وحده. اختر للسطر الأول (المخزن الرئيسي) وللسطر الثاني (مخزن الفرع) ثم اضغط اعتماد.'
            }
        ]
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

    // Dedicated Sales Steps (شرح منظومة المبيعات وتقسيمها فقط - GDN)
    const SALES_ONLY_STEPS = [
        {
            badge: 'مبيعات 1️⃣ — فتح قسم تسليم المبيعات (GDN)',
            cursorLabel: 'نضغط هنا لقسم المبيعات 📤',
            targetSelector: '#sl-tab-sales',
            speechByDialect: {
                egyptian: () => `${SHORT_SAHIHAYN_SALAWAT} يا أهلاً بحضرتك يا فندم في شرح قسم المبيعات وتقسيمها. علشان نسلّم بضاعة لعميل ونخصمها من المخزن، بنضغط الأول على زرار منظومة المبيعات الأخضر اللي فوق ده.`,
                sudanese: () => `${SHORT_SAHIHAYN_SALAWAT} حبابك عشرة في شرح قسم المبيعات وتقسيمها. عشان تسلم بضاعة لعميل وتخصمها من المخزن، تضغط أول حاجة على زر منظومة المبيعات الأخضر الفوق ده.`,
                gulf:     () => `${SHORT_SAHIHAYN_SALAWAT} يا هلا والله في شرح قسم المبيعات وتقسيمها. عشان نسلم بضاعة لعميل ونخصمها من المستودع، نضغط أول شيء على زر منظومة المبيعات الأخضر في أعلى الشاشة.`
            },
            speech: () => `${SHORT_SAHIHAYN_SALAWAT} لتسليم بضاعة المبيعات للعميل، نضغط أولاً على زر منظومة المبيعات الأخضر في أعلى الشاشة.`,
            action: () => {
                if (typeof window.switchLogisticsMode === 'function') {
                    window.switchLogisticsMode('sales', true);
                }
            }
        },
        {
            badge: 'مبيعات 2️⃣ — عرض فواتير المبيعات قيد التسليم',
            cursorLabel: 'الأذونات غير المسلمة ⏳',
            targetSelector: '#sl-kpi-card-pending',
            speechByDialect: {
                egyptian: () => `وبعد كده على طول، بنضغط على كارت الأذونات اللي لسه ما اتسلمتش، علشان تفتح قدامنا كل فواتير العملاء اللي مستنية التسليم.`,
                sudanese: () => `وبعد داك، تضغط طوالي على بطاقة الأذونات الما مستلمة، عشان تظهر قدامك فواتير العملاء الراجية التسليم.`,
                gulf:     () => `ومباشرة، نضغط على بطاقة الأذونات قيد التسليم، عشان تطلع قدامنا فواتير العملاء الجاهزة للصرف.`
            },
            speech: () => `نضغط على بطاقة الأذونات قيد التسليم لتظهر فواتير المبيعات الجاهزة للصرف.`,
            action: () => {
                if (typeof window.setSlRegistryFilter === 'function') {
                    window.setSlRegistryFilter('pending');
                }
                window.ensureLiveDemoInvoiceForGuide();
            }
        },
        {
            badge: 'مبيعات 3️⃣ — فتح فاتورة العميل وتقسيم الكمية المسلمة',
            cursorLabel: 'نفتح الفاتورة ونكتب المسلم الآن (5) ✍️',
            targetSelector: '.sl-qty-primary-input',
            speechByDialect: {
                egyptian: () => `بنضغط على فاتورة العميل من الجدول فتفتح معانا تحت، ولو الفاتورة باقي فيها سبعة والعميل هيستلم النهارده خمسة بس، بنكتب خمسة في مربع الكمية، والبرنامج بيحسب لوحده إن فاضل اتنين للدفعة الجاية.`,
                sudanese: () => `تضغط على فاتورة العميل من الجدول وبتفتح معاك تحت، ولو باقي فيها سبعة والعميل داير يستلم خمسة بس، بنكتب خمسة في مربع الكمية، والبرنامج براهو بيحسب إنو الباقي اتنين.`,
                gulf:     () => `نضغط على فاتورة العميل من الجدول فتفتح بالأسفل، وإذا باقي فيها سبعة والعميل بيستلم اليوم خمسة بس، نكتب خمسة في مربع الكمية، والبرنامج يحسب تلقائياً أنه باقي اثنين.`
            },
            speech: () => `نفتح فاتورة العميل ونكتب الكمية المسلمة الآن في مربع الكمية، فيحسب البرنامج المتبقي تلقائياً.`,
            action: () => {
                const firstRow = document.querySelector('#sl-registry-tbody tr:first-child');
                if (firstRow) firstRow.click();
                setTimeout(() => {
                    const inp = document.querySelector('.sl-qty-primary-input');
                    if (inp) {
                        inp.value = '5';
                        if (typeof window.onSlPrimaryQtyInput === 'function') window.onSlPrimaryQtyInput(inp, true);
                    }
                }, 150);
            }
        },
        {
            badge: 'مبيعات 4️⃣ — اعتماد إذن الصرف (GDN) أو التراجع',
            cursorLabel: 'اعتماد الصرف 🚀 أو التراجع ↩️',
            targetSelector: '#sl-btn-execute',
            speechByDialect: {
                egyptian: () => `وفي الآخر خالص، بنضغط على زرار تأكيد واعتماد، علشان الكمية تتخصم من المخزن ويطلع إذن تسليم بوابة للعميل، وجنبه دايماً زرار إلغاء وعكس الحركة لو حبيت ترجع.`,
                sudanese: () => `وفي النهاية، تضغط على زر تأكيد واعتماد، عشان الكمية تتخصم من المخزن ويطلع إذن تسليم للعميل، وجنبو دايماً زر إلغاء وعكس الحركة لو داير تتراجع.`,
                gulf:     () => `وبالأخير، نضغط على زر تأكيد واعتماد، لخصم الكمية من المستودع وإصدار إذن فسح بوابة للعميل، وبجانبه زر إلغاء وعكس الحركة للتراجع.`
            },
            speech: () => `وأخيراً نضغط على زر تأكيد واعتماد لخصم الكمية من المخزن وإصدار إذن التسليم.`,
            action: () => {}
        }
    ];

    function buildUniversalScreenGuideIfNeeded() {
        if (document.getElementById('sl-tab-purchases') || document.getElementById('sl-tab-sales')) {
            return 'smart-logistics';
        }
        const pageTitle = (document.querySelector('h1, h2, .page-title')?.textContent || document.title || 'الشاشة الحالية').trim();
        const visibleBtns = Array.from(document.querySelectorAll('button, a.btn, .sl-btn')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 40 && r.height > 20 && r.top >= 0 && !b.closest('#msg-universal-fixed-launcher') && !b.closest('#msg-universal-modal');
        }).slice(0, 6);

        window.MagaiesSmartGuides['universal-screen'] = {
            title: `المرشد الذكي: ${pageTitle}`,
            subtitle: 'شرح تفاعلي مباشر للشاشة الحالية وأزرارها بالصوت الرجالي الواضح',
            autopilotSteps: visibleBtns.length ? visibleBtns.map((btn, i) => {
                const lbl = (btn.innerText || btn.getAttribute('title') || `العنصر ${i + 1}`).replace(/\s+/g, ' ').trim().slice(0, 45);
                if (!btn.id) btn.id = `msg-auto-el-${i}`;
                return {
                    badge: `الخطوة ${i + 1} — ${lbl}`,
                    cursorLabel: lbl,
                    targetSelector: `#${btn.id}`,
                    speech: () => `${ i === 0 ? SHORT_SAHIHAYN_SALAWAT + ' ' : '' }هذا الزر مخصص لـ ${lbl}، يمكنك الضغط عليه لتنفيذ العملية مباشرة على الشاشة.`
                };
            }) : [
                {
                    badge: 'الخطوة 1 — نظرة عامة على الشاشة',
                    cursorLabel: pageTitle,
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
            buttonsDict: visibleBtns.map(b => {
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

                <!-- 4 CLEAR EXPLANATION MODES: FULL SCREEN vs PURCHASES vs SALES vs SPECIFIC ELEMENT -->
                <div class="msg-mode-selector-bar" style="flex-shrink:0;">
                    <button type="button" class="msg-mode-card-btn" onclick="window.startLiveAutopilotSimulation('all')" style="border-color:#10b981; background:linear-gradient(135deg,rgba(6,78,59,0.75),rgba(15,23,42,0.92));">
                        <span style="font-size:1.4rem;">🌐</span>
                        <div>
                            <div style="font-weight:900; font-size:0.84rem; color:#6ee7b7;">1. اشرح الشاشة كلها بالكامل</div>
                            <div style="font-size:0.71rem; color:#cbd5e1;">جولة كاملة بدون تغطية الشاشة</div>
                        </div>
                    </button>
                    <button type="button" class="msg-mode-card-btn" onclick="window.startLiveAutopilotSimulation('purchases')" style="border-color:#38bdf8; background:linear-gradient(135deg,rgba(12,74,110,0.75),rgba(15,23,42,0.92));">
                        <span style="font-size:1.4rem;">📥</span>
                        <div>
                            <div style="font-weight:900; font-size:0.84rem; color:#7dd3fc;">2. اشرح منظومة المشتريات وتقسيمها</div>
                            <div style="font-size:0.71rem; color:#cbd5e1;">استلام البضاعة وتقسيم الدفعات (GRN)</div>
                        </div>
                    </button>
                    <button type="button" class="msg-mode-card-btn" onclick="window.startLiveAutopilotSimulation('sales')" style="border-color:#34d399; background:linear-gradient(135deg,rgba(4,120,87,0.65),rgba(15,23,42,0.92));">
                        <span style="font-size:1.4rem;">📤</span>
                        <div>
                            <div style="font-weight:900; font-size:0.84rem; color:#a7f3d0;">3. اشرح منظومة المبيعات وتقسيمها</div>
                            <div style="font-size:0.71rem; color:#cbd5e1;">تسليم بضاعة العميل وتقسيمها (GDN)</div>
                        </div>
                    </button>
                    <button type="button" class="msg-mode-card-btn" onclick="window.startSmartPickElementMode()" style="border-color:#fbbf24; background:linear-gradient(135deg,rgba(120,53,15,0.65),rgba(15,23,42,0.92));">
                        <span style="font-size:1.4rem;">👆</span>
                        <div>
                            <div style="font-weight:900; font-size:0.84rem; color:#fde047;">4. اشرح جزءاً معيناً أضغط عليه</div>
                            <div style="font-size:0.71rem; color:#cbd5e1;">اضغط على أي زر في الشاشة ليشرحه وحده</div>
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

        // ── SLEEK COMPACT NON-BLOCKING FLOATING STUDIO CAPSULE ──
        const coachBar = document.createElement('div');
        coachBar.id = 'msg-live-coach-bar';
        coachBar.className = 'msg-live-coach-bar';
        coachBar.innerHTML = `
            <!-- TOP SLIM HEADER ROW (ALWAYS VISIBLE EVEN WHEN MINIMIZED) -->
            <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                    <button type="button" id="msg-btn-pause-resume" onclick="window.togglePauseResumeAudio()" class="sl-btn" style="padding:4px 10px; font-size:0.76rem; background:linear-gradient(135deg,#f59e0b,#d97706); color:#020617; border:none; font-weight:900; border-radius:99px;">
                        ⏸️ إيقاف مؤقت
                    </button>
                    <button type="button" onclick="window.seekAudioBy(-3)" class="sl-btn" style="padding:3px 7px; font-size:0.72rem; color:#bae6fd; border-color:#38bdf8; border-radius:99px;" title="تأخير 3 ثوانٍ">⏪ 3ث</button>
                    <div style="display:inline-flex; align-items:center; gap:5px; background:rgba(15,23,42,0.9); padding:3px 8px; border-radius:99px; border:1px solid rgba(56,189,248,0.35);" dir="ltr">
                        <input type="range" id="msg-audio-seek-slider" min="0" max="100" step="0.1" value="0" oninput="window.seekAudioTo(this.value)" style="width:74px; height:5px; accent-color:#fbbf24; cursor:pointer;" title="تقديم أو تأخير التسجيل">
                        <span id="msg-audio-time-lbl" style="font-size:0.7rem; font-weight:900; color:#fef08a; font-family:monospace; white-space:nowrap;">00:00</span>
                    </div>
                    <button type="button" onclick="window.seekAudioBy(3)" class="sl-btn" style="padding:3px 7px; font-size:0.72rem; color:#bae6fd; border-color:#38bdf8; border-radius:99px;" title="تقديم 3 ثوانٍ">3ث ⏩</button>
                    <button type="button" onclick="window.stopLiveAutopilotSimulation()" class="sl-btn" style="padding:4px 10px; font-size:0.75rem; background:linear-gradient(135deg,#ef4444,#dc2626); color:#ffffff; border:1px solid #fca5a5; font-weight:900; border-radius:99px;" title="إيقاف الشرح نهائياً">
                        ⏹️ إنهاء
                    </button>
                </div>

                <!-- COMPACT WINDOW CONTROLS: TEXT TOGGLE + TOP/BOTTOM + MINIMIZE -->
                <div style="display:flex; align-items:center; gap:4px;">
                    <button type="button" onclick="window.toggleCoachSubtitleBox()" class="sl-btn" style="padding:3px 8px; font-size:0.71rem; color:#6ee7b7; border-color:rgba(52,211,153,0.4); border-radius:99px;" title="إظهار أو إخفاء النص المكتوب">💬 النص</button>
                    <button type="button" id="msg-coach-pos-btn" onclick="window.toggleCoachCapsulePosition()" class="sl-btn" style="padding:3px 8px; font-size:0.71rem; color:#bae6fd; border-color:rgba(56,189,248,0.4); border-radius:99px;" title="نقل الكبسولة لأعلى أو أسفل الشاشة">⬆️ للأعلى</button>
                    <button type="button" id="msg-coach-min-btn" onclick="window.toggleCoachCapsuleMinimize()" class="sl-btn" style="padding:3px 8px; font-size:0.71rem; color:#fef08a; border-color:rgba(251,191,36,0.45); border-radius:99px;" title="تصغير الكبسولة لعدم تغطية أي جزء من الشاشة">🗕 تصغير</button>
                </div>
            </div>

            <!-- COLLAPSIBLE COMPACT SETTINGS & SUBTITLE BODY -->
            <div class="msg-coach-full-body" style="display:flex; flex-direction:column; gap:5px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:5px; flex-wrap:wrap; border-top:1px solid rgba(148,163,184,0.18); padding-top:5px;">
                    <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                        <span id="msg-coach-step-badge" style="background:rgba(251,191,36,0.18); border:1px solid #fbbf24; color:#fef08a; padding:2px 9px; border-radius:99px; font-size:0.72rem; font-weight:900; max-width:210px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">الشرح الحي</span>
                        <select id="msg-coach-mode-select" class="sl-input" onchange="window.onCoachModeSelectChange(this.value)" style="padding:2px 7px !important; font-size:0.72rem !important; font-weight:900 !important; height:auto; border-radius:99px !important;" title="نوع التسجيل والشرح">
                            <option value="all">🌐 شرح الكل</option>
                            <option value="purchases">📥 المشتريات</option>
                            <option value="sales">📤 المبيعات</option>
                            <option value="pick">👆 جزء معين</option>
                        </select>
                        <select id="msg-coach-voice-select" class="sl-input" onchange="window.onMsgVoiceChange(this.value)" style="padding:2px 7px !important; font-size:0.72rem !important; font-weight:900 !important; height:auto; border-radius:99px !important;" title="تغيير اللهجة فوراً">
                            <option value="egyptian">🇪🇬 مصري</option>
                            <option value="sudanese">🇸🇩 سوداني</option>
                            <option value="gulf">🇸🇦 خليجي</option>
                        </select>
                        <div class="msg-speed-arrows-group" style="border-radius:99px; padding:1px 5px;">
                            <button type="button" class="msg-speed-arrow-btn" onclick="window.adjustSpeechSpeedBy(-0.08, false)" style="padding:1px 6px; font-size:0.7rem;" title="إبطاء الصوت">-</button>
                            <span id="msg-coach-speed-lbl" style="font-size:0.7rem; font-weight:900; color:#fef08a; min-width:48px; text-align:center;">${formatSpeedPctLabel(selectedSpeechRate)}</span>
                            <button type="button" class="msg-speed-arrow-btn" onclick="window.adjustSpeechSpeedBy(0.08, false)" style="padding:1px 6px; font-size:0.7rem;" title="تسريع الصوت">+</button>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:3px;">
                        <button type="button" onclick="window.stepLiveAutopilot(-1)" class="sl-btn" style="padding:2px 7px; font-size:0.7rem; border-radius:8px;" title="الخطوة السابقة">⏮️</button>
                        <div id="msg-step-pills-wrap" style="display:inline-flex; gap:3px;"></div>
                        <button type="button" onclick="window.stepLiveAutopilot(1)" class="sl-btn" style="padding:2px 7px; font-size:0.7rem; background:#38bdf8; color:#020617; border:none; font-weight:900; border-radius:8px;" title="الخطوة التالية">⏭️</button>
                    </div>
                </div>

                <div id="msg-coach-speech-text" style="font-size:0.82rem; font-weight:800; color:#f8fafc; line-height:1.5; background:rgba(15,23,42,0.82); padding:5px 11px; border-radius:10px; border-right:3px solid #34d399; max-height:46px; overflow-y:auto;"></div>
            </div>
        `;
        document.body.appendChild(coachBar);

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
        const nextRate = Math.max(0.70, Math.min(1.30, parseFloat((selectedSpeechRate + (parseFloat(delta) || 0)).toFixed(2))));
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

        selectedSpeechRate = Math.max(0.70, Math.min(1.30, parsed));
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
                ? `${SHORT_SAHIHAYN_SALAWAT} تم إبطاء سرعة الصوت إلى ${pct} بالمائة ليكون الشرح هادئاً ومتأنياً.`
                : `${SHORT_SAHIHAYN_SALAWAT} تم ضبط سرعة الصوت على ${pct} بالمائة.`;
            speakArabicMaleText(speedMsg, null, selectedMaleProfile);
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
            egyptian: `${SHORT_SAHIHAYN_SALAWAT} يا أهلاً بحضرتك يا فندم، تم تفعيل الصوت الرجالي الهادئ والواضح، باللهجة المصرية.`,
            sudanese: `${SHORT_SAHIHAYN_SALAWAT} حبابك عشرة، تم تفعيل الصوت الرجالي الهادئ والواضح، باللهجة السودانية.`,
            gulf:     `${SHORT_SAHIHAYN_SALAWAT} يا هلا والله، تم تفعيل الصوت الرجالي الهادئ والواضح، باللهجة الخليجية.`
        };
        speakArabicMaleText(greetings[profileId] || greetings.egyptian, null, profileId);
    };

    window.openSmartGuide = function (guideKey) {
        ensureGuideDomElements();
        window.stopLiveAutopilotSimulation();
        const resolvedKey = guideKey || buildUniversalScreenGuideIfNeeded();
        currentGuideKey = resolvedKey;
        const data = window.MagaiesSmartGuides[resolvedKey] || window.MagaiesSmartGuides['smart-logistics'];
        if (!data) return;

        prewarmGuideAudio(resolvedKey, selectedMaleProfile);

        document.getElementById('msg-hdr-title').textContent = data.title;
        document.getElementById('msg-hdr-sub').textContent = data.subtitle;

        // Tab 1
        const storyPane = document.getElementById('msg-pane-story');
        storyPane.innerHTML = `
            <div class="msg-story-grid">
                ${(data.visualSteps || []).map(st => `
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
                            <span class="msg-replica-badge" style="${b.style || ''}">${b.label}</span>
                            ${b.targetSelector ? `<button type="button" onclick="window.pointGhostCursorAt('${b.targetSelector.replace(/'/g, "\\'")}', '${b.label.replace(/'/g, "\\'")}', \`${(b.whatItDoes + ' ' + b.whatHappensIfClicked).replace(/`/g, '')}\`)" class="sl-btn" style="padding:3px 10px; font-size:0.74rem; color:#38bdf8;">🔊 اشرح هذا الزر وحده على الشاشة</button>` : ''}
                        </div>
                        <div style="font-size:0.84rem; font-weight:800; color:#ffffff;">🔹 <b>وظيفته:</b> ${b.whatItDoes}</div>
                        <div class="msg-whatif-box">⚡ <b>لو ضغطت عليه ماذا يحدث؟</b><br>${b.whatHappensIfClicked.replace(/\n/g, '<br>')}</div>
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
        const dialectTxt = adaptTextToDialect(txt, selectedMaleProfile);
        speakArabicMaleText(`${SHORT_SAHIHAYN_SALAWAT} ${dialectTxt}`, null, selectedMaleProfile);
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

    function moveGhostCursorToElement(el, labelText, triggerClickEffect = true) {
        if (!el) return;
        currentTargetDomEl = el;
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
        const adaptedBody = adaptTextToDialect(`${title}. ${spokenText}`, selectedMaleProfile);
        const fullSpeech = `${SHORT_SAHIHAYN_SALAWAT} ${adaptedBody}`;
        if (badge) badge.textContent = `📍 شرح جزء محدد: ${title}`;
        if (speechBox) speechBox.textContent = fullSpeech;

        if (el) moveGhostCursorToElement(el, title, true);
        speakArabicMaleText(fullSpeech, null, selectedMaleProfile);
    };

    // ── 4 EXPLANATION MODES: 'all' | 'purchases' | 'sales' | 'pick' ──────────
    window.startLiveAutopilotSimulation = async function (mode = 'all') {
        ensureGuideDomElements();
        window.stopSmartPickElementMode();
        const resolvedKey = buildUniversalScreenGuideIfNeeded();
        currentGuideKey = resolvedKey;
        const data = window.MagaiesSmartGuides[resolvedKey] || window.MagaiesSmartGuides['smart-logistics'];
        if (!data) return;

        if (resolvedKey === 'smart-logistics') {
            if (mode === 'sales') {
                activeAutopilotStepsList = SALES_ONLY_STEPS;
            } else if (mode === 'purchases') {
                activeAutopilotStepsList = data.autopilotSteps;
            } else {
                // 'all' = Full Purchases & Split tour + Sales overview step at the end
                activeAutopilotStepsList = [...data.autopilotSteps, SALES_ONLY_STEPS[0]];
            }
        } else {
            activeAutopilotStepsList = data.autopilotSteps || [];
        }

        isAutopilotRunning = true;
        window.closeSmartGuide();
        autopilotStepIdx = 0;

        const coachBar = document.getElementById('msg-live-coach-bar');
        if (coachBar) coachBar.style.display = 'flex';

        renderStepPillsForActiveList();
        prewarmGuideAudio(currentGuideKey, selectedMaleProfile);
        runCurrentAutopilotStep();
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

    function runCurrentAutopilotStep() {
        if (!isAutopilotRunning || !activeAutopilotStepsList.length) return;

        const step = activeAutopilotStepsList[autopilotStepIdx];
        if (!step) return;

        renderStepPillsForActiveList();

        // Pre-decode the NEXT step in advance while the current step is playing!
        const nextStep = activeAutopilotStepsList[autopilotStepIdx + 1];
        if (nextStep) {
            const nextTxt = resolveStepSpeech(nextStep, selectedMaleProfile);
            fetchAndDecodeMaleBuffer(nextTxt, selectedMaleProfile).catch(() => {});
        }

        const badge = document.getElementById('msg-coach-step-badge');
        const speechBox = document.getElementById('msg-coach-speech-text');
        const spoken = resolveStepSpeech(step, selectedMaleProfile);

        if (badge) badge.textContent = `${step.badge} (${autopilotStepIdx + 1} من ${activeAutopilotStepsList.length})`;
        if (speechBox) speechBox.textContent = spoken;

        // Execute step DOM action first if needed, then move cursor smoothly without jitter!
        if (typeof step.action === 'function') {
            try { step.action(); } catch (e) {}
        }

        setTimeout(() => {
            if (!isAutopilotRunning) return;
            const el = document.querySelector(step.targetSelector) || document.body;
            moveGhostCursorToElement(el, step.cursorLabel, true);
        }, 60);

        speakArabicMaleText(spoken, () => {
            if (!isAutopilotRunning || isAudioPaused) return;
            nextStepTimeoutId = setTimeout(() => {
                if (isAutopilotRunning && !isAudioPaused) window.stepLiveAutopilot(1);
            }, 220);
        }, selectedMaleProfile);
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
        if (!isPickElementModeActive) return;
        if (e.target.closest('#msg-pick-mode-banner') || e.target.closest('#msg-universal-fixed-launcher') || e.target.closest('#msg-live-coach-bar') || e.target.closest('#msg-pinned-audio-dock')) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();

        const clickedEl = e.target.closest('button, a, select, input, .sl-kpi-card, tr, th, .sl-mode-btn, [onclick]') || e.target;
        window.stopSmartPickElementMode();
        explainPickedDomElement(clickedEl);
    }, true);

    function explainPickedDomElement(el) {
        if (!el) return;
        const data = window.MagaiesSmartGuides['smart-logistics'];
        let matchedTitle = '';
        let matchedDesc = '';

        if (data && Array.isArray(data.buttonsDict)) {
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
                matchedTitle = 'قسم استلام المشتريات وتقسيمها (GRN)';
                matchedDesc = 'يفتح منظومة استلام البضاعة من الموردين، ويمكنك استلام الفاتورة كاملة أو تقسيمها على دفعات وإضافتها للمخزن.';
            } else if (el.id === 'sl-tab-sales' || el.closest('#sl-tab-sales')) {
                matchedTitle = 'قسم تسليم المبيعات وتقسيمها (GDN)';
                matchedDesc = 'يفتح منظومة تسليم البضاعة للعملاء، ويمكنك صرف الفاتورة كاملة أو تقسيمها على دفعات وخصمها من المخزن.';
            } else if (el.classList.contains('sl-qty-primary-input') || el.classList.contains('sl-qty-base-input')) {
                matchedTitle = 'مربع إدخال وتقسيم الكمية المستلمة أو المسلمة';
                matchedDesc = 'تكتب هنا الكمية التي استلمتها أو سلمتها الآن فقط، وسيحسب البرنامج الباقي للدفعة القادمة أوتوماتيكياً.';
            } else {
                matchedDesc = `عند الضغط على (${matchedTitle})، يقوم النظام بتنفيذ هذه الوظيفة مباشرة داخل الشاشة.`;
            }
        }

        isAutopilotRunning = true;
        const coachBar = document.getElementById('msg-live-coach-bar');
        const speechBox = document.getElementById('msg-coach-speech-text');
        const badge = document.getElementById('msg-coach-step-badge');
        if (coachBar) coachBar.style.display = 'flex';

        const adapted = adaptTextToDialect(`${matchedTitle}. ${matchedDesc}`, selectedMaleProfile);
        const fullTxt = `${SHORT_SAHIHAYN_SALAWAT} ${adapted}`;
        if (badge) badge.textContent = `👆 شرح الجزء المختار: ${matchedTitle}`;
        if (speechBox) speechBox.textContent = fullTxt;

        moveGhostCursorToElement(el, matchedTitle, true);
        speakArabicMaleText(fullTxt, null, selectedMaleProfile);
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

        const adaptedAns = adaptTextToDialect(answerText, selectedMaleProfile);
        const spokenAns = `${SHORT_SAHIHAYN_SALAWAT} ${adaptedAns}`;

        if (ansBox) {
            ansBox.style.display = 'block';
            ansBox.innerHTML = `<div style="color:#fde047; font-weight:900; margin-bottom:4px;">💡 الإجابة المباشرة على الشاشة:</div>${adaptedAns}`;
        }

        const el = document.querySelector(targetSel) || document.body;
        moveGhostCursorToElement(el, cursorTitle, true);
        speakArabicMaleText(spokenAns, null, selectedMaleProfile);
    };

    window.stopLiveAutopilotSimulation = function () {
        isAutopilotRunning = false;
        stopCurrentSpeech();

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
    };

    // Inject the fixed square launcher immediately on DOM ready and pre-warm audio!
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureFixedSquareLauncher);
    } else {
        ensureFixedSquareLauncher();
    }

    window.addEventListener('load', () => {
        ensureFixedSquareLauncher();
        setTimeout(() => prewarmGuideAudio('smart-logistics', selectedMaleProfile), 800);
    });
})();
