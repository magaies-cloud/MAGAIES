/* ═══════════════════════════════════════════════════════
   general-ledger.js — Smart General Ledger (SGL)
   ═══════════════════════════════════════════════════════ */

function fmtNum(n) {
    const val = parseFloat(n) || 0;
    return val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function parseNum(str) {
    return parseFloat(String(str || '0').replace(/,/g, '')) || 0;
}
function showToast(msg, type = 'success') {
    const c = document.getElementById('toast-container');
    if(!c) return;
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = msg;
    c.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

let ALL_ACCOUNTS = [{ id: 'ALL', name: 'جميع الحسابات', icon: '📊', type: 'عام' }];
let ALL_TXNS = []; // Cache to extract cost centers and currencies

function buildAccountsList() {
    ALL_ACCOUNTS = [{ id: 'ALL', name: 'جميع الحسابات', icon: '📊', type: 'عام' }];
    
    const accounts = (JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => !x.deleted);
    accounts.forEach(a => ALL_ACCOUNTS.push({ id: a.id, name: a.name, icon: '📝', type: 'عام' }));
    
    const safes = (JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => !x.deleted);
    safes.forEach(s => ALL_ACCOUNTS.push({ id: s.id, name: s.safeName || s.name, icon: '💰', type: 'صندوق' }));
    
    const banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
    banks.forEach(b => ALL_ACCOUNTS.push({ id: b.id, name: b.bankName || b.name, icon: '🏦', type: 'بنك' }));
    
    const custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    const sups  = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
    custs.forEach(c => ALL_ACCOUNTS.push({ id: c.id, name: c.name, icon: '👤', type: 'عميل' }));
    sups.forEach(s  => ALL_ACCOUNTS.push({ id: s.id, name: s.name, icon: '🏭', type: 'مورد' }));

    const accountSelect = document.getElementById('filter-account');
    if (accountSelect) {
        accountSelect.innerHTML = '<option value="ALL">جميع الحسابات</option>';
        ALL_ACCOUNTS.forEach(a => {
            if (a.id !== 'ALL') {
                accountSelect.innerHTML += `<option value="${a.id}">${a.icon} ${a.name} (${a.type})</option>`;
            }
        });
    }
}





/* ─── Collect Transactions & Meta (Currency, Cost Center) ─── */
function collectAllTransactions() {
    let txns = [];
    const timeNow = new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}); // Fallback time

    // 1. Single Receipts
    const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    receipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'receipt', typeLabel: 'سند قبض',
            account: v.toAccount || 'safe-001', counterAccount: v.fromAccount,
            statement: v.fromStatement || 'سند قبض نقدي',
            debit: parseNum(v.amountLocal), credit: 0, costCenter, currency
        });
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'receipt', typeLabel: 'سند قبض',
            account: v.fromAccount, counterAccount: v.toAccount || 'safe-001',
            statement: v.fromStatement || 'سند قبض نقدي',
            debit: 0, credit: parseNum(v.amountLocal), costCenter, currency
        });
    });

    // 2. Single Payments
    const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    payments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;

        txns.push({
            date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
            account: v.fromAccount || 'safe-001', counterAccount: v.toAccount,
            statement: v.toStatement || 'سند صرف نقدي',
            debit: 0, credit: parseNum(v.amountLocal), costCenter, currency
        });
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
            account: v.toAccount, counterAccount: v.fromAccount || 'safe-001',
            statement: v.toStatement || 'سند صرف نقدي',
            debit: parseNum(v.amountLocal), credit: 0, costCenter, currency
        });
    });

    // 3. Journal Vouchers
    const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
    jvs.forEach(v => {
        if (!v.lines) return;
        const time = v.time || timeNow;
        v.lines.forEach(line => {
            const costCenter = line.costCenter || v.costCenter || 'بدون مركز';
            const currency = line.currency || v.currency || 'SDG';
            txns.push({
                date: v.date, time: time, ref: v.id, type: 'jv', typeLabel: 'قيد يومي',
                account: line.account, counterAccount: '-',
                statement: v.statement || line.note || 'قيد يومي',
                debit: parseNum(line.debit), credit: parseNum(line.credit), costCenter, currency
            });
        });
    });

    // 4. Sales Invoices
    const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    sales.forEach(inv => {
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        if (!total) return;
        const costCenter = inv.costCenter || 'بدون مركز';
        const currency = inv.currency || 'SDG';
        const time = inv.time || timeNow;
        const refNo = inv.invoiceNo || inv.id;
        const customerAcct = inv.customerId || inv.customer;

        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'invoice', typeLabel: 'مبيعات',
            account: customerAcct, counterAccount: 'revenue-sales',
            statement: `مبيعات آجلة — ${inv.customerName || inv.customer || ''}`,
            debit: total, credit: 0, costCenter, currency
        });
        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'invoice', typeLabel: 'مبيعات',
            account: 'revenue-sales', counterAccount: customerAcct,
            statement: `إيرادات — ${inv.customerName || inv.customer || ''}`,
            debit: 0, credit: total, costCenter, currency
        });

        if (inv.paymentMethod !== 'credit') {
            const paymentAcct = inv.paymentAccountId || 'safe-001';
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'سداد فاتورة',
                account: paymentAcct, counterAccount: customerAcct,
                statement: 'سداد نقدي مع الفاتورة',
                debit: total, credit: 0, costCenter, currency
            });
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'سداد فاتورة',
                account: customerAcct, counterAccount: paymentAcct,
                statement: 'سداد نقدي مع الفاتورة',
                debit: 0, credit: total, costCenter, currency
            });
        }
    });

    // 5. Purchase Invoices
    const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    purchases.forEach(inv => {
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        if (!total) return;
        const costCenter = inv.costCenter || 'بدون مركز';
        const currency = inv.currency || 'SDG';
        const time = inv.time || timeNow;
        const refNo = inv.invoiceNo || inv.id;
        const supplierAcct = inv.supplierId || inv.supplier || inv.customer;

        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'purchase', typeLabel: 'مشتريات',
            account: 'exp-purchases', counterAccount: supplierAcct,
            statement: `مشتريات آجلة — ${inv.supplierName || inv.supplier || ''}`,
            debit: total, credit: 0, costCenter, currency
        });
        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'purchase', typeLabel: 'مشتريات',
            account: supplierAcct, counterAccount: 'exp-purchases',
            statement: `مستحقات مورد — ${inv.supplierName || inv.supplier || ''}`,
            debit: 0, credit: total, costCenter, currency
        });

        if (inv.paymentMethod !== 'credit') {
            const paymentAcct = inv.paymentAccountId || 'safe-001';
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: 'payment', typeLabel: 'سداد مشتريات',
                account: paymentAcct, counterAccount: supplierAcct,
                statement: 'سداد نقدي مع الفاتورة',
                debit: 0, credit: total, costCenter, currency
            });
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: 'payment', typeLabel: 'سداد مشتريات',
                account: supplierAcct, counterAccount: paymentAcct,
                statement: 'سداد نقدي مع الفاتورة',
                debit: total, credit: 0, costCenter, currency
            });
        }
    });

    return txns;
}

function extractFilters() {
    ALL_TXNS = collectAllTransactions();
    
    // Currencies
    const curSet = new Set();
    // Cost Centers
    const ccSet = new Set();

    ALL_TXNS.forEach(t => {
        if(t.currency) curSet.add(t.currency);
        if(t.costCenter) ccSet.add(t.costCenter);
    });

    const curSelect = document.getElementById('filter-currency');
    if(curSelect) {
        curSelect.innerHTML = '<option value="ALL">جميع العملات</option>';
        Array.from(curSet).forEach(c => {
            curSelect.innerHTML += `<option value="${c}">${c}</option>`;
        });
    }

    const ccSelect = document.getElementById('filter-costcenter');
    if(ccSelect) {
        ccSelect.innerHTML = '<option value="ALL">جميع مراكز التكلفة</option>';
        Array.from(ccSet).forEach(cc => {
            ccSelect.innerHTML += `<option value="${cc}">${cc}</option>`;
        });
    }
}

/* ─── Initialization ─── */
let choicesInstances = [];
document.addEventListener('DBReady', () => {
    buildAccountsList();
    extractFilters(); // Build filters dynamically
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    document.getElementById('filter-from').value = firstDay.toISOString().slice(0, 10);
    document.getElementById('filter-to').value = now.toISOString().slice(0, 10);

    if (typeof Choices !== 'undefined') {
        document.querySelectorAll('.choices-select').forEach(el => {
            choicesInstances.push(new Choices(el, {
                searchEnabled: true,
                itemSelectText: '',
                noResultsText: 'لا توجد نتائج',
                noChoicesText: 'لا توجد خيارات',
                searchPlaceholderValue: 'بحث...',
                shouldSort: false
            }));
        });
    }
});

function getAccountName(id) {
    if(id === 'ALL') return 'جميع الحسابات';
    if(id === '-') return '-';
    const found = ALL_ACCOUNTS.find(a => a.id === id);
    return found ? found.name : id;
}

/* ─── Drill Down & Attachments ─── */
function openDrillDown(type, ref) {
    let target = '';
    if (type === 'invoice') target = `sales-invoice.html?id=${ref}`;
    else if (type === 'purchase') target = `purchase.html?id=${ref}`;
    else if (type === 'jv') target = `journal-voucher.html?id=${ref}`;
    else if (type === 'receipt') target = `single-cash-receipt.html?id=${ref}`;
    else if (type === 'payment') target = `single-cash-payment.html?id=${ref}`;
    
    if(target) {
        window.open(target, '_blank');
    }
}

/* ─── Sharing Logic ─── */
function getReportSummaryText() {
    let accName = 'حساب عام';
    const accSelect = document.getElementById('account-search-input');
    if (accSelect && accSelect.value) {
        accName = accSelect.value.trim();
    }
    
    const debitEl = document.getElementById('sum-debit');
    const creditEl = document.getElementById('sum-credit');
    const balanceEl = document.getElementById('sum-balance');
    
    const debit = debitEl ? debitEl.innerText : '0.00';
    const credit = creditEl ? creditEl.innerText : '0.00';
    const balance = balanceEl ? balanceEl.innerText : '0.00';
    
    let text = `مرحباً،\nهذا ملخص كشف حساب: *${accName}*\n\n`;
    text += `إجمالي المدين: ${debit}\n`;
    text += `إجمالي الدائن: ${credit}\n`;
    text += `الرصيد النهائي: ${balance}\n\n`;
    text += `يرجى مراجعة التفاصيل والمرفقات إذا وجدت.`;
    
    return text;
}

function shareViaWhatsApp() {
    const text = encodeURIComponent(getReportSummaryText());
    // Opens WhatsApp Web/App allowing the user to select the contact to send the pre-filled text
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

function shareViaEmail() {
    let accName = 'حساب عام';
    const accSelect = document.getElementById('account-search-input');
    if (accSelect && accSelect.value) {
        accName = accSelect.value.trim();
    }
    
    const subject = encodeURIComponent(`كشف حساب - ${accName}`);
    const body = encodeURIComponent(getReportSummaryText());
    
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
}

function showAttachment(e, ref) {
    e.stopPropagation(); // prevent row click drill-down
    const m = document.getElementById('attach-modal');
    if(m) m.style.display = 'flex';
}

/* ─── Run Report ─── */
function runReport() {
    const acctFilter = document.getElementById('filter-account').value;
    const fromDate = document.getElementById('filter-from').value;
    const toDate = document.getElementById('filter-to').value;
    const typeFilter = document.getElementById('filter-type').value;
    const ccFilter = document.getElementById('filter-costcenter').value;
    const curFilter = document.getElementById('filter-currency').value;

    if (!fromDate || !toDate) {
        return showToast('يرجى تحديد الفترة الزمنية', 'warning');
    }

    let txns = [...ALL_TXNS];

    // Filter Account and Setup Opening Balance
    let intrinsicBal = 0;
    if (acctFilter !== 'ALL') {
        txns = txns.filter(t => t.account === acctFilter);
        
        let custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
        let sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        
        let c = custs.find(x => x.id === acctFilter);
        if (c) intrinsicBal = parseNum(c.openingBalance || c.initialBalance || 0);
        else {
            let s = sups.find(x => x.id === acctFilter);
            if (s) intrinsicBal = -parseNum(s.openingBalance || s.initialBalance || 0);
            else {
                let b = banks.find(x => x.id === acctFilter);
                if (b) intrinsicBal = parseNum(b.openingBalance || b.initialBalance || 0);
            }
        }
    }

    let runningBal = intrinsicBal;
    let priorTxns = txns.filter(t => t.date < fromDate);
    priorTxns.forEach(t => {
        runningBal += t.debit;
        runningBal -= t.credit;
    });

    let currentTxns = txns.filter(t => t.date >= fromDate && t.date <= toDate);

    if (typeFilter !== 'ALL') currentTxns = currentTxns.filter(t => t.type === typeFilter);
    if (ccFilter !== 'ALL') currentTxns = currentTxns.filter(t => t.costCenter === ccFilter);
    if (curFilter !== 'ALL') currentTxns = currentTxns.filter(t => t.currency === curFilter);

    currentTxns.sort((a, b) => {
        const da = a.date + ' ' + (a.time || '00:00');
        const db = b.date + ' ' + (b.time || '00:00');
        return da.localeCompare(db);
    });

    if (acctFilter !== 'ALL' && (runningBal !== 0 || currentTxns.length > 0)) {
        let openingDebit = runningBal > 0 ? runningBal : 0;
        let openingCredit = runningBal < 0 ? Math.abs(runningBal) : 0;
        currentTxns.unshift({
            date: fromDate, time: '', ref: 'OPENING', type: 'opening', typeLabel: 'افتتاحي',
            account: acctFilter, counterAccount: '-',
            statement: 'رصيد ما قبل الفترة',
            debit: openingDebit, credit: openingCredit, costCenter: '', currency: '',
            isOpening: true
        });
    }

    renderTable(currentTxns, acctFilter, fromDate, toDate);
}

function renderTable(txns, acctFilter, fromDate, toDate) {
    const tbody = document.getElementById('ledger-tbody');
    const summaryCards = document.getElementById('summary-cards');
    const auditStamp = document.getElementById('audit-stamp');
    const thead = document.querySelector('.ledger-table thead');

    // 1. Build Header Dynamically
    let ths = '';
    let activeColsCount = 0;
    colConfig.forEach(c => {
        if (!c.visible) return;
        activeColsCount++;
        let displayName = c.alias ? c.alias : c.name;
        ths += `<th style="width:${c.width};">${displayName}<div class="resize-handle"></div></th>`;
    });
    thead.innerHTML = `<tr>${ths}</tr>`;

    if (txns.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${activeColsCount}" class="empty-state"><span style="font-size:3rem;display:block;margin-bottom:15px;">🔍</span><div>لا توجد بيانات تطابق الفلاتر المحددة</div></td></tr>`;
        summaryCards.style.display = 'none';
        auditStamp.style.display = 'none';
        return;
    }

    let totalDebit = 0, totalCredit = 0, runningBalance = 0;
    let runningBalanceForeign = 0;
    let rows = '';

    txns.forEach((t, i) => {
        const exRate = t.exchangeRate || 1;
        const addNote = t.additionalNote || '-';
        const debF = t.debitForeign || (t.debit / exRate);
        const credF = t.creditForeign || (t.credit / exRate);

        if (!t.isOpening) {
            totalDebit += t.debit;
            totalCredit += t.credit;
        }
        runningBalance += t.debit - t.credit;
        runningBalanceForeign += debF - credF;

        const counterAccName = getAccountName(t.counterAccount) || '-';

        let rowHtml = `<tr title="${t.isOpening ? '' : 'انقر لعرض المستند (Drill-down)'}" ${t.isOpening ? '' : `onclick="openDrillDown('${t.type}', '${t.ref}')"`} style="${t.isOpening ? 'background:rgba(255,255,255,0.03);' : ''}">`;

        // Map values to IDs
        const colValues = {
            'idx': i + 1,
            'date': t.date,
            'counterAcc': counterAccName,
            'statement': t.statement,
            'note': addNote,
            'costCenter': t.costCenter || '-',
            'debit': t.debit > 0 ? t.debit : null,
            'credit': t.credit > 0 ? t.credit : null,
            'balance': Math.abs(runningBalance),
            'currency': t.currency || '-',
            'exRate': exRate,
            'debitF': debF > 0 ? debF : null,
            'creditF': credF > 0 ? credF : null,
            'balanceF': Math.abs(runningBalanceForeign)
        };

        colConfig.forEach(c => {
            if (!c.visible) return;
            
            let val = colValues[c.id];
            
            if (['debit', 'credit', 'balance', 'exRate', 'debitF', 'creditF', 'balanceF'].includes(c.id)) {
                if (val === null) {
                    val = '<span class="td-empty">—</span>';
                } else {
                    let formatted = fmtNumDec(val, c.dec);
                    val = `<div class="num-cell-wrapper ${currentNumFont}" onclick="toggleReviewMode(event)" oncontextmenu="showContextMenu(event, '${t.type}', '${t.ref}')">${formatted}</div>`;
                }
            }

            let style = [];
            if (c.bg) style.push(`background-color:${c.bg} !important`);
            if (c.color) style.push(`color:${c.color} !important`);
            if (c.bold || t.isOpening) style.push(`font-weight:bold`);
            if (c.size) style.push(`font-size:${c.size}`);
            
            if (['statement', 'note'].includes(c.id)) {
                style.push('max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;');
            }
            if (['debit', 'debitF', 'credit', 'creditF'].includes(c.id) && (typeof val === 'string' && val.indexOf('<div') === -1)) style.push('font-family:monospace');

            rowHtml += `<td style="${style.join('; ')}" title="${(typeof val === 'string' && val.includes('<')) ? '' : val}">${val}</td>`;
        });

        rowHtml += `</tr>`;
        rows += rowHtml;
    });

    tbody.innerHTML = rows;

    document.getElementById('sum-debit').textContent = fmtNum(totalDebit);
    document.getElementById('sum-credit').textContent = fmtNum(totalCredit);
    document.getElementById('sum-balance').textContent = fmtNum(Math.abs(runningBalance)) + (runningBalance >= 0 ? ' (مدين)' : ' (دائن)');
    summaryCards.style.display = 'grid';
    auditStamp.style.display = 'flex';

    document.getElementById('print-header').style.display = 'block';
    const accName = acctFilter === 'ALL' ? 'جميع الحسابات المتأثرة' : getAccountName(acctFilter);
    document.getElementById('print-meta').innerHTML = `
        <span>الحساب: <strong>${accName}</strong></span>
        <span>الفترة: من <strong>${fromDate}</strong> إلى <strong>${toDate}</strong></span>
        <span>تاريخ الطباعة: <strong>${new Date().toLocaleDateString('ar-SD')}</strong></span>
    `;

    showToast(`✅ اكتمل التحليل: ${txns.length} حركة مسجلة`, 'success');
}

/* ─── Export to CSV ─── */

function previewGL() {
    const tableWrap = document.querySelector('.ledger-table-wrap');
    if (!tableWrap) {
        return showToast('الرجاء تشغيل التقرير أولاً للمعاينة', 'warning');
    }
    
    const tbody = document.getElementById('ledger-tbody');
    if (!tbody || tbody.querySelector('.empty-state')) {
        return showToast('لا توجد بيانات للمعاينة، شغّل التقرير أولاً', 'warning');
    }
    
    const tableHtml = document.querySelector('.ledger-table').outerHTML;
    
    // Robust settings retrieval (handles tenant-prefixed keys + DB_STORE proxy)
    let cfg = {};
    try {
        cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
    } catch(e) {}
    // Fallback: read from DB_STORE proxy if available
    if ((!cfg || !cfg.companyName) && window.DB_STORE && window.getTenantKey) {
        try {
            const tk = window.getTenantKey('erp_settings_v1');
            if (window.DB_STORE[tk]) cfg = JSON.parse(window.DB_STORE[tk]);
        } catch(e) {}
    }
    const companyName = cfg.companyName || '\u0645\u0624\u0633\u0633\u0629 \u0627\u0644\u0633\u062c\u0627\u0646\u0629 \u0644\u0644\u062a\u062c\u0627\u0631\u0629';
    const companyPhone = cfg.phone || '';
    const companyAddress = cfg.address || '';
    const companyReg = cfg.regNumber || '';
    const companyVat = cfg.vat || '';
    
    // Logo: check logoBase64 in settings, or look for the header logo already on page
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
                    ${companyReg ? `<div>السجل التجاري: ${companyReg}</div>` : ''}
                </div>
            </div>
            <div>
                ${logoImg}
            </div>
        </div>
    `;

    const win = window.open('', '_blank', 'width=1000,height=700');
    win.document.write(`
        <html dir="rtl" lang="ar">
        <head>
            <title>معاينة الأستاذ العام</title>
            <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Tajawal', sans-serif; padding: 30px; background: #fff; color: #000; direction: rtl; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9rem; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
                th { background: #f3f4f6; color: #333; font-weight: bold; }
                .td-debit { color: #059669; font-weight: bold; }
                .td-credit { color: #dc2626; font-weight: bold; }
                .td-balance-dr { color: #059669; font-weight: bold; }
                .td-balance-cr { color: #dc2626; font-weight: bold; }
                h2, h3 { text-align: center; margin: 5px 0; }
                #print-meta { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; font-weight: bold; }
                @media print { .no-print { display: none !important; } }
            </style>
        </head>
        <body>
            ${companyHeaderHtml}
            <h2 style="text-align:center; margin: 10px 0 5px;">دفتر الأستاذ العام</h2>
            <div id="print-meta" style="display:flex; justify-content:space-between; border-bottom:2px solid #333; padding-bottom:10px; margin-bottom:15px; font-weight:bold;">
                <span>الحساب: <strong>${document.getElementById('filter-account').options[document.getElementById('filter-account').selectedIndex]?.text || 'جميع الحسابات'}</strong></span>
                <span>الفترة: من <strong>${document.getElementById('filter-from').value}</strong> إلى <strong>${document.getElementById('filter-to').value}</strong></span>
                <span>تاريخ الطباعة: <strong>${new Date().toLocaleDateString('ar-SD')}</strong></span>
            </div>
            ${tableHtml}
            <div style="margin-top: 30px; text-align: center;" class="no-print">
                <button onclick="window.print()" style="padding: 10px 20px; background: #38bdf8; border: none; border-radius: 5px; color: #fff; cursor: pointer; font-family: inherit; font-weight: bold; margin: 10px;">🖨️ طباعة</button>
                <button onclick="window.close()" style="padding: 10px 20px; background: #94a3b8; border: none; border-radius: 5px; color: #fff; cursor: pointer; font-family: inherit; font-weight: bold; margin: 10px;">❌ إغلاق</button>
            </div>
        </body>
        </html>
    `);
    win.document.close();
}

function exportCSV() {
    const rows = document.querySelectorAll('#ledger-tbody tr:not(.empty-state)');
    if (rows.length === 0) return showToast('لا توجد بيانات للتصدير', 'warning');

    let csv = '\uFEFF';
    let visibleCols = colConfig.filter(c => c.visible);
    let headers = visibleCols.map(c => `"${c.alias || c.name}"`).join(',');
    csv += headers + '\n';
    
    rows.forEach(r => {
        const cells = Array.from(r.querySelectorAll('td'));
        if (cells.length < visibleCols.length) return;
        let rowCsv = cells.map(cell => `"${cell.innerText.replace(/"/g, '""').replace(/\n/g, ' ')}"`).join(',');
        csv += rowCsv + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `SGL-Report-${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showToast('📥 تم التصدير بنجاح', 'success');
}

/* ─── Dynamic Column Configuration & Sidebar ─── */
const defaultColConfig = [
    { id: 'idx', name: '#', alias: '', visible: true, width: '50px', bg: '', color: '#64748b', bold: false, size: '0.8rem', dec: 0 },
    { id: 'date', name: 'التاريخ', alias: '', visible: true, width: 'auto', bg: '', color: '', bold: false, size: '0.85rem', dec: 0 },
    { id: 'counterAcc', name: 'الحساب المقابل', alias: '', visible: true, width: 'auto', bg: '', color: '#e2e8f0', bold: false, size: '0.85rem', dec: 0 },
    { id: 'statement', name: 'البيان', alias: '', visible: true, width: '150px', bg: '', color: '#e2e8f0', bold: false, size: '0.85rem', dec: 0 },
    { id: 'note', name: 'ملحوظة إضافية', alias: '', visible: true, width: '150px', bg: '', color: '#94a3b8', bold: false, size: '0.8rem', dec: 0 },
    { id: 'costCenter', name: 'مركز التكلفة', alias: '', visible: true, width: 'auto', bg: '', color: '#94a3b8', bold: false, size: '0.8rem', dec: 0 },
    { id: 'debit', name: 'مدين محلي', alias: '', visible: true, width: 'auto', bg: '', color: '#34d399', bold: true, size: '0.9rem', dec: 2 },
    { id: 'credit', name: 'دائن محلي', alias: '', visible: true, width: 'auto', bg: '', color: '#f87171', bold: true, size: '0.9rem', dec: 2 },
    { id: 'balance', name: 'الرصيد محلي', alias: '', visible: true, width: 'auto', bg: '', color: '#c084fc', bold: true, size: '0.9rem', dec: 2 },
    { id: 'currency', name: 'العملة', alias: '', visible: true, width: 'auto', bg: '', color: '#c084fc', bold: false, size: '0.85rem', dec: 0 },
    { id: 'exRate', name: 'سعر الصرف', alias: '', visible: true, width: 'auto', bg: '', color: '#94a3b8', bold: false, size: '0.8rem', dec: 2 },
    { id: 'debitF', name: 'مدين أجنبي', alias: '', visible: true, width: 'auto', bg: '', color: '#34d399', bold: true, size: '0.9rem', dec: 2 },
    { id: 'creditF', name: 'دائن أجنبي', alias: '', visible: true, width: 'auto', bg: '', color: '#f87171', bold: true, size: '0.9rem', dec: 2 },
    { id: 'balanceF', name: 'رصيد أجنبي', alias: '', visible: true, width: 'auto', bg: '', color: '#c084fc', bold: true, size: '0.9rem', dec: 2 }
];

let colConfig = [];
function loadColumnConfig() {
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        if (settingsStr) {
            let settings = JSON.parse(settingsStr);
            if (settings.sgl_col_config_v2) {
                colConfig = settings.sgl_col_config_v2;
                return;
            }
        }
        
        // Fallback for old local-only data
        const oldSaved = localStorage.getItem('sgl_col_config_v2');
        if (oldSaved) {
            colConfig = JSON.parse(oldSaved);
            saveColumnConfig(); // migrate to erp_settings_v1
            return;
        }
        
        colConfig = JSON.parse(JSON.stringify(defaultColConfig));
    } catch(e) { colConfig = JSON.parse(JSON.stringify(defaultColConfig)); }
}
loadColumnConfig();

// Table Theme Management
let currentTableTheme = '';
function loadTableTheme() {
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        if (settingsStr) {
            let settings = JSON.parse(settingsStr);
            if (settings.sgl_table_theme !== undefined) {
                currentTableTheme = settings.sgl_table_theme;
                applyTableTheme(currentTableTheme);
                updateCrystalButtons(currentTableTheme);
            }
        }
    } catch(e) {}
}

function updateCrystalButtons(themeClass) {
    const btns = document.querySelectorAll('.crystal-theme-container .crystal-btn');
    if (btns.length > 0) {
        btns.forEach(b => b.classList.remove('active'));
        let activeBtn = document.getElementById('btn-theme-' + (themeClass || ''));
        if (activeBtn) activeBtn.classList.add('active');
    }
}

function applyTableTheme(themeClass) {
    const table = document.querySelector('.ledger-table');
    if (!table) return;
    // Remove all existing theme classes
    table.classList.forEach(cls => {
        if (cls.startsWith('table-theme-')) {
            table.classList.remove(cls);
        }
    });
    // Add new theme
    if (themeClass) {
        table.classList.add(themeClass);
    }
}

window.changeTableTheme = function(themeClass) {
    currentTableTheme = themeClass;
    applyTableTheme(themeClass);
    updateCrystalButtons(themeClass);
    
    // Save to settings
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        let settings = settingsStr ? JSON.parse(settingsStr) : {};
        settings.sgl_table_theme = themeClass;
        localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
    } catch(e) { console.error("Failed to save theme", e); }
};

let currentNumFont = 'num-font-roboto';

function loadNumberFont() {
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        if (settingsStr) {
            let settings = JSON.parse(settingsStr);
            if (settings.sgl_num_font) {
                currentNumFont = settings.sgl_num_font;
            }
        }
    } catch(e) {}
    
    document.querySelectorAll('.font-chip').forEach(el => el.classList.remove('active'));
    let btn = document.getElementById('btn-font-' + currentNumFont);
    if (btn) btn.classList.add('active');
}

window.changeNumberFont = function(fontClass) {
    currentNumFont = fontClass;
    document.querySelectorAll('.num-cell-wrapper').forEach(el => {
        el.classList.remove('num-font-roboto', 'num-font-inter', 'num-font-jetbrains', 'num-font-poppins');
        el.classList.add(fontClass);
    });
    
    document.querySelectorAll('.font-chip').forEach(el => el.classList.remove('active'));
    let btn = document.getElementById('btn-font-' + fontClass);
    if (btn) btn.classList.add('active');

    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        let settings = settingsStr ? JSON.parse(settingsStr) : {};
        settings.sgl_num_font = fontClass;
        localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
    } catch(e) {}
};

window.toggleReviewMode = function(e) {
    e.stopPropagation();
    e.currentTarget.classList.toggle('reviewed-cell');
};

window.showContextMenu = function(e, type, ref) {
    if (type === 'opening') return; // no drill down for opening balances
    e.preventDefault();
    e.stopPropagation();
    
    const menu = document.getElementById('drilldown-context-menu');
    if (!menu) return;
    
    menu.style.top = e.clientY + 'px';
    menu.style.left = e.clientX + 'px';
    menu.classList.add('show');
    
    const btn = document.getElementById('ctx-drilldown-btn');
    btn.onclick = function() {
        menu.classList.remove('show');
        openDrillDown(type, ref);
    };
    
    document.addEventListener('click', closeContextMenu);
};

function closeContextMenu() {
    const menu = document.getElementById('drilldown-context-menu');
    if (menu) menu.classList.remove('show');
    document.removeEventListener('click', closeContextMenu);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        applyConfigToHeaders();
        loadTableTheme();
        loadNumberFont();
    });
} else {
    setTimeout(() => {
        applyConfigToHeaders();
        loadTableTheme();
        loadNumberFont();
    }, 100);
}

function saveColumnConfig() {
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        let settings = settingsStr ? JSON.parse(settingsStr) : {};
        settings.sgl_col_config_v2 = colConfig;
        
        // By setting it in erp_settings_v1, app.js will automatically push it to the SaaS DB!
        localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
    } catch(e) {
        console.error("Failed to save col config to DB", e);
    }
}

function toggleSettingsSidebar() {
    const sb = document.getElementById('settings-sidebar');
    const ov = document.getElementById('settings-overlay');
    if (sb.classList.contains('active')) {
        sb.classList.remove('active');
        ov.classList.remove('active');
    } else {
        // Load position and size if exists
        try {
            let settingsStr = localStorage.getItem('erp_settings_v1');
            if (settingsStr) {
                let settings = JSON.parse(settingsStr);
                if (settings.sgl_sidebar_ui) {
                    if (settings.sgl_sidebar_ui.left) sb.style.left = settings.sgl_sidebar_ui.left;
                    if (settings.sgl_sidebar_ui.top) sb.style.top = settings.sgl_sidebar_ui.top;
                    if (settings.sgl_sidebar_ui.width) sb.style.width = settings.sgl_sidebar_ui.width;
                    if (settings.sgl_sidebar_ui.height) sb.style.height = settings.sgl_sidebar_ui.height;
                }
            }
        } catch(e) {}
        
        renderSidebarConfig();
        sb.classList.add('active');
        ov.classList.add('active');
    }
}

function renderSidebarConfig() {
    const list = document.getElementById('columns-config-list');
    list.innerHTML = '';
    colConfig.forEach((col, index) => {
        const li = document.createElement('li');
        li.className = 'col-config-item';
        li.draggable = true;
        li.dataset.index = index;
        
        li.innerHTML = `
            <div class="col-config-header">
                <div class="col-title-wrap">
                    <span class="col-drag-handle">☰</span>
                    <span class="col-title">${col.name}</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button class="col-settings-btn" onclick="toggleColSettings(${index})">تخصيص</button>
                    <div class="col-toggle ${col.visible ? 'on' : ''}" onclick="toggleColVisibility(${index}, this)"></div>
                </div>
            </div>
            <div class="col-settings-panel" id="col-settings-${index}">
                <div class="cfg-row">
                    <span>مسمى بديل:</span>
                    <input type="text" class="cfg-input" value="${col.alias}" onchange="updateColField(${index}, 'alias', this.value)" placeholder="مثلاً: ${col.name}">
                </div>
                <div class="cfg-row">
                    <span>الخط (حجم/سمك):</span>
                    <div class="cfg-btns">
                        <button class="cfg-btn" onclick="adjustFontSize(${index}, -1)">A-</button>
                        <button class="cfg-btn" onclick="adjustFontSize(${index}, 1)">A+</button>
                        <button class="cfg-btn ${col.bold ? 'active' : ''}" onclick="toggleColBold(${index}, this)">B</button>
                    </div>
                </div>
                <div class="cfg-row">
                    <span>الألوان (نص/خلفية):</span>
                    <div style="display:flex; gap:5px;">
                        <input type="color" class="cfg-input" value="${col.color || '#ffffff'}" onchange="updateColField(${index}, 'color', this.value)" title="لون النص">
                        <input type="color" class="cfg-input" value="${col.bg || '#000000'}" onchange="updateColField(${index}, 'bg', this.value)" title="لون الخلفية">
                    </div>
                </div>
                ${['debit', 'credit', 'balance', 'exRate', 'debitF', 'creditF', 'balanceF'].includes(col.id) ? `
                <div class="cfg-row">
                    <span>الفواصل العشرية:</span>
                    <input type="number" class="cfg-input" value="${col.dec}" min="0" max="6" style="width:60px;" onchange="updateColField(${index}, 'dec', parseInt(this.value))">
                </div>
                ` : ''}
            </div>
        `;
        
        li.addEventListener('dragstart', handleDragStart);
        li.addEventListener('dragover', handleDragOver);
        li.addEventListener('drop', handleDrop);
        li.addEventListener('dragenter', handleDragEnter);
        li.addEventListener('dragleave', handleDragLeave);
        
        list.appendChild(li);
    });
}

function toggleColSettings(index) {
    const p = document.getElementById('col-settings-' + index);
    p.classList.toggle('open');
}
function toggleColVisibility(index, el) {
    colConfig[index].visible = !colConfig[index].visible;
    el.classList.toggle('on');
}
function updateColField(index, field, val) {
    colConfig[index][field] = val;
}
function adjustFontSize(index, dir) {
    let size = parseFloat(colConfig[index].size) || 0.9;
    size += dir * 0.05;
    colConfig[index].size = size + 'rem';
}
function toggleColBold(index, el) {
    colConfig[index].bold = !colConfig[index].bold;
    el.classList.toggle('active');
}

let dragSrcEl = null;
function handleDragStart(e) {
    dragSrcEl = this;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
    this.classList.add('dragging');
}
function handleDragOver(e) {
    if (e.preventDefault) e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}
function handleDragEnter(e) { this.classList.add('over'); }
function handleDragLeave(e) { this.classList.remove('over'); }
function handleDrop(e) {
    if (e.stopPropagation) e.stopPropagation();
    if (dragSrcEl !== this) {
        let fromIdx = parseInt(dragSrcEl.dataset.index);
        let toIdx = parseInt(this.dataset.index);
        
        let moved = colConfig.splice(fromIdx, 1)[0];
        colConfig.splice(toIdx, 0, moved);
        
        renderSidebarConfig();
    }
    return false;
}

function applyAndSaveConfig() {
    saveColumnConfig();
    runReport();
    toggleSettingsSidebar();
    showToast('💾 تم حفظ التخصيصات وتطبيقها بنجاح', 'success');
}
function resetColumnConfig() {
    colConfig = JSON.parse(JSON.stringify(defaultColConfig));
    saveColumnConfig();
    renderSidebarConfig();
    runReport();
    showToast('🔄 تم استعادة الوضع الافتراضي', 'success');
}
function fmtNumDec(n, decimals) {
    const val = parseFloat(n) || 0;
    return val.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

function applyConfigToHeaders() {
    const thead = document.querySelector('#ledger-thead');
    if (!thead) return;
    let ths = '';
    colConfig.forEach(c => {
        if (!c.visible) return;
        let displayName = c.alias ? c.alias : c.name;
        ths += `<th style="width:${c.width};">${displayName}<div class="resize-handle"></div></th>`;
    });
    thead.innerHTML = `<tr>${ths}</tr>`;
}

/* ─── Column Resizing Logic ─── */
let isResizing = false;
let currentTh = null;
let startX = 0;
let startWidth = 0;
let resizeColIndex = -1;

document.addEventListener('mousedown', function(e) {
    if (e.target.classList.contains('resize-handle')) {
        isResizing = true;
        currentTh = e.target.parentElement;
        startX = e.pageX;
        startWidth = currentTh.offsetWidth;
        
        let thName = currentTh.textContent.trim();
        resizeColIndex = colConfig.findIndex(c => c.visible && (c.alias || c.name) === thName);
        e.preventDefault(); 
    }
});

document.addEventListener('mousemove', function(e) {
    if (!isResizing) return;
    let diff = startX - e.pageX; // RTL: drag left increases width
    let newWidth = startWidth + diff;
    if (newWidth > 30) {
        currentTh.style.width = newWidth + 'px';
    }
});

document.addEventListener('mouseup', function(e) {
    if (isResizing) {
        isResizing = false;
        if (resizeColIndex >= 0 && resizeColIndex < colConfig.length) {
            colConfig[resizeColIndex].width = currentTh.style.width;
            saveColumnConfig();
        }
        currentTh = null;
    }
});
