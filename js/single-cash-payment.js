/* ═══════════════════════════════════════════════════════
   single-cash-payment.js — Single Cash/Check Disbursement Module
   ═══════════════════════════════════════════════════════ */

let ACCOUNTS = [];

// Load real accounts from master-data
// (Moved to DBReady event)

let singlePaymentsData = [];
            try {
                singlePaymentsData = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
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

    let localCusts = [], localSups = [], localBanks = [], localSafes = [], localExp = [], localJobs = [];
    try { localCusts = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    try { localSups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    try { localBanks = JSON.parse(localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
    try { localSafes = JSON.parse(localStorage.getItem('erp_safes_v1')) || []; } catch(e) {}
    try { localExp = JSON.parse(localStorage.getItem('erp_accounts_v1')) || []; } catch(e) {}
    try { localJobs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || []; } catch(e) {}
    
    ACCOUNTS = [];
    localCusts.forEach(c => ACCOUNTS.push({ id: `cust-${c.id}`, name: c.name, type: 'عميل', color: '#10b981', balances: { checks: 0, debt: parseFloat(c.currentBalance) || 0, loan: 0 } }));
    localSups.forEach(s => ACCOUNTS.push({ id: `supp-${s.id}`, name: s.name, type: 'مورد', color: '#f59e0b', balances: { checks: 0, debt: 0, loan: parseFloat(s.currentBalance) || 0 } }));
    localBanks.forEach(b => ACCOUNTS.push({ id: `bank-${b.id}`, name: b.name, type: 'بنك', color: '#3b82f6', balances: { checks: 0, debt: parseFloat(b.currentBalance) || 0, loan: 0 } }));
    localSafes.forEach(s => ACCOUNTS.push({ id: `safe-${s.id}`, name: s.name, type: 'خزنة', color: '#8b5cf6', balances: { checks: 0, debt: parseFloat(s.currentBalance) || 0, loan: 0 } }));
    localExp.forEach(e => ACCOUNTS.push({ id: `exp-${e.id}`, name: e.name, type: 'عام', color: '#64748b', balances: { checks: 0, debt: parseFloat(e.currentBalance) || 0, loan: 0 } }));

    document.getElementById('hdr-date').valueAsDate = new Date();
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
        singlePaymentsData = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
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
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    document.getElementById('section-mobile').classList.remove('active');
    document.getElementById('section-check').classList.remove('active');

    if (method === 'mobile') {
        document.getElementById('section-mobile').classList.add('active');
    } else if (method === 'check') {
        document.getElementById('section-check').classList.add('active');
    }
}

function setNextVoucherNumber() {
    let spData = [];
    try {
        spData = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    } catch(e) {}

    const now = new Date();
    const yy = String(now.getFullYear()).substring(2, 4);
    const mm = String(now.getMonth() + 1).padStart(2, '0');
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
    document.getElementById('voucher-number').innerText = nextNo;
}

function updateCurrency() {
    const curr = document.getElementById('currency').value;
    const rateEl = document.getElementById('exchange-rate');
    if (curr === 'SDG') {
        rateEl.value = '1.0';
        rateEl.readOnly = true;
    } else {
        rateEl.readOnly = false;
        if(parseNum(rateEl.value) === 1) rateEl.value = ''; 
    }
    calcEquiv('rate');
}

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
        eqEl.value = addCommas(eq.toFixed(2));
    } else if (source === 'foreign') {
        amt = eq * rate;
        amtEl.value = addCommas(amt.toFixed(2));
    }
}

function resetVoucher() {
    if(confirm("هل أنت متأكد من مسح البيانات والبدء بسند جديد؟")) {
        resetVoucherSilent();
        showToast("تم فتح سند جديد", "success");
    }
}

function resetVoucherSilent() {
    currentViewedIndex = -1;
    document.getElementById('hdr-date').valueAsDate = new Date();
    
    document.getElementById('to-account').value = '';
    document.getElementById('to-account-search').value = '';
    
    document.getElementById('amount-local').value = '';
    document.getElementById('currency').value = 'SDG';
    document.getElementById('exchange-rate').value = '1.0';
    document.getElementById('exchange-rate').readOnly = true;
    document.getElementById('amount-foreign').value = '';
    document.getElementById('to-statement').value = '';
    document.getElementById('beneficiary-name').value = '';
    
    document.getElementById('from-account').value = '';
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

    // Reset Mobile
    document.getElementById('mobile-transfer-no').value = '';
    document.getElementById('mobile-note').value = '';

    // Reset Check
    document.getElementById('check-no').value = '';
    document.getElementById('check-date').value = '';
    document.getElementById('check-due-date').value = '';
    document.getElementById('check-ref').value = '';
    document.getElementById('check-ref-no').value = '';
    document.getElementById('check-branch').value = '';
    document.getElementById('check-settle-local').checked = false;

    updateBalances();
    setNextVoucherNumber();
    updateLedgerButtons();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
}

function loadPreviousVoucher() {
    singlePaymentsData = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
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
    singlePaymentsData = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
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
    document.querySelector(`input[name="payment_method"][value="${method}"]`).checked = true;
    togglePaymentMethod();

    if (method === 'mobile') {
        document.getElementById('mobile-transfer-no').value = v.mobileTransferNo || '';
        document.getElementById('mobile-note').value = v.mobileNote || '';
    } else if (method === 'check') {
        document.getElementById('check-no').value = v.checkNo || '';
        document.getElementById('check-date').value = v.checkDate || '';
        document.getElementById('check-due-date').value = v.checkDueDate || '';
        document.getElementById('check-ref').value = v.checkRef || '';
        document.getElementById('check-ref-no').value = v.checkRefNo || '';
        document.getElementById('check-branch').value = v.checkBranch || '';
        document.getElementById('check-settle-local').checked = !!v.checkSettleLocal;
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
    }

    
    const _docDateStr = document.getElementById('hdr-date') ? document.getElementById('hdr-date').value : new Date().toISOString().split('T')[0];
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
        const transferNo = document.getElementById('mobile-transfer-no').value.trim();
        
        if (!transferNo) {
            showToast("يرجى إدخال رقم حساب التحويل", "error");
            document.getElementById('mobile-transfer-no').focus();
            return;
        }

        if (transferNo.length < 6) {
            showToast("يجب أن يكون رقم التحويل أكثر من 5 أرقام", "error");
            document.getElementById('mobile-transfer-no').focus();
            return;
        }

        if (!window.isBankReferenceUnique(transferNo, 'mobile', document.getElementById('voucher-number').innerText)) {
            showToast("رقم التحويل مكرر. يجب أن لا يتكرر الرقم نهائياً في النظام.", "error");
            showDuplicateModal(transferNo);
            return; // Prevent saving
        }

        voucherData.mobileTransferNo = transferNo;
        voucherData.mobileNote = document.getElementById('mobile-note').value;
    } else if (method === 'check') {
        voucherData.checkNo = document.getElementById('check-no').value.trim();
        
        if (!voucherData.checkNo || voucherData.checkNo.length < 6) {
            showToast("يجب أن يكون رقم الشيك أكثر من 5 أرقام", "error");
            document.getElementById('check-no').focus();
            return;
        }

        if (!window.isBankReferenceUnique(voucherData.checkNo, 'check', document.getElementById('voucher-number').innerText)) {
            showToast("رقم الشيك مكرر. يجب أن لا يتكرر الرقم نهائياً في النظام.", "error");
            return; // Prevent saving
        }

        voucherData.checkDate = document.getElementById('check-date').value;
        voucherData.checkDueDate = document.getElementById('check-due-date').value;
        voucherData.checkRef = document.getElementById('check-ref').value;
        voucherData.checkRefNo = document.getElementById('check-ref-no').value;
        voucherData.checkBranch = document.getElementById('check-branch').value;
        voucherData.checkSettleLocal = document.getElementById('check-settle-local').checked;
        
        if (!voucherData.checkBranch) {
            showToast("يرجى إدخال اسم البنك", "error");
            return;
        }
    }

    singlePaymentsData = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    
    // Balance update logic
    function applyPaymentEffect(fromAccId, toAccId, amount, isRevert = false) {
        let multiplier = isRevert ? -1 : 1;
        let amtSafe = -amount * multiplier; // safe decreases
        let amtBeneficiary = -amount * multiplier; // beneficiary balance decreases

        // Update Beneficiary (toAccount)
        let custs = [];
            try {
                custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
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
                sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
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

        // Update Safe (fromAccount)
        let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
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

    if (currentViewedIndex !== -1) {
        if (!window.canEditRecord(voucherData.date, 'finance')) return;
        
        // Revert old payment effect
        const oldVoucher = singlePaymentsData[currentViewedIndex];
        applyPaymentEffect(oldVoucher.fromAccount, oldVoucher.toAccount, oldVoucher.amountLocal, true);
        
        // Apply new payment effect
        applyPaymentEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
        
        singlePaymentsData[currentViewedIndex] = voucherData;
        localStorage.setItem('erp_single_payments_v1', JSON.stringify(singlePaymentsData));
        showToast(`تم تعديل السند رقم ${voucherData.id} بنجاح!`, "success");
    } else {
        applyPaymentEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
        singlePaymentsData.push(voucherData);
        localStorage.setItem('erp_single_payments_v1', JSON.stringify(singlePaymentsData));
        const currentSeq = parseInt(localStorage.getItem('erp_single_payment_seq') || '0');
        localStorage.setItem('erp_single_payment_seq', currentSeq + 1);
        showToast(`تم اعتماد السند رقم ${voucherData.id} بنجاح!`, "success");
    }

    setTimeout(() => { resetVoucherSilent(); }, 1500);
    } catch (err) {
        console.error(err);
        alert("حدث خطأ أثناء الحفظ: " + err.message + "في السطر: " + err.stack);
    }
}

function printVoucher() {
    window.print();
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
                custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
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
                sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
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
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
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

// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Accounts
// ═══════════════════════════════════════════════════════
function renderAccountDropdown(type = 'from', filter = '') {
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
            
            let displayText = c.name;
            if (c.type) displayText += ` <span style="font-size:0.75rem; background: ${c.color}20; color: ${c.color}; padding: 2px 8px; border-radius: 12px; margin-right: 10px; font-weight: bold; vertical-align: middle;">${c.type}</span>`;
            if (c.code) displayText += ` <span style="font-size:0.8rem; color:#888;">[${c.code}]</span>`;
            
            item.innerHTML = displayText;
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

