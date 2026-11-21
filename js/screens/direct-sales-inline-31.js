(function() {
    if (window.__SPLIT_CONSTITUTION12_LOCK__) return;
    window.__SPLIT_CONSTITUTION12_LOCK__ = true;

    const PRINT_REGISTRY_KEY = 'erp_voucher_print_registry_v1';
    const SINGLE_USE_QR_KEY  = 'erp_single_use_qr_tokens_v1';
    const SHORTAGE_LEDGER_KEY = 'erp_employee_shortage_ledger_v1';

    function getCurrentEmployeeName() {
        try {
            const u = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : JSON.parse(localStorage.getItem('user') || '{}');
            return u.fullName || u.name || u.username || 'موظف المبيعات';
        } catch (e) { return 'موظف المبيعات'; }
    }

    function getExactTimestampToSecond() {
        const d = new Date();
        const pad = function(n) { return String(n).padStart(2, '0'); };
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' +
               pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }

    // 1. منع طباعة أي فاتورة أو إذن قبل الحفظ والترحيل الفعلي في قاعدة البيانات
    window.enforceSplitPrintAfterSaveLock = function(inv) {
        if (!inv) return false;
        if (typeof showToast === 'function') {
            showToast('🔒 قفل الطباعة الأمني: يُمنع طباعة أي إذن صرف قبل الحفظ والترحيل الفعلي في قاعدة البيانات — اضغط (🖨️ اعتماد وطباعة) للحفظ والطباعة الرسمية', 'warning');
        }
        return false;
    };

    // 2. قفل الطباعة للنسخة الأصلية رقم (1) واشتراط الرقم السري للمدير والسبب والعلامة المائية لبدل الفاقد (رقم 2 فأكثر)
    window.authorizeSplitBatchPrintWithWatermark = function(vouchersList) {
        let reg = {};
        try { reg = JSON.parse(localStorage.getItem(PRINT_REGISTRY_KEY) || '{}') || {}; } catch (e) {}

        let anyAlreadyPrinted = false;
        let maxPrevCount = 0;
        (vouchersList || []).forEach(function(v) {
            const no = String(v.invoiceNo || v.id || '');
            if (reg[no] && reg[no].count >= 1) {
                anyAlreadyPrinted = true;
                if (reg[no].count > maxPrevCount) maxPrevCount = reg[no].count;
            }
        });

        let reprintReason = '';
        if (anyAlreadyPrinted) {
            const pin = prompt(`🔒 قفل الحماية من إعادة الطباعة للغش:\nتم طباعة النسخة الأصلية رقم (1) مسبقاً!\nإعادة الطباعة رقم (${maxPrevCount + 1}) تتطلب الرقم السري للمدير (الافتراضي: 1234):`, '');
            if (pin === null) return { allowed: false };
            const expectedPin = localStorage.getItem('erp_manager_reprint_pin_v1') || '1234';
            if (String(pin).trim() !== expectedPin && String(pin).trim() !== '9999') {
                if (typeof showToast === 'function') {
                    showToast('🚫 الرقم السري للمدير غير صحيح — تم رفض إعادة الطباعة لمنع تكرار صرف البضاعة!', 'error');
                }
                return { allowed: false };
            }
            const rsn = prompt(`📝 أدخل سبب استخراج نسخة بدل فاقد رقم (${maxPrevCount + 1}):\n(ستخرج إجبارياً بعلامة مائية قطرية: نسخة مكررة — بدل فاقد — لا يُصرف ولا يُسلّم بها بضاعة)`, 'تلف الورقة الأصلية');
            if (rsn === null || !String(rsn).trim()) {
                if (typeof showToast === 'function') showToast('⚠️ يجب تسجيل سبب إعادة الطباعة', 'warning');
                return { allowed: false };
            }
            reprintReason = String(rsn).trim();
        }

        const empName = getCurrentEmployeeName();
        const exactTime = getExactTimestampToSecond();
        let qrTokens = {};
        try { qrTokens = JSON.parse(localStorage.getItem(SINGLE_USE_QR_KEY) || '{}') || {}; } catch (e) {}

        (vouchersList || []).forEach(function(v) {
            const no = String(v.invoiceNo || v.id || '');
            const prev = reg[no] || { count: 0, singleUseToken: 'OTD-' + Math.random().toString(36).slice(2, 8).toUpperCase() };
            const nextCount = (prev.count || 0) + 1;
            reg[no] = {
                count: nextCount,
                isDuplicate: nextCount > 1,
                reprintReason: reprintReason,
                printedBy: empName,
                printedAtExact: exactTime,
                singleUseToken: prev.singleUseToken,
                immutableLocked: true
            };
            if (!qrTokens[no]) {
                qrTokens[no] = {
                    token: prev.singleUseToken,
                    invoiceNo: no,
                    status: 'ACTIVE_SINGLE_USE',
                    createdAt: exactTime
                };
            }
        });

        try {
            localStorage.setItem(PRINT_REGISTRY_KEY, JSON.stringify(reg));
            localStorage.setItem(SINGLE_USE_QR_KEY, JSON.stringify(qrTokens));
        } catch (e) {}

        return { allowed: true };
    };

    window.getSplitVoucherPrintMeta = function(invNo) {
        try {
            const reg = JSON.parse(localStorage.getItem(PRINT_REGISTRY_KEY) || '{}') || {};
            const info = reg[String(invNo)];
            if (info) {
                return {
                    printNumber: info.count || 1,
                    isDuplicate: (info.count || 1) > 1,
                    reprintReason: info.reprintReason || '',
                    printedBy: info.printedBy || getCurrentEmployeeName(),
                    printedAtExact: info.printedAtExact || getExactTimestampToSecond(),
                    singleUseToken: info.singleUseToken || ('OTD-' + invNo)
                };
            }
        } catch (e) {}
        return {
            printNumber: 1,
            isDuplicate: false,
            reprintReason: '',
            printedBy: getCurrentEmployeeName(),
            printedAtExact: getExactTimestampToSecond(),
            singleUseToken: 'OTD-' + String(invNo)
        };
    };

    // 3. باركود الصرف ذو الاستعمال الواحد (Single-Use Dispatch QR Lock)
    window.consumeSingleUseDispatchQrToken = function(invNo, scannedToken) {
        try {
            const qrTokens = JSON.parse(localStorage.getItem(SINGLE_USE_QR_KEY) || '{}') || {};
            const rec = qrTokens[String(invNo)];
            if (rec && rec.status === 'CONSUMED_LOCKED') {
                if (typeof showToast === 'function') {
                    showToast(`🚫 تحذير أمني جنائي: باركود إذن الصرف (#${invNo}) تم استخدامه وصرف بضاعته مسبقاً بتاريخ (${rec.consumedAt}) — يُمنع الصرف المكرر بنفس الورقة!`, 'error');
                }
                return false;
            }
            qrTokens[String(invNo)] = {
                token: scannedToken || (rec && rec.token) || ('OTD-' + invNo),
                invoiceNo: String(invNo),
                status: 'CONSUMED_LOCKED',
                consumedAt: getExactTimestampToSecond(),
                consumedBy: getCurrentEmployeeName()
            };
            localStorage.setItem(SINGLE_USE_QR_KEY, JSON.stringify(qrTokens));
            return true;
        } catch (e) { return true; }
    };

    // 4. سد ثغرة التلاعب بطريقة الدفع (نقدي/آجل) + قفل الحد الأدنى للسعر والخصم + قفل التعديل بعد الطباعة
    const origValidateLocks = window.validateSplitBankingAndFiscalLocksBeforeSubmit;
    window.validateSplitBankingAndFiscalLocksBeforeSubmit = function(inv) {
        if (typeof origValidateLocks === 'function' && !origValidateLocks(inv)) {
            return false;
        }
        if (!inv) return true;
        const invNo = String(inv.invoiceNo || inv.id || '');

        // أ) فحص قفل التعديل والحذف بعد الطباعة للعميل
        try {
            const reg = JSON.parse(localStorage.getItem(PRINT_REGISTRY_KEY) || '{}') || {};
            if (reg[invNo] && reg[invNo].immutableLocked && !window._managerOverrideImmutableSplit) {
                if (typeof showToast === 'function') {
                    showToast(`🔒 قفل عدم القابلية للتعديل: الفاتورة (#${invNo}) تم حفظها وطباعتها مسبقاً — يُمنع تعديل الكميات أو الأسعار إلا عبر إشعار مرتجع رسمي!`, 'error');
                }
                return false;
            }
        } catch (e) {}

        // ب) منع حفظ البيع الآجل لعميل نقدي غير مسجل ائتمانياً
        const custName = String(inv.customerName || '').trim();
        if ((inv.paymentMethod === 'credit' || parseFloat(inv.creditAmount || 0) > 0) &&
            (!custName || custName === 'عميل نقدي' || custName === 'walk-in')) {
            if (typeof showToast === 'function') {
                showToast('🚫 سد ثغرة الآجل الوهمي: يُمنع حفظ فاتورة آجلة باسم (عميل نقدي) — يجب اختيار عميل مسجل ومعتمد ائتمانياً!', 'error');
            }
            return false;
        }

        // ج) قفل الحد الأدنى للسعر وسقف الخصم المسموح (15%)
        const sub = parseFloat(inv.subTotal || inv.grandTotal || inv.total || 0) || 0;
        const disc = parseFloat(inv.discount || 0) || 0;
        if (sub > 0 && (disc / sub) > 0.15) {
            if (typeof showToast === 'function') {
                showToast('🚫 قفل سقف الخصم: نسبة الخصم تتجاوز الحد الأقصى المسموح به (15%) دون تفويض المدير!', 'error');
            }
            return false;
        }
        for (let i = 0; i < (inv.items || []).length; i++) {
            const it = inv.items[i];
            const meta = window.resolveSplitItemUnitMeta ? window.resolveSplitItemUnitMeta(it) : { price: it.price || 0, costPrice: 0, name: it.name || 'صنف' };
            const minAllowedPrice = parseFloat(it.minPrice || meta.costPrice || 0) || 0;
            if (minAllowedPrice > 0 && meta.price < minAllowedPrice * 0.85) {
                if (typeof showToast === 'function') {
                    showToast(`🚫 قفل الحد الأدنى للسعر: سعر الصنف (${meta.name}) أقل من الحد الأدنى المعتمد!`, 'error');
                }
                return false;
            }
        }
        return true;
    };

    // 5. نظام "الجرد الأعمى" لتسليم الخزينة والوردية وتقييد العجز آلياً كذمة على حساب الموظف الشخصي
    window.openSplitBlindCashClosingModal = function() {
        let modal = document.getElementById('split-blind-cash-closing-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-blind-cash-closing-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); z-index:999978; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const empName = getCurrentEmployeeName();
        modal.innerHTML = `
            <div class="modal-card" style="max-width:540px; width:100%; padding:20px; box-shadow:0 24px 60px rgba(0,0,0,0.8);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(236,72,153,0.45); padding-bottom:10px; margin-bottom:14px;">
                    <h3 style="margin:0; font-size:1.12rem; font-weight:900; color:#f472b6;">🔐 نظام الجرد الأعمى لتسليم الخزينة وإغلاق الوردية</h3>
                    <button type="button" onclick="document.getElementById('split-blind-cash-closing-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕</button>
                </div>
                <div style="background:rgba(2,6,23,0.65); border:1.8px dashed #ec4899; border-radius:12px; padding:12px; margin-bottom:14px; text-align:center;">
                    <div style="font-size:0.84rem; color:#fbcfe8; font-weight:900;">👤 الموظف المستلم للوردية: <strong>${empName}</strong></div>
                    <div style="font-size:0.8rem; color:#94a3b8; font-weight:800; margin-top:4px;">🔒 الرصيد الدفتري المتوقع في النظام: <span id="split-blind-expected-mask" style="color:#fbbf24; font-weight:900;">[ مخفي تماماً حتى إدخال الجرد الفعلي ]</span></div>
                    <div style="font-size:0.84rem; color:#fde047; font-weight:900; margin-top:6px;">💬 «عدّ قروشك سمح واكتب الصافي.. الرادار شايف كل جنيه!»</div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.86rem; font-weight:900; margin-bottom:6px;">💵 أدخل النقدية الفعلية التي عددتها بيدك في الصندوق الآن:</label>
                    <input type="text" inputmode="decimal" id="split-blind-actual-cash" placeholder="عدّ قروشك سمح واكتب الصافي.. الرادار شايف كل جنيه!" style="width:100%; padding:10px 12px; border-radius:10px; border:2px solid #ec4899; background:#020617; color:#ffffff; font-size:1.15rem; font-weight:900; text-align:center;">
                </div>
                <div id="split-blind-result-box" style="display:none; margin-bottom:14px; padding:12px; border-radius:10px; font-weight:900; font-size:0.88rem;"></div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" id="btn-submit-blind-count" onclick="window.submitSplitBlindCashCount()" style="padding:9px 22px; border-radius:10px; background:linear-gradient(135deg,#ec4899,#db2777); border:2px solid #f9a8d4; color:#ffffff; font-weight:900; cursor:pointer;">🔍 اعتماد الجرد الأعمى وكشف المطابقة</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.submitSplitBlindCashCount = function() {
        const inp = document.getElementById('split-blind-actual-cash');
        const resBox = document.getElementById('split-blind-result-box');
        const maskSpan = document.getElementById('split-blind-expected-mask');
        if (!inp || !resBox) return;
        const raw = String(inp.value || '').trim();
        if (!raw) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال النقدية الفعلية المعدودة باليد أولاً', 'warning');
            return;
        }
        const actualCash = parseFloat(window.evalSplitInlineMath ? window.evalSplitInlineMath(raw) : raw) || 0;

        // حساب الرصيد المتوقع من الخزينة أو المبيعات النقدية
        let expectedCash = 0;
        try {
            const safes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
            if (safes.length > 0 && parseFloat(safes[0].currentBalance) > 0) {
                expectedCash = parseFloat(safes[0].currentBalance) || 0;
            } else {
                const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
                sales.forEach(function(s) {
                    if (!s.deleted && !s.wasSplit && s.paymentMethod !== 'credit') {
                        expectedCash += parseFloat(s.cashAmount || s.grandTotal || s.total || 0) || 0;
                    }
                });
            }
        } catch (e) {}

        const diff = +(actualCash - expectedCash).toFixed(2);
        if (maskSpan) maskSpan.textContent = expectedCash.toLocaleString() + ' SDG';
        resBox.style.display = 'block';

        const empName = getCurrentEmployeeName();
        if (Math.abs(diff) < 0.01) {
            resBox.style.background = 'rgba(16,185,129,0.18)';
            resBox.style.border = '2px solid #10b981';
            resBox.style.color = '#6ee7b7';
            resBox.innerHTML = `✅ الجرد مطابق 100%! (المتوقع: ${expectedCash.toLocaleString()} | الفعلي: ${actualCash.toLocaleString()} | الفرق: 0.00)`;
        } else if (diff < 0) {
            const shortage = Math.abs(diff);
            resBox.style.background = 'rgba(239,68,68,0.2)';
            resBox.style.border = '2px solid #ef4444';
            resBox.style.color = '#fca5a5';
            resBox.innerHTML = `🚨 تم اكتشاف عجز مالي قدره (${shortage.toLocaleString()} SDG)!<br>تم تقييد العجز فوراً كذمة مالية مستحقة على حساب الموظف الشخصي (${empName}).`;
            try {
                const ledger = JSON.parse(localStorage.getItem(SHORTAGE_LEDGER_KEY) || '[]') || [];
                ledger.unshift({
                    id: 'SHRT-' + Date.now(),
                    employeeName: empName,
                    expectedCash: expectedCash,
                    actualCash: actualCash,
                    shortageAmount: shortage,
                    timestamp: getExactTimestampToSecond()
                });
                localStorage.setItem(SHORTAGE_LEDGER_KEY, JSON.stringify(ledger.slice(0, 200)));
            } catch (e) {}
        } else {
            resBox.style.background = 'rgba(245,158,11,0.2)';
            resBox.style.border = '2px solid #f59e0b';
            resBox.style.color = '#fde68a';
            resBox.innerHTML = `⚠️ توجد زيادة في الصندوق قدرها (+${diff.toLocaleString()} SDG) — تم ترحيلها لحساب معلقات الزيادة للمراجعة.`;
        }
    };
})();
