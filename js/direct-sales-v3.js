
function extractItemUnits(item) {
    if (!item) return [];
    const baseUnit = (item.baseUnit || item.mainUnit || item.unit || "سيخه").trim();
    let list = [];

    // Parse units if string
    if (typeof item.units === 'string') {
        try { item.units = JSON.parse(item.units); } catch(e) {}
    }
    if (typeof item.unitsArray === 'string') {
        try { item.unitsArray = JSON.parse(item.unitsArray); } catch(e) {}
    }

    // 1. From item.units (Matrix format from master-data)
    if (Array.isArray(item.units) && item.units.length > 0) {
        item.units.forEach(u => {
            if (!u) return;
            const uName = (u.unitName || u.name || u.unit || "").trim();
            const uFactor = (u.factor !== undefined && u.factor !== null && u.factor !== "") ? parseFloat(u.factor) : 1;
            const uPrice = parseFloat(u.cashPrice || u.price || item.sellingPrice || item.price || 0) || 0;
            if (uName && !list.find(x => x.name === uName)) {
                list.push({ name: uName, unitName: uName, factor: isNaN(uFactor) ? 1 : uFactor, price: uPrice, barcode: u.barcode || '' });
            }
        });
    }

    // 2. From item.unitsArray
    if (Array.isArray(item.unitsArray) && item.unitsArray.length > 0) {
        item.unitsArray.forEach(u => {
            if (!u) return;
            const uName = (typeof u === "string" ? u : (u.name || u.unitName || u.unit || "")).trim();
            const uFactor = (u && typeof u === 'object' && u.factor !== undefined && u.factor !== null && u.factor !== "") ? parseFloat(u.factor) : 1;
            const uPrice = (u && typeof u === 'object') ? parseFloat(u.price || u.cashPrice || item.sellingPrice || item.price || 0) || 0 : 0;
            if (uName && !list.find(x => x.name === uName)) {
                list.push({ name: uName, unitName: uName, factor: isNaN(uFactor) ? 1 : uFactor, price: uPrice, barcode: (u && u.barcode) || '' });
            }
        });
    }

    // 3. Specific fallback only for Steel / Linya items if list has <= 1 unit
    const normName = (item.name || '').toLowerCase();
    const isSteel = normName.includes('سيخ') || normName.includes('لينيا') || normName.includes('حديد') || normName.includes('تسليح') || normName.includes('ل');
    if (isSteel && list.length <= 1) {
        let fallbackMatrix = null;
        if (normName.includes('3') || normName.includes('ثلاث')) {
            fallbackMatrix = [
                { name: 'سيخه', unitName: 'سيخه', factor: 1, price: 0 },
                { name: 'طن عددي', unitName: 'طن عددي', factor: 135, price: 0 },
                { name: 'طن وزني', unitName: 'طن وزني', factor: 166, price: 0 },
                { name: 'شيله', unitName: 'شيله', factor: 15, price: 0 }
            ];
        } else if (normName.includes('4') || normName.includes('اربع') || normName.includes('أربع')) {
            fallbackMatrix = [
                { name: 'سيخه', unitName: 'سيخه', factor: 1, price: 0 },
                { name: 'طن عددي', unitName: 'طن عددي', factor: 92, price: 0 },
                { name: 'طن وزني', unitName: 'طن وزني', factor: 110, price: 0 },
                { name: 'شيله', unitName: 'شيله', factor: 10, price: 0 }
            ];
        } else if (normName.includes('5') || normName.includes('خمس')) {
            fallbackMatrix = [
                { name: 'سيخه', unitName: 'سيخه', factor: 1, price: 0 },
                { name: 'طن عددي', unitName: 'طن عددي', factor: 52, price: 0 },
                { name: 'طن وزني', unitName: 'طن وزني', factor: 59, price: 0 },
                { name: 'شيله', unitName: 'شيله', factor: 5, price: 0 }
            ];
        }

        if (fallbackMatrix) {
            fallbackMatrix.forEach(fb => {
                if (!list.find(x => x.name === fb.name)) {
                    list.push(fb);
                }
            });
        }
    }

    // 4. Ensure base unit is present
    if (baseUnit && !list.find(x => x.name === baseUnit)) {
        list.unshift({ name: baseUnit, unitName: baseUnit, factor: 1, price: parseFloat(item.sellingPrice || item.price || 0) || 0, barcode: item.barcode || '' });
    }

    if (list.length === 0) {
        list = [{ name: baseUnit || "قطعة", unitName: baseUnit || "قطعة", factor: 1, price: parseFloat(item.sellingPrice || item.price || 0) || 0, barcode: item.barcode || '' }];
    }

    return list;
}
/* ═══════════════════════════════════════════════════════
   sales-invoice.js — POS Engine v6.0
   Real-time Reactive Calculations � Sequential Invoices
   ═══════════════════════════════════════════════════════ */

// Global logout function (used by sidebar)
// Global logout is handled by app.js

// ════════════════════════════════════════
//  MOCK DATA (Replace with API calls)
// ════════════════════════════════════════

// Safe localStorage wrapper to prevent crashes when JSON.parse is called on object/array values
const originalGetItem = localStorage.getItem.bind(localStorage);
localStorage.getItem = function(key) {
    let val = originalGetItem(key);
    if (val !== null && typeof val === 'object') {
        return JSON.stringify(val);
    }
    return val;
};

let WAREHOUSES = JSON.parse(localStorage.getItem('erp_warehouses_v1'));
if (!WAREHOUSES || WAREHOUSES.length === 0) {
    WAREHOUSES = [{ id:'w-main', name:'المخزن الرئيسي' }];
}

const COST_CENTERS = [
    { id:'cc-001', name:'الفرع الرئيسي' },
    { id:'cc-002', name:'فرع بحري' },
    { id:'cc-003', name:'فرع أمدرمان' },
];

let CUSTOMERS = [
    { id:'walk-in', name:'زبون نقدي (Walk-in)', ledger:0, checks:0, creditLimit:0, status:'active' },
];

// Load real customer data from master-data
// (Moved to DBReady event)

let UOMS = JSON.parse(localStorage.getItem('erp_uoms_v1')) || [
    {code: 'TON', name: 'طن'},
    {code: 'BAG', name: 'كيس'},
    {code: 'REBAR', name: 'سيخة / عود'},
    {code: 'PCS', name: 'حبة / قطعة'},
    {code: 'GAL', name: 'جالون'}
];

let ITEMS = [];

// ════════════════════════════════════════
//  STATE
// ════════════════════════════════════════
let rows = [];
let rowIdCounter = 0;
let currentSaleType = 'direct';
let currentViewedInvoiceNo = null;

window._cachedSalesInvoicesRaw = null;
window._cachedSalesSorted = null;

function getSortedSalesInvoices() {
    const raw = localStorage.getItem('erp_sales_invoices_v1');
    if (window._cachedSalesInvoicesRaw === raw && window._cachedSalesSorted) {
        return window._cachedSalesSorted;
    }
    let allSales = [];
    try { allSales = JSON.parse(raw) || []; } catch(e) {}
    
    const sorted = allSales
        .filter(inv => inv.invoiceNo && inv.invoiceNo !== 'preliminary' && !String(inv.invoiceNo).startsWith('PRE-'))
        .sort((a, b) => {
            const cmp = (a.date || a.createdAt || '').localeCompare(b.date || b.createdAt || '');
            if (cmp !== 0) return cmp;
            const tcmp = (a.time || '').localeCompare(b.time || '');
            if (tcmp !== 0) return tcmp;
            return String(a.invoiceNo || '').localeCompare(String(b.invoiceNo || ''));
        });
        
    window._cachedSalesInvoicesRaw = raw;
    window._cachedSalesSorted = sorted;
    return sorted;
}

// ════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════
function fmtNum(n, colId = null) {
    const cfg = window.CurrentGridConfig || {};
    let decimals = (cfg.decimalPlaces !== undefined && cfg.decimalPlaces !== '' && cfg.decimalPlaces !== null) ? parseInt(cfg.decimalPlaces) : 2;
    let separator = (cfg.thousandSep !== undefined && cfg.thousandSep !== '' && cfg.thousandSep !== null && cfg.thousandSep !== 'default') ? cfg.thousandSep : ',';
    let decimalSep = cfg.decimalSep !== undefined ? cfg.decimalSep : '.';

    if (colId && Array.isArray(cfg.columns)) {
        const colCfg = cfg.columns.find(c => String(c.id) === String(colId));
        if (colCfg) {
            if (colCfg.decimals !== undefined && colCfg.decimals !== '' && colCfg.decimals !== null) decimals = parseInt(colCfg.decimals);
            if (colCfg.thousandSep !== undefined && colCfg.thousandSep !== '' && colCfg.thousandSep !== null && colCfg.thousandSep !== 'default') separator = colCfg.thousandSep;
        }
    } else if (colId && cfg[colId]) {
        const colCfg = cfg[colId];
        if (colCfg.decimals !== undefined && colCfg.decimals !== '' && colCfg.decimals !== null) decimals = parseInt(colCfg.decimals);
        if (colCfg.thousandSep !== undefined && colCfg.thousandSep !== '' && colCfg.thousandSep !== null && colCfg.thousandSep !== 'default') separator = colCfg.thousandSep;
    }

    if (separator === 'default') separator = ',';

    if (n === 0 || n === '0' || !n) {
        if (n === 0 || n === '0') {
            return decimals > 0 ? (0).toFixed(decimals) : '0';
        }
        return decimals > 0 ? (0).toFixed(decimals) : '0';
    }

    let val = parseFloat(n);
    if (isNaN(val)) val = 0;

    let numStr = val.toFixed(decimals);
    let parts = numStr.split('.');
    let intPart = parts[0];
    let decPart = parts.length > 1 ? decimalSep + parts[1] : '';

    if (separator !== 'none' && separator !== '') {
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, separator);
    }

    return intPart + decPart;
}
function parseNum(str) {
    return parseFloat(String(str).replace(/,/g, '')) || 0;
}
function showToast(msg, type = 'success', ms = 3000) {
    const c = document.getElementById('toast-container');
    if (c) {
        const existingToasts = c.querySelectorAll('.toast');
        for (let t of existingToasts) {
            if (t.innerHTML === msg) return;
        }
    }
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = msg;
    c.appendChild(t);
    setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 500); }, ms);
}
function openModal(id) { document.getElementById(id).classList.add('visible'); }
function closeModal(id) { document.getElementById(id).classList.remove('visible'); }

// ════════════════════════════════════════
//  SEQUENTIAL INVOICE NUMBER
// ════════════════════════════════════════
const SALE_PREFIX = { direct:'', split:'', delivery:'', proforma:'', costcenter:'' };

// Peek: show the next number WITHOUT incrementing (safe for refresh)
function peekNextInvoiceNumber(type) {
    let list = getSortedSalesInvoices();
    
    if (type === 'preliminary') {
        let prefix = 'PRE-';
        let max = 0;
        list.forEach(inv => {
            if (inv.saleType === 'preliminary' || (inv.invoiceNo && inv.invoiceNo.startsWith('PRE-'))) {
                let parts = (inv.invoiceNo || '').split('-');
                let noStr = parts.length > 1 ? parts[1] : parts[0];
                let no = parseInt(noStr || '0', 10);
                if (!isNaN(no) && no > max) {
                    max = no;
                }
            }
        });
        return `${prefix}${String(max + 1).padStart(6, '0')}`;
    } else {
        const now = new Date();
        const yy = String(now.getFullYear()).substring(2, 4);
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const datePrefix = 'S' + yy + mm;
        
        let max = 0;
        list.forEach(inv => {
            if (inv.saleType !== 'preliminary' && (!inv.invoiceNo || !inv.invoiceNo.startsWith('PRE-'))) {
                const invNo = String(inv.invoiceNo || '');
                if (invNo.startsWith(datePrefix)) {
                    const seqStr = invNo.substring(datePrefix.length);
                    const seq = parseInt(seqStr, 10);
                    if (!isNaN(seq) && seq > max) {
                        max = seq;
                    }
                } else if (invNo.includes('-')) {
                    // Fallback to match old format containing '-' if it matches current year/month
                    let parts = invNo.split('-');
                    let noStr = parts.length > 1 ? parts[1] : parts[0];
                    if (noStr.startsWith(yy + mm)) {
                        const seq = parseInt(noStr.substring(4), 10);
                        if (!isNaN(seq) && seq > max) {
                            max = seq;
                        }
                    }
                }
            }
        });
        return `${datePrefix}${String(max + 1).padStart(4, '0')}`;
    }
}

// Commit: actually increment the counter (only on real save/submit)
function commitInvoiceNumber(type) {
    return peekNextInvoiceNumber(type);
}

// ════════════════════════════════════════
//  INIT
// ════════════════════════════════════════
function initSalesApp() {
    // ────── MIGRATE OLD INVOICE NUMBERS TO S+YYMM+SEQ ──────
    try {
        let allInv = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
        const sRx = /^S\d{8}$/;
        let hasOld = false;
        for (const inv of allInv) {
            const no = String(inv.invoiceNo || '');
            if (no && no !== 'preliminary' && !no.startsWith('PRE-') && !sRx.test(no)) {
                hasOld = true; break;
            }
        }
        if (hasOld) {
            const byMonth = {};
            allInv.forEach(inv => {
                const no = String(inv.invoiceNo || '');
                if (no === 'preliminary' || no.startsWith('PRE-')) return;
                const d = (inv.date || inv.createdAt) ? new Date(inv.date || inv.createdAt) : new Date();
                const vd = isNaN(d.getTime()) ? new Date() : d;
                const yymm = String(vd.getFullYear()).substring(2, 4) + String(vd.getMonth() + 1).padStart(2, '0');
                if (!byMonth[yymm]) byMonth[yymm] = { s: [], old: [] };
                if (sRx.test(no)) { byMonth[yymm].s.push(inv); } else { byMonth[yymm].old.push(inv); }
            });
            const idMap = {};
            for (const yymm of Object.keys(byMonth)) {
                const { s, old } = byMonth[yymm];
                if (old.length === 0) continue;
                const dp = 'S' + yymm;
                let mx = 0;
                s.forEach(inv => { const sq = parseInt(String(inv.invoiceNo).substring(5), 10); if (!isNaN(sq) && sq > mx) mx = sq; });
                old.sort((a, b) => { const c = (a.date||a.createdAt||'').localeCompare(b.date||b.createdAt||''); return c !== 0 ? c : (a.time||'').localeCompare(b.time||''); });
                old.forEach(inv => {
                    mx++;
                    const oldNo = inv.invoiceNo;
                    const newNo = dp + String(mx).padStart(4, '0');
                    idMap[oldNo] = newNo;
                    inv.invoiceNo = newNo;
                    if (inv.id === oldNo) inv.id = newNo;
                    // Migrate detail keys
                    ['erp_inv_', 'erp_draft_'].forEach(pfx => {
                        const oldData = localStorage.getItem(pfx + oldNo);
                        if (oldData) {
                            try {
                                const p = JSON.parse(oldData);
                                p.invoiceNo = newNo;
                                if (p.id === oldNo) p.id = newNo;
                                localStorage.setItem(pfx + newNo, JSON.stringify(p));
                                localStorage.removeItem(pfx + oldNo);
                            } catch(e) {}
                        }
                    });
                });
            }
            if (Object.keys(idMap).length > 0) {
                localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allInv));
                console.log('[initSalesApp Migration] Converted old invoice numbers:', idMap);
            }
        }
    } catch(e) {
        console.error('[initSalesApp Migration] Error:', e);
    }
    // ────── END MIGRATION ──────
    try {
        // Reload WAREHOUSES from localStorage
        let localWH = [];
        try {
            localWH = JSON.parse(localStorage.getItem('erp_warehouses_v1'));
        } catch(e) { console.error(e); }
        WAREHOUSES = (Array.isArray(localWH) && localWH.length > 0) ? localWH : [{ id:'w-main', name:'المخزن الرئيسي' }];

        // Load real customer data from master-data
        CUSTOMERS = [{ id:'walk-in', name:'زبون نقدي (Walk-in)', ledger:0, checks:0, creditLimit:0, status:'active' }];
        let localCusts = [];
        try {
            localCusts = JSON.parse(localStorage.getItem('erp_customers_v1'));
        } catch(e) { console.error(e); }
        
        if(Array.isArray(localCusts) && localCusts.length > 0) {
            localCusts.forEach(c => {
                if (c && c.id) {
                    CUSTOMERS.push({
                        id: c.id,
                        name: c.name || 'عميل بدون اسم',
                        phone: c.phone || '',
                        email: c.email || '',
                        ledger: Math.abs(c.currentBalance < 0 ? c.currentBalance : 0) || 0,
                        checks: 0,
                        creditLimit: c.creditLimit || 0,
                        status: c.status || 'active'
                    });
                }
            });
        }

                // Load Master Data Items
        let rawItems = [];
        let invData = [];
        try {
            if (window._PRELOADED_ITEMS && Array.isArray(window._PRELOADED_ITEMS) && window._PRELOADED_ITEMS.length > 0) {
                rawItems = window._PRELOADED_ITEMS;
            } else {
                rawItems = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            }
            invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        } catch(e) { console.error(e); }
        
        ITEMS = [];
        if (Array.isArray(rawItems)) {
            rawItems.forEach(item => {
                if (!item || !item.id) return;
                
                let actualStockMap = {};
                let reservedStockMap = {};
                
                if (Array.isArray(invData)) {
                    let itemInv = invData.filter(inv => inv && inv.itemId === item.id);
                    itemInv.forEach(inv => {
                        if (!inv) return;
                        let wh = WAREHOUSES.find(w => w && w.name === inv.warehouse);
                        let whId = wh ? wh.id : 'w-main';
                        actualStockMap[whId] = (actualStockMap[whId] || 0) + (parseFloat(inv.qty) || parseFloat(inv.quantity) || 0);
                        reservedStockMap[whId] = (reservedStockMap[whId] || 0) + (parseFloat(inv.reservedQuantity) || 0);
                    });
                }

                // Clean price
                let sp = item.sellingPrice || item.salePrice || item.price || 0;
                sp = sp.toString().replace(/,/g, '');
                
                const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'قطعة';
                const unitsArray = extractItemUnits(item);
                ITEMS.push({
                    id: item.id,
                    name: item.name || 'صنف بدون اسم',
                    unit: baseUnit,
                    baseUnit: baseUnit,
                    conversionFactor: 1, 
                    bundleFactor: parseFloat(item.bundleFactor) || 1,
                    price: parseFloat(sp) || 0,
                    actualStock: actualStockMap,
                    reservedStock: reservedStockMap,
                    units: item.units || [],
                    unitsArray: unitsArray,
                    barcode: item.barcode || '',
                    maxDiscount: item.maxDiscount || 0,
                    status: item.status || 'active'
                });
            });
        }
        ITEMS = ITEMS.filter(item => item && item.status !== 'inactive');
        console.log('[initSalesApp] Loaded ' + ITEMS.length + ' items. Items with >1 units:', ITEMS.filter(i => i.unitsArray && i.unitsArray.length > 1).length);
        ITEMS.slice(0, 3).forEach(i => console.log('  Item: ' + i.name + ' → ' + (i.unitsArray||[]).map(u=>u.name).join(', ')));


                populateDropdowns();
        populatePaymentAccounts();
        initMultiPaymentSystem();
        initHeader();

        if (rows.length === 0) {
            addRow(true);
            recalcAll();
        } else {
            renderGrid();
        }
        if (typeof loadHubTransactions === 'function') loadHubTransactions();

        // Background sync latest items & units from MySQL unconditionally
        fetch('api/get_master_data.php').then(r => r.json()).then(res => {
            if (res && res.success && res.data) {
                if (Array.isArray(res.data.units) && res.data.units.length > 0) {
                    localStorage.setItem('erp_global_units_v1', JSON.stringify(res.data.units));
                }
                if (Array.isArray(res.data.items) && res.data.items.length > 0) {
                    localStorage.setItem('erp_items_v1', JSON.stringify(res.data.items));
                    res.data.items.forEach(dbItem => {
                        let existing = ITEMS.find(i => String(i.id) === String(dbItem.id));
                        const baseUnit = dbItem.baseUnit || dbItem.mainUnit || dbItem.unit || 'سيخه';
                        const unitsArr = extractItemUnits(dbItem);
                        if (!existing) {
                            ITEMS.push({
                                id: dbItem.id,
                                name: dbItem.name || 'صنف',
                                unit: baseUnit,
                                baseUnit: baseUnit,
                                conversionFactor: 1,
                                bundleFactor: parseFloat(dbItem.bundleFactor) || 1,
                                price: parseFloat(dbItem.sellingPrice || dbItem.price || 0) || 0,
                                actualStock: {},
                                reservedStock: {},
                                units: dbItem.units || [],
                                unitsArray: unitsArr,
                                maxDiscount: dbItem.maxDiscount || 0,
                                status: dbItem.status || 'active'
                            });
                        } else {
                            existing.units = dbItem.units || existing.units;
                            existing.unitsArray = unitsArr;
                        }
                    });
                    renderGrid();
                }
            }
        }).catch(err => console.error('[Master Data Sync Error]:', err));
    } catch(globalErr) {
        console.error("Critical error during initSalesApp:", globalErr);
    }
}

if (window.APP_READY) {
    initSalesApp();
} else {
    document.addEventListener('DBReady', initSalesApp);
}
window.addEventListener('cloudDataRestored', initSalesApp);
window.addEventListener('strictDataReady', initSalesApp);

function populateDropdowns() {
    try {
        const custInput = document.getElementById('hdr-customer');
        if (custInput) custInput.value = '';
        
        const whSel = document.getElementById('hdr-warehouse');
        if (whSel) {
            whSel.innerHTML = '';
            WAREHOUSES.forEach(w => {
                if (w) {
                    const o = document.createElement('option');
                    o.value = w.id; o.textContent = w.name;
                    whSel.appendChild(o);
                }
            });
        }
        
        togglePaymentAccount();
    } catch (e) {
        console.error("Error in populateDropdowns:", e);
    }
}

function togglePaymentAccount() {
    try {
        const methodEl = document.getElementById('hdr-payment-method');
        if (!methodEl) return;
        const method = methodEl.value;
        const detailsRow = document.getElementById('payment-details-row');
        const lbl1 = document.getElementById('pay-detail-1-label');
        const lbl2 = document.getElementById('pay-detail-2-label');
        const lbl3 = document.getElementById('pay-detail-3-label');
        
        if (method === 'cheque' || method === 'bank') {
            if (detailsRow) {
                detailsRow.style.display = 'flex';
                detailsRow.style.animation = 'none';
                requestAnimationFrame(() => {
                    detailsRow.style.animation = 'fadeSlideIn 0.35s cubic-bezier(0.16,1,0.3,1) both';
                });
            }
            
            if (method === 'cheque') {
                if (lbl1) lbl1.innerText = 'رقم الشيك';
                if (lbl2) lbl2.innerText = 'اسم البنك';
                if (lbl3) lbl3.innerText = 'الفرع';
            } else if (method === 'bank') {
                if (lbl1) lbl1.innerText = 'رقم التحويل';
                if (lbl2) lbl2.innerText = 'البنك المحول إليه';
                if (lbl3) lbl3.innerText = 'الفرع / المرجع';
            }
        } else {
            if (detailsRow) detailsRow.style.display = 'none';
        }

        const select = document.getElementById('hdr-payment-account');
        if (select) {
            if (typeof Choices !== 'undefined') {
                if (!window.paymentAccountChoices) {
                    window.paymentAccountChoices = new Choices(select, { searchEnabled: true, itemSelectText: '', shouldSort: false });
                }
                
                if (method === 'credit') {
                    window.paymentAccountChoices.setChoices([{ value: '', label: '-- آجل (على الحساب) --' }], 'value', 'label', true);
                    window.paymentAccountChoices.disable();
                } else {
                    window.paymentAccountChoices.enable();
                    populateAccountSelect(select);
                }
            } else {
                // Fallback to normal select when Choices.js is not loaded
                if (method === 'credit') {
                    select.innerHTML = '<option value="">-- آجل (على الحساب) --</option>';
                    select.disabled = true;
                } else {
                    select.disabled = false;
                    populateAccountSelectFallback(select);
                }
            }
        }
    } catch (e) {
        console.error("Error in togglePaymentAccount:", e);
    }
}

function populateAccountSelect(select) {
    try {
        let safes = [];
        let banks = [];
        try {
            safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
            banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        } catch(e) {}
        
        safes = safes.filter(x => x && !x.deleted);
        banks = banks.filter(x => x && !x.deleted);
        
        let choicesArr = [];
        safes.forEach(s => {
            choicesArr.push({ value: s.id, label: '💰 ' + s.name });
        });
        banks.forEach(b => {
            choicesArr.push({ value: b.id, label: '🏦 ' + b.name });
        });
        
        if (choicesArr.length === 0) {
            choicesArr.push({ value: '', label: '-- لا توجد حسابات مالية --' });
        }
        
        if (window.paymentAccountChoices) {
            window.paymentAccountChoices.setChoices(choicesArr, 'value', 'label', true);
        }
    } catch (e) {
        console.error("Error in populateAccountSelect:", e);
    }
}

function populateAccountSelectFallback(select) {
    try {
        let safes = [];
        let banks = [];
        try {
            safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
            banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        } catch(e) {}
        
        safes = safes.filter(x => x && !x.deleted);
        banks = banks.filter(x => x && !x.deleted);
        
        let html = '';
        safes.forEach(s => {
            html += `<option value="${s.id}">💰 ${s.name}</option>`;
        });
        banks.forEach(b => {
            html += `<option value="${b.id}">🏦 ${b.name}</option>`;
        });
        
        if (!html) {
            html = '<option value="">-- لا توجد حسابات مالية --</option>';
        }
        select.innerHTML = html;
    } catch (e) {
        console.error("Error in populateAccountSelectFallback:", e);
    }
}

function syncWarehouse(el) {
    // مزامنة المخزن بين الأزرة المختلفة
    const val = el.value;
    ['hdr-warehouse','hdr-warehouse-credit','hdr-warehouse-bank','hdr-warehouse-cheque'].forEach(id => {
        const s = document.getElementById(id);
        if (s && s !== el) s.value = val;
    });
    onMainWarehouseChange();
}


function initHeader() {
    // Invoice number — peek only (no increment)
    const invNo = peekNextInvoiceNumber(currentSaleType);
    document.getElementById('inv-number').textContent = invNo;
    document.getElementById('hdr-inv-no').value = invNo;

    // Date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('hdr-date').value = today;
}

// ════════════════════════════════════════
//  SALE TYPE TABS
// ════════════════════════════════════════
function setSaleType(btn, type) {
    currentSaleType = type;
    document.querySelectorAll('.action-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    const invNo = peekNextInvoiceNumber(type);
    document.getElementById('inv-number').textContent = invNo;
    document.getElementById('hdr-inv-no').value = invNo;
}

// ════════════════════════════════════════
//  CUSTOMER CHANGE → Financial Panel
// ════════════════════════════════════════
function onCustomerChange() {
    const id = document.getElementById('hdr-customer').value;
    const cust = CUSTOMERS.find(c => String(c.id) === String(id));
    const cur = document.getElementById('hdr-currency').value;
    const strip = document.getElementById('fin-strip');
    const badge = document.getElementById('fin-status-badge');

    if (!cust || id === '') {
        ['fin-ledger','fin-checks','fin-total-debt','fin-credit-limit','fin-remaining'].forEach(el => {
            const dom = document.getElementById(el);
            if (dom) dom.textContent = '—';
        });
        if (strip) strip.classList.remove('visible');
        if (badge) badge.style.display = 'none';
        return;
    }

    const totalDebt = cust.ledger + cust.checks;
    const remaining = cust.creditLimit - totalDebt;

    document.getElementById('fin-ledger').textContent      = fmtNum(cust.ledger)      + ' ' + cur;
    document.getElementById('fin-checks').textContent      = fmtNum(cust.checks)      + ' ' + cur;
    document.getElementById('fin-total-debt').textContent  = fmtNum(totalDebt)        + ' ' + cur;
    document.getElementById('fin-credit-limit').textContent= fmtNum(cust.creditLimit) + ' ' + cur;
    document.getElementById('fin-remaining').textContent   = fmtNum(remaining)        + ' ' + cur;

    // إظهار الشريط المالي الأفقي بأنيميشن
    if (strip) {
        // أعد تشغيل الأنيميشن بإزالة وإعادة الكلاس
        strip.classList.remove('visible');
        requestAnimationFrame(() => requestAnimationFrame(() => strip.classList.add('visible')));
    }

    // badge الحالة بجانب عنوان اللوحة
    if (badge) {
        const txtEl = document.getElementById('fin-status-txt');
        const dot = badge.querySelector('.status-dot');
        if (cust.status === 'blacklisted') {
            if (dot) dot.className = 'status-dot danger';
            if (txtEl) txtEl.textContent = '⛔ قائمة سوداء';
            showToast('تحذير: هذا العميل في القائمة السوداء!', 'error', 5000);
        } else if (remaining > 20000) {
            if (dot) dot.className = 'status-dot good';
            if (txtEl) txtEl.textContent = '✅ ائتمان جيد';
        } else if (remaining > 0) {
            if (dot) dot.className = 'status-dot warn';
            if (txtEl) txtEl.textContent = '⚠️ ائتمان منخفض';
        } else {
            if (dot) dot.className = 'status-dot danger';
            if (txtEl) txtEl.textContent = '🚫 تجاوز الحد';
        }
        badge.style.display = 'inline-flex';
    }
}

function toggleFinancialPanel() { /* deprecated - kept for compatibility */ }
function expandFinancialPanel()  { onCustomerChange(); }
function collapseFinancialPanel() {
    const strip = document.getElementById('fin-strip');
    if (strip) strip.classList.remove('visible');
}

function addNewCustomer() {
    const name = document.getElementById('new-cust-name').value.trim();
    const phone = document.getElementById('new-cust-phone').value.trim();
    const whatsapp = document.getElementById('new-cust-whatsapp') ? document.getElementById('new-cust-whatsapp').value.trim() : '';
    const email = document.getElementById('new-cust-email') ? document.getElementById('new-cust-email').value.trim() : '';
    const address = document.getElementById('new-cust-address') ? document.getElementById('new-cust-address').value.trim() : '';
    const securityLevel = document.getElementById('new-cust-security') ? document.getElementById('new-cust-security').value : '1';
    const openingBalance = document.getElementById('new-cust-opening') ? parseNum(document.getElementById('new-cust-opening').value) : 0;
    const credit = document.getElementById('new-cust-credit') ? parseNum(document.getElementById('new-cust-credit').value) : 0;

    if (!name) { showToast('❌ يرجى إدخال اسم العميل', 'error'); return; }

    const newId = 'C-' + String(Date.now()).slice(-6);
    const openingDebit = openingBalance >= 0 ? openingBalance : 0;
    const openingCredit = openingBalance < 0 ? Math.abs(openingBalance) : 0;
    const newCust = { 
        id: newId, 
        name: name, 
        phone: phone,
        whatsapp: whatsapp,
        email: email,
        address: address,
        securityLevel: securityLevel,
        openingDebit: openingDebit,
        openingCredit: openingCredit,
        currentBalance: openingBalance,
        creditLimit: credit,
        status: 'active',
        bankAccounts: [],
        timestamp: new Date().toISOString()
    };
    
    if (typeof CUSTOMERS !== 'undefined') {
        CUSTOMERS.push(newCust);
    }

    let allCustomers = [];
    try {
        allCustomers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
    } catch(e) {}
    allCustomers.push(newCust);
    localStorage.setItem('erp_customers_v1', JSON.stringify(allCustomers));

    selectCustomer(newId, name);
    closeModal('add-customer-modal');
    
    document.getElementById('new-cust-name').value = '';
    document.getElementById('new-cust-phone').value = '';
    if(document.getElementById('new-cust-whatsapp')) document.getElementById('new-cust-whatsapp').value = '';
    if(document.getElementById('new-cust-email')) document.getElementById('new-cust-email').value = '';
    if(document.getElementById('new-cust-address')) document.getElementById('new-cust-address').value = '';
    if(document.getElementById('new-cust-opening')) document.getElementById('new-cust-opening').value = '';
    if(document.getElementById('new-cust-credit')) document.getElementById('new-cust-credit').value = '';
    if(document.getElementById('new-cust-security')) document.getElementById('new-cust-security').value = '1';

    showToast('✅ تم إضافة العميل بنجاح');
}

function loadPreviousInvoice() {
    const currentNo = document.getElementById('hdr-inv-no').value;
    if (!currentNo) return;
    
    const sorted = getSortedSalesInvoices();
    
    if (sorted.length === 0) {
        showToast('ℹ️ لا توجد فواتير محفوظة', 'warning');
        return;
    }
    
    const idx = sorted.findIndex(inv => inv.invoiceNo === currentNo || inv.id === currentNo);
    let targetIdx = -1;
    if (idx === -1) {
        // Current invoice not in list (new/unsaved) — go to last saved invoice
        targetIdx = sorted.length - 1;
    } else if (idx > 0) {
        targetIdx = idx - 1;
    }
    
    if (targetIdx >= 0) {
        const prevNo = sorted[targetIdx].invoiceNo;
        if (loadInvoiceFromStorage(prevNo)) {
            showToast(`✅ تم استرجاع الفاتورة ${prevNo}`);
        }
    } else {
        showToast('ℹ️ أنت في أول فاتورة', 'warning');
    }
}

function loadNextInvoice() {
    const currentNo = document.getElementById('hdr-inv-no').value;
    if (!currentNo) return;
    
    const sorted = getSortedSalesInvoices();
    
    const idx = sorted.findIndex(inv => inv.invoiceNo === currentNo || inv.id === currentNo);
    if (idx >= 0 && idx < sorted.length - 1) {
        const nextNo = sorted[idx + 1].invoiceNo;
        if (loadInvoiceFromStorage(nextNo)) {
            showToast(`✅ تم استرجاع الفاتورة ${nextNo}`);
        }
    } else {
        // No next invoice, jump to new
        onSuccessNewInvoice();
        showToast('✨ فاتورة جديدة', 'success');
    }
}

window.isInvoiceLocked = false;
window.isInvoicePosted = false;

function showEditBanner(invNo, opts = {}) {
    const banner = document.getElementById('saved-invoice-edit-banner');
    if (!banner) return;

    const isPosted = !!opts.isPosted;
    const isLocked = !!opts.isLocked;

    if (isPosted) {
        banner.style.background = 'linear-gradient(135deg, rgba(239, 68, 68, 0.18), rgba(185, 28, 28, 0.3))';
        banner.style.borderColor = 'rgba(239, 68, 68, 0.7)';
        banner.style.boxShadow = '0 4px 20px rgba(239, 68, 68, 0.35)';
        banner.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 1.8rem;">🔒</span>
                    <div>
                        <div style="color: #fca5a5; font-size: 1.15rem; font-weight: 900;">
                            تنبيه أمان: هذه الفاتورة (رقم: <span style="color:#ffffff; font-family:'Outfit',sans-serif; text-decoration: underline;">${invNo}</span>) مُرحّلة محاسبياً في دفتر الأستاذ العام
                        </div>
                        <div style="color: #fecaca; font-size: 0.9rem; font-weight: 700; margin-top: 2px;">
                            تم إثبات الأثر المالي والمخزني لهذه الفاتورة. الفاتورة مغلقة للقراءة فقط وممنوع تعديلها نهائياً للحفاظ على سلامة الحسابات.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" onclick="onSuccessNewInvoice()" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 7px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                        ➕ بدء فاتورة جديدة
                    </button>
                </div>
            </div>
        `;
    } else if (isLocked) {
        banner.style.background = 'linear-gradient(135deg, rgba(56, 189, 248, 0.18), rgba(30, 58, 138, 0.3))';
        banner.style.borderColor = 'rgba(56, 189, 248, 0.7)';
        banner.style.boxShadow = '0 4px 20px rgba(56, 189, 248, 0.25)';
        banner.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 1.8rem;">👁️</span>
                    <div>
                        <div style="color: #38bdf8; font-size: 1.15rem; font-weight: 900;">
                            وضع المعاينة فقط للفاتورة رقم (<span style="color:#ffffff; font-family:'Outfit',sans-serif; text-decoration: underline;">${invNo}</span>)
                        </div>
                        <div style="color: #bae6fd; font-size: 0.9rem; font-weight: 700; margin-top: 2px;">
                            الفاتورة مفتوحة للعرض والقراءة فقط. الحقول مغلقة ضد التعديل غير المقصود.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" onclick="setInvoiceLockState(false, false, '${invNo}')" style="background: linear-gradient(135deg, #0284c7, #0369a1); border: 1px solid rgba(56,189,248,0.5); color: #fff; padding: 7px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.9rem; transition: 0.2s;">
                        ✏️ تفعيل التعديل
                    </button>
                    <button type="button" onclick="onSuccessNewInvoice()" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); color: #fff; padding: 7px 14px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.85rem; transition: 0.2s;">
                        ➕ فاتورة جديدة
                    </button>
                </div>
            </div>
        `;
    } else {
        banner.style.background = 'linear-gradient(135deg, rgba(245, 158, 11, 0.18), rgba(180, 83, 9, 0.3))';
        banner.style.borderColor = 'rgba(245, 158, 11, 0.7)';
        banner.style.boxShadow = '0 4px 20px rgba(245, 158, 11, 0.25)';
        banner.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 1.8rem;">⚠️</span>
                    <div>
                        <div style="color: #fbbf24; font-size: 1.15rem; font-weight: 900;">
                            تنبيه: أنت الآن في وضع تعديل فاتورة محفوظة مسبقاً (<span style="color:#ffffff; font-family:'Outfit',sans-serif; text-decoration: underline;">${invNo}</span>)
                        </div>
                        <div style="color: #fde68a; font-size: 0.9rem; font-weight: 700; margin-top: 2px;">
                            أصل الفاتورة تم اعتماده مسبقاً في النظام. حفظ أي تعديلات سيقوم بتحديث الحسابات وحركات المخزن المرتبطة بها تلقائياً.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" onclick="onSuccessNewInvoice()" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 7px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.85rem; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                        ➕ إلغاء التعديل وبدء فاتورة جديدة
                    </button>
                </div>
            </div>
        `;
    }
    banner.style.display = 'block';
}

function hideEditBanner() {
    const banner = document.getElementById('saved-invoice-edit-banner');
    if (banner) {
        banner.style.display = 'none';
    }
}

function setInvoiceLockState(isLocked, isPosted = false, invNo = '') {
    window.isInvoiceLocked = !!isLocked;
    window.isInvoicePosted = !!isPosted;

    if (!invNo) {
        invNo = document.getElementById('hdr-inv-no')?.value || currentViewedInvoiceNo || '';
    }

    const editor = document.getElementById('invoice-editor-view');
    if (editor) {
        editor.querySelectorAll('input:not(#hub-search):not(#delivery-search), select:not(#preset-selector), textarea').forEach(el => {
            if (isLocked) {
                el.disabled = true;
                el.setAttribute('data-immutability-locked', 'true');
            } else {
                el.disabled = false;
                el.removeAttribute('data-immutability-locked');
            }
        });

        // Hide/show table add and delete buttons
        const addBtn = editor.querySelector('button[onclick*="addRow"]');
        if (addBtn) addBtn.style.display = isLocked ? 'none' : '';

        editor.querySelectorAll('.btn-del-row, table button').forEach(b => {
            b.style.display = isLocked ? 'none' : '';
        });

        // Save Button vs Locked Badge
        const saveBtn = editor.querySelector('.btn-submit');
        let lockedBadge = document.getElementById('locked-submit-badge');

        if (isLocked) {
            if (saveBtn) saveBtn.style.display = 'none';
            if (!lockedBadge && saveBtn && saveBtn.parentNode) {
                lockedBadge = document.createElement('div');
                lockedBadge.id = 'locked-submit-badge';
                lockedBadge.style.cssText = 'flex: 1.5; height: 45px; display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(239, 68, 68, 0.15); border: 1.5px solid rgba(239, 68, 68, 0.5); color: #fca5a5; border-radius: 8px; font-weight: 800; font-size: 1rem; box-shadow: 0 2px 10px rgba(239,68,68,0.2);';
                lockedBadge.innerHTML = isPosted ? '<span style="font-size:1.2rem;">🔒</span> <span>فاتورة مرحلة محاسبياً - ممنوع التعديل</span>' : '<span style="font-size:1.2rem;">👁️</span> <span>وضع المعاينة فقط - التعديل معطل</span>';
                saveBtn.parentNode.insertBefore(lockedBadge, saveBtn);
            } else if (lockedBadge) {
                lockedBadge.style.display = 'flex';
                lockedBadge.innerHTML = isPosted ? '<span style="font-size:1.2rem;">🔒</span> <span>فاتورة مرحلة محاسبياً - ممنوع التعديل</span>' : '<span style="font-size:1.2rem;">👁️</span> <span>وضع المعاينة فقط - التعديل معطل</span>';
            }
        } else {
            if (saveBtn) saveBtn.style.display = 'flex';
            if (lockedBadge) lockedBadge.style.display = 'none';
        }
    }

    if (invNo) {
        showEditBanner(invNo, { isLocked: isLocked, isPosted: isPosted });
    }
}

function loadInvoiceFromStorage(invNo, opts = {}) {
    let str = localStorage.getItem('erp_inv_' + invNo);
    if (!str) {
        str = localStorage.getItem('erp_draft_' + invNo);
    }
    let data;
    if (str) {
        try { data = JSON.parse(str); } catch(e) {}
    }
    
    if (!data) {
        let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
        data = allSales.find(inv => (inv.invoiceNo || inv.id) === invNo);
    }
    
    if (!data) return false;
    
    try {
        const isPosted = (data.status === 'posted' || data.effectsApplied || opts.isPosted);
        const isLocked = (isPosted || opts.isLocked === true);

        currentViewedInvoiceNo = data.invoiceNo || data.id;

        if (data.saleType) {
            currentSaleType = data.saleType;
            if (typeof openInvoiceEditor === 'function') openInvoiceEditor(currentSaleType, true);
        }

        // Reset form
        rows = [];
        rowIdCounter = 0;
        
        // Set Header
        document.getElementById('hdr-inv-no').value = data.invoiceNo;
        document.getElementById('inv-number').textContent = data.invoiceNo;
        if(data.date) document.getElementById('hdr-date').value = data.date;
        if(data.description) document.getElementById('hdr-description').value = data.description;
        if(data.notes) document.getElementById('inv-notes').value = data.notes;
        if(data.warehouse) document.getElementById('hdr-warehouse').value = data.warehouse;
        if(data.currency) document.getElementById('hdr-currency').value = data.currency;
        if(data.exchangeRate !== undefined) {
            document.getElementById('hdr-exchange-rate').value = data.exchangeRate;
            document.getElementById('hdr-exchange-rate').disabled = (data.currency === 'SDG');
        }
        
        // Customer
        const custId = data.customer || data.customerId;
        if (custId) {
            const c = CUSTOMERS.find(x => String(x.id) === String(custId));
            if (c) selectCustomer(c.id, c.name);
        }
        
        // Rows — PERFORMANCE: direct property assignment (no recalcAll per field)
        const itemsList = data.items || data.products || [];
        if(itemsList && itemsList.length) {
            itemsList.forEach(i => {
                addRow(true, true); // silent and skip render
                const r = rows[rows.length-1];
                
                r.itemId = i.itemId || i.id || i.item_id || '';
                r.selectedUnit = i.selectedUnit || i.unit || i.unitName || '';
                r.customFactor = i.customFactor !== undefined ? i.customFactor : (i.factor !== undefined ? i.factor : 1);
                r.quantity = i.quantity !== undefined ? i.quantity : (i.qty !== undefined ? i.qty : 0);
                if (i.customBaseQty !== undefined) r.customBaseQty = i.customBaseQty;
                r.price = i.price !== undefined ? i.price : (i.rate !== undefined ? i.rate : 0);
                if (i.fcPrice !== undefined) r.fcPrice = i.fcPrice;
                r.warehouseId = i.warehouseId || i.warehouse || '';
                r.costCenterId = i.costCenterId || i.costCenter || '';
                if (i.qrCode) r.qrCode = i.qrCode;
                if (i.note) r.note = i.note;
                if (i.itemName || i.name) r.itemName = i.itemName || i.name;
                if (i.buyPrice !== undefined) r.buyPrice = i.buyPrice;
                if (i.sellPrice !== undefined) r.sellPrice = i.sellPrice;
            });
        }
        
        renderGrid();
        recalcAll();
        loadPaymentDataIntoUI(data);
        if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
        
        // Apply strict lock state if posted or preview-only
        setInvoiceLockState(isLocked, isPosted, currentViewedInvoiceNo);

        return true;
    } catch(e) {
        console.error('Error loading invoice', e);
        alert('حدث خطأ أثناء تحميل الفاتورة:\n' + e.message + '\n' + e.stack);
        return false;
    }
}

// ════════════════════════════════════════
//  CUSTOMERS DROPDOWN LOGIC
// ════════════════════════════════════════
function renderCustomerDropdown(filter = '') {
    const listEl = document.getElementById('customer-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
    
    try {
        let localMatches = CUSTOMERS;
        if (normalizedTerm) {
            const searchWords = normalizedTerm.split(/\s+/).filter(w => w.length > 0);
            
            localMatches = CUSTOMERS.filter(c => {
                const normName = window.normalizeArabic ? window.normalizeArabic(c.name) : c.name.toLowerCase();
                const normPhone = String(c.phone || '');
                const normEmail = String(c.email || '').toLowerCase();
                
                return searchWords.every(word => 
                    normName.includes(word) || 
                    normPhone.includes(word) || 
                    normEmail.includes(word)
                );
            });
        }

        const finalMatches = Array.from(new Map(localMatches.map(m => [String(m.id), m])).values()).slice(0, 50);

        if (finalMatches.length === 0) {
            listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة للبحث.<br><small style="color:#888;">(إجمالي العملاء: ' + CUSTOMERS.length + ')</small></div>';
            return;
        }

        finalMatches.forEach(c => {
            const item = document.createElement('div');
            item.className = 'custom-dropdown-item';
            
            const nameH = window.highlightSearchMatches ? window.highlightSearchMatches(c.name, filter) : c.name;
            const phoneH = c.phone ? (window.highlightSearchMatches ? window.highlightSearchMatches(String(c.phone), filter) : c.phone) : '';
            const emailH = c.email ? (window.highlightSearchMatches ? window.highlightSearchMatches(String(c.email), filter) : c.email) : '';

            item.innerHTML = `<div style="display: flex; justify-content: space-between; align-items: center; width: 100%; text-align: right; dir: rtl;"><div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap;"><span style="font-weight: bold; color: #fff;">${nameH}</span>${phoneH ? `<span style="font-size:0.8rem; color:#94a3b8;">(${phoneH})</span>` : ''}${emailH ? `<span style="font-size:0.8rem; color:#64748b;">[${emailH}]</span>` : ''}</div></div>`;
            item.onmousedown = () => selectCustomer(c.id, c.name); 
            item.onpointerdown = () => selectCustomer(c.id, c.name);
            listEl.appendChild(item);
        });
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">حدث خطأ داخلي</div>';
    }
}

function showCustomerDropdown() {
    renderCustomerDropdown(document.getElementById('hdr-customer-search').value);
    const listEl = document.getElementById('customer-dropdown-list');
    if (listEl) {
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

let hideDropdownTimeout;

function hideCustomerDropdownDelayed() {
    clearTimeout(hideDropdownTimeout);
    hideDropdownTimeout = setTimeout(() => {
        if (document.activeElement && document.activeElement.id === 'hdr-customer-search') {
            return;
        }

        const listEl = document.getElementById('customer-dropdown-list');
        if(listEl) {
            listEl.classList.remove('show');
            listEl.style.display = 'none';
        }
        
        const idVal = document.getElementById('hdr-customer').value;
        const searchVal = document.getElementById('hdr-customer-search').value.trim();
        if (idVal) {
            const cust = CUSTOMERS.find(c => String(c.id) === String(idVal));
            if (cust && cust.name !== searchVal) {
                document.getElementById('hdr-customer-search').value = cust.name;
            }
        } else if (searchVal) {
            const exactMatch = CUSTOMERS.find(c => c.name === searchVal);
            if (exactMatch) {
                selectCustomer(exactMatch.id, exactMatch.name);
            }
        } else {
            document.getElementById('hdr-customer-search').value = '';
        }
    }, 200);
}

function filterCustomerDropdown() {
    document.getElementById('hdr-customer').value = '';
    updateLedgerButton();
    onCustomerChange();
    renderCustomerDropdown(document.getElementById('hdr-customer-search').value);
    const listEl = document.getElementById('customer-dropdown-list');
    if (listEl) {
        listEl.style.display = 'block';
        listEl.classList.add('show');
    }
}

function selectCustomer(id, name) {
    document.getElementById('hdr-customer').value = id;
    const searchEl = document.getElementById('hdr-customer-search');
    if (searchEl) {
        searchEl.value = name;
        searchEl.blur();
    }
    const cList = document.getElementById('customer-dropdown-list');
    if (cList) {
        cList.classList.remove('show');
        cList.style.display = 'none';
    }
    updateLedgerButton();
    onCustomerChange();
}

function updateLedgerButton() {
    const custVal = document.getElementById('hdr-customer') ? document.getElementById('hdr-customer').value : '';
    const btn = document.getElementById('hdr-customer-ledger-btn');
    if (btn) {
        btn.style.display = (custVal && custVal !== 'walk-in') ? 'inline-flex' : 'none';
    }
}

function onCustomerChange() {
    updateLedgerButton();
    const id = document.getElementById('hdr-customer').value;
    const cust = CUSTOMERS.find(c => String(c.id) === String(id));
    const cur = document.getElementById('hdr-currency').value;
    const strip = document.getElementById('fin-strip');
    const badge = document.getElementById('fin-status-badge');

    if (!cust || id === '') {
        ['fin-ledger','fin-checks','fin-total-debt','fin-credit-limit','fin-remaining'].forEach(el => {
            const dom = document.getElementById(el);
            if (dom) dom.textContent = '—';
        });
        if (strip) strip.classList.remove('visible');
        if (badge) badge.style.display = 'none';
        return;
    }

    const totalDebt = cust.ledger + cust.checks;
    const remaining = cust.creditLimit - totalDebt;

    document.getElementById('fin-ledger').textContent      = fmtNum(cust.ledger)      + ' ' + cur;
    document.getElementById('fin-checks').textContent      = fmtNum(cust.checks)      + ' ' + cur;
    document.getElementById('fin-total-debt').textContent  = fmtNum(totalDebt)        + ' ' + cur;
    document.getElementById('fin-credit-limit').textContent= fmtNum(cust.creditLimit) + ' ' + cur;
    document.getElementById('fin-remaining').textContent   = fmtNum(remaining)        + ' ' + cur;

    // إظهار الشريط المالي الأفقي بأنيميشن
    if (strip) {
        // أعد تشغيل الأنيميشن بإزالة وإعادة الكلاس
        strip.classList.remove('visible');
        requestAnimationFrame(() => requestAnimationFrame(() => strip.classList.add('visible')));
    }

    // badge الحالة بجانب عنوان اللوحة
    if (badge) {
        const txtEl = document.getElementById('fin-status-txt');
        const dot = badge.querySelector('.status-dot');
        if (cust.status === 'blacklisted') {
            if (dot) dot.className = 'status-dot danger';
            if (txtEl) txtEl.textContent = '⛔ قائمة سوداء';
            showToast('تحذير: هذا العميل في القائمة السوداء!', 'error', 5000);
        } else if (remaining > 20000) {
            if (dot) dot.className = 'status-dot good';
            if (txtEl) txtEl.textContent = '✅ ائتمان جيد';
        } else if (remaining > 0) {
            if (dot) dot.className = 'status-dot warn';
            if (txtEl) txtEl.textContent = '⚠️ ائتمان منخفض';
        } else {
            if (dot) dot.className = 'status-dot danger';
            if (txtEl) txtEl.textContent = '🚫 تجاوز الحد';
        }
        badge.style.display = 'inline-flex';
    }
}

function toggleFinancialPanel() { /* deprecated - kept for compatibility */ }
function expandFinancialPanel()  { onCustomerChange(); }
function collapseFinancialPanel() {
    const strip = document.getElementById('fin-strip');
    if (strip) strip.classList.remove('visible');
}

function addNewCustomer() {
    const name = document.getElementById('new-cust-name').value.trim();
    const phone = document.getElementById('new-cust-phone').value.trim();
    const whatsapp = document.getElementById('new-cust-whatsapp') ? document.getElementById('new-cust-whatsapp').value.trim() : '';
    const email = document.getElementById('new-cust-email') ? document.getElementById('new-cust-email').value.trim() : '';
    const address = document.getElementById('new-cust-address') ? document.getElementById('new-cust-address').value.trim() : '';
    const securityLevel = document.getElementById('new-cust-security') ? document.getElementById('new-cust-security').value : '1';
    const openingBalance = document.getElementById('new-cust-opening') ? parseNum(document.getElementById('new-cust-opening').value) : 0;
    const credit = document.getElementById('new-cust-credit') ? parseNum(document.getElementById('new-cust-credit').value) : 0;

    if (!name) { showToast('❌ يرجى إدخال اسم العميل', 'error'); return; }

    const newId = 'C-' + String(Date.now()).slice(-6);
    const openingDebit = openingBalance >= 0 ? openingBalance : 0;
    const openingCredit = openingBalance < 0 ? Math.abs(openingBalance) : 0;
    const newCust = { 
        id: newId, 
        name: name, 
        phone: phone,
        whatsapp: whatsapp,
        email: email,
        address: address,
        securityLevel: securityLevel,
        openingDebit: openingDebit,
        openingCredit: openingCredit,
        currentBalance: openingBalance,
        creditLimit: credit,
        status: 'active',
        bankAccounts: [],
        timestamp: new Date().toISOString()
    };
    
    if (typeof CUSTOMERS !== 'undefined') {
        CUSTOMERS.push(newCust);
    }

    let allCustomers = [];
    try {
        allCustomers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
    } catch(e) {}
    allCustomers.push(newCust);
    localStorage.setItem('erp_customers_v1', JSON.stringify(allCustomers));

    selectCustomer(newId, name);
    closeModal('add-customer-modal');
    
    document.getElementById('new-cust-name').value = '';
    document.getElementById('new-cust-phone').value = '';
    if(document.getElementById('new-cust-whatsapp')) document.getElementById('new-cust-whatsapp').value = '';
    if(document.getElementById('new-cust-email')) document.getElementById('new-cust-email').value = '';
    if(document.getElementById('new-cust-address')) document.getElementById('new-cust-address').value = '';
    if(document.getElementById('new-cust-opening')) document.getElementById('new-cust-opening').value = '';
    if(document.getElementById('new-cust-credit')) document.getElementById('new-cust-credit').value = '';
    if(document.getElementById('new-cust-security')) document.getElementById('new-cust-security').value = '1';

    showToast('✅ تم إضافة العميل بنجاح');
}

function loadPreviousInvoice() {
    const currentNo = document.getElementById('hdr-inv-no').value;
    if (!currentNo) return;
    
    const sorted = getSortedSalesInvoices();
    if (sorted.length === 0) {
        showToast('ℹ️ لا توجد فواتير محفوظة', 'warning');
        return;
    }
    
    const idx = sorted.findIndex(inv => inv.invoiceNo === currentNo || inv.id === currentNo);
    let targetIdx = -1;
    if (idx === -1) {
        targetIdx = sorted.length - 1;
    } else if (idx > 0) {
        targetIdx = idx - 1;
    }
    
    if (targetIdx >= 0) {
        const prevNo = sorted[targetIdx].invoiceNo;
        if (loadInvoiceFromStorage(prevNo)) {
            showToast(`✅ تم استرجاع الفاتورة ${prevNo}`);
        }
    } else {
        showToast('ℹ️ أنت في أول فاتورة', 'warning');
    }
}

function loadNextInvoice() {
    const currentNo = document.getElementById('hdr-inv-no').value;
    if (!currentNo) return;
    
    const sorted = getSortedSalesInvoices();
    
    const idx = sorted.findIndex(inv => inv.invoiceNo === currentNo || inv.id === currentNo);
    if (idx >= 0 && idx < sorted.length - 1) {
        const nextNo = sorted[idx + 1].invoiceNo;
        if (loadInvoiceFromStorage(nextNo)) {
            showToast(`✅ تم استرجاع الفاتورة ${nextNo}`);
        }
    } else {
        onSuccessNewInvoice();
        showToast('✨ فاتورة جديدة', 'success');
    }
}



// ════════════════════════════════════════
//  CUSTOMERS DROPDOWN LOGIC
// ════════════════════════════════════════
function renderCustomerDropdown(filter = '') {
    const listEl = document.getElementById('customer-dropdown-list');
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    
    let matches = CUSTOMERS;
    if (term) {
        matches = CUSTOMERS.filter(c => c.name.toLowerCase().includes(term));
    }

    if (matches.length === 0) {
        listEl.innerHTML = `<div class="custom-dropdown-empty">لا يوجد عميل بهذا الاسم.<br><small style="color:#888;">(إجمالي العملاء: ${CUSTOMERS.length})</small></div>`;
        return;
    }

    matches.slice(0, 50).forEach(c => {
        const item = document.createElement('div');
        item.className = 'custom-dropdown-item';
        
        const nameH = window.highlightSearchMatches ? window.highlightSearchMatches(c.name, filter) : c.name;
        const phoneH = c.phone ? (window.highlightSearchMatches ? window.highlightSearchMatches(String(c.phone), filter) : c.phone) : '';
        const emailH = c.email ? (window.highlightSearchMatches ? window.highlightSearchMatches(String(c.email), filter) : c.email) : '';

        item.innerHTML = `<div style="display: flex; justify-content: space-between; align-items: center; width: 100%; text-align: right; dir: rtl; padding: 6px 4px;"><div style="display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap;"><span style="font-weight: 800; font-size: 1.45rem; color: #6ee7b7; text-shadow: 0 0 6px rgba(110,231,183,0.2);">${nameH}</span>${phoneH ? `<span style="font-size:1rem; color:#94a3b8; font-weight:600;">(${phoneH})</span>` : ''}${emailH ? `<span style="font-size:0.9rem; color:#64748b;">[${emailH}]</span>` : ''}</div></div>`;
        item.onmousedown = () => selectCustomer(c.id, c.name); 
        item.onpointerdown = () => selectCustomer(c.id, c.name);
        listEl.appendChild(item);
    });
    if (typeof GridCustomizer !== 'undefined') {
        GridCustomizer.attachEvents();
        GridCustomizer.applyStyles();
    }
}

function showCustomerDropdown() {
    renderCustomerDropdown(document.getElementById('hdr-customer-search').value);
    const listEl = document.getElementById('customer-dropdown-list');
    if (listEl) {
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

function hideCustomerDropdownDelayed() {
    setTimeout(() => {
        const listEl = document.getElementById('customer-dropdown-list');
        if(listEl) listEl.classList.remove('show'); listEl.style.display = 'none';
        
        const idVal = document.getElementById('hdr-customer').value;
        const searchVal = document.getElementById('hdr-customer-search').value.trim();
        if (idVal) {
            const cust = CUSTOMERS.find(c => String(c.id) === String(idVal));
            if (cust && cust.name !== searchVal) {
                document.getElementById('hdr-customer-search').value = cust.name;
            }
        } else {
            // Allow walk-in customers or custom names without disappearing
            // 
        }
    }, 150);
}

function filterCustomerDropdown() {
    document.getElementById('hdr-customer').value = '';
    onCustomerChange();
    renderCustomerDropdown(document.getElementById('hdr-customer-search').value);
    document.getElementById('customer-dropdown-list').classList.add('show');
}

function selectCustomer(id, name) {
    document.getElementById('hdr-customer').value = id;
    document.getElementById('hdr-customer-search').value = name;
    
    document.getElementById('customer-dropdown-list').classList.remove('show');
    onCustomerChange();
}

// ════════════════════════════════════════
//  ITEMS DROPDOWN LOGIC
// ════════════════════════════════════════
async function renderItemDropdown(rowId, isMobile = false, filter = '') {
    const prefix = isMobile ? 'm-' : '';
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    if (!listEl) return;
    
    listEl.innerHTML = '<div class="custom-dropdown-empty">جاري البحث...</div>';
    const term = filter.toLowerCase().trim();
    
    try {
        let localMatches = ITEMS;
        if (term) {
            localMatches = ITEMS.filter(i => {
                if (!i) return false;
                const normName = (i.name || '').toLowerCase();
                const nameWords = normName.split(' ');
                const bcMatches = i.barcode && i.barcode.toLowerCase().includes(term);
                const unitBcMatches = i.units && Array.isArray(i.units) && i.units.some(u => u.barcode && u.barcode.toLowerCase().includes(term));
                return nameWords.some(w => w.startsWith(term)) || normName.includes(term) || bcMatches || unitBcMatches;
            });
        }

        let dbMatches = [];
        try {
            const token = localStorage.getItem('token');
            const headers = token ? { 'Authorization': 'Bearer ' + token } : {};
            const res = await fetch('api/items_search.php?q=' + encodeURIComponent(term), { headers });
            const rawMatches = await res.json();
            if (Array.isArray(rawMatches)) {
                dbMatches = rawMatches;
            }
        } catch(err) {
            console.error('API Search Failed:', err);
        }
        
        const rawItems = window._PRELOADED_ITEMS || JSON.parse(localStorage.getItem('erp_items_v1')) || [];
        
        // Merge dbMatches and newly added rawItems
        const allNew = [...rawItems, ...dbMatches];
        allNew.forEach(item => {
            if (!item || !item.id) return;
            let existing = ITEMS.find(i => String(i.id) === String(item.id));
            const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'قطعة';
            const unitsArray = extractItemUnits(item);
            let sp = item.sellingPrice || item.salePrice || item.price || 0;
            sp = sp.toString().replace(/,/g, '');

            if (!existing) {
                existing = {
                    id: item.id,
                    name: item.name || 'صنف',
                    unit: baseUnit,
                    baseUnit: baseUnit,
                    conversionFactor: 1, 
                    bundleFactor: parseFloat(item.bundleFactor) || 1,
                    price: parseFloat(sp) || 0,
                    actualStock: {},
                    reservedStock: {},
                    units: item.units || [],
                    unitsArray: unitsArray,
                    barcode: item.barcode || '',
                    maxDiscount: item.maxDiscount || 0,
                    status: item.status || 'active'
                };
                ITEMS.push(existing);
                if (!term || existing.name.toLowerCase().includes(term)) {
                    localMatches.push(existing);
                }
            } else {
                existing.units = item.units || existing.units;
                existing.unitsArray = unitsArray;
            }
        });

        const finalMatches = Array.from(new Map(localMatches.map(m => [String(m.id), m])).values());

        if (finalMatches.length === 0) {
            listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة.<br><small style="color:#888;">(حاول استخدام اسم آخر)</small></div>';
            return;
        }

        listEl.innerHTML = '';
        finalMatches.slice(0, 50).forEach((existing, idx) => {
            const div = document.createElement('div');
            div.className = 'custom-dropdown-item' + (idx === 0 ? ' active' : '');
            div.setAttribute('data-item-id', existing.id);

            let displayText = `<strong>${existing.name}</strong>`;
            if (existing.barcode) displayText += ` <span style="font-size:0.8rem; color:#888;">[${existing.barcode}]</span>`;
            
            div.innerHTML = `<div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                <span>${displayText}</span>
            </div>`;
            
            const doSelect = (e) => {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                selectRowItem(rowId, existing.id, isMobile);
            };
            div.onmousedown = doSelect;
            div.onpointerdown = doSelect;
            div.onclick = doSelect;
            listEl.appendChild(div);
        });
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">خطأ في الاتصال</div>';
    }
}

function applyItemToRowDOM(rowId, item, selectedUnit) {
    if (!item) return;
    const units = extractItemUnits(item);
    const chosenUnit = selectedUnit || (units.length > 0 ? units[0].name : (item.unit || item.baseUnit || ''));
    const chosenFactor = (units.find(u => u.name === chosenUnit) || {}).factor || 1;
    const chosenPrice = (units.find(u => u.name === chosenUnit) || {}).price || parseFloat(item.sellingPrice || item.price || 0) || 0;

    // 1. Update Desktop DOM elements directly
    const itemInput = document.getElementById(`item-search-${rowId}`);
    if (itemInput) itemInput.value = item.name;

    const unitSelects = document.querySelectorAll(`select[data-unit-select="${rowId}"], #grid-body tr [data-col-id="col_unit"] select`);
    unitSelects.forEach(sel => {
        if (units.length > 0) {
            sel.innerHTML = units.map(u => `<option value="${u.name}" ${u.name === chosenUnit ? 'selected' : ''}>${u.name}</option>`).join('');
            sel.value = chosenUnit;
        } else {
            sel.innerHTML = `<option value="${chosenUnit || 'قطعة'}" selected>${chosenUnit || 'قطعة'}</option>`;
        }
    });

    const factorInputs = document.querySelectorAll(`input[data-factor-input="${rowId}"], #grid-body tr [data-col-id="col_factor"] input`);
    factorInputs.forEach(inp => {
        inp.value = fmtNum(chosenFactor, 'col_factor');
    });

    const priceInputs = document.querySelectorAll(`input[data-price-input="${rowId}"], #grid-body tr [data-col-id="col_price"] input`);
    priceInputs.forEach(inp => {
        if (!inp.value || inp.value === '0' || inp.value === '0.00') {
            inp.value = fmtNum(chosenPrice, 'col_price');
        }
    });

    // 2. Update Mobile DOM elements directly
    const mItemInput = document.getElementById(`m-item-search-${rowId}`);
    if (mItemInput) mItemInput.value = item.name;

    const mUnitSelects = document.querySelectorAll(`select[data-m-unit-select="${rowId}"]`);
    mUnitSelects.forEach(sel => {
        if (units.length > 0) {
            sel.innerHTML = units.map(u => `<option value="${u.name}" ${u.name === chosenUnit ? 'selected' : ''}>${u.name}</option>`).join('');
            sel.value = chosenUnit;
        } else {
            sel.innerHTML = `<option value="${chosenUnit || 'قطعة'}" selected>${chosenUnit || 'قطعة'}</option>`;
        }
    });
}

function selectRowItem(rowId, itemId, isMobile = false) {
    const prefix = isMobile ? 'm-' : '';
    
    // Hide ALL open dropdown lists immediately
    document.querySelectorAll('.custom-dropdown-list').forEach(el => {
        el.classList.remove('show');
        el.style.display = 'none';
    });

    const searchInput = document.getElementById(`${prefix}item-search-${rowId}`);
    if (searchInput) {
        searchInput.blur();
    }

    // Ensure item exists in ITEMS
    let item = ITEMS.find(i => String(i.id) === String(itemId));
    if (!item) {
        try {
            const raw = (window._PRELOADED_ITEMS || JSON.parse(localStorage.getItem('erp_items_v1')) || []).find(i => String(i.id) === String(itemId));
            if (raw) {
                item = raw;
                item.unitsArray = extractItemUnits(item);
                ITEMS.push(item);
            }
        } catch(e) {}
    }

    // Immediate in-place DOM population before grid rebuild
    if (item) {
        applyItemToRowDOM(rowId, item);
    }

    updateRow(rowId, 'itemId', itemId);
    renderGrid();
    recalcAll();

    // Secondary guarantee in-place DOM population after grid rebuild
    if (item) {
        applyItemToRowDOM(rowId, item);
    }

    // Auto-focus quantity column on the selected row
    setTimeout(() => {
        const trIndex = rows.findIndex(r => r.id === rowId);
        if (trIndex !== -1) {
            const tr = document.querySelectorAll('#grid-body tr')[trIndex];
            if (tr) {
                const qtyInput = tr.querySelector('[data-col-id="col_qty"] input');
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.select();
                }
            }
        }
    }, 50);
}

function showItemDropdown(rowId, isMobile = false) {
    const prefix = isMobile ? 'm-' : '';
    renderItemDropdown(rowId, isMobile, '');
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    if (listEl) {
        listEl.style.display = 'block';
        listEl.classList.add('show');
    }
}

function hideItemDropdownDelayed(rowId, isMobile = false) {
    setTimeout(() => {
        const prefix = isMobile ? 'm-' : '';
        const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
        if (listEl) { 
            listEl.classList.remove('show'); 
            listEl.style.display = 'none'; 
        }
        
        const row = rows.find(r => r.id === rowId);
        const searchEl = document.getElementById(`${prefix}item-search-${rowId}`);
        if (searchEl) {
            const val = searchEl.value.trim().toLowerCase();
            // If row has no itemId selected but user typed or scanned something, auto-select best match
            if ((!row || !row.itemId) && val !== '') {
                const exactItem = ITEMS.find(i => (i.name && i.name.toLowerCase() === val) || (i.barcode && i.barcode.toLowerCase() === val));
                if (exactItem) {
                    selectRowItem(rowId, exactItem.id, isMobile);
                    return;
                }
                const partialItem = ITEMS.find(i => i.name && i.name.toLowerCase().includes(val));
                if (partialItem) {
                    selectRowItem(rowId, partialItem.id, isMobile);
                    return;
                }
            }

            if (row && row.itemId) {
                const item = ITEMS.find(i => String(i.id) === String(row.itemId));
                if (item) {
                    searchEl.value = item.name;
                }
            } else {
                searchEl.value = '';
                // Automatically remove empty row if there are multiple rows and this row has no item
                if (row && !row.itemId && rows.length > 1 && (!row.quantity || row.quantity === 0)) {
                    removeRow(rowId, true);
                    return;
                }
            }
        }
    }, 200);
}

function filterItemDropdown(rowId, isMobile = false) {
    const prefix = isMobile ? 'm-' : '';
    const searchEl = document.getElementById(`${prefix}item-search-${rowId}`);
    renderItemDropdown(rowId, isMobile, searchEl.value);
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    if (listEl) {
        listEl.style.display = 'block';
        listEl.classList.add('show');
    }
}

function handleItemSearchKeydown(event, rowId, isMobile = false) {
    const prefix = isMobile ? 'm-' : '';
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    const searchEl = document.getElementById(`${prefix}item-search-${rowId}`);
    const typedVal = searchEl ? searchEl.value.trim().toLowerCase() : '';

    if (event.key === 'Enter' || event.key === 'Tab') {
        event.preventDefault();
        
        // 1. Check exact match by barcode or name in ITEMS
        if (typedVal !== '') {
            const exactItem = ITEMS.find(i => (i.name && i.name.toLowerCase() === typedVal) || (i.barcode && i.barcode.toLowerCase() === typedVal));
            if (exactItem) {
                selectRowItem(rowId, exactItem.id, isMobile);
                return;
            }
        }

        // 2. Check highlighted or first item in dropdown list
        if (listEl && listEl.style.display !== 'none') {
            const items = Array.from(listEl.querySelectorAll('.custom-dropdown-item'));
            if (items.length > 0) {
                let activeIdx = items.findIndex(el => el.classList.contains('active'));
                const selectedEl = (activeIdx !== -1) ? items[activeIdx] : items[0];
                if (selectedEl) {
                    const itemId = selectedEl.getAttribute('data-item-id');
                    if (itemId) {
                        selectRowItem(rowId, itemId, isMobile);
                        return;
                    }
                }
            }
        }

        // 3. Check partial match in ITEMS
        if (typedVal !== '') {
            const partialItem = ITEMS.find(i => i.name && i.name.toLowerCase().includes(typedVal));
            if (partialItem) {
                selectRowItem(rowId, partialItem.id, isMobile);
                return;
            }
        }
        return;
    }

    if (!listEl || listEl.style.display === 'none') {
        if (event.key === 'ArrowDown') {
            showItemDropdown(rowId, isMobile);
            event.preventDefault();
        }
        return;
    }

    const items = Array.from(listEl.querySelectorAll('.custom-dropdown-item'));
    if (items.length === 0) return;

    let activeIdx = items.findIndex(el => el.classList.contains('active'));

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (activeIdx !== -1) items[activeIdx].classList.remove('active');
        activeIdx = (activeIdx + 1) % items.length;
        items[activeIdx].classList.add('active');
        items[activeIdx].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (activeIdx !== -1) items[activeIdx].classList.remove('active');
        activeIdx = (activeIdx - 1 + items.length) % items.length;
        items[activeIdx].classList.add('active');
        items[activeIdx].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'Escape') {
        listEl.style.display = 'none';
        listEl.classList.remove('show');
    }
}

// ════════════════════════════════════════
//  QUICK ITEM ADD LOGIC
// ════════════════════════════════════════
async function renderQuickItemDropdown(filter = '') {
    const listEl = document.getElementById('quick-item-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '<div class="custom-dropdown-empty">جاري البحث...</div>';
    const term = filter.toLowerCase().trim();
    
    try {
        let localMatches = ITEMS;
        if (term) {
            localMatches = ITEMS.filter(i => {
                if (!i) return false;
                const normName = (i.name || '').toLowerCase();
                const nameWords = normName.split(' ');
                const bcMatches = i.barcode && i.barcode.toLowerCase().includes(term);
                const unitBcMatches = i.units && Array.isArray(i.units) && i.units.some(u => u.barcode && u.barcode.toLowerCase().includes(term));
                return nameWords.some(w => w.startsWith(term)) || normName.includes(term) || bcMatches || unitBcMatches;
            });
        }

        let dbMatches = [];
        try {
            const res = await fetch('api/items_search.php?q=' + encodeURIComponent(term));
            const rawMatches = await res.json();
            if (Array.isArray(rawMatches)) {
                dbMatches = rawMatches;
            }
        } catch(err) {
            console.error('API Search Failed:', err);
        }
        
        const rawItems = window._PRELOADED_ITEMS || JSON.parse(localStorage.getItem('erp_items_v1')) || [];
        const allNew = [...rawItems, ...dbMatches];
        allNew.forEach(item => {
            if (!item || !item.id) return;
            let existing = ITEMS.find(i => String(i.id) === String(item.id));
            const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'قطعة';
            const unitsArray = extractItemUnits(item);
            let sp = item.sellingPrice || item.salePrice || item.price || 0;
            sp = sp.toString().replace(/,/g, '');

            if (!existing) {
                existing = {
                    id: item.id,
                    name: item.name || 'صنف',
                    unit: baseUnit,
                    baseUnit: baseUnit,
                    conversionFactor: 1, 
                    bundleFactor: parseFloat(item.bundleFactor) || 1,
                    price: parseFloat(sp) || 0,
                    actualStock: {},
                    reservedStock: {},
                    units: item.units || [],
                    unitsArray: unitsArray,
                    barcode: item.barcode || '',
                    maxDiscount: item.maxDiscount || 0,
                    status: item.status || 'active'
                };
                ITEMS.push(existing);
                if (!term || existing.name.toLowerCase().includes(term)) {
                    localMatches.push(existing);
                }
            } else {
                existing.units = item.units || existing.units;
                existing.unitsArray = unitsArray;
            }
        });

        const finalMatches = Array.from(new Map(localMatches.map(m => [String(m.id), m])).values());

        if (finalMatches.length === 0) {
            listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة.<br><small style="color:#888;">(حاول استخدام اسم آخر)</small></div>';
            return;
        }

        listEl.innerHTML = '';
        finalMatches.slice(0, 50).forEach((existing, idx) => {
            const div = document.createElement('div');
            div.className = 'custom-dropdown-item' + (idx === 0 ? ' active' : '');
            div.setAttribute('data-item-id', existing.id);

            let displayText = `<strong>${existing.name}</strong>`;
            if (existing.barcode) displayText += ` <span style="font-size:0.8rem; color:#888;">[${existing.barcode}]</span>`;

            div.innerHTML = `<div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                <span>${displayText}</span>
            </div>`;
            div.onmousedown = () => { selectQuickItem(existing.id); };
            div.onpointerdown = () => { selectQuickItem(existing.id); };
            div.onclick = () => { selectQuickItem(existing.id); };
            listEl.appendChild(div);
        });
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">خطأ في الاتصال</div>';
    }
}

function showQuickItemDropdown() {
    renderQuickItemDropdown('');
    const listEl = document.getElementById('quick-item-dropdown-list');
    if (listEl) {
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

function hideQuickItemDropdownDelayed() {
    setTimeout(() => {
        const listEl = document.getElementById('quick-item-dropdown-list');
        if(listEl) {
            listEl.classList.remove('show');
            listEl.style.display = 'none';
        }
    }, 200);
}

function filterQuickItemDropdown() {
    const searchEl = document.getElementById('quick-item-search');
    renderQuickItemDropdown(searchEl.value);
    const listEl = document.getElementById('quick-item-dropdown-list');
    if (listEl) {
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

function handleQuickItemSearchKeydown(event) {
    const listEl = document.getElementById('quick-item-dropdown-list');
    const searchEl = document.getElementById('quick-item-search');
    const typedVal = searchEl ? searchEl.value.trim().toLowerCase() : '';

    if (event.key === 'Enter' || event.key === 'Tab') {
        event.preventDefault();

        // 1. Exact match by barcode or name
        if (typedVal !== '') {
            const exactItem = ITEMS.find(i => (i.name && i.name.toLowerCase() === typedVal) || (i.barcode && i.barcode.toLowerCase() === typedVal));
            if (exactItem) {
                selectQuickItem(exactItem.id);
                return;
            }
        }

        // 2. Dropdown item
        if (listEl && listEl.style.display !== 'none') {
            const items = Array.from(listEl.querySelectorAll('.custom-dropdown-item'));
            if (items.length > 0) {
                let activeIdx = items.findIndex(el => el.classList.contains('active'));
                const selectedEl = (activeIdx !== -1) ? items[activeIdx] : items[0];
                if (selectedEl) {
                    const itemId = selectedEl.getAttribute('data-item-id');
                    if (itemId) {
                        selectQuickItem(itemId);
                        return;
                    }
                }
            }
        }

        // 3. Partial match
        if (typedVal !== '') {
            const partialItem = ITEMS.find(i => i.name && i.name.toLowerCase().includes(typedVal));
            if (partialItem) {
                selectQuickItem(partialItem.id);
                return;
            }
        }
        return;
    }

    if (!listEl || listEl.style.display === 'none') {
        if (event.key === 'ArrowDown') {
            showQuickItemDropdown();
            event.preventDefault();
        }
        return;
    }

    const items = Array.from(listEl.querySelectorAll('.custom-dropdown-item'));
    if (items.length === 0) return;

    let activeIdx = items.findIndex(el => el.classList.contains('active'));

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (activeIdx !== -1) items[activeIdx].classList.remove('active');
        activeIdx = (activeIdx + 1) % items.length;
        items[activeIdx].classList.add('active');
        items[activeIdx].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (activeIdx !== -1) items[activeIdx].classList.remove('active');
        activeIdx = (activeIdx - 1 + items.length) % items.length;
        items[activeIdx].classList.add('active');
        items[activeIdx].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'Escape') {
        listEl.style.display = 'none';
        listEl.classList.remove('show');
    }
}

function selectQuickItem(itemId) {
    let targetRow = rows.find(r => !r.itemId);
    if (!targetRow) {
        addRow(true, true);
        targetRow = rows[rows.length - 1];
    }
    
    const searchEl = document.getElementById('quick-item-search');
    if (searchEl) searchEl.value = '';
    const listEl = document.getElementById('quick-item-dropdown-list');
    if (listEl) { listEl.classList.remove('show'); listEl.style.display = 'none'; }
    
    selectRowItem(targetRow.id, itemId);
}

// ════════════════════════════════════════
//  WAREHOUSE CHANGE
// ════════════════════════════════════════
function onMainWarehouseChange() {
    // Recalc available qty for rows using fallback warehouse
    renderGrid();
}

// ════════════════════════════════════════
//  ROW MANAGEMENT
// ════════════════════════════════════════
function addRow(silent = false, skipRender = false) {
    let quiet = silent;
    rowIdCounter++;
    rows.push({
        id: rowIdCounter,
        itemId: '',
        quantity: 0,
        price: 0,
        warehouseId: '',
        costCenterId: '',
        qrCode: '',
        selectedUnit: ''
    });
    if (!skipRender) renderGrid();
    if (!quiet) showToast('�� تم إضافة صنف جديد', 'success', 1500);
}

function checkAutoAddRow(rowId) {
    // Disabled auto-adding row: new rows should only be added explicitly by clicking the plus (+) button
    return;
}

function removeRow(id, quiet = false) {
    rows = rows.filter(r => r.id !== id);
    renderGrid();
    recalcAll();
    if (!quiet) showToast('🗑️ تم حذف الصنف', 'warning', 1500);
}

function updateRow(id, field, value) {
    const row = rows.find(r => r.id === id);
    if (!row) return;

    if (field === "quantity" || field === "price" || field === "customFactor" || field === "fcPrice" || field === "customBaseQty") {
        row[field] = (value === "" || isNaN(parseNum(value))) ? "" : parseNum(value);
        if (field === "price") {
            const exRate = parseNum(document.getElementById("hdr-exchange-rate").value) || 1;
            const isBaseSDG = document.getElementById("hdr-currency").value === "SDG";
            if (isBaseSDG) {
                row.fcPrice = row.price / exRate;
            } else {
                row.fcPrice = row.price * exRate;
            }
        }
        if (field === "customBaseQty") {
            let item = ITEMS.find(i => String(i.id) === String(row.itemId));
            let factor = (row.customFactor !== undefined && row.customFactor !== null && row.customFactor !== "") ? parseFloat(row.customFactor) : 1;
            if (item && row.selectedUnit) {
                const units = extractItemUnits(item);
                const su = units.find(u => u.name === row.selectedUnit);
                if (su) factor = su.factor;
            }
            const conv = item ? (item.conversionFactor || 1) : 1;
            if (factor * conv > 0) {
                row.quantity = row.customBaseQty / (factor * conv);
            }
        }
    } else {
        row[field] = value;
        if (field === "itemId") {
            row.customBaseQty = undefined;
                        let item = ITEMS.find(i => String(i.id) === String(value));
            if (!item) {
                try {
                    const rawItems = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                    const raw = rawItems.find(i => String(i.id) === String(value));
                    if (raw) {
                        item = raw;
                        // BUG FIX: ensure item is in ITEMS so renderGrid can find it
                        item.unitsArray = extractItemUnits(item);
                        if (!ITEMS.find(i => String(i.id) === String(item.id))) ITEMS.push(item);
                    }
                } catch(e) {}
            }
            if (item) {
                const units = extractItemUnits(item);
                item.unitsArray = units;
                row.price = parseFloat(item.sellingPrice || item.price || 0) || 0;
                if (units && units.length > 0) {
                    row.selectedUnit = units[0].name;
                    row.customFactor = units[0].factor;
                    if (units[0].price && units[0].price > 0) {
                        row.price = units[0].price;
                    }
                } else {
                    row.selectedUnit = item.unit || item.baseUnit || "قطعة";
                    row.customFactor = 1;
                }
            } else {
                row.selectedUnit = '';
                row.customFactor = 1;
            }
                } else if (field === "selectedUnit") {
            row.customBaseQty = undefined;
            let item = ITEMS.find(i => String(i.id) === String(row.itemId));
            if (!item) {
                try {
                    const rawItems = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                    item = rawItems.find(i => String(i.id) === String(row.itemId));
                } catch(e) {}
            }
            if (item) {
                const units = extractItemUnits(item);
                const su = units.find(u => u.name === value || u.unitName === value);
                if (su) {
                    row.customFactor = (su.factor !== undefined && su.factor !== null && su.factor !== "") ? parseFloat(su.factor) : 1;
                    if (su.price && su.price > 0) {
                        row.price = su.price;
                    } else if (su.cashPrice && su.cashPrice > 0) {
                        row.price = su.cashPrice;
                    }
                }
            }
            updateRowCalcs(id);
            recalcAll();
        }
    }
    // Live update without full re-render (keeps focus)
    updateRowCalcs(id);
    recalcAll();
}

// ════════════════════════════════════════
//  ROW CALCULATIONS (Live, no re-render)
// ════════════════════════════════════════
function getResolvedWarehouse(row) {
    return row.warehouseId || document.getElementById('hdr-warehouse').value;
}

function getRowCalcs(row) {
    const item = ITEMS.find(i => String(i.id) === String(row.itemId));
    
    let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
    let selectedUnitName = item ? item.unit : '—';

    if (row.customFactor === undefined && item && row.selectedUnit && item.unitsArray && item.unitsArray.length > 0) {
        const su = item.unitsArray.find(u => u.name === row.selectedUnit);
        if (su) selectedUnitFactor = su.factor;
    }

    if (row.selectedUnit) {
        selectedUnitName = row.selectedUnit;
    }

    const total = row.quantity * row.price;

    let baseQty = 0;
    if (row.customBaseQty !== undefined) {
        baseQty = row.customBaseQty;
    } else {
        baseQty = item ? row.quantity * selectedUnitFactor * item.conversionFactor : 0;
    }
    const bundles = item ? row.quantity * item.bundleFactor : 0;
    
    const shilaFactor = item && item.bundleFactor > 0 ? item.bundleFactor : 1;
    const shilaQty = baseQty ? Math.floor(baseQty / shilaFactor) : 0;
    const shilaRem = baseQty ? (baseQty % shilaFactor) : 0;

    const whId = getResolvedWarehouse(row);
    let availQty = '—';
    if (item) {
        if (item.stock && item.stock[whId] !== undefined) {
            availQty = item.stock[whId];
        } else if (item.actualStock && item.actualStock[whId] !== undefined) {
            const act = item.actualStock[whId];
            const res = (item.reservedStock && item.reservedStock[whId] !== undefined) ? item.reservedStock[whId] : 0;
            availQty = act - res;
        }
    }
    return { total, baseQty, bundles, shilaQty, shilaRem, availQty, unit: selectedUnitName, unitFactor: selectedUnitFactor };
}

function updateRowCalcs(id) {
    const row = rows.find(r => r.id === id);
    if (!row) return;
    const calcs = getRowCalcs(row);

    // Desktop
    const totalEl = document.querySelector(`[data-total="${id}"]`);
    const baseEl = document.querySelector(`[data-base-input="${id}"]`);
    const availEl = document.querySelector(`[data-avail="${id}"]`);
    const remEl = document.querySelector(`[data-rem="${id}"]`);
    const bundleEl = document.querySelector(`[data-bundle="${id}"]`);
    const shilaEl = document.querySelector(`[data-shila="${id}"]`);
    const shilaRemEl = document.querySelector(`[data-shila-rem="${id}"]`);
    const priceEl = document.querySelector(`[data-price-input="${id}"]`);
    const fcPriceEl = document.querySelector(`[data-fcprice-input="${id}"]`);
    const factorEl = document.querySelector(`[data-factor-input="${id}"]`);

    if (totalEl) totalEl.textContent = fmtNum(calcs.total, 'col_total');
    if (factorEl && document.activeElement !== factorEl) factorEl.value = fmtNum(calcs.unitFactor, 'col_factor');
    if (baseEl && document.activeElement !== baseEl) baseEl.value = fmtNum(calcs.baseQty, 'col_baseqty');
    if (bundleEl) bundleEl.textContent = fmtNum(calcs.bundles);
    if (shilaEl) shilaEl.textContent = fmtNum(calcs.shilaQty, 'col_shila');
    if (shilaRemEl) shilaRemEl.textContent = fmtNum(calcs.shilaRem, 'col_shila_rem');
    if (priceEl && document.activeElement !== priceEl) priceEl.value = fmtNum(row.price, 'col_price');

    if (availEl) {
        availEl.textContent = calcs.availQty === '—' ? '—' : fmtNum(calcs.availQty, 'col_avail');
        availEl.className = 'g-readonly ' + (calcs.availQty !== '—' && calcs.availQty < row.quantity ? 'warn-val' : 'avail-val');
    }
    if (remEl) {
        remEl.textContent = calcs.availQty === '—' ? '—' : fmtNum(calcs.availQty - calcs.baseQty, '7');
        remEl.className = 'g-readonly ' + (calcs.availQty !== '—' && (calcs.availQty - calcs.baseQty) < 0 ? 'warn-val' : 'rem-val');
    }

    // Mobile
    const mTotal = document.querySelector(`[data-m-total="${id}"]`);
    if (mTotal) mTotal.textContent = fmtNum(calcs.total, 'col_total') + ' ' + getCurrency();

    const mFcPriceEl = document.querySelector(`[data-m-fcprice-input="${id}"]`);
    const mTotalFcEl = document.querySelector(`[data-m-total-fc="${id}"]`);
    if (mFcPriceEl && document.activeElement !== mFcPriceEl) mFcPriceEl.value = fmtNum(computedFcPrice, 'col_fcprice');
    if (mTotalFcEl) mTotalFcEl.textContent = fmtNum(computedFcPrice * (row.quantity || 0), 'col_fctotal');

    const mShilaEl = document.querySelector(`[data-m-shila="${id}"]`);
    const mShilaRemEl = document.querySelector(`[data-m-shila-rem="${id}"]`);
    if (mShilaEl) mShilaEl.textContent = fmtNum(calcs.shilaQty, 'col_shila');
    if (mShilaRemEl) mShilaRemEl.textContent = fmtNum(calcs.shilaRem, 'col_shila_rem');
}

function getCurrency() {
    return document.getElementById('hdr-currency').value;
}

// ════════════════════════════════════════
//  RENDER GRID
// ════════════════════════════════════════
function renderGrid() {
    const tbody = document.getElementById('grid-body');
    const mCards = document.getElementById('mobile-cards');
    const empty = document.getElementById('empty-state');

    tbody.innerHTML = '';
    mCards.innerHTML = '';

    if (rows.length === 0) {
        addRow(true);
        return;
    }
    empty.style.display = 'none';

    rows.forEach((row, idx) => {
        const item = ITEMS.find(i => String(i.id) === String(row.itemId));
        const calcs = getRowCalcs(row);

        const unitsList = extractItemUnits(item);
        let itemUnitsHtml = '';
        if (unitsList && unitsList.length > 0) {
            itemUnitsHtml = unitsList.map(u => `<option value="${u.name}" ${row.selectedUnit === u.name ? 'selected' : ''}>${u.name}</option>`).join('');
        } else {
            itemUnitsHtml = `<option value="${item ? (item.unit || item.baseUnit || 'قطعة') : ''}">${item ? (item.unit || item.baseUnit || '—') : '—'}</option>`;
        }

        // ── Desktop Row ──
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td data-col-id="col_num"><div class="row-num">${idx + 1}</div></td>
            <td data-col-id="col_item" style="position:relative;">
                <input type="text" class="g-input" id="item-search-${row.id}" placeholder="ابحث أو اختر الصنف..." 
                    value="${item ? item.name : ''}" autocomplete="one-time-code"
                    style="width: 100%;"
                    onfocus="this.select(); showItemDropdown(${row.id})" onkeydown="handleItemSearchKeydown(event, ${row.id})" 
                    onblur="hideItemDropdownDelayed(${row.id})" 
                    oninput="filterItemDropdown(${row.id})">
                <div class="custom-dropdown-list" id="item-dropdown-list-${row.id}" style="text-align:right; min-width: 220px; white-space: normal;">
                    <!-- populated by JS -->
                </div>
            </td>
            <td data-col-id="col_unit">
                <select class="g-select" data-unit-select="${row.id}" style="min-width: 80px;" onchange="updateRow(${row.id},'selectedUnit',this.value); renderGrid();" tabindex="-1">
                    ${itemUnitsHtml}
                </select>
            </td>
            <td data-col-id="col_factor">
                <input type="text" inputmode="decimal" class="g-input" data-factor-input="${row.id}" value="${fmtNum(calcs.unitFactor, 'col_factor')}"
                    onfocus="this.select()" oninput="updateRow(${row.id},'customFactor',this.value)"
                    onblur="this.value=parseNum(this.value)||1; checkAutoAddRow(${row.id})" style="text-align:center;">
            </td>
            <td data-col-id="col_qty"><input type="text" inputmode="decimal" class="g-input" value="${row.quantity || ''}" placeholder="0"
                onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)"
                onblur="this.value=parseNum(this.value)||''; checkAutoAddRow(${row.id})"></td>
            <td data-col-id="col_price">
                <div style="display:flex; align-items:center; gap:4px;">
                    <input type="text" inputmode="decimal" class="g-input" data-price-input="${row.id}" value="${fmtNum(row.price, 'col_price')}"
                        onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                        onblur="this.value=fmtNum(parseNum(this.value)); checkAutoAddRow(${row.id})" style="flex:1;">
                    <button class="btn-icon" onclick="showPriceHistory(${row.id}, 'sales')" title="سجل أسعار الصنف للعميل" style="color:#38bdf8; background:rgba(56,189,248,0.1); border-radius:4px; padding:4px; border:1px solid rgba(56,189,248,0.2); cursor:pointer;" tabindex="-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </button>
                </div>
            </td>
            <td data-col-id="col_baseqty">
                <div style="display:flex; align-items:center; gap:4px; justify-content:center;">
                    <input type="text" inputmode="decimal" class="g-input" data-base-input="${row.id}" value="${fmtNum(calcs.baseQty, 'col_baseqty')}" placeholder="0"
                        onfocus="this.select()" oninput="updateRow(${row.id},'customBaseQty',this.value)"
                        onblur="this.value=fmtNum(parseNum(this.value))" style="width:100%;" tabindex="-1">
                    <span style="font-size:11px; color:var(--text-muted); white-space:nowrap;">${item ? item.baseUnit : ''}</span>
                </div>
            </td>
            <td data-col-id="col_avail"><span class="g-readonly" data-avail="${row.id}" style="display:block; text-align:center;">${calcs.availQty !== '—' ? fmtNum(calcs.availQty, 'col_avail') : '—'}</span></td>
            <td data-col-id="col_wh" class="td-wh">
                <select class="g-select" onchange="updateRow(${row.id},'warehouseId',this.value)" tabindex="-1">
                    <option value="">نفس الفاتورة ↩</option>
                    ${WAREHOUSES.map(w => `<option value="${w.id}" ${w.id === row.warehouseId ? 'selected' : ''}>${w.name}</option>`).join('')}
                </select>
            </td>
            <td data-col-id="col_note">
                <input type="text" class="g-input" value="${row.note || ''}" placeholder="ملاحظة..."
                    oninput="updateRow(${row.id},'note',this.value)" tabindex="-1">
            </td>
            <td data-col-id="col_total"><span class="g-readonly total-val" style="display:block; text-align:center;" data-total="${row.id}">${fmtNum(calcs.total, 'col_total')}</span></td>
            <td data-col-id="col_del">
                <div style="display: flex; gap: 5px; justify-content: center;">
                    <button class="edit-btn" onclick="document.getElementById('item-search-${row.id}').focus()" title="تعديل السطر" style="background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.2); color: #38bdf8; border-radius: 6px; padding: 6px; cursor: pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="del-btn" onclick="removeRow(${row.id})" title="حذف السطر">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </td>
        `;
        if (window.reorderTds) window.reorderTds(tr);
        tbody.appendChild(tr);

        // ── Mobile Card ──
        const card = document.createElement('div');
        card.className = 'm-card';
        card.innerHTML = `
            <div class="m-card-num">${idx + 1}</div>
            <div style="position: absolute; left: 10px; top: 10px; display: flex; gap: 5px;">
                <button class="m-card-add" onclick="addRow()" title="إضافة سطر" style="background: transparent; border: none; color: #34d399; cursor: pointer;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
                <button class="m-card-del" onclick="removeRow(${row.id})" title="حذف">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </div>
            <div class="ctrl-group m-card-full" style="padding-top:4px; position:relative;">
                <label class="label">الصنف</label>
                <input type="text" class="ctrl-input" id="m-item-search-${row.id}" placeholder="ابحث أو اختر الصنف..." 
                    value="${item ? item.name : ''}" autocomplete="one-time-code"
                    onfocus="this.select(); showItemDropdown(${row.id}, true)" 
                    onblur="hideItemDropdownDelayed(${row.id}, true)" 
                    oninput="filterItemDropdown(${row.id}, true)">
                <div class="custom-dropdown-list" id="m-item-dropdown-list-${row.id}" style="text-align:right;">
                    <!-- populated by JS -->
                </div>
            </div>
            <div class="m-card-grid">
                <div class="ctrl-group">
                    <label class="label">الوحدات</label>
                    <select class="ctrl-input" data-m-unit-select="${row.id}" onchange="updateRow(${row.id},'selectedUnit',this.value); renderGrid();" tabindex="-1">
                        ${itemUnitsHtml}
                    </select>
                </div>
                <div class="ctrl-group">
                    <label class="label">التعادل</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" value="${row.customFactor !== undefined ? row.customFactor : calcs.unitFactor}" placeholder="1"
                        onfocus="this.select()" oninput="updateRow(${row.id},'customFactor',this.value)"
                        onblur="this.value=parseNum(this.value)||1" tabindex="-1">
                </div>
                <div class="ctrl-group">
                    <label class="label">الكمية الوحدة</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" data-qty-input="${row.id}" value="${Number.isInteger(row.quantity) ? row.quantity : parseFloat((row.quantity || 0).toFixed(4)) || ''}" placeholder="0"
                        onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)" onblur="checkAutoAddRow(${row.id})">
                </div>
                <div class="ctrl-group">
                    <label class="label">السعر (محلي / أجنبي) <button class="btn-icon" onclick="showPriceHistory(${row.id}, 'sales')" title="سجل أسعار الصنف للعميل" style="color:#38bdf8; background:none; border:none; cursor:pointer; padding:0; margin-right:4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></button></label>
                    <div style="display:flex; gap:4px;">
                        <input type="text" inputmode="decimal" class="ctrl-input" value="${fmtNum(row.price, 'col_price')}" placeholder="0"
                            onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                            onblur="this.value=fmtNum(parseNum(this.value)); checkAutoAddRow(${row.id})" style="flex:1;">
                        <input type="text" inputmode="decimal" class="ctrl-input" data-m-fcprice-input="${row.id}" value="${fmtNum(row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1)))}" placeholder="${document.getElementById('hdr-currency').value === 'SDG' ? 'أجنبي' : 'جنيه'}"
                            onfocus="this.select()" oninput="updateRow(${row.id},'fcPrice',this.value)"
                            onblur="this.value=fmtNum(parseNum(this.value)); checkAutoAddRow(${row.id})" style="flex:1; color:#10b981;">
                    </div>
                </div>
                <div class="ctrl-group">
                    <label class="label fc-total-label">${document.getElementById('hdr-currency').value === 'SDG' ? 'جملة الوحدة أجنبي' : 'جملة الوحدة جنيه'}</label>
                    <div class="ctrl-input readonly" data-m-total-fc="${row.id}" style="text-align:center;color:#10b981;">${fmtNum((row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1))) * (row.quantity || 0))}</div>
                </div>
                <div class="ctrl-group">
                    <label class="label">عدد ${item ? item.baseUnit : ''}</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" data-base-input="${row.id}" value="${fmtNum(calcs.baseQty, 'col_baseqty')}" placeholder="0"
                        onfocus="this.select()" oninput="updateRow(${row.id},'customBaseQty',this.value)"
                        onblur="this.value=fmtNum(parseNum(this.value))" style="color:var(--primary-accent); font-weight:700;" tabindex="-1">
                </div>
                <div class="ctrl-group m-wh-group">
                    <label class="label">المخزن</label>
                    <select class="ctrl-input" onchange="updateRow(${row.id},'warehouseId',this.value)" tabindex="-1">
                        <option value="">الرئيسي ↩</option>
                        ${WAREHOUSES.map(w => `<option value="${w.id}" ${w.id === row.warehouseId ? 'selected' : ''}>${w.name}</option>`).join('')}
                    </select>
                </div>
            </div>
            <div style="padding: 10px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 8px;">
                <div class="ctrl-group">
                    <label class="label">ملحوظة</label>
                    <input type="text" class="ctrl-input" placeholder="ملحوظة..." value="${row.note || ''}" oninput="updateRow(${row.id},'note',this.value)">
                </div>
                <div style="display:flex; gap:8px;">
                    <div class="ctrl-group" style="flex:1;">
                        <label class="label">سعر الشراء</label>
                        <input type="text" inputmode="decimal" class="ctrl-input" placeholder="0" value="${row.buyPrice !== undefined ? fmtNum(row.buyPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'buyPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
                    </div>
                    <div class="ctrl-group" style="flex:1;">
                        <label class="label">سعر البيع</label>
                        <input type="text" inputmode="decimal" class="ctrl-input" placeholder="0" value="${row.sellPrice !== undefined ? fmtNum(row.sellPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'sellPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" >
                    </div>
                </div>
            </div>
            <div class="m-card-total" data-m-total="${row.id}">${fmtNum(calcs.total, 'col_total')} ${getCurrency()}</div>
        `;
        mCards.appendChild(card);
    });

    if (typeof applyGridSettings === 'function') {
        applyGridSettings();
    }

    // FINAL PASS: Force-populate all unit selects after DOM reordering
    // This guarantees units are always visible after any grid rebuild
    rows.forEach(row => {
        if (!row.itemId) return;
        let item = ITEMS.find(i => String(i.id) === String(row.itemId));
        if (!item) {
            try {
                const rawItems = (window._PRELOADED_ITEMS || JSON.parse(localStorage.getItem('erp_items_v1')) || []);
                const raw = rawItems.find(i => String(i.id) === String(row.itemId));
                if (raw) item = raw;
            } catch(e) {}
        }
        if (!item) return;
        const units = extractItemUnits(item);
        if (!units || units.length === 0) return;
        
        const sel = document.querySelector(`select[data-unit-select="${row.id}"]`);
        if (sel) {
            sel.innerHTML = units.map(u => 
                `<option value="${u.name}" ${row.selectedUnit === u.name ? 'selected' : ''}>${u.name}</option>`
            ).join('');
            if (row.selectedUnit) sel.value = row.selectedUnit;
        }

        const mSel = document.querySelector(`select[data-m-unit-select="${row.id}"]`);
        if (mSel) {
            mSel.innerHTML = units.map(u => 
                `<option value="${u.name}" ${row.selectedUnit === u.name ? 'selected' : ''}>${u.name}</option>`
            ).join('');
            if (row.selectedUnit) mSel.value = row.selectedUnit;
        }
    });
}

// ════════════════════════════════════════
//  ADJUSTMENTS (Discount / Tax / Addition)
// ════════════════════════════════════════
function getInvoiceTotal() {
    return rows.reduce((s, r) => s + (parseNum(r.quantity || 0) * parseNum(r.price || 0)), 0);
}

function onAdjPctChange(type) {
    const pct = parseFloat(document.getElementById(`${type}-pct`).value) || 0;
    const invTotal = getInvoiceTotal();
    const amt = invTotal * (pct / 100);
    document.getElementById(`${type}-amt`).value = fmtNum(amt);
    recalcAll();
}

function onAdjAmtChange(type) {
    // When amount is typed directly, clear the percentage
    document.getElementById(`${type}-pct`).value = '';
    recalcAll();
}

// ════════════════════════════════════════
//  GRAND TOTAL CALCULATION
// ════════════════════════════════════════
function recalcAll() {
    const cur = getCurrency();
    const invTotal = getInvoiceTotal();
    const labor = parseNum(document.getElementById('t-labor')?.value || 0);
    const transport = parseNum(document.getElementById('t-transport')?.value || 0);
    const discountAmt = parseNum(document.getElementById('discount-amt')?.value || 0);
    const taxAmt = parseNum(document.getElementById('tax-amt')?.value || 0);
    const additionAmt = parseNum(document.getElementById('addition-amt')?.value || 0);

    const grand = invTotal + labor + transport + additionAmt + taxAmt - discountAmt;

    // Update totals display
    const grandEl = document.getElementById('t-grand');
    if (grandEl) {
        grandEl.textContent = grand.toString();
    }
    const invTotEl = document.getElementById('t-invoice-total');
    if (invTotEl) {
        invTotEl.textContent = fmtNum(invTotal) + ' ' + cur;
    }
    const footerGrandEl = document.getElementById('footer-grand');
    if (footerGrandEl) {
        footerGrandEl.textContent = fmtNum(grand) + ' ' + cur;
    }
    const dynLoc = document.getElementById('t-grand-dynamic-loc');
    if (dynLoc) {
        const cleanVal = parseNum(grand) || 0;
        dynLoc.textContent = Math.round(cleanVal).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
    const dynBadge = document.getElementById('t-grand-currency-badge');
    if (dynBadge) {
        dynBadge.textContent = cur;
    }

    if (typeof window.syncTotalsVisibility === 'function') {
        window.syncTotalsVisibility();
    }

    // Dynamic UI Animation — toggle CSS classes
    const totalsCard = document.getElementById('totals-calc-card');
    const formCard = document.getElementById('form-fields-card');
    if (totalsCard && formCard) {
        const hasItems = rows.some(r => (r && r.itemId) || parseFloat(r.price) > 0 || parseFloat(r.quantity) > 0);

        if (hasItems) {
            // Remove hiding classes, add showing classes
            totalsCard.classList.remove('hidden-totals');
            formCard.classList.remove('expanded-form');
        } else {
            // Add hiding classes, add expanded class
            totalsCard.classList.add('hidden-totals');
            formCard.classList.add('expanded-form');
        }
    }
    
    // Recalculate payment details whenever invoice totals change
    recalcPaymentSummary();
}

// ════════════════════════════════════════
//  MULTI-PAYMENT SYSTEM FUNCTIONS
// ════════════════════════════════════════
function populatePaymentAccounts() {
    try {
        let safes = [];
        let banks = [];
        try {
            safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
            banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        } catch(e) {}
        
        safes = safes.filter(x => x && !x.deleted);
        banks = banks.filter(x => x && !x.deleted);
        
        window.PAYMENT_SAFES = safes;
        window.PAYMENT_BANKS = banks;
        
        const select = document.getElementById('hdr-payment-account');
        if (select) {
            let html = '';
            safes.forEach(s => {
                html += `<option value="${s.id}">💰 ${s.name}</option>`;
            });
            if (!html) {
                html = '<option value="">-- لا توجد صناديق نقدية --</option>';
            }
            select.innerHTML = html;
        }
    } catch(e) {
        console.error("Error in populatePaymentAccounts:", e);
    }
}

function initMultiPaymentSystem() {
    try {
        // Set default due date to 30 days from now
        const dueInput = document.getElementById('pay-credit-due-date');
        if (dueInput) {
            const nextMonth = new Date();
            nextMonth.setDate(nextMonth.getDate() + 30);
            dueInput.value = nextMonth.toISOString().split('T')[0];
        }
        
        // Default to Credit active (الآجل افتراضياً)
        const cashBtn = document.getElementById('toggle-cash');
        const creditBtn = document.getElementById('toggle-credit');
        const bankBtn = document.getElementById('toggle-bank');
        const chequeBtn = document.getElementById('toggle-cheque');

        if (creditBtn) creditBtn.classList.add('active');
        if (cashBtn) cashBtn.classList.remove('active');
        if (bankBtn) bankBtn.classList.remove('active');
        if (chequeBtn) chequeBtn.classList.remove('active');
        
        // Reset inputs
        if (document.getElementById('pay-cash-amount')) document.getElementById('pay-cash-amount').value = '0';
        if (document.getElementById('pay-cash-ref')) document.getElementById('pay-cash-ref').value = '';
        if (document.getElementById('pay-cash-note')) document.getElementById('pay-cash-note').value = '';
        if (document.getElementById('pay-credit-amount')) document.getElementById('pay-credit-amount').value = '0';
        if (document.getElementById('bank-transfers-list')) document.getElementById('bank-transfers-list').innerHTML = '';
        if (document.getElementById('cheques-list')) document.getElementById('cheques-list').innerHTML = '';
        
        populatePaymentAccounts();
        updatePaymentSectionsVisibility();
        recalcPaymentSummary();
    } catch(e) {
        console.error("Error in initMultiPaymentSystem:", e);
    }
}

function togglePaymentSection(type) {
    if (type === 'credit') {
        const btn = document.getElementById('toggle-credit');
        if (btn) {
            const isActive = btn.classList.contains('active');
            if (isActive) {
                btn.classList.remove('active');
                if (document.getElementById('pay-credit-amount')) document.getElementById('pay-credit-amount').value = '0';
            } else {
                btn.classList.add('active');
                if (typeof autofillRemaining === 'function') autofillRemaining('credit');
            }
        }
        updatePaymentSectionsVisibility();
        recalcPaymentSummary();
        return;
    }

    const btn = document.getElementById('toggle-' + type);
    if (!btn) return;
    
    const isActive = btn.classList.contains('active');
    
    // Toggle active state class
    if (isActive) {
        btn.classList.remove('active');
        // Reset values on deactivation
        if (type === 'cash') {
            if (document.getElementById('pay-cash-amount')) document.getElementById('pay-cash-amount').value = '0';
        } else if (type === 'bank') {
            if (document.getElementById('bank-transfers-list')) document.getElementById('bank-transfers-list').innerHTML = '';
        } else if (type === 'cheque') {
            if (document.getElementById('cheques-list')) document.getElementById('cheques-list').innerHTML = '';
        }
    } else {
        btn.classList.add('active');
        // Auto-fill remaining into this section
        if (typeof autofillRemaining === 'function') {
            autofillRemaining(type);
        }
    }
    
    // If all toggles are turned off, default back to Credit (آجل)
    const cashActive = document.getElementById('toggle-cash')?.classList.contains('active');
    const bankActive = document.getElementById('toggle-bank')?.classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque')?.classList.contains('active');
    const creditActive = document.getElementById('toggle-credit')?.classList.contains('active');
    if (!cashActive && !bankActive && !chequeActive && !creditActive) {
        document.getElementById('toggle-credit')?.classList.add('active');
    }
    
    // Update container visibilities
    updatePaymentSectionsVisibility();
    recalcPaymentSummary();
}

function updatePaymentSectionsVisibility() {
    const cashActive = document.getElementById('toggle-cash')?.classList.contains('active') || false;
    const bankActive = document.getElementById('toggle-bank')?.classList.contains('active') || false;
    const chequeActive = document.getElementById('toggle-cheque')?.classList.contains('active') || false;
    const creditActive = document.getElementById('toggle-credit')?.classList.contains('active') || false;
    
    if (document.getElementById('pay-cash-section')) {
        document.getElementById('pay-cash-section').style.display = cashActive ? 'flex' : 'none';
    }
    if (document.getElementById('pay-credit-section')) {
        document.getElementById('pay-credit-section').style.display = creditActive ? 'flex' : 'none';
    }
    if (document.getElementById('pay-bank-section')) {
        document.getElementById('pay-bank-section').style.display = bankActive ? 'flex' : 'none';
    }
    if (document.getElementById('pay-cheque-section')) {
        document.getElementById('pay-cheque-section').style.display = chequeActive ? 'flex' : 'none';
    }

    // Auto-add first row if empty when section becomes active
    if (bankActive) {
        const btList = document.getElementById('bank-transfers-list');
        if (btList && btList.children.length === 0) {
            addBankTransferRow();
        }
    }
    if (chequeActive) {
        const chList = document.getElementById('cheques-list');
        if (chList && chList.children.length === 0) {
            addChequeRow();
        }
    }

    const hasActive = cashActive || bankActive || chequeActive || creditActive;
    const layout = document.getElementById('payment-layout');
    if (layout) {
        layout.style.display = 'flex';
    }

    // Toggle show-totals with smooth animation
    const totalsCard = document.querySelector('.payment-summary-card');
    if (totalsCard) {
        if (hasActive) {
            totalsCard.classList.add('show-totals');
        } else {
            totalsCard.classList.remove('show-totals');
        }
    }
}

function addBankTransferRow(existingData = null) {
    const container = document.getElementById('bank-transfers-list');
    if (!container) return;
    
    const rowId = 'bt-row-' + Date.now() + '-' + Math.random().toString(36).substring(2,5);
    const today = new Date().toISOString().split('T')[0];
    
    let banksHtml = '';
    if (window.PAYMENT_BANKS && window.PAYMENT_BANKS.length > 0) {
        window.PAYMENT_BANKS.forEach(b => {
            const selected = (existingData && (existingData.bankAccountId === b.id || existingData.bankName === b.name)) ? 'selected' : '';
            banksHtml += `<option value="${b.id}" ${selected}>🏦 ${b.name}</option>`;
        });
    } else {
        banksHtml = '<option value="">-- لا توجد بنوك --</option>';
    }
    
    const isFirst = container.children.length === 0;
    
    const labelHtml = isFirst 
        ? `<div style="display: flex; align-items: center; justify-content: flex-start; color: #67e8f9; font-weight: 900; font-size: 0.95rem; gap: 6px; text-shadow: 0 0 8px rgba(6, 182, 212, 0.4);"><span>🏦</span> بنكي</div>`
        : `<div></div>`;

    const actionBtn = isFirst
        ? `<button type="button" onclick="addBankTransferRow()" style="background: rgba(6,182,212,0.15); border: 1.5px solid #06b6d4; color: #06b6d4; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; padding: 0; outline: none; transition: 0.2s;" onmouseover="this.style.background='rgba(6,182,212,0.3)'" onmouseout="this.style.background='rgba(6,182,212,0.15)'" title="إضافة تحويل بنكي">+</button>`
        : `<button type="button" onclick="document.getElementById('${rowId}').remove(); onPaymentAmountInput();" style="background: rgba(239,68,68,0.15); border: 1.5px solid #ef4444; color: #ef4444; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; padding: 0; padding-bottom: 2px; outline: none; transition: 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.3)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'" title="حذف">×</button>`;

    const inputStyle = "background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 8px; text-align: center; font-size: 1.05rem; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%; transition: all 0.3s ease;";
    const amountStyle = "background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(6, 182, 212, 0.5); border-radius: 8px; text-align: center; font-size: 2.25rem; font-weight: 800; color: #fff; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; transition: all 0.3s ease;";

    const rowHtml = `
        <div class="payment-row-item" id="${rowId}" style="display: grid; grid-template-columns: 85px 1.5fr 1.2fr 1.2fr 2fr 30px; gap: 8px; align-items: center; width: 100%; margin-bottom: 6px;">
            ${labelHtml}
            <input type="number" class="v-input ctrl-input bt-amount-input dynamic-focus-input dynamic-focus-amount" value="${existingData ? Math.round(parseFloat(existingData.amount)) : ''}" step="1" oninput="onPaymentAmountInput()" placeholder="المبلغ" style="${amountStyle}">
            <select class="v-input ctrl-input bt-bank-select" style="${inputStyle}">
                ${banksHtml}
            </select>
            <input type="text" maxlength="20" class="v-input ctrl-input bt-ref-input dynamic-focus-input dynamic-focus-number" value="${existingData?.chequeNo || existingData?.reference || ''}" placeholder="رقم التحويلة" style="${inputStyle}">
            <input type="text" class="v-input ctrl-input bt-note-input" value="${existingData?.notes || ''}" placeholder="بيان التحويل" style="${inputStyle}">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                ${actionBtn}
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHtml);

    const newRow = document.getElementById(rowId);
    if (newRow) {
        const bankSelect = newRow.querySelector('.bt-bank-select');
        if (bankSelect && !existingData && window.PAYMENT_BANKS && window.PAYMENT_BANKS.length > 0) {
            bankSelect.value = window.PAYMENT_BANKS[0].id;
        }
    }

    if (!existingData) {
        onPaymentAmountInput();
    }
}

function addChequeRow(existingData = null) {
    const container = document.getElementById('cheques-list');
    if (!container) return;
    
    const rowId = 'ch-row-' + Date.now() + '-' + Math.random().toString(36).substring(2,5);
    const today = new Date().toISOString().split('T')[0];
    
    let banksHtml = '';
    if (window.PAYMENT_BANKS && window.PAYMENT_BANKS.length > 0) {
        window.PAYMENT_BANKS.forEach(b => {
            const selected = (existingData && (existingData.bankAccountId === b.id || existingData.bankName === b.name)) ? 'selected' : '';
            banksHtml += `<option value="${b.id}" ${selected}>🏦 ${b.name}</option>`;
        });
    } else {
        banksHtml = '<option value="">-- لا توجد بنوك --</option>';
    }

    const isFirst = container.children.length === 0;

    const labelHtml = isFirst 
        ? `<div style="display: flex; align-items: center; justify-content: flex-start; color: #d8b4fe; font-weight: 900; font-size: 0.95rem; gap: 6px; text-shadow: 0 0 8px rgba(168, 85, 247, 0.4);"><span>✍️</span> شيك</div>`
        : `<div></div>`;

    const actionBtn = isFirst
        ? `<button type="button" onclick="addChequeRow()" style="background: rgba(168,85,247,0.15); border: 1.5px solid #a855f7; color: #a855f7; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; padding: 0; outline: none; transition: 0.2s;" onmouseover="this.style.background='rgba(168,85,247,0.3)'" onmouseout="this.style.background='rgba(168,85,247,0.15)'" title="إضافة شيك">+</button>`
        : `<button type="button" onclick="document.getElementById('${rowId}').remove(); onPaymentAmountInput();" style="background: rgba(239,68,68,0.15); border: 1.5px solid #ef4444; color: #ef4444; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; padding: 0; padding-bottom: 2px; outline: none; transition: 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.3)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'" title="حذف">×</button>`;

    const inputStyle = "background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 8px; text-align: center; font-size: 1.05rem; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; width: 100%; transition: all 0.3s ease;";
    const amountStyle = "background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(168, 85, 247, 0.5); border-radius: 8px; text-align: center; font-size: 2.25rem; font-weight: 800; color: #fff; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; transition: all 0.3s ease;";

    const rowHtml = `
        <div class="payment-row-item" id="${rowId}" style="display: grid; grid-template-columns: 85px 1.5fr 1.2fr 1.2fr 1fr 1fr 30px; gap: 8px; align-items: center; width: 100%; margin-bottom: 6px;">
            ${labelHtml}
            <input type="number" class="v-input ctrl-input ch-amount-input dynamic-focus-input dynamic-focus-amount" value="${existingData ? Math.round(parseFloat(existingData.amount)) : ''}" step="1" oninput="onPaymentAmountInput()" onblur="const v=parseNum(this.value); const cont=document.getElementById('cheques-list'); if((!this.value || v<=0) && cont && cont.querySelectorAll('.payment-row-item').length>1){ this.closest('.payment-row-item').remove(); onPaymentAmountInput(); }" placeholder="المبلغ" style="${amountStyle}">
            <select class="v-input ctrl-input ch-bank-select" style="${inputStyle}">
                ${banksHtml}
            </select>
            <input type="text" maxlength="20" class="v-input ctrl-input ch-no-input dynamic-focus-input dynamic-focus-number" value="${existingData?.chequeNo || ''}" placeholder="رقم الشيك" style="${inputStyle}">
            <input type="date" class="v-input ctrl-input ch-date-input" value="${existingData?.dueDate || today}" placeholder="mm/dd/yyyy" style="${inputStyle}">
            <input type="text" class="v-input ctrl-input bt-note-input" value="${existingData?.notes || ''}" placeholder="بيان الشيك" style="${inputStyle}">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                ${actionBtn}
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHtml);
}

// Audio Synthesizer for high-quality audio feedbacks
const PlaySound = {
    ctx: null,
    init() {
        if (!this.ctx) {
            this.ctx = new (window.AudioContext || window.webkitAudioContext)();
        }
    },
    success() {
        try {
            this.init();
            const now = this.ctx.currentTime;
            const osc1 = this.ctx.createOscillator();
            const osc2 = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc1.type = 'sine';
            osc2.type = 'sine';
            osc1.frequency.setValueAtTime(523.25, now); // C5
            osc1.frequency.exponentialRampToValueAtTime(880, now + 0.15); // A5
            osc2.frequency.setValueAtTime(659.25, now); // E5
            osc2.frequency.exponentialRampToValueAtTime(1046.50, now + 0.15); // C6
            gain.gain.setValueAtTime(0.08, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
            osc1.connect(gain);
            osc2.connect(gain);
            gain.connect(this.ctx.destination);
            osc1.start(now);
            osc2.start(now);
            osc1.stop(now + 0.25);
            osc2.stop(now + 0.25);
        } catch(e) {}
    },
    error() {
        try {
            this.init();
            const now = this.ctx.currentTime;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(180, now);
            osc.frequency.linearRampToValueAtTime(120, now + 0.15);
            gain.gain.setValueAtTime(0.12, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
            osc.connect(gain);
            gain.connect(this.ctx.destination);
            osc.start(now);
            osc.stop(now + 0.2);
        } catch(e) {}
    }
};

function recalcPaymentSummary(isRevertCall = false) {
    const cur = getCurrency();
    
    // Calculate grand total from invoice items total
    const invTotal = getInvoiceTotal();
    const labor = parseNum(document.getElementById('t-labor')?.value || 0);
    const transport = parseNum(document.getElementById('t-transport')?.value || 0);
    const discountAmt = parseNum(document.getElementById('discount-amt')?.value || 0);
    const taxAmt = parseNum(document.getElementById('tax-amt')?.value || 0);
    const additionAmt = parseNum(document.getElementById('addition-amt')?.value || 0);
    const grand = invTotal + labor + transport + additionAmt + taxAmt - discountAmt;
    
    // Always keep payment details panel visible
    const paymentPanel = document.getElementById('payment-panel');
    if (paymentPanel) {
        paymentPanel.style.display = 'block';
    }
    
    const cashActive = document.getElementById('toggle-cash')?.classList.contains('active') || false;
    let creditActive = document.getElementById('toggle-credit')?.classList.contains('active') || false;
    const bankActive = document.getElementById('toggle-bank')?.classList.contains('active') || false;
    const chequeActive = document.getElementById('toggle-cheque')?.classList.contains('active') || false;

    // Default rule: If no payment methods chosen, default to Credit (آجل)
    if (!cashActive && !bankActive && !chequeActive && !creditActive) {
        creditActive = true;
        document.getElementById('toggle-credit')?.classList.add('active');
    }
    
    // Calculate payment sums
    const cash = cashActive ? parseNum(document.getElementById('pay-cash-amount')?.value || 0) : 0;
    
    let transfersSum = 0;
    if (bankActive) {
        document.querySelectorAll('.bt-amount-input').forEach(input => {
            transfersSum += parseNum(input.value);
        });
    }
    
    let chequesSum = 0;
    if (chequeActive) {
        document.querySelectorAll('.ch-amount-input').forEach(input => {
            chequesSum += parseNum(input.value);
        });
    }
    
    // Credit is always the remaining balance of the invoice total (shown in yellow)
    const credit = Math.max(0, grand - (cash + transfersSum + chequesSum));
    const creditInput = document.getElementById('pay-credit-amount');
    if (creditInput) {
        creditInput.value = credit > 0 ? (credit % 1 === 0 ? credit.toString() : credit.toFixed(2)) : '0';
    }
    
    // Credit section visibility
    const creditSection = document.getElementById('pay-credit-section');
    if (creditSection) {
        if (credit > 0.005 || creditActive) {
            creditSection.style.display = 'flex';
            creditSection.classList.remove('collapsed');
            document.getElementById('toggle-credit')?.classList.add('active');
        } else {
            creditSection.classList.add('collapsed');
            if (cashActive || bankActive || chequeActive) {
                document.getElementById('toggle-credit')?.classList.remove('active');
            }
        }
    }
    
    const totalPaid = cash + credit + transfersSum + chequesSum;
    const remaining = grand - totalPaid;
    
    // Initialize last valid values on inputs
    const cashInput = document.getElementById('pay-cash-amount');
    if (cashInput && cashInput.dataset.lastValidValue === undefined) {
        cashInput.dataset.lastValidValue = cashInput.value || '0';
    }
    document.querySelectorAll('.bt-amount-input').forEach(input => {
        if (input.dataset.lastValidValue === undefined) {
            input.dataset.lastValidValue = input.value || '0';
        }
    });
    document.querySelectorAll('.ch-amount-input').forEach(input => {
        if (input.dataset.lastValidValue === undefined) {
            input.dataset.lastValidValue = input.value || '0';
        }
    });

    // Check if entered payment amounts exceed the invoice grand total
    const enteredPayments = cash + transfersSum + chequesSum;
    if (enteredPayments > grand + 0.01) {
        showToast('⚠️ المبلغ الذي تم إدخاله أكبر من قيمة الفاتورة!', 'warning');
        if (typeof PlaySound !== 'undefined') PlaySound.error();
        
        // Revert active input to its last valid value
        const activeInput = document.activeElement;
        if (activeInput && (activeInput.id === 'pay-cash-amount' || activeInput.classList.contains('bt-amount-input') || activeInput.classList.contains('ch-amount-input'))) {
            activeInput.value = activeInput.dataset.lastValidValue || '0';
            
            // Re-trigger shake animation
            activeInput.classList.remove('input-error-shake');
            void activeInput.offsetWidth; // trigger reflow
            activeInput.classList.add('input-error-shake');
        }
        
        // Recalculate to correct the indicators using reverted values
        return recalcPaymentSummary(true);
    } else {
        // Save the valid values
        if (cashInput) {
            cashInput.dataset.lastValidValue = cashInput.value;
            if (!isRevertCall) cashInput.classList.remove('input-error-shake');
        }
        document.querySelectorAll('.bt-amount-input').forEach(input => {
            input.dataset.lastValidValue = input.value;
            if (!isRevertCall) input.classList.remove('input-error-shake');
        });
        document.querySelectorAll('.ch-amount-input').forEach(input => {
            input.dataset.lastValidValue = input.value;
            if (!isRevertCall) input.classList.remove('input-error-shake');
        });
    }
    
    // Update displays
    const totInd = document.getElementById('payment-total-indicator');
    const paidInd = document.getElementById('payment-paid-indicator');
    const remInd = document.getElementById('payment-remaining-indicator');
    
    if (totInd) totInd.textContent = fmtNum(grand) + ' ' + cur;
    if (paidInd) paidInd.textContent = fmtNum(totalPaid) + ' ' + cur;
    const remCard = document.getElementById('payment-remaining-card');
    if (remInd) {
        remInd.textContent = fmtNum(remaining) + ' ' + cur;
        if (Math.abs(remaining) < 0.01) {
            remInd.style.color = '#34d399'; // Green
            remInd.style.textShadow = '0 0 12px rgba(52,211,153,0.5)';
            if (remCard) {
                remCard.style.background = 'rgba(16, 185, 129, 0.1)';
                remCard.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            }
        } else if (remaining > 0) {
            remInd.style.color = '#f87171'; // Red
            remInd.style.textShadow = '0 0 12px rgba(248,113,113,0.4)';
            if (remCard) {
                remCard.style.background = 'rgba(239, 68, 68, 0.08)';
                remCard.style.borderColor = 'rgba(239, 68, 68, 0.35)';
            }
        } else {
            remInd.style.color = '#fbbf24'; // Amber
            remInd.style.textShadow = '0 0 12px rgba(251,191,36,0.4)';
            if (remCard) {
                remCard.style.background = 'rgba(245, 158, 11, 0.08)';
                remCard.style.borderColor = 'rgba(245, 158, 11, 0.35)';
            }
        }
    }

    // Update progress bars & status badge dynamically
    const totalPayments = cash + transfersSum + chequesSum + credit;
    if (totalPayments > 0) {
        const cashPct = (cash / totalPayments) * 100;
        const bankPct = (transfersSum / totalPayments) * 100;
        const chequePct = (chequesSum / totalPayments) * 100;
        const creditPct = (credit / totalPayments) * 100;
        
        const elCash = document.getElementById('prog-cash');
        const elBank = document.getElementById('prog-bank');
        const elCheque = document.getElementById('prog-cheque');
        const elCredit = document.getElementById('prog-credit');
        
        if (elCash) elCash.style.width = cashPct + '%';
        if (elBank) elBank.style.width = bankPct + '%';
        if (elCheque) elCheque.style.width = chequePct + '%';
        if (elCredit) elCredit.style.width = creditPct + '%';
        
        // Update Status Badge
        const statusBadge = document.getElementById('payment-status-badge');
        if (statusBadge) {
            if (credit <= 0.01) {
                // Play sound if transitioned to fully paid
                if (statusBadge.dataset.status !== 'fully-paid') {
                    if (typeof PlaySound !== 'undefined') PlaySound.success();
                    statusBadge.dataset.status = 'fully-paid';
                }
                statusBadge.textContent = '💳 مدفوعة بالكامل';
                statusBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                statusBadge.style.color = '#34d399';
                statusBadge.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.2)';
            } else if (cash > 0 || transfersSum > 0 || chequesSum > 0) {
                statusBadge.dataset.status = 'partially-paid';
                statusBadge.textContent = '⚖️ مدفوعة جزئياً';
                statusBadge.style.background = 'rgba(59, 130, 246, 0.15)';
                statusBadge.style.color = '#60a5fa';
                statusBadge.style.boxShadow = '0 0 10px rgba(59, 130, 246, 0.2)';
            } else {
                statusBadge.dataset.status = 'deferred';
                statusBadge.textContent = '⏳ آجلة بالكامل';
                statusBadge.style.background = 'rgba(245, 158, 11, 0.15)';
                statusBadge.style.color = '#fbbf24';
                statusBadge.style.boxShadow = '0 0 10px rgba(245, 158, 11, 0.2)';
            }
        }
    }
}

function onPaymentAmountInput() {
    recalcPaymentSummary();
}

function previewCurrentPromissoryNote() {
    const custSearch = document.getElementById('hdr-customer-search').value;
    const creditAmt = parseNum(document.getElementById('pay-credit-amount').value);
    if (creditAmt <= 0) {
        showToast('⚠️ الرجاء إدخال مبلغ آجل أولاً لمعاينة الكمبيالة', 'warning');
        return;
    }
    
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    const cashAmt = cashActive ? parseNum(document.getElementById('pay-cash-amount').value) : 0;
    
    let bankTransfers = [];
    if (bankActive) {
        document.querySelectorAll('#bank-transfers-list .payment-row-item').forEach(row => {
            const amount = parseNum(row.querySelector('.bt-amount-input').value);
            if (amount > 0) {
                bankTransfers.push({ amount });
            }
        });
    }
    
    let cheques = [];
    if (chequeActive) {
        document.querySelectorAll('#cheques-list .payment-row-item').forEach(row => {
            const amount = parseNum(row.querySelector('.ch-amount-input').value);
            if (amount > 0) {
                cheques.push({ amount });
            }
        });
    }
    
    const tempInv = {
        invoiceNo: document.getElementById('hdr-inv-no').value,
        date: document.getElementById('hdr-date').value,
        dueDate: document.getElementById('pay-credit-due-date').value,
        customerName: custSearch || 'عميل آجل',
        currency: getCurrency(),
        creditAmount: creditAmt,
        cashAmount: cashAmt,
        bankTransfers: bankTransfers,
        cheques: cheques,
        grandTotal: parseNum(document.getElementById('t-grand').textContent)
    };
    
    if (window.InvoicePrinter && typeof window.InvoicePrinter.printPromissoryNote === 'function') {
        window.InvoicePrinter.printPromissoryNote(tempInv);
    }
}

function printCurrentPromissoryNote(invNo) {
    const str = localStorage.getItem('erp_inv_' + invNo) || localStorage.getItem('erp_draft_' + invNo);
    if (!str) {
        showToast('❌ لم يتم العثور على الفاتورة', 'error');
        return;
    }
    try {
        const data = JSON.parse(str);
        if (window.InvoicePrinter && typeof window.InvoicePrinter.printPromissoryNote === 'function') {
            window.InvoicePrinter.printPromissoryNote(data);
        }
    } catch(e) {
        console.error("Error printing promissory note:", e);
    }
}

function autofillRemaining(type) {
    const cur = getCurrency();
    const invTotal = getInvoiceTotal();
    const labor = parseNum(document.getElementById('t-labor')?.value || 0);
    const transport = parseNum(document.getElementById('t-transport')?.value || 0);
    const discountAmt = parseNum(document.getElementById('discount-amt')?.value || 0);
    const taxAmt = parseNum(document.getElementById('tax-amt')?.value || 0);
    const additionAmt = parseNum(document.getElementById('addition-amt')?.value || 0);
    const grand = invTotal + labor + transport + additionAmt + taxAmt - discountAmt;
    
    const cashActive = document.getElementById('toggle-cash')?.classList.contains('active') || false;
    const bankActive = document.getElementById('toggle-bank')?.classList.contains('active') || false;
    const chequeActive = document.getElementById('toggle-cheque')?.classList.contains('active') || false;
    
    const cash = (cashActive && type !== 'cash') ? parseNum(document.getElementById('pay-cash-amount')?.value || 0) : 0;
    
    let transfersSum = 0;
    if (bankActive && type !== 'bank') {
        document.querySelectorAll('.bt-amount-input').forEach(input => {
            transfersSum += parseNum(input.value);
        });
    }
    
    let chequesSum = 0;
    if (chequeActive && type !== 'cheque') {
        document.querySelectorAll('.ch-amount-input').forEach(input => {
            chequesSum += parseNum(input.value);
        });
    }
    
    const otherPayments = cash + transfersSum + chequesSum;
    const remainingToFill = Math.max(0, grand - otherPayments);
    const fillVal = remainingToFill > 0 ? (remainingToFill % 1 === 0 ? remainingToFill.toString() : remainingToFill.toFixed(2)) : '0';
    
    if (type === 'cash') {
        const cashInput = document.getElementById('pay-cash-amount');
        if (cashInput) cashInput.value = fillVal;
    } else if (type === 'credit') {
        const creditInput = document.getElementById('pay-credit-amount');
        if (creditInput) creditInput.value = fillVal;
    } else if (type === 'bank') {
        const firstAmtInput = document.querySelector('#bank-transfers-list .bt-amount-input');
        if (firstAmtInput) {
            firstAmtInput.value = fillVal;
        }
    } else if (type === 'cheque') {
        const firstAmtInput = document.querySelector('#cheques-list .ch-amount-input');
        if (firstAmtInput) {
            firstAmtInput.value = fillVal;
        }
    }
    
    recalcPaymentSummary();
}

function getPaymentMethodFromUI() {
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const creditActive = document.getElementById('toggle-credit').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    if (!cashActive && !creditActive && !bankActive && !chequeActive) {
        return 'credit';
    }
    
    const cashAmt = cashActive ? parseNum(document.getElementById('pay-cash-amount').value) : 0;
    const creditAmt = creditActive ? parseNum(document.getElementById('pay-credit-amount').value) : 0;
    
    let transfersCount = 0;
    if (bankActive) {
        document.querySelectorAll('#bank-transfers-list .bt-amount-input').forEach(input => {
            if (parseNum(input.value) > 0) transfersCount++;
        });
    }
    
    let chequesCount = 0;
    if (chequeActive) {
        document.querySelectorAll('#cheques-list .ch-amount-input').forEach(input => {
            if (parseNum(input.value) > 0) chequesCount++;
        });
    }
    
    if (creditAmt > 0 && cashAmt === 0 && transfersCount === 0 && chequesCount === 0) {
        return 'credit';
    } else if (transfersCount > 0 && cashAmt === 0 && creditAmt === 0 && chequesCount === 0 && transfersCount === 1) {
        return 'bank';
    } else if (chequesCount > 0 && cashAmt === 0 && creditAmt === 0 && transfersCount === 0 && chequesCount === 1) {
        return 'cheque';
    } else if (cashAmt > 0 && creditAmt === 0 && transfersCount === 0 && chequesCount === 0) {
        return 'cash';
    } else {
        return 'hybrid';
    }
}

function loadPaymentDataIntoUI(data) {
    try {
        initMultiPaymentSystem(); // Clear first
        
        if (!data) return;
        
        const cashInput = document.getElementById('pay-cash-amount');
        const creditInput = document.getElementById('pay-credit-amount');
        const cashAccSelect = document.getElementById('hdr-payment-account');
        const dueDateInput = document.getElementById('pay-credit-due-date');
        
        // Deactivate all toggle buttons first
        document.getElementById('toggle-cash').classList.remove('active');
        document.getElementById('toggle-credit').classList.remove('active');
        document.getElementById('toggle-bank').classList.remove('active');
        document.getElementById('toggle-cheque').classList.remove('active');
        
        if (data.paymentMethod === 'hybrid' || data.paymentMethod === 'multipay') {
            if (data.cashAmount > 0) {
                document.getElementById('toggle-cash').classList.add('active');
                if (cashInput) cashInput.value = (data.cashAmount || 0).toFixed(2);
                if (cashAccSelect && data.paymentAccountId) cashAccSelect.value = data.paymentAccountId;
            }
            if (data.creditAmount > 0) {
                document.getElementById('toggle-credit').classList.add('active');
                if (creditInput) creditInput.value = (data.creditAmount || 0).toFixed(2);
                if (dueDateInput && data.dueDate) dueDateInput.value = data.dueDate;
            }
            
            // Load Bank transfers
            if (data.bankTransfers && data.bankTransfers.length > 0) {
                document.getElementById('toggle-bank').classList.add('active');
                data.bankTransfers.forEach(bt => {
                    addBankTransferRow(bt);
                });
            }
            // Load Cheques
            if (data.cheques && data.cheques.length > 0) {
                document.getElementById('toggle-cheque').classList.add('active');
                data.cheques.forEach(ch => {
                    addChequeRow(ch);
                });
            }
        } else {
            // Legacy / Standard invoice conversions
            if (data.paymentMethod === 'credit') {
                document.getElementById('toggle-credit').classList.add('active');
                if (creditInput) creditInput.value = (data.grandTotal || 0).toFixed(2);
                if (dueDateInput && data.dueDate) dueDateInput.value = data.dueDate;
            } else if (data.paymentMethod === 'cash') {
                document.getElementById('toggle-cash').classList.add('active');
                if (cashInput) cashInput.value = (data.grandTotal || 0).toFixed(2);
                if (cashAccSelect && data.paymentAccountId) cashAccSelect.value = data.paymentAccountId;
            } else if (data.paymentMethod === 'bank') {
                document.getElementById('toggle-bank').classList.add('active');
                addBankTransferRow({
                    bankAccountId: data.paymentAccountId,
                    reference: data.reference || data.payDetail1 || '',
                    amount: data.grandTotal || 0
                });
            } else if (data.paymentMethod === 'cheque') {
                document.getElementById('toggle-cheque').classList.add('active');
                addChequeRow({
                    chequeNo: data.chequeNo || data.payDetail1 || '',
                    bankName: data.bankName || data.payDetail2 || '',
                    dueDate: data.dueDate || data.payDetail3 || '',
                    amount: data.grandTotal || 0
                });
            }
        }
        
        updatePaymentSectionsVisibility();
        recalcPaymentSummary();
    } catch(e) {
        console.error("Error in loadPaymentDataIntoUI:", e);
    }
}

// ════════════════════════════════════════
//  SAVE / SUBMIT
// ════════════════════════════════════════
function saveDraft() {
    if (rows.length === 0) { showToast('⚠️ لا توجد أصناف لحفظها', 'warning'); return; }
    const paymentMethod = getPaymentMethodFromUI();
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const creditActive = document.getElementById('toggle-credit').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    const cashAmt = cashActive ? parseNum(document.getElementById('pay-cash-amount').value) : 0;
    const creditAmt = creditActive ? parseNum(document.getElementById('pay-credit-amount').value) : 0;
    
    let bankTransfers = [];
    if (bankActive) {
        document.querySelectorAll('#bank-transfers-list .payment-row-item').forEach(row => {
            const bankAccountId = row.querySelector('.bt-bank-select').value;
            const reference = row.querySelector('.bt-ref-input').value;
            const amount = parseNum(row.querySelector('.bt-amount-input').value);
            if (amount > 0) {
                bankTransfers.push({ bankAccountId, reference, amount });
            }
        });
    }
    
    let cheques = [];
    if (chequeActive) {
        document.querySelectorAll('#cheques-list .payment-row-item').forEach(row => {
            const chequeNo = row.querySelector('.ch-no-input').value;
            const bankName = row.querySelector('.ch-bank-select') ? row.querySelector('.ch-bank-select').value : '';
            const dueDate = row.querySelector('.ch-date-input').value;
            const amount = parseNum(row.querySelector('.ch-amount-input').value);
            if (amount > 0) {
                cheques.push({ chequeNo, bankName, dueDate, amount });
            }
        });
    }

    // Automatically remove any empty rows without an item selected
    rows = rows.filter(r => r && r.itemId && String(r.itemId).trim() !== '');
    if (rows.length === 0) {
        showToast('⚠️ لا توجد أصناف لحفظها في المسودة', 'warning', 2500);
        return;
    }

    const data = {
        invoiceNo: document.getElementById('hdr-inv-no').value,
        date: document.getElementById('hdr-date').value,
        customer: document.getElementById('hdr-customer').value,
        saleType: currentSaleType,
        currency: getCurrency(),
        exchangeRate: document.getElementById('hdr-exchange-rate').value,
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1,
        paymentMethod: paymentMethod,
        paymentAccountId: document.getElementById('hdr-payment-account').value,
        cashAmount: cashAmt,
        creditAmount: creditAmt,
        bankTransfers: bankTransfers,
        cheques: cheques,
        warehouse: document.getElementById('hdr-warehouse').value,
        description: document.getElementById('hdr-description').value,
        items: rows,
        notes: document.getElementById('inv-notes').value,
        status: 'draft'
    };
    localStorage.setItem('erp_draft_' + data.invoiceNo, JSON.stringify(data));
    showToast('📑 تم حفظ المسودة بنجاح', 'success', 2500);
}

function submitInvoice(ignoreWarnings = false) {
    if (window.isSubmitting) return;
    window.isSubmitting = true;

    try {
        if (window.isInvoiceLocked || window.isInvoicePosted) {
            showToast('🔒 هذه الفاتورة مرحلة محاسبياً في دفتر الأستاذ ومقفلة تماماً - ممنوع التعديل', 'error', 6000);
            alert('🚫 تنبيه أمان:\n\nلا يمكن تعديل هذه الفاتورة نهائياً لأنها فاتورة مرحلة محاسبياً في دفتر الأستاذ العام.');
            window.isSubmitting = false;
            return;
        }

        if (currentViewedInvoiceNo) {
            // Edit mode check
            const invDate = document.getElementById('hdr-date').value;
            if (!window.canEditRecord(invDate, 'sales')) {
                window.isSubmitting = false;
                return; // blocked by policy
            }

            if (!confirm('⚠️ تأكيد حفظ التعديلات:\n\nالفاتورة رقم (' + currentViewedInvoiceNo + ') هي فاتورة أصلية معتمدة ومحفوظة مسبقاً في النظام.\n\nهل تريد بالتأكيد تطبيق التعديلات عليها وتحديث الحسابات وحركات المخزن المرتبطة بها؟')) {
                window.isSubmitting = false;
                return;
            }
        }

        const custId = document.getElementById('hdr-customer').value;
        if (!custId) { showToast('❌ يرجى تحديد العميل لتأكيد الفاتورة', 'error'); window.isSubmitting = false; return; }

        // Automatically clean and remove any empty rows without an item selected
        const hadEmptyRows = rows.some(r => !r.itemId || String(r.itemId).trim() === '');
        if (hadEmptyRows) {
            rows = rows.filter(r => r && r.itemId && String(r.itemId).trim() !== '');
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
        }

        if (rows.length === 0) { 
            showToast('❌ لا يمكن تأكيد فاتورة فارغة، يرجى اختيار صنف', 'error'); 
            window.isSubmitting = false; 
            return; 
        }

        const hasZeroQty = rows.some(r => r.quantity <= 0);
        if (hasZeroQty) { showToast('⚠️ الكمية يجب أن تكون أكبر من صفر', 'warning'); window.isSubmitting = false; return; }

    const cur = getCurrency();
    const grandTotal = document.getElementById('t-grand').textContent;
    const grandAmount = parseNum(grandTotal);

    // --- 1. Multi-Payment Read & Validation ---
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const creditActive = document.getElementById('toggle-credit').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');

    let cashAmt = 0;
    let creditAmt = 0;
    let bankTransfers = [];
    let transfersSum = 0;
    let cheques = [];
    let chequesSum = 0;
    let paymentMethod = 'cash';

    if (!cashActive && !creditActive && !bankActive && !chequeActive) {
        // DEFAULT RULE: If no payment method is toggled, default to Credit (Deferred) for full amount
        creditAmt = grandAmount;
        paymentMethod = 'credit';
        cashAmt = cashActive ? parseNum(document.getElementById('pay-cash-amount')?.value || 0) : 0;
        
        if (bankActive) {
            document.querySelectorAll('#bank-transfers-list .payment-row-item').forEach(row => {
                const bankAccountId = row.querySelector('.bt-bank-select') ? row.querySelector('.bt-bank-select').value : '';
                const refEl = row.querySelector('.bt-ref-input') || row.querySelector('.bt-no-input');
                const reference = refEl ? refEl.value.trim() : '';
                const amountEl = row.querySelector('.bt-amount-input');
                const amount = amountEl ? parseNum(amountEl.value) : 0;
                if (amount > 0) {
                    bankTransfers.push({ bankAccountId, reference, amount });
                    transfersSum += amount;
                }
            });
        }
        
        if (chequeActive) {
            document.querySelectorAll('#cheques-list .payment-row-item').forEach(row => {
                const chequeNo = row.querySelector('.ch-no-input') ? row.querySelector('.ch-no-input').value.trim() : '';
                const bankName = row.querySelector('.ch-bank-select') ? row.querySelector('.ch-bank-select').value : '';
                const dueDate = row.querySelector('.ch-date-input') ? row.querySelector('.ch-date-input').value : '';
                const amountEl = row.querySelector('.ch-amount-input');
                const amount = amountEl ? parseNum(amountEl.value) : 0;
                if (amount > 0) {
                    cheques.push({ chequeNo, bankName, dueDate, amount });
                    chequesSum += amount;
                }
            });
        }

        const totalPaymentsPaid = cashAmt + transfersSum + chequesSum;
        if (totalPaymentsPaid < grandAmount) {
            creditAmt = Math.max(0, grandAmount - totalPaymentsPaid);
        } else {
            creditAmt = 0;
        }

        if (cashAmt > 0 && transfersSum === 0 && chequesSum === 0 && creditAmt === 0) {
            paymentMethod = 'cash';
        } else if (transfersSum > 0 && cashAmt === 0 && chequesSum === 0 && creditAmt === 0) {
            paymentMethod = 'bank';
        } else if (chequesSum > 0 && cashAmt === 0 && transfersSum === 0 && creditAmt === 0) {
            paymentMethod = 'cheque';
        } else if (totalPaymentsPaid === 0) {
            paymentMethod = 'credit';
        } else {
            paymentMethod = 'hybrid';
        }
    }

    const cust = CUSTOMERS.find(c => String(c.id) === String(custId));

    // --- Hard Block: 2 Unpaid Credit Invoices (Run if there is credit amount) ---
    if (creditAmt > 0 && cust && cust.id !== 'walk-in') {
        const debt = cust.ledger || 0;
        if (debt > 0) {
            let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            const creditInvs = allSales.filter(inv => (inv.customer === cust.id || inv.customerId === cust.id) && (inv.paymentMethod === 'credit' || (inv.creditAmount && inv.creditAmount > 0)));
            creditInvs.reverse(); // Newest first

            let remainingDebt = debt;
            let unpaidCount = 0;

            for (const inv of creditInvs) {
                let invTotal = inv.creditAmount !== undefined ? inv.creditAmount : inv.grandTotal;
                if (invTotal === undefined) {
                    invTotal = inv.items ? inv.items.reduce((sum, item) => sum + ((item.quantity||0) * (item.price||0)), 0) : 0;
                }
                remainingDebt -= invTotal;
                unpaidCount++;
                if (remainingDebt <= 0) break;
            }

            if (unpaidCount >= 2) {
                const existing = document.getElementById('confirm-warning-overlay');
                if (existing) existing.remove();
                
                alert(`❌ حظر ائتماني صارم!\nالعميل (${cust.name}) لديه ${unpaidCount} فواتير آجلة سابقة لم يقم بسدادها بالكامل.\nيجب سداد الديون القديمة أولاً لإنشاء فاتورة آجلة جديدة.`);
                window.isSubmitting = false; return;
            }
        }
    }
    // ---------------------------------------------

    let warnings = [];

    if (!ignoreWarnings) {
        // Stock availability check
        const mainWh = document.getElementById('hdr-warehouse').value;
        for (const row of rows) {
            const item = ITEMS.find(i => String(i.id) === String(row.itemId));
            if (!item) continue;
            const calcs = getRowCalcs(row);
            if (calcs.availQty !== '—' && calcs.availQty !== undefined && calcs.baseQty > calcs.availQty) {
                const diff = calcs.baseQty - calcs.availQty;
                warnings.push(`الصنف "<strong>${item.name}</strong>" تجاوز المخزون المتاح. (سوف يدخل بالسالب بمقدار <strong>${fmtNum(diff)} ${item.baseUnit || 'وحدة أساسية'}</strong>)`);
            }
        }

        // Credit check for non-cash customers
        if (creditAmt > 0 && cust && cust.id !== 'walk-in' && cust.creditLimit > 0) {
            const totalDebt = cust.ledger + cust.checks;
            const remainingLimit = cust.creditLimit - totalDebt;
            if (creditAmt > remainingLimit) {
                const diff = creditAmt - remainingLimit;
                warnings.push(`مبلغ البيع الآجل يتجاوز حد الائتمان. (المبلغ سيتجاوز الحد بمقدار <strong>${fmtNum(diff)} ${cur}</strong>)`);
            }
        }

        if (warnings.length > 0) {
            window.isSubmitting = false;
            showConfirmWarningModal(warnings);
            return;
        }
    }

    setTimeout(() => { window.isSubmitting = false; }, 3000);

    // Commit the invoice number
    let committedNo;
    if (currentViewedInvoiceNo) {
        committedNo = currentViewedInvoiceNo; // keep old number in edit mode
    } else {
        committedNo = commitInvoiceNumber(currentSaleType); // increment
    }

    // Save invoice data
    let dueDateValue = null;
    if (creditAmt > 0) {
        dueDateValue = document.getElementById('pay-credit-due-date').value;
    }

    const paymentAccountId = document.getElementById('hdr-payment-account').value; // Cash account ID

    const postingMode = window.getPostingMode ? window.getPostingMode() : 'auto';
    let finalStatus = 'pending_delivery';
    if (currentSaleType === 'preliminary') {
        finalStatus = 'preliminary';
    } else {
        if (postingMode === 'auto') {
            finalStatus = 'posted';
        } else {
            finalStatus = 'pending_delivery';
        }
    }

    const data = {
        invoiceNo: committedNo,
        date: document.getElementById('hdr-date').value,
        time: new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
        dueDate: dueDateValue,
        customer: custId,
        costCenter: document.getElementById('hdr-cost-center').value,
        gpsLocation: {
            lat: document.getElementById('hdr-gps-lat')?.value || '',
            lng: document.getElementById('hdr-gps-lng')?.value || ''
        },
        saleType: currentSaleType,
        currency: cur,
        exchangeRate: document.getElementById('hdr-exchange-rate').value,
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1,
        paymentMethod: paymentMethod,
        paymentAccountId: paymentAccountId, // Safe for cash portion
        cashAmount: cashAmt,
        creditAmount: creditAmt,
        bankTransfers: bankTransfers,
        cheques: cheques,
        warehouse: document.getElementById('hdr-warehouse').value,
        description: document.getElementById('hdr-description').value,
        items: rows.map(r => ({...r})),
        notes: document.getElementById('inv-notes').value,
        subTotal: parseNum(document.getElementById('t-subtotal')?.textContent || document.getElementById('t-invoice-total')?.textContent || 0),
        discount: parseNum(document.getElementById('t-discount')?.value || document.getElementById('t-discount')?.textContent || 0),
        vatAmount: parseNum(document.getElementById('t-vat')?.textContent || document.getElementById('t-tax')?.textContent || 0),
        grandTotal: grandAmount,
        status: finalStatus,
        timestamp: new Date().toISOString(),
        customerName: cust ? cust.name : 'عميل نقدي',
        effectsApplied: (currentSaleType !== 'preliminary' && postingMode === 'auto')
    };

    // If editing, revert old financial effects first
    if (currentViewedInvoiceNo) {
        revertSalesInvoiceEffects(currentViewedInvoiceNo, false); // false = don't revert inventory for edits
    }

    // Update Bank/Safe balance ONLY if not preliminary AND posting mode is auto
    let safeAccountName = '';
    let bankTransferNames = [];
    
    if (currentSaleType !== 'preliminary' && postingMode === 'auto') {
        let banks = [];
        try { banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
        
        // A. Cash Safe Portion
        if (cashAmt > 0 && paymentAccountId) {
            const bIdx = banks.findIndex(b => b.id === paymentAccountId);
            if (bIdx > -1) {
                let currentBalance = parseFloat(banks[bIdx].currentBalance);
                if (isNaN(currentBalance)) {
                    currentBalance = parseFloat(banks[bIdx].openingBalance) || 0;
                }
                banks[bIdx].currentBalance = currentBalance + cashAmt;
                safeAccountName = banks[bIdx].name;
            }
        }
        
        // B. Bank Transfer Portions
        bankTransfers.forEach(bt => {
            const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
            if (bIdx > -1) {
                let currentBalance = parseFloat(banks[bIdx].currentBalance);
                if (isNaN(currentBalance)) {
                    currentBalance = parseFloat(banks[bIdx].openingBalance) || 0;
                }
                banks[bIdx].currentBalance = currentBalance + bt.amount;
                bankTransferNames.push(banks[bIdx].name);
            }
        });
        
        localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        
        // C. Customer Credit Debt Portion
        if (creditAmt > 0 && custId && custId !== 'walk-in') {
            let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const cIdx = customers.findIndex(c => c.id === custId);
            if (cIdx > -1) {
                let currentBalance = parseFloat(customers[cIdx].currentBalance);
                if (isNaN(currentBalance)) currentBalance = 0;
                customers[cIdx].currentBalance = currentBalance + creditAmt;
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            }
        }
    }

    localStorage.setItem('erp_inv_' + committedNo, JSON.stringify(data));

    // Append or Update global sales invoices array
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    if (currentViewedInvoiceNo) {
        const idx = allSales.findIndex(inv => inv.invoiceNo === currentViewedInvoiceNo);
        if (idx !== -1) allSales[idx] = data;
        else allSales.push(data);
    } else {
        allSales.push(data);
    }
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));

    if (typeof window.rebuildGLFromAllTables === 'function') {
        window.rebuildGLFromAllTables(false);
    }
    if (typeof window.pushToCloud === 'function') {
        window.pushToCloud(true).catch(e => console.warn("Cloud sync failed:", e));
    }

    // Notice: Inventory is NO LONGER DEDUCTED HERE for new invoices!
    // It will be deducted only upon Delivery.

    // Show professional success toasts
    if (postingMode === 'manual' && currentSaleType !== 'preliminary') {
        showToast('📋 تم تأكيد الفاتورة — تحتاج ترحيل لتؤثر في الأرصدة', 'warning', 5000);
    } else {
        if (safeAccountName && cashAmt > 0) {
            showToast(`تمت إضافة ${fmtNum(cashAmt)} ${cur} إلى رصيد: ${safeAccountName}`, 'success', 4000);
        }
        if (bankTransferNames.length > 0) {
            showToast(`تم إيداع التحويلات البنكية بقيمة ${fmtNum(transfersSum)} ${cur} بنجاح`, 'success', 4000);
        }
        if (creditAmt > 0 && custId !== 'walk-in') {
            showToast(`تم ترحيل مبلغ ${fmtNum(creditAmt)} ${cur} إلى حساب العميل كمديونية آجل`, 'warning', 4000);
        }
    }

    // Show warnings if ignoreWarnings is true
    let finalWarnings = [];
    if (ignoreWarnings) {
        for (const row of rows) {
            const item = ITEMS.find(i => String(i.id) === String(row.itemId));
            if (!item) continue;
            const calcs = getRowCalcs(row);
            if (calcs.availQty !== '—' && calcs.availQty !== undefined && calcs.baseQty > calcs.availQty) {
                const diff = calcs.baseQty - calcs.availQty;
                finalWarnings.push(`الصنف "<strong>${item.name}</strong>" تجاوز المخزون بمقدار <strong>${fmtNum(diff)} ${item.baseUnit || 'وحدة أساسية'}</strong>`);
            }
        }
    }

    currentViewedInvoiceNo = committedNo;
    document.getElementById('hdr-inv-no').value = committedNo;
    document.getElementById('inv-number').textContent = committedNo;
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
    
    if (typeof updateSalesPostingStatusBar === 'function') {
        updateSalesPostingStatusBar(finalStatus);
    }

    showSuccessModal(committedNo, grandTotal, cur, finalWarnings);
    } catch(err) {
        console.error("Error in submitInvoice:", err);
        showToast("❌ حدث خطأ غير متوقع أثناء الحفظ: " + (err.message || err), "error");
    } finally {
        window.isSubmitting = false;
    }
}

// ════════════════════════════════════════
//  CONFIRM WARNING MODAL
// ════════════════════════════════════════
function showConfirmWarningModal(warnings) {
    const existing = document.getElementById('confirm-warning-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'confirm-warning-overlay';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div class="modal-card" style="max-width: 500px; border-color: rgba(245,158,11,0.4);">
            <div class="modal-header" style="border-bottom-color: rgba(245,158,11,0.2);">
                <h3 style="color: #fbbf24; display:flex; align-items:center; gap:8px;">
                    <span>⚠️</span> تنبيه بتجاوز الحدود
                </h3>
                <button class="modal-close" onclick="closeConfirmWarningModal()">✕</button>
            </div>
            <div class="modal-body" style="color: #f1f5f9; font-size: 0.9rem; line-height: 1.6;">
                <p style="margin-bottom: 12px; color: #cbd5e1;">لقد تجاوزت هذه الفاتورة بعض الحدود المسموحة. يرجى مراجعة التنبيهات التالية قبل التأكيد:</p>
                <ul style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 12px 24px;">
                    ${warnings.map(w => `<li style="margin-bottom: 8px;">${w}</li>`).join('')}
                </ul>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; border-top-color: rgba(245,158,11,0.2);">
                <button class="btn btn-ghost" onclick="closeConfirmWarningModal()">إلغاء المراجعة</button>
                <button class="btn btn-primary" style="background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(217,119,6,0.12)); border-color: rgba(245,158,11,0.4); color: #fbbf24;" onclick="executeConfirmWarning()">استمرار وتأكيد الفاتورة</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('visible'));
}

function closeConfirmWarningModal() {
    const overlay = document.getElementById('confirm-warning-overlay');
    if (overlay) {
        overlay.classList.remove('visible');
        setTimeout(() => overlay.remove(), 400);
    }
}

function executeConfirmWarning() {
    closeConfirmWarningModal();
    submitInvoice(true);
}

// ════════════════════════════════════════
//  DELETE TRANSACTION MODAL
// ════════════════════════════════════════
function confirmDeleteTransaction() {
    const existing = document.getElementById('delete-confirm-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'delete-confirm-overlay';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div class="modal-card" style="max-width: 400px; border-color: rgba(239,68,68,0.4);">
            <div class="modal-header" style="border-bottom-color: rgba(239,68,68,0.2);">
                <h3 style="color: #f87171; display:flex; align-items:center; gap:8px;">
                    <span>⚠️</span> تأكيد الحذف
                </h3>
                <button class="modal-close" onclick="closeDeleteConfirmModal()">✕</button>
            </div>
            <div class="modal-body" style="color: #f1f5f9; font-size: 0.95rem; line-height: 1.6; text-align: center;">
                <p>هل أنت متأكد أنك تريد حذف هذه العملية ومسح جميع البيانات؟</p>
                <p style="color: #cbd5e1; font-size: 0.85rem; margin-top: 8px;">لا يمكن التراجع عن هذا الإجراء.</p>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:center; gap:15px; border-top-color: rgba(239,68,68,0.2);">
                <button class="btn btn-ghost" onclick="closeDeleteConfirmModal()">إلغاء</button>
                <button class="btn btn-primary" style="background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(185,28,28,0.12)); border-color: rgba(239,68,68,0.4); color: #f87171;" onclick="executeDeleteTransaction()">نعم، احذف العملية</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('visible'));
}

function closeDeleteConfirmModal() {
    const overlay = document.getElementById('delete-confirm-overlay');
    if (overlay) {
        overlay.classList.remove('visible');
        setTimeout(() => overlay.remove(), 400);
    }
}

function executeDeleteTransaction() {
    closeDeleteConfirmModal();
    
    const currentInvNo = document.getElementById('hdr-inv-no').value;
    if (!currentInvNo) return;

    // Retrieve old invoice to revert its effects
    let oldInvStr = localStorage.getItem('erp_inv_' + currentInvNo);
    let oldInv = null;
    if (oldInvStr) {
        try { oldInv = JSON.parse(oldInvStr); } catch(e) {}
    }
    
    if (!oldInv) {
        let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
        oldInv = allSales.find(inv => inv.invoiceNo === currentInvNo || inv.id === currentInvNo);
    }

    if (!oldInv) {
        localStorage.removeItem('erp_draft_' + currentInvNo);
        onSuccessNewInvoice(); 
        showToast('🗑️ تم مسح بيانات الفاتورة', 'success');
        return;
    }
    // Revert effects
    revertSalesInvoiceEffects(currentInvNo, true);

    // 4. Remove from sales invoices list
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    allSales = allSales.filter(inv => inv.invoiceNo !== currentInvNo);
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));

    // 5. Delete individual records
    localStorage.removeItem('erp_inv_' + currentInvNo);
    localStorage.removeItem('erp_draft_' + currentInvNo);

    // Reset Form
    onSuccessNewInvoice(); 
    showToast('🗑️ تم حذف الفاتورة وإلغاء تأثيراتها المالية والمخزنية بنجاح', 'success');
}

// ════════════════════════════════════════
//  SUCCESS MODAL
// ════════════════════════════════════════
function showSuccessModal(invoiceNo, grandTotal, currency, warnings = []) {
    // Remove any existing success modal
    const existing = document.getElementById('success-modal-overlay');
    if (existing) existing.remove();

    let warningsHtml = '';
    if (warnings && warnings.length > 0) {
        warningsHtml = `
            <div style="margin-top: 15px; padding: 12px; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 8px; text-align: right;">
                <strong style="color: #fbbf24; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                    <span>⚠️</span> تنبيهات أثناء الحفظ:
                </strong>
                <ul style="color: #f1f5f9; font-size: 0.8rem; margin-top: 8px; padding-right: 24px; line-height: 1.5;">
                    ${warnings.map(w => `<li style="margin-bottom: 4px;">${w}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    const overlay = document.createElement('div');
    overlay.id = 'success-modal-overlay';
    overlay.className = 'success-overlay';
    overlay.innerHTML = `
        <div class="success-card">
            <div class="success-check-wrap">
                <div class="success-check-circle">
                    <svg class="success-check-svg" viewBox="0 0 52 52">
                        <circle class="check-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="check-mark" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
            </div>
            <h2 class="success-title">🎉 تم الحفظ بنجاح!</h2>
            <p class="success-inv-no">رقم الفاتورة: <strong>#${invoiceNo}</strong></p>
            <div class="success-amount-box">
                <div class="success-amount-label">إجمالي المبلغ</div>
                <div class="success-amount-value">${grandTotal}</div>
            </div>
            ${warningsHtml}
            <div class="success-actions" style="margin-top: 20px; display:flex; flex-direction:column; gap:10px;">
                <button class="btn btn-success success-btn" style="background:linear-gradient(135deg,#38bdf8,#0284c7);" onclick="printCurrentInvoice()">🖨️ طباعة الفاتورة</button>
                
                ${(() => {
                    const str = localStorage.getItem('erp_inv_' + invoiceNo) || localStorage.getItem('erp_draft_' + invoiceNo);
                    if (str) {
                        try {
                            const data = JSON.parse(str);
                            if (parseFloat(data.creditAmount) > 0) {
                                return `<button class="btn btn-success success-btn" style="background:linear-gradient(135deg,#f59e0b,#d97706);" onclick="printCurrentPromissoryNote('${invoiceNo}')">📜 طباعة الكمبيالة / إقرار دين المدين</button>`;
                            }
                        } catch(e) {}
                    }
                    return '';
                })()}
                
                <div style="display:flex; gap:10px;">
                    <button class="btn btn-ghost success-btn" style="flex:1;" onclick="onSuccessNewInvoice()">عملية جديدة</button>
                    <button class="btn btn-success success-btn" style="flex:1;" onclick="onSuccessDone()">قائمة المبيعات</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);

    // Trigger animation
    requestAnimationFrame(() => overlay.classList.add('visible'));
}

function onSuccessNewInvoice(closeModalFlag = true) {
    currentViewedInvoiceNo = null; // Reset edit mode
    if (typeof hideEditBanner === 'function') hideEditBanner();
    if (typeof updateSalesPostingStatusBar === 'function') {
        updateSalesPostingStatusBar('new');
    }
    // Close modal
    if (closeModalFlag) {
        const overlay = document.getElementById('success-modal-overlay');
        if (overlay) { overlay.classList.remove('visible'); setTimeout(() => overlay.remove(), 400); }
    }

    // Reset form for new invoice
    rows = [];
    rowIdCounter = 0;
    
    // Clear customer inputs completely
    if (document.getElementById('hdr-customer')) document.getElementById('hdr-customer').value = '';
    if (document.getElementById('hdr-customer-search')) {
        document.getElementById('hdr-customer-search').value = '';
        document.getElementById('hdr-customer-search').placeholder = 'ابحث عن اسم العميل...';
    }
    
    // Clear description and notes
    if (document.getElementById('hdr-description')) document.getElementById('hdr-description').value = '';
    if (document.getElementById('inv-notes')) document.getElementById('inv-notes').value = '';
    
    // Reset date to today
    if (document.getElementById('hdr-date')) {
        const today = new Date();
        const localDateStr = new Date(today.getTime() - (today.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
        document.getElementById('hdr-date').value = localDateStr;
    }

    if (document.getElementById('discount-amt')) document.getElementById('discount-amt').value = '0';
    if (document.getElementById('discount-pct')) document.getElementById('discount-pct').value = '0';
    if (document.getElementById('tax-amt')) document.getElementById('tax-amt').value = '0';
    if (document.getElementById('tax-pct')) document.getElementById('tax-pct').value = '0';
    if (document.getElementById('addition-amt')) document.getElementById('addition-amt').value = '0';
    if (document.getElementById('addition-pct')) document.getElementById('addition-pct').value = '0';
    if (document.getElementById('t-labor')) document.getElementById('t-labor').value = '0';
    if (document.getElementById('t-transport')) document.getElementById('t-transport').value = '0';
    
    // Trigger customer change to reset balances UI
    if (typeof onCustomerChange === 'function') onCustomerChange();

    // Reset currency to default System Currency and set exchange rate
    const currencySelect = document.getElementById('hdr-currency');
    const rateInput = document.getElementById('hdr-exchange-rate');
    
    let defaultCurrency = 'SDG';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.currency) {
            defaultCurrency = cfg.currency;
        }
    } catch(e) {}

    if (currencySelect) {
        currencySelect.value = defaultCurrency;
        if (typeof handleCurrencyChange === 'function') {
            handleCurrencyChange(defaultCurrency);
        }
    }

    // Show the NEXT invoice number (peeked, not committed)
    if (typeof peekNextInvoiceNumber === 'function') {
        const nextNo = peekNextInvoiceNumber(currentSaleType);
        if (document.getElementById('inv-number')) document.getElementById('inv-number').textContent = nextNo;
        if (document.getElementById('hdr-inv-no')) document.getElementById('hdr-inv-no').value = nextNo;
    }

    if (typeof renderGrid === 'function') renderGrid();
    if (typeof initMultiPaymentSystem === 'function') initMultiPaymentSystem();
    if (typeof recalcAll === 'function') recalcAll();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
}

function onSuccessDone() {
    const overlay = document.getElementById('success-modal-overlay');
    if (overlay) { overlay.classList.remove('visible'); setTimeout(() => overlay.remove(), 400); }
    // In production: navigate to sales list
    // window.location.href = 'sales-list.html';
    showToast('📋 قائمة المبيعات (قيد التطوير)', 'warning');
}

// ════════════════════════════════════════
//  PRICE HISTORY MODAL
// ════════════════════════════════════════
function showPriceHistory(rowId, type) {
    const row = rows.find(r => r.id === rowId);
    if (!row || !row.itemId) {
        showToast('⚠️ يرجى اختيار الصنف أولاً', 'warning');
        return;
    }
    
    const custId = document.getElementById('hdr-customer').value;
    if (!custId) {
        showToast('⚠️ يرجى تحديد العميل أولاً لعرض سجله', 'warning');
        return;
    }
    
    const item = ITEMS.find(i => String(i.id) === String(row.itemId));
    const itemName = item ? item.name : 'صنف غير معروف';
    const custName = document.getElementById('hdr-customer-search').value || 'العميل';

    let historyKey = type === 'sales' ? 'erp_sales_invoices_v1' : 'erp_purchase_invoices_v1';
    let invoices = JSON.parse(localStorage.getItem(historyKey)) || [];
    
    // Filter invoices by this customer/vendor
    let customerInvoices = invoices.filter(inv => (inv.customer === custId || inv.customerId === custId) && inv.status === 'confirmed');
    
    // Sort by date descending
    customerInvoices.sort((a, b) => new Date(b.date) - new Date(a.date));
    
    let historyRows = [];
    customerInvoices.forEach(inv => {
        if (inv.items) {
            let matchedItems = inv.items.filter(i => i.itemId === row.itemId);
            matchedItems.forEach(i => {
                let usdPrice = 0;
                let price = parseFloat(i.price);
                let exRate = parseFloat(inv.exchangeRate) || 1;
                if (inv.currency === 'USD') {
                    usdPrice = price;
                } else if (exRate > 1) {
                    usdPrice = price / exRate;
                }

                historyRows.push({
                    date: inv.date,
                    invoiceNo: inv.invoiceNo,
                    qty: i.quantity,
                    unit: i.selectedUnit || item.unit,
                    price: price,
                    currency: inv.currency || 'SDG',
                    usdPrice: usdPrice
                });
            });
        }
    });

    // Take top 5 recent
    historyRows = historyRows.slice(0, 5);

    let tbodyHtml = '';
    if (historyRows.length === 0) {
        tbodyHtml = `<tr><td colspan="5" style="text-align:center; color:#94a3b8; padding: 15px;">لا يوجد سجل أسعار سابق لهذا الصنف مع هذا العميل</td></tr>`;
    } else {
        tbodyHtml = historyRows.map(h => `
            <tr>
                <td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.05);">${h.date}</td>
                <td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.05);">${h.invoiceNo}</td>
                <td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.05);">${h.qty} ${h.unit}</td>
                <td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.05); font-weight:bold; color:var(--text-primary);">${fmtNum(h.price)} ${h.currency}</td>
                <td style="padding:8px; border-bottom:1px solid rgba(255,255,255,0.05); color:#10b981;">${h.usdPrice > 0 ? '$' + h.usdPrice.toFixed(2) : '-'}</td>
            </tr>
        `).join('');
    }

    const existing = document.getElementById('price-history-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'price-history-overlay';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div class="modal-card" style="max-width: 600px;">
            <div class="modal-header">
                <h3 style="display:flex; align-items:center; gap:8px; color: #38bdf8;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    سجل الأسعار السابقة
                </h3>
                <button class="modal-close" onclick="closePriceHistoryModal()">✕</button>
            </div>
            <div class="modal-body">
                <div style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                        <span style="color:var(--text-muted); font-size:0.85rem;">الصنف:</span>
                        <strong style="color:var(--text-primary);">${itemName}</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted); font-size:0.85rem;">العميل:</span>
                        <strong style="color:var(--text-primary);">${custName}</strong>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.9rem;">
                        <thead>
                            <tr style="background: rgba(0,0,0,0.2);">
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">التاريخ</th>
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">رقم الفاتورة</th>
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">الكمية</th>
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">السعر</th>
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">ما يعادل بالدولار</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tbodyHtml}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end;">
                <button class="btn btn-ghost" onclick="closePriceHistoryModal()">إغلاق</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('visible'));
}

function closePriceHistoryModal() {
    const overlay = document.getElementById('price-history-overlay');
    if (overlay) {
        overlay.classList.remove('visible');
        setTimeout(() => overlay.remove(), 400);
    }
}

// ════════════════════════════════════════
//  PRINT LOGIC — uses InvoicePrinter engine
// ════════════════════════════════════════
function getInvoiceDataForPrint() {
    const WAREHOUSES_local = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || WAREHOUSES;
    const whEl   = document.getElementById('hdr-warehouse');
    const whId   = whEl ? whEl.value : '';
    const whObj  = WAREHOUSES_local.find(w => w.id === whId);

    // Build items array for the print engine
    const printItems = (rows || [])
        .filter(r => r && r.itemId)
        .map(r => {
            const item   = ITEMS.find(i => i && String(i.id) === String(r.itemId));
            const calcs  = getRowCalcs(r);
            return {
                itemName:     item ? item.name : '—',
                unit:         r.selectedUnit || (item ? item.unit : '—'),
                quantity:     r.quantity,
                price:        r.price,
                total:        calcs.total,
                baseQty:      calcs.baseQty
            };
        });

    const subtotalVal = parseNum(
        document.getElementById('t-sub')?.textContent || 
        document.getElementById('t-subtotal')?.textContent || 
        document.getElementById('t-invoice-total')?.textContent || 
        '0'
    );
    const discAmt     = parseNum(document.getElementById('discount-amt')?.value || '0');
    const taxAmt      = parseNum(document.getElementById('tax-amt')?.value      || '0');
    const addAmt      = parseNum(document.getElementById('addition-amt')?.value || '0');
    const laborVal    = parseNum(document.getElementById('t-labor')?.value      || '0');
    const transportVal= parseNum(document.getElementById('t-transport')?.value  || '0');
    const grandVal    = parseNum(document.getElementById('t-grand')?.textContent || '0');

    const paymentMethodEl = document.getElementById('hdr-payment-method');
    const paymentMethod = paymentMethodEl ? paymentMethodEl.value : (typeof getPaymentMethodFromUI === 'function' ? getPaymentMethodFromUI() : 'cash');

    return {
        type:          'sales',
        typeLabel:     currentSaleType === 'direct' ? 'فاتورة مبيعات مباشرة' :
                       currentSaleType === 'split'  ? 'فاتورة بيع مقسّمة'    :
                       currentSaleType === 'proforma'? 'عرض سعر (بروفورما)'  : 'فاتورة مبيعات',
        invoiceNo:     document.getElementById('hdr-inv-no')?.value || '',
        date:          document.getElementById('hdr-date')?.value || '',
        customerName:  document.getElementById('hdr-customer-search')?.value || 'زبون نقدي',
        currency:      getCurrency(),
        exchangeRate:  parseNum(document.getElementById('hdr-exchange-rate')?.value || '1'),
        paymentMethod: paymentMethod,
        warehouseName: whObj ? whObj.name : '—',
        notes:         document.getElementById('inv-notes')?.value || '',
        items:         printItems,
        subtotal:      subtotalVal,
        discountAmt:   discAmt,
        taxAmt:        taxAmt,
        additionAmt:   addAmt,
        laborCost:     laborVal,
        transportCost: transportVal,
        grandTotal:    grandVal
    };
}

function printCurrentInvoice() {
    const data = getInvoiceDataForPrint();
    if (window.InvoicePrinter) {
        window.InvoicePrinter.print(data);
    } else {
        window.print();
    }
}

// Legacy alias kept for backward compatibility
function generatePrintArea(type) {
    printCurrentInvoice();
}

// ════════════════════════════════════════
//  OLD generatePrintArea body (replaced)
// ════════════════════════════════════════
function _legacyBuildPrintArea(type) {
    let printArea = document.getElementById('print-area');
    if (!printArea) {
        printArea = document.createElement('div');
        printArea.id = 'print-area';
        document.body.appendChild(printArea);
    }
    const defaultWhId = document.getElementById('hdr-warehouse').value;
    const warehouseGroups = {};
    rows.forEach(row => {
        let whId = row.warehouseId || defaultWhId;
        if (!warehouseGroups[whId]) warehouseGroups[whId] = [];
        warehouseGroups[whId].push(row);
    });

    const custName = document.getElementById('hdr-customer-search').value || (type === 'sales' ? 'عميل نقدي' : 'مورد نقدي');
    const invNo = document.getElementById('hdr-inv-no').value;
    const invDate = document.getElementById('hdr-date').value;
    const grandTotal = document.getElementById('t-grand').textContent;
    const currency = getCurrency();

    let html = '';
    
    // 1. The Main Invoice Copy
    html += `
    <div class="print-page" style="padding: 20px; direction: rtl; font-family: 'Cairo', Tahoma, Arial;">
        <div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
            <h2>${type === 'sales' ? 'فاتورة مبيعات' : 'فاتورة مشتريات'}</h2>
            <h3>${JSON.parse(localStorage.getItem('user'))?.company_name || 'الشركة'}</h3>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom: 20px; font-size: 14px;">
            <div><strong>رقم الفاتورة:</strong> ${invNo}</div>
            <div><strong>التاريخ:</strong> ${invDate}</div>
            <div><strong>${type === 'sales' ? 'العميل' : 'المورد'}:</strong> ${custName}</div>
        </div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;" border="1">
            <thead>
                <tr style="background:#eee; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    <th style="padding:8px;">م</th>
                    <th style="padding:8px;">الصنف</th>
                    <th style="padding:8px;">الوحدة</th>
                    <th style="padding:8px;">الكمية</th>
                    <th style="padding:8px;">السعر</th>
                    <th style="padding:8px;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    rows.forEach((r, idx) => {
        const item = ITEMS.find(i => String(i.id) === String(r.itemId));
        const itemName = item ? item.name : 'غير معروف';
        const calcs = getRowCalcs(r);
        html += `
            <tr>
                <td style="padding:8px; text-align:center;">${idx+1}</td>
                <td style="padding:8px;">${itemName}</td>
                <td style="padding:8px; text-align:center;">${r.selectedUnit || ''}</td>
                <td style="padding:8px; text-align:center;">${r.quantity}</td>
                <td style="padding:8px; text-align:center;">${fmtNum(r.price)}</td>
                <td style="padding:8px; text-align:center;">${fmtNum(calcs.total, '11')}</td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
        <div style="margin-top: 20px; text-align:left; font-size: 18px;">
            <strong>الإجمالي النهائي:</strong> ${grandTotal} ${currency}
        </div>
        <div style="margin-top: 50px; display:flex; justify-content:space-between;">
            <div><strong>${type === 'sales' ? 'البائع' : 'المستلم'}:</strong> ......................</div>
            <div><strong>توقيع ${type === 'sales' ? 'العميل' : 'المورد'}:</strong> ......................</div>
        </div>
    </div>
    `;

    // 2. The Warehouse Issue/Receipt Notes
    for (let whId in warehouseGroups) {
        let whName = "المستودع الرئيسي";
        if (whId) {
            let w = WAREHOUSES.find(x => x.id === whId);
            if (w) whName = w.name;
        }
        
        let noteTitle = type === 'sales' ? 'إذن صرف مخزني' : 'إذن استلام مخزني';

        html += `
        <div class="print-page" style="padding: 20px; direction: rtl; font-family: 'Cairo', Tahoma, Arial;">
            <div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
                <h2>${noteTitle}</h2>
                <h3>مستودع: ${whName}</h3>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom: 20px; font-size: 14px;">
                <div><strong>رقم الفاتورة المرجعي:</strong> ${invNo}</div>
                <div><strong>التاريخ:</strong> ${invDate}</div>
                <div><strong>${type === 'sales' ? 'العميل' : 'المورد'}:</strong> ${custName}</div>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;" border="1">
                <thead>
                    <tr style="background:#eee; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <th style="padding:8px;">م</th>
                        <th style="padding:8px;">الصنف</th>
                        <th style="padding:8px;">الوحدة</th>
                        <th style="padding:8px;">الكمية</th>
                        <th style="padding:8px;">ملاحظات المستودع</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        warehouseGroups[whId].forEach((r, idx) => {
            const item = ITEMS.find(i => String(i.id) === String(r.itemId));
            const itemName = item ? item.name : 'غير معروف';
            html += `
                <tr>
                    <td style="padding:8px; text-align:center;">${idx+1}</td>
                    <td style="padding:8px;">${itemName}</td>
                    <td style="padding:8px; text-align:center;">${r.selectedUnit || ''}</td>
                    <td style="padding:8px; text-align:center; font-weight:bold; font-size:16px;">${r.quantity}</td>
                    <td style="padding:8px;"></td>
                </tr>
            `;
        });
        
        html += `
                </tbody>
            </table>
            <div style="margin-top: 50px; display:flex; justify-content:space-between;">
                <div><strong>أمين المستودع:</strong> ......................</div>
                <div><strong>المستلم:</strong> ......................</div>
            </div>
        </div>
        `;
    }
    
    printArea.innerHTML = html;
}

// ════════════════════════════════════════
//  GPS TOGGLE
// ════════════════════════════════════════
function toggleGPS() {
    const btn = document.getElementById('btn-gps');
    const status = document.getElementById('gps-status');
    const latInp = document.getElementById('hdr-gps-lat');
    const lngInp = document.getElementById('hdr-gps-lng');

    if (latInp.value && lngInp.value) {
        // Turn off
        latInp.value = '';
        lngInp.value = '';
        btn.style.color = 'var(--text-muted)';
        btn.style.background = 'rgba(0,0,0,0.2)';
        status.textContent = 'GPS إيقاف';
        showToast('تم إيقاف ختم الموقع الجغرافي', 'warning');
    } else {
        // Turn on
        if (navigator.geolocation) {
            btn.style.color = '#fbbf24';
            status.textContent = 'جاري التحديد...';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    latInp.value = pos.coords.latitude;
                    lngInp.value = pos.coords.longitude;
                    btn.style.color = '#10b981';
                    btn.style.background = 'rgba(16,185,129,0.15)';
                    status.textContent = 'GPS نشط';
                    showToast('تم التقاط إحداثيات الموقع بنجاح ✅', 'success');
                },
                (err) => {
                    btn.style.color = '#f87171';
                    status.textContent = 'مرفوض';
                    showToast('تم رفض صلاحية الموقع أو فشل التحديد', 'error');
                }
            );
        } else {
            showToast('متصفحك لا يدعم تحديد الموقع', 'error');
        }
    }
}

// ════════════════════════════════════════
//  DELIVERY HUB & PRELIMINARY LOGIC
// ════════════════════════════════════════

function loadDeliveryTransactions() {
    const tbody = document.getElementById('delivery-transactions-body');
    const emptyState = document.getElementById('delivery-empty-state');
    
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let pendingInvoices = allSales.filter(inv => inv.saleType !== 'preliminary' && inv.status !== 'preliminary' && inv.isDelivered !== true);
    
    if (pendingInvoices.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'flex';
        return;
    }
    
    emptyState.style.display = 'none';
    tbody.innerHTML = '';
    
    pendingInvoices.sort((a,b) => new Date(b.timestamp) - new Date(a.timestamp));
    
    let warehouses = [];
    try { warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || []; } catch(e) {}
    let whOptions = warehouses.map(w => `<option value='${w.id}' ${w.isPrimary ? 'selected' : ''}>${w.name}</option>`).join('');
    if (!whOptions) whOptions = `<option value='main'>المخزن الرئيسي</option>`;

    pendingInvoices.forEach(inv => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td style='text-align:right; font-weight:800; color:var(--gold-primary);'>${inv.invoiceNo}</td>
            <td style='text-align:right; color:var(--text-dim);'>${new Date(inv.timestamp).toLocaleString('ar-EG-u-nu-latn')}</td>
            <td style='text-align:right; font-weight:600;'>${inv.customerName || inv.customer || 'عميل نقدي'}</td>
            <td style='text-align:center;'>
                <select id='del-wh-${inv.invoiceNo}' class='v-input' style='padding: 4px; font-size: 0.8rem;'>
                    ${whOptions}
                </select>
            </td>
            <td style='text-align:center;'>
                <button onclick="executeDelivery('${inv.invoiceNo}')" style='background:linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.35)); border:1px solid rgba(16,185,129,0.6); color:#34d399; font-weight:800; border-radius:8px; padding:6px 14px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-size:0.88rem; box-shadow:0 2px 8px rgba(16,185,129,0.2); transition:0.2s;' onmouseover="this.style.background='linear-gradient(135deg, rgba(16,185,129,0.35), rgba(5,150,105,0.5))'" onmouseout="this.style.background='linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.35))'">
                    <span>📦</span> <span>تسليم البضاعة</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filterDeliveryTransactions() {
    const query = document.getElementById('delivery-search').value.toLowerCase();
    const rows = document.querySelectorAll('#delivery-transactions-body tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

let currentDeliveryInvoiceNo = null;

function executeDelivery(invNo) {
    currentDeliveryInvoiceNo = invNo;
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let inv = allSales.find(i => i.invoiceNo === invNo);
    if (!inv) return;

    const modal = document.getElementById('delivery-confirmation-modal');
    if (!modal) {
        if (!confirm('هل أنت متأكد من تسليم الفاتورة رقم ' + invNo + ' وخصم البضاعة من المخزون؟')) return;
        confirmExecuteDeliveryAction();
        return;
    }

    const selWh = document.getElementById('del-wh-' + invNo);
    let selectedWhId = selWh ? selWh.value : (inv.warehouse || 'main');
    let warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
    let whName = warehouses.find(w => w.id === selectedWhId)?.name || 'المخزن الرئيسي';

    const invNoEl = document.getElementById('del-modal-inv-no');
    if (invNoEl) invNoEl.textContent = invNo;

    const custEl = document.getElementById('del-modal-cust-name');
    if (custEl) custEl.textContent = inv.customerName || inv.customer || 'عميل نقدي';

    const whEl = document.getElementById('del-modal-wh-name');
    if (whEl) whEl.textContent = whName;

    const amtEl = document.getElementById('del-modal-amount');
    if (amtEl) amtEl.textContent = fmtNum(inv.grandTotal || 0) + ' ' + (inv.currency || 'SDG');

    modal.style.display = 'flex';
}

function closeDeliveryConfirmationModal() {
    const modal = document.getElementById('delivery-confirmation-modal');
    if (modal) modal.style.display = 'none';
    currentDeliveryInvoiceNo = null;
}

function confirmExecuteDeliveryAction() {
    if (!currentDeliveryInvoiceNo) return;
    const invNo = currentDeliveryInvoiceNo;
    closeDeliveryConfirmationModal();

    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let invIndex = allSales.findIndex(i => i.invoiceNo === invNo);
    if (invIndex === -1) return;

    let inv = allSales[invIndex];
    let selWh = document.getElementById('del-wh-' + invNo);
    let selectedWhId = selWh ? selWh.value : (inv.warehouse || 'main');
    
    let warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
    let whName = warehouses.find(w => w.id === selectedWhId)?.name || 'المخزن الرئيسي';

    // Deduct Inventory
    let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
    
    if (inv.items) {
        for (const row of inv.items) {
            const item = typeof ITEMS !== 'undefined' ? ITEMS.find(i => String(i.id) === String(row.itemId)) : null;
            let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
            if (row.customFactor === undefined && item && row.selectedUnit && item.unitsArray) {
                const su = item.unitsArray.find(u => u.name === row.selectedUnit);
                if (su) selectedUnitFactor = su.factor;
            }
            let conversionFactor = item ? (item.conversionFactor || 1) : 1;
            let computedBaseQty = row.quantity * selectedUnitFactor * conversionFactor;
            
            let qtyToDeduct = row.customBaseQty !== undefined ? parseFloat(row.customBaseQty) : computedBaseQty;
            
            let itemInv = currentInvData.find(i => i.itemId === row.itemId && i.warehouse === whName);
            if (itemInv) {
                itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - qtyToDeduct;
            } else {
                currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: -qtyToDeduct, minQty: 10 });
            }
            
            if (item) {
                if (item.actualStock && item.actualStock[selectedWhId] !== undefined) {
                    item.actualStock[selectedWhId] -= qtyToDeduct;
                } else if (item.actualStock) {
                    item.actualStock[selectedWhId] = -qtyToDeduct;
                }
            }
        }
    }
    
    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));
    
    // Update Invoice Status
    inv.isDelivered = true;
    inv.deliveredAt = new Date().toISOString();
    inv.deliveryWarehouse = whName;
    
    allSales[invIndex] = inv;
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));

    showToast('✨ تم تسليم الفاتورة وخصم البضاعة من المخزن بنجاح!', 'success', 4000);
    loadDeliveryTransactions();
}

function convertToOfficial(invNo) {
    if (!confirm('تحويل الفاتورة المبدئية رقم ' + invNo + ' إلى فاتورة رسمية (تحديث الحسابات)؟')) return;

    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let invIndex = allSales.findIndex(i => i.invoiceNo === invNo);
    if (invIndex === -1) return;

    let inv = allSales[invIndex];
    if (inv.status !== 'preliminary') return;

    // Process Bank / Customer
    const grandAmount = parseFloat(inv.grandTotal) || 0;
    
    if (inv.paymentMethod !== 'credit' && inv.paymentAccountId) {
        let banks = [];
        try { banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
        const bIdx = banks.findIndex(b => b.id === inv.paymentAccountId);
        if (bIdx > -1) {
            let currentBalance = parseFloat(banks[bIdx].currentBalance);
            if (isNaN(currentBalance)) currentBalance = parseFloat(banks[bIdx].openingBalance) || 0;
            banks[bIdx].currentBalance = currentBalance + grandAmount;
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        }
    }
    
    if (inv.paymentMethod === 'credit' && inv.customer && inv.customer !== 'walk-in') {
        let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        const cIdx = customers.findIndex(c => c.id === inv.customer);
        if (cIdx > -1) {
            let currentBalance = parseFloat(customers[cIdx].currentBalance);
            if (isNaN(currentBalance)) currentBalance = 0;
            customers[cIdx].currentBalance = currentBalance + grandAmount;
            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
        }
    }

    inv.status = 'pending_delivery';
    inv.saleType = 'pending_delivery';
    allSales[invIndex] = inv;
    
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));

    showToast('تم تحويل الفاتورة المبدئية بنجاح!', 'success');
    if (typeof loadHubTransactions === 'function') loadHubTransactions();
}

async function sendInvoiceWA(invoiceNo) {
    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : localStorage.getItem('erp_wa_user_id');
        const res = await fetch(`http://localhost:3001/wa/status?userId=${userId}`);
        const data = await res.json();
        
        if (data.status !== 'CONNECTED' && data.status !== 'AUTHENTICATED') {
            const wantsToConnect = typeof showWAConnectPrompt === 'function' ? await showWAConnectPrompt() : confirm('رقم الواتساب غير مربوط. هل تريد الربط الآن؟');
            if (wantsToConnect) {
                if (typeof openWAModal === 'function') {
                    openWAModal();
                } else {
                    alert('يرجى الذهاب إلى شاشة الإعدادات لربط الواتساب.');
                }
            }
            return;
        }
    } catch (err) {
        alert('خدمة الواتساب غير متاحة حالياً. تأكد من تشغيل الخادم.');
        return;
    }

    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let inv = allSales.find(i => i.invoiceNo === invoiceNo);
    if (!inv) { alert('لم يتم العثور على الفاتورة'); return; }

    let defaultPhone = '';
    let customerName = inv.customerName || 'عميل نقدي (Walk-in)';
    if (inv.customer && inv.customer !== 'walk-in') {
        let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        let cust = customers.find(c => String(c.id) === String(inv.customer));
        if(cust) {
            defaultPhone = cust.whatsapp || cust.phone || '';
            customerName = cust.name || inv.customerName || 'عميل مسجل';
        }
    }

    const phone = await new Promise((resolve) => {
        const overlayId = 'wa-phone-prompt-overlay';
        if (document.getElementById(overlayId)) document.getElementById(overlayId).remove();

        window.toggleEditPhone = function() {
            const displayWrap = document.getElementById('wa-phone-display-wrap');
            const inputWrap = document.getElementById('wa-phone-input-wrap');
            if (displayWrap && inputWrap) {
                if (displayWrap.style.display !== 'none') {
                    displayWrap.style.display = 'none';
                    inputWrap.style.display = 'block';
                    const inp = document.getElementById('wa-prompt-input');
                    if (inp) {
                        inp.focus();
                        inp.select();
                    }
                } else {
                    displayWrap.style.display = 'flex';
                    inputWrap.style.display = 'none';
                }
            }
        };
        
        const html = `
        <div id="${overlayId}" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:999999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);">
            <div style="background:linear-gradient(145deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95)); border:1px solid rgba(37,211,102,0.4); border-radius:24px; padding:35px; width:90%; max-width:450px; text-align:center; transform:translateY(40px) scale(0.9); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow:0 25px 50px -12px rgba(37,211,102,0.2), inset 0 1px 0 rgba(255,255,255,0.1);">
                
                <div style="width:80px; height:80px; background:linear-gradient(135deg, #25D366, #128C7E); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#fff; box-shadow:0 10px 20px rgba(37,211,102,0.4); border:3px solid rgba(255,255,255,0.2);">
                    <svg viewBox="0 0 24 24" width="45" height="45" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                
                <h3 style="color:#f8fafc; font-size:1.6rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0; font-weight:800;">إرسال الفاتورة عبر واتساب</h3>
                <p style="color:#94a3b8; font-size:0.95rem; margin-bottom:20px; line-height:1.6; font-family:'Tajawal',sans-serif;">يرجى تأكيد إرسال الفاتورة إلى العميل:</p>
                
                <!-- Display card -->
                <div id="wa-phone-display-wrap" style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 15px 20px; margin-bottom: 25px; text-align: right;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">👤 عميل الفاتورة:</span>
                        <span style="font-size: 1.15rem; color: #25d366; font-weight: 800;">${customerName}</span>
                        <span style="font-size: 0.95rem; color: #38bdf8; font-weight: 700; direction: ltr; margin-top: 4px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>📞</span> <span id="wa-display-phone-text">${defaultPhone || 'لا يوجد رقم مسجل'}</span>
                        </span>
                    </div>
                    <button type="button" onclick="toggleEditPhone()" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); color: #38bdf8; border-radius: 12px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(56, 189, 248, 0.2)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(56, 189, 248, 0.1)'; this.style.transform='scale(1)';" title="إرسال لرقم آخر">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Input container (Hidden by default) -->
                <div id="wa-phone-input-wrap" style="display: none; margin-bottom: 25px; animation: wa-scale-up 0.3s ease-out; text-align: right;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">✏️ إرسال إلى رقم هاتف آخر:</span>
                        <button type="button" onclick="toggleEditPhone()" style="background: transparent; border: none; color: #cbd5e1; font-size: 0.75rem; cursor: pointer; text-decoration: underline; outline: none;">رجوع</button>
                    </div>
                    <div style="position: relative;">
                        <input type="tel" id="wa-prompt-input" placeholder="رقم الهاتف الجديد..." value="${defaultPhone}" oninput="document.getElementById('wa-display-phone-text').textContent = this.value;" style="width:100%; background:rgba(0,0,0,0.4); border:2px solid rgba(255,255,255,0.1); color:#fff; padding:12px 15px; border-radius:12px; font-size:1.15rem; text-align:center; transition:all 0.3s; outline:none; font-family:'Inter',sans-serif;" onfocus="this.style.borderColor='#25d366'; this.style.boxShadow='0 0 12px rgba(37,211,102,0.2)';" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-send" style="background:linear-gradient(135deg, #25d366, #1da851); color:#fff; border:none; padding:12px 30px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.3s; box-shadow:0 4px 15px rgba(37,211,102,0.4); display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(37,211,102,0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(37,211,102,0.4)';">
                        <span>إرسال</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                    <button id="wa-prompt-cancel" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">إلغاء</button>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        
        const overlay = document.getElementById(overlayId);
        const modal = overlay.children[0];
        const input = document.getElementById('wa-prompt-input');
        input.value = defaultPhone;

        // Animate in
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0) scale(1)';
            setTimeout(() => input.focus(), 400); // Focus input after animation
        });

        const closePrompt = (resultVal) => {
            overlay.style.opacity = '0';
            modal.style.transform = 'translateY(40px) scale(0.9)';
            setTimeout(() => {
                overlay.remove();
                resolve(resultVal);
            }, 400);
        };

        document.getElementById('wa-prompt-send').onclick = () => closePrompt(input.value.trim());
        document.getElementById('wa-prompt-cancel').onclick = () => closePrompt(null);
        input.addEventListener('keyup', (e) => {
            if(e.key === 'Enter') closePrompt(input.value.trim());
        });
    });

    if (!phone) return;

    const htmlContent = window.InvoicePrinter.buildHTML(inv, 'نسخة العميل');
    
    const printContainer = document.createElement('div');
    printContainer.className = 'print-preview-wrapper';
    printContainer.style.position = 'fixed';
    printContainer.style.top = '0';
    printContainer.style.left = '0';
    printContainer.style.width = '800px';
    printContainer.style.zIndex = '-99999';
    printContainer.style.opacity = '0.01';
    printContainer.style.pointerEvents = 'none';
    printContainer.style.padding = '24px';
    printContainer.style.background = '#f0f4f8';
    
    printContainer.innerHTML = htmlContent;
    document.body.appendChild(printContainer);

    const message = `مرحباً، مرفق فاتورتكم رقم ${invoiceNo} من ${JSON.parse(localStorage.getItem('user'))?.company_name || 'مؤسستنا'}. شكراً لتعاملكم معنا!`;
    
    try {
        await sendPDFViaWhatsApp(printContainer, phone, message);
    } catch (e) {
        console.error(e);
    } finally {
        printContainer.remove();
    }
}

function updateActionButtonsVisibility() {
    const previewBtn = document.getElementById('btn-preview-invoice');
    const waBtn = document.getElementById('btn-whatsapp-invoice');
    // Preview button always visible
    if (previewBtn) previewBtn.style.display = 'flex';
    if (currentViewedInvoiceNo) {
        if (waBtn) waBtn.style.display = 'flex';
    } else {
        if (waBtn) waBtn.style.display = 'none';
    }
}

async function sendCurrentInvoiceWA() {
    if (currentViewedInvoiceNo) {
        await sendInvoiceWA(currentViewedInvoiceNo);
    } else {
        showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
    }
}

// ════════════════════════════════════════════════════════════
// ✂️ SPLIT INVOICE FEATURE LOGIC
// ════════════════════════════════════════════════════════════

let activeSplitInvoice = null;

function loadSplitInvoices() {
    const searchVal = document.getElementById('split-invoice-search')?.value.toLowerCase() || '';
    const tbody = document.getElementById('split-invoices-body');
    const emptyState = document.getElementById('split-empty-state');
    if (!tbody) return;
    
    let invoices = [];
    try {
        invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    } catch(e) {}
    
    const eligible = invoices.filter(inv => 
        !inv.deleted && 
        inv.status === 'pending_delivery' && 
        inv.saleType !== 'preliminary' &&
        (!inv.wasSplit)
    );
    
    const filtered = eligible.filter(inv => {
        const no = (inv.invoiceNo || '').toLowerCase();
        const custName = (inv.customerName || '').toLowerCase();
        const total = String(inv.grandTotal || '').toLowerCase();
        return no.includes(searchVal) || custName.includes(searchVal) || total.includes(searchVal);
    });
    
    if (filtered.length === 0) {
        tbody.innerHTML = '';
        if (emptyState) emptyState.style.display = 'flex';
        return;
    }
    
    if (emptyState) emptyState.style.display = 'none';
    tbody.innerHTML = '';
    
    filtered.forEach(inv => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong style="color: #38bdf8; font-size: 1.15rem;">${inv.invoiceNo}</strong></td>
            <td><span style="font-weight: 800; font-size: 1.05rem; color:#cbd5e1;">${inv.date}</span></td>
            <td><span style="font-weight: bold; font-size: 1.1rem; color:#fff;">${inv.customerName || 'عميل نقدي'}</span></td>
            <td><span class="badge" style="background: rgba(99,102,241,0.2); color: #a5b4fc; padding: 6px 12px; border-radius: 8px; font-size: 0.95rem; font-weight: 900; display: inline-flex; align-items: center; gap: 4px;">${inv.paymentMethod === 'credit' ? '✍️ آجل' : '💵 نقدي'}</span></td>
            <td style="text-align: left; font-family: monospace; font-weight: 900; font-size: 1.3rem; color: #fff;">${parseFloat(inv.grandTotal).toLocaleString()} <span style="font-size:0.8rem; color:#94a3b8; font-family:'Tajawal', sans-serif;">SDG</span></td>
            <td style="text-align: center;"><span class="badge draft" style="padding: 6px 12px; border-radius: 8px; font-size: 0.95rem; font-weight: 900;">بانتظار التسليم</span></td>
            <td style="text-align: center;">
                <button class="btn-action" style="width: auto; padding: 6px 16px; font-size: 0.95rem; font-weight: 800; background: rgba(236,72,153,0.2); color: #f472b6; border: 1px solid rgba(236,72,153,0.45); border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(236,72,153,0.35)'" onmouseout="this.style.background='rgba(236,72,153,0.2)'" onclick="startInvoiceSplit('${inv.invoiceNo}')">تقسيم الفاتورة ✂️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filterSplitInvoices() {
    loadSplitInvoices();
}

function startInvoiceSplit(invoiceNo) {
    let invoices = [];
    try {
        invoices = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    } catch(e) {}
    
    const inv = invoices.find(x => x.invoiceNo === invoiceNo);
    if (!inv) {
        alert('حدث خطأ: لم يتم العثور على الفاتورة المحددة!');
        return;
    }
    
    activeSplitInvoice = JSON.parse(JSON.stringify(inv));
    
    document.getElementById('split-select-step').style.display = 'none';
    document.getElementById('split-board-step').style.display = 'flex';
    
    const headerInfo = document.getElementById('split-header-info');
    if (headerInfo) {
        headerInfo.innerHTML = `
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 8px; width: 100%;">
                <!-- Card 1: Original Invoice No -->
                <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 12px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                    <span style="font-size: 0.95rem; color: #cbd5e1; font-weight: bold;">🧾 الفاتورة الأصلية:</span>
                    <span style="font-size: 1.25rem; font-weight: 900; color: #38bdf8; font-family: monospace; text-shadow: 0 0 10px rgba(56,189,248,0.2);">${inv.invoiceNo}</span>
                </div>
                <!-- Card 2: Current Customer -->
                <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 12px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                    <span style="font-size: 0.95rem; color: #cbd5e1; font-weight: bold;">👤 العميل الحالي:</span>
                    <span style="font-size: 1.15rem; font-weight: 800; color: #f8fafc;">${inv.customerName || 'عميل نقدي'}</span>
                </div>
                <!-- Card 3: Payment Method -->
                <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 12px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                    <span style="font-size: 0.95rem; color: #cbd5e1; font-weight: bold;">💳 طريقة الدفع:</span>
                    <span style="font-size: 1.15rem; font-weight: 800; color: #c084fc; text-shadow: 0 0 10px rgba(168,85,247,0.2);">${inv.paymentMethod === 'credit' ? '✍️ آجل' : '💵 نقدي'}</span>
                </div>
                <!-- Card 4: Grand Total -->
                <div style="background: rgba(16, 185, 129, 0.07); border: 1px solid rgba(16, 185, 129, 0.3); padding: 8px 12px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(16,185,129,0.1);">
                    <span style="font-size: 0.95rem; color: #a7f3d0; font-weight: bold;">💰 القيمة الإجمالية:</span>
                    <span style="font-size: 1.25rem; font-weight: 900; color: #10b981; font-family: monospace; text-shadow: 0 0 10px rgba(16,185,129,0.2);">${parseFloat(inv.grandTotal).toLocaleString()} SDG</span>
                </div>
            </div>
        `;
    }
    
    const tbody = document.getElementById('split-items-body');
    if (tbody) {
        tbody.innerHTML = '';
        activeSplitInvoice.items.forEach((item, idx) => {
            const tr = document.createElement('tr');
            const originalQty = parseFloat(item.quantity || item.qty || 0);
            const price = parseFloat(item.price || 0);
            const totalVal = originalQty * price;
            
            // Resolve actual name from global ITEMS array
            const itemObj = (typeof ITEMS !== 'undefined' ? ITEMS.find(i => String(i.id) === String(item.itemId)) : null);
            const name = itemObj ? itemObj.name : (item.itemName || item.name || 'صنف غير معروف');
            
            tr.innerHTML = `
                <td style="font-weight: 800; font-size: 1.05rem; color: #fff;">${name}</td>
                <td style="text-align: center; font-weight: 900; font-size: 1.2rem; color: #cbd5e1;">${originalQty}</td>
                <td style="text-align: left; font-weight: 800; font-size: 1.15rem; color: #f8fafc; font-family: monospace;">${price.toLocaleString()} <span style="font-size:0.8rem; color:#cbd5e1; font-family: 'Tajawal', sans-serif;">SDG</span></td>
                <td style="text-align: center;">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <button class="btn-action" style="width:34px; height:34px; padding:0; line-height:34px; background: rgba(56,189,248,0.15); color:#38bdf8; border: 1px solid rgba(56,189,248,0.25); font-weight: bold; font-size: 1.2rem; border-radius: 6px; cursor: pointer;" onclick="adjustSplitQty(${idx}, -1)">-</button>
                        <input type="number" id="split-qty-a-${idx}" class="v-input" style="width: 90px; text-align: center; font-family: monospace; font-size: 1.2rem; font-weight: 900; border-color: rgba(56,189,248,0.4); margin: 0; padding: 6px; color: #38bdf8; background: rgba(0,0,0,0.2);" value="${originalQty}" min="0" max="${originalQty}" step="any" oninput="handleSplitQtyInput(${idx})">
                        <button class="btn-action" style="width:34px; height:34px; padding:0; line-height:34px; background: rgba(56,189,248,0.15); color:#38bdf8; border: 1px solid rgba(56,189,248,0.25); font-weight: bold; font-size: 1.2rem; border-radius: 6px; cursor: pointer;" onclick="adjustSplitQty(${idx}, 1)">+</button>
                    </div>
                </td>
                <td style="text-align: center;">
                    <span id="split-qty-b-${idx}" style="font-family: monospace; font-size: 1.35rem; font-weight: 900; color: #ec4899; text-shadow: 0 0 8px rgba(236,72,153,0.3);">0</span>
                </td>
                <td style="text-align: left; font-weight: 900; font-size: 1.45rem; color: #10b981; font-family: monospace;">${totalVal.toLocaleString()} <span style="font-size:0.8rem; color:#34d399; font-family: 'Tajawal', sans-serif;">SDG</span></td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    updateSplitCalculations();
}

function adjustSplitQty(idx, delta) {
    if (!activeSplitInvoice) return;
    const item = activeSplitInvoice.items[idx];
    const originalQty = parseFloat(item.quantity || item.qty || 0);
    const inputA = document.getElementById(`split-qty-a-${idx}`);
    if (!inputA) return;
    let qtyA = parseFloat(inputA.value) || 0;
    
    qtyA = Math.max(0, Math.min(originalQty, qtyA + delta));
    inputA.value = qtyA;
    
    handleSplitQtyInput(idx);
}

function handleSplitQtyInput(idx) {
    if (!activeSplitInvoice) return;
    const item = activeSplitInvoice.items[idx];
    const originalQty = parseFloat(item.quantity || item.qty || 0);
    const inputA = document.getElementById(`split-qty-a-${idx}`);
    if (!inputA) return;
    let qtyA = parseFloat(inputA.value) || 0;
    
    if (qtyA < 0) qtyA = 0;
    if (qtyA > originalQty) qtyA = originalQty;
    
    inputA.value = qtyA;
    
    const qtyB = originalQty - qtyA;
    const labelB = document.getElementById(`split-qty-b-${idx}`);
    if (labelB) {
        labelB.innerText = qtyB.toFixed(2).replace(/\.00$/, '');
    }
    
    updateSplitCalculations();
}

function updateSplitCalculations() {
    if (!activeSplitInvoice) return;
    
    let subtotalA = 0;
    let subtotalB = 0;
    
    activeSplitInvoice.items.forEach((item, idx) => {
        const price = parseFloat(item.price || 0);
        const originalQty = parseFloat(item.quantity || item.qty || 0);
        
        const inputA = document.getElementById(`split-qty-a-${idx}`);
        const qtyA = inputA ? parseFloat(inputA.value) || 0 : originalQty;
        const qtyB = originalQty - qtyA;
        
        subtotalA += qtyA * price;
        subtotalB += qtyB * price;
        
        const labelB = document.getElementById(`split-qty-b-${idx}`);
        if (labelB) {
            labelB.innerText = qtyB.toFixed(2).replace(/\.00$/, '');
        }
    });
    
    const originalSubtotal = parseFloat(activeSplitInvoice.subTotal) || 0;
    const originalDiscount = parseFloat(activeSplitInvoice.discount) || 0;
    const originalTax = parseFloat(activeSplitInvoice.vatAmount) || 0;
    
    let discountA = 0;
    let discountB = 0;
    let taxA = 0;
    let taxB = 0;
    
    if (originalSubtotal > 0) {
        const ratioA = subtotalA / originalSubtotal;
        const ratioB = subtotalB / originalSubtotal;
        
        discountA = originalDiscount * ratioA;
        discountB = originalDiscount * ratioB;
        
        taxA = originalTax * ratioA;
        taxB = originalTax * ratioB;
    }
    
    const grandA = Math.max(0, subtotalA - discountA + taxA);
    const grandB = Math.max(0, subtotalB - discountB + taxB);
    
    const elSubA = document.getElementById('split-subtotal-a');
    const elDiscA = document.getElementById('split-discount-a');
    const elTaxA = document.getElementById('split-tax-a');
    const elGrandA = document.getElementById('split-grand-a');
    
    if (elSubA) elSubA.innerText = subtotalA.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
    if (elDiscA) elDiscA.innerText = discountA.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
    if (elTaxA) elTaxA.innerText = taxA.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
    if (elGrandA) elGrandA.innerText = grandA.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
    
    const elSubB = document.getElementById('split-subtotal-b');
    const elDiscB = document.getElementById('split-discount-b');
    const elTaxB = document.getElementById('split-tax-b');
    const elGrandB = document.getElementById('split-grand-b');
    
    if (elSubB) elSubB.innerText = subtotalB.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
    if (elDiscB) elDiscB.innerText = discountB.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
    if (elTaxB) elTaxB.innerText = taxB.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
    if (elGrandB) elGrandB.innerText = grandB.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
}

function cancelCurrentSplit() {
    activeSplitInvoice = null;
    document.getElementById('split-board-step').style.display = 'none';
    document.getElementById('split-select-step').style.display = 'flex';
    loadSplitInvoices();
}

function submitInvoiceSplit() {
    if (!activeSplitInvoice) return;
    
    const itemsA = [];
    const itemsB = [];
    
    let hasQtyA = false;
    let hasQtyB = false;
    
    activeSplitInvoice.items.forEach((item, idx) => {
        const originalQty = parseFloat(item.quantity || item.qty || 0);
        const inputA = document.getElementById(`split-qty-a-${idx}`);
        const qtyA = inputA ? parseFloat(inputA.value) || 0 : originalQty;
        const qtyB = originalQty - qtyA;
        
        let customBaseA = undefined;
        let customBaseB = undefined;
        if (item.customBaseQty !== undefined) {
            const cbq = parseFloat(item.customBaseQty) || 0;
            customBaseA = originalQty > 0 ? cbq * (qtyA / originalQty) : 0;
            customBaseB = originalQty > 0 ? cbq * (qtyB / originalQty) : 0;
        }
        
        if (qtyA > 0) {
            hasQtyA = true;
            itemsA.push({
                ...item,
                qty: qtyA,
                quantity: qtyA,
                total: qtyA * parseFloat(item.price || 0),
                ...(customBaseA !== undefined && { customBaseQty: customBaseA })
            });
        }
        
        if (qtyB > 0) {
            hasQtyB = true;
            itemsB.push({
                ...item,
                qty: qtyB,
                quantity: qtyB,
                total: qtyB * parseFloat(item.price || 0),
                ...(customBaseB !== undefined && { customBaseQty: customBaseB })
            });
        }
    });
    
    if (!hasQtyA || !hasQtyB) {
        alert('خطأ: لا يمكن إتمام عملية تقسيم الفاتورة إلا إذا تم وضع صنف واحد على الأقل في كل فاتورة فرعية! (لا يمكن أن تكون إحداهما فارغة).');
        return;
    }
    
    if (!confirm('هل أنت متأكد من رغبتك في تقسيم هذه الفاتورة واعتماد الفاتورتين الجديدتين؟\nسيتم أرشفة الفاتورة القديمة تلقائياً.')) {
        return;
    }
    
    const invoiceNoA = `${activeSplitInvoice.invoiceNo}-1`;
    const invoiceNoB = `${activeSplitInvoice.invoiceNo}-2`;
    
    const subtotalA = itemsA.reduce((sum, item) => sum + item.total, 0);
    const originalSubtotal = parseFloat(activeSplitInvoice.subTotal) || 0;
    const originalDiscount = parseFloat(activeSplitInvoice.discount) || 0;
    const originalTax = parseFloat(activeSplitInvoice.vatAmount) || 0;
    
    const ratioA = subtotalA / originalSubtotal;
    const discountA = originalDiscount * ratioA;
    const taxA = originalTax * ratioA;
    const grandA = Math.max(0, subtotalA - discountA + taxA);
    const cashA = (parseFloat(activeSplitInvoice.cashAmount) || 0) * ratioA;
    const creditA = (parseFloat(activeSplitInvoice.creditAmount) || 0) * ratioA;
    const bankTransfersA = (activeSplitInvoice.bankTransfers || []).map(bt => ({...bt, amount: bt.amount * ratioA}));
    const chequesA = (activeSplitInvoice.cheques || []).map(ch => ({...ch, amount: ch.amount * ratioA}));
    const laborA = (parseFloat(activeSplitInvoice.laborCost) || 0) * ratioA;
    const laborFcA = (parseFloat(activeSplitInvoice.laborCostFc) || 0) * ratioA;
    const transportA = (parseFloat(activeSplitInvoice.transportCost) || 0) * ratioA;
    const transportFcA = (parseFloat(activeSplitInvoice.transportCostFc) || 0) * ratioA;
    const additionsA = (parseFloat(activeSplitInvoice.additionsCost) || 0) * ratioA;
    const additionsFcA = (parseFloat(activeSplitInvoice.additionsCostFc) || 0) * ratioA;
    
    const subtotalB = itemsB.reduce((sum, item) => sum + item.total, 0);
    const ratioB = subtotalB / originalSubtotal;
    const discountB = originalDiscount * ratioB;
    const taxB = originalTax * ratioB;
    const grandB = Math.max(0, subtotalB - discountB + taxB);
    const cashB = (parseFloat(activeSplitInvoice.cashAmount) || 0) * ratioB;
    const creditB = (parseFloat(activeSplitInvoice.creditAmount) || 0) * ratioB;
    const bankTransfersB = (activeSplitInvoice.bankTransfers || []).map(bt => ({...bt, amount: bt.amount * ratioB}));
    const chequesB = (activeSplitInvoice.cheques || []).map(ch => ({...ch, amount: ch.amount * ratioB}));
    const laborB = (parseFloat(activeSplitInvoice.laborCost) || 0) * ratioB;
    const laborFcB = (parseFloat(activeSplitInvoice.laborCostFc) || 0) * ratioB;
    const transportB = (parseFloat(activeSplitInvoice.transportCost) || 0) * ratioB;
    const transportFcB = (parseFloat(activeSplitInvoice.transportCostFc) || 0) * ratioB;
    const additionsB = (parseFloat(activeSplitInvoice.additionsCost) || 0) * ratioB;
    const additionsFcB = (parseFloat(activeSplitInvoice.additionsCostFc) || 0) * ratioB;
    
    const invoiceA = {
        ...activeSplitInvoice,
        invoiceNo: invoiceNoA,
        items: itemsA,
        subTotal: subtotalA,
        discount: discountA,
        vatAmount: taxA,
        grandTotal: grandA,
        cashAmount: cashA,
        creditAmount: creditA,
        bankTransfers: bankTransfersA,
        cheques: chequesA,
        laborCost: laborA,
        laborCostFc: laborFcA,
        transportCost: transportA,
        transportCostFc: transportFcA,
        additionsCost: additionsA,
        additionsCostFc: additionsFcA,
        timestamp: new Date().toISOString(),
        parentInvoice: activeSplitInvoice.invoiceNo
    };
    
    const invoiceB = {
        ...activeSplitInvoice,
        invoiceNo: invoiceNoB,
        items: itemsB,
        subTotal: subtotalB,
        discount: discountB,
        vatAmount: taxB,
        grandTotal: grandB,
        cashAmount: cashB,
        creditAmount: creditB,
        bankTransfers: bankTransfersB,
        cheques: chequesB,
        laborCost: laborB,
        laborCostFc: laborFcB,
        transportCost: transportB,
        transportCostFc: transportFcB,
        additionsCost: additionsB,
        additionsCostFc: additionsFcB,
        timestamp: new Date().toISOString(),
        parentInvoice: activeSplitInvoice.invoiceNo
    };
    
    let allSales = [];
    try {
        allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    } catch(e) {}
    
    const origIdx = allSales.findIndex(x => x.invoiceNo === activeSplitInvoice.invoiceNo);
    if (origIdx !== -1) {
        allSales[origIdx].deleted = true;
        allSales[origIdx].wasSplit = true;
        allSales[origIdx].splitTo = [invoiceNoA, invoiceNoB];
    }
    
    allSales.push(invoiceA);
    allSales.push(invoiceB);
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    
    localStorage.setItem('erp_inv_' + invoiceNoA, JSON.stringify(invoiceA));
    localStorage.setItem('erp_inv_' + invoiceNoB, JSON.stringify(invoiceB));
    
    // ── 1. Revert Old Invoice Effects ──
    if (typeof revertSalesInvoiceEffects === 'function') {
        revertSalesInvoiceEffects(activeSplitInvoice.invoiceNo, false);
    }
    
    // ── 2. Apply New Invoices Effects ──
    const applyEffects = (inv) => {
        if (inv.creditAmount > 0 && inv.customer && inv.customer !== 'walk-in') {
            let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const cIdx = customers.findIndex(c => c.id === inv.customer || c.id === inv.customerId);
            if (cIdx > -1) {
                let currentBalance = parseFloat(customers[cIdx].currentBalance);
                if (isNaN(currentBalance)) currentBalance = 0;
                customers[cIdx].currentBalance = currentBalance + parseFloat(inv.creditAmount);
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            }
        }
        
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        let banksModified = false;
        
        if (inv.cashAmount > 0 && inv.paymentAccountId) {
            const bIdx = banks.findIndex(b => b.id === inv.paymentAccountId);
            if (bIdx > -1) {
                let currentBalance = parseFloat(banks[bIdx].currentBalance);
                if (isNaN(currentBalance)) currentBalance = parseFloat(banks[bIdx].openingBalance) || 0;
                banks[bIdx].currentBalance = currentBalance + parseFloat(inv.cashAmount);
                banksModified = true;
            }
        }
        
        if (inv.bankTransfers && inv.bankTransfers.length > 0) {
            inv.bankTransfers.forEach(bt => {
                const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
                if (bIdx > -1) {
                    let currentBalance = parseFloat(banks[bIdx].currentBalance);
                    if (isNaN(currentBalance)) currentBalance = parseFloat(banks[bIdx].openingBalance) || 0;
                    banks[bIdx].currentBalance = currentBalance + parseFloat(bt.amount);
                    banksModified = true;
                }
            });
        }
        
        if (banksModified) {
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        }
    };
    
    applyEffects(invoiceA);
    applyEffects(invoiceB);
    
    if (typeof showToast === 'function') {
        showToast('تم تقسيم الفاتورة واعتماد المستندين الجديدين بنجاح! ✂️', 'success');
    } else {
        alert('تم تقسيم الفاتورة واعتماد المستندين الجديدين بنجاح! ✂️');
    }
    
    showSalesHub();
}

// Keydown delegation for payment inputs to block typing when value exceeds grand total
document.addEventListener('keydown', function(event) {
    const input = event.target;
    if (!(input.id === 'pay-cash-amount' || input.classList.contains('bt-amount-input') || input.classList.contains('ch-amount-input'))) {
        return;
    }
    
    // Allow control keys
    if (event.key === 'Backspace' || event.key === 'Delete' || event.key === 'ArrowLeft' || event.key === 'ArrowRight' || 
        event.key === 'ArrowUp' || event.key === 'ArrowDown' || event.key === 'Tab' || event.key === 'Escape' || event.key === 'Enter') {
        return;
    }
    
    // Only intercept digits and decimal points
    const isDigit = /^[0-9]$/.test(event.key);
    const isDot = event.key === '.';
    if (!isDigit && !isDot) {
        if (!event.ctrlKey && !event.metaKey && event.key.length === 1) {
            event.preventDefault();
        }
        return;
    }
    
    // Construct potential new value
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const oldVal = input.value;
    const newValStr = oldVal.slice(0, start) + event.key + oldVal.slice(end);
    const newVal = parseFloat(newValStr) || 0;
    
    // Calculate potential total payment
    const invTotal = typeof getInvoiceTotal === 'function' ? getInvoiceTotal() : 0;
    const labor = parseNum(document.getElementById('t-labor')?.value || 0);
    const transport = parseNum(document.getElementById('t-transport')?.value || 0);
    const discountAmt = parseNum(document.getElementById('discount-amt')?.value || 0);
    const taxAmt = parseNum(document.getElementById('tax-amt')?.value || 0);
    const additionAmt = parseNum(document.getElementById('addition-amt')?.value || 0);
    const grand = invTotal + labor + transport + additionAmt + taxAmt - discountAmt;
    
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    let cash = 0;
    if (cashActive) {
        cash = (input.id === 'pay-cash-amount') ? newVal : parseNum(document.getElementById('pay-cash-amount')?.value || 0);
    }
    
    let transfersSum = 0;
    if (bankActive) {
        document.querySelectorAll('.bt-amount-input').forEach(inp => {
            if (inp === input) {
                transfersSum += newVal;
            } else {
                transfersSum += parseNum(inp.value);
            }
        });
    }
    
    let chequesSum = 0;
    if (chequeActive) {
        document.querySelectorAll('.ch-amount-input').forEach(inp => {
            if (inp === input) {
                chequesSum += newVal;
            } else {
                chequesSum += parseNum(inp.value);
            }
        });
    }
    
    const potentialTotalPaid = cash + transfersSum + chequesSum;
    if (potentialTotalPaid > grand + 0.01) {
        event.preventDefault(); // Block the typing!
        if (typeof PlaySound !== 'undefined') PlaySound.error();
        
        showToast('⚠️ المبلغ الذي تم إدخاله أكبر من قيمة الفاتورة!', 'warning');
        
        // Shake the input container and turn border red immediately
        input.classList.remove('input-error-shake');
        void input.offsetWidth; // trigger reflow
        input.classList.add('input-error-shake');
    }
});

// Blur delegation to format payment inputs to 2 decimal places
document.addEventListener('blur', function(event) {
    const input = event.target;
    if (input.id === 'pay-cash-amount' || input.classList.contains('bt-amount-input') || input.classList.contains('ch-amount-input')) {
        const val = parseFloat(input.value) || 0;
        input.value = val.toFixed(2);
    }
}, true);

// Focus delegation to clear zero values instantly on click/focus
document.addEventListener('focus', function(event) {
    const input = event.target;
    if (input.id === 'pay-cash-amount' || input.classList.contains('bt-amount-input') || input.classList.contains('ch-amount-input')) {
        const val = parseFloat(input.value) || 0;
        if (val === 0) {
            input.value = '';
        }
    }
}, true);

// ════════════════════════════════════════
//  SALES POSTING SYSTEM
// ════════════════════════════════════════

function revertSalesInvoiceEffects(invNo, revertInventory = true) {
    let oldInvStr = localStorage.getItem('erp_inv_' + invNo);
    let oldInv = null;
    if (oldInvStr) {
        try { oldInv = JSON.parse(oldInvStr); } catch(e) {}
    }
    if (!oldInv) {
        let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
        oldInv = allSales.find(inv => inv.invoiceNo === invNo || inv.id === invNo);
    }
    if (!oldInv) return;

    let hadEffects = oldInv.effectsApplied === true;
    if (oldInv.effectsApplied === undefined) {
        hadEffects = (oldInv.status !== 'preliminary' && oldInv.saleType !== 'preliminary');
    }

    const grandAmount = parseFloat(oldInv.grandTotal) || 0;

    if (hadEffects) {
        // 1. Revert Customer Balance
        let creditRevertAmount = 0;
        if (oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') {
            creditRevertAmount = parseFloat(oldInv.creditAmount) || 0;
        } else if (oldInv.paymentMethod === 'credit') {
            creditRevertAmount = grandAmount;
        }
        if (creditRevertAmount > 0 && oldInv.customer && oldInv.customer !== 'walk-in') {
            let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const cIdx = customers.findIndex(c => c.id === oldInv.customer);
            if (cIdx > -1) {
                let currentBalance = parseFloat(customers[cIdx].currentBalance) || 0;
                customers[cIdx].currentBalance = currentBalance - creditRevertAmount;
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            }
        }

        // 2. Revert Bank/Safe Balance
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        let safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
        
        if (oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') {
            if (oldInv.cashAmount > 0 && oldInv.paymentAccountId) {
                const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
                if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - parseFloat(oldInv.cashAmount);
                else {
                    const sIdx = safes.findIndex(s => s.id === oldInv.paymentAccountId);
                    if (sIdx > -1) safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) - parseFloat(oldInv.cashAmount);
                }
            }
            if (oldInv.bankTransfers && oldInv.bankTransfers.length > 0) {
                oldInv.bankTransfers.forEach(bt => {
                    const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
                    if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - parseFloat(bt.amount);
                    else {
                        const sIdx = safes.findIndex(s => s.id === bt.bankAccountId);
                        if (sIdx > -1) safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) - parseFloat(bt.amount);
                    }
                });
            }
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
        } else if (oldInv.paymentMethod !== 'credit' && oldInv.paymentAccountId) {
            const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - grandAmount;
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            } else {
                const sIdx = safes.findIndex(s => s.id === oldInv.paymentAccountId);
                if (sIdx > -1) {
                    safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) - grandAmount;
                    localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
                }
            }
        }
    }

    if (revertInventory) {
        // 3. Revert Inventory (Only for backwards compatibility if needed, or if deleting entirely)
        let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        const mainWh = oldInv.warehouse || 'wh-001';
        if (oldInv.items && Array.isArray(oldInv.items)) {
            for (const row of oldInv.items) {
                const item = ITEMS.find(i => String(i.id) === String(row.itemId));
                if (!item) continue;
                
                let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
                let baseQty = 0;
                if (row.customBaseQty !== undefined) {
                    baseQty = row.customBaseQty;
                } else {
                    baseQty = item ? row.quantity * selectedUnitFactor * item.conversionFactor : 0;
                }

                const whId = row.warehouseId || mainWh;
                const whName = WAREHOUSES.find(w => w.id === whId)?.name || 'المخزن الرئيسي';
                
                let itemInv = currentInvData.find(inv => inv.itemId === row.itemId && inv.warehouse === whName);
                if (itemInv) {
                    itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) + baseQty;
                } else {
                    currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: baseQty, minQty: 10 });
                }
                
                if (item.actualStock && item.actualStock[whId] !== undefined) {
                    item.actualStock[whId] += baseQty;
                }
            }
        }
        localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));
    }
}

function updateSalesPostingStatusBar(status) {
    const bar = document.getElementById('posting-status-bar');
    const icon = document.getElementById('posting-status-icon');
    const text = document.getElementById('posting-status-text');
    const desc = document.getElementById('posting-status-desc');
    const actions = document.getElementById('posting-status-actions');
    
    if (!bar) return;

    const postingMode = window.getPostingMode ? window.getPostingMode() : 'auto';
    
    if (postingMode === 'auto' || status === 'new' || status === 'preliminary' || status === 'draft') {
        bar.style.display = 'none';
        
        // Remove immutability if it was set
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.hasAttribute('data-immutability-locked')) {
                el.disabled = false;
                el.removeAttribute('data-immutability-locked');
            }
        });
        const saveBtn = document.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.style.display = 'flex';
        }
        return;
    }

    bar.style.display = 'flex';
    
    const invNo = document.getElementById('hdr-inv-no').value;

    if (status === 'posted') {
        bar.style.background = 'rgba(16, 185, 129, 0.1)';
        bar.style.borderColor = 'rgba(16, 185, 129, 0.3)';
        icon.innerText = '✅';
        text.innerText = 'فاتورة مُرحّلة';
        text.style.color = '#10b981';
        desc.innerText = 'تم إثبات الأثر المالي لهذه الفاتورة على الأرصدة.';
        
        actions.innerHTML = `
            <button onclick="unpostSalesInvoice('${invNo}')" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                ↩️ إلغاء الترحيل
            </button>
        `;
        
        // Make form immutable
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (!el.disabled && el.id !== 'search-input') {
                el.disabled = true;
                el.setAttribute('data-immutability-locked', 'true');
            }
        });
        const saveBtn = document.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.style.display = 'none';
        }
        
    } else {
        // Confirmed but not posted (pending_delivery)
        bar.style.background = 'rgba(245, 158, 11, 0.1)';
        bar.style.borderColor = 'rgba(245, 158, 11, 0.3)';
        icon.innerText = '⏳';
        text.innerText = 'بانتظار الترحيل';
        text.style.color = '#f59e0b';
        desc.innerText = 'الفاتورة مؤكدة ولكن لم يتم ترحيلها لتؤثر في الأرصدة.';
        
        actions.innerHTML = `
            <button onclick="postSalesInvoice('${invNo}')" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                🚀 ترحيل الآن
            </button>
        `;
        
        // Ensure form is mutable
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.hasAttribute('data-immutability-locked')) {
                el.disabled = false;
                el.removeAttribute('data-immutability-locked');
            }
        });
        const saveBtn = document.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.style.display = 'flex';
        }
    }
}

function postSalesInvoice(invNo) {
    if (!invNo) return;
    
    let str = localStorage.getItem('erp_inv_' + invNo);
    if (!str) return;
    
    let oldInv = null;
    try { oldInv = JSON.parse(str); } catch(e) { return; }
    
    if (oldInv.status === 'posted') {
        showToast('الفاتورة مُرحلة مسبقاً', 'warning');
        return;
    }
    
    const grandAmount = parseFloat(oldInv.grandTotal) || 0;
    
    // Apply financial effects
    // A. Cash
    let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    if ((oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') && oldInv.cashAmount > 0 && oldInv.paymentAccountId) {
        const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
        if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + parseFloat(oldInv.cashAmount);
    } else if (oldInv.paymentMethod !== 'credit' && oldInv.paymentMethod !== 'hybrid' && oldInv.paymentMethod !== 'multipay' && oldInv.paymentAccountId) {
        const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
        if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + grandAmount;
    }
    
    // B. Bank Transfers
    if (oldInv.bankTransfers && oldInv.bankTransfers.length > 0) {
        oldInv.bankTransfers.forEach(bt => {
            const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
            if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + parseFloat(bt.amount);
        });
    }
    localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
    
    // C. Customer Balance
    let creditRevertAmount = 0;
    if (oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') {
        creditRevertAmount = parseFloat(oldInv.creditAmount) || 0;
    } else if (oldInv.paymentMethod === 'credit') {
        creditRevertAmount = grandAmount;
    }
    if (creditRevertAmount > 0 && oldInv.customer && oldInv.customer !== 'walk-in') {
        let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        const cIdx = customers.findIndex(c => c.id === oldInv.customer);
        if (cIdx > -1) {
            customers[cIdx].currentBalance = (parseFloat(customers[cIdx].currentBalance) || 0) + creditRevertAmount;
            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
        }
    }
    
    // Update invoice status
    oldInv.status = 'posted';
    oldInv.effectsApplied = true;
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(oldInv));
    
    // Update in global array
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const idx = allSales.findIndex(inv => inv.invoiceNo === invNo);
    if (idx !== -1) {
        allSales[idx].status = 'posted';
        allSales[idx].effectsApplied = true;
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    }
    
    updateSalesPostingStatusBar('posted');
    showToast('✅ تم ترحيل الفاتورة بنجاح', 'success');
}

function unpostSalesInvoice(invNo) {
    if (!invNo) return;
    
    if (!confirm('هل أنت متأكد من إلغاء ترحيل هذه الفاتورة؟ سيتم عكس التأثيرات المالية.')) return;
    
    revertSalesInvoiceEffects(invNo, false);
    
    let str = localStorage.getItem('erp_inv_' + invNo);
    if (!str) return;
    let oldInv = null;
    try { oldInv = JSON.parse(str); } catch(e) { return; }
    
    oldInv.status = 'pending_delivery';
    oldInv.effectsApplied = false;
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(oldInv));
    
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const idx = allSales.findIndex(inv => inv.invoiceNo === invNo);
    if (idx !== -1) {
        allSales[idx].status = 'pending_delivery';
        allSales[idx].effectsApplied = false;
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    }
    
    updateSalesPostingStatusBar('pending_delivery');
    showToast('↩️ تم إلغاء ترحيل الفاتورة بنجاح', 'success');
}

// Deep Drill-down
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const invoiceId = urlParams.get('id');
    if (invoiceId) {
        setTimeout(() => {
            if (typeof loadInvoiceFromStorage === 'function') loadInvoiceFromStorage(invoiceId);
        }, 500);
    }
});
