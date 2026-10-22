// ==========================================
// PROJECT: SAJANA INTERNATIONAL PROTOCOL
// MODULE: WORLD-CLASS 4-GATE SALES DELIVERY & GDN REGISTRY
// ==========================================

let currentLoadedInvoice = null;
let currentLevel = 4;
let isVerified = false;
let currentAttachment = null;
let verificationMethod = 'override';
let html5QrCode = null;
window.gateRegistryFilter = 'pending'; // 'pending' | 'stage1' | 'delivered' | 'all' | 'notes'

function showToast(message, type = 'success', duration = 3500) {
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

// ── HELPERS FOR ITEM, UNIT, TON (طن عددي / طن وزني) & CUSTOMER RESOLUTION ──
function resolveGateItemMeta(it) {
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
        (normRawName && normStr(x.name) === normRawName) ||
        (normRawName && normRawName.length >= 4 && (normStr(x.name).includes(normRawName) || normRawName.includes(normStr(x.name))))
    );

    const name = rawName || (master ? master.name : (itemId || 'صنف عام'));
    const unit = String(it.selectedUnit || it.unit || it.unitName || (master ? (master.unit || master.baseUnit || 'قطعة') : 'قطعة')).trim();

    let unitsList = [];
    if (master) {
        if (typeof window.extractItemUnits === 'function') {
            unitsList = window.extractItemUnits(master) || [];
        } else if (Array.isArray(master.unitsArray) && master.unitsArray.length > 0) {
            unitsList = master.unitsArray;
        } else if (Array.isArray(master.units) && master.units.length > 0) {
            unitsList = master.units;
        }
    }

    const isSteel = /سيخ|حديد|لينيه|لينية|ملي|مم|رباط/.test(name) || /طن/.test(unit);
    const isCement = /اسمنت|أسمنت/.test(name);
    let baseUnit = (master && (master.baseUnit || master.subUnitName)) || '';
    if (!baseUnit && unitsList.length > 0) {
        const u1 = unitsList.find(u => parseFloat(u.factor) === 1);
        if (u1) baseUnit = u1.name || u1.unitName || '';
    }
    if (!baseUnit || normStr(baseUnit) === normStr(unit)) {
        if (/سيخ|حديد|لينيه|لينية/.test(name)) baseUnit = 'سيخة';
        else if (isCement) baseUnit = 'كيس';
        else if (/طوب|بلوك/.test(name)) baseUnit = 'طوبة';
        else if (/زنك/.test(name)) baseUnit = 'لوح';
        else if (/مواسير|ماسورة|ماسوره/.test(name)) baseUnit = 'ماسورة';
        else baseUnit = (master && master.unit) || unit || 'وحدة';
    }

    const code = it.code || it.itemCode || (master ? (master.code || master.barcode || itemId) : (itemId || '---'));

    const normUnit = normStr(unit);
    const isBulkOrTonUnit = /طن|شيله|شيلة|ربطه|ربطة|بندل|طرد|كرتون|جوال|شوال|باكيت|لفه|لفة/.test(normUnit) && normUnit !== normStr(baseUnit);

    let factor = parseFloat(it.customFactor ?? it.factor ?? it.unitFactor ?? 0);

    if ((!factor || isNaN(factor) || factor <= 0 || (factor === 1 && isBulkOrTonUnit)) && unitsList.length > 0) {
        const exactU = unitsList.find(u => normStr(u.name || u.unitName) === normUnit);
        if (exactU && parseFloat(exactU.factor) > 1) {
            factor = parseFloat(exactU.factor);
        } else {
            const partialU = unitsList.find(u => {
                const un = normStr(u.name || u.unitName);
                return (un.includes(normUnit) || normUnit.includes(un)) && parseFloat(u.factor) > 1;
            });
            if (partialU) factor = parseFloat(partialU.factor);
        }
    }

    if ((!factor || factor <= 1) && isBulkOrTonUnit && master) {
        const mConv = parseFloat(master.conversionFactor || master.bundleFactor || master.subUnitFactor || 0);
        if (mConv > 1) factor = mConv;
    }

    let countTonFactor = 0;
    let weightTonFactor = 0;
    if (unitsList.length > 0) {
        const cTon = unitsList.find(u => /عددي|عادي/.test(normStr(u.name || u.unitName)));
        const wTon = unitsList.find(u => /وزني/.test(normStr(u.name || u.unitName)));
        if (cTon && parseFloat(cTon.factor) > 1) countTonFactor = parseFloat(cTon.factor);
        if (wTon && parseFloat(wTon.factor) > 1) weightTonFactor = parseFloat(wTon.factor);
    }

    if (isSteel || isCement || isBulkOrTonUnit) {
        const n = normStr(name);
        const isWeightTon = /وزني/.test(normUnit);
        if (/3\s*لين|8\s*مم|8\s*ملي|3\s*لنيه/.test(n)) {
            if (!countTonFactor) countTonFactor = 135;
            if (!weightTonFactor) weightTonFactor = 166;
        } else if (/4\s*لين|10\s*مم|10\s*ملي|12\s*مم|12\s*ملي|4\s*لنيه/.test(n)) {
            if (!countTonFactor) countTonFactor = 92;
            if (!weightTonFactor) weightTonFactor = 110;
        } else if (/5\s*لين|16\s*مم|16\s*ملي|5\s*لنيه/.test(n)) {
            if (!countTonFactor) countTonFactor = 52;
            if (!weightTonFactor) weightTonFactor = 59;
        } else if (/6\s*لين|18\s*مم|20\s*مم/.test(n)) {
            if (!countTonFactor) countTonFactor = 37;
            if (!weightTonFactor) weightTonFactor = 40;
        } else if (/2\.5\s*لين|6\s*مم|6\s*ملي/.test(n)) {
            if (!countTonFactor) countTonFactor = 220;
            if (!weightTonFactor) weightTonFactor = 250;
        } else if (isCement && /طن/.test(normUnit)) {
            if (!countTonFactor) countTonFactor = 20;
            if (!weightTonFactor) weightTonFactor = 20;
        }

        if (!factor || factor <= 1) {
            if (isWeightTon && weightTonFactor > 1) factor = weightTonFactor;
            else if (countTonFactor > 1) factor = countTonFactor;
        }
    }

    if (!factor || isNaN(factor) || factor <= 0) factor = 1;

    const qty = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
    const savedBase = parseFloat(it.customBaseQty ?? it.baseQty ?? 0);
    const baseQty = (savedBase > qty && factor === 1) ? savedBase : +(qty * factor).toFixed(4);
    if (savedBase > qty && factor === 1 && qty > 0) {
        factor = +(savedBase / qty).toFixed(4);
    }

    const price = parseFloat(it.price ?? it.unitPrice ?? it.rate ?? (master ? (master.sellingPrice || master.price || 0) : 0)) || 0;
    const total = parseFloat(it.total ?? it.grossTotal ?? it.subtotal ?? (qty * price)) || (qty * price);

    const hasMultiUnits = factor > 1 || unit !== baseUnit;
    let tonRefHint = '';
    if (isSteel && countTonFactor > 1) {
        tonRefHint = `طن عددي = ${countTonFactor} ${baseUnit}` + (weightTonFactor > 1 ? ` | طن وزني = ${weightTonFactor} ${baseUnit}` : '');
    } else if (factor > 1) {
        tonRefHint = `1 ${unit} = ${factor} ${baseUnit}`;
    }

    return {
        name, unit, baseUnit, code, master, factor, qty, baseQty, price, total,
        hasMultiUnits, tonRefHint, countTonFactor, weightTonFactor
    };
}

function resolveGateCustomerName(inv) {
    if (inv.customerName && inv.customerName !== 'walk-in') return inv.customerName;
    const cId = inv.customer || inv.customerId || '';
    if (!cId || cId === 'walk-in') return 'عميل نقدي';
    const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
    const found = custs.find(c => c && String(c.id) === String(cId));
    return found ? found.name : cId;
}

function getWarehousesList() {
    const whs = JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]');
    return whs.length > 0 ? whs : [{ id: 1, name: 'المخزن الرئيسي' }];
}

function getAvailableStockForGateItem(item, whId) {
    const { name, master } = resolveGateItemMeta(item);
    if (master && master.actualStock && master.actualStock[whId] !== undefined) {
        return parseFloat(master.actualStock[whId]) || 0;
    }
    if (master && master.stock && master.stock[whId] !== undefined) {
        return parseFloat(master.stock[whId]) || 0;
    }
    const invList = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
    const entry = invList.find(x =>
        (String(x.itemId || x.id) === String(item.itemId || item.id) || String(x.name || '').trim() === name.trim()) &&
        (String(x.warehouseId || x.warehouse || 1) === String(whId))
    );
    if (entry) return parseFloat(entry.qty ?? entry.quantity ?? 0) || 0;
    return master && master.quantity !== undefined ? (parseFloat(master.quantity) || 0) : 0;
}

function populateGateWarehouseSelect(selectedVal) {
    const sel = document.getElementById('gate-warehouse-select');
    if (!sel) return;
    const whs = getWarehousesList();
    sel.innerHTML = whs.map(w =>
        `<option value="${w.id}" ${String(w.id) === String(selectedVal) || w.name === selectedVal ? 'selected' : ''}>${w.name}</option>`
    ).join('');
}

// ── SECURITY LEVELS LOGIC (4 GATES) ──
function simulateUserRole(levelStr) {
    const level = parseInt(levelStr) || 4;
    currentLevel = level;
    renderAuthTabs();
    switchLevel(level);
}

function renderAuthTabs() {
    const container = document.getElementById('auth-tabs-container');
    if (!container) return;
    const gateNames = {
        1: '🔒 البوابة 1 (QR)',
        2: '🔎 البوابة 2 (الفرز)',
        3: '🚛 البوابة 3 (الخروج)',
        4: '👑 البوابة 4 (الإدارة L4)'
    };
    let html = '';
    for (let lvl = 1; lvl <= currentLevel; lvl++) {
        html += `<button class="level-tab" data-level="${lvl}" onclick="switchLevel(${lvl})" style="padding: 7px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); background: rgba(0,0,0,0.3); color: #94a3b8; font-size: 0.82rem; font-weight: 800; cursor: pointer;">${gateNames[lvl]}</button>`;
    }
    container.innerHTML = html;
}

function switchLevel(level) {
    currentLevel = level;
    const tabs = document.querySelectorAll('.level-tab');
    tabs.forEach(tab => {
        tab.style.background = 'rgba(0,0,0,0.3)';
        tab.style.color = '#94a3b8';
        tab.style.borderColor = 'rgba(255,255,255,0.1)';
        if (parseInt(tab.dataset.level) === level) {
            tab.style.background = 'rgba(16,185,129,0.2)';
            tab.style.color = '#6ee7b7';
            tab.style.borderColor = 'rgba(52,211,153,0.5)';
        }
    });

    if (level >= 4) {
        setVerified('override');
    }
    if (currentLoadedInvoice) {
        renderInvoiceDetails();
    }
}

// ── LIVE REGISTRY TABLE & KPIS ──
function setGateRegistryFilter(mode) {
    window.gateRegistryFilter = mode;
    ['pending', 'stage1', 'delivered', 'all'].forEach(t => {
        const btn = document.getElementById('reg-tab-' + t);
        if (btn) {
            if (t === mode) {
                btn.style.background = '#10b981';
                btn.style.color = '#ffffff';
                btn.style.borderColor = '#10b981';
            } else {
                btn.style.background = 'rgba(255,255,255,0.08)';
                btn.style.color = '#e2e8f0';
                btn.style.borderColor = 'rgba(255,255,255,0.15)';
            }
        }
    });
    renderGateRegistryTable();
}

function readPersistentGateJson(globalKey, legacyKey) {
    try {
        const rawGlobal = localStorage.getItem(globalKey);
        if (rawGlobal) {
            const parsed = JSON.parse(rawGlobal);
            if (parsed && typeof parsed === 'object' && Object.keys(parsed).length > 0) return parsed;
        }
    } catch (e) {}
    try {
        const rawLegacy = localStorage.getItem(legacyKey);
        if (rawLegacy) {
            const parsed = JSON.parse(rawLegacy);
            if (parsed && typeof parsed === 'object' && Object.keys(parsed).length > 0) return parsed;
        }
    } catch (e) {}
    return {};
}

function writePersistentGateJson(globalKey, legacyKey, obj) {
    const str = JSON.stringify(obj || {});
    try { localStorage.setItem(globalKey, str); } catch (e) {}
    try { localStorage.setItem(legacyKey, str); } catch (e) {}
}

function toggleGateRegCols(fromInit = true) {
    const colMap = {
        'reg-col-inv': '.reg-col-inv-cell',
        'reg-col-date': '.reg-col-date-cell',
        'reg-col-cust': '.reg-col-cust-cell',
        'reg-col-items': '.reg-col-items-cell',
        'reg-col-wh': '.reg-col-wh-cell',
        'reg-col-act': '.reg-col-act-cell'
    };
    const saved = readPersistentGateJson('magaies_gate_reg_cols_v2', 'erp_gate_reg_cols_v1');
    const hasSaved = Object.keys(saved).length > 0;
    if (fromInit && hasSaved) {
        Object.keys(colMap).forEach(id => {
            const chk = document.getElementById(id);
            if (chk && saved[id] !== undefined) chk.checked = !!saved[id];
        });
    }
    const toSave = {};
    Object.keys(colMap).forEach(id => {
        const chk = document.getElementById(id);
        const show = (fromInit && hasSaved && saved[id] !== undefined)
            ? !!saved[id]
            : (chk ? chk.checked : (saved[id] !== undefined ? !!saved[id] : true));
        if (chk) chk.checked = show;
        toSave[id] = show;
        document.querySelectorAll(colMap[id]).forEach(el => {
            if (show) {
                el.style.removeProperty('display');
            } else {
                el.style.setProperty('display', 'none', 'important');
            }
        });
    });
    if (!fromInit || !hasSaved) {
        writePersistentGateJson('magaies_gate_reg_cols_v2', 'erp_gate_reg_cols_v1', toSave);
    }
}

function lockAndSaveGateRegCols() {
    toggleGateRegCols(false);
    const drawer = document.getElementById('gate-reg-cols-drawer');
    if (drawer) drawer.style.display = 'none';
    if (typeof showToast === 'function') {
        showToast('✅ تم تثبيت وحفظ ضبط الكولومات نهائياً ولن يتغير إلا عند تعديله مجدداً', 'success');
    }
}

function renderGateRegistryTable() {
    const tbody = document.getElementById('gate-registry-tbody');
    if (!tbody) return;

    const whs = getWarehousesList();
    const whFilterSel = document.getElementById('gate-reg-wh-filter');
    if (whFilterSel && whFilterSel.options.length <= 1) {
        whFilterSel.innerHTML = `<option value="all">🏢 كل المخازن</option>` + whs.map(w => `<option value="${w.id}">🏢 ${w.name}</option>`).join('');
    }

    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]').filter(inv => !inv.deleted && !inv.wasSplit && inv.saleType !== 'preliminary' && inv.status !== 'preliminary');
    const delNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]').filter(n => !n.deleted);

    let pendingCnt = 0;
    let stage1Cnt = 0;
    let deliveredCnt = 0;

    allSales.forEach(inv => {
        const st = inv.deliveryStatus || inv.status || 'pending';
        if (st === 'Delivered_Pending_Admin') {
            stage1Cnt++;
        } else if (inv.isDelivered === true || st === 'delivered' || st === 'Admin_Closed') {
            deliveredCnt++;
        } else {
            pendingCnt++;
        }
    });

    const elPend = document.getElementById('gate-kpi-pending');
    const elStg1 = document.getElementById('gate-kpi-stage1');
    const elDelv = document.getElementById('gate-kpi-delivered');
    const elNotes = document.getElementById('gate-kpi-notes');
    if (elPend) elPend.textContent = pendingCnt;
    if (elStg1) elStg1.textContent = stage1Cnt;
    if (elDelv) elDelv.textContent = deliveredCnt;
    if (elNotes) elNotes.textContent = delNotes.length || deliveredCnt;

    let filtered = [...allSales];
    if (window.gateRegistryFilter === 'pending') {
        filtered = allSales.filter(inv => {
            const st = inv.deliveryStatus || inv.status || 'pending';
            return !inv.isDelivered && st !== 'delivered' && st !== 'Delivered_Pending_Admin' && st !== 'Admin_Closed';
        });
    } else if (window.gateRegistryFilter === 'stage1') {
        filtered = allSales.filter(inv => (inv.deliveryStatus || inv.status) === 'Delivered_Pending_Admin');
    } else if (window.gateRegistryFilter === 'delivered') {
        filtered = allSales.filter(inv => inv.isDelivered === true || (inv.deliveryStatus || inv.status) === 'delivered' || (inv.deliveryStatus || inv.status) === 'Admin_Closed');
    }

    // 1000+ Vouchers Quick Search & Warehouse Filter
    const qVal = (document.getElementById('gate-reg-quick-search')?.value || '').trim().toLowerCase();
    const whFilter = document.getElementById('gate-reg-wh-filter')?.value || 'all';
    if (whFilter && whFilter !== 'all') {
        const whObj = whs.find(w => String(w.id) === String(whFilter));
        const whName = whObj ? whObj.name : '';
        filtered = filtered.filter(inv => {
            const invWh = String(inv.deliveredWarehouse || inv.warehouseId || inv.warehouse || '');
            return invWh === String(whFilter) || (whName && invWh === whName);
        });
    }
    if (qVal) {
        filtered = filtered.filter(inv => {
            const invNo = String(inv.invoiceNo || inv.id || '').toLowerCase();
            const gdnNo = String(inv.deliveryNo || '').toLowerCase();
            const cust = resolveGateCustomerName(inv).toLowerCase();
            const rec = String(inv.receiverName || '').toLowerCase();
            const itemsTxt = (inv.items || []).map(it => resolveGateItemMeta(it).name).join(' ').toLowerCase();
            return invNo.includes(qVal) || gdnNo.includes(qVal) || cust.includes(qVal) || rec.includes(qVal) || itemsTxt.includes(qVal);
        });
    }

    filtered.sort((a, b) => new Date(b.timestamp || b.date || 0) - new Date(a.timestamp || a.date || 0));

    const limitVal = parseInt(document.getElementById('gate-reg-limit')?.value || '100', 10) || 100;
    const visibleSlice = filtered.slice(0, limitVal);
    const matchBadge = document.getElementById('gate-reg-match-badge');
    if (matchBadge) {
        matchBadge.textContent = `🔍 معروض ${visibleSlice.length} من أصل ${filtered.length} إذن`;
    }

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 32px; color: #94a3b8; font-weight: 800;">
                    📭 لا توجد أذونات مطابقة حالياً — جرب مسح نص البحث أو التبديل إلى تبويب "الكل"
                </td>
            </tr>
        `;
        return;
    }

    const rowsHtml = visibleSlice.map(inv => {
        const invNo = String(inv.invoiceNo || inv.id || '');
        const custName = resolveGateCustomerName(inv);
        const dateStr = inv.date || (inv.timestamp ? inv.timestamp.slice(0, 10) : '---');
        const timeStr = inv.time || (inv.timestamp ? new Date(inv.timestamp).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '');
        const st = inv.deliveryStatus || inv.status || 'pending';
        const isClosed = st === 'Admin_Closed';
        const isStage1 = st === 'Delivered_Pending_Admin';
        const isDel = inv.isDelivered === true || st === 'delivered' || isClosed || isStage1;

        const whObj = whs.find(w => String(w.id) === String(inv.deliveredWarehouse || inv.warehouseId || inv.warehouse)) || { name: inv.warehouse || 'المخزن الرئيسي' };
        const items = Array.isArray(inv.items) ? inv.items : [];

        const pillsHtml = items.map((it, itemIdx) => {
            const m = resolveGateItemMeta(it);
            const q = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
            const eqUnits = +(q * m.factor).toFixed(2);
            return `<div class="gdn-item-pill" style="display:flex; justify-content:space-between; align-items:center; width:100%; flex-wrap:wrap; gap:6px;">
                <span style="display:inline-flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <strong style="color:#38bdf8;">#${itemIdx + 1}</strong>
                    <span style="background:rgba(245,158,11,0.14); color:#fcd34d; padding:1px 6px; border-radius:5px; font-size:0.72rem; font-weight:800;">🏢 ${whObj.name}</span>
                    <strong>${m.name}</strong>
                    ${m.tonRefHint ? `<span style="color:#7dd3fc; font-size:0.72rem;">(${m.tonRefHint})</span>` : ''}
                </span>
                <span style="display:inline-flex; align-items:center; gap:5px; flex-wrap:wrap;">
                    <button type="button" onclick="event.stopPropagation(); loadInvoiceByNo('${invNo}', false, ${itemIdx}, 'primary')" class="gdn-unit-badge" style="cursor:pointer; border:1px solid #10b981; padding:3px 9px; font-size:0.8rem;" title="اضغط للاستلام بالـ ${m.unit}">📦 ${q} ${m.unit}</button>
                    ${m.hasMultiUnits ? `<button type="button" onclick="event.stopPropagation(); loadInvoiceByNo('${invNo}', false, ${itemIdx}, 'base')" class="gdn-base-unit-badge" style="cursor:pointer; border:1px solid #818cf8; padding:3px 9px; font-size:0.8rem;" title="اضغط للاستلام بالـ ${m.baseUnit}">🔢 = ${eqUnits.toLocaleString('en-US')} ${m.baseUnit}</button>` : ''}
                </span>
            </div>`;
        }).join('');

        let badgeHtml = `<span class="status-badge">⏳ بانتظار الصرف</span>`;
        if (isClosed) {
            badgeHtml = `<span class="status-badge delivered">👑 مغلق إدارياً (L4)</span>`;
        } else if (isStage1) {
            badgeHtml = `<span class="status-badge stage1">🚚 خرجت (بانتظار L4)</span>`;
        } else if (isDel) {
            badgeHtml = `<span class="status-badge delivered">✅ تم الصرف (${inv.deliveryNo || 'GDN'})</span>`;
        }

        const isSelected = currentLoadedInvoice && String(currentLoadedInvoice.invoiceNo) === invNo;

        return `
            <tr onclick="loadInvoiceByNo('${invNo}')" style="cursor: pointer; background: ${isSelected ? 'rgba(16,185,129,0.16)' : 'transparent'}; transition: 0.2s;">
                <td class="reg-col-inv-cell" style="text-align: right; font-weight: 900; color: #38bdf8; font-family: 'Inter', monospace;">
                    #${invNo}
                    ${inv.isSplit ? `<div style="font-size:0.68rem; color:#f472b6; font-weight:900;">✂️ أصل #${inv.rootInvoice || inv.parentInvoice} • تقسيم #${inv.splitBatchNo || 1}</div>` : ''}
                    ${inv.deliveryNo ? `<div style="font-size:0.74rem; color:#6ee7b7;">${inv.deliveryNo}</div>` : ''}
                </td>
                <td class="reg-col-date-cell" style="text-align: center; font-size: 0.84rem; color: #cbd5e1; font-weight: 800;">
                    <div>📅 ${dateStr}</div>
                    ${timeStr ? `<div style="color:#38bdf8; font-size:0.76rem;">🕒 ${timeStr}</div>` : ''}
                </td>
                <td class="reg-col-cust-cell" style="text-align: right; font-weight: 800; color: #f8fafc;">
                    👤 ${custName}
                    ${inv.receiverName ? `<div style="font-size:0.75rem; color:#34d399;">المستلم: ${inv.receiverName}</div>` : ''}
                </td>
                <td class="reg-col-items-cell" style="text-align: right;">
                    <div style="font-size:0.75rem; color:#94a3b8; font-weight:800; margin-bottom:5px;">عدد البنود: <strong style="color:#38bdf8;">${items.length} أصناف</strong></div>
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        ${pillsHtml || '<span style="color:#64748b;">لا توجد أصناف</span>'}
                    </div>
                </td>
                <td class="reg-col-wh-cell" style="text-align: center;">
                    <div style="font-size: 0.8rem; color: #fcd34d; font-weight: 800; margin-bottom: 4px;">🏢 ${whObj.name}</div>
                    ${badgeHtml}
                </td>
                <td class="reg-col-act-cell" style="text-align: center;">
                    <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                        <button type="button" class="gdn-kinetic-btn" onclick="event.stopPropagation(); loadInvoiceByNo('${invNo}')" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 6px 12px; font-size: 0.8rem;">
                            📦 استدعاء وفحص
                        </button>
                        <button type="button" class="gdn-kinetic-btn" onclick="event.stopPropagation(); loadInvoiceByNo('${invNo}'); printCurrentGatePass();" style="background: rgba(56,189,248,0.18); border: 1px solid rgba(56,189,248,0.4); color: #38bdf8; padding: 6px 10px; font-size: 0.8rem;" title="طباعة إذن المنصرف">
                            🖨️
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    const loadMoreHtml = filtered.length > visibleSlice.length ? `
        <tr>
            <td colspan="6" style="text-align:center; padding:12px;">
                <button type="button" onclick="const sel=document.getElementById('gate-reg-limit'); if(sel){sel.value='99999'; renderGateRegistryTable();}" class="gdn-kinetic-btn" style="background:linear-gradient(135deg,#0284c7,#0369a1); color:#fff; border:1px solid #38bdf8; padding:7px 20px;">
                    ⬇️ عرض جميع الأذونات المتبقية (${filtered.length - visibleSlice.length} إذن إضافي)
                </button>
            </td>
        </tr>
    ` : '';

    tbody.innerHTML = rowsHtml + loadMoreHtml;
    toggleGateRegCols();
}

// ── SYNC WITH MARIADB & INITIALIZE ──
async function syncSalesDeliveryWithMariaDB() {
    if (!navigator.onLine) return;
    try {
        const res = await fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now());
        if (!res.ok) return;
        const data = await res.json();
        if (data && data.success && Array.isArray(data.sales)) {
            let allSales = [];
            try { allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []; } catch(e) {}
            let changed = false;
            data.sales.forEach(s => {
                const invNo = s.invoiceNo || s.id;
                if (!invNo) return;
                const idx = allSales.findIndex(x => (x.invoiceNo || x.id) === invNo);
                if (idx === -1) {
                    allSales.unshift(s);
                    changed = true;
                } else if (s.deliveryStatus === 'delivered' && allSales[idx].deliveryStatus !== 'delivered') {
                    allSales[idx].deliveryStatus = 'delivered';
                    allSales[idx].isDelivered = true;
                    changed = true;
                }
            });
            if (changed) {
                localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
            }
        }
    } catch (e) {}
    renderGateRegistryTable();
}

function initGateDeliveryPage() {
    populateGateWarehouseSelect(1);
    toggleGateRegCols(true);
    toggleGateOptionalCols(true);
    const roleSelect = document.getElementById('demo-role-select');
    simulateUserRole(roleSelect ? roleSelect.value : '4');
    renderGateRegistryTable();

    // Auto-load first pending invoice or URL ?inv=... parameter so screen is immediately rich and complete
    const params = new URLSearchParams(window.location.search);
    const urlInv = params.get('inv') || params.get('invoiceNo') || params.get('id');
    if (urlInv) {
        loadInvoiceByNo(urlInv);
    } else {
        const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]').filter(i => !i.deleted && i.saleType !== 'preliminary');
        const firstPending = allSales.find(i => !i.isDelivered && i.deliveryStatus !== 'delivered' && i.deliveryStatus !== 'Admin_Closed');
        if (firstPending) {
            loadInvoiceByNo(firstPending.invoiceNo || firstPending.id, true);
        } else if (allSales.length > 0) {
            setGateRegistryFilter('all');
            loadInvoiceByNo(allSales[0].invoiceNo || allSales[0].id, true);
        }
    }
    syncSalesDeliveryWithMariaDB();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGateDeliveryPage);
} else {
    initGateDeliveryPage();
}
document.addEventListener('DBReady', initGateDeliveryPage);

// ── CAMERA, CONTINUOUS ZERO-CLICK QR & MOBILE-TO-LAPTOP LIVE BRIDGE ──
let gateQrCooldownMap = {};
let gateContinuousDeliveredCount = 0;
let gateContinuousCancelledCount = 0;
let gateBridgePollTimer = null;
let gateProcessedScanIds = {};
let gateBroadcastChannel = null;
const GATE_BRIDGE_CHANNEL = 'default_gate';

function playGateQrTone(type = 'ok') {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        if (type === 'ok') {
            if (navigator.vibrate) navigator.vibrate(90);
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(980, ctx.currentTime);
            osc.frequency.setValueAtTime(1480, ctx.currentTime + 0.08);
            gain.gain.setValueAtTime(0.26, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.24);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.24);
        } else {
            if (navigator.vibrate) navigator.vibrate([180, 70, 220]);
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(290, ctx.currentTime);
            osc.frequency.setValueAtTime(185, ctx.currentTime + 0.15);
            gain.gain.setValueAtTime(0.32, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.42);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.42);
        }
    } catch (e) {}
}

function showGateLiveQrBanner(type, message) {
    const banner = document.getElementById('gate-live-qr-banner');
    const txt = document.getElementById('gate-live-qr-banner-text');
    const pill = document.getElementById('gate-live-qr-counter-pill');
    if (!banner || !txt) return;

    banner.style.display = 'flex';
    if (type === 'ok') {
        banner.style.background = 'linear-gradient(135deg, rgba(6,78,59,0.96), rgba(5,150,105,0.92))';
        banner.style.border = '2px solid #34d399';
        banner.style.color = '#ffffff';
    } else {
        banner.style.background = 'linear-gradient(135deg, rgba(127,29,29,0.96), rgba(220,38,38,0.92))';
        banner.style.border = '2px solid #fca5a5';
        banner.style.color = '#ffffff';
    }
    txt.innerHTML = message;
    if (pill) {
        pill.textContent = `✅ المستلم بالتتابع: ${gateContinuousDeliveredCount} | 🚫 ملغي/مكرر: ${gateContinuousCancelledCount}`;
    }

    const modalCounter = document.getElementById('bridge-modal-Counter-badge');
    if (modalCounter) {
        modalCounter.textContent = `المستلم الآن: ${gateContinuousDeliveredCount} | الملغي: ${gateContinuousCancelledCount}`;
    }
    const modalFeed = document.getElementById('bridge-modal-live-feed');
    if (modalFeed) {
        if (modalFeed.textContent.includes('بانتظار')) modalFeed.innerHTML = '';
        const row = document.createElement('div');
        row.style.cssText = `padding:6px 10px; margin-bottom:5px; border-radius:8px; font-weight:800; background:${type === 'ok' ? 'rgba(16,185,129,0.18)' : 'rgba(239,68,68,0.22)'}; color:${type === 'ok' ? '#6ee7b7' : '#fecaca'}; border-right:3px solid ${type === 'ok' ? '#10b981' : '#ef4444'};`;
        row.innerHTML = `${message} <span style="float:left; font-size:0.74rem; opacity:0.8;">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}</span>`;
        modalFeed.prepend(row);
    }
}

function processContinuousGateQrScan(rawDecodedText, source = 'camera') {
    if (!rawDecodedText) return null;
    let q = String(rawDecodedText).trim();
    try {
        if (q.includes('qr_deliver=')) {
            const u = new URL(q, window.location.origin);
            q = u.searchParams.get('qr_deliver') || q;
        } else if (q.includes('inv=')) {
            const u = new URL(q, window.location.origin);
            q = u.searchParams.get('inv') || q;
        }
    } catch (e) {}
    q = q.replace(/^#/, '').trim();
    if (!q) return null;

    const keyLower = q.toLowerCase();
    const now = Date.now();
    // 2.2s cooldown per code so holding the same paper doesn't double-fire within the same second
    if (gateQrCooldownMap[keyLower] && (now - gateQrCooldownMap[keyLower]) < 2200) {
        return null;
    }
    gateQrCooldownMap[keyLower] = now;

    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const inv = allSales.find(i =>
        String(i.invoiceNo || i.id || '').toLowerCase() === keyLower ||
        String(i.deliveryNo || '').toLowerCase() === keyLower ||
        (i.hash && String(i.hash).toLowerCase() === keyLower)
    );

    if (!inv) {
        gateContinuousCancelledCount++;
        playGateQrTone('cancelled');
        const msg = `❌ لم يتم العثور على إذن برقم: #${q}`;
        showGateLiveQrBanner('cancelled', msg);
        showToast(msg, 'error');
        return { status: 'not_found', message: msg, invoiceNo: q };
    }

    const invNo = String(inv.invoiceNo || inv.id);
    const custName = resolveGateCustomerName(inv);
    const alreadyDelivered = (
        inv.isDelivered === true ||
        inv.deliveryStatus === 'delivered' ||
        inv.deliveryStatus === 'Delivered_Pending_Admin' ||
        inv.deliveryStatus === 'Admin_Closed' ||
        inv.status === 'delivered' ||
        inv.status === 'Delivered_Pending_Admin' ||
        inv.status === 'Admin_Closed'
    );

    // SECOND SCAN OR ALREADY DELIVERED -> REJECT AS CANCELLED!
    if (alreadyDelivered) {
        gateContinuousCancelledCount++;
        loadInvoiceByNo(invNo, true);
        playGateQrTone('cancelled');
        const cancelMsg = `🚫 تم تسليم هذا الإذن (#${invNo} — ${custName}) مسبقاً — الإذن ملغي ولا يمكن صرفه مرة أخرى!`;
        showGateLiveQrBanner('cancelled', cancelMsg);
        showToast(cancelMsg, 'error');
        return {
            status: 'already_delivered_cancelled',
            message: `تم تسليم هذا الإذن (#${invNo}) مسبقاً — الإذن ملغي!`,
            invoiceNo: invNo,
            customerName: custName
        };
    }

    // FIRST SCAN -> INSTANT AUTO-DELIVERY WITHOUT TOUCHING ANY BUTTON!
    setVerified('qr');
    loadInvoiceByNo(invNo, true);

    const autoDeliverEnabled = document.getElementById('gate-continuous-auto-deliver-chk')?.checked !== false;
    if (autoDeliverEnabled) {
        document.querySelectorAll('.gate-deliver-qty-input').forEach(inp => {
            const idx = inp.dataset.itemIdx;
            setGateRowUnitMode(idx, 'primary', true);
        });
        executeDelivery();
        gateContinuousDeliveredCount++;
        playGateQrTone('ok');
        const okMsg = `✅ [${gateContinuousDeliveredCount}] تم استلام وخصم الإذن #${invNo} (${custName}) بالـ QR فوراً — مرّر الإذن التالي...`;
        showGateLiveQrBanner('ok', okMsg);
        return {
            status: 'delivered',
            message: `تم استلام وتسليم وخصم الإذن #${invNo} (${custName}) فوراً!`,
            invoiceNo: invNo,
            customerName: custName
        };
    } else {
        playGateQrTone('ok');
        showToast(`✅ تم قراءة باركود الإذن #${invNo} بنجاح`, 'success');
        return {
            status: 'loaded',
            message: `تم فتح الإذن #${invNo}`,
            invoiceNo: invNo,
            customerName: custName
        };
    }
}

function startCamera() {
    const ph = document.getElementById('camera-placeholder');
    const wrap = document.getElementById('qr-reader-wrap');
    const readerEl = document.getElementById('qr-reader');
    if (ph) ph.style.display = 'none';
    if (wrap) wrap.style.display = 'flex';
    if (readerEl) readerEl.style.display = 'block';

    if (typeof Html5Qrcode === 'undefined') {
        showToast('مكتبة الكاميرا قيد التحميل، يمكنك استخدام ربط كاميرا الجوال أو البحث الذكي', 'warning');
        return;
    }

    if (html5QrCode) {
        try { html5QrCode.stop().catch(() => {}); } catch (e) {}
    }

    html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 15, qrbox: { width: 240, height: 240 } },
        (decodedText) => {
            // Continuous Zero-Click Stream: keep camera running for all 10+ vouchers!
            processContinuousGateQrScan(decodedText, 'local_camera');
        },
        () => {}
    ).catch((err) => {
        showToast('تعذر فتح كاميرا الجهاز — استخدم زر "📱 ربط كاميرا الجوال" للربط المباشر', 'warning');
        if (ph) ph.style.display = 'flex';
        if (wrap) wrap.style.display = 'none';
    });
}

function stopCamera() {
    const ph = document.getElementById('camera-placeholder');
    const wrap = document.getElementById('qr-reader-wrap');
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            if (wrap) wrap.style.display = 'none';
            if (ph) ph.style.display = 'flex';
        }).catch(() => {
            if (wrap) wrap.style.display = 'none';
            if (ph) ph.style.display = 'flex';
        });
    } else {
        if (wrap) wrap.style.display = 'none';
        if (ph) ph.style.display = 'flex';
    }
}

async function openMobileCameraBridgeModal() {
    const modal = document.getElementById('mobile-camera-bridge-modal');
    if (modal) modal.style.display = 'flex';

    let baseOrigin = window.location.origin;
    const pathDir = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
    try {
        const res = await fetch(`api/qr_live_bridge.php?action=info&channel=${encodeURIComponent(GATE_BRIDGE_CHANNEL)}`);
        const info = await res.json();
        if (info && info.lanIp && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
            const portPart = window.location.port ? `:${window.location.port}` : '';
            baseOrigin = `${window.location.protocol}//${info.lanIp}${portPart}`;
        }
    } catch (e) {}

    const mobileGunUrl = `${baseOrigin}${pathDir}mobile-qr-gun.php?channel=${encodeURIComponent(GATE_BRIDGE_CHANNEL)}`;
    const qrImg = document.getElementById('bridge-pairing-qr-img');
    if (qrImg) {
        qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=6&data=${encodeURIComponent(mobileGunUrl)}`;
    }
    const directLink = document.getElementById('bridge-direct-open-link');
    if (directLink) {
        directLink.href = mobileGunUrl;
    }
    startGateMobileBridgeListener();
}

function closeMobileCameraBridgeModal() {
    const modal = document.getElementById('mobile-camera-bridge-modal');
    if (modal) modal.style.display = 'none';
}

function startGateMobileBridgeListener() {
    if (!gateBroadcastChannel) {
        try {
            gateBroadcastChannel = new BroadcastChannel('elsajana_qr_live_bridge');
            gateBroadcastChannel.onmessage = (ev) => {
                const msg = ev.data;
                if (!msg || msg.channel !== GATE_BRIDGE_CHANNEL) return;
                if (msg.type === 'MOBILE_CONNECTED') {
                    updateGateMobileBridgeDot(true);
                } else if (msg.type === 'QR_SCANNED' && msg.rawCode) {
                    processContinuousGateQrScan(msg.rawCode, 'mobile_broadcast');
                }
            };
        } catch (e) {}
    }

    if (gateBridgePollTimer) return;
    gateBridgePollTimer = setInterval(async () => {
        try {
            const res = await fetch(`api/qr_live_bridge.php?action=poll&channel=${encodeURIComponent(GATE_BRIDGE_CHANNEL)}`);
            const data = await res.json();
            if (!data || !data.ok) return;

            updateGateMobileBridgeDot(!!data.mobileConnected);

            const scans = Array.isArray(data.unprocessedScans) ? data.unprocessedScans : [];
            for (const s of scans) {
                if (!s.scanId || gateProcessedScanIds[s.scanId]) continue;
                gateProcessedScanIds[s.scanId] = true;

                const result = processContinuousGateQrScan(s.rawCode, 'mobile_bridge') || {
                    status: 'already_delivered_cancelled',
                    message: 'تم معالجة المسحة',
                    invoiceNo: s.rawCode
                };

                fetch(`api/qr_live_bridge.php?action=ack_scan&channel=${encodeURIComponent(GATE_BRIDGE_CHANNEL)}`, {
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
    }, 550);
}

function updateGateMobileBridgeDot(connected) {
    const dot = document.getElementById('mobile-bridge-live-dot');
    const connStatus = document.getElementById('bridge-modal-conn-status');
    if (dot) {
        dot.style.background = connected ? '#22c55e' : '#fcd34d';
        dot.style.boxShadow = connected ? '0 0 10px #22c55e' : 'none';
    }
    if (connStatus && connected) {
        connStatus.style.background = 'rgba(16,185,129,0.24)';
        connStatus.querySelector('span').innerHTML = '🟢 كاميرا الجوال متصلة الآن باللابتوب — مرّر الأذونات على الجوال للاستلام الفوري!';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    startGateMobileBridgeListener();
});

// ── IMAGE ATTACHMENT LOGIC ──
function handleImageUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(event) {
        currentAttachment = event.target.result;
        setVerified('photo');

        const previewDiv = document.getElementById('attachment-preview');
        const thumbImg = document.getElementById('attachment-thumb');
        if (previewDiv && thumbImg) {
            thumbImg.src = currentAttachment;
            previewDiv.style.display = 'block';
        }
        showToast('📸 تم إرفاق صورة توثيق التسليم بنجاح', 'success');
    };
    reader.readAsDataURL(file);
}

function setVerified(method) {
    isVerified = true;
    verificationMethod = method;
    const vStatus = document.getElementById('verification-status');
    const vBadge = document.getElementById('verification-badge');
    const btnDeliver = document.getElementById('btn-deliver');

    const labels = {
        'qr':       { status: '✅ تم التحقق البصري (كاميرا QR)', badge: 'متحقق (QR)' },
        'photo':    { status: '✅ تم التحقق البصري (مرفق صورة)', badge: 'متحقق (مرفق)' },
        'override': { status: '👑 صلاحية إشراف واعتماد مباشر (البوابة 4)', badge: 'معتمد (L4)' }
    };
    const lbl = labels[method] || { status: '✅ تم التحقق', badge: 'متحقق' };

    if (vStatus) {
        vStatus.innerHTML = lbl.status;
        vStatus.style.color = '#6ee7b7';
        vStatus.style.borderColor = 'rgba(16, 185, 129, 0.45)';
        vStatus.style.background = 'rgba(16, 185, 129, 0.14)';
    }
    if (vBadge) {
        vBadge.innerHTML = lbl.badge;
        vBadge.style.color = '#6ee7b7';
        vBadge.style.borderColor = 'rgba(16, 185, 129, 0.45)';
        vBadge.style.background = 'rgba(16, 185, 129, 0.14)';
    }

    if (currentLoadedInvoice && btnDeliver) {
        btnDeliver.disabled = false;
        btnDeliver.style.opacity = '1';
    }
}

// ── SMART OMNIBOX SEARCH ──
function filterSmartSearch(query) {
    const list = document.getElementById('smart-search-dropdown');
    if (!list) return;
    if (!query || !query.trim()) {
        list.style.display = 'none';
        return;
    }

    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const q = query.toLowerCase().trim();
    const results = allSales.filter(inv => {
        const invNo = String(inv.invoiceNo || inv.id || '').toLowerCase();
        const gdnNo = String(inv.deliveryNo || '').toLowerCase();
        const cName = resolveGateCustomerName(inv).toLowerCase();
        const date  = String(inv.date || '').toLowerCase();
        const itemsText = (inv.items || []).map(it => resolveGateItemMeta(it).name).join(' ').toLowerCase();
        return invNo.includes(q) || gdnNo.includes(q) || cName.includes(q) || date.includes(q) || itemsText.includes(q);
    }).slice(0, 10);

    if (results.length > 0) {
        list.innerHTML = results.map(r => {
            const invNo = r.invoiceNo || r.id;
            const cName = resolveGateCustomerName(r);
            const itemsCnt = (r.items || []).length;
            return `
                <div class="custom-dropdown-item" onclick="selectSmartResult('${invNo}')" style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color:#38bdf8;">#${invNo}</strong> — <span style="color:#6ee7b7; font-weight:800;">${cName}</span>
                        <div style="font-size:0.78rem; color:#94a3b8;">📦 ${itemsCnt} أصناف | 📅 ${r.date || ''}</div>
                    </div>
                    <span style="background:rgba(16,185,129,0.2); color:#34d399; padding:3px 10px; border-radius:99px; font-size:0.75rem; font-weight:800;">استدعاء</span>
                </div>
            `;
        }).join('');
        list.style.display = 'block';
    } else {
        list.innerHTML = `<div style="padding:12px; color:#94a3b8; font-size:0.85rem; text-align:center;">لا توجد نتائج مطابقة</div>`;
        list.style.display = 'block';
    }
}

function selectSmartResult(invNo) {
    const inp = document.getElementById('smart-omnibox');
    if (inp) inp.value = invNo;
    const dd = document.getElementById('smart-search-dropdown');
    if (dd) dd.style.display = 'none';
    loadInvoiceByNo(invNo);
}

function handleSmartSearch(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        loadInvoiceByNo(e.target.value.trim());
        const dd = document.getElementById('smart-search-dropdown');
        if (dd) dd.style.display = 'none';
    }
}

// ── INVOICE LOADING & ITEM INSPECTION ──
function loadInvoiceByNo(queryVal, silent = false, targetItemIdx = null, preferredUnitMode = 'primary') {
    if (!queryVal) return;
    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let q = String(queryVal).trim();
    try {
        if (q.includes('qr_deliver=')) {
            const u = new URL(q, window.location.origin);
            q = u.searchParams.get('qr_deliver') || q;
        }
    } catch (e) {}
    q = q.replace(/^#/, '').toLowerCase();

    let inv = allSales.find(i =>
        String(i.invoiceNo || i.id || '').toLowerCase() === q ||
        String(i.deliveryNo || '').toLowerCase() === q ||
        (i.hash && String(i.hash).toLowerCase() === q)
    );

    if (!inv) {
        inv = allSales.find(i => resolveGateCustomerName(i).toLowerCase().includes(q));
    }

    if (!inv) {
        if (!silent) showToast(`❌ لم يتم العثور على فاتورة أو إذن برقم: ${queryVal}`, 'error');
        return;
    }

    currentLoadedInvoice = inv;
    if (currentLevel >= 2) {
        setVerified('override');
    }
    renderInvoiceDetails(silent);
    renderGrid();
    renderGateRegistryTable();

    if (targetItemIdx !== null && targetItemIdx !== undefined) {
        setTimeout(() => {
            setGateRowUnitMode(targetItemIdx, preferredUnitMode, true);
            const targetInp = preferredUnitMode === 'base'
                ? document.querySelector(`.gate-deliver-base-input[data-item-idx="${targetItemIdx}"]`)
                : document.querySelector(`.gate-deliver-qty-input[data-item-idx="${targetItemIdx}"]`);
            if (targetInp) {
                targetInp.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetInp.focus();
                targetInp.select();
            }
        }, 60);
    }
}

function renderInvoiceDetails(silent = false) {
    if (!currentLoadedInvoice) return;
    const inv = currentLoadedInvoice;
    const invNo = inv.invoiceNo || inv.id || '---';
    const custName = resolveGateCustomerName(inv);

    document.getElementById('inv-info').style.opacity = '1';
    document.getElementById('lbl-inv-no').textContent = '#' + invNo + (inv.deliveryNo ? ` (${inv.deliveryNo})` : '');
    document.getElementById('lbl-customer').textContent = custName;
    document.getElementById('lbl-date').textContent = (inv.date || '---') + (inv.time ? ` | ${inv.time}` : '');
    document.getElementById('lbl-hash').textContent = inv.hash || ('GDN-SEC-' + btoa(unescape(encodeURIComponent(String(invNo)))).slice(0, 16));

    populateGateWarehouseSelect(inv.deliveredWarehouse || inv.warehouseId || inv.warehouse || 1);

    const recEl = document.getElementById('gate-receiver-name');
    const drvEl = document.getElementById('gate-driver-name');
    const trkEl = document.getElementById('gate-truck-plate');
    const gpEl  = document.getElementById('gate-pass-no');
    const wNotes = document.getElementById('warehouse-notes');
    const pNotes = document.getElementById('personal-notes');

    if (recEl) recEl.value = inv.receiverName || custName || '';
    if (drvEl) drvEl.value = inv.driverName || '';
    if (trkEl) trkEl.value = inv.truckPlate || '';
    if (gpEl)  gpEl.value  = inv.gatePassNo || ('GP-' + String(invNo).replace(/\D/g, '').slice(-4) + '-' + Date.now().toString().slice(-3));
    if (wNotes) wNotes.value = inv.warehouseNotes || inv.notes || '';
    if (pNotes) pNotes.value = inv.personalNotes || '';

    const statusEl = document.getElementById('lbl-status');
    const adminEl  = document.getElementById('lbl-admin-status');
    const btnDeliver = document.getElementById('btn-deliver');
    const btnAdminClose = document.getElementById('btn-admin-close');
    const btnPartial = document.getElementById('btn-partial');

    const st = inv.deliveryStatus || inv.status || 'pending';

    if (st === 'Admin_Closed') {
        statusEl.textContent = '👑 مغلق ومعتمد نهائياً (L4)';
        statusEl.className = 'status-badge delivered';
        adminEl.textContent = '✅ مكتمل ومغلق إدارياً';
        adminEl.style.color = '#34d399';
        if (btnDeliver) { btnDeliver.disabled = true; btnDeliver.style.opacity = '0.45'; }
        if (btnAdminClose) btnAdminClose.style.display = 'none';
        if (btnPartial) btnPartial.disabled = false;
    } else if (st === 'Delivered_Pending_Admin' || inv.isDelivered === true || st === 'delivered') {
        statusEl.textContent = '✅ تم صرف البضاعة (' + (inv.deliveryNo || 'GDN') + ')';
        statusEl.className = 'status-badge stage1';
        adminEl.textContent = '⏳ خرجت من المخزن — متاح للاعتماد الإداري النهائي (L4)';
        adminEl.style.color = '#38bdf8';
        if (btnDeliver) { btnDeliver.disabled = false; btnDeliver.style.opacity = '1'; btnDeliver.innerHTML = '🔄 تحديث بيانات إذن الصرف'; }
        if (btnAdminClose) btnAdminClose.style.display = currentLevel >= 4 ? 'inline-flex' : 'none';
        if (btnPartial) btnPartial.disabled = false;
    } else {
        statusEl.textContent = st === 'partial' ? '🌗 تسليم جزئي مفتوح' : '⏳ قيد التسليم (مفتوح للصرف)';
        statusEl.className = 'status-badge';
        adminEl.textContent = '🟢 جاهز للفحص والصرف المخزني';
        adminEl.style.color = '#fbbf24';
        if (btnDeliver) { btnDeliver.disabled = false; btnDeliver.style.opacity = '1'; btnDeliver.innerHTML = '🚚 تأكيد واعتماد التسليم والصرف الآن'; }
        if (btnAdminClose) btnAdminClose.style.display = 'none';
        if (btnPartial) btnPartial.disabled = true;
        if (!silent) showToast(`📦 تم استدعاء الفاتورة #${invNo} بكامل أصنافها`, 'success');
    }
}

function onReviewNotesChange() {
    const wNotes = document.getElementById('warehouse-notes');
    const hint   = document.getElementById('warehouse-notes-hint');
    if (!wNotes) return;
    if (wNotes.value.trim().length > 0 && hint) {
        hint.style.display = 'none';
    }
}

function toggleGateOptionalCols(fromInit = true) {
    const colMap = {
        'gate-chk-col-wh': '.gate-opt-col-wh',
        'gate-chk-col-item': '.gate-opt-col-item',
        'gate-chk-col-spec': '.gate-opt-col-spec',
        'gate-chk-col-punit': '.gate-opt-col-punit',
        'gate-chk-col-qty': '.gate-opt-col-qty',
        'gate-chk-col-units': '.gate-opt-col-units',
        'gate-chk-col-price': '.gate-opt-col-price',
        'gate-chk-col-deliver': '.gate-opt-col-deliver',
        'gate-chk-col-status': '.gate-opt-col-status'
    };
    const saved = readPersistentGateJson('magaies_gate_opt_cols_v2', 'erp_gate_opt_cols_v1');
    const hasSaved = Object.keys(saved).length > 0;
    if (fromInit && hasSaved) {
        Object.keys(colMap).forEach(id => {
            const chk = document.getElementById(id);
            if (chk && saved[id] !== undefined) chk.checked = !!saved[id];
        });
    }
    const toSave = {};
    Object.keys(colMap).forEach(id => {
        const chk = document.getElementById(id);
        const show = (fromInit && hasSaved && saved[id] !== undefined)
            ? !!saved[id]
            : (chk ? chk.checked : (saved[id] !== undefined ? !!saved[id] : (id !== 'gate-chk-col-price')));
        if (chk) chk.checked = show;
        toSave[id] = show;
        document.querySelectorAll(colMap[id]).forEach(el => {
            if (show) {
                el.style.removeProperty('display');
            } else {
                el.style.setProperty('display', 'none', 'important');
            }
        });
    });
    if (!fromInit || !hasSaved) {
        writePersistentGateJson('magaies_gate_opt_cols_v2', 'erp_gate_opt_cols_v1', toSave);
    }
    const savedScale = localStorage.getItem('magaies_gate_table_scale_v2') || localStorage.getItem('erp_gate_table_scale_v1');
    if (savedScale) setGateTableScale(savedScale, false);
}

function lockAndSaveGateOptCols() {
    toggleGateOptionalCols(false);
    const drawer = document.getElementById('gate-opt-cols-drawer');
    if (drawer) drawer.style.display = 'none';
    if (typeof showToast === 'function') {
        showToast('✅ تم تثبيت وحفظ ضبط كولومات البطاقة نهائياً ولن يتغير إلا عند تعديله مجدداً', 'success');
    }
}

function setGateTableScale(mode, persist = true) {
    const tbl = document.getElementById('delivery-grid');
    if (tbl) {
        if (mode === 'ultrawide') {
            tbl.style.minWidth = '1780px';
        } else if (mode === 'wide') {
            tbl.style.minWidth = '1520px';
        } else {
            tbl.style.minWidth = '1320px';
        }
    }
    if (persist) {
        try { localStorage.setItem('magaies_gate_table_scale_v2', mode); } catch (e) {}
        try { localStorage.setItem('erp_gate_table_scale_v1', mode); } catch (e) {}
    }
}

function applyGateColPreset(preset) {
    const setChk = (id, val) => { const el = document.getElementById(id); if (el) el.checked = val; };
    if (preset === 'focus') {
        setChk('gate-chk-col-wh', true);
        setChk('gate-chk-col-item', true);
        setChk('gate-chk-col-spec', false);
        setChk('gate-chk-col-punit', false);
        setChk('gate-chk-col-qty', true);
        setChk('gate-chk-col-units', true);
        setChk('gate-chk-col-price', false);
        setChk('gate-chk-col-deliver', true);
        setChk('gate-chk-col-status', true);
        setGateTableScale('ultrawide', true);
    } else {
        setChk('gate-chk-col-wh', true);
        setChk('gate-chk-col-item', true);
        setChk('gate-chk-col-spec', true);
        setChk('gate-chk-col-punit', true);
        setChk('gate-chk-col-qty', true);
        setChk('gate-chk-col-units', true);
        setChk('gate-chk-col-deliver', true);
        setChk('gate-chk-col-status', true);
        setGateTableScale('wide', true);
    }
    toggleGateOptionalCols(false);
}

function renderGrid() {
    if (!currentLoadedInvoice) return;
    const tbody = document.getElementById('delivery-tbody');
    const tfoot = document.getElementById('delivery-tfoot');
    const summaryBadge = document.getElementById('gate-items-summary-badge');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (tfoot) tfoot.innerHTML = '';

    toggleGateOptionalCols(true);
    const showUnits = document.getElementById('gate-chk-col-units')?.checked !== false;
    const showPrice = document.getElementById('gate-chk-col-price')?.checked === true;

    const rows = currentLoadedInvoice.items || currentLoadedInvoice.rows || [];
    const whs = getWarehousesList();
    const selWhId = document.getElementById('gate-warehouse-select')?.value || currentLoadedInvoice.warehouseId || currentLoadedInvoice.warehouse || whs[0].id;
    const whObj = whs.find(w => String(w.id) === String(selWhId) || w.name === selWhId) || whs[0];

    let totalOrdered = 0;
    let totalBaseUnits = 0;
    let totalAmountSum = 0;
    let totalDeliverNow = 0;

    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:28px; color:#fca5a5; font-weight:800;">⚠️ لا توجد أصناف مسجلة في هذا المستند</td></tr>`;
        if (summaryBadge) summaryBadge.textContent = '0 أصناف';
        return;
    }

    rows.forEach((row, idx) => {
        const meta = resolveGateItemMeta(row);
        const origQty = meta.qty;
        const prevDel = parseFloat(row.deliveredQty || 0) || 0;
        const isAlreadyDel = currentLoadedInvoice.isDelivered === true || currentLoadedInvoice.deliveryStatus === 'delivered' || currentLoadedInvoice.deliveryStatus === 'Delivered_Pending_Admin' || currentLoadedInvoice.deliveryStatus === 'Admin_Closed';
        const remQty = isAlreadyDel ? origQty : Math.max(0, origQty - prevDel);
        totalOrdered += origQty;
        totalBaseUnits += meta.baseQty;
        totalAmountSum += meta.total;
        totalDeliverNow += remQty;

        const availStock = getAvailableStockForGateItem(row, whObj.id);
        const deliverBaseNow = +(remQty * meta.factor).toFixed(2);

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align: center; color: #94a3b8; font-weight: 800;">${idx + 1}</td>
            <td class="gate-opt-col-wh" style="text-align: right;">
                <div style="font-size: 0.88rem; color: #fcd34d; font-weight: 900;">🏢 ${whObj.name}</div>
                <div style="font-size: 0.74rem; color: ${availStock < remQty ? '#f87171' : '#94a3b8'}; font-weight: 700;">المتاح: ${availStock} ${meta.unit}</div>
            </td>
            <td class="gate-opt-col-item" style="text-align: right;">
                <div style="font-weight: 900; color: #f8fafc; font-size: 0.98rem;">${meta.name}</div>
                <div style="font-size: 0.72rem; color: #64748b; font-family: 'Inter', monospace;">كود: ${meta.code}</div>
            </td>
            <td class="gate-opt-col-spec" style="text-align: center;">
                ${meta.tonRefHint
                    ? `<div style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.35); color:#7dd3fc; padding:4px 8px; border-radius:8px; font-size:0.78rem; font-weight:800;">⚖️ ${meta.tonRefHint}</div>`
                    : `<div style="color:#94a3b8; font-size:0.78rem; font-weight:700;">1 ${meta.unit} = ${meta.factor} ${meta.baseUnit}</div>`
                }
            </td>
            <td class="gate-opt-col-punit" style="text-align: center;">
                <span class="gdn-unit-badge" style="font-size: 0.86rem; padding: 4px 12px;">${meta.unit}</span>
            </td>
            <td class="qty-col gate-opt-col-qty" style="text-align: center; font-size: 1.08rem;">
                ${origQty}
                ${prevDel > 0 && !isAlreadyDel ? `<div style="font-size:0.72rem; color:#34d399;">سُلّم: ${prevDel}</div>` : ''}
            </td>
            <td class="gate-opt-col-units" style="text-align: center; display: ${showUnits ? '' : 'none'}; background: rgba(99,102,241,0.06);">
                <div class="gdn-receive-unit-box units-mirror-box" style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 10px; cursor: pointer;">
                    <div style="font-weight: 900; color: #ffffff !important; font-size: 0.98rem;">${meta.baseQty.toLocaleString('en-US', { maximumFractionDigits: 2 })} ${meta.baseUnit}</div>
                    <div style="font-size: 0.72rem; color: #cbd5e1 !important; font-weight: 700;">1 ${meta.unit} = ${meta.factor} ${meta.baseUnit}</div>
                </div>
            </td>
            <td class="gate-opt-col-price" style="text-align: center; display: ${showPrice ? '' : 'none'}; color: #fcd34d; font-weight: 800;">
                ${meta.price.toLocaleString('en-US', { maximumFractionDigits: 2 })}
            </td>
            <td class="gate-opt-col-price" style="text-align: center; display: ${showPrice ? '' : 'none'}; color: #6ee7b7; font-weight: 800;">
                ${meta.total.toLocaleString('en-US', { maximumFractionDigits: 2 })}
            </td>
            <td class="gate-opt-col-deliver" style="text-align: center; min-width: 530px;">
                <div style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
                    <div style="display: flex; align-items: stretch; justify-content: center; gap: 10px; flex-wrap: nowrap; padding: 4px 0;">
                        <!-- المربع الأول: استلم هنا بالوحدة الأساسية (نفس المقاس والإضاءة والرجوع للحجم الطبيعي 100%) -->
                        <div id="gate-unit-box-primary-${idx}" class="gdn-receive-unit-box primary-box is-active-unit-mode" onmouseenter="setGateRowUnitMode(${idx}, 'primary', false)" style="display: flex; flex-direction: column; justify-content: space-between; gap: 6px; width: 242px; min-width: 242px; max-width: 242px; min-height: 114px; padding: 8px 10px; border-radius: 12px; box-sizing: border-box;">
                            <div style="display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 5px; width: 100%;">
                                <button type="button" id="gate-mode-btn-primary-${idx}" onclick="setGateRowUnitMode(${idx}, 'primary', true)" class="gdn-kinetic-btn" style="height: 28px; padding: 0 8px; font-size: 0.74rem; font-weight: 900; background: #020617 !important; color: #ffffff !important; border: 1.5px solid #10b981 !important; border-radius: 7px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    📦 استلم هنا بالـ ${meta.unit} (${remQty})
                                </button>
                                <button type="button" onclick="setGateRowUnitMode(${idx}, 'primary', true)" class="gdn-kinetic-btn" style="height: 28px; padding: 0 8px; font-size: 0.72rem; font-weight: 900; background: #020617 !important; color: #38bdf8 !important; border: 1px solid rgba(56,189,248,0.55) !important; border-radius: 7px; white-space: nowrap;">
                                    ⚡ الكل
                                </button>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; width: 100%; background: rgba(2,6,23,0.92); padding: 4px 6px; border-radius: 9px; border: 1px solid rgba(16,185,129,0.4);">
                                <button type="button" onclick="adjustGateItemQty(${idx}, -1, 'primary')" class="gdn-kinetic-btn" style="width: 28px; height: 32px; padding: 0; background: rgba(239,68,68,0.22); color: #ffffff !important; border: 1px solid rgba(239,68,68,0.45); font-size: 0.95rem; font-weight: 900; border-radius: 7px; flex-shrink: 0;">−</button>
                                <input type="text" inputmode="decimal" class="gdn-borderless-input gate-deliver-qty-input" data-item-idx="${idx}" data-max-qty="${remQty}" data-factor="${meta.factor}" data-primary-unit="${meta.unit}" data-base-unit="${meta.baseUnit}" max="${origQty}" value="${remQty}" oninput="onGateDeliverQtyInput(this)" style="width: 96px !important; min-width: 96px !important; max-width: 96px !important; height: 34px !important; text-align: center; padding: 2px 4px !important; font-size: 1.12rem !important; font-weight: 900 !important; color: #ffffff !important; background: #020617 !important;">
                                <span style="font-size: 0.74rem; font-weight: 900; color: #ffffff !important; min-width: 42px; max-width: 48px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${meta.unit}</span>
                                <button type="button" onclick="adjustGateItemQty(${idx}, 1, 'primary')" class="gdn-kinetic-btn" style="width: 28px; height: 32px; padding: 0; background: rgba(16,185,129,0.25); color: #ffffff !important; border: 1px solid rgba(16,185,129,0.5); font-size: 0.95rem; font-weight: 900; border-radius: 7px; flex-shrink: 0;">+</button>
                            </div>
                        </div>
                        ${meta.hasMultiUnits ? `
                        <span style="color: #94a3b8; font-weight: 900; align-self: center;">⇄</span>
                        <!-- المربع الثاني: استلم هنا بالوحدة الفرعية (مطابق تماماً للمربع الأول في المقاس والتكبير والرجوع للحجم الطبيعي مع تباين لوني هادئ) -->
                        <div id="gate-unit-box-base-${idx}" class="gdn-receive-unit-box base-box" onmouseenter="setGateRowUnitMode(${idx}, 'base', false)" style="display: flex; flex-direction: column; justify-content: space-between; gap: 6px; width: 242px; min-width: 242px; max-width: 242px; min-height: 114px; padding: 8px 10px; border-radius: 12px; box-sizing: border-box;">
                            <div style="display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 5px; width: 100%;">
                                <button type="button" id="gate-mode-btn-base-${idx}" onclick="setGateRowUnitMode(${idx}, 'base', true)" class="gdn-kinetic-btn" style="height: 28px; padding: 0 8px; font-size: 0.74rem; font-weight: 900; background: #020617 !important; color: #ffffff !important; border: 1.5px solid #6366f1 !important; border-radius: 7px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    🔢 استلم هنا بالـ ${meta.baseUnit} (${deliverBaseNow})
                                </button>
                                <button type="button" onclick="setGateRowUnitMode(${idx}, 'base', true)" class="gdn-kinetic-btn" style="height: 28px; padding: 0 8px; font-size: 0.72rem; font-weight: 900; background: #020617 !important; color: #38bdf8 !important; border: 1px solid rgba(56,189,248,0.55) !important; border-radius: 7px; white-space: nowrap;">
                                    ⚡ الكل
                                </button>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; width: 100%; background: rgba(2,6,23,0.92); padding: 4px 6px; border-radius: 9px; border: 1px solid rgba(99,102,241,0.45);">
                                <button type="button" onclick="adjustGateItemQty(${idx}, -1, 'base')" class="gdn-kinetic-btn" style="width: 28px; height: 32px; padding: 0; background: rgba(239,68,68,0.22); color: #ffffff !important; border: 1px solid rgba(239,68,68,0.45); font-size: 0.95rem; font-weight: 900; border-radius: 7px; flex-shrink: 0;">−</button>
                                <input type="text" inputmode="decimal" class="gdn-borderless-input gate-deliver-base-input" data-item-idx="${idx}" data-max-base="${deliverBaseNow}" data-factor="${meta.factor}" value="${deliverBaseNow}" oninput="onGateDeliverBaseInput(this)" style="width: 96px !important; min-width: 96px !important; max-width: 96px !important; height: 34px !important; text-align: center; padding: 2px 4px !important; font-size: 1.12rem !important; font-weight: 900 !important; color: #ffffff !important; background: #020617 !important;">
                                <span style="font-size: 0.74rem; font-weight: 900; color: #ffffff !important; min-width: 42px; max-width: 48px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${meta.baseUnit}</span>
                                <button type="button" onclick="adjustGateItemQty(${idx}, 1, 'base')" class="gdn-kinetic-btn" style="width: 28px; height: 32px; padding: 0; background: rgba(16,185,129,0.25); color: #ffffff !important; border: 1px solid rgba(16,185,129,0.5); font-size: 0.95rem; font-weight: 900; border-radius: 7px; flex-shrink: 0;">+</button>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    <div id="gate-live-base-${idx}" style="font-size: 0.73rem; color: #ffffff; font-weight: 800;">
                        المستلم الآن: <strong style="color:#34d399;">${remQty} ${meta.unit}</strong> ${meta.hasMultiUnits ? `⇄ <strong style="color:#a5b4fc;">${deliverBaseNow.toLocaleString('en-US')} ${meta.baseUnit}</strong>` : ''}
                    </div>
                </div>
            </td>
            <td class="gate-opt-col-status" style="text-align: center;">
                ${isAlreadyDel
                    ? `<span class="status-badge delivered">✅ تم التسليم</span>`
                    : `<span class="status-badge" style="background:rgba(16,185,129,0.14); color:#6ee7b7; border-color:rgba(16,185,129,0.3);">جاهز للصرف</span>`
                }
            </td>
        `;
        tbody.appendChild(tr);
    });

    if (tfoot) {
        tfoot.innerHTML = `
            <tr style="background: rgba(15,23,42,0.9); font-weight: 900; border-top: 1px solid rgba(148,163,184,0.2);">
                <td colspan="3" style="text-align: right; color: #cbd5e1;">إجمالي البنود: <strong style="color:#fff;">${rows.length} أصناف</strong></td>
                <td style="text-align: center; color: #94a3b8;">—</td>
                <td style="text-align: center; color: #38bdf8; font-size: 1rem;">${totalOrdered}</td>
                <td class="gate-opt-col-units" style="text-align: center; color: #a5b4fc; display: ${showUnits ? '' : 'none'};">${totalBaseUnits.toLocaleString('en-US', { maximumFractionDigits: 2 })} وحدة</td>
                <td class="gate-opt-col-price" style="text-align: center; display: ${showPrice ? '' : 'none'};">—</td>
                <td class="gate-opt-col-price" style="text-align: center; color: #6ee7b7; display: ${showPrice ? '' : 'none'};">${totalAmountSum.toLocaleString('en-US', { maximumFractionDigits: 2 })}</td>
                <td style="text-align: center; color: #34d399; font-size: 1rem;">${totalDeliverNow}</td>
                <td></td>
            </tr>
        `;
    }

    if (summaryBadge) {
        summaryBadge.textContent = `${rows.length} أصناف | إجمالي الوحدات: ${totalBaseUnits.toLocaleString('en-US', { maximumFractionDigits: 2 })}`;
    }
    toggleGateOptionalCols(true);
}

function setGateRowUnitMode(idx, mode = 'primary', fillFull = false) {
    const inp = document.querySelector(`.gate-deliver-qty-input[data-item-idx="${idx}"]`);
    const baseInp = document.querySelector(`.gate-deliver-base-input[data-item-idx="${idx}"]`);
    const boxPrimary = document.getElementById(`gate-unit-box-primary-${idx}`);
    const boxBase = document.getElementById(`gate-unit-box-base-${idx}`);
    const btnPrimary = document.getElementById(`gate-mode-btn-primary-${idx}`);
    const btnBase = document.getElementById(`gate-mode-btn-base-${idx}`);
    if (!inp) return;

    const maxQ = parseFloat(inp.dataset.maxQty || inp.getAttribute('max') || 0);
    const factor = parseFloat(inp.dataset.factor || 1) || 1;
    if (fillFull) {
        inp.value = +maxQ.toFixed(4);
        if (baseInp) baseInp.value = +(maxQ * factor).toFixed(2);
    }
    if (boxPrimary && boxBase) {
        boxPrimary.classList.toggle('is-active-unit-mode', mode === 'primary');
        boxBase.classList.toggle('is-active-unit-mode', mode === 'base');
    }
    if (btnPrimary && btnBase) {
        btnPrimary.style.borderColor = mode === 'primary' ? '#10b981' : 'rgba(16,185,129,0.45)';
        btnPrimary.style.boxShadow = mode === 'primary' ? '0 0 8px rgba(16,185,129,0.35)' : 'none';
        btnBase.style.borderColor = mode === 'base' ? '#10b981' : 'rgba(99,102,241,0.5)';
        btnBase.style.boxShadow = mode === 'base' ? '0 0 8px rgba(16,185,129,0.35)' : 'none';
    }
    onGateDeliverQtyInput(inp, false);
}

function onGateDeliverQtyInput(inp, syncBase = true) {
    if (!inp) return;
    const idx = inp.dataset.itemIdx;
    const factor = parseFloat(inp.dataset.factor || 1) || 1;
    const primaryUnit = inp.dataset.primaryUnit || 'وحدة';
    const baseUnit = inp.dataset.baseUnit || 'وحدة';
    const maxQ = parseFloat(inp.dataset.maxQty || inp.getAttribute('max') || 999999);
    let val = parseFloat(inp.value || 0) || 0;
    if (val > maxQ) {
        val = maxQ;
        inp.value = +val.toFixed(4);
    }
    const baseVal = +(val * factor).toFixed(2);
    if (syncBase) {
        const baseInp = document.querySelector(`.gate-deliver-base-input[data-item-idx="${idx}"]`);
        if (baseInp) baseInp.value = baseVal;
    }
    const liveEl = document.getElementById(`gate-live-base-${idx}`);
    if (liveEl) {
        liveEl.innerHTML = `المستلم الآن: <strong style="color:#34d399;">${val.toLocaleString('en-US', { maximumFractionDigits: 3 })} ${primaryUnit}</strong> ${factor > 1 ? `⇄ <strong style="color:#a5b4fc;">${baseVal.toLocaleString('en-US')} ${baseUnit}</strong>` : ''}`;
    }
}

function onGateDeliverBaseInput(baseInp) {
    if (!baseInp) return;
    const idx = baseInp.dataset.itemIdx;
    const factor = parseFloat(baseInp.dataset.factor || 1) || 1;
    const maxB = parseFloat(baseInp.dataset.maxBase || 999999);
    let bVal = parseFloat(baseInp.value || 0) || 0;
    if (bVal > maxB) {
        bVal = maxB;
        baseInp.value = +bVal.toFixed(2);
    }
    const inp = document.querySelector(`.gate-deliver-qty-input[data-item-idx="${idx}"]`);
    if (inp) {
        inp.value = factor > 0 ? +(bVal / factor).toFixed(4) : bVal;
        setGateRowUnitMode(idx, 'base', false);
    }
}

function adjustGateItemQty(idx, delta, targetMode = 'primary') {
    const inp = document.querySelector(`.gate-deliver-qty-input[data-item-idx="${idx}"]`);
    const baseInp = document.querySelector(`.gate-deliver-base-input[data-item-idx="${idx}"]`);
    if (!inp) return;
    const maxQ = parseFloat(inp.dataset.maxQty || inp.getAttribute('max') || 999999);
    const factor = parseFloat(inp.dataset.factor || 1) || 1;
    if (targetMode === 'base' && baseInp) {
        const maxB = +(maxQ * factor).toFixed(4);
        const curB = parseFloat(baseInp.value || 0);
        const nextB = Math.min(maxB, Math.max(0, +(curB + delta).toFixed(2)));
        baseInp.value = nextB;
        inp.value = factor > 0 ? +(nextB / factor).toFixed(4) : nextB;
        setGateRowUnitMode(idx, 'base', false);
    } else {
        const cur = parseFloat(inp.value || 0);
        const next = Math.min(maxQ, Math.max(0, +(cur + delta).toFixed(4)));
        inp.value = next;
        if (baseInp) baseInp.value = +(next * factor).toFixed(2);
        setGateRowUnitMode(idx, 'primary', false);
    }
}

function fillAllGateDeliverQtys() {
    document.querySelectorAll('.gate-deliver-qty-input').forEach(inp => {
        const idx = inp.dataset.itemIdx;
        setGateRowUnitMode(idx, 'primary', true);
    });
    showToast('⚡ تم ضبط جميع الأصناف على كامل الكمية المطلوبة', 'success');
}

// ── EXECUTE PHYSICAL DELIVERY & SYNC WITH ALL SCREENS ──
function executeDelivery() {
    if (!currentLoadedInvoice) {
        showToast('⚠️ يرجى اختيار أو استدعاء فاتورة أولاً من السجل العلوي', 'warning');
        return;
    }
    if (!isVerified && currentLevel < 2) {
        showToast('يجب التحقق أولاً بالكاميرا أو إرفاق الصورة في البوابة 1!', 'error');
        return;
    }

    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const invNo = String(currentLoadedInvoice.invoiceNo || currentLoadedInvoice.id);
    const invIndex = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);

    if (invIndex === -1) {
        showToast('خطأ: الفاتورة غير موجودة في قاعدة البيانات.', 'error');
        return;
    }

    const inv = allSales[invIndex];
    const whs = getWarehousesList();
    const selectedWhId = document.getElementById('gate-warehouse-select')?.value || inv.warehouseId || inv.warehouse || whs[0].id;
    const selectedWhObj = whs.find(w => String(w.id) === String(selectedWhId) || w.name === selectedWhId) || whs[0];

    const qtyInputs = document.querySelectorAll('.gate-deliver-qty-input');
    const rows = Array.isArray(inv.items) ? inv.items : [];
    const wasAlreadyDelivered = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Delivered_Pending_Admin';

    let isPartialDelivery = false;
    const deliveredItemsPayload = [];

    rows.forEach((item, idx) => {
        const meta = resolveGateItemMeta(item);
        const origQty = parseFloat(item.qty ?? item.quantity ?? 0) || 0;
        const prevDel = wasAlreadyDelivered ? 0 : (parseFloat(item.deliveredQty || 0) || 0);
        const maxRem = Math.max(0, origQty - prevDel);
        let deliverNow = maxRem;

        if (qtyInputs[idx]) {
            const parsed = parseFloat(qtyInputs[idx].value);
            if (!isNaN(parsed)) deliverNow = Math.min(origQty, Math.max(0, parsed));
        }

        item.name = meta.name;
        item.itemName = meta.name;
        item.unit = meta.unit;
        item.selectedUnit = meta.unit;
        item.deliveredQty = wasAlreadyDelivered ? deliverNow : (prevDel + deliverNow);

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
                warehouseId: selectedWhObj.id,
                warehouseName: selectedWhObj.name
            });
        }
    });

    if (deliveredItemsPayload.length === 0) {
        showToast('⚠️ يرجى تحديد كمية أكبر من صفر للتسليم', 'warning');
        return;
    }

    // Deduct stock if not already deducted
    if (!wasAlreadyDelivered) {
        let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
        let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

        deliveredItemsPayload.forEach(dItem => {
            const deductQty = parseFloat(dItem.qty || 0);
            const itemNameClean = String(dItem.name || '').trim();

            let invEntry = inventory.find(x =>
                (String(x.id || x.itemId) === String(dItem.itemId || dItem.id) || String(x.name || '').trim() === itemNameClean) &&
                String(x.warehouseId || x.warehouse || 1) === String(selectedWhObj.id)
            );
            if (invEntry) {
                invEntry.qty = (parseFloat(invEntry.qty || 0) - deductQty);
            } else {
                inventory.push({
                    id: dItem.itemId || dItem.id || Date.now(),
                    name: itemNameClean,
                    warehouseId: selectedWhObj.id,
                    qty: -deductQty,
                    unit: dItem.unit || 'قطعة'
                });
            }

            const masterIdx = erpItems.findIndex(x =>
                String(x.id) === String(dItem.itemId || dItem.id) ||
                String(x.name || '').trim() === itemNameClean
            );
            if (masterIdx !== -1) {
                const mItem = erpItems[masterIdx];
                if (!mItem.stock || typeof mItem.stock !== 'object') mItem.stock = {};
                if (!mItem.actualStock || typeof mItem.actualStock !== 'object') mItem.actualStock = {};
                mItem.stock[selectedWhObj.id] = (parseFloat(mItem.stock[selectedWhObj.id]) || 0) - deductQty;
                mItem.actualStock[selectedWhObj.id] = (parseFloat(mItem.actualStock[selectedWhObj.id]) || 0) - deductQty;
                if (mItem.quantity !== undefined) {
                    mItem.quantity = (parseFloat(mItem.quantity) || 0) - deductQty;
                }
                erpItems[masterIdx] = mItem;
            }
        });

        localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
        localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));
    }

    const warehouseNotesVal = document.getElementById('warehouse-notes')?.value.trim() || 'تم الفحص والتسليم من البوابة المخزنية';
    const personalNotesVal  = document.getElementById('personal-notes')?.value.trim() || '';
    const receiverName      = document.getElementById('gate-receiver-name')?.value.trim() || resolveGateCustomerName(inv);
    const driverName        = document.getElementById('gate-driver-name')?.value.trim() || '';
    const truckPlate        = document.getElementById('gate-truck-plate')?.value.trim() || '';
    const gatePassNo        = document.getElementById('gate-pass-no')?.value.trim() || ('GP-' + Date.now().toString().slice(-5));
    const gdnNo             = inv.deliveryNo || ('GDN-' + Date.now().toString().slice(-6));
    const nowIso            = new Date().toISOString();

    const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };

    inv.isDelivered         = !isPartialDelivery;
    inv.deliveryStatus      = isPartialDelivery ? 'partial' : 'Delivered_Pending_Admin';
    inv.status              = isPartialDelivery ? 'partial' : 'Delivered_Pending_Admin';
    inv.deliveryNo          = gdnNo;
    inv.deliveredWarehouse  = selectedWhObj.id;
    inv.deliveredWarehouseName = selectedWhObj.name;
    inv.deliveryDate        = nowIso.slice(0, 10);
    inv.deliveryTime        = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    inv.deliveredAt         = nowIso;
    inv.deliveredBy         = curUser.id;
    inv.deliveredByName     = curUser.fullName || curUser.name;
    inv.adminDeadline       = Date.now() + (72 * 3600 * 1000);
    inv.verificationMethod  = verificationMethod;
    inv.attachmentBase64    = currentAttachment;
    inv.deliveredByLevel    = currentLevel;
    inv.warehouseNotes      = warehouseNotesVal;
    inv.personalNotes       = personalNotesVal;
    inv.receiverName        = receiverName;
    inv.driverName          = driverName;
    inv.truckPlate          = truckPlate;
    inv.gatePassNo          = gatePassNo;

    allSales[invIndex] = inv;
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
        customer: resolveGateCustomerName(inv),
        customerName: resolveGateCustomerName(inv),
        warehouse: selectedWhObj.name,
        warehouseId: selectedWhObj.id,
        receiverName,
        driverName,
        truckPlate,
        gatePassNo,
        notes: warehouseNotesVal,
        personalNotes: personalNotesVal,
        isPartialDelivery,
        status: 'delivered',
        items: deliveredItemsPayload,
        deleted: 0
    };

    let deliveryNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
    deliveryNotes = deliveryNotes.filter(n => String(n.invoiceNo) !== invNo);
    deliveryNotes.unshift(gdnRecord);
    localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(deliveryNotes));

    try {
        fetch('api/mirror_sync.php?action=save_delivery_note', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...gdnRecord, updatedInvoiceItems: inv.items })
        }).catch(() => {});
    } catch (eOff) {}

    currentLoadedInvoice = inv;
    renderInvoiceDetails(true);
    renderGrid();
    renderGateRegistryTable();
    showToast(`🎉 تم اعتماد إذن الصرف (${gdnNo}) وخصم المخزون بنجاح!`, 'success');
}

// ── L4 FINAL ADMINISTRATIVE CLOSURE ──
function executeAdminClosureL4() {
    if (!currentLoadedInvoice) return;
    const invNo = String(currentLoadedInvoice.invoiceNo || currentLoadedInvoice.id);
    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const idx = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);
    if (idx === -1) return;

    allSales[idx].deliveryStatus = 'Admin_Closed';
    allSales[idx].status = 'Admin_Closed';
    allSales[idx].isDelivered = true;
    allSales[idx].adminClosedAt = new Date().toISOString();
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(allSales[idx]));

    currentLoadedInvoice = allSales[idx];
    renderInvoiceDetails(true);
    renderGrid();
    renderGateRegistryTable();
    showToast(`👑 تم الاعتماد الإداري النهائي وإغلاق إذن المنصرف للفاتورة #${invNo} (L4)`, 'success');
}

// ── UNDO / REVERSE DELIVERY & RESTORE STOCK ──
function undoCurrentGateDelivery() {
    if (!currentLoadedInvoice) return;
    const invNo = String(currentLoadedInvoice.invoiceNo || currentLoadedInvoice.id);
    if (!confirm(`هل أنت متأكد من إلغاء صرف الفاتورة #${invNo} وإعادة الكميات إلى رصيد المخزن؟`)) return;

    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const idx = allSales.findIndex(i => String(i.invoiceNo || i.id) === invNo);
    if (idx === -1) return;

    const inv = allSales[idx];
    const whId = inv.deliveredWarehouse || inv.warehouseId || inv.warehouse || 1;
    let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
    let erpItems = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');

    (inv.items || []).forEach(it => {
        const retQty = parseFloat(it.deliveredQty || it.qty || it.quantity || 0);
        it.deliveredQty = 0;
        const meta = resolveGateItemMeta(it);

        let invEntry = inventory.find(x =>
            (String(x.id || x.itemId) === String(it.itemId || it.id) || String(x.name || '').trim() === meta.name.trim()) &&
            String(x.warehouseId || x.warehouse || 1) === String(whId)
        );
        if (invEntry) invEntry.qty = (parseFloat(invEntry.qty || 0) + retQty);

        const mIdx = erpItems.findIndex(x =>
            String(x.id) === String(it.itemId || it.id) || String(x.name || '').trim() === meta.name.trim()
        );
        if (mIdx !== -1) {
            if (!erpItems[mIdx].stock) erpItems[mIdx].stock = {};
            if (!erpItems[mIdx].actualStock) erpItems[mIdx].actualStock = {};
            erpItems[mIdx].stock[whId] = (parseFloat(erpItems[mIdx].stock[whId]) || 0) + retQty;
            erpItems[mIdx].actualStock[whId] = (parseFloat(erpItems[mIdx].actualStock[whId]) || 0) + retQty;
            if (erpItems[mIdx].quantity !== undefined) {
                erpItems[mIdx].quantity = (parseFloat(erpItems[mIdx].quantity) || 0) + retQty;
            }
        }
    });

    inv.isDelivered = false;
    inv.deliveryStatus = 'pending';
    inv.status = 'pending_delivery';
    allSales[idx] = inv;

    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));
    localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
    localStorage.setItem('erp_items_v1', JSON.stringify(erpItems));

    let delNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1') || '[]');
    delNotes = delNotes.filter(n => String(n.invoiceNo || n.relatedInvoice) !== invNo);
    localStorage.setItem('erp_delivery_notes_v1', JSON.stringify(delNotes));

    currentLoadedInvoice = inv;
    renderInvoiceDetails(true);
    renderGrid();
    renderGateRegistryTable();
    showToast(`↩️ تم إلغاء صرف الإذن #${invNo} وإعادة الكميات للمخزن بنجاح`, 'warning');
}

// ── PRINT OFFICIAL GATE PASS & WHATSAPP SHARE ──
function printCurrentGatePass() {
    if (!currentLoadedInvoice) {
        showToast('⚠️ اختر فاتورة أولاً لطباعة إذن المنصرف', 'warning');
        return;
    }
    const inv = currentLoadedInvoice;
    const invNo = inv.invoiceNo || inv.id;
    const custName = resolveGateCustomerName(inv);
    const receiverName = document.getElementById('gate-receiver-name')?.value || inv.receiverName || custName;
    const driverName = document.getElementById('gate-driver-name')?.value || inv.driverName || '---';
    const truckPlate = document.getElementById('gate-truck-plate')?.value || inv.truckPlate || '---';
    const gatePassNo = document.getElementById('gate-pass-no')?.value || inv.gatePassNo || 'GP-AUTO';
    const qrTargetUrl = `${window.location.origin}/direct-sales.php?action=delivery&qr_deliver=${encodeURIComponent(invNo)}&auto_deduct=1`;
    const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=4&data=${encodeURIComponent(qrTargetUrl)}`;

    const rowsHtml = (inv.items || []).map((it, idx) => {
        const m = resolveGateItemMeta(it);
        const q = parseFloat(it.deliveredQty || it.qty || it.quantity || 0) || 0;
        const baseUnitsVal = +(q * m.factor).toFixed(2);
        return `
            <tr>
                <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:800;">${idx + 1}</td>
                <td style="padding:8px; border:1px solid #0f172a; font-weight:900;">${m.name}</td>
                <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:900;">${q} ${m.unit}</td>
                <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:900;">${m.hasMultiUnits ? `${baseUnitsVal.toLocaleString('en-US')} ${m.baseUnit} <span style="font-size:0.78rem; color:#475569;">(1 ${m.unit} = ${m.factor} ${m.baseUnit})</span>` : `${q} ${m.baseUnit}`}</td>
            </tr>
        `;
    }).join('');

    const w = window.open('', '_blank', 'width=850,height=700');
    if (!w) return;
    w.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>إذن استلام وتسليم بضاعة (Gate Pass) - #${invNo}</title>
            <style>
                body { font-family: 'Cairo', 'Tajawal', sans-serif; padding: 24px; color: #000; }
                .header { display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #000; padding-bottom:14px; margin-bottom:18px; }
                table { width:100%; border-collapse:collapse; margin-top:14px; }
                th { background:#064e3b; color:#fff; padding:10px; border:1px solid #000; }
            </style>
        </head>
        <body>
            <div class="header">
                <div>
                    <h2 style="margin:0; font-size:1.5rem;">🚚 إذن استلام وتسليم بضاعة رسمي (GDN / Gate Pass)</h2>
                    <p style="margin:6px 0 0; font-weight:800;">رقم الفاتورة: #${invNo} &nbsp;|&nbsp; رقم إذن الصرف: ${inv.deliveryNo || 'GDN'} &nbsp;|&nbsp; تصريح البوابة: ${gatePassNo}</p>
                    <p style="margin:4px 0 0; font-weight:800;">👤 العميل: ${custName} &nbsp;|&nbsp; المستلم الفعلي: ${receiverName}</p>
                    <p style="margin:4px 0 0; font-weight:800;">🚚 السائق: ${driverName} &nbsp;|&nbsp; 🔢 رقم اللوحة: ${truckPlate}</p>
                </div>
                <div style="text-align:center;">
                    <img src="${qrImgUrl}" style="width:120px; height:120px; border:2px solid #000; border-radius:10px;">
                    <div style="font-size:0.72rem; font-weight:900; margin-top:3px;">رمز التحقق الأمني للبوابة</div>
                </div>
            </div>
            <table>
                <thead>
                    <tr><th>#</th><th>اسم الصنف</th><th>الكمية بالوحدة (الإذن)</th><th>عدد الوحدات الفعلية</th></tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <div style="margin-top:16px; padding:10px; border:1px dashed #000; font-weight:800;">
                ملاحظات المخزن والبوابة: ${document.getElementById('warehouse-notes')?.value || inv.warehouseNotes || 'تم فحص ومطابقة الأصناف بالكامل'}
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:45px; font-weight:900;">
                <div>توقيع أمين المخزن: ________________</div>
                <div>توقيع السائق / المستلم: ________________</div>
                <div>ختم أمن البوابة (L4): ________________</div>
            </div>
            <script>window.onload = () => { setTimeout(() => window.print(), 350); };<\/script>
        </body>
        </html>
    `);
    w.document.close();
}

function shareCurrentGateWhatsApp() {
    if (!currentLoadedInvoice) {
        showToast('⚠️ اختر فاتورة أولاً لمشاركة إذن التسليم عبر واتساب', 'warning');
        return;
    }
    const inv = currentLoadedInvoice;
    const invNo = inv.invoiceNo || inv.id;
    const custName = resolveGateCustomerName(inv);
    const itemsList = (inv.items || []).map(i => {
        const m = resolveGateItemMeta(i);
        const q = parseFloat(i.deliveredQty || i.qty || i.quantity || 0) || 0;
        const eqStr = m.hasMultiUnits ? ` (تعادل ${(q * m.factor).toLocaleString('en-US', { maximumFractionDigits: 2 })} ${m.baseUnit})` : '';
        return `• ${m.name}: ${q} ${m.unit}${eqStr}`;
    }).join('\n');
    const msg = `🚚 *إذن استلام وتسليم بضاعة رسمي*\n📄 رقم الفاتورة: #${invNo}\n🏷️ رقم إذن الصرف: ${inv.deliveryNo || 'قيد الصرف'}\n👤 العميل: ${custName}\n🤝 المستلم: ${document.getElementById('gate-receiver-name')?.value || custName}\n📦 *الأصناف والوحدات:*\n${itemsList}\n✅ الحالة: ${inv.isDelivered ? 'تم الصرف والخروج من المخزن' : 'جاهز للتسليم'}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
}
