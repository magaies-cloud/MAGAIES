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
    
    custs.forEach(c => ALL_PARTNERS.push({ 
        id: c.id, code: c.accountNo || c.code || '-', name: c.name, type: 'CUST', 
        phone: c.phone || '', taxNumber: c.taxNumber || '', currency: c.currency || 'SDG',
        openingDebit: parseNum(c.openingDebit || 0), openingCredit: parseNum(c.openingCredit || 0),
        notes: c.notes || '',
        debit: 0, credit: 0, lastDate: null 
    }));
    sups.forEach(s  => ALL_PARTNERS.push({ 
        id: s.id, code: s.accountNo || s.code || '-', name: s.name, type: 'SUPP', 
        phone: s.phone || '', taxNumber: s.taxNumber || '', currency: s.currency || 'SDG',
        openingDebit: parseNum(s.openingDebit || 0), openingCredit: parseNum(s.openingCredit || 0),
        notes: s.notes || '',
        debit: 0, credit: 0, lastDate: null 
    }));
    
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
        const balA = Math.abs((a.openingDebit + a.debit) - (a.openingCredit + a.credit));
        const balB = Math.abs((b.openingDebit + b.debit) - (b.openingCredit + b.credit));
        if(sortFilter === 'BALANCE_DESC') return balB - balA;
        if(sortFilter === 'BALANCE_ASC') return balA - balB;
        if(sortFilter === 'NAME') return a.name.localeCompare(b.name);
        if(sortFilter === 'LAST_DATE') return (b.lastDate || '').localeCompare(a.lastDate || '');
        return 0;
    });

    renderTable(results);
}

function openWhatsAppModal() {
    document.getElementById('whatsapp-modal').style.display = 'flex';
}

function closeWhatsAppModal() {
    document.getElementById('whatsapp-modal').style.display = 'none';
}

let startX, startWidth, resizingTh;

function bindResizers() {
    const table = document.getElementById('balances-table');
    if (!table) return;
    const resizers = table.querySelectorAll('.resizer');
    resizers.forEach(resizer => {
        resizer.addEventListener('mousedown', initResize);
        resizer.addEventListener('dblclick', autoSize);
    });
}

function autoSize(e) {
    e.stopPropagation();
    const th = e.target.parentElement;
    th.style.width = 'auto'; 
}

function initResize(e) {
    e.stopPropagation();
    resizingTh = e.target.parentElement;
    startX = e.clientX;
    startWidth = parseInt(window.getComputedStyle(resizingTh).width, 10);
    
    const ths = document.querySelectorAll('#balances-table th');
    ths.forEach((th) => {
        if (!th.style.width) {
            th.style.width = window.getComputedStyle(th).width;
        }
    });
    
    const table = document.getElementById('balances-table');
    table.style.width = 'max-content';
    
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup', stopResize);
    e.target.classList.add('resizing');
}

function doResize(e) {
    const delta = startX - e.clientX; 
    const newWidth = startWidth + delta;
    if (newWidth > 40) {
        resizingTh.style.width = newWidth + 'px';
    }
}

function stopResize(e) {
    document.removeEventListener('mousemove', doResize);
    document.removeEventListener('mouseup', stopResize);
    document.querySelectorAll('.resizer').forEach(r => r.classList.remove('resizing'));
}

function bindNumberFocus() {
    const nums = document.querySelectorAll('#balances-tbody td.num');
    nums.forEach(td => {
        td.addEventListener('click', function(e) {
            const cell = e.target;
            if (cell.classList.contains('focused-num')) {
                cell.classList.remove('focused-num');
            } else {
                document.querySelectorAll('.focused-num').forEach(c => c.classList.remove('focused-num'));
                cell.classList.add('focused-num');
            }
        });
    });
}

function bindContextMenu() {
    const table = document.getElementById('balances-table');
    const thead = table.querySelector('thead');
    const ctxMenu = document.getElementById('columnContextMenu');
    const togglesContainer = document.getElementById('columnToggles');

    thead.addEventListener('contextmenu', function(e) {
        // Prevent default if we clicked on a TH or inside it
        const th = e.target.closest('th');
        if (!th) return;
        
        e.preventDefault();
        
        const ths = table.querySelectorAll('thead th');
        togglesContainer.innerHTML = '';
        ths.forEach((thEl, index) => {
            const isHidden = thEl.style.display === 'none';
            // clean up text from child elements
            let text = thEl.innerText.replace('اسحب لتغيير عرض العمود (Double click للإرجاع)', '').trim();
            if(!text) text = 'عمود ' + (index+1);

            const div = document.createElement('div');
            div.style.padding = '6px 5px';
            div.style.cursor = 'pointer';
            div.style.borderBottom = '1px solid #f1f5f9';
            div.innerHTML = `<label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600; font-size:0.9rem;">
                <input type="checkbox" style="width:16px; height:16px; cursor:pointer;" ${!isHidden ? 'checked' : ''} onchange="toggleColumn(${index}, this.checked)"> 
                ${text}
            </label>`;
            togglesContainer.appendChild(div);
        });

        ctxMenu.style.display = 'block';
        ctxMenu.style.left = e.pageX + 'px';
        ctxMenu.style.top = e.pageY + 'px';
    });

    document.addEventListener('click', function(e) {
        if (!ctxMenu.contains(e.target)) {
            ctxMenu.style.display = 'none';
        }
    });
}

window.toggleColumn = function(index, show) {
    const table = document.getElementById('balances-table');
    const rows = table.querySelectorAll('tr');
    const display = show ? '' : 'none';
    rows.forEach(row => {
        const cells = row.children;
        // Ignore rows that don't have enough cells (like empty states)
        if (cells.length > 1 && cells[index]) {
            cells[index].style.display = display;
        }
    });
}


function renderTable(results) {
    const tbody = document.getElementById('balances-tbody');
    
    if(results.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="empty-state">لا توجد حسابات مسجلة تطابق الفلتر</td></tr>`;
        document.getElementById('analytics-dashboard').style.display = 'none';
        document.getElementById('actions-panel').style.display = 'none';
        document.getElementById('print-container').style.display = 'none';
        return;
    }

    let rows = '';
    let sumCust = 0;
    let sumSupp = 0;

    results.forEach(p => {
        const bal = (p.openingDebit + p.debit) - (p.openingCredit + p.credit);
        let balStr = '';
        if(bal > 0) { balStr = fmtNum(bal) + ' (عليه)'; sumCust += bal; }
        else if(bal < 0) { balStr = fmtNum(Math.abs(bal)) + ' (له)'; sumSupp += Math.abs(bal); }
        else { balStr = '0.00'; }

        rows += `<tr>
            <td>${p.name} <span style="color:#64748b;font-size:0.7rem;margin-right:5px;">(${p.type === 'CUST' ? 'عميل' : 'مورد'})</span></td>
            <td class="num" dir="ltr">${p.phone}</td>
            <td class="num">${p.taxNumber}</td>
            <td class="num">${p.openingDebit > 0 ? fmtNum(p.openingDebit) : '-'}</td>
            <td class="num">${p.openingCredit > 0 ? fmtNum(p.openingCredit) : '-'}</td>
            <td class="num">${p.debit > 0 ? fmtNum(p.debit) : '-'}</td>
            <td class="num">${p.credit > 0 ? fmtNum(p.credit) : '-'}</td>
            <td style="max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${p.notes || ''}">${p.notes || '-'}</td>
        </tr>`;
    });

    tbody.innerHTML = rows;

    document.getElementById('kpi-dr').textContent = fmtNum(sumCust);
    document.getElementById('kpi-cr').textContent = fmtNum(sumSupp);
    
    const net = sumCust - sumSupp;
    document.getElementById('kpi-bal').textContent = fmtNum(Math.abs(net)) + (net >= 0 ? ' (لنا)' : ' (علينا)');

    document.getElementById('analytics-dashboard').style.display = 'block';
    document.getElementById('actions-panel').style.display = 'flex';
    document.getElementById('print-container').style.display = 'block';

    showToast(`✅ تم تجميع أرصدة ${results.length} حساب بنجاح`, 'success');
    
    bindResizers();
    bindNumberFocus();
    bindContextMenu();
}

function exportExcel() {
    const rows = document.querySelectorAll('#balances-tbody tr:not(.empty-state)');
    if (rows.length === 0) return showToast('لا توجد بيانات للتصدير', 'warning');

    let csv = '\uFEFF';
    csv += 'اسم الحساب,رقم الهاتف,الرقم الضريبي,عليه عملة,له عملة,عليه,له,ملحوظة\n';
    rows.forEach(r => {
        const cells = Array.from(r.querySelectorAll('td'));
        const name = cells[0].innerText.replace('(عميل)', '').replace('(مورد)', '').trim();
        const phone = cells[1].innerText;
        const tax = cells[2].innerText;
        const opDeb = cells[3].innerText.replace(/-/g, '0');
        const opCred = cells[4].innerText.replace(/-/g, '0');
        const txDeb = cells[5].innerText.replace(/-/g, '0');
        const txCred = cells[6].innerText.replace(/-/g, '0');
        const notes = cells[7].innerText;
        
        csv += `"${name}","${phone}","${tax}","${opDeb}","${opCred}","${txDeb}","${txCred}","${notes}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `Global-Balances-${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showToast('📥 تم التصدير للإكسل بنجاح', 'success');
}

function exportPDF() {
    const element = document.getElementById('print-container');
    if (!element || element.style.display === 'none') {
        return showToast('الرجاء تشغيل التقرير أولاً للتصدير', 'warning');
    }
    showToast('⏳ جاري تصدير التقرير كملف PDF...');
    
    // Using html2pdf if included
    if(typeof html2pdf !== 'undefined') {
        var opt = {
          margin:       0.5,
          filename:     'Balances-Report.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save().then(() => {
            showToast('✅ تم التصدير بنجاح!', 'success');
        });
    } else {
        window.print();
    }
}

function shareEmail() {
    showToast('تم فتح تطبيق البريد لإرسال التقرير', 'success');
    window.location.href = "mailto:?subject=تقرير الأرصدة الإجمالية&body=تجدون مرفقاً التقرير...";
}

function shareWhatsApp() {
    showToast('جاري التحويل للواتساب...', 'success');
    window.open("https://api.whatsapp.com/send?text=تم إرسال تقرير الأرصدة من النظام المالي", "_blank");
}

