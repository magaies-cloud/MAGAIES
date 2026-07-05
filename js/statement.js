/* ═══════════════════════════════════════════════════════
   statement.js — Partner Statement of Account (كشف حساب)
   Includes Deep Drill-down feature.
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

let ALL_PARTNERS = [];

function loadPartners() {
    ALL_PARTNERS = [];
    const custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    const sups  = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
    const banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    const safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
    const expenses = JSON.parse(localStorage.getItem('erp_expenses_v1')) || [];
    const ccs = JSON.parse(localStorage.getItem('erp_cost_centers_v1')) || [];
    const employees = JSON.parse(localStorage.getItem('erp_employees_v1')) || [];
    const warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
    
    custs.forEach(c => ALL_PARTNERS.push({ id: c.id, name: c.name, type: 'عميل', icon: '👤' }));
    sups.forEach(s  => ALL_PARTNERS.push({ id: s.id, name: s.name, type: 'مورد', icon: '🏭' }));
    banks.forEach(b => ALL_PARTNERS.push({ id: b.id, name: b.name, type: 'بنك', icon: '🏦' }));
    safes.forEach(s => ALL_PARTNERS.push({ id: s.id, name: s.name, type: 'خزينة', icon: '💰' }));
    expenses.forEach(e => ALL_PARTNERS.push({ id: e.id, name: e.name, type: 'مصروف', icon: '📉' }));
    ccs.forEach(c => ALL_PARTNERS.push({ id: c.id, name: c.name, type: 'مركز تكلفة', icon: '📊' }));
    employees.forEach(e => ALL_PARTNERS.push({ id: e.id, name: e.name, type: 'موظف', icon: '👨‍💼' }));
    warehouses.forEach(w => ALL_PARTNERS.push({ id: w.id, name: w.name, type: 'مخزن', icon: '🏢' }));

    const sel = document.getElementById('filter-partner');
    if (sel) {
        let html = '<option value="">-- اختر الحساب --</option>';
        if (custs.length) html += `<optgroup label="العملاء">${custs.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}</optgroup>`;
        if (sups.length) html += `<optgroup label="الموردين">${sups.map(s => `<option value="${s.id}">${s.name}</option>`).join('')}</optgroup>`;
        if (banks.length) html += `<optgroup label="البنوك">${banks.map(b => `<option value="${b.id}">${b.name}</option>`).join('')}</optgroup>`;
        if (safes.length) html += `<optgroup label="الخزن">${safes.map(s => `<option value="${s.id}">${s.name}</option>`).join('')}</optgroup>`;
        if (expenses.length) html += `<optgroup label="المصاريف">${expenses.map(e => `<option value="${e.id}">${e.name}</option>`).join('')}</optgroup>`;
        if (ccs.length || employees.length || warehouses.length) {
            html += `<optgroup label="أرصدة">`;
            html += ccs.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            html += employees.map(e => `<option value="${e.id}">${e.name}</option>`).join('');
            html += warehouses.map(w => `<option value="${w.id}">${w.name}</option>`).join('');
            html += `</optgroup>`;
        }
        sel.innerHTML = html;
    }
}

function collectPartnerTransactions(partnerId) {
    let txns = [];

    // 1. Single Receipts
    const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    receipts.forEach(v => {
        let curr = v.currency || 'SDG';
        let rate = parseNum(v.rate) || 1;
        if (v.fromAccount === partnerId) { // Partner paid us -> Credit partner
            txns.push({
                date: v.date, ref: v.id, type: 'receipt', typeLabel: 'سند قبض',
                statement: v.fromStatement || 'دفعة نقدية',
                debit: 0, credit: parseNum(v.amountLocal),
                currency: curr, rate: rate, origDebit: 0, origCredit: parseNum(v.amountForeign) || parseNum(v.amountLocal)
            });
        }
        if (v.toAccount === partnerId) { // We gave partner money -> Debit partner
            txns.push({
                date: v.date, ref: v.id, type: 'receipt', typeLabel: 'سند قبض',
                statement: v.fromStatement || 'تسليم نقدية',
                debit: parseNum(v.amountLocal), credit: 0,
                currency: curr, rate: rate, origDebit: parseNum(v.amountForeign) || parseNum(v.amountLocal), origCredit: 0
            });
        }
    });

    // 2. Single Payments
    const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    payments.forEach(v => {
        let curr = v.currency || 'SDG';
        let rate = parseNum(v.rate) || 1;
        if (v.toAccount === partnerId) { // We paid partner -> Debit partner
            txns.push({
                date: v.date, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
                statement: v.toStatement || 'دفعة مسددة',
                debit: parseNum(v.amountLocal), credit: 0,
                currency: curr, rate: rate, origDebit: parseNum(v.amountForeign) || parseNum(v.amountLocal), origCredit: 0
            });
        }
        if (v.fromAccount === partnerId) { // Partner paid us (rare in payment) -> Credit partner
            txns.push({
                date: v.date, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
                statement: v.toStatement || 'استلام نقدية',
                debit: 0, credit: parseNum(v.amountLocal),
                currency: curr, rate: rate, origDebit: 0, origCredit: parseNum(v.amountForeign) || parseNum(v.amountLocal)
            });
        }
    });

    // 3. Journal Vouchers
    const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
    jvs.forEach(v => {
        if (!v.lines) return;
        v.lines.forEach(line => {
            if (line.account === partnerId) {
                let curr = line.currency || 'SDG';
                let rate = parseNum(line.rate) || 1;
                let deb = parseNum(line.debit);
                let cred = parseNum(line.credit);
                txns.push({
                    date: v.date, ref: v.id, type: 'jv', typeLabel: 'قيد يومي',
                    statement: v.statement || line.note || 'حركة قيد يومي',
                    debit: deb, credit: cred,
                    currency: curr, rate: rate, 
                    origDebit: deb > 0 ? (deb / rate) : 0, 
                    origCredit: cred > 0 ? (cred / rate) : 0
                });
            }
        });
    });

    // 4. Sales Invoices
    const sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => x.saleType !== 'preliminary' && x.status !== 'preliminary');
    sales.forEach(inv => {
        const total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        const refNo = inv.invoiceNo || inv.id;
        let curr = inv.currency || 'SDG';
        let rate = parseNum(inv.exchangeRate) || 1;
        let origTotal = total > 0 ? (total / rate) : 0;
        
        if (inv.customerId === partnerId || inv.customer === partnerId) {
            txns.push({
                date: inv.date, ref: refNo, type: 'invoice', typeLabel: 'فاتورة مبيعات',
                statement: inv.paymentMethod === 'credit' ? 'مبيعات آجلة' : 'مبيعات نقدية',
                debit: total, credit: 0,
                currency: curr, rate: rate, origDebit: origTotal, origCredit: 0
            });
            if (inv.paymentMethod !== 'credit') {
                txns.push({
                    date: inv.date, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'سداد فاتورة',
                    statement: 'سداد نقدي مع الفاتورة',
                    debit: 0, credit: total,
                    currency: curr, rate: rate, origDebit: 0, origCredit: origTotal
                });
            } else {
                const paid = parseNum(inv.paidAmount || inv.paid);
                if (paid > 0) {
                    txns.push({
                        date: inv.date, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'دفعة فاتورة',
                        statement: 'دفعة مقدمة مع الفاتورة',
                        debit: 0, credit: paid,
                        currency: curr, rate: rate, origDebit: 0, origCredit: paid / rate
                    });
                }
            }
        }
        
        const accountId = inv.toAccount || inv.safeId || 'safe-001';
        const paid = inv.paymentMethod === 'credit' ? parseNum(inv.paidAmount || inv.paid) : total;
        if (paid > 0 && accountId === partnerId) {
            txns.push({
                date: inv.date, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'مقبوضات مبيعات',
                statement: 'مقبوضات نقدية من فاتورة مبيعات',
                debit: paid, credit: 0,
                currency: curr, rate: rate, origDebit: paid / rate, origCredit: 0
            });
        }
    });

    // 5. Purchase Invoices
    const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN);
    purchases.forEach(inv => {
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        const refNo = inv.invoiceNo || inv.id;
        let curr = inv.currency || 'SDG';
        let rate = parseNum(inv.exchangeRate) || 1;
        let origTotal = total > 0 ? (total / rate) : 0;

        if (inv.supplierId === partnerId || inv.supplier === partnerId || inv.customer === partnerId) {
            txns.push({
                date: inv.date, ref: refNo, type: 'purchase', typeLabel: 'فاتورة مشتريات',
                statement: inv.paymentMethod === 'credit' ? 'مشتريات آجلة' : 'مشتريات نقدية',
                debit: 0, credit: total,
                currency: curr, rate: rate, origDebit: 0, origCredit: origTotal
            });
            if (inv.paymentMethod !== 'credit') {
                txns.push({
                    date: inv.date, ref: refNo + '-PAID', type: 'payment', typeLabel: 'سداد مشتريات',
                    statement: 'سداد نقدي مع الفاتورة',
                    debit: total, credit: 0,
                    currency: curr, rate: rate, origDebit: origTotal, origCredit: 0
                });
            } else {
                const paid = parseNum(inv.paidAmount || inv.paid);
                if (paid > 0) {
                    txns.push({
                        date: inv.date, ref: refNo + '-PAID', type: 'payment', typeLabel: 'دفعة فاتورة',
                        statement: 'سداد نقدي مع الفاتورة',
                        debit: paid, credit: 0,
                        currency: curr, rate: rate, origDebit: paid / rate, origCredit: 0
                    });
                }
            }
        }

        const accountId = inv.fromAccount || inv.safeId || 'safe-001';
        const paid = inv.paymentMethod === 'credit' ? parseNum(inv.paidAmount || inv.paid) : total;
        if (paid > 0 && accountId === partnerId) {
            txns.push({
                date: inv.date, ref: refNo + '-PAID', type: 'payment', typeLabel: 'مدفوعات مشتريات',
                statement: 'مدفوعات نقدية لفاتورة مشتريات',
                debit: 0, credit: paid,
                currency: curr, rate: rate, origDebit: 0, origCredit: paid / rate
            });
        }
    });

    // Sort by date ascending
    txns.sort((a, b) => a.date.localeCompare(b.date));
    return txns;
}

/* Search Auto-complete Logic Removed (Using Native Select) */

/* ─── Drill Down Logic ─── */
function openDrillDown(type, ref) {
    const cleanRef = String(ref).replace('-PAID', '');
    let target = '';
    if (type === 'invoice') target = `sales-invoice.php?id=${cleanRef}`;
    else if (type === 'purchase') target = `purchase.php?id=${cleanRef}`;
    else if (type === 'jv') target = `journal-voucher.php?id=${cleanRef}`;
    else if (type === 'receipt') target = `single-cash-receipt.php?id=${cleanRef}`;
    else if (type === 'payment') target = `single-cash-payment.php?id=${cleanRef}`;
    
    if(target) {
        window.open(target, '_blank');
    } else {
        showToast('التفاصيل غير متاحة لهذا المستند', 'warning');
    }
}

/* ─── Run Report ─── */
function runReport() {
    const partnerId = document.getElementById('filter-partner').value;
    const fromDate = document.getElementById('filter-from').value;
    const toDate = document.getElementById('filter-to').value;

    if (!partnerId) {
        showToast('يرجى اختيار العميل أو المورد أولاً', 'error');
        return;
    }
    if (!fromDate || !toDate) {
        showToast('يرجى تحديد الفترة الزمنية', 'warning');
        return;
    }

    let txns = collectPartnerTransactions(partnerId);

    const repCurr = document.getElementById('filter-currency')?.value || 'SDG';
    let repRate = 1;
    if (repCurr !== 'SDG') {
        repRate = parseNum(document.getElementById('filter-exchange-rate')?.value) || 1;
        if (repRate <= 0) repRate = 1;
    }

    if (repRate !== 1) {
        txns.forEach(t => {
            t.debit = t.debit / repRate;
            t.credit = t.credit / repRate;
        });
    }

    // Find intrinsic opening balance from master data
    let intrinsicBal = 0;
    let custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    let sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
    let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    let safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
    let expenses = JSON.parse(localStorage.getItem('erp_expenses_v1')) || [];
    let employees = JSON.parse(localStorage.getItem('erp_employees_v1')) || [];
    let warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
    
    const cust = custs.find(c => c.id === partnerId);
    if (cust) {
        intrinsicBal = parseNum(cust.openingDebit || 0) - parseNum(cust.openingCredit || 0); // debit
    } else {
        const sup = sups.find(s => s.id === partnerId);
        if (sup) {
            intrinsicBal = parseNum(sup.openingDebit || 0) - parseNum(sup.openingCredit || 0); // credit
        } else {
            const bnk = banks.find(b => b.id === partnerId);
            if (bnk) {
                intrinsicBal = parseNum(bnk.openingDebit || 0) - parseNum(bnk.openingCredit || 0); // debit
            } else {
                const sfe = safes.find(s => s.id === partnerId);
                if (sfe) {
                    intrinsicBal = parseNum(sfe.openingDebit || 0) - parseNum(sfe.openingCredit || 0); // debit
                } else {
                    const exp = expenses.find(e => e.id === partnerId);
                    if (exp) {
                        intrinsicBal = parseNum(exp.openingDebit || 0) - parseNum(exp.openingCredit || 0); // debit
                    } else {
                        const emp = employees.find(e => e.id === partnerId);
                        if (emp) {
                            intrinsicBal = parseNum(emp.openingDebit || 0) - parseNum(emp.openingCredit || 0); // debit
                        } else {
                            const wh = warehouses.find(w => w.id === partnerId);
                            if (wh) intrinsicBal = parseNum(wh.openingDebit || 0) - parseNum(wh.openingCredit || 0);
                        }
                    }
                }
            }
        }
    }

    if (repRate !== 1) {
        intrinsicBal = intrinsicBal / repRate;
    }

    let runningBal = intrinsicBal; // positive = debit, negative = credit

    let priorTxns = txns.filter(t => t.date < fromDate);
    priorTxns.forEach(t => {
        runningBal += t.debit;
        runningBal -= t.credit;
    });

    let currentTxns = txns.filter(t => t.date >= fromDate && t.date <= toDate);

    // Prepend Opening Balance if non-zero
    let openingDebit = 0;
    let openingCredit = 0;
    if (runningBal !== 0 || currentTxns.length > 0) {
        if (runningBal > 0) openingDebit = runningBal;
        else openingCredit = Math.abs(runningBal);
        
        currentTxns.unshift({
            date: fromDate,
            ref: 'OPENING',
            type: 'opening',
            typeLabel: 'رصيد افتتاحي',
            statement: 'رصيد ما قبل الفترة المحددة',
            debit: openingDebit,
            credit: openingCredit,
            isOpening: true
        });
    }

    renderTable(currentTxns, partnerId, fromDate, toDate);
}

function renderTable(txns, partnerId, fromDate, toDate) {
    const tbody = document.getElementById('statement-tbody');
    const summaryCards = document.getElementById('summary-cards');
    const tableWrap = document.getElementById('statement-table-wrap');

    if (txns.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty-state">لا توجد حركات مالية لهذه الجهة في الفترة المحددة</td></tr>`;
        summaryCards.style.display = 'none';
        tableWrap.style.display = 'block';
        return;
    }

    let repCurr = document.getElementById('filter-currency').value;
    let showFc = repCurr !== 'SDG';
    
    document.querySelectorAll('.col-fc').forEach(el => el.style.display = showFc ? 'table-cell' : 'none');

    let totalDebit = 0, totalCredit = 0, runningBalance = 0;
    let totalDebitFc = 0, totalCreditFc = 0, runningBalanceFc = 0;
    let rows = '';

    txns.forEach((t, i) => {
        if (!t.isOpening) {
            totalDebit += t.debit;
            totalCredit += t.credit;
            totalDebitFc += t.origDebit || 0;
            totalCreditFc += t.origCredit || 0;
        }
        runningBalance += t.debit - t.credit;
        runningBalanceFc += (t.origDebit || 0) - (t.origCredit || 0);

        const balClass = runningBalance > 0 ? 'td-balance-dr' : (runningBalance < 0 ? 'td-balance-cr' : '');
        const balSuffix = runningBalance > 0 ? 'مدين له' : (runningBalance < 0 ? 'دائن علينا' : 'صفر');

        const typeBadgeColor = {
            'opening': '#64748b',
            'receipt': '#34d399', 'payment': '#f87171',
            'jv': '#c084fc', 'invoice': '#38bdf8', 'purchase': '#fb923c'
        }[t.type] || '#94a3b8';

        let refHtml = t.isOpening ? t.ref : `
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="color:#38bdf8; font-family:monospace;"><u>${t.ref}</u></span>
                <button onclick="event.stopPropagation(); openDrillDown('${t.type}', '${t.ref}')" style="background:rgba(56,189,248,0.1); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); border-radius:6px; padding:3px 8px; cursor:pointer; font-size:0.75rem; font-weight:bold; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#38bdf8'; this.style.color='#fff';" onmouseout="this.style.background='rgba(56,189,248,0.1)'; this.style.color='#38bdf8';">
                    🔗 عرض الأصل
                </button>
            </div>
        `;

        let fcRateCol = '', fcDebitCol = '', fcCreditCol = '', fcBalCol = '';
        if (showFc) {
            const balClassFc = runningBalanceFc > 0 ? 'td-balance-dr' : (runningBalanceFc < 0 ? 'td-balance-cr' : '');
            const balSuffixFc = runningBalanceFc > 0 ? 'مدين له' : (runningBalanceFc < 0 ? 'دائن علينا' : 'صفر');
            let rateTxt = t.rate !== 1 && t.rate !== undefined ? fmtNum(t.rate) : (t.currency === 'SDG' ? '—' : '1.0');
            fcRateCol = `<td class="col-fc" style="color:#fbbf24; font-weight:bold;">${t.isOpening ? '—' : rateTxt}</td>`;
            fcDebitCol = `<td class="col-fc td-debit">${t.origDebit > 0 ? fmtNum(t.origDebit) : '-'}</td>`;
            fcCreditCol = `<td class="col-fc td-credit">${t.origCredit > 0 ? fmtNum(t.origCredit) : '-'}</td>`;
            fcBalCol = `<td class="col-fc ${balClassFc}" style="color:#38bdf8;">${fmtNum(Math.abs(runningBalanceFc))} <span style="font-size:0.7rem; opacity:0.8;">${balSuffixFc}</span></td>`;
        } else {
            fcRateCol = `<td class="col-fc" style="display:none;"></td>`;
            fcDebitCol = `<td class="col-fc" style="display:none;"></td>`;
            fcCreditCol = `<td class="col-fc" style="display:none;"></td>`;
            fcBalCol = `<td class="col-fc" style="display:none;"></td>`;
        }

        rows += `<tr class="${t.isOpening ? 'opening-row' : 'drill-row'}" ${t.isOpening ? '' : `onclick="openDrillDown('${t.type}', '${t.ref}')" title="انقر لفتح المستند الأصلي (Deep Drill-down)"`}>
            <td>${t.date}</td>
            <td>${refHtml}</td>
            <td><span style="background:${typeBadgeColor}20; color:${typeBadgeColor}; padding:2px 8px; border-radius:10px; font-size:0.75rem;">${t.typeLabel}</span></td>
            <td style="${t.isOpening ? 'font-weight:bold;' : ''}">${t.statement}</td>
            ${fcRateCol}
            ${fcDebitCol}
            <td class="td-debit">${t.debit > 0 ? fmtNum(t.debit) : '-'}</td>
            ${fcCreditCol}
            <td class="td-credit">${t.credit > 0 ? fmtNum(t.credit) : '-'}</td>
            ${fcBalCol}
            <td class="${balClass}">${fmtNum(Math.abs(runningBalance))} <span style="font-size:0.7rem; opacity:0.8;">${balSuffix}</span></td>
        </tr>`;
    });

    tbody.innerHTML = rows;

    document.getElementById('sum-debit').textContent = fmtNum(totalDebit);
    document.getElementById('sum-credit').textContent = fmtNum(totalCredit);
    document.getElementById('sum-balance').textContent = fmtNum(Math.abs(runningBalance)) + ' ' + (runningBalance >= 0 ? 'مدين (له)' : 'دائن (علينا)');

    summaryCards.style.display = 'grid';
    tableWrap.style.display = 'block';

    const sel = document.getElementById('filter-partner');
    const partnerName = sel.options[sel.selectedIndex]?.text || '';
    document.getElementById('print-header').style.display = 'block';
    document.getElementById('print-meta').innerHTML = `
        <span>جهة الحساب: <strong>${partnerName}</strong></span>
        <span>الفترة: من <strong>${fromDate}</strong> إلى <strong>${toDate}</strong></span>
        <span>تاريخ الطباعة: <strong>${new Date().toLocaleDateString('ar-SD')}</strong></span>
    `;

    showToast(`✅ تم عرض ${txns.length} حركة بنجاح`, 'success');
}

function previewStatement() {
    const tableWrap = document.getElementById('statement-table-wrap');
    if (!tableWrap || tableWrap.style.display === 'none') {
        return showToast('الرجاء تشغيل التقرير أولاً للمعاينة', 'warning');
    }
    
    const printHeader = document.getElementById('print-header').innerHTML;
    const tableHtml = document.querySelector('.statement-table').outerHTML;
    
    const win = window.open('', '_blank', 'width=1000,height=700');
    win.document.write(`
        <html dir="rtl" lang="ar">
        <head>
            <title>معاينة كشف الحساب</title>
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
                .empty-state { text-align: center; padding: 40px; color: #666; font-weight: bold; }
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

function exportStatementCSV() {
    const rows = document.querySelectorAll('#statement-tbody tr:not(.empty-state)');
    if (rows.length === 0) return showToast('لا يوجد بيانات للتصدير', 'warning');
    
    let repCurr = document.getElementById('filter-currency').value;
    let showFc = repCurr !== 'SDG';
    
    let csv = '\uFEFFالتاريخ,رقم المستند,نوع الحركة,البيان,';
    if(showFc) csv += 'سعر الصرف,مدين أجنبي,';
    csv += 'مدين محلي,';
    if(showFc) csv += 'دائن أجنبي,';
    csv += 'دائن محلي,';
    if(showFc) csv += 'رصيد أجنبي,';
    csv += 'الرصيد التراكمي\n';
    
    rows.forEach(r => {
        const tds = Array.from(r.querySelectorAll(showFc ? 'td' : 'td:not(.col-fc)'));
        const cells = tds.map(td => `"${td.textContent.trim()}"`);
        csv += cells.join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    const sel = document.getElementById('filter-partner');
    const partnerName = sel.options[sel.selectedIndex]?.text || '';
    link.download = `Statement-${partnerName}-${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
    showToast('📥 تم التصدير بنجاح', 'success');
}

document.addEventListener('DBReady', () => {
    loadPartners();
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    document.getElementById('filter-from').value = firstDay.toISOString().slice(0, 10);
    document.getElementById('filter-to').value = now.toISOString().slice(0, 10);

    const urlParams = new URLSearchParams(window.location.search);
    const partnerId = urlParams.get('partnerId');
    if (partnerId) {
        const partner = ALL_PARTNERS.find(p => p.id === partnerId);
        if (partner) {
            document.getElementById('filter-partner').value = partner.id;
            setTimeout(runReport, 300);
        }
    }
});
