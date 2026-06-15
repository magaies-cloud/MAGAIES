/* ═══════════════════════════════════════════════════════════
   print-engine.js — Universal Invoice Print Engine v2.0
   Shared by: direct-sales, sales-invoice, purchase, reserved-sales
   ═══════════════════════════════════════════════════════════ */

window.InvoicePrinter = (function () {

    // ── Helpers ──────────────────────────────────────────────
    function fmt(n) {
        if (!n && n !== 0) return '0';
        return Number(n).toLocaleString('en-US');
    }

    function getSettings() {
        return JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
    }

    // ── Build the full HTML document ─────────────────────────
    function buildInvoiceHTML(data) {
        const cfg = getSettings();
        const companyName = cfg.companyName || 'مؤسسة السجانة للتجارة';
        const companyPhone = cfg.phone || '';
        const companyAddress = cfg.address || '';
        const companyReg = cfg.regNumber || '';
        const currency = data.currency || cfg.currency || 'SDG';
        const printedAt = new Date().toLocaleString('ar-SD');

        // Build copies array: [customer, company, warehouse]
        const copies = [];

        // ── Customer / Company copy ───────────────────────────
        const mainCopyHtml = buildMainCopy(data, companyName, companyPhone, companyAddress, companyReg, currency);
        copies.push({ label: 'نسخة العميل', html: mainCopyHtml, color: '#1e3a5f' });
        copies.push({ label: 'نسخة الشركة', html: mainCopyHtml, color: '#065f46' });

        // ── Warehouse copy ────────────────────────────────────
        if (data.items && data.items.length > 0) {
            const whHtml = buildWarehouseCopy(data, companyName, currency);
            copies.push({ label: 'إذن المستودع', html: whHtml, color: '#4c1d95', isWarehouse: true });
        }

        // ── Assemble all copies ───────────────────────────────
        const allHtml = copies.map((copy, idx) => `
            <div class="inv-doc">
                <div class="inv-copy-header">
                    <span class="inv-copy-label" style="color:${copy.color};">📄 ${copy.label}</span>
                    <span>طُبع في: ${printedAt}</span>
                </div>
                ${copy.html}
                <div class="inv-doc-footer">
                    ${companyName}
                    ${companyPhone ? ' · ' + companyPhone : ''}
                    ${companyAddress ? ' · ' + companyAddress : ''}
                    ${companyReg ? ' · سجل تجاري: ' + companyReg : ''}
                </div>
            </div>
        `).join('<div style="height:20px;"></div>');

        return allHtml;
    }

    function buildMainCopy(data, companyName, companyPhone, companyAddress, companyReg, currency) {
        const isSales = data.type !== 'purchase';
        const typeLabel = isSales ? (data.typeLabel || 'فاتورة مبيعات') : 'فاتورة مشتريات';
        const partyLabel = isSales ? 'العميل' : 'المورد';

        // Items rows
        const itemsHtml = (data.items || []).map((item, idx) => `
            <tr>
                <td>${idx + 1}</td>
                <td class="text-right">${item.itemName || item.name || '—'}</td>
                <td>${item.selectedUnit || item.unit || '—'}</td>
                <td>${fmt(item.quantity)}</td>
                <td>${fmt(item.price)} ${currency}</td>
                <td style="font-weight:700;">${fmt(item.total || (item.quantity * item.price))} ${currency}</td>
            </tr>
        `).join('');

        // Totals
        const subtotal = data.subtotal || data.grandTotal || 0;
        const discount = data.discountAmt || 0;
        const tax      = data.taxAmt || 0;
        const addition = data.additionAmt || 0;
        const labor    = data.laborCost || 0;
        const transport = data.transportCost || 0;
        const grand    = data.grandTotal || 0;

        const totalsRows = [
            { label: 'إجمالي الأصناف', value: fmt(subtotal) + ' ' + currency, show: true },
            { label: 'الخصم', value: '(' + fmt(discount) + ') ' + currency, show: discount > 0, red: true },
            { label: 'الضريبة', value: fmt(tax) + ' ' + currency, show: tax > 0 },
            { label: 'إضافات أخرى', value: fmt(addition) + ' ' + currency, show: addition > 0 },
            { label: 'أجرة نقل', value: fmt(transport) + ' ' + currency, show: transport > 0 },
            { label: 'أجرة عمالة', value: fmt(labor) + ' ' + currency, show: labor > 0 },
        ].filter(r => r.show).map(r => `
            <div class="inv-total-row">
                <span class="label">${r.label}</span>
                <span class="value" style="${r.red ? 'color:#ef4444;' : ''}">${r.value}</span>
            </div>
        `).join('');

        const notes = data.notes ? `
            <div class="inv-notes-box">
                <div class="inv-notes-label">📝 ملاحظات:</div>
                ${data.notes}
            </div>
        ` : '';

        const payMethodMap = { cash: 'نقدي', credit: 'آجل', bank: 'تحويل بنكي', cheque: 'شيك' };
        const payMethod = payMethodMap[data.paymentMethod] || data.paymentMethod || '—';

        return `
            <div class="inv-header">
                <div class="inv-logo-section">
                    <div class="inv-logo-box">🏢</div>
                    <div>
                        <div class="inv-company-name">${companyName}</div>
                        <div class="inv-company-sub">${companyAddress || ''} ${companyPhone ? '· ' + companyPhone : ''}</div>
                    </div>
                </div>
                <div class="inv-title-section">
                    <div class="inv-type-badge">${typeLabel}</div>
                    <div class="inv-number-row">رقم: <strong>#${data.invoiceNo || '—'}</strong></div>
                    <div class="inv-number-row" style="margin-top:4px;">التاريخ: <strong>${data.date || '—'}</strong></div>
                </div>
            </div>

            <div class="inv-info-grid">
                <div class="inv-info-block">
                    <div class="inv-info-label">${partyLabel}</div>
                    <div class="inv-info-value">${data.customerName || data.customer || '—'}</div>
                </div>
                <div class="inv-info-block">
                    <div class="inv-info-label">طريقة الدفع</div>
                    <div class="inv-info-value">${payMethod}</div>
                </div>
                <div class="inv-info-block">
                    <div class="inv-info-label">المستودع</div>
                    <div class="inv-info-value">${data.warehouseName || data.warehouse || '—'}</div>
                </div>
                <div class="inv-info-block">
                    <div class="inv-info-label">العملة</div>
                    <div class="inv-info-value">${currency} ${data.exchangeRate && data.exchangeRate != 1 ? '(صرف: ' + data.exchangeRate + ')' : ''}</div>
                </div>
            </div>

            <table class="inv-table">
                <thead>
                    <tr>
                        <th>م</th>
                        <th class="text-right" style="min-width:150px;">الصنف</th>
                        <th>الوحدة</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml || '<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:20px;">لا توجد أصناف</td></tr>'}
                </tbody>
            </table>

            <div class="inv-totals-section">
                ${notes}
                <div class="inv-totals-box">
                    ${totalsRows}
                    <div class="inv-total-row grand">
                        <span class="label">💰 الإجمالي النهائي</span>
                        <span class="value">${fmt(grand)} ${currency}</span>
                    </div>
                </div>
            </div>

            <div class="inv-signatures">
                <div class="inv-sig-block">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-label">${isSales ? 'البائع / المحاسب' : 'المحاسب'}</div>
                </div>
                <div class="inv-sig-block">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-label">أمين المستودع</div>
                </div>
                <div class="inv-sig-block">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-label">توقيع ${partyLabel}</div>
                </div>
            </div>
        `;
    }

    function buildWarehouseCopy(data, companyName, currency) {
        const isSales = data.type !== 'purchase';
        const noteTitle = isSales ? 'إذن صرف مخزني' : 'إذن استلام مخزني';

        const itemsHtml = (data.items || []).map((item, idx) => `
            <tr>
                <td>${idx + 1}</td>
                <td class="text-right" style="font-weight:700;">${item.itemName || item.name || '—'}</td>
                <td>${item.selectedUnit || item.unit || '—'}</td>
                <td style="font-size:1.1rem; font-weight:900; color:#1e3a5f;">${fmt(item.quantity)}</td>
                <td style="font-size:0.85rem; color:#64748b;">${item.baseQty ? fmt(item.baseQty) + ' (وحدة أساسية)' : '—'}</td>
                <td></td>
            </tr>
        `).join('');

        return `
            <div class="inv-wh-note-header">
                🏭 ${noteTitle} — ${data.warehouseName || 'المستودع الرئيسي'}
            </div>

            <div class="inv-header" style="padding-bottom:12px; margin-bottom:14px;">
                <div>
                    <div style="font-weight:900; font-size:1rem; color:#1e3a5f;">${companyName}</div>
                </div>
                <div style="text-align:left;">
                    <div style="font-size:0.85rem;">رقم الفاتورة: <strong>#${data.invoiceNo || '—'}</strong></div>
                    <div style="font-size:0.85rem; margin-top:4px;">التاريخ: <strong>${data.date || '—'}</strong></div>
                    <div style="font-size:0.85rem; margin-top:4px;">${isSales ? 'العميل' : 'المورد'}: <strong>${data.customerName || '—'}</strong></div>
                </div>
            </div>

            <table class="inv-table">
                <thead>
                    <tr>
                        <th>م</th>
                        <th class="text-right" style="min-width:180px;">الصنف</th>
                        <th>الوحدة</th>
                        <th>الكمية المطلوبة</th>
                        <th>بالوحدة الأساسية</th>
                        <th>ملاحظات المستودع</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>

            <div class="inv-signatures" style="margin-top:40px;">
                <div class="inv-sig-block">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-label">أمين المستودع</div>
                </div>
                <div class="inv-sig-block">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-label">المستلم / السائق</div>
                </div>
                <div class="inv-sig-block">
                    <div class="inv-sig-line"></div>
                    <div class="inv-sig-label">المدير / المشرف</div>
                </div>
            </div>
        `;
    }

    // ── Show Preview Modal ───────────────────────────────────
    function showPreview(data) {
        // Remove existing
        const existing = document.getElementById('print-invoice-frame');
        if (existing) existing.remove();

        const html = buildInvoiceHTML(data);

        const overlay = document.createElement('div');
        overlay.id = 'print-invoice-frame';
        overlay.className = 'print-preview-overlay show';
        overlay.innerHTML = `
            <div class="print-preview-wrapper">
                <div class="print-toolbar">
                    <div class="print-toolbar-title">
                        🖨️ معاينة الطباعة — فاتورة #${data.invoiceNo || '—'}
                    </div>
                    <div class="print-toolbar-actions">
                        <span class="print-copy-badge">3 نسخ: عميل · شركة · مستودع</span>
                        <button class="btn-print-now" onclick="window.InvoicePrinter.doPrint()">
                            🖨️ طباعة الآن
                        </button>
                        <button class="btn-close-preview" onclick="window.InvoicePrinter.closePreview()">
                            ✕ إغلاق
                        </button>
                    </div>
                </div>
                <div style="padding: 24px; background: #f0f4f8;">
                    ${html}
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
    }

    function closePreview() {
        const el = document.getElementById('print-invoice-frame');
        if (el) el.remove();
    }

    function doPrint() {
        window.print();
    }

    // ── Public API ───────────────────────────────────────────
    return {
        /**
         * Show print preview for a given invoice data object.
         * @param {Object} data - Invoice data
         * @param {string} data.invoiceNo
         * @param {string} data.date
         * @param {string} data.customerName
         * @param {string} data.currency
         * @param {string} data.paymentMethod
         * @param {string} data.warehouseName
         * @param {number} data.grandTotal
         * @param {number} data.subtotal
         * @param {number} data.discountAmt
         * @param {number} data.taxAmt
         * @param {string} data.notes
         * @param {string} data.type  - 'sales' | 'purchase'
         * @param {string} data.typeLabel - e.g. 'فاتورة مبيعات مباشرة'
         * @param {Array}  data.items - [{itemName, unit, quantity, price, total, baseQty}]
         */
        print: showPreview,
        closePreview: closePreview,
        doPrint: doPrint
    };

})();
