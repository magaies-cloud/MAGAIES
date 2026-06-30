/* ═══════════════════════════════════════════════════════
   balances-report.js — Global Balances & Aging Report
   ═══════════════════════════════════════════════════════ */

window.colConfigs = [
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '' },
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '' },
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '' },
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '' },
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '' },
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '' },
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '' },
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '' }
];
window.colOrder = [0, 1, 2, 3, 4, 5, 6, 7];

function loadLayout() {
    try {
        const saved = localStorage.getItem('balancesReportLayout');
        if (saved) {
            const layout = JSON.parse(saved);
            if (layout.colConfigs && layout.colOrder) {
                window.colConfigs = layout.colConfigs;
                window.colOrder = layout.colOrder;
            }
        }
    } catch(e) { console.error('Error loading layout', e); }
}
loadLayout();

function saveLayout() {
    const layout = {
        colConfigs: window.colConfigs,
        colOrder: window.colOrder
    };
    localStorage.setItem('balancesReportLayout', JSON.stringify(layout));
    showToast('تم حفظ التصميم بنجاح', 'success');
}

function fmtColNum(n, origIdx) {
    const d = window.colConfigs[origIdx].decimals;
    const val = parseFloat(n) || 0;
    return val.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
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
        const th = e.target.closest('th');
        if (!th) return;
        
        e.preventDefault();
        
        const ths = table.querySelectorAll('thead th');
        togglesContainer.innerHTML = '';
        ths.forEach((thEl, visualIndex) => {
            const isHidden = thEl.style.display === 'none';
            let text = thEl.innerText.replace('اسحب لتغيير عرض العمود (Double click للإرجاع)', '').trim();
            if(!text) text = 'عمود ' + (visualIndex+1);
            
            const origIdx = parseInt(thEl.getAttribute('data-orig-idx'));
            const conf = window.colConfigs[origIdx];

            const div = document.createElement('div');
            div.style.marginBottom = '8px';
            div.style.border = '1px solid #e2e8f0';
            div.style.borderRadius = '6px';
            div.style.overflow = 'hidden';
            
            div.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#f8fafc; cursor:pointer;" onclick="const p = document.getElementById('config-panel-${visualIndex}'); p.style.display = p.style.display === 'none' ? 'block' : 'none'">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:0.9rem; margin:0;" onclick="event.stopPropagation()">
                        <input type="checkbox" style="width:16px; height:16px; cursor:pointer;" ${!isHidden ? 'checked' : ''} onchange="toggleColumn(${visualIndex}, this.checked)"> 
                        ${text}
                    </label>
                    <span style="font-size:0.8rem; color:#475569;">⚙️</span>
                </div>
                <div id="config-panel-${visualIndex}" style="display:none; padding:12px; background:#fff; border-top:1px solid #e2e8f0;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; align-items:center;">
                        <span style="font-size:0.8rem; font-weight:bold; color:#475569;">الخط:</span>
                        <div style="display:flex; gap:4px;">
                            <button onclick="changeColFs(${origIdx}, ${visualIndex}, -0.1)" style="padding:2px 8px; cursor:pointer; border-radius:4px; border:1px solid #ccc; background:#f1f5f9;">-</button>
                            <button onclick="changeColFs(${origIdx}, ${visualIndex}, 0.1)" style="padding:2px 8px; cursor:pointer; border-radius:4px; border:1px solid #ccc; background:#f1f5f9;">+</button>
                            <button onclick="toggleColBold(${origIdx}, ${visualIndex})" style="padding:2px 8px; cursor:pointer; font-weight:bold; border-radius:4px; border:1px solid #ccc; background:${conf.bold ? '#cbd5e1' : '#f1f5f9'}">B</button>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; align-items:center;">
                        <span style="font-size:0.8rem; font-weight:bold; color:#475569;">الخانات:</span>
                        <div style="display:flex; gap:4px;">
                            <button onclick="setColDecimals(${origIdx}, ${visualIndex}, 0)" style="padding:2px 8px; cursor:pointer; border-radius:4px; border:1px solid #ccc; background:${conf.decimals === 0 ? '#cbd5e1' : '#f1f5f9'}">0</button>
                            <button onclick="setColDecimals(${origIdx}, ${visualIndex}, 2)" style="padding:2px 8px; cursor:pointer; border-radius:4px; border:1px solid #ccc; background:${conf.decimals === 2 ? '#cbd5e1' : '#f1f5f9'}">2</button>
                            <button onclick="setColDecimals(${origIdx}, ${visualIndex}, 3)" style="padding:2px 8px; cursor:pointer; border-radius:4px; border:1px solid #ccc; background:${conf.decimals === 3 ? '#cbd5e1' : '#f1f5f9'}">3</button>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span style="font-size:0.8rem; font-weight:bold; color:#475569;">لون النص:</span>
                        <input type="color" value="${conf.color || '#000000'}" onchange="setColColor(${origIdx}, ${visualIndex}, 'color', this.value)" style="width:30px; height:25px; padding:0; border:none; background:transparent; cursor:pointer;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.8rem; font-weight:bold; color:#475569;">لون الخلفية:</span>
                        <input type="color" value="${conf.bg || '#ffffff'}" onchange="setColColor(${origIdx}, ${visualIndex}, 'bg', this.value)" style="width:30px; height:25px; padding:0; border:none; background:transparent; cursor:pointer;">
                    </div>
                </div>
            `;
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

window.toggleColumn = function(visualIndex, show) {
    const table = document.getElementById('balances-table');
    const display = show ? '' : 'none';
    const ths = table.querySelectorAll('thead th');
    if (ths[visualIndex]) ths[visualIndex].style.display = display;
    
    const rows = table.querySelectorAll('tbody tr:not(.empty-state)');
    rows.forEach(row => {
        const cells = row.children;
        if (cells.length > visualIndex) {
            cells[visualIndex].style.display = display;
        }
    });
}

function updateColDOM(visualIndex, origIdx) {
    const conf = window.colConfigs[origIdx];
    const table = document.getElementById('balances-table');
    const th = table.querySelectorAll('thead th')[visualIndex];
    if(th) {
        th.style.fontWeight = conf.bold ? '900' : 'bold'; // Headers are bold by default, 900 for thicker
        th.style.fontSize = conf.fs + 'em';
        if(conf.color) th.style.color = conf.color; else th.style.color = '';
        if(conf.bg && conf.bg !== '#ffffff') th.style.backgroundColor = conf.bg; else th.style.backgroundColor = '';
    }
    
    const rows = table.querySelectorAll('tbody tr:not(.empty-state)');
    rows.forEach(row => {
        const cell = row.children[visualIndex];
        if(cell) {
            cell.style.fontWeight = conf.bold ? 'bold' : '';
            cell.style.fontSize = conf.fs + 'em';
            if(conf.color) cell.style.color = conf.color; else cell.style.color = '';
            if(conf.bg && conf.bg !== '#ffffff') cell.style.backgroundColor = conf.bg; else cell.style.backgroundColor = '';
        }
    });
}

window.changeColFs = function(origIdx, visualIndex, delta) {
    window.colConfigs[origIdx].fs += delta;
    if(window.colConfigs[origIdx].fs < 0.5) window.colConfigs[origIdx].fs = 0.5;
    if(window.colConfigs[origIdx].fs > 2.5) window.colConfigs[origIdx].fs = 2.5;
    updateColDOM(visualIndex, origIdx);
}
window.toggleColBold = function(origIdx, visualIndex) {
    window.colConfigs[origIdx].bold = !window.colConfigs[origIdx].bold;
    updateColDOM(visualIndex, origIdx);
    document.getElementById('columnContextMenu').style.display = 'none'; // quick close to refresh UI
}
window.setColDecimals = function(origIdx, visualIndex, d) {
    window.colConfigs[origIdx].decimals = d;
    if (window.lastResults) renderTable(window.lastResults);
}
window.setColColor = function(origIdx, visualIndex, type, val) {
    window.colConfigs[origIdx][type] = val;
    updateColDOM(visualIndex, origIdx);
}

function bindColumnReorder() {
    const table = document.getElementById('balances-table');
    const tr = table.querySelector('thead tr');
    if (!window.Sortable) return;
    
    const ths = tr.querySelectorAll('th');
    ths.forEach(th => th.style.cursor = 'grab');

    if (window.colSortable) {
        window.colSortable.destroy();
    }
    
    window.colSortable = new Sortable(tr, {
        animation: 150,
        filter: '.resizer',
        preventOnFilter: false,
        onStart: function(evt) {
            evt.item.style.cursor = 'grabbing';
        },
        onEnd: function(evt) {
            evt.item.style.cursor = 'grab';
            const oldIndex = evt.oldIndex;
            const newIndex = evt.newIndex;
            if (oldIndex === newIndex) return;
            
            // update window.colOrder
            const movedIdx = window.colOrder.splice(oldIndex, 1)[0];
            window.colOrder.splice(newIndex, 0, movedIdx);

            const tbody = table.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr:not(.empty-state)');
            rows.forEach(row => {
                const cellsArray = Array.from(row.children);
                if (cellsArray.length > oldIndex && cellsArray.length > newIndex) {
                    const movedCell = cellsArray.splice(oldIndex, 1)[0];
                    cellsArray.splice(newIndex, 0, movedCell);
                    row.innerHTML = '';
                    cellsArray.forEach(c => row.appendChild(c));
                }
            });
        }
    });
}


function renderTable(results) {
    window.lastResults = results;
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

    function getCellStyle(origIdx) {
        const conf = window.colConfigs[origIdx];
        let style = '';
        if(conf.bold) style += 'font-weight:bold;';
        if(conf.fs !== 1.0) style += `font-size:${conf.fs}em;`;
        if(conf.color) style += `color:${conf.color};`;
        if(conf.bg && conf.bg !== '#ffffff') style += `background-color:${conf.bg};`;
        return style;
    }

    results.forEach(p => {
        const bal = (p.openingDebit + p.debit) - (p.openingCredit + p.credit);
        let balStr = '';
        if(bal > 0) { balStr = fmtColNum(bal, 5) + ' (عليه)'; sumCust += bal; }
        else if(bal < 0) { balStr = fmtColNum(Math.abs(bal), 6) + ' (له)'; sumSupp += Math.abs(bal); }
        else { balStr = '0.00'; }

        let cells = [];
        cells[0] = `<td style="${getCellStyle(0)}">${p.name} <span style="color:#64748b;font-size:0.7rem;margin-right:5px;">(${p.type === 'CUST' ? 'عميل' : 'مورد'})</span></td>`;
        cells[1] = `<td class="num" dir="ltr" style="${getCellStyle(1)}">${p.phone}</td>`;
        cells[2] = `<td class="num" style="${getCellStyle(2)}">${p.taxNumber}</td>`;
        cells[3] = `<td class="num" style="${getCellStyle(3)}">${p.openingDebit > 0 ? fmtColNum(p.openingDebit, 3) : '-'}</td>`;
        cells[4] = `<td class="num" style="${getCellStyle(4)}">${p.openingCredit > 0 ? fmtColNum(p.openingCredit, 4) : '-'}</td>`;
        cells[5] = `<td class="num" style="${getCellStyle(5)}">${p.debit > 0 ? fmtColNum(p.debit, 5) : '-'}</td>`;
        cells[6] = `<td class="num" style="${getCellStyle(6)}">${p.credit > 0 ? fmtColNum(p.credit, 6) : '-'}</td>`;
        cells[7] = `<td style="max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; ${getCellStyle(7)}" title="${p.notes || ''}">${p.notes || '-'}</td>`;
        
        let orderedCells = window.colOrder.map(idx => cells[idx]).join('');
        rows += `<tr>${orderedCells}</tr>`;
    });

    tbody.innerHTML = rows;

    document.getElementById('kpi-dr').textContent = fmtColNum(sumCust, 5);
    document.getElementById('kpi-cr').textContent = fmtColNum(sumSupp, 6);
    
    const net = sumCust - sumSupp;
    document.getElementById('kpi-bal').textContent = fmtColNum(Math.abs(net), 5) + (net >= 0 ? ' (لنا)' : ' (علينا)');

    document.getElementById('analytics-dashboard').style.display = 'block';
    document.getElementById('actions-panel').style.display = 'flex';
    document.getElementById('print-container').style.display = 'block';

    showToast(`✅ تم تجميع أرصدة ${results.length} حساب بنجاح`, 'success');
    
    bindResizers();
    bindNumberFocus();
    bindContextMenu();
    bindColumnReorder();
}

function exportExcel() {
    const table = document.getElementById('balances-table');
    const visibleIndices = [];
    const headers = Array.from(table.querySelectorAll('thead th'))
        .filter((th, idx) => {
            if (th.style.display !== 'none') {
                visibleIndices.push(idx);
                return true;
            }
            return false;
        })
        .map(th => th.innerText.replace('اسحب لتغيير عرض العمود (Double click للإرجاع)', '').trim());

    const rows = document.querySelectorAll('#balances-tbody tr:not(.empty-state)');
    if (rows.length === 0) return showToast('لا توجد بيانات للتصدير', 'warning');

    let csv = '\uFEFF';
    csv += headers.map(h => `"${h}"`).join(',') + '\n';
    
    rows.forEach(r => {
        const cells = Array.from(r.querySelectorAll('td'));
        const rowData = visibleIndices.map(idx => {
            let text = cells[idx].innerText.replace('(عميل)', '').replace('(مورد)', '').trim();
            if (text === '-') text = '0';
            return `"${text}"`;
        });
        csv += rowData.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
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

