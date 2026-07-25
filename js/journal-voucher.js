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
    const localCusts = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    const localSups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
    const localSafes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
    const localExp = JSON.parse(localStorage.getItem('erp_accounts_v1')) || [];

    localCusts.forEach(c => ACCOUNTS.push({ id: c.id, name: c.name }));
    localSups.forEach(s => ACCOUNTS.push({ id: s.id, name: s.name }));
    localBanks.forEach(b => ACCOUNTS.push({ id: b.id, name: b.name }));
    localSafes.forEach(s => ACCOUNTS.push({ id: s.id, name: s.name }));
    localExp.forEach(e => ACCOUNTS.push({ id: e.id || e.code, name: e.name }));

    const __d = new Date();
    const __y = __d.getFullYear();
    const __m = String(__d.getMonth() + 1).padStart(2, '0');
    const __day = String(__d.getDate()).padStart(2, '0');
    document.getElementById('hdr-date').value = `${__y}-${__m}-${__day}`;
    setNextVoucherNumber();
    
    // Initialize with 2 empty rows by default
    addGridRow();
    addGridRow();

    // Deep Drill-down
    const urlParams = new URLSearchParams(window.location.search);
    const voucherId = urlParams.get('id');
    if (voucherId) {
        jvData = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
        const idx = jvData.findIndex(v => String(v.id) === String(voucherId) || String(v.voucherNo) === String(voucherId) || String(v.jvNo) === String(voucherId));
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

let jvRowIdCounter = 0;
function addGridRow(data = {}) {
    jvRowIdCounter++;
    const tbody = document.getElementById('jv-tbody');
    const tr = document.createElement('tr');
    tr.className = 'jv-row';
    
    
    let accName = '';
    if (data.account) {
        accName = getAccountName(data.account);
    }
    tr.innerHTML = `
        <td>
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" class="jv-input acct-select" id="acc-${jvRowIdCounter}" value="${data.account || ''}">
                <input type="text" class="jv-input" id="acc-${jvRowIdCounter}-search" value="${accName}" placeholder="ابحث عن حساب..." onfocus="showAccountDropdown('acc-${jvRowIdCounter}')" onblur="hideAccountDropdownDelayed('acc-${jvRowIdCounter}')" oninput="filterAccountDropdown('acc-${jvRowIdCounter}')" style="width: 100%; box-sizing: border-box; background: transparent; border: none; color: inherit; font-size: 0.95rem;">
                <div class="custom-dropdown-list" id="acc-${jvRowIdCounter}-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5);"></div>
            </div>
        </td>
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
    if (window.applyImmutabilityUI) window.applyImmutabilityUI(v.status);
}

function removeGridRow(btn) {
    const tbody = document.getElementById('jv-tbody');
    if (tbody.children.length <= 2) {
        showToast("يجب أن يحتوي القيد على سطرين كحد أدنى!", "warning");
        return;
    }
    btn.closest('tr').remove();
    calculateTotals();
    if (window.applyImmutabilityUI) window.applyImmutabilityUI(v.status);
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
    if (window.applyImmutabilityUI) window.applyImmutabilityUI(v.status);
}

function formatInput(input) {
    let val = parseNum(input.value);
    input.value = val > 0 ? fmtNum(val) : '';
    calculateTotals();
    if (window.applyImmutabilityUI) window.applyImmutabilityUI(v.status);
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
    if (window.applyImmutabilityUI) window.applyImmutabilityUI(v.status);
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
    if (window.applyImmutabilityUI) window.applyImmutabilityUI(v.status);
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

    
    const _docDateStr = document.getElementById('hdr-date') ? document.getElementById('hdr-date').value : new Date().toISOString().split('T')[0];
    if (!window.canCreateRecord(_docDateStr)) return;

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    const voucherData = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        id: document.getElementById('voucher-number').innerText,
        date: document.getElementById('hdr-date').value,
        time: new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
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
        if (window.logSystemAction) window.logSystemAction(currentViewedIndex !== -1 ? 'تعديل قيد يومية' : 'إنشاء قيد يومية', 'FINANCE', 'رقم القيد: ' + voucherData.id);
        showToast(`تم تعديل القيد رقم ${voucherData.id} بنجاح!`, "success");
    } else {
        jvData.push(voucherData);
        localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvData));
        if (window.logSystemAction) window.logSystemAction(currentViewedIndex !== -1 ? 'تعديل قيد يومية' : 'إنشاء قيد يومية', 'FINANCE', 'رقم القيد: ' + voucherData.id);
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
        if (window.logSystemAction) window.logSystemAction(currentViewedIndex !== -1 ? 'تعديل قيد يومية' : 'إنشاء قيد يومية', 'FINANCE', 'رقم القيد: ' + voucherData.id);
        showToast("تم حذف القيد بنجاح", "success");
        resetVoucherSilent();
    }
}


// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Accounts
// ═══════════════════════════════════════════════════════
function renderAccountDropdown(type, filter = '') {
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
                    <span>[${a.id}]</span>
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
    document.getElementById(type).value = id; // Store ID for save logic
    document.getElementById(type + '-search').value = name; // Store Name for display
    
    const listEl = document.getElementById(type + '-dropdown-list');
    if(listEl) {
        listEl.classList.remove('show');
        listEl.style.display = 'none';
    }
    
    // Jump logic to next input on the same row
    if (type.startsWith('acc-')) {
        const rowTr = document.getElementById(type).closest('tr');
        if(rowTr) {
            const debInp = rowTr.querySelector('.debit');
            if(debInp) debInp.focus();
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
