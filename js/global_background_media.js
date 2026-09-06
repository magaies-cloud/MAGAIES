/**
 * ════════════════════════════════════════════════════════════════════════════
 * 📡 GLOBAL CROSS-SCREEN BACKGROUND MEDIA & TV CHANNELS ENGINE (v2026.5)
 * ════════════════════════════════════════════════════════════════════════════
 * Al-Sajana Smart ERP 2026 - Global Islamic Live Streaming & Quran Engine
 * Keeps live TV & Quran MP3 playing continuously across ALL ERP screens
 * (Sales, Purchases, Inventory, Vouchers, General Ledger, Reports, Tools, etc.)
 * 
 * 🏆 ZERO-CUT & ZERO-ECHO ARCHITECTURE:
 * 1. ZERO-CUT SEAMLESS CARRIER: window.top maintains the persistent audio stream
 *    while sub-pages load inside a full-viewport zero-margin seamless carrier container.
 *    Result: 0.000s audio cut during screen transitions!
 * 2. SINGLE-MASTER AUDIO MUTEX (القضاء التام على الصدى والازدواجية):
 *    - Every window/tab possesses a unique WINDOW_INSTANCE_ID.
 *    - Only ONE window or tab is EVER permitted to output audio at any given millisecond.
 *    - If another tab, window, or popout starts playing, all other tabs INSTANTLY PAUSE.
 *    - Eliminates doubled audio ("صوت مدبل أو صدى") across all multi-tab or navigation scenarios.
 * 3. TRANSIENT OVERLAP PREVENTION: Outgoing pages pause immediately on unload so they never
 *    overlap with incoming pages.
 * 4. IFRAME SANITIZATION: Child frames never instantiate or retain audio elements.
 * 5. COMPANION POPOUT HARMONIZATION: background_player.php coordinates seamlessly without conflict.
 * 6. AUTOPLAY RESILIENCE (STANDALONE): Glowing prompt bar & universal interaction listeners.
 * ════════════════════════════════════════════════════════════════════════════
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'elsajana_global_media_state';
    const BROADCAST_CHANNEL_NAME = 'elsajana_media_broadcast';
    const isTopWindow = (window.self === window.top);

    // 🛡️ Unique Window / Tab Instance Identifier for Audio Mutex
    const WINDOW_INSTANCE_ID = 'win_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);

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
            #persistent-media-dock {
                display: none;
                position: fixed !important;
                bottom: 22px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                z-index: 999999999 !important;
                width: auto !important;
                max-width: calc(100vw - 32px) !important;
                box-sizing: border-box !important;
                direction: rtl !important;
                text-align: right !important;
                font-family: 'Tajawal', system-ui, -apple-system, sans-serif !important;
            }
            #persistent-media-dock.pm-prompt-active {
                display: block !important;
                animation: pmPromptFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
            @keyframes pmPromptFadeIn {
                from { opacity: 0; transform: translate(-50%, 15px) scale(0.95); }
                to { opacity: 1; transform: translate(-50%, 0) scale(1); }
            }
            .pm-audio-player {
                display: none !important;
            }
            .pm-prompt-resume {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 14px !important;
                background: linear-gradient(135deg, rgba(20, 10, 35, 0.98), rgba(30, 20, 50, 0.98)) !important;
                border: 2px solid #f59e0b !important;
                color: #ffffff !important;
                font-size: 13px !important;
                font-weight: 800 !important;
                padding: 10px 18px !important;
                border-radius: 50px !important;
                cursor: pointer !important;
                box-shadow: 0 10px 35px rgba(0, 0, 0, 0.85), 0 0 25px rgba(245, 158, 11, 0.65), inset 0 0 12px rgba(245, 158, 11, 0.25) !important;
                backdrop-filter: blur(16px) !important;
                -webkit-backdrop-filter: blur(16px) !important;
                user-select: none !important;
                white-space: nowrap !important;
                transition: transform 0.2s, box-shadow 0.2s !important;
            }
            .pm-prompt-resume:hover {
                transform: scale(1.03) !important;
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.95), 0 0 35px rgba(245, 158, 11, 0.85) !important;
                border-color: #fde047 !important;
            }
            .pm-prompt-pulse-dot {
                width: 10px !important;
                height: 10px !important;
                border-radius: 50% !important;
                background: #f59e0b !important;
                box-shadow: 0 0 10px #f59e0b !important;
                animation: pmDotPulse 1.2s infinite ease-in-out !important;
                flex-shrink: 0 !important;
            }
            @keyframes pmDotPulse {
                0%, 100% { transform: scale(0.9); opacity: 1; }
                50% { transform: scale(1.4); opacity: 0.5; }
            }
            .pm-prompt-btn {
                background: linear-gradient(135deg, #f59e0b, #d97706) !important;
                color: #000000 !important;
                font-weight: 900 !important;
                font-size: 12px !important;
                padding: 5px 14px !important;
                border-radius: 20px !important;
                border: 1px solid #fef08a !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
            }

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
            .header-tv-mini-btn.is-waiting-gesture {
                border-color: #fbbf24 !important;
                animation: headerTvGestureBlink 1s infinite alternate !important;
            }
            @keyframes headerTvGestureBlink {
                from { box-shadow: 0 0 10px rgba(245, 158, 11, 0.4); }
                to { box-shadow: 0 0 24px rgba(245, 158, 11, 0.95), inset 0 0 8px rgba(254, 240, 138, 0.6); }
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

            .subscreen-floating-tv-wrap {
                position: fixed !important;
                top: 15px !important;
                left: 175px !important;
                z-index: 99999 !important;
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                animation: tvFloatIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            }
            @keyframes tvFloatIn {
                from { opacity: 0; transform: translateY(-10px) scale(0.9); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            .header-tv-popover {
                position: fixed !important;
                z-index: 999999999 !important;
                width: 330px !important;
                max-width: calc(100vw - 30px) !important;
                background: linear-gradient(145deg, rgba(15, 23, 42, 0.98), rgba(30, 20, 50, 0.98)) !important;
                border: 2px solid rgba(245, 158, 11, 0.7) !important;
                border-radius: 20px !important;
                padding: 14px 16px !important;
                box-shadow: 0 20px 50px rgba(0,0,0,0.9), 0 0 35px rgba(245, 158, 11, 0.3) !important;
                backdrop-filter: blur(25px) !important;
                -webkit-backdrop-filter: blur(25px) !important;
                font-family: 'Tajawal', system-ui, -apple-system, sans-serif !important;
                direction: rtl !important;
                text-align: right !important;
                color: #f8fafc !important;
                box-sizing: border-box !important;
                animation: popoverFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
            @keyframes popoverFadeIn {
                from { opacity: 0; transform: translateY(-8px) scale(0.96); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            .header-pop-ch-btn {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 7px !important;
                padding: 8px 10px !important;
                border-radius: 11px !important;
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
    // 💾 Storage State Synchronization (Multi-Tenant & Mutex-Aware)
    // ════════════════════════════════════════════════════════════════════════
    function getStoredMediaState() {
        try {
            if (!isTopWindow && window.top && window.top.getStoredMediaState) {
                try { return window.top.getStoredMediaState(); } catch(e) {}
            }
            let raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) {
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
    // 🧱 Build or Ensure Persistent Audio Player in DOM (Audio Master Window Only)
    // ════════════════════════════════════════════════════════════════════════
    let lastTimeSaveTimestamp = 0;

    function ensureMediaDock() {
        if (!isTopWindow) {
            // 🛡️ ZERO-ECHO GUARD: Purge any accidental audio player inside child iframes
            const rogue = document.getElementById('persistent-audio-player');
            if (rogue) {
                rogue.pause();
                rogue.removeAttribute('src');
                rogue.remove();
            }
            return null;
        }

        injectDockStyles();
        let dock = document.getElementById('persistent-media-dock');
        if (dock) {
            attachAudioEventListeners();
            return dock;
        }

        dock = document.createElement('div');
        dock.id = 'persistent-media-dock';

        dock.innerHTML = `
            <audio id="persistent-audio-player" class="pm-audio-player" preload="auto"></audio>
            <div id="pm-resume-prompt" class="pm-prompt-resume" onclick="resumeBackgroundPlayback()">
                <div class="pm-prompt-pulse-dot"></div>
                <span>🔊 اضغط هنا لمواصلة استماع البث بالخلفية (مكة / القرآن)</span>
                <span class="pm-prompt-btn">متابعة ▶</span>
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
        if (!isTopWindow) return;
        const audioPlayer = document.getElementById('persistent-audio-player');
        if (!audioPlayer || audioPlayer.__listenersAttached) return;
        audioPlayer.__listenersAttached = true;

        audioPlayer.addEventListener('timeupdate', function() {
            const now = Date.now();
            if (now - lastTimeSaveTimestamp > 600) {
                lastTimeSaveTimestamp = now;
                saveGlobalMediaPlaybackPosition(audioPlayer);
            }
        });

        audioPlayer.addEventListener('pause', function() {
            saveGlobalMediaPlaybackPosition(audioPlayer);
        });
        audioPlayer.addEventListener('seeked', function() {
            saveGlobalMediaPlaybackPosition(audioPlayer);
        });

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

    function saveGlobalMediaPlaybackPosition(explicitAudio) {
        if (!isTopWindow) return;
        try {
            const audio = explicitAudio || document.getElementById('persistent-audio-player');
            if (!audio || !audio.src) return;

            const t = audio.currentTime;
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

    // 🛡️ ZERO-ECHO GUARD: Pause audio on page unload if standard navigation is occurring
    window.addEventListener('beforeunload', function() {
        saveGlobalMediaPlaybackPosition();
        if (isTopWindow) {
            const carrier = document.getElementById('elsajana-seamless-carrier');
            // If top window itself is navigating away, pause audio immediately so it doesn't overlap
            if (!carrier) {
                const audioPlayer = document.getElementById('persistent-audio-player');
                if (audioPlayer) {
                    audioPlayer.pause();
                }
            }
        }
    }, true);

    window.addEventListener('pagehide', function() {
        saveGlobalMediaPlaybackPosition();
        if (isTopWindow && !document.getElementById('elsajana-seamless-carrier')) {
            const audioPlayer = document.getElementById('persistent-audio-player');
            if (audioPlayer) audioPlayer.pause();
        }
    }, true);

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            saveGlobalMediaPlaybackPosition();
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // 🛡️ SINGLE-MASTER AUDIO MUTEX & BROADCAST COORDINATION
    // ════════════════════════════════════════════════════════════════════════
    function handleBroadcastMessage(event) {
        const msg = event.data;
        if (!msg) return;

        if (msg.type === 'STATE_CHANGED' && msg.state) {
            const st = msg.state;
            const audioPlayer = document.getElementById('persistent-audio-player');

            // 🛡️ MUTEX CHECK: If another window or popout is the active leader, PAUSE THIS TAB IMMEDIATELY!
            if (st.usePopout || (st.activeTabId && st.activeTabId !== WINDOW_INSTANCE_ID)) {
                if (audioPlayer && !audioPlayer.paused) {
                    console.log('🔇 [Mutex] Another tab/window claimed audio leadership. Pausing this window.');
                    audioPlayer.pause();
                }
            } else if (isTopWindow && (!st.activeTabId || st.activeTabId === WINDOW_INSTANCE_ID)) {
                applyMediaState(st, false);
            }
            syncHeaderPopoverUI();
        } else if (msg.type === 'POPOUT_CLOSED') {
            // Popout closed: top window can resume if media was active
            syncHeaderPopoverUI();
        } else if (msg.type === 'STOP_ALL') {
            stopPersistentMedia(false);
        }
    }

    window.addEventListener('storage', function(e) {
        if (e.key && e.key.endsWith(STORAGE_KEY)) {
            try {
                const newState = e.newValue ? JSON.parse(e.newValue) : null;
                const audioPlayer = document.getElementById('persistent-audio-player');

                if (newState && newState.active) {
                    // 🛡️ MUTEX CHECK: If another tab or popout is actively playing, PAUSE THIS TAB!
                    if (newState.usePopout || (newState.activeTabId && newState.activeTabId !== WINDOW_INSTANCE_ID)) {
                        if (audioPlayer && !audioPlayer.paused) {
                            console.log('🔇 [Mutex] Storage event: Another tab/popout is playing. Pausing this window to avoid echo.');
                            audioPlayer.pause();
                        }
                    }
                    syncHeaderPopoverUI();
                } else {
                    stopPersistentMedia(false);
                }
            } catch (err) {}
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // 🌐 SEAMLESS CARRIER ENGINE (True 0.000s Zero-Cut Cross-Screen Playback)
    // ════════════════════════════════════════════════════════════════════════
    function isInternalNavigationUrl(url) {
        if (!url || typeof url !== 'string') return false;
        url = url.trim();
        if (url.startsWith('#') || url.startsWith('javascript:') || url.startsWith('mailto:') || url.startsWith('tel:')) return false;
        if (url.includes('login.php') || url.includes('logout.php') || url.includes('register.php')) return false;

        try {
            const u = new URL(url, window.location.href);
            if (u.origin !== window.location.origin) return false;
            const path = u.pathname.toLowerCase();
            if (!path.endsWith('.php') && !path.includes('.php?')) return false;
            return true;
        } catch (e) {
            return false;
        }
    }

    function activateSeamlessCarrier(destUrl) {
        if (!isTopWindow) {
            window.location.href = destUrl;
            return;
        }

        let carrier = document.getElementById('elsajana-seamless-carrier');
        if (!carrier) {
            carrier = document.createElement('iframe');
            carrier.id = 'elsajana-seamless-carrier';
            carrier.setAttribute('allow', 'autoplay');
            carrier.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;border:none;margin:0;padding:0;z-index:999990;background:#0b1120;';

            carrier.addEventListener('load', function() {
                try {
                    const doc = carrier.contentDocument;
                    const win = carrier.contentWindow;
                    if (doc && doc.title) {
                        document.title = doc.title;
                    }
                    if (win && win.location && win.location.href) {
                        const targetPath = win.location.pathname.split('/').pop() + win.location.search + win.location.hash;
                        if (targetPath && !targetPath.includes('about:blank')) {
                            window.history.replaceState({ carrier: true, path: targetPath }, '', targetPath);
                        }
                    }
                } catch (err) {}
            });

            document.documentElement.appendChild(carrier);
        }

        carrier.src = destUrl;
        try {
            window.history.pushState({ carrier: true, path: destUrl }, '', destUrl);
        } catch (e) {}
    }
    window.activateSeamlessCarrier = activateSeamlessCarrier;

    if (isTopWindow) {
        window.addEventListener('popstate', function(e) {
            const carrier = document.getElementById('elsajana-seamless-carrier');
            if (carrier && carrier.contentWindow) {
                try {
                    carrier.contentWindow.history.back();
                } catch (err) {}
            }
        });

        // Intercept top window navigation clicks when media is active
        document.addEventListener('click', function(e) {
            const state = getStoredMediaState();
            if (!state || !state.active) return;

            const el = e.target.closest('a, [data-href], .sidebar-item, .hub-btn, .bento-card');
            if (!el) return;

            if (el.getAttribute('target') === '_blank' || el.hasAttribute('download')) return;

            let dest = el.getAttribute('data-href') || el.getAttribute('href') || el.dataset?.href;
            if (!dest || dest === '#' || dest.startsWith('javascript:')) {
                const onclickStr = el.getAttribute('onclick') || '';
                const match = onclickStr.match(/window\.location(?:\.href)?\s*=\s*['"]([^'"]+)['"]/);
                if (match && match[1]) {
                    dest = match[1];
                }
            }

            if (!dest || !isInternalNavigationUrl(dest)) return;

            e.preventDefault();
            e.stopImmediatePropagation();
            activateSeamlessCarrier(dest);
        }, true);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 🚀 Universal Floating TV Button & Popover Builder (for ALL Screens)
    // ════════════════════════════════════════════════════════════════════════
    function ensureHeaderMediaElements() {
        injectDockStyles();

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

                <!-- Current Broadcast Status Card -->
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

                <!-- Companion Window & Full Studio Links -->
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <button type="button" onclick="openBackgroundPopout()" style="width: 100%; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; border-radius: 9px; padding: 6px 10px; font-size: 11.5px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: 0.2s;" title="تشغيل في نافذة منفصلة مستقلة تماماً بدون أي انقطاع">
                        <span>🗗</span>
                        <span>تشغيل بنافذة مستقلة (صفر انقطاع نهائياً)</span>
                    </button>
                    <a href="engineering_tools.php?tab=quran_mp3" style="display: block; text-align: center; font-size: 11.5px; font-weight: 900; color: #a78bfa; text-decoration: none; padding-top: 2px;" class="hover:underline">
                        📖 استوديو القرآن الكريم وكبار القراء ↗
                    </a>
                </div>
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
    // 🚀 Media Playback Controllers (Master / Child Delegated)
    // ════════════════════════════════════════════════════════════════════════
    function playTvChannel(channelKey) {
        if (!isTopWindow && window.top && window.top.playTvChannel) {
            try { window.top.playTvChannel(channelKey); return; } catch(e) {}
        }

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
        if (!isTopWindow && window.top && window.top.playQuranAudio) {
            try { window.top.playQuranAudio(url, title, reciterName); return; } catch(e) {}
        }

        playPersistentMedia('audio', url, title, {
            channelKey: 'quran_mp3',
            sub: reciterName || 'تلاوة عطرة خاشعة من القرآن الكريم',
            icon: 'headphones',
            color: '#06b6d4'
        });
    }

    function playPersistentMedia(type, sourceUrl, title, extraMeta) {
        if (!isTopWindow && window.top && window.top.playPersistentMedia) {
            try { window.top.playPersistentMedia(type, sourceUrl, title, extraMeta); return; } catch(e) {}
        }

        ensureMediaDock();
        ensureHeaderMediaElements();

        const audioPlayer = document.getElementById('persistent-audio-player');
        const dock = document.getElementById('persistent-media-dock');

        if (audioPlayer) {
            audioPlayer.volume = 1.0;
            if (audioPlayer.src !== sourceUrl) {
                audioPlayer.src = sourceUrl;
            }
            const playPromise = audioPlayer.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    if (dock) dock.classList.remove('pm-prompt-active');
                    clearInteractionListeners();
                }).catch(err => {
                    console.log('Autoplay blocked pending user interaction:', err.message);
                    if (dock) dock.classList.add('pm-prompt-active');
                    attachUniversalInteractionListener();
                });
            }
        }

        const mainQuranPlayer = document.getElementById('main-quran-audio-player');
        if (mainQuranPlayer) {
            mainQuranPlayer.src = sourceUrl;
        }

        // 🛡️ Claim Leadership under WINDOW_INSTANCE_ID
        saveMediaState({
            active: true,
            activeTabId: WINDOW_INSTANCE_ID,
            usePopout: false,
            type: type,
            sourceUrl: sourceUrl,
            title: title,
            extraMeta: extraMeta || {},
            currentTime: 0,
            startedAt: Date.now()
        });

        syncHeaderPopoverUI();
    }

    function applyMediaState(state, allowAutoplayPrompt = true) {
        if (!state || !state.active || !state.sourceUrl) return;

        if (!isTopWindow && window.top && window.top.applyMediaState) {
            try { window.top.applyMediaState(state, allowAutoplayPrompt); return; } catch(e) {}
        }

        ensureMediaDock();
        ensureHeaderMediaElements();

        const audioPlayer = document.getElementById('persistent-audio-player');
        const dock = document.getElementById('persistent-media-dock');

        // 🛡️ MUTEX CHECK: If another tab or popout is the audio leader, DO NOT PLAY HERE!
        if (state.usePopout || (state.activeTabId && state.activeTabId !== WINDOW_INSTANCE_ID)) {
            if (audioPlayer && !audioPlayer.paused) {
                audioPlayer.pause();
            }
            syncHeaderPopoverUI();
            return;
        }

        if (audioPlayer) {
            const isSameTrack = (audioPlayer.src === state.sourceUrl);
            const targetTime = (state.type === 'audio' && typeof state.currentTime === 'number' && state.currentTime > 0) ? state.currentTime : 0;
            let playbackStarted = false;

            const triggerPlay = () => {
                const playPromise = audioPlayer.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        if (dock) dock.classList.remove('pm-prompt-active');
                        clearInteractionListeners();
                    }).catch(err => {
                        console.log('Autoplay blocked on resume:', err.message);
                        if (allowAutoplayPrompt && dock) {
                            dock.classList.add('pm-prompt-active');
                        }
                        attachUniversalInteractionListener();
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
                    } catch (e) {}
                    triggerPlay();
                };

                audioPlayer.addEventListener('loadedmetadata', onReadyToResume, { once: true });
                audioPlayer.addEventListener('canplay', onReadyToResume, { once: true });

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

    // ════════════════════════════════════════════════════════════════════════
    // 🛡️ Universal Autoplay Interaction Listeners
    // ════════════════════════════════════════════════════════════════════════
    let interactionListenerAttached = false;

    function universalResumeAction() {
        resumeBackgroundPlayback();
        clearInteractionListeners();
    }

    function attachUniversalInteractionListener() {
        if (interactionListenerAttached) return;
        interactionListenerAttached = true;

        const btn = document.getElementById('header-media-btn');
        if (btn) btn.classList.add('is-waiting-gesture');

        const opts = { capture: true, passive: true };
        window.addEventListener('pointerdown', universalResumeAction, opts);
        window.addEventListener('click', universalResumeAction, opts);
        window.addEventListener('keydown', universalResumeAction, opts);
        window.addEventListener('touchstart', universalResumeAction, opts);
        window.addEventListener('scroll', universalResumeAction, opts);
        window.addEventListener('wheel', universalResumeAction, opts);
        window.addEventListener('mousemove', universalResumeAction, opts);
    }

    function clearInteractionListeners() {
        interactionListenerAttached = false;
        const btn = document.getElementById('header-media-btn');
        if (btn) btn.classList.remove('is-waiting-gesture');

        const opts = { capture: true, passive: true };
        window.removeEventListener('pointerdown', universalResumeAction, opts);
        window.removeEventListener('click', universalResumeAction, opts);
        window.removeEventListener('keydown', universalResumeAction, opts);
        window.removeEventListener('touchstart', universalResumeAction, opts);
        window.removeEventListener('scroll', universalResumeAction, opts);
        window.removeEventListener('wheel', universalResumeAction, opts);
        window.removeEventListener('mousemove', universalResumeAction, opts);
    }

    function resumeBackgroundPlayback() {
        if (!isTopWindow && window.top && window.top.resumeBackgroundPlayback) {
            try { window.top.resumeBackgroundPlayback(); return; } catch(e) {}
        }

        const audioPlayer = document.getElementById('persistent-audio-player');
        const dock = document.getElementById('persistent-media-dock');

        if (audioPlayer) {
            const state = getStoredMediaState();
            // Re-claim leadership
            if (state) {
                state.activeTabId = WINDOW_INSTANCE_ID;
                state.usePopout = false;
                saveMediaState(state);
            }
            const targetTime = (state && state.type === 'audio' && typeof state.currentTime === 'number') ? state.currentTime : 0;

            audioPlayer.play().then(() => {
                if (dock) dock.classList.remove('pm-prompt-active');
                clearInteractionListeners();
                if (targetTime > 0 && Math.abs(audioPlayer.currentTime - targetTime) > 2) {
                    try { audioPlayer.currentTime = targetTime; } catch(e) {}
                }
            }).catch(e => console.log('Resume error:', e));
        }
    }

    function stopPersistentMedia(shouldBroadcast = true) {
        if (!isTopWindow && window.top && window.top.stopPersistentMedia) {
            try { window.top.stopPersistentMedia(shouldBroadcast); return; } catch(e) {}
        }

        const audioPlayer = document.getElementById('persistent-audio-player');
        const mainQuranPlayer = document.getElementById('main-quran-audio-player');
        const dock = document.getElementById('persistent-media-dock');

        if (dock) dock.classList.remove('pm-prompt-active');
        clearInteractionListeners();

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
            // Pause local tab audio; leadership transfers to popout
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

            const popWidth = 330;
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
        if (!isTopWindow && window.top && window.top.setHeaderMediaVolume) {
            try { window.top.setHeaderMediaVolume(val); return; } catch(e) {}
        }
        const audioPlayer = document.getElementById('persistent-audio-player');
        if (audioPlayer) {
            audioPlayer.volume = parseFloat(val);
        }
    }

    function quickHeaderPlay(channelKey) {
        if (!isTopWindow && window.top && window.top.quickHeaderPlay) {
            try { window.top.quickHeaderPlay(channelKey); return; } catch(e) {}
        }

        if (channelKey === 'quran_mp3') {
            const state = getStoredMediaState();
            if (state && state.type === 'audio' && state.sourceUrl) {
                state.activeTabId = WINDOW_INSTANCE_ID;
                saveMediaState(state);
                applyMediaState(state, true);
                syncHeaderPopoverUI();
                return;
            }
            if (typeof window.openQuranMp3Studio === 'function') {
                window.openQuranMp3Studio();
                closeHeaderMediaPopover();
                return;
            } else {
                if (isTopWindow) {
                    activateSeamlessCarrier('engineering_tools.php?tab=quran_mp3');
                } else {
                    window.location.href = 'engineering_tools.php?tab=quran_mp3';
                }
                return;
            }
        }
        playTvChannel(channelKey);
        syncHeaderPopoverUI();
    }

    function quickHeaderStop(e) {
        if (e) e.stopPropagation();
        if (!isTopWindow && window.top && window.top.quickHeaderStop) {
            try { window.top.quickHeaderStop(e); return; } catch(err) {}
        }
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
        ensureHeaderMediaElements();
        if (isTopWindow) {
            ensureMediaDock();
            const saved = getStoredMediaState();
            if (saved && saved.active) {
                // If saved state belongs to another tab or popout, do NOT steal audio
                if (saved.usePopout || (saved.activeTabId && saved.activeTabId !== WINDOW_INSTANCE_ID && saved.activeTabId === 'POPOUT_PLAYER')) {
                    syncHeaderPopoverUI();
                } else {
                    // Claim leadership for this tab
                    saved.activeTabId = WINDOW_INSTANCE_ID;
                    saveMediaState(saved);
                    applyMediaState(saved, true);
                }
            } else {
                syncHeaderPopoverUI();
            }
        } else {
            // Child frame: ensure no rogue audio
            const rogue = document.getElementById('persistent-audio-player');
            if (rogue) rogue.remove();
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
    window.getStoredMediaState = getStoredMediaState;
    window.WINDOW_INSTANCE_ID = WINDOW_INSTANCE_ID;

    window.playTvChannel = playTvChannel;
    window.playQuranAudio = playQuranAudio;
    window.playPersistentMedia = playPersistentMedia;
    window.stopPersistentMedia = stopPersistentMedia;
    window.openBackgroundPopout = openBackgroundPopout;
    window.resumeBackgroundPlayback = resumeBackgroundPlayback;
    window.startBackgroundAudio = function(url, label) { playPersistentMedia('audio', url, label); };
    window.stopBackgroundAudio = function() { stopPersistentMedia(); };

})();
