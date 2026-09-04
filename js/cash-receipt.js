/* ═══════════════════════════════════════════════════════
   cash-receipt.js — Bulk Cash Receipt Module
   ═══════════════════════════════════════════════════════ */

// ════════════════════════════════════════
//  MOCK DATA (Should be unified with master-data later)
// ════════════════════════════════════════
const COST_CENTERS = [
    { id:'cc-001', name:'الفرع الرئيسي' },
    { id:'cc-002', name:'فرع بحري' },
    { id:'cc-003', name:'فرع أمدرمان' },
];

let ACCOUNTS = [];

// Load real accounts from master-data
// (Moved to DBReady event)

// ════════════════════════════════════════
//  STATE
// ════════════════════════════════════════
let rows = [];
let rowIdCounter = 0;
let receiptsData = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
let currentViewedIndex = -1; // -1 means we are creating a new voucher

// ════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════
function fmtNum(n) {
    if (n === 0 || n === '0' || !n) return '0';
    return Number(n).toLocaleString('en-US');
}
function parseNum(str) {
    return parseFloat(String(str).replace(/,/g, '')) || 0;
}
function showToast(msg, type = 'success', ms = 3000) {
    const c = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = msg;
    c.appendChild(t);
    setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 500); }, ms);
}

// ════════════════════════════════════════
//  INITIALIZATION
// ════════════════════════════════════════
function initCashReceiptApp() {
    if (window._cashReceiptAppInitialized) return;
    window._cashReceiptAppInitialized = true;

    // Load accounts dynamically here to ensure DB_STORE is populated
    const localCusts = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    const localSups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
    const localBanks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    const localSafes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
    const localExp = JSON.parse(localStorage.getItem('erp_accounts_v1')) || [];

    ACCOUNTS = [];
    localCusts.forEach(c => ACCOUNTS.push({ id: c.id, name: c.name }));
    localSups.forEach(s => ACCOUNTS.push({ id: s.id, name: s.name }));
    localBanks.forEach(b => ACCOUNTS.push({ id: b.id, name: b.name }));
    localSafes.forEach(s => ACCOUNTS.push({ id: s.id, name: s.name }));
    localExp.forEach(e => ACCOUNTS.push({ id: e.id || e.code, name: e.name }));

    // Set today's date
    const dateInput = document.getElementById('hdr-date');
    if (dateInput) dateInput.valueAsDate = new Date();
    
    // Set next voucher number
    setNextVoucherNumber();

    // Add first row by default
    if (rows.length === 0) addRow();
}

if (window.APP_READY) {
    initCashReceiptApp();
} else {
    document.addEventListener('DBReady', initCashReceiptApp);
}

function setNextVoucherNumber() {
    let max = 0;
    const receipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
    receipts.forEach(r => {
        const no = parseInt(r.id || r.voucherNo || '0', 10);
        if (!isNaN(no) && no > max) max = no;
    });
    const savedSeq = parseInt(localStorage.getItem('erp_receipt_seq') || '0', 10);
    const nextSeq = Math.max(savedSeq, max) + 1;
    document.getElementById('voucher-number').innerText = String(nextSeq).padStart(6, '0');
}

// ════════════════════════════════════════
//  GRID MANAGEMENT
// ════════════════════════════════════════
function addRow() {
    rowIdCounter++;
    const rowId = rowIdCounter;
    
    // Build options for accounts
    let accOptions = `<option value="">-- اختر --</option>`;
    ACCOUNTS.forEach(a => accOptions += `<option value="${a.id}">${a.name}</option>`);

    let ccOptions = `<option value="">-- عام --</option>`;
    COST_CENTERS.forEach(c => ccOptions += `<option value="${c.id}">${c.name}</option>`);

    const tr = document.createElement('tr');
    tr.id = `row-${rowId}`;
    tr.innerHTML = `
        <td>
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" id="acc-${rowId}">
                <input type="text" class="grid-input" id="acc-${rowId}-search" placeholder="ابحث..." onfocus="showAccountDropdown('acc-${rowId}')" onblur="hideAccountDropdownDelayed('acc-${rowId}')" oninput="filterAccountDropdown('acc-${rowId}')" style="width: 100%; box-sizing: border-box; background: transparent; border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                <div class="custom-dropdown-list" id="acc-${rowId}-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5);"></div>
            </div>
        </td>
        <td>
            <input type="text" class="grid-input amount" id="amt-${rowId}" value="0" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv(${rowId}, 'local')" onblur="handleGridAmountBlur(${rowId}, this)">
        </td>
        <td>
            <select class="grid-input" id="curr-${rowId}" onchange="updateCurrency(${rowId})">
                <option value="SDG">جنيه (SDG)</option>
                <option value="USD">دولار (USD)</option>
                <option value="AED">درهم (AED)</option>
            </select>
        </td>
        <td>
            <input type="text" class="grid-input" id="rate-${rowId}" value="1.0" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv(${rowId}, 'rate')" readonly>
        </td>
        <td>
            <input type="text" class="grid-input amount" id="eq-amt-${rowId}" value="0" inputmode="decimal" style="color: #38bdf8;" onfocus="this.select()" oninput="calcEquiv(${rowId}, 'foreign')" onblur="this.value=fmtNum(parseNum(this.value))">
        </td>
        <td>
            <select class="grid-input" id="cc-${rowId}">
                ${ccOptions}
            </select>
        </td>
        <td>
            <input type="text" class="grid-input" id="note-${rowId}" placeholder="ملاحظة السطر...">
        </td>
        <td style="text-align: center;">
            <button class="btn-del-row" onclick="removeRow(${rowId})" title="حذف السطر">✖</button>
        </td>
    `;
    
    document.getElementById('grid-body').appendChild(tr);
    rows.push(rowId);
}

function handleGridAmountBlur(rowId, input) {
    const num = parseNum(input.value);
    if ((!input.value || num <= 0) && rows.length > 1) {
        removeRow(rowId);
    } else {
        input.value = fmtNum(num);
        calcTotals();
    }
}

function removeRow(id) {
    const tr = document.getElementById(`row-${id}`);
    if (tr) tr.remove();
    rows = rows.filter(r => r !== id);
    calcTotals();
    
    // Always keep at least one row
    if(rows.length === 0) addRow();
}

function calcTotals() {
    let totalSDG = 0;
    let totalUSD = 0;
    let totalAED = 0;

    rows.forEach(id => {
        const amt = Math.abs(parseNum(document.getElementById(`amt-${id}`).value));
        const eqAmt = parseNum(document.getElementById(`eq-amt-${id}`).value);
        const curr = document.getElementById(`curr-${id}`).value;
        
        totalSDG += amt;
        if (curr === 'USD') totalUSD += eqAmt;
        if (curr === 'AED') totalAED += eqAmt;
    });
    
    if (typeof window.animateValue === 'function') {
        window.animateValue(document.getElementById('footer-grand'), totalSDG, 600, 2);
        if (document.getElementById('header-grand-sdg')) window.animateValue(document.getElementById('header-grand-sdg'), totalSDG, 600, 2);
        if (document.getElementById('header-grand-usd')) window.animateValue(document.getElementById('header-grand-usd'), totalUSD, 600, 2);
        if (document.getElementById('header-grand-aed')) window.animateValue(document.getElementById('header-grand-aed'), totalAED, 600, 2);
    } else {
        document.getElementById('footer-grand').innerText = fmtNum(totalSDG);
        if(document.getElementById('header-grand-sdg')) document.getElementById('header-grand-sdg').innerText = fmtNum(totalSDG);
        if(document.getElementById('header-grand-usd')) document.getElementById('header-grand-usd').innerText = fmtNum(totalUSD);
        if(document.getElementById('header-grand-aed')) document.getElementById('header-grand-aed').innerText = fmtNum(totalAED);
    }
}

// ════════════════════════════════════════
//  CURRENCY CALCULATIONS
// ════════════════════════════════════════
function updateCurrency(id) {
    const curr = document.getElementById(`curr-${id}`).value;
    const rateEl = document.getElementById(`rate-${id}`);
    if (curr === 'SDG') {
        rateEl.value = '1.0';
        rateEl.readOnly = true;
    } else {
        rateEl.readOnly = false;
        if(parseNum(rateEl.value) === 1) rateEl.value = ''; // Clear 1.0 if they switch to foreign
    }
    calcEquiv(id, 'rate');
}

function calcEquiv(id, source) {
    const amtEl = document.getElementById(`amt-${id}`);
    const rateEl = document.getElementById(`rate-${id}`);
    const eqEl = document.getElementById(`eq-amt-${id}`);
    
    let amt = parseNum(amtEl.value);
    let rate = parseNum(rateEl.value) || 1;
    let eq = parseNum(eqEl.value);

    if (source === 'local' || source === 'rate') {
        let actualRateForForeign = rate;
        if (rate === 1 && typeof window.getExchangeRateForCurrency === 'function') {
            const globalRate = window.getExchangeRateForCurrency('USD');
            if (globalRate && globalRate > 1) actualRateForForeign = globalRate;
            else if (typeof window.getTenantKey === 'function') {
                const settings = JSON.parse(localStorage.getItem(window.getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
                if (settings.exchangeRate && settings.exchangeRate > 1) actualRateForForeign = settings.exchangeRate;
            }
        }
        eq = actualRateForForeign === 0 ? 0 : amt / actualRateForForeign;
        eqEl.value = eq.toFixed(2);
    } else if (source === 'foreign') {
        amt = eq * (typeof actualRateForForeign !== 'undefined' ? actualRateForForeign : rate);
        amtEl.value = amt.toFixed(2);
    }
    
    calcTotals();
}

// ════════════════════════════════════════
//  CRUD ACTIONS
// ════════════════════════════════════════
function resetVoucher() {
    if(confirm("هل أنت متأكد من مسح البيانات والبدء بسند جديد؟")) {
        resetVoucherSilent();
        showToast("تم فتح سند جديد", "success");
    }
}

function resetVoucherSilent() {
    currentViewedIndex = -1;
    document.getElementById('grid-body').innerHTML = '';
    rows = [];
    rowIdCounter = 0;
    addRow();
    document.getElementById('global-statement').value = '';
    document.getElementById('hdr-date').valueAsDate = new Date();
    document.getElementById('footer-grand').innerText = '0.00';
    if(document.getElementById('header-grand-sdg')) document.getElementById('header-grand-sdg').innerText = '0.00';
    if(document.getElementById('header-grand-usd')) document.getElementById('header-grand-usd').innerText = '0.00';
    if(document.getElementById('header-grand-aed')) document.getElementById('header-grand-aed').innerText = '0.00';
    setNextVoucherNumber();
}

function loadPreviousVoucher() {
    receiptsData = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
    if (receiptsData.length === 0) return showToast("لا توجد سندات سابقة", "warning");
    
    if (currentViewedIndex === -1) {
        currentViewedIndex = receiptsData.length - 1;
    } else if (currentViewedIndex > 0) {
        currentViewedIndex--;
    } else {
        return showToast("هذا هو أول سند تم حفظه", "warning");
    }
    renderVoucher(receiptsData[currentViewedIndex]);
}

function loadNextVoucher() {
    receiptsData = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
    if (currentViewedIndex === -1) return; // already at new
    
    if (currentViewedIndex < receiptsData.length - 1) {
        currentViewedIndex++;
        renderVoucher(receiptsData[currentViewedIndex]);
    } else {
        resetVoucherSilent();
        showToast("سند جديد", "success");
    }
}

function renderVoucher(v) {
    document.getElementById('voucher-number').innerText = v.id;
    document.getElementById('hdr-date').value = v.date;
    document.getElementById('to-account').value = v.toAccount;
    document.getElementById('global-statement').value = v.statement;
    
    document.getElementById('grid-body').innerHTML = '';
    rows = [];
    rowIdCounter = 0;
    
    v.details.forEach(det => {
        rowIdCounter++;
        const rowId = rowIdCounter;
        
        // Build options
        let accOptions = `<option value="">-- اختر --</option>`;
        ACCOUNTS.forEach(a => accOptions += `<option value="${a.id}">${a.name}</option>`);

        let ccOptions = `<option value="">-- عام --</option>`;
        COST_CENTERS.forEach(c => ccOptions += `<option value="${c.id}">${c.name}</option>`);

        const tr = document.createElement('tr');
        tr.id = `row-${rowId}`;
        tr.innerHTML = `
            <td>
                <select class="grid-input" id="acc-${rowId}">${accOptions}</select>
            </td>
            <td>
                <input type="text" class="grid-input amount" id="amt-${rowId}" value="${det.amountLocal}" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv(${rowId}, 'local')" onblur="handleGridAmountBlur(${rowId}, this)">
            </td>
            <td>
                <select class="grid-input" id="curr-${rowId}" onchange="updateCurrency(${rowId})">
                    <option value="SDG" ${det.currency === 'SDG' ? 'selected' : ''}>جنيه (SDG)</option>
                    <option value="USD" ${det.currency === 'USD' ? 'selected' : ''}>دولار (USD)</option>
                    <option value="AED" ${det.currency === 'AED' ? 'selected' : ''}>درهم (AED)</option>
                </select>
            </td>
            <td>
                <input type="text" class="grid-input" id="rate-${rowId}" value="${det.rate}" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv(${rowId}, 'rate')" ${det.currency === 'SDG' ? 'readonly' : ''}>
            </td>
            <td>
                <input type="text" class="grid-input amount" id="eq-amt-${rowId}" value="${det.amountForeign}" inputmode="decimal" style="color: #38bdf8;" onfocus="this.select()" oninput="calcEquiv(${rowId}, 'foreign')" onblur="this.value=fmtNum(parseNum(this.value))">
            </td>
            <td>
                <select class="grid-input" id="cc-${rowId}">${ccOptions}</select>
            </td>
            <td>
                <input type="text" class="grid-input" id="note-${rowId}" value="${det.note || ''}">
            </td>
            <td style="text-align: center;">
                <button class="btn-del-row" onclick="removeRow(${rowId})" title="حذف السطر">✖</button>
            </td>
        `;
        document.getElementById('grid-body').appendChild(tr);
        rows.push(rowId);
        
        document.getElementById(`acc-${rowId}`).value = det.accountId;
        document.getElementById(`cc-${rowId}`).value = det.costCenter;
        
        // Format initial values
        document.getElementById(`amt-${rowId}`).value = fmtNum(det.amountLocal);
        document.getElementById(`eq-amt-${rowId}`).value = fmtNum(det.amountForeign);
    });
    
    calcTotals();
}

function submitVoucher() {
    const total = parseNum(document.getElementById('footer-grand').innerText);
    if (total <= 0) {
        showToast("لا يمكن اعتماد سند قبض بقيمة صفر", "error");
        return;
    }

    const voucherNo = document.getElementById('voucher-number').innerText;
    const date = document.getElementById('hdr-date').value;
    const toAccount = document.getElementById('to-account').value;
    const globalStatement = document.getElementById('global-statement').value;

    const details = [];
    let hasEmptyAccounts = false;

    rows.forEach(id => {
        const accId = document.getElementById(`acc-${id}`).value;
        const amt = parseNum(document.getElementById(`amt-${id}`).value);
        if (amt < 0) {
            showToast("لا يمكن إدخال مبالغ سالبة في سند القبض", "error");
            return;
        }
        if (amt > 0) {
            if(!accId) hasEmptyAccounts = true;
            details.push({
                accountId: accId,
                amountLocal: amt,
                currency: document.getElementById(`curr-${id}`).value,
                rate: parseNum(document.getElementById(`rate-${id}`).value),
                amountForeign: parseNum(document.getElementById(`eq-amt-${id}`).value),
                costCenter: document.getElementById(`cc-${id}`).value,
                note: document.getElementById(`note-${id}`).value
            });
        }
    });

    if (details.length === 0) {
        showToast("يرجى إدخال مبلغ واحد على الأقل", "error");
        return;
    }
    if (hasEmptyAccounts) {
        showToast("يرجى اختيار الحساب الدافع لجميع الأسطر التي بها مبالغ", "error");
        return;
    }

    
    const _docDateStr = document.getElementById('hdr-date') ? document.getElementById('hdr-date').value : new Date().toISOString().split('T')[0];
    if (!window.canCreateRecord(_docDateStr)) return;

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    // Save to LocalStorage
    const voucherData = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        id: voucherNo,
        date: date,
        type: 'bulk_receipt',
        toAccount: toAccount,
        statement: globalStatement,
        totalAmount: total,
        details: details,
        timestamp: new Date().toISOString(),
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1
    };

    receiptsData = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
    
    // Balance update logic
    function applyBulkReceiptEffect(toAccId, detailsArray, isRevert = false) {
        let multiplier = isRevert ? -1 : 1;
        let totalToSafe = 0;
        
        let custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
        let sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        
        detailsArray.forEach(det => {
            let amt = det.amountLocal * multiplier; 
            let amtFrom = -amt; // Customer balance decreases
            totalToSafe += amt; // Safe balance increases

            let cIdx = custs.findIndex(c => c.id === det.accountId);
            if (cIdx > -1) {
                custs[cIdx].currentBalance = (custs[cIdx].currentBalance || 0) + amtFrom;
            } else {
                let sIdx = sups.findIndex(s => s.id === det.accountId);
                if (sIdx > -1) {
                    sups[sIdx].currentBalance = (sups[sIdx].currentBalance || 0) + amtFrom;
                }
            }
        });

        localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
        localStorage.setItem('erp_suppliers_v1', JSON.stringify(sups));

        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        let bIdx = banks.findIndex(b => b.id === toAccId);
        if (bIdx > -1) {
            banks[bIdx].currentBalance = (banks[bIdx].currentBalance || 0) + totalToSafe;
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        }
    }

    if (currentViewedIndex !== -1) {
        // Edit mode
        if (!window.canEditRecord(voucherData.date, 'finance')) return;
        
        // Revert old voucher effect
        const oldVoucher = receiptsData[currentViewedIndex];
        applyBulkReceiptEffect(oldVoucher.toAccount, oldVoucher.details, true);
        
        // Apply new voucher effect
        applyBulkReceiptEffect(voucherData.toAccount, voucherData.details, false);
        
        receiptsData[currentViewedIndex] = voucherData;
        localStorage.setItem('erp_receipts_v1', JSON.stringify(receiptsData));
        showToast(`تم تعديل سند القبض رقم ${voucherNo} بنجاح!`, "success");
    } else {
        // New mode
        applyBulkReceiptEffect(voucherData.toAccount, voucherData.details, false);
        receiptsData.push(voucherData);
        localStorage.setItem('erp_receipts_v1', JSON.stringify(receiptsData));
        const currentSeq = parseInt(localStorage.getItem('erp_receipt_seq') || '0');
        localStorage.setItem('erp_receipt_seq', currentSeq + 1);
        showToast(`تم اعتماد سند القبض رقم ${voucherNo} بنجاح!`, "success");
    }

    // Clear for next
    setTimeout(() => {
        resetVoucherSilent();
    }, 1500);
}

function printVoucher() {
    window.print();
}

function confirmDelete() {
    if (currentViewedIndex === -1) {
        showToast("لا يوجد سند محفوظ لحذفه حالياً", "warning");
        return;
    }

    if (confirm("هل أنت متأكد من حذف هذا السند نهائياً؟ لا يمكن التراجع عن هذه الخطوة.")) {
        const oldVoucher = receiptsData[currentViewedIndex];
        
        function applyBulkReceiptEffect(toAccId, detailsArray, isRevert = false) {
            let multiplier = isRevert ? -1 : 1;
            let totalToSafe = 0;
            let custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
            let sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
            detailsArray.forEach(det => {
                let amt = det.amountLocal * multiplier; 
                let amtFrom = -amt; 
                totalToSafe += amt; 
                let cIdx = custs.findIndex(c => c.id === det.accountId);
                if (cIdx > -1) { custs[cIdx].currentBalance = (custs[cIdx].currentBalance || 0) + amtFrom; } 
                else {
                    let sIdx = sups.findIndex(s => s.id === det.accountId);
                    if (sIdx > -1) { sups[sIdx].currentBalance = (sups[sIdx].currentBalance || 0) + amtFrom; }
                }
            });
            localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
            localStorage.setItem('erp_suppliers_v1', JSON.stringify(sups));
            let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
            let bIdx = banks.findIndex(b => b.id === toAccId);
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (banks[bIdx].currentBalance || 0) + totalToSafe;
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            }
        }
        
        applyBulkReceiptEffect(oldVoucher.toAccount, oldVoucher.details, true);

        receiptsData.splice(currentViewedIndex, 1);
        localStorage.setItem('erp_receipts_v1', JSON.stringify(receiptsData));
        showToast("تم حذف السند بنجاح", "success");
        resetVoucherSilent();
    }
}


// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Accounts
// ═══════════════════════════════════════════════════════
function renderAccountDropdown(type = 'to', filter = '') {
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

            const nameH = window.highlightSearchMatches ? window.highlightSearchMatches(a.name, filter) : a.name;
            const idH = window.highlightSearchMatches ? window.highlightSearchMatches(String(a.id), filter) : String(a.id);
            div.innerHTML = `
                <div style="font-weight: bold; color: #fff;">${nameH}</div>
                <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 3px; display: flex; gap: 8px; align-items: center;">
                    <span>[${idH}]</span>
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
        renderAccountDropdown(type, document.getElementById(type + '-search').value);
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
    const val = document.getElementById(type + '-search').value;
    renderAccountDropdown(type, val);
}

window.selectAccount = function(type, id, name) {
    document.getElementById(type).value = id;
    document.getElementById(type + '-search').value = name;
    
    const listEl = document.getElementById(type + '-dropdown-list');
    if(listEl) {
        listEl.classList.remove('show');
        listEl.style.display = 'none';
    }
    
    if (type === 'to-account') {
        const firstAmt = document.getElementById('amt-1');
        if(firstAmt) firstAmt.focus();
    } else if (type.startsWith('acc-')) {
        const rowId = type.split('-')[1];
        const amt = document.getElementById('amt-' + rowId);
        if(amt) amt.focus();
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
