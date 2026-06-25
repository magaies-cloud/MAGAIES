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
    let localCusts = [], localSups = [], localBanks = [], localSafes = [], localExp = [], localJobs = [];
    try { localCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || []; } catch(e) {}
    try { localSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []; } catch(e) {}
    try { localBanks = JSON.parse(localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
    try { localSafes = JSON.parse(localStorage.getItem('erp_safes_v1')) || []; } catch(e) {}
    try { localExp = JSON.parse(localStorage.getItem('erp_accounts_v1')) || []; } catch(e) {}
    try { localJobs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || []; } catch(e) {}
    
    ACCOUNTS = [];
    localCusts.forEach(c => ACCOUNTS.push({ id: `cust-${c.id}`, name: c.name, balances: { checks: 0, debt: parseFloat(c.currentBalance) || 0, loan: 0 } }));
    localSups.forEach(s => ACCOUNTS.push({ id: `supp-${s.id}`, name: s.name, balances: { checks: 0, debt: 0, loan: parseFloat(s.currentBalance) || 0 } }));
    localBanks.forEach(b => ACCOUNTS.push({ id: `bank-${b.id}`, name: b.name, balances: { checks: 0, debt: parseFloat(b.currentBalance) || 0, loan: 0 } }));
    localSafes.forEach(s => ACCOUNTS.push({ id: `safe-${s.id}`, name: s.name, balances: { checks: 0, debt: parseFloat(s.currentBalance) || 0, loan: 0 } }));
    localExp.forEach(e => ACCOUNTS.push({ id: `exp-${e.id}`, name: e.name, balances: { checks: 0, debt: parseFloat(e.currentBalance) || 0, loan: 0 } }));

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

    // Initialize Smart Search (Choices.js)
    if (window.Choices) {
        window.toAccChoice = new Choices(toAccEl, { searchEnabled: true, searchPlaceholderValue: '🔍 ابحث هنا...', itemSelectText: '', renderChoiceLimit: 15 });
        window.fromAccChoice = new Choices(fromAccEl, { searchEnabled: true, searchPlaceholderValue: '🔍 ابحث هنا...', itemSelectText: '', renderChoiceLimit: 15 });
        if (costCenterEl) window.ccChoice = new Choices(costCenterEl, { searchEnabled: true, searchPlaceholderValue: '🔍 ابحث هنا...', itemSelectText: '', renderChoiceLimit: 15 });

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
    }

    toAccEl.addEventListener('change', updateBalances);
    
    // Initialize UI
    togglePaymentMethod();

    // Deep Drill-down
    const urlParams = new URLSearchParams(window.location.search);
    const voucherId = urlParams.get('id');
    if (voucherId) {
        singlePaymentsData = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
        const idx = singlePaymentsData.findIndex(v => v.id === voucherId);
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
    const seq = parseInt(localStorage.getItem('erp_single_payment_seq') || '0') + 1;
    let prefix = '';
    try {
        const userStr = localStorage.getItem('user');
        if(userStr) {
            const userObj = JSON.parse(userStr);
            if(userObj && userObj.username) {
                prefix = userObj.username.substring(0, 3).toUpperCase() + '-';
            }
        }
    } catch(e) {}
    document.getElementById('voucher-number').innerText = prefix + String(seq).padStart(6, '0');
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

    if (source === 'local' || source === 'rate') {
        eq = rate === 0 ? 0 : amt / rate;
        eqEl.value = eq.toFixed(2);
    } else if (source === 'foreign') {
        amt = eq * rate;
        amtEl.value = amt.toFixed(2);
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
    document.getElementById('amount-local').value = '0';
    document.getElementById('currency').value = 'SDG';
    document.getElementById('exchange-rate').value = '1.0';
    document.getElementById('exchange-rate').readOnly = true;
    document.getElementById('amount-foreign').value = '0';
    document.getElementById('to-statement').value = '';
    document.getElementById('beneficiary-name').value = '';
    
    document.getElementById('from-account').value = 'safe-001';
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
    document.getElementById('amount-local').value = fmtNum(v.amountLocal);
    document.getElementById('currency').value = v.currency || 'SDG';
    document.getElementById('exchange-rate').value = v.rate || 1.0;
    document.getElementById('amount-foreign').value = fmtNum(v.amountForeign);
    document.getElementById('to-statement').value = v.toStatement || '';
    document.getElementById('beneficiary-name').value = v.beneficiaryName || '';
    
    document.getElementById('from-account').value = v.fromAccount || 'safe-001';
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
}

function submitVoucher() {
    try { // WRAPPED
    const amtLocal = parseNum(document.getElementById('amount-local').value);
    const toAcc = document.getElementById('to-account').value;

    if (amtLocal <= 0) {
        showToast("لا يمكن اعتماد سند بقيمة صفر", "error");
        return;
    }
    if (!toAcc) {
        showToast("يرجى اختيار الحساب المستفيد", "error");
        return;
    }

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    const method = document.querySelector('input[name="payment_method"]:checked').value;

    const voucherData = {
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
        
        paymentMethod: method,
        
        timestamp: new Date().toISOString()
    };

    if (method === 'mobile') {
        const transferNo = document.getElementById('mobile-transfer-no').value;
        if (transferNo) {
            let allPayments = [];
            try {
                allPayments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
                if (!Array.isArray(allPayments)) allPayments = [];
            } catch(e) {
                allPayments = [];
            }
            const isDuplicate = allPayments.some(v => v.mobileTransferNo === transferNo && v.id !== document.getElementById('voucher-number').innerText);
            if (isDuplicate) {
                showDuplicateModal(transferNo);
                return; // Prevent saving
            }
        }
        voucherData.mobileTransferNo = transferNo;
        voucherData.mobileNote = document.getElementById('mobile-note').value;
    } else if (method === 'check') {
        voucherData.checkNo = document.getElementById('check-no').value;
        voucherData.checkDate = document.getElementById('check-date').value;
        voucherData.checkDueDate = document.getElementById('check-due-date').value;
        voucherData.checkRef = document.getElementById('check-ref').value;
        voucherData.checkRefNo = document.getElementById('check-ref-no').value;
        voucherData.checkBranch = document.getElementById('check-branch').value;
        voucherData.checkSettleLocal = document.getElementById('check-settle-local').checked;
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
                custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
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
                sups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
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
        alert("حدث خطأ أثناء الحفظ: " + err.message + "\nفي السطر: " + err.stack);
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
                custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
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
                sups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
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
});
