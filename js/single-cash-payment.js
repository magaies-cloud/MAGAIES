/* ═══════════════════════════════════════════════════════
   single-cash-payment.js — Single Cash/Check Disbursement Module
   ═══════════════════════════════════════════════════════ */

let ACCOUNTS = [];

// Load real accounts from master-data
// (Moved to DBReady event)

let singlePaymentsData = [];
            try {
                singlePaymentsData = (window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : ((window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []))));
                if (!Array.isArray(singlePaymentsData)) singlePaymentsData = [];
            } catch(e) {
                singlePaymentsData = [];
            }
let currentViewedIndex = -1; 

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

function showDuplicateModal(transferNo) {
    const modalHTML = `
        <div id="duplicate-modal" style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.8); backdrop-filter:blur(5px); z-index:99999; display:flex; justify-content:center; align-items:center;">
            <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.5); padding:30px; border-radius:16px; text-align:center; max-width:400px; box-shadow:0 20px 50px rgba(0,0,0,0.5); animation:toastIn 0.3s ease;">
                <div style="font-size:3rem; margin-bottom:10px;">⚠️</div>
                <h2 style="color:#f87171; margin:0 0 15px 0;">تنبيه: حوالة مكررة!</h2>
                <p style="color:#fff; font-size:1rem; line-height:1.5;">رقم الحوالة <strong>(${transferNo})</strong> تم إدخاله مسبقاً في النظام.</p>
                <p style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:20px;">يرجى التأكد لتجنب إدخال نفس الحوالة مرتين عن طريق الخطأ.</p>
                <button onclick="document.getElementById('duplicate-modal').remove()" style="background:#ef4444; color:#fff; border:none; padding:10px 25px; border-radius:8px; font-weight:bold; cursor:pointer; font-size:1rem; width:100%;">فهمت، سأراجع الرقم</button>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

document.addEventListener('DBReady', () => {
    // Load accounts dynamically here to ensure DB_STORE is populated

    const fromSelect = document.getElementById('from-account');
    const toSelect = document.getElementById('to-account');
    if (fromSelect && toSelect && window.DB_STORE) {
        let options = '<option value="">-- اختر الحساب --</option>';
        Object.keys(window.DB_STORE).forEach(key => {
            const acc = window.DB_STORE[key];
            if (acc.name && !acc.deleted) {
                options += `<option value="${acc.id}">${acc.name}</option>`;
            }
        });
        fromSelect.innerHTML = options;
        toSelect.innerHTML = options;
    }

    const getTenantKey = window.getTenantKey || ((k) => k);
    let localCusts = [], localSups = [], localBanks = [], localSafes = [], localExp = [], localJobs = [];
    try { localCusts = (JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1')) || localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    try { localSups = (JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    try { localBanks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
    try { localSafes = JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1')) || localStorage.getItem('erp_safes_v1')) || []; } catch(e) {}
    try { localExp = JSON.parse(localStorage.getItem(getTenantKey('erp_accounts_v1')) || localStorage.getItem('erp_accounts_v1')) || []; } catch(e) {}
    try { localJobs = JSON.parse(localStorage.getItem(getTenantKey('erp_jobs_v1')) || localStorage.getItem('erp_jobs_v1')) || []; } catch(e) {}
    
    ACCOUNTS = [];
    localCusts.forEach(c => ACCOUNTS.push({ id: `cust-${c.id}`, name: c.name || c.customerName, type: 'عميل', color: '#10b981', balances: { checks: 0, debt: parseFloat(c.currentBalance) || 0, loan: 0 } }));
    localSups.forEach(s => ACCOUNTS.push({ id: `supp-${s.id}`, name: s.name || s.supplierName, type: 'مورد', color: '#f59e0b', balances: { checks: 0, debt: 0, loan: parseFloat(s.currentBalance) || 0 } }));
    localBanks.forEach(b => ACCOUNTS.push({ id: `bank-${b.id}`, name: b.name || b.bankName, type: 'بنك', color: '#3b82f6', balances: { checks: 0, debt: parseFloat(b.currentBalance) || 0, loan: 0 } }));
    localSafes.forEach(s => ACCOUNTS.push({ id: `safe-${s.id}`, name: s.name || s.safeName, type: 'خزنة', color: '#8b5cf6', balances: { checks: 0, debt: parseFloat(s.currentBalance) || 0, loan: 0 } }));
    localExp.forEach(e => ACCOUNTS.push({ id: `exp-${e.id}`, name: e.name || e.accountName, type: 'عام', color: '#64748b', balances: { checks: 0, debt: parseFloat(e.currentBalance) || 0, loan: 0 } }));

    const dateInputEl = document.getElementById('hdr-date');
    if (dateInputEl) {
        dateInputEl.valueAsDate = new Date();
        dateInputEl.addEventListener('change', () => {
            if (typeof currentViewedIndex !== 'undefined' && currentViewedIndex === -1) {
                setNextVoucherNumber();
            }
        });
    }
    setNextVoucherNumber();

    function buildOptions() {
        let html = '<option value="">-- اختر الحساب --</option>';
        if (localBanks.length || localSafes.length) {
            html += '<optgroup label="حسابات البنوك والخزن">';
            localSafes.forEach(s => html += `<option value="safe-${s.id}">${s.name}</option>`);
            localBanks.forEach(b => html += `<option value="bank-${b.id}">${b.name}</option>`);
            html += '</optgroup>';
        }
        if (localCusts.length) {
            html += '<optgroup label="العملاء والزبائن">';
            localCusts.forEach(c => html += `<option value="cust-${c.id}">${c.name}</option>`);
            html += '</optgroup>';
        }
        if (localSups.length) {
            html += '<optgroup label="الموردين">';
            localSups.forEach(s => html += `<option value="supp-${s.id}">${s.name}</option>`);
            html += '</optgroup>';
        }
        if (localExp.length) {
            html += '<optgroup label="المصاريف">';
            localExp.forEach(e => html += `<option value="exp-${e.id}">${e.name}</option>`);
            html += '</optgroup>';
        }
        return html;
    }

    const toAccEl = document.getElementById('to-account');
    const fromAccEl = document.getElementById('from-account');
    const costCenterEl = document.getElementById('cost-center');

    const opts = buildOptions();
    if(toAccEl) toAccEl.innerHTML = opts;
    if(fromAccEl) fromAccEl.innerHTML = opts;

    if (costCenterEl) {
        let jobOpts = '<option value="">-- بدون مركز تكلفة --</option>';
        localJobs.forEach(j => jobOpts += `<option value="${j.id}">${j.name}</option>`);
        costCenterEl.innerHTML = jobOpts;
    }

    // Auto-jump to next field on selection
        toAccEl.addEventListener('change', (e) => {
            if(e.detail && e.detail.value) document.getElementById('to-statement').focus();
        });
        fromAccEl.addEventListener('change', (e) => {
            if(e.detail && e.detail.value) {
                if (window.ccChoice) window.ccChoice.showDropdown();
                else document.getElementById('from-statement').focus();
            }
        });
        if (costCenterEl) costCenterEl.addEventListener('change', (e) => {
            if(e.detail && e.detail.value) document.getElementById('from-statement').focus();
        });


    toAccEl.addEventListener('change', updateBalances);
    
    // Initialize UI
    togglePaymentMethod();

    // Deep Drill-down
    const urlParams = new URLSearchParams(window.location.search);
    const voucherId = urlParams.get('id');
    if (voucherId) {
        singlePaymentsData = (window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : ((window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []))));
        const idx = singlePaymentsData.findIndex(v => String(v.id) === String(voucherId) || String(v.voucherNo) === String(voucherId) || String(v.paymentNo) === String(voucherId));
        if (idx !== -1) {
            currentViewedIndex = idx;
            renderVoucher(singlePaymentsData[idx]);
        }
    }
});

function updateBalances() {
    const accId = document.getElementById('to-account').value;
    const acc = ACCOUNTS.find(a => a.id === accId);
    if(acc && acc.balances) {
        document.getElementById('bal-checks').innerText = acc.balances.checks;
        document.getElementById('bal-debt').innerText = acc.balances.debt;
        document.getElementById('bal-loan').innerText = acc.balances.loan;
    } else {
        document.getElementById('bal-checks').innerText = '0';
        document.getElementById('bal-debt').innerText = '0';
        document.getElementById('bal-loan').innerText = '0';
    }
}

function togglePaymentMethod() {
    const methodEl = document.querySelector('input[name="payment_method"]:checked');
    if (!methodEl) return;
    const method = methodEl.value;
    
    // Check if the legacy elements exist (to prevent crashes on old layouts)
    const secMobile = document.getElementById('section-mobile');
    const secCheck = document.getElementById('section-check');
    if (secMobile) secMobile.classList.remove('active');
    if (secCheck) secCheck.classList.remove('active');

    if (method === 'mobile') {
        if (secMobile) secMobile.classList.add('active');
    } else if (method === 'check') {
        if (secCheck) secCheck.classList.add('active');
    } else if (method === 'hybrid') {
        if (secMobile) secMobile.classList.add('active');
        if (secCheck) secCheck.classList.add('active');
    }
}

function setNextVoucherNumber() {
    let spData = [];
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        spData = (window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem(getTenantKey('erp_single_payments_v1')) || localStorage.getItem('erp_single_payments_v1')) || []));
    } catch(e) {}

    // Base prefix on selected voucher date, not current system date
    const dateInput = document.getElementById('hdr-date');
    const selectedDate = (dateInput && dateInput.value) ? new Date(dateInput.value) : new Date();
    const validDate = isNaN(selectedDate.getTime()) ? new Date() : selectedDate;
    const yy = String(validDate.getFullYear()).substring(2, 4);
    const mm = String(validDate.getMonth() + 1).padStart(2, '0');
    const datePrefix = 'E' + yy + mm;

    let max = 0;
    spData.forEach(v => {
        const vNo = String(v.id || v.voucherNo || '');
        if (vNo.startsWith(datePrefix)) {
            const seqStr = vNo.substring(datePrefix.length);
            const seq = parseInt(seqStr, 10);
            if (!isNaN(seq) && seq > max) {
                max = seq;
            }
        }
    });

    const nextNo = `${datePrefix}${String(max + 1).padStart(4, '0')}`;
    const vNumEl = document.getElementById('voucher-number');
    if (vNumEl) {
        vNumEl.innerText = nextNo;
    }
}

window.adjustAmountFontSize = function(input) {
    const len = input.value.length;
    if (len > 14) {
        input.style.setProperty('font-size', '1.2rem', 'important');
        input.style.setProperty('letter-spacing', '0px', 'important');
    } else if (len > 11) {
        input.style.setProperty('font-size', '1.5rem', 'important');
        input.style.setProperty('letter-spacing', '1px', 'important');
    } else if (len > 8) {
        input.style.setProperty('font-size', '1.8rem', 'important');
        input.style.setProperty('letter-spacing', '2px', 'important');
    } else {
        input.style.setProperty('font-size', '2.5rem', 'important');
        input.style.setProperty('letter-spacing', '3px', 'important');
    }
};

function updateCurrency() {
    const curr = document.getElementById('currency').value;
    const rateEl = document.getElementById('exchange-rate');
    const symbolEl1 = document.getElementById('amount-currency-symbol');
    const symbolEl2 = document.getElementById('amount-foreign-currency-symbol');
    
    // Fetch local default currency from settings
    let localCurr = 'SDG';
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const settings = JSON.parse(localStorage.getItem(getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
        localCurr = settings.currency || 'SDG';
    } catch (e) {
        localCurr = 'SDG';
    }
    
    // symbolEl1 is the Local Amount (المبلغ), so it uses the system local currency
    if (symbolEl1) symbolEl1.innerText = localCurr;
    // symbolEl2 is the Foreign Amount (القيمة), so it uses the selected currency
    if (symbolEl2) symbolEl2.innerText = curr;

    if (curr === localCurr || curr === 'SDG') {
        rateEl.value = '1.0';
        rateEl.readOnly = true;
    } else {
        rateEl.readOnly = false;
        if (typeof window.getExchangeRateForCurrency === 'function') {
            const fetchedRate = window.getExchangeRateForCurrency(curr);
            if (fetchedRate && fetchedRate !== 1) {
                rateEl.value = fetchedRate;
            } else if(parseNum(rateEl.value) === 1) {
                rateEl.value = ''; 
            }
        } else {
            if(parseNum(rateEl.value) === 1) rateEl.value = ''; 
        }
    }
    calcEquiv('rate');
    if (typeof adjustAmountFontSize === 'function') {
        adjustAmountFontSize(rateEl);
    }
}

window.animateInputNumber = function(inputEl, endValueStr, duration = 300) {
    if (inputEl.animationInterval) {
        clearInterval(inputEl.animationInterval);
    }
    const endVal = parseNum(endValueStr);
    let startVal = parseNum(inputEl.value) || 0;
    
    if (Math.abs(endVal - startVal) < 0.01) {
        inputEl.value = endValueStr;
        if (typeof adjustAmountFontSize === 'function') adjustAmountFontSize(inputEl);
        return;
    }

    const startTime = performance.now();
    
    function addCommas(numStr) {
        let parts = numStr.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    inputEl.animationInterval = setInterval(() => {
        const elapsed = performance.now() - startTime;
        let progress = elapsed / duration;
        
        if (progress >= 1) {
            progress = 1;
            clearInterval(inputEl.animationInterval);
            inputEl.value = endValueStr;
        } else {
            const easeOut = progress * (2 - progress);
            const currentVal = startVal + (endVal - startVal) * easeOut;
            inputEl.value = addCommas(currentVal.toFixed(2));
        }
        if (typeof adjustAmountFontSize === 'function') adjustAmountFontSize(inputEl);
    }, 16);
};

function calcEquiv(source) {
    const amtEl = document.getElementById('amount-local');
    const rateEl = document.getElementById('exchange-rate');
    const eqEl = document.getElementById('amount-foreign');
    
    let amt = parseNum(amtEl.value);
    let rate = parseNum(rateEl.value) || 1;
    let eq = parseNum(eqEl.value);

    function addCommas(numStr) {
        let parts = numStr.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    if (source === 'local' || source === 'rate') {
        eq = rate === 0 ? 0 : amt / rate;
        if (typeof animateInputNumber === 'function') {
            animateInputNumber(eqEl, addCommas(eq.toFixed(2)));
        } else {
            eqEl.value = addCommas(eq.toFixed(2));
            if (typeof adjustAmountFontSize === 'function') adjustAmountFontSize(eqEl);
        }
    } else if (source === 'foreign') {
        amt = eq * rate;
        if (typeof animateInputNumber === 'function') {
            animateInputNumber(amtEl, addCommas(amt.toFixed(2)));
        } else {
            amtEl.value = addCommas(amt.toFixed(2));
            if (typeof adjustAmountFontSize === 'function') adjustAmountFontSize(amtEl);
        }
    }
    if (typeof syncFirstRowAmount === 'function') syncFirstRowAmount();
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
}

function resetVoucher() {
    window.location.href = window.location.pathname;
}

function resetVoucherSilent() {
    currentViewedIndex = -1;
    document.getElementById('hdr-date').valueAsDate = new Date();
    
    document.getElementById('to-account').value = '';
    document.getElementById('to-account-search').value = '';
    
    let localCurr = 'SDG';
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const settings = JSON.parse(localStorage.getItem(getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
        localCurr = settings.currency || 'SDG';
    } catch (e) { }

    document.getElementById('amount-local').value = '';
    document.getElementById('currency').value = localCurr;
    document.getElementById('exchange-rate').value = '1.0';
    document.getElementById('exchange-rate').readOnly = true;
    document.getElementById('amount-foreign').value = '';
    document.getElementById('to-statement').value = '';
    document.getElementById('beneficiary-name').value = '';
    
    document.getElementById('from-account').value = '';
    updateCurrency();
    const defFromAcc = (ACCOUNTS || []).find(a => String(a.id) === 'safe-001');
    document.getElementById('from-account-search').value = defFromAcc ? defFromAcc.name : '';
    if (window.fromAccChoice) {
        window.fromAccChoice.removeActiveItems();
        window.fromAccChoice.setChoiceByValue('');
    }
    
    const costCenterEl = document.getElementById('cost-center');
    if (costCenterEl) {
        costCenterEl.value = '';
        if (window.ccChoice) {
            window.ccChoice.removeActiveItems();
            window.ccChoice.setChoiceByValue('');
        }
    }
    
    document.getElementById('from-statement').value = '';

    // Reset Radio to Cash
    document.querySelector('input[name="payment_method"][value="cash"]').checked = true;
    togglePaymentMethod();

    if (typeof initPaymentContainers === 'function') initPaymentContainers();

    updateBalances();
    // setNextVoucherNumber();
    updateLedgerButtons();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
}

function loadPreviousVoucher() {
    singlePaymentsData = (window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : ((window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []))));
    if (singlePaymentsData.length === 0) return showToast("لا توجد سندات سابقة", "warning");
    
    if (currentViewedIndex === -1) {
        currentViewedIndex = singlePaymentsData.length - 1;
    } else if (currentViewedIndex > 0) {
        currentViewedIndex--;
    } else {
        return showToast("هذا هو أول سند تم حفظه", "warning");
    }
    renderVoucher(singlePaymentsData[currentViewedIndex]);
}

function loadNextVoucher() {
    singlePaymentsData = (window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : ((window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []))));
    if (currentViewedIndex === -1) return; // already at new
    
    if (currentViewedIndex < singlePaymentsData.length - 1) {
        currentViewedIndex++;
        renderVoucher(singlePaymentsData[currentViewedIndex]);
    } else {
        resetVoucherSilent();
        showToast("سند جديد", "success");
    }
}

function renderVoucher(v) {
    document.getElementById('voucher-number').innerText = v.id;
    document.getElementById('hdr-date').value = v.date;
    
    document.getElementById('to-account').value = v.toAccount || '';
    document.getElementById('to-account-search').value = getAccountName(v.toAccount || '');

    document.getElementById('amount-local').value = fmtNum(v.amountLocal);
    document.getElementById('currency').value = v.currency || 'SDG';
    document.getElementById('exchange-rate').value = v.rate || 1.0;
    document.getElementById('amount-foreign').value = fmtNum(v.amountForeign);
    document.getElementById('to-statement').value = v.toStatement || '';
    document.getElementById('beneficiary-name').value = v.beneficiaryName || '';
    
    document.getElementById('from-account').value = v.fromAccount || 'safe-001';
    document.getElementById('from-account-search').value = getAccountName(v.fromAccount || 'safe-001');
    if (window.fromAccChoice) window.fromAccChoice.setChoiceByValue(v.fromAccount || 'safe-001');

    const costCenterEl = document.getElementById('cost-center');
    if (costCenterEl) {
        costCenterEl.value = v.costCenter || '';
        if (window.ccChoice) window.ccChoice.setChoiceByValue(v.costCenter || '');
    }

    document.getElementById('from-statement').value = v.fromStatement || '';

    // Set Payment Method
    const method = v.paymentMethod || 'cash';
    const methodEl = document.querySelector(`input[name="payment_method"][value="${method}"]`); if (methodEl) { methodEl.checked = true; } else { /* Fallback */ const firstMethod = document.querySelector(`input[name="payment_method"]`); if(firstMethod) firstMethod.checked = true; }
    togglePaymentMethod();

    if (typeof loadVoucherPaymentDetails === 'function') {
        loadVoucherPaymentDetails(v);
    }
    
    updateCurrency();
    updateBalances();
    updateLedgerButtons();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
}

function submitVoucher() {
    try { // WRAPPED
    const amtLocal = parseNum(document.getElementById('amount-local').value);
    const toAcc = document.getElementById('to-account').value;
    const fromAcc = document.getElementById('from-account').value;

    if (amtLocal <= 0) {
        showToast("لا يمكن اعتماد سند بقيمة صفر", "error");
        return;
    }
    if (!toAcc) {
        showToast("يرجى اختيار الحساب المستفيد", "error");
        return;
    }
    if (!fromAcc) {
        showToast("يرجى اختيار حساب الدفع (الدافع)", "error");
        return;
    }function showProfessionalSuccess(voucherId, fromName, toName, amountLocal, currency, title) {
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0'; overlay.style.left = '0';
    overlay.style.width = '100vw'; overlay.style.height = '100vh';
    overlay.style.background = 'rgba(5, 3, 8, 0.7)';
    overlay.style.backdropFilter = 'blur(12px)';
    overlay.style.zIndex = '999999';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.opacity = '0';
    overlay.style.transition = 'opacity 0.4s ease';

    const box = document.createElement('div');
    box.style.background = 'linear-gradient(145deg, #1e1b4b, #312e81)';
    box.style.padding = '40px 50px';
    box.style.borderRadius = '24px';
    box.style.boxShadow = '0 30px 60px rgba(0,0,0,0.8), 0 0 40px rgba(99, 102, 241, 0.5) inset';
    box.style.border = '1px solid rgba(167, 139, 250, 0.3)';
    box.style.textAlign = 'center';
    box.style.transform = 'scale(0.8) translateY(30px)';
    box.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
    box.style.color = '#fff';
    box.style.minWidth = '400px';

    box.innerHTML = `
        <style>
            @keyframes drawCheck {
                0% { stroke-dasharray: 0, 100; stroke-dashoffset: 0; opacity: 0; }
                50% { opacity: 1; }
                100% { stroke-dasharray: 100, 0; stroke-dashoffset: 0; opacity: 1; }
            }
            @keyframes pulseGlow {
                0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
                70% { box-shadow: 0 0 0 25px rgba(16, 185, 129, 0); }
                100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
            }
            @keyframes slideRight {
                0% { transform: translateX(-30px); opacity: 0; }
                100% { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideLeft {
                0% { transform: translateX(30px); opacity: 0; }
                100% { transform: translateX(0); opacity: 1; }
            }
            @keyframes arrowMove {
                0% { transform: translateX(0); }
                50% { transform: translateX(-15px); text-shadow: 0 0 15px rgba(99,102,241,1); }
                100% { transform: translateX(0); }
            }
        </style>
        <div style="width: 90px; height: 90px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; margin: 0 auto 25px auto; display: flex; align-items: center; justify-content: center; animation: pulseGlow 2s infinite; border: 4px solid #34d399;">
            <svg width="45" height="45" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray: 100; animation: drawCheck 0.8s ease-out forwards;">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h2 style="margin: 0 0 10px 0; font-size: 2rem; font-weight: 800; color: #34d399; text-shadow: 0 2px 8px rgba(0,0,0,0.5);">${title}</h2>
        <p style="margin: 0 0 30px 0; font-size: 1.2rem; color: #e2e8f0;">تم اعتماد السند المالي رقم <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 6px;">${voucherId}</strong></p>
        
        <div style="background: rgba(0,0,0,0.4); padding: 20px; border-radius: 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 10px 25px rgba(0,0,0,0.5) inset;">
            <div style="flex: 1; text-align: right; animation: slideRight 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; animation-delay: 0.2s; opacity: 0;">
                <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 6px;">من حساب (الدافع)</div>
                <div style="font-weight: 900; color: #38bdf8; font-size: 1.1rem; line-height: 1.4;">${fromName}</div>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center; animation: arrowMove 1.5s ease-in-out infinite;">
                <div style="color: #34d399; font-weight: 900; font-size: 1.4rem; margin-bottom: 5px; text-shadow: 0 0 10px rgba(52,211,153,0.5);">${Number(amountLocal).toLocaleString()}</div>
                <div style="font-size: 0.8rem; font-weight: bold; color: #059669; background: #d1fae5; padding: 2px 10px; border-radius: 6px;">${currency}</div>
                <div style="font-size: 2rem; color: #818cf8; margin-top: 8px;">⬅️</div>
            </div>
            
            <div style="flex: 1; text-align: left; animation: slideLeft 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; animation-delay: 0.4s; opacity: 0;">
                <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 6px;">إلى حساب (المستفيد)</div>
                <div style="font-weight: 900; color: #f472b6; font-size: 1.1rem; line-height: 1.4;">${toName}</div>
            </div>
        </div>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        box.style.transform = 'scale(1) translateY(0)';
    });

    if (window.PlaySound && window.PlaySound.success) {
        window.PlaySound.success();
    }

    setTimeout(() => {
        overlay.style.opacity = '0';
        box.style.transform = 'scale(0.8) translateY(-40px)';
        setTimeout(() => {
            if (document.body.contains(overlay)) {
                document.body.removeChild(overlay);
            }
        }, 600);
    }, 2500);
}    const _docDateStr = document.getElementById('hdr-date') ? document.getElementById('hdr-date').value : new Date().toISOString().split('T')[0];
    if (!window.canCreateRecord(_docDateStr)) return;

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    const method = document.querySelector('input[name="payment_method"]:checked').value;

    const voucherData = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        id: document.getElementById('voucher-number').innerText,
        date: document.getElementById('hdr-date').value,
        type: 'single_payment',
        
        toAccount: toAcc,
        amountLocal: amtLocal,
        currency: document.getElementById('currency').value,
        rate: parseNum(document.getElementById('exchange-rate').value),
        amountForeign: parseNum(document.getElementById('amount-foreign').value),
        toStatement: document.getElementById('to-statement').value,
        beneficiaryName: document.getElementById('beneficiary-name').value,
        
        fromAccount: document.getElementById('from-account').value,
        fromStatement: document.getElementById('from-statement').value,
        costCenter: document.getElementById('cost-center') ? document.getElementById('cost-center').value : '',
        
        paymentMethod: method,
        
        timestamp: new Date().toISOString()
    };

    if (method === 'mobile') {
        const transferRows = document.querySelectorAll('.transfer-row');
        let transfers = [];
        let totalTransfersAmount = 0;
        let hasInvalidTransfer = false;

        transferRows.forEach((row, idx) => {
            const trNo = row.querySelector('.transfer-no').value.trim();
            const trNote = row.querySelector('.transfer-note').value.trim();
            const trAmt = parseNum(row.querySelector('.transfer-amount').value);
            const trStmt = row.querySelector('.transfer-statement')?.value.trim() || '';

            if (!trNo) {
                showToast(`السطر ${idx + 1}: يرجى إدخال رقم حساب التحويل`, "error");
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (trNo.length < 6) {
                showToast(`السطر ${idx + 1}: رقم التحويل يجب أن يكون 6 أرقام على الأقل`, "error");
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (!window.isBankReferenceUnique(trNo, 'mobile', document.getElementById('voucher-number').innerText)) {
                window.showDuplicateWarningModal(trNo, 'mobile');
                hasInvalidTransfer = true;
                return;
            }

            transfers.push({ transferNo: trNo, note: trNote, amount: trAmt, statement: trStmt });
            totalTransfersAmount += trAmt;
        });

        if (hasInvalidTransfer) return;

        if (Math.abs(totalTransfersAmount - amtLocal) > 0.01) {
            showToast("مجموع مبالغ التحويلات لا يساوي مبلغ السند الإجمالي.", "error");
            return;
        }

        voucherData.bankTransfers = transfers;
        if (transfers.length > 0) {
            voucherData.mobileTransferNo = transfers[0].transferNo;
            voucherData.mobileNote = transfers[0].note;
        }
    } else if (method === 'check') {
        const chequeRows = document.querySelectorAll('.cheque-row');
        let cheques = [];
        let totalChequesAmount = 0;
        let hasInvalidCheque = false;

        chequeRows.forEach((row, idx) => {
            const chNo = row.querySelector('.cheque-no').value.trim();
            const chBranch = row.querySelector('.cheque-branch').value.trim();
            const chDate = row.querySelector('.cheque-date').value;
            const chAmt = parseNum(row.querySelector('.cheque-amount').value);
            const chStmt = row.querySelector('.cheque-statement')?.value.trim() || '';
            if (!chBranch) {
                showToast(`السطر ${idx + 1}: يرجى إدخال اسم البنك`, "error");
                row.querySelector('.cheque-branch').focus();
                hasInvalidCheque = true;
                return;
            }
            if (!chNo || chNo.length < 6) {
                showToast(`السطر ${idx + 1}: رقم الشيك يجب أن يكون 6 أرقام على الأقل`, "error");
                row.querySelector('.cheque-no').focus();
                hasInvalidCheque = true;
                return;
            }
            if (!window.isBankReferenceUnique(chNo, 'check', document.getElementById('voucher-number').innerText)) {
                window.showDuplicateWarningModal(chNo, 'check');
                hasInvalidCheque = true;
                return;
            }

            cheques.push({ checkNo: chNo, checkBranch: chBranch, checkDate: chDate, amount: chAmt, statement: chStmt });
            totalChequesAmount += chAmt;
        });

        if (hasInvalidCheque) return;

        if (Math.abs(totalChequesAmount - amtLocal) > 0.01) {
            showToast("مجموع مبالغ الشيكات لا يساوي مبلغ السند الإجمالي.", "error");
            return;
        }

        voucherData.cheques = cheques;
        if (cheques.length > 0) {
            voucherData.checkNo = cheques[0].checkNo;
            voucherData.checkBranch = cheques[0].checkBranch;
            voucherData.checkDate = cheques[0].checkDate;
            voucherData.checkDueDate = '';
            voucherData.checkRef = '';
            voucherData.checkRefNo = '';
            voucherData.checkSettleLocal = false;
        }
    } else if (method === 'hybrid') {
        const transferRows = document.querySelectorAll('.transfer-row');
        let transfers = [];
        let totalTransfersAmount = 0;
        let hasInvalidTransfer = false;

        transferRows.forEach((row, idx) => {
            const trNo = row.querySelector('.transfer-no').value.trim();
            const trNote = row.querySelector('.transfer-note').value.trim();
            const trAmt = parseNum(row.querySelector('.transfer-amount').value);
            const trStmt = row.querySelector('.transfer-statement')?.value.trim() || '';

            if (!trNo) {
                showToast(`السطر ${idx + 1}: يرجى إدخال رقم حساب التحويل`, "error");
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (trNo.length < 6) {
                showToast(`السطر ${idx + 1}: رقم التحويل يجب أن يكون 6 أرقام على الأقل`, "error");
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (!window.isBankReferenceUnique(trNo, 'mobile', document.getElementById('voucher-number').innerText)) {
                window.showDuplicateWarningModal(trNo, 'mobile');
                hasInvalidTransfer = true;
                return;
            }

            transfers.push({ transferNo: trNo, note: trNote, amount: trAmt, statement: trStmt });
            totalTransfersAmount += trAmt;
        });

        if (hasInvalidTransfer) return;

        const chequeRows = document.querySelectorAll('.cheque-row');
        let cheques = [];
        let totalChequesAmount = 0;
        let hasInvalidCheque = false;

        chequeRows.forEach((row, idx) => {
            const chNo = row.querySelector('.cheque-no').value.trim();
            const chBranch = row.querySelector('.cheque-branch').value.trim();
            const chDate = row.querySelector('.cheque-date').value;
            const chAmt = parseNum(row.querySelector('.cheque-amount').value);
            const chStmt = row.querySelector('.cheque-statement')?.value.trim() || '';
            if (!chBranch) {
                showToast(`السطر ${idx + 1}: يرجى إدخال اسم البنك`, "error");
                row.querySelector('.cheque-branch').focus();
                hasInvalidCheque = true;
                return;
            }
            if (!chNo || chNo.length < 6) {
                showToast(`السطر ${idx + 1}: رقم الشيك يجب أن يكون 6 أرقام على الأقل`, "error");
                row.querySelector('.cheque-no').focus();
                hasInvalidCheque = true;
                return;
            }
            if (!window.isBankReferenceUnique(chNo, 'check', document.getElementById('voucher-number').innerText)) {
                window.showDuplicateWarningModal(chNo, 'check');
                hasInvalidCheque = true;
                return;
            }

            cheques.push({ checkNo: chNo, checkBranch: chBranch, checkDate: chDate, amount: chAmt, statement: chStmt });
            totalChequesAmount += chAmt;
        });

        if (hasInvalidCheque) return;

        if (Math.abs((totalTransfersAmount + totalChequesAmount) - amtLocal) > 0.01) {
            showToast("مجموع مبالغ التحويلات والشيكات لا يساوي مبلغ السند الإجمالي.", "error");
            return;
        }

        voucherData.bankTransfers = transfers;
        if (transfers.length > 0) {
            voucherData.mobileTransferNo = transfers[0].transferNo;
            voucherData.mobileNote = transfers[0].note;
        }
        voucherData.cheques = cheques;
        if (cheques.length > 0) {
            voucherData.checkNo = cheques[0].checkNo;
            voucherData.checkBranch = cheques[0].checkBranch;
            voucherData.checkDate = cheques[0].checkDate;
            voucherData.checkDueDate = '';
            voucherData.checkRef = '';
            voucherData.checkRefNo = '';
            voucherData.checkSettleLocal = false;
        }
    }

    const getTenantKey = window.getTenantKey || ((k) => k);
    singlePaymentsData = (window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem(getTenantKey('erp_single_payments_v1')) || localStorage.getItem('erp_single_payments_v1')) || []));
    
    // Balance update logic
    function applyPaymentEffect(fromAccId, toAccId, amount, isRevert = false) {
        if (!fromAccId || !toAccId) return;
        let multiplier = isRevert ? -1 : 1;
        let amtSafe = -amount * multiplier; // safe decreases
        let amtBeneficiary = -amount * multiplier; // beneficiary balance decreases (repaid)

        // Helper function for multi-tenant local storage
        const loadEntity = (key) => {
            try { return JSON.parse(localStorage.getItem(getTenantKey(key)) || localStorage.getItem(key)) || []; } catch(e) { return []; }
        };
        const saveEntity = (key, data) => {
            localStorage.setItem(getTenantKey(key), JSON.stringify(data));
        };

        // 1. Update fromAccount (Safe/Bank)
        if (fromAccId.startsWith('safe-')) {
            let id = fromAccId.substring(5);
            let safes = loadEntity('erp_safes_v1');
            let idx = safes.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtSafe;
                saveEntity('erp_safes_v1', safes);
            }
        } else if (fromAccId.startsWith('bank-')) {
            let id = fromAccId.substring(5);
            let banks = loadEntity('erp_banks_v1');
            let idx = banks.findIndex(b => String(b.id) === String(id));
            if (idx > -1) {
                banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtSafe;
                saveEntity('erp_banks_v1', banks);
            }
        }

        // 2. Update toAccount (Beneficiary: Customer/Supplier/Safe/Bank/Expense)
        if (toAccId.startsWith('cust-')) {
            let id = toAccId.substring(5);
            let custs = loadEntity('erp_customers_v1');
            let idx = custs.findIndex(c => String(c.id) === String(id));
            if (idx > -1) {
                // When we pay a customer, their debt to us INCREASES (or our debt to them decreases). Customer balance is Debit.
                custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amount * multiplier;
                saveEntity('erp_customers_v1', custs);
            }
        } else if (toAccId.startsWith('supp-')) {
            let id = toAccId.substring(5);
            let sups = loadEntity('erp_suppliers_v1');
            let idx = sups.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtBeneficiary;
                saveEntity('erp_suppliers_v1', sups);
            }
        } else if (toAccId.startsWith('bank-')) {
            let id = toAccId.substring(5);
            let banks = loadEntity('erp_banks_v1');
            let idx = banks.findIndex(b => String(b.id) === String(id));
            if (idx > -1) {
                banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtBeneficiary;
                saveEntity('erp_banks_v1', banks);
            }
        } else if (toAccId.startsWith('safe-')) {
            let id = toAccId.substring(5);
            let safes = loadEntity('erp_safes_v1');
            let idx = safes.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtBeneficiary;
                saveEntity('erp_safes_v1', safes);
            }
        } else if (toAccId.startsWith('exp-')) {
            let id = toAccId.substring(4);
            let accounts = loadEntity('erp_accounts_v1');
            let idx = accounts.findIndex(a => String(a.id) === String(id));
            if (idx > -1) {
                accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amount * multiplier;
                saveEntity('erp_accounts_v1', accounts);
            }
        }
    }

        let fromSearch = document.getElementById('from-account-search');
        let fromName = fromSearch && fromSearch.value ? fromSearch.value : voucherData.fromAccount;
        let toSearch = document.getElementById('to-account-search');
        let toName = toSearch && toSearch.value ? toSearch.value : voucherData.toAccount;
        
        if (currentViewedIndex !== -1) {
            if (!window.canEditRecord(voucherData.date, 'finance')) return;
            
            // Revert old payment effect
            const oldVoucher = singlePaymentsData[currentViewedIndex];
            applyPaymentEffect(oldVoucher.fromAccount, oldVoucher.toAccount, oldVoucher.amountLocal, true);
            
            // Apply new payment effect
            applyPaymentEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
            
            singlePaymentsData[currentViewedIndex] = voucherData;
            localStorage.setItem(getTenantKey('erp_single_payments_v1'), JSON.stringify(singlePaymentsData));
            showProfessionalSuccess(voucherData.id, fromName, toName, voucherData.amountLocal, voucherData.currency, 'تم التعديل بنجاح');
        } else {
            applyPaymentEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
            singlePaymentsData.push(voucherData);
            localStorage.setItem(getTenantKey('erp_single_payments_v1'), JSON.stringify(singlePaymentsData));
            const currentSeq = parseInt(localStorage.getItem(getTenantKey('erp_single_payment_seq')) || localStorage.getItem('erp_single_payment_seq') || '0');
            localStorage.setItem(getTenantKey('erp_single_payment_seq'), currentSeq + 1);
            showProfessionalSuccess(voucherData.id, fromName, toName, voucherData.amountLocal, voucherData.currency, 'تم الحفظ بنجاح');
        }

        setTimeout(() => { resetVoucherSilent(); }, 2500);
    } catch (err) {
        console.error(err);
        alert("حدث خطأ أثناء الحفظ: " + err.message + "في السطر: " + err.stack);
    }
}

function buildVoucherPrintHTML() {
    let printWrapper = document.getElementById('voucher-print-wrapper');
    let printArea = document.getElementById('voucher-print-area');
    
    if (!printWrapper) {
        printWrapper = document.createElement('div');
        printWrapper.id = 'voucher-print-wrapper';
        printWrapper.style.display = 'none';
        document.body.appendChild(printWrapper);
        
        printArea = document.createElement('div');
        printArea.id = 'voucher-print-area';
        printWrapper.appendChild(printArea);
    }
    
    // Get Company Settings
    let cfg = {};
    try {
        cfg = JSON.parse(localStorage.getItem('erp_company_settings')) || {};
    } catch(e) {}
    
    const vCompany = cfg.companyName || document.getElementById('header-company-name-top')?.innerText || 'مقاييس (MAGAIEES)';
    const logoData = cfg.logoBase64 || '';
    const logoHtml = logoData ? `<img src="${logoData}" style="max-height: 80px; max-width: 180px; object-fit: contain; border-radius: 8px;">` : `<div style="font-size:24px; font-weight:900; color:#0f172a;">${vCompany}</div>`;
    
    // Gather values
    const vDate = document.getElementById('hdr-date') ? document.getElementById('hdr-date').value : '';
    const vNo = document.getElementById('voucher-number') ? document.getElementById('voucher-number').innerText : '';
    const vUser = document.getElementById('header-user-name') ? document.getElementById('header-user-name').innerText : '';
    const vAmount = document.getElementById('amount-local') ? document.getElementById('amount-local').value : '';
    
    const vCurSelect = document.getElementById('currency');
    const vCurrency = vCurSelect && vCurSelect.selectedIndex > -1 ? vCurSelect.options[vCurSelect.selectedIndex].text : '';
    
    const vRate = document.getElementById('exchange-rate') ? document.getElementById('exchange-rate').value : '';
    const vValue = document.getElementById('amount-foreign') ? document.getElementById('amount-foreign').value : '';
    
    const fromInput = document.getElementById('from-account-search');
    const vFrom = fromInput ? fromInput.value : '';
    
    const toInput = document.getElementById('to-account-search');
    const vTo = toInput ? toInput.value : '';
    
    const vBeneficiary = document.getElementById('beneficiary-name') ? document.getElementById('beneficiary-name').value : '';
    const vStatement = document.getElementById('to-statement') ? document.getElementById('to-statement').value : '';
    
    const payMethodEl = document.querySelector('input[name="payment_method"]:checked');
    let vMethod = 'نقدي';
    if(payMethodEl) {
        if(payMethodEl.id === 'pay-cash') vMethod = 'نقدي';
        else if(payMethodEl.id === 'pay-check') vMethod = 'شيك';
        else if(payMethodEl.id === 'pay-transfer') vMethod = 'تحويل بنكي';
        else if(payMethodEl.id === 'pay-card') vMethod = 'بطاقة بنكية';
    }

    const premiumHtml = `
<div class="voucher-print" style="direction: rtl; font-family: 'Tajawal', 'Cairo', sans-serif; background: #fff; color: #0f172a; padding: 20px;">
    
    <!-- Header with Logo -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 30px;">
        <div style="text-align: right; width: 33%;">
            <h2 style="margin: 0; font-size: 22px; font-weight: 900; color: #1e293b;">${vCompany}</h2>
            <div style="color: #64748b; font-size: 14px; margin-top: 5px;">نظام إدارة الأعمال المتكامل</div>
        </div>
        <div style="text-align: center; width: 33%;">
            <div style="display: inline-block; padding: 10px 25px; border: 2px solid #0f172a; border-radius: 12px; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 24px; font-weight: 900; color: #0f172a;">سند صرف نقدية</h3>
                <div style="font-size: 14px; font-weight: bold; color: #b91c1c; margin-top: 4px;">حركة مالية</div>
            </div>
        </div>
        <div style="text-align: left; width: 33%; display: flex; justify-content: flex-end;">
            ${logoHtml}
        </div>
    </div>
    
    <!-- Meta Data Grid -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; background: #f1f5f9; border-radius: 12px; padding: 20px; margin-bottom: 30px; border: 1px solid #cbd5e1;">
        <div style="width: 30%; margin-bottom: 10px;">
            <div style="font-size: 12px; color: #64748b; font-weight: bold;">رقم المستند</div>
            <div style="font-size: 18px; font-weight: 900; color: #b91c1c; font-family: monospace;">${vNo}</div>
        </div>
        <div style="width: 30%; margin-bottom: 10px;">
            <div style="font-size: 12px; color: #64748b; font-weight: bold;">التاريخ</div>
            <div style="font-size: 16px; font-weight: bold; color: #0f172a;">${vDate}</div>
        </div>
        <div style="width: 30%; margin-bottom: 10px;">
            <div style="font-size: 12px; color: #64748b; font-weight: bold;">المستخدم المستخرج</div>
            <div style="font-size: 16px; font-weight: bold; color: #0f172a;">${vUser}</div>
        </div>
    </div>
    
    <!-- Financial Values -->
    <div style="display: flex; justify-content: space-between; border-radius: 12px; border: 2px solid #0ea5e9; overflow: hidden; margin-bottom: 30px;">
        <div style="flex: 1; text-align: center; padding: 15px; background: rgba(14, 165, 233, 0.1);">
            <div style="font-size: 14px; color: #0284c7; font-weight: bold; margin-bottom: 5px;">المبلغ (محلي)</div>
            <div style="font-size: 28px; font-weight: 900; color: #0f172a;">${vAmount}</div>
        </div>
        <div style="width: 1px; background: #0ea5e9;"></div>
        <div style="flex: 1; text-align: center; padding: 15px;">
            <div style="font-size: 14px; color: #64748b; font-weight: bold; margin-bottom: 5px;">العملة</div>
            <div style="font-size: 20px; font-weight: bold; color: #0f172a;">${vCurrency}</div>
        </div>
        <div style="width: 1px; background: #0ea5e9;"></div>
        <div style="flex: 1; text-align: center; padding: 15px;">
            <div style="font-size: 14px; color: #64748b; font-weight: bold; margin-bottom: 5px;">سعر التعادل</div>
            <div style="font-size: 20px; font-weight: bold; color: #0f172a;">${vRate}</div>
        </div>
        <div style="width: 1px; background: #0ea5e9;"></div>
        <div style="flex: 1; text-align: center; padding: 15px; background: rgba(245, 158, 11, 0.1);">
            <div style="font-size: 14px; color: #d97706; font-weight: bold; margin-bottom: 5px;">القيمة (أجنبي)</div>
            <div style="font-size: 20px; font-weight: 900; color: #0f172a;">${vValue}</div>
        </div>
    </div>

    <!-- Accounts Info -->
    <div style="margin-bottom: 40px;">
        <h4 style="font-size: 18px; color: #1e293b; border-bottom: 2px solid #cbd5e1; padding-bottom: 10px; margin-bottom: 20px;">تفاصيل الحسابات</h4>
        
        <table style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
            <tr>
                <td style="width: 25%; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 8px 8px 0; font-weight: bold; color: #475569;">من حساب (الصرف):</td>
                <td style="padding: 15px; border: 1px solid #e2e8f0; border-right: none; border-radius: 8px 0 0 8px; font-weight: 900; font-size: 18px; color: #1d4ed8;">${vFrom}</td>
            </tr>
            <tr>
                <td style="padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 8px 8px 0; font-weight: bold; color: #475569;">إلى حساب (الاستلام):</td>
                <td style="padding: 15px; border: 1px solid #e2e8f0; border-right: none; border-radius: 8px 0 0 8px; font-weight: 900; font-size: 18px; color: #047857;">${vTo}</td>
            </tr>
            <tr>
                <td style="padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 8px 8px 0; font-weight: bold; color: #475569;">اسم المستفيد:</td>
                <td style="padding: 15px; border: 1px solid #e2e8f0; border-right: none; border-radius: 8px 0 0 8px; font-size: 16px; color: #0f172a;">${vBeneficiary || '—'}</td>
            </tr>
            <tr>
                <td style="padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 8px 8px 0; font-weight: bold; color: #475569;">طريقة الدفع:</td>
                <td style="padding: 15px; border: 1px solid #e2e8f0; border-right: none; border-radius: 8px 0 0 8px; font-size: 16px; color: #0f172a;">
                    <span style="display: inline-block; padding: 4px 12px; background: #e0e7ff; color: #4338ca; border-radius: 6px; font-weight: bold;">${vMethod}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 8px 8px 0; font-weight: bold; color: #475569;">البيان / الملاحظات:</td>
                <td style="padding: 15px; border: 1px solid #e2e8f0; border-right: none; border-radius: 8px 0 0 8px; font-size: 16px; color: #0f172a;">${vStatement || '—'}</td>
            </tr>
        </table>
    </div>

    <!-- Signatures -->
    <div style="display: flex; justify-content: space-around; margin-top: 60px; padding-top: 30px; border-top: 1px dashed #cbd5e1;">
        <div style="text-align: center; width: 25%;">
            <div style="height: 60px;"></div>
            <hr style="border-top: 2px solid #94a3b8; margin-bottom: 10px;">
            <strong style="color: #475569;">توقيع المستلم</strong>
        </div>
        <div style="text-align: center; width: 25%;">
            <div style="height: 60px;"></div>
            <hr style="border-top: 2px solid #94a3b8; margin-bottom: 10px;">
            <strong style="color: #475569;">توقيع المحاسب</strong>
        </div>
        <div style="text-align: center; width: 25%;">
            <div style="height: 60px;"></div>
            <hr style="border-top: 2px solid #94a3b8; margin-bottom: 10px;">
            <strong style="color: #475569;">الاعتماد</strong>
        </div>
    </div>
</div>
    `;
    
    printArea.innerHTML = premiumHtml;
    
    const previewPaper = document.getElementById('premium-voucher-paper');
    if(previewPaper) {
        previewPaper.innerHTML = premiumHtml;
    }
}

function previewVoucher() {
    buildVoucherPrintHTML();
    const modal = document.getElementById('premium-voucher-preview-modal');
    if(modal) {
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.querySelector('.glass-panel').style.transform = 'translateY(0)';
        }, 10);
    }
}

function closePremiumPreview() {
    const modal = document.getElementById('premium-voucher-preview-modal');
    if(modal) {
        modal.style.opacity = '0';
        modal.querySelector('.glass-panel').style.transform = 'translateY(30px)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

function printVoucher() {
    buildVoucherPrintHTML();
    
    if (!document.getElementById('voucher-print-css')) {
        const style = document.createElement('style');
        style.id = 'voucher-print-css';
        style.textContent = `
            @media print {
                body * {
                    visibility: hidden;
                }
                #voucher-print-wrapper, #voucher-print-wrapper * {
                    visibility: visible;
                }
                #voucher-print-wrapper {
                    display: block !important;
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    margin: 0;
                    padding: 0;
                }
                @page {
                    size: A4 portrait;
                    margin: 10mm;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    const printWrapper = document.getElementById('voucher-print-wrapper');
    if (printWrapper) printWrapper.style.display = 'block';

    setTimeout(() => {
        window.print();
        if (printWrapper) printWrapper.style.display = 'none';
    }, 200);
}

function confirmDelete() {
    if (currentViewedIndex === -1) {
        showToast("لا يوجد سند محفوظ لحذفه حالياً", "warning");
        return;
    }
    if (confirm("هل أنت متأكد من حذف هذا السند نهائياً؟")) {
        const oldVoucher = singlePaymentsData[currentViewedIndex];
        
        // Revert old payment effect
        function applyPaymentEffect(fromAccId, toAccId, amount, isRevert = false) {
            let multiplier = isRevert ? -1 : 1;
            let amtSafe = -amount * multiplier;
            let amtBeneficiary = -amount * multiplier;

            let custs = [];
            try {
                custs = ((window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []))).filter(x => !x.deleted);
                if (!Array.isArray(custs)) custs = [];
            } catch(e) {
                custs = [];
            }
            let cIdx = custs.findIndex(c => c.id === toAccId);
            if (cIdx > -1) {
                custs[cIdx].currentBalance = (custs[cIdx].currentBalance || 0) + amtBeneficiary;
                localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
            } else {
                let sups = [];
            try {
                sups = ((window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []))).filter(x => !x.deleted);
                if (!Array.isArray(sups)) sups = [];
            } catch(e) {
                sups = [];
            }
                let sIdx = sups.findIndex(s => s.id === toAccId);
                if (sIdx > -1) {
                    sups[sIdx].currentBalance = (sups[sIdx].currentBalance || 0) + amtBeneficiary;
                    localStorage.setItem('erp_suppliers_v1', JSON.stringify(sups));
                }
            }

            let banks = [];
            try {
                banks = (window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []));
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            let bIdx = banks.findIndex(b => b.id === fromAccId);
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (banks[bIdx].currentBalance || 0) + amtSafe;
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            }
        }
        
        applyPaymentEffect(oldVoucher.fromAccount, oldVoucher.toAccount, oldVoucher.amountLocal, true);

        singlePaymentsData.splice(currentViewedIndex, 1);
        localStorage.setItem('erp_single_payments_v1', JSON.stringify(singlePaymentsData));
        showToast("تم حذف السند بنجاح", "success");
        resetVoucherSilent();
    }
}

// ================= UI/UX ENHANCEMENTS (ANIMATIONS & VALIDATION) =================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Strict Number Validation & Feedback
    const numberInputs = document.querySelectorAll('input[inputmode="numeric"], input[inputmode="decimal"], .amount');
    numberInputs.forEach(input => {
        input.addEventListener('keydown', (e) => {
            if (e.key.length === 1 && !/[0-9.]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                // Visual feedback for invalid input
                input.style.boxShadow = '0 0 15px rgba(239, 68, 68, 0.8)';
                input.style.borderColor = '#ef4444';
                input.style.transform = 'translateY(-2px) rotate(1deg)';
                setTimeout(() => {
                    input.style.boxShadow = '';
                    input.style.borderColor = '';
                    input.style.transform = '';
                }, 200);
            }
        });
    });

    // 2. Enhance Enter Navigation visually
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            setTimeout(() => {
                const active = document.activeElement;
                if(active && active.tagName === 'INPUT') {
                    active.style.boxShadow = '0 0 20px hsla(160, 70%, 45%, 0.4)';
                    setTimeout(() => active.style.boxShadow = '', 300);
                }
            }, 50);
        }
    });

    updateLedgerButtons();
});

window.buildAccountsArray = function() {
    const getTenantKey = window.getTenantKey || ((k) => k);
    let localCusts = [], localSups = [], localBanks = [], localSafes = [], localExp = [], localJobs = [];
    try { localCusts = (JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1')) || localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    try { localSups = (JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    try { localBanks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
    try { localSafes = JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1')) || localStorage.getItem('erp_safes_v1')) || []; } catch(e) {}
    try { localExp = JSON.parse(localStorage.getItem(getTenantKey('erp_accounts_v1')) || localStorage.getItem('erp_accounts_v1')) || []; } catch(e) {}
    try { localJobs = JSON.parse(localStorage.getItem(getTenantKey('erp_jobs_v1')) || localStorage.getItem('erp_jobs_v1')) || []; } catch(e) {}
    
    ACCOUNTS = [];
    localCusts.forEach(c => ACCOUNTS.push({ id: `cust-${c.id}`, name: c.name || c.customerName, type: 'عميل', color: '#10b981', balances: { checks: 0, debt: parseFloat(c.currentBalance) || 0, loan: 0 } }));
    localSups.forEach(s => ACCOUNTS.push({ id: `supp-${s.id}`, name: s.name || s.supplierName, type: 'مورد', color: '#f59e0b', balances: { checks: 0, debt: 0, loan: parseFloat(s.currentBalance) || 0 } }));
    localBanks.forEach(b => ACCOUNTS.push({ id: `bank-${b.id}`, name: b.name || b.bankName, type: 'بنك', color: '#3b82f6', balances: { checks: 0, debt: parseFloat(b.currentBalance) || 0, loan: 0 } }));
    localSafes.forEach(s => ACCOUNTS.push({ id: `safe-${s.id}`, name: s.name || s.safeName, type: 'خزنة', color: '#8b5cf6', balances: { checks: 0, debt: parseFloat(s.currentBalance) || 0, loan: 0 } }));
    localExp.forEach(e => ACCOUNTS.push({ id: `exp-${e.id}`, name: e.name || e.accountName, type: 'عام', color: '#64748b', balances: { checks: 0, debt: parseFloat(e.currentBalance) || 0, loan: 0 } }));
};

// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Accounts
// ═══════════════════════════════════════════════════════
function renderAccountDropdown(type = 'from', filter = '') {
    if (!ACCOUNTS || ACCOUNTS.length === 0) {
        window.buildAccountsArray();
    }
    const listEl = document.getElementById(type + '-account-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
    
    try {
        let localMatches = (ACCOUNTS || []);
        if (normalizedTerm) {
            const searchWords = normalizedTerm.split(/\s+/).filter(w => w.length > 0);
            
            localMatches = localMatches.filter(c => {
                const normName = window.normalizeArabic ? window.normalizeArabic(c.name) : c.name.toLowerCase();
                const normCode = String(c.code || '').toLowerCase();
                
                return searchWords.every(word => 
                    normName.includes(word) || normCode.includes(word)
                );
            });
        }

        const finalMatches = Array.from(new Map(localMatches.map(m => [String(m.id), m])).values()).slice(0, 50);

        if (finalMatches.length === 0) {
            listEl.innerHTML = `<div class="custom-dropdown-empty">لا توجد حسابات مطابقة.<br><small style="color:#888;">(إجمالي الحسابات: ${(ACCOUNTS||[]).length})</small></div>`;
            return;
        }

        finalMatches.forEach(c => {
            const item = document.createElement('div');
            item.className = 'custom-dropdown-item';
            item.style.padding = '12px 16px';
            item.style.cursor = 'pointer';
            item.style.borderBottom = '1px solid rgba(255, 255, 255, 0.05)';
            item.style.transition = 'background 0.2s';
            
            item.onmouseenter = () => item.style.background = 'rgba(255, 255, 255, 0.1)';
            item.onmouseleave = () => item.style.background = 'transparent';
            
            let typeBadge = '';
            if (c.type) {
                typeBadge = `<span style="font-size:0.8rem; background: ${c.color}20; color: ${c.color}; padding: 4px 12px; border-radius: 20px; font-weight: bold; min-width: 60px; text-align: center; box-shadow: inset 0 0 5px ${c.color}40;">${c.type}</span>`;
            }
            let codeBadge = '';
            if (c.code) {
                codeBadge = `<span style="font-size:0.8rem; color:#888;">[${c.code}]</span>`;
            }

            item.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: bold; color: #fff;">${c.name}</span>
                        ${codeBadge}
                    </div>
                    ${typeBadge}
                </div>
            `;
            
            item.onmousedown = () => selectAccount(type, c.id, c.name); 
            item.onpointerdown = () => selectAccount(type, c.id, c.name);
            listEl.appendChild(item);
        });
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">حدث خطأ داخلي</div>';
    }
}

function showAccountDropdown(type) {
    renderAccountDropdown(type, document.getElementById(type + '-account-search').value);
    const listEl = document.getElementById(type + '-account-dropdown-list');
    if (listEl) {
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

let hideAccountDropdownTimeout = {};

function hideAccountDropdownDelayed(type) {
    clearTimeout(hideAccountDropdownTimeout[type]);
    hideAccountDropdownTimeout[type] = setTimeout(() => {
        if (document.activeElement && document.activeElement.id === type + '-account-search') {
            return;
        }

        const listEl = document.getElementById(type + '-account-dropdown-list');
        if(listEl) listEl.classList.remove('show'); listEl.style.display = 'none';
        
        const idVal = document.getElementById(type + '-account').value;
        const searchVal = document.getElementById(type + '-account-search').value.trim();
        if (idVal) {
            const acc = (ACCOUNTS || []).find(c => String(c.id) === String(idVal));
            if (acc && acc.name !== searchVal) {
                document.getElementById(type + '-account-search').value = acc.name;
            }
        } else if (searchVal) {
            const exactMatch = (ACCOUNTS || []).find(c => c.name === searchVal);
            if (exactMatch) {
                selectAccount(type, exactMatch.id, exactMatch.name);
            }
        } else {
            document.getElementById(type + '-account-search').value = '';
        }
    }, 250);
}

function filterAccountDropdown(type) {
    document.getElementById(type + '-account').value = '';
    updateLedgerButtons();
    renderAccountDropdown(type, document.getElementById(type + '-account-search').value);
    document.getElementById(type + '-account-dropdown-list').classList.add('show');
}

function selectAccount(type, id, name) {
    document.getElementById(type + '-account').value = id;
    document.getElementById(type + '-account-search').value = name;
    document.getElementById(type + '-account-dropdown-list').classList.remove('show');
    updateLedgerButtons();
}

// Override selectAccount to handle focus jumping and balance updating
window.selectAccount = function(type, id, name) {
    document.getElementById(type + '-account').value = id;
    document.getElementById(type + '-account-search').value = name;
    document.getElementById(type + '-account-dropdown-list').classList.remove('show');
    updateLedgerButtons();
    
    if (type === 'to') {
        if(typeof updateBalances === 'function') updateBalances();
        const toStmt = document.getElementById('to-statement');
        if (toStmt) toStmt.focus();
    } else if (type === 'from') {
        const cc = document.getElementById('cost-center');
        if (cc && cc.closest('.choices') && window.ccChoice) {
            window.ccChoice.showDropdown();
            let inp = cc.closest('.choices').querySelector('.choices__input');
            if (inp) inp.focus();
        } else {
            const fromStmt = document.getElementById('from-statement');
            if (fromStmt) fromStmt.focus();
        }
    }
};

function updateLedgerButtons() {
    const fromVal = document.getElementById('from-account') ? document.getElementById('from-account').value : '';
    const toVal = document.getElementById('to-account') ? document.getElementById('to-account').value : '';
    
    const fromBtn = document.getElementById('from-account-ledger-btn');
    const toBtn = document.getElementById('to-account-ledger-btn');
    
    if (fromBtn) fromBtn.style.display = fromVal ? 'inline-flex' : 'none';
    if (toBtn) toBtn.style.display = toVal ? 'inline-flex' : 'none';
}

function swapAccounts() {
    const fromIdEl = document.getElementById('from-account');
    const fromSearchEl = document.getElementById('from-account-search');
    const toIdEl = document.getElementById('to-account');
    const toSearchEl = document.getElementById('to-account-search');

    if (!fromIdEl || !fromSearchEl || !toIdEl || !toSearchEl) return;

    // Get current values
    const fromId = fromIdEl.value;
    const fromSearch = fromSearchEl.value;
    const toId = toIdEl.value;
    const toSearch = toSearchEl.value;

    // Set swapped values
    fromIdEl.value = toId;
    fromSearchEl.value = toSearch;
    toIdEl.value = fromId;
    toSearchEl.value = fromSearch;

    // Add a visual rotation feedback to the arrow
    const container = document.querySelector('.epic-arrow-container');
    if (container) {
        container.style.transform = 'translate(-50%, -50%) scale(1.18) rotate(180deg)';
        container.style.boxShadow = '0 15px 45px rgba(251, 191, 36, 0.6), 0 0 35px rgba(251, 191, 36, 0.4)';
        setTimeout(() => {
            container.style.transform = '';
            container.style.boxShadow = '';
        }, 800);
    }

    // Update balances since "to" account changed
    if (typeof updateBalances === 'function') {
        updateBalances();
    }

    // Play a tiny toast notification
    if (typeof showToast === 'function') {
        showToast('🔄 تم عكس حساب الصرف والاستلام بنجاح!', 'success');
    }
    updateLedgerButtons();
}

function getVoucherContactDetails(accountId) {
    if (!accountId) return null;
    const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(x => !x.deleted);
    const customer = custs.find(c => String(c.id) === String(accountId) || String(c.id) === String(accountId).replace('cust-', ''));
    if (customer) {
        return {
            type: 'customer',
            name: customer.name,
            phone: customer.phone || '',
            whatsapp: customer.whatsapp || ''
        };
    }
    const sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]').filter(x => !x.deleted);
    const supplier = sups.find(s => String(s.id) === String(accountId) || String(s.id) === String(accountId).replace('supp-', ''));
    if (supplier) {
        return {
            type: 'supplier',
            name: supplier.name,
            phone: supplier.phone || '',
            whatsapp: supplier.whatsapp || ''
        };
    }
    return null;
}

async function sendVoucherWA(voucherId) {
    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : localStorage.getItem('erp_wa_user_id');
        const res = await fetch(`http://localhost:3001/wa/status?userId=${userId}`);
        const data = await res.json();
        
        if (data.status !== 'CONNECTED' && data.status !== 'AUTHENTICATED') {
            const wantsToConnect = typeof showWAConnectPrompt === 'function' ? await showWAConnectPrompt() : confirm('رقم الواتساب غير مربوط. هل تريد الربط الآن؟');
            if (wantsToConnect) {
                if (typeof openWAModal === 'function') {
                    openWAModal();
                } else {
                    alert('يرجى الذهاب إلى شاشة الإعدادات لربط الواتساب.');
                }
            }
            return;
        }
    } catch (err) {
        alert('خدمة الواتساب غير متاحة حالياً. تأكد من تشغيل الخادم.');
        return;
    }

    const voucher = singlePaymentsData.find(x => String(x.id) === String(voucherId));
    if (!voucher) { alert('لم يتم العثور على السند'); return; }

    let defaultPhone = '';
    let accountName = voucher.beneficiaryName || 'مورد غير محدد';
    let accountTypeLabel = 'المستفيد';

    const contact = getVoucherContactDetails(voucher.toAccount);
    if (contact) {
        defaultPhone = contact.whatsapp || contact.phone || '';
        accountName = contact.name || accountName;
        accountTypeLabel = contact.type === 'customer' ? 'العميل' : 'المورد';
    }

    const phone = await new Promise((resolve) => {
        const overlayId = 'wa-phone-prompt-overlay';
        if (document.getElementById(overlayId)) document.getElementById(overlayId).remove();

        window.toggleEditPhone = function() {
            const displayWrap = document.getElementById('wa-phone-display-wrap');
            const inputWrap = document.getElementById('wa-phone-input-wrap');
            if (displayWrap && inputWrap) {
                if (displayWrap.style.display !== 'none') {
                    displayWrap.style.display = 'none';
                    inputWrap.style.display = 'block';
                    const inp = document.getElementById('wa-prompt-input');
                    if (inp) {
                        inp.focus();
                        inp.select();
                    }
                } else {
                    displayWrap.style.display = 'flex';
                    inputWrap.style.display = 'none';
                }
            }
        };

        const html = `
        <div id="${overlayId}" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:999999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);">
            <div style="background:linear-gradient(145deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95)); border:1px solid rgba(37,211,102,0.4); border-radius:24px; padding:35px; width:90%; max-width:450px; text-align:center; transform:translateY(40px) scale(0.9); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow:0 25px 50px -12px rgba(37,211,102,0.2), inset 0 1px 0 rgba(255,255,255,0.1);">
                
                <div style="width:80px; height:80px; background:linear-gradient(135deg, #25D366, #128C7E); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#fff; box-shadow:0 10px 20px rgba(37,211,102,0.4); border:3px solid rgba(255,255,255,0.2);">
                    <svg viewBox="0 0 24 24" width="45" height="45" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                
                <h3 style="color:#f8fafc; font-size:1.6rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0; font-weight:800;">إرسال السند عبر واتساب</h3>
                <p style="color:#94a3b8; font-size:0.95rem; margin-bottom:20px; line-height:1.6; font-family:'Tajawal',sans-serif;">يرجى تأكيد إرسال السند:</p>
                
                <!-- Display card -->
                <div id="wa-phone-display-wrap" style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 15px 20px; margin-bottom: 25px; text-align: right;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">👤 ${accountTypeLabel}:</span>
                        <span style="font-size: 1.15rem; color: #25d366; font-weight: 800;">${accountName}</span>
                        <span style="font-size: 0.95rem; color: #38bdf8; font-weight: 700; direction: ltr; margin-top: 4px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>📞</span> <span id="wa-display-phone-text">${defaultPhone || 'لا يوجد رقم مسجل'}</span>
                        </span>
                    </div>
                    <button type="button" onclick="toggleEditPhone()" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); color: #38bdf8; border-radius: 12px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(56, 189, 248, 0.2)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(56, 189, 248, 0.1)'; this.style.transform='scale(1)';" title="إرسال لرقم آخر">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Input container (Hidden by default) -->
                <div id="wa-phone-input-wrap" style="display: none; margin-bottom: 25px; animation: wa-scale-up 0.3s ease-out; text-align: right;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">✏️ إرسال إلى رقم هاتف آخر:</span>
                        <button type="button" onclick="toggleEditPhone()" style="background: transparent; border: none; color: #cbd5e1; font-size: 0.75rem; cursor: pointer; text-decoration: underline; outline: none;">رجوع</button>
                    </div>
                    <div style="position: relative;">
                        <input type="tel" id="wa-prompt-input" placeholder="رقم الهاتف الجديد..." value="${defaultPhone}" oninput="document.getElementById('wa-display-phone-text').textContent = this.value;" style="width:100%; background:rgba(0,0,0,0.4); border:2px solid rgba(255,255,255,0.1); color:#fff; padding:12px 15px; border-radius:12px; font-size:1.15rem; text-align:center; transition:all 0.3s; outline:none; font-family:'Inter',sans-serif;" onfocus="this.style.borderColor='#25d366'; this.style.boxShadow='0 0 12px rgba(37,211,102,0.2)';" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-send" style="background:linear-gradient(135deg, #25d366, #1da851); color:#fff; border:none; padding:12px 30px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.3s; box-shadow:0 4px 15px rgba(37,211,102,0.4); display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(37,211,102,0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(37,211,102,0.4)';">
                        <span>إرسال</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                    <button id="wa-prompt-cancel" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">إلغاء</button>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);

        const overlay = document.getElementById(overlayId);
        const modal = overlay.children[0];
        const input = document.getElementById('wa-prompt-input');

        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0) scale(1)';
            setTimeout(() => input.focus(), 400);
        });

        const closePrompt = (resultVal) => {
            overlay.style.opacity = '0';
            modal.style.transform = 'translateY(40px) scale(0.9)';
            setTimeout(() => {
                overlay.remove();
                resolve(resultVal);
            }, 400);
        };

        document.getElementById('wa-prompt-send').onclick = () => closePrompt(input.value.trim());
        document.getElementById('wa-prompt-cancel').onclick = () => closePrompt(null);
        input.addEventListener('keyup', (e) => {
            if(e.key === 'Enter') closePrompt(input.value.trim());
        });
    });

    if (!phone) return;

    let cfg = {};
    try { cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}'); } catch(e) {}
    
    const companyName = cfg.companyName || 'اسم الشركة غير مسجل';
    const companyPhone = cfg.phone || '';
    const companyAddress = cfg.address || '';
    
    let logoSrc = cfg.logoBase64 || '';
    if (!logoSrc) {
        const headerLogo = document.querySelector('.company-logo, #hdr-c-logo');
        if (headerLogo && headerLogo.src && !headerLogo.src.includes('placeholder')) {
            logoSrc = headerLogo.src;
        }
    }
    const logoImg = logoSrc ? `<img src="${logoSrc}" style="max-height:80px; max-width:200px; object-fit:contain;">` : '';

    const companyHeaderHtml = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px;">
            <div style="text-align: right;">
                <h1 style="margin: 0; font-size: 1.5rem;">${companyName}</h1>
                <div style="font-size: 0.9rem; color: #555; margin-top: 5px;">
                    ${companyAddress ? `<div>العنوان: ${companyAddress}</div>` : ''}
                    ${companyPhone ? `<div>الهاتف: ${companyPhone}</div>` : ''}
                </div>
            </div>
            <div>
                ${logoImg}
            </div>
        </div>
    `;

    const printContainer = document.createElement('div');
    printContainer.style.position = 'fixed';
    printContainer.style.top = '0';
    printContainer.style.left = '0';
    printContainer.style.width = '700px';
    printContainer.style.zIndex = '-99999';
    printContainer.style.opacity = '0.01';
    printContainer.style.pointerEvents = 'none';
    printContainer.style.background = '#fff';
    printContainer.style.color = '#000';
    printContainer.style.padding = '30px';

    const fromAccName = getAccountName(voucher.fromAccount || 'safe-001');
    const toAccName = getAccountName(voucher.toAccount || '');

    const htmlContent = `
        <html dir="rtl" lang="ar">
        <head>
            <style>
                body { font-family: 'Tajawal', sans-serif; background: #fff; color: #000; direction: rtl; }
                .voucher-card { border: 2px solid #333; border-radius: 12px; padding: 25px; margin-top: 10px; }
                .voucher-title { text-align: center; margin: 15px 0; color: #ef4444; font-size: 1.8rem; font-weight: 800; border-bottom: 2px dashed #ddd; padding-bottom: 10px; }
                .meta-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                .meta-table td { padding: 12px; border: 1px solid #eee; text-align: right; font-size: 1rem; }
                .meta-table td.label { font-weight: bold; background: #f9fafb; width: 30%; }
                .amount-highlight { font-size: 1.4rem; font-weight: bold; color: #ef4444; }
                .signatures-section { display: flex; justify-content: space-between; margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px; }
                .sig-box { width: 30%; text-align: center; font-size: 0.9rem; }
                .sig-line { border-bottom: 1px solid #777; margin-bottom: 8px; height: 30px; }
            </style>
        </head>
        <body>
            ${companyHeaderHtml}
            <div class="voucher-card">
                <div class="voucher-title">سند صرف نقدى</div>
                <table class="meta-table">
                    <tr>
                        <td class="label">رقم السند:</td>
                        <td><strong>#${voucher.id}</strong></td>
                        <td class="label">التاريخ:</td>
                        <td><strong>${voucher.date}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">صرف إلى:</td>
                        <td colspan="3">${toAccName}</td>
                    </tr>
                    <tr>
                        <td class="label">المبلغ:</td>
                        <td colspan="3" class="amount-highlight">${fmtNum(voucher.amountLocal)} SDG</td>
                    </tr>
                    ${voucher.currency && voucher.currency !== 'SDG' ? `
                    <tr>
                        <td class="label">المبلغ بالعملة الأجنبية:</td>
                        <td colspan="3">${fmtNum(voucher.amountForeign)} ${voucher.currency} (السعر: ${voucher.rate})</td>
                    </tr>
                    ` : ''}
                    <tr>
                        <td class="label">وذلك لـ:</td>
                        <td colspan="3">${voucher.toStatement || '—'}</td>
                    </tr>
                    <tr>
                        <td class="label">طريقة الدفع:</td>
                        <td>${voucher.paymentMethod === 'mobile' ? 'تحويل موبايل' : (voucher.paymentMethod === 'check' ? 'شيك' : 'نقداً')}</td>
                        <td class="label">المستفيد:</td>
                        <td>${voucher.beneficiaryName || '—'}</td>
                    </tr>
                </table>
                <div class="signatures-section">
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        <span>توقيع المستلم</span>
                    </div>
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        <span>توقيع المعتمد</span>
                    </div>
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        <span>توقيع المحاسب</span>
                    </div>
                </div>
            </div>
        </body>
        </html>
    `;
    printContainer.innerHTML = htmlContent;
    document.body.appendChild(printContainer);

    const message = 'مرحباً، مرفق سند الصرف الخاص بكم رقم ' + voucherId + ' بقيمة ' + fmtNum(voucher.amountLocal) + ' SDG.';
    
    if (typeof sendPDFViaWhatsApp === 'function') {
        await sendPDFViaWhatsApp(printContainer, phone, message);
    } else {
        showToast('برنامج ربط الواتساب غير متوفر في هذه الصفحة', 'error');
    }
    
    printContainer.remove();
}

async function sendCurrentVoucherWA() {
    if (currentViewedIndex !== -1) {
        const v = singlePaymentsData[currentViewedIndex];
        if (v) await sendVoucherWA(v.id);
    } else {
        showToast('⚠️ يرجى حفظ وتأكيد السند أولاً', 'warning');
    }
}

function updateActionButtonsVisibility() {
    const previewBtn = document.getElementById('btn-preview-invoice');
    const waBtn = document.getElementById('btn-whatsapp-invoice');
    if (currentViewedIndex !== -1) {
        if (previewBtn) previewBtn.style.display = 'inline-flex';
        if (waBtn) waBtn.style.display = 'inline-flex';
    } else {
        if (previewBtn) previewBtn.style.display = 'none';
        if (waBtn) waBtn.style.display = 'none';
    }
}

function getAccountName(id) {
    if(!id) return '';
    if (!ACCOUNTS || ACCOUNTS.length === 0) {
        if (typeof window.buildAccountsArray === 'function') window.buildAccountsArray();
    }
    let idStr = String(id).trim();
    let found = (ACCOUNTS || []).find(a => String(a.id).toLowerCase() === idStr.toLowerCase());
    if (found) return found.name;
    
    let cleanId = idStr.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
    found = (ACCOUNTS || []).find(a => {
        const aId = String(a.id).toLowerCase();
        const aName = String(a.name).toLowerCase();
        const cleanAId = aId.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
        return aId === cleanId.toLowerCase() || cleanAId === cleanId.toLowerCase() || aName === cleanId.toLowerCase();
    });
    if (found) return found.name;
    
    if (cleanId.length > 1) {
        found = (ACCOUNTS || []).find(a => {
            const aName = String(a.name).toLowerCase();
            const aId = String(a.id).toLowerCase();
            return aName.includes(cleanId.toLowerCase()) || cleanId.toLowerCase().includes(aName) || aId.includes(cleanId.toLowerCase()) || cleanId.toLowerCase().includes(aId);
        });
        if (found) return found.name;
    }
    return cleanId || idStr;
}

// ═══════════════════════════════════════════════════════
// Multiple Bank Transfers & Cheques Dynamic Manipulation
// ═══════════════════════════════════════════════════════

window.addTransferRow = function(no = '', note = '', amt = 0, statement = '') {
    const container = document.getElementById('transfers-container');
    if (!container) return;

    const isFirst = (container.children.length === 0);
    const row = document.createElement('div');
    row.className = 'transfer-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; width: 100%;';
    
    const labelHTML = isFirst 
        ? `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem;">🏦</span>
               <span style="font-weight: 800; color: #34d399; font-size: 0.9rem;">تفاصيل التحويلة</span>
           </div>`
        : `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem; visibility: hidden;">🏦</span>
               <span style="font-weight: 800; color: #34d399; font-size: 0.9rem; opacity: 0.6;">تحويلة إضافية</span>
           </div>`;

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addTransferRow()" class="pay-toggle-btn" style="background: rgba(16,185,129,0.2); border: 1px solid #34d399; color:#34d399; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeTransferRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div style="width: 200px; flex: none; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">المبلغ:</label>
            <input type="text" class="v-input ctrl-input transfer-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="0.00" oninput="restrictToNumbers(this); if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();" onblur="if(this.value) this.value = parseFloat(this.value.replace(/,/g, '') || 0).toFixed(2); if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:1.35rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1; min-width: 130px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">اسم البنك:</label>
            <input type="text" class="v-input ctrl-input transfer-note" value="${note}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div style="flex: 1; min-width: 140px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">رقم التحويلة:</label>
            <input type="text" class="v-input ctrl-input transfer-no" value="${no}" placeholder="رقم التحويلة..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div style="flex: 1.5; min-width: 160px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">البيان:</label>
            <input type="text" class="v-input ctrl-input transfer-statement" value="${statement}" placeholder="البيان..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div style="display: flex; justify-content: flex-end; width: 40px; flex: none;">
            ${buttonHTML}
        </div>
    `;
    container.appendChild(row);
    
    // Play sound if manual addition
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
    btn.closest('.transfer-row').remove();
    syncFirstRowAmount();
};

window.addChequeRow = function(branch = '', no = '', date = '', amt = 0, statement = '') {
    const container = document.getElementById('cheques-container');
    if (!container) return;

    const isFirst = (container.children.length === 0);
    const row = document.createElement('div');
    row.className = 'cheque-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; width: 100%;';
    
    const labelHTML = isFirst 
        ? `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem;">✍️</span>
               <span style="font-weight: 800; color: #d8b4fe; font-size: 0.9rem;">تفاصيل الشيك</span>
           </div>`
        : `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem; visibility: hidden;">✍️</span>
               <span style="font-weight: 800; color: #d8b4fe; font-size: 0.9rem; opacity: 0.6;">شيك إضافي</span>
           </div>`;

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addChequeRow()" class="pay-toggle-btn" style="background: rgba(147,51,234,0.22); border: 1px solid #d8b4fe; color:#d8b4fe; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeChequeRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div style="width: 200px; flex: none; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">المبلغ:</label>
            <input type="text" class="v-input ctrl-input cheque-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="0.00" oninput="restrictToNumbers(this); if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();" onblur="if(this.value) this.value = parseFloat(this.value.replace(/,/g, '') || 0).toFixed(2); if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:1.35rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1; min-width: 130px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">اسم البنك:</label>
            <input type="text" class="v-input ctrl-input cheque-branch" value="${branch}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div style="flex: 1; min-width: 120px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">رقم الشيك:</label>
            <input type="text" class="v-input ctrl-input cheque-no" value="${no}" placeholder="رقم الشيك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div style="flex: 0.8; min-width: 170px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">تاريخ الاستحقاق:</label>
            <input type="date" class="v-input ctrl-input cheque-date" value="${date}" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 4px; width: 100%; border-radius: 6px; color:#fff;">
        </div>
        <div style="flex: 1.5; min-width: 160px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">البيان:</label>
            <input type="text" class="v-input ctrl-input cheque-statement" value="${statement}" placeholder="البيان..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div style="display: flex; justify-content: flex-end; width: 40px; flex: none;">
            ${buttonHTML}
        </div>
    `;
    container.appendChild(row);
    
    // Play sound if manual addition
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
    btn.closest('.cheque-row').remove();
    syncFirstRowAmount();
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
    const totalAmount = parseNum(document.getElementById('amount-local').value);
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

    if (method === 'mobile' || method === 'hybrid') {
        if (v.bankTransfers && Array.isArray(v.bankTransfers) && v.bankTransfers.length > 0) {
            v.bankTransfers.forEach(tr => {
                addTransferRow(tr.transferNo, tr.note, tr.amount, tr.statement || '');
            });
        } else {
            addTransferRow(v.mobileTransferNo || '', v.mobileNote || '', v.amountLocal || 0, v.statement || '');
        }
    }
    if (method === 'check' || method === 'hybrid') {
        if (v.cheques && Array.isArray(v.cheques) && v.cheques.length > 0) {
            v.cheques.forEach(ch => {
                addChequeRow(ch.checkBranch, ch.checkNo, ch.checkDate, ch.amount, ch.statement || '');
            });
        } else {
            addChequeRow(v.checkBranch || '', v.checkNo || '', v.checkDate || '', v.amountLocal || 0, v.statement || '');
        }
    }
    if (method === 'cash') {
        initPaymentContainers();
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

// Hook setup into document load
document.addEventListener('DOMContentLoaded', () => {
    initPaymentContainers();
});

window.unifyVoucherSequences = function() {
    if (!confirm('هل أنت متأكد من رغبتك في توحيد أرقام جميع السندات القديمة لتتبع التسلسل الجديد (E+سنة+شهر+000X)؟\nسيتم ترتيب جميع السندات حسب تاريخ إنشائها وإعطاؤها تسلسلاً موحداً، ولا يمكن التراجع عن هذه العملية.')) return;
    
    let spData = [];
    try {
        spData = (window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : ((window.SERVER_SINGLE_PAYMENTS && window.SERVER_SINGLE_PAYMENTS.length > 0 ? window.SERVER_SINGLE_PAYMENTS : (JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []))));
    } catch(e) {}

    if (spData.length === 0) {
        showToast('لا توجد سندات قديمة لإعادة ترقيمها', 'warning');
        return;
    }

    // Sort by date oldest first
    spData.sort((a, b) => new Date(a.date) - new Date(b.date));

    let seqMap = {};

    spData.forEach(v => {
        const d = new Date(v.date || Date.now());
        const yy = String(d.getFullYear()).substring(2, 4);
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const prefix = 'E' + yy + mm;
        
        if (!seqMap[prefix]) seqMap[prefix] = 1;
        
        const newId = `${prefix}${String(seqMap[prefix]).padStart(4, '0')}`;
        v.id = newId;
        if(v.voucherNo !== undefined) v.voucherNo = newId;
        
        seqMap[prefix]++;
    });

    localStorage.setItem('erp_single_payments_v1', JSON.stringify(spData));
    showToast('تم ترتيب وتوحيد تسلسل جميع السندات بنجاح!', 'success');
    
    setTimeout(() => {
        window.location.href = window.location.pathname;
    }, 1500);
};




