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
function showSlToast(message, type = 'success', duration = 3500) {
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

// ── PURCHASE PENDING & RECEIVED CALCULATION ENGINE (100% COMPATIBLE WITH purchase-receipt.php) ──
function getProcessedPurchaseInvoices() {
    const allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
    const grnList = allPurchases.filter(p => p && !p.deleted && p.isGRN);
    const returnList = allPurchases.filter(p => p && !p.deleted && p.isReturn);

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
    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]').filter(i => i && !i.deleted && i.status !== 'draft' && i.status !== 'proforma');
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

    // Update dynamic labels across the entire cockpit
    const setTxt = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
    setTxt('sl-main-title-icon', isSales ? '📤' : '📥');
    setTxt('sl-main-title-text', isSales
        ? 'بوابة تسليم المبيعات وتقسيمها الذكية (صادر — GDN)'
        : 'بوابة استلام المشتريات وتقسيمها الذكية (وارد — GRN)');
    setTxt('sl-kpi-lbl-pending', isSales ? '⏳ فواتير مبيعات جاهزة للصرف (قيد التسليم)' : '⏳ فواتير مشتريات بانتظار الاستلام المخزني');
    setTxt('sl-kpi-lbl-partial', isSales ? '🌗 مسلّمة جزئياً على دفعات' : '🌗 مستلمة جزئياً على دفعات');
    setTxt('sl-kpi-lbl-done', isSales ? '✅ أذونات مبيعات منصرفة ومكتملة' : '✅ فواتير مشتريات مستلمة بالكامل');
    setTxt('sl-kpi-lbl-notes', isSales ? '📊 إجمالي أذونات الصرف الرسمية (GDN)' : '📊 إجمالي مذكرات الاستلام الرسمية (GRN)');

    setTxt('sl-registry-title', isSales
        ? 'السجل الذكي الفوري لفواتير وأذونات المبيعات (صادر 📤 — خصم مخزني)'
        : 'السجل الذكي الفوري لفواتير ومذكرات استلام المشتريات (وارد 📥 — إضافة مخزنية)');
    setTxt('sl-reg-th-party', isSales ? '👤 العميل والمستلم' : '🏢 المورد والمسلّم');
    setTxt('sl-lbl-party-title', isSales ? '👤 العميل صاحب الفاتورة' : '🏢 المورد صاحب الفاتورة');
    setTxt('sl-lbl-wh-select', isSales ? '🏢 مخزن الصرف الافتراضي (للكل):' : '🏢 مخزن الاستلام الافتراضي (للكل):');
    setTxt('sl-lbl-receiver', isSales ? '🤝 اسم المستلم الفعلي للبضاعة:' : '🤝 أمين المخزن المستلم / المندوب:');
    setTxt('sl-th-dual-action', isSales
        ? '📤 كمية الصرف والتسليم الآن (بالوحدة الأساسية ⇄ أو بالوحدة الفرعية)'
        : '📥 كمية الاستلام والتوريد الآن (بالوحدة الأساسية ⇄ أو بالوحدة الفرعية)');
    setTxt('sl-summary-mode-badge', isSales ? '📤 وضع تسليم المبيعات (صادر GDN)' : '📥 وضع استلام المشتريات (وارد GRN)');

    const btnExec = document.getElementById('sl-btn-execute');
    if (btnExec) {
        btnExec.innerHTML = isSales
            ? '🚚 تأكيد واعتماد التسليم وخصم المخزون الآن (GDN)'
            : '📥 تأكيد واعتماد الاستلام وإضافة المخزون الآن (GRN)';
    }

    const btnUndo = document.getElementById('sl-btn-undo');
    if (btnUndo) {
        btnUndo.innerHTML = isSales
            ? '↩️ إلغاء الصرف وإعادة البضاعة للمخزن'
            : '↩️ إلغاء الاستلام وخصم البضاعة من المخزن';
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

    const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    setVal('sl-kpi-val-pending', kpiPending);
    setVal('sl-kpi-val-partial', kpiPartial);
    setVal('sl-kpi-val-done', kpiDone);
    setVal('sl-kpi-val-notes', kpiNotes);

    const tbody = document.getElementById('sl-registry-tbody');
    if (!tbody) return;

    const q = (document.getElementById('sl-reg-search')?.value || '').trim().toLowerCase();
    const whFilter = document.getElementById('sl-reg-wh-filter')?.value || 'all';
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

    // Apply search & warehouse filter
    const filtered = rowsData.filter(r => {
        if (whFilter !== 'all') {
            const whObj = whs.find(w => String(w.id) === String(whFilter));
            const matchWh = whObj && (String(r.warehouse).includes(whObj.name) || String(r.warehouse) === String(whObj.id));
            if (!matchWh) return false;
        }
        if (!q) return true;
        const itemsTxt = (r.items || []).map(it => resolveSlItemMeta(it).name).join(' ').toLowerCase();
        return (
            String(r.invoiceNo).toLowerCase().includes(q) ||
            String(r.noteId).toLowerCase().includes(q) ||
            String(r.partyName).toLowerCase().includes(q) ||
            itemsTxt.includes(q)
        );
    });

    const countBadge = document.getElementById('sl-reg-count-badge');
    if (countBadge) countBadge.textContent = `${filtered.length} مستند`;

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
                <span style="color:var(--sl-primary-light);">(${m.qty} ${m.unit})</span>
                ${rem < m.qty && rem > 0 ? `<span style="color:#fbbf24;">[متبقي: ${rem}]</span>` : ''}
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
function clearSlDocumentCard() {
    const setTxt = (id, t) => { const el = document.getElementById(id); if (el) el.textContent = t; };
    setTxt('sl-lbl-inv-no', '---');
    setTxt('sl-lbl-party-name', '---');
    setTxt('sl-lbl-date', '---');
    setTxt('sl-lbl-hash', '---');
    const tbody = document.getElementById('sl-items-tbody');
    if (tbody) {
        tbody.innerHTML = `<tr><td colspan="9" style="padding:40px; text-align:center; color:#94a3b8; font-weight:800;">👆 اضغط على أي فاتورة من السجل العلوي أو امسح باركود الـ QR لعرض الأصناف وتقسيمها بالوحدتين والمخازن</td></tr>`;
    }
    updateSlFloatingSummary();
}

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

    document.getElementById('sl-lbl-inv-no').textContent = `#${doc.invoiceNo}` + (doc._slNoteNo ? ` (${doc._slNoteNo})` : '');
    document.getElementById('sl-lbl-party-name').textContent = partyName;
    document.getElementById('sl-lbl-date').textContent = (doc.date || doc.savedAt || '---').slice(0, 10);
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

    if (recInput) recInput.value = doc.receiverName || partyName || '';
    if (drvInput) drvInput.value = doc.driverName || '';
    if (trkInput) trkInput.value = doc.truckPlate || '';
    if (gpInput)  gpInput.value  = doc.gatePassNo || (`${isSales ? 'GP' : 'GRN'}-` + String(doc.invoiceNo).replace(/\D/g, '').slice(-4) + '-' + Date.now().toString().slice(-3));
    if (wNotes)   wNotes.value   = doc.warehouseNotes || doc.notes || '';
    if (pNotes)   pNotes.value   = doc.personalNotes || '';

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
        tbody.innerHTML = `<tr><td colspan="9" style="padding:30px; text-align:center; color:#fca5a5;">⚠️ لا توجد أصناف مسجلة في هذا المستند</td></tr>`;
        updateSlFloatingSummary();
        return;
    }

    tbody.innerHTML = items.map((row, idx) => {
        const meta = resolveSlItemMeta(row);
        const origQty = meta.qty;
        const prevQty = parseFloat(row.deliveredQty || row.receivedQty || 0) || 0;
        const remQty = slCurrentDoc._slStatus === 'done' ? 0 : Math.max(0, +(origQty - prevQty).toFixed(4));
        const defaultExecQty = slCurrentDoc._slStatus === 'done' ? origQty : remQty;
        const defaultBaseQty = +(defaultExecQty * meta.factor).toFixed(2);

        const rowWhCandidate = row.warehouseId || slCurrentDoc.deliveredWarehouse || slCurrentDoc.warehouseId || slCurrentDoc.warehouse || masterWhId;
        const matchedWh = whs.find(w => String(w.id) === String(rowWhCandidate) || String(w.name) === String(rowWhCandidate)) || whs[0];
        const availStock = getSlItemStockInWarehouse(meta, matchedWh.id);

        const whOptionsHtml = whs.map(w => `<option value="${w.id}" ${String(w.id) === String(matchedWh.id) ? 'selected' : ''}>🏢 ${w.name}</option>`).join('');

        return `
            <tr data-row-idx="${idx}">
                <td style="text-align:center; font-weight:900; color:#94a3b8;">${idx + 1}</td>

                <td class="sl-col-wh" style="text-align:right;">
                    <select class="sl-input sl-row-wh-select" data-item-idx="${idx}" onchange="updateSlRowStockBadge(${idx}, this.value)" style="width:100%; padding:6px 10px !important; font-size:0.82rem !important; color:#fcd34d !important;">
                        ${whOptionsHtml}
                    </select>
                    <div id="sl-row-stock-badge-${idx}" style="font-size:0.74rem; color:${isSales && availStock < remQty ? '#f87171' : '#94a3b8'}; font-weight:800; margin-top:4px;">
                        الرصيد الحالي: ${availStock} ${meta.unit}
                    </div>
                </td>

                <td style="text-align:right;">
                    <div style="font-weight:900; color:#ffffff; font-size:0.98rem;">${meta.name}</div>
                    <div style="font-size:0.73rem; color:#64748b; font-family:'Inter',monospace;">كود: ${meta.code}</div>
                </td>

                <td class="sl-col-spec" style="text-align:center;">
                    <span class="sl-badge" style="background:rgba(56,189,248,0.12); color:#7dd3fc; border:1px solid rgba(56,189,248,0.3);">
                        1 ${meta.unit} = ${meta.factor} ${meta.baseUnit}
                    </span>
                </td>

                <td style="text-align:center; font-weight:900; font-size:1.05rem; color:#e2e8f0;">
                    <div>${origQty} <span style="font-size:0.78rem; color:#94a3b8;">${meta.unit}</span></div>
                    ${meta.hasMultiUnits ? `<div style="font-size:0.73rem; color:#818cf8;">(${meta.baseQty.toLocaleString('en-US')} ${meta.baseUnit})</div>` : ''}
                </td>

                <td class="sl-col-prev" style="text-align:center;">
                    <div style="font-size:0.82rem; font-weight:800; color:#a855f7;">منفذ سابقاً: ${prevQty}</div>
                    <div style="font-size:0.86rem; font-weight:900; color:#fbbf24; margin-top:2px;">المتبقي: ${remQty} ${meta.unit}</div>
                </td>

                <td class="sl-col-price" style="text-align:center; display:none; font-weight:800; color:#fcd34d;">
                    <div>${meta.price.toLocaleString('en-US', { maximumFractionDigits: 2 })}</div>
                    <div style="font-size:0.75rem; color:#6ee7b7;">إجمالي: ${meta.total.toLocaleString('en-US', { maximumFractionDigits: 2 })}</div>
                </td>

                <td style="text-align:center;">
                    <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
                        <div style="display:flex; align-items:stretch; justify-content:center; gap:10px; flex-wrap:nowrap;">
                            <!-- PRIMARY UNIT BOX -->
                            <div id="sl-ubox-pri-${idx}" class="sl-unit-box is-active-unit-mode" onmouseenter="activateSlUnitBox(${idx}, 'primary')">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:4px; margin-bottom:6px;">
                                    <span style="font-size:0.75rem; font-weight:900; color:var(--sl-primary-light);">📦 بالـ ${meta.unit} (المتبقي: ${remQty})</span>
                                    <button type="button" onclick="setSlRowFullQty(${idx})" class="sl-btn" style="padding:2px 8px; font-size:0.7rem; color:#38bdf8; border-color:rgba(56,189,248,0.4);">⚡ الكل</button>
                                </div>
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; background:rgba(2,6,23,0.9); padding:4px 6px; border-radius:9px; border:1px solid var(--sl-border-glow);">
                                    <button type="button" onclick="stepSlRowQty(${idx}, -1, 'primary')" class="sl-btn" style="width:28px; height:30px; padding:0; background:rgba(239,68,68,0.22); color:#fff; border-color:rgba(239,68,68,0.45);">−</button>
                                    <input type="text" inputmode="decimal" class="sl-input sl-qty-primary-input"
                                        data-item-idx="${idx}"
                                        data-max-qty="${slCurrentDoc._slStatus === 'done' ? origQty : remQty}"
                                        data-orig-qty="${origQty}"
                                        data-prev-qty="${prevQty}"
                                        data-factor="${meta.factor}"
                                        data-pri-unit="${meta.unit}"
                                        data-base-unit="${meta.baseUnit}"
                                        value="${defaultExecQty}"
                                        oninput="onSlPrimaryQtyInput(this)"
                                        style="width:88px !important; text-align:center; padding:3px 4px !important; font-size:1.08rem !important;">
                                    <span style="font-size:0.74rem; font-weight:900; color:#cbd5e1; max-width:44px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${meta.unit}</span>
                                    <button type="button" onclick="stepSlRowQty(${idx}, 1, 'primary')" class="sl-btn" style="width:28px; height:30px; padding:0; background:rgba(16,185,129,0.24); color:#fff; border-color:rgba(16,185,129,0.5);">+</button>
                                </div>
                            </div>

                            ${meta.hasMultiUnits ? `
                            <span style="align-self:center; font-weight:900; color:#94a3b8; font-size:1.1rem;">⇄</span>
                            <!-- SUB / BASE UNIT BOX -->
                            <div id="sl-ubox-base-${idx}" class="sl-unit-box base-box" onmouseenter="activateSlUnitBox(${idx}, 'base')">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:4px; margin-bottom:6px;">
                                    <span style="font-size:0.75rem; font-weight:900; color:#a5b4fc;">🔢 بالـ ${meta.baseUnit} (${+(remQty * meta.factor).toFixed(2)})</span>
                                    <button type="button" onclick="setSlRowFullQty(${idx})" class="sl-btn" style="padding:2px 8px; font-size:0.7rem; color:#a5b4fc; border-color:rgba(129,140,248,0.4);">⚡ الكل</button>
                                </div>
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; background:rgba(2,6,23,0.9); padding:4px 6px; border-radius:9px; border:1px solid rgba(99,102,241,0.45);">
                                    <button type="button" onclick="stepSlRowQty(${idx}, -1, 'base')" class="sl-btn" style="width:28px; height:30px; padding:0; background:rgba(239,68,68,0.22); color:#fff; border-color:rgba(239,68,68,0.45);">−</button>
                                    <input type="text" inputmode="decimal" class="sl-input sl-qty-base-input"
                                        data-item-idx="${idx}"
                                        data-max-base="${+((slCurrentDoc._slStatus === 'done' ? origQty : remQty) * meta.factor).toFixed(2)}"
                                        data-factor="${meta.factor}"
                                        value="${defaultBaseQty}"
                                        oninput="onSlBaseQtyInput(this)"
                                        style="width:88px !important; text-align:center; padding:3px 4px !important; font-size:1.08rem !important;">
                                    <span style="font-size:0.74rem; font-weight:900; color:#cbd5e1; max-width:44px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${meta.baseUnit}</span>
                                    <button type="button" onclick="stepSlRowQty(${idx}, 1, 'base')" class="sl-btn" style="width:28px; height:30px; padding:0; background:rgba(16,185,129,0.24); color:#fff; border-color:rgba(16,185,129,0.5);">+</button>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        <div id="sl-row-live-eq-${idx}" style="font-size:0.75rem; font-weight:800; color:#cbd5e1;">
                            المنفذ الآن: <strong style="color:var(--sl-primary-light);">${defaultExecQty} ${meta.unit}</strong>
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
}

function activateSlUnitBox(idx, mode) {
    document.getElementById(`sl-ubox-pri-${idx}`)?.classList.toggle('is-active-unit-mode', mode === 'primary');
    document.getElementById(`sl-ubox-base-${idx}`)?.classList.toggle('is-active-unit-mode', mode === 'base');
}

function onSlPrimaryQtyInput(inp, syncBase = true) {
    if (!inp) return;
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
        liveEl.innerHTML = `المنفذ الآن: <strong style="color:var(--sl-primary-light);">${+val.toFixed(3)} ${priUnit}</strong> ${factor > 1 ? `⇄ <strong style="color:#a5b4fc;">${baseVal.toLocaleString('en-US')} ${baseUnit}</strong>` : ''}`;
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
}

// ── EXECUTE TRANSACTION (STRICT ISOLATION: SALES GDN VS PURCHASES GRN) ────────
function executeSlTransaction() {
    if (!slCurrentDoc) {
        showSlToast('⚠️ يرجى اختيار مستند من السجل العلوي أو مسح باركود QR أولاً', 'warning');
        return false;
    }
    if (slCurrentMode === 'sales') {
        return executeSlSalesDelivery();
    } else {
        return executeSlPurchaseReceipt();
    }
}

function executeSlSalesDelivery() {
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

    let isPartialDelivery = false;
    const deliveredItemsPayload = [];
    const items = inv.items || inv.rows || [];

    items.forEach((item, idx) => {
        const meta = resolveSlItemMeta(item);
        const origQty = meta.qty;
        const prevDel = wasAlreadyDone ? 0 : (parseFloat(item.deliveredQty || 0) || 0);
        const maxRem = Math.max(0, +(origQty - prevDel).toFixed(4));
        let deliverNow = maxRem;

        if (priInputs[idx]) {
            const parsed = parseFloat(priInputs[idx].value);
            if (!isNaN(parsed)) deliverNow = Math.min(maxRem, Math.max(0, parsed));
        }

        const rowWhId = whSelects[idx]?.value || masterWhId;
        const rowWhObj = whs.find(w => String(w.id) === String(rowWhId)) || whs[0];

        item.deliveredQty = +(prevDel + deliverNow).toFixed(4);
        item.warehouseId = rowWhObj.id;
        item.warehouseName = rowWhObj.name;

        if (item.deliveredQty < origQty - 0.0001) {
            isPartialDelivery = true;
        }

        if (deliverNow > 0) {
            deliveredItemsPayload.push({
                ...item,
                name: meta.name,
                itemName: meta.name,
                unit: meta.unit,
                qty: deliverNow,
                quantity: deliverNow,
                orderedQty: origQty,
                warehouseId: rowWhObj.id,
                warehouseName: rowWhObj.name
            });
        }
    });

    if (!deliveredItemsPayload.length) {
        showSlToast('⚠️ يرجى تحديد كمية أكبر من صفر للصرف والتسليم', 'warning');
        return false;
    }

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
                invEntry.qty = (parseFloat(invEntry.qty || 0) - deductQty);
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
                String(x.id) === String(dItem.itemId || dItem.id) || String(x.name || '').trim() === itemNameClean
            );
            if (masterIdx !== -1) {
                const mItem = erpItems[masterIdx];
                if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
                if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
                mItem.stock[whId] = (parseFloat(mItem.stock[whId]) || 0) - deductQty;
                mItem.actualStock[whId] = (parseFloat(mItem.actualStock[whId]) || 0) - deductQty;
                if (mItem.quantity !== undefined) {
                    mItem.quantity = (parseFloat(mItem.quantity) || 0) - deductQty;
                }
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

    allSales[invIdx] = inv;
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));

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
        isPartialDelivery,
        status: 'delivered',
        items: deliveredItemsPayload,
        deleted: 0
    };

    let deliveryNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
    deliveryNotes.unshift(gdnRecord);
    localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(deliveryNotes));

    try {
        fetch('api/mirror_sync.php?action=save_delivery_note', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...gdnRecord, updatedInvoiceItems: inv.items })
        }).catch(() => {});
    } catch (e) {}

    loadSlDocument(invNo);
    showSlToast(`🚚 تم اعتماد إذن صرف المبيعات (${gdnNo}) وخصم المخزون بنجاح!`, 'success');
    return true;
}

function executeSlPurchaseReceipt() {
    let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
    const invNo = String(slCurrentDoc.invoiceNo);
    const origIdx = allPurchases.findIndex(p => !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === invNo);
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
        showSlToast('⚠️ يرجى تحديد كمية أكبر من صفر لاستلام البضاعة', 'warning');
        return false;
    }

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
            mItem.stock[whId] = (parseFloat(mItem.stock[whId]) || 0) + parseFloat(gItem.quantity || 0);
            mItem.actualStock[whId] = (parseFloat(mItem.actualStock[whId]) || 0) + parseFloat(gItem.quantity || 0);
            if (mItem.quantity !== undefined) {
                mItem.quantity = (parseFloat(mItem.quantity) || 0) + parseFloat(gItem.quantity || 0);
            }
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
        createdBy: curUser.id,
        createdByName: curUser.fullName || curUser.name,
        savedAt: now.toISOString()
    };

    allPurchases[origIdx].receiptStatus = isPartialReceipt ? 'partial_received' : 'received';
    allPurchases[origIdx].status = isPartialReceipt ? 'partial_received' : 'received';
    allPurchases[origIdx].grnNo = grnId;
    allPurchases.push(grnRecord);

    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
    if (typeof window.pushToCloud === 'function') window.pushToCloud(true);

    try {
        fetch('api/mirror_sync.php?action=save_purchase_receipt', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(grnRecord)
        }).catch(() => {});
    } catch (e) {}

    loadSlDocument(invNo);
    showSlToast(`📥 تم إصدار مذكرة استلام المشتريات (${grnId}) وإضافة الكميات للمخزون بنجاح!`, 'success');
    return true;
}

function quickExecuteFullSlDoc(invNo) {
    if (!loadSlDocument(invNo)) return;
    fillAllSlQuantities(1);
    executeSlTransaction();
}

// ── UNDO / REVERSE TRANSACTION ───────────────────────────────────────────────
function undoSlTransaction() {
    if (!slCurrentDoc) return;
    const invNo = String(slCurrentDoc.invoiceNo);
    const isSales = (slCurrentMode === 'sales');

    if (!confirm(`هل أنت متأكد من إلغاء ${isSales ? 'صرف المبيعات' : 'استلام المشتريات'} للمستند #${invNo} وعكس الحركة المخزنية؟`)) {
        return;
    }

    if (isSales) {
        const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        const idx = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);
        if (idx === -1) return;
        const inv = allSales[idx];
        const whId = inv.deliveredWarehouse || inv.warehouseId || inv.warehouse || getSlWarehouses()[0].id;
        let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');

        (inv.items || []).forEach(it => {
            const retQty = parseFloat(it.deliveredQty || it.qty || 0) || 0;
            it.deliveredQty = 0;
            const meta = resolveSlItemMeta(it);
            const entry = inventory.find(x =>
                (String(x.id || x.itemId) === String(meta.itemId) || String(x.name || '').trim() === meta.name.trim()) &&
                String(x.warehouseId || x.warehouse) === String(whId)
            );
            if (entry) entry.qty = (parseFloat(entry.qty || 0) + retQty);
        });

        inv.isDelivered = false;
        inv.deliveryStatus = 'pending';
        inv.status = 'pending_delivery';
        allSales[idx] = inv;
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));

        let delNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
        delNotes = delNotes.filter(n => String(n.invoiceNo || n.relatedInvoice) !== invNo);
        localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(delNotes));

        loadSlDocument(invNo);
        showSlToast(`↩️ تم إلغاء صرف الفاتورة #${invNo} وإعادة الكميات للمخزن`, 'warning');
    } else {
        let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
        const grnsToUndo = allPurchases.filter(p => p && !p.deleted && p.isGRN && String(p.originalInvoiceId) === invNo);
        let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');

        grnsToUndo.forEach(grn => {
            (grn.items || []).forEach(gItem => {
                const revQty = parseFloat(gItem.customBaseQty || gItem.quantity || 0) || 0;
                const entry = inventory.find(x =>
                    String(x.itemId || x.id) === String(gItem.itemId) &&
                    (String(x.warehouse) === String(gItem.warehouseName) || String(x.warehouseId) === String(gItem.warehouseId))
                );
                if (entry) entry.qty = Math.max(0, (parseFloat(entry.qty || 0) - revQty));
            });
            grn.deleted = true;
        });

        const origIdx = allPurchases.findIndex(p => !p.isGRN && !p.isReturn && String(p.invoiceNo || p.id) === invNo);
        if (origIdx !== -1) {
            allPurchases[origIdx].receiptStatus = 'pending_receipt';
            allPurchases[origIdx].status = 'pending_receipt';
        }

        localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));

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
                    <tr><th>#</th><th>الصنف</th><th>المستودع</th><th>الكمية بالوحدة</th><th>المعادل بالفرعية</th></tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <div style="margin-top:16px; padding:10px; border:1px dashed #000; font-weight:800;">
                ملاحظات المخزن والبوابة: ${document.getElementById('sl-warehouse-notes')?.value || 'تم فحص ومطابقة الأصناف'}
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

function shareSlWhatsApp() {
    if (!slCurrentDoc) {
        showSlToast('⚠️ اختر مستنداً أولاً للمشاركة عبر واتساب', 'warning');
        return;
    }
    const isSales = (slCurrentMode === 'sales');
    const invNo = slCurrentDoc.invoiceNo;
    const partyName = resolveSlPartyName(slCurrentDoc, slCurrentMode);
    const itemsTxt = (slCurrentDoc.items || []).map(it => {
        const m = resolveSlItemMeta(it);
        return `• ${m.name}: ${m.qty} ${m.unit}`;
    }).join('\n');
    const msg = `${isSales ? '📤 *إذن صرف وتسليم مبيعات (GDN)*' : '📥 *مذكرة استلام مشتريات مخزنية (GRN)*'}\n📄 رقم المستند: #${invNo}\n👤 ${isSales ? 'العميل' : 'المورد'}: ${partyName}\n📦 *الأصناف:*\n${itemsTxt}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
}

// ── CLOUD SYNC & INITIALIZATION ──────────────────────────────────────────────
async function refreshAllSlData() {
    try {
        const [salesRes, purRes] = await Promise.allSettled([
            fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now()),
            fetch('api/mirror_sync.php?action=get_recent_purchases&_t=' + Date.now())
        ]);
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
    startSlMobileBridgeListener();

    const qrParam = params.get('qr_deliver') || params.get('inv');
    if (qrParam) {
        setTimeout(() => processSlContinuousQr(qrParam), 300);
    }
});
