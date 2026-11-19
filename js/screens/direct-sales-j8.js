(function() {
    if (window.__SPLIT_CONSTITUTION14_LOCK__) return;
    window.__SPLIT_CONSTITUTION14_LOCK__ = true;

    // 1. التقرير العالمي وكشف حساب وتاريخ عمليات التقسيم (مع فلاتر البرق + رصيد أول المدة + الرصيد التراكمي المتحرك + Drill-Down بضغطة زر)
    window.openSplitGlobalLedgerReportModal = function() {
        let modal = document.getElementById('split-global-ledger-report-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-global-ledger-report-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); z-index:999982; align-items:center; justify-content:center; padding:14px;';
            document.body.appendChild(modal);
        }
        const today = new Date().toISOString().slice(0, 10);
        const monthStart = today.slice(0, 8) + '01';
        modal.innerHTML = `
            <div class="modal-card" style="max-width:1020px; width:100%; padding:18px; box-shadow:0 28px 70px rgba(0,0,0,0.85); max-height:92vh; overflow-y:auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(56,189,248,0.45); padding-bottom:10px; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                    <div>
                        <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#38bdf8;">📊 التقرير العالمي وكشف حساب الفواتير والأذونات المقسمة (Drill-Down)</h3>
                        <small style="color:#cbd5e1; font-weight:800;">اضغط على أي سطر في التقرير لفتح أصل الفاتورة أو الإذن كشاشة وليدة (Drill-Down) في نفس جزء الثانية</small>
                    </div>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <button type="button" onclick="window.exportSplitReportToExcel()" style="padding:6px 12px; border-radius:8px; background:rgba(16,185,129,0.2); border:2px solid #10b981; color:#6ee7b7; font-weight:900; cursor:pointer;">📗 تصدير Excel</button>
                        <button type="button" onclick="window.exportSplitReportToPdf()" style="padding:6px 12px; border-radius:8px; background:rgba(56,189,248,0.2); border:2px solid #38bdf8; color:#7dd3fc; font-weight:900; cursor:pointer;">📑 طباعة A4 / PDF</button>
                        <button type="button" onclick="window.exportSplitReportToPdf(true)" style="padding:6px 12px; border-radius:8px; background:rgba(245,158,11,0.2); border:2px solid #f59e0b; color:#fde68a; font-weight:900; cursor:pointer;">🧾 كاشير 80mm</button>
                        <button type="button" onclick="window.shareCurrentSplitViaWhatsApp && window.shareCurrentSplitViaWhatsApp()" style="padding:6px 12px; border-radius:8px; background:rgba(34,197,94,0.2); border:2px solid #22c55e; color:#4ade80; font-weight:900; cursor:pointer;">📲 واتساب</button>
                        <button type="button" onclick="document.getElementById('split-global-ledger-report-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:6px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
                    </div>
                </div>

                <!-- فلاتر البرق اللحظية (التاريخ، العميل، المخزن، الصنف، المستخدم) -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(145px, 1fr)); gap:8px; margin-bottom:12px; background:rgba(2,6,23,0.55); padding:10px; border-radius:12px; border:1.5px solid rgba(148,163,184,0.3);">
                    <div>
                        <label style="font-size:0.74rem; font-weight:900; display:block; margin-bottom:3px;">📅 من تاريخ:</label>
                        <input type="date" id="split-rep-from" value="${monthStart}" onchange="window.renderSplitGlobalLedgerRows()" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:800;">
                    </div>
                    <div>
                        <label style="font-size:0.74rem; font-weight:900; display:block; margin-bottom:3px;">📅 إلى تاريخ:</label>
                        <input type="date" id="split-rep-to" value="${today}" onchange="window.renderSplitGlobalLedgerRows()" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:800;">
                    </div>
                    <div>
                        <label style="font-size:0.74rem; font-weight:900; display:block; margin-bottom:3px;">👤 العميل / المورد:</label>
                        <input type="text" id="split-rep-cust" placeholder="بحث بالعميل..." oninput="window.renderSplitGlobalLedgerRows()" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:800;">
                    </div>
                    <div>
                        <label style="font-size:0.74rem; font-weight:900; display:block; margin-bottom:3px;">📦 الصنف / المخزن:</label>
                        <input type="text" id="split-rep-item" placeholder="بحث بالصنف أو المخزن..." oninput="window.renderSplitGlobalLedgerRows()" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:800;">
                    </div>
                    <div>
                        <label style="font-size:0.74rem; font-weight:900; display:block; margin-bottom:3px;">👨‍💼 المستخدم:</label>
                        <input type="text" id="split-rep-user" placeholder="اسم الموظف..." oninput="window.renderSplitGlobalLedgerRows()" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:800;">
                    </div>
                </div>

                <div id="split-rep-summary-strip" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:10px; padding:8px 12px; border-radius:10px; background:rgba(16,185,129,0.12); border:1.5px solid #10b981; font-weight:900; font-size:0.85rem;"></div>

                <div style="overflow-x:auto;">
                    <table id="split-global-ledger-table" class="pos-grid" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="padding:7px; text-align:right;">🧾 رقم المستند</th>
                                <th style="padding:7px; text-align:center;">📅 التاريخ</th>
                                <th style="padding:7px; text-align:right;">👤 العميل والمخزن</th>
                                <th style="padding:7px; text-align:right;">📦 الأصناف والوحدات</th>
                                <th style="padding:7px; text-align:center;">💰 قيمة المستند</th>
                                <th style="padding:7px; text-align:center;">📈 الرصيد التراكمي المتحرك</th>
                                <th style="padding:7px; text-align:center;">🔍 التعمق (Drill-Down)</th>
                            </tr>
                        </thead>
                        <tbody id="split-global-ledger-tbody"></tbody>
                    </table>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
        window.renderSplitGlobalLedgerRows();
    };

    window.getFilteredSplitLedgerData = function() {
        const fromD = (document.getElementById('split-rep-from')?.value || '').trim();
        const toD   = (document.getElementById('split-rep-to')?.value || '').trim();
        const custQ = (document.getElementById('split-rep-cust')?.value || '').trim().toLowerCase();
        const itemQ = (document.getElementById('split-rep-item')?.value || '').trim().toLowerCase();
        const userQ = (document.getElementById('split-rep-user')?.value || '').trim().toLowerCase();

        let allSales = [];
        try { allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]') || []; } catch (e) {}
        if (!allSales.length && window.activeSplitInvoice) {
            allSales = [window.activeSplitInvoice];
        }

        let openingBalance = 0;
        const matched = [];

        allSales.forEach(function(inv) {
            if (inv.deleted && !inv.wasSplit) return;
            const d = String(inv.date || (inv.timestamp ? inv.timestamp.slice(0, 10) : new Date().toISOString().slice(0, 10))).slice(0, 10);
            const val = parseFloat(inv.grandTotal || inv.total || 0) || 0;
            const cName = String(inv.customerName || 'عميل نقدي');
            const wh = String(inv.warehouseName || 'المخزن الرئيسي');
            const uName = String(inv.createdByName || inv.splitByName || 'المسؤول');
            const itemsStr = (inv.items || []).map(function(it) { return (it.name || it.itemName || '') + ' (' + (it.qty ?? it.quantity ?? 0) + ')'; }).join(' ، ');

            if (fromD && d < fromD) {
                if (!inv.wasSplit) openingBalance += val;
                return;
            }
            if (toD && d > toD) return;
            if (custQ && cName.toLowerCase().indexOf(custQ) === -1) return;
            if (itemQ && itemsStr.toLowerCase().indexOf(itemQ) === -1 && wh.toLowerCase().indexOf(itemQ) === -1) return;
            if (userQ && uName.toLowerCase().indexOf(userQ) === -1) return;

            matched.push({
                inv: inv,
                invoiceNo: String(inv.invoiceNo || inv.id || ''),
                date: d,
                customerName: cName,
                warehouseName: wh,
                userName: uName,
                itemsSummary: itemsStr || '—',
                amount: val,
                currency: inv.currency || 'SDG'
            });
        });

        let runningBalance = openingBalance;
        matched.forEach(function(row) {
            runningBalance = +(runningBalance + row.amount).toFixed(2);
            row.runningBalance = runningBalance;
        });

        return { openingBalance: +openingBalance.toFixed(2), rows: matched, closingBalance: +runningBalance.toFixed(2) };
    };

    window.renderSplitGlobalLedgerRows = function() {
        const tbody = document.getElementById('split-global-ledger-tbody');
        const strip = document.getElementById('split-rep-summary-strip');
        if (!tbody) return;
        const data = window.getFilteredSplitLedgerData();

        if (strip) {
            strip.innerHTML = `
                <span>🏁 رصيد أول المدة: <strong style="color:#38bdf8; font-family:monospace;">${data.openingBalance.toLocaleString()}</strong></span>
                <span>📊 عدد المستندات المطابقة: <strong style="color:#fde047; font-family:monospace;">${data.rows.length}</strong></span>
                <span>💰 الرصيد التراكمي الختامي: <strong style="color:#6ee7b7; font-family:monospace; font-size:1rem;">${data.closingBalance.toLocaleString()} SDG</strong></span>
            `;
        }

        const openingRowHtml = `
            <tr style="background:rgba(56,189,248,0.12) !important; font-weight:900;">
                <td colspan="4" style="padding:7px; text-align:right; color:#38bdf8;">🏁 رصيد أول المدة قبل الفترة المحددة</td>
                <td style="padding:7px; text-align:center; font-family:monospace;">—</td>
                <td style="padding:7px; text-align:center; font-family:monospace; color:#38bdf8; font-weight:900;">${data.openingBalance.toLocaleString()}</td>
                <td style="padding:7px; text-align:center;">—</td>
            </tr>
        `;

        const rowsHtml = data.rows.slice(0, 80).map(function(r) {
            const escNo = r.invoiceNo.replace(/'/g, "\\'");
            return `
                <tr onclick="window.drillDownToSplitVoucherDetails('${escNo}')" style="cursor:pointer;" title="اضغط للتعمق (Drill-Down) وفتح أصل الفاتورة أو الإذن">
                    <td style="padding:7px; font-family:monospace; font-weight:900; color:#38bdf8;">#${r.invoiceNo}</td>
                    <td style="padding:7px; text-align:center; font-family:monospace; font-weight:800;">${r.date}</td>
                    <td style="padding:7px; font-weight:900;">${r.customerName}<br><small style="color:#94a3b8;">${r.warehouseName} • ${r.userName}</small></td>
                    <td style="padding:7px; font-size:0.8rem; font-weight:800;">${r.itemsSummary}</td>
                    <td style="padding:7px; text-align:center; font-family:monospace; font-weight:900; color:#fcd34d;">${r.amount.toLocaleString()} ${r.currency}</td>
                    <td style="padding:7px; text-align:center; font-family:monospace; font-weight:900; color:#6ee7b7;">${r.runningBalance.toLocaleString()}</td>
                    <td style="padding:7px; text-align:center;">
                        <button type="button" onclick="event.stopPropagation(); window.drillDownToSplitVoucherDetails('${escNo}')" style="padding:3px 9px; border-radius:7px; font-size:0.74rem; background:rgba(56,189,248,0.2); border:1.5px solid #38bdf8; color:#7dd3fc; font-weight:900; cursor:pointer;">🔍 فتح الأصل</button>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.innerHTML = openingRowHtml + (rowsHtml || `<tr><td colspan="7" style="padding:12px; text-align:center; font-weight:800; color:#94a3b8;">لا توجد حركات مطابقة للفلاتر الحالية</td></tr>`);
    };

    // 2. التعمق بضغطة زر (One-Click Drill-Down Modal) لفتح أصل الفاتورة أو السند كشاشة وليدة بنفس السمة وسُمك الحواف (+2)
    window.drillDownToSplitVoucherDetails = function(invoiceNo) {
        let allSales = [];
        try { allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]') || []; } catch (e) {}
        const inv = allSales.find(function(x) { return String(x.invoiceNo || x.id) === String(invoiceNo); }) || window.activeSplitInvoice;
        if (!inv) {
            if (typeof showToast === 'function') showToast('ℹ️ لم يتم العثور على تفاصيل المستند #' + invoiceNo, 'info');
            return;
        }
        let modal = document.getElementById('split-drilldown-voucher-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-drilldown-voucher-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.86); z-index:999988; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const escNo = String(inv.invoiceNo || inv.id || '').replace(/'/g, "\\'");
        const itemsRows = (inv.items || []).map(function(it, idx) {
            const m = window.resolveSplitItemUnitMeta ? window.resolveSplitItemUnitMeta(it) : { name: it.name || 'صنف', primaryUnit: it.unit || 'وحدة', price: it.price || 0 };
            const q = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
            return `
                <tr>
                    <td style="padding:6px; text-align:center; font-weight:900;">${idx + 1}</td>
                    <td style="padding:6px; font-weight:900;">${m.name}</td>
                    <td style="padding:6px; text-align:center; font-family:monospace; font-weight:900; color:#38bdf8;">${q} ${m.primaryUnit}</td>
                    <td style="padding:6px; text-align:center; font-family:monospace; font-weight:900;">${m.price.toLocaleString()}</td>
                    <td style="padding:6px; text-align:center; font-family:monospace; font-weight:900; color:#10b981;">${(q * m.price).toLocaleString()}</td>
                </tr>
            `;
        }).join('');

        modal.innerHTML = `
            <div class="modal-card" style="max-width:680px; width:100%; padding:20px; box-shadow:0 28px 70px rgba(0,0,0,0.88);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #38bdf8; padding-bottom:8px; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:1.12rem; font-weight:900; color:#38bdf8;">🔍 التعمق في أصل المستند (Drill-Down): #${inv.invoiceNo || inv.id}</h3>
                    <button type="button" onclick="document.getElementById('split-drilldown-voucher-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕</button>
                </div>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-bottom:12px; font-size:0.85rem; font-weight:900;">
                    <div>👤 العميل: <strong style="color:#fde047;">${inv.customerName || 'عميل نقدي'}</strong></div>
                    <div>📅 التاريخ: <strong style="font-family:monospace;">${inv.date || '—'}</strong></div>
                    <div>💰 الصافي: <strong style="color:#10b981; font-family:monospace;">${(parseFloat(inv.grandTotal || inv.total || 0)).toLocaleString()} ${inv.currency || 'SDG'}</strong></div>
                </div>
                <table class="pos-grid" style="width:100%; border-collapse:collapse; margin-bottom:14px;">
                    <thead>
                        <tr>
                            <th style="padding:6px;">#</th>
                            <th style="padding:6px; text-align:right;">الصنف</th>
                            <th style="padding:6px;">الكمية</th>
                            <th style="padding:6px;">السعر</th>
                            <th style="padding:6px;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>${itemsRows}</tbody>
                </table>
                <div style="display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;">
                    <button type="button" onclick="document.getElementById('split-drilldown-voucher-modal').style.display='none'; document.getElementById('split-global-ledger-report-modal') && (document.getElementById('split-global-ledger-report-modal').style.display='none'); if(typeof prepareInvoiceForSplit==='function') prepareInvoiceForSplit('${escNo}');" style="padding:7px 16px; border-radius:9px; background:linear-gradient(135deg,#10b981,#059669); border:2px solid #34d399; color:#fff; font-weight:900; cursor:pointer;">✂️ فتح في لوحة التقسيم</button>
                    <button type="button" onclick="if(typeof window.openSplitGenealogyModal==='function') window.openSplitGenealogyModal('${escNo}');" style="padding:7px 14px; border-radius:9px; background:rgba(236,72,153,0.2); border:2px solid #ec4899; color:#fbcfe8; font-weight:900; cursor:pointer;">🌳 شجرة عائلة الإذن</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    // 3. التصدير الفوري النظيف إلى Excel (CSV UTF-8 BOM) و PDF (A4 / 80mm)
    window.exportSplitReportToExcel = function() {
        const data = window.getFilteredSplitLedgerData();
        const lines = [
            ['رقم المستند', 'التاريخ', 'العميل', 'المخزن', 'الموظف', 'الأصناف', 'المبلغ', 'العملة', 'الرصيد التراكمي'].join(',')
        ];
        data.rows.forEach(function(r) {
            const esc = function(v) { return '"' + String(v ?? '').replace(/"/g, '""') + '"'; };
            lines.push([
                esc(r.invoiceNo),
                esc(r.date),
                esc(r.customerName),
                esc(r.warehouseName),
                esc(r.userName),
                esc(r.itemsSummary),
                r.amount,
                esc(r.currency),
                r.runningBalance
            ].join(','));
        });
        const csvContent = '\uFEFF' + lines.join('\r\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'كشف_تقسيم_الفواتير_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        setTimeout(function() { document.body.removeChild(a); URL.revokeObjectURL(url); }, 200);
        if (typeof showToast === 'function') showToast('📗 تم تصدير التقرير النظيف إلى Excel بنجاح', 'success');
    };

    window.exportSplitReportToPdf = function(isThermal80mm) {
        const data = window.getFilteredSplitLedgerData();
        const w = window.open('', '_blank', 'width=920,height=760');
        if (!w) return;
        const rowsHtml = data.rows.map(function(r, i) {
            return `<tr>
                <td style="padding:6px; border:1px solid #000; text-align:center;">${i + 1}</td>
                <td style="padding:6px; border:1px solid #000; font-family:monospace; font-weight:900;">#${r.invoiceNo}</td>
                <td style="padding:6px; border:1px solid #000; text-align:center;">${r.date}</td>
                <td style="padding:6px; border:1px solid #000; font-weight:800;">${r.customerName}</td>
                <td style="padding:6px; border:1px solid #000; text-align:center; font-weight:900;">${r.amount.toLocaleString()}</td>
                <td style="padding:6px; border:1px solid #000; text-align:center; font-weight:900;">${r.runningBalance.toLocaleString()}</td>
            </tr>`;
        }).join('');
        w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>تقرير كشف تقسيم الفواتير</title>
            <style>
                @page { size: ${isThermal80mm ? '80mm auto' : 'A4 portrait'}; margin: ${isThermal80mm ? '3mm' : '10mm'}; }
                body { font-family: 'Cairo', 'Tajawal', sans-serif; color:#000; max-width: ${isThermal80mm ? '76mm' : '210mm'}; margin:0 auto; font-size: ${isThermal80mm ? '10.5px' : '13px'}; }
                table { width:100%; border-collapse:collapse; margin-top:10px; }
            </style></head><body>
            <h2 style="margin:0 0 6px; text-align:center;">📊 كشف حركة تقسيم الفواتير والأذونات</h2>
            <div style="text-align:center; font-weight:800; margin-bottom:8px;">رصيد أول المدة: ${data.openingBalance.toLocaleString()} | الرصيد الختامي التراكمي: ${data.closingBalance.toLocaleString()} SDG</div>
            <table>
                <thead><tr style="background:#e2e8f0;"><th style="padding:6px; border:1px solid #000;">#</th><th style="padding:6px; border:1px solid #000;">المستند</th><th style="padding:6px; border:1px solid #000;">التاريخ</th><th style="padding:6px; border:1px solid #000;">العميل</th><th style="padding:6px; border:1px solid #000;">المبلغ</th><th style="padding:6px; border:1px solid #000;">التراكمي</th></tr></thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <script>window.onload = function(){ setTimeout(function(){ window.print(); }, 300); };<\/script>
            <\/body><\/html>`);
        w.document.close();
    };

    // 4. حصر إيماءات الجوال في 3 حركات أساسية بسيطة فقط (Touch Gesture Trinity: Edge Swipe to Return, Pull Down to Dismiss, Row Swipe for Actions)
    let touchStartX = 0, touchStartY = 0, touchStartTarget = null;
    document.addEventListener('touchstart', function(e) {
        if (!e.touches || e.touches.length !== 1) return; // منع أي إيماءات متعددة الأصابع معقدة
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        touchStartTarget = e.target;
    }, { passive: true });

    document.addEventListener('touchend', function(e) {
        if (!e.changedTouches || e.changedTouches.length !== 1) return;
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;

        const dx = e.changedTouches[0].clientX - touchStartX;
        const dy = e.changedTouches[0].clientY - touchStartY;

        // الحركة (1): السحب من الحافة للرجوع (Edge Swipe to Return)
        if ((touchStartX < 28 || touchStartX > window.innerWidth - 28) && Math.abs(dx) > 85 && Math.abs(dy) < 45) {
            if (navigator.vibrate) try { navigator.vibrate(15); } catch (err) {}
            if (window.activeSplitInvoice && typeof cancelCurrentSplit === 'function') {
                cancelCurrentSplit();
            } else if (typeof showSalesHub === 'function') {
                showSalesHub();
            }
            return;
        }

        // الحركة (2): السحب للأسفل لإغلاق النافذة الوليدة (Pull Down to Dismiss Modal)
        const openModalCard = touchStartTarget && touchStartTarget.closest && touchStartTarget.closest('.modal-overlay .modal-card');
        if (openModalCard && dy > 85 && Math.abs(dx) < 55) {
            const overlay = openModalCard.closest('.modal-overlay');
            if (overlay && overlay.id !== 'split-idle-pin-lock-overlay') {
                if (navigator.vibrate) try { navigator.vibrate(12); } catch (err) {}
                overlay.style.display = 'none';
                return;
            }
        }

        // الحركة (3): سحب الصف لإجراءات القائمة السريعة (Row Swipe for Actions)
        const rowEl = touchStartTarget && touchStartTarget.closest && touchStartTarget.closest('#split-items-body tr, #split-invoices-body tr');
        if (rowEl && Math.abs(dx) > 75 && Math.abs(dy) < 40) {
            if (navigator.vibrate) try { navigator.vibrate(12); } catch (err) {}
            const existingBar = rowEl.querySelector('.c14-row-swipe-actions');
            if (existingBar) {
                existingBar.remove();
            } else {
                const firstTd = rowEl.querySelector('td');
                if (firstTd) {
                    const bar = document.createElement('div');
                    bar.className = 'c14-row-swipe-actions';
                    bar.innerHTML = `
                        <button type="button" onclick="window.distributeSplitEqually && window.distributeSplitEqually()" style="padding:2px 8px; font-size:0.72rem; border-radius:6px; background:#0ea5e9; color:#fff; border:none; font-weight:900;">⚖️ بالتساوي</button>
                        <button type="button" onclick="window.openSplitInspectionSortingModal && window.openSplitInspectionSortingModal()" style="padding:2px 8px; font-size:0.72rem; border-radius:6px; background:#10b981; color:#fff; border:none; font-weight:900;">🔬 فحص</button>
                        <button type="button" onclick="this.parentElement.remove()" style="padding:2px 6px; font-size:0.72rem; border-radius:6px; background:#ef4444; color:#fff; border:none; font-weight:900;">✕</button>
                    `;
                    firstTd.appendChild(bar);
                }
            }
        }
    }, { passive: true });

    // 5. حارس رفع الخانة النشطة فوق كيبورد الجوال (Auto-Scroll above Mobile Virtual Keyboard)
    function scrollActiveInputAboveVirtualKeyboard(el) {
        if (!el || (el.tagName !== 'INPUT' && el.tagName !== 'SELECT' && el.tagName !== 'TEXTAREA')) return;
        if (!el.closest || !el.closest('#split-invoice-view, .modal-overlay')) return;
        setTimeout(function() {
            try {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch (e) {}
        }, 220);
    }

    document.addEventListener('focusin', function(e) {
        if (window.innerWidth <= 920 || ('ontouchstart' in window)) {
            scrollActiveInputAboveVirtualKeyboard(e.target);
        }
    }, true);

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function() {
            const activeEl = document.activeElement;
            if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'SELECT')) {
                scrollActiveInputAboveVirtualKeyboard(activeEl);
            }
        });
    }
})();
