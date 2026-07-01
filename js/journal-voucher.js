/* ═══════════════════════════════════════════════════════
   journal-voucher.js — Journal Voucher Module
   ═══════════════════════════════════════════════════════ */

// Load real accounts from master-data
let ACCOUNTS = [];
// (Moved to DBReady event)

let jvData = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
let currentViewedIndex = -1; 

function fmtNum(n) {
    if (n === 0 || n === '0' || !n) return '0.00';
    return Number(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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

document.addEventListener('DBReady', () => {
    // Load real accounts from master-data
    const localBanks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    const localCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
    const localSups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
    const localSafes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
    const localExp = JSON.parse(localStorage.getItem('erp_accounts_v1')) || [];

    localCusts.forEach(c => ACCOUNTS.push({ id: c.id, name: c.name }));
    localSups.forEach(s => ACCOUNTS.push({ id: s.id, name: s.name }));
    localBanks.forEach(b => ACCOUNTS.push({ id: b.id, name: b.name }));
    localSafes.forEach(s => ACCOUNTS.push({ id: s.id, name: s.name }));
    localExp.forEach(e => ACCOUNTS.push({ id: e.id || e.code, name: e.name }));

    document.getElementById('hdr-date').valueAsDate = new Date();
    setNextVoucherNumber();
    
    // Initialize with 2 empty rows by default
    addGridRow();
    addGridRow();

    // Deep Drill-down
    const urlParams = new URLSearchParams(window.location.search);
    const voucherId = urlParams.get('id');
    if (voucherId) {
        jvData = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
        const idx = jvData.findIndex(v => v.id === voucherId);
        if (idx !== -1) {
            currentViewedIndex = idx;
            renderVoucher(jvData[idx]);
        }
    }
});

function getAccountOptions(selectedId = '') {
    let opts = `<option value="">-- اختر الحساب --</option>`;
    ACCOUNTS.forEach(a => {
        opts += `<option value="${a.id}" ${a.id === selectedId ? 'selected' : ''}>${a.name}</option>`;
    });
    return opts;
}

function addGridRow(data = {}) {
    const tbody = document.getElementById('jv-tbody');
    const tr = document.createElement('tr');
    tr.className = 'jv-row';
    
    tr.innerHTML = `
        <td><select class="jv-input acct-select">${getAccountOptions(data.account)}</select></td>
        <td><input type="text" class="jv-input debit" value="${data.debit ? fmtNum(data.debit) : ''}" placeholder="0.00" onfocus="this.select()" oninput="handleInput(this, 'debit')" onblur="formatInput(this)"></td>
        <td><input type="text" class="jv-input credit" value="${data.credit ? fmtNum(data.credit) : ''}" placeholder="0.00" onfocus="this.select()" oninput="handleInput(this, 'credit')" onblur="formatInput(this)"></td>
        <td>
            <select class="jv-input currency" onchange="calcRowEquiv(this)">
                <option value="SDG" ${data.currency === 'SDG' ? 'selected' : ''}>SDG</option>
                <option value="USD" ${data.currency === 'USD' ? 'selected' : ''}>USD</option>
                <option value="AED" ${data.currency === 'AED' ? 'selected' : ''}>AED</option>
            </select>
        </td>
        <td><input type="text" class="jv-input rate" value="${data.rate || '1.0'}" oninput="calcRowEquiv(this)" ${data.currency && data.currency !== 'SDG' ? '' : 'readonly'}></td>
        <td><input type="text" class="jv-input cost-center" value="${data.costCenter || ''}" placeholder="مركز التكلفة..."></td>
        <td><input type="text" class="jv-input note" value="${data.note || ''}" placeholder="بيان السطر..."></td>
        <td><button class="jv-del-btn" onclick="removeGridRow(this)" title="حذف السطر">🗑️</button></td>
    `;
    tbody.appendChild(tr);
    calculateTotals();
}

function removeGridRow(btn) {
    const tbody = document.getElementById('jv-tbody');
    if (tbody.children.length <= 2) {
        showToast("يجب أن يحتوي القيد على سطرين كحد أدنى!", "warning");
        return;
    }
    btn.closest('tr').remove();
    calculateTotals();
}

// Logic: If user types in Debit, clear Credit, and vice versa.
function handleInput(input, type) {
    const row = input.closest('tr');
    const debitInp = row.querySelector('.debit');
    const creditInp = row.querySelector('.credit');
    
    if (type === 'debit' && parseNum(input.value) > 0) {
        creditInp.value = '';
    } else if (type === 'credit' && parseNum(input.value) > 0) {
        debitInp.value = '';
    }
    calculateTotals();
}

function formatInput(input) {
    let val = parseNum(input.value);
    input.value = val > 0 ? fmtNum(val) : '';
    calculateTotals();
}

function calcRowEquiv(element) {
    const row = element.closest('tr');
    const curr = row.querySelector('.currency').value;
    const rateInp = row.querySelector('.rate');
    
    if (curr === 'SDG') {
        rateInp.value = '1.0';
        rateInp.readOnly = true;
    } else {
        rateInp.readOnly = false;
        if(parseNum(rateInp.value) === 1) rateInp.value = ''; 
    }
    // We calculate base logic based on local equivalents.
    // Future expansion: store foreign amounts alongside local.
}

function calculateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;
    
    document.querySelectorAll('#jv-tbody tr').forEach(row => {
        totalDebit += parseNum(row.querySelector('.debit').value);
        totalCredit += parseNum(row.querySelector('.credit').value);
    });

    const diff = Math.abs(totalDebit - totalCredit);
    
    document.getElementById('tot-debit').innerText = fmtNum(totalDebit);
    document.getElementById('tot-credit').innerText = fmtNum(totalCredit);
    
    const diffEl = document.getElementById('tot-diff');
    diffEl.innerText = fmtNum(diff);
    
    if (diff === 0 && totalDebit > 0) {
        diffEl.classList.add('balanced');
    } else {
        diffEl.classList.remove('balanced');
    }
}

function setNextVoucherNumber() {
    const seq = parseInt(localStorage.getItem('erp_jv_seq') || '0') + 1;
    document.getElementById('voucher-number').innerText = 'JV-' + String(seq).padStart(5, '0');
}

function resetVoucher() {
    if(confirm("هل أنت متأكد من مسح البيانات والبدء بقيد جديد؟")) {
        resetVoucherSilent();
        showToast("تم فتح قيد جديد", "success");
    }
}

function resetVoucherSilent() {
    currentViewedIndex = -1;
    document.getElementById('hdr-date').valueAsDate = new Date();
    document.getElementById('hdr-ref').value = '';
    document.getElementById('hdr-statement').value = '';
    
    document.getElementById('jv-tbody').innerHTML = '';
    addGridRow();
    addGridRow();
    
    setNextVoucherNumber();
    calculateTotals();
}

function loadPreviousVoucher() {
    jvData = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
    if (jvData.length === 0) return showToast("لا توجد قيود سابقة", "warning");
    
    if (currentViewedIndex === -1) {
        currentViewedIndex = jvData.length - 1;
    } else if (currentViewedIndex > 0) {
        currentViewedIndex--;
    } else {
        return showToast("هذا هو أول قيد تم حفظه", "warning");
    }
    renderVoucher(jvData[currentViewedIndex]);
}

function loadNextVoucher() {
    jvData = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
    if (currentViewedIndex === -1) return; // already at new
    
    if (currentViewedIndex < jvData.length - 1) {
        currentViewedIndex++;
        renderVoucher(jvData[currentViewedIndex]);
    } else {
        resetVoucherSilent();
        showToast("قيد جديد", "success");
    }
}

function renderVoucher(v) {
    document.getElementById('voucher-number').innerText = v.id;
    document.getElementById('hdr-date').value = v.date;
    document.getElementById('hdr-ref').value = v.ref || '';
    document.getElementById('hdr-statement').value = v.statement || '';
    
    document.getElementById('jv-tbody').innerHTML = '';
    v.lines.forEach(line => addGridRow(line));
    
    calculateTotals();
}

function submitVoucher() {
    let totalDebit = 0;
    let totalCredit = 0;
    let lines = [];
    let hasError = false;

    document.querySelectorAll('#jv-tbody tr').forEach(row => {
        const acc = row.querySelector('.acct-select').value;
        const deb = parseNum(row.querySelector('.debit').value);
        const cred = parseNum(row.querySelector('.credit').value);
        
        if (deb > 0 || cred > 0) {
            if (!acc) {
                showToast("يرجى تحديد الحساب في كل سطر يحتوي على مبالغ!", "error");
                hasError = true;
                return;
            }
            totalDebit += deb;
            totalCredit += cred;
            
            lines.push({
                account: acc,
                debit: deb,
                credit: cred,
                currency: row.querySelector('.currency').value,
                rate: parseNum(row.querySelector('.rate').value),
                costCenter: row.querySelector('.cost-center').value,
                note: row.querySelector('.note').value
            });
        }
    });

    if (hasError) return;

    if (lines.length < 2) {
        showToast("يجب إدخال طرفين على الأقل (مدين ودائن)", "error");
        return;
    }

    if (totalDebit === 0) {
        showToast("لا يمكن حفظ قيد بقيمة صفرية!", "error");
        return;
    }

    if (Math.abs(totalDebit - totalCredit) > 0.01) { // 0.01 tolerance for floating point
        showToast("🚨 القيد غير متوازن! يجب أن يتساوى إجمالي المدين والدائن.", "error");
        return;
    }

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    const voucherData = {
        id: document.getElementById('voucher-number').innerText,
        date: document.getElementById('hdr-date').value,
        ref: document.getElementById('hdr-ref').value,
        statement: document.getElementById('hdr-statement').value,
        totalAmount: totalDebit,
        lines: lines,
        timestamp: new Date().toISOString()
    };

    jvData = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
    
    if (currentViewedIndex !== -1) {
        if (!window.canEditRecord(voucherData.date, 'finance')) return;
        jvData[currentViewedIndex] = voucherData;
        localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvData));
        showToast(`تم تعديل القيد رقم ${voucherData.id} بنجاح!`, "success");
    } else {
        jvData.push(voucherData);
        localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvData));
        const currentSeq = parseInt(localStorage.getItem('erp_jv_seq') || '0');
        localStorage.setItem('erp_jv_seq', currentSeq + 1);
        showToast(`تم اعتماد القيد رقم ${voucherData.id} بنجاح!`, "success");
    }

    setTimeout(() => { resetVoucherSilent(); }, 1500);
}

function printVoucher() {
    window.print();
}

function confirmDelete() {
    if (currentViewedIndex === -1) {
        showToast("لا يوجد قيد محفوظ لحذفه حالياً", "warning");
        return;
    }
    if (confirm("هل أنت متأكد من حذف هذا القيد نهائياً؟")) {
        jvData.splice(currentViewedIndex, 1);
        localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvData));
        showToast("تم حذف القيد بنجاح", "success");
        resetVoucherSilent();
    }
}
