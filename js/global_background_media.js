/**
 * ════════════════════════════════════════════════════════════════════════════
 * 📡 GLOBAL CROSS-SCREEN BACKGROUND MEDIA & TV CHANNELS ENGINE
 * ════════════════════════════════════════════════════════════════════════════
 * Al-Sajana Smart ERP 2026 - Global Islamic Live Streaming & Quran Engine
 * Keeps live TV & Quran MP3 playing continuously across all ERP screens
 * (Sales, Purchases, Inventory, Vouchers, General Ledger, Tools, etc.)
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
    // 🎨 Self-Contained Styles for Floating Media Dock (Cross-Screen)
    // ════════════════════════════════════════════════════════════════════════
    function injectDockStyles() {
        if (document.getElementById('pm-dock-styles')) return;

        const style = document.createElement('style');
        style.id = 'pm-dock-styles';
        style.textContent = `
            #persistent-media-dock {
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
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease !important;
            }
            #persistent-media-dock * {
                box-sizing: border-box !important;
            }
            #persistent-media-dock.pm-hidden {
                display: none !important;
            }
            .pm-dock-header {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding-bottom: 8px !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
                margin-bottom: 10px !important;
            }
            .pm-dock-title-wrap {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                font-size: 13px !important;
                font-weight: 800 !important;
                color: #fde047 !important;
                overflow: hidden !important;
                white-space: nowrap !important;
                text-overflow: ellipsis !important;
                flex: 1 !important;
            }
            .pm-ping-badge {
                display: inline-block !important;
                width: 10px !important;
                height: 10px !important;
                border-radius: 50% !important;
                background-color: #ef4444 !important;
                box-shadow: 0 0 10px #ef4444 !important;
                animation: pmPulse 1.5s infinite !important;
                flex-shrink: 0 !important;
            }
            @keyframes pmPulse {
                0% { transform: scale(0.9); opacity: 1; }
                50% { transform: scale(1.3); opacity: 0.7; }
                100% { transform: scale(0.9); opacity: 1; }
            }
            .pm-btn-action {
                width: 28px !important;
                height: 28px !important;
                border-radius: 8px !important;
                background: rgba(30, 41, 59, 0.8) !important;
                border: 1px solid rgba(255, 255, 255, 0.15) !important;
                color: #e2e8f0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 12px !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                padding: 0 !important;
            }
            .pm-btn-action:hover {
                background: rgba(51, 65, 85, 1) !important;
                color: #ffffff !important;
                transform: scale(1.08) !important;
            }
            .pm-btn-close {
                background: rgba(159, 18, 57, 0.7) !important;
                border-color: rgba(244, 63, 94, 0.6) !important;
                color: #fecdd3 !important;
                font-weight: 900 !important;
            }
            .pm-btn-close:hover {
                background: rgba(225, 29, 72, 1) !important;
                color: #ffffff !important;
            }
            .pm-visualizer-box {
                background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 27, 75, 0.85)) !important;
                border: 1px solid rgba(245, 158, 11, 0.35) !important;
                border-radius: 16px !important;
                padding: 10px 14px !important;
                margin-bottom: 10px !important;
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }
            .pm-icon-wrap {
                width: 44px !important;
                height: 44px !important;
                border-radius: 12px !important;
                background: rgba(245, 158, 11, 0.2) !important;
                border: 1.5px solid rgba(245, 158, 11, 0.5) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 20px !important;
                color: #fde047 !important;
                flex-shrink: 0 !important;
            }
            .pm-meta-wrap {
                flex: 1 !important;
                overflow: hidden !important;
            }
            .pm-meta-title {
                font-size: 13px !important;
                font-weight: 900 !important;
                color: #ffffff !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                margin-bottom: 2px !important;
            }
            .pm-meta-sub {
                font-size: 11px !important;
                font-weight: 600 !important;
                color: #cbd5e1 !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
            .pm-waves-wrap {
                display: flex !important;
                align-items: center !important;
                gap: 3px !important;
                height: 20px !important;
            }
            .pm-wave-bar {
                width: 3px !important;
                background: #f59e0b !important;
                border-radius: 2px !important;
                animation: pmEqualizer 1.2s infinite ease-in-out !important;
            }
            .pm-wave-bar:nth-child(1) { height: 8px; animation-delay: 0.1s; }
            .pm-wave-bar:nth-child(2) { height: 16px; animation-delay: 0.3s; background: #fbbf24; }
            .pm-wave-bar:nth-child(3) { height: 12px; animation-delay: 0.5s; background: #fde047; }
            .pm-wave-bar:nth-child(4) { height: 20px; animation-delay: 0.2s; background: #f59e0b; }
            .pm-wave-bar:nth-child(5) { height: 10px; animation-delay: 0.4s; background: #eab308; }
            @keyframes pmEqualizer {
                0%, 100% { transform: scaleY(0.4); }
                50% { transform: scaleY(1.3); }
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
                animation: pmPulse 1.8s infinite !important;
            }
            .pm-footer-status {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                margin-top: 8px !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                color: #94a3b8 !important;
            }
            .pm-badge-green {
                color: #34d399 !important;
                display: flex !important;
                align-items: center !important;
                gap: 4px !important;
            }
            .pm-badge-live {
                color: #f87171 !important;
                font-family: monospace !important;
                font-weight: 900 !important;
                background: rgba(239, 68, 68, 0.15) !important;
                padding: 1px 6px !important;
                border-radius: 6px !important;
                border: 1px solid rgba(239, 68, 68, 0.3) !important;
            }
        `;
        document.head.appendChild(style);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 🧱 Build or Ensure Persistent Media Dock in DOM
    // ════════════════════════════════════════════════════════════════════════
    function ensureMediaDock() {
        injectDockStyles();
        let dock = document.getElementById('persistent-media-dock');
        if (dock) return dock;

        dock = document.createElement('div');
        dock.id = 'persistent-media-dock';
        dock.className = 'pm-hidden';

        dock.innerHTML = `
            <div class="pm-dock-header">
                <div class="pm-dock-title-wrap">
                    <span class="pm-ping-badge"></span>
                    <span id="persistent-media-title">بث مباشر شغال في الخلفية</span>
                </div>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <button type="button" class="pm-btn-action" onclick="openBackgroundPopout()" title="فتح في نافذة خلفية مستقلة لعدم الانقطاع نهائياً أثناء التنقل">
                        🗗
                    </button>
                    <button type="button" id="persistent-toggle-btn" class="pm-btn-action" onclick="togglePersistentVideoSize()" title="تصغير/تكبير">
                        📺
                    </button>
                    <button type="button" class="pm-btn-action pm-btn-close" onclick="stopPersistentMedia()" title="إيقاف البث وإغلاق المشغل">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Mini Visualizer Screen -->
            <div id="persistent-video-box" class="pm-visualizer-box">
                <div id="persistent-tv-icon-box" class="pm-icon-wrap">
                    <span id="persistent-tv-emoji">📻</span>
                </div>
                <div class="pm-meta-wrap">
                    <div id="persistent-media-head" class="pm-meta-title">قناة البث المباشر</div>
                    <div id="persistent-tv-sub" class="pm-meta-sub">شغال في الخلفية 24/7</div>
                </div>
                <div class="pm-waves-wrap">
                    <span class="pm-wave-bar"></span>
                    <span class="pm-wave-bar"></span>
                    <span class="pm-wave-bar"></span>
                    <span class="pm-wave-bar"></span>
                    <span class="pm-wave-bar"></span>
                </div>
            </div>

            <!-- Audio Player -->
            <div id="persistent-audio-box" style="width: 100%;">
                <audio id="persistent-audio-player" class="pm-audio-player" preload="none" controls></audio>
            </div>

            <!-- Auto-Resume Notification Banner (If browser pauses autoplay) -->
            <div id="pm-resume-prompt" class="pm-prompt-resume" onclick="resumeBackgroundPlayback()">
                🔊 انقر هنا لمواصلة استماع البث في الخلفية
            </div>

            <div class="pm-footer-status">
                <span class="pm-badge-green">
                    ✓ شغال في الخلفية أثناء العمل على الحسابات
                </span>
                <span class="pm-badge-live">LIVE 🔴</span>
            </div>
        `;

        if (document.body) {
            document.body.appendChild(dock);
        } else {
            document.addEventListener('DOMContentLoaded', () => document.body.appendChild(dock));
        }

        return dock;
    }

    // ════════════════════════════════════════════════════════════════════════
    // 💾 Storage State Synchronization
    // ════════════════════════════════════════════════════════════════════════
    function getStoredMediaState() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function saveMediaState(state) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            if (broadcastChannel) {
                broadcastChannel.postMessage({ type: 'STATE_CHANGED', state: state });
            }
        } catch (e) {}
    }

    function clearMediaState() {
        try {
            localStorage.removeItem(STORAGE_KEY);
            if (broadcastChannel) {
                broadcastChannel.postMessage({ type: 'STOP_ALL' });
            }
        } catch (e) {}
    }

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
        if (e.key === STORAGE_KEY) {
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
    // 🚀 Media Controls Implementation
    // ════════════════════════════════════════════════════════════════════════
    let isPersistentVideoExpanded = true;

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
            channelKey: 'quran',
            sub: reciterName || 'تلاوة عطرة خاشعة من القرآن الكريم',
            icon: 'headphones',
            color: '#06b6d4'
        });
    }

    function playPersistentMedia(type, sourceUrl, title, extraMeta) {
        const dock = ensureMediaDock();
        const titleEl = document.getElementById('persistent-media-title');
        const headEl = document.getElementById('persistent-media-head');
        const tvSub = document.getElementById('persistent-tv-sub');
        const tvEmoji = document.getElementById('persistent-tv-emoji');
        const audioPlayer = document.getElementById('persistent-audio-player');
        const promptResume = document.getElementById('pm-resume-prompt');

        if (titleEl) titleEl.innerText = title || 'بث مباشر شغال في الخلفية';
        if (headEl) headEl.innerText = title || 'بث مباشر متواصل';
        if (tvSub && extraMeta && extraMeta.sub) tvSub.innerText = extraMeta.sub;

        if (tvEmoji && extraMeta) {
            if (extraMeta.channelKey === 'makkah') tvEmoji.innerText = '🕋';
            else if (extraMeta.channelKey === 'madinah') tvEmoji.innerText = '🕌';
            else if (extraMeta.channelKey === 'majd') tvEmoji.innerText = '📺';
            else tvEmoji.innerText = '🎧';
        }

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
                    console.log('Autoplay deferred until user interaction:', err.message);
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

        dock.classList.remove('pm-hidden');

        // Save state for persistence across screen navigation
        saveMediaState({
            active: true,
            type: type,
            sourceUrl: sourceUrl,
            title: title,
            extraMeta: extraMeta || {},
            startedAt: Date.now()
        });
    }

    function applyMediaState(state, allowAutoplayPrompt = true) {
        if (!state || !state.active || !state.sourceUrl) return;

        const dock = ensureMediaDock();
        const titleEl = document.getElementById('persistent-media-title');
        const headEl = document.getElementById('persistent-media-head');
        const tvSub = document.getElementById('persistent-tv-sub');
        const tvEmoji = document.getElementById('persistent-tv-emoji');
        const audioPlayer = document.getElementById('persistent-audio-player');
        const promptResume = document.getElementById('pm-resume-prompt');

        if (titleEl) titleEl.innerText = state.title || 'بث حي متواصل في الخلفية';
        if (headEl) headEl.innerText = state.title || 'بث مباشر';
        if (tvSub && state.extraMeta && state.extraMeta.sub) tvSub.innerText = state.extraMeta.sub;

        if (tvEmoji && state.extraMeta) {
            if (state.extraMeta.channelKey === 'makkah') tvEmoji.innerText = '🕋';
            else if (state.extraMeta.channelKey === 'madinah') tvEmoji.innerText = '🕌';
            else if (state.extraMeta.channelKey === 'majd') tvEmoji.innerText = '📺';
            else tvEmoji.innerText = '🎧';
        }

        if (audioPlayer) {
            if (audioPlayer.src !== state.sourceUrl) {
                audioPlayer.src = state.sourceUrl;
            }
            const playPromise = audioPlayer.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    if (promptResume) promptResume.style.display = 'none';
                }).catch(err => {
                    if (allowAutoplayPrompt && promptResume) {
                        promptResume.style.display = 'block';
                    }
                    attachOneTimeInteractionListener();
                });
            }
        }

        dock.classList.remove('pm-hidden');
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
            audioPlayer.play().then(() => {
                if (promptResume) promptResume.style.display = 'none';
            }).catch(e => console.log('Resume error:', e));
        }
    }

    function stopPersistentMedia(shouldBroadcast = true) {
        const dock = document.getElementById('persistent-media-dock');
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
        if (dock) dock.classList.add('pm-hidden');

        if (shouldBroadcast) {
            clearMediaState();
        }
    }

    function togglePersistentVideoSize() {
        const videoBox = document.getElementById('persistent-video-box');
        const btn = document.getElementById('persistent-toggle-btn');
        if (!videoBox) return;

        if (isPersistentVideoExpanded) {
            videoBox.style.display = 'none';
            if (btn) btn.innerText = '📺';
            isPersistentVideoExpanded = false;
        } else {
            videoBox.style.display = 'flex';
            if (btn) btn.innerText = '▲';
            isPersistentVideoExpanded = true;
        }
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
            // Pause in-page audio so there is no echo with the popout
            const audioPlayer = document.getElementById('persistent-audio-player');
            if (audioPlayer) audioPlayer.pause();
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // 🌐 Initial Auto-Resume on Page Load (Across Sales, Accounting, etc.)
    // ════════════════════════════════════════════════════════════════════════
    function initGlobalMedia() {
        ensureMediaDock();
        const saved = getStoredMediaState();
        if (saved && saved.active) {
            // Auto-resume across page transitions
            applyMediaState(saved, true);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlobalMedia);
    } else {
        initGlobalMedia();
    }

    // Expose to window for global availability
    window.playTvChannel = playTvChannel;
    window.playQuranAudio = playQuranAudio;
    window.playPersistentMedia = playPersistentMedia;
    window.stopPersistentMedia = stopPersistentMedia;
    window.togglePersistentVideoSize = togglePersistentVideoSize;
    window.openBackgroundPopout = openBackgroundPopout;
    window.resumeBackgroundPlayback = resumeBackgroundPlayback;
    window.startBackgroundAudio = function(url, label) { playPersistentMedia('audio', url, label); };
    window.stopBackgroundAudio = function() { stopPersistentMedia(); };

})();
