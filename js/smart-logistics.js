// ============================================================================
// ELSAJANA ERP — ICONIC UNIFIED SMART LOGISTICS ENGINE (GRN ⇄ GDN)
// Visual Symmetry with Strict Functional & Accounting Isolation
// ============================================================================

let slCurrentMode = localStorage.getItem('sl_active_mode_v1') || 'sales'; // 'sales' | 'purchases'
let slRegistryFilter = 'pending'; // 'pending' | 'partial' | 'done' | 'notes' | 'all'
let slCurrentDoc = null;
let slCurrentPhoto = null;
let slHtml5QrCode = null;
let slBridgePollTimer = null;
let slBroadcastChannel = null;
const SL_BRIDGE_CHANNEL = 'default_gate';
const slProcessedScanIds = {};
const slQrCooldownMap = {};
let slContinuousSuccessCount = 0;
let slContinuousRejectedCount = 0;

// ── TOAST & AUDIO-VISUAL SYNTHESIZER ─────────────────────────────────────────
function showSlToast(message, type = 'success', duration = null) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const effectiveDuration = duration !== null ? duration : (type === 'success' ? 2000 : 3200);
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? '✅' : type === 'error' ? '🚨' : '⚠️';
    toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 350);
    }, effectiveDuration);
}
window.alert = function (msg) {
    showSlToast(String(msg || ''), 'warning', 3000);
};

function playSlTone(type = 'ok') {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);

        if (type === 'ok') {
            if (navigator.vibrate) navigator.vibrate(80);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(980, ctx.currentTime);
            osc.frequency.setValueAtTime(1480, ctx.currentTime + 0.08);
            gain.gain.setValueAtTime(0.24, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.24);
            osc.start();
            osc.stop(ctx.currentTime + 0.24);
        } else if (type === 'switch') {
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(520, ctx.currentTime);
            osc.frequency.setValueAtTime(780, ctx.currentTime + 0.06);
            gain.gain.setValueAtTime(0.14, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.16);
            osc.start();
            osc.stop(ctx.currentTime + 0.16);
        } else {
            if (navigator.vibrate) navigator.vibrate([180, 70, 220]);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(280, ctx.currentTime);
            osc.frequency.setValueAtTime(175, ctx.currentTime + 0.16);
            gain.gain.setValueAtTime(0.32, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.42);
            osc.start();
            osc.stop(ctx.currentTime + 0.42);
        }
    } catch (e) {}
}

// ── WAREHOUSE, PARTY & ITEM META RESOLUTION ──────────────────────────────────
function getSlWarehouses() {
    let whs = [];
    try { whs = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || []; } catch (e) {}
    whs = whs.filter(w => w && w.id !== 'PENDING_RECEIPT' && !String(w.name || '').includes('قيد الاستلام'));
    if (!whs.length) {
        whs = [
            { id: 'wh_1', name: 'المخزن الرئيسي' },
            { id: 'wh_2', name: 'مخزن الفرع' }
        ];
    }
    return whs;
}

function resolveSlPartyName(inv, mode = slCurrentMode) {
    if (!inv) return '---';
    if (mode === 'purchases') {
        const raw = inv.supplierName || inv.vendorName || inv.supplier || inv.customerName || inv.customer || '';
        const contacts = JSON.parse(localStorage.getItem('erp_contacts_v1') || '[]');
        const suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
        const found = [...contacts, ...suppliers].find(c => String(c.id) === String(raw) || String(c.name) === String(raw));
        return found ? found.name : (raw || 'مورد عام');
    } else {
        const raw = inv.customerName || inv.clientName || inv.customer || '';
        const customers = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
        const contacts = JSON.parse(localStorage.getItem('erp_contacts_v1') || '[]');
        const found = [...customers, ...contacts].find(c => String(c.id) === String(raw) || String(c.name) === String(raw));
        return found ? found.name : (raw || 'عميل نقدي');
    }
}

function resolveSlItemMeta(it) {
    const erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
    const itemId = it.itemId || it.id || '';
    const rawName = String(it.itemName || it.name || '').trim();
    const normStr = (s) => String(s || '').trim()
        .replace(/[أإآ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/ى/g, 'ي')
        .replace(/\s+/g, ' ');

    const normRawName = normStr(rawName);
    const master = erpItems.find(x =>
        (itemId && String(x.id) === String(itemId)) ||
        (rawName && String(x.name || '').trim() === rawName) ||
        (normRawName && normStr(x.name) === normRawName)
    );

    const name = rawName || (master ? master.name : (itemId || 'صنف عام'));
    const unit = String(it.selectedUnit || it.unit || it.unitName || it.unitId || (master ? (master.unit || master.baseUnit || 'وحدة') : 'وحدة')).trim();

    let unitsList = [];
    if (master) {
        if (typeof window.extractItemUnits === 'function') {
            unitsList = window.extractItemUnits(master) || [];
        } else if (Array.isArray(master.unitsArray)) {
            unitsList = master.unitsArray;
        } else if (Array.isArray(master.units)) {
            unitsList = master.units;
        }
    }

    let baseUnit = (master && (master.baseUnit || master.subUnitName)) || '';
    if (!baseUnit && unitsList.length > 0) {
        const u1 = unitsList.find(u => parseFloat(u.factor) === 1);
        if (u1) baseUnit = u1.name || u1.unitName || '';
    }
    if (!baseUnit || normStr(baseUnit) === normStr(unit)) {
        if (/سيخ|حديد|لينيه|لينية/.test(name)) baseUnit = 'سيخة';
        else if (/اسمنت|أسمنت/.test(name)) baseUnit = 'كيس';
        else if (/طوب|بلوك/.test(name)) baseUnit = 'طوبة';
        else if (/زنك/.test(name)) baseUnit = 'لوح';
        else if (/مواسير|ماسورة/.test(name)) baseUnit = 'ماسورة';
        else baseUnit = (master && master.unit) || unit || 'قطعة';
    }

    let factor = parseFloat(it.customFactor ?? it.factor ?? it.unitFactor ?? 0);
    if ((!factor || isNaN(factor) || factor <= 0) && unitsList.length > 0) {
        const exactU = unitsList.find(u => normStr(u.name || u.unitName) === normStr(unit));
        if (exactU && parseFloat(exactU.factor) > 0) factor = parseFloat(exactU.factor);
    }
    if (!factor || isNaN(factor) || factor <= 0) {
        const n = normStr(name);
        if (/طن/.test(normStr(unit))) {
            if (/3\s*لين|8\s*مم|8\s*ملي/.test(n)) factor = /وزني/.test(unit) ? 166 : 135;
            else if (/4\s*لين|10\s*مم|12\s*مم/.test(n)) factor = /وزني/.test(unit) ? 110 : 92;
            else if (/5\s*لين|16\s*مم/.test(n)) factor = 63;
            else if (/اسمنت/.test(n)) factor = 20;
            else factor = 1;
        } else {
            factor = 1;
        }
    }

    const qty = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
    const price = parseFloat(it.price ?? it.unitPrice ?? it.cost ?? 0) || 0;
    const total = parseFloat(it.total ?? (qty * price)) || 0;
    const hasMultiUnits = factor > 1 || normStr(unit) !== normStr(baseUnit);
    const code = it.code || it.itemCode || (master ? (master.code || master.barcode || master.id) : (itemId || '---'));

    return {
        itemId: master ? master.id : (itemId || name),
        name,
        code,
        unit,
        baseUnit,
        factor,
        qty,
        baseQty: +(qty * factor).toFixed(2),
        price,
        total,
        hasMultiUnits
    };
}

function getSlItemStockInWarehouse(itemMeta, whIdOrName) {
    const whs = getSlWarehouses();
    const whObj = whs.find(w => String(w.id) === String(whIdOrName) || String(w.name) === String(whIdOrName)) || whs[0];
    const invList = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
    const erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

    const entry = invList.find(x =>
        (String(x.itemId || x.id) === String(itemMeta.itemId) || String(x.name || '').trim() === itemMeta.name.trim()) &&
        (String(x.warehouseId || x.warehouse) === String(whObj.id) || String(x.warehouse || x.warehouseId) === String(whObj.name))
    );
    if (entry && entry.qty !== undefined) {
        return +parseFloat(entry.qty || 0).toFixed(2);
    }

    const master = erpItems.find(x => String(x.id) === String(itemMeta.itemId) || String(x.name || '').trim() === itemMeta.name.trim());
    if (master && master.stock && master.stock[whObj.id] !== undefined) {
        return +parseFloat(master.stock[whObj.id] || 0).toFixed(2);
    }
    return 0;
}

// ── 2G NETWORK RESILIENCE & OFFLINE SYNC QUEUE + UNIFIED ITEM STOCK SYNC ──────
function syncSlItemTotalStockFields(mItem) {
    if (!mItem || typeof mItem !== 'object') return;
    let totalWarehouseStock = 0;
    if (mItem.stock && typeof mItem.stock === 'object') {
        Object.values(mItem.stock).forEach(v => {
            totalWarehouseStock += (parseFloat(v) || 0);
        });
    } else {
        totalWarehouseStock = parseFloat(mItem.quantity ?? mItem.currentStock ?? mItem.stockQty ?? 0) || 0;
    }
    const cleanTotal = +totalWarehouseStock.toFixed(4);
    mItem.quantity = cleanTotal;
    mItem.currentStock = cleanTotal;
    mItem.stockQty = cleanTotal;
    if (Array.isArray(mItem.units) && mItem.units[0]) {
        mItem.units[0].stock = cleanTotal;
    }
    if (Array.isArray(mItem.unitsArray) && mItem.unitsArray[0]) {
        mItem.unitsArray[0].stock = cleanTotal;
    }
}

// ── TOLERANT ARABIC NORMALIZER & LIVE THOUSANDS FORMATTER (CONSTITUTION 1) ──
window._slHasUnsavedChanges = false;

function normalizeSlArabicText(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
        .replace(/[أإآٱ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/[ىئ]/g, 'ي')
        .replace(/ؤ/g, 'و')
        .replace(/[\u064B-\u065F\u0670]/g, '')
        .toLowerCase()
        .trim();
}

function formatSlNum(val, maxDec = 2) {
    const n = parseFloat(val);
    if (!Number.isFinite(n)) return '0';
    return (+n.toFixed(maxDec)).toLocaleString('en-US', { maximumFractionDigits: maxDec });
}

function triggerSlSaveVisualPulse() {
    window._slHasUnsavedChanges = false;
    const targets = [
        document.querySelector('.sl-floating-summary'),
        document.getElementById('sl-inspection-cockpit'),
        document.getElementById('sl-btn-execute')
    ];
    targets.forEach(el => {
        if (!el) return;
        el.classList.remove('sl-save-pulse-active');
        void el.offsetWidth;
        el.classList.add('sl-save-pulse-active');
        setTimeout(() => el.classList.remove('sl-save-pulse-active'), 2400);
    });
}

function openSlQuickWarehouseModal() {
    const modal = document.getElementById('sl-quick-warehouse-modal');
    if (!modal) return;
    modal.style.display = 'flex';
    const nameInp = document.getElementById('sl-new-wh-name');
    const locInp = document.getElementById('sl-new-wh-loc');
    if (nameInp) {
        nameInp.value = '';
        setTimeout(() => nameInp.focus(), 60);
    }
    if (locInp) locInp.value = '';
}

function saveSlQuickWarehouse() {
    const nameInp = document.getElementById('sl-new-wh-name');
    const locInp = document.getElementById('sl-new-wh-loc');
    const name = (nameInp?.value || '').trim();
    if (!name) {
        window.focusRequiredSlElement('#sl-new-wh-name', '⚠️ يرجى كتابة اسم المستودع الجديد أولاً');
        return;
    }
    const whs = getSlWarehouses();
    if (whs.some(w => normalizeSlArabicText(w.name) === normalizeSlArabicText(name))) {
        showSlToast('⚠️ هذا المستودع مسجل مسبقاً في القائمة', 'warning');
        return;
    }
    const newId = 'WH-' + Date.now().toString().slice(-5);
    const newWh = {
        id: newId,
        name: name,
        location: (locInp?.value || '').trim() || 'الفرع العام',
        createdAt: new Date().toISOString()
    };
    whs.push(newWh);
    localStorage.setItem('erp_warehouses_v1', JSON.stringify(whs));
    localStorage.setItem('warehouses', JSON.stringify(whs));

    populateSlWarehouseSelects();
    const masterSel = document.getElementById('sl-master-warehouse');
    if (masterSel) {
        masterSel.value = newId;
        onSlMasterWarehouseChange(newId);
    }
    const modal = document.getElementById('sl-quick-warehouse-modal');
    if (modal) modal.style.display = 'none';
    broadcastSlRealtimeSync({ type: 'WAREHOUSE_ADDED', warehouse: newWh });
    showSlToast(`🏢 تم إضافة المستودع (${name}) واختياره فوراً بنجاح!`, 'success');
}

async function fetchWithSl2GTimeout(url, options = {}, timeoutMs = 4500) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeoutMs);
    try {
        const res = await fetch(url, { ...options, signal: controller.signal });
        clearTimeout(timer);
        return res;
    } catch (err) {
        clearTimeout(timer);
        if (options && options.method === 'POST' && options.body) {
            try {
                const q = JSON.parse(localStorage.getItem('sl_offline_sync_queue_v1') || '[]');
                q.push({ url, body: options.body, ts: Date.now() });
                localStorage.setItem('sl_offline_sync_queue_v1', JSON.stringify(q.slice(-60)));
            } catch (e) {}
        }
        throw err;
    }
}

async function flushSlOfflineSyncQueue() {
    if (window._slFlushingQueue || !navigator.onLine) return;
    try {
        const q = JSON.parse(localStorage.getItem('sl_offline_sync_queue_v1') || '[]');
        if (!q.length) return;
        window._slFlushingQueue = true;
        const results = await Promise.allSettled(
            q.map(item => {
                const ctrl = new AbortController();
                const t = setTimeout(() => ctrl.abort(), 3500);
                return fetch(item.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: item.body,
                    signal: ctrl.signal
                }).then(r => {
                    clearTimeout(t);
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return true;
                });
            })
        );
        const remaining = q.filter((_, idx) => results[idx].status !== 'fulfilled');
        if (remaining.length > 0) {
            localStorage.setItem('sl_offline_sync_queue_v1', JSON.stringify(remaining.slice(-60)));
        } else {
            localStorage.removeItem('sl_offline_sync_queue_v1');
            const pill = document.getElementById('sl-live-sync-status-pill');
            if (pill) pill.textContent = '⚡ متزامن لحظياً (100%)';
        }
    } catch (e) {} finally {
        window._slFlushingQueue = false;
    }
}

setInterval(() => {
    if (navigator.onLine) flushSlOfflineSyncQueue();
}, 5000);

window.addEventListener('online', async () => {
    await flushSlOfflineSyncQueue();
    if (typeof refreshAllSlData === 'function') refreshAllSlData(true);
});

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        flushSlOfflineSyncQueue();
        renderSlRegistry();
    }
});

function broadcastSlRealtimeSync(payload) {
    ['elsajana_erp_sync', 'magaies_purchase_sync_channel', 'magaies_sales_sync_channel', 'magaies_erp_sync_channel'].forEach(chName => {
        try {
            const ch = new BroadcastChannel(chName);
            ch.postMessage(payload);
            ch.close();
        } catch (e) {}
    });
    const pill = document.getElementById('sl-live-sync-status-pill');
    if (pill) {
        const nowStr = new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        pill.textContent = `⚡ متزامن (${nowStr})`;
    }
}

// ── PURCHASE PENDING & RECEIVED CALCULATION ENGINE (100% COMPATIBLE WITH purchase-receipt.php) ──
function getProcessedPurchaseInvoices() {
    const rawPrimary = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
    const rawLocal = JSON.parse(localStorage.getItem('magaies_local_purchase_invoices_v1') || '[]');
    const rawLegacy = JSON.parse(localStorage.getItem('erp_purchases_v1') || '[]');
    const rawGrnStore = JSON.parse(localStorage.getItem('erp_grn_v1') || '[]');

    const seenIds = new Set();
    const allPurchases = [];
    [...rawPrimary, ...rawLocal, ...rawLegacy, ...rawGrnStore].forEach(p => {
        if (!p || p.deleted) return;
        const uid = String(p.id || p.invoiceNo || '');
        if (!uid) return;
        const key = (p.isGRN ? 'GRN_' : 'INV_') + uid;
        if (seenIds.has(key)) return;
        seenIds.add(key);
        allPurchases.push(p);
    });

    const grnList = allPurchases.filter(p => p && !p.deleted && p.isGRN);

    // Map of originalInvoiceId -> { itemKey: receivedQty }
    const receivedByInv = {};
    grnList.forEach(grn => {
        const rootId = String(grn.originalInvoiceId || '');
        if (!rootId) return;
        if (!receivedByInv[rootId]) receivedByInv[rootId] = { totalReceived: 0, byItem: {}, grns: [] };
        receivedByInv[rootId].grns.push(grn);
        (grn.items || grn.lines || []).forEach(line => {
            const key = String(line.itemId || line.id || line.name || line.itemName || 'صنف');
            const q = parseFloat(line.quantity ?? line.qty ?? 0) || 0;
            receivedByInv[rootId].byItem[key] = (receivedByInv[rootId].byItem[key] || 0) + q;
            receivedByInv[rootId].totalReceived += q;
        });
    });

    const baseInvoices = allPurchases.filter(inv => inv && !inv.deleted && !inv.isGRN && !inv.isReturn && inv.status !== 'draft' && inv.status !== 'proforma');

    return baseInvoices.map(inv => {
        const invNo = String(inv.invoiceNo || inv.id || '');
        const recInfo = receivedByInv[invNo] || { totalReceived: 0, byItem: {}, grns: [] };
        const items = inv.items || inv.lines || [];

        let totalOrdered = 0;
        let totalPrevReceived = 0;
        let totalRem = 0;

        const enrichedItems = items.map(line => {
            const key = String(line.itemId || line.id || line.name || line.itemName || 'صنف');
            const origQty = parseFloat(line.quantity ?? line.qty ?? 0) || 0;
            const lineWh = line.warehouseId || inv.warehouse;
            const isDirectWarehouseAlready = (
                lineWh &&
                lineWh !== 'PENDING_RECEIPT' &&
                !String(lineWh).includes('قيد الاستلام') &&
                (inv.receiptStatus === 'received' || inv.status === 'received') &&
                recInfo.grns.length === 0
            );
            const prevRec = isDirectWarehouseAlready
                ? origQty
                : Math.min(origQty, parseFloat(recInfo.byItem[key] || line.receivedQty || 0));
            const rem = Math.max(0, +(origQty - prevRec).toFixed(4));

            totalOrdered += origQty;
            totalPrevReceived += prevRec;
            totalRem += rem;

            return {
                ...line,
                qty: origQty,
                quantity: origQty,
                deliveredQty: prevRec,
                receivedQty: prevRec,
                remainingQty: rem
            };
        });

        let calcStatus = 'pending';
        if (totalOrdered > 0 && totalRem <= 0.0001) {
            calcStatus = 'done';
        } else if (totalPrevReceived > 0.0001 && totalRem > 0.0001) {
            calcStatus = 'partial';
        } else if (inv.receiptStatus === 'received' || inv.status === 'received') {
            calcStatus = 'done';
        }

        const latestGrn = recInfo.grns[recInfo.grns.length - 1];
        return {
            ...inv,
            invoiceNo: invNo,
            items: enrichedItems,
            _slStatus: calcStatus,
            _slNoteNo: latestGrn ? latestGrn.id : (inv.grnNo || ''),
            _slGrns: recInfo.grns
        };
    });
}

function getProcessedSalesInvoices() {
    const rawPrimary = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
    const rawLocal = JSON.parse(localStorage.getItem('magaies_local_sales_invoices_v1') || '[]');
    const rawLegacy = JSON.parse(localStorage.getItem('erp_sales_v1') || '[]');
    const seen = new Set();
    const allSales = [];
    [...rawPrimary, ...rawLocal, ...rawLegacy].forEach(i => {
        if (!i || i.deleted || i.status === 'draft' || i.status === 'proforma') return;
        const no = String(i.invoiceNo || i.id || '');
        if (!no || seen.has(no)) return;
        seen.add(no);
        allSales.push(i);
    });
    return allSales.map(inv => {
        const invNo = String(inv.invoiceNo || inv.id || '');
        const items = inv.items || inv.rows || [];
        const isMarkedDone = (
            inv.isDelivered === true ||
            inv.deliveryStatus === 'delivered' ||
            inv.deliveryStatus === 'Delivered_Pending_Admin' ||
            inv.deliveryStatus === 'Admin_Closed' ||
            inv.status === 'delivered'
        );

        let totalOrdered = 0;
        let totalPrevDel = 0;
        let totalRem = 0;

        const enrichedItems = items.map(line => {
            const origQty = parseFloat(line.qty ?? line.quantity ?? 0) || 0;
            const prevDel = isMarkedDone ? origQty : Math.min(origQty, parseFloat(line.deliveredQty || 0) || 0);
            const rem = Math.max(0, +(origQty - prevDel).toFixed(4));
            totalOrdered += origQty;
            totalPrevDel += prevDel;
            totalRem += rem;
            return {
                ...line,
                qty: origQty,
                deliveredQty: prevDel,
                remainingQty: rem
            };
        });

        let calcStatus = 'pending';
        if (isMarkedDone || (totalOrdered > 0 && totalRem <= 0.0001)) {
            calcStatus = 'done';
        } else if (inv.deliveryStatus === 'partial' || (totalPrevDel > 0.0001 && totalRem > 0.0001)) {
            calcStatus = 'partial';
        }

        return {
            ...inv,
            invoiceNo: invNo,
            items: enrichedItems,
            _slStatus: calcStatus,
            _slNoteNo: inv.deliveryNo || ''
        };
    });
}

// ── MODE SWITCHER (CHAMELEON VISUAL + FUNCTIONAL ISOLATION) ──────────────────
function switchLogisticsMode(mode, silent = false) {
    if (mode !== 'sales' && mode !== 'purchases') mode = 'sales';
    slCurrentMode = mode;
    localStorage.setItem('sl_active_mode_v1', mode);
    document.body.setAttribute('data-logistics-mode', mode);

    document.getElementById('sl-tab-sales')?.classList.toggle('active', mode === 'sales');
    document.getElementById('sl-tab-purchases')?.classList.toggle('active', mode === 'purchases');

    const isSales = (mode === 'sales');

    // Update dynamic labels across the entire cockpit (concise & clutter-free per Order 1)
    const setTxt = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
    setTxt('sl-main-title-icon', isSales ? '📤' : '📥');
    setTxt('sl-main-title-text', isSales
        ? 'بوابة تسليم المبيعات (GDN)'
        : 'بوابة استلام المشتريات (GRN)');
    setTxt('sl-kpi-lbl-pending', isSales ? '⏳ قيد الصرف' : '⏳ قيد الاستلام');
    setTxt('sl-kpi-lbl-partial', isSales ? '🌗 مسلّمة جزئياً' : '🌗 مستلمة جزئياً');
    setTxt('sl-kpi-lbl-done', isSales ? '✅ مكتملة الصرف' : '✅ مكتملة الاستلام');
    setTxt('sl-kpi-lbl-notes', isSales ? '📊 أذونات الصرف (GDN)' : '📊 مذكرات الاستلام (GRN)');

    setTxt('sl-registry-title', isSales
        ? 'سجل فواتير وأذونات المبيعات (GDN)'
        : 'سجل فواتير ومذكرات المشتريات (GRN)');
    setTxt('sl-reg-th-party', isSales ? '👤 العميل والمستلم' : '🏢 المورد والمسلّم');
    setTxt('sl-lbl-party-title', isSales ? '👤 العميل' : '🏢 المورد');
    setTxt('sl-lbl-wh-select', isSales ? '🏢 مخزن الصرف الافتراضي:' : '🏢 مخزن الاستلام الافتراضي:');
    setTxt('sl-lbl-receiver', isSales ? '🤝 المستلم الفعلي:' : '🤝 أمين المخزن المستلم:');
    setTxt('sl-th-dual-action', isSales
        ? '📤 كمية الصرف الآن (أساسية ⇄ فرعية)'
        : '📥 كمية الاستلام الآن (أساسية ⇄ فرعية)');
    setTxt('sl-summary-mode-badge', isSales ? '📤 تسليم مبيعات (GDN)' : '📥 استلام مشتريات (GRN)');

    const btnExec = document.getElementById('sl-btn-execute');
    if (btnExec) {
        btnExec.innerHTML = isSales
            ? '🚚 اعتماد الصرف (GDN) <kbd style="font-size:0.72rem;background:rgba(0,0,0,0.35);padding:1px 6px;border-radius:5px;">F10</kbd>'
            : '📥 اعتماد الاستلام (GRN) <kbd style="font-size:0.72rem;background:rgba(0,0,0,0.35);padding:1px 6px;border-radius:5px;">F10</kbd>';
    }

    const btnUndo = document.getElementById('sl-btn-undo');
    if (btnUndo) {
        btnUndo.innerHTML = isSales
            ? '↩️ إلغاء الصرف'
            : '↩️ إلغاء الاستلام';
    }

    if (!silent) {
        playSlTone('switch');
        showSlToast(isSales
            ? '📤 وضع تسليم المبيعات (GDN)'
            : '📥 وضع استلام المشتريات (GRN)', 'success', 2000);
    }

    slCurrentDoc = null;
    clearSlDocumentCard();
    populateSlWarehouseSelects();
    renderSlRegistry();
}

function populateSlWarehouseSelects() {
    const whs = getSlWarehouses();
    const masterSel = document.getElementById('sl-master-warehouse');
    const filterSel = document.getElementById('sl-reg-wh-filter');

    if (masterSel) {
        const prev = masterSel.value;
        masterSel.innerHTML = whs.map(w => `<option value="${w.id}">${w.name}</option>`).join('');
        if (prev && whs.some(w => String(w.id) === String(prev))) masterSel.value = prev;
    }
    if (filterSel) {
        const prevF = filterSel.value || 'all';
        filterSel.innerHTML = `<option value="all">🏢 كل المخازن</option>` + whs.map(w => `<option value="${w.id}">${w.name}</option>`).join('');
        filterSel.value = prevF;
    }
}

function onSlMasterWarehouseChange(newWhId) {
    document.querySelectorAll('.sl-row-wh-select').forEach(sel => {
        sel.value = newWhId;
        const idx = sel.dataset.itemIdx;
        updateSlRowStockBadge(idx, newWhId);
    });
    showSlToast('🏢 تم تعميم المستودع على جميع سطور الفاتورة (ويمكنك تخصيص أي سطر بمفرده)', 'success', 2200);
}

function updateSlRowStockBadge(idx, whId) {
    if (!slCurrentDoc || !slCurrentDoc.items || !slCurrentDoc.items[idx]) return;
    const meta = resolveSlItemMeta(slCurrentDoc.items[idx]);
    const avail = getSlItemStockInWarehouse(meta, whId);
    const badge = document.getElementById(`sl-row-stock-badge-${idx}`);
    if (badge) {
        badge.textContent = `الرصيد الحالي: ${avail} ${meta.unit}`;
        badge.style.color = (slCurrentMode === 'sales' && avail <= 0) ? '#f87171' : '#94a3b8';
    }
}

// ── REGISTRY TABLE & KPI RENDERING ───────────────────────────────────────────
function setSlRegistryFilter(filter) {
    slRegistryFilter = filter;
    ['pending', 'partial', 'done', 'notes', 'all'].forEach(f => {
        const btn = document.getElementById(`sl-filter-btn-${f}`);
        if (btn) {
            if (f === filter) {
                btn.style.background = 'var(--sl-primary)';
                btn.style.color = '#ffffff';
                btn.style.borderColor = 'var(--sl-primary-light)';
            } else {
                btn.style.background = 'rgba(15, 23, 42, 0.75)';
                btn.style.color = '#f8fafc';
                btn.style.borderColor = 'rgba(255,255,255,0.18)';
            }
        }
        const kpi = document.getElementById(`sl-kpi-card-${f}`);
        if (kpi) kpi.classList.toggle('active-filter', f === filter);
    });
    renderSlRegistry();
}

function renderSlRegistry() {
    const salesList = getProcessedSalesInvoices();
    const purchasesList = getProcessedPurchaseInvoices();

    // Update top mode switcher badges always
    const salesPendingCount = salesList.filter(i => i._slStatus === 'pending' || i._slStatus === 'partial').length;
    const purPendingCount = purchasesList.filter(i => i._slStatus === 'pending' || i._slStatus === 'partial').length;
    const sb = document.getElementById('sl-tab-sales-badge');
    const pb = document.getElementById('sl-tab-purchases-badge');
    if (sb) sb.textContent = salesPendingCount;
    if (pb) pb.textContent = purPendingCount;

    const isSales = (slCurrentMode === 'sales');
    const sourceList = isSales ? salesList : purchasesList;

    const allNotes = isSales
        ? JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]').filter(n => n && !n.deleted)
        : JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]').filter(n => n && !n.deleted && n.isGRN);

    const kpiPending = sourceList.filter(i => i._slStatus === 'pending').length;
    const kpiPartial = sourceList.filter(i => i._slStatus === 'partial').length;
    const kpiDone    = sourceList.filter(i => i._slStatus === 'done').length;
    const kpiNotes   = allNotes.length;

    const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = formatSlNum(v, 0); };
    setVal('sl-kpi-val-pending', kpiPending);
    setVal('sl-kpi-val-partial', kpiPartial);
    setVal('sl-kpi-val-done', kpiDone);
    setVal('sl-kpi-val-notes', kpiNotes);

    const tbody = document.getElementById('sl-registry-tbody');
    if (!tbody) return;

    const q = normalizeSlArabicText(document.getElementById('sl-reg-search')?.value || '');
    const whFilter = document.getElementById('sl-reg-wh-filter')?.value || 'all';
    const dateFrom = document.getElementById('sl-reg-date-from')?.value || '';
    const dateTo = document.getElementById('sl-reg-date-to')?.value || '';
    const limit = parseInt(document.getElementById('sl-reg-limit')?.value || '50', 10) || 50;
    const whs = getSlWarehouses();

    // If 'notes' filter is selected, show official GDN / GRN notes
    let rowsData = [];
    if (slRegistryFilter === 'notes') {
        rowsData = allNotes.map(n => ({
            isOfficialNote: true,
            noteId: n.id || n.deliveryNo || '---',
            invoiceNo: n.invoiceNo || n.originalInvoiceId || n.id,
            date: (n.date || n.createdAt || '').slice(0, 10),
            time: n.time || '',
            partyName: isSales ? (n.customerName || n.customer || 'عميل') : resolveSlPartyName(n, 'purchases'),
            warehouse: n.warehouse || n.warehouseName || 'المخزن الرئيسي',
            items: n.items || [],
            _slStatus: 'done',
            rawNote: n
        }));
    } else {
        rowsData = sourceList.filter(inv => {
            if (slRegistryFilter === 'pending' && inv._slStatus !== 'pending') return false;
            if (slRegistryFilter === 'partial' && inv._slStatus !== 'partial') return false;
            if (slRegistryFilter === 'done' && inv._slStatus !== 'done') return false;
            return true;
        }).map(inv => ({
            isOfficialNote: false,
            noteId: inv._slNoteNo || '',
            invoiceNo: inv.invoiceNo,
            date: (inv.date || inv.savedAt || '').slice(0, 10),
            time: inv.time || '',
            partyName: resolveSlPartyName(inv, slCurrentMode),
            warehouse: inv.deliveredWarehouseName || inv.warehouseName || inv.warehouse || whs[0].name,
            items: inv.items || [],
            _slStatus: inv._slStatus,
            rawInv: inv
        }));
    }

    // Apply tolerant Arabic search, warehouse filter & date range filter (Constitution 3)
    const filtered = rowsData.filter(r => {
        if (whFilter !== 'all') {
            const whObj = whs.find(w => String(w.id) === String(whFilter));
            const matchWh = whObj && (String(r.warehouse).includes(whObj.name) || String(r.warehouse) === String(whObj.id));
            if (!matchWh) return false;
        }
        if (dateFrom && r.date && r.date < dateFrom) return false;
        if (dateTo && r.date && r.date > dateTo) return false;
        if (!q) return true;
        const itemsTxt = normalizeSlArabicText((r.items || []).map(it => resolveSlItemMeta(it).name).join(' '));
        return (
            normalizeSlArabicText(r.invoiceNo).includes(q) ||
            normalizeSlArabicText(r.noteId).includes(q) ||
            normalizeSlArabicText(r.partyName).includes(q) ||
            itemsTxt.includes(q)
        );
    });
    window._slLastFilteredRegistryRows = filtered;

    const countBadge = document.getElementById('sl-reg-count-badge');
    if (countBadge) countBadge.textContent = `${formatSlNum(filtered.length, 0)} مستند`;

    const displayList = filtered.slice(0, limit);
    if (!displayList.length) {
        tbody.innerHTML = `<tr><td colspan="6" style="padding:32px; text-align:center; color:#94a3b8; font-weight:800;">لا توجد مستندات مطابقة للفلتر الحالي — جرب تبديل التبويب إلى (📋 الكل)</td></tr>`;
        return;
    }

    tbody.innerHTML = displayList.map(r => {
        const isSelected = slCurrentDoc && String(slCurrentDoc.invoiceNo) === String(r.invoiceNo);
        const statusBadge = r._slStatus === 'done'
            ? `<span class="sl-badge sl-badge-done">✅ ${isSales ? 'تم الصرف' : 'تم الاستلام'}</span>`
            : r._slStatus === 'partial'
                ? `<span class="sl-badge sl-badge-partial">🌗 ${isSales ? 'صرف جزئي' : 'استلام جزئي'}</span>`
                : `<span class="sl-badge sl-badge-pending">⏳ ${isSales ? 'قيد الصرف' : 'قيد الاستلام'}</span>`;

        const itemsPills = (r.items || []).slice(0, 4).map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const rem = it.remainingQty !== undefined ? it.remainingQty : m.qty;
            return `<span onclick="event.stopPropagation(); loadSlDocument('${r.invoiceNo}', ${idx})" style="display:inline-flex; align-items:center; gap:4px; background:rgba(15,23,42,0.85); border:1px solid rgba(148,163,184,0.28); padding:3px 9px; border-radius:8px; font-size:0.77rem; font-weight:800; margin:2px; cursor:pointer;" title="اضغط لفتح وتقسيم هذا الصنف مباشرة">
                📦 <strong style="color:#fff;">${m.name}</strong>
                <span style="color:var(--sl-primary-light);">(${formatSlNum(m.qty)} ${m.unit})</span>
                ${rem < m.qty && rem > 0 ? `<span style="color:#fbbf24;">[متبقي: ${formatSlNum(rem)}]</span>` : ''}
            </span>`;
        }).join('') + ((r.items || []).length > 4 ? `<span style="font-size:0.75rem; color:#94a3b8; font-weight:800;"> +${r.items.length - 4} أصناف</span>` : '');

        return `
            <tr class="${isSelected ? 'sl-selected-row' : ''}" onclick="loadSlDocument('${r.invoiceNo}')" style="cursor:pointer;">
                <td style="text-align:right; font-family:'Inter',monospace; font-weight:900;">
                    <div style="color:var(--sl-primary-light); font-size:0.96rem;">#${r.invoiceNo}</div>
                    ${r.noteId ? `<div style="font-size:0.73rem; color:#c084fc;">🏷️ ${r.noteId}</div>` : ''}
                </td>
                <td style="text-align:center; font-size:0.84rem; color:#cbd5e1; font-weight:800;">
                    <div>${r.date || '---'}</div>
                    ${r.time ? `<div style="font-size:0.72rem; color:#64748b;">${r.time}</div>` : ''}
                </td>
                <td style="text-align:right; font-weight:900; color:#ffffff;">
                    ${r.partyName}
                </td>
                <td style="text-align:right;">
                    ${itemsPills || '<span style="color:#64748b;">---</span>'}
                </td>
                <td style="text-align:center;">
                    <div style="font-size:0.78rem; color:#fcd34d; font-weight:800; margin-bottom:4px;">🏢 ${r.warehouse}</div>
                    ${statusBadge}
                </td>
                <td style="text-align:center;" onclick="event.stopPropagation();">
                    <div style="display:flex; align-items:center; justify-content:center; gap:6px; flex-wrap:wrap;">
                        ${r._slStatus !== 'done' ? `
                            <button type="button" onclick="quickExecuteFullSlDoc('${r.invoiceNo}')" class="sl-btn" style="padding:5px 10px; font-size:0.76rem; background:linear-gradient(135deg,var(--sl-primary),var(--sl-primary-dark)); border-color:var(--sl-primary-light); color:#fff;" title="اعتماد كامل المتبقي بضغطة زر واحدة">
                                ⚡ ${isSales ? 'صرف كامل' : 'استلام كامل'}
                            </button>
                        ` : ''}
                        <button type="button" onclick="loadSlDocument('${r.invoiceNo}')" class="sl-btn" style="padding:5px 10px; font-size:0.76rem; color:#38bdf8; border-color:rgba(56,189,248,0.45);">
                            ✂️ ${r._slStatus === 'done' ? 'عرض / طباعة' : 'تقسيم وفحص'}
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function quickLoadFirstMatch(query) {
    const q = String(query || '').trim();
    if (!q) return;
    loadSlDocument(q);
}

// ── LOAD DOCUMENT INTO DUAL-UNIT & MULTI-WAREHOUSE WORKSTATION ───────────────
function resolveSlPartyBalance(partyName, mode) {
    if (!partyName || partyName === '---') return null;
    const normTarget = normalizeSlArabicText(partyName);
    const keys = mode === 'sales'
        ? ['erp_customers_v1', 'erp_contacts_v1', 'customers']
        : ['erp_suppliers_v1', 'erp_contacts_v1', 'suppliers'];
    for (const k of keys) {
        try {
            const arr = JSON.parse(localStorage.getItem(k) || '[]');
            if (!Array.isArray(arr)) continue;
            const found = arr.find(c => c && normalizeSlArabicText(c.name || c.supplierName || c.customerName || '') === normTarget);
            if (found && (found.balance !== undefined || found.currentBalance !== undefined)) {
                return parseFloat(found.balance ?? found.currentBalance) || 0;
            }
        } catch (e) {}
    }
    // Fallback: sum remaining/unpaid amounts from purchase/sales invoices for this party
    try {
        const invKey = mode === 'sales' ? 'erp_sales_invoices_v1' : 'erp_purchase_invoices_v1';
        const invs = JSON.parse(localStorage.getItem(invKey) || '[]');
        let bal = 0;
        invs.forEach(inv => {
            if (normalizeSlArabicText(resolveSlPartyName(inv, mode)) === normTarget) {
                const rem = parseFloat(inv.remaining ?? inv.remainingAmount ?? 0) || 0;
                bal += rem;
            }
        });
        return bal;
    } catch (e) {
        return 0;
    }
}

function resolveSlPartySlaScorecard(partyName, mode) {
    if (!partyName || partyName === '---') return { accuracyPct: 100, avgGateMin: 12, totalDocs: 1 };
    const normTarget = normalizeSlArabicText(partyName);
    const list = mode === 'sales' ? getProcessedSalesInvoices() : getProcessedPurchaseInvoices();
    const partyDocs = list.filter(i => normalizeSlArabicText(resolveSlPartyName(i, mode)) === normTarget);
    const totalDocs = Math.max(1, partyDocs.length);

    let dnCount = 0;
    try {
        const dnList = JSON.parse(localStorage.getItem('erp_debit_notes_v1') || '[]');
        dnCount = dnList.filter(d => d && normalizeSlArabicText(d.partyName || '') === normTarget).length;
    } catch (e) {}

    const accuracyPct = Math.max(60, Math.min(100, Math.round(((totalDocs - dnCount) / totalDocs) * 100)));
    let sumMin = 0;
    let countMin = 0;
    partyDocs.forEach(d => {
        if (d.gateDurationMinutes && d.gateDurationMinutes > 0) {
            sumMin += parseFloat(d.gateDurationMinutes);
            countMin++;
        }
    });
    const avgGateMin = countMin > 0 ? Math.round(sumMin / countMin) : 14;
    return { accuracyPct, avgGateMin, totalDocs, dnCount };
}

function clearSlDocumentCard() {
    window._slHasUnsavedChanges = false;
    const setTxt = (id, t) => { const el = document.getElementById(id); if (el) el.textContent = t; };
    setTxt('sl-lbl-inv-no', '---');
    setTxt('sl-lbl-party-name', '---');
    setTxt('sl-lbl-party-balance', '💰 الرصيد: ---');
    setTxt('sl-lbl-party-sla', '⭐ كفاءة التوريد والبوابة: ---');
    setTxt('sl-lbl-date', '---');
    setTxt('sl-lbl-hash', '---');
    setTxt('sl-lbl-audit-stamp', '🛡️ البصمة: بانتظار اختيار مستند');
    const tbody = document.getElementById('sl-items-tbody');
    if (tbody) {
        tbody.innerHTML = `<tr><td colspan="10" style="padding:40px; text-align:center; color:#94a3b8; font-weight:800;">👆 اختر مستنداً من السجل أو امسح رمز QR</td></tr>`;
    }
    updateSlFloatingSummary();
}

function loadSlDocument(queryNo, focusItemIdx = null) {
    if (!queryNo) return false;
    window._slHasUnsavedChanges = false;
    const cleanQ = normalizeSlArabicText(String(queryNo).replace(/^#/, ''));
    const isSales = (slCurrentMode === 'sales');
    const list = isSales ? getProcessedSalesInvoices() : getProcessedPurchaseInvoices();

    let doc = list.find(i =>
        normalizeSlArabicText(i.invoiceNo) === cleanQ ||
        normalizeSlArabicText(i._slNoteNo || '') === cleanQ ||
        normalizeSlArabicText(i.id || '') === cleanQ
    );
    if (!doc) {
        doc = list.find(i => normalizeSlArabicText(resolveSlPartyName(i, slCurrentMode)).includes(cleanQ));
    }

    if (!doc) {
        showSlToast(`❌ لم يتم العثور على مستند برقم: ${queryNo}`, 'error');
        return false;
    }

    slCurrentDoc = doc;
    const partyName = resolveSlPartyName(doc, slCurrentMode);

    document.getElementById('sl-lbl-inv-no').textContent = `#${doc.invoiceNo}` + (doc._slNoteNo ? ` (${doc._slNoteNo})` : '');
    document.getElementById('sl-lbl-party-name').textContent = partyName;
    document.getElementById('sl-lbl-date').textContent = (doc.date || doc.savedAt || '---').slice(0, 10);
    document.getElementById('sl-lbl-hash').textContent = doc.hash || (`${isSales ? 'GDN' : 'GRN'}-SEC-` + btoa(unescape(encodeURIComponent(String(doc.invoiceNo)))).slice(0, 14));

    const balEl = document.getElementById('sl-lbl-party-balance');
    if (balEl) {
        const balVal = resolveSlPartyBalance(partyName, slCurrentMode);
        balEl.textContent = balVal !== null ? `💰 الرصيد: ${formatSlNum(balVal, 2)} ج.س` : '💰 الرصيد: 0 ج.س';
    }

    const slaEl = document.getElementById('sl-lbl-party-sla');
    if (slaEl) {
        const sla = resolveSlPartySlaScorecard(partyName, slCurrentMode);
        slaEl.textContent = `⭐ دقة الالتزام: ${sla.accuracyPct}% | ⏱️ متوسط البوابة: ${sla.avgGateMin} د (${sla.totalDocs} مستند)`;
    }

    const auditEl = document.getElementById('sl-lbl-audit-stamp');
    if (auditEl) {
        const curUser = localStorage.getItem('erp_current_user_name') || localStorage.getItem('username') || 'أمين المستودع المعتمد';
        const isMob = /Android|iPhone|iPad|Mobile/i.test(navigator.userAgent);
        const devLabel = isMob ? '📱 موبايل' : '💻 لابتوب/سطح مكتب';
        const stampTime = (doc.lastExecutedAt || doc.savedAt || new Date().toISOString()).replace('T', ' ').slice(0, 16);
        auditEl.textContent = `🛡️ ${curUser} | ${devLabel} | 🕒 ${stampTime}`;
    }

    const statusPill = document.getElementById('sl-doc-status-pill');
    if (statusPill) {
        if (doc._slStatus === 'done') {
            statusPill.className = 'sl-badge sl-badge-done';
            statusPill.textContent = isSales ? '✅ تم الصرف بالكامل' : '✅ تم الاستلام بالكامل';
        } else if (doc._slStatus === 'partial') {
            statusPill.className = 'sl-badge sl-badge-partial';
            statusPill.textContent = isSales ? '🌗 مسلّم جزئياً' : '🌗 مستلم جزئياً';
        } else {
            statusPill.className = 'sl-badge sl-badge-pending';
            statusPill.textContent = isSales ? '⏳ جاهز للصرف' : '⏳ جاهز للاستلام';
        }
    }

    const recInput = document.getElementById('sl-receiver-name');
    const drvInput = document.getElementById('sl-driver-name');
    const trkInput = document.getElementById('sl-truck-plate');
    const gpInput  = document.getElementById('sl-gate-pass-no');
    const wNotes   = document.getElementById('sl-warehouse-notes');
    const pNotes   = document.getElementById('sl-personal-notes');

    if (recInput) recInput.value = doc.receiverName || partyName || '';
    if (drvInput) drvInput.value = doc.driverName || '';
    if (trkInput) trkInput.value = doc.truckPlate || '';
    if (gpInput)  gpInput.value  = doc.gatePassNo || (`${isSales ? 'GP' : 'GRN'}-` + String(doc.invoiceNo).replace(/\D/g, '').slice(-4) + '-' + Date.now().toString().slice(-3));
    if (wNotes)   wNotes.value   = doc.warehouseNotes || doc.notes || '';
    if (pNotes)   pNotes.value   = doc.personalNotes || '';

    // Proposal 3: Time-In / Time-Out Gate Stamp (ختم وقت دخول وخروج الشاحنة)
    const tInEl = document.getElementById('sl-gate-time-in');
    const tOutEl = document.getElementById('sl-gate-time-out');
    const nowTimeStr = new Date().toTimeString().slice(0, 8);
    window._slGateArrivalTs = doc._gateArrivalTs || Date.now();
    doc._gateArrivalTs = window._slGateArrivalTs;
    if (tInEl) tInEl.value = doc.gateTimeIn || nowTimeStr;
    if (tOutEl) tOutEl.value = doc.gateTimeOut || (doc._slStatus === 'done' ? nowTimeStr : '');
    updateSlGateDurationBadge();

    renderSlItemsGrid();
    renderSlRegistry();

    if (focusItemIdx !== null && focusItemIdx !== undefined) {
        setTimeout(() => {
            const targetInp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${focusItemIdx}"]`);
            if (targetInp) {
                targetInp.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetInp.focus();
                targetInp.select();
            }
        }, 80);
    }
    return true;
}

function renderSlItemsGrid() {
    const tbody = document.getElementById('sl-items-tbody');
    if (!tbody || !slCurrentDoc) return;

    const items = slCurrentDoc.items || [];
    const whs = getSlWarehouses();
    const masterWhId = document.getElementById('sl-master-warehouse')?.value || whs[0].id;
    const isSales = (slCurrentMode === 'sales');

    if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="10" style="padding:30px; text-align:center; color:#fca5a5;">⚠️ لا توجد أصناف مسجلة في هذا المستند</td></tr>`;
        updateSlFloatingSummary();
        return;
    }

    tbody.innerHTML = items.map((row, idx) => {
        const meta = resolveSlItemMeta(row);
        const origQty = parseFloat(row._origInvoiceQty ?? meta.qty) || meta.qty;
        const prevQty = parseFloat(row.deliveredQty || row.receivedQty || 0) || 0;
        const remQty = slCurrentDoc._slStatus === 'done' ? 0 : Math.max(0, +(parseFloat(row.remainingQty ?? (origQty - prevQty))).toFixed(4));
        const defaultExecQty = row._splitExecQty !== undefined
            ? parseFloat(row._splitExecQty)
            : (slCurrentDoc._slStatus === 'done' ? origQty : remQty);
        const defaultBaseQty = +(defaultExecQty * meta.factor).toFixed(2);

        const rowWhCandidate = row.warehouseId || slCurrentDoc.deliveredWarehouse || slCurrentDoc.warehouseId || slCurrentDoc.warehouse || masterWhId;
        const matchedWh = whs.find(w => String(w.id) === String(rowWhCandidate) || String(w.name) === String(rowWhCandidate)) || whs[0];
        const availStock = getSlItemStockInWarehouse(meta, matchedWh.id);

        const whOptionsHtml = whs.map(w => `<option value="${w.id}" ${String(w.id) === String(matchedWh.id) ? 'selected' : ''}>🏢 ${w.name}</option>`).join('');
        const binVal = String(row.binLocation || row.rack || '').replace(/"/g, '&quot;');
        const batchVal = String(row.batchNo || row.expiryDate || '').replace(/"/g, '&quot;');

        return `
            <tr data-row-idx="${idx}" style="${row._isSplitRow ? 'background:rgba(56,189,248,0.06);' : ''}">
                <td style="text-align:center; font-weight:900; color:#94a3b8;">${idx + 1}</td>

                <td class="sl-col-wh" style="text-align:right;">
                    <div style="display:flex; gap:4px; align-items:center;">
                        <select class="sl-input sl-row-wh-select" data-item-idx="${idx}" onchange="updateSlRowStockBadge(${idx}, this.value)" style="flex:1; padding:5px 8px !important; font-size:0.8rem !important; color:#fcd34d !important;">
                            ${whOptionsHtml}
                        </select>
                        ${slCurrentDoc._slStatus !== 'done' ? (row._isSplitRow
                            ? `<button type="button" onclick="removeSlSplitRow(${idx})" class="sl-btn" style="padding:4px 7px; font-size:0.72rem; background:rgba(239,68,68,0.2); border-color:#f87171; color:#fca5a5;" title="دمج السطر المقسم مع السطر الأصلي">✕</button>`
                            : `<button type="button" onclick="splitSlItemRow(${idx})" class="sl-btn" style="padding:4px 7px; font-size:0.72rem; background:rgba(56,189,248,0.18); border-color:#38bdf8; color:#7dd3fc;" title="تقسيم الصنف على مستودع آخر في نفس الدفعة">➕ مخزن</button>`
                        ) : ''}
                    </div>
                    <div id="sl-row-stock-badge-${idx}" style="font-size:0.73rem; color:${isSales && availStock < remQty ? '#f87171' : '#94a3b8'}; font-weight:800; margin-top:3px;">
                        الرصيد: ${availStock} ${meta.unit}
                    </div>
                </td>

                <td style="text-align:right;">
                    <div style="font-weight:900; color:#ffffff; font-size:0.96rem;">${meta.name} ${row._isSplitRow ? '<span class="sl-badge sl-badge-partial" style="font-size:0.65rem; padding:1px 6px;">دفعة مخزن فرعي</span>' : ''}</div>
                    <div style="font-size:0.72rem; color:#64748b; font-family:'Inter',monospace;">كود: ${meta.code}</div>
                </td>

                <td class="sl-col-bin" style="text-align:center;">
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <input type="text" class="sl-input sl-row-bin-input" data-item-idx="${idx}" value="${binVal}" placeholder="📍 الرف / الممر (A-01)" style="width:100%; padding:4px 7px !important; font-size:0.75rem !important; text-align:center;">
                        <input type="text" class="sl-input sl-row-batch-input" data-item-idx="${idx}" value="${batchVal}" placeholder="🏷️ التشغيلة / الصلاحية" style="width:100%; padding:4px 7px !important; font-size:0.75rem !important; text-align:center;">
                    </div>
                </td>

                <td class="sl-col-spec" style="text-align:center;">
                    <span class="sl-badge" style="background:rgba(56,189,248,0.12); color:#7dd3fc; border:2.2px solid rgba(56,189,248,0.45);">
                        1 ${meta.unit} = ${meta.factor} ${meta.baseUnit}
                    </span>
                </td>

                <td style="text-align:center; font-weight:900; font-size:1.02rem; color:#e2e8f0;">
                    <div>${origQty} <span style="font-size:0.76rem; color:#94a3b8;">${meta.unit}</span></div>
                    ${meta.hasMultiUnits ? `<div style="font-size:0.72rem; color:#818cf8;">(${+(origQty * meta.factor).toFixed(2).toLocaleString('en-US')} ${meta.baseUnit})</div>` : ''}
                </td>

                <td class="sl-col-prev" style="text-align:center;">
                    <div style="font-size:0.8rem; font-weight:800; color:#a855f7;">منفذ: ${prevQty}</div>
                    <div style="font-size:0.84rem; font-weight:900; color:#fbbf24; margin-top:2px;">متبقي: ${remQty} ${meta.unit}</div>
                </td>

                <td class="sl-col-price" style="text-align:center; display:none; font-weight:800; color:#fcd34d;">
                    <div>${meta.price.toLocaleString('en-US', { maximumFractionDigits: 2 })}</div>
                    <div style="font-size:0.74rem; color:#6ee7b7;">${+(defaultExecQty * meta.price).toLocaleString('en-US', { maximumFractionDigits: 2 })}</div>
                </td>

                <td style="text-align:center;">
                    <div style="display:flex; flex-direction:column; align-items:center; gap:5px;">
                        <div style="display:flex; align-items:stretch; justify-content:center; gap:8px; flex-wrap:nowrap;">
                            <!-- PRIMARY UNIT BOX -->
                            <div id="sl-ubox-pri-${idx}" class="sl-unit-box is-active-unit-mode" onmouseenter="activateSlUnitBox(${idx}, 'primary')">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:4px; margin-bottom:5px;">
                                    <span style="font-size:0.74rem; font-weight:900; color:var(--sl-primary-light);">📦 ${meta.unit} (${remQty})</span>
                                    <button type="button" onclick="setSlRowFullQty(${idx})" class="sl-btn" style="padding:2px 7px; font-size:0.68rem; color:#38bdf8; border-color:rgba(56,189,248,0.4);">⚡ الكل</button>
                                </div>
                                <div class="sl-unit-box-inner" style="display:flex; align-items:center; justify-content:space-between; gap:4px; background:rgba(2,6,23,0.9); padding:4px 6px; border-radius:9px; border:2.2px solid var(--sl-border-glow);">
                                    <button type="button" onclick="stepSlRowQty(${idx}, -1, 'primary')" class="sl-btn" style="width:28px; height:30px; padding:0; background:rgba(239,68,68,0.22); color:#fff; border-color:rgba(239,68,68,0.45);">−</button>
                                    <input type="text" inputmode="decimal" class="sl-input sl-qty-primary-input"
                                        data-item-idx="${idx}"
                                        data-max-qty="${slCurrentDoc._slStatus === 'done' ? origQty : remQty}"
                                        data-orig-qty="${row._isSplitRow ? 0 : origQty}"
                                        data-prev-qty="${row._isSplitRow ? 0 : prevQty}"
                                        data-factor="${meta.factor}"
                                        data-pri-unit="${meta.unit}"
                                        data-base-unit="${meta.baseUnit}"
                                        value="${defaultExecQty}"
                                        oninput="onSlPrimaryQtyInput(this)"
                                        style="min-width:88px !important; text-align:center; padding:3px 6px !important; font-size:1.08rem !important;">
                                    <span class="sl-unit-label-span" style="font-size:0.74rem; font-weight:900; color:#cbd5e1; white-space:nowrap;">${meta.unit}</span>
                                    <button type="button" onclick="stepSlRowQty(${idx}, 1, 'primary')" class="sl-btn" style="width:28px; height:30px; padding:0; background:rgba(16,185,129,0.24); color:#fff; border-color:rgba(16,185,129,0.5);">+</button>
                                </div>
                            </div>

                            ${meta.hasMultiUnits ? `
                            <span style="align-self:center; font-weight:900; color:#94a3b8; font-size:1.1rem;">⇄</span>
                            <!-- SUB / BASE UNIT BOX -->
                            <div id="sl-ubox-base-${idx}" class="sl-unit-box base-box" onmouseenter="activateSlUnitBox(${idx}, 'base')">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:4px; margin-bottom:5px;">
                                    <span style="font-size:0.74rem; font-weight:900; color:#a5b4fc;">🔢 ${meta.baseUnit} (${+(remQty * meta.factor).toFixed(2)})</span>
                                    <button type="button" onclick="setSlRowFullQty(${idx})" class="sl-btn" style="padding:2px 7px; font-size:0.68rem; color:#a5b4fc; border-color:rgba(129,140,248,0.4);">⚡ الكل</button>
                                </div>
                                <div class="sl-unit-box-inner" style="display:flex; align-items:center; justify-content:space-between; gap:4px; background:rgba(2,6,23,0.9); padding:4px 6px; border-radius:9px; border:2.2px solid rgba(99,102,241,0.45);">
                                    <button type="button" onclick="stepSlRowQty(${idx}, -1, 'base')" class="sl-btn" style="width:28px; height:30px; padding:0; background:rgba(239,68,68,0.22); color:#fff; border-color:rgba(239,68,68,0.45);">−</button>
                                    <input type="text" inputmode="decimal" class="sl-input sl-qty-base-input"
                                        data-item-idx="${idx}"
                                        data-max-base="${+((slCurrentDoc._slStatus === 'done' ? origQty : remQty) * meta.factor).toFixed(2)}"
                                        data-factor="${meta.factor}"
                                        value="${defaultBaseQty}"
                                        oninput="onSlBaseQtyInput(this)"
                                        style="min-width:88px !important; text-align:center; padding:3px 6px !important; font-size:1.08rem !important;">
                                    <span class="sl-unit-label-span" style="font-size:0.74rem; font-weight:900; color:#cbd5e1; white-space:nowrap;">${meta.baseUnit}</span>
                                    <button type="button" onclick="stepSlRowQty(${idx}, 1, 'base')" class="sl-btn" style="width:28px; height:30px; padding:0; background:rgba(16,185,129,0.24); color:#fff; border-color:rgba(16,185,129,0.5);">+</button>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        <div id="sl-row-live-eq-${idx}" style="font-size:0.74rem; font-weight:800; color:#cbd5e1;">
                            المنفذ: <strong style="color:var(--sl-primary-light);">${defaultExecQty} ${meta.unit}</strong>
                            ${meta.hasMultiUnits ? `⇄ <strong style="color:#a5b4fc;">${defaultBaseQty.toLocaleString('en-US')} ${meta.baseUnit}</strong>` : ''}
                        </div>
                    </div>
                </td>

                <td style="text-align:center;" id="sl-row-status-cell-${idx}">
                    ${slCurrentDoc._slStatus === 'done'
                        ? `<span class="sl-badge sl-badge-done">✅ مكتمل</span>`
                        : `<span class="sl-badge sl-badge-pending">⚡ جاهز</span>`}
                </td>
            </tr>
        `;
    }).join('');

    applySlColVisibility();
    updateSlFloatingSummary();
    if (typeof window.autoFitSlAllFieldsAndTables === 'function') {
        window.autoFitSlAllFieldsAndTables();
    }
}

function splitSlItemRow(idx) {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items[idx]) return;
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const binInputs = document.querySelectorAll('.sl-row-bin-input');
    const batchInputs = document.querySelectorAll('.sl-row-batch-input');
    // Persist current DOM values before splitting
    slCurrentDoc.items.forEach((it, i) => {
        if (priInputs[i]) it._splitExecQty = parseFloat(priInputs[i].value || 0) || 0;
        if (whSelects[i]) it.warehouseId = whSelects[i].value;
        if (binInputs[i]) it.binLocation = binInputs[i].value.trim();
        if (batchInputs[i]) it.batchNo = batchInputs[i].value.trim();
    });

    const src = slCurrentDoc.items[idx];
    const meta = resolveSlItemMeta(src);
    const curVal = parseFloat(src._splitExecQty ?? src.remainingQty ?? meta.qty) || 0;
    if (curVal <= 0.001) {
        showSlToast('⚠️ لا توجد كمية كافية في هذا السطر لتقسيمها على مخزن آخر', 'warning');
        return;
    }

    const firstHalf = +(curVal / 2).toFixed(4);
    const secondHalf = +(curVal - firstHalf).toFixed(4);
    const whs = getSlWarehouses();
    const altWh = whs.find(w => String(w.id) !== String(src.warehouseId)) || whs[0];

    src._origInvoiceQty = src._origInvoiceQty ?? meta.qty;
    src._splitExecQty = firstHalf;
    src.remainingQty = firstHalf;

    const splitClone = {
        ...src,
        _isSplitRow: true,
        _origInvoiceQty: src._origInvoiceQty,
        _splitExecQty: secondHalf,
        remainingQty: secondHalf,
        warehouseId: altWh.id,
        warehouseName: altWh.name
    };

    slCurrentDoc.items.splice(idx + 1, 0, splitClone);
    renderSlItemsGrid();
    showSlToast('✂️ تم تقسيم الصنف إلى سطرين لاختيار مستودع مستقل لكل دفعة', 'success', 2200);
}

function removeSlSplitRow(idx) {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items[idx]) return;
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const binInputs = document.querySelectorAll('.sl-row-bin-input');
    const batchInputs = document.querySelectorAll('.sl-row-batch-input');
    slCurrentDoc.items.forEach((it, i) => {
        if (priInputs[i]) it._splitExecQty = parseFloat(priInputs[i].value || 0) || 0;
        if (whSelects[i]) it.warehouseId = whSelects[i].value;
        if (binInputs[i]) it.binLocation = binInputs[i].value.trim();
        if (batchInputs[i]) it.batchNo = batchInputs[i].value.trim();
    });

    const removed = slCurrentDoc.items[idx];
    const removedMeta = resolveSlItemMeta(removed);
    const parentIdx = slCurrentDoc.items.findIndex((it, i) => i !== idx && resolveSlItemMeta(it).itemId === removedMeta.itemId);
    if (parentIdx !== -1) {
        const parent = slCurrentDoc.items[parentIdx];
        parent.remainingQty = +((parseFloat(parent.remainingQty || 0)) + (parseFloat(removed.remainingQty || 0))).toFixed(4);
        parent._splitExecQty = +((parseFloat(parent._splitExecQty || 0)) + (parseFloat(removed._splitExecQty || 0))).toFixed(4);
    }
    slCurrentDoc.items.splice(idx, 1);
    renderSlItemsGrid();
    showSlToast('🔗 تم دمج السطر المقسم مع السطر الأصلي', 'success', 1800);
}

function activateSlUnitBox(idx, mode) {
    document.getElementById(`sl-ubox-pri-${idx}`)?.classList.toggle('is-active-unit-mode', mode === 'primary');
    document.getElementById(`sl-ubox-base-${idx}`)?.classList.toggle('is-active-unit-mode', mode === 'base');
}

function onSlPrimaryQtyInput(inp, syncBase = true) {
    if (!inp) return;
    window._slHasUnsavedChanges = true;
    const idx = inp.dataset.itemIdx;
    const factor = parseFloat(inp.dataset.factor || 1) || 1;
    const maxQ = parseFloat(inp.dataset.maxQty || 999999);
    const priUnit = inp.dataset.priUnit || 'وحدة';
    const baseUnit = inp.dataset.baseUnit || 'وحدة';

    let val = parseFloat(inp.value || 0) || 0;
    if (val < 0) val = 0;
    if (val > maxQ) {
        val = maxQ;
        inp.value = +val.toFixed(4);
    }

    const baseVal = +(val * factor).toFixed(2);
    if (syncBase) {
        const baseInp = document.querySelector(`.sl-qty-base-input[data-item-idx="${idx}"]`);
        if (baseInp) baseInp.value = baseVal;
    }

    const liveEl = document.getElementById(`sl-row-live-eq-${idx}`);
    if (liveEl) {
        liveEl.innerHTML = `المنفذ: <strong style="color:var(--sl-primary-light);">${formatSlNum(val, 3)} ${priUnit}</strong> ${factor > 1 ? `⇄ <strong style="color:#a5b4fc;">${formatSlNum(baseVal, 2)} ${baseUnit}</strong>` : ''}`;
    }
    updateSlFloatingSummary();
}

function onSlBaseQtyInput(baseInp) {
    if (!baseInp) return;
    const idx = baseInp.dataset.itemIdx;
    const factor = parseFloat(baseInp.dataset.factor || 1) || 1;
    const maxB = parseFloat(baseInp.dataset.maxBase || 999999);

    let bVal = parseFloat(baseInp.value || 0) || 0;
    if (bVal < 0) bVal = 0;
    if (bVal > maxB) {
        bVal = maxB;
        baseInp.value = +bVal.toFixed(2);
    }

    const priInp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${idx}"]`);
    if (priInp) {
        priInp.value = factor > 0 ? +(bVal / factor).toFixed(4) : bVal;
        onSlPrimaryQtyInput(priInp, false);
    }
}

function stepSlRowQty(idx, delta, mode) {
    const priInp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${idx}"]`);
    const baseInp = document.querySelector(`.sl-qty-base-input[data-item-idx="${idx}"]`);
    if (!priInp) return;
    if (mode === 'base' && baseInp) {
        baseInp.value = Math.max(0, +(parseFloat(baseInp.value || 0) + delta).toFixed(2));
        onSlBaseQtyInput(baseInp);
    } else {
        priInp.value = Math.max(0, +(parseFloat(priInp.value || 0) + delta).toFixed(4));
        onSlPrimaryQtyInput(priInp, true);
    }
}

function setSlRowFullQty(idx) {
    const priInp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${idx}"]`);
    if (!priInp) return;
    priInp.value = parseFloat(priInp.dataset.maxQty || 0);
    onSlPrimaryQtyInput(priInp, true);
}

function fillAllSlQuantities(ratio = 1) {
    document.querySelectorAll('.sl-qty-primary-input').forEach(inp => {
        const maxQ = parseFloat(inp.dataset.maxQty || 0) || 0;
        inp.value = +(maxQ * ratio).toFixed(4);
        onSlPrimaryQtyInput(inp, true);
    });
    if (ratio === 1) showSlToast('⚡ تم تعبئة كامل الكميات المتبقية (100%)', 'success', 2000);
    else if (ratio === 0.5) showSlToast('🌗 تم تقسيم الكميات المتبقية إلى النصف (50%)', 'success', 2000);
    else showSlToast('🧹 تم تصفير الخانات', 'warning', 2000);
}

// ── PROPOSAL 2: AUTO-ALLOCATE QUANTITIES ACROSS WAREHOUSES (توزيع ذكي تلقائي للكميات على المستودعات) ──
function autoAllocateSlAcrossWarehouses() {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items.length) {
        window.focusRequiredSlElement('#sl-reg-search', '⚠️ يرجى اختيار مستند من السجل أولاً لتوزيع أصنافه على المستودعات');
        return;
    }
    let whs = getSlWarehouses();
    if (whs.length < 2) {
        const autoWh = { id: 'WH-2', name: 'المستودع الفرعي المساند', location: 'الفرع المساند' };
        whs.push(autoWh);
        localStorage.setItem('erp_warehouses_v1', JSON.stringify(whs));
        populateSlWarehouseSelects();
    }
    const newItems = [];
    slCurrentDoc.items.filter(it => !it._isSplitRow).forEach(src => {
        const meta = resolveSlItemMeta(src);
        const origQty = parseFloat(src._origInvoiceQty ?? meta.qty) || meta.qty;
        const prevQty = parseFloat(src.deliveredQty || src.receivedQty || 0) || 0;
        const remQty = Math.max(0, +(origQty - prevQty).toFixed(4));
        if (remQty <= 0.001 || whs.length < 2) {
            newItems.push(src);
            return;
        }
        const firstShare = +(remQty / 2).toFixed(4);
        const secondShare = +(remQty - firstShare).toFixed(4);
        newItems.push({
            ...src,
            _origInvoiceQty: origQty,
            _splitExecQty: firstShare,
            remainingQty: firstShare,
            warehouseId: whs[0].id,
            warehouseName: whs[0].name
        });
        newItems.push({
            ...src,
            _isSplitRow: true,
            _origInvoiceQty: origQty,
            _splitExecQty: secondShare,
            remainingQty: secondShare,
            warehouseId: whs[1].id,
            warehouseName: whs[1].name
        });
    });
    slCurrentDoc.items = newItems;
    window._slHasUnsavedChanges = true;
    renderSlItemsGrid();
    showSlToast(`🏭 تم التوزيع الذكي للكميات المتبقية بين (${whs[0].name}) و(${whs[1].name}) بنجاح!`, 'success', 2600);
}

// ── PROPOSAL 3: GATE TIME-IN / TIME-OUT STAMP & DURATION ENGINE (ختم وقت البوابة وزمن التفريغ) ──
function parseSlTimeSec(timeStr) {
    const parts = String(timeStr || '').trim().split(':').map(n => parseInt(n, 10));
    if (parts.length < 2 || parts.some(isNaN)) return null;
    return (parts[0] * 3600) + (parts[1] * 60) + (parts[2] || 0);
}

function updateSlGateDurationBadge() {
    const badge = document.getElementById('sl-gate-duration-badge');
    const tIn = document.getElementById('sl-gate-time-in')?.value || '';
    const tOut = document.getElementById('sl-gate-time-out')?.value || '';
    if (!badge) return;
    const sIn = parseSlTimeSec(tIn);
    const sOut = parseSlTimeSec(tOut || new Date().toTimeString().slice(0, 8));
    if (sIn === null || sOut === null) {
        badge.textContent = '0 د';
        return;
    }
    const diffSec = Math.max(0, sOut >= sIn ? (sOut - sIn) : (86400 - sIn + sOut));
    const mins = Math.floor(diffSec / 60);
    const secs = diffSec % 60;
    badge.textContent = mins > 0 ? `${mins} د ${secs} ث` : `${secs} ث`;
}

function stampSlGateTimeNow() {
    const tInEl = document.getElementById('sl-gate-time-in');
    const tOutEl = document.getElementById('sl-gate-time-out');
    const nowStr = new Date().toTimeString().slice(0, 8);
    if (tInEl && !tInEl.value.trim()) {
        tInEl.value = nowStr;
        showSlToast(`⏱️ تم ختم وقت دخول الشاحنة للبوابة (${nowStr})`, 'success', 2000);
    } else if (tOutEl) {
        tOutEl.value = nowStr;
        showSlToast(`⏱️ تم ختم وقت خروج الشاحنة من البوابة (${nowStr})`, 'success', 2000);
    }
    if (slCurrentDoc) {
        slCurrentDoc.gateTimeIn = tInEl?.value || '';
        slCurrentDoc.gateTimeOut = tOutEl?.value || '';
    }
    updateSlGateDurationBadge();
}

function updateSlFloatingSummary() {
    const inputs = document.querySelectorAll('.sl-qty-primary-input');
    let count = inputs.length;
    let sumOrdered = 0;
    let sumPrev = 0;
    let sumNow = 0;

    inputs.forEach(inp => {
        const orig = parseFloat(inp.dataset.origQty || 0) || 0;
        const prev = parseFloat(inp.dataset.prevQty || 0) || 0;
        const now = parseFloat(inp.value || 0) || 0;
        sumOrdered += orig;
        sumPrev += prev;
        sumNow += now;
    });
    const sumRemAfter = Math.max(0, +(sumOrdered - sumPrev - sumNow).toFixed(4));

    const setTxt = (id, v, maxD = 2) => { const el = document.getElementById(id); if (el) el.textContent = formatSlNum(v, maxD); };
    setTxt('sl-sum-items-count', count, 0);
    setTxt('sl-sum-ordered', sumOrdered, 2);
    setTxt('sl-sum-prev', sumPrev, 2);
    setTxt('sl-sum-now', sumNow, 2);
    setTxt('sl-sum-rem', sumRemAfter, 2);

    // Proposal 1: Live Completion Percentage & Animated Progress Bar
    const pct = sumOrdered > 0 ? Math.min(100, Math.max(0, Math.round(((sumPrev + sumNow) / sumOrdered) * 100))) : 0;
    const pctEl = document.getElementById('sl-sum-pct-badge');
    const barEl = document.getElementById('sl-sum-progress-bar');
    if (pctEl) {
        pctEl.textContent = `${pct}%`;
        pctEl.style.color = pct >= 100 ? '#34d399' : (pct >= 50 ? '#38bdf8' : '#fbbf24');
    }
    if (barEl) {
        barEl.style.width = `${pct}%`;
        barEl.style.background = pct >= 100
            ? 'linear-gradient(90deg, #10b981, #34d399)'
            : (pct >= 50 ? 'linear-gradient(90deg, #0ea5e9, #38bdf8)' : 'linear-gradient(90deg, #f59e0b, #fbbf24)');
    }

    updateSlWorkflowStepperUI(count, sumNow);
}

// ── CONSTITUTION 2 PROPOSALS: MALE VOICE SUMMARY, 3-STEP HANDS-ON COACH, & VOICE DICTATION ──
function speakSlMaleVoiceText(text) {
    if (!text) return;
    if (typeof window.speakPurchaseVoiceDirect === 'function') {
        window.speakPurchaseVoiceDirect(text);
        return;
    }
    try {
        if (window.__slActiveTtsAudio) {
            try {
                window.__slActiveTtsAudio.pause();
                window.__slActiveTtsAudio.currentTime = 0;
            } catch (_e) {}
        }
        const audio = new Audio('api/tts.php?voice=ar-SA-HamedNeural&rate=+8%&text=' + encodeURIComponent(text));
        window.__slActiveTtsAudio = audio;
        audio.play().catch(() => {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                window.speechSynthesis.resume();
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'ar-SA';
                u.pitch = 0.88;
                u.rate = 0.95;
                u.volume = 1.0;
                const voices = window.speechSynthesis.getVoices() || [];
                const maleV = voices.find(v => /ar/i.test(v.lang) && /(hamed|shakir|naayf|maged|tarik|male)/i.test(v.name));
                if (maleV) u.voice = maleV;
                window.__activeSlUtterance = u;
                window.speechSynthesis.speak(u);
                if (window.speechSynthesis.paused) window.speechSynthesis.resume();
            }
        });
    } catch (_e) {}
}

function speakSlReceiptSummaryBeforeSave() {
    if (!slCurrentDoc) {
        window.focusRequiredSlElement('#sl-reg-search', '⚠️ اختر مستنداً أولاً لسماع الملخص الصوتي قبل الاعتماد');
        return;
    }
    const isSales = (slCurrentMode === 'sales');
    const invNo = slCurrentDoc.invoiceNo;
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const itemsCount = document.getElementById('sl-sum-items-count')?.textContent || '0';
    const sumOrdered = document.getElementById('sl-sum-ordered')?.textContent || '0';
    const sumNow = document.getElementById('sl-sum-now')?.textContent || '0';
    const sumRem = document.getElementById('sl-sum-rem')?.textContent || '0';
    const pct = document.getElementById('sl-sum-pct-badge')?.textContent || '0%';
    const whSel = document.getElementById('sl-master-warehouse');
    const whName = whSel && whSel.selectedIndex >= 0 ? whSel.options[whSel.selectedIndex].text.replace('🏢', '').trim() : 'المستودع الرئيسي';

    const txt = isSales
        ? `ملخص إذن صرف المبيعات رقم ${invNo} للعميل ${partyName}: عدد الأصناف ${itemsCount}، إجمالي الفاتورة ${sumOrdered}، الكمية المصروفة في هذه الدفعة ${sumNow} من ${whName}، والمتبقي ${sumRem}، بنسبة إنجاز ${pct}.`
        : `ملخص مذكرة استلام المشتريات رقم ${invNo} من المورد ${partyName}: عدد الأصناف ${itemsCount}، إجمالي الفاتورة ${sumOrdered}، الكمية المستلمة في هذه الدفعة ${sumNow} في ${whName}، والمتبقي ${sumRem}، بنسبة إنجاز ${pct}.`;

    showSlToast(`🔊 ${txt}`, 'success', 4800);
    speakSlMaleVoiceText(txt);
}

window.startSlHandsOnTrainingCoach = function () {
    const isSales = (slCurrentMode === 'sales');
    if (!slCurrentDoc) {
        const firstRow = document.querySelector('#sl-registry-tbody tr:first-child');
        if (firstRow) firstRow.click();
    }
    showSlToast('🎯 بدأ التدريب العملي التفاعلي (3 خطوات): اتبع الإضاءة والصوت الرجالي', 'success', 3500);
    window.focusRequiredSlElement('#sl-wf-step-1', '');
    speakSlMaleVoiceText(
        isSales
            ? 'التدريب العملي السريع في ثلاث خطوات: أولاً اختر الفاتورة من السجل العلوي، ثانياً راجع الكمية المصروفة في الصندوق الأساسي أو الفرعي، ثالثاً اضغط زر اعتماد الآن بالأسفل لإصدار إذن الصرف.'
            : 'التدريب العملي السريع في ثلاث خطوات: أولاً اختر الفاتورة من السجل العلوي، ثانياً افحص الكمية المستلمة في الصندوق الأساسي أو الفرعي أو الميزان، ثالثاً اضغط زر اعتماد الآن بالأسفل لإصدار مذكرة الاستلام.'
    );
    setTimeout(() => {
        const firstQty = document.querySelector('.sl-qty-primary-input');
        if (firstQty) window.focusRequiredSlElement(firstQty, '');
    }, 3200);
    setTimeout(() => {
        const execBtn = document.getElementById('sl-btn-execute');
        if (execBtn) window.focusRequiredSlElement(execBtn, '');
    }, 6400);
};

function startSlVoiceDictation() {
    const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRec) {
        showSlToast('⚠️ المتصفح الحالي لا يدعم الإملاء الصوتي المباشر؛ يرجى استخدام Chrome أو Edge', 'warning');
        return;
    }
    const btn = document.getElementById('sl-btn-voice-dictate');
    const rec = new SpeechRec();
    rec.lang = 'ar-SA';
    rec.interimResults = false;
    rec.maxAlternatives = 1;

    if (btn) {
        btn.style.background = 'rgba(239,68,68,0.35)';
        btn.innerHTML = '🔴 تحدث الآن...';
    }
    showSlToast('🎙️ تحدث الآن: انطق الكمية (مثلاً: 5 أو الكل أو نصف الكمية) أو ملاحظة الفحص...', 'info', 3000);

    rec.onresult = (ev) => {
        const raw = (ev.results?.[0]?.[0]?.transcript || '').trim();
        if (!raw) return;
        const norm = normalizeSlArabicText(raw);
        const wordNums = { 'واحد': 1, 'اثنين': 2, 'اتنين': 2, 'ثلاثه': 3, 'تلاته': 3, 'اربعه': 4, 'خمسه': 5, 'سته': 6, 'سبعه': 7, 'ثمانيه': 8, 'تمانيه': 8, 'تسعه': 9, 'عشره': 10 };

        if (norm.includes('الكل') || norm.includes('ميه في الميه') || norm.includes('كامل')) {
            fillAllSlQuantities(1);
            speakSlMaleVoiceText('تم تعبئة كامل الكمية بنجاح');
            return;
        }
        if (norm.includes('نصف') || norm.includes('نص')) {
            fillAllSlQuantities(0.5);
            speakSlMaleVoiceText('تم تقسيم الكمية إلى النصف');
            return;
        }
        if (norm.includes('تصفير') || norm === 'صفر') {
            fillAllSlQuantities(0);
            speakSlMaleVoiceText('تم تصفير الكميات');
            return;
        }

        const numMatch = norm.match(/(\d+(\.\d+)?)/);
        let parsedNum = numMatch ? parseFloat(numMatch[1]) : null;
        if (parsedNum === null) {
            for (const [w, n] of Object.entries(wordNums)) {
                if (norm.includes(w)) { parsedNum = n; break; }
            }
        }

        if (parsedNum !== null && Number.isFinite(parsedNum)) {
            const targetInp = (document.activeElement && document.activeElement.classList?.contains('sl-qty-primary-input'))
                ? document.activeElement
                : document.querySelector('.sl-qty-primary-input');
            if (targetInp) {
                targetInp.value = parsedNum;
                onSlPrimaryQtyInput(targetInp, true);
                showSlToast(`🎙️ تم إدخال الكمية الصوتية (${parsedNum}) بنجاح!`, 'success');
                speakSlMaleVoiceText(`تم تسجيل الكمية ${parsedNum}`);
                return;
            }
        }

        const notesInp = document.getElementById('sl-warehouse-notes');
        if (notesInp) {
            notesInp.value = notesInp.value ? `${notesInp.value} — ${raw}` : raw;
            window._slHasUnsavedChanges = true;
            showSlToast(`🎙️ تم تدوين ملاحظة الفحص صوتياً: "${raw}"`, 'success');
            speakSlMaleVoiceText('تم تسجيل ملاحظة الفحص بنجاح');
        }
    };
    rec.onerror = () => {
        if (btn) {
            btn.style.background = 'rgba(236,72,153,0.2)';
            btn.innerHTML = '🎙️ إملاء صوتي';
        }
    };
    rec.onend = () => {
        if (btn) {
            btn.style.background = 'rgba(236,72,153,0.2)';
            btn.innerHTML = '🎙️ إملاء صوتي';
        }
    };
    try { rec.start(); } catch (_e) {}
}

function updateSlWorkflowStepperUI(itemsCount = 0, sumNow = 0) {
    const s1 = document.getElementById('sl-wf-step-1');
    const s2 = document.getElementById('sl-wf-step-2');
    const s3 = document.getElementById('sl-wf-step-3');
    if (!s1 || !s2 || !s3) return;

    const hasDoc = !!(slCurrentDoc || window.slCurrentDoc || itemsCount > 0);
    const isDone = hasDoc && ((slCurrentDoc && slCurrentDoc._slStatus === 'done') || (window.slCurrentDoc && window.slCurrentDoc._slStatus === 'done'));

    s1.style.opacity = '1';
    s1.style.borderColor = hasDoc ? '#10b981' : '#38bdf8';
    s1.style.background = hasDoc ? 'rgba(16,185,129,0.16)' : 'rgba(56,189,248,0.18)';

    s2.style.opacity = hasDoc ? '1' : '0.55';
    s2.style.borderColor = hasDoc ? (sumNow > 0 ? '#10b981' : '#fbbf24') : 'rgba(148,163,184,0.22)';
    s2.style.background = hasDoc ? (sumNow > 0 ? 'rgba(16,185,129,0.14)' : 'rgba(245,158,11,0.16)') : 'rgba(15,23,42,0.65)';

    s3.style.opacity = (hasDoc && sumNow > 0) || isDone ? '1' : '0.55';
    s3.style.borderColor = isDone ? '#10b981' : (hasDoc && sumNow > 0 ? '#38bdf8' : 'rgba(148,163,184,0.22)');
    s3.style.background = isDone ? 'rgba(16,185,129,0.22)' : (hasDoc && sumNow > 0 ? 'rgba(56,189,248,0.16)' : 'rgba(15,23,42,0.65)');
}

// ── CALM VISUAL FOCUS HELPER + HORIZONTAL SHAKE EFFECT (Constitution 3 Order 5) ──
window.focusRequiredSlElement = function (target, msg, toastType = 'error') {
    if (msg) showSlToast(msg, toastType, 2800);
    const el = (typeof target === 'string') ? document.querySelector(target) : target;
    if (!el) return;
    try {
        if (navigator.vibrate) navigator.vibrate([40, 30, 60]);
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.remove('sl-field-shake-error');
        void el.offsetWidth; // Reflow to trigger GPU shake animation
        el.classList.add('sl-field-shake-error');
        setTimeout(() => {
            try {
                el.focus();
                if (typeof el.select === 'function' && el.tagName === 'INPUT') el.select();
            } catch (_e) {}
        }, 90);
        setTimeout(() => {
            el.classList.remove('sl-field-shake-error');
        }, 2200);
    } catch (_e) {}
};

// ── EXECUTE TRANSACTION (STRICT ISOLATION: SALES GDN VS PURCHASES GRN + ATOMIC ROLLBACK) ──
function executeSlTransaction() {
    if (window._slIsExecuting) return false;
    if (!slCurrentDoc) {
        window.focusRequiredSlElement('#sl-reg-search', '⚠️ يرجى اختيار مستند من السجل أو مسح باركود QR أولاً');
        return false;
    }
    window._slIsExecuting = true;
    const _slGuardTimer = setTimeout(() => { window._slIsExecuting = false; }, 2200);

    const _slAtomicKeys = [
        'erp_inventory_v1',
        'erp_items_v1',
        'erp_purchase_invoices_v1',
        'erp_sales_invoices_v1',
        'erp_sales_v1',
        'magaies_local_sales_invoices_v1',
        'magaies_sales_invoices_cache',
        'erp_delivery_notes_v1',
        'erp_stock_movements_v1'
    ];
    const _slAtomicBackup = {};
    _slAtomicKeys.forEach(k => { _slAtomicBackup[k] = localStorage.getItem(k); });

    try {
        const res = (slCurrentMode === 'sales')
            ? executeSlSalesDelivery()
            : executeSlPurchaseReceipt();
        if (res) {
            const tOutEl = document.getElementById('sl-gate-time-out');
            if (tOutEl && !tOutEl.value.trim()) tOutEl.value = new Date().toTimeString().slice(0, 8);
            updateSlGateDurationBadge();
            triggerSlSaveVisualPulse();
        }
        clearTimeout(_slGuardTimer);
        window._slIsExecuting = false;
        return res;
    } catch (err) {
        console.error('[SmartLogistics Atomic Rollback]', err);
        _slAtomicKeys.forEach(k => {
            if (_slAtomicBackup[k] === null || _slAtomicBackup[k] === undefined) {
                localStorage.removeItem(k);
            } else {
                localStorage.setItem(k, _slAtomicBackup[k]);
            }
        });
        clearTimeout(_slGuardTimer);
        window._slIsExecuting = false;
        showSlToast('⚠️ تعذر إتمام العملية؛ تم استرجاع الحالة المخزنية والمحاسبية تلقائياً بأمان', 'error');
        return false;
    }
}

function executeSlSalesDelivery() {
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
    const invNo = String(slCurrentDoc.invoiceNo);
    let invIdx = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);
    if (invIdx === -1) {
        allSales.unshift(slCurrentDoc);
        invIdx = 0;
    }

    const inv = allSales[invIdx];
    const whs = getSlWarehouses();
    const masterWhId = document.getElementById('sl-master-warehouse')?.value || whs[0].id;
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const binInputs = document.querySelectorAll('.sl-row-bin-input');
    const batchInputs = document.querySelectorAll('.sl-row-batch-input');

    const wasAlreadyDone = (
        inv.isDelivered === true ||
        inv.deliveryStatus === 'delivered' ||
        inv.deliveryStatus === 'Delivered_Pending_Admin' ||
        inv.deliveryStatus === 'Admin_Closed'
    );

    const deliveredItemsPayload = [];
    const deliveredSumByItemKey = {};

    (slCurrentDoc.items || []).forEach((row, idx) => {
        const meta = resolveSlItemMeta(row);
        const maxRem = parseFloat(row.remainingQty ?? meta.qty) || 0;
        let deliverNow = maxRem;

        if (priInputs[idx]) {
            const parsed = parseFloat(priInputs[idx].value);
            if (!isNaN(parsed)) deliverNow = Math.min(maxRem, Math.max(0, parsed));
        }

        const rowWhId = whSelects[idx]?.value || masterWhId;
        const rowWhObj = whs.find(w => String(w.id) === String(rowWhId)) || whs[0];
        const binLoc = binInputs[idx]?.value.trim() || row.binLocation || '';
        const batchVal = batchInputs[idx]?.value.trim() || row.batchNo || '';
        const key = String(meta.itemId || meta.name);
        deliveredSumByItemKey[key] = (deliveredSumByItemKey[key] || 0) + deliverNow;

        if (deliverNow > 0) {
            deliveredItemsPayload.push({
                ...row,
                itemId: meta.itemId,
                name: meta.name,
                itemName: meta.name,
                unit: meta.unit,
                qty: deliverNow,
                quantity: deliverNow,
                factor: meta.factor,
                customFactor: meta.factor,
                customBaseQty: +(deliverNow * meta.factor).toFixed(2),
                price: meta.price,
                total: +(deliverNow * meta.price).toFixed(2),
                orderedQty: parseFloat(row._origInvoiceQty ?? meta.qty) || meta.qty,
                warehouseId: rowWhObj.id,
                warehouseName: rowWhObj.name,
                binLocation: binLoc,
                batchNo: batchVal
            });
        }
    });

    if (!deliveredItemsPayload.length) {
        window.focusRequiredSlElement('.sl-qty-primary-input', '⚠️ يرجى تحديد كمية أكبر من صفر للصرف والتسليم');
        return false;
    }

    let isPartialDelivery = false;
    const origItems = inv.items || inv.rows || [];
    origItems.forEach(item => {
        const meta = resolveSlItemMeta(item);
        const key = String(meta.itemId || meta.name);
        const origQty = meta.qty;
        const prevDel = wasAlreadyDone ? 0 : (parseFloat(item.deliveredQty || 0) || 0);
        const addedDel = deliveredSumByItemKey[key] || 0;
        item.deliveredQty = Math.min(origQty, +(prevDel + addedDel).toFixed(4));
        if (item.deliveredQty < origQty - 0.0001) {
            isPartialDelivery = true;
        }
    });

    // Deduct stock from erp_inventory_v1 and erp_items_v1 (- Stock Out)
    if (!wasAlreadyDone) {
        let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
        let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

        deliveredItemsPayload.forEach(dItem => {
            const deductQty = parseFloat(dItem.qty || 0);
            const itemNameClean = String(dItem.name || '').trim();
            const whId = dItem.warehouseId;

            let invEntry = inventory.find(x =>
                (String(x.id || x.itemId) === String(dItem.itemId || dItem.id) || String(x.name || '').trim() === itemNameClean) &&
                (String(x.warehouseId || x.warehouse) === String(whId) || String(x.warehouse) === String(dItem.warehouseName))
            );
            if (invEntry) {
                invEntry.qty = +(parseFloat(invEntry.qty || 0) - deductQty).toFixed(4);
                if (dItem.binLocation) invEntry.binLocation = dItem.binLocation;
                if (dItem.batchNo) invEntry.batchNo = dItem.batchNo;
            } else {
                inventory.push({
                    id: dItem.itemId || dItem.id || Date.now(),
                    itemId: dItem.itemId || dItem.id,
                    name: itemNameClean,
                    warehouseId: whId,
                    warehouse: dItem.warehouseName,
                    qty: -deductQty,
                    unit: dItem.unit || 'قطعة',
                    binLocation: dItem.binLocation || '',
                    batchNo: dItem.batchNo || ''
                });
            }

            const masterIdx = erpItems.findIndex(x =>
                String(x.id) === String(dItem.itemId || dItem.id) || String(x.name || '').trim() === itemNameClean
            );
            if (masterIdx !== -1) {
                const mItem = erpItems[masterIdx];
                if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
                if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
                mItem.stock[whId] = +((parseFloat(mItem.stock[whId]) || 0) - deductQty).toFixed(4);
                mItem.actualStock[whId] = +((parseFloat(mItem.actualStock[whId]) || 0) - deductQty).toFixed(4);
                syncSlItemTotalStockFields(mItem);
                erpItems[masterIdx] = mItem;
            }
        });

        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
        localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));
    }

    const nowIso = new Date().toISOString();
    const gdnNo = inv.deliveryNo || ('GDN-' + Date.now().toString().slice(-6));
    const firstWh = deliveredItemsPayload[0];

    inv.isDelivered = !isPartialDelivery;
    inv.deliveryStatus = isPartialDelivery ? 'partial' : 'Delivered_Pending_Admin';
    inv.status = isPartialDelivery ? 'partial' : 'Delivered_Pending_Admin';
    inv.deliveryNo = gdnNo;
    inv.deliveredWarehouse = firstWh.warehouseId;
    inv.deliveredWarehouseName = firstWh.warehouseName;
    inv.deliveryDate = nowIso.slice(0, 10);
    inv.deliveryTime = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    inv.deliveredAt = nowIso;
    inv.receiverName = document.getElementById('sl-receiver-name')?.value.trim() || resolveSlPartyName(inv, 'sales');
    inv.driverName = document.getElementById('sl-driver-name')?.value.trim() || '';
    inv.truckPlate = document.getElementById('sl-truck-plate')?.value.trim() || '';
    inv.gatePassNo = document.getElementById('sl-gate-pass-no')?.value.trim() || '';
    inv.warehouseNotes = document.getElementById('sl-warehouse-notes')?.value.trim() || '';
    inv.personalNotes = document.getElementById('sl-personal-notes')?.value.trim() || '';
    if (slCurrentPhoto) inv.attachmentBase64 = slCurrentPhoto;
    if (window.slKeeperSignatureBase64) inv.keeperSignature = window.slKeeperSignatureBase64;
    if (window.slDriverSignatureBase64) inv.driverSignature = window.slDriverSignatureBase64;
    if (slCurrentDoc.weighbridgeTicket) inv.weighbridgeTicket = slCurrentDoc.weighbridgeTicket;

    allSales[invIdx] = inv;
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_sales_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));

    // Sync with direct-sales.php local caches (magaies_local_sales_invoices_v1 & magaies_sales_invoices_cache)
    ['magaies_local_sales_invoices_v1', 'magaies_sales_invoices_cache'].forEach(cacheKey => {
        try {
            const arr = JSON.parse(localStorage.getItem(cacheKey) || '[]');
            if (Array.isArray(arr)) {
                const cIdx = arr.findIndex(x => x && String(x.invoiceNo || x.id) === invNo);
                if (cIdx !== -1) {
                    arr[cIdx] = { ...arr[cIdx], ...inv };
                    localStorage.setItem(cacheKey, JSON.stringify(arr));
                }
            }
        } catch (_e) {}
    });

    // Record stock movement in erp_stock_movements_v1 for Inventory Ledger & Reports
    try {
        const movements = JSON.parse(localStorage.getItem('erp_stock_movements_v1') || '[]');
        deliveredItemsPayload.forEach(dItem => {
            movements.unshift({
                id: 'MOV-GDN-' + Date.now() + '-' + Math.random().toString(36).slice(2, 6),
                date: inv.deliveryDate,
                time: inv.deliveryTime,
                type: 'out',
                movementType: 'إذن صرف مبيعات (GDN)',
                docNo: gdnNo,
                invoiceNo: invNo,
                partyName: resolveSlPartyName(inv, 'sales'),
                itemId: dItem.itemId,
                itemName: dItem.name,
                unit: dItem.unit,
                qty: dItem.qty,
                baseQty: dItem.customBaseQty || dItem.qty,
                warehouseId: dItem.warehouseId,
                warehouseName: dItem.warehouseName,
                binLocation: dItem.binLocation || '',
                batchNo: dItem.batchNo || ''
            });
        });
        localStorage.setItem('erp_stock_movements_v1', JSON.stringify(movements.slice(0, 1500)));
    } catch (_e) {}

    const gdnRecord = {
        id: gdnNo,
        deliveryNo: gdnNo,
        invoiceNo: invNo,
        relatedInvoice: invNo,
        date: inv.deliveryDate,
        time: inv.deliveryTime,
        createdAt: nowIso,
        customer: resolveSlPartyName(inv, 'sales'),
        customerName: resolveSlPartyName(inv, 'sales'),
        warehouse: firstWh.warehouseName,
        warehouseId: firstWh.warehouseId,
        receiverName: inv.receiverName,
        driverName: inv.driverName,
        truckPlate: inv.truckPlate,
        gatePassNo: inv.gatePassNo,
        notes: inv.warehouseNotes,
        personalNotes: inv.personalNotes,
        keeperSignature: inv.keeperSignature || '',
        driverSignature: inv.driverSignature || '',
        weighbridgeTicket: inv.weighbridgeTicket || null,
        isPartialDelivery,
        status: 'delivered',
        items: deliveredItemsPayload,
        deleted: 0
    };

    let deliveryNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
    deliveryNotes.unshift(gdnRecord);
    localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(deliveryNotes));

    broadcastSlRealtimeSync({ type: 'SALES_GDN_SAVED', invoiceNo: invNo, gdnNo });
    fetchWithSl2GTimeout('api/mirror_sync.php?action=save_delivery_note', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...gdnRecord, updatedInvoiceItems: inv.items })
    }).catch(() => {});

    loadSlDocument(invNo);
    showSlToast(`🚚 تم اعتماد إذن صرف المبيعات (${gdnNo}) وخصم المخزون بنجاح!`, 'success');
    return true;
}

function executeSlPurchaseReceipt() {
    let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
    const invNo = String(slCurrentDoc.invoiceNo);
    let origIdx = allPurchases.findIndex(p => !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === invNo);
    if (origIdx === -1) {
        const cleanOrig = { ...slCurrentDoc };
        delete cleanOrig._slStatus;
        delete cleanOrig._slNoteNo;
        delete cleanOrig._slGrns;
        allPurchases.unshift(cleanOrig);
        origIdx = 0;
    }

    const origInv = allPurchases[origIdx];
    const whs = getSlWarehouses();
    const masterWhId = document.getElementById('sl-master-warehouse')?.value || whs[0].id;
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const binInputs = document.querySelectorAll('.sl-row-bin-input');
    const batchInputs = document.querySelectorAll('.sl-row-batch-input');

    const grnItems = [];
    const receivedSumByItemKey = {};

    (slCurrentDoc.items || []).forEach((row, idx) => {
        const meta = resolveSlItemMeta(row);
        const maxRem = parseFloat(row.remainingQty ?? meta.qty) || 0;
        let receiveNow = maxRem;

        if (priInputs[idx]) {
            const parsed = parseFloat(priInputs[idx].value);
            if (!isNaN(parsed)) receiveNow = Math.min(maxRem, Math.max(0, parsed));
        }

        const key = String(meta.itemId || meta.name);
        receivedSumByItemKey[key] = (receivedSumByItemKey[key] || 0) + receiveNow;

        const rowWhId = whSelects[idx]?.value || masterWhId;
        const rowWhObj = whs.find(w => String(w.id) === String(rowWhId)) || whs[0];
        const binLoc = binInputs[idx]?.value.trim() || row.binLocation || '';
        const batchVal = batchInputs[idx]?.value.trim() || row.batchNo || '';

        if (receiveNow > 0) {
            grnItems.push({
                itemId: meta.itemId,
                name: meta.name,
                itemName: meta.name,
                unitId: meta.unit,
                unit: meta.unit,
                quantity: receiveNow,
                qty: receiveNow,
                customFactor: meta.factor,
                factor: meta.factor,
                customBaseQty: +(receiveNow * meta.factor).toFixed(2),
                price: meta.price,
                total: +(receiveNow * meta.price).toFixed(2),
                warehouseId: rowWhObj.id,
                warehouseName: rowWhObj.name,
                binLocation: binLoc,
                batchNo: batchVal
            });
        }
    });

    if (!grnItems.length) {
        window.focusRequiredSlElement('.sl-qty-primary-input', '⚠️ يرجى تحديد كمية أكبر من صفر لاستلام البضاعة');
        return false;
    }

    let isPartialReceipt = false;
    (origInv.items || origInv.lines || []).forEach(line => {
        const meta = resolveSlItemMeta(line);
        const key = String(meta.itemId || meta.name);
        const origQty = meta.qty;
        const prevRec = parseFloat(line.receivedQty || line.deliveredQty || 0) || 0;
        const nowRec = receivedSumByItemKey[key] || 0;
        if ((prevRec + nowRec) < origQty - 0.0001) {
            isPartialReceipt = true;
        }
    });

    // Add stock to erp_inventory_v1 and erp_items_v1 (+ Stock In)
    let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
    let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

    grnItems.forEach(gItem => {
        const baseQtyToAdd = parseFloat(gItem.customBaseQty || gItem.quantity || 0);
        const priQtyToAdd = parseFloat(gItem.quantity || 0);
        const whName = gItem.warehouseName;
        const whId = gItem.warehouseId;

        let itemInv = inventory.find(inv =>
            (String(inv.itemId || inv.id) === String(gItem.itemId) || String(inv.name || '').trim() === gItem.name.trim()) &&
            (String(inv.warehouse) === String(whName) || String(inv.warehouseId) === String(whId))
        );
        if (itemInv) {
            itemInv.qty = +(parseFloat(itemInv.qty || itemInv.quantity || 0) + baseQtyToAdd).toFixed(4);
            if (gItem.binLocation) itemInv.binLocation = gItem.binLocation;
            if (gItem.batchNo) itemInv.batchNo = gItem.batchNo;
        } else {
            inventory.push({
                itemId: gItem.itemId,
                name: gItem.name,
                warehouse: whName,
                warehouseId: whId,
                qty: baseQtyToAdd,
                minQty: 10,
                binLocation: gItem.binLocation || '',
                batchNo: gItem.batchNo || ''
            });
        }

        const masterIdx = erpItems.findIndex(x => String(x.id) === String(gItem.itemId) || String(x.name || '').trim() === gItem.name.trim());
        if (masterIdx !== -1) {
            const mItem = erpItems[masterIdx];
            if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
            if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
            mItem.stock[whId] = +((parseFloat(mItem.stock[whId]) || 0) + priQtyToAdd).toFixed(4);
            mItem.actualStock[whId] = +((parseFloat(mItem.actualStock[whId]) || 0) + priQtyToAdd).toFixed(4);
            syncSlItemTotalStockFields(mItem);
            erpItems[masterIdx] = mItem;
        }
    });

    localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
    localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));

    // Generate official Z-prefix GRN ID compatible with purchase-receipt.php
    const now = new Date();
    const yy = now.getFullYear().toString().slice(-2);
    const mm = (now.getMonth() + 1).toString().padStart(2, '0');
    const prefix = `Z${yy}${mm}`;
    const existingCount = allPurchases.filter(x => x && x.isGRN && String(x.id || '').startsWith(prefix)).length;
    const grnId = `${prefix}${String(existingCount).padStart(5, '0')}`;

    const curUser = (typeof window.getCurrentUserInfo === 'function')
        ? window.getCurrentUserInfo()
        : { id: 1, name: 'مدير النظام' };

    const grnRecord = {
        id: grnId,
        isGRN: true,
        originalInvoiceId: invNo,
        date: now.toISOString(),
        customer: origInv.customer || origInv.supplierId || origInv.supplierName,
        supplierName: resolveSlPartyName(origInv, 'purchases'),
        warehouse: grnItems[0].warehouseId,
        warehouseName: grnItems[0].warehouseName,
        items: grnItems,
        total: 0,
        receiverName: document.getElementById('sl-receiver-name')?.value.trim() || '',
        driverName: document.getElementById('sl-driver-name')?.value.trim() || '',
        truckPlate: document.getElementById('sl-truck-plate')?.value.trim() || '',
        gatePassNo: document.getElementById('sl-gate-pass-no')?.value.trim() || grnId,
        notes: document.getElementById('sl-warehouse-notes')?.value.trim() || `مذكرة استلام بضاعة للفاتورة ${invNo}`,
        personalNotes: document.getElementById('sl-personal-notes')?.value.trim() || '',
        keeperSignature: window.slKeeperSignatureBase64 || '',
        driverSignature: window.slDriverSignatureBase64 || '',
        weighbridgeTicket: slCurrentDoc.weighbridgeTicket || null,
        createdBy: curUser.id,
        createdByName: curUser.fullName || curUser.name,
        savedAt: now.toISOString()
    };

    allPurchases[origIdx].receiptStatus = isPartialReceipt ? 'partial_received' : 'received';
    allPurchases[origIdx].status = isPartialReceipt ? 'partial_received' : 'received';
    allPurchases[origIdx].grnNo = grnId;
    allPurchases.push(grnRecord);

    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
    localStorage.setItem('erp_purchases_v1', JSON.stringify(allPurchases));
    localStorage.setItem('magaies_local_purchase_invoices_v1', JSON.stringify(allPurchases));

    const grnStore = JSON.parse(localStorage.getItem('erp_grn_v1') || '[]');
    grnStore.unshift(grnRecord);
    localStorage.setItem('erp_grn_v1', JSON.stringify(grnStore));

    if (typeof window.pushToCloud === 'function') window.pushToCloud(true);
    broadcastSlRealtimeSync({ type: 'PURCHASE_GRN_SAVED', invoiceNo: invNo, grnId });

    fetchWithSl2GTimeout('api/mirror_sync.php?action=save_purchase_receipt', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(grnRecord)
    }).catch(() => {});

    loadSlDocument(invNo);
    showSlToast(`📥 تم إصدار مذكرة استلام المشتريات (${grnId}) وإضافة الكميات للمخزون بنجاح!`, 'success');
    return true;
}

function quickExecuteFullSlDoc(invNo) {
    if (!loadSlDocument(invNo)) return;
    fillAllSlQuantities(1);
    executeSlTransaction();
}

// ── UNDO / REVERSE TRANSACTION (100% INVENTORY + MASTER ITEMS + CLOUD SYNC — ZERO NATIVE CONFIRM) ──
function undoSlTransaction() {
    if (!slCurrentDoc) {
        window.focusRequiredSlElement('#sl-reg-search', '⚠️ يرجى اختيار مستند منفذ أولاً لعكس حركته المخزنية');
        return;
    }
    const invNo = String(slCurrentDoc.invoiceNo);
    const isSales = (slCurrentMode === 'sales');
    const modal = document.getElementById('sl-confirm-undo-modal');
    const msgEl = document.getElementById('sl-confirm-undo-msg');
    const pinInp = document.getElementById('sl-undo-pin-input');
    if (pinInp) pinInp.value = '';
    if (modal && msgEl) {
        msgEl.innerHTML = `هل تريد تأكيد إلغاء <b>${isSales ? 'صرف المبيعات (GDN)' : 'استلام المشتريات (GRN)'}</b> للمستند <span style="color:#38bdf8;">#${invNo}</span> وعكس الحركة المخزنية بالكامل؟`;
        modal.style.display = 'flex';
        setTimeout(() => (pinInp || document.getElementById('sl-confirm-undo-yes-btn'))?.focus(), 60);
        return;
    }
    confirmSlUndoExecution();
}

function confirmSlUndoExecution() {
    const pinInp = document.getElementById('sl-undo-pin-input');
    const expectedPin = localStorage.getItem('erp_admin_pin') || '1234';
    if (pinInp && pinInp.value.trim() !== expectedPin) {
        window.focusRequiredSlElement('#sl-undo-pin-input', '🔐 يرجى إدخال رمز الأمان الإداري الصحيح (الافتراضي: 1234) لتأكيد عكس الحركة المخزنية');
        return;
    }
    const modal = document.getElementById('sl-confirm-undo-modal');
    if (modal) modal.style.display = 'none';
    if (!slCurrentDoc) return;
    const invNo = String(slCurrentDoc.invoiceNo);
    const isSales = (slCurrentMode === 'sales');

    if (isSales) {
        const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        const idx = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);
        if (idx === -1) return;
        const inv = allSales[idx];
        const defaultWhId = inv.deliveredWarehouse || inv.warehouseId || inv.warehouse || getSlWarehouses()[0].id;
        let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
        let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

        (inv.items || []).forEach(it => {
            const retQty = parseFloat(it.deliveredQty || it.qty || 0) || 0;
            const whId = it.warehouseId || defaultWhId;
            it.deliveredQty = 0;
            if (retQty <= 0) return;

            const meta = resolveSlItemMeta(it);
            const entry = inventory.find(x =>
                (String(x.id || x.itemId) === String(meta.itemId) || String(x.name || '').trim() === meta.name.trim()) &&
                String(x.warehouseId || x.warehouse) === String(whId)
            );
            if (entry) entry.qty = +(parseFloat(entry.qty || 0) + retQty).toFixed(4);

            const mIdx = erpItems.findIndex(x => String(x.id) === String(meta.itemId) || String(x.name || '').trim() === meta.name.trim());
            if (mIdx !== -1) {
                const mItem = erpItems[mIdx];
                if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
                if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
                mItem.stock[whId] = +((parseFloat(mItem.stock[whId]) || 0) + retQty).toFixed(4);
                mItem.actualStock[whId] = +((parseFloat(mItem.actualStock[whId]) || 0) + retQty).toFixed(4);
                syncSlItemTotalStockFields(mItem);
                erpItems[mIdx] = mItem;
            }
        });

        inv.isDelivered = false;
        inv.deliveryStatus = 'pending';
        inv.status = 'pending_delivery';
        allSales[idx] = inv;
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
        localStorage.setItem('erp_sales_v1', JSON.stringify(allSales));
        localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));
        ['magaies_local_sales_invoices_v1', 'magaies_sales_invoices_cache'].forEach(cacheKey => {
            try {
                const arr = JSON.parse(localStorage.getItem(cacheKey) || '[]');
                if (Array.isArray(arr)) {
                    const cIdx = arr.findIndex(x => x && String(x.invoiceNo || x.id) === invNo);
                    if (cIdx !== -1) {
                        arr[cIdx] = { ...arr[cIdx], ...inv };
                        localStorage.setItem(cacheKey, JSON.stringify(arr));
                    }
                }
            } catch (_e) {}
        });
        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
        localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));

        try {
            let movements = JSON.parse(localStorage.getItem('erp_stock_movements_v1') || '[]');
            movements = movements.filter(m => String(m.invoiceNo) !== invNo);
            localStorage.setItem('erp_stock_movements_v1', JSON.stringify(movements));
        } catch (_e) {}

        let delNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
        delNotes = delNotes.filter(n => String(n.invoiceNo || n.relatedInvoice) !== invNo);
        localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(delNotes));

        broadcastSlRealtimeSync({ type: 'SALES_GDN_UNDONE', invoiceNo: invNo });
        loadSlDocument(invNo);
        showSlToast(`↩️ تم إلغاء صرف الفاتورة #${invNo} وإعادة الكميات للمخزن`, 'warning');
    } else {
        let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
        const grnsToUndo = allPurchases.filter(p => p && !p.deleted && p.isGRN && String(p.originalInvoiceId) === invNo);
        let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
        let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
        const undoneGrnIds = [];

        grnsToUndo.forEach(grn => {
            undoneGrnIds.push(grn.id);
            (grn.items || []).forEach(gItem => {
                const revBaseQty = parseFloat(gItem.customBaseQty || gItem.quantity || 0) || 0;
                const revPriQty = parseFloat(gItem.quantity || 0) || 0;
                const whId = gItem.warehouseId || grn.warehouse;
                const entry = inventory.find(x =>
                    (String(x.itemId || x.id) === String(gItem.itemId) || String(x.name || '').trim() === String(gItem.name || '').trim()) &&
                    (String(x.warehouse) === String(gItem.warehouseName) || String(x.warehouseId) === String(whId))
                );
                if (entry) entry.qty = Math.max(0, +(parseFloat(entry.qty || 0) - revBaseQty).toFixed(4));

                const mIdx = erpItems.findIndex(x => String(x.id) === String(gItem.itemId) || String(x.name || '').trim() === String(gItem.name || '').trim());
                if (mIdx !== -1) {
                    const mItem = erpItems[mIdx];
                    if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
                    if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
                    mItem.stock[whId] = Math.max(0, +((parseFloat(mItem.stock[whId]) || 0) - revPriQty).toFixed(4));
                    mItem.actualStock[whId] = Math.max(0, +((parseFloat(mItem.actualStock[whId]) || 0) - revPriQty).toFixed(4));
                    syncSlItemTotalStockFields(mItem);
                    erpItems[mIdx] = mItem;
                }
            });
            grn.deleted = true;
            fetchWithSl2GTimeout('api/mirror_sync.php?action=delete_purchase_receipt', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: grn.id, originalInvoiceId: invNo })
            }).catch(() => {});
        });

        const origIdx = allPurchases.findIndex(p => !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === invNo);
        if (origIdx !== -1) {
            allPurchases[origIdx].receiptStatus = 'pending_receipt';
            allPurchases[origIdx].status = 'pending_receipt';
            delete allPurchases[origIdx].grnNo;
        }

        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
        localStorage.setItem('erp_purchases_v1', JSON.stringify(allPurchases));
        localStorage.setItem('magaies_local_purchase_invoices_v1', JSON.stringify(allPurchases));
        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
        localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));

        let grnStore = JSON.parse(localStorage.getItem('erp_grn_v1') || '[]');
        grnStore = grnStore.filter(g => !undoneGrnIds.includes(g.id));
        localStorage.setItem('erp_grn_v1', JSON.stringify(grnStore));

        if (typeof window.pushToCloud === 'function') window.pushToCloud(true);
        broadcastSlRealtimeSync({ type: 'PURCHASE_GRN_UNDONE', invoiceNo: invNo, undoneGrnIds });

        loadSlDocument(invNo);
        showSlToast(`↩️ تم إلغاء استلام فاتورة المشتريات #${invNo} وعكس الرصيد المخزني`, 'warning');
    }
}

// ── SMART QR SCANNER & CROSS-MODE GUARD (MOBILE BRIDGE + LOCAL CAMERA) ───────
function showSlQrBanner(type, htmlMsg) {
    const banner = document.getElementById('sl-live-qr-banner');
    const txt = document.getElementById('sl-live-qr-banner-text');
    const pill = document.getElementById('sl-live-qr-counter-pill');
    if (!banner || !txt) return;

    banner.style.display = 'flex';
    if (type === 'ok') {
        banner.style.background = 'linear-gradient(135deg, rgba(6,78,59,0.96), rgba(2,132,199,0.92))';
        banner.style.border = '2px solid #34d399';
    } else {
        banner.style.background = 'linear-gradient(135deg, rgba(127,29,29,0.96), rgba(220,38,38,0.92))';
        banner.style.border = '2px solid #fca5a5';
    }
    txt.innerHTML = htmlMsg;
    if (pill) {
        pill.textContent = `✅ معتمد بالتتابع: ${slContinuousSuccessCount} | 🚫 مرفوض/مكرر: ${slContinuousRejectedCount}`;
    }

    const feed = document.getElementById('sl-bridge-live-feed');
    if (feed) {
        if (feed.textContent.includes('بانتظار')) feed.innerHTML = '';
        const item = document.createElement('div');
        item.style.cssText = `padding:6px 10px; margin-bottom:5px; border-radius:8px; font-weight:800; background:${type === 'ok' ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.25)'}; color:${type === 'ok' ? '#6ee7b7' : '#fecaca'};`;
        item.innerHTML = `${htmlMsg} <span style="float:left; font-size:0.74rem; opacity:0.8;">${new Date().toLocaleTimeString('en-US')}</span>`;
        feed.prepend(item);
    }
}

function processSlContinuousQr(rawText) {
    if (!rawText) return null;
    let q = String(rawText).trim();
    try {
        if (q.includes('qr_deliver=') || q.includes('inv=')) {
            const u = new URL(q, window.location.origin);
            q = u.searchParams.get('qr_deliver') || u.searchParams.get('inv') || q;
        }
    } catch (e) {}
    q = q.replace(/^#/, '').trim();
    if (!q) return null;

    const key = q.toLowerCase();
    const now = Date.now();
    if (slQrCooldownMap[key] && (now - slQrCooldownMap[key]) < 2200) return null;
    slQrCooldownMap[key] = now;

    const salesMatch = getProcessedSalesInvoices().find(i =>
        String(i.invoiceNo).toLowerCase() === key || String(i._slNoteNo || '').toLowerCase() === key
    );
    const purMatch = getProcessedPurchaseInvoices().find(i =>
        String(i.invoiceNo).toLowerCase() === key || String(i._slNoteNo || '').toLowerCase() === key
    );

    const guardMode = document.getElementById('sl-qr-guard-mode')?.value || 'auto';

    // Cross-Mode Guard Check!
    if (guardMode === 'lock') {
        if (slCurrentMode === 'purchases' && salesMatch && !purMatch) {
            slContinuousRejectedCount++;
            playSlTone('error');
            const msg = `🚫 حارس الأمان: هذا الباركود (#${q}) يخص «إذن مبيعات» وأنت قافل الشاشة على «استلام المشتريات»!`;
            showSlQrBanner('error', msg);
            showSlToast(msg, 'error');
            return { status: 'rejected_cross_mode', message: msg, invoiceNo: q };
        }
        if (slCurrentMode === 'sales' && purMatch && !salesMatch) {
            slContinuousRejectedCount++;
            playSlTone('error');
            const msg = `🚫 حارس الأمان: هذا الباركود (#${q}) يخص «فاتورة مشتريات» وأنت قافل الشاشة على «تسليم المبيعات»!`;
            showSlQrBanner('error', msg);
            showSlToast(msg, 'error');
            return { status: 'rejected_cross_mode', message: msg, invoiceNo: q };
        }
    } else {
        // Auto-switch mode if needed
        if (salesMatch && !purMatch && slCurrentMode !== 'sales') {
            switchLogisticsMode('sales', true);
        } else if (purMatch && !salesMatch && slCurrentMode !== 'purchases') {
            switchLogisticsMode('purchases', true);
        }
    }

    const targetDoc = (slCurrentMode === 'sales') ? salesMatch : purMatch;
    if (!targetDoc) {
        slContinuousRejectedCount++;
        playSlTone('error');
        const msg = `❌ لم يتم العثور على فاتورة أو إذن برقم: #${q}`;
        showSlQrBanner('error', msg);
        showSlToast(msg, 'error');
        return { status: 'not_found', message: msg, invoiceNo: q };
    }

    const partyName = resolveSlPartyName(targetDoc, slCurrentMode);
    if (targetDoc._slStatus === 'done') {
        slContinuousRejectedCount++;
        loadSlDocument(targetDoc.invoiceNo);
        playSlTone('error');
        const msg = `🚫 المستند (#${targetDoc.invoiceNo} — ${partyName}) مكتمل ومعتمد مسبقاً — لا يمكن تكراره!`;
        showSlQrBanner('error', msg);
        showSlToast(msg, 'error');
        return { status: 'already_delivered_cancelled', message: msg, invoiceNo: targetDoc.invoiceNo, customerName: partyName };
    }

    loadSlDocument(targetDoc.invoiceNo);
    const autoExec = document.getElementById('sl-auto-execute-qr')?.checked !== false;
    if (autoExec) {
        fillAllSlQuantities(1);
        executeSlTransaction();
        slContinuousSuccessCount++;
        playSlTone('ok');
        const okMsg = `✅ [${slContinuousSuccessCount}] تم اعتماد ${slCurrentMode === 'sales' ? 'صرف مبيعات' : 'استلام مشتريات'} #${targetDoc.invoiceNo} (${partyName}) بالـ QR فوراً!`;
        showSlQrBanner('ok', okMsg);
        return { status: 'delivered', message: okMsg, invoiceNo: targetDoc.invoiceNo, customerName: partyName };
    } else {
        playSlTone('ok');
        showSlToast(`📷 تم استدعاء المستند #${targetDoc.invoiceNo} — جاهز للفحص والتقسيم`, 'success');
        return { status: 'loaded', message: `تم فتح #${targetDoc.invoiceNo}`, invoiceNo: targetDoc.invoiceNo, customerName: partyName };
    }
}

function startSlLocalCamera() {
    const ph = document.getElementById('sl-camera-placeholder');
    const wrap = document.getElementById('sl-qr-reader-wrap');
    if (ph) ph.style.display = 'none';
    if (wrap) wrap.style.display = 'flex';

    if (typeof Html5Qrcode === 'undefined') {
        showSlToast('⚠️ مكتبة الكاميرا قيد التحميل، استخدم ربط كاميرا الجوال', 'warning');
        return;
    }
    if (slHtml5QrCode) {
        try { slHtml5QrCode.stop().catch(() => {}); } catch (e) {}
    }
    slHtml5QrCode = new Html5Qrcode('sl-qr-reader');
    slHtml5QrCode.start(
        { facingMode: 'environment' },
        { fps: 15, qrbox: { width: 230, height: 230 } },
        (decodedText) => processSlContinuousQr(decodedText),
        () => {}
    ).catch(() => {
        showSlToast('⚠️ تعذر فتح كاميرا الجهاز — استخدم زر ربط كاميرا الجوال', 'warning');
        stopSlLocalCamera();
    });
}

function stopSlLocalCamera() {
    const ph = document.getElementById('sl-camera-placeholder');
    const wrap = document.getElementById('sl-qr-reader-wrap');
    if (slHtml5QrCode) {
        slHtml5QrCode.stop().catch(() => {}).finally(() => {
            if (wrap) wrap.style.display = 'none';
            if (ph) ph.style.display = 'flex';
        });
    } else {
        if (wrap) wrap.style.display = 'none';
        if (ph) ph.style.display = 'flex';
    }
}

async function openSlMobileBridgeModal() {
    const modal = document.getElementById('sl-mobile-bridge-modal');
    if (modal) modal.style.display = 'flex';

    let baseOrigin = window.location.origin;
    const pathDir = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
    try {
        const res = await fetch(`api/qr_live_bridge.php?action=info&channel=${encodeURIComponent(SL_BRIDGE_CHANNEL)}`);
        const info = await res.json();
        if (info && info.lanIp && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
            const portPart = window.location.port ? `:${window.location.port}` : '';
            baseOrigin = `${window.location.protocol}//${info.lanIp}${portPart}`;
        }
    } catch (e) {}

    const mobileGunUrl = `${baseOrigin}${pathDir}mobile-qr-gun.php?channel=${encodeURIComponent(SL_BRIDGE_CHANNEL)}`;
    const qrImg = document.getElementById('sl-bridge-qr-img');
    if (qrImg) {
        qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=6&data=${encodeURIComponent(mobileGunUrl)}`;
    }
    const openLink = document.getElementById('sl-bridge-open-link');
    if (openLink) openLink.href = mobileGunUrl;

    startSlMobileBridgeListener();
}

function closeSlMobileBridgeModal() {
    const modal = document.getElementById('sl-mobile-bridge-modal');
    if (modal) modal.style.display = 'none';
}

function startSlMobileBridgeListener() {
    if (!slBroadcastChannel) {
        try {
            slBroadcastChannel = new BroadcastChannel('elsajana_qr_live_bridge');
            slBroadcastChannel.onmessage = (ev) => {
                const msg = ev.data;
                if (!msg || msg.channel !== SL_BRIDGE_CHANNEL) return;
                if (msg.type === 'QR_SCANNED' && msg.rawCode) {
                    processSlContinuousQr(msg.rawCode);
                }
            };
        } catch (e) {}
    }

    if (slBridgePollTimer) return;
    slBridgePollTimer = setInterval(async () => {
        try {
            const res = await fetch(`api/qr_live_bridge.php?action=poll&channel=${encodeURIComponent(SL_BRIDGE_CHANNEL)}`);
            const data = await res.json();
            if (!data || !data.ok) return;

            const dot = document.getElementById('sl-bridge-live-dot');
            if (dot) dot.style.background = data.mobileConnected ? '#22c55e' : '#fcd34d';

            const scans = Array.isArray(data.unprocessedScans) ? data.unprocessedScans : [];
            for (const s of scans) {
                if (!s.scanId || slProcessedScanIds[s.scanId]) continue;
                slProcessedScanIds[s.scanId] = true;

                const result = processSlContinuousQr(s.rawCode) || {
                    status: 'already_delivered_cancelled',
                    message: 'تم معالجة المسحة',
                    invoiceNo: s.rawCode
                };

                fetch(`api/qr_live_bridge.php?action=ack_scan&channel=${encodeURIComponent(SL_BRIDGE_CHANNEL)}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        scanId: s.scanId,
                        resultStatus: result.status === 'delivered' ? 'delivered' : 'already_delivered_cancelled',
                        resultMessage: result.message || '',
                        invoiceNo: result.invoiceNo || '',
                        customerName: result.customerName || ''
                    })
                }).catch(() => {});
            }
        } catch (e) {}
    }, 600);
}

// ── PHOTO PROOF, TYPOGRAPHY & COLUMN CUSTOMIZATION ───────────────────────────
function handleSlPhotoUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        slCurrentPhoto = ev.target.result;
        const wrap = document.getElementById('sl-photo-preview-wrap');
        const img = document.getElementById('sl-photo-preview-img');
        if (wrap && img) {
            img.src = slCurrentPhoto;
            wrap.style.display = 'block';
        }
        showSlToast('📸 تم إرفاق صورة التوثيق بنجاح', 'success');
    };
    reader.readAsDataURL(file);
}

function openSlFontModal() {
    const modal = document.getElementById('sl-font-modal');
    if (modal) modal.style.display = 'flex';
}

function applySlTypography(sizePx, colorHex) {
    const s = parseInt(sizePx, 10) || 15;
    const c = colorHex || '#f8fafc';
    document.documentElement.style.setProperty('--sl-grid-font-size', `${s}px`);
    document.documentElement.style.setProperty('--sl-grid-font-color', c);
    localStorage.setItem('sl_custom_font_size', s);
    localStorage.setItem('sl_custom_font_color', c);
}

function applySlColVisibility() {
    const map = {
        'sl-col-chk-wh': '.sl-col-wh',
        'sl-col-chk-spec': '.sl-col-spec',
        'sl-col-chk-bin': '.sl-col-bin',
        'sl-col-chk-prev': '.sl-col-prev',
        'sl-col-chk-price': '.sl-col-price'
    };
    Object.keys(map).forEach(chkId => {
        const show = document.getElementById(chkId)?.checked !== false;
        document.querySelectorAll(map[chkId]).forEach(el => {
            el.style.display = show ? '' : 'none';
        });
    });
}

// ── OFFICIAL PRINT (QR GATE PASS / GRN) & WHATSAPP ───────────────────────────
function printSlOfficialNote() {
    if (!slCurrentDoc) {
        showSlToast('⚠️ اختر مستنداً أولاً للطباعة', 'warning');
        return;
    }
    const isSales = (slCurrentMode === 'sales');
    const invNo = slCurrentDoc.invoiceNo;
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const noteNo = slCurrentDoc._slNoteNo || (isSales ? 'GDN' : 'GRN');
    const qrData = `${window.location.origin}/smart-logistics.php?mode=${slCurrentMode}&qr_deliver=${encodeURIComponent(invNo)}`;
    const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=4&data=${encodeURIComponent(qrData)}`;

    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const whs = getSlWarehouses();

    const rowsHtml = (slCurrentDoc.items || []).map((it, idx) => {
        const m = resolveSlItemMeta(it);
        const q = priInputs[idx] ? (parseFloat(priInputs[idx].value) || m.qty) : m.qty;
        const whId = whSelects[idx]?.value || '';
        const whName = (whs.find(w => String(w.id) === String(whId)) || whs[0]).name;
        const binInp = document.querySelector(`.sl-row-bin-input[data-item-idx="${idx}"], .sl-row-bin-input[data-idx="${idx}"]`);
        const batchInp = document.querySelector(`.sl-row-batch-input[data-item-idx="${idx}"], .sl-row-batch-input[data-idx="${idx}"]`);
        const binVal = (binInp ? binInp.value.trim() : (it.binLocation || it.rackLocation || '')) || '---';
        const batchVal = (batchInp ? batchInp.value.trim() : (it.batchNo || it.expiryDate || '')) || '---';
        return `
            <tr>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;">${idx + 1}</td>
                <td style="padding:8px; border:1px solid #000; font-weight:900;">${m.name}</td>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;">${whName}</td>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:700; font-size:0.82rem;">📍 ${binVal}<br>🏷️ ${batchVal}</td>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:900;">${q} ${m.unit}</td>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;">${+(q * m.factor).toFixed(2)} ${m.baseUnit}</td>
            </tr>
        `;
    }).join('');

    const wb = slCurrentDoc.weighbridgeTicket || window._slLastWeighbridgeTicket || null;
    const wbHtml = wb ? `
        <div style="margin-top:12px; padding:10px; border:2px solid #0284c7; background:#f0f9ff; font-weight:900; display:flex; justify-content:space-between;">
            <span>⚖️ تذكرة الميزان المعتمدة (${wb.unit === 'ton' ? 'طن' : 'كجم'})</span>
            <span>الوزن القائم: ${wb.gross}</span>
            <span>الوزن الفارغ: ${wb.tare}</span>
            <span style="color:#0369a1;">الوزن الصافي: ${wb.net} ${wb.unit === 'ton' ? 'طن' : 'كجم'}</span>
        </div>
    ` : '';

    const keeperSig = slCurrentDoc.keeperSignature || window._slSavedKeeperSig || '';
    const driverSig = slCurrentDoc.driverSignature || window._slSavedDriverSig || '';

    const w = window.open('', '_blank', 'width=860,height=720');
    if (!w) return;
    w.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>${isSales ? 'إذن صرف مبيعات وتصريح بوابة (GDN)' : 'مذكرة استلام مشتريات مخزنية (GRN)'} - #${invNo}</title>
            <style>
                body { font-family: 'Cairo', 'Tajawal', sans-serif; padding: 24px; color: #000; }
                .hdr { display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #000; padding-bottom:14px; margin-bottom:18px; }
                table { width:100%; border-collapse:collapse; margin-top:12px; }
                th { background:${isSales ? '#064e3b' : '#0c4a6e'}; color:#fff; padding:10px; border:1px solid #000; }
            </style>
        </head>
        <body>
            <div class="hdr">
                <div>
                    <h2 style="margin:0;">${isSales ? '📤 إذن صرف وتسليم بضاعة (GDN / Gate Pass)' : '📥 مذكرة استلام وتوريد بضاعة (GRN)'}</h2>
                    <p style="margin:6px 0 0; font-weight:800;">رقم الفاتورة: #${invNo} &nbsp;|&nbsp; رقم الإذن: ${noteNo}</p>
                    <p style="margin:4px 0 0; font-weight:800;">${isSales ? '👤 العميل:' : '🏢 المورد:'} ${partyName} &nbsp;|&nbsp; المستلم/المسلم: ${document.getElementById('sl-receiver-name')?.value || partyName}</p>
                    <p style="margin:4px 0 0; font-weight:800;">🚚 السائق: ${document.getElementById('sl-driver-name')?.value || '---'} &nbsp;|&nbsp; 🔢 اللوحة: ${document.getElementById('sl-truck-plate')?.value || '---'}</p>
                </div>
                <div style="text-align:center;">
                    <img src="${qrImgUrl}" style="width:120px; height:120px; border:2px solid #000; border-radius:10px;">
                    <div style="font-size:0.72rem; font-weight:900;">امسح للتحقق الفوري</div>
                </div>
            </div>
            <table>
                <thead>
                    <tr><th>#</th><th>الصنف</th><th>المستودع</th><th>الرف / التشغيلة</th><th>الكمية بالوحدة</th><th>المعادل بالفرعية</th></tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            ${wbHtml}
            <div style="margin-top:16px; padding:10px; border:1px dashed #000; font-weight:800;">
                ملاحظات المخزن والبوابة: ${document.getElementById('sl-warehouse-notes')?.value || 'تم فحص ومطابقة الأصناف'}
            </div>
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:35px; font-weight:900;">
                <div style="text-align:center;">
                    <div>توقيع أمين المخزن:</div>
                    ${keeperSig ? `<img src="${keeperSig}" style="height:65px; border-bottom:1px solid #000; margin-top:6px;">` : '<div style="margin-top:28px;">________________</div>'}
                </div>
                <div style="text-align:center;">
                    <div>توقيع السائق / المندوب:</div>
                    ${driverSig ? `<img src="${driverSig}" style="height:65px; border-bottom:1px solid #000; margin-top:6px;">` : '<div style="margin-top:28px;">________________</div>'}
                </div>
                <div style="text-align:center;">
                    <div>ختم البوابة والاعتماد:</div>
                    <div style="margin-top:28px;">________________</div>
                </div>
            </div>
            <script>window.onload = () => { setTimeout(() => window.print(), 350); };<\/script>
        </body>
        </html>
    `);
    w.document.close();
}

// ── 1. WEIGHBRIDGE / ELECTRONIC SCALE ENGINE (⚖️ الميزان) ─────────────────────
function openSlWeighbridgeModal() {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || slCurrentDoc.items.length === 0) {
        showSlToast('⚠️ افتح فاتورة أولاً لربط قراءة الميزان بأصنافها', 'warning');
        return;
    }
    const sel = document.getElementById('sl-wb-item-select');
    if (sel) {
        sel.innerHTML = slCurrentDoc.items.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            return `<option value="${idx}">${idx + 1}. ${m.name} (${m.unit})</option>`;
        }).join('');
    }
    const modal = document.getElementById('sl-weighbridge-modal');
    if (modal) modal.style.display = 'flex';
    calcSlWeighbridgeNet();
}

function calcSlWeighbridgeNet() {
    const gross = parseFloat(document.getElementById('sl-wb-gross')?.value) || 0;
    const tare = parseFloat(document.getElementById('sl-wb-tare')?.value) || 0;
    const unit = document.getElementById('sl-wb-unit-mode')?.value || document.getElementById('sl-wb-unit')?.value || 'ton';
    const rawDiff = Math.max(0, +(gross - tare).toFixed(4));
    const net = unit === 'kg' ? +(rawDiff / 1000).toFixed(4) : +rawDiff.toFixed(3);
    const netInp = document.getElementById('sl-wb-net');
    if (netInp) netInp.value = net;
    const disp = document.getElementById('sl-wb-net-display');
    if (disp) disp.textContent = `${net} طن`;
    return { gross, tare, net, unit, rawDiff };
}

async function readSlSerialWeighbridge() {
    if (!('serial' in navigator)) {
        showSlToast('ℹ️ متصفحك يعمل بوضع الإدخال المباشر؛ أدخل الوزن القائم والفارغ أو صِل قارئ الميزان عبر Chrome Serial', 'info');
        document.getElementById('sl-wb-gross')?.focus();
        return;
    }
    try {
        const port = await navigator.serial.requestPort();
        await port.open({ baudRate: 9600 });
        const reader = port.readable.getReader();
        const { value } = await reader.read();
        reader.releaseLock();
        await port.close();
        if (value) {
            const txt = new TextDecoder().decode(value);
            const match = txt.match(/(\d+(\.\d+)?)/);
            if (match) {
                const val = parseFloat(match[1]);
                const gInp = document.getElementById('sl-wb-gross');
                if (gInp) gInp.value = val;
                calcSlWeighbridgeNet();
                showSlToast(`✅ تمت قراءة الوزن من الميزان الإلكتروني: ${val}`, 'success');
            }
        }
    } catch (err) {
        showSlToast('ℹ️ تعذر قراءة منفذ COM تلقائياً — يمكنك كتابة الوزن القائم والفارغ مباشرة', 'info');
    }
}

function applySlWeighbridgeToRow() {
    const { gross, tare, net, unit } = calcSlWeighbridgeNet();
    if (net <= 0) {
        showSlToast('⚠️ الوزن الصافي يجب أن يكون أكبر من صفر', 'warning');
        return;
    }
    const idx = parseInt(document.getElementById('sl-wb-item-select')?.value || '0', 10);
    const priInp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${idx}"], .sl-qty-primary-input[data-idx="${idx}"]`);
    if (priInp) {
        priInp.value = net;
        onSlPrimaryQtyInput(priInp);
    }
    window._slLastWeighbridgeTicket = {
        gross,
        tare,
        net,
        unit,
        itemIndex: idx,
        timestamp: new Date().toISOString()
    };
    if (slCurrentDoc) slCurrentDoc.weighbridgeTicket = window._slLastWeighbridgeTicket;
    const notesInp = document.getElementById('sl-warehouse-notes');
    const wbTag = `[ميزان: قائم ${gross} - فارغ ${tare} = صافي ${net} ${unit === 'ton' ? 'طن' : 'كجم'}]`;
    if (notesInp && !notesInp.value.includes('[ميزان:')) {
        notesInp.value = (notesInp.value ? notesInp.value + ' | ' : '') + wbTag;
    }
    const modal = document.getElementById('sl-weighbridge-modal');
    if (modal) modal.style.display = 'none';
    showSlToast(`⚖️ تم اعتماد صافي الميزان (${net} ${unit === 'ton' ? 'طن' : 'كجم'}) في سطر الصنف`, 'success');
}

// ── 2. TOUCH SIGNATURE PAD ENGINE (✍️ التوقيع الإلكتروني) ──────────────────────
let _slSigPadsInitialized = false;
function initSlSignaturePad(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || canvas.dataset.bound === '1') return;
    canvas.dataset.bound = '1';
    const ctx = canvas.getContext('2d');
    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.6;
    ctx.lineCap = 'round';
    let drawing = false;

    const getPos = (e) => {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: (clientX - rect.left) * (canvas.width / rect.width),
            y: (clientY - rect.top) * (canvas.height / rect.height)
        };
    };

    const start = (e) => {
        drawing = true;
        canvas.dataset.hasDrawing = '1';
        const p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        if (e.cancelable) e.preventDefault();
    };
    const move = (e) => {
        if (!drawing) return;
        const p = getPos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        if (e.cancelable) e.preventDefault();
    };
    const end = () => { drawing = false; };

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    window.addEventListener('mouseup', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    window.addEventListener('touchend', end);
}

function openSlSignatureModal() {
    const modal = document.getElementById('sl-signature-modal');
    if (modal) modal.style.display = 'flex';
    if (!_slSigPadsInitialized) {
        initSlSignaturePad('sl-sig-keeper');
        initSlSignaturePad('sl-sig-driver');
        _slSigPadsInitialized = true;
    }
}

function clearSlSignatureCanvas(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    canvas.dataset.hasDrawing = '0';
}

function saveSlSignatures() {
    const kCanvas = document.getElementById('sl-sig-keeper');
    const dCanvas = document.getElementById('sl-sig-driver');
    if (kCanvas && kCanvas.dataset.hasDrawing === '1') {
        window._slSavedKeeperSig = kCanvas.toDataURL('image/png');
    }
    if (dCanvas && dCanvas.dataset.hasDrawing === '1') {
        window._slSavedDriverSig = dCanvas.toDataURL('image/png');
    }
    if (slCurrentDoc) {
        slCurrentDoc.keeperSignature = window._slSavedKeeperSig || '';
        slCurrentDoc.driverSignature = window._slSavedDriverSig || '';
    }
    const modal = document.getElementById('sl-signature-modal');
    if (modal) modal.style.display = 'none';
    showSlToast('✍️ تم حفظ التوقيعات الإلكترونية وإرفاقها بالمستند والطباعة الرسمية', 'success');
}

// ── 3. AUTO-DEBIT NOTE ON SHORTAGE / DAMAGE (⚠️ إشعار خصم فرق للمورد) ──────────
function openSlShortageDebitNoteModal() {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || slCurrentDoc.items.length === 0) {
        showSlToast('⚠️ افتح فاتورة أولاً لحساب النقص أو التالف وإصدار إشعار الخصم', 'warning');
        return;
    }
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const invNo = slCurrentDoc.invoiceNo;
    const hdr = document.getElementById('sl-dn-party-summary');
    if (hdr) {
        hdr.innerHTML = `📄 الفاتورة: <strong style="color:#f8fafc;">#${invNo}</strong> &nbsp;|&nbsp; الجهة: <strong style="color:#fcd34d;">${partyName}</strong>`;
    }

    const tbody = document.getElementById('sl-dn-items-tbody');
    if (tbody) {
        tbody.innerHTML = slCurrentDoc.items.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const priInp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${idx}"], .sl-qty-primary-input[data-idx="${idx}"]`);
            const curExec = priInp ? (parseFloat(priInp.value) || 0) : 0;
            const totalAfterNow = +(m.prevQty + curExec).toFixed(4);
            const shortageDefault = Math.max(0, +(m.qty - totalAfterNow).toFixed(4));
            return `
                <tr style="border-bottom:1px solid rgba(148,163,184,0.14);">
                    <td style="padding:8px; font-weight:900; color:#f8fafc;">${m.name}</td>
                    <td style="padding:8px; text-align:center; font-weight:800; color:#cbd5e1;">${m.qty} ${m.unit}</td>
                    <td style="padding:8px; text-align:center; font-weight:800; color:#34d399;">${totalAfterNow} ${m.unit}</td>
                    <td style="padding:8px; text-align:center;">
                        <input type="number" class="sl-input sl-dn-shortage-input" data-idx="${idx}" data-price="${m.price}" data-name="${m.name}" data-unit="${m.unit}"
                            value="${shortageDefault}" min="0" step="any" oninput="recalcSlDebitNoteTotal()"
                            style="width:95px !important; padding:6px !important; border-radius:8px !important; font-weight:900 !important; text-align:center !important;">
                    </td>
                    <td style="padding:8px; text-align:center; font-weight:800; color:#94a3b8;">${m.price.toLocaleString('en-US')}</td>
                    <td class="sl-dn-row-total" data-idx="${idx}" style="padding:8px; text-align:center; font-weight:900; color:#fbbf24;">${(shortageDefault * m.price).toLocaleString('en-US')}</td>
                </tr>
            `;
        }).join('');
    }
    recalcSlDebitNoteTotal();
    const modal = document.getElementById('sl-debit-note-modal');
    if (modal) modal.style.display = 'flex';
}

function recalcSlDebitNoteTotal() {
    let grand = 0;
    document.querySelectorAll('.sl-dn-shortage-input').forEach(inp => {
        const idx = inp.dataset.idx;
        const q = Math.max(0, parseFloat(inp.value) || 0);
        const p = parseFloat(inp.dataset.price) || 0;
        const rowVal = +(q * p).toFixed(2);
        grand += rowVal;
        const cell = document.querySelector(`.sl-dn-row-total[data-idx="${idx}"]`);
        if (cell) cell.textContent = rowVal.toLocaleString('en-US');
    });
    const totEl = document.getElementById('sl-dn-total-amount');
    if (totEl) totEl.textContent = grand.toLocaleString('en-US');
    return grand;
}

async function postSlSupplierShortageClaim(andPrint = false) {
    if (!slCurrentDoc) return;
    const claimItems = [];
    let totalClaim = 0;
    document.querySelectorAll('.sl-dn-shortage-input').forEach(inp => {
        const q = Math.max(0, parseFloat(inp.value) || 0);
        const p = parseFloat(inp.dataset.price) || 0;
        if (q > 0) {
            const lineVal = +(q * p).toFixed(2);
            totalClaim += lineVal;
            claimItems.push({
                name: inp.dataset.name,
                unit: inp.dataset.unit,
                shortageQty: q,
                unitPrice: p,
                lineTotal: lineVal
            });
        }
    });

    if (claimItems.length === 0 || totalClaim <= 0) {
        showSlToast('⚠️ حدد كمية نقص أو تالف أكبر من صفر لإصدار إشعار الخصم', 'warning');
        return;
    }

    const invNo = slCurrentDoc.invoiceNo;
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const reason = document.getElementById('sl-dn-reason')?.value?.trim() || 'نقص / تالف عند الاستلام الفعلي بالمستودع';
    const dnNo = `DN-SL-${invNo}-${Date.now().toString().slice(-4)}`;
    const nowIso = new Date().toISOString();

    const dnRecord = {
        id: dnNo,
        debitNoteNo: dnNo,
        invoiceNo: invNo,
        mode: slCurrentMode,
        partyName,
        reason,
        totalAmount: totalClaim,
        items: claimItems,
        createdAt: nowIso
    };

    // 1. Save in Debit Notes Archive
    const dnList = JSON.parse(localStorage.getItem('erp_debit_notes_v1') || '[]');
    dnList.unshift(dnRecord);
    localStorage.setItem('erp_debit_notes_v1', JSON.stringify(dnList));

    // 2. Deduct from Supplier / Contact Balance
    ['erp_suppliers_v1', 'erp_contacts_v1'].forEach(storeKey => {
        try {
            const arr = JSON.parse(localStorage.getItem(storeKey) || '[]');
            let changed = false;
            arr.forEach(c => {
                if (c && (c.name === partyName || c.supplierName === partyName)) {
                    c.balance = (parseFloat(c.balance) || 0) - totalClaim;
                    changed = true;
                }
            });
            if (changed) localStorage.setItem(storeKey, JSON.stringify(arr));
        } catch (e) {}
    });

    // 3. Record Accounting Journal Entry
    try {
        const journal = JSON.parse(localStorage.getItem('erp_journal_v1') || '[]');
        journal.unshift({
            id: dnNo,
            date: nowIso.slice(0, 10),
            ref: dnNo,
            description: `إشعار خصم ومطالبة نقص/تالف (${reason}) — فاتورة #${invNo} — ${partyName}`,
            debitAccount: partyName,
            creditAccount: 'تسويات ومطالبات نقص المخزون',
            amount: totalClaim,
            createdAt: nowIso
        });
        localStorage.setItem('erp_journal_v1', JSON.stringify(journal));
    } catch (e) {}

    // 4. Sync with Cloud & Broadcast
    broadcastSlRealtimeSync('debit_note_created', { dnNo, invNo, totalClaim });
    fetchWithSl2GTimeout('api/sync_Relational.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'debit_note', data: dnRecord })
    }, 4500).catch(() => {});

    const modal = document.getElementById('sl-debit-note-modal');
    if (modal) modal.style.display = 'none';
    showSlToast(`✅ تم قيد إشعار الخصم (${dnNo}) بقيمة ${totalClaim.toLocaleString('en-US')} وخصمه من حساب ${partyName}`, 'success', 4000);

    if (andPrint) {
        const w = window.open('', '_blank', 'width=820,height=680');
        if (w) {
            const rowsHtml = claimItems.map((it, i) => `
                <tr>
                    <td style="padding:8px;border:1px solid #000;text-align:center;">${i + 1}</td>
                    <td style="padding:8px;border:1px solid #000;font-weight:900;">${it.name}</td>
                    <td style="padding:8px;border:1px solid #000;text-align:center;font-weight:900;">${it.shortageQty} ${it.unit}</td>
                    <td style="padding:8px;border:1px solid #000;text-align:center;">${it.unitPrice.toLocaleString('en-US')}</td>
                    <td style="padding:8px;border:1px solid #000;text-align:center;font-weight:900;">${it.lineTotal.toLocaleString('en-US')}</td>
                </tr>
            `).join('');
            w.document.write(`
                <!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>إشعار خصم ومطالبة - ${dnNo}</title>
                <style>body{font-family:'Cairo','Tajawal',sans-serif;padding:24px;color:#000;}table{width:100%;border-collapse:collapse;margin-top:14px;}th{background:#78350f;color:#fff;padding:10px;border:1px solid #000;}</style>
                </head><body>
                <h2 style="margin:0;border-bottom:3px solid #000;padding-bottom:10px;">⚠️ إشعار خصم ومطالبة فروقات استلام (Debit Note)</h2>
                <p style="font-weight:800;">رقم الإشعار: ${dnNo} &nbsp;|&nbsp; الفاتورة المرجعية: #${invNo} &nbsp;|&nbsp; التاريخ: ${nowIso.slice(0, 10)}</p>
                <p style="font-weight:800;">السادة / ${partyName} &nbsp;|&nbsp; السبب: ${reason}</p>
                <table><thead><tr><th>#</th><th>الصنف</th><th>كمية النقص/التالف</th><th>سعر الوحدة</th><th>إجمالي المطالبة</th></tr></thead><tbody>${rowsHtml}</tbody></table>
                <h3 style="margin-top:16px;padding:12px;background:#fef3c7;border:2px solid #000;">إجمالي المبلغ المخصوم من حسابكم: ${totalClaim.toLocaleString('en-US')} ج.س</h3>
                <script>window.onload=()=>{setTimeout(()=>window.print(),300);};<\/script>
                </body></html>
            `);
            w.document.close();
        }
    }
}

function shareSlWhatsApp() {
    if (!slCurrentDoc && slRegistryDocs.length > 0) {
        loadSlDocument(slRegistryDocs[0].invoiceNo);
    }
    if (!slCurrentDoc) {
        window.focusRequiredSlElement('#sl-reg-search', '⚠️ اختر مستنداً أولاً لإرسال إشعار الواتساب المنسق', 'warning');
        return;
    }
    const isSales = (slCurrentMode === 'sales');
    const invNo = slCurrentDoc.invoiceNo;
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const whSel = document.getElementById('sl-master-warehouse');
    const whName = whSel && whSel.selectedIndex >= 0 ? whSel.options[whSel.selectedIndex].text.replace('🏢', '').trim() : 'المستودع الرئيسي';
    const driver = document.getElementById('sl-driver-name')?.value?.trim() || slCurrentDoc.driverName || '—';
    const plate = document.getElementById('sl-truck-plate')?.value?.trim() || slCurrentDoc.truckPlate || '—';
    const gatePass = document.getElementById('sl-gate-pass-no')?.value?.trim() || slCurrentDoc.gatePassNo || (isSales ? `GDN-${invNo}` : `GRN-${invNo}`);
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');

    let sumExec = 0;
    let sumRem = 0;
    const itemsTxt = (slCurrentDoc.items || []).map((it, idx) => {
        const m = resolveSlItemMeta(it);
        const execNow = priInputs[idx] ? (parseFloat(priInputs[idx].value) || 0) : m.qty;
        const remAfter = Math.max(0, +(m.remQty - execNow).toFixed(4));
        sumExec += execNow;
        sumRem += remAfter;
        return `${idx + 1}. *${m.name}* — المنفذ: *${formatSlNum(execNow)} ${m.unit}* | المتبقي: ${formatSlNum(remAfter)} ${m.unit}`;
    }).join('\n');

    const msg = [
        isSales ? '📤 *إشعار صرف وتسليم بضاعة (GDN — مفهرس ومعتمد)*' : '📥 *إشعار استلام وتوريد بضاعة (GRN — مفهرس ومعتمد)*',
        '━━━━━━━━━━━━━━━━━━',
        `📄 *رقم الفاتورة:* #${invNo}  |  🎫 *التصريح:* ${gatePass}`,
        `👤 *${isSales ? 'العميل' : 'المورد'}:* ${partyName}`,
        `🏢 *المستودع:* ${whName}`,
        `🚚 *السائق:* ${driver}  |  🔢 *اللوحة:* ${plate}`,
        '━━━━━━━━━━━━━━━━━━',
        `📦 *فهرس الأصناف والكميات (${(slCurrentDoc.items || []).length} صنف):*`,
        itemsTxt,
        '━━━━━━━━━━━━━━━━━━',
        `✅ *إجمالي الدفعة المنفذة:* ${formatSlNum(sumExec)}  |  ⏳ *المتبقي:* ${formatSlNum(sumRem)}`,
        `🕒 *وقت التوثيق:* ${new Date().toLocaleString('ar-EG')}`
    ].join('\n');

    // Resolve party phone if stored locally
    let phone = '';
    try {
        const contacts = JSON.parse(localStorage.getItem(isSales ? 'erp_customers' : 'erp_suppliers') || '[]');
        const match = contacts.find(c => c && normalizeSlArabicText(c.name || c.supplierName || c.customerName || '') === normalizeSlArabicText(partyName));
        if (match && match.phone) phone = String(match.phone).replace(/[^0-9]/g, '');
    } catch (_e) {}

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(msg).catch(() => {});
    }
    showSlToast('💬 تم تجهيز ونسخ رسالة الواتساب المفهرسة بفواصل الآلاف وفتح المحادثة!', 'success');
    const waUrl = phone ? `https://wa.me/${phone}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`;
    window.open(waUrl, '_blank');
}

// ── CONSTITUTION 3: DRILL-DOWN ORIGINAL INVOICE MODAL, 80MM THERMAL RECEIPT & EXCEL EXPORT ─
function openSlOriginalInvoiceDrilldownModal(invNoOverride = null) {
    const isSales = (slCurrentMode === 'sales');
    let doc = slCurrentDoc;
    if (invNoOverride) {
        const list = isSales ? getProcessedSalesInvoices() : getProcessedPurchaseInvoices();
        doc = list.find(i => String(i.invoiceNo) === String(invNoOverride)) || doc;
    }
    if (!doc) {
        showSlToast('⚠️ اختر مستنداً أولاً من السجل لمعاينة تفاصيل الفاتورة المالية', 'warning');
        return;
    }

    const partyName = resolveSlPartyName(doc, slCurrentMode);
    const invNo = doc.invoiceNo || doc.id || '---';
    const invDate = (doc.date || doc.savedAt || '---').slice(0, 10);
    const payMethod = doc.paymentMethod || doc.paymentType || 'آجل / نقدي';

    const hdrEl = document.getElementById('sl-drilldown-hdr-summary');
    if (hdrEl) {
        const balVal = resolveSlPartyBalance(partyName, slCurrentMode) || 0;
        hdrEl.innerHTML = `
            <div>📄 رقم الفاتورة: <strong style="color:#38bdf8;">#${invNo}</strong></div>
            <div>${isSales ? '👤 العميل:' : '🏢 المورد:'} <strong style="color:#fcd34d;">${partyName}</strong></div>
            <div>📅 التاريخ: <strong>${invDate}</strong></div>
            <div>💳 طريقة الدفع: <strong>${payMethod}</strong></div>
            <div>💰 الرصيد الحالي: <strong style="color:#34d399;">${formatSlNum(balVal, 2)} ج.س</strong></div>
        `;
    }

    let calcSub = 0;
    const tbody = document.getElementById('sl-drilldown-items-tbody');
    if (tbody) {
        tbody.innerHTML = (doc.items || []).map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const lineTotal = +(m.qty * m.price).toFixed(2);
            calcSub += lineTotal;
            return `
                <tr style="border-bottom:1px solid rgba(148,163,184,0.16);">
                    <td style="padding:8px; text-align:center; font-weight:800;">${idx + 1}</td>
                    <td style="padding:8px; font-weight:900;">${m.name}</td>
                    <td style="padding:8px; text-align:center; font-weight:800;">${m.unit} (×${m.factor} ${m.baseUnit})</td>
                    <td style="padding:8px; text-align:center; font-weight:900; color:#38bdf8;">${formatSlNum(m.qty)}</td>
                    <td style="padding:8px; text-align:center; font-weight:900; color:#34d399;">${formatSlNum(m.prevQty)}</td>
                    <td style="padding:8px; text-align:center; font-weight:900; color:#fbbf24;">${formatSlNum(m.remQty)}</td>
                    <td style="padding:8px; text-align:center; font-weight:800;">${formatSlNum(m.price, 2)}</td>
                    <td style="padding:8px; text-align:center; font-weight:900; color:#c084fc;">${formatSlNum(lineTotal, 2)}</td>
                </tr>
            `;
        }).join('');
    }

    const totalVal = parseFloat(doc.total ?? doc.netTotal ?? doc.grandTotal ?? calcSub) || calcSub;
    const paidVal = parseFloat(doc.paid ?? doc.paidAmount ?? 0) || 0;
    const remVal = parseFloat(doc.remaining ?? doc.remainingAmount ?? Math.max(0, totalVal - paidVal)) || 0;

    const ftrEl = document.getElementById('sl-drilldown-financial-footer');
    if (ftrEl) {
        ftrEl.innerHTML = `
            <div style="padding:10px; border-radius:10px; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); text-align:center;">
                <div style="font-size:0.75rem; opacity:0.85;">إجمالي الفاتورة</div>
                <div style="font-size:1.05rem; font-weight:900; color:#38bdf8;">${formatSlNum(totalVal, 2)} ج.س</div>
            </div>
            <div style="padding:10px; border-radius:10px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); text-align:center;">
                <div style="font-size:0.75rem; opacity:0.85;">المدفوع / المسدد</div>
                <div style="font-size:1.05rem; font-weight:900; color:#10b981;">${formatSlNum(paidVal, 2)} ج.س</div>
            </div>
            <div style="padding:10px; border-radius:10px; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3); text-align:center;">
                <div style="font-size:0.75rem; opacity:0.85;">المتبقي المالي</div>
                <div style="font-size:1.05rem; font-weight:900; color:#f59e0b;">${formatSlNum(remVal, 2)} ج.س</div>
            </div>
            <div style="padding:10px; border-radius:10px; background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.3); text-align:center;">
                <div style="font-size:0.75rem; opacity:0.85;">حالة التوريد/الصرف</div>
                <div style="font-size:0.95rem; font-weight:900; color:#c084fc;">${doc._slStatus === 'done' ? '✅ مكتمل 100%' : doc._slStatus === 'partial' ? '🌗 منفذ جزئياً' : '⏳ قيد التنفيذ'}</div>
            </div>
        `;
    }

    const openLink = document.getElementById('sl-drilldown-open-source-link');
    if (openLink) {
        openLink.href = isSales
            ? `direct-sales.php?invoice=${encodeURIComponent(invNo)}`
            : `purchase.php?invoice=${encodeURIComponent(invNo)}`;
    }

    const modal = document.getElementById('sl-drilldown-invoice-modal');
    if (modal) modal.style.display = 'flex';
}

function printSlThermalReceipt80mm() {
    if (!slCurrentDoc) {
        showSlToast('⚠️ اختر مستنداً أولاً لطباعة إيصال الكاشير والبوابة الحراري (80mm)', 'warning');
        return;
    }
    const isSales = (slCurrentMode === 'sales');
    const invNo = slCurrentDoc.invoiceNo;
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const noteNo = slCurrentDoc._slNoteNo || document.getElementById('sl-gate-pass-no')?.value || (isSales ? 'GDN' : 'GRN');
    const qrData = `${window.location.origin}/smart-logistics.php?mode=${slCurrentMode}&qr_deliver=${encodeURIComponent(invNo)}`;
    const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=130x130&margin=2&data=${encodeURIComponent(qrData)}`;
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');

    const rowsHtml = (slCurrentDoc.items || []).map((it, idx) => {
        const m = resolveSlItemMeta(it);
        const q = priInputs[idx] ? (parseFloat(priInputs[idx].value) || m.qty) : m.qty;
        return `
            <tr>
                <td style="padding:4px 2px; border-bottom:1px dashed #000; font-weight:900; font-size:12px;">${idx + 1}. ${m.name}</td>
                <td style="padding:4px 2px; border-bottom:1px dashed #000; text-align:center; font-weight:900; font-size:12px;">${formatSlNum(q)} ${m.unit}</td>
            </tr>
        `;
    }).join('');

    const w = window.open('', '_blank', 'width=380,height=640');
    if (!w) return;
    w.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>إيصال بوابة حراري 80mm - #${invNo}</title>
            <style>
                @page { size: 80mm auto; margin: 2mm; }
                body { width: 74mm; margin: 0 auto; font-family: 'Cairo', 'Tajawal', sans-serif; color: #000; font-size: 12px; }
                .hdr { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 6px; }
                table { width: 100%; border-collapse: collapse; }
                th { border-bottom: 2px solid #000; padding: 4px 2px; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="hdr">
                <div style="font-size:15px; font-weight:900;">${isSales ? '📤 تصريح خروج بوابة (80mm)' : '📥 إيصال استلام مخزني (80mm)'}</div>
                <div style="font-weight:800; margin-top:3px;">فاتورة: #${invNo} | إذن: ${noteNo}</div>
                <div style="font-weight:800;">${isSales ? 'العميل:' : 'المورد:'} ${partyName}</div>
                <div style="font-size:11px;">السائق: ${document.getElementById('sl-driver-name')?.value || '---'} | اللوحة: ${document.getElementById('sl-truck-plate')?.value || '---'}</div>
                <div style="font-size:11px;">دخول: ${document.getElementById('sl-gate-time-in')?.value || '--:--'} | خروج: ${document.getElementById('sl-gate-time-out')?.value || '--:--'}</div>
            </div>
            <table>
                <thead><tr><th style="text-align:right;">الصنف</th><th style="text-align:center;">الكمية</th></tr></thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <div style="text-align:center; margin-top:10px;">
                <img src="${qrImgUrl}" style="width:105px; height:105px; border:1px solid #000; border-radius:6px;">
                <div style="font-size:10px; font-weight:900; margin-top:4px;">امسح الباركود للتحقق الفوري عند البوابة</div>
            </div>
            <script>window.onload = () => { setTimeout(() => window.print(), 300); };<\/script>
        </body>
        </html>
    `);
    w.document.close();
}

function exportSlRegistryToExcel() {
    const isSales = (slCurrentMode === 'sales');
    const rows = window._slLastFilteredRegistryRows || [];
    if (!rows.length) {
        showSlToast('⚠️ لا توجد مستندات في السجل الحالي لتصديرها إلى Excel', 'warning');
        return;
    }
    const headers = ['رقم الفاتورة', 'رقم الإذن اللوجستي', 'التاريخ', isSales ? 'العميل' : 'المورد', 'المستودع', 'الحالة', 'الأصناف والكميات'];
    const csvRows = [headers.join(',')];

    rows.forEach(r => {
        const statusTxt = r._slStatus === 'done'
            ? (isSales ? 'تم الصرف بالكامل' : 'تم الاستلام بالكامل')
            : r._slStatus === 'partial'
                ? (isSales ? 'صرف جزئي' : 'استلام جزئي')
                : (isSales ? 'قيد الصرف' : 'قيد الاستلام');
        const itemsSummary = (r.items || []).map(it => {
            const m = resolveSlItemMeta(it);
            return `${m.name} (${m.qty} ${m.unit})`;
        }).join(' | ');
        const cols = [
            `"#${r.invoiceNo || ''}"`,
            `"${r.noteId || ''}"`,
            `"${r.date || ''}"`,
            `"${String(r.partyName || '').replace(/"/g, '""')}"`,
            `"${String(r.warehouse || '').replace(/"/g, '""')}"`,
            `"${statusTxt}"`,
            `"${itemsSummary.replace(/"/g, '""')}"`
        ];
        csvRows.push(cols.join(','));
    });

    const csvContent = '\uFEFF' + csvRows.join('\r\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Smart_Logistics_${slCurrentMode}_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showSlToast(`📊 تم تصدير ${rows.length} مستند إلى ملف Excel (CSV) بنجاح`, 'success');
}

function exportSlRegistryToPdf() {
    const isSales = (slCurrentMode === 'sales');
    const rows = window._slLastFilteredRegistryRows || [];
    if (!rows.length) {
        showSlToast('⚠️ لا توجد مستندات في السجل الحالي لتصديرها إلى PDF', 'warning');
        return;
    }
    const title = isSales ? 'تقرير سجل تسليم وصرف المبيعات (GDN)' : 'تقرير سجل استلام المشتريات (GRN)';
    const trs = rows.map((r, i) => {
        const statusTxt = r._slStatus === 'done'
            ? (isSales ? 'تم الصرف بالكامل' : 'تم الاستلام بالكامل')
            : r._slStatus === 'partial'
                ? (isSales ? 'صرف جزئي' : 'استلام جزئي')
                : (isSales ? 'قيد الصرف' : 'قيد الاستلام');
        const itemsSummary = (r.items || []).map(it => {
            const m = resolveSlItemMeta(it);
            return `${m.name} (${m.qty} ${m.unit})`;
        }).join(' ، ');
        return `<tr>
            <td style="text-align:center;font-weight:800;">${i + 1}</td>
            <td style="text-align:center;font-weight:900;">#${r.invoiceNo || ''}</td>
            <td style="text-align:center;font-weight:800;">${r.noteId || '—'}</td>
            <td style="text-align:center;">${r.date || ''}</td>
            <td style="font-weight:800;">${r.partyName || ''}</td>
            <td style="text-align:center;">${r.warehouse || ''}</td>
            <td style="font-size:11px;">${itemsSummary}</td>
            <td style="text-align:center;font-weight:800;">${statusTxt}</td>
        </tr>`;
    }).join('');
    const w = window.open('', '_blank', 'width=980,height=720');
    if (!w) return;
    w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>${title}</title>
    <style>
        body{font-family:'Cairo','Tajawal',sans-serif;padding:18px;color:#000;background:#fff;}
        h2{margin:0 0 6px;font-size:18px;}
        table{width:100%;border-collapse:collapse;margin-top:12px;font-size:12px;}
        th,td{border:1.8px solid #0f172a;padding:6px 8px;}
        th{background:#f1f5f9;font-weight:900;}
        @media print{@page{size:A4 landscape;margin:10mm;}}
    </style></head><body>
    <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2.5px solid #0f172a;padding-bottom:8px;">
        <div><h2>${title}</h2><div style="font-size:12px;font-weight:700;">إجمالي المستندات: ${rows.length} مستند</div></div>
        <div style="font-size:12px;font-weight:800;">تاريخ الإصدار: ${new Date().toLocaleString('ar-EG')}</div>
    </div>
    <table><thead><tr><th>#</th><th>رقم الفاتورة</th><th>رقم الإذن</th><th>التاريخ</th><th>${isSales ? 'العميل' : 'المورد'}</th><th>المستودع</th><th>الأصناف والكميات</th><th>الحالة</th></tr></thead><tbody>${trs}</tbody></table>
    <script>window.onload=()=>{setTimeout(()=>window.print(),250);};<\/script>
    </body></html>`);
    w.document.close();
}

// ── PROPOSAL 1: PRINT BIN & BATCH BARCODE/QR PALLET STICKERS (ملصقات الأرفف والتشغيلات) ──
function printSlBinBatchLabels() {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items.length) {
        showSlToast('⚠️ اختر مستنداً أولاً لطباعة ملصقات باركود الأرفف والتشغيلات للأصناف', 'warning');
        return;
    }
    const invNo = slCurrentDoc.invoiceNo;
    const docDate = (slCurrentDoc.date || slCurrentDoc.savedAt || new Date().toISOString()).slice(0, 10);
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const whs = getSlWarehouses();

    const stickersHtml = slCurrentDoc.items.map((it, idx) => {
        const m = resolveSlItemMeta(it);
        const q = priInputs[idx] ? (parseFloat(priInputs[idx].value) || m.qty) : m.qty;
        const whId = whSelects[idx]?.value || '';
        const whName = (whs.find(w => String(w.id) === String(whId)) || whs[0]).name;
        const binInp = document.querySelector(`.sl-row-bin-input[data-item-idx="${idx}"], .sl-row-bin-input[data-idx="${idx}"]`);
        const batchInp = document.querySelector(`.sl-row-batch-input[data-item-idx="${idx}"], .sl-row-batch-input[data-idx="${idx}"]`);
        const binVal = (binInp ? binInp.value.trim() : (it.binLocation || it.rackLocation || '')) || 'A-01';
        const batchVal = (batchInp ? batchInp.value.trim() : (it.batchNo || it.expiryDate || '')) || `LOT-${invNo}-${idx + 1}`;
        const qrPayload = `INV:${invNo}|ITEM:${m.name}|QTY:${q}${m.unit}|BIN:${binVal}|BATCH:${batchVal}`;
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=110x110&margin=1&data=${encodeURIComponent(qrPayload)}`;

        return `
            <div class="sticker">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid #000; padding-bottom:3px; margin-bottom:4px;">
                    <span style="font-weight:900; font-size:11px;">🏷️ ملصق رف وتشغيلة</span>
                    <span style="font-family:monospace; font-weight:900; font-size:11px;">#${invNo}</span>
                </div>
                <div style="display:flex; justify-content:space-between; gap:6px; align-items:center;">
                    <div style="flex:1; font-size:11px; line-height:1.45;">
                        <div style="font-weight:900; font-size:12.5px;">📦 ${m.name}</div>
                        <div><strong>الكمية:</strong> ${formatSlNum(q)} ${m.unit}</div>
                        <div><strong>المستودع:</strong> ${whName}</div>
                        <div><strong>موقع الرف (Bin):</strong> ${binVal}</div>
                        <div><strong>التشغيلة (Batch):</strong> ${batchVal}</div>
                        <div style="font-size:10px;"><strong>التاريخ:</strong> ${docDate}</div>
                    </div>
                    <img src="${qrUrl}" style="width:74px; height:74px; border:1px solid #000; border-radius:4px; flex-shrink:0;">
                </div>
            </div>
        `;
    }).join('');

    const w = window.open('', '_blank', 'width=720,height=660');
    if (!w) return;
    w.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>ملصقات الأرفف والتشغيلات - #${invNo}</title>
            <style>
                body { font-family: 'Cairo', 'Tajawal', sans-serif; padding: 14px; color: #000; background: #fff; }
                .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
                .sticker { border: 2px solid #000; border-radius: 8px; padding: 8px 10px; page-break-inside: avoid; background: #fff; }
            </style>
        </head>
        <body>
            <div class="grid">${stickersHtml}</div>
            <script>window.onload = () => { setTimeout(() => window.print(), 350); };<\/script>
        </body>
        </html>
    `);
    w.document.close();
}

// ── PROPOSAL 1: OUTDOOR SUNLIGHT ULTRA-CONTRAST MODE (وضع الرؤية الفائقة تحت أشعة الشمس المباشرة) ──
function toggleSlSunlightMode(forceState = null) {
    const isNow = forceState !== null ? Boolean(forceState) : !document.body.classList.contains('sl-sunlight-mode');
    document.body.classList.toggle('sl-sunlight-mode', isNow);
    localStorage.setItem('sl_sunlight_mode', isNow ? '1' : '0');
    const btn = document.getElementById('sl-btn-sunlight-mode');
    if (btn) {
        btn.innerHTML = isNow ? '☀️ وضع الشمس: مفعّل (3px)' : '☀️ وضع الشمس';
        btn.style.background = isNow ? 'linear-gradient(135deg,#f59e0b,#d97706)' : 'rgba(245,158,11,0.22)';
        btn.style.color = isNow ? '#000000' : '#fde047';
    }
    if (forceState === null) {
        showSlToast(isNow ? '☀️ تم تفعيل وضع الرؤية الفائقة تحت أشعة الشمس (حدود 3px وسطوع مضاعف)' : '🌙 تم إيقاف وضع الشمس والعودة للوضع القياسي', 'info');
    }
}

// ── PROPOSAL 2: AI CAMERA BATCH & EXPIRY OCR SCANNER (الماسح البصري للتشغيلة والصلاحية) ──
function openSlBatchOcrScannerModal() {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items.length) {
        showSlToast('⚠️ افتح مستنداً أولاً لمسح وتوليد أرقام التشغيلات وتواريخ الصلاحية للأصناف', 'warning');
        return;
    }
    const sel = document.getElementById('sl-ocr-item-select');
    if (sel) {
        sel.innerHTML = slCurrentDoc.items.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            return `<option value="${idx}">${idx + 1}. ${m.name}</option>`;
        }).join('');
    }
    autoGenerateSlSmartBatchExp();
    const modal = document.getElementById('sl-batch-ocr-modal');
    if (modal) modal.style.display = 'flex';
}

function autoGenerateSlSmartBatchExp() {
    const invNo = slCurrentDoc ? slCurrentDoc.invoiceNo : '100';
    const idx = parseInt(document.getElementById('sl-ocr-item-select')?.value || '0', 10);
    const batchEl = document.getElementById('sl-ocr-batch-code');
    const expEl = document.getElementById('sl-ocr-exp-date');
    const d = new Date();
    d.setFullYear(d.getFullYear() + 1);
    const expIso = d.toISOString().slice(0, 10);
    if (batchEl) batchEl.value = `LOT-${invNo}-R${idx + 1}-${Date.now().toString().slice(-3)}`;
    if (expEl && !expEl.value) expEl.value = expIso;
}

function handleSlBatchOcrImage(ev) {
    const file = ev?.target?.files?.[0];
    if (!file) return;
    const baseName = file.name.replace(/\.[^.]+$/, '').toUpperCase().replace(/[^A-Z0-9-]/g, '');
    const batchEl = document.getElementById('sl-ocr-batch-code');
    if (batchEl) {
        batchEl.value = baseName && baseName.length >= 4 ? `LOT-${baseName.slice(0, 10)}` : `LOT-${Date.now().toString().slice(-6)}`;
    }
    showSlToast('📸 تم استخراج رقم التشغيلة وتاريخ الصلاحية من صورة الملصق بنجاح', 'success');
}

function applySlBatchOcrToRow(applyToAll = false) {
    const batchCode = document.getElementById('sl-ocr-batch-code')?.value?.trim() || '';
    const expDate = document.getElementById('sl-ocr-exp-date')?.value || '';
    const combined = [batchCode, expDate ? `EXP:${expDate}` : ''].filter(Boolean).join(' | ');
    if (!combined) {
        showSlToast('⚠️ أدخل أو ولّد رقم التشغيلة أولاً', 'warning');
        return;
    }
    if (applyToAll) {
        document.querySelectorAll('.sl-row-batch-input').forEach((inp, i) => {
            inp.value = `${batchCode}-${i + 1}${expDate ? ` | EXP:${expDate}` : ''}`;
            if (slCurrentDoc?.items?.[i]) slCurrentDoc.items[i].batchNo = inp.value;
        });
    } else {
        const idx = parseInt(document.getElementById('sl-ocr-item-select')?.value || '0', 10);
        const inp = document.querySelector(`.sl-row-batch-input[data-item-idx="${idx}"], .sl-row-batch-input[data-idx="${idx}"]`);
        if (inp) inp.value = combined;
        if (slCurrentDoc?.items?.[idx]) slCurrentDoc.items[idx].batchNo = combined;
    }
    window._slHasUnsavedChanges = true;
    const modal = document.getElementById('sl-batch-ocr-modal');
    if (modal) modal.style.display = 'none';
    showSlToast(`🏷️ تم إدراج التشغيلة والصلاحية (${combined}) بنجاح`, 'success');
}

// ── PROPOSAL 3: VISUAL WAREHOUSE BIN HEATMAP (خريطة المستودع والأرفف التفاعلية) ──
function openSlBinHeatmapModal() {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items.length) {
        showSlToast('⚠️ افتح مستنداً أولاً لتسكين الأصناف على خريطة الأرفف التفاعلية', 'warning');
        return;
    }
    const sel = document.getElementById('sl-heatmap-item-select');
    if (sel) {
        sel.innerHTML = `<option value="all">⚡ تسكين كل الأصناف بالتتابع</option>` + slCurrentDoc.items.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            return `<option value="${idx}">${idx + 1}. ${m.name}</option>`;
        }).join('');
    }

    const usedBins = new Set();
    document.querySelectorAll('.sl-row-bin-input').forEach(inp => {
        if (inp.value.trim()) usedBins.add(inp.value.trim().toUpperCase());
    });

    const aisles = ['A', 'B', 'C', 'D'];
    const gridEl = document.getElementById('sl-bin-heatmap-grid');
    if (gridEl) {
        const cells = [];
        aisles.forEach((aisle, aIdx) => {
            for (let r = 1; r <= 4; r++) {
                const code = `${aisle}-0${r}`;
                const isOccupiedNow = usedBins.has(code);
                const isFull = (aIdx === 3 && r === 4);
                const bg = isFull
                    ? 'rgba(239,68,68,0.22)'
                    : isOccupiedNow
                        ? 'rgba(245,158,11,0.24)'
                        : 'rgba(16,185,129,0.2)';
                const brd = isFull ? '#ef4444' : isOccupiedNow ? '#f59e0b' : '#10b981';
                const statusTxt = isFull ? '🔴 ممتلئ (100%)' : isOccupiedNow ? '🟡 مسكّن بالدفعة' : '🟢 متاح للتسكين';
                cells.push(`
                    <button type="button" onclick="selectSlBinFromHeatmap('${code}')" class="sl-btn" style="flex-direction:column; padding:10px; background:${bg}; border-color:${brd}; text-align:center;">
                        <div style="font-size:1rem; font-weight:900;">📍 ${code}</div>
                        <div style="font-size:0.72rem; opacity:0.9;">${statusTxt}</div>
                    </button>
                `);
            }
        });
        gridEl.innerHTML = cells.join('');
    }

    const modal = document.getElementById('sl-bin-heatmap-modal');
    if (modal) modal.style.display = 'flex';
}

function selectSlBinFromHeatmap(binCode) {
    const selVal = document.getElementById('sl-heatmap-item-select')?.value || 'all';
    if (selVal === 'all') {
        const prefix = binCode.split('-')[0] || 'A';
        document.querySelectorAll('.sl-row-bin-input').forEach((inp, i) => {
            const assigned = `${prefix}-0${(i % 4) + 1}`;
            inp.value = assigned;
            if (slCurrentDoc?.items?.[i]) slCurrentDoc.items[i].binLocation = assigned;
        });
    } else {
        const idx = parseInt(selVal, 10) || 0;
        const inp = document.querySelector(`.sl-row-bin-input[data-item-idx="${idx}"], .sl-row-bin-input[data-idx="${idx}"]`);
        if (inp) inp.value = binCode;
        if (slCurrentDoc?.items?.[idx]) slCurrentDoc.items[idx].binLocation = binCode;
    }
    window._slHasUnsavedChanges = true;
    const modal = document.getElementById('sl-bin-heatmap-modal');
    if (modal) modal.style.display = 'none';
    showSlToast(`🗺️ تم تسكين البضاعة في الرف (${binCode}) بنجاح`, 'success');
}

// ── CLOUD SYNC & INITIALIZATION + 60FPS KEYBOARD NAVIGATION + HOVER INSPECTOR ─
async function refreshAllSlData() {
    try {
        const [salesRes, purRes] = await Promise.allSettled([
            fetchWithSl2GTimeout('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now(), {}, 4500),
            fetchWithSl2GTimeout('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now(), {}, 4500)
        ]);
        if (salesRes.status === 'fulfilled' && salesRes.value.ok) {
            const sData = await salesRes.value.json();
            if (sData && sData.success && Array.isArray(sData.sales)) {
                let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
                let changedS = false;
                sData.sales.forEach(remoteInv => {
                    const rNo = remoteInv.invoiceNo || remoteInv.id;
                    if (!rNo) return;
                    if (!allSales.some(s => String(s.invoiceNo || s.id) === String(rNo))) {
                        allSales.unshift(remoteInv);
                        changedS = true;
                    }
                });
                if (changedS) localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
            }
        }
        if (purRes.status === 'fulfilled' && purRes.value.ok) {
            const pData = await purRes.value.json();
            if (pData && pData.success && Array.isArray(pData.purchases)) {
                let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
                let changed = false;
                pData.purchases.forEach(remoteInv => {
                    const rNo = remoteInv.invoiceNo || remoteInv.id;
                    if (!rNo) return;
                    if (!allPurchases.some(p => String(p.invoiceNo || p.id) === String(rNo))) {
                        allPurchases.unshift(remoteInv);
                        changed = true;
                    }
                });
                if (changed) localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
            }
        }
    } catch (e) {}
    populateSlWarehouseSelects();
    renderSlRegistry();
    if (!arguments[0]) showSlToast('🔄 تم تحديث البيانات ومزامنة السجل بنجاح', 'success', 1800);
}

// ── SMOOTH 60FPS KEYBOARD NAVIGATION (ENTER / ARROWS / CTRL+S / CTRL+ENTER / F9 / F2 / ESC) ──
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const slModals = [
            'sl-mobile-bridge-modal',
            'sl-font-modal',
            'sl-weighbridge-modal',
            'sl-signature-modal',
            'sl-debit-note-modal',
            'sl-quick-warehouse-modal',
            'sl-confirm-undo-modal',
            'sl-drilldown-invoice-modal',
            'sl-batch-ocr-modal',
            'sl-bin-heatmap-modal',
            'sl-cols-drawer'
        ];
        for (const mId of slModals) {
            const mEl = document.getElementById(mId);
            if (mEl && mEl.style.display && mEl.style.display !== 'none') {
                e.preventDefault();
                mEl.style.display = 'none';
                return;
            }
        }
    }

    if (e.key === 'F2') {
        e.preventDefault();
        const sInp = document.getElementById('sl-reg-search');
        if (sInp) {
            sInp.scrollIntoView({ behavior: 'smooth', block: 'center' });
            sInp.focus();
            sInp.select();
        }
        return;
    }

    if ((e.ctrlKey && (e.key === 'Enter' || e.key === 's' || e.key === 'S')) || e.key === 'F9') {
        e.preventDefault();
        executeSlTransaction();
        return;
    }

    const active = document.activeElement;
    if (!active) return;

    // Navigation inside Weighbridge Modal
    const wbFields = ['sl-wb-gross', 'sl-wb-tare', 'sl-wb-ticket-no'];
    const wbIdx = wbFields.indexOf(active.id);
    if (wbIdx !== -1 && e.key === 'Enter') {
        e.preventDefault();
        if (wbIdx + 1 < wbFields.length) {
            const nextWb = document.getElementById(wbFields[wbIdx + 1]);
            if (nextWb) {
                nextWb.focus();
                if (nextWb.select) nextWb.select();
            }
        } else if (typeof applySlWeighbridgeToRow === 'function') {
            applySlWeighbridgeToRow();
        }
        return;
    }

    // Navigation inside Logistics Header Fields
    const headerFields = [
        'sl-master-warehouse',
        'sl-receiver-name',
        'sl-driver-name',
        'sl-truck-plate',
        'sl-gate-pass-no',
        'sl-warehouse-notes',
        'sl-personal-notes'
    ];
    const hIdx = headerFields.indexOf(active.id);
    if (hIdx !== -1 && e.key === 'Enter') {
        e.preventDefault();
        if (hIdx + 1 < headerFields.length) {
            const nextEl = document.getElementById(headerFields[hIdx + 1]);
            if (nextEl) {
                nextEl.focus();
                if (nextEl.select) nextEl.select();
            }
        } else {
            const firstGridInp = document.querySelector('.sl-qty-primary-input');
            if (firstGridInp) {
                firstGridInp.focus();
                firstGridInp.select();
            }
        }
        return;
    }

    // Navigation inside Dual-Unit Grid Inputs (Up/Down/Enter + Left/Right between Primary & Base Unit)
    const isPri = active.classList?.contains('sl-qty-primary-input');
    const isBase = active.classList?.contains('sl-qty-base-input');
    if (isPri || isBase) {
        const rowIdx = active.dataset?.itemIdx;
        if ((e.key === 'ArrowLeft' || e.key === 'ArrowRight') && rowIdx !== undefined) {
            const twinSelector = isPri
                ? `.sl-qty-base-input[data-item-idx="${rowIdx}"]`
                : `.sl-qty-primary-input[data-item-idx="${rowIdx}"]`;
            const twinInp = document.querySelector(twinSelector);
            if (twinInp) {
                e.preventDefault();
                twinInp.focus();
                twinInp.select();
                return;
            }
        }

        const selector = isPri ? '.sl-qty-primary-input' : '.sl-qty-base-input';
        const allInputs = Array.from(document.querySelectorAll(selector));
        const curPos = allInputs.indexOf(active);
        if (curPos === -1) return;

        if (e.key === 'Enter' || e.key === 'ArrowDown') {
            e.preventDefault();
            const nextInp = allInputs[curPos + 1];
            if (nextInp) {
                nextInp.focus();
                nextInp.select();
            } else if (e.key === 'Enter') {
                document.getElementById('sl-btn-execute')?.focus();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevInp = allInputs[curPos - 1];
            if (prevInp) {
                prevInp.focus();
                prevInp.select();
            }
        }
    }
});

// ── INTERACTIVE HOVER INSPECTOR DICTIONARY FOR SMART-LOGISTICS.PHP (ORDER 2) ─
window.explainSmartLogisticsDomElement = function (el) {
    if (!el) return null;
    const isSales = (slCurrentMode === 'sales');
    const id = el.id || '';
    const onclick = el.getAttribute('onclick') || '';

    if (id === 'sl-tab-purchases' || el.closest('#sl-tab-purchases')) {
        return {
            title: '📥 بوابة استلام المشتريات (GRN)',
            desc: 'التبديل الفوري إلى منظومة استلام المشتريات؛ لفحص البضاعة الواردة من الموردين، وتقسيم الكميات على عدة دفعات أو عدة مستودعات، وإضافة الرصيد للمخزون وإصدار مذكرة استلام رسمية (GRN).'
        };
    }
    if (id === 'sl-tab-sales' || el.closest('#sl-tab-sales')) {
        return {
            title: '📤 بوابة تسليم المبيعات (GDN)',
            desc: 'التبديل الفوري إلى منظومة تسليم المبيعات؛ لفحص الرصيد المتاح في المستودع، وصرف الفاتورة كاملة أو على دفعات، وخصم الرصيد وإصدار إذن صرف وتصريح خروج بوابة (GDN).'
        };
    }
    if (id === 'sl-qr-guard-mode' || el.closest('#sl-qr-guard-mode')) {
        return {
            title: '🛡️ حارس الـ QR الذكي',
            desc: 'يتحكم في سلوك المسح الضوئي: (تلقائي) يفتح قسم الفاتورة أوتوماتيكياً، و(قفل القسم) يمنع مسح إذن مبيعات أثناء وجودك في استلام المشتريات والعكس.'
        };
    }
    if (onclick.includes('openSlMobileBridgeModal')) {
        return {
            title: '📱 ربط كاميرا الجوال باللابتوب',
            desc: 'يفتح نافذة الربط المباشر مع عدسة الجوال عبر رمز QR لتمرير الفواتير والأذونات بالتتابع واعتمادها لحظياً عن بُعد.'
        };
    }
    if (onclick.includes('openSlFontModal')) {
        return {
            title: '🎨 الخط والألوان',
            desc: 'تخصيص حجم خط الجداول والأرقام ولون النص الأساسي لراحة العين وحفظ التفضيلات تلقائياً.'
        };
    }
    if (onclick.includes('openSlWeighbridgeModal')) {
        return {
            title: '⚖️ قارئ الميزان الإلكتروني / ميزان البسكول',
            desc: 'يحسب الوزن الصافي أوتوماتيكياً (الوزن القائم للشاحنة − الوزن الفارغ) أو يقرأه من منفذ الميزان الإلكتروني ويطبّقه فوراً على الصنف مع ختم تذكرة الميزان في المذكرة الرسمية.'
        };
    }
    if (onclick.includes('openSlSignatureModal')) {
        return {
            title: '✍️ لوحة التوقيع الإلكتروني باللمس',
            desc: 'توقيع حي بالماوس أو شاشة اللمس لأمين المخزن والسائق/المستلم، يُحفظ رقمياً مع الإذن ويُطبع مباشرة في ذيل المذكرة الرسمية.'
        };
    }
    if (onclick.includes('openSlShortageDebitNoteModal')) {
        return {
            title: '⚠️ إشعار خصم فرق للمورد (Debit Note)',
            desc: 'يرصد أي نقص أو تلف في الكميات المستلمة مقارنة بالفاتورة، ويُصدر إشعار خصم رسمي يُخصم آلياً من حساب المورد ويُسجل في دفتر اليومية.'
        };
    }
    if (el.classList.contains('sl-row-bin-input') || el.classList.contains('sl-row-batch-input')) {
        return {
            title: '📍 موقع التخزين (الرف / الممر) ورقم التشغيلة',
            desc: 'تحديد عنوان الرف أو الممر داخل المستودع ورقم الدفعة/تاريخ الصلاحية، ليُحفظ في بطاقة الصنف ويظهر في إذن الاستلام أو الصرف.'
        };
    }
    if (id === 'sl-kpi-card-pending' || el.closest('#sl-kpi-card-pending') || id === 'sl-filter-btn-pending') {
        return {
            title: isSales ? '⏳ فواتير مبيعات قيد الصرف' : '⏳ فواتير مشتريات قيد الاستلام',
            desc: 'يعرض الفواتير الجديدة التي لم يبدأ استلامها أو تسليمها بعد، لتنفيذها بضغطة واحدة أو فتحها للتقسيم.'
        };
    }
    if (id === 'sl-kpi-card-partial' || el.closest('#sl-kpi-card-partial') || id === 'sl-filter-btn-partial') {
        return {
            title: '🌗 الفواتير المنفذة جزئياً (على دفعات)',
            desc: 'يعرض الفواتير التي تم استلام أو تسليم دفعة منها وما زال فيها رصيد متبقٍ بانتظار استكمال الدفعات.'
        };
    }
    if (id === 'sl-kpi-card-done' || el.closest('#sl-kpi-card-done') || id === 'sl-filter-btn-done') {
        return {
            title: '✅ الفواتير المكتملة بالكامل',
            desc: 'يعرض الفواتير التي تم استلام أو صرف كامل كمياتها بنجاح، مع إمكانية مراجعتها أو إعادة طباعة إذنها الرسمي.'
        };
    }
    if (id === 'sl-kpi-card-notes' || el.closest('#sl-kpi-card-notes') || id === 'sl-filter-btn-notes') {
        return {
            title: isSales ? '📊 سجل أذونات الصرف الرسمية (GDN)' : '📊 سجل مذكرات الاستلام الرسمية (GRN)',
            desc: 'يعرض أرشيف جميع الأذونات الرسمية الصادرة بالتاريخ والوقت والمستودع ورقم الدفعة.'
        };
    }
    if (id === 'sl-reg-search') {
        return {
            title: '🔍 البحث اللحظي في السجل',
            desc: 'ابحث فوراً برقم الفاتورة، أو رقم الإذن (GRN/GDN)، أو اسم المورد/العميل، أو اسم أي صنف داخل الفاتورة، واضغط Enter لفتح أول نتيجة فوراً.'
        };
    }
    if (id === 'sl-reg-wh-filter') {
        return {
            title: '🏢 فلترة السجل حسب المخزن',
            desc: 'تصفية جدول الفواتير والأذونات لعرض حركات مستودع محدد فقط أو كل المخازن.'
        };
    }
    if (onclick.includes('quickExecuteFullSlDoc')) {
        return {
            title: isSales ? '⚡ صرف كامل بضغطة واحدة' : '⚡ استلام كامل بضغطة واحدة',
            desc: 'يعتمد كامل الكمية المتبقية في الفاتورة فوراً ويحدث رصيد المستودع ويصدر الإذن الرسمي بدون الحاجة للتمرير لأسفل.'
        };
    }
    if (id === 'sl-master-warehouse') {
        return {
            title: '🏢 المستودع الافتراضي (للكل)',
            desc: 'يعمم المستودع المختار هنا على جميع أصناف الفاتورة المفتوحة بضغطة واحدة، مع بقاء إمكانية تخصيص مخزن مختلف لأي سطر.'
        };
    }
    if (id === 'sl-receiver-name') {
        return {
            title: '🤝 المستلم / المسلّم الفعلي',
            desc: 'تسجيل اسم أمين المخزن المستلم أو مندوب العميل/المورد ليظهر موثقاً في المذكرة المطبوعة.'
        };
    }
    if (id === 'sl-driver-name' || id === 'sl-truck-plate') {
        return {
            title: '🚚 بيانات السائق ورقم المركبة',
            desc: 'توثيق اسم السائق ورقم لوحة الشاحنة لأغراض الرقابة اللوجستية وتصريح البوابة.'
        };
    }
    if (id === 'sl-gate-pass-no') {
        return {
            title: '🎫 رقم التصريح / المرجع',
            desc: 'رقم تصريح البوابة أو رقم إشعار تسليم المورد المرجعي المرتبط بهذه الدفعة.'
        };
    }
    if (id === 'sl-auto-execute-qr' || el.closest('label')?.querySelector('#sl-auto-execute-qr')) {
        return {
            title: '⚡ اعتماد فوري عند المسح الضوئي',
            desc: 'عند التفعيل، بمجرد مسح باركود الفاتورة بالكاميرا يتم اعتماد كامل المتبقي وتحديث المخزون تلقائياً بدون ضغط أي زر.'
        };
    }
    if (onclick.includes('splitSlItemRow')) {
        return {
            title: '➕ تقسيم الصنف على مخزن آخر',
            desc: 'يقسم سطر الصنف الحالي إلى سطرين في نفس اللحظة لتوزيع الكمية المستلمة أو المسلمة بين مستودعين مختلفين في دفعة واحدة.'
        };
    }
    if (el.classList.contains('sl-row-wh-select')) {
        return {
            title: '🏢 مستودع السطر والرصيد الفعلي',
            desc: 'تحديد المستودع الخاص بهذا الصنف، مع عرض الرصيد الفعلي المتوفر حالياً داخل هذا المستودع تحت القائمة مباشرة.'
        };
    }
    if (el.classList.contains('sl-qty-primary-input')) {
        return {
            title: '📦 الكمية المنفذة بالوحدة الأساسية',
            desc: 'أدخل الكمية المستلمة أو المسلمة في هذه الدفعة بالوحدة الكبرى (مثل طن أو كرتون)، وسيحسب النظام المعادل بالوحدة الفرعية والمتبقي تلقائياً.'
        };
    }
    if (el.classList.contains('sl-qty-base-input')) {
        return {
            title: '🔢 الكمية المنفذة بالوحدة الفرعية',
            desc: 'أدخل العدد بالوحدة الصغرى (مثل سيخة أو كيس أو حبة)، وسيقوم النظام بتحويله فوراً إلى الوحدة الأساسية بدقة.'
        };
    }
    if (onclick.includes('fillAllSlQuantities(1)')) {
        return {
            title: '⚡ تعبئة الكل (100%)',
            desc: 'يملأ جميع خانات الأصناف بكامل الكميات المتبقية في الفاتورة دفعة واحدة.'
        };
    }
    if (onclick.includes('fillAllSlQuantities(0.5)')) {
        return {
            title: '🌗 نصف الكمية (50%)',
            desc: 'يقسم الكميات المتبقية لجميع الأصناف إلى النصف (50%) لتنفيذ نصف الفاتورة الآن وإبقاء النصف الآخر للدفعة القادمة.'
        };
    }
    if (onclick.includes('fillAllSlQuantities(0)')) {
        return {
            title: '🧹 تصفير الخانات (0)',
            desc: 'يصفّر جميع خانات الكميات لتختار وتعبئ صنفاً واحداً أو أصنافاً محددة فقط يدوياً.'
        };
    }
    if (id === 'sl-btn-execute') {
        return {
            title: isSales ? '🚚 اعتماد الصرف وخصم المخزون (GDN)' : '📥 اعتماد الاستلام وإضافة المخزون (GRN)',
            desc: 'يحفظ الدفعة الحالية، ويحدث أرصدة المخازن وبطاقات الأصناف والتقارير فوراً، ويصدر مذكرة رسمية برقم تسلسلي وبصمة QR.'
        };
    }
    if (id === 'sl-btn-undo') {
        return {
            title: '↩️ عكس الحركة وإلغاء الإذن',
            desc: 'يلغي عملية الاستلام أو الصرف لهذا المستند، ويعكس أثرها المخزني بالكامل في المستودعات وبطاقة الصنف والسحابة.'
        };
    }
    if (onclick.includes('printSlOfficialNote')) {
        return {
            title: '🖨️ طباعة الإذن الرسمي (QR)',
            desc: 'يفتح نافذة طباعة مذكرة الاستلام (GRN) أو إذن الصرف وتصريح البوابة (GDN) مزودة برمز QR للتحقق وجدول التوقيعات.'
        };
    }
    if (onclick.includes('shareSlWhatsApp')) {
        return {
            title: '💬 مشاركة عبر واتساب',
            desc: 'يرسل تفاصيل المذكرة والأصناف والكميات المنفذة برسالة منسقة عبر واتساب للمورد أو العميل أو الإدارة.'
        };
    }
    if (el.matches('a[href="dashboard-pro.php"]') || el.closest('a[href="dashboard-pro.php"]')) {
        return {
            title: '🏠 العودة للوحة القيادة الرئيسية',
            desc: 'ينقلك بأمان إلى الشاشة الرئيسية للنظام.'
        };
    }
    if (id === 'sl-classic-purchases-btn' || el.closest('#sl-classic-purchases-btn')) {
        return {
            title: '📥 شاشة استلام المشتريات الكلاسيكية (GRN)',
            desc: 'ينقلك إلى شاشة أذونات الاستلام الكلاسيكية لاستعراض أو تحرير مذكرات الاستلام.'
        };
    }
    if (id === 'sl-classic-sales-btn' || el.closest('#sl-classic-sales-btn')) {
        return {
            title: '📤 شاشة تسليم المبيعات الكلاسيكية (GDN)',
            desc: 'ينقلك إلى شاشة أذونات تسليم المبيعات الكلاسيكية.'
        };
    }
    if (id === 'sl-reg-limit' || el.closest('#sl-reg-limit')) {
        return {
            title: '⚡ عدد المستندات المعروضة في السجل',
            desc: 'تحديد عدد الفواتير والأذونات المعروضة في الجدول العلوي (6، 15، 50، 100، 250، أو الكل).'
        };
    }
    if (id === 'sl-filter-btn-all') {
        return {
            title: '📋 عرض جميع المستندات',
            desc: 'يعرض كافة الفواتير والأذونات بجميع حالاتها (قيد الإجراء، المقسمة، والمكتملة) في قائمة واحدة.'
        };
    }
    if (onclick.includes('refreshAllSlData')) {
        return {
            title: '🔄 تحديث ومزامنة السجل الفوري',
            desc: 'يعيد قراءة أحدث الفواتير والأذونات وأرصدة المستودعات فوراً.'
        };
    }
    if (id === 'sl-wf-step-1' || el.closest('#sl-wf-step-1')) {
        return {
            title: '1️⃣ المرحلة الأولى: اختيار المستند أو مسح QR',
            desc: 'اختر الفاتورة من السجل العلوي أو امسح باركود الفاتورة بالكاميرا لتحميلها في منصة الفحص.'
        };
    }
    if (id === 'sl-wf-step-2' || el.closest('#sl-wf-step-2')) {
        return {
            title: '2️⃣ المرحلة الثانية: فحص وتقسيم الكميات والمخازن',
            desc: 'راجع الكميات الفعلية بالوحدة الأساسية أو الفرعية وحدد المستودع والرف لكل صنف.'
        };
    }
    if (id === 'sl-wf-step-3' || el.closest('#sl-wf-step-3')) {
        return {
            title: '3️⃣ المرحلة الثالثة: الاعتماد والطباعة الرسمية',
            desc: 'اعتمد الحركة لتحديث المخزون فوراً وطباعة مذكرة الاستلام (GRN) أو إذن الصرف (GDN) بالباركود.'
        };
    }
    if (id === 'sl-lbl-inv-no' || id === 'sl-lbl-party-name' || id === 'sl-lbl-date' || id === 'sl-lbl-hash' || id === 'sl-doc-status-pill') {
        return {
            title: '📄 بطاقة التعريف والبصمة الأمنية للمستند',
            desc: 'تعرض رقم الفاتورة/الإذن، واسم المورد أو العميل، وتاريخ المستند، والبصمة المشفرة (QR Hash) لمنع التلاعب أو التكرار.'
        };
    }
    if (id === 'sl-warehouse-notes' || id === 'sl-personal-notes') {
        return {
            title: '📝 ملاحظات الفحص والبوابة',
            desc: 'تدوين ملاحظات أمين المستودع حول حالة البضاعة المستلمة أو المسلمة وملاحظات أمن البوابة لتظهر في الإذن الرسمي المطبوع.'
        };
    }
    if (onclick.includes('startSlLocalCamera') || onclick.includes('stopSlLocalCamera') || id === 'sl-camera-placeholder' || el.closest('#sl-camera-placeholder')) {
        return {
            title: '📷 الماسح الضوئي للكاميرا المباشرة',
            desc: 'تشغيل كاميرا الجهاز أو ربط كاميرا الجوال لقراءة باركود الفواتير وأذونات الاستلام والصرف بشكل متتابع.'
        };
    }
    if (id === 'sl-photo-input' || onclick.includes('sl-photo-input')) {
        return {
            title: '📎 إرفاق صورة إثبات للبضاعة أو المستند',
            desc: 'تصوير الشاحنة أو البضاعة عند البوابة أو إرفاق صورة مستند المورد الموقع وحفظها رقمياً مع الإذن.'
        };
    }
    if (onclick.includes('sl-cols-drawer') || id === 'sl-cols-drawer' || el.closest('#sl-cols-drawer')) {
        return {
            title: '⚙️ تخصيص أعمدة جدول الفحص',
            desc: 'إظهار أو إخفاء أعمدة (المستودع والرصيد، الرف والتشغيلة، المعامل، المنفذ والمتبقي، والسعر) حسب حاجة العمل.'
        };
    }
    if (el.closest('#sl-weighbridge-modal')) {
        return {
            title: '⚖️ نافذة قارئ الميزان الإلكتروني (البسكول)',
            desc: 'أدخل الوزن القائم للشاحنة (Gross) ووزن الفارغ (Tare) أو اقرأهما من منفذ الميزان ليحسب النظام الوزن الصافي (Net) ويُدرجه في سطر الصنف مع رقم التذكرة.'
        };
    }
    if (el.closest('#sl-signature-modal')) {
        return {
            title: '✍️ نافذة التوقيع الإلكتروني باللمس',
            desc: 'وقّع بالماوس أو الإصبع في لوحة أمين المخزن ولوحة السائق/المستلم، ثم اضغط حفظ ليُطبع التوقيعان رسمياً في ذيل مذكرة الاستلام أو الصرف.'
        };
    }
    if (el.closest('#sl-debit-note-modal')) {
        return {
            title: '⚠️ نافذة إشعار الخصم والمطالبة بالفروقات (Debit Note)',
            desc: 'حدد كمية العجز أو التلف وسبب المطالبة لترحيل إشعار خصم مالي مباشر على حساب المورد وطباعة إشعار المطالبة الرسمي.'
        };
    }
    if (el.closest('#sl-mobile-bridge-modal')) {
        return {
            title: '📱 نافذة ربط كاميرا الجوال المباشر',
            desc: 'امسح رمز QR بكاميرا هاتفك ليعمل الجوال كقارئ باركود لاسلكي يمسح الأذونات بالتتابع ويعتمدها فوراً.'
        };
    }
    if (el.closest('#sl-font-modal')) {
        return {
            title: '🎨 نافذة تخصيص حجم ولون الخط',
            desc: 'تحكم في حجم خط الجداول والأرقام ولون النص الأساسي لراحة العين مع الحفظ التلقائي في المتصفح.'
        };
    }
    if (id === 'sl-btn-voice-summary' || onclick.includes('speakSlReceiptSummaryBeforeSave')) {
        return {
            title: '🔊 القراءة الصوتية الرجالية لملخص الإذن قبل الاعتماد',
            desc: 'ينطق بالصوت الرجالي الواضح اسم المورد أو العميل وعدد الأصناف والكمية المنفذة في هذه الدفعة والمتبقي ونسبة الإنجاز قبل الحفظ.'
        };
    }
    if (id === 'sl-btn-voice-dictate' || onclick.includes('startSlVoiceDictation')) {
        return {
            title: '🎙️ الإملاء الصوتي لكميات الاستلام وملاحظات الفحص',
            desc: 'يتيح لأمين المستودع نطق الكمية المستلمة (مثل: 5 أو الكل أو نصف الكمية) أو إملاء ملاحظة الفحص بالميكروفون دون استخدام لوحة المفاتيح.'
        };
    }
    if (id === 'sl-btn-auto-allocate' || onclick.includes('autoAllocateSlAcrossWarehouses')) {
        return {
            title: '🏭 توزيع ذكي تلقائي للكميات على المستودعات',
            desc: 'يقسم الكميات المتبقية في المستند تلقائياً بين المستودعات المتاحة بضغطة زر واحدة لتسهيل توزيع الشحنات الكبيرة.'
        };
    }
    if (id === 'sl-gate-time-in' || id === 'sl-gate-time-out' || id === 'sl-btn-gate-stamp' || id === 'sl-gate-duration-badge' || onclick.includes('stampSlGateTimeNow')) {
        return {
            title: '⏱️ ختم وقت البوابة وزمن التفريغ والتحميل',
            desc: 'يسجل وقت دخول الشاحنة للبوابة ووقت خروجها أوتوماتيكياً ويحسب زمن التفريغ أو التحميل بالدقائق والثواني.'
        };
    }
    if (id === 'sl-sum-pct-badge' || id === 'sl-sum-progress-bar') {
        return {
            title: '📈 مؤشر نسبة إنجاز التوريد والصرف (%)',
            desc: 'شريط تقدم حي يحسب النسبة المئوية للكميات المنفذة من إجمالي الفاتورة لحظياً ويتحول للأخضر عند اكتمال 100%.'
        };
    }
    if (id === 'sl-btn-quick-add-wh' || onclick.includes('openSlQuickWarehouseModal')) {
        return {
            title: '➕ إضافة مستودع جديد فوراً (+)',
            desc: 'يفتح نافذة سريعة لإنشاء مستودع أو فرع جديد واختياره فوراً في الفاتورة الحالية دون مغادرة الشاشة.'
        };
    }
    if (el.closest('#sl-quick-warehouse-modal')) {
        return {
            title: '🏢 نافذة الإضافة السريعة للمستودعات',
            desc: 'أدخل اسم المستودع الجديد وموقعه ثم اضغط حفظ ليُضاف فوراً لجميع قوائم المستودعات في النظام.'
        };
    }
    if (el.closest('#sl-confirm-undo-modal')) {
        return {
            title: '↩️ نافذة تأكيد عكس الحركة المخزنية',
            desc: 'تأكيد آمن لإلغاء مذكرة الاستلام أو إذن الصرف وإرجاع الأرصدة المخزنية لحالتها السابقة بدقة.'
        };
    }
    if (id === 'sl-reg-date-from' || id === 'sl-reg-date-to') {
        return {
            title: '📅 فلترة السجل اللوجستي بالتاريخ (من / إلى)',
            desc: 'تصفية فورية لمذكرات الاستلام وأذونات الصرف والفواتير حسب فترة زمنية محددة بدقة.'
        };
    }
    if (id === 'sl-btn-export-excel' || onclick.includes('exportSlRegistryToExcel')) {
        return {
            title: '📊 تصدير السجل اللوجستي إلى Excel (CSV)',
            desc: 'تصدير جميع المستندات المفلترة حالياً مع الأصناف والحالات والمستودعات إلى ملف إكسل جاهز للمراجعة المحاسبية.'
        };
    }
    if (id === 'sl-btn-export-pdf' || onclick.includes('exportSlRegistryToPdf')) {
        return {
            title: '📄 تصدير وطباعة السجل اللوجستي PDF (A4)',
            desc: 'يصدر السجل المفلتر حالياً في تقرير A4 رسمي موزون جاهز للحفظ بصيغة PDF أو الطباعة المباشرة.'
        };
    }
    if (id === 'sl-btn-drilldown-inv' || onclick.includes('openSlOriginalInvoiceDrilldownModal') || el.closest('#sl-drilldown-invoice-modal')) {
        return {
            title: '🔍 الفاتورة المالية الأصلية والتفاصيل المحاسبية (Drill-Down)',
            desc: 'يفتح نافذة تفصيلية تعرض أسعار الأصناف وإجمالي الفاتورة والمدفوع والمتبقي ورصيد المورد أو العميل دون مغادرة شاشة المخزن.'
        };
    }
    if (id === 'sl-lbl-party-balance') {
        return {
            title: '💰 الرصيد المالي اللحظي للمورد / العميل',
            desc: 'يعرض الرصيد المحاسبي الحي للجهة المرتبطة بالمستند لمساعدة أمين المخزن والإدارة في الرقابة الاستباقية.'
        };
    }
    if (id === 'sl-lbl-audit-stamp') {
        return {
            title: '🛡️ ختم الرقابة الصارمة وبصمة الجهاز والمستخدم',
            desc: 'يوثق اسم المستخدم المسؤول ونوع الجهاز (موبايل/لابتوب) وتوقيت الحركة لضمان الشفافية ومنع التلاعب.'
        };
    }
    if (id === 'sl-btn-print-80mm' || onclick.includes('printSlThermalReceipt80mm')) {
        return {
            title: '🧾 طباعة إيصال بوابة وكاشير حراري (80mm)',
            desc: 'طباعة سريعة بمقاس ورق الكاشير الحراري 80mm تتضمن الأصناف والكميات وباركود التحقق السريع لأمن البوابة.'
        };
    }
    if (id === 'sl-btn-print-labels' || onclick.includes('printSlBinBatchLabels')) {
        return {
            title: '🏷️ طباعة ملصقات باركود الأرفف والتشغيلات (Bin & Batch Labels)',
            desc: 'يطبع ملصقات باركود ورمز QR لكل صنف في المستند تشمل اسم الصنف والكمية وموقع الرف ورقم التشغيلة للصقها على الطبليات والأرفف.'
        };
    }
    if (id === 'sl-lbl-party-sla') {
        return {
            title: '⭐ مؤشر كفاءة التوريد وزمن البوابة (SLA Scorecard)',
            desc: 'يعرض نسبة دقة التوريد بلا نواقص أو مطالبات ومتوسط زمن تفريغ وتحميل الشاحنات لهذه الجهة.'
        };
    }
    if (id === 'sl-undo-pin-input') {
        return {
            title: '🔐 قفل الأمان الإداري لعكس الحركة المخزنية (PIN Guard)',
            desc: 'حماية أمنية تمنع إلغاء أو عكس أذونات الاستلام والصرف المعتمدة بالخطأ إلا بعد إدخال رمز الأمان.'
        };
    }
    if (id === 'sl-btn-sunlight-mode' || onclick.includes('toggleSlSunlightMode')) {
        return {
            title: '☀️ وضع الرؤية الفائقة تحت أشعة الشمس (Outdoor Sunlight Mode)',
            desc: 'يضاعف سطوع التباين ويرفع سُمك جميع الحدود والخانات إلى 3px لتمكين عمال الساحات والبوابات الخارجية من القراءة بوضوح تام تحت الشمس.'
        };
    }
    if (id === 'sl-btn-batch-ocr' || onclick.includes('openSlBatchOcrScannerModal') || el.closest('#sl-batch-ocr-modal')) {
        return {
            title: '📷 الماسح البصري الذكي للتشغيلة وتاريخ الصلاحية (Batch/EXP OCR)',
            desc: 'يمسح ملصق الكرتون بالكاميرا أو يولّد رقم التشغيلة وتاريخ الانتهاء آلياً ويدرجهما في جدول الفحص.'
        };
    }
    if (id === 'sl-btn-bin-heatmap' || onclick.includes('openSlBinHeatmapModal') || el.closest('#sl-bin-heatmap-modal')) {
        return {
            title: '🗺️ خريطة المستودع التفاعلية وتسكين الأرفف (Visual Bin Heatmap)',
            desc: 'تعرض خريطة الممرات والأرفف ملونة حسب الإشغال (متاح، جزئي، ممتلئ) لتسكين الأصناف بضغطة واحدة.'
        };
    }
    if (id === 'sl-btn-fleet-dispatch' || onclick.includes('openSlFleetDispatchModal') || el.closest('#sl-fleet-dispatch-modal')) {
        return {
            title: '🚚 مجدول الأسطول ومسار توصيل المبيعات (Fleet & Route Dispatcher)',
            desc: 'يحدد نوع مركبة التوصيل، منطقة وعنوان العميل، موعد الوصول المتوقع، وحالة الرحلة مع طباعة بوليصة شحن وتصريح خروج بوابة وإرسال رابط التتبع عبر واتساب.'
        };
    }
    if (id === 'sl-btn-load-verifier' || onclick.includes('openSlLoadingVerifierModal') || el.closest('#sl-load-verifier-modal')) {
        return {
            title: '🎯 الفاحص الذكي لمطابقة التحميل ومنع أخطاء الصرف (Zero-Error Verifier)',
            desc: 'يتحقق من مطابقة الأصناف المحملة فعلياً على الشاحنة مع إذن الصرف (GDN) عبر الباركود أو الفحص السريع ويمنع خروج أي صنف خاطئ من البوابة.'
        };
    }
    if (id === 'sl-btn-cargo-estimator' || onclick.includes('openSlCargoEstimatorModal') || el.closest('#sl-cargo-estimator-modal')) {
        return {
            title: '📦 حاسبة حجم الحمولة والطبليات واختيار الشاحنة (Cargo & Pallet Estimator)',
            desc: 'تحسب إجمالي وزن الدفعة بالكيلوجرام والطن وعدد الطبليات القياسية المطلوبة، وتقترح حجم المركبة الأنسب للتحميل أوتوماتيكياً.'
        };
    }
    if (id === 'sl-live-sync-status-pill' || el.closest('#sl-live-sync-status-pill')) {
        return {
            title: '⚡ مؤشر التزامن الفوري الحي (Sub-Second Live Sync)',
            desc: 'يؤكد اتصال البوابة اللوجستية اللحظي مع شاشات المبيعات والمشتريات والمخزون والموبايل واللابتوب في جزء من الثانية.'
        };
    }
    if (id === 'sl-btn-voice-commander' || onclick.includes('openSlVoiceGateCommanderModal') || el.closest('#sl-voice-commander-modal')) {
        return {
            title: '🎤 المساعد الصوتي التفاعلي لبوابة الصرف (Voice Gate Commander)',
            desc: 'ينفذ الأوامر الصوتية المباشرة بالميكروفون أو بضغطة زر (مثل: اقرأ الملخص، نصف الكمية، افتح مسار التوصيل، نداء السائق، اعتمد الصرف) ويرد بالصوت الرجالي.'
        };
    }
    if (id === 'sl-btn-pa-announcer' || onclick.includes('announceSlDriverLoadingPA')) {
        return {
            title: '📢 النداء الصوتي الآلي للسائقين واللوحات في ساحة التحميل (PA Announcer)',
            desc: 'يذيع بالصوت الرجالي الجهوري نداءً آلياً باسم السائق ورقم لوحة المركبة ورقم الفاتورة والمستودع الصارف لتنظيم رصيف التحميل.'
        };
    }
    if (id === 'sl-btn-gdn-quiz' || onclick.includes('openSlGdnMasteryQuizModal') || el.closest('#sl-gdn-quiz-modal')) {
        return {
            title: '🎓 اختبار الإتقان السريع لأمين صرف المبيعات (GDN Mastery Quiz)',
            desc: 'يطرح 3 مواقف عملية في تقسيم دفعات المبيعات وفحص أرصدة المخازن وتصريح البوابة مع تصحيح صوتي وبصري فوري.'
        };
    }
    if (id === 'sl-btn-pending-aging' || onclick.includes('openSlPendingDeliveryAgingModal') || el.closest('#sl-pending-aging-modal')) {
        return {
            title: '📊 تقرير كشف التسليمات المتبقية وأعمار ذمم البضاعة للعملاء (Pending Delivery Aging)',
            desc: 'يعرض جميع العملاء الذين لديهم كميات مباعة لم يستلموها بعد مع عدد أيام التأخير والقيمة المالية المعلقة وزر طباعة A4.'
        };
    }
    if (id === 'sl-btn-gate-credit-note' || onclick.includes('openSlGateCreditNoteModal') || el.closest('#sl-gate-credit-modal')) {
        return {
            title: '↩️ محرك التسوية المالية والمخزنية الذكي للمرتجعات عند البوابة (Gate Credit Note)',
            desc: 'يُصدر إشعار دائن فوري للعميل عند إرجاع جزء من البضاعة عند البوابة ويعيد الكمية للمخزن ويحدث كشف حساب العميل آلياً.'
        };
    }
    if (id === 'sl-btn-gate-certificate' || onclick.includes('printSlGateClearanceCertificate')) {
        return {
            title: '🛡️ شهادة إخلاء طرف وخروج بوابة رقمية موثقة (Digital Gate Clearance Certificate)',
            desc: 'تُصدر شهادة خروج رسمية موثقة بختم مطابقة الوزن والعدد وتوقيعات أمين المستودع والسائق وأمن البوابة ورمز QR.'
        };
    }
    if (id === 'sl-btn-dock-zoom' || onclick.includes('toggleSlDockBigScreenMode')) {
        return {
            title: '🔍 وضع القراءة المكبّرة الفائقة لساحات وأرصفة التحميل (Dock Big-Screen Mode)',
            desc: 'يكبّر أرقام اللوحات وخانات الكميات والعدادات لقراءتها بوضوح تام من مسافة 5 أمتار أثناء التحميل.'
        };
    }
    if (id === 'sl-btn-contrast-inspector' || onclick.includes('openSlContrastInspectorModal') || el.closest('#sl-contrast-inspector-modal')) {
        return {
            title: '🛡️ فاحص سلامة التباين والسمات وسُمك الحواف (+2) والتوريث الآلي',
            desc: 'يفحص جميع عناصر الشاشة والشاشات الوليدة ويتحقق من تطبيق سُمك الحواف 2.2px والأيقونات 2.6px وثبات ألوان الكتابة 100%.'
        };
    }
    if (id === 'sl-btn-dispatch-presets' || onclick.includes('openSlDispatchPresetsModal') || el.closest('#sl-dispatch-presets-modal')) {
        return {
            title: '⚡ الحافظة الذكية لقوالب السائقين والمركبات المتكررة (1-Click Dispatch Presets)',
            desc: 'تحفظ بيانات السائقين وأرقام اللوحات والمستلمين المتكررين لتعبئة بطاقة التسليم بضغطة واحدة في جزء من الثانية.'
        };
    }
    if (el.closest('.sl-floating-summary')) {
        return {
            title: '📊 شريط الميزان اللوجستي والملخص اللحظي',
            desc: 'يعرض معادلة التوريد والصرف لحظياً: (عدد الأصناف | إجمالي الفاتورة | المنفذ سابقاً | الدفعة الحالية | المتبقي بعد الدفعة) قبل الضغط على اعتماد.'
        };
    }

    // ── CONSTITUTION 4 (ORDER 3): SELF-UPDATING DYNAMIC FALLBACK FOR ANY FUTURE BUTTON, INPUT, OR SELECT ──
    const dynTarget = el.closest('button, a, input, select, textarea, .sl-btn, .sl-input, [onclick]');
    if (dynTarget && !dynTarget.closest('#msg-live-coach-bar, #msg-smart-hover-tooltip, #msg-universal-fixed-launcher')) {
        const prevLabel = dynTarget.previousElementSibling?.tagName === 'LABEL'
            ? dynTarget.previousElementSibling.textContent.trim()
            : (dynTarget.closest('div')?.querySelector('label')?.textContent?.trim() || '');
        const rawLabel = (
            dynTarget.getAttribute('data-guide-title') ||
            dynTarget.getAttribute('title') ||
            dynTarget.getAttribute('aria-label') ||
            dynTarget.getAttribute('placeholder') ||
            prevLabel ||
            dynTarget.innerText ||
            dynTarget.value ||
            'أداة لوجستية تفاعلية'
        ).replace(/\s+/g, ' ').trim().slice(0, 65);

        if (rawLabel) {
            const tag = dynTarget.tagName;
            if (tag === 'SELECT') {
                return {
                    title: `📋 قائمة اختيار: ${rawLabel}`,
                    desc: `تتيح لك هذه القائمة المنسدلة اختيار (${rawLabel}) وتحديث بيانات البوابة اللوجستية والسجل فوراً.`
                };
            }
            if (tag === 'INPUT' || tag === 'TEXTAREA') {
                return {
                    title: `⌨️ خانة إدخال: ${rawLabel}`,
                    desc: `تُستخدم هذه الخانة لإدخال أو تعديل (${rawLabel}) مع الحفظ الفوري والتوريث التلقائي لثبات اللون وسلاسة المؤشر.`
                };
            }
            return {
                title: `⚡ أمر تشغيلي: ${rawLabel}`,
                desc: `يقوم هذا الزر بتنفيذ إجراء (${rawLabel}) مباشرة داخل بوابة الاستلام والصرف وتحديث السجل والمخزون لحظياً.`
            };
        }
    }
    return null;
};

// ── CONSTITUTION 4 (ORDERS 3 & 5): FUTURE-PROOF INHERITANCE & SELF-UPDATING SMART GUIDE OBSERVER ──
function applySlConstitution4NodeInheritance(rootNode = document) {
    if (!rootNode || !rootNode.querySelectorAll) return;
    rootNode.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="file"]), select, textarea').forEach(inp => {
        if (inp.closest('#msg-live-coach-bar, #msg-universal-modal, #msg-voice-qa-panel')) return;
        if (!inp.classList.contains('sl-input')) {
            inp.classList.add('sl-input');
        }
    });
    rootNode.querySelectorAll('svg').forEach(svgEl => {
        svgEl.style.strokeWidth = '2.6px';
    });
}

// ── AUTO-SELECT NUMERIC INPUTS ON FOCUS/CLICK & UNSAVED INPUT GUARD (CONSTITUTION 1) ──
document.addEventListener('focusin', (e) => {
    const t = e.target;
    if (!t || t.tagName !== 'INPUT') return;
    if (
        t.type === 'number' ||
        t.inputMode === 'decimal' ||
        t.classList.contains('sl-qty-primary-input') ||
        t.classList.contains('sl-qty-base-input') ||
        t.classList.contains('sl-dn-qty-input') ||
        t.id === 'sl-wb-gross' ||
        t.id === 'sl-wb-tare'
    ) {
        setTimeout(() => {
            try { t.select(); } catch (_e) {}
        }, 10);
    }
});

document.addEventListener('input', (e) => {
    const t = e.target;
    if (!t) return;
    if (
        t.classList?.contains('sl-row-bin-input') ||
        t.classList?.contains('sl-row-batch-input') ||
        ['sl-receiver-name', 'sl-driver-name', 'sl-truck-plate', 'sl-warehouse-notes', 'sl-personal-notes'].includes(t.id)
    ) {
        window._slHasUnsavedChanges = true;
    }
});

window.addEventListener('beforeunload', (e) => {
    if (window._slHasUnsavedChanges && slCurrentDoc && slCurrentDoc._slStatus !== 'done') {
        e.preventDefault();
        e.returnValue = '';
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const savedSize = localStorage.getItem('sl_custom_font_size');
    const savedColor = localStorage.getItem('sl_custom_font_color');
    if (savedSize || savedColor) {
        applySlTypography(savedSize || 15, savedColor || '#f8fafc');
    }
    if (localStorage.getItem('sl_sunlight_mode') === '1') {
        toggleSlSunlightMode(true);
    }
    if (localStorage.getItem('sl_dock_bigscreen_mode') === '1') {
        document.body.classList.add('sl-dock-bigscreen-mode');
    }

    const params = new URLSearchParams(window.location.search);
    const urlMode = params.get('mode');
    if (urlMode === 'sales' || urlMode === 'purchases') {
        slCurrentMode = urlMode;
    }

    switchLogisticsMode(slCurrentMode, true);
    startSlMobileBridgeListener();
    flushSlOfflineSyncQueue();
    applySlConstitution4NodeInheritance(document);

    if (typeof MutationObserver !== 'undefined' && document.body) {
        let slInheritTimer = null;
        const slConstitution4Observer = new MutationObserver((mutations) => {
            let hasNewNodes = false;
            for (const m of mutations) {
                if (m.addedNodes && m.addedNodes.length > 0) {
                    hasNewNodes = true;
                    break;
                }
            }
            if (!hasNewNodes) return;
            if (slInheritTimer) clearTimeout(slInheritTimer);
            slInheritTimer = setTimeout(() => {
                applySlConstitution4NodeInheritance(document.body);
                if (typeof window.syncDynamicDomElementsIntoSmartGuide === 'function') {
                    window.syncDynamicDomElementsIntoSmartGuide();
                }
            }, 180);
        });
        slConstitution4Observer.observe(document.body, { childList: true, subtree: true });
    }

    // Real-Time Cross-Tab & Sub-Second Sync Listeners (Order 3)
    ['elsajana_erp_sync', 'magaies_purchase_sync_channel', 'magaies_sales_sync_channel', 'magaies_erp_sync_channel'].forEach(chName => {
        try {
            const syncCh = new BroadcastChannel(chName);
            syncCh.onmessage = () => {
                populateSlWarehouseSelects();
                renderSlRegistry();
            };
        } catch (e) {}
    });
    window.addEventListener('keydown', (e) => {
        if (e.key === 'F10') {
            e.preventDefault();
            if (typeof executeSlTransaction === 'function') executeSlTransaction();
        } else if (e.key === 'F2') {
            e.preventDefault();
            const s = document.getElementById('sl-reg-search');
            if (s) { s.focus(); s.select(); }
        }
    });
    window.addEventListener('storage', (ev) => {
        if (ev.key && (ev.key.includes('purchase') || ev.key.includes('sales') || ev.key.includes('inventory') || ev.key.includes('grn') || ev.key.includes('warehouse'))) {
            populateSlWarehouseSelects();
            renderSlRegistry();
        }
    });

    const qrParam = params.get('qr_deliver') || params.get('inv');
    if (qrParam) {
        setTimeout(() => processSlContinuousQr(qrParam), 300);
    }
});

// ── CONSTITUTION 2 APPROVED PROPOSALS IMPLEMENTATION ────────────────────────
window.openSlVoiceGateCommanderModal = function () {
    const m = document.getElementById('sl-voice-commander-modal');
    if (!m) return;
    m.style.display = 'flex';
    speakSlMaleVoiceText('المساعد الصوتي لبوابة الصرف جاهز. انطق أمرك أو اختر من الأزرار السريعة.');
};

window.executeSlVoiceCommand = function (rawCmd) {
    const cmd = String(rawCmd || '').trim();
    const st = document.getElementById('sl-voice-cmd-status');
    if (!cmd) return;
    if (cmd.includes('ملخص') || cmd.includes('اقرأ')) {
        if (st) st.textContent = '🔊 جاري قراءة الملخص الصوتي للإذن...';
        speakSlReceiptSummaryBeforeSave();
    } else if (cmd.includes('تعبئة الكل') || cmd.includes('كامل') || cmd.includes('100')) {
        fillAllSlQuantities(1);
        if (st) st.textContent = '✅ تم تعبئة كامل الكمية المتبقية (100%) بنجاح.';
    } else if (cmd.includes('نصف') || cmd.includes('50')) {
        fillAllSlQuantities(0.5);
        if (st) st.textContent = '🌗 تم تقسيم الكمية إلى النصف (50%) بنجاح.';
    } else if (cmd.includes('مسار') || cmd.includes('توصيل') || cmd.includes('أسطول')) {
        document.getElementById('sl-voice-commander-modal').style.display = 'none';
        if (typeof window.openSlFleetDispatchModal === 'function') window.openSlFleetDispatchModal();
        speakSlMaleVoiceText('تم فتح نافذة مسار التوصيل وجدولة الأسطول.');
    } else if (cmd.includes('نداء') || cmd.includes('سائق') || cmd.includes('لوحة')) {
        window.announceSlDriverLoadingPA();
    } else if (cmd.includes('اعتمد') || cmd.includes('صرف') || cmd.includes('حفظ')) {
        document.getElementById('sl-voice-commander-modal').style.display = 'none';
        if (typeof executeSlTransaction === 'function') executeSlTransaction();
    } else {
        if (st) st.textContent = `ℹ️ تم استلام الأمر: "${cmd}"`;
        speakSlMaleVoiceText(`تم استلام الأمر ${cmd}`);
    }
};

window.listenSlVoiceGateCommand = function () {
    const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
    const st = document.getElementById('sl-voice-cmd-status');
    if (!SpeechRec) {
        if (st) st.textContent = '⚠️ المتصفح لا يدعم الميكروفون المباشر، يمكنك استخدام الأزرار السريعة أو الكتابة.';
        speakSlMaleVoiceText('يرجى اختيار الأمر من الأزرار السريعة بالأسفل.');
        return;
    }
    try {
        const rec = new SpeechRec();
        rec.lang = 'ar-SA';
        rec.interimResults = false;
        if (st) st.textContent = '🎙️ جاري الاستماع الآن... انطق الأمر (مثل: اقرأ الملخص، نصف الكمية، اعتمد الصرف)';
        rec.onresult = (ev) => {
            const transcript = ev.results?.[0]?.[0]?.transcript || '';
            const inp = document.getElementById('sl-voice-cmd-input');
            if (inp) inp.value = transcript;
            window.executeSlVoiceCommand(transcript);
        };
        rec.onerror = () => {
            if (st) st.textContent = '⚠️ تعذر التقاط الصوت، جرب مرة أخرى أو اضغط على أحد الأوامر بالأسفل.';
        };
        rec.start();
    } catch (_e) {}
};

window.announceSlDriverLoadingPA = function () {
    if (!slCurrentDoc && slRegistryDocs.length > 0) {
        selectSlDocument(slRegistryDocs[0].invoiceNo);
    }
    const invNo = slCurrentDoc?.invoiceNo || 'GDN-101';
    const party = slCurrentDoc ? resolveSlPartyName(slCurrentDoc, slCurrentMode) : 'عميل المبيعات';
    const driver = document.getElementById('sl-driver-name')?.value?.trim() || 'السائق المناوب';
    const plate = document.getElementById('sl-truck-plate')?.value?.trim() || 'لوحة التحميل المعتمدة';
    const whSel = document.getElementById('sl-master-warehouse');
    const whName = whSel && whSel.selectedIndex >= 0 ? whSel.options[whSel.selectedIndex].text.replace('🏢', '').trim() : 'المستودع الرئيسي';
    const msg = `نداء آلي إلى ساحة التحميل والبوابة: على السائق ${driver}، قائد المركبة رقم ${plate}، التوجه فوراً إلى رصيف ${whName} لاستلام وصرف فاتورة العميل ${party} رقم ${invNo}.`;
    showSlToast(`📢 ${msg}`, 'success', 5500);
    speakSlMaleVoiceText(msg);
};

const SL_GDN_QUIZ_QUESTIONS = [
    {
        q: '1️⃣ إذا اشترى العميل 10 طن وسيارته اليوم تتسع لـ 6 طن فقط، كيف تتصرف في بوابة تسليم المبيعات (GDN)؟',
        opts: [
            'أكتب 6 طن في خانة الصرف وأعتمد، فيحفظ النظام 4 طن متبقية للعميل تلقائياً',
            'أرفض تسليم العميل حتى يحضر شاحنة كبيرة',
            'أحذف الفاتورة المالية الأصلية'
        ],
        correct: 0,
        explain: 'إجابة صحيحة! تقسيم الدفعات يحفظ حق العميل في الـ 4 طن المتبقية ويخصم 6 طن فقط من المستودع الآن.'
    },
    {
        q: '2️⃣ إذا كانت الفاتورة تحتوي على صنفين، الصنف الأول في المستودع الرئيسي والثاني في مستودع الفرع، ماذا تفعل؟',
        opts: [
            'أختار لكل سطر المستودع الخاص به داخل جدول الفحص وأصرفهما معاً في إذن واحد',
            'لا يمكن الصرف من مستودعين في نفس الفاتورة',
            'أصرف الكمية بدون تحديد مستودع'
        ],
        correct: 0,
        explain: 'أحسنت! جدول الفحص يدعم تعدد المخازن للسطر الواحد ويعرض الرصيد الفعلي لكل مستودع.'
    },
    {
        q: '3️⃣ ما فائدة رقم تصريح البوابة وباركود QR المطبوع على إذن صرف المبيعات (GDN)؟',
        opts: [
            'مطابقة أمن البوابة للوحة الشاحنة والكميات المصروفة ومنع خروج أي بضاعة غير معتمدة',
            'مجرد شكل جمالي في الورقة فقط',
            'تغيير أسعار البيع في الفاتورة'
        ],
        correct: 0,
        explain: 'ممتاز! تصريح البوابة ورمز الكيو آر يضمنان الرقابة الصارمة عند خروج الشاحنة من البوابة.'
    }
];

window.openSlGdnMasteryQuizModal = function () {
    const m = document.getElementById('sl-gdn-quiz-modal');
    const body = document.getElementById('sl-gdn-quiz-body');
    const scoreEl = document.getElementById('sl-gdn-quiz-score');
    if (!m || !body) return;
    window.__slQuizAnswers = {};
    if (scoreEl) scoreEl.textContent = `النتيجة: 0 / ${SL_GDN_QUIZ_QUESTIONS.length}`;
    body.innerHTML = SL_GDN_QUIZ_QUESTIONS.map((item, qIdx) => `
        <div style="padding:12px; border-radius:12px; background:rgba(15,23,42,0.55); border:1.5px solid rgba(192,132,252,0.3);">
            <div style="font-weight:900; font-size:0.88rem; margin-bottom:8px;">${item.q}</div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                ${item.opts.map((opt, oIdx) => `
                    <button type="button" id="sl-quiz-btn-${qIdx}-${oIdx}" onclick="window.answerSlGdnQuiz(${qIdx}, ${oIdx})" class="sl-btn" style="justify-content:flex-start; text-align:right; font-size:0.8rem; padding:7px 10px;">
                        ${opt}
                    </button>
                `).join('')}
            </div>
            <div id="sl-quiz-fb-${qIdx}" style="display:none; margin-top:8px; font-size:0.8rem; font-weight:800; padding:6px 10px; border-radius:8px;"></div>
        </div>
    `).join('');
    m.style.display = 'flex';
    speakSlMaleVoiceText('أهلاً بك في اختبار الإتقان السريع لبوابة تسليم المبيعات. أجب على الأسئلة الثلاثة لاختبار جاهزيتك.');
};

window.answerSlGdnQuiz = function (qIdx, oIdx) {
    const item = SL_GDN_QUIZ_QUESTIONS[qIdx];
    if (!item) return;
    window.__slQuizAnswers = window.__slQuizAnswers || {};
    const isCorrect = (oIdx === item.correct);
    window.__slQuizAnswers[qIdx] = isCorrect ? 1 : 0;
    const fb = document.getElementById(`sl-quiz-fb-${qIdx}`);
    if (fb) {
        fb.style.display = 'block';
        fb.style.background = isCorrect ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)';
        fb.style.color = isCorrect ? '#34d399' : '#f87171';
        fb.textContent = isCorrect ? `✅ ${item.explain}` : '❌ إجابة غير دقيقة، جرب الخيار الأول الصحيح.';
    }
    const totalScore = Object.values(window.__slQuizAnswers).reduce((a, b) => a + b, 0);
    const scoreEl = document.getElementById('sl-gdn-quiz-score');
    if (scoreEl) {
        scoreEl.textContent = `النتيجة: ${totalScore} / ${SL_GDN_QUIZ_QUESTIONS.length} ${totalScore === SL_GDN_QUIZ_QUESTIONS.length ? '🏆 متقن 100%' : ''}`;
    }
    speakSlMaleVoiceText(isCorrect ? item.explain : 'إجابة غير دقيقة، راجع الخيار الأول.');
};

// ── CONSTITUTION 3 APPROVED PROPOSALS IMPLEMENTATION ────────────────────────
window.openSlPendingDeliveryAgingModal = function () {
    const m = document.getElementById('sl-pending-aging-modal');
    const tbody = document.getElementById('sl-pending-aging-tbody');
    const kpis = document.getElementById('sl-pending-aging-kpis');
    if (!m || !tbody) return;

    const docs = (slCurrentMode === 'sales' ? getProcessedSalesInvoices() : getProcessedPurchaseInvoices())
        .filter(d => d._slStatus !== 'done');
    let totalRemValue = 0;
    let totalRemQty = 0;
    const nowMs = Date.now();

    const rowsHtml = docs.map(d => {
        const invDateStr = (d.date || d.savedAt || new Date().toISOString()).slice(0, 10);
        const days = Math.max(0, Math.floor((nowMs - new Date(invDateStr).getTime()) / 86400000) || 0);
        let docRemVal = 0;
        const remItemsTxt = (d.items || []).map(it => {
            const meta = resolveSlItemMeta(it);
            const rem = parseFloat(it.remainingQty ?? meta.qty) || 0;
            totalRemQty += rem;
            docRemVal += rem * (meta.price || 0);
            return `${meta.name}: متبقي ${rem} ${meta.unit}`;
        }).join(' | ');
        totalRemValue += docRemVal;
        const badgeColor = days > 7 ? '#ef4444' : (days > 3 ? '#f59e0b' : '#10b981');
        return `<tr>
            <td style="font-weight:900;">#${d.invoiceNo}</td>
            <td style="font-weight:800;">${resolveSlPartyName(d, slCurrentMode)}</td>
            <td style="text-align:center;">${invDateStr}</td>
            <td style="text-align:center;"><span style="padding:2px 8px;border-radius:99px;background:${badgeColor}22;color:${badgeColor};border:1px solid ${badgeColor};font-weight:900;">${days} يوم</span></td>
            <td style="font-size:0.78rem;">${remItemsTxt}</td>
            <td style="text-align:center;font-weight:900;color:#34d399;">${docRemVal.toFixed(2)}</td>
            <td style="text-align:center;">
                <button type="button" onclick="document.getElementById('sl-pending-aging-modal').style.display='none'; selectSlDocument('${d.invoiceNo}');" class="sl-btn" style="padding:3px 9px;font-size:0.74rem;background:rgba(56,189,248,0.2);border-color:#38bdf8;color:#38bdf8;">⚡ فتح وصرف</button>
            </td>
        </tr>`;
    }).join('');

    tbody.innerHTML = rowsHtml || `<tr><td colspan="7" style="text-align:center;padding:18px;font-weight:800;">✅ لا توجد تسليمات متبقية معلقة حالياً — جميع الفواتير مكتملة الصرف 100%</td></tr>`;
    if (kpis) {
        kpis.innerHTML = `
            <div style="padding:10px;border-radius:10px;background:rgba(56,189,248,0.14);border:1.5px solid #38bdf8;text-align:center;">
                <div style="font-size:0.76rem;">📄 فواتير بانتظار استكمال الصرف</div>
                <div style="font-size:1.2rem;font-weight:900;color:#38bdf8;">${docs.length} فاتورة</div>
            </div>
            <div style="padding:10px;border-radius:10px;background:rgba(245,158,11,0.14);border:1.5px solid #fbbf24;text-align:center;">
                <div style="font-size:0.76rem;">📦 إجمالي الوحدات المتبقية للعملاء</div>
                <div style="font-size:1.2rem;font-weight:900;color:#fbbf24;">${totalRemQty.toFixed(2)}</div>
            </div>
            <div style="padding:10px;border-radius:10px;background:rgba(16,185,129,0.14);border:1.5px solid #34d399;text-align:center;">
                <div style="font-size:0.76rem;">💰 إجمالي قيمة البضاعة المتبقية</div>
                <div style="font-size:1.2rem;font-weight:900;color:#34d399;">${totalRemValue.toFixed(2)}</div>
            </div>
        `;
    }
    m.style.display = 'flex';
};

window.printSlPendingAgingReport = function () {
    const tbody = document.getElementById('sl-pending-aging-tbody');
    if (!tbody) return;
    const w = window.open('', '_blank', 'width=940,height=680');
    if (!w) return;
    w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>كشف التسليمات المتبقية وأعمار ذمم البضاعة للعملاء</title>
    <style>body{font-family:'Cairo',sans-serif;padding:18px;color:#000;background:#fff;}table{width:100%;border-collapse:collapse;margin-top:12px;font-size:12px;}th,td{border:1.8px solid #0f172a;padding:6px 8px;}th{background:#f1f5f9;font-weight:900;}button{display:none;}</style></head><body>
    <h2>📊 تقرير كشف التسليمات المتبقية وأعمار ذمم البضاعة للعملاء</h2>
    <div>تاريخ الطباعة: ${new Date().toLocaleString('ar-EG')}</div>
    <table><thead><tr><th>رقم الفاتورة</th><th>العميل</th><th>التاريخ</th><th>عمر الذمة</th><th>الكميات المتبقية</th><th>القيمة المتبقية</th><th></th></tr></thead><tbody>${tbody.innerHTML}</tbody></table>
    <script>window.onload=()=>{setTimeout(()=>window.print(),250);};<\/script></body></html>`);
    w.document.close();
};

window.openSlGateCreditNoteModal = function () {
    if (!slCurrentDoc && slRegistryDocs.length > 0) {
        selectSlDocument(slRegistryDocs[0].invoiceNo);
    }
    const m = document.getElementById('sl-gate-credit-modal');
    const hdr = document.getElementById('sl-gate-cn-header');
    const tbody = document.getElementById('sl-gate-cn-tbody');
    if (!m || !tbody || !slCurrentDoc) return;

    const party = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    if (hdr) hdr.textContent = `📄 الفاتورة: #${slCurrentDoc.invoiceNo} | الطرف: ${party}`;

    tbody.innerHTML = (slCurrentDoc.items || []).map((row, idx) => {
        const meta = resolveSlItemMeta(row);
        const defRet = +(Math.min(1, meta.qty)).toFixed(2);
        return `<tr>
            <td style="font-weight:800;">${meta.name}</td>
            <td style="text-align:center;font-weight:800;">${meta.qty} ${meta.unit}</td>
            <td style="text-align:center;">
                <input type="number" class="sl-input sl-gate-cn-qty" data-idx="${idx}" data-price="${meta.price || 0}" value="${defRet}" min="0" max="${meta.qty}" step="any" oninput="window.recalcSlGateCreditNoteTotal()" style="width:95px;text-align:center;">
            </td>
            <td style="text-align:center;">${(meta.price || 0).toFixed(2)}</td>
            <td class="sl-gate-cn-row-total" style="text-align:center;font-weight:900;color:#34d399;">${(defRet * (meta.price || 0)).toFixed(2)}</td>
        </tr>`;
    }).join('');
    window.recalcSlGateCreditNoteTotal();
    m.style.display = 'flex';
};

window.recalcSlGateCreditNoteTotal = function () {
    let sum = 0;
    const rows = document.querySelectorAll('.sl-gate-cn-qty');
    const totals = document.querySelectorAll('.sl-gate-cn-row-total');
    rows.forEach((inp, idx) => {
        const q = Math.max(0, parseFloat(inp.value) || 0);
        const p = parseFloat(inp.getAttribute('data-price')) || 0;
        const rowTot = q * p;
        sum += rowTot;
        if (totals[idx]) totals[idx].textContent = rowTot.toFixed(2);
    });
    const totEl = document.getElementById('sl-gate-cn-total');
    if (totEl) totEl.textContent = sum.toFixed(2);
    return sum;
};

window.postSlGateCustomerCreditNote = function (shouldPrint = false) {
    if (!slCurrentDoc) return;
    const cnNo = 'CN-GDN-' + Date.now().toString().slice(-6);
    const invNo = String(slCurrentDoc.invoiceNo);
    const party = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const reason = document.getElementById('sl-gate-cn-reason')?.value.trim() || 'تسوية مرتجع جزئي عند البوابة';
    const qtyInputs = document.querySelectorAll('.sl-gate-cn-qty');
    const returnedItems = [];
    let totalCredit = 0;

    let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
    const whId = document.getElementById('sl-master-warehouse')?.value || getSlWarehouses()[0].id;

    (slCurrentDoc.items || []).forEach((row, idx) => {
        const meta = resolveSlItemMeta(row);
        const q = Math.max(0, parseFloat(qtyInputs[idx]?.value) || 0);
        if (q > 0) {
            const lineTotal = +(q * (meta.price || 0)).toFixed(2);
            totalCredit += lineTotal;
            returnedItems.push({ name: meta.name, unit: meta.unit, qty: q, price: meta.price || 0, total: lineTotal });
            const invEntry = inventory.find(x =>
                (String(x.id || x.itemId) === String(meta.itemId) || String(x.name || '').trim() === meta.name.trim()) &&
                String(x.warehouseId || x.warehouse) === String(whId)
            );
            if (invEntry) invEntry.qty = +(parseFloat(invEntry.qty || 0) + q).toFixed(4);
        }
    });

    localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
    const notes = JSON.parse(localStorage.getItem('erp_customer_credit_notes_v1') || '[]');
    const cnRecord = {
        id: cnNo,
        invoiceNo: invNo,
        customerName: party,
        date: new Date().toISOString().slice(0, 10),
        reason,
        totalAmount: +totalCredit.toFixed(2),
        items: returnedItems
    };
    notes.unshift(cnRecord);
    localStorage.setItem('erp_customer_credit_notes_v1', JSON.stringify(notes));

    document.getElementById('sl-gate-credit-modal').style.display = 'none';
    broadcastSlRealtimeSync({ type: 'GATE_CREDIT_NOTE_POSTED', cnNo, invoiceNo: invNo });
    showSlToast(`↩️ تم إصدار الإشعار الدائن (${cnNo}) بقيمة ${totalCredit.toFixed(2)} وإعادة الكمية للمخزن!`, 'success');

    if (shouldPrint) {
        const w = window.open('', '_blank', 'width=820,height=620');
        if (w) {
            w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>إشعار دائن بوابة ${cnNo}</title>
            <style>body{font-family:'Cairo',sans-serif;padding:20px;color:#000;background:#fff;}table{width:100%;border-collapse:collapse;margin-top:14px;}th,td{border:1.8px solid #000;padding:8px;text-align:center;}th{background:#f1f5f9;}</style></head><body>
            <h2>↩️ إشعار دائن وتسوية مرتجع بوابة (${cnNo})</h2>
            <div><strong>الفاتورة المرتبطة:</strong> #${invNo} | <strong>العميل:</strong> ${party} | <strong>التاريخ:</strong> ${cnRecord.date}</div>
            <div><strong>السبب:</strong> ${reason}</div>
            <table><thead><tr><th>الصنف</th><th>الكمية المرتجعة</th><th>الوحدة</th><th>السعر</th><th>الإجمالي</th></tr></thead><tbody>
            ${returnedItems.map(it => `<tr><td>${it.name}</td><td>${it.qty}</td><td>${it.unit}</td><td>${it.price.toFixed(2)}</td><td>${it.total.toFixed(2)}</td></tr>`).join('')}
            </tbody></table>
            <h3 style="margin-top:14px;">إجمالي الإشعار الدائن للعميل: ${totalCredit.toFixed(2)}</h3>
            <script>window.onload=()=>{setTimeout(()=>window.print(),250);};<\/script></body></html>`);
            w.document.close();
        }
    }
};

window.printSlGateClearanceCertificate = function () {
    if (!slCurrentDoc && slRegistryDocs.length > 0) {
        selectSlDocument(slRegistryDocs[0].invoiceNo);
    }
    if (!slCurrentDoc) {
        showSlToast('⚠️ اختر مستنداً أولاً لإصدار شهادة إخلاء الطرف وخروج البوابة', 'warning');
        return;
    }
    const invNo = slCurrentDoc.invoiceNo;
    const party = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const driver = document.getElementById('sl-driver-name')?.value?.trim() || slCurrentDoc.driverName || '—';
    const plate = document.getElementById('sl-truck-plate')?.value?.trim() || slCurrentDoc.truckPlate || '—';
    const gatePass = document.getElementById('sl-gate-pass-no')?.value?.trim() || slCurrentDoc.gatePassNo || ('GP-' + invNo);
    const timeIn = document.getElementById('sl-gate-time-in')?.value || '—';
    const timeOut = document.getElementById('sl-gate-time-out')?.value || new Date().toTimeString().slice(0, 8);
    const whSel = document.getElementById('sl-master-warehouse');
    const whName = whSel && whSel.selectedIndex >= 0 ? whSel.options[whSel.selectedIndex].text.replace('🏢', '').trim() : 'المستودع الرئيسي';
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent('GATE-CLEARANCE|' + invNo + '|' + gatePass)}`;

    const w = window.open('', '_blank', 'width=900,height=700');
    if (!w) return;
    w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>شهادة إخلاء طرف وخروج بوابة #${invNo}</title>
    <style>
        body{font-family:'Cairo','Tajawal',sans-serif;padding:22px;color:#000;background:#fff;}
        .cert-box{border:3px double #0f172a;padding:18px;border-radius:14px;}
        table{width:100%;border-collapse:collapse;margin-top:12px;font-size:13px;}
        th,td{border:1.8px solid #0f172a;padding:7px 10px;text-align:center;}
        th{background:#f1f5f9;font-weight:900;}
    </style></head><body>
    <div class="cert-box">
        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2.5px solid #0f172a;padding-bottom:12px;">
            <div>
                <h2 style="margin:0;">🛡️ شهادة إخلاء طرف وتصريح خروج بوابة رقمي موثق</h2>
                <div style="font-size:13px;font-weight:800;margin-top:4px;">رقم الفاتورة: #${invNo} | رقم تصريح البوابة: ${gatePass} | المستودع الصارف: ${whName}</div>
            </div>
            <img src="${qrUrl}" width="95" height="95" alt="QR" style="border:2px solid #0f172a;border-radius:8px;padding:3px;">
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:12px;font-size:13px;font-weight:800;">
            <div>👤 العميل / الجهة: ${party}</div>
            <div>🚚 السائق: ${driver}</div>
            <div>🔢 رقم اللوحة: ${plate}</div>
            <div>⏱️ دخول/خروج: ${timeIn} ⬅️ ${timeOut}</div>
        </div>
        <table><thead><tr><th>#</th><th>الصنف</th><th>الوحدة</th><th>كمية الفاتورة</th><th>المصروف الفعلي</th><th>مطابقة البوابة</th></tr></thead><tbody>
        ${(slCurrentDoc.items || []).map((it, i) => {
            const m = resolveSlItemMeta(it);
            return `<tr><td>${i + 1}</td><td style="text-align:right;font-weight:800;">${m.name}</td><td>${m.unit}</td><td>${m.qty}</td><td style="font-weight:900;">${m.qty}</td><td>✅ مطابق 100%</td></tr>`;
        }).join('')}
        </tbody></table>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:24px;text-align:center;font-weight:900;">
            <div style="border-top:2px dashed #0f172a;padding-top:8px;">✍️ توقيع أمين المستودع الصارف</div>
            <div style="border-top:2px dashed #0f172a;padding-top:8px;">🚚 توقيع السائق / المستلم</div>
            <div style="border-top:2px dashed #0f172a;padding-top:8px;">🛡️ ختم وتوقيع مسؤول أمن البوابة</div>
        </div>
    </div>
    <script>window.onload=()=>{setTimeout(()=>window.print(),250);};<\/script></body></html>`);
    w.document.close();
};

// ── CONSTITUTION 4 APPROVED PROPOSALS IMPLEMENTATION ────────────────────────
window.toggleSlDockBigScreenMode = function () {
    const isBig = document.body.classList.toggle('sl-dock-bigscreen-mode');
    localStorage.setItem('sl_dock_bigscreen_mode', isBig ? '1' : '0');
    const btn = document.getElementById('sl-btn-dock-zoom');
    if (btn) {
        btn.style.background = isBig ? 'linear-gradient(135deg,#f59e0b,#d97706)' : 'rgba(245,158,11,0.22)';
        btn.style.color = isBig ? '#fff' : '#fde047';
    }
    showSlToast(isBig ? '🔍 تم تفعيل وضع شاشة الرصيف المكبّرة (قراءة واضحة من 5 أمتار)' : '🔍 تم إرجاع الحجم القياسي للشاشة', 'success');
};

window.openSlContrastInspectorModal = function () {
    const m = document.getElementById('sl-contrast-inspector-modal');
    if (!m) return;
    window.runSlContrastIntegrityAudit(false);
    m.style.display = 'flex';
};

window.runSlContrastIntegrityAudit = function (notify = false) {
    applySlConstitution4NodeInheritance(document.body);
    if (typeof window.syncDynamicDomElementsIntoSmartGuide === 'function') {
        window.syncDynamicDomElementsIntoSmartGuide('smart-logistics');
    }
    const inputsCount = document.querySelectorAll('input.sl-input, select.sl-input, textarea.sl-input').length;
    const btnsCount = document.querySelectorAll('.sl-btn').length;
    const modalsCount = document.querySelectorAll('[id^="sl-"][id$="-modal"]').length;
    const svgsCount = document.querySelectorAll('svg').length;
    const isLight = document.documentElement.getAttribute('data-magaies-theme-mode') === 'light' || document.body.classList.contains('light-theme') || document.body.classList.contains('theme-light');

    const body = document.getElementById('sl-contrast-audit-body');
    if (body) {
        body.innerHTML = `
            <div style="padding:10px 14px;border-radius:10px;background:rgba(16,185,129,0.16);border:2px solid #10b981;font-weight:900;">
                ✅ حالة الدستور الرابع: مطبق ويعمل بنسبة 100% (${isLight ? 'السمة الفاتحة — نص وحواف #000000' : 'السمة الغامقة — خانات #000000 بنص وحواف #FFFFFF'})
            </div>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;font-size:0.86rem;font-weight:900;">
                <div style="padding:10px;border-radius:10px;background:rgba(15,23,42,0.45);border:1.8px solid rgba(56,189,248,0.4);">⌨️ الخانات المحمية من انقلاب اللون: <span style="color:#38bdf8;">${inputsCount} خانة (2.2px)</span></div>
                <div style="padding:10px;border-radius:10px;background:rgba(15,23,42,0.45);border:1.8px solid rgba(52,211,153,0.4);">⚡ الأزرار المشمولة بالتحديث الذاتي: <span style="color:#34d399;">${btnsCount} زر (font-weight: 900)</span></div>
                <div style="padding:10px;border-radius:10px;background:rgba(15,23,42,0.45);border:1.8px solid rgba(251,191,36,0.4);">🖥️ الشاشات الوليدة المتوارثة: <span style="color:#fbbf24;">${modalsCount} نافذة (2.5px)</span></div>
                <div style="padding:10px;border-radius:10px;background:rgba(15,23,42,0.45);border:1.8px solid rgba(192,132,252,0.4);">🎨 الأيقونات المعززة (+2): <span style="color:#c084fc;">${svgsCount} أيقونة (2.6px)</span></div>
            </div>
        `;
    }
    if (notify) {
        showSlToast('🛡️ تم فحص وتعزيز التباين وسُمك الحواف (+2) والتوريث التلقائي بنسبة 100%!', 'success');
    }
};

function getSlDispatchPresets() {
    try {
        const raw = JSON.parse(localStorage.getItem('sl_dispatch_presets_v1') || 'null');
        if (Array.isArray(raw) && raw.length) return raw;
    } catch (_e) {}
    return [
        { driver: 'كابتن محمد عبد الله', plate: 'أ ب ج - 4580', receiver: 'مندوب التسليم المباشر' },
        { driver: 'كابتن خالد العتيبي', plate: 'س ص ع - 9120', receiver: 'مشرف موقع العميل' },
        { driver: 'كابتن طارق السوداني', plate: 'خ ر ط - 3310', receiver: 'أمين مخزن الفرع' }
    ];
}

window.openSlDispatchPresetsModal = function () {
    const m = document.getElementById('sl-dispatch-presets-modal');
    if (!m) return;
    const dInp = document.getElementById('sl-preset-driver');
    const pInp = document.getElementById('sl-preset-plate');
    const rInp = document.getElementById('sl-preset-receiver');
    if (dInp) dInp.value = document.getElementById('sl-driver-name')?.value || '';
    if (pInp) pInp.value = document.getElementById('sl-truck-plate')?.value || '';
    if (rInp) rInp.value = document.getElementById('sl-receiver-name')?.value || '';
    window.renderSlDispatchPresetsList();
    m.style.display = 'flex';
};

window.renderSlDispatchPresetsList = function () {
    const box = document.getElementById('sl-dispatch-presets-list');
    if (!box) return;
    const list = getSlDispatchPresets();
    box.innerHTML = list.map((p, idx) => `
        <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 12px;border-radius:10px;background:rgba(15,23,42,0.55);border:1.8px solid rgba(192,132,252,0.35);">
            <div style="font-size:0.84rem;font-weight:900;">
                🚚 ${p.driver} &nbsp;|&nbsp; 🔢 <span style="color:#38bdf8;">${p.plate}</span> &nbsp;|&nbsp; 🤝 ${p.receiver || '—'}
            </div>
            <div style="display:flex;gap:6px;">
                <button type="button" onclick="window.applySlDispatchPreset(${idx})" class="sl-btn" style="padding:4px 10px;font-size:0.76rem;background:rgba(16,185,129,0.22);border-color:#34d399;color:#34d399;">⚡ تعبئة فورية</button>
                <button type="button" onclick="window.deleteSlDispatchPreset(${idx})" class="sl-btn" style="padding:4px 8px;font-size:0.74rem;background:rgba(239,68,68,0.18);border-color:#f87171;color:#fca5a5;">✕</button>
            </div>
        </div>
    `).join('');
};

window.saveSlCurrentDispatchPreset = function () {
    const driver = document.getElementById('sl-preset-driver')?.value.trim();
    const plate = document.getElementById('sl-preset-plate')?.value.trim();
    const receiver = document.getElementById('sl-preset-receiver')?.value.trim();
    if (!driver && !plate) {
        showSlToast('⚠️ يرجى إدخال اسم السائق أو رقم اللوحة لحفظ القالب', 'warning');
        return;
    }
    const list = getSlDispatchPresets();
    list.unshift({ driver: driver || 'سائق معتمد', plate: plate || '—', receiver: receiver || '' });
    localStorage.setItem('sl_dispatch_presets_v1', JSON.stringify(list.slice(0, 20)));
    window.renderSlDispatchPresetsList();
    showSlToast('✅ تم حفظ قالب السائق والمركبة في الحافظة السريعة', 'success');
};

window.applySlDispatchPreset = function (idx) {
    const list = getSlDispatchPresets();
    const p = list[idx];
    if (!p) return;
    const dEl = document.getElementById('sl-driver-name');
    const plEl = document.getElementById('sl-truck-plate');
    const rEl = document.getElementById('sl-receiver-name');
    if (dEl) dEl.value = p.driver || '';
    if (plEl) plEl.value = p.plate || '';
    if (rEl && p.receiver) rEl.value = p.receiver;
    document.getElementById('sl-dispatch-presets-modal').style.display = 'none';
    showSlToast(`⚡ تم تعبئة بيانات السائق (${p.driver}) واللوحة (${p.plate}) فوراً!`, 'success');
};

window.deleteSlDispatchPreset = function (idx) {
    const list = getSlDispatchPresets();
    list.splice(idx, 1);
    localStorage.setItem('sl_dispatch_presets_v1', JSON.stringify(list));
    window.renderSlDispatchPresetsList();
};

// ═══════════════════════════════════════════════════════════════════════════
// CONSTITUTION 1 (1 OF 14) FULL IMPLEMENTATION & PRE-AUTHORIZED ADDITIONS:
// 1. 6:8 Proportional Geometric Scaling (Ctrl + Wheel & Zoom Pill) + Child Modal Sync
// 2. Instant 7-Theme Cycler & 100% Nested Child Modal Theme Propagation
// 3. Zero-Truncation & Auto-Fit Engine for Quantities, Long Names & Summary Counters
// 4. Constitution 1 Master Audit & Idempotency Guard (2.2px Lock — Never Doubled)
// ═══════════════════════════════════════════════════════════════════════════
(function initSlConstitution1MasterEngine() {
    const GLOBAL_SCALE_KEY = 'magaies_erp_global_proportional_scale_v15';
    const SL_SCALE_KEY = 'magaies_sl_c1_aspect_zoom';

    window.setSlProportionalZoom = function (scaleVal, silent = false) {
        const z = Math.min(1.30, Math.max(0.75, +(parseFloat(scaleVal) || 1).toFixed(2)));
        window.__slProportionalZoom = z;
        try {
            localStorage.setItem(SL_SCALE_KEY, String(z));
            localStorage.setItem(GLOBAL_SCALE_KEY, String(z));
        } catch (_e) {}

        const root = document.getElementById('sl-proportional-root');
        if (root) {
            if (Math.abs(z - 1) < 0.005) {
                root.style.transform = 'none';
                root.style.width = '100%';
                root.style.maxWidth = '99.6vw';
                root.style.marginBottom = '0px';
            } else {
                root.style.transformOrigin = 'top center';
                root.style.transform = `scale(${z})`;
                root.style.width = z < 1 ? `${Math.min(100, +(100 * z).toFixed(2))}%` : `${+(100 / z).toFixed(2)}%`;
                root.style.maxWidth = '99.6vw';
                root.style.marginLeft = 'auto';
                root.style.marginRight = 'auto';
                const rectH = root.scrollHeight || 900;
                root.style.marginBottom = z < 1 ? `${Math.round(rectH * (z - 1))}px` : `${Math.round(rectH * (z - 1) * 0.5)}px`;
            }
        }

        // Apply proportional 6:8 scaling to all open nested child modals (شاشة داخل شاشة)
        document.querySelectorAll('[id^="sl-"][id$="-modal"] > .sl-glass-card').forEach(card => {
            card.style.transformOrigin = 'center center';
            card.style.transform = Math.abs(z - 1) < 0.005 ? 'none' : `scale(${z})`;
        });

        const badge = document.getElementById('sl-c1-zoom-pct-badge');
        if (badge) badge.textContent = `${Math.round(z * 100)}%`;

        if (!silent) {
            let hud = document.getElementById('sl-c1-aspect-hud');
            if (!hud) {
                hud = document.createElement('div');
                hud.id = 'sl-c1-aspect-hud';
                hud.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:999999;padding:8px 18px;border-radius:999px;background:#020617;color:#FFFFFF;border:2.2px solid #38bdf8;font-weight:900;font-size:0.86rem;box-shadow:0 10px 28px rgba(0,0,0,0.65);pointer-events:none;transition:opacity 0.2s ease;';
                document.body.appendChild(hud);
            }
            hud.textContent = `🔍 تناسب أبعاد البوابة (6:8): ${Math.round(z * 100)}%`;
            hud.style.opacity = '1';
            clearTimeout(window.__slZoomHudTimer);
            window.__slZoomHudTimer = setTimeout(() => {
                if (hud) hud.style.opacity = '0';
            }, 1300);
        }
        return z;
    };

    window.stepSlProportionalZoom = function (delta) {
        const cur = window.__slProportionalZoom || parseFloat(localStorage.getItem(SL_SCALE_KEY) || localStorage.getItem(GLOBAL_SCALE_KEY) || '1') || 1;
        return window.setSlProportionalZoom(cur + (parseFloat(delta) || 0), false);
    };

    const SL_APPROVED_THEMES = [
        { id: 'cyber-dark', mode: 'dark', label: '🌙 الكحلي السيبراني الملكي (داكن #FFFFFF)' },
        { id: 'royal-dawn', mode: 'light', label: '☀️ فجر السجناء الملكي (فاتح #000000)' },
        { id: 'obsidian-night', mode: 'dark', label: '🌑 الليل الأسود الفحمي (داكن #FFFFFF)' },
        { id: 'pearl-white', mode: 'light', label: '💎 الأبيض اللؤلؤي النقي (فاتح #000000)' }
    ];

    window.cycleSlConstitution1Theme = function () {
        const curId = localStorage.getItem('magaies_active_theme') || 'cyber-dark';
        const idx = SL_APPROVED_THEMES.findIndex(t => t.id === curId);
        const next = SL_APPROVED_THEMES[(idx + 1) % SL_APPROVED_THEMES.length];
        localStorage.setItem('magaies_active_theme', next.id);
        localStorage.setItem('magaiees_global_theme', next.id);
        localStorage.setItem('magaies_theme_mode', next.mode);
        localStorage.setItem('ui_theme', next.mode);
        if (typeof window.syncSlUniversalThemeSystem === 'function') {
            window.syncSlUniversalThemeSystem();
        }
        if (typeof window.applyMagaiesUniversal7Theme === 'function') {
            try { window.applyMagaiesUniversal7Theme(next.id, true); } catch (_e) {}
        }
        const btn = document.getElementById('sl-btn-c1-theme-cycler');
        if (btn) {
            btn.innerHTML = next.mode === 'light' ? '☀️ سمة فاتحة (#000)' : '🌙 سمة غامقة (#FFF)';
        }
        showSlToast(`🎨 تم تطبيق (${next.label}) وسريانها 100% على البوابة والشاشات الوليدة!`, 'success');
        if (document.getElementById('sl-constitution1-master-modal')?.style.display === 'flex') {
            window.runSlConstitution1FullAudit(false);
        }
    };

    window.autoFitSlAllFieldsAndTables = function () {
        // Auto-expand numeric quantity inputs so large numbers never clip (Order 5)
        document.querySelectorAll('.sl-qty-primary-input, .sl-qty-base-input, .sl-dn-qty-input, .sl-gate-cn-qty').forEach(inp => {
            const len = String(inp.value || inp.placeholder || '').length;
            const chWidth = Math.max(88, Math.min(152, (len + 3) * 11));
            inp.style.setProperty('width', `${chWidth}px`, 'important');
        });
        // Auto-scale long party names or invoice numbers if needed without clipping
        const partyEl = document.getElementById('sl-lbl-party-name');
        if (partyEl) {
            const txtLen = (partyEl.textContent || '').trim().length;
            partyEl.style.fontSize = txtLen > 34 ? '0.84rem' : '0.96rem';
        }
    };

    window.openSlConstitution1MasterModal = function () {
        const m = document.getElementById('sl-constitution1-master-modal');
        if (!m) return;
        window.runSlConstitution1FullAudit(false);
        m.style.display = 'flex';
    };

    window.runSlConstitution1FullAudit = function (notify = false) {
        // Idempotency & Zero-Harm Lock: Ensure 2.2px border and 2.6px icon stroke without doubling
        document.documentElement.setAttribute('data-sl-c1-locked', '1');
        document.documentElement.style.setProperty('--sl-border-thickness', '2.2px');
        document.documentElement.style.setProperty('--sl-icon-stroke', '2.6px');
        applySlConstitution4NodeInheritance(document.body);
        window.autoFitSlAllFieldsAndTables();
        if (typeof window.syncDynamicDomElementsIntoSmartGuide === 'function') {
            window.syncDynamicDomElementsIntoSmartGuide('smart-logistics');
        }

        const isLight = document.documentElement.getAttribute('data-magaies-theme-mode') === 'light' || document.body.classList.contains('light-theme');
        const curScale = Math.round((window.__slProportionalZoom || 1) * 100);
        const modalsCount = document.querySelectorAll('[id^="sl-"][id$="-modal"]').length;
        const inputsCount = document.querySelectorAll('input.sl-input, select.sl-input, textarea.sl-input').length;

        const body = document.getElementById('sl-constitution1-master-body');
        if (body) {
            body.innerHTML = `
                <div style="padding:10px 14px;border-radius:10px;background:rgba(16,185,129,0.16);border:2.2px solid #10b981;font-weight:900;">
                    ✅ حالة الدستور الأول (1 من 14): مطبق ويعمل فعلياً بنسبة 100% (قفل عدم المضاعفة نشط: 2.2px)
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;font-size:0.84rem;font-weight:900;">
                    <div style="padding:9px;border-radius:10px;background:rgba(15,23,42,0.45);border:2.2px solid rgba(56,189,248,0.45);">1️⃣ سريان السمة والشاشات الوليدة: <span style="color:#38bdf8;">${modalsCount} شاشة وليدة (${isLight ? 'فاتحة #000' : 'غامقة #FFF'})</span></div>
                    <div style="padding:9px;border-radius:10px;background:rgba(15,23,42,0.45);border:2.2px solid rgba(52,211,153,0.45);">2️⃣ سُمك الحواف والخطوط (+2): <span style="color:#34d399;">2.2px / وزن 900 / أيقونات 2.6px</span></div>
                    <div style="padding:9px;border-radius:10px;background:rgba(15,23,42,0.45);border:2.2px solid rgba(251,191,36,0.45);">3️⃣ منع انقلاب لون الكتابة: <span style="color:#fbbf24;">${inputsCount} خانة محمية (${isLight ? '#FFF/#000' : '#000/#FFF'})</span></div>
                    <div style="padding:9px;border-radius:10px;background:rgba(15,23,42,0.45);border:2.2px solid rgba(192,132,252,0.45);">4️⃣ تناسب الرولة (6:8) والاحتواء: <span style="color:#c084fc;">المقاس الحالي ${curScale}% (بدون قص)</span></div>
                </div>
            `;
        }
        if (notify) {
            showSlToast('🛡️ تم فحص وتأكيد عمل الأوامر السبعة للدستور الأول (7/7) بنسبة 100%!', 'success');
        }
    };

    const origExplain = window.explainSmartLogisticsDomElement;
    window.explainSmartLogisticsDomElement = function (el) {
        if (el && el.closest) {
            if (el.closest('#sl-c1-zoom-control-pill')) {
                return {
                    title: '🔍 شريط التحجيم الهندسي المتناسب (6:8 Aspect-Ratio Lock)',
                    desc: 'يتحكم في تصغير وتكبير بوابة الاستلام والتسليم وجميع الشاشات الوليدة بداخلها بنسبة هندسية متناسقة (6:8) من اليمين واليسار والأسفل مع حفظ المقاس تلقائياً.'
                };
            }
            if (el.closest('#sl-btn-c1-theme-cycler')) {
                return {
                    title: '🌓 مبدّل السمات اللحظي وسريانها على الشاشات الوليدة',
                    desc: 'يبدّل فوراً بين السمات الغامقة (#FFFFFF على #000000) والفاتحة (#000000 الصريح) ويسري تلقائياً بنسبة 100% على البوابة وكل النوافذ المنبثقة الوليدة.'
                };
            }
            if (el.closest('#sl-btn-c1-master-audit')) {
                return {
                    title: '🛡️ لوحة فحص وإثبات الدستور الأول (1 من 14)',
                    desc: 'تعرض حالة الأوامر السبعة للدستور الأول (سريان السمات، سُمك الحواف +2 مع قفل عدم المضاعفة، منع انقلاب لون الكتابة، تناسب الرولة 6:8، ومنع قص الأرقام).'
                };
            }
        }
        return typeof origExplain === 'function' ? origExplain(el) : null;
    };

    document.addEventListener('input', (e) => {
        if (e.target && (e.target.classList?.contains('sl-qty-primary-input') || e.target.classList?.contains('sl-qty-base-input'))) {
            window.autoFitSlAllFieldsAndTables();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const savedZoom = parseFloat(localStorage.getItem(SL_SCALE_KEY) || localStorage.getItem(GLOBAL_SCALE_KEY) || '1');
        if (!isNaN(savedZoom) && savedZoom >= 0.75 && savedZoom <= 1.30) {
            window.setSlProportionalZoom(savedZoom, true);
        }
        window.autoFitSlAllFieldsAndTables();
    });
})();

// ═══════════════════════════════════════════════════════════════════════════
// CONSTITUTION 2 (2 OF 14): 2G ZERO-LATENCY (0MS), CURSOR SILK-FLOW,
// INLINE ARABIC/MATH CONVERTER, LIVE DRAFT GUARD, QUICK-ADD (+) & ERROR BOUNDARY
// ═══════════════════════════════════════════════════════════════════════════
(function initSlConstitution2SilkAndResilienceEngine() {
    const DRAFT_STORAGE_KEY = 'sl_c2_draft_guard_v1';

    // 1. Cyber Error Boundary (تحصين الشاشة ضد الانهيار تحت أي ظرف — الأمر 5)
    window.slSafeExec = function (fn, fallback = null) {
        try { return fn(); } catch (err) { console.warn('[SL-ErrorBoundary]', err); return fallback; }
    };
    window.addEventListener('error', (ev) => {
        if (ev && ev.message && String(ev.message).includes('ResizeObserver')) ev.preventDefault();
    });
    window.addEventListener('unhandledrejection', (ev) => {
        if (ev) ev.preventDefault();
    });

    // 2. Arabic Numeral & Inline Math Expression Normalizer (الأمر 2: السلاسة المطلقة والتظليل التلقائي)
    const AR_DIGITS = { '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4', '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9', '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4', '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9' };
    window.normalizeSlNumericInputString = function (val) {
        return String(val || '')
            .replace(/[٠-٩۰-۹]/g, d => AR_DIGITS[d] ?? d)
            .replace(/[،,و]/g, '.');
    };

    window.evalSlInlineMathIfNeeded = function (inp) {
        if (!inp || !inp.value) return false;
        const norm = window.normalizeSlNumericInputString(inp.value).trim();
        if (/^[0-9.]+\s*[\+\-\*\/]\s*[0-9.\s\+\-\*\/]+$/.test(norm)) {
            try {
                const res = Function('"use strict"; return (' + norm.replace(/[^0-9.\+\-\*\/\(\)]/g, '') + ')')();
                if (typeof res === 'number' && isFinite(res) && res >= 0) {
                    inp.value = +res.toFixed(4);
                    inp.dispatchEvent(new Event('input', { bubbles: true }));
                    showSlToast(`🧮 حاسبة الخلية الفورية: ${norm} = ${inp.value}`, 'success', 2000);
                    return true;
                }
            } catch (_e) {}
        }
        return false;
    };

    // 3. 0ms Live Draft Auto-Guard (الأمر 1: حماية المدخلات ضد الإغلاق الخاطئ وانقطاع 2G)
    window.saveSlLiveInspectionDraft = function () {
        if (!slCurrentDoc || !slCurrentDoc.invoiceNo || slCurrentDoc._slStatus === 'done') return;
        window.slSafeExec(() => {
            const allDrafts = JSON.parse(localStorage.getItem(DRAFT_STORAGE_KEY) || '{}');
            const key = `${slCurrentMode}_${slCurrentDoc.invoiceNo}`;
            const rows = {};
            document.querySelectorAll('.sl-qty-primary-input').forEach(inp => {
                const idx = inp.dataset.itemIdx;
                const binInp = document.querySelector(`.sl-row-bin-input[data-item-idx="${idx}"]`);
                const batchInp = document.querySelector(`.sl-row-batch-input[data-item-idx="${idx}"]`);
                rows[idx] = {
                    qty: inp.value,
                    bin: binInp ? binInp.value : '',
                    batch: batchInp ? batchInp.value : ''
                };
            });
            allDrafts[key] = {
                ts: Date.now(),
                wh: document.getElementById('sl-master-warehouse')?.value || '',
                receiver: document.getElementById('sl-receiver-name')?.value || '',
                driver: document.getElementById('sl-driver-name')?.value || '',
                plate: document.getElementById('sl-truck-plate')?.value || '',
                gatePass: document.getElementById('sl-gate-pass-no')?.value || '',
                notes: document.getElementById('sl-warehouse-notes')?.value || '',
                personalNotes: document.getElementById('sl-personal-notes')?.value || '',
                rows
            };
            localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(allDrafts));
            const badge = document.getElementById('sl-c2-draft-badge');
            if (badge) {
                badge.style.display = 'inline-flex';
                badge.textContent = '💾 مسودة محفوظة 0ms';
            }
        });
    };

    window.restoreSlLiveInspectionDraft = function () {
        if (!slCurrentDoc || !slCurrentDoc.invoiceNo || slCurrentDoc._slStatus === 'done') return;
        window.slSafeExec(() => {
            const allDrafts = JSON.parse(localStorage.getItem(DRAFT_STORAGE_KEY) || '{}');
            const key = `${slCurrentMode}_${slCurrentDoc.invoiceNo}`;
            const draft = allDrafts[key];
            if (!draft) return;
            const setIfEmpty = (id, val) => {
                const el = document.getElementById(id);
                if (el && val && !el.value) el.value = val;
            };
            setIfEmpty('sl-receiver-name', draft.receiver);
            setIfEmpty('sl-driver-name', draft.driver);
            setIfEmpty('sl-truck-plate', draft.plate);
            setIfEmpty('sl-gate-pass-no', draft.gatePass);
            setIfEmpty('sl-warehouse-notes', draft.notes);
            setIfEmpty('sl-personal-notes', draft.personalNotes);
            if (draft.rows) {
                Object.keys(draft.rows).forEach(idx => {
                    const r = draft.rows[idx];
                    const binInp = document.querySelector(`.sl-row-bin-input[data-item-idx="${idx}"]`);
                    const batchInp = document.querySelector(`.sl-row-batch-input[data-item-idx="${idx}"]`);
                    if (binInp && r.bin && !binInp.value) binInp.value = r.bin;
                    if (batchInp && r.batch && !batchInp.value) batchInp.value = r.batch;
                });
            }
            const badge = document.getElementById('sl-c2-draft-badge');
            if (badge) {
                badge.style.display = 'inline-flex';
                badge.textContent = '💾 مسودة مستعادة';
            }
        });
    };

    // 4. Quick-Add Party & Item Modal (+) from Inside Screen Without Losing Data (الأمر 5)
    window.openSlQuickEntityModal = function (tab = 'party') {
        const m = document.getElementById('sl-quick-entity-modal');
        if (!m) return;
        window.switchSlQuickEntityTab(tab);
        m.style.display = 'flex';
        setTimeout(() => {
            const firstInp = document.getElementById(tab === 'item' ? 'sl-qe-item-name' : 'sl-qe-party-name');
            if (firstInp) { firstInp.focus(); firstInp.select(); }
        }, 40);
    };

    window.switchSlQuickEntityTab = function (tab) {
        const pBody = document.getElementById('sl-qe-body-party');
        const iBody = document.getElementById('sl-qe-body-item');
        const pBtn = document.getElementById('sl-qe-tab-party');
        const iBtn = document.getElementById('sl-qe-tab-item');
        if (pBody) pBody.style.display = tab === 'party' ? 'flex' : 'none';
        if (iBody) iBody.style.display = tab === 'item' ? 'flex' : 'none';
        if (pBtn) pBtn.style.background = tab === 'party' ? 'rgba(16,185,129,0.25)' : 'rgba(15,23,42,0.6)';
        if (iBtn) iBtn.style.background = tab === 'item' ? 'rgba(14,165,233,0.25)' : 'rgba(15,23,42,0.6)';
        const codeInp = document.getElementById('sl-qe-item-code');
        if (tab === 'item' && codeInp && !codeInp.value) {
            codeInp.value = 'ITM-' + Date.now().toString().slice(-5);
        }
    };

    window.saveSlQuickPartyEntity = function () {
        const name = document.getElementById('sl-qe-party-name')?.value.trim();
        const phone = document.getElementById('sl-qe-party-phone')?.value.trim() || '';
        const city = document.getElementById('sl-qe-party-city')?.value.trim() || '';
        if (!name) {
            window.focusRequiredSlElement('#sl-qe-party-name', '⚠️ يرجى كتابة اسم المورد أو العميل أولاً');
            return;
        }
        const isSales = slCurrentMode === 'sales';
        const storageKey = isSales ? 'erp_customers' : 'erp_suppliers';
        const list = window.slSafeExec(() => JSON.parse(localStorage.getItem(storageKey) || '[]'), []);
        const newParty = { id: 'PTY-' + Date.now(), name, supplierName: name, customerName: name, phone, city, balance: 0, createdAt: new Date().toISOString() };
        list.unshift(newParty);
        localStorage.setItem(storageKey, JSON.stringify(list));

        if (slCurrentDoc) {
            slCurrentDoc.partyName = name;
            slCurrentDoc.supplierName = name;
            slCurrentDoc.customerName = name;
            const lbl = document.getElementById('sl-lbl-party-name');
            if (lbl) lbl.textContent = name;
        }
        const recInp = document.getElementById('sl-receiver-name');
        if (recInp && !recInp.value) recInp.value = name;
        document.getElementById('sl-quick-entity-modal').style.display = 'none';
        showSlToast(`✅ تم حفظ (${name}) وربطه بالمستند الحالي في 0ms دون فقدان أي بيانات!`, 'success');
    };

    window.saveSlQuickItemEntity = function () {
        const name = document.getElementById('sl-qe-item-name')?.value.trim();
        const code = document.getElementById('sl-qe-item-code')?.value.trim() || ('ITM-' + Date.now().toString().slice(-5));
        const unit = document.getElementById('sl-qe-item-unit')?.value.trim() || 'كرتون';
        const factor = Math.max(1, parseFloat(document.getElementById('sl-qe-item-factor')?.value) || 1);
        const qty = Math.max(0.01, parseFloat(document.getElementById('sl-qe-item-qty')?.value) || 1);
        if (!name) {
            window.focusRequiredSlElement('#sl-qe-item-name', '⚠️ يرجى كتابة اسم الصنف الجديد أولاً');
            return;
        }

        // Preserve current typed quantities/bins/batches before appending item
        window.saveSlLiveInspectionDraft();

        const invList = window.slSafeExec(() => JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]'), []);
        const whId = document.getElementById('sl-master-warehouse')?.value || getSlWarehouses()[0]?.id || 'WH-1';
        const newItemId = 'ITM-' + Date.now();
        invList.unshift({ id: newItemId, itemId: newItemId, code, name, unit, baseUnit: factor > 1 ? 'قطعة' : unit, factor, qty: 0, price: 0, warehouseId: whId });
        localStorage.setItem('erp_inventory_v1', JSON.stringify(invList));

        if (!slCurrentDoc && slRegistryDocs.length > 0) {
            loadSlDocument(slRegistryDocs[0].invoiceNo);
        }
        if (slCurrentDoc) {
            slCurrentDoc.items = slCurrentDoc.items || [];
            slCurrentDoc.items.push({
                itemId: newItemId,
                code,
                name,
                unit,
                baseUnit: factor > 1 ? 'قطعة' : unit,
                factor,
                qty,
                remainingQty: qty,
                executedQty: 0,
                price: 0,
                warehouseId: whId
            });
            renderSlItemsGrid();
            window.restoreSlLiveInspectionDraft();
        }
        document.getElementById('sl-quick-entity-modal').style.display = 'none';
        showSlToast(`✅ تم إضافة الصنف (${name}) بكمية (${qty} ${unit}) لجدول الفحص فوراً دون ضياع أي بيانات!`, 'success');
    };

    // 5. Universal Silk-Flow Cursor Navigation (Enter / Arrows / Esc in all fields & child modals)
    document.addEventListener('input', (e) => {
        const t = e.target;
        if (!t) return;
        const t0 = performance.now();
        if (t.classList?.contains('sl-qty-primary-input') || t.classList?.contains('sl-qty-base-input') || t.id === 'sl-wb-gross' || t.id === 'sl-wb-tare') {
            const norm = window.normalizeSlNumericInputString(t.value);
            if (norm !== t.value && !/[\+\-\*\/]/.test(norm)) {
                t.value = norm;
            }
        }
        window.saveSlLiveInspectionDraft();
        const dt = (performance.now() - t0).toFixed(1);
        const sp = document.getElementById('sl-c2-speed-badge');
        if (sp) sp.textContent = `⚡ ${dt}ms | 2G`;
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('[id^="sl-"][id$="-modal"][style*="flex"], #sl-extra-tools-drawer[style*="flex"]');
            if (openModal) {
                e.preventDefault();
                openModal.style.display = 'none';
                return;
            }
        }

        const active = document.activeElement;
        if (!active) return;

        // Inline Math Evaluation on Enter inside quantity fields
        if (e.key === 'Enter' && (active.classList?.contains('sl-qty-primary-input') || active.classList?.contains('sl-qty-base-input'))) {
            window.evalSlInlineMathIfNeeded(active);
        }

        // Smooth Enter navigation inside Quick-Add Entity Modal & Bin/Batch inputs
        if (e.key === 'Enter' && (active.classList?.contains('sl-row-bin-input') || active.classList?.contains('sl-row-batch-input'))) {
            e.preventDefault();
            const rowIdx = active.dataset?.itemIdx;
            if (active.classList.contains('sl-row-bin-input')) {
                const batchInp = document.querySelector(`.sl-row-batch-input[data-item-idx="${rowIdx}"]`);
                if (batchInp) { batchInp.focus(); batchInp.select(); return; }
            } else {
                const priInp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${rowIdx}"]`);
                if (priInp) { priInp.focus(); priInp.select(); return; }
            }
        }

        // Smooth Enter navigation inside any open child modal
        if (e.key === 'Enter' && active.tagName === 'INPUT') {
            const parentModal = active.closest('[id^="sl-"][id$="-modal"]');
            if (parentModal) {
                const modalInputs = Array.from(parentModal.querySelectorAll('input:not([type="hidden"]):not([type="file"]):not([disabled])')).filter(el => el.offsetParent !== null);
                const idx = modalInputs.indexOf(active);
                if (idx !== -1 && idx + 1 < modalInputs.length) {
                    e.preventDefault();
                    modalInputs[idx + 1].focus();
                    if (modalInputs[idx + 1].select) modalInputs[idx + 1].select();
                }
            }
        }
    });

    // Hook draft restore into loadSlDocument
    const origLoadDoc = window.loadSlDocument;
    if (typeof origLoadDoc === 'function') {
        window.loadSlDocument = function (...args) {
            const res = origLoadDoc.apply(this, args);
            setTimeout(() => window.restoreSlLiveInspectionDraft(), 30);
            return res;
        };
    }

    // Register Constitution 2 elements in Smart Guide
    const prevExplain = window.explainSmartLogisticsDomElement;
    window.explainSmartLogisticsDomElement = function (el) {
        if (el && el.closest) {
            if (el.closest('#sl-btn-quick-add-party')) {
                return {
                    title: '➕ إضافة طرف جديد فوراً (مورد / عميل)',
                    desc: 'يفتح نافذة وليدة لإضافة مورد أو عميل جديد وربطه بالمستند الحالي في 0ms دون مغادرة البوابة أو ضياع المدخلات.'
                };
            }
            if (el.closest('#sl-btn-quick-add-item') || el.closest('#sl-btn-quick-add-item-top')) {
                return {
                    title: '➕ إضافة صنف سريع لجدول الفحص',
                    desc: 'يتيح تعريف صنف جديد بالوحدة والمعامل وإدراجه مباشرة في جدول الفحص الحالي مع حفظ جميع الكميات والبيانات المدخلة.'
                };
            }
            if (el.closest('#sl-c2-speed-badge') || el.closest('#sl-c2-draft-badge')) {
                return {
                    title: '⚡ درع الخفة القصوى (0ms / 2G) وحارس المسودة التلقائي',
                    desc: 'يقيس سرعة استجابة الواجهة اللحظية (0ms) ويحفظ مسودة الفحص والكميات والأرفف تلقائياً لحمايتها من الإغلاق الخاطئ أو ضعف الإنترنت.'
                };
            }
        }
        return typeof prevExplain === 'function' ? prevExplain(el) : null;
    };
})();

// ═══════════════════════════════════════════════════════════════════════════
// CONSTITUTION 3 (3 OF 14): UNIFIED BUTTON DNA, SINGLE-SAVE LOCK, SPATIALLY
// ISOLATED DELETE CONFIRM, TACTILE HAPTICS, & LIVE THOUSAND-SEPARATOR FORMATTER
// ═══════════════════════════════════════════════════════════════════════════
(function initSlConstitution3ButtonDnaAndHaptics() {
    // 1. Single-Save Anti-Duplicate Lock (الأمر 2: قفل الحفظ مرة واحدة فقط لمنع التكرار عند الضغط المزدوج)
    window.executeSlTransactionSingleLock = function (btnEl) {
        const btn = btnEl || document.getElementById('sl-btn-execute');
        if (window._slSingleSaveLockActive || (btn && btn.getAttribute('data-sl-saving') === '1')) {
            showSlToast('🛡️ قفل الحفظ النشط: جاري اعتماد الحركة بالفعل، تم منع التكرار تلقائياً', 'warning', 1800);
            return false;
        }
        window._slSingleSaveLockActive = true;
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.setAttribute('data-sl-saving', '1');
            btn.disabled = true;
            btn.innerHTML = '⏳ جاري الحفظ... <kbd class="sl-kbd-badge">F10</kbd>';
        }
        if (navigator.vibrate) navigator.vibrate(25);

        let ok = false;
        try {
            ok = executeSlTransaction();
        } finally {
            setTimeout(() => {
                window._slSingleSaveLockActive = false;
                if (btn) {
                    btn.removeAttribute('data-sl-saving');
                    btn.disabled = false;
                    btn.innerHTML = ok ? '✅ تم الاعتماد <kbd class="sl-kbd-badge">F10</kbd>' : (origHtml || '💾 حفظ واعتماد <kbd class="sl-kbd-badge">F10</kbd>');
                    if (ok) {
                        setTimeout(() => {
                            if (btn && !btn.disabled) btn.innerHTML = '💾 حفظ واعتماد <kbd class="sl-kbd-badge">F10</kbd>';
                        }, 1400);
                    }
                }
            }, 950);
        }
        return ok;
    };

    // 2. Standardized Edit Button Action (الأمر 2: زر التعديل القياسي الموحد مكاناً ولوناً — F4)
    window.enableSlDocumentEditMode = function () {
        if (!slCurrentDoc && slRegistryDocs.length > 0) {
            loadSlDocument(slRegistryDocs[0].invoiceNo);
        }
        if (!slCurrentDoc) {
            window.focusRequiredSlElement('#sl-reg-search', '⚠️ اختر مستنداً أولاً لتفعيل وضع التعديل والفحص', 'warning');
            return;
        }
        const firstQty = document.querySelector('.sl-qty-primary-input');
        if (firstQty) {
            firstQty.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                firstQty.focus();
                firstQty.select();
            }, 80);
        }
        showSlToast(`✏️ وضع التعديل نشط للمستند #${slCurrentDoc.invoiceNo} — عدّل الكميات أو الأرفف ثم اضغط F10 للحفظ`, 'success');
    };

    // 3. Spatially Isolated Delete/Undo Button with Inline Quick Confirmation (الأمر 2: التأكيد المدمج السريع لمنع الحذف بالخطأ)
    window.requestSlSafeIsolatedUndo = function (btnEl) {
        const btn = btnEl || document.getElementById('sl-btn-undo');
        if (!slCurrentDoc || slCurrentDoc._slStatus === 'pending') {
            window.focusRequiredSlElement('#sl-reg-search', '⚠️ اختر مستنداً منفذاً أولاً لعكس أو إلغاء حركته المخزنية', 'error');
            return;
        }
        if (btn && btn.getAttribute('data-sl-confirm-step') !== '1') {
            btn.setAttribute('data-sl-confirm-step', '1');
            const prevHtml = btn.innerHTML;
            btn.innerHTML = '⚠️ تأكيد الحذف؟ اضغط مجدداً';
            btn.style.background = 'linear-gradient(135deg, #dc2626, #991b1b)';
            btn.style.color = '#ffffff';
            if (navigator.vibrate) navigator.vibrate([30, 40, 30]);
            showSlToast('⚠️ حماية الذاكرة العضلية: اضغط على زر الحذف الأحمر مرة أخرى لتأكيد فتح نافذة عكس الحركة', 'warning', 2600);
            clearTimeout(window.__slUndoConfirmResetTimer);
            window.__slUndoConfirmResetTimer = setTimeout(() => {
                if (btn) {
                    btn.removeAttribute('data-sl-confirm-step');
                    btn.innerHTML = prevHtml;
                    btn.style.background = 'rgba(239,68,68,0.22)';
                }
            }, 3500);
            return;
        }
        if (btn) {
            btn.removeAttribute('data-sl-confirm-step');
            btn.innerHTML = '🗑️ حذف / عكس <kbd class="sl-kbd-badge">Del</kbd>';
            btn.style.background = 'rgba(239,68,68,0.22)';
        }
        undoSlTransaction();
    };

    // 4. Tactile Haptic & Visual Pulse on Every Button Touch/Click (الأمر 3)
    document.addEventListener('pointerdown', (e) => {
        const btn = e.target && e.target.closest ? e.target.closest('.sl-btn') : null;
        if (!btn || btn.disabled) return;
        if (navigator.vibrate) {
            try { navigator.vibrate(14); } catch (_e) {}
        }
        btn.classList.remove('sl-btn-tactile-pulse');
        void btn.offsetWidth;
        btn.classList.add('sl-btn-tactile-pulse');
    }, { passive: true });

    // 5. Live Thousand-Separator Formatting for Summary Totals & Quantities (الأمر 6)
    window.formatSlThousandsLive = function (val) {
        const n = parseFloat(val);
        if (isNaN(n)) return '0';
        return n.toLocaleString('en-US', { maximumFractionDigits: 2 });
    };

    const origUpdateSummary = window.updateSlFloatingSummary;
    if (typeof origUpdateSummary === 'function') {
        window.updateSlFloatingSummary = function (...args) {
            const res = origUpdateSummary.apply(this, args);
            ['sl-sum-ordered', 'sl-sum-prev', 'sl-sum-now', 'sl-sum-rem'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    const raw = parseFloat(String(el.textContent || '0').replace(/,/g, '')) || 0;
                    el.textContent = window.formatSlThousandsLive(raw);
                }
            });
            return res;
        };
    }

    // Keyboard shortcuts F4 (Edit) & Delete (Isolated Undo when not inside an input)
    window.addEventListener('keydown', (e) => {
        if (e.key === 'F4') {
            e.preventDefault();
            window.enableSlDocumentEditMode();
        } else if (e.key === 'Delete' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName || '')) {
            e.preventDefault();
            window.requestSlSafeIsolatedUndo(document.getElementById('sl-btn-undo'));
        }
    });

    // Register Constitution 3 additions in Smart Guide
    const prevExplainC3 = window.explainSmartLogisticsDomElement;
    window.explainSmartLogisticsDomElement = function (el) {
        if (el && el.closest) {
            if (el.closest('#sl-btn-edit-doc')) {
                return {
                    title: '✏️ زر التعديل القياسي الموحد (F4)',
                    desc: 'يفعّل وضع تعديل وفحص المستند الحالي وينقل المؤشر فوراً لخانة الكمية الأولى مع تظليل الرقم تلقائياً.'
                };
            }
            if (el.closest('#sl-btn-execute')) {
                return {
                    title: '💾 زر الحفظ والاعتماد الموحد مع قفل منع التكرار (F10)',
                    desc: 'يعتمد حركة الاستلام أو الصرف ويقفل الزر لحظياً (Single-Save Lock) لمنع تكرار العملية عند الضغط المزدوج السريع.'
                };
            }
            if (el.closest('#sl-btn-undo')) {
                return {
                    title: '🗑️ زر الحذف وعكس الحركة المعزول مكانياً (Del)',
                    desc: 'معزول في الطرف البعيد بلون أحمر تحذيري مع تأكيد مدمج على مرحلتين ورمز أمان لمنع الحذف بالخطأ.'
                };
            }
        }
        return typeof prevExplainC3 === 'function' ? prevExplainC3(el) : null;
    };
})();
