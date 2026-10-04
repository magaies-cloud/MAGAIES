/**
 * ═════════════════════════════════════════════════════════════════════
 * 📲 MAGAIEES ERP 2026 - FLAGSHIP PWA & REAL DATA RUNTIME ENGINE
 * ═════════════════════════════════════════════════════════════════════
 * Engine Capabilities:
 * 1. 1-Click PWA Installation on First Visit (Android native prompt + iOS Safari visual guide)
 * 2. Real-Time Data Synchronization with Computer Database (MariaDB/MySQL)
 *    - Customers, Suppliers, Banks, Visa cards, Safes, Items, Units, Ledgers
 * 3. Dynamic Hardware & Screen Auto-Calibration (iPhone Dynamic Island / Notch vs Android navigation)
 */

window.PWAManager = {
    deferredPrompt: null,
    isStandalone: false,
    isIOS: false,
    isAndroid: false,
    deviceProfile: {},
    liveData: null,
    isSyncing: false,
    lastSyncTimestamp: null,

    init() {
        this.detectDisplayMode();
        this.detectPlatform();
        this.calibrateDeviceAndScreen();
        this.registerServiceWorker();
        this.listenInstallPrompt();
        this.setupAdaptiveResizeListener();
        this.bindExistingDomButtons();
        this.renderFirstTimeInstallPrompts();
        this.loadCachedDataFirst();
        this.syncRealData();

        console.log('[Magaiees Mobile Engine] Initialized successfully with Real Data & Hardware Calibration 🚀');
    },

    // ────────────────────────────────────────────────────────────────────
    // 1. HARDWARE & SCREEN AUTO-CALIBRATION (IPHONE / ANDROID SIZES)
    // ────────────────────────────────────────────────────────────────────
    detectDisplayMode() {
        this.isStandalone = window.matchMedia('(display-mode: standalone)').matches 
            || window.matchMedia('(display-mode: fullscreen)').matches
            || window.matchMedia('(display-mode: minimal-ui)').matches
            || (window.navigator.standalone === true);

        if (this.isStandalone) {
            document.documentElement.classList.add('is-pwa-standalone');
            document.body && document.body.classList.add('is-pwa-standalone');
            this.hideAllInstallBanners();
        }
    },

    detectPlatform() {
        const ua = navigator.userAgent || '';
        this.isIOS = /iPhone|iPad|iPod/i.test(ua) && !window.MSStream;
        this.isAndroid = /Android/i.test(ua);
    },

    calibrateDeviceAndScreen() {
        const ua = navigator.userAgent || '';
        const width = window.innerWidth || document.documentElement.clientWidth || screen.width;
        const height = window.innerHeight || document.documentElement.clientHeight || screen.height;
        const ratio = window.devicePixelRatio || 1;

        // Set dynamic app height variable for CSS (100dvh fallback)
        const vh = window.innerHeight;
        document.documentElement.style.setProperty('--app-height', `${vh}px`);
        document.documentElement.style.setProperty('--viewport-width', `${width}px`);

        let deviceName = 'هاتف ذكي';
        let deviceType = 'android';
        let hasDynamicIsland = false;
        let hasNotch = false;

        if (this.isIOS) {
            deviceType = 'ios';
            document.documentElement.classList.add('device-ios');
            document.body && document.body.classList.add('device-ios');

            // Detect Dynamic Island vs Notch vs Classic iPhone
            // iPhone 14 Pro, 15, 16 have height >= 852 and ratio 3
            if (height >= 844 && (height === 852 || height === 932 || (ratio === 3 && width >= 393))) {
                hasDynamicIsland = true;
                deviceName = width >= 430 ? 'iPhone Pro Max (Dynamic Island)' : 'iPhone Pro (Dynamic Island)';
                document.documentElement.classList.add('has-dynamic-island');
            } else if (height >= 812) {
                hasNotch = true;
                deviceName = width >= 414 ? 'iPhone Plus / Max (Notch)' : 'iPhone (Notch)';
                document.documentElement.classList.add('has-notch');
            } else {
                deviceName = 'iPhone SE / Classic';
                document.documentElement.classList.add('classic-ios');
            }
        } else if (this.isAndroid) {
            deviceType = 'android';
            document.documentElement.classList.add('device-android');
            document.body && document.body.classList.add('device-android');

            if (/Samsung/i.test(ua)) deviceName = 'Samsung Galaxy';
            else if (/Xiaomi|Redmi|POCO/i.test(ua)) deviceName = 'Xiaomi / Redmi';
            else if (/Huawei|HONOR/i.test(ua)) deviceName = 'Huawei / Honor';
            else if (/Pixel/i.test(ua)) deviceName = 'Google Pixel';
            else deviceName = 'Android Smartphone';
        }

        // Screen width categorization
        document.documentElement.classList.remove('screen-compact', 'screen-regular', 'screen-large', 'screen-tablet');
        let sizeCategory = 'regular';
        if (width <= 375) {
            sizeCategory = 'compact';
            document.documentElement.classList.add('screen-compact');
        } else if (width <= 414) {
            sizeCategory = 'regular';
            document.documentElement.classList.add('screen-regular');
        } else if (width <= 480) {
            sizeCategory = 'large';
            document.documentElement.classList.add('screen-large');
        } else {
            sizeCategory = 'tablet';
            document.documentElement.classList.add('screen-tablet');
        }

        this.deviceProfile = {
            deviceType,
            deviceName,
            width,
            height,
            sizeCategory,
            hasDynamicIsland,
            hasNotch,
            isStandalone: this.isStandalone
        };

        // Update hardware calibration badge in UI
        this.updateCalibrationBadge();
    },

    updateCalibrationBadge() {
        const badge = document.getElementById('deviceCalibrationBadge');
        if (!badge) return;

        const p = this.deviceProfile;
        const icon = p.deviceType === 'ios' ? '🍎' : '🤖';
        badge.innerHTML = `
            <span class="text-xs">${icon}</span>
            <span class="truncate">${p.deviceName}</span>
            <span class="text-slate-400 font-mono text-[9px]">(${p.width}×${p.height})</span>
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#10b981]"></span>
        `;
        badge.title = `معايرة الشاشة موزونة تلقائياً لجهاز ${p.deviceName} (${p.width}×${p.height}px)`;
    },

    setupAdaptiveResizeListener() {
        let resizeTimer = null;
        const handleResize = () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.calibrateDeviceAndScreen();
            }, 100);
        };

        window.addEventListener('resize', handleResize);
        window.addEventListener('orientationchange', handleResize);
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', handleResize);
        }
    },

    // ────────────────────────────────────────────────────────────────────
    // 2. PWA 1-CLICK INSTALLATION ENGINE (FIRST VISIT PROMPT & IOS GUIDANCE)
    // ────────────────────────────────────────────────────────────────────
    registerServiceWorker() {
        if ('serviceWorker' in navigator && (window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('./sw.js', { scope: './' })
                    .then((reg) => {
                        console.log('[Magaiees PWA] SW registered with scope:', reg.scope);
                    })
                    .catch((err) => {
                        console.warn('[Magaiees PWA] SW notice:', err);
                    });
            });
        }
    },

    listenInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            console.log('[Magaiees PWA] beforeinstallprompt captured!');
            this.showInstallUI();
        });

        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.isStandalone = true;
            this.hideAllInstallBanners();
            if (window.showModernToast) {
                window.showModernToast('🎉 تم تثبيت تطبيق مقاييس بنجاح على الشاشة الرئيسية!', 'success', 5000);
            } else {
                alert('🎉 تم تثبيت تطبيق مقاييس بنجاح على الشاشة الرئيسية!');
            }
        });
    },

    renderFirstTimeInstallPrompts() {
        if (this.isStandalone) return;

        // If user hasn't explicitly dismissed the banner this session, show it
        const isDismissed = sessionStorage.getItem('magaiees_install_banner_dismissed') === '1';
        
        // Show header install button
        const headerBtn = document.getElementById('headerPwaInstallBtn');
        if (headerBtn) {
            headerBtn.classList.remove('hidden');
            headerBtn.style.display = 'inline-flex';
        }

        if (!isDismissed) {
            this.renderFloatingInstallPill();
        }
    },

    showInstallUI() {
        if (this.isStandalone) return;

        const headerBtn = document.getElementById('headerPwaInstallBtn');
        if (headerBtn) {
            headerBtn.classList.remove('hidden');
            headerBtn.style.display = 'inline-flex';
        }

        if (!document.getElementById('magaiees-floating-install-pill')) {
            this.renderFloatingInstallPill();
        }
    },

    renderFloatingInstallPill() {
        if (this.isStandalone || document.getElementById('magaiees-floating-install-pill')) return;

        const pill = document.createElement('div');
        pill.id = 'magaiees-floating-install-pill';
        pill.style.cssText = `
            position: fixed;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 99999;
            width: 92%;
            max-width: 430px;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1.5px solid rgba(56, 189, 248, 0.6);
            border-radius: 24px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(56, 189, 248, 0.25);
            animation: slideDownMagaiees 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            direction: rtl;
        `;

        pill.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                <div style="width:40px; height:40px; border-radius:12px; background:#0f172a; border:1.5px solid #38bdf8; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; box-shadow:0 0 12px rgba(56,189,248,0.4);">
                    <img src="assets/icon-192.png?v=2026_m_v7" alt="أيقونة مقاييس" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='assets/apple-touch-icon.png'">
                </div>
                <div style="line-height:1.25; overflow:hidden;">
                    <div style="font-size:0.84rem; font-weight:900; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">تطبيق مقاييس ERP</div>
                    <div style="font-size:0.7rem; color:#94a3b8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">تثبيت الأيقونة على الموبايل لسرعة الوصول</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                <button type="button" id="btnPwaActionInstall" style="background:linear-gradient(135deg, #0ea5e9, #0284c7); color:#fff; border:none; border-radius:18px; padding:7px 14px; font-size:0.75rem; font-weight:900; cursor:pointer; font-family:inherit; white-space:nowrap; box-shadow:0 3px 12px rgba(14,165,233,0.5); display:flex; align-items:center; gap:5px;">
                    <span>📲 تثبيت الآن</span>
                </button>
                <button type="button" onclick="PWAManager.dismissFloatingPill()" style="background:transparent; border:none; color:rgba(255,255,255,0.5); font-size:1.1rem; cursor:pointer; padding:4px 6px; line-height:1;" title="إغلاق">✕</button>
            </div>
        `;

        document.body.appendChild(pill);

        const btn = document.getElementById('btnPwaActionInstall');
        if (btn) {
            btn.onclick = () => this.triggerInstall();
        }
    },

    dismissFloatingPill() {
        sessionStorage.setItem('magaiees_install_banner_dismissed', '1');
        const pill = document.getElementById('magaiees-floating-install-pill');
        if (pill) pill.remove();
    },

    triggerInstall() {
        if (this.deferredPrompt) {
            // Android 1-Click Native Dialog
            this.deferredPrompt.prompt();
            this.deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('[Magaiees PWA] User accepted installation');
                }
                this.deferredPrompt = null;
                this.hideAllInstallBanners();
            });
        } else if (this.isIOS && !this.isStandalone) {
            // iOS Visual Guide
            this.showIOSInstallModal();
        } else {
            if (this.isStandalone) {
                alert('✨ التطبيق مثبت كنسخة أصلية كاملة على جهازك بالفعل!');
            } else {
                this.showIOSInstallModal();
            }
        }
    },

    showIOSInstallModal() {
        const modalId = 'pwa-magaiees-ios-guidance-sheet';
        let el = document.getElementById(modalId);
        if (el) el.remove();

        const sheet = document.createElement('div');
        sheet.id = modalId;
        sheet.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.82);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            z-index: 100000;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 0 12px 24px;
            direction: rtl;
            animation: fadeInPWA 0.25s ease-out;
        `;

        const isApple = this.isIOS;

        sheet.innerHTML = `
            <div style="background:#0f172a; border:2px solid rgba(56,189,248,0.5); border-radius:30px; padding:24px 20px; width:100%; max-width:440px; color:#fff; text-align:center; box-shadow:0 25px 60px rgba(0,0,0,0.8); position:relative;">
                <button type="button" onclick="document.getElementById('${modalId}').remove()" style="position:absolute; top:14px; left:16px; background:rgba(255,255,255,0.1); border:none; color:#fff; border-radius:50%; width:30px; height:30px; cursor:pointer; font-weight:bold;">✕</button>

                <div style="width:68px; height:68px; border-radius:20px; background:#020617; border:2px solid #38bdf8; margin:0 auto 12px; display:flex; align-items:center; justify-content:center; overflow:hidden; box-shadow:0 0 25px rgba(56,189,248,0.5);">
                    <img src="assets/icon-192.png?v=2026_m_v7" alt="أيقونة مقاييس" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='assets/apple-touch-icon.png'">
                </div>

                <h3 style="font-size:1.15rem; font-weight:900; margin:0 0 4px; color:#f8fafc;">تثبيت تطبيق مقاييس على الجوال</h3>
                <p style="font-size:0.8rem; color:#94a3b8; margin:0 0 18px;">لتثبيت أيقونة التطبيق على شاشة هاتفك والتشغيل بشاشة كاملة وبأعلى سرعة</p>

                <div style="background:rgba(30, 41, 59, 0.7); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:14px; text-align:right; font-size:0.85rem; line-height:1.6; margin-bottom:20px; space-y-2;">
                    ${isApple ? `
                        <div style="margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                            <span style="background:#0284c7; color:#fff; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.75rem; flex-shrink:0;">1</span>
                            <span>اضغط على زر المشاركة أسفل شاشة سفاري:</span>
                        </div>
                        <div style="text-align:center; margin-bottom:12px;">
                            <span style="display:inline-flex; align-items:center; gap:6px; background:rgba(56,189,248,0.18); border:1.5px solid #38bdf8; padding:6px 16px; border-radius:12px; font-weight:900; color:#38bdf8; font-size:0.82rem;">
                                <span>[ مشاركة / Share ]</span>
                                <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </span>
                        </div>
                        <div style="margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                            <span style="background:#10b981; color:#fff; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.75rem; flex-shrink:0;">2</span>
                            <span>مرر للأسفل واختر:</span>
                        </div>
                        <div style="text-align:center;">
                            <span style="display:inline-flex; align-items:center; gap:6px; background:rgba(16,185,129,0.2); border:1.5px solid #10b981; padding:6px 16px; border-radius:12px; font-weight:900; color:#34d399; font-size:0.82rem;">
                                <span>إضافة إلى الشاشة الرئيسية (Add to Home Screen)</span>
                                <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </span>
                        </div>
                    ` : `
                        <div style="margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                            <span style="background:#0284c7; color:#fff; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.75rem; flex-shrink:0;">1</span>
                            <span>اضغط على قائمة المتصفح (نقاط القائمة ⋮ في الأعلى أو الأسفل)</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="background:#10b981; color:#fff; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.75rem; flex-shrink:0;">2</span>
                            <span>اختر <strong>"تثبيت التطبيق"</strong> أو <strong>"إضافة للشاشة الرئيسية"</strong></span>
                        </div>
                    `}
                </div>

                <button type="button" onclick="document.getElementById('${modalId}').remove()" style="width:100%; height:46px; border-radius:16px; background:linear-gradient(135deg, #0ea5e9, #0284c7); color:#fff; font-weight:900; border:none; cursor:pointer; font-size:0.95rem; font-family:inherit; box-shadow:0 4px 20px rgba(14,165,233,0.4);">
                    فهمت ذلك ✓
                </button>
            </div>
        `;

        document.body.appendChild(sheet);
    },

    hideAllInstallBanners() {
        const pill = document.getElementById('magaiees-floating-install-pill');
        if (pill) pill.remove();
        const headerBtn = document.getElementById('headerPwaInstallBtn');
        if (headerBtn) headerBtn.style.display = 'none';
    },

    bindExistingDomButtons() {
        document.querySelectorAll('[data-action="install-magaiees-pwa"], .btn-install-magaiees').forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                this.triggerInstall();
            };
        });
    },

    // ────────────────────────────────────────────────────────────────────
    // 3. REAL DATABASE DATA SYNCHRONIZATION WITH COMPUTER
    // ────────────────────────────────────────────────────────────────────
    loadCachedDataFirst() {
        try {
            const cached = localStorage.getItem('magaiees_mobile_live_cache');
            if (cached) {
                const data = JSON.parse(cached);
                if (data && data.success) {
                    this.hydrateViewsWithRealData(data, true);
                }
            }
        } catch (e) {
            console.warn('[Cache Load]', e);
        }
    },

    async syncRealData(showFeedback = false) {
        if (this.isSyncing) return;
        this.isSyncing = true;

        const refreshBtn = document.getElementById('mainRefreshBtn');
        const refreshIcon = document.getElementById('mainRefreshIcon');
        const syncStatusPill = document.getElementById('liveSyncStatusPill');

        if (refreshIcon) refreshIcon.classList.add('animate-spin');
        if (syncStatusPill) {
            syncStatusPill.innerHTML = `
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                <span>جاري المزامنة مع الكمبيوتر...</span>
            `;
        }

        try {
            const token = localStorage.getItem('authToken') || '';
            const tenantId = localStorage.getItem('currentTenantId') || '2';

            const headers = { 'X-Tenant-Id': String(tenantId) };
            if (token) headers['Authorization'] = 'Bearer ' + token;

            const res = await fetch(`api/mobile_api.php?action=get_full_mobile_data&tenant_id=${tenantId}`, {
                method: 'GET',
                headers: headers,
                cache: 'no-cache'
            });

            if (!res.ok) throw new Error('فشل استجابة الخادم: ' + res.status);
            const data = await res.json();

            if (data && data.success) {
                this.liveData = data;
                localStorage.setItem('magaiees_mobile_live_cache', JSON.stringify(data));
                this.lastSyncTimestamp = new Date();

                this.hydrateViewsWithRealData(data, false);

                if (syncStatusPill) {
                    const timeStr = this.lastSyncTimestamp.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
                    syncStatusPill.innerHTML = `
                        <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_6px_#10b981]"></span>
                        <span>مربوط بالقاعدة الحقيقية (${timeStr})</span>
                    `;
                }

                if (showFeedback && window.showModernToast) {
                    window.showModernToast('✓ تم تحديث ومزامنة كافة البيانات الحقيقية من الكمبيوتر بنجاح', 'success', 3000);
                }
            } else {
                throw new Error(data.error || 'خطأ غير معروف في البيانات');
            }
        } catch (err) {
            console.error('[Sync Error]', err);
            if (syncStatusPill) {
                syncStatusPill.innerHTML = `
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>يعمل بالذاكرة المؤقتة (دون اتصال)</span>
                `;
            }
            if (showFeedback && window.showModernToast) {
                window.showModernToast('تعذر الاتصال المباشر بالقاعدة: ' + err.message, 'warning', 4000);
            }
        } finally {
            this.isSyncing = false;
            if (refreshIcon) refreshIcon.classList.remove('animate-spin');
        }
    },

    hydrateViewsWithRealData(data, isCached = false) {
        if (!data) return;

        // 1. Update Master Accounts List in memory for transfers, dropdowns, searches
        if (Array.isArray(data.master_accounts) && data.master_accounts.length > 0) {
            window.masterAccountsList = data.master_accounts;
        }

        // 2. Update KPI Dashboards
        if (data.kpis) {
            const k = data.kpis;

            // Bank Balance KPI
            const bankCardAmt = document.getElementById('cardBankBalanceAmt');
            if (bankCardAmt) bankCardAmt.textContent = k.total_bank_balance_sdg;

            // Safe Balance KPI
            const safeCardAmt = document.getElementById('cardSafeBalanceAmt');
            if (safeCardAmt) safeCardAmt.textContent = k.total_safe_balance_sdg;

            // Receivables / Customers Debt KPI
            const custDebtEl = document.getElementById('dashReceivablesAmt');
            if (custDebtEl) custDebtEl.textContent = k.total_receivables_sdg;

            // Payables / Suppliers KPI
            const suppDebtEl = document.getElementById('dashPayablesAmt');
            if (suppDebtEl) suppDebtEl.textContent = k.total_payables_sdg;

            // Sales Total Today / Recent
            const salesTotalEl = document.getElementById('dashSalesTotalAmt');
            if (salesTotalEl) salesTotalEl.textContent = k.total_sales_amount_sdg;

            const salesCountEl = document.getElementById('dashSalesCountBadge');
            if (salesCountEl) salesCountEl.textContent = `${k.total_sales_count} فاتورة`;

            // Inventory Total
            const invCountEl = document.getElementById('dashInventoryItemsCount');
            if (invCountEl) invCountEl.textContent = `${k.total_items_count} صنف`;
        }

        // 3. Render Real Banks into modalBanks
        this.renderRealBanksModal(data.banks || []);

        // 4. Render Real Safes into modalSafes
        this.renderRealSafesModal(data.safes || []);

        // 5. Render Real Inventory Items into pageInventory
        this.renderRealInventoryItems(data.items || []);

        // 6. Render Real Recent Transactions into pageTransactions
        this.renderRealTransactions(data.recent_transactions || []);

        // 7. Update Non-Zero Balances List if currently filtered
        if (typeof window.filterBalances === 'function') {
            window.filterBalances();
        }

        // 8. Update Financial Transfer modal options if available
        if (typeof window.initFinancialTransferModal === 'function') {
            window.initFinancialTransferModal();
        }
    },

    renderRealBanksModal(banks) {
        const container = document.getElementById('realBanksListContainer');
        const countBadge = document.getElementById('banksCountBadge');
        const totalBadge = document.getElementById('banksTotalHeaderBadge');

        if (countBadge) countBadge.textContent = `${banks.length} حسابات`;

        if (container) {
            if (banks.length === 0) {
                container.innerHTML = '<div class="p-4 bg-slate-900/60 rounded-2xl text-center text-xs text-slate-400">لا توجد حسابات بنكية مسجلة</div>';
                return;
            }

            container.innerHTML = banks.map(b => `
                <div class="p-3 bg-slate-900/90 rounded-2xl border border-slate-800 flex justify-between items-center hover:border-cyan-500/40 transition">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-bold text-slate-100">${b.name}</span>
                            <span class="text-[9px] bg-cyan-950 text-cyan-300 border border-cyan-500/30 px-1.5 py-0.2 rounded-full font-bold">${b.type}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">${b.branch ? (b.branch + ' • ') : ''}${b.iban ? ('رقم: ' + b.iban) : 'حساب رئيسي'}</span>
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-black text-cyan-400 font-mono">${b.balanceFormatted}</div>
                        <div class="text-[10px] text-slate-400 font-mono">${b.sdgFormatted}</div>
                    </div>
                </div>
            `).join('');
        }
    },

    renderRealSafesModal(safes) {
        const container = document.getElementById('realSafesListContainer');
        const countBadge = document.getElementById('safesCountBadge');

        if (countBadge) countBadge.textContent = `${safes.length} خزن`;

        if (container) {
            if (safes.length === 0) {
                container.innerHTML = '<div class="p-4 bg-slate-900/60 rounded-2xl text-center text-xs text-slate-400">لا توجد خزن مسجلة</div>';
                return;
            }

            container.innerHTML = safes.map(s => `
                <div class="p-3 bg-slate-900/90 rounded-2xl border border-slate-800 flex justify-between items-center hover:border-amber-500/40 transition">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-bold text-slate-100">${s.name}</span>
                            <span class="text-[9px] bg-amber-950 text-amber-300 border border-amber-500/30 px-1.5 py-0.2 rounded-full font-bold">خزينة</span>
                        </div>
                        <span class="text-[10px] text-slate-400">${s.responsible ? ('المسؤول: ' + s.responsible) : 'الصندوق المالي الرئيسي'}</span>
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-black text-amber-400 font-mono">${s.balanceFormatted}</div>
                        <div class="text-[10px] text-slate-400 font-mono">${s.sdgFormatted}</div>
                    </div>
                </div>
            `).join('');
        }
    },

    renderRealInventoryItems(items) {
        const container = document.getElementById('realInventoryItemsList');
        if (!container) return;

        if (items.length === 0) {
            container.innerHTML = '<div class="p-4 bg-slate-900/60 rounded-2xl text-center text-xs text-slate-400">لا توجد أصناف مسجلة في المخزون</div>';
            return;
        }

        // Show first 15 items with clean card design
        container.innerHTML = items.slice(0, 15).map(it => `
            <div class="p-3 bg-slate-900/90 rounded-2xl border border-slate-800 flex justify-between items-center hover:border-indigo-500/40 transition">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-white">${it.name}</span>
                        <span class="text-[9px] bg-slate-800 text-slate-300 px-1.5 py-0.5 rounded-full">${it.unit}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">${it.barcode ? ('باركود: ' + it.barcode) : ('كود: ' + it.id)}</span>
                </div>
                <div class="text-left">
                    <div class="text-xs font-black text-indigo-300 font-mono">${it.stockFormatted}</div>
                    <div class="text-[10px] text-slate-400 font-mono">${it.priceFormatted}</div>
                </div>
            </div>
        `).join('');
    },

    renderRealTransactions(txs) {
        const container = document.getElementById('realTransactionsFeedList');
        if (!container) return;

        if (txs.length === 0) {
            container.innerHTML = '<div class="p-4 bg-slate-900/60 rounded-2xl text-center text-xs text-slate-400">لا توجد حركات حديثة مسجلة</div>';
            return;
        }

        container.innerHTML = txs.map(t => `
            <div class="p-3 bg-slate-900/90 rounded-2xl border border-slate-800 flex justify-between items-center hover:border-cyan-500/40 transition">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-xs shrink-0">
                        📄
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-white">${t.title}</span>
                            <span class="text-[9px] bg-slate-800 text-cyan-300 border border-cyan-500/30 px-1.5 py-0.2 rounded-full">${t.badge || 'عملية'}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">${t.subtitle} • ${t.date}</span>
                    </div>
                </div>
                <div class="text-left">
                    <div class="text-xs font-black text-emerald-400 font-mono">${t.amountSdg}</div>
                    <div class="text-[9px] text-slate-400 font-mono">${t.amountUsd}</div>
                </div>
            </div>
        `).join('');
    },

    // ────────────────────────────────────────────────────────────────────
    // 4. REAL BACKEND ACTIONS (ADD PARTNER & RECORD TRANSFER)
    // ────────────────────────────────────────────────────────────────────
    async savePartnerToBackend(partnerPayload) {
        try {
            const tenantId = localStorage.getItem('currentTenantId') || '2';
            const res = await fetch('api/mobile_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Tenant-Id': String(tenantId)
                },
                body: JSON.stringify({
                    action: 'add_partner',
                    ...partnerPayload
                })
            });

            const json = await res.json();
            if (json && json.success) {
                // Re-sync immediately to get fresh database accounts
                await this.syncRealData(false);
                return { success: true, message: json.message, partner: json.partner };
            } else {
                return { success: false, error: json.error || 'فشل حفظ الحساب' };
            }
        } catch (e) {
            return { success: false, error: e.message };
        }
    },

    async recordFinancialTransferToBackend(transferPayload) {
        try {
            const tenantId = localStorage.getItem('currentTenantId') || '2';
            const res = await fetch('api/mobile_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Tenant-Id': String(tenantId)
                },
                body: JSON.stringify({
                    action: 'record_financial_transfer',
                    ...transferPayload
                })
            });

            const json = await res.json();
            if (json && json.success) {
                // Re-sync to reflect updated balances
                await this.syncRealData(false);
                return { success: true, message: json.message, reference: json.reference };
            } else {
                return { success: false, error: json.error || 'فشل ترحيل الحركة' };
            }
        } catch (e) {
            return { success: false, error: e.message };
        }
    }
};

// Auto-initialize PWA Manager
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.PWAManager.init());
} else {
    window.PWAManager.init();
}
