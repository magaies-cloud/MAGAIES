/**
 * ELSAJANA 2027 — Sovereign Accounting, Inventory, Banking & AI Brain
 * Implements all 7 Decisive Points with 0ms execution & zero visual intrusion.
 */
(function () {
    'use strict';
    if (window.ElsajanaAccountingBrain) return;

    const BANKAK_DUP_MSG = 'أمسك عندك! إشعار بنكك دا دخل الخزنة قبل كدا.. الموية ما بتمر من تحتنا مرتين!';
    const EMPTY_STOCK_MSG = 'المخزن فاضي يا حبيبنا! السيخ دا اتباع خلاص ولّا لسه في الدفار بره؟';

    const getTenantPrefix = () => {
        try {
            const u = JSON.parse(localStorage.getItem('user') || '{}');
            const tid = u.tenant_id || localStorage.getItem('currentTenantId') || '';
            return tid && tid !== 'default' ? `tenant_${tid}_` : '';
        } catch (_) { return ''; }
    };

    const writeDualStorage = (baseKey, valObj) => {
        const raw = typeof valObj === 'string' ? valObj : JSON.stringify(valObj);
        try { localStorage.setItem(baseKey, raw); } catch (_) {}
        const pref = getTenantPrefix();
        if (pref) { try { localStorage.setItem(pref + baseKey, raw); } catch (_) {} }
    };

    const readDualArray = (baseKey) => {
        const pref = getTenantPrefix();
        try {
            const raw = (pref && localStorage.getItem(pref + baseKey)) || localStorage.getItem(baseKey) || '[]';
            const arr = JSON.parse(raw);
            return Array.isArray(arr) ? arr : [];
        } catch (_) { return []; }
    };

    // ── POINT 6: Unified Floating Toasts + Field Shake & Auto-Focus + Native Alert Override ──
    const ensureToastStyles = () => {
        if (document.getElementById('elsajana-brain-toast-css')) return;
        const st = document.createElement('style');
        st.id = 'elsajana-brain-toast-css';
        st.textContent = `
            @keyframes elsajanaShake { 0%,100%{transform:translateX(0)} 20%,60%{transform:translateX(-7px)} 40%,80%{transform:translateX(7px)} }
            .input-error-shake { animation: elsajanaShake 0.42s cubic-bezier(.36,.07,.19,.97) both !important; outline: 2.5px solid #dc2626 !important; box-shadow: 0 0 0 4px rgba(220,38,38,0.32) !important; }
            #elsajana-floating-toast-stack { position:fixed; top:16px; left:50%; transform:translateX(-50%); z-index:2147483646; display:flex; flex-direction:column; gap:8px; pointer-events:none; max-width:min(94vw,620px); direction:rtl; font-family:Tajawal,Cairo,sans-serif; }
            .elsajana-ftoast { pointer-events:auto; display:flex; align-items:center; gap:10px; padding:11px 16px; border-radius:12px; color:#fff!important; -webkit-text-fill-color:#fff!important; font-weight:900; font-size:0.95rem; box-shadow:0 14px 32px rgba(0,0,0,0.45); border:2px solid rgba(255,255,255,0.35); transition:opacity 0.22s,transform 0.22s; }
            .elsajana-ftoast.t-success { background:linear-gradient(135deg,#059669,#047857)!important; border-color:#6ee7b7!important; }
            .elsajana-ftoast.t-warning { background:linear-gradient(135deg,#d97706,#b45309)!important; border-color:#fde047!important; }
            .elsajana-ftoast.t-error { background:linear-gradient(135deg,#dc2626,#991b1b)!important; border-color:#fca5a5!important; }
        `;
        document.head.appendChild(st);
    };

    const floatingToast = (msg, type = 'success', ms = 3800, actionLabel = '', onAction = null) => {
        ensureToastStyles();
        let stack = document.getElementById('elsajana-floating-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'elsajana-floating-toast-stack';
            document.body.appendChild(stack);
        }
        const normType = (type === 'error' || type === 'danger') ? 't-error' : ((type === 'warning' || type === 'warn' || type === 'info') ? 't-warning' : 't-success');
        const icon = normType === 't-error' ? '🚫' : (normType === 't-warning' ? '⚠️' : '✅');
        const el = document.createElement('div');
        el.className = `elsajana-ftoast ${normType}`;
        el.innerHTML = `<span style="font-size:1.18rem;flex-shrink:0;">${icon}</span><span style="flex:1;line-height:1.45;">${String(msg || '').replace(/\n+/g, ' — ')}</span>`;
        if (actionLabel && typeof onAction === 'function') {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = actionLabel;
            btn.style.cssText = 'background:#fff;color:#0f172a!important;-webkit-text-fill-color:#0f172a!important;border:none;border-radius:7px;padding:4px 10px;font-weight:900;font-size:0.82rem;cursor:pointer;flex-shrink:0;';
            btn.onclick = () => { el.remove(); onAction(); };
            el.appendChild(btn);
        }
        stack.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(-8px)'; setTimeout(() => el.remove(), 220); }, ms);
    };

    const shakeAndFocus = (elOrSelector, msg, type = 'error', ms = 4200) => {
        const el = typeof elOrSelector === 'string' ? document.querySelector(elOrSelector) : elOrSelector;
        if (el) {
            try { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (_) {}
            el.classList.remove('input-error-shake');
            void el.offsetWidth;
            el.classList.add('input-error-shake');
            setTimeout(() => el.classList.remove('input-error-shake'), 900);
            try { el.focus(); if (typeof el.select === 'function' && el.tagName === 'INPUT') el.select(); } catch (_) {}
        }
        if (msg) floatingToast(msg, type, ms);
    };

    window.showToast = function (msg, type = 'success', ms = 3400) {
        floatingToast(msg, type, ms);
    };
    window.focusRequiredPurchaseField = function (elOrSel, msg, type = 'warning') {
        shakeAndFocus(elOrSel, msg, type);
    };
    window.alert = function (msg) {
        const s = String(msg || '');
        const isErr = /حظر|خطأ|مكرر|لا يمكن|رفض|عفواً|فشل|أمسك|فاضي/.test(s);
        const isWarn = /تنبيه|تحذير|يرجى|تأكد/.test(s);
        floatingToast(s, isErr ? 'error' : (isWarn ? 'warning' : 'success'), isErr ? 5200 : 3800);
    };

    // ── POINT 7: Regulatory Guards (Bankak Duplicate & Outbound Stock Guard) ──
    const normalizeTrxRef = (ref) => String(ref || '').replace(/[\s\-_#]/g, '').toUpperCase();

    const checkBankakDuplicate = (ref, imgHash = '', ignoreInvoiceNo = '') => {
        const cleanRef = normalizeTrxRef(ref);
        if (!cleanRef && !imgHash) return { duplicate: false };
        const reg = readDualArray('erp_bankak_trx_registry_v1');
        if (cleanRef && cleanRef.length >= 4) {
            const hitReg = reg.find(x => x && normalizeTrxRef(x.ref) === cleanRef && String(x.invoiceNo || '') !== String(ignoreInvoiceNo || ''));
            if (hitReg) return { duplicate: true, source: hitReg.invoiceNo || 'سجل الإشعارات', message: BANKAK_DUP_MSG };
            const pmts = readDualArray('erp_single_payments_v1');
            const hitPmt = pmts.find(p => p && normalizeTrxRef(p.reference || p.trxNo) === cleanRef && String(p.refInvoice || '') !== String(ignoreInvoiceNo || ''));
            if (hitPmt) return { duplicate: true, source: hitPmt.paymentNo || 'سندات الصرف', message: BANKAK_DUP_MSG };
            const purs = readDualArray('erp_purchase_invoices_v1');
            for (const inv of purs) {
                if (!inv || String(inv.invoiceNo || inv.id) === String(ignoreInvoiceNo || '')) continue;
                const bts = Array.isArray(inv.bankTransfers) ? inv.bankTransfers : [];
                if (bts.some(b => normalizeTrxRef(b.reference) === cleanRef)) {
                    return { duplicate: true, source: inv.invoiceNo || inv.id, message: BANKAK_DUP_MSG };
                }
            }
        }
        if (imgHash) {
            const hitImg = reg.find(x => x && x.imgHash && x.imgHash === imgHash && String(x.invoiceNo || '') !== String(ignoreInvoiceNo || ''));
            if (hitImg) return { duplicate: true, source: hitImg.invoiceNo || 'بصمة الصورة', message: BANKAK_DUP_MSG };
        }
        return { duplicate: false };
    };

    const registerBankakReceipts = (bankTransfers, invoiceNo, partnerName, dateStr) => {
        if (!Array.isArray(bankTransfers) || !bankTransfers.length) return;
        const reg = readDualArray('erp_bankak_trx_registry_v1');
        bankTransfers.forEach(bt => {
            const cleanRef = normalizeTrxRef(bt.reference);
            if (!cleanRef && !bt.imgHash) return;
            if (!reg.some(x => x && normalizeTrxRef(x.ref) === cleanRef && String(x.invoiceNo) === String(invoiceNo))) {
                reg.unshift({
                    ref: bt.reference || '',
                    normRef: cleanRef,
                    imgHash: bt.imgHash || '',
                    amount: parseFloat(bt.amount) || 0,
                    invoiceNo: invoiceNo,
                    partnerName: partnerName || '',
                    date: dateStr || new Date().toISOString().slice(0, 10),
                    ts: Date.now()
                });
            }
        });
        writeDualStorage('erp_bankak_trx_registry_v1', reg.slice(0, 5000));
    };

    const checkDuplicateChequeNo = (chequeNo, ignoreInvoiceNo = '') => {
        const cleanNo = normalizeTrxRef(chequeNo);
        if (!cleanNo) return { duplicate: false };
        const checks = readDualArray('erp_checks_v1');
        const hit = checks.find(c => c && normalizeTrxRef(c.checkNumber || c.chequeNo) === cleanNo && String(c.refInvoice || '') !== String(ignoreInvoiceNo || ''));
        return hit ? { duplicate: true, existing: hit } : { duplicate: false };
    };

    const checkSameDayDuplicateInvoice = (custId, grandAmount, rowsArr, dateStr) => {
        const day = String(dateStr || '').slice(0, 10);
        const itemsSig = (Array.isArray(rowsArr) ? rowsArr : [])
            .filter(r => r && r.itemId)
            .map(r => `${r.itemId}:${parseFloat(r.quantity || r.qty || 0)}:${parseFloat(r.price || 0)}`)
            .sort()
            .join('|');
        const purs = readDualArray('erp_purchase_invoices_v1');
        const hit = purs.find(inv => {
            if (!inv || inv.deleted) return false;
            if (String(inv.date || '').slice(0, 10) !== day) return false;
            if (String(inv.supplierId || inv.customer || '') !== String(custId || '')) return false;
            if (Math.abs((parseFloat(inv.grandTotal || inv.total || 0)) - (parseFloat(grandAmount) || 0)) > 0.01) return false;
            const invSig = (Array.isArray(inv.items) ? inv.items : [])
                .filter(r => r && r.itemId)
                .map(r => `${r.itemId}:${parseFloat(r.quantity || r.qty || 0)}:${parseFloat(r.price || 0)}`)
                .sort()
                .join('|');
            return invSig === itemsSig;
        });
        return hit || null;
    };

    const checkOutboundStockGuard = (itemObj, requiredBaseQty, allowNegativeOverride = false) => {
        if (allowNegativeOverride) return { allowed: true };
        try {
            const u = JSON.parse(localStorage.getItem('user') || '{}');
            if (u.allowNegativeStock === true) return { allowed: true };
        } catch (_) {}
        const curStock = itemObj
            ? (itemObj.stock && typeof itemObj.stock === 'object'
                ? Object.entries(itemObj.stock).filter(([k]) => k !== 'PENDING_RECEIPT').reduce((a, [, v]) => a + (parseFloat(v) || 0), 0)
                : (parseFloat(itemObj.currentStock ?? itemObj.quantity ?? itemObj.stock ?? 0) || 0))
            : 0;
        if (curStock <= 0 || curStock < requiredBaseQty) {
            return { allowed: false, currentStock: curStock, message: EMPTY_STOCK_MSG };
        }
        return { allowed: true, currentStock: curStock };
    };
    window.elsajanaCheckOutboundStockGuard = checkOutboundStockGuard;

    // ── POINT 2: Elsajana Steel Unit Equation (135 / 140 / 15) + Bascule + Profit Erosion Alert ──
    const ELSAJANA_STEEL_UNITS = {
        COUNT_TON_BARS: 135,
        WEIGHT_TON_BARS: 140,
        SHEILA_BARS: 15
    };

    const checkLandedCostMarginErosion = (itemsList, landedAdditionsLocal, silentIfOk = true) => {
        const active = (Array.isArray(itemsList) ? itemsList : []).filter(r => r && r.itemId && (parseFloat(r.quantity || r.qty) || 0) > 0);
        if (!active.length || landedAdditionsLocal <= 0) return [];
        const allItems = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : readDualArray('erp_items_v1');
        const totalSub = active.reduce((s, r) => s + ((parseFloat(r.price) || 0) * (parseFloat(r.quantity || r.qty) || 0)), 0);
        const eroded = [];
        active.forEach(r => {
            const it = allItems.find(i => String(i.id) === String(r.itemId));
            if (!it) return;
            const sellPrice = parseFloat(it.sellingPrice || it.price || 0) || 0;
            if (sellPrice <= 0) return;
            const rowVal = (parseFloat(r.price) || 0) * (parseFloat(r.quantity || r.qty) || 0);
            const share = totalSub > 0 ? (landedAdditionsLocal * (rowVal / totalSub)) : 0;
            const factor = parseFloat(r.customFactor || 1) || 1;
            const baseQty = (parseFloat(r.quantity || r.qty) || 0) * factor;
            const landedUnitCost = baseQty > 0 ? ((rowVal + share) / baseQty) : 0;
            if (landedUnitCost >= sellPrice * 0.96) {
                eroded.push({ name: it.name, landedUnitCost: Math.round(landedUnitCost * 100) / 100, sellPrice });
            }
        });
        if (eroded.length > 0) {
            const first = eroded[0];
            floatingToast(`🚨 تنبيه للمالك (تآكل هامش الربح): تكلفة العتالة والنقل رفعت تكلفة "${first.name}" إلى (${first.landedUnitCost.toLocaleString()}) مقابل سعر بيع (${first.sellPrice.toLocaleString()})!`, 'warning', 6500);
        } else if (!silentIfOk) {
            floatingToast('✅ هامش الربح آمن بعد تحميل مصروفات النقل والعتالة', 'success', 2600);
        }
        return eroded;
    };

    // ── POINT 1: 0ms Atomic Cross-System Sync (Journal, Trial Balance, Stock Movements, Supplier, Safe/Bank, Checks) ──
    let _invoiceIndexMap = null;
    const rebuildFastInvoiceIndex = (arr) => {
        _invoiceIndexMap = new Map();
        (Array.isArray(arr) ? arr : []).forEach(inv => {
            const k = inv && (inv.invoiceNo || inv.id);
            if (k) _invoiceIndexMap.set(String(k), inv);
        });
        return _invoiceIndexMap;
    };

    const atomicCrossSystemSync = (invData) => {
        if (!invData || !invData.invoiceNo) return;
        const invNo = String(invData.invoiceNo);
        const rate = parseFloat(invData.rate || invData.exchangeRate || 1) || 1;
        const isBase = (invData.currency || 'SDG') === 'SDG';
        const toLocal = (v) => (parseFloat(v) || 0) * (isBase ? 1 : rate);

        // 1. Register Bankak / Fawry receipts
        registerBankakReceipts(invData.bankTransfers, invNo, invData.supplierName, invData.date);

        // 2. Sync tenant-prefixed + global core tables
        ['erp_suppliers_v1', 'erp_items_v1', 'erp_inventory_v1', 'erp_banks_v1', 'erp_safes_v1', 'erp_checks_v1', 'erp_single_payments_v1', 'erp_purchase_invoices_v1'].forEach(k => {
            const curVal = localStorage.getItem(k);
            if (curVal) writeDualStorage(k, curVal);
        });

        // 3. Item Card & Bascule Weight Movements (erp_item_movements_v1)
        const movs = readDualArray('erp_item_movements_v1').filter(m => !(m && m.docNo === invNo && m.docType === 'purchase'));
        (Array.isArray(invData.items) ? invData.items : []).forEach(r => {
            const qty = parseFloat(r.quantity || r.qty || 0) || 0;
            const factor = parseFloat(r.customFactor || 1) || 1;
            const baseBars = qty * factor;
            const estTonsWeight = Math.round((baseBars / ELSAJANA_STEEL_UNITS.WEIGHT_TON_BARS) * 1000) / 1000;
            movs.unshift({
                id: `MOV-PUR-${invNo}-${r.itemId}`,
                docNo: invNo,
                docType: 'purchase',
                drillUrl: `purchase.php?action=direct&inv=${encodeURIComponent(invNo)}`,
                date: invData.date,
                itemId: r.itemId,
                itemName: r.itemName || r.name,
                warehouse: r.warehouse || invData.warehouseName || 'المخزن الرئيسي',
                unit: r.unit || r.selectedUnit || 'وحدة',
                inQty: qty,
                outQty: 0,
                baseInQty: baseBars,
                weightTons: estTonsWeight,
                unitCost: toLocal(r.price),
                totalCost: toLocal(r.total || (qty * (parseFloat(r.price) || 0))),
                partnerName: invData.supplierName || 'مورد عام',
                timestamp: Date.now()
            });
        });
        writeDualStorage('erp_item_movements_v1', movs.slice(0, 15000));

        // 4. Atomic Balanced Double-Entry Journal Entry (اليومية العامة + ميزان المراجعة + الأرباح والخسائر والميزانية)
        const jEntries = readDualArray('erp_journal_entries_v1').filter(j => !(j && (j.refNo === invNo || j.sourceId === invNo) && j.sourceType === 'purchase'));
        if (invData.status === 'posted' && invData.saleType !== 'proforma') {
            const grandLoc = toLocal(invData.grandTotal || invData.total || 0);
            const cashLoc = toLocal(invData.cashAmount || 0);
            const bankLoc = (Array.isArray(invData.bankTransfers) ? invData.bankTransfers : []).reduce((s, b) => s + toLocal(b.amount), 0);
            const chqLoc = (Array.isArray(invData.cheques) ? invData.cheques : []).reduce((s, c) => s + toLocal(c.amount), 0);
            const paidTotal = cashLoc + bankLoc + chqLoc;
            const creditLoc = Math.max(0, grandLoc - paidTotal);
            const overpaidLoc = Math.max(0, paidTotal - grandLoc);

            const lines = [
                { accountCode: '1104', accountName: 'المخزون السلعي / المشتريات (شامل التكلفة المضافة)', debit: grandLoc, credit: 0, statementType: 'balance_sheet' }
            ];
            if (overpaidLoc > 0) {
                lines.push({ accountCode: '2101', accountName: `الموردون - ${invData.supplierName || 'مورد'}`, debit: overpaidLoc, credit: 0, partnerId: invData.supplierId });
            }
            if (cashLoc > 0) lines.push({ accountCode: '1101', accountName: 'الخزينة النقدية', debit: 0, credit: cashLoc });
            if (bankLoc > 0) lines.push({ accountCode: '1102', accountName: 'البنوك (بنكك / فوري)', debit: 0, credit: bankLoc });
            if (chqLoc > 0) lines.push({ accountCode: '2103', accountName: 'حافظة الشيكات الصادرة (حساب وسيط)', debit: 0, credit: chqLoc });
            if (creditLoc > 0) lines.push({ accountCode: '2101', accountName: `الموردون - ${invData.supplierName || 'مورد'}`, debit: 0, credit: creditLoc, partnerId: invData.supplierId });

            jEntries.unshift({
                id: `JE-PUR-${invNo}`,
                entryNo: `JV-PUR-${invNo}`,
                refNo: invNo,
                sourceId: invNo,
                sourceType: 'purchase',
                drillUrl: `purchase.php?action=direct&inv=${encodeURIComponent(invNo)}`,
                date: invData.date,
                description: `فاتورة مشتريات #${invNo} - ${invData.supplierName || 'مورد عام'}`,
                totalDebit: grandLoc + overpaidLoc,
                totalCredit: cashLoc + bankLoc + chqLoc + creditLoc,
                balanced: Math.abs((grandLoc + overpaidLoc) - (cashLoc + bankLoc + chqLoc + creditLoc)) < 0.05,
                lines,
                createdAt: new Date().toISOString()
            });
            writeDualStorage('erp_journal_entries_v1', jEntries.slice(0, 20000));

            // Instant Trial Balance Aggregation Cache
            const tbMap = {};
            jEntries.forEach(je => {
                (je.lines || []).forEach(ln => {
                    const k = ln.accountCode || ln.accountName;
                    if (!tbMap[k]) tbMap[k] = { code: ln.accountCode, name: ln.accountName, debit: 0, credit: 0 };
                    tbMap[k].debit += parseFloat(ln.debit) || 0;
                    tbMap[k].credit += parseFloat(ln.credit) || 0;
                });
            });
            writeDualStorage('erp_trial_balance_cache_v1', { updatedAt: Date.now(), accounts: Object.values(tbMap) });
        }

        // 5. Check Landed Cost Margin Erosion
        const landedLocal = (parseFloat(invData.laborCost) || 0) + (parseFloat(invData.transportCost) || 0) + (parseFloat(invData.additionsCost) || 0);
        if (landedLocal > 0) checkLandedCostMarginErosion(invData.items, landedLocal, true);

        // 6. Rebuild O(1) index & Broadcast 0ms Sync across all tabs/reports
        rebuildFastInvoiceIndex(readDualArray('erp_purchase_invoices_v1'));
        try {
            const payload = { type: 'atomic_erp_sync', source: 'purchase', invoiceNo: invNo, drillUrl: `purchase.php?action=direct&inv=${encodeURIComponent(invNo)}`, ts: Date.now() };
            ['magaiees_sync_channel', 'erp_live_sync', 'erp_db_sync'].forEach(chName => {
                if (typeof BroadcastChannel !== 'undefined') {
                    const bc = new BroadcastChannel(chName);
                    bc.postMessage(payload);
                    bc.close();
                }
            });
        } catch (_) {}
    };

    // ── POINT 4: Flash Sync / CRDT Additive Offline Merge ──
    const getDeviceId = () => {
        let id = localStorage.getItem('magaies_crdt_device_id');
        if (!id) {
            id = 'DEV-' + Math.random().toString(36).slice(2, 9).toUpperCase();
            try { localStorage.setItem('magaies_crdt_device_id', id); } catch (_) {}
        }
        return id;
    };

    const crdtMergeInvoices = (localArr, remoteArr) => {
        const map = new Map();
        const ingest = (list) => {
            (Array.isArray(list) ? list : []).forEach(inv => {
                if (!inv) return;
                const key = String(inv.idempotencyKey || inv.invoiceNo || inv.id || '').trim();
                if (!key) return;
                const prev = map.get(key);
                if (!prev) {
                    map.set(key, inv);
                } else {
                    const tPrev = Date.parse(prev.updatedAt || prev.timestamp || 0) || 0;
                    const tCur = Date.parse(inv.updatedAt || inv.timestamp || 0) || 0;
                    map.set(key, tCur >= tPrev ? Object.assign({}, prev, inv) : Object.assign({}, inv, prev));
                }
            });
        };
        ingest(remoteArr);
        ingest(localArr);
        return Array.from(map.values());
    };

    let _flashBusy = false;
    const triggerFlashSyncSpark = async () => {
        if (!navigator.onLine || _flashBusy) return;
        _flashBusy = true;
        try {
            const q = readDualArray('erp_purchase_offline_queue_v1');
            if (q.length > 0) {
                const remain = [];
                for (const op of q) {
                    if (!op || !op.payload) continue;
                    op.payload._crdtDevice = getDeviceId();
                    op.payload._crdtHlc = Date.now();
                    try {
                        const r = await fetch('api/mirror_sync.php?action=' + (op.type === 'purchase_split' ? 'save_purchase_split' : 'save_purchase_invoice'), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            keepalive: true,
                            body: JSON.stringify(op.payload)
                        }).then(x => x.json());
                        if (!r || !r.success) remain.push(op);
                    } catch (_) { remain.push(op); }
                }
                writeDualStorage('erp_purchase_offline_queue_v1', remain);
            }
        } catch (_) {} finally { _flashBusy = false; }
    };

    // ── POINT 3B: Camera Image Compression <= 40KB + Perceptual Hash + WhatsApp Bankak/Fawry Parser ──
    const compressImageTo40KB = (file) => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onerror = reject;
        reader.onload = (ev) => {
            const img = new Image();
            img.onerror = reject;
            img.onload = () => {
                const maxDim = 900;
                let w = img.width, h = img.height;
                if (w > maxDim || h > maxDim) {
                    const r = Math.min(maxDim / w, maxDim / h);
                    w = Math.round(w * r);
                    h = Math.round(h * r);
                }
                const canvas = document.createElement('canvas');
                canvas.width = w; canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);

                // Compute 8x8 grayscale perceptual hash (aHash)
                const hc = document.createElement('canvas');
                hc.width = 8; hc.height = 8;
                const hctx = hc.getContext('2d');
                hctx.drawImage(img, 0, 0, 8, 8);
                const px = hctx.getImageData(0, 0, 8, 8).data;
                const grays = [];
                for (let i = 0; i < px.length; i += 4) grays.push(0.299 * px[i] + 0.587 * px[i + 1] + 0.114 * px[i + 2]);
                const avg = grays.reduce((a, b) => a + b, 0) / grays.length;
                const imgHash = 'AHASH-' + grays.map(g => (g >= avg ? '1' : '0')).join('');

                let quality = 0.68;
                let dataUrl = canvas.toDataURL('image/jpeg', quality);
                while (dataUrl.length * 0.75 > 40960 && quality > 0.16) {
                    quality -= 0.1;
                    dataUrl = canvas.toDataURL('image/jpeg', quality);
                }
                if (dataUrl.length * 0.75 > 40960) {
                    canvas.width = Math.round(w * 0.68);
                    canvas.height = Math.round(h * 0.68);
                    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                    dataUrl = canvas.toDataURL('image/jpeg', 0.42);
                }
                const kb = Math.round((dataUrl.length * 0.75) / 1024);
                resolve({ dataUrl, imgHash, sizeKB: kb });
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });

    const parseBankakWhatsAppText = (rawText) => {
        const txt = String(rawText || '').replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
        const refMatch = txt.match(/(?:رقم العملية|الرقم المرجعي|مرجع|Trx\s*ID|Transaction\s*(?:ID|No)|Ref(?:\s*No)?|رقم المعاملة)\s*[:#\-]?\s*([A-Za-z0-9]{5,24})/i)
            || txt.match(/\b(\d{9,16})\b/);
        const amtMatch = txt.match(/(?:المبلغ|مبلغ|Amount|SDG|ج\.س|جنيه)\s*[:\-]?\s*([0-9]{1,3}(?:,[0-9]{3})+(?:\.[0-9]+)?|[0-9]+(?:\.[0-9]+)?)/i)
            || txt.match(/([0-9]{1,3}(?:,[0-9]{3})+(?:\.[0-9]{2})?)\s*(?:SDG|ج\.س|جنيه)/i);
        const dateMatch = txt.match(/(\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}|\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/);
        const senderMatch = txt.match(/(?:من حساب|المرسل|اسم المرسل|From|Account\s*Name|المستفيد)\s*[:\-]?\s*([^\n\r0-9]{3,45})/i);
        const isFawry = /فوري|fawry/i.test(txt);
        return {
            provider: isFawry ? 'فوري' : 'بنكك',
            trxNo: refMatch ? refMatch[1].trim() : '',
            amount: amtMatch ? parseFloat(amtMatch[1].replace(/,/g, '')) || 0 : 0,
            date: dateMatch ? dateMatch[1] : new Date().toISOString().slice(0, 10),
            senderName: senderMatch ? senderMatch[1].trim() : ''
        };
    };

    // ── UI MODALS (Inheriting active theme cleanly without altering purchase.php styling) ──
    const createModalShell = (id, title, bodyHtml, maxWidth = '720px') => {
        let m = document.getElementById(id);
        if (m) m.remove();
        const isLight = document.documentElement.classList.contains('light-theme');
        const bg = isLight ? '#fffdf8' : '#0f172a';
        const fg = isLight ? '#0f172a' : '#ffffff';
        const brd = isLight ? '#854d0e' : '#38bdf8';
        m = document.createElement('div');
        m.id = id;
        m.style.cssText = 'position:fixed;inset:0;z-index:999998;background:rgba(2,6,23,0.72);display:flex;align-items:center;justify-content:center;padding:14px;direction:rtl;font-family:Tajawal,Cairo,sans-serif;';
        m.innerHTML = `<div style="width:100%;max-width:${maxWidth};max-height:90vh;overflow-y:auto;background:${bg};color:${fg};border:2.5px solid ${brd};border-radius:16px;padding:16px;box-shadow:0 22px 50px rgba(0,0,0,0.65);">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid ${brd};padding-bottom:10px;margin-bottom:12px;">
                <strong style="font-size:1.08rem;font-weight:900;">${title}</strong>
                <button type="button" onclick="document.getElementById('${id}').remove()" style="background:#dc2626;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;border-radius:8px;padding:4px 12px;font-weight:900;cursor:pointer;">✕ إغلاق</button>
            </div>
            <div>${bodyHtml}</div>
        </div>`;
        m.addEventListener('click', (e) => { if (e.target === m) m.remove(); });
        document.body.appendChild(m);
        return m;
    };

    // 1. Bankak & Fawry Smart WhatsApp + 40KB Camera Reader Modal
    const openBankakFawrySmartModal = () => {
        const html = `
            <div style="display:grid;gap:10px;">
                <label style="font-weight:900;font-size:0.9rem;">📋 الصق نص إشعار (بنكك / فوري) من الواتساب أو التقط صورة الإشعار (تُضغط آلياً ≤ 40KB):</label>
                <textarea id="els-bk-wa-txt" rows="3" placeholder="مثال: تم تحويل مبلغ 1,500,000 ج.س رقم العملية 9988776655 من حساب محمد أحمد..." style="width:100%;padding:9px;border-radius:9px;border:2px solid #0284c7;font-weight:800;font-family:inherit;"></textarea>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <input type="file" id="els-bk-img-inp" accept="image/*" capture="environment" style="display:none;">
                    <button type="button" onclick="document.getElementById('els-bk-img-inp').click()" style="padding:7px 14px;border-radius:9px;background:#0284c7;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;cursor:pointer;">📷 تصوير / رفع إشعار (ضغط 40KB)</button>
                    <button type="button" id="els-bk-parse-btn" style="padding:7px 14px;border-radius:9px;background:#059669;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;cursor:pointer;">⚡ استخراج وفحص فوري</button>
                    <span id="els-bk-compress-badge" style="font-size:0.82rem;font-weight:900;color:#059669;"></span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:8px;margin-top:4px;">
                    <div><label style="font-size:0.8rem;font-weight:900;">المبلغ:</label><input id="els-bk-amt" type="number" step="0.01" style="width:100%;padding:7px;border-radius:8px;border:1.5px solid #64748b;font-weight:900;"></div>
                    <div><label style="font-size:0.8rem;font-weight:900;">رقم العملية المرجعي:</label><input id="els-bk-ref" type="text" style="width:100%;padding:7px;border-radius:8px;border:1.5px solid #64748b;font-weight:900;"></div>
                    <div><label style="font-size:0.8rem;font-weight:900;">التاريخ:</label><input id="els-bk-date" type="date" value="${new Date().toISOString().slice(0,10)}" style="width:100%;padding:7px;border-radius:8px;border:1.5px solid #64748b;font-weight:900;"></div>
                    <div><label style="font-size:0.8rem;font-weight:900;">اسم المرسل:</label><input id="els-bk-sender" type="text" style="width:100%;padding:7px;border-radius:8px;border:1.5px solid #64748b;font-weight:900;"></div>
                </div>
                <input type="hidden" id="els-bk-imghash" value="">
                <button type="button" id="els-bk-apply-btn" style="margin-top:6px;padding:10px;border-radius:10px;background:linear-gradient(135deg,#d97706,#b45309);color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;font-size:0.96rem;cursor:pointer;">✓ فحص عدم التكرار وتعبئة خانة التحويل البنكي تلقائياً</button>
            </div>`;
        createModalShell('elsajana-bankak-modal', '🏦 الذكاء الاصطناعي لإشعارات (بنكك وفوري) — فحص فوري وضغط 40KB', html, '650px');

        document.getElementById('els-bk-parse-btn').onclick = () => {
            const p = parseBankakWhatsAppText(document.getElementById('els-bk-wa-txt').value);
            if (p.amount) document.getElementById('els-bk-amt').value = p.amount;
            if (p.trxNo) document.getElementById('els-bk-ref').value = p.trxNo;
            if (p.senderName) document.getElementById('els-bk-sender').value = p.senderName;
            const dup = checkBankakDuplicate(p.trxNo, document.getElementById('els-bk-imghash').value, window.currentViewedInvoiceNo || '');
            if (dup.duplicate) shakeAndFocus('#els-bk-ref', BANKAK_DUP_MSG, 'error', 5500);
            else floatingToast(`✅ تم قراءة إشعار ${p.provider} بنجاح — جاهز للاعتماد`, 'success', 2600);
        };

        document.getElementById('els-bk-img-inp').onchange = async (e) => {
            const f = e.target.files && e.target.files[0];
            if (!f) return;
            const badge = document.getElementById('els-bk-compress-badge');
            badge.textContent = '⏳ جاري ضغط الصورة وفحص البصمة...';
            try {
                const res = await compressImageTo40KB(f);
                document.getElementById('els-bk-imghash').value = res.imgHash;
                badge.textContent = `✅ تم ضغط الصورة إلى (${res.sizeKB} KB ≤ 40KB) وفحص البصمة`;
                const dup = checkBankakDuplicate('', res.imgHash, window.currentViewedInvoiceNo || '');
                if (dup.duplicate) {
                    shakeAndFocus('#els-bk-ref', BANKAK_DUP_MSG, 'error', 5500);
                    return;
                }
                if (window.Tesseract && typeof window.Tesseract.recognize === 'function') {
                    const ocr = await window.Tesseract.recognize(res.dataUrl, 'ara+eng');
                    if (ocr && ocr.data && ocr.data.text) {
                        document.getElementById('els-bk-wa-txt').value = ocr.data.text;
                        document.getElementById('els-bk-parse-btn').click();
                    }
                }
            } catch (_) { badge.textContent = '⚠️ تعذر قراءة الصورة'; }
        };

        document.getElementById('els-bk-apply-btn').onclick = () => {
            const amt = parseFloat(document.getElementById('els-bk-amt').value) || 0;
            const ref = document.getElementById('els-bk-ref').value.trim();
            const sender = document.getElementById('els-bk-sender').value.trim();
            const imgHash = document.getElementById('els-bk-imghash').value.trim();
            if (amt <= 0) { shakeAndFocus('#els-bk-amt', '⚠️ يرجى إدخال مبلغ الإشعار الصحيح', 'warning'); return; }
            if (!ref) { shakeAndFocus('#els-bk-ref', '⚠️ يرجى إدخال رقم العملية المرجعي للإشعار', 'warning'); return; }
            const dup = checkBankakDuplicate(ref, imgHash, window.currentViewedInvoiceNo || '');
            if (dup.duplicate) { shakeAndFocus('#els-bk-ref', BANKAK_DUP_MSG, 'error', 5500); return; }

            const tglBank = document.getElementById('toggle-bank');
            if (tglBank && !tglBank.classList.contains('active')) tglBank.click();
            setTimeout(() => {
                let row = document.querySelector('#bank-transfers-list .payment-row-item:last-child');
                if (!row && typeof window.addBankTransferRow === 'function') { window.addBankTransferRow(); row = document.querySelector('#bank-transfers-list .payment-row-item:last-child'); }
                if (row) {
                    const amtInp = row.querySelector('.bt-amount-input');
                    const refInp = row.querySelector('.bt-ref-input') || row.querySelector('.bt-no-input');
                    const noteInp = row.querySelector('.bt-note-input');
                    if (amtInp) { amtInp.value = amt; amtInp.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (refInp) { refInp.value = ref; refInp.dataset.imgHash = imgHash; }
                    if (noteInp) noteInp.value = sender ? `مرسل: ${sender}` : 'إشعار بنكك/فوري مفحوص بالذكاء';
                } else if (document.getElementById('mcp-proportional-root')) {
                    const blk = document.querySelector('#accounts-blocks-container .account-block:last-child') || document.querySelector('.account-block');
                    if (blk) {
                        const mainAmt = blk.querySelector('.block-amount-input, .account-amount-input');
                        if (mainAmt) { mainAmt.value = amt; mainAmt.dispatchEvent(new Event('input', { bubbles: true })); }
                        const bChk = blk.querySelector('.method-bank-chk, [data-method="bank"]');
                        if (bChk && bChk.type === 'checkbox' && !bChk.checked) { bChk.checked = true; bChk.dispatchEvent(new Event('change', { bubbles: true })); }
                        else if (bChk && !bChk.classList.contains('active')) bChk.click();
                        setTimeout(() => {
                            const tNo = blk.querySelector('.block-transfer-no-input, .transfer-no');
                            const tAm = blk.querySelector('.block-transfer-amount, .transfer-amount');
                            const tSt = blk.querySelector('.block-statement-input');
                            if (tNo) { tNo.value = ref; tNo.dataset.imgHash = imgHash; tNo.dispatchEvent(new Event('input', { bubbles: true })); }
                            if (tAm) { tAm.value = amt; tAm.dispatchEvent(new Event('input', { bubbles: true })); }
                            if (tSt && !tSt.value) tSt.value = `تحويل بنكك/فوري رقم ${ref}${sender ? ' — ' + sender : ''}`;
                        }, 40);
                    }
                } else if (document.getElementById('scp-proportional-root')) {
                    const amtEl = document.getElementById('amount-local');
                    if (amtEl) { amtEl.value = amt; amtEl.dispatchEvent(new Event('input', { bubbles: true })); }
                    const bankRadio = document.querySelector('input[name="payment_method"][value="bank"], input[name="payment_method"][value="transfer"], input[name="payment_method"][value="hybrid"]');
                    if (bankRadio && !bankRadio.checked) { bankRadio.checked = true; bankRadio.dispatchEvent(new Event('change', { bubbles: true })); }
                    const trNoInp = document.querySelector('.transfer-row .transfer-no') || document.getElementById('mobile-transfer-no');
                    const trAmtInp = document.querySelector('.transfer-row .transfer-amount');
                    const trNoteInp = document.querySelector('.transfer-row .transfer-note');
                    if (trNoInp) { trNoInp.value = ref; trNoInp.dataset.imgHash = imgHash; trNoInp.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (trAmtInp) { trAmtInp.value = amt; trAmtInp.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (trNoteInp && !trNoteInp.value) trNoteInp.value = sender ? `بنكك/فوري — ${sender}` : 'بنكك/فوري مفحوص بالذكاء';
                    const stmtEl = document.getElementById('to-statement');
                    if (stmtEl && !stmtEl.value) stmtEl.value = `تحويل بنكك/فوري رقم ${ref}${sender ? ' — ' + sender : ''}`;
                } else if (document.getElementById('sl-gate-pass-no') || document.getElementById('grn-proportional-root')) {
                    const invNo = (typeof slCurrentDoc !== 'undefined' && slCurrentDoc) ? slCurrentDoc.invoiceNo : (typeof currentInvoiceId !== 'undefined' && currentInvoiceId ? currentInvoiceId : 'GRN-DOC');
                    const pName = document.getElementById('sl-lbl-party-name')?.textContent || document.getElementById('lbl-supplier')?.textContent || sender || 'مورد';
                    registerBankakReceipts([{ reference: ref, imgHash, amount: amt }], invNo, pName, new Date().toISOString().slice(0, 10));
                    const pmts = readDualArray('erp_single_payments_v1');
                    pmts.unshift({ id: 'pay-' + Date.now(), paymentNo: 'PMT-GRN-' + ref, date: new Date().toISOString().slice(0, 10), type: 'supplier', partnerId: window._grnCurrentSupplierId || '', partnerName: pName, amount: amt, currency: 'SDG', paymentMethod: 'bank', reference: ref, refInvoice: invNo, notes: `إشعار بنكك/فوري مفحوص (${sender})` });
                    writeDualStorage('erp_single_payments_v1', pmts);
                    const gp = document.getElementById('sl-gate-pass-no');
                    if (gp) gp.value = ref;
                } else if (document.getElementById('pa-proportional-root')) {
                    registerBankakReceipts([{ reference: ref, imgHash, amount: amt }], window.currentViewedInvoiceNo || 'PA-VERIFY', sender || 'عميل واتساب', new Date().toISOString().slice(0, 10));
                    const sInp = document.getElementById('search-input');
                    if (sInp) { sInp.value = ref; if (typeof window.handleFilterChange === 'function') window.handleFilterChange(); }
                }
                if (typeof window.recalcAll === 'function') window.recalcAll();
                document.getElementById('elsajana-bankak-modal')?.remove();
                floatingToast(`✅ تم تعبئة وتوثيق إشعار بنكك رقم (${ref}) بمبلغ (${amt.toLocaleString()})`, 'success', 3200);
            }, 60);
        };
    };

    // 2. Elsajana Bascule Weight & Partial Inspection ("فحص واستلام") Modal
    const openInspectionAndBasculeModal = () => {
        const isSl = typeof slCurrentDoc !== 'undefined' && slCurrentDoc && Array.isArray(slCurrentDoc.items);
        const isGrnPage = !isSl && typeof receiptLines !== 'undefined' && Array.isArray(receiptLines) && receiptLines.length > 0;
        const activeRows = isSl
            ? slCurrentDoc.items
            : (isGrnPage ? receiptLines : ((typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId) : []));
        const rowsHtml = activeRows.length ? activeRows.map((r, i) => {
            const q = parseFloat(r.receiveQty ?? r.remainingQty ?? r.quantity ?? r.qty ?? 0) || 0;
            const rName = r.itemName || r.name || (typeof getItemName === 'function' ? getItemName(r.itemId) : r.itemId);
            return `<tr data-ridx="${i}">
                <td style="padding:6px;font-weight:900;">${rName}</td>
                <td style="padding:6px;text-align:center;font-weight:900;">${q} (${r.unitName || r.selectedUnit || r.unit || 'وحدة'})</td>
                <td style="padding:4px;"><input type="number" class="grn-ok" value="${q}" step="0.01" style="width:74px;padding:4px;font-weight:900;border:1.5px solid #059669;border-radius:6px;"></td>
                <td style="padding:4px;"><input type="number" class="grn-dmg" value="0" step="0.01" style="width:66px;padding:4px;font-weight:900;border:1.5px solid #d97706;border-radius:6px;"></td>
                <td style="padding:4px;"><input type="number" class="grn-rej" value="0" step="0.01" style="width:66px;padding:4px;font-weight:900;border:1.5px solid #dc2626;border-radius:6px;"></td>
                <td style="padding:4px;"><input type="number" class="grn-diff" value="0" step="0.01" style="width:66px;padding:4px;font-weight:900;border:1.5px solid #0284c7;border-radius:6px;"></td>
            </tr>`;
        }).join('') : `<tr><td colspan="6" style="padding:12px;text-align:center;font-weight:900;">اختر مستنداً أو أضف أصنافاً أولاً لفرزها وفحصها</td></tr>`;

        const html = `
            <div style="background:rgba(2,132,199,0.1);border:1.5px solid #0284c7;border-radius:10px;padding:10px;margin-bottom:12px;">
                <div style="font-weight:900;margin-bottom:6px;">⚖️ حاسبة ميزان البسكول والمصروفات المضافة (الطن العددي = 135 | الطن الوزني = 140 | الشيلة = 15):</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(125px,1fr));gap:8px;">
                    <div><label style="font-size:0.78rem;font-weight:900;">وزن الفاتورة (طن):</label><input id="basc-expected" type="number" step="0.001" value="10" style="width:100%;padding:5px;border-radius:6px;font-weight:900;"></div>
                    <div><label style="font-size:0.78rem;font-weight:900;">وزن القائم (طن):</label><input id="basc-gross" type="number" step="0.001" value="25" style="width:100%;padding:5px;border-radius:6px;font-weight:900;"></div>
                    <div><label style="font-size:0.78rem;font-weight:900;">وزن الفارغ (طن):</label><input id="basc-tare" type="number" step="0.001" value="15" style="width:100%;padding:5px;border-radius:6px;font-weight:900;"></div>
                    <div><label style="font-size:0.78rem;font-weight:900;">🚚 عتالة ونقل ومشال:</label><input id="basc-landed" type="number" step="1" value="0" placeholder="ج.س" style="width:100%;padding:5px;border-radius:6px;font-weight:900;border:1.5px solid #d97706;"></div>
                </div>
                <div id="basc-result-box" style="margin-top:8px;font-weight:900;font-size:0.88rem;color:#0284c7;"></div>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:0.86rem;">
                    <thead><tr style="border-bottom:2px solid #64748b;">
                        <th style="text-align:right;padding:6px;">الصنف</th><th>كمية الفاتورة</th><th>✅ سليم</th><th>⚠️ تالف</th><th>🚫 مرفوض</th><th>⚖️ فرق ميزان</th>
                    </tr></thead>
                    <tbody id="els-grn-tbody">${rowsHtml}</tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
                <button type="button" id="els-apply-grn-btn" style="padding:9px 18px;border-radius:9px;background:#059669;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;cursor:pointer;">✓ اعتماد الاستلام الفعلي (الجزئي/الكامل) وتحديث الفاتورة وإذن المخزن</button>
            </div>`;
        createModalShell('elsajana-grn-bascule-modal', '🚛 شاشة فحص واستلام البضاعة وميزان البسكول (معيار السجانة)', html, '780px');

        const calcBasc = () => {
            const exp = parseFloat(document.getElementById('basc-expected')?.value) || 0;
            const gross = parseFloat(document.getElementById('basc-gross')?.value) || 0;
            const tare = parseFloat(document.getElementById('basc-tare')?.value) || 0;
            const net = Math.round((gross - tare) * 1000) / 1000;
            const diffTons = Math.round((net - exp) * 1000) / 1000;
            const barsWeight = Math.round(net * ELSAJANA_STEEL_UNITS.WEIGHT_TON_BARS);
            const barsCount = Math.round(net * ELSAJANA_STEEL_UNITS.COUNT_TON_BARS);
            const sheilas = (barsWeight / ELSAJANA_STEEL_UNITS.SHEILA_BARS).toFixed(1);
            const diffBars = Math.round(diffTons * ELSAJANA_STEEL_UNITS.WEIGHT_TON_BARS);
            const box = document.getElementById('basc-result-box');
            if (box) box.textContent = `الصافي الفعلي: ${net} طن | فرق البسكول: ${diffTons >= 0 ? '+' : ''}${diffTons} طن (${diffBars} سيخة وزنية) | يعادل: ${barsWeight} سيخة وزنية (140) أو ${barsCount} سيخة عددية (135) = ${sheilas} شيلة (15)`;
        };
        ['basc-expected', 'basc-gross', 'basc-tare'].forEach(id => document.getElementById(id)?.addEventListener('input', calcBasc));
        calcBasc();

        document.getElementById('els-apply-grn-btn').onclick = () => {
            if (!activeRows.length) { document.getElementById('elsajana-grn-bascule-modal')?.remove(); return; }
            const grnList = readDualArray('erp_grn_v1');
            const trs = document.querySelectorAll('#els-grn-tbody tr[data-ridx]');
            const priInputs = document.querySelectorAll('.sl-qty-primary-input');
            const landedExtra = parseFloat(document.getElementById('basc-landed')?.value) || 0;
            const grnItems = [];
            trs.forEach(tr => {
                const idx = parseInt(tr.dataset.ridx, 10);
                const r = activeRows[idx];
                if (!r) return;
                const ok = parseFloat(tr.querySelector('.grn-ok')?.value) || 0;
                const dmg = parseFloat(tr.querySelector('.grn-dmg')?.value) || 0;
                const rej = parseFloat(tr.querySelector('.grn-rej')?.value) || 0;
                const diff = parseFloat(tr.querySelector('.grn-diff')?.value) || 0;
                if (isSl && priInputs[idx]) {
                    priInputs[idx].value = ok;
                    priInputs[idx].dispatchEvent(new Event('input', { bubbles: true }));
                } else if (isGrnPage) {
                    r.receiveQty = ok;
                    r.notes = `فحص واستلام: سليم(${ok}) تالف(${dmg}) مرفوض(${rej}) فرق ميزان(${diff})`;
                } else {
                    r.quantity = ok; r.qty = ok;
                }
                r.note = `فحص واستلام: سليم(${ok}) تالف(${dmg}) مرفوض(${rej}) فرق ميزان(${diff})`;
                const gName = r.itemName || r.name || (typeof getItemName === 'function' ? getItemName(r.itemId) : r.itemId);
                grnItems.push({ itemId: r.itemId, itemName: gName, acceptedQty: ok, damagedQty: dmg, rejectedQty: rej, scaleDiffQty: diff });
            });
            if (isSl) {
                const wn = document.getElementById('sl-warehouse-notes');
                if (wn) wn.value = grnItems.map(g => `${g.itemName}: سليم(${g.acceptedQty}) تالف(${g.damagedQty}) مرفوض(${g.rejectedQty}) فرق(${g.scaleDiffQty})`).join(' | ');
                window._slPendingLandedCost = landedExtra;
            } else if (isGrnPage) {
                window._slPendingLandedCost = landedExtra;
                if (typeof window.renderReceiptLines === 'function') window.renderReceiptLines();
                if (typeof window.updateSummary === 'function') window.updateSummary();
            }
            if (landedExtra > 0) checkLandedCostMarginErosion(activeRows, landedExtra, false);
            grnList.unshift({
                id: 'GRN-' + Date.now(),
                invoiceNo: (isSl && slCurrentDoc ? slCurrentDoc.invoiceNo : (isGrnPage && typeof currentInvoiceId !== 'undefined' ? currentInvoiceId : document.getElementById('hdr-inv-no')?.value)) || 'DRAFT',
                date: new Date().toISOString().slice(0, 10),
                landedCost: landedExtra,
                items: grnItems
            });
            writeDualStorage('erp_grn_v1', grnList.slice(0, 3000));
            if (typeof window.renderGrid === 'function') window.renderGrid();
            if (typeof window.recalcAll === 'function') window.recalcAll();
            document.getElementById('elsajana-grn-bascule-modal')?.remove();
            floatingToast('✅ تم اعتماد فرز البضاعة (سليم/تالف/مرفوض/فرق ميزان) وتحديث الكميات المستلمة فعلياً', 'success', 3800);
        };
    };

    // 3. 5-Stage Check Lifecycle Modal
    const STAGE_LABELS = {
        in_portfolio: '1️⃣ في الحافظة (حساب وسيط)',
        under_collection: '2️⃣ تحت التحصيل / المقاصة',
        cleared: '3️⃣ تحصيل / صرف فعلي بالبنك',
        bounced: '4️⃣ شيك مرتد (عكس القيد وإعادة المديونية)',
        endorsed: '5️⃣ تظهير الشيك لطرف ثالث'
    };

    const transitionCheckStage = (chkId, newStage, endorseTo = '') => {
        const checks = readDualArray('erp_checks_v1');
        const idx = checks.findIndex(c => c && String(c.id) === String(chkId));
        if (idx === -1) return;
        const chk = checks[idx];
        const prevStage = chk.status || 'in_portfolio';
        if (prevStage === newStage) return;

        const amt = (parseFloat(chk.amount) || 0) * ((chk.currency && chk.currency !== 'SDG') ? (parseFloat(chk.exchangeRate) || 1) : 1);
        const banks = readDualArray('erp_banks_v1');
        const suppliers = readDualArray('erp_suppliers_v1');

        if (newStage === 'cleared' && prevStage !== 'cleared') {
            const bIdx = banks.findIndex(b => String(b.id) === String(chk.bankAccountId));
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + (chk.type === 'receivable' ? amt : -amt);
                writeDualStorage('erp_banks_v1', banks);
            }
        } else if (newStage === 'bounced' && prevStage !== 'bounced') {
            if (prevStage === 'cleared') {
                const bIdx = banks.findIndex(b => String(b.id) === String(chk.bankAccountId));
                if (bIdx > -1) {
                    banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + (chk.type === 'receivable' ? -amt : amt);
                    writeDualStorage('erp_banks_v1', banks);
                }
            }
            const sIdx = suppliers.findIndex(s => String(s.id) === String(chk.beneficiaryId));
            if (sIdx > -1) {
                suppliers[sIdx].currentBalance = (parseFloat(suppliers[sIdx].currentBalance) || 0) + amt;
                writeDualStorage('erp_suppliers_v1', suppliers);
            }
        }
        chk.status = newStage;
        if (endorseTo) chk.endorsedTo = endorseTo;
        chk.updatedAt = new Date().toISOString();
        writeDualStorage('erp_checks_v1', checks);
        floatingToast(`✅ تم نقل الشيك رقم (${chk.checkNumber}) إلى مرحلة: ${STAGE_LABELS[newStage]}`, 'success', 3400);
    };

    const openChecksLifecycleModal = () => {
        const checks = readDualArray('erp_checks_v1').slice(0, 40);
        const rowsHtml = checks.length ? checks.map(c => {
            const st = STAGE_LABELS[c.status] ? c.status : 'in_portfolio';
            return `<tr style="border-bottom:1px solid rgba(148,163,184,0.25);">
                <td style="padding:6px;font-weight:900;">#${c.checkNumber || '---'}</td>
                <td style="padding:6px;font-weight:900;">${c.beneficiary || '---'}</td>
                <td style="padding:6px;font-weight:900;">${(parseFloat(c.amount)||0).toLocaleString()} ${c.currency||'SDG'}</td>
                <td style="padding:6px;">
                    <select onchange="window.ElsajanaAccountingBrain.transitionCheckStage('${c.id}', this.value)" style="padding:4px 6px;border-radius:7px;font-weight:900;">
                        ${Object.entries(STAGE_LABELS).map(([k, lbl]) => `<option value="${k}" ${st === k ? 'selected' : ''}>${lbl}</option>`).join('')}
                    </select>
                </td>
            </tr>`;
        }).join('') : `<tr><td colspan="4" style="padding:14px;text-align:center;font-weight:900;">لا توجد شيكات مسجلة في الحافظة حالياً</td></tr>`;

        createModalShell('elsajana-checks-modal', '💳 إدارة دورة الشيكات الخماسية (حافظة ➔ تحصيل ➔ بنك ➔ مرتد ➔ تظهير)', `
            <div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;font-size:0.86rem;">
                <thead><tr style="border-bottom:2px solid #64748b;"><th style="text-align:right;padding:6px;">رقم الشيك</th><th>المستفيد/الساحب</th><th>المبلغ</th><th>المرحلة المحاسبية (تسميع فوري)</th></tr></thead>
                <tbody>${rowsHtml}</tbody>
            </table></div>`, '760px');
    };

    // 4. 1-Click Document Cycle (Quote -> Invoice -> GRN -> Payment Voucher -> Auto-Reversal Return)
    const executeDocumentCycleAction = (mode) => {
        const slDoc = (typeof slCurrentDoc !== 'undefined' && slCurrentDoc) ? slCurrentDoc : null;
        const grnInv = (typeof currentInvoiceId !== 'undefined' && currentInvoiceId) ? currentInvoiceId : '';
        const invNo = document.getElementById('hdr-inv-no')?.value || window.currentViewedInvoiceNo || slDoc?.invoiceNo || grnInv || '';
        const custId = document.getElementById('hdr-customer')?.value || slDoc?.customer || slDoc?.supplierId || window._grnCurrentSupplierId || '';
        const custName = document.getElementById('hdr-customer-search')?.value || slDoc?.partyName || slDoc?.supplierName || document.getElementById('lbl-supplier')?.textContent || 'مورد عام';
        if (mode === 'proforma_to_invoice') {
            if (typeof window.setSaleType === 'function') window.setSaleType('cash');
            else if (invNo) { window.open(`purchase.php?action=direct&inv=${encodeURIComponent(invNo)}`, '_blank'); return; }
            floatingToast('✅ تم تحويل عرض السعر إلى فاتورة مشتريات فعلية بلمسة زر — اضغط حفظ للاعتماد', 'success', 3200);
        } else if (mode === 'to_grn') {
            openInspectionAndBasculeModal();
        } else if (mode === 'to_voucher') {
            const grnVal = parseFloat((document.getElementById('lbl-total-received-val')?.textContent || '0').replace(/[^\d.]/g, '')) || 0;
            const grand = parseFloat((document.getElementById('t-grand-local')?.textContent || '0').replace(/,/g, '')) || parseFloat(slDoc?.grandTotal || slDoc?.total || 0) || grnVal || 0;
            try {
                sessionStorage.setItem('magaies_prefill_voucher', JSON.stringify({ partnerId: custId, partnerName: custName, amount: grand, refInvoice: invNo }));
            } catch (_) {}
            window.open(`single-cash-payment.php?partner=${encodeURIComponent(custId)}&ref=${encodeURIComponent(invNo)}&amount=${encodeURIComponent(grand)}`, '_blank');
            floatingToast('🧾 تم توليد مسودة سند الصرف للمورد بلمسة زر', 'success', 2800);
        } else if (mode === 'auto_return') {
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && r.itemId && (parseFloat(r.quantity) || 0) > 0)
                : (Array.isArray(slDoc?.items) ? slDoc.items.filter(r => r && (r.itemId || r.id)) : ((typeof receiptLines !== 'undefined' && Array.isArray(receiptLines)) ? receiptLines.filter(r => r && r.itemId && (parseFloat(r.receiveQty) || 0) > 0) : []));
            if (!activeRows.length) { floatingToast('⚠️ افتح الفاتورة أو إذن الاستلام المراد إرجاعه أولاً', 'warning'); return; }
            const allItems = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : readDualArray('erp_items_v1');
            for (const r of activeRows) {
                const it = allItems.find(i => String(i.id) === String(r.itemId || r.id));
                const reqBase = (parseFloat(r.quantity || r.qtyOrdered || r.receiveQty) || 0) * (parseFloat(r.customFactor || r.factor || 1) || 1);
                const g = checkOutboundStockGuard(it, reqBase, false);
                if (!g.allowed) { floatingToast(g.message, 'error', 5200); return; }
            }
            if (window.currentViewedInvoiceNo && typeof window.revertOldInvoiceEffects === 'function') {
                window.revertOldInvoiceEffects(window.currentViewedInvoiceNo);
                const jEntries = readDualArray('erp_journal_entries_v1');
                jEntries.unshift({
                    id: 'JE-RET-' + Date.now(),
                    entryNo: 'JV-RET-' + window.currentViewedInvoiceNo,
                    refNo: window.currentViewedInvoiceNo,
                    sourceType: 'purchase_return',
                    date: new Date().toISOString().slice(0, 10),
                    description: `قيد عكسي آلي لمرتجع مشتريات فاتورة #${window.currentViewedInvoiceNo}`,
                    lines: [],
                    createdAt: new Date().toISOString()
                });
                writeDualStorage('erp_journal_entries_v1', jEntries);
                floatingToast(`↩️ تم تنفيذ مرتجع المشتريات وعكس القيد والمخزون والمديونية آلياً للفاتورة #${window.currentViewedInvoiceNo}`, 'success', 4200);
            } else {
                window.location.href = `purchase-return.php?ref=${encodeURIComponent(invNo)}`;
            }
        }
    };

    const openDocumentCycleModal = () => {
        createModalShell('elsajana-doc-cycle-modal', '🔄 الدورة المستندية الذكية بلمسة زر (دون إعادة كتابة البيانات)', `
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
                <button type="button" onclick="window.ElsajanaAccountingBrain.executeDocumentCycleAction('proforma_to_invoice');document.getElementById('elsajana-doc-cycle-modal')?.remove();" style="padding:12px;border-radius:10px;background:#0284c7;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;cursor:pointer;">📄 تحويل عرض سعر ➔ فاتورة فعلية</button>
                <button type="button" onclick="document.getElementById('elsajana-doc-cycle-modal')?.remove();window.ElsajanaAccountingBrain.openInspectionAndBasculeModal();" style="padding:12px;border-radius:10px;background:#059669;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;cursor:pointer;">🚛 فاتورة ➔ إذن فحص واستلام وميزان بسكول</button>
                <button type="button" onclick="window.ElsajanaAccountingBrain.executeDocumentCycleAction('to_voucher');document.getElementById('elsajana-doc-cycle-modal')?.remove();" style="padding:12px;border-radius:10px;background:#d97706;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;cursor:pointer;">💵 فاتورة ➔ سند صرف للمورد بلمسة زر</button>
                <button type="button" onclick="window.ElsajanaAccountingBrain.executeDocumentCycleAction('auto_return');document.getElementById('elsajana-doc-cycle-modal')?.remove();" style="padding:12px;border-radius:10px;background:#dc2626;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;cursor:pointer;">↩️ مرتجع مشتريات بقيد عكسي ذري آلي</button>
            </div>`, '640px');
    };

    // 5. Point 5: AI Result Summary & Predictive Q&A Modal
    const buildAiPurchaseInsights = (question = '') => {
        const purs = readDualArray('erp_purchase_invoices_v1').filter(x => x && !x.deleted);
        const items = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS) && ITEMS.length) ? ITEMS : readDualArray('erp_items_v1');
        const suppliers = readDualArray('erp_suppliers_v1');
        const customers = readDualArray('erp_customers_v1');

        const totalSpent = purs.reduce((s, p) => s + (parseFloat(p.localTotal || p.grandTotal || p.total || 0) || 0), 0);
        const totalLanded = purs.reduce((s, p) => s + (parseFloat(p.transportCost || 0) + parseFloat(p.laborCost || 0) + parseFloat(p.additionsCost || 0)), 0);
        const lowStock = items.filter(it => {
            const st = it.stock && typeof it.stock === 'object' ? Object.values(it.stock).reduce((a, b) => a + (parseFloat(b) || 0), 0) : (parseFloat(it.currentStock ?? it.quantity ?? 0) || 0);
            return st <= (parseFloat(it.minQty) || 5);
        }).slice(0, 4);
        const expectedPayers = customers.filter(c => (parseFloat(c.currentBalance) || 0) > 0).sort((a, b) => (parseFloat(b.currentBalance) || 0) - (parseFloat(a.currentBalance) || 0)).slice(0, 3);
        const topSup = suppliers.slice().sort((a, b) => (parseFloat(b.currentBalance) || 0) - (parseFloat(a.currentBalance) || 0))[0];
        const mbRes = Array.isArray(window.lastResults) && document.getElementById('mb-proportional-root') ? window.lastResults : null;
        const mbDr = mbRes ? mbRes.reduce((s, r) => s + (parseFloat(r.endingDebitLocal) || 0), 0) : 0;
        const mbCr = mbRes ? mbRes.reduce((s, r) => s + (parseFloat(r.endingCreditLocal) || 0), 0) : 0;
        const mbTopCust = mbRes ? mbRes.filter(r => (parseFloat(r.endingDebitLocal) || 0) > 0).sort((a, b) => (b.endingDebitLocal || 0) - (a.endingDebitLocal || 0)).slice(0, 3) : [];
        const mbTopSupp = mbRes ? mbRes.filter(r => (parseFloat(r.endingCreditLocal) || 0) > 0).sort((a, b) => (b.endingCreditLocal || 0) - (a.endingCreditLocal || 0))[0] : null;
        const dmrSt = (document.getElementById('dmr-proportional-root') && window._dmrSupremeStats) ? window._dmrSupremeStats : null;

        if (question) {
            const q = question.trim();
            if (/نفاد|ناقص|مخزن|رصيد صنف|سيخ/.test(q)) return lowStock.length ? `📦 الأصناف التي قاربت على النفاد حالياً: ${lowStock.map(i => i.name).join(' ، ')} — يُوصى بإصدار طلب شراء فوري.` : '✅ جميع الأصناف الرئيسية فوق حد الطلب الآمن حالياً.';
            if (/عميل|تحصيل|سداد|توقع/.test(q)) {
                if (mbTopCust.length) return `💰 العملاء الأعلى مديونية المتوقع تحصيل سدادهم هذا الأسبوع: ${mbTopCust.map(c => `${c.name} (${Math.round(c.endingDebitLocal).toLocaleString()} ج.س)`).join(' ، ')}.`;
                return expectedPayers.length ? `💰 العملاء الأعلى مديونية المتوقع تحصيل سدادهم هذا الأسبوع: ${expectedPayers.map(c => `${c.name} (${(parseFloat(c.currentBalance)||0).toLocaleString()} ج.س)`).join(' ، ')}.` : 'لا توجد ذمم عملاء معلقة مسجلة حالياً.';
            }
            if (/مورد|مديونية|له/.test(q)) {
                if (mbTopSupp) return `🏢 أعلى مورد/حساب دائن مستحق له رصيد: ${mbTopSupp.name} بقيمة ${Math.round(mbTopSupp.endingCreditLocal).toLocaleString()} ج.س.`;
                return topSup ? `🏢 أعلى مورد مستحق له رصيد: ${topSup.name} بقيمة ${(parseFloat(topSup.currentBalance)||0).toLocaleString()} ج.س.` : 'لا توجد مديونيات موردين مفتوحة.';
            }
            if (dmrSt) return `📊 صافي سيولة اليومية الكلية (${Math.round(dmrSt.grandTotalLiquidity || 0).toLocaleString()} ج.س): كاش (${Math.round(dmrSt.netCash || 0).toLocaleString()}) + بنك وبنكك (${Math.round(dmrSt.netBank || 0).toLocaleString()}) + شيكات (${Math.round(dmrSt.netCheques || 0).toLocaleString()}).`;
            if (mbRes) return `📊 صافي أرصدة (${mbRes.length}) حساب: إجمالي لنا (عليه) ${Math.round(mbDr).toLocaleString()} ج.س مقابل التزامات علينا (له) ${Math.round(mbCr).toLocaleString()} ج.س — الفارق الصافي ${Math.round(mbDr - mbCr).toLocaleString()} ج.س.`;
            return `📊 إجمالي فواتير المشتريات المسجلة (${purs.length}) فاتورة بإجمالي (${totalSpent.toLocaleString()} ج.س)، منها مصروفات نقل وعتالة مضافة (${totalLanded.toLocaleString()} ج.س).`;
        }

        if (dmrSt) {
            return {
                meaning: `صافي السيولة الشاملة (${Math.round(dmrSt.grandTotalLiquidity || 0).toLocaleString()} ج.س): صافي الكاش ${Math.round(dmrSt.netCash || 0).toLocaleString()} ج.س | صافي البنك وبنكك ${Math.round(dmrSt.netBank || 0).toLocaleString()} ج.س | الشيكات ${Math.round(dmrSt.netCheques || 0).toLocaleString()} ج.س.`,
                whyChanged: totalLanded > 0 ? `تأثرت المصروفات بترحيل وعتالة شحنات البضاعة (${totalLanded.toLocaleString()} ج.س) الموزعة آلياً على تكلفة الطن.` : `التغير محكوم بصافي المقبوضات النقدية والبنكية مقابل المدفوعات اليومية المرحلة لحظياً (0ms).`,
                lowStockText: lowStock.length ? lowStock.map(i => i.name).join(' ، ') : 'لا يوجد صنف حرج تحت حد الطلب حالياً',
                payerText: expectedPayers.length ? `متوقع سداد: ${expectedPayers.map(c => `${c.name} (${(parseFloat(c.currentBalance)||0).toLocaleString()} ج.س)`).join(' ، ')}` : 'السيولة اليومية متوازنة وآمنة'
            };
        }

        if (mbRes) {
            const netDiff = mbDr - mbCr;
            return {
                meaning: `يعرض التقرير (${mbRes.length} حساب): إجمالي مطالباتنا المدينة ${Math.round(mbDr).toLocaleString()} ج.س مقابل التزامات دائنة ${Math.round(mbCr).toLocaleString()} ج.س (الصافي: ${Math.abs(Math.round(netDiff)).toLocaleString()} ج.س ${netDiff >= 0 ? 'فائض لنا' : 'التزام علينا'}).`,
                whyChanged: totalLanded > 0 ? `تأثرت الأرصدة بحركة المبيعات الآجلة وفواتير الشراء متضمنة مصروفات عتالة ونقل بقيمة ${totalLanded.toLocaleString()} ج.س.` : `التغير ناتج مباشرة عن صافي الفواتير الآجلة وسندات القبض والصرف المرحلة لحظياً (0ms).`,
                lowStockText: lowStock.length ? lowStock.map(i => i.name).join(' ، ') : 'لا يوجد صنف حرج تحت حد الطلب حالياً',
                payerText: mbTopCust.length ? `متوقع سداد: ${mbTopCust.map(c => `${c.name} (${Math.round(c.endingDebitLocal).toLocaleString()} ج.س)`).join(' ، ')}` : (mbTopSupp ? `أولوية سداد للمورد: ${mbTopSupp.name}` : 'السيولة متوازنة هذا الأسبوع')
            };
        }

        return {
            meaning: `إجمالي المشتريات (${purs.length} فاتورة) بقيمة ${totalSpent.toLocaleString()} ج.س.`,
            whyChanged: totalLanded > 0 ? `بلغت مصروفات النقل والعتالة المضافة ${totalLanded.toLocaleString()} ج.س (${totalSpent > 0 ? ((totalLanded / totalSpent) * 100).toFixed(1) : 0}% من المشتريات) وهي المؤثر المباشر في تغير تكلفة الطن.` : 'التكلفة مستقرة لعدم وجود مصروفات نقل/عتالة استثنائية.',
            lowStockText: lowStock.length ? lowStock.map(i => i.name).join(' ، ') : 'لا يوجد صنف حرج حالياً',
            payerText: expectedPayers.length ? expectedPayers.map(c => `${c.name} (${(parseFloat(c.currentBalance)||0).toLocaleString()})`).join(' ، ') : (topSup ? `متابعة سداد المورد: ${topSup.name}` : 'السيولة متوازنة هذا الأسبوع')
        };
    };

    const openPurchaseAiAnalystModal = () => {
        const info = buildAiPurchaseInsights();
        const html = `
            <div style="display:grid;gap:9px;font-size:0.92rem;line-height:1.6;">
                <div style="padding:9px;border-radius:9px;background:rgba(16,185,129,0.12);border:1.5px solid #059669;"><strong>📌 ماذا تعني هذه النتيجة؟</strong> ${info.meaning}</div>
                <div style="padding:9px;border-radius:9px;background:rgba(245,158,11,0.12);border:1.5px solid #d97706;"><strong>📈 لماذا زادت أو تغيرت التكلفة والمصروفات؟</strong> ${info.whyChanged}</div>
                <div style="padding:9px;border-radius:9px;background:rgba(220,38,38,0.12);border:1.5px solid #dc2626;"><strong>🚨 أصناف قاربت على النفاد:</strong> ${info.lowStockText}</div>
                <div style="padding:9px;border-radius:9px;background:rgba(2,132,199,0.12);border:1.5px solid #0284c7;"><strong>🔮 التنبؤ الأسبوعي للسداد والسيولة:</strong> ${info.payerText}</div>
                <div style="display:flex;gap:6px;margin-top:6px;">
                    <input id="els-ai-q-inp" type="text" placeholder="اسأل التقرير مباشرة (مثال: ما الصنف الذي قارب على النفاد؟ من العميل المتوقع سداده؟)" style="flex:1;padding:8px 10px;border-radius:9px;border:2px solid #0284c7;font-weight:800;">
                    <button type="button" id="els-ai-q-btn" style="padding:8px 14px;border-radius:9px;background:#059669;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;cursor:pointer;">اسأل الذكاء 🧠</button>
                </div>
                <div id="els-ai-q-ans" style="font-weight:900;color:#059669;min-height:22px;"></div>
            </div>`;
        createModalShell('elsajana-ai-analyst-modal', '🧠 الملخص الذكي لقراءة النتائج والتنبؤ المستقبلي', html, '680px');
        const ask = () => {
            const q = document.getElementById('els-ai-q-inp')?.value || '';
            const ans = document.getElementById('els-ai-q-ans');
            if (ans) ans.textContent = buildAiPurchaseInsights(q || 'ملخص');
        };
        document.getElementById('els-ai-q-btn').onclick = ask;
        document.getElementById('els-ai-q-inp').onkeydown = (e) => { if (e.key === 'Enter') { e.preventDefault(); ask(); } };
    };
    window.runPurchaseAiExecutiveSummary = openPurchaseAiAnalystModal;

    const syncLogisticsReceiptAtomically = (grnRecord, origInv, grnItems) => {
        if (!grnRecord) return;
        const landedExtra = parseFloat(window._slPendingLandedCost || 0) || 0;
        window._slPendingLandedCost = 0;

        // 1. Dual-write tenant + global inventory, items, and purchase invoices
        ['erp_inventory_v1', 'erp_items_v1', 'erp_purchase_invoices_v1', 'erp_suppliers_v1'].forEach(k => {
            const v = localStorage.getItem(k);
            if (v) writeDualStorage(k, v);
        });

        // 2. Item Card & Weight Movements (140 bars/weight-ton, 135 count-ton, 15 sheila)
        const movs = readDualArray('erp_item_movements_v1');
        let totalVal = 0;
        (Array.isArray(grnItems) ? grnItems : []).forEach(g => {
            const baseBars = parseFloat(g.customBaseQty || ((parseFloat(g.quantity) || 0) * (parseFloat(g.factor) || 1))) || 0;
            const tons = Math.round((baseBars / ELSAJANA_STEEL_UNITS.WEIGHT_TON_BARS) * 1000) / 1000;
            const rowCost = (parseFloat(g.total) || ((parseFloat(g.quantity) || 0) * (parseFloat(g.price) || 0))) || 0;
            totalVal += rowCost;
            movs.unshift({
                id: `MOV-GRN-${grnRecord.id}-${g.itemId}`,
                docNo: grnRecord.id,
                refInvoice: grnRecord.originalInvoiceId,
                docType: 'grn',
                drillUrl: `purchase.php?action=direct&inv=${encodeURIComponent(grnRecord.originalInvoiceId || '')}`,
                date: String(grnRecord.date || '').slice(0, 10),
                itemId: g.itemId,
                itemName: g.name || g.itemName,
                warehouse: g.warehouseName || 'المخزن الرئيسي',
                unit: g.unit || 'وحدة',
                inQty: parseFloat(g.quantity) || 0,
                outQty: 0,
                baseInQty: baseBars,
                weightTons: tons,
                totalCost: rowCost,
                partnerName: grnRecord.supplierName || 'مورد',
                timestamp: Date.now()
            });
        });
        writeDualStorage('erp_item_movements_v1', movs.slice(0, 15000));

        // 3. Landed Cost Margin Erosion Guard + Journal Entry if Landed Cost applied
        if (landedExtra > 0) {
            checkLandedCostMarginErosion(grnItems, landedExtra, true);
            const jEntries = readDualArray('erp_journal_entries_v1');
            jEntries.unshift({
                id: `JE-GRN-LC-${grnRecord.id}`,
                entryNo: `JV-GRN-${grnRecord.id}`,
                refNo: grnRecord.originalInvoiceId,
                sourceType: 'grn_landed_cost',
                drillUrl: `purchase.php?action=direct&inv=${encodeURIComponent(grnRecord.originalInvoiceId || '')}`,
                date: new Date().toISOString().slice(0, 10),
                description: `تحميل مصروفات عتالة ونقل ومشال على إذن استلام #${grnRecord.id}`,
                totalDebit: landedExtra,
                totalCredit: landedExtra,
                balanced: true,
                lines: [
                    { accountCode: '1104', accountName: 'المخزون السلعي (تكلفة مضافة عتالة ونقل)', debit: landedExtra, credit: 0 },
                    { accountCode: '1101', accountName: 'الخزينة النقدية / مصروفات مستحقة', debit: 0, credit: landedExtra }
                ],
                createdAt: new Date().toISOString()
            });
            writeDualStorage('erp_journal_entries_v1', jEntries.slice(0, 20000));
        }

        // 4. CRDT Flash Sync / Offline Queue fallback
        const sendPayload = Object.assign({}, grnRecord, { _crdtDevice: getDeviceId(), _crdtHlc: Date.now(), landedCost: landedExtra });
        const enqueueGrnOffline = () => {
            const q = readDualArray('erp_sl_offline_queue_v1').filter(x => x && x.id !== grnRecord.id);
            q.push({ id: grnRecord.id, action: 'save_purchase_receipt', payload: sendPayload, ts: Date.now() });
            writeDualStorage('erp_sl_offline_queue_v1', q.slice(-200));
        };
        if (!navigator.onLine) {
            enqueueGrnOffline();
        } else {
            fetch('api/mirror_sync.php?action=save_purchase_receipt', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, keepalive: true, body: JSON.stringify(sendPayload)
            }).then(r => r.json()).then(res => { if (!res || !res.success) enqueueGrnOffline(); }).catch(enqueueGrnOffline);
        }

        // 5. 0ms BroadcastChannel Sync across Purchase, Inventory, Supplier Statement, Trial Balance
        try {
            const msg = { type: 'grn_saved_atomic', grnId: grnRecord.id, invoiceNo: grnRecord.originalInvoiceId, totalVal, ts: Date.now() };
            ['magaiees_sync_channel', 'erp_live_sync', 'erp_db_sync'].forEach(ch => {
                if (typeof BroadcastChannel !== 'undefined') { const bc = new BroadcastChannel(ch); bc.postMessage(msg); bc.close(); }
            });
        } catch (_) {}
    };

    // ── POINT 1B: 0ms Atomic Cross-System Sync for Single & Grouped Financial Vouchers ──
    const syncVoucherAtomically = (vData) => {
        if (!vData || (!vData.id && !vData.voucherNo)) return;
        const vNo = String(vData.id || vData.voucherNo);
        const amtLoc = parseFloat(vData.amountLocal || vData.totalAmount || vData.sdg || vData.amount || 0) || 0;
        const isGrp = !!(vData.isGrouped && Array.isArray(vData.items));
        const isRec = vData.type === 'receipt';
        const bts = [];
        const allChqs = [];
        if (isGrp) {
            vData.items.forEach(it => {
                (Array.isArray(it.bankTransfers) ? it.bankTransfers : []).forEach(b => bts.push({ reference: b.transferNo || b.reference || '', imgHash: b.imgHash || '', amount: parseFloat(b.amount) || 0 }));
                (Array.isArray(it.cheques) ? it.cheques : []).forEach(ch => allChqs.push(Object.assign({}, ch, { partyName: it.accountName || '', partyId: it.accountId || it.account || '' })));
            });
        } else {
            (Array.isArray(vData.bankTransfers) ? vData.bankTransfers : []).forEach(b => bts.push({ reference: b.transferNo || b.reference || '', imgHash: b.imgHash || '', amount: parseFloat(b.amount) || amtLoc }));
            if (vData.mobileTransferNo && !bts.length) bts.push({ reference: vData.mobileTransferNo, amount: amtLoc });
            (Array.isArray(vData.cheques) ? vData.cheques : []).forEach(ch => allChqs.push(Object.assign({}, ch, { partyName: vData.toAccountName || vData.to || '', partyId: vData.toAccount || '' })));
        }
        registerBankakReceipts(bts, vNo, vData.toAccountName || vData.to || vData.description || '', vData.date);
        if (Array.isArray(window.DB_ALL_TRANSFER_REFS)) {
            bts.forEach(b => { if (b.reference) window.DB_ALL_TRANSFER_REFS.unshift({ ref: b.reference, cleanRef: normalizeTrxRef(b.reference), voucherNo: vNo, amount: b.amount }); });
        }
        if (allChqs.length) {
            const chks = readDualArray('erp_checks_v1').filter(c => !(c && String(c.refInvoice) === vNo));
            allChqs.forEach((ch, idx) => {
                chks.unshift({ id: `CHK-V-${vNo}-${idx}`, checkNumber: ch.chequeNo || ch.checkNo || ch.checkNumber, beneficiary: ch.partyName || '', beneficiaryId: String(ch.partyId || '').replace(/^(supp|cust)-/, ''), bankName: ch.chequeBank || ch.checkBranch || '', bankAccountId: String(vData.mainAccount || vData.fromAccount || '').replace(/^bank-/, ''), amount: parseFloat(ch.amount) || 0, currency: vData.currency || 'SDG', dueDate: ch.chequeDate || ch.checkDate || vData.date, status: 'in_portfolio', type: isRec ? 'receivable' : 'payable', refInvoice: vNo, createdAt: new Date().toISOString() });
            });
            writeDualStorage('erp_checks_v1', chks.slice(0, 5000));
        }
        ['erp_safes_v1', 'erp_banks_v1', 'erp_customers_v1', 'erp_suppliers_v1', 'erp_accounts_v1', 'erp_single_payments_v1', 'erp_payments_v1', 'erp_receipts_v1'].forEach(k => { const v = localStorage.getItem(k); if (v) writeDualStorage(k, v); });
        const srcType = isGrp ? 'grouped_voucher' : 'single_voucher';
        const drill = isGrp ? `multi-cash-payment.php?voucher=${encodeURIComponent(vNo)}&type=${encodeURIComponent(vData.type || 'payment')}` : `single-cash-payment.php?voucher=${encodeURIComponent(vNo)}`;
        const jLines = isGrp
            ? (isRec
                ? [{ accountCode: vData.mainAccount || '1101', accountName: 'الخزينة/البنك الرئيسي', debit: amtLoc, credit: 0 }, ...vData.items.map(it => ({ accountCode: it.accountId || '1103', accountName: it.accountName || 'حساب فرعي', debit: 0, credit: parseFloat(it.amount) || 0 }))]
                : [...vData.items.map(it => ({ accountCode: it.accountId || '2101', accountName: it.accountName || 'حساب فرعي', debit: parseFloat(it.amount) || 0, credit: 0 })), { accountCode: vData.mainAccount || '1101', accountName: 'الخزينة/البنك الرئيسي', debit: 0, credit: amtLoc }])
            : [{ accountCode: vData.toAccount || '2101', accountName: vData.toAccountName || vData.to || 'المستفيد', debit: amtLoc, credit: 0 }, { accountCode: vData.fromAccount || '1101', accountName: vData.fromAccountName || vData.from || 'الخزينة/البنك', debit: 0, credit: amtLoc }];
        const jEntries = readDualArray('erp_journal_entries_v1').filter(j => !(j && String(j.refNo) === vNo && j.sourceType === srcType));
        jEntries.unshift({ id: `JE-VCH-${vNo}`, entryNo: `JV-VCH-${vNo}`, refNo: vNo, sourceId: vNo, sourceType: srcType, drillUrl: drill, date: vData.date || new Date().toISOString().slice(0, 10), description: vData.description || vData.toStatement || `سند مالي رقم #${vNo}`, totalDebit: amtLoc, totalCredit: amtLoc, balanced: true, lines: jLines, createdAt: new Date().toISOString() });
        writeDualStorage('erp_journal_entries_v1', jEntries.slice(0, 20000));
        try {
            const msg = { type: isGrp ? 'grouped_voucher_saved_atomic' : 'voucher_saved_atomic', voucherNo: vNo, amountLocal: amtLoc, ts: Date.now() };
            ['magaiees_sync_channel', 'erp_live_sync', 'erp_db_sync'].forEach(ch => { if (typeof BroadcastChannel !== 'undefined') { const bc = new BroadcastChannel(ch); bc.postMessage(msg); bc.close(); } });
        } catch (_) {}
    };

    const resolveSourceDrillUrl = (item) => {
        if (!item) return 'posting-accounts.php';
        const ref = encodeURIComponent(item.id || item.invoiceNo || item.voucherNo || '');
        const mk = String(item._metaKey || '');
        if (mk.includes('purchase')) return `purchase.php?action=direct&inv=${ref}`;
        if (mk.includes('sales')) return `sales.php?inv=${ref}`;
        if (mk.includes('single_payment')) return `single-cash-payment.php?voucher=${ref}`;
        if (mk === 'erp_payments_v1') return `multi-cash-payment.php?voucher=${ref}&type=payment`;
        if (mk === 'erp_receipts_v1') return `multi-cash-payment.php?voucher=${ref}&type=receipt`;
        if (mk.includes('journal')) return `journal.php?entry=${ref}`;
        return `vouchers-hub.php?ref=${ref}`;
    };

    const syncPostedTransactionAtomically = (item, isUnpost = false) => {
        if (!item) return;
        const ref = String(item.id || item.invoiceNo || item.voucherNo || 'DOC');
        const amt = parseFloat(item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.grandTotal || 0) || 0;
        const mk = String(item._metaKey || '');
        const sign = isUnpost ? -1 : 1;
        const drillUrl = resolveSourceDrillUrl(item);
        if (!isUnpost && mk.includes('purchase')) {
            atomicCrossSystemSync(Object.assign({}, item, { invoiceNo: ref, localTotal: amt, grandTotal: amt }));
        } else if (!isUnpost && (mk.includes('payment') || mk.includes('receipt'))) {
            syncVoucherAtomically(Object.assign({}, item, { voucherNo: ref, amountLocal: amt, type: mk.includes('receipt') ? 'receipt' : 'payment', isGrouped: mk === 'erp_payments_v1' || mk === 'erp_receipts_v1' }));
        } else {
            const jEntries = readDualArray('erp_journal_entries_v1').filter(j => !(j && String(j.refNo) === ref && j.sourceType === 'posting_accounts'));
            if (!isUnpost) {
                jEntries.unshift({ id: `JE-PA-${ref}`, entryNo: `JV-PA-${ref}`, refNo: ref, sourceId: ref, sourceType: 'posting_accounts', drillUrl, date: item._dateStr || item.date || new Date().toISOString().slice(0, 10), description: item.description || `ترحيل مستند #${ref}`, totalDebit: amt, totalCredit: amt, balanced: true, lines: [{ accountCode: '1103', accountName: item.customerName || item.supplierName || 'طرف مدين', debit: amt, credit: 0 }, { accountCode: '4101', accountName: 'المبيعات / الحساب المقابل', debit: 0, credit: amt }], createdAt: new Date().toISOString() });
            } else {
                jEntries.unshift({ id: `JE-UNPOST-${ref}-${Date.now()}`, entryNo: `JV-REV-${ref}`, refNo: ref, sourceId: ref, sourceType: 'posting_reversal', drillUrl, date: new Date().toISOString().slice(0, 10), description: `قيد عكسي لإلغاء ترحيل مستند #${ref}`, totalDebit: amt, totalCredit: amt, balanced: true, lines: [{ accountCode: '4101', accountName: 'عكس الترحيل', debit: amt, credit: 0 }, { accountCode: '1103', accountName: item.customerName || item.supplierName || 'عكس الطرف', debit: 0, credit: amt }], createdAt: new Date().toISOString() });
            }
            writeDualStorage('erp_journal_entries_v1', jEntries.slice(0, 20000));
        }
        const tb = (() => { try { return JSON.parse(localStorage.getItem('erp_trial_balance_snapshot_v1') || '{}'); } catch (_) { return {}; } })();
        tb.lastPostedRef = ref; tb.totalPostedVolume = Math.max(0, (parseFloat(tb.totalPostedVolume) || 0) + sign * amt); tb.balanced = true; tb.updatedAt = new Date().toISOString();
        writeDualStorage('erp_trial_balance_snapshot_v1', tb);
        const pnl = (() => { try { return JSON.parse(localStorage.getItem('erp_pnl_balance_sheet_snapshot_v1') || '{}'); } catch (_) { return {}; } })();
        if (mk.includes('sales')) pnl.totalSales = Math.max(0, (parseFloat(pnl.totalSales) || 0) + sign * amt);
        if (mk.includes('purchase')) pnl.totalPurchases = Math.max(0, (parseFloat(pnl.totalPurchases) || 0) + sign * amt);
        pnl.netIndicator = (parseFloat(pnl.totalSales) || 0) - (parseFloat(pnl.totalPurchases) || 0); pnl.updatedAt = new Date().toISOString();
        writeDualStorage('erp_pnl_balance_sheet_snapshot_v1', pnl);
        triggerFlashSyncSpark();
        try {
            const msg = { type: isUnpost ? 'pa_unposted_atomic' : 'pa_posted_atomic', ref, amount: amt, drillUrl, ts: Date.now() };
            ['magaiees_sync_channel', 'erp_live_sync', 'erp_db_sync'].forEach(ch => { if (typeof BroadcastChannel !== 'undefined') { const bc = new BroadcastChannel(ch); bc.postMessage(msg); bc.close(); } });
        } catch (_) {}
    };

    // ── Drill-Down Handler + Compact Non-Intrusive Quick Action Pills ──
    document.addEventListener('DOMContentLoaded', () => {
        // 1. URL 1-Touch Drill-Down (?inv=PUR-xxx or ?invoiceNo= or ?drill=)
        try {
            const sp = new URLSearchParams(window.location.search);
            const targetInv = sp.get('inv') || sp.get('invoiceNo') || sp.get('drill') || sp.get('doc');
            if (targetInv) {
                setTimeout(() => {
                    if (typeof window.loadSlDocument === 'function') window.loadSlDocument(targetInv);
                    else if (typeof window.editHubTransaction === 'function') window.editHubTransaction(targetInv);
                    else if (typeof window.loadInvoiceFromStorage === 'function') window.loadInvoiceFromStorage(targetInv);
                }, 180);
            }
        } catch (_) {}

        // 1B. Smart-Logistics 1-Touch Drill-Down on #sl-lbl-inv-no & Live Cross-Tab Broadcast Listener
        const slInvLbl = document.getElementById('sl-lbl-inv-no');
        if (slInvLbl) {
            slInvLbl.style.cursor = 'pointer';
            slInvLbl.title = 'اضغط لفتح أصل الفاتورة بلمسة واحدة (Drill-Down)';
            slInvLbl.addEventListener('click', () => {
                const no = (slInvLbl.textContent || '').trim();
                if (!no || no === '---') return;
                const isSales = document.body.getAttribute('data-logistics-mode') === 'sales';
                window.location.href = isSales ? `direct-sales.php?inv=${encodeURIComponent(no)}` : `purchase.php?action=direct&inv=${encodeURIComponent(no)}`;
            });
            if (typeof BroadcastChannel !== 'undefined') {
                const slBc = new BroadcastChannel('magaiees_sync_channel');
                slBc.onmessage = () => { if (typeof window.refreshAllSlData === 'function') window.refreshAllSlData(); };
            }
        }

        // 1B. Purchase-Receipt.php Drill-Down (?inv= auto-open + 1-touch original invoice click + 0ms BroadcastChannel)
        const grnInvLbl = document.getElementById('lbl-inv-no');
        if (grnInvLbl) {
            const urlInv = new URLSearchParams(window.location.search).get('inv');
            if (urlInv) {
                setTimeout(() => { if (typeof window.openReceiptEditor === 'function') window.openReceiptEditor(urlInv); }, 280);
            }
            grnInvLbl.style.cursor = 'pointer';
            grnInvLbl.title = 'انقر لفتح الفاتورة الأصلية بلمسة واحدة (Drill-Down)';
            grnInvLbl.addEventListener('click', () => {
                const no = (grnInvLbl.textContent || '').trim();
                if (no && no !== '-') window.location.href = `purchase.php?action=direct&inv=${encodeURIComponent(no)}`;
            });
            if (typeof BroadcastChannel !== 'undefined') {
                const grnBc = new BroadcastChannel('magaiees_sync_channel');
                grnBc.onmessage = () => {
                    if (typeof window.calculatePendingGoods === 'function') window.calculatePendingGoods();
                    if (typeof window.renderPendingInvoices === 'function') window.renderPendingInvoices(false);
                };
            }
        }

        // 2. 1-Touch Row Drill-Down inside Hub Table
        const hubBody = document.getElementById('hub-transactions-body');
        if (hubBody) {
            hubBody.addEventListener('click', (e) => {
                if (e.target.closest('button, a, input, select')) return;
                const tr = e.target.closest('tr');
                if (!tr) return;
                const invCell = tr.querySelector('.hub-col-invno') || tr.cells[0];
                const invNo = (invCell ? invCell.textContent : '').replace(/[#\s]/g, '').trim();
                if (invNo && typeof window.editHubTransaction === 'function') window.editHubTransaction(invNo);
            });
        }

        // 3. Add Weight/Tons option to Landed Cost distribution select if present
        const lcSel = document.getElementById('hdr-landed-cost-mode');
        if (lcSel && !lcSel.querySelector('option[value="weight"]')) {
            const opt = document.createElement('option');
            opt.value = 'weight';
            opt.textContent = 'توزيع بالوزن / الأطنان (معيار السجانة)';
            lcSel.appendChild(opt);
        }

        // 4. Inject ultra-compact operational buttons into Purchase, Smart-Logistics, or Purchase-Receipt toolbar (zero change to screen colors/layout)
        const slToolbar = document.querySelector('#sl-items-grid-card > div:first-child > div:last-child');
        const grnHubBar = document.getElementById('grn-hub-export-print-bar');
        const grnEdBar = document.querySelector('#view-editor .neo-card-title > div:last-child');
        const buildOpsHtml = (withAi) => `
            <button type="button" onclick="window.ElsajanaAccountingBrain.openInspectionAndBasculeModal()" title="فحص واستلام وميزان البسكول والعتالة (135/140/15)" style="height:30px;padding:0 9px;border-radius:8px;background:#059669;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #6ee7b7;font-size:0.78rem;font-weight:900;cursor:pointer;">⚖️ فحص وبسكول</button>
            <button type="button" onclick="window.ElsajanaAccountingBrain.openBankakFawrySmartModal()" title="قراءة وفحص إشعار بنكك/فوري بالذكاء وضغط 40KB" style="height:30px;padding:0 9px;border-radius:8px;background:#0284c7;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #7dd3fc;font-size:0.78rem;font-weight:900;cursor:pointer;">🏦 بنكك/فوري AI</button>
            <button type="button" onclick="window.ElsajanaAccountingBrain.openChecksLifecycleModal()" title="دورة الشيكات الخماسية" style="height:30px;padding:0 9px;border-radius:8px;background:#7c3aed;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #c4b5fd;font-size:0.78rem;font-weight:900;cursor:pointer;">💳 دورة الشيكات</button>
            <button type="button" onclick="window.ElsajanaAccountingBrain.openDocumentCycleModal()" title="الدورة المستندية بلمسة زر" style="height:30px;padding:0 9px;border-radius:8px;background:#d97706;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #fde047;font-size:0.78rem;font-weight:900;cursor:pointer;">🔄 الدورة المستندية</button>
            ${withAi ? `<button type="button" onclick="window.ElsajanaAccountingBrain.openPurchaseAiAnalystModal()" title="ملخص الذكاء الاصطناعي والتنبؤ المستقبلي واسأل التقرير" style="height:30px;padding:0 9px;border-radius:8px;background:#0f766e;color:#fff!important;-webkit-text-fill-color:#fff!important;border:1.5px solid #5eead4;font-size:0.78rem;font-weight:900;cursor:pointer;">🧠 اسأل التقرير AI</button>` : ''}`;
        const togglesArea = document.getElementById('toggles-area') || document.querySelector('.items-toolbar') || slToolbar || grnEdBar;
        if (togglesArea && !document.getElementById('elsajana-ops-bar')) {
            const span = document.createElement('span');
            span.id = 'elsajana-ops-bar';
            span.style.cssText = 'display:inline-flex;align-items:center;gap:4px;flex-wrap:wrap;';
            span.innerHTML = buildOpsHtml(!!(slToolbar || grnEdBar));
            togglesArea.appendChild(span);
        }
        if (grnHubBar && !document.getElementById('elsajana-grn-hub-ops')) {
            const hSpan = document.createElement('span');
            hSpan.id = 'elsajana-grn-hub-ops';
            hSpan.style.cssText = 'display:inline-flex;align-items:center;gap:4px;flex-wrap:wrap;';
            hSpan.innerHTML = buildOpsHtml(true);
            grnHubBar.appendChild(hSpan);
        }

        // 5. Real-time Bankak duplicate check when typing/pasting in .bt-ref-input, .transfer-no, .block-transfer-no-input, or #sl-gate-pass-no
        document.addEventListener('change', async (e) => {
            const t = e.target;
            if (!t) return;
            if (t.matches('.bt-ref-input, .bt-no-input, #sl-gate-pass-no, .transfer-no, #mobile-transfer-no, .block-transfer-no-input')) {
                const curV = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || '').trim();
                const dup = checkBankakDuplicate(t.value, t.dataset.imgHash || '', window.currentViewedInvoiceNo || curV || '');
                let extDup = false;
                if (!dup.duplicate && typeof window.checkTransferDuplicate === 'function') {
                    const r = await window.checkTransferDuplicate(t.value, curV);
                    extDup = (typeof r === 'boolean') ? !r : !!(r && r.duplicate);
                }
                if (dup.duplicate || extDup) shakeAndFocus(t, BANKAK_DUP_MSG, 'error', 5500);
            } else if (t.matches('.ch-no-input, .cheque-no, .block-cheque-no-input')) {
                const curV = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || '').trim();
                const dup = checkDuplicateChequeNo(t.value, window.currentViewedInvoiceNo || curV || '');
                if (dup.duplicate) shakeAndFocus(t, `🚫 رقم الشيك (${t.value}) مسجل مسبقاً في حافظة الشيكات!`, 'error', 4500);
            }
        });

        // 6B. Multi-Cash-Payment 1-Touch Drill-Down (?voucher= / ?vno=) + Prefill + Compact ERP Brain Ops Bar + Live Broadcast Sync
        if (document.getElementById('mcp-proportional-root')) {
            const sp = new URLSearchParams(window.location.search);
            const drillV = sp.get('voucher') || sp.get('vno') || sp.get('drill_voucher') || sp.get('inv') || sp.get('drill');
            if (drillV) {
                setTimeout(() => {
                    const vType = sp.get('type') || document.getElementById('voucher-type')?.value || 'payment';
                    const key = vType === 'receipt' ? 'erp_receipts_v1' : 'erp_payments_v1';
                    const arr = typeof window.getStoredVouchers === 'function' ? window.getStoredVouchers(key) : readDualArray(key);
                    const hit = (arr || []).find(v => v && (String(v.voucherNo) === String(drillV) || String(v.id) === String(drillV)));
                    if (hit && typeof window.loadVoucherIntoForm === 'function') window.loadVoucherIntoForm(hit);
                }, 340);
            }
            const injectMcpOps = () => {
                const bar = document.getElementById('mcpFc1AiTopCommandBar') || document.getElementById('payment-methods-row');
                if (bar && !document.getElementById('elsajana-mcp-ops-bar')) {
                    const spn = document.createElement('span');
                    spn.id = 'elsajana-mcp-ops-bar';
                    spn.style.cssText = 'display:inline-flex;align-items:center;gap:4px;flex-wrap:wrap;margin-right:auto;';
                    spn.innerHTML = buildOpsHtml(true);
                    bar.appendChild(spn);
                }
            };
            setTimeout(injectMcpOps, 250);
            if (typeof BroadcastChannel !== 'undefined') {
                const mcpBc = new BroadcastChannel('magaiees_sync_channel');
                mcpBc.onmessage = () => { if (typeof window.updateAllLiveBalances === 'function') window.updateAllLiveBalances(); };
            }
        }

        // 6. Single-Cash-Payment 1-Touch Drill-Down (?voucher= / ?vno=) + Prefill (?partner=&amount=&ref=) + Live Broadcast Sync
        if (document.getElementById('scp-proportional-root')) {
            const sp = new URLSearchParams(window.location.search);
            const drillV = sp.get('voucher') || sp.get('vno') || sp.get('drill_voucher') || sp.get('inv') || sp.get('drill');
            if (drillV) {
                setTimeout(() => {
                    const list = typeof window.getAllSinglePayments === 'function' ? window.getAllSinglePayments() : readDualArray('erp_single_payments_v1');
                    const idx = list.findIndex(v => v && (String(v.id) === String(drillV) || String(v.voucherNo) === String(drillV)));
                    if (idx > -1 && typeof window.loadVoucherByIndex === 'function') window.loadVoucherByIndex(idx);
                }, 320);
            } else if (sp.get('amount') || sessionStorage.getItem('magaies_prefill_voucher')) {
                setTimeout(() => {
                    let pf = {};
                    try { pf = JSON.parse(sessionStorage.getItem('magaies_prefill_voucher') || '{}'); sessionStorage.removeItem('magaies_prefill_voucher'); } catch (_) {}
                    const amt = sp.get('amount') || pf.amount || 0;
                    const ref = sp.get('ref') || pf.refInvoice || '';
                    const pName = pf.partnerName || '';
                    if (amt > 0 && document.getElementById('amount-local')) { document.getElementById('amount-local').value = amt; document.getElementById('amount-local').dispatchEvent(new Event('input', { bubbles: true })); }
                    if (pName && document.getElementById('to-account-search')) document.getElementById('to-account-search').value = pName;
                    if (ref && document.getElementById('to-statement')) document.getElementById('to-statement').value = `دفعة مسددة مقابل مستند رقم #${ref}`;
                }, 350);
            }
            if (typeof BroadcastChannel !== 'undefined') {
                const scpBc = new BroadcastChannel('magaiees_sync_channel');
                scpBc.onmessage = () => { if (typeof window.updateHeaderBalances === 'function') window.updateHeaderBalances(); };
            }
        }

        // 6C. Posting-Accounts 1-Touch Drill-Down (?ref= / ?inv= / ?voucher= / ?drill=) + Compact Brain Bar + Live Broadcast Sync
        if (document.getElementById('pa-proportional-root')) {
            const sp = new URLSearchParams(window.location.search);
            const drillRef = sp.get('ref') || sp.get('inv') || sp.get('voucher') || sp.get('drill');
            if (drillRef) {
                setTimeout(() => {
                    const sInp = document.getElementById('search-input');
                    if (sInp) { sInp.value = drillRef; if (typeof window.handleFilterChange === 'function') window.handleFilterChange(); }
                    setTimeout(() => { if (Array.isArray(window.filteredData) && window.filteredData.length > 0 && typeof window.selectRow === 'function') window.selectRow(0); }, 180);
                }, 260);
            }
            const fltTop = document.querySelector('#pa-proportional-root .filters-top');
            if (fltTop && !document.getElementById('elsajana-pa-ops-bar')) {
                const spn = document.createElement('span');
                spn.id = 'elsajana-pa-ops-bar';
                spn.style.cssText = 'display:inline-flex;align-items:center;gap:4px;flex-wrap:wrap;';
                spn.innerHTML = buildOpsHtml(true);
                fltTop.appendChild(spn);
            }
            if (typeof BroadcastChannel !== 'undefined') {
                const paBc = new BroadcastChannel('magaiees_sync_channel');
                paBc.onmessage = (ev) => { if (ev && ev.data && ev.data.type !== 'pa_posted_atomic' && typeof window.loadAllTransactions === 'function') window.loadAllTransactions(); };
            }
        }

        // 6D. Main-Balances-Report 0ms Atomic Cross-System Sync + 1-Touch Source Drill-Down + Live AI Executive Summary
        if (document.getElementById('mb-proportional-root')) {
            const sp = new URLSearchParams(window.location.search);
            const qGrp = sp.get('group'); const qCur = sp.get('currency'); const qCond = sp.get('condition');
            if (qGrp && document.getElementById('filter-group')) document.getElementById('filter-group').value = qGrp;
            if (qCur && document.getElementById('filter-currency')) document.getElementById('filter-currency').value = qCur;
            if (qCond && document.getElementById('filter-condition')) document.getElementById('filter-condition').value = qCond;
            const presetsRibbon = document.getElementById('main-presets-container');
            if (presetsRibbon && !document.getElementById('elsajana-mb-ops-bar')) {
                const spn = document.createElement('span');
                spn.id = 'elsajana-mb-ops-bar';
                spn.style.cssText = 'display:inline-flex;align-items:center;gap:4px;flex-wrap:nowrap;margin-right:4px;';
                spn.innerHTML = buildOpsHtml(true);
                presetsRibbon.appendChild(spn);
            }
            window.updateMainBalancesAiSummary = (results, sumDr, sumCr) => {
                const dash = document.getElementById('analytics-dashboard');
                if (!dash || !Array.isArray(results)) return;
                let bar = document.getElementById('mb-ai-live-summary-strip');
                if (!bar) {
                    bar = document.createElement('div');
                    bar.id = 'mb-ai-live-summary-strip';
                    bar.className = 'no-print';
                    bar.style.cssText = 'margin-top:6px;padding:6px 10px;border-radius:8px;border:1.5px solid rgba(16,185,129,0.45);background:rgba(16,185,129,0.09);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;font-size:0.82rem;font-weight:800;';
                    dash.appendChild(bar);
                }
                const info = buildAiPurchaseInsights();
                bar.innerHTML = `<span>🧠 <strong>ملخص الذكاء المالي الفوري:</strong> ${info.meaning} | 🎯 ${info.payerText} | 📦 النفاد: ${info.lowStockText}</span><button type="button" onclick="window.ElsajanaAccountingBrain.openPurchaseAiAnalystModal()" style="padding:3px 10px;border-radius:7px;background:#0f766e;color:#fff!important;-webkit-text-fill-color:#fff!important;border:none;font-weight:900;font-size:0.76rem;cursor:pointer;">اسأل التقرير AI 💬</button>`;
                writeDualStorage('erp_mainbalances_snapshot_v1', { accountCount: results.length, totalReceivablesSDG: sumDr || 0, totalPayablesSDG: sumCr || 0, netSDG: (sumDr || 0) - (sumCr || 0), updatedAt: new Date().toISOString() });
            };
            window.openPartnerLastSourceDoc = () => {
                const pMenu = document.getElementById('partnerContextMenu'); if (pMenu) pMenu.style.display = 'none';
                const pId = String(window.currentSelectedPartnerId || '').replace(/^(cust|supp|bank|safe)-/, '');
                const pName = String(window.currentSelectedPartnerName || '').trim();
                const matchP = (o) => o && ((pId && [o.supplierId, o.customerId, o.partnerId, o.toAccount, o.accountId].some(x => String(x || '').replace(/^(cust|supp)-/, '') === pId)) || (pName && [o.supplierName, o.customerName, o.partnerName, o.toAccountName, o.to].some(x => String(x || '').trim() === pName)));
                const pur = readDualArray('erp_purchase_invoices_v1').find(matchP);
                if (pur) { window.location.href = `purchase.php?action=direct&inv=${encodeURIComponent(pur.invoiceNo || pur.id)}`; return; }
                const sal = readDualArray('erp_sales_invoices_v1').find(matchP);
                if (sal) { window.location.href = `sales.php?inv=${encodeURIComponent(sal.invoiceNo || sal.id)}`; return; }
                const spv = readDualArray('erp_single_payments_v1').find(matchP);
                if (spv) { window.location.href = `single-cash-payment.php?voucher=${encodeURIComponent(spv.voucherNo || spv.id)}`; return; }
                if (typeof window.goToPartnerLedger === 'function') window.goToPartnerLedger();
            };
            window.issuePartnerQuickVoucher = () => {
                const pMenu = document.getElementById('partnerContextMenu'); if (pMenu) pMenu.style.display = 'none';
                const pName = String(window.currentSelectedPartnerName || '').trim();
                const hit = (window.lastResults || []).find(r => r && (String(r.id) === String(window.currentSelectedPartnerId) || r.name === pName));
                const amt = hit ? Math.max(parseFloat(hit.endingCreditLocal) || 0, parseFloat(hit.endingDebitLocal) || 0) : 0;
                try { sessionStorage.setItem('magaies_prefill_voucher', JSON.stringify({ partnerName: pName, partnerId: window.currentSelectedPartnerId || '', amount: amt, refInvoice: 'تسوية رصيد حساب' })); } catch (_) {}
                window.location.href = `single-cash-payment.php?amount=${encodeURIComponent(amt)}&ref=${encodeURIComponent(pName)}`;
            };
            const liveReloadMb = () => { if (typeof window.loadData === 'function' && typeof window.runReport === 'function') { window.loadData(); window.runReport(true); } };
            ['magaiees_sync_channel', 'erp_live_sync', 'erp_db_sync'].forEach(ch => {
                if (typeof BroadcastChannel !== 'undefined') { const bc = new BroadcastChannel(ch); bc.onmessage = liveReloadMb; }
            });
            window.addEventListener('storage', (ev) => { if (ev && ev.key && /erp_.*_v1/.test(ev.key)) liveReloadMb(); });
        }

        // 6E. Daily-Master-Report 0ms Cross-System Sync + 1-Touch Source Drill-Down on All 4 Tables + Sovereign Brain Ops Bar
        if (document.getElementById('dmr-proportional-root')) {
            const topNav = document.getElementById('dmr-top-nav-bar');
            if (topNav && !document.getElementById('elsajana-dmr-ops-bar')) {
                const spn = document.createElement('span');
                spn.id = 'elsajana-dmr-ops-bar';
                spn.style.cssText = 'display:inline-flex;align-items:center;gap:4px;flex-wrap:wrap;';
                spn.innerHTML = buildOpsHtml(true);
                topNav.appendChild(spn);
            }
            document.getElementById('dmr-proportional-root').addEventListener('click', (ev) => {
                if (ev.target && ev.target.closest('button, a, input, select')) return;
                const tr = ev.target && ev.target.closest('#financial-vouchers-tbody tr, #invoices-tbody tr, #checks-tbody tr, #stock-tbody tr');
                if (!tr || tr.cells.length < 2) return;
                const tbId = tr.parentElement ? tr.parentElement.id : '';
                const c0 = (tr.cells[0]?.textContent || '').trim();
                const c1 = (tr.cells[1]?.textContent || '').replace(/^#/, '').split(/\s+/)[0].trim();
                const c4 = (tr.cells[4]?.textContent || '').trim();
                if (!c1 && !c4) return;
                if (tbId === 'financial-vouchers-tbody') {
                    if (/مجمع/.test(c0)) window.location.href = `multi-cash-payment.php?voucher=${encodeURIComponent(c1)}&type=${/قبض/.test(c0) ? 'receipt' : 'payment'}`;
                    else if (/قيد/.test(c0)) window.location.href = `posting-accounts.php?ref=${encodeURIComponent(c1)}`;
                    else window.location.href = `single-cash-payment.php?voucher=${encodeURIComponent(c1)}`;
                } else if (tbId === 'invoices-tbody') {
                    if (/مشتريات/.test(c0)) window.location.href = `purchase.php?action=direct&inv=${encodeURIComponent(c1)}`;
                    else window.location.href = `sales.php?inv=${encodeURIComponent(c1)}`;
                } else if (tbId === 'checks-tbody') {
                    openChecksLifecycleModal();
                } else if (tbId === 'stock-tbody') {
                    const m = c4.match(/#([\w\-]+)/);
                    const ref = m ? m[1] : '';
                    if (/مشتريات/.test(c4)) window.location.href = `purchase.php?action=direct&inv=${encodeURIComponent(ref)}`;
                    else if (/مبيعات/.test(c4)) window.location.href = `sales.php?inv=${encodeURIComponent(ref)}`;
                    else if (ref) window.location.href = `smart-logistics.php?mode=purchases&inv=${encodeURIComponent(ref)}`;
                }
            });
            const refreshDmrLive = () => {
                const d = document.getElementById('report-date-input')?.value || new Date().toISOString().slice(0, 10);
                if (typeof window.renderMasterDailyReport === 'function') window.renderMasterDailyReport(d);
                if (window.DailyMasterSupremeEngine && typeof window.DailyMasterSupremeEngine.computeAndRenderSupremeDailyPosition === 'function') window.DailyMasterSupremeEngine.computeAndRenderSupremeDailyPosition();
            };
            ['magaiees_sync_channel', 'erp_live_sync', 'erp_db_sync'].forEach(ch => {
                if (typeof BroadcastChannel !== 'undefined') { const bc = new BroadcastChannel(ch); bc.onmessage = refreshDmrLive; }
            });
        }
    });

    window.addEventListener('online', () => {
        triggerFlashSyncSpark();
        const slQ = readDualArray('erp_sl_offline_queue_v1');
        if (slQ.length) {
            Promise.all(slQ.map(op => fetch('api/mirror_sync.php?action=' + op.action, { method: 'POST', headers: { 'Content-Type': 'application/json' }, keepalive: true, body: JSON.stringify(op.payload) }).then(r => r.json()).catch(() => null)))
                .then(() => writeDualStorage('erp_sl_offline_queue_v1', []));
        }
    });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) triggerFlashSyncSpark(); });

    window.ElsajanaAccountingBrain = {
        BANKAK_DUP_MSG,
        EMPTY_STOCK_MSG,
        ELSAJANA_STEEL_UNITS,
        floatingToast,
        shakeAndFocus,
        checkBankakDuplicate,
        registerBankakReceipts,
        checkDuplicateChequeNo,
        checkSameDayDuplicateInvoice,
        checkOutboundStockGuard,
        checkLandedCostMarginErosion,
        atomicCrossSystemSync,
        syncLogisticsReceiptAtomically,
        syncVoucherAtomically,
        syncPostedTransactionAtomically,
        resolveSourceDrillUrl,
        crdtMergeInvoices,
        triggerFlashSyncSpark,
        compressImageTo40KB,
        parseBankakWhatsAppText,
        openBankakFawrySmartModal,
        openInspectionAndBasculeModal,
        openChecksLifecycleModal,
        transitionCheckStage,
        openDocumentCycleModal,
        executeDocumentCycleAction,
        openPurchaseAiAnalystModal
    };
})();
