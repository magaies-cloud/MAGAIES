/**
 * ═════════════════════════════════════════════════════════════════════
 * 📲 MAGAIEES ERP 2026 - PWA CLIENT MANAGER & 1-CLICK INSTALL ENGINE
 * ═════════════════════════════════════════════════════════════════════
 * Official Magaiees PWA Client Driver (مقاييس):
 * - Catches 'beforeinstallprompt' and provides 1-Click Native System Dialog
 * - In-App Install Banner: [ 📲 تثبيت تطبيق مقاييس على الجوال / Install Magaiees App ]
 * - Standalone Execution: Hides all install prompts, enables 100% full-screen native UX
 * - iOS Safari Visual Guidance Sheet (Share -> Add to Home Screen)
 * - Dynamic Viewport & Screen Adaptation with Desktop View Toggle
 */

window.PWAManager = {
    deferredPrompt: null,
    isStandalone: false,
    isIOS: false,
    isAndroid: false,

    init() {
        this.detectDisplayMode();
        this.detectPlatform();
        this.registerServiceWorker();
        this.listenInstallPrompt();
        this.setupAdaptiveResizeListener();
        this.bindExistingDomButtons();
        this.checkPushInitialStatus();
    },

    checkPushInitialStatus() {
        if ('Notification' in window && Notification.permission === 'granted' && localStorage.getItem('magaiees_push_subscribed') === '1') {
            this.updatePushUI(true);
        }
    },

    detectDisplayMode() {
        this.isStandalone = window.matchMedia('(display-mode: standalone)').matches 
            || window.matchMedia('(display-mode: fullscreen)').matches
            || window.matchMedia('(display-mode: minimal-ui)').matches
            || (window.navigator.standalone === true);

        if (this.isStandalone) {
            document.documentElement.classList.add('is-pwa-standalone');
            document.body && document.body.classList.add('is-pwa-standalone');
            console.log('[Magaiees PWA] Running in 100% Native Standalone Mode 🚀');
            this.hideAllInstallBanners();
        }
    },

    detectPlatform() {
        const ua = navigator.userAgent || '';
        this.isIOS = /iPhone|iPad|iPod/i.test(ua) && !window.MSStream;
        this.isAndroid = /Android/i.test(ua);
    },

    registerServiceWorker() {
        if ('serviceWorker' in navigator && (window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('./sw.js', { scope: './' })
                    .then((reg) => {
                        console.log('[Magaiees PWA] Service Worker registered:', reg.scope);
                        reg.onupdatefound = () => {
                            const installingWorker = reg.installing;
                            if (!installingWorker) return;
                            installingWorker.onstatechange = () => {
                                if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    if (window.showModernToast) {
                                        window.showModernToast('✨ تم تحديث منظومة مقاييس تلقائياً لأحدث إصدار', 'success', 3000);
                                    }
                                }
                            };
                        };

                        if ('PushManager' in window && reg.pushManager) {
                            reg.pushManager.getSubscription().then(sub => {
                                if (sub) {
                                    localStorage.setItem('magaies_push_subscribed', '1');
                                    PWAManager.updatePushUI(true);
                                }
                            }).catch(() => {});
                        }
                    })
                    .catch((err) => {
                        console.warn('[Magaiees PWA] SW registration notice:', err);
                    });
            });
        }
    },

    listenInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome mini-infobar from appearing
            e.preventDefault();
            this.deferredPrompt = e;
            console.log('[Magaiees PWA] beforeinstallprompt captured successfully!');
            
            // Show all install triggers
            this.showInstallUI();
        });

        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.hideAllInstallBanners();
            if (window.showModernToast) {
                window.showModernToast('🎉 تم تثبيت تطبيق مقاييس بنجاح على الشاشة الرئيسية!', 'success', 4000);
            }
        });
    },

    showInstallUI() {
        if (this.isStandalone) return;

        // Reveal any pre-rendered banners in DOM
        const domBanners = document.querySelectorAll('.magaiees-install-trigger-wrap, #magaiees-native-install-banner, #pwa-install-banner');
        domBanners.forEach(b => {
            b.style.display = 'flex';
        });

        // If no container exists, inject the master floating banner
        if (!document.getElementById('magaiees-floating-install-pill') && domBanners.length === 0) {
            this.renderFloatingInstallPill();
        }
    },

    renderFloatingInstallPill() {
        if (this.isStandalone) return;

        const container = document.createElement('div');
        container.id = 'magaiees-floating-install-pill';
        container.style.cssText = `
            position: fixed;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1.5px solid rgba(56, 189, 248, 0.5);
            border-radius: 30px;
            padding: 7px 14px 7px 10px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6);
            animation: slideDownMagaiees 0.4s ease-out;
            font-family: 'Tajawal', system-ui, sans-serif;
            max-width: 92%;
        `;

        container.innerHTML = `
            <div style="width:34px; height:34px; border-radius:10px; background:#0f172a; border:1.5px solid rgba(56, 189, 248, 0.6); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; box-shadow:0 0 12px rgba(56,189,248,0.4);">
                <img src="assets/icon-192.png?v=2026_m_logo_v4" alt="أيقونة مقاييس" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div style="font-size:0.82rem; font-weight:900; color:#fff; white-space:nowrap;">
                تطبيق مقاييس ERP
            </div>
            <button type="button" id="btn-magaiees-native-install" style="background:linear-gradient(135deg, #0ea5e9, #0284c7); color:#fff; border:none; border-radius:20px; padding:6px 14px; font-size:0.75rem; font-weight:900; cursor:pointer; font-family:inherit; white-space:nowrap; box-shadow:0 2px 8px rgba(14,165,233,0.4); display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-plus-circle"></i> إضافة أيقونة مقاييس لسطح المكتب
            </button>
            <button type="button" onclick="PWAManager.hideAllInstallBanners()" style="background:transparent; border:none; color:rgba(255,255,255,0.6); font-size:0.9rem; cursor:pointer; padding:2px; line-height:1;">✕</button>
        `;

        document.body.appendChild(container);

        const btn = document.getElementById('btn-magaiees-native-install');
        if (btn) {
            btn.onclick = () => this.triggerInstall();
        }
    },

    triggerInstall() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            this.deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('[Magaiees PWA] User accepted the 1-Click install prompt');
                } else {
                    console.log('[Magaiees PWA] User dismissed the install prompt');
                }
                this.deferredPrompt = null;
                this.hideAllInstallBanners();
            });
        } else if (this.isIOS && !this.isStandalone) {
            this.showIOSInstallModal();
        } else {
            if (this.isStandalone) {
                if (window.showModernToast) {
                    window.showModernToast('✨ تطبيق مقاييس مثبت ويعمل حالياً كنسخة أصلية كاملة', 'info', 3000);
                }
            } else {
                this.showIOSInstallModal();
            }
        }
    },

    showIOSInstallModal() {
        const modalId = 'pwa-magaiees-ios-modal';
        let el = document.getElementById(modalId);
        if (!el) {
            const div = document.createElement('div');
            div.id = modalId;
            div.style.cssText = `
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.78);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                z-index: 10005;
                display: flex;
                align-items: flex-end;
                justify-content: center;
                padding-bottom: 24px;
                animation: fadeInPWA 0.25s ease-out;
                font-family: 'Tajawal', system-ui, sans-serif;
            `;

            div.innerHTML = `
                <div style="background:#0f172a; border:2px solid rgba(56,189,248,0.4); border-radius:26px; padding:22px 20px; width:92%; max-width:420px; color:#fff; text-align:center; box-shadow:0 25px 50px rgba(0,0,0,0.7);">
                    <div style="width:64px; height:64px; border-radius:18px; background:#0f172a; border:2px solid rgba(56,189,248,0.6); margin:0 auto 12px; display:flex; align-items:center; justify-content:center; overflow:hidden; box-shadow:0 0 25px rgba(56,189,248,0.5);">
                        <img src="assets/icon-192.png?v=2026_m_logo_v4" alt="أيقونة مقاييس" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <strong style="font-size:1.1rem; display:block; margin-bottom:6px; color:#f8fafc;">تثبيت تطبيق مقاييس (Magaiees ERP)</strong>
                    <p style="font-size:0.84rem; color:#cbd5e1; line-height:1.6; margin-bottom:16px;">
                        لتشغيل مقاييس كـ <strong>تطبيق أصلي بشاشة كاملة</strong> وبدون أشرطة المتصفح:
                        <br>
                        1. اضغط على زر المشاركة في أسفل شاشة الآيفون:
                        <br>
                        <span style="display:inline-block; margin:6px 0; background:rgba(56,189,248,0.15); border:1px solid #38bdf8; padding:4px 12px; border-radius:10px; font-weight:800; color:#38bdf8;">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i> [ مشاركة / Share ]
                        </span>
                        <br>
                        2. مرر للأسفل واختر:
                        <br>
                        <span style="display:inline-block; margin-top:4px; background:rgba(16,185,129,0.18); border:1px solid #10b981; padding:5px 14px; border-radius:10px; font-weight:900; color:#34d399;">
                            <i class="fa-regular fa-square-plus"></i> إضافة إلى الشاشة الرئيسية (Add to Home Screen)
                        </span>
                    </p>
                    <button type="button" onclick="document.getElementById('${modalId}').remove()" style="width:100%; height:44px; border-radius:14px; background:linear-gradient(135deg, #0ea5e9, #0284c7); color:#fff; font-weight:900; border:none; cursor:pointer; font-size:0.92rem; font-family:inherit; box-shadow:0 4px 15px rgba(14,165,233,0.35);">
                        فهمت ذلك ✓
                    </button>
                </div>
            `;
            document.body.appendChild(div);
        }
    },

    hideAllInstallBanners() {
        const floatPill = document.getElementById('magaiees-floating-install-pill');
        if (floatPill) floatPill.remove();

        const bannerWrap = document.getElementById('pwa-install-banner-wrap');
        if (bannerWrap) bannerWrap.remove();

        const staticBanner = document.getElementById('pwa-install-banner');
        if (staticBanner) staticBanner.style.display = 'none';

        const inAppBanners = document.querySelectorAll('.magaiees-install-trigger-wrap, #magaiees-native-install-banner');
        inAppBanners.forEach(b => b.style.display = 'none');
    },

    bindExistingDomButtons() {
        // Automatically wire up any install buttons in the markup
        document.querySelectorAll('[data-action="install-magaiees-pwa"], .btn-install-magaiees').forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                this.triggerInstall();
            };
        });
    },

    setupAdaptiveResizeListener() {
        let resizeTimer = null;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const width = window.innerWidth;
                const path = window.location.pathname;
                const params = new URLSearchParams(window.location.search);
                const hasDesktopOverride = params.get('force_desktop') === '1' || localStorage.getItem('magaiees_view_preference') === 'desktop';
                const hasMobileOverride = params.get('force_mobile') === '1' || localStorage.getItem('magaiees_view_preference') === 'mobile';

                // If user is on desktop ERP and screen drops under 768px (without manual desktop lock)
                if (width < 768 && path.includes('magaiees_unified_dashboard') && !hasDesktopOverride) {
                    window.location.replace('mobile-dashboard.php');
                }
                // If user is on mobile dashboard and screen expands to desktop width (without manual mobile lock)
                // Skip redirect if device is mobile in landscape — check screen.orientation or touch support
                else if (width >= 1024 && path.includes('mobile-dashboard') && !hasMobileOverride) {
                    const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
                    const isLandscape = window.innerHeight < window.innerWidth;
                    // Only redirect if NOT a touch device in landscape (i.e. actual desktop resize)
                    if (!(isTouchDevice && isLandscape)) {
                        window.location.replace('magaiees_unified_dashboard.php');
                    }
                }
            }, 350);
        });
    },

    // ────────────────────────────────────────────────────────────────────
    // 🔔 WEB PUSH NOTIFICATIONS ENGINE (Lockscreen & Background Alerts)
    // ────────────────────────────────────────────────────────────────────
    defaultVapidPublicKey: 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5Nksh8U',

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    },

    async enablePushNotifications() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            if (window.showModernToast) {
                window.showModernToast('⚠️ متصفحك الحالي لا يدعم إشعارات الويب المتقدمة (Web Push)', 'warning', 3500);
            }
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                if (window.showModernToast) {
                    window.showModernToast('يرجى السماح بالإشعارات من إعدادات المتصفح أو القفل', 'warning', 3000);
                }
                return false;
            }

            const reg = await navigator.serviceWorker.ready;
            let sub = await reg.pushManager.getSubscription();

            if (!sub) {
                let vapidKey = this.defaultVapidPublicKey;
                try {
                    const cfgRes = await fetch('api/push_subscription.php?action=get_config');
                    if (cfgRes.ok) {
                        const cfg = await cfgRes.json();
                        if (cfg && cfg.vapid_public_key) vapidKey = cfg.vapid_public_key;
                    }
                } catch(e) {}

                const convertedVapidKey = this.urlBase64ToUint8Array(vapidKey);
                sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });
            }

            // Save subscription to backend
            await fetch('api/push_subscription.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_subscription',
                    subscription: sub.toJSON()
                })
            });

            localStorage.setItem('magaiees_push_subscribed', '1');
            if (window.showModernToast) {
                window.showModernToast('🔔 تم تفعيل إشعارات الخلفية بنجاح! ستصلك التنبيهات حتى والشاشة مقفلة', 'success', 4000);
            }

            this.updatePushUI(true);
            return true;
        } catch(err) {
            console.error('[Magaiees Push Error]', err);
            if (window.showModernToast) {
                window.showModernToast('حدث خطأ أثناء تفعيل الإشعارات: ' + err.message, 'error', 4000);
            }
            return false;
        }
    },

    async sendTestPushNotification() {
        try {
            const reg = await navigator.serviceWorker.ready;
            if (reg.showNotification) {
                reg.showNotification('🔔 تجربة إشعار مقاييس ERP', {
                    body: '🎉 ممتاز! إشعارات النظام تعمل بأعلى كفاءة والشاشة مقفلة.',
                    icon: './assets/icon-192.png',
                    badge: './assets/icon-192.png',
                    tag: 'test-push-' + Date.now(),
                    vibrate: [200, 100, 200, 100, 300],
                    data: { url: './mobile-dashboard.php' }
                });
            }

            fetch('api/push_subscription.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'test_push',
                    title: '🚨 تنبيه تنفيذي عاجل (مقاييس)',
                    body: 'تم تسجيل فاتورة تجاوزت السقف المالي المحدد • يرجى الاطلاع والاعتماد'
                })
            });

            if (window.showModernToast) {
                window.showModernToast('📲 تم إرسال الإشعار! اقفل شاشة هاتفك لاختبار ظهوره في شاشة القفل', 'info', 4000);
            }
        } catch(err) {
            console.warn('Test notification error:', err);
        }
    },

    updatePushUI(isActive) {
        const toggle = document.getElementById('push-notification-toggle');
        if (toggle) toggle.checked = isActive;
        const statusBadge = document.getElementById('push-status-badge');
        if (statusBadge) {
            statusBadge.style.color = isActive ? '#10b981' : 'rgba(255,255,255,0.4)';
            statusBadge.textContent = isActive ? '🟢 مفعلة ونشطة' : '⚪ غير مفعلة';
        }
    }
};

// Global helper for manual view toggling
window.toggleMagaieesViewMode = function(targetMode) {
    if (targetMode === 'desktop') {
        localStorage.setItem('magaiees_view_preference', 'desktop');
        document.cookie = "magaiees_view_preference=desktop; path=/; max-age=31536000; SameSite=Lax";
        window.location.href = 'magaiees_unified_dashboard.php?force_desktop=1';
    } else if (targetMode === 'mobile') {
        localStorage.setItem('magaiees_view_preference', 'mobile');
        document.cookie = "magaiees_view_preference=mobile; path=/; max-age=31536000; SameSite=Lax";
        window.location.href = 'mobile-dashboard.php?force_mobile=1';
    }
};

// Auto-initialize PWA Manager
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.PWAManager.init());
} else {
    window.PWAManager.init();
}
