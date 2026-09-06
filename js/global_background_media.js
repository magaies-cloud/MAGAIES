/**
 * ════════════════════════════════════════════════════════════════════════════
 * 📡 GLOBAL CROSS-SCREEN BACKGROUND MEDIA & TV CHANNELS ENGINE (v2026.3)
 * ════════════════════════════════════════════════════════════════════════════
 * Al-Sajana Smart ERP 2026 - Global Islamic Live Streaming & Quran Engine
 * Keeps live TV & Quran MP3 playing continuously across ALL ERP screens
 * (Sales, Purchases, Inventory, Vouchers, General Ledger, Reports, Tools, etc.)
 * 
 * KEY ARCHITECTURAL FEATURES:
 * 1. SEAMLESS NAVIGATION CONTINUITY: Quran recitations DO NOT restart from 0:00!
 *    The audio pre-seeks to exact second BEFORE playing to prevent verse 1 leak.
 * 2. TRANSIENT ZERO PROTECTION: Never overwrites valid playback time with 0s.
 * 3. MULTI-TENANT PROXY COMPATIBILITY: Handles globalKeys and tenant-prefixed storage.
 * 4. UNIVERSAL CROSS-SCREEN COVERAGE: Works on every single screen, including
 *    headerless sub-screens (via dynamic floating TV pill widget).
 * 5. DUAL-LAYER AUTO BOOTSTRAP: Self-contained styles & popovers injected on demand.
 * 6. LIGHTNING DEFENSIVE SYNCHRONIZATION: Cross-tab BroadcastChannel & LocalStorage.
 * ════════════════════════════════════════════════════════════════════════════
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'elsajana_global_media_state';
    const BROADCAST_CHANNEL_NAME = 'elsajana_media_broadcast';

    const CHANNELS_INFO = {
        quran_mp3: {
            title: "القرآن الكريم MP3 - تلاوات خاشعة لكبار القراء",
            sub: "تلاوات عطرة متواصلة في الخلفية",
            audioUrl: "https://backup.qurango.net/radio/tarateel",
            icon: "headphones",
            color: "#06b6d4"
        },
        majd: {
            title: "تلفزيون وإذاعة المجد للقرآن الكريم (بث مباشر)",
            sub: "تلاوات عطرة خاشعة متواصلة 24/7",
            audioUrl: "https://backup.qurango.net/radio/tarateel",
            icon: "tv",
            color: "#f59e0b"
        },
        makkah: {
            title: "تلفزيون مكة المكرمة - الحرم المكي الشريف",
            sub: "بث حي ومباشر من بيت الله الحرام 24/7",
            audioUrl: "https://backup.qurango.net/radio/maher",
            icon: "kaaba",
            color: "#eab308"
        },
        madinah: {
            title: "تلفزيون المدينة المنورة - المسجد النبوي",
            sub: "بث حي ومباشر من الروضة الشريفة 24/7",
            audioUrl: "https://backup.qurango.net/radio/ali_alhuthaifi",
            icon: "mosque",
            color: "#10b981"
        }
    };

    let broadcastChannel = null;
    try {
        if (typeof window.BroadcastChannel !== 'undefined') {
            broadcastChannel = new BroadcastChannel(BROADCAST_CHANNEL_NAME);
            broadcastChannel.onmessage = handleBroadcastMessage;
        }
    } catch (e) {}

    // ════════════════════════════════════════════════════════════════════════
    // 🎨 Self-Contained Styles for Media Dock & Header Controls (Cross-Screen)
    // ════════════════════════════════════════════════════════════════════════
    function injectDockStyles() {
        if (document.getElementById('pm-dock-styles')) return;

        const style = document.createElement('style');
        style.id = 'pm-dock-styles';
        style.textContent = `
            /* Background Audio Container (Hidden from view, never blocks screen) */
            #persistent-media-dock {
                display: none !important;
                position: fixed !important;
                bottom: 18px !important;
                left: 18px !important;
                z-index: 9999999 !important;
                width: 380px !important;
                max-width: calc(100vw - 36px) !important;
                padding: 14px 16px !important;
                border-radius: 24px !important;
                background: rgba(10, 15, 30, 0.98) !important;
                border: 2px solid rgba(245, 158, 11, 0.75) !important;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.92), 0 0 35px rgba(245, 158, 11, 0.35) !important;
                backdrop-filter: blur(20px) !important;
                -webkit-backdrop-filter: blur(20px) !important;
                color: #f8fafc !important;
                font-family: 'Tajawal', system-ui, -apple-system, sans-serif !important;
                direction: rtl !important;
                text-align: right !important;
                box-sizing: border-box !important;
            }
            #persistent-media-dock.pm-hidden {
                display: none !important;
            }
            .pm-audio-player {
                width: 100% !important;
                height: 36px !important;
                border-radius: 12px !important;
                outline: none !important;
                background: #020617 !important;
            }
            .pm-prompt-resume {
                display: none;
                margin-top: 8px !important;
                background: linear-gradient(90deg, #b45309, #d97706) !important;
                border: 1px solid #fde047 !important;
                color: #ffffff !important;
                font-size: 11px !important;
                font-weight: 800 !important;
                padding: 6px 10px !important;
                border-radius: 10px !important;
                text-align: center !important;
                cursor: pointer !important;
                box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4) !important;
            }

            /* 🌟 Sleek Header & Sub-Screen Mini TV Button (1cm x 2.5cm) */
            .header-tv-mini-btn {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                height: 34px !important;
                padding: 4px 13px !important;
                border-radius: 12px !important;
                background: linear-gradient(135deg, rgba(30, 20, 50, 0.92), rgba(15, 23, 42, 0.98)) !important;
                border: 1.5px solid rgba(245, 158, 11, 0.45) !important;
                color: #f8fafc !important;
                font-family: 'Tajawal', system-ui, -apple-system, sans-serif !important;
                font-size: 12.5px !important;
                font-weight: 800 !important;
                cursor: pointer !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                position: relative !important;
                box-shadow: 0 4px 14px rgba(0,0,0,0.45) !important;
                white-space: nowrap !important;
                user-select: none !important;
                text-decoration: none !important;
            }
            .header-tv-mini-btn:hover {
                transform: translateY(-2px) scale(1.02) !important;
                border-color: #f59e0b !important;
                box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45) !important;
                color: #fef08a !important;
            }
            @keyframes header-tv-pulse-glow {
                0%, 100% {
                    box-shadow: 0 0 16px #f59e0b, 0 0 32px rgba(245, 158, 11, 0.8), inset 0 0 10px rgba(245, 158, 11, 0.4) !important;
                    border-color: #f59e0b !important;
                }
                50% {
                    box-shadow: 0 0 28px #fbbf24, 0 0 50px rgba(251, 191, 36, 0.95), inset 0 0 16px rgba(251, 191, 36, 0.7) !important;
                    border-color: #ffffff !important;
                }
            }
            .header-tv-mini-btn.is-playing {
                border-color: #f59e0b !important;
                background: linear-gradient(135deg, rgba(180, 83, 9, 0.92), rgba(15, 23, 42, 0.98)) !important;
                animation: header-tv-pulse-glow 1.8s infinite ease-in-out !important;
                color: #ffffff !important;
                font-weight: 900 !important;
            }
            .header-tv-mini-btn.is-playing .header-tv-icon {
                filter: drop-shadow(0 0 8px #fbbf24) !important;
            }
            .header-tv-icon {
                font-size: 15px !important;
                line-height: 1 !important;
            }
            .header-tv-label {
                font-weight: 800 !important;
                color: inherit !important;
            }
            .header-tv-status-dot {
                width: 8px !important;
                height: 8px !important;
                border-radius: 50% !important;
                background-color: #ef4444 !important;
                box-shadow: 0 0 8px #ef4444 !important;
                animation: headerTvPulse 1.4s infinite !important;
                display: inline-block !important;
                flex-shrink: 0 !important;
            }
            @keyframes headerTvPulse {
                0% { transform: scale(0.85); opacity: 1; }
                50% { transform: scale(1.3); opacity: 0.6; }
                100% { transform: scale(0.85); opacity: 1; }
            }

            /* 🌟 Floating Mini TV Button Bar for Headerless Sub-Screens (Alongside Go-Back Button) */
            .subscreen-floating-tv-wrap {
                position: fixed !important;
                top: 15px !important;
                left: 175px !important;
                z-index: 99999 !important;
                display: flex !important;
                align-items: center !important;
                direction: rtl !important;
                box-sizing: border-box !important;
            }
            .subscreen-floating-tv-wrap .header-tv-mini-btn {
                box-shadow: 0 8px 25px rgba(0,0,0,0.85), 0 0 15px rgba(245, 158, 11, 0.4) !important;
                border: 1.5px solid #f59e0b !important;
                backdrop-filter: blur(14px) !important;
                -webkit-backdrop-filter: blur(14px) !important;
            }
            @media (max-width: 640px) {
                .subscreen-floating-tv-wrap {
                    left: 120px !important;
                    top: 10px !important;
                }
            }

            /* 🌟 Universal Popover Modal (Always Rendered on Top of All Screens) */
            .header-tv-popover {
                position: fixed !important;
                width: 320px !important;
                max-width: calc(100vw - 30px) !important;
                background: linear-gradient(155deg, rgba(15, 23, 42, 0.99), rgba(5, 10, 24, 0.99)) !important;
                border: 2px solid #f59e0b !important;
                border-radius: 22px !important;
                padding: 16px !important;
                box-shadow: 0 25px 70px rgba(0,0,0,0.95), 0 0 45px rgba(245, 158, 11, 0.45) !important;
                backdrop-filter: blur(25px) !important;
                -webkit-backdrop-filter: blur(25px) !important;
                z-index: 999999999 !important;
                direction: rtl !important;
                text-align: right !important;
                box-sizing: border-box !important;
                animation: popoverFadeIn 0.22s ease-out !important;
                font-family: 'Tajawal', system-ui, -apple-system, sans-serif !important;
            }
            @keyframes popoverFadeIn {
                from { opacity: 0; transform: translateY(-6px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .header-pop-ch-btn {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                padding: 8px 10px !important;
                border-radius: 10px !important;
                background: rgba(255,255,255,0.06) !important;
                border: 1px solid rgba(255,255,255,0.14) !important;
                color: #f8fafc !important;
                font-size: 11.5px !important;
                font-weight: 800 !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                box-sizing: border-box !important;
                font-family: inherit !important;
            }
            .header-pop-ch-btn:hover {
                background: rgba(255,255,255,0.14) !important;
                border-color: var(--ch-c) !important;
                color: var(--ch-c) !important;
                transform: translateY(-1px) !important;
            }
            .header-pop-ch-btn.active-ch {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.28), rgba(15, 23, 42, 0.95)) !important;
                border-color: var(--ch-c) !important;
                box-shadow: 0 0 12px var(--ch-c) !important;
                color: #fff !important;
            }
        `;
        document.head.appendChild(style);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 🧱 Build or Ensure Persistent Audio Player in DOM
    // ════════════════════════════════════════════════════════════════════════
    let lastTimeSaveTimestamp = 0;

    function ensureMediaDock() {
        injectDockStyles();
        let dock = document.getElementById('persistent-media-dock');
        if (dock) {
            attachAudioEventListeners();
            return dock;
        }

        dock = document.createElement('div');
        dock.id = 'persistent-media-dock';
        dock.className = 'pm-hidden';

        dock.innerHTML = `
            <audio id="persistent-audio-player" class="pm-audio-player" preload="auto"></audio>
            <div id="pm-resume-prompt" class="pm-prompt-resume" onclick="resumeBackgroundPlayback()">
                🔊 انقر هنا لمواصلة استماع البث في الخلفية
            </div>
        `;

        if (document.body) {
            document.body.appendChild(dock);
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                if (!document.getElementById('persistent-media-dock')) {
                    document.body.appendChild(dock);
                }
            });
        }

        attachAudioEventListeners();
        return dock;
    }

    function attachAudioEventListeners() {
        const audioPlayer = document.getElementById('persistent-audio-player');
        if (!audioPlayer || audioPlayer.__listenersAttached) return;
        audioPlayer.__listenersAttached = true;

        // Continuous high-frequency playback position persistence (throttled ~600ms)
        audioPlayer.addEventListener('timeupdate', function() {
            const now = Date.now();
            if (now - lastTimeSaveTimestamp > 600) {
                lastTimeSaveTimestamp = now;
                saveGlobalMediaPlaybackPosition(audioPlayer);
            }
        });

        // Instant save on pause or seeking
        audioPlayer.addEventListener('pause', function() {
            saveGlobalMediaPlaybackPosition(audioPlayer);
        });
        audioPlayer.addEventListener('seeked', function() {
            saveGlobalMediaPlaybackPosition(audioPlayer);
        });

        // Audio track ended: reset position and clean up
        audioPlayer.addEventListener('ended', function() {
            const st = getStoredMediaState();
            if (st) {
                st.currentTime = 0;
                st.active = false;
                saveMediaState(st);
            }
            syncHeaderPopoverUI();
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    // 💾 Storage State Synchronization (Multi-Tenant Compatible)
    // ════════════════════════════════════════════════════════════════════════
    function getStoredMediaState() {
        try {
            let raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) {
                // Check if stored under tenant prefix (fallback)
                for (let i = 0; i < localStorage.length; i++) {
                    const k = localStorage.key(i);
                    if (k && k.endsWith(STORAGE_KEY)) {
                        raw = localStorage.getItem(k);
                        if (raw) break;
                    }
                }
            }
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function saveMediaState(state) {
        try {
            const json = JSON.stringify(state);
            localStorage.setItem(STORAGE_KEY, json);
            // Also sync to any existing tenant-prefixed keys for 100% backward safety
            try {
                for (let i = 0; i < localStorage.length; i++) {
                    const k = localStorage.key(i);
                    if (k && k.endsWith(STORAGE_KEY) && k !== STORAGE_KEY) {
                        localStorage.setItem(k, json);
                    }
                }
            } catch(e2) {}

            if (broadcastChannel) {
                broadcastChannel.postMessage({ type: 'STATE_CHANGED', state: state });
            }
        } catch (e) {}
    }

    function clearMediaState() {
        try {
            localStorage.removeItem(STORAGE_KEY);
            try {
                for (let i = 0; i < localStorage.length; i++) {
                    const k = localStorage.key(i);
                    if (k && k.endsWith(STORAGE_KEY)) {
                        localStorage.removeItem(k);
                    }
                }
            } catch(e2) {}

            if (broadcastChannel) {
                broadcastChannel.postMessage({ type: 'STOP_ALL' });
            }
        } catch (e) {}
    }

    // ════════════════════════════════════════════════════════════════════════
    // 💾 Continuous Position Persistence Engine (with Transient Zero Guard)
    // ════════════════════════════════════════════════════════════════════════
    function saveGlobalMediaPlaybackPosition(explicitAudio) {
        try {
            const audio = explicitAudio || document.getElementById('persistent-audio-player');
            if (!audio || !audio.src) return;

            const t = audio.currentTime;
            // 🛡️ GUARD: Only save positive time > 0.5s so we NEVER overwrite a valid state with transient 0 on initial page load!
            if (isNaN(t) || t <= 0.5) return;

            const state = getStoredMediaState();
            if (state && state.active && state.type === 'audio') {
                state.currentTime = t;
                state.duration = isNaN(audio.duration) ? 0 : audio.duration;
                state.lastSavedAt = Date.now();
                saveMediaState(state);
            }
        } catch (e) {}
    }
    window.saveGlobalMediaPlaybackPosition = saveGlobalMediaPlaybackPosition;

    // Instant save triggers on page transition events
    window.addEventListener('beforeunload', function() {
        saveGlobalMediaPlaybackPosition();
    }, true);

    window.addEventListener('pagehide', function() {
        saveGlobalMediaPlaybackPosition();
    }, true);

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            saveGlobalMediaPlaybackPosition();
        }
    });

    // Save before user clicks any anchor navigation link
    document.addEventListener('click', function(e) {
        const a = e.target.closest('a');
        if (a && a.href && !a.href.startsWith('javascript') && !a.href.startsWith('#')) {
            saveGlobalMediaPlaybackPosition();
        }
    }, true);

    function handleBroadcastMessage(event) {
        const msg = event.data;
        if (!msg) return;

        if (msg.type === 'STATE_CHANGED' && msg.state) {
            applyMediaState(msg.state, false);
        } else if (msg.type === 'STOP_ALL') {
            stopPersistentMedia(false);
        }
    }

    window.addEventListener('storage', function(e) {
        if (e.key && e.key.endsWith(STORAGE_KEY)) {
            try {
                const newState = e.newValue ? JSON.parse(e.newValue) : null;
                if (newState && newState.active) {
                    applyMediaState(newState, false);
                } else {
                    stopPersistentMedia(false);
                }
            } catch (err) {}
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // 🚀 Universal Floating TV Button & Popover Builder (for ALL Sub-Screens)
    // ════════════════════════════════════════════════════════════════════════
    function ensureHeaderMediaElements() {
        injectDockStyles();

        // 1. If button doesn't exist, inject floating pill on sub-screen
        if (!document.getElementById('header-media-btn')) {
            let wrap = document.getElementById('header-media-control-wrap');
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.id = 'header-media-control-wrap';
                wrap.className = 'subscreen-floating-tv-wrap';
                wrap.innerHTML = `
                    <button id="header-media-btn" type="button" onclick="toggleHeaderMediaPopover(event)" class="header-tv-mini-btn" title="قنوات التلفاز والقرآن الكريم (بث حي في الخلفية)">
                        <span id="header-media-icon" class="header-tv-icon">📺</span>
                        <span id="header-media-label" class="header-tv-label">التلفاز</span>
                        <span id="header-media-dot" class="header-tv-status-dot" style="display: none;"></span>
                    </button>
                `;

                if (document.body) {
                    document.body.appendChild(wrap);
                } else {
                    document.addEventListener('DOMContentLoaded', () => {
                        if (!document.getElementById('header-media-control-wrap')) {
                            document.body.appendChild(wrap);
                        }
                    });
                }
            }
        }

        // 2. If popover doesn't exist, build and append universal popover modal
        if (!document.getElementById('header-media-popover')) {
            let pop = document.createElement('div');
            pop.id = 'header-media-popover';
            pop.className = 'header-tv-popover';
            pop.style.display = 'none';
            pop.innerHTML = `
                <!-- Popover Header -->
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.12); padding-bottom: 8px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 13px; color: #fde047;">
                        <span id="popover-status-dot" class="header-tv-status-dot"></span>
                        <span>📺 تحكم البث المباشر (فوق الشاشة)</span>
                    </div>
                    <button type="button" onclick="closeHeaderMediaPopover(event)" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); color: #f8fafc; border-radius: 8px; width: 26px; height: 26px; font-size: 13px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" title="إخفاء النافذة (مع استمرار البث بالخلفية)">✕</button>
                </div>

                <!-- Prominent Current Broadcast Status Card -->
                <div id="popover-broadcast-status-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(15, 23, 42, 0.98)); border: 1.5px solid rgba(245, 158, 11, 0.55); border-radius: 14px; padding: 10px 12px; margin-bottom: 12px; box-shadow: inset 0 0 15px rgba(245, 158, 11, 0.15);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-size: 11px; font-weight: 800; color: #94a3b8;">📡 حالة البث الحالية:</span>
                        <span id="popover-status-badge" style="font-size: 10.5px; font-weight: 900; padding: 2px 8px; border-radius: 6px; background: rgba(16, 185, 129, 0.25); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.5);">
                            🟢 شغال الآن
                        </span>
                    </div>
                    <div id="popover-media-title" style="font-size: 13.5px; font-weight: 900; color: #ffffff; line-height: 1.35; text-shadow: 0 0 12px rgba(245, 158, 11, 0.6); word-break: break-word;">
                        البث المباشر يعمل بالخلفية
                    </div>
                </div>

                <!-- 4 Sacred Channel Quick Switch Buttons -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 7px; margin-bottom: 12px;">
                    <button type="button" id="hdr-ch-makkah" onclick="quickHeaderPlay('makkah')" class="header-pop-ch-btn" style="--ch-c: #f59e0b;" title="تشغيل تلفزيون مكة المكرمة">
                        <span style="font-size: 13px;">🕋</span>
                        <span>مكة المكرمة</span>
                    </button>
                    <button type="button" id="hdr-ch-madinah" onclick="quickHeaderPlay('madinah')" class="header-pop-ch-btn" style="--ch-c: #10b981;" title="تشغيل تلفزيون المدينة المنورة">
                        <span style="font-size: 13px;">🕌</span>
                        <span>المدينة المنورة</span>
                    </button>
                    <button type="button" id="hdr-ch-majd" onclick="quickHeaderPlay('majd')" class="header-pop-ch-btn" style="--ch-c: #fbbf24;" title="تشغيل تلفزيون المجد">
                        <span style="font-size: 13px;">📺</span>
                        <span>قناة المجد</span>
                    </button>
                    <button type="button" id="hdr-ch-quran_mp3" onclick="quickHeaderPlay('quran_mp3')" class="header-pop-ch-btn" style="--ch-c: #06b6d4;" title="تشغيل القرآن الكريم">
                        <span style="font-size: 13px;">🎧</span>
                        <span>القرآن MP3</span>
                    </button>
                </div>

                <!-- Volume & Stop Controls -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; background: rgba(0,0,0,0.5); padding: 8px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 7px;">
                        <span style="font-size: 14px; color: #94a3b8;">🔊</span>
                        <input id="header-media-vol" type="range" min="0" max="1" step="0.05" value="1" oninput="setHeaderMediaVolume(this.value)" style="width: 85px; height: 5px; accent-color: #f59e0b; cursor: pointer;">
                    </div>
                    <button type="button" onclick="quickHeaderStop(event)" style="background: rgba(225, 29, 72, 0.35); border: 1.5px solid rgba(244, 63, 94, 0.6); color: #ffffff; padding: 5px 12px; border-radius: 9px; font-size: 12px; font-weight: 900; cursor: pointer; transition: 0.2s;" title="إيقاف البث تماماً">⏹️ إيقاف</button>
                </div>

                <a href="engineering_tools.php?tab=quran_mp3" style="display: block; text-align: center; font-size: 11.5px; font-weight: 900; color: #38bdf8; text-decoration: none; padding-top: 4px;" class="hover:underline">
                    📖 استوديو القرآن الكريم والشاشات الكاملة ↗
                </a>
            `;

            if (document.body) {
                document.body.appendChild(pop);
            } else {
                document.addEventListener('DOMContentLoaded', () => {
                    if (!document.getElementById('header-media-popover')) {
                        document.body.appendChild(pop);
                    }
                });
            }
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // 🚀 Media Playback Controllers
    // ════════════════════════════════════════════════════════════════════════
    function playTvChannel(channelKey) {
        const ch = CHANNELS_INFO[channelKey];
        if (!ch) return;

        playPersistentMedia('tv', ch.audioUrl, ch.title, {
            channelKey: channelKey,
            sub: ch.sub,
            icon: ch.icon,
            color: ch.color
        });
    }

    function playQuranAudio(url, title, reciterName) {
        playPersistentMedia('audio', url, title, {
            channelKey: 'quran_mp3',
            sub: reciterName || 'تلاوة عطرة خاشعة من القرآن الكريم',
            icon: 'headphones',
            color: '#06b6d4'
        });
    }

    function playPersistentMedia(type, sourceUrl, title, extraMeta) {
        ensureMediaDock();
        ensureHeaderMediaElements();

        const audioPlayer = document.getElementById('persistent-audio-player');
        const promptResume = document.getElementById('pm-resume-prompt');

        if (audioPlayer) {
            audioPlayer.volume = 1.0;
            if (audioPlayer.src !== sourceUrl) {
                audioPlayer.src = sourceUrl;
            }
            const playPromise = audioPlayer.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    if (promptResume) promptResume.style.display = 'none';
                }).catch(err => {
                    console.log('Autoplay deferred until interaction:', err.message);
                    if (promptResume) promptResume.style.display = 'block';
                    attachOneTimeInteractionListener();
                });
            }
        }

        // Also sync with page-specific quran player if in engineering tools
        const mainQuranPlayer = document.getElementById('main-quran-audio-player');
        if (mainQuranPlayer) {
            mainQuranPlayer.src = sourceUrl;
        }

        // Save fresh active state (new playback starts at 0)
        saveMediaState({
            active: true,
            type: type,
            sourceUrl: sourceUrl,
            title: title,
            extraMeta: extraMeta || {},
            currentTime: 0,
            startedAt: Date.now()
        });

        syncHeaderPopoverUI();
    }

    /**
     * 🎯 Seamless Audio Position Restoration on Page Navigation
     * Ensures recitations DO NOT restart from verse 1 or second 0!
     */
    function applyMediaState(state, allowAutoplayPrompt = true) {
        if (!state || !state.active || !state.sourceUrl) return;

        ensureMediaDock();
        ensureHeaderMediaElements();

        const audioPlayer = document.getElementById('persistent-audio-player');
        const promptResume = document.getElementById('pm-resume-prompt');

        if (audioPlayer) {
            const isSameTrack = (audioPlayer.src === state.sourceUrl);
            const targetTime = (state.type === 'audio' && typeof state.currentTime === 'number' && state.currentTime > 0) ? state.currentTime : 0;
            let playbackStarted = false;

            const triggerPlay = () => {
                const playPromise = audioPlayer.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        if (promptResume) promptResume.style.display = 'none';
                    }).catch(err => {
                        console.log('Autoplay deferred until interaction:', err.message);
                        if (allowAutoplayPrompt && promptResume) {
                            promptResume.style.display = 'block';
                        }
                        attachOneTimeInteractionListener();
                    });
                }
            };

            if (targetTime > 0 && !isSameTrack) {
                audioPlayer.src = state.sourceUrl;
                audioPlayer.preload = 'auto';

                const onReadyToResume = () => {
                    if (playbackStarted) return;
                    playbackStarted = true;
                    try {
                        audioPlayer.currentTime = targetTime;
                        console.log('🎯 [Elsajana Engine] Pre-seeked to ' + targetTime.toFixed(1) + 's before play');
                    } catch (e) {}
                    triggerPlay();
                };

                audioPlayer.addEventListener('loadedmetadata', onReadyToResume, { once: true });
                audioPlayer.addEventListener('canplay', onReadyToResume, { once: true });

                // Safety fallback: if network metadata takes > 900ms, start anyway
                setTimeout(() => {
                    if (!playbackStarted) {
                        onReadyToResume();
                    }
                }, 900);
            } else {
                if (!isSameTrack) {
                    audioPlayer.src = state.sourceUrl;
                }
                if (targetTime > 0) {
                    try { audioPlayer.currentTime = targetTime; } catch(e) {}
                }
                triggerPlay();
            }
        }

        syncHeaderPopoverUI();
    }

    function attachOneTimeInteractionListener() {
        const resumeOnFirstAction = function() {
            resumeBackgroundPlayback();
            window.removeEventListener('pointerdown', resumeOnFirstAction, true);
            window.removeEventListener('click', resumeOnFirstAction, true);
            window.removeEventListener('keydown', resumeOnFirstAction, true);
            window.removeEventListener('touchstart', resumeOnFirstAction, true);
        };
        window.addEventListener('pointerdown', resumeOnFirstAction, true);
        window.addEventListener('click', resumeOnFirstAction, true);
        window.addEventListener('keydown', resumeOnFirstAction, true);
        window.addEventListener('touchstart', resumeOnFirstAction, true);
    }

    function resumeBackgroundPlayback() {
        const audioPlayer = document.getElementById('persistent-audio-player');
        const promptResume = document.getElementById('pm-resume-prompt');
        if (audioPlayer) {
            const state = getStoredMediaState();
            const targetTime = (state && state.type === 'audio' && typeof state.currentTime === 'number') ? state.currentTime : 0;

            audioPlayer.play().then(() => {
                if (promptResume) promptResume.style.display = 'none';
                if (targetTime > 0 && Math.abs(audioPlayer.currentTime - targetTime) > 2) {
                    try { audioPlayer.currentTime = targetTime; } catch(e) {}
                }
            }).catch(e => console.log('Resume error:', e));
        }
    }

    function stopPersistentMedia(shouldBroadcast = true) {
        const audioPlayer = document.getElementById('persistent-audio-player');
        const mainQuranPlayer = document.getElementById('main-quran-audio-player');

        if (audioPlayer) {
            audioPlayer.pause();
            audioPlayer.removeAttribute('src');
            audioPlayer.load();
        }
        if (mainQuranPlayer) {
            mainQuranPlayer.pause();
            mainQuranPlayer.removeAttribute('src');
            mainQuranPlayer.load();
        }

        if (shouldBroadcast) {
            clearMediaState();
        }

        syncHeaderPopoverUI();
    }

    function openBackgroundPopout() {
        const state = getStoredMediaState();
        const channelKey = (state && state.extraMeta && state.extraMeta.channelKey) ? state.extraMeta.channelKey : 'majd';
        const w = 420;
        const h = 240;
        const left = (screen.width - w) - 20;
        const top = (screen.height - h) - 60;
        const popoutUrl = 'background_player.php?ch=' + encodeURIComponent(channelKey);

        const pop = window.open(
            popoutUrl,
            'ElsajanaBackgroundMediaWindow',
            `width=${w},height=${h},top=${top},left=${left},status=no,menubar=no,toolbar=no,location=no,resizable=yes`
        );

        if (pop) {
            pop.focus();
            const audioPlayer = document.getElementById('persistent-audio-player');
            if (audioPlayer) audioPlayer.pause();
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // 📺 Header & Sub-Screen Mini TV Popover Interactions
    // ════════════════════════════════════════════════════════════════════════
    function toggleHeaderMediaPopover(e) {
        if (e) e.stopPropagation();
        ensureHeaderMediaElements();

        const pop = document.getElementById('header-media-popover');
        const btn = document.getElementById('header-media-btn');
        if (!pop || !btn) return;

        if (pop.style.display === 'none' || !pop.style.display) {
            const rect = btn.getBoundingClientRect();
            pop.style.position = 'fixed';
            pop.style.top = Math.max(10, rect.bottom + 8) + 'px';

            const popWidth = 320;
            let leftPos = rect.left + (rect.width / 2) - (popWidth / 2);
            if (leftPos < 15) leftPos = 15;
            if (leftPos + popWidth > window.innerWidth - 15) {
                leftPos = window.innerWidth - popWidth - 15;
            }
            pop.style.left = leftPos + 'px';
            pop.style.zIndex = '999999999';
            pop.style.display = 'block';
            syncHeaderPopoverUI();
        } else {
            pop.style.display = 'none';
        }
    }

    function closeHeaderMediaPopover(e) {
        if (e) e.stopPropagation();
        const pop = document.getElementById('header-media-popover');
        if (pop) pop.style.display = 'none';
    }

    function setHeaderMediaVolume(val) {
        const audioPlayer = document.getElementById('persistent-audio-player');
        if (audioPlayer) {
            audioPlayer.volume = parseFloat(val);
        }
    }

    function quickHeaderPlay(channelKey) {
        if (channelKey === 'quran_mp3') {
            const state = getStoredMediaState();
            if (state && state.type === 'audio' && state.sourceUrl) {
                applyMediaState(state, true);
                syncHeaderPopoverUI();
                return;
            }
            if (typeof window.openQuranMp3Studio === 'function') {
                window.openQuranMp3Studio();
                closeHeaderMediaPopover();
                return;
            } else {
                window.location.href = 'engineering_tools.php?tab=quran_mp3';
                return;
            }
        }
        playTvChannel(channelKey);
        syncHeaderPopoverUI();
    }

    function quickHeaderStop(e) {
        if (e) e.stopPropagation();
        stopPersistentMedia(true);
        syncHeaderPopoverUI();
    }

    function syncHeaderPopoverUI() {
        const state = getStoredMediaState();
        const isPlaying = state && state.active;

        const btn = document.getElementById('header-media-btn');
        const label = document.getElementById('header-media-label');
        const dot = document.getElementById('header-media-dot');
        const popTitle = document.getElementById('popover-media-title');
        const popDot = document.getElementById('popover-status-dot');

        const chKey = (state && state.extraMeta && state.extraMeta.channelKey) ? state.extraMeta.channelKey : null;

        if (btn) {
            if (isPlaying) {
                btn.classList.add('is-playing');
                if (dot) dot.style.display = 'inline-block';
                if (label) {
                    if (chKey === 'makkah') label.innerText = 'مكة 🔴';
                    else if (chKey === 'madinah') label.innerText = 'المدينة 🔴';
                    else if (chKey === 'majd') label.innerText = 'المجد 🔴';
                    else if (chKey === 'quran_mp3' || state.type === 'audio') label.innerText = 'القرآن 🟢';
                    else label.innerText = 'شغال 🔴';
                }
            } else {
                btn.classList.remove('is-playing');
                if (dot) dot.style.display = 'none';
                if (label) label.innerText = 'التلفاز';
            }
        }

        if (popTitle) {
            popTitle.innerText = isPlaying ? (state.title || 'بث حي شغال بالخلفية') : 'البث متوقف حالياً (انقر على أي قناة للتشغيل)';
        }
        const popBadge = document.getElementById('popover-status-badge');
        if (popBadge) {
            if (isPlaying) {
                popBadge.innerHTML = '🟢 شغال الآن بالخلفية';
                popBadge.style.background = 'rgba(16, 185, 129, 0.25)';
                popBadge.style.color = '#34d399';
                popBadge.style.borderColor = 'rgba(16, 185, 129, 0.5)';
            } else {
                popBadge.innerHTML = '⏹️ متوقف حالياً';
                popBadge.style.background = 'rgba(100, 116, 139, 0.25)';
                popBadge.style.color = '#94a3b8';
                popBadge.style.borderColor = 'rgba(100, 116, 139, 0.4)';
            }
        }
        if (popDot) {
            popDot.style.display = isPlaying ? 'inline-block' : 'none';
        }

        // Highlight active channel in popover
        ['makkah', 'madinah', 'majd', 'quran_mp3'].forEach(k => {
            const chBtn = document.getElementById('hdr-ch-' + k);
            if (chBtn) {
                if (isPlaying && (chKey === k || (k === 'quran_mp3' && state.type === 'audio'))) {
                    chBtn.classList.add('active-ch');
                } else {
                    chBtn.classList.remove('active-ch');
                }
            }
        });

        // Also sync in-page cards if on engineering_tools.php
        if (typeof window.renderQuickChannelVisuals === 'function') {
            window.renderQuickChannelVisuals(isPlaying ? (chKey || (state.type === 'audio' ? 'quran_mp3' : null)) : null);
        }
    }

    // Close popover when clicking anywhere outside
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('header-media-control-wrap');
        const pop = document.getElementById('header-media-popover');
        if (pop && pop.style.display !== 'none') {
            const isClickInsideBtn = wrap && wrap.contains(e.target);
            const isClickInsidePop = pop.contains(e.target);
            if (!isClickInsideBtn && !isClickInsidePop) {
                pop.style.display = 'none';
            }
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // 🌐 Initial Auto-Resume on Page Load (Across All Screens)
    // ════════════════════════════════════════════════════════════════════════
    function initGlobalMedia() {
        ensureMediaDock();
        ensureHeaderMediaElements();
        const saved = getStoredMediaState();
        if (saved && saved.active) {
            applyMediaState(saved, true);
        } else {
            syncHeaderPopoverUI();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlobalMedia);
    } else {
        initGlobalMedia();
    }

    // Expose all necessary APIs to window
    window.initGlobalMedia = initGlobalMedia;
    window.applyMediaState = applyMediaState;
    window.toggleHeaderMediaPopover = toggleHeaderMediaPopover;
    window.closeHeaderMediaPopover = closeHeaderMediaPopover;
    window.setHeaderMediaVolume = setHeaderMediaVolume;
    window.quickHeaderPlay = quickHeaderPlay;
    window.quickHeaderStop = quickHeaderStop;
    window.syncHeaderPopoverUI = syncHeaderPopoverUI;

    window.playTvChannel = playTvChannel;
    window.playQuranAudio = playQuranAudio;
    window.playPersistentMedia = playPersistentMedia;
    window.stopPersistentMedia = stopPersistentMedia;
    window.openBackgroundPopout = openBackgroundPopout;
    window.resumeBackgroundPlayback = resumeBackgroundPlayback;
    window.startBackgroundAudio = function(url, label) { playPersistentMedia('audio', url, label); };
    window.stopBackgroundAudio = function() { stopPersistentMedia(); };

})();
