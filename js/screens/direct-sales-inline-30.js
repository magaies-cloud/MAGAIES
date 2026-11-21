(function() {
    if (window.__SPLIT_CONSTITUTION9_LOCK__) return;
    window.__SPLIT_CONSTITUTION9_LOCK__ = true;

    // 1. التعديل اللحظي لمعامل تحويل الوحدات (Multi-Unit Auto-Conversion Factor)
    window.promptSplitUnitFactorChange = function(itemIdx) {
        if (!window.activeSplitInvoice || !window.activeSplitInvoice.items[itemIdx]) return;
        const item = window.activeSplitInvoice.items[itemIdx];
        const meta = window.resolveSplitItemUnitMeta(item);
        const inputVal = prompt(`⚖️ تعديل معامل التحويل اللحظي للصنف (${meta.name}):\nكم (${meta.baseUnit}) في كل 1 (${meta.primaryUnit})؟`, String(meta.factor));
        if (inputVal === null) return;
        const parsed = parseFloat(window.normalizeSplitNumericString ? window.normalizeSplitNumericString(inputVal, true, true) : inputVal);
        if (!isNaN(parsed) && parsed > 0) {
            if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot();
            item._customSplitFactor = parsed;
            item.unitFactor = parsed;
            if (typeof window.renderWorldClassSplitBoard === 'function') {
                window.renderWorldClassSplitBoard();
            }
            if (typeof showToast === 'function') {
                showToast(`⚖️ تم تحديث معامل التحويل لحظياً: 1 ${meta.primaryUnit} = ${parsed} ${meta.baseUnit}`, 'success');
            }
        }
    };

    // 2. الشاشة الوليدة "فحص واستلام" لفرز البضاعة (سليم / تالف لمخزن التوالف / مرفوض مع السبب)
    window.openSplitInspectionSortingModal = function() {
        if (!window.activeSplitInvoice) {
            if (typeof showToast === 'function') showToast('⚠️ افتح فاتورة للتقسيم أولاً لفحص وفرز أصنافها', 'warning');
            return;
        }
        let modal = document.getElementById('split-inspection-sorting-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-inspection-sorting-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.84); z-index:999975; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const inv = window.activeSplitInvoice;
        const rowsHtml = (inv.items || []).map(function(it, idx) {
            const meta = window.resolveSplitItemUnitMeta(it);
            const maxAvail = Math.max(0, +(meta.origQty - meta.deliveredQty).toFixed(4));
            const dmg = meta.damagedQty || 0;
            const rej = meta.rejectedQty || 0;
            const good = Math.max(0, +(maxAvail - dmg - rej).toFixed(4));
            const reason = (it._inspectedRejectReason || '').replace(/"/g, '&quot;');
            return `
                <tr>
                    <td style="text-align:right; font-weight:900; padding:8px;">${meta.name}<br><small style="color:#38bdf8;">الوحدة: ${meta.primaryUnit} (المتاح للفحص: ${maxAvail})</small></td>
                    <td style="text-align:center; padding:8px;">
                        <input type="text" inputmode="decimal" id="split-insp-good-${idx}" value="${good}" readonly style="width:85px; text-align:center; font-weight:900; border:2px solid #10b981; border-radius:8px; padding:4px; background:#020617; color:#6ee7b7;">
                    </td>
                    <td style="text-align:center; padding:8px;">
                        <input type="text" inputmode="decimal" id="split-insp-dmg-${idx}" data-max="${maxAvail}" value="${dmg}" oninput="window.recalcSplitInspectionRow(${idx})" style="width:85px; text-align:center; font-weight:900; border:2px solid #f59e0b; border-radius:8px; padding:4px; background:#020617; color:#fde68a;">
                    </td>
                    <td style="text-align:center; padding:8px;">
                        <input type="text" inputmode="decimal" id="split-insp-rej-${idx}" data-max="${maxAvail}" value="${rej}" oninput="window.recalcSplitInspectionRow(${idx})" style="width:85px; text-align:center; font-weight:900; border:2px solid #ef4444; border-radius:8px; padding:4px; background:#020617; color:#fca5a5;">
                    </td>
                    <td style="text-align:right; padding:8px;">
                        <input type="text" id="split-insp-reason-${idx}" value="${reason}" placeholder="سبب الرفض أو التلف..." style="width:100%; font-weight:800; border:2px solid rgba(148,163,184,0.45); border-radius:8px; padding:5px 8px; background:#020617; color:#ffffff;">
                    </td>
                </tr>
            `;
        }).join('');

        modal.innerHTML = `
            <div class="modal-card" style="max-width:860px; width:100%; padding:20px; box-shadow:0 24px 60px rgba(0,0,0,0.75); max-height:90vh; overflow-y:auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(56,189,248,0.35); padding-bottom:10px; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#38bdf8;">🔬 شاشة فحص واستلام وفرز البضاعة (#${inv.invoiceNo || inv.id})</h3>
                    <button type="button" onclick="document.getElementById('split-inspection-sorting-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="pos-grid" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:right; padding:8px;">📦 الصنف</th>
                                <th style="text-align:center; padding:8px; color:#10b981;">✅ سليم ومطابق</th>
                                <th style="text-align:center; padding:8px; color:#f59e0b;">⚠️ تالف (مخزن التوالف)</th>
                                <th style="text-align:center; padding:8px; color:#ef4444;">🚫 مرفوض (يُرد)</th>
                                <th style="text-align:right; padding:8px;">📝 سبب الرفض / حساب التسوية</th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:14px; padding-top:10px; border-top:1px solid rgba(148,163,184,0.25);">
                    <button type="button" onclick="document.getElementById('split-inspection-sorting-modal').style.display='none'" style="padding:8px 16px; border-radius:10px; background:rgba(148,163,184,0.15); border:2px solid #64748b; color:#e2e8f0; font-weight:900; cursor:pointer;">إلغاء</button>
                    <button type="button" onclick="window.applySplitInspectionSorting()" style="padding:8px 22px; border-radius:10px; background:linear-gradient(135deg,#10b981,#059669); border:2px solid #34d399; color:#ffffff; font-weight:900; cursor:pointer;">✅ اعتماد الفرز وتحديث صافي التقسيم</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.recalcSplitInspectionRow = function(idx) {
        const goodEl = document.getElementById('split-insp-good-' + idx);
        const dmgEl = document.getElementById('split-insp-dmg-' + idx);
        const rejEl = document.getElementById('split-insp-rej-' + idx);
        if (!goodEl || !dmgEl || !rejEl) return;
        const maxAvail = parseFloat(dmgEl.dataset.max || '0') || 0;
        let dmg = Math.max(0, parseFloat(window.normalizeSplitNumericString ? window.normalizeSplitNumericString(dmgEl.value, true, false) : dmgEl.value) || 0);
        let rej = Math.max(0, parseFloat(window.normalizeSplitNumericString ? window.normalizeSplitNumericString(rejEl.value, true, false) : rejEl.value) || 0);
        if (dmg + rej > maxAvail) {
            rej = Math.max(0, +(maxAvail - dmg).toFixed(4));
            rejEl.value = rej;
        }
        goodEl.value = Math.max(0, +(maxAvail - dmg - rej).toFixed(4));
    };

    window.applySplitInspectionSorting = function() {
        if (!window.activeSplitInvoice) return;
        if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot();
        const inv = window.activeSplitInvoice;
        const S = window.splitSectionsCount || 2;
        let totalDmg = 0, totalRej = 0;

        (inv.items || []).forEach(function(it, idx) {
            const dmgEl = document.getElementById('split-insp-dmg-' + idx);
            const rejEl = document.getElementById('split-insp-rej-' + idx);
            const rsnEl = document.getElementById('split-insp-reason-' + idx);
            const dmg = dmgEl ? (parseFloat(dmgEl.value) || 0) : 0;
            const rej = rejEl ? (parseFloat(rejEl.value) || 0) : 0;
            it._inspectedDamagedQty = dmg;
            it._inspectedRejectedQty = rej;
            it._inspectedRejectReason = rsnEl ? rsnEl.value.trim() : '';
            totalDmg += dmg;
            totalRej += rej;

            const meta = window.resolveSplitItemUnitMeta(it);
            const rem = meta.deliverableQty;
            const share = +(rem / S).toFixed(3);
            const arr = [];
            let sum = 0;
            for (let s = 0; s < S; s++) {
                if (s === S - 1) arr.push(Math.max(0, +(rem - sum).toFixed(4)));
                else { arr.push(share); sum += share; }
            }
            inv._splitDraftQtys[idx] = arr;
        });

        // توثيق محضر الفرز والتوالف في التخزين المحلي
        try {
            const logs = JSON.parse(localStorage.getItem('erp_split_inspection_logs_v1') || '[]');
            logs.unshift({
                invoiceNo: inv.invoiceNo || inv.id,
                totalDamaged: totalDmg,
                totalRejected: totalRej,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('erp_split_inspection_logs_v1', JSON.stringify(logs.slice(0, 50)));
        } catch (e) {}

        const modal = document.getElementById('split-inspection-sorting-modal');
        if (modal) modal.style.display = 'none';
        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        if (typeof showToast === 'function') {
            showToast(`🔬 تم اعتماد محضر الفحص والفرز (تالف لمخزن التوالف: ${totalDmg} | مرفوض: ${totalRej}) وتحديث الصافي القابل للتقسيم`, 'success');
        }
    };

    // 3. الشاشة الوليدة للتحويل المباشر بين المخازن بإذن تحويل مخزني متوازن
    window.openSplitWarehouseTransferModal = function() {
        let modal = document.getElementById('split-warehouse-transfer-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-warehouse-transfer-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.84); z-index:999975; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const inv = window.activeSplitInvoice;
        const firstItemName = (inv && inv.items && inv.items[0]) ? (inv.items[0].name || inv.items[0].itemName || 'صنف الفاتورة') : 'حديد تسليح / أسمنت';
        modal.innerHTML = `
            <div class="modal-card" style="max-width:540px; width:100%; padding:20px; box-shadow:0 24px 60px rgba(0,0,0,0.75);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(99,102,241,0.4); padding-bottom:10px; margin-bottom:14px;">
                    <h3 style="margin:0; font-size:1.12rem; font-weight:900; color:#818cf8;">🔄 إذن تحويل مخزني فوري متوازن</h3>
                    <button type="button" onclick="document.getElementById('split-warehouse-transfer-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕</button>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:4px;">📤 من مخزن (المصدر):</label>
                        <select id="split-wt-from" style="width:100%; padding:7px 10px; border-radius:8px; border:2px solid #6366f1; background:#020617; color:#fff; font-weight:800;">
                            <option value="المخزن الرئيسي">المخزن الرئيسي</option>
                            <option value="مخزن السجانة">مخزن السجانة</option>
                            <option value="مخزن المصنع">مخزن المصنع</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:4px;">📥 إلى مخزن (الوجهة):</label>
                        <select id="split-wt-to" style="width:100%; padding:7px 10px; border-radius:8px; border:2px solid #10b981; background:#020617; color:#fff; font-weight:800;">
                            <option value="مخزن السجانة">مخزن السجانة</option>
                            <option value="المخزن الرئيسي">المخزن الرئيسي</option>
                            <option value="مخزن التوالف">مخزن التوالف</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:10px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:4px;">📦 الصنف المحول:</label>
                        <input type="text" id="split-wt-item" value="${firstItemName}" style="width:100%; padding:7px 10px; border-radius:8px; border:2px solid rgba(148,163,184,0.45); background:#020617; color:#fff; font-weight:900;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:4px;">🔢 الكمية المحولة:</label>
                        <input type="text" inputmode="decimal" id="split-wt-qty" value="5" style="width:100%; padding:7px 10px; border-radius:8px; border:2px solid #38bdf8; background:#020617; color:#38bdf8; font-weight:900; text-align:center;">
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="window.executeSplitWarehouseTransfer()" style="padding:8px 20px; border-radius:10px; background:linear-gradient(135deg,#6366f1,#4f46e5); border:2px solid #818cf8; color:#ffffff; font-weight:900; cursor:pointer;">🚀 اعتماد التحويل المخزني المتوازن</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.executeSplitWarehouseTransfer = function() {
        const fromWh = document.getElementById('split-wt-from')?.value || 'المخزن الرئيسي';
        const toWh = document.getElementById('split-wt-to')?.value || 'مخزن السجانة';
        const itemName = document.getElementById('split-wt-item')?.value || '';
        const qty = parseFloat(document.getElementById('split-wt-qty')?.value || '0') || 0;
        if (fromWh === toWh) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار مخزن وجهة مختلف عن مخزن المصدر', 'warning');
            return;
        }
        if (qty <= 0) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال كمية صحيحة للتحويل', 'warning');
            return;
        }
        try {
            const transfers = JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1') || '[]');
            transfers.unshift({
                id: 'TR-' + Date.now(),
                fromWarehouse: fromWh,
                toWarehouse: toWh,
                itemName: itemName,
                qty: qty,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('erp_warehouse_transfers_v1', JSON.stringify(transfers.slice(0, 100)));
        } catch (e) {}
        const modal = document.getElementById('split-warehouse-transfer-modal');
        if (modal) modal.style.display = 'none';
        if (window.activeSplitInvoice) {
            window.activeSplitInvoice.warehouseName = toWh;
            if (typeof window.renderWorldClassSplitBoard === 'function') window.renderWorldClassSplitBoard();
        }
        if (typeof showToast === 'function') {
            showToast(`✅ تم اعتماد إذن التحويل المخزني المتوازن (${qty} من ${fromWh} إلى ${toWh})`, 'success');
        }
    };
})();
