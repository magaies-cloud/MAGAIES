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

// ═══ Currency Change: Auto-fill exchange rate from settings ═══
window.onCurrencyChange = function() {
    const currencyEl = document.getElementById('currency');
    const rateEl = document.getElementById('exchange-rate');
    if (!currencyEl || !rateEl) return;
    
    const currency = currencyEl.value;
    
    if (currency === 'SDG') {
        rateEl.value = '1.0';
        rateEl.readOnly = true;
        rateEl.style.opacity = '0.6';
    } else {
        // Get rate from settings or API
        let rate = 0;
        if (typeof window.getExchangeRateForCurrency === 'function') {
            rate = window.getExchangeRateForCurrency(currency);
        }
        
        if (rate > 0) {
            rateEl.value = parseFloat(rate).toFixed(4);
        } else {
            rateEl.value = '';
        }
        
        // Check if user has permission to edit exchange rates
        const canEdit = checkExchangeRatePermission();
        rateEl.readOnly = !canEdit;
        rateEl.style.opacity = canEdit ? '1' : '0.6';
        
        if (!canEdit && rate > 0) {
            showToast('تم تعبئة سعر الصرف تلقائياً من الإعدادات (' + parseFloat(rate).toFixed(4) + ')', 'info');
        } else if (rate > 0) {
            showToast('سعر الصرف: ' + parseFloat(rate).toFixed(4) + ' - يمكنك تعديله يدوياً', 'info');
        } else {
            showToast('لم يتم العثور على سعر صرف. يرجى إدخاله يدوياً.', 'info');
            rateEl.focus();
        }
    }
    
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
    const currencyEl = document.getElementById('currency');
    if (!rateEl || !currencyEl) return;
    
    if (currencyEl.value !== 'SDG') {
        const canEdit = checkExchangeRatePermission();
        rateEl.readOnly = !canEdit;
        rateEl.style.opacity = canEdit ? '1' : '0.6';
    }
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
            pmRow.style.background = 'linear-gradient(45deg, #b91c1c, #ef4444, #991b1b, #dc2626)';
            pmRow.style.borderColor = '#f87171';
            pmRow.style.boxShadow = '0 8px 20px rgba(220, 38, 38, 0.4)';
        }
    } else {
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn active-receipt';
        if (btnPayment) btnPayment.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'linear-gradient(45deg, #047857, #10b981, #065f46, #059669)';
            pmRow.style.borderColor = '#34d399';
            pmRow.style.boxShadow = '0 8px 20px rgba(16, 185, 129, 0.4)';
        }
    }
    
    updateLabels();
    currentVoucherIndex = -1;
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
    
    if (type === 'payment') {
        if (primaryContainer) {
            primaryContainer.className = 'glass-container glass-red';
        }
        if (secondaryContainer) {
            secondaryContainer.className = 'glass-container glass-green';
        }
        if (label) {
            label.innerHTML = 'من حساب (صرف) <span class="animated-arrow-left">←</span>';
            label.style.setProperty('color', '#ef4444', 'important');
            label.style.fontSize = '1.2rem';
        }
        if (subHeader) {
            subHeader.innerHTML = 'إلى حساب (استلام) <span class="animated-arrow-right">→</span>';
            subHeader.style.setProperty('color', '#34d399', 'important');
            subHeader.style.fontSize = '1.2rem';
        }
        if (subTitle) {
            subTitle.innerHTML = '<span>👥</span> إلى حساب (استلام) <span class="animated-arrow-right">→</span>';
            subTitle.style.setProperty('color', '#34d399', 'important');
            subTitle.style.fontSize = '1.2rem';
        }
        if (entryCardLabel) {
            entryCardLabel.innerHTML = 'إلى حساب (مستفيد) <span class="animated-arrow-right">→</span>';
            entryCardLabel.style.setProperty('color', '#34d399', 'important');
        }
        document.querySelectorAll('.block-label').forEach(lbl => {
            lbl.textContent = 'إلى حساب (مستلم)';
            lbl.style.color = '#34d399';
            if (lbl.parentElement) lbl.parentElement.style.color = '#34d399';
        });
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
            label.style.fontSize = '1.2rem';
        }
        if (subHeader) {
            subHeader.innerHTML = 'من حساب (دفع) <span class="animated-arrow-left">←</span>';
            subHeader.style.setProperty('color', '#ef4444', 'important');
            subHeader.style.fontSize = '1.2rem';
        }
        if (subTitle) {
            subTitle.innerHTML = '<span>👥</span> من حساب (دفع) <span class="animated-arrow-left">←</span>';
            subTitle.style.setProperty('color', '#ef4444', 'important');
            subTitle.style.fontSize = '1.2rem';
        }
        if (entryCardLabel) {
            entryCardLabel.innerHTML = 'من حساب (دافع) <span class="animated-arrow-left">←</span>';
            entryCardLabel.style.setProperty('color', '#ef4444', 'important');
        }
        document.querySelectorAll('.block-label').forEach(lbl => {
            lbl.textContent = 'من حساب (دافع)';
            lbl.style.color = '#ef4444';
            if (lbl.parentElement) lbl.parentElement.style.color = '#ef4444';
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
            if (label.parentElement) label.parentElement.style.color = '#34d399';
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
            if (label.parentElement) label.parentElement.style.color = '#ef4444';
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
        <div style="width: 80px; flex-shrink: 0;"></div>
        <div style="width: 150px; flex-shrink: 0;">
            <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" data-block-id="${blockId}" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1; min-width: 120px;">
            <input type="text" autocomplete="off" class="form-input block-bank-name-input" placeholder="اسم البنك" style="margin-bottom: 0;">
        </div>
        <div style="flex: 1; min-width: 120px;">
            <input type="text" autocomplete="off" class="form-input block-transfer-no-input" placeholder="رقم التحويلة" style="margin-bottom: 0;">
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
        <div style="width: 80px; flex-shrink: 0;"></div>
        <div style="width: 150px; flex-shrink: 0;">
            <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" data-block-id="${blockId}" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1; min-width: 90px;">
            <input type="text" autocomplete="off" class="form-input block-cheque-bank-input" placeholder="اسم البنك" style="margin-bottom: 0;">
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
            <div style="width: 80px; flex-shrink: 0;"></div>
            <div style="width: 150px; flex-shrink: 0;">
                <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
            </div>
            <div style="flex: 1; min-width: 120px;">
                <input type="text" autocomplete="off" class="form-input block-bank-name-input" placeholder="اسم البنك" style="margin-bottom: 0;">
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
            <div style="width: 80px; flex-shrink: 0;"></div>
            <div style="width: 150px; flex-shrink: 0;">
                <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
            </div>
            <div style="flex: 1; min-width: 90px;">
                <input type="text" autocomplete="off" class="form-input block-cheque-bank-input" placeholder="اسم البنك" style="margin-bottom: 0;">
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
            recalcBlockTotal(block.getAttribute('data-block-id'));
        }
    }
});

function formatInput(input) {
    formatAmount(input);
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
    
    // Update Foreign Amount (القيمة المعادل)
    const rateEl = document.getElementById('exchange-rate');
    const eqEl = document.getElementById('amount-foreign');
    const localHdrEl = document.getElementById('amount-local-hdr');
    if (localHdrEl) {
        const dec = localHdrEl.getAttribute('data-decimals') !== null ? parseInt(localHdrEl.getAttribute('data-decimals')) : null;
        localHdrEl.value = fmtNum(total, dec);
    }
    if (rateEl && eqEl) {
        let rate = cleanNum(rateEl.value) || 1;
        let actualRateForForeign = rate;
        if (rate === 1 && typeof window.getExchangeRateForCurrency === 'function') {
            const globalRate = window.getExchangeRateForCurrency('USD');
            if (globalRate && globalRate > 1) actualRateForForeign = globalRate;
        }
        let eq = actualRateForForeign === 0 ? 0 : total / actualRateForForeign;
        eqEl.value = fmtNum(eq);
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

    // Build Table Rows from entryItems or fallback to DOM rows
    let tableHtml = `
        <table class="print-table">
            <thead>
                <tr>
                    <th>م</th>
                    <th>الحساب / الجهة المستفيدة</th>
                    <th>المبلغ</th>
                    <th>البيان التفصيلي</th>
                    <th>طريقة الدفع</th>
                </tr>
            </thead>
            <tbody>
    `;
    let totalAmt = 0;
    if (entryItems.length > 0) {
        entryItems.forEach((item, idx) => {
            totalAmt += item.amount;
            const pmLabel = item.paymentMethod === 'cash' ? 'نقدي' :
                            item.paymentMethod === 'bank' ? 'تحويل بنكي' : 'شيك';
            tableHtml += `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${item.accountName || item.account}</td>
                    <td style="font-weight: bold; color: #1e293b;">${fmtNum(item.amount)}</td>
                    <td>${item.statement || ''}</td>
                    <td>${pmLabel}</td>
                </tr>
            `;
        });
    } else {
        const rows = document.querySelectorAll('.multi-row');
        rows.forEach((row, idx) => {
            const searchInput = row.querySelector('input[id$="-search"]');
            const accName = searchInput ? searchInput.value : '';
            if (!accName && idx > 0) return;
            const amtEl = row.querySelector('.sub-amount');
            const amt = amtEl ? amtEl.value : '0';
            totalAmt += parseFloat(amt.replace(/,/g, '') || 0);
            const descEl = row.querySelector('.sub-desc');
            const note = descEl ? descEl.value : '';
            tableHtml += `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${accName}</td>
                    <td style="font-weight: bold; color: #1e293b;">${amt}</td>
                    <td>${note}</td>
                    <td>---</td>
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
                    <td colspan="2"></td>
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
        showToast('حدث خطأ فادح أثناء الحفظ! يرجى المحاولة مرة أخرى.', 'error');
        const saveBtn = document.getElementById('btn-save');
        if (saveBtn) { saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.pointerEvents = 'auto'; }
    }
}
async function _saveVoucherCore() {
    // Double-click protection
    const saveBtn = document.getElementById('btn-save');
    if (saveBtn && saveBtn.disabled) return;
    if (saveBtn) { saveBtn.disabled = true; saveBtn.style.opacity = '0.6'; saveBtn.style.pointerEvents = 'none'; }

    // Helper to re-enable save button on validation errors
    function reEnableSave() {
        if (saveBtn) { saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.pointerEvents = 'auto'; }
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
    const ref = document.getElementById('voucher-ref').value.trim();
    const currency = document.getElementById('currency').value;
    const exchangeRate = parseFloat(document.getElementById('exchange-rate').value) || 1.0;
    
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
                    if (tno.length < 6) {
                        showCenterError(`رقم التحويل البنكي للحساب ( ${accountName} - السطر ${i + 1} ) قصير جداً!<br><br>يجب أن لا يقل عن 6 أرقام`, 'short');
                        highlightError(row.querySelector('.block-transfer-no-input'));
                        hasInvalidTransfer = true;
                        break;
                    }
                    
                    const targetSuffix = String(tno).trim().slice(-6);
                    if (seenTransfers.has(targetSuffix)) {
                        showCenterError(`رقم التحويل ( ${tno} ) للحساب ( ${accountName} - السطر ${i + 1} ) مكرر داخل نفس الشاشة!<br><br>يوجد تطابق في آخر 6 أرقام مع تحويلة أخرى أدخلتها الآن.`, 'duplicate');
                        highlightError(row.querySelector('.block-transfer-no-input'));
                        hasInvalidTransfer = true;
                        break;
                    }
                    seenTransfers.add(targetSuffix);
                    
                    const cvNo = document.getElementById('voucher-no-display') ? document.getElementById('voucher-no-display').value : '';
                    const isUnique = await window.checkTransferDuplicate(tno, cvNo);
                    if (!isUnique) {
                        showCenterError(`رقم التحويل ( ${tno} ) للحساب ( ${accountName} - السطر ${i + 1} ) مستخدم من قبل!<br><br>يوجد تطابق في آخر 6 أرقام مع سند آخر في النظام.`, 'duplicate');
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
        currency: currency,
        exchangeRate: exchangeRate,
        foreignAmount: finalTotal / exchangeRate,
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
        createdAt: new Date().toISOString(),
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1
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

    // Determine storage key based on type
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    
    try {
        let existingData = getStoredVouchers(storageKey);

        // Update existing record if editing, otherwise insert
        const existingIdx = existingData.findIndex(v => (v && (v.voucherNo === vNo || v.id === vNo) && v.isGrouped));
        if (existingIdx > -1) {
            voucher.createdAt = existingData[existingIdx].createdAt || voucher.createdAt;
            existingData[existingIdx] = voucher;
        } else {
            existingData.push(voucher);
        }

        // SAVE TO BOTH TENANT KEY AND BASE KEY
        saveStoredVouchers(storageKey, existingData);

        // Rebuild GL immediately so General Ledger reflects new voucher
        if (window.rebuildGLFromAllTables) {
            try { window.rebuildGLFromAllTables(false); } catch(e) {}
        }

        // Apply dynamic account balance effects
        if (typeof applyGroupedVoucherEffect === 'function') {
            applyGroupedVoucherEffect(voucher, false);
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
            
            // Refresh navigation list and keep the current saved voucher on screen
            refreshVoucherNavigationList().then(() => {
                currentVoucherIndex = currentVoucherList.length - 1;
            });
            
        }, 3200);

    } catch (e) {
        console.error('Save error:', e);
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
        
        data.forEach(item => {
            if (item.deleted) return;
            const name = item.name || item.accountName || item.customerName || item.supplierName || item.bankName || item.safeName;
            const id = item.id || item.code || '';
            if (name) {
                ACCOUNTS.push({ id: store.prefix + id, name: name, rawId: id });
            }
        });
    }
}

// Hook into DBReady to build ACCOUNTS
document.addEventListener('DBReady', () => {
    buildAccountsArray();
});

function renderAccountDropdown(type, filter = '') {
    if (ACCOUNTS.length === 0) buildAccountsArray();

    const listEl = document.getElementById(type + '-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
    
    try {
        let filtered = ACCOUNTS.filter(a => {
            if (!term) return true;
            const normalizedName = window.normalizeArabic ? window.normalizeArabic(a.name) : a.name.toLowerCase();
            return normalizedName.includes(normalizedTerm) || String(a.id).includes(term);
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

            div.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: bold; color: #fff;">${highlightedName}</span>
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
    
    if (type.startsWith('account-')) {
        const blockId = type.replace('account-', '');
        const amtEl = document.getElementById('amount-' + blockId);
        if (amtEl) amtEl.focus();
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
            pmRow.style.background = 'rgba(239, 68, 68, 0.08)';
            pmRow.style.borderColor = 'rgba(239, 68, 68, 0.2)';
        }
    } else {
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn active-receipt';
        if (btnPayment) btnPayment.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'rgba(16, 185, 129, 0.08)';
            pmRow.style.borderColor = 'rgba(16, 185, 129, 0.2)';
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
    
    const currencyEl = document.getElementById('currency');
    if (currencyEl) currencyEl.value = v.currency || 'SDG';
    
    const rateEl = document.getElementById('exchange-rate');
    if (rateEl) rateEl.value = v.exchangeRate || '1.0';
    
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
                            <div style="width: 80px; font-weight: bold; color: #22d3ee; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">${idx === 0 ? '🏦 تحويل' : ''}</div>
                            <div style="width: 150px; flex-shrink: 0;">
                                <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" data-block-id="${blockId}" placeholder="المبلغ" value="${bt.amount || ''}" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
                            </div>
                            <div style="flex: 1; min-width: 120px;">
                                <input type="text" autocomplete="off" class="form-input block-bank-name-input" placeholder="اسم البنك" value="${bt.bankName || ''}" style="margin-bottom: 0;">
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
                            <div style="width: 80px; font-weight: bold; color: #d8b4fe; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">${idx === 0 ? '✍️ شيك' : ''}</div>
                            <div style="width: 150px; flex-shrink: 0;">
                                <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" data-block-id="${blockId}" placeholder="المبلغ" value="${ch.amount || ''}" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
                            </div>
                            <div style="flex: 1; min-width: 90px;">
                                <input type="text" autocomplete="off" class="form-input block-cheque-bank-input" placeholder="اسم البنك" value="${ch.chequeBank || ''}" style="margin-bottom: 0;">
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
    
    calcTotal();
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
    
    // Helper function to update balance
    function applyBalanceChange(accId, amtEffect) {
        if (!accId || amtEffect === 0) return;
        
        if (accId.startsWith('safe-')) {
            let id = accId.substring(5);
            let safes = [];
            try { safes = JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1'))) || []; } catch(e){}
            let idx = safes.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem(getTenantKey('erp_safes_v1'), JSON.stringify(safes));
            }
        } else if (accId.startsWith('bank-')) {
            let id = accId.substring(5);
            let banks = [];
            try { banks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1'))) || []; } catch(e){}
            let idx = banks.findIndex(b => String(b.id) === String(id));
            if (idx > -1) {
                banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem(getTenantKey('erp_banks_v1'), JSON.stringify(banks));
            }
        } else if (accId.startsWith('cust-')) {
            let id = accId.substring(5);
            let custs = [];
            try { custs = JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1'))) || []; } catch(e){}
            let idx = custs.findIndex(c => String(c.id) === String(id));
            if (idx > -1) {
                custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem(getTenantKey('erp_customers_v1'), JSON.stringify(custs));
            }
        } else if (accId.startsWith('supp-')) {
            let id = accId.substring(5);
            let sups = [];
            try { sups = JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1'))) || []; } catch(e){}
            let idx = sups.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem(getTenantKey('erp_suppliers_v1'), JSON.stringify(sups));
            }
        } else if (accId.startsWith('exp-')) {
            let id = accId.substring(4);
            let accounts = [];
            try { accounts = JSON.parse(localStorage.getItem(getTenantKey('erp_accounts_v1'))) || []; } catch(e){}
            let idx = accounts.findIndex(a => String(a.id) === String(id));
            if (idx > -1) {
                accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem(getTenantKey('erp_accounts_v1'), JSON.stringify(accounts));
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
    if (input.classList.contains('sub-amount')) {
        const val = parseFloat(input.value.replace(/,/g, '')) || 0;
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
            <input type="text" autocomplete="off" class="v-input ctrl-input transfer-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="0.00" onfocus="if(parseFloat(this.value.replace(/,/g, '') || 0) === 0) this.value = '';" oninput="restrictToNumbers(this); recalcPaymentSummary();" onblur="if(this.value) this.value = parseFloat(this.value.replace(/,/g, '') || 0).toFixed(2); recalcPaymentSummary();" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:1.35rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 170px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">اسم البنك:</label>
            <input type="text" autocomplete="off" class="v-input ctrl-input transfer-note" value="${note}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
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
        setTimeout(() => { row.remove(); syncFirstRowAmount(); }, 300);
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
            <input type="text" class="v-input ctrl-input cheque-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="0.00" onfocus="if(parseFloat(this.value.replace(/,/g, '') || 0) === 0) this.value = '';" oninput="restrictToNumbers(this); recalcPaymentSummary();" onblur="if(this.value) this.value = parseFloat(this.value.replace(/,/g, '') || 0).toFixed(2); recalcPaymentSummary();" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:1.35rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 170px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">اسم البنك:</label>
            <input type="text" class="v-input ctrl-input cheque-branch" value="${branch}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
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
        setTimeout(() => { row.remove(); syncFirstRowAmount(); }, 300);
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

// ─── Unsaved Changes Warning ───
let formDirty = false;
document.addEventListener('input', function(e) {
    if (e.target.closest('#accounts-blocks-container') || e.target.id === 'main-account-search' || e.target.id === 'voucher-desc') {
        formDirty = true;
    }
});
window.addEventListener('beforeunload', function(e) {
    if (formDirty) {
        e.preventDefault();
        e.returnValue = 'لديك تغييرات غير محفوظة. هل تريد المغادرة؟';
        return e.returnValue;
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
    e.preventDefault();
    currentTargetInput = input;
    
    const menu = document.getElementById('num-props-menu');
    if (!menu) return;
    
    const hasComma = input.getAttribute('data-thousands') !== 'false';
    const decimals = parseInt(input.getAttribute('data-decimals') || '0');
    const font = input.getAttribute('data-font') || 'inter';
    const numColor = input.getAttribute('data-color') || '#ffffff';
    const isRounding = input.getAttribute('data-rounding') === 'true';
    
    document.getElementById('prop-comma-status').textContent = hasComma ? 'مفعل' : 'معطل';
    document.getElementById('prop-comma-status').style.color = hasComma ? '#10b981' : '#ef4444';
    document.getElementById('prop-dec-val').textContent = decimals;
    document.getElementById('prop-font-status').textContent = font === 'inter' ? 'Inter' : 'Roboto Mono';
    
    const colorPicker = document.getElementById('prop-color-picker');
    if (colorPicker) colorPicker.value = numColor;
    
    const roundingStatus = document.getElementById('prop-rounding-status');
    if (roundingStatus) {
        roundingStatus.textContent = isRounding ? 'مفعل' : 'معطل';
        roundingStatus.style.color = isRounding ? '#10b981' : '#ef4444';
    }
    
    menu.style.display = 'block';
    
    let x = e.pageX;
    let y = e.pageY;
    if (x + menu.offsetWidth > window.innerWidth) x = window.innerWidth - menu.offsetWidth - 10;
    if (y + menu.offsetHeight > window.innerHeight) y = window.innerHeight - menu.offsetHeight - 10;
    
    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
};

window.closeNumberProperties = function() {
    const menu = document.getElementById('num-props-menu');
    if (menu) menu.style.display = 'none';
};

document.addEventListener('click', function(e) {
    const menu = document.getElementById('num-props-menu');
    if (menu && menu.style.display === 'block') {
        if (!menu.contains(e.target)) {
            closeNumberProperties();
        }
    }
});

// Attach context menu to total fields
document.addEventListener('contextmenu', function(e) {
    if (e.target.classList.contains('block-total-amount') || e.target.classList.contains('block-sub-amount') || e.target.id === 'total-amount' || e.target.id === 'amount-local-hdr' || e.target.classList.contains('transfer-amount') || e.target.classList.contains('cheque-amount')) {
        showNumberProperties(e, e.target);
    }
});

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
        document.getElementById('prop-comma-status').textContent = !current ? 'مفعل' : 'معطل';
        document.getElementById('prop-comma-status').style.color = !current ? '#10b981' : '#ef4444';
    } else if (action === 'dec-set-2') {
        let dec = 2;
        input.setAttribute('data-decimals', dec);
        input.setAttribute('data-rounding', 'true');
        document.getElementById('prop-dec-val').textContent = dec;
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
        document.getElementById('prop-dec-val').textContent = dec;
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
        document.getElementById('prop-dec-val').textContent = dec;
        const roundingStatus = document.getElementById('prop-rounding-status');
        if (roundingStatus) {
            roundingStatus.textContent = 'مفعل';
            roundingStatus.style.color = '#10b981';
        }
    } else if (action === 'toggle-font') {
        const current = input.getAttribute('data-font') || 'inter';
        const newFont = current === 'inter' ? 'monospace' : 'inter';
        input.setAttribute('data-font', newFont);
        if (newFont === 'monospace') {
            input.style.setProperty('font-family', "'Roboto Mono', Consolas, monospace", 'important');
            document.getElementById('prop-font-status').textContent = 'Roboto Mono';
        } else {
            input.style.setProperty('font-family', "'Inter', system-ui, sans-serif", 'important');
            document.getElementById('prop-font-status').textContent = 'Inter';
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

    if (e.key === 'Enter' && !e.ctrlKey) {
        
        // 1. main-account-search -> account-search input of first block
        if (active.id === 'main-account-search') {
            const list = document.getElementById('main-account-dropdown-list');
            if (list && list.style.display !== 'none' && list.innerHTML.trim() !== '') {
                // Dropdown is open. User is selecting. We let default happen.
                return;
            } else {
                // Second enter (dropdown closed) jumps to first account search
                e.preventDefault();
                const firstAccount = document.querySelector('.block-account-search');
                if (firstAccount) {
                    firstAccount.focus();
                    firstAccount.select();
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

        // 3. account-*-search -> bank toggle
        if (active.classList.contains('block-account-search')) {
            const listId = active.id.replace('-search', '-dropdown-list');
            const list = document.getElementById(listId);
            if (list && list.style.display !== 'none' && list.innerHTML.trim() !== '') {
                // Dropdown open, let selection happen
                setTimeout(() => {
                    const block = active.closest('.account-block');
                    if (block) {
                        const bankBtn = block.querySelector('.block-btn-bank');
                        if (bankBtn) bankBtn.focus();
                    }
                }, 150);
                return; 
            } else {
                e.preventDefault();
                const block = active.closest('.account-block');
                if (block) {
                    const bankBtn = block.querySelector('.block-btn-bank');
                    if (bankBtn) bankBtn.focus();
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

        // 5. statement-* -> create new block and focus its amount
        if (active.id && active.id.startsWith('statement-')) {
            e.preventDefault();
            if (typeof window.addAccountBlock === 'function') {
                window.addAccountBlock();
                setTimeout(() => {
                    const amounts = document.querySelectorAll('.block-total-amount');
                    if (amounts.length > 0) {
                        amounts[amounts.length - 1].focus();
                        amounts[amounts.length - 1].select();
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
                    const btn = row.querySelector('[data-action="add-bank-row"]');
                    if (btn) btn.focus();
                }
            }
            return;
        }
        
        // 9. bank desc -> trigger add bank row and focus amount
        if (active.classList.contains('block-bank-desc-input')) {
            e.preventDefault();
            const row = active.closest('.bank-row');
            if (row) {
                const btn = row.querySelector('[data-action="add-bank-row"]');
                if (btn) {
                    btn.click();
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
                    const btn = row.querySelector('[data-action="add-cheque-row"]');
                    if (btn) btn.focus();
                }
            }
            return;
        }

        // 14. cheque desc -> trigger add cheque row and focus amount
        if (active.classList.contains('block-cheque-desc-input')) {
            e.preventDefault();
            const row = active.closest('.cheque-row');
            if (row) {
                const btn = row.querySelector('[data-action="add-cheque-row"]');
                if (btn) {
                    btn.click();
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
