// js/multi-cash-payment.js

let currentVoucherIndex = -1;
let currentVoucherList = [];

document.addEventListener('DOMContentLoaded', () => {
    // Set today's date
    const today = new Date().toISOString().split('T')[0];
    const dateEl = document.getElementById('voucher-date');
    if (dateEl) dateEl.value = today;
    
    // Add first empty row
    addRow();
    addRow(); // Start with 2 rows for convenience

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
    loadNextMultiVoucherNumber();
});

document.addEventListener('DBReady', async () => {
    await loadAllAccounts();
});

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
const fmtNum = (num) => Number(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

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
    const btnPayment = document.getElementById('btn-payment');
    const btnReceipt = document.getElementById('btn-receipt');
    const typeInput = document.getElementById('voucher-type');
    if (typeInput) typeInput.value = type;
    
    const pmRow = document.getElementById('payment-methods-row');
    
    if (type === 'payment') {
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
    currentVoucherIndex = -1;
    loadNextMultiVoucherNumber();
}

function togglePaymentMethod() {
    const pm = document.querySelector('input[name="payment_method"]:checked').value;
    const sidePanel = document.getElementById('payment-details-panel');
    const layoutWrapper = document.getElementById('main-layout-wrapper');
    const secCheck = document.getElementById('section-check');
    const secMobile = document.getElementById('section-mobile');
    const panelTitle = document.getElementById('panel-title-type');

    if (pm === 'check') {
        if (layoutWrapper) layoutWrapper.classList.add('has-sidebar');
        if (secCheck) secCheck.style.display = 'block';
        if (secMobile) secMobile.style.display = 'none';
        if (panelTitle) panelTitle.innerText = 'الشيك';
    } else if (pm === 'mobile') {
        if (layoutWrapper) layoutWrapper.classList.add('has-sidebar');
        if (secCheck) secCheck.style.display = 'none';
        if (secMobile) secMobile.style.display = 'block';
        if (panelTitle) panelTitle.innerText = 'التحويل';
    } else {
        if (layoutWrapper) layoutWrapper.classList.remove('has-sidebar');
        setTimeout(() => {
            if (secCheck) secCheck.style.display = 'none';
            if (secMobile) secMobile.style.display = 'none';
        }, 500); // Wait for transition
    }
}

function updateLabels() {
    const type = document.getElementById('voucher-type').value;
    const label = document.getElementById('main-account-label');
    const subHeader = document.getElementById('sub-account-header');
    const subTitle = document.getElementById('sub-accounts-title');
    
    if (type === 'payment') {
        if (label) {
            label.innerText = 'من حساب (صرف)';
            label.style.setProperty('color', '#f87171', 'important');
            label.style.fontSize = '1.1rem';
        }
        if (subHeader) subHeader.innerText = 'إلى حساب (استلام)';
        if (subTitle) subTitle.innerHTML = '<span>👥</span> إلى حساب (استلام)';
    } else {
        if (label) {
            label.innerText = 'إلى حساب (استلام)';
            label.style.setProperty('color', '#34d399', 'important');
            label.style.fontSize = '1.1rem';
        }
        if (subHeader) subHeader.innerText = 'من حساب (دفع)';
        if (subTitle) subTitle.innerHTML = '<span>👥</span> من حساب (دفع)';
    }
}

let multiRowIdCounter = 0;
function addRow() {
    multiRowIdCounter++;
    const rowId = multiRowIdCounter;
    const tbody = document.getElementById('entries-body');
    const tr = document.createElement('tr');
    tr.className = 'multi-row';
    tr.innerHTML = `
        <td>
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" id="acc-${rowId}" class="sub-account">
                <input type="text" class="multi-input" id="acc-${rowId}-search" placeholder="ابحث عن حساب..." onfocus="showAccountDropdown('acc-${rowId}')" onblur="hideAccountDropdownDelayed('acc-${rowId}')" oninput="filterAccountDropdown('acc-${rowId}')" style="width: 100%; box-sizing: border-box; background: transparent; border: none; color: inherit; padding: 10px;">
                <div class="custom-dropdown-list" id="acc-${rowId}-dropdown-list"></div>
            </div>
        </td>
        <td><input type="text" class="multi-input sub-amount" placeholder="0.00" onblur="formatInput(this)" oninput="restrictToNumbers(this); calcTotal();" style="font-family: monospace; font-weight: bold; font-size: 1.1rem; color: #10b981;"></td>
        <td class="col-desc"><input type="text" class="multi-input sub-desc" placeholder="بيان هذا الحساب..."></td>
        <td class="col-cost-center"><input type="text" class="multi-input sub-cost-center" placeholder="مركز التكلفة..."></td>
        <td class="col-note"><input type="text" class="multi-input sub-note" placeholder="ملحوظة..."></td>
        <td class="col-check-no"><input type="text" class="multi-input sub-check-no" placeholder="رقم الشيك..."></td>
        <td class="col-check-date"><input type="date" class="multi-input sub-check-date"></td>
        <td class="col-check-due"><input type="date" class="multi-input sub-check-due"></td>
        <td style="text-align: center;"><button class="btn-del" onclick="removeRow(this)" title="حذف السطر">✖</button></td>
    `;
    if (tbody) tbody.appendChild(tr);
    calcTotal();
    if (typeof window.applySystemNumberFormatting === 'function') window.applySystemNumberFormatting();
}

function removeRow(btn) {
    const tr = btn.closest('tr');
    if (tr) tr.remove();
    calcTotal();
}

function formatInput(input) {
    const val = cleanNum(input.value);
    input.value = val === 0 ? '' : fmtNum(val);
    calcTotal();
}

function calcTotal() {
    const amounts = document.querySelectorAll('.sub-amount');
    let total = 0;
    amounts.forEach(input => {
        total += cleanNum(input.value);
    });

    const totalAmtEl = document.getElementById('total-amount');
    if (totalAmtEl) totalAmtEl.innerText = fmtNum(total);

    const totalRowsEl = document.getElementById('total-rows');
    if (totalRowsEl) totalRowsEl.innerText = amounts.length;
    
    // Update Foreign Amount (القيمة المعادل)
    const rateEl = document.getElementById('exchange-rate');
    const eqEl = document.getElementById('amount-foreign');
    const localHdrEl = document.getElementById('amount-local-hdr');
    if (localHdrEl) {
        localHdrEl.value = fmtNum(total);
    }
    if (rateEl && eqEl) {
        let rate = cleanNum(rateEl.value) || 1;
        let eq = rate === 0 ? 0 : total / rate;
        eqEl.value = fmtNum(eq);
    }
    
    if (typeof window.applySystemNumberFormatting === 'function') {
        window.applySystemNumberFormatting();
    }
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
    if (mainAcc) mainAcc.value = '';
    const mainSearch = document.getElementById('main-account-search');
    if (mainSearch) mainSearch.value = '';
    const refEl = document.getElementById('voucher-ref');
    if (refEl) refEl.value = '';
    const descEl = document.getElementById('voucher-desc');
    if (descEl) descEl.value = '';
    
    // Clear dynamic rows
    const tbody = document.getElementById('entries-body');
    if (tbody) tbody.innerHTML = '';
    
    addRow(); 
    addRow();
    calcTotal();
    loadNextMultiVoucherNumber();
}

function printVoucher() {
    window.print();
}

function confirmDelete() {
    if (confirm('هل أنت متأكد من رغبتك في حذف البيانات الحالية وإلغاء المستند المجمع؟')) {
        resetVoucher();
        showToast('تم إلغاء المستند وتفريغ الحقول.', 'info');
    }
}

async function saveVoucher() {
    const type = document.getElementById('voucher-type').value;
    const date = document.getElementById('voucher-date').value;
    const mainAccount = document.getElementById('main-account').value;
    const desc = document.getElementById('voucher-desc').value.trim();
    const ref = document.getElementById('voucher-ref').value.trim();
    const currency = document.getElementById('currency').value;
    const exchangeRate = parseFloat(document.getElementById('exchange-rate').value) || 1.0;
    
    // Payment method and dynamic fields
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const checkNo = document.getElementById('check-no') ? document.getElementById('check-no').value.trim() : '';
    const checkBranch = document.getElementById('check-branch') ? document.getElementById('check-branch').value.trim() : '';
    const checkDate = document.getElementById('check-date') ? document.getElementById('check-date').value : '';
    const checkDueDate = document.getElementById('check-due-date') ? document.getElementById('check-due-date').value : '';
    const mobileTransferNo = document.getElementById('mobile-transfer-no') ? document.getElementById('mobile-transfer-no').value.trim() : '';
    const mobileNote = document.getElementById('mobile-note') ? document.getElementById('mobile-note').value.trim() : '';

    if (!date) return showToast('يرجى تحديد تاريخ العملية', 'error');
    if (!mainAccount) return showToast('يرجى اختيار الحساب الرئيسي', 'error');
    
    // Strict validations for bank references
    if (paymentMethod === 'check') {
        if (!checkNo || checkNo.length < 6) return showToast('رقم الشيك يجب أن يكون أكثر من 5 أرقام.', 'error');
        if (window.isBankReferenceUnique && !window.isBankReferenceUnique(checkNo, 'check')) return showToast('رقم الشيك هذا مسجل مسبقاً في النظام ولا يمكن تكراره.', 'error');
        if (!checkBranch) return showToast('يرجى إدخال اسم البنك', 'error');
    } else if (paymentMethod === 'mobile') {
        if (!mobileTransferNo || mobileTransferNo.length < 6) return showToast('رقم التحويل يجب أن يكون أكثر من 5 أرقام.', 'error');
        if (window.isBankReferenceUnique && !window.isBankReferenceUnique(mobileTransferNo, 'mobile')) return showToast('رقم التحويل هذا مسجل مسبقاً في النظام ولا يمكن تكراره.', 'error');
    }
    
    const rows = document.querySelectorAll('.multi-row');
    const items = [];
    let hasError = false;

    rows.forEach((row, index) => {
        const acc = row.querySelector('.sub-account').value.trim();
        const amtStr = row.querySelector('.sub-amount').value;
        const amt = cleanNum(amtStr);
        const subDesc = row.querySelector('.sub-desc') ? row.querySelector('.sub-desc').value.trim() : '';
        const subCostCenter = row.querySelector('.sub-cost-center') ? row.querySelector('.sub-cost-center').value.trim() : '';
        const subNote = row.querySelector('.sub-note') ? row.querySelector('.sub-note').value.trim() : '';
        const subCheckNo = row.querySelector('.sub-check-no') ? row.querySelector('.sub-check-no').value.trim() : '';
        const subCheckDate = row.querySelector('.sub-check-date') ? row.querySelector('.sub-check-date').value : '';
        const subCheckDue = row.querySelector('.sub-check-due') ? row.querySelector('.sub-check-due').value : '';

        if (acc || amt > 0) {
            if (!acc) { showToast(`السطر ${index+1}: الرجاء إدخال اسم الحساب.`, 'error'); hasError = true; }
            if (amt <= 0) { showToast(`السطر ${index+1}: الرجاء إدخال مبلغ صحيح.`, 'error'); hasError = true; }
            items.push({
                account: acc,
                amount: amt,
                statement: subDesc,
                costCenter: subCostCenter,
                note: subNote,
                checkNo: subCheckNo,
                checkDate: subCheckDate,
                checkDueDate: subCheckDue
            });
        }
    });

    if (hasError) return;
    if (items.length === 0) {
        showToast('يجب إدخال حساب فرعي واحد على الأقل.', 'error');
        return;
    }

    const finalTotal = items.reduce((sum, item) => sum + item.amount, 0);

    // Build the voucher object
    const voucher = {
        id: 'MV-' + Date.now(), // Generate a unique ID
        voucherNo: (document.getElementById('voucher-no-display') ? document.getElementById('voucher-no-display').value : '') || ref || ('MV-' + Math.floor(Math.random() * 10000)),
        type: type, // 'payment' or 'receipt'
        isGrouped: true,
        mainAccount: mainAccount,
        date: date,
        description: desc,
        totalAmount: finalTotal,
        currency: currency,
        exchangeRate: exchangeRate,
        foreignAmount: finalTotal / exchangeRate,
        paymentMethod: paymentMethod,
        checkNo: checkNo,
        checkBranch: checkBranch,
        checkDate: checkDate,
        checkDueDate: checkDueDate,
        mobileTransferNo: mobileTransferNo,
        mobileNote: mobileNote,
        items: items, // Contains {account, amount, statement}
        isPosted: false, // Sent to posting screen
        createdAt: new Date().toISOString()
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
        const getTenantKey = window.getTenantKey || ((k) => k);
        const tKey = getTenantKey(storageKey);
        
        let existingData = [];
        if (typeof localforage !== 'undefined') {
            existingData = await localforage.getItem(tKey) || [];
        } else {
            existingData = JSON.parse(localStorage.getItem(tKey) || '[]');
        }

        if (!Array.isArray(existingData)) existingData = Object.values(existingData);

        existingData.push(voucher);

        if (typeof localforage !== 'undefined') {
            await localforage.setItem(tKey, existingData);
        } else {
            localStorage.setItem(tKey, JSON.stringify(existingData));
        }

        showToast('تم حفظ السند المجمع بنجاح وإرساله لشاشة الترحيل!', 'success');
        
        // Reset Form after short delay
        setTimeout(() => {
            resetVoucher();
        }, 1500);

    } catch (e) {
        console.error('Save error:', e);
        showToast('حدث خطأ أثناء الحفظ!', 'error');
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
            div.style.cssText = 'padding: 10px 15px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;';
            div.onmouseover = () => div.style.background = 'rgba(255,255,255,0.1)';
            div.onmouseout = () => div.style.background = 'transparent';
            
            let typeTag = '';
            if (String(a.id).startsWith('cust-')) typeTag = '<span style="font-size:0.75rem; color:#60a5fa; background:rgba(96,165,250,0.1); padding:2px 6px; border-radius:4px;">عميل</span>';
            else if (String(a.id).startsWith('supp-')) typeTag = '<span style="font-size:0.75rem; color:#f472b6; background:rgba(244,114,182,0.1); padding:2px 6px; border-radius:4px;">مورد</span>';
            else if (String(a.id).startsWith('bank-')) typeTag = '<span style="font-size:0.75rem; color:#a78bfa; background:rgba(167,139,250,0.1); padding:2px 6px; border-radius:4px;">بنك</span>';
            else if (String(a.id).startsWith('safe-')) typeTag = '<span style="font-size:0.75rem; color:#34d399; background:rgba(52,211,153,0.1); padding:2px 6px; border-radius:4px;">خزينة</span>';
            else if (String(a.id).startsWith('exp-')) typeTag = '<span style="font-size:0.75rem; color:#fbbf24; background:rgba(251,191,36,0.1); padding:2px 6px; border-radius:4px;">مصروف</span>';

            div.innerHTML = `
                <div style="font-weight: bold; color: #fff;">${a.name}</div>
                <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 3px; display: flex; gap: 8px; align-items: center;">
                    <span>[${a.rawId}]</span>
                    ${typeTag}
                </div>
            `;
            div.onclick = (e) => {
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
    const searchEl = document.getElementById(type + '-search');
    if (searchEl) searchEl.value = name;

    const listEl = document.getElementById(type + '-dropdown-list');
    if(listEl) {
        listEl.classList.remove('show');
        listEl.style.display = 'none';
    }
    
    // Jump logic
    if (type.startsWith('acc-')) {
        const rowTr = document.getElementById(type).closest('tr');
        if(rowTr) {
            const amtInp = rowTr.querySelector('.sub-amount');
            if(amtInp) amtInp.focus();
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

async function loadNextMultiVoucherNumber() {
    const type = document.getElementById('voucher-type') ? document.getElementById('voucher-type').value : 'payment';
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    const getTenantKey = window.getTenantKey || ((k) => k);
    const tKey = getTenantKey(storageKey);
    
    let existingData = [];
    try {
        if (typeof localforage !== 'undefined') {
            existingData = await localforage.getItem(tKey) || [];
        } else {
            existingData = JSON.parse(localStorage.getItem(tKey) || '[]');
        }
    } catch(e) {}
    if (!Array.isArray(existingData)) existingData = Object.values(existingData);
    
    const groupedVouchers = existingData.filter(v => v.isGrouped);
    const nextNum = groupedVouchers.length + 1;
    const prefix = type === 'payment' ? 'MVP' : 'MVR';
    const padNum = String(nextNum).padStart(4, '0');
    
    const displayEl = document.getElementById('voucher-no-display');
    if (displayEl) {
        displayEl.value = `${prefix}-${padNum}`;
    }
}

async function refreshVoucherNavigationList() {
    const type = document.getElementById('voucher-type') ? document.getElementById('voucher-type').value : 'payment';
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    const getTenantKey = window.getTenantKey || ((k) => k);
    const tKey = getTenantKey(storageKey);
    
    let existingData = [];
    try {
        if (typeof localforage !== 'undefined') {
            existingData = await localforage.getItem(tKey) || [];
        } else {
            existingData = JSON.parse(localStorage.getItem(tKey) || '[]');
        }
    } catch(e) {}
    if (!Array.isArray(existingData)) existingData = Object.values(existingData);
    
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
    document.getElementById('voucher-no-display').value = v.voucherNo || '';
    document.getElementById('voucher-date').value = v.date || '';
    document.getElementById('voucher-ref').value = v.voucherNo || v.ref || '';
    document.getElementById('voucher-desc').value = v.description || '';
    
    const currencyEl = document.getElementById('currency');
    if (currencyEl) currencyEl.value = v.currency || 'SDG';
    
    const rateEl = document.getElementById('exchange-rate');
    if (rateEl) rateEl.value = v.exchangeRate || '1.0';
    
    // 3. Set main account
    const mainAccHidden = document.getElementById('main-account');
    if (mainAccHidden) {
        mainAccHidden.value = v.mainAccount || '';
    }
    const mainAccSearch = document.getElementById('main-account-search');
    if (mainAccSearch) {
        mainAccSearch.value = v.mainAccount || '';
    }
    
    // 4. Set payment method radio
    const radio = document.querySelector(`input[name="payment_method"][value="${v.paymentMethod || 'cash'}"]`);
    if (radio) {
        radio.checked = true;
        togglePaymentMethod();
    }
    
    if (document.getElementById('check-no')) document.getElementById('check-no').value = v.checkNo || '';
    if (document.getElementById('check-branch')) document.getElementById('check-branch').value = v.checkBranch || '';
    if (document.getElementById('check-date')) document.getElementById('check-date').value = v.checkDate || '';
    if (document.getElementById('check-due-date')) document.getElementById('check-due-date').value = v.checkDueDate || '';
    
    if (document.getElementById('mobile-transfer-no')) document.getElementById('mobile-transfer-no').value = v.mobileTransferNo || '';
    if (document.getElementById('mobile-note')) document.getElementById('mobile-note').value = v.mobileNote || '';
    
    // 5. Populate items table
    const tbody = document.getElementById('entries-body');
    if (tbody) tbody.innerHTML = '';
    
    if (v.items && Array.isArray(v.items)) {
        v.items.forEach(item => {
            addRowWithData(item);
        });
    }
    
    calcTotal();
    showToast(`تم عرض السند ${v.voucherNo} بنجاح`, 'success');
}

function getAccountName(id) {
    if (!id) return '';
    const cleanId = String(id).replace(/^(cust|supp|bank|safe|exp)-/, '');
    const matched = ACCOUNTS.find(a => String(a.id) === String(id) || String(a.rawId) === String(cleanId) || String(a.name) === String(id));
    return matched ? matched.name : id;
}

function addRowWithData(item) {
    multiRowIdCounter++;
    const rowId = multiRowIdCounter;
    const tbody = document.getElementById('entries-body');
    const tr = document.createElement('tr');
    tr.className = 'multi-row';
    
    const accName = getAccountName(item.account);

    tr.innerHTML = `
        <td>
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" id="acc-${rowId}" class="sub-account" value="${accName}">
                <input type="text" class="multi-input" id="acc-${rowId}-search" placeholder="ابحث عن حساب..." onfocus="showAccountDropdown('acc-${rowId}')" onblur="hideAccountDropdownDelayed('acc-${rowId}')" oninput="filterAccountDropdown('acc-${rowId}')" style="width: 100%; box-sizing: border-box; background: transparent; border: none; color: inherit; padding: 10px;" value="${accName}">
                <div class="custom-dropdown-list" id="acc-${rowId}-dropdown-list"></div>
            </div>
        </td>
        <td><input type="text" class="multi-input sub-amount" placeholder="0.00" onblur="formatInput(this)" oninput="restrictToNumbers(this); calcTotal();" style="font-family: monospace; font-weight: bold; font-size: 1.1rem; color: #10b981;" value="${item.amount > 0 ? fmtNum(item.amount) : ''}"></td>
        <td class="col-desc"><input type="text" class="multi-input sub-desc" placeholder="بيان هذا الحساب..." value="${item.statement || ''}"></td>
        <td class="col-cost-center"><input type="text" class="multi-input sub-cost-center" placeholder="مركز التكلفة..." value="${item.costCenter || ''}"></td>
        <td class="col-note"><input type="text" class="multi-input sub-note" placeholder="ملحوظة..." value="${item.note || ''}"></td>
        <td class="col-check-no"><input type="text" class="multi-input sub-check-no" placeholder="رقم الشيك..." value="${item.checkNo || ''}"></td>
        <td class="col-check-date"><input type="date" class="multi-input sub-check-date" value="${item.checkDate || ''}"></td>
        <td class="col-check-due"><input type="date" class="multi-input sub-check-due" value="${item.checkDueDate || ''}"></td>
        <td style="text-align: center;"><button class="btn-del" onclick="removeRow(this)" title="حذف السطر">✖</button></td>
    `;
    if (tbody) tbody.appendChild(tr);
}
