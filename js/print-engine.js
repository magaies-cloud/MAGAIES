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
    function buildInvoiceHTML(data, singleCopyLabel = null) {
        const cfg = getSettings();
        const companyName = cfg.companyName || JSON.parse(localStorage.getItem('user'))?.company_name || 'الشركة';
        const companyPhone = cfg.phone || '';
        const companyAddress = cfg.address || '';
        const companyReg = cfg.regNumber || '';
        const currency = data.currency || cfg.currency || 'SDG';
        const printedAt = new Date().toLocaleString('ar-SD-u-nu-latn');

        // Build copies array: [customer/supplier, company, warehouse]
        const copies = [];
        const isPurchase = data.type === 'purchase';

        // ── Customer/Supplier & Company copy ──────────────────
        const mainCopyHtml = buildMainCopy(data, companyName, companyPhone, companyAddress, companyReg, currency);
        copies.push({
            label: isPurchase ? 'نسخة المورد' : 'نسخة العميل',
            html: mainCopyHtml,
            color: isPurchase ? '#047857' : '#1e3a5f'
        });
        copies.push({
            label: 'نسخة الشركة',
            html: mainCopyHtml,
            color: isPurchase ? '#0f766e' : '#065f46'
        });

        // ── Warehouse copy ────────────────────────────────────
        if (data.items && data.items.length > 0) {
            const whHtml = buildWarehouseCopy(data, companyName, currency);
            copies.push({
                label: 'إذن المستودع',
                html: whHtml,
                color: isPurchase ? '#7c2d12' : '#4c1d95',
                isWarehouse: true
            });
        }

        // Filter copies if a single copy is requested
        let filteredCopies = copies;
        if (singleCopyLabel) {
            filteredCopies = copies.filter(c => c.label === singleCopyLabel);
            if (filteredCopies.length === 0) filteredCopies = [copies[0]];
        }

        // ── Assemble all copies ───────────────────────────────
        const allHtml = filteredCopies.map((copy, idx) => `
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
        const cfg = getSettings();

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

        const payMethodMap = { cash: 'نقدي', credit: 'آجل', bank: 'تحويل بنكي', cheque: 'شيك', hybrid: 'دفع مشترك (مختلط)' };
        let payMethod = payMethodMap[data.paymentMethod] || data.paymentMethod || '—';
        
        if (data.paymentMethod === 'hybrid' || data.paymentMethod === 'multipay') {
            const parts = [];
            const curStr = data.currency || 'SDG';
            if (data.cashAmount > 0) parts.push(`نقدي ${data.cashAmount} ${curStr}`);
            if (data.creditAmount > 0) parts.push(`آجل ${data.creditAmount} ${curStr}`);
            if (data.bankTransfers && data.bankTransfers.length > 0) {
                const bSum = data.bankTransfers.reduce((s, b) => s + (parseFloat(b.amount) || 0), 0);
                parts.push(`بنكي ${bSum} ${curStr}`);
            }
            if (data.cheques && data.cheques.length > 0) {
                const cSum = data.cheques.reduce((s, c) => s + (parseFloat(c.amount) || 0), 0);
                parts.push(`شيكات ${cSum} ${curStr}`);
            }
            if (parts.length > 0) {
                payMethod = `دفع مشترك (${parts.join(' + ')})`;
            }
        }

        return `
            <div class="inv-header">
                <div class="inv-logo-section">
                    ${cfg.logoBase64 ? `
                        <div class="inv-logo-img-wrapper" style="max-height: 56px; max-width: 150px; display: flex; align-items: center;">
                            <img src="${cfg.logoBase64}" alt="Logo" style="max-height: 56px; max-width: 100%; object-fit: contain;">
                        </div>
                    ` : `
                        <div class="inv-logo-box">🏢</div>
                    `}
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
                ${isSales ? `
                    <div class="inv-sig-block">
                        <div class="inv-sig-line"></div>
                        <div class="inv-sig-label">البائع / المحاسب</div>
                    </div>
                    <div class="inv-sig-block">
                        <div class="inv-sig-line"></div>
                        <div class="inv-sig-label">أمين المستودع</div>
                    </div>
                    <div class="inv-sig-block">
                        <div class="inv-sig-line"></div>
                        <div class="inv-sig-label">توقيع العميل</div>
                    </div>
                ` : `
                    <div class="inv-sig-block">
                        <div class="inv-sig-line"></div>
                        <div class="inv-sig-label">توقيع المستلم</div>
                    </div>
                    <div class="inv-sig-block">
                        <div class="inv-sig-line"></div>
                        <div class="inv-sig-label">توقيع المورد</div>
                    </div>
                `}
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

    // ── Current preview data (stored for re-rendering on copy change) ──
    let _currentPreviewData = null;

    function _getSelectedCopyFilter() {
        const sel = document.getElementById('print-copy-selector');
        return sel ? sel.value : 'all';
    }

    function _refreshPreviewContent() {
        if (!_currentPreviewData) return;
        const filter = _getSelectedCopyFilter();
        const isPurchase = _currentPreviewData.type === 'purchase';
        let singleCopyLabel = null;
        if (filter === 'customer') singleCopyLabel = isPurchase ? 'نسخة المورد' : 'نسخة العميل';
        else if (filter === 'company') singleCopyLabel = 'نسخة الشركة';
        else if (filter === 'warehouse') singleCopyLabel = 'إذن المستودع';
        // else 'all' → null → show all copies

        const html = buildInvoiceHTML(_currentPreviewData, singleCopyLabel);
        const container = document.getElementById('print-preview-content');
        if (container) container.innerHTML = html;
    }

    // ── Show Preview Modal ───────────────────────────────────
    function showPreview(data) {
        // Remove existing
        const existing = document.getElementById('print-invoice-frame');
        if (existing) existing.remove();

        _currentPreviewData = data;

        // Context-aware theming: purchase vs sales
        const isPurchase = data.type === 'purchase';
        const toolbarBg = isPurchase ? 'linear-gradient(135deg, #064e3b, #065f46)' : '#1e293b';
        const previewTitle = isPurchase
            ? `📋 معاينة فاتورة المشتريات — #${data.invoiceNo || '—'}`
            : `🖨️ معاينة الطباعة — فاتورة #${data.invoiceNo || '—'}`;
        const printBtnStyle = isPurchase
            ? 'background: linear-gradient(135deg, #10b981, #059669);'
            : 'background: linear-gradient(135deg, #38bdf8, #0284c7);';
        const selectClass = isPurchase ? 'print-copy-select purchase-theme' : 'print-copy-select';
        const partyLabel = isPurchase ? '🏪 نسخة المورد' : '👤 نسخة العميل';
        const partyValue = 'customer';

        const html = buildInvoiceHTML(data);

        const overlay = document.createElement('div');
        overlay.id = 'print-invoice-frame';
        overlay.className = 'print-preview-overlay show';
        overlay.innerHTML = `
            <div class="print-preview-wrapper ${isPurchase ? 'purchase-preview' : ''}">
                <div class="print-toolbar" style="background: ${toolbarBg};">
                    <div class="print-toolbar-title">
                        ${previewTitle}
                    </div>
                    <div class="print-toolbar-actions">
                        <div class="print-copy-dropdown-wrapper">
                            <select id="print-copy-selector" class="${selectClass}" onchange="window.InvoicePrinter._refreshPreviewContent()">
                                <option value="all">📄 الكل (3 نسخ)</option>
                                <option value="${partyValue}">${partyLabel}</option>
                                <option value="company">🏢 نسخة الشركة</option>
                                <option value="warehouse">🏭 إذن المستودع</option>
                            </select>
                        </div>
                        <button class="btn-print-now" onclick="window.InvoicePrinter.doPrint()">
                            🖨️ طباعة الآن
                        </button>
                        <button class="btn-close-preview" onclick="window.InvoicePrinter.closePreview()">
                            ✕ إغلاق
                        </button>
                    </div>
                </div>
                <div id="print-preview-content" style="padding: 24px; background: ${isPurchase ? '#f0fdf4' : '#f0f4f8'};">
                    ${html}
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
    }

    function buildPromissoryNoteHTML(data) {
        const cfg = getSettings();
        const companyName = cfg.companyName || 'MAGAIES ERP';
        const dateStr = data.date || new Date().toISOString().split('T')[0];
        const creditAmt = parseFloat(data.creditAmount || 0);
        const curStr = data.currency || 'SDG';
        
        let otherPaymentsText = '';
        const grand = parseFloat(data.grandTotal || 0);
        const otherPaid = grand - creditAmt;
        if (otherPaid > 0) {
            const methods = [];
            if (data.cashAmount > 0) methods.push(`نقداً: ${data.cashAmount} ${curStr}`);
            if (data.bankTransfers && data.bankTransfers.length > 0) {
                const bSum = data.bankTransfers.reduce((s, b) => s + (parseFloat(b.amount) || 0), 0);
                methods.push(`تحويل بنكي: ${bSum} ${curStr}`);
            }
            if (data.cheques && data.cheques.length > 0) {
                const cSum = data.cheques.reduce((s, c) => s + (parseFloat(c.amount) || 0), 0);
                methods.push(`شيكات: ${cSum} ${curStr}`);
            }
            otherPaymentsText = `<p style="margin-top:10px; font-size:0.95rem; color:#475569;">علماً بأن إجمالي قيمة الفاتورة هو <strong>${grand} ${curStr}</strong>، وتم سداد الباقي وقدره <strong>${otherPaid} ${curStr}</strong> بموجب (${methods.join(' و ')}).</p>`;
        }
        
        const isFull = (Math.abs(creditAmt - grand) < 0.05);
        const amtTypeLabel = isFull ? 'كامل قيمة' : 'الجزء المتبقي من قيمة';
        
        return `
            <div class="promissory-note-container" style="direction: rtl; text-align: right; font-family: 'Cairo', sans-serif; padding: 40px; background: #fff; color: #1e293b; max-width: 800px; margin: 0 auto; border: 2px double #cbd5e1; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 25px;">
                    <h2 style="margin: 0; font-size: 1.8rem; font-weight: 900; color: #0f172a;">📜 سند لأمر / كمبيالة إقرار دين</h2>
                    <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.95rem;">محرر وفقاً للأنظمة واللوائح المعمول بها</p>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 0.95rem; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div>المبلغ: <strong>${creditAmt} ${curStr}</strong></div>
                    <div>التاريخ: <strong>${dateStr}</strong></div>
                    <div>المكان: <strong>الفرع الرئيسي</strong></div>
                </div>
                
                <div style="font-size: 1.1rem; line-height: 1.8; color: #334155; margin-bottom: 40px; text-align: justify;">
                    أنا الموقع أدناه المدين/ <strong>${data.customerName || data.customer || '..............................................................'}</strong>
                    <br>
                    أقر وأعترف بكامل أهليتي المعتبرة شرعاً وقانوناً بأنني مدين لـ: <strong>${companyName}</strong>
                    <br>
                    بمبلغ وقدره: <strong>${creditAmt} ${curStr}</strong> (فقط وقدره ..........................................................................................................)
                    <br>
                    وهو يمثل <strong>${amtTypeLabel}</strong> الفاتورة رقم <strong>#${data.invoiceNo || '—'}</strong> الصادرة بتاريخ <strong>${dateStr}</strong>.
                    ${otherPaymentsText}
                    <br>
                    وأتعهد وألتزم التزاماً غير معلق على شرط بسداد هذا المبلغ بالكامل لصالح الدائن في تاريخ الاستحقاق الموافق: <strong>${data.dueDate || '................................'}</strong>.
                </div>
                
                <div style="display: flex; gap: 30px; justify-content: space-between; margin-top: 50px; font-size: 0.95rem;">
                    <div style="flex: 1; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; background: #fafafa;">
                        <h4 style="margin: 0 0 10px 0; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; color:#0f172a;">✍️ بيانات وتوقيع المدين (المقر بالدين)</h4>
                        <div style="margin-bottom: 8px;">الاسم الكامل: ..............................................................</div>
                        <div style="margin-bottom: 8px;">رقم الهوية/الجواز: ..............................................................</div>
                        <div style="margin-bottom: 8px;">رقم الهاتف: ..............................................................</div>
                        <div style="margin-bottom: 8px;">التوقيع: ..............................................................</div>
                        <div>التاريخ: ..............................................................</div>
                    </div>
                    
                    <div style="flex: 1; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; background: #fafafa; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                        <h4 style="margin: 0 0 15px 0; color:#0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; width: 100%;">🏢 الختم الرسمي للشركة</h4>
                        <div style="width: 120px; height: 120px; border: 2px dashed #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 0.8rem;">ختم المؤسسة</div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 40px; font-size: 0.8rem; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                    تم إنشاء هذا السند تلقائياً عبر نظام المبيعات. يحق للمؤسسة اتخاذ الإجراءات القانونية اللازمة في حال عدم السداد في الموعد المحدد.
                </div>
            </div>
        `;
    }

    function showPromissoryNotePreview(data) {
        const existing = document.getElementById('print-invoice-frame');
        if (existing) existing.remove();

        const html = buildPromissoryNoteHTML(data);

        const overlay = document.createElement('div');
        overlay.id = 'print-invoice-frame';
        overlay.className = 'print-preview-overlay show';
        overlay.innerHTML = `
            <div class="print-preview-wrapper">
                <div class="print-toolbar">
                    <div class="print-toolbar-title">
                        📜 معاينة الكمبيالة / سند لأمر — فاتورة #${data.invoiceNo || '—'}
                    </div>
                    <div class="print-toolbar-actions">
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
        // Re-render with the selected copy filter before printing
        _refreshPreviewContent();
        setTimeout(() => window.print(), 100);
    }

    // ── Public API ───────────────────────────────────────────
    return {
        print: showPreview,
        printPromissoryNote: showPromissoryNotePreview,
        closePreview: closePreview,
        doPrint: doPrint,
        buildHTML: buildInvoiceHTML,
        _refreshPreviewContent: _refreshPreviewContent
    };

})();

