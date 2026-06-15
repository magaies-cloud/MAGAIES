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
const localCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
const localSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
localCusts.forEach(c => ACCOUNTS.push({ id: c.id, name: c.name }));
localSups.forEach(s => ACCOUNTS.push({ id: s.id, name: s.name }));


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
document.addEventListener('DBReady', () => {
    // Set today's date
    document.getElementById('hdr-date').valueAsDate = new Date();
    
    // Set next voucher number
    setNextVoucherNumber();

    // Add first row by default
    addRow();
});

function setNextVoucherNumber() {
    const seq = parseInt(localStorage.getItem('erp_receipt_seq') || '0') + 1;
    document.getElementById('voucher-number').innerText = String(seq).padStart(6, '0');
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
            <select class="grid-input" id="acc-${rowId}">
                ${accOptions}
            </select>
        </td>
        <td>
            <input type="text" class="grid-input amount" id="amt-${rowId}" value="0" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv(${rowId}, 'local')" onblur="this.value=fmtNum(parseNum(this.value)); calcTotals()">
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
        const amt = parseNum(document.getElementById(`amt-${id}`).value);
        const eqAmt = parseNum(document.getElementById(`eq-amt-${id}`).value);
        const curr = document.getElementById(`curr-${id}`).value;
        
        totalSDG += amt;
        if (curr === 'USD') totalUSD += eqAmt;
        if (curr === 'AED') totalAED += eqAmt;
    });
    
    document.getElementById('footer-grand').innerText = fmtNum(totalSDG);
    if(document.getElementById('header-grand-sdg')) document.getElementById('header-grand-sdg').innerText = fmtNum(totalSDG);
    if(document.getElementById('header-grand-usd')) document.getElementById('header-grand-usd').innerText = fmtNum(totalUSD);
    if(document.getElementById('header-grand-aed')) document.getElementById('header-grand-aed').innerText = fmtNum(totalAED);
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
        eq = rate === 0 ? 0 : amt / rate;
        eqEl.value = eq.toFixed(2);
    } else if (source === 'foreign') {
        amt = eq * rate;
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
                <input type="text" class="grid-input amount" id="amt-${rowId}" value="${det.amountLocal}" inputmode="decimal" onfocus="this.select()" oninput="calcEquiv(${rowId}, 'local')" onblur="this.value=fmtNum(parseNum(this.value)); calcTotals()">
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

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    // Save to LocalStorage
    const voucherData = {
        id: voucherNo,
        date: date,
        type: 'bulk_receipt',
        toAccount: toAccount,
        statement: globalStatement,
        totalAmount: total,
        details: details,
        timestamp: new Date().toISOString()
    };

    receiptsData = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
    
    // Balance update logic
    function applyBulkReceiptEffect(toAccId, detailsArray, isRevert = false) {
        let multiplier = isRevert ? -1 : 1;
        let totalToSafe = 0;
        
        let custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        let sups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
        
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
            let custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            let sups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
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
