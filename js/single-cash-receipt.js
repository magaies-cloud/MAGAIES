/* ═══════════════════════════════════════════════════════
   single-cash-receipt.js — Single Cash/Check Receipt Module
   ═══════════════════════════════════════════════════════ */

let ACCOUNTS = [];

// Load real accounts from master-data
// (Moved to DBReady event)

let singleReceiptsData = [];
            try {
                singleReceiptsData = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                if (!Array.isArray(singleReceiptsData)) singleReceiptsData = [];
            } catch(e) {
                singleReceiptsData = [];
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
    let localCusts = [];
    try { localCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || []; } catch(e) { localCusts = []; }
    let localSups = [];
    try { localSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []; } catch(e) { localSups = []; }
    
    ACCOUNTS = [];
    localCusts.forEach(c => ACCOUNTS.push({ id: c.id, name: c.name, balances: { checks: 0, debt: parseFloat(c.currentBalance) || 0, credit: 0 } }));
    localSups.forEach(s => ACCOUNTS.push({ id: s.id, name: s.name, balances: { checks: 0, debt: 0, credit: parseFloat(s.currentBalance) || 0 } }));

    document.getElementById('hdr-date').valueAsDate = new Date();
    setNextVoucherNumber();

    // Populate from-account (Payer) options
    const fromAccEl = document.getElementById('from-account');
    let accOptions = `<option value="">-- اختر الدافع / العميل --</option>`;
    ACCOUNTS.forEach(a => accOptions += `<option value="${a.id}">${a.name}</option>`);
    fromAccEl.innerHTML = accOptions;

    fromAccEl.addEventListener('change', updateBalances);
    
    // Initialize UI
    togglePaymentMethod();

    // Deep Drill-down
    const urlParams = new URLSearchParams(window.location.search);
    const voucherId = urlParams.get('id');
    if (voucherId) {
        singleReceiptsData = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
        const idx = singleReceiptsData.findIndex(v => v.id === voucherId);
        if (idx !== -1) {
            currentViewedIndex = idx;
            renderVoucher(singleReceiptsData[idx]);
        }
    }
});

function updateBalances() {
    const accId = document.getElementById('from-account').value;
    const acc = ACCOUNTS.find(a => a.id === accId);
    if(acc && acc.balances) {
        document.getElementById('bal-checks').innerText = acc.balances.checks;
        document.getElementById('bal-debt').innerText = acc.balances.debt;
        document.getElementById('bal-credit').innerText = acc.balances.credit;
    } else {
        document.getElementById('bal-checks').innerText = '0';
        document.getElementById('bal-debt').innerText = '0';
        document.getElementById('bal-credit').innerText = '0';
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
    const seq = parseInt(localStorage.getItem('erp_single_receipt_seq') || '0') + 1;
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
    document.getElementById('from-account').value = '';
    document.getElementById('amount-local').value = '0';
    document.getElementById('currency').value = 'SDG';
    document.getElementById('exchange-rate').value = '1.0';
    document.getElementById('exchange-rate').readOnly = true;
    document.getElementById('amount-foreign').value = '0';
    document.getElementById('from-statement').value = '';
    document.getElementById('collector-name').value = '';
    
    document.getElementById('to-account').value = 'safe-001';
    document.getElementById('to-statement').value = '';

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
    singleReceiptsData = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    if (singleReceiptsData.length === 0) return showToast("لا توجد سندات سابقة", "warning");
    
    if (currentViewedIndex === -1) {
        currentViewedIndex = singleReceiptsData.length - 1;
    } else if (currentViewedIndex > 0) {
        currentViewedIndex--;
    } else {
        return showToast("هذا هو أول سند تم حفظه", "warning");
    }
    renderVoucher(singleReceiptsData[currentViewedIndex]);
}

function loadNextVoucher() {
    singleReceiptsData = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    if (currentViewedIndex === -1) return; // already at new
    
    if (currentViewedIndex < singleReceiptsData.length - 1) {
        currentViewedIndex++;
        renderVoucher(singleReceiptsData[currentViewedIndex]);
    } else {
        resetVoucherSilent();
        showToast("سند جديد", "success");
    }
}

function renderVoucher(v) {
    document.getElementById('voucher-number').innerText = v.id;
    document.getElementById('hdr-date').value = v.date;
    
    document.getElementById('from-account').value = v.fromAccount || '';
    document.getElementById('amount-local').value = fmtNum(v.amountLocal);
    document.getElementById('currency').value = v.currency || 'SDG';
    document.getElementById('exchange-rate').value = v.rate || 1.0;
    document.getElementById('amount-foreign').value = fmtNum(v.amountForeign);
    document.getElementById('from-statement').value = v.fromStatement || '';
    document.getElementById('collector-name').value = v.collectorName || '';
    
    document.getElementById('to-account').value = v.toAccount || 'safe-001';
    document.getElementById('to-statement').value = v.toStatement || '';

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
    const fromAcc = document.getElementById('from-account').value;

    if (amtLocal <= 0) {
        showToast("لا يمكن اعتماد سند بقيمة صفر", "error");
        return;
    }
    if (!fromAcc) {
        showToast("يرجى اختيار الحساب الدافع (العميل)", "error");
        return;
    }

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    const method = document.querySelector('input[name="payment_method"]:checked').value;

    const voucherData = {
        id: document.getElementById('voucher-number').innerText,
        date: document.getElementById('hdr-date').value,
        type: 'single_receipt',
        
        fromAccount: fromAcc,
        amountLocal: amtLocal,
        currency: document.getElementById('currency').value,
        rate: parseNum(document.getElementById('exchange-rate').value),
        amountForeign: parseNum(document.getElementById('amount-foreign').value),
        fromStatement: document.getElementById('from-statement').value,
        collectorName: document.getElementById('collector-name').value,
        
        toAccount: document.getElementById('to-account').value,
        toStatement: document.getElementById('to-statement').value,
        
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

    singleReceiptsData = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    
    // Balance update logic
    function applyVoucherEffect(fromAccId, toAccId, amount, isRevert = false) {
        let multiplier = isRevert ? -1 : 1;
        let amtFrom = -amount * multiplier; // debt decreases
        let amtTo = amount * multiplier;    // safe increases

        let custs = [];
            try {
                custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(custs)) custs = [];
            } catch(e) {
                custs = [];
            }
        let cIdx = custs.findIndex(c => c.id === fromAccId);
        if (cIdx > -1) {
            custs[cIdx].currentBalance = (custs[cIdx].currentBalance || 0) + amtFrom;
            localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
        } else {
            let sups = [];
            try {
                sups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(sups)) sups = [];
            } catch(e) {
                sups = [];
            }
            let sIdx = sups.findIndex(s => s.id === fromAccId);
            if (sIdx > -1) {
                sups[sIdx].currentBalance = (sups[sIdx].currentBalance || 0) + amtFrom;
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
        let bIdx = banks.findIndex(b => b.id === toAccId);
        if (bIdx > -1) {
            banks[bIdx].currentBalance = (banks[bIdx].currentBalance || 0) + amtTo;
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        }
    }

    if (currentViewedIndex !== -1) {
        if (!window.canEditRecord(voucherData.date, 'finance')) return;
        
        // Revert old voucher effect
        const oldVoucher = singleReceiptsData[currentViewedIndex];
        applyVoucherEffect(oldVoucher.fromAccount, oldVoucher.toAccount, oldVoucher.amountLocal, true);
        
        // Apply new voucher effect
        applyVoucherEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
        
        singleReceiptsData[currentViewedIndex] = voucherData;
        localStorage.setItem('erp_single_receipts_v1', JSON.stringify(singleReceiptsData));
        showSuccessModal("تم تعديل السند بنجاح", `تم تحديث السند رقم ${voucherData.id} وحفظ التغييرات بنجاح.`);
    } else {
        applyVoucherEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
        singleReceiptsData.push(voucherData);
        localStorage.setItem('erp_single_receipts_v1', JSON.stringify(singleReceiptsData));
        const currentSeq = parseInt(localStorage.getItem('erp_single_receipt_seq') || '0');
        localStorage.setItem('erp_single_receipt_seq', currentSeq + 1);
        showSuccessModal("تم اعتماد السند بنجاح", `تم إصدار السند رقم ${voucherData.id} وتحديث الأرصدة بنجاح.`);
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
        const oldVoucher = singleReceiptsData[currentViewedIndex];
        
        // Revert old voucher effect
        function applyVoucherEffect(fromAccId, toAccId, amount, isRevert = false) {
            let multiplier = isRevert ? -1 : 1;
            let amtFrom = -amount * multiplier;
            let amtTo = amount * multiplier;

            let custs = [];
            try {
                custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(custs)) custs = [];
            } catch(e) {
                custs = [];
            }
            let cIdx = custs.findIndex(c => c.id === fromAccId);
            if (cIdx > -1) {
                custs[cIdx].currentBalance = (custs[cIdx].currentBalance || 0) + amtFrom;
                localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
            } else {
                let sups = [];
            try {
                sups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(sups)) sups = [];
            } catch(e) {
                sups = [];
            }
                let sIdx = sups.findIndex(s => s.id === fromAccId);
                if (sIdx > -1) {
                    sups[sIdx].currentBalance = (sups[sIdx].currentBalance || 0) + amtFrom;
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
            let bIdx = banks.findIndex(b => b.id === toAccId);
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (banks[bIdx].currentBalance || 0) + amtTo;
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            }
        }
        
        applyVoucherEffect(oldVoucher.fromAccount, oldVoucher.toAccount, oldVoucher.amountLocal, true);

        singleReceiptsData.splice(currentViewedIndex, 1);
        localStorage.setItem('erp_single_receipts_v1', JSON.stringify(singleReceiptsData));
        showToast("تم حذف السند بنجاح", "success");
        resetVoucherSilent();
    }
}

function openPartnerStatement() {
    const accId = document.getElementById('from-account').value;
    if (!accId) {
        showToast('يرجى اختيار العميل أولاً لعرض كشف الحساب', 'warning');
        return;
    }
    window.open('statement.html?partnerId=' + accId, '_blank');
}

function showSuccessModal(title, subtitle) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px);
        z-index: 999999; display: flex; justify-content: center; align-items: center;
        opacity: 0; animation: fadeInGlass 0.3s forwards;
    `;

    const modal = document.createElement('div');
    modal.style.cssText = `
        background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(16, 185, 129, 0.3);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), inset 0 0 0 1px rgba(255, 255, 255, 0.1);
        border-radius: 24px; padding: 40px; text-align: center; width: 90%; max-width: 450px;
        transform: scale(0.8) translateY(30px); animation: popInModal 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        position: relative; overflow: hidden;
    `;

    const glow = document.createElement('div');
    glow.style.cssText = `
        position: absolute; top: -50px; left: 50%; transform: translateX(-50%);
        width: 150px; height: 150px; background: radial-gradient(circle, rgba(16,185,129,0.3) 0%, transparent 70%);
        border-radius: 50%; pointer-events: none;
    `;

    const iconWrap = document.createElement('div');
    iconWrap.innerHTML = `
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(16,185,129,0.5)); animation: bounceIcon 2s infinite;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
    `;

    const h2 = document.createElement('h2');
    h2.innerText = title;
    h2.style.cssText = `color: #34d399; font-size: 1.8rem; margin: 0 0 10px 0; font-weight: 800; font-family: inherit;`;

    const p = document.createElement('p');
    p.innerText = subtitle;
    p.style.cssText = `color: #94a3b8; font-size: 1.1rem; margin: 0 0 30px 0; line-height: 1.5; font-family: inherit;`;

    const btnWrap = document.createElement('div');
    btnWrap.style.cssText = `display: flex; gap: 15px; justify-content: center;`;

    const btnPrint = document.createElement('button');
    btnPrint.innerText = '🖨️ طباعة السند';
    btnPrint.style.cssText = `
        background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);
        padding: 12px 25px; border-radius: 12px; font-size: 1rem; cursor: pointer;
        transition: all 0.2s; font-family: inherit; font-weight: 600; flex: 1;
    `;
    btnPrint.onmouseover = () => btnPrint.style.background = 'rgba(255,255,255,0.1)';
    btnPrint.onmouseout = () => btnPrint.style.background = 'rgba(255,255,255,0.05)';
    btnPrint.onclick = () => { printVoucher(); };

    const btnClose = document.createElement('button');
    btnClose.innerText = 'استمرار';
    btnClose.style.cssText = `
        background: linear-gradient(135deg, #10b981, #059669); color: white; border: none;
        padding: 12px 25px; border-radius: 12px; font-size: 1rem; cursor: pointer;
        font-family: inherit; font-weight: bold; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        transition: all 0.2s; flex: 1;
    `;
    btnClose.onmouseover = () => btnClose.style.transform = 'translateY(-2px)';
    btnClose.onmouseout = () => btnClose.style.transform = 'translateY(0)';
    btnClose.onclick = () => {
        overlay.style.animation = 'fadeOutGlass 0.3s forwards';
        modal.style.animation = 'slideDownGlass 0.3s forwards';
        setTimeout(() => overlay.remove(), 300);
    };

    btnWrap.appendChild(btnPrint);
    btnWrap.appendChild(btnClose);

    if (!document.getElementById('success-modal-styles')) {
        const style = document.createElement('style');
        style.id = 'success-modal-styles';
        style.innerHTML = `
            @keyframes fadeInGlass { from { opacity: 0; } to { opacity: 1; } }
            @keyframes fadeOutGlass { from { opacity: 1; } to { opacity: 0; } }
            @keyframes popInModal { 0% { opacity: 0; transform: scale(0.8) translateY(30px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
            @keyframes slideDownGlass { from { opacity: 1; transform: scale(1) translateY(0); } to { opacity: 0; transform: scale(0.8) translateY(30px); } }
            @keyframes bounceIcon { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        `;
        document.head.appendChild(style);
    }

    modal.appendChild(glow);
    modal.appendChild(iconWrap);
    modal.appendChild(h2);
    modal.appendChild(p);
    modal.appendChild(btnWrap);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
}
