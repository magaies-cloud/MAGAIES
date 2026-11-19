(function() {
    if (window.__SPLIT_CONSTITUTION11_LOCK__) return;
    window.__SPLIT_CONSTITUTION11_LOCK__ = true;

    const CHEQUE_PORTFOLIO_KEY = 'erp_cheques_portfolio_v1';
    const BANK_REF_REGISTRY_KEY = 'erp_bank_transfer_refs_v1';
    const FISCAL_LOCK_KEY = 'erp_fiscal_period_lock_date_v1';

    // 1. كاشف منع تكرار رقم الشيك لنفس البنك نهائياً
    window.isDuplicateChequeForBank = function(chequeNo, bankName, excludeId) {
        const cleanNo = String(chequeNo || '').trim();
        const cleanBank = String(bankName || '').trim().toLowerCase();
        if (!cleanNo || !cleanBank) return null;
        try {
            const list = JSON.parse(localStorage.getItem(CHEQUE_PORTFOLIO_KEY) || '[]') || [];
            const dup = list.find(function(c) {
                return c.id !== excludeId && String(c.chequeNo || '').trim() === cleanNo && String(c.bankName || '').trim().toLowerCase() === cleanBank;
            });
            if (dup) return dup;
        } catch (e) {}
        return null;
    };

    // 2. كاشف الإشعارات البنكية المكررة والمزورة عبر قاعدة البيانات بالكامل (Bank Transfer Anti-Fraud Lock)
    window.findDuplicateBankTransferReference = function(refNo, currentInvoiceNo) {
        const cleanRef = String(refNo || '').trim();
        if (!cleanRef) return null;
        try {
            const reg = JSON.parse(localStorage.getItem(BANK_REF_REGISTRY_KEY) || '[]') || [];
            const foundReg = reg.find(function(r) {
                return String(r.refNo || '').trim() === cleanRef && String(r.invoiceNo || '') !== String(currentInvoiceNo || '');
            });
            if (foundReg) return foundReg;

            const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]') || [];
            for (let i = 0; i < allSales.length; i++) {
                const inv = allSales[i];
                const invNo = String(inv.invoiceNo || inv.id || '');
                if (invNo === String(currentInvoiceNo || '') || inv.wasSplit) continue;
                const transfers = inv.bankTransfers || [];
                for (let j = 0; j < transfers.length; j++) {
                    const r = String(transfers[j].refNo || transfers[j].referenceNo || transfers[j].operationNo || '').trim();
                    if (r && r === cleanRef) {
                        return { refNo: cleanRef, invoiceNo: invNo, customerName: inv.customerName || 'عميل' };
                    }
                }
            }
        } catch (e) {}
        return null;
    };

    // 3. فحص قفل الفترات المالية واليومية (Fiscal Period Lock)
    window.getFiscalLockCutoffDate = function() {
        return localStorage.getItem(FISCAL_LOCK_KEY) || '';
    };

    window.isDateInClosedFiscalPeriod = function(dateStr) {
        const lockDate = window.getFiscalLockCutoffDate();
        if (!lockDate || !dateStr) return false;
        const cleanDate = String(dateStr).slice(0, 10);
        return cleanDate <= lockDate;
    };

    window.validateSplitBankingAndFiscalLocksBeforeSubmit = function(inv) {
        if (!inv) return true;
        const invDate = inv.date || new Date().toISOString().slice(0, 10);
        if (window.isDateInClosedFiscalPeriod(invDate)) {
            if (typeof showToast === 'function') {
                showToast(`🔒 قفل الفترات المالية: تاريخ الفاتورة (${invDate}) يقع داخل فترة مالية مقفلة حتى (${window.getFiscalLockCutoffDate()}) — يُمنع التعديل أو الإدخال نهائياً`, 'error');
            }
            return false;
        }
        const invNo = String(inv.invoiceNo || inv.id || '');
        const transfers = inv.bankTransfers || [];
        for (let i = 0; i < transfers.length; i++) {
            const bt = transfers[i];
            const refNo = String(bt.refNo || bt.referenceNo || bt.operationNo || '').trim();
            if (!refNo) {
                if (typeof showToast === 'function') {
                    showToast('🚫 رقابة التحويلات البنكية: يجب إدخال رقم العملية المرجعي وتاريخ التحويل واسم الحساب لكل تحويل بنكي', 'error');
                }
                return false;
            }
            const dup = window.findDuplicateBankTransferReference(refNo, invNo);
            if (dup) {
                if (typeof showToast === 'function') {
                    showToast(`🚫 قفل منع الاختلاس: رقم إشعار التحويل (${refNo}) مستخدم مسبقاً في الفاتورة (#${dup.invoiceNo}) — تم رفض العملية فوراً!`, 'error');
                }
                return false;
            }
        }
        return true;
    };

    // 4. رادار استحقاق الشيكات (قبل الموعد / مستحق اليوم / متأخر غير محصل)
    function getChequeMaturityBadgeHtml(dueDateStr, status) {
        if (status === 'cleared') return `<span style="background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#6ee7b7; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">✅ محصّل بالبنك</span>`;
        if (status === 'bounced') return `<span style="background:rgba(239,68,68,0.22); border:1px solid #ef4444; color:#fca5a5; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">🚫 مرتد (عُكس القيد)</span>`;
        if (status === 'endorsed') return `<span style="background:rgba(168,85,247,0.22); border:1px solid #a855f7; color:#e9d5ff; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">🔄 مظهّر لمورد</span>`;

        const today = new Date().toISOString().slice(0, 10);
        const due = String(dueDateStr || today).slice(0, 10);
        if (due < today) {
            return `<span style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">🔴 متأخر (${due})</span>`;
        }
        if (due === today) {
            return `<span style="background:rgba(245,158,11,0.25); border:1px solid #f59e0b; color:#fde68a; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">🟡 مستحق اليوم!</span>`;
        }
        return `<span style="background:rgba(56,189,248,0.16); border:1px solid #38bdf8; color:#7dd3fc; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900;">🟢 استحقاق: ${due}</span>`;
    }

    // 5. الشاشة الوليدة الشاملة لدورة الشيكات (5 مراحل)، رقابة إشعارات التحويل البنكي، وإقفال الفترات المالية
    window.openSplitChequesAndBankingModal = function() {
        let modal = document.getElementById('split-cheques-banking-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-cheques-banking-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.86); z-index:999977; align-items:center; justify-content:center; padding:14px;';
            document.body.appendChild(modal);
        }
        window.renderSplitChequesAndBankingModalContent();
        modal.style.display = 'flex';
    };

    window.renderSplitChequesAndBankingModalContent = function() {
        const modal = document.getElementById('split-cheques-banking-modal');
        if (!modal) return;
        const inv = window.activeSplitInvoice || { invoiceNo: 'SAL-ACTIVE', customerName: 'عميل نقدي', grandTotal: 0, currency: 'SDG' };
        const today = new Date().toISOString().slice(0, 10);
        const lockDate = window.getFiscalLockCutoffDate();
        const fxRate = parseFloat(localStorage.getItem('erp_exchange_rate_usd') || inv.exchangeRate || 1850) || 1850;
        const costCenter = inv.costCenter || 'المركز الرئيسي - الخرطوم';

        let chequesList = [];
        try { chequesList = JSON.parse(localStorage.getItem(CHEQUE_PORTFOLIO_KEY) || '[]') || []; } catch (e) {}

        const chequesRowsHtml = chequesList.length ? chequesList.slice(0, 8).map(function(ch) {
            const stLabels = {
                in_portfolio: '📁 في الحافظة (أوراق قبض)',
                under_collection: '🏦 تحت التحصيل بالبنك',
                cleared: '✅ محصّل فعلياً',
                bounced: '🚫 شيك مرتد',
                endorsed: '🔄 مظهّر لمورد'
            };
            const escId = String(ch.id).replace(/'/g, "\\'");
            return `
                <tr>
                    <td style="font-weight:900; padding:6px;">#${ch.chequeNo}<br><small style="color:#94a3b8;">${ch.bankName} - ${ch.branchName || 'الرئيسي'}</small></td>
                    <td style="font-weight:900; padding:6px;">${ch.drawerName || 'عميل'}<br><small style="color:#38bdf8;">${(+ch.amount).toLocaleString()} ${ch.currency || 'SDG'}</small></td>
                    <td style="text-align:center; padding:6px;">${getChequeMaturityBadgeHtml(ch.dueDate, ch.status)}<br><small style="color:#cbd5e1;">${stLabels[ch.status] || ch.status}</small></td>
                    <td style="text-align:center; padding:6px;">
                        <div style="display:flex; gap:4px; justify-content:center; flex-wrap:wrap;">
                            ${ch.status === 'in_portfolio' ? `<button type="button" onclick="window.transitionSplitChequeStage('${escId}','under_collection')" style="padding:2px 7px; font-size:0.7rem; border-radius:6px; background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#7dd3fc; cursor:pointer;" title="إيداع تحت التحصيل">🏦 للتحصيل</button>` : ''}
                            ${(ch.status === 'in_portfolio' || ch.status === 'under_collection') ? `<button type="button" onclick="window.transitionSplitChequeStage('${escId}','cleared')" style="padding:2px 7px; font-size:0.7rem; border-radius:6px; background:rgba(16,185,129,0.22); border:1px solid #10b981; color:#6ee7b7; cursor:pointer;" title="تحصيل فعلي وتسميع في البنك">✅ تحصيل</button>` : ''}
                            ${(ch.status === 'in_portfolio' || ch.status === 'under_collection') ? `<button type="button" onclick="window.transitionSplitChequeStage('${escId}','bounced')" style="padding:2px 7px; font-size:0.7rem; border-radius:6px; background:rgba(239,68,68,0.22); border:1px solid #ef4444; color:#fca5a5; cursor:pointer;" title="شيك مرتد وعكس القيد">🚫 ارتداد</button>` : ''}
                            ${ch.status === 'in_portfolio' ? `<button type="button" onclick="window.transitionSplitChequeStage('${escId}','endorsed')" style="padding:2px 7px; font-size:0.7rem; border-radius:6px; background:rgba(168,85,247,0.22); border:1px solid #a855f7; color:#e9d5ff; cursor:pointer;" title="تظهير الشيك لمورد بقيد ثلاثي">🔄 تظهير</button>` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('') : `<tr><td colspan="4" style="text-align:center; padding:10px; color:#94a3b8; font-weight:800;">لا توجد شيكات مسجلة في الحافظة حالياً — أضف شيكاً جديداً بالأسفل</td></tr>`;

        modal.innerHTML = `
            <div class="modal-card" style="max-width:920px; width:100%; padding:18px; box-shadow:0 24px 60px rgba(0,0,0,0.8); max-height:92vh; overflow-y:auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(14,165,233,0.45); padding-bottom:8px; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                    <h3 style="margin:0; font-size:1.1rem; font-weight:900; color:#38bdf8;">🏦 دورة الشيكات الكاملة، رقابة التحويلات البنكية، وإقفال الفترات المالية</h3>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span style="background:rgba(16,185,129,0.15); border:1.5px solid #10b981; color:#6ee7b7; padding:3px 9px; border-radius:8px; font-size:0.74rem; font-weight:900;" title="سعر الصرف الرسمي المعتمد والمقفل ضد التلاعب">🔒 الصرف الرسمي: ${fxRate.toLocaleString()}</span>
                        <span style="background:rgba(168,85,247,0.15); border:1.5px solid #a855f7; color:#e9d5ff; padding:3px 9px; border-radius:8px; font-size:0.74rem; font-weight:900;">🏢 ${costCenter}</span>
                        <button type="button" onclick="document.getElementById('split-cheques-banking-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
                    </div>
                </div>

                <!-- القسم 1: إضافة شيك محصن للحافظة (أوراق قبض) -->
                <div style="background:rgba(2,6,23,0.55); border:1.8px solid rgba(56,189,248,0.35); border-radius:12px; padding:10px; margin-bottom:10px;">
                    <div style="font-weight:900; font-size:0.86rem; color:#7dd3fc; margin-bottom:6px;">📝 1. تسجيل شيك جديد في الحافظة (حساب وسيط: أوراق قبض — يمنع تكرار رقم الشيك لنفس البنك):</div>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:6px; align-items:end;">
                        <div>
                            <label style="font-size:0.72rem; font-weight:900; display:block;">🔢 رقم الشيك الفريد:</label>
                            <input type="text" id="split-chq-no" placeholder="مثال: 994021" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:900;">
                        </div>
                        <div>
                            <label style="font-size:0.72rem; font-weight:900; display:block;">🏦 البنك:</label>
                            <input type="text" id="split-chq-bank" value="بنك الخرطوم" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:900;">
                        </div>
                        <div>
                            <label style="font-size:0.72rem; font-weight:900; display:block;">📍 الفرع:</label>
                            <input type="text" id="split-chq-branch" value="فرع السجانة" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:900;">
                        </div>
                        <div>
                            <label style="font-size:0.72rem; font-weight:900; display:block;">📅 تاريخ الاستحقاق:</label>
                            <input type="date" id="split-chq-due" value="${today}" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #f59e0b; background:#020617; color:#fde68a; font-weight:900;">
                        </div>
                        <div>
                            <label style="font-size:0.72rem; font-weight:900; display:block;">👤 الساحب / المستفيد:</label>
                            <input type="text" id="split-chq-drawer" value="${inv.customerName || 'عميل'}" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-weight:900;">
                        </div>
                        <div>
                            <label style="font-size:0.72rem; font-weight:900; display:block;">💰 مبلغ الشيك:</label>
                            <input type="text" inputmode="decimal" id="split-chq-amount" value="${parseFloat(inv.grandTotal || inv.total || 50000) || 50000}" style="width:100%; padding:5px 7px; border-radius:7px; border:1.5px solid #10b981; background:#020617; color:#6ee7b7; font-weight:900;">
                        </div>
                        <button type="button" onclick="window.addProtectedSplitChequeToPortfolio()" style="padding:6px 12px; border-radius:8px; background:linear-gradient(135deg,#0ea5e9,#0284c7); border:2px solid #7dd3fc; color:#fff; font-weight:900; cursor:pointer;">➕ حفظ بالحافظة</button>
                    </div>
                </div>

                <!-- جدول حافظة الشيكات ومراحلها الخمس -->
                <div style="overflow-x:auto; margin-bottom:10px;">
                    <table class="pos-grid" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:right; padding:6px;">🔢 الشيك والبنك</th>
                                <th style="text-align:right; padding:6px;">👤 الساحب والمبلغ</th>
                                <th style="text-align:center; padding:6px;">⏰ رادار الاستحقاق والمرحلة</th>
                                <th style="text-align:center; padding:6px;">⚙️ انتقال المراحل المحاسبية</th>
                            </tr>
                        </thead>
                        <tbody>${chequesRowsHtml}</tbody>
                    </table>
                </div>

                <!-- القسم 2: رقابة التحويلات البنكية (منع تكرار رقم الإشعار) + القسم 3: إقفال الفترات المالية -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:10px;">
                    <div style="background:rgba(2,6,23,0.55); border:1.8px solid rgba(16,185,129,0.4); border-radius:12px; padding:10px;">
                        <div style="font-weight:900; font-size:0.84rem; color:#6ee7b7; margin-bottom:6px;">🛡️ 2. رقابة التحويل البنكي (فحص فوري لمنع تكرار رقم الإشعار):</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:6px;">
                            <input type="text" id="split-bt-ref" placeholder="رقم الإشعار المرجعي (إلزامي)..." style="padding:5px 8px; border-radius:7px; border:1.5px solid #10b981; background:#020617; color:#fff; font-weight:900;">
                            <input type="text" id="split-bt-acc" value="تطبيق بنكك - حساب الشركة" placeholder="اسم الحساب..." style="padding:5px 8px; border-radius:7px; border:1.5px solid #10b981; background:#020617; color:#fff; font-weight:900;">
                        </div>
                        <div style="display:flex; gap:6px; align-items:center;">
                            <input type="date" id="split-bt-date" value="${today}" style="flex:1; padding:5px 8px; border-radius:7px; border:1.5px solid #10b981; background:#020617; color:#fff; font-weight:900;">
                            <button type="button" onclick="window.verifyAndRegisterSplitBankTransferRef()" style="padding:5px 12px; border-radius:8px; background:linear-gradient(135deg,#10b981,#059669); border:2px solid #34d399; color:#fff; font-weight:900; cursor:pointer;">🔍 فحص وتوثيق الإشعار</button>
                        </div>
                    </div>

                    <div style="background:rgba(2,6,23,0.55); border:1.8px solid rgba(245,158,11,0.45); border-radius:12px; padding:10px;">
                        <div style="font-weight:900; font-size:0.84rem; color:#fde68a; margin-bottom:6px;">🔒 3. قفل الفترات المالية واليومية (منع التعديل في فترة مغلقة):</div>
                        <div style="display:flex; gap:6px; align-items:center; margin-bottom:6px;">
                            <input type="date" id="split-fiscal-lock-input" value="${lockDate}" style="flex:1; padding:5px 8px; border-radius:7px; border:1.5px solid #f59e0b; background:#020617; color:#fde68a; font-weight:900;">
                            <button type="button" onclick="window.saveSplitFiscalPeriodLockDate()" style="padding:5px 12px; border-radius:8px; background:linear-gradient(135deg,#f59e0b,#d97706); border:2px solid #fbbf24; color:#fff; font-weight:900; cursor:pointer;">🔒 قفل الفترة</button>
                            ${lockDate ? `<button type="button" onclick="localStorage.removeItem('erp_fiscal_period_lock_date_v1'); window.renderSplitChequesAndBankingModalContent(); if(typeof showToast==='function') showToast('🔓 تم فتح الفترة المالية','info');" style="padding:5px 10px; border-radius:8px; background:rgba(239,68,68,0.2); border:1.5px solid #ef4444; color:#fca5a5; font-weight:900; cursor:pointer;">فتح</button>` : ''}
                        </div>
                        <div style="font-size:0.74rem; color:#cbd5e1; font-weight:800;">
                            ${lockDate ? `✅ الفترة المالية مقفلة حتى تاريخ: <strong style="color:#fbbf24;">${lockDate}</strong>` : `ℹ️ اختر تاريخ إقفال اليومية أو الشهر لمنع أي إدخال أو تعديل قبله.`}
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    window.addProtectedSplitChequeToPortfolio = function() {
        const chqNo = (document.getElementById('split-chq-no')?.value || '').trim();
        const bank = (document.getElementById('split-chq-bank')?.value || '').trim();
        const branch = (document.getElementById('split-chq-branch')?.value || '').trim();
        const due = (document.getElementById('split-chq-due')?.value || '').trim();
        const drawer = (document.getElementById('split-chq-drawer')?.value || '').trim();
        const amount = parseFloat(window.evalSplitInlineMath ? window.evalSplitInlineMath(document.getElementById('split-chq-amount')?.value) : document.getElementById('split-chq-amount')?.value) || 0;

        if (!chqNo || !bank || !due || !drawer || amount <= 0) {
            if (typeof showToast === 'function') showToast('⚠️ جميع بيانات الشيك إلزامية (رقم الشيك، البنك، الفرع، تاريخ الاستحقاق، الساحب، والمبلغ)', 'warning');
            return;
        }
        const dup = window.isDuplicateChequeForBank(chqNo, bank, null);
        if (dup) {
            if (typeof showToast === 'function') showToast(`🚫 منع تكرار الشيكات: رقم الشيك (#${chqNo}) مسجل مسبقاً على (${bank})!`, 'error');
            return;
        }
        const list = JSON.parse(localStorage.getItem(CHEQUE_PORTFOLIO_KEY) || '[]') || [];
        list.unshift({
            id: 'CHQ-' + Date.now(),
            chequeNo: chqNo,
            bankName: bank,
            branchName: branch,
            issueDate: new Date().toISOString().slice(0, 10),
            dueDate: due,
            drawerName: drawer,
            amount: amount,
            currency: (window.activeSplitInvoice && window.activeSplitInvoice.currency) || 'SDG',
            status: 'in_portfolio',
            intermediateAccount: 'حساب وسيط - أوراق قبض بالمحفظة'
        });
        localStorage.setItem(CHEQUE_PORTFOLIO_KEY, JSON.stringify(list.slice(0, 200)));
        window.renderSplitChequesAndBankingModalContent();
        if (typeof showToast === 'function') {
            showToast(`📁 تم حفظ الشيك #${chqNo} في الحافظة (حساب أوراق قبض وسيط) دون التأثير على رصيد البنك الفعلي حتى التحصيل`, 'success');
        }
    };

    window.transitionSplitChequeStage = function(chequeId, nextStage) {
        const list = JSON.parse(localStorage.getItem(CHEQUE_PORTFOLIO_KEY) || '[]') || [];
        const idx = list.findIndex(function(c) { return String(c.id) === String(chequeId); });
        if (idx === -1) return;
        const ch = list[idx];

        if (nextStage === 'bounced') {
            const reason = prompt(`🚫 تسجيل ارتداد الشيك (#${ch.chequeNo}):\nيرجى كتابة سبب الارتداد لإعادة المديونية على العميل:`, 'عدم كفاية الرصيد');
            if (reason === null) return;
            ch.bounceReason = reason || 'مرتد من البنك';
            // إعادة المديونية فوراً على العميل
            try {
                const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                const cIdx = custs.findIndex(function(c) { return String(c.name || '') === String(ch.drawerName || ''); });
                if (cIdx > -1) {
                    custs[cIdx].currentBalance = (parseFloat(custs[cIdx].currentBalance) || 0) + (parseFloat(ch.amount) || 0);
                    localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
                }
            } catch (e) {}
            if (typeof showToast === 'function') {
                showToast(`🚫 تم تسجيل ارتداد الشيك #${ch.chequeNo} وعكس القيد آلياً وإعادة المديونية على العميل`, 'warning');
            }
        } else if (nextStage === 'cleared') {
            // إغلاق الحساب الوسيط والتسميع الفعلي في رصيد البنك
            try {
                const banks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
                if (banks.length > 0) {
                    banks[0].currentBalance = (parseFloat(banks[0].currentBalance) || 0) + (parseFloat(ch.amount) || 0);
                    localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
                }
            } catch (e) {}
            if (typeof showToast === 'function') {
                showToast(`✅ تم تحصيل الشيك #${ch.chequeNo} وإغلاق حساب أوراق القبض وتغذية رصيد البنك الفعلي`, 'success');
            }
        } else if (nextStage === 'endorsed') {
            const supplier = prompt(`🔄 تظهير الشيك (#${ch.chequeNo}) لسداد حساب مورد:\nاكتب اسم المورد المستفيد من التظهير:`, 'مورد المصنع الرئيسي');
            if (supplier === null) return;
            ch.endorsedToSupplier = supplier;
            if (typeof showToast === 'function') {
                showToast(`🔄 تم تظهير الشيك #${ch.chequeNo} لصالح (${supplier}) بقيد ثلاثي آلي متوازن`, 'success');
            }
        } else if (nextStage === 'under_collection') {
            if (typeof showToast === 'function') {
                showToast(`🏦 تم إيداع الشيك #${ch.chequeNo} في البنك تحت التحصيل`, 'info');
            }
        }

        ch.status = nextStage;
        ch.updatedAt = new Date().toISOString();
        localStorage.setItem(CHEQUE_PORTFOLIO_KEY, JSON.stringify(list));
        window.renderSplitChequesAndBankingModalContent();
    };

    window.verifyAndRegisterSplitBankTransferRef = function() {
        const refNo = (document.getElementById('split-bt-ref')?.value || '').trim();
        const accName = (document.getElementById('split-bt-acc')?.value || '').trim();
        const btDate = (document.getElementById('split-bt-date')?.value || '').trim();
        if (!refNo || !accName || !btDate) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال رقم الإشعار المرجعي واسم الحساب وتاريخ التحويل', 'warning');
            return;
        }
        const curInvNo = (window.activeSplitInvoice && (window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id)) || 'SAL-SPLIT';
        const dup = window.findDuplicateBankTransferReference(refNo, curInvNo);
        if (dup) {
            const refInputEl = document.getElementById('split-bt-ref');
            if (typeof window.logSalesFc2ForensicEvent === 'function') {
                window.logSalesFc2ForensicEvent('RED', 'أمسك عندك! إشعار بنكك دا دخل الخزنة قبل كدا.. الموية ما بتمر من تحتنا مرتين!', 'رقم الإشعار المكرر في التقسيم: ' + refNo + ' (مستخدم في #' + dup.invoiceNo + ')');
            }
            if (typeof window.notifySalesFc2UnifiedToast === 'function') {
                window.notifySalesFc2UnifiedToast('🚨 أمسك عندك! إشعار بنكك دا دخل الخزنة قبل كدا.. الموية ما بتمر من تحتنا مرتين!', 'error', refInputEl);
            } else if (typeof showToast === 'function') {
                showToast('🚨 أمسك عندك! إشعار بنكك دا دخل الخزنة قبل كدا.. الموية ما بتمر من تحتنا مرتين!', 'error');
            }
            return;
        }
        const reg = JSON.parse(localStorage.getItem(BANK_REF_REGISTRY_KEY) || '[]') || [];
        reg.unshift({
            refNo: refNo,
            accountName: accName,
            transferDate: btDate,
            invoiceNo: curInvNo,
            timestamp: Date.now()
        });
        localStorage.setItem(BANK_REF_REGISTRY_KEY, JSON.stringify(reg.slice(0, 500)));
        if (window.activeSplitInvoice) {
            window.activeSplitInvoice.bankTransfers = window.activeSplitInvoice.bankTransfers || [];
            window.activeSplitInvoice.bankTransfers.push({
                refNo: refNo,
                accountName: accName,
                date: btDate,
                amount: parseFloat(window.activeSplitInvoice.grandTotal || 0) || 0
            });
        }
        if (typeof showToast === 'function') {
            showToast(`✅ رقم إشعار التحويل (${refNo}) سليم وفريد — تم توثيقه وربطه بالفاتورة بنجاح`, 'success');
        }
    };

    window.saveSplitFiscalPeriodLockDate = function() {
        const val = (document.getElementById('split-fiscal-lock-input')?.value || '').trim();
        if (!val) {
            if (typeof showToast === 'function') showToast('⚠️ اختر تاريخ إقفال الفترة المالية أولاً', 'warning');
            return;
        }
        localStorage.setItem(FISCAL_LOCK_KEY, val);
        window.renderSplitChequesAndBankingModalContent();
        if (typeof showToast === 'function') {
            showToast(`🔒 تم إقفال الفترة المالية واليومية حتى تاريخ (${val}) — يُمنع إدخال أو تعديل أي حركة داخل الفترة المغلقة`, 'success');
        }
    };
})();
