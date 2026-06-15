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

let ALL_ACCOUNTS = [];
let ALL_TXNS = []; // Cache to extract cost centers and currencies

function buildAccountsList() {
    ALL_ACCOUNTS = [
        { id: 'ALL',           name: 'جميع الحسابات',       icon: '📊', type: 'عام' },
        { id: 'safe-001',      name: 'الخزينة الرئيسية',    icon: '💰', type: 'صندوق' },
        { id: 'bank-001',      name: 'بنك (مثال 1)',          icon: '🏦', type: 'بنك' },
        { id: 'exp-rent',      name: 'مصروفات إيجار',        icon: '🏠', type: 'مصروف' },
        { id: 'exp-salary',    name: 'مصروفات رواتب',        icon: '👷', type: 'مصروف' },
        { id: 'revenue-sales', name: 'إيرادات المبيعات',     icon: '📈', type: 'إيراد' },
        { id: 'exp-purchases', name: 'مصروفات المشتريات',    icon: '📦', type: 'مصروف' },
    ];
    const custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
    const sups  = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
    custs.forEach(c => ALL_ACCOUNTS.push({ id: c.id, name: c.name, icon: '👤', type: 'عميل' }));
    sups.forEach(s  => ALL_ACCOUNTS.push({ id: s.id, name: s.name, icon: '🏭', type: 'مورد' }));
}

let acActiveIdx = -1;

function onAccountSearch() {
    const q = document.getElementById('account-search-input').value.trim();
    const list = document.getElementById('autocomplete-list');
    const clearBtn = document.getElementById('search-clear-btn');
    const chip = document.getElementById('selected-chip');
    acActiveIdx = -1;

    clearBtn.style.display = q ? 'block' : 'none';
    chip.style.display = 'none';
    document.getElementById('filter-account').value = 'ALL';

    if (!q) { list.style.display = 'none'; return; }

    const matches = ALL_ACCOUNTS.filter(a =>
        a.id !== 'ALL' && a.name.includes(q)
    ).slice(0, 10);

    if (matches.length === 0) {
        list.innerHTML = `<li style="color:#64748b; cursor:default;">لا توجد نتائج</li>`;
    } else {
        list.innerHTML = matches.map((a, i) => `
            <li onclick="selectAccount('${a.id}','${a.name}','${a.icon}')">
                <span>${a.icon}</span>
                <span>${a.name}</span>
                <span class="ac-badge">${a.type}</span>
            </li>`).join('');
    }
    list.style.display = 'block';
}

function onAccountSearchKey(e) {
    const list = document.getElementById('autocomplete-list');
    const items = list.querySelectorAll('li:not([style*="cursor:default"])');
    if (!items.length) return;
    if (e.key === 'ArrowDown') { acActiveIdx = Math.min(acActiveIdx + 1, items.length - 1); }
    else if (e.key === 'ArrowUp') { acActiveIdx = Math.max(acActiveIdx - 1, 0); }
    else if (e.key === 'Enter' && acActiveIdx >= 0) { items[acActiveIdx].click(); return; }
    else if (e.key === 'Escape') { list.style.display = 'none'; return; }
    items.forEach((li, i) => li.classList.toggle('active', i === acActiveIdx));
}

function selectAccount(id, name, icon) {
    document.getElementById('filter-account').value = id;
    document.getElementById('account-search-input').value = '';
    document.getElementById('autocomplete-list').style.display = 'none';
    document.getElementById('search-clear-btn').style.display = 'none';

    const chip = document.getElementById('selected-chip');
    document.getElementById('chip-icon').textContent = icon;
    document.getElementById('chip-name').textContent = name;
    chip.style.display = 'flex';
}

function clearAccountSearch() {
    document.getElementById('filter-account').value = 'ALL';
    document.getElementById('account-search-input').value = '';
    document.getElementById('autocomplete-list').style.display = 'none';
    document.getElementById('search-clear-btn').style.display = 'none';
    document.getElementById('selected-chip').style.display = 'none';
}

document.addEventListener('click', e => {
    if (!e.target.closest('.search-wrap') && e.target.id !== 'search-clear-btn') {
        const list = document.getElementById('autocomplete-list');
        if (list) list.style.display = 'none';
    }
});

if(document.getElementById('search-clear-btn')) {
    document.getElementById('search-clear-btn').addEventListener('click', clearAccountSearch);
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
document.addEventListener('DBReady', () => {
    buildAccountsList();
    extractFilters(); // Build filters dynamically
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    document.getElementById('filter-from').value = firstDay.toISOString().slice(0, 10);
    document.getElementById('filter-to').value = now.toISOString().slice(0, 10);
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
        
        let custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        let sups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
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

    if (txns.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="empty-state"><span style="font-size:3rem;display:block;margin-bottom:15px;">🔍</span><div>لا توجد بيانات تطابق الفلاتر المحددة</div></td></tr>`;
        summaryCards.style.display = 'none';
        auditStamp.style.display = 'none';
        return;
    }

    let totalDebit = 0, totalCredit = 0, runningBalance = 0;
    let rows = '';

    txns.forEach((t, i) => {
        if (!t.isOpening) {
            totalDebit += t.debit;
            totalCredit += t.credit;
        }
        runningBalance += t.debit - t.credit;

        const balClass = runningBalance >= 0 ? 'td-balance-dr' : 'td-balance-cr';
        const typeBadgeColor = {
            'opening': '#64748b',
            'receipt': '#34d399', 'payment': '#f87171',
            'jv': '#c084fc', 'invoice': '#38bdf8', 'purchase': '#fb923c'
        }[t.type] || '#94a3b8';

        let refHtml = t.isOpening ? t.ref : `
            <div style="display:flex; align-items:center; gap:8px;">
                <span>${t.ref}</span>
                <button onclick="event.stopPropagation(); openDrillDown('${t.type}', '${t.ref}')" style="background:rgba(56,189,248,0.1); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); border-radius:6px; padding:3px 8px; cursor:pointer; font-size:0.75rem; font-weight:bold; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#38bdf8'; this.style.color='#fff';" onmouseout="this.style.background='rgba(56,189,248,0.1)'; this.style.color='#38bdf8';">
                    🔗 عرض الأصل
                </button>
            </div>
        `;

        rows += `<tr title="${t.isOpening ? '' : 'انقر لعرض المستند (Drill-down)'}" ${t.isOpening ? '' : `onclick="openDrillDown('${t.type}', '${t.ref}')"`} style="${t.isOpening ? 'background:rgba(255,255,255,0.03);' : ''}">
            <td style="color:#64748b; font-size:0.8rem;">${i + 1}</td>
            <td class="td-date">${t.date} <br><span style="font-size:0.7rem;">${t.time||''}</span></td>
            <td class="td-ref" style="${t.isOpening ? 'cursor:default; text-decoration:none;' : ''}">${refHtml}</td>
            <td><span style="background:${typeBadgeColor}20; color:${typeBadgeColor}; padding:3px 8px; border-radius:6px; font-size:0.75rem;">${t.typeLabel}</span></td>
            <td style="color:#e2e8f0; font-size:0.85rem; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; ${t.isOpening ? 'font-weight:bold;' : ''}" title="${t.statement}">${t.statement}</td>
            <td class="td-debit">${t.debit > 0 ? fmtNum(t.debit) : '<span class="td-empty">—</span>'}</td>
            <td class="td-credit">${t.credit > 0 ? fmtNum(t.credit) : '<span class="td-empty">—</span>'}</td>
            <td class="${balClass}">${fmtNum(Math.abs(runningBalance))}</td>
            <td style="text-align:center;">${t.isOpening ? '' : `<button class="btn-attach" onclick="showAttachment(event, '${t.ref}')">📎 مرفق</button>`}</td>
        </tr>`;
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
    const tableWrap = document.getElementById('gl-table-wrap');
    if (!tableWrap || tableWrap.style.display === 'none') {
        return showToast('الرجاء تشغيل التقرير أولاً للمعاينة', 'warning');
    }
    
    const printHeader = document.getElementById('print-header').innerHTML;
    const tableHtml = document.querySelector('.gl-table').outerHTML;
    
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
            ${printHeader}
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
    csv += 'التاريخ,الوقت,رقم المرجع,النوع,البيان,مدين,دائن,الرصيد\n';
    rows.forEach(r => {
        const cells = Array.from(r.querySelectorAll('td'));
        // Ignore # column and Attachments column
        const dt = cells[1].innerText.replace(/\n/g, ' ');
        const ref = cells[2].innerText;
        const type = cells[3].innerText;
        const state = cells[4].innerText;
        const deb = cells[5].innerText.replace(/—/g, '0');
        const cred = cells[6].innerText.replace(/—/g, '0');
        const bal = cells[7].innerText;
        
        csv += `"${dt}","${dt}","${ref}","${type}","${state}","${deb}","${cred}","${bal}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `SGL-Report-${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showToast('📥 تم التصدير بنجاح', 'success');
}
