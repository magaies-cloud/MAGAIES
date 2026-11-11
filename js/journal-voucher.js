window.openRateEditor = function(inputEl) {
    if (typeof window.openGlobalExchangeRateModal === 'function') {
        window.openGlobalExchangeRateModal(function(newRate) {
            inputEl.value = newRate;
            if (typeof calcRowEquiv === 'function') calcRowEquiv(inputEl);
            document.querySelectorAll('#jv-tbody tr').forEach(row => {
                const curSel = row.querySelector('.currency');
                if (curSel && curSel.value !== 'SDG') {
                    const rInp = row.querySelector('.rate');
                    if (rInp) {
                        rInp.value = newRate;
                        if (typeof calcRowEquiv === 'function') calcRowEquiv(rInp);
                    }
                }
            });
        });
    } else {
        const r = prompt('أدخل سعر الصرف مقابل الجنيه (SDG):', inputEl.value || '600');
        if (r && !isNaN(parseFloat(r))) {
            inputEl.value = parseFloat(r);
            calculateTotals();
        }
    }
};

/* ═══════════════════════════════════════════════════════════════════════════
   journal-voucher.js — Enterprise Journal Voucher & Adjustments Engine 360°
   ═══════════════════════════════════════════════════════════════════════════ */

let ACCOUNTS = [];
let jvData = [];
let currentViewedIndex = -1;
let jvRowIdCounter = 0;

function readAllStoredJVs() {
    try {
        const a = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]');
        const b = JSON.parse(localStorage.getItem('erp_journal_entries_v1') || '[]');
        const map = new Map();
        [...(Array.isArray(b) ? b : []), ...(Array.isArray(a) ? a : [])].forEach(item => {
            if (!item || item.deleted) return;
            const id = String(item.id || item.entryNo || item.voucherNo || '');
            if (!id) return;
            map.set(id, {
                ...item,
                id: id,
                entryNo: id,
                voucherNo: id,
                statement: item.statement || item.description || item.notes || '',
                description: item.statement || item.description || item.notes || '',
                totalAmount: Number(item.totalAmount || item.totalDebit || item.amount || 0),
                lines: Array.isArray(item.lines) ? item.lines : []
            });
        });
        return Array.from(map.values());
    } catch (e) {
        return [];
    }
}

function saveAllStoredJVs(list) {
    localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(list));
    localStorage.setItem('erp_journal_entries_v1', JSON.stringify(list));
}

function fmtNum(n) {
    if (n === 0 || n === '0' || !n) return '0.00';
    return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function parseNum(str) {
    return parseFloat(String(str || '0').replace(/,/g, '')) || 0;
}

function showToast(msg, type = 'success', ms = 3200) {
    const c = document.getElementById('toast-container');
    if (!c) return;
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = msg;
    c.appendChild(t);
    setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 400); }, ms);
}

function buildAccountsCatalog() {
    const localBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
    const localCusts = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(x => !x.deleted);
    const localSups = (JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]')).filter(x => !x.deleted);
    const localSafes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
    const localExp = JSON.parse(localStorage.getItem('erp_accounts_v1') || '[]');

    // Standard General Ledger Accounts built-in so templates & adjustments always work
    const stdAccounts = [
        { id: 'acc-sales', name: 'حساب المبيعات والإيرادات العامة', tag: 'إيراد', balance: 0 },
        { id: 'acc-purchases', name: 'حساب المشتريات وتكلفة البضاعة', tag: 'مصروف', balance: 0 },
        { id: 'acc-ifrs9-exp', name: 'مصروف ديون مشكوك في تحصيلها (IFRS-9)', tag: 'مصروف', balance: 0 },
        { id: 'acc-ifrs9-prov', name: 'مخصص الديون المشكوك في تحصيلها', tag: 'مخصص', balance: 0 },
        { id: 'acc-depr-exp', name: 'مصروف إهلاك الأصول الثابتة', tag: 'مصروف', balance: 0 },
        { id: 'acc-depr-accum', name: 'مجمع إهلاك الأصول الثابتة', tag: 'مجمع إهلاك', balance: 0 },
        { id: 'acc-salaries-exp', name: 'حساب الرواتب والأجور والمكافآت', tag: 'مصروف', balance: 0 },
        { id: 'acc-salaries-pay', name: 'رواتب وأجور مستحقة الدفع', tag: 'التزام', balance: 0 },
        { id: 'acc-petty-cash', name: 'حساب العهد النقدية والمصروفات النثرية', tag: 'عهدة', balance: 0 },
        { id: 'acc-inv-shortage', name: 'حساب تسوية فروق وعجز/فائض المخزون', tag: 'تسوية مخزون', balance: 0 },
        { id: 'acc-inventory', name: 'حساب مخزون البضاعة بالمستودعات', tag: 'أصل متداول', balance: 0 },
        { id: 'acc-fx-gainloss', name: 'حساب أرباح وخسائر فروق العملة', tag: 'فروق عملة', balance: 0 },
        { id: 'acc-capital', name: 'حساب رأس المال وحقوق الملكية والأرصدة الافتتاحية', tag: 'حقوق ملكية', balance: 0 }
    ];

    const map = new Map();
    const addAcc = (id, name, tag, bal) => {
        if (!name) return;
        const k = String(id || name).trim();
        map.set(k, { id: k, name: String(name).trim(), tag: tag || 'حساب', balance: Number(bal || 0) });
    };

    localCusts.forEach(c => addAcc(c.id || ('cust-' + c.name), c.name, 'عميل', c.currentBalance ?? c.balance ?? 0));
    localSups.forEach(s => addAcc(s.id || ('supp-' + s.name), s.name, 'مورد', s.currentBalance ?? s.balance ?? 0));
    localBanks.forEach(b => addAcc(b.id || ('bank-' + b.name), b.name, 'بنك', b.balance ?? b.currentBalance ?? 0));
    localSafes.forEach(s => addAcc(s.id || ('safe-' + s.name), s.name, 'خزينة', s.balance ?? s.currentBalance ?? 0));
    localExp.forEach(e => addAcc(e.id || e.code || e.name, e.name, e.type || 'حساب عام', e.balance ?? 0));
    stdAccounts.forEach(a => { if (!map.has(a.id)) map.set(a.id, a); });

    ACCOUNTS = Array.from(map.values());
}

function initJVApp() {
    if (window._jvAppInitialized) return;
    window._jvAppInitialized = true;

    buildAccountsCatalog();
    jvData = readAllStoredJVs();

    const dateEl = document.getElementById('hdr-date');
    if (dateEl && !dateEl.value) {
        dateEl.value = new Date().toISOString().slice(0, 10);
    }
    setNextVoucherNumber();

    const tbody = document.getElementById('jv-tbody');
    if (tbody && tbody.children.length === 0) {
        addGridRow();
        addGridRow();
    }

    // Handle URL Deep-Links (?id=..., ?entry=..., or ?desc=...&amount=...)
    const urlParams = new URLSearchParams(window.location.search);
    const voucherId = urlParams.get('id') || urlParams.get('entry') || urlParams.get('voucher');
    const preDesc = urlParams.get('desc');
    const preAmount = parseFloat(urlParams.get('amount') || '0');

    if (voucherId) {
        const idx = jvData.findIndex(v => String(v.id) === String(voucherId) || String(v.voucherNo) === String(voucherId));
        if (idx !== -1) {
            currentViewedIndex = idx;
            renderVoucher(jvData[idx]);
        }
    } else if (preDesc || preAmount > 0) {
        if (preDesc) document.getElementById('hdr-statement').value = preDesc;
        document.getElementById('hdr-jv-type').value = 'قيد استحقاق ومخصصات';
        if (preAmount > 0) {
            document.getElementById('jv-tbody').innerHTML = '';
            addGridRow({ account: 'acc-ifrs9-exp', debit: preAmount, credit: 0, note: preDesc || 'إثبات مصروف المخصص' });
            addGridRow({ account: 'acc-ifrs9-prov', debit: 0, credit: preAmount, note: preDesc || 'تكوين مخصص الديون المشكوك فيها' });
            calculateTotals();
        }
    } else if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('jv-user-audit-container', {}, true);
    }

    renderJournalArchiveTable();
}

window.initJVApp = initJVApp;
if (document.readyState !== 'loading') {
    initJVApp();
} else {
    document.addEventListener('DOMContentLoaded', initJVApp);
}
document.addEventListener('DBReady', () => {
    buildAccountsCatalog();
    renderJournalArchiveTable();
});

function addGridRow(data = {}) {
    jvRowIdCounter++;
    const tbody = document.getElementById('jv-tbody');
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.className = 'jv-row';

    const accName = data.account ? getAccountName(data.account) : (data.accountName || '');
    const cur = data.currency || 'SDG';
    const rate = data.rate || (cur === 'SDG' ? '1.0' : (localStorage.getItem('erp_exchange_rate') || '600'));

    tr.innerHTML = `
        <td>
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" class="jv-input acct-select" id="acc-${jvRowIdCounter}" value="${data.account || accName || ''}">
                <input type="text" class="jv-input" id="acc-${jvRowIdCounter}-search" value="${accName}" placeholder="🔍 ابحث باسم أو كود الحساب..." autocomplete="off" onfocus="showAccountDropdown('acc-${jvRowIdCounter}')" onblur="hideAccountDropdownDelayed('acc-${jvRowIdCounter}')" oninput="filterAccountDropdown('acc-${jvRowIdCounter}')">
                <div class="custom-dropdown-list" id="acc-${jvRowIdCounter}-dropdown-list" style="display:none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #0f172a; border: 1.5px solid #c084fc; border-radius: 10px; max-height: 240px; overflow-y: auto; text-align: right; box-shadow: 0 12px 30px rgba(0,0,0,0.65);"></div>
            </div>
        </td>
        <td><input type="text" class="jv-input debit" value="${data.debit ? fmtNum(data.debit) : ''}" placeholder="0.00" onfocus="this.select()" oninput="handleInput(this, 'debit')" onblur="formatInput(this)"></td>
        <td><input type="text" class="jv-input credit" value="${data.credit ? fmtNum(data.credit) : ''}" placeholder="0.00" onfocus="this.select()" oninput="handleInput(this, 'credit')" onblur="formatInput(this)"></td>
        <td>
            <select class="jv-input currency" onchange="calcRowEquiv(this)">
                <option value="SDG" ${cur === 'SDG' ? 'selected' : ''}>SDG</option>
                <option value="USD" ${cur === 'USD' ? 'selected' : ''}>USD ($)</option>
                <option value="AED" ${cur === 'AED' ? 'selected' : ''}>AED</option>
            </select>
        </td>
        <td><input type="text" class="jv-input rate" style="cursor:pointer; background:rgba(16,185,129,0.12); color:#34d399; font-weight:900; text-align:center;" value="${rate}" onclick="window.openRateEditor(this)" oninput="calculateTotals()"></td>
        <td><input type="text" class="jv-input cost-center" value="${data.costCenter || ''}" placeholder="المركز / المشروع..."></td>
        <td><input type="text" class="jv-input note" value="${data.note || ''}" placeholder="بيان وشرح السطر..."></td>
        <td class="no-print" style="text-align:center;"><button type="button" class="jv-del-btn" onclick="removeGridRow(this)" title="حذف السطر">🗑️</button></td>
    `;
    tbody.appendChild(tr);
    calculateTotals();
}

function removeGridRow(btn) {
    const tbody = document.getElementById('jv-tbody');
    if (tbody.children.length <= 2) {
        showToast("يجب أن يحتوي القيد على طرفين (سطرين) كحد أدنى!", "warning");
        return;
    }
    btn.closest('tr').remove();
    calculateTotals();
}

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
    const val = parseNum(input.value);
    input.value = val > 0 ? fmtNum(val) : '';
    calculateTotals();
}

function calcRowEquiv(element) {
    const row = element.closest('tr');
    const curr = row.querySelector('.currency').value;
    const rateInp = row.querySelector('.rate');

    if (curr === 'SDG') {
        rateInp.value = '1.0';
    } else {
        const defRate = (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(curr) : 0) || Number(localStorage.getItem('erp_exchange_rate') || 600);
        if (parseNum(rateInp.value) <= 1) {
            rateInp.value = defRate;
        }
    }
    calculateTotals();
}

function calculateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;

    document.querySelectorAll('#jv-tbody tr').forEach(row => {
        const rate = Math.max(1, parseNum(row.querySelector('.rate')?.value || 1));
        const curr = row.querySelector('.currency')?.value || 'SDG';
        const mult = curr === 'SDG' ? 1 : rate;
        totalDebit += parseNum(row.querySelector('.debit')?.value) * mult;
        totalCredit += parseNum(row.querySelector('.credit')?.value) * mult;
    });

    const diff = Math.abs(totalDebit - totalCredit);
    const diffEl = document.getElementById('tot-diff');
    const badgeEl = document.getElementById('jv-balance-status-badge');

    document.getElementById('tot-debit').innerText = fmtNum(totalDebit);
    document.getElementById('tot-credit').innerText = fmtNum(totalCredit);
    if (diffEl) diffEl.innerText = fmtNum(diff);

    if (diff < 0.01 && totalDebit > 0) {
        if (diffEl) diffEl.classList.add('balanced');
        if (badgeEl) {
            badgeEl.style.color = '#34d399';
            badgeEl.innerHTML = '✅ القيد متوازن ومطابق 100%';
        }
    } else if (totalDebit === 0 && totalCredit === 0) {
        if (diffEl) diffEl.classList.remove('balanced');
        if (badgeEl) {
            badgeEl.style.color = '#fbbf24';
            badgeEl.innerHTML = '⏳ بانتظار إدخال المبالغ';
        }
    } else {
        if (diffEl) diffEl.classList.remove('balanced');
        if (badgeEl) {
            badgeEl.style.color = '#f87171';
            badgeEl.innerHTML = `🚨 غير متوازن (فرق: ${fmtNum(diff)})`;
        }
    }
}

// ── Innovation #2: Auto-Balance Journal Entry ──
window.autoBalanceJournal = function() {
    let totalDebit = 0;
    let totalCredit = 0;
    const rows = Array.from(document.querySelectorAll('#jv-tbody tr'));
    rows.forEach(row => {
        const rate = Math.max(1, parseNum(row.querySelector('.rate')?.value || 1));
        const curr = row.querySelector('.currency')?.value || 'SDG';
        const mult = curr === 'SDG' ? 1 : rate;
        totalDebit += parseNum(row.querySelector('.debit')?.value) * mult;
        totalCredit += parseNum(row.querySelector('.credit')?.value) * mult;
    });

    const diff = +(totalDebit - totalCredit).toFixed(2);
    if (Math.abs(diff) < 0.01) {
        showToast('القيد متوازن بالفعل 100% ✅', 'success');
        return;
    }

    // Find an empty row or create a new one
    let targetRow = rows.find(r => parseNum(r.querySelector('.debit')?.value) === 0 && parseNum(r.querySelector('.credit')?.value) === 0);
    if (!targetRow) {
        addGridRow();
        const updatedRows = document.querySelectorAll('#jv-tbody tr');
        targetRow = updatedRows[updatedRows.length - 1];
    }

    if (diff > 0) {
        targetRow.querySelector('.credit').value = fmtNum(Math.abs(diff));
        targetRow.querySelector('.debit').value = '';
    } else {
        targetRow.querySelector('.debit').value = fmtNum(Math.abs(diff));
        targetRow.querySelector('.credit').value = '';
    }
    calculateTotals();
    showToast(`⚡ تمت موازنة فرق القيد بمبلغ ${fmtNum(Math.abs(diff))} تلقائياً!`, 'success');
};

// ── Innovation #3: Reverse Journal Entry ──
window.reverseCurrentJournal = function() {
    const rows = document.querySelectorAll('#jv-tbody tr');
    let swapped = 0;
    rows.forEach(row => {
        const dInp = row.querySelector('.debit');
        const cInp = row.querySelector('.credit');
        const dVal = dInp.value;
        const cVal = cInp.value;
        if (parseNum(dVal) > 0 || parseNum(cVal) > 0) {
            dInp.value = cVal;
            cInp.value = dVal;
            swapped++;
        }
    });
    if (swapped === 0) {
        showToast('لا توجد مبالغ لعكسها في القيد الحالي', 'warning');
        return;
    }
    const stmtEl = document.getElementById('hdr-statement');
    const oldNo = document.getElementById('voucher-number').innerText;
    if (stmtEl && !stmtEl.value.includes('قيد عكسي')) {
        stmtEl.value = `[قيد عكسي لـ ${oldNo}] ` + stmtEl.value;
    }
    document.getElementById('hdr-jv-type').value = 'قيد عكسي';
    currentViewedIndex = -1;
    setNextVoucherNumber();
    calculateTotals();
    showToast('🔄 تم عكس أطراف القيد (المدين ⬅️ دائن) وتوليد رقم قيد عكسي جديد جاهز للحفظ!', 'success');
};

// ── Innovation #4: Duplicate Journal Entry ──
window.duplicateCurrentJournal = function() {
    currentViewedIndex = -1;
    document.getElementById('hdr-date').value = new Date().toISOString().slice(0, 10);
    setNextVoucherNumber();
    showToast('📋 تم نسخ القيد كقيد جديد بتاريخ اليوم — يمكنك تعديله وحفظه الآن!', 'success');
};

// ── Innovation #1: Smart Accounting Templates ──
window.applyJournalTemplate = function(tplKey) {
    const templates = {
        expense_settlement: {
            type: 'قيد تسوية محاسبية',
            statement: 'تسوية عهدة نقدية ومصروفات تشغيلية',
            lines: [
                { account: 'acc-purchases', debit: 50000, credit: 0, note: 'إثبات المصروفات التشغيلية الفعلية' },
                { account: 'acc-petty-cash', debit: 0, credit: 50000, note: 'إقفال وتسوية العهدة النقدية' }
            ]
        },
        partner_offset: {
            type: 'قيد مقاصة شركاء',
            statement: 'إجراء مقاصة محاسبية بين رصيد المورد ورصيد العميل',
            lines: [
                { account: ACCOUNTS.find(a => a.tag === 'مورد')?.id || 'acc-purchases', debit: 100000, credit: 0, note: 'تخفيض مديونية المورد بالمقاصة' },
                { account: ACCOUNTS.find(a => a.tag === 'عميل')?.id || 'acc-sales', debit: 0, credit: 100000, note: 'تسديد رصيد العميل بالمقاصة' }
            ]
        },
        ifrs9_provision: {
            type: 'قيد استحقاق ومخصصات',
            statement: 'تكوين مخصص الديون المشكوك في تحصيلها وفق معيار IFRS-9',
            lines: [
                { account: 'acc-ifrs9-exp', debit: 150000, credit: 0, note: 'تحميل مصروف الديون المشكوك فيها للفترة' },
                { account: 'acc-ifrs9-prov', debit: 0, credit: 150000, note: 'تكوين مخصص مقابلة مخاطر الائتمان' }
            ]
        },
        depreciation: {
            type: 'قيد تسوية محاسبية',
            statement: 'إثبات قسط إهلاك الأصول الثابتة والمعدات للفترة',
            lines: [
                { account: 'acc-depr-exp', debit: 75000, credit: 0, note: 'مصروف إهلاك الفترة' },
                { account: 'acc-depr-accum', debit: 0, credit: 75000, note: 'مجمع إهلاك الأصول الثابتة' }
            ]
        },
        payroll: {
            type: 'قيد استحقاق ومخصصات',
            statement: 'إثبات استحقاق الرواتب والأجور الشهرية للعاملين',
            lines: [
                { account: 'acc-salaries-exp', debit: 500000, credit: 0, note: 'إجمالي استحقاق الرواتب والأجور' },
                { account: 'acc-salaries-pay', debit: 0, credit: 500000, note: 'رواتب مستحقة الصرف للعاملين' }
            ]
        },
        fx_revaluation: {
            type: 'قيد تسوية محاسبية',
            statement: 'إعادة تقييم الأرصدة بالعملة الأجنبية وإثبات فروق الصرف',
            lines: [
                { account: ACCOUNTS.find(a => a.tag === 'بنك')?.id || 'acc-capital', debit: 60000, credit: 0, note: 'زيادة تقييم الرصيد الأجنبي' },
                { account: 'acc-fx-gainloss', debit: 0, credit: 60000, note: 'أرباح إعادة تقييم العملة الأجنبية' }
            ]
        },
        inventory_diff: {
            type: 'قيد تسوية محاسبية',
            statement: 'تسوية فروق الجرد الفعلي للمخزون مع الرصيد الدفتري',
            lines: [
                { account: 'acc-inv-shortage', debit: 25000, credit: 0, note: 'إثبات عجز/فروق الجرد المخزني' },
                { account: 'acc-inventory', debit: 0, credit: 25000, note: 'تخفيض رصيد المخزون الدفتري للمطابقة' }
            ]
        },
        opening_entry: {
            type: 'قيد افتتاحي',
            statement: 'إثبات القيد الافتتاحي للأرصدة في بداية الفترة المالية',
            lines: [
                { account: 'acc-inventory', debit: 1000000, credit: 0, note: 'رصيد بضاعة أول المدة والأصول' },
                { account: 'acc-capital', debit: 0, credit: 1000000, note: 'صافي رأس المال وحقوق الملكية' }
            ]
        }
    };

    const tpl = templates[tplKey];
    if (!tpl) return;
    currentViewedIndex = -1;
    setNextVoucherNumber();
    document.getElementById('hdr-jv-type').value = tpl.type;
    document.getElementById('hdr-statement').value = tpl.statement;
    document.getElementById('jv-tbody').innerHTML = '';
    tpl.lines.forEach(l => addGridRow(l));
    calculateTotals();
    showToast(`⚡ تم تحميل قالب (${tpl.type}) — يمكنك تعديل الحسابات أو المبالغ وحفظ القيد!`, 'success');
};

// ── Innovation #8: AI Journal Validator ──
window.runAIJournalValidator = function() {
    const box = document.getElementById('jv-ai-validator-box');
    if (!box) return;
    box.style.display = 'block';

    const stmt = (document.getElementById('hdr-statement')?.value || '').trim();
    const rows = Array.from(document.querySelectorAll('#jv-tbody tr'));
    let deb = 0, cred = 0, cashTouched = false, missingAcc = false;

    rows.forEach(r => {
        const accId = r.querySelector('.acct-select')?.value || '';
        const accName = r.querySelector(`input[type="text"]`)?.value || '';
        const d = parseNum(r.querySelector('.debit')?.value);
        const c = parseNum(r.querySelector('.credit')?.value);
        if (d > 0 || c > 0) {
            deb += d;
            cred += c;
            if (!accId && !accName) missingAcc = true;
            if (accName.includes('خزينة') || accName.includes('صندوق') || accId.startsWith('safe-')) cashTouched = true;
        }
    });

    const notes = [];
    if (Math.abs(deb - cred) < 0.01 && deb > 0) {
        notes.push('✅ <strong>التوازن المحاسبي (Double-Entry Equilibrium):</strong> القيد متوازن بدقة 100% بين المدين والدائن.');
    } else {
        notes.push(`🚨 <strong>خلل في التوازن:</strong> يوجد فرق قدره (${fmtNum(Math.abs(deb - cred))}) — اضغط على زر "⚡ موازنة تلقائية" لمعالجته فوراً.`);
    }
    if (missingAcc) {
        notes.push('⚠️ <strong>تنبيه توجيه الحسابات:</strong> يوجد سطر يحتوي على مبلغ بدون تحديد الحساب المحاسبي.');
    }
    if (cashTouched) {
        notes.push('💡 <strong>ملاحظة رقابية (الحارس الأمين):</strong> يتضمن هذا القيد حركة على حساب نقدي/خزينة؛ تذكر أن الحركات النقدية المباشرة يُفضل توثيقها عبر شاشة (حركة مالية) لإصدار باركود قبض/صرف رسمي.');
    } else {
        notes.push('✅ <strong>سلامة الرقابة النقدية:</strong> القيد عبارة عن تسوية دفتريه سليمة ولا يتجاوز الدورة المستندية للخزينة.');
    }
    if (!stmt || stmt.length < 6) {
        notes.push('⚠️ <strong>جودة التوثيق:</strong> يُنصح بكتابة شرح وافٍ في خانة (البيان العام) لسهولة المراجعة في دفتر الأستاذ العام.');
    } else {
        notes.push('✅ <strong>التوثيق والبيان:</strong> شرح القيد واضح ومستوفٍ لمتطلبات المراجعة القانونية.');
    }

    box.innerHTML = `<div style="font-weight:900; color:#38bdf8; margin-bottom:6px;">🤖 تقرير المدقق المحاسبي الذكي (AI Entry Audit):</div>` + notes.join('<br>');
};

function setNextVoucherNumber() {
    let max = 0;
    const jvs = readAllStoredJVs();
    jvs.forEach(j => {
        const str = String(j.id || j.voucherNo || j.jvNo || '');
        const num = parseInt(str.replace(/\D/g, ''), 10);
        if (!isNaN(num) && num > max) max = num;
    });
    const savedSeq = parseInt(localStorage.getItem('erp_jv_seq') || '0', 10);
    const nextSeq = Math.max(savedSeq, max) + 1;
    const el = document.getElementById('voucher-number');
    if (el) el.innerText = 'JV-' + String(nextSeq).padStart(5, '0');
}

function resetVoucher() {
    resetVoucherSilent();
    showToast("🆕 تم فتح سند قيد يومية جديد", "success");
}

function resetVoucherSilent() {
    currentViewedIndex = -1;
    const dEl = document.getElementById('hdr-date');
    if (dEl) dEl.value = new Date().toISOString().slice(0, 10);
    document.getElementById('hdr-ref').value = '';
    document.getElementById('hdr-statement').value = '';
    if (document.getElementById('hdr-jv-type')) document.getElementById('hdr-jv-type').value = 'قيد يومية عام';
    if (document.getElementById('jv-ai-validator-box')) document.getElementById('jv-ai-validator-box').style.display = 'none';

    document.getElementById('jv-tbody').innerHTML = '';
    addGridRow();
    addGridRow();

    setNextVoucherNumber();
    calculateTotals();
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('jv-user-audit-container', {}, true);
    }
}

function loadPreviousVoucher() {
    jvData = readAllStoredJVs();
    if (jvData.length === 0) return showToast("لا توجد قيود سابقة محفوظة", "warning");

    if (currentViewedIndex === -1) {
        currentViewedIndex = jvData.length - 1;
    } else if (currentViewedIndex > 0) {
        currentViewedIndex--;
    } else {
        return showToast("هذا هو أول قيد في السجل", "warning");
    }
    renderVoucher(jvData[currentViewedIndex]);
}

function loadNextVoucher() {
    jvData = readAllStoredJVs();
    if (currentViewedIndex === -1) return;

    if (currentViewedIndex < jvData.length - 1) {
        currentViewedIndex++;
        renderVoucher(jvData[currentViewedIndex]);
    } else {
        resetVoucherSilent();
        showToast("قيد جديد", "success");
    }
}

function renderVoucher(v) {
    if (!v) return;
    document.getElementById('voucher-number').innerText = v.id || v.entryNo || 'JV';
    document.getElementById('hdr-date').value = (v.date || '').slice(0, 10);
    document.getElementById('hdr-ref').value = v.ref || '';
    document.getElementById('hdr-statement').value = v.statement || v.description || '';
    if (document.getElementById('hdr-jv-type') && v.jvType) {
        document.getElementById('hdr-jv-type').value = v.jvType;
    }

    document.getElementById('jv-tbody').innerHTML = '';
    const lines = Array.isArray(v.lines) && v.lines.length > 0 ? v.lines : [{}, {}];
    lines.forEach(line => addGridRow(line));

    calculateTotals();
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('jv-user-audit-container', v, false);
    }
}

function submitVoucher() {
    let totalDebit = 0;
    let totalCredit = 0;
    let lines = [];
    let hasError = false;

    document.querySelectorAll('#jv-tbody tr').forEach(row => {
        const accHidden = row.querySelector('.acct-select')?.value || '';
        const accSearch = row.querySelector('input[type="text"]')?.value || '';
        const acc = accHidden || accSearch;
        const deb = parseNum(row.querySelector('.debit')?.value);
        const cred = parseNum(row.querySelector('.credit')?.value);
        const curr = row.querySelector('.currency')?.value || 'SDG';
        const rate = Math.max(1, parseNum(row.querySelector('.rate')?.value || 1));
        const mult = curr === 'SDG' ? 1 : rate;

        if (deb > 0 || cred > 0) {
            if (!acc) {
                showToast("يرجى تحديد الحساب المحاسبي في كل سطر يحتوي على مبالغ!", "error");
                hasError = true;
                return;
            }
            const eqDeb = deb * mult;
            const eqCred = cred * mult;
            totalDebit += eqDeb;
            totalCredit += eqCred;

            lines.push({
                account: acc,
                accountName: getAccountName(acc) || accSearch || acc,
                debit: eqDeb,
                credit: eqCred,
                foreignDebit: deb,
                foreignCredit: cred,
                currency: curr,
                rate: rate,
                costCenter: row.querySelector('.cost-center')?.value || '',
                note: row.querySelector('.note')?.value || ''
            });
        }
    });

    if (hasError) return;
    if (lines.length < 2) {
        showToast("يجب إدخال طرفين على الأقل (مدين ودائن) لإتمام القيد المزدوج!", "error");
        return;
    }
    if (totalDebit === 0) {
        showToast("لا يمكن حفظ قيد بقيمة صفرية!", "error");
        return;
    }
    if (Math.abs(totalDebit - totalCredit) > 0.01) {
        showToast("🚨 القيد غير متوازن! اضغط على (⚡ موازنة تلقائية) لمطابقة المدين والدائن.", "error");
        return;
    }

    const _docDateStr = document.getElementById('hdr-date')?.value || new Date().toISOString().split('T')[0];
    if (window.canCreateRecord && !window.canCreateRecord(_docDateStr)) return;

    jvData = readAllStoredJVs();
    const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'مدير النظام', fullName: 'مدير النظام', username: 'admin' };
    const oldVoucher = (currentViewedIndex !== -1 && jvData[currentViewedIndex]) ? jvData[currentViewedIndex] : null;
    const vNo = document.getElementById('voucher-number').innerText;
    const stmt = document.getElementById('hdr-statement').value || 'قيد يومية محاسبي';
    const jvType = document.getElementById('hdr-jv-type')?.value || 'قيد يومية عام';

    const voucherData = {
        id: vNo,
        entryNo: vNo,
        voucherNo: vNo,
        jvType: jvType,
        date: _docDateStr,
        time: new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }),
        ref: document.getElementById('hdr-ref').value,
        statement: stmt,
        description: stmt,
        totalAmount: totalDebit,
        totalDebit: totalDebit,
        totalCredit: totalCredit,
        lines: lines,
        createdBy: oldVoucher?.createdBy || curUser.id,
        createdByName: oldVoucher?.createdByName || curUser.fullName || curUser.name || 'مدير النظام',
        createdByUsername: oldVoucher?.createdByUsername || curUser.username || 'admin',
        updatedBy: oldVoucher ? curUser.id : null,
        updatedByName: oldVoucher ? (curUser.fullName || curUser.name) : null,
        updatedAt: oldVoucher ? new Date().toISOString() : null,
        timestamp: new Date().toISOString()
    };

    if (currentViewedIndex !== -1) {
        jvData[currentViewedIndex] = voucherData;
        saveAllStoredJVs(jvData);
        if (window.logSystemAction) window.logSystemAction('تعديل قيد يومية', 'القيود والأستاذ العام', voucherData.id, `تعديل ${jvType} رقم ${voucherData.id} بقيمة ${fmtNum(voucherData.totalAmount)} — ${stmt}`);
        showToast(`✅ تم تحديث وترحيل القيد رقم ${voucherData.id} بنجاح!`, "success");
    } else {
        jvData.push(voucherData);
        saveAllStoredJVs(jvData);
        if (window.logSystemAction) window.logSystemAction('قيد يومية جديد', 'القيود والأستاذ العام', voucherData.id, `إصدار وترحيل ${jvType} رقم ${voucherData.id} بقيمة ${fmtNum(voucherData.totalAmount)} — ${stmt}`);
        const currentSeq = parseInt(localStorage.getItem('erp_jv_seq') || '0', 10);
        localStorage.setItem('erp_jv_seq', currentSeq + 1);
        showToast(`✅ تم حفظ وترحيل القيد رقم ${voucherData.id} إلى الأستاذ العام بنجاح!`, "success");
    }

    renderJournalArchiveTable();
    setTimeout(() => { resetVoucherSilent(); }, 900);
}

function printVoucher() {
    const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { fullName: 'مدير النظام' };
    const v = (currentViewedIndex !== -1 && jvData[currentViewedIndex]) ? jvData[currentViewedIndex] : {};
    const creator = v.createdByName || curUser.fullName || 'مدير النظام';
    const updater = v.updatedByName ? ` (عُدل بواسطة: ${v.updatedByName})` : '';

    const cEl = document.getElementById('jv-print-creator');
    const pEl = document.getElementById('jv-print-printer');
    const tEl = document.getElementById('jv-print-time');
    if (cEl) cEl.innerText = creator + updater;
    if (pEl) pEl.innerText = curUser.fullName || 'مدير النظام';
    if (tEl) tEl.innerText = new Date().toLocaleString('ar-EG');

    window.print();
}

function confirmDelete() {
    if (currentViewedIndex === -1) {
        showToast("لا يوجد قيد محفوظ مفتوح حالياً لحذفه", "warning");
        return;
    }
    if (confirm("هل أنت متأكد من حذف وإلغاء هذا القيد من دفتر اليومية والأستاذ العام؟")) {
        jvData = readAllStoredJVs();
        const deletedVoucher = jvData[currentViewedIndex];
        const deletedId = deletedVoucher ? deletedVoucher.id : '---';
        jvData.splice(currentViewedIndex, 1);
        saveAllStoredJVs(jvData);
        if (window.logSystemAction) window.logSystemAction('حذف قيد يومية', 'القيود والأستاذ العام', deletedId, `تم حذف قيد اليومية رقم ${deletedId}`);
        showToast("🗑️ تم حذف القيد وتحديث الأستاذ العام بنجاح", "success");
        renderJournalArchiveTable();
        resetVoucherSilent();
    }
}

window.shareJournalWhatsApp = function() {
    const vNo = document.getElementById('voucher-number').innerText;
    const dt = document.getElementById('hdr-date').value;
    const stmt = document.getElementById('hdr-statement').value || 'قيد يومية عام';
    const tot = document.getElementById('tot-debit').innerText;
    const msg = `⚖️ *إشعار سند قيد يومية محاسبي معتمد*\n`
        + `━━━━━━━━━━━━━━━━━━━━\n`
        + `• رقم القيد: *${vNo}*\n`
        + `• التاريخ: *${dt}*\n`
        + `• البيان: *${stmt}*\n`
        + `• إجمالي القيد المتوازن: *${tot} SDG*\n`
        + `━━━━━━━━━━━━━━━━━━━━\n`
        + `مرحل ومطابق في دفتر الأستاذ العام — نظام مقاييس ERP.`;
    window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
};

// ── Integrated Journal Register Archive Table ──
function renderJournalArchiveTable() {
    const tbody = document.getElementById('jv-archive-tbody');
    if (!tbody) return;
    jvData = readAllStoredJVs();

    const q = (document.getElementById('jv-archive-search')?.value || '').trim().toLowerCase();
    const dFrom = document.getElementById('jv-archive-from')?.value || '';
    const dTo = document.getElementById('jv-archive-to')?.value || '';

    const filtered = jvData.map((v, idx) => ({ ...v, _origIdx: idx })).filter(v => {
        const dt = (v.date || '').slice(0, 10);
        if (dFrom && dt && dt < dFrom) return false;
        if (dTo && dt && dt > dTo) return false;
        if (q) {
            const accNames = (v.lines || []).map(l => getAccountName(l.account) || l.accountName || '').join(' ');
            const hay = `${v.id} ${v.statement} ${v.ref || ''} ${v.jvType || ''} ${accNames}`.toLowerCase();
            if (!hay.includes(q)) return false;
        }
        return true;
    }).reverse();

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:28px; color:#94a3b8;">لا توجد قيود يومية محفوظة مطابقة للبحث — يمكنك استخدام أحد القوالب الذكية أعلاه أو إدخال قيد جديد.</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.slice(0, 100).map(v => `
        <tr style="cursor:pointer;" onclick="openSavedJournalByIndex(${v._origIdx})">
            <td style="font-family:monospace; font-weight:900; color:#c084fc;">${v.id}</td>
            <td>${(v.date || '').slice(0, 10)}</td>
            <td><span style="background:rgba(192,132,252,0.14); color:#e9d5ff; padding:2px 8px; border-radius:6px; font-size:0.74rem; font-weight:800;">${v.jvType || 'قيد يومية عام'}</span></td>
            <td style="color:#94a3b8;">${v.ref || '—'}</td>
            <td style="font-weight:700; color:#f8fafc;">${v.statement || '—'}</td>
            <td>${(v.lines || []).length} أطراف</td>
            <td style="font-weight:900; color:#34d399; font-family:monospace;">${fmtNum(v.totalAmount)}</td>
            <td style="font-size:0.8rem; color:#38bdf8;">👤 ${v.createdByName || 'مدير النظام'}</td>
            <td onclick="event.stopPropagation();">
                <div style="display:flex; gap:5px;">
                    <button onclick="openSavedJournalByIndex(${v._origIdx})" class="jv-top-btn jv-btn-sky" style="padding:3px 8px; font-size:0.73rem;">👁️ فتح</button>
                    <button onclick="openSavedJournalByIndex(${v._origIdx}); reverseCurrentJournal();" class="jv-top-btn jv-btn-amber" style="padding:3px 8px; font-size:0.73rem;">🔄 عكس</button>
                </div>
            </td>
        </tr>
    `).join('');
};

window.openSavedJournalByIndex = function(idx) {
    jvData = readAllStoredJVs();
    if (!jvData[idx]) return;
    currentViewedIndex = idx;
    renderVoucher(jvData[idx]);
    window.scrollTo({ top: 0, behavior: 'smooth' });
    showToast(`📂 تم فتح القيد رقم ${jvData[idx].id}`, 'success');
};

window.exportJournalRegisterExcel = function() {
    const list = readAllStoredJVs();
    let csv = '\uFEFFرقم القيد,التاريخ,نوع القيد,المرجع,البيان العام,إجمالي المدين/الدائن (SDG),المستخدم المنشئ\n';
    list.forEach(v => {
        csv += `"${v.id}","${v.date || ''}","${v.jvType || 'قيد يومية'}","${v.ref || ''}","${String(v.statement || '').replace(/"/g, '""')}","${v.totalAmount || 0}","${v.createdByName || 'مدير النظام'}"\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `Elsajana_Journal_Entries_${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
};

// ── Account Search Dropdown ──
function renderAccountDropdown(type, filter = '') {
    const listEl = document.getElementById(type + '-dropdown-list');
    if (!listEl) return;
    if (!ACCOUNTS || ACCOUNTS.length === 0) buildAccountsCatalog();

    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;

    let filtered = ACCOUNTS.filter(a => {
        if (!term) return true;
        const normalizedName = window.normalizeArabic ? window.normalizeArabic(a.name) : a.name.toLowerCase();
        return normalizedName.includes(normalizedTerm) || String(a.id).toLowerCase().includes(term);
    }).slice(0, 50);

    if (filtered.length === 0) {
        listEl.innerHTML = `<div style="padding: 12px; color: #94a3b8; text-align: center; font-size:0.82rem;">سيتم اعتماد (${filter}) كحساب مباشر عند الحفظ</div>`;
        return;
    }

    filtered.forEach(a => {
        const div = document.createElement('div');
        div.style.cssText = 'padding: 9px 14px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center;';
        div.onmouseover = () => div.style.background = 'rgba(192,132,252,0.18)';
        div.onmouseout = () => div.style.background = 'transparent';
        div.innerHTML = `
            <div>
                <div style="font-weight: 800; color: #f8fafc; font-size:0.86rem;">${a.name}</div>
                <div style="font-size: 0.72rem; color: #94a3b8;">[${a.id}]</div>
            </div>
            <span style="font-size:0.72rem; color:#c084fc; background:rgba(192,132,252,0.14); padding:2px 8px; border-radius:6px; font-weight:800;">${a.tag || 'حساب'}</span>
        `;
        div.onclick = (e) => {
            e.stopPropagation();
            selectAccount(type, a.id, a.name);
        };
        listEl.appendChild(div);
    });
}

window.showAccountDropdown = function(type) {
    const listEl = document.getElementById(type + '-dropdown-list');
    if (listEl) {
        renderAccountDropdown(type, document.getElementById(type + '-search').value);
        listEl.style.display = 'block';
    }
};

window.hideAccountDropdownDelayed = function(type) {
    setTimeout(() => {
        const listEl = document.getElementById(type + '-dropdown-list');
        if (listEl) listEl.style.display = 'none';
    }, 200);
};

window.filterAccountDropdown = function(type) {
    const val = document.getElementById(type + '-search').value;
    document.getElementById(type).value = val;
    renderAccountDropdown(type, val);
};

window.selectAccount = function(type, id, name) {
    document.getElementById(type).value = id;
    document.getElementById(type + '-search').value = name;
    const listEl = document.getElementById(type + '-dropdown-list');
    if (listEl) listEl.style.display = 'none';

    if (type.startsWith('acc-')) {
        const rowTr = document.getElementById(type).closest('tr');
        if (rowTr) {
            const debInp = rowTr.querySelector('.debit');
            if (debInp) debInp.focus();
        }
    }
};

if (!window.normalizeArabic) {
    window.normalizeArabic = function(text) {
        if (!text) return '';
        return text.toString().replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').toLowerCase();
    };
}

function getAccountName(id) {
    if (!id) return '';
    if (!ACCOUNTS || ACCOUNTS.length === 0) buildAccountsCatalog();
    const idStr = String(id).trim();
    const found = ACCOUNTS.find(a => String(a.id).toLowerCase() === idStr.toLowerCase() || String(a.name).toLowerCase() === idStr.toLowerCase());
    return found ? found.name : idStr;
}

// Global Keyboard Shortcuts for Accountants
window.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
        e.preventDefault();
        submitVoucher();
    } else if (e.altKey && (e.key === 'n' || e.key === 'N')) {
        e.preventDefault();
        addGridRow();
    } else if (e.altKey && (e.key === 'b' || e.key === 'B')) {
        e.preventDefault();
        window.autoBalanceJournal();
    } else if (e.altKey && (e.key === 'r' || e.key === 'R')) {
        e.preventDefault();
        window.reverseCurrentJournal();
    }
});
