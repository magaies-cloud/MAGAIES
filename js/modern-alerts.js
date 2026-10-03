/**
 * ═════════════════════════════════════════════════════════════════════
 * MAGAIEES MODERN ALERT & TOAST SUITE (الأجنحة العصرية للإشعارات والتنبيهات)
 * ═════════════════════════════════════════════════════════════════════
 * High-End Visuals, Motion Gestures, Glassmorphism, Sound Synthesizer,
 * and Smart Dialogs for Enterprise Accounting & ERP System.
 */
(function(window) {
    'use strict';

    // 1. Inject Modern Dynamic Styles & Keyframes
    function injectModernAlertStyles() {
        if (typeof document === 'undefined') return;
        if (document.getElementById('magaiees-modern-alert-styles')) return;

        const style = document.createElement('style');
        style.id = 'magaiees-modern-alert-styles';
        style.innerHTML = `
            /* ═══ Glassmorphism Toast Container — Low-Profile Mobile First ═══ */
            #modern-toast-container {
                position: fixed;
                top: 14px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 2147483647;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 6px;
                pointer-events: none;
                width: calc(100% - 20px);
                max-width: 440px;
                box-sizing: border-box;
                font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif;
                direction: rtl;
            }

            /* ═══ The Modern Toast Card — Ultra-Sleek & Low Height ═══ */
            .modern-toast-card {
                position: relative;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                padding: 7px 12px;
                border-radius: 12px;
                backdrop-filter: blur(20px) saturate(190%);
                -webkit-backdrop-filter: blur(20px) saturate(190%);
                color: #ffffff;
                box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.65), 0 0 15px rgba(255, 255, 255, 0.05);
                pointer-events: auto;
                cursor: default;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                gap: 0;
                min-height: auto;
                transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease, box-shadow 0.3s ease;
                animation: modernToastSpringIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                user-select: none;
            }

            .modern-toast-card:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.75);
            }

            /* ═══ Glowing Shimmer Top Line ═══ */
            .modern-toast-shimmer {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.9), transparent);
                animation: modernShimmerWave 2.5s infinite linear;
            }

            /* ═══ Toast Header & Body — Compact Horizontal Alignment ═══ */
            .modern-toast-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                width: 100%;
            }

            .modern-toast-icon-wrap {
                width: 26px;
                height: 26px;
                min-width: 26px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.92rem;
                position: relative;
                flex-shrink: 0;
                animation: modernIconPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            .modern-toast-title-wrap {
                flex: 1;
                min-width: 0;
                display: flex;
                flex-direction: column;
                gap: 1px;
                justify-content: center;
            }

            .modern-toast-title {
                font-weight: 800;
                font-size: 0.90rem;
                letter-spacing: -0.2px;
                display: flex;
                align-items: center;
                gap: 4px;
                line-height: 1.2;
            }

            .modern-toast-message {
                font-size: 0.88rem;
                line-height: 1.35;
                color: rgba(255, 255, 255, 0.96);
                font-weight: 700;
                word-break: break-word;
            }

            .modern-toast-close-btn {
                background: rgba(255, 255, 255, 0.12);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: rgba(255, 255, 255, 0.85);
                width: 22px;
                height: 22px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 0.72rem;
                transition: all 0.2s ease;
                outline: none;
                flex-shrink: 0;
            }

            .modern-toast-close-btn:hover {
                background: rgba(255, 255, 255, 0.28);
                color: #ffffff;
                transform: scale(1.1);
            }

            /* ═══ Progress Countdown Bar — Zero Vertical Waste ═══ */
            .modern-toast-progress-track {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: rgba(255, 255, 255, 0.15);
                overflow: hidden;
                margin: 0;
                border-radius: 0 0 12px 12px;
            }

            .modern-toast-progress-bar {
                height: 100%;
                width: 100%;
                background: rgba(255, 255, 255, 0.85);
                transform-origin: right;
                transition: width linear;
            }

            /* ═══ Color Variants ═══ */
            .modern-toast-success {
                background: linear-gradient(135deg, rgba(6, 78, 59, 0.92) 0%, rgba(15, 23, 42, 0.95) 100%);
                border: 1.5px solid rgba(16, 185, 129, 0.5);
                box-shadow: 0 15px 35px -5px rgba(16, 185, 129, 0.35), 0 0 20px rgba(16, 185, 129, 0.2);
            }
            .modern-toast-success .modern-toast-icon-wrap {
                background: linear-gradient(135deg, #10b981, #059669);
                color: #ffffff;
                box-shadow: 0 0 15px rgba(16, 185, 129, 0.6);
            }
            .modern-toast-success .modern-toast-title { color: #34d399; }

            .modern-toast-error, .modern-toast-danger {
                background: linear-gradient(135deg, rgba(127, 29, 29, 0.92) 0%, rgba(15, 23, 42, 0.95) 100%);
                border: 1.5px solid rgba(239, 68, 68, 0.55);
                box-shadow: 0 15px 35px -5px rgba(239, 68, 68, 0.4), 0 0 20px rgba(239, 68, 68, 0.25);
            }
            .modern-toast-error .modern-toast-icon-wrap, .modern-toast-danger .modern-toast-icon-wrap {
                background: linear-gradient(135deg, #ef4444, #b91c1c);
                color: #ffffff;
                box-shadow: 0 0 15px rgba(239, 68, 68, 0.6);
            }
            .modern-toast-error .modern-toast-title, .modern-toast-danger .modern-toast-title { color: #f87171; }

            .modern-toast-warning {
                background: linear-gradient(135deg, rgba(120, 53, 15, 0.92) 0%, rgba(15, 23, 42, 0.95) 100%);
                border: 1.5px solid rgba(245, 158, 11, 0.5);
                box-shadow: 0 15px 35px -5px rgba(245, 158, 11, 0.35), 0 0 20px rgba(245, 158, 11, 0.2);
            }
            .modern-toast-warning .modern-toast-icon-wrap {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: #ffffff;
                box-shadow: 0 0 15px rgba(245, 158, 11, 0.6);
            }
            .modern-toast-warning .modern-toast-title { color: #fbbf24; }

            .modern-toast-info {
                background: linear-gradient(135deg, rgba(12, 74, 110, 0.92) 0%, rgba(15, 23, 42, 0.95) 100%);
                border: 1.5px solid rgba(56, 189, 248, 0.5);
                box-shadow: 0 15px 35px -5px rgba(56, 189, 248, 0.35), 0 0 20px rgba(56, 189, 248, 0.2);
            }
            .modern-toast-info .modern-toast-icon-wrap {
                background: linear-gradient(135deg, #0ea5e9, #0284c7);
                color: #ffffff;
                box-shadow: 0 0 15px rgba(56, 189, 248, 0.6);
            }
            .modern-toast-info .modern-toast-title { color: #38bdf8; }

            .modern-toast-ai {
                background: linear-gradient(135deg, rgba(46, 16, 101, 0.92) 0%, rgba(15, 23, 42, 0.95) 100%);
                border: 1.5px solid rgba(139, 92, 246, 0.55);
                box-shadow: 0 15px 35px -5px rgba(139, 92, 246, 0.45), 0 0 25px rgba(139, 92, 246, 0.3);
            }
            .modern-toast-ai .modern-toast-icon-wrap {
                background: linear-gradient(135deg, #8b5cf6, #6366f1);
                color: #ffffff;
                box-shadow: 0 0 20px rgba(139, 92, 246, 0.7);
            }
            .modern-toast-ai .modern-toast-title { color: #c4b5fd; }

            /* ═══ Modal Backdrop & Dialog ═══ */
            .modern-alert-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.75);
                backdrop-filter: blur(14px) saturate(180%);
                -webkit-backdrop-filter: blur(14px) saturate(180%);
                z-index: 2147483646;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                opacity: 0;
                transition: opacity 0.3s ease;
                font-family: 'Tajawal', sans-serif;
                direction: rtl;
            }

            .modern-alert-dialog {
                background: linear-gradient(135deg, rgba(30, 41, 59, 0.92) 0%, rgba(15, 23, 42, 0.96) 100%);
                border: 2px solid rgba(255, 255, 255, 0.15);
                border-radius: 24px;
                max-width: 480px;
                width: 100%;
                padding: 28px 24px 22px 24px;
                color: #ffffff;
                box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 35px rgba(255, 255, 255, 0.08);
                transform: scale(0.85) translateY(20px);
                transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                position: relative;
                overflow: hidden;
            }

            .modern-alert-dialog-active {
                opacity: 1 !important;
            }
            .modern-alert-dialog-active .modern-alert-dialog {
                transform: scale(1) translateY(0) !important;
            }

            .modern-alert-btn-confirm {
                background: linear-gradient(135deg, #10b981, #059669);
                border: none;
                color: #ffffff;
                font-weight: 800;
                font-size: 0.95rem;
                padding: 12px 22px;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
                font-family: inherit;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            .modern-alert-btn-confirm:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
                filter: brightness(1.1);
            }

            .modern-alert-btn-cancel {
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.18);
                color: #cbd5e1;
                font-weight: 700;
                font-size: 0.95rem;
                padding: 12px 20px;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.2s ease;
                font-family: inherit;
            }
            .modern-alert-btn-cancel:hover {
                background: rgba(255, 255, 255, 0.16);
                color: #ffffff;
            }

            /* ═══ Keyframe Animations ═══ */
            @keyframes modernToastSpringIn {
                0% {
                    opacity: 0;
                    transform: translateY(-35px) scale(0.9);
                }
                60% {
                    opacity: 1;
                    transform: translateY(6px) scale(1.02);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            @keyframes modernShimmerWave {
                0% { transform: translateX(100%); }
                100% { transform: translateX(-100%); }
            }

            @keyframes modernIconPop {
                0% { transform: scale(0) rotate(-45deg); opacity: 0; }
                70% { transform: scale(1.2) rotate(10deg); }
                100% { transform: scale(1) rotate(0deg); opacity: 1; }
            }

            @keyframes modernShakeAlert {
                0%, 100% { transform: translateX(0); }
                20%, 60% { transform: translateX(-6px); }
                40%, 80% { transform: translateX(6px); }
            }
        `;
        document.head.appendChild(style);
    }

    // 2. Synthesized Subtle Audio Chimes (Web Audio API)
    function playModernAcoustic(type) {
        if (typeof window === 'undefined') return;
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const now = ctx.currentTime;

            if (type === 'success') {
                // Pleasant Harmonic Dual Chime (E6 -> G#6 -> B6)
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();

                osc1.type = 'sine';
                osc2.type = 'sine';

                osc1.frequency.setValueAtTime(659.25, now);
                osc1.frequency.exponentialRampToValueAtTime(1318.51, now + 0.12);

                osc2.frequency.setValueAtTime(830.61, now + 0.08);
                osc2.frequency.exponentialRampToValueAtTime(1661.22, now + 0.22);

                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc2.start(now + 0.08);
                osc1.stop(now + 0.45);
                osc2.stop(now + 0.45);
            } else if (type === 'error' || type === 'danger') {
                // Low tech double pulse
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(320, now);
                osc.frequency.exponentialRampToValueAtTime(180, now + 0.25);
                gain.gain.setValueAtTime(0.1, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.35);
            } else if (type === 'warning') {
                // Soft dual alert
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, now);
                osc.frequency.setValueAtTime(440.00, now + 0.12);
                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.38);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.38);
            } else if (type === 'ai') {
                // Futuristic cyber sparkle
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.exponentialRampToValueAtTime(1046.50, now + 0.15);
                osc.frequency.exponentialRampToValueAtTime(1567.98, now + 0.3);
                gain.gain.setValueAtTime(0.09, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.5);
            }
        } catch(e) {}
    }

    // 3. Ensure Container Exists
    function getToastContainer() {
        injectModernAlertStyles();
        let container = document.getElementById('modern-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'modern-toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    // 4. Default Icon and Title Provider
    function getToastMeta(type) {
        switch(type) {
            case 'success':
                return {
                    icon: '<i class="fa-solid fa-check"></i>',
                    defaultTitle: 'تمت العملية بنجاح',
                    cssClass: 'modern-toast-success'
                };
            case 'error':
            case 'danger':
                return {
                    icon: '<i class="fa-solid fa-shield-xmark"></i>',
                    defaultTitle: 'تنبيه أمني / خطأ',
                    cssClass: 'modern-toast-error'
                };
            case 'warning':
                return {
                    icon: '<i class="fa-solid fa-triangle-exclamation"></i>',
                    defaultTitle: 'تحذير وتنبيه',
                    cssClass: 'modern-toast-warning'
                };
            case 'ai':
                return {
                    icon: '<i class="fa-solid fa-brain"></i>',
                    defaultTitle: 'الذكاء الاصطناعي (AI)',
                    cssClass: 'modern-toast-ai'
                };
            case 'info':
            default:
                return {
                    icon: '<i class="fa-solid fa-circle-info"></i>',
                    defaultTitle: 'إشعار النظام',
                    cssClass: 'modern-toast-info'
                };
        }
    }

    // 5. Core Show Toast Function
    function showModernToast(message, type = 'success', duration = 3800, options = {}) {
        if (!message) return;
        if (typeof document === 'undefined') return;
        const container = getToastContainer();
        const meta = getToastMeta(type);

        const card = document.createElement('div');
        card.className = `modern-toast-card ${meta.cssClass}`;

        const hasCustomTitle = !!options.title;
        const titleText = options.title || '';
        const soundEnabled = options.sound !== false;

        card.innerHTML = `
            <div class="modern-toast-shimmer"></div>
            <div class="modern-toast-header">
                <div class="modern-toast-icon-wrap">
                    ${options.icon || meta.icon}
                </div>
                <div class="modern-toast-title-wrap">
                    ${hasCustomTitle ? `<span class="modern-toast-title">${titleText}</span>` : ''}
                    <div class="modern-toast-message">${message}</div>
                </div>
                <button type="button" class="modern-toast-close-btn" title="إغلاق">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modern-toast-progress-track">
                <div class="modern-toast-progress-bar"></div>
            </div>
        `;

        container.appendChild(card);

        // Sound cue
        if (soundEnabled) {
            playModernAcoustic(type);
        }

        // Haptic feedback for mobile devices
        if (typeof navigator !== 'undefined' && navigator.vibrate) {
            if (type === 'error' || type === 'danger') navigator.vibrate([40, 60, 40]);
            else if (type === 'success') navigator.vibrate(30);
        }

        // Progress Countdown & Hover-to-Pause Gestures
        const progressBar = card.querySelector('.modern-toast-progress-bar');
        let startTime = Date.now();
        let remaining = duration;
        let isPaused = false;
        let timeoutId = null;

        function updateProgress() {
            if (progressBar && duration > 0) {
                progressBar.style.transitionDuration = `${remaining}ms`;
                progressBar.style.width = '0%';
            }
        }

        function dismissToast() {
            if (card.dataset.dismissed) return;
            card.dataset.dismissed = 'true';
            card.style.transition = 'all 0.35s cubic-bezier(0.16, 1, 0.3, 1)';
            card.style.transform = 'translateY(-25px) scale(0.92)';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
            }, 360);
        }

        function startTimer() {
            if (duration <= 0) return;
            startTime = Date.now();
            updateProgress();
            timeoutId = setTimeout(dismissToast, remaining);
        }

        function pauseTimer() {
            if (duration <= 0 || isPaused) return;
            isPaused = true;
            clearTimeout(timeoutId);
            remaining -= (Date.now() - startTime);
            if (progressBar) {
                const currentWidth = window.getComputedStyle(progressBar).width;
                progressBar.style.transitionDuration = '0s';
                progressBar.style.width = currentWidth;
            }
        }

        function resumeTimer() {
            if (duration <= 0 || !isPaused) return;
            isPaused = false;
            startTimer();
        }

        // Event listeners for hover
        card.addEventListener('mouseenter', pauseTimer);
        card.addEventListener('mouseleave', resumeTimer);

        // Close button click
        const closeBtn = card.querySelector('.modern-toast-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dismissToast();
            });
        }

        // Touch swipe-up to dismiss gesture
        let touchStartY = 0;
        card.addEventListener('touchstart', (e) => {
            touchStartY = e.touches[0].clientY;
            pauseTimer();
        }, { passive: true });

        card.addEventListener('touchend', (e) => {
            const touchEndY = e.changedTouches[0].clientY;
            if (touchStartY - touchEndY > 35) {
                dismissToast();
            } else {
                resumeTimer();
            }
        }, { passive: true });

        startTimer();
        return card;
    }

    // 6. Modern Modal Alert Dialog (Replaces native browser alert)
    function showModernAlert(message, title = 'تنبيه النظام', type = 'info') {
        if (typeof document === 'undefined') return Promise.resolve(true);
        injectModernAlertStyles();
        const meta = getToastMeta(type);
        playModernAcoustic(type);

        return new Promise((resolve) => {
            const backdrop = document.createElement('div');
            backdrop.className = 'modern-alert-backdrop';

            backdrop.innerHTML = `
                <div class="modern-alert-dialog" style="border-color:${type === 'success' ? '#10b981' : type === 'error' || type === 'danger' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#38bdf8'};">
                    <div style="display:flex; flex-direction:column; align-items:center; text-align:center; gap:16px;">
                        <div class="modern-toast-icon-wrap" style="width:58px; height:58px; border-radius:18px; font-size:1.6rem; ${type === 'success' ? 'background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 0 25px rgba(16,185,129,0.5);' : type === 'error' || type === 'danger' ? 'background:linear-gradient(135deg,#ef4444,#b91c1c); box-shadow:0 0 25px rgba(239,68,68,0.5); animation:modernShakeAlert 0.45s;' : type === 'warning' ? 'background:linear-gradient(135deg,#f59e0b,#d97706); box-shadow:0 0 25px rgba(245,158,11,0.5);' : 'background:linear-gradient(135deg,#0ea5e9,#0284c7); box-shadow:0 0 25px rgba(56,189,248,0.5);'}">
                            ${meta.icon}
                        </div>
                        <div>
                            <h3 style="margin:0 0 8px 0; font-size:1.25rem; font-weight:800; color:#fff;">${title}</h3>
                            <div style="font-size:0.95rem; line-height:1.7; color:#cbd5e1; white-space:pre-wrap; max-height:60vh; overflow-y:auto; padding:4px;">${message}</div>
                        </div>
                        <button type="button" class="modern-alert-btn-confirm" id="modern-alert-btn-ok" style="width:100%; max-width:200px; padding:12px; margin-top:8px;">
                            <span>حسناً، فهمت ذلك</span>
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(backdrop);
            requestAnimationFrame(() => {
                backdrop.classList.add('modern-alert-dialog-active');
            });

            const okBtn = backdrop.querySelector('#modern-alert-btn-ok');
            function closeAlert() {
                backdrop.classList.remove('modern-alert-dialog-active');
                setTimeout(() => {
                    backdrop.remove();
                    resolve(true);
                }, 300);
            }

            okBtn.addEventListener('click', closeAlert);
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) closeAlert();
            });
            document.addEventListener('keydown', function escHandler(e) {
                if (e.key === 'Escape' || e.key === 'Enter') {
                    document.removeEventListener('keydown', escHandler);
                    closeAlert();
                }
            });
            setTimeout(() => okBtn.focus(), 50);
        });
    }

    // 7. Modern Confirm Dialog (Returns Promise<boolean>)
    function showModernConfirm(message, title = 'تأكيد الإجراء', options = {}) {
        if (typeof document === 'undefined') return Promise.resolve(true);
        injectModernAlertStyles();
        playModernAcoustic(options.type || 'warning');

        return new Promise((resolve) => {
            const backdrop = document.createElement('div');
            backdrop.className = 'modern-alert-backdrop';

            const confirmText = options.confirmText || 'نعم، تأكيد الإجراء';
            const cancelText = options.cancelText || 'إلغاء التراجع';
            const isDanger = options.danger !== false;

            backdrop.innerHTML = `
                <div class="modern-alert-dialog" style="border-color:${isDanger ? '#ef4444' : '#38bdf8'};">
                    <div style="display:flex; flex-direction:column; align-items:center; text-align:center; gap:16px;">
                        <div class="modern-toast-icon-wrap" style="width:58px; height:58px; border-radius:18px; font-size:1.6rem; ${isDanger ? 'background:linear-gradient(135deg,#ef4444,#b91c1c); box-shadow:0 0 25px rgba(239,68,68,0.5);' : 'background:linear-gradient(135deg,#f59e0b,#d97706); box-shadow:0 0 25px rgba(245,158,11,0.5);'}">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <div>
                            <h3 style="margin:0 0 8px 0; font-size:1.25rem; font-weight:800; color:#fff;">${title}</h3>
                            <div style="font-size:0.95rem; line-height:1.7; color:#cbd5e1; white-space:pre-wrap; max-height:60vh; overflow-y:auto; padding:4px;">${message}</div>
                        </div>
                        <div style="display:flex; gap:12px; width:100%; justify-content:center; margin-top:8px;">
                            <button type="button" class="modern-alert-btn-confirm" id="modern-confirm-btn-yes" style="${isDanger ? 'background:linear-gradient(135deg,#ef4444,#dc2626); box-shadow:0 4px 15px rgba(239,68,68,0.4);' : ''}">
                                <span>${confirmText}</span>
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button type="button" class="modern-alert-btn-cancel" id="modern-confirm-btn-no">
                                <span>${cancelText}</span>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(backdrop);
            requestAnimationFrame(() => {
                backdrop.classList.add('modern-alert-dialog-active');
            });

            const yesBtn = backdrop.querySelector('#modern-confirm-btn-yes');
            const noBtn = backdrop.querySelector('#modern-confirm-btn-no');

            function finish(result) {
                backdrop.classList.remove('modern-alert-dialog-active');
                setTimeout(() => {
                    backdrop.remove();
                    resolve(result);
                }, 300);
            }

            yesBtn.addEventListener('click', () => finish(true));
            noBtn.addEventListener('click', () => finish(false));
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) finish(false);
            });
            setTimeout(() => yesBtn.focus(), 50);
        });
    }

    // 8. Global Exports & Overrides
    window.showModernToast = showModernToast;
    window.showToast = showModernToast;
    window.showModernAlert = showModernAlert;
    window.showModernConfirm = showModernConfirm;

    // Export suite object
    window.MagaieesAlerts = {
        toast: showModernToast,
        alert: showModernAlert,
        confirm: showModernConfirm,
        playChime: playModernAcoustic,
        success: (msg, title) => showModernToast(msg, 'success', 3800, { title }),
        error: (msg, title) => showModernToast(msg, 'error', 5000, { title }),
        warning: (msg, title) => showModernToast(msg, 'warning', 4500, { title }),
        info: (msg, title) => showModernToast(msg, 'info', 3800, { title }),
        ai: (msg, title) => showModernToast(msg, 'ai', 4500, { title })
    };

    // 9. Intelligent Native Alert Enhancement
    // Transform plain window.alert into modern visual alerts smoothly
    window.alert = function(msg) {
        if (typeof msg === 'string' && (msg.includes('✅') || msg.includes('نجاح'))) {
            showModernToast(msg, 'success', 4000);
        } else if (typeof msg === 'string' && (msg.includes('❌') || msg.includes('عفواً') || msg.includes('خطأ') || msg.includes('حظر') || msg.includes('لا يمكن'))) {
            showModernToast(msg, 'error', 5000);
        } else if (typeof msg === 'string' && (msg.includes('⚠️') || msg.includes('تحذير') || msg.includes('تنبيه') || msg.includes('يرجى'))) {
            showModernToast(msg, 'warning', 4500);
        } else {
            showModernAlert(msg, 'تنبيه النظام', 'info');
        }
    };

    // Auto-initialize on load
    if (typeof document !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', injectModernAlertStyles);
        } else {
            injectModernAlertStyles();
        }
    }

})(typeof window !== 'undefined' ? window : this);
