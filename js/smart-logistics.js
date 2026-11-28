// ============================================================================
// ELSAJANA ERP — ICONIC UNIFIED SMART LOGISTICS ENGINE (GRN ⇄ GDN)
// Visual Symmetry with Strict Functional & Accounting Isolation
// ============================================================================

let slCurrentMode = (new URLSearchParams(window.location.search).get('mode') === 'purchases' || (document.body && document.body.getAttribute('data-logistics-mode') === 'purchases')) ? 'purchases' : 'sales'; // 'sales' | 'purchases'
let slRegistryFilter = 'pending'; // 'pending' | 'partial' | 'done' | 'notes' | 'all'
let slCurrentDoc = null;
let slDeductionLevel = parseInt(localStorage.getItem('sl_deduction_level_v1') || '1', 10); // 1: Manager, 2: Warehouse Mgr, 3: Storekeeper QR
let slStorekeeperFocus = false;
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
function showSlToast(message, type = 'success', duration = 3500) {
    if (window.ElsajanaAccountingBrain && typeof window.ElsajanaAccountingBrain.floatingToast === 'function') {
        window.ElsajanaAccountingBrain.floatingToast(message, type, duration);
        return;
    }
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : '⚠️';
    toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
    }, duration);
}

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

// ── FAST IN-MEMORY CACHE FOR LOCALSTORAGE (PREVENTS 1000x JSON.parse IN LOOPS) ──
const _slStoreCache = {};
function getSlCachedStore(key, fallback = []) {
    const now = Date.now();
    const hit = _slStoreCache[key];
    if (hit && (now - hit.ts) < 1800) {
        return hit.data;
    }
    let parsed = fallback;
    try {
        const raw = localStorage.getItem(key);
        parsed = raw ? JSON.parse(raw) : fallback;
    } catch (e) {
        parsed = fallback;
    }
    _slStoreCache[key] = { ts: now, data: parsed };
    return parsed;
}
function invalidateSlStoreCache() {
    for (const k in _slStoreCache) delete _slStoreCache[k];
}

// ── WAREHOUSE, PARTY & ITEM META RESOLUTION ──────────────────────────────────
function getSlWarehouses() {
    let whs = getSlCachedStore('erp_warehouses_v1', []);
    if (!Array.isArray(whs)) whs = [];
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
        const contacts = getSlCachedStore('erp_contacts_v1', []);
        const suppliers = getSlCachedStore('erp_suppliers_v1', []);
        const found = [...contacts, ...suppliers].find(c => c && (String(c.id) === String(raw) || String(c.name) === String(raw)));
        return found ? found.name : (raw || 'مورد عام');
    } else {
        const raw = inv.customerName || inv.clientName || inv.customer || '';
        const customers = getSlCachedStore('erp_customers_v1', []);
        const contacts = getSlCachedStore('erp_contacts_v1', []);
        const found = [...customers, ...contacts].find(c => c && (String(c.id) === String(raw) || String(c.name) === String(raw)));
        return found ? found.name : (raw || 'عميل نقدي');
    }
}

function resolveSlItemMeta(it) {
    if (!it) it = {};
    const erpItems = getSlCachedStore('erp_items_v1', []);
    const itemId = it.itemId || it.id || '';
    const rawName = String(it.itemName || it.name || '').trim();
    const normStr = (s) => String(s || '').trim()
        .replace(/[أإآ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/ى/g, 'ي')
        .replace(/\s+/g, ' ');

    const normRawName = normStr(rawName);
    const master = erpItems.find(x =>
        x && (
            (itemId && String(x.id) === String(itemId)) ||
            (rawName && String(x.name || '').trim() === rawName) ||
            (normRawName && normStr(x.name) === normRawName)
        )
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
        const uNorm = normStr(unit);
        if (/شيل/.test(uNorm)) {
            factor = /4\s*لين|10\s*مم|12\s*مم/.test(n) ? 10 : (/5\s*لين|16\s*مم/.test(n) ? 5 : 15);
        } else if (/طن/.test(uNorm)) {
            if (/4\s*لين|10\s*مم|12\s*مم/.test(n)) factor = /وزني/.test(unit) ? 110 : 92;
            else if (/5\s*لين|16\s*مم/.test(n)) factor = /وزني/.test(unit) ? 59 : 52;
            else if (/اسمنت/.test(n)) factor = 20;
            else if (/سيخ|حديد|لينيه|لينية|تسليح|3\s*لين|8\s*مم|8\s*ملي/.test(n)) factor = /وزني/.test(unit) ? 140 : 135;
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

    let tonRefHint = '';
    const nCheck = normStr(name);
    if (/سيخ|حديد|لينيه|لينية|تسليح/.test(nCheck) && (/طن|شيل/.test(normStr(unit)))) {
        if (/4\s*لين|10\s*مم|12\s*مم/.test(nCheck)) tonRefHint = 'طن عددي = 92 سيخة | طن وزني = 110 سيخة | شيلة = 10';
        else if (/5\s*لين|16\s*مم/.test(nCheck)) tonRefHint = 'طن عددي = 52 سيخة | طن وزني = 59 سيخة | شيلة = 5';
        else tonRefHint = 'معيار السجانة: طن عددي = 135 سيخة | طن وزني = 140 سيخة | شيلة = 15 سيخة';
    }
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
        hasMultiUnits,
        tonRefHint
    };
}

function getSlItemStockInWarehouse(itemMeta, whIdOrName) {
    const whs = getSlWarehouses();
    const whObj = whs.find(w => String(w.id) === String(whIdOrName) || String(w.name) === String(whIdOrName)) || whs[0];
    const invList = getSlCachedStore('erp_inventory_v1', []);
    const erpItems = getSlCachedStore('erp_items_v1', []);

    const entry = invList.find(x =>
        x &&
        (String(x.itemId || x.id) === String(itemMeta.itemId) || String(x.name || '').trim() === itemMeta.name.trim()) &&
        (String(x.warehouseId || x.warehouse) === String(whObj.id) || String(x.warehouse || x.warehouseId) === String(whObj.name))
    );
    if (entry && entry.qty !== undefined) {
        return +parseFloat(entry.qty || 0).toFixed(2);
    }

    const master = erpItems.find(x => x && (String(x.id) === String(itemMeta.itemId) || String(x.name || '').trim() === itemMeta.name.trim()));
    if (master && master.stock && master.stock[whObj.id] !== undefined) {
        return +parseFloat(master.stock[whObj.id] || 0).toFixed(2);
    }
    return 0;
}

function _slSeqNum(no) {
    const s = String(no || '').trim();
    const m = s.match(/^[A-Za-z]+(\d+)$/);
    if (m) return parseInt(m[1], 10);
    const d = s.replace(/\D/g, '');
    return d ? parseInt(d, 10) : 0;
}

// ── PURCHASE PENDING & RECEIVED CALCULATION ENGINE (100% COMPATIBLE WITH purchase-receipt.php) ──
function getProcessedPurchaseInvoices() {
    const nowTs = Date.now();
    if (_slStoreCache._procPur && (nowTs - _slStoreCache._procPur.ts) < 1200) return _slStoreCache._procPur.data;
    const deletedGrnSet = new Set((getSlCachedStore('erp_deleted_grns_v1', []) || []).map(String));
    const mergedMap = new Map();
    ['erp_purchase_invoices_v1', 'erp_purchases_v5', 'magaies_local_purchase_invoices_v1'].forEach(k => {
        try {
            (getSlCachedStore(k, []) || []).forEach(p => {
                if (!p || typeof p !== 'object') return;
                const pid = String(p.invoiceNo || p.id || '').trim();
                if (!pid || (p.isGRN && deletedGrnSet.has(pid))) return;
                const prev = mergedMap.get(pid);
                if (prev && (prev.deleted || prev.isDeleted)) return;
                mergedMap.set(pid, prev ? Object.assign({}, prev, p) : p);
            });
        } catch (e) {}
    });
    const allPurchases = Array.from(mergedMap.values());
    const grnList = allPurchases.filter(p => p && !p.deleted && !p.isDeleted && p.isGRN && !deletedGrnSet.has(String(p.id || '')));
    const returnList = allPurchases.filter(p => p && !p.deleted && !p.isDeleted && p.isReturn);

    // Map of originalInvoiceId -> { itemKey: receivedQty }
    const receivedByInv = {};
    grnList.forEach(grn => {
        const rootId = String(grn.originalInvoiceId || '');
        if (!rootId) return;
        if (!receivedByInv[rootId]) receivedByInv[rootId] = { totalReceived: 0, byItem: {}, grns: [] };
        receivedByInv[rootId].grns.push(grn);
        (grn.items || grn.lines || []).forEach(line => {
            const q = parseFloat(line.quantity ?? line.qty ?? 0) || 0;
            if (q <= 0 || String(line.warehouseId || '') === 'PENDING_RECEIPT') return;
            const key = String(line.itemId || line.id || line.name || line.itemName || 'صنف');
            receivedByInv[rootId].byItem[key] = (receivedByInv[rootId].byItem[key] || 0) + q;
            receivedByInv[rootId].totalReceived += q;
        });
    });

    const baseInvoices = allPurchases.filter(inv => inv && !inv.deleted && !inv.isGRN && !inv.isReturn && inv.status !== 'draft' && inv.status !== 'proforma');

    const resList = baseInvoices.map(inv => {
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
            _slCompletedAt: latestGrn ? (latestGrn.savedAt || latestGrn.date || '') : (inv.savedAt || inv.date || ''),
            _slGrns: recInfo.grns
        };
    });
    _slStoreCache._procPur = { ts: nowTs, data: resList };
    return resList;
}

function getProcessedSalesInvoices() {
    const rawSales = getSlCachedStore('erp_sales_invoices_v1', []);
    const allGdns = (getSlCachedStore('erp_delivery_notes_v1', []) || []).filter(n => n && !n.deleted && !n.isDeleted);
    const gdnByInv = {};
    allGdns.forEach(g => {
        const k = String(g.invoiceNo || g.relatedInvoice || '');
        if (!k) return;
        if (!gdnByInv[k]) gdnByInv[k] = [];
        gdnByInv[k].push(g);
    });
    const allSales = (Array.isArray(rawSales) ? rawSales : []).filter(i => i && !i.deleted && i.status !== 'draft' && i.status !== 'proforma');
    return allSales.map(inv => {
        const invNo = String(inv.invoiceNo || inv.id || '');
        const invGdns = gdnByInv[invNo] || [];
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

        const latestGdn = invGdns[invGdns.length - 1];
        return {
            ...inv,
            invoiceNo: invNo,
            items: enrichedItems,
            _slStatus: calcStatus,
            _slNoteNo: (latestGdn && (latestGdn.id || latestGdn.deliveryNo)) || inv.deliveryNo || '',
            _slCompletedAt: (latestGdn && (latestGdn.createdAt || latestGdn.date)) || inv.deliveredAt || inv.deliveryDate || inv.date || '',
            _slGrns: invGdns
        };
    });
}

// ── MODE SWITCHER (CHAMELEON VISUAL + FUNCTIONAL ISOLATION) ──────────────────
function switchLogisticsMode(mode, silent = false) {
    if (mode !== 'purchases' && mode !== 'sales') {
        const urlMode = new URLSearchParams(window.location.search).get('mode');
        const bodyMode = document.body ? document.body.getAttribute('data-logistics-mode') : '';
        mode = (urlMode === 'purchases' || bodyMode === 'purchases') ? 'purchases' : 'sales';
    }
    slCurrentMode = mode;
    localStorage.setItem('sl_active_mode_v1', mode);
    document.body.setAttribute('data-logistics-mode', mode);

    document.getElementById('sl-tab-sales')?.classList.toggle('active', mode === 'sales');
    document.getElementById('sl-tab-purchases')?.classList.toggle('active', mode === 'purchases');

    const isSales = (mode === 'sales');

    // Update dynamic labels across the entire cockpit (concise & clean)
    const setTxt = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
    setTxt('sl-main-title-icon', isSales ? '📤' : '📥');
    setTxt('sl-main-title-text', isSales
        ? 'تسليم وتقسيم مبيعات'
        : 'استلام المشتريات');
    setTxt('sl-reg-wh-filter-lbl', isSales ? 'مخزن المبيعات:' : 'مخزن المشتريات:');
    const srchIn = document.getElementById('sl-reg-search');
    if (srchIn) srchIn.placeholder = isSales ? '🔍 بحث برقم الفاتورة أو العميل أو الصنف...' : '🔍 بحث برقم الفاتورة أو المورد أو الصنف...';
    const pendBtn = document.getElementById('sl-filter-btn-pending');
    if (pendBtn) {
        const tn = Array.from(pendBtn.childNodes).find(n => n.nodeType === 3);
        const lbl = isSales ? 'قيد التسليم ' : 'قيد الاستلام ';
        if (tn) tn.nodeValue = lbl;
    }
    setTxt('sl-kpi-lbl-pending', isSales ? '⏳ قيد التسليم' : '⏳ قيد الاستلام');
    setTxt('sl-kpi-lbl-partial', isSales ? '🌗 مسلّمة جزئياً' : '🌗 مستلمة جزئياً');
    setTxt('sl-kpi-lbl-done', isSales ? '✅ مكتملة التسليم' : '✅ مكتملة الاستلام');
    setTxt('sl-kpi-lbl-notes', isSales ? '📊 سجل أذونات التسليم (GDN)' : '📊 سجل مذكرات الاستلام (GRN)');

    setTxt('sl-registry-title', isSales
        ? 'سجل فواتير وأذونات المبيعات'
        : 'سجل فواتير وأذونات المشتريات');
    setTxt('sl-reg-th-party', isSales ? '👤 العميل' : '🏢 المورد');
    setTxt('sl-lbl-party-title', isSales ? '👤 العميل' : '🏢 المورد');
    setTxt('sl-lbl-wh-select', isSales ? 'مخزن التسليم:' : 'مخزن الاستلام:');
    setTxt('sl-lbl-receiver', isSales ? 'المستلم:' : 'المخزنجي:');
    setTxt('sl-lbl-rec-no-title', isSales ? 'رقم التسليم:' : 'رقم الاستلام:');
    setTxt('sl-lbl-rec-date-title', isSales ? 'تاريخ التسليم:' : 'تاريخ الاستلام:');
    setTxt('sl-th-dual-action', isSales
        ? '📤 الكمية المسلّمة الآن (أساسية ⇄ فرعية)'
        : '📥 الكمية المستلمة الآن (أساسية ⇄ فرعية)');
    setTxt('sl-summary-mode-badge', isSales ? '📤 تسليم المبيعات (GDN)' : '📥 استلام المشتريات (GRN)');

    const btnExec = document.getElementById('sl-btn-execute');
    if (btnExec) {
        btnExec.innerHTML = isSales
            ? '🚚 اعتماد التسليم وخصم المخزون'
            : '📥 اعتماد الاستلام وإضافة المخزون';
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
            ? '📤 تم التبديل إلى منظومة تسليم وتقسيم المبيعات (صادر GDN)'
            : '📥 تم التبديل إلى منظومة استلام وتقسيم المشتريات (وارد GRN)', 'success', 2200);
    }

    slCurrentDoc = null;
    clearSlDocumentCard();
    populateSlWarehouseSelects();
    renderSlRegistry();
}

function resolveSlWarehouseKeeper(whIdOrName) {
    const q = String(whIdOrName || '').trim();
    if (!q) return '';
    let allWh = getSlWarehouses();
    try {
        const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_warehouses_v1') : 'erp_warehouses_v1';
        const tWh = JSON.parse(localStorage.getItem(tk) || localStorage.getItem('erp_warehouses_v1') || '[]');
        if (Array.isArray(tWh) && tWh.length) allWh = tWh;
    } catch (_) {}
    const found = allWh.find(w => w && (String(w.id) === q || String(w.name) === q));
    return found ? String(found.keeper || found.storekeeper || found.manager || found.responsible || '').trim() : '';
}

function populateSlWarehouseSelects() {
    const whs = getSlWarehouses();
    const masterSel = document.getElementById('sl-master-warehouse');
    const filterSel = document.getElementById('sl-reg-wh-filter');

    if (masterSel) {
        const prev = masterSel.value;
        masterSel.innerHTML = whs.map(w => `<option value="${w.id}">${w.name}</option>`).join('');
        if (prev && whs.some(w => String(w.id) === String(prev))) masterSel.value = prev;
        if (slCurrentMode === 'purchases') {
            const recIn = document.getElementById('sl-receiver-name');
            if (recIn && !recIn.value) recIn.value = resolveSlWarehouseKeeper(masterSel.value);
        }
    }
    if (filterSel) {
        const defaultF = 'PENDING_RECEIPT';
        const prevF = filterSel.dataset.userPicked ? filterSel.value : defaultF;
        filterSel.innerHTML = `<option value="PENDING_RECEIPT">${slCurrentMode === 'sales' ? '⏳ قيد التسليم' : '⏳ قيد الاستلام'}</option><option value="all">🏢 كل المخازن</option>` + whs.map(w => `<option value="${w.id}">${w.name}</option>`).join('');
        filterSel.value = prevF;
        if (!filterSel._slUserPickBound) {
            filterSel._slUserPickBound = true;
            filterSel.addEventListener('change', () => { filterSel.dataset.userPicked = '1'; });
        }
    }
}

function onSlMasterWarehouseChange(newWhId) {
    if (slCurrentMode === 'purchases') {
        const recIn = document.getElementById('sl-receiver-name');
        if (recIn) recIn.value = resolveSlWarehouseKeeper(newWhId);
    }
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
            btn.classList.toggle('sl-flt-active', f === filter);
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
    if (typeof window.renderSlRegColsDrawer === 'function') window.renderSlRegColsDrawer();
}

function renderSlRegistry() {
    const isSales = (slCurrentMode === 'sales');
    const salesList = isSales ? getProcessedSalesInvoices() : [];
    const purchasesList = !isSales ? getProcessedPurchaseInvoices() : [];

    // Update top mode switcher badges without unnecessary cross-mode full scans
    const salesPendingCount = isSales ? salesList.filter(i => i._slStatus === 'pending' || i._slStatus === 'partial').length : 0;
    const purPendingCount = !isSales ? purchasesList.filter(i => i._slStatus === 'pending' || i._slStatus === 'partial').length : 0;
    const sb = document.getElementById('sl-tab-sales-badge');
    const pb = document.getElementById('sl-tab-purchases-badge');
    if (sb && isSales) sb.textContent = salesPendingCount;
    if (pb && !isSales) pb.textContent = purPendingCount;

    const sourceList = isSales ? salesList : purchasesList;

    const validInvSet = new Set(sourceList.map(i => String(i.invoiceNo || i.id || '')));
    const allNotes = isSales
        ? (getSlCachedStore('erp_delivery_notes_v1', []) || []).filter(n => n && !n.deleted && !n.isDeleted)
        : (getSlCachedStore('erp_purchase_invoices_v1', []) || []).filter(n => n && !n.deleted && !n.isDeleted && n.isGRN && (!n.originalInvoiceId || validInvSet.has(String(n.originalInvoiceId))) && (n.items || []).some(it => (parseFloat(it.quantity ?? it.qty ?? 0) || 0) > 0));

    const q = (document.getElementById('sl-reg-search')?.value || '').trim().toLowerCase();
    const whFilter = document.getElementById('sl-reg-wh-filter')?.value || 'all';
    const limit = parseInt(document.getElementById('sl-reg-limit')?.value || '50', 10) || 50;
    const whs = getSlWarehouses();

    const matchesWhFilter = (whVal, rawInv) => {
        if (whFilter === 'all') return true;
        if (whFilter === 'PENDING_RECEIPT') {
            if (slRegistryFilter && slRegistryFilter !== 'pending') return true;
            const wStr = String(whVal || rawInv?.warehouse || '');
            const wId = String(rawInv?.warehouseId || '');
            return rawInv?._slStatus === 'pending' || rawInv?._slStatus === 'partial' || wStr.includes('قيد الاستلام') || wStr === 'PENDING_RECEIPT' || wId === 'PENDING_RECEIPT';
        }
        const whObj = whs.find(w => String(w.id) === String(whFilter));
        return !!(whObj && (String(whVal || '').includes(whObj.name) || String(whVal || '') === String(whObj.id) || String(rawInv?.warehouseId || '') === String(whObj.id)));
    };

    const kpiBaseSource = (whFilter === 'all' || whFilter === 'PENDING_RECEIPT')
        ? sourceList
        : sourceList.filter(inv => matchesWhFilter(inv.deliveredWarehouseName || inv.warehouseName || inv.warehouse || '', inv));
    const whFilteredSource = sourceList.filter(inv => matchesWhFilter(inv.deliveredWarehouseName || inv.warehouseName || inv.warehouse || '', inv));
    const whFilteredNotes = allNotes.filter(n => whFilter === 'all' || whFilter === 'PENDING_RECEIPT' ? true : matchesWhFilter(n.warehouse || n.warehouseName || '', n));

    const buildDoneBatchRows = (srcArr) => {
        const out = [];
        srcArr.filter(inv => inv._slStatus === 'done' || inv._slStatus === 'partial').forEach(inv => {
            const rawItems = inv.items || [];
            const origMap = {};
            rawItems.forEach(ln => {
                const k = String(ln.itemId || ln.id || ln.name || ln.itemName || 'صنف');
                origMap[k] = { q: (origMap[k]?.q || 0) + (parseFloat(ln.qty ?? ln.quantity ?? 0) || 0), p: parseFloat(ln.price ?? ln.unitPrice ?? 0) || 0, unit: ln.unitName || ln.selectedUnit || ln.unit || '' };
            });
            const batches = (!isSales ? (Array.isArray(inv._slGrns) ? inv._slGrns : []) : whFilteredNotes.filter(n => String(n.invoiceNo || n.relatedInvoice || '') === String(inv.invoiceNo)))
                .slice().sort((a, b) => (Date.parse(a.savedAt || a.date || a.createdAt || '') || 0) - (Date.parse(b.savedAt || b.date || b.createdAt || '') || 0));
            if (batches.length > 0) {
                const cum = {};
                batches.forEach((b, bIdx) => {
                    const pos = (b.items || b.lines || []).filter(it => (parseFloat(it.quantity ?? it.qty ?? 0) || 0) > 0 && String(it.warehouseId || '') !== 'PENDING_RECEIPT');
                    if (!pos.length) return;
                    const whRaw = b.warehouseName || pos[0]?.warehouseName || b.warehouse || inv.deliveredWarehouseName || inv.warehouseName || whs[0].name;
                    const whObjMatch = whs.find(w => String(w.id) === String(whRaw) || String(w.name) === String(whRaw));
                    const whDisp = whObjMatch ? whObjMatch.name : whRaw;
                    if (whFilter !== 'all' && whFilter !== 'PENDING_RECEIPT' && !matchesWhFilter(whDisp, b)) return;
                    const bItems = pos.map(it => {
                        const k = String(it.itemId || it.id || it.name || it.itemName || 'صنف');
                        const bQ = parseFloat(it.quantity ?? it.qty ?? 0) || 0;
                        const oQ = origMap[k]?.q || parseFloat(it.origQty || bQ) || bQ;
                        cum[k] = (cum[k] || 0) + bQ;
                        const remAfter = Math.max(0, +(oQ - cum[k]).toFixed(4));
                        return { ...it, origInvQty: oQ, qty: oQ, quantity: oQ, receivedQty: bQ, deliveredQty: bQ, remainingQty: remAfter, price: parseFloat(it.price || origMap[k]?.p || 0) || 0 };
                    });
                    const nid = b.id || b.deliveryNo || inv._slNoteNo || '';
                    const anyRem = bItems.some(x => x.remainingQty > 0.0001);
                    const isSp = batches.length > 1 || anyRem || !!b.isSplitPart || String(nid).includes('/');
                    const spNum = b.splitSeq || (String(nid).includes('/') ? String(nid).split('/').pop() : (bIdx + 1));
                    const rootNid = String(b.rootSplitNo || nid || '').split('/')[0];
                    const dispNid = isSp && rootNid && !String(nid).includes('/') ? `${rootNid}/${spNum}` : nid;
                    out.push({ isOfficialNote: false, hasSingleNote: !!nid, isSplitRow: isSp, splitTag: isSp ? `تقسيم /${spNum}` : 'كامل', batchSeq: bIdx + 1, totalBatches: batches.length, noteId: nid, allNoteIds: dispNid ? [dispNid] : [], invoiceNo: inv.invoiceNo, date: (b.date || b.savedAt || b.createdAt || inv.date || '').slice(0, 10), recSortTs: Date.parse(b.savedAt || b.date || b.createdAt || '') || 0, invSortTs: Date.parse(inv.savedAt || inv.date || '') || 0, time: b.time || '', partyName: resolveSlPartyName(inv, slCurrentMode), warehouse: whDisp, items: bItems, _slStatus: anyRem ? 'partial' : 'done', _viewMode: 'done_part', rawInv: inv, rawNote: b });
                });
            } else {
                out.push({ isOfficialNote: false, hasSingleNote: false, isSplitRow: false, splitTag: 'كامل', batchSeq: 1, totalBatches: 1, noteId: inv._slNoteNo || '', allNoteIds: inv._slNoteNo ? [inv._slNoteNo] : [], invoiceNo: inv.invoiceNo, date: (inv._slCompletedAt || inv.date || inv.savedAt || '').slice(0, 10), recSortTs: Date.parse(inv._slCompletedAt || '') || 0, invSortTs: Date.parse(inv.savedAt || inv.date || '') || 0, time: inv.time || '', partyName: resolveSlPartyName(inv, slCurrentMode), warehouse: inv.deliveredWarehouseName || inv.warehouseName || inv.warehouse || whs[0].name, items: rawItems, _slStatus: 'done', _viewMode: 'done_part', rawInv: inv });
            }
        });
        return out;
    };
    const doneBatchRowsKpi = buildDoneBatchRows(kpiBaseSource);
    const kpiPending = kpiBaseSource.filter(i => i._slStatus === 'pending').length;
    const kpiPartial = kpiBaseSource.filter(i => i._slStatus === 'partial').length;
    const kpiDone    = doneBatchRowsKpi.length;
    const kpiNotes   = whFilteredNotes.length;

    const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    setVal('sl-kpi-val-pending', kpiPending);
    setVal('sl-kpi-val-partial', kpiPartial);
    setVal('sl-kpi-val-done', kpiDone);
    setVal('sl-kpi-val-notes', kpiNotes);

    const tbody = document.getElementById('sl-registry-tbody');
    if (!tbody) return;

    // If 'notes' filter is selected, show official GDN / GRN notes
    let rowsData = [];
    if (slRegistryFilter === 'notes') {
        rowsData = whFilteredNotes.map(n => {
            const rawNid = n.id || n.deliveryNo || '---';
            const isSp = !!n.isSplitPart || !!n.isPartialDelivery || String(rawNid).includes('/');
            const spNum = n.splitSeq || (String(rawNid).includes('/') ? String(rawNid).split('/').pop() : 1);
            return {
                isOfficialNote: true,
                isSplitRow: isSp,
                splitTag: isSp ? `تقسيم /${spNum}` : 'كامل',
                noteId: rawNid,
                allNoteIds: [rawNid],
                invoiceNo: n.invoiceNo || n.originalInvoiceId || n.id,
                date: (n.date || n.savedAt || n.createdAt || '').slice(0, 10),
                recSortTs: Date.parse(n.savedAt || n.date || n.createdAt || '') || 0,
                invSortTs: Date.parse(n.date || n.createdAt || '') || 0,
                time: n.time || '',
                partyName: isSales ? (n.customerName || n.customer || 'عميل') : resolveSlPartyName(n, 'purchases'),
                warehouse: n.warehouseName || n.warehouse || 'المخزن الرئيسي',
                items: (n.items || []).filter(it => (parseFloat(it.quantity ?? it.qty ?? 0) || 0) > 0),
                _slStatus: 'done',
                _viewMode: 'note',
                rawNote: n
            };
        });
    } else if (slRegistryFilter === 'done') {
        rowsData = buildDoneBatchRows(whFilteredSource);
    } else {
        rowsData = whFilteredSource.filter(inv => {
            if (slRegistryFilter === 'pending' && inv._slStatus !== 'pending') return false;
            if (slRegistryFilter === 'partial' && inv._slStatus !== 'partial') return false;
            return true;
        }).map(inv => {
            const useCompDt = (inv._slStatus === 'done' || inv._slStatus === 'partial') && inv._slCompletedAt;
            const grnIds = Array.isArray(inv._slGrns) && inv._slGrns.length ? inv._slGrns.map(g => g.id).filter(Boolean) : (inv._slNoteNo ? [inv._slNoteNo] : []);
            const isPartTab = (slRegistryFilter === 'partial');
            const vMode = isPartTab ? 'rem_part' : 'orig_full';
            const rawItems = inv.items || [];
            const viewItems = isPartTab ? rawItems.filter(it => (parseFloat(it.remainingQty ?? 0) || 0) > 0.0001) : rawItems;
            const rootBase = String(inv.rootSplitNo || grnIds[0] || inv._slNoteNo || '').split('/')[0];
            const nextSeq = grnIds.length + 1;
            const partNextBadge = isPartTab ? [rootBase ? `${rootBase}/${nextSeq}` : `دفعة /${nextSeq}`] : grnIds;
            return {
                isOfficialNote: false,
                isSplitRow: inv._slStatus === 'partial' || grnIds.length > 1,
                splitTag: isPartTab ? `تقسيم /${nextSeq}` : (inv._slStatus === 'pending' ? '—' : (grnIds.length > 1 || inv._slStatus === 'partial' ? `1..${grnIds.length}` : 'كامل')),
                noteId: isPartTab ? '' : (grnIds.length ? grnIds[grnIds.length - 1] : (inv._slNoteNo || '')),
                allNoteIds: partNextBadge,
                invoiceNo: inv.invoiceNo,
                date: (useCompDt ? inv._slCompletedAt : (inv.date || inv.savedAt || '')).slice(0, 10),
                recSortTs: Date.parse(inv._slCompletedAt || '') || 0,
                invSortTs: Date.parse(inv.savedAt || inv.date || '') || 0,
                time: inv.time || '',
                partyName: resolveSlPartyName(inv, slCurrentMode),
                warehouse: inv.deliveredWarehouseName || inv.warehouseName || inv.warehouse || whs[0].name,
                items: viewItems.length ? viewItems : rawItems,
                _slStatus: inv._slStatus,
                _viewMode: vMode,
                rawInv: inv
            };
        });
    }

    const sortByReceipt = (slRegistryFilter === 'done' || slRegistryFilter === 'partial' || slRegistryFilter === 'notes');
    const filtered = rowsData.filter(r => {
        if (!q) return true;
        const itemsTxt = (r.items || []).map(it => resolveSlItemMeta(it).name).join(' ').toLowerCase();
        const notesTxt = (r.allNoteIds || []).join(' ').toLowerCase();
        return (
            String(r.invoiceNo).toLowerCase().includes(q) ||
            String(r.noteId).toLowerCase().includes(q) ||
            notesTxt.includes(q) ||
            String(r.partyName).toLowerCase().includes(q) ||
            itemsTxt.includes(q)
        );
    }).sort((a, b) => {
        if (sortByReceipt) {
            const hasA = !!(a.noteId && String(a.noteId).trim() && a.noteId !== '---');
            const hasB = !!(b.noteId && String(b.noteId).trim() && b.noteId !== '---');
            if (hasA !== hasB) return hasB ? 1 : -1;
            if (hasA && hasB) {
                const ra = _slSeqNum(a.noteId);
                const rb = _slSeqNum(b.noteId);
                if (ra !== rb) return rb - ra;
            }
            if ((b.recSortTs || 0) !== (a.recSortTs || 0)) return (b.recSortTs || 0) - (a.recSortTs || 0);
        } else {
            const ia = _slSeqNum(a.invoiceNo);
            const ib = _slSeqNum(b.invoiceNo);
            if (ia !== ib) return ib - ia;
            if ((b.invSortTs || 0) !== (a.invSortTs || 0)) return (b.invSortTs || 0) - (a.invSortTs || 0);
        }
        return String(b.invoiceNo || '').localeCompare(String(a.invoiceNo || ''), undefined, { numeric: true });
    });

    window._slLastFilteredRows = filtered;
    const countBadge = document.getElementById('sl-reg-count-badge');
    if (countBadge) countBadge.textContent = `${filtered.length} مستند`;

    const selEl = document.getElementById('sl-reg-limit');
    const isShowAll = window._slPageShowAll === true || (selEl && selEl.value === 'all');
    const pageSize = 10;
    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
    if (typeof window.slCurrentPage !== 'number' || window.slCurrentPage < 1) window.slCurrentPage = 1;
    if (window.slCurrentPage > totalPages) window.slCurrentPage = totalPages;

    if (selEl) {
        let opts = '';
        for (let p = 1; p <= totalPages; p++) {
            const sel = (!isShowAll && p === window.slCurrentPage) ? 'selected' : '';
            opts += `<option value="p_${p}" ${sel}>الصفحة رقم ${p}</option>`;
        }
        opts += `<option value="all" ${isShowAll ? 'selected' : ''}>الكل (${filtered.length})</option>`;
        selEl.innerHTML = opts;
    }

    const startIdx = isShowAll ? 0 : (window.slCurrentPage - 1) * pageSize;
    const displayList = isShowAll ? filtered : filtered.slice(startIdx, startIdx + pageSize);

    const pagBar = document.getElementById('sl-pagination-bar');
    if (pagBar) {
        if (filtered.length <= pageSize || isShowAll) {
            pagBar.innerHTML = filtered.length ? `<span style="font-size:0.82rem;font-weight:900;color:#94a3b8;">عرض ${displayList.length} من ${filtered.length} مستند ${isShowAll && totalPages > 1 ? `<button type="button" onclick="onSlPageSelectChange('p_1')" class="sl-btn" style="padding:2px 9px;font-size:0.76rem;margin-right:8px;">تقسيم لصفحات (10)</button>` : ''}</span>` : '';
        } else {
            let btns = `<button type="button" onclick="goToSlPage(${Math.max(1, window.slCurrentPage - 1)})" class="sl-btn" style="padding:4px 11px;font-size:0.78rem;" ${window.slCurrentPage <= 1 ? 'disabled' : ''}>⏮ السابق</button>`;
            for (let p = 1; p <= totalPages; p++) {
                if (totalPages > 9 && p > 2 && p < totalPages - 1 && Math.abs(p - window.slCurrentPage) > 2) {
                    if (p === 3 || p === totalPages - 2) btns += `<span style="color:#94a3b8;padding:0 4px;">...</span>`;
                    continue;
                }
                const act = p === window.slCurrentPage;
                btns += `<button type="button" onclick="goToSlPage(${p})" class="sl-btn ${act ? 'sl-page-active' : ''}" style="padding:4px 11px;font-size:0.8rem;background:${act ? 'var(--sl-primary)' : 'rgba(15,23,42,0.7)'};color:#fff;border-color:${act ? 'var(--sl-primary-light)' : 'rgba(148,163,184,0.3)'};">${p}</button>`;
            }
            btns += `<button type="button" onclick="goToSlPage(${Math.min(totalPages, window.slCurrentPage + 1)})" class="sl-btn" style="padding:4px 11px;font-size:0.78rem;" ${window.slCurrentPage >= totalPages ? 'disabled' : ''}>التالي ⏭</button>`;
            btns += `<button type="button" onclick="onSlPageSelectChange('all')" class="sl-btn" style="padding:4px 11px;font-size:0.78rem;border-color:#fbbf24;color:#fde68a;">الكل</button>`;
            pagBar.innerHTML = `<span style="font-size:0.82rem;font-weight:900;color:#cbd5e1;">الصفحة رقم ${window.slCurrentPage} من ${totalPages} (10 أذونات بالصفحة — الإجمالي ${filtered.length})</span><div style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">${btns}</div>`;
        }
    }

    if (!displayList.length) {
        tbody.innerHTML = `<tr><td colspan="12" style="padding:32px; text-align:center; color:#94a3b8; font-weight:800;">لا توجد مستندات مطابقة للفلتر الحالي — جرب تبديل التبويب إلى (الكل)</td></tr>`;
        if (typeof window.applySlRegTablePrefs === 'function') window.applySlRegTablePrefs();
        return;
    }

    tbody.innerHTML = displayList.map(r => {
        const isSelected = slCurrentDoc && String(slCurrentDoc.invoiceNo) === String(r.invoiceNo);
        const effDoneView = (r._slStatus === 'done' || r._viewMode === 'done_part');
        const statusBadge = r._viewMode === 'done_part'
            ? `<span class="sl-badge sl-badge-done">✅ ${r.totalBatches > 1 || r._slStatus === 'partial' ? `${isSales ? 'دفعة صرف' : 'دفعة استلام'} (${r.batchSeq || 1})` : (isSales ? 'تم الصرف' : 'تم الاستلام')}</span>`
            : r._slStatus === 'done'
            ? `<span class="sl-badge sl-badge-done">✅ ${isSales ? 'تم الصرف' : 'تم الاستلام'}</span>`
            : r._slStatus === 'partial'
                ? `<span class="sl-badge sl-badge-partial">🌗 ${isSales ? 'صرف جزئي' : 'استلام جزئي'}</span>`
                : `<span class="sl-badge sl-badge-pending">⏳ ${isSales ? 'قيد الصرف' : 'قيد الاستلام'}</span>`;

        const rowBoxStyle = 'height:25px;display:flex;align-items:center;padding:0 6px;margin:2px 0;border-radius:6px;background:rgba(2,6,23,0.48);border:1px solid rgba(148,163,184,0.22);cursor:pointer;overflow:hidden;';
        const itemsArr = r.items || [];
        const nameCells = itemsArr.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const safeNm = (m.name || '').replace(/"/g, '&quot;');
            return `<div data-rc-item-id="${m.itemId || ''}" data-rc-item-name="${safeNm}" data-rc-invoice-no="${r.invoiceNo}" data-rc-invoice-mode="${isSales ? 'sales' : 'purchases'}" onclick="event.stopPropagation(); loadSlDocument('${r.invoiceNo}', ${idx})" style="${rowBoxStyle}" title="${safeNm} — كليك يمين للشرح والذهاب إلى كرت الصنف وأصل الفاتورة"><span style="font-size:15.4px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:100%;text-align:right;">${m.name}</span></div>`;
        }).join('') || '<span style="color:#64748b;">---</span>';
        const origQtyCells = itemsArr.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const totQ = +(parseFloat(m.qty) || 0).toFixed(3);
            return `<div onclick="event.stopPropagation(); loadSlDocument('${r.invoiceNo}', ${idx})" style="${rowBoxStyle}justify-content:center;" title="أصل الفاتورة: ${totQ} ${m.unit}"><span style="font-size:0.71rem;font-weight:900;color:#38bdf8;white-space:nowrap;">${totQ} ${m.unit}</span></div>`;
        }).join('') || '<span style="color:#64748b;">---</span>';
        const qtyCells = itemsArr.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const totQ = +(parseFloat(m.qty) || 0).toFixed(3);
            const remQ = it.remainingQty !== undefined ? +(parseFloat(it.remainingQty) || 0).toFixed(3) : totQ;
            const rcvQ = +(parseFloat(it.receivedQty ?? it.deliveredQty ?? Math.max(0, totQ - remQ)) || 0).toFixed(3);
            const dispQ = r._viewMode === 'rem_part' ? remQ : (r._viewMode === 'done_part' ? rcvQ : totQ);
            const qClr = r._viewMode === 'rem_part' ? '#fde047' : (r._viewMode === 'done_part' ? '#34d399' : '#7dd3fc');
            const tipTxt = `أصل الفاتورة: ${totQ} | المستلم: ${rcvQ} | المتبقي: ${remQ}`;
            return `<div onclick="event.stopPropagation(); loadSlDocument('${r.invoiceNo}', ${idx})" style="${rowBoxStyle}justify-content:center;" title="${tipTxt}"><span style="font-size:0.71rem;font-weight:900;color:${qClr};white-space:nowrap;">${dispQ} ${m.unit}</span></div>`;
        }).join('') || '<span style="color:#64748b;">---</span>';
        const remQtyCells = itemsArr.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const totQ = +(parseFloat(it.origInvQty ?? m.qty) || 0).toFixed(3);
            const remQ = it.remainingQty !== undefined ? +(parseFloat(it.remainingQty) || 0).toFixed(3) : 0;
            const rClr = remQ <= 0.0001 ? '#34d399' : '#fde047';
            return `<div onclick="event.stopPropagation(); loadSlDocument('${r.invoiceNo}', ${idx})" style="${rowBoxStyle}justify-content:center;" title="المتبقي بعد هذه الدفعة: ${remQ} من ${totQ}"><span style="font-size:0.71rem;font-weight:900;color:${rClr};white-space:nowrap;">${remQ} ${m.unit}</span></div>`;
        }).join('') || '<span style="color:#64748b;">---</span>';
        const barCells = itemsArr.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const totQ = Math.max(0.0001, parseFloat(it.origInvQty ?? m.qty) || 0);
            const rem = it.remainingQty !== undefined ? parseFloat(it.remainingQty) : (r.isOfficialNote ? 0 : totQ);
            const pendPct = r.isOfficialNote ? 0 : Math.max(0, Math.min(100, Math.round((rem / totQ) * 100)));
            const barClr = pendPct === 0 ? '#10b981' : (pendPct < 100 ? '#38bdf8' : '#f59e0b');
            return `<div onclick="event.stopPropagation(); loadSlDocument('${r.invoiceNo}', ${idx})" style="${rowBoxStyle}gap:5px;" title="المتبقي: ${pendPct}%"><div style="flex:1;min-width:34px;height:7px;background:rgba(148,163,184,0.22);border-radius:99px;overflow:hidden;"><div style="width:${pendPct}%;height:100%;background:${barClr};border-radius:99px;"></div></div><span style="font-size:0.67rem;font-weight:900;color:${barClr};min-width:28px;text-align:center;">${pendPct}%</span></div>`;
        }).join('') || '<span style="color:#64748b;">---</span>';
        const priceCells = itemsArr.map((it, idx) => {
            const m = resolveSlItemMeta(it);
            const totQ = Math.max(0.0001, parseFloat(m.qty) || 0);
            const remQ = it.remainingQty !== undefined ? parseFloat(it.remainingQty) : totQ;
            const rcvQ = parseFloat(it.receivedQty ?? it.deliveredQty ?? Math.max(0, totQ - remQ)) || 0;
            const unitP = parseFloat(m.price) || ((parseFloat(it.total || it.lineTotal) || 0) / totQ) || 0;
            const dispQ = r._viewMode === 'rem_part' ? remQ : (r._viewMode === 'done_part' ? rcvQ : totQ);
            const lineVal = r._viewMode === 'orig_full' || r.isOfficialNote
                ? Math.round((parseFloat(it.total || it.lineTotal) || (totQ * unitP)) || 0)
                : Math.round(dispQ * unitP);
            return `<div onclick="event.stopPropagation(); loadSlDocument('${r.invoiceNo}', ${idx})" style="${rowBoxStyle}justify-content:center;"><span style="font-size:0.70rem;font-weight:900;color:#fde047;font-family:'Outfit',monospace;white-space:nowrap;">${lineVal.toLocaleString('en-US')}</span></div>`;
        }).join('') || '<span style="color:#64748b;">---</span>';

        const actHtml = r.isOfficialNote
            ? `<button type="button" onclick="slRowPrintDoc('${r.invoiceNo}', '${r.noteId}')" class="sl-btn sl-act-mini" style="background:linear-gradient(135deg,#059669,#047857); color:#fff; border-color:#6ee7b7;" title="طباعة هذا الإذن المخزني المستقل">طباعة الإذن</button>
               <button type="button" onclick="printSlBarcodeStickers('${r.invoiceNo}', '${r.noteId}')" class="sl-btn sl-act-mini" style="background:rgba(124,58,237,0.25); color:#e9d5ff; border-color:#c084fc;" title="طباعة ملصق باركود الدفعة والكمية والرف">ملصق</button>
               <button type="button" onclick="slRowWhatsAppDoc('${r.invoiceNo}', '${r.noteId}')" class="sl-btn sl-act-mini" style="background:rgba(34,197,94,0.22); color:#86efac; border-color:#4ade80;" title="إرسال الإذن عبر واتساب">واتساب</button>
               <button type="button" onclick="slRowUndoSingleNote('${r.noteId}', '${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(239,68,68,0.22); color:#fca5a5; border-color:#f87171;" title="إلغاء هذا الإذن فقط وعكس كميته للمخزن">إلغاء الإذن</button>`
            : r._slStatus === 'pending'
            ? `<button type="button" onclick="quickExecuteFullSlDoc('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="padding:4px 11px !important; font-size:0.75rem !important; background:linear-gradient(135deg,#0284c7,#0369a1); border:1.8px solid #7dd3fc; color:#fff; box-shadow:0 2px 8px rgba(2,132,199,0.35);" title="${isSales ? 'اعتماد تسليم كامل الكمية' : 'اعتماد استلام كامل الكمية'}">${isSales ? 'تسليم' : 'استلام'}</button>
               <button type="button" onclick="openSlSplitInspectModal('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="padding:4px 11px !important; font-size:0.75rem !important; background:linear-gradient(135deg,#4f46e5,#3730a3); color:#fff; border:1.8px solid #a5b4fc;" title="تقسيم الكمية على دفعات جزئية أو عدة مخازن">تقسيم</button>
               <button type="button" onclick="slPrintInspectionSheet('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(148,163,184,0.22); color:#f1f5f9; border-color:#94a3b8;" title="${isSales ? 'طباعة أمر فحص وعدّ قبل التسليم' : 'طباعة أمر فحص وعدّ قبل الاستلام'}">فحص</button>`
            : (!effDoneView && r._slStatus === 'partial')
                ? `<button type="button" onclick="quickExecuteFullSlDoc('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:linear-gradient(135deg,#0284c7,#0369a1); border-color:#7dd3fc; color:#fff;" title="${isSales ? 'تسليم كامل الرصيد المتبقي' : 'استلام كامل الرصيد المتبقي'}">${isSales ? 'تسليم المتبقي' : 'استلام المتبقي'}</button>
                   <button type="button" onclick="openSlSplitInspectModal('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(99,102,241,0.24); color:#c7d2fe; border-color:#818cf8;" title="استلام دفعة جزئية جديدة من المتبقي">تقسيم</button>
                   <button type="button" onclick="slRowEditDoc('${r.invoiceNo}', 'partial')" class="sl-btn sl-act-mini" style="background:rgba(245,158,11,0.22); color:#fde047; border-color:#fbbf24;" title="فك وتعديل الدفعات المستلمة سابقاً">تعديل</button>
                   <button type="button" onclick="slRowPrintDoc('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(16,185,129,0.22); color:#6ee7b7; border-color:#34d399;" title="طباعة إذن الاستلام الجزئي">طباعة</button>
                   <button type="button" onclick="printSlBarcodeStickers('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(124,58,237,0.25); color:#e9d5ff; border-color:#c084fc;" title="طباعة ملصق باركود للأصناف والكميات المستلمة">ملصق</button>
                   <button type="button" onclick="slRowWhatsAppDoc('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(34,197,94,0.20); color:#86efac; border-color:#4ade80;" title="مشاركة الاستلام الجزئي عبر واتساب">واتساب</button>
                   <button type="button" onclick="slRowDeleteUndoDoc('${r.invoiceNo}', '${r._slStatus}')" class="sl-btn sl-act-mini" style="background:rgba(239,68,68,0.22); color:#fca5a5; border-color:#f87171;" title="عكس الدفعات المستلمة وإرجاع الفاتورة لقيد الاستلام">عكس</button>`
                : r.hasSingleNote
                    ? `<button type="button" onclick="slRowPrintDoc('${r.invoiceNo}', '${r.noteId}')" class="sl-btn sl-act-mini" style="background:rgba(16,185,129,0.22); color:#6ee7b7; border-color:#34d399;" title="طباعة إذن هذه الدفعة (#${r.noteId})">طباعة</button>
                       <button type="button" onclick="printSlBarcodeStickers('${r.invoiceNo}', '${r.noteId}')" class="sl-btn sl-act-mini" style="background:rgba(124,58,237,0.25); color:#e9d5ff; border-color:#c084fc;" title="طباعة ملصق باركود هذه الدفعة">ملصق</button>
                       <button type="button" onclick="slRowWhatsAppDoc('${r.invoiceNo}', '${r.noteId}')" class="sl-btn sl-act-mini" style="background:rgba(34,197,94,0.20); color:#86efac; border-color:#4ade80;" title="إرسال عبر واتساب">واتساب</button>
                       <button type="button" onclick="slRowUndoSingleNote('${r.noteId}', '${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(239,68,68,0.22); color:#fca5a5; border-color:#f87171;" title="عكس هذه الدفعة المستقلة فقط (#${r.noteId}) وإعادتها للمتبقي">عكس الدفعة</button>`
                    : `<button type="button" onclick="slRowEditDoc('${r.invoiceNo}', 'done')" class="sl-btn sl-act-mini" style="background:rgba(245,158,11,0.22); color:#fde047; border-color:#fbbf24;" title="فتح الإذن لتعديل المخازن أو الكميات أو السائق وإعادة اعتماده">تعديل</button>
                       <button type="button" onclick="slRowPrintDoc('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(16,185,129,0.22); color:#6ee7b7; border-color:#34d399;" title="طباعة الإذن الرسمي">طباعة</button>
                       <button type="button" onclick="printSlBarcodeStickers('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(124,58,237,0.25); color:#e9d5ff; border-color:#c084fc;" title="طباعة ملصق باركود الأصناف والكمية والرف">ملصق</button>
                       <button type="button" onclick="slRowWhatsAppDoc('${r.invoiceNo}')" class="sl-btn sl-act-mini" style="background:rgba(34,197,94,0.20); color:#86efac; border-color:#4ade80;" title="إرسال عبر واتساب">واتساب</button>
                       <button type="button" onclick="slRowDeleteUndoDoc('${r.invoiceNo}', '${r._slStatus}')" class="sl-btn sl-act-mini" style="background:rgba(239,68,68,0.22); color:#fca5a5; border-color:#f87171;" title="عكس إذن الاستلام وإرجاع الفاتورة لقيد الاستلام دون حذف الفاتورة">عكس</button>`;

        const recBadgesHtml = (r.allNoteIds && r.allNoteIds.length)
            ? r.allNoteIds.map(nid => {
                const isNextHint = String(nid).startsWith('دفعة #');
                return `<div style="font-family:${isNextHint ? "'Cairo',sans-serif" : "'Outfit',monospace"}; font-size:0.71rem; font-weight:900; color:${isNextHint ? '#fde047' : '#34d399'}; padding:1px 6px; margin:1px 0; border-radius:5px; background:${isNextHint ? 'rgba(245,158,11,0.16)' : 'rgba(16,185,129,0.14)'}; border:1px solid ${isNextHint ? 'rgba(251,191,36,0.45)' : 'rgba(52,211,153,0.4)'}; display:inline-block; white-space:nowrap;">${isNextHint ? nid : '#' + String(nid).replace(/^#/, '')}</div>`;
            }).join('<br>')
            : `<span style="color:#64748b; font-size:0.70rem;">—</span>`;

        const splitClr = r.isSplitRow ? (slRegistryFilter === 'partial' ? '#fde047' : '#e9d5ff') : '#94a3b8';
        const splitBg = r.isSplitRow ? (slRegistryFilter === 'partial' ? 'rgba(245,158,11,0.18)' : 'rgba(124,58,237,0.22)') : 'rgba(148,163,184,0.12)';
        const splitBdr = r.isSplitRow ? (slRegistryFilter === 'partial' ? 'rgba(251,191,36,0.45)' : 'rgba(192,132,252,0.48)') : 'rgba(148,163,184,0.25)';
        const splitCellHtml = `<span style="display:inline-block;padding:2px 7px;border-radius:6px;font-size:0.70rem;font-weight:900;color:${splitClr};background:${splitBg};border:1px solid ${splitBdr};white-space:nowrap;">${r.splitTag || '—'}</span>`;

        return `
            <tr class="${isSelected ? 'sl-selected-row' : ''}" data-rc-invoice-no="${r.invoiceNo}" data-rc-invoice-mode="${isSales ? 'sales' : 'purchases'}" onclick="openSlSplitInspectModal('${r.invoiceNo}')" style="cursor:pointer;">
                <td data-regcol="date" style="text-align:center; font-size:0.50rem; color:#cbd5e1; font-weight:800; white-space:nowrap; font-family:'Outfit',monospace; letter-spacing:-0.2px;"><div style="font-size:0.50rem;">${r.date || '---'}</div></td>
                <td data-regcol="inv" data-rc-invoice-no="${r.invoiceNo}" data-rc-invoice-mode="${isSales ? 'sales' : 'purchases'}" onclick="event.stopPropagation(); loadSlDocument('${r.invoiceNo}'); if(typeof window.__openErpRightClickChoiceMenu==='function'){ window.__openErpRightClickChoiceMenu(event); }" style="text-align:right; font-family:'Inter',monospace; font-weight:900;" title="اضغط أو كليك يمين للشرح والذهاب إلى أصل الفاتورة">
                    <div style="color:var(--sl-primary-light); font-size:0.73rem; text-decoration:underline; text-underline-offset:3px;">#${r.invoiceNo}</div>
                </td>
                <td data-regcol="recno" style="text-align:center;">${recBadgesHtml}</td>
                <td data-regcol="isplit" style="text-align:center;">${splitCellHtml}</td>
                <td data-regcol="party" data-rc-account-name="${(r.partyName || '').replace(/"/g, '&quot;')}" data-rc-account-type="${isSales ? 'customer' : 'supplier'}" data-rc-invoice-no="${r.invoiceNo}" data-rc-invoice-mode="${isSales ? 'sales' : 'purchases'}" style="text-align:right; font-weight:900; font-size:0.66rem; overflow:hidden; text-overflow:ellipsis;">
                    <span class="sl-party-cell-txt">${r.partyName}</span>
                </td>
                <td data-regcol="iname" style="text-align:right;">${nameCells}</td>
                <td data-regcol="iorig" style="text-align:center;">${origQtyCells}</td>
                <td data-regcol="iqty" style="text-align:center;">${qtyCells}</td>
                <td data-regcol="irem" style="text-align:center;">${remQtyCells}</td>
                <td data-regcol="ibar" style="text-align:center;">${barCells}</td>
                <td data-regcol="iprice" style="text-align:center;">${priceCells}</td>
                <td data-regcol="wh" style="text-align:center;">
                    <div style="font-size:0.72rem; color:#fcd34d; font-weight:800; margin-bottom:3px;">${r.warehouse}</div>
                    ${statusBadge}
                </td>
                <td data-regcol="actions" style="text-align:center;" onclick="event.stopPropagation();">
                    <div style="display:flex; align-items:center; justify-content:center; gap:4px; flex-wrap:wrap;">${actHtml}</div>
                </td>
            </tr>
        `;
    }).join('');
    if (typeof window.applySlRegTablePrefs === 'function') window.applySlRegTablePrefs();
}

function quickLoadFirstMatch(query) {
    const q = String(query || '').trim();
    if (!q) return;
    loadSlDocument(q);
}

// ── LOAD DOCUMENT INTO DUAL-UNIT & MULTI-WAREHOUSE WORKSTATION ───────────────
function clearSlDocumentCard() {
    const setTxt = (id, t) => { const el = document.getElementById(id); if (el) el.textContent = t; };
    setTxt('sl-lbl-inv-no', '---');
    setTxt('sl-lbl-party-name', '---');
    setTxt('sl-lbl-date', '---');
    setTxt('sl-lbl-rec-no', '---');
    setTxt('sl-lbl-rec-date', '---');
    setTxt('sl-lbl-hash', '---');
    const tbody = document.getElementById('sl-items-tbody');
    if (tbody) {
        tbody.innerHTML = `<tr><td colspan="9" style="padding:40px; text-align:center; color:#94a3b8; font-weight:800;">👆 اضغط على أي فاتورة من السجل العلوي أو امسح باركود الـ QR لعرض الأصناف وتقسيمها بالوحدتين والمخازن</td></tr>`;
    }
    updateSlFloatingSummary();
}

window.getNextSlZGrnNumber = function(offset = 0, rawList = null) {
    const now = new Date();
    const zPrefix = `Z${now.getFullYear().toString().slice(-2)}${String(now.getMonth() + 1).padStart(2, '0')}`;
    const list = Array.isArray(rawList) ? rawList : (getSlCachedStore('erp_purchase_invoices_v1', []) || []);
    let maxZ = -1;
    list.forEach(x => {
        if (x && x.isGRN && !x.deleted && !x.isDeleted && String(x.id || '').startsWith(zPrefix)) {
            const n = parseInt(String(x.id).slice(zPrefix.length).split(/[/-]/)[0], 10);
            if (!isNaN(n) && n > maxZ) maxZ = n;
        }
    });
    return `${zPrefix}${String(maxZ + 1 + (parseInt(offset, 10) || 0)).padStart(5, '0')}`;
};

window.openSlModernConfirm = function(titleTxt, bodyTxt, okTxt, okType, onYes) {
    document.getElementById('sl-modern-confirm-modal')?.remove();
    const isLight = document.documentElement.classList.contains('light-theme') || (document.body && document.body.classList.contains('light-theme'));
    const bg = isLight ? 'var(--lt-card-1, #ffffff)' : 'linear-gradient(165deg, #10213d 0%, #091326 100%)';
    const ink = isLight ? '#000000' : '#ffffff';
    const sub = isLight ? '#334155' : '#cbd5e1';
    const bdr = okType === 'danger' ? '#ef4444' : '#38bdf8';
    const btnGrad = okType === 'danger' ? 'linear-gradient(135deg,#dc2626,#991b1b)' : 'linear-gradient(135deg,#d97706,#b45309)';
    const ov = document.createElement('div');
    ov.id = 'sl-modern-confirm-modal';
    ov.style.cssText = 'position:fixed;inset:0;z-index:1000005;background:rgba(2,6,23,0.75);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
    ov.innerHTML = `<div style="width:100%;max-width:430px;background:${bg};color:${ink};border:2.5px solid ${bdr};border-radius:16px;padding:20px 22px;box-shadow:0 20px 50px rgba(0,0,0,0.65);display:flex;flex-direction:column;gap:12px;">
        <div style="font-size:1.02rem;font-weight:900;color:${okType === 'danger' ? (isLight ? '#b91c1c' : '#fca5a5') : (isLight ? '#b45309' : '#fde047')};">${titleTxt}</div>
        <div style="font-size:0.86rem;font-weight:800;color:${sub};line-height:1.65;">${bodyTxt}</div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
            <button type="button" id="sl-mconf-cancel" style="padding:8px 18px;border-radius:9px;border:1.5px solid #64748b;background:transparent;color:${ink};font-family:inherit;font-size:0.84rem;font-weight:900;cursor:pointer;">تراجع</button>
            <button type="button" id="sl-mconf-ok" style="padding:8px 20px;border-radius:9px;border:1.5px solid ${bdr};background:${btnGrad};color:#ffffff;font-family:inherit;font-size:0.86rem;font-weight:900;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,0.3);">${okTxt}</button>
        </div>
    </div>`;
    document.body.appendChild(ov);
    ov.querySelector('#sl-mconf-cancel').onclick = () => ov.remove();
    ov.onclick = (e) => { if (e.target === ov) ov.remove(); };
    ov.querySelector('#sl-mconf-ok').onclick = () => { ov.remove(); if (typeof onYes === 'function') onYes(); };
};

function loadSlDocument(queryNo, focusItemIdx = null) {
    if (!queryNo) return false;
    const cleanQ = String(queryNo).replace(/^#/, '').trim().toLowerCase();
    const isSales = (slCurrentMode === 'sales');
    const list = isSales ? getProcessedSalesInvoices() : getProcessedPurchaseInvoices();

    let doc = list.find(i =>
        String(i.invoiceNo).toLowerCase() === cleanQ ||
        String(i._slNoteNo || '').toLowerCase() === cleanQ ||
        String(i.id || '').toLowerCase() === cleanQ
    );
    if (!doc) {
        doc = list.find(i => resolveSlPartyName(i, slCurrentMode).toLowerCase().includes(cleanQ));
    }

    if (!doc) {
        showSlToast(`❌ لم يتم العثور على مستند برقم: ${queryNo}`, 'error');
        return false;
    }

    slCurrentDoc = doc;
    const partyName = resolveSlPartyName(doc, slCurrentMode);

    document.getElementById('sl-lbl-inv-no').textContent = `#${doc.invoiceNo}`;
    document.getElementById('sl-lbl-party-name').textContent = partyName;
    document.getElementById('sl-lbl-date').textContent = (doc.date || doc.savedAt || '---').slice(0, 10);
    const recNoEl = document.getElementById('sl-lbl-rec-no');
    if (recNoEl) {
        if (doc._slNoteNo && doc._slStatus === 'done') {
            recNoEl.textContent = `#${String(doc._slNoteNo).replace(/^#/, '')}`;
        } else if (isSales) {
            recNoEl.textContent = '#' + (doc.deliveryNo || ('GDN-' + Date.now().toString().slice(-6)));
        } else {
            recNoEl.textContent = `#${window.getNextSlZGrnNumber(0)}`;
        }
    }
    const recDtEl = document.getElementById('sl-lbl-rec-date');
    if (recDtEl) {
        const lastGrn = Array.isArray(doc._slGrns) && doc._slGrns.length ? doc._slGrns[doc._slGrns.length - 1] : null;
        recDtEl.textContent = lastGrn ? String(lastGrn.date || lastGrn.createdAt || '').slice(0, 10) : new Date().toISOString().slice(0, 10);
    }
    document.getElementById('sl-lbl-hash').textContent = doc.hash || (`${isSales ? 'GDN' : 'GRN'}-SEC-` + btoa(unescape(encodeURIComponent(String(doc.invoiceNo)))).slice(0, 14));

    const statusPill = document.getElementById('sl-doc-status-pill');
    if (statusPill) {
        if (doc._slStatus === 'done') {
            statusPill.className = 'sl-badge sl-badge-done';
            statusPill.textContent = isSales ? '✅ تم الصرف بالكامل' : '✅ تم الاستلام بالكامل';
        } else if (doc._slStatus === 'partial') {
            statusPill.className = 'sl-badge sl-badge-partial';
            statusPill.textContent = isSales ? '🌗 مسلّم جزئياً (متاح لتكملة الدفعات)' : '🌗 مستلم جزئياً (متاح لتكملة الدفعات)';
        } else {
            statusPill.className = 'sl-badge sl-badge-pending';
            statusPill.textContent = isSales ? '⏳ جاهز للصرف والتقسيم' : '⏳ جاهز للاستلام والتقسيم';
        }
    }

    const recInput = document.getElementById('sl-receiver-name');
    const drvInput = document.getElementById('sl-driver-name');
    const trkInput = document.getElementById('sl-truck-plate');
    const gpInput  = document.getElementById('sl-gate-pass-no');
    const wNotes   = document.getElementById('sl-warehouse-notes');
    const pNotes   = document.getElementById('sl-personal-notes');

    const masterWhVal = document.getElementById('sl-master-warehouse')?.value || '';
    const whKeeper = resolveSlWarehouseKeeper(masterWhVal);
    if (recInput) recInput.value = (doc.receiverName && doc.receiverName !== partyName ? doc.receiverName : '') || (isSales ? partyName : whKeeper) || '';
    if (drvInput) drvInput.value = doc.driverName || '';
    if (trkInput) trkInput.value = doc.truckPlate || '';
    if (gpInput)  gpInput.value  = doc.gatePassNo || '';
    if (wNotes)   wNotes.value   = doc.warehouseNotes || doc.notes || '';
    if (pNotes)   pNotes.value   = doc.personalNotes || '';
    const setExtraInp = (id, v) => { const el = document.getElementById(id); if (el) el.value = v || ''; };
    setExtraInp('sl-driver-phone', doc.driverPhone);
    setExtraInp('sl-driver-receipt-no', doc.driverReceiptNo);
    setExtraInp('sl-bundles-count', doc.bundlesCount);
    setExtraInp('sl-manifesto-no', doc.manifestoNo);
    setExtraInp('sl-final-inv-no', doc.finalInvoiceNo);
    setExtraInp('sl-transit-permit-no', doc.transitPermitNo);

    const btnL4 = document.getElementById('sl-btn-admin-l4');
    if (btnL4) {
        const showL4 = isSales && slDeductionLevel === 1 && (doc.deliveryStatus === 'Delivered_Pending_Admin' || doc._slStatus === 'done') && doc.deliveryStatus !== 'Admin_Closed';
        btnL4.style.display = showL4 ? 'inline-flex' : 'none';
    }
    if (typeof window.unlockSlSplitModalControls === 'function') window.unlockSlSplitModalControls();
    renderSlItemsGrid();
    renderSlRegistry();
    if (typeof window.updateSlPartyFinancialStrip === 'function') window.updateSlPartyFinancialStrip(doc);

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
        tbody.innerHTML = `<tr><td colspan="11" style="padding:30px; text-align:center; color:#fca5a5;">لا توجد أصناف مسجلة في هذا المستند</td></tr>`;
        updateSlFloatingSummary();
        return;
    }

    const seenWhSeq = new Map();
    let whCounter = 0;

    tbody.innerHTML = items.map((row, idx) => {
        const meta = resolveSlItemMeta(row);
        const origQty = meta.qty;
        const prevQty = parseFloat(row.deliveredQty || row.receivedQty || 0) || 0;
        const remQty = slCurrentDoc._slStatus === 'done' ? 0 : Math.max(0, +(origQty - prevQty).toFixed(4));
        const defaultExecQty = row._tempExecQty !== undefined ? parseFloat(row._tempExecQty) : (slCurrentDoc._slStatus === 'done' ? origQty : remQty);
        const defaultBaseQty = +(defaultExecQty * meta.factor).toFixed(2);
        const remAfter = Math.max(0, +((slCurrentDoc._slStatus === 'done' ? origQty : remQty) - defaultExecQty).toFixed(4));

        const rowWhCandidate = row.warehouseId || slCurrentDoc.deliveredWarehouse || slCurrentDoc.warehouseId || slCurrentDoc.warehouse || masterWhId;
        const matchedWh = whs.find(w => String(w.id) === String(rowWhCandidate) || String(w.name) === String(rowWhCandidate)) || whs[0];
        if (!seenWhSeq.has(String(matchedWh.id))) seenWhSeq.set(String(matchedWh.id), whCounter++);
        const subRef = isSales ? `${slCurrentDoc.invoiceNo}-${seenWhSeq.get(String(matchedWh.id)) + 1}` : window.getNextSlZGrnNumber(seenWhSeq.get(String(matchedWh.id)));
        const whOptionsHtml = whs.map(w => `<option value="${w.id}" ${String(w.id) === String(matchedWh.id) ? 'selected' : ''}>${w.name}</option>`).join('');
        const inpSty = 'width:100%; height:36px; text-align:center; padding:3px 6px; font-size:1.06rem; font-weight:900; font-family:Outfit,Cairo,sans-serif; border-radius:7px;';
        const showQuickSplit = slCurrentDoc._slStatus !== 'done' && !row._isSplitChild && remQty > 0.0001;

        return `
            <tr data-row-idx="${idx}">
                <td style="text-align:center; font-family:'Outfit',monospace; font-weight:900; font-size:0.78rem; color:#38bdf8;">
                    <span id="sl-row-subref-${idx}" style="padding:2px 6px; border-radius:6px; background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.4); white-space:nowrap;">#${subRef}</span>
                </td>
                <td class="sl-col-code" style="text-align:center;">
                    <div style="display:flex; align-items:center; justify-content:center; gap:4px;">
                        <span style="font-size:0.72rem; font-weight:800; color:#cbd5e1; font-family:monospace;">${meta.code}</span>
                        <a href="item-ledger.php?item=${encodeURIComponent(meta.itemId || meta.name)}" target="_blank" onclick="event.stopPropagation();" style="text-decoration:none; padding:1px 5px; border-radius:5px; background:rgba(16,185,129,0.2); border:1px solid #34d399; color:#6ee7b7; font-size:0.66rem; font-weight:900;" title="فتح كرت الصنف وحركة المخازن">كرت</a>
                    </div>
                </td>
                <td data-rc-item-id="${meta.itemId || ''}" data-rc-item-name="${String(meta.name || '').replace(/"/g, '&quot;')}" style="text-align:right; font-weight:900; font-size:0.84rem; color:#ffffff; cursor:context-menu;">
                    <span>${meta.name}</span>
                </td>
                <td style="text-align:center; font-weight:800; font-size:0.78rem; color:#cbd5e1;">${meta.unit}</td>
                <td class="sl-col-orig" style="text-align:center; font-weight:900; font-size:0.96rem; font-family:'Outfit',sans-serif; color:#e2e8f0;">${origQty}</td>
                <td class="sl-col-prev" style="text-align:center; font-family:'Outfit',sans-serif;">
                    <div style="font-size:0.95rem; font-weight:900; color:#fde047;">${remQty}</div>
                    ${prevQty > 0 ? `<div style="font-size:0.66rem; color:#c084fc; font-weight:800;">سابق: ${prevQty}</div>` : ''}
                </td>
                <td class="sl-col-spec" style="text-align:center; font-size:0.76rem; font-weight:800; color:#93c5fd;">${meta.factor} ${meta.baseUnit}</td>
                <td class="sl-col-exec1" style="text-align:center;">
                    <input type="text" inputmode="decimal" class="sl-input sl-qty-primary-input"
                        data-item-idx="${idx}"
                        data-max-qty="${slCurrentDoc._slStatus === 'done' ? origQty : remQty}"
                        data-orig-qty="${origQty}"
                        data-prev-qty="${prevQty}"
                        data-factor="${meta.factor}"
                        data-pri-unit="${meta.unit}"
                        data-base-unit="${meta.baseUnit}"
                        value="${defaultExecQty}"
                        onfocus="this.select()"
                        oninput="onSlPrimaryQtyInput(this)"
                        style="${inpSty}">
                </td>
                <td class="sl-col-rem2" style="text-align:center;">
                    <input type="text" inputmode="decimal" id="sl-rem-after-${idx}" class="sl-input sl-qty-rem-input"
                        data-item-idx="${idx}"
                        data-max-qty="${slCurrentDoc._slStatus === 'done' ? origQty : remQty}"
                        data-factor="${meta.factor}"
                        value="${remAfter}"
                        onfocus="this.select()"
                        oninput="onSlRemQtyInput(this)"
                        style="${inpSty}border-color:#fbbf24 !important; color:${remAfter <= 0.0001 ? '#34d399' : '#fde047'} !important;">
                </td>
                <td class="sl-col-base" style="text-align:center;">
                    <input type="text" inputmode="decimal" class="sl-input sl-qty-base-input"
                        data-item-idx="${idx}"
                        data-max-base="${+((slCurrentDoc._slStatus === 'done' ? origQty : remQty) * meta.factor).toFixed(2)}"
                        data-factor="${meta.factor}"
                        value="${defaultBaseQty}"
                        onfocus="this.select()"
                        oninput="onSlBaseQtyInput(this)"
                        style="${inpSty}border-color:#818cf8 !important;">
                </td>
                <td class="sl-col-base2" style="text-align:center;">
                    <input type="text" inputmode="decimal" class="sl-input sl-qty-base2-input"
                        data-item-idx="${idx}"
                        data-max-base="${+((slCurrentDoc._slStatus === 'done' ? origQty : remQty) * meta.factor).toFixed(2)}"
                        data-factor="${meta.factor}"
                        value="${+(remAfter * meta.factor).toFixed(2)}"
                        onfocus="this.select()"
                        oninput="onSlBase2QtyInput(this)"
                        style="${inpSty}border-color:#f472b6 !important;">
                </td>
                <td class="sl-col-wh" style="text-align:center;">
                    <div style="display:flex; align-items:center; gap:4px;">
                        <select class="sl-input sl-row-wh-select" data-item-idx="${idx}" onchange="updateSlRowStockBadge(${idx}, this.value); window.refreshSlSplitSubRefs();" style="flex:1; height:32px !important; min-height:32px !important; padding:2px 6px !important; font-size:0.78rem !important; font-weight:900 !important;">
                            ${whOptionsHtml}
                        </select>
                        ${row._isSplitChild
                            ? `<button type="button" onclick="mergeSlSplitRow(${idx})" class="sl-btn sl-act-mini" style="background:rgba(239,68,68,0.2); border-color:#f87171; color:#fca5a5;" title="دمج مع السطر الأم">دمج</button>`
                            : (showQuickSplit ? `<button type="button" id="sl-auto-split-btn-${idx}" onclick="splitSlItemRow(${idx})" class="sl-btn sl-act-mini" style="background:${remAfter > 0.0001 ? 'linear-gradient(135deg,#0284c7,#0369a1)' : 'rgba(56,189,248,0.16)'}; border-color:#7dd3fc; color:#fff;" title="إنشاء سطر ثانٍ آلياً بالكمية المتبقية للمخزن الآخر">${remAfter > 0.0001 ? `+ مخزن آخر (${remAfter})` : '+ مخزن'}</button>` : '')}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    applySlColVisibility();
    updateSlFloatingSummary();
}

function activateSlUnitBox(idx, mode) {
    document.getElementById(`sl-ubox-pri-${idx}`)?.classList.toggle('is-active-unit-mode', mode === 'primary');
    document.getElementById(`sl-ubox-base-${idx}`)?.classList.toggle('is-active-unit-mode', mode === 'base');
}

function syncSlFourSplitFields(idx, source) {
    const priInp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${idx}"]`);
    if (!priInp) return;
    const remInp = document.querySelector(`.sl-qty-rem-input[data-item-idx="${idx}"]`);
    const base1Inp = document.querySelector(`.sl-qty-base-input[data-item-idx="${idx}"]`);
    const base2Inp = document.querySelector(`.sl-qty-base2-input[data-item-idx="${idx}"]`);
    const factor = parseFloat(priInp.dataset.factor || 1) || 1;
    const rowObj = slCurrentDoc && slCurrentDoc.items ? slCurrentDoc.items[idx] : null;
    const maxQ = (rowObj && rowObj._splitGroup)
        ? (parseFloat(rowObj._groupTotalRem || priInp.dataset.maxQty || 0) || 0)
        : (parseFloat(priInp.dataset.maxQty || 0) || 0);
    const maxB = +(maxQ * factor).toFixed(2);

    let q1 = 0, q2 = 0, b1 = 0, b2 = 0;
    if (source === 'q1') {
        q1 = parseFloat(priInp.value || 0) || 0;
        if (q1 < 0) { q1 = 0; priInp.value = 0; }
        if (q1 > maxQ) { q1 = maxQ; priInp.value = +q1.toFixed(4); }
        q2 = Math.max(0, +(maxQ - q1).toFixed(4));
        b1 = +(q1 * factor).toFixed(2);
        b2 = +(q2 * factor).toFixed(2);
    } else if (source === 'q2' && remInp) {
        q2 = parseFloat(remInp.value || 0) || 0;
        if (q2 < 0) { q2 = 0; remInp.value = 0; }
        if (q2 > maxQ) { q2 = maxQ; remInp.value = +q2.toFixed(4); }
        q1 = Math.max(0, +(maxQ - q2).toFixed(4));
        b1 = +(q1 * factor).toFixed(2);
        b2 = +(q2 * factor).toFixed(2);
    } else if (source === 'b1' && base1Inp) {
        b1 = parseFloat(base1Inp.value || 0) || 0;
        if (b1 < 0) { b1 = 0; base1Inp.value = 0; }
        if (b1 > maxB) { b1 = maxB; base1Inp.value = +b1.toFixed(2); }
        q1 = factor > 0 ? +(b1 / factor).toFixed(4) : b1;
        q2 = Math.max(0, +(maxQ - q1).toFixed(4));
        b2 = Math.max(0, +(maxB - b1).toFixed(2));
    } else if (source === 'b2' && base2Inp) {
        b2 = parseFloat(base2Inp.value || 0) || 0;
        if (b2 < 0) { b2 = 0; base2Inp.value = 0; }
        if (b2 > maxB) { b2 = maxB; base2Inp.value = +b2.toFixed(2); }
        q2 = factor > 0 ? +(b2 / factor).toFixed(4) : b2;
        q1 = Math.max(0, +(maxQ - q2).toFixed(4));
        b1 = Math.max(0, +(maxB - b2).toFixed(2));
    }

    if (rowObj && rowObj._splitGroup && !window._slInAutoBalance) {
        const sibIdx = slCurrentDoc.items.findIndex((r, i) => i !== Number(idx) && r && r._splitGroup === rowObj._splitGroup);
        if (sibIdx !== -1) {
            window._slInAutoBalance = true;
            const sibPri = document.querySelector(`.sl-qty-primary-input[data-item-idx="${sibIdx}"]`);
            if (sibPri) { sibPri.dataset.maxQty = maxQ; sibPri.value = q2; syncSlFourSplitFields(sibIdx, 'q1'); }
            window._slInAutoBalance = false;
        }
    }

    const dispRem = (rowObj && rowObj._splitGroup) ? 0 : q2;
    const dispBase2 = (rowObj && rowObj._splitGroup) ? 0 : b2;
    if (source !== 'q1') priInp.value = q1;
    if (remInp) {
        if (source !== 'q2') remInp.value = dispRem;
        remInp.style.color = dispRem <= 0.0001 ? '#34d399' : '#fde047';
    }
    if (base1Inp && source !== 'b1') base1Inp.value = b1;
    if (base2Inp && source !== 'b2') base2Inp.value = dispBase2;

    const spBtn = document.getElementById(`sl-auto-split-btn-${idx}`);
    if (spBtn && (!rowObj || !rowObj._splitGroup)) {
        spBtn.textContent = dispRem > 0.0001 ? `+ مخزن آخر (${dispRem})` : '+ مخزن';
        spBtn.style.background = dispRem > 0.0001 ? 'linear-gradient(135deg,#0284c7,#0369a1)' : 'rgba(56,189,248,0.16)';
    }
    window.refreshSlSplitSubRefs();
    updateSlFloatingSummary();
}

function onSlPrimaryQtyInput(inp) {
    if (!inp) return;
    syncSlFourSplitFields(inp.dataset.itemIdx, 'q1');
}
function onSlRemQtyInput(inp) {
    if (!inp) return;
    syncSlFourSplitFields(inp.dataset.itemIdx, 'q2');
}

window.refreshSlSplitSubRefs = function() {
    if (!slCurrentDoc) return;
    const isSales = (slCurrentMode === 'sales');
    const seen = new Map();
    let cnt = 0;
    document.querySelectorAll('.sl-row-wh-select').forEach(sel => {
        const idx = sel.dataset.itemIdx;
        const wId = String(sel.value || '');
        const pri = document.querySelector(`.sl-qty-primary-input[data-item-idx="${idx}"]`);
        const q = parseFloat(pri?.value || 0) || 0;
        if (q > 0 && !seen.has(wId)) seen.set(wId, cnt++);
        const badge = document.getElementById(`sl-row-subref-${idx}`);
        if (badge) {
            const refStr = isSales ? `${slCurrentDoc.invoiceNo}-${(seen.get(wId) || 0) + 1}` : window.getNextSlZGrnNumber(seen.get(wId) || 0);
            badge.textContent = q > 0 ? `#${refStr}` : '—';
        }
    });
    const hdrRec = document.getElementById('sl-lbl-rec-no');
    if (hdrRec && slCurrentDoc._slStatus !== 'done') {
        const refs = Array.from(seen.values()).map(n => isSales ? `#${slCurrentDoc.invoiceNo}-${n + 1}` : `#${window.getNextSlZGrnNumber(n)}`);
        hdrRec.textContent = refs.length ? refs.join(' + ') : (isSales ? `#${slCurrentDoc.invoiceNo}-1` : `#${window.getNextSlZGrnNumber(0)}`);
    }
};

window.onSlSplitBarcodeScan = function(rawVal) {
    const raw = String(rawVal || '').trim();
    if (!raw || !slCurrentDoc || !Array.isArray(slCurrentDoc.items)) return;
    let codePart = raw, addQty = 1;
    if (raw.includes('*')) {
        const parts = raw.split('*');
        codePart = parts[0].trim();
        addQty = Math.max(0.01, parseFloat(parts[1]) || 1);
    }
    const qNorm = codePart.toLowerCase();
    const idx = slCurrentDoc.items.findIndex(it => {
        const m = resolveSlItemMeta(it);
        return String(m.code || '').toLowerCase() === qNorm || String(m.itemId || '').toLowerCase() === qNorm || String(m.name || '').toLowerCase().includes(qNorm);
    });
    if (idx === -1) return showSlToast(`لم يتم العثور على صنف بالكود/الاسم: ${codePart}`, 'error');
    const inp = document.querySelector(`.sl-qty-primary-input[data-item-idx="${idx}"]`);
    if (!inp) return;
    const maxQ = parseFloat(inp.dataset.maxQty || 0) || 0;
    const curQ = parseFloat(inp.value || 0) || 0;
    if (curQ >= maxQ) { playSlTone('error'); return showSlToast(`اكتملت كمية هذا الصنف (${maxQ}) — تم منع الزيادة آلياً!`, 'warning'); }
    inp.value = Math.min(maxQ, +(curQ + addQty).toFixed(4));
    onSlPrimaryQtyInput(inp, true);
    playSlTone('ok');
    showSlToast(`تم عدّ الصنف آلياً: ${inp.value} من ${maxQ}`, 'success', 1400);
};

function onSlBaseQtyInput(baseInp) {
    if (!baseInp) return;
    syncSlFourSplitFields(baseInp.dataset.itemIdx, 'b1');
}
function onSlBase2QtyInput(base2Inp) {
    if (!base2Inp) return;
    syncSlFourSplitFields(base2Inp.dataset.itemIdx, 'b2');
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
    window._slInAutoBalance = true;
    document.querySelectorAll('.sl-qty-primary-input').forEach(inp => {
        const idx = Number(inp.dataset.itemIdx);
        const rowObj = slCurrentDoc && slCurrentDoc.items ? slCurrentDoc.items[idx] : null;
        const baseTarget = (rowObj && rowObj._splitGroup) ? (parseFloat(rowObj.qty || 0) || 0) : (parseFloat(inp.dataset.maxQty || 0) || 0);
        inp.value = +(baseTarget * ratio).toFixed(4);
        onSlPrimaryQtyInput(inp, true);
    });
    window._slInAutoBalance = false;
    if (ratio === 1) showSlToast('⚡ تم تعبئة كامل الكميات المتبقية (100%)', 'success', 2000);
    else if (ratio === 0.5) showSlToast('🌗 تم تقسيم الكميات المتبقية إلى النصف (50%) للدفعة الحالية', 'success', 2000);
    else showSlToast('🧹 تم تصفير الخانات لتحديد الأصناف يدوياً', 'warning', 2000);
}

function updateSlFloatingSummary() {
    const inputs = document.querySelectorAll('.sl-qty-primary-input');
    let count = inputs.length;
    let sumOrdered = 0;
    let sumPrev = 0;
    let sumNow = 0;
    let sumRemAfter = 0;

    inputs.forEach(inp => {
        const orig = parseFloat(inp.dataset.origQty || 0) || 0;
        const prev = parseFloat(inp.dataset.prevQty || 0) || 0;
        const maxRem = parseFloat(inp.dataset.maxQty || 0) || 0;
        const now = parseFloat(inp.value || 0) || 0;
        sumOrdered += orig;
        sumPrev += prev;
        sumNow += now;
        sumRemAfter += Math.max(0, +(maxRem - now).toFixed(4));
    });

    const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = +v.toFixed(2); };
    setTxt('sl-sum-items-count', count);
    setTxt('sl-sum-ordered', sumOrdered);
    setTxt('sl-sum-prev', sumPrev);
    setTxt('sl-sum-now', sumNow);
    setTxt('sl-sum-rem', sumRemAfter);

    const exRate = parseFloat(window.SYSTEM_USD_RATE) || parseFloat(localStorage.getItem('erp_rate_usd')) || 5999;
    let totalSdg = 0;
    if (slCurrentDoc && Array.isArray(slCurrentDoc.items)) {
        slCurrentDoc.items.forEach((it, idx) => {
            const m = resolveSlItemMeta(it);
            const inp = inputs[idx];
            const q = inp ? (parseFloat(inp.value) || 0) : (m.qty || 0);
            totalSdg += q * (parseFloat(m.price) || 0);
        });
        if (!totalSdg && slCurrentDoc.grandTotal) totalSdg = parseFloat(slCurrentDoc.grandTotal) || 0;
    }
    const totalUsd = exRate > 0 ? (totalSdg / exRate) : 0;
    const sdgEl = document.getElementById('sl-sum-total-sdg');
    const usdEl = document.getElementById('sl-sum-total-usd');
    if (sdgEl) sdgEl.textContent = Math.round(totalSdg).toLocaleString('en-US') + ' ج.س';
    if (usdEl) usdEl.textContent = totalUsd.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' $';
}

// ── EXECUTE TRANSACTION (STRICT ISOLATION: SALES GDN VS PURCHASES GRN) ────────
window.captureSlSplitSnapshot = function(rootBase, baseSeq, noteId) {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items)) return null;
    const isSales = (slCurrentMode === 'sales');
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const whs = getSlWarehouses();
    const rows = [];
    slCurrentDoc.items.forEach((it, idx) => {
        const m = resolveSlItemMeta(it);
        const origQ = parseFloat(m.qty) || 0;
        const prevQ = parseFloat(it.deliveredQty || it.receivedQty || 0) || 0;
        const maxRem = slCurrentDoc._slStatus === 'done' ? origQ : Math.max(0, +(origQ - prevQ).toFixed(4));
        const execQ = priInputs[idx] ? Math.min(maxRem, Math.max(0, parseFloat(priInputs[idx].value) || 0)) : maxRem;
        const remAfter = (it && it._splitGroup) ? 0 : Math.max(0, +(maxRem - execQ).toFixed(4));
        const whId = whSelects[idx]?.value || it.warehouseId || whs[0]?.id || '';
        const whName = (whs.find(w => String(w.id) === String(whId)) || whs[0] || { name: 'المخزن الرئيسي' }).name;
        rows.push({ name: m.name, unit: m.unit, baseUnit: m.baseUnit, factor: m.factor || 1, origQ, execQ, remAfter, whName });
    });
    return { invNo: String(slCurrentDoc.invoiceNo), isSales, partyName: resolveSlPartyName(slCurrentDoc, slCurrentMode), rootBase: String(rootBase || slCurrentDoc.invoiceNo).split('/')[0], baseSeq: baseSeq || 0, noteId: noteId || '', rows };
};
window.lockSlSplitModalAfterSave = function(snap) {
    window._slSplitModalLocked = true;
    window._slSavedSplitSnapshot = snap || null;
    document.querySelectorAll('#sl-split-inspect-modal input, #sl-split-inspect-modal select, #sl-split-inspect-modal textarea, #sl-doc-meta-card input, #sl-doc-meta-card select, #sl-doc-meta-card textarea').forEach(el => {
        if (el.type === 'checkbox') return;
        el.disabled = true;
        el.style.opacity = '0.88';
        el.style.cursor = 'not-allowed';
    });
    document.querySelectorAll('#sl-btn-fill-full, #sl-btn-fill-zero, #sl-btn-undo, [id^="sl-auto-split-btn-"], [onclick^="mergeSlSplitRow"]').forEach(b => {
        b.disabled = true; b.style.pointerEvents = 'none'; b.style.opacity = '0.42';
    });
    const exBtn = document.getElementById('sl-btn-execute');
    if (exBtn) { exBtn.disabled = true; exBtn.style.pointerEvents = 'none'; exBtn.style.opacity = '0.72'; exBtn.textContent = '🔒 تم الحفظ والاعتماد (جاهز للطباعة فقط)'; }
    const pill = document.getElementById('sl-doc-status-pill');
    if (pill) { pill.className = 'sl-badge sl-badge-done'; pill.textContent = '🔒 تم الحفظ — اطبع القسم (1) والقسم (2)'; }
    if (snap && snap.noteId) { const rn = document.getElementById('sl-lbl-rec-no'); if (rn) rn.textContent = '#' + snap.noteId; }
};
window.unlockSlSplitModalControls = function() {
    window._slSplitModalLocked = false;
    window._slSavedSplitSnapshot = null;
    document.querySelectorAll('#sl-split-inspect-modal input, #sl-split-inspect-modal select, #sl-split-inspect-modal textarea, #sl-doc-meta-card input, #sl-doc-meta-card select, #sl-doc-meta-card textarea').forEach(el => {
        el.disabled = false; el.style.opacity = ''; el.style.cursor = '';
    });
    document.querySelectorAll('#sl-btn-fill-full, #sl-btn-fill-zero, #sl-btn-undo').forEach(b => {
        b.disabled = false; b.style.pointerEvents = ''; b.style.opacity = '';
    });
    const exBtn = document.getElementById('sl-btn-execute');
    if (exBtn) { exBtn.disabled = false; exBtn.style.pointerEvents = ''; exBtn.style.opacity = ''; exBtn.textContent = (slCurrentMode === 'sales') ? '🚚 حفظ واعتماد التسليم' : '📥 حفظ واعتماد الاستلام'; }
};

function executeSlTransaction() {
    if (window._slSplitModalLocked) {
        showSlToast('🔒 تم حفظ هذا التقسيم مسبقاً — يمكنك طباعة القسم (1) والقسم (2) فقط بدون تعديل', 'info');
        return false;
    }
    if (!slCurrentDoc) {
        showSlToast('⚠️ يرجى اختيار مستند من السجل العلوي أو مسح باركود QR أولاً', 'warning');
        return false;
    }
    return (slCurrentMode === 'sales') ? executeSlSalesDelivery() : executeSlPurchaseReceipt();
}

function executeSlSalesDelivery() {
    invalidateSlStoreCache();
    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
    const invNo = String(slCurrentDoc.invoiceNo);
    const invIdx = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);
    if (invIdx === -1) {
        showSlToast('❌ الفاتورة غير موجودة في سجل المبيعات', 'error');
        return false;
    }

    const inv = allSales[invIdx];
    const whs = getSlWarehouses();
    const masterWhId = document.getElementById('sl-master-warehouse')?.value || whs[0].id;
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');

    const wasAlreadyDone = (
        inv.isDelivered === true ||
        inv.deliveryStatus === 'delivered' ||
        inv.deliveryStatus === 'Delivered_Pending_Admin' ||
        inv.deliveryStatus === 'Admin_Closed'
    );

    if (wasAlreadyDone) {
        // Update logistics metadata only without duplicate stock deduction or corrupting deliveredQty
        inv.receiverName = document.getElementById('sl-receiver-name')?.value.trim() || resolveSlPartyName(inv, 'sales');
        inv.driverName = document.getElementById('sl-driver-name')?.value.trim() || '';
        inv.truckPlate = document.getElementById('sl-truck-plate')?.value.trim() || '';
        inv.gatePassNo = document.getElementById('sl-gate-pass-no')?.value.trim() || '';
        inv.warehouseNotes = document.getElementById('sl-warehouse-notes')?.value.trim() || '';
        inv.personalNotes = document.getElementById('sl-personal-notes')?.value.trim() || '';
        if (slCurrentPhoto) inv.attachmentBase64 = slCurrentPhoto;

        allSales[invIdx] = inv;
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
        localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));

        let deliveryNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
        const noteIdx = deliveryNotes.findIndex(n => String(n.invoiceNo || n.relatedInvoice) === invNo);
        if (noteIdx !== -1) {
            deliveryNotes[noteIdx].receiverName = inv.receiverName;
            deliveryNotes[noteIdx].driverName = inv.driverName;
            deliveryNotes[noteIdx].truckPlate = inv.truckPlate;
            deliveryNotes[noteIdx].gatePassNo = inv.gatePassNo;
            deliveryNotes[noteIdx].notes = inv.warehouseNotes;
            deliveryNotes[noteIdx].personalNotes = inv.personalNotes;
            localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(deliveryNotes));
        }
        invalidateSlStoreCache();
        loadSlDocument(invNo);
        showSlToast(`✅ الفاتورة #${invNo} منصرفة بالكامل مسبقاً — تم حفظ تحديثات السائق والبوابة والملاحظات (لإعادة الصرف اضغط إلغاء الصرف أولاً)`, 'success');
        return true;
    }

    let isPartialDelivery = false;
    const deliveredItemsPayload = [];
    const docRows = (slCurrentDoc && Array.isArray(slCurrentDoc.items) && slCurrentDoc.items.length)
        ? slCurrentDoc.items
        : (inv.items || inv.rows || []);

    const origItemsSrc = inv.items || inv.rows || docRows;
    const delivSumByKey = {};
    docRows.forEach((item, idx) => {
        const meta = resolveSlItemMeta(item);
        const origQty = parseFloat(item._origLineQty || meta.qty) || 0;
        const prevDel = parseFloat(item.deliveredQty || 0) || 0;
        const maxRem = item._splitGroup ? (parseFloat(item._groupTotalRem || origQty) || origQty) : Math.max(0, +(origQty - prevDel).toFixed(4));
        let deliverNow = maxRem;

        if (priInputs[idx]) {
            const parsed = parseFloat(priInputs[idx].value);
            if (!isNaN(parsed)) deliverNow = Math.min(maxRem, Math.max(0, parsed));
        }

        const rowWhId = whSelects[idx]?.value || item.warehouseId || masterWhId;
        const rowWhObj = whs.find(w => String(w.id) === String(rowWhId)) || whs[0];
        const k = String(meta.itemId || meta.name || idx);
        delivSumByKey[k] = (delivSumByKey[k] || 0) + deliverNow;

        if (deliverNow > 0) {
            const cleanRow = { ...item };
            delete cleanRow._isSplitChild; delete cleanRow._parentIdx; delete cleanRow._tempExecQty; delete cleanRow._splitGroup; delete cleanRow._groupTotalRem;
            deliveredItemsPayload.push({
                ...cleanRow,
                name: meta.name,
                itemName: meta.name,
                unit: meta.unit,
                qty: deliverNow,
                quantity: deliverNow,
                orderedQty: origQty,
                warehouseId: rowWhObj.id,
                warehouseName: rowWhObj.name,
                deductedByLevel: slDeductionLevel
            });
        }
    });
    const updatedInvItems = origItemsSrc.filter(r => !r._isSplitChild).map((origIt, oIdx) => {
        const meta = resolveSlItemMeta(origIt);
        const origQty = parseFloat(origIt._origLineQty || meta.qty) || 0;
        const prevDel = parseFloat(origIt.deliveredQty || 0) || 0;
        const k = String(meta.itemId || meta.name || oIdx);
        const nextDelQty = Math.min(origQty, +(prevDel + (delivSumByKey[k] || 0)).toFixed(4));
        if (nextDelQty < origQty - 0.0001) isPartialDelivery = true;
        const cleanOrig = { ...origIt };
        delete cleanOrig._isSplitChild; delete cleanOrig._parentIdx; delete cleanOrig._tempExecQty; delete cleanOrig._splitGroup; delete cleanOrig._groupTotalRem;
        cleanOrig.qty = origQty; cleanOrig.quantity = origQty;
        cleanOrig.deliveredQty = nextDelQty;
        cleanOrig.remainingQty = Math.max(0, +(origQty - nextDelQty).toFixed(4));
        return cleanOrig;
    });

    if (!deliveredItemsPayload.length) {
        if (window.ElsajanaAccountingBrain) window.ElsajanaAccountingBrain.shakeAndFocus(priInputs[0] || '#sl-items-tbody', '⚠️ يرجى تحديد كمية أكبر من صفر للصرف والتسليم', 'warning');
        else showSlToast('⚠️ يرجى تحديد كمية أكبر من صفر للصرف والتسليم', 'warning');
        return false;
    }

    // Point 7 Guard: Block zero/negative stock outbound delivery without explicit permission
    if (typeof window.elsajanaCheckOutboundStockGuard === 'function') {
        const masterList = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
        for (let i = 0; i < deliveredItemsPayload.length; i++) {
            const dIt = deliveredItemsPayload[i];
            const mObj = masterList.find(x => x && (String(x.id) === String(dIt.itemId || dIt.id) || String(x.name || '').trim() === String(dIt.name || '').trim()));
            const factorVal = parseFloat(dIt.customFactor || dIt.factor || 1) || 1;
            const gRes = window.elsajanaCheckOutboundStockGuard(mObj, (parseFloat(dIt.qty) || 0) * factorVal, false);
            if (!gRes.allowed) {
                if (window.ElsajanaAccountingBrain) window.ElsajanaAccountingBrain.shakeAndFocus(priInputs[i] || '#sl-items-tbody', gRes.message, 'error', 5600);
                else showSlToast(gRes.message, 'error', 5600);
                return false;
            }
        }
    }

    inv.items = updatedInvItems;
    if (Array.isArray(inv.rows)) inv.rows = updatedInvItems;
    inv.deliveredByLevel = slDeductionLevel;

    // Deduct stock from erp_inventory_v1 and erp_items_v1 (- Stock Out)
    let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
    let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

    deliveredItemsPayload.forEach(dItem => {
        const deductQty = parseFloat(dItem.qty || 0);
        const itemNameClean = String(dItem.name || '').trim();
        const whId = dItem.warehouseId;

        let invEntry = inventory.find(x =>
            x &&
            (String(x.id || x.itemId) === String(dItem.itemId || dItem.id) || String(x.name || '').trim() === itemNameClean) &&
            (String(x.warehouseId || x.warehouse) === String(whId) || String(x.warehouse) === String(dItem.warehouseName))
        );
        if (invEntry) {
            invEntry.qty = +(parseFloat(invEntry.qty || 0) - deductQty).toFixed(4);
        } else {
            inventory.push({
                id: dItem.itemId || dItem.id || Date.now(),
                itemId: dItem.itemId || dItem.id,
                name: itemNameClean,
                warehouseId: whId,
                warehouse: dItem.warehouseName,
                qty: -deductQty,
                unit: dItem.unit || 'قطعة'
            });
        }

        const masterIdx = erpItems.findIndex(x =>
            x && (String(x.id) === String(dItem.itemId || dItem.id) || String(x.name || '').trim() === itemNameClean)
        );
        if (masterIdx !== -1) {
            const mItem = erpItems[masterIdx];
            if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
            if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
            mItem.stock[whId] = +((parseFloat(mItem.stock[whId]) || 0) - deductQty).toFixed(4);
            mItem.actualStock[whId] = +((parseFloat(mItem.actualStock[whId]) || 0) - deductQty).toFixed(4);
            if (mItem.quantity !== undefined) {
                mItem.quantity = +((parseFloat(mItem.quantity) || 0) - deductQty).toFixed(4);
            }
            erpItems[masterIdx] = mItem;
        }
    });

    localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
    localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));

    const nowIso = new Date().toISOString();
    let deliveryNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
    const existingGdns = deliveryNotes.filter(n => n && !n.deleted && String(n.invoiceNo || n.relatedInvoice) === invNo);
    const salesByWh = new Map();
    deliveredItemsPayload.forEach(di => {
        const wk = String(di.warehouseId);
        if (!salesByWh.has(wk)) salesByWh.set(wk, []);
        salesByWh.get(wk).push(di);
    });
    const isSalesSplitOp = isPartialDelivery || existingGdns.length > 0 || salesByWh.size > 1 || docRows.some(r => r._isSplitChild);
    const rootGdnBase = String(inv.rootSplitNo || (existingGdns[0] && (existingGdns[0].rootSplitNo || existingGdns[0].deliveryNo || existingGdns[0].id)) || inv.deliveryNo || ('GDN-' + Date.now().toString().slice(-6))).split('/')[0];
    let nextGdnSeq = existingGdns.length + 1;
    const firstWh = deliveredItemsPayload[0];
    const createdGdnNos = [];

    inv.isDelivered = !isPartialDelivery;
    inv.deliveryStatus = isPartialDelivery ? 'partial' : 'Delivered_Pending_Admin';
    inv.status = isPartialDelivery ? 'partial' : 'Delivered_Pending_Admin';
    inv.rootSplitNo = rootGdnBase;
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

    const batchesToSave = isSalesSplitOp && docRows.some(r => r._isSplitChild) && salesByWh.size === 1
        ? deliveredItemsPayload.map(singleIt => [singleIt])
        : Array.from(salesByWh.values());
    const createdGdnRecords = [];
    const preBaseSeq = existingGdns.length;
    batchesToSave.forEach(whItems => {
        const sSeq = nextGdnSeq++;
        const subGdnNo = isSalesSplitOp ? `${rootGdnBase}/${sSeq}` : rootGdnBase;
        createdGdnNos.push(subGdnNo);
        const gdnRec = {
            id: subGdnNo,
            deliveryNo: subGdnNo,
            rootSplitNo: rootGdnBase,
            splitSeq: sSeq,
            isSplitPart: isSalesSplitOp,
            invoiceNo: invNo,
            relatedInvoice: invNo,
            date: inv.deliveryDate,
            time: inv.deliveryTime,
            createdAt: nowIso,
            customer: resolveSlPartyName(inv, 'sales'),
            customerName: resolveSlPartyName(inv, 'sales'),
            warehouse: whItems[0].warehouseName,
            warehouseId: whItems[0].warehouseId,
            receiverName: inv.receiverName,
            driverName: inv.driverName,
            truckPlate: inv.truckPlate,
            gatePassNo: inv.gatePassNo || subGdnNo,
            notes: inv.warehouseNotes,
            personalNotes: inv.personalNotes,
            isPartialDelivery,
            status: 'delivered',
            items: whItems,
            deleted: 0
        };
        deliveryNotes.unshift(gdnRec);
        createdGdnRecords.push(gdnRec);
    });
    const gdnNo = createdGdnNos.join(' + ') || rootGdnBase;
    const isSplitModalOpen = document.getElementById('sl-split-inspect-modal')?.style.display === 'block';
    const savedSnap = isSplitModalOpen ? window.captureSlSplitSnapshot(rootGdnBase, preBaseSeq, gdnNo) : null;
    inv.deliveryNo = createdGdnNos[createdGdnNos.length - 1] || rootGdnBase;
    allSales[invIdx] = inv;
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));
    localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(deliveryNotes));
    invalidateSlStoreCache();

    try {
        createdGdnRecords.forEach(gdnRecord => {
            fetch('api/mirror_sync.php?action=save_delivery_note', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                keepalive: true,
                body: JSON.stringify({ ...gdnRecord, updatedInvoiceItems: inv.items })
            }).catch(() => {});
        });
        fetch('api/mirror_sync.php?action=save_sales_invoice', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            keepalive: true,
            body: JSON.stringify(inv)
        }).catch(() => {});
    } catch (e) {}

    if (isSplitModalOpen) {
        renderSlRegistry();
        window.lockSlSplitModalAfterSave(savedSnap);
    } else {
        loadSlDocument(invNo);
    }
    showSlToast(`🚚 تم اعتماد إذن تسليم المبيعات (${gdnNo}) وخصم المخزون بنجاح — الشاشة جاهزة للطباعة!`, 'success');
    return true;
}

function executeSlPurchaseReceipt() {
    let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
    const invNo = String(slCurrentDoc.invoiceNo);
    let origIdx = allPurchases.findIndex(p => p && !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === invNo);
    if (origIdx === -1 && slCurrentDoc) {
        const seedInv = Object.assign({}, slCurrentDoc);
        delete seedInv._slStatus; delete seedInv._slNoteNo; delete seedInv._slGrns; delete seedInv._slCompletedAt;
        allPurchases.unshift(seedInv);
        origIdx = 0;
    }
    if (origIdx === -1) {
        showSlToast('❌ فاتورة المشتريات غير موجودة في السجل', 'error');
        return false;
    }

    const origInv = allPurchases[origIdx];
    const whs = getSlWarehouses();
    const masterWhId = document.getElementById('sl-master-warehouse')?.value || whs[0].id;
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');

    const grnItems = [];
    let isPartialReceipt = false;

    (slCurrentDoc.items || []).forEach((row, idx) => {
        const meta = resolveSlItemMeta(row);
        const maxRem = parseFloat(row.remainingQty ?? meta.qty) || 0;
        let receiveNow = maxRem;

        if (priInputs[idx]) {
            const parsed = parseFloat(priInputs[idx].value);
            if (!isNaN(parsed)) receiveNow = Math.min(maxRem, Math.max(0, parsed));
        }

        if (receiveNow < maxRem - 0.0001) {
            isPartialReceipt = true;
        }

        const rowWhId = whSelects[idx]?.value || masterWhId;
        const rowWhObj = whs.find(w => String(w.id) === String(rowWhId)) || whs[0];

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
                warehouseName: rowWhObj.name
            });
        }
    });

    if (!grnItems.length) {
        if (window.ElsajanaAccountingBrain) window.ElsajanaAccountingBrain.shakeAndFocus(priInputs[0] || '#sl-items-tbody', '⚠️ يرجى تحديد كمية أكبر من صفر لاستلام البضاعة', 'warning');
        else showSlToast('⚠️ يرجى تحديد كمية أكبر من صفر لاستلام البضاعة', 'warning');
        return false;
    }

    // Prevent rapid accidental double-click within 2.5s while allowing re-receipt after Undo/Reverse
    const grnSig = `${invNo}|${grnItems.map(g => `${g.itemId}:${g.quantity}:${g.warehouseId}`).join(',')}`;
    if (!window._forceAllowDupSlGrn && window._lastSlGrnSig === grnSig && (Date.now() - (window._lastSlGrnTs || 0)) < 2500) {
        return false;
    }
    window._lastSlGrnSig = grnSig;
    window._lastSlGrnTs = Date.now();

    // Add stock to erp_inventory_v1 and erp_items_v1 (+ Stock In)
    let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
    let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

    grnItems.forEach(gItem => {
        const baseQtyToAdd = parseFloat(gItem.customBaseQty || gItem.quantity || 0);
        const whName = gItem.warehouseName;
        const whId = gItem.warehouseId;

        let itemInv = inventory.find(inv =>
            (String(inv.itemId || inv.id) === String(gItem.itemId) || String(inv.name || '').trim() === gItem.name.trim()) &&
            (String(inv.warehouse) === String(whName) || String(inv.warehouseId) === String(whId))
        );
        if (itemInv) {
            itemInv.qty = (parseFloat(itemInv.qty || itemInv.quantity || 0) + baseQtyToAdd);
        } else {
            inventory.push({
                itemId: gItem.itemId,
                name: gItem.name,
                warehouse: whName,
                warehouseId: whId,
                qty: baseQtyToAdd,
                minQty: 10
            });
        }

        const masterIdx = erpItems.findIndex(x => String(x.id) === String(gItem.itemId) || String(x.name || '').trim() === gItem.name.trim());
        if (masterIdx !== -1) {
            const mItem = erpItems[masterIdx];
            if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
            if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
            // Deduct from PENDING_RECEIPT bucket if present
            if (mItem.stock['PENDING_RECEIPT'] !== undefined) {
                mItem.stock['PENDING_RECEIPT'] = Math.max(0, (parseFloat(mItem.stock['PENDING_RECEIPT']) || 0) - baseQtyToAdd);
            }
            if (mItem.actualStock['PENDING_RECEIPT'] !== undefined) {
                mItem.actualStock['PENDING_RECEIPT'] = Math.max(0, (parseFloat(mItem.actualStock['PENDING_RECEIPT']) || 0) - baseQtyToAdd);
            }
            mItem.stock[whId] = (parseFloat(mItem.stock[whId]) || 0) + baseQtyToAdd;
            mItem.actualStock[whId] = (parseFloat(mItem.actualStock[whId]) || 0) + baseQtyToAdd;
            const prevTotalStock = Math.max(0, parseFloat(mItem.currentStock ?? mItem.quantity ?? 0) || 0);
            const realWhStock = Object.entries(mItem.stock)
                .filter(([k]) => k !== 'PENDING_RECEIPT')
                .reduce((acc, [, v]) => acc + (parseFloat(v) || 0), 0);
            mItem.currentStock = realWhStock;
            mItem.quantity = realWhStock;
            if (Array.isArray(mItem.units) && mItem.units[0]) {
                mItem.units[0].stock = realWhStock;
            }
            if (parseFloat(gItem.price) > 0) {
                const subVal = Math.max(1, parseFloat(origInv.subtotal || origInv.total || 0) || 1);
                const extraNet = (parseFloat(origInv.transportCost || origInv.shippingCost || 0) || 0) + (parseFloat(origInv.laborCost || 0) || 0) + (parseFloat(origInv.extraCost || 0) || 0) - (parseFloat(origInv.discount || 0) || 0);
                const landedRatio = extraNet !== 0 ? Math.max(0.5, 1 + (extraNet / subVal)) : 1;
                const unitCostBase = +((parseFloat(gItem.price) * landedRatio) / (parseFloat(gItem.factor) || 1)).toFixed(4);
                const oldCost = parseFloat(mItem.costPrice || mItem.lastPurchaseCost || unitCostBase) || unitCostBase;
                mItem.costPrice = (prevTotalStock + baseQtyToAdd) > 0 ? +(((prevTotalStock * oldCost) + (baseQtyToAdd * unitCostBase)) / (prevTotalStock + baseQtyToAdd)).toFixed(4) : unitCostBase;
                mItem.lastPurchaseCost = unitCostBase;
            }
            erpItems[masterIdx] = mItem;
        }
        // Also deduct from PENDING_RECEIPT row in erp_inventory_v1 if present
        let pendingInv = inventory.find(inv =>
            (String(inv.itemId || inv.id) === String(gItem.itemId) || String(inv.name || '').trim() === gItem.name.trim()) &&
            (String(inv.warehouse) === 'PENDING_RECEIPT' || String(inv.warehouseId) === 'PENDING_RECEIPT')
        );
        if (pendingInv) {
            pendingInv.qty = Math.max(0, (parseFloat(pendingInv.qty || pendingInv.quantity || 0) - baseQtyToAdd));
        }
    });

    localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
    localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));

    // Generate Monotonic Sequential Z-Prefix Receipt ID identical to #sl-lbl-rec-no preview!
    const now = new Date();
    let nextSubSeq = allPurchases.filter(x => x && x.isGRN && !x.deleted && !x.isDeleted && String(x.originalInvoiceId) === invNo).length + 1;
    let zBatchOffset = 0;

    const curUser = (typeof window.getCurrentUserInfo === 'function')
        ? window.getCurrentUserInfo()
        : { id: 1, name: 'مدير النظام' };

    const prevRecByItem = {};
    allPurchases.filter(p => p && !p.deleted && !p.isDeleted && p.isGRN && String(p.originalInvoiceId) === invNo).forEach(g => {
        (g.items || g.lines || []).forEach(l => {
            const k = String(l.itemId || l.id || l.name || l.itemName || 'صنف');
            prevRecByItem[k] = (prevRecByItem[k] || 0) + (parseFloat(l.quantity ?? l.qty ?? 0) || 0);
        });
    });
    grnItems.forEach(l => {
        const k = String(l.itemId || l.name || 'صنف');
        prevRecByItem[k] = (prevRecByItem[k] || 0) + (parseFloat(l.quantity || 0) || 0);
    });
    isPartialReceipt = (origInv.items || origInv.lines || []).some(origLine => {
        const k = String(origLine.itemId || origLine.id || origLine.name || origLine.itemName || 'صنف');
        const orderedQ = parseFloat(origLine.quantity ?? origLine.qty ?? 0) || 0;
        return (prevRecByItem[k] || 0) < (orderedQ - 0.0001);
    });

    const itemsByWh = new Map();
    grnItems.forEach(gi => {
        const wk = String(gi.warehouseId);
        if (!itemsByWh.has(wk)) itemsByWh.set(wk, []);
        itemsByWh.get(wk).push(gi);
    });

    const existingInvGrns = allPurchases.filter(x => x && x.isGRN && !x.deleted && !x.isDeleted && String(x.originalInvoiceId) === invNo);
    const hasSplitChildren = Array.isArray(slCurrentDoc?.items) && slCurrentDoc.items.some(r => r._isSplitChild);
    const isPurSplitOp = isPartialReceipt || existingInvGrns.length > 0 || itemsByWh.size > 1 || hasSplitChildren;
    const rootPurBase = String(origInv.rootSplitNo || (existingInvGrns[0] && (existingInvGrns[0].rootSplitNo || existingInvGrns[0].id)) || window.getNextSlZGrnNumber(0, allPurchases)).split('/')[0];
    origInv.rootSplitNo = rootPurBase;
    const purBatches = (isPurSplitOp && hasSplitChildren && itemsByWh.size === 1) ? grnItems.map(gi => [gi]) : Array.from(itemsByWh.values());
    const createdGrnIds = [];
    const createdRecords = [];
    const resolvedSupName = resolveSlPartyName(origInv, 'purchases');
    purBatches.forEach((whBatchItems) => {
        const seqNum = nextSubSeq++;
        const subGrnId = isPurSplitOp ? `${rootPurBase}/${seqNum}` : rootPurBase;
        createdGrnIds.push(subGrnId);
        const rec = {
            id: subGrnId,
            rootSplitNo: rootPurBase,
            isSplitPart: isPurSplitOp,
            isGRN: true,
            originalInvoiceId: invNo,
            parentInvoiceNo: invNo,
            splitSeq: seqNum,
            date: now.toISOString(),
            customer: origInv.customer || origInv.supplierId || resolvedSupName,
            supplierId: origInv.supplierId || origInv.customer || '',
            supplierName: resolvedSupName,
            vendorName: resolvedSupName,
            warehouse: whBatchItems[0].warehouseId,
            warehouseName: whBatchItems[0].warehouseName,
            items: whBatchItems,
            total: 0,
            receiverName: document.getElementById('sl-receiver-name')?.value.trim() || '',
            driverName: document.getElementById('sl-driver-name')?.value.trim() || '',
            driverPhone: document.getElementById('sl-driver-phone')?.value.trim() || '',
            truckPlate: document.getElementById('sl-truck-plate')?.value.trim() || '',
            driverReceiptNo: document.getElementById('sl-driver-receipt-no')?.value.trim() || '',
            bundlesCount: document.getElementById('sl-bundles-count')?.value.trim() || '',
            gatePassNo: document.getElementById('sl-gate-pass-no')?.value.trim() || subGrnId,
            manifestoNo: document.getElementById('sl-manifesto-no')?.value.trim() || '',
            finalInvoiceNo: document.getElementById('sl-final-inv-no')?.value.trim() || '',
            transitPermitNo: document.getElementById('sl-transit-permit-no')?.value.trim() || '',
            notes: document.getElementById('sl-warehouse-notes')?.value.trim() || `إذن استلام فرعي #${subGrnId} للفاتورة الأم #${invNo}`,
            personalNotes: document.getElementById('sl-personal-notes')?.value.trim() || '',
            createdBy: curUser.id,
            createdByName: curUser.fullName || curUser.name,
            savedAt: now.toISOString()
        };
        allPurchases.push(rec);
        createdRecords.push(rec);
    });

    const grnId = createdGrnIds.join(' + ');
    allPurchases[origIdx].receiptStatus = isPartialReceipt ? 'partial_received' : 'received';
    allPurchases[origIdx].status = isPartialReceipt ? 'partial_received' : 'received';
    allPurchases[origIdx].grnNo = createdGrnIds[createdGrnIds.length - 1];

    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
    if (typeof window.logSystemAction === 'function') {
        window.logSystemAction('استلام مشتريات شجري (GRN)', 'المشتريات / المخازن', grnId, `إصدار إذن استلام مرتبط بالأم (${grnId}) للفاتورة #${invNo}`);
    }
    if (typeof window.pushToCloud === 'function') window.pushToCloud(true);

    try {
        createdRecords.forEach(grnRecord => {
            if (window.ElsajanaAccountingBrain && typeof window.ElsajanaAccountingBrain.syncLogisticsReceiptAtomically === 'function') {
                window.ElsajanaAccountingBrain.syncLogisticsReceiptAtomically(grnRecord, origInv, grnRecord.items);
            } else {
                fetch('api/mirror_sync.php?action=save_purchase_receipt', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    keepalive: true,
                    body: JSON.stringify(grnRecord)
                }).catch(() => {});
            }
        });
    } catch (e) {}

    const isPurSplitModalOpen = document.getElementById('sl-split-inspect-modal')?.style.display === 'block';
    const purSnap = isPurSplitModalOpen ? window.captureSlSplitSnapshot(rootPurBase, existingInvGrns.length, grnId) : null;
    invalidateSlStoreCache();
    if (isPurSplitModalOpen) {
        renderSlRegistry();
        window.lockSlSplitModalAfterSave(purSnap);
    } else {
        loadSlDocument(invNo);
    }
    showSlToast(`📥 تم إصدار مذكرة استلام المشتريات (${grnId}) وإضافة الكميات للمخزون بنجاح — الشاشة جاهزة للطباعة!`, 'success');
    return true;
}

function quickExecuteFullSlDoc(invNo, confirmedWhId = null) {
    if (!loadSlDocument(invNo)) return;
    if (!confirmedWhId) { openSlTwoStepFullReceiptModal(invNo); return; }
    const masterSel = document.getElementById('sl-master-warehouse');
    if (masterSel && confirmedWhId !== '__KEEP_ROWS__') { masterSel.value = confirmedWhId; onSlMasterWarehouseChange(confirmedWhId); }
    document.querySelectorAll('.sl-full-rcv-row-wh').forEach(sel => {
        const rIdx = Number(sel.dataset.ridx);
        const rowWhSel = document.querySelectorAll('.sl-row-wh-select')[rIdx];
        if (rowWhSel && sel.value) rowWhSel.value = sel.value;
    });
    fillAllSlQuantities(1);
    const ok = executeSlTransaction();
    if (ok) document.getElementById('sl-twostep-full-modal')?.remove();
}

function openSlTwoStepFullReceiptModal(invNo) {
    const doc = slCurrentDoc; if (!doc || typeof window.launchSlFullReceipt2027 !== 'function') return;
    const recTitle = document.getElementById('sl-lbl-rec-no-title');
    if (recTitle) recTitle.textContent = (slCurrentMode === 'sales') ? 'رقم التسليم' : 'رقم الاستلام';
    const whs = getSlWarehouses();
    const defWhId = document.getElementById('sl-master-warehouse')?.value || whs[0]?.id || '';
    const partyName = resolveSlPartyName(doc, slCurrentMode);
    const parties = getSlCachedStore(slCurrentMode === 'sales' ? 'erp_customers_v1' : 'erp_suppliers_v1', []) || [];
    window.launchSlFullReceipt2027(doc, slCurrentMode, whs, defWhId, partyName, parties, resolveSlItemMeta, () => {
        openSlSplitInspectModal(doc.invoiceNo);
    }, () => quickExecuteFullSlDoc(doc.invoiceNo, '__KEEP_ROWS__'));
}

window.slCurrentPage = 1;
window._slPageShowAll = false;
window.onSlPageSelectChange = function(val) {
    if (val === 'all') {
        window._slPageShowAll = true;
    } else {
        window._slPageShowAll = false;
        window.slCurrentPage = Math.max(1, parseInt(String(val || '').replace(/^p_/, ''), 10) || 1);
    }
    renderSlRegistry();
};
window.goToSlPage = function(p) {
    window._slPageShowAll = false;
    window.slCurrentPage = Math.max(1, parseInt(p, 10) || 1);
    renderSlRegistry();
};

const SL_REG_PREFS_KEY = 'sl_reg_table_prefs_v3';
const SL_REG_DEF_COLS = {
    date: { w: 9, vis: true, lbl: 'التاريخ' },
    inv: { w: 10, vis: true, lbl: 'الفاتورة' },
    recno: { w: 14, vis: true, lbl: 'رقم الاستلام' },
    isplit: { w: 10, vis: true, lbl: 'التقسيم' },
    party: { w: 15, vis: true, lbl: 'المورد' },
    iname: { w: 18, vis: true, lbl: 'اسم الصنف' },
    iorig: { w: 12, vis: true, lbl: 'أصل الفاتورة' },
    iqty: { w: 13, vis: true, lbl: 'أصل الفاتورة' },
    irem: { w: 11, vis: false, lbl: 'المتبقي' },
    ibar: { w: 9, vis: false, lbl: 'الشريط' },
    iprice: { w: 8, vis: false, lbl: 'السعر' },
    wh: { w: 9, vis: false, lbl: 'المخزن والحالة' },
    actions: { w: 15, vis: true, lbl: 'إجراءات' }
};
window.getSlRegTablePrefs = function() {
    try {
        const p = JSON.parse(localStorage.getItem(SL_REG_PREFS_KEY) || localStorage.getItem('sl_reg_table_prefs_v2') || '{}');
        const cols = {};
        Object.keys(SL_REG_DEF_COLS).forEach(k => { cols[k] = Object.assign({}, SL_REG_DEF_COLS[k], (p.cols && p.cols[k]) || {}); });
        return { cols };
    } catch (_) { return { cols: JSON.parse(JSON.stringify(SL_REG_DEF_COLS)) }; }
};
window.applySlRegTablePrefs = function() {
    const prefs = window.getSlRegTablePrefs();
    const tbl = document.getElementById('sl-main-reg-table');
    if (!tbl) return;
    const isPendingTab = (slRegistryFilter === 'pending');
    const isPartialTab = (slRegistryFilter === 'partial');
    const isDoneTab = (slRegistryFilter === 'done');
    const isSales = (slCurrentMode === 'sales');
    const isNotesTab = (slRegistryFilter === 'notes');
    const partyTh = document.getElementById('sl-reg-th-party');
    if (partyTh) {
        const pn = Array.from(partyTh.childNodes).find(n => n.nodeType === 3);
        const pHdr = isSales ? 'العميل' : 'المورد';
        if (pn) pn.nodeValue = pHdr; else partyTh.textContent = pHdr;
    }
    const execTh1 = document.getElementById('sl-th-exec-qty-1');
    if (execTh1) execTh1.textContent = isSales ? 'الكمية المسلّمة (1)' : 'الكمية المستلمة (1)';
    const origTh = document.getElementById('sl-th-orig-qty');
    if (origTh) origTh.textContent = isSales ? 'كمية البيع' : 'كمية الشراء';
    const chkOrig = document.getElementById('sl-lbl-chk-orig');
    if (chkOrig) chkOrig.textContent = isSales ? 'كمية البيع' : 'كمية الشراء';
    const chkExec1 = document.getElementById('sl-lbl-chk-exec1');
    if (chkExec1) chkExec1.textContent = isSales ? 'الكمية المسلّمة (1)' : 'الكمية المستلمة (1)';
    const recTh = tbl.querySelector('th[data-regcol="recno"]');
    if (recTh) {
        const tn = Array.from(recTh.childNodes).find(n => n.nodeType === 3);
        const hdrTxt = isPartialTab ? 'رقم التقسيم' : (isSales ? 'رقم التسليم' : 'رقم الاستلام');
        if (tn) tn.nodeValue = hdrTxt; else recTh.textContent = hdrTxt;
    }
    const qtyTh = tbl.querySelector('th[data-regcol="iqty"]');
    if (qtyTh) {
        const qn = Array.from(qtyTh.childNodes).find(n => n.nodeType === 3);
        const qHdr = isPartialTab ? 'المتبقي' : ((isDoneTab || isNotesTab) ? (isSales ? 'المسلّم' : 'المستلم') : 'أصل الفاتورة');
        if (qn) qn.nodeValue = qHdr; else qtyTh.textContent = qHdr;
    }
    const showOrigCol = (isPartialTab || isDoneTab);
    Object.keys(prefs.cols).forEach(k => {
        const c = prefs.cols[k];
        const showCol = ((k === 'recno' || k === 'isplit') && isPendingTab) ? false : (k === 'iorig' && !showOrigCol) ? false : (k === 'irem' && !isDoneTab) ? false : !!c.vis;
        tbl.querySelectorAll(`[data-regcol="${k}"]`).forEach(el => {
            el.style.display = showCol ? '' : 'none';
            if (el.tagName === 'TH') {
                el.style.width = c.w + '%';
                if (!el.querySelector('.sl-reg-th-resizer')) {
                    const rz = document.createElement('span');
                    rz.className = 'sl-reg-th-resizer';
                    rz.title = 'اسحب لتكبير أو تصغير العمود';
                    rz.onmousedown = (ev) => window.startSlRegColDrag(ev, k);
                    el.appendChild(rz);
                }
            }
        });
    });
};
window.startSlRegColDrag = function(ev, colKey) {
    ev.preventDefault(); ev.stopPropagation();
    const tbl = document.getElementById('sl-main-reg-table');
    if (!tbl) return;
    const tblW = Math.max(300, tbl.getBoundingClientRect().width);
    const prefs = window.getSlRegTablePrefs();
    const startX = ev.clientX, startW = prefs.cols[colKey].w;
    const onMove = (e) => {
        const deltaPct = Math.round(((startX - e.clientX) / tblW) * 100);
        prefs.cols[colKey].w = Math.max(5, Math.min(60, startW + deltaPct));
        localStorage.setItem(SL_REG_PREFS_KEY, JSON.stringify(prefs));
        window.applySlRegTablePrefs();
        window.renderSlRegColsDrawer();
    };
    const onUp = () => { document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); };
    document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp);
};
window.toggleSlRegColsDrawer = function() {
    const d = document.getElementById('sl-reg-cols-drawer');
    if (!d) return;
    const open = d.style.display === 'none' || !d.style.display;
    d.style.display = open ? 'flex' : 'none';
    if (open) window.renderSlRegColsDrawer();
};
window.renderSlRegColsDrawer = function() {
    const d = document.getElementById('sl-reg-cols-drawer');
    if (!d || d.style.display === 'none') return;
    const prefs = window.getSlRegTablePrefs();
    const isPendingTab = (slRegistryFilter === 'pending');
    const isPartialTab = (slRegistryFilter === 'partial');
    const isDoneTab = (slRegistryFilter === 'done');
    const isSales = (slCurrentMode === 'sales');
    const isNotesTab = (slRegistryFilter === 'notes');
    const showOrigCol = (isPartialTab || isDoneTab);
    const colChips = Object.keys(SL_REG_DEF_COLS).filter(k => {
        if ((k === 'recno' || k === 'isplit') && isPendingTab) return false;
        if (k === 'iorig' && !showOrigCol) return false;
        if (k === 'irem' && !isDoneTab) return false;
        return true;
    }).map(k => {
        const c = prefs.cols[k];
        const dynLbl = k === 'iqty'
            ? (isPartialTab ? 'المتبقي' : ((isDoneTab || isNotesTab) ? (isSales ? 'المسلّم' : 'المستلم') : 'أصل الفاتورة'))
            : (k === 'recno' ? (isPartialTab ? 'رقم التقسيم' : (isSales ? 'رقم التسليم' : 'رقم الاستلام')) : (k === 'party' ? (isSales ? 'العميل' : 'المورد') : c.lbl));
        return `<div style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:8px;background:rgba(30,41,59,0.75);border:1px solid rgba(148,163,184,0.35);font-size:0.75rem;font-weight:900;color:#fff;">
            <input type="checkbox" ${c.vis ? 'checked' : ''} onchange="updSlRegCol('${k}','vis',this.checked)" style="cursor:pointer;">
            <span>${dynLbl}</span>
            <button type="button" onclick="updSlRegCol('${k}','w',-2)" class="sl-btn" style="padding:1px 6px;font-size:0.72rem;" title="تصغير العمود">➖</button>
            <span style="color:#fde047;min-width:28px;text-align:center;font-family:monospace;">${c.w}%</span>
            <button type="button" onclick="updSlRegCol('${k}','w',2)" class="sl-btn" style="padding:1px 6px;font-size:0.72rem;" title="تكبير العمود">➕</button>
        </div>`;
    }).join('');
    d.innerHTML = `<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;"><div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;"><span style="font-size:0.78rem;font-weight:900;color:#e9d5ff;">📐 التحكم المستقل في كل عمود (إظهار/إخفاء وتكبير/تصغير):</span>${colChips}</div><div style="display:flex;gap:6px;"><button type="button" onclick="saveSlRegPrefsPerm()" class="sl-btn" style="padding:4px 10px;font-size:0.75rem;background:linear-gradient(135deg,#059669,#047857);border-color:#6ee7b7;color:#fff;">💾 حفظ نهائي</button><button type="button" onclick="resetSlRegPrefs()" class="sl-btn" style="padding:4px 9px;font-size:0.74rem;">↺ افتراضي</button></div></div>`;
};
window.updSlRegCol = function(k, prop, val) {
    const prefs = window.getSlRegTablePrefs();
    if (!prefs.cols[k]) return;
    if (prop === 'vis') prefs.cols[k].vis = !!val;
    else if (prop === 'w') prefs.cols[k].w = Math.max(4, Math.min(55, (prefs.cols[k].w || 10) + val));
    localStorage.setItem(SL_REG_PREFS_KEY, JSON.stringify(prefs));
    window.applySlRegTablePrefs();
    window.renderSlRegColsDrawer();
};
window.saveSlRegPrefsPerm = function() {
    const prefs = window.getSlRegTablePrefs();
    localStorage.setItem(SL_REG_PREFS_KEY, JSON.stringify(prefs));
    fetch('api/save_grid_config.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ grid_id: SL_REG_PREFS_KEY, config: prefs }) }).catch(() => {});
    showSlToast('✅ تم حفظ تنسيق الأعمدة نهائياً في قاعدة البيانات', 'success');
};
window.resetSlRegPrefs = function() {
    localStorage.removeItem(SL_REG_PREFS_KEY);
    try {
        const rcMap = JSON.parse(localStorage.getItem('erp_rc_col_styles_v1') || '{}');
        Object.keys(rcMap).forEach(k => { if (k.includes('smart-logistics.php::#sl-main-reg-table::')) delete rcMap[k]; });
        localStorage.setItem('erp_rc_col_styles_v1', JSON.stringify(rcMap));
        fetch('api/save_grid_config.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ grid_id: 'rc_col_styles_v1', config: rcMap }) }).catch(() => {});
        if (typeof window.__applyLockedFieldStyleSheet === 'function') window.__applyLockedFieldStyleSheet();
    } catch (_) {}
    fetch('api/save_grid_config.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ grid_id: SL_REG_PREFS_KEY, config: { cols: SL_REG_DEF_COLS } }) }).catch(() => {});
    renderSlRegistry();
    window.renderSlRegColsDrawer();
    showSlToast('↺ تمت استعادة الضبط الافتراضي المعتمد للأعمدة والخطوط', 'info');
};
fetch('api/get_grid_config.php?grid_id=' + SL_REG_PREFS_KEY).then(r => r.json()).then(d => {
    if (d && d.config && d.config.cols) {
        if (!localStorage.getItem(SL_REG_PREFS_KEY)) localStorage.setItem(SL_REG_PREFS_KEY, JSON.stringify(d.config));
        window.applySlRegTablePrefs();
    }
}).catch(() => {});
window.slRowEditDoc = function(invNo, st) {
    if (!loadSlDocument(invNo)) return;
    if (st === 'done' || st === 'partial' || slCurrentDoc?._slStatus === 'done' || slCurrentDoc?._slStatus === 'partial') {
        window.openSlModernConfirm(
            `تعديل إذن الاستلام — فاتورة #${invNo}`,
            `سيتم فتح الإذن لتعديل الكميات أو المستودعات أو بيانات السائق ثم إعادة اعتماده.`,
            'فتح للتعديل',
            'warn',
            () => { undoSlTransaction(true); openSlSplitInspectModal(invNo); }
        );
        return;
    }
    openSlSplitInspectModal(invNo);
};
window.slRowOpenOrigInv = function(invNo) {
    const targetPage = (slCurrentMode === 'sales') ? 'sales.php' : 'purchase.php';
    window.location.href = `${targetPage}?inv=${encodeURIComponent(invNo)}&edit=${encodeURIComponent(invNo)}&from=smart-logistics.php`;
};
window.printSlBarcodeStickers = function(invNo, noteId) {
    if (invNo && !loadSlDocument(invNo)) return;
    if (!slCurrentDoc) return;
    const docRef = noteId || slCurrentDoc._slNoteNo || ('#' + slCurrentDoc.invoiceNo);
    const whs = getSlWarehouses();
    const defWh = slCurrentDoc.deliveredWarehouseName || slCurrentDoc.warehouseName || whs[0]?.name || 'المخزن الرئيسي';
    const svgBars = (str) => {
        const s = String(str || '1001'); let bars = '', x = 6;
        for (let i = 0; i < s.length; i++) {
            const c = s.charCodeAt(i), w1 = (c % 3) + 1.5, w2 = ((c >> 2) % 2) + 1;
            bars += `<rect x="${x}" y="4" width="${w1}" height="34" fill="#000"/><rect x="${x + w1 + 2}" y="4" width="${w2}" height="34" fill="#000"/>`;
            x += w1 + w2 + 5.5;
        }
        return `<svg viewBox="0 0 ${Math.max(120, x + 6)} 42" width="100%" height="42">${bars}</svg>`;
    };
    const cards = (slCurrentDoc.items || []).map(it => {
        const m = resolveSlItemMeta(it);
        const rcvQ = (parseFloat(it.receivedQty || it.deliveredQty) > 0) ? parseFloat(it.receivedQty || it.deliveredQty) : parseFloat(m.qty || 0);
        const baseQ = +(rcvQ * (m.factor || 1)).toFixed(2);
        const batch = it.batchNo || ('LOT-' + String(docRef).replace(/^#/, '').slice(-6));
        const exp = it.expiryDate || (slCurrentDoc.date || '').slice(0, 10) || '—';
        const whNm = it.warehouseName || defWh;
        return `<div class="lbl-card"><div class="lbl-title">${m.name}</div><div>${svgBars(m.code + '-' + batch)}</div><div class="lbl-code">${m.code} | الدفعة: ${batch}</div><div class="lbl-meta"><span>الكمية: <b>${rcvQ} ${m.unit}${m.factor > 1 ? ` (${baseQ} ${m.baseUnit})` : ''}</b></span><span>المخزن: <b>${whNm}</b></span></div><div class="lbl-meta"><span>التاريخ/الصلاحية: <b>${exp}</b></span><span>الإذن: <b>${docRef}</b></span></div></div>`;
    }).join('');
    const w = window.open('', '_blank', 'width=920,height=720');
    if (!w) return showSlToast('يرجى السماح بالنوافذ المنبثقة لطباعة الملصقات', 'warning');
    w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>ملصقات باركود - ${docRef}</title><style>body{font-family:'Cairo','Tajawal',sans-serif;margin:18px;background:#f8fafc;color:#0f172a}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px}.lbl-card{background:#fff;border:2px dashed #0f172a;border-radius:10px;padding:10px 12px;text-align:center;page-break-inside:avoid}.lbl-title{font-size:14px;font-weight:900;margin-bottom:4px}.lbl-code{font-family:monospace;font-size:11.5px;font-weight:800;margin-bottom:6px}.lbl-meta{display:flex;justify-content:space-between;font-size:11px;border-top:1px solid #e2e8f0;padding-top:4px;margin-top:4px}@media print{.no-print{display:none!important}body{background:#fff;margin:5px}}</style></head><body><div class="no-print" style="margin-bottom:14px;display:flex;gap:10px;"><button onclick="window.print()" style="padding:9px 22px;background:#7c3aed;color:#fff;border:none;border-radius:8px;font-weight:800;cursor:pointer;">طباعة ملصقات الباركود الآن</button><button onclick="window.close()" style="padding:9px 16px;background:#e2e8f0;color:#0f172a;border:none;border-radius:8px;font-weight:700;cursor:pointer;">إغلاق</button></div><div class="grid">${cards}</div></body></html>`);
    w.document.close();
};
window.slPrintInspectionSheet = function(invNo) {
    if (!loadSlDocument(invNo)) return;
    printSlOfficialNote();
};
window.slRowPrintDoc = function(invNo, noteId = null) { if (loadSlDocument(invNo)) printSlOfficialNote(noteId); };
window.slRowWhatsAppDoc = function(invNo, noteId = null) { if (loadSlDocument(invNo)) shareSlWhatsApp(noteId); };
window.slRowUndoSingleNote = function(noteId, invNo) {
    if (!noteId || slCurrentMode === 'sales') return window.slRowDeleteUndoDoc(invNo, 'done');
    window.openSlModernConfirm(
        `عكس الإذن المستقل #${noteId}`,
        `سيتم خصم كمية هذا الإذن من المخزن وإعادتها إلى الرصيد المتبقي للفاتورة #${invNo} دون المساس بأصل الفاتورة.`,
        'تأكيد عكس الإذن',
        'danger',
        () => window._execSlUndoSingleNote(noteId, invNo)
    );
};
window._execSlUndoSingleNote = function(noteId, invNo) {
    invalidateSlStoreCache();
    const allPur = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
    const gIdx = allPur.findIndex(x => x && x.isGRN && !x.deleted && String(x.id) === String(noteId));
    if (gIdx === -1) return window.slRowDeleteUndoDoc(invNo, 'done');
    const grn = allPur[gIdx];
    const origIdx = allPur.findIndex(x => x && !x.isGRN && String(x.invoiceNo || x.id) === String(invNo));
    const wasPendingWh = origIdx !== -1 && String(allPur[origIdx].warehouseId || allPur[origIdx].warehouse || '') === 'PENDING_RECEIPT';
    let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
    let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
    (grn.items || []).forEach(gItem => {
        const revQty = parseFloat(gItem.customBaseQty || gItem.quantity || 0) || 0;
        const whId = gItem.warehouseId;
        const entry = inventory.find(x => x && String(x.itemId || x.id) === String(gItem.itemId) && (String(x.warehouse) === String(gItem.warehouseName) || String(x.warehouseId) === String(whId)));
        if (entry) entry.qty = Math.max(0, (parseFloat(entry.qty || 0) - revQty));
        const mIdx = erpItems.findIndex(x => x && (String(x.id) === String(gItem.itemId) || String(x.name || '').trim() === String(gItem.name || '').trim()));
        if (mIdx !== -1) {
            const mIt = erpItems[mIdx];
            if (mIt.stock && mIt.stock[whId] !== undefined) mIt.stock[whId] = Math.max(0, (parseFloat(mIt.stock[whId]) || 0) - revQty);
            if (mIt.actualStock && mIt.actualStock[whId] !== undefined) mIt.actualStock[whId] = Math.max(0, (parseFloat(mIt.actualStock[whId]) || 0) - revQty);
            if (wasPendingWh && mIt.stock) mIt.stock['PENDING_RECEIPT'] = (parseFloat(mIt.stock['PENDING_RECEIPT']) || 0) + revQty;
            const realStock = Object.entries(mIt.stock || {}).filter(([k]) => k !== 'PENDING_RECEIPT').reduce((a, [, v]) => a + (parseFloat(v) || 0), 0);
            mIt.currentStock = realStock; mIt.quantity = realStock;
            erpItems[mIdx] = mIt;
        }
    });
    window._lastSlGrnSig = null; window._lastSlGrnTs = 0;
    allPur[gIdx].deleted = true; allPur[gIdx].isDeleted = true;
    try {
        const delGrns = JSON.parse(localStorage.getItem('erp_deleted_grns_v1') || '[]');
        if (!delGrns.includes(String(noteId))) { delGrns.push(String(noteId)); localStorage.setItem('erp_deleted_grns_v1', JSON.stringify(delGrns)); }
        ['erp_purchases_v5', 'magaies_local_purchase_invoices_v1', 'erp_grn_v1'].forEach(sk => {
            const arr = JSON.parse(localStorage.getItem(sk) || '[]');
            if (Array.isArray(arr)) { arr.forEach(p => { if (p && String(p.id) === String(noteId)) { p.deleted = true; p.isDeleted = true; } }); localStorage.setItem(sk, JSON.stringify(arr)); }
        });
    } catch (_) {}
    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPur));
    localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
    localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));
    if (typeof window.pushToCloud === 'function') window.pushToCloud(true);
    invalidateSlStoreCache();
    renderSlRegistry();
    showSlToast(`↩️ تم إلغاء الإذن #${noteId} وإعادة كميته لقيد الاستلام`, 'warning');
};
window.slRowDeleteUndoDoc = function(invNo, st) {
    if (!loadSlDocument(invNo)) return;
    if (st === 'done' || st === 'partial' || slCurrentDoc?._slStatus !== 'pending') {
        undoSlTransaction(false);
        renderSlRegistry();
    }
};

// ── PROPORTIONAL CENTERED ZOOM ENGINE (100% = EXACT VIEWPORT FIT, SHRINKS X & Y EQUALLY IN CENTER) ──
window.__slPropZoom = 1;
window.applySlProportionalZoom = function(scaleVal) {
    const s = Math.max(0.5, Math.min(1, +(parseFloat(scaleVal) || 1).toFixed(2)));
    window.__slPropZoom = s;
    const wrap = document.getElementById('sl-main-wrapper');
    const stage = document.getElementById('sl-scale-stage');
    if (wrap) {
        wrap.style.width = '100%';
        wrap.style.maxWidth = '100%';
        wrap.style.transform = s === 1 ? 'none' : `scale(${s})`;
        wrap.style.transformOrigin = 'center center';
        wrap.style.borderRadius = s < 1 ? '14px' : '0';
        wrap.style.border = s < 1 ? '2px solid rgba(56,189,248,0.45)' : 'none';
    }
    if (stage) stage.style.alignItems = s < 1 ? 'center' : 'flex-start';
    const fullModalBox = document.getElementById('magaies-luxury-full-rcv-box');
    if (fullModalBox) {
        fullModalBox.style.transform = s === 1 ? 'none' : `scale(${s})`;
        fullModalBox.style.transformOrigin = 'center center';
        fullModalBox.style.borderRadius = s < 1 ? '14px' : '0';
    }
    const splitBox = document.querySelector('#sl-split-inspect-modal > div');
    if (splitBox) {
        splitBox.style.transform = s === 1 ? 'none' : `scale(${s})`;
        splitBox.style.transformOrigin = 'center center';
    }
};
window.addEventListener('wheel', function(e) {
    if (!e.ctrlKey) return;
    e.preventDefault();
    const nextS = (window.__slPropZoom || 1) + (e.deltaY < 0 ? 0.05 : -0.05);
    window.applySlProportionalZoom(nextS);
    showSlToast(`🔍 مقاس العرض المتناسب: ${Math.round(window.__slPropZoom * 100)}% (Ctrl+0 لملء الشاشة 100%)`, 'info', 1300);
}, { passive: false });
window.addEventListener('keydown', function(e) {
    if (!e.ctrlKey) return;
    if (e.key === '-' || e.key === '_') { e.preventDefault(); window.applySlProportionalZoom((window.__slPropZoom || 1) - 0.05); showSlToast(`🔍 مقاس العرض: ${Math.round(window.__slPropZoom * 100)}%`, 'info', 1200); }
    else if (e.key === '=' || e.key === '+') { e.preventDefault(); window.applySlProportionalZoom((window.__slPropZoom || 1) + 0.05); showSlToast(`🔍 مقاس العرض: ${Math.round(window.__slPropZoom * 100)}%`, 'info', 1200); }
    else if (e.key === '0') { e.preventDefault(); window.applySlProportionalZoom(1); showSlToast('🖥️ تم ضبط الشاشة ملء العرض بنسبة 100%', 'success', 1400); }
});
document.addEventListener('DOMContentLoaded', function() {
    try {
        if (window.MagaiesScreenDimensions && typeof window.MagaiesScreenDimensions.preview === 'function') {
            window.MagaiesScreenDimensions.preview(100, 100);
        }
        if (/^(localhost|127\.0\.0\.1)$/i.test(location.hostname) && 'caches' in window) {
            caches.keys().then(ks => ks.forEach(k => { if (!k.includes('v88')) caches.delete(k); })).catch(() => {});
            if (navigator.serviceWorker) navigator.serviceWorker.getRegistrations().then(rs => rs.forEach(r => r.update())).catch(() => {});
        }
    } catch (_) {}
    window.applySlProportionalZoom(1);
});

window.openSlSplitInspectModal = function(invNo) {
    if (invNo && !loadSlDocument(invNo)) return;
    const recTitle = document.getElementById('sl-lbl-rec-no-title');
    if (recTitle) recTitle.textContent = 'رقم التقسيم';
    const modal = document.getElementById('sl-split-inspect-modal');
    const slot = document.getElementById('sl-split-card3-slot');
    const card3 = document.getElementById('sl-doc-meta-card');
    if (card3 && slot) { card3.style.margin = '0'; slot.appendChild(card3); }
    const titleEl = document.getElementById('sl-split-modal-hdr-title');
    if (titleEl && slCurrentDoc) titleEl.textContent = `شاشة التقسيم والدفعات الجزئية — فاتورة #${slCurrentDoc.invoiceNo} (${resolveSlPartyName(slCurrentDoc, slCurrentMode)})`;
    if (modal) modal.style.display = 'block';
    setTimeout(() => document.querySelector('.sl-qty-primary-input')?.focus(), 180);
};

window.closeSlSplitInspectModal = function() {
    const recTitle = document.getElementById('sl-lbl-rec-no-title');
    if (recTitle) recTitle.textContent = (slCurrentMode === 'sales') ? 'رقم التسليم' : 'رقم الاستلام';
    const modal = document.getElementById('sl-split-inspect-modal');
    const host = document.getElementById('sl-inspection-cockpit');
    const card3 = document.getElementById('sl-doc-meta-card');
    if (card3 && host && card3.parentElement !== host) host.appendChild(card3);
    if (modal) modal.style.display = 'none';
};

// ── UNDO / REVERSE TRANSACTION ───────────────────────────────────────────────
function undoSlTransaction(skipConfirm = false) {
    if (!slCurrentDoc) {
        showSlToast('⚠️ اختر مستنداً من السجل العلوي أولاً لإلغاء حركته', 'warning');
        return;
    }
    const invNo = String(slCurrentDoc.invoiceNo);
    const isSales = (slCurrentMode === 'sales');

    if (!skipConfirm) {
        window.openSlModernConfirm(
            `عكس حركة ${isSales ? 'الصرف' : 'الاستلام'} — فاتورة #${invNo}`,
            `سيتم عكس الكمية من المخزن وإرجاع الفاتورة فوراً إلى قائمة «قيد الاستلام» لتتمكن من استلامها مجدداً دون المساس بأصل الفاتورة.`,
            'تأكيد العكس والإرجاع',
            'danger',
            () => { undoSlTransaction(true); renderSlRegistry(); }
        );
        return;
    }

    invalidateSlStoreCache();
    if (isSales) {
        const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        const idx = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);
        if (idx === -1) return;
        const inv = allSales[idx];
        const fallbackWhId = inv.deliveredWarehouse || inv.warehouseId || inv.warehouse || getSlWarehouses()[0].id;
        let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
        let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

        (inv.items || inv.rows || []).forEach(it => {
            const retQty = parseFloat(it.deliveredQty || 0) || 0;
            it.deliveredQty = 0;
            it.remainingQty = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
            if (retQty <= 0) return;

            const rowWhId = it.warehouseId || fallbackWhId;
            const meta = resolveSlItemMeta(it);
            const itemNameClean = String(meta.name || '').trim();

            const entry = inventory.find(x =>
                x &&
                (String(x.id || x.itemId) === String(meta.itemId) || String(x.name || '').trim() === itemNameClean) &&
                (String(x.warehouseId || x.warehouse) === String(rowWhId) || String(x.warehouse) === String(it.warehouseName || ''))
            );
            if (entry) {
                entry.qty = +((parseFloat(entry.qty || 0) || 0) + retQty).toFixed(4);
            }

            const masterIdx = erpItems.findIndex(x =>
                x && (String(x.id) === String(meta.itemId) || String(x.name || '').trim() === itemNameClean)
            );
            if (masterIdx !== -1) {
                const mItem = erpItems[masterIdx];
                if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
                if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
                mItem.stock[rowWhId] = +((parseFloat(mItem.stock[rowWhId]) || 0) + retQty).toFixed(4);
                mItem.actualStock[rowWhId] = +((parseFloat(mItem.actualStock[rowWhId]) || 0) + retQty).toFixed(4);
                if (mItem.quantity !== undefined) {
                    mItem.quantity = +((parseFloat(mItem.quantity) || 0) + retQty).toFixed(4);
                }
                erpItems[masterIdx] = mItem;
            }
        });

        inv.isDelivered = false;
        inv.deliveryStatus = 'pending';
        inv.status = 'pending_delivery';
        if (Array.isArray(inv.rows)) inv.rows = inv.items;
        allSales[idx] = inv;
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
        localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));
        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
        localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));

        let delNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
        delNotes = delNotes.filter(n => String(n.invoiceNo || n.relatedInvoice) !== invNo);
        localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(delNotes));
        invalidateSlStoreCache();

        loadSlDocument(invNo);
        showSlToast(`↩️ تم إلغاء صرف الفاتورة #${invNo} وإعادة الكميات للمخازن بدقة`, 'warning');
    } else {
        let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
        const grnsToUndo = allPurchases.filter(p => p && !p.deleted && !p.isDeleted && p.isGRN && String(p.originalInvoiceId) === invNo);
        let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
        let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
        const origIdx = allPurchases.findIndex(p => !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === invNo);
        const wasPendingWh = origIdx !== -1 && String(allPurchases[origIdx].warehouseId || allPurchases[origIdx].warehouse || '') === 'PENDING_RECEIPT';

        grnsToUndo.forEach(grn => {
            (grn.items || []).forEach(gItem => {
                const revQty = parseFloat(gItem.customBaseQty || gItem.quantity || 0) || 0;
                const whId = gItem.warehouseId;
                const entry = inventory.find(x =>
                    x && String(x.itemId || x.id) === String(gItem.itemId) &&
                    (String(x.warehouse) === String(gItem.warehouseName) || String(x.warehouseId) === String(whId))
                );
                if (entry) entry.qty = Math.max(0, (parseFloat(entry.qty || 0) - revQty));
                const mIdx = erpItems.findIndex(x => x && (String(x.id) === String(gItem.itemId) || String(x.name || '').trim() === String(gItem.name || '').trim()));
                if (mIdx !== -1) {
                    const mIt = erpItems[mIdx];
                    if (mIt.stock && mIt.stock[whId] !== undefined) mIt.stock[whId] = Math.max(0, (parseFloat(mIt.stock[whId]) || 0) - revQty);
                    if (mIt.actualStock && mIt.actualStock[whId] !== undefined) mIt.actualStock[whId] = Math.max(0, (parseFloat(mIt.actualStock[whId]) || 0) - revQty);
                    if (wasPendingWh && mIt.stock) mIt.stock['PENDING_RECEIPT'] = (parseFloat(mIt.stock['PENDING_RECEIPT']) || 0) + revQty;
                    const realStock = Object.entries(mIt.stock || {}).filter(([k]) => k !== 'PENDING_RECEIPT').reduce((a, [, v]) => a + (parseFloat(v) || 0), 0);
                    mIt.currentStock = realStock; mIt.quantity = realStock;
                    erpItems[mIdx] = mIt;
                }
            });
        });
        window._lastSlGrnSig = null; window._lastSlGrnTs = 0;
        let delGrns = [];
        try { delGrns = JSON.parse(localStorage.getItem('erp_deleted_grns_v1') || '[]'); } catch (_) {}
        grnsToUndo.forEach(grn => {
            if (grn.id && !delGrns.includes(String(grn.id))) delGrns.push(String(grn.id));
            grn.deleted = true; grn.isDeleted = true;
        });
        localStorage.setItem('erp_deleted_grns_v1', JSON.stringify(delGrns));

        const resetOrigInvState = (invObj) => {
            if (!invObj) return;
            invObj.receiptStatus = 'pending_receipt';
            invObj.status = 'pending_receipt';
            delete invObj.grnNo; delete invObj.deliveredWarehouse; delete invObj.deliveredWarehouseName;
            (invObj.items || invObj.lines || []).forEach(ln => {
                if (ln && typeof ln === 'object') { delete ln.receivedQty; delete ln.deliveredQty; delete ln.remainingQty; delete ln._tempExecQty; }
            });
        };
        if (origIdx !== -1) resetOrigInvState(allPurchases[origIdx]);
        const undoneIds = new Set(grnsToUndo.map(g => String(g.id || '')).filter(Boolean));
        ['erp_purchases_v5', 'magaies_local_purchase_invoices_v1', 'erp_grn_v1'].forEach(sk => {
            try {
                const arr = JSON.parse(localStorage.getItem(sk) || '[]');
                if (Array.isArray(arr)) {
                    let hit = false;
                    arr.forEach(p => {
                        if (!p) return;
                        if (p.isGRN && (undoneIds.has(String(p.id || '')) || String(p.originalInvoiceId) === invNo)) { p.deleted = true; p.isDeleted = true; hit = true; }
                        else if (!p.isGRN && String(p.invoiceNo || p.id) === invNo) { resetOrigInvState(p); hit = true; }
                    });
                    if (hit) localStorage.setItem(sk, JSON.stringify(arr));
                }
            } catch (_) {}
        });

        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
        localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));
        if (typeof window.pushToCloud === 'function') window.pushToCloud(true);
        if (typeof window.logSystemAction === 'function') {
            window.logSystemAction('تحديث استلام مشتريات (GRN)', 'المشتريات / المخازن', String(invNo || ''), `تحديث حالة استلام فاتورة المشتريات #${invNo}`);
        }
        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
        invalidateSlStoreCache();

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

let slBridgePollInFlight = false;

function startSlLocalCamera() {
    const ph = document.getElementById('sl-camera-placeholder');
    const wrap = document.getElementById('sl-qr-reader-wrap');
    const stLbl = document.getElementById('sl-local-cam-status');
    if (ph) ph.style.display = 'none';
    if (wrap) wrap.style.display = 'flex';
    if (stLbl) stLbl.textContent = 'جارٍ الاتصال بكاميرا الجهاز...';

    const startNativeFallback = async () => {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) throw new Error('NO_MEDIA');
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            const box = document.getElementById('sl-qr-reader');
            if (!box) return;
            box.innerHTML = '<video id="sl-native-cam-vid" autoplay playsinline muted style="width:100%;display:block;border-radius:8px;"></video>';
            const vid = document.getElementById('sl-native-cam-vid');
            vid.srcObject = stream;
            window._slNativeStream = stream;
            if (stLbl) stLbl.textContent = '✅ كاميرا الجهاز تعمل — وجّه الباركود أمام العدسة';
            if ('BarcodeDetector' in window) {
                const det = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13'] });
                window._slNativeScanTimer = setInterval(async () => {
                    if (!vid || vid.readyState < 2) return;
                    try {
                        const codes = await det.detect(vid);
                        if (codes && codes.length && codes[0].rawValue) processSlContinuousQr(codes[0].rawValue);
                    } catch (_) {}
                }, 350);
            }
        } catch (err) {
            if (stLbl) stLbl.textContent = '⚠️ لا توجد كاميرا موصولة بهذا الكمبيوتر أو تم حظر الإذن — استخدم صورة QR أو كاميرا الجوال بالأسفل';
            showSlToast('⚠️ لا توجد كاميرا موصولة بهذا الجهاز — استخدم زر قراءة صورة QR أو كاميرا الجوال', 'warning', 3200);
        }
    };

    const bootQrScanner = () => {
        if (typeof Html5Qrcode === 'undefined') return startNativeFallback();
        if (slHtml5QrCode) { try { slHtml5QrCode.stop().catch(() => {}); } catch (e) {} }
        slHtml5QrCode = new Html5Qrcode('sl-qr-reader');
        Html5Qrcode.getCameras().then(cams => {
            if (!cams || !cams.length) return startNativeFallback();
            const backCam = cams.find(c => /back|rear|environment/i.test(c.label || ''));
            const camId = (backCam || cams[0]).id;
            return slHtml5QrCode.start(
                camId,
                { fps: 15, qrbox: { width: 220, height: 220 } },
                (decodedText) => processSlContinuousQr(decodedText),
                () => {}
            ).then(() => {
                if (stLbl) stLbl.textContent = `✅ كاميرا الجهاز متصلة (${(backCam || cams[0]).label || 'Webcam'}) — وجّه الباركود الآن`;
            });
        }).catch(() => startNativeFallback());
    };

    if (typeof Html5Qrcode === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/html5-qrcode';
        script.async = true;
        script.onload = bootQrScanner;
        script.onerror = startNativeFallback;
        document.head.appendChild(script);
        return;
    }
    bootQrScanner();
}

window.scanSlQrFromImageFile = async function(fileInp) {
    const f = fileInp && fileInp.files && fileInp.files[0];
    if (!f) return;
    try {
        if ('BarcodeDetector' in window) {
            const bmp = await createImageBitmap(f);
            const det = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13'] });
            const codes = await det.detect(bmp);
            if (codes && codes.length && codes[0].rawValue) { processSlContinuousQr(codes[0].rawValue); fileInp.value = ''; return; }
        }
        if (typeof Html5Qrcode !== 'undefined') {
            const tmp = new Html5Qrcode('sl-qr-reader');
            const txt = await tmp.scanFile(f, false);
            if (txt) processSlContinuousQr(txt);
        } else {
            showSlToast('لم يتم العثور على باركود واضح في الصورة', 'warning');
        }
    } catch (_) { showSlToast('تعذر قراءة باركود من هذه الصورة', 'warning'); }
    fileInp.value = '';
};

function stopSlLocalCamera() {
    const ph = document.getElementById('sl-camera-placeholder');
    const wrap = document.getElementById('sl-qr-reader-wrap');
    if (window._slNativeScanTimer) { clearInterval(window._slNativeScanTimer); window._slNativeScanTimer = null; }
    if (window._slNativeStream) { try { window._slNativeStream.getTracks().forEach(t => t.stop()); } catch (_) {} window._slNativeStream = null; }
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

    startSlMobileBridgeListener(true);
}

function closeSlMobileBridgeModal() {
    const modal = document.getElementById('sl-mobile-bridge-modal');
    if (modal) modal.style.display = 'none';
}

function startSlMobileBridgeListener(enableHttpPoll = false) {
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

    if (!enableHttpPoll || slBridgePollTimer) return;
    slBridgePollTimer = setInterval(async () => {
        if (slBridgePollInFlight || document.hidden || !navigator.onLine) return;
        slBridgePollInFlight = true;
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
        } catch (e) {
        } finally {
            slBridgePollInFlight = false;
        }
    }, 1400);
}

// ── PHOTO PROOF, TYPOGRAPHY & COLUMN CUSTOMIZATION ───────────────────────────
async function handleSlPhotoUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (window.ElsajanaAccountingBrain && typeof window.ElsajanaAccountingBrain.compressImageTo40KB === 'function') {
        try {
            const compressed = await window.ElsajanaAccountingBrain.compressImageTo40KB(file);
            slCurrentPhoto = compressed.dataUrl;
            const wrap = document.getElementById('sl-photo-preview-wrap');
            const img = document.getElementById('sl-photo-preview-img');
            if (wrap && img) { img.src = slCurrentPhoto; wrap.style.display = 'block'; }
            showSlToast(`📸 تم ضغط الصورة آلياً (${compressed.sizeKB}KB ≤ 40KB) وإرفاقها بنجاح`, 'success');
            return;
        } catch (_) {}
    }
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
    const s = parseInt(sizePx, 10) || 16;
    const rawC = (colorHex || '#f8fafc').trim();
    const isLight = document.documentElement.classList.contains('light-theme');
    const c = (isLight && ['#ffffff', '#fff', '#f8fafc', '#e2e8f0'].includes(rawC.toLowerCase())) ? 'var(--lt-ink, #120500)' : rawC;
    document.documentElement.style.setProperty('--sl-grid-font-size', `${s}px`);
    document.documentElement.style.setProperty('--sl-grid-font-color', c);
    localStorage.setItem('sl_custom_font_size', s);
    localStorage.setItem('sl_custom_font_color', rawC);
}

function applySlColVisibility() {
    const map = {
        'sl-col-chk-code': '.sl-col-code',
        'sl-col-chk-orig': '.sl-col-orig',
        'sl-col-chk-prev': '.sl-col-prev',
        'sl-col-chk-spec': '.sl-col-spec',
        'sl-col-chk-exec1': '.sl-col-exec1',
        'sl-col-chk-rem2': '.sl-col-rem2',
        'sl-col-chk-base': '.sl-col-base',
        'sl-col-chk-base2': '.sl-col-base2',
        'sl-col-chk-wh': '.sl-col-wh',
        'sl-col-chk-price': '.sl-col-price'
    };
    Object.keys(map).forEach(chkId => {
        const elChk = document.getElementById(chkId);
        const show = elChk ? (elChk.checked !== false) : true;
        document.querySelectorAll(map[chkId]).forEach(el => {
            el.style.display = show ? '' : 'none';
        });
    });
}

// ── OFFICIAL PRINT (QR GATE PASS / GRN) & WHATSAPP ───────────────────────────
function _findSlNoteRecord(noteId) {
    if (!noteId) return null;
    const isSales = (slCurrentMode === 'sales');
    const pool = isSales ? (getSlCachedStore('erp_delivery_notes_v1', []) || []) : (getSlCachedStore('erp_purchase_invoices_v1', []) || []);
    return pool.find(n => n && !n.deleted && !n.isDeleted && (isSales ? true : n.isGRN) && String(n.id || n.deliveryNo || '') === String(noteId)) || null;
}

function printSlOfficialNote(specificNoteId = null) {
    if (!slCurrentDoc) {
        showSlToast('⚠️ اختر مستنداً أولاً للطباعة', 'warning');
        return;
    }
    const isSales = (slCurrentMode === 'sales');
    const invNo = slCurrentDoc.invoiceNo;
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const matchedNote = _findSlNoteRecord(specificNoteId);
    const rawNoteNo = matchedNote ? (matchedNote.id || matchedNote.deliveryNo) : (specificNoteId || slCurrentDoc._slNoteNo || (isSales ? 'GDN' : 'GRN'));
    const splitSeqVal = matchedNote?.splitSeq || (String(rawNoteNo).includes('/') ? String(rawNoteNo).split('/').pop() : '');
    const isSplitPrint = !!(matchedNote?.isSplitPart || matchedNote?.isPartialDelivery || String(rawNoteNo).includes('/'));
    const noteNo = isSplitPrint && splitSeqVal && !String(rawNoteNo).includes('/') ? `${rawNoteNo}/${splitSeqVal}` : rawNoteNo;
    const splitBadgePrint = isSplitPrint && splitSeqVal ? ` &nbsp;|&nbsp; التقسيم: /${splitSeqVal}` : '';
    const noteDate = (matchedNote ? (matchedNote.date || matchedNote.savedAt || matchedNote.createdAt) : slCurrentDoc.date || '').slice(0, 10);
    const qrData = `${window.location.origin}/smart-logistics.php?mode=${slCurrentMode}&qr_deliver=${encodeURIComponent(invNo)}&note=${encodeURIComponent(noteNo)}`;
    const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=4&data=${encodeURIComponent(qrData)}`;

    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const whs = getSlWarehouses();
    const srcItems = matchedNote
        ? (matchedNote.items || []).filter(it => (parseFloat(it.quantity ?? it.qty ?? 0) || 0) > 0 && String(it.warehouseId || '') !== 'PENDING_RECEIPT')
        : (slCurrentDoc.items || []);

    const rowsHtml = srcItems.map((it, idx) => {
        const m = resolveSlItemMeta(it);
        const q = matchedNote ? (parseFloat(it.quantity ?? it.qty ?? m.qty) || 0) : (priInputs[idx] ? (parseFloat(priInputs[idx].value) || m.qty) : m.qty);
        const whId = matchedNote ? (it.warehouseId || matchedNote.warehouseId || matchedNote.warehouse || '') : (whSelects[idx]?.value || '');
        const whName = (matchedNote && (it.warehouseName || matchedNote.warehouseName || matchedNote.warehouse)) || (whs.find(w => String(w.id) === String(whId)) || whs[0]).name;
        return `
            <tr>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;">${idx + 1}</td>
                <td style="padding:8px; border:1px solid #000; font-weight:900;">${m.name}</td>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;">${whName}</td>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:900;">${q} ${m.unit}</td>
                <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;">${+(q * m.factor).toFixed(2)} ${m.baseUnit}</td>
            </tr>
        `;
    }).join('');

    const rcvName = (matchedNote && matchedNote.receiverName) || document.getElementById('sl-receiver-name')?.value || partyName;
    const drvName = (matchedNote && matchedNote.driverName) || document.getElementById('sl-driver-name')?.value || '---';
    const trkNo = (matchedNote && matchedNote.truckPlate) || document.getElementById('sl-truck-plate')?.value || '---';
    const whNoteTxt = (matchedNote && matchedNote.notes) || document.getElementById('sl-warehouse-notes')?.value || 'تم فحص ومطابقة الأصناف';

    const w = window.open('', '_blank', 'width=860,height=720');
    if (!w) return;
    w.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>${isSales ? 'إذن صرف مبيعات وتصريح بوابة (GDN)' : 'مذكرة استلام مشتريات مخزنية (GRN)'} - #${invNo} (${noteNo})</title>
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
                    <p style="margin:6px 0 0; font-weight:900; font-size:1.02rem;">رقم الفاتورة: #${invNo} &nbsp;|&nbsp; رقم التقسيم / الإذن: ${noteNo}${splitBadgePrint}${noteDate ? ` &nbsp;|&nbsp; التاريخ: ${noteDate}` : ''}</p>
                    <p style="margin:4px 0 0; font-weight:800;">${isSales ? '👤 العميل:' : '🏢 المورد:'} ${partyName} &nbsp;|&nbsp; المستلم/المسلم: ${rcvName}</p>
                    <p style="margin:4px 0 0; font-weight:800;">🚚 السائق: ${drvName} &nbsp;|&nbsp; 🔢 اللوحة: ${trkNo}</p>
                </div>
                <div style="text-align:center;">
                    <img src="${qrImgUrl}" style="width:120px; height:120px; border:2px solid #000; border-radius:10px;">
                    <div style="font-size:0.72rem; font-weight:900;">امسح للتحقق الفوري</div>
                </div>
            </div>
            <table>
                <thead>
                    <tr><th>#</th><th>الصنف</th><th>المستودع</th><th>الكمية بالوحدة</th><th>المعادل بالفرعية</th></tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <div style="margin-top:16px; padding:10px; border:1px dashed #000; font-weight:800;">
                ملاحظات المخزن والبوابة: ${whNoteTxt}
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:45px; font-weight:900;">
                <div>توقيع أمين المخزن: ________________</div>
                <div>توقيع السائق / المندوب: ________________</div>
                <div>ختم البوابة والاعتماد: ________________</div>
            </div>
            <script>window.onload = () => { setTimeout(() => window.print(), 350); };<\/script>
        </body>
        </html>
    `);
    w.document.close();
}

function shareSlWhatsApp(specificNoteId = null) {
    if (!slCurrentDoc) {
        showSlToast('⚠️ اختر مستنداً أولاً للمشاركة عبر واتساب', 'warning');
        return;
    }
    const isSales = (slCurrentMode === 'sales');
    const invNo = slCurrentDoc.invoiceNo;
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const matchedNote = _findSlNoteRecord(specificNoteId);
    const noteHdr = matchedNote ? `\n🔢 رقم الإذن/التقسيم: ${matchedNote.id || matchedNote.deliveryNo}` : '';
    const srcItems = matchedNote ? (matchedNote.items || []).filter(it => (parseFloat(it.quantity ?? it.qty ?? 0) || 0) > 0 && String(it.warehouseId || '') !== 'PENDING_RECEIPT') : (slCurrentDoc.items || []);
    const itemsTxt = srcItems.map(it => {
        const m = resolveSlItemMeta(it);
        const q = matchedNote ? (parseFloat(it.quantity ?? it.qty ?? m.qty) || 0) : m.qty;
        return `• ${m.name}: ${q} ${m.unit}`;
    }).join('\n');
    const msg = `${isSales ? '📤 *إذن صرف وتسليم مبيعات (GDN)*' : '📥 *مذكرة استلام مشتريات مخزنية (GRN)*'}\n📄 رقم الفاتورة: #${invNo}${noteHdr}\n👤 ${isSales ? 'العميل' : 'المورد'}: ${partyName}\n📦 *الأصناف:*\n${itemsTxt}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
}


// ── 3-LEVEL DEDUCTION & SPLITTING ENGINE (1: المدير، 2: مدير المخزن، 3: المخزنجي بالـ QR) ──
function setSlDeductionLevel(lvl, silent = false) {
    slDeductionLevel = parseInt(lvl, 10) || 1;
    localStorage.setItem('sl_deduction_level_v1', slDeductionLevel);

    [1, 2, 3].forEach(n => {
        const btn = document.getElementById('sl-lvl-btn-' + n);
        if (btn) {
            btn.className = 'sl-level-pill' + (n === slDeductionLevel ? (' active-lvl-' + n) : '');
        }
    });

    const priceChk = document.getElementById('sl-col-chk-price');
    const specChk = document.getElementById('sl-col-chk-spec');
    const prevChk = document.getElementById('sl-col-chk-prev');
    const splitAbBtn = document.getElementById('sl-btn-split-ab');
    const verifyBadge = document.getElementById('sl-verify-badge');

    const hasSavedCols = !!(silent && localStorage.getItem('sl_custom_grid_config_v1'));
    if (slDeductionLevel === 1) {
        slStorekeeperFocus = false;
        if (!hasSavedCols) {
            if (specChk) specChk.checked = true;
            if (prevChk) prevChk.checked = true;
        }
        if (splitAbBtn) splitAbBtn.style.display = '';
        if (verifyBadge) verifyBadge.innerHTML = '👑 المستوى 1: صلاحية المدير الكاملة';
        if (!silent) showSlToast('👑 المستوى 1 (المدير العام): صلاحية كاملة للتقسيم المالي والمخزني واعتماد الإغلاق الإداري L4', 'success', 2600);
    } else if (slDeductionLevel === 2) {
        slStorekeeperFocus = false;
        if (!hasSavedCols) {
            if (priceChk) priceChk.checked = true;
            if (specChk) specChk.checked = true;
            if (prevChk) prevChk.checked = true;
        }
        if (splitAbBtn) splitAbBtn.style.display = '';
        if (verifyBadge) verifyBadge.innerHTML = '🏢 المستوى 2: تقسيم وتوزيع مدير المخزن';
        if (!silent) showSlToast('🏢 المستوى 2 (مدير المخزن): تقسيم الأصناف وتوزيعها على المخازن والدفعات واعتماد الصرف/التوريد', 'success', 2600);
    } else {
        slStorekeeperFocus = true;
        if (!hasSavedCols) {
            if (priceChk) priceChk.checked = true;
            if (specChk) specChk.checked = false;
            if (prevChk) prevChk.checked = true;
        }
        if (splitAbBtn) splitAbBtn.style.display = 'none';
        const autoQr = document.getElementById('sl-auto-execute-qr');
        if (autoQr) autoQr.checked = true;
        if (verifyBadge) verifyBadge.innerHTML = '📦 المستوى 3: خصم المخزنجي الفوري بالـ QR';
        if (!silent) showSlToast('📦 المستوى 3 (المخزنجي + QR): تم تفعيل وضع الراحة والتركيز الميداني — مرّر الباركود للخصم الفوري في جزء مخزنك!', 'success', 2800);
    }

    applySlColVisibility();
    if (slCurrentDoc) loadSlDocument(slCurrentDoc.invoiceNo);
}

function toggleSlStorekeeperFocus() {
    slStorekeeperFocus = !slStorekeeperFocus;
    const priceChk = document.getElementById('sl-col-chk-price');
    const specChk = document.getElementById('sl-col-chk-spec');
    if (slStorekeeperFocus) {
        if (priceChk) priceChk.checked = false;
        if (specChk) specChk.checked = false;
        applySlTypography(17, '#ffffff');
        showSlToast('🎯 تم تفعيل وضع تركيز المخزنجي (تكبير الأرقام وإخفاء الأعمدة الثانوية لراحة العين)', 'success', 2400);
    } else {
        if (specChk) specChk.checked = true;
        applySlTypography(15, '#f8fafc');
        showSlToast('👁️ تم استعادة العرض الكامل لجميع الأعمدة', 'success', 2000);
    }
    applySlColVisibility();
}

function splitSlItemRow(idx) {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items[idx]) return;
    const priInputs = document.querySelectorAll('.sl-qty-primary-input');
    const whSelects = document.querySelectorAll('.sl-row-wh-select');
    const whs = getSlWarehouses();

    slCurrentDoc.items.forEach((it, i) => {
        if (priInputs[i]) it._tempExecQty = parseFloat(priInputs[i].value) || 0;
        if (whSelects[i]) it.warehouseId = whSelects[i].value;
    });

    const parentRow = slCurrentDoc.items[idx];
    const meta = resolveSlItemMeta(parentRow);
    const prevDel = parseFloat(parentRow.deliveredQty || parentRow.receivedQty || 0) || 0;
    const remQty = Math.max(0, +((parseFloat(parentRow.qty ?? meta.qty) || meta.qty) - prevDel).toFixed(4));
    if (remQty <= 0) {
        showSlToast('⚠️ الكمية المتبقية في هذا السطر صفر — لا يمكن تقسيمها', 'warning');
        return;
    }

    const typedQty = parentRow._tempExecQty !== undefined ? parentRow._tempExecQty : remQty;
    const part1 = (typedQty > 0 && typedQty < remQty) ? +typedQty.toFixed(4) : +(remQty / 2).toFixed(2);
    const part2 = +(remQty - part1).toFixed(4);

    if (part2 <= 0) {
        showSlToast('⚠️ الكمية صغيرة جداً للتقسيم بين سطرين', 'warning');
        return;
    }

    const curWhId = parentRow.warehouseId || document.getElementById('sl-master-warehouse')?.value || whs[0].id;
    const altWh = whs.find(w => String(w.id) !== String(curWhId)) || whs[0];
    const splitGroupId = parentRow._splitGroup || ('slgrp_' + idx + '_' + Date.now());
    const totalGroupRem = parseFloat(parentRow._groupTotalRem || remQty) || remQty;

    parentRow._splitGroup = splitGroupId;
    parentRow._groupTotalRem = totalGroupRem;
    parentRow._origLineQty = parseFloat(parentRow._origLineQty || parentRow.qty || meta.qty) || meta.qty;
    parentRow.qty = +(prevDel + part1).toFixed(4);
    parentRow.quantity = parentRow.qty;
    parentRow.remainingQty = totalGroupRem;
    parentRow._tempExecQty = part1;

    const childRow = {
        ...JSON.parse(JSON.stringify(parentRow)),
        qty: part2,
        quantity: part2,
        deliveredQty: 0,
        receivedQty: 0,
        remainingQty: totalGroupRem,
        _tempExecQty: part2,
        warehouseId: altWh.id,
        warehouseName: altWh.name,
        _isSplitChild: true,
        _splitGroup: splitGroupId,
        _groupTotalRem: totalGroupRem,
        _parentIdx: idx
    };

    slCurrentDoc.items.splice(idx + 1, 0, childRow);
    renderSlItemsGrid();
    showSlToast(`✂️ تم تقسيم الصنف (${meta.name}) بدقة محاسبية إلى دفعتين/مخزنين (${part1} + ${part2} = ${remQty} ${meta.unit})`, 'success');
}

function mergeSlSplitRow(idx) {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items[idx]) return;
    const child = slCurrentDoc.items[idx];
    const parent = slCurrentDoc.items[idx - 1];
    if (parent) {
        const restoredOrig = parseFloat(parent._origLineQty || ((parseFloat(parent.qty || 0) || 0) + (parseFloat(child.qty || 0) || 0))) || 0;
        const prevDel = parseFloat(parent.deliveredQty || parent.receivedQty || 0) || 0;
        parent.qty = +restoredOrig.toFixed(4);
        parent.quantity = parent.qty;
        parent.remainingQty = Math.max(0, +(restoredOrig - prevDel).toFixed(4));
        delete parent._tempExecQty;
        delete parent._splitGroup;
        delete parent._groupTotalRem;
    }
    slCurrentDoc.items.splice(idx, 1);
    renderSlItemsGrid();
    showSlToast('🔗 تم دمج السطر الفرعي مع السطر الأصلي بنجاح', 'success', 2000);
}

function executeSlAdminClosureL4() {
    if (!slCurrentDoc) return;
    invalidateSlStoreCache();
    const invNo = String(slCurrentDoc.invoiceNo);
    if (slCurrentMode === 'sales') {
        const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        const idx = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);
        if (idx === -1) return;
        allSales[idx].deliveryStatus = 'Admin_Closed';
        allSales[idx].status = 'Admin_Closed';
        allSales[idx].isDelivered = true;
        allSales[idx].adminClosedAt = new Date().toISOString();
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
        localStorage.setItem('erp_inv_' + invNo, JSON.stringify(allSales[idx]));
        invalidateSlStoreCache();
        if (typeof window.logSystemAction === 'function') {
            window.logSystemAction('اعتماد إداري نهائي L4 (مبيعات)', 'المبيعات / المخازن', invNo, `إغلاق إداري نهائي لإذن صرف الفاتورة #${invNo}`);
        }
        loadSlDocument(invNo);
        showSlToast(`👑 تم الاعتماد الإداري النهائي وإغلاق إذن المنصرف للفاتورة #${invNo} (L4)`, 'success');
    } else {
        const allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
        const idx = allPurchases.findIndex(p => !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === invNo);
        if (idx === -1) return;
        allPurchases[idx].receiptStatus = 'Admin_Closed';
        allPurchases[idx].status = 'Admin_Closed';
        allPurchases[idx].adminClosedAt = new Date().toISOString();
        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
        invalidateSlStoreCache();
        if (typeof window.logSystemAction === 'function') {
            window.logSystemAction('اعتماد إداري نهائي L4 (مشتريات)', 'المشتريات / المخازن', invNo, `إغلاق إداري نهائي لإذن استلام فاتورة المشتريات #${invNo}`);
        }
        loadSlDocument(invNo);
        showSlToast(`👑 تم الاعتماد الإداري النهائي وإغلاق إذن استلام المشتريات #${invNo} (L4)`, 'success');
    }
}

function openSlInvoiceSplitBoard() {
    if (!slCurrentDoc) {
        const list = (slCurrentMode === 'sales') ? getProcessedSalesInvoices() : getProcessedPurchaseInvoices();
        const firstPending = list.find(i => i._slStatus === 'pending' || i._slStatus === 'partial') || list[0];
        if (firstPending) {
            loadSlDocument(firstPending.invoiceNo);
        } else {
            showSlToast('⚠️ اختر فاتورة من السجل العلوي أولاً لتقسيمها إلى دفعتين (أ / ب)', 'warning');
            return;
        }
    }
    fillAllSlQuantities(0.5);
    showSlToast(`✂️ تم تقسيم الفاتورة #${slCurrentDoc.invoiceNo} إلى دفعتين (أ: 50% جاهزة للصرف الآن ⇄ ب: 50% متبقية) — يمكنك أيضاً تقسيم أي سطر لمخزنين!`, 'success', 3400);
}

// ── CLOUD SYNC & INITIALIZATION ──────────────────────────────────────────────
async function refreshAllSlData() {
    invalidateSlStoreCache();
    try {
        const [salesRes, purRes] = await Promise.allSettled([
            fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now()),
            fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now())
        ]);
        if (salesRes.status === 'fulfilled' && salesRes.value.ok) {
            const sData = await salesRes.value.json();
            if (sData && sData.success && Array.isArray(sData.sales)) {
                let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
                let sChanged = false;
                sData.sales.forEach(remoteInv => {
                    const rNo = remoteInv.invoiceNo || remoteInv.id;
                    if (!rNo) return;
                    if (!allSales.some(s => String(s.invoiceNo || s.id) === String(rNo))) {
                        allSales.unshift(remoteInv);
                        sChanged = true;
                    }
                });
                if (sChanged) localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
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
    invalidateSlStoreCache();
    populateSlWarehouseSelects();
    renderSlRegistry();
    showSlToast('🔄 تم تحديث البيانات ومزامنة السجل بنجاح', 'success', 1800);
}

document.addEventListener('DOMContentLoaded', () => {
    const savedSize = localStorage.getItem('sl_custom_font_size');
    const savedColor = localStorage.getItem('sl_custom_font_color');
    if (savedSize || savedColor) {
        applySlTypography(savedSize || 15, savedColor || '#f8fafc');
    }

    const params = new URLSearchParams(window.location.search);
    const urlMode = params.get('mode');
    if (urlMode === 'sales' || urlMode === 'purchases') {
        slCurrentMode = urlMode;
    }

    switchLogisticsMode(slCurrentMode, true);
    setSlDeductionLevel(slDeductionLevel, true);
    setTimeout(() => startSlMobileBridgeListener(false), 1800);
    setTimeout(async () => {
        try {
            const res = await fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now());
            if (!res.ok) return;
            const d = await res.json();
            if (d && d.success && Array.isArray(d.purchases)) {
                let allP = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
                let chg = false;
                d.purchases.forEach(rInv => {
                    const rNo = rInv.invoiceNo || rInv.id; if (!rNo) return;
                    const idx = allP.findIndex(p => p && String(p.invoiceNo || p.id) === String(rNo));
                    if (idx === -1) { allP.unshift(rInv); chg = true; }
                    else if (rInv.receiptStatus && allP[idx].receiptStatus !== rInv.receiptStatus) { allP[idx].receiptStatus = rInv.receiptStatus; allP[idx].status = rInv.status || rInv.receiptStatus; chg = true; }
                });
                if (chg) { localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allP)); invalidateSlStoreCache(); renderSlRegistry(); }
            }
        } catch (_) {}
    }, 350);

    const qrParam = params.get('qr_deliver') || params.get('inv');
    if (qrParam) {
        setTimeout(() => processSlContinuousQr(qrParam), 150);
    }
});


// ═══ PURCHASE CONSTITUTION 2027 EXTENSIONS FOR SMART LOGISTICS ═══
window._slFinStripCur = localStorage.getItem('pur_fin_strip_cur') || 'SDG';
window.toggleSlFinStripCurrency = function() {
    window._slFinStripCur = (window._slFinStripCur === 'SDG') ? 'USD' : 'SDG';
    try { localStorage.setItem('pur_fin_strip_cur', window._slFinStripCur); } catch (_e) {}
    const btn = document.getElementById('sl-fin-strip-currency-btn');
    if (btn) {
        btn.setAttribute('data-cur-mode', window._slFinStripCur);
        btn.textContent = window._slFinStripCur === 'USD' ? 'دولار ⇄' : 'جنيه ⇄';
    }
    if (typeof slCurrentDoc !== 'undefined' && slCurrentDoc) window.updateSlPartyFinancialStrip(slCurrentDoc);
};

window.updateSlPartyFinancialStrip = function(doc) {
    const btn = document.getElementById('sl-fin-strip-currency-btn');
    if (btn) {
        btn.setAttribute('data-cur-mode', window._slFinStripCur || 'SDG');
        btn.textContent = (window._slFinStripCur === 'USD') ? 'دولار ⇄' : 'جنيه ⇄';
    }
    const partyName = doc ? resolveSlPartyName(doc, slCurrentMode) : '';
    const isSales = (slCurrentMode === 'sales');
    const storeKey = isSales ? 'erp_customers_v1' : 'erp_suppliers_v1';
    const invKey = isSales ? 'erp_sales_invoices_v1' : 'erp_purchase_invoices_v1';
    const parties = getSlCachedStore(storeKey, []) || [];
    const party = parties.find(p => p && (String(p.name || '').trim() === partyName || String(p.id) === String(doc?.customerId || doc?.supplierId || ''))) || {};
    const allInvs = getSlCachedStore(invKey, []) || [];
    let debtSdg = 0;
    if (partyName && partyName !== '---') {
        allInvs.forEach(inv => {
            if (!inv || inv.deleted) return;
            const pName = resolveSlPartyName(inv, slCurrentMode);
            if (pName === partyName) {
                const rem = parseFloat(inv.remainingAmount ?? inv.creditAmount ?? 0) || 0;
                debtSdg += rem;
            }
        });
    }
    const ledgerSdg = parseFloat(party.balance ?? party.currentBalance ?? debtSdg) || 0;
    const checksSdg = parseFloat(party.checksBalance ?? party.pendingChecks ?? 0) || 0;
    const limitSdg = parseFloat(party.creditLimit ?? party.limit ?? 50000000) || 50000000;
    const remSdg = Math.max(0, limitSdg - debtSdg - checksSdg);

    const exRate = parseFloat(window.SYSTEM_USD_RATE) || parseFloat(localStorage.getItem('erp_rate_usd')) || 5999;
    const isUsd = (window._slFinStripCur === 'USD');
    const conv = (v) => isUsd ? (exRate > 0 ? (v / exRate) : v) : v;
    const fmt = (v) => isUsd ? conv(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' $' : Math.round(conv(v)).toLocaleString('en-US') + ' ج.س';

    const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = fmt(val); };
    setEl('sl-fin-ledger', ledgerSdg);
    setEl('sl-fin-debt', debtSdg);
    setEl('sl-fin-checks', checksSdg);
    setEl('sl-fin-limit', limitSdg);
    setEl('sl-fin-rem', remSdg);
};

window.openSlPartyLedger = function() {
    const partyName = (document.getElementById('sl-lbl-party-name')?.textContent || '').trim();
    if (!partyName || partyName === '---') {
        showSlToast('⚠️ اختر مستنداً من السجل أولاً لفتح كشف حساب الأستاذ', 'warning');
        return;
    }
    const type = (slCurrentMode === 'sales') ? 'customer' : 'supplier';
    window.location.href = `account-statement.php?type=${type}&search=${encodeURIComponent(partyName)}`;
};

window.openSlAIAuditModal = function() {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items.length) {
        showSlToast('⚠️ اختر مستنداً أولاً لتشغيل تدقيق الذكاء الاصطناعي للأسعار والمخزون', 'warning');
        return;
    }
    document.getElementById('purchase-ai-audit-modal')?.remove();
    const isSales = (slCurrentMode === 'sales');
    const isLight = document.documentElement.classList.contains('light-theme');
    const ink = isLight ? '#120500' : '#f8fafc';
    const exRate = parseFloat(window.SYSTEM_USD_RATE) || 5999;
    const whId = document.getElementById('sl-master-warehouse')?.value || getSlWarehouses()[0]?.id || '';
    const rowsHtml = slCurrentDoc.items.map((it) => {
        const m = resolveSlItemMeta(it);
        const curP = parseFloat(m.price) || 0;
        const refCost = parseFloat(it.costPrice || it.lastPrice || (isSales ? curP * 0.82 : curP * 0.96)) || curP;
        const diffPct = refCost > 0 ? (((curP - refCost) / refCost) * 100) : 0;
        const availStk = getSlItemStockInWarehouse(m, it.warehouseId || whId);
        const targetP = isSales ? Math.round(refCost * 1.18) : Math.round(curP * 1.20);
        const badgeClr = isSales ? (diffPct < 5 ? '#b91c1c' : '#047857') : (diffPct > 5 ? '#b91c1c' : (diffPct < -1 ? '#047857' : '#0369a1'));
        const stkClr = (isSales && availStk < m.qty) ? '#b91c1c' : '#047857';
        return `<tr style="border-bottom:1.8px solid ${isLight ? 'rgba(59,20,3,0.35)' : 'rgba(148,163,184,0.25)'};">
            <td style="padding:9px;font-weight:900;">${m.name} (${m.unit})</td>
            <td style="padding:9px;text-align:center;font-weight:900;">${Math.round(refCost).toLocaleString('en-US')} ج.س</td>
            <td style="padding:9px;text-align:center;font-weight:900;">${Math.round(curP).toLocaleString('en-US')} ج.س (${(curP/exRate).toFixed(2)} $)</td>
            <td style="padding:9px;text-align:center;font-weight:900;color:${badgeClr};">${diffPct >= 0 ? '▲ +' : '▼ '}${diffPct.toFixed(1)}%</td>
            <td style="padding:9px;text-align:center;font-weight:900;color:${stkClr};">${availStk} ${m.unit}</td>
            <td style="padding:9px;text-align:center;font-weight:900;color:#047857;">${targetP.toLocaleString('en-US')} ج.س</td>
        </tr>`;
    }).join('');
    const modal = document.createElement('div');
    modal.id = 'purchase-ai-audit-modal';
    modal.className = 'modal-overlay';
    modal.style.cssText = 'position:fixed;inset:0;background:rgba(2,6,23,0.82);z-index:999999;display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;';
    modal.innerHTML = `<div class="modal-card" style="width:100%;max-width:900px;background:${isLight ? '#ffffff' : '#0f172a'};color:${ink};border:3px solid ${isLight ? '#3b1403' : '#38bdf8'};border-radius:16px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,0.65);">
        <div style="padding:13px 20px;background:${isLight ? '#f3e5cc' : '#162847'};border-bottom:2.5px solid ${isLight ? '#3b1403' : '#38bdf8'};display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1.05rem;font-weight:900;color:${ink};">🤖 التدقيق الذكي AI (${isSales ? 'هامش الربح وكفاية المخزون قبل الصرف' : 'رادار أسعار الشراء وهوامش البيع'}) — #${slCurrentDoc.invoiceNo}</h3>
            <button type="button" onclick="document.getElementById('purchase-ai-audit-modal').remove()" class="sl-btn" style="padding:4px 12px;">✖ إغلاق</button>
        </div>
        <div style="padding:14px 18px;max-height:65vh;overflow-y:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                <thead><tr style="background:${isLight ? '#e7d4b5' : '#1e293b'};color:${ink};font-weight:900;border-bottom:2px solid ${isLight ? '#3b1403' : '#475569'};">
                    <th style="padding:9px;text-align:right;">الصنف / الوحدة</th>
                    <th style="padding:9px;text-align:center;">${isSales ? 'التكلفة المرجعية' : 'السعر السابق'}</th>
                    <th style="padding:9px;text-align:center;">سعر المستند</th>
                    <th style="padding:9px;text-align:center;">${isSales ? 'هامش الربح' : 'تغير السعر'}</th>
                    <th style="padding:9px;text-align:center;">رصيد المخزن الحالي</th>
                    <th style="padding:9px;text-align:center;">${isSales ? 'السعر العادل المقترح' : 'سعر البيع المقترح'}</th>
                </tr></thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        </div>
    </div>`;
    document.body.appendChild(modal);
};

window.saveSlCustomConfigPermanently = function() {
    const cfg = {
        fontSize: parseInt(document.getElementById('sl-font-size-range')?.value || localStorage.getItem('sl_custom_font_size') || '16', 10),
        fontColor: document.getElementById('sl-font-color-picker')?.value || localStorage.getItem('sl_custom_font_color') || '#f8fafc',
        cols: {
            code: document.getElementById('sl-col-chk-code')?.checked !== false,
            orig: document.getElementById('sl-col-chk-orig')?.checked !== false,
            prev: document.getElementById('sl-col-chk-prev')?.checked !== false,
            spec: document.getElementById('sl-col-chk-spec')?.checked !== false,
            exec1: document.getElementById('sl-col-chk-exec1')?.checked !== false,
            rem2: document.getElementById('sl-col-chk-rem2')?.checked !== false,
            base: document.getElementById('sl-col-chk-base')?.checked !== false,
            base2: document.getElementById('sl-col-chk-base2')?.checked !== false,
            wh: document.getElementById('sl-col-chk-wh')?.checked !== false,
            price: document.getElementById('sl-col-chk-price')?.checked !== false
        }
    };
    try {
        localStorage.setItem('sl_custom_grid_config_v1', JSON.stringify(cfg));
        localStorage.setItem('sl_custom_font_size', String(cfg.fontSize));
        localStorage.setItem('sl_custom_font_color', cfg.fontColor);
        fetch('api/save_grid_config.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ grid_id: 'sl_custom_grid_config_v1', config: cfg })
        }).catch(() => {});
    } catch (_e) {}
    applySlTypography(cfg.fontSize, cfg.fontColor);
    applySlColVisibility();
    showSlToast('💾 تم حفظ تخصيص الأعمدة والخطوط نهائياً في قاعدة البيانات', 'success');
};

document.addEventListener('DOMContentLoaded', () => {
    const applySavedSlCfg = (cfg) => {
        if (!cfg || !cfg.cols) return;
        ['code', 'orig', 'prev', 'spec', 'exec1', 'rem2', 'base', 'base2', 'wh', 'price'].forEach(k => {
            const cEl = document.getElementById('sl-col-chk-' + k);
            if (cEl && cfg.cols[k] !== undefined) cEl.checked = cfg.cols[k] !== false;
        });
        if (cfg.fontSize || cfg.fontColor) applySlTypography(cfg.fontSize || 15, cfg.fontColor || '#f8fafc');
        applySlColVisibility();
    };
    try {
        const raw = localStorage.getItem('sl_custom_grid_config_v1');
        if (raw) applySavedSlCfg(JSON.parse(raw));
        fetch('api/get_grid_config.php?grid_id=sl_custom_grid_config_v1').then(r => r.json()).then(d => {
            if (d && d.config && d.config.cols) {
                localStorage.setItem('sl_custom_grid_config_v1', JSON.stringify(d.config));
                applySavedSlCfg(d.config);
            }
        }).catch(() => {});
    } catch (_e) {}
    window.updateSlPartyFinancialStrip(null);
});

window.printSlSplitPartFromModal = function(partNo = 1) {
    if (!slCurrentDoc || !Array.isArray(slCurrentDoc.items) || !slCurrentDoc.items.length) {
        return showSlToast('⚠️ اختر مستنداً أولاً للطباعة', 'warning');
    }
    const isSales = (slCurrentMode === 'sales');
    const invNo = slCurrentDoc.invoiceNo;
    const snap = (window._slSplitModalLocked && window._slSavedSplitSnapshot && String(window._slSavedSplitSnapshot.invNo) === String(invNo)) ? window._slSavedSplitSnapshot : null;
    const partyName = snap ? snap.partyName : resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const rootBase = snap ? snap.rootBase : String(slCurrentDoc.rootSplitNo || slCurrentDoc._slNoteNo || (isSales ? `GDN-${invNo}` : window.getNextSlZGrnNumber(0))).split('/')[0];
    const baseSeq = snap ? snap.baseSeq : (Array.isArray(slCurrentDoc._slGrns) ? slCurrentDoc._slGrns.length : 0);
    const splitSeq = partNo === 1 ? (baseSeq + 1) : (baseSeq + 2);
    const fullNoteNo = `${rootBase}/${splitSeq}`;
    const rows = [];
    if (snap && Array.isArray(snap.rows)) {
        snap.rows.forEach(sr => {
            const q = partNo === 1 ? sr.execQ : sr.remAfter;
            if (q <= 0.0001) return;
            rows.push({ idx: rows.length + 1, name: sr.name, unit: sr.unit, baseUnit: sr.baseUnit, factor: sr.factor || 1, origQ: sr.origQ, q, whName: sr.whName });
        });
    } else {
        const priInputs = document.querySelectorAll('.sl-qty-primary-input');
        const whSelects = document.querySelectorAll('.sl-row-wh-select');
        const whs = getSlWarehouses();
        slCurrentDoc.items.forEach((it, idx) => {
            const m = resolveSlItemMeta(it);
            const origQ = parseFloat(m.qty) || 0;
            const prevQ = parseFloat(it.deliveredQty || it.receivedQty || 0) || 0;
            const maxRem = slCurrentDoc._slStatus === 'done' ? origQ : Math.max(0, +(origQ - prevQ).toFixed(4));
            const execQ = priInputs[idx] ? Math.min(maxRem, Math.max(0, parseFloat(priInputs[idx].value) || 0)) : maxRem;
            const remAfter = Math.max(0, +(maxRem - execQ).toFixed(4));
            const q = partNo === 1 ? execQ : remAfter;
            if (q <= 0.0001) return;
            const whId = whSelects[idx]?.value || it.warehouseId || whs[0]?.id || '';
            const whName = (whs.find(w => String(w.id) === String(whId)) || whs[0] || { name: 'المخزن الرئيسي' }).name;
            rows.push({ idx: rows.length + 1, name: m.name, unit: m.unit, baseUnit: m.baseUnit, factor: m.factor || 1, origQ, q, whName });
        });
    }
    if (!rows.length) return showSlToast(partNo === 1 ? '⚠️ لا توجد كمية في القسم الأول (1)' : '⚠️ لا توجد كمية متبقية في القسم الثاني (2)', 'warning');
    const partTitle = partNo === 1 ? (isSales ? 'إذن تسليم القسم الأول (1) — الكمية المسلّمة /1' : 'إذن استلام القسم الأول (1) — الكمية المستلمة /1') : 'بيان القسم الثاني (2) — الكمية المتبقية /2';
    const w = window.open('', '_blank', 'width=880,height=720');
    if (!w) return;
    w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>${partTitle} - #${invNo}</title><style>body{font-family:'Cairo','Tajawal',sans-serif;padding:22px;color:#000}table{width:100%;border-collapse:collapse;margin-top:12px}th{background:#0f172a;color:#fff;padding:9px;border:1.5px solid #000}td{padding:8px;border:1.5px solid #000;text-align:center;font-weight:800}</style></head><body><div style="display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #000;padding-bottom:12px;"><div><h2 style="margin:0;">${partTitle}</h2><p style="margin:6px 0 0;font-weight:900;">رقم الفاتورة: #${invNo} &nbsp;|&nbsp; رقم التقسيم: ${fullNoteNo} &nbsp;|&nbsp; القسم: (${partNo}) /${splitSeq}</p><p style="margin:4px 0 0;font-weight:800;">${isSales ? 'العميل:' : 'المورد:'} ${partyName} &nbsp;|&nbsp; التاريخ: ${new Date().toISOString().slice(0,10)}</p></div></div><table><thead><tr><th>#</th><th>اسم الصنف</th><th>المستودع</th><th>أصل الفاتورة</th><th>${partNo === 1 ? (isSales ? 'الكمية المسلّمة (1)' : 'الكمية المستلمة (1)') : 'الكمية المتبقية (2)'}</th><th>بالوحدة الأساسية</th></tr></thead><tbody>${rows.map(r => `<tr><td>${r.idx}</td><td style="text-align:right;font-weight:900;">${r.name}</td><td>${r.whName}</td><td>${r.origQ} ${r.unit}</td><td style="font-size:1.05rem;font-weight:900;">${r.q} ${r.unit}</td><td>${+(r.q * r.factor).toFixed(2)} ${r.baseUnit}</td></tr>`).join('')}</tbody></table><div style="display:flex;justify-content:space-between;margin-top:42px;font-weight:900;"><div>توقيع أمين المخزن: ______________</div><div>توقيع المستلم / السائق: ______________</div><div>الاعتماد والختم: ______________</div></div><script>window.onload=()=>setTimeout(()=>window.print(),300);<\/script></body></html>`);
    w.document.close();
};

window.printSlRegistryReport = function(reportType = 'current') {
    const isSales = (slCurrentMode === 'sales');
    const rows = Array.isArray(window._slLastFilteredRows) && window._slLastFilteredRows.length ? window._slLastFilteredRows : [];
    const allSrc = isSales ? getProcessedSalesInvoices() : getProcessedPurchaseInvoices();
    const tabNames = { pending: isSales ? 'قيد التسليم' : 'قيد الاستلام', partial: 'الجزئي والتقسيمات', done: 'المكتمل', notes: 'الأذونات الرسمية', all: 'السجل الشامل' };
    const w = window.open('', '_blank', 'width=980,height=760');
    if (!w) return showSlToast('يرجى السماح بالنوافذ المنبثقة للطباعة', 'warning');
    const today = new Date().toISOString().slice(0, 10);
    let title = '', thead = '', tbody = '', pageCss = 'body{font-family:Cairo,Tajawal,sans-serif;padding:20px;color:#000}table{width:100%;border-collapse:collapse;margin-top:12px;font-size:12.5px}th{background:#0f172a;color:#fff;padding:8px;border:1.5px solid #000}td{padding:7px;border:1.5px solid #000;text-align:center;font-weight:800}';

    if (reportType === 'items_summary') {
        title = `📦 تقرير تجميع الأصناف ومطابقة الرصيد المخزني — (${tabNames[slRegistryFilter] || 'الكل'})`;
        const agg = {};
        const whId = document.getElementById('sl-reg-wh-filter')?.value;
        const targetWh = (!whId || whId === 'all' || whId === 'PENDING_RECEIPT') ? (getSlWarehouses()[0]?.id || '') : whId;
        (rows.length ? rows : allSrc).forEach(r => (r.items || []).forEach(it => {
            const m = resolveSlItemMeta(it);
            const k = `${m.name}|${m.unit}`;
            const orig = parseFloat(it.origInvQty ?? m.qty) || 0;
            const rem = it.remainingQty !== undefined ? parseFloat(it.remainingQty) : orig;
            const rcv = parseFloat(it.receivedQty ?? it.deliveredQty ?? Math.max(0, orig - rem)) || 0;
            if (!agg[k]) agg[k] = { m, orig: 0, rcv: 0, rem: 0, docs: new Set(), stk: getSlItemStockInWarehouse(m, targetWh) };
            agg[k].orig += orig; agg[k].rcv += rcv; agg[k].rem += rem; agg[k].docs.add(r.invoiceNo);
        }));
        thead = `<tr><th>#</th><th>اسم الصنف</th><th>الوحدة</th><th>عدد الفواتير</th><th>إجمالي أصل الفواتير</th><th>${isSales ? 'المسلّم' : 'المستلم'}</th><th>المتبقي</th><th>رصيد المخزن الحالي</th></tr>`;
        tbody = Object.values(agg).map((x, i) => `<tr><td>${i + 1}</td><td style="text-align:right;font-weight:900;">${x.m.name}</td><td>${x.m.unit}</td><td>${x.docs.size}</td><td>${+x.orig.toFixed(2)}</td><td>${+x.rcv.toFixed(2)}</td><td>${+x.rem.toFixed(2)}</td><td>${x.stk}</td></tr>`).join('');
    } else if (reportType === 'zero_variance_audit') {
        title = `🛡️ تقرير تدقيق انعدام الفروقات والمطابقة الرقابية (${isSales ? 'تسليم وتقسيم المبيعات' : 'استلام المشتريات'})`;
        thead = `<tr><th>#</th><th>الفاتورة</th><th>${isSales ? 'العميل' : 'المورد'}</th><th>الصنف</th><th>أصل الفاتورة</th><th>${isSales ? 'المسلّم (1)' : 'المستلم (1)'}</th><th>المتبقي (2)</th><th>الفارق الرقابي</th><th>حالة المطابقة</th></tr>`;
        let seq = 0;
        tbody = allSrc.map(inv => (inv.items || []).map(it => {
            const m = resolveSlItemMeta(it);
            const orig = +(parseFloat(m.qty) || 0).toFixed(3);
            const rcv = +(parseFloat(it.receivedQty ?? it.deliveredQty ?? 0) || 0).toFixed(3);
            const rem = +(parseFloat(it.remainingQty ?? Math.max(0, orig - rcv)) || 0).toFixed(3);
            const diff = +(orig - (rcv + rem)).toFixed(3);
            const ok = Math.abs(diff) <= 0.001;
            return `<tr><td>${++seq}</td><td>#${inv.invoiceNo}</td><td style="text-align:right;">${resolveSlPartyName(inv, slCurrentMode)}</td><td style="text-align:right;">${m.name}</td><td>${orig} ${m.unit}</td><td>${rcv} ${m.unit}</td><td>${rem} ${m.unit}</td><td>${diff}</td><td style="color:${ok ? '#065f46' : '#991b1b'};font-weight:900;">${ok ? '✅ صفر فروقات' : '⚠️ فرق يحتاج مراجعة'}</td></tr>`;
        }).join('')).join('');
    } else {
        const isThermal = (reportType === 'thermal');
        if (isThermal) pageCss = 'body{font-family:Cairo,sans-serif;width:76mm;margin:0 auto;padding:6px;color:#000;font-size:11px}table{width:100%;border-collapse:collapse;margin-top:6px}th,td{border:1px solid #000;padding:4px;text-align:center;font-weight:800}';
        const srcRows = reportType === 'part1' ? allSrc.filter(x => x._slStatus === 'done' || x._slStatus === 'partial') : (reportType === 'part2' ? allSrc.filter(x => x._slStatus === 'partial' || x._slStatus === 'pending') : rows);
        title = reportType === 'part1' ? `🖨️ كشف القسم الأول (1): الكميات ${isSales ? 'المسلّمة' : 'المستلمة'} (/1)` : reportType === 'part2' ? `🖨️ كشف القسم الثاني (2): الكميات المتبقية (/2)` : reportType === 'inspection' ? `📋 استمارة الفحص والعدّ الفعلي للمخزن` : `${isSales ? '📤 كشف تسليم وتقسيم المبيعات' : '📥 كشف استلام المشتريات'} — (${tabNames[slRegistryFilter] || 'الحالي'})`;
        thead = isThermal ? `<tr><th>فاتورة</th><th>الصنف</th><th>الكمية</th></tr>` : reportType === 'inspection' ? `<tr><th>التاريخ</th><th>الفاتورة</th><th>${isSales ? 'العميل' : 'المورد'}</th><th>الصنف</th><th>أصل الفاتورة</th><th>العدّ الفعلي</th><th>التالف/النقص</th><th>توقيع الفاحص</th></tr>` : `<tr><th>التاريخ</th><th>الفاتورة</th><th>رقم التقسيم/الإذن</th><th>التقسيم</th><th>${isSales ? 'العميل' : 'المورد'}</th><th>الصنف</th><th>أصل الفاتورة</th><th>${reportType === 'part2' || slRegistryFilter === 'partial' ? 'المتبقي (2)' : (isSales ? 'المسلّم (1)' : 'المستلم (1)')}</th><th>المستودع</th></tr>`;
        tbody = srcRows.map(r => (r.items || []).map(it => {
            const m = resolveSlItemMeta(it);
            const orig = +(parseFloat(it.origInvQty ?? m.qty) || 0).toFixed(3);
            const rem = it.remainingQty !== undefined ? +(parseFloat(it.remainingQty) || 0).toFixed(3) : orig;
            const rcv = +(parseFloat(it.receivedQty ?? it.deliveredQty ?? Math.max(0, orig - rem)) || 0).toFixed(3);
            const qShow = (reportType === 'part2' || r._viewMode === 'rem_part') ? rem : (reportType === 'part1' || r._viewMode === 'done_part' ? rcv : orig);
            if ((reportType === 'part1' || reportType === 'part2') && qShow <= 0.0001) return '';
            const recId = (r.allNoteIds && r.allNoteIds[0]) || r._slNoteNo || '—';
            const spLbl = r.splitTag || (r._slStatus === 'partial' ? 'تقسيم جزئي' : '—');
            if (isThermal) return `<tr><td>#${r.invoiceNo}</td><td>${m.name}</td><td>${qShow} ${m.unit}</td></tr>`;
            if (reportType === 'inspection') return `<tr><td>${(r.date || '').slice(0,10)}</td><td>#${r.invoiceNo}</td><td style="text-align:right;">${r.partyName || resolveSlPartyName(r, slCurrentMode)}</td><td style="text-align:right;">${m.name}</td><td>${orig} ${m.unit}</td><td></td><td></td><td></td></tr>`;
            return `<tr><td>${(r.date || '').slice(0,10)}</td><td>#${r.invoiceNo}</td><td>${recId}</td><td>${spLbl}</td><td style="text-align:right;">${r.partyName || resolveSlPartyName(r, slCurrentMode)}</td><td style="text-align:right;">${m.name}</td><td>${orig} ${m.unit}</td><td>${qShow} ${m.unit}</td><td>${r.warehouse || 'المخزن الرئيسي'}</td></tr>`;
        }).join('')).join('');
    }
    w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>${title}</title><style>${pageCss}</style></head><body><div style="border-bottom:2.5px solid #000;padding-bottom:8px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;"><h3 style="margin:0;">${title}</h3><span style="font-weight:900;">التاريخ: ${today}</span></div><table><thead>${thead}</thead><tbody>${tbody || '<tr><td colspan="9">لا توجد بيانات مطابقة</td></tr>'}</tbody></table><script>window.onload=()=>setTimeout(()=>window.print(),300);<\/script></body></html>`);
    w.document.close();
};

window.exportSlRegistryCsv = function() {
    const isSales = (slCurrentMode === 'sales');
    const rows = Array.isArray(window._slLastFilteredRows) ? window._slLastFilteredRows : [];
    if (!rows.length) return showSlToast('⚠️ لا توجد صفوف معروضة حالياً للتصدير', 'warning');
    const lines = [['التاريخ', 'الفاتورة', 'رقم الإذن/التقسيم', 'التقسيم', isSales ? 'العميل' : 'المورد', 'اسم الصنف', 'الوحدة', 'أصل الفاتورة', isSales ? 'المصروف' : 'المستلم', 'المتبقي', 'المستودع'].join(',')];
    rows.forEach(r => (r.items || []).forEach(it => {
        const m = resolveSlItemMeta(it);
        const orig = +(parseFloat(it.origInvQty ?? m.qty) || 0).toFixed(3);
        const rem = it.remainingQty !== undefined ? +(parseFloat(it.remainingQty) || 0).toFixed(3) : orig;
        const rcv = +(parseFloat(it.receivedQty ?? it.deliveredQty ?? Math.max(0, orig - rem)) || 0).toFixed(3);
        const q = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
        lines.push([q(r.date), q(r.invoiceNo), q((r.allNoteIds && r.allNoteIds[0]) || r.noteId || ''), q(r.splitTag || ''), q(r.partyName), q(m.name), q(m.unit), orig, rcv, rem, q(r.warehouse)].join(','));
    }));
    const blob = new Blob(['\uFEFF' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `${isSales ? 'sales_delivery_split' : 'purchase_receipt_split'}_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showSlToast('📊 تم تصدير ملف Excel بنجاح', 'success');
};
