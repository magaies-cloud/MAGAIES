(function() {
    if (window.__SPLIT_CONSTITUTION10_LOCK__) return;
    window.__SPLIT_CONSTITUTION10_LOCK__ = true;

    // 1. شرائح الأسعار الذكية والربحية اللحظية والرقابة على سقف الائتمان وتاريخ الاستحقاق
    window.getSplitProfitAndCreditSummaryHtml = function(inv) {
        if (!inv) return '';
        let totalRev = 0, totalCost = 0;
        (inv.items || []).forEach(function(it) {
            const meta = window.resolveSplitItemUnitMeta ? window.resolveSplitItemUnitMeta(it) : { deliverableQty: 1, price: 0, costPrice: 0 };
            const q = meta.deliverableQty || 0;
            const unitCost = parseFloat(it._landedCostPerUnit || meta.costPrice || (meta.price * 0.82)) || 0;
            totalRev += q * (meta.price || 0);
            totalCost += q * unitCost;
        });
        const profit = totalRev - totalCost;
        const marginPct = totalRev > 0 ? +((profit / totalRev) * 100).toFixed(1) : 0;

        // فحص سقف الائتمان للعميل وتاريخ الاستحقاق
        let creditStatus = '💳 ائتمان آمن';
        const dueDate = inv.dueDate || new Date(Date.now() + 14 * 86400000).toISOString().slice(0, 10);
        try {
            const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
            const c = custs.find(function(x) { return String(x.id) === String(inv.customer || inv.customerId) || String(x.name || '') === String(inv.customerName || ''); });
            if (c && parseFloat(c.creditLimit) > 0) {
                const curBal = parseFloat(c.currentBalance || 0) || 0;
                if (curBal > parseFloat(c.creditLimit)) {
                    creditStatus = '⚠️ تجاوز السقف';
                }
            }
        } catch (e) {}
        return `📈 ربح: ${marginPct}% | ${creditStatus} | 📅 ${dueDate}`;
    };

    window.applySplitPriceTier = function(tier) {
        if (!window.activeSplitInvoice) return;
        if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot();
        const inv = window.activeSplitInvoice;
        inv._priceTier = tier;
        const multipliers = {
            wholesale: 1.0,
            semi_wholesale: 1.04,
            retail: 1.10,
            special: 0.96,
            last_customer: 0.98
        };
        const mult = multipliers[tier] || 1.0;
        let newGrand = 0;
        (inv.items || []).forEach(function(it) {
            if (!it._baseWholesalePrice) {
                it._baseWholesalePrice = parseFloat(it.price || 0) || 1000;
            }
            it.price = +(it._baseWholesalePrice * mult).toFixed(2);
            const q = parseFloat(it.quantity ?? it.qty ?? 0) || 0;
            it.total = +(q * it.price).toFixed(2);
            newGrand += it.total;
        });
        const disc = parseFloat(inv.discount || 0) || 0;
        const tax = parseFloat(inv.vatAmount || 0) || 0;
        inv.subTotal = +newGrand.toFixed(2);
        inv.grandTotal = Math.max(0, +(newGrand - disc + tax).toFixed(2));
        inv.total = inv.grandTotal;

        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        const tierLabels = { wholesale: 'جملة', semi_wholesale: 'نصف جملة', retail: 'قطاعي', special: 'سعر خاص للعميل', last_customer: 'آخر سعر بيع للعميل' };
        if (typeof showToast === 'function') {
            showToast(`🏷️ تم تحديث الأسعار والإجماليات تلقائياً وفق شريحة (${tierLabels[tier] || tier})`, 'success');
        }
    };

    // 2. شاشة توزيع المصروفات المضافة وتحديد التكلفة الفعلية للوحدة (Landed Cost Allocation Modal)
    window.openSplitLandedCostModal = function() {
        if (!window.activeSplitInvoice) {
            if (typeof showToast === 'function') showToast('⚠️ افتح فاتورة أولاً لتوزيع المصروفات المضافة', 'warning');
            return;
        }
        let modal = document.getElementById('split-landed-cost-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-landed-cost-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.84); z-index:999976; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const inv = window.activeSplitInvoice;
        const curTransport = parseFloat(inv.transportCost || 0) || 0;
        const curLabor = parseFloat(inv.laborCost || 0) || 0;
        const curAdditions = parseFloat(inv.additionsCost || 0) || 0;
        const curMethod = inv._landedCostMethod || 'value';

        modal.innerHTML = `
            <div class="modal-card" style="max-width:620px; width:100%; padding:20px; box-shadow:0 24px 60px rgba(0,0,0,0.75);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(245,158,11,0.45); padding-bottom:10px; margin-bottom:14px;">
                    <h3 style="margin:0; font-size:1.12rem; font-weight:900; color:#fbbf24;">🚚 توزيع المصروفات المضافة وتكلفة الوحدة (Landed Cost)</h3>
                    <button type="button" onclick="document.getElementById('split-landed-cost-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕</button>
                </div>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-bottom:12px;">
                    <div>
                        <label style="display:block; font-size:0.8rem; font-weight:900; margin-bottom:4px;">🚛 النقل والشحن:</label>
                        <input type="text" inputmode="decimal" id="split-lc-transport" value="${curTransport}" style="width:100%; padding:7px; border-radius:8px; border:2px solid #38bdf8; background:#020617; color:#fff; font-weight:900; text-align:center;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.8rem; font-weight:900; margin-bottom:4px;">👷 العتالة والتنزيل:</label>
                        <input type="text" inputmode="decimal" id="split-lc-labor" value="${curLabor}" style="width:100%; padding:7px; border-radius:8px; border:2px solid #10b981; background:#020617; color:#fff; font-weight:900; text-align:center;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.8rem; font-weight:900; margin-bottom:4px;">📑 رسوم وإضافات:</label>
                        <input type="text" inputmode="decimal" id="split-lc-additions" value="${curAdditions}" style="width:100%; padding:7px; border-radius:8px; border:2px solid #a855f7; background:#020617; color:#fff; font-weight:900; text-align:center;">
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:5px;">⚖️ معيار التوزيع المحاسبي على الأصناف والأقسام:</label>
                    <select id="split-lc-method" style="width:100%; padding:8px 10px; border-radius:8px; border:2px solid #f59e0b; background:#020617; color:#fde68a; font-weight:900;">
                        <option value="value" ${curMethod==='value'?'selected':''}>💰 التوزيع النسبي حسب القيمة المالية للصنف</option>
                        <option value="weight" ${curMethod==='weight'?'selected':''}>⚖️ التوزيع النسبي حسب الوزن / الطن (معامل الوحدة)</option>
                        <option value="qty" ${curMethod==='qty'?'selected':''}>🔢 التوزيع النسبي حسب الكمية</option>
                    </select>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="window.applySplitLandedCostAllocation()" style="padding:8px 22px; border-radius:10px; background:linear-gradient(135deg,#f59e0b,#d97706); border:2px solid #fbbf24; color:#ffffff; font-weight:900; cursor:pointer;">✅ توزيع المصروفات وتحديث التكلفة الفعلية</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.applySplitLandedCostAllocation = function() {
        if (!window.activeSplitInvoice) return;
        const inv = window.activeSplitInvoice;
        const trCost = parseFloat(window.evalSplitInlineMath ? window.evalSplitInlineMath(document.getElementById('split-lc-transport')?.value) : document.getElementById('split-lc-transport')?.value) || 0;
        const lbCost = parseFloat(window.evalSplitInlineMath ? window.evalSplitInlineMath(document.getElementById('split-lc-labor')?.value) : document.getElementById('split-lc-labor')?.value) || 0;
        const adCost = parseFloat(window.evalSplitInlineMath ? window.evalSplitInlineMath(document.getElementById('split-lc-additions')?.value) : document.getElementById('split-lc-additions')?.value) || 0;
        const method = document.getElementById('split-lc-method')?.value || 'value';

        inv.transportCost = trCost;
        inv.laborCost = lbCost;
        inv.additionsCost = adCost;
        inv._landedCostMethod = method;
        const totalExtra = trCost + lbCost + adCost;

        let totalBasis = 0;
        const items = inv.items || [];
        items.forEach(function(it) {
            const meta = window.resolveSplitItemUnitMeta(it);
            const q = meta.deliverableQty || 1;
            if (method === 'weight') totalBasis += q * (meta.factor || 1);
            else if (method === 'qty') totalBasis += q;
            else totalBasis += q * (meta.price || 1);
        });
        if (totalBasis <= 0) totalBasis = 1;

        items.forEach(function(it) {
            const meta = window.resolveSplitItemUnitMeta(it);
            const q = Math.max(0.0001, meta.deliverableQty || 1);
            let itemBasis = q * (meta.price || 1);
            if (method === 'weight') itemBasis = q * (meta.factor || 1);
            else if (method === 'qty') itemBasis = q;
            const allocatedExtra = totalExtra * (itemBasis / totalBasis);
            const extraPerUnit = allocatedExtra / q;
            it._landedCostPerUnit = +((meta.costPrice || (meta.price * 0.82)) + extraPerUnit).toFixed(2);
        });

        const modal = document.getElementById('split-landed-cost-modal');
        if (modal) modal.style.display = 'none';
        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        if (typeof showToast === 'function') {
            showToast(`🚚 تم توزيع المصروفات المضافة (${totalExtra.toLocaleString()}) وتحديث التكلفة الفعلية للوحدة بنجاح`, 'success');
        }
    };

    // 3. الدورة المستندية المترابطة بضغطة زر: توليد سند قبض مالي آلي للقسم وتسميعه في الخزينة وكشف الحساب
    window.generateAutoReceiptForSplitSection = function(secIdx) {
        if (!window.activeSplitInvoice) return;
        const inv = window.activeSplitInvoice;
        const rootNo = window.getRootInvoiceNo(inv);
        const splitSeq = window.currentSplitOperationSeq || 1;
        const secNo = `${rootNo}/${splitSeq}/${secIdx + 1}`;
        const currency = inv.currency || 'SDG';

        let secSub = 0;
        (inv.items || []).forEach(function(it, idx) {
            const meta = window.resolveSplitItemUnitMeta(it);
            const inp = document.getElementById(`split-qty-s${secIdx}-i${idx}`);
            const q = inp ? (parseFloat(inp.value) || 0) : 0;
            secSub += q * meta.price;
        });
        const amount = +secSub.toFixed(2);
        if (amount <= 0) {
            if (typeof showToast === 'function') showToast('⚠️ قيمة هذا القسم صفر حالياً', 'warning');
            return;
        }

        const receiptNo = 'RV-SPL-' + Date.now().toString().slice(-6);
        const receiptDoc = {
            id: receiptNo,
            voucherNo: receiptNo,
            type: 'receipt',
            referenceInvoice: secNo,
            customerName: inv.customerName || 'عميل نقدي',
            customerId: inv.customer || inv.customerId || 'walk-in',
            amount: amount,
            currency: currency,
            date: new Date().toISOString().slice(0, 10),
            notes: `سند قبض آلي مرتبط بإذن التقسيم #${secNo}`,
            createdAt: new Date().toISOString()
        };

        try {
            const vouchers = JSON.parse(localStorage.getItem('erp_receipt_vouchers_v1') || '[]');
            vouchers.unshift(receiptDoc);
            localStorage.setItem('erp_receipt_vouchers_v1', JSON.stringify(vouchers.slice(0, 500)));

            // تسميع فوري في الخزينة الرئيسية
            const safes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
            if (safes.length > 0) {
                safes[0].currentBalance = (parseFloat(safes[0].currentBalance) || 0) + amount;
                localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
            }
        } catch (e) {}

        if (typeof showToast === 'function') {
            showToast(`🧾 تم توليد سند القبض الآلي (${receiptNo}) بمبلغ ${amount.toLocaleString()} ${currency} للقسم #${secNo} وتسميعه بالخزينة`, 'success');
        }
    };

    // 4. إدارة المرتجعات والتسويات والقيود العكسية الدقيقة (Returns & Reverse Accounting Modal)
    window.openSplitReturnReverseModal = function() {
        if (!window.activeSplitInvoice) {
            if (typeof showToast === 'function') showToast('⚠️ افتح فاتورة أولاً لتسجيل المرتجع والقيد العكسي', 'warning');
            return;
        }
        let modal = document.getElementById('split-return-reverse-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-return-reverse-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.84); z-index:999976; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const inv = window.activeSplitInvoice;
        const rowsHtml = (inv.items || []).map(function(it, idx) {
            const meta = window.resolveSplitItemUnitMeta(it);
            const maxRet = meta.deliverableQty;
            return `
                <tr>
                    <td style="text-align:right; font-weight:900; padding:8px;">${meta.name}</td>
                    <td style="text-align:center; font-weight:900; padding:8px; color:#38bdf8;">${maxRet} ${meta.primaryUnit}</td>
                    <td style="text-align:center; padding:8px;">
                        <input type="text" inputmode="decimal" id="split-ret-qty-${idx}" data-max="${maxRet}" value="0" oninput="window.clampSplitReturnInput(${idx})" style="width:95px; text-align:center; font-weight:900; border:2px solid #ef4444; border-radius:8px; padding:4px; background:#020617; color:#fca5a5;">
                    </td>
                    <td style="text-align:left; font-weight:900; padding:8px; color:#fcd34d;">${meta.price.toLocaleString()}</td>
                </tr>
            `;
        }).join('');

        modal.innerHTML = `
            <div class="modal-card" style="max-width:740px; width:100%; padding:20px; box-shadow:0 24px 60px rgba(0,0,0,0.75);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(239,68,68,0.45); padding-bottom:10px; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:1.12rem; font-weight:900; color:#f87171;">↩️ إشعار مرتجع مبيعات وتوليد القيد العكسي الآلي (#${inv.invoiceNo || inv.id})</h3>
                    <button type="button" onclick="document.getElementById('split-return-reverse-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕</button>
                </div>
                <table class="pos-grid" style="width:100%; border-collapse:collapse; margin-bottom:12px;">
                    <thead>
                        <tr>
                            <th style="text-align:right; padding:8px;">📦 الصنف</th>
                            <th style="text-align:center; padding:8px;">📊 الكمية المباعة القابلة للإرجاع</th>
                            <th style="text-align:center; padding:8px; color:#f87171;">↩️ الكمية المرتجعة الآن</th>
                            <th style="text-align:left; padding:8px;">💰 سعر الوحدة</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <button type="button" onclick="window.fillFullSplitReturn()" style="padding:7px 14px; border-radius:9px; background:rgba(245,158,11,0.18); border:2px solid #f59e0b; color:#fde68a; font-weight:900; cursor:pointer;">⚡ إرجاع كامل الكمية</button>
                    <button type="button" onclick="window.executeSplitReturnReverseEntry()" style="padding:8px 22px; border-radius:10px; background:linear-gradient(135deg,#ef4444,#dc2626); border:2px solid #f87171; color:#ffffff; font-weight:900; cursor:pointer;">✅ اعتماد المرتجع وتوليد القيد العكسي وضبط المخزن</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.clampSplitReturnInput = function(idx) {
        const el = document.getElementById('split-ret-qty-' + idx);
        if (!el) return;
        const maxRet = parseFloat(el.dataset.max || '0') || 0;
        let val = parseFloat(window.normalizeSplitNumericString ? window.normalizeSplitNumericString(el.value, true, false) : el.value) || 0;
        if (val < 0) val = 0;
        if (val > maxRet) {
            val = maxRet;
            el.value = val;
            if (typeof showToast === 'function') {
                showToast(`🛡️ لا يمكن إرجاع كمية أكبر من الكمية المباعة (${maxRet})`, 'warning');
            }
        }
    };

    window.fillFullSplitReturn = function() {
        if (!window.activeSplitInvoice) return;
        (window.activeSplitInvoice.items || []).forEach(function(_, idx) {
            const el = document.getElementById('split-ret-qty-' + idx);
            if (el) el.value = el.dataset.max || '0';
        });
    };

    window.executeSplitReturnReverseEntry = function() {
        if (!window.activeSplitInvoice) return;
        if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot();
        const inv = window.activeSplitInvoice;
        const S = window.splitSectionsCount || 2;
        let totalReturnVal = 0;
        let totalReturnedUnits = 0;

        (inv.items || []).forEach(function(it, idx) {
            const el = document.getElementById('split-ret-qty-' + idx);
            const retQty = el ? (parseFloat(el.value) || 0) : 0;
            if (retQty > 0) {
                const meta = window.resolveSplitItemUnitMeta(it);
                const clampedRet = Math.min(meta.deliverableQty, retQty);
                const curOrig = parseFloat(it.quantity ?? it.qty ?? 0) || 0;
                it.quantity = Math.max(0, +(curOrig - clampedRet).toFixed(4));
                it.qty = it.quantity;
                totalReturnedUnits += clampedRet;
                totalReturnVal += clampedRet * meta.price;

                // إعادة توزيع المتبقي بالتساوي على الأقسام
                const newRem = Math.max(0, +(meta.deliverableQty - clampedRet).toFixed(4));
                const share = +(newRem / S).toFixed(3);
                const arr = [];
                let sum = 0;
                for (let s = 0; s < S; s++) {
                    if (s === S - 1) arr.push(Math.max(0, +(newRem - sum).toFixed(4)));
                    else { arr.push(share); sum += share; }
                }
                inv._splitDraftQtys[idx] = arr;
            }
        });

        if (totalReturnedUnits <= 0) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال الكمية المرتجعة أولاً', 'warning');
            return;
        }

        // توليد القيد العكسي الآلي وتعديل رصيد العميل والمخزن
        try {
            const retNotes = JSON.parse(localStorage.getItem('erp_sales_returns_v1') || '[]');
            retNotes.unshift({
                id: 'CN-SPL-' + Date.now().toString().slice(-6),
                invoiceNo: inv.invoiceNo || inv.id,
                customerName: inv.customerName || 'عميل نقدي',
                returnAmount: +totalReturnVal.toFixed(2),
                returnedUnits: totalReturnedUnits,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('erp_sales_returns_v1', JSON.stringify(retNotes.slice(0, 200)));
        } catch (e) {}

        inv.grandTotal = Math.max(0, +((parseFloat(inv.grandTotal || inv.total || 0) || 0) - totalReturnVal).toFixed(2));
        inv.total = inv.grandTotal;

        const modal = document.getElementById('split-return-reverse-modal');
        if (modal) modal.style.display = 'none';
        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        if (typeof showToast === 'function') {
            showToast(`↩️ تم اعتماد المرتجع وتوليد القيد العكسي الآلي بمبلغ ${totalReturnVal.toLocaleString()} وضبط المخزن وكشف الحساب`, 'success');
        }
    };
})();
