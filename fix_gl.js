const fs = require('fs');
let code = fs.readFileSync('c:\\ELSAJANA\\magaies\\js\\general-ledger.js', 'utf8');

let lines = code.split('\n');
let renderTableStart = lines.findIndex(l => l.includes('function renderTable(txns, acctFilter, fromDate, toDate)'));
let exportCSVStart = lines.findIndex(l => l.includes('function exportCSV()'));

const correctRenderTable = `function renderTable(txns, acctFilter, fromDate, toDate) {
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
        ths += \`<th style="width:\${c.width};">\${displayName}<div class="resize-handle"></div></th>\`;
    });
    thead.innerHTML = \`<tr>\${ths}</tr>\`;

    if (txns.length === 0) {
        tbody.innerHTML = \`<tr><td colspan="\${activeColsCount}" class="empty-state"><span style="font-size:3rem;display:block;margin-bottom:15px;">🔍</span><div>لا توجد بيانات تطابق الفلاتر المحددة</div></td></tr>\`;
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

        let rowHtml = \`<tr title="\${t.isOpening ? '' : 'انقر لعرض المستند (Drill-down)'}" \${t.isOpening ? '' : \`onclick="openDrillDown('\${t.type}', '\${t.ref}')"\`} style="\${t.isOpening ? 'background:rgba(255,255,255,0.03);' : ''}">\`;

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
                    val = \`<div class="num-cell-wrapper \${currentNumFont}" onclick="toggleReviewMode(event)" oncontextmenu="showContextMenu(event, '\${t.type}', '\${t.ref}')">\${formatted}</div>\`;
                }
            }

            let style = [];
            if (c.bg) style.push(\`background-color:\${c.bg} !important\`);
            if (c.color) style.push(\`color:\${c.color} !important\`);
            if (c.bold || t.isOpening) style.push(\`font-weight:bold\`);
            if (c.size) style.push(\`font-size:\${c.size}\`);
            
            if (['statement', 'note'].includes(c.id)) {
                style.push('max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;');
            }
            if (['debit', 'debitF', 'credit', 'creditF'].includes(c.id) && (typeof val === 'string' && val.indexOf('<div') === -1)) style.push('font-family:monospace');

            rowHtml += \`<td style="\${style.join('; ')}" title="\${(typeof val === 'string' && val.includes('<')) ? '' : val}">\${val}</td>\`;
        });

        rowHtml += \`</tr>\`;
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
    document.getElementById('print-meta').innerHTML = \`
        <span>الحساب: <strong>\${accName}</strong></span>
        <span>الفترة: من <strong>\${fromDate}</strong> إلى <strong>\${toDate}</strong></span>
        <span>تاريخ الطباعة: <strong>\${new Date().toLocaleDateString('ar-SD')}</strong></span>
    \`;

    showToast(\`✅ اكتمل التحليل: \${txns.length} حركة مسجلة\`, 'success');
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
    win.document.write(\`
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
            \${printHeader}
            \${tableHtml}
            <div style="margin-top: 30px; text-align: center;" class="no-print">
                <button onclick="window.print()" style="padding: 10px 20px; background: #38bdf8; border: none; border-radius: 5px; color: #fff; cursor: pointer; font-family: inherit; font-weight: bold; margin: 10px;">🖨️ طباعة</button>
                <button onclick="window.close()" style="padding: 10px 20px; background: #94a3b8; border: none; border-radius: 5px; color: #fff; cursor: pointer; font-family: inherit; font-weight: bold; margin: 10px;">❌ إغلاق</button>
            </div>
        </body>
        </html>
    \`);
    win.document.close();
}
`;

lines.splice(renderTableStart, exportCSVStart - renderTableStart, correctRenderTable);
fs.writeFileSync('c:\\ELSAJANA\\magaies\\js\\general-ledger.js', lines.join('\n'));
console.log('Restored correctly!');
