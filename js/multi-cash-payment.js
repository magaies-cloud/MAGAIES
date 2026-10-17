window.getAllMultiPayments = function() {
    let localInvs = [];
    try { localInvs = JSON.parse(localStorage.getItem('erp_multi_payments_v1')) || []; } catch(e){}
    let serverInvs = window.SERVER_MULTI_PAYMENTS || [];
    let map = new Map();
    serverInvs.forEach(inv => map.set(inv.id, inv));
    localInvs.forEach(inv => map.set(inv.id, inv));
    return Array.from(map.values());
};
// js/multi-cash-payment.js

let currentVoucherIndex = -1;
let currentVoucherList = [];
let blockCounter = 0;

// ══════════════════════════════════════════════════════════════════════
// 1. ENTERPRISE CRYPTOGRAPHIC IDEMPOTENCY & DOUBLE-CLICK GUARD
// ══════════════════════════════════════════════════════════════════════
window.IdempotencyGuard = {
    currentKey: null,
    isSaving: false,

    init() {
        this.regenerate();
    },

    regenerate() {
        let randStr = '';
        try {
            if (typeof crypto !== 'undefined' && crypto.randomUUID) {
                randStr = crypto.randomUUID();
            }
        } catch(e) {}
        if (!randStr) {
            randStr = 'IDEM-' + Date.now() + '-' + Math.random().toString(36).substring(2, 10);
        }
        this.currentKey = randStr;
        return this.currentKey;
    },

    getKey() {
        if (!this.currentKey) this.regenerate();
        return this.currentKey;
    }
};
IdempotencyGuard.init();

// ══════════════════════════════════════════════════════════════════════
// 2. REAL-TIME CONCURRENCY LOCK & ACTIVE PRESENCE MANAGER
// ══════════════════════════════════════════════════════════════════════
window.ConcurrencyLockManager = {
    tabSessionId: 'TAB-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9),
    heartbeatInterval: null,
    currentVoucherNo: null,
    isLockedByOther: false,

    getUserName() {
        try {
            const u = JSON.parse(localStorage.getItem('user') || '{}');
            return u.fullName || u.username || 'محاسب النظام';
        } catch(e) {
            return 'محاسب النظام';
        }
    },

    getUserId() {
        try {
            const u = JSON.parse(localStorage.getItem('user') || '{}');
            return u.id || '';
        } catch(e) {
            return '';
        }
    },

    async checkAndAcquireLock(voucherNo, voucherType = 'payment') {
        const cleanNo = String(voucherNo || '').trim();
        if (!cleanNo) {
            this.releaseLock();
            return;
        }
        this.currentVoucherNo = cleanNo;

        try {
            const fd = new FormData();
            fd.append('action', 'acquire_voucher_lock');
            fd.append('voucher_no', cleanNo);
            fd.append('voucher_type', voucherType);
            fd.append('user_name', this.getUserName());
            fd.append('user_id', this.getUserId());
            fd.append('session_id', this.tabSessionId);

            const resp = await fetch('multi-cash-payment.php', { method: 'POST', body: fd });
            const data = await resp.json();

            if (data && data.is_locked) {
                // Another accountant owns this lock!
                this.isLockedByOther = true;
                this.showWarningBadge(data.locked_by, data.elapsed_sec);
                this.stopHeartbeat();
            } else {
                // Lock successfully acquired or renewed for this session
                this.isLockedByOther = false;
                this.hideWarningBadge();
                this.startHeartbeat();
            }
        } catch(e) {
            console.warn('Concurrency lock check network warning:', e);
        }
    },

    startHeartbeat() {
        this.stopHeartbeat();
        this.heartbeatInterval = setInterval(() => {
            if (!this.currentVoucherNo || this.isLockedByOther) return;
            const fd = new FormData();
            fd.append('action', 'heartbeat_voucher_lock');
            fd.append('voucher_no', this.currentVoucherNo);
            fd.append('session_id', this.tabSessionId);
            fetch('multi-cash-payment.php', { method: 'POST', body: fd }).catch(() => {});
        }, 25000);
    },

    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    },

    async releaseLock(voucherNo = null) {
        const target = voucherNo || this.currentVoucherNo;
        this.stopHeartbeat();
        this.hideWarningBadge();
        this.isLockedByOther = false;
        this.currentVoucherNo = null;

        if (!target) return;
        try {
            const fd = new FormData();
            fd.append('action', 'release_voucher_lock');
            fd.append('voucher_no', target);
            fd.append('session_id', this.tabSessionId);
            if (navigator.sendBeacon) {
                navigator.sendBeacon('multi-cash-payment.php', fd);
            } else {
                fetch('multi-cash-payment.php', { method: 'POST', body: fd, keepalive: true }).catch(() => {});
            }
        } catch(e) {}
    },

    async takeoverLock() {
        if (!this.currentVoucherNo) return;
        if (!confirm(`هل ترغب بالتأكيد في استلام تحرير السند رقم (${this.currentVoucherNo}) ونقل القفل لجلستك الحالية؟`)) {
            return;
        }
        try {
            const typeInput = document.getElementById('voucher-type');
            const fd = new FormData();
            fd.append('action', 'takeover_voucher_lock');
            fd.append('voucher_no', this.currentVoucherNo);
            fd.append('voucher_type', typeInput ? typeInput.value : 'payment');
            fd.append('user_name', this.getUserName());
            fd.append('user_id', this.getUserId());
            fd.append('session_id', this.tabSessionId);

            const resp = await fetch('multi-cash-payment.php', { method: 'POST', body: fd });
            const data = await resp.json();
            if (data && data.success) {
                this.isLockedByOther = false;
                this.hideWarningBadge();
                this.startHeartbeat();
                if (typeof showToast === 'function') {
                    showToast('✨ تم استلام تحرير السند بنجاح وبدء قفل جلستك الحالية 🔒', 'success');
                }
            }
        } catch(e) {
            if (typeof showToast === 'function') showToast('تعذر استلام التحرير!', 'error');
        }
    },

    showWarningBadge(lockedBy, elapsedSec) {
        const badge = document.getElementById('concurrency-warning-badge');
        const userSpan = document.getElementById('concurrency-lock-username');
        const timeSpan = document.getElementById('concurrency-lock-time');
        if (!badge) return;

        if (userSpan) userSpan.textContent = lockedBy || 'محاسب آخر';
        let timeText = '(منذ قليل)';
        if (elapsedSec && elapsedSec >= 60) {
            const mins = Math.floor(elapsedSec / 60);
            timeText = `(منذ ${mins} دقيقة)`;
        } else if (elapsedSec) {
            timeText = `(منذ ${elapsedSec} ثانية)`;
        }
        if (timeSpan) timeSpan.textContent = timeText;

        badge.style.display = 'block';
    },

    hideWarningBadge() {
        const badge = document.getElementById('concurrency-warning-badge');
        if (badge) badge.style.display = 'none';
    },

    dismissBadge() {
        this.hideWarningBadge();
    }
};

// Auto-release presence lock on tab unload
window.addEventListener('beforeunload', () => {
    if (window.ConcurrencyLockManager && window.ConcurrencyLockManager.currentVoucherNo) {
        window.ConcurrencyLockManager.releaseLock();
    }
});

window.getStoredVouchers = function(storageKey) {
    const getTenantKey = window.getTenantKey || ((k) => k);
    const tKey = getTenantKey(storageKey);
    let list1 = [];
    let list2 = [];
    try { list1 = JSON.parse(localStorage.getItem(tKey) || '[]'); } catch(e) {}
    try { list2 = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch(e) {}
    if (!Array.isArray(list1)) list1 = Object.values(list1);
    if (!Array.isArray(list2)) list2 = Object.values(list2);
    
    const map = new Map();
    [...list2, ...list1].forEach(v => {
        if (!v) return;
        const key = String(v.voucherNo || v.id || '').trim();
        if (key) map.set(key, v);
    });
    return Array.from(map.values());
};

window.saveStoredVouchers = function(storageKey, dataArray) {
    if (!Array.isArray(dataArray)) return;
    const getTenantKey = window.getTenantKey || ((k) => k);
    const tKey = getTenantKey(storageKey);
    const jsonStr = JSON.stringify(dataArray);
    
    try { localStorage.setItem(tKey, jsonStr); } catch(e) {}
    if (tKey !== storageKey) {
        try { localStorage.setItem(storageKey, jsonStr); } catch(e) {}
    }
    
    if (typeof localforage !== 'undefined') {
        localforage.setItem(tKey, dataArray).catch(e => console.error(e));
        if (tKey !== storageKey) {
            localforage.setItem(storageKey, dataArray).catch(e => console.error(e));
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('multi-voucher-user-audit-container', {}, true);
    }
    // Set today's date
    const __d = new Date();
    const __y = __d.getFullYear();
    const __m = String(__d.getMonth() + 1).padStart(2, '0');
    const __day = String(__d.getDate()).padStart(2, '0');
    const today = `${__y}-${__m}-${__day}`;
    const dateEl = document.getElementById('voucher-date');
    if (dateEl) {
        dateEl.value = today;
        dateEl.addEventListener('change', () => {
            if (currentVoucherIndex === -1) {
                loadNextMultiVoucherNumber();
            }
        });
    }

    // Live date display in header
    const dateMini = document.getElementById('header-date-mini');
    if (dateMini) {
        setInterval(() => {
            const now = new Date();
            dateMini.innerText = now.toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }, 1000);
    }

    // If app is already ready
    if (window.APP_READY) {
        loadAllAccounts();
    }
    
    // Merge server data for offline navigation
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const mergeData = (serverData, storageKey) => {
            if (serverData && serverData.length > 0) {
                const tKey = getTenantKey(storageKey);
                let localData = [];
                if (typeof localStorage !== 'undefined') {
                    localData = JSON.parse(localStorage.getItem(tKey) || '[]');
                }
                const serverMap = new Map();
                serverData.forEach(v => {
                    // Only keep grouped vouchers for this screen to avoid polluting memory if needed, but actually we merge all to match single-cash logic
                    if (v.id) serverMap.set(v.id, v);
                });
                localData.forEach(v => {
                    // Filter out old formats that were already fixed on the server
                    const vid = String(v.id);
                    if (vid && (vid.startsWith('MVP-') || vid.startsWith('TEMP_') || vid.startsWith('C-'))) {
                        return; // discard
                    }
                    if (v.id && !serverMap.has(v.id)) {
                        serverMap.set(v.id, v);
                    }
                });
                const merged = Array.from(serverMap.values());
                localStorage.setItem(tKey, JSON.stringify(merged));
                // Also update localforage if used
                if (typeof localforage !== 'undefined') {
                    localforage.setItem(tKey, merged);
                }
            }
        };
        
        mergeData(window.SERVER_PAYMENTS, 'erp_payments_v1');
        mergeData(window.SERVER_RECEIPTS, 'erp_receipts_v1');
    } catch(e) {
        console.error("Error merging server data:", e);
    }

    loadNextMultiVoucherNumber();
    updateLabels();
    
    // Auto-fill USD exchange rate and apply initial currency UI
    if (typeof window.onCurrencyChange === 'function') {
        window.onCurrencyChange();
    }
    
    // Apply exchange rate permission on load
    setTimeout(() => applyExchangeRatePermission(), 500);
    
    // Initialize first account block (delayed to ensure all functions are defined)
    setTimeout(() => {
        const container = document.getElementById('accounts-blocks-container');
        if (container) container.innerHTML = '';
        if (typeof window.addAccountBlock === 'function') {
            window.addAccountBlock();
        }
    }, 100);
});

document.addEventListener('DBReady', async () => {
    await loadAllAccounts();
    if (typeof window.onCurrencyChange === 'function') {
        window.onCurrencyChange();
    }
    applyExchangeRatePermission();
    if (typeof populateSafesDropdown === 'function') populateSafesDropdown();
});

// Deep Drill-down (Run as soon as possible, SERVER_PAYMENTS is inline)
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(async () => {
        const urlParams = new URLSearchParams(window.location.search);
        const voucherId = urlParams.get('id');
        const voucherType = urlParams.get('type') || 'payment';
        if (!voucherId) return;
        
        if (voucherType === 'receipt') {
            setVoucherType('receipt');
        } else {
            setVoucherType('payment');
        }
        
        let found = null;
        
        // 1. PRIORITY: Check sessionStorage (set by vouchers-hub before navigation)
        try {
            let cached = sessionStorage.getItem('_edit_voucher_data');
            if (cached) {
                let parsed = JSON.parse(cached);
                if (parsed) {
                    let parsedId = String(parsed.id || parsed.voucherNo || parsed.paymentNo || parsed.receiptNo || '');
                    let targetId = String(voucherId || '');
                    if (!targetId || parsedId === targetId || parsedId.replace(/^MV-?/, '') === targetId.replace(/^MV-?/, '')) {
                        found = parsed;
                        sessionStorage.removeItem('_edit_voucher_data');
                    }
                }
            }
        } catch(e) {}
        
        // 2. FALLBACK: Search through all data sources
        if (!found) {
            let serverSource = voucherType === 'receipt' ? (window.SERVER_RECEIPTS || []) : (window.SERVER_PAYMENTS || []);
            let localSourceKey = voucherType === 'receipt' ? 'erp_receipts_v1' : 'erp_payments_v1';
            let tKey = window.getTenantKey ? window.getTenantKey(localSourceKey) : localSourceKey;
            
            let localSource = [];
            if (typeof localforage !== 'undefined') {
                try {
                    localSource = (await localforage.getItem(tKey)) || (await localforage.getItem(localSourceKey)) || [];
                } catch(e) {}
            }
            if (!Array.isArray(localSource) || localSource.length === 0) {
                localSource = JSON.parse(localStorage.getItem(tKey) || localStorage.getItem(localSourceKey) || '[]');
            }
            if (!Array.isArray(localSource)) localSource = Object.values(localSource);

            let dataSource = [...serverSource, ...localSource];
            found = dataSource.find(v => {
                if (!v) return false;
                const vid = String(v.id || '');
                const vno = String(v.voucherNo || '');
                const target = String(voucherId || '');
                if (vid === target || vno === target) return true;
                if (target && vid.replace(/^MV-?/, '') === target.replace(/^MV-?/, '')) return true;
                if (target && vno.replace(/^MV-?/, '') === target.replace(/^MV-?/, '')) return true;
                return false;
            });
        }
        
        // 3. Load into form if found
        if (found) {
            loadVoucherIntoForm(found);
        }
    }, 300);
});

// ═══ Get Active System Dollar Exchange Rate ═══
window.getSystemUsdRate = function() {
    if (window.SYSTEM_USD_RATE && parseFloat(window.SYSTEM_USD_RATE) > 1) {
        return parseFloat(window.SYSTEM_USD_RATE);
    }
    let rate = 0;
    if (typeof window.getExchangeRateForCurrency === 'function') {
        rate = window.getExchangeRateForCurrency('USD');
    }
    if (!rate || rate <= 1) {
        try {
            const getTenantKey = window.getTenantKey || ((k) => k);
            const settings = JSON.parse(localStorage.getItem(getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
            rate = parseFloat(settings.rateUSD || settings.exchangeRate) || 0;
        } catch (e) {}
    }
    if (!rate || rate <= 1) {
        try {
            const glRate = parseFloat(localStorage.getItem('erp_rate_usd') || localStorage.getItem('erp_exchange_rate_usd')) || 0;
            if (glRate > 1) rate = glRate;
        } catch(e) {}
    }
    return (rate && rate > 0) ? rate : 5999;
};

// ═══ Update Currency UI Labels & Row Placeholders ═══
window.updateCurrencyUI = function(currency) {
    const isSDG = (!currency || currency === 'SDG');
    const currSymbol = isSDG ? 'SDG' : currency;
    const currName = isSDG ? 'جنيه' : (currency === 'USD' ? '$ دولار' : currency);
    
    // Header labels
    const labelLocal = document.getElementById('label-amount-local');
    const labelForeign = document.getElementById('label-amount-foreign');
    
    if (isSDG) {
        if (labelLocal) labelLocal.innerHTML = '💵 الإجمالي بالمحلي (SDG)';
        if (labelForeign) labelForeign.innerHTML = '💵 المعادل بالأجنبي (USD)';
    } else {
        if (labelLocal) labelLocal.innerHTML = '💵 المعادل بالمحلي (SDG)';
        if (labelForeign) labelForeign.innerHTML = `💵 الإجمالي بالأجنبي (${currSymbol})`;
    }
    
    // Block placeholders
    document.querySelectorAll('.block-total-amount').forEach(input => {
        input.placeholder = `المبلغ (${currName})`;
    });
    document.querySelectorAll('.block-sub-amount').forEach(input => {
        input.placeholder = `المبلغ (${currName})`;
    });

    // Refresh tafqeet for all account blocks with new currency
    document.querySelectorAll('.account-block').forEach(block => {
        const bid = block.getAttribute('data-block-id');
        if (typeof window.updateBlockTafqeetAndTier === 'function') {
            window.updateBlockTafqeetAndTier(bid);
        }
    });
};

// ═══ Currency Change: Auto-fill exchange rate from settings & update labels ═══
window.onCurrencyChange = function() {
    const currencyEl = document.getElementById('currency');
    const rateEl = document.getElementById('exchange-rate');
    if (!currencyEl || !rateEl) return;
    
    const currency = currencyEl.value || 'SDG';
    const isSDG = (currency === 'SDG');
    const usdRate = window.getSystemUsdRate();
    
    if (isSDG) {
        // When SDG: Show Dollar exchange rate (NOT 1.0!)
        let currentRate = cleanNum(rateEl.value);
        if (!currentRate || currentRate <= 1) {
            rateEl.value = Math.round(usdRate);
        }
    } else if (currency === 'USD') {
        // When USD: Show Dollar exchange rate
        let currentRate = cleanNum(rateEl.value);
        if (!currentRate || currentRate <= 1) {
            rateEl.value = Math.round(usdRate);
        }
    } else {
        // Other foreign currency (e.g. AED)
        let rate = 0;
        if (typeof window.getExchangeRateForCurrency === 'function') {
            rate = window.getExchangeRateForCurrency(currency);
        }
        if (rate > 0) {
            rateEl.value = parseFloat(rate).toFixed(2);
        } else {
            rateEl.value = Math.round(usdRate);
        }
    }
    
    const canEdit = checkExchangeRatePermission();
    rateEl.style.opacity = canEdit ? '1' : '0.6';
    
    window.updateCurrencyUI(currency);
    window._waCurrentModalCurrency = currency;
    const topSelect = document.getElementById('wa-modal-currency-select');
    if (topSelect) topSelect.value = currency;
    
    if (typeof calcTotal === 'function') calcTotal();
};

// ═══ Check exchange_rates permission ═══
function checkExchangeRatePermission() {
    try {
        const userStr = localStorage.getItem('erp_currentUser');
        if (!userStr) return true; // Fallback: allow
        const user = JSON.parse(userStr);
        
        // Admin always has access
        if (user.username === 'admin' || user.role === 'admin' || (user.securityLevel && user.securityLevel >= 9)) {
            return true;
        }
        
        let perms = {};
        if (typeof window.resolveUserPermissions === 'function') {
            perms = window.resolveUserPermissions(user);
        }
        
        // Check exchange_rates module edit permission
        if (perms.exchange_rates && perms.exchange_rates.edit) {
            return true;
        }
        
        return false;
    } catch(e) {
        return true; // Fallback: allow on error
    }
}

// ═══ Apply exchange rate permission on page load ═══
function applyExchangeRatePermission() {
    const rateEl = document.getElementById('exchange-rate');
    if (!rateEl) return;
    const canEdit = checkExchangeRatePermission();
    rateEl.style.opacity = canEdit ? '1' : '0.6';
}

let mainAccountChoices = null;
let allAccountsList = [];

async function loadAllAccounts() {
    try {
        buildAccountsArray();
    } catch (e) {
        console.error('Error loading accounts:', e);
    }
}

// Helper: Format number with commas
window.fmtNum = function(num, dec = null, hasComma = true) {
    if (num === 0 || num === '0' || !num) return '0';
    let n = parseFloat(num);
    if (isNaN(n)) return '0';
    
    if (dec !== null) {
        let factor = Math.pow(10, dec);
        n = Math.round(n * factor) / factor;
        let parts = n.toFixed(dec).split('.');
        if (hasComma) parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return dec > 0 ? parts.join('.') : parts[0];
    }
    
    let parts = n.toString().split('.');
    if (hasComma) parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
};

// Helper: Clean commas for calculation
const cleanNum = (str) => Number(String(str).replace(/,/g, '')) || 0;

// Helper: Restrict input to numbers and decimal point only
function restrictToNumbers(el) {
    let val = el.value.replace(/[^0-9.,]/g, '');
    const parts = val.split('.');
    if (parts.length > 2) {
        val = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, '');
    }
    if (el.value !== val) {
        el.value = val;
    }
}

function setVoucherType(type) {
    const typeInput = document.getElementById('voucher-type');
    if (typeInput && typeInput.value === type) return; // Prevent redundant clicks

    const primary = document.getElementById('primary-inputs-container');
    const secondary = document.getElementById('accounts-blocks-container');
    
    if (primary && secondary) {
        if (typeof playClickSound === 'function') playClickSound();

        // 1. Outward Animation: Smooth fade out and subtle shift
        primary.style.transition = 'all 0.3s ease';
        secondary.style.transition = 'all 0.3s ease';
        
        primary.style.opacity = '0';
        primary.style.transform = 'translateY(10px) scale(0.99)';
        
        secondary.style.opacity = '0';
        secondary.style.transform = 'translateY(10px) scale(0.99)';
        
        setTimeout(() => {
            // 2. Apply UI changes while hidden
            executeSetVoucherType(type);
            
            // 3. Reset transform slightly higher for smooth drop-in
            primary.style.transition = 'none';
            secondary.style.transition = 'none';
            
            primary.style.transform = 'translateY(-10px) scale(0.99)';
            secondary.style.transform = 'translateY(-10px) scale(0.99)';
            
            // Force browser reflow
            void primary.offsetWidth;
            
            // 4. Inward Animation: Elegant fade in and settle
            primary.style.transition = 'all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1)'; // Smooth ease-out
            secondary.style.transition = 'all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1)';
            
            primary.style.opacity = '1';
            primary.style.transform = 'translateY(0) scale(1)';
            
            secondary.style.opacity = '1';
            secondary.style.transform = 'translateY(0) scale(1)';
        }, 300);
    } else {
        executeSetVoucherType(type);
    }
}

function executeSetVoucherType(type) {
    const btnPayment = document.getElementById('btn-payment');
    const btnReceipt = document.getElementById('btn-receipt');
    const typeInput = document.getElementById('voucher-type');
    if (typeInput) typeInput.value = type;
    
    document.body.setAttribute('data-voucher-type', type);
    
    const pmRow = document.getElementById('payment-methods-row');
    
    if (type === 'payment') {
        if (btnPayment) btnPayment.className = 'voucher-type-btn active-payment';
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'linear-gradient(135deg, rgba(75, 14, 25, 0.45), rgba(15, 23, 42, 0.95))';
            pmRow.style.borderColor = 'rgba(159, 18, 57, 0.4)';
            pmRow.style.boxShadow = '0 8px 30px rgba(0, 0, 0, 0.5), inset 0 1px 10px rgba(120, 24, 40, 0.2)';
            pmRow.style.backdropFilter = 'blur(16px)';
            pmRow.style.webkitBackdropFilter = 'blur(16px)';
        }
    } else {
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn active-receipt';
        if (btnPayment) btnPayment.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'linear-gradient(135deg, rgba(13, 84, 64, 0.45), rgba(15, 23, 42, 0.95))';
            pmRow.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            pmRow.style.boxShadow = '0 8px 30px rgba(0, 0, 0, 0.5), inset 0 1px 10px rgba(13, 84, 64, 0.2)';
            pmRow.style.backdropFilter = 'blur(16px)';
            pmRow.style.webkitBackdropFilter = 'blur(16px)';
        }
    }
    
    updateLabels();
    currentVoucherIndex = -1;
    if (window.ConcurrencyLockManager) {
        window.ConcurrencyLockManager.releaseLock();
    }
    if (window.IdempotencyGuard) {
        window.IdempotencyGuard.regenerate();
        window.IdempotencyGuard.isSaving = false;
    }
    loadNextMultiVoucherNumber();
}

function togglePaymentMethod() {
    const radioCash = document.getElementById('radio-cash');
    const radioCheck = document.getElementById('radio-check');
    const radioMobile = document.getElementById('radio-mobile');
    const radioHybrid = document.getElementById('radio-hybrid');
    
    let method = 'cash';
    if (radioCheck && radioCheck.checked) method = 'cheque';
    else if (radioMobile && radioMobile.checked) method = 'bank';
    else if (radioHybrid && radioHybrid.checked) method = 'hybrid';
    
    togglePaymentSection(method);
}

function updateLabels() {
    const type = document.getElementById('voucher-type').value;
    const label = document.getElementById('main-account-label');
    const subHeader = document.getElementById('sub-account-header');
    const subTitle = document.getElementById('sub-accounts-title');
    const primaryContainer = document.getElementById('primary-inputs-container');
    const secondaryContainer = document.getElementById('secondary-table-container');
    const entryCardLabel = document.getElementById('entry-card-label');
    const currencyLabel = document.getElementById('currency-label');
    
    if (type === 'payment') {
        if (primaryContainer) {
            primaryContainer.className = 'glass-container glass-red';
        }
        if (secondaryContainer) {
            secondaryContainer.className = 'glass-container glass-green';
        }
        if (label) {
            label.innerHTML = 'من حساب (صرف) <span class="animated-arrow-left">←</span>';
            label.style.setProperty('color', '#f43f5e', 'important');
            label.style.setProperty('font-size', '1.45rem', 'important');
            label.style.setProperty('font-weight', '900', 'important');
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.gap = '8px';
        }
        if (subHeader) {
            subHeader.innerHTML = 'إلى حساب (استلام) <span class="animated-arrow-right">→</span>';
            subHeader.style.setProperty('color', '#34d399', 'important');
            subHeader.style.fontSize = '1.45rem';
        }
        if (subTitle) {
            subTitle.innerHTML = '<span>👥</span> إلى حساب (استلام) <span class="animated-arrow-right">→</span>';
            subTitle.style.setProperty('color', '#34d399', 'important');
            subTitle.style.fontSize = '1.45rem';
        }
        if (entryCardLabel) {
            entryCardLabel.innerHTML = 'إلى حساب (مستفيد) <span class="animated-arrow-right">→</span>';
            entryCardLabel.style.setProperty('color', '#34d399', 'important');
        }
        document.querySelectorAll('.block-label').forEach(lbl => {
            lbl.textContent = 'إلى حساب (مستلم)';
            lbl.style.color = '#34d399';
            if (lbl.parentElement) {
                lbl.parentElement.style.color = '#34d399';
                lbl.parentElement.style.fontSize = '1.45rem';
                lbl.parentElement.style.fontWeight = '900';
            }
        });
        if (currencyLabel) {
            currencyLabel.innerText = 'عملة الصرف';
        }
        document.querySelectorAll('.account-block').forEach(block => {
            block.classList.remove('glass-red');
            block.classList.add('glass-green');
            const arrow = block.querySelector('.animated-arrow-right, .animated-arrow-left');
            if (arrow) {
                arrow.className = 'animated-arrow-right';
                arrow.textContent = '→';
                arrow.style.color = '#34d399';
            }
        });
        document.querySelectorAll('.block-total-amount').forEach(el => {
            el.style.color = '#34d399';
            el.style.background = 'rgba(16, 185, 129, 0.08)';
            el.style.borderColor = 'rgba(52, 211, 153, 0.4)';
            el.style.boxShadow = '0 0 10px rgba(52, 211, 153, 0.25)';
        });
    } else {
        if (primaryContainer) {
            primaryContainer.className = 'glass-container glass-green';
        }
        if (secondaryContainer) {
            secondaryContainer.className = 'glass-container glass-red';
        }
        if (label) {
            label.innerHTML = 'إلى حساب (استلام) <span class="animated-arrow-right">→</span>';
            label.style.setProperty('color', '#34d399', 'important');
            label.style.setProperty('font-size', '1.45rem', 'important');
            label.style.setProperty('font-weight', '900', 'important');
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.gap = '8px';
        }
        if (subHeader) {
            subHeader.innerHTML = 'من حساب (دفع) <span class="animated-arrow-left">←</span>';
            subHeader.style.setProperty('color', '#ef4444', 'important');
            subHeader.style.fontSize = '1.45rem';
        }
        if (subTitle) {
            subTitle.innerHTML = '<span>👥</span> من حساب (دفع) <span class="animated-arrow-left">←</span>';
            subTitle.style.setProperty('color', '#ef4444', 'important');
            subTitle.style.fontSize = '1.45rem';
        }
        if (entryCardLabel) {
            entryCardLabel.innerHTML = 'من حساب (دافع) <span class="animated-arrow-left">←</span>';
            entryCardLabel.style.setProperty('color', '#ef4444', 'important');
        }
        document.querySelectorAll('.block-label').forEach(lbl => {
            lbl.textContent = 'من حساب (دافع)';
            lbl.style.color = '#ef4444';
            if (lbl.parentElement) {
                lbl.parentElement.style.color = '#ef4444';
                lbl.parentElement.style.fontSize = '1.45rem';
                lbl.parentElement.style.fontWeight = '900';
            }
        });
        document.querySelectorAll('.account-block').forEach(block => {
            block.classList.remove('glass-green');
            block.classList.add('glass-red');
            const arrow = block.querySelector('.animated-arrow-right, .animated-arrow-left');
            if (arrow) {
                arrow.className = 'animated-arrow-left';
                arrow.textContent = '←';
                arrow.style.color = '#ef4444';
            }
        });
        document.querySelectorAll('.block-total-amount').forEach(el => {
            el.style.color = '#ef4444';
            el.style.background = 'rgba(239, 68, 68, 0.08)';
            el.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            el.style.boxShadow = '0 0 10px rgba(239, 68, 68, 0.25)';
        });
        if (currencyLabel) {
            currencyLabel.innerText = 'عملة الاستلام';
        }
    }
}

// ═══════════════════════════════════════════════════════
// Entry Card System - New UI functions
// ═══════════════════════════════════════════════════════

window.addAccountBlock = function() {
    blockCounter++;
    const id = Date.now() + '_' + blockCounter;
    const templateEl = document.getElementById('account-block-template');
    if (!templateEl) { console.error('Template not found!'); return; }
    
    // Get raw HTML string from template
    // Use innerHTML directly (works in all modern browsers for <template>)
    let rawHtml = templateEl.innerHTML;
    // Fallback: if innerHTML is empty, try content serialization
    if (!rawHtml || rawHtml.trim().length === 0) {
        const tempDiv = document.createElement('div');
        tempDiv.appendChild(templateEl.content.cloneNode(true));
        rawHtml = tempDiv.innerHTML;
    }
    const html = rawHtml.replace(/{ID}/g, id);
    
    const container = document.getElementById('accounts-blocks-container');
    if (!container) return;
    container.insertAdjacentHTML('beforeend', html);
    
    // Set label based on voucher type
    const voucherTypeEl = document.getElementById('voucher-type');
    const isPayment = voucherTypeEl ? (voucherTypeEl.value === 'payment') : true;
    const blockEl = document.querySelector(`.account-block[data-block-id="${id}"]`);
    const label = blockEl ? blockEl.querySelector('.block-label') : null;
    const arrow = blockEl ? blockEl.querySelector('.animated-arrow-right, .animated-arrow-left') : null;
    
    const totalInput = blockEl ? blockEl.querySelector('.block-total-amount') : null;
    
    if (isPayment) {
        if (blockEl) {
            blockEl.classList.remove('glass-red');
            blockEl.classList.add('glass-green');
        }
        if (label) {
            label.textContent = 'إلى حساب (مستلم)';
            label.style.color = '#34d399';
            if (label.parentElement) {
                label.parentElement.style.color = '#34d399';
                label.parentElement.style.fontSize = '1.45rem';
                label.parentElement.style.fontWeight = '900';
            }
        }
        if (arrow) {
            arrow.className = 'animated-arrow-right';
            arrow.textContent = '→';
            arrow.style.color = '#34d399';
        }
        if (totalInput) {
            totalInput.style.color = '#34d399';
            totalInput.style.background = 'rgba(16, 185, 129, 0.08)';
            totalInput.style.borderColor = 'rgba(52, 211, 153, 0.4)';
            totalInput.style.boxShadow = '0 0 10px rgba(52, 211, 153, 0.25)';
        }
    } else {
        if (blockEl) {
            blockEl.classList.remove('glass-green');
            blockEl.classList.add('glass-red');
        }
        if (label) {
            label.textContent = 'من حساب (دافع)';
            label.style.color = '#ef4444';
            if (label.parentElement) {
                label.parentElement.style.color = '#ef4444';
                label.parentElement.style.fontSize = '1.45rem';
                label.parentElement.style.fontWeight = '900';
            }
        }
        if (arrow) {
            arrow.className = 'animated-arrow-left';
            arrow.textContent = '←';
            arrow.style.color = '#ef4444';
        }
        if (totalInput) {
            totalInput.style.color = '#ef4444';
            totalInput.style.background = 'rgba(239, 68, 68, 0.08)';
            totalInput.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            totalInput.style.boxShadow = '0 0 10px rgba(239, 68, 68, 0.25)';
        }
    }
    
    // Populate safes for this block
    const safeSelect = document.getElementById(`cash-safe-${id}`);
    if (safeSelect && typeof window.populateSafesDropdown === 'function') {
        window.populateSafesDropdown(safeSelect);
    }
    
    // Auto toggle bank by default
    if (typeof window.toggleBlockPaymentMethod === 'function') {
        window.toggleBlockPaymentMethod(id, 'bank');
    }
    
    // Update amount placeholder to match current currency
    const currEl = document.getElementById('currency');
    const curVal = currEl ? (currEl.value || 'SDG') : 'SDG';
    const curPlaceholder = curVal === 'SDG' ? 'المبلغ (جنيه)' : (curVal === 'USD' ? 'المبلغ ($ دولار)' : `المبلغ (${curVal})`);
    if (totalInput) totalInput.placeholder = curPlaceholder;
    if (blockEl) {
        blockEl.querySelectorAll('.block-sub-amount').forEach(si => si.placeholder = curPlaceholder);
    }
    
    // Update delete buttons visibility
    if (typeof window.updateDeleteButtonsVisibility === 'function') {
        window.updateDeleteButtonsVisibility();
    }
}

window.updateDeleteButtonsVisibility = function() {
    const blocks = document.querySelectorAll('.account-block');
    blocks.forEach(block => {
        const btn = block.querySelector('.btn-remove-block');
        if (btn) {
            if (blocks.length <= 1) {
                btn.style.display = 'none';
            } else {
                btn.style.display = 'flex';
            }
        }
    });
}

window.removeAccountBlock = function(id) {
    const blocks = document.querySelectorAll('.account-block');
    if (blocks.length <= 1) {
        showToast('يجب أن يكون هناك حساب واحد على الأقل', 'error');
        return;
    }
    const block = document.querySelector(`.account-block[data-block-id="${id}"]`);
    if (block) {
        block.remove();
        calcTotal();
        
        // Update delete buttons visibility
        if (typeof window.updateDeleteButtonsVisibility === 'function') {
            window.updateDeleteButtonsVisibility();
        }
    }
}

window.toggleBlockPaymentMethod = function(blockId, method) {
    const btn = document.getElementById(`pm-${method}-${blockId}`);
    const details = document.getElementById(`${method}-details-${blockId}`);
    if (!btn || !details) return;
    
    if (btn.classList.contains('active')) {
        btn.classList.remove('active');
        details.style.display = 'none';
        // Clear the amount when deactivating
        const amtEl = document.getElementById(`${method}-amount-${blockId}`);
        if (amtEl) amtEl.value = '';
        recalcBlockTotal(blockId);
    } else {
        btn.classList.add('active');
        details.style.display = 'block';
    }
}

// ═══ Animated Block Total Recalculation ═══
window.recalcBlockTotal = function(blockId) {
    const block = document.querySelector(`.account-block[data-block-id="${blockId}"]`);
    const totalEl = document.getElementById(`amount-${blockId}`);
    if (!totalEl || !block) return;
    
    // Sum ALL sub-amount inputs within this block
    let newTotal = 0;
    block.querySelectorAll('.block-sub-amount').forEach(inp => {
        newTotal += parseFloat(inp.value.replace(/,/g, '')) || 0;
    });

    
    // Animate the total with counting effect
    const oldTotal = parseFloat(totalEl.value.replace(/,/g, '')) || 0;
    if (oldTotal === newTotal) return;
    
    const voucherTypeEl = document.getElementById('voucher-type');
    const isPayment = voucherTypeEl ? (voucherTypeEl.value === 'payment') : true;
    const pulseShadow = isPayment ? '0 0 15px rgba(52, 211, 153, 0.65)' : '0 0 15px rgba(239, 68, 68, 0.65)';
    const defaultShadow = isPayment ? '0 0 10px rgba(52, 211, 153, 0.25)' : '0 0 10px rgba(239, 68, 68, 0.25)';

    // Visual pulse effect
    totalEl.style.transform = 'scale(1.08)';
    totalEl.style.boxShadow = pulseShadow;
    
    // Counting animation
    const duration = 300; // ms
    const startTime = performance.now();
    const diff = newTotal - oldTotal;
    
    function animateCount(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Ease out quad
        const eased = 1 - (1 - progress) * (1 - progress);
        const currentVal = oldTotal + diff * eased;
        
        const animDec = totalEl.getAttribute('data-decimals') !== null ? parseInt(totalEl.getAttribute('data-decimals')) : 2;
        totalEl.value = currentVal > 0 ? fmtNum(currentVal, animDec) : '0.00';
        
        if (progress < 1) {
            requestAnimationFrame(animateCount);
        } else {
            const finalDec = totalEl.getAttribute('data-decimals') !== null ? parseInt(totalEl.getAttribute('data-decimals')) : null;
            totalEl.value = newTotal > 0 ? fmtNum(newTotal, finalDec) : '0.00';
            // Reset visual effect
            setTimeout(() => {
                totalEl.style.transform = 'scale(1)';
                totalEl.style.boxShadow = defaultShadow;
            }, 150);
            if (typeof window.updateBlockTafqeetAndTier === 'function') {
                window.updateBlockTafqeetAndTier(blockId);
            }
            // Update voucher grand total
            if (typeof calcTotal === 'function') calcTotal();
        }
    }
    requestAnimationFrame(animateCount);
}

// ═══ Add/Remove Bank Transfer Rows ═══
window.addBankRow = function(blockId) {
    console.log('addBankRow called with blockId:', blockId);
    const container = document.getElementById(`bank-rows-${blockId}`);
    console.log('bank container found:', !!container, `bank-rows-${blockId}`);
    if (!container) return;
    const row = document.createElement('div');
    row.className = 'bank-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px; animation: rowSlideIn 0.3s ease;';
    row.innerHTML = `
        <div style="width: 90px; flex-shrink: 0;"></div>
        <div style="width: 220px; flex-shrink: 0;">
            <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" data-block-id="${blockId}" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1; min-width: 120px;">
            <input type="text" autocomplete="off" list="master-banks-datalist" class="form-input block-bank-name-input" placeholder="اسم البنك" style="margin-bottom: 0;">
        </div>
        <div style="flex: 1; min-width: 120px;">
            <input type="text" autocomplete="off" class="form-input block-transfer-no-input" placeholder="رقم التحويلة" style="margin-bottom: 0;">
        </div>
        <div style="flex: 1.5; min-width: 150px;">
            <input type="text" autocomplete="off" class="form-input block-bank-desc-input" placeholder="بيان التحويلة" style="margin-bottom: 0;">
        </div>
        <button type="button" data-action="remove-bank-row" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #ef4444; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="حذف هذه التحويلة">✖</button>
    `;
    container.appendChild(row);
}

window.removeBankRow = function(btn, blockId) {
    const row = btn.closest('.bank-row');
    if (row) {
        row.style.animation = 'rowSlideIn 0.2s ease reverse';
        setTimeout(() => {
            row.remove();
            recalcBlockTotal(blockId);
        }, 200);
    }
}

// ═══ Add/Remove Cheque Rows ═══
window.addChequeRow = function(blockId) {
    console.log('addChequeRow called with blockId:', blockId);
    const container = document.getElementById(`cheque-rows-${blockId}`);
    console.log('cheque container found:', !!container, `cheque-rows-${blockId}`);
    if (!container) return;
    const row = document.createElement('div');
    row.className = 'cheque-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px; animation: rowSlideIn 0.3s ease;';
    row.innerHTML = `
        <div style="width: 90px; flex-shrink: 0;"></div>
        <div style="width: 220px; flex-shrink: 0;">
            <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" data-block-id="${blockId}" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1; min-width: 90px;">
            <input type="text" autocomplete="off" list="master-banks-datalist" class="form-input block-cheque-bank-input" placeholder="اسم البنك" style="margin-bottom: 0;">
        </div>
        <div style="flex: 1; min-width: 80px;">
            <input type="text" autocomplete="off" class="form-input block-cheque-no-input" placeholder="رقم الشيك" style="margin-bottom: 0;">
        </div>
        <div style="flex: 1; min-width: 100px;">
            <input type="date" class="form-input block-cheque-date-input" title="تاريخ الاستحقاق" style="margin-bottom: 0;">
        </div>
        <button type="button" data-action="remove-cheque-row" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #ef4444; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="حذف هذا الشيك">✖</button>
    `;
    container.appendChild(row);
}

window.removeChequeRow = function(btn, blockId) {
    const row = btn.closest('.cheque-row');
    if (row) {
        row.style.animation = 'rowSlideIn 0.2s ease reverse';
        setTimeout(() => {
            row.remove();
            recalcBlockTotal(blockId);
        }, 200);
    }
}

// ═══ CENTRAL EVENT DELEGATION ═══
// Uses DOM traversal instead of getElementById for maximum reliability
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    
    const action = btn.getAttribute('data-action');
    const block = btn.closest('.account-block');
    const blockId = block ? block.getAttribute('data-block-id') : btn.getAttribute('data-block');
    
    if (action === 'add-bank-row') {
        // Find container by DOM traversal, not by ID
        const container = block ? block.querySelector('.bank-rows-container') : null;
        if (!container) { console.error('bank container not found'); return; }
        const row = document.createElement('div');
        row.className = 'bank-row';
        row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px; animation: rowSlideIn 0.3s ease;';
        row.innerHTML = `
            <div style="width: 90px; flex-shrink: 0;"></div>
            <div style="width: 220px; flex-shrink: 0;">
                <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
            </div>
            <div style="flex: 1; min-width: 120px;">
                <input type="text" autocomplete="off" list="master-banks-datalist" class="form-input block-bank-name-input" placeholder="اسم البنك" style="margin-bottom: 0;">
            </div>
            <div style="flex: 1; min-width: 120px;">
                <input type="text" autocomplete="off" class="form-input block-transfer-no-input" placeholder="رقم التحويلة" style="margin-bottom: 0;">
            </div>
            <div style="flex: 1.5; min-width: 150px;">
                <input type="text" autocomplete="off" class="form-input block-bank-desc-input" placeholder="بيان التحويل" style="margin-bottom: 0;">
            </div>
            <button type="button" data-action="remove-bank-row" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #ef4444; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="حذف هذه التحويلة">✖</button>
        `;
        container.appendChild(row);
        
    } else if (action === 'add-cheque-row') {
        // Find container by DOM traversal, not by ID
        const container = block ? block.querySelector('.cheque-rows-container') : null;
        if (!container) { console.error('cheque container not found'); return; }
        const row = document.createElement('div');
        row.className = 'cheque-row';
        row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px; animation: rowSlideIn 0.3s ease;';
        row.innerHTML = `
            <div style="width: 90px; flex-shrink: 0;"></div>
            <div style="width: 220px; flex-shrink: 0;">
                <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
            </div>
            <div style="flex: 1; min-width: 90px;">
                <input type="text" autocomplete="off" list="master-banks-datalist" class="form-input block-cheque-bank-input" placeholder="اسم البنك" style="margin-bottom: 0;">
            </div>
            <div style="flex: 1; min-width: 80px;">
                <input type="text" autocomplete="off" class="form-input block-cheque-no-input" placeholder="رقم الشيك" style="margin-bottom: 0;">
            </div>
            <div style="flex: 1; min-width: 100px;">
                <input type="date" class="form-input block-cheque-date-input" title="تاريخ الاستحقاق" style="margin-bottom: 0;">
            </div>
            <div style="flex: 1.5; min-width: 150px;">
                <input type="text" autocomplete="off" class="form-input block-cheque-desc-input" placeholder="بيان الشيك" style="margin-bottom: 0;">
            </div>
            <button type="button" data-action="remove-cheque-row" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #ef4444; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="حذف هذا الشيك">✖</button>
        `;
        container.appendChild(row);
        
        
    } else if (action === 'remove-block' && blockId) {
        removeAccountBlock(blockId);
    } else if (action === 'remove-bank-row') {
        const row = btn.closest('.bank-row');
        if (row) {
            const bid = block ? block.getAttribute('data-block-id') : null;
            row.style.animation = 'rowSlideIn 0.2s ease reverse';
            setTimeout(() => { row.remove(); if(bid) recalcBlockTotal(bid); }, 200);
        }
    } else if (action === 'remove-cheque-row') {
        const row = btn.closest('.cheque-row');
        if (row) {
            const bid = block ? block.getAttribute('data-block-id') : null;
            row.style.animation = 'rowSlideIn 0.2s ease reverse';
            setTimeout(() => { row.remove(); if(bid) recalcBlockTotal(bid); }, 200);
        }
    } else if (action === 'toggle-pm') {
        const method = btn.getAttribute('data-method');
        if (method && blockId) toggleBlockPaymentMethod(blockId, method);
    }
});

// Number formatting utility
window.formatWhileTyping = function(input) {
    let cursor = input.selectionStart;
    let oldLength = input.value.length;
    let val = input.value.replace(/[^0-9.-]/g, '');
    let parts = val.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    input.value = parts.join('.');
    let newLength = input.value.length;
    let newCursor = cursor + (newLength - oldLength);
    if (newCursor > 0) input.setSelectionRange(newCursor, newCursor);
};

// Handle oninput for sub-amount fields (via delegation)
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('block-sub-amount') || 
        e.target.classList.contains('block-total-amount') || 
        e.target.classList.contains('block-cash-amount') || 
        e.target.classList.contains('transfer-amount') || 
        e.target.classList.contains('cheque-amount')) {
        
        window.formatWhileTyping(e.target);
        
        const block = e.target.closest('.account-block');
        if (block) {
            const bid = block.getAttribute('data-block-id');
            recalcBlockTotal(bid);
            if (typeof window.updateBlockTafqeetAndTier === 'function') {
                window.updateBlockTafqeetAndTier(bid);
            }
        }
    }
});

// ══════════════════════════════════════════════════════════════════════════════════════
// 🧠 Cognitive Ergonomics & Fat-Finger Prevention Engine (محرك الحماية والتفقيط اللحظي)
// ══════════════════════════════════════════════════════════════════════════════════════

window.formatArabicTafqeet = function(amount, currencyCode = 'SDG') {
    const rawNum = typeof amount === 'number' ? amount : (parseFloat(String(amount).replace(/,/g, '')) || 0);
    
    const getCurrencyNames = (c) => {
        const norm = (c || 'SDG').toUpperCase();
        switch (norm) {
            case 'USD': return { unit: 'دولار أمريكي', sub: 'سنت' };
            case 'AED': return { unit: 'درهم إماراتي', sub: 'فلس' };
            case 'SAR': return { unit: 'ريال سعودي', sub: 'هللة' };
            case 'QAR': return { unit: 'ريال قطري', sub: 'درهم' };
            case 'EUR': return { unit: 'يورو', sub: 'سنت' };
            case 'GBP': return { unit: 'جنيه إسترليني', sub: 'بنس' };
            case 'SDG':
            default:
                return { unit: 'جنيه سوداني', sub: 'قرش' };
        }
    };

    const cInfo = getCurrencyNames(currencyCode);
    if (rawNum <= 0) return `فقط صفر ${cInfo.unit} لا غير`;

    const intPart = Math.floor(rawNum);
    const decPart = Math.round((rawNum - intPart) * 100);

    const ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة',
        'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
    const tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    const hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];

    function convertThreeDigits(num) {
        let str = '';
        const h = Math.floor(num / 100);
        const rem = num % 100;
        if (h > 0) str += hundreds[h];
        if (rem > 0) {
            if (str !== '') str += ' و';
            if (rem < 20) {
                str += ones[rem];
            } else {
                const t = Math.floor(rem / 10);
                const o = rem % 10;
                if (o > 0) str += ones[o] + ' و' + tens[t];
                else str += tens[t];
            }
        }
        return str;
    }

    const scales = [
        { val: 1000000000000, single: 'تريليون', two: 'تريليونان', plural: 'تريليونات' },
        { val: 1000000000, single: 'مليار', two: 'ملياران', plural: 'مليارات' },
        { val: 1000000, single: 'مليون', two: 'مليونان', plural: 'ملايين' },
        { val: 1000, single: 'ألف', two: 'ألفان', plural: 'آلاف' }
    ];

    let remaining = intPart;
    let parts = [];

    for (const scale of scales) {
        if (remaining >= scale.val) {
            const count = Math.floor(remaining / scale.val);
            remaining = remaining % scale.val;
            if (count === 1) {
                parts.push(scale.single);
            } else if (count === 2) {
                parts.push(scale.two);
            } else if (count >= 3 && count <= 10) {
                parts.push(convertThreeDigits(count) + ' ' + scale.plural);
            } else {
                parts.push(convertThreeDigits(count) + ' ' + scale.single);
            }
        }
    }

    if (remaining > 0) {
        parts.push(convertThreeDigits(remaining));
    }

    let result = parts.length > 0 ? parts.join(' و') : 'صفر';
    result = `فقط ${result} ${cInfo.unit}`;
    if (decPart > 0) {
        result += ` و ${decPart} ${cInfo.sub}`;
    }
    result += ' لا غير';
    return result;
};

window.updateBlockTafqeetAndTier = function(blockId) {
    if (!blockId) return;
    const amountInput = document.getElementById(`amount-${blockId}`);
    const rowEl = document.getElementById(`tafqeet-row-${blockId}`);
    const textEl = document.getElementById(`tafqeet-text-${blockId}`);
    const alertEl = document.getElementById(`tafqeet-alert-${blockId}`);
    if (!amountInput) return;

    const val = parseFloat(String(amountInput.value).replace(/,/g, '')) || 0;
    const currencyEl = document.getElementById('currency');
    const currency = currencyEl ? (currencyEl.value || 'SDG') : 'SDG';
    const isSDG = (currency === 'SDG');

    // Thresholds: For SDG: Mega >= 1,000,000, Ultra >= 10,000,000.
    // For Foreign (USD/AED): Mega >= 1,000, Ultra >= 10,000.
    const megaThreshold = isSDG ? 1000000 : 1000;
    const ultraThreshold = isSDG ? 10000000 : 10000;

    // 1. Visual Tiering on input element
    amountInput.classList.remove('amount-tier-mega', 'amount-tier-ultra');
    if (val >= ultraThreshold) {
        amountInput.classList.add('amount-tier-ultra');
    } else if (val >= megaThreshold) {
        amountInput.classList.add('amount-tier-mega');
    }

    // 2. Tafqeet and Alert Badge
    if (val > 0) {
        if (textEl) {
            textEl.textContent = window.formatArabicTafqeet(val, currency);
        }
        if (rowEl) {
            rowEl.style.display = 'flex';
        }

        if (alertEl) {
            if (val >= ultraThreshold) {
                alertEl.className = 'block-fat-finger-alert alert-badge-ultra';
                alertEl.innerHTML = `🚨 مبلغ فائق (${isSDG ? '10 مليون فما فوق' : '10,000 فما فوق'}) | تأكد من عدد الأصفار ⚠️`;
                alertEl.style.display = 'inline-flex';
            } else if (val >= megaThreshold) {
                alertEl.className = 'block-fat-finger-alert alert-badge-mega';
                alertEl.innerHTML = `👑 مبلغ كبير (${isSDG ? 'مليون فما فوق' : '1,000 فما فوق'}) | يرجى مراجعة الأصفار ⚠️`;
                alertEl.style.display = 'inline-flex';
            } else {
                alertEl.style.display = 'none';
                alertEl.innerHTML = '';
            }
        }
    } else {
        if (rowEl) rowEl.style.display = 'none';
        if (textEl) textEl.textContent = '';
        if (alertEl) {
            alertEl.style.display = 'none';
            alertEl.innerHTML = '';
        }
    }
};

window.updateGrandTafqeet = function(totalAmount, currency, localAmt) {
    const textEl = document.getElementById('grand-tafqeet-text');
    const alertEl = document.getElementById('grand-fat-finger-alert');
    const localHdrEl = document.getElementById('amount-local-hdr');
    if (!textEl) return;

    const val = parseFloat(String(totalAmount).replace(/,/g, '')) || 0;
    const curr = currency || (document.getElementById('currency') ? document.getElementById('currency').value : 'SDG') || 'SDG';
    const isSDG = (curr === 'SDG');

    if (isSDG) {
        textEl.textContent = window.formatArabicTafqeet(val, 'SDG');
    } else {
        const localVal = typeof localAmt === 'number' ? localAmt : (parseFloat(String(localAmt).replace(/,/g, '')) || 0);
        const foreignText = window.formatArabicTafqeet(val, curr);
        const localText = window.formatArabicTafqeet(localVal, 'SDG');
        textEl.textContent = `${foreignText} (المعادل بالمحلي: ${localText})`;
    }

    // Visual tiering on local grand total input
    if (localHdrEl) {
        localHdrEl.classList.remove('amount-tier-mega', 'amount-tier-ultra');
        const localVal = parseFloat(String(localHdrEl.value).replace(/,/g, '')) || 0;
        if (localVal >= 10000000) {
            localHdrEl.classList.add('amount-tier-ultra');
        } else if (localVal >= 1000000) {
            localHdrEl.classList.add('amount-tier-mega');
        }
    }

    // Grand Alert Badge
    if (alertEl) {
        const effLocal = isSDG ? val : (parseFloat(String(localHdrEl ? localHdrEl.value : val).replace(/,/g, '')) || val);
        if (effLocal >= 10000000) {
            alertEl.className = 'alert-badge-ultra';
            alertEl.innerHTML = `🚨 إجمالي السند ضخم جداً (10 مليون فما فوق) | تأكد من صحة المبالغ ⚠️`;
            alertEl.style.display = 'inline-flex';
        } else if (effLocal >= 1000000) {
            alertEl.className = 'alert-badge-mega';
            alertEl.innerHTML = `👑 إجمالي السند مليون فما فوق | تأكد من عدد الأصفار ⚠️`;
            alertEl.style.display = 'inline-flex';
        } else {
            alertEl.style.display = 'none';
            alertEl.innerHTML = '';
        }
    }
};

function formatInput(input) {
    formatAmount(input);
    const block = input.closest('.account-block');
    if (block) {
        const blockId = block.getAttribute('data-block-id');
        if (typeof window.updateBlockTafqeetAndTier === 'function') {
            window.updateBlockTafqeetAndTier(blockId);
        }
    }
    calcTotal();
}

function calcTotal() {
    let total = 0;

    const blocks = document.querySelectorAll('.account-block');
    blocks.forEach(block => {
        const id = block.getAttribute('data-block-id');
        const amountInput = document.getElementById(`amount-${id}`);
        if (amountInput) {
            total += cleanNum(amountInput.value);
        }
    });

    const totalAmtEl = document.getElementById('total-amount');
    if (totalAmtEl) {
        const dec = totalAmtEl.getAttribute('data-decimals') !== null ? parseInt(totalAmtEl.getAttribute('data-decimals')) : null;
        totalAmtEl.innerText = fmtNum(total, dec);
    }

    const totalRowsEl = document.getElementById('total-rows');
    if (totalRowsEl) totalRowsEl.innerText = document.querySelectorAll('.account-block').length;
    
    // Currency & Exchange Rate Calculation
    const currencyEl = document.getElementById('currency');
    const currency = currencyEl ? (currencyEl.value || 'SDG') : 'SDG';
    const isSDG = (currency === 'SDG');

    const rateEl = document.getElementById('exchange-rate');
    let rate = rateEl ? cleanNum(rateEl.value) : 0;
    if (rate <= 0) {
        rate = (typeof window.getSystemUsdRate === 'function') ? window.getSystemUsdRate() : 2600;
        if (rateEl && rate > 0) rateEl.value = Math.round(rate);
    }
    const effRate = rate > 0 ? rate : 1;

    let localAmt = 0;
    let foreignAmt = 0;

    if (isSDG) {
        // Any amount entered is in Sudanese Pounds (SDG)
        // Local = entered total; Foreign = total / rate (USD equivalent)
        localAmt = total;
        foreignAmt = effRate > 0 ? (total / effRate) : 0;
    } else {
        // Any amount entered is in Foreign Currency (USD / AED / etc.)
        // Foreign = entered total; Local = total * rate (SDG equivalent)
        foreignAmt = total;
        localAmt = total * effRate;
    }

    const localHdrEl = document.getElementById('amount-local-hdr');
    if (localHdrEl) {
        const dec = localHdrEl.getAttribute('data-decimals') !== null ? parseInt(localHdrEl.getAttribute('data-decimals')) : null;
        localHdrEl.value = fmtNum(localAmt, dec);
        localHdrEl.style.setProperty('color', '#ef4444', 'important');
        localHdrEl.style.setProperty('-webkit-text-fill-color', '#ef4444', 'important');
        localHdrEl.style.setProperty('background', '#ffffff', 'important');
    }

    const eqEl = document.getElementById('amount-foreign');
    if (eqEl) {
        const dec = eqEl.getAttribute('data-decimals') !== null ? parseInt(eqEl.getAttribute('data-decimals')) : null;
        eqEl.value = fmtNum(foreignAmt, dec);
        eqEl.style.setProperty('color', '#ef4444', 'important');
        eqEl.style.setProperty('-webkit-text-fill-color', '#ef4444', 'important');
        eqEl.style.setProperty('background', '#ffffff', 'important');
    }

    if (typeof window.updateGrandTafqeet === 'function') {
        window.updateGrandTafqeet(total, currency, localAmt);
    }

    if (typeof window.updateCurrencyUI === 'function') {
        window.updateCurrencyUI(currency);
    }
    
    if (typeof window.applySystemNumberFormatting === 'function') {
        window.applySystemNumberFormatting();
    }

    const paymentPanel = document.getElementById('payment-panel');
    if (paymentPanel) {
        if (total > 0) {
            paymentPanel.style.display = 'block';
            if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
        } else {
            paymentPanel.style.display = 'none';
        }
    }
}


function showCenterError(msg, type = 'default') {
    const existing = document.getElementById('center-error-modal');
    if (existing) existing.remove();
    
    let bgGradient = 'linear-gradient(135deg, #7f1d1d, #b91c1c)';
    let borderColor = '#f87171';
    let icon = '❌';
    let btnText = 'فهمت ذلك';
    let shadowColor = 'rgba(239, 68, 68, 0.5)';
    let btnDismissLogic = "document.getElementById('center-error-modal').remove()";
    
    if (type === 'empty') {
        bgGradient = 'linear-gradient(135deg, #ea580c, #c2410c)'; // Orange-Red for empty fields
        borderColor = '#fdba74';
        icon = '⚠️';
        btnText = 'حسناً، سأقوم بإدخاله';
        shadowColor = 'rgba(234, 88, 12, 0.6)';
    } else if (type === 'short') {
        bgGradient = 'linear-gradient(135deg, #be123c, #9f1239)'; // Pinkish-Red for short numbers
        borderColor = '#fda4af';
        icon = '🔢';
        btnText = 'تعديل الرقم';
        shadowColor = 'rgba(190, 18, 60, 0.6)';
    } else if (type === 'duplicate') {
        bgGradient = 'linear-gradient(135deg, #7f1d1d, #450a0a)'; // Dark Maroon for duplicates
        borderColor = '#ef4444';
        icon = '🔄';
        btnText = 'إلغاء التكرار';
        shadowColor = 'rgba(127, 29, 29, 0.7)';
    }

    const modal = document.createElement('div');
    modal.id = 'center-error-modal';
    modal.style.cssText = 'position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 999999; display: flex; align-items: center; justify-content: center; direction: rtl; opacity: 0; transition: opacity 0.3s;';
    
    if (type === 'empty') {
        modal.onclick = function(e) {
            if (e.target === modal) modal.remove();
        };
    }
    
    modal.innerHTML = `
        <div style="background: ${bgGradient}; border: 2px solid ${borderColor}; border-radius: 15px; padding: 30px 40px; box-shadow: 0 10px 30px ${shadowColor}; text-align: center; max-width: 500px; width: 90%; transform: scale(0.8); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <div style="font-size: 4rem; margin-bottom: 15px; text-shadow: 0 4px 10px rgba(0,0,0,0.3);">${icon}</div>
            <h2 style="color: #fff; font-family: 'Tajawal', sans-serif; font-size: 1.6rem; margin: 0 0 20px 0; line-height: 1.4;">${msg}</h2>
            <button onclick="${btnDismissLogic}" style="background: #fff; color: #b91c1c; border: none; padding: 10px 30px; border-radius: 8px; font-weight: bold; font-family: 'Tajawal', sans-serif; font-size: 1.1rem; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">${btnText}</button>
        </div>
    `;
    document.body.appendChild(modal);
    if (typeof playErrorSound === 'function') playErrorSound();
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
        modal.children[0].style.transform = 'scale(1)';
    });
}


function showToast(msg, type='success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icons = { success:'✔️', error:'✖️', info:'ℹ️' };
    toast.innerHTML = `<span>${icons[type] || '🔔'}</span> <span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

/* --- Context Menu and Dynamic Columns Logic --- */
function toggleColumn(colClass, isVisible) {
    const table = document.getElementById('voucher-table');
    if (!table) return;
    if (isVisible) {
        table.classList.add('show-col-' + colClass);
    } else {
        table.classList.remove('show-col-' + colClass);
    }
    // Save preference
    const prefs = JSON.parse(localStorage.getItem('multiVoucherColPrefs') || '{}');
    prefs[colClass] = isVisible;
    localStorage.setItem('multiVoucherColPrefs', JSON.stringify(prefs));
}

function loadColumnPrefs() {
    const prefs = JSON.parse(localStorage.getItem('multiVoucherColPrefs') || '{}');
    const menu = document.getElementById('column-context-menu');
    if (menu) {
        const checkboxes = menu.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => {
            if (prefs[cb.value] === true) {
                cb.checked = true;
                toggleColumn(cb.value, true);
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadColumnPrefs();
    
    // Right click on table header
    const tableHeader = document.getElementById('table-header');
    const contextMenu = document.getElementById('column-context-menu');
    
    if (tableHeader && contextMenu) {
        tableHeader.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.classList.add('active');
        });
    }

    // Click anywhere else to hide context menu
    document.addEventListener('click', (e) => {
        if (contextMenu && !contextMenu.contains(e.target) && e.target !== tableHeader && (!tableHeader || !tableHeader.contains(e.target))) {
            contextMenu.classList.remove('active');
        }
    });
});

/* --- Footer Actions --- */
function resetVoucher() {
    if (window.ConcurrencyLockManager) {
        window.ConcurrencyLockManager.releaseLock();
    }
    if (window.IdempotencyGuard) {
        window.IdempotencyGuard.regenerate();
        window.IdempotencyGuard.isSaving = false;
    }
    const mainAcc = document.getElementById('main-account');
    if (mainAcc) { mainAcc.value = ''; mainAcc.removeAttribute('data-id'); }
    const mainSearch = document.getElementById('main-account-search');
    if (mainSearch) mainSearch.value = '';
    const refEl = document.getElementById('voucher-ref');
    if (refEl) refEl.value = '';
    const descEl = document.getElementById('voucher-desc');
    if (descEl) descEl.value = '';
    
    // Clear blocks
    const container = document.getElementById('accounts-blocks-container');
    if (container) container.innerHTML = '';
    blockCounter = 0;
    if (typeof window.addAccountBlock === 'function') window.addAccountBlock();
    
    if (typeof initPaymentContainers === 'function') initPaymentContainers();
    calcTotal();
    loadNextMultiVoucherNumber();
    formDirty = false;
    
    // Re-enable save button
    const saveBtn = document.getElementById('btn-save');
    if (saveBtn) { 
        saveBtn.disabled = false; 
        saveBtn.style.opacity = '1'; 
        saveBtn.style.pointerEvents = 'auto'; 
    }
    if (typeof window.syncWAAutoDispatchUI === 'function') {
        window.syncWAAutoDispatchUI();
    }
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('multi-voucher-user-audit-container', {}, true);
    }
}

window.printVoucher = function() {
    // Extract Voucher Document info
    const voucherTypeSel = document.getElementById('voucher-type');
    let voucherType = 'حركة مالية';
    if (voucherTypeSel) {
        const val = voucherTypeSel.value;
        voucherType = (val === 'payment') ? 'حركة صرف' : (val === 'receipt' ? 'حركة قبض' : 'حركة مالية');
    }
    const voucherNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || '').trim();
    const voucherDate = document.getElementById('voucher-date') ? document.getElementById('voucher-date').value : '';
    
    // Extract Main Account
    const mainAccountSearchEl = document.getElementById('main-account-search');
    const mainAccount = mainAccountSearchEl ? mainAccountSearchEl.value : '';
    
    // Extract Description, Currency, Rate, Totals
    const voucherDesc = document.getElementById('voucher-desc') ? document.getElementById('voucher-desc').value : '';
    const currencySel = document.getElementById('currency');
    const currency = currencySel ? (currencySel.options[currencySel.selectedIndex]?.text || currencySel.value) : '';
    const exchangeRate = document.getElementById('exchange-rate') ? document.getElementById('exchange-rate').value : '1.0';
    const amountLocal = document.getElementById('amount-local-hdr') ? document.getElementById('amount-local-hdr').value : '0.00';
    const amountForeign = document.getElementById('amount-foreign') ? document.getElementById('amount-foreign').value : '0.00';

    // Retrieve User and Company Info
    let userName = 'مدير النظام';
    let tenantId = 'default';
    const userStr = localStorage.getItem('erp_currentUser') || localStorage.getItem('user');
    if (userStr) {
        try {
            const u = JSON.parse(userStr);
            userName = u.fullName || u.username || userName;
            if (u.tenant_id) tenantId = u.tenant_id;
        } catch(e) {}
    }
    
    const prefix = 'tenant_' + tenantId + '_';
    let cfg = {};
    try {
        cfg = JSON.parse(localStorage.getItem(prefix + 'erp_settings_v1') || '{}');
    } catch(e) {}
    
    const companyLogo = cfg.logoBase64 || '';
    const companyName = cfg.companyName || 'اسم الشركة';

    // Current print date and time
    const printDateTime = new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit'});

    // Build Table Rows from entryItems or fallback to DOM blocks
    let entryItems = [];
    if (typeof currentVoucherIndex !== 'undefined' && currentVoucherIndex >= 0 && typeof currentVoucherList !== 'undefined' && currentVoucherList[currentVoucherIndex] && currentVoucherList[currentVoucherIndex].items) {
        entryItems = currentVoucherList[currentVoucherIndex].items;
    } else {
        const blocks = document.querySelectorAll('.account-block');
        blocks.forEach(block => {
            const bId = block.getAttribute('data-block-id');
            const searchInput = document.getElementById(`account-${bId}-search`);
            const accName = searchInput ? searchInput.value.trim() : '';
            const amtVal = parseFloat(cleanNum(document.getElementById(`amount-${bId}`)?.value || 0)) || 0;
            const stmtVal = document.getElementById(`statement-${bId}`)?.value || '';
            const ccVal = document.getElementById(`cost-center-${bId}`)?.value || '';
            if (accName || amtVal > 0) {
                entryItems.push({
                    accountName: accName,
                    amount: amtVal,
                    statement: stmtVal,
                    costCenter: ccVal,
                    paymentMethod: 'hybrid'
                });
            }
        });
    }

    let tableHtml = `
        <table class="print-table">
            <thead>
                <tr>
                    <th>م</th>
                    <th>الحساب / الجهة المستفيدة</th>
                    <th>المبلغ</th>
                    <th>البيان التفصيلي</th>
                    <th>مركز التكلفة / المشروع</th>
                    <th>طريقة الدفع</th>
                </tr>
            </thead>
            <tbody>
    `;
    let totalAmt = 0;
    if (entryItems.length > 0) {
        entryItems.forEach((item, idx) => {
            totalAmt += (parseFloat(item.amount) || 0);
            const pmLabel = item.paymentMethod === 'cash' ? 'نقدي' :
                            item.paymentMethod === 'bank' ? 'تحويل بنكي' :
                            item.paymentMethod === 'cheque' ? 'شيك' : 'متعدد';
            const ccLabel = item.costCenter || item.costCenterName || '-';
            tableHtml += `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${item.accountName || item.account || '-'}</td>
                    <td style="font-weight: bold; color: #1e293b;">${fmtNum(item.amount)}</td>
                    <td>${item.statement || ''}</td>
                    <td style="color: #0284c7; font-weight: 600;">${ccLabel}</td>
                    <td>${pmLabel}</td>
                </tr>
            `;
        });
    }
    tableHtml += `
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: left; font-weight: bold;">الإجمالي:</td>
                    <td style="font-weight: bold; color: #059669; font-size: 1.1rem;">${totalAmt.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    `;

    // Build Payment Methods Summary
    const payMethodRadio = document.querySelector('input[name="payment_method"]:checked');
    const payMethodVal = payMethodRadio ? payMethodRadio.value : 'cash';
    
    let payMethodLabel = 'نقدي';
    if (payMethodVal === 'check') payMethodLabel = 'شيك';
    else if (payMethodVal === 'mobile') payMethodLabel = 'تحويل بنكي';
    else if (payMethodVal === 'hybrid') payMethodLabel = 'متعدد (تحويل وشيك)';
    
    let paymentHtml = `<div class="payment-section"><h3 class="section-title">طريقة الدفع: ${payMethodLabel}</h3>`;
    
    if (payMethodVal === 'cash') {
        const cashAmt = document.getElementById('cash-amount') ? document.getElementById('cash-amount').value : '';
        paymentHtml += `<p style="font-size: 1.1rem;"><strong>المبلغ النقدي:</strong> <span style="font-family: monospace; font-weight: bold; color: #059669;">${cashAmt}</span></p>`;
    } else if (payMethodVal === 'mobile') {
        paymentHtml += `<table class="print-table" style="margin-top:10px;">
            <thead><tr><th>المبلغ</th><th>رقم التحويل</th><th>البيان</th></tr></thead><tbody>`;
        const transfers = document.querySelectorAll('.transfer-row');
        transfers.forEach(tr => {
            const tAmt = tr.querySelector('.transfer-amount')?.value || '';
            const tNo = tr.querySelector('.transfer-no')?.value || '';
            const tNote = tr.querySelector('.transfer-note')?.value || '';
            paymentHtml += `<tr><td style="font-weight: bold;">${tAmt}</td><td>${tNo}</td><td>${tNote}</td></tr>`;
        });
        paymentHtml += `</tbody></table>`;
    } else if (payMethodVal === 'check') {
        paymentHtml += `<table class="print-table" style="margin-top:10px;">
            <thead><tr><th>المبلغ</th><th>اسم البنك</th><th>رقم الشيك</th><th>تاريخ الاستحقاق</th></tr></thead><tbody>`;
        const cheques = document.querySelectorAll('.cheque-row');
        cheques.forEach(cr => {
            const cAmt = cr.querySelector('.cheque-amount')?.value || '';
            const cBranch = cr.querySelector('.cheque-branch')?.value || '';
            const cNo = cr.querySelector('.cheque-no')?.value || '';
            const cDate = cr.querySelector('.cheque-date')?.value || '';
            paymentHtml += `<tr><td style="font-weight: bold;">${cAmt}</td><td>${cBranch}</td><td>${cNo}</td><td dir="ltr">${cDate}</td></tr>`;
        });
        paymentHtml += `</tbody></table>`;
    } else if (payMethodVal === 'hybrid') {
        paymentHtml += `<h4>التحويلات البنكية</h4>`;
        paymentHtml += `<table class="print-table" style="margin-top:10px; margin-bottom: 20px;">
            <thead><tr><th>المبلغ</th><th>رقم التحويل</th><th>البيان</th></tr></thead><tbody>`;
        const transfers = document.querySelectorAll('.transfer-row');
        transfers.forEach(tr => {
            const tAmt = tr.querySelector('.transfer-amount')?.value || '';
            const tNo = tr.querySelector('.transfer-no')?.value || '';
            const tNote = tr.querySelector('.transfer-note')?.value || '';
            paymentHtml += `<tr><td style="font-weight: bold;">${tAmt}</td><td>${tNo}</td><td>${tNote}</td></tr>`;
        });
        paymentHtml += `</tbody></table>`;
        
        paymentHtml += `<h4>الشيكات</h4>`;
        paymentHtml += `<table class="print-table" style="margin-top:10px;">
            <thead><tr><th>المبلغ</th><th>اسم البنك</th><th>رقم الشيك</th><th>تاريخ الاستحقاق</th></tr></thead><tbody>`;
        const cheques = document.querySelectorAll('.cheque-row');
        cheques.forEach(cr => {
            const cAmt = cr.querySelector('.cheque-amount')?.value || '';
            const cBranch = cr.querySelector('.cheque-branch')?.value || '';
            const cNo = cr.querySelector('.cheque-no')?.value || '';
            const cDate = cr.querySelector('.cheque-date')?.value || '';
            paymentHtml += `<tr><td style="font-weight: bold;">${cAmt}</td><td>${cBranch}</td><td>${cNo}</td><td dir="ltr">${cDate}</td></tr>`;
        });
        paymentHtml += `</tbody></table>`;
    }
    paymentHtml += `</div>`;

    const logoHtml = companyLogo ? `<img src="${companyLogo}" alt="Logo" style="max-height: 80px; max-width: 150px; object-fit: contain;">` : `<div style="width: 80px; height: 80px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-weight: bold;">شعار الشركة</div>`;

    const html = `
        <style>
            .print-container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                color: #1e293b;
            }
            .header-flex {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                border-bottom: 2px solid #0ea5e9;
                padding-bottom: 15px;
                margin-bottom: 25px;
            }
            .company-info h1 {
                margin: 0 0 5px 0;
                font-size: 1.5rem;
                color: #0ea5e9;
            }
            .voucher-title {
                text-align: center;
                background: rgba(14, 165, 233, 0.1);
                color: #0ea5e9;
                padding: 8px 18px;
                border-radius: 6px;
                font-size: 1.4rem;
                font-weight: 800;
                margin: 0;
            }
            .meta-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-bottom: 25px;
                background: #f1f5f9;
                padding: 15px;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
            }
            .meta-item {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .meta-label {
                font-size: 0.85rem;
                color: #64748b;
                font-weight: 700;
            }
            .meta-val {
                font-size: 1rem;
                font-weight: bold;
                color: #1e293b;
            }
            .highlight-val {
                color: #10b981;
                font-family: monospace;
                font-size: 1.15rem;
            }
            .print-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
            }
            .print-table th, .print-table td {
                padding: 10px 12px;
                border: 1px solid #cbd5e1;
                text-align: right;
                font-size: 0.95rem;
            }
            .print-table th {
                background: #f8fafc;
                font-weight: 800;
                color: #1e293b;
            }
            .print-table tbody tr:nth-child(even) {
                background: #fdfdfd;
            }
            .section-title {
                border-bottom: 2px solid #e2e8f0;
                padding-bottom: 6px;
                margin-bottom: 12px;
                color: #0ea5e9;
                font-size: 1.1rem;
            }
            .signatures {
                display: flex;
                justify-content: space-between;
                margin-top: 40px;
                padding-top: 20px;
            }
            .sig-box {
                text-align: center;
                width: 200px;
            }
            .sig-line {
                border-bottom: 1px dashed #64748b;
                margin-bottom: 8px;
                height: 35px;
            }
        </style>
        <div class="print-container">
            <div class="header-flex">
                <div class="company-info">
                    <h1>${companyName}</h1>
                    <div style="color: #64748b; font-size: 0.85rem;">تاريخ ووقت الطباعة: ${printDateTime}</div>
                </div>
                <div>${logoHtml}</div>
            </div>

            <div style="display: flex; justify-content: center; margin-top: -50px; margin-bottom: 25px;">
                <h2 class="voucher-title">${voucherType} مجمعة</h2>
            </div>

            <div class="meta-grid">
                <div class="meta-item">
                    <span class="meta-label">رقم المستند</span>
                    <span class="meta-val">${voucherNo || '-'}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">تاريخ المستند</span>
                    <span class="meta-val" dir="ltr">${voucherDate}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">حساب الاستلاف / الدفع (الرئيسي)</span>
                    <span class="meta-val">${mainAccount}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">المستخدم / الموظف</span>
                    <span class="meta-val">${userName}</span>
                </div>
                <div class="meta-item" style="grid-column: 1 / -1;">
                    <span class="meta-label">البيان العام / الوصف</span>
                    <span class="meta-val">${voucherDesc || '---'}</span>
                </div>
            </div>

            <div class="meta-grid" style="background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2);">
                <div class="meta-item">
                    <span class="meta-label">العملة والتعادل</span>
                    <span class="meta-val">${currency} (سعر الصرف: ${exchangeRate})</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label" style="color: #10b981;">الإجمالي بالمحلي</span>
                    <span class="meta-val highlight-val">${amountLocal}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label" style="color: #ef4444;">الإجمالي بالأجنبي</span>
                    <span class="meta-val highlight-val" style="color: #ef4444;">${amountForeign}</span>
                </div>
            </div>

            <h3 class="section-title">تفاصيل الحسابات المستفيدة</h3>
            ${tableHtml}

            ${paymentHtml}

            <div class="signatures">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="meta-label">توقيع المستلم</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="meta-label">توقيع المحاسب / ${userName}</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="meta-label">الاعتماد / المدير</div>
                </div>
            </div>

            <!-- User Tracking & Accountability Print Footer -->
            <div style="margin-top: 15px; border-top: 1px dashed #cbd5e1; padding-top: 6px; font-size: 0.72rem; color: #475569; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; background: #f8fafc; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                <span>👤 المستخدم (معد الحركة): <strong>${(typeof currentVoucherList !== 'undefined' && typeof currentVoucherIndex !== 'undefined' && currentVoucherIndex >= 0 && currentVoucherList[currentVoucherIndex]) ? (currentVoucherList[currentVoucherIndex].createdByName || currentVoucherList[currentVoucherIndex].createdByUsername || currentVoucherList[currentVoucherIndex].createdBy || userName) : userName}</strong>${(typeof currentVoucherList !== 'undefined' && typeof currentVoucherIndex !== 'undefined' && currentVoucherIndex >= 0 && currentVoucherList[currentVoucherIndex] && currentVoucherList[currentVoucherIndex].updatedByName) ? ' | عُدلت بواسطة: <strong>' + currentVoucherList[currentVoucherIndex].updatedByName + '</strong>' : ''} | طُبع بواسطة: <strong>${userName}</strong> | وقت الطباعة: ${printDateTime}</span>
                <span style="font-weight: 800; color: #0284c7;">نظام مقاييس المحاسبي الموحد ⚡</span>
            </div>
        </div>
    `;

    const bodyEl = document.getElementById('preview-modal-body');
    const modalEl = document.getElementById('print-preview-modal');
    if (bodyEl && modalEl) {
        bodyEl.innerHTML = html;
        modalEl.style.display = 'flex';
    } else {
        alert('حدث خطأ في عرض نافذة المعاينة المنبثقة.');
    }
}

window.closePrintPreviewModal = function() {
    const modalEl = document.getElementById('print-preview-modal');
    if (modalEl) {
        modalEl.style.display = 'none';
    }
};

async function confirmDelete() {
    const voucherNoEl = document.getElementById('voucher-no-display');
    const voucherNo = voucherNoEl ? voucherNoEl.value : '';
    if (!voucherNo) {
        showToast('لا يوجد مستند لحذفه.', 'error');
        return;
    }
    if (!confirm('⚠️ هل أنت متأكد من رغبتك في حذف هذا المستند نهائياً؟\nرقم المستند: ' + voucherNo + '\n\nلا يمكن التراجع عن هذا الإجراء!')) return;
    
    const type = document.getElementById('voucher-type').value;
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    
    try {
        let existingData = getStoredVouchers(storageKey);
        
        const idx = existingData.findIndex(v => v && (v.voucherNo === voucherNo || v.id === voucherNo) && v.isGrouped === true);
        if (idx > -1) {
            // Revert balance effects before deleting
            if (typeof applyGroupedVoucherEffect === 'function') {
                applyGroupedVoucherEffect(existingData[idx], true);
            }
            existingData.splice(idx, 1);
            saveStoredVouchers(storageKey, existingData);
            if (window.rebuildGLFromAllTables) {
                try { window.rebuildGLFromAllTables(false); } catch(e) {}
            }
            if (window.logSystemAction) {
                const actionType = type === 'payment' ? 'حذف حركة دفع متعددة' : 'حذف استلام متعدد';
                window.logSystemAction(actionType, 'المعاملات المالية', voucherNo, `تم حذف المستند رقم ${voucherNo}`);
            }
            showToast('تم حذف المستند بنجاح وعكس تأثيره المحاسبي!', 'success');
        } else {
            showToast('المستند غير محفوظ بعد، تم تفريغ الحقول.', 'info');
        }
        resetVoucher();
    } catch (e) {
        console.error('Delete error:', e);
        showToast('حدث خطأ أثناء الحذف!', 'error');
    }
}

async function saveVoucher() {
    try {
        await _saveVoucherCore();
    } catch (e) {
        console.error('Unhandled Save error:', e);
        if (window.IdempotencyGuard) window.IdempotencyGuard.isSaving = false;
        showToast('حدث خطأ فادح أثناء الحفظ! يرجى المحاولة مرة أخرى.', 'error');
        const saveBtn = document.getElementById('btn-save');
        if (saveBtn) { saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.pointerEvents = 'auto'; }
    }
}
async function _saveVoucherCore() {
    // 1. Enterprise Double-click protection & Idempotency guard
    if (window.IdempotencyGuard && window.IdempotencyGuard.isSaving) {
        console.warn('Double click blocked by IdempotencyGuard');
        return;
    }

    const saveBtn = document.getElementById('btn-save');
    if (saveBtn && saveBtn.disabled) return;

    if (window.IdempotencyGuard) window.IdempotencyGuard.isSaving = true;

    const originalBtnHTML = saveBtn ? saveBtn.innerHTML : '';
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.style.opacity = '0.75';
        saveBtn.style.pointerEvents = 'none';
        saveBtn.innerHTML = `
            <span style="display:inline-flex; align-items:center; gap:8px;">
                <span style="display:inline-block; animation:spin 1s linear infinite;">🔄</span>
                <span>جاري الحفظ والاعتماد بأمان... 🔒</span>
            </span>
        `;
    }

    // Helper to re-enable save button on validation errors
    function reEnableSave() {
        if (window.IdempotencyGuard) window.IdempotencyGuard.isSaving = false;
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
            saveBtn.style.pointerEvents = 'auto';
            saveBtn.innerHTML = originalBtnHTML;
        }
    }
    // Helper to highlight field with red border and shake
    function highlightError(el) {
        if (!el) return;
        el.style.border = '2px solid #ef4444';
        el.style.boxShadow = '0 0 12px rgba(239,68,68,0.5)';
        if (typeof shakeElement === 'function') shakeElement(el);
        setTimeout(() => { el.style.border = ''; el.style.boxShadow = ''; }, 4000);
    }

    const type = document.getElementById('voucher-type').value;
    const date = document.getElementById('voucher-date').value;
    const mainAccount = document.getElementById('main-account').getAttribute('data-id') || document.getElementById('main-account').value;
    const desc = document.getElementById('voucher-desc').value.trim();
    const ref = document.getElementById('voucher-ref') ? document.getElementById('voucher-ref').value.trim() : '';
    const currency = (document.getElementById('currency') ? document.getElementById('currency').value : 'SDG') || 'SDG';
    const exchangeRate = cleanNum(document.getElementById('exchange-rate') ? document.getElementById('exchange-rate').value : 0) || (typeof window.getSystemUsdRate === 'function' ? window.getSystemUsdRate() : 1.0);
    
    const blocks = document.querySelectorAll('.account-block');
    let items = [];
    let totalAmt = 0;
    const seenTransfers = new Set(); // Prevent duplicates within the same voucher

    for (let block of blocks) {
        const id = block.getAttribute('data-block-id');
        const accountSearch = document.getElementById(`account-${id}-search`);
        const accountName = accountSearch ? accountSearch.value : '';
        const accountId = document.getElementById(`account-${id}-id`) ? document.getElementById(`account-${id}-id`).value : (document.getElementById(`account-${id}`) ? document.getElementById(`account-${id}`).value : accountName);
        const amount = parseFloat(cleanNum(document.getElementById(`amount-${id}`).value)) || 0;
        const statement = document.getElementById(`statement-${id}`) ? document.getElementById(`statement-${id}`).value : '';
        const ccInput = document.getElementById(`cost-center-${id}`);
        const ccIdInput = document.getElementById(`cost-center-${id}-id`);
        const costCenter = ccInput ? ccInput.value.trim() : '';
        const costCenterId = (ccIdInput && ccIdInput.value) ? ccIdInput.value.trim() : costCenter;
        
        if (!accountName || (!accountId && !accountName)) { 
            showToast('يرجى تحديد جميع الحسابات', 'error'); 
            if(accountSearch) highlightError(accountSearch);
            reEnableSave(); 
            return; 
        }
        if (amount <= 0) { 
            showToast('المبلغ يجب أن يكون أكبر من الصفر', 'error'); 
            highlightError(document.getElementById(`amount-${id}`));
            reEnableSave(); 
            return; 
        }
        
        const btnCash = document.getElementById(`pm-cash-${id}`);
        const btnBank = document.getElementById(`pm-bank-${id}`);
        const btnCheque = document.getElementById(`pm-cheque-${id}`);
        
        const isCash = btnCash && btnCash.classList.contains('active');
        const isBank = btnBank && btnBank.classList.contains('active');
        const isCheque = btnCheque && btnCheque.classList.contains('active');
        
        const isDirectTransfer = !isCash && !isBank && !isCheque;
        // If not using Cash, Bank, or Cheque, it's considered a direct Account-to-Account transfer.
        
        // Collect cash data
        const cashAmtEl = block.querySelector('.block-cash-amount');
        const cashSafeEl = block.querySelector('.block-cash-safe');
        const cashDescEl = block.querySelector('.block-cash-desc-input');
        const cashAmt = (isCash && cashAmtEl) ? (parseFloat(cleanNum(cashAmtEl.value)) || 0) : 0;
        const cashSafe = (isCash && cashSafeEl) ? cashSafeEl.value : '';
        const cashDesc = (isCash && cashDescEl) ? cashDescEl.value.trim() : '';
        
        // Collect ALL bank transfer rows
        let bankTransfers = [];
        let bankTotalAmt = 0;
        if (isBank) {
            const bankRows = block.querySelectorAll('.bank-row');
            let hasInvalidTransfer = false;
            
            for (let i = 0; i < bankRows.length; i++) {
                const row = bankRows[i];
                const amt = parseFloat(cleanNum(row.querySelector('.block-bank-amount')?.value)) || 0;
                const name = row.querySelector('.block-bank-name-input')?.value || '';
                const tno = row.querySelector('.block-transfer-no-input')?.value || '';
                const desc = row.querySelector('.block-bank-desc-input')?.value || '';
                
                if (amt > 0) {
                    if (!tno) {
                        showCenterError(`الحساب ${accountName} - السطر ${i + 1}:<br><br>يرجى إدخال رقم حساب التحويل البنكي!`, 'empty');
                        highlightError(row.querySelector('.block-transfer-no-input'));
                        hasInvalidTransfer = true;
                        break;
                    }
                    const cleanDigits = String(tno).replace(/\D/g, '');
                    if (cleanDigits.length < 6) {
                        showCenterError(`رقم التحويل البنكي للحساب ( ${accountName} - السطر ${i + 1} ) غير صالح!<br><br>يحتوي على ${cleanDigits.length} أرقام فقط. يشترط النظام 6 أرقام على الأقل، ويمنع الحفظ نهائياً لأي تحويلة أقل من 6 أرقام.`, 'short');
                        highlightError(row.querySelector('.block-transfer-no-input'));
                        hasInvalidTransfer = true;
                        break;
                    }
                    
                    const targetSuffix = cleanDigits.slice(-6);
                    if (seenTransfers.has(targetSuffix)) {
                        showCenterError(`رقم التحويل ( ${tno} ) للحساب ( ${accountName} - السطر ${i + 1} ) مكرر داخل نفس الشاشة!<br><br>يوجد تطابق في آخر 6 أرقام (${targetSuffix}) مع تحويلة أخرى أدخلتها الآن.`, 'duplicate');
                        highlightError(row.querySelector('.block-transfer-no-input'));
                        hasInvalidTransfer = true;
                        break;
                    }
                    seenTransfers.add(targetSuffix);
                    
                    const cvNo = document.getElementById('voucher-no-display') ? document.getElementById('voucher-no-display').value : '';
                    const isUnique = await window.checkTransferDuplicate(tno, cvNo);
                    if (!isUnique) {
                        showCenterError(`رقم التحويل ( ${tno} ) للحساب ( ${accountName} - السطر ${i + 1} ) مستخدم من قبل!<br><br>يوجد تطابق في آخر 6 أرقام (${targetSuffix}) مع سند آخر مسجل مسبقاً في النظام. يمنع النظام تكرار آخر 6 أرقام نهائياً.`, 'duplicate');
                        highlightError(row.querySelector('.block-transfer-no-input'));
                        hasInvalidTransfer = true;
                        break;
                    }
                    const currentVoucherNo = document.getElementById('voucher-no-display')?.value || '';
                    bankTotalAmt += amt;
                    bankTransfers.push({ amount: amt, bankName: name, transferNo: tno, statement: desc });
                }
            }
            
            if (hasInvalidTransfer) {
                reEnableSave();
                return;
            }
        }
        
        // Collect ALL cheque rows
        let cheques = [];
        let chequeTotalAmt = 0;
        if (isCheque) {
            const chequeRows = block.querySelectorAll('.cheque-row');
            let hasInvalidCheque = false;
            
            for (let i = 0; i < chequeRows.length; i++) {
                const row = chequeRows[i];
                const amt = parseFloat(cleanNum(row.querySelector('.block-cheque-amount')?.value)) || 0;
                const bank = row.querySelector('.block-cheque-bank-input')?.value || '';
                const no = row.querySelector('.block-cheque-no-input')?.value || '';
                const dt = row.querySelector('.block-cheque-date-input')?.value || '';
                const desc = row.querySelector('.block-cheque-desc-input')?.value || '';
                
                if (amt > 0) {
                    if (!bank) {
                        showCenterError(`الحساب ${accountName} - السطر ${i + 1}:<br><br>يرجى إدخال اسم البنك الخاص بالشيك!`, 'empty');
                        highlightError(row.querySelector('.block-cheque-bank-input'));
                        hasInvalidCheque = true;
                        break;
                    }
                    if (!no) {
                        showCenterError(`الحساب ${accountName} - السطر ${i + 1}:<br><br>يرجى إدخال رقم الشيك!`, 'empty');
                        highlightError(row.querySelector('.block-cheque-no-input'));
                        hasInvalidCheque = true;
                        break;
                    }
                    const currentVoucherNo = document.getElementById('voucher-no-display')?.value || '';
                    chequeTotalAmt += amt;
                    cheques.push({ amount: amt, chequeBank: bank, chequeNo: no, chequeDate: dt, statement: desc });
                }
            }
            
            if (hasInvalidCheque) {
                reEnableSave();
                return;
            }
        }

        // VALIDATE SUM
        const subSum = cashAmt + bankTotalAmt + chequeTotalAmt;
        if (!isDirectTransfer && Math.abs(subSum - amount) > 0.01) {
            showToast(`مجموع مبالغ الدفع للحساب ${accountName} لا يساوي الإجمالي (${fmtNum(amount)})`, 'error'); 
            reEnableSave(); 
            return;
        }
        
        let activeMethods = [];
        if(isCash) activeMethods.push('cash');
        if(isBank) activeMethods.push('bank');
        if(isCheque) activeMethods.push('cheque');

        items.push({
            account: accountId, 
            accountName: accountName,
            accountId: accountId, 
            amount: amount, 
            statement: statement,
            costCenter: costCenter,
            costCenterId: costCenterId,
            costCenterName: costCenter,
            paymentMethods: isDirectTransfer ? ['direct'] : activeMethods,
            paymentMethod: isDirectTransfer ? 'direct' : (activeMethods.length > 0 ? activeMethods[0] : 'cash'),
            cashAmount: cashAmt, 
            cashSafe: cashSafe, 
            cashStatement: cashDesc,
            bankAmount: bankTotalAmt, 
            bankTransfers: bankTransfers,
            chequeAmount: chequeTotalAmt,
            cheques: cheques,
            // Legacy single fields (first transfer/cheque for backward compatibility)
            bankName: bankTransfers.length > 0 ? bankTransfers[0].bankName : '',
            transferNo: bankTransfers.length > 0 ? bankTransfers[0].transferNo : '',
            chequeBank: cheques.length > 0 ? cheques[0].chequeBank : '',
            chequeNo: cheques.length > 0 ? cheques[0].chequeNo : '',
            chequeDate: cheques.length > 0 ? cheques[0].chequeDate : '',
            chequeDueDate: cheques.length > 0 ? cheques[0].chequeDate : ''
        });
        totalAmt += amount;
    }
    
    if (items.length === 0) {
        showToast('يرجى إضافة حساب واحد على الأقل', 'error');
        reEnableSave(); 
        return;
    }
    const finalTotal = totalAmt;
    paymentMethod = 'hybrid';

    // Build clean voucher number
    const displayVal = (document.getElementById('voucher-no-display') ? document.getElementById('voucher-no-display').value : '').trim();
    let vNo = displayVal;
    if (!vNo || vNo.startsWith('MV-') || vNo.startsWith('MV17')) {
        const yymm = (date || '').replace(/-/g, '').substring(2, 6) || '2608';
        vNo = 'M' + yymm + String(Math.floor(Math.random() * 8999) + 1000);
        if (document.getElementById('voucher-no-display')) {
            document.getElementById('voucher-no-display').value = vNo;
        }
    }

    // Enterprise Idempotency Verification with server to prevent microsecond double commits
    if (window.IdempotencyGuard) {
        try {
            const currentKey = window.IdempotencyGuard.getKey();
            const fd = new FormData();
            fd.append('action', 'verify_idempotency');
            fd.append('idempotency_key', currentKey);
            fd.append('voucher_no', vNo);
            fd.append('user_name', window.ConcurrencyLockManager ? window.ConcurrencyLockManager.getUserName() : 'محاسب');

            const chkResp = await fetch('multi-cash-payment.php', { method: 'POST', body: fd });
            const chkData = await chkResp.json();
            if (chkData && chkData.duplicate) {
                showToast('⚠️ تنبيه: تم استلام هذا السند مسبقاً وتأمينه ضد التكرار اللحظي (Idempotency Protection)', 'warning');
                reEnableSave();
                return;
            }
        } catch(e) {
            console.warn('Idempotency check network warning:', e);
        }
    }

    // Determine storage key based on type
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    let existingData = getStoredVouchers(storageKey);
    const existingIdx = existingData.findIndex(v => (v && ((v.voucherNo === vNo || v.id === vNo) || (window._activeInboundReceiptId && v.inboundReceiptId && String(v.inboundReceiptId) === String(window._activeInboundReceiptId))) && v.isGrouped));
    const oldVoucher = (existingIdx > -1) ? existingData[existingIdx] : null;
    if (oldVoucher && oldVoucher.voucherNo) vNo = oldVoucher.voucherNo;
    const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };

    // Build the voucher object
    const voucher = {
        id: vNo, // Clean sequence ID matching voucherNo
        voucherNo: vNo,
        type: type, // 'payment' or 'receipt'
        isGrouped: true,
        mainAccount: mainAccount,
        date: date,
        time: new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
        description: desc,
        totalAmount: finalTotal,
        amountLocal: (currency === 'SDG') ? finalTotal : (finalTotal * exchangeRate),
        foreignAmount: (currency === 'SDG') ? (exchangeRate > 0 ? (finalTotal / exchangeRate) : 0) : finalTotal,
        amountForeign: (currency === 'SDG') ? (exchangeRate > 0 ? (finalTotal / exchangeRate) : 0) : finalTotal,
        currency: currency,
        exchangeRate: exchangeRate,
        paymentMethod: 'hybrid',
        checkNo: '',
        checkBranch: '',
        checkDate: '',
        checkDueDate: '',
        mobileTransferNo: '',
        mobileNote: '',
        bankTransfers: [],
        cheques: [],
        items: items, // Contains {account, amount, statement, paymentMethod, bankName, ...}
        isPosted: true, // Auto-post to reflect in general ledger immediately
        createdAt: oldVoucher?.createdAt || new Date().toISOString(),
        idempotencyKey: (window.IdempotencyGuard ? window.IdempotencyGuard.getKey() : null),
        inboundReceiptId: window._activeInboundReceiptId || oldVoucher?.inboundReceiptId || null,
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1,
        createdBy: oldVoucher?.createdBy || curUser.id,
        createdByName: oldVoucher?.createdByName || curUser.fullName || curUser.name,
        createdByUsername: oldVoucher?.createdByUsername || curUser.username,
        updatedBy: oldVoucher ? curUser.id : (oldVoucher?.updatedBy || null),
        updatedByName: oldVoucher ? (curUser.fullName || curUser.name) : (oldVoucher?.updatedByName || null),
        updatedAt: oldVoucher ? new Date().toISOString() : (oldVoucher?.updatedAt || null)
    };

    // Set fromAccount and toAccount properly for compatibility
    if (type === 'payment') {
        voucher.fromAccount = mainAccount; // Source of funds
        voucher.toAccount = 'حسابات فرعية متعددة';
        voucher.notes = desc;
    } else {
        voucher.toAccount = mainAccount; // Destination of funds
        voucher.fromAccount = 'حسابات فرعية متعددة';
        voucher.notes = desc;
    }
    
    try {
        // Revert old voucher balance effects first if editing an existing voucher
        if (existingIdx > -1 && oldVoucher && typeof applyGroupedVoucherEffect === 'function') {
            applyGroupedVoucherEffect(oldVoucher, true);
        }

        // Update existing record if editing, otherwise insert
        if (existingIdx > -1) {
            existingData[existingIdx] = voucher;
        } else {
            existingData.push(voucher);
        }

        // SAVE TO BOTH TENANT KEY AND BASE KEY
        saveStoredVouchers(storageKey, existingData);

        // Apply dynamic account balance effects BEFORE pushing to cloud
        if (typeof applyGroupedVoucherEffect === 'function') {
            applyGroupedVoucherEffect(voucher, false);
        }

        // Rebuild GL immediately so General Ledger reflects new voucher BEFORE pushing to cloud
        if (window.rebuildGLFromAllTables) {
            try { window.rebuildGLFromAllTables(false); } catch(e) {}
        }

        if (typeof window.pushToCloud === 'function') {
            window.pushToCloud(true);
        }
        try {
            if (!navigator.onLine && window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                window.MagaiesOffline.enqueue('finance', voucher, vNo);
            }
        } catch (eOff) {}

        // If this voucher is reconciling an inbound bank receipt, mark it verified/reconciled on the backend
        if (window._activeInboundReceiptId) {
            const activeReceiptId = window._activeInboundReceiptId;
            window._activeInboundReceiptId = null;
            fetch('api/inbound_receipts.php?action=reconcile_receipt', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    receipt_id: activeReceiptId,
                    voucher_no: vNo
                })
            }).then(() => {
                if (window.InboundBankakDrawer && typeof window.InboundBankakDrawer.fetchReceipts === 'function') {
                    window.InboundBankakDrawer.fetchReceipts();
                }
            }).catch(() => {});
        }

        if (window.logSystemAction) {
            const isEdit = (existingIdx > -1);
            const isPay = (type === 'payment');
            const actionType = isEdit ? (isPay ? 'تعديل حركة دفع متعددة' : 'تعديل استلام متعدد') : (isPay ? 'حركة دفع متعددة' : 'استلام متعدد');
            window.logSystemAction(actionType, 'المعاملات المالية', vNo, `${actionType} رقم ${vNo} بمبلغ ${finalTotal} ${currency}`);
        }
        if (window.renderDocumentUserBadge) {
            window.renderDocumentUserBadge('multi-voucher-user-audit-container', voucher, false);
        }
        
        // Clear dirty flag so user doesn't get unsaved changes warning on exit
        if (typeof formDirty !== 'undefined') formDirty = false;

        const mainAccName = document.getElementById('main-account-search') ? document.getElementById('main-account-search').value : 'الحساب الرئيسي';
        const subNames = voucher.items.map(i => i.accountName).join(' و ');
                const isEdit = (existingIdx > -1);
        const isPay = (type === 'payment');
        let actionText = isPay ? 'تم صرف مبلغ' : 'تم استلام مبلغ';
        let flowColor = isPay ? '#ef4444' : '#10b981';
        let headerIcon = '✅';
        let btnText = '✅ تم الحفظ بنجاح!';
        let btnTextFinal = '✅ تم الحفظ بنجاح';
        let btnColor = 'linear-gradient(135deg, #10b981 0%, #34d399 100%)';
        let btnShadow1 = '0 0 30px rgba(16,185,129,0.6)';
        let btnShadow2 = '0 0 15px rgba(16,185,129,0.4)';

        if (isEdit) {
            actionText = 'تم تعديل البيانات بمبلغ';
            flowColor = '#ef4444'; // Red
            headerIcon = '📝';
            btnText = '📝 تم التعديل بنجاح!';
            btnTextFinal = '📝 تم تعديل البيانات';
            btnColor = 'linear-gradient(135deg, #ef4444 0%, #f87171 100%)';
            btnShadow1 = '0 0 30px rgba(239,68,68,0.6)';
            btnShadow2 = '0 0 15px rgba(239,68,68,0.4)';
        }

        const autoSendActive = (typeof window.getWAAutoDispatchSettings === 'function' && window.getWAAutoDispatchSettings().enabled);
        if (autoSendActive) {
            btnText = isEdit ? '📝 تم التعديل والإرسال! ⚡' : '✅ تم الحفظ والإرسال! ⚡';
            btnTextFinal = isEdit ? '📝 تم التعديل والإرسال ⚡' : '✅ تم الحفظ والإرسال ⚡';
        }
        
        const fromText = isPay ? mainAccName : subNames;
        const toText = isPay ? subNames : mainAccName;
        const currencyName = document.getElementById('currency') ? document.getElementById('currency').options[document.getElementById('currency').selectedIndex].text : '';
        
        // Build Animated Modal
        const modal = document.createElement('div');
        modal.innerHTML = `
            <div style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(10px); z-index: 99999; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.4s ease; direction: rtl;" id="success-flow-modal">
                <div style="background: linear-gradient(145deg, #1e293b, #0f172a); border: 2px solid ${flowColor}; border-radius: 20px; padding: 40px; max-width: 600px; width: 90%; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.6), 0 0 40px ${flowColor}40; transform: scale(0.8) translateY(30px); transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);">
                    <div style="font-size: 3.5rem; margin-bottom: 10px; animation: bounceIn 0.6s ease-out;">${headerIcon}</div>
                    <h2 style="color: #fff; font-family: 'Tajawal', sans-serif; margin-bottom: 25px; font-size: 1.8rem;">
                        ${actionText} <span style="color: #fbbf24; font-weight: 900;">${parseFloat(voucher.totalAmount).toLocaleString()}</span> <span style="font-size: 1.2rem; color: #cbd5e1;">${currencyName}</span>
                    </h2>
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.05); padding: 25px 20px; border-radius: 15px; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                        <!-- Right side (From) -->
                        <div style="flex: 1; text-align: right; z-index: 2;">
                            <div style="font-size: 0.9rem; color: #94a3b8; margin-bottom: 8px;">من حساب:</div>
                            <div style="color: #fff; font-weight: 800; font-size: 1.2rem; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-shadow: 0 2px 4px rgba(0,0,0,0.5);" title="${fromText}">${fromText}</div>
                        </div>
                        
                        <!-- Middle (Animation) -->
                        <div style="width: 120px; height: 3px; background: rgba(255,255,255,0.1); position: relative; flex-shrink: 0; margin: 0 20px; border-radius: 3px;">
                            <div style="position: absolute; top: -20px; right: 0; font-size: 2rem; animation: moneyFlow 1.5s infinite linear;">💸</div>
                        </div>
                        
                        <!-- Left side (To) -->
                        <div style="flex: 1; text-align: left; z-index: 2;">
                            <div style="font-size: 0.9rem; color: #94a3b8; margin-bottom: 8px;">إلى حساب:</div>
                            <div style="color: #fff; font-weight: 800; font-size: 1.2rem; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-shadow: 0 2px 4px rgba(0,0,0,0.5);" title="${toText}">${toText}</div>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                @keyframes moneyFlow {
                    0% { transform: translateX(20px) scale(0.5); opacity: 0; }
                    15% { opacity: 1; transform: translateX(0px) scale(1.2); }
                    85% { opacity: 1; transform: translateX(-80px) scale(1.2); }
                    100% { transform: translateX(-100px) scale(0.5); opacity: 0; }
                }
                @keyframes bounceIn {
                    0% { transform: scale(0); }
                    60% { transform: scale(1.2); }
                    100% { transform: scale(1); }
                }
            </style>
        `;
        document.body.appendChild(modal);
        
        // Trigger animation
        requestAnimationFrame(() => {
            const el = document.getElementById('success-flow-modal');
            if (el) {
                el.style.opacity = '1';
                el.children[0].style.transform = 'scale(1) translateY(0)';
            }
        });
        
        if (typeof playClickSound === 'function') playClickSound();
        
        // ═══ Trigger Smart WhatsApp Auto-Dispatch if enabled ═══
        try {
            if (typeof window.triggerAutoWADispatchOnSave === 'function') {
                window.triggerAutoWADispatchOnSave(voucher, vNo);
            }
        } catch(autoWaErr) {
            console.warn('Auto WhatsApp dispatch warning:', autoWaErr);
        }
        
        // Save success button animation
                if (saveBtn) {
            saveBtn.innerHTML = btnText;
            saveBtn.style.background = btnColor;
            saveBtn.style.transform = 'scale(1.05)';
            saveBtn.style.boxShadow = btnShadow1;
        }

        // Hide modal and reset form after 3 seconds
        setTimeout(() => {
            const el = document.getElementById('success-flow-modal');
            if (el) {
                el.style.opacity = '0';
                el.children[0].style.transform = 'scale(0.9) translateY(-20px)';
                setTimeout(() => modal.remove(), 500);
            }
            
                        if (saveBtn) {
                saveBtn.innerHTML = btnTextFinal;
                saveBtn.style.transform = 'scale(1)';
                saveBtn.style.boxShadow = btnShadow2;
                saveBtn.style.background = '';
                saveBtn.disabled = true;
                saveBtn.style.opacity = '1';
                saveBtn.style.pointerEvents = 'none';
            }
            
            // Release saving state and regenerate key for next action
            if (window.IdempotencyGuard) {
                window.IdempotencyGuard.regenerate();
                window.IdempotencyGuard.isSaving = false;
            }

            // Acquire / refresh lock for the newly saved voucher
            if (window.ConcurrencyLockManager) {
                window.ConcurrencyLockManager.checkAndAcquireLock(vNo, type);
            }

            // Refresh navigation list and keep the current saved voucher on screen
            refreshVoucherNavigationList().then(() => {
                currentVoucherIndex = currentVoucherList.length - 1;
            });
            
        }, 3200);

    } catch (e) {
        console.error('Save error:', e);
        if (window.IdempotencyGuard) window.IdempotencyGuard.isSaving = false;
        showToast('حدث خطأ أثناء الحفظ!', 'error');
        if (saveBtn) { saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.pointerEvents = 'auto'; }
    }
}


// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Accounts
// ═══════════════════════════════════════════════════════

let ACCOUNTS = [];
function buildAccountsArray() {
    ACCOUNTS = [];
    const getTenantKey = window.getTenantKey || ((k) => k);
    const stores = [
        { key: 'erp_customers_v1', prefix: 'cust-' },
        { key: 'erp_suppliers_v1', prefix: 'supp-' },
        { key: 'erp_banks_v1', prefix: 'bank-' },
        { key: 'erp_safes_v1', prefix: 'safe-' },
        { key: 'erp_accounts_v1', prefix: 'exp-' }
    ];

    for (const store of stores) {
        const tKey = getTenantKey(store.key);
        let data = JSON.parse(localStorage.getItem(tKey) || localStorage.getItem(store.key) || '[]');
        if (!Array.isArray(data)) data = Object.values(data);
        if (store.key === 'erp_suppliers_v1' && (!data || data.length === 0)) {
            data = [
        { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', currentBalance: 0, creditLimit: 50000000 },
        { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', currentBalance: 0, creditLimit: 30000000 },
        { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', currentBalance: 0, creditLimit: 20000000 },
        { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', currentBalance: 0, creditLimit: 100000000 },
        { id: 'SUP-005', code: 'SUP-005', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', currentBalance: 0, creditLimit: 15000000 },
        { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', currentBalance: 0, creditLimit: 25000000 },
        { id: 'SUP-007', code: 'SUP-007', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', currentBalance: 0, creditLimit: 40000000 }
    ];
            try { localStorage.setItem(tKey, JSON.stringify(data)); localStorage.setItem(store.key, JSON.stringify(data)); } catch(e){}
        }
        
        data.forEach(item => {
            if (item.deleted) return;
            const name = item.name || item.accountName || item.customerName || item.supplierName || item.bankName || item.safeName;
            const id = item.id || item.code || '';
            if (name) {
                ACCOUNTS.push({
                    id: store.prefix + id,
                    name: name,
                    rawId: id,
                    receiptCodeNum: item.receiptCodeNum ? String(item.receiptCodeNum).trim() : '',
                    receiptCodeWord: item.receiptCodeWord ? String(item.receiptCodeWord).trim() : '',
                    phone: item.whatsapp || item.phone || ''
                });
            }
        });
    }
}

// Hook into DBReady to build ACCOUNTS and COST_CENTERS
document.addEventListener('DBReady', () => {
    buildAccountsArray();
    buildCostCentersArray();
});

function renderAccountDropdown(type, filter = '') {
    if (ACCOUNTS.length === 0) buildAccountsArray();

    const listEl = document.getElementById(type + '-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const cleanCodeTerm = term.replace(/^#/, '');
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
    
    try {
        let filtered = ACCOUNTS.filter(a => {
            if (!term) return true;
            const normalizedName = window.normalizeArabic ? window.normalizeArabic(a.name) : a.name.toLowerCase();
            const codeNum = String(a.receiptCodeNum || '').toLowerCase();
            const codeWord = window.normalizeArabic ? window.normalizeArabic(a.receiptCodeWord || '') : String(a.receiptCodeWord || '').toLowerCase();
            const combinedCode1 = `${codeNum} ${codeWord}`.trim();
            const combinedCode2 = `${codeWord} ${codeNum}`.trim();
            return normalizedName.includes(normalizedTerm)
                || String(a.id).toLowerCase().includes(term)
                || (codeNum && (codeNum === cleanCodeTerm || codeNum.includes(cleanCodeTerm)))
                || (codeWord && codeWord.includes(normalizedTerm))
                || (combinedCode1 && combinedCode1.includes(normalizedTerm))
                || (combinedCode2 && combinedCode2.includes(normalizedTerm));
        });

        const totalFound = filtered.length;
        filtered = filtered.slice(0, 50);

        if (filtered.length === 0) {
            listEl.innerHTML = `<div style="padding: 15px; color: var(--text-secondary); text-align: center;">لا توجد حسابات مطابقة</div>`;
            return;
        }

        filtered.forEach(a => {
            const div = document.createElement('div');
            div.className = 'custom-dropdown-item';
            div.dataset.id = a.id;
            div.dataset.name = a.name;
            div.style.padding = '12px 16px';
            div.style.cursor = 'pointer';
            div.style.borderBottom = '1px solid rgba(255, 255, 255, 0.05)';
            div.style.transition = 'background 0.2s';
            
            div.onmouseenter = () => div.style.background = 'rgba(255, 255, 255, 0.1)';
            div.onmouseleave = () => div.style.background = 'transparent';
            
            let typeTag = '';
            if (String(a.id).startsWith('cust-')) typeTag = '<span style="font-size:0.8rem; background:rgba(96,165,250,0.15); color:#60a5fa; padding: 4px 12px; border-radius: 20px; font-weight: bold; min-width: 60px; text-align: center; box-shadow: inset 0 0 5px rgba(96,165,250,0.3);">عميل</span>';
            else if (String(a.id).startsWith('supp-')) typeTag = '<span style="font-size:0.8rem; background:rgba(244,114,182,0.15); color:#f472b6; padding: 4px 12px; border-radius: 20px; font-weight: bold; min-width: 60px; text-align: center; box-shadow: inset 0 0 5px rgba(244,114,182,0.3);">مورد</span>';
            else if (String(a.id).startsWith('bank-')) typeTag = '<span style="font-size:0.8rem; background:rgba(167,139,250,0.15); color:#a78bfa; padding: 4px 12px; border-radius: 20px; font-weight: bold; min-width: 60px; text-align: center; box-shadow: inset 0 0 5px rgba(167,139,250,0.3);">بنك</span>';
            else if (String(a.id).startsWith('safe-')) typeTag = '<span style="font-size:0.8rem; background:rgba(52,211,153,0.15); color:#34d399; padding: 4px 12px; border-radius: 20px; font-weight: bold; min-width: 60px; text-align: center; box-shadow: inset 0 0 5px rgba(52,211,153,0.3);">خزينة</span>';
            else if (String(a.id).startsWith('exp-')) typeTag = '<span style="font-size:0.8rem; background:rgba(251,191,36,0.15); color:#fbbf24; padding: 4px 12px; border-radius: 20px; font-weight: bold; min-width: 60px; text-align: center; box-shadow: inset 0 0 5px rgba(251,191,36,0.3);">مصروف</span>';

            const highlightedName = window.highlightSearchMatches ? window.highlightSearchMatches(a.name, filter) : a.name;
            const codeBadges = [];
            if (a.receiptCodeNum) codeBadges.push(`<span style="background:rgba(16,185,129,0.2); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:1px 7px; border-radius:6px; font-size:0.78rem; font-family:monospace; font-weight:900;">#${a.receiptCodeNum}</span>`);
            if (a.receiptCodeWord) codeBadges.push(`<span style="background:rgba(245,158,11,0.2); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); padding:1px 7px; border-radius:6px; font-size:0.78rem; font-weight:800;">🏷️ ${a.receiptCodeWord}</span>`);

            div.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-weight: bold; color: #fff;">${highlightedName}</span>
                        ${codeBadges.join('')}
                    </div>
                    ${typeTag}
                </div>
            `;
            div.onmousedown = (e) => {
                e.stopPropagation();
                selectAccount(type, a.id, a.name);
            };
            div.onpointerdown = (e) => {
                e.stopPropagation();
                selectAccount(type, a.id, a.name);
            };
            listEl.appendChild(div);
        });

        if (totalFound > 50) {
            const note = document.createElement('div');
            note.style.cssText = 'padding: 8px; font-size: 0.8rem; color: #fbbf24; text-align: center; background: rgba(251,191,36,0.1);';
            note.innerText = `عرض أول 50 حساب من أصل ${totalFound}. يرجى تضييق نطاق البحث.`;
            listEl.appendChild(note);
        }

    } catch (e) {
        console.error('Error rendering accounts:', e);
    }
}

window.showAccountDropdown = function(type) {
    const listEl = document.getElementById(type + '-dropdown-list');
    if (listEl) {
        const searchEl = document.getElementById(type + '-search');
        const filterVal = searchEl ? searchEl.value : '';
        renderAccountDropdown(type, filterVal);
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

window.hideAccountDropdownDelayed = function(type) {
    setTimeout(() => {
        const listEl = document.getElementById(type + '-dropdown-list');
        if (listEl) {
            listEl.classList.remove('show');
            listEl.style.display = 'none';
        }
    }, 200);
}

window.filterAccountDropdown = function(type) {
    const searchEl = document.getElementById(type + '-search');
    const val = searchEl ? searchEl.value : '';
    renderAccountDropdown(type, val);
}

window.selectAccount = function(type, id, name) {
    const hiddenInp = document.getElementById(type);
    if(hiddenInp) {
        hiddenInp.value = name;
        hiddenInp.setAttribute('data-id', id);
    }
    const hiddenIdInp = document.getElementById(type + '-id');
    if (hiddenIdInp) {
        hiddenIdInp.value = id;
    }
    const searchEl = document.getElementById(type + '-search');
    if (searchEl) searchEl.value = name;

    const listEl = document.getElementById(type + '-dropdown-list');
    if(listEl) {
        listEl.classList.remove('show');
        listEl.style.display = 'none';
    }
    
    if (type === 'main-account') {
        setTimeout(() => {
            const firstAccount = document.querySelector('.block-account-search');
            if (firstAccount) {
                firstAccount.focus();
                firstAccount.select();
                const blockId = firstAccount.id.replace('account-search-', '');
                if (typeof showAccountDropdown === 'function') {
                    showAccountDropdown('account-' + blockId);
                }
            }
        }, 50);
    }

    if (type.startsWith('account-')) {
        const blockId = type.replace('account-', '');
        const block = document.getElementById('block-' + blockId);
        if (block) {
            const btnBank = block.querySelector('.block-btn-bank');
            if (btnBank && !btnBank.classList.contains('active')) {
                if (typeof window.toggleBlockPaymentMethod === 'function') {
                    window.toggleBlockPaymentMethod(blockId, 'bank');
                }
            }
            setTimeout(() => {
                const amtEl = block.querySelector('.block-bank-amount');
                if (amtEl) { amtEl.focus(); amtEl.select(); }
            }, 100);
        }
    }
};

if (!window.normalizeArabic) {
    window.normalizeArabic = function(text) {
        if (!text) return '';
        return text.toString()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .toLowerCase();
    };
}

// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Analytical Cost Centers & Projects
// ═══════════════════════════════════════════════════════

let COST_CENTERS = [];
function buildCostCentersArray() {
    COST_CENTERS = [];
    const getTenantKey = window.getTenantKey || ((k) => k);

    let serverCC = window.SERVER_COST_CENTERS || [];
    if (!Array.isArray(serverCC)) {
        try { serverCC = JSON.parse(serverCC); } catch(e) { serverCC = []; }
    }

    let localCC = [];
    try {
        const tKey = getTenantKey('erp_cost_centers_v1');
        localCC = JSON.parse(localStorage.getItem(tKey) || localStorage.getItem('erp_cost_centers_v1') || localStorage.getItem('erp_jobs_v1') || '[]');
    } catch(e) { localCC = []; }
    if (!Array.isArray(localCC)) localCC = Object.values(localCC);

    const map = new Map();
    const defaults = [
        { id: 'cc-main', name: 'الفرع الرئيسي / الإدارة العامة', tag: 'فرع' },
        { id: 'cc-bahri', name: 'فرع بحري', tag: 'فرع' },
        { id: 'cc-omdurman', name: 'فرع أمدرمان', tag: 'فرع' }
    ];

    [...defaults, ...localCC, ...serverCC].forEach(item => {
        if (!item || item.deleted) return;
        const name = String(item.name || item.costCenterName || '').trim();
        const id = String(item.id || item.code || name).trim();
        if (name && id) {
            let tag = item.tag || 'مركز تكلفة';
            const low = name.toLowerCase();
            if (low.includes('مشروع')) tag = 'مشروع';
            else if (low.includes('فرع')) tag = 'فرع';
            else if (low.includes('شاحنة') || low.includes('عربة') || low.includes('سيارة')) tag = 'شاحنة';
            map.set(id, { id: id, name: name, desc: item.desc || '', tag: tag });
        }
    });

    COST_CENTERS = Array.from(map.values());
    return COST_CENTERS;
}

window.showCostCenterDropdown = function(blockId) {
    if (COST_CENTERS.length === 0) buildCostCentersArray();
    const input = document.getElementById(`cost-center-${blockId}`);
    const filter = input ? input.value : '';
    renderCostCenterDropdown(blockId, filter);
    const list = document.getElementById(`cost-center-${blockId}-dropdown-list`);
    if (list) {
        list.classList.add('show');
        list.style.display = 'block';
    }
};

window.hideCostCenterDropdownDelayed = function(blockId) {
    setTimeout(() => {
        const list = document.getElementById(`cost-center-${blockId}-dropdown-list`);
        if (list) {
            list.classList.remove('show');
            list.style.display = 'none';
        }
        
        // Auto-assign custom typed value if not already assigned with ID
        const input = document.getElementById(`cost-center-${blockId}`);
        const idInput = document.getElementById(`cost-center-${blockId}-id`);
        if (input && idInput) {
            const val = input.value.trim();
            if (val && !idInput.value) {
                const found = COST_CENTERS.find(c => c.name.toLowerCase() === val.toLowerCase());
                if (found) {
                    idInput.value = found.id;
                } else {
                    idInput.value = 'CC-' + val;
                }
            } else if (!val) {
                idInput.value = '';
            }
        }
    }, 250);
};

window.filterCostCenterDropdown = function(blockId) {
    const input = document.getElementById(`cost-center-${blockId}`);
    const filter = input ? input.value : '';
    const idInput = document.getElementById(`cost-center-${blockId}-id`);
    if (idInput) idInput.value = ''; // Reset ID when user modifies text
    renderCostCenterDropdown(blockId, filter);
};

window.selectCostCenter = function(blockId, id, name) {
    const input = document.getElementById(`cost-center-${blockId}`);
    const idInput = document.getElementById(`cost-center-${blockId}-id`);
    const list = document.getElementById(`cost-center-${blockId}-dropdown-list`);
    if (input) input.value = name;
    if (idInput) idInput.value = id;
    if (list) {
        list.classList.remove('show');
        list.style.display = 'none';
    }
};

window.saveNewCostCenterQuick = async function(blockId, newName) {
    const cleanName = String(newName || '').trim();
    if (!cleanName) return;
    
    const newId = 'CC-' + Date.now();
    let tag = 'مركز تكلفة';
    if (cleanName.includes('مشروع')) tag = 'مشروع';
    else if (cleanName.includes('فرع')) tag = 'فرع';
    else if (cleanName.includes('شاحنة') || cleanName.includes('سيارة')) tag = 'شاحنة';

    const newItem = { id: newId, name: cleanName, desc: '', tag: tag, createdAt: Date.now() };
    COST_CENTERS.push(newItem);

    // Save to localStorage
    const getTenantKey = window.getTenantKey || ((k) => k);
    const tKey = getTenantKey('erp_cost_centers_v1');
    try {
        let existing = JSON.parse(localStorage.getItem(tKey) || localStorage.getItem('erp_cost_centers_v1') || '[]');
        if (!Array.isArray(existing)) existing = Object.values(existing);
        existing.push(newItem);
        localStorage.setItem(tKey, JSON.stringify(existing));
        localStorage.setItem('erp_cost_centers_v1', JSON.stringify(existing));
        localStorage.setItem('erp_jobs_v1', JSON.stringify(existing));
    } catch(e) {}

    // Sync to backend
    try {
        const fd = new FormData();
        fd.append('type', 'cost_centers');
        fd.append('data', JSON.stringify(COST_CENTERS));
        fetch('api/save_master_data.php', { method: 'POST', body: fd }).catch(() => {});
    } catch(e) {}

    // Select it
    selectCostCenter(blockId, newId, cleanName);
    if (typeof showToast === 'function') {
        showToast(`✨ تم ربط وحفظ "${cleanName}" كمركز تكلفة/مشروع دائم في النظام!`, 'success');
    }
};

function renderCostCenterDropdown(blockId, filter = '') {
    if (COST_CENTERS.length === 0) buildCostCentersArray();
    const listEl = document.getElementById(`cost-center-${blockId}-dropdown-list`);
    if (!listEl) return;

    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;

    // 1. Clear / General option
    const clearDiv = document.createElement('div');
    clearDiv.className = 'custom-dropdown-item';
    clearDiv.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid rgba(255, 255, 255, 0.08); color: #94a3b8; font-size: 0.88rem; display: flex; align-items: center; justify-content: space-between;';
    clearDiv.innerHTML = `<span>-- بدون مركز تكلفة (عام) --</span> <span style="font-size:0.75rem; color:#64748b;">افتراضي</span>`;
    clearDiv.onmousedown = (e) => {
        e.stopPropagation();
        selectCostCenter(blockId, '', '');
    };
    clearDiv.onpointerdown = (e) => {
        e.stopPropagation();
        selectCostCenter(blockId, '', '');
    };
    listEl.appendChild(clearDiv);

    // 2. Filter existing cost centers
    let filtered = COST_CENTERS.filter(c => {
        if (!term) return true;
        const normName = window.normalizeArabic ? window.normalizeArabic(c.name) : c.name.toLowerCase();
        return normName.includes(normalizedTerm) || String(c.id).toLowerCase().includes(term);
    });

    filtered.slice(0, 30).forEach(c => {
        const div = document.createElement('div');
        div.className = 'custom-dropdown-item';
        div.dataset.id = c.id;
        div.dataset.name = c.name;
        div.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;';

        div.onmouseenter = () => div.style.background = 'rgba(56, 189, 248, 0.15)';
        div.onmouseleave = () => div.style.background = 'transparent';

        let tagBadge = '<span style="font-size:0.75rem; background:rgba(56,189,248,0.15); color:#38bdf8; padding:2px 8px; border-radius:10px; font-weight:700;">🏷️ مركز</span>';
        if (c.tag === 'مشروع') tagBadge = '<span style="font-size:0.75rem; background:rgba(251,191,36,0.15); color:#fbbf24; padding:2px 8px; border-radius:10px; font-weight:700;">🏗️ مشروع</span>';
        else if (c.tag === 'فرع') tagBadge = '<span style="font-size:0.75rem; background:rgba(52,211,153,0.15); color:#34d399; padding:2px 8px; border-radius:10px; font-weight:700;">🏢 فرع</span>';
        else if (c.tag === 'شاحنة') tagBadge = '<span style="font-size:0.75rem; background:rgba(244,114,182,0.15); color:#f472b6; padding:2px 8px; border-radius:10px; font-weight:700;">🚛 شاحنة</span>';

        const highName = window.highlightSearchMatches ? window.highlightSearchMatches(c.name, filter) : c.name;
        div.innerHTML = `
            <div style="font-weight:700; color:#ffffff; font-size:0.92rem; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">${highName}</div>
            ${tagBadge}
        `;
        div.onmousedown = (e) => {
            e.stopPropagation();
            selectCostCenter(blockId, c.id, c.name);
        };
        div.onpointerdown = (e) => {
            e.stopPropagation();
            selectCostCenter(blockId, c.id, c.name);
        };
        listEl.appendChild(div);
    });

    // 3. Quick-Add option if text is entered and not an exact match
    if (term) {
        const exactMatch = COST_CENTERS.some(c => c.name.toLowerCase() === term);
        if (!exactMatch) {
            const addDiv = document.createElement('div');
            addDiv.className = 'custom-dropdown-item';
            addDiv.style.cssText = 'padding: 9px 12px; cursor: pointer; background: rgba(16, 185, 129, 0.12); border-top: 1px dashed rgba(52, 211, 153, 0.4); color: #34d399; font-weight: bold; font-size: 0.88rem; transition: background 0.2s;';
            addDiv.innerHTML = `<span>➕ استخدام وحفظ "<strong>${filter}</strong>" كمركز تكلفة دائم</span>`;
            addDiv.onmouseenter = () => addDiv.style.background = 'rgba(16, 185, 129, 0.25)';
            addDiv.onmouseleave = () => addDiv.style.background = 'rgba(16, 185, 129, 0.12)';
            addDiv.onmousedown = (e) => {
                e.stopPropagation();
                saveNewCostCenterQuick(blockId, filter);
            };
            addDiv.onpointerdown = (e) => {
                e.stopPropagation();
                saveNewCostCenterQuick(blockId, filter);
            };
            listEl.appendChild(addDiv);
        }
    }
}

async function loadNextMultiVoucherNumber() {
    const type = document.getElementById('voucher-type') ? document.getElementById('voucher-type').value : 'payment';
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    let existingData = getStoredVouchers(storageKey);
    
    // Base prefix on selected voucher date, not current system date
    const dateEl = document.getElementById('voucher-date');
    const selectedDate = (dateEl && dateEl.value) ? new Date(dateEl.value) : new Date();
    const validDate = isNaN(selectedDate.getTime()) ? new Date() : selectedDate;
    const yy = String(validDate.getFullYear()).substring(2, 4);
    const mm = String(validDate.getMonth() + 1).padStart(2, '0');
    const datePrefix = yy + mm;
    const prefix = 'M';
    
    let max = 0;
    const groupedVouchers = existingData.filter(v => v.isGrouped);
    groupedVouchers.forEach(v => {
        const vNo = String(v.voucherNo || v.id || '');
        const matchPrefix = prefix + datePrefix;
        if (vNo.startsWith(matchPrefix)) {
            const seqStr = vNo.substring(matchPrefix.length);
            const seq = parseInt(seqStr, 10);
            if (!isNaN(seq) && seq > max) {
                max = seq;
            }
        }
    });
    
    const padNum = String(max + 1).padStart(4, '0');
    const displayEl = document.getElementById('voucher-no-display');
    if (displayEl) {
        displayEl.value = `${prefix}${datePrefix}${padNum}`;
    }
}

async function refreshVoucherNavigationList() {
    const type = document.getElementById('voucher-type') ? document.getElementById('voucher-type').value : 'payment';
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    let existingData = getStoredVouchers(storageKey);
    
    currentVoucherList = existingData.filter(v => v.isGrouped);
    currentVoucherList.sort((a, b) => {
        const da = a.date + ' ' + (a.createdAt || '');
        const db = b.date + ' ' + (b.createdAt || '');
        return da.localeCompare(db);
    });
}

async function navigateVoucher(direction) {
    await refreshVoucherNavigationList();
    if (currentVoucherList.length === 0) {
        showToast('لا توجد سندات محفوظة للملاحة.', 'info');
        return;
    }
    
    if (currentVoucherIndex === -1) {
        if (direction === -1) {
            currentVoucherIndex = currentVoucherList.length - 1;
        } else {
            showToast('لا توجد سندات تالية، أنت على مسودة جديدة.', 'info');
            return;
        }
    } else {
        currentVoucherIndex += direction;
        if (currentVoucherIndex < 0) {
            currentVoucherIndex = 0;
            showToast('هذا هو أول سند محفوظ.', 'info');
            return;
        }
        if (currentVoucherIndex >= currentVoucherList.length) {
            currentVoucherIndex = -1;
            resetVoucher();
            return;
        }
    }
    
    const v = currentVoucherList[currentVoucherIndex];
    if (v) {
        loadVoucherIntoForm(v);
    }
}

function loadVoucherIntoForm(v) {
    // 1. Set type (but avoid infinite loop/index resets)
    const btnPayment = document.getElementById('btn-payment');
    const btnReceipt = document.getElementById('btn-receipt');
    const typeInput = document.getElementById('voucher-type');
    if (typeInput) typeInput.value = v.type;
    
    const pmRow = document.getElementById('payment-methods-row');
    if (v.type === 'payment') {
        if (btnPayment) btnPayment.className = 'voucher-type-btn active-payment';
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'linear-gradient(135deg, rgba(75, 14, 25, 0.45), rgba(15, 23, 42, 0.95))';
            pmRow.style.borderColor = 'rgba(159, 18, 57, 0.4)';
        }
    } else {
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn active-receipt';
        if (btnPayment) btnPayment.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'linear-gradient(135deg, rgba(13, 84, 64, 0.45), rgba(15, 23, 42, 0.95))';
            pmRow.style.borderColor = 'rgba(16, 185, 129, 0.4)';
        }
    }
    updateLabels();

    // 2. Set fields
    let vid = v.voucherNo || v.id || v.paymentNo || v.receiptNo || v.invoiceNo || '';
    if ((!vid || vid.startsWith('MV-') || vid.startsWith('MV17')) && v.id && !v.id.startsWith('MV-') && !v.id.startsWith('MV17')) {
        vid = v.id;
    }
    document.getElementById('voucher-no-display').value = vid;
    document.getElementById('voucher-date').value = v.date || '';
    document.getElementById('voucher-ref').value = vid || v.ref || '';
    document.getElementById('voucher-desc').value = v.description || '';

    // Real-Time Concurrency Lock & Idempotency Key Refresh on voucher load
    if (window.ConcurrencyLockManager && vid) {
        window.ConcurrencyLockManager.checkAndAcquireLock(vid, v.type || 'payment');
    }
    if (window.IdempotencyGuard) {
        window.IdempotencyGuard.regenerate();
        window.IdempotencyGuard.isSaving = false;
    }
    
    const currencyEl = document.getElementById('currency');
    const currency = v.currency || 'SDG';
    if (currencyEl) currencyEl.value = currency;
    
    const rateEl = document.getElementById('exchange-rate');
    if (rateEl) {
        let r = parseFloat(v.exchangeRate);
        if (!r || r <= 1) {
            r = (typeof window.getSystemUsdRate === 'function') ? window.getSystemUsdRate() : 2600;
        }
        rateEl.value = Math.round(r);
    }
    if (typeof window.updateCurrencyUI === 'function') {
        window.updateCurrencyUI(currency);
    }
    
    // 3. Set main account
    const mainAccHidden = document.getElementById('main-account');
    if (mainAccHidden) {
        mainAccHidden.value = getAccountName(v.mainAccount) || '';
        mainAccHidden.setAttribute('data-id', v.mainAccount || '');
    }
    const mainAccSearch = document.getElementById('main-account-search');
    if (mainAccSearch) {
        mainAccSearch.value = getAccountName(v.mainAccount) || '';
    }
    
    // 4. Set payment method radio (fallback for old vouchers)
    const radioMethod = v.paymentMethod === 'hybrid' ? 'cash' : (v.paymentMethod || 'cash');
    const radio = document.querySelector(`input[name="payment_method"][value="${radioMethod}"]`);
    if (radio) {
        radio.checked = true;
        togglePaymentMethod();
    }
    
    if (typeof loadVoucherPaymentDetails === 'function') {
        loadVoucherPaymentDetails(v);
    }
    
    // 5. Populate blocks from saved items
    const container = document.getElementById('accounts-blocks-container');
    if (container) container.innerHTML = '';
    blockCounter = 0;
    
    if (v.items && Array.isArray(v.items)) {
        v.items.forEach(item => {
            if (typeof window.addAccountBlock === 'function') {
                window.addAccountBlock();
            }
            
            const blocks = document.querySelectorAll('.account-block');
            if (blocks.length === 0) return;
            const lastBlock = blocks[blocks.length - 1];
            const blockId = lastBlock.getAttribute('data-block-id');
            
            const accName = item.accountName || getAccountName(item.account) || item.account || '';
            const accSearch = document.getElementById(`account-${blockId}-search`);
            if (accSearch) accSearch.value = accName;
            
            const accIdEl = document.getElementById(`account-${blockId}-id`);
            if (accIdEl) accIdEl.value = item.accountId || item.account || '';
            
            const amtEl = document.getElementById(`amount-${blockId}`);
            if (amtEl) amtEl.value = item.amount || '';
            
            const stmtEl = document.getElementById(`statement-${blockId}`);
            if (stmtEl) stmtEl.value = item.statement || '';
            
            const ccEl = document.getElementById(`cost-center-${blockId}`);
            const ccIdEl = document.getElementById(`cost-center-${blockId}-id`);
            const ccVal = item.costCenter || item.costCenterName || '';
            if (ccEl) ccEl.value = ccVal;
            if (ccIdEl) ccIdEl.value = item.costCenterId || ccVal;
            
            const pms = item.paymentMethods || [item.paymentMethod || 'cash'];
            ['cash', 'bank', 'cheque'].forEach(pm => {
                const btn = document.getElementById(`pm-${pm}-${blockId}`);
                const details = document.getElementById(`${pm}-details-${blockId}`);
                if (btn && details) {
                    if (pms.includes(pm)) {
                        btn.classList.add('active');
                        details.style.display = 'block';
                    } else {
                        btn.classList.remove('active');
                        details.style.display = 'none';
                    }
                }
            });
            
            if (pms.includes('cash')) {
                const cAmt = document.getElementById(`cash-amount-${blockId}`);
                if (cAmt) cAmt.value = item.cashAmount || item.amount || '';
                const cSafe = document.getElementById(`cash-safe-${blockId}`);
                if (cSafe) {
                    if (Array.from(cSafe.options).some(opt => opt.value === (item.cashSafe || ''))) {
                        cSafe.value = item.cashSafe || '';
                    } else if (item.cashSafe) {
                        const opt = document.createElement('option');
                        opt.value = item.cashSafe;
                        opt.textContent = item.cashSafe;
                        cSafe.appendChild(opt);
                        cSafe.value = item.cashSafe;
                    }
                }
                const cDesc = lastBlock.querySelector('.block-cash-desc-input');
                if (cDesc) cDesc.value = item.cashStatement || '';
            }
            if (pms.includes('bank')) {
                const bContainer = lastBlock.querySelector('.bank-rows-container');
                if (bContainer) {
                    bContainer.innerHTML = ''; // clear default row
                    const bts = item.bankTransfers || [{ amount: item.bankAmount || item.amount || 0, bankName: item.bankName || '', transferNo: item.transferNo || '', statement: item.bankStatement || '' }];
                    bts.forEach((bt, idx) => {
                        const row = document.createElement('div');
                        row.className = 'bank-row';
                        row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;';
                        row.innerHTML = `
                            <div style="width: 90px; font-weight: bold; color: #22d3ee; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">${idx === 0 ? '🏦 تحويل' : ''}</div>
                            <div style="width: 220px; flex-shrink: 0;">
                                <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" data-block-id="${blockId}" placeholder="المبلغ" value="${bt.amount || ''}" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
                            </div>
                            <div style="flex: 1; min-width: 120px;">
                                <input type="text" autocomplete="off" list="master-banks-datalist" class="form-input block-bank-name-input" placeholder="اسم البنك" value="${bt.bankName || ''}" style="margin-bottom: 0;">
                            </div>
                            <div style="flex: 1; min-width: 120px;">
                                <input type="text" autocomplete="off" class="form-input block-transfer-no-input" placeholder="رقم التحويلة" value="${bt.transferNo || ''}" style="margin-bottom: 0;">
                            </div>
                            <div style="flex: 1.5; min-width: 150px;">
                                <input type="text" autocomplete="off" class="form-input block-bank-desc-input" placeholder="بيان التحويل" value="${bt.statement || ''}" style="margin-bottom: 0;">
                            </div>
                            ${idx === 0 ? `
                            <button type="button" data-action="add-bank-row" data-block="${blockId}" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(6, 182, 212, 0.2); border: 1px solid #22d3ee; color: #22d3ee; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="إضافة تحويلة أخرى">➕</button>
                            ` : `
                            <button type="button" data-action="remove-bank-row" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #ef4444; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="حذف هذه التحويلة">✖</button>
                            `}
                        `;
                        bContainer.appendChild(row);
                    });
                }
            }
            if (pms.includes('cheque')) {
                const cContainer = lastBlock.querySelector('.cheque-rows-container');
                if (cContainer) {
                    cContainer.innerHTML = ''; // clear default row
                    const chs = item.cheques || [{ amount: item.chequeAmount || item.amount || 0, chequeBank: item.chequeBank || '', chequeNo: item.chequeNo || item.checkNo || '', chequeDate: item.chequeDate || item.checkDate || '', statement: item.chequeStatement || '' }];
                    chs.forEach((ch, idx) => {
                        const row = document.createElement('div');
                        row.className = 'cheque-row';
                        row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;';
                        row.innerHTML = `
                            <div style="width: 90px; font-weight: bold; color: #d8b4fe; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">${idx === 0 ? '✍️ شيك' : ''}</div>
                            <div style="width: 220px; flex-shrink: 0;">
                                <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" data-block-id="${blockId}" placeholder="المبلغ" value="${ch.amount || ''}" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
                            </div>
                            <div style="flex: 1; min-width: 90px;">
                                <input type="text" autocomplete="off" list="master-banks-datalist" class="form-input block-cheque-bank-input" placeholder="اسم البنك" value="${ch.chequeBank || ''}" style="margin-bottom: 0;">
                            </div>
                            <div style="flex: 1; min-width: 80px;">
                                <input type="text" autocomplete="off" class="form-input block-cheque-no-input" placeholder="رقم الشيك" value="${ch.chequeNo || ''}" style="margin-bottom: 0;">
                            </div>
                            <div style="flex: 1; min-width: 100px;">
                                <input type="date" class="form-input block-cheque-date-input" title="تاريخ الاستحقاق" value="${ch.chequeDate || ''}" style="margin-bottom: 0;">
                            </div>
                            <div style="flex: 1.5; min-width: 150px;">
                                <input type="text" autocomplete="off" class="form-input block-cheque-desc-input" placeholder="بيان الشيك" value="${ch.statement || ''}" style="margin-bottom: 0;">
                            </div>
                            ${idx === 0 ? `
                            <button type="button" data-action="add-cheque-row" data-block="${blockId}" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(147, 51, 234, 0.22); border: 1px solid #d8b4fe; color: #d8b4fe; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="إضافة شيك آخر">➕</button>
                            ` : `
                            <button type="button" data-action="remove-cheque-row" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #ef4444; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="حذف هذا الشيك">✖</button>
                            `}
                        `;
                        cContainer.appendChild(row);
                    });
                }
            }
        });
    }
    
    if (document.querySelectorAll('.account-block').length === 0) {
        if (typeof window.addAccountBlock === 'function') window.addAccountBlock();
    }
    
    document.querySelectorAll('.account-block').forEach(b => {
        const bid = b.getAttribute('data-block-id');
        if (typeof window.updateBlockTafqeetAndTier === 'function') {
            window.updateBlockTafqeetAndTier(bid);
        }
    });

    calcTotal();
    const saveBtn = document.getElementById('btn-save');
    if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.style.opacity = '1';
        saveBtn.style.pointerEvents = 'auto';
    }
    if (typeof window.syncWAAutoDispatchUI === 'function') {
        window.syncWAAutoDispatchUI();
    }
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('multi-voucher-user-audit-container', v, false);
    }
    showToast(`تم عرض السند ${v.voucherNo} بنجاح`, 'success');
}

function getAccountName(id) {
    if (!id) return '';
    if (!ACCOUNTS || ACCOUNTS.length === 0) {
        if (typeof window.buildAccountsArray === 'function') window.buildAccountsArray();
    }
    const cleanId = String(id).replace(/^(cust|supp|bank|safe|exp)-/, '');
    const matched = ACCOUNTS.find(a => String(a.id) === String(id) || String(a.rawId) === String(cleanId) || String(a.name) === String(id));
    if (matched) return matched.name;
    if (cleanId === 'safe-001' || id === 'safe-001') return 'الخزينة الرئيسية';
    if (cleanId === 'bank-001' || id === 'bank-001') return 'البنك الرئيسي';
    if (cleanId === 'bankok' || id === 'bankok') return 'بنكك (Bankak)';
    if (cleanId === 'fawry' || id === 'fawry') return 'فوري (Fawry)';
    if (cleanId === 'o-cash' || id === 'o-cash') return 'أوكاش (O-Cash)';
    if (cleanId === 'other' || id === 'other') return 'أخرى';
    return id;
}
function applyGroupedVoucherEffect(voucher, isRevert = false) {
    if (!voucher) return;
    const type = voucher.type; // 'payment' or 'receipt'
    const multiplier = isRevert ? -1 : 1;
    const getTenantKey = window.getTenantKey || ((k) => k);
    
    // Helper function to update balance in both tenant key and base key
    function saveEntityList(baseKey, list) {
        const str = JSON.stringify(list);
        try { localStorage.setItem(getTenantKey(baseKey), str); } catch(e){}
        try { localStorage.setItem(baseKey, str); } catch(e){}
    }
    function loadEntityList(baseKey) {
        try {
            return JSON.parse(localStorage.getItem(getTenantKey(baseKey)) || localStorage.getItem(baseKey) || '[]') || [];
        } catch(e) { return []; }
    }

    function applyBalanceChange(accId, amtEffect) {
        if (!accId || amtEffect === 0) return;
        accId = String(accId).trim();
        
        if (accId.startsWith('safe-')) {
            let id = accId.substring(5);
            let safes = loadEntityList('erp_safes_v1');
            let idx = safes.findIndex(s => s && String(s.id) === String(id));
            if (idx > -1) {
                safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtEffect;
                saveEntityList('erp_safes_v1', safes);
            }
        } else if (accId.startsWith('bank-')) {
            let id = accId.substring(5);
            let banks = loadEntityList('erp_banks_v1');
            let idx = banks.findIndex(b => b && String(b.id) === String(id));
            if (idx > -1) {
                banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtEffect;
                saveEntityList('erp_banks_v1', banks);
            }
        } else if (accId.startsWith('cust-')) {
            let id = accId.substring(5);
            let custs = loadEntityList('erp_customers_v1');
            let idx = custs.findIndex(c => c && String(c.id) === String(id));
            if (idx > -1) {
                custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amtEffect;
                saveEntityList('erp_customers_v1', custs);
            }
        } else if (accId.startsWith('supp-')) {
            let id = accId.substring(5);
            let sups = loadEntityList('erp_suppliers_v1');
            let idx = sups.findIndex(s => s && String(s.id) === String(id));
            if (idx > -1) {
                sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtEffect;
                saveEntityList('erp_suppliers_v1', sups);
            }
        } else if (accId.startsWith('exp-')) {
            let id = accId.substring(4);
            let accounts = loadEntityList('erp_accounts_v1');
            let idx = accounts.findIndex(a => a && String(a.id) === String(id));
            if (idx > -1) {
                accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amtEffect;
                saveEntityList('erp_accounts_v1', accounts);
            }
        } else {
            // Fallback: match by customer or bank name if ID has no prefix
            let custs = loadEntityList('erp_customers_v1');
            let cIdx = custs.findIndex(c => c && !c.deleted && (String(c.id) === accId || (c.name && c.name.trim() === accId)));
            if (cIdx > -1) {
                custs[cIdx].currentBalance = (parseFloat(custs[cIdx].currentBalance) || 0) + amtEffect;
                saveEntityList('erp_customers_v1', custs);
                return;
            }
            let banks = loadEntityList('erp_banks_v1');
            let bIdx = banks.findIndex(b => b && !b.deleted && (String(b.id) === accId || b.name === accId || b.bankName === accId));
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + amtEffect;
                saveEntityList('erp_banks_v1', banks);
            }
        }
    }

    const mainAccIdFallback = voucher.mainAccount || (type === 'payment' ? voucher.fromAccount : voucher.toAccount);

    // Update Items Accounts & Specific Payment Sources
    if (voucher.items && Array.isArray(voucher.items)) {
        voucher.items.forEach(item => {
            const itemAccId = item.account;
            if (!itemAccId) return;
            const itemAmt = (parseFloat(item.amount) || 0) * multiplier;
            
            // 1. Process Destination (Supplier/Customer/Expense)
            let destEffect = 0;
            if (type === 'payment') {
                if (itemAccId.startsWith('cust-')) destEffect = itemAmt; // Customer Debt Increases
                else if (itemAccId.startsWith('supp-')) destEffect = -itemAmt; // Supplier Liability Decreases
                else destEffect = itemAmt; // Expenses/Others Increase
            } else { // receipt
                if (itemAccId.startsWith('cust-')) destEffect = -itemAmt; // Customer Debt Decreases
                else if (itemAccId.startsWith('supp-')) destEffect = itemAmt; // Supplier Liability Increases
                else destEffect = -itemAmt; // Others Decrease
            }
            applyBalanceChange(itemAccId, destEffect);

            // 2. Process Sources (Cash/Bank/Cheque)
            // In Payment: source loses money (-)
            // In Receipt: source gains money (+)
            const sourceEffectMultiplier = (type === 'payment') ? -1 : 1;
            
            // Cash
            const cashAmt = (parseFloat(item.cashAmount) || 0) * multiplier;
            if (cashAmt > 0) {
                const sourceAcc = item.cashSafe || mainAccIdFallback;
                applyBalanceChange(sourceAcc, cashAmt * sourceEffectMultiplier);
            }
            
            // Bank Transfers
            const bankAmt = (parseFloat(item.bankAmount) || 0) * multiplier;
            if (bankAmt > 0) {
                let sourceAcc = mainAccIdFallback;
                // Try to find the specific bank by name
                if (item.bankTransfers && item.bankTransfers.length > 0) {
                    const bName = item.bankTransfers[0].bankName;
                    if (bName) {
                        let banks = [];
                        try { banks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1'))) || []; } catch(e){}
                        let foundBank = banks.find(b => (b.name === bName || b.bankName === bName));
                        if (foundBank) sourceAcc = 'bank-' + foundBank.id;
                    }
                }
                applyBalanceChange(sourceAcc, bankAmt * sourceEffectMultiplier);
            }
            
            // Cheques
            const chequeAmt = (parseFloat(item.chequeAmount) || 0) * multiplier;
            if (chequeAmt > 0) {
                let sourceAcc = mainAccIdFallback;
                // Cheques usually hit main bank or stay as notes, fallback to main account for local balance
                applyBalanceChange(sourceAcc, chequeAmt * sourceEffectMultiplier);
            }
        });
    }
}

// ═══════════════════════════════════════════════════════
// Inline Payment Panel & Formatting Logic (Grouped Vouchers)
// ═══════════════════════════════════════════════════════

window.togglePaymentSection = function(clickedMethod) {
    const btnCash = document.getElementById('toggle-cash');
    const btnBank = document.getElementById('toggle-bank');
    const btnCheque = document.getElementById('toggle-cheque');

    if (clickedMethod === 'cash') {
        if (btnCash) btnCash.classList.add('active');
        if (btnBank) btnBank.classList.remove('active');
        if (btnCheque) btnCheque.classList.remove('active');
    } else {
        if (btnCash) btnCash.classList.remove('active');
        
        const btn = document.getElementById('toggle-' + clickedMethod);
        if (btn) {
            btn.classList.toggle('active');
        }
        
        // If both bank and cheque are deactivated, fallback to cash
        const bankActive = btnBank?.classList.contains('active');
        const chequeActive = btnCheque?.classList.contains('active');
        if (!bankActive && !chequeActive) {
            if (btnCash) btnCash.classList.add('active');
        }
    }

    if (typeof playClickSound === 'function') {
        playClickSound();
    }

    // Sync with radio buttons
    const btnBankActive = btnBank?.classList.contains('active');
    const btnChequeActive = btnCheque?.classList.contains('active');

    const radioCash = document.getElementById('radio-cash');
    const radioCheck = document.getElementById('radio-check');
    const radioMobile = document.getElementById('radio-mobile');
    const radioHybrid = document.getElementById('radio-hybrid');

    if (radioCash) radioCash.checked = false;
    if (radioCheck) radioCheck.checked = false;
    if (radioMobile) radioMobile.checked = false;
    if (radioHybrid) radioHybrid.checked = false;

    if (btnBankActive && btnChequeActive) {
        if (radioHybrid) radioHybrid.checked = true;
    } else if (btnBankActive) {
        if (radioMobile) radioMobile.checked = true;
    } else if (btnChequeActive) {
        if (radioCheck) radioCheck.checked = true;
    } else {
        if (radioCash) radioCash.checked = true;
    }

    // Update payment method boxes visibility
    const isCash = !btnBankActive && !btnChequeActive;
    const secCash = document.getElementById('pay-cash-section');
    const secBank = document.getElementById('pay-bank-section');
    const secCheque = document.getElementById('pay-cheque-section');

    if (secCash) secCash.style.display = isCash ? 'flex' : 'none';
    if (secBank) secBank.style.display = btnBankActive ? 'flex' : 'none';
    if (secCheque) secCheque.style.display = btnChequeActive ? 'flex' : 'none';

    recalcPaymentSummary();
};

window.recalcPaymentSummary = function() {
    const totalEl = document.getElementById('total-amount') || document.getElementById('amount-local-hdr');
    const totalAmount = totalEl ? cleanNum(totalEl.value !== undefined ? totalEl.value : totalEl.innerText) : 0;
    const btnCash = document.getElementById('toggle-cash');
    const btnBank = document.getElementById('toggle-bank');
    const btnCheque = document.getElementById('toggle-cheque');

    const bankActive = btnBank?.classList.contains('active');
    const chequeActive = btnCheque?.classList.contains('active');
    const isCash = !bankActive && !chequeActive;
    const isHybrid = bankActive && chequeActive;
    const isMobile = bankActive && !chequeActive;
    const isCheck = chequeActive && !bankActive;
    
    const progCash = document.getElementById('prog-cash');
    const progBank = document.getElementById('prog-bank');
    const progCheque = document.getElementById('prog-cheque');
    
    if (progCash) progCash.style.width = '0%';
    if (progBank) progBank.style.width = '0%';
    if (progCheque) progCheque.style.width = '0%';

    const badge = document.getElementById('payment-status-badge');

    if (totalAmount > 0) {
        if (isCash) {
            if (progCash) progCash.style.width = '100%';
            if (badge) {
                badge.innerText = '💵 مدفوعة كاش بالكامل';
                badge.style.background = 'rgba(16, 185, 129, 0.15)';
                badge.style.color = '#34d399';
                badge.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.2)';
            }
        } else if (isMobile) {
            if (progBank) progBank.style.width = '100%';
            if (badge) {
                badge.innerText = '🏦 محولة بالكامل';
                badge.style.background = 'rgba(59, 130, 246, 0.15)';
                badge.style.color = '#60a5fa';
                badge.style.boxShadow = '0 0 10px rgba(59, 130, 246, 0.2)';
            }
        } else if (isCheck) {
            if (progCheque) progCheque.style.width = '100%';
            if (badge) {
                badge.innerText = '🧾 شيك بالكامل';
                badge.style.background = 'rgba(139, 92, 246, 0.15)';
                badge.style.color = '#c084fc';
                badge.style.boxShadow = '0 0 10px rgba(139, 92, 246, 0.2)';
            }
        } else if (isHybrid) {
            let tSum = 0;
            document.querySelectorAll('.transfer-row .transfer-amount').forEach(inp => tSum += cleanNum(inp.value));
            let cSum = 0;
            document.querySelectorAll('.cheque-row .cheque-amount').forEach(inp => cSum += cleanNum(inp.value));
            
            const bankPct = Math.min(100, (tSum / totalAmount) * 100);
            const chequePct = Math.min(100, (cSum / totalAmount) * 100);
            
            if (progBank) progBank.style.width = bankPct + '%';
            if (progCheque) progCheque.style.width = chequePct + '%';
            
            if (badge) {
                if (Math.abs((tSum + cSum) - totalAmount) < 0.01) {
                    badge.innerText = '💳 مدفوعة بالكامل (تحويل وشيك)';
                    badge.style.background = 'rgba(56, 189, 248, 0.15)';
                    badge.style.color = '#38bdf8';
                    badge.style.boxShadow = '0 0 10px rgba(56, 189, 248, 0.2)';
                } else {
                    badge.innerText = `⏳ متبقي: ${fmtNum(totalAmount - (tSum + cSum))}`;
                    badge.style.background = 'rgba(245, 158, 11, 0.15)';
                    badge.style.color = '#fbbf24';
                    badge.style.boxShadow = '0 0 10px rgba(245, 158, 11, 0.2)';
                }
            }
        }
    } else {
        if (badge) {
            badge.innerText = '⚠️ في انتظار إدخال المبالغ';
            badge.style.background = 'rgba(239, 68, 68, 0.15)';
            badge.style.color = '#f87171';
            badge.style.boxShadow = '0 0 10px rgba(239, 68, 68, 0.2)';
        }
    }
};

function playClickSound() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(800, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.08);
        gain.gain.setValueAtTime(0.08, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.08);
    } catch(e) {}
}

// Event Delegation for dynamically added sub-amount input fields
document.addEventListener('focus', function(event) {
    const input = event.target;
    if (input.classList.contains('sub-amount')) {
        const val = parseFloat(input.value.replace(/,/g, '')) || 0;
        if (val === 0) {
            input.value = '';
        }
    }
}, true);

document.addEventListener('blur', function(event) {
    const input = event.target;
    if (input.classList.contains('sub-amount') || input.classList.contains('block-cheque-amount')) {
        const val = parseFloat(input.value.replace(/,/g, '')) || 0;
        if (input.classList.contains('block-cheque-amount')) {
            const container = input.closest('.cheque-rows-container') || (input.closest('.account-block') ? input.closest('.account-block').querySelector('.cheque-rows-container') : null);
            if (!input.value || val <= 0) {
                if (container && container.querySelectorAll('.cheque-row').length > 1) {
                    const row = input.closest('.cheque-row');
                    if (row) {
                        const block = input.closest('.account-block');
                        const blockId = block ? block.getAttribute('data-block-id') : null;
                        row.remove();
                        if (blockId) recalcBlockTotal(blockId);
                        return;
                    }
                }
            }
        }
        if (input.value.trim() === '') {
            input.value = '';
        } else {
            let parts = val.toFixed(2).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            input.value = parts.join('.');
        }
        calcTotal();
    }
}, true);

// ═══════════════════════════════════════════════════════
// Multiple Bank Transfers & Cheques Dynamic Manipulation
// ═══════════════════════════════════════════════════════

window.refreshTransferRowButtons = function() {
    const container = document.getElementById('transfers-container');
    if (!container) return;
    const rows = container.querySelectorAll('.transfer-row');
    rows.forEach((row, idx) => {
        const btnBox = row.querySelector('div:last-child');
        if (!btnBox) return;
        if (idx === 0) {
            btnBox.innerHTML = `<button type="button" onclick="addTransferRow()" class="pay-toggle-btn" style="background: rgba(251, 191, 36, 0.2); border: 1px solid #fbbf24; color:#fbbf24; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`;
        } else {
            btnBox.innerHTML = `<button type="button" onclick="removeTransferRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;
        }
    });
};

window.refreshChequeRowButtons = function() {
    const container = document.getElementById('cheques-container');
    if (!container) return;
    const rows = container.querySelectorAll('.cheque-row');
    rows.forEach((row, idx) => {
        const btnBox = row.querySelector('div:last-child');
        if (!btnBox) return;
        if (idx === 0) {
            btnBox.innerHTML = `<button type="button" onclick="addChequeRow()" class="pay-toggle-btn" style="background: rgba(139,92,246,0.2); border: 1px solid #c084fc; color:#c084fc; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`;
        } else {
            btnBox.innerHTML = `<button type="button" onclick="removeChequeRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;
        }
    });
};

window.handleTransferAmountBlur = function(input) {
    const container = document.getElementById('transfers-container');
    const val = cleanNum(input.value);
    const row = input.closest('.transfer-row');
    if (!input.value || val <= 0) {
        if (container && container.querySelectorAll('.transfer-row').length > 1) {
            if (row) {
                row.remove();
                refreshTransferRowButtons();
                syncFirstRowAmount();
                if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
            }
            return;
        } else {
            input.value = '';
        }
    } else {
        input.value = parseFloat(val).toFixed(2);
    }
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
};

window.handleChequeAmountBlur = function(input) {
    const container = document.getElementById('cheques-container');
    const val = cleanNum(input.value);
    const row = input.closest('.cheque-row');
    if (!input.value || val <= 0) {
        if (container && container.querySelectorAll('.cheque-row').length > 1) {
            if (row) {
                row.remove();
                refreshChequeRowButtons();
                syncFirstRowAmount();
                if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
            }
            return;
        } else {
            input.value = '';
        }
    } else {
        input.value = parseFloat(val).toFixed(2);
    }
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
};

window.addTransferRow = function(no = '', note = '', amt = 0) {
    const container = document.getElementById('transfers-container');
    if (!container) return;

    const isFirst = (container.children.length === 0);
    const row = document.createElement('div');
    row.className = 'transfer-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; width: 100%;';
    
    const labelHTML = isFirst 
        ? `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem;">🏦</span>
               <span style="font-weight: 800; color: #fbbf24; font-size: 0.9rem;">تفاصيل التحويلة</span>
           </div>`
        : `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem; visibility: hidden;">🏦</span>
               <span style="font-weight: 800; color: #fbbf24; font-size: 0.9rem; opacity: 0.6;">تحويلة إضافية</span>
           </div>`;

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addTransferRow()" class="pay-toggle-btn" style="background: rgba(251, 191, 36, 0.2); border: 1px solid #fbbf24; color:#fbbf24; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeTransferRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div style="width: 220px; flex: none; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">المبلغ:</label>
            <input type="text" autocomplete="off" class="v-input ctrl-input transfer-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="0.00" onfocus="if(parseFloat(this.value.replace(/,/g, '') || 0) === 0) this.value = '';" oninput="restrictToNumbers(this); recalcPaymentSummary();" onblur="handleTransferAmountBlur(this);" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:1.35rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 170px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">اسم البنك:</label>
            <input type="text" autocomplete="off" list="master-banks-datalist" class="v-input ctrl-input transfer-note" value="${note}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 190px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">رقم التحويلة:</label>
            <input type="text" autocomplete="off" class="v-input ctrl-input transfer-no" value="${no}" placeholder="رقم التحويلة..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div style="display: flex; justify-content: flex-end; width: 40px; flex: none;">
            ${buttonHTML}
        </div>
    `;
    container.appendChild(row);
    
    if (no === '' && typeof playClickSound === 'function') playClickSound();
    syncFirstRowAmount();
};

window.removeTransferRow = function(btn) {
    const container = document.getElementById('transfers-container');
    const rows = container.querySelectorAll('.transfer-row');
    if (rows.length <= 1) {
        showToast('يجب إدخال تحويل واحد على الأقل', 'error');
        return;
    }
    const row = btn.closest('.transfer-row');
    if (row) {
        row.classList.add('removing');
        setTimeout(() => { 
            row.remove(); 
            refreshTransferRowButtons();
            syncFirstRowAmount(); 
        }, 300);
    }
};

window.addChequeRow = function(branch = '', no = '', date = '', amt = 0) {
    const container = document.getElementById('cheques-container');
    if (!container) return;

    const isFirst = (container.children.length === 0);
    const row = document.createElement('div');
    row.className = 'cheque-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; width: 100%;';
    
    const labelHTML = isFirst 
        ? `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem;">✍️</span>
               <span style="font-weight: 800; color: #c084fc; font-size: 0.9rem;">تفاصيل الشيك</span>
           </div>`
        : `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem; visibility: hidden;">✍️</span>
               <span style="font-weight: 800; color: #c084fc; font-size: 0.9rem; opacity: 0.6;">شيك إضافي</span>
           </div>`;

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addChequeRow()" class="pay-toggle-btn" style="background: rgba(139,92,246,0.2); border: 1px solid #c084fc; color:#c084fc; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeChequeRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div style="width: 220px; flex: none; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">المبلغ:</label>
            <input type="text" class="v-input ctrl-input cheque-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="0.00" onfocus="if(parseFloat(this.value.replace(/,/g, '') || 0) === 0) this.value = '';" oninput="restrictToNumbers(this); recalcPaymentSummary();" onblur="handleChequeAmountBlur(this);" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:1.35rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 170px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">اسم البنك:</label>
            <input type="text" autocomplete="off" list="master-banks-datalist" class="v-input ctrl-input cheque-branch" value="${branch}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">رقم الشيك:</label>
            <input type="text" class="v-input ctrl-input cheque-no" value="${no}" placeholder="رقم الشيك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div style="flex: 0.9; min-width: 220px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">تاريخ الاستحقاق:</label>
            <input type="date" class="v-input ctrl-input cheque-date" value="${date}" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 4px; width: 100%; border-radius: 6px; color:#fff;">
        </div>
        <div style="display: flex; justify-content: flex-end; width: 40px; flex: none;">
            ${buttonHTML}
        </div>
    `;
    container.appendChild(row);
    
    if (no === '' && typeof playClickSound === 'function') playClickSound();
    syncFirstRowAmount();
};

window.removeChequeRow = function(btn) {
    const container = document.getElementById('cheques-container');
    const rows = container.querySelectorAll('.cheque-row');
    if (rows.length <= 1) {
        showToast('يجب إدخال شيك واحد على الأقل', 'error');
        return;
    }
    const row = btn.closest('.cheque-row');
    if (row) {
        row.classList.add('removing');
        setTimeout(() => { 
            row.remove(); 
            refreshChequeRowButtons();
            syncFirstRowAmount(); 
        }, 300);
    }
};

window.initPaymentContainers = function() {
    const transfersContainer = document.getElementById('transfers-container');
    const chequesContainer = document.getElementById('cheques-container');
    if (transfersContainer) {
        transfersContainer.innerHTML = '';
        addTransferRow('', '', 0);
    }
    if (chequesContainer) {
        chequesContainer.innerHTML = '';
        addChequeRow('', '', '', 0);
    }
};

window.syncFirstRowAmount = function() {
    const totalEl = document.getElementById('total-amount') || document.getElementById('amount-local-hdr');
    const totalAmount = totalEl ? cleanNum(totalEl.value !== undefined ? totalEl.value : totalEl.innerText) : 0;
    const transferRows = document.querySelectorAll('.transfer-row');
    if (transferRows.length === 1) {
        transferRows[0].querySelector('.transfer-amount').value = fmtNum(totalAmount);
    }
    const chequeRows = document.querySelectorAll('.cheque-row');
    if (chequeRows.length === 1) {
        chequeRows[0].querySelector('.cheque-amount').value = fmtNum(totalAmount);
    }
};

window.loadVoucherPaymentDetails = function(v) {
    const method = v.paymentMethod || 'cash';
    const transfersContainer = document.getElementById('transfers-container');
    const chequesContainer = document.getElementById('cheques-container');
    if (transfersContainer) transfersContainer.innerHTML = '';
    if (chequesContainer) chequesContainer.innerHTML = '';

    if (method === 'mobile') {
        if (v.bankTransfers && Array.isArray(v.bankTransfers) && v.bankTransfers.length > 0) {
            v.bankTransfers.forEach(tr => {
                addTransferRow(tr.transferNo, tr.note, tr.amount);
            });
        } else {
            addTransferRow(v.mobileTransferNo || '', v.mobileNote || '', v.totalAmount || 0);
        }
    } else if (method === 'check') {
        if (v.cheques && Array.isArray(v.cheques) && v.cheques.length > 0) {
            v.cheques.forEach(ch => {
                addChequeRow(ch.checkBranch, ch.checkNo, ch.checkDate, ch.amount);
            });
        } else {
            addChequeRow(v.checkBranch || '', v.checkNo || '', v.checkDate || '', v.totalAmount || 0);
        }
    } else {
        initPaymentContainers();
    }
};

// Hook setup into document load
document.addEventListener('DOMContentLoaded', () => {
    initPaymentContainers();
});

// ═══════════════════════════════════════════════════════
// PHASE 2 & 3: Professional Enhancements
// ═══════════════════════════════════════════════════════

// ─── Direct Print (opens preview then auto-prints) ───
window.directPrint = function() {
    printVoucher(); // Opens the preview modal first
    setTimeout(() => { window.print(); }, 600); // Then trigger print dialog
};

// ─── Confetti Celebration Effect ───
window.launchConfetti = function() {
    const colors = ['#10b981', '#34d399', '#6ee7b7', '#fbbf24', '#38bdf8', '#c084fc', '#f87171'];
    for (let i = 0; i < 40; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.left = Math.random() * 100 + 'vw';
        piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        piece.style.animationDuration = (1.5 + Math.random() * 2) + 's';
        piece.style.animationDelay = Math.random() * 0.8 + 's';
        piece.style.width = (6 + Math.random() * 8) + 'px';
        piece.style.height = (6 + Math.random() * 8) + 'px';
        piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        document.body.appendChild(piece);
        setTimeout(() => piece.remove(), 4000);
    }
};

// ─── Shake Error Helper ───
window.shakeElement = function(el) {
    if (!el) return;
    el.classList.add('shake-error');
    setTimeout(() => el.classList.remove('shake-error'), 600);
};

// ─── Keyboard Shortcuts ───
document.addEventListener('keydown', function(e) {
    // Ctrl+S = Save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        if (typeof saveVoucher === 'function') saveVoucher();
    }
    // Ctrl+P = Preview
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        if (typeof printVoucher === 'function') printVoucher();
    }
    // Ctrl+N = New Document
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        if (typeof resetVoucher === 'function') resetVoucher();
    }
    // Escape = Close modal
    if (e.key === 'Escape') {
        const modal = document.getElementById('print-preview-modal');
        if (modal && modal.style.display !== 'none') {
            if (typeof closePrintPreviewModal === 'function') closePrintPreviewModal();
        }
    }
});

// ─── Unsaved Changes Tracking (silent, no beforeunload dialog) ───
let formDirty = false;
document.addEventListener('input', function(e) {
    if (e.target.closest('#accounts-blocks-container') || e.target.id === 'main-account-search' || e.target.id === 'voucher-desc') {
        formDirty = true;
    }
});
// Clear dirty flag after save
const origResetVoucher = window.resetVoucher || resetVoucher;
if (typeof resetVoucher === 'function') {
    const _origReset = resetVoucher;
    // We can't reassign the function declaration, so we hook into save success
}
// Hook: mark clean after successful save toast
const origShowToast = window.showToast;
if (origShowToast) {
    window.showToast = function(msg, type) {
        if (type === 'success' && msg.includes('حفظ')) {
            formDirty = false;
            // Launch confetti on save success!
            if (typeof launchConfetti === 'function') launchConfetti();
        }
        return origShowToast.call(this, msg, type);
    };
}

window.populateSafesDropdown = function(selectElement) {
    if (!selectElement) return;
    
    // Clear and add default
    selectElement.innerHTML = '<option value="">-- اختر الخزنة --</option>';
    
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const tKey = getTenantKey('erp_safes_v1');
        let safes = JSON.parse(localStorage.getItem(tKey) || localStorage.getItem('erp_safes_v1') || '[]');
        if (!Array.isArray(safes)) safes = Object.values(safes);
        
        safes.forEach(safe => {
            if (safe.deleted) return;
            const name = safe.safeName || safe.name;
            if (name) {
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = name;
                selectElement.appendChild(opt);
            }
        });
    } catch (e) {
        console.error('Error populating safes dropdown:', e);
    }
};

window.openLedgerForMainAccount = function() {
    const accId = document.getElementById('main-account').value;
    if (!accId) {
        if(typeof showToast === 'function') showToast('يرجى اختيار الحساب أولاً', 'info');
        return;
    }
    const accName = document.getElementById('main-account-search').value;
    window.open(`general-ledger.php?accountId=${encodeURIComponent(accId)}&name=${encodeURIComponent(accName)}`, '_blank');
};

window.openLedgerForBlockAccount = function(blockId) {
    let accId = '';
    const idEl = document.getElementById(`account-${blockId}-id`);
    if(idEl && idEl.value) {
        accId = idEl.value;
    } else {
        const nameEl = document.getElementById(`account-${blockId}`);
        if(nameEl) accId = nameEl.value;
    }
    if (!accId) {
        if(typeof showToast === 'function') showToast('يرجى اختيار الحساب أولاً', 'info');
        return;
    }
    const accName = document.getElementById(`account-${blockId}-search`).value;
    window.open(`general-ledger.php?accountId=${encodeURIComponent(accId)}&name=${encodeURIComponent(accName)}`, '_blank');
};






// Check for transfer number duplicates
window.checkTransferDuplicate = async function(tno, currentVoucherId) {
    if (!tno || String(tno).trim().length < 6) return true;
    const targetSuffix = String(tno).trim().slice(-6);
    const getTenantKey = window.getTenantKey || ((k) => k);
    const keys = [
        getTenantKey('erp_payments_v1'),
        getTenantKey('erp_receipts_v1'),
        getTenantKey('erp_comprehensive_payments_v1'),
        getTenantKey('erp_comprehensive_receipts_v1'),
        getTenantKey('erp_journal_v1')
    ];
    
    // Helper to check transfer number matches
    const isMatch = (no) => {
        let strNo = String(no || '').trim();
        return (strNo.length >= 6 && strNo.slice(-6) === targetSuffix);
    };

    for (let key of keys) {
        let vouchers = [];
        try {
            // Using localStorage since all vouchers are typically stored there, but fallback to localforage just in case
            vouchers = JSON.parse(localStorage.getItem(key)) || [];
            if (!vouchers.length && typeof localforage !== 'undefined') {
                vouchers = (await localforage.getItem(key)) || [];
            }
        } catch(e) { continue; }
        
        for (let v of vouchers) {
            if (!v || v.deleted) continue;
            if (currentVoucherId && (v.id === currentVoucherId || v.voucherNo === currentVoucherId)) continue;
            if (window._activeInboundReceiptId && v.inboundReceiptId && String(v.inboundReceiptId) === String(window._activeInboundReceiptId)) continue;
            
            // 1. Check top-level bankTransfers
            if (v.bankTransfers && Array.isArray(v.bankTransfers)) {
                for (let bt of v.bankTransfers) {
                    if (isMatch(bt.transferNo)) return false;
                }
            } else if (v.mobileTransferNo && isMatch(v.mobileTransferNo)) {
                return false;
            }
            
            // 2. Check items array (Multi Payment)
            if (v.items && Array.isArray(v.items)) {
                for (let item of v.items) {
                    if (!item) continue;
                    if (item.bankTransfers && Array.isArray(item.bankTransfers)) {
                        for (let bt of item.bankTransfers) {
                            if (isMatch(bt.transferNo)) return false;
                        }
                    } else if (item.transferNo && isMatch(item.transferNo)) {
                        return false;
                    }
                }
            }
            
            // 3. Check details array (Single Payment)
            if (v.details && Array.isArray(v.details)) {
                for (let det of v.details) {
                    if (!det) continue;
                    if (det.bankTransfers && Array.isArray(det.bankTransfers)) {
                        for (let bt of det.bankTransfers) {
                            if (isMatch(bt.transferNo)) return false;
                        }
                    } else if (det.transferNo && isMatch(det.transferNo)) {
                        return false;
                    }
                }
            }
            
            // 4. Check subTransactions (Journal)
            if (v.subTransactions && Array.isArray(v.subTransactions)) {
                for (let sub of v.subTransactions) {
                    if (!sub) continue;
                    if (sub.transferNo && isMatch(sub.transferNo)) return false;
                }
            }
        }
    }
    return true; // Not duplicated
};

// --- Custom Number Properties Menu Logic ---
let currentTargetInput = null;

window.showNumberProperties = function(e, input) {
    if (typeof window.openNumPropsFor === 'function') {
        window.openNumPropsFor(input, e);
        return;
    }
};

window.closeNumberProperties = function() {
    const menu = document.getElementById('custom-num-menu');
    if (menu) menu.style.display = 'none';
};

window.applyNumProp = function(action, val) {
    if (!currentTargetInput) return;
    const input = currentTargetInput;
    
    if (action === 'font-inc') {
        let currentSize = parseFloat(window.getComputedStyle(input).fontSize);
        input.style.setProperty('font-size', (currentSize + 2) + 'px', 'important');
    } else if (action === 'font-dec') {
        let currentSize = parseFloat(window.getComputedStyle(input).fontSize);
        input.style.setProperty('font-size', (currentSize - 2) + 'px', 'important');
    } else if (action === 'toggle-comma') {
        const current = input.getAttribute('data-thousands') !== 'false';
        input.setAttribute('data-thousands', current ? 'false' : 'true');
        const commaEl = document.getElementById('prop-comma-status');
        if (commaEl) {
            commaEl.textContent = !current ? 'مفعل' : 'معطل';
            commaEl.style.color = !current ? '#10b981' : '#ef4444';
        }
    } else if (action === 'dec-set-2') {
        let dec = 2;
        input.setAttribute('data-decimals', dec);
        input.setAttribute('data-rounding', 'true');
        const decEl = document.getElementById('prop-dec-val');
        if (decEl) decEl.textContent = dec;
        const roundingStatus = document.getElementById('prop-rounding-status');
        if (roundingStatus) {
            roundingStatus.textContent = 'مفعل';
            roundingStatus.style.color = '#10b981';
        }
    } else if (action === 'dec-inc') {
        let dec = parseInt(input.getAttribute('data-decimals') || '0');
        if (dec < 10) dec++;
        input.setAttribute('data-decimals', dec);
        input.setAttribute('data-rounding', 'true');
        const decEl = document.getElementById('prop-dec-val');
        if (decEl) decEl.textContent = dec;
        const roundingStatus = document.getElementById('prop-rounding-status');
        if (roundingStatus) {
            roundingStatus.textContent = 'مفعل';
            roundingStatus.style.color = '#10b981';
        }
    } else if (action === 'dec-dec') {
        let dec = parseInt(input.getAttribute('data-decimals') || '0');
        if (dec > 0) dec--;
        input.setAttribute('data-decimals', dec);
        input.setAttribute('data-rounding', 'true');
        const decEl = document.getElementById('prop-dec-val');
        if (decEl) decEl.textContent = dec;
        const roundingStatus = document.getElementById('prop-rounding-status');
        if (roundingStatus) {
            roundingStatus.textContent = 'مفعل';
            roundingStatus.style.color = '#10b981';
        }
    } else if (action === 'toggle-font') {
        const current = input.getAttribute('data-font') || 'inter';
        const newFont = current === 'inter' ? 'monospace' : 'inter';
        input.setAttribute('data-font', newFont);
        const fontEl = document.getElementById('prop-font-status');
        if (newFont === 'monospace') {
            input.style.setProperty('font-family', "'Roboto Mono', Consolas, monospace", 'important');
            if (fontEl) fontEl.textContent = 'Roboto Mono';
        } else {
            input.style.setProperty('font-family', "'Inter', system-ui, sans-serif", 'important');
            if (fontEl) fontEl.textContent = 'Inter';
        }
    } else if (action === 'color-change') {
        input.setAttribute('data-color', val);
        input.style.setProperty('color', val, 'important');
    } else if (action === 'toggle-rounding') {
        const current = input.getAttribute('data-rounding') === 'true';
        input.setAttribute('data-rounding', current ? 'false' : 'true');
        const roundingStatus = document.getElementById('prop-rounding-status');
        if (roundingStatus) {
            roundingStatus.textContent = !current ? 'مفعل' : 'معطل';
            roundingStatus.style.color = !current ? '#10b981' : '#ef4444';
        }
    }
    
    formatAmount(input);
    if (typeof calcTotal === 'function') calcTotal();
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
};

window.formatAmount = function(input) {
    if (!input.value) return;
    let raw = input.value.toString().replace(/[^0-9.-]/g, '');
    let num = parseFloat(raw);
    if (isNaN(num)) num = 0;
    
    const hasComma = input.getAttribute('data-thousands') !== 'false';
    const isRounding = input.getAttribute('data-rounding') === 'true';
    const decimals = isRounding ? parseInt(input.getAttribute('data-decimals') || '0') : null;
    
    input.value = fmtNum(num, decimals, hasComma);
};

window.saveNumberProperties = function() {
    // Placeholder for save logic if needed globally.
    const btn = document.getElementById('save-props-btn');
    if (btn) {
        const oldText = btn.innerHTML;
        btn.innerHTML = 'تم الحفظ بنجاح';
        setTimeout(() => btn.innerHTML = oldText, 1500);
    }
};

// --- Custom Keyboard Navigation Flow (Enter & Ctrl+Enter) ---
document.addEventListener('keydown', function(e) {
    const active = document.activeElement;
    if (!active) return;

    // --- Ctrl + Enter : Switch to Cheque Mode ---
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        const block = active.closest('.account-block');
        if (block) {
            const chequeBtn = block.querySelector('.block-btn-cheque');
            if (chequeBtn) {
                // Click it to ensure it opens
                chequeBtn.click();
                setTimeout(() => {
                    const chequeAmount = block.querySelector('.block-cheque-amount');
                    if (chequeAmount) {
                        chequeAmount.focus();
                        chequeAmount.select();
                    }
                }, 100);
            }
        }
        return;
    }

    if (e.key === 'Enter' && e.ctrlKey) {
        const block = active.closest('.account-block');
        if (block) {
            e.preventDefault();
            const blockId = block.id.replace('block-', '');
            const btnCheque = block.querySelector('.block-btn-cheque');
            if (btnCheque && !btnCheque.classList.contains('active')) {
                if (typeof window.toggleBlockPaymentMethod === 'function') {
                    window.toggleBlockPaymentMethod(blockId, 'cheque');
                }
            }
            setTimeout(() => {
                const amtEl = block.querySelector('.block-cheque-amount');
                if (amtEl) { amtEl.focus(); amtEl.select(); }
            }, 50);
        }
        return;
    }

    if (e.key === 'Enter' && !e.ctrlKey) {
        
        // 1. main-account-search -> account-search input of first block
        if (active.id === 'main-account-search') {
            const list = document.getElementById('main-account-dropdown-list');
            if (list && list.style.display !== 'none' && list.innerHTML.trim() !== '') {
                // Dropdown is open. Auto-select first item and jump to first receiving account
                e.preventDefault();
                const firstItem = list.querySelector('.custom-dropdown-item');
                if (firstItem) {
                    const id = firstItem.dataset.id;
                    const name = firstItem.dataset.name;
                    if (id && name && typeof selectAccount === 'function') {
                        selectAccount('main-account', id, name);
                    }
                }
                setTimeout(() => {
                    const firstAccount = document.querySelector('.block-account-search');
                    if (firstAccount) {
                        firstAccount.focus();
                        firstAccount.select();
                        const blockId = firstAccount.id.replace('account-search-', '');
                        if (typeof showAccountDropdown === 'function') {
                            showAccountDropdown('account-' + blockId);
                        }
                    }
                }, 100);
                return;
            } else {
                // Second enter (dropdown closed) jumps to first account search
                e.preventDefault();
                const firstAccount = document.querySelector('.block-account-search');
                if (firstAccount) {
                    firstAccount.focus();
                    firstAccount.select();
                    const blockId = firstAccount.id.replace('account-search-', '');
                    if (typeof showAccountDropdown === 'function') {
                        showAccountDropdown('account-' + blockId);
                    }
                }
                return;
            }
        }
        
        // 2. amount-* -> account-*-search
        if (active.classList.contains('block-total-amount')) {
            e.preventDefault();
            const block = active.closest('.account-block');
            if (block) {
                const acctSearch = block.querySelector('.block-account-search');
                if (acctSearch) { acctSearch.focus(); acctSearch.select(); }
            }
            return;
        }

        // 3. account-*-search -> bank amount
        if (active.classList.contains('block-account-search')) {
            const type = active.id.replace('-search', '');
            const listId = type + '-dropdown-list';
            const list = document.getElementById(listId);
            if (list && list.style.display !== 'none' && list.innerHTML.trim() !== '') {
                // Dropdown open, auto-select first item
                e.preventDefault();
                const firstItem = list.querySelector('.custom-dropdown-item');
                if (firstItem) {
                    const id = firstItem.dataset.id;
                    const name = firstItem.dataset.name;
                    if (id && name && typeof selectAccount === 'function') {
                        selectAccount(type, id, name);
                    }
                }
                return; 
            } else {
                e.preventDefault();
                const block = active.closest('.account-block');
                if (block) {
                    const blockId = block.id.replace('block-', '');
                    const btnBank = block.querySelector('.block-btn-bank');
                    if (btnBank && !btnBank.classList.contains('active')) {
                        if (typeof window.toggleBlockPaymentMethod === 'function') {
                            window.toggleBlockPaymentMethod(blockId, 'bank');
                        }
                    }
                    setTimeout(() => {
                        const amtEl = block.querySelector('.block-bank-amount');
                        if (amtEl ) { amtEl.focus(); amtEl.select(); }
                    }, 50);
                }
                return;
            }
        }

        // 4. bank toggle -> bank amount
        if (active.classList.contains('block-btn-bank')) {
            setTimeout(() => {
                const block = active.closest('.account-block');
                if (block) {
                    const amt = block.querySelector('.block-bank-amount');
                    if (amt && amt.offsetParent !== null) {
                        amt.focus();
                        amt.select();
                    }
                }
            }, 50);
            return;
        }

        if (active.classList.contains('block-btn-cheque')) {
            setTimeout(() => {
                const block = active.closest('.account-block');
                if (block) {
                    const amt = block.querySelector('.block-cheque-amount');
                    if (amt && amt.offsetParent !== null) {
                        amt.focus();
                        amt.select();
                    }
                }
            }, 50);
            return;
        }

        // 5. statement-* -> create new block and focus its account search
        if (active.id && active.id.startsWith('statement-')) {
            e.preventDefault();
            if (typeof window.addAccountBlock === 'function') {
                window.addAccountBlock();
                setTimeout(() => {
                    const blocks = document.querySelectorAll('.account-block');
                    if (blocks.length > 0) {
                        const lastBlock = blocks[blocks.length - 1];
                        const acctSearch = lastBlock.querySelector('.block-account-search');
                        if (acctSearch) {
                            acctSearch.focus();
                            acctSearch.select();
                        }
                    }
                }, 100);
            }
            return;
        }
        
        // --- Bank Details Navigation ---
        // 6. bank amount -> bank name
        if (active.classList.contains('block-bank-amount')) {
            e.preventDefault();
            const row = active.closest('.bank-row');
            if (row) {
                const nameInp = row.querySelector('.block-bank-name-input');
                if (nameInp) { nameInp.focus(); nameInp.select(); }
            }
            return;
        }
        
        // 7. bank name -> transfer no
        if (active.classList.contains('block-bank-name-input')) {
            e.preventDefault();
            const row = active.closest('.bank-row');
            if (row) {
                const trNo = row.querySelector('.block-transfer-no-input');
                if (trNo) { trNo.focus(); trNo.select(); }
            }
            return;
        }

        // 8. transfer no -> bank desc
        if (active.classList.contains('block-transfer-no-input')) {
            e.preventDefault();
            const row = active.closest('.bank-row');
            if (row) {
                const desc = row.querySelector('.block-bank-desc-input');
                if (desc) { desc.focus(); desc.select(); }
                else {
                    const block = active.closest('.account-block');
                    if (block) {
                        const btn = block.querySelector('[data-action="add-bank-row"]');
                        if (btn) btn.focus();
                    }
                }
            }
            return;
        }
        
        // 9. bank desc -> trigger add bank row and focus amount
        if (active.classList.contains('block-bank-desc-input')) {
            e.preventDefault();
            const block = active.closest('.account-block');
            if (block) {
                const btn = block.querySelector('[data-action="add-bank-row"]');
                if (btn) {
                    btn.click();
                    setTimeout(() => {
                        const bankAmts = block.querySelectorAll('.block-bank-amount');
                        if (bankAmts.length > 0) {
                            bankAmts[bankAmts.length - 1].focus();
                            bankAmts[bankAmts.length - 1].select();
                        }
                    }, 50);
                }
            }
            return;
        }

        // Handle enter on 'add-bank-row' button to focus new amount
        if (active.getAttribute('data-action') === 'add-bank-row') {
            setTimeout(() => {
                const block = active.closest('.account-block');
                if (block) {
                    const bankAmts = block.querySelectorAll('.block-bank-amount');
                    if (bankAmts.length > 0) {
                        bankAmts[bankAmts.length - 1].focus();
                        bankAmts[bankAmts.length - 1].select();
                    }
                }
            }, 50);
            return;
        }

        // --- Cheque Details Navigation ---
        // 10. cheque amount -> cheque bank name
        if (active.classList.contains('block-cheque-amount')) {
            e.preventDefault();
            const row = active.closest('.cheque-row');
            if (row) {
                const nameInp = row.querySelector('.block-cheque-bank-input');
                if (nameInp) { nameInp.focus(); nameInp.select(); }
            }
            return;
        }

        // 11. cheque name -> cheque no
        if (active.classList.contains('block-cheque-bank-input')) {
            e.preventDefault();
            const row = active.closest('.cheque-row');
            if (row) {
                const chqNo = row.querySelector('.block-cheque-no-input');
                if (chqNo) { chqNo.focus(); chqNo.select(); }
            }
            return;
        }

        // 12. cheque no -> cheque date
        if (active.classList.contains('block-cheque-no-input')) {
            e.preventDefault();
            const row = active.closest('.cheque-row');
            if (row) {
                const chqDate = row.querySelector('.block-cheque-date-input');
                if (chqDate) { chqDate.focus(); }
            }
            return;
        }

        // 13. cheque date -> cheque desc
        if (active.classList.contains('block-cheque-date-input')) {
            e.preventDefault();
            const row = active.closest('.cheque-row');
            if (row) {
                const desc = row.querySelector('.block-cheque-desc-input');
                if (desc) { desc.focus(); desc.select(); }
                else {
                    const block = active.closest('.account-block');
                    if (block) {
                        const btn = block.querySelector('[data-action="add-cheque-row"]');
                        if (btn) btn.focus();
                    }
                }
            }
            return;
        }

        // 14. cheque desc -> trigger add cheque row and focus amount
        if (active.classList.contains('block-cheque-desc-input')) {
            e.preventDefault();
            const block = active.closest('.account-block');
            if (block) {
                const btn = block.querySelector('[data-action="add-cheque-row"]');
                if (btn) {
                    btn.click();
                    setTimeout(() => {
                        const chqAmts = block.querySelectorAll('.block-cheque-amount');
                        if (chqAmts.length > 0) {
                            chqAmts[chqAmts.length - 1].focus();
                            chqAmts[chqAmts.length - 1].select();
                        }
                    }, 50);
                }
            }
            return;
        }

        // Handle enter on 'add-cheque-row' button to focus new amount
        if (active.getAttribute('data-action') === 'add-cheque-row') {
            setTimeout(() => {
                const block = active.closest('.account-block');
                if (block) {
                    const chqAmts = block.querySelectorAll('.block-cheque-amount');
                    if (chqAmts.length > 0) {
                        chqAmts[chqAmts.length - 1].focus();
                        chqAmts[chqAmts.length - 1].select();
                    }
                }
            }, 50);
            return;
        }
    }
}, true);

// Auto-focus the main account search when the script loads
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        const searchInput = document.getElementById('main-account-search');
        if (searchInput) searchInput.focus();
    }, 300);
});

// ══════════════════════════════════════════════════════════════════════════════════════
// 📱 WhatsApp Enterprise Automation Suite (منظومة تكامل الواتساب الذكي والمراسلات المصرفية)
// ══════════════════════════════════════════════════════════════════════════════════════

window.normalizePhoneNumber = function(rawPhone) {
    if (!rawPhone) return '';
    let p = String(rawPhone).replace(/[^0-9+]/g, '');
    if (p.startsWith('+')) p = p.substring(1);
    if (p.startsWith('00')) p = p.substring(2);
    // Sudan local numbers starting with 09 or 01 (e.g. 0912345678 or 0123456789)
    if (p.startsWith('0') && p.length === 10) {
        p = '249' + p.substring(1);
    } else if (p.length === 9 && (p.startsWith('9') || p.startsWith('1'))) {
        p = '249' + p;
    }
    return p;
};

window.getVoucherContactDetails = function(accountId, accountName) {
    if (!accountId && !accountName) return null;
    const cleanId = String(accountId || '').trim();
    const cleanName = String(accountName || '').trim();
    
    // 0. Check localStorage cached phone for this account
    try {
        const cached = (cleanId ? localStorage.getItem(`erp_wa_phone_${cleanId}`) : null) || 
                       (cleanName ? localStorage.getItem(`erp_wa_phone_${cleanName}`) : null);
        if (cached) {
            return {
                name: cleanName,
                phone: cached,
                type: 'cached'
            };
        }
    } catch(e) {}

    // 1. Check customers in localStorage
    try {
        const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(x => !x.deleted);
        const cleanCode = (cleanName || cleanId || '').replace(/^#/, '').trim();
        const customer = custs.find(c => 
            (cleanId && (String(c.id) === cleanId || String(c.id) === cleanId.replace('cust-', ''))) ||
            (cleanName && c.name && c.name.trim() === cleanName) ||
            (cleanCode && c.receiptCodeNum && String(c.receiptCodeNum).trim() === cleanCode) ||
            (cleanCode && c.receiptCodeWord && String(c.receiptCodeWord).trim().toLowerCase() === cleanCode.toLowerCase())
        );
        if (customer && (customer.phone || customer.whatsapp)) {
            return {
                name: customer.name,
                phone: customer.whatsapp || customer.phone || '',
                type: 'customer',
                receiptCodeNum: customer.receiptCodeNum || '',
                receiptCodeWord: customer.receiptCodeWord || ''
            };
        }
    } catch(e) {}
    
    // 2. Check suppliers in localStorage
    try {
        const sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]').filter(x => !x.deleted);
        const supplier = sups.find(s => 
            (cleanId && (String(s.id) === cleanId || String(s.id) === cleanId.replace('supp-', ''))) ||
            (cleanName && s.name && s.name.trim() === cleanName)
        );
        if (supplier && (supplier.phone || supplier.whatsapp)) {
            return {
                name: supplier.name,
                phone: supplier.whatsapp || supplier.phone || '',
                type: 'supplier'
            };
        }
    } catch(e) {}

    // 3. Check general accounts in localStorage (erp_accounts_v1)
    try {
        const accs = JSON.parse(localStorage.getItem('erp_accounts_v1') || '[]').filter(x => !x.deleted);
        const accItem = accs.find(a => 
            (cleanId && (String(a.id) === cleanId || String(a.code) === cleanId)) ||
            (cleanName && a.name && a.name.trim() === cleanName)
        );
        if (accItem && (accItem.phone || accItem.whatsapp)) {
            return {
                name: accItem.name,
                phone: accItem.whatsapp || accItem.phone || '',
                type: 'account'
            };
        }
    } catch(e) {}

    // 4. Check global ACCOUNTS list
    if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
        const acc = ACCOUNTS.find(a => 
            (cleanId && (String(a.id) === cleanId || String(a.rawId) === cleanId)) ||
            (cleanName && a.name && a.name.trim() === cleanName)
        );
        if (acc && (acc.phone || acc.whatsapp)) {
            return {
                name: acc.name,
                phone: acc.whatsapp || acc.phone || '',
                type: acc.type || 'account'
            };
        }
    }
    return null;
};

// ═══ 1. التفاعل الموحد مع زر واتساب بالكارت (إرسال مباشر أو استيراد إشعارات) ═══
window.handleBlockWhatsAppClick = function(blockId) {
    if (!blockId) return;
    const accSearch = document.getElementById(`account-${blockId}-search`);
    const accName = accSearch ? accSearch.value.trim() : '';
    const amtEl = document.getElementById(`amount-${blockId}`);
    const amt = amtEl ? cleanNum(amtEl.value) : 0;

    // إذا كان الكارت يحتوي على حساب أو مبلغ، فتح نافذة الإرسال المباشر لهذا البند فوراً
    if (amt > 0 || accName) {
        if (typeof window.openBlockWhatsAppDispatchModal === 'function') {
            window.openBlockWhatsAppDispatchModal(blockId);
            return;
        }
    }

    // إذا كان الكارت فارغاً، فتح قائمة الإشعارات البنكية الواردة للاستيراد السريع
    if (typeof InboundBankakManager !== 'undefined' && typeof InboundBankakManager.toggleDrawer === 'function') {
        InboundBankakManager.toggleDrawer();
        if (typeof showToast === 'function') {
            showToast('💡 يمكنك إدراج إشعار بنكي وارد لهذا الحساب أو تعبئة بيانات السند للإرسال', 'info', 5000);
        }
    } else if (typeof window.openBlockWhatsAppDispatchModal === 'function') {
        window.openBlockWhatsAppDispatchModal(blockId);
    }
};

window.openBlockWhatsAppIngest = function(blockId) {
    if (!blockId) return;
    const accSearch = document.getElementById(`account-${blockId}-search`);
    const accIdEl = document.getElementById(`account-${blockId}-id`);
    const accName = accSearch ? accSearch.value.trim() : '';
    const accId = accIdEl ? accIdEl.value.trim() : '';

    const contact = window.getVoucherContactDetails(accId, accName);
    const phone = contact && contact.phone ? contact.phone : '';
    const cleanPhone = phone ? window.normalizePhoneNumber(phone) : '';

    // توجيه تلقائي: فتح نافذة استيراد الإشعار المالي أو نافذة الإشعارات الواردة بدون فتح تطبيق واتساب
    if (typeof InboundBankakManager !== 'undefined' && typeof InboundBankakManager.toggleDrawer === 'function') {
        InboundBankakManager.toggleDrawer();
    } else if (typeof FinancialOCR !== 'undefined' && typeof FinancialOCR.openWhatsAppIngest === 'function') {
        FinancialOCR.openWhatsAppIngest({
            target: 'multi',
            blockId: blockId,
            accountName: accName,
            phone: cleanPhone
        });
    } else if (typeof FinancialOCR !== 'undefined' && typeof FinancialOCR.openScanner === 'function') {
        FinancialOCR.openScanner({ target: 'multi', blockId: blockId });
    }
};

// ═══ 1.5. توحيد وإدارة عملة السند والأرصدة في كروت (من وإلى) والكمبيالة المعتمدة ═══
window.getWACurrencySelectOptions = function(selectedCurr) {
    const list = [
        { code: 'SDG', name: 'SDG' },
        { code: 'USD', name: 'USD' },
        { code: 'SAR', name: 'SAR' },
        { code: 'AED', name: 'AED' },
        { code: 'EGP', name: 'EGP' }
    ];
    const curr = (selectedCurr || window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG').toUpperCase();
    return list.map(c => `<option value="${c.code}" ${c.code === curr ? 'selected' : ''}>${c.code}</option>`).join('');
};

window.getVoucherAmountForCurrency = function(targetCurrency, blockId) {
    const voucherCurrency = document.getElementById('currency')?.value || 'SDG';
    const localHdr = cleanNum(document.getElementById('amount-local-hdr')?.value) || 0;
    const foreignHdr = cleanNum(document.getElementById('amount-foreign')?.value) || 0;
    
    let blockAmt = 0;
    if (blockId) {
        const bAmtEl = document.getElementById(`amount-${blockId}`);
        if (bAmtEl) blockAmt = cleanNum(bAmtEl.value);
    }
    
    // Base amount in SDG
    let baseAmtSDG = 0;
    if (blockAmt > 0) {
        if (voucherCurrency === 'SDG') {
            baseAmtSDG = blockAmt;
        } else {
            const vRate = (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(voucherCurrency) : (voucherCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1)) || 2600;
            baseAmtSDG = blockAmt * vRate;
        }
    } else {
        baseAmtSDG = localHdr;
    }
    
    const target = (targetCurrency || 'SDG').toUpperCase();
    if (target === 'SDG') {
        return baseAmtSDG;
    } else if (target === voucherCurrency && foreignHdr > 0 && !blockAmt) {
        return foreignHdr;
    } else {
        const targetRate = (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(target) : (target === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1)) || 1;
        return targetRate > 0 ? (baseAmtSDG / targetRate) : baseAmtSDG;
    }
};

window.setWAModalCurrency = function(newCurr) {
    if (!newCurr) return;
    window._waCurrentModalCurrency = newCurr;
    
    // 1. مزامنة القائمة العلوية في شريط وجهة السند
    const topSelect = document.getElementById('wa-modal-currency-select');
    if (topSelect && topSelect.value !== newCurr) {
        topSelect.value = newCurr;
    }
    
    // 2. مزامنة كافة شرائح العملة الموحدة في كروت من وإلى
    document.querySelectorAll('.wa-currency-chip').forEach(el => {
        if (el.value !== newCurr) el.value = newCurr;
    });
    
    // 3. إعادة استخراج أطراف السند بالعملة الموحدة والمبلغ المعادل
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    parties.currency = newCurr;
    parties.amount = window.getVoucherAmountForCurrency(newCurr, blockId);
    
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    window.setWATargetMode(targetMode);
    
    // 4. تحديث المعاينة الحية للكمبيالة والكرت المصور فوراً
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    try { window.renderPromissoryNotePreview(activeTab); } catch(e) {}
    try { window.renderWAVoucherImageCard(activeTab, parties); } catch(e) {}
    
    // 5. تحديث قوالب ونصوص الرسائل بالعملة الجديدة
    const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
    window._waBothPartyMessages = {
        debtor: window.buildWATailoredMessage('debtor', parties, fmt),
        creditor: window.buildWATailoredMessage('creditor', parties, fmt)
    };
    
    const curPreviewText = document.getElementById('wa-both-preview-message');
    if (curPreviewText && window._waBothPartyMessages[activeTab]) {
        curPreviewText.value = window._waBothPartyMessages[activeTab];
    }
    
    if (typeof showToast === 'function') {
        showToast(`تم توحيد العملة في (من وإلى) إلى: ${newCurr}`, 'info');
    }
};

// ═══ 2. منظومة حساب واستعلام رصيد دفتر الأستاذ العام اللحظي (General Ledger Balance Engine) ═══
window.getPartyAccountBalance = function(partyId, partyName, targetRole = 'debtor', voucherAmt = 0, isPayment = true, forcedCurrency = null) {
    const getTenantKey = window.getTenantKey || ((k) => k);
    const cleanId = String(partyId || '').trim();
    const bareId = cleanId.replace(/^(cust|supp|bank|safe|exp|sub|sust)-/i, '').trim();
    const cleanName = String(partyName || '').trim();

    let masterRec = null;
    let entityType = 'account'; // 'customer', 'supplier', 'bank', 'safe', 'account'
    let nativeCurrency = 'SDG';
    const activeModalCurrency = window._waCurrentModalCurrency;
    const voucherCurrency = document.getElementById('currency')?.value || 'SDG';
    const targetCurrency = forcedCurrency || activeModalCurrency || voucherCurrency;

    // 1. البحث في سجلات العملاء
    try {
        const custs = (JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1')) || localStorage.getItem('erp_customers_v1')) || []).filter(x => x && !x.deleted);
        masterRec = custs.find(c => 
            (cleanId && (String(c.id) === cleanId || String(c.id) === bareId)) ||
            (cleanName && c.name && c.name.trim() === cleanName)
        );
        if (masterRec) entityType = 'customer';
    } catch(e) {}

    // 2. البحث في سجلات الموردين
    if (!masterRec) {
        try {
            const sups = (JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1')) || []).filter(x => x && !x.deleted);
            masterRec = sups.find(s => 
                (cleanId && (String(s.id) === cleanId || String(s.id) === bareId)) ||
                (cleanName && s.name && s.name.trim() === cleanName)
            );
            if (masterRec) entityType = 'supplier';
        } catch(e) {}
    }

    // 3. البحث في سجلات البنوك
    if (!masterRec) {
        try {
            const banks = (JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1')) || []).filter(x => x && !x.deleted);
            masterRec = banks.find(b => 
                (cleanId && (String(b.id) === cleanId || String(b.id) === bareId)) ||
                (cleanName && b.name && b.name.trim() === cleanName)
            );
            if (masterRec) entityType = 'bank';
        } catch(e) {}
    }

    // 4. البحث في سجلات الخزن
    if (!masterRec) {
        try {
            const safes = (JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1')) || localStorage.getItem('erp_safes_v1')) || []).filter(x => x && !x.deleted);
            masterRec = safes.find(s => 
                (cleanId && (String(s.id) === cleanId || String(s.id) === bareId)) ||
                (cleanName && s.name && s.name.trim() === cleanName)
            );
            if (masterRec) entityType = 'safe';
        } catch(e) {}
    }

    // 5. البحث في سجلات دليل الحسابات العام
    if (!masterRec) {
        try {
            const accounts = (JSON.parse(localStorage.getItem(getTenantKey('erp_accounts_v1')) || localStorage.getItem('erp_accounts_v1')) || []).filter(x => x && !x.deleted);
            masterRec = accounts.find(a => 
                (cleanId && (String(a.id) === cleanId || String(a.id) === bareId || String(a.code) === cleanId || String(a.code) === bareId)) ||
                (cleanName && a.name && a.name.trim() === cleanName)
            );
            if (masterRec) entityType = 'account';
        } catch(e) {}
    }

    if (masterRec && masterRec.currency) {
        nativeCurrency = masterRec.currency;
    } else {
        nativeCurrency = voucherCurrency;
    }

    // حساب الرصيد الافتتاحي للأستاذ العام
    let intrinsicBal = 0;
    if (masterRec) {
        const opDebit = parseFloat(masterRec.openingDebit || masterRec.debit || 0) || 0;
        const opCredit = parseFloat(masterRec.openingCredit || masterRec.credit || 0) || 0;
        const rawBal = parseFloat(masterRec.openingBalance || masterRec.opening_balance || masterRec.balance || 0) || 0;
        if (opDebit > 0 || opCredit > 0) {
            intrinsicBal = opDebit - opCredit;
        } else if (rawBal !== 0) {
            if (masterRec.balance_type === 'credit' || masterRec.balanceType === 'credit') {
                intrinsicBal = -Math.abs(rawBal);
            } else {
                intrinsicBal = Math.abs(rawBal);
            }
        }
    }

    // احتساب حركات دفتر الأستاذ العام من erp_gl_v1
    let glTxns = [];
    try {
        glTxns = JSON.parse(localStorage.getItem(getTenantKey('erp_gl_v1')) || localStorage.getItem('erp_gl_v1') || '[]');
    } catch(e) { glTxns = []; }
    if (!Array.isArray(glTxns)) glTxns = Object.values(glTxns || {});

    let runningBal = intrinsicBal;
    let hasGLTxns = false;
    let isVoucherAlreadyInGL = false;
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || '').trim();

    if (glTxns.length > 0) {
        const pRefPrefixed = (entityType === 'customer' ? 'cust-' : entityType === 'supplier' ? 'supp-' : entityType === 'bank' ? 'bank-' : entityType === 'safe' ? 'safe-' : 'exp-') + bareId;
        const normName = cleanName ? cleanName.replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').replace(/\s+/g, ' ').toLowerCase() : '';

        glTxns.forEach(t => {
            if (!t) return;
            const tAcc = String(t.account || '').trim();
            const cleanTAcc = tAcc.replace(/^(cust|supp|bank|safe|exp|sub|sust)-/i, '').trim();
            let matches = false;

            if (cleanId && (tAcc === cleanId || cleanTAcc === cleanId)) matches = true;
            else if (bareId && (cleanTAcc === bareId || tAcc === pRefPrefixed)) matches = true;
            else if (cleanName && (tAcc === cleanName || t.accountName === cleanName || t.partner === cleanName)) matches = true;
            else if (normName) {
                const normT = tAcc.replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').replace(/\s+/g, ' ').toLowerCase();
                const normP = String(t.partner || t.accountName || '').replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').replace(/\s+/g, ' ').toLowerCase();
                if (normT === normName || normP === normName) matches = true;
            }

            if (matches) {
                hasGLTxns = true;
                const deb = parseFloat(t.debit || t.dr || 0) || 0;
                const cred = parseFloat(t.credit || t.cr || 0) || 0;
                runningBal += (deb - cred);

                if (vNo && (t.ref === vNo || t.voucherNo === vNo || t.id === vNo)) {
                    isVoucherAlreadyInGL = true;
                }
            }
        });
    }

    // في حال عدم وجود حركات في الأستاذ العام، الاعتماد على currentBalance المسجل
    if (!hasGLTxns && masterRec && masterRec.currentBalance !== undefined && masterRec.currentBalance !== null) {
        const cb = parseFloat(masterRec.currentBalance) || 0;
        if (entityType === 'supplier') {
            runningBal = -cb; // في المحاسبة: التزام المورد دائن (-)
        } else {
            runningBal = cb;
        }
    }

    // تحويل رصيد الأستاذ العام إلى العملة الموحدة المختارة إذا اختلفت
    let finalBal = runningBal;
    if (nativeCurrency !== targetCurrency) {
        const rateToSDG_Native = (nativeCurrency === 'SDG') ? 1 : (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(nativeCurrency) : (nativeCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1));
        const rateToSDG_Target = (targetCurrency === 'SDG') ? 1 : (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(targetCurrency) : (targetCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1));

        const balInSDG = runningBal * (rateToSDG_Native > 0 ? rateToSDG_Native : 1);
        finalBal = (rateToSDG_Target > 0) ? (balInSDG / rateToSDG_Target) : balInSDG;
    }

    // إذا كان السند لم يتم ترحيله بعد لدفتر الأستاذ (لم يضغط حفظ أو قيد)، نحتسب أثره المستقبلي بالعملة الموحدة
    if (!isVoucherAlreadyInGL && voucherAmt > 0) {
        const isTargetDebtor = (targetRole === 'debtor');
        // في السند: الطرف المدين (المستلم) يزداد رصيده مديناً (+)، والطرف الدائن (الصارف) يزداد دائناً (-)
        const effect = isTargetDebtor ? voucherAmt : -voucherAmt;
        finalBal += effect;
    }

    // تحديد الموقف المحاسبي الدقيق والتوصيف المالي بالعملة الموحدة
    const absBal = Math.abs(finalBal);
    let status = 'balanced'; // 'debit', 'credit', 'balanced'
    let statusText = 'متوازن';
    let balanceDescription = '';
    let shortTag = '';

    if (absBal < 0.01) {
        status = 'balanced';
        statusText = 'متوازن';
        balanceDescription = `*0.00* ${targetCurrency} (متوازن ✅)`;
        shortTag = 'متوازن';
    } else if (finalBal > 0.01) { // مدين (DEBIT)
        status = 'debit';
        statusText = 'عليه';
        balanceDescription = `*${fmtNum(absBal)}* ${targetCurrency} (عليه 🔴)`;
        shortTag = 'عليه';
    } else { // دائن (CREDIT - finalBal < -0.01)
        status = 'credit';
        statusText = 'له';
        balanceDescription = `*${fmtNum(absBal)}* ${targetCurrency} (له 🟢)`;
        shortTag = 'له';
    }

    // احتساب الرصيد المقابل بالعملة الأجنبية
    let foreignCurrency = 'USD';
    if (targetCurrency === 'USD') {
        foreignCurrency = 'SDG';
    } else if (nativeCurrency && nativeCurrency !== targetCurrency && nativeCurrency !== 'SDG') {
        foreignCurrency = nativeCurrency;
    }

    const rateToSDG_TargetForForeign = (targetCurrency === 'SDG') ? 1 : ((typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(targetCurrency) : (targetCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1)) || 1);
    const rateToSDG_Foreign = (foreignCurrency === 'SDG') ? 1 : ((typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(foreignCurrency) : (foreignCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1)) || 2600);

    const balInSDG_Foreign = finalBal * (rateToSDG_TargetForForeign > 0 ? rateToSDG_TargetForForeign : 1);
    const rawForeignBal = (rateToSDG_Foreign > 0) ? (balInSDG_Foreign / rateToSDG_Foreign) : balInSDG_Foreign;
    const absForeignBal = Math.abs(rawForeignBal);

    let foreignBalanceDescription = '';
    if (absForeignBal < 0.01) {
        foreignBalanceDescription = `*0.00* ${foreignCurrency} (متوازن ✅)`;
    } else if (finalBal > 0.01) {
        foreignBalanceDescription = `*${fmtNum(absForeignBal)}* ${foreignCurrency} (عليه 🔴)`;
    } else {
        foreignBalanceDescription = `*${fmtNum(absForeignBal)}* ${foreignCurrency} (له 🟢)`;
    }

    return {
        amount: absBal,
        rawBalance: finalBal,
        currency: targetCurrency,
        nativeCurrency: nativeCurrency,
        foreignAmount: absForeignBal,
        foreignCurrency: foreignCurrency,
        foreignBalanceDescription: foreignBalanceDescription,
        entityType,
        status,
        statusText,
        balanceDescription,
        shortTag,
        partyName: cleanName
    };
};

// ═══ 3. منظومة استخراج أطراف السند والبيانات البنكية والشيكات ═══
window.getVoucherParties = function(blockId) {
    const vTypeEl = document.getElementById('voucher-type');
    const isPayment = vTypeEl ? (vTypeEl.value === 'payment') : true;

    // Main Account (Section 1)
    const mainAccSearch = document.getElementById('main-account-search');
    const mainAccIdEl = document.getElementById('main-account');
    const mainName = mainAccSearch ? (mainAccSearch.value.trim() || 'حساب الصرف/الخزينة') : 'حساب الصرف/الخزينة';
    const mainId = mainAccIdEl ? mainAccIdEl.value.trim() : '';
    const mainContact = window.getVoucherContactDetails(mainId, mainName);
    const mainPhone = mainContact ? (mainContact.phone || mainContact.whatsapp || '') : '';

    // Block Account (Section 2)
    let bId = blockId;
    if (!bId) {
        const firstBlock = document.querySelector('.account-block');
        if (firstBlock) bId = firstBlock.getAttribute('data-block-id');
    }

    let blockName = 'المستفيد';
    let blockIdVal = '';
    let blockAmt = 0;
    let statement = '';
    let ccName = '';
    let pmDetails = [];
    let structuredPayments = [];

    // دالة مساعدة لاستخراج تفاصيل الدفع من كارت محدد
    function extractBlockPayments(targetBid) {
        if (!targetBid) return;
        const blockEl = document.querySelector(`.account-block[data-block-id="${targetBid}"]`);

        // 1. تحويلات بنكية
        const bankRows = document.querySelectorAll(`#bank-rows-${targetBid} .bank-row`);
        bankRows.forEach(r => {
            const bAmt = cleanNum(r.querySelector('.block-bank-amount')?.value);
            const bName = r.querySelector('.block-bank-name-input')?.value?.trim();
            const bTx = r.querySelector('.block-transfer-no-input')?.value?.trim();
            const bDesc = r.querySelector('.block-bank-desc-input')?.value?.trim();
            if (bAmt > 0 || bTx || bName) {
                pmDetails.push(`🏦 تحويل بنكي: ${fmtNum(bAmt)} (${bName || 'بنك'}) | رقم التحويلة: ${bTx || 'غير محدد'}`);
                structuredPayments.push({
                    type: 'bank',
                    amount: bAmt,
                    bankName: bName,
                    transferNo: bTx,
                    desc: bDesc
                });
            }
        });

        // 2. شيكات
        const chRows = document.querySelectorAll(`#cheque-rows-${targetBid} .cheque-row`);
        chRows.forEach(r => {
            const cAmt = cleanNum(r.querySelector('.block-cheque-amount')?.value);
            const cBank = r.querySelector('.block-cheque-bank-input')?.value?.trim();
            const cNo = r.querySelector('.block-cheque-no-input')?.value?.trim();
            const cDue = r.querySelector('.block-cheque-date-input')?.value?.trim();
            const cDesc = r.querySelector('.block-cheque-desc-input')?.value?.trim();
            if (cAmt > 0 || cNo) {
                pmDetails.push(`✍️ شيك: ${fmtNum(cAmt)} (${cBank || 'بنك'}) | رقم الشيك: ${cNo || ''} ${cDue ? `(استحقاق: ${cDue})` : ''}`);
                structuredPayments.push({
                    type: 'cheque',
                    amount: cAmt,
                    bankName: cBank,
                    chequeNo: cNo,
                    dueDate: cDue,
                    desc: cDesc
                });
            }
        });

        // 3. نقدية (كاش)
        if (blockEl) {
            const cashAmtEl = blockEl.querySelector('.block-cash-amount');
            const cashSafeEl = blockEl.querySelector('.block-cash-safe');
            const cashDescEl = blockEl.querySelector('.block-cash-desc-input');
            const cashAmt = cleanNum(cashAmtEl?.value);
            const cashSafe = cashSafeEl ? (cashSafeEl.options ? cashSafeEl.options[cashSafeEl.selectedIndex]?.text : cashSafeEl.value) : '';
            const cashDesc = cashDescEl?.value?.trim() || '';
            if (cashAmt > 0) {
                pmDetails.push(`💵 نقداً: ${fmtNum(cashAmt)} (${cashSafe || 'الخزينة'})`);
                structuredPayments.push({
                    type: 'cash',
                    amount: cashAmt,
                    safeName: cashSafe,
                    desc: cashDesc
                });
            }
        }
    }

    if (bId) {
        const bAccSearch = document.getElementById(`account-${bId}-search`);
        const bAccId = document.getElementById(`account-${bId}-id`);
        const bAmtEl = document.getElementById(`amount-${bId}`);
        const bStmt = document.getElementById(`statement-${bId}`);
        const bCc = document.getElementById(`cost-center-${bId}`);

        if (bAccSearch && bAccSearch.value) blockName = bAccSearch.value.trim();
        if (bAccId && bAccId.value) blockIdVal = bAccId.value.trim();
        if (bAmtEl) blockAmt = cleanNum(bAmtEl.value);
        if (bStmt && bStmt.value) statement = bStmt.value.trim();
        if (bCc && bCc.value) ccName = bCc.value.trim();

        extractBlockPayments(bId);
    } else {
        // إذا لم يُحدد كارت معين، نجمع وسائل الدفع من كافة الكروت المتاحة
        const allBlocks = document.querySelectorAll('.account-block');
        allBlocks.forEach(blk => {
            const curBid = blk.getAttribute('data-block-id');
            extractBlockPayments(curBid);
        });
    }

    const blockContact = window.getVoucherContactDetails(blockIdVal, blockName);
    const blockPhone = blockContact ? (blockContact.phone || blockContact.whatsapp || '') : '';

    const activeCurrency = window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG';
    const grandAmount = (typeof window.getVoucherAmountForCurrency === 'function') 
        ? window.getVoucherAmountForCurrency(activeCurrency, bId) 
        : (blockAmt > 0 ? blockAmt : (cleanNum(document.getElementById('amount-local-hdr')?.value) || 0));
    const currency = activeCurrency;

    // في الصرف: الحساب الرئيسي دائن (صارف)، وكارت البند مدين (مستلم)
    // في القبض: الحساب الرئيسي مدين (قابض)، وكارت البند دائن (دافع)
    let debtorParty, creditorParty;

    if (isPayment) {
        debtorParty = {
            roleKey: 'debtor',
            roleTitle: 'إلى',
            name: blockName,
            id: blockIdVal,
            phone: blockPhone,
            isMain: false,
            blockId: bId
        };
        creditorParty = {
            roleKey: 'creditor',
            roleTitle: 'من',
            name: mainName,
            id: mainId,
            phone: mainPhone,
            isMain: true,
            blockId: null
        };
    } else {
        debtorParty = {
            roleKey: 'debtor',
            roleTitle: 'إلى',
            name: mainName,
            id: mainId,
            phone: mainPhone,
            isMain: true,
            blockId: null
        };
        creditorParty = {
            roleKey: 'creditor',
            roleTitle: 'من',
            name: blockName,
            id: blockIdVal,
            phone: blockPhone,
            isMain: false,
            blockId: bId
        };
    }

    return {
        debtor: debtorParty,
        creditor: creditorParty,
        amount: grandAmount,
        currency,
        isPayment,
        blockId: bId,
        statement,
        ccName,
        pmDetails,
        structuredPayments
    };
};

// ═══ 4. صياغة الإشعار المصرفي المعتمد للواتساب (3 نماذج: خط مصرفي، كمبيالة مؤطرة، كارت صورة) ═══

// النموذج 1: صيغة رسالة الواتساب المعتمدة للطرفين (المدين والدائن)
window.buildWATextLineMessage = function(targetType, parties) {
    if (!parties) parties = window.getVoucherParties();
    const isPayment = parties.isPayment;
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0];
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const tafqeetText = typeof window.formatArabicTafqeet === 'function' ? window.formatArabicTafqeet(amount, currency) : '';

    let companyName = 'الإدارة المالية';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const otherParty = isDebtor ? parties.creditor : parties.debtor;
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment, currency);

    let msg = `السلام عليكم ورحمة الله وبركاته\n`;
    msg += `عزيزنا: *${activeParty.name}* المحترم\n`;

    if (isDebtor) {
        msg += `نحيطكم علماً بأنه تم اضافه لحسابكم بالتفاصيل التالية:\n`;
    } else {
        if (otherParty && otherParty.name && otherParty.name !== 'المستفيد' && otherParty.name !== 'حساب غير محدد') {
            msg += `نحيطكم علماً بأنه تم خصم من حسابكم لصالح (*${otherParty.name}*) بالتفاصيل التالية:\n`;
        } else {
            msg += `نحيطكم علماً بأنه تم خصم من حسابكم بالتفاصيل التالية:\n`;
        }
    }

    msg += `━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    const cleanVNo = vNo.startsWith('#') ? vNo : '#' + vNo;
    msg += `📄  ${cleanVNo}\n`;
    msg += `📅 ${vDate}\n`;
    msg += `💰 *المبلغ:* *${fmtNum(amount)}* ${currency}\n`;
    if (tafqeetText) {
        msg += `✍️ *المبلغ كتابةً:* فقط ${tafqeetText}\n`;
    }
    if (parties.statement) {
        msg += `📝 *البيان:* ${parties.statement}\n`;
    }
    if (parties.ccName) {
        msg += `🏷️ *المشروع:* ${parties.ccName}\n`;
    }
    msg += `━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;

    if (parties.structuredPayments && parties.structuredPayments.length > 0) {
        msg += `💳 *طريقة السداد والتحويل:* \n`;
        parties.structuredPayments.forEach((p, idx) => {
            if (p.type === 'bank') {
                msg += `  🏦 *البنك:* ${p.bankName || 'تحويل بنكي'}\n`;
                if (p.transferNo) msg += `  🔢 *رقم التحويلة / الإشعار:* ${p.transferNo}\n`;
                msg += `  💵 *مبلغ التحويل:* *${fmtNum(p.amount)}* ${currency}\n`;
                if (p.desc) msg += `  📝 *بيان التحويل:* ${p.desc}\n`;
            } else if (p.type === 'cheque') {
                msg += `  ✍️ *شيك بنكي:* ${p.bankName ? `مسحوب على (${p.bankName})` : 'شيك بنكي'}\n`;
                if (p.chequeNo) msg += `  🔢 *رقم الشيك:* ${p.chequeNo}\n`;
                if (p.dueDate) msg += `  📅 *تاريخ الاستحقاق:* ${p.dueDate}\n`;
                msg += `  💵 *مبلغ الشيك:* *${fmtNum(p.amount)}* ${currency}\n`;
                if (p.desc) msg += `  📝 *بيان الشيك:* ${p.desc}\n`;
            } else if (p.type === 'cash') {
                msg += `  💵 *طريقة الدفع:* نقداً\n`;
                if (p.safeName) msg += `  🏛️ *الخزينة:* ${p.safeName}\n`;
                msg += `  💰 *المبلغ النقدي:* *${fmtNum(p.amount)}* ${currency}\n`;
                if (p.desc) msg += `  📝 *البيان:* ${p.desc}\n`;
            }
            if (idx < parties.structuredPayments.length - 1) {
                msg += `  ──────────────────────\n`;
            }
        });
        msg += `━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    } else if (parties.pmDetails && parties.pmDetails.length > 0) {
        msg += `💳 *وسيلة الدفع:*\n${parties.pmDetails.join('\n')}\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    }

    msg += `📊  ${balInfo.balanceDescription}\n`;
    if (balInfo.foreignBalanceDescription) {
        msg += `${balInfo.foreignBalanceDescription}\n`;
    }
    msg += `━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;

    msg += `شاكرين لكم\n`;
    msg += `🏢 *${companyName}* - إدارة الحسابات العامة`;
    return msg;
};

// النموذج 2: شكل ورقة / كمبيالة (سند مؤطر بنكياً وقانونياً)
window.buildWAPromissoryNoteMessage = function(targetType, parties) {
    if (!parties) parties = window.getVoucherParties();
    const isPayment = parties.isPayment;
    const vTypeName = isPayment ? 'صرف مالي' : 'قبض مالي';
    const vTitle = isPayment ? 'سند صرف مالي رسمي معتمد' : 'سند قبض مالي رسمي معتمد';
    const vSubTitle = isPayment ? '(كمبيالة صرف / إشعار خصم رسمي)' : '(كمبيالة قبض / إشعار توريد رسمي)';
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0];
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const tafqeetText = typeof window.formatArabicTafqeet === 'function' ? window.formatArabicTafqeet(amount, currency) : '';

    let companyName = 'الإدارة المالية';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const otherParty = isDebtor ? parties.creditor : parties.debtor;
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment);

    let msg = `╔══════════════════════════════════════╗\n`;
    msg += `║      🏛️ ${vTitle}       ║\n`;
    msg += `║       ${vSubTitle}       ║\n`;
    msg += `╚══════════════════════════════════════╝\n`;
    msg += `📋 *رقم السند:* #${vNo}\n`;
    msg += `📅 *تاريخ التحرير:* ${vDate}\n`;
    msg += `══════════════════════════════════════\n`;

    if (isPayment) {
        if (isDebtor) {
            msg += `أقر وأعترف بأننا قيدنا وصرفنا لحساب المكرم:\n`;
            msg += `👤 *المستفيد:* ${activeParty.name}\n`;
        } else {
            msg += `نحيطكم علماً بأنه تم قيد وخصم السند من حسابكم:\n`;
            msg += `👤 *الحساب المخصوم منه:* ${activeParty.name}\n`;
            msg += `👤 *لصالح المستفيد:* ${otherParty.name}\n`;
        }
    } else {
        if (isDebtor) {
            msg += `أقر وأعترف بأننا استلمنا وقيدنا لحسابكم:\n`;
            msg += `👤 *حساب القبض:* ${activeParty.name}\n`;
            msg += `👤 *من المكرم:* ${otherParty.name}\n`;
        } else {
            msg += `وصلنا واستلمنا من المكرم:\n`;
            msg += `👤 *الدافع / المسدد:* ${activeParty.name}\n`;
        }
    }

    msg += `💰 *مبلغاً وقدره:* *${fmtNum(amount)}* ${currency}\n`;
    if (tafqeetText) {
        msg += `✍️ *وقدره كتابةً:* فقط ${tafqeetText}\n`;
    }
    if (parties.statement) {
        msg += `📝 *وذلك عن:* ${parties.statement}\n`;
    }
    if (parties.ccName) {
        msg += `🏷️ *المشروع / مركز التكلفة:* ${parties.ccName}\n`;
    }
    msg += `══════════════════════════════════════\n`;

    if (parties.structuredPayments && parties.structuredPayments.length > 0) {
        msg += `💳 *بيانات السداد المعتمدة:*\n`;
        parties.structuredPayments.forEach((p, idx) => {
            if (p.type === 'bank') {
                msg += `  🏦 *البنك:* ${p.bankName || 'تحويل مصرفي'}\n`;
                if (p.transferNo) msg += `  🔢 *رقم التحويلة / الإشعار:* ${p.transferNo}\n`;
                msg += `  💵 *المبلغ:* *${fmtNum(p.amount)}* ${currency}\n`;
            } else if (p.type === 'cheque') {
                msg += `  ✍️ *شيك بنكي:* ${p.bankName ? `مسحوب على (${p.bankName})` : 'شيك'}\n`;
                if (p.chequeNo) msg += `  🔢 *رقم الشيك:* ${p.chequeNo}\n`;
                if (p.dueDate) msg += `  📅 *الاستحقاق:* ${p.dueDate}\n`;
                msg += `  💵 *المبلغ:* *${fmtNum(p.amount)}* ${currency}\n`;
            } else if (p.type === 'cash') {
                msg += `  💵 *طريقة الدفع:* نقداً (${p.safeName || 'الخزينة'})\n`;
                msg += `  💰 *المبلغ:* *${fmtNum(p.amount)}* ${currency}\n`;
            }
            if (idx < parties.structuredPayments.length - 1) msg += `  ──────────────────────\n`;
        });
        msg += `══════════════════════════════════════\n`;
    } else if (parties.pmDetails && parties.pmDetails.length > 0) {
        msg += `💳 *بيانات السداد:*\n${parties.pmDetails.join('\n')}\n══════════════════════════════════════\n`;
    }

    msg += `📊 *الموقف المالي الختامي (دفتر الأستاذ العام):*\n`;
    msg += `رصيد حسابكم الحالي طرفنا:\n`;
    msg += `${balInfo.balanceDescription}\n`;
    if (balInfo.foreignBalanceDescription) {
        msg += `${balInfo.foreignBalanceDescription}\n`;
    }
    msg += `══════════════════════════════════════\n`;
    msg += `          *ختم واعتماد الإدارة المالية*\n`;
    msg += `          [ معتمد ومقيد إلكترونياً ]\n`;
    msg += `🏢 *${companyName}* - إدارة الحسابات`;
    return msg;
};

// النموذج 3: نص مرافق لشكل الصورة (كارت السند المصور)
window.buildWAImageCardTextMessage = function(targetType, parties) {
    if (!parties) parties = window.getVoucherParties();
    const isPayment = parties.isPayment;
    const vTypeName = isPayment ? 'صرف مالي' : 'قبض مالي';
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0];
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment, currency);

    let companyName = 'الإدارة المالية';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    let msg = `السلام عليكم ورحمة الله وبركاته 🌸\n`;
    msg += `عزيزنا: *${activeParty.name}* المحترم\n\n`;
    msg += `مرفق لكم كارت إشعار سند *${vTypeName}* رقم (#${vNo}) بتاريخ ${vDate} بمبلغ (*${fmtNum(amount)}* ${currency}).\n`;
    if (parties.statement) msg += `📝 *البيان:* ${parties.statement}\n`;
    msg += `📊 *الموقف المالي بدفتر الأستاذ:* ${balInfo.balanceDescription}\n`;
    if (balInfo.foreignBalanceDescription) msg += `   ${balInfo.foreignBalanceDescription}\n`;
    msg += `\n(كارت السند المصور مرفق أعلاه 👆)\n`;
    msg += `🏢 *${companyName}* - إدارة الحسابات العامة`;
    return msg;
};

// المولد الشامل المعتمد للواتساب
window.buildWATailoredMessage = function(targetType, parties, formatType) {
    if (formatType === 'promissory_note_box') {
        return window.buildWAPromissoryNoteMessage(targetType, parties);
    }
    return window.buildWATextLineMessage(targetType, parties);
};

// ═══ رسم كارت السند المصور اللحظي على Canvas (فائق السرعة والدقة 0% تأخير) ═══
window.renderWAVoucherImageCard = function(targetType = 'debtor', parties) {
    if (!parties) {
        const blockId = document.getElementById('wa-modal-block-id')?.value;
        parties = window.getVoucherParties(blockId);
    }
    let canvas = document.getElementById('wa-voucher-canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'wa-voucher-canvas';
        canvas.style.display = 'none';
        document.body.appendChild(canvas);
    }
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const width = 900;
    const height = 540;
    canvas.width = width;
    canvas.height = height;

    const isPayment = parties.isPayment;
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0];
    const tafqeetText = typeof window.formatArabicTafqeet === 'function' ? window.formatArabicTafqeet(amount, currency) : '';
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment);

    let companyName = 'الإدارة المالية - الحسابات العامة';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    // 1. Background
    const bgGrad = ctx.createLinearGradient(0, 0, width, height);
    bgGrad.addColorStop(0, '#0a0f1d');
    bgGrad.addColorStop(1, '#1e293b');
    ctx.fillStyle = bgGrad;
    ctx.fillRect(0, 0, width, height);

    // Border
    ctx.strokeStyle = '#334155';
    ctx.lineWidth = 3;
    ctx.strokeRect(4, 4, width - 8, height - 8);

    // 2. Header Banner
    const hdrGrad = ctx.createLinearGradient(0, 0, width, 0);
    if (isPayment) {
        hdrGrad.addColorStop(0, '#881337');
        hdrGrad.addColorStop(0.5, '#991b1b');
        hdrGrad.addColorStop(1, '#7f1d1d');
    } else {
        hdrGrad.addColorStop(0, '#064e3b');
        hdrGrad.addColorStop(0.5, '#047857');
        hdrGrad.addColorStop(1, '#065f46');
    }
    ctx.fillStyle = hdrGrad;
    ctx.fillRect(6, 6, width - 12, 90);

    ctx.strokeStyle = '#fbbf24';
    ctx.lineWidth = 2.5;
    ctx.beginPath();
    ctx.moveTo(6, 96);
    ctx.lineTo(width - 6, 96);
    ctx.stroke();

    ctx.textAlign = 'right';
    ctx.direction = 'rtl';
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 26px "Tajawal", "Segoe UI", Arial';
    const mainTitle = isPayment ? 'سند صرف مالي رسمي معتمد' : 'سند قبض مالي رسمي معتمد';
    ctx.fillText(mainTitle, width - 35, 45);

    ctx.fillStyle = '#fde047';
    ctx.font = '15px "Tajawal", "Segoe UI", Arial';
    ctx.fillText(`🏛️ ${companyName}`, width - 35, 75);

    // Header Voucher # & Date Box (Left side)
    ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
    ctx.fillRect(25, 20, 240, 60);
    ctx.strokeStyle = 'rgba(251, 191, 36, 0.4)';
    ctx.lineWidth = 1;
    ctx.strokeRect(25, 20, 240, 60);

    ctx.textAlign = 'left';
    ctx.direction = 'ltr';
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 16px monospace';
    ctx.fillText(`NO: #${vNo}`, 40, 45);
    ctx.fillStyle = '#cbd5e1';
    ctx.font = '13px "Tajawal", Arial';
    ctx.fillText(`DATE: ${vDate}`, 40, 68);

    // 3. Body: Right Side (Party Details & Statement)
    ctx.direction = 'rtl';
    ctx.textAlign = 'right';

    // Recipient Box
    ctx.fillStyle = 'rgba(255, 255, 255, 0.04)';
    ctx.fillRect(width - 440, 115, 410, 80);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.12)';
    ctx.strokeRect(width - 440, 115, 410, 80);

    const roleLabel = isPayment ? (isDebtor ? 'إلى حساب (المستفيد / المستلم):' : 'الصرف خصماً من حساب:') : (isDebtor ? 'إلى حساب (حساب القبض):' : 'استلمنا من المكرم / الدافع:');
    ctx.fillStyle = isDebtor ? '#34d399' : '#fca5a5';
    ctx.font = 'bold 14px "Tajawal", Arial';
    ctx.fillText(roleLabel, width - 455, 140);

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 20px "Tajawal", Arial';
    ctx.fillText(activeParty.name || 'حساب غير محدد', width - 455, 175);

    // Statement Box
    ctx.fillStyle = 'rgba(255, 255, 255, 0.03)';
    ctx.fillRect(width - 440, 205, 410, 75);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
    ctx.strokeRect(width - 440, 205, 410, 75);

    ctx.fillStyle = '#94a3b8';
    ctx.font = 'bold 13px "Tajawal", Arial';
    ctx.fillText('📝 البيان والسبب:', width - 455, 228);

    ctx.fillStyle = '#e2e8f0';
    ctx.font = '15px "Tajawal", Arial';
    const stmt = parties.statement || 'سداد مستحقات مالية معتمدة';
    ctx.fillText(stmt.length > 45 ? stmt.substring(0, 45) + '...' : stmt, width - 455, 258);

    // Cost Center / Payment Method Box
    ctx.fillStyle = 'rgba(255, 255, 255, 0.03)';
    ctx.fillRect(width - 440, 290, 410, 105);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
    ctx.strokeRect(width - 440, 290, 410, 105);

    ctx.fillStyle = '#38bdf8';
    ctx.font = 'bold 13px "Tajawal", Arial';
    ctx.fillText('💳 طريقة السداد والتحويل المالي:', width - 455, 314);

    ctx.fillStyle = '#cbd5e1';
    ctx.font = '14px "Tajawal", Arial';
    let pmStr = 'نقداً عبر الخزينة';
    if (parties.structuredPayments && parties.structuredPayments.length > 0) {
        const p = parties.structuredPayments[0];
        if (p.type === 'bank') pmStr = `🏦 تحويل: ${p.bankName || 'بنك'} | رقم: ${p.transferNo || '-'}`;
        else if (p.type === 'cheque') pmStr = `✍️ شيك: ${p.bankName || 'بنك'} | رقم: ${p.chequeNo || '-'}`;
        else if (p.type === 'cash') pmStr = `💵 نقداً: ${p.safeName || 'الخزينة'}`;
    }
    ctx.fillText(pmStr, width - 455, 342);

    if (parties.ccName) {
        ctx.fillStyle = '#94a3b8';
        ctx.font = '13px "Tajawal", Arial';
        ctx.fillText(`🏷️ المشروع: ${parties.ccName}`, width - 455, 372);
    }

    // 4. Body: Left Side (Grand Amount & GL Balance)
    ctx.fillStyle = 'rgba(245, 158, 11, 0.07)';
    ctx.fillRect(30, 115, 390, 130);
    ctx.strokeStyle = '#f59e0b';
    ctx.lineWidth = 1.8;
    ctx.strokeRect(30, 115, 390, 130);

    ctx.fillStyle = '#fbbf24';
    ctx.font = 'bold 14px "Tajawal", Arial';
    ctx.fillText('💰 المبلغ المعتمد الصافي:', 405, 142);

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 30px monospace';
    ctx.fillText(`${fmtNum(amount)} ${currency}`, 405, 185);

    if (tafqeetText) {
        ctx.fillStyle = '#fde68a';
        ctx.font = '13px "Tajawal", Arial';
        const taf = `فقط ${tafqeetText}`;
        ctx.fillText(taf.length > 38 ? taf.substring(0, 38) + '...' : taf, 405, 222);
    }

    // General Ledger Balance Pill
    const isCreditBal = balInfo.status === 'credit';
    const isDebitBal = balInfo.status === 'debit';
    ctx.fillStyle = isCreditBal ? 'rgba(16, 185, 129, 0.12)' : (isDebitBal ? 'rgba(239, 68, 68, 0.12)' : 'rgba(148, 163, 184, 0.12)');
    ctx.fillRect(30, 260, 390, 65);
    ctx.strokeStyle = isCreditBal ? '#10b981' : (isDebitBal ? '#ef4444' : '#64748b');
    ctx.lineWidth = 1.2;
    ctx.strokeRect(30, 260, 390, 65);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '12px "Tajawal", Arial';
    ctx.fillText('📊 رصيد الحساب الختامي في دفتر الأستاذ العام:', 405, 284);

    ctx.fillStyle = isCreditBal ? '#86efac' : (isDebitBal ? '#fca5a5' : '#cbd5e1');
    ctx.font = 'bold 15px "Tajawal", Arial';
    ctx.fillText(`${fmtNum(balInfo.amount)} ${balInfo.currency} (${balInfo.shortTag})`, 405, 310);

    // Official Stamp / Watermark
    ctx.save();
    ctx.translate(140, 425);
    ctx.beginPath();
    ctx.arc(0, 0, 42, 0, Math.PI * 2);
    ctx.strokeStyle = '#fbbf24';
    ctx.lineWidth = 2.5;
    ctx.stroke();

    ctx.beginPath();
    ctx.arc(0, 0, 36, 0, Math.PI * 2);
    ctx.strokeStyle = 'rgba(251, 191, 36, 0.5)';
    ctx.setLineDash([4, 3]);
    ctx.lineWidth = 1.2;
    ctx.stroke();
    ctx.setLineDash([]);

    ctx.textAlign = 'center';
    ctx.fillStyle = '#fde047';
    ctx.font = 'bold 11px "Tajawal", Arial';
    ctx.fillText('معتمد ومقيد', 0, -8);
    ctx.fillText('نظامياً ✅', 0, 10);
    ctx.font = '9px "Tajawal", Arial';
    ctx.fillStyle = '#cbd5e1';
    ctx.fillText('إدارة الحسابات', 0, 24);
    ctx.restore();

    ctx.textAlign = 'right';
    ctx.fillStyle = '#64748b';
    ctx.font = '12px "Tajawal", Arial';
    ctx.fillText('🛡️ سند مالي إلكتروني معتمد رسمياً ومقيد بدفتر الأستاذ العام', width - 210, 435);

    // 5. Footer Bottom Bar
    ctx.fillStyle = '#070b14';
    ctx.fillRect(6, height - 38, width - 12, 32);
    ctx.fillStyle = '#475569';
    ctx.font = '11px "Tajawal", monospace';
    ctx.fillText(`ELSAJANA ERP • FINANCIAL VOUCHER SYSTEM • SECURE DISPATCH`, width - 35, height - 18);

    const previewImg = document.getElementById('wa-modal-image-preview');
    if (previewImg) {
        previewImg.src = canvas.toDataURL('image/png');
    }
};

window.copyWAVoucherImageToClipboard = function() {
    let canvas = document.getElementById('wa-voucher-canvas');
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    const targetType = (targetMode === 'both') ? activeTab : targetMode;

    window.renderWAVoucherImageCard(targetType, parties);
    canvas = document.getElementById('wa-voucher-canvas');

    if (!canvas) {
        showToast('تعذر العثور على كارت السند للتصوير', 'error');
        return;
    }
    canvas.toBlob(blob => {
        if (!blob) {
            showToast('تعذر تجهيز الصورة للنسخ', 'error');
            return;
        }
        if (navigator.clipboard && window.ClipboardItem) {
            const item = new ClipboardItem({ 'image/png': blob });
            navigator.clipboard.write([item]).then(() => {
                showToast('📋 تم نسخ صورة السند المصور للحافظة! يمكنك الآن لصقها بـ Ctrl+V في محادثة واتساب', 'success', 6000);
            }).catch(err => {
                console.warn('Clipboard write error:', err);
                showToast('💡 تم تجهيز الصورة، يمكنك حفظها بزر (حفظ صورة السند)', 'info', 6000);
            });
        } else {
            showToast('💡 يمكنك حفظ الصورة بزر (حفظ صورة السند) ثم إرسالها في محادثة واتساب', 'info', 6000);
        }
    }, 'image/png');
};

window.downloadWAVoucherImage = function() {
    let canvas = document.getElementById('wa-voucher-canvas');
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    const targetType = (targetMode === 'both') ? activeTab : targetMode;

    window.renderWAVoucherImageCard(targetType, parties);
    canvas = document.getElementById('wa-voucher-canvas');

    if (!canvas) {
        showToast('تعذر تجهيز كارت السند للتصوير', 'error');
        return;
    }

    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'voucher').trim();
    const a = document.createElement('a');
    a.download = `سند_مالي_${vNo}_${targetType}.png`;
    a.href = canvas.toDataURL('image/png');
    a.click();
    showToast('💾 تم تنزيل صورة كمبيالة السند المصورة كملف PNG بجودة فائقة', 'success');
};

window.copyFullTextMessage = function() {
    const isBoth = (document.getElementById('wa-modal-target-mode')?.value || 'both') === 'both';
    let text = '';
    if (isBoth) {
        text = document.getElementById('wa-both-preview-message')?.value || '';
    } else {
        text = document.getElementById('wa-modal-message')?.value || '';
    }
    if (!text) {
        showToast('لا يوجد نص للنسخ', 'warning');
        return;
    }
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('📋 تم نسخ نص كمبيالة السند بالكامل إلى الحافظة بنجاح!', 'success');
        }).catch(() => {
            showToast('تم تحديد النص، يمكنك نسخه بـ Ctrl+C', 'info');
        });
    }
};

// ═══ 1. توليد باركود فيكتور عالي الدقة للسند المالي بنظام Code 128 الدولي القياسي ═══
window.generateBarcodeSVG = function(vNo) {
    const code128Patterns = [
        "212222", "222122", "222221", "121223", "121322", "131222", "122213", "122312", "132212", "221213",
        "221312", "231212", "112232", "122132", "122231", "113222", "123122", "123221", "223211", "221132",
        "221231", "213212", "223112", "312131", "311222", "321122", "321221", "312212", "322112", "322211",
        "212123", "212321", "232121", "111323", "131123", "131321", "112313", "132113", "132311", "211313",
        "231113", "231311", "112133", "112331", "132131", "113123", "113321", "133121", "313121", "211331",
        "231131", "213113", "213311", "213131", "311123", "311321", "331121", "312113", "312311", "332111",
        "314111", "221411", "431111", "111224", "111422", "121124", "121421", "141122", "141221", "112214",
        "112412", "122114", "122411", "142112", "142211", "241211", "221114", "413111", "241112", "134111",
        "111242", "121142", "121241", "114212", "124112", "124211", "411212", "421112", "421211", "212141",
        "214121", "412121", "111143", "111341", "131141", "114113", "114311", "411113", "411311", "113141",
        "114131", "311141", "411131", "211412", "211214", "211232", "2331112"
    ];
    let clean = (vNo || 'M26090000').replace(/[^\x20-\x7E]/g, '');
    if (!clean) clean = "M26090000";
    let indices = [104]; // Code 128B start
    for (let i = 0; i < clean.length; i++) {
        indices.push(clean.charCodeAt(i) - 32);
    }
    let checksum = 104;
    for (let i = 1; i < indices.length; i++) {
        checksum += indices[i] * i;
    }
    indices.push(checksum % 103);
    indices.push(106); // Stop symbol

    let modules = [];
    indices.forEach(idx => {
        let pattern = code128Patterns[idx];
        if (pattern) {
            for (let j = 0; j < pattern.length; j++) {
                let width = parseInt(pattern[j]);
                let isBar = (j % 2 === 0);
                for (let k = 0; k < width; k++) {
                    modules.push(isBar);
                }
            }
        }
    });

    let rects = '';
    const barWidth = 1.25;
    const quietZone = 6;
    for (let i = 0; i < modules.length; i++) {
        if (modules[i]) {
            rects += `<rect x="${(quietZone + i * barWidth).toFixed(1)}" y="0" width="${barWidth}" height="26" fill="#0f172a" />`;
        }
    }
    const totalW = (quietZone * 2 + modules.length * barWidth).toFixed(1);
    return `<svg width="${totalW}" height="26" viewBox="0 0 ${totalW} 26" xmlns="http://www.w3.org/2000/svg" style="display:block;">${rects}</svg>`;
};

// ═══ 2. توليد باركود التحقق الثنائي QR Code عبر خادم الواتساب المدمج ═══
window._waQrCache = window._waQrCache || {};
window.getVoucherQRCode = async function(text) {
    if (window._waQrCache[text]) return window._waQrCache[text];
    try {
        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=generate-qr&text=${encodeURIComponent(text)}`);
        const data = await res.json();
        if (data && data.success && data.dataUrl) {
            window._waQrCache[text] = data.dataUrl;
            return data.dataUrl;
        }
    } catch(e) {}
    const fallbackUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(text)}`;
    window._waQrCache[text] = fallbackUrl;
    return fallbackUrl;
};

// ═══ 2.1 صياغة الكابشن والرسالة الرسمية المرفقة بكمبيالة السند PDF ═══
window.buildWAPromissoryPDFCaption = function(targetType = 'debtor', parties) {
    if (!parties) {
        const blockId = document.getElementById('wa-modal-block-id')?.value;
        parties = window.getVoucherParties(blockId);
    }
    const isPayment = parties?.isPayment !== false;
    const vTitle = isPayment ? 'سند صرف مالي رسمي معتمد' : 'سند قبض مالي رسمي معتمد';
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0];
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties?.debtor : parties?.creditor;
    const amount = parties?.amount || 0;
    const currency = parties?.currency || 'SDG';

    let caption = `🏛️ *${vTitle}* (كمبيالة مالية رسمية)\n`;
    caption += `📋 *رقم السند:* #${vNo}\n`;
    caption += `📅 *تاريخ التحرير:* ${vDate}\n`;
    caption += `👤 *المعني:* ${activeParty?.name || '—'}\n`;
    caption += `💰 *المبلغ الصافي:* *${fmtNum(amount)}* ${currency}\n`;
    if (parties?.statement) caption += `📝 *البيان:* ${parties.statement}\n`;
    caption += `📎 *مرفق أعلاه:* ورقة كمبيالة السند المعتمدة رسمياً والمقيدة إلكترونياً بنظام الحسابات ELSAJANA ERP (ملف PDF عالي الجودة) 📄✅`;
    return caption;
};

// ═══ 3. صياغة وتصميم ورقة كمبيالة السند المعتمدة بتصاميم وألوان وباركود احترافي (HTML Promissory Note) ═══
window.generatePromissoryNoteHTML = function(targetType = 'debtor', parties, qrDataUrl = '') {
    if (!parties) {
        const blockId = document.getElementById('wa-modal-block-id')?.value;
        parties = window.getVoucherParties(blockId);
    }
    const isPayment = parties.isPayment !== false;
    const vTitle = isPayment ? 'سند صرف مالي رسمي معتمد' : 'سند قبض مالي رسمي معتمد';
    const vSubTitle = isPayment ? 'كمبيالة صرف رسمية • إشعار خصم وقيد مالي' : 'كمبيالة قبض رسمية • إشعار إضافة وقيد مالي';
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'M26090000').trim();
    const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0];
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const tafqeetText = typeof window.formatArabicTafqeet === 'function' ? window.formatArabicTafqeet(amount, currency) : '';

    let companyName = 'الإدارة المالية';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const otherParty = isDebtor ? parties.creditor : parties.debtor;
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment);
    const isCredit = balInfo.status === 'credit';

    let acknowledgmentText = '';
    if (isPayment) {
        if (isDebtor) {
            acknowledgmentText = 'أقر وأعترف بأننا قيدنا وصرفنا لحساب المكرم';
        } else {
            acknowledgmentText = 'نحيطكم علماً بأنه تم قيد وخصم السند من حسابكم لصالح المكرم';
        }
    } else {
        if (isDebtor) {
            acknowledgmentText = 'أقر وأعترف بأننا استلمنا وقيدنا لحسابكم';
        } else {
            acknowledgmentText = 'وصلنا واستلمنا من المكرم';
        }
    }

    const barcodeSvg = window.generateBarcodeSVG(vNo);
    const qrPlaceholder = qrDataUrl || `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=VERIFIED_${vNo}`;

    // بيانات السداد
    let paymentMethodText = 'نقداً عبر الخزينة';
    let transferNoText = '—';
    let bankNameText = '';
    if (parties.structuredPayments && parties.structuredPayments.length > 0) {
        const p = parties.structuredPayments[0];
        if (p.type === 'bank') {
            paymentMethodText = 'تحويل مصرفي (Bank Transfer)';
            bankNameText = p.bankName || 'تحويل مصرفي';
            transferNoText = p.transferNo || '—';
        } else if (p.type === 'cheque') {
            paymentMethodText = 'شيك مصرفي (Bank Cheque)';
            bankNameText = p.bankName || 'شيك';
            transferNoText = p.chequeNo || '—';
        } else if (p.type === 'cash') {
            paymentMethodText = 'نقداً (Cash)';
            bankNameText = p.safeName || 'الخزينة';
            transferNoText = 'نقدي مباشر';
        }
    }

    return `
    <div class="promissory-note-card" style="background:#ffffff; color:#0f172a; font-family:'Tajawal', 'Segoe UI', Arial, sans-serif; direction:rtl; border:3.5px double #d97706; outline:2px solid #1e293b; border-radius:10px; padding:18px 22px; position:relative; box-sizing:border-box; width:100%; min-height:980px; height:990px; display:flex; flex-direction:column; justify-content:space-between; margin:0 auto; box-shadow:0 8px 25px rgba(0,0,0,0.12);">
        <!-- Watermark -->
        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; opacity:0.035; pointer-events:none; z-index:0; user-select:none;">
            <svg width="380" height="380" viewBox="0 0 24 24" fill="#0f172a"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
        </div>

        <!-- Top Header & Details Section -->
        <div style="position:relative; z-index:1;">
            <!-- 1. Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2.5px solid #b45309; padding-bottom:10px; margin-bottom:10px; gap:10px;">
                <!-- Right -->
                <div style="text-align:right; flex:1.2;">
                    <div style="font-size:1.30rem; font-weight:900; color:#0f172a; line-height:1.2;">${companyName}</div>
                    <div style="font-size:0.82rem; color:#b45309; font-weight:800; margin-top:2px;">الإدارة المالية والمصرفية • الحسابات المركزية</div>
                    <div style="font-size:0.78rem; color:#64748b; margin-top:3px;">
                        <span>تاريخ التحرير:&nbsp;</span><b dir="ltr">${vDate}</b>
                    </div>
                </div>

                <!-- Center -->
                <div style="text-align:center; flex:1.4;">
                    <div style="display:block; margin:0 auto; max-width:340px; background:linear-gradient(135deg, #1e293b, #0f172a); border:2px solid #d97706; border-radius:8px; padding:6px 14px; box-shadow:0 3px 10px rgba(0,0,0,0.15);">
                        <div style="color:#fbbf24; font-size:1.10rem; font-weight:900;">🏛️ ${vTitle}</div>
                        <div style="color:#cbd5e1; font-size:0.74rem; font-weight:800; margin-top:2px;">${vSubTitle}</div>
                    </div>
                    <div style="font-size:0.72rem; color:#059669; font-weight:900; margin-top:3px;">سند مالي معتمد وموثق رقمياً وفق المعايير المحاسبية</div>
                </div>

                <!-- Left -->
                <div style="text-align:left; flex:1.2; display:flex; flex-direction:column; align-items:flex-end;">
                    <div style="display:flex; gap:8px; align-items:center;">
                        <div style="text-align:right;">
                            <div style="font-size:0.70rem; color:#64748b; font-weight:800;">رقم السند المالي</div>
                            <div style="font-family:monospace; font-size:1.15rem; font-weight:900; color:#b45309; direction:ltr;">#${vNo}</div>
                            <div style="margin-top:2px;">${barcodeSvg}</div>
                        </div>
                        <div style="width:58px; height:58px; border:1.5px solid #cbd5e1; border-radius:6px; padding:2px; background:#fff; flex-shrink:0;">
                            <img id="wa-promissory-qr-img" src="${qrPlaceholder}" style="width:100%; height:100%; object-fit:contain;" alt="QR" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Two Parties Grid (من حساب / إلى حساب) -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                <!-- Party 1: الصارف (من حساب) -->
                <div style="background:linear-gradient(135deg, #fef2f2 0%, #fff 100%); border:1.5px solid #fca5a5; border-radius:8px; padding:8px 12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.82rem; font-weight:900; color:#991b1b;">📤 صُرف من حساب (طرف الصرف):</span>
                        ${!isDebtor ? '<span style="background:#ef4444; color:#fff; font-size:0.70rem; font-weight:900; padding:1px 7px; border-radius:8px;">نسخة الصارف المعتمدة</span>' : ''}
                    </div>
                    <div style="font-size:1.10rem; font-weight:900; color:#0f172a; margin-top:3px;">
                        ${parties.creditor?.name || 'حساب الصرف / الخزينة'}
                    </div>
                    ${parties.creditor?.phone ? `<div style="font-size:0.78rem; color:#dc2626; font-weight:800; font-family:monospace; direction:ltr; margin-top:2px;">📱 ${parties.creditor.phone}</div>` : ''}
                </div>

                <!-- Party 2: المستفيد / المستلم (إلى حساب) -->
                <div style="background:linear-gradient(135deg, #ecfdf5 0%, #fff 100%); border:1.5px solid #10b981; border-radius:8px; padding:8px 12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.82rem; font-weight:900; color:#065f46;">📥 قُيد وصُرف لحساب (المستفيد / المستلم):</span>
                        ${isDebtor ? '<span style="background:#10b981; color:#fff; font-size:0.70rem; font-weight:900; padding:1px 7px; border-radius:8px;">نسخة المستلم المعتمدة</span>' : ''}
                    </div>
                    <div style="font-size:1.10rem; font-weight:900; color:#0f172a; margin-top:3px;">
                        ${parties.debtor?.name || 'المستفيد'}
                    </div>
                    ${parties.debtor?.phone ? `<div style="font-size:0.78rem; color:#059669; font-weight:800; font-family:monospace; direction:ltr; margin-top:2px;">📱 ${parties.debtor.phone}</div>` : ''}
                </div>
            </div>

            <!-- 3. Legal Promissory Clause -->
            <div style="background:#fffbeb; border:1.5px solid #f59e0b; border-radius:8px; padding:8px 14px; margin-bottom:10px;">
                <div style="font-size:0.90rem; color:#78350f; font-weight:900; line-height:1.5;">
                    📜 <b>صيغة الإقرار المالي:&nbsp;</b>${acknowledgmentText} <b>(${activeParty?.name || 'المعني'})</b> مبلغاً وقدره الصافي المبيّن أدناه، وذلك بموجب القيود الحسابية الرسمية المعتمدة بنظام الحسابات.
                </div>
            </div>

            <!-- 4. Grand Financial Amount Box -->
            <div style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border:2px solid #d97706; border-radius:9px; padding:12px 18px; margin-bottom:10px; color:#ffffff; box-shadow:0 4px 14px rgba(15,23,42,0.12);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <div>
                        <div style="color:#fbbf24; font-size:0.86rem; font-weight:800;">💰 المبلغ المعتمد والمنصرف:</div>
                        <div style="font-size:2.05rem; font-weight:900; color:#34d399; font-family:monospace; letter-spacing:1px; margin-top:2px;" dir="ltr">
                            ${fmtNum(amount)} <span style="font-size:1.20rem; color:#fde047;">${currency}</span>
                        </div>
                    </div>
                    <div style="text-align:right; flex:1; max-width:460px;">
                        <div style="color:#94a3b8; font-size:0.80rem; font-weight:800;">✍️ وقدره كتابةً بالحروف (تفقيط معتمد):</div>
                        <div style="color:#fef08a; font-size:0.96rem; font-weight:900; line-height:1.45; margin-top:3px; background:rgba(255,255,255,0.08); padding:6px 10px; border-radius:6px; border:1px dashed rgba(251,191,36,0.5);">
                            فقط ${tafqeetText || '—'}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Statement & Purpose -->
            <div style="background:#f8fafc; border:1.5px solid #cbd5e1; border-radius:8px; padding:8px 12px; margin-bottom:10px;">
                <div style="display:flex; align-items:baseline; gap:8px;">
                    <span style="color:#334155; font-size:0.86rem; font-weight:900; white-space:nowrap;">📝 البيان والسبب:&nbsp;</span>
                    <span style="color:#0f172a; font-size:0.92rem; font-weight:900; flex:1; line-height:1.45;">${parties.statement || 'سداد مستحقات مالية معتمدة وفق القيود الحسابية'}</span>
                </div>
                ${parties.ccName ? `<div style="margin-top:4px; padding-top:4px; border-top:1px dashed #cbd5e1; font-size:0.80rem; color:#0369a1; font-weight:900;"><span>🏷️ المشروع أو مركز التكلفة:&nbsp;</span><b style="color:#0f172a;">${parties.ccName}</b></div>` : ''}
            </div>

            <!-- 6. Banking Settlement & Transfer Details -->
            <div style="border:1.5px solid #0d9488; border-radius:8px; overflow:hidden; margin-bottom:10px;">
                <div style="background:linear-gradient(90deg, #0f766e, #115e59); color:#ffffff; padding:5px 12px; font-size:0.85rem; font-weight:900; display:flex; align-items:center; justify-content:space-between;">
                    <span>💳 بيانات السداد والتحويل المالي المعتمدة:</span>
                    <span style="font-size:0.75rem; color:#a7f3d0; background:rgba(0,0,0,0.2); padding:2px 7px; border-radius:4px;">قيد مالي موثق ✅</span>
                </div>
                <div style="background:#f0fdfa; padding:8px 12px;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                        <tbody>
                            <tr>
                                <td style="padding:3px 6px; color:#475569; font-weight:800; width:18%;">طريقة السداد:</td>
                                <td style="padding:3px 6px; color:#0f172a; font-weight:900; width:32%;">${paymentMethodText}</td>
                                <td style="padding:3px 6px; color:#475569; font-weight:800; width:22%;">رقم التحويلة أو الإشعار:</td>
                                <td style="padding:3px 6px; color:#0f766e; font-weight:900; font-family:monospace; font-size:1.0rem; width:28%;" dir="ltr">${transferNoText}</td>
                            </tr>
                            ${bankNameText ? `
                            <tr>
                                <td style="padding:3px 6px; color:#475569; font-weight:800;">البنك أو الخزينة:</td>
                                <td style="padding:3px 6px; color:#0f172a; font-weight:900;">${bankNameText}</td>
                                <td style="padding:3px 6px; color:#475569; font-weight:800;">المبلغ المسدد:</td>
                                <td style="padding:3px 6px; color:#059669; font-weight:900; font-family:monospace;" dir="ltr">${fmtNum(amount)} ${currency}</td>
                            </tr>` : ''}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 7. General Ledger Account Balance -->
            <div style="background:#f8fafc; border:1.5px solid ${isCredit ? '#10b981' : '#ef4444'}; border-radius:8px; padding:8px 14px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div>
                    <div style="font-size:0.85rem; color:#334155; font-weight:900;">📊 الموقف المالي الختامي لحساب (${activeParty?.name || 'المعني'}):</div>
                    <div style="font-size:0.74rem; color:#64748b; margin-top:2px;">رصيد الحساب المالي في دفتر الأستاذ العام بعد قيد هذا السند مباشرة</div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="font-size:1.20rem; font-weight:900; color:#0f172a; font-family:monospace; background:#ffffff; padding:3px 10px; border-radius:6px; border:1.5px solid #cbd5e1;" dir="ltr">
                        ${fmtNum(balInfo.amount)} <span style="font-size:0.90rem; color:#64748b;">${balInfo.currency}</span>
                    </div>
                    <div style="background:${isCredit ? '#10b981' : '#ef4444'}; color:#ffffff; padding:4px 10px; border-radius:6px; font-weight:900; font-size:0.86rem;">
                        ${balInfo.shortTag} • ${isCredit ? 'رصيد دائن لصالحكم' : 'رصيد مدين طرفكم'}
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Signatures, Stamp & Security Stripe -->
        <div style="position:relative; z-index:1; margin-top:auto;">
            <!-- 8. Signatures & Official Stamp -->
            <div style="display:flex; justify-content:space-between; align-items:flex-end; padding-top:10px; border-top:2px solid #cbd5e1;">
                <!-- Accountant -->
                <div style="text-align:center; flex:1;">
                    <div style="font-size:0.82rem; color:#475569; font-weight:900;">المحاسب المالي (معد السند)</div>
                    <div style="margin:6px auto 3px auto; width:80px; height:1.5px; background:#94a3b8;"></div>
                    <div style="font-size:0.75rem; color:#059669; font-weight:900;">قيد آلي نظامي</div>
                </div>

                <!-- Official Stamp -->
                <div style="text-align:center; flex:1.2;">
                    <div style="display:inline-block; width:80px; height:80px; border:2.5px solid #b45309; border-radius:50%; padding:2px; background:rgba(254,243,199,0.3); transform:rotate(-5deg); box-shadow:0 0 0 2px #fff, 0 0 0 4px #b45309;">
                        <div style="width:100%; height:100%; border:1px dashed #b45309; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;">
                            <div style="font-size:0.60rem; color:#b45309; font-weight:900;">🏛️ الإدارة المالية</div>
                            <div style="font-size:0.70rem; color:#78350f; font-weight:900; margin:1px 0;">معتمد ومقيد</div>
                            <div style="font-size:0.60rem; color:#059669; font-weight:900;">إلكترونياً ✅</div>
                            <div style="font-size:0.54rem; color:#64748b; margin-top:1px;">${vDate}</div>
                        </div>
                    </div>
                </div>

                <!-- Controller -->
                <div style="text-align:center; flex:1;">
                    <div style="font-size:0.82rem; color:#475569; font-weight:900;">المدير المالي / الاعتماد</div>
                    <div style="margin:6px auto 3px auto; width:80px; height:1.5px; background:#94a3b8;"></div>
                    <div style="font-size:0.75rem; color:#0f172a; font-weight:900;">إجازة وصرف معتمد</div>
                </div>

                <!-- Recipient -->
                <div style="text-align:center; flex:1;">
                    <div style="font-size:0.82rem; color:#475569; font-weight:900;">المستلم / صاحب الحساب</div>
                    <div style="margin:6px auto 3px auto; width:80px; height:1.5px; background:#94a3b8;"></div>
                    <div style="font-size:0.75rem; color:#0369a1; font-weight:900;">${activeParty?.name || '—'}</div>
                </div>
            </div>

            <!-- 9. Document Bottom Security Stripe -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px dashed #cbd5e1; padding-top:6px; margin-top:8px; font-size:0.72rem; color:#64748b; font-weight:800;">
                <div>VERIFICATION: <span style="font-family:monospace; color:#0f172a; font-weight:900;">#DOC-${vNo}-${vDate.replace(/-/g, '')}</span></div>
                <div>وثيقة مالية معتمدة ومقيدة بالنظام المحاسبي العام ELSAJANA ERP 🔒</div>
            </div>
        </div>
    </div>
    `;
};

// ═══ 4. عرض كمبيالة السند المعتمدة في نافذة المعاينة الحية ═══
window.renderPromissoryNotePreview = function(targetType = 'debtor') {
    const container = document.getElementById('wa-promissory-note-container');
    if (!container) return;

    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'M26090000').trim();
    const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0];

    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;

    const qrText = `VERIFIED_VOUCHER|NO:${vNo}|DATE:${vDate}|AMT:${parties.amount}|PARTY:${activeParty.name}`;

    // رسم السند فوراً بالباركود
    container.innerHTML = window.generatePromissoryNoteHTML(targetType, parties, window._waQrCache[qrText] || '');

    // توليد وتحديث رمز QR عالي الدقة بصورة غير متزامنة
    window.getVoucherQRCode(qrText).then(dataUrl => {
        const qrImg = document.getElementById('wa-promissory-qr-img');
        if (qrImg) qrImg.src = dataUrl;
    });
};

// ═══ 5. توليد ملف PDF عالي الجودة للكمبيالة عبر html2pdf ═══
window.generatePromissoryNotePDFBlob = async function(targetType = 'debtor', parties) {
    if (!parties) {
        const blockId = document.getElementById('wa-modal-block-id')?.value;
        parties = window.getVoucherParties(blockId);
    }
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'M26090000').trim();
    const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0];
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;

    const qrText = `VERIFIED_VOUCHER|NO:${vNo}|DATE:${vDate}|AMT:${parties.amount}|PARTY:${activeParty.name}`;
    const qrDataUrl = await window.getVoucherQRCode(qrText);

    if (typeof html2pdf === 'undefined') {
        await new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = 'js/html2pdf.bundle.min.js';
            s.onload = resolve;
            s.onerror = () => reject(new Error('مكتبة html2pdf غير متوفرة'));
            document.head.appendChild(s);
        });
    }

    // إنشاء حاوية رسم A4 مستقلة ومثبتة دون التأثر بتمرير الشاشة (Scroll) لضمان رسم كامل التفاصيل من أعلى الصفحة بدون أي فراغ
    const renderWrapper = document.createElement('div');
    renderWrapper.style.position = 'fixed';
    renderWrapper.style.top = '0';
    renderWrapper.style.left = '0';
    renderWrapper.style.width = '780px';
    renderWrapper.style.zIndex = '-99999';
    renderWrapper.style.overflow = 'visible';
    renderWrapper.style.pointerEvents = 'none';

    const innerDiv = document.createElement('div');
    innerDiv.style.width = '780px';
    innerDiv.style.background = '#ffffff';
    innerDiv.style.margin = '0';
    innerDiv.style.padding = '0';
    innerDiv.innerHTML = window.generatePromissoryNoteHTML(targetType, parties, qrDataUrl);

    renderWrapper.appendChild(innerDiv);
    document.body.appendChild(renderWrapper);

    try {
        await new Promise(r => setTimeout(r, 60));
        const opt = {
            margin: [5, 5, 5, 5],
            filename: `كمبيالة_سند_${vNo}_${activeParty.name}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2, 
                useCORS: true, 
                letterRendering: false, 
                logging: false,
                scrollX: 0,
                scrollY: 0
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        const pdfBlob = await html2pdf().set(opt).from(innerDiv).output('blob');
        return pdfBlob;
    } finally {
        if (renderWrapper.parentNode) {
            renderWrapper.parentNode.removeChild(renderWrapper);
        }
    }
};

// ═══ 6. تنزيل ملف كمبيالة السند PDF مباشرة للمستخدم ═══
window.downloadPromissoryPDF = async function(targetType) {
    if (!targetType) {
        targetType = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    }
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'M26090000').trim();
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;

    showToast('⏳ جاري تجهيز وتنزيل ملف PDF المعتمد...', 'info', 2000);
    try {
        const blob = await window.generatePromissoryNotePDFBlob(targetType, parties);
        if (blob) {
            const safeName = (activeParty.name || targetType).replace(/[/\\?%*:|"<>]/g, '_');
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
            a.click();
            setTimeout(() => URL.revokeObjectURL(url), 5000);
            showToast('📥 تم تنزيل كمبيالة السند المعتمدة بنجاح!', 'success');
        }
    } catch(err) {
        showToast('تعذر تنزيل ملف PDF: ' + err.message, 'error');
    }
};

// ═══ 7. طباعة كمبيالة السند المعتمدة ═══
window.printPromissoryNote = function(targetType) {
    if (!targetType) {
        targetType = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    }
    const container = document.getElementById('wa-promissory-note-container');
    if (!container) return;

    const printWin = window.open('', '_blank', 'width=900,height=700');
    if (!printWin) {
        window.print();
        return;
    }
    printWin.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="utf-8">
            <title>طباعة كمبيالة السند المعتمد</title>
            <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;900&display=swap" rel="stylesheet">
            <style>
                body { margin: 20px; font-family: 'Tajawal', sans-serif; background: #fff; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            ${container.innerHTML}
            <script>
                window.onload = function() {
                    window.focus();
                    window.print();
                    setTimeout(function() { window.close(); }, 1000);
                };
            <\/script>
        </body>
        </html>
    `);
    printWin.document.close();
};

// ═══ 8. تبديل وضع العرض بين كمبيالة PDF والنص ═══
window.toggleWAPreviewMode = function() {
    const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
    const bothBox = document.getElementById('wa-preview-both-box');
    const singleBox = document.getElementById('wa-preview-single-box');
    const toggleBtn = document.getElementById('btn-wa-toggle-preview-mode');
    const isBoth = (document.getElementById('wa-modal-target-mode')?.value || 'both') === 'both';

    if (promissoryWrapper && (promissoryWrapper.style.display !== 'none')) {
        // التبديل إلى صندوق النص
        promissoryWrapper.style.display = 'none';
        if (isBoth) {
            if (bothBox) bothBox.style.display = 'flex';
        } else {
            if (singleBox) singleBox.style.display = 'flex';
        }
        if (toggleBtn) toggleBtn.innerHTML = '📄 عرض كمبيالة PDF';
        showToast('📝 تم التبديل إلى محاذاة النص', 'info');
    } else {
        // التبديل إلى كمبيالة PDF
        if (bothBox) bothBox.style.display = 'none';
        if (singleBox) singleBox.style.display = 'none';
        if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
        const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
        window.renderPromissoryNotePreview(activeTab);
        if (toggleBtn) toggleBtn.innerHTML = '📝 عرض النص';
        showToast('📄 تم التبديل إلى كمبيالة السند المعتمدة', 'info');
    }
};

// ═══ 9. إدارة نموذج الإشعار المعتمد (ورقة كمبيالة رسمية مؤطرة) ═══
window.setWAFormatType = function(formatType = 'promissory_note') {
    const fmtInput = document.getElementById('wa-modal-format-type');
    if (fmtInput) fmtInput.value = 'promissory_note';

    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';

    // تحديث صندوق الرسالة المفردة
    const msgInput = document.getElementById('wa-modal-message');
    if (msgInput) {
        const msg = window.buildWATailoredMessage(targetMode === 'both' ? 'debtor' : targetMode, parties, 'promissory_note');
        msgInput.value = msg;
        msgInput.dataset.defaultText = msg;
    }

    // تحديث ذاكرة رسائل الطرفين
    window._waBothPartyMessages = {
        debtor: window.buildWATailoredMessage('debtor', parties, 'promissory_note'),
        creditor: window.buildWATailoredMessage('creditor', parties, 'promissory_note')
    };

    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    const bothPreview = document.getElementById('wa-both-preview-message');
    if (bothPreview) {
        bothPreview.value = window._waBothPartyMessages[activeTab] || '';
    }

    window.renderPromissoryNotePreview(activeTab);
};

// ═══ 10. التبديل بين تبويب المستلم والصارف ═══
window._waBothPartyMessages = { debtor: '', creditor: '' };

window.switchWABothPreviewTab = function(tab = 'debtor') {
    const hiddenTab = document.getElementById('wa-both-active-tab');
    if (hiddenTab) hiddenTab.value = tab;

    const btnDebtor = document.getElementById('btn-wa-both-tab-debtor');
    const btnCreditor = document.getElementById('btn-wa-both-tab-creditor');
    const textarea = document.getElementById('wa-both-preview-message');

    if (btnDebtor && btnCreditor) {
        if (tab === 'debtor') {
            btnDebtor.style.background = 'rgba(16, 185, 129, 0.25)';
            btnDebtor.style.borderColor = '#10b981';
            btnDebtor.style.color = '#34d399';

            btnCreditor.style.background = 'rgba(255, 255, 255, 0.06)';
            btnCreditor.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            btnCreditor.style.color = '#cbd5e1';
        } else {
            btnCreditor.style.background = 'rgba(239, 68, 68, 0.25)';
            btnCreditor.style.borderColor = '#ef4444';
            btnCreditor.style.color = '#fca5a5';

            btnDebtor.style.background = 'rgba(255, 255, 255, 0.06)';
            btnDebtor.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            btnDebtor.style.color = '#cbd5e1';
        }
    }

    if (textarea) {
        textarea.value = window._waBothPartyMessages[tab] || '';
    }

    // تحديث المعاينة الحية للكمبيالة دائماً
    window.renderPromissoryNotePreview(tab);
};

window.saveWABothPreviewEdit = function(text) {
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    window._waBothPartyMessages[activeTab] = text;
};

window.resetWABothPreviewText = function() {
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    const fmtType = document.getElementById('wa-modal-format-type')?.value || 'text_line';

    const newMsg = window.buildWATailoredMessage(activeTab, parties, fmtType);
    window._waBothPartyMessages[activeTab] = newMsg;

    const textarea = document.getElementById('wa-both-preview-message');
    if (textarea) textarea.value = newMsg;
    showToast('تمت استعادة القالب الأصلي لهذه الرسالة', 'info');
};

window.buildWhatsAppRecipientMessage = function(blockId) {
    const parties = window.getVoucherParties(blockId);
    return window.buildWATailoredMessage('debtor', parties);
};

// ═══ 5. دالة حفظ كاش أرقام هواتف الأطراف اللحظي ═══
window.cachePartyPhone = function(partyType, phoneVal) {
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    if (!parties) return;
    const party = (partyType === 'debtor') ? parties.debtor : parties.creditor;
    const cleanPhone = (phoneVal || '').trim();
    if (cleanPhone && party) {
        try {
            if (party.id) localStorage.setItem(`erp_wa_phone_${party.id}`, cleanPhone);
            if (party.name) localStorage.setItem(`erp_wa_phone_${party.name}`, cleanPhone);
        } catch(e) {}
    }
};

// ═══ 5. التبديل المرن بين أطراف السند (المدين / الدائن / كليهما) وتحديث الرصيد اللحظي ═══
window.setWATargetMode = function(mode = 'both') {
    const modeInput = document.getElementById('wa-modal-target-mode');
    if (modeInput) modeInput.value = mode;

    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);

    const btnDebtor = document.getElementById('btn-wa-target-debtor');
    const btnCreditor = document.getElementById('btn-wa-target-creditor');
    const btnBoth = document.getElementById('btn-wa-target-both');
    const statusBadge = document.getElementById('wa-target-status-badge');

    const singleView = document.getElementById('wa-single-party-view');
    const bothView = document.getElementById('wa-both-parties-view');
    const desktopBtnText = document.getElementById('btn-wa-send-desktop-text');
    const webBtnText = document.getElementById('btn-wa-send-web-text') || document.getElementById('btn-text-wa-web');
    const gatewayBtnText = document.getElementById('btn-wa-send-gateway-text') || document.getElementById('btn-text-wa-gateway');

    // Reset styles
    [btnDebtor, btnCreditor, btnBoth].forEach(btn => {
        if (btn) {
            btn.classList.remove('active');
            btn.style.boxShadow = 'none';
        }
    });

    if (btnDebtor) {
        btnDebtor.style.background = 'rgba(16, 185, 129, 0.08)';
        btnDebtor.style.borderColor = 'rgba(16, 185, 129, 0.25)';
        btnDebtor.style.color = '#34d399';
    }
    if (btnCreditor) {
        btnCreditor.style.background = 'rgba(239, 68, 68, 0.08)';
        btnCreditor.style.borderColor = 'rgba(239, 68, 68, 0.25)';
        btnCreditor.style.color = '#fca5a5';
    }
    if (btnBoth) {
        btnBoth.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(239, 68, 68, 0.12) 100%)';
        btnBoth.style.borderColor = 'rgba(255, 255, 255, 0.2)';
        btnBoth.style.color = '#cbd5e1';
    }

    if (mode === 'debtor') {
        if (btnDebtor) {
            btnDebtor.classList.add('active');
            btnDebtor.style.background = 'rgba(16, 185, 129, 0.28)';
            btnDebtor.style.borderColor = '#10b981';
            btnDebtor.style.color = '#34d399';
            btnDebtor.style.boxShadow = '0 0 14px rgba(16, 185, 129, 0.4)';
        }
        if (statusBadge) {
            statusBadge.style.background = 'rgba(16, 185, 129, 0.18)';
            statusBadge.style.color = '#86efac';
            statusBadge.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            statusBadge.textContent = `محدد: إلى حساب (${parties.debtor.name})`;
        }
        if (singleView) singleView.style.display = 'flex';
        if (bothView) bothView.style.display = 'none';

        const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
        const singlePreviewBox = document.getElementById('wa-preview-single-box');
        const bothPreviewBox = document.getElementById('wa-preview-both-box');
        const bothTabs = document.getElementById('wa-both-tabs-toolbar');
        const imgContainer = document.getElementById('wa-modal-image-preview-container');

        const isTextMode = promissoryWrapper && promissoryWrapper.style.display === 'none';
        if (!isTextMode) {
            if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
            if (singlePreviewBox) singlePreviewBox.style.display = 'none';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        } else {
            if (singlePreviewBox) singlePreviewBox.style.display = 'flex';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        }
        if (bothTabs) bothTabs.style.display = 'none';
        if (imgContainer) imgContainer.style.display = 'none';
        try { window.renderPromissoryNotePreview('debtor'); } catch(e) {}
        try { window.renderWAVoucherImageCard('debtor', parties); } catch(e) {}

        const labelName = document.getElementById('wa-label-recipient-name');
        if (labelName) {
            labelName.textContent = `👤 إلى:`;
            labelName.style.color = '#34d399';
        }

        const nameInput = document.getElementById('wa-modal-recipient-name');
        if (nameInput) nameInput.value = parties.debtor.name;

        const phoneInput = document.getElementById('wa-modal-phone');
        if (phoneInput) phoneInput.value = parties.debtor.phone || '';

        // تحديث الرصيد اللحظي لدفتر الأستاذ في واجهة النافذة بالعملة الموحدة
        const curCurrencyDebtor = window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG';
        const debtorBal = window.getPartyAccountBalance(parties.debtor.id, parties.debtor.name, 'debtor', parties.amount, parties.isPayment, curCurrencyDebtor);
        const balEl = document.getElementById('wa-modal-live-balance');
        const balTag = document.getElementById('wa-modal-live-balance-tag');
        if (balEl) {
            balEl.textContent = `${fmtNum(debtorBal.amount)}`;
            const singleChip = document.getElementById('wa-modal-single-currency-chip');
            if (singleChip) singleChip.value = debtorBal.currency;
        }
        if (balTag) {
            balTag.textContent = debtorBal.shortTag;
            if (debtorBal.status === 'debit') {
                balTag.style.background = '#ef4444';
                balTag.style.color = '#ffffff';
                balTag.style.borderColor = '#f87171';
            } else if (debtorBal.status === 'credit') {
                balTag.style.background = '#10b981';
                balTag.style.color = '#022c22';
                balTag.style.borderColor = '#34d399';
            } else {
                balTag.style.background = '#475569';
                balTag.style.color = '#ffffff';
                balTag.style.borderColor = '#64748b';
            }
        }

        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const msgInput = document.getElementById('wa-modal-message');
        if (msgInput) {
            const msg = window.buildWATailoredMessage('debtor', parties, fmt);
            msgInput.value = msg;
            msgInput.dataset.defaultText = msg;
        }
        if (desktopBtnText) desktopBtnText.textContent = 'تطبيق واتساب (المستلم)';
        if (webBtnText) webBtnText.textContent = 'واتساب ويب (المستلم)';
        if (gatewayBtnText) gatewayBtnText.textContent = 'عبر الخادم (المستلم)';

    } else if (mode === 'creditor') {
        if (btnCreditor) {
            btnCreditor.classList.add('active');
            btnCreditor.style.background = 'rgba(239, 68, 68, 0.28)';
            btnCreditor.style.borderColor = '#ef4444';
            btnCreditor.style.color = '#fca5a5';
            btnCreditor.style.boxShadow = '0 0 14px rgba(239, 68, 68, 0.4)';
        }
        if (statusBadge) {
            statusBadge.style.background = 'rgba(239, 68, 68, 0.18)';
            statusBadge.style.color = '#fca5a5';
            statusBadge.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            statusBadge.textContent = `محدد: من (${parties.creditor.name})`;
        }
        if (singleView) singleView.style.display = 'flex';
        if (bothView) bothView.style.display = 'none';

        const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
        const singlePreviewBox = document.getElementById('wa-preview-single-box');
        const bothPreviewBox = document.getElementById('wa-preview-both-box');
        const bothTabs = document.getElementById('wa-both-tabs-toolbar');
        const imgContainer = document.getElementById('wa-modal-image-preview-container');

        const isTextMode = promissoryWrapper && promissoryWrapper.style.display === 'none';
        if (!isTextMode) {
            if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
            if (singlePreviewBox) singlePreviewBox.style.display = 'none';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        } else {
            if (singlePreviewBox) singlePreviewBox.style.display = 'flex';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        }
        if (bothTabs) bothTabs.style.display = 'none';
        if (imgContainer) imgContainer.style.display = 'none';

        try { window.renderPromissoryNotePreview('creditor'); } catch(e) {}
        try { window.renderWAVoucherImageCard('creditor', parties); } catch(e) {}

        const labelName = document.getElementById('wa-label-recipient-name');
        if (labelName) {
            labelName.textContent = `🏛️ من:`;
            labelName.style.color = '#f87171';
        }

        const nameInput = document.getElementById('wa-modal-recipient-name');
        if (nameInput) nameInput.value = parties.creditor.name;

        const phoneInput = document.getElementById('wa-modal-phone');
        if (phoneInput) phoneInput.value = parties.creditor.phone || '';

        // تحديث الرصيد اللحظي لدفتر الأستاذ في واجهة النافذة بالعملة الموحدة
        const curCurrencyCreditor = window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG';
        const creditorBal = window.getPartyAccountBalance(parties.creditor.id, parties.creditor.name, 'creditor', parties.amount, parties.isPayment, curCurrencyCreditor);
        const balEl = document.getElementById('wa-modal-live-balance');
        const balTag = document.getElementById('wa-modal-live-balance-tag');
        if (balEl) {
            balEl.textContent = `${fmtNum(creditorBal.amount)}`;
            const singleChip = document.getElementById('wa-modal-single-currency-chip');
            if (singleChip) singleChip.value = creditorBal.currency;
        }
        if (balTag) {
            balTag.textContent = creditorBal.shortTag;
            if (creditorBal.status === 'debit') {
                balTag.style.background = '#ef4444';
                balTag.style.color = '#ffffff';
                balTag.style.borderColor = '#f87171';
            } else if (creditorBal.status === 'credit') {
                balTag.style.background = '#10b981';
                balTag.style.color = '#022c22';
                balTag.style.borderColor = '#34d399';
            } else {
                balTag.style.background = '#475569';
                balTag.style.color = '#ffffff';
                balTag.style.borderColor = '#64748b';
            }
        }

        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const msgInput = document.getElementById('wa-modal-message');
        if (msgInput) {
            const msg = window.buildWATailoredMessage('creditor', parties, fmt);
            msgInput.value = msg;
            msgInput.dataset.defaultText = msg;
        }
        if (desktopBtnText) desktopBtnText.textContent = 'تطبيق واتساب (الصارف)';
        if (webBtnText) webBtnText.textContent = 'واتساب ويب (الصارف)';
        if (gatewayBtnText) gatewayBtnText.textContent = 'عبر الخادم (الصارف)';

    } else if (mode === 'both') {
        if (btnBoth) {
            btnBoth.classList.add('active');
            btnBoth.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.3) 0%, rgba(239, 68, 68, 0.3) 100%)';
            btnBoth.style.borderColor = '#fbbf24';
            btnBoth.style.color = '#fef08a';
            btnBoth.style.boxShadow = '0 0 14px rgba(251, 191, 36, 0.4)';
        }
        if (statusBadge) {
            statusBadge.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(239, 68, 68, 0.2) 100%)';
            statusBadge.style.color = '#fef08a';
            statusBadge.style.borderColor = 'rgba(251, 191, 36, 0.5)';
            statusBadge.textContent = 'محدد: إرسال لكليهما معاً (إلى + من) 👥';
        }
        if (singleView) singleView.style.display = 'none';
        if (bothView) bothView.style.display = 'flex';

        const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
        const singlePreviewBox = document.getElementById('wa-preview-single-box');
        const bothPreviewBox = document.getElementById('wa-preview-both-box');
        const bothTabs = document.getElementById('wa-both-tabs-toolbar');
        const imgContainer = document.getElementById('wa-modal-image-preview-container');

        const isTextMode = promissoryWrapper && promissoryWrapper.style.display === 'none';
        if (!isTextMode) {
            if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
            if (singlePreviewBox) singlePreviewBox.style.display = 'none';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        } else {
            if (bothPreviewBox) bothPreviewBox.style.display = 'flex';
            if (singlePreviewBox) singlePreviewBox.style.display = 'none';
        }
        if (bothTabs) bothTabs.style.display = 'flex';
        if (imgContainer) imgContainer.style.display = 'none';

        // Populate both with unified currency
        const curCurrency = window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG';
        const debtorBal = window.getPartyAccountBalance(parties.debtor.id, parties.debtor.name, 'debtor', parties.amount, parties.isPayment, curCurrency);
        const creditorBal = window.getPartyAccountBalance(parties.creditor.id, parties.creditor.name, 'creditor', parties.amount, parties.isPayment, curCurrency);

        const debtorRole = document.getElementById('wa-both-debtor-role-label');
        if (debtorRole) debtorRole.textContent = parties.debtor.roleTitle;
        const debtorName = document.getElementById('wa-both-debtor-name');
        if (debtorName) debtorName.textContent = parties.debtor.name;
        const debtorPhone = document.getElementById('wa-both-debtor-phone');
        if (debtorPhone) {
            let dVal = parties.debtor.phone || '';
            if (!dVal) {
                try {
                    dVal = (parties.debtor.id ? localStorage.getItem(`erp_wa_phone_${parties.debtor.id}`) : '') || 
                           (parties.debtor.name ? localStorage.getItem(`erp_wa_phone_${parties.debtor.name}`) : '') || '';
                } catch(e) {}
            }
            debtorPhone.value = dVal;
        }
        const dBalEl = document.getElementById('wa-both-debtor-balance');
        if (dBalEl) {
            const dTagBg = debtorBal.shortTag === 'له' ? '#10b981' : (debtorBal.shortTag === 'عليه' ? '#ef4444' : '#0284c7');
            const dCurrSelect = window.getWACurrencySelectOptions(debtorBal.currency);
            dBalEl.innerHTML = `<span style="color: #6ee7b7; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد المستلم:</span> <div style="display: inline-flex; align-items: center; gap: 6px;"><b style="color: #ffffff; background: #064e3b; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #10b981; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">${fmtNum(debtorBal.amount)}</b><select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">${dCurrSelect}</select><span style="background: ${dTagBg}; color: #ffffff; border: 1.5px solid rgba(255,255,255,0.4); padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">${debtorBal.shortTag}</span></div>`;
        }

        const creditorRole = document.getElementById('wa-both-creditor-role-label');
        if (creditorRole) creditorRole.textContent = parties.creditor.roleTitle;
        const creditorName = document.getElementById('wa-both-creditor-name');
        if (creditorName) creditorName.textContent = parties.creditor.name;
        const creditorPhone = document.getElementById('wa-both-creditor-phone');
        if (creditorPhone) {
            let cVal = parties.creditor.phone || '';
            if (!cVal) {
                try {
                    cVal = (parties.creditor.id ? localStorage.getItem(`erp_wa_phone_${parties.creditor.id}`) : '') || 
                           (parties.creditor.name ? localStorage.getItem(`erp_wa_phone_${parties.creditor.name}`) : '') || '';
                } catch(e) {}
            }
            creditorPhone.value = cVal;
        }
        const cBalEl = document.getElementById('wa-both-creditor-balance');
        if (cBalEl) {
            const cTagBg = creditorBal.shortTag === 'له' ? '#10b981' : (creditorBal.shortTag === 'عليه' ? '#ef4444' : '#0284c7');
            const cCurrSelect = window.getWACurrencySelectOptions(creditorBal.currency);
            cBalEl.innerHTML = `<span style="color: #fca5a5; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد الصرف:</span> <div style="display: inline-flex; align-items: center; gap: 6px;"><b style="color: #ffffff; background: #7f1d1d; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #ef4444; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">${fmtNum(creditorBal.amount)}</b><select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">${cCurrSelect}</select><span style="background: ${cTagBg}; color: #ffffff; border: 1.5px solid rgba(255,255,255,0.4); padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">${creditorBal.shortTag}</span></div>`;
        }

        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        window._waBothPartyMessages = {
            debtor: window.buildWATailoredMessage('debtor', parties, fmt),
            creditor: window.buildWATailoredMessage('creditor', parties, fmt)
        };
        const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
        window.switchWABothPreviewTab(activeTab);
        try { window.renderPromissoryNotePreview(activeTab); } catch(e) {}
        try { window.renderWAVoucherImageCard(activeTab, parties); } catch(e) {}

        if (desktopBtnText) desktopBtnText.textContent = 'تطبيق واتساب (الطرفين)';
        if (webBtnText) webBtnText.textContent = 'واتساب ويب (الطرفين)';
        if (gatewayBtnText) gatewayBtnText.textContent = 'عبر الخادم (الطرفين)';
    } else {
        if (desktopBtnText) desktopBtnText.textContent = 'تطبيق واتساب';
    }
};

window.swapWATargetParties = function() {
    const swapBtn = document.getElementById('btn-wa-swap-parties');
    if (swapBtn) {
        swapBtn.style.transform = 'rotate(180deg) scale(1.15)';
        setTimeout(() => {
            swapBtn.style.transform = 'rotate(0deg) scale(1)';
        }, 300);
    }

    const currentMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    if (currentMode === 'debtor') {
        window.setWATargetMode('creditor');
        showToast('⇄ تم التبديل إلى: من', 'info');
    } else if (currentMode === 'creditor') {
        window.setWATargetMode('both');
        showToast('⇄ تم التبديل إلى: كليهما معاً (إلى + من)', 'info');
    } else {
        window.setWATargetMode('debtor');
        showToast('⇄ تم التبديل إلى: إلى', 'info');
    }
};

window.openBlockWhatsAppDispatchModal = function(blockId) {
    if (!blockId) return;
    const modal = document.getElementById('wa-voucher-modal');
    if (!modal) return;

    if (typeof window.cancelHandoverTimer === 'function') {
        window.cancelHandoverTimer();
    }
    const handoverPanel = document.getElementById('wa-modal-handover-panel');
    if (handoverPanel) handoverPanel.style.display = 'none';

    const parties = window.getVoucherParties(blockId);
    if (parties.amount <= 0 && (!parties.debtor.name || parties.debtor.name === 'المستفيد')) {
        showToast('يرجى تحديد الحساب والمبلغ أولاً قبل إرسال الواتساب', 'warning');
        return;
    }

    const blockIdInput = document.getElementById('wa-modal-block-id');
    if (blockIdInput) blockIdInput.value = blockId;

    // Set names in top selector buttons
    const debtorAccName = document.getElementById('wa-debtor-acc-name');
    if (debtorAccName) debtorAccName.textContent = parties.debtor.name;
    const debtorTitle = document.getElementById('wa-debtor-role-title');
    if (debtorTitle) debtorTitle.textContent = parties.debtor.roleTitle;

    const creditorAccName = document.getElementById('wa-creditor-acc-name');
    if (creditorAccName) creditorAccName.textContent = parties.creditor.name;
    const creditorTitle = document.getElementById('wa-creditor-role-title');
    if (creditorTitle) creditorTitle.textContent = parties.creditor.roleTitle;

    // تهيئة العملة الموحدة للنافذة بناءً على عملة السند الحالية
    const defaultCurr = document.getElementById('currency')?.value || 'SDG';
    window._waCurrentModalCurrency = defaultCurr;
    const topCurrSelect = document.getElementById('wa-modal-currency-select');
    if (topCurrSelect) topCurrSelect.value = defaultCurr;

    // Default to BOTH (المستلم والصارف) and promissory note format
    window.setWATargetMode('both');
    window.setWAFormatType('promissory_note');

    // Reset cards to pending status
    if (typeof window.updatePartyCardStatus === 'function') {
        window.updatePartyCardStatus('debtor', false);
        window.updatePartyCardStatus('creditor', false);
    }

    // Reset big action button to initial state
    const mainActionBtn = document.getElementById('btn-wa-send-both-action');
    if (mainActionBtn) {
        mainActionBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%)';
        mainActionBtn.style.borderColor = '#34d399';
        mainActionBtn.style.boxShadow = '0 0 18px rgba(16, 185, 129, 0.4)';
        mainActionBtn.style.animation = 'none';
        mainActionBtn.innerHTML = `
            <span style="font-size: 1.45rem;">🚀</span>
            <span id="btn-wa-send-both-action-text">إرسال إلى كل الأطراف عبر الواتساب ⚡</span>
        `;
        mainActionBtn.onclick = function() {
            window.executeWASend('gateway');
        };
    }

    const alertEl = document.getElementById('wa-modal-status-alert');
    if (alertEl) alertEl.style.display = 'none';

    // Reset position cleanly so it appears centered without clipping
    const card = document.getElementById('wa-voucher-modal-card');
    if (card && !card.classList.contains('wa-maximized')) {
        card.style.position = 'relative';
        card.style.left = '';
        card.style.top = '';
        card.style.margin = 'auto';
    }

    modal.style.display = 'flex';
    try {
        const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
        const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
        const bothBox = document.getElementById('wa-preview-both-box');
        const singleBox = document.getElementById('wa-preview-single-box');
        const imgContainer = document.getElementById('wa-modal-image-preview-container');
        if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
        if (bothBox) bothBox.style.display = 'none';
        if (singleBox) singleBox.style.display = 'none';
        if (imgContainer) imgContainer.style.display = 'none';
        const toggleBtn = document.getElementById('btn-wa-toggle-preview-mode');
        if (toggleBtn) toggleBtn.innerHTML = '📄 PDF / 📝 نص';

        window.renderPromissoryNotePreview(activeTab);
        window.renderWAVoucherImageCard(activeTab, parties);
    } catch(e) {}

    if (typeof window.initWAVoucherModalDrag === 'function') {
        window.initWAVoucherModalDrag();
    }
};

window.closeWAVoucherModal = function() {
    if (window._waHandoverTimer) {
        clearInterval(window._waHandoverTimer);
        window._waHandoverTimer = null;
    }
    window._pendingHandoverData = null;
    const handoverPanel = document.getElementById('wa-modal-handover-panel');
    if (handoverPanel) handoverPanel.style.display = 'none';
    const modal = document.getElementById('wa-voucher-modal');
    if (modal) modal.style.display = 'none';
};

// ═══ تحكم النافذة: التكبير والاستعادة والسحب الحر بالماوس واللمس ═══
window.toggleWAModalMaximize = function() {
    const card = document.getElementById('wa-voucher-modal-card');
    const btn = document.getElementById('btn-wa-modal-maximize');
    if (!card) return;

    if (card.classList.contains('wa-maximized')) {
        // استعادة الحجم الطبيعي
        card.classList.remove('wa-maximized');
        card.style.width = card.dataset.origW || '1400px';
        card.style.height = card.dataset.origH || '92vh';
        card.style.maxWidth = '98vw';
        card.style.maxHeight = '940px';
        card.style.position = 'relative';
        card.style.left = '';
        card.style.top = '';
        card.style.margin = 'auto';
        if (btn) {
            btn.innerHTML = '🗖';
            btn.title = 'تكبير النافذة';
        }
    } else {
        // التكبير لملء الشاشة تقريباً
        card.dataset.origW = card.style.width || '1400px';
        card.dataset.origH = card.style.height || '92vh';
        card.classList.add('wa-maximized');
        card.style.position = 'fixed';
        card.style.left = '1vw';
        card.style.top = '1vh';
        card.style.width = '98vw';
        card.style.height = '98vh';
        card.style.maxWidth = '98vw';
        card.style.maxHeight = '98vh';
        card.style.margin = '0';
        if (btn) {
            btn.innerHTML = '🗗';
            btn.title = 'استعادة الحجم الطبيعي';
        }
    }
};

window.makeModalDraggable = function(cardId, headerId) {
    const card = document.getElementById(cardId);
    const header = document.getElementById(headerId);
    if (!card || !header || header.dataset.dragInit === 'true') return;
    header.dataset.dragInit = 'true';

    let isDragging = false;
    let startX, startY;
    let initialLeft, initialTop;

    const beginDrag = (clientX, clientY) => {
        if (card.classList.contains('wa-maximized')) return false;
        isDragging = true;
        startX = clientX;
        startY = clientY;

        const rect = card.getBoundingClientRect();
        initialLeft = rect.left;
        initialTop = rect.top;

        card.style.position = 'fixed';
        card.style.left = initialLeft + 'px';
        card.style.top = initialTop + 'px';
        card.style.margin = '0';
        card.style.transform = 'none';

        document.body.style.userSelect = 'none';
        return true;
    };

    header.addEventListener('mousedown', (e) => {
        if (e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) return;
        if (!beginDrag(e.clientX, e.clientY)) return;

        const onMouseMove = (ev) => {
            if (!isDragging) return;
            ev.preventDefault();
            const dx = ev.clientX - startX;
            const dy = ev.clientY - startY;

            let newLeft = initialLeft + dx;
            let newTop = initialTop + dy;

            const maxLeft = Math.max(10, window.innerWidth - 80);
            const maxTop = Math.max(10, window.innerHeight - 50);
            newLeft = Math.max(10, Math.min(maxLeft, newLeft));
            newTop = Math.max(10, Math.min(maxTop, newTop));

            card.style.left = newLeft + 'px';
            card.style.top = newTop + 'px';
        };

        const onMouseUp = () => {
            isDragging = false;
            document.body.style.userSelect = '';
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });

    header.addEventListener('touchstart', (e) => {
        if (e.target.closest('button') || e.target.closest('input')) return;
        const touch = e.touches[0];
        if (!touch) return;
        if (!beginDrag(touch.clientX, touch.clientY)) return;

        const onTouchMove = (ev) => {
            if (!isDragging) return;
            const t = ev.touches[0];
            if (!t) return;
            const dx = t.clientX - startX;
            const dy = t.clientY - startY;

            let newLeft = initialLeft + dx;
            let newTop = initialTop + dy;
            newLeft = Math.max(10, Math.min(window.innerWidth - 80, newLeft));
            newTop = Math.max(10, Math.min(window.innerHeight - 50, newTop));

            card.style.left = newLeft + 'px';
            card.style.top = newTop + 'px';
        };

        const onTouchEnd = () => {
            isDragging = false;
            document.body.style.userSelect = '';
            document.removeEventListener('touchmove', onTouchMove);
            document.removeEventListener('touchend', onTouchEnd);
        };

        document.addEventListener('touchmove', onTouchMove, { passive: true });
        document.addEventListener('touchend', onTouchEnd);
    }, { passive: true });
};

window.initWAVoucherModalDrag = function() {
    window.makeModalDraggable('wa-voucher-modal-card', 'wa-voucher-modal-header');
    window.makeModalDraggable('wa-batch-voucher-modal-card', 'wa-batch-voucher-modal-header');
};

window.resetWAPreviewText = function() {
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const currentMode = document.getElementById('wa-modal-target-mode')?.value || 'debtor';
    const parties = window.getVoucherParties(blockId);
    const msgInput = document.getElementById('wa-modal-message');
    if (msgInput) {
        msgInput.value = window.buildWATailoredMessage(currentMode, parties);
    }
};

window.markBlockAsSentWA = function(blockId, phone) {
    if (!blockId) return;
    const badge = document.getElementById(`wa-dispatch-badge-${blockId}`);
    if (badge) {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
        badge.innerHTML = `<span>✅ تم الإرسال واتساب (${timeStr})</span>`;
        badge.style.display = 'inline-flex';
        badge.title = `أُرسل للرقم: ${phone || 'مسجل'} في ${timeStr}`;
    }
};

// ═══════════════════════════════════════════════════════════════════
// WhatsApp Instant & Auto-Dispatch on Voucher Save Engine
// ═══════════════════════════════════════════════════════════════════
const WA_AUTO_SETTINGS_KEY = 'erp_wa_auto_dispatch_settings';

window.getWAAutoDispatchSettings = function() {
    try {
        const raw = localStorage.getItem(WA_AUTO_SETTINGS_KEY);
        if (raw) return JSON.parse(raw);
    } catch(e) {}
    return {
        enabled: false,
        target: 'both',   // 'both' | 'debtor' | 'creditor' (افتراضي لكليهما معاً)
        method: 'gateway'   // 'gateway' (خادم الواتساب المباشر بالخلفية)
    };
};

window.saveWAAutoDispatchSettings = function(settings) {
    try {
        localStorage.setItem(WA_AUTO_SETTINGS_KEY, JSON.stringify(settings));
    } catch(e) {}
    window.syncWAAutoDispatchUI();
};

window.syncWAAutoDispatchUI = function() {
    const s = window.getWAAutoDispatchSettings();
    const toggleMain = document.getElementById('wa-auto-send-toggle');
    const targetSelect = document.getElementById('wa-auto-send-target');
    const methodSelect = document.getElementById('wa-auto-send-method');
    const toggleModal = document.getElementById('wa-modal-auto-dispatch-sync');
    const bar = document.getElementById('wa-auto-dispatch-bar');

    if (toggleMain) toggleMain.checked = !!s.enabled;
    if (toggleModal) toggleModal.checked = !!s.enabled;
    if (targetSelect) targetSelect.value = s.target || 'both';
    if (methodSelect) methodSelect.value = s.method || 'gateway';

    if (bar) {
        if (s.enabled) {
            bar.style.borderColor = '#25d366';
            bar.style.boxShadow = '0 0 16px rgba(37, 211, 102, 0.45)';
            bar.style.background = 'rgba(15, 23, 42, 0.92)';
        } else {
            bar.style.borderColor = 'rgba(37, 211, 102, 0.3)';
            bar.style.boxShadow = 'none';
            bar.style.background = 'rgba(15, 23, 42, 0.75)';
        }
    }

    // ═══ Sync Unified Footer Bar Auto-Send Control Button ═══
    const toggleBtn = document.getElementById('btn-toggle-auto-send');
    const optBtn = document.getElementById('btn-auto-send-options');
    const autoSendIcon = document.getElementById('auto-send-icon');
    const autoSendLabel = document.getElementById('auto-send-label');
    const autoSendPill = document.getElementById('auto-send-status-pill');
    const saveBtn = document.getElementById('btn-save');

    if (toggleBtn) {
        if (s.enabled) {
            toggleBtn.className = 'btn crystal-btn btn-auto-send-toggle active';
            if (optBtn) optBtn.className = 'btn crystal-btn btn-auto-send-options active';
            
            const targetDesc = s.target === 'both' ? 'لكليهما' : (s.target === 'debtor' ? 'المستلم' : 'الصارف');
            toggleBtn.title = `الوضع الحالي: إرسال تلقائي مفعّل (${targetDesc}) ⚡ - سيتم إرسال الكمبيالة/السند فور الحفظ. انقر للتحويل إلى: حفظ فقط بدون إرسال`;
            if (optBtn) optBtn.title = `خيارات وجهة الإرسال التلقائي (الحالي: ${targetDesc}) - انقر لتغيير الوجهة`;
            
            if (autoSendIcon) autoSendIcon.innerHTML = '⚡';
            if (autoSendLabel) autoSendLabel.textContent = 'إرسال تلقائي:';
            if (autoSendPill) {
                autoSendPill.textContent = `مفعّل (${targetDesc})`;
                autoSendPill.style.background = '#10b981';
                autoSendPill.style.color = '#ffffff';
                autoSendPill.style.boxShadow = '0 0 10px rgba(16,185,129,0.5)';
            }
        } else {
            toggleBtn.className = 'btn crystal-btn btn-auto-send-toggle inactive';
            if (optBtn) optBtn.className = 'btn crystal-btn btn-auto-send-options inactive';
            
            toggleBtn.title = 'الوضع الحالي: حفظ فقط 💾 - لن يتم إرسال أي كمبيالة أو إشعار. انقر لتفعيل الإرسال التلقائي مع الحفظ';
            if (optBtn) optBtn.title = 'خيارات وجهة الإرسال التلقائي - انقر للاختيار والتفعيل';
            
            if (autoSendIcon) autoSendIcon.innerHTML = '💾';
            if (autoSendLabel) autoSendLabel.textContent = 'إرسال تلقائي:';
            if (autoSendPill) {
                autoSendPill.textContent = 'معطّل (حفظ فقط)';
                autoSendPill.style.background = 'rgba(148, 163, 184, 0.25)';
                autoSendPill.style.color = '#cbd5e1';
                autoSendPill.style.boxShadow = 'none';
            }
        }
    }

    if (saveBtn && !window.IdempotencyGuard?.isSaving && !saveBtn.disabled) {
        if (s.enabled) {
            saveBtn.title = 'حفظ واعتماد السند وإرسال الكمبيالة مباشرة عبر الواتساب ⚡';
            saveBtn.innerHTML = `
                <span>✅</span>
                <span>حفظ وإرسال السند</span>
                <span style="font-size: 0.8rem; background: rgba(0,0,0,0.28); padding: 1px 7px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.3); font-weight: 800;">⚡ إرسال</span>
            `;
        } else {
            saveBtn.title = 'حفظ واعتماد وترحيل السند المالي فقط في النظام (بدون إرسال)';
            saveBtn.innerHTML = `
                <span>✅</span>
                <span>حفظ واعتماد السند</span>
            `;
        }
    }
};

window.toggleAutoSendMode = function() {
    const s = window.getWAAutoDispatchSettings();
    const newEnabled = !s.enabled;
    window.toggleWAAutoDispatch(newEnabled);
};

window.toggleAutoSendDropdown = function(e) {
    if (e) {
        e.stopPropagation();
        e.preventDefault();
    }
    const menu = document.getElementById('auto-send-dropdown-menu');
    if (!menu) return;
    const isShowing = (menu.style.display === 'block');
    menu.style.display = isShowing ? 'none' : 'block';
};

window.selectAutoSendOption = function(enabled, target = null) {
    const menu = document.getElementById('auto-send-dropdown-menu');
    if (menu) menu.style.display = 'none';

    const s = window.getWAAutoDispatchSettings();
    s.enabled = !!enabled;
    if (target) {
        s.target = target;
    }
    window.saveWAAutoDispatchSettings(s);

    if (s.enabled) {
        const targetTitle = s.target === 'both' ? 'لكلا الطرفين (المستلم + الصارف)' : (s.target === 'debtor' ? 'للطرف المستلم فقط' : 'للطرف الصارف فقط');
        showToast(`⚡ تم تفعيل الإرسال التلقائي: سيتم إرسال الكمبيالة/السند مباشرة فور الحفظ (${targetTitle})`, 'success');
    } else {
        showToast('💾 تم ضبط الحفظ فقط: لن يتم إرسال أي كمبيالة أو سند تلقائياً عند الحفظ', 'info');
    }
};

// Close auto-send dropdown on click outside
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('auto-send-control-wrapper');
    const menu = document.getElementById('auto-send-dropdown-menu');
    if (menu && menu.style.display === 'block' && wrap && !wrap.contains(e.target)) {
        menu.style.display = 'none';
    }
});

window.toggleWAAutoDispatch = function(checked) {
    const s = window.getWAAutoDispatchSettings();
    s.enabled = !!checked;
    window.saveWAAutoDispatchSettings(s);
    if (s.enabled) {
        const targetTitle = s.target === 'both' ? 'لكلا الطرفين (المستلم + الصارف)' : (s.target === 'debtor' ? 'للمستلم فقط' : 'للصارف فقط');
        showToast(`⚡ تم تفعيل الإرسال التلقائي للواتساب عند الاعتماد (${targetTitle})`, 'success');
    } else {
        showToast('⏸️ تم إيقاف الإرسال التلقائي للواتساب (حفظ فقط بدون إرسال)', 'info');
    }
};

window.changeWAAutoDispatchTarget = function(val) {
    const s = window.getWAAutoDispatchSettings();
    s.target = val;
    window.saveWAAutoDispatchSettings(s);
    showToast(`🎯 الوجهة التلقائية: ${val === 'both' ? 'كليهما معاً (إلى + من)' : (val === 'debtor' ? 'إلى' : 'من')}`, 'info');
};

window.changeWAAutoDispatchMethod = function(val) {
    const s = window.getWAAutoDispatchSettings();
    s.method = val;
    window.saveWAAutoDispatchSettings(s);
    const methodDesc = val === 'desktop' ? 'تطبيق واتساب الخاطف (صفر استهلاك رامات)' : (val === 'web' ? 'واتساب ويب الموحد' : 'خادم الواتساب بالخلفية');
    showToast(`⚡ أسلوب الإرسال: ${methodDesc}`, 'info');
};

// Auto WhatsApp Dispatch trigger when voucher is successfully saved and posted
window.triggerAutoWADispatchOnSave = function(voucher, vNo) {
    const s = window.getWAAutoDispatchSettings();
    if (!s || !s.enabled) return;

    const parties = window.getVoucherParties();
    if (!parties) return;

    const targetMode = s.target || 'both';
    const method = s.method || 'gateway';

    setTimeout(() => {
        const blocks = document.querySelectorAll('.account-block');
        const isPayment = parties.isPayment;

        // 1. إرسال لطرف الحساب الرئيسي (الصرف أو القبض)
        const mainPartyObj = isPayment ? parties.creditor : parties.debtor;
        const mainCleanPhone = mainPartyObj.phone ? window.normalizePhoneNumber(mainPartyObj.phone) : '';
        const shouldSendMain = (targetMode === 'both' || (isPayment && targetMode === 'creditor') || (!isPayment && targetMode === 'debtor'));

        if (shouldSendMain && mainCleanPhone) {
            const mainType = isPayment ? 'creditor' : 'debtor';
            const mainMsg = window.buildWATailoredMessage(mainType, parties);
            window.sendDirectWABackground('gateway', mainCleanPhone, mainMsg, mainPartyObj.name);
        }

        // 2. إرسال لكافة بنود المستلمين / الأطراف الفرعية في السند
        const shouldSendBlocks = (targetMode === 'both' || (isPayment && targetMode === 'debtor') || (!isPayment && targetMode === 'creditor'));
        if (shouldSendBlocks && blocks.length > 0) {
            blocks.forEach((b, idx) => {
                const bid = b.getAttribute('data-block-id');
                const bParties = window.getVoucherParties(bid);
                const blockPartyObj = isPayment ? bParties.debtor : bParties.creditor;
                const blockCleanPhone = blockPartyObj.phone ? window.normalizePhoneNumber(blockPartyObj.phone) : '';
                if (blockCleanPhone) {
                    setTimeout(() => {
                        const blockType = isPayment ? 'debtor' : 'creditor';
                        const blockMsg = window.buildWATailoredMessage(blockType, bParties);
                        window.sendDirectWABackground('gateway', blockCleanPhone, blockMsg, blockPartyObj.name);
                    }, (idx + 1) * 400);
                }
            });
        }
    }, 450);
};

window.sendDirectWABackground = function(method = 'gateway', cleanPhone, message, recipientName = '') {
    if (!message) return;
    if (!cleanPhone) {
        console.warn('Auto WA Gateway: Phone is empty for', recipientName);
        return;
    }
    const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
    const formData = new FormData();
    formData.append('userId', userId);
    formData.append('phone', cleanPhone);
    formData.append('message', message);
    fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(`✅ تم إرسال إشعار واتساب تلقائياً إلى: ${recipientName || cleanPhone}`, 'success');
        } else {
            console.warn('WA send error:', data.error);
        }
    })
    .catch(err => console.error('WA send error:', err));
};

// ═══ تحديث الحالة البصرية لبطاقة الطرف (المستلم / الصارف) ═══
window.updatePartyCardStatus = function(partyType, isSent) {
    const isDebtor = (partyType === 'debtor');
    const tagEl = document.getElementById(isDebtor ? 'wa-debtor-status-tag' : 'wa-creditor-status-tag');
    const cardEl = document.getElementById(isDebtor ? 'wa-both-debtor-card' : 'wa-both-creditor-card');

    if (tagEl) {
        if (isSent) {
            tagEl.innerHTML = `<span>✅ تم الفتح</span>`;
            tagEl.style.background = 'rgba(16, 185, 129, 0.25)';
            tagEl.style.border = '1px solid #10b981';
            tagEl.style.color = '#86efac';
            tagEl.style.boxShadow = '0 0 8px rgba(16, 185, 129, 0.4)';
        } else {
            tagEl.innerHTML = `<span>⏳ بانتظار الإرسال</span>`;
            tagEl.style.background = 'rgba(148, 163, 184, 0.15)';
            tagEl.style.border = '1px solid rgba(148, 163, 184, 0.3)';
            tagEl.style.color = '#94a3b8';
            tagEl.style.boxShadow = 'none';
        }
    }

    if (cardEl) {
        if (isSent) {
            cardEl.style.borderColor = '#10b981';
            cardEl.style.boxShadow = '0 0 14px rgba(16, 185, 129, 0.35)';
            cardEl.style.background = 'rgba(16, 185, 129, 0.14)';
        } else {
            cardEl.style.borderColor = isDebtor ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.4)';
            cardEl.style.boxShadow = 'none';
            cardEl.style.background = isDebtor ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)';
        }
    }
};

// ═══ تحويل الزر الرئيسي لحالة الاحتفال والنجاح المكتمل ═══
window.setBothActionCompleteState = function() {
    const mainActionBtn = document.getElementById('btn-wa-send-both-action');
    if (mainActionBtn) {
        mainActionBtn.style.background = 'linear-gradient(135deg, #059669 0%, #10b981 100%)';
        mainActionBtn.style.borderColor = '#34d399';
        mainActionBtn.style.boxShadow = '0 0 20px rgba(16, 185, 129, 0.6)';
        mainActionBtn.style.animation = 'none';
        mainActionBtn.innerHTML = `
            <span style="font-size: 1.45rem;">🎉</span>
            <span id="btn-wa-send-both-action-text">اكتمل إرسال السند لكلا الطرفين بنجاح (المستلم + الصارف) ✅</span>
        `;
        mainActionBtn.onclick = function() {
            window.closeWAVoucherModal();
        };
    }
};

window._waHandoverTimer = null;
window._pendingHandoverData = null;

window.cancelHandoverTimer = function() {
    if (window._waHandoverTimer) {
        clearInterval(window._waHandoverTimer);
        window._waHandoverTimer = null;
    }
    window._pendingHandoverData = null;
    const panel = document.getElementById('wa-modal-handover-panel');
    if (panel) panel.style.display = 'none';
};

// ═══ تسليم وإرسال الطرف الثاني (الصارف) بنقرة مستخدم مباشرة تمنع حجب المتصفح 100% ═══
window.triggerHandoverNextParty = async function() {
    if (window._waHandoverTimer) {
        clearInterval(window._waHandoverTimer);
        window._waHandoverTimer = null;
    }

    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);

    // إذا لم تكن البيانات مجهزة مسبقاً، استخراجها مباشرة من حقول النافذة
    if (!window._pendingHandoverData) {
        const credPhone = document.getElementById('wa-both-creditor-phone')?.value?.trim() || parties?.creditor?.phone || '';
        const cleanCredPhone = window.normalizePhoneNumber(credPhone);
        if (!cleanCredPhone) {
            const credInput = document.getElementById('wa-both-creditor-phone');
            if (credInput) {
                credInput.focus();
                credInput.style.borderColor = '#ef4444';
                credInput.style.boxShadow = '0 0 12px rgba(239, 68, 68, 0.7)';
                setTimeout(() => { if (credInput) { credInput.style.borderColor = 'rgba(239,68,68,0.5)'; credInput.style.boxShadow = 'none'; } }, 3000);
            }
            showToast('⚠️ يرجى إدخال هاتف الصارف (واتساب) أولاً لإتمام الإرسال!', 'warning');
            return;
        }
        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const credMsg = (window._waBothPartyMessages && window._waBothPartyMessages.creditor)
            ? window._waBothPartyMessages.creditor
            : window.buildWATailoredMessage('creditor', parties, fmt);
        window._pendingHandoverData = {
            method: 'gateway',
            phone: cleanCredPhone,
            message: credMsg,
            name: parties?.creditor?.name || 'الصارف',
            blockId
        };
    }

    const { phone, message, name, blockId: bId } = window._pendingHandoverData;
    window._pendingHandoverData = null;

    const alertEl = document.getElementById('wa-modal-status-alert');
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();

    // إطلاق المؤثر الحركي الملكي لإرسال كمبيالة السند للطرف الثاني
    const flightAnim = (typeof window.startPromissoryFlightAnimation === 'function')
        ? window.startPromissoryFlightAnimation({
            recipientName: name || phone,
            phone: phone,
            voucherNo: vNo,
            amount: (document.getElementById('voucher-amount')?.value || parties?.amount || '0'),
            currency: (document.getElementById('voucher-currency')?.value || 'SDG'),
            partyType: 'طرف الصرف'
        })
        : null;

    if (alertEl) {
        alertEl.style.display = 'block';
        alertEl.style.background = 'rgba(56, 189, 248, 0.15)';
        alertEl.style.color = '#38bdf8';
        alertEl.innerHTML = `⏳ جاري إرسال الإشعار لـ (${name}) عبر خادم الواتساب المربوط...`;
    }

    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
        const fd = new FormData();
        fd.append('userId', userId);
        fd.append('phone', phone);

        try {
            const pdfBlob = await window.generatePromissoryNotePDFBlob('creditor', parties);
            if (pdfBlob) {
                const safeName = (name || 'الصارف').replace(/[/\\?%*:|"<>]/g, '_');
                const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                fd.append('file', new File([pdfBlob], docName, { type: 'application/pdf' }));
                fd.append('documentName', docName);
                fd.append('message', window.buildWAPromissoryPDFCaption('creditor', parties));
            } else {
                fd.append('message', message);
            }
        } catch(e) {
            fd.append('message', message);
        }

        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, { method: 'POST', body: fd });
        const data = await res.json();

        if (data && data.success) {
            if (flightAnim) flightAnim.complete({ recipientName: name, phone: phone });
            window.updatePartyCardStatus('creditor', true);
            if (bId) window.markBlockAsSentWA(bId, phone);
            window.setBothActionCompleteState();

            const statusText = document.getElementById('wa-handover-status-text');
            const subText = document.getElementById('wa-handover-subtext');
            const btnHandover = document.getElementById('btn-wa-handover-next');
            if (statusText) {
                statusText.innerHTML = `<span>🎉</span> تم إرسال كمبيالة السند لكلا الطرفين (المستلم + الصارف) بنجاح عبر الواتساب!`;
                statusText.style.color = '#34d399';
            }
            if (subText) {
                subText.textContent = `تم تأكيد إرسال الإشعار بنجاح لكل من المستلم والصارف عبر خادم الواتساب.`;
            }
            if (btnHandover) {
                btnHandover.style.background = 'linear-gradient(135deg, #059669, #10b981)';
                btnHandover.style.borderColor = '#34d399';
                btnHandover.innerHTML = `<span>✅</span> <span>تم الإرسال للطرفين بنجاح (إغلاق)</span>`;
                btnHandover.onclick = function() { window.closeWAVoucherModal(); };
            }
            setTimeout(() => {
                const modal = document.getElementById('wa-voucher-modal');
                if (modal && modal.style.display !== 'none') {
                    window.closeWAVoucherModal();
                }
            }, 2500);
        } else {
            if (flightAnim) flightAnim.error(data?.error || 'تعذر الإرسال');
            else showToast('❌ تعذر إرسال الإشعار: ' + (data?.error || 'خطأ غير معروف'), 'error');
        }
    } catch(err) {
        console.error('WA Handover error:', err);
        if (flightAnim) flightAnim.error('تعذر الاتصال بخادم الواتساب');
        else showToast('⚠️ تعذر الاتصال بخادم الواتساب', 'error');
    }
};

// ═══ إرسال مباشر لأحد الطرفين بشكل فردي من بطاقته الخاصة ═══
window.sendSinglePartyDirectly = async function(partyType) {
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const parties = window.getVoucherParties(blockId);
    if (!parties) return;

    const isDebtor = (partyType === 'debtor');
    const partyObj = isDebtor ? parties.debtor : parties.creditor;
    const inputPhone = isDebtor 
        ? document.getElementById('wa-both-debtor-phone')?.value?.trim()
        : document.getElementById('wa-both-creditor-phone')?.value?.trim();
    
    const phone = inputPhone || partyObj.phone || '';
    const cleanPhone = window.normalizePhoneNumber(phone);

    if (!cleanPhone) {
        const inputEl = isDebtor ? document.getElementById('wa-both-debtor-phone') : document.getElementById('wa-both-creditor-phone');
        if (inputEl) {
            inputEl.focus();
            inputEl.style.borderColor = '#ef4444';
            inputEl.style.boxShadow = '0 0 12px rgba(239, 68, 68, 0.7)';
            setTimeout(() => {
                if (inputEl) {
                    inputEl.style.borderColor = isDebtor ? 'rgba(16,185,129,0.5)' : 'rgba(239,68,68,0.5)';
                    inputEl.style.boxShadow = 'none';
                }
            }, 3000);
        }
        showToast(`⚠️ يرجى إدخال رقم هاتف (${partyObj.name}) أولاً لإرسال الإشعار!`, 'warning');
        return;
    }

    // حفظ الهاتف مباشرة
    window.cachePartyPhone(partyType, cleanPhone);

    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();

    // إطلاق المؤثر الحركي الملكي لإرسال الكمبيالة في منتصف الشاشة
    const flightAnim = (typeof window.startPromissoryFlightAnimation === 'function')
        ? window.startPromissoryFlightAnimation({
            recipientName: partyObj.name || cleanPhone,
            phone: cleanPhone,
            voucherNo: vNo,
            amount: (document.getElementById('voucher-amount')?.value || parties.amount || '0'),
            currency: (document.getElementById('voucher-currency')?.value || 'SDG'),
            partyType: isDebtor ? 'المستفيد / المستلم' : 'طرف الصرف'
        })
        : null;

    const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
    const fd = new FormData();
    fd.append('userId', userId);
    fd.append('phone', cleanPhone);

    try {
        const pdfBlob = await window.generatePromissoryNotePDFBlob(partyType, parties);
        if (pdfBlob) {
            const safeName = (partyObj.name || partyType).replace(/[/\\?%*:|"<>]/g, '_');
            const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
            fd.append('file', new File([pdfBlob], docName, { type: 'application/pdf' }));
            fd.append('documentName', docName);
            fd.append('message', window.buildWAPromissoryPDFCaption(partyType, parties));
        } else {
            const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
            const msg = (window._waBothPartyMessages && window._waBothPartyMessages[partyType])
                ? window._waBothPartyMessages[partyType]
                : window.buildWATailoredMessage(partyType, parties, fmt);
            fd.append('message', msg);
        }
    } catch (e) {
        console.warn('PDF blob generation fallback:', e);
        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const msg = (window._waBothPartyMessages && window._waBothPartyMessages[partyType])
            ? window._waBothPartyMessages[partyType]
            : window.buildWATailoredMessage(partyType, parties, fmt);
        fd.append('message', msg);
    }

    try {
        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data && data.success) {
            if (flightAnim) flightAnim.complete({ recipientName: partyObj.name || cleanPhone, phone: cleanPhone });
            window.updatePartyCardStatus(partyType, true);
            if (blockId) window.markBlockAsSentWA(blockId, cleanPhone);
        } else {
            if (flightAnim) flightAnim.error(`تعذر الإرسال: ${data?.error || 'خطأ'}`);
            else showToast(`❌ تعذر الإرسال: ${data?.error || 'خطأ'}`, 'error');
        }
    } catch(err) {
        if (flightAnim) flightAnim.error('تعذر الاتصال بخادم الواتساب');
        else showToast('⚠️ تعذر الاتصال بخادم الواتساب', 'error');
    }

    // تحديث كارت هذا الطرف
    window.updatePartyCardStatus(partyType, true);

    if (blockId) window.markBlockAsSentWA(blockId, cleanPhone);

    // التحقق مما إذا كان الطرف الآخر مرسلاً أيضاً
    const otherType = isDebtor ? 'creditor' : 'debtor';
    const otherTag = document.getElementById(isDebtor ? 'wa-creditor-status-tag' : 'wa-debtor-status-tag');
    const isOtherSent = otherTag && otherTag.textContent.includes('تم الفتح');

    if (isOtherSent) {
        window.setBothActionCompleteState();
    } else {
        const otherPartyObj = isDebtor ? parties.creditor : parties.debtor;
        const mainActionBtn = document.getElementById('btn-wa-send-both-action');
        if (mainActionBtn) {
            mainActionBtn.style.background = isDebtor ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #10b981, #059669)';
            mainActionBtn.style.borderColor = '#fca5a5';
            mainActionBtn.style.boxShadow = '0 0 20px rgba(239, 68, 68, 0.6)';
            mainActionBtn.style.animation = 'pulse 1.3s infinite ease-in-out';
            mainActionBtn.innerHTML = `
                <span style="font-size: 1.45rem;">📤</span>
                <span id="btn-wa-send-both-action-text">اضغط هنا الآن لإرسال الطرف الآخر (${otherPartyObj.name}) ➔</span>
            `;
            mainActionBtn.onclick = function() {
                window.sendSinglePartyDirectly(otherType);
            };
        }
    }
};

// ═══ المحرك المركزي لإرسال السند (مفرد أو لكليهما) - موحد عبر خادم الواتساب المؤسسي ═══
window.executeWASend = async function(method = 'gateway') {
    const blockId = document.getElementById('wa-modal-block-id')?.value;
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    const alertEl = document.getElementById('wa-modal-status-alert');
    const parties = window.getVoucherParties(blockId);

    // ═══ الحالة الأولى والثانية: إرسال لطرف مفرد (المدين أو الدائن) مع تسليم سلس للطرف الآخر ═══
    if (targetMode === 'debtor' || targetMode === 'creditor') {
        const phone = document.getElementById('wa-modal-phone')?.value?.trim();
        const message = document.getElementById('wa-modal-message')?.value?.trim();

        if (!message) {
            showToast('نص الرسالة فارغ!', 'warning');
            return;
        }

        const cleanPhone = window.normalizePhoneNumber(phone);
        if (!cleanPhone) {
            const pInput = document.getElementById('wa-modal-phone');
            if (pInput) { pInput.focus(); pInput.style.borderColor = '#ef4444'; setTimeout(() => { if (pInput) pInput.style.borderColor = ''; }, 2500); }
            showToast('⚠️ يرجى إدخال رقم الهاتف للمستلم أولاً!', 'warning');
            return;
        }

        const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();
        const activePartyObj = (targetMode === 'debtor') ? parties.debtor : parties.creditor;

        // إطلاق المؤثر الحركي الملكي لتحرير وإرسال الكمبيالة في منتصف الشاشة
        const flightAnim = (typeof window.startPromissoryFlightAnimation === 'function')
            ? window.startPromissoryFlightAnimation({
                recipientName: activePartyObj.name || cleanPhone,
                phone: cleanPhone,
                voucherNo: vNo,
                amount: (document.getElementById('voucher-amount')?.value || parties.amount || '0'),
                currency: (document.getElementById('voucher-currency')?.value || 'SDG'),
                partyType: (targetMode === 'debtor') ? 'المستفيد / المستلم' : 'طرف الصرف'
            })
            : null;

        if (alertEl) {
            alertEl.style.display = 'block';
            alertEl.style.background = 'rgba(56, 189, 248, 0.15)';
            alertEl.style.color = '#38bdf8';
            alertEl.innerHTML = '⏳ جاري تجهيز كمبيالة السند PDF وإرسالها فوراً عبر الواتساب...';
        }

        try {
            const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
            const statusRes = await fetch(`api_index.php?r=wa-tenant&endpoint=status&userId=${userId}`);
            const statusData = await statusRes.json();

            if (statusData.status !== 'CONNECTED' && statusData.status !== 'AUTHENTICATED') {
                if (flightAnim) flightAnim.error('خادم الواتساب غير متصل. يرجى مسح رمز QR أولاً.');
                if (alertEl) {
                    alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    alertEl.style.color = '#f87171';
                    alertEl.innerHTML = '⚠️ خادم الواتساب غير مربوط حالياً. يرجى مسح رمز الاستجابة (QR) أولاً.';
                }
                const wantsQR = confirm('خادم الواتساب غير مربوط حالياً. هل تريد فتح نافذة الربط (QR) الآن؟');
                if (wantsQR && typeof openWAModal === 'function') {
                    openWAModal();
                }
                return;
            }

            const formData = new FormData();
            formData.append('userId', userId);
            formData.append('phone', cleanPhone);

            try {
                const pdfBlob = await window.generatePromissoryNotePDFBlob(targetMode, parties);
                if (pdfBlob) {
                    const safeName = (activePartyObj.name || targetMode).replace(/[/\\?%*:|"<>]/g, '_');
                    const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                    formData.append('file', new File([pdfBlob], docName, { type: 'application/pdf' }));
                    formData.append('documentName', docName);
                    formData.append('message', window.buildWAPromissoryPDFCaption(targetMode, parties));
                } else {
                    formData.append('message', message);
                }
            } catch(e) {
                console.warn('PDF blob generation fallback:', e);
                formData.append('message', message);
            }

            const sendRes = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, {
                method: 'POST',
                body: formData
            });
            const sendData = await sendRes.json();

            if (sendData && sendData.success) {
                if (flightAnim) {
                    flightAnim.complete({ recipientName: activePartyObj.name || cleanPhone, phone: cleanPhone });
                }
                if (blockId) window.markBlockAsSentWA(blockId, cleanPhone);

                const isTargetDebtor = (targetMode === 'debtor');
                const otherPartyObj = isTargetDebtor ? parties.creditor : parties.debtor;
                const otherPartyType = isTargetDebtor ? 'creditor' : 'debtor';
                const otherPhone = otherPartyObj.phone ? window.normalizePhoneNumber(otherPartyObj.phone) : '';

                if (otherPartyObj.name && otherPartyObj.name !== 'حساب الصرف/الخزينة') {
                    const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
                    const otherMsg = window.buildWATailoredMessage(otherPartyType, parties, fmt);
                    window._pendingHandoverData = {
                        method: 'gateway',
                        phone: otherPhone,
                        message: otherMsg,
                        name: otherPartyObj.name,
                        blockId
                    };

                    const handoverPanel = document.getElementById('wa-modal-handover-panel');
                    const statusText = document.getElementById('wa-handover-status-text');
                    const subText = document.getElementById('wa-handover-subtext');
                    const btnLabel = document.getElementById('btn-wa-handover-label');

                    if (handoverPanel) {
                        handoverPanel.style.display = 'block';
                        if (statusText) statusText.textContent = `تم إرسال إشعار (${isTargetDebtor ? parties.debtor.name : parties.creditor.name}) بنجاح عبر الواتساب!`;
                        if (subText) subText.textContent = `اضغط الزر أدناه لإرسال إشعار الطرف الآخر (${otherPartyObj.name}) فوراً عبر الخادم:`;
                        if (btnLabel) btnLabel.textContent = `إرسال إشعار (${otherPartyObj.name}) عبر الخادم الآن ➔`;
                    }
                    return;
                }

                setTimeout(() => window.closeWAVoucherModal(), 2200);
                return;
            } else {
                if (flightAnim) {
                    flightAnim.error(sendData?.error || 'فشل الإرسال عبر خادم الواتساب');
                }
                if (alertEl) {
                    alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    alertEl.style.color = '#f87171';
                    alertEl.innerHTML = '❌ فشل الإرسال عبر الخادم: ' + (sendData?.error || 'خطأ غير معروف');
                }
            }
        } catch (err) {
            console.error('WA Gateway error:', err);
            if (flightAnim) flightAnim.error('تعذر الإرسال عبر خادم الواتساب');
            if (alertEl) {
                alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                alertEl.style.color = '#f87171';
                alertEl.innerHTML = '⚠️ تعذر الوصول لخادم الواتساب. تأكد من تشغيل الخدمة.';
            }
        }
        return;
    }

    // ═══ الحالة الثالثة: إرسال لكليهما معاً (المدين + الدائن / المستلم + الصارف) ═══
    if (targetMode === 'both') {
        const debtorInput = document.getElementById('wa-both-debtor-phone');
        const creditorInput = document.getElementById('wa-both-creditor-phone');

        const debtorPhone = debtorInput?.value?.trim() || parties.debtor.phone || '';
        const creditorPhone = creditorInput?.value?.trim() || parties.creditor.phone || '';

        const cleanDebtorPhone = window.normalizePhoneNumber(debtorPhone);
        const cleanCreditorPhone = window.normalizePhoneNumber(creditorPhone);

        // 1. التحقق الإلزامي من توفر رقم هاتف المستلم (الطرف 1)
        if (!cleanDebtorPhone) {
            if (debtorInput) {
                debtorInput.focus();
                debtorInput.style.borderColor = '#ef4444';
                debtorInput.style.boxShadow = '0 0 14px rgba(239, 68, 68, 0.8)';
                setTimeout(() => {
                    if (debtorInput) {
                        debtorInput.style.borderColor = 'rgba(16,185,129,0.5)';
                        debtorInput.style.boxShadow = 'none';
                    }
                }, 3500);
            }
            showToast(`⚠️ يرجى إدخال رقم هاتف المستلم (${parties.debtor.name}) لإتمام الإرسال للطرفين!`, 'warning', 5000);
            return;
        }

        // 2. التحقق الإلزامي من توفر رقم هاتف الصارف (الطرف 2)
        if (!cleanCreditorPhone) {
            if (creditorInput) {
                creditorInput.focus();
                creditorInput.style.borderColor = '#ef4444';
                creditorInput.style.boxShadow = '0 0 14px rgba(239, 68, 68, 0.8)';
                setTimeout(() => {
                    if (creditorInput) {
                        creditorInput.style.borderColor = 'rgba(239,68,68,0.5)';
                        creditorInput.style.boxShadow = 'none';
                    }
                }, 3500);
            }
            showToast(`⚠️ يرجى إدخال رقم هاتف الصارف (${parties.creditor.name}) لإتمام الإرسال للطرفين!`, 'warning', 5000);
            return;
        }

        // حفظ الأرقام المدخلة في الذاكرة اللحظية
        if (cleanDebtorPhone && parties.debtor) window.cachePartyPhone('debtor', cleanDebtorPhone);
        if (cleanCreditorPhone && parties.creditor) window.cachePartyPhone('creditor', cleanCreditorPhone);

        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const debtorMsg = (window._waBothPartyMessages && window._waBothPartyMessages.debtor)
            ? window._waBothPartyMessages.debtor
            : window.buildWATailoredMessage('debtor', parties, fmt);
        const creditorMsg = (window._waBothPartyMessages && window._waBothPartyMessages.creditor)
            ? window._waBothPartyMessages.creditor
            : window.buildWATailoredMessage('creditor', parties, fmt);

        const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();
        const dualName = `${parties.debtor.name || 'المستلم'} و ${parties.creditor.name || 'الصارف'}`;

        // إطلاق المؤثر الحركي الملكي لإرسال كمبيالات السند لكلا الطرفين في منتصف الشاشة
        const flightAnim = (typeof window.startPromissoryFlightAnimation === 'function')
            ? window.startPromissoryFlightAnimation({
                recipientName: dualName,
                phone: `${cleanDebtorPhone} / ${cleanCreditorPhone}`,
                voucherNo: vNo,
                amount: (document.getElementById('voucher-amount')?.value || parties.amount || '0'),
                currency: (document.getElementById('voucher-currency')?.value || 'SDG'),
                partyType: 'المستلم والصارف معاً'
            })
            : null;

        if (alertEl) {
            alertEl.style.display = 'block';
            alertEl.style.background = 'rgba(56, 189, 248, 0.15)';
            alertEl.style.color = '#38bdf8';
            alertEl.innerHTML = '⏳ جاري تجهيز كمبيالات السند PDF وإرسالها لكلا الطرفين عبر الواتساب...';
        }

        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));

        try {
            const statusRes = await fetch(`api_index.php?r=wa-tenant&endpoint=status&userId=${userId}`);
            const statusData = await statusRes.json();

            if (statusData.status !== 'CONNECTED' && statusData.status !== 'AUTHENTICATED') {
                if (flightAnim) flightAnim.error('خادم الواتساب غير متصل. يرجى ربط الحساب أولاً.');
                if (alertEl) {
                    alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    alertEl.style.color = '#f87171';
                    alertEl.innerHTML = '⚠️ خادم الواتساب غير متصل. يرجى ربط الحساب بالباركود أولاً.';
                }
                const wantsQR = confirm('خادم الواتساب غير مربوط حالياً. هل تريد فتح نافذة الربط (QR) الآن؟');
                if (wantsQR && typeof openWAModal === 'function') {
                    openWAModal();
                }
                return;
            }

            // توليد ملفات PDF لكلا الطرفين بتتابع دقيق ومستقر
            const debtorPdf = await window.generatePromissoryNotePDFBlob('debtor', parties).catch(err => {
                console.warn('Debtor PDF err:', err);
                return null;
            });
            const creditorPdf = await window.generatePromissoryNotePDFBlob('creditor', parties).catch(err => {
                console.warn('Creditor PDF err:', err);
                return null;
            });

            const promises = [];

            const fd1 = new FormData();
            fd1.append('userId', userId);
            fd1.append('phone', cleanDebtorPhone);
            if (debtorPdf) {
                const safeName = (parties.debtor.name || 'المستلم').replace(/[/\\?%*:|"<>]/g, '_');
                const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                fd1.append('file', new File([debtorPdf], docName, { type: 'application/pdf' }));
                fd1.append('documentName', docName);
                fd1.append('message', window.buildWAPromissoryPDFCaption('debtor', parties));
            } else {
                fd1.append('message', debtorMsg);
            }
            promises.push(fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, { method: 'POST', body: fd1 }).then(r => r.json()));

            const fd2 = new FormData();
            fd2.append('userId', userId);
            fd2.append('phone', cleanCreditorPhone);
            if (creditorPdf) {
                const safeName = (parties.creditor.name || 'الصارف').replace(/[/\\?%*:|"<>]/g, '_');
                const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                fd2.append('file', new File([creditorPdf], docName, { type: 'application/pdf' }));
                fd2.append('documentName', docName);
                fd2.append('message', window.buildWAPromissoryPDFCaption('creditor', parties));
            } else {
                fd2.append('message', creditorMsg);
            }
            promises.push(fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, { method: 'POST', body: fd2 }).then(r => r.json()));

            const results = await Promise.all(promises);
            const successful = results.filter(res => res && res.success).length;
            if (successful > 0) {
                if (flightAnim) {
                    flightAnim.complete({ recipientName: dualName, phone: `${cleanDebtorPhone} / ${cleanCreditorPhone}` });
                }
                if (blockId) window.markBlockAsSentWA(blockId, cleanDebtorPhone);
                window.updatePartyCardStatus('debtor', true);
                window.updatePartyCardStatus('creditor', true);
                window.setBothActionCompleteState();
                setTimeout(() => window.closeWAVoucherModal(), 2500);
            } else {
                if (flightAnim) {
                    flightAnim.error('فشل الإرسال لكلا الطرفين عبر الخادم');
                }
                if (alertEl) {
                    alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    alertEl.style.color = '#f87171';
                    alertEl.innerHTML = '❌ فشل الإرسال عبر الخادم. تحقق من أرقام الهواتف أو حالة الخادم.';
                }
            }
        } catch (err) {
            console.error('WA Dual Gateway error:', err);
            if (flightAnim) flightAnim.error('تعذر الإرسال لكلا الطرفين');
            if (alertEl) {
                alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                alertEl.style.color = '#f87171';
                alertEl.innerHTML = '⚠️ تعذر الإرسال عبر الخادم. تأكد من تشغيل خدمة الواتساب.';
            }
        }
    }
};

// Initialize Auto-Dispatch UI on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.syncWAAutoDispatchUI());
} else {
    window.syncWAAutoDispatchUI();
}

window.openMasterWhatsAppDispatchModal = function() {
    const blocks = document.querySelectorAll('.account-block');
    if (blocks.length === 0) {
        showToast('لا توجد بنود في السند للإرسال', 'warning');
        return;
    }

    const validBlocks = [];
    blocks.forEach(b => {
        const bid = b.getAttribute('data-block-id');
        const accSearch = document.getElementById(`account-${bid}-search`);
        const accIdEl = document.getElementById(`account-${bid}-id`);
        const amtEl = document.getElementById(`amount-${bid}`);
        const accName = accSearch ? accSearch.value.trim() : '';
        const accId = accIdEl ? accIdEl.value.trim() : '';
        const amt = amtEl ? cleanNum(amtEl.value) : 0;
        if (amt > 0 || accName) {
            validBlocks.push({ bid, accName, accId, amt });
        }
    });

    if (validBlocks.length === 0) {
        showToast('لا توجد مبالغ صالحة للإرسال', 'warning');
        return;
    }

    // If 1 recipient block, open the Single/Dual Dispatch Modal with Debtor/Creditor/Both Switcher!
    if (validBlocks.length === 1) {
        window.openBlockWhatsAppDispatchModal(validBlocks[0].bid);
        return;
    }

    // If multiple blocks, open Batch Modal including Main Account and All Blocks
    const batchModal = document.getElementById('wa-batch-voucher-modal');
    const listContainer = document.getElementById('wa-batch-recipients-list');
    if (!batchModal || !listContainer) return;

    listContainer.innerHTML = '';
    const currency = document.getElementById('currency')?.value || 'SDG';

    // 1. Add Main Account at the top of the batch list
    const parties = window.getVoucherParties();
    const mainRole = parties.isPayment ? 'الصرف من حساب' : 'إلى حساب (حساب القبض)';
    const mainAccountObj = parties.isPayment ? parties.creditor : parties.debtor;
    const mainDefPhone = mainAccountObj.phone || '';
    const totalVoucherAmt = cleanNum(document.getElementById('amount-local-hdr')?.value) || 0;

    const mainRow = document.createElement('div');
    mainRow.className = 'wa-batch-row wa-batch-row-main';
    mainRow.dataset.type = 'main';
    mainRow.style.display = 'flex';
    mainRow.style.alignItems = 'center';
    mainRow.style.justifyContent = 'space-between';
    mainRow.style.gap = '10px';
    mainRow.style.padding = '10px 14px';
    mainRow.style.background = 'rgba(239,68,68,0.12)';
    mainRow.style.border = '1.5px solid rgba(239,68,68,0.45)';
    mainRow.style.borderRadius = '10px';
    mainRow.style.marginBottom = '6px';

    mainRow.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px; flex: 1.2;">
            <input type="checkbox" id="wa-batch-check-main" class="wa-batch-check" checked style="transform: scale(1.15); cursor: pointer;">
            <div>
                <span style="background: rgba(239,68,68,0.25); border: 1px solid #ef4444; color: #fca5a5; padding: 1px 6px; border-radius: 5px; font-size: 0.72rem; font-weight: 800;">🏛️ ${mainRole}</span>
                <label for="wa-batch-check-main" style="color: #ffffff; font-weight: 900; font-size: 0.95rem; margin-right: 4px; cursor: pointer; display: block;">${mainAccountObj.name}</label>
            </div>
        </div>
        <div style="flex: 1; text-align: center; color: #f87171; font-weight: 900; font-size: 0.92rem;">
            ${fmtNum(totalVoucherAmt)} ${currency}
        </div>
        <div style="flex: 1.2;">
            <input type="tel" id="wa-batch-phone-main" value="${mainDefPhone}" placeholder="هاتف حساب الصرف..." style="width: 100%; background: rgba(0,0,0,0.4); border: 1px solid rgba(239,68,68,0.5); color: #fca5a5; padding: 5px 8px; border-radius: 6px; font-size: 0.85rem; direction: ltr; text-align: left;">
        </div>
    `;
    listContainer.appendChild(mainRow);

    // 2. Add recipient blocks
    validBlocks.forEach((item, index) => {
        const contact = window.getVoucherContactDetails(item.accId, item.accName);
        const defPhone = contact ? (contact.phone || contact.whatsapp || '') : '';
        const blockRole = parties.isPayment ? 'إلى حساب (المستلم)' : 'من حساب (الدافع)';
        
        const row = document.createElement('div');
        row.className = 'wa-batch-row wa-batch-row-block';
        row.dataset.type = 'block';
        row.style.display = 'flex';
        row.style.alignItems = 'center';
        row.style.justifyContent = 'space-between';
        row.style.gap = '10px';
        row.style.padding = '8px 12px';
        row.style.background = 'rgba(255,255,255,0.04)';
        row.style.border = '1px solid rgba(255,255,255,0.08)';
        row.style.borderRadius = '8px';

        row.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px; flex: 1.2;">
                <input type="checkbox" id="wa-batch-check-${item.bid}" class="wa-batch-check" checked style="transform: scale(1.15); cursor: pointer;">
                <div>
                    <span style="background: rgba(16,185,129,0.2); border: 1px solid #10b981; color: #86efac; padding: 1px 5px; border-radius: 4px; font-size: 0.7rem; font-weight: 800;">👤 ${blockRole}</span>
                    <label for="wa-batch-check-${item.bid}" style="color: #fbbf24; font-weight: 800; font-size: 0.92rem; margin-right: 4px; cursor: pointer; display: block;">${item.accName || 'مستفيد ' + (index + 1)}</label>
                </div>
            </div>
            <div style="flex: 1; text-align: center; color: #34d399; font-weight: 800; font-size: 0.9rem;">
                ${fmtNum(item.amt)} ${currency}
            </div>
            <div style="flex: 1.2;">
                <input type="tel" id="wa-batch-phone-${item.bid}" value="${defPhone}" placeholder="رقم الهاتف..." style="width: 100%; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 0.85rem; direction: ltr; text-align: left;">
            </div>
        `;
        listContainer.appendChild(row);
    });

    const progressBar = document.getElementById('wa-batch-progress-bar');
    if (progressBar) progressBar.style.display = 'none';
    const statusText = document.getElementById('wa-batch-status-text');
    if (statusText) statusText.style.display = 'none';

    const bCard = document.getElementById('wa-batch-voucher-modal-card');
    if (bCard) {
        bCard.style.position = 'relative';
        bCard.style.left = '';
        bCard.style.top = '';
        bCard.style.margin = 'auto';
    }
    batchModal.style.display = 'flex';
    if (typeof window.initWAVoucherModalDrag === 'function') {
        window.initWAVoucherModalDrag();
    }
};

window.filterWABatchTargets = function(type) {
    const filters = document.querySelectorAll('.btn-batch-filter');
    filters.forEach(b => {
        b.classList.remove('active');
        b.style.background = 'rgba(255,255,255,0.06)';
        b.style.borderColor = 'rgba(255,255,255,0.15)';
        b.style.color = '#cbd5e1';
    });

    const activeBtn = document.getElementById(`btn-batch-filter-${type}`);
    if (activeBtn) {
        activeBtn.classList.add('active');
        activeBtn.style.background = 'rgba(37,211,102,0.2)';
        activeBtn.style.borderColor = '#25d366';
        activeBtn.style.color = '#86efac';
    }

    const rows = document.querySelectorAll('.wa-batch-row');
    rows.forEach(r => {
        const chk = r.querySelector('.wa-batch-check');
        if (!chk) return;
        if (type === 'all') {
            chk.checked = true;
        } else if (type === 'blocks') {
            chk.checked = (r.dataset.type === 'block');
        } else if (type === 'main') {
            chk.checked = (r.dataset.type === 'main');
        }
    });
};

window.toggleWABatchChecks = function() {
    const checks = document.querySelectorAll('.wa-batch-check');
    checks.forEach(c => { c.checked = !c.checked; });
};

window.closeWABatchModal = function() {
    const batchModal = document.getElementById('wa-batch-voucher-modal');
    if (batchModal) batchModal.style.display = 'none';
};

window.executeBatchWASend = async function() {
    const checks = document.querySelectorAll('.wa-batch-check:checked');
    if (checks.length === 0) {
        showToast('يرجى تحديد طرف واحد على الأقل للإرسال', 'warning');
        return;
    }

    const progressBar = document.getElementById('wa-batch-progress-bar');
    const progressFill = document.getElementById('wa-batch-progress-fill');
    const statusText = document.getElementById('wa-batch-status-text');
    const sendBtn = document.getElementById('btn-wa-batch-send');

    if (progressBar) progressBar.style.display = 'block';
    if (statusText) {
        statusText.style.display = 'block';
        statusText.style.color = '#38bdf8';
        statusText.textContent = '⏳ جاري فحص اتصال خادم الواتساب...';
    }
    if (sendBtn) sendBtn.disabled = true;

    const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));

    // 1. التحقق من اتصال خادم الواتساب
    try {
        const statusRes = await fetch(`api_index.php?r=wa-tenant&endpoint=status&userId=${userId}`);
        const statusData = await statusRes.json();

        if (statusData.status !== 'CONNECTED' && statusData.status !== 'AUTHENTICATED') {
            if (sendBtn) sendBtn.disabled = false;
            if (statusText) {
                statusText.style.color = '#f87171';
                statusText.textContent = '⚠️ خادم الواتساب غير مربوط حالياً. يرجى مسح رمز الاستجابة (QR) أولاً.';
            }
            const wantsQR = confirm('خادم الواتساب غير مربوط حالياً. هل تريد فتح نافذة الربط (QR) الآن؟');
            if (wantsQR && typeof openWAModal === 'function') {
                openWAModal();
            }
            return;
        }
    } catch (e) {
        console.warn('WA Batch status check error:', e);
    }

    let successCount = 0;
    const total = checks.length;
    const parties = window.getVoucherParties();
    const vNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();

    // 2. معالجة وإرسال إشعار وكمبيالة كل طرف مباشرة عبر الخادم في الخلفية
    for (let i = 0; i < total; i++) {
        const check = checks[i];
        const idStr = check.id.replace('wa-batch-check-', '');
        const phoneInput = document.getElementById(`wa-batch-phone-${idStr}`);
        const phone = phoneInput ? phoneInput.value.trim() : '';
        const cleanPhone = window.normalizePhoneNumber(phone);

        const isMain = (idStr === 'main');
        const targetType = isMain ? (parties.isPayment ? 'creditor' : 'debtor') : (parties.isPayment ? 'debtor' : 'creditor');
        const targetParties = isMain ? parties : window.getVoucherParties(idStr);
        const partyObj = (targetType === 'debtor') ? targetParties.debtor : targetParties.creditor;
        const msg = window.buildWATailoredMessage(targetType, targetParties);

        if (statusText) {
            statusText.style.color = '#38bdf8';
            statusText.textContent = `جاري إرسال إشعار (${partyObj.name || cleanPhone}) عبر خادم الواتساب (${i + 1} من ${total})...`;
        }
        if (progressFill) {
            progressFill.style.width = `${Math.round(((i + 1) / total) * 100)}%`;
        }

        if (!cleanPhone) {
            console.warn('Skipping send for empty phone:', partyObj.name);
            continue;
        }

        const formData = new FormData();
        formData.append('userId', userId);
        formData.append('phone', cleanPhone);

        // محاولة إنشاء وإرفاق كمبيالة السند المعتمدة كملف PDF
        try {
            const pdfBlob = await window.generatePromissoryNotePDFBlob(targetType, targetParties).catch(() => null);
            if (pdfBlob) {
                const safeName = (partyObj.name || targetType).replace(/[/\\?%*:|"<>]/g, '_');
                const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                formData.append('file', new File([pdfBlob], docName, { type: 'application/pdf' }));
                formData.append('documentName', docName);
                formData.append('message', window.buildWAPromissoryPDFCaption(targetType, targetParties));
            } else {
                formData.append('message', msg);
            }
        } catch (e) {
            console.warn('PDF blob generation fallback in batch:', e);
            formData.append('message', msg);
        }

        try {
            const sendRes = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, {
                method: 'POST',
                body: formData
            });
            const sendData = await sendRes.json();
            if (sendData && sendData.success) {
                successCount++;
                if (!isMain) {
                    window.markBlockAsSentWA(idStr, cleanPhone);
                }
            } else {
                console.warn('WA batch send error for', partyObj.name, sendData?.error);
            }
        } catch (err) {
            console.error('WA fetch send error in batch:', err);
        }

        await new Promise(r => setTimeout(r, 450));
    }

    if (sendBtn) sendBtn.disabled = false;
    if (successCount > 0) {
        if (statusText) {
            statusText.style.color = '#4ade80';
            statusText.textContent = `✅ اكتمل الإرسال المباشر لـ ${successCount} طرف بنجاح!`;
        }
        showToast(`⚡ تم إرسال ${successCount} إشعار مباشرة عبر واتساب بنجاح دون فتح أي تطبيقات!`, 'success', 6000);
        setTimeout(() => {
            window.closeWABatchModal();
        }, 1600);
    } else {
        if (statusText) {
            statusText.style.color = '#f87171';
            statusText.textContent = `⚠️ تعذر إرسال الإشعارات. يرجى التأكد من حالة الخادم وأرقام الهواتف.`;
        }
        showToast(`⚠️ تعذر إرسال الإشعارات عبر الخادم. تأكد من أرقام الهواتف.`, 'warning', 6000);
    }
};



