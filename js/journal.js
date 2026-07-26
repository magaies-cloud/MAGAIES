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
    if (type === 'sales') target = `direct-sales.php?id=${ref}`;
    else if (type === 'purchase') target = `purchase.php?id=${ref}`;
    else if (type === 'jv') target = `journal-voucher.php?id=${ref}`;
    else if (type === 'receipt') target = `single-cash-payment.php?id=${ref}&type=receipt`;
    else if (type === 'payment') target = `single-cash-payment.php?id=${ref}&type=payment`;
    
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
    let accountCurrency = 'SDG';
    let accountExRate = 1;

    if (acctFilter !== 'ALL') {
        txns = txns.filter(t => t.account === acctFilter);
        
        let custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
        let sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        let banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
        let safes = (JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => !x.deleted);
        let exp = (JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => !x.deleted);
        
        let c = custs.find(x => x.id === acctFilter);
        if (c) {
            intrinsicBal = parseNum(c.openingDebit || 0) - parseNum(c.openingCredit || 0);
            accountCurrency = c.currency || 'SDG';
            accountExRate = parseNum(c.exRate || 1);
        } else {
            let s = sups.find(x => x.id === acctFilter);
            if (s) {
                intrinsicBal = parseNum(s.openingDebit || 0) - parseNum(s.openingCredit || 0);
                accountCurrency = s.currency || 'SDG';
                accountExRate = parseNum(s.exRate || 1);
            } else {
                let b = banks.find(x => x.id === acctFilter);
                if (b) {
                    intrinsicBal = parseNum(b.openingDebit || 0) - parseNum(b.openingCredit || 0);
                    accountCurrency = b.currency || 'SDG';
                    accountExRate = parseNum(b.exRate || 1);
                } else {
                    let sf = safes.find(x => x.id === acctFilter);
                    if (sf) {
                        intrinsicBal = parseNum(sf.openingDebit || 0) - parseNum(sf.openingCredit || 0);
                        accountCurrency = sf.currency || 'SDG';
                        accountExRate = parseNum(sf.exRate || 1);
                    } else {
                        let e = exp.find(x => (x.id || x.code) === acctFilter);
                        if (e) {
                            intrinsicBal = parseNum(e.openingDebit || 0) - parseNum(e.openingCredit || 0);
                            accountCurrency = e.currency || 'SDG';
                            accountExRate = parseNum(e.exRate || 1);
                        }
                    }
                }
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
            debit: openingDebit, credit: openingCredit, costCenter: '', 
            currency: accountCurrency, exchangeRate: accountExRate,
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

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filter-type').value = 'ALL';
    runReport();
});
