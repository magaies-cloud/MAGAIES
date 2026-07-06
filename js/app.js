// --- CLOUD SYNC ENGINE (Offline-First to SaaS Bridge) ---
(function() {
    // Intercept DOMContentLoaded listeners for already-loaded document states (iOS WebKit compatibility)
    const interceptDOMContentLoaded = (target) => {
        const originalAdd = target.addEventListener;
        target.addEventListener = function(type, listener, options) {
            if (type === 'DOMContentLoaded' && (document.readyState === 'interactive' || document.readyState === 'complete')) {
                setTimeout(() => {
                    try {
                        listener.call(target, new Event('DOMContentLoaded'));
                    } catch (e) {
                        console.error("Error in deferred DOMContentLoaded listener:", e);
                    }
                }, 0);
                return;
            }
            return originalAdd.apply(this, arguments);
        };
    };
    interceptDOMContentLoaded(document);
    interceptDOMContentLoaded(window);
})();
const originalJSONParse = JSON.parse;
JSON.parse = function(val) {
    if (val === null || val === undefined) return null;
    if (typeof val === 'object') return val; // Safe bypass if already parsed
    return originalJSONParse.apply(JSON, arguments);
};
(function() {
    const getBaseUrl = () => {
        const origin = window.location.origin;
        const path = window.location.pathname;
        const dir = path.substring(0, path.lastIndexOf('/'));
        return origin + dir + "/";
    };
    const SYNC_PULL_URL = getBaseUrl() + "api_index.php/sync/pull";
    const SYNC_PUSH_URL = getBaseUrl() + "api_index.php/sync/push";

    // -------------------------------------------------------
    // TENANT ISOLATION — كل شركة لها بياناتها المنفصلة
    // -------------------------------------------------------
    function getTenantPrefix() {
        try {
            // Read directly from original storage to avoid infinite loops in proxy
            const userStr = localGetItem('user') || '{}';
            const user = JSON.parse(userStr);
            const tenantId = user.tenant_id || localGetItem('currentTenantId') || 'default';
            return 'tenant_' + tenantId + '_';
        } catch(e) {
            return 'tenant_default_';
        }
    }

    // Convert a base key like "erp_customers_v1" to a tenant-specific key
    window.getTenantKey = function(baseKey) {
        // Keys that are NOT tenant-specific (global settings)
        const globalKeys = ['authToken', 'companyName', 'user', 'erp_theme', 'currentTenantId', 'session_active', 'deviceUuid'];
        if (globalKeys.includes(baseKey)) return baseKey;
        // If already prefixed, return as-is
        if (baseKey.startsWith('tenant_')) return baseKey;
        return getTenantPrefix() + baseKey;
    };

    const syncKeys = [
        'erp_users_v1', 'erp_customers_v1', 'erp_suppliers_v1', 'erp_items_v1',
        'erp_inventory_v1', 'erp_sales_invoices_v1', 'erp_purchase_invoices_v1',
        'erp_receipts_v1', 'erp_payments_v1', 'erp_journal_vouchers_v1',
        'erp_settings_v1', 'erp_warehouses_v1', 'erp_banks_v1', 'erp_safes_v1', 'erp_categories_v1',
        'erp_roles_v1', 'erp_checks_v1', 'erp_gl_v1', 'erp_single_receipts_v1',
        'erp_single_payments_v1', 'erp_partners_v1', 'erp_employees_v1',
        'erp_payroll_v1', 'erp_reserved_sales_v1', 'erp_delivery_notes_v1',
        'erp_accounts_v1',
        'erp_jobs_v1',
        'erp_audit_logs'
    ];

    // --- IndexedDB Memory Proxy Initialization ---
    window.DB_STORE = {};
    window.APP_READY = false;

    // Save originals BEFORE any overrides
    const originalSetItem = Storage.prototype.setItem;
    const originalGetItem = Storage.prototype.getItem;
    const originalRemoveItem = Storage.prototype.removeItem;

    // Helpers bound to localStorage for direct calls
    const localSetItem = originalSetItem.bind(localStorage);
    const localGetItem = originalGetItem.bind(localStorage);
    const localRemoveItem = originalRemoveItem.bind(localStorage);

    // Cross-Tab Synchronization
    const syncChannel = new BroadcastChannel('erp_db_sync');
    syncChannel.onmessage = function(ev) {
        if (ev.data && ev.data.type === 'SYNC_KEY') {
            window.DB_STORE[ev.data.tenantKey] = ev.data.value;
        }
    };

    const promiseTimeout = (promise, ms) => {
        let timeout = new Promise((resolve, reject) => {
            let id = setTimeout(() => {
                clearTimeout(id);
                reject(new Error("Timeout after " + ms + "ms"));
            }, ms);
        });
        return Promise.race([promise, timeout]);
    };

    if (typeof localforage !== 'undefined') {
        const originalLfGet = localforage.getItem.bind(localforage);
        const originalLfSet = localforage.setItem.bind(localforage);
        const originalLfRemove = localforage.removeItem.bind(localforage);

        localforage.getItem = function(key) {
            return promiseTimeout(originalLfGet(key), 250)
                .catch(err => {
                    console.warn(`[localforage override] getItem failed/timeout for ${key}:`, err);
                    return localStorage.getItem(key);
                });
        };

        localforage.setItem = function(key, value) {
            try {
                originalSetItem.call(localStorage, key, value);
            } catch(e) {}
            return promiseTimeout(originalLfSet(key, value), 250)
                .catch(err => {
                    console.warn(`[localforage override] setItem failed/timeout for ${key}:`, err);
                    return value;
                });
        };

        localforage.removeItem = function(key) {
            try { originalRemoveItem.call(localStorage, key); } catch(e) {}
            return promiseTimeout(originalLfRemove(key), 250)
                .catch(err => {
                    console.warn(`[localforage override] removeItem failed/timeout for ${key}:`, err);
                });
        };
    }

    // Load data from IndexedDB into DB_STORE
    async function initDB() {
        try {
            await Promise.all(syncKeys.map(async (key) => {
                const tenantKey = window.getTenantKey(key);
                let val = null;
                try {
                    val = await localforage.getItem(tenantKey);
                } catch(err) {
                    console.warn(`localforage failed or timed out for ${tenantKey}, using localStorage`, err);
                }
                if (val === null) {
                    val = originalGetItem.call(localStorage, tenantKey) || originalGetItem.call(localStorage, key);
                }
                if (val !== null) {
                    window.DB_STORE[tenantKey] = val;
                }
            }));
        } catch (e) {
            console.error("IndexedDB Initialization Error:", e);
        }

        // Complete fallback check: Ensure all syncKeys are loaded in DB_STORE from localStorage if still empty
        syncKeys.forEach(key => {
            const tenantKey = window.getTenantKey(key);
            if (!window.DB_STORE[tenantKey]) {
                const val = originalGetItem.call(localStorage, tenantKey) || originalGetItem.call(localStorage, key);
                if (val !== null) {
                    window.DB_STORE[tenantKey] = val;
                }
            }
        });

        window.APP_READY = true;
        document.dispatchEvent(new Event('DBReady'));
    }

    let isPushing = false;
    let pushQueue = false;
    let pushTimeout = null;
    let dirtyKeys = new Set();

    window.addEventListener('beforeunload', () => {
        if (pushTimeout) {
            clearTimeout(pushTimeout);
            pushToCloud();
        }
    });

    Storage.prototype.setItem = function(key, value) {
        const sessionKeys = ['session_active'];
        if (sessionKeys.includes(key)) {
            return originalSetItem.call(sessionStorage, key, value);
        }

        if (this === localStorage) {
            if (syncKeys.includes(key) && !navigator.onLine) {
                alert('السيرفر غير متصل! لا يمكن إجراء العملية. النظام يعمل الآن بوضع Online-Only.');
                throw new Error('Offline mode disabled. Cannot save data.');
            }

            const tenantKey = window.getTenantKey(key);
            
            // Save to In-Memory Proxy
            window.DB_STORE[tenantKey] = value;

            // Broadcast to other tabs
            syncChannel.postMessage({ type: 'SYNC_KEY', tenantKey: tenantKey, value: value });

            // Save asynchronously to IndexedDB
            localforage.setItem(tenantKey, value);

            // Always save to original localStorage as a robust synchronous fallback for WebKit/iOS
            try {
                originalSetItem.call(localStorage, tenantKey, value);
            } catch (e) {
                console.warn("localStorage quota exceeded for key", key, e);
            }

            if (syncKeys.includes(key)) {
                dirtyKeys.add(key);
                // Debounce Sync: Wait for 800ms of inactivity before pushing to cloud
                if (pushTimeout) clearTimeout(pushTimeout);
                pushTimeout = setTimeout(() => {
                    if (!isPushing) {
                        pushToCloud();
                    } else {
                        pushQueue = true;
                    }
                }, 800);
            }
        } else {
            return originalSetItem.call(this, key, value);
        }
    };

    Storage.prototype.getItem = function(key) {
        const sessionKeys = ['session_active'];
        if (sessionKeys.includes(key)) {
            return originalGetItem.call(sessionStorage, key);
        }

        if (this === localStorage) {
            const tenantKey = window.getTenantKey(key);
            
            // Read from In-Memory Proxy first (this makes it synchronous for the app)
            if (window.DB_STORE.hasOwnProperty(tenantKey)) {
                return window.DB_STORE[tenantKey];
            }
            
            // Fallback for non-sync keys or missing keys
            return originalGetItem.call(localStorage, tenantKey) || originalGetItem.call(localStorage, key);
        }
        return originalGetItem.call(this, key);
    };

    // Global helper for checking uniqueness of Check/Transfer Numbers across all vouchers
    window.isBankReferenceUnique = function(num, type, excludeVoucherId = null) {
        if (!num) return true;
        
        // We will check all these stores because they can all contain financial transactions
        const storesToCheck = [
            'erp_receipts_v1', 'erp_payments_v1', 
            'erp_single_receipts_v1', 'erp_single_payments_v1', 
            'erp_journal_vouchers_v1'
        ];
        
        for (const store of storesToCheck) {
            const dataStr = localStorage.getItem(store);
            if (!dataStr) continue;
            let arr = [];
            try {
                const data = JSON.parse(dataStr);
                arr = Array.isArray(data) ? data : Object.values(data);
            } catch(e) { continue; }
            
            for (const item of arr) {
                // If we are editing, we ignore the same voucher ID
                if (excludeVoucherId && item.id === excludeVoucherId) continue;
                
                if (type === 'check' && item.checkNo) {
                    const itemVal = item.checkNo.toString().trim();
                    const inputVal = num.toString().trim();
                    const itemLast5 = itemVal.length >= 5 ? itemVal.slice(-5) : itemVal;
                    const inputLast5 = inputVal.length >= 5 ? inputVal.slice(-5) : inputVal;
                    if (itemLast5 === inputLast5) return false;
                }
                if (type === 'mobile' && item.mobileTransferNo) {
                    const itemVal = item.mobileTransferNo.toString().trim();
                    const inputVal = num.toString().trim();
                    const itemLast5 = itemVal.length >= 5 ? itemVal.slice(-5) : itemVal;
                    const inputLast5 = inputVal.length >= 5 ? inputVal.slice(-5) : inputVal;
                    if (itemLast5 === inputLast5) return false;
                }
            }
        }
        return true;
    };

    Storage.prototype.removeItem = function(key) {
        const sessionKeys = ['session_active'];
        if (sessionKeys.includes(key)) {
            return originalRemoveItem.call(sessionStorage, key);
        }

        if (this === localStorage) {
            const tenantKey = window.getTenantKey(key);
            
            // Remove from proxy, IndexedDB, and localStorage
            delete window.DB_STORE[tenantKey];
            localforage.removeItem(tenantKey);
            originalRemoveItem.call(localStorage, tenantKey);
            originalRemoveItem.call(localStorage, key); // Also clear non-prefixed if exists
        } else {
            return originalRemoveItem.call(this, key);
        }
    };
    
    // Call initDB when DOM is ready
    if(document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDB);
    } else {
        initDB();
    }

    const originalClear = localStorage.clear.bind(localStorage);

    localStorage.clear = function() {
        const prefix = getTenantPrefix();
        if (prefix === 'tenant_default_') {
            // Unsafe to clear everything even for default, so we only clear the default keys
        }
        
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const k = localStorage.key(i);
            if (k && k.startsWith(prefix)) {
                keysToRemove.push(k);
            }
        }
        keysToRemove.forEach(k => localRemoveItem(k));
    };

    window.getTenantKeys = function() {
        const prefix = getTenantPrefix();
        const keys = [];
        for (let i = 0; i < localStorage.length; i++) {
            const k = localStorage.key(i);
            if (k && k.startsWith(prefix)) {
                keys.push(k.substring(prefix.length));
            }
        }
        return keys;
    };

    function getAuthHeaders() {
        const token = localStorage.getItem('authToken');
        return {
            "Content-Type": "application/json",
            "Authorization": token ? `Bearer ${token}` : ""
        };
    }

    function showSyncOverlay(message, isPull = false) {
        if (isPull) {
            if (document.getElementById('sync-strict-overlay')) return;
            const overlay = document.createElement('div');
            overlay.id = 'sync-strict-overlay';
            overlay.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(15, 23, 42, 0.9); z-index: 999999;
                display: flex; flex-direction: column; align-items: center; justify-content: center;
                backdrop-filter: blur(8px);
            `;
            overlay.innerHTML = `
                <div style="width: 50px; height: 50px; border: 4px solid rgba(56, 189, 248, 0.2); border-top-color: #38bdf8; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <div style="margin-top: 20px; color: #fff; font-family: Tajawal, sans-serif; font-size: 1.2rem; font-weight: 700;" id="sync-strict-msg">${message}</div>
                <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
            `;
            document.body.appendChild(overlay);
        } else {
            // Show neat non-blocking status pill for pushes (saves)
            let widget = document.getElementById('sync-status-widget');
            if (!widget) {
                widget = document.createElement('div');
                widget.id = 'sync-status-widget';
                widget.style.cssText = `
                    position: fixed; bottom: 20px; right: 20px;
                    background: rgba(15, 23, 42, 0.85);
                    border: 1px solid rgba(168, 85, 247, 0.4);
                    color: #fff; padding: 10px 18px; border-radius: 12px;
                    font-family: Tajawal, sans-serif; font-size: 0.85rem; font-weight: bold;
                    display: flex; align-items: center; gap: 8px; z-index: 999999;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.35);
                    backdrop-filter: blur(10px);
                    transition: all 0.3s ease;
                    transform: translateY(0);
                    opacity: 1;
                `;
                document.body.appendChild(widget);
            }
            widget.style.display = 'flex';
            widget.style.opacity = '1';
            widget.style.transform = 'translateY(0)';
            widget.innerHTML = `
                <div style="width: 14px; height: 14px; border: 2px solid rgba(168, 85, 247, 0.2); border-top-color: #c084fc; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <span>${message}</span>
            `;
        }
    }

    function hideSyncOverlay(success = true) {
        // Remove full screen overlay if exists
        const overlay = document.getElementById('sync-strict-overlay');
        if (overlay) overlay.remove();
        
        // Hide/update status widget
        const widget = document.getElementById('sync-status-widget');
        if (widget) {
            if (success) {
                widget.innerHTML = `
                    <span style="color: #34d399; font-weight: bold; display: flex; align-items: center; gap: 4px;">✓ تم المزامنة مع السيرفر</span>
                `;
                setTimeout(() => {
                    widget.style.opacity = '0';
                    widget.style.transform = 'translateY(10px)';
                    setTimeout(() => {
                        widget.style.display = 'none';
                    }, 300);
                }, 2000);
            } else {
                widget.style.display = 'none';
            }
        }
    }

    const pushToCloud = window.pushToCloud = function() {
        return new Promise((resolve, reject) => {
            if (!navigator.onLine) {
                alert('السيرفر غير متصل! لا يمكن حفظ البيانات في وضع Strict Sync.');
                reject(new Error("Offline"));
                return;
            }
            const token = localStorage.getItem('authToken');
            if (!token) {
                resolve();
                return;
            }

            const prefix = getTenantPrefix();
            let payload = {};
            let hasData = false;
            
            // Sync only modified keys if available, otherwise fallback to all syncKeys (e.g. initial force sync)
            const keysToSync = dirtyKeys.size > 0 ? Array.from(dirtyKeys) : syncKeys;
            
            // Keep a copy of current dirty keys in case we need to restore them if the sync fails
            const currentDirty = Array.from(dirtyKeys);
            dirtyKeys.clear();

            keysToSync.forEach(k => {
                const pk = window.getTenantKey(k);
                const data = window.DB_STORE[pk] || localGetItem(pk);
                if (data) {
                    try {
                        payload[k] = JSON.parse(data);
                        hasData = true;
                    } catch(e) {}
                }
            });

            if (!hasData) {
                resolve();
                return;
            }

            isPushing = true;
            showSyncOverlay('جاري حفظ البيانات بالسيرفر...');

            fetch(SYNC_PUSH_URL, {
                method: "POST",
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            }).then(res => {
                if (res.status === 401) {
                    alert('انتهت الجلسة. يرجى تسجيل الدخول مرة أخرى.');
                    window.logout(true);
                    reject(new Error("Unauthorized"));
                    return;
                }
                return res.json();
            }).then(data => {
                if(!data.success) {
                    alert('حدث خطأ أثناء الحفظ في قاعدة البيانات!');
                    // Restore dirty keys
                    currentDirty.forEach(k => dirtyKeys.add(k));
                    reject(new Error("Save failed"));
                } else {
                    console.log("Cloud Sync: Strict Backup Successful");
                    resolve(data);
                }
            }).catch(err => {
                console.error("Cloud Sync Push Failed:", err);
                // Restore dirty keys on failure
                currentDirty.forEach(k => dirtyKeys.add(k));
                alert('فشل الاتصال بقاعدة البيانات! أعد المحاولة.');
                reject(err);
            }).finally(() => {
                isPushing = false;
                hideSyncOverlay();
                if (pushQueue) {
                    pushQueue = false;
                    pushToCloud();
                }
            });
        });
    };


    window.pullFromCloud = function(force = false) {
        if (!navigator.onLine) return Promise.resolve();
        const token = localStorage.getItem('authToken');
        if (!token) return Promise.resolve();

        // Only pull once per session unless forced
        if (!force && sessionStorage.getItem("cloud_synced")) return Promise.resolve();
        
        const prefix = getTenantPrefix();
        
        // Check if we already have local data to decide if we should block the UI
        const hasLocalData = localGetItem(prefix + "erp_customers_v1") !== null || localGetItem(prefix + "erp_users_v1") !== null;
        
        if (!hasLocalData) {
            showSyncOverlay('جاري جلب أحدث البيانات من السيرفر...', true);
        }
        return fetch(SYNC_PULL_URL, { headers: getAuthHeaders() })
            .then(res => {
                if (res.status === 401) {
                    alert('انتهت الجلسة. يرجى تسجيل الدخول مرة أخرى.');
                    window.logout(true);
                    throw new Error("Unauthorized");
                }
                return res.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    Object.keys(data.data).forEach(key => {
                        if (data.data[key]) {
                            const pk = window.getTenantKey(key);
                            const localDataStr = window.DB_STORE[pk] || localGetItem(pk);
                            let localData = [];
                            try { localData = JSON.parse(localDataStr || '[]'); } catch(e){}
                            // ONLY overwrite if server has data, or if local is empty. Do not wipe local data with empty server data!
                            if (data.data[key].length > 0 || localData.length === 0) {
                                const valStr = JSON.stringify(data.data[key]);
                                window.DB_STORE[pk] = valStr;
                                localforage.setItem(pk, valStr).catch(e=>{});
                                try { localSetItem(pk, valStr); } catch(e){}
                            } else {
                                console.log(`Skipping overwrite of ${key}: server sent empty array but local has data. You should PUSH to restore server.`);
                            }
                        }
                    });
                    sessionStorage.setItem("cloud_synced", "true");
                    console.log("Cloud Sync: Strict Restore Successful for tenant:", prefix);
                    window.dispatchEvent(new Event('cloudDataRestored'));
                } else {
                    if (!hasLocalData) alert('فشل جلب البيانات من السيرفر.');
                }
            }).catch(err => {
                console.error("Cloud Sync Pull Failed:", err);
                if (!hasLocalData) alert('فشل الاتصال بالسيرفر! تأكد من تشغيل السيرفر المحلي.');
            }).finally(() => {
                if (!hasLocalData) hideSyncOverlay();
            });
    };

    // Run pull once on load if needed
    document.addEventListener("DOMContentLoaded", () => {
        if (window.location.pathname.includes('login')) return;
        if (localStorage.getItem('authToken')) {
            // Only pull if not already synced in this session
            window.pullFromCloud().then(() => {
                window.dispatchEvent(new Event('strictDataReady'));
            });
        }
    });

})();

// c:\mynewapp\frontend-pwa\js\app.js

// --- Global Theme Initialization (runs immediately to prevent flashing) ---
(function() {
    const savedTheme = localStorage.getItem('erp_theme') || 'dark';
    if (savedTheme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
})();

document.addEventListener('DOMContentLoaded', () => {

    // --- 0.1 Global Glassmorphism Alert Override ---
    window.alert = function(message) {
        if (document.getElementById('glass-alert-modal')) {
            document.getElementById('glass-alert-modal').remove();
        }

        // --- Smart Type Detection ---
        let type = 'info';
        let iconHtml = 'ℹ️';
        let mainColorRGB = '56, 189, 248'; // Blue
        let btnGradient = 'linear-gradient(135deg, #38bdf8, #0284c7)';
        let iconAnimation = 'floatIcon 2s ease-in-out infinite';

        if (message.includes('نجاح') || message.includes('تم ') || message.includes('بنجاح')) {
            type = 'success';
            iconHtml = '✅';
            mainColorRGB = '16, 185, 129'; // Green
            btnGradient = 'linear-gradient(135deg, #10b981, #059669)';
            iconAnimation = 'bounceIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        } else if (message.includes('خطأ') || message.includes('فشل') || message.includes('غير صحيح') || message.includes('معلّق') || message.includes('موقوف') || message.includes('مستخد') || message.includes('مطلوب')) {
            type = 'error';
            iconHtml = '❌';
            mainColorRGB = '239, 68, 68'; // Red
            btnGradient = 'linear-gradient(135deg, #ef4444, #b91c1c)';
            iconAnimation = 'shakeIcon 0.5s';
        } else if (message.includes('تنبيه') || message.includes('تحذير') || message.includes('هل أنت متأكد')) {
            type = 'warning';
            iconHtml = '⚠️';
            mainColorRGB = '245, 158, 11'; // Orange
            btnGradient = 'linear-gradient(135deg, #f59e0b, #d97706)';
        }

        const overlay = document.createElement('div');
        overlay.id = 'glass-alert-modal';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            z-index: 9999999; display: flex; justify-content: center; align-items: center;
            opacity: 0; animation: fadeInGlass 0.3s forwards;
            font-family: 'Inter', sans-serif; direction: rtl;
        `;

        const modal = document.createElement('div');
        modal.style.cssText = `
            background: rgba(30, 41, 59, 0.95); border: 1px solid rgba(${mainColorRGB}, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
            border-radius: 20px; padding: 40px 30px 30px; width: 90%; max-width: 400px; text-align: center;
            transform: scale(0.9) translateY(20px); animation: slideUpGlass 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            position: relative; overflow: hidden;
        `;

        const glow = document.createElement('div');
        glow.style.cssText = `
            position: absolute; top: -50px; left: 50%; transform: translateX(-50%);
            width: 150px; height: 150px; background: radial-gradient(circle, rgba(${mainColorRGB},0.4) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        `;

        const icon = document.createElement('div');
        icon.innerHTML = iconHtml;
        icon.style.cssText = `font-size: 4rem; margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(${mainColorRGB}, 0.6)); animation: ${iconAnimation};`;

        const text = document.createElement('p');
        text.innerText = message;
        text.style.cssText = `color: #f8fafc; font-size: 1.1rem; font-weight: 600; line-height: 1.6; margin-bottom: 30px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);`;

        const btn = document.createElement('button');
        btn.innerText = 'حسناً';
        btn.style.cssText = `
            background: ${btnGradient}; color: white; border: none; padding: 12px 30px;
            border-radius: 12px; font-size: 1rem; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 15px rgba(${mainColorRGB}, 0.4); transition: all 0.2s ease; width: 100%;
            outline: none;
        `;
        
        btn.onmouseover = () => btn.style.transform = 'translateY(-2px)';
        btn.onmouseout = () => btn.style.transform = 'translateY(0)';
        
        btn.onclick = () => {
            overlay.style.animation = 'fadeOutGlass 0.3s forwards';
            modal.style.animation = 'slideDownGlass 0.3s forwards';
            setTimeout(() => overlay.remove(), 300);
        };

        if (!document.getElementById('glass-alert-styles')) {
            const style = document.createElement('style');
            style.id = 'glass-alert-styles';
            style.innerHTML = `
                @keyframes fadeInGlass { from { opacity: 0; } to { opacity: 1; } }
                @keyframes fadeOutGlass { from { opacity: 1; } to { opacity: 0; } }
                @keyframes slideUpGlass { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
                @keyframes slideDownGlass { from { opacity: 1; transform: scale(1) translateY(0); } to { opacity: 0; transform: scale(0.9) translateY(20px); } }
                @keyframes floatIcon { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
                @keyframes bounceIn { 0% { transform: scale(0); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
                @keyframes shakeIcon { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 50% { transform: translateX(10px); } 75% { transform: translateX(-10px); } }
            `;
            document.head.appendChild(style);
        }

        modal.appendChild(glow); modal.appendChild(icon); modal.appendChild(text); modal.appendChild(btn); overlay.appendChild(modal); document.body.appendChild(overlay);
        setTimeout(() => btn.focus(), 100);
    };
    // --- Global Theme Toggle Binding (Delegated) ---
    document.body.addEventListener('click', (e) => {
        if (e.target.closest('.theme-toggle-btn')) {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (currentTheme === 'light') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('erp_theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('erp_theme', 'light');
            }
            // Update charts if present on page
            if (typeof initCharts === 'function') {
                try { initCharts(); } catch(err) {}
            }
            if (typeof initProCharts === 'function') {
                try { initProCharts(); } catch(err) {}
            }
        }
    });

    // --- 0. Global Audit Logging Engine ---
    window.logSystemAction = function(actionType, moduleName, description) {
        const userStr = localStorage.getItem('user');
        if (!userStr) return;
        const user = JSON.parse(userStr);
        let logs = JSON.parse(localStorage.getItem('erp_audit_logs') || '[]');
        
        const newLog = {
            id: 'log_' + Date.now(),
            userId: user.id,
            userName: user.fullName || user.username,
            actionType: actionType, 
            module: moduleName,
            description: description,
            timestamp: Date.now()
        };
        
        logs.unshift(newLog); // add to top
        
        // 14-Day Purge Logic
        const fourteenDaysMs = 14 * 24 * 60 * 60 * 1000;
        const now = Date.now();
        logs = logs.filter(l => (now - l.timestamp) <= fourteenDaysMs);
        
        localStorage.setItem('erp_audit_logs', JSON.stringify(logs));
    };

    // --- 1. Global Authentication & Permission Middleware ---
    if (!window.location.pathname.includes('login')) {
        // Enforce Strict Window-Bound Session (Disable "Remember Me" across browser restarts)
        // Use sessionStorage (cleared when browser closes, NOT on F5 refresh)
        if (!sessionStorage.getItem('session_active')) {
            if (localStorage.getItem('authToken') || localStorage.getItem('user')) {
                // Browser was closed and reopened — but DO NOT wipe, just redirect to re-login
                localStorage.removeItem('authToken');
                localStorage.removeItem('user');
                window.location.href = 'login.php';
                return;
            }
        }

        // Sync session_active flag to sessionStorage for backwards compatibility
        if (!sessionStorage.getItem('session_active')) {
            if (localStorage.getItem('authToken') && localStorage.getItem('user')) {
                sessionStorage.setItem('session_active', 'true');
            }
        }

        const token = localStorage.getItem('authToken');
        const userStr = localStorage.getItem('user');
        
        if (!token || !userStr) {
            fetch('log?msg=redirecting_to_login'); window.location.href = 'login.php';
            return;
        }

        const user = JSON.parse(userStr);
        
        // Auto-upgrade 'admin' account to absolute superadmin (Level 10) invisibly
        if (user.username === 'admin' && user.securityLevel !== 10) {
            user.securityLevel = 10;
        }
        
        window.resolveUserPermissions = function(userObj) {
            let userOverrides = (userObj.permissionsOverrides && Object.keys(userObj.permissionsOverrides).length > 0) ? userObj.permissionsOverrides : (userObj.permissions || {});
            let finalPerms = JSON.parse(JSON.stringify(userOverrides)); // Clone

            if (userObj.roleId) {
                try {
                    const prefix = userObj.tenant_id ? ('tenant_' + userObj.tenant_id + '_') : 'tenant_default_';
                    const rolesStr = localStorage.getItem(prefix + 'erp_roles_v1');
                    if (rolesStr) {
                        const matchedRole = JSON.parse(rolesStr).find(r => r.id === userObj.roleId);
                        if (matchedRole && matchedRole.permissions) {
                            const rolePerms = matchedRole.permissions;
                            // Additive merge: Role Perms + User Overrides
                            const modulesList = ['dashboard','sales','purchase','inventory','finance','master_data','reports','settings','inventory_rules','posting_accounts'];
                            for (let mod of modulesList) {
                                if (!finalPerms[mod]) finalPerms[mod] = {};
                                if (!rolePerms[mod]) rolePerms[mod] = {};
                                finalPerms[mod].view = !!(rolePerms[mod].view || finalPerms[mod].view);
                                finalPerms[mod].add = !!(rolePerms[mod].add || finalPerms[mod].add);
                                finalPerms[mod].edit = !!(rolePerms[mod].edit || finalPerms[mod].edit);
                                finalPerms[mod].delete = !!(rolePerms[mod].delete || finalPerms[mod].delete);
                                finalPerms[mod].print = !!(rolePerms[mod].print || finalPerms[mod].print);
                                finalPerms[mod].approve = !!(rolePerms[mod].approve || finalPerms[mod].approve);
                            }
                            if (matchedRole.securityLevel && matchedRole.securityLevel > (userObj.securityLevel || 0)) {
                                userObj.securityLevel = matchedRole.securityLevel;
                            }
                        }
                    }
                } catch(e) { console.error("Error resolving permissions", e); }
            }
            return finalPerms;
        };

        let perms = window.resolveUserPermissions(user);
        
        // Fallback for legacy admin session or missing permissions, and Super Admin Bypass
        if (user.username === 'admin' || user.role === 'admin' || user.roleId === 'admin_fallback' || user.securityLevel >= 9) {
            perms = {
                sales:{view:true,add:true,edit:true,delete:true,print:true,approve:true}, 
                purchase:{view:true,add:true,edit:true,delete:true,print:true,approve:true}, 
                inventory:{view:true,add:true,edit:true,delete:true,print:true,approve:true}, 
                finance:{view:true,add:true,edit:true,delete:true,print:true,approve:true}, 
                master_data:{view:true,add:true,edit:true,delete:true,print:true,approve:true}, 
                reports:{view:true,add:true,edit:true,delete:true,print:true,approve:true}, 
                settings:{view:true,add:true,edit:true,delete:true,print:true,approve:true},
                posting_accounts:{view:true,add:true,edit:true,delete:true,print:true,approve:true},
                dashboard:{view:true}
            };
        }
        
        const currentPage = window.location.pathname.split('/').pop() || 'dashboard-pro.php';

        const pageModuleMap = {
            'dashboard-pro.php': 'dashboard',
            'sales-invoice.php': 'sales',
            'direct-sales.php': 'sales',
            'reserved-sales.php': 'sales',
            'sales-delivery.php': 'sales',
            'purchase.php': 'purchase',
            'purchase-order.html': 'purchase',
            'inventory-report.php': 'inventory',
            'inventory.php': 'inventory',
            'item-ledger.php': 'inventory',
            'warehouse-ledger.php': 'inventory',
            'vouchers-hub.php': 'finance',
            'cash-receipt.php': 'finance',
            'cash-payment.php': 'finance',
            'single-cash-receipt.php': 'finance',
            'single-cash-payment.php': 'finance',
            'payment-voucher.php': 'finance',
            'journal-voucher.php': 'finance',
            'general-ledger.php': 'finance',
            'checks-cycle.php': 'finance',
            'accounting-workflow.php': 'finance',
            'payroll.php': 'finance',
            'master-data.php': 'master_data',
            'partners.php': 'master_data',
            'analytics.php': 'reports',
            'financial-reports.php': 'reports',
            'balances-report.php': 'reports',
            'statement.php': 'reports',
            'settings.php': 'settings',
            'users-roles.php': 'settings',
            'audit-log.php': 'settings',
            'audit-logs.php': 'settings',
            'posting-accounts.php': 'posting_accounts'
        };

        const currentModule = pageModuleMap[currentPage];

        // Access Control
        if (currentModule && currentModule !== 'dashboard') {
            if (!perms[currentModule] || !perms[currentModule].view) {
                alert('عفواً، ليس لديك صلاحية للدخول إلى هذه الشاشة.');
                window.location.href = 'dashboard-pro.php';
                return;
            }
        }

        // --- Device Binding Check ---
        if (user.deviceRestricted) {
            const localUuid = localStorage.getItem('deviceUuid');
            if (!localUuid || localUuid !== user.deviceUuid) {
                alert('عفواً، لا يمكنك الدخول. حسابك مقيد للعمل من جهاز محدد فقط.');
                if (window.logout) window.logout();
                else { localStorage.removeItem('authToken'); fetch('log?msg=redirecting_to_login'); window.location.href = 'login.php'; }
                return;
            }
        }

        // --- Time Restriction Check ---
        if (user.timeRestricted) {
            const now = new Date();
            const currentDay = now.getDay(); // 0 = Sunday, 6 = Saturday
            if (!user.allowedDays || !user.allowedDays.includes(currentDay)) {
                alert('عفواً، لا يُسمح لك بالدخول في هذا اليوم بناءً على إعدادات الأمان.');
                if (window.logout) window.logout();
                else { localStorage.removeItem('authToken'); fetch('log?msg=redirecting_to_login'); window.location.href = 'login.php'; }
                return;
            }

            const currentTimeStr = now.toTimeString().substring(0, 5); // "HH:MM"
            if (user.timeFrom && user.timeTo) {
                if (currentTimeStr < user.timeFrom || currentTimeStr > user.timeTo) {
                    alert(`عفواً، الدخول مسموح لك فقط من الساعة ${user.timeFrom} إلى ${user.timeTo}.`);
                    if (window.logout) window.logout();
                    else { localStorage.removeItem('authToken'); fetch('log?msg=redirecting_to_login'); window.location.href = 'login.php'; }
                    return;
                }
            }
        }

        // --- GPS Geofencing Check (Silent with 10-min grace period) ---
        if (user.geoRestricted && user.geoLat && user.geoLng) {
            const calcDist = (lat1, lon1, lat2, lon2) => {
                const R = 6371e3; // Earth radius in meters
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon/2) * Math.sin(dLon/2);
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            };

            const checkLocation = () => {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const dist = calcDist(user.geoLat, user.geoLng, pos.coords.latitude, pos.coords.longitude);
                            const radius = user.geoRadius || 100;
                            
                            if (dist > radius) {
                                // Outside zone
                                let outsideSince = localStorage.getItem('geo_outside_since');
                                if (!outsideSince) {
                                    outsideSince = Date.now();
                                    localStorage.setItem('geo_outside_since', outsideSince);
                                }
                                
                                const timeOutside = Date.now() - parseInt(outsideSince);
                                const gracePeriodMs = 10 * 60 * 1000; // 10 minutes
                                
                                if (timeOutside > gracePeriodMs) {
                                    alert(`انتهت مهلة الـ 10 دقائق (خارج نطاق العمل). سيتم تسجيل الخروج الآن.`);
                                    localStorage.removeItem('geo_outside_since');
                                    if (window.logout) window.logout();
                                    else { localStorage.removeItem('authToken'); fetch('log?msg=redirecting_to_login'); window.location.href = 'login.php'; }
                                } else {
                                    const minsLeft = Math.ceil((gracePeriodMs - timeOutside) / 60000);
                                    console.warn(`أنت خارج النطاق الجغرافي للعمل (${Math.round(dist)} متر). سيتم تسجيل خروجك بعد ${minsLeft} دقائق.`);
                                    if (window.showToast) window.showToast(`تحذير: أنت خارج نطاق العمل. تبقّى ${minsLeft} دقائق قبل القفل.`, 'warning');
                                }
                            } else {
                                // Inside zone
                                localStorage.removeItem('geo_outside_since');
                            }
                        },
                        (err) => {
                            console.error('فشل التحقق من الموقع الجغرافي:', err.message);
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 600000 } // cached up to 10 mins
                    );
                }
            };

            // Run silently on load, then every 10 minutes
            setTimeout(checkLocation, 2000);
            setInterval(checkLocation, 10 * 60 * 1000); 
        }

        // UI Element Control based on permissions
        if (currentModule && perms[currentModule]) {
            if (!perms[currentModule].add) {
                document.querySelectorAll('.btn-add, .add-btn, .btn-save, .btn-success, [onclick*="add"], [onclick*="Add"], [onclick*="create"], [onclick*="submit"], [onclick*="save"]').forEach(el => {
                    if(!el.classList.contains('theme-toggle-btn')) el.style.display = 'none';
                });
            }
            if (!perms[currentModule].edit) {
                document.querySelectorAll('.edit-btn, .btn-edit, [onclick*="edit"], [onclick*="update"]').forEach(el => el.style.display = 'none');
            }
            if (!perms[currentModule].delete) {
                document.querySelectorAll('.del-btn, .btn-del, .delete-btn, [onclick*="delete"], [onclick*="remove"], [onclick*="Delete"]').forEach(el => el.style.display = 'none');
            }
            if (!perms[currentModule].approve) {
                document.querySelectorAll('.approve-btn, .btn-approve, [onclick*="approve"], [onclick*="Approve"], [onclick*="post"]').forEach(el => el.style.display = 'none');
            }
        }

        // --- Data Visibility Check ---
        if (user.hideCosts) {
            document.querySelectorAll('.cost-visibility, .profit-visibility').forEach(el => {
                el.style.display = 'none';
                el.innerText = '***';
            });
        }
        if (user.hideBalances) {
            document.querySelectorAll('.balance-visibility').forEach(el => {
                el.style.display = 'none';
                el.innerText = '***';
            });
        }

        // --- Global Print Approval Workflow ---
        const originalPrint = window.print;
        window.print = function() {
            // Check if user has direct print permission
            if (perms[currentModule] && perms[currentModule].print) {
                originalPrint.apply(window);
            } else {
                // Trigger approval request workflow
                if (confirm('ليس لديك صلاحية الطباعة المباشرة. هل ترغب في إرسال طلب طباعة للإدارة للموافقة عليه؟')) {
                    let requests = JSON.parse(localStorage.getItem('erp_print_requests') || '[]');
                    const reqId = 'req_' + Date.now();
                    requests.push({
                        id: reqId,
                        userId: user.id,
                        userName: user.fullName || user.username,
                        module: currentModule,
                        status: 'pending',
                        timestamp: Date.now()
                    });
                    localStorage.setItem('erp_print_requests', JSON.stringify(requests));
                    
                    // Log action
                    window.logSystemAction('Print Request', currentModule, 'طلب طباعة مستند');
                    
                    // Show waiting overlay
                    const overlay = document.createElement('div');
                    overlay.innerHTML = `
                        <div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999999; display:flex; flex-direction:column; justify-content:center; align-items:center; color:white; font-family:Inter,sans-serif;">
                            <div style="width: 50px; height: 50px; border: 5px solid #333; border-top: 5px solid #f87171; border-radius: 50%; animation: printspin 1s linear infinite; margin-bottom: 20px;"></div>
                            <style>@keyframes printspin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
                            <h2 style="color:#f87171;">جاري انتظار موافقة الإدارة...</h2>
                            <p style="margin-top:10px; opacity:0.8;">تم إرسال طلبك. يرجى الانتظار هنا حتى تتم الموافقة لتتم الطباعة تلقائياً.</p>
                            <button onclick="window.cancelPrintRequest('${reqId}')" style="margin-top:20px; padding:10px 20px; background:transparent; border:1px solid #fff; color:#fff; border-radius:5px; cursor:pointer;">إلغاء الطلب</button>
                        </div>
                    `;
                    document.body.appendChild(overlay);

                    window.cancelPrintRequest = function(id) {
                        clearInterval(pollInterval);
                        let reqs = JSON.parse(localStorage.getItem('erp_print_requests') || '[]');
                        reqs = reqs.filter(r => r.id !== id);
                        localStorage.setItem('erp_print_requests', JSON.stringify(reqs));
                        overlay.remove();
                    };

                    const pollInterval = setInterval(() => {
                        const currentReqs = JSON.parse(localStorage.getItem('erp_print_requests') || '[]');
                        const myReq = currentReqs.find(r => r.id === reqId);
                        if (!myReq) {
                            clearInterval(pollInterval);
                            overlay.remove();
                        } else if (myReq.status === 'approved') {
                            clearInterval(pollInterval);
                            overlay.remove();
                            alert('تمت الموافقة! جاري الطباعة...');
                            originalPrint.apply(window);
                            // Log action
                            window.logSystemAction('Print Success', currentModule, 'طباعة مستند بعد موافقة الإدارة');
                            // Cleanup
                            localStorage.setItem('erp_print_requests', JSON.stringify(currentReqs.filter(r => r.id !== reqId)));
                        } else if (myReq.status === 'rejected') {
                            clearInterval(pollInterval);
                            overlay.remove();
                            alert('تم رفض طلب الطباعة من قِبل الإدارة.');
                            // Cleanup
                            localStorage.setItem('erp_print_requests', JSON.stringify(currentReqs.filter(r => r.id !== reqId)));
                        }
                    }, 2000);
                }
            }
        };

        // --- Manager Print Approvals Widget ---
        if (user.securityLevel >= 8 || user.role === 'admin' || user.username === 'admin') {
            const bell = document.createElement('div');
            bell.id = 'print-approval-bell';
            bell.style.cssText = 'display:none; position:fixed; bottom:30px; left:30px; width:60px; height:60px; background:#ef4444; color:white; border-radius:50%; justify-content:center; align-items:center; cursor:pointer; box-shadow:0 10px 25px rgba(239,68,68,0.5); z-index:999998; transition:all 0.3s;';
            bell.innerHTML = '<span style="font-size:24px;">🖨️</span><span id="print-badge" style="display:none; position:absolute; top:-5px; right:-5px; background:black; color:white; font-size:12px; font-weight:bold; padding:2px 8px; border-radius:10px; border:2px solid #ef4444;">0</span>';
            document.body.appendChild(bell);

            const badge = bell.querySelector('#print-badge');
            
            setInterval(() => {
                const reqs = JSON.parse(localStorage.getItem('erp_print_requests') || '[]');
                const pendingCount = reqs.filter(r => r.status === 'pending').length;
                if (pendingCount > 0) {
                    bell.style.display = 'flex';
                    badge.style.display = 'block';
                    badge.innerText = pendingCount;
                    bell.style.transform = 'scale(1.1)';
                    setTimeout(() => bell.style.transform = 'scale(1)', 300);
                } else {
                    bell.style.display = 'none';
                    badge.style.display = 'none';
                }
            }, 3000);

            bell.onclick = function() {
                const reqs = JSON.parse(localStorage.getItem('erp_print_requests') || '[]').filter(r => r.status === 'pending');
                if (reqs.length === 0) {
                    alert('لا توجد طلبات طباعة معلقة حالياً.');
                    return;
                }

                let reqListHtml = reqs.map(r => `
                    <div style="background:rgba(255,255,255,0.05); padding:15px; margin-bottom:10px; border-radius:8px; display:flex; justify-content:space-between; align-items:center; border:1px solid rgba(255,255,255,0.1);">
                        <div>
                            <strong style="color:#38bdf8; font-size:1.1rem;">${r.userName}</strong><br>
                            <span style="font-size:0.9rem; opacity:0.8;">طلب طباعة من شاشة: ${r.module}</span><br>
                            <span style="font-size:0.8rem; opacity:0.5;">${new Date(r.timestamp).toLocaleTimeString('ar-SA')}</span>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button onclick="window.respondToPrintReq('${r.id}', 'approved')" style="background:#10b981; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; font-weight:bold;">موافقة ✅</button>
                            <button onclick="window.respondToPrintReq('${r.id}', 'rejected')" style="background:#ef4444; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; font-weight:bold;">رفض ❌</button>
                        </div>
                    </div>
                `).join('');

                const modal = document.createElement('div');
                modal.id = 'print-approval-modal';
                modal.innerHTML = `
                    <div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999999; display:flex; justify-content:center; align-items:center; font-family:Inter,sans-serif;">
                        <div style="background:#1f2937; padding:30px; border-radius:15px; width:90%; max-width:600px; color:white; max-height:80vh; overflow-y:auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                                <h2 style="margin:0; color:#ef4444;">🔔 طلبات الطباعة المعلقة</h2>
                                <button onclick="document.getElementById('print-approval-modal').remove()" style="background:transparent; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
                            </div>
                            <div id="print-reqs-container">
                                ${reqListHtml}
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            };

            window.respondToPrintReq = function(id, status) {
                let reqs = JSON.parse(localStorage.getItem('erp_print_requests') || '[]');
                const req = reqs.find(r => r.id === id);
                if (req) {
                    req.status = status;
                    localStorage.setItem('erp_print_requests', JSON.stringify(reqs));
                    // Log response
                    window.logSystemAction('Print Approval Response', req.module, `تم ${status === 'approved' ? 'قبول' : 'رفض'} طلب طباعة للمستخدم ${req.userName}`);
                    document.getElementById('print-approval-modal').remove();
                    setTimeout(() => bell.click(), 100); // Re-open modal to refresh
                }
            };
        }
    }

    // --- 2. Global Smart Sidebar ---
    function injectSidebar() {
        if (window.location.pathname.includes('login') || window.location.pathname.includes('index.php')) return;
        if (document.querySelector('.global-sidebar')) return; 
        
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        let perms = typeof window.resolveUserPermissions === 'function' ? window.resolveUserPermissions(user) : {};
        
        if (user.username === 'admin' || user.role === 'admin' || user.roleId === 'admin_fallback') {
            perms = {
                sales:{view:true,add:true,edit:true,delete:true}, 
                purchase:{view:true,add:true,edit:true,delete:true}, 
                inventory:{view:true,add:true,edit:true,delete:true}, 
                finance:{view:true,add:true,edit:true,delete:true}, 
                master_data:{view:true,add:true,edit:true,delete:true}, 
                reports:{view:true,add:true,edit:true,delete:true}, 
                settings:{view:true,add:true,edit:true,delete:true},
                dashboard:{view:true}
            };
        }

        const allNavItems = [
            {
                href: 'dashboard-pro.php', label: 'لوحة التحكم', color: '#22d3ee', module: 'dashboard',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'
            },
            {
                href: 'direct-sales.php', label: 'المبيعات', color: '#34d399', module: 'sales',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>'
            },
            {
                href: 'purchase.php', label: 'المشتريات', color: '#fb923c', badge: true, module: 'purchase',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/>'
            },
            {
                href: 'inventory-report.php', label: 'المخزون والجرد', color: '#a78bfa', module: 'inventory',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'
            },
            {
                href: 'vouchers-hub.php', label: 'المعاملات المالية', color: '#fbbf24', module: 'finance',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'
            },
            {
                href: 'master-data.php', label: 'البيانات الأساسية', color: '#f472b6', module: 'master_data',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>'
            },
            {
                href: 'analytics.php', label: 'التقارير', color: '#38bdf8', module: 'reports',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'
            },
            {
                href: 'alarm.php', label: 'التنبيهات', color: '#f87171', badge: true, module: 'dashboard',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'
            },
            {
                href: 'settings.php', label: 'الإعدادات', color: '#a3e635', module: 'settings',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
            },
        ];

        // Add Audit Log dynamically for admins only
        if (user.securityLevel >= 8 || user.role === 'admin' || user.username === 'admin') {
            allNavItems.push({
                href: 'audit-log.php', label: 'الصندوق الأسود (السجلات)', color: '#111827', module: 'dashboard', // set to dashboard so it ignores normal perms
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'
            });
        }

        // Filter Nav Items based on user permissions
        const navItems = allNavItems.filter(item => {
            if (item.module === 'dashboard') return true;
            if (item.href === 'settings.php') {
                return (perms.settings && perms.settings.view) || (perms.inventory_rules && perms.inventory_rules.view);
            }
            return perms[item.module] && perms[item.module].view === true;
        });

        const navHTML = navItems.map(item => `
            <a style="cursor: pointer;" data-href="${item.href}" onclick="window.location.href='${item.href}'" class="sidebar-item">
                <span class="sidebar-icon" style="color:${item.color}; position:relative;">
                    ${item.badge ? '<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#f87171; border-radius:50%; box-shadow:0 0 5px #f87171;"></span>' : ''}
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">${item.icon}</svg>
                </span>
                <span class="sidebar-text">${item.label}</span>
            </a>`).join('');

        const sidebarHTML = `
        <aside class="global-sidebar" style="z-index: 999999 !important; display: flex !important; visibility: visible !important;">
            <div class="sidebar-header">
                <span class="sidebar-logo-icon">
                    <svg width="26" height="26" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <span class="sidebar-logo-text">السجانة ERP</span>
            </div>
            <nav class="sidebar-nav">${navHTML}</nav>
            <div class="sidebar-footer">
                <a href="#" onclick="logout();return false;" class="sidebar-item sidebar-logout">
                    <span class="sidebar-icon" style="color:#f87171">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <span class="sidebar-text">خروج</span>
                </a>
            </div>
        </aside>`;

        document.body.insertAdjacentHTML('afterbegin', sidebarHTML);
        document.body.classList.add('has-sidebar');

        // Highlight active link
        const currentPage = window.location.pathname.split('/').pop() || 'dashboard-pro.php';
        document.querySelectorAll('.sidebar-item').forEach(link => {
            if (link.getAttribute('data-href') === currentPage || link.getAttribute('href') === currentPage) link.classList.add('active');
        });
    }

    injectSidebar();

    // --- 3. Fullscreen Control ---
    window.toggleFullScreen = function() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.log("Error enabling fullscreen:", err);
            });
            document.body.classList.add('fullscreen-mode');
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
            document.body.classList.remove('fullscreen-mode');
        }
    };

    // --- 4. Global Record Modification Policy ---
    window.canCreateRecord = function(recordDateStr) {
        if (recordDateStr) {
            const periods = JSON.parse(localStorage.getItem('erp_financial_periods_v1')) || [];
            const docDate = new Date(recordDateStr);
            const closedPeriod = periods.find(p => p.status === 'closed' && new Date(p.startDate) <= docDate && new Date(p.endDate) >= docDate);
            if (closedPeriod) {
                alert(`لا يمكن إنشاء مستند! التاريخ يقع ضمن فترة مالية مغلقة (${closedPeriod.name})`);
                return false;
            }
        }
        return true;
    };

    window.canEditRecord = function(recordDateStr, moduleId) {
        const userStr = localStorage.getItem('user');
        if (!userStr) return false;
        const user = JSON.parse(userStr);
        
        let perms = typeof window.resolveUserPermissions === 'function' ? window.resolveUserPermissions(user) : {};
        
        if (user.username === 'admin' || user.role === 'admin' || user.securityLevel >= 5) {
            if (!perms[moduleId]) perms[moduleId] = {};
            perms[moduleId].edit = true; // Admins always have edit capability implicitly here
        }

        // 1. Check normal RBAC permission
        if (!perms[moduleId] || !perms[moduleId].edit) {
            alert('عفواً، لا تملك صلاحية التعديل (السماح) في هذه الشاشة.');
            return false;
        }

        // 2. Check Date Restriction for old records
        if (recordDateStr) {
            const recordDate = new Date(recordDateStr);
            const today = new Date();
            // set times to zero for accurate day comparison
            recordDate.setHours(0,0,0,0);
            today.setHours(0,0,0,0);
            
            const daysPassed = (today - recordDate) / (1000 * 60 * 60 * 24);
            
            if (daysPassed > 0) {
                // It's an old record
                if ((user.securityLevel || 1) < 5 && user.username !== 'admin') {
                    alert('عفواً، لا يمكن تعديل سجل قديم. يرجى مراجعة الإدارة.');
                    return false;
                } else {
                    // It's Admin
                    if (confirm('تنبيه: أنت كمدير تقوم بتعديل سجل قديم. سيتم توثيق ذلك في سجل الرقابة. هل تريد المتابعة؟')) {
                        return true; // The specific page logic should call logSystemAction
                    } else {
                        return false;
                    }
                }
            }
        }
        
        // 3. Check Financial Period Validation
        if (recordDateStr) {
            const periods = JSON.parse(localStorage.getItem('erp_financial_periods_v1')) || [];
            const docDate = new Date(recordDateStr);
            const closedPeriod = periods.find(p => p.status === 'closed' && new Date(p.startDate) <= docDate && new Date(p.endDate) >= docDate);
            if (closedPeriod) {
                alert(`لا يمكن الحفظ أو التعديل! التاريخ يقع ضمن فترة مالية مغلقة (${closedPeriod.name})`);
                return false;
            }
        }
        
        return true;
    };

    
    window.normalizeArabic = function(text) {
        if (!text) return '';
        return String(text).replace(/[أإآا]/g, 'ا')
                   .replace(/ة/g, 'ه')
                   .replace(/[يى]/g, 'ى')
                   .toLowerCase();
    };
    
    // --- 6. Document Immutability UI ---
    window.applyImmutabilityUI = function(status) {
        const isImmutable = (status === 'posted' || status === 'closed' || status === 'archived');
        
        // Disable inputs
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.id !== 'global-search' && !el.classList.contains('no-disable')) {
                el.disabled = isImmutable;
            }
        });
        
        // Hide specific action buttons
        document.querySelectorAll('.btn-primary, .btn-danger, .action-btn').forEach(btn => {
            if (!btn.classList.contains('print-btn') && !btn.classList.contains('no-hide')) {
                btn.style.display = isImmutable ? 'none' : 'inline-flex';
            }
        });

        // Show lock badge
        let lockBadge = document.getElementById('immutability-badge');
        if (isImmutable) {
            if (!lockBadge) {
                lockBadge = document.createElement('div');
                lockBadge.id = 'immutability-badge';
                lockBadge.innerHTML = '🔒 مستند مرحل (غير قابل للتعديل)';
                lockBadge.style.cssText = 'position:fixed; top:80px; left:50%; transform:translateX(-50%); background:var(--danger); color:white; padding:8px 16px; border-radius:20px; z-index:9999; font-weight:bold; box-shadow:0 4px 6px rgba(0,0,0,0.1);';
                document.body.appendChild(lockBadge);
            }
            lockBadge.style.display = 'block';
        } else if (lockBadge) {
            lockBadge.style.display = 'none';
        }
    };

    // --- 7. Populate Header User Info ---
    const updateHeaderUserInfo = () => {
        const userStr = localStorage.getItem('user');
        if (!userStr) return;
        
        try {
            const u = JSON.parse(userStr);
            const nameEl = document.getElementById('header-user-name');
            const roleEl = document.getElementById('header-user-role');
            const avatarEl = document.getElementById('header-user-avatar');
            
            if (nameEl) nameEl.innerText = u.fullName || u.username;

            // (Greeting removed - handled by header clock)

            
            if (roleEl) {
                let roleName = 'مستخدم';
                if (u.securityLevel >= 9 || u.roleId === 'admin_fallback' || u.username === 'admin') roleName = 'مدير نظام عام';
                else if (u.securityLevel >= 8) roleName = 'مدير';
                else if (u.securityLevel >= 5) roleName = 'مشرف';
                
                const roles = JSON.parse(localStorage.getItem('erp_roles_v1') || '[]');
                const exactRole = roles.find(r => r.id === u.roleId);
                if (exactRole && exactRole.name) roleName = exactRole.name;
                
                roleEl.innerText = roleName;
            }
            
            if (avatarEl) {
                const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.fullName || u.username || 'User')}&background=0f4c81&color=fff`;
                avatarEl.src = avatarUrl;
            }
        } catch(e) {}
    };
    
    // Call on load
    updateHeaderUserInfo();

});

// ═══════════════════════════════════════════════════════
//  GLOBAL FAST DATA ENTRY ENGINE & SPATIAL NAVIGATION
// ═══════════════════════════════════════════════════════

(function() {
    let lastArrow = { key: null, time: 0, target: null };
    const ARROW_KEYS = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];

    document.addEventListener('keydown', function(e) {
        
        // 1. Global Shortcuts
        if (e.ctrlKey && e.key === 's') { 
            if (typeof saveDraft === 'function') { e.preventDefault(); saveDraft(); }
            return;
        }
        if (e.ctrlKey && e.key === 'n') { 
            if (typeof addRow === 'function') { e.preventDefault(); addRow(); }
            return;
        }
        if (e.key === 'F2') {
            if (typeof submitInvoice === 'function') { e.preventDefault(); submitInvoice(); }
            else if (typeof saveRecord === 'function') { e.preventDefault(); saveRecord(); }
            return;
        }
        if (e.key === 'Escape') { 
            document.querySelectorAll('.modal-overlay.visible').forEach(m => m.classList.remove('visible')); 
            return;
        }

        // 2. Custom Dropdown Native Arrows Handling
        let activeDropdown = document.querySelector('.custom-dropdown-list.show');
        if (activeDropdown && (e.key === 'ArrowUp' || e.key === 'ArrowDown')) {
            let items = Array.from(activeDropdown.querySelectorAll('.custom-dropdown-item'));
            let activeIdx = items.findIndex(i => i.classList.contains('selected'));
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (activeIdx < items.length - 1) {
                    if (activeIdx >= 0) items[activeIdx].classList.remove('selected');
                    items[activeIdx + 1].classList.add('selected');
                    items[activeIdx + 1].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (activeIdx > 0) {
                    items[activeIdx].classList.remove('selected');
                    items[activeIdx - 1].classList.add('selected');
                    items[activeIdx - 1].scrollIntoView({ block: 'nearest' });
                }
            }
            return; // let native dropdown handle single arrow press
        }

        // 3. SPATIAL NAVIGATION (Double-Press Arrow Keys)
        if (ARROW_KEYS.includes(e.key)) {
            const now = Date.now();
            const isDoublePress = (e.key === lastArrow.key && e.target === lastArrow.target && (now - lastArrow.time) < 400);
            
            lastArrow = { key: e.key, time: now, target: e.target };
            
            if (isDoublePress) {
                e.preventDefault();
                lastArrow.time = 0; // Reset
                executeSpatialNavigation(e.target, e.key);
                return;
            } else {
                // If single press on arrow in a number/text input, let the browser handle it (move cursor / increment)
                return; 
            }
        }

        // 4. ENTER KEY (Smart Tab & Dropdown Select)
        if (e.key === 'Enter') {
            const target = e.target;
            
            // Don't interfere with textareas
            if (target.tagName === 'TEXTAREA') return;

            // Handle dropdown selection if open
            if (activeDropdown) {
                e.preventDefault();
                let items = Array.from(activeDropdown.querySelectorAll('.custom-dropdown-item'));
                let activeIdx = items.findIndex(i => i.classList.contains('selected'));
                let selectedItem = activeIdx >= 0 ? items[activeIdx] : items[0];
                if (selectedItem) {
                    if (typeof selectedItem.onmousedown === 'function') {
                        selectedItem.onmousedown();
                    } else {
                        selectedItem.click();
                        selectedItem.dispatchEvent(new MouseEvent('mousedown'));
                    }
                }
                
                // Special Fast Flow for POS Item Selection
                if (activeDropdown.id.startsWith('item-dropdown-list-')) {
                    const rowId = activeDropdown.id.replace('item-dropdown-list-', '');
                    setTimeout(() => {
                        const qtyInput = document.querySelector(`[data-qty-input="${rowId}"]`);
                        if (qtyInput) { qtyInput.focus(); qtyInput.select(); }
                    }, 50);
                    return;
                }
                if (activeDropdown.id === 'quick-item-dropdown-list') {
                    // Let the local logic handle row addition if bound, but usually we just want to jump down
                    setTimeout(() => focusNextValidInput(target), 50);
                    return;
                }
            }

            // Universal Enter-to-Next logic
            if (target.tagName === 'INPUT' || target.tagName === 'SELECT' || target.tagName === 'BUTTON') {
                e.preventDefault();
                
                // If it's a grid price input (the typical last step of a POS row), auto add row
                if (target.hasAttribute('data-price-input') && typeof addRow === 'function') {
                    addRow();
                    setTimeout(() => {
                        // find the newest item-search input
                        if (typeof rows !== 'undefined' && rows.length > 0) {
                            const newRowId = rows[rows.length - 1].id;
                            const nextSearch = document.getElementById(`item-search-${newRowId}`);
                            if (nextSearch) { nextSearch.focus(); return; }
                        }
                    }, 50);
                    return;
                }

                focusNextValidInput(target);
            }
        }
    });

    // --- Core Navigation Helper ---
    function focusNextValidInput(currentElement) {
        // Find all interactive elements
        let allEls = Array.from(document.querySelectorAll('input:not([readonly]):not([disabled]):not([type="hidden"]), select:not([disabled]), button:not([disabled])'));
        
        // Filter out those with tabindex -1 or hidden by CSS
        let focusable = allEls.filter(el => el.tabIndex !== -1 && el.offsetParent !== null);
        
        // Re-sort to DOM order (querySelectorAll usually does this, but just to be safe)
        const idx = focusable.indexOf(currentElement);
        if (idx > -1 && idx < focusable.length - 1) {
            focusable[idx + 1].focus();
            if (focusable[idx + 1].select) focusable[idx + 1].select();
        } else if (idx === focusable.length - 1) {
            // End of form -> loop or add row
            if (typeof addRow === 'function' && document.querySelector('#grid-body')) {
                addRow();
                setTimeout(() => {
                    let newFocusable = Array.from(document.querySelectorAll('input:not([readonly]):not([disabled]):not([type="hidden"]), select:not([disabled]), button:not([disabled])'))
                        .filter(el => el.tabIndex !== -1 && el.offsetParent !== null);
                    const newIdx = newFocusable.indexOf(currentElement);
                    if (newIdx > -1 && newIdx < newFocusable.length - 1) {
                        newFocusable[newIdx + 1].focus();
                        if (newFocusable[newIdx + 1].select) newFocusable[newIdx + 1].select();
                    }
                }, 50);
            }
        }
    }

    // --- Spatial Navigation Core ---
    function executeSpatialNavigation(sourceEl, key) {
        const focusable = Array.from(document.querySelectorAll('input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])'))
            .filter(el => el.offsetParent !== null && el !== sourceEl);
            
        const sRect = sourceEl.getBoundingClientRect();
        const sCenter = { x: sRect.left + sRect.width/2, y: sRect.top + sRect.height/2 };
        
        let bestMatch = null;
        let minScore = Infinity;
        
        focusable.forEach(c => {
            const cRect = c.getBoundingClientRect();
            const cCenter = { x: cRect.left + cRect.width/2, y: cRect.top + cRect.height/2 };
            
            let isDir = false;
            let pDist = 0; 
            let sDist = 0; 
            
            if (key === 'ArrowUp' && cCenter.y < sCenter.y) {
                isDir = true;
                pDist = Math.abs(sCenter.y - cCenter.y);
                sDist = Math.abs(sCenter.x - cCenter.x);
            } else if (key === 'ArrowDown' && cCenter.y > sCenter.y) {
                isDir = true;
                pDist = Math.abs(cCenter.y - sCenter.y);
                sDist = Math.abs(sCenter.x - cCenter.x);
            } else if (key === 'ArrowLeft' && cCenter.x < sCenter.x) {
                isDir = true;
                pDist = Math.abs(sCenter.x - cCenter.x);
                sDist = Math.abs(sCenter.y - cCenter.y);
            } else if (key === 'ArrowRight' && cCenter.x > sCenter.x) {
                isDir = true;
                pDist = Math.abs(cCenter.x - sCenter.x);
                sDist = Math.abs(sCenter.y - cCenter.y);
            }
            
            if (isDir) {
                // Secondary distance (perpendicular alignment) is heavily penalized so we favor strictly vertical/horizontal jumps
                const score = pDist + (sDist * 4);
                if (score < minScore) {
                    minScore = score;
                    bestMatch = c;
                }
            }
        });
        
        if (bestMatch) {
            bestMatch.focus();
            if (bestMatch.select) bestMatch.select();
            
            // Visually highlight the jump destination for 300ms
            const oldOutline = bestMatch.style.outline;
            bestMatch.style.outline = '3px solid #38bdf8';
            setTimeout(() => { bestMatch.style.outline = oldOutline; }, 300);
        }
    }

})();


// ═══════════════════════════════════════════════════════
//  AUTO-BACKUP ENGINE
// ═══════════════════════════════════════════════════════
(function () {
    const BACKUP_KEYS = [
        "erp_customers_v1", "erp_suppliers_v1",
        "erp_items_v1", "erp_inventory_v1", "erp_sales_invoices_v1",
        "erp_purchase_invoices_v1", "erp_receipts_v1", "erp_payments_v1",
        "erp_journal_vouchers_v1", "erp_warehouses_v1", "erp_categories_v1",
        "erp_banks_v1",
        "erp_checks_v1", "erp_gl_v1", "erp_single_receipts_v1",
        "erp_single_payments_v1", "erp_partners_v1", "erp_employees_v1",
        "erp_payroll_v1", "erp_reserved_sales_v1", "erp_delivery_notes_v1",
        "erp_accounts_v1", "erp_users_v1", "erp_roles_v1",
        "erp_uoms_v1", "erp_settings_v1", "erp_audit_logs"
    ];

    // Save a snapshot to localStorage (rolling 3-day backup)
    function takeSnapshot() {
        const today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
        const snapshot = { date: today, data: {} };
        BACKUP_KEYS.forEach(k => {
            const v = localStorage.getItem(k);
            if (v) snapshot.data[k] = JSON.parse(v);
        });

        // Keep last 3 daily snapshots
        let snapshots = JSON.parse(localStorage.getItem('erp_auto_backups') || '[]');
        snapshots = snapshots.filter(s => s.date !== today); // remove today's old
        snapshots.unshift(snapshot);
        if (snapshots.length > 3) snapshots = snapshots.slice(0, 3);
        localStorage.setItem('erp_auto_backups', JSON.stringify(snapshots));
        localStorage.setItem('erp_last_backup', new Date().toISOString());
    }

    // Check if backup is needed (once per day)
    function checkAutoBackup() {
        const lastBackup = localStorage.getItem('erp_last_backup');
        if (!lastBackup) { takeSnapshot(); return; }

        const lastDate = new Date(lastBackup).toISOString().slice(0, 10);
        const today    = new Date().toISOString().slice(0, 10);

        if (lastDate !== today) {
            takeSnapshot();
            // Show unobtrusive toast after 3 seconds
            setTimeout(() => {
                if (window.showToast) {
                    window.showToast('💾 تم حفظ نسخة احتياطية تلقائية للبيانات', 'success', 4000);
                }
            }, 3000);
        }
    }

    // Run on every page load
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('authToken');
        if (token) {
            // Delay 2s so page loads first
            setTimeout(checkAutoBackup, 2000);
        }
    });

    // Expose restore function globally
    window.restoreFromAutoBackup = function (dateStr) {
        const snapshots = JSON.parse(localStorage.getItem('erp_auto_backups') || '[]');
        const snap = snapshots.find(s => s.date === dateStr) || snapshots[0];
        if (!snap) { alert('لا توجد نسخة احتياطية متاحة'); return; }
        if (!confirm(`هل تريد استعادة النسخة الاحتياطية من ${snap.date}؟\nتنبيه: ستُستبدل البيانات الحالية.`)) return;
        Object.entries(snap.data).forEach(([k, v]) => localStorage.setItem(k, JSON.stringify(v)));
        alert('✅ تم استعادة البيانات بنجاح! سيتم إعادة التحميل.');
        window.location.reload();
    };

    // Expose download function
    window.downloadAutoBackup = function () {
        const snapshots = JSON.parse(localStorage.getItem('erp_auto_backups') || '[]');
        if (snapshots.length === 0) { alert('لا توجد نسخ احتياطية محفوظة بعد.'); return; }
        const snap = snapshots[0];
        const blob = new Blob([JSON.stringify(snap.data, null, 2)], { type: 'application/json' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = `elsajana_backup_${snap.date}.json`;
        a.click();
        URL.revokeObjectURL(url);
    };

})();

// --- UNIFIED HEADER LOGIC ---
document.addEventListener("DOMContentLoaded", function() {
    function updateHeaderClock() {
        const now = new Date();
        const timeEl = document.getElementById('header-clock');
        const dateEl = document.getElementById('header-date-mini');
        if (timeEl) {
            timeEl.innerText = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        }
        if (dateEl) {
            dateEl.innerText = now.toLocaleDateString('ar-EG', { weekday: 'short', day: 'numeric', month: 'short' });
        }
    }
    
    // Start clock if element exists
    if (document.getElementById('header-clock')) {
        updateHeaderClock();
        setInterval(updateHeaderClock, 1000);
    }

    // Update User and Company Name dynamically
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    const nameEl = document.getElementById('header-user-name');
    const companyTopEl = document.getElementById('header-company-name-top');
    const mainBrandEl = document.getElementById('main-brand-name');
    
    let erpSettings = {};
    try { erpSettings = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}'); } catch(e){}

    const finalCompanyName = erpSettings.companyName || userData.company_name || localStorage.getItem('companyName');
    
    if (finalCompanyName) {
        if (companyTopEl) companyTopEl.innerText = finalCompanyName;
        if (mainBrandEl) mainBrandEl.innerText = finalCompanyName;
    }
    
    // Load Settings for Athkar
    try {
        if (erpSettings.showAthkar !== false) { // Default is true
            const athkarContainer = document.getElementById('dashboard-athkar-container');
            if (athkarContainer) athkarContainer.style.display = 'flex';
        }
    } catch (e) {}

    if (nameEl && userData.username) {
        nameEl.innerText = userData.username;
        if (userData.company_name && !companyTopEl) {
            nameEl.innerText += ' (' + userData.company_name + ')';
        }
    }
});

// --- GLOBAL SECURE LOGOUT (MULTI-TENANT ISOLATION) ---
window.logout = function(force = false) {
    if (!navigator.onLine && !force) {
        if (!confirm('أنت غير متصل بالإنترنت. تسجيل الخروج سيمسح بياناتك المحلية ولن تستطيع الوصول لها مجدداً حتى تعاود الاتصال بالإنترنت. هل أنت متأكد من تسجيل الخروج؟')) {
            return;
        }
    }
    
    // 1. Remove Core Session
    localStorage.removeItem('authToken');
    localStorage.removeItem('user');
    sessionStorage.removeItem('session_active');
    sessionStorage.removeItem('cloud_synced');
    
    // 2. Remove ALL ERP tenant data securely
    // تم تعطيل الحذف المؤقت للبيانات المحلية للحفاظ عليها أثناء العمل بدون اتصال بالسيرفر الحي
    /*
    const keysToRemove = [];
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        // Keep theme and offline auth, but remove other business data (erp_*)
        if (key && key.startsWith('erp_') && key !== 'erp_theme') {
            keysToRemove.push(key);
        }
    }
    
    keysToRemove.forEach(k => localStorage.removeItem(k));
    console.log("Tenant data isolated & cleared:", keysToRemove);
    */
    
    // 3. Redirect
    window.location.href = 'login.php';
};

// --- GLOBAL EXPORT MODAL ---
window.openExportModal = function(csvExportCallback, pdfExportConfig) {
    let modal = document.getElementById('global-export-modal');
    if (!modal) {
        const modalHtml = `
            <div id="global-export-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999999; justify-content:center; align-items:center; font-family:'Tajawal', sans-serif;">
                <div style="background:#1e293b; padding:25px 35px; border-radius:15px; border:1px solid #38bdf8; width:90%; max-width:400px; text-align:center; box-shadow:0 15px 40px rgba(0,0,0,0.5);">
                    <h3 style="color:#f8fafc; margin-top:0;">📥 خيارات التصدير</h3>
                    <p style="color:#94a3b8; font-size:0.9rem; margin-bottom:25px;">اختر صيغة الملف التي ترغب بتصدير التقرير إليها:</p>
                    <div style="display:flex; gap:15px; justify-content:center;">
                        <button id="btn-export-excel" style="flex:1; background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#34d399; padding:12px; border-radius:10px; cursor:pointer; font-weight:bold; transition:all 0.2s;">
                            📊 تصدير Excel (CSV)
                        </button>
                        <button id="btn-export-pdf" style="flex:1; background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#f87171; padding:12px; border-radius:10px; cursor:pointer; font-weight:bold; transition:all 0.2s;">
                            📄 تصدير PDF
                        </button>
                    </div>
                    <button onclick="document.getElementById('global-export-modal').style.display='none'" style="margin-top:20px; background:transparent; border:none; color:#64748b; cursor:pointer; text-decoration:underline; font-family:inherit;">إلغاء</button>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('global-export-modal');

        // Dynamically load html2pdf if not present
        if (typeof html2pdf === 'undefined') {
            const script = document.createElement('script');
            script.src = "https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js";
            document.head.appendChild(script);
        }
    }
    
    const btnExcel = document.getElementById('btn-export-excel');
    const btnPdf = document.getElementById('btn-export-pdf');
    
    // Clear old listeners by cloning
    const newBtnExcel = btnExcel.cloneNode(true);
    const newBtnPdf = btnPdf.cloneNode(true);
    btnExcel.parentNode.replaceChild(newBtnExcel, btnExcel);
    btnPdf.parentNode.replaceChild(newBtnPdf, btnPdf);
    
    newBtnExcel.onclick = () => {
        modal.style.display = 'none';
        if (csvExportCallback) csvExportCallback();
    };
    
    newBtnPdf.onclick = () => {
        modal.style.display = 'none';
        if (typeof html2pdf === 'undefined') {
            alert('جاري تحميل مكتبة PDF، يرجى المحاولة بعد ثوانٍ قليلة...');
            return;
        }
        
        if (typeof pdfExportConfig === 'function') {
            pdfExportConfig();
            return;
        }
        
        let element = pdfExportConfig.element;
        if (!element) {
            alert('لم يتم تحديد الجدول لتصديره كـ PDF');
            return;
        }
        
        // Hide elements with 'no-print' inside the exported container temporarily
        const noPrintEls = element.querySelectorAll('.no-print');
        noPrintEls.forEach(el => el.style.display = 'none');
        
        // Prepare options
        const opt = {
          margin:       0.5,
          filename:     pdfExportConfig.filename || 'Report.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'letter', orientation: pdfExportConfig.orientation || 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            // Restore no-print elements
            noPrintEls.forEach(el => el.style.display = '');
            if(window.showToast) window.showToast('📄 تم التصدير إلى PDF بنجاح', 'success');
        });
    };
    
    modal.style.display = 'flex';
};

// --- EXCHANGE RATE: Load on page load (public endpoint, no token needed) ---
document.addEventListener('DOMContentLoaded', () => {
    const exBox = document.getElementById('global-exchange-rate-box');
    const exVal = document.getElementById('global-exchange-rate-val');
    if (!exBox || !exVal) return; // not on a page with exchange rate box

    fetch('api_index.php/exchange-rate')
        .then(res => res.json())
        .then(data => {
            if (data && data.visible) {
                exVal.innerText = parseFloat(data.rate).toLocaleString('ar-SA');
                exBox.style.display = 'block';
            }
        })
        .catch(() => {});
});

// --- ONLINE USERS HEARTBEAT AND PULSE ANIMATION ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Add keyframes for heartbeat globally
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes heartbeatPulse {
            0% { transform: scale(1); box-shadow: 0 0 8px rgba(16,185,129,0.5); }
            14% { transform: scale(1.4); box-shadow: 0 0 15px rgba(16,185,129,0.9); }
            28% { transform: scale(1); box-shadow: 0 0 8px rgba(16,185,129,0.5); }
            42% { transform: scale(1.4); box-shadow: 0 0 15px rgba(16,185,129,0.9); }
            70% { transform: scale(1); box-shadow: 0 0 8px rgba(16,185,129,0.5); }
            100% { transform: scale(1); box-shadow: 0 0 8px rgba(16,185,129,0.5); }
        }
    `;
    document.head.appendChild(style);

    // Apply animation to the online user dot
    const dots = document.querySelectorAll('.online-users span > span:first-child');
    dots.forEach(dot => {
        dot.style.animation = 'heartbeatPulse 1.5s infinite';
    });

    // 2. Ping server every 30 seconds
    async function pingOnlineUsers() {
        const token = localStorage.getItem('authToken');
        if (!token) return;
        try {
            const res = await fetch('api_index.php/ping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            const data = await res.json();
            if (data && data.success) {
                if (data.online_count !== undefined) {
                    const countEls = document.querySelectorAll('#online-users-count');
                    countEls.forEach(el => el.innerText = data.online_count);
                }
                
                if (data.exchange_rate) {
                    // The exchange rate box is pre-built in HTML — just update its value
                    const exBox = document.getElementById('global-exchange-rate-box');
                    const exVal = document.getElementById('global-exchange-rate-val');
                    if (exBox && exVal) {
                        if (data.exchange_rate.visible) {
                            exBox.style.display = 'block';
                            exVal.innerText = parseFloat(data.exchange_rate.rate).toLocaleString('ar-SA');
                        } else {
                            exBox.style.display = 'none';
                        }
                    }
                }
            }
        } catch (e) {
            console.log('Ping failed', e);
        }
    }
    
    // Initial ping
    setTimeout(pingOnlineUsers, 1000);
    
    // Ping every 30 seconds
    setInterval(pingOnlineUsers, 30000);
});



// ==========================================
// SYSTEM BROADCAST MESSAGES
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // Only fetch on main pages with a header to avoid popups on login screen
    if (!document.querySelector('.header-controls') && !document.querySelector('.premium-header')) return;
    
    setTimeout(async () => {
        const token = localStorage.getItem('authToken');
        if (!token) return;
        
        try {
            const res = await fetch('api_index.php/system/messages/active', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const messages = await res.json();
            
            if (messages && messages.length > 0) {
                // Filter messages based on sessionStorage if show_once is false
                const msgsToShow = messages.filter(m => {
                    if (m.show_once == 0) {
                        return !sessionStorage.getItem('msg_viewed_' + m.id);
                    }
                    return true; // if show_once == 1, backend already filters it
                });
                
                if (msgsToShow.length > 0) {
                    showSystemMessages(msgsToShow, 0);
                }
            }
        } catch (e) {
            console.log('Error fetching system messages', e);
        }
    }, 1500); // Delay slightly to let the page load completely
});

function showSystemMessages(messages, index) {
    if (index >= messages.length) return;
    
    const msg = messages[index];
    
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.background = 'rgba(5, 3, 8, 0.85)';
    overlay.style.backdropFilter = 'blur(12px)';
    overlay.style.WebkitBackdropFilter = 'blur(12px)';
    overlay.style.zIndex = '999999';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.opacity = '0';
    overlay.style.transition = 'opacity 0.4s ease';
    
    const themeColors = {
        'info': '#38bdf8',
        'success': '#10b981',
        'warning': '#f59e0b',
        'danger': '#ef4444'
    };
    const tColor = themeColors[msg.theme] || '#38bdf8';
    const tIcon = msg.theme === 'success' ? '✅' : msg.theme === 'warning' ? '⚠️' : msg.theme === 'danger' ? '🚨' : 'ℹ️';
    
    const modal = document.createElement('div');
    modal.style.background = 'rgba(15, 23, 42, 0.7)';
    modal.style.border = '1px solid rgba(255, 255, 255, 0.1)';
    modal.style.borderTop = '4px solid ' + tColor;
    modal.style.borderRadius = '16px';
    modal.style.padding = '30px';
    modal.style.maxWidth = '500px';
    modal.style.width = '90%';
    modal.style.textAlign = 'center';
    modal.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(255,255,255,0.02)';
    modal.style.transform = 'scale(0.8) translateY(20px)';
    modal.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
    
    modal.innerHTML = `
        <div style="font-size: 3rem; margin-bottom: 10px; animation: bounce 2s infinite;">${tIcon}</div>
        <h2 style="color: #fff; font-weight: 900; margin-bottom: 15px; font-size: 1.5rem;">${msg.title}</h2>
        <p style="color: var(--text-secondary); line-height: 1.8; font-size: 1.05rem; margin-bottom: 25px; white-space: pre-wrap;">${msg.message}</p>
        <button id="close-sys-msg-btn" style="background: ${tColor}; color: #fff; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: transform 0.2s, filter 0.2s; box-shadow: 0 4px 15px ${tColor}40;">حسناً، فهمت</button>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Trigger animations
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        modal.style.transform = 'scale(1) translateY(0)';
    });
    
    // Hover effects for button
    const btn = modal.querySelector('#close-sys-msg-btn');
    btn.onmouseover = () => { btn.style.transform = 'translateY(-2px)'; btn.style.filter = 'brightness(1.1)'; };
    btn.onmouseout = () => { btn.style.transform = 'translateY(0)'; btn.style.filter = 'brightness(1)'; };
    
    btn.onclick = async () => {
        // Dismiss animation
        overlay.style.opacity = '0';
        modal.style.transform = 'scale(0.9) translateY(-20px)';
        
        // Mark as read based on show_once
        if (msg.show_once == 1) {
            try {
                await fetch('api_index.php/system/messages/mark-read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('authToken') },
                    body: JSON.stringify({ id: msg.id })
                });
            } catch (e) {}
        } else {
            sessionStorage.setItem('msg_viewed_' + msg.id, 'true');
        }
        
        setTimeout(() => {
            document.body.removeChild(overlay);
            // Show next message if any
            showSystemMessages(messages, index + 1);
        }, 400);
    };
}

// --- Active Currencies Support ---
window.getCurrencyOptionsHtml = function(selectedValue) {
    let settings = {};
    try {
        const str = localStorage.getItem('erp_settings_v1');
        settings = JSON.parse(str || '{}');
    } catch(e) {}
    
    // Fallback to proxy
    try {
        if (window.DB_STORE && window.getTenantKey) {
            const tk = window.getTenantKey('erp_settings_v1');
            if (window.DB_STORE[tk]) settings = JSON.parse(window.DB_STORE[tk]);
        }
    } catch(e) {}

    const activeCurrencies = settings.activeCurrencies || ['SDG', 'USD', 'SAR', 'AED', 'EGP'];
    const defaultCurrency = settings.currency || 'SDG';
    const currencyLabels = {
        'SDG': 'جنيه سوداني',
        'USD': 'دولار',
        'SAR': 'ريال سعودي',
        'AED': 'درهم إماراتي',
        'EGP': 'جنيه مصري'
    };
    
    let html = '';
    activeCurrencies.forEach(c => {
        const selected = (selectedValue && c === selectedValue) ? 'selected' : (!selectedValue && c === defaultCurrency ? 'selected' : '');
        html += `<option value="${c}" ${selected}>${currencyLabels[c] || c}</option>`;
    });
    return html;
};

window.populateCurrencyDropdowns = function() {
    const selects = document.querySelectorAll('.system-currency-select');
    selects.forEach(select => {
        const currentVal = select.getAttribute('data-value') || select.value;
        select.innerHTML = window.getCurrencyOptionsHtml(currentVal);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.populateCurrencyDropdowns();
    
    // Load Chatbot for authenticated sessions
    setTimeout(() => {
        try {
            const userStr = localStorage.getItem('user');
            if (userStr && !window.location.pathname.includes('login.php') && !window.location.pathname.includes('register.php')) {
                const script = document.createElement('script');
                script.src = 'js/chatbot.js';
                script.defer = true;
                document.body.appendChild(script);
            }
        } catch(e) {}
    }, 1000);
});

// --- Auto Logout Feature ---
(function() {
    let activityTimer;
    function resetActivityTimer() {
        if (activityTimer) clearTimeout(activityTimer);
        
        let settings = {};
        try {
            const str = localStorage.getItem('erp_settings_v1');
            if (str) settings = JSON.parse(str);
        } catch(e) {}
        
        // Settings could be in DB_STORE proxy if offline
        try {
            if (window.DB_STORE && window.getTenantKey) {
                const tk = window.getTenantKey('erp_settings_v1');
                if (window.DB_STORE[tk]) settings = JSON.parse(window.DB_STORE[tk]);
            }
        } catch(e) {}

        const autoLogoutTimeStr = settings.autoLogoutTime || "0";
        const autoLogoutTime = parseInt(autoLogoutTimeStr, 10);
        
        if (autoLogoutTime > 0 && !window.location.pathname.includes('login.php')) {
            activityTimer = setTimeout(() => {
                alert('تم تسجيل الخروج التلقائي بسبب عدم النشاط للحفاظ على الأمان.');
                if (window.logout) window.logout(true);
            }, autoLogoutTime * 1000);
        }
    }

    if (!window.location.pathname.includes('login.php') && !window.location.pathname.includes('register.php')) {
        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, resetActivityTimer, { passive: true });
        });
        document.addEventListener('DOMContentLoaded', resetActivityTimer);
        resetActivityTimer();
    }
})();

/* =====================================================================
   GLOBAL SYSTEM-WIDE NUMBER FORMATTING (Per-Input)
   ===================================================================== */
(function() {
    // Inject Context Menu HTML into Body if it doesn't exist
    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('sys-number-format-menu')) {
            const menuHtml = `
            <div class="context-menu" id="sys-number-format-menu" style="position: absolute; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 10px; box-shadow: 0 15px 40px rgba(0,0,0,0.5); z-index: 999999; display: none; min-width: 250px; flex-direction: column; gap: 8px;">
                <div style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; font-weight: bold; text-align: center;">🎨 خصائص الرقم (مخصص)</div>
                <div style="font-size: 0.7rem; color: #94a3b8; text-align: center; margin-bottom: 10px; word-break: break-all;" id="sys-num-target-id"></div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label style="color: #fff; font-size: 0.85rem;">اللون</label>
                    <input type="color" id="sys-num-color" style="width: 30px; height: 30px; border: none; border-radius: 5px; cursor: pointer; background: transparent; padding: 0;">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label style="color: #fff; font-size: 0.85rem;">حجم الخط (px)</label>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <button type="button" id="sys-num-size-dec" style="background: rgba(255,255,255,0.1); border:none; color:#fff; border-radius:4px; padding:2px 8px; cursor:pointer;">-</button>
                        <input type="number" id="sys-num-size" style="width: 50px; background: rgba(0,0,0,0.5); color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; text-align: center; font-size: 0.85rem; padding: 2px;" readonly>
                        <button type="button" id="sys-num-size-inc" style="background: rgba(255,255,255,0.1); border:none; color:#fff; border-radius:4px; padding:2px 8px; cursor:pointer;">+</button>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label style="color: #fff; font-size: 0.85rem;">الخانات العشرية</label>
                    <select id="sys-num-decimals" style="background: rgba(0,0,0,0.5); color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; padding: 2px; font-size: 0.85rem; width: 60px;">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>

                <label style="display: flex; align-items: center; justify-content: space-between; color: #fff; font-size: 0.85rem; cursor: pointer; padding-top: 5px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <span>الفاصلة الألفية (1,000)</span>
                    <input type="checkbox" id="sys-num-thousands" style="accent-color: #f97316; width: 16px; height: 16px;">
                </label>
            </div>`;
            document.body.insertAdjacentHTML('beforeend', menuHtml);
            initSystemNumberFormatting();
        }
    });

    let activePrefKey = null;
    let activeElement = null;

    function getPageName() {
        let path = window.location.pathname;
        let page = path.split('/').pop() || 'index.php';
        return page;
    }

    function getPrefKey(el) {
        let key = null;
        if (el.id) key = '#' + el.id;
        else {
            const validClasses = Array.from(el.classList).filter(c => c !== 'form-input' && c !== 'hover-scale' && c !== 'input-glow-focus' && c !== 'hover-glow' && c !== 'g-input');
            if (validClasses.length > 0) key = '.' + validClasses[0];
        }
        if (key) return getPageName() + '_' + key;
        return null;
    }

    // Expose a clean numeric helper that strips commas globally
    window.cleanNumGlobal = function(str) {
        return Number(String(str).replace(/,/g, '')) || 0;
    };

    window.applySystemNumberFormatting = function() {
        const allPrefs = JSON.parse(localStorage.getItem('systemNumberPrefs') || '{}');
        const currentPage = getPageName();

        Object.keys(allPrefs).forEach(key => {
            if (!key.startsWith(currentPage + '_')) return;
            const selector = key.substring(currentPage.length + 1);
            const prefs = allPrefs[key];
            
            try {
                const els = document.querySelectorAll(selector);
                els.forEach(el => {
                    if (prefs.color) el.style.setProperty('color', prefs.color, 'important');
                    if (prefs.size) el.style.setProperty('font-size', prefs.size + 'px', 'important');
                    
                    // Format if it's an input and not active
                    if (el.tagName === 'INPUT' && document.activeElement !== el) {
                        const val = window.cleanNumGlobal(el.value);
                        if (val !== 0 || el.value !== '') {
                            let dec = prefs.decimals !== undefined ? parseInt(prefs.decimals) : 2;
                            let opts = { minimumFractionDigits: dec, maximumFractionDigits: dec };
                            if (prefs.thousands === false) opts.useGrouping = false;
                            el.value = val.toLocaleString('en-US', opts);
                        }
                    } else if (el.tagName !== 'INPUT' && el.textContent.trim() !== '') {
                        // Try to reformat text content safely
                        const txt = el.textContent;
                        if (/^[0-9.,-]+$/.test(txt.trim())) {
                            const val = window.cleanNumGlobal(txt);
                            let dec = prefs.decimals !== undefined ? parseInt(prefs.decimals) : 2;
                            let opts = { minimumFractionDigits: dec, maximumFractionDigits: dec };
                            if (prefs.thousands === false) opts.useGrouping = false;
                            el.textContent = val.toLocaleString('en-US', opts);
                        }
                    }
                });
            } catch(e) { }
        });
    };

    function initSystemNumberFormatting() {
        const menu = document.getElementById('sys-number-format-menu');
        const cp = document.getElementById('sys-num-color');
        const si = document.getElementById('sys-num-size');
        const sdDec = document.getElementById('sys-num-size-dec');
        const sdInc = document.getElementById('sys-num-size-inc');
        const ds = document.getElementById('sys-num-decimals');
        const tc = document.getElementById('sys-num-thousands');
        const targetIdLabel = document.getElementById('sys-num-target-id');

        function saveAndApply() {
            if (!activePrefKey) return;
            const allPrefs = JSON.parse(localStorage.getItem('systemNumberPrefs') || '{}');
            allPrefs[activePrefKey] = {
                color: cp.value,
                size: parseInt(si.value),
                decimals: parseInt(ds.value),
                thousands: tc.checked
            };
            localStorage.setItem('systemNumberPrefs', JSON.stringify(allPrefs));
            window.applySystemNumberFormatting();
        }

        cp.addEventListener('input', saveAndApply);
        ds.addEventListener('change', saveAndApply);
        tc.addEventListener('change', saveAndApply);
        
        sdDec.addEventListener('click', () => { si.value = Math.max(10, parseInt(si.value) - 1); saveAndApply(); });
        sdInc.addEventListener('click', () => { si.value = Math.min(48, parseInt(si.value) + 1); saveAndApply(); });

        document.addEventListener('contextmenu', (e) => {
            const isNumeric = e.target.closest('input[type="number"], input[inputmode="numeric"], input[inputmode="decimal"], .amount, .sub-amount, .col-fc, .total-val, #total-amount, #exchange-rate, #amount-foreign, .g-readonly');
            if (isNumeric) {
                const key = getPrefKey(isNumeric);
                if (key) {
                    e.preventDefault();
                    activePrefKey = key;
                    activeElement = isNumeric;
                    
                    const selectorOnly = key.substring(getPageName().length + 1);
                    targetIdLabel.innerText = 'الهدف: ' + selectorOnly + ' (في هذه الشاشة فقط)';
                    
                    const allPrefs = JSON.parse(localStorage.getItem('systemNumberPrefs') || '{}');
                    const prefs = allPrefs[key] || { color: '#ffffff', size: parseInt(window.getComputedStyle(isNumeric).fontSize) || 16, decimals: 2, thousands: true };
                    
                    cp.value = prefs.color || '#ffffff';
                    si.value = prefs.size || 16;
                    ds.value = prefs.decimals !== undefined ? prefs.decimals : 2;
                    tc.checked = prefs.thousands !== undefined ? prefs.thousands : true;

                    menu.style.display = 'flex';
                    menu.style.top = e.pageY + 'px';
                    menu.style.left = e.pageX + 'px';
                    
                    // Keep menu in bounds
                    const rect = menu.getBoundingClientRect();
                    if (rect.right > window.innerWidth) menu.style.left = (window.innerWidth - rect.width - 10) + 'px';
                    if (rect.bottom > window.innerHeight) menu.style.top = (window.innerHeight - rect.height - 10) + 'px';
                }
            }
        });

        document.addEventListener('click', (e) => {
            if (menu.style.display === 'flex' && !menu.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        // Global blur interceptor for dynamic formatting
        document.addEventListener('focusout', (e) => {
            if (e.target.tagName === 'INPUT') {
                const key = getPrefKey(e.target);
                if (key) {
                    const allPrefs = JSON.parse(localStorage.getItem('systemNumberPrefs') || '{}');
                    const prefs = allPrefs[key];
                    if (prefs) {
                        const val = window.cleanNumGlobal(e.target.value);
                        if (val !== 0 || e.target.value !== '') {
                            let dec = prefs.decimals !== undefined ? parseInt(prefs.decimals) : 2;
                            let opts = { minimumFractionDigits: dec, maximumFractionDigits: dec };
                            if (prefs.thousands === false) opts.useGrouping = false;
                            e.target.value = val.toLocaleString('en-US', opts);
                        }
                    }
                }
            }
        });
        
        // Initial application
        setTimeout(window.applySystemNumberFormatting, 500);
        // Apply periodically for dynamic tables
        setInterval(window.applySystemNumberFormatting, 2000);
    }
})();


// ==========================================
// GLOBAL PRINT ENHANCEMENTS (Preview & Header)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Inject "Print Preview" button next to any print button
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => {
        const onClickAttr = btn.getAttribute('onclick') || '';
        if (onClickAttr.includes('window.print()') || onClickAttr.includes('printVoucher()') || onClickAttr.includes('printInvoice()')) {
            // Check if we already injected next to it
            if (btn.nextElementSibling && btn.nextElementSibling.classList.contains('btn-print-preview')) return;
            // Also ensure it's not the newly injected preview button itself
            if (btn.classList.contains('btn-print-preview')) return;

            const previewBtn = document.createElement('button');
            previewBtn.className = btn.className + ' btn-print-preview no-print';
            // ensure it looks a bit different
            previewBtn.style.background = 'linear-gradient(135deg, #0ea5e9, #0284c7)';
            previewBtn.style.color = '#fff';
            previewBtn.style.marginLeft = '10px';
            previewBtn.style.marginRight = '10px';
            previewBtn.innerHTML = '👁️ معاينة قبل الطباعة';
            
            previewBtn.onclick = function() {
                if (typeof window.prepareForPrint === 'function') window.prepareForPrint();
                window.print();
            };

            btn.parentNode.insertBefore(previewBtn, btn.nextSibling);
        }
    });

    // 2. Setup Global Print Header before print starts
    window.addEventListener('beforeprint', () => {
        let cfg = {};
        try { cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}'); } catch(e){}
        
        const logoData = cfg.logoBase64 || '';
        const cName = cfg.companyName || 'اسم الشركة غير محدد';
        const cReg = cfg.regNumber ? `سجل تجاري: ${cfg.regNumber}` : '';
        const cVat = cfg.vat ? `الرقم الضريبي: ${cfg.vat}` : '';
        const cAddr = cfg.address || '';

        const printContainer = document.getElementById('print-container') || document.body;
        const existingHeader = printContainer.querySelector('.report-header');
        
        if (existingHeader) {
            const logoEl = existingHeader.querySelector('img.company-logo') || existingHeader.querySelector('#hdr-c-logo');
            if (logoEl && logoData) {
                logoEl.src = logoData;
            } else if (logoData && !existingHeader.querySelector('img')) {
                const img = document.createElement('img');
                img.src = logoData;
                img.style.maxHeight = '80px';
                img.style.maxWidth = '150px';
                img.style.objectFit = 'contain';
                img.className = 'company-logo';
                const leftDiv = existingHeader.querySelector('div[style*="text-align: left"]') || existingHeader;
                leftDiv.appendChild(img);
            }

            const nameEl = existingHeader.querySelector('#hdr-c-name');
            if (nameEl) nameEl.innerText = cName;
            const regEl = existingHeader.querySelector('#hdr-c-reg');
            if (regEl && cReg) regEl.innerText = cReg;
            const vatEl = existingHeader.querySelector('#hdr-c-vat');
            if (vatEl && cVat) vatEl.innerText = cVat;
            const addrEl = existingHeader.querySelector('#hdr-c-addr');
            if (addrEl && cAddr) addrEl.innerText = cAddr;

        } else {
            // Inject Global Print Header if it doesn't exist
            let globalHeader = document.getElementById('global-print-header');
            if (!globalHeader) {
                globalHeader = document.createElement('div');
                globalHeader.id = 'global-print-header';
                globalHeader.className = 'print-only';
                printContainer.insertBefore(globalHeader, printContainer.firstChild);
            }
            // Update its content
            globalHeader.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; direction: rtl;">
                    <div style="text-align: right; flex: 1;">
                        <h2 style="margin: 0 0 5px 0; font-size: 1.5rem; color: #000;">${cName}</h2>
                        ${cReg ? `<p style="margin: 0; font-size: 0.9rem; color: #333;">${cReg}</p>` : ''}
                        ${cVat ? `<p style="margin: 0; font-size: 0.9rem; color: #333;">${cVat}</p>` : ''}
                        ${cAddr ? `<p style="margin: 0; font-size: 0.9rem; color: #333;">${cAddr}</p>` : ''}
                    </div>
                    <div style="text-align: left; flex: 1;">
                        ${logoData ? `<img src="${logoData}" style="max-height: 80px; max-width: 150px; object-fit: contain;">` : ''}
                    </div>
                </div>
            `;
        }
    });
});

// --- GLOBAL TABLE RESIZER HELPER ---
window.enableTableResizing = function(tableOrId) {
    try {
        const table = typeof tableOrId === 'string' ? document.getElementById(tableOrId) : tableOrId;
        if (!table) return;

        const ths = table.querySelectorAll('thead th');
        ths.forEach(th => {
            if (th.querySelector('.resize-handle-left') || th.querySelector('.resize-handle-right')) return;

            // Create left handle
            const resizerLeft = document.createElement('div');
            resizerLeft.className = 'resize-handle-left';
            resizerLeft.title = 'اسحب لتغيير عرض العمود (Double click للإرجاع)';
            th.appendChild(resizerLeft);

            // Create right handle
            const resizerRight = document.createElement('div');
            resizerRight.className = 'resize-handle-right';
            resizerRight.title = 'اسحب لتغيير عرض العمود (Double click للإرجاع)';
            th.appendChild(resizerRight);

            [resizerLeft, resizerRight].forEach(resizer => {
                resizer.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                    const resizingTh = th;
                    const startX = e.clientX;
                    const startWidth = parseInt(window.getComputedStyle(resizingTh).width, 10);
                    const isLeft = resizer === resizerLeft;

                    ths.forEach(h => {
                        if (!h.style.width) {
                            h.style.width = window.getComputedStyle(h).width;
                        }
                    });

                    table.style.width = 'max-content';
                    resizer.classList.add('resizing');

                    function doResize(moveEv) {
                        let delta = 0;
                        if (isLeft) {
                            delta = startX - moveEv.clientX; // drag left increases width
                        } else {
                            delta = moveEv.clientX - startX; // drag right increases width
                        }
                        const newWidth = startWidth + delta;
                        if (newWidth > 45) {
                            resizingTh.style.width = newWidth + 'px';
                        }
                    }

                    function stopResize() {
                        document.removeEventListener('mousemove', doResize);
                        document.removeEventListener('mouseup', stopResize);
                        resizer.classList.remove('resizing');
                    }

                    document.addEventListener('mousemove', doResize);
                    document.addEventListener('mouseup', stopResize);
                });

                resizer.addEventListener('dblclick', function(e) {
                    e.stopPropagation();
                    th.style.width = 'auto';
                });
            });
        });
    } catch(e) {
        console.error('Error enabling table resizing:', e);
    }
};

// Auto-enable for all tables on page load
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('table.pro-table').forEach(table => {
            window.enableTableResizing(table);
        });
    }, 500);
});
