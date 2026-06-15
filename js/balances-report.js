/* ═══════════════════════════════════════════════════════
   balances-report.js — Global Balances & Aging Report
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
let ALL_TXNS = [];

function loadData() {
    ALL_PARTNERS = [];
    const custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
    const sups  = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
    
    custs.forEach(c => ALL_PARTNERS.push({ id: c.id, code: c.code || '-', name: c.name, type: 'CUST', debit: 0, credit: 0, lastDate: null }));
    sups.forEach(s  => ALL_PARTNERS.push({ id: s.id, code: s.code || '-', name: s.name, type: 'SUPP', debit: 0, credit: 0, lastDate: null }));
    
    // We reuse the collector logic from SGL, modified to only grab partner transactions
    ALL_TXNS = [];
    const timeNow = new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'});

    // 1. Receipts
    const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    receipts.forEach(v => {
        const cc = v.costCenter || 'ALL';
        ALL_TXNS.push({ partner: v.fromAccount, debit: 0, credit: parseNum(v.amountLocal), date: v.date, time: v.time || timeNow, costCenter: cc });
        ALL_TXNS.push({ partner: v.toAccount, debit: parseNum(v.amountLocal), credit: 0, date: v.date, time: v.time || timeNow, costCenter: cc });
    });

    // 2. Payments
    const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    payments.forEach(v => {
        const cc = v.costCenter || 'ALL';
        ALL_TXNS.push({ partner: v.toAccount, debit: parseNum(v.amountLocal), credit: 0, date: v.date, time: v.time || timeNow, costCenter: cc });
        ALL_TXNS.push({ partner: v.fromAccount, debit: 0, credit: parseNum(v.amountLocal), date: v.date, time: v.time || timeNow, costCenter: cc });
    });

    // 3. JVs
    const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
    jvs.forEach(v => {
        if (!v.lines) return;
        const time = v.time || timeNow;
        v.lines.forEach(line => {
            const cc = line.costCenter || v.costCenter || 'ALL';
            ALL_TXNS.push({ partner: line.account, debit: parseNum(line.debit), credit: parseNum(line.credit), date: v.date, time: time, costCenter: cc });
        });
    });

    // 4. Sales
    const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    sales.forEach(inv => {
        const cc = inv.costCenter || 'ALL';
        const time = inv.time || timeNow;
        const partner = inv.customerId || inv.customer;
        
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        
        const paid = parseNum(inv.paidAmount || inv.paid);
        if(partner && partner !== 'walk-in') {
            ALL_TXNS.push({ partner: partner, debit: total, credit: 0, date: inv.date, time: time, costCenter: cc });
            if(inv.paymentMethod !== 'credit' && paid === 0) {
                ALL_TXNS.push({ partner: partner, debit: 0, credit: total, date: inv.date, time: time, costCenter: cc });
            } else if(paid > 0) {
                ALL_TXNS.push({ partner: partner, debit: 0, credit: paid, date: inv.date, time: time, costCenter: cc });
            }
        }
    });

    // 5. Purchases
    const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    purchases.forEach(inv => {
        const cc = inv.costCenter || 'ALL';
        const time = inv.time || timeNow;
        const partner = inv.supplierId || inv.supplier || inv.customer;
        
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        
        const paid = parseNum(inv.paidAmount || inv.paid);
        if(partner && partner !== 'walk-in') {
            ALL_TXNS.push({ partner: partner, debit: 0, credit: total, date: inv.date, time: time, costCenter: cc });
            if(inv.paymentMethod !== 'credit' && paid === 0) {
                ALL_TXNS.push({ partner: partner, debit: total, credit: 0, date: inv.date, time: time, costCenter: cc });
            } else if(paid > 0) {
                ALL_TXNS.push({ partner: partner, debit: paid, credit: 0, date: inv.date, time: time, costCenter: cc });
            }
        }
    });

    // Extract Cost Centers
    const ccSet = new Set();
    ALL_TXNS.forEach(t => { if(t.costCenter && t.costCenter !== 'ALL') ccSet.add(t.costCenter); });
    const ccSelect = document.getElementById('filter-costcenter');
    if(ccSelect) {
        ccSelect.innerHTML = '<option value="ALL">جميع مراكز التكلفة (مجمعة)</option>';
        Array.from(ccSet).forEach(cc => {
            ccSelect.innerHTML += `<option value="${cc}">${cc}</option>`;
        });
    }
}

document.addEventListener('DBReady', () => {
    loadData();
});

function runReport() {
    const grpFilter = document.getElementById('filter-group').value;
    const sortFilter = document.getElementById('filter-sort').value;
    const ccFilter = document.getElementById('filter-costcenter').value;

    // Reset balances
    ALL_PARTNERS.forEach(p => { p.debit = 0; p.credit = 0; p.lastDate = null; });

    // Filter transactions
    const filteredTxns = ccFilter === 'ALL' ? ALL_TXNS : ALL_TXNS.filter(t => t.costCenter === ccFilter);

    // Aggregate
    filteredTxns.forEach(t => {
        const p = ALL_PARTNERS.find(x => x.id === t.partner);
        if(p) {
            p.debit += t.debit;
            p.credit += t.credit;
            const tDate = t.date + ' ' + t.time;
            if(!p.lastDate || tDate > p.lastDate) {
                p.lastDate = tDate;
            }
        }
    });

    // Filter partners by group
    let results = ALL_PARTNERS.filter(p => {
        // Exclude completely empty accounts if they have no activity? 
        // Let's include them if they want to see all balances, but usually we filter out zero activity
        // Let's keep them if they are selected.
        if(grpFilter === 'ALL') return true;
        return p.type === grpFilter;
    });

    // Sort
    results.sort((a, b) => {
        const balA = Math.abs(a.debit - a.credit);
        const balB = Math.abs(b.debit - b.credit);
        if(sortFilter === 'BALANCE_DESC') return balB - balA;
        if(sortFilter === 'BALANCE_ASC') return balA - balB;
        if(sortFilter === 'NAME') return a.name.localeCompare(b.name);
        if(sortFilter === 'LAST_DATE') return (b.lastDate || '').localeCompare(a.lastDate || '');
        return 0;
    });

    renderTable(results);
}

function renderTable(results) {
    const tbody = document.getElementById('balances-tbody');
    
    if(results.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="empty-state">لا توجد حسابات مسجلة تطابق الفلتر</td></tr>`;
        document.getElementById('summary-cards').style.display = 'none';
        document.getElementById('audit-stamp').style.display = 'none';
        return;
    }

    let rows = '';
    let sumCust = 0;
    let sumSupp = 0;

    results.forEach(p => {
        const bal = p.debit - p.credit;
        let balStr = '';
        if(bal > 0) { balStr = fmtNum(bal) + ' (مدين)'; sumCust += bal; }
        else if(bal < 0) { balStr = fmtNum(Math.abs(bal)) + ' (دائن)'; sumSupp += Math.abs(bal); }
        else { balStr = '0.00'; }

        rows += `<tr>
            <td class="td-code">${p.code}</td>
            <td class="td-name">${p.name} <span style="color:#64748b;font-size:0.7rem;margin-right:5px;">(${p.type === 'CUST' ? 'عميل' : 'مورد'})</span></td>
            <td style="color:#64748b;">0.00</td>
            <td class="td-debit">${p.debit > 0 ? fmtNum(p.debit) : '-'}</td>
            <td class="td-credit">${p.credit > 0 ? fmtNum(p.credit) : '-'}</td>
            <td class="td-balance">${balStr}</td>
            <td><span class="badge-active">نشط</span></td>
            <td class="td-date">${p.lastDate ? p.lastDate.substring(0, 10) : 'لا يوجد حركات'}</td>
        </tr>`;
    });

    tbody.innerHTML = rows;

    document.getElementById('sum-customers').textContent = fmtNum(sumCust);
    document.getElementById('sum-suppliers').textContent = fmtNum(sumSupp);
    
    const net = sumCust - sumSupp;
    document.getElementById('sum-net').textContent = fmtNum(Math.abs(net)) + (net >= 0 ? ' (صالحنا)' : ' (علينا)');

    document.getElementById('summary-cards').style.display = 'grid';
    document.getElementById('audit-stamp').style.display = 'flex';
    document.getElementById('print-header').style.display = 'block';

    showToast(`✅ تم تجميع أرصدة ${results.length} حساب بنجاح`, 'success');
}

function exportExcel() {
    const rows = document.querySelectorAll('#balances-tbody tr:not(.empty-state)');
    if (rows.length === 0) return showToast('لا توجد بيانات للتصدير', 'warning');

    let csv = '\uFEFF';
    csv += 'كود الحساب,اسم المورد / الزبون,الرصيد الافتتاحي,إجمالي مدين,إجمالي دائن,الرصيد الحالي,حالة الحساب,آخر حركة\n';
    rows.forEach(r => {
        const cells = Array.from(r.querySelectorAll('td'));
        const code = cells[0].innerText;
        const name = cells[1].innerText.replace('(عميل)', '').replace('(مورد)', '').trim();
        const op = cells[2].innerText;
        const deb = cells[3].innerText.replace(/-/g, '0');
        const cred = cells[4].innerText.replace(/-/g, '0');
        const bal = cells[5].innerText;
        const state = cells[6].innerText;
        const last = cells[7].innerText;
        
        csv += `"${code}","${name}","${op}","${deb}","${cred}","${bal}","${state}","${last}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `Global-Balances-${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showToast('📥 تم التصدير بنجاح', 'success');
}


function previewReport() {
    const tableWrap = document.getElementById('report-table-wrap');
    if (!tableWrap || tableWrap.style.display === 'none') {
        if(typeof showToast !== 'undefined') showToast('الرجاء تشغيل التقرير أولاً للمعاينة', 'warning');
        else alert('الرجاء تشغيل التقرير أولاً للمعاينة');
        return;
    }
    
    const printHeader = document.getElementById('print-header') ? document.getElementById('print-header').innerHTML : '';
    const tableHtml = tableWrap.outerHTML; // or just the table
    
    const win = window.open('', '_blank', 'width=1000,height=700');
    win.document.write(`
        <html dir="rtl" lang="ar">
        <head>
            <title>معاينة أرصدة الحسابات</title>
            <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Tajawal', sans-serif; padding: 30px; background: #fff; color: #000; direction: rtl; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9rem; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
                th { background: #f3f4f6; color: #333; font-weight: bold; }
                .td-debit { color: #059669; font-weight: bold; }
                .td-credit { color: #dc2626; font-weight: bold; }
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
