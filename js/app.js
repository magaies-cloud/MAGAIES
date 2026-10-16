// --- CLOUD SYNC ENGINE (Offline-First to SaaS Bridge) ---
// ============================================================================
// [الدرع الثاني & الدرع الثالث]: رادار الأخطاء المباشر والبرمجة الدفاعية
// ============================================================================
(function() {
    // 📡 1. رادار الأخطاء المباشر (Global Error Radar): رصد وتتبع أي خطأ مع رقم السطر والمستخدم
    window.addEventListener('error', function(event) {
        try {
            const user = (typeof localGetItem === 'function') ? JSON.parse(localGetItem('user') || '{}') : {};
            const errorReport = {
                type: 'JavaScript Error',
                message: event.message || 'Unknown Error',
                filename: event.filename || window.location.href,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error ? event.error.stack : null,
                user_email: user.email || user.username || 'guest',
                tenant_id: user.tenant_id || 'default',
                url: window.location.href,
                time: new Date().toISOString()
            };
            console.warn("🛡️ [رادار الأخطاء]: تم رصد ومعالجة خطأ بنجاح:", errorReport);
            if (window.Sentry && typeof window.Sentry.captureException === 'function') {
                window.Sentry.captureException(event.error || new Error(event.message), { extra: errorReport });
            }
        } catch(e) {}
    });

    // 📡 2. رصد وعزل الوعود المرفوضة (Unhandled Promise Rejections) لمنع تجمد الشاشة
    window.addEventListener('unhandledrejection', function(event) {
        try {
            const user = (typeof localGetItem === 'function') ? JSON.parse(localGetItem('user') || '{}') : {};
            const reason = event.reason || {};
            const errorReport = {
                type: 'Unhandled Promise Rejection',
                message: reason.message || String(reason),
                stack: reason.stack || null,
                user_email: user.email || user.username || 'guest',
                url: window.location.href,
                time: new Date().toISOString()
            };
            console.warn("🛡️ [رادار الأخطاء - Promise]: تم احتواء الخطأ البرمجي ومنع تجمد الشاشة:", errorReport);
            if (window.Sentry && typeof window.Sentry.captureException === 'function') {
                window.Sentry.captureException(reason instanceof Error ? reason : new Error(String(reason)), { extra: errorReport });
            }
        } catch(e) {}
    });

    // ⚔️ 3. دالة دفاعية مساعدة لجلب عنصر واحد بأمان تام (Defensive Limit 1)
    window.defensiveFindSingle = function(array, predicate) {
        if (!Array.isArray(array)) return null;
        try {
            for (let i = 0; i < array.length; i++) {
                if (predicate(array[i], i, array)) {
                    return array[i]; // Return first match immediately (Limit 1)
                }
            }
        } catch(err) {
            console.warn("Defensive search fallback triggered:", err);
        }
        return null;
    };
})();

// 📡 AUTO-BOOTSTRAP GLOBAL BACKGROUND MEDIA & LIVE TV ACROSS ALL SCREENS
(function() {
    if (typeof window.playPersistentMedia !== 'function') {
        const existing = document.querySelector('script[src*="global_background_media"]');
        if (!existing) {
            const ms = document.createElement('script');
            ms.src = 'js/global_background_media.js?v=2026_seamless_v5';
            ms.defer = true;
            if (document.head) {
                document.head.appendChild(ms);
            } else {
                document.addEventListener('DOMContentLoaded', () => document.head.appendChild(ms));
            }
        }
    }
})();

// 🛡️ AUTO-BOOTSTRAP ENTERPRISE PERMISSIONS MANAGER ACROSS ALL SCREENS
(function() {
    if (typeof window.MagaieesPermissions === 'undefined') {
        const existing = document.querySelector('script[src*="permissions-manager"]');
        if (!existing) {
            const ps = document.createElement('script');
            ps.src = 'js/permissions-manager.js?v=' + (window.MAGAIEES_VERSION || '2026_enterprise_v2');
            ps.defer = false;
            if (document.head) {
                document.head.appendChild(ps);
            } else {
                document.addEventListener('DOMContentLoaded', () => document.head.appendChild(ps));
            }
        }
    }
})();

// 🔔 AUTO-BOOTSTRAP MODERN ALERTS & TOASTS ACROSS ALL SCREENS
(function() {
    if (typeof window.MagaieesAlerts === 'undefined') {
        const existing = document.querySelector('script[src*="modern-alerts"]');
        if (!existing) {
            const mas = document.createElement('script');
            mas.src = 'js/modern-alerts.js?v=' + (window.MAGAIEES_VERSION || '2026_enterprise_v2');
            mas.defer = false;
            if (document.head) {
                document.head.appendChild(mas);
            } else {
                document.addEventListener('DOMContentLoaded', () => document.head.appendChild(mas));
            }
        }
    }
})();

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
    try {
        return originalJSONParse.apply(JSON, arguments);
    } catch(e) {
        return null; // Defensive JSON Parse fallback
    }
};
(function() {
    const getBaseUrl = () => {
        const origin = window.location.origin;
        if (origin === 'https://elsajana.com' || origin === 'https://www.elsajana.com') {
            return 'https://c.elsajana.com/';
        }
        const path = window.location.pathname;
        let dir = path.substring(0, path.lastIndexOf('/'));
        if (dir.endsWith('/')) dir = dir.slice(0, -1);
        return origin + (dir ? dir : '') + "/";
    };
    const SYNC_PULL_URL = getBaseUrl() + "api_index.php/sync/pull";
    const SYNC_PUSH_URL = getBaseUrl() + "api_index.php/sync/push";

    // -------------------------------------------------------
    // TENANT ISOLATION — كل شركة لها بياناتها المنفصلة
    // -------------------------------------------------------
    let _cachedTenantPrefix = null;
    function getTenantPrefix() {
        if (_cachedTenantPrefix) return _cachedTenantPrefix;
        try {
            // Read directly from original storage to avoid infinite loops in proxy
            const userStr = localGetItem('user') || '{}';
            const user = JSON.parse(userStr);
            const tenantId = user.tenant_id || localGetItem('currentTenantId') || 'default';
            _cachedTenantPrefix = 'tenant_' + tenantId + '_';
            return _cachedTenantPrefix;
        } catch(e) {
            return 'tenant_default_';
        }
    }
    // Allow cache invalidation on login/logout
    window._clearTenantCache = function() { _cachedTenantPrefix = null; };

    // Convert a base key like "erp_customers_v1" to a tenant-specific key
    window.getTenantKey = function(baseKey) {
        // Keys that are NOT tenant-specific (global session settings only)
        const globalKeys = ['authToken', 'user', 'erp_theme', 'erp_lang', 'currentTenantId', 'session_active', 'deviceUuid', 'elsajana_global_media_state'];
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

        localforage.getItem = async function(key) {
            try {
                const val = await originalLfGet(key);
                return val !== null ? val : JSON.parse(localStorage.getItem(key) || 'null');
            } catch (err) {
                console.warn('[localforage override] getItem failed for ' + key + ':', err);
                try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch(e) { return null; }
            }
        };

        localforage.setItem = async function(key, value) {
            try {
                let strVal = typeof value === 'string' ? value : JSON.stringify(value);
                originalSetItem.call(localStorage, key, strVal);
            } catch(e) {}
            try {
                return await originalLfSet(key, value);
            } catch (err) {
                console.warn('[localforage override] setItem failed for ' + key + ':', err);
                return value;
            }
        };

        localforage.removeItem = async function(key) {
            try { originalRemoveItem.call(localStorage, key); } catch(e) {}
            try {
                return await originalLfRemove(key);
            } catch (err) {
                console.warn('[localforage override] removeItem failed for ' + key + ':', err);
            }
        };
    }

    // Load data from IndexedDB into DB_STORE (optimized: localStorage first, IndexedDB async)
    async function initDB() {
                // ---------------------------------------------------------------------------------
        // 🛡️ IRON-CLAD TENANT ISOLATION BARRIER:
        // Strictly prohibit cross-tenant data recovery. Data for one tenant can NEVER
        // be populated or recovered from another tenant or global un-prefixed keys.
        // Wipe legacy un-prefixed leak keys (logo, company name, shared backup cookies).
        // ---------------------------------------------------------------------------------
        try {
            const legacyLeakKeys = ['company_logo_base64', 'erp_settings_backup', 'erp_rate_usd_backup', 'companyName'];
            legacyLeakKeys.forEach(k => {
                if (originalGetItem.call(localStorage, k) !== null) {
                    originalRemoveItem.call(localStorage, k);
                }
            });
            // Clear un-isolated backup cookies
            document.cookie = "erp_settings_backup=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "erp_rate_usd_backup=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        } catch(e) {}
        // ---------------------------------------------------------------------------------

        // 1. FAST PATH: Load from localStorage immediately (synchronous)
        syncKeys.forEach(key => {
            const tenantKey = window.getTenantKey(key);
            const val = originalGetItem.call(localStorage, tenantKey);
            if (val !== null) {
                window.DB_STORE[tenantKey] = val;
            }
        });

        // 2. SLOW PATH: Update from IndexedDB in background ONLY if not already loaded from localStorage
        try {
            Promise.all(syncKeys.map(async (key) => {
                const tenantKey = window.getTenantKey(key);
                if (!window.DB_STORE[tenantKey]) {
                    try {
                        const val = await localforage.getItem(tenantKey);
                        if (val !== null && !window.DB_STORE[tenantKey]) {
                            window.DB_STORE[tenantKey] = val;
                        }
                    } catch(err) {}
                }
            })).catch(e => {});
        } catch (e) {}

        // Complete fallback check: Ensure all syncKeys are loaded in DB_STORE from localStorage if still empty
        syncKeys.forEach(key => {
            const tenantKey = window.getTenantKey(key);
            if (!window.DB_STORE[tenantKey]) {
                const val = originalGetItem.call(localStorage, tenantKey);
                if (val !== null) {
                    window.DB_STORE[tenantKey] = val;
                }
            }
        });

        // ════════════════════════════════════════
        //  ONE-TIME DATA MIGRATION TO DATE-BASED SERIALS (INSIDE initDB)
        // ════════════════════════════════════════
        try {
            if (localGetItem('erp_miga_v10_done') !== 'true') {
                const suffixes = [
                    'erp_sales_invoices_v1',
                    'erp_purchase_invoices_v1',
                    'erp_single_payments_v1',
                    'erp_single_receipts_v1',
                    'erp_payments_v1',
                    'erp_receipts_v1',
                    'erp_journal_vouchers_v1',
                    'erp_inventory_v1'
                ];
                
                const prefixSet = new Set(['tenant_default_', getTenantPrefix()]);
                
                // Scan IndexedDB keys
                try {
                    if (window.localforage && typeof localforage.keys === 'function') {
                        const lfKeys = await localforage.keys();
                        lfKeys.forEach(k => {
                            suffixes.forEach(suff => {
                                if (k.endsWith(suff)) {
                                    prefixSet.add(k.substring(0, k.length - suff.length));
                                }
                            });
                        });
                    }
                } catch(e) {}
                
                // Scan localStorage keys
                try {
                    const lsKeys = Object.keys(localStorage);
                    lsKeys.forEach(k => {
                        suffixes.forEach(suff => {
                            if (k.endsWith(suff)) {
                                prefixSet.add(k.substring(0, k.length - suff.length));
                            }
                        });
                    });
                } catch(e) {}
                
                for (const pref of prefixSet) {
                    const salesKey = pref + 'erp_sales_invoices_v1';
                    const purchaseKey = pref + 'erp_purchase_invoices_v1';
                    const singlePayKey = pref + 'erp_single_payments_v1';
                    const singleRecKey = pref + 'erp_single_receipts_v1';
                    const payKey = pref + 'erp_payments_v1';
                    const recKey = pref + 'erp_receipts_v1';
                    const jvKey = pref + 'erp_journal_vouchers_v1';
                    const invKey = pref + 'erp_inventory_v1';
                    const printKey = 'erp_print_requests';
                    
                    let sales = JSON.parse(window.DB_STORE[salesKey] || localGetItem(salesKey) || '[]');
                    let purchases = JSON.parse(window.DB_STORE[purchaseKey] || localGetItem(purchaseKey) || '[]');
                    let singlePayments = JSON.parse(window.DB_STORE[singlePayKey] || localGetItem(singlePayKey) || '[]');
                    let singleReceipts = JSON.parse(window.DB_STORE[singleRecKey] || localGetItem(singleRecKey) || '[]');
                    let payments = JSON.parse(window.DB_STORE[payKey] || localGetItem(payKey) || '[]');
                    let receipts = JSON.parse(window.DB_STORE[recKey] || localGetItem(recKey) || '[]');
                    let jvs = JSON.parse(window.DB_STORE[jvKey] || localGetItem(jvKey) || '[]');
                    let inventory = JSON.parse(window.DB_STORE[invKey] || localGetItem(invKey) || '[]');
                    let prints = JSON.parse(window.DB_STORE[printKey] || localGetItem(printKey) || '[]');
                    
                    const idMap = {};
                    const detailsToSave = {};
                    const keysToDelete = [];
                    
                    function getYYMM(dateStr) {
                        const d = dateStr ? new Date(dateStr) : new Date();
                        const validD = isNaN(d.getTime()) ? new Date() : d;
                        const yy = String(validD.getFullYear()).substring(2, 4);
                        const mm = String(validD.getMonth() + 1).padStart(2, '0');
                        return yy + mm;
                    }

                    function chronoCompare(a, b) {
                        const dateA = a.date || a.createdAt || '';
                        const dateB = b.date || b.createdAt || '';
                        const cmp = dateA.localeCompare(dateB);
                        if (cmp !== 0) return cmp;
                        
                        const timeA = a.time || '';
                        const timeB = b.time || '';
                        const cmpTime = timeA.localeCompare(timeB);
                        if (cmpTime !== 0) return cmpTime;
                        
                        return (a._originalIndex || 0) - (b._originalIndex || 0);
                    }
                    
                    // 1. Migrate Sales Invoices
                    const salesByMonth = {};
                    sales.forEach((inv, idx) => {
                        inv._originalIndex = idx;
                        const yymm = getYYMM(inv.date || inv.createdAt);
                        if (!salesByMonth[yymm]) salesByMonth[yymm] = [];
                        salesByMonth[yymm].push(inv);
                    });
                    for (const yymm of Object.keys(salesByMonth)) {
                        salesByMonth[yymm].sort(chronoCompare);
                        for (let index = 0; index < salesByMonth[yymm].length; index++) {
                            const inv = salesByMonth[yymm][index];
                            const oldNo = inv.invoiceNo;
                            if (oldNo === 'preliminary' || String(oldNo).startsWith('PRE-')) continue;
                            
                            const newNo = 'S' + yymm + String(index + 1).padStart(4, '0');
                            if (oldNo !== newNo) {
                                idMap[oldNo] = newNo;
                                inv.invoiceNo = newNo;
                                if (inv.id === oldNo) inv.id = newNo;
                                
                                const detailKeyOld = pref + 'erp_inv_' + oldNo;
                                const detailKeyNew = pref + 'erp_inv_' + newNo;
                                const detailData = window.DB_STORE[detailKeyOld] || localGetItem(detailKeyOld);
                                if (detailData) {
                                    try {
                                        const parsed = JSON.parse(detailData);
                                        parsed.invoiceNo = newNo;
                                        if (parsed.id === oldNo) parsed.id = newNo;
                                        detailsToSave[detailKeyNew] = JSON.stringify(parsed);
                                        keysToDelete.push(detailKeyOld);
                                    } catch(e){}
                                }
                            }
                        }
                    }
                    
                    // 2. Migrate Purchase Invoices
                    const purByMonth = {};
                    purchases.forEach((inv, idx) => {
                        inv._originalIndex = idx;
                        const yymm = getYYMM(inv.date || inv.createdAt);
                        if (!purByMonth[yymm]) purByMonth[yymm] = [];
                        purByMonth[yymm].push(inv);
                    });
                    for (const yymm of Object.keys(purByMonth)) {
                        purByMonth[yymm].sort(chronoCompare);
                        for (let index = 0; index < purByMonth[yymm].length; index++) {
                            const inv = purByMonth[yymm][index];
                            const oldNo = inv.invoiceNo;
                            
                            const newNo = 'P' + yymm + String(index + 1).padStart(4, '0');
                            if (oldNo !== newNo) {
                                idMap[oldNo] = newNo;
                                inv.invoiceNo = newNo;
                                if (inv.id === oldNo) inv.id = newNo;
                                
                                const detailKeyOld = pref + 'erp_pur_' + oldNo;
                                const detailKeyNew = pref + 'erp_pur_' + newNo;
                                const detailData = window.DB_STORE[detailKeyOld] || localGetItem(detailKeyOld);
                                if (detailData) {
                                    try {
                                        const parsed = JSON.parse(detailData);
                                        parsed.invoiceNo = newNo;
                                        if (parsed.id === oldNo) parsed.id = newNo;
                                        detailsToSave[detailKeyNew] = JSON.stringify(parsed);
                                        keysToDelete.push(detailKeyOld);
                                    } catch(e){}
                                }
                            }
                        }
                    }
                    
                    // 3. Migrate Single Payments
                    const spByMonth = {};
                    singlePayments.forEach((v, idx) => {
                        v._originalIndex = idx;
                        const yymm = getYYMM(v.date || v.createdAt);
                        if (!spByMonth[yymm]) spByMonth[yymm] = [];
                        spByMonth[yymm].push(v);
                    });
                    for (const yymm of Object.keys(spByMonth)) {
                        spByMonth[yymm].sort(chronoCompare);
                        for (let index = 0; index < spByMonth[yymm].length; index++) {
                            const v = spByMonth[yymm][index];
                            const oldNo = v.id || v.voucherNo;
                            
                            const newNo = 'E' + yymm + String(index + 1).padStart(4, '0');
                            if (oldNo !== newNo) {
                                idMap[oldNo] = newNo;
                                if (v.id === oldNo) v.id = newNo;
                                if (v.voucherNo === oldNo) v.voucherNo = newNo;
                            }
                        }
                    }
                    
                    // 4. Migrate Single Receipts
                    const srByMonth = {};
                    singleReceipts.forEach((v, idx) => {
                        v._originalIndex = idx;
                        const yymm = getYYMM(v.date || v.createdAt);
                        if (!srByMonth[yymm]) srByMonth[yymm] = [];
                        srByMonth[yymm].push(v);
                    });
                    for (const yymm of Object.keys(srByMonth)) {
                        srByMonth[yymm].sort(chronoCompare);
                        for (let index = 0; index < srByMonth[yymm].length; index++) {
                            const v = srByMonth[yymm][index];
                            const oldNo = v.id || v.voucherNo;
                            
                            const newNo = 'E' + yymm + String(index + 1).padStart(4, '0');
                            if (oldNo !== newNo) {
                                idMap[oldNo] = newNo;
                                if (v.id === oldNo) v.id = newNo;
                                if (v.voucherNo === oldNo) v.voucherNo = newNo;
                            }
                        }
                    }
                    
                    // 4a. Migrate Grouped Payments (MVP-XXXX -> M26070001)
                    const seenPayVouchers = new Set();
                    const uniqueGroupedPayVouchers = [];
                    payments.forEach((p, idx) => {
                        if (p.isGrouped && p.voucherNo) {
                            if (!seenPayVouchers.has(p.voucherNo)) {
                                seenPayVouchers.add(p.voucherNo);
                                uniqueGroupedPayVouchers.push({
                                    voucherNo: p.voucherNo,
                                    date: p.date || p.createdAt,
                                    _originalIndex: idx
                                });
                            }
                        }
                    });
                    const gpByMonth = {};
                    uniqueGroupedPayVouchers.forEach(v => {
                        const yymm = getYYMM(v.date);
                        if (!gpByMonth[yymm]) gpByMonth[yymm] = [];
                        gpByMonth[yymm].push(v);
                    });
                    for (const yymm of Object.keys(gpByMonth)) {
                        gpByMonth[yymm].sort(chronoCompare);
                        for (let index = 0; index < gpByMonth[yymm].length; index++) {
                            const v = gpByMonth[yymm][index];
                            const oldNo = v.voucherNo;
                            const isOldFormat = !/^M\d{8}$/.test(oldNo);
                            if (isOldFormat) {
                                const newNo = 'M' + yymm + String(index + 1).padStart(4, '0');
                                idMap[oldNo] = newNo;
                            }
                        }
                    }

                    // 4b. Migrate Grouped Receipts (MVR-XXXX -> M26070001)
                    const seenRecVouchers = new Set();
                    const uniqueGroupedRecVouchers = [];
                    receipts.forEach((r, idx) => {
                        if (r.isGrouped && r.voucherNo) {
                            if (!seenRecVouchers.has(r.voucherNo)) {
                                seenRecVouchers.add(r.voucherNo);
                                uniqueGroupedRecVouchers.push({
                                    voucherNo: r.voucherNo,
                                    date: r.date || r.createdAt,
                                    _originalIndex: idx
                                });
                            }
                        }
                    });
                    const grByMonth = {};
                    uniqueGroupedRecVouchers.forEach(v => {
                        const yymm = getYYMM(v.date);
                        if (!grByMonth[yymm]) grByMonth[yymm] = [];
                        grByMonth[yymm].push(v);
                    });
                    for (const yymm of Object.keys(grByMonth)) {
                        grByMonth[yymm].sort(chronoCompare);
                        for (let index = 0; index < grByMonth[yymm].length; index++) {
                            const v = grByMonth[yymm][index];
                            const oldNo = v.voucherNo;
                            const isOldFormat = !/^M\d{8}$/.test(oldNo);
                            if (isOldFormat) {
                                const newNo = 'M' + yymm + String(index + 1).padStart(4, '0');
                                idMap[oldNo] = newNo;
                            }
                        }
                    }
                    
                    // Cleanup _originalIndex properties
                    sales.forEach(inv => delete inv._originalIndex);
                    purchases.forEach(inv => delete inv._originalIndex);
                    singlePayments.forEach(v => delete v._originalIndex);
                    singleReceipts.forEach(v => delete v._originalIndex);

                    const hasChanges = Object.keys(idMap).length > 0;
                    
                    if (hasChanges) {
                        // Apply details changes
                        for (const keyOld of keysToDelete) {
                            delete window.DB_STORE[keyOld];
                            localRemoveItem(keyOld);
                            await localforage.removeItem(keyOld);
                        }
                        for (const keyNew of Object.keys(detailsToSave)) {
                            const valStr = detailsToSave[keyNew];
                            window.DB_STORE[keyNew] = valStr;
                            localSetItem(keyNew, valStr);
                            await localforage.setItem(keyNew, valStr);
                        }
                        
                        // Save main lists
                        const salesVal = JSON.stringify(sales);
                        window.DB_STORE[salesKey] = salesVal;
                        localSetItem(salesKey, salesVal);
                        await localforage.setItem(salesKey, salesVal);
                        
                        const purVal = JSON.stringify(purchases);
                        window.DB_STORE[purchaseKey] = purVal;
                        localSetItem(purchaseKey, purVal);
                        await localforage.setItem(purchaseKey, purVal);
                        
                        const spVal = JSON.stringify(singlePayments);
                        window.DB_STORE[singlePayKey] = spVal;
                        localSetItem(singlePayKey, spVal);
                        await localforage.setItem(singlePayKey, spVal);
                        
                        const srVal = JSON.stringify(singleReceipts);
                        window.DB_STORE[singleRecKey] = srVal;
                        localSetItem(singleRecKey, srVal);
                        await localforage.setItem(singleRecKey, srVal);
                        
                        // 5. Update references in GL payments / receipts
                        let paymentsChanged = false;
                        payments.forEach(p => {
                            if (p.voucherNo && idMap[p.voucherNo]) {
                                p.voucherNo = idMap[p.voucherNo];
                                paymentsChanged = true;
                            }
                            if (p.invoiceNo && idMap[p.invoiceNo]) {
                                p.invoiceNo = idMap[p.invoiceNo];
                                paymentsChanged = true;
                            }
                            if (p.refNo && idMap[p.refNo]) {
                                p.refNo = idMap[p.refNo];
                                paymentsChanged = true;
                            }
                        });
                        if (paymentsChanged) {
                            const valStr = JSON.stringify(payments);
                            window.DB_STORE[payKey] = valStr;
                            localSetItem(payKey, valStr);
                            await localforage.setItem(payKey, valStr);
                        }
                        
                        let receiptsChanged = false;
                        receipts.forEach(r => {
                            if (r.voucherNo && idMap[r.voucherNo]) {
                                r.voucherNo = idMap[r.voucherNo];
                                receiptsChanged = true;
                            }
                            if (r.invoiceNo && idMap[r.invoiceNo]) {
                                r.invoiceNo = idMap[r.invoiceNo];
                                receiptsChanged = true;
                            }
                            if (r.refNo && idMap[r.refNo]) {
                                r.refNo = idMap[r.refNo];
                                receiptsChanged = true;
                            }
                        });
                        if (receiptsChanged) {
                            const valStr = JSON.stringify(receipts);
                            window.DB_STORE[recKey] = valStr;
                            localSetItem(recKey, valStr);
                            await localforage.setItem(recKey, valStr);
                        }
                        
                        // 6. Update Inventory entries references
                        let invChanged = false;
                        inventory.forEach(item => {
                            if (item.invoiceNo && idMap[item.invoiceNo]) {
                                item.invoiceNo = idMap[item.invoiceNo];
                                invChanged = true;
                            }
                            if (item.refNo && idMap[item.refNo]) {
                                item.refNo = idMap[item.refNo];
                                invChanged = true;
                            }
                        });
                        if (invChanged) {
                            const valStr = JSON.stringify(inventory);
                            window.DB_STORE[invKey] = valStr;
                            localSetItem(invKey, valStr);
                            await localforage.setItem(invKey, valStr);
                        }
                        
                        // 7. Update Print Requests references
                        let printChanged = false;
                        prints.forEach(req => {
                            if (req.id && idMap[req.id]) {
                                req.id = idMap[req.id];
                                printChanged = true;
                            }
                            if (req.invoiceNo && idMap[req.invoiceNo]) {
                                req.invoiceNo = idMap[req.invoiceNo];
                                printChanged = true;
                            }
                        });
                        if (printChanged) {
                            const valStr = JSON.stringify(prints);
                            window.DB_STORE[printKey] = valStr;
                            localSetItem(printKey, valStr);
                            await localforage.setItem(printKey, valStr);
                        }
                    }
                }
                
                localSetItem('erp_miga_v10_done', 'true');
            }
        } catch(e) {
            console.error('Migration failed:', e);
        }

        // ═════════════════════════════════════════════════════════════════════
        //  COMPLETE RE-SEQUENCING: CONVERT ALL PURCHASES (P+YYMM+SEQ) & SALES (S+YYMM+SEQ)
        // ═════════════════════════════════════════════════════════════════════
        if (localGetItem('erp_resequence_v1_done') !== 'true') {
        try {
            const migPrefixSet = new Set(['tenant_default_', getTenantPrefix()]);
            try {
                const lsKeys2 = Object.keys(localStorage);
                lsKeys2.forEach(k => {
                    if (k.endsWith('erp_sales_invoices_v1') || k.endsWith('erp_purchase_invoices_v1')) {
                        const baseName = k.endsWith('erp_sales_invoices_v1') ? 'erp_sales_invoices_v1' : 'erp_purchase_invoices_v1';
                        migPrefixSet.add(k.substring(0, k.length - baseName.length));
                    }
                });
            } catch(e) {}
            try {
                if (window.DB_STORE) {
                    Object.keys(window.DB_STORE).forEach(k => {
                        if (k.endsWith('erp_sales_invoices_v1') || k.endsWith('erp_purchase_invoices_v1')) {
                            const baseName = k.endsWith('erp_sales_invoices_v1') ? 'erp_sales_invoices_v1' : 'erp_purchase_invoices_v1';
                            migPrefixSet.add(k.substring(0, k.length - baseName.length));
                        }
                    });
                }
            } catch(e) {}

            for (const pref of migPrefixSet) {
                // ----------------------------------------------------
                // 1. RE-SEQUENCE ALL PURCHASE INVOICES (P+YYMM+0001, 0002...)
                // ----------------------------------------------------
                const purKey = pref + 'erp_purchase_invoices_v1';
                let purVal = window.DB_STORE[purKey] || localGetItem(purKey);
                if (purVal) {
                    let purchases = [];
                    try { purchases = JSON.parse(purVal); } catch(e) {}
                    if (Array.isArray(purchases) && purchases.length > 0) {
                        const pFormatRegex = /^P\d{8}$/;
                        let purNeedsMigration = purchases.some(inv => {
                            const no = String(inv.invoiceNo || '');
                            return !no || !pFormatRegex.test(no) || no.startsWith('C-') || no.startsWith('PUR-');
                        });

                        if (purNeedsMigration) {
                            const purIdMap = {};
                            const purByMonth = {};
                            purchases.forEach((inv, idx) => {
                                inv._origIdx = idx;
                                const d = (inv.date || inv.createdAt) ? new Date(inv.date || inv.createdAt) : new Date();
                                const validD = isNaN(d.getTime()) ? new Date() : d;
                                const yy = String(validD.getFullYear()).substring(2, 4);
                                const mm = String(validD.getMonth() + 1).padStart(2, '0');
                                const yymm = yy + mm;
                                if (!purByMonth[yymm]) purByMonth[yymm] = [];
                                purByMonth[yymm].push(inv);
                            });

                            for (const yymm of Object.keys(purByMonth)) {
                                purByMonth[yymm].sort((a, b) => {
                                    const cmpDate = (a.date || a.createdAt || '').localeCompare(b.date || b.createdAt || '');
                                    if (cmpDate !== 0) return cmpDate;
                                    const cmpTime = (a.time || '').localeCompare(b.time || '');
                                    if (cmpTime !== 0) return cmpTime;
                                    return (a._origIdx || 0) - (b._origIdx || 0);
                                });

                                purByMonth[yymm].forEach((inv, index) => {
                                    const oldNo = inv.invoiceNo;
                                    const newNo = 'P' + yymm + String(index + 1).padStart(4, '0');
                                    if (oldNo !== newNo) {
                                        purIdMap[oldNo] = newNo;
                                        inv.invoiceNo = newNo;
                                        if (inv.id === oldNo || !inv.id) inv.id = newNo;
                                    }
                                });
                            }

                            if (Object.keys(purIdMap).length > 0) {
                                purchases.forEach(inv => delete inv._origIdx);
                                const updatedPurVal = JSON.stringify(purchases);
                                window.DB_STORE[purKey] = updatedPurVal;
                                localSetItem(purKey, updatedPurVal);
                                await localforage.setItem(purKey, updatedPurVal);
                                dirtyKeys.add('erp_purchase_invoices_v1');

                                // Migrate detail keys for purchases (erp_pur_C-000013 -> erp_pur_P26060001)
                                for (const oldNo of Object.keys(purIdMap)) {
                                    const newNo = purIdMap[oldNo];
                                    for (const dKey of ['erp_pur_', 'erp_pur_draft_']) {
                                        const keyOld = pref + dKey + oldNo;
                                        const keyNew = pref + dKey + newNo;
                                        const detailData = window.DB_STORE[keyOld] || localGetItem(keyOld);
                                        if (detailData) {
                                            try {
                                                const parsed = JSON.parse(detailData);
                                                parsed.invoiceNo = newNo;
                                                if (parsed.id === oldNo) parsed.id = newNo;
                                                const valStr = JSON.stringify(parsed);
                                                window.DB_STORE[keyNew] = valStr;
                                                localSetItem(keyNew, valStr);
                                                await localforage.setItem(keyNew, valStr);
                                                delete window.DB_STORE[keyOld];
                                                localRemoveItem(keyOld);
                                                await localforage.removeItem(keyOld);
                                            } catch(e){}
                                        }
                                    }
                                }

                                // Update references in ledger/vouchers for purchases
                                const purStoresToUpdate = [
                                    'erp_receipts_v1', 'erp_payments_v1', 
                                    'erp_single_receipts_v1', 'erp_single_payments_v1', 
                                    'erp_journal_vouchers_v1', 'erp_inventory_v1'
                                ];
                                for (const baseKey of purStoresToUpdate) {
                                    const targetKey = pref + baseKey;
                                    const storeVal = window.DB_STORE[targetKey] || localGetItem(targetKey);
                                    if (!storeVal) continue;
                                    let storeData = [];
                                    try { storeData = JSON.parse(storeVal); } catch(e) { continue; }
                                    let storeChanged = false;
                                    storeData.forEach(item => {
                                        ['invoiceNo','invoice_id','docId','reference','ref'].forEach(field => {
                                            if (item[field] && purIdMap[item[field]]) {
                                                item[field] = purIdMap[item[field]];
                                                storeChanged = true;
                                            }
                                        });
                                    });
                                    if (storeChanged) {
                                        const storeStr = JSON.stringify(storeData);
                                        window.DB_STORE[targetKey] = storeStr;
                                        localSetItem(targetKey, storeStr);
                                        await localforage.setItem(targetKey, storeStr);
                                        dirtyKeys.add(baseKey);
                                    }
                                }
                                console.log(`[Purchases P-Migration] Fully re-sequenced purchase invoices for prefix "${pref}":`, purIdMap);
                            }
                        }
                    }
                }

                // ----------------------------------------------------
                // 2. RE-SEQUENCE ALL SALES INVOICES (S+YYMM+0001, 0002...)
                // ----------------------------------------------------
                const salesKey = pref + 'erp_sales_invoices_v1';
                let salesVal = window.DB_STORE[salesKey] || localGetItem(salesKey);
                if (salesVal) {
                    let sales = [];
                    try { sales = JSON.parse(salesVal); } catch(e) {}
                    if (Array.isArray(sales) && sales.length > 0) {
                        const sFormatRegex = /^S\d{8}$/;
                        let salesNeedsMigration = sales.some(inv => {
                            const no = String(inv.invoiceNo || '');
                            return no && no !== 'preliminary' && !no.startsWith('PRE-') && !sFormatRegex.test(no);
                        });

                        if (salesNeedsMigration) {
                            const salesIdMap = {};
                            const salesByMonth = {};
                            sales.forEach((inv, idx) => {
                                const no = String(inv.invoiceNo || '');
                                if (no === 'preliminary' || no.startsWith('PRE-')) return;
                                inv._origIdx = idx;

                                const d = (inv.date || inv.createdAt) ? new Date(inv.date || inv.createdAt) : new Date();
                                const validD = isNaN(d.getTime()) ? new Date() : d;
                                const yy = String(validD.getFullYear()).substring(2, 4);
                                const mm = String(validD.getMonth() + 1).padStart(2, '0');
                                const yymm = yy + mm;
                                if (!salesByMonth[yymm]) salesByMonth[yymm] = [];
                                salesByMonth[yymm].push(inv);
                            });

                            for (const yymm of Object.keys(salesByMonth)) {
                                salesByMonth[yymm].sort((a, b) => {
                                    const cmpDate = (a.date || a.createdAt || '').localeCompare(b.date || b.createdAt || '');
                                    if (cmpDate !== 0) return cmpDate;
                                    const cmpTime = (a.time || '').localeCompare(b.time || '');
                                    if (cmpTime !== 0) return cmpTime;
                                    return (a._origIdx || 0) - (b._origIdx || 0);
                                });

                                salesByMonth[yymm].forEach((inv, index) => {
                                    const oldNo = inv.invoiceNo;
                                    const newNo = 'S' + yymm + String(index + 1).padStart(4, '0');
                                    if (oldNo !== newNo) {
                                        salesIdMap[oldNo] = newNo;
                                        inv.invoiceNo = newNo;
                                        if (inv.id === oldNo || !inv.id) inv.id = newNo;
                                    }
                                });
                            }

                            if (Object.keys(salesIdMap).length > 0) {
                                sales.forEach(inv => delete inv._origIdx);
                                const updatedSalesVal = JSON.stringify(sales);
                                window.DB_STORE[salesKey] = updatedSalesVal;
                                localSetItem(salesKey, updatedSalesVal);
                                await localforage.setItem(salesKey, updatedSalesVal);
                                dirtyKeys.add('erp_sales_invoices_v1');

                                // Migrate detail keys for sales (erp_inv_...)
                                for (const oldNo of Object.keys(salesIdMap)) {
                                    const newNo = salesIdMap[oldNo];
                                    for (const dKey of ['erp_inv_', 'erp_draft_']) {
                                        const keyOld = pref + dKey + oldNo;
                                        const keyNew = pref + dKey + newNo;
                                        const detailData = window.DB_STORE[keyOld] || localGetItem(keyOld);
                                        if (detailData) {
                                            try {
                                                const parsed = JSON.parse(detailData);
                                                parsed.invoiceNo = newNo;
                                                if (parsed.id === oldNo) parsed.id = newNo;
                                                const valStr = JSON.stringify(parsed);
                                                window.DB_STORE[keyNew] = valStr;
                                                localSetItem(keyNew, valStr);
                                                await localforage.setItem(keyNew, valStr);
                                                delete window.DB_STORE[keyOld];
                                                localRemoveItem(keyOld);
                                                await localforage.removeItem(keyOld);
                                            } catch(e){}
                                        }
                                    }
                                }

                                // Update references in ledger/vouchers for sales
                                const salesStoresToUpdate = [
                                    'erp_receipts_v1', 'erp_payments_v1', 
                                    'erp_single_receipts_v1', 'erp_single_payments_v1', 
                                    'erp_journal_vouchers_v1', 'erp_inventory_v1'
                                ];
                                for (const baseKey of salesStoresToUpdate) {
                                    const targetKey = pref + baseKey;
                                    const storeVal = window.DB_STORE[targetKey] || localGetItem(targetKey);
                                    if (!storeVal) continue;
                                    let storeData = [];
                                    try { storeData = JSON.parse(storeVal); } catch(e) { continue; }
                                    let storeChanged = false;
                                    storeData.forEach(item => {
                                        ['invoiceNo','invoice_id','docId','reference','ref'].forEach(field => {
                                            if (item[field] && salesIdMap[item[field]]) {
                                                item[field] = salesIdMap[item[field]];
                                                storeChanged = true;
                                            }
                                        });
                                    });
                                    if (storeChanged) {
                                        const storeStr = JSON.stringify(storeData);
                                        window.DB_STORE[targetKey] = storeStr;
                                        localSetItem(targetKey, storeStr);
                                        await localforage.setItem(targetKey, storeStr);
                                        dirtyKeys.add(baseKey);
                                    }
                                }
                                console.log(`[Sales S-Migration] Fully re-sequenced sales invoices for prefix "${pref}":`, salesIdMap);
                            }
                        }
                    }
                }
                localSetItem('erp_resequence_v1_done', 'true');
            }
        } catch(e) {
            console.error('[Unified Invoices Re-sequencing] failed:', e);
        }
        }

        // ═════════════════════════════════════════════════════════════════════
        //  ENSURE DEFAULT SUPPLIERS ARE SEEDED IF MISSING
        // ═════════════════════════════════════════════════════════════════════
        try {
            const suppTenantKey = window.getTenantKey('erp_suppliers_v1');
            let currentSuppsRaw = window.DB_STORE[suppTenantKey] || localGetItem(suppTenantKey) || localGetItem('erp_suppliers_v1');
            let parsedSupps = [];
            try { parsedSupps = JSON.parse(currentSuppsRaw || '[]'); } catch(e) { parsedSupps = []; }
            if (!Array.isArray(parsedSupps)) parsedSupps = [];
            parsedSupps = parsedSupps.filter(s => s && !s.deleted);

            if (parsedSupps.length === 0) {
                const defaultSuppliers = [
                    { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', whatsapp: '0123456789', email: 'alasaad@steel.sd', address: 'المنطقة الصناعية - الخرطوم بحري', currency: 'SDG', currentBalance: 0, creditLimit: 50000000, securityLevel: 1, status: 'active', notes: 'المورد الرئيسي لقضبان التسليح' },
                    { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', whatsapp: '0912345678', email: 'omega@steel.sd', address: 'سوبا الصناعية', currency: 'SDG', currentBalance: 0, creditLimit: 30000000, securityLevel: 1, status: 'active', notes: 'توريد زوايا ومواسير وحديد مبوم' },
                    { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', whatsapp: '0112345678', email: 'atbara@cement.sd', address: 'عطبرة - ولاية نهر النيل', currency: 'SDG', currentBalance: 0, creditLimit: 20000000, securityLevel: 1, status: 'active', notes: 'توريد أسمنت بورتلاندي عادي ومقاوم' },
                    { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', whatsapp: '0901234567', email: 'giad@giad.com', address: 'مدينة جياد الصناعية', currency: 'SDG', currentBalance: 0, creditLimit: 100000000, securityLevel: 1, status: 'active', notes: 'توريد الصاج والألواح والمواسير المجلفنة' },
                    { id: 'SUP-005', code: 'SUP-005', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', whatsapp: '0123987654', email: 'alhuda@supply.sd', address: 'السجانة - السوق المركزي', currency: 'SDG', currentBalance: 0, creditLimit: 15000000, securityLevel: 1, status: 'active', notes: 'مستلزمات البناء والتشييد' },
                    { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', whatsapp: '0912987654', email: 'berber@cement.sd', address: 'بربر - ولاية نهر النيل', currency: 'SDG', currentBalance: 0, creditLimit: 25000000, securityLevel: 1, status: 'active', notes: 'توريد أسمنت صخر السودان' },
                    { id: 'SUP-007', code: 'SUP-007', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', whatsapp: '0123112233', email: 'alrajhi@steel.sd', address: 'المنطقة الحرة - قري', currency: 'SDG', currentBalance: 0, creditLimit: 40000000, securityLevel: 1, status: 'active', notes: 'حديد تسليح عالي المقاومة' }
                ];
                const suppStr = JSON.stringify(defaultSuppliers);
                window.DB_STORE[suppTenantKey] = suppStr;
                window.DB_STORE['erp_suppliers_v1'] = suppStr;
                localSetItem(suppTenantKey, suppStr);
                localSetItem('erp_suppliers_v1', suppStr);
                if (typeof localforage !== 'undefined') {
                    localforage.setItem(suppTenantKey, suppStr).catch(e => {});
                    localforage.setItem('erp_suppliers_v1', suppStr).catch(e => {});
                }
            }
        } catch(suppErr) {
            console.warn('Auto-seed suppliers failed:', suppErr);
        }

        // ═════════════════════════════════════════════════════════════════════
        //  ENSURE DEFAULT SAFES & BANKS ARE SEEDED IF MISSING
        // ═════════════════════════════════════════════════════════════════════
        try {
            const safesTenantKey = window.getTenantKey('erp_safes_v1');
            let currentSafesRaw = window.DB_STORE[safesTenantKey] || localGetItem(safesTenantKey) || localGetItem('erp_safes_v1');
            let parsedSafes = [];
            try { parsedSafes = JSON.parse(currentSafesRaw || '[]'); } catch(e) { parsedSafes = []; }
            if (!Array.isArray(parsedSafes)) parsedSafes = [];
            parsedSafes = parsedSafes.filter(s => s && !s.deleted);

            if (parsedSafes.length === 0) {
                const defaultSafes = [
                    { id: 'safe-001', code: 'SAFE-01', name: 'الخزينة الرئيسية (نقدية المركز)', type: 'safe', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' },
                    { id: 'safe-002', code: 'SAFE-02', name: 'خزينة مبيعات الصالة (كاش)', type: 'safe', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' },
                    { id: 'safe-003', code: 'SAFE-03', name: 'صندوق العهد والمصروفات النثرية', type: 'safe', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' },
                    { id: 'safe-004', code: 'SAFE-04', name: 'خزينة فرع بحري', type: 'safe', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' }
                ];
                const safesStr = JSON.stringify(defaultSafes);
                window.DB_STORE[safesTenantKey] = safesStr;
                window.DB_STORE['erp_safes_v1'] = safesStr;
                localSetItem(safesTenantKey, safesStr);
                localSetItem('erp_safes_v1', safesStr);
                if (typeof localforage !== 'undefined') {
                    localforage.setItem(safesTenantKey, safesStr).catch(e => {});
                    localforage.setItem('erp_safes_v1', safesStr).catch(e => {});
                }
            }
        } catch(safesErr) {
            console.warn('Auto-seed safes failed:', safesErr);
        }

        try {
            const banksTenantKey = window.getTenantKey('erp_banks_v1');
            let currentBanksRaw = window.DB_STORE[banksTenantKey] || localGetItem(banksTenantKey) || localGetItem('erp_banks_v1');
            let parsedBanks = [];
            try { parsedBanks = JSON.parse(currentBanksRaw || '[]'); } catch(e) { parsedBanks = []; }
            if (!Array.isArray(parsedBanks)) parsedBanks = [];
            parsedBanks = parsedBanks.filter(b => b && !b.deleted);

            if (parsedBanks.length === 0) {
                const defaultBanks = [
                    { id: 'bank-001', code: 'BNK-01', name: 'بنك الخرطوم (بنكك - Bankak)', accountNumber: '1234567', type: 'bank', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' },
                    { id: 'bank-002', code: 'BNK-02', name: 'بنك فيصل الإسلامي (فوري - Fawry)', accountNumber: '2345678', type: 'bank', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' },
                    { id: 'bank-003', code: 'BNK-03', name: 'بنك أمدرمان الوطني (أوكاش - O-Cash)', accountNumber: '3456789', type: 'bank', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' },
                    { id: 'bank-004', code: 'BNK-04', name: 'بنك النيل (سريع)', accountNumber: '4567890', type: 'bank', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' },
                    { id: 'bank-005', code: 'BNK-05', name: 'بنك العمال الوطني', accountNumber: '5678901', type: 'bank', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' },
                    { id: 'bank-006', code: 'BNK-06', name: 'بنك البركة السوداني', accountNumber: '6789012', type: 'bank', currency: 'SDG', openingBalance: 0, currentBalance: 0, status: 'active' }
                ];
                const banksStr = JSON.stringify(defaultBanks);
                window.DB_STORE[banksTenantKey] = banksStr;
                window.DB_STORE['erp_banks_v1'] = banksStr;
                localSetItem(banksTenantKey, banksStr);
                localSetItem('erp_banks_v1', banksStr);
                if (typeof localforage !== 'undefined') {
                    localforage.setItem(banksTenantKey, banksStr).catch(e => {});
                    localforage.setItem('erp_banks_v1', banksStr).catch(e => {});
                }
            }
        } catch(banksErr) {
            console.warn('Auto-seed banks failed:', banksErr);
        }

        window.APP_READY = true;
        document.dispatchEvent(new Event('DBReady'));
    }

    let isPushing = false;
    let pushQueue = false;
    let pushTimeout = null;
    const DIRTY_KEYS_STORAGE_KEY = 'magaies_dirty_keys_v1';
    let dirtyKeys = new Set();
    try {
        const savedDirty = originalGetItem.call(localStorage, DIRTY_KEYS_STORAGE_KEY);
        if (savedDirty) {
            const parsedDirty = JSON.parse(savedDirty);
            if (Array.isArray(parsedDirty)) {
                parsedDirty.forEach(k => { if (syncKeys.includes(k)) dirtyKeys.add(k); });
            }
        }
    } catch (e) {}

    let _idbMirrorTimer = null;
    function mirrorDirtyKeysToSwIDB() {
        if (_idbMirrorTimer) clearTimeout(_idbMirrorTimer);
        _idbMirrorTimer = setTimeout(() => {
            try {
                if (typeof indexedDB === 'undefined') return;
                const req = indexedDB.open('MagaiesOfflineIDB_v1', 2);
                req.onupgradeneeded = (e) => {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains('outbox')) db.createObjectStore('outbox', { keyPath: 'id' });
                    if (!db.objectStoreNames.contains('meta')) db.createObjectStore('meta', { keyPath: 'key' });
                    if (!db.objectStoreNames.contains('synced_ack')) db.createObjectStore('synced_ack', { keyPath: 'offline_id' });
                };
                req.onsuccess = (e) => {
                    try {
                        const db = e.target.result;
                        const tx = db.transaction(['meta'], 'readwrite');
                        const store = tx.objectStore('meta');
                        if (dirtyKeys.size === 0) {
                            store.delete('cloud_push_outbox');
                            return;
                        }
                        const payload = {};
                        let hasData = false;
                        Array.from(dirtyKeys).forEach(k => {
                            const pk = window.getTenantKey(k);
                            const d = (window.DB_STORE && window.DB_STORE[pk]) || localGetItem(pk) || localGetItem(k);
                            if (d) {
                                try { payload[k] = JSON.parse(d); hasData = true; } catch (err) {}
                            }
                        });
                        const token = localStorage.getItem('authToken') || '';
                        if (hasData && token) {
                            store.put({
                                key: 'cloud_push_outbox',
                                payload: payload,
                                auth_token: token,
                                keys: Array.from(dirtyKeys),
                                updated_at: Date.now()
                            });
                            if (window.MagaiesOffline && typeof window.MagaiesOffline.registerBackgroundSync === 'function') {
                                window.MagaiesOffline.registerBackgroundSync();
                            } else if ('serviceWorker' in navigator && navigator.serviceWorker.ready) {
                                navigator.serviceWorker.ready.then(reg => {
                                    if (reg && reg.sync && typeof reg.sync.register === 'function') {
                                        reg.sync.register('sync-offline-outbox').catch(() => {});
                                    }
                                }).catch(() => {});
                            }
                        } else {
                            store.delete('cloud_push_outbox');
                        }
                    } catch (txErr) {}
                };
            } catch (err) {}
        }, 120);
    }

    function persistDirtyKeys() {
        try {
            originalSetItem.call(localStorage, DIRTY_KEYS_STORAGE_KEY, JSON.stringify(Array.from(dirtyKeys)));
            mirrorDirtyKeysToSwIDB();
        } catch (e) {}
    }

    window.addEventListener('beforeunload', (e) => {
        if (pushTimeout) {
            clearTimeout(pushTimeout);
        }
        // Use sendBeacon for reliable delivery on page close
        if (dirtyKeys.size > 0 && navigator.onLine) {
            try {
                const prefix = getTenantPrefix();
                let payload = {};
                let hasData = false;
                const keysToSync = Array.from(dirtyKeys);
                keysToSync.forEach(k => {
                    const pk = window.getTenantKey(k);
                    const d = window.DB_STORE[pk] || localGetItem(pk);
                    if (d) {
                        try { payload[k] = JSON.parse(d); hasData = true; } catch(e){}
                    }
                });
                if (hasData) {
                    const token = localStorage.getItem('authToken');
                    if (token) {
                        const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
                        const url = SYNC_PUSH_URL + '?token=' + encodeURIComponent(token);
                        
                        // Attempt sendBeacon first (fastest, asynchronous but 64KB limit)
                        const sent = navigator.sendBeacon(url, blob);
                        
                        // Fallback to synchronous XHR if sendBeacon fails (e.g., payload > 64KB)
                        if (!sent) {
                            console.warn("sendBeacon failed (likely >64KB limit). Falling back to synchronous XHR.");
                            const xhr = new XMLHttpRequest();
                            xhr.open("POST", url, false); // false makes it synchronous
                            xhr.setRequestHeader("Content-Type", "application/json");
                            xhr.send(JSON.stringify(payload));
                        }
                        
                        dirtyKeys.clear();
                        persistDirtyKeys();
                    }
                }
            } catch(beaconErr) {
                console.warn('Sync on unload failed:', beaconErr);
            }
        } else if (dirtyKeys.size > 0) {
            persistDirtyKeys();
        }
    });

    Storage.prototype.setItem = function(key, value) {
        const sessionKeys = ['session_active'];
        if (sessionKeys.includes(key)) {
            return originalSetItem.call(sessionStorage, key, value);
        }

        if (this === localStorage) {
            const tenantKey = window.getTenantKey(key);
            const isGlobalOrOfflineKey = ['authToken', 'user', 'tenant_id', 'erp_theme', 'erp_rate_usd'].includes(key) ||
                String(key).startsWith('magaies_') || String(key).startsWith('magaiees_');
            
            // Save to In-Memory Proxy
            window.DB_STORE[tenantKey] = value;
            if (isGlobalOrOfflineKey) {
                window.DB_STORE[key] = value;
            }

            // Broadcast to other tabs
            syncChannel.postMessage({ type: 'SYNC_KEY', tenantKey: tenantKey, value: value });

            // Save asynchronously to IndexedDB
            if (typeof localforage !== 'undefined') localforage.setItem(tenantKey, value);

            // Always save to original localStorage as a robust synchronous fallback for WebKit/iOS
            try {
                originalSetItem.call(localStorage, tenantKey, value);
                if (isGlobalOrOfflineKey && tenantKey !== key) {
                    originalSetItem.call(localStorage, key, value);
                }
            } catch (e) {
                console.warn("localStorage quota exceeded for key", key, ". Auto-cleaning...");
                // Auto-cleanup: find and remove largest non-essential localStorage entries
                try {
                    const protectedKeys = ['authToken', 'user', 'tenant_id', 'erp_settings_v1', 'session_active', 'tenant_currencies_locked', 'magaies_offline_queue', DIRTY_KEYS_STORAGE_KEY];
                    const currentTenantKey = tenantKey; // don't delete what we're trying to save
                    
                    // Collect all entries with sizes
                    let entries = [];
                    for (let i = 0; i < localStorage.length; i++) {
                        const k = localStorage.key(i);
                        if (!k || protectedKeys.some(pk => k.includes(pk)) || k === currentTenantKey) continue;
                        const val = originalGetItem.call(localStorage, k);
                        entries.push({ key: k, size: val ? val.length : 0 });
                    }
                    
                    // Sort by size descending — remove biggest items first
                    entries.sort((a, b) => b.size - a.size);
                    
                    // Remove up to 30% of entries (the biggest ones)
                    const safeToRemove = ['erp_auto_backups', 'erp_audit_logs'];
                    for (let j = 0; j < entries.length; j++) {
                        if (safeToRemove.some(sk => entries[j].key.includes(sk))) {
                            originalRemoveItem.call(localStorage, entries[j].key);
                            console.log('Safe-cleaned localStorage key:', entries[j].key);
                        }
                    }
                    
                    // Retry saving
                    originalSetItem.call(localStorage, tenantKey, value);
                    console.log("localStorage save succeeded after auto-cleanup");
                } catch (retryErr) {
                    console.warn("localStorage still full after cleanup, relying on IndexedDB", retryErr);
                }
            }

            const baseSyncKey = String(key).replace(/^tenant_[^_]+_/, '');
            if (syncKeys.includes(baseSyncKey)) {
                dirtyKeys.add(baseSyncKey);
                persistDirtyKeys();
                if (!navigator.onLine) {
                    return;
                }
                // Debounce Sync: Wait for 600ms of inactivity before pushing to cloud
                if (pushTimeout) clearTimeout(pushTimeout);
                pushTimeout = setTimeout(() => {
                    if (!isPushing) {
                        pushToCloud();
                    } else {
                        pushQueue = true;
                    }
                }, 600);
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
            
            const val = originalGetItem.call(localStorage, tenantKey);
            if (val !== null) return val;

            // Fallback to unprefixed key for global/auth/offline keys or shared local records
            return originalGetItem.call(localStorage, key);
        }
        return originalGetItem.call(this, key);
    };

    // Global helper for checking uniqueness of Check/Transfer Numbers across all vouchers
    window.isBankReferenceUnique = function(num, type, excludeVoucherId = null) {
        if (!num) return true;
        
        const inputVal = num.toString().trim();
        const inputLast6 = inputVal.length >= 6 ? inputVal.slice(-6) : inputVal;

        // We will check all these stores because they can all contain financial transactions
        const storesToCheck = [
            'erp_receipts_v1', 'erp_payments_v1', 
            'erp_single_receipts_v1', 'erp_single_payments_v1', 
            'erp_journal_vouchers_v1',
            'erp_comprehensive_payments_v1', 'erp_comprehensive_receipts_v1'
        ];
        
        // Helper to extract all references of a specific type (check or mobile/bank transfer) from any voucher object
        function getVoucherReferences(item, refType) {
            const refs = [];
            if (!item) return refs;

            if (refType === 'check') {
                if (item.checkNo) refs.push(item.checkNo.toString().trim());
                if (item.checkNumber) refs.push(item.checkNumber.toString().trim());
                if (Array.isArray(item.cheques)) {
                    item.cheques.forEach(c => {
                        if (c.checkNo) refs.push(c.checkNo.toString().trim());
                        if (c.chequeNo) refs.push(c.chequeNo.toString().trim());
                    });
                }
                if (Array.isArray(item.items)) {
                    item.items.forEach(child => {
                        if (child.checkNo) refs.push(child.checkNo.toString().trim());
                        if (child.chequeNo) refs.push(child.chequeNo.toString().trim());
                        if (Array.isArray(child.cheques)) {
                            child.cheques.forEach(c => {
                                if (c.checkNo) refs.push(c.checkNo.toString().trim());
                                if (c.chequeNo) refs.push(c.chequeNo.toString().trim());
                            });
                        }
                    });
                }
            } else if (refType === 'mobile' || refType === 'bank') {
                if (item.mobileTransferNo) refs.push(item.mobileTransferNo.toString().trim());
                if (item.bankRef) refs.push(item.bankRef.toString().trim());
                if (item.mobileRef) refs.push(item.mobileRef.toString().trim());
                if (Array.isArray(item.bankTransfers)) {
                    item.bankTransfers.forEach(t => {
                        if (t.transferNo) refs.push(t.transferNo.toString().trim());
                    });
                }
                if (Array.isArray(item.items)) {
                    item.items.forEach(child => {
                        if (child.transferNo) refs.push(child.transferNo.toString().trim());
                        if (Array.isArray(child.bankTransfers)) {
                            child.bankTransfers.forEach(t => {
                                if (t.transferNo) refs.push(t.transferNo.toString().trim());
                            });
                        }
                    });
                }
            }
            return refs.filter(r => r.length > 0);
        }

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
                if (excludeVoucherId && (item.id === excludeVoucherId || item.voucherNo === excludeVoucherId)) continue;
                
                const itemRefs = getVoucherReferences(item, type);
                for (const ref of itemRefs) {
                    const itemLast6 = ref.length >= 6 ? ref.slice(-6) : ref;
                    if (itemLast6 === inputLast6) {
                        return false;
                    }
                }
            }
        }
        return true;
    };

    // Global helper for showing original clean duplicate warning modal
    window.showDuplicateWarningModal = function(referenceNo, type) {
        // Remove existing duplicate modal if any
        const existing = document.getElementById('duplicate-warning-modal') || document.getElementById('duplicate-modal');
        if (existing) existing.remove();

        const isCheck = type === 'check';
        const typeLabel = isCheck ? 'شيك' : 'حوالة بنكية';
        
        // Create elements
        const overlay = document.createElement('div');
        overlay.id = 'duplicate-warning-modal';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Tajawal', 'Inter', sans-serif;
            direction: rtl;
        `;

        overlay.innerHTML = `
            <div style="
                background: rgba(30, 41, 59, 0.95);
                border: 1px solid rgba(239, 68, 68, 0.5);
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
                border-radius: 16px;
                padding: 30px;
                width: 90%;
                max-width: 420px;
                text-align: center;
                animation: toastIn 0.3s ease;
            " id="modal-card">
                
                <!-- Icon -->
                <div style="font-size: 3rem; margin-bottom: 10px;">⚠️</div>

                <!-- Title -->
                <h2 style="
                    color: #f87171;
                    font-size: 1.5rem;
                    font-weight: 800;
                    margin: 0 0 15px 0;
                ">تنبيه: ${typeLabel} مكرر!</h2>

                <!-- Message Description -->
                <p style="
                    color: #ffffff;
                    font-size: 1rem;
                    line-height: 1.5;
                    margin: 0 0 15px 0;
                ">
                    رقم الـ${typeLabel} <strong>(${referenceNo})</strong> تم إدخاله مسبقاً في النظام.
                </p>
                
                <p style="
                    color: #94a3b8;
                    font-size: 0.85rem;
                    line-height: 1.5;
                    margin: 0 0 25px 0;
                ">
                    يرجى التأكد لتجنب إدخال نفس الحركة مرتين عن طريق الخطأ.
                </p>

                <!-- Close Button -->
                <button onclick="closeDuplicateWarningModal()" style="
                    background: #ef4444;
                    color: #ffffff;
                    border: none;
                    padding: 12px 25px;
                    border-radius: 8px;
                    font-weight: bold;
                    cursor: pointer;
                    font-size: 1rem;
                    font-family: inherit;
                    width: 100%;
                    transition: background 0.2s ease;
                " onmouseover="this.style.background='#dc2626';"
                   onmouseout="this.style.background='#ef4444';">
                    فهمت، سأراجع الرقم
                </button>
            </div>
        `;

        document.body.appendChild(overlay);

        window.closeDuplicateWarningModal = function() {
            const m = document.getElementById('duplicate-warning-modal');
            if (m) m.remove();
        };
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
            if (typeof localforage !== 'undefined') localforage.removeItem(tenantKey);
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

    const pushToCloud = window.pushToCloud = function(force = false) {
        return new Promise((resolve, reject) => {
            if (!navigator.onLine) {
                // وضع محلي مستقل بالكامل
                resolve({ success: true, offline: true });
                return;
            }
            const token = localStorage.getItem('authToken');
            if (!token || !navigator.onLine) {
                resolve();
                return;
            }

            const prefix = getTenantPrefix();
            let payload = {};
            let hasData = false;
            
            if (dirtyKeys.size === 0 && !force) {
                resolve();
                return;
            }
            
            // Sync only modified keys if available, otherwise fallback to all syncKeys (e.g. initial force sync)
            const keysToSync = dirtyKeys.size > 0 ? Array.from(dirtyKeys) : syncKeys;
            
            // Keep a copy of current dirty keys in case we need to restore them if the sync fails
            const currentDirty = Array.from(dirtyKeys);
            dirtyKeys.clear();
            persistDirtyKeys();

            keysToSync.forEach(k => {
                const pk = window.getTenantKey(k);
                const data = window.DB_STORE[pk] || localGetItem(pk) || localGetItem(k);
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

            let resStatus = 200;
            fetch(SYNC_PUSH_URL, {
                method: "POST",
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            }).then(res => {
                resStatus = res.status;
                if (res.status === 401) {
                    currentDirty.forEach(k => dirtyKeys.add(k));
                    persistDirtyKeys();
                    console.warn("401 push response: keeping dirty keys for retry");
                    return null;
                }
                return res.json().catch(() => ({}));
            }).then(data => {
                if (!data) return;
                
                if (data.error && (resStatus === 401 || data.error.includes('الجلسة') || data.error.includes('تسجيل الدخول'))) {
                    currentDirty.forEach(k => dirtyKeys.add(k));
                    persistDirtyKeys();
                    console.warn("Session warning in push: keeping dirty keys safe");
                    return;
                }

                if (!data.success && data.error !== 'تمت المزامنة بنجاح') {
                    // Restore dirty keys for retry
                    currentDirty.forEach(k => dirtyKeys.add(k));
                    persistDirtyKeys();
                    console.warn("Cloud Sync Push Notice:", data.error || "Save failed");
                    reject(new Error(data.error || "Save failed"));
                } else {
                    persistDirtyKeys();
                    console.log("Cloud Sync: Strict Backup Successful");
                    resolve(data);
                }
            }).catch(err => {
                console.warn("Cloud Sync Push Deferred (Offline/Network):", err);
                // Restore dirty keys on failure
                currentDirty.forEach(k => dirtyKeys.add(k));
                persistDirtyKeys();
                resolve({ success: false, offline: true });
            }).finally(() => {
                isPushing = false;
                hideSyncOverlay();
                pushTimeout = null;
                if (pushQueue && navigator.onLine) {
                    pushQueue = false;
                    pushToCloud(force);
                }
            });
        });
    };

    window.addEventListener('online', () => {
        if (dirtyKeys.size > 0 && !isPushing) {
            pushToCloud();
        }
        if (window.MagaiesOffline && typeof window.MagaiesOffline.flushQueue === 'function') {
            window.MagaiesOffline.flushQueue();
        }
    });

    // فحص تلقائي نشط كل 4 ثوانٍ على اللابتوب والجوال لرفع أي بيانات أوفلاين فور توفر الإنترنت دون الحاجة لأي إجراء يدوي
    setInterval(() => {
        if (dirtyKeys.size > 0 && !isPushing && navigator.onLine !== false) {
            pushToCloud();
        }
    }, 4000);

    // تسجيل Service Worker وتفعيل محرك الأوفلاين الموحد تلقائياً في كافة شاشات اللابتوب والجوال
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('./sw.js?v=2026_10_16_offline_v23', { scope: './', updateViaCache: 'none' }).catch(() => {});
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (!event.data) return;
            if (event.data.type === 'CLOUD_PUSH_COMPLETED_BY_SW') {
                dirtyKeys.clear();
                persistDirtyKeys();
            } else if (event.data.type === 'TRIGGER_OFFLINE_SYNC') {
                if (dirtyKeys.size > 0 && !isPushing && navigator.onLine !== false) {
                    pushToCloud();
                }
            }
        });
    }
    if (dirtyKeys.size > 0) {
        mirrorDirtyKeysToSwIDB();
    }
    if (!window.__MAGAIES_OFFLINE_ENGINE_LOADED__ && typeof document !== 'undefined') {
        const injectOfflineEngine = () => {
            if (window.__MAGAIES_OFFLINE_ENGINE_LOADED__) return;
            const s = document.createElement('script');
            s.src = 'js/magaies-offline-sync.js?v=2026_10_16_v23';
            s.defer = true;
            (document.head || document.documentElement).appendChild(s);
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', injectOfflineEngine);
        else injectOfflineEngine();
    }


    window.pullFromCloud = function(force = false) {
        if (!navigator.onLine) return Promise.resolve();
        const isLocal = window.location.hostname.includes('localhost') || window.location.hostname.includes('127.0.0.1');
        // Sync enabled on localhost - no longer skip
        const token = localStorage.getItem('authToken');
        if (!token) return Promise.resolve();

        // Only pull once per session unless forced
        if (!force && sessionStorage.getItem("cloud_synced")) return Promise.resolve();
        
        const prefix = getTenantPrefix();
        
        // Check if we already have local data to decide if we should block the UI
        const hasLocalData = localGetItem(prefix + "erp_customers_v1") !== null || localGetItem(prefix + "erp_users_v1") !== null;
        
        if (!hasLocalData && !isLocal && navigator.onLine) {
            showSyncOverlay('جاري جلب أحدث البيانات من السيرفر...', true);
        }
        return fetch(SYNC_PULL_URL, { headers: getAuthHeaders() })
            .then(res => {
                if (res.status === 401) {
                    if (isLocal || !navigator.onLine) {
                        console.warn("Localhost/Offline mode: ignoring 401 in pull");
                        return { success: false, offline: true };
                    }
                    alert('انتهت الجلسة. يرجى تسجيل الدخول مرة أخرى.');
                    window.logout(true);
                    throw new Error("Unauthorized");
                }
                return res.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    if (data.max_users !== undefined) {
                        let userObj = {};
                        try { userObj = JSON.parse(localStorage.getItem('user') || '{}'); } catch(e){}
                        userObj.max_users = data.max_users;
                        localStorage.setItem('user', JSON.stringify(userObj));
                    }
                    Object.keys(data.data).forEach(key => {
                        if (data.data[key]) {
                            const pk = window.getTenantKey(key);
                            const localDataStr = window.DB_STORE[pk] || localGetItem(pk);
                            const serverVal = data.data[key];

                            let serverHasData = false;
                            if (Array.isArray(serverVal)) {
                                serverHasData = serverVal.length > 0;
                            } else if (typeof serverVal === 'object' && serverVal !== null) {
                                serverHasData = Object.keys(serverVal).length > 0;
                            }

                            let localIsEmpty = !localDataStr || localDataStr === '{}' || localDataStr === '[]';

                            // NEVER overwrite existing local settings with empty server data
                            if (key.includes('settings') && !serverHasData && !localIsEmpty) {
                                console.log(`Skipping settings sync overwrite: local has settings, server has empty.`);
                                return;
                            }

                            if (serverHasData || localIsEmpty) {
                                let finalVal = serverVal;
                                
                                if (Array.isArray(serverVal) && !localIsEmpty) {
                                    try {
                                        const localArr = JSON.parse(localDataStr);
                                        if (Array.isArray(localArr) && localArr.length > 0) {
                                            const serverIds = new Set();
                                            serverVal.forEach(rec => {
                                                if (rec?.id) serverIds.add(String(rec.id));
                                                if (rec?.invoiceNo) serverIds.add(String(rec.invoiceNo));
                                                if (rec?.receiptNo) serverIds.add(String(rec.receiptNo));
                                                if (rec?.paymentNo) serverIds.add(String(rec.paymentNo));
                                                if (rec?.jvNo) serverIds.add(String(rec.jvNo));
                                            });
                                            
                                            const localOnlyRecords = localArr.filter(rec => {
                                                const rid = rec?.id || rec?.invoiceNo || rec?.receiptNo || rec?.paymentNo || rec?.jvNo;
                                                const invNo = rec?.invoiceNo;
                                                const recId = rec?.id;
                                                let existsOnServer = false;
                                                if (rid && serverIds.has(String(rid))) existsOnServer = true;
                                                if (invNo && serverIds.has(String(invNo))) existsOnServer = true;
                                                if (recId && serverIds.has(String(recId))) existsOnServer = true;
                                                return !existsOnServer;
                                            });
                                            
                                            if (localOnlyRecords.length > 0) {
                                                console.log(`[Smart Merge] ${key}: Preserving ${localOnlyRecords.length} local-only records not yet on server`);
                                                finalVal = [...serverVal, ...localOnlyRecords];
                                                dirtyKeys.add(key);
                                            }
                                        }
                                    } catch(mergeErr) {
                                        console.warn(`[Smart Merge] Failed for ${key}:`, mergeErr);
                                    }
                                }

                                const valStr = typeof finalVal === 'string' ? finalVal : JSON.stringify(finalVal);
                                window.DB_STORE[pk] = valStr;
                                if (typeof localforage !== 'undefined') localforage.setItem(pk, valStr).catch(e=>{});
                                try { localSetItem(pk, valStr); } catch(e){}
                            } else {
                                console.log(`Skipping overwrite of ${key}: server sent empty data but local has data.`);
                            }
                        }
                    });
                    sessionStorage.setItem("cloud_synced", "true");
                    console.log("Cloud Sync: Strict Restore Successful for tenant:", prefix);
                    window.dispatchEvent(new Event('cloudDataRestored'));
                } else {
                    console.warn("Cloud Sync Pull: using local offline cache.");
                }
            }).catch(err => {
                console.warn("Cloud Sync Pull Deferred (Working Offline):", err);
            }).finally(() => {
                hideSyncOverlay();
            });
    };

    // Run pull once on load if needed (deferred to not block instant UI render)
    document.addEventListener("DOMContentLoaded", () => {
        if (window.location.pathname.includes('login')) return;
        if (localStorage.getItem('authToken')) {
            setTimeout(() => {
                window.pullFromCloud().then(() => {
                    window.dispatchEvent(new Event('strictDataReady'));
                }).catch(() => {});
            }, 2000);
        }
    });

})();

// c:\mynewapp\frontend-pwa\js\app.js

// --- Global Theme Initialization (runs immediately to prevent flashing) ---
(function() {
    const activeMasterTheme = localStorage.getItem('magaies_active_theme') || localStorage.getItem('magaiees_global_theme') || localStorage.getItem('magaiees_mobile_theme');
    if (activeMasterTheme) {
        if (!document.documentElement.getAttribute('data-theme')) {
            document.documentElement.setAttribute('data-theme', activeMasterTheme);
        }
        return;
    }
    const savedTheme = localStorage.getItem('erp_theme') || 'dark';
    if (savedTheme === 'light' && !document.documentElement.getAttribute('data-theme')) {
        document.documentElement.setAttribute('data-theme', 'warm_cream');
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

        const isNegative = message.includes('لم يتم') || message.includes('لا يوجد') || message.includes('غير صحيح') || message.includes('خطأ') || message.includes('فشل') || message.includes('تعذر');

        if (!isNegative && (message.includes('نجاح') || message.includes('تم ') || message.includes('بنجاح') || message.includes('اعتمد'))) {
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
            if (typeof window.toggleTheme === 'function') {
                window.toggleTheme();
            } else {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const isLight = ['warm_cream', 'warm-alabaster', 'ultra_white', 'pure-platinum', 'exec_gray', 'pearl-platinum', 'light'].includes(currentTheme);
                const nextTheme = isLight ? 'midnight_obsidian' : 'warm_cream';
                document.documentElement.setAttribute('data-theme', nextTheme);
                localStorage.setItem('magaies_active_theme', nextTheme);
                localStorage.setItem('erp_theme', isLight ? 'dark' : 'light');
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
                // Browser was closed and reopened — restore session instead of forcing re-login
                // This prevents logout after tab crash/restore
                sessionStorage.setItem('session_active', 'true');
            }
        }

        // Sync session_active flag to sessionStorage for backwards compatibility
        if (!sessionStorage.getItem('session_active')) {
            if (localStorage.getItem('authToken') && localStorage.getItem('user')) {
                sessionStorage.setItem('session_active', 'true');
            }
        }

        let token = localStorage.getItem('authToken');
        let userStr = localStorage.getItem('user');
        
        if (!token || !userStr) {
            // Allow cold-start offline execution or localhost fallback without redirecting to login.php
            token = 'offline_token_' + Date.now();
            const defaultLocalUser = {
                id: 1,
                username: 'admin',
                fullName: 'مدير النظام (الباشمهندس مهدي)',
                role: 'admin',
                roleId: 'admin_fallback',
                securityLevel: 10,
                subdomain: ''
            };
            userStr = JSON.stringify(defaultLocalUser);
            localStorage.setItem('authToken', token);
            localStorage.setItem('user', userStr);
            document.cookie = "session_active=true; path=/";
            sessionStorage.setItem('session_active', 'true');
            sessionStorage.setItem('dashboard_unlocked', '1');
        }

        const user = JSON.parse(userStr);
        
        // Admin Detection: True admin or system superadmin
        if (user.username === 'admin' || user.role === 'admin' || user.userType === 'admin' || user.roleId === 'role_admin_default' || user.roleId === 'admin_fallback' || (user.securityLevel >= 9 && user.userType !== 'user')) {
            user.securityLevel = 10;
            user.role = 'admin';
            user.userType = 'admin';
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
                            const modulesList = ['dashboard','sales','purchase','inventory','finance','finance_single','finance_multi','master_data','reports','settings','inventory_rules','posting_accounts','exchange_rates'];
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
                finance_single:{view:true,add:true,edit:true,delete:true,print:true,approve:true},
                finance_multi:{view:true,add:true,edit:true,delete:true,print:true,approve:true},
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
            'purchase-receipt.php': 'purchase',
            'inventory-report.php': 'inventory',
            'warehouse-inventory.php': 'inventory',
            'inventory-movement.php': 'inventory',
            'inventory-summary.php': 'inventory',
            'inventory-analytics.php': 'inventory',
            'inventory.php': 'inventory',
            'item-ledger.php': 'inventory',
            'warehouse-ledger.php': 'inventory',
            'warehouse-transfer.php': 'inventory',
            'vouchers-hub.php': 'finance',
            'cash-receipt.php': 'finance',
            'cash-payment.php': 'finance',
            'single-cash-receipt.php': 'finance_single', // redirects to single-cash-payment.php
            'single-cash-payment.php': 'finance_single',
            'multi-cash-payment.php': 'finance_multi',
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


            'settings.php': 'settings',
            'users-roles.php': 'settings',
            'audit-log.php': 'settings',
            'audit-logs.php': 'settings',
            'posting-accounts.php': 'posting_accounts',
            'purchase-return.php': 'purchase',
            'delivery-note.php': 'sales',
            'bank-transfer.php': 'finance',
            'safe-transfer.php': 'finance',
            'cash-bank-statement.php': 'finance',
            'journal.php': 'finance',
            'general_ledger_v2.php': 'finance',
            'trial-balance.php': 'reports',
            'pl-statement.php': 'reports',
            'mainbalances-report.php': 'reports',
            'balances_modern_hub.php': 'reports',
            'aging-report.php': 'reports',
            'combined_receivables.php': 'reports',
            'daily_master_report.php': 'reports',
            'smart_inventory_report.php': 'inventory',
            'magaiees_unified_dashboard.php': 'dashboard',
            'customer-portfolio.php': 'sales',
            'alarm.php': 'dashboard',
            'engineering_tools.php': 'dashboard',
            'backup.php': 'settings'
        };

        const currentModule = pageModuleMap[currentPage];

        // Access Control (Granular Screen & Module Security)
        if (currentPage !== 'login.php' && currentPage !== 'magaiees_unified_dashboard.php' && currentPage !== 'dashboard-pro.php' && currentPage !== 'index.php') {
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function') {
                if (!window.MagaieesPermissions.canUserAccessScreen(currentPage, user)) {
                    alert('عفواً، ليس لديك صلاحية للدخول إلى هذه الشاشة (' + currentPage + ').');
                    window.location.href = 'magaiees_unified_dashboard.php';
                    return;
                }
                if (typeof window.MagaieesPermissions.enforceScreenSecurity === 'function') {
                    window.MagaieesPermissions.enforceScreenSecurity(user);
                } else if (typeof window.MagaieesPermissions.applyScreenButtonSecurity === 'function') {
                    window.MagaieesPermissions.applyScreenButtonSecurity(currentPage, user);
                }
            } else if (currentModule && currentModule !== 'dashboard') {
                if (!perms[currentModule] || !perms[currentModule].view) {
                    alert('عفواً، ليس لديك صلاحية للدخول إلى هذه الشاشة.');
                    window.location.href = 'magaiees_unified_dashboard.php';
                    return;
                }
            }
        }

        // --- Device Binding Check (Multi-Device & Quotas) ---
        if (user.deviceRestricted && user.role !== 'admin' && user.userType !== 'admin') {
            const localUuid = localStorage.getItem('deviceUuid');
            let isTrusted = false;
            if (localUuid) {
                if (Array.isArray(user.trustedDevices) && user.trustedDevices.length > 0) {
                    isTrusted = user.trustedDevices.some(d => (typeof d === 'string' ? d : d.uuid) === localUuid);
                } else if (user.deviceUuid) {
                    isTrusted = (localUuid === user.deviceUuid);
                }
            }
            if (!isTrusted) {
                const maxDevs = user.maxDevices || 1;
                alert(`عفواً، لا يمكنك العمل من هذا الجهاز. حسابك مقيد للعمل من أجهزة مصرح بها فقط (الحد الأقصى المصرح: ${maxDevs} أجهزة).`);
                if (window.logout) window.logout();
                else { 
                    localStorage.removeItem('authToken'); 
                    sessionStorage.removeItem('session_active');
                    window.location.href = 'login.php'; 
                }
                return;
            }
        }

        // --- Status Check ---
        if (user.status === 'inactive' || user.status === 'forced_logout') {
            alert('تم تعطيل هذا الحساب من قبل الإدارة. غير مصرح لك بالعمل على النظام.');
            if (window.logout) window.logout();
            else { localStorage.removeItem('authToken'); localStorage.removeItem('user'); window.location.href = 'login.php'; }
            return;
        }

        // --- Time Restriction Check (Supports Overnight Shifts & Custom Days) ---
        if (user.timeRestricted && user.role !== 'admin' && user.userType !== 'admin') {
            const now = new Date();
            const currentDay = now.getDay(); // 0 = Sunday, 6 = Saturday
            const allowedDays = Array.isArray(user.allowedDays) ? user.allowedDays : [0, 1, 2, 3, 4, 6];

            const curMinutes = now.getHours() * 60 + now.getMinutes();
            let fromMin = 0, toMin = 1439;
            if (user.timeFrom && user.timeTo) {
                const [fh, fm] = user.timeFrom.split(':').map(Number);
                const [th, tm] = user.timeTo.split(':').map(Number);
                fromMin = fh * 60 + (fm || 0);
                toMin = th * 60 + (tm || 0);
            }
            const isOvernight = fromMin > toMin;
            const shiftDay = (isOvernight && curMinutes <= toMin) ? (currentDay === 0 ? 6 : currentDay - 1) : currentDay;

            if (!allowedDays.includes(currentDay) && !allowedDays.includes(shiftDay)) {
                alert('عفواً، لا يُسمح لك بالدخول في هذا اليوم بناءً على جدول أوقات العمل المحدد لحسابك.');
                if (window.logout) window.logout();
                else { localStorage.removeItem('authToken'); window.location.href = 'login.php'; }
                return;
            }

            if (user.timeFrom && user.timeTo) {
                let isAllowedTime = false;
                if (!isOvernight) {
                    isAllowedTime = (curMinutes >= fromMin && curMinutes <= toMin);
                } else {
                    isAllowedTime = (curMinutes >= fromMin || curMinutes <= toMin);
                }
                if (!isAllowedTime) {
                    alert(`عفواً، الدخول مسموح لك فقط خلال فترة الدوام المحددة لحسابك من الساعة ${user.timeFrom} إلى ${user.timeTo}.`);
                    if (window.logout) window.logout();
                    else { localStorage.removeItem('authToken'); window.location.href = 'login.php'; }
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
                            const radius = user.geoRadius || 500;
                            
                            sessionStorage.setItem('magaiees_last_lat', String(pos.coords.latitude));
                            sessionStorage.setItem('magaiees_last_lng', String(pos.coords.longitude));
                            localStorage.setItem('magaiees_last_lat', String(pos.coords.latitude));
                            localStorage.setItem('magaiees_last_lng', String(pos.coords.longitude));

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
        window.rawNativePrint = originalPrint;
        window.print = function() {
            const isAdminUser = user && (user.is_admin == 1 || user.role === 'admin' || user.role === 'super_admin' || user.is_super == 1 || user.username === 'admin' || user.role === 'مدير النظام');
            const hasModulePerm = perms && (
                (perms[currentModule] && perms[currentModule].print !== false) ||
                (perms.reports && perms.reports.print !== false) ||
                (perms.balances_report && perms.balances_report.print !== false) ||
                (perms.print !== false)
            );
            
            // Allow direct print for Admin, authorized users, or standalone preview pages
            if (isAdminUser || hasModulePerm || !currentModule || currentModule === 'preview' || currentModule === 'balances_report') {
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
        if (window.location.pathname.includes('login') || window.location.pathname.includes('index.php') || window.location.pathname.includes('preview') || document.body.classList.contains('no-sidebar') || document.documentElement.classList.contains('no-sidebar')) return;
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
        const _t = (typeof window.t === 'function') ? window.t : function(k, fb) { return fb || k; };

        const allNavItems = [
            {
                href: 'magaiees_unified_dashboard.php', label: _t('sidebar.dashboard', 'لوحة التحكم'), color: '#22d3ee', module: 'dashboard',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'
            },
            {
                href: 'purchase.php', label: _t('sidebar.purchases', 'المشتريات'), color: '#fb923c', badge: true, module: 'purchase',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/>'
            },
            {
                href: 'direct-sales.php', label: _t('sidebar.sales', 'المبيعات'), color: '#34d399', module: 'sales',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>'
            },
            {
                href: 'vouchers-hub.php', label: _t('sidebar.finance', 'المعاملات المالية'), color: '#fbbf24', module: 'finance',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'
            },
            {
                href: 'inventory-report.php', label: _t('sidebar.inventory', 'المخازن'), color: '#a78bfa', module: 'inventory',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'
            },
            {
                href: 'analytics.php', label: _t('sidebar.reports', 'تقارير مالية'), color: '#38bdf8', module: 'reports',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'
            },
            {
                href: 'master-data.php', label: _t('sidebar.masterData', 'البيانات الأساسية'), color: '#f472b6', module: 'master_data',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>'
            },
            {
                href: 'alarm.php', label: _t('sidebar.alerts', 'التنبيهات'), color: '#f87171', badge: true, module: 'dashboard',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'
            },
            {
                href: 'engineering_tools.php', label: _t('sidebar.tools', 'أدوات مساعدة'), color: '#ec4899', module: 'dashboard',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>'
            },
            {
                href: 'settings.php', label: _t('sidebar.settings', 'الإعدادات'), color: '#a3e635', module: 'settings',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
            }
        ];



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
        <aside class="global-sidebar">
            <div class="sidebar-header">
                <span class="sidebar-logo-icon">
                    <svg width="26" height="26" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <span class="sidebar-logo-text">${_t('sidebar.brand', 'مقاييس (MAGAIEES)')}</span>
            </div>
            <nav class="sidebar-nav">${navHTML}</nav>
            <div class="sidebar-footer">
                <a href="#" onclick="logout();return false;" class="sidebar-item sidebar-logout">
                    <span class="sidebar-icon" style="color:#f87171">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <span class="sidebar-text">${_t('sidebar.logout', 'خروج')}</span>
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

        // --- PREFETCH: Pre-download pages on hover for instant navigation ---
        const _prefetched = new Set();
        document.querySelectorAll('.sidebar-item[data-href]').forEach(link => {
            link.addEventListener('mouseenter', () => {
                const href = link.getAttribute('data-href');
                if (href && !_prefetched.has(href) && href !== currentPage) {
                    _prefetched.add(href);
                    const prefetchLink = document.createElement('link');
                    prefetchLink.rel = 'prefetch';
                    prefetchLink.href = href;
                    prefetchLink.as = 'document';
                    document.head.appendChild(prefetchLink);
                }
            }, { passive: true, once: true });
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
    
    // --- 5b. Posting Mode Helper ---
    window.getPostingMode = function() {
        return localStorage.getItem('erp_posting_mode') || 'auto';
    };
    window.setPostingMode = function(mode) {
        localStorage.setItem('erp_posting_mode', mode);
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
        const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
        const settings = JSON.parse(localStorage.getItem(tk) || '{}');
        if (settings.autoBackup === false) return; // User disabled auto backup

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
            timeEl.innerText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        }
        if (dateEl) {
            dateEl.innerText = now.toLocaleDateString('ar-EG-u-nu-latn', { weekday: 'short', day: 'numeric', month: 'short' });
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
    
    // Fetch tenant-specific exchange rate on startup for multi-user sync
    if (localStorage.getItem('authToken')) {
        const _base = (() => { const o = window.location.origin; if (o === 'https://elsajana.com' || o === 'https://www.elsajana.com') return 'https://c.elsajana.com/'; let d = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')); if (d.endsWith('/')) d = d.slice(0,-1); return o + (d||'') + '/'; })();
        fetch(_base + 'api_index.php/tenant-exchange-rate', {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('authToken')}` }
        }).then(r => r.json()).then(data => {
            if (data && data.success && data.payload) {
                const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
                let str = localStorage.getItem(tk) || localStorage.getItem('erp_settings_v1');
                let s = {};
                try { s = JSON.parse(str || '{}'); } catch(e){}
                
                // Force sync the exchange settings from server
                if (data.payload.exchangeMode) {
                    s.exchangeMode = data.payload.exchangeMode;
                    if (data.payload.rateUSD) s.rateUSD = parseFloat(data.payload.rateUSD);
                    if (data.payload.rateSAR) s.rateSAR = parseFloat(data.payload.rateSAR);
                    if (data.payload.rateAED) s.rateAED = parseFloat(data.payload.rateAED);
                    
                    localStorage.setItem(tk, JSON.stringify(s));
                    if (window.DB_STORE) window.DB_STORE[tk] = JSON.stringify(s); // Update proxy store as well
                }
            }
        }).catch(e => {});
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
window.logout = async function(force = false) {
    if (!navigator.onLine && !force) {
        if (!confirm('أنت غير متصل بالإنترنت. تسجيل الخروج سيمسح بياناتك المحلية ولن تستطيع الوصول لها مجدداً حتى تعاود الاتصال بالإنترنت. هل أنت متأكد من تسجيل الخروج؟')) {
            return;
        }
    }
    
    // Ensure pending changes are pushed
    if (typeof dirtyKeys !== 'undefined' && dirtyKeys.size > 0 && navigator.onLine) {
        console.log("Pushing dirty keys before logout...");
        const btn = document.querySelector('.sidebar-logout');
        if (btn) btn.innerHTML = '<span class="sidebar-text">جاري المزامنة...</span>';
        try {
            await window.pushToCloud(true);
        } catch(e) {
            console.warn("Failed to push during logout", e);
        }
    }

    // 1. Invalidate in-memory store & tenant prefix cache
    window.DB_STORE = {};
    if (typeof window._clearTenantCache === 'function') window._clearTenantCache();

    // 2. Remove Core Session & Un-isolated Keys
    localStorage.removeItem('authToken');
    localStorage.removeItem('user');
    localStorage.removeItem('currentTenantId');
    localStorage.removeItem('companyName');
    localStorage.removeItem('company_logo_base64');
    sessionStorage.removeItem('session_active');
    sessionStorage.removeItem('dashboard_unlocked');
    sessionStorage.removeItem('cloud_synced');
    
    // 3. Clear All Session & Backup Cookies
    document.cookie = "saas_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "currentTenantId=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "erp_settings_backup=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "erp_rate_usd_backup=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "session_active=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    
    // 4. Redirect
    window.location.href = 'login.php';
};

// --- GLOBAL EXCHANGE RATE MODAL ---
window.openGlobalExchangeRateModal = function(callback) {
    let modal = document.getElementById('global-exchange-modal');
    if (!modal) {
        const modalHtml = `
            <div id="global-exchange-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:999999; justify-content:center; align-items:center; font-family:'Tajawal', sans-serif;">
                <div style="background:#1e293b; padding:25px 35px; border-radius:18px; border:1px solid #38bdf8; width:90%; max-width:460px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.6);">
                    <h3 style="color:#f8fafc; margin-top:0; font-size:1.25rem;">💱 وحدة التحكم بسعر الصرف المركزي</h3>
                    <p style="color:#94a3b8; font-size:0.85rem; margin-bottom:18px;">أي تعديل هنا سينعكس على كل الشاشات لحظياً ويحفظ في الإعدادات المركزية.</p>
                    
                    <div style="text-align: right; margin-bottom: 15px;">
                        <label style="color: #cbd5e1; font-size: 0.9rem; display: block; margin-bottom: 6px; font-weight:bold;">مصدر سعر الصرف:</label>
                        <select id="gem-source" style="width: 100%; padding: 10px 14px; border-radius: 10px; background: #090d16; border: 1px solid #334155; color: #f8fafc; font-family: 'Tajawal'; font-size: 1rem; cursor:pointer;">
                            <option value="saas">سحب تلقائي من منصة المشتركين (SaaS)</option>
                            <option value="manual">إدخال يدوي مخصص</option>
                        </select>
                    </div>

                    <div style="text-align: right; margin-bottom: 20px;">
                        <label style="color: #cbd5e1; font-size: 0.9rem; display: block; margin-bottom: 6px; font-weight:bold;">سعر صرف الدولار (USD) الحالي:</label>
                        <input type="number" id="gem-rate" step="0.01" style="width: 100%; padding: 12px 16px; border-radius: 12px; background: #090d16 !important; border: 2px solid #f59e0b; color: #f59e0b; font-family: 'JetBrains Mono', 'Outfit', sans-serif; font-size: 2.2rem; font-weight: 900; text-align: center; letter-spacing: 2px; box-sizing: border-box; transition: all 0.3s; box-shadow: inset 0 2px 6px rgba(0,0,0,0.8), 0 0 15px rgba(245, 158, 11, 0.2);">
                        <div id="gem-rate-indicator" style="margin-top: 10px; font-size: 0.9rem; font-weight: bold; min-height: 32px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;"></div>
                    </div>
                    
                    <div id="gem-auth-warning" style="display:none; color: #ef4444; font-size: 0.85rem; margin-bottom: 15px; text-align: right; background: rgba(239,68,68,0.1); padding: 8px 12px; border-radius: 8px;">
                        ⚠️ لا تملك صلاحية تعديل السعر المركزي. السعر للقراءة فقط.
                    </div>

                    <div style="display:flex; gap:15px; justify-content:center;">
                        <button id="btn-gem-save" style="flex:1; background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#34d399; padding:12px; border-radius:10px; cursor:pointer; font-weight:bold; font-size:1rem; transition:all 0.2s;">
                            💾 حفظ واعتماد
                        </button>
                        <button id="btn-gem-cancel" style="flex:1; background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#f87171; padding:12px; border-radius:10px; cursor:pointer; font-weight:bold; font-size:1rem; transition:all 0.2s;">
                            ❌ إلغاء
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('global-exchange-modal');

        document.getElementById('btn-gem-cancel').onclick = () => {
            modal.style.display = 'none';
        };

        const updateRateTrendIndicator = () => {
            const val = parseFloat(document.getElementById('gem-rate').value) || 0;
            const ind = document.getElementById('gem-rate-indicator');
            const input = document.getElementById('gem-rate');
            const prevRate = window._gemInitialRate || val;
            if (!ind || !val || !prevRate) return;

            const diff = val - prevRate;
            if (diff < -0.01) {
                // Downward price drop -> GREEN with green down arrow ⬇️
                input.style.color = '#10b981';
                input.style.borderColor = '#10b981';
                input.style.boxShadow = 'inset 0 2px 6px rgba(0,0,0,0.8), 0 0 20px rgba(16,185,129,0.4)';
                ind.innerHTML = `<div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 6px 16px; border-radius: 20px; font-size: 0.95rem; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;"><span style="font-size: 1.2rem; color: #10b981;">⬇️</span> <span>انخفاض في سعر الصرف بمقدار <strong>${Math.abs(diff).toFixed(2)}</strong> ج.س.</span></div>`;
            } else if (diff > 0.01) {
                // Upward price increase -> RED with red up arrow ⬆️
                input.style.color = '#ef4444';
                input.style.borderColor = '#ef4444';
                input.style.boxShadow = 'inset 0 2px 6px rgba(0,0,0,0.8), 0 0 20px rgba(239,68,68,0.4)';
                ind.innerHTML = `<div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 6px 16px; border-radius: 20px; font-size: 0.95rem; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;"><span style="font-size: 1.2rem; color: #ef4444;">⬆️</span> <span>ارتفاع في سعر الصرف بمقدار <strong>${diff.toFixed(2)}</strong> ج.س.</span></div>`;
            } else {
                // Same price -> Golden Orange
                input.style.color = '#f59e0b';
                input.style.borderColor = '#f59e0b';
                input.style.boxShadow = 'inset 0 2px 6px rgba(0,0,0,0.8), 0 0 15px rgba(245,158,11,0.2)';
                ind.innerHTML = `<div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; padding: 6px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;"><span style="font-size: 1rem;">⚖️</span> <span>السعر الحالي المعتمد: <strong>${val}</strong> ج.س.</span></div>`;
            }
        };

        document.getElementById('gem-rate').addEventListener('input', updateRateTrendIndicator);
        document.getElementById('gem-rate').addEventListener('change', updateRateTrendIndicator);

        document.getElementById('gem-source').addEventListener('change', function() {
            const hasPerm = (typeof window.hasPermission === 'function' ? window.hasPermission('settings', 'edit') : true) || 
                            (JSON.parse(localStorage.getItem('user')||'{}').securityLevel >= 8);
            if (this.value === 'saas') {
                document.getElementById('gem-rate').readOnly = true;
                document.getElementById('gem-rate').style.opacity = '0.85';
                document.getElementById('gem-rate').style.background = 'rgba(56,189,248,0.15)';
                document.getElementById('gem-rate').style.borderColor = '#38bdf8';
                
                let lastSaas = parseFloat(localStorage.getItem('erp_saas_rate_usd'));
                if (lastSaas && lastSaas > 0) {
                    document.getElementById('gem-rate').value = lastSaas;
                    if(typeof updateRateTrendIndicator === 'function') updateRateTrendIndicator();
                }

                // Fetch from SaaS
                const _base = (() => { const o = window.location.origin; if (o === 'https://elsajana.com' || o === 'https://www.elsajana.com') return 'https://c.elsajana.com/'; let d = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')); if (d.endsWith('/')) d = d.slice(0,-1); return o + (d||'') + '/'; })();
                const token = localStorage.getItem('authToken');
                if (token) {
                    const btn = document.getElementById('btn-gem-save');
                    const origBtnText = btn.innerHTML;
                    btn.innerHTML = 'جاري السحب...';
                    btn.disabled = true;
                    fetch(_base + 'api_index.php/exchange-rate', {
                        headers: { 'Authorization': `Bearer ${token}` }
                    }).then(r => r.json()).then(data => {
                        btn.innerHTML = origBtnText;
                        btn.disabled = false;
                        if (data && data.rate && data.visible) {
                            document.getElementById('gem-rate').value = parseFloat(data.rate);
                            localStorage.setItem('erp_saas_rate_usd', data.rate);
                            updateRateTrendIndicator();
                        } else {
                            alert('لم يتم العثور على سعر للصرف في المنصة.');
                        }
                    }).catch(e => {
                        btn.innerHTML = origBtnText;
                        btn.disabled = false;
                        console.error('Error fetching rate:', e);
                    });
                }
            } else {
                if (hasPerm) {
                    const rateEl = document.getElementById('gem-rate');
                    rateEl.readOnly = false;
                    rateEl.disabled = false;
                    rateEl.style.opacity = '1';
                    rateEl.style.background = '#090d16';
                    rateEl.style.borderColor = '#f59e0b';
                    let lastManual = parseFloat(localStorage.getItem('erp_manual_rate_usd'));
                    if (lastManual && lastManual > 0) {
                        rateEl.value = lastManual;
                        if(typeof updateRateTrendIndicator === 'function') updateRateTrendIndicator();
                    }
                    rateEl.focus();
                    rateEl.select();
                }
            }
        });

        document.getElementById('btn-gem-save').onclick = () => {
            const hasPerm = (typeof window.hasPermission === 'function' ? window.hasPermission('settings', 'edit') : true) || 
                            (JSON.parse(localStorage.getItem('user')||'{}').securityLevel >= 8);
            
            if (!hasPerm) {
                alert('لا تملك الصلاحيات لتعديل السعر المركزي.');
                return;
            }

            const source = document.getElementById('gem-source').value;
            const newRate = parseFloat(document.getElementById('gem-rate').value);
            if (!newRate || newRate <= 0) {
                alert('الرجاء إدخال سعر صحيح.');
                return;
            }

            // Save to settings
            window.SYSTEM_USD_RATE = newRate;
            window._cachedGlobalRate = newRate;
            const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
            let str = localStorage.getItem(tk) || localStorage.getItem('erp_settings_v1');
            let s = {};
            try { s = JSON.parse(str || '{}') || {}; } catch(e){}
            
            s.exchangeMode = (source === 'saas' ? 'global' : 'manual');
            s._userExplicitManual = (source === 'manual');
            s.rateUSD = newRate;
            s.exchangeRate = newRate;
            localStorage.setItem(tk, JSON.stringify(s));
            if(tk !== 'erp_settings_v1') localStorage.setItem('erp_settings_v1', JSON.stringify(s));
            localStorage.setItem('erp_rate_usd', newRate);
            localStorage.setItem('magaiees_system_usd_rate', newRate);
            localStorage.setItem('magaies_custom_usd_rate', newRate);
            localStorage.setItem('magaies_usd_rate', newRate);
            localStorage.setItem('erp_cached_global_rate', newRate);
            if (source === 'manual') {
                localStorage.setItem('erp_manual_rate_usd', newRate);
            }
            try {
                let crObj = JSON.parse(localStorage.getItem('erp_currency_rates_v1') || '{}') || {};
                crObj.USD = newRate;
                crObj.SDG = 1;
                localStorage.setItem('erp_currency_rates_v1', JSON.stringify(crObj));
            } catch(e){}
            try {
                const _base = (() => { const o = window.location.origin; if (o === 'https://elsajana.com' || o === 'https://www.elsajana.com') return 'https://c.elsajana.com/'; let d = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')); if (d.endsWith('/')) d = d.slice(0,-1); return o + (d||'') + '/'; })();
                fetch(_base + 'api/mirror_sync.php?action=save_currency_rate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'save_currency_rate', rate: newRate })
                }).catch(() => {});
                if (localStorage.getItem('authToken')) {
                    fetch(_base + 'api_index.php/tenant-exchange-rate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                        },
                        body: JSON.stringify({
                            payload: {
                                exchangeMode: source,
                                rateUSD: newRate,
                                current: newRate,
                                rate: newRate
                            }
                        })
                    }).catch(() => {});
                }
            } catch(e){}

            // Save to History Log
            const historyKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
            let hist = [];
            try { hist = JSON.parse(localStorage.getItem(historyKey) || '[]'); } catch(e){}
            
            const usr = JSON.parse(localStorage.getItem('user')||'{}');
            const now = new Date();
            
            hist.push({
                id: 'HR-' + Date.now(),
                rate: newRate,
                date: now.toISOString().split('T')[0],
                time: now.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12:true}),
                user: usr.name || usr.username || 'مدير النظام',
                source: source
            });
            localStorage.setItem(historyKey, JSON.stringify(hist));

            modal.style.display = 'none';
            if (typeof callback === 'function') {
                callback(newRate);
            }
            if (typeof window.showToast === 'function') {
                window.showToast('تم اعتماد السعر المركزي بنجاح!', 'success');
            } else {
                alert('تم التعديل بنجاح');
            }
        };
    }

    // Load current settings
    const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
    let str = localStorage.getItem(tk) || localStorage.getItem('erp_settings_v1');
    let s = {};
    try { s = JSON.parse(str || '{}') || {}; } catch(e){}
    
    let rate = (window.SYSTEM_USD_RATE && parseFloat(window.SYSTEM_USD_RATE) > 1 ? parseFloat(window.SYSTEM_USD_RATE) : 0) || parseFloat(localStorage.getItem('erp_rate_usd')) || s.rateUSD || 5999;
    let mode = (s.exchangeMode === 'manual' && s._userExplicitManual) ? 'manual' : 'saas';
    
    window._gemInitialRate = rate;

    const rateInput = document.getElementById('gem-rate');
    rateInput.value = rate;
    document.getElementById('gem-source').value = mode;
    
    // Check Permission
    const hasPerm = (typeof window.hasPermission === 'function' ? window.hasPermission('settings', 'edit') : true) || 
                    (JSON.parse(localStorage.getItem('user')||'{}').securityLevel >= 8);
    
    if (!hasPerm) {
        rateInput.readOnly = true;
        document.getElementById('gem-source').disabled = true;
        document.getElementById('btn-gem-save').style.display = 'none';
        document.getElementById('gem-auth-warning').style.display = 'block';
    } else {
        document.getElementById('gem-source').disabled = false;
        document.getElementById('btn-gem-save').style.display = 'block';
        document.getElementById('gem-auth-warning').style.display = 'none';
        
        if (mode === 'manual') {
            rateInput.readOnly = false;
            rateInput.disabled = false;
            rateInput.style.opacity = '1';
            rateInput.style.background = '#090d16';
            rateInput.style.borderColor = '#f59e0b';
            rateInput.style.color = '#f59e0b';
            setTimeout(() => { rateInput.focus(); rateInput.select(); }, 150);
        } else {
            rateInput.readOnly = true;
            rateInput.style.opacity = '0.85';
            rateInput.style.background = 'rgba(56,189,248,0.15)';
            rateInput.style.borderColor = '#38bdf8';
            rateInput.style.color = '#38bdf8';
        }
    }

    // Initialize rate trend indicator
    if (rateInput) {
        const ind = document.getElementById('gem-rate-indicator');
        if (ind) ind.innerHTML = `<div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; padding: 6px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;"><span style="font-size: 1rem;">⚖️</span> <span>السعر الحالي المعتمد: <strong>${rate}</strong> ج.س.</span></div>`;
    }

    modal.style.display = 'flex';
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

window.toggleExchangeHistory = function(e) {
    if (e) e.stopPropagation();
    const dropdown = document.getElementById('exchange-rate-history-dropdown');
    if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'none' ? 'flex' : 'none';
    }
};

document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('exchange-rate-history-dropdown');
    // If click is outside the box and the dropdown is open, close it
    // Wait, the dropdown overlay itself spans 100vw/100vh and catches clicks, 
    // so we don't strictly need this document click listener, but we can leave it safe.
    if (dropdown && dropdown.style.display === 'flex') {
        // We already have onclick="toggleExchangeHistory(event)" on the overlay backdrop
        // so clicking the dark area closes it. No action needed here to prevent double toggling.
    }
});

function updateExchangeRateUI(data) {
    const exBox = document.getElementById('global-exchange-rate-box');
    const exVal = document.getElementById('global-exchange-rate-val');
    const exTrend = document.getElementById('global-exchange-rate-trend');

    if (exBox && exVal && data) {
        if (data.rate) {
            window._cachedGlobalRate = parseFloat(data.rate);
            localStorage.setItem('erp_cached_global_rate', window._cachedGlobalRate);
        }
        if (data.visible) {
            exBox.style.display = 'block';
            exVal.innerText = parseFloat(data.rate).toLocaleString('en-US');
            
            // Determine stability (48 hours)
            let isStable = false;
            let latestChangeDate = null;
            if (data.history && data.history.length > 0) {
                const latest = data.history[0];
                latestChangeDate = new Date(`${latest.date}T${latest.time}:00`);
                const now = new Date();
                const diffHours = (now - latestChangeDate) / (1000 * 60 * 60);
                if (diffHours >= 48) {
                    isStable = true;
                }
            } else {
                isStable = true; // No history = stable
            }

            // Apply Styling only to the Arrow, keep Box background unchanged
            if (isStable) {
                if (exTrend) exTrend.innerHTML = '<span style="color:#f59e0b; font-size:0.85rem; padding-left:4px;">■</span>';
            } else if (data.trend === 'up') {
                if (exTrend) exTrend.innerHTML = '<span style="color:#ef4444; font-size:0.85rem; padding-left:4px;">▲</span>';
            } else if (data.trend === 'down') {
                if (exTrend) exTrend.innerHTML = '<span style="color:#10b981; font-size:0.85rem; padding-left:4px;">▼</span>';
            }

            // Create Modal Dynamically in body to bypass backdrop-filter stacking contexts
            let modal = document.getElementById('exchange-rate-history-dropdown');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'exchange-rate-history-dropdown';
                modal.style.cssText = 'display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); z-index:999999; justify-content:center; align-items:center; cursor:default;';
                modal.onclick = window.toggleExchangeHistory;
                modal.innerHTML = `
                    <div style="background:var(--bg-secondary, #1e293b); border:1px solid var(--border-color, #334155); border-radius:16px; width:90%; max-width:350px; padding:20px; box-shadow:0 15px 35px rgba(0,0,0,0.6); text-align:right; position:relative;" onclick="event.stopPropagation()">
                        <div style="color:var(--text-primary, #f8fafc); font-size:1.1rem; font-weight:bold; margin-bottom:15px; border-bottom:1px solid var(--border-color, #334155); padding-bottom:10px; text-align:center; display:flex; justify-content:space-between; align-items:center;">
                            <span>سجل تغيرات سعر الصرف</span>
                            <span style="cursor:pointer; color:var(--text-muted, #94a3b8); font-size:1.2rem;" onclick="window.toggleExchangeHistory()">✕</span>
                        </div>
                        <div id="exchange-rate-history-list" style="max-height:300px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;">
                            <div style="text-align:center; color:var(--text-secondary, #cbd5e1); font-size:0.85rem;">جاري التحميل...</div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const exHistoryList = document.getElementById('exchange-rate-history-list');
            if (exHistoryList) {
                exHistoryList.innerHTML = '';
                if (data.history && data.history.length > 0) {
                    data.history.forEach(item => {
                        let arrow = '';
                        let color = 'var(--text-primary, #f8fafc)';
                        if (item.trend === 'up') { arrow = '▲'; color = '#ef4444'; }
                        if (item.trend === 'down') { arrow = '▼'; color = '#10b981'; }
                        
                        exHistoryList.innerHTML += `
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:6px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:0.85rem;">
                                <div style="display:flex; flex-direction:column; align-items:flex-end;">
                                    <span style="color:var(--text-secondary, #cbd5e1); font-size:0.75rem;">${item.date}</span>
                                    <span style="color:rgba(255,255,255,0.4); font-size:0.65rem;">${item.time}</span>
                                </div>
                                <div style="font-weight:bold; color:${color}; display:flex; align-items:center; gap:6px;">
                                    ${parseFloat(item.rate).toLocaleString('en-US')} ${arrow}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    exHistoryList.innerHTML = '<div style="text-align:center; color:var(--text-secondary, #cbd5e1); font-size:0.8rem; padding:10px;">لا توجد سجلات سابقة</div>';
                }
            }
        } else {
            exBox.style.display = 'none';
        }
    }
}

// --- EXCHANGE RATE: Load on page load ---
document.addEventListener('DOMContentLoaded', () => {
    fetch('api_index.php/exchange-rate', {
        headers: { "Authorization": `Bearer ${localStorage.getItem('authToken')}` }
    })
        .then(res => res.json())
        .then(data => {
            if (data) updateExchangeRateUI(data);
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
            const res = await fetch('api_index.php?r=ping', {
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
                    updateExchangeRateUI(data.exchange_rate);
                }
            }
        } catch (e) {
            console.log('Ping failed', e);
        }
    }
    
    // Initial ping
    setTimeout(pingOnlineUsers, 3000);
    
    // Ping every 2 minutes (reduced from 30s to improve performance)
    setInterval(pingOnlineUsers, 120000);
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
        const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
        const str = localStorage.getItem(tk) || localStorage.getItem('erp_settings_v1');
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

window.getExchangeRateForCurrency = function(currency) {
    if (!currency || currency === 'SDG') return 1;
    
    // Always prioritize live database USD rate if available
    const dbUsd = (window.SYSTEM_USD_RATE && parseFloat(window.SYSTEM_USD_RATE) > 1) ? parseFloat(window.SYSTEM_USD_RATE) : 0;
    if (currency === 'USD' && dbUsd > 1) return dbUsd;

    let settings = {};
    try {
        const str = localStorage.getItem(typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1') || localStorage.getItem('erp_settings_v1');
        if (str) settings = JSON.parse(str) || {};
    } catch(e) {}
    
    // Check fallback sources for USD rate if missing
    let fallbackUsdRate = dbUsd;
    if (fallbackUsdRate <= 1 && !settings.rateUSD) {
        try { 
            const dedicated = localStorage.getItem('erp_rate_usd') || localStorage.getItem('magaiees_system_usd_rate') || localStorage.getItem('magaies_usd_rate');
            if (dedicated) fallbackUsdRate = parseFloat(dedicated);
        } catch(e) {}
        
        if (fallbackUsdRate <= 0) {
            try {
                const cookieMatch = document.cookie.match(/erp_rate_usd_backup=([0-9.]+)/);
                if (cookieMatch) fallbackUsdRate = parseFloat(cookieMatch[1]);
            } catch(e) {}
        }
    }
    if (fallbackUsdRate > 1) {
        settings.rateUSD = fallbackUsdRate;
    }
    
    const mode = settings.exchangeMode || 'global';
    
    if (mode === 'manual') {
        // Manual mode: use rates from settings
        if (currency === 'USD' && settings.rateUSD) return parseFloat(settings.rateUSD);
        if (currency === 'SAR' && settings.rateSAR) return parseFloat(settings.rateSAR);
        if (currency === 'AED' && settings.rateAED) return parseFloat(settings.rateAED);
        // Cross-rate fallback from USD
        if (settings.rateUSD) {
            const usd = parseFloat(settings.rateUSD);
            if (usd > 0) {
                if (currency === 'SAR') return parseFloat((usd / 3.75).toFixed(4));
                if (currency === 'AED') return parseFloat((usd / 3.67).toFixed(4));
                if (currency === 'EGP') return parseFloat((usd / 50.0).toFixed(4));
            }
        }
        return 0; // Return 0 so the user knows they need to enter a rate
    }
    
    // Global mode: use central rate from DB / header / cached API response
    let centralRate = dbUsd;
    
    // Try header element next
    if (centralRate <= 0) {
        const centralValEl = document.getElementById('global-exchange-rate-val');
        if (centralValEl) {
            const txt = centralValEl.innerText.replace(/,/g, '').trim();
            if (txt && txt !== '-') centralRate = parseFloat(txt) || 0;
        }
    }
    
    // Try cached rate
    if (centralRate <= 0 && window._cachedGlobalRate) {
        centralRate = window._cachedGlobalRate;
    }
    if (centralRate <= 0) {
        try {
            const cachedRateStr = localStorage.getItem('erp_cached_global_rate') || localStorage.getItem('erp_rate_usd') || localStorage.getItem('magaiees_system_usd_rate');
            if (cachedRateStr) centralRate = parseFloat(cachedRateStr);
        } catch(e) {}
    }
    
    if (centralRate > 0) {
        if (currency === 'USD') return centralRate;
        if (currency === 'SAR') return parseFloat((centralRate / 3.75).toFixed(4));
        if (currency === 'AED') return parseFloat((centralRate / 3.67).toFixed(4));
        if (currency === 'EGP') return parseFloat((centralRate / 50.0).toFixed(4));
    }
    
    // Fallback: try manual rates even in global mode
    if (settings.rateUSD && currency === 'USD') return parseFloat(settings.rateUSD);
    if (settings.rateSAR && currency === 'SAR') return parseFloat(settings.rateSAR);
    if (settings.rateAED && currency === 'AED') return parseFloat(settings.rateAED);
    
    return currency === 'USD' ? 5999 : 0;
};

// Pre-fetch the global exchange rate from database so it's available immediately
(function() {
    try {
        fetch('api/mirror_sync.php?action=get_currency_rate&_t=' + Date.now())
            .then(r => r.json())
            .then(d => {
                if (d && d.rate && parseFloat(d.rate) > 1) {
                    const dbRate = parseFloat(d.rate);
                    window.SYSTEM_USD_RATE = dbRate;
                    window._cachedGlobalRate = dbRate;
                    localStorage.setItem('erp_cached_global_rate', String(dbRate));
                    localStorage.setItem('erp_rate_usd', String(dbRate));
                    localStorage.setItem('magaiees_system_usd_rate', String(dbRate));
                    localStorage.setItem('magaies_custom_usd_rate', String(dbRate));
                    localStorage.setItem('magaies_usd_rate', String(dbRate));
                }
            }).catch(() => {});
    } catch(e) {}
})();

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
    let _cachedAutoLogoutTime = null;
    let _cacheTimestamp = 0;
    function resetActivityTimer() {
        if (activityTimer) clearTimeout(activityTimer);
        
        // Cache settings for 30 seconds to avoid parsing JSON on every mouse move
        const now = Date.now();
        if (_cachedAutoLogoutTime === null || (now - _cacheTimestamp) > 30000) {
            let settings = {};
            try {
                const str = localStorage.getItem(typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1') || localStorage.getItem('erp_settings_v1');
                if (str) settings = JSON.parse(str);
            } catch(e) {}
            
            try {
                if (window.DB_STORE && window.getTenantKey) {
                    const tk = window.getTenantKey('erp_settings_v1');
                    if (window.DB_STORE[tk]) settings = JSON.parse(window.DB_STORE[tk]);
                }
            } catch(e) {}

            const autoLogoutTimeStr = settings.autoLogoutTime || "0";
            _cachedAutoLogoutTime = parseInt(autoLogoutTimeStr, 10);
            _cacheTimestamp = now;
        }
        
        if (_cachedAutoLogoutTime > 0 && !window.location.pathname.includes('login.php') && !window.location.hostname.includes('localhost') && !window.location.hostname.includes('127.0.0.1')) {
            activityTimer = setTimeout(() => {
                alert('تم تسجيل الخروج التلقائي بسبب عدم النشاط للحفاظ على الأمان.');
                if (window.logout) window.logout(true);
            }, _cachedAutoLogoutTime * 1000);
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




// ==========================================
// GLOBAL PRINT ENHANCEMENTS (Preview & Header)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Inject "Print Preview" button next to any print button
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => {
        const onClickAttr = btn.getAttribute('onclick') || '';
        if (onClickAttr.includes('window.print()') || onClickAttr.includes('printVoucher()') || onClickAttr.includes('printInvoice()')) {
            // Skip injection on inventory/ledger/single-cash-payment pages as requested by user
            const currentPath = window.location.pathname.toLowerCase();
            if (currentPath.includes('inventory') || currentPath.includes('ledger') || currentPath.includes('analytics') || currentPath.includes('single-cash-payment') || currentPath.includes('alarm')) {
                return;
            }

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

// Auto-enable for all tables on page load and inject mobile layout helpers
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('table.pro-table').forEach(table => {
            window.enableTableResizing(table);
        });
    }, 500);

    // Inject mobile bottom navigation bar if screen is mobile-sized
    if (window.innerWidth <= 768) {
        if (!window.location.pathname.includes('login.php') && !window.location.pathname.includes('register.php')) {
            const nav = document.createElement('div');
            nav.className = 'mobile-bottom-nav';
            
            const pages = [
                { name: 'الرئيسية', icon: '📊', url: 'dashboard-pro.php' },
                { name: 'البيانات', icon: '📁', url: 'master-data.php' },
                { name: 'المبيعات', icon: '💰', url: 'direct-sales.php' },
                { name: 'تقارير مالية', icon: '📈', url: 'financial-reports.php' },
                { name: 'الإعدادات', icon: '⚙️', url: 'settings.php' }
            ];
            
            const currentPath = window.location.pathname;
            
            nav.innerHTML = pages.map(p => {
                const isActive = currentPath.includes(p.url) ? 'active' : '';
                return `
                    <a href="${p.url}" class="mobile-nav-item ${isActive}">
                        <span class="icon">${p.icon}</span>
                        <span>${p.name}</span>
                    </a>
                `;
            }).join('');
            
            document.body.appendChild(nav);
        }
    }
});

// Helper to open general ledger for a specific account in a new tab
window.openGeneralLedgerForAccount = function(accountId) {
    if (!accountId) {
        alert('الرجاء اختيار الحساب أولاً.');
        return;
    }
    window.open(`general-ledger.php?accountId=${accountId}`, '_blank');
};



// ==========================================
// GLOBAL NUMERIC FIELD PROTECTION (IRONCLAD)
// ==========================================
document.addEventListener('keypress', function(e) {
    if (e.target && e.target.tagName === 'INPUT') {
        const t = e.target;
        const isNumField = (
            t.type === 'number' || 
            t.inputMode === 'decimal' || 
            t.inputMode === 'numeric' || 
            (t.id && (t.id.toLowerCase().includes('amount') || t.id.toLowerCase().includes('price') || t.id.toLowerCase().includes('qty') || t.id.toLowerCase().includes('amt') || t.id.toLowerCase().includes('total') || t.id.toLowerCase().includes('balance')))
        );

        if (isNumField) {
            const charCode = (e.which) ? e.which : e.keyCode;
            // Allow numbers (48-57), dot (46), minus (45)
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46 && charCode !== 45) {
                e.preventDefault();
            }
        }
    }
});

// Also prevent pasting letters
document.addEventListener('paste', function(e) {
    if (e.target && e.target.tagName === 'INPUT') {
        const t = e.target;
        const isNumField = (
            t.type === 'number' || 
            t.inputMode === 'decimal' || 
            t.inputMode === 'numeric' || 
            (t.id && (t.id.toLowerCase().includes('amount') || t.id.toLowerCase().includes('price') || t.id.toLowerCase().includes('qty') || t.id.toLowerCase().includes('amt') || t.id.toLowerCase().includes('total') || t.id.toLowerCase().includes('balance')))
        );
        if (isNumField) {
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            if (/[^0-9.-]/.test(paste)) {
                e.preventDefault();
            }
        }
    }
});

// --- Universal Report Print Preview System ---
window.openReportPreview = function(title, targetSelector) {
    let target = null;
    if (typeof targetSelector === 'string') {
        target = document.getElementById(targetSelector) || 
                 document.querySelector(targetSelector) ||
                 document.getElementById(targetSelector.replace('#', '')) ||
                 document.querySelector('.table-wrapper') ||
                 document.querySelector('.table-panel') ||
                 document.querySelector('.dash-table') ||
                 document.querySelector('.report-table');
    } else {
        target = targetSelector;
    }
    if (!target) {
        alert('لم يتم العثور على جدول التقرير!');
        return;
    }

    let modal = document.getElementById('report-preview-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'report-preview-modal';
        modal.className = 'modal-overlay';
        modal.setAttribute('style', `
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(15, 23, 42, 0.9); z-index: 11000; align-items: center; 
            justify-content: center; direction: rtl; backdrop-filter: blur(8px);
        `);
        modal.innerHTML = `
            <div class="glass-panel" style="width: 100%; max-width: 100%; height: 100vh; display: flex; flex-direction: column; padding: 18px 22px; border: none; background: #0b1120; border-radius: 0; color:#fff; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:15px; margin-bottom:15px;" class="no-print">
                    <h3 style="margin:0; font-weight:900; color:#38bdf8; display:flex; align-items:center; gap:10px; font-size:1.25rem;">
                        <span>👁️</span> معاينة الطباعة الاحترافية
                    </h3>
                    <div style="display:flex; gap:10px;">
                        <button onclick="printReportFromPreview()" class="btn-action" style="background:linear-gradient(135deg,#38bdf8,#0284c7); color:white; border:none; padding:8px 20px; font-weight:700; border-radius:8px; cursor:pointer; box-shadow:0 4px 10px rgba(56,189,248,0.2);">🖨️ طباعة التقرير</button>
                        <button onclick="closeReportPreview()" class="btn-action" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:white; padding:8px 15px; border-radius:8px; cursor:pointer;">إغلاق</button>
                    </div>
                </div>
                
                <div style="flex:1; overflow-y:auto; background:#f1f5f9; padding:45px 20px; display:flex; justify-content:center; border-radius:12px;" class="preview-scroll-container">
                    <div id="report-preview-sheet" style="width:100%; max-width:1200px; background:#fff; color:#000; padding:45px; box-shadow:0 10px 30px rgba(0,0,0,0.15); border-radius:6px; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height:fit-content; height:fit-content; box-sizing:border-box; overflow-x:auto;">
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        let style = document.getElementById('report-preview-print-css');
        if (!style) {
            style = document.createElement('style');
            style.id = 'report-preview-print-css';
            style.textContent = `
                #report-preview-modal.print-direct-mode {
                    display: none !important;
                }
                @media print {
                    #report-preview-modal.print-direct-mode {
                        display: block !important;
                    }
                    body > *:not(#report-preview-modal) {
                        display: none !important;
                    }
                    #report-preview-modal {
                        position: absolute !important;
                        left: 0 !important;
                        top: 0 !important;
                        width: 100% !important;
                        height: auto !important;
                        background: #fff !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        display: block !important;
                        overflow: visible !important;
                    }
                    #report-preview-modal .glass-panel {
                        width: 100% !important;
                        max-width: 100% !important;
                        height: auto !important;
                        border: none !important;
                        background: #fff !important;
                        padding: 0 !important;
                        box-shadow: none !important;
                        margin: 0 !important;
                        overflow: visible !important;
                    }
                    @page {
                        size: landscape;
                    }
                    /* Hide Sidebar specifically if inline important was somehow preserved */
                    .global-sidebar { display: none !important; }
                    .btn-link, .btn-action { display: none !important; }
                    
                    #report-preview-modal .glass-panel > div:first-child {
                        display: none !important;
                    }
                    #report-preview-modal .preview-scroll-container {
                        background: #fff !important;
                        padding: 0 !important;
                        overflow: visible !important;
                    }
                    #report-preview-sheet {
                        box-shadow: none !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        border: none !important;
                        width: 100% !important;
                        max-width: 100% !important;
                    }
                    /* Professional A4 Fit Adjustments */
                    #report-preview-sheet table, #report-preview-sheet .dash-table {
                        min-width: 0 !important;
                        width: 100% !important;
                        table-layout: auto !important;
                        border-collapse: collapse !important;
                    }
                    #report-preview-sheet th[style], #report-preview-sheet td[style] {
                        /* Override inline widths that break print layout */
                        width: auto !important;
                    }
                    #report-preview-sheet th, #report-preview-sheet td {
                        padding: 6px 4px !important;
                        font-size: 11px !important;
                        white-space: normal !important;
                        word-break: break-word !important;
                        border: 1px solid #cbd5e1 !important;
                    }
                    #report-preview-sheet th {
                        font-size: 10px !important;
                        background: #f8fafc !important;
                        color: #1e293b !important;
                        padding: 8px 4px !important;
                    }
                    /* Let specific columns compress more based on content */
                    #report-preview-sheet td {
                        max-width: 150px; /* Force word wrap on very long text */
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    const cloned = target.cloneNode(true);
    
    // Remove "إجراءات" column from cloned table specifically for printing
    const ths = cloned.querySelectorAll('th');
    let actionColIndex = -1;
    ths.forEach((th, index) => {
        if(th.textContent.includes('إجراءات')) {
            actionColIndex = index;
            th.remove();
        }
    });
    if(actionColIndex > -1) {
        const trs = cloned.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            const tds = tr.querySelectorAll('td');
            if(tds[actionColIndex]) {
                tds[actionColIndex].remove();
            }
        });
    }
    
    const originalInputs = target.querySelectorAll('input, select');
    const clonedInputs = cloned.querySelectorAll('input, select');
    clonedInputs.forEach((input, index) => {
        const orig = originalInputs[index];
        let valText = '';
        if (orig.tagName === 'SELECT') {
            valText = orig.options[orig.selectedIndex]?.text || '';
        } else {
            valText = orig.value || '';
        }
        const span = document.createElement('span');
        span.textContent = valText;
        span.style.fontWeight = 'bold';
        span.style.color = '#000';
        input.parentNode.replaceChild(span, input);
    });

    cloned.querySelectorAll('button, .btn, .btn-action, .no-print, script').forEach(el => el.remove());

    cloned.querySelectorAll('table').forEach(table => {
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        table.style.marginTop = '15px';
        table.style.direction = 'rtl';
        table.style.fontSize = '0.85rem';
        table.style.color = '#000';
        table.style.background = '#fff';
        
        table.querySelectorAll('th, td').forEach(cell => {
            cell.style.border = '1px solid #94a3b8';
            cell.style.padding = '8px 10px';
            cell.style.verticalAlign = 'middle';
            if (cell.tagName === 'TH') {
                cell.style.background = '#f8fafc';
                cell.style.color = '#0f172a';
                cell.style.fontWeight = '800';
                cell.style.borderBottom = '2px solid #475569';
            }
        });
    });

    const filters = [];
    document.querySelectorAll('.filters-bar input, .filters-bar select, .filter-panel input, .filter-panel select, .f-group input, .f-group select').forEach(input => {
        if (input.closest('#stocktake-modal') || input.closest('#report-preview-modal')) return;
        let labelText = '';
        const parent = input.closest('.f-group') || input.parentElement;
        if (parent) {
            const label = parent.querySelector('label');
            if (label) labelText = label.textContent.trim().replace(':', '');
        }
        if (!labelText && input.placeholder) labelText = input.placeholder;
        
        let valText = '';
        if (input.tagName === 'SELECT') {
            valText = input.options[input.selectedIndex]?.text || '';
        } else {
            valText = input.value || '';
        }
        
        if (valText && valText !== 'ALL' && valText !== 'الكل' && valText !== 'جميع الفئات' && valText !== 'جميع المخازن') {
            filters.push(`<strong>${labelText || 'تصفية'}:</strong> ${valText}`);
        }
    });

    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
    const companyName = cfg.companyName || JSON.parse(localStorage.getItem('user'))?.company_name || 'شركة الأصيل للتجارة والمخازن';
    const printedAt = new Date().toLocaleString('ar-SD-u-nu-latn');
    const userName = JSON.parse(localStorage.getItem('user'))?.fullName || 'المدير العام';

    let headerHtml = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:3px double #0f172a; padding-bottom:15px; margin-bottom:20px; direction:rtl;">
            <div style="text-align:right; width:33%;">
                <h2 style="margin:0; font-size:1.4rem; font-weight:800; color:#0f172a;">${companyName}</h2>
                <div style="font-size:0.75rem; color:#475569; margin-top:4px;">الهاتف: ${cfg.phone || '—'}</div>
                <div style="font-size:0.75rem; color:#475569;">العنوان: ${cfg.address || '—'}</div>
            </div>
            <div style="text-align:center; width:33%;">
                <div style="border: 2px solid #0f172a; padding: 8px 15px; border-radius: 6px; background: #f8fafc; display:inline-block;">
                    <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#0f172a; white-space:nowrap;">${title}</h3>
                </div>
            </div>
            <div style="text-align:left; width:33%; font-size:0.75rem; color:#475569; line-height:1.5;">
                <div>تاريخ الجرد/الطباعة: <strong>${printedAt}</strong></div>
                <div>المستخدم: <strong>${userName}</strong></div>
            </div>
        </div>
    `;

    let filtersHtml = '';
    if (filters.length > 0) {
        filtersHtml = `
            <div style="font-size:0.8rem; margin-bottom:15px; padding:8px 12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px; direction:rtl; text-align:right; color:#334155;">
                🎛️ <strong>الفلاتر المطبقة:</strong> ${filters.join('  |  ')}
            </div>
        `;
    }

    let footerHtml = `
        <div style="margin-top:40px; border-top:1px solid #94a3b8; padding-top:15px; display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b; direction:rtl;">
            <div>طُبع بواسطة نظام MAGAIES للمحاسبة والمستودعات · magaees.cloud</div>
            <div>صفحة 1 من 1</div>
        </div>
    `;

    const sheet = document.getElementById('report-preview-sheet');
    sheet.innerHTML = headerHtml + filtersHtml + `<div style="margin-top:10px;"></div>` + cloned.innerHTML + footerHtml;

    modal.style.display = 'flex';
};

window.closeReportPreview = function() {
    const modal = document.getElementById('report-preview-modal');
    if (modal) modal.style.display = 'none';
};

window.printReportFromPreview = function() {
    window.print();
};

window.printReportDirectly = function(title, targetSelector) {
    window.openReportPreview(title, targetSelector);
    
    const modal = document.getElementById('report-preview-modal');
    if (modal) {
        modal.classList.add('print-direct-mode');
    }
    
    const closeAfterPrint = () => {
        window.closeReportPreview();
        if (modal) {
            modal.classList.remove('print-direct-mode');
        }
        window.removeEventListener('afterprint', closeAfterPrint);
    };
    window.addEventListener('afterprint', closeAfterPrint);
    
    setTimeout(() => {
        window.print();
    }, 150);
};

// ----------------------------------------------------------------------
// 🛠️ CUSTOM NUMBER & CELL STYLING ENGINE (Right-Click Context Menu)
// ----------------------------------------------------------------------
(function() {
    // 1. Dynamic premium fonts loading
    if (!document.getElementById('premium-number-fonts')) {
        const link = document.createElement('link');
        link.id = 'premium-number-fonts';
        link.rel = 'stylesheet';
        link.href = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Roboto+Mono:wght@400;700&family=JetBrains+Mono:wght@400;700&family=Fira+Code:wght@400;700&family=Poppins:wght@400;600;700;800&display=swap';
        document.head.appendChild(link);
    }

    const isNumericElement = (el) => {
        if (!el) return false;
        
        // Check if it's an input or textarea
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
            const val = el.value.trim();
            return /^-?\d*[\d,.\s%]+$/.test(val) || el.type === 'number';
        }
        
        // Check if editable
        if (el.isContentEditable) {
            const val = el.innerText.trim();
            return /^-?\d*[\d,.\s%]+$/.test(val);
        }
        
        // Check ordinary element text
        const text = el.textContent.trim();
        if (!text) return false;
        
        const hasDigit = /\d/.test(text);
        const hasLetters = /[a-zA-Z\u0600-\u06FF]/.test(text.replace(/[\d,.\s%$\-+/*=]/g, ''));
        if (hasDigit && !hasLetters && text.length < 40) {
            return true;
        }
        
        return false;
    };

    const isTargetElement = (el) => {
        if (!el) return false;
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'TD' || el.tagName === 'TH' || el.classList.contains('form-input') || el.classList.contains('ctrl-input')) {
            return true;
        }
        return isNumericElement(el);
    };

    const formatElementNumber = (el, decimals, thousands) => {
        const isInput = el.tagName === 'INPUT' || el.tagName === 'TEXTAREA';
        const valStr = isInput ? el.value : el.textContent;
        
        const clean = valStr.replace(/,/g, '').trim();
        const num = parseFloat(clean);
        if (isNaN(num)) return;
        
        let formatted = num.toFixed(decimals);
        if (thousands) {
            const parts = formatted.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            formatted = parts.join('.');
        }
        
        // Prevent infinite loop: only update if value actually changed
        if (isInput) {
            if (el.value !== formatted) el.value = formatted;
        } else {
            if (el.textContent !== formatted) el.textContent = formatted;
        }
    };

    const NumberStyleManager = {
        savedStyles: {},
        currentElement: null,
        menuEl: null,

        init() {
            try {
                this.savedStyles = JSON.parse(localStorage.getItem('erp_custom_styles_v1')) || {};
            } catch(e) {
                this.savedStyles = {};
            }

            // Custom right-click context menu popup disabled per user request
            // document.addEventListener('contextmenu', (e) => { ... });

            document.addEventListener('click', (e) => {
                if (this.menuEl && !this.menuEl.contains(e.target)) {
                    this.hideMenu();
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.applyAllSaved());
            } else {
                this.applyAllSaved();
            }
            // MutationObserver removed — it was firing applyAllSaved() on every DOM change causing severe lag
        },

        getElementPath(el) {
            if (!el || el.nodeType !== Node.ELEMENT_NODE) return null;
            const parts = [];
            let current = el;
            while (current && current.nodeType === Node.ELEMENT_NODE) {
                let name = current.nodeName.toLowerCase();
                if (current.id) {
                    name += '#' + current.id;
                    parts.unshift(name);
                    break;
                } else {
                    let sib = current;
                    let nth = 1;
                    while (sib = sib.previousElementSibling) {
                        if (sib.nodeName.toLowerCase() === name) nth++;
                    }
                    name += `:nth-of-type(${nth})`;
                }
                parts.unshift(name);
                current = current.parentNode;
            }
            return window.location.pathname + ' > ' + parts.join(' > ');
        },

        showMenu(el, x, y) {
            this.currentElement = el;
            this.createMenuDOM();

            const menuWidth = 320;
            const menuHeight = 440;
            let left = x;
            let top = y;

            if (left + menuWidth > window.innerWidth) {
                left = window.innerWidth - menuWidth - 10;
            }
            if (top + menuHeight > window.innerHeight) {
                top = window.innerHeight - menuHeight - 10;
            }
            if (left < 0) left = 10;
            if (top < 0) top = 10;

            this.menuEl.style.left = left + 'px';
            this.menuEl.style.top = top + 'px';
            this.menuEl.style.display = 'block';

            this.loadCurrentStylesToInputs(el);
        },

        hideMenu() {
            if (this.menuEl) {
                this.menuEl.style.display = 'none';
            }
        },

        createMenuDOM() {
            if (this.menuEl) return;

            const menu = document.createElement('div');
            menu.id = 'custom-number-style-menu';
            menu.style.cssText = `
                position: fixed;
                z-index: 100000;
                width: 320px;
                background: rgba(15, 23, 42, 0.94);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 14px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
                color: #f8fafc;
                font-family: 'Cairo', 'Inter', sans-serif;
                font-size: 13px;
                padding: 18px;
                direction: rtl;
                text-align: right;
                box-sizing: border-box;
                display: none;
                animation: menuFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            `;

            const styleSheet = document.createElement("style");
            styleSheet.innerText = `
                @keyframes menuFadeIn {
                    from { opacity: 0; transform: scale(0.95) translateY(-5px); }
                    to { opacity: 1; transform: scale(1) translateY(0); }
                }
                .style-menu-label {
                    font-size: 0.75rem;
                    color: #94a3b8;
                    margin-bottom: 5px;
                    display: block;
                    font-weight: 600;
                }
                .style-menu-row {
                    margin-bottom: 12px;
                }
                .style-menu-flex {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                .style-menu-input {
                    background: rgba(30, 41, 59, 0.7);
                    border: 1px solid rgba(255,255,255,0.1);
                    color: #fff;
                    border-radius: 6px;
                    height: 32px;
                    padding: 0 8px;
                    font-size: 0.8rem;
                    box-sizing: border-box;
                    width: 100%;
                    outline: none;
                    transition: border 0.2s;
                }
                .style-menu-input:focus {
                    border-color: #38bdf8;
                }
                .style-menu-color-btn {
                    width: 32px;
                    height: 32px;
                    border-radius: 6px;
                    border: 1px solid rgba(255,255,255,0.1);
                    cursor: pointer;
                    padding: 0;
                    overflow: hidden;
                    background: none;
                }
                .style-menu-btn {
                    height: 34px;
                    border-radius: 6px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                    border: none;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 5px;
                    font-size: 0.8rem;
                }
            `;
            document.head.appendChild(styleSheet);

            menu.innerHTML = `
                <div style="font-weight: 800; font-size: 0.95rem; color: #38bdf8; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
                    <span>🛠️ تخصيص وتنسيق الخانة</span>
                    <span id="menu-target-type" style="font-size: 0.7rem; background: rgba(56,189,248,0.15); color: #38bdf8; padding: 2px 6px; border-radius: 4px;">رقم</span>
                </div>

                <!-- Font Family -->
                <div class="style-menu-row">
                    <label class="style-menu-label">خط الأرقام الإنجليزي (أفضل 5 خطوط وضوحاً):</label>
                    <select id="style-font-family" class="style-menu-input">
                        <option value="" style="font-family: inherit;">-- افتراضي الصفحة --</option>
                        <option value="'Inter', sans-serif" style="font-family: 'Inter'; font-weight: bold;">1. Inter (عصري وواضح جداً)</option>
                        <option value="'Roboto Mono', monospace" style="font-family: 'Roboto Mono';">2. Roboto Mono (أرقام متساوية العرض)</option>
                        <option value="'JetBrains Mono', monospace" style="font-family: 'JetBrains Mono';">3. JetBrains Mono (وضوح فائق للمطورين)</option>
                        <option value="'Fira Code', monospace" style="font-family: 'Fira Code';">4. Fira Code (خط أرقام تقني مريح)</option>
                        <option value="'Poppins', sans-serif" style="font-family: 'Poppins';">5. Poppins (خط هندسي واضح وناعم)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <!-- Font Size -->
                    <div class="style-menu-row" style="flex: 1.2;">
                        <label class="style-menu-label">حجم الخط:</label>
                        <div class="style-menu-flex">
                            <input type="range" id="style-font-size-range" min="10" max="32" step="1" style="flex: 1; cursor: pointer; height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; appearance: none; outline: none;">
                            <span id="style-font-size-val" style="font-family: monospace; font-weight: bold; font-size: 0.8rem; width: 32px; text-align: center;">14px</span>
                        </div>
                    </div>

                    <!-- Font Weight (Bold) -->
                    <div class="style-menu-row" style="flex: 0.8; display: flex; flex-direction: column; justify-content: flex-end;">
                        <button type="button" id="style-btn-bold" class="style-menu-btn" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: #fff; width: 100%;">
                            <span><b>B</b> عريض</span>
                        </button>
                    </div>
                </div>

                <!-- Colors -->
                <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                    <div style="flex: 1;">
                        <label class="style-menu-label">لون الرقم:</label>
                        <div class="style-menu-flex">
                            <input type="color" id="style-color-picker" class="style-menu-color-btn" value="#ffffff">
                            <input type="text" id="style-color-hex" class="style-menu-input" placeholder="#FFFFFF" style="font-family: monospace; font-size: 0.75rem;">
                        </div>
                    </div>
                    <div style="flex: 1;">
                        <label class="style-menu-label">خلفية الخانة:</label>
                        <div class="style-menu-flex">
                            <input type="color" id="style-bg-picker" class="style-menu-color-btn" value="#0f172a">
                            <input type="text" id="style-bg-hex" class="style-menu-input" placeholder="#0F172A" style="font-family: monospace; font-size: 0.75rem;">
                        </div>
                    </div>
                </div>

                <!-- Number Formatting Options -->
                <div id="number-formatting-section" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 12px; margin-top: 12px;">
                    <div style="font-weight: bold; color: #10b981; font-size: 0.8rem; margin-bottom: 10px;">🔢 خيارات تنسيق الرقم:</div>
                    
                    <div style="display: flex; gap: 10px;">
                        <!-- Decimals -->
                        <div class="style-menu-row" style="flex: 1.2;">
                            <label class="style-menu-label">المنازل العشرية (الكسور):</label>
                            <select id="style-decimals" class="style-menu-input">
                                <option value="none">-- بدون تقريب --</option>
                                <option value="0">0 (رقم صحيح)</option>
                                <option value="1">منزلة واحدة (0.0)</option>
                                <option value="2">منزلتين (0.00)</option>
                                <option value="3">3 منازل (0.000)</option>
                                <option value="4">4 منازل (0.0000)</option>
                                <option value="5">5 منازل (0.00000)</option>
                                <option value="6">6 منازل (0.000000)</option>
                            </select>
                        </div>

                        <!-- Thousands Separator -->
                        <div class="style-menu-row" style="flex: 0.8; display: flex; flex-direction: column; justify-content: flex-end;">
                            <button type="button" id="style-btn-thousands" class="style-menu-btn" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #34d399; width: 100%;">
                                <span>1,000 فاصلة</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 12px; margin-top: 15px; display: flex; gap: 8px;">
                    <button type="button" id="style-btn-apply" class="style-menu-btn" style="background: #38bdf8; color: #0f172a; flex: 1.5; font-size: 0.85rem;">💾 حفظ وتطبيق</button>
                    <button type="button" id="style-btn-reset" class="style-menu-btn" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; flex: 1;">✖ إزالة</button>
                </div>
            `;

            document.body.appendChild(menu);
            this.menuEl = menu;

            this.bindMenuEvents();
        },

        bindMenuEvents() {
            const sizeRange = document.getElementById('style-font-size-range');
            const sizeVal = document.getElementById('style-font-size-val');
            const btnBold = document.getElementById('style-btn-bold');
            const colorPicker = document.getElementById('style-color-picker');
            const colorHex = document.getElementById('style-color-hex');
            const bgPicker = document.getElementById('style-bg-picker');
            const bgHex = document.getElementById('style-bg-hex');
            const btnThousands = document.getElementById('style-btn-thousands');

            sizeRange.addEventListener('input', () => {
                sizeVal.textContent = sizeRange.value + 'px';
            });

            btnBold.addEventListener('click', () => {
                const isBold = btnBold.classList.toggle('active');
                btnBold.style.background = isBold ? 'rgba(56,189,248,0.2)' : 'rgba(255,255,255,0.06)';
                btnBold.style.borderColor = isBold ? '#38bdf8' : 'rgba(255,255,255,0.1)';
                btnBold.style.color = isBold ? '#38bdf8' : '#fff';
            });

            btnThousands.addEventListener('click', () => {
                const isThousands = btnThousands.classList.toggle('active');
                btnThousands.style.background = isThousands ? 'rgba(16,185,129,0.2)' : 'rgba(255,255,255,0.06)';
                btnThousands.style.borderColor = isThousands ? '#10b981' : 'rgba(255,255,255,0.1)';
                btnThousands.style.color = isThousands ? '#34d399' : '#fff';
            });

            colorPicker.addEventListener('input', () => {
                colorHex.value = colorPicker.value.toUpperCase();
            });
            colorHex.addEventListener('input', () => {
                if (/^#[0-9A-F]{6}$/i.test(colorHex.value)) {
                    colorPicker.value = colorHex.value;
                }
            });

            bgPicker.addEventListener('input', () => {
                bgHex.value = bgPicker.value.toUpperCase();
            });
            bgHex.addEventListener('input', () => {
                if (/^#[0-9A-F]{6}$/i.test(bgHex.value)) {
                    bgPicker.value = bgHex.value;
                }
            });

            document.getElementById('style-btn-apply').addEventListener('click', () => {
                this.applyMenuSelection();
            });

            document.getElementById('style-btn-reset').addEventListener('click', () => {
                this.resetElementStyles();
            });
        },

        loadCurrentStylesToInputs(el) {
            const sizeRange = document.getElementById('style-font-size-range');
            const sizeVal = document.getElementById('style-font-size-val');
            const btnBold = document.getElementById('style-btn-bold');
            const colorPicker = document.getElementById('style-color-picker');
            const colorHex = document.getElementById('style-color-hex');
            const bgPicker = document.getElementById('style-bg-picker');
            const bgHex = document.getElementById('style-bg-hex');
            const fontFamily = document.getElementById('style-font-family');
            const decimals = document.getElementById('style-decimals');
            const btnThousands = document.getElementById('style-btn-thousands');
            const formatSection = document.getElementById('number-formatting-section');
            const targetType = document.getElementById('menu-target-type');

            const computed = window.getComputedStyle(el);
            
            fontFamily.value = el.style.fontFamily || '';
            
            const currentSize = parseInt(el.style.fontSize || computed.fontSize) || 14;
            sizeRange.value = currentSize;
            sizeVal.textContent = currentSize + 'px';

            const isBold = el.style.fontWeight === 'bold' || el.style.fontWeight === '800' || el.style.fontWeight === '900' || computed.fontWeight === '700' || computed.fontWeight === '800' || computed.fontWeight === '900';
            btnBold.classList.toggle('active', isBold);
            btnBold.style.background = isBold ? 'rgba(56,189,248,0.2)' : 'rgba(255,255,255,0.06)';
            btnBold.style.borderColor = isBold ? '#38bdf8' : 'rgba(255,255,255,0.1)';
            btnBold.style.color = isBold ? '#38bdf8' : '#fff';

            const rgbToHex = (rgb) => {
                if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') return '#000000';
                const m = rgb.match(/^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/i);
                if (m) {
                    return "#" + ("0" + parseInt(m[1]).toString(16)).slice(-2) +
                                 ("0" + parseInt(m[2]).toString(16)).slice(-2) +
                                 ("0" + parseInt(m[3]).toString(16)).slice(-2);
                }
                return '#ffffff';
            };

            const currentColor = rgbToHex(el.style.color || computed.color);
            colorPicker.value = currentColor;
            colorHex.value = currentColor.toUpperCase();

            const currentBg = rgbToHex(el.style.backgroundColor || computed.backgroundColor);
            bgPicker.value = currentBg;
            bgHex.value = currentBg.toUpperCase();

            const savedDec = el.dataset.customDecimals || 'none';
            decimals.value = savedDec;

            const isThousands = el.dataset.customThousands !== 'false';
            btnThousands.classList.toggle('active', isThousands);
            btnThousands.style.background = isThousands ? 'rgba(16,185,129,0.2)' : 'rgba(255,255,255,0.06)';
            btnThousands.style.borderColor = isThousands ? '#10b981' : 'rgba(255,255,255,0.1)';
            btnThousands.style.color = isThousands ? '#34d399' : '#fff';

            const isNumeric = isNumericElement(el);
            targetType.textContent = isNumeric ? 'رقم' : 'خانة';
            targetType.style.background = isNumeric ? 'rgba(16,185,129,0.15)' : 'rgba(56,189,248,0.15)';
            targetType.style.color = isNumeric ? '#34d399' : '#38bdf8';
        },

        applyMenuSelection() {
            const el = this.currentElement;
            if (!el) return;

            const sizeRange = document.getElementById('style-font-size-range');
            const btnBold = document.getElementById('style-btn-bold');
            const colorPicker = document.getElementById('style-color-picker');
            const bgPicker = document.getElementById('style-bg-picker');
            const fontFamily = document.getElementById('style-font-family');
            const decimals = document.getElementById('style-decimals');
            const btnThousands = document.getElementById('style-btn-thousands');

            const stylesObj = {
                color: colorPicker.value,
                backgroundColor: bgPicker.value,
                fontSize: sizeRange.value + 'px',
                fontWeight: btnBold.classList.contains('active') ? '800' : 'normal',
                fontFamily: fontFamily.value,
                decimals: decimals.value,
                thousands: btnThousands.classList.contains('active')
            };

            el.style.setProperty('color', stylesObj.color, 'important');
            el.style.setProperty('background-color', stylesObj.backgroundColor, 'important');
            el.style.setProperty('font-size', stylesObj.fontSize, 'important');
            el.style.setProperty('font-weight', stylesObj.fontWeight, 'important');
            if (stylesObj.fontFamily) {
                el.style.setProperty('font-family', stylesObj.fontFamily, 'important');
            } else {
                el.style.removeProperty('font-family');
            }

            if (stylesObj.decimals !== undefined) {
                el.dataset.customDecimals = stylesObj.decimals;
                el.dataset.customThousands = stylesObj.thousands;
                if (stylesObj.decimals !== 'none') {
                    formatElementNumber(el, parseInt(stylesObj.decimals), stylesObj.thousands);
                }
            }

            const path = this.getElementPath(el);
            if (path) {
                this.savedStyles[path] = stylesObj;
                localStorage.setItem('erp_custom_styles_v1', JSON.stringify(this.savedStyles));
            }

            if (typeof showToast === 'function') {
                showToast('تم حفظ وتطبيق التنسيق بنجاح', 'success');
            }
            this.hideMenu();
        },

        resetElementStyles() {
            const el = this.currentElement;
            if (!el) return;

            el.style.removeProperty('color');
            el.style.removeProperty('background-color');
            el.style.removeProperty('font-size');
            el.style.removeProperty('font-weight');
            el.style.removeProperty('font-family');
            delete el.dataset.customDecimals;
            delete el.dataset.customThousands;

            const path = this.getElementPath(el);
            if (path && this.savedStyles[path]) {
                delete this.savedStyles[path];
                localStorage.setItem('erp_custom_styles_v1', JSON.stringify(this.savedStyles));
            }

            if (typeof showToast === 'function') {
                showToast('تمت إعادة تعيين التنسيق الأصلي للخانة', 'info');
            }
            this.hideMenu();
            window.location.reload();
        },

        applyAllSaved() {
            Object.keys(this.savedStyles).forEach(path => {
                if (!path.startsWith(window.location.pathname + ' > ')) return;
                const selector = path.substring((window.location.pathname + ' > ').length);
                try {
                    const el = document.querySelector(selector);
                    if (el) {
                        const styles = this.savedStyles[path];
                        el.style.setProperty('color', styles.color, 'important');
                        el.style.setProperty('background-color', styles.backgroundColor, 'important');
                        el.style.setProperty('font-size', styles.fontSize, 'important');
                        el.style.setProperty('font-weight', styles.fontWeight, 'important');
                        if (styles.fontFamily) {
                            el.style.setProperty('font-family', styles.fontFamily, 'important');
                        }

                        if (styles.decimals !== undefined && styles.decimals !== 'none') {
                            el.dataset.customDecimals = styles.decimals;
                            el.dataset.customThousands = styles.thousands;
                            if (document.activeElement !== el) {
                                formatElementNumber(el, parseInt(styles.decimals), styles.thousands);
                            }
                        }
                    }
                } catch(e) {}
            });
        }
    };

    NumberStyleManager.init();
})();


window.syncHeaderExchangeRate = function() {
    try {
        const exVal = document.getElementById('global-exchange-rate-val');
        const exBox = document.getElementById('global-exchange-rate-box');
        if (!exVal) return;
        
        const token = localStorage.getItem('authToken');
        if (token) {
            fetch('api_index.php/exchange-rate', {
                headers: { 'Authorization': 'Bearer ' + token }
            }).then(r => r.json()).then(data => {
                if (data && data.visible && data.rate) {
                    const saasRate = parseFloat(data.rate);
                    window._cachedGlobalRate = saasRate;
                    localStorage.setItem('erp_cached_global_rate', saasRate.toString());
                    if (exBox) exBox.style.display = 'block';
                    exVal.innerText = saasRate.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                }
            }).catch(() => {});
        } else {
            const cached = localStorage.getItem('erp_cached_global_rate');
            if (cached && parseFloat(cached) > 0) {
                if (exBox) exBox.style.display = 'block';
                exVal.innerText = parseFloat(cached).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
            }
        }
    } catch(e) { console.error('Error syncing header exchange rate:', e); }
};

document.addEventListener('DOMContentLoaded', () => {
    // Fetch immediately on page load (no delay)
    window.syncHeaderExchangeRate();
    // Then refresh every 10 seconds
    setInterval(window.syncHeaderExchangeRate, 10000);
});




window.rebuildGLFromAllTables = function(skipSave = false) {
    let txns = [];
    const timeNow = new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}); // Fallback time

    // Helper: resolve the effective exchange rate for a transaction
    // Priority: 1) systemExchangeRate saved at creation, 2) rate if foreign, 3) current ledgerExchangeRate
    function getEffectiveRate(item, txnRate) {
        const saved = parseNum(item.systemExchangeRate || 0);
        if (saved > 1) return saved;
        if (txnRate && txnRate > 1) return txnRate;
        return (typeof ledgerExchangeRate !== "undefined" && ledgerExchangeRate > 0 ? ledgerExchangeRate : 1);
    }

    // Pre-build O(1) Master Data Lookup Maps for maximum performance
    const custs = []; try { custs.push(...(JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => x && !x.deleted)); } catch(e){}
    const sups = []; try { sups.push(...(JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => x && !x.deleted)); } catch(e){}
    const banks = []; try { banks.push(...(JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => x && !x.deleted)); } catch(e){}
    const safes = []; try { safes.push(...(JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => x && !x.deleted)); } catch(e){}
    const accounts = []; try { accounts.push(...(JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => x && !x.deleted)); } catch(e){}

    const accountMap = new Map();
    const registerAcc = (item, prefix) => {
        if (!item) return;
        const id = String(item.id || '').trim();
        const name = String(item.name || item.bankName || item.safeName || item.accountName || '').trim();
        const code = String(item.code || '').trim();
        let canonicalId = id;
        if (prefix && !id.startsWith(prefix)) canonicalId = prefix + id;
        if (id) {
            accountMap.set(id.toLowerCase(), canonicalId);
            accountMap.set(id.replace(/^(cust|supp|bank|safe|exp)-/, '').toLowerCase(), canonicalId);
        }
        if (name) accountMap.set(name.toLowerCase(), canonicalId);
        if (code) accountMap.set(code.toLowerCase(), canonicalId);
    };

    custs.forEach(c => registerAcc(c, 'cust-'));
    sups.forEach(s => registerAcc(s, 'supp-'));
    banks.forEach(b => registerAcc(b, 'bank-'));
    safes.forEach(sf => registerAcc(sf, 'safe-'));
    accounts.forEach(a => registerAcc(a, 'exp-'));

    function resolveAccount(accVal, fallback = '') {
        if (!accVal) return fallback;
        const str = String(accVal).trim().toLowerCase();
        if (accountMap.has(str)) return accountMap.get(str);
        const cleanStr = str.replace(/^(cust|supp|bank|safe|exp)-/, '');
        if (accountMap.has(cleanStr)) return accountMap.get(cleanStr);
        return accVal;
    }

    // Helper: merge data across tenant-prefixed key and base key for zero data loss
    function loadSourceTable(baseKey) {
        const getTK = window.getTenantKey || ((k) => k);
        const tk = getTK(baseKey);
        let list1 = [];
        let list2 = [];
        try { list1 = JSON.parse(localStorage.getItem(tk) || '[]'); } catch(e){}
        try { list2 = JSON.parse(localStorage.getItem(baseKey) || '[]'); } catch(e){}
        if (!Array.isArray(list1)) list1 = Object.values(list1);
        if (!Array.isArray(list2)) list2 = Object.values(list2);
        
        const map = new Map();
        [...list2, ...list1].forEach(v => {
            if (!v) return;
            const key = String(v.voucherNo || v.id || v.paymentNo || v.receiptNo || v.invoiceNo || Math.random()).trim();
            map.set(key, v);
        });
        return Array.from(map.values()).filter(v => v && !v.deleted);
    }

    // 1. Single Receipts (erp_single_receipts_v1)
    const receipts = loadSourceTable('erp_single_receipts_v1');
    receipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        
        const rate = parseNum(v.rate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');
        const amount = parseNum(v.amount !== undefined ? v.amount : (isBaseSDG ? v.amountLocal : v.amountForeign));
        const amountLocal = parseNum(v.amountLocal || (amount * effRate));
        const valLoc = amountLocal;
        const valFc = isBaseSDG ? (amountLocal / effRate) : amount;
        const toAcct = resolveAccount(v.toAccount || 'safe-001');
        const fromAcct = resolveAccount(v.fromAccount);
        const refNo = v.voucherNo || v.id;

        const hasTransfers = v.bankTransfers && v.bankTransfers.length > 0;
        const hasCheques = v.cheques && v.cheques.length > 0;

        if (hasTransfers && v.paymentMethod !== 'cash') {
            v.bankTransfers.forEach(bt => {
                const amt = parseNum(bt.amount);
                const amtLoc = isBaseSDG ? amt : (amt > valFc * 1.5 ? amt : amt * effRate);
                const amtFc = isBaseSDG ? (amt / effRate) : (amt > valFc * 1.5 ? (amt / effRate) : amt);
                const stmt = bt.statement || v.statement || v.fromStatement || ('تحويل بنكي - سند قبض #' + refNo);

                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'receipt', typeLabel: 'سند قبض',
                    account: toAcct, counterAccount: fromAcct,
                    statement: stmt, additionalNote: stmt,
                    transferNo: bt.transferNo, bankName: bt.note,
                    debit: amtLoc, credit: 0, debitForeign: amtFc, creditForeign: 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'receipt', typeLabel: 'سند صرف',
                    account: fromAcct, counterAccount: toAcct,
                    statement: stmt, additionalNote: stmt,
                    transferNo: bt.transferNo, bankName: bt.note,
                    debit: 0, credit: amtLoc, debitForeign: 0, creditForeign: amtFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            });
        }
        
        if (hasCheques && v.paymentMethod !== 'cash') {
            v.cheques.forEach(ch => {
                const amt = parseNum(ch.amount);
                const amtLoc = isBaseSDG ? amt : (amt > valFc * 1.5 ? amt : amt * effRate);
                const amtFc = isBaseSDG ? (amt / effRate) : (amt > valFc * 1.5 ? (amt / effRate) : amt);
                const chkLabel = ch.checkNo ? ('الشيك ' + ch.checkNo) : 'الشيك';
                const stmt = ch.statement || (chkLabel + ' / قبض نقدية');

                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'receipt', typeLabel: 'سند قبض',
                    account: toAcct, counterAccount: fromAcct,
                    statement: stmt, additionalNote: stmt,
                    checkNo: ch.checkNo, bankName: ch.checkBranch,
                    debit: amtLoc, credit: 0, debitForeign: amtFc, creditForeign: 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'receipt', typeLabel: 'سند صرف',
                    account: fromAcct, counterAccount: toAcct,
                    statement: stmt, additionalNote: stmt,
                    checkNo: ch.checkNo, bankName: ch.checkBranch,
                    debit: 0, credit: amtLoc, debitForeign: 0, creditForeign: amtFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            });
        }
        
        if (!hasTransfers && !hasCheques) {
            // Pure Cash Single Receipt
            const singleTr = v.transferNo || v.mobileTransferNo || "";
            const singleBank = v.bankName || v.mobileNote || "";
            const singleChk = v.checkNo || v.chequeNo || "";
            txns.push({
                date: v.date, time: time, ref: refNo, type: "receipt", typeLabel: "سند قبض",
                account: toAcct, counterAccount: fromAcct,
                statement: v.statement || "سند قبض #" + refNo,
                additionalNote: v.fromStatement || "سند قبض نقدي",
                transferNo: singleTr, bankName: singleBank, checkNo: singleChk,
                debit: valLoc, credit: 0, debitForeign: valFc, creditForeign: 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
            txns.push({
                date: v.date, time: time, ref: refNo, type: "receipt", typeLabel: "سند صرف",
                account: fromAcct, counterAccount: toAcct,
                statement: v.statement || "سند قبض #" + refNo,
                additionalNote: v.fromStatement || "سند قبض نقدي",
                transferNo: singleTr, bankName: singleBank, checkNo: singleChk,
                debit: 0, credit: valLoc, debitForeign: 0, creditForeign: valFc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }
    });

    // 2. Single Payments (erp_single_payments_v1)
    const payments = loadSourceTable('erp_single_payments_v1');
    payments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        
        const rate = parseNum(v.rate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');
        const amount = parseNum(v.amount !== undefined ? v.amount : (isBaseSDG ? v.amountLocal : v.amountForeign));
        const amountLocal = parseNum(v.amountLocal || (amount * effRate));
        const valLoc = amountLocal;
        const valFc = isBaseSDG ? (amountLocal / effRate) : amount;
        const fromAcct = resolveAccount(v.fromAccount || 'safe-001');
        const toAcct = resolveAccount(v.toAccount);
        const refNo = v.voucherNo || v.id;

        const hasTransfers = v.bankTransfers && v.bankTransfers.length > 0;
        const hasCheques = v.cheques && v.cheques.length > 0;

        if (hasTransfers && v.paymentMethod !== 'cash') {
            v.bankTransfers.forEach(bt => {
                const amt = parseNum(bt.amount);
                const amtLoc = isBaseSDG ? amt : (amt > valFc * 1.5 ? amt : amt * effRate);
                const amtFc = isBaseSDG ? (amt / effRate) : (amt > valFc * 1.5 ? (amt / effRate) : amt);
                const stmt = bt.statement || v.statement || v.toStatement || ('تحويل بنكي - سند صرف #' + refNo);

                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'payment', typeLabel: 'سند صرف',
                    account: toAcct, counterAccount: fromAcct,
                    statement: stmt, additionalNote: stmt,
                    transferNo: bt.transferNo, bankName: bt.note,
                    debit: amtLoc, credit: 0, debitForeign: amtFc, creditForeign: 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'payment', typeLabel: 'سند صرف',
                    account: fromAcct, counterAccount: toAcct,
                    statement: stmt, additionalNote: stmt,
                    transferNo: bt.transferNo, bankName: bt.note,
                    debit: 0, credit: amtLoc, debitForeign: 0, creditForeign: amtFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            });
        }
        
        if (hasCheques && v.paymentMethod !== 'cash') {
            v.cheques.forEach(ch => {
                const amt = parseNum(ch.amount);
                const amtLoc = isBaseSDG ? amt : (amt > valFc * 1.5 ? amt : amt * effRate);
                const amtFc = isBaseSDG ? (amt / effRate) : (amt > valFc * 1.5 ? (amt / effRate) : amt);
                const chkLabel = ch.checkNo ? ('الشيك ' + ch.checkNo) : 'الشيك';
                const stmt = ch.statement || (chkLabel + ' / صرف نقدية');

                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'payment', typeLabel: 'سند صرف',
                    account: toAcct, counterAccount: fromAcct,
                    statement: stmt, additionalNote: stmt,
                    checkNo: ch.checkNo, bankName: ch.checkBranch,
                    debit: amtLoc, credit: 0, debitForeign: amtFc, creditForeign: 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'payment', typeLabel: 'سند صرف',
                    account: fromAcct, counterAccount: toAcct,
                    statement: stmt, additionalNote: stmt,
                    checkNo: ch.checkNo, bankName: ch.checkBranch,
                    debit: 0, credit: amtLoc, debitForeign: 0, creditForeign: amtFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            });
        }
        
        if (!hasTransfers && !hasCheques) {
            // Pure Cash Single Payment
            const singleTr = v.transferNo || v.mobileTransferNo || "";
            const singleBank = v.bankName || v.mobileNote || "";
            const singleChk = v.checkNo || v.chequeNo || "";
            txns.push({
                date: v.date, time: time, ref: refNo, type: "payment", typeLabel: "سند صرف",
                account: toAcct, counterAccount: fromAcct,
                statement: v.statement || "سند صرف #" + refNo,
                additionalNote: v.toStatement || "سند صرف نقدي",
                transferNo: singleTr, bankName: singleBank, checkNo: singleChk,
                debit: valLoc, credit: 0, debitForeign: valFc, creditForeign: 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
            txns.push({
                date: v.date, time: time, ref: refNo, type: "payment", typeLabel: "سند صرف",
                account: fromAcct, counterAccount: toAcct,
                statement: v.statement || "سند صرف #" + refNo,
                additionalNote: v.toStatement || "سند صرف نقدي",
                transferNo: singleTr, bankName: singleBank, checkNo: singleChk,
                debit: 0, credit: valLoc, debitForeign: 0, creditForeign: valFc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }
    });

    // 2b. Multi-Receipts (erp_receipts_v1)
    const rawMultiReceipts = loadSourceTable('erp_receipts_v1');
    rawMultiReceipts.forEach(v => {
        if (v.voucherNo && !String(v.voucherNo).startsWith('MV-') && !String(v.voucherNo).startsWith('MV17')) {
            v.id = v.voucherNo;
        } else if (v.id && !String(v.id).startsWith('MV-') && !String(v.id).startsWith('MV17')) {
            v.voucherNo = v.id;
        }
    });
    const multiReceipts = rawMultiReceipts;
    multiReceipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        const mainAcc = resolveAccount(v.toAccount || v.mainAccount || v.depositAccount || 'safe-001', 'safe-001');
        const refNo = (v.voucherNo && !String(v.voucherNo).startsWith('MV-')) ? v.voucherNo : ((v.id && !String(v.id).startsWith('MV-')) ? v.id : (v.voucherNo || v.id));

        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                if (!item) return;
                const pms = (item.paymentMethods && item.paymentMethods.length > 0) ? item.paymentMethods : [item.paymentMethod || 'cash'];
                const itemAcct = resolveAccount(item.accountId || item.account || item.accountName, 'unknown');
                const itemTotal = parseNum(item.amount);

                let cashAmt = parseNum(item.cashAmount);
                let bankAmt = parseNum(item.bankAmount);
                let chequeAmt = parseNum(item.chequeAmount);

                if (itemTotal > 0 && cashAmt <= 0 && bankAmt <= 0 && chequeAmt <= 0) {
                    if (pms.includes('bank')) bankAmt = itemTotal;
                    else if (pms.includes('cheque')) chequeAmt = itemTotal;
                    else cashAmt = itemTotal;
                }

                // Cash Portion
                if ((pms.includes('cash') || cashAmt > 0) && cashAmt > 0) {
                    const amtLoc = isBaseSDG ? cashAmt : cashAmt * effRate;
                    const amtFc = isBaseSDG ? (cashAmt / effRate) : cashAmt;
                    const safeAcct = mainAcc;
                    const stmt = item.cashSafe ? `خزينة: ${item.cashSafe} - ${item.cashStatement || item.statement || v.description || 'قبض نقدية'}` : (item.cashStatement || item.statement || v.description || 'قبض نقدية');

                    // Safe/Main Account (Debit)
                    txns.push({
                        date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                        account: safeAcct, counterAccount: itemAcct,
                        statement: v.description || stmt,
                        additionalNote: v.description || stmt,
                        debit: amtLoc, credit: 0, 
                        debitForeign: amtFc, creditForeign: 0,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    
                    // Recipient/Payer Account (Credit)
                    txns.push({
                        date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                        account: itemAcct, counterAccount: safeAcct,
                        statement: stmt,
                        additionalNote: stmt,
                        debit: 0, credit: amtLoc, 
                        debitForeign: 0, creditForeign: amtFc,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                }

                // Bank Transfer Portion
                if ((pms.includes('bank') || bankAmt > 0) && bankAmt > 0) {
                    const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: bankAmt, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                    transfers.forEach(bt => {
                        const amt = parseNum(bt.amount || bankAmt);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * effRate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const bankAcct = mainAcc;
                        const baseStmt = bt.statement || item.statement || v.description || 'قبض تحويل بنكي';
                        const stmt = bt.bankName ? `بنك: ${bt.bankName} - ${baseStmt}` : baseStmt;

                        // Bank/Main Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: bankAcct, counterAccount: itemAcct,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            transferNo: bt.transferNo, bankName: bt.bankName || bt.note,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Recipient/Payer Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: itemAcct, counterAccount: bankAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            transferNo: bt.transferNo, bankName: bt.bankName || bt.note,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }

                // Cheque Portion
                if ((pms.includes('cheque') || chequeAmt > 0) && chequeAmt > 0) {
                    const cheques = (item.cheques && item.cheques.length > 0) ? item.cheques : [{ amount: chequeAmt, chequeBank: item.chequeBank, chequeNo: item.chequeNo || item.checkNo, chequeDate: item.chequeDate || item.checkDate, statement: item.chequeStatement || '' }];
                    cheques.forEach(ch => {
                        const amt = parseNum(ch.amount || chequeAmt);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * effRate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const chequeAcct = mainAcc;
                        const baseStmt = ch.statement || item.statement || v.description || 'قبض شيك';
                        const stmt = ch.chequeBank ? `شيك بنك: ${ch.chequeBank} - ${baseStmt}` : baseStmt;

                        // Cheque/Main Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: chequeAcct, counterAccount: itemAcct,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            checkNo: ch.chequeNo || ch.checkNo, bankName: ch.chequeBank || ch.checkBank,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Recipient/Payer Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: itemAcct, counterAccount: chequeAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            checkNo: ch.chequeNo || ch.checkNo, bankName: ch.chequeBank || ch.checkBank,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }
            });
        } else if (v.details && v.details.length > 0) {
            // Single Receipt (from cash-receipt.php) which uses bulk format v.details
            v.details.forEach(det => {
                if (!det || (!det.accountId && !det.account)) return;
                const detAmtLocal = parseNum(det.amountLocal || det.amount);
                const detAmtForeign = parseNum(det.amountForeign);
                if (detAmtLocal <= 0 && detAmtForeign <= 0) return;
                
                const detAcct = resolveAccount(det.accountId || det.account);
                const detRate = parseNum(det.rate || effRate);
                const stmt = det.note || det.statement || v.statement || v.description || 'قبض نقدية';

                // Debit Safe/Main Account
                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'receipt', typeLabel: 'سند قبض نقدية',
                    account: mainAcc, counterAccount: detAcct,
                    statement: stmt, additionalNote: stmt,
                    debit: detAmtLocal, credit: 0,
                    debitForeign: detAmtForeign, creditForeign: 0,
                    costCenter: det.costCenter || costCenter, currency, exchangeRate: detRate, isPosted
                });
                
                // Credit Payer/Recipient Account
                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'receipt', typeLabel: 'سند قبض نقدية',
                    account: detAcct, counterAccount: mainAcc,
                    statement: stmt, additionalNote: stmt,
                    debit: 0, credit: detAmtLocal,
                    debitForeign: 0, creditForeign: detAmtForeign,
                    costCenter: det.costCenter || costCenter, currency, exchangeRate: detRate, isPosted
                });
            });
        } else {
            // Fallback for old/empty items vouchers
            const totalAmount = parseNum(v.totalAmount);
            const valLoc = isBaseSDG ? totalAmount : totalAmount * effRate;
            const valFc = isBaseSDG ? (totalAmount / effRate) : totalAmount;
            txns.push({
                date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                account: mainAcc, counterAccount: 'حسابات فرعية متعددة',
                statement: v.description || 'قبض مجمع',
                additionalNote: v.description || 'سند قبض مجمع',
                debit: valLoc, credit: 0, 
                debitForeign: valFc, creditForeign: 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
            txns.push({
                date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                account: 'حسابات فرعية متعددة', counterAccount: mainAcc,
                statement: v.description || 'قبض مجمع',
                additionalNote: v.description || 'سند قبض مجمع',
                debit: 0, credit: valLoc, 
                debitForeign: 0, creditForeign: valFc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }
    });

    // 2b2. Comprehensive Receipts (erp_comprehensive_receipts_v1)
    const compReceipts = (JSON.parse(localStorage.getItem('erp_comprehensive_receipts_v1')) || []).filter(v => v && !v.deleted);
    compReceipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        
        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');
        const amt = parseNum(v.amount);
        const valLoc = isBaseSDG ? amt : amt * effRate;
        const valFc = isBaseSDG ? (amt / effRate) : amt;
        
        let safeAcct = v.depositAccount || 'safe-001';
        if (v.paymentType === 'check') safeAcct = v.checkBank || 'cheques-receivable';
        else if (v.paymentType === 'bank') safeAcct = v.bankSourceName || safeAcct;
        else if (v.paymentType === 'mobile') safeAcct = v.mobileWallet || safeAcct;
        
        const stmt = v.description || v.depositDesc || 'سند قبض شامل';

        // Safe/Bank Account (Debit)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'comprehensive-receipt', typeLabel: 'سند قبض شامل',
            account: safeAcct, counterAccount: v.accountId,
            statement: stmt, additionalNote: stmt,
            debit: valLoc, credit: 0, 
            debitForeign: valFc, creditForeign: 0,
            costCenter, currency, exchangeRate: effRate, isPosted
        });
        
        // Payer Account (Credit)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'comprehensive-receipt', typeLabel: 'سند قبض شامل',
            account: v.accountId, counterAccount: safeAcct,
            statement: stmt, additionalNote: stmt,
            debit: 0, credit: valLoc, 
            debitForeign: 0, creditForeign: valFc,
            costCenter, currency, exchangeRate: effRate, isPosted
        });
    });

    // 2c. Multi-Payments (erp_payments_v1)
    const rawMultiPayments = loadSourceTable('erp_payments_v1');
    rawMultiPayments.forEach(v => {
        if (v.voucherNo && !String(v.voucherNo).startsWith('MV-') && !String(v.voucherNo).startsWith('MV17')) {
            v.id = v.voucherNo;
        } else if (v.id && !String(v.id).startsWith('MV-') && !String(v.id).startsWith('MV17')) {
            v.voucherNo = v.id;
        }
    });
    const multiPayments = rawMultiPayments;
    multiPayments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        const mainAcc = resolveAccount(v.fromAccount || v.mainAccount || v.withdrawalAccount || 'safe-001', 'safe-001');
        const refNo = (v.voucherNo && !String(v.voucherNo).startsWith('MV-')) ? v.voucherNo : ((v.id && !String(v.id).startsWith('MV-')) ? v.id : (v.voucherNo || v.id));

        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                if (!item) return;
                const pms = (item.paymentMethods && item.paymentMethods.length > 0) ? item.paymentMethods : [item.paymentMethod || 'cash'];
                const itemAcct = resolveAccount(item.accountId || item.account || item.accountName, 'unknown');
                const itemTotal = parseNum(item.amount);

                let cashAmt = parseNum(item.cashAmount);
                let bankAmt = parseNum(item.bankAmount);
                let chequeAmt = parseNum(item.chequeAmount);

                if (itemTotal > 0 && cashAmt <= 0 && bankAmt <= 0 && chequeAmt <= 0) {
                    if (pms.includes('bank')) bankAmt = itemTotal;
                    else if (pms.includes('cheque')) chequeAmt = itemTotal;
                    else cashAmt = itemTotal;
                }
                
                // Cash Portion
                if ((pms.includes('cash') || cashAmt > 0) && cashAmt > 0) {
                    const amtLoc = isBaseSDG ? cashAmt : cashAmt * effRate;
                    const amtFc = isBaseSDG ? (cashAmt / effRate) : cashAmt;
                    const safeAcct = mainAcc;
                    const stmt = item.cashSafe ? `خزينة: ${item.cashSafe} - ${item.cashStatement || item.statement || v.description || 'صرف نقدية'}` : (item.cashStatement || item.statement || v.description || 'صرف نقدية');

                    // Recipient Account (Debit)
                    txns.push({
                        date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                        account: itemAcct, counterAccount: safeAcct,
                        statement: stmt,
                        additionalNote: stmt,
                        debit: amtLoc, credit: 0, 
                        debitForeign: amtFc, creditForeign: 0,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    
                    // Safe/Main Account (Credit)
                    txns.push({
                        date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                        account: safeAcct, counterAccount: itemAcct,
                        statement: v.description || stmt,
                        additionalNote: v.description || stmt,
                        debit: 0, credit: amtLoc, 
                        debitForeign: 0, creditForeign: amtFc,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                }

                // Bank Transfer Portion
                if ((pms.includes('bank') || bankAmt > 0) && bankAmt > 0) {
                    const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: bankAmt, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                    transfers.forEach(bt => {
                        const amt = parseNum(bt.amount || bankAmt);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * effRate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const bankAcct = mainAcc;
                        const baseStmt = bt.statement || item.statement || v.description || 'صرف تحويل بنكي';
                        const stmt = bt.bankName ? `بنك: ${bt.bankName} - ${baseStmt}` : baseStmt;

                        // Recipient Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: itemAcct, counterAccount: bankAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            transferNo: bt.transferNo, bankName: bt.bankName || bt.note,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Bank/Main Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: bankAcct, counterAccount: itemAcct,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            transferNo: bt.transferNo, bankName: bt.bankName || bt.note,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }

                // Cheque Portion
                if ((pms.includes('cheque') || chequeAmt > 0) && chequeAmt > 0) {
                    const cheques = (item.cheques && item.cheques.length > 0) ? item.cheques : [{ amount: chequeAmt, chequeBank: item.chequeBank, chequeNo: item.chequeNo || item.checkNo, chequeDate: item.chequeDate || item.checkDate, statement: item.chequeStatement || '' }];
                    cheques.forEach(ch => {
                        const amt = parseNum(ch.amount || chequeAmt);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * effRate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const chequeAcct = mainAcc;
                        const baseStmt = ch.statement || item.statement || v.description || 'صرف شيك';
                        const stmt = ch.chequeBank ? `شيك بنك: ${ch.chequeBank} - ${baseStmt}` : baseStmt;

                        // Recipient Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: itemAcct, counterAccount: chequeAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            checkNo: ch.chequeNo || ch.checkNo, bankName: ch.chequeBank || ch.checkBank,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Cheque/Main Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: chequeAcct, counterAccount: itemAcct,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            checkNo: ch.chequeNo || ch.checkNo, bankName: ch.chequeBank || ch.checkBank,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }
            });
        } else if (v.details && v.details.length > 0) {
            // Single Payment (from cash-payment.php) which uses bulk format v.details
            v.details.forEach(det => {
                if (!det || (!det.accountId && !det.account)) return;
                const detAmtLocal = parseNum(det.amountLocal || det.amount);
                const detAmtForeign = parseNum(det.amountForeign);
                if (detAmtLocal <= 0 && detAmtForeign <= 0) return;
                
                const detAcct = resolveAccount(det.accountId || det.account);
                const detRate = parseNum(det.rate || effRate);
                const stmt = det.note || det.statement || v.statement || v.description || 'صرف نقدية';

                // Debit Recipient Account
                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'payment', typeLabel: 'سند صرف نقدية',
                    account: detAcct, counterAccount: mainAcc,
                    statement: stmt, additionalNote: stmt,
                    debit: detAmtLocal, credit: 0,
                    debitForeign: detAmtForeign, creditForeign: 0,
                    costCenter: det.costCenter || costCenter, currency, exchangeRate: detRate, isPosted
                });
                
                // Credit Safe/Main Account
                txns.push({
                    date: v.date, time: time, ref: refNo, type: 'payment', typeLabel: 'سند صرف نقدية',
                    account: mainAcc, counterAccount: detAcct,
                    statement: stmt, additionalNote: stmt,
                    debit: 0, credit: detAmtLocal,
                    debitForeign: 0, creditForeign: detAmtForeign,
                    costCenter: det.costCenter || costCenter, currency, exchangeRate: detRate, isPosted
                });
            });
        } else {
            // Fallback for old/empty items vouchers
            const totalAmount = parseNum(v.totalAmount);
            const valLoc = isBaseSDG ? totalAmount : totalAmount * effRate;
            const valFc = isBaseSDG ? (totalAmount / effRate) : totalAmount;
            txns.push({
                date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                account: mainAcc, counterAccount: 'حسابات فرعية متعددة',
                statement: v.description || 'صرف مجمع',
                additionalNote: v.description || 'سند صرف مجمع',
                debit: 0, credit: valLoc, 
                debitForeign: 0, creditForeign: valFc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
            txns.push({
                date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                account: 'حسابات فرعية متعددة', counterAccount: mainAcc,
                statement: v.description || 'صرف مجمع',
                additionalNote: v.description || 'سند صرف مجمع',
                debit: valLoc, credit: 0, 
                debitForeign: valFc, creditForeign: 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }
    });
    // 2c2. Comprehensive Payments (erp_comprehensive_payments_v1)
    const compPayments = loadSourceTable('erp_comprehensive_payments_v1');
    compPayments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        
        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');
        const amt = parseNum(v.amount);
        const valLoc = isBaseSDG ? amt : amt * effRate;
        const valFc = isBaseSDG ? (amt / effRate) : amt;
        
        let safeAcct = v.withdrawalAccount || 'safe-001';
        if (v.paymentType === 'check') safeAcct = v.checkBank || 'banks-cheques';
        else if (v.paymentType === 'bank') safeAcct = v.bankSourceName || safeAcct;
        else if (v.paymentType === 'mobile') safeAcct = v.mobileWallet || safeAcct;
        
        const stmt = v.description || v.withdrawalDesc || 'سند صرف شامل';

        // Safe/Bank Account (Credit)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'comprehensive-payment', typeLabel: 'سند صرف شامل',
            account: safeAcct, counterAccount: v.accountId,
            statement: stmt, additionalNote: stmt,
            debit: 0, credit: valLoc, 
            debitForeign: 0, creditForeign: valFc,
            costCenter, currency, exchangeRate: effRate, isPosted
        });
        
        // Payee Account (Debit)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'comprehensive-payment', typeLabel: 'سند صرف شامل',
            account: v.accountId, counterAccount: safeAcct,
            statement: stmt, additionalNote: stmt,
            debit: valLoc, credit: 0, 
            debitForeign: valFc, creditForeign: 0,
            costCenter, currency, exchangeRate: effRate, isPosted
        });
    });

    // 3. Journal Vouchers
    const jvs = loadSourceTable('erp_journal_vouchers_v1');
    jvs.forEach(v => {
        if (!v.lines) return;
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        v.lines.forEach(line => {
            const costCenter = line.costCenter || v.costCenter || 'بدون مركز';
            const currency = line.currency || v.currency || 'SDG';

            const rate = parseNum(line.rate || 1);
            const effRate = getEffectiveRate(v, rate);
            const isBaseSDG = (currency === 'SDG');
            const deb = parseNum(line.debit);
            const cred = parseNum(line.credit);
            const debLoc = isBaseSDG ? deb : deb * effRate;
            const debFc = isBaseSDG ? (deb / effRate) : deb;
            const credLoc = isBaseSDG ? cred : cred * effRate;
            const credFc = isBaseSDG ? (cred / effRate) : cred;

            txns.push({
                date: v.date, time: time, ref: v.id, type: 'jv', typeLabel: 'قيد يومي',
                account: line.account, counterAccount: '-',
                statement: 'قيد يومي #' + (v.voucherNo || v.id || 'بدون_رقم'),
                additionalNote: v.statement || line.note || 'قيد يومي',
                debit: debLoc, credit: credLoc, 
                debitForeign: debFc, creditForeign: credFc,
                costCenter, currency,
                exchangeRate: effRate,
                isPosted: isPosted
            });
        });
    });

    
    // 4. Sales Invoices
    const sales = loadSourceTable('erp_sales_invoices_v1').filter(x => !x.isGDN);
    sales.forEach(inv => {
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        let subtotal = 0;
        
        if (inv.items && inv.items.length > 0) {
            subtotal = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
        } else {
            subtotal = total - parseNum(inv.laborCost || 0) - parseNum(inv.transportCost || 0) - parseNum(inv.additions || 0) - parseNum(inv.taxAmt || inv.vatAmount || 0) + parseNum(inv.discountAmt || inv.discountAmount || 0);
        }

        const tax = parseNum(inv.taxAmt || inv.vatAmount || 0);
        const discount = parseNum(inv.discountAmt || inv.discountAmount || 0);
        const additions = parseNum(inv.additions || 0) + parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0);

        if (total === 0 && subtotal > 0) {
            total = subtotal + additions + tax - discount;
        }

        if (!total) return;
        const costCenter = inv.costCenter || 'بدون مركز';
        const currency = inv.currency || 'SDG';
        const time = inv.time || timeNow;
        const refNo = inv.invoiceNo || inv.id;
        const customerAcct = inv.customerId || inv.customer;
        const isPosted = inv.isPosted !== false;

        const rate = parseNum(inv.exchangeRate || 1);
        const effRate = getEffectiveRate(inv, rate);
        const isBaseSDG = (currency === 'SDG');

        const isReturn = (inv.type === 'return' || inv.isReturn === true);

        const calcAmt = (amt) => {
            const loc = isBaseSDG ? amt : amt * effRate;
            const fc = isBaseSDG ? (amt / effRate) : amt;
            return { loc, fc };
        };

        const totalAmt = calcAmt(total);
        const subAmt = calcAmt(subtotal);
        const taxAmt = calcAmt(tax);
        const discAmt = calcAmt(discount);
        const addAmt = calcAmt(additions);

        // 1. Customer Account
        txns.push({
            date: inv.date, time: time, ref: refNo, type: isReturn ? 'sales-return' : 'invoice', typeLabel: isReturn ? 'مرتجع مبيعات' : 'مبيعات',
            account: customerAcct, counterAccount: 'revenue-sales',
            statement: (isReturn ? 'مرتجع مبيعات #' : 'فاتورة مبيعات #') + refNo,
            additionalNote: (isReturn ? `مردودات مبيعات — ` : `مبيعات آجلة — `) + (inv.customerName || inv.customer || ''),
            debit: isReturn ? 0 : totalAmt.loc, credit: isReturn ? totalAmt.loc : 0, 
            debitForeign: isReturn ? 0 : totalAmt.fc, creditForeign: isReturn ? totalAmt.fc : 0,
            costCenter, currency, exchangeRate: effRate, isPosted
        });

        // 2. Sales Revenue
        if (subtotal > 0) {
            txns.push({
                date: inv.date, time: time, ref: refNo, type: isReturn ? 'sales-return' : 'invoice', typeLabel: isReturn ? 'مرتجع مبيعات' : 'مبيعات',
                account: 'revenue-sales', counterAccount: customerAcct,
                statement: (isReturn ? 'مرتجع مبيعات #' : 'فاتورة مبيعات #') + refNo,
                additionalNote: (isReturn ? `خصم إيرادات — ` : `إيرادات — `) + (inv.customerName || inv.customer || ''),
                debit: isReturn ? subAmt.loc : 0, credit: isReturn ? 0 : subAmt.loc, 
                debitForeign: isReturn ? subAmt.fc : 0, creditForeign: isReturn ? 0 : subAmt.fc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }

        // 3. Tax Payable
        if (tax > 0) {
            txns.push({
                date: inv.date, time: time, ref: refNo, type: isReturn ? 'sales-return' : 'invoice', typeLabel: isReturn ? 'مرتجع مبيعات' : 'مبيعات',
                account: 'liab-tax-payable', counterAccount: customerAcct,
                statement: 'ضريبة القيمة المضافة #' + refNo,
                additionalNote: (isReturn ? `مردود ضريبة — ` : `ضريبة مبيعات — `) + (inv.customerName || inv.customer || ''),
                debit: isReturn ? taxAmt.loc : 0, credit: isReturn ? 0 : taxAmt.loc, 
                debitForeign: isReturn ? taxAmt.fc : 0, creditForeign: isReturn ? 0 : taxAmt.fc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }

        // 4. Additions
        if (additions > 0) {
            txns.push({
                date: inv.date, time: time, ref: refNo, type: isReturn ? 'sales-return' : 'invoice', typeLabel: isReturn ? 'مرتجع مبيعات' : 'مبيعات',
                account: 'revenue-additions', counterAccount: customerAcct,
                statement: 'إضافات ونقل مبيعات #' + refNo,
                additionalNote: 'إضافات — ' + (inv.customerName || inv.customer || ''),
                debit: isReturn ? addAmt.loc : 0, credit: isReturn ? 0 : addAmt.loc, 
                debitForeign: isReturn ? addAmt.fc : 0, creditForeign: isReturn ? 0 : addAmt.fc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }

        // 5. Discount
        if (discount > 0) {
            txns.push({
                date: inv.date, time: time, ref: refNo, type: isReturn ? 'sales-return' : 'invoice', typeLabel: isReturn ? 'مرتجع مبيعات' : 'مبيعات',
                account: 'exp-sales-discounts', counterAccount: customerAcct,
                statement: 'خصم مسموح به #' + refNo,
                additionalNote: 'خصم لعميل — ' + (inv.customerName || inv.customer || ''),
                debit: isReturn ? 0 : discAmt.loc, credit: isReturn ? discAmt.loc : 0, 
                debitForeign: isReturn ? 0 : discAmt.fc, creditForeign: isReturn ? discAmt.fc : 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }

        if (inv.paymentMethod === 'hybrid' || inv.paymentMethod === 'multipay') {
            // A. Cash Safe Portion
            const cash = parseNum(inv.cashAmount || 0);
            if (cash > 0) {
                const cashLoc = isBaseSDG ? cash : cash * effRate;
                const cashFc = isBaseSDG ? (cash / effRate) : cash;
                const paymentAcct = inv.paymentAccountId || 'safe-001';
                const debCash = isReturn ? 0 : cashLoc;
                const credCash = isReturn ? cashLoc : 0;
                const debCashFc = isReturn ? 0 : cashFc;
                const credCashFc = isReturn ? cashFc : 0;
                
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PAID', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    account: paymentAcct, counterAccount: customerAcct,
                    statement: isReturn ? 'سداد نقدي لمرتجع' : 'استلام نقدية (نقداً)',
                    additionalNote: isReturn ? 'صرف نقدي للمرتجع' : 'سداد نقدي مع الفاتورة',
                    debit: debCash, credit: credCash, 
                    debitForeign: debCashFc, creditForeign: credCashFc,
                    costCenter, currency,
                    exchangeRate: effRate,
                    isPosted: isPosted
                });
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PAID', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    account: customerAcct, counterAccount: paymentAcct,
                    statement: isReturn ? 'سداد نقدي لمرتجع' : 'استلام نقدية (نقداً)',
                    additionalNote: isReturn ? 'صرف نقدي للمرتجع' : 'سداد نقدي مع الفاتورة',
                    debit: credCash, credit: debCash, 
                    debitForeign: credCashFc, creditForeign: debCashFc,
                    costCenter, currency,
                    exchangeRate: effRate,
                    isPosted: isPosted
                });
            }
            // B. Bank Transfer Portions
            if (inv.bankTransfers && inv.bankTransfers.length > 0) {
                inv.bankTransfers.forEach((bt, idx) => {
                    const amt = parseNum(bt.amount || 0);
                    if (amt > 0) {
                        const amtLocBt = isBaseSDG ? amt : amt * effRate;
                        const amtFcBt = isBaseSDG ? (amt / effRate) : amt;
                        const bankAcct = bt.bankAccountId || 'bank-001';
                        
                        const debBt = isReturn ? 0 : amtLocBt;
                        const credBt = isReturn ? amtLocBt : 0;
                        const debBtFc = isReturn ? 0 : amtFcBt;
                        const credBtFc = isReturn ? amtFcBt : 0;
                        
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-BT${idx+1}`, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            account: bankAcct, counterAccount: customerAcct,
                            statement: `تحويل بنكي مرجع #${bt.reference || ''}`,
                            additionalNote: isReturn ? 'إرجاع تحويل بنكي' : 'سداد تحويل بنكي مع الفاتورة',
                            transferNo: bt.reference || bt.transferNo || '', bankName: bt.bankName || 'البنك',
                            debit: debBt, credit: credBt, 
                            debitForeign: debBtFc, creditForeign: credBtFc,
                            costCenter, currency,
                            exchangeRate: effRate,
                            isPosted: isPosted
                        });
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-BT${idx+1}`, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            account: customerAcct, counterAccount: bankAcct,
                            statement: `تحويل بنكي مرجع #${bt.reference || ''}`,
                            additionalNote: isReturn ? 'إرجاع تحويل بنكي' : 'سداد تحويل بنكي مع الفاتورة',
                            transferNo: bt.reference || bt.transferNo || '', bankName: bt.bankName || 'البنك',
                            debit: credBt, credit: debBt, 
                            debitForeign: credBtFc, creditForeign: debBtFc,
                            costCenter, currency,
                            exchangeRate: effRate,
                            isPosted: isPosted
                        });
                    }
                });
            }
            // C. Cheque Portions
            if (inv.cheques && inv.cheques.length > 0) {
                inv.cheques.forEach((ch, idx) => {
                    const amt = parseNum(ch.amount || 0);
                    if (amt > 0) {
                        const amtLocCh = isBaseSDG ? amt : amt * effRate;
                        const amtFcCh = isBaseSDG ? (amt / effRate) : amt;
                        const chequeAcct = 'cheques-receivable';
                        
                        const debCh = isReturn ? 0 : amtLocCh;
                        const credCh = isReturn ? amtLocCh : 0;
                        const debChFc = isReturn ? 0 : amtFcCh;
                        const credChFc = isReturn ? amtFcCh : 0;
                        
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-CH${idx+1}`, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            account: chequeAcct, counterAccount: customerAcct,
                            statement: `شيك رقم #${ch.chequeNo || ''} استحقاق ${ch.dueDate || ''}`,
                            additionalNote: isReturn ? 'إرجاع شيك' : 'سداد شيك مع الفاتورة',
                            debit: debCh, credit: credCh, 
                            debitForeign: debChFc, creditForeign: credChFc,
                            costCenter, currency,
                            exchangeRate: effRate,
                            isPosted: isPosted
                        });
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-CH${idx+1}`, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            account: customerAcct, counterAccount: chequeAcct,
                            statement: `شيك رقم #${ch.chequeNo || ''} استحقاق ${ch.dueDate || ''}`,
                            additionalNote: isReturn ? 'إرجاع شيك' : 'سداد شيك مع الفاتورة',
                            debit: credCh, credit: debCh, 
                            debitForeign: credChFc, creditForeign: debChFc,
                            costCenter, currency,
                            exchangeRate: effRate,
                            isPosted: isPosted
                        });
                    }
                });
            }
        } else 
        if (inv.paymentMethod !== 'credit') {
            const pms = inv.paymentMethods || [inv.paymentMethod];
            let cashAmt = parseFloat(inv.cashAmount) || 0;
            let bankAmt = parseFloat(inv.bankTransfers ? inv.bankTransfers.reduce((s, b) => s + (parseFloat(b.amount) || 0), 0) : inv.bankAmount) || 0;
            let chequeAmt = parseFloat(inv.cheques ? inv.cheques.reduce((s, c) => s + (parseFloat(c.amount) || 0), 0) : inv.chequeAmount) || 0;
            
            const totalForFallback = typeof totalAmt !== 'undefined' ? totalAmt.fc : (typeof amtFc !== 'undefined' ? amtFc : 0);
            
            if (totalForFallback > 0 && cashAmt <= 0 && bankAmt <= 0 && chequeAmt <= 0) {
                if (pms.includes('bank')) bankAmt = totalForFallback;
                else if (pms.includes('cheque')) chequeAmt = totalForFallback;
                else cashAmt = totalForFallback;
            }

            // Cash
            if (cashAmt > 0) {
                const cLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? cashAmt : cashAmt * effRate;
                const cFc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? (cashAmt / effRate) : cashAmt;
                const paymentAcct = inv.paymentAccountId || 'safe-001';
                
                let safeName = 'الخزينة';
                try {
                    const allSafes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
                    const sObj = allSafes.find(s => String(s.id) === String(paymentAcct));
                    if (sObj) safeName = sObj.name;
                } catch(e) {}
                
                const stmt = isReturn ? 'سداد مرتجع نقدي' : 'استلام نقدية';
                const addNote = isReturn ? `صرف مبالغ المرتجع من [${safeName}]` : `سداد نقدي مبيعات (${refNo})`;

                txns.push({
                    date: inv.date, time: time, ref: refNo + '-SAL-PAID-C', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    account: paymentAcct, counterAccount: customerAcct,
                    statement: stmt, additionalNote: addNote,
                    transferNo: safeName,
                    debit: isReturn ? 0 : cLoc, credit: isReturn ? cLoc : 0,
                    debitForeign: isReturn ? 0 : cFc, creditForeign: isReturn ? cFc : 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-SAL-PAID-C', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    account: customerAcct, counterAccount: paymentAcct,
                    statement: stmt, additionalNote: addNote,
                    transferNo: safeName,
                    debit: isReturn ? cLoc : 0, credit: isReturn ? 0 : cLoc,
                    debitForeign: isReturn ? cFc : 0, creditForeign: isReturn ? 0 : cFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            }

            // Bank
            if (bankAmt > 0 && inv.bankTransfers && inv.bankTransfers.length > 0) {
                let allBanks = [];
                try { allBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]'); } catch(e) {}
                inv.bankTransfers.forEach((bt, idx) => {
                    const btFc = parseFloat(bt.amount) || 0;
                    if (btFc <= 0) return;
                    const btLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? btFc : btFc * effRate;
                    
                    let bankName = 'البنك';
                    const bObj = allBanks.find(b => String(b.id) === String(bt.bankAccountId));
                    if (bObj) bankName = bObj.name;
                    
                    const stmt = isReturn ? 'سداد مرتجع تحويل بنكي' : 'استلام تحويل بنكي';
                    const addNote = `سداد تحويلة مبيعات (${refNo})`;
                    const trNum = bt.reference || bt.transferNo || '';

                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-SAL-PAID-B' + idx, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        account: bt.bankAccountId || 'bank-001', counterAccount: customerAcct,
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: isReturn ? 0 : btLoc, credit: isReturn ? btLoc : 0,
                        debitForeign: isReturn ? 0 : btFc, creditForeign: isReturn ? btFc : 0,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-SAL-PAID-B' + idx, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        account: customerAcct, counterAccount: bt.bankAccountId || 'bank-001',
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: isReturn ? btLoc : 0, credit: isReturn ? 0 : btLoc,
                        debitForeign: isReturn ? btFc : 0, creditForeign: isReturn ? 0 : btFc,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                });
            }

            // Cheque
            if (chequeAmt > 0 && inv.cheques && inv.cheques.length > 0) {
                let allBanks = [];
                try { allBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]'); } catch(e) {}
                inv.cheques.forEach((ch, idx) => {
                    const chFc = parseFloat(ch.amount) || 0;
                    if (chFc <= 0) return;
                    const chLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? chFc : chFc * effRate;
                    
                    let bankName = ch.bankName || 'البنك';
                    if (!ch.bankName || ch.bankName === 'بنك غير محدد') {
                        const bObj = allBanks.find(b => String(b.id) === String(ch.bankAccountId));
                        if (bObj) bankName = bObj.name;
                    }
                    
                    const stmt = isReturn ? 'سداد مرتجع شيك' : 'استلام شيك';
                    const addNote = `سداد شيك مبيعات (${refNo})`;
                    const trNum = `شيك (${ch.chequeNo || ''})`;

                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-SAL-PAID-Q' + idx, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        account: ch.bankAccountId || 'cheques-receivable', counterAccount: customerAcct,
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: isReturn ? 0 : chLoc, credit: isReturn ? chLoc : 0,
                        debitForeign: isReturn ? 0 : chFc, creditForeign: isReturn ? chFc : 0,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-SAL-PAID-Q' + idx, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        account: customerAcct, counterAccount: ch.bankAccountId || 'cheques-receivable',
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: isReturn ? chLoc : 0, credit: isReturn ? 0 : chLoc,
                        debitForeign: isReturn ? chFc : 0, creditForeign: isReturn ? 0 : chFc,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                });
            }
        }
    });

    
    // 5. Purchase Invoices
    const purchases = loadSourceTable('erp_purchase_invoices_v1').filter(x => !x.isGRN);
    purchases.forEach(inv => {
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        let subtotal = 0;

        if (inv.items && inv.items.length > 0) {
            subtotal = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
        } else {
            subtotal = total - parseNum(inv.laborCost || 0) - parseNum(inv.transportCost || 0) - parseNum(inv.additions || 0) - parseNum(inv.taxAmt || inv.vatAmount || 0) + parseNum(inv.discountAmt || inv.discountAmount || 0);
        }

        const tax = parseNum(inv.taxAmt || inv.vatAmount || 0);
        const discount = parseNum(inv.discountAmt || inv.discountAmount || 0);
        const additions = parseNum(inv.additions || 0) + parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0);

        if (total === 0 && subtotal > 0) {
            total = subtotal + additions + tax - discount;
        }

        if (!total) return;
        const costCenter = inv.costCenter || 'بدون مركز';
        const currency = inv.currency || 'SDG';
        const time = inv.time || timeNow;
        const refNo = inv.invoiceNo || inv.id;
        const supplierAcct = resolveAccount(inv.supplierId || inv.supplier || inv.customer || 'supp-001');
        const isPosted = inv.isPosted !== false;

        const rate = parseNum(inv.exchangeRate || 1);
        const effRate = getEffectiveRate(inv, rate);
        const isBaseSDG = (currency === 'SDG');

        const isReturn = (inv.type === 'return' || inv.isReturn === true);

        const calcAmt = (amt) => {
            const loc = isBaseSDG ? amt : amt * effRate;
            const fc = isBaseSDG ? (amt / effRate) : amt;
            return { loc, fc };
        };

        const totalAmt = calcAmt(total);
        const subAmt = calcAmt(subtotal);
        const taxAmt = calcAmt(tax);
        const discAmt = calcAmt(discount);
        const addAmt = calcAmt(additions);

        if (inv.isAdjustment) {
            const absAmtLoc = Math.abs(totalAmt.loc);
            const absAmtFc = Math.abs(totalAmt.fc);
            const isSurplus = (totalAmt.loc >= 0);

            txns.push({
                date: inv.date, time: time, ref: refNo, type: 'adjustment', typeLabel: 'تسوية مخزنية',
                account: isSurplus ? 'act-inventory' : 'exp-inventory-adjustments',
                counterAccount: isSurplus ? 'exp-inventory-adjustments' : 'act-inventory',
                statement: 'تسوية مخزنية للفروقات #' + refNo,
                additionalNote: inv.notes || 'تسوية مخزنية للفروقات',
                debit: absAmtLoc, credit: 0, 
                debitForeign: absAmtFc, creditForeign: 0,
                costCenter, currency,
                exchangeRate: effRate,
                isPosted: isPosted
            });
            txns.push({
                date: inv.date, time: time, ref: refNo, type: 'adjustment', typeLabel: 'تسوية مخزنية',
                account: isSurplus ? 'exp-inventory-adjustments' : 'act-inventory',
                counterAccount: isSurplus ? 'act-inventory' : 'exp-inventory-adjustments',
                statement: 'تسوية مخزنية للفروقات #' + refNo,
                additionalNote: inv.notes || 'تسوية مخزنية للفروقات',
                debit: 0, credit: absAmtLoc, 
                debitForeign: 0, creditForeign: absAmtFc,
                costCenter, currency,
                exchangeRate: effRate,
                isPosted: isPosted
            });
            return;
        }

        // 1. Supplier Account
        txns.push({
            date: inv.date, time: time, ref: refNo, type: isReturn ? 'purchase-return' : 'purchase', typeLabel: isReturn ? 'مرتجع مشتريات' : 'مشتريات',
            account: supplierAcct, counterAccount: 'exp-purchases',
            statement: (isReturn ? 'مرتجع مشتريات #' : 'فاتورة مشتريات #') + refNo,
            additionalNote: (isReturn ? `مسترد مورد (مرتجع مشتريات ${refNo})` : `مستحقات مورد (مشتريات ${refNo})`),
            debit: isReturn ? totalAmt.loc : 0, credit: isReturn ? 0 : totalAmt.loc, 
            debitForeign: isReturn ? totalAmt.fc : 0, creditForeign: isReturn ? 0 : totalAmt.fc,
            costCenter, currency, exchangeRate: effRate, isPosted
        });

        // 2. Purchases Expense / Inventory
        if (subtotal > 0) {
            txns.push({
                date: inv.date, time: time, ref: refNo, type: isReturn ? 'purchase-return' : 'purchase', typeLabel: isReturn ? 'مرتجع مشتريات' : 'مشتريات',
                account: 'exp-purchases', counterAccount: supplierAcct,
                statement: (isReturn ? 'مرتجع مشتريات #' : 'فاتورة مشتريات #') + refNo,
                additionalNote: (isReturn ? `تخفيض مشتريات — ` : `مشتريات آجلة — `) + (inv.supplierName || inv.supplier || ''),
                debit: isReturn ? 0 : subAmt.loc, credit: isReturn ? subAmt.loc : 0, 
                debitForeign: isReturn ? 0 : subAmt.fc, creditForeign: isReturn ? subAmt.fc : 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }

        // 3. Tax Receivable
        if (tax > 0) {
            txns.push({
                date: inv.date, time: time, ref: refNo, type: isReturn ? 'purchase-return' : 'purchase', typeLabel: isReturn ? 'مرتجع مشتريات' : 'مشتريات',
                account: 'act-tax-receivable', counterAccount: supplierAcct,
                statement: 'ضريبة القيمة المضافة المدفوعة #' + refNo,
                additionalNote: (isReturn ? `استرداد ضريبة — ` : `ضريبة مشتريات — `) + (inv.supplierName || inv.supplier || ''),
                debit: isReturn ? 0 : taxAmt.loc, credit: isReturn ? taxAmt.loc : 0, 
                debitForeign: isReturn ? 0 : taxAmt.fc, creditForeign: isReturn ? taxAmt.fc : 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }

        // 4. Additions
        if (additions > 0) {
            txns.push({
                date: inv.date, time: time, ref: refNo, type: isReturn ? 'purchase-return' : 'purchase', typeLabel: isReturn ? 'مرتجع مشتريات' : 'مشتريات',
                account: 'exp-additions', counterAccount: supplierAcct,
                statement: 'مصاريف نقل وإضافات #' + refNo,
                additionalNote: 'إضافات مشتريات — ' + (inv.supplierName || inv.supplier || ''),
                debit: isReturn ? 0 : addAmt.loc, credit: isReturn ? addAmt.loc : 0, 
                debitForeign: isReturn ? 0 : addAmt.fc, creditForeign: isReturn ? addAmt.fc : 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }

        // 5. Discount
        if (discount > 0) {
            txns.push({
                date: inv.date, time: time, ref: refNo, type: isReturn ? 'purchase-return' : 'purchase', typeLabel: isReturn ? 'مرتجع مشتريات' : 'مشتريات',
                account: 'rev-purchase-discounts', counterAccount: supplierAcct,
                statement: 'خصم مكتسب #' + refNo,
                additionalNote: 'خصم من مورد — ' + (inv.supplierName || inv.supplier || ''),
                debit: isReturn ? discAmt.loc : 0, credit: isReturn ? 0 : discAmt.loc, 
                debitForeign: isReturn ? discAmt.fc : 0, creditForeign: isReturn ? 0 : discAmt.fc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }

        
        if (inv.paymentMethod !== 'credit') {
            const pms = inv.paymentMethods || [inv.paymentMethod];
            let cashAmt = parseFloat(inv.cashAmount) || 0;
            let bankAmt = parseFloat(inv.bankTransfers ? inv.bankTransfers.reduce((s, b) => s + (parseFloat(b.amount) || 0), 0) : inv.bankAmount) || 0;
            let chequeAmt = parseFloat(inv.cheques ? inv.cheques.reduce((s, c) => s + (parseFloat(c.amount) || 0), 0) : inv.chequeAmount) || 0;

            const totalForFallback = typeof totalAmt !== 'undefined' ? totalAmt.fc : (typeof amtFc !== 'undefined' ? amtFc : 0);
            
            if (totalForFallback > 0 && cashAmt <= 0 && bankAmt <= 0 && chequeAmt <= 0) {
                if (pms.includes('bank')) bankAmt = totalForFallback;
                else if (pms.includes('cheque')) chequeAmt = totalForFallback;
                else cashAmt = totalForFallback;
            }

            // Cash Portion
            if (cashAmt > 0) {
                const cLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? cashAmt : cashAmt * effRate;
                const cFc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? (cashAmt / effRate) : cashAmt;
                const paymentAcct = inv.paymentAccountId || 'safe-001';
                
                let safeName = 'الخزينة';
                try {
                    const allSafes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
                    const sObj = allSafes.find(s => String(s.id) === String(paymentAcct));
                    if (sObj) safeName = sObj.name;
                } catch(e) {}
                
                const stmt = (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد نقدية' : 'صرف نقدية';
                const addNote = (typeof isReturn !== 'undefined' && isReturn) ? `استرداد نقدي مع مرتجع الفاتورة من [${safeName}]` : `سداد نقدي مشتريات (${refNo})`;

                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PUR-PAID-C', type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                    account: paymentAcct, counterAccount: supplierAcct,
                    statement: stmt, additionalNote: addNote,
                    transferNo: safeName,
                    debit: (typeof isReturn !== 'undefined' && isReturn) ? cLoc : 0, credit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : cLoc,
                    debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? cFc : 0, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : cFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PUR-PAID-C', type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                    account: supplierAcct, counterAccount: paymentAcct,
                    statement: stmt, additionalNote: addNote,
                    transferNo: safeName,
                    debit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : cLoc, credit: (typeof isReturn !== 'undefined' && isReturn) ? cLoc : 0,
                    debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : cFc, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? cFc : 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            }

            // Bank Transfers
            if (bankAmt > 0 && inv.bankTransfers && inv.bankTransfers.length > 0) {
                let allBanks = [];
                try { allBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]'); } catch(e) {}
                inv.bankTransfers.forEach((bt, idx) => {
                    const btFc = parseFloat(bt.amount) || 0;
                    if (btFc <= 0) return;
                    const btLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? btFc : btFc * effRate;
                    
                    let bankName = 'البنك';
                    const bObj = allBanks.find(b => String(b.id) === String(bt.bankAccountId));
                    if (bObj) bankName = bObj.name;
                    
                    const stmt = (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد تحويل بنكي' : 'صرف تحويل بنكي';
                    const addNote = `سداد تحويلة مشتريات (${refNo})`;
                    const trNum = bt.reference || bt.transferNo || '';

                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-PUR-PAID-B' + idx, type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                        account: bt.bankAccountId || 'bank-001', counterAccount: supplierAcct,
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: (typeof isReturn !== 'undefined' && isReturn) ? btLoc : 0, credit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : btLoc,
                        debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? btFc : 0, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : btFc,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-PUR-PAID-B' + idx, type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                        account: supplierAcct, counterAccount: bt.bankAccountId || 'bank-001',
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : btLoc, credit: (typeof isReturn !== 'undefined' && isReturn) ? btLoc : 0,
                        debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : btFc, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? btFc : 0,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                });
            }

            // Cheques
            if (chequeAmt > 0 && inv.cheques && inv.cheques.length > 0) {
                let allBanks = [];
                try { allBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]'); } catch(e) {}
                inv.cheques.forEach((ch, idx) => {
                    const chFc = parseFloat(ch.amount) || 0;
                    if (chFc <= 0) return;
                    const chLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? chFc : chFc * effRate;
                    
                    let bankName = ch.bankName || 'البنك';
                    if (!ch.bankName || ch.bankName === 'بنك غير محدد') {
                        const bObj = allBanks.find(b => String(b.id) === String(ch.bankAccountId));
                        if (bObj) bankName = bObj.name;
                    }
                    
                    const stmt = (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد شيك' : 'صرف شيك';
                    const addNote = `سداد شيك مشتريات (${refNo})`;
                    const trNum = `شيك (${ch.chequeNo || ''})`;

                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-PUR-PAID-Q' + idx, type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                        account: ch.bankAccountId || 'cheques-payable', counterAccount: supplierAcct,
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: (typeof isReturn !== 'undefined' && isReturn) ? chLoc : 0, credit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : chLoc,
                        debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? chFc : 0, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : chFc,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-PUR-PAID-Q' + idx, type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                        account: supplierAcct, counterAccount: ch.bankAccountId || 'cheques-payable',
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : chLoc, credit: (typeof isReturn !== 'undefined' && isReturn) ? chLoc : 0,
                        debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : chFc, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? chFc : 0,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                });
            }
        }
    });

    // 6. Map source data for checkNo and transferNo extraction
    const bankTransfersData = JSON.parse(localStorage.getItem('erp_bank_transfers_v1')) || [];
    const checksData = JSON.parse(localStorage.getItem('erp_checks_v1')) || [];
    const allData = [
        ...receipts, ...payments, ...multiReceipts, ...multiPayments, ...compReceipts, ...jvs, ...sales, ...purchases, ...bankTransfersData, ...checksData
    ];
    const dataMap = new Map();
    allData.forEach(d => {
        if(d.id) dataMap.set(String(d.id), d);
        if(d.invoiceNo) dataMap.set(String(d.invoiceNo), d);
        if(d.voucherNo) dataMap.set(String(d.voucherNo), d);
        if(d.refNo) dataMap.set(String(d.refNo), d);
    });

    txns.forEach(t => {
        let baseRef = String(t.ref).replace(/-PAID$/, '').replace(/-BT\d+$/, '').replace(/-CH\d+$/, '');
        let source = dataMap.get(baseRef);
        if (source) {
            t._source = source;
            const isBaseSDG = (t.currency === 'SDG');
            const amt = t.debit || t.credit || 0;
            const matchAmt = (val) => {
                let v = parseFloat(val) || 0;
                return Math.abs(v - amt) < 0.1 || Math.abs((isBaseSDG ? v : v * (t.exchangeRate || 1)) - amt) < 0.1;
            };

            // Process cheques
            if (!t.checkNo && !t.transferNo) {
                if (source.cheques && source.cheques.length > 0) {
                    let ch = source.cheques.find(c => matchAmt(c.amount));
                    if (ch) t.checkNo = (ch.checkNo || ch.chequeNo || ch.check_number || '') + (ch.checkBranch || ch.chequeBank ? ` (${ch.checkBranch || ch.chequeBank})` : '');
                } else if (!source.bankTransfers || source.bankTransfers.length === 0) {
                    if (source.checkNo || source.checkNumber || source.check_number || source.chequeNo) {
                        let cNo = source.checkNo || source.checkNumber || source.check_number || source.chequeNo;
                        let cBank = source.checkBranch || source.chequeBank || source.bankName || '';
                        t.checkNo = cBank ? `${cNo} (${cBank})` : cNo;
                    }
                }
            }

            // Process bank transfers
            if (!t.transferNo && !t.checkNo) {
                if (source.bankTransfers && source.bankTransfers.length > 0) {
                    let bt = source.bankTransfers.find(b => matchAmt(b.amount));
                    if (bt) {
                        t.transferNo = (bt.transferNo || bt.transfer_no || bt.referenceNo || '');
                        t.bankName = (bt.bankName || bt.note || '');
                    }
                } else if (!source.cheques || source.cheques.length === 0) {
                    if (source.transferNo || source.transfer_no || source.transferNumber || source.mobileTransferNo) {
                        let tr = source.transferNo || source.transfer_no || source.transferNumber || source.mobileTransferNo;
                        let note = source.mobileNote || source.bankName || source.note || '';
                        t.transferNo = tr;
                        t.bankName = note;
                    }
                }
            }

            // Process multi-items
            if (source.items && source.items.length > 0) {
                let item = source.items.find(it => it.account === t.account || it.account === t.counterAccount);
                if (item) {
                    if (!t.checkNo && !t.transferNo) {
                        if (item.cheques && item.cheques.length > 0) {
                            let ch = item.cheques.find(c => matchAmt(c.amount));
                            if (ch) t.checkNo = (ch.chequeNo || ch.checkNo || ch.check_number || '') + (ch.chequeBank || ch.checkBranch ? ` (${ch.chequeBank || ch.checkBranch})` : '');
                        } else if (!item.bankTransfers || item.bankTransfers.length === 0) {
                            if (item.checkNo || item.chequeNo || item.check_number) {
                                t.checkNo = (item.checkNo || item.chequeNo || item.check_number) + (item.chequeBank || item.checkBranch ? ` (${item.chequeBank || item.checkBranch})` : '');
                            }
                        }
                    }

                    if (!t.transferNo && !t.checkNo) {
                        if (item.bankTransfers && item.bankTransfers.length > 0) {
                            let bt = item.bankTransfers.find(b => matchAmt(b.amount));
                            if (bt) {
                                t.transferNo = (bt.transferNo || bt.transfer_no || '');
                                t.bankName = (bt.bankName || bt.note || '');
                            }
                        } else if (!item.cheques || item.cheques.length === 0) {
                            if (item.transferNo || item.transfer_no) {
                                t.transferNo = (item.transferNo || item.transfer_no);
                                t.bankName = (item.bankName || item.note || '');
                            }
                        }
                    }
                }
            }
        }
    });

    // Recalibrate true exchange rate if both debit/credit exist in local and foreign
    txns.forEach(t => {
        const d = parseFloat(t.debit) || 0;
        const c = parseFloat(t.credit) || 0;
        const df = parseFloat(t.debitForeign || t.dr_foreign) || 0;
        const cf = parseFloat(t.creditForeign || t.cr_foreign) || 0;
        
        const loc = d > 0 ? d : c;
        const forgn = df > 0 ? df : cf;
        
        if (loc > 0 && forgn > 0) {
            t.exchangeRate = loc / forgn;
        }
    });

    
    // Map txns to gl_entries structure if needed, or keep as is.
    // general-ledger expects txns format. We will save it directly to erp_gl_v1
    if (!skipSave) {
        // Use original localStorage to prevent infinite loops in app.js proxy
        const glStr = JSON.stringify(txns);
        const tenantKey = window.getTenantKey ? window.getTenantKey('erp_gl_v1') : 'erp_gl_v1';
        
        // Update DB_STORE directly
        if (window.DB_STORE) window.DB_STORE[tenantKey] = glStr;
        
        // Save using original SetItem to avoid interceptor loop
        if (typeof Storage !== 'undefined' && Storage.prototype.setItem) {
            const originalSetItem = Storage.prototype.setItem;
            if (originalSetItem.name === "setItem") {
                // Not overridden yet
                localStorage.setItem(tenantKey, glStr);
            } else {
                // Need to use native
                Object.getPrototypeOf(localStorage).setItem.call(localStorage, tenantKey, glStr);
            }
        } else {
            localStorage.setItem(tenantKey, glStr);
        }
        
        // Push to localforage
        if (typeof localforage !== 'undefined') {
            localforage.setItem(tenantKey, glStr);
        }
        
        // Mark erp_gl_v1 as dirty for sync push
        if (window.dirtyKeys) window.dirtyKeys.add('erp_gl_v1');
    }
    return txns;
    
}

window.animateValue = function(element, end, duration = 600, decimals = null, formatterFn = null) {
    if (!element) return;
    
    let startStr = element.tagName === 'INPUT' ? element.value : element.innerText;
    let cleanStr = (startStr || '0').toString().replace(/,/g, '').replace(/[^\d.-]/g, '');
    let start = parseFloat(cleanStr);
    if (isNaN(start)) start = 0;
    
    if (start === end) return;
    
    if (element._animId) {
        cancelAnimationFrame(element._animId);
    }
    
    const targetDecimals = decimals !== null ? decimals : (end % 1 !== 0 ? 2 : 0);
    
    const format = formatterFn || ((n, d) => {
        if (typeof window.fmtNum === 'function') return window.fmtNum(n);
        return Number(n).toLocaleString('en-US', {minimumFractionDigits: d, maximumFractionDigits: d});
    });

    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        
        const easeProgress = progress * (2 - progress);
        let current = start + easeProgress * (end - start);
        
        current = Number(current.toFixed(targetDecimals));
        
        if (element.tagName === 'INPUT') {
            element.value = format(current, targetDecimals);
        } else {
            element.innerHTML = format(current, targetDecimals);
        }
        
        if (progress < 1) {
            element._animId = window.requestAnimationFrame(step);
        } else {
            if (element.tagName === 'INPUT') {
                element.value = format(end, targetDecimals);
            } else {
                element.innerHTML = format(end, targetDecimals);
            }
            element._animId = null;
        }
    };
    element._animId = window.requestAnimationFrame(step);
};
