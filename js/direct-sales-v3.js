
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
    const rawErp = localStorage.getItem('erp_sales_invoices_v1') || '';
    const rawLocal = localStorage.getItem('magaies_local_sales_invoices_v1') || '';
    const rawQueue = localStorage.getItem('magaies_mobile_offline_queue_v2') || '';
    const cacheKey = rawErp.length + '|' + rawLocal.length + '|' + rawQueue.length + '|' + rawErp.slice(0, 64) + '|' + rawLocal.slice(0, 64);
    if (window._cachedSalesInvoicesRaw === cacheKey && window._cachedSalesSorted) {
        return window._cachedSalesSorted;
    }

    const mergedMap = new Map();
    const addInv = (inv) => {
        if (!inv || typeof inv !== 'object') return;
        const no = String(inv.invoiceNo || inv.id || '').trim();
        if (!no || no === 'preliminary' || no.startsWith('PRE-')) return;
        if (!mergedMap.has(no)) {
            mergedMap.set(no, Object.assign({ invoiceNo: no, id: no }, inv));
        } else {
            const prev = mergedMap.get(no);
            const combined = Object.assign({}, prev);
            Object.keys(inv).forEach(k => {
                const val = inv[k];
                if (Array.isArray(val)) {
                    if (val.length > 0) combined[k] = val;
                } else if (val !== undefined && val !== null && val !== '') {
                    combined[k] = val;
                }
            });
            combined.invoiceNo = no;
            combined.id = no;
            mergedMap.set(no, combined);
        }
    };

    try { (JSON.parse(rawErp || '[]') || []).forEach(addInv); } catch(e) {}
    try { (JSON.parse(rawLocal || '[]') || []).forEach(addInv); } catch(e) {}
    try {
        if (window.MagaiesOffline && typeof MagaiesOffline.getQueue === 'function') {
            MagaiesOffline.getQueue().forEach(q => {
                if ((q.type === 'sales' || q.type === 'sales_split') && q.payload) {
                    const qNo = q.offline_id || q.tempId || q.payload.invoiceNo || q.payload.id;
                    addInv(Object.assign({ invoiceNo: qNo, id: qNo }, q.payload));
                }
            });
        }
    } catch(e) {}

    const sorted = Array.from(mergedMap.values()).sort((a, b) => {
        const cmp = String(a.date || a.savedAt || a.createdAt || '').localeCompare(String(b.date || b.savedAt || b.createdAt || ''));
        if (cmp !== 0) return cmp;
        const tcmp = String(a.time || '').localeCompare(String(b.time || ''));
        if (tcmp !== 0) return tcmp;
        return String(a.invoiceNo || '').localeCompare(String(b.invoiceNo || ''));
    });

    window._cachedSalesInvoicesRaw = cacheKey;
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
        const extractSalesSeq = (raw) => {
            const s = String(raw || '').trim();
            if (!s || s === 'preliminary' || s.startsWith('PRE-')) return 0;
            if (s.startsWith(datePrefix)) {
                const n = parseInt(s.substring(datePrefix.length), 10);
                if (!isNaN(n) && n > 0 && n < 1000000) return n;
            }
            const m = s.match(/^S\d{4}(\d{3,6})$/i);
            if (m) {
                const n = parseInt(m[1], 10);
                if (!isNaN(n) && n > 0 && n < 1000000) return n;
            }
            if (s.includes('-')) {
                let parts = s.split('-');
                let noStr = parts.length > 1 ? parts[1] : parts[0];
                if (noStr.length > 4) {
                    const n = parseInt(noStr.substring(4), 10);
                    if (!isNaN(n) && n > 0 && n < 1000000) return n;
                }
            }
            return 0;
        };

        list.forEach(inv => {
            if (inv.saleType !== 'preliminary' && (!inv.invoiceNo || !inv.invoiceNo.startsWith('PRE-'))) {
                const seq = extractSalesSeq(inv.invoiceNo || inv.id);
                if (seq > max) max = seq;
            }
        });
        if (window._mirrorNextSalesNo) {
            const mSeq = extractSalesSeq(window._mirrorNextSalesNo);
            if (mSeq - 1 > max) max = mSeq - 1;
        }
        if (window.MagaiesOfflineSync && typeof window.MagaiesOfflineSync.getHighestKnownSeq === 'function') {
            const offMax = window.MagaiesOfflineSync.getHighestKnownSeq('save_sales_invoice');
            if (offMax > max) max = offMax;
        }
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
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.filterEntitiesByClearance === 'function') {
                localCusts = window.MagaieesPermissions.filterEntitiesByClearance(localCusts);
            }
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
        if (window.renderDocumentUserBadge) {
            window.renderDocumentUserBadge('sales-user-audit-container', {}, true);
        }

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
        if (window.MagaieesPermissions && typeof window.MagaieesPermissions.filterEntitiesByClearance === 'function') {
            safes = window.MagaieesPermissions.filterEntitiesByClearance(safes);
            banks = window.MagaieesPermissions.filterEntitiesByClearance(banks);
        }
        
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
        if (window.MagaieesPermissions && typeof window.MagaieesPermissions.filterEntitiesByClearance === 'function') {
            safes = window.MagaieesPermissions.filterEntitiesByClearance(safes);
            banks = window.MagaieesPermissions.filterEntitiesByClearance(banks);
        }
        
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
    if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();

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
    if (typeof window.autoFitVoucherNumbers === 'function') window.autoFitVoucherNumbers();
}

// ════════════════════════════════════════
//  CUSTOMER CHANGE → Financial Panel (Unified below with Customer Dropdown)
// ════════════════════════════════════════
function toggleFinancialPanel() { /* deprecated - kept for compatibility */ }
function expandFinancialPanel()  { if (typeof onCustomerChange === 'function') onCustomerChange(); }
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
window.isInvoiceEditingPermitted = false;
window.isAmendingInvoice = false;

function showEditBanner(invNo, opts = {}) {
    const banner = document.getElementById('saved-invoice-edit-banner');
    if (!banner) return;

    const isPosted = !!opts.isPosted;
    const isLocked = !!opts.isLocked;
    const isAmended = !!opts.isAmended;

    if (isPosted && isLocked) {
        // 🔒 Locked & Posted State (المطابق للطلب: الفاتورة مرحّلة وممنوع التعديل إلا بطلب صريح)
        banner.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 78, 59, 0.35))';
        banner.style.borderColor = 'rgba(16, 185, 129, 0.6)';
        banner.style.boxShadow = '0 4px 20px rgba(16, 185, 129, 0.25)';
        banner.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 2rem;">🔒</span>
                    <div>
                        <div style="color: #34d399; font-size: 1.15rem; font-weight: 900; display: flex; align-items: center; gap: 8px;">
                            <span>الفاتورة مرحّلة</span>
                            <span style="background: rgba(16, 185, 129, 0.25); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.5); padding: 2px 10px; border-radius: 12px; font-size: 0.85rem; font-family:'Outfit',sans-serif;">#${invNo}</span>
                            ${isAmended ? '<span style="background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.5); padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; font-weight: 800;">نسخة معدّلة</span>' : ''}
                        </div>
                        <div style="color: #cbd5e1; font-size: 0.88rem; font-weight: 600; margin-top: 3px;">
                            تم إثبات الأثر المالي والمخزني لهذه الفاتورة. الفاتورة مقفلة للقراءة فقط — لإجراء أي تعديل اضغط على زر «تعديل الفاتورة».
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" onclick="requestEditPostedInvoice('${invNo}')" style="background: linear-gradient(135deg, #f59e0b, #d97706); border: none; color: #fff; padding: 8px 18px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.95rem; box-shadow: 0 2px 10px rgba(245,158,11,0.35); transition: 0.2s; display: flex; align-items: center; gap: 6px;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 15px rgba(245,158,11,0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 10px rgba(245,158,11,0.35)';" title="فك القفل وتفعيل وضع التعديل الرسمي">
                        <span>✏️</span> تعديل الفاتورة
                    </button>
                    <button type="button" onclick="onSuccessNewInvoice(false)" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); color: #fff; padding: 8px 14px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.88rem; transition: 0.2s;">
                        ✨ جديد
                    </button>
                </div>
            </div>
        `;
    } else if (isPosted && !isLocked) {
        // ⚠️ Active Editing of Posted Invoice (Amendment Mode)
        banner.style.background = 'linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(180, 83, 9, 0.4))';
        banner.style.borderColor = 'rgba(245, 158, 11, 0.8)';
        banner.style.boxShadow = '0 4px 20px rgba(245, 158, 11, 0.35)';
        banner.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 2rem;">⚠️</span>
                    <div>
                        <div style="color: #fbbf24; font-size: 1.15rem; font-weight: 900; display: flex; align-items: center; gap: 8px;">
                            <span>أنت الآن في وضع تعديل الفاتورة المرحّلة</span>
                            <span style="background: rgba(245, 158, 11, 0.3); color: #fef08a; border: 1px solid rgba(245, 158, 11, 0.5); padding: 2px 10px; border-radius: 12px; font-size: 0.85rem; font-family:'Outfit',sans-serif;">#${invNo}</span>
                        </div>
                        <div style="color: #fde68a; font-size: 0.88rem; font-weight: 600; margin-top: 3px;">
                            تنبيه: سيتم حفظ التعديلات وتحديث قيود الحسابات والأرصدة، وإرسال إشعار رسمي للعميل يفيد بأن هذه الفاتورة قد تم تعديلها.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" onclick="cancelEditPostedInvoice('${invNo}')" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 8px 14px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.88rem; transition: 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.3)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.2)'">
                        ↩️ إلغاء التعديل
                    </button>
                    <button type="button" onclick="onSuccessNewInvoice(false)" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); color: #fff; padding: 8px 14px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.88rem; transition: 0.2s;">
                        ✨ جديد
                    </button>
                </div>
            </div>
        `;
    } else {
        // Standard view or draft
        banner.style.background = 'linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(30, 58, 138, 0.25))';
        banner.style.borderColor = 'rgba(56, 189, 248, 0.6)';
        banner.style.boxShadow = '0 4px 15px rgba(56, 189, 248, 0.2)';
        banner.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 1.6rem;">📄</span>
                    <div>
                        <div style="color: #38bdf8; font-size: 1.1rem; font-weight: 900;">
                            فاتورة محفوظة (<span style="color:#ffffff; font-family:'Outfit',sans-serif; text-decoration: underline;">${invNo}</span>)
                        </div>
                        <div style="color: #bae6fd; font-size: 0.85rem; font-weight: 600; margin-top: 2px;">
                            يمكنك تعديل الفاتورة أو طباعتها أو بدء فاتورة جديدة.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" onclick="onSuccessNewInvoice(false)" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 7px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.85rem; transition: 0.2s;">
                        ✨ جديد
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

    let isAmended = false;
    if (invNo) {
        try {
            const str = localStorage.getItem('erp_inv_' + invNo);
            if (str) {
                const parsed = JSON.parse(str);
                isAmended = !!(parsed.isAmended || parsed.amendmentCount);
            }
        } catch(e) {}
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

        // Save Button vs Locked Badge & Edit button
        const saveBtn = editor.querySelector('.btn-submit');
        let lockedBadge = document.getElementById('locked-submit-badge');

        if (isLocked) {
            if (saveBtn) saveBtn.style.display = 'none';
            const badgeContent = `
                <div style="display: flex; gap: 8px; width: 100%; align-items: center; justify-content: center;">
                    <div style="flex: 1; height: 45px; display: flex; align-items: center; justify-content: center; gap: 6px; background: rgba(16, 185, 129, 0.15); border: 1.5px solid rgba(16, 185, 129, 0.4); color: #34d399; border-radius: 8px; font-weight: 800; font-size: 0.95rem;">
                        <span style="font-size:1.15rem;">🔒</span> <span>الفاتورة مرحّلة</span>
                    </div>
                    <button type="button" class="btn-action" onclick="requestEditPostedInvoice('${invNo}')" style="flex: 1.2; height: 45px; background: linear-gradient(135deg, #f59e0b, #d97706); border: none; color: #fff; font-size: 0.95rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: center; gap: 6px;" onmouseover="this.style.boxShadow='0 4px 15px rgba(245, 158, 11, 0.5)';" onmouseout="this.style.boxShadow='0 2px 10px rgba(245, 158, 11, 0.3)';" title="فك القفل وتفعيل وضع التعديل الرسمي">
                        <span>✏️</span> <span>تعديل الفاتورة</span>
                    </button>
                </div>
            `;
            if (!lockedBadge && saveBtn && saveBtn.parentNode) {
                lockedBadge = document.createElement('div');
                lockedBadge.id = 'locked-submit-badge';
                lockedBadge.style.cssText = 'flex: 1.5; height: 45px; display: flex; align-items: center; justify-content: center;';
                lockedBadge.innerHTML = badgeContent;
                saveBtn.parentNode.insertBefore(lockedBadge, saveBtn);
            } else if (lockedBadge) {
                lockedBadge.style.display = 'flex';
                lockedBadge.innerHTML = badgeContent;
            }
        } else {
            if (saveBtn) {
                saveBtn.style.display = 'flex';
                if (window.isInvoiceEditingPermitted || window.isAmendingInvoice) {
                    saveBtn.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
                    saveBtn.style.boxShadow = '0 2px 10px rgba(245, 158, 11, 0.4)';
                    saveBtn.innerHTML = '💾 تأكيد وحفظ التعديل وإرسال الإشعار';
                } else {
                    saveBtn.style.background = 'linear-gradient(135deg, #0ea5e9, #2563eb)';
                    saveBtn.style.boxShadow = '0 2px 10px rgba(56, 189, 248, 0.4)';
                    saveBtn.innerHTML = '✅ تأكيد وحفظ';
                }
            }
            if (lockedBadge) lockedBadge.style.display = 'none';
        }
    }

    if (invNo) {
        showEditBanner(invNo, { isLocked: isLocked, isPosted: isPosted, isAmended: isAmended });
    }
}

function requestEditPostedInvoice(invNo) {
    if (!invNo) invNo = document.getElementById('hdr-inv-no')?.value || currentViewedInvoiceNo;
    if (!invNo) return;

    // Inject styles if not present
    if (!document.getElementById('edit-warning-modal-styles')) {
        const style = document.createElement('style');
        style.id = 'edit-warning-modal-styles';
        style.textContent = `
            @keyframes pulseWarnGlow {
                0%, 100% {
                    box-shadow: 0 0 15px rgba(245, 158, 11, 0.4), inset 0 0 10px rgba(245, 158, 11, 0.2);
                    transform: scale(1);
                }
                50% {
                    box-shadow: 0 0 28px rgba(239, 68, 68, 0.75), inset 0 0 15px rgba(239, 68, 68, 0.35);
                    transform: scale(1.06);
                }
            }
            @keyframes editModalCardIn {
                0% {
                    opacity: 0;
                    transform: scale(0.92) translateY(16px);
                }
                100% {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Remove any existing overlay
    const existing = document.getElementById('edit-posted-invoice-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'edit-posted-invoice-overlay';
    overlay.style.cssText = `
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(8, 13, 24, 0.82);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 9999999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.25s ease;
        padding: 16px;
        box-sizing: border-box;
        font-family: 'Tajawal', sans-serif;
        direction: rtl;
    `;

    overlay.innerHTML = `
        <div class="edit-warn-card" style="
            max-width: 530px;
            width: 100%;
            background: linear-gradient(165deg, #111827 0%, #0b1120 100%);
            border: 2px solid rgba(245, 158, 11, 0.6);
            border-radius: 20px;
            box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.9), 0 0 35px rgba(245, 158, 11, 0.22);
            overflow: hidden;
            color: #f1f5f9;
            animation: editModalCardIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
        ">
            <!-- Header with glowing warning banner -->
            <div style="
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(239, 68, 68, 0.14));
                border-bottom: 1px solid rgba(245, 158, 11, 0.3);
                padding: 18px 22px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: relative;
            ">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="
                        width: 48px;
                        height: 48px;
                        border-radius: 14px;
                        background: linear-gradient(135deg, #f59e0b, #ef4444);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.55rem;
                        color: #ffffff;
                        animation: pulseWarnGlow 2s infinite ease-in-out;
                        flex-shrink: 0;
                    ">
                        ⚠️
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 900; color: #fbbf24; letter-spacing: -0.3px;">
                                تأكيد تعديل الفاتورة المرحلة
                            </h3>
                            <span style="
                                background: rgba(245, 158, 11, 0.2);
                                color: #fde047;
                                border: 1px solid rgba(245, 158, 11, 0.45);
                                padding: 2px 10px;
                                border-radius: 20px;
                                font-size: 0.85rem;
                                font-weight: 800;
                                font-family: 'Outfit', sans-serif;
                            ">#${invNo}</span>
                        </div>
                        <div style="color: #94a3b8; font-size: 0.82rem; margin-top: 3px; font-weight: 600;">
                            الفاتورة مرحّلة ومثبتة رسمياً في قيود الحسابات وحركات المخزن
                        </div>
                    </div>
                </div>
                <button type="button" id="edit-modal-close-btn" style="
                    background: rgba(255, 255, 255, 0.06);
                    border: 1px solid rgba(255, 255, 255, 0.12);
                    color: #94a3b8;
                    font-size: 1.2rem;
                    width: 34px;
                    height: 34px;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(239,68,68,0.2)'; this.style.color='#f87171'; this.style.borderColor='rgba(239,68,68,0.4)';" onmouseout="this.style.background='rgba(255,255,255,0.06)'; this.style.color='#94a3b8'; this.style.borderColor='rgba(255,255,255,0.12)';">✕</button>
            </div>

            <!-- Body -->
            <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 14px;">
                
                <!-- Notice Alert Box -->
                <div style="
                    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(239, 68, 68, 0.06));
                    border: 1px solid rgba(245, 158, 11, 0.35);
                    border-right: 4px solid #f59e0b;
                    border-radius: 10px;
                    padding: 12px 16px;
                    font-size: 0.92rem;
                    color: #fef3c7;
                    line-height: 1.6;
                ">
                    <div style="font-weight: 800; color: #fbbf24; margin-bottom: 2px; display: flex; align-items: center; gap: 6px;">
                        <span>🛡️</span> <span>تنبيه أمني ومحاسبي هام:</span>
                    </div>
                    أنت على وشك فتح قفل هذه الفاتورة للتعديل المباشر. عند اعتماد التعديلات وحفظها، ستحدث الإجراءات النظامية التالية:
                </div>

                <!-- 3 Colorful Feature Warning Cards -->
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Card 1: Unlock Fields -->
                    <div style="
                        display: flex;
                        align-items: flex-start;
                        gap: 12px;
                        background: rgba(245, 158, 11, 0.06);
                        border: 1px solid rgba(245, 158, 11, 0.22);
                        border-radius: 12px;
                        padding: 12px 14px;
                    ">
                        <div style="
                            width: 38px;
                            height: 38px;
                            border-radius: 10px;
                            background: rgba(245, 158, 11, 0.15);
                            border: 1px solid rgba(245, 158, 11, 0.35);
                            color: #fbbf24;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1.2rem;
                            flex-shrink: 0;
                        ">🔓</div>
                        <div>
                            <div style="font-size: 0.93rem; font-weight: 800; color: #fde68a; margin-bottom: 2px;">
                                فك قفل الحقول للتعديل الفوري
                            </div>
                            <div style="font-size: 0.83rem; color: #cbd5e1; line-height: 1.45;">
                                سيتم إتاحة تعديل بنود الأصناف، الكميات، الأسعار، الخصومات، وطرق الدفع بحرية تامة.
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Recompute Entries & Stock -->
                    <div style="
                        display: flex;
                        align-items: flex-start;
                        gap: 12px;
                        background: rgba(59, 130, 246, 0.06);
                        border: 1px solid rgba(59, 130, 246, 0.22);
                        border-radius: 12px;
                        padding: 12px 14px;
                    ">
                        <div style="
                            width: 38px;
                            height: 38px;
                            border-radius: 10px;
                            background: rgba(59, 130, 246, 0.15);
                            border: 1px solid rgba(59, 130, 246, 0.35);
                            color: #60a5fa;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1.2rem;
                            flex-shrink: 0;
                        ">📊</div>
                        <div>
                            <div style="font-size: 0.93rem; font-weight: 800; color: #93c5fd; margin-bottom: 2px;">
                                إعادة احتساب القيود وحركات المخزن
                            </div>
                            <div style="font-size: 0.83rem; color: #cbd5e1; line-height: 1.45;">
                                عند الحفظ، يُلغى الأثر المالي والمخزني السابق ويتم تحديث قيود الحسابات والأستاذ والمخزن بالقيم الجديدة.
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: WhatsApp Notification & Amendment Badge -->
                    <div style="
                        display: flex;
                        align-items: flex-start;
                        gap: 12px;
                        background: rgba(16, 185, 129, 0.06);
                        border: 1px solid rgba(16, 185, 129, 0.22);
                        border-radius: 12px;
                        padding: 12px 14px;
                    ">
                        <div style="
                            width: 38px;
                            height: 38px;
                            border-radius: 10px;
                            background: rgba(16, 185, 129, 0.15);
                            border: 1px solid rgba(16, 185, 129, 0.35);
                            color: #34d399;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1.2rem;
                            flex-shrink: 0;
                        ">📲</div>
                        <div>
                            <div style="font-size: 0.93rem; font-weight: 800; color: #6ee7b7; margin-bottom: 2px;">
                                توثيق الفاتورة كـ «نسخة معدّلة» وإشعار العميل
                            </div>
                            <div style="font-size: 0.83rem; color: #cbd5e1; line-height: 1.45;">
                                ستُعلّم الفاتورة رسمياً كـ «نسخة معدّلة» وسيتم إرسال إشعار رسمي فوري للعميل بالتعديل عبر واتساب.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div style="
                background: rgba(15, 23, 42, 0.7);
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                padding: 16px 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
            ">
                <button type="button" id="edit-modal-cancel-btn" style="
                    flex: 1;
                    min-width: 140px;
                    padding: 12px 18px;
                    border-radius: 10px;
                    font-size: 0.9rem;
                    font-weight: 700;
                    cursor: pointer;
                    background: rgba(255, 255, 255, 0.06);
                    color: #cbd5e1;
                    border: 1px solid rgba(255, 255, 255, 0.15);
                    font-family: 'Tajawal', sans-serif;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 6px;
                " onmouseover="this.style.background='rgba(255,255,255,0.12)'; this.style.color='#fff';" onmouseout="this.style.background='rgba(255,255,255,0.06)'; this.style.color='#cbd5e1';">
                    <span>🔒</span> إلغاء والاحتفاظ بالقفل
                </button>

                <button type="button" id="edit-modal-confirm-btn" style="
                    flex: 1.4;
                    min-width: 190px;
                    padding: 12px 22px;
                    border-radius: 10px;
                    font-size: 0.95rem;
                    font-weight: 900;
                    cursor: pointer;
                    background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
                    color: #ffffff;
                    border: none;
                    box-shadow: 0 4px 18px rgba(245, 158, 11, 0.45);
                    font-family: 'Tajawal', sans-serif;
                    transition: all 0.25s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                " onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 24px rgba(245,158,11,0.65)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 18px rgba(245,158,11,0.45)';">
                    <span>✏️</span> تأكيد فك القفل وبدء التعديل
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Fade in
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
    });

    const closeModal = () => {
        overlay.style.opacity = '0';
        const card = overlay.querySelector('.edit-warn-card');
        if (card) {
            card.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
            card.style.transform = 'scale(0.92) translateY(15px)';
            card.style.opacity = '0';
        }
        document.removeEventListener('keydown', onKeyDown);
        setTimeout(() => {
            if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 250);
    };

    const onKeyDown = (e) => {
        if (e.key === 'Escape') closeModal();
    };
    document.addEventListener('keydown', onKeyDown);

    overlay.querySelector('#edit-modal-close-btn').onclick = closeModal;
    overlay.querySelector('#edit-modal-cancel-btn').onclick = closeModal;

    // Click outside card to close
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    overlay.querySelector('#edit-modal-confirm-btn').onclick = () => {
        closeModal();

        window.isInvoiceEditingPermitted = true;
        window.isAmendingInvoice = true;
        setInvoiceLockState(false, true, invNo);
        if (typeof showToast === 'function') {
            showToast('✏️ تم فك القفل — أنت الآن في وضع التعديل (سيتم إرسال إشعار التعديل عند الحفظ)', 'warning', 5000);
        }
    };
}
window.requestEditPostedInvoice = requestEditPostedInvoice;

function cancelEditPostedInvoice(invNo) {
    if (!invNo) invNo = document.getElementById('hdr-inv-no')?.value || currentViewedInvoiceNo;
    window.isInvoiceEditingPermitted = false;
    window.isAmendingInvoice = false;
    if (invNo && loadInvoiceFromStorage(invNo, { isLocked: true, isPosted: true })) {
        if (typeof showToast === 'function') {
            showToast('🔒 تم إلغاء التعديل وإعادة قفل الفاتورة', 'info');
        }
    } else {
        onSuccessNewInvoice(false);
    }
}
window.cancelEditPostedInvoice = cancelEditPostedInvoice;

function loadInvoiceFromStorage(invNo, opts = {}) {
    let str = localStorage.getItem('erp_inv_' + invNo);
    if (!str) {
        str = localStorage.getItem('erp_draft_' + invNo);
    }
    let data;
    if (str) {
        try { data = JSON.parse(str); } catch(e) {}
    }

    const sortedAll = getSortedSalesInvoices();
    const listMatch = sortedAll.find(inv => String(inv.invoiceNo || inv.id) === String(invNo));
    if (data && listMatch) {
        data = Object.assign({}, listMatch, data);
        if ((!data.items || !data.items.length) && Array.isArray(listMatch.items) && listMatch.items.length) {
            data.items = listMatch.items;
        }
    } else if (!data && listMatch) {
        data = listMatch;
    }

    if (!data && window.MagaiesOffline && typeof MagaiesOffline.getQueue === 'function') {
        try {
            const qItem = MagaiesOffline.getQueue().find(q => (q.type === 'sales' || q.type === 'sales_split') && (String(q.offline_id || q.tempId) === String(invNo) || String(q.payload?.invoiceNo || q.payload?.id) === String(invNo)));
            if (qItem && qItem.payload) {
                const qNo = qItem.offline_id || qItem.tempId || invNo;
                data = Object.assign({ invoiceNo: qNo, id: qNo }, qItem.payload);
            }
        } catch(e) {}
    }

    if (!data) return false;

    try {
        const docNo = data.invoiceNo || data.id || invNo;
        const isPosted = (data.status === 'posted' || data.status === 'completed' || data.effectsApplied || opts.isPosted);
        const isLocked = (isPosted || opts.isLocked === true);

        currentViewedInvoiceNo = docNo;

        if (data.saleType) {
            currentSaleType = data.saleType;
            if (typeof openInvoiceEditor === 'function') openInvoiceEditor(currentSaleType, true);
        }

        // Reset form
        rows = [];
        rowIdCounter = 0;

        // Set Header
        const invNoEl = document.getElementById('hdr-inv-no');
        if (invNoEl) invNoEl.value = docNo;
        const invNumTxt = document.getElementById('inv-number');
        if (invNumTxt) invNumTxt.textContent = docNo;

        let amendedBadge = document.getElementById('hdr-amended-badge');
        if (data.isAmended) {
            if (!amendedBadge) {
                amendedBadge = document.createElement('span');
                amendedBadge.id = 'hdr-amended-badge';
                amendedBadge.style.cssText = 'background: rgba(245, 158, 11, 0.25); border: 1px solid rgba(245, 158, 11, 0.6); color: #fbbf24; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800; margin: 0 4px;';
                const invWrap = document.querySelector('.gold-serial-wrap');
                if (invWrap && invNumTxt) invWrap.insertBefore(amendedBadge, invNumTxt.nextSibling);
            }
            amendedBadge.textContent = 'مُعَدَّلَة';
            amendedBadge.style.display = 'inline-block';
        } else if (amendedBadge) {
            amendedBadge.style.display = 'none';
        }

        // 1. التاريخ والبيان والملحوظات (تحديث كامل دائماً حتى لا تبقى بيانات فاتورة سابقة)
        const rawDate = String(data.date || data.savedAt || data.createdAt || '').split('T')[0].split(' ')[0];
        if (document.getElementById('hdr-date') && rawDate) {
            document.getElementById('hdr-date').value = rawDate;
        }
        const descVal = String(data.description ?? data.notes ?? data.remark ?? '');
        const notesVal = String(data.notes ?? data.description ?? data.remark ?? '');
        if (document.getElementById('hdr-description')) document.getElementById('hdr-description').value = descVal;
        if (document.getElementById('inv-notes')) document.getElementById('inv-notes').value = notesVal;

        // 2. المستودع ومركز التكلفة (يدعم المعرف أو الاسم العربي)
        const itemsList = data.items || data.products || [];
        const firstIt = itemsList.length > 0 ? itemsList[0] : null;
        const rawWh = data.warehouse || data.warehouseId || firstIt?.warehouseId || firstIt?.warehouse || '';
        const whEl = document.getElementById('hdr-warehouse');
        if (whEl && rawWh) {
            const matchedWh = (typeof WAREHOUSES !== 'undefined' ? WAREHOUSES : []).find(w => String(w.id) === String(rawWh) || w.name === rawWh);
            const whVal = matchedWh ? matchedWh.id : rawWh;
            if (!Array.from(whEl.options || []).some(o => o.value === whVal)) {
                const opt = document.createElement('option');
                opt.value = whVal;
                opt.textContent = matchedWh ? matchedWh.name : rawWh;
                whEl.appendChild(opt);
            }
            whEl.value = whVal;
        }

        const rawCc = data.costCenter || firstIt?.costCenterId || firstIt?.costCenter || '';
        const ccEl = document.getElementById('hdr-cost-center');
        if (ccEl && rawCc) {
            const matchedCc = (typeof COST_CENTERS !== 'undefined' ? COST_CENTERS : []).find(c => String(c.id) === String(rawCc) || c.name === rawCc);
            const ccVal = matchedCc ? matchedCc.id : rawCc;
            if (!Array.from(ccEl.options || []).some(o => o.value === ccVal)) {
                const opt = document.createElement('option');
                opt.value = ccVal;
                opt.textContent = matchedCc ? matchedCc.name : rawCc;
                ccEl.appendChild(opt);
            }
            ccEl.value = ccVal;
        }

        // 3. العملة وسعر الصرف
        if (data.currency && document.getElementById('hdr-currency')) {
            document.getElementById('hdr-currency').value = data.currency;
        }
        const exRateLoaded = data.exchangeRate ?? data.rate;
        if (exRateLoaded !== undefined && document.getElementById('hdr-exchange-rate')) {
            document.getElementById('hdr-exchange-rate').value = exRateLoaded;
            document.getElementById('hdr-exchange-rate').disabled = (data.currency === 'SDG');
        }

        // 4. العميل (يدعم البحث بالـ ID أو الاسم العربي المباشر)
        const custIdOrName = data.customer || data.customerId || data.customerName || '';
        const c = (typeof CUSTOMERS !== 'undefined' ? CUSTOMERS : []).find(x =>
            String(x.id) === String(data.customer || data.customerId || '') ||
            (data.customerName && x.name === data.customerName) ||
            (custIdOrName && x.name === custIdOrName)
        );
        if (c) {
            selectCustomer(c.id, c.name);
        } else {
            const fallbackCustName = data.customerName || (typeof data.customer === 'string' && !data.customer.startsWith('C-') ? data.customer : '') || 'عميل نقدي عام';
            if (document.getElementById('hdr-customer')) document.getElementById('hdr-customer').value = data.customer || data.customerId || 'walk-in';
            if (document.getElementById('hdr-customer-search')) document.getElementById('hdr-customer-search').value = fallbackCustName;
            updateLedgerButton();
            onCustomerChange();
        }

        // 5. الأصناف وأسطر الفاتورة (الكمية، السعر، الوحدة، المخزن، الملحوظة)
        let sumItemDiscount = 0;
        let sumItemTax = 0;
        let sumItemLabor = 0;

        if (itemsList && itemsList.length) {
            itemsList.forEach(i => {
                addRow(true, true); // silent and skip render
                const r = rows[rows.length - 1];

                let resolvedItemId = i.itemId || i.id || i.item_id || '';
                const rawName = (i.itemName || i.name || '').trim();
                const unitStr = (i.selectedUnit || i.unit || i.unitName || '').trim();
                let matchedItem = (typeof ITEMS !== 'undefined' ? ITEMS : []).find(x =>
                    (resolvedItemId && String(x.id) === String(resolvedItemId)) ||
                    (rawName && x.name && x.name.trim() === rawName)
                );
                if (!matchedItem && rawName && typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) {
                    const synId = resolvedItemId || ('it_sync_' + rawName);
                    const defU = unitStr || 'قطعة';
                    matchedItem = {
                        id: synId,
                        name: rawName,
                        unit: defU,
                        baseUnit: defU,
                        conversionFactor: 1,
                        bundleFactor: 1,
                        price: parseFloat(i.price ?? i.rate ?? 0) || 0,
                        sellingPrice: parseFloat(i.price ?? i.rate ?? 0) || 0,
                        unitsArray: [{ name: defU, unitName: defU, factor: parseFloat(i.customFactor ?? i.factor ?? 1) || 1, price: parseFloat(i.price ?? i.rate ?? 0) || 0 }]
                    };
                    ITEMS.push(matchedItem);
                }
                if (matchedItem) {
                    resolvedItemId = matchedItem.id;
                    if (unitStr) {
                        const uArr = typeof extractItemUnits === 'function' ? extractItemUnits(matchedItem) : (matchedItem.unitsArray || []);
                        if (!uArr.some(u => u.name === unitStr || u.unitName === unitStr)) {
                            uArr.push({ name: unitStr, unitName: unitStr, factor: parseFloat(i.customFactor ?? i.factor ?? 1) || 1, price: parseFloat(i.price ?? i.rate ?? 0) || 0 });
                        }
                        matchedItem.unitsArray = uArr;
                    }
                }

                r.itemId = resolvedItemId || rawName || 'item-custom';
                r.itemName = rawName || (matchedItem ? matchedItem.name : '');
                r.name = r.itemName;
                r.selectedUnit = unitStr || (matchedItem ? matchedItem.unit : 'حبة');
                r.unit = r.selectedUnit;
                let resolvedRowFactor = parseFloat(i.customFactor ?? i.factor ?? i.unitFactor ?? 0);
                if ((!resolvedRowFactor || resolvedRowFactor <= 1) && matchedItem && Array.isArray(matchedItem.unitsArray)) {
                    const matchedU = matchedItem.unitsArray.find(u => String(u.name || u.unitName || '').trim() === String(r.selectedUnit || '').trim());
                    if (matchedU && parseFloat(matchedU.factor) > 1) {
                        resolvedRowFactor = parseFloat(matchedU.factor);
                    }
                }
                r.customFactor = resolvedRowFactor > 0 ? resolvedRowFactor : 1;
                r.quantity = parseFloat(i.quantity !== undefined ? i.quantity : (i.qty !== undefined ? i.qty : 1)) || 0;
                r.qty = r.quantity;
                if (i.customBaseQty !== undefined) r.customBaseQty = i.customBaseQty;
                r.price = parseFloat(i.price !== undefined ? i.price : (i.rate !== undefined ? i.rate : 0)) || 0;
                if (i.fcPrice !== undefined) r.fcPrice = i.fcPrice;

                const rowWhRaw = i.warehouseId || i.warehouse || data.warehouse || data.warehouseId || '';
                const rowWhMatch = (typeof WAREHOUSES !== 'undefined' ? WAREHOUSES : []).find(w => String(w.id) === String(rowWhRaw) || w.name === rowWhRaw);
                r.warehouseId = rowWhMatch ? rowWhMatch.id : rowWhRaw;
                r.warehouse = rowWhMatch ? rowWhMatch.name : rowWhRaw;

                const rowCcRaw = i.costCenterId || i.costCenter || data.costCenter || '';
                const rowCcMatch = (typeof COST_CENTERS !== 'undefined' ? COST_CENTERS : []).find(cc => String(cc.id) === String(rowCcRaw) || cc.name === rowCcRaw);
                r.costCenterId = rowCcMatch ? rowCcMatch.id : rowCcRaw;

                if (i.qrCode) r.qrCode = i.qrCode;
                r.note = i.note ?? i.notes ?? notesVal ?? '';
                r.notes = r.note;
                if (i.buyPrice !== undefined) r.buyPrice = i.buyPrice;
                if (i.sellPrice !== undefined) r.sellPrice = i.sellPrice;

                sumItemDiscount += parseFloat(i.discount || 0) || 0;
                sumItemTax += parseFloat(i.tax || 0) || 0;
                sumItemLabor += parseFloat(i.labor || 0) || 0;
            });
        }

        // 6. المصروفات والتسويات (الخصم، الضريبة، الإضافات، العتالة، الترحيل)
        const laborVal = data.laborCost ?? data.labor ?? sumItemLabor ?? 0;
        const transportVal = data.transportCost ?? data.transport ?? 0;
        const discountVal = data.discountAmt ?? data.discountCost ?? data.discount ?? sumItemDiscount ?? 0;
        const taxVal = data.taxAmt ?? data.taxCost ?? data.vatAmount ?? data.tax ?? sumItemTax ?? 0;
        const additionVal = data.additionAmt ?? data.additionsCost ?? 0;

        if (document.getElementById('t-labor')) document.getElementById('t-labor').value = laborVal;
        if (document.getElementById('t-transport')) document.getElementById('t-transport').value = transportVal;
        if (document.getElementById('discount-amt')) document.getElementById('discount-amt').value = discountVal;
        if (document.getElementById('tax-amt')) document.getElementById('tax-amt').value = taxVal;
        if (document.getElementById('addition-amt')) document.getElementById('addition-amt').value = additionVal;

        renderGrid();
        recalcAll();
        loadPaymentDataIntoUI(data);
        if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();

        // Apply strict lock state if posted or preview-only
        setInvoiceLockState(isLocked, isPosted, currentViewedInvoiceNo);

        if (window.renderDocumentUserBadge) {
            window.renderDocumentUserBadge('sales-user-audit-container', data, false);
        }

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

            item.innerHTML = `<div style="display: flex; justify-content: space-between; align-items: center; width: 100%; text-align: right; dir: rtl; padding: 6px 4px;"><div style="display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap;"><span style="font-weight: 800; font-size: 1.35rem; color: #6ee7b7; text-shadow: 0 0 6px rgba(110,231,183,0.2);">${nameH}</span>${phoneH ? `<span style="font-size:0.95rem; color:#94a3b8; font-weight:600;">(${phoneH})</span>` : ''}${emailH ? `<span style="font-size:0.85rem; color:#64748b;">[${emailH}]</span>` : ''}</div></div>`;
            item.onmousedown = () => selectCustomer(c.id, c.name); 
            item.onpointerdown = () => selectCustomer(c.id, c.name);
            listEl.appendChild(item);
        });
        if (typeof GridCustomizer !== 'undefined') {
            GridCustomizer.attachEvents();
            GridCustomizer.applyStyles();
        }
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
        }
        if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
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
    if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
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
    if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
}

function updateLedgerButton() {
    const custVal = document.getElementById('hdr-customer') ? document.getElementById('hdr-customer').value : '';
    const btn = document.getElementById('hdr-customer-ledger-btn');
    if (btn) {
        btn.style.display = 'inline-flex';
        btn.style.opacity = (custVal && custVal !== 'walk-in') ? '1' : '0.88';
    }
}

window.openCustomerLedgerFromHeader = function() {
    const custVal = document.getElementById('hdr-customer')?.value || '';
    const custName = document.getElementById('hdr-customer-search')?.value.trim() || '';
    if (custVal && custVal !== 'walk-in') {
        if (typeof openGeneralLedgerForAccount === 'function') {
            openGeneralLedgerForAccount('cust-' + custVal);
        } else {
            window.open('general-ledger.php?accountId=' + encodeURIComponent('cust-' + custVal), '_blank');
        }
    } else if (custName) {
        window.open('general-ledger.php?name=' + encodeURIComponent(custName), '_blank');
    } else {
        window.open('general-ledger.php', '_blank');
    }
};

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
        if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
        return;
    }

    const totalDebt = (parseFloat(cust.ledger) || 0) + (parseFloat(cust.checks) || 0);
    const remaining = (parseFloat(cust.creditLimit) || 0) - totalDebt;

    if (document.getElementById('fin-ledger')) document.getElementById('fin-ledger').textContent = fmtNum(cust.ledger) + ' ' + cur;
    if (document.getElementById('fin-checks')) document.getElementById('fin-checks').textContent = fmtNum(cust.checks) + ' ' + cur;
    if (document.getElementById('fin-total-debt')) document.getElementById('fin-total-debt').textContent = fmtNum(totalDebt) + ' ' + cur;
    if (document.getElementById('fin-credit-limit')) document.getElementById('fin-credit-limit').textContent = fmtNum(cust.creditLimit) + ' ' + cur;
    if (document.getElementById('fin-remaining')) document.getElementById('fin-remaining').textContent = fmtNum(remaining) + ' ' + cur;

    if (strip) {
        strip.classList.remove('visible');
        requestAnimationFrame(() => requestAnimationFrame(() => strip.classList.add('visible')));
    }

    if (badge) {
        const txtEl = document.getElementById('fin-status-txt') || document.getElementById('fin-status-msg');
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
    if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
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
    const curVal = document.getElementById('hdr-currency')?.value || 'SDG';
    const exRateVal = parseNum(document.getElementById('hdr-exchange-rate')?.value) || 1;
    const computedFcPrice = row.fcPrice !== undefined ? parseFloat(row.fcPrice) || 0 : (curVal === 'SDG' ? (parseFloat(row.price) || 0) / exRateVal : (parseFloat(row.price) || 0) * exRateVal);
    if (mFcPriceEl && document.activeElement !== mFcPriceEl) mFcPriceEl.value = fmtNum(computedFcPrice, 'col_fcprice');
    if (mTotalFcEl) mTotalFcEl.textContent = fmtNum(computedFcPrice * (row.quantity || 0), 'col_fctotal');

    const mShilaEl = document.querySelector(`[data-m-shila="${id}"]`);
    const mShilaRemEl = document.querySelector(`[data-m-shila-rem="${id}"]`);
    if (mShilaEl) mShilaEl.textContent = fmtNum(calcs.shilaQty, 'col_shila');
    if (mShilaRemEl) mShilaRemEl.textContent = fmtNum(calcs.shilaRem, 'col_shila_rem');

    if (typeof window.autoFitGridCells === 'function') {
        const trEl = totalEl ? totalEl.closest('tr') : null;
        window.autoFitGridCells(trEl || undefined);
    }
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
    if (typeof window.autoFitGridCells === 'function') {
        requestAnimationFrame(() => window.autoFitGridCells());
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
        if (window.MagaieesPermissions && typeof window.MagaieesPermissions.filterEntitiesByClearance === 'function') {
            safes = window.MagaieesPermissions.filterEntitiesByClearance(safes);
            banks = window.MagaieesPermissions.filterEntitiesByClearance(banks);
        }
        
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

    // Save current values on inputs
    if (cashInput) cashInput.dataset.lastValidValue = cashInput.value;
    document.querySelectorAll('.bt-amount-input').forEach(input => {
        input.dataset.lastValidValue = input.value;
    });
    document.querySelectorAll('.ch-amount-input').forEach(input => {
        input.dataset.lastValidValue = input.value;
    });

    const enteredPayments = cash + transfersSum + chequesSum;
    const isOverpaid = (enteredPayments > grand + 0.01);
    const surplusAmt = isOverpaid ? (enteredPayments - grand) : 0;
    
    // Update displays
    const totInd = document.getElementById('payment-total-indicator');
    const paidInd = document.getElementById('payment-paid-indicator');
    const remInd = document.getElementById('payment-remaining-indicator');
    
    if (totInd) totInd.textContent = fmtNum(grand) + ' ' + cur;
    if (paidInd) paidInd.textContent = fmtNum(enteredPayments) + ' ' + cur;
    const remCard = document.getElementById('payment-remaining-card');
    if (remInd) {
        if (isOverpaid) {
            remInd.textContent = '+ ' + fmtNum(surplusAmt) + ' ' + cur;
            remInd.style.color = '#34d399'; // Emerald green
            remInd.style.textShadow = '0 0 12px rgba(52,211,153,0.5)';
            if (remCard) {
                remCard.style.background = 'rgba(16, 185, 129, 0.12)';
                remCard.style.borderColor = 'rgba(16, 185, 129, 0.45)';
                const remLabel = remCard.querySelector('.indicator-label, span');
                if (remLabel && !remLabel.dataset.origText) remLabel.dataset.origText = remLabel.textContent;
                if (remLabel) remLabel.textContent = '🌟 فائض سداد (رصيد دائن)';
            }
        } else if (Math.abs(remaining) < 0.01) {
            remInd.textContent = fmtNum(remaining) + ' ' + cur;
            remInd.style.color = '#34d399'; // Green
            remInd.style.textShadow = '0 0 12px rgba(52,211,153,0.5)';
            if (remCard) {
                remCard.style.background = 'rgba(16, 185, 129, 0.1)';
                remCard.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                const remLabel = remCard.querySelector('.indicator-label, span');
                if (remLabel && remLabel.dataset.origText) remLabel.textContent = remLabel.dataset.origText;
            }
        } else if (remaining > 0) {
            remInd.textContent = fmtNum(remaining) + ' ' + cur;
            remInd.style.color = '#f87171'; // Red
            remInd.style.textShadow = '0 0 12px rgba(248,113,113,0.4)';
            if (remCard) {
                remCard.style.background = 'rgba(239, 68, 68, 0.08)';
                remCard.style.borderColor = 'rgba(239, 68, 68, 0.35)';
                const remLabel = remCard.querySelector('.indicator-label, span');
                if (remLabel && remLabel.dataset.origText) remLabel.textContent = remLabel.dataset.origText;
            }
        } else {
            remInd.textContent = fmtNum(remaining) + ' ' + cur;
            remInd.style.color = '#fbbf24'; // Amber
            remInd.style.textShadow = '0 0 12px rgba(251,191,36,0.4)';
            if (remCard) {
                remCard.style.background = 'rgba(245, 158, 11, 0.08)';
                remCard.style.borderColor = 'rgba(245, 158, 11, 0.35)';
                const remLabel = remCard.querySelector('.indicator-label, span');
                if (remLabel && remLabel.dataset.origText) remLabel.textContent = remLabel.dataset.origText;
            }
        }
    }

    // Update progress bars & status badge dynamically
    const totalPayments = Math.max(grand, enteredPayments + credit);
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
            if (isOverpaid) {
                statusBadge.dataset.status = 'overpaid';
                statusBadge.textContent = '🌟 فائض سداد (رصيد دائن)';
                statusBadge.style.background = 'rgba(16, 185, 129, 0.2)';
                statusBadge.style.color = '#34d399';
                statusBadge.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.3)';
            } else if (credit <= 0.01) {
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
        if (window.isInvoiceLocked && !window.isInvoiceEditingPermitted) {
            showToast('🔒 الفاتورة مرحّلة ومقفلة — يرجى تأكيد فك القفل للتعديل', 'warning', 5000);
            requestEditPostedInvoice(currentViewedInvoiceNo);
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

            if (!window._dsConfirmedEditSave) {
                if (typeof window.openDsConfirmModal === 'function') {
                    window.isSubmitting = false;
                    window.openDsConfirmModal(
                        '⚠️ تأكيد حفظ تعديلات الفاتورة',
                        `الفاتورة رقم (${currentViewedInvoiceNo}) محفوظة في النظام. هل تريد حفظ التعديلات وتحديث القيود والأرصدة الآن؟`,
                        () => {
                            window._dsConfirmedEditSave = true;
                            submitInvoice(ignoreWarnings);
                            setTimeout(() => { window._dsConfirmedEditSave = false; }, 400);
                        }
                    );
                    return;
                }
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

    // 🛡️ Enterprise Financial Approval Limits Enforcement (Discount & Invoice Amount)
    if (window.MagaieesPermissions && typeof window.MagaieesPermissions.checkFinancialLimits === 'function' && !window.managerOverrideBypass) {
        const invTotalVal = typeof getInvoiceTotal === 'function' ? getInvoiceTotal() : grandAmount;
        const discountAmt = parseNum(document.getElementById('discount-amt')?.value || 0);
        const discountPct = invTotalVal > 0 ? ((discountAmt / invTotalVal) * 100) : 0;

        // 1. Discount limit check
        const discCheck = window.MagaieesPermissions.checkFinancialLimits('discount_percent', discountPct);
        if (!discCheck.allowed) {
            window.isSubmitting = false;
            window.MagaieesPermissions.requestManagerApproval({
                actionType: 'discount_exceeded',
                details: `طلب تطبيق خصم بنسبة (${discountPct.toFixed(1)}%) وهو أعلى من حد الصلاحية المسموح (${discCheck.maxAllowed}%).`,
                onApproved: (mgr) => {
                    window.managerOverrideBypass = true;
                    submitInvoice(ignoreWarnings);
                    setTimeout(() => { window.managerOverrideBypass = false; }, 2000);
                },
                onRejected: () => {
                    if (typeof showToast === 'function') showToast('❌ تم رفض طلب تجاوز حد الخصم من قبل المشرف', 'warning');
                }
            });
            return;
        }

        // 2. Invoice amount limit check
        const invCheck = window.MagaieesPermissions.checkFinancialLimits('invoice_amount', grandAmount);
        if (!invCheck.allowed) {
            window.isSubmitting = false;
            window.MagaieesPermissions.requestManagerApproval({
                actionType: 'invoice_amount_exceeded',
                details: `إصدار فاتورة مبيعات بقيمة (${grandAmount.toLocaleString('en-US')} ${cur}) وهو أعلى من السقف المالي للمستخدم (${invCheck.maxAllowed.toLocaleString('en-US')} ${cur}).`,
                onApproved: (mgr) => {
                    window.managerOverrideBypass = true;
                    submitInvoice(ignoreWarnings);
                    setTimeout(() => { window.managerOverrideBypass = false; }, 2000);
                },
                onRejected: () => {
                    if (typeof showToast === 'function') showToast('❌ تم رفض اعتماد سقف الفاتورة المالي', 'warning');
                }
            });
            return;
        }
    }

    // --- 1. Multi-Payment Read & Validation ---
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const creditActive = document.getElementById('toggle-credit').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');

    let cashAmt = 0;
    let creditAmt = 0;
    let overpaidAmt = 0;
    let bankTransfers = [];
    let transfersSum = 0;
    let cheques = [];
    let chequesSum = 0;
    let paymentMethod = 'cash';

    if (!cashActive && !creditActive && !bankActive && !chequeActive) {
        // DEFAULT RULE: If no payment method is toggled, default to Credit (Deferred) for full amount
        creditAmt = grandAmount;
        overpaidAmt = 0;
        paymentMethod = 'credit';
    } else {
        if (cashActive) {
            cashAmt = parseNum(document.getElementById('pay-cash-amount')?.value || 0);
        }
        
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
            overpaidAmt = 0;
        } else {
            creditAmt = 0;
            overpaidAmt = Math.max(0, totalPaymentsPaid - grandAmount);
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
        // Overpayment check ("رسالة ظريفة")
        const totalPaymentsPaid = cashAmt + transfersSum + chequesSum;
        if (totalPaymentsPaid > grandAmount + 0.01) {
            window.isSubmitting = false;
            const overpaid = totalPaymentsPaid - grandAmount;
            showOverpaymentConfirmModal({
                type: 'sales',
                grand: grandAmount,
                paid: totalPaymentsPaid,
                overpaid: overpaid,
                cur: cur,
                partnerName: cust ? cust.name : 'العميل',
                onConfirm: () => submitInvoice(true),
                onCancel: () => {
                    window.isSubmitting = false;
                    const cashInp = document.getElementById('pay-cash-amount');
                    if (cashInp) cashInp.focus();
                }
            });
            return;
        }

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
        const salesSig = `${custId}|${grandAmount}|${rows.map(r => `${r.itemId}:${r.quantity}:${r.price}`).join(',')}|${document.getElementById('hdr-date')?.value || ''}`;
        if (window._lastSubmittedSalesSig === salesSig && (Date.now() - (window._lastSubmittedSalesTime || 0)) < 15000) {
            showToast(`✅ تم حفظ هذه الفاتورة مسبقاً برقم (${window._lastSubmittedSalesNo || ''}) — لن يتم تكرار الحفظ`, 'info', 3500);
            window.isSubmitting = false;
            return;
        }
        committedNo = commitInvoiceNumber(currentSaleType); // increment
        window._lastSubmittedSalesSig = salesSig;
        window._lastSubmittedSalesTime = Date.now();
        window._lastSubmittedSalesNo = committedNo;
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

    let oldInv = null;
    const wasAmending = !!(currentViewedInvoiceNo && (window.isInvoiceEditingPermitted || window.isAmendingInvoice));
    if (currentViewedInvoiceNo) {
        try {
            const oldStr = localStorage.getItem('erp_inv_' + currentViewedInvoiceNo);
            if (oldStr) oldInv = JSON.parse(oldStr);
        } catch(e) {}
    }

    const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };

    const data = {
        invoiceNo: committedNo,
        idempotencyKey: window._lastSubmittedSalesSig ? ('LAP-S-' + btoa(unescape(encodeURIComponent(window._lastSubmittedSalesSig))).substring(0, 32)) : ('LAP-S-' + committedNo),
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
        warehouseId: document.getElementById('hdr-warehouse').value,
        warehouse: ((typeof WAREHOUSES !== 'undefined' && Array.isArray(WAREHOUSES)) ? (WAREHOUSES.find(w => String(w.id) === String(document.getElementById('hdr-warehouse').value))?.name || document.getElementById('hdr-warehouse').value) : document.getElementById('hdr-warehouse').value),
        description: document.getElementById('hdr-description').value,
        items: rows.map(r => {
            const itObj = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS.find(i => String(i.id) === String(r.itemId)) : null;
            const rName = r.itemName || r.name || (itObj ? itObj.name : String(r.itemId || ''));
            const rUnit = r.selectedUnit || r.unit || (itObj ? (itObj.unit || itObj.baseUnit || 'قطعة') : 'قطعة');
            const rQty = parseFloat(r.quantity ?? r.qty ?? 0) || 0;
            const rPrice = parseFloat(r.price ?? 0) || 0;
            const rGross = rQty * rPrice;
            const rWhRaw = r.warehouseId || r.warehouse || document.getElementById('hdr-warehouse').value;
            const rWhObj = (typeof WAREHOUSES !== 'undefined' && Array.isArray(WAREHOUSES)) ? WAREHOUSES.find(w => String(w.id) === String(rWhRaw) || w.name === rWhRaw) : null;
            return {
                ...r,
                name: rName,
                itemName: rName,
                unit: rUnit,
                selectedUnit: rUnit,
                unitName: rUnit,
                qty: rQty,
                quantity: rQty,
                price: rPrice,
                subtotal: rGross,
                grossTotal: rGross,
                total: rGross,
                warehouseId: rWhObj ? rWhObj.id : rWhRaw,
                warehouse: rWhObj ? rWhObj.name : rWhRaw,
                note: r.note || r.notes || document.getElementById('inv-notes').value || '',
                notes: r.note || r.notes || document.getElementById('inv-notes').value || ''
            };
        }),
        notes: document.getElementById('inv-notes').value,
        subTotal: parseNum(document.getElementById('t-subtotal')?.textContent || document.getElementById('t-invoice-total')?.textContent || 0),
        subtotal: parseNum(document.getElementById('t-subtotal')?.textContent || document.getElementById('t-invoice-total')?.textContent || 0),
        discount: parseNum(document.getElementById('t-discount')?.value || document.getElementById('t-discount')?.textContent || 0),
        vatAmount: parseNum(document.getElementById('t-vat')?.textContent || document.getElementById('t-tax')?.textContent || 0),
        grandTotal: grandAmount,
        total: grandAmount,
        netTotal: grandAmount,
        totalAmount: grandAmount,
        status: finalStatus,
        timestamp: new Date().toISOString(),
        customerName: cust ? cust.name : (document.getElementById('hdr-customer-search')?.value || 'عميل نقدي'),
        effectsApplied: (currentSaleType !== 'preliminary' && postingMode === 'auto'),
        isAmended: wasAmending ? true : !!(oldInv?.isAmended),
        amendmentCount: wasAmending ? ((Number(oldInv?.amendmentCount) || 0) + 1) : (oldInv?.amendmentCount || 0),
        lastAmendedAt: wasAmending ? new Date().toISOString() : (oldInv?.lastAmendedAt || null),
        wasEdited: wasAmending ? true : !!(oldInv?.wasEdited),
        createdBy: oldInv?.createdBy || curUser.id,
        createdByName: oldInv?.createdByName || curUser.fullName || curUser.name,
        createdByUsername: oldInv?.createdByUsername || curUser.username,
        updatedBy: currentViewedInvoiceNo ? curUser.id : (oldInv?.updatedBy || null),
        updatedByName: currentViewedInvoiceNo ? (curUser.fullName || curUser.name) : (oldInv?.updatedByName || null),
        updatedAt: currentViewedInvoiceNo ? new Date().toISOString() : (oldInv?.updatedAt || null)
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
        
        // C. Customer Credit Debt / Surplus Portion
        if (creditAmt > 0 && custId && custId !== 'walk-in') {
            let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const cIdx = customers.findIndex(c => c.id === custId);
            if (cIdx > -1) {
                let currentBalance = parseFloat(customers[cIdx].currentBalance);
                if (isNaN(currentBalance)) currentBalance = 0;
                customers[cIdx].currentBalance = currentBalance + creditAmt;
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            }
        } else if (overpaidAmt > 0 && custId && custId !== 'walk-in') {
            let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const cIdx = customers.findIndex(c => c.id === custId);
            if (cIdx > -1) {
                let currentBalance = parseFloat(customers[cIdx].currentBalance);
                if (isNaN(currentBalance)) currentBalance = 0;
                customers[cIdx].currentBalance = currentBalance - overpaidAmt;
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            }
        }
    }

    localStorage.setItem('erp_inv_' + committedNo, JSON.stringify(data));

    // Append or Update global sales invoices array & mobile mirror
    ['erp_sales_invoices_v1', 'magaies_local_sales_invoices_v1'].forEach(storageKey => {
        try {
            let listArr = JSON.parse(localStorage.getItem(storageKey)) || [];
            if (!Array.isArray(listArr)) listArr = [];
            const targetNo = currentViewedInvoiceNo || committedNo;
            const idx = listArr.findIndex(inv => inv && (String(inv.invoiceNo) === String(targetNo) || String(inv.id) === String(targetNo)));
            if (idx !== -1) listArr[idx] = data;
            else listArr.push(data);
            localStorage.setItem(storageKey, JSON.stringify(listArr));
        } catch (e) {}
    });

    if (window.logSystemAction) {
        const actionType = currentViewedInvoiceNo ? 'تعديل فاتورة مبيعات' : (currentSaleType === 'preliminary' ? 'فاتورة مبيعات مبدئية' : 'إصدار فاتورة مبيعات');
        const actionDetails = `${actionType} رقم ${committedNo} بقيمة ${grandTotal} ${cur} للعميل ${cust ? cust.name : 'نقدي'}`;
        window.logSystemAction(actionType, 'المبيعات', committedNo, actionDetails);
    }

    window.isInvoiceEditingPermitted = false;
    window.isAmendingInvoice = false;

    if (typeof window.rebuildGLFromAllTables === 'function') {
        try {
            window.rebuildGLFromAllTables(false);
        } catch(eGL) {
            console.warn("rebuildGLFromAllTables warning:", eGL);
        }
    }
    if (typeof window.pushToCloud === 'function') {
        try {
            window.pushToCloud(true).catch(e => console.warn("Cloud sync failed:", e));
        } catch(eCloud) {
            console.warn("Cloud push warning:", eCloud);
        }
    }
    // 🪞 REAL-TIME MIRROR SYNC + UNIVERSAL OFFLINE QUEUE: Commit sales invoice to MariaDB or Offline Queue
    try {
        const queueType = (currentSaleType === 'split' || data.isSplit || data.saleType === 'split') ? 'sales_split' : 'sales';
        if (!navigator.onLine) {
            if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                window.MagaiesOffline.enqueue(queueType, data, data.invoiceNo);
            }
        } else {
            fetch('api/mirror_sync.php?action=' + (queueType === 'sales_split' ? 'save_sales_split' : 'save_sales_invoice'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            }).then(r => r.json()).then(res => {
                if (res && res.success && res.nextInvoiceNo) {
                    window._mirrorNextSalesNo = res.nextInvoiceNo;
                } else if (!res || !res.success) {
                    if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                        window.MagaiesOffline.enqueue(queueType, data, data.invoiceNo);
                    }
                }
            }).catch(e => {
                console.warn("Mirror sync sales failed, enqueuing offline:", e);
                if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                    window.MagaiesOffline.enqueue(queueType, data, data.invoiceNo);
                }
            });
        }
    } catch(eMirror) {}

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
        } else if (overpaidAmt > 0 && custId !== 'walk-in') {
            showToast(`🌟 تم ترحيل فائض السداد بقيمة ${fmtNum(overpaidAmt)} ${cur} كرصيد دائن لحساب العميل`, 'success', 5000);
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
    window.currentViewedInvoiceNo = committedNo;
    document.getElementById('hdr-inv-no').value = committedNo;
    document.getElementById('inv-number').textContent = committedNo;
    window._lastSubmittedSalesInvoice = data;
    window._lastSubmittedSalesInvoiceNo = committedNo;

    // ═══ Trigger Auto-Send if enabled ═══
    if (typeof window.triggerSalesAutoSendOnSave === 'function') {
        try {
            window.triggerSalesAutoSendOnSave(committedNo, data);
        } catch(eAuto) {
            console.warn("Auto-send non-fatal error:", eAuto);
        }
    }

    // Automatically return to and stay on the saved invoice in locked posted state
    try {
        loadInvoiceFromStorage(committedNo, { isLocked: true, isPosted: true });
    } catch(eLoad) {
        console.warn("loadInvoiceFromStorage non-fatal error:", eLoad);
    }

    try {
        if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
    } catch(eAct) {}

    if (wasAmending) {
        showToast('✅ تم حفظ التعديل بنجاح وتسجيل الفاتورة كـ «نسخة معدّلة»', 'success', 5000);
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
//  OVERPAYMENT CONFIRMATION MODAL ("رسالة ظريفة")
// ════════════════════════════════════════
function showOverpaymentConfirmModal(options) {
    const {
        type = 'sales',
        grand = 0,
        paid = 0,
        overpaid = 0,
        cur = 'SDG',
        partnerName = '',
        onConfirm,
        onCancel
    } = options;

    const existing = document.getElementById('overpayment-confirm-overlay');
    if (existing) existing.remove();

    const isSales = (type === 'sales');
    const partnerLabel = isSales ? 'العميل' : 'المورد';
    const effectText = isSales 
        ? `سيتم حفظ الفاتورة واستلام كامل المبلغ المسدد (${fmtNum(paid)} ${cur}) في الخزينة/البنك، وترحيل الفائض (+${fmtNum(overpaid)} ${cur}) كرصيد دائن في كشف حساب [${partnerName || 'العميل'}].`
        : `سيتم حفظ الفاتورة وإثبات كامل المبلغ المسدد (${fmtNum(paid)} ${cur})، وترحيل الفائض (+${fmtNum(overpaid)} ${cur}) كرصيد لصالحنا في كشف حساب [${partnerName || 'المورد'}].`;

    const overlay = document.createElement('div');
    overlay.id = 'overpayment-confirm-overlay';
    overlay.className = 'modal-overlay';
    overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.78); backdrop-filter: blur(6px); z-index: 999999; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.25s ease; padding: 15px;';
    
    overlay.innerHTML = `
        <div class="modal-card" style="max-width: 520px; width: 100%; background: #0f172a; border: 2px solid rgba(16, 185, 129, 0.6); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85); overflow: hidden; font-family: 'Tajawal', sans-serif; direction: rtl; text-align: right; transform: translateY(-20px); transition: transform 0.25s ease;">
            <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(6, 182, 212, 0.15)); padding: 16px 20px; border-bottom: 1px solid rgba(16, 185, 129, 0.25); display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; color: #34d399; font-size: 1.15rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.35rem;">💰</span> تنبيه: السداد أعلى من قيمة الفاتورة
                </h3>
                <button type="button" id="overpay-btn-close" style="background: transparent; border: none; color: #94a3b8; font-size: 1.3rem; cursor: pointer; padding: 4px 8px; line-height: 1;">✕</button>
            </div>
            <div style="padding: 20px; color: #f1f5f9; font-size: 0.95rem; line-height: 1.6;">
                <p style="margin: 0 0 14px 0; color: #cbd5e1; font-size: 0.98rem;">
                    المبلغ المسدد أكبر من إجمالي الفاتورة بمقدار <strong style="color: #34d399; font-size: 1.2rem; font-weight: 900;">+${fmtNum(overpaid)} ${cur}</strong>.
                </p>
                
                <div style="background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 12px; margin-bottom: 14px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; text-align: center;">
                    <div style="padding: 8px 4px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="font-size: 0.74rem; color: #94a3b8; margin-bottom: 4px;">إجمالي الفاتورة</div>
                        <div style="font-size: 1rem; font-weight: 800; color: #f8fafc;">${fmtNum(grand)} <small style="font-size:0.68rem; color:#94a3b8;">${cur}</small></div>
                    </div>
                    <div style="padding: 8px 4px; background: rgba(59, 130, 246, 0.08); border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.25);">
                        <div style="font-size: 0.74rem; color: #93c5fd; margin-bottom: 4px;">إجمالي المسدد</div>
                        <div style="font-size: 1rem; font-weight: 800; color: #60a5fa;">${fmtNum(paid)} <small style="font-size:0.68rem; color:#93c5fd;">${cur}</small></div>
                    </div>
                    <div style="padding: 8px 4px; background: rgba(16, 185, 129, 0.12); border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.4);">
                        <div style="font-size: 0.74rem; color: #6ee7b7; margin-bottom: 4px;">فائض السداد</div>
                        <div style="font-size: 1.05rem; font-weight: 900; color: #34d399;">+${fmtNum(overpaid)} <small style="font-size:0.68rem; color:#6ee7b7;">${cur}</small></div>
                    </div>
                </div>

                <div style="background: rgba(56, 189, 248, 0.08); border-right: 4px solid #38bdf8; border-radius: 8px; padding: 12px 14px; color: #bae6fd; font-size: 0.88rem; line-height: 1.5;">
                    <span style="font-size: 1rem; margin-left: 4px;">ℹ️</span> <strong>الأثر في كشف الحساب:</strong> ${effectText}
                </div>
            </div>
            <div style="padding: 14px 20px; background: rgba(15, 23, 42, 0.7); border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">
                <button type="button" id="overpay-btn-cancel" style="padding: 8px 16px; border-radius: 8px; font-size: 0.88rem; font-weight: bold; cursor: pointer; background: rgba(255,255,255,0.06); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.12); font-family: 'Tajawal', sans-serif;">✕ مراجعة وتعديل المبلغ</button>
                <button type="button" id="overpay-btn-confirm" style="padding: 8px 22px; border-radius: 8px; font-size: 0.9rem; font-weight: 800; cursor: pointer; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); font-family: 'Tajawal', sans-serif;">✓ تمرير واعتماد الفاتورة</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    const closeFn = () => {
        overlay.style.opacity = '0';
        const card = overlay.querySelector('.modal-card');
        if (card) card.style.transform = 'translateY(-20px)';
        setTimeout(() => overlay.remove(), 250);
        if (typeof onCancel === 'function') onCancel();
    };

    overlay.querySelector('#overpay-btn-close').onclick = closeFn;
    overlay.querySelector('#overpay-btn-cancel').onclick = closeFn;

    overlay.querySelector('#overpay-btn-confirm').onclick = () => {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 250);
        if (typeof onConfirm === 'function') onConfirm();
    };

    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        const card = overlay.querySelector('.modal-card');
        if (card) card.style.transform = 'translateY(0)';
    });
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

    if (window.logSystemAction) {
        window.logSystemAction('حذف فاتورة مبيعات', 'المبيعات', currentInvNo, `تم حذف فاتورة المبيعات رقم ${currentInvNo} وإلغاء تأثيراتها المالية والمخزنية`);
    }

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
            <div style="margin-top: 10px; padding: 8px 12px; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 8px; text-align: right;">
                <strong style="color: #fbbf24; font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
                    <span>⚠️</span> تنبيهات:
                </strong>
                <ul style="color: #f1f5f9; font-size: 0.78rem; margin: 4px 0 0 0; padding-right: 18px; line-height: 1.4;">
                    ${warnings.map(w => `<li style="margin-bottom: 2px;">${w}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    const overlay = document.createElement('div');
    overlay.id = 'success-modal-overlay';
    overlay.className = 'success-overlay';
    overlay.innerHTML = `
        <div class="success-card" style="position: relative; max-width: 380px !important; padding: 22px 24px 20px !important; border-radius: 20px !important; box-sizing: border-box !important;">
            <!-- زر إغلاق والعودة للفاتورة -->
            <button type="button" onclick="onSuccessReturnToInvoice('${invoiceNo}')" style="position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #94a3b8; width: 30px; height: 30px; border-radius: 50%; font-size: 1.15rem; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.25)'; this.style.color='#f43f5e';" onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.color='#94a3b8';" title="العودة لمتابعة الفاتورة">&times;</button>
            <div class="success-check-wrap" style="margin-bottom: 10px !important;">
                <div class="success-check-circle" style="width: 48px !important; height: 48px !important; box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important;">
                    <svg class="success-check-svg" viewBox="0 0 52 52" style="width: 26px !important; height: 26px !important;">
                        <circle class="check-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="check-mark" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
            </div>
            <h2 class="success-title" style="font-size: 1.25rem !important; margin-bottom: 3px !important; font-weight: 800 !important; color: #f8fafc !important;">🎉 تم الحفظ بنجاح</h2>
            <p class="success-inv-no" style="font-size: 0.9rem !important; margin-bottom: 12px !important; color: #94a3b8 !important;">رقم الفاتورة: <strong style="color: #38bdf8 !important;">#${invoiceNo}</strong></p>
            <div class="success-amount-box" style="padding: 10px 16px !important; margin-bottom: 16px !important; border-radius: 12px !important;">
                <div class="success-amount-label" style="font-size: 0.75rem !important; margin-bottom: 2px !important;">إجمالي المبلغ</div>
                <div class="success-amount-value" style="font-size: 1.4rem !important; font-weight: 800 !important;">${grandTotal} ${currency || ''}</div>
            </div>
            ${warningsHtml}
            <div class="success-actions" style="margin-top: 15px; display: flex; flex-direction: column; gap: 8px;">
                <!-- زر العودة لمتابعة الفاتورة (نفس المشتريات) -->
                <button class="btn success-btn" style="height: 44px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; font-weight: 800; font-size: 0.95rem; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35); transition: 0.2s;" onmouseover="this.style.transform='translateY(-1px)';" onmouseout="this.style.transform='none';" onclick="onSuccessReturnToInvoice('${invoiceNo}')">
                    <span>🔙 العودة لمتابعة الفاتورة</span>
                </button>
                <!-- زر إرسال إذن استلام للعميل عبر واتساب (نفس المشتريات) -->
                <button class="btn btn-success success-btn" style="height: 44px; background: linear-gradient(135deg, #25d366, #128c7e); color: #fff; font-weight: 800; font-size: 0.95rem; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35); transition: 0.2s;" onmouseover="this.style.transform='translateY(-1px)';" onmouseout="this.style.transform='none';" onclick="if(typeof sendCurrentInvoiceWA==='function'){sendCurrentInvoiceWA();}else if(typeof openSalesWhatsAppModal==='function'){openSalesWhatsAppModal('${invoiceNo}');}else{alert('دالة الإرسال غير متوفرة');}">
                    <span>📲 إرسال إذن استلام للعميل (واتساب)</span>
                </button>
                ${(() => {
                    const str = localStorage.getItem('erp_inv_' + invoiceNo) || localStorage.getItem('erp_draft_' + invoiceNo);
                    if (str) {
                        try {
                            const data = JSON.parse(str);
                            if (parseFloat(data.creditAmount) > 0) {
                                return `<button class="btn btn-success success-btn" style="height: 40px; background:linear-gradient(135deg,#f59e0b,#d97706); font-size: 0.9rem; font-weight: 700; border-radius: 8px;" onclick="printCurrentPromissoryNote('${invoiceNo}')">📜 طباعة سند لأمر / إقرار دين</button>`;
                            }
                        } catch(e) {}
                    }
                    return '';
                })()}
                <div style="display: flex; gap: 8px; margin-top: 2px;">
                    <button class="btn btn-ghost success-btn" style="flex: 1; height: 38px; font-size: 0.88rem; font-weight: 700; border-radius: 8px;" onclick="onSuccessNewInvoice()">✨ عملية جديدة</button>
                    <button class="btn btn-success success-btn" style="flex: 1; height: 38px; font-size: 0.88rem; font-weight: 700; border-radius: 8px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.35); color: #60a5fa;" onclick="onSuccessDone()">📋 قائمة المبيعات</button>
                </div>
            </div>
        </div>
    `;
    overlay.onclick = function(e) {
        if (e.target === overlay) {
            onSuccessReturnToInvoice(invoiceNo);
        }
    };
    document.body.appendChild(overlay);

    // Trigger animation
    requestAnimationFrame(() => overlay.classList.add('visible'));
}

function onSuccessReturnToInvoice(invoiceNo) {
    const overlay = document.getElementById('success-modal-overlay');
    if (overlay) {
        overlay.classList.remove('visible');
        setTimeout(() => overlay.remove(), 250);
    }
    const targetNo = invoiceNo || currentViewedInvoiceNo || window.currentViewedInvoiceNo || window._lastSubmittedSalesInvoiceNo;
    if (targetNo) {
        currentViewedInvoiceNo = targetNo;
        window.currentViewedInvoiceNo = targetNo;
        loadInvoiceFromStorage(targetNo, { isLocked: true, isPosted: true });
    }
    if (typeof updateActionButtonsVisibility === 'function') {
        updateActionButtonsVisibility();
    }
    if (typeof showToast === 'function') {
        showToast(`📄 تم العودة للفاتورة #${targetNo} (الفاتورة مرحّلة ومقفلة)`, 'info', 3500);
    }
}

function onSuccessNewInvoice(closeModalFlag = true) {
    if (!closeModalFlag && !window._dsForceNewInv && typeof rows !== 'undefined' && rows.length > 0 && rows.some(r => r && (r.itemId || (r.price && Number(r.price) > 0)))) {
        if (typeof window.openDsConfirmModal === 'function') {
            window.openDsConfirmModal(
                '➕ بدء فاتورة جديدة',
                'هل تريد تفريغ البيانات الحالية وبدء فاتورة مبيعات جديدة؟',
                () => {
                    window._dsForceNewInv = true;
                    onSuccessNewInvoice(closeModalFlag);
                    setTimeout(() => { window._dsForceNewInv = false; }, 300);
                }
            );
            return;
        }
    }
    currentViewedInvoiceNo = null; // Reset edit mode
    window.isInvoiceEditingPermitted = false;
    window.isAmendingInvoice = false;
    window.isInvoiceLocked = false;
    window.isInvoicePosted = false;

    // Remove immutability locks from form elements
    document.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.hasAttribute('data-immutability-locked')) {
            el.disabled = false;
            el.removeAttribute('data-immutability-locked');
        }
    });

    // Restore save button and hide locked badge
    const lockedBadge = document.getElementById('locked-submit-badge');
    if (lockedBadge) lockedBadge.style.display = 'none';
    const saveBtn = document.querySelector('.btn-submit');
    if (saveBtn) {
        saveBtn.style.display = 'flex';
        saveBtn.style.background = 'linear-gradient(135deg, #0ea5e9, #2563eb)';
        saveBtn.style.boxShadow = '0 2px 10px rgba(56, 189, 248, 0.4)';
        saveBtn.innerHTML = '✅ تأكيد وحفظ';
    }

    const amendedBadge = document.getElementById('hdr-amended-badge');
    if (amendedBadge) amendedBadge.style.display = 'none';

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
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('sales-user-audit-container', {}, true);
    }
    if (!closeModalFlag && typeof showToast === 'function') {
        showToast('✨ تم تجهيز فاتورة جديدة', 'success');
    }
}
window.onSuccessNewInvoice = onSuccessNewInvoice;

function onSuccessDone() {
    const overlay = document.getElementById('success-modal-overlay');
    if (overlay) { overlay.classList.remove('visible'); setTimeout(() => overlay.remove(), 250); }
    if (typeof showSalesHub === 'function') {
        showSalesHub();
    } else {
        showToast('📋 قائمة المبيعات', 'info');
    }
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
        if (typeof window.openDsConfirmModal === 'function') {
            window.openDsConfirmModal(
                '🚚 تأكيد تسليم الفاتورة',
                'هل أنت متأكد من تسليم الفاتورة رقم (' + invNo + ') وخصم البضاعة من المخزون؟',
                () => { confirmExecuteDeliveryAction(); }
            );
            return;
        }
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
    if (!window._dsConfirmedConvertOfficial && typeof window.openDsConfirmModal === 'function') {
        window.openDsConfirmModal(
            '📋 تحويل إلى فاتورة رسمية',
            'هل تريد تحويل الفاتورة المبدئية رقم (' + invNo + ') إلى فاتورة مبيعات رسمية وتحديث الحسابات؟',
            () => {
                window._dsConfirmedConvertOfficial = true;
                convertToOfficial(invNo);
                setTimeout(() => { window._dsConfirmedConvertOfficial = false; }, 300);
            }
        );
        return;
    }

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
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=status&userId=${userId}`);
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
    
    // Create a fullscreen overlay to display the document clearly while sending (نفس المشتريات)
    const overlayWrapper = document.createElement('div');
    overlayWrapper.style.position = 'fixed';
    overlayWrapper.style.top = '0';
    overlayWrapper.style.left = '0';
    overlayWrapper.style.width = '100vw';
    overlayWrapper.style.height = '100vh';
    overlayWrapper.style.background = 'rgba(15, 23, 42, 0.85)';
    overlayWrapper.style.backdropFilter = 'blur(10px)';
    overlayWrapper.style.zIndex = '999999999';
    overlayWrapper.style.display = 'flex';
    overlayWrapper.style.flexDirection = 'column';
    overlayWrapper.style.alignItems = 'center';
    overlayWrapper.style.justifyContent = 'center';
    overlayWrapper.style.overflowY = 'auto';
    overlayWrapper.style.padding = '40px 20px';

    const statusHeader = document.createElement('div');
    statusHeader.innerHTML = `
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
            <div style="width:30px; height:30px; border:3px solid #25d366; border-top-color:transparent; border-radius:50%; animation:spin 1s linear infinite;"></div>
            <h3 style="color:#25d366; font-family:'Tajawal', sans-serif; font-size:1.6rem; margin:0; font-weight:bold;">جاري الإرسال عبر واتساب...</h3>
        </div>
    `;
    
    const printContainer = document.createElement('div');
    printContainer.className = 'print-preview-wrapper';
    printContainer.style.width = '800px';
    printContainer.style.maxWidth = '100%';
    printContainer.style.background = '#f0f4f8';
    printContainer.style.padding = '24px';
    printContainer.style.borderRadius = '16px';
    printContainer.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5)';
    printContainer.style.transform = 'scale(0.85)';
    
    printContainer.innerHTML = htmlContent;
    
    overlayWrapper.appendChild(statusHeader);
    overlayWrapper.appendChild(printContainer);
    document.body.appendChild(overlayWrapper);

    const isAmended = !!(inv.isAmended || inv.amendmentCount);
    const companyName = JSON.parse(localStorage.getItem('user') || '{}')?.company_name || 'مؤسستنا';
    let message = '';
    if (isAmended) {
        message = `⚠️ إشعار تعديل فاتورة: مرحباً، نود إحاطتكم بأنه تم إجراء تعديل رسمي على فاتورتكم رقم ${invoiceNo} من ${companyName}. مرفق طيه الفاتورة المعدلة للاعتماد والعمل بموجبها. شكراً لتعاملكم معنا!`;
    } else {
        message = `مرحباً، مرفق فاتورتكم رقم ${invoiceNo} من ${companyName}. شكراً لتعاملكم معنا!`;
    }
    
    try {
        await sendPDFViaWhatsApp(printContainer, phone, message);
    } catch (e) {
        console.error(e);
    } finally {
        overlayWrapper.remove();
    }
}

function updateActionButtonsVisibility() {
    const previewBtn = document.getElementById('btn-preview-invoice');
    const invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : window.currentViewedInvoiceNo;
    if (invNo && invNo !== '---' && invNo !== 'SAL-NEW' && invNo !== 'جديد') {
        if (previewBtn) previewBtn.style.display = 'flex';
    } else {
        if (previewBtn) previewBtn.style.display = 'none';
    }
}

async function sendCurrentInvoiceWA() {
    let invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : (window.currentViewedInvoiceNo || window._lastSubmittedSalesInvoiceNo || document.getElementById('hdr-inv-no')?.value);
    if (invNo && invNo !== '---' && invNo !== 'SAL-NEW' && invNo !== 'جديد') {
        await sendInvoiceWA(invNo);
    } else {
        showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
    }
}

// ════════════════════════════════════════════════════════════
// ✂️ SPLIT INVOICE FEATURE LOGIC
// ════════════════════════════════════════════════════════════

let activeSplitInvoice = null;
let _isFetchingSplitSync = false;
window.splitSectionsCount = 2;
window.currentSplitOperationSeq = 1;
window._splitSectionMeta = {};
window._splitReturnToView = null;

window.getRootInvoiceNo = function(invOrNo) {
    if (!invOrNo) return '';
    if (typeof invOrNo === 'object') {
        if (invOrNo.rootInvoice) return String(invOrNo.rootInvoice).split('/')[0].trim();
        if (invOrNo.parentInvoice) return String(invOrNo.parentInvoice).split('/')[0].trim();
        const raw = String(invOrNo.invoiceNo || invOrNo.serial || invOrNo.id || '').trim();
        return raw.split('/')[0].replace(/-\d+$/, '').trim();
    }
    return String(invOrNo).split('/')[0].replace(/-\d+$/, '').trim();
};

window.parseSplitInvoiceParts = function(invOrNo) {
    const invObj = typeof invOrNo === 'object' ? invOrNo : null;
    const rawNo = String(invObj ? (invObj.invoiceNo || invObj.serial || invObj.id || '') : (invOrNo || '')).trim();
    const slashMatch = rawNo.match(/^([^\/]+)\/(\d+)\/(\d+)$/);
    if (slashMatch) {
        return {
            isSplit: true,
            fullNo: rawNo,
            rootInvoice: invObj?.rootInvoice || slashMatch[1],
            parentInvoice: invObj?.parentInvoice || slashMatch[1],
            splitBatchNo: parseInt(slashMatch[2], 10),
            splitBranchIndex: parseInt(slashMatch[3], 10),
            splitTotalBranches: invObj?.splitTotalBranches || 2
        };
    }
    if (invObj && (invObj.isSplit || invObj.parentInvoice || invObj.splitBatchNo)) {
        return {
            isSplit: true,
            fullNo: rawNo,
            rootInvoice: invObj.rootInvoice || window.getRootInvoiceNo(invObj),
            parentInvoice: invObj.parentInvoice || window.getRootInvoiceNo(invObj),
            splitBatchNo: invObj.splitBatchNo || 1,
            splitBranchIndex: invObj.splitBranchIndex || 1,
            splitTotalBranches: invObj.splitTotalBranches || 2
        };
    }
    return { isSplit: false, fullNo: rawNo };
};

window.renderSplitGenealogyBadge = function(invOrNo, compact = false) {
    const info = window.parseSplitInvoiceParts(invOrNo);
    const invObj = typeof invOrNo === 'object' ? invOrNo : null;
    if (!info.isSplit && !(invObj && invObj.wasSplit)) return '';
    const escNo = String(info.fullNo).replace(/'/g, "\\'");
    if (invObj && invObj.wasSplit) {
        const children = Array.isArray(invObj.splitTo) ? invObj.splitTo.join(' ، ') : '';
        return `<span onclick="event.stopPropagation(); window.openSplitGenealogyModal('${escNo}')" class="kinetic-split-genealogy-pill" title="اضغط لعرض شجرة التقسيم الكاملة" style="cursor:pointer; display:inline-flex; align-items:center; gap:5px; background:linear-gradient(135deg, rgba(236,72,153,0.22), rgba(139,92,246,0.22)); border:1px solid rgba(244,114,182,0.55); color:#f9a8d4; padding:2px 9px; border-radius:99px; font-size:0.74rem; font-weight:900; box-shadow:0 0 12px rgba(236,72,153,0.25);">✂️ قُسّم إلى: ${children || 'أقسام فرعية'}</span>`;
    }
    if (compact) {
        return `<span onclick="event.stopPropagation(); window.openSplitGenealogyModal('${escNo}')" class="kinetic-split-genealogy-pill" title="أصل الإذن #${info.rootInvoice} | عملية التقسيم #${info.splitBatchNo} | القسم ${info.splitBranchIndex} — اضغط لعرض شجرة التقسيم" style="cursor:pointer; display:inline-flex; align-items:center; gap:4px; background:linear-gradient(135deg, rgba(236,72,153,0.2), rgba(56,189,248,0.18)); border:1px solid rgba(244,114,182,0.5); color:#fbcfe8; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:900; box-shadow:0 0 10px rgba(236,72,153,0.22);">🔗 أصل #${info.rootInvoice} • تقسيم #${info.splitBatchNo} • قسم ${info.splitBranchIndex}/${info.splitTotalBranches}</span>`;
    }
    return `<div onclick="event.stopPropagation(); window.openSplitGenealogyModal('${escNo}')" class="kinetic-split-genealogy-pill" title="اضغط لفتح شجرة عائلة الإذن وتتبع جميع الأقسام" style="cursor:pointer; margin-top:4px; display:inline-flex; align-items:center; gap:5px; background:linear-gradient(135deg, rgba(236,72,153,0.22), rgba(99,102,241,0.22)); border:1px solid rgba(244,114,182,0.55); color:#f9a8d4; padding:3px 10px; border-radius:99px; font-size:0.74rem; font-weight:900; box-shadow:0 0 14px rgba(236,72,153,0.28); transition:all 0.2s;">
        <span>✂️</span>
        <span>أصل الإذن: <strong style="color:#38bdf8;">#${info.rootInvoice}</strong></span>
        <span style="opacity:0.6;">|</span>
        <span>تقسيم: <strong style="color:#fcd34d;">#${info.splitBatchNo}</strong></span>
        <span style="opacity:0.6;">|</span>
        <span>قسم <strong style="color:#6ee7b7;">${info.splitBranchIndex}</strong> من ${info.splitTotalBranches}</span>
    </div>`;
};

window.getNextSplitSequenceNo = function() {
    let maxSeq = parseInt(localStorage.getItem('erp_sales_split_seq_v1') || '0', 10) || 0;
    try {
        const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        allSales.forEach(inv => {
            if (inv.splitBatchNo && parseInt(inv.splitBatchNo, 10) > maxSeq) {
                maxSeq = parseInt(inv.splitBatchNo, 10);
            }
            const rawNo = String(inv.invoiceNo || inv.id || '');
            const m = rawNo.match(/^[^\/]+\/(\d+)\/\d+$/);
            if (m && parseInt(m[1], 10) > maxSeq) {
                maxSeq = parseInt(m[1], 10);
            }
        });
    } catch (e) {}
    return maxSeq + 1;
};

window.resolveSplitItemUnitMeta = function(item) {
    const richMeta = (typeof window.resolveItemMeta === 'function') ? window.resolveItemMeta(item) : null;
    let masterItem = richMeta ? richMeta.master : null;
    if (!masterItem) {
        try {
            const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS) && ITEMS.length)
                ? ITEMS
                : JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
            masterItem = itemsList.find(i =>
                String(i.id) === String(item.itemId || item.id) ||
                String(i.name || '').trim() === String(item.itemName || item.name || '').trim()
            );
        } catch (e) {}
    }

    const name = richMeta ? richMeta.name : (masterItem ? masterItem.name : (item.itemName || item.name || 'صنف غير معروف'));
    const primaryUnit = richMeta ? richMeta.unit : (item.selectedUnit || item.unit || (masterItem && (masterItem.unit || masterItem.mainUnit)) || 'طن');
    let baseUnit = richMeta ? richMeta.baseUnit : (item.baseUnit || (masterItem && (masterItem.baseUnit || masterItem.subUnit || masterItem.smallUnit)) || 'وحدة');
    let factor = richMeta ? parseFloat(richMeta.factor) : parseFloat(item.unitFactor || item.factor || item.conversionFactor || (masterItem && (masterItem.conversionFactor || masterItem.unitFactor || masterItem.piecesPerTon)) || 1);
    if (isNaN(factor) || factor <= 0) factor = 1;
    if (baseUnit === primaryUnit && factor === 1) {
        baseUnit = 'وحدة فرعية';
    }

    const origQty = parseFloat(item.quantity ?? item.qty ?? 0) || 0;
    const deliveredQty = parseFloat(item.deliveredQty || 0) || 0;
    const deliverableQty = Math.max(0, +(origQty - deliveredQty).toFixed(4));
    const deliverableBaseQty = +(deliverableQty * factor).toFixed(2);
    const price = parseFloat(item.price ?? (richMeta ? richMeta.price : 0)) || 0;
    const tonRefHint = (richMeta && richMeta.tonRefHint) ? richMeta.tonRefHint : (factor > 1 ? `1 ${primaryUnit} = ${factor} ${baseUnit}` : `1 ${primaryUnit} = 1 ${baseUnit}`);

    return {
        name,
        primaryUnit,
        baseUnit,
        factor,
        tonRefHint,
        hasMultiUnits: factor > 1 || (primaryUnit !== baseUnit && baseUnit !== 'وحدة فرعية'),
        origQty,
        deliveredQty,
        deliverableQty,
        deliverableBaseQty,
        price
    };
};

window.splitUnitDisplayMode = window.splitUnitDisplayMode || 'both'; // 'both' | 'primary' | 'base'
window.setSplitUnitDisplayMode = function(mode) {
    window.splitUnitDisplayMode = mode || 'both';
    window.saveCurrentSplitDraftState();
    renderWorldClassSplitBoard();
};

window.openSplitForInvoiceFromDelivery = function(invoiceNo, preferredUnitMode = null) {
    if (!invoiceNo) return;
    window._splitReturnToView = 'hub';
    if (preferredUnitMode) window.splitUnitDisplayMode = preferredUnitMode;
    if (typeof closeDeliveryConfirmationModal === 'function') {
        closeDeliveryConfirmationModal();
    } else {
        const m = document.getElementById('delivery-confirmation-modal');
        if (m) m.style.display = 'none';
    }
    if (typeof openSplitInvoiceView === 'function') {
        openSplitInvoiceView(true);
    }
    setTimeout(() => {
        startInvoiceSplit(String(invoiceNo), preferredUnitMode);
    }, 20);
};

function normalizeSplitArabicText(str) {
    return String(str || '')
        .toLowerCase()
        .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
        .replace(/[أإآٱ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/[ىئ]/g, 'ي')
        .replace(/ؤ/g, 'و')
        .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
        .trim();
}

function getMergedSalesInvoicesForSplit() {
    const map = new Map();
    ['magaies_sales_invoices_cache', 'magaies_local_sales_invoices_v1', 'erp_sales_v1', 'erp_sales_invoices_v1'].forEach(k => {
        try {
            const arr = JSON.parse(localStorage.getItem(k) || '[]');
            if (Array.isArray(arr)) {
                arr.forEach(inv => {
                    if (!inv || typeof inv !== 'object') return;
                    const no = String(inv.invoiceNo || inv.id || '').trim();
                    if (no) map.set(no, { ...(map.get(no) || {}), ...inv });
                });
            }
        } catch (_e) {}
    });
    return Array.from(map.values());
}

function loadSplitInvoices(skipRemoteFetch = false) {
    const rawSearch = (document.getElementById('split-invoice-search')?.value || '').trim();
    const searchVal = normalizeSplitArabicText(rawSearch);
    const tbody = document.getElementById('split-invoices-body');
    const emptyState = document.getElementById('split-empty-state');
    if (!tbody) return;

    if (!skipRemoteFetch && !_isFetchingSplitSync && navigator.onLine) {
        _isFetchingSplitSync = true;
        const ctrl = new AbortController();
        const tId = setTimeout(() => ctrl.abort(), 4000);
        fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now(), { signal: ctrl.signal })
            .then(r => { clearTimeout(tId); return r.json(); })
            .then(res => {
                _isFetchingSplitSync = false;
                if (res && res.success && Array.isArray(res.sales)) {
                    let localSales = [];
                    try { localSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []; } catch(e) {}
                    let updated = false;
                    res.sales.forEach(s => {
                        const invNo = s.invoiceNo || s.id;
                        if (!invNo) return;
                        const idx = localSales.findIndex(x => String(x.invoiceNo || x.id) === String(invNo));
                        if (idx === -1) {
                            if (!s.deleted && !s.wasSplit) {
                                localSales.unshift(s);
                                updated = true;
                            }
                        } else if (s.wasSplit && !localSales[idx].wasSplit) {
                            localSales[idx] = Object.assign({}, localSales[idx], s, { deleted: true, wasSplit: true });
                            updated = true;
                        }
                    });
                    if (updated) {
                        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(localSales));
                        loadSplitInvoices(true);
                    }
                }
            })
            .catch(() => { clearTimeout(tId); _isFetchingSplitSync = false; });
    }
    
    const invoices = getMergedSalesInvoicesForSplit();
    
    const eligible = invoices.filter(inv => {
        if (inv.deleted || inv.wasSplit) return false;
        if (inv.status === 'draft' || inv.saleType === 'preliminary' || inv.status === 'preliminary') return false;
        const isFullyDelivered = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed';
        if (isFullyDelivered) return false;
        const items = Array.isArray(inv.items) ? inv.items : [];
        const hasRem = items.some(it => (parseFloat(it.quantity ?? it.qty ?? 0) - parseFloat(it.deliveredQty || 0)) > 0.0001);
        return hasRem || items.length > 0;
    });
    
    const statusFilter = document.getElementById('split-filter-status')?.value || 'all';
    const payFilter = document.getElementById('split-filter-payment')?.value || 'all';
    const dateFilter = (document.getElementById('split-filter-date')?.value || '').trim();

    const filtered = eligible.filter(inv => {
        const isBranch = !!(inv.isSplit || inv.parentInvoice || String(inv.invoiceNo || inv.id || '').includes('/'));
        const isPartial = inv.deliveryStatus === 'partial';
        if (statusFilter === 'original' && isBranch) return false;
        if (statusFilter === 'branches' && !isBranch) return false;
        if (statusFilter === 'partial' && !isPartial) return false;

        if (payFilter === 'credit' && inv.paymentMethod !== 'credit') return false;
        if (payFilter === 'cash' && inv.paymentMethod === 'credit') return false;

        if (dateFilter && String(inv.date || '').slice(0, 10) !== dateFilter) return false;

        if (!searchVal) return true;
        const no = normalizeSplitArabicText(inv.invoiceNo || inv.id || '');
        const rootNo = normalizeSplitArabicText(window.getRootInvoiceNo(inv));
        const custName = normalizeSplitArabicText(inv.customerName || inv.customer || '');
        const total = normalizeSplitArabicText(inv.grandTotal || inv.total || '');
        const itemsStr = normalizeSplitArabicText((inv.items || []).map(i => window.resolveSplitItemUnitMeta(i).name).join(' '));
        return no.includes(searchVal) || rootNo.includes(searchVal) || custName.includes(searchVal) || total.includes(searchVal) || itemsStr.includes(searchVal);
    });

    filtered.sort((a, b) => new Date(b.timestamp || b.date || 0) - new Date(a.timestamp || a.date || 0));
    window._lastFilteredSplitInvoices = filtered;

    const kpiBar = document.getElementById('split-kpi-summary-bar');
    if (kpiBar) {
        let totalUnits = 0;
        let totalNetVal = 0;
        let branchesCount = 0;
        filtered.forEach(inv => {
            if (inv.isSplit || inv.parentInvoice || String(inv.invoiceNo || inv.id || '').includes('/')) branchesCount++;
            totalNetVal += parseFloat(inv.grandTotal ?? inv.total ?? 0) || 0;
            (inv.items || []).forEach(it => {
                const m = window.resolveSplitItemUnitMeta(it);
                totalUnits += m.deliverableQty;
            });
        });
        kpiBar.innerHTML = `
            <div class="neo-card" style="margin:0; padding:8px 12px; border-radius:10px; border:1.5px solid rgba(56,189,248,0.45); background:rgba(15,23,42,0.7); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.82rem; font-weight:900; color:#94a3b8;">📄 فواتير جاهزة للتجزئة:</span>
                <strong style="font-size:1.1rem; font-family:'Outfit',monospace; color:#38bdf8;">${filtered.length}</strong>
            </div>
            <div class="neo-card" style="margin:0; padding:8px 12px; border-radius:10px; border:1.5px solid rgba(236,72,153,0.45); background:rgba(15,23,42,0.7); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.82rem; font-weight:900; color:#94a3b8;">🌳 فروع منبثقة نشطة:</span>
                <strong style="font-size:1.1rem; font-family:'Outfit',monospace; color:#f472b6;">${branchesCount}</strong>
            </div>
            <div class="neo-card" style="margin:0; padding:8px 12px; border-radius:10px; border:1.5px solid rgba(168,85,247,0.45); background:rgba(15,23,42,0.7); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.82rem; font-weight:900; color:#94a3b8;">📦 إجمالي الوحدات المتبقية:</span>
                <strong style="font-size:1.1rem; font-family:'Outfit',monospace; color:#c084fc;">${+totalUnits.toFixed(2)}</strong>
            </div>
            <div class="neo-card" style="margin:0; padding:8px 12px; border-radius:10px; border:1.5px solid rgba(16,185,129,0.45); background:rgba(15,23,42,0.7); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.82rem; font-weight:900; color:#94a3b8;">💰 الإجمالي الختامي الموزون:</span>
                <strong style="font-size:1.1rem; font-family:'Outfit',monospace; color:#10b981;">${totalNetVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</strong>
            </div>
        `;
    }
    
    if (filtered.length === 0) {
        tbody.innerHTML = '';
        if (emptyState) emptyState.style.display = 'flex';
        return;
    }
    
    if (emptyState) emptyState.style.display = 'none';
    tbody.innerHTML = '';
    const nextSplitSeq = window.getNextSplitSequenceNo();
    let customersList = [];
    try { customersList = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]'); } catch (_e) {}
    
    filtered.forEach(inv => {
        const invNo = String(inv.invoiceNo || inv.id || '');
        const rootNo = window.getRootInvoiceNo(inv);
        const escInvNo = invNo.replace(/'/g, "\\'");
        const invTotal = parseFloat(inv.grandTotal ?? inv.total ?? 0) || 0;
        const items = Array.isArray(inv.items) ? inv.items : [];
        const custObj = customersList.find(c => String(c.id) === String(inv.customer || inv.customerId) || c.name === inv.customerName);
        const custBal = custObj ? (parseFloat(custObj.currentBalance || custObj.balance || 0) || 0) : null;
        const auditUser = inv.splitByName || inv.createdByName || 'المسؤول';

        let remSummary = items.map(it => {
            const m = window.resolveSplitItemUnitMeta(it);
            return `
                <div style="display:inline-flex; align-items:center; gap:5px; background:rgba(15,23,42,0.85); border:1px solid rgba(56,189,248,0.3); padding:3px 8px; border-radius:8px; font-size:0.78rem; font-weight:800;">
                    <span style="color:#f8fafc;">${m.name}:</span>
                    <button type="button" onclick="startInvoiceSplit('${escInvNo}', 'primary')" style="background:rgba(16,185,129,0.18); border:1px solid rgba(52,211,153,0.45); color:#6ee7b7; padding:2px 7px; border-radius:6px; font-weight:900; font-size:0.76rem; cursor:pointer;" title="تقسيم بالوحدة الأساسية (${m.primaryUnit})">📦 ${m.deliverableQty} ${m.primaryUnit}</button>
                    ${m.factor > 1 ? `<button type="button" onclick="startInvoiceSplit('${escInvNo}', 'base')" style="background:rgba(99,102,241,0.2); border:1px solid rgba(129,140,248,0.5); color:#c4b5fd; padding:2px 7px; border-radius:6px; font-weight:900; font-size:0.75rem; cursor:pointer;" title="تقسيم بالوحدة الفرعية (${m.baseUnit})">🔢 ⇄ ${m.deliverableBaseQty.toLocaleString('en-US')} ${m.baseUnit}</button>` : ''}
                </div>
            `;
        }).join(' ');

        const isPartial = inv.deliveryStatus === 'partial';
        const statusHtml = isPartial
            ? `<span class="badge" style="background:rgba(245,158,11,0.18); border:1px solid rgba(251,191,36,0.4); color:#fcd34d; padding:5px 10px; border-radius:8px; font-size:0.85rem; font-weight:900;">🌗 متبقي للتسليم</span>`
            : `<span class="badge draft" style="padding:5px 10px; border-radius:8px; font-size:0.85rem; font-weight:900;">⏳ بانتظار التسليم</span>`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div style="display:flex; flex-direction:column; gap:3px;">
                    <strong onclick="window.openSplitDrillDownDocModal('${escInvNo}')" title="اضغط لاستعراض أصل الفاتورة والبصمة الرقابية (Drill-Down)" style="cursor:pointer; color:#38bdf8; font-size:1.12rem; font-family:'Outfit',monospace; text-decoration:underline dotted;">#${invNo} 🔍</strong>
                    ${window.renderSplitGenealogyBadge(inv, true)}
                    <span style="font-size:0.73rem; color:#94a3b8;">سيُقسم إلى: <strong style="color:#f472b6; font-family:monospace;">${rootNo}/${nextSplitSeq}/1</strong> و <strong style="color:#38bdf8; font-family:monospace;">${rootNo}/${nextSplitSeq}/2</strong></span>
                </div>
            </td>
            <td>
                <span style="font-weight:800; font-size:0.95rem; color:#cbd5e1;">${inv.date || ''}</span>
                <div style="font-size:0.7rem; color:#94a3b8; font-weight:800; margin-top:2px;">🛡️ ${auditUser}</div>
            </td>
            <td>
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <span style="font-weight:900; font-size:1.02rem; color:#fff;">👤 ${inv.customerName || 'عميل نقدي'}</span>
                    ${custBal !== null ? `<span style="font-size:0.72rem; background:rgba(16,185,129,0.15); border:1px solid rgba(52,211,153,0.4); color:#6ee7b7; padding:1px 7px; border-radius:99px; font-weight:900;">رصيد العميل: ${custBal.toLocaleString()}</span>` : ''}
                </div>
                <div style="margin-top:4px; display:flex; flex-wrap:wrap; gap:5px;">${remSummary}</div>
            </td>
            <td style="text-align:center;"><span class="badge" style="background:rgba(99,102,241,0.2); color:#a5b4fc; padding:5px 10px; border-radius:8px; font-size:0.88rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;">${inv.paymentMethod === 'credit' ? '✍️ آجل' : '💵 نقدي'}</span></td>
            <td style="text-align:left; font-family:monospace; font-weight:900; font-size:1.2rem; color:#10b981;">${invTotal.toLocaleString()} <span style="font-size:0.78rem; color:#94a3b8; font-family:'Tajawal',sans-serif;">${inv.currency || 'SDG'}</span></td>
            <td style="text-align:center;">${statusHtml}</td>
            <td style="text-align:center;">
                <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                    <button class="btn-action kinetic-glow-arm kinetic-arm-pink" style="width:auto; padding:7px 14px; font-size:0.86rem; font-weight:900; border-radius:10px; cursor:pointer;" onclick="startInvoiceSplit('${escInvNo}')">
                        ✂️ تقسيم
                    </button>
                    <button class="btn-action kinetic-glow-arm" style="width:auto; padding:7px 10px; font-size:0.8rem; font-weight:900; border-radius:10px; cursor:pointer; background:rgba(56,189,248,0.16); border:1px solid rgba(56,189,248,0.45); color:#7dd3fc;" onclick="window.openSplitDrillDownDocModal('${escInvNo}')" title="استعراض أصل المستند والبصمة الرقابية">
                        🔍 أصل
                    </button>
                    <button class="btn-action kinetic-glow-arm kinetic-arm-emerald" style="width:auto; padding:7px 14px; font-size:0.86rem; font-weight:900; border-radius:10px; cursor:pointer;" onclick="if(typeof window.openDeliveryForInvoiceFromHub==='function') window.openDeliveryForInvoiceFromHub('${escInvNo}')" title="الانتقال الفوري لتسليم وصرف هذا الإذن">
                        📦 تسليم
                    </button>
                    ${(inv.isSplit || inv.parentInvoice) ? `
                    <button class="btn-action kinetic-glow-arm kinetic-arm-cyan" style="width:auto; padding:7px 10px; font-size:0.8rem; font-weight:900; border-radius:10px; cursor:pointer;" onclick="window.printSplitFamilyVouchers('${escInvNo}')" title="طباعة الجزئين / جميع الأذونات المقسمة">
                        🖨️ الجزئين
                    </button>
                    <button class="btn-action kinetic-glow-arm kinetic-arm-cyan" style="width:auto; padding:7px 10px; font-size:0.8rem; font-weight:900; border-radius:10px; cursor:pointer;" onclick="window.openSplitGenealogyModal('${escInvNo}')" title="شجرة التقسيم">
                        🌳
                    </button>` : ''}
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filterSplitInvoices() {
    loadSplitInvoices(true);
}

const SPLIT_SECTION_PALETTE = [
    { name: 'القسم الأول (1)', color: '#38bdf8', bg: 'rgba(56,189,248,0.08)', border: 'rgba(56,189,248,0.55)', glow: 'rgba(56,189,248,0.28)' },
    { name: 'القسم الثاني (2)', color: '#ec4899', bg: 'rgba(236,72,153,0.08)', border: 'rgba(236,72,153,0.55)', glow: 'rgba(236,72,153,0.28)' },
    { name: 'القسم الثالث (3)', color: '#10b981', bg: 'rgba(16,185,129,0.08)', border: 'rgba(16,185,129,0.55)', glow: 'rgba(16,185,129,0.28)' },
    { name: 'القسم الرابع (4)', color: '#f59e0b', bg: 'rgba(245,158,11,0.08)', border: 'rgba(245,158,11,0.55)', glow: 'rgba(245,158,11,0.28)' },
    { name: 'القسم الخامس (5)', color: '#a855f7', bg: 'rgba(168,85,247,0.08)', border: 'rgba(168,85,247,0.55)', glow: 'rgba(168,85,247,0.28)' },
    { name: 'القسم السادس (6)', color: '#14b8a6', bg: 'rgba(20,184,166,0.08)', border: 'rgba(20,184,166,0.55)', glow: 'rgba(20,184,166,0.28)' }
];

function getSplitPalette(secIdx) {
    const base = SPLIT_SECTION_PALETTE[secIdx % SPLIT_SECTION_PALETTE.length];
    return {
        ...base,
        title: `القسم (${secIdx + 1})`
    };
}

function startInvoiceSplit(invoiceNo, preferredUnitMode = null) {
    const invoices = getMergedSalesInvoicesForSplit();
    const inv = invoices.find(x => String(x.invoiceNo || x.id) === String(invoiceNo));
    if (!inv) {
        if (typeof showToast === 'function') showToast('❌ لم يتم العثور على الإذن المحدد للتقسيم', 'error');
        else alert('حدث خطأ: لم يتم العثور على الفاتورة المحددة!');
        return;
    }
    
    activeSplitInvoice = JSON.parse(JSON.stringify(inv));
    window.activeSplitInvoice = activeSplitInvoice;
    window.splitSectionsCount = 2;
    window.currentSplitOperationSeq = window.getNextSplitSequenceNo();
    window._splitSectionMeta = {};
    if (preferredUnitMode) {
        window.splitUnitDisplayMode = preferredUnitMode;
    } else if (!window.splitUnitDisplayMode) {
        window.splitUnitDisplayMode = 'both';
    }

    activeSplitInvoice._splitDraftQtys = {};
    (activeSplitInvoice.items || []).forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const rem = meta.deliverableQty;
        let q1 = rem > 1 ? Math.ceil(rem / 2) : +(rem / 2).toFixed(3);
        if (rem === 5) q1 = 3;
        let q2 = Math.max(0, +(rem - q1).toFixed(4));
        activeSplitInvoice._splitDraftQtys[idx] = [q1, q2];
    });
    
    document.getElementById('split-select-step').style.display = 'none';
    document.getElementById('split-board-step').style.display = 'flex';
    
    renderWorldClassSplitBoard();
}

window.saveCurrentSplitDraftState = function() {
    if (!activeSplitInvoice) return;
    const S = window.splitSectionsCount || 2;
    (activeSplitInvoice.items || []).forEach((item, idx) => {
        const arr = [];
        for (let s = 0; s < S; s++) {
            const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
            if (inp) {
                arr.push(parseFloat(inp.value) || 0);
            } else {
                const subInp = document.getElementById(`split-subqty-s${s}-i${idx}`);
                const meta = window.resolveSplitItemUnitMeta(item);
                const subVal = subInp ? (parseFloat(subInp.value) || 0) : ((activeSplitInvoice._splitDraftQtys?.[idx]?.[s] || 0) * meta.factor);
                arr.push(meta.factor > 0 ? +(subVal / meta.factor).toFixed(4) : subVal);
            }
        }
        activeSplitInvoice._splitDraftQtys[idx] = arr;
    });
    for (let s = 0; s < S; s++) {
        const recEl = document.getElementById(`split-sec-receiver-${s}`);
        const trkEl = document.getElementById(`split-sec-truck-${s}`);
        const locEl = document.getElementById(`split-sec-location-${s}`);
        const phnEl = document.getElementById(`split-sec-phone-${s}`);
        window._splitSectionMeta[s] = {
            receiverName: recEl ? recEl.value : (window._splitSectionMeta[s]?.receiverName || ''),
            truckPlate: trkEl ? trkEl.value : (window._splitSectionMeta[s]?.truckPlate || ''),
            deliveryLocation: locEl ? locEl.value : (window._splitSectionMeta[s]?.deliveryLocation || ''),
            receiverPhone: phnEl ? phnEl.value : (window._splitSectionMeta[s]?.receiverPhone || '')
        };
    }
};

window.addSplitSection = function() {
    if (!activeSplitInvoice) return;
    window.saveCurrentSplitDraftState();
    window.splitSectionsCount = Math.min(100, (window.splitSectionsCount || 2) + 1);
    const S = window.splitSectionsCount;
    (activeSplitInvoice.items || []).forEach((item, idx) => {
        const arr = activeSplitInvoice._splitDraftQtys[idx] || [];
        while (arr.length < S) arr.push(0);
        activeSplitInvoice._splitDraftQtys[idx] = arr;
    });
    renderWorldClassSplitBoard();
    if (typeof showToast === 'function') {
        const rootNo = window.getRootInvoiceNo(activeSplitInvoice);
        showToast(`➕ تمت إضافة القسم (${S}) برقم: ${rootNo}/${window.currentSplitOperationSeq}/${S}`, 'info');
    }
};

window.removeSplitSection = function(secIdx) {
    if (!activeSplitInvoice || (window.splitSectionsCount || 2) <= 2) return;
    window.saveCurrentSplitDraftState();
    const S = window.splitSectionsCount;
    (activeSplitInvoice.items || []).forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const arr = activeSplitInvoice._splitDraftQtys[idx] || [];
        const removedVal = arr[secIdx] || 0;
        arr.splice(secIdx, 1);
        if (arr.length > 0) {
            arr[arr.length - 1] = Math.min(meta.deliverableQty, +((arr[arr.length - 1] || 0) + removedVal).toFixed(4));
        }
        activeSplitInvoice._splitDraftQtys[idx] = arr;
    });
    window.splitSectionsCount = S - 1;
    renderWorldClassSplitBoard();
};

window.distributeSplitEqually = function() {
    if (!activeSplitInvoice) return;
    const S = window.splitSectionsCount || 2;
    (activeSplitInvoice.items || []).forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const rem = meta.deliverableQty;
        const baseShare = +(rem / S).toFixed(3);
        const arr = [];
        let sumSoFar = 0;
        for (let s = 0; s < S; s++) {
            if (s === S - 1) {
                arr.push(Math.max(0, +(rem - sumSoFar).toFixed(4)));
            } else {
                arr.push(baseShare);
                sumSoFar += baseShare;
            }
        }
        activeSplitInvoice._splitDraftQtys[idx] = arr;
    });
    renderWorldClassSplitBoard();
    if (typeof showToast === 'function') {
        showToast(`⚖️ تم توزيع الكميات بالتساوي على ${S} أقسام`, 'success');
    }
};

window.adjustSplitOperationSeq = function(delta) {
    const nextVal = Math.max(1, (parseInt(window.currentSplitOperationSeq, 10) || 1) + delta);
    window.currentSplitOperationSeq = nextVal;
    const seqInp = document.getElementById('split-operation-seq-input');
    if (seqInp) seqInp.value = nextVal;
    window.saveCurrentSplitDraftState();
    renderWorldClassSplitBoard();
};

window.onSplitOperationSeqInput = function(val) {
    const parsed = parseInt(val, 10);
    if (!isNaN(parsed) && parsed > 0) {
        window.currentSplitOperationSeq = parsed;
        window.saveCurrentSplitDraftState();
        updateSplitCalculations();
    }
};

window.renderWorldClassSplitBoard = function() {
    if (!activeSplitInvoice) return;
    const inv = activeSplitInvoice;
    const rootNo = window.getRootInvoiceNo(inv);
    const parentNo = String(inv.invoiceNo || inv.id || '');
    const escParentNo = parentNo.replace(/'/g, "\\'");
    const splitSeq = window.currentSplitOperationSeq || 1;
    const S = window.splitSectionsCount || 2;
    const currency = inv.currency || 'SDG';
    const uMode = window.splitUnitDisplayMode || 'both';

    const headerInfo = document.getElementById('split-header-info');
    if (headerInfo) {
        const previewBadges = Array.from({ length: S }, (_, s) => {
            const pal = getSplitPalette(s);
            return `<span style="background:${pal.bg}; border:1px solid ${pal.border}; color:${pal.color}; padding:3px 10px; border-radius:99px; font-family:'Outfit',monospace; font-weight:900; font-size:0.88rem; box-shadow:0 0 12px ${pal.glow};">#${rootNo}/${splitSeq}/${s + 1}</span>`;
        }).join(' <span style="color:#94a3b8; font-weight:900;">+</span> ');

        headerInfo.innerHTML = `
            <div style="display:flex; flex-direction:column; gap:10px; width:100%;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(215px, 1fr)); gap:8px; width:100%;">
                    <div style="background:rgba(2,6,23,0.65); border:1.5px solid rgba(56,189,248,0.4); padding:9px 14px; border-radius:12px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 4px 18px rgba(56,189,248,0.12);">
                        <div>
                            <span style="font-size:0.78rem; color:#94a3b8; font-weight:800; display:block;">🧾 الإذن المرجعي الأصلي:</span>
                            <span style="font-size:1.25rem; font-weight:900; color:#38bdf8; font-family:'Outfit',monospace;">#${rootNo}</span>
                            ${parentNo !== rootNo ? `<span style="font-size:0.74rem; color:#f472b6; display:block; font-weight:800;">الفرع الحالي: #${parentNo}</span>` : ''}
                        </div>
                        <button type="button" onclick="if(typeof window.openDeliveryForInvoiceFromHub==='function') window.openDeliveryForInvoiceFromHub('${escParentNo}')" class="kinetic-glow-arm kinetic-arm-emerald" style="padding:6px 12px; border-radius:9px; font-size:0.78rem; font-weight:900; cursor:pointer;" title="تحويل فوري لشاشة التسليم لهذا الإذن">🚚 للتسليم</button>
                    </div>

                    <div style="background:rgba(2,6,23,0.65); border:1.5px solid rgba(236,72,153,0.45); padding:8px 12px; border-radius:12px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 4px 18px rgba(236,72,153,0.14);">
                        <div>
                            <span style="font-size:0.78rem; color:#fbcfe8; font-weight:900; display:block;">🔢 رقم عملية التقسيم التسلسلي:</span>
                            <span style="font-size:0.72rem; color:#94a3b8;">يُدمج تلقائياً (${rootNo}/<b style="color:#f472b6;">${splitSeq}</b>/1)</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:5px; direction:ltr;">
                            <button type="button" onclick="adjustSplitOperationSeq(-1)" class="kinetic-mini-step-btn" title="إنقاص رقم التقسيم">−</button>
                            <input type="number" id="split-operation-seq-input" value="${splitSeq}" min="1" step="1" oninput="onSplitOperationSeqInput(this.value)" style="width:68px; height:34px; text-align:center; font-family:'Outfit',monospace; font-size:1.15rem; font-weight:900; color:#f472b6; background:#020617; border:1.5px solid rgba(236,72,153,0.6); border-radius:8px;">
                            <button type="button" onclick="adjustSplitOperationSeq(1)" class="kinetic-mini-step-btn" title="زيادة رقم التقسيم">+</button>
                        </div>
                    </div>

                    <div style="background:rgba(2,6,23,0.65); border:1px solid rgba(148,163,184,0.25); padding:9px 14px; border-radius:12px; display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <span style="font-size:0.78rem; color:#94a3b8; font-weight:800; display:block;">👤 العميل وطريقة الدفع:</span>
                            <span style="font-size:1.02rem; font-weight:900; color:#f8fafc;">${inv.customerName || 'عميل نقدي'}</span>
                        </div>
                        <span style="background:rgba(168,85,247,0.18); border:1px solid rgba(192,132,252,0.4); color:#d8b4fe; padding:4px 10px; border-radius:8px; font-size:0.82rem; font-weight:900;">${inv.paymentMethod === 'credit' ? '✍️ آجل' : '💵 نقدي'}</span>
                    </div>

                    <div style="background:rgba(16,185,129,0.08); border:1.5px solid rgba(16,185,129,0.35); padding:9px 14px; border-radius:12px; display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <span style="font-size:0.78rem; color:#a7f3d0; font-weight:800; display:block;">💰 إجمالي الإذن المرجعي:</span>
                            <span style="font-size:1.2rem; font-weight:900; color:#10b981; font-family:'Outfit',monospace;">${(parseFloat(inv.grandTotal || inv.total || 0)).toLocaleString()} ${currency}</span>
                        </div>
                        <button type="button" onclick="window.previewCurrentSplitVouchersBeforeSave()" class="kinetic-glow-arm kinetic-arm-cyan" style="padding:6px 12px; border-radius:9px; font-size:0.78rem; font-weight:900; cursor:pointer;" title="طباعة الجزئين / جميع الأقسام معاً">🖨️ طباعة الجزئين</button>
                    </div>
                </div>

                <!-- World-Class Unit Mode Switcher + Section Actions Bar -->
                <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; background:rgba(15,23,42,0.92); border:1.5px solid rgba(99,102,241,0.35); padding:8px 14px; border-radius:12px;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <span style="font-size:0.84rem; font-weight:900; color:#e2e8f0;">🎛️ نظام التقسيم بالوحدات (كما في التسليم):</span>
                        <button type="button" onclick="window.setSplitUnitDisplayMode('both')" style="padding:5px 12px; border-radius:8px; font-size:0.8rem; font-weight:900; cursor:pointer; border:1.5px solid ${uMode === 'both' ? '#38bdf8' : 'rgba(255,255,255,0.12)'}; background:${uMode === 'both' ? 'linear-gradient(135deg, rgba(56,189,248,0.28), rgba(99,102,241,0.28))' : 'rgba(2,6,23,0.6)'}; color:${uMode === 'both' ? '#fff' : '#94a3b8'};">📦 + 🔢 الوحدتان معاً (الأساسية والفرعية)</button>
                        <button type="button" onclick="window.setSplitUnitDisplayMode('primary')" style="padding:5px 12px; border-radius:8px; font-size:0.8rem; font-weight:900; cursor:pointer; border:1.5px solid ${uMode === 'primary' ? '#10b981' : 'rgba(255,255,255,0.12)'}; background:${uMode === 'primary' ? 'rgba(16,185,129,0.25)' : 'rgba(2,6,23,0.6)'}; color:${uMode === 'primary' ? '#6ee7b7' : '#94a3b8'};">📦 بالوحدة الأساسية فقط</button>
                        <button type="button" onclick="window.setSplitUnitDisplayMode('base')" style="padding:5px 12px; border-radius:8px; font-size:0.8rem; font-weight:900; cursor:pointer; border:1.5px solid ${uMode === 'base' ? '#a855f7' : 'rgba(255,255,255,0.12)'}; background:${uMode === 'base' ? 'rgba(168,85,247,0.25)' : 'rgba(2,6,23,0.6)'}; color:${uMode === 'base' ? '#e9d5ff' : '#94a3b8'};">🔢 بالوحدة الفرعية فقط</button>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <div id="split-generated-numbers-preview" style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                            ${previewBadges}
                        </div>
                        <button type="button" onclick="distributeSplitEqually()" class="btn-action kinetic-glow-arm kinetic-arm-cyan" style="width:auto; padding:5px 10px; font-size:0.78rem; font-weight:900; border-radius:8px;" title="توزيع الكميات بالتساوي على جميع الأقسام">
                            ⚖️ بالتساوي (${S})
                        </button>
                        <button type="button" onclick="window.applySplitRatioPreset(0.7)" class="btn-action kinetic-glow-arm" style="width:auto; padding:5px 10px; font-size:0.78rem; font-weight:900; border-radius:8px; background:rgba(245,158,11,0.16); border:1px solid rgba(251,191,36,0.45); color:#fde68a;" title="توزيع سريع 70% للقسم الأول و30% للمتبقي">
                            📊 70% / 30%
                        </button>
                        <button type="button" onclick="window.separateHeavyItemsSplit()" class="btn-action kinetic-glow-arm" style="width:auto; padding:5px 10px; font-size:0.78rem; font-weight:900; border-radius:8px; background:rgba(168,85,247,0.16); border:1px solid rgba(192,132,252,0.45); color:#e9d5ff;" title="فصل الأصناف الثقيلة (معامل تحويل أو وزن كبير) في القسم الأول وبقية الأصناف في القسم الثاني">
                            🏋️ فصل الثقيل
                        </button>
                        <button type="button" onclick="window.separateSplitItemsIntoSections()" class="btn-action kinetic-glow-arm" style="width:auto; padding:5px 10px; font-size:0.78rem; font-weight:900; border-radius:8px; background:rgba(236,72,153,0.16); border:1px solid rgba(244,114,182,0.45); color:#fbcfe8;" title="وضع كل صنف في فاتورة فرعية مستقلة">
                            📦 كل صنف بقسم
                        </button>
                        <button type="button" onclick="addSplitSection()" class="btn-action kinetic-glow-arm kinetic-arm-emerald" style="width:auto; padding:5px 12px; font-size:0.8rem; font-weight:900; border-radius:8px;">
                            ➕ قسم (${S + 1})
                        </button>
                    </div>
                </div>

                <div id="split-live-balance-bar" style="background:rgba(16,185,129,0.1); border:1.5px solid rgba(16,185,129,0.45); padding:8px 14px; border-radius:12px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; transition:all 0.25s;">
                </div>
            </div>
        `;
    }

    const splitTable = document.querySelector('#split-board-step table.pos-grid');
    if (splitTable) {
        const thead = splitTable.querySelector('thead');
        if (thead) {
            const secHeadersHtml = Array.from({ length: S }, (_, s) => {
                const pal = getSplitPalette(s);
                const secNo = `${rootNo}/${splitSeq}/${s + 1}`;
                const removeBtn = S > 2
                    ? `<button type="button" onclick="removeSplitSection(${s})" title="حذف هذا القسم" style="background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.45); color:#fca5a5; border-radius:6px; padding:1px 6px; font-size:0.7rem; cursor:pointer; margin-right:6px;">✕</button>`
                    : '';
                return `<th style="text-align:center; min-width:255px; color:${pal.color} !important; border-bottom:2px solid ${pal.color};">
                    <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                        <div>✂️ تقسيم ${pal.title} (أساسي / فرعي) ${removeBtn}</div>
                        <span class="split-col-header-no" data-sec-idx="${s}" style="font-family:'Outfit',monospace; font-size:0.78rem; background:${pal.bg}; border:1px solid ${pal.border}; padding:1px 8px; border-radius:99px; color:#fff;">#${secNo}</span>
                    </div>
                </th>`;
            }).join('');

            thead.innerHTML = `
                <tr>
                    <th style="text-align:right; min-width:185px;">📦 الصنف ومعادلة الوحدات</th>
                    <th style="text-align:center; width:145px;">📊 الكمية القابلة للتقسيم</th>
                    <th style="text-align:left; width:110px;">💰 السعر</th>
                    ${secHeadersHtml}
                    <th style="text-align:center; width:155px;">⚖️ حالة التوازن</th>
                </tr>
            `;
        }
    }

    const tbody = document.getElementById('split-items-body');
    if (tbody) {
        tbody.innerHTML = '';
        (inv.items || []).forEach((item, idx) => {
            const meta = window.resolveSplitItemUnitMeta(item);
            const remQty = meta.deliverableQty;
            const remBaseQty = +(remQty * meta.factor).toFixed(2);
            const draftArr = (inv._splitDraftQtys && inv._splitDraftQtys[idx]) ? inv._splitDraftQtys[idx] : [remQty, 0];
            const showPriBox = (uMode === 'both' || uMode === 'primary');
            const showSubBox = (uMode === 'both' || uMode === 'base');

            const secCellsHtml = Array.from({ length: S }, (_, s) => {
                const pal = getSplitPalette(s);
                const val = draftArr[s] !== undefined ? draftArr[s] : 0;
                const subVal = +(val * meta.factor).toFixed(2);
                const isLastSec = (s === S - 1);
                return `
                    <td style="text-align:center; background:${pal.bg}; padding:8px !important;">
                        <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
                            <!-- Box 1: التقسيم بالوحدة الأساسية -->
                            <div style="display:${showPriBox ? 'flex' : 'none'}; flex-direction:column; gap:4px; width:100%; background:rgba(2,6,23,0.68); border:1.5px solid ${pal.border}; border-radius:10px; padding:6px 8px; box-shadow:0 0 10px ${pal.glow};">
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px;">
                                    <span style="font-size:0.74rem; font-weight:900; color:#6ee7b7;">📦 بالـ ${meta.primaryUnit} ${isLastSec ? '<small style="color:#94a3b8;">(متبقي)</small>' : ''}</span>
                                    <div style="display:flex; gap:3px;">
                                        <button type="button" onclick="setSplitSectionHalfQty(${idx}, ${s})" style="background:rgba(56,189,248,0.16); border:1px solid rgba(56,189,248,0.4); color:#7dd3fc; border-radius:5px; padding:1px 6px; font-size:0.68rem; font-weight:900; cursor:pointer;" title="نصف الكمية">½</button>
                                        <button type="button" onclick="setSplitSectionFullQty(${idx}, ${s})" style="background:rgba(16,185,129,0.18); border:1px solid rgba(52,211,153,0.45); color:#6ee7b7; border-radius:5px; padding:1px 6px; font-size:0.68rem; font-weight:900; cursor:pointer;" title="كامل الكمية (${remQty} ${meta.primaryUnit})">⚡ الكل</button>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; justify-content:center; gap:5px; direction:ltr;">
                                    <button type="button" class="kinetic-mini-step-btn" style="border-color:${pal.border}; color:${pal.color};" onclick="adjustSplitQty(${idx}, -1, ${s})">−</button>
                                    <input type="number" id="split-qty-s${s}-i${idx}" data-item-idx="${idx}" data-sec-idx="${s}" class="v-input" style="width:102px; text-align:center; font-family:'Outfit',monospace; font-size:1.12rem; font-weight:900; border:1.5px solid #10b981; margin:0; padding:4px; color:#ffffff; background:#020617; border-radius:8px;" value="${val}" min="0" max="${remQty}" step="any" oninput="handleSplitQtyInput(${idx}, ${s}, 'primary')" title="قسّم هنا بالوحدة الأساسية (${meta.primaryUnit})">
                                    <button type="button" class="kinetic-mini-step-btn" style="border-color:${pal.border}; color:${pal.color};" onclick="adjustSplitQty(${idx}, 1, ${s})">+</button>
                                </div>
                            </div>

                            <!-- Box 2: التقسيم بالوحدة الفرعية (مثل التسليم تماماً) -->
                            <div style="display:${showSubBox ? 'flex' : 'none'}; flex-direction:column; gap:4px; width:100%; background:rgba(15,23,42,0.85); border:1.5px solid rgba(168,85,247,0.5); border-radius:10px; padding:6px 8px;">
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:4px;">
                                    <span style="font-size:0.74rem; font-weight:900; color:#c4b5fd;">🔢 بالـ ${meta.baseUnit} <small style="color:#94a3b8;">(×${meta.factor})</small></span>
                                    <div style="display:flex; gap:3px;">
                                        <button type="button" onclick="setSplitSectionHalfQty(${idx}, ${s})" style="background:rgba(168,85,247,0.18); border:1px solid rgba(192,132,252,0.45); color:#e9d5ff; border-radius:5px; padding:1px 6px; font-size:0.68rem; font-weight:900; cursor:pointer;" title="نصف الوحدات الفرعية">½</button>
                                        <button type="button" onclick="setSplitSectionFullQty(${idx}, ${s})" style="background:rgba(99,102,241,0.22); border:1px solid rgba(129,140,248,0.5); color:#c4b5fd; border-radius:5px; padding:1px 6px; font-size:0.68rem; font-weight:900; cursor:pointer;" title="كامل الوحدات الفرعية (${remBaseQty} ${meta.baseUnit})">⚡ الكل (${remBaseQty})</button>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; justify-content:center; gap:5px; direction:ltr;">
                                    <button type="button" class="kinetic-mini-step-btn" style="border-color:rgba(168,85,247,0.55); color:#c4b5fd;" onclick="adjustSplitSubQty(${idx}, -1, ${s})">−</button>
                                    <input type="number" id="split-subqty-s${s}-i${idx}" value="${subVal}" min="0" max="${remBaseQty}" step="any" oninput="handleSplitQtyInput(${idx}, ${s}, 'base')" style="width:102px; text-align:center; font-family:'Outfit',monospace; font-size:1.05rem; font-weight:900; color:#e9d5ff; background:#020617; border:1.5px solid #a855f7; border-radius:8px; padding:4px;" title="قسّم هنا بالوحدة الفرعية (${meta.baseUnit})">
                                    <button type="button" class="kinetic-mini-step-btn" style="border-color:rgba(168,85,247,0.55); color:#c4b5fd;" onclick="adjustSplitSubQty(${idx}, 1, ${s})">+</button>
                                </div>
                            </div>
                        </div>
                    </td>
                `;
            }).join('');

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="text-align:right;">
                    <div style="font-weight:900; font-size:1.04rem; color:#fff;">${meta.name}</div>
                    <div style="display:inline-flex; align-items:center; gap:5px; font-size:0.74rem; color:#38bdf8; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.28); padding:2px 8px; border-radius:6px; font-weight:800; margin-top:4px;">
                        ⚖️ ${meta.tonRefHint}
                    </div>
                </td>
                <td style="text-align:center;">
                    <div style="font-weight:900; font-size:1.15rem; color:#6ee7b7; font-family:'Outfit',monospace;">📦 ${remQty} <span style="font-size:0.8rem;">${meta.primaryUnit}</span></div>
                    <div style="font-size:0.82rem; color:#c4b5fd; font-weight:900; margin-top:2px;">🔢 ⇄ ${remBaseQty.toLocaleString('en-US')} ${meta.baseUnit}</div>
                    ${meta.deliveredQty > 0 ? `<div style="font-size:0.72rem; color:#34d399;">(سُلّم سابقاً: ${meta.deliveredQty})</div>` : ''}
                </td>
                <td style="text-align:left; font-weight:800; font-size:1.02rem; color:#fcd34d; font-family:'Outfit',monospace;">
                    ${meta.price.toLocaleString()} <span style="font-size:0.75rem; color:#94a3b8;">${currency}</span>
                </td>
                ${secCellsHtml}
                <td id="split-row-status-${idx}" style="text-align:center;"></td>
            `;
            tbody.appendChild(tr);
        });
    }

    updateSplitCalculations();
};

window.setSplitSectionFullQty = function(itemIdx, targetSecIdx) {
    if (!activeSplitInvoice) return;
    const S = window.splitSectionsCount || 2;
    const meta = window.resolveSplitItemUnitMeta(activeSplitInvoice.items[itemIdx]);
    for (let s = 0; s < S; s++) {
        const inp = document.getElementById(`split-qty-s${s}-i${itemIdx}`);
        const subInp = document.getElementById(`split-subqty-s${s}-i${itemIdx}`);
        const qVal = (s === targetSecIdx) ? meta.deliverableQty : 0;
        if (inp) inp.value = qVal;
        if (subInp) subInp.value = +(qVal * meta.factor).toFixed(2);
    }
    window.saveCurrentSplitDraftState();
    updateSplitCalculations();
};

window.setSplitSectionHalfQty = function(itemIdx, targetSecIdx) {
    if (!activeSplitInvoice) return;
    const meta = window.resolveSplitItemUnitMeta(activeSplitInvoice.items[itemIdx]);
    const halfPri = +(meta.deliverableQty / 2).toFixed(4);
    const inp = document.getElementById(`split-qty-s${targetSecIdx}-i${itemIdx}`);
    if (inp) {
        inp.value = halfPri;
        handleSplitQtyInput(itemIdx, targetSecIdx, 'primary');
    }
};

function adjustSplitQty(itemIdx, delta, secIdx = 0) {
    if (!activeSplitInvoice) return;
    const item = activeSplitInvoice.items[itemIdx];
    const meta = window.resolveSplitItemUnitMeta(item);
    const maxQty = meta.deliverableQty;
    const inp = document.getElementById(`split-qty-s${secIdx}-i${itemIdx}`);
    if (!inp) return;
    let cur = parseFloat(inp.value) || 0;
    cur = Math.max(0, Math.min(maxQty, +(cur + delta).toFixed(4)));
    inp.value = cur;
    handleSplitQtyInput(itemIdx, secIdx, 'primary');
}

window.adjustSplitSubQty = function(itemIdx, delta, secIdx = 0) {
    if (!activeSplitInvoice) return;
    const item = activeSplitInvoice.items[itemIdx];
    const meta = window.resolveSplitItemUnitMeta(item);
    const maxSub = +(meta.deliverableQty * meta.factor).toFixed(4);
    const subInp = document.getElementById(`split-subqty-s${secIdx}-i${itemIdx}`);
    if (!subInp) return;
    let curSub = parseFloat(subInp.value) || 0;
    curSub = Math.max(0, Math.min(maxSub, +(curSub + delta).toFixed(2)));
    subInp.value = curSub;
    handleSplitQtyInput(itemIdx, secIdx, 'base');
};

function handleSplitQtyInput(itemIdx, changedSecIdx = 0, mode = 'primary') {
    if (!activeSplitInvoice) return;
    const S = window.splitSectionsCount || 2;
    const item = activeSplitInvoice.items[itemIdx];
    const meta = window.resolveSplitItemUnitMeta(item);
    const maxQty = meta.deliverableQty;

    const priInp = document.getElementById(`split-qty-s${changedSecIdx}-i${itemIdx}`);
    const subInp = document.getElementById(`split-subqty-s${changedSecIdx}-i${itemIdx}`);
    if (!priInp) return;

    let newPriVal = 0;
    if (mode === 'base' && subInp) {
        let subVal = parseFloat(subInp.value) || 0;
        const maxSub = +(maxQty * meta.factor).toFixed(4);
        if (subVal < 0) subVal = 0;
        if (subVal > maxSub) subVal = maxSub;
        subInp.value = subVal;
        newPriVal = meta.factor > 0 ? +(subVal / meta.factor).toFixed(4) : subVal;
        priInp.value = newPriVal;
    } else {
        newPriVal = parseFloat(priInp.value);
        if (isNaN(newPriVal) || newPriVal < 0) newPriVal = 0;
        if (newPriVal > maxQty) {
            newPriVal = maxQty;
            priInp.value = newPriVal;
        }
        if (subInp) {
            subInp.value = +(newPriVal * meta.factor).toFixed(2);
        }
    }

    if (S === 2) {
        const otherSec = changedSecIdx === 0 ? 1 : 0;
        const otherPriInp = document.getElementById(`split-qty-s${otherSec}-i${itemIdx}`);
        const otherSubInp = document.getElementById(`split-subqty-s${otherSec}-i${itemIdx}`);
        const rem = Math.max(0, +(maxQty - newPriVal).toFixed(4));
        if (otherPriInp) otherPriInp.value = rem;
        if (otherSubInp) otherSubInp.value = +(rem * meta.factor).toFixed(2);
    } else if (changedSecIdx < S - 1) {
        let sumOthers = 0;
        for (let s = 0; s < S - 1; s++) {
            const el = document.getElementById(`split-qty-s${s}-i${itemIdx}`);
            sumOthers += el ? (parseFloat(el.value) || 0) : 0;
        }
        if (sumOthers > maxQty) {
            const prevOthers = sumOthers - newPriVal;
            newPriVal = Math.max(0, +(maxQty - prevOthers).toFixed(4));
            priInp.value = newPriVal;
            if (subInp) subInp.value = +(newPriVal * meta.factor).toFixed(2);
            sumOthers = maxQty;
        }
        const lastSec = S - 1;
        const lastPriInp = document.getElementById(`split-qty-s${lastSec}-i${itemIdx}`);
        const lastSubInp = document.getElementById(`split-subqty-s${lastSec}-i${itemIdx}`);
        const remForLast = Math.max(0, +(maxQty - sumOthers).toFixed(4));
        if (lastPriInp) lastPriInp.value = remForLast;
        if (lastSubInp) lastSubInp.value = +(remForLast * meta.factor).toFixed(2);
    }

    window.saveCurrentSplitDraftState();
    updateSplitCalculations();
}

function updateSplitCalculations() {
    if (!activeSplitInvoice) return;
    const inv = activeSplitInvoice;
    const S = window.splitSectionsCount || 2;
    const rootNo = window.getRootInvoiceNo(inv);
    const splitSeq = window.currentSplitOperationSeq || 1;
    const currency = inv.currency || 'SDG';

    const previewContainer = document.getElementById('split-generated-numbers-preview');
    if (previewContainer) {
        previewContainer.innerHTML = Array.from({ length: S }, (_, s) => {
            const pal = getSplitPalette(s);
            return `<span style="background:${pal.bg}; border:1px solid ${pal.border}; color:${pal.color}; padding:3px 10px; border-radius:99px; font-family:'Outfit',monospace; font-weight:900; font-size:0.88rem; box-shadow:0 0 12px ${pal.glow};">#${rootNo}/${splitSeq}/${s + 1}</span>`;
        }).join(' <span style="color:#94a3b8; font-weight:900;">+</span> ');
    }
    document.querySelectorAll('#split-board-step .split-col-header-no').forEach(el => {
        const s = parseInt(el.dataset.secIdx, 10) || 0;
        el.textContent = `#${rootNo}/${splitSeq}/${s + 1}`;
    });

    const secSubtotals = new Array(S).fill(0);
    const secTotalQtys = new Array(S).fill(0);
    let totalDeliverableAllItems = 0;
    let totalAllocatedAllItems = 0;
    let allRowsBalanced = true;

    (inv.items || []).forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const maxQty = meta.deliverableQty;
        totalDeliverableAllItems += maxQty;

        let rowSum = 0;
        for (let s = 0; s < S; s++) {
            const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
            const q = inp ? (parseFloat(inp.value) || 0) : 0;
            rowSum += q;
            secTotalQtys[s] += q;
            secSubtotals[s] += q * meta.price;
        }
        totalAllocatedAllItems += rowSum;

        const diff = +(maxQty - rowSum).toFixed(4);
        const statusCell = document.getElementById(`split-row-status-${idx}`);
        if (statusCell) {
            if (Math.abs(diff) <= 0.0005) {
                statusCell.innerHTML = `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(16,185,129,0.16); border:1px solid rgba(52,211,153,0.45); color:#6ee7b7; padding:4px 10px; border-radius:99px; font-size:0.8rem; font-weight:900;">✅ متطابق (${rowSum} ${meta.primaryUnit})</span>`;
            } else {
                allRowsBalanced = false;
                const msg = diff > 0 ? `⚠️ متبقي غير موزع: ${diff}` : `❌ زيادة: ${Math.abs(diff)}`;
                statusCell.innerHTML = `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(239,68,68,0.18); border:1px solid rgba(248,113,113,0.5); color:#fca5a5; padding:4px 10px; border-radius:99px; font-size:0.8rem; font-weight:900;">${msg}</span>`;
            }
        }
    });

    const totalSplitSubtotal = secSubtotals.reduce((a, b) => a + b, 0) || 1;
    const originalDiscount = parseFloat(inv.discount) || 0;
    const originalTax = parseFloat(inv.vatAmount) || 0;

    const balanceBar = document.getElementById('split-live-balance-bar');
    if (balanceBar) {
        const emptySections = secTotalQtys.filter(q => q <= 0.0001).length;
        const isPerfect = allRowsBalanced && emptySections === 0;
        balanceBar.style.background = isPerfect ? 'rgba(16,185,129,0.12)' : 'rgba(245,158,11,0.14)';
        balanceBar.style.borderColor = isPerfect ? 'rgba(52,211,153,0.55)' : 'rgba(251,191,36,0.55)';

        const breakdownPills = secTotalQtys.map((q, s) => {
            const pal = getSplitPalette(s);
            return `<span style="background:rgba(2,6,23,0.7); border:1px solid ${pal.border}; color:${pal.color}; padding:3px 10px; border-radius:8px; font-weight:900; font-size:0.84rem;">#${rootNo}/${splitSeq}/${s + 1}: <strong>${+q.toFixed(3)}</strong></span>`;
        }).join(' + ');

        balanceBar.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span style="font-weight:900; font-size:0.9rem; color:#f8fafc;">⚖️ ميزان التوزيع الذكي:</span>
                <span style="font-size:0.86rem; color:#cbd5e1;">الأصل القابل للتقسيم: <strong style="color:#38bdf8; font-family:monospace; font-size:1rem;">${+totalDeliverableAllItems.toFixed(3)}</strong></span>
                <span style="color:#64748b;">|</span>
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">${breakdownPills}</div>
            </div>
            <div>
                ${isPerfect
                    ? `<span style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; padding:5px 14px; border-radius:99px; font-weight:900; font-size:0.84rem; box-shadow:0 0 15px rgba(16,185,129,0.4);">✅ التوزيع متوازن 100% — جاهز للاعتماد والطباعة</span>`
                    : (emptySections > 0
                        ? `<span style="background:rgba(245,158,11,0.25); border:1px solid #fbbf24; color:#fde68a; padding:5px 14px; border-radius:99px; font-weight:900; font-size:0.84rem;">⚠️ يوجد قسم بكمية صفر — وزّع الكميات على جميع الأقسام</span>`
                        : `<span style="background:rgba(239,68,68,0.25); border:1px solid #f87171; color:#fecaca; padding:5px 14px; border-radius:99px; font-weight:900; font-size:0.84rem;">⚠️ مجموع الأقسام (${+totalAllocatedAllItems.toFixed(3)}) لا يطابق الأصل (${+totalDeliverableAllItems.toFixed(3)})</span>`)
                }
            </div>
        `;
    }

    const cardsContainer = document.getElementById('split-cards-grid-container') || document.getElementById('split-subtotal-a')?.closest('div[style*="grid-template-columns"]');
    if (cardsContainer) {
        cardsContainer.id = 'split-cards-grid-container';
        cardsContainer.innerHTML = Array.from({ length: S }, (_, s) => {
            const pal = getSplitPalette(s);
            const secNo = `${rootNo}/${splitSeq}/${s + 1}`;
            const sub = secSubtotals[s];
            const ratio = totalSplitSubtotal > 0 ? (sub / totalSplitSubtotal) : 0;
            const disc = originalDiscount * ratio;
            const tax = originalTax * ratio;
            const grand = Math.max(0, sub - disc + tax);
            const metaInfo = window._splitSectionMeta[s] || {};
            const recVal = (metaInfo.receiverName ?? (inv.receiverName || inv.customerName || '')).replace(/"/g, '&quot;');
            const trkVal = (metaInfo.truckPlate ?? (inv.truckPlate || '')).replace(/"/g, '&quot;');
            const locVal = (metaInfo.deliveryLocation ?? (inv.deliveryLocation || inv.customerAddress || '')).replace(/"/g, '&quot;');
            const phnVal = (metaInfo.receiverPhone ?? (inv.receiverPhone || inv.customerPhone || inv.phone || '')).replace(/"/g, '&quot;');
            const secWeightKg = (inv.items || []).reduce((wSum, item, idx) => {
                const m = window.resolveSplitItemUnitMeta(item);
                const q = parseFloat(document.getElementById(`split-qty-s${s}-i${idx}`)?.value || 0) || 0;
                const unitKg = parseFloat(item.unitWeightKg || item.weightKg || (m.factor > 1 ? m.factor : 50)) || 50;
                return wSum + (q * unitKg);
            }, 0);
            const secWeightTon = +(secWeightKg / 1000).toFixed(2);
            const maxCapTon = parseFloat(metaInfo.maxCapacityTon) || 5.0;
            const isOverweight = secWeightTon > maxCapTon + 0.001;
            const hasDriverSig = Boolean(metaInfo.driverSignatureDataUrl);

            return `
                <div class="neo-card" style="border:1.5px solid ${isOverweight ? '#ef4444' : pal.border}; background:linear-gradient(145deg, rgba(15,23,42,0.9), ${pal.bg}); padding:10px 14px; border-radius:14px; position:relative; overflow:hidden; margin:0; box-shadow:0 8px 24px rgba(0,0,0,0.3), 0 0 18px ${pal.glow};">
                    <div style="position:absolute; top:0; right:0; width:5px; height:100%; background:${isOverweight ? '#ef4444' : pal.color};"></div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:6px; gap:6px; flex-wrap:wrap;">
                        <h4 style="color:${pal.color}; margin:0; display:flex; align-items:center; gap:6px; font-size:0.95rem; font-weight:900;">
                            <span>📄</span> إذن ${pal.title}
                        </h4>
                        <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                            <button type="button" onclick="window.quickPickDriverForSplitSec(${s})" style="background:rgba(16,185,129,0.16); border:1px solid rgba(52,211,153,0.45); color:#6ee7b7; border-radius:7px; padding:2px 8px; font-size:0.72rem; font-weight:900; cursor:pointer;" title="تعيين سائق وسيارة جاهزة لهذا القسم">🚛 سائق سريع</button>
                            <button type="button" onclick="window.openSplitDriverSignatureModal(${s})" style="background:${hasDriverSig ? 'rgba(16,185,129,0.25)' : 'rgba(56,189,248,0.16)'}; border:1px solid ${hasDriverSig ? '#10b981' : 'rgba(56,189,248,0.45)'}; color:${hasDriverSig ? '#6ee7b7' : '#bae6fd'}; border-radius:7px; padding:2px 8px; font-size:0.72rem; font-weight:900; cursor:pointer;" title="التوقيع الرقمي لسائق هذا القسم">${hasDriverSig ? '✅ موقّع' : '✍️ توقيع السائق'}</button>
                            <button type="button" onclick="window.previewCurrentSplitVouchersBeforeSave(${s})" style="background:rgba(56,189,248,0.16); border:1px solid rgba(56,189,248,0.45); color:#7dd3fc; border-radius:7px; padding:2px 8px; font-size:0.74rem; font-weight:900; cursor:pointer;" title="طباعة هذا الجزء بمفرده (#${secNo})">🖨️ طباعة الجزء (${s + 1})</button>
                            <span style="background:#020617; border:1.5px solid ${pal.border}; color:#ffffff; font-family:'Outfit',monospace; font-weight:900; font-size:0.92rem; padding:2px 10px; border-radius:99px; box-shadow:0 0 10px ${pal.glow};">#${secNo}</span>
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:5px; font-size:0.84rem;">
                        <div style="display:flex; justify-content:space-between; color:#cbd5e1; font-weight:800;">
                            <span>إجمالي الكميات بالقسم:</span>
                            <span style="font-family:'Outfit',monospace; font-weight:900; color:${pal.color}; font-size:0.98rem;">${+secTotalQtys[s].toFixed(3)}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:3px 8px; border-radius:7px; background:${isOverweight ? 'rgba(239,68,68,0.22)' : 'rgba(16,185,129,0.12)'}; border:1px solid ${isOverweight ? '#ef4444' : 'rgba(16,185,129,0.35)'};">
                            <span style="font-size:0.76rem; font-weight:900; color:${isOverweight ? '#fecaca' : '#a7f3d0'};">${isOverweight ? '🚨 تجاوز الحمولة:' : '🛡️ درع الوزن والحمولة:'}</span>
                            <span onclick="window.openSplitWeightShieldModal()" style="cursor:pointer; font-family:'Outfit',monospace; font-size:0.8rem; font-weight:900; color:${isOverweight ? '#fca5a5' : '#6ee7b7'};" title="اضغط لضبط سعة الشاحنة وموازنة الأوزان">${secWeightTon} طن (${Math.round(secWeightKg)} كجم) / حد ${maxCapTon} طن ⚙️</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; color:#cbd5e1; font-weight:700;">
                            <span>الإجمالي قبل الخصم:</span>
                            <span style="font-family:'Outfit',monospace; font-weight:800;">${sub.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${currency}</span>
                        </div>
                        ${(originalDiscount > 0 || originalTax > 0) ? `
                        <div style="display:flex; justify-content:space-between; color:#94a3b8; font-size:0.78rem;">
                            <span>الخصم / الضريبة النسبية:</span>
                            <span style="font-family:monospace;">-${disc.toFixed(2)} | +${tax.toFixed(2)}</span>
                        </div>` : ''}
                        <div style="display:flex; justify-content:space-between; font-weight:900; color:${pal.color}; font-size:1.12rem; border-top:1px solid rgba(255,255,255,0.08); padding-top:4px;">
                            <span>الصافي النهائي:</span>
                            <span style="font-family:'Outfit',monospace;">${grand.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${currency}</span>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-top:6px; padding-top:6px; border-top:1px dashed rgba(255,255,255,0.1);">
                            <input type="text" id="split-sec-receiver-${s}" value="${recVal}" oninput="window._splitSectionMeta[${s}] = Object.assign(window._splitSectionMeta[${s}]||{}, {receiverName: this.value})" placeholder="👤 المستلم / السائق..." style="background:rgba(2,6,23,0.75); border:1px solid rgba(148,163,184,0.3); color:#f8fafc; border-radius:7px; padding:4px 8px; font-size:0.76rem; font-weight:700;">
                            <input type="text" id="split-sec-truck-${s}" value="${trkVal}" oninput="window._splitSectionMeta[${s}] = Object.assign(window._splitSectionMeta[${s}]||{}, {truckPlate: this.value})" placeholder="🚛 رقم السيارة / اللوحة..." style="background:rgba(2,6,23,0.75); border:1px solid rgba(148,163,184,0.3); color:#fcd34d; border-radius:7px; padding:4px 8px; font-size:0.76rem; font-weight:700;">
                            <input type="text" id="split-sec-location-${s}" value="${locVal}" oninput="window._splitSectionMeta[${s}] = Object.assign(window._splitSectionMeta[${s}]||{}, {deliveryLocation: this.value})" placeholder="📍 موقع التسليم / الوجهة..." style="background:rgba(2,6,23,0.75); border:1px solid rgba(148,163,184,0.3); color:#a7f3d0; border-radius:7px; padding:4px 8px; font-size:0.76rem; font-weight:700;">
                            <input type="tel" id="split-sec-phone-${s}" value="${phnVal}" oninput="window._splitSectionMeta[${s}] = Object.assign(window._splitSectionMeta[${s}]||{}, {receiverPhone: this.value})" placeholder="📲 واتساب المستلم..." style="background:rgba(2,6,23,0.75); border:1px solid rgba(148,163,184,0.3); color:#93c5fd; border-radius:7px; padding:4px 8px; font-size:0.76rem; font-weight:700;">
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
}

function cancelCurrentSplit() {
    activeSplitInvoice = null;
    window.activeSplitInvoice = null;
    const boardStep = document.getElementById('split-board-step');
    if (boardStep) boardStep.style.display = 'none';
    window._splitReturnToView = null;
    if (typeof showSalesHub === 'function') {
        showSalesHub();
    }
}

function submitInvoiceSplit(autoPrintVouchers = false) {
    if (!activeSplitInvoice) return;
    window.saveCurrentSplitDraftState();

    const inv = activeSplitInvoice;
    const S = window.splitSectionsCount || 2;
    const rootNo = window.getRootInvoiceNo(inv);
    const parentNo = String(inv.invoiceNo || inv.id || '');
    const splitSeq = Math.max(1, parseInt(window.currentSplitOperationSeq, 10) || window.getNextSplitSequenceNo());

    const sectionItems = Array.from({ length: S }, () => []);
    let mismatchError = null;

    (inv.items || []).forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const maxQty = meta.deliverableQty;
        const origQty = meta.origQty;
        let rowSum = 0;

        for (let s = 0; s < S; s++) {
            const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
            const q = inp ? (parseFloat(inp.value) || 0) : 0;
            rowSum += q;

            if (q > 0) {
                let customBaseS = undefined;
                if (item.customBaseQty !== undefined && origQty > 0) {
                    customBaseS = (parseFloat(item.customBaseQty) || 0) * (q / origQty);
                }
                sectionItems[s].push({
                    ...item,
                    qty: q,
                    quantity: q,
                    deliveredQty: 0,
                    total: +(q * meta.price).toFixed(2),
                    ...(customBaseS !== undefined && { customBaseQty: customBaseS })
                });
            }
        }

        if (Math.abs(maxQty - rowSum) > 0.001) {
            mismatchError = `الصنف "${meta.name}": مجموع الأقسام (${+rowSum.toFixed(3)}) لا يساوي الكمية القابلة للتقسيم (${+maxQty.toFixed(3)} ${meta.primaryUnit}).`;
        }
    });

    if (mismatchError) {
        if (typeof showToast === 'function') showToast('⚠️ ' + mismatchError, 'warning');
        else alert(mismatchError);
        return;
    }

    for (let s = 0; s < S; s++) {
        if (sectionItems[s].length === 0) {
            const msg = `خطأ: القسم رقم (${s + 1}) برقم #${rootNo}/${splitSeq}/${s + 1} فارغ تماماً! يرجى توزيع كمية فيه أو حذفه.`;
            if (typeof showToast === 'function') showToast('⚠️ ' + msg, 'warning');
            else alert(msg);
            return;
        }
    }

    const generatedNumbers = Array.from({ length: S }, (_, s) => `${rootNo}/${splitSeq}/${s + 1}`);
    const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };

    const secSubtotals = sectionItems.map(itemsList => itemsList.reduce((sum, it) => sum + (parseFloat(it.total) || 0), 0));
    const totalSplitSubtotal = secSubtotals.reduce((a, b) => a + b, 0) || 1;

    const originalDiscount = parseFloat(inv.discount) || 0;
    const originalTax = parseFloat(inv.vatAmount) || 0;
    const origCash = parseFloat(inv.cashAmount) || 0;
    const origCredit = parseFloat(inv.creditAmount) || 0;
    const origPaid = parseFloat(inv.paidAmount ?? inv.downPayment ?? 0) || 0;
    const origLabor = parseFloat(inv.laborCost) || 0;
    const origLaborFc = parseFloat(inv.laborCostFc) || 0;
    const origTransport = parseFloat(inv.transportCost) || 0;
    const origTransportFc = parseFloat(inv.transportCostFc) || 0;
    const origAdditions = parseFloat(inv.additionsCost) || 0;
    const origAdditionsFc = parseFloat(inv.additionsCostFc) || 0;
    const nowIso = new Date().toISOString();

    const createdInvoices = generatedNumbers.map((childNo, s) => {
        const sub = +secSubtotals[s].toFixed(2);
        const ratio = totalSplitSubtotal > 0 ? (sub / totalSplitSubtotal) : (1 / S);
        const disc = +(originalDiscount * ratio).toFixed(2);
        const tax = +(originalTax * ratio).toFixed(2);
        const grand = Math.max(0, +(sub - disc + tax).toFixed(2));
        const cashVal = +(origCash * ratio).toFixed(2);
        const creditVal = +(origCredit * ratio).toFixed(2);
        const paidVal = +(origPaid * ratio).toFixed(2);
        const secMeta = window._splitSectionMeta[s] || {};

        return {
            ...inv,
            id: childNo,
            invoiceNo: childNo,
            serial: childNo,
            items: sectionItems[s],
            subTotal: sub,
            discount: disc,
            vatAmount: tax,
            grandTotal: grand,
            total: grand,
            netTotal: grand,
            cashAmount: cashVal,
            creditAmount: creditVal,
            paidAmount: paidVal,
            remainingBalance: Math.max(0, +(grand - paidVal).toFixed(2)),
            bankTransfers: (inv.bankTransfers || []).map(bt => ({ ...bt, amount: +(parseFloat(bt.amount || 0) * ratio).toFixed(2) })),
            cheques: (inv.cheques || []).map(ch => ({ ...ch, amount: +(parseFloat(ch.amount || 0) * ratio).toFixed(2) })),
            laborCost: +(origLabor * ratio).toFixed(2),
            laborCostFc: +(origLaborFc * ratio).toFixed(2),
            transportCost: +(origTransport * ratio).toFixed(2),
            transportCostFc: +(origTransportFc * ratio).toFixed(2),
            additionsCost: +(origAdditions * ratio).toFixed(2),
            additionsCostFc: +(origAdditionsFc * ratio).toFixed(2),
            receiverName: secMeta.receiverName || inv.receiverName || inv.customerName || '',
            truckPlate: secMeta.truckPlate || inv.truckPlate || '',
            deliveryLocation: secMeta.deliveryLocation || inv.deliveryLocation || inv.customerAddress || '',
            receiverPhone: secMeta.receiverPhone || inv.receiverPhone || inv.customerPhone || inv.phone || '',
            driverSignatureDataUrl: secMeta.driverSignatureDataUrl || '',
            maxCapacityTon: parseFloat(secMeta.maxCapacityTon) || 5.0,
            timestamp: nowIso,
            rootInvoice: rootNo,
            parentInvoice: parentNo,
            splitBatchNo: splitSeq,
            splitBranchIndex: s + 1,
            splitTotalBranches: S,
            splitSiblings: generatedNumbers,
            isSplit: true,
            wasSplit: false,
            deleted: false,
            isDelivered: false,
            deliveryStatus: 'pending',
            status: 'pending_delivery',
            deliveryNo: '',
            splitBy: curUser.id,
            splitByName: curUser.fullName || curUser.name,
            createdBy: curUser.id,
            createdByName: curUser.fullName || curUser.name
        };
    });

    const currentStoredSeq = parseInt(localStorage.getItem('erp_sales_split_seq_v1') || '0', 10) || 0;
    if (splitSeq > currentStoredSeq) {
        localStorage.setItem('erp_sales_split_seq_v1', String(splitSeq));
    }

    if (typeof revertSalesInvoiceEffects === 'function') {
        revertSalesInvoiceEffects(parentNo, false);
    }

    let allSales = [];
    try {
        allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    } catch(e) {}
    
    const origIdx = allSales.findIndex(x => String(x.invoiceNo || x.id) === parentNo);
    let archivedOrig = null;
    if (origIdx !== -1) {
        allSales[origIdx].deleted = true;
        allSales[origIdx].wasSplit = true;
        allSales[origIdx].rootInvoice = rootNo;
        allSales[origIdx].splitBatchNo = splitSeq;
        allSales[origIdx].splitTo = generatedNumbers;
        allSales[origIdx].splitAt = nowIso;
        allSales[origIdx].splitBy = curUser.id;
        allSales[origIdx].splitByName = curUser.fullName || curUser.name;
        archivedOrig = allSales[origIdx];
        localStorage.setItem('erp_inv_' + parentNo, JSON.stringify(archivedOrig));
    }
    
    createdInvoices.forEach(cInv => {
        allSales.unshift(cInv);
        localStorage.setItem('erp_inv_' + cInv.invoiceNo, JSON.stringify(cInv));
    });
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_sales_v1', JSON.stringify(allSales));
    ['magaies_local_sales_invoices_v1', 'magaies_sales_invoices_cache'].forEach(cacheKey => {
        try {
            let arr = JSON.parse(localStorage.getItem(cacheKey) || '[]');
            if (!Array.isArray(arr)) arr = [];
            const pIdx = arr.findIndex(x => x && String(x.invoiceNo || x.id) === parentNo);
            if (pIdx !== -1 && archivedOrig) arr[pIdx] = archivedOrig;
            createdInvoices.forEach(cInv => arr.unshift(cInv));
            localStorage.setItem(cacheKey, JSON.stringify(arr));
        } catch (_e) {}
    });

    const applyEffects = (cInv) => {
        const grandAmount = parseFloat(cInv.grandTotal) || 0;
        let creditAddAmount = 0;
        if (cInv.paymentMethod === 'hybrid' || cInv.paymentMethod === 'multipay') {
            creditAddAmount = parseFloat(cInv.creditAmount) || 0;
        } else if (cInv.paymentMethod === 'credit') {
            creditAddAmount = grandAmount;
        }
        if (creditAddAmount > 0 && cInv.customer && cInv.customer !== 'walk-in') {
            let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const cIdx = customers.findIndex(c => c.id === cInv.customer || c.id === cInv.customerId);
            if (cIdx > -1) {
                customers[cIdx].currentBalance = (parseFloat(customers[cIdx].currentBalance) || 0) + creditAddAmount;
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            }
        }

        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        let safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
        let banksMod = false;
        let safesMod = false;

        if (cInv.paymentMethod === 'hybrid' || cInv.paymentMethod === 'multipay') {
            if (cInv.cashAmount > 0 && cInv.paymentAccountId) {
                const bIdx = banks.findIndex(b => b.id === cInv.paymentAccountId);
                if (bIdx > -1) {
                    banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + parseFloat(cInv.cashAmount);
                    banksMod = true;
                } else {
                    const sIdx = safes.findIndex(s => s.id === cInv.paymentAccountId);
                    if (sIdx > -1) {
                        safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) + parseFloat(cInv.cashAmount);
                        safesMod = true;
                    }
                }
            }
            if (Array.isArray(cInv.bankTransfers)) {
                cInv.bankTransfers.forEach(bt => {
                    const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
                    if (bIdx > -1) {
                        banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + (parseFloat(bt.amount) || 0);
                        banksMod = true;
                    } else {
                        const sIdx = safes.findIndex(s => s.id === bt.bankAccountId);
                        if (sIdx > -1) {
                            safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) + (parseFloat(bt.amount) || 0);
                            safesMod = true;
                        }
                    }
                });
            }
        } else if (cInv.paymentMethod !== 'credit' && cInv.paymentAccountId) {
            const bIdx = banks.findIndex(b => b.id === cInv.paymentAccountId);
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + grandAmount;
                banksMod = true;
            } else {
                const sIdx = safes.findIndex(s => s.id === cInv.paymentAccountId);
                if (sIdx > -1) {
                    safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) + grandAmount;
                    safesMod = true;
                }
            }
        }

        if (banksMod) localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        if (safesMod) localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
    };

    createdInvoices.forEach(applyEffects);

    const docsToSync = [
        ...(archivedOrig ? [Object.assign({}, archivedOrig, { isEdit: true, deleted: 1, wasSplit: true })] : []),
        ...createdInvoices
    ];
    const enqueueSplitDocOffline = (doc) => {
        if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
            window.MagaiesOffline.enqueue('sales_split', doc, doc.invoiceNo || doc.id);
        } else {
            try {
                const q = JSON.parse(localStorage.getItem('ds_offline_sync_queue_v1') || '[]');
                q.push({ action: 'save_sales_invoice', payload: doc, ts: Date.now() });
                localStorage.setItem('ds_offline_sync_queue_v1', JSON.stringify(q.slice(-80)));
            } catch (_e) {}
        }
    };

    if (navigator.onLine) {
        docsToSync.forEach(doc => {
            const ctrl = new AbortController();
            const tId = setTimeout(() => ctrl.abort(), 4500);
            fetch('api/mirror_sync.php?action=save_sales_invoice', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(doc),
                signal: ctrl.signal
            }).then(r => {
                clearTimeout(tId);
                if (!r.ok) enqueueSplitDocOffline(doc);
            }).catch(() => {
                clearTimeout(tId);
                enqueueSplitDocOffline(doc);
            });
        });
    } else {
        docsToSync.forEach(enqueueSplitDocOffline);
    }

    ['elsajana_erp_sync', 'magaies_sales_sync_channel', 'magaies_erp_sync_channel'].forEach(chName => {
        try {
            const ch = new BroadcastChannel(chName);
            ch.postMessage({ type: 'SALES_INVOICE_SPLIT', parentNo, generatedNumbers, ts: Date.now() });
            ch.close();
        } catch (_e) {}
    });
    const syncPill = document.getElementById('split-live-sync-pill');
    if (syncPill) {
        syncPill.textContent = `⚡ متزامن (${new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' })})`;
    }

    if (window.logSystemAction) {
        window.logSystemAction('تقسيم فاتورة/إذن مبيعات', 'المبيعات والتسليم', parentNo, `تم تقسيم الإذن #${parentNo} (المرجع الأصلي #${rootNo} - عملية تقسيم #${splitSeq}) إلى: ${generatedNumbers.join(' ، ')}`);
    }

    if (typeof window.playGdnSuccessBeep === 'function') {
        window.playGdnSuccessBeep();
    }

    if (typeof showToast === 'function') {
        showToast(`✅ تم تقسيم الإذن #${parentNo} بنجاح إلى: ${generatedNumbers.join(' و ')} ✂️`, 'success');
    }

    if (window._hubSalesCache) {
        window._hubSalesCache.raw = null;
    }

    if (autoPrintVouchers) {
        window.printSplitVouchersBatch(createdInvoices, rootNo, splitSeq);
    }

    activeSplitInvoice = null;
    window.activeSplitInvoice = null;
    const boardStep = document.getElementById('split-board-step');
    if (boardStep) boardStep.style.display = 'none';
    window._splitReturnToView = null;

    if (typeof showSalesHub === 'function') {
        showSalesHub();
    }
    if (typeof loadHubTransactions === 'function') {
        loadHubTransactions();
    }

    window.showPostSplitSuccessModal(createdInvoices, rootNo, splitSeq);
}

window.showPostSplitSuccessModal = function(createdInvoices, rootNo, splitSeq) {
    if (!Array.isArray(createdInvoices) || createdInvoices.length === 0) return;
    window._lastCreatedSplitVouchers = { list: createdInvoices, rootNo, splitSeq };

    let modal = document.getElementById('post-split-success-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'post-split-success-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }

    const partsCardsHtml = createdInvoices.map((cInv, idx) => {
        const cNo = String(cInv.invoiceNo || cInv.id || '');
        const escCNo = cNo.replace(/'/g, "\\'");
        const pal = getSplitPalette(idx);
        const itemsSummary = (cInv.items || []).map(it => {
            const m = window.resolveSplitItemUnitMeta(it);
            const q = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
            const subQ = +(q * m.factor).toFixed(2);
            return `<div style="font-size:0.84rem; color:#f8fafc; font-weight:800;">📦 ${m.name}: <span style="color:${pal.color};">${q} ${m.primaryUnit}</span> ${m.factor > 1 ? `<span style="color:#c4b5fd;">(🔢 ⇄ ${subQ.toLocaleString('en-US')} ${m.baseUnit})</span>` : ''}</div>`;
        }).join('');

        return `
            <div style="background:rgba(15,23,42,0.9); border:1.5px solid ${pal.border}; border-radius:14px; padding:14px; display:flex; flex-direction:column; gap:8px; box-shadow:0 0 18px ${pal.glow};">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:6px;">
                    <strong style="color:${pal.color}; font-size:1rem;">📄 الجزء (${idx + 1}) من (${createdInvoices.length})</strong>
                    <span style="background:#020617; border:1px solid ${pal.border}; color:#fff; padding:2px 10px; border-radius:99px; font-family:'Outfit',monospace; font-weight:900;">#${cNo}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">${itemsSummary}</div>
                ${(cInv.receiverName || cInv.truckPlate || cInv.deliveryLocation) ? `
                <div style="font-size:0.78rem; color:#94a3b8; background:rgba(2,6,23,0.55); padding:5px 8px; border-radius:8px; border:1px dashed rgba(148,163,184,0.25);">
                    👤 المستلم: <strong style="color:#f8fafc;">${cInv.receiverName || '—'}</strong>
                    ${cInv.truckPlate ? ` | 🚛 <strong style="color:#fcd34d;">${cInv.truckPlate}</strong>` : ''}
                    ${cInv.deliveryLocation ? ` | 📍 <strong style="color:#6ee7b7;">${cInv.deliveryLocation}</strong>` : ''}
                </div>` : ''}
                <div style="font-size:0.9rem; font-weight:900; color:#10b981; font-family:'Outfit',monospace;">💰 الصافي: ${(parseFloat(cInv.grandTotal || cInv.total || 0)).toLocaleString()} ${cInv.currency || 'SDG'}</div>
                <div style="display:flex; gap:6px; margin-top:4px; flex-wrap:wrap;">
                    <button type="button" onclick="window.printSplitVouchersBatch([window._lastCreatedSplitVouchers.list[${idx}]], '${rootNo}', '${splitSeq}')" class="kinetic-glow-arm kinetic-arm-cyan" style="flex:1; padding:7px 10px; border-radius:8px; font-size:0.78rem; font-weight:900; cursor:pointer;">🖨️ طباعة (${idx + 1})</button>
                    <button type="button" onclick="window.printSplitDriverAuthPass(window._lastCreatedSplitVouchers.list[${idx}])" class="kinetic-glow-arm" style="flex:1; padding:7px 10px; border-radius:8px; font-size:0.78rem; font-weight:900; cursor:pointer; background:rgba(245,158,11,0.18); border:1.5px solid #fbbf24; color:#fde68a;">📑 تفويض سائق</button>
                    <button type="button" onclick="window.shareSplitVoucherWhatsApp(${idx})" class="kinetic-glow-arm" style="flex:1; padding:7px 10px; border-radius:8px; font-size:0.78rem; font-weight:900; cursor:pointer; background:rgba(34,197,94,0.2); border:1.5px solid rgba(34,197,94,0.55); color:#86efac;">📲 واتساب (${idx + 1})</button>
                    <button type="button" onclick="document.getElementById('post-split-success-modal').style.display='none'; if(typeof window.openDeliveryForInvoiceFromHub==='function') window.openDeliveryForInvoiceFromHub('${escCNo}', 0, 'primary');" class="kinetic-glow-arm kinetic-arm-emerald" style="flex:1; padding:7px 10px; border-radius:8px; font-size:0.78rem; font-weight:900; cursor:pointer;">📦 تسليم (${idx + 1})</button>
                </div>
            </div>
        `;
    }).join('');

    modal.innerHTML = `
        <div style="width:95vw; max-width:860px; max-height:90vh; overflow-y:auto; background:linear-gradient(160deg, #070c18, #0f172a, #170d26); border:2px solid rgba(16,185,129,0.55); border-radius:20px; padding:22px; box-shadow:0 30px 90px rgba(0,0,0,0.92), 0 0 40px rgba(16,185,129,0.22); color:#fff;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="margin:0; font-size:1.28rem; font-weight:900; color:#6ee7b7;">✅ تم تقسيم الفاتورة #${rootNo} بنجاح إلى (${createdInvoices.length}) أجزاء جاهزة للطباعة والتسليم</h3>
                    <p style="margin:4px 0 0; font-size:0.85rem; color:#cbd5e1;">يمكنك طباعة الجزئين معاً في ورقة واحدة، أو طباعة كل جزء بمفرده، أو تسليم أي جزء فوراً:</p>
                </div>
                <button type="button" onclick="document.getElementById('post-split-success-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.5); color:#fca5a5; border-radius:10px; padding:6px 14px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px; margin-bottom:16px;">
                ${partsCardsHtml}
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; border-top:1px solid rgba(255,255,255,0.1); padding-top:14px;">
                <button type="button" onclick="window.printSplitVouchersBatch(window._lastCreatedSplitVouchers.list, '${rootNo}', '${splitSeq}')" class="kinetic-glow-arm kinetic-arm-pink" style="padding:10px 24px; border-radius:11px; font-size:0.96rem; font-weight:900; cursor:pointer;">
                    🖨️ طباعة الجزئين / جميع الأذونات المقسمة معاً الآن
                </button>
                <button type="button" onclick="document.getElementById('post-split-success-modal').style.display='none'" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:10px 20px; border-radius:11px; font-weight:900; cursor:pointer;">
                    👍 تم — العودة للجدول الموحد
                </button>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

window.applySplitRatioPreset = function(firstRatio = 0.7) {
    if (!activeSplitInvoice) return;
    const S = window.splitSectionsCount || 2;
    const r0 = Math.max(0.1, Math.min(0.9, parseFloat(firstRatio) || 0.7));
    (activeSplitInvoice.items || []).forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const maxQty = meta.deliverableQty;
        const firstQty = +(maxQty * r0).toFixed(4);
        const remQty = Math.max(0, +(maxQty - firstQty).toFixed(4));
        const otherCount = Math.max(1, S - 1);
        const perOther = +(remQty / otherCount).toFixed(4);
        let allocated = firstQty;
        for (let s = 0; s < S; s++) {
            const qVal = (s === 0) ? firstQty : (s === S - 1 ? Math.max(0, +(maxQty - allocated).toFixed(4)) : perOther);
            if (s > 0 && s < S - 1) allocated += qVal;
            const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
            const subInp = document.getElementById(`split-subqty-s${s}-i${idx}`);
            if (inp) inp.value = qVal;
            if (subInp) subInp.value = +(qVal * meta.factor).toFixed(2);
        }
    });
    window.saveCurrentSplitDraftState();
    updateSplitCalculations();
    if (typeof showToast === 'function') showToast(`📊 تم توزيع الكميات بنسبة ${Math.round(r0 * 100)}% للقسم الأول والباقي للأقسام الأخرى`, 'info');
};

window.separateHeavyItemsSplit = function() {
    if (!activeSplitInvoice) return;
    const items = activeSplitInvoice.items || [];
    if (items.length === 0) return;
    const S = window.splitSectionsCount || 2;
    items.forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const isHeavy = (meta.factor >= 20) || ((meta.deliverableQty * meta.factor) >= 50);
        const targetSec = isHeavy ? 0 : Math.min(1, S - 1);
        for (let s = 0; s < S; s++) {
            const qVal = (s === targetSec) ? meta.deliverableQty : 0;
            const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
            const subInp = document.getElementById(`split-subqty-s${s}-i${idx}`);
            if (inp) inp.value = qVal;
            if (subInp) subInp.value = +(qVal * meta.factor).toFixed(2);
        }
    });
    window.saveCurrentSplitDraftState();
    updateSplitCalculations();
    if (typeof showToast === 'function') showToast('🏋️ تم فصل الأصناف الثقيلة في القسم (أ) والأصناف الخفيفة في القسم (ب)', 'success');
};

window.separateSplitItemsIntoSections = function() {
    if (!activeSplitInvoice) return;
    const items = activeSplitInvoice.items || [];
    if (items.length <= 1) {
        distributeSplitEqually();
        return;
    }
    const targetS = Math.min(6, Math.max(2, items.length));
    window.splitSectionsCount = targetS;
    activeSplitInvoice._splitDraftQtys = {};
    items.forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const arr = new Array(targetS).fill(0);
        arr[idx % targetS] = meta.deliverableQty;
        activeSplitInvoice._splitDraftQtys[idx] = arr;
    });
    renderWorldClassSplitBoard();
    if (typeof showToast === 'function') showToast(`📦 تم توزيع كل صنف في قسم فرعي مستقل (${targetS} أقسام)`, 'success');
};

window.quickPickDriverForSplitSec = function(secIdx = 0) {
    let presets = [];
    try { presets = JSON.parse(localStorage.getItem('magaies_driver_presets_v1') || '[]'); } catch (_e) {}
    if (!Array.isArray(presets) || presets.length === 0) {
        presets = [
            { driver: 'محمد أحمد (سائق البوابة 1)', plate: 'خ 45812', location: 'المستودع الرئيسي - بوابة 1', phone: '' },
            { driver: 'عبدالرحمن علي (شاحنة النقل)', plate: 'خ 91204', location: 'موقع العميل المباشر', phone: '' },
            { driver: 'عمر حسن (توصيل سريع)', plate: 'خ 33190', location: 'فرع التوزيع', phone: '' }
        ];
    }
    const pick = presets[(secIdx + (window._lastDriverPickOffset || 0)) % presets.length];
    window._lastDriverPickOffset = (window._lastDriverPickOffset || 0) + 1;
    window._splitSectionMeta[secIdx] = Object.assign(window._splitSectionMeta[secIdx] || {}, {
        receiverName: pick.driver,
        truckPlate: pick.plate,
        deliveryLocation: pick.location || ''
    });
    const recEl = document.getElementById(`split-sec-receiver-${secIdx}`);
    const trkEl = document.getElementById(`split-sec-truck-${secIdx}`);
    const locEl = document.getElementById(`split-sec-location-${secIdx}`);
    if (recEl) recEl.value = pick.driver;
    if (trkEl) trkEl.value = pick.plate;
    if (locEl && pick.location) locEl.value = pick.location;
    if (typeof showToast === 'function') showToast(`🚛 تم تعيين "${pick.driver}" (${pick.plate}) للقسم رقم (${secIdx + 1})`, 'info');
};

window.shareSplitVoucherWhatsApp = function(idx = 0) {
    const store = window._lastCreatedSplitVouchers;
    if (!store || !Array.isArray(store.list) || !store.list[idx]) return;
    const cInv = store.list[idx];
    const cNo = String(cInv.invoiceNo || cInv.id || '');
    const lines = [
        `📄 *إشعار فاتورة / إذن تسليم مجزأ رقم #${cNo}*`,
        `🔗 المرجع الأصلي: #${store.rootNo} (الجزء ${idx + 1} من ${store.list.length})`,
        `👤 العميل: ${cInv.customerName || 'عميل نقدي'}`,
        `🚚 المستلم / السائق: ${cInv.receiverName || cInv.customerName || '—'}`,
        ...(cInv.truckPlate ? [`🚛 رقم السيارة: ${cInv.truckPlate}`] : []),
        ...(cInv.deliveryLocation ? [`📍 موقع التسليم: ${cInv.deliveryLocation}`] : []),
        `──────────────────`,
        `📦 *الأصناف المعتمدة بهذا الجزء:*`
    ];
    (cInv.items || []).forEach((it, i) => {
        const m = window.resolveSplitItemUnitMeta(it);
        const q = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
        const subQ = +(q * m.factor).toFixed(2);
        lines.push(`${i + 1}. ${m.name}: *${q} ${m.primaryUnit}*${m.factor > 1 ? ` (⇄ ${subQ} ${m.baseUnit})` : ''}`);
    });
    lines.push(`──────────────────`);
    lines.push(`💰 *الصافي النهائي لهذا الجزء:* ${(parseFloat(cInv.grandTotal || cInv.total || 0)).toLocaleString()} ${cInv.currency || 'SDG'}`);
    const rawPhone = String(cInv.receiverPhone || cInv.customerPhone || cInv.phone || '').replace(/[^\d+]/g, '');
    const waUrl = rawPhone
        ? `https://wa.me/${rawPhone.replace(/^\+/, '')}?text=${encodeURIComponent(lines.join('\n'))}`
        : `https://wa.me/?text=${encodeURIComponent(lines.join('\n'))}`;
    window.open(waUrl, '_blank', 'noopener,noreferrer');
};

window.printSplitVouchersBatch = function(vouchersList, rootNo, splitSeq) {
    if (!Array.isArray(vouchersList) || vouchersList.length === 0) return;
    const totalBranches = vouchersList.length;

    const pagesHtml = vouchersList.map((inv, idx) => {
        const invNo = String(inv.invoiceNo || inv.id || '');
        const qrTargetUrl = `${window.location.origin}${window.location.pathname}?action=delivery&qr_deliver=${encodeURIComponent(invNo)}&auto_deduct=1`;
        const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=4&data=${encodeURIComponent(qrTargetUrl)}`;
        const custName = inv.customerName || 'عميل نقدي';
        const receiver = inv.receiverName || custName;
        const truck = inv.truckPlate || '—';

        const rowsHtml = (inv.items || []).map((it, i) => {
            const m = window.resolveSplitItemUnitMeta(it);
            const q = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
            const subQ = +(q * m.factor).toFixed(2);
            return `
                <tr>
                    <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:800;">${i + 1}</td>
                    <td style="padding:8px; border:1px solid #0f172a; font-weight:900;">${m.name}</td>
                    <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:900; font-size:1.1rem;">${q} ${m.primaryUnit}</td>
                    <td style="padding:8px; border:1px solid #0f172a; text-align:center; font-weight:800;">${m.hasMultiUnits ? `${subQ.toLocaleString('en-US')} ${m.baseUnit}` : `${q} ${m.baseUnit}`}</td>
                </tr>
            `;
        }).join('');

        return `
            <div class="voucher-card" style="${idx < totalBranches - 1 ? 'page-break-after:always; margin-bottom:28px; border-bottom:3px dashed #64748b; padding-bottom:24px;' : ''}">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #0f172a; padding-bottom:12px; margin-bottom:14px;">
                    <div>
                        <div style="display:inline-block; background:#0f172a; color:#fff; padding:4px 12px; border-radius:6px; font-weight:900; font-size:0.85rem; margin-bottom:6px;">
                            ✂️ إذن صرف مقسّم — القسم (${idx + 1}) من (${totalBranches})
                        </div>
                        <h2 style="margin:0; font-size:1.45rem;">🚚 إذن تسليم وصرف بضاعة رقم: <span style="font-family:monospace; direction:ltr; display:inline-block;">#${invNo}</span></h2>
                        <p style="margin:6px 0 0; font-weight:900; font-size:0.95rem;">
                            📌 الإذن المرجعي الأصلي: <span style="font-family:monospace;">#${rootNo}</span> &nbsp;|&nbsp;
                            🔢 رقم عملية التقسيم: <span style="font-family:monospace;">#${splitSeq}</span> &nbsp;|&nbsp;
                            🏷️ القسم الفرعي: <span style="font-family:monospace;">${idx + 1}/${totalBranches}</span>
                        </p>
                        <p style="margin:4px 0 0; font-weight:800;">👤 العميل: ${custName} &nbsp;|&nbsp; المستلم: ${receiver} &nbsp;|&nbsp; 🚛 المركبة: ${truck}</p>
                        <p style="margin:4px 0 0; font-weight:800;">📅 التاريخ: ${inv.date || new Date().toISOString().slice(0, 10)}</p>
                    </div>
                    <div style="text-align:center;">
                        <img src="${qrImgUrl}" style="width:115px; height:115px; border:2px solid #000; border-radius:10px;">
                        <div style="font-size:0.7rem; font-weight:900; margin-top:3px;">امسح للصرف الفوري (#${invNo})</div>
                    </div>
                </div>
                <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                    <thead>
                        <tr style="background:#064e3b; color:#fff;">
                            <th style="padding:9px; border:1px solid #000; width:50px;">#</th>
                            <th style="padding:9px; border:1px solid #000; text-align:right;">الصنف</th>
                            <th style="padding:9px; border:1px solid #000;">الكمية المعتمدة بهذا القسم</th>
                            <th style="padding:9px; border:1px solid #000;">المعادل بالوحدات الفرعية</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:32px; font-weight:900;">
                    <div>توقيع أمين المستودع: ________________</div>
                    <div style="text-align:center;">
                        <div>توقيع السائق / المستلم:</div>
                        ${inv.driverSignatureDataUrl ? `<img src="${inv.driverSignatureDataUrl}" style="max-height:55px; border-bottom:1.5px solid #000; margin-top:4px;">` : `________________`}
                    </div>
                    <div>ختم بوابة الخروج: ________________</div>
                </div>
            </div>
        `;
    }).join('');

    const w = window.open('', '_blank', 'width=940,height=780');
    if (!w) return;
    w.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>أذونات الصرف المقسمة - أصل #${rootNo} - تقسيم #${splitSeq}</title>
            <style>
                body { font-family: 'Cairo', 'Tajawal', sans-serif; padding: 20px; color: #000; }
                body.compact-two-per-page .voucher-card { page-break-after: auto !important; margin-bottom: 18px !important; padding-bottom: 14px !important; }
                body.thermal-80mm { width: 78mm; margin: 0 auto; padding: 4mm; font-size: 11px; }
                body.thermal-80mm h2 { font-size: 13px !important; }
                body.thermal-80mm img { width: 80px !important; height: 80px !important; }
                @media print { .no-print { display: none !important; } }
            </style>
        </head>
        <body class="compact-two-per-page">
            <div class="no-print" style="margin-bottom:16px; padding:10px 16px; background:#0f172a; color:#fff; border-radius:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <strong>🖨️ جاهز للطباعة: ${totalBranches} أذونات صرف مقسمة من الإذن المرجعي #${rootNo} (عملية تقسيم #${splitSeq})</strong>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button onclick="document.body.classList.remove('thermal-80mm'); document.body.classList.toggle('compact-two-per-page')" style="background:#3b82f6; color:#fff; border:none; padding:8px 14px; border-radius:8px; font-weight:900; cursor:pointer;">📄 A4: الجزئين في ورقة / صفحات مستقلة</button>
                    <button onclick="document.body.classList.toggle('thermal-80mm')" style="background:#8b5cf6; color:#fff; border:none; padding:8px 14px; border-radius:8px; font-weight:900; cursor:pointer;">🧾 إيصال كاشير حراري 80mm</button>
                    <button onclick="window.print()" style="background:#10b981; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:900; cursor:pointer;">🖨️ طباعة الآن</button>
                </div>
            </div>
            ${pagesHtml}
            <script>window.onload = () => { setTimeout(() => window.print(), 350); };<\/script>
        </body>
        </html>
    `);
    w.document.close();
};

window.previewCurrentSplitVouchersBeforeSave = function(onlySecIdx = null) {
    if (!activeSplitInvoice) return;
    window.saveCurrentSplitDraftState();
    const inv = activeSplitInvoice;
    const S = window.splitSectionsCount || 2;
    const rootNo = window.getRootInvoiceNo(inv);
    const splitSeq = Math.max(1, parseInt(window.currentSplitOperationSeq, 10) || window.getNextSplitSequenceNo());

    const draftVouchers = [];
    for (let s = 0; s < S; s++) {
        if (onlySecIdx !== null && onlySecIdx !== s) continue;
        const secItems = [];
        (inv.items || []).forEach((item, idx) => {
            const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
            const q = inp ? (parseFloat(inp.value) || 0) : ((inv._splitDraftQtys?.[idx]?.[s]) || 0);
            if (q > 0) {
                secItems.push({ ...item, qty: q, quantity: q });
            }
        });
        const secMeta = window._splitSectionMeta?.[s] || {};
        draftVouchers.push({
            ...inv,
            invoiceNo: `${rootNo}/${splitSeq}/${s + 1}`,
            id: `${rootNo}/${splitSeq}/${s + 1}`,
            items: secItems,
            receiverName: secMeta.receiverName || inv.receiverName || inv.customerName || '',
            truckPlate: secMeta.truckPlate || inv.truckPlate || ''
        });
    }
    if (draftVouchers.length > 0) {
        window.printSplitVouchersBatch(draftVouchers, rootNo, splitSeq);
    }
};

window.printSplitFamilyVouchers = function(invIdentifier) {
    const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
    const target = allSales.find(x => String(x.invoiceNo || x.id) === String(invIdentifier));
    const rootNo = target ? window.getRootInvoiceNo(target) : window.getRootInvoiceNo(invIdentifier);
    if (!rootNo) return;
    const familyBranches = allSales.filter(x => !x.deleted && !x.wasSplit && window.getRootInvoiceNo(x) === rootNo && String(x.invoiceNo || x.id) !== rootNo);
    const listToPrint = familyBranches.length > 0 ? familyBranches : (target ? [target] : []);
    if (listToPrint.length > 0) {
        const splitSeq = listToPrint[0].splitBatchNo || 1;
        window.printSplitVouchersBatch(listToPrint, rootNo, splitSeq);
    }
};

window.openSplitGenealogyModal = function(invIdentifier, isGuideDemo = false) {
    const allSales = (typeof window.getMergedSalesInvoicesForSplit === 'function')
        ? window.getMergedSalesInvoicesForSplit()
        : JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
    const target = allSales.find(x => String(x.invoiceNo || x.id) === String(invIdentifier));
    const rootNo = target ? window.getRootInvoiceNo(target) : (window.getRootInvoiceNo(invIdentifier) || '100');
    if (!rootNo) return;

    const family = allSales.filter(x => window.getRootInvoiceNo(x) === rootNo);
    const rootDoc = family.find(x => String(x.invoiceNo || x.id) === rootNo) || target || window.activeSplitInvoice || { customerName: 'عميل تجريبي لمعاينة شجرة التقسيم', currency: 'SDG' };
    let activeBranches = family.filter(x => !x.deleted && !x.wasSplit && String(x.invoiceNo || x.id) !== rootNo);
    const archivedSplits = family.filter(x => x.wasSplit);

    if (activeBranches.length === 0 && (isGuideDemo || (window.activeSplitInvoice && window.activeSplitInvoice._isGuideDemo))) {
        activeBranches = [
            {
                invoiceNo: `${rootNo}/1/1`,
                id: `${rootNo}/1/1`,
                splitBatchNo: 1,
                splitBranchIndex: 1,
                splitTotalBranches: 2,
                grandTotal: 50000,
                currency: 'SDG',
                deliveryStatus: 'pending',
                items: [{ name: 'سكر أبيض عبوة 50 كجم', qty: 5, primaryUnit: 'جوال', baseUnit: 'كيلو', conversionFactor: 50 }]
            },
            {
                invoiceNo: `${rootNo}/1/2`,
                id: `${rootNo}/1/2`,
                splitBatchNo: 1,
                splitBranchIndex: 2,
                splitTotalBranches: 2,
                grandTotal: 50000,
                currency: 'SDG',
                deliveryStatus: 'delivered',
                isDelivered: true,
                deliveryNo: 'GDN-100-1',
                items: [{ name: 'سكر أبيض عبوة 50 كجم', qty: 5, primaryUnit: 'جوال', baseUnit: 'كيلو', conversionFactor: 50 }]
            }
        ];
    }

    let modal = document.getElementById('split-genealogy-tree-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'split-genealogy-tree-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }

    const branchesRows = activeBranches.map((b, i) => {
        const bNo = String(b.invoiceNo || b.id);
        const escBNo = bNo.replace(/'/g, "\\'");
        const info = window.parseSplitInvoiceParts(b);
        const isDel = b.isDelivered === true || b.deliveryStatus === 'delivered' || b.deliveryStatus === 'Admin_Closed';
        const isPartial = !isDel && b.deliveryStatus === 'partial';
        const itemsTxt = (b.items || []).map(it => {
            const m = window.resolveSplitItemUnitMeta(it);
            return `${m.name}: <b>${it.qty ?? it.quantity ?? 0} ${m.primaryUnit}</b>`;
        }).join(' ، ');
        const statusBadge = isDel
            ? `<span style="background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#6ee7b7; padding:3px 10px; border-radius:99px; font-size:0.76rem; font-weight:900;">✅ تم الصرف (${b.deliveryNo || 'GDN'})</span>`
            : (isPartial
                ? `<span style="background:rgba(245,158,11,0.2); border:1px solid #f59e0b; color:#fcd34d; padding:3px 10px; border-radius:99px; font-size:0.76rem; font-weight:900;">🌗 تسليم جزئي</span>`
                : `<span style="background:rgba(56,189,248,0.18); border:1px solid #38bdf8; color:#7dd3fc; padding:3px 10px; border-radius:99px; font-size:0.76rem; font-weight:900;">⏳ جاهز للصرف</span>`);

        return `
            <tr style="border-bottom:1px solid rgba(255,255,255,0.08);">
                <td style="padding:10px; font-family:'Outfit',monospace; font-weight:900; color:#38bdf8; font-size:1.05rem;">#${bNo}</td>
                <td style="padding:10px; text-align:center;"><span style="background:rgba(236,72,153,0.18); border:1px solid rgba(244,114,182,0.45); color:#f9a8d4; padding:2px 9px; border-radius:99px; font-weight:900; font-size:0.8rem;">تقسيم #${info.splitBatchNo} • قسم ${info.splitBranchIndex}/${info.splitTotalBranches}</span></td>
                <td style="padding:10px; color:#f8fafc; font-size:0.88rem;">${itemsTxt}</td>
                <td style="padding:10px; text-align:center; font-family:monospace; font-weight:900; color:#10b981;">${(parseFloat(b.grandTotal || b.total || 0)).toLocaleString()} ${b.currency || 'SDG'}</td>
                <td style="padding:10px; text-align:center;">${statusBadge}</td>
                <td style="padding:10px; text-align:center;">
                    <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                        ${!isDel ? `
                        <button onclick="document.getElementById('split-genealogy-tree-modal').style.display='none'; if(typeof openWorldClassDeliveryModal==='function'){openDeliveryHub(); setTimeout(()=>openWorldClassDeliveryModal('${escBNo}'),60);}" class="kinetic-glow-arm kinetic-arm-emerald" style="padding:4px 10px; border-radius:7px; font-size:0.76rem; font-weight:900; cursor:pointer;">📦 صرف</button>
                        <button onclick="document.getElementById('split-genealogy-tree-modal').style.display='none'; window.openSplitForInvoiceFromDelivery('${escBNo}');" class="kinetic-glow-arm kinetic-arm-pink" style="padding:4px 10px; border-radius:7px; font-size:0.76rem; font-weight:900; cursor:pointer;">✂️ تقسيم إضافي</button>
                        ` : ''}
                        <button onclick="window.printSplitDriverAuthPass('${escBNo}')" style="background:rgba(245,158,11,0.18); border:1px solid #fbbf24; color:#fde68a; padding:4px 10px; border-radius:7px; font-size:0.76rem; font-weight:900; cursor:pointer;">📑 تفويض</button>
                        <button onclick="if(typeof printOfficialGdnGatePass==='function') printOfficialGdnGatePass('${escBNo}');" class="kinetic-glow-arm kinetic-arm-cyan" style="padding:4px 10px; border-radius:7px; font-size:0.76rem; font-weight:900; cursor:pointer;">🖨️ طباعة</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    modal.innerHTML = `
        <div style="width:95vw; max-width:1050px; max-height:90vh; overflow-y:auto; background:linear-gradient(160deg, #070c18, #0f172a, #170d26); border:2px solid rgba(236,72,153,0.55); border-radius:20px; padding:22px; box-shadow:0 30px 90px rgba(0,0,0,0.9), 0 0 40px rgba(236,72,153,0.25); color:#fff;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(236,72,153,0.3); padding-bottom:12px; margin-bottom:16px; flex-wrap:wrap; gap:8px;">
                <div>
                    <h3 style="margin:0; font-size:1.3rem; font-weight:900; color:#f8fafc;">🌳 شجرة النسب والتتبع للإذن المرجعي الأصلي <span style="color:#38bdf8; font-family:'Outfit',monospace;">#${rootNo}</span></h3>
                    <p style="margin:4px 0 0; font-size:0.84rem; color:#cbd5e1;">العميل: <b>${rootDoc?.customerName || 'عميل نقدي'}</b> • عدد الأقسام الفرعية النشطة: <b>${activeBranches.length}</b> • عمليات التقسيم المسجلة: <b>${archivedSplits.length}</b></p>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button type="button" onclick="window.safeReverseAndMergeSplitFamily('${rootNo.replace(/'/g, "\\'")}')" style="background:rgba(16,185,129,0.2); border:1.5px solid #34d399; color:#6ee7b7; border-radius:10px; padding:6px 14px; font-weight:900; cursor:pointer;">🔄 دمج / التراجع الآمن عن التجزئة</button>
                    <button onclick="document.getElementById('split-genealogy-tree-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.5); color:#fca5a5; border-radius:10px; padding:6px 14px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
                </div>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:rgba(15,23,42,0.9); color:#94a3b8; font-size:0.82rem;">
                        <th style="padding:10px; text-align:right;">رقم الإذن الفرعي</th>
                        <th style="padding:10px; text-align:center;">رقم التقسيم والفرع</th>
                        <th style="padding:10px; text-align:right;">الأصناف والكميات</th>
                        <th style="padding:10px; text-align:center;">القيمة</th>
                        <th style="padding:10px; text-align:center;">حالة التسليم</th>
                        <th style="padding:10px; text-align:center;">إجراءات مباشرة</th>
                    </tr>
                </thead>
                <tbody>
                    ${branchesRows || `<tr><td colspan="6" style="padding:24px; text-align:center; color:#94a3b8;">لا توجد فروع نشطة</td></tr>`}
                </tbody>
            </table>
        </div>
    `;
    modal.style.display = 'flex';
};

window.previewGuidePostSplitSuccessModal = function() {
    if (window._lastCreatedSplitVouchers && Array.isArray(window._lastCreatedSplitVouchers.list) && window._lastCreatedSplitVouchers.list.length > 0) {
        window.showPostSplitSuccessModal(window._lastCreatedSplitVouchers.list, window._lastCreatedSplitVouchers.rootNo, window._lastCreatedSplitVouchers.splitSeq);
        return;
    }
    const baseInv = window.activeSplitInvoice || { invoiceNo: '100', customerName: 'عميل تجريبي', currency: 'SDG' };
    const rootNo = window.getRootInvoiceNo ? window.getRootInvoiceNo(baseInv) : '100';
    const demoChildren = [
        {
            invoiceNo: `${rootNo}/1/1`,
            id: `${rootNo}/1/1`,
            customerName: baseInv.customerName || 'عميل تجريبي',
            receiverName: 'محمد أحمد (سائق البوابة 1)',
            truckPlate: 'خ 45812',
            deliveryLocation: 'المستودع الرئيسي - بوابة 1',
            grandTotal: 50000,
            currency: baseInv.currency || 'SDG',
            items: [{ name: 'سكر أبيض عبوة 50 كجم', qty: 5, primaryUnit: 'جوال', baseUnit: 'كيلو', conversionFactor: 50 }]
        },
        {
            invoiceNo: `${rootNo}/1/2`,
            id: `${rootNo}/1/2`,
            customerName: baseInv.customerName || 'عميل نقدي',
            receiverName: 'عبدالرحمن علي (شاحنة النقل)',
            truckPlate: 'خ 91204',
            deliveryLocation: 'موقع العميل المباشر',
            grandTotal: 50000,
            currency: baseInv.currency || 'SDG',
            items: [{ name: 'سكر أبيض عبوة 50 كجم', qty: 5, primaryUnit: 'جوال', baseUnit: 'كيلو', conversionFactor: 50 }]
        }
    ];
    window.showPostSplitSuccessModal(demoChildren, rootNo, 1);
};

window.speakSplitMaleVoice = function(text) {
    if (!text) return;
    if (typeof window.speakSmartGuideMale === 'function') {
        window.speakSmartGuideMale(text);
        return;
    }
    try {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            window.speechSynthesis.resume();
            const u = new SpeechSynthesisUtterance(text);
            u.lang = 'ar-SA';
            u.pitch = 0.88;
            u.rate = 0.96;
            const voices = window.speechSynthesis.getVoices() || [];
            const arMale = voices.find(v => /ar/i.test(v.lang) && /(hamed|naayf|maged|tarik|male|osama|shakir)/i.test(v.name)) || voices.find(v => /ar/i.test(v.lang));
            if (arMale) u.voice = arMale;
            window.speechSynthesis.speak(u);
        }
    } catch (_e) {}
};

window.readSplitSummaryAloud = function() {
    if (!window.activeSplitInvoice && typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
        window.ensureDemoOrFirstSplitBoardLoaded();
    }
    const inv = window.activeSplitInvoice;
    if (!inv) return;
    const S = window.splitSectionsCount || 2;
    const rootNo = window.getRootInvoiceNo ? window.getRootInvoiceNo(inv) : (inv.invoiceNo || '100');
    const splitSeq = window.currentSplitOperationSeq || 1;
    const currency = inv.currency || 'SDG';
    const parts = [];
    for (let s = 0; s < S; s++) {
        let qSum = 0;
        let subSum = 0;
        (inv.items || []).forEach((it, idx) => {
            const m = window.resolveSplitItemUnitMeta(it);
            const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
            const q = inp ? (parseFloat(inp.value) || 0) : 0;
            qSum += q;
            subSum += q * m.price;
        });
        const rec = document.getElementById(`split-sec-receiver-${s}`)?.value || window._splitSectionMeta?.[s]?.receiverName || 'بدون سائق محدد';
        parts.push(`القسم رقم ${s + 1} برقم فرعي ${rootNo} على ${splitSeq} على ${s + 1}، إجمالي كمياته ${+qSum.toFixed(2)} وحدة، بقيمة ${Math.round(subSum).toLocaleString('en-US')} ${currency}، والمستلم ${rec}`);
    }
    const msg = `ملخص تجزئة الفاتورة رقم ${rootNo} إلى ${S} أقسام: ${parts.join('؛ و')}.`;
    window.speakSplitMaleVoice(msg);
    if (typeof showToast === 'function') showToast('🔊 جاري قراءة ملخص التجزئة وحصة كل سائق بالصوت الرجالي...', 'info');
};

window.executeSplitVoiceCommand = function(rawText) {
    const cmd = window.normalizeSplitArabicText(rawText || '');
    if (!cmd) return;
    const statusEl = document.getElementById('split-voice-cmd-status');
    const setStatus = (txt) => { if (statusEl) statusEl.innerHTML = txt; };

    if (!window.activeSplitInvoice && typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
        window.ensureDemoOrFirstSplitBoardLoaded();
    }
    if (cmd.includes('بالتساوي') || cmd.includes('نصف') || cmd.includes('تساوي')) {
        distributeSplitEqually();
        setStatus('✅ تم تنفيذ الأمر: توزيع الكميات بالتساوي على جميع الأقسام');
        window.speakSplitMaleVoice('تم توزيع الكميات بالتساوي على جميع الأقسام بنجاح.');
    } else if (cmd.includes('سبعين') || cmd.includes('70')) {
        window.applySplitRatioPreset(0.7);
        setStatus('✅ تم تنفيذ الأمر: توزيع 70% للقسم الأول و30% للمتبقي');
        window.speakSplitMaleVoice('تم توزيع الكميات بنسبة سبعين بالمائة للقسم الأول وثلاثين بالمائة للقسم الثاني.');
    } else if (cmd.includes('ثقيل')) {
        window.separateHeavyItemsSplit();
        setStatus('✅ تم تنفيذ الأمر: فصل الأصناف الثقيلة في القسم (أ)');
        window.speakSplitMaleVoice('تم فصل الأصناف الثقيلة في القسم الأول والأصناف الخفيفة في القسم الثاني.');
    } else if (cmd.includes('قسم جديد') || cmd.includes('اضف قسم') || cmd.includes('اضافه قسم')) {
        addSplitSection();
        setStatus('✅ تم تنفيذ الأمر: إضافة قسم فرعي جديد');
        window.speakSplitMaleVoice('تمت إضافة قسم فرعي جديد إلى لوحة التجزئة.');
    } else if (cmd.includes('سائق') || cmd.includes('سياره')) {
        window.quickPickDriverForSplitSec(0);
        window.quickPickDriverForSplitSec(1);
        setStatus('✅ تم تنفيذ الأمر: تعيين السائقين والسيارات للأقسام');
        window.speakSplitMaleVoice('تم تعيين السائقين وأرقام اللوحات ومواقع التسليم للأقسام الفرعية.');
    } else if (cmd.includes('ملخص') || cmd.includes('اقرا') || cmd.includes('قراءه')) {
        window.readSplitSummaryAloud();
        setStatus('🔊 جاري قراءة ملخص التجزئة...');
    } else if (cmd.includes('اعتماد') || cmd.includes('حفظ')) {
        setStatus('✅ جاري اعتماد التقسيم...');
        window.speakSplitMaleVoice('جاري التحقق من التوازن واعتماد تقسيم الفاتورة.');
        submitInvoiceSplit(false);
    } else if (cmd.includes('اشرح') || cmd.includes('مرشد')) {
        const m = document.getElementById('split-voice-assistant-modal');
        if (m) m.style.display = 'none';
        if (typeof window.openSmartGuide === 'function') window.openSmartGuide('sales-split');
    } else {
        setStatus(`💡 تم سماع: "${rawText}" — اختر أحد الأوامر السريعة بالأسفل أو أعد النطق`);
    }
};

window.openSplitVoiceAssistantModal = function() {
    let modal = document.getElementById('split-voice-assistant-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'split-voice-assistant-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.86); backdrop-filter:blur(10px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }
    modal.innerHTML = `
        <div style="width:94vw; max-width:560px; background:linear-gradient(160deg,#0f172a,#020617); border:2px solid #fbbf24; border-radius:18px; padding:20px; color:#fff; box-shadow:0 24px 70px rgba(0,0,0,0.9);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(251,191,36,0.3); padding-bottom:10px; margin-bottom:14px;">
                <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#fde68a;">🎙️ المساعد الصوتي التفاعلي للتجزئة السريعة</h3>
                <button type="button" onclick="document.getElementById('split-voice-assistant-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:1px solid #f87171; color:#fecaca; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
            </div>
            <p style="margin:0 0 12px; font-size:0.86rem; color:#cbd5e1;">انطق أمر التجزئة بالعربية أو اضغط على أي أمر فوري لتنفيذه وسماع التأكيد بالصوت الرجالي:</p>
            <div style="display:flex; gap:8px; margin-bottom:12px;">
                <input type="text" id="split-voice-cmd-input" placeholder="مثال: بالتساوي، سبعين ثلاثين، قسم جديد، سائق سريع، ملخص..." style="flex:1; background:#020617; border:1.5px solid #38bdf8; color:#fff; border-radius:10px; padding:9px 12px; font-weight:800;" onkeydown="if(event.key==='Enter') window.executeSplitVoiceCommand(this.value)">
                <button type="button" onclick="window.startSplitSpeechRecognition()" style="background:linear-gradient(135deg,#f59e0b,#d97706); border:none; color:#fff; border-radius:10px; padding:0 14px; font-weight:900; cursor:pointer;">🎤 تحدث</button>
                <button type="button" onclick="window.executeSplitVoiceCommand(document.getElementById('split-voice-cmd-input').value)" style="background:linear-gradient(135deg,#10b981,#059669); border:none; color:#fff; border-radius:10px; padding:0 14px; font-weight:900; cursor:pointer;">تنفيذ</button>
            </div>
            <div id="split-voice-cmd-status" style="background:rgba(16,185,129,0.12); border:1px solid rgba(52,211,153,0.4); color:#6ee7b7; padding:8px 12px; border-radius:10px; font-size:0.84rem; font-weight:900; margin-bottom:12px;">🎙️ جاهز لاستقبال أمرك الصوتي أو النقرة المباشرة</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <button type="button" onclick="window.executeSplitVoiceCommand('بالتساوي')" style="background:rgba(56,189,248,0.16); border:1px solid #38bdf8; color:#7dd3fc; padding:8px; border-radius:9px; font-weight:900; cursor:pointer;">⚖️ «وزّع بالتساوي»</button>
                <button type="button" onclick="window.executeSplitVoiceCommand('سبعين ثلاثين')" style="background:rgba(245,158,11,0.16); border:1px solid #fbbf24; color:#fde68a; padding:8px; border-radius:9px; font-weight:900; cursor:pointer;">📊 «سبعين ثلاثين (70/30)»</button>
                <button type="button" onclick="window.executeSplitVoiceCommand('فصل الثقيل')" style="background:rgba(168,85,247,0.16); border:1px solid #c084fc; color:#e9d5ff; padding:8px; border-radius:9px; font-weight:900; cursor:pointer;">🏋️ «افصل الأصناف الثقيلة»</button>
                <button type="button" onclick="window.executeSplitVoiceCommand('سائق سريع')" style="background:rgba(16,185,129,0.16); border:1px solid #34d399; color:#6ee7b7; padding:8px; border-radius:9px; font-weight:900; cursor:pointer;">🚛 «عيّن السائقين والسيارات»</button>
                <button type="button" onclick="window.executeSplitVoiceCommand('قسم جديد')" style="background:rgba(236,72,153,0.16); border:1px solid #f472b6; color:#fbcfe8; padding:8px; border-radius:9px; font-weight:900; cursor:pointer;">➕ «أضف قسماً جديداً»</button>
                <button type="button" onclick="window.executeSplitVoiceCommand('اقرأ الملخص')" style="background:rgba(34,197,94,0.16); border:1px solid #4ade80; color:#86efac; padding:8px; border-radius:9px; font-weight:900; cursor:pointer;">🔊 «اقرأ ملخص التجزئة»</button>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

window.startSplitSpeechRecognition = function() {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    const statusEl = document.getElementById('split-voice-cmd-status');
    if (!SR) {
        if (statusEl) statusEl.textContent = '⚠️ المتصفح الحالي لا يدعم المايك المباشر — اكتب الأمر أو اضغط أحد الأزرار الفورية بالأسفل.';
        return;
    }
    try {
        const rec = new SR();
        rec.lang = 'ar-SA';
        rec.interimResults = false;
        if (statusEl) statusEl.textContent = '🎤 تفضل بالتحدث الآن (مثلاً: بالتساوي، سبعين ثلاثين، سائق سريع)...';
        rec.onresult = (e) => {
            const txt = e.results?.[0]?.[0]?.transcript || '';
            const inp = document.getElementById('split-voice-cmd-input');
            if (inp) inp.value = txt;
            window.executeSplitVoiceCommand(txt);
        };
        rec.onerror = () => {
            if (statusEl) statusEl.textContent = '💡 لم نتمكن من التقاط الصوت، يمكنك الضغط على أي زر أمر فوري بالأسفل.';
        };
        rec.start();
    } catch (_e) {}
};

window.openSplitHandsOnSimulatorModal = function(stepNo = 1) {
    let modal = document.getElementById('split-hands-on-sim-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'split-hands-on-sim-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.86); backdrop-filter:blur(10px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }
    const steps = [
        {
            no: 1,
            title: 'الخطوة 1 من 3: فتح لوحة التجزئة وتوليد الأرقام التسلسلية الهرمية',
            desc: 'سنقوم الآن بفتح فاتورة تجريبية (#100) وتوليد قسمين فرعيين برقم (#100/1/1 و #100/1/2) تلقائياً أمامك.',
            btnText: '🚀 نفّذ الخطوة 1 عملياً وانتقل للخطوة 2',
            run: () => {
                if (typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') window.ensureDemoOrFirstSplitBoardLoaded();
                window.speakSplitMaleVoice('الخطوة الأولى مكتملة: تم فتح لوحة التجزئة وتوليد الأرقام الفرعية المتسلسلة مئة على واحد على واحد ومئة على واحد على اثنين.');
                window.openSplitHandsOnSimulatorModal(2);
            }
        },
        {
            no: 2,
            title: 'الخطوة 2 من 3: التوزيع المتوازن بالوحدة الأساسية والفرعية',
            desc: 'الآن سنقوم بتوزيع الكمية بالتساوي (50% / 50%) بين القسمين مع موازنة الوحدات الكبرى والفرعية بنسبة 100%.',
            btnText: '⚖️ نفّذ التوزيع المتوازن وانتقل للخطوة 3',
            run: () => {
                if (typeof distributeSplitEqually === 'function') distributeSplitEqually();
                window.speakSplitMaleVoice('الخطوة الثانية مكتملة: تم توزيع الكميات بالتساوي بالوحدة الأساسية والفرعية وأصبح ميزان التطابق مئة بالمائة.');
                window.openSplitHandsOnSimulatorModal(3);
            }
        },
        {
            no: 3,
            title: 'الخطوة 3 من 3: تعيين السائقين ومعاينة شاشة الأذونات الوليدة',
            desc: 'الآن سنعيّن سائقاً ورقم سيارة وموقع تسليم مستقل لكل قسم، ونفتح شاشة الأذونات الوليدة الجاهزة للطباعة والواتساب.',
            btnText: '🏆 إتمام التدريب العملي ومعاينة الأذونات الوليدة',
            run: () => {
                window.quickPickDriverForSplitSec(0);
                window.quickPickDriverForSplitSec(1);
                modal.style.display = 'none';
                window.previewGuidePostSplitSuccessModal();
                window.speakSplitMaleVoice('مبارك لك! لقد أتقنت دورة تجزئة الفواتير وتعيين السائقين وإصدار الأذونات الفرعية بنسبة مئة بالمائة.');
            }
        }
    ];
    const cur = steps[Math.max(0, Math.min(2, stepNo - 1))];
    window._runSplitSimCurrentStep = cur.run;
    modal.innerHTML = `
        <div style="width:94vw; max-width:560px; background:linear-gradient(160deg,#1e1b4b,#0f172a); border:2px solid #c084fc; border-radius:18px; padding:22px; color:#fff; box-shadow:0 24px 70px rgba(0,0,0,0.92);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(192,132,252,0.3); padding-bottom:10px; margin-bottom:14px;">
                <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#e9d5ff;">🎯 محاكي التدريب العملي لتجزئة الفواتير (3 خطوات)</h3>
                <button type="button" onclick="document.getElementById('split-hands-on-sim-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:1px solid #f87171; color:#fecaca; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:14px;">
                ${[1, 2, 3].map(n => `<div style="flex:1; text-align:center; padding:6px; border-radius:8px; font-weight:900; font-size:0.82rem; background:${n === cur.no ? '#9333ea' : (n < cur.no ? 'rgba(16,185,129,0.25)' : 'rgba(255,255,255,0.06)')}; border:1px solid ${n === cur.no ? '#e9d5ff' : 'rgba(255,255,255,0.15)'};">${n < cur.no ? '✅' : '🎯'} خطوة ${n}</div>`).join('')}
            </div>
            <div style="background:rgba(2,6,23,0.65); border:1.5px solid rgba(192,132,252,0.4); border-radius:12px; padding:14px; margin-bottom:16px;">
                <div style="font-weight:900; font-size:1rem; color:#fde047; margin-bottom:6px;">${cur.title}</div>
                <div style="font-size:0.88rem; color:#e2e8f0; line-height:1.6;">${cur.desc}</div>
            </div>
            <div style="display:flex; justify-content:space-between; gap:10px;">
                <button type="button" onclick="if(typeof window._runSplitSimCurrentStep==='function') window._runSplitSimCurrentStep()" style="flex:1; background:linear-gradient(135deg,#10b981,#059669); border:1.5px solid #6ee7b7; color:#fff; padding:11px 16px; border-radius:11px; font-weight:900; font-size:0.94rem; cursor:pointer;">${cur.btnText}</button>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

window.openSplitDrillDownDocModal = function(invIdentifier) {
    const allSales = window.getMergedSalesInvoicesForSplit();
    const inv = allSales.find(x => String(x.invoiceNo || x.id) === String(invIdentifier)) || window.activeSplitInvoice;
    if (!inv) return;
    const invNo = String(inv.invoiceNo || inv.id || '');
    const rootNo = window.getRootInvoiceNo ? window.getRootInvoiceNo(inv) : invNo;
    let customersList = [];
    try { customersList = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]'); } catch (_e) {}
    const custObj = customersList.find(c => String(c.id) === String(inv.customer || inv.customerId) || c.name === inv.customerName);
    const custBal = custObj ? (parseFloat(custObj.currentBalance || custObj.balance || 0) || 0) : 0;

    let modal = document.getElementById('split-drilldown-doc-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'split-drilldown-doc-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }

    const itemsRows = (inv.items || []).map((it, i) => {
        const m = window.resolveSplitItemUnitMeta(it);
        return `
            <tr style="border-bottom:1px solid rgba(255,255,255,0.08);">
                <td style="padding:8px; text-align:center; font-weight:900;">${i + 1}</td>
                <td style="padding:8px; font-weight:900;">${m.name}</td>
                <td style="padding:8px; text-align:center; font-weight:900; color:#38bdf8;">${m.origQty} ${m.primaryUnit}</td>
                <td style="padding:8px; text-align:center; font-weight:900; color:#6ee7b7;">${m.deliverableQty} ${m.primaryUnit} ${m.factor > 1 ? `(${m.deliverableBaseQty} ${m.baseUnit})` : ''}</td>
                <td style="padding:8px; text-align:left; font-family:monospace; font-weight:900;">${m.price.toLocaleString()}</td>
                <td style="padding:8px; text-align:left; font-family:monospace; font-weight:900; color:#10b981;">${(m.deliverableQty * m.price).toLocaleString()}</td>
            </tr>
        `;
    }).join('');

    modal.innerHTML = `
        <div style="width:94vw; max-width:780px; max-height:90vh; overflow-y:auto; background:linear-gradient(160deg,#0f172a,#020617); border:2px solid #38bdf8; border-radius:18px; padding:22px; color:#fff; box-shadow:0 24px 80px rgba(0,0,0,0.92);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(56,189,248,0.3); padding-bottom:10px; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
                <div>
                    <h3 style="margin:0; font-size:1.2rem; font-weight:900; color:#7dd3fc;">🔍 أصل المستند والبصمة الرقابية — فاتورة رقم #${invNo}</h3>
                    <div style="font-size:0.8rem; color:#94a3b8; font-weight:800; margin-top:4px;">📌 الأصل المرجعي: #${rootNo} | 📅 التاريخ: ${inv.date || '—'} | 🛡️ المستخدم: ${inv.splitByName || inv.createdByName || 'المسؤول'} | 💻 الجهاز: ${navigator.platform || 'Web ERP'}</div>
                </div>
                <button type="button" onclick="document.getElementById('split-drilldown-doc-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:1px solid #f87171; color:#fecaca; border-radius:8px; padding:5px 14px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:8px; margin-bottom:14px;">
                <div style="background:rgba(2,6,23,0.65); border:1px solid rgba(56,189,248,0.35); border-radius:10px; padding:8px 12px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">👤 العميل:</div>
                    <strong style="font-size:0.95rem; color:#fff;">${inv.customerName || 'عميل نقدي'}</strong>
                </div>
                <div style="background:rgba(2,6,23,0.65); border:1px solid rgba(16,185,129,0.35); border-radius:10px; padding:8px 12px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">📊 رصيد العميل الحي:</div>
                    <strong style="font-size:0.95rem; color:#6ee7b7; font-family:monospace;">${custBal.toLocaleString()} ${inv.currency || 'SDG'}</strong>
                </div>
                <div style="background:rgba(2,6,23,0.65); border:1px solid rgba(236,72,153,0.35); border-radius:10px; padding:8px 12px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">💳 طريقة الدفع:</div>
                    <strong style="font-size:0.95rem; color:#f9a8d4;">${inv.paymentMethod === 'credit' ? '✍️ آجل (ذمم)' : '💵 نقدي / بنكي'}</strong>
                </div>
                <div style="background:rgba(2,6,23,0.65); border:1px solid rgba(251,191,36,0.35); border-radius:10px; padding:8px 12px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">💰 صافي الفاتورة:</div>
                    <strong style="font-size:1.05rem; color:#fde047; font-family:monospace;">${(parseFloat(inv.grandTotal || inv.total || 0)).toLocaleString()} ${inv.currency || 'SDG'}</strong>
                </div>
            </div>
            <table style="width:100%; border-collapse:collapse; margin-bottom:14px;">
                <thead>
                    <tr style="background:rgba(15,23,42,0.9); color:#94a3b8; font-size:0.8rem;">
                        <th style="padding:8px;">#</th>
                        <th style="padding:8px; text-align:right;">الصنف</th>
                        <th style="padding:8px;">الكمية الأصلية</th>
                        <th style="padding:8px;">المتبقي للتجزئة</th>
                        <th style="padding:8px; text-align:left;">السعر</th>
                        <th style="padding:8px; text-align:left;">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>${itemsRows}</tbody>
            </table>
            <div style="display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                <button type="button" onclick="document.getElementById('split-drilldown-doc-modal').style.display='none'; startInvoiceSplit('${invNo.replace(/'/g, "\\'")}');" class="kinetic-glow-arm kinetic-arm-pink" style="padding:9px 18px; border-radius:10px; font-weight:900; cursor:pointer;">✂️ فتح لوحة تجزئة هذه الفاتورة الآن</button>
                <button type="button" onclick="window.openSplitGenealogyModal('${invNo.replace(/'/g, "\\'")}')" class="kinetic-glow-arm kinetic-arm-cyan" style="padding:9px 18px; border-radius:10px; font-weight:900; cursor:pointer;">🌳 عرض شجرة النسب</button>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

window.exportSplitInvoicesToExcel = function() {
    const list = window._lastFilteredSplitInvoices || window.getMergedSalesInvoicesForSplit();
    if (!Array.isArray(list) || list.length === 0) {
        if (typeof showToast === 'function') showToast('⚠️ لا توجد فواتير لتصديرها', 'warning');
        return;
    }
    const headers = ['رقم الفاتورة', 'الأصل المرجعي', 'التاريخ', 'العميل', 'طريقة الدفع', 'الأصناف المتبقية', 'الصافي المالي', 'العملة', 'المستخدم'];
    const rowsCsv = list.map(inv => {
        const itemsTxt = (inv.items || []).map(it => {
            const m = window.resolveSplitItemUnitMeta(it);
            return `${m.name} (${m.deliverableQty} ${m.primaryUnit})`;
        }).join(' | ');
        return [
            inv.invoiceNo || inv.id || '',
            window.getRootInvoiceNo ? window.getRootInvoiceNo(inv) : '',
            inv.date || '',
            inv.customerName || 'عميل نقدي',
            inv.paymentMethod === 'credit' ? 'آجل' : 'نقدي',
            itemsTxt,
            parseFloat(inv.grandTotal ?? inv.total ?? 0) || 0,
            inv.currency || 'SDG',
            inv.splitByName || inv.createdByName || 'المسؤول'
        ].map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(',');
    });
    const csvContent = '\uFEFF' + [headers.join(','), ...rowsCsv].join('\r\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `split_invoices_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    if (typeof showToast === 'function') showToast('📥 تم تصدير كشف الفواتير القابلة للتجزئة إلى Excel بنجاح', 'success');
};

window.printSplitEligibleReportA4 = function() {
    const list = window._lastFilteredSplitInvoices || window.getMergedSalesInvoicesForSplit();
    let totalVal = 0;
    const rowsHtml = (list || []).map((inv, idx) => {
        const val = parseFloat(inv.grandTotal ?? inv.total ?? 0) || 0;
        totalVal += val;
        const itemsTxt = (inv.items || []).map(it => {
            const m = window.resolveSplitItemUnitMeta(it);
            return `${m.name}: ${m.deliverableQty} ${m.primaryUnit}`;
        }).join(' ، ');
        return `
            <tr>
                <td style="padding:7px; border:1px solid #000; text-align:center; font-weight:800;">${idx + 1}</td>
                <td style="padding:7px; border:1px solid #000; font-family:monospace; font-weight:900;">#${inv.invoiceNo || inv.id}</td>
                <td style="padding:7px; border:1px solid #000;">${inv.date || ''}</td>
                <td style="padding:7px; border:1px solid #000; font-weight:800;">${inv.customerName || 'عميل نقدي'}</td>
                <td style="padding:7px; border:1px solid #000;">${itemsTxt}</td>
                <td style="padding:7px; border:1px solid #000; text-align:left; font-family:monospace; font-weight:900;">${val.toLocaleString()} ${inv.currency || 'SDG'}</td>
            </tr>
        `;
    }).join('');
    const w = window.open('', '_blank', 'width=940,height=760');
    if (!w) return;
    w.document.write(`
        <!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>كشف الفواتير الجاهزة للتجزئة</title>
        <style>body{font-family:'Cairo','Tajawal',sans-serif;padding:20px;color:#000;}table{width:100%;border-collapse:collapse;margin-top:12px;}@media print{.no-print{display:none!important;}}</style></head>
        <body>
            <div class="no-print" style="margin-bottom:12px;"><button onclick="window.print()" style="background:#10b981;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-weight:900;cursor:pointer;">🖨️ طباعة الكشف A4</button></div>
            <h2 style="margin:0 0 6px;">✂️ كشف الفواتير وأذونات التسليم الجاهزة للتجزئة (${(list || []).length} مستند)</h2>
            <div style="font-weight:800; margin-bottom:8px;">📅 تاريخ الاستخراج: ${new Date().toLocaleString('ar-EG')} | 💰 الإجمالي الختامي: ${totalVal.toLocaleString()} SDG</div>
            <table>
                <thead><tr style="background:#e2e8f0;"><th style="padding:8px;border:1px solid #000;">#</th><th style="padding:8px;border:1px solid #000;">رقم الفاتورة</th><th style="padding:8px;border:1px solid #000;">التاريخ</th><th style="padding:8px;border:1px solid #000;">العميل</th><th style="padding:8px;border:1px solid #000;">الأصناف المتبقية للتجزئة</th><th style="padding:8px;border:1px solid #000;">الصافي المالي</th></tr></thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <script>window.onload=()=>{setTimeout(()=>window.print(),300);};<\/script>
        </body></html>
    `);
    w.document.close();
};

window.flushSplitOfflineQueueNow = function(manualClick = false) {
    if (!navigator.onLine) {
        if (manualClick && typeof showToast === 'function') showToast('📡 الجهاز حالياً أوفلاين — ستُرفع التجزئات تلقائياً فور توفر الشبكة ولو لثوانٍ', 'info');
        return;
    }
    try {
        const q = JSON.parse(localStorage.getItem('ds_offline_sync_queue_v1') || '[]');
        if (Array.isArray(q) && q.length > 0) {
            localStorage.setItem('ds_offline_sync_queue_v1', '[]');
            q.forEach(item => {
                if (!item || !item.payload) return;
                const ctrl = new AbortController();
                const tId = setTimeout(() => ctrl.abort(), 4500);
                fetch('api/mirror_sync.php?action=save_sales_invoice', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(item.payload),
                    signal: ctrl.signal
                }).then(() => clearTimeout(tId)).catch(() => clearTimeout(tId));
            });
        }
    } catch (_e) {}
    const syncPill = document.getElementById('split-live-sync-pill');
    if (syncPill) {
        syncPill.textContent = `⚡ متزامن (${new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' })})`;
    }
    if (manualClick) {
        loadSplitInvoices(false);
        if (typeof showToast === 'function') showToast('⚡ تمت المزامنة اللحظية مع السيرفر والأجهزة الأخرى بنجاح', 'success');
    }
};

(function initSplitSubSecondAutoSync() {
    if (window._splitSubSecondAutoSyncBound) return;
    window._splitSubSecondAutoSyncBound = true;

    const refreshSplitIfVisible = () => {
        const splitView = document.getElementById('split-invoice-view');
        const selectStep = document.getElementById('split-select-step');
        if (splitView && splitView.style.display !== 'none' && selectStep && selectStep.style.display !== 'none') {
            loadSplitInvoices(true);
        }
        const syncPill = document.getElementById('split-live-sync-pill');
        if (syncPill) {
            syncPill.textContent = `⚡ متزامن (${new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' })})`;
        }
    };

    window.addEventListener('online', () => {
        window.flushSplitOfflineQueueNow(false);
        refreshSplitIfVisible();
    });

    window.addEventListener('storage', (e) => {
        if (!e.key || ['erp_sales_invoices_v1', 'erp_sales_v1', 'magaies_local_sales_invoices_v1', 'magaies_sales_invoices_cache'].includes(e.key)) {
            refreshSplitIfVisible();
        }
    });

    ['elsajana_erp_sync', 'magaies_sales_sync_channel', 'magaies_erp_sync_channel'].forEach(chName => {
        try {
            const ch = new BroadcastChannel(chName);
            ch.onmessage = () => refreshSplitIfVisible();
        } catch (_e) {}
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') refreshSplitIfVisible();
    });
})();

window.printSplitDriverAuthPass = function(invOrNo) {
    let inv = invOrNo;
    if (typeof invOrNo === 'string') {
        const all = window.getMergedSalesInvoicesForSplit();
        inv = all.find(x => String(x.invoiceNo || x.id) === String(invOrNo));
    }
    if (!inv) return;
    const invNo = String(inv.invoiceNo || inv.id || '');
    const rootNo = window.getRootInvoiceNo ? window.getRootInvoiceNo(inv) : invNo;
    const qrData = `${window.location.origin}${window.location.pathname}?action=delivery&qr_deliver=${encodeURIComponent(invNo)}`;
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=4&data=${encodeURIComponent(qrData)}`;
    const rowsHtml = (inv.items || []).map((it, i) => {
        const m = window.resolveSplitItemUnitMeta(it);
        const q = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
        const subQ = +(q * m.factor).toFixed(2);
        return `<tr><td style="padding:8px;border:1px solid #000;text-align:center;">${i + 1}</td><td style="padding:8px;border:1px solid #000;font-weight:900;">${m.name}</td><td style="padding:8px;border:1px solid #000;text-align:center;font-weight:900;">${q} ${m.primaryUnit}</td><td style="padding:8px;border:1px solid #000;text-align:center;">${m.factor > 1 ? `${subQ} ${m.baseUnit}` : `${q} ${m.baseUnit}`}</td></tr>`;
    }).join('');
    const w = window.open('', '_blank', 'width=860,height=720');
    if (!w) return;
    w.document.write(`
        <!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>سند تفويض استلام جزئي للسائق #${invNo}</title>
        <style>body{font-family:'Cairo','Tajawal',sans-serif;padding:24px;color:#000;border:3px double #0f172a;margin:12px;}table{width:100%;border-collapse:collapse;margin-top:14px;}@media print{.no-print{display:none!important;}}</style></head>
        <body>
            <div class="no-print" style="margin-bottom:14px;"><button onclick="window.print()" style="background:#10b981;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-weight:900;cursor:pointer;">🖨️ طباعة سند التفويض الرسمي</button></div>
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2.5px solid #000;padding-bottom:12px;">
                <div>
                    <h2 style="margin:0;">📑 سند تفويض استلام وخروج شحنة مجزأة رقم: #${invNo}</h2>
                    <div style="font-weight:800;margin-top:6px;">📌 الفاتورة المرجعية الأم: #${rootNo} | 📅 التاريخ: ${inv.date || new Date().toISOString().slice(0, 10)}</div>
                    <div style="font-weight:900;margin-top:6px;">👤 العميل: ${inv.customerName || 'عميل نقدي'} | 🚚 السائق المفوّض: ${inv.receiverName || '________________'}</div>
                    <div style="font-weight:900;margin-top:4px;">🚛 رقم السيارة / اللوحة: ${inv.truckPlate || '________'} | 📍 وجهة التسليم: ${inv.deliveryLocation || '________'}</div>
                </div>
                <div style="text-align:center;"><img src="${qrUrl}" style="width:110px;height:110px;border:2px solid #000;border-radius:8px;"><div style="font-size:11px;font-weight:900;">QR تحقق البوابة</div></div>
            </div>
            <p style="font-weight:800;background:#f1f5f9;padding:8px 12px;border:1px solid #000;border-radius:6px;">بموجب هذا السند يُفوّض السائق الموضح اسمه ورقم مركبته أعلاه باستلام الأصناف المحددة أدناه فقط كجزء فرعي معتمد من الفاتورة #${rootNo}:</p>
            <table><thead><tr style="background:#e2e8f0;"><th style="padding:8px;border:1px solid #000;">#</th><th style="padding:8px;border:1px solid #000;text-align:right;">الصنف</th><th style="padding:8px;border:1px solid #000;">الكمية بالوحدة الكبرى</th><th style="padding:8px;border:1px solid #000;">المعادل بالوحدة الفرعية</th></tr></thead><tbody>${rowsHtml}</tbody></table>
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:36px;font-weight:900;">
                <div style="text-align:center;">
                    <div>توقيع السائق المستلم:</div>
                    ${inv.driverSignatureDataUrl ? `<img src="${inv.driverSignatureDataUrl}" style="max-height:55px;border-bottom:1.5px solid #000;margin-top:4px;">` : `________________`}
                </div>
                <div>اعتماد أمين المستودع: ________________</div>
                <div>ختم أمن البوابة: ________________</div>
            </div>
            <script>window.onload=()=>{setTimeout(()=>window.print(),300);};<\/script>
        </body></html>
    `);
    w.document.close();
};

window.safeReverseAndMergeSplitFamily = function(rootNo) {
    if (!rootNo) return;
    let allSales = [];
    try { allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]'); } catch (_e) {}
    const family = allSales.filter(x => window.getRootInvoiceNo(x) === String(rootNo));
    const rootDoc = family.find(x => String(x.invoiceNo || x.id) === String(rootNo));
    const activeBranches = family.filter(x => !x.deleted && !x.wasSplit && String(x.invoiceNo || x.id) !== String(rootNo));

    if (!rootDoc || activeBranches.length === 0) {
        if (typeof showToast === 'function') showToast('ℹ️ لا توجد فروع مقسمة نشطة قابلة لإعادة الدمج لهذه الفاتورة', 'info');
        return;
    }

    const anyDelivered = activeBranches.some(b => {
        if (b.isDelivered === true || b.deliveryStatus === 'delivered' || b.deliveryStatus === 'partial') return true;
        return (b.items || []).some(it => (parseFloat(it.deliveredQty || 0) || 0) > 0.0001);
    });
    if (anyDelivered) {
        if (typeof showToast === 'function') showToast('⛔ حماية رقابية: لا يمكن دمج/إلغاء التجزئة لأن أحد الفروع تم صرفه مخزنياً بالفعل!', 'error');
        return;
    }

    const branchNos = activeBranches.map(b => String(b.invoiceNo || b.id));
    allSales = allSales.filter(x => !branchNos.includes(String(x.invoiceNo || x.id)));
    const rIdx = allSales.findIndex(x => String(x.invoiceNo || x.id) === String(rootNo));
    if (rIdx !== -1) {
        allSales[rIdx].deleted = false;
        allSales[rIdx].wasSplit = false;
        delete allSales[rIdx].splitTo;
        localStorage.setItem('erp_inv_' + rootNo, JSON.stringify(allSales[rIdx]));
    }
    branchNos.forEach(bNo => localStorage.removeItem('erp_inv_' + bNo));
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_sales_v1', JSON.stringify(allSales));
    ['magaies_local_sales_invoices_v1', 'magaies_sales_invoices_cache'].forEach(k => {
        try { localStorage.setItem(k, JSON.stringify(allSales)); } catch (_e) {}
    });

    if (window.logSystemAction) {
        window.logSystemAction('دمج والتراجع عن تجزئة فاتورة', 'المبيعات والتسليم', rootNo, `تم إعادة دمج الفروع (${branchNos.join(' ، ')}) في الفاتورة الأم #${rootNo}`);
    }
    const gModal = document.getElementById('split-genealogy-tree-modal');
    if (gModal) gModal.style.display = 'none';
    loadSplitInvoices(true);
    if (typeof showToast === 'function') showToast(`✅ تم دمج الفروع واستعادة الفاتورة الأم #${rootNo} بنجاح`, 'success');
};

window.openSplitLogisticsAnalyticsModal = function() {
    const allSales = window.getMergedSalesInvoicesForSplit();
    const splitRoots = allSales.filter(x => x.wasSplit);
    const activeBranches = allSales.filter(x => !x.deleted && !x.wasSplit && (x.isSplit || x.parentInvoice || String(x.invoiceNo || x.id || '').includes('/')));
    const driverMap = {};
    const itemSplitMap = {};

    activeBranches.forEach(b => {
        const drv = b.receiverName || b.customerName || 'غير محدد';
        driverMap[drv] = (driverMap[drv] || 0) + 1;
        (b.items || []).forEach(it => {
            const m = window.resolveSplitItemUnitMeta(it);
            itemSplitMap[m.name] = (itemSplitMap[m.name] || 0) + (parseFloat(it.qty ?? it.quantity ?? 0) || 0);
        });
    });

    let modal = document.getElementById('split-analytics-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'split-analytics-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }

    const topItemsHtml = Object.entries(itemSplitMap).slice(0, 5).map(([name, qty]) =>
        `<div style="display:flex; justify-content:space-between; padding:6px 10px; border-bottom:1px solid rgba(255,255,255,0.08);"><span>📦 ${name}</span><strong style="color:#38bdf8; font-family:monospace;">${+qty.toFixed(2)}</strong></div>`
    ).join('') || '<div style="padding:10px; color:#94a3b8;">لا توجد أصناف مجزأة بعد</div>';

    const topDriversHtml = Object.entries(driverMap).slice(0, 5).map(([drv, cnt]) =>
        `<div style="display:flex; justify-content:space-between; padding:6px 10px; border-bottom:1px solid rgba(255,255,255,0.08);"><span>🚛 ${drv}</span><strong style="color:#6ee7b7; font-family:monospace;">${cnt} شحنة فرعية</strong></div>`
    ).join('') || '<div style="padding:10px; color:#94a3b8;">لا توجد شحنات سائقين مسجلة بعد</div>';

    modal.innerHTML = `
        <div style="width:94vw; max-width:720px; background:linear-gradient(160deg,#0f172a,#020617); border:2px solid #f472b6; border-radius:18px; padding:22px; color:#fff; box-shadow:0 24px 80px rgba(0,0,0,0.92);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(244,114,182,0.35); padding-bottom:10px; margin-bottom:14px;">
                <h3 style="margin:0; font-size:1.2rem; font-weight:900; color:#fbcfe8;">📊 تقرير تحليل كفاءة تجزئة الشحنات وتوزيع السائقين</h3>
                <button type="button" onclick="document.getElementById('split-analytics-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:1px solid #f87171; color:#fecaca; border-radius:8px; padding:5px 14px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:10px; margin-bottom:16px;">
                <div style="background:rgba(2,6,23,0.65); border:1.5px solid rgba(236,72,153,0.45); border-radius:12px; padding:12px; text-align:center;">
                    <div style="font-size:0.8rem; color:#94a3b8;">✂️ إجمالي عمليات التجزئة</div>
                    <strong style="font-size:1.5rem; color:#f472b6; font-family:monospace;">${splitRoots.length}</strong>
                </div>
                <div style="background:rgba(2,6,23,0.65); border:1.5px solid rgba(56,189,248,0.45); border-radius:12px; padding:12px; text-align:center;">
                    <div style="font-size:0.8rem; color:#94a3b8;">📄 الأذونات الفرعية النشطة</div>
                    <strong style="font-size:1.5rem; color:#38bdf8; font-family:monospace;">${activeBranches.length}</strong>
                </div>
                <div style="background:rgba(2,6,23,0.65); border:1.5px solid rgba(16,185,129,0.45); border-radius:12px; padding:12px; text-align:center;">
                    <div style="font-size:0.8rem; color:#94a3b8;">🚛 السائقون المعينون</div>
                    <strong style="font-size:1.5rem; color:#10b981; font-family:monospace;">${Object.keys(driverMap).length}</strong>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px;">
                <div style="background:rgba(15,23,42,0.8); border:1px solid rgba(56,189,248,0.3); border-radius:12px; padding:12px;">
                    <h4 style="margin:0 0 8px; color:#7dd3fc;">📦 أكثر الأصناف تجزئة</h4>
                    ${topItemsHtml}
                </div>
                <div style="background:rgba(15,23,42,0.8); border:1px solid rgba(16,185,129,0.3); border-radius:12px; padding:12px;">
                    <h4 style="margin:0 0 8px; color:#6ee7b7;">🚛 توزيع الشحنات الفرعية على السائقين</h4>
                    ${topDriversHtml}
                </div>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

// ═══════════════════════════════════════════════════════════════════════════
// CONSTITUTION 4 APPROVED PROPOSALS FOR SPLIT INVOICES (?action=split):
// 1. 🛡️ Truck Capacity & Weight Shield (درع توازن الأوزان والحمولة القصوى للشاحنات)
// 2. ✍️ Split Driver Digital Signature Pad (التوقيع الرقمي الميداني لسائقي الأقسام)
// 3. 📦 Saved Split Presets (القوالب الجاهزة للتجزئة المتكررة)
// ═══════════════════════════════════════════════════════════════════════════

window.computeSplitSectionWeights = function() {
    if (!activeSplitInvoice && typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
        window.ensureDemoOrFirstSplitBoardLoaded();
    }
    const inv = activeSplitInvoice;
    if (!inv) return { totalWeightKg: 0, totalWeightTon: 0, sections: [] };
    const S = window.splitSectionsCount || 2;
    const rootNo = window.getRootInvoiceNo ? window.getRootInvoiceNo(inv) : String(inv.invoiceNo || inv.id || '100');
    const splitSeq = Math.max(1, parseInt(window.currentSplitOperationSeq, 10) || 1);
    let totalWeightKg = 0;

    const sections = Array.from({ length: S }, (_, s) => {
        const metaInfo = window._splitSectionMeta[s] || {};
        const maxCapTon = parseFloat(metaInfo.maxCapacityTon) || 5.0;
        let secKg = 0;
        (inv.items || []).forEach((item, idx) => {
            const m = window.resolveSplitItemUnitMeta(item);
            const q = parseFloat(document.getElementById(`split-qty-s${s}-i${idx}`)?.value || 0) || 0;
            const unitKg = parseFloat(item.unitWeightKg || item.weightKg || (m.factor > 1 ? m.factor : 50)) || 50;
            secKg += (q * unitKg);
        });
        totalWeightKg += secKg;
        const secTon = +(secKg / 1000).toFixed(2);
        const utilizationPct = maxCapTon > 0 ? Math.min(999, Math.round((secTon / maxCapTon) * 100)) : 0;
        const isOver = secTon > maxCapTon + 0.001;
        return {
            secIdx: s,
            secNo: `${rootNo}/${splitSeq}/${s + 1}`,
            driver: metaInfo.receiverName || inv.receiverName || `سائق القسم (${s + 1})`,
            truck: metaInfo.truckPlate || inv.truckPlate || 'غير محدد',
            weightKg: Math.round(secKg),
            weightTon: secTon,
            maxCapTon,
            utilizationPct,
            isOver
        };
    });

    return {
        totalWeightKg: Math.round(totalWeightKg),
        totalWeightTon: +(totalWeightKg / 1000).toFixed(2),
        sections
    };
};

window.updateSplitTruckCap = function(secIdx, val) {
    const num = Math.max(0.5, parseFloat(val) || 5.0);
    window._splitSectionMeta = window._splitSectionMeta || {};
    window._splitSectionMeta[secIdx] = Object.assign(window._splitSectionMeta[secIdx] || {}, { maxCapacityTon: num });
    if (typeof updateSplitCalculations === 'function') updateSplitCalculations();
    window.openSplitWeightShieldModal();
};

window.autoBalanceSplitByWeight = function() {
    if (!activeSplitInvoice && typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
        window.ensureDemoOrFirstSplitBoardLoaded();
    }
    if (!activeSplitInvoice) return;
    const S = window.splitSectionsCount || 2;
    const caps = Array.from({ length: S }, (_, s) => parseFloat(window._splitSectionMeta?.[s]?.maxCapacityTon) || 5.0);
    const totalCap = caps.reduce((a, b) => a + b, 0) || S;

    (activeSplitInvoice.items || []).forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const maxQty = meta.deliverableQty;
        let allocated = 0;
        for (let s = 0; s < S; s++) {
            const ratio = caps[s] / totalCap;
            const qVal = (s === S - 1)
                ? Math.max(0, +(maxQty - allocated).toFixed(4))
                : +(maxQty * ratio).toFixed(4);
            if (s < S - 1) allocated += qVal;
            const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
            const subInp = document.getElementById(`split-subqty-s${s}-i${idx}`);
            if (inp) inp.value = qVal;
            if (subInp) subInp.value = +(qVal * meta.factor).toFixed(2);
        }
    });
    if (typeof window.saveCurrentSplitDraftState === 'function') window.saveCurrentSplitDraftState();
    if (typeof updateSplitCalculations === 'function') updateSplitCalculations();
    window.openSplitWeightShieldModal();
    if (typeof showToast === 'function') showToast('⚖️ تم موازنة الكميات والأوزان نسبياً حسب الحمولة القصوى لكل شاحنة بنجاح', 'success');
};

window.speakSplitWeightShieldReport = function() {
    const rep = window.computeSplitSectionWeights();
    const overList = rep.sections.filter(x => x.isOver);
    const details = rep.sections.map(x => `القسم ${x.secIdx + 1} وزنه ${x.weightTon} طن من أصل ${x.maxCapTon} طن`).join('، ');
    const msg = overList.length > 0
        ? `تنبيه من درع الأوزان: إجمالي وزن الشحنة ${rep.totalWeightTon} طن. يوجد تجاوز للحمولة المسموحة في ${overList.length} شاحنة. ${details}. يُنصح بالضغط على زر موازنة الأوزان تلقائياً.`
        : `تقرير درع الأوزان آمن مئة بالمئة: إجمالي وزن الشحنة ${rep.totalWeightTon} طن موزعة بأمان دون تجاوز سعة أي شاحنة. ${details}.`;
    if (window.MagaiesGuideEngine && typeof window.MagaiesGuideEngine.speakText === 'function') {
        window.MagaiesGuideEngine.speakText(msg);
    } else if (typeof showToast === 'function') {
        showToast(msg, overList.length > 0 ? 'warning' : 'success');
    }
};

window.openSplitWeightShieldModal = function() {
    const rep = window.computeSplitSectionWeights();
    let modal = document.getElementById('split-weight-shield-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'split-weight-shield-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }
    const overCount = rep.sections.filter(x => x.isOver).length;
    const cardsHtml = rep.sections.map(sec => `
        <div style="background:rgba(15,23,42,0.85); border:2px solid ${sec.isOver ? '#ef4444' : '#10b981'}; border-radius:14px; padding:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <strong style="color:${sec.isOver ? '#fca5a5' : '#6ee7b7'}; font-size:0.98rem;">🚛 القسم (${sec.secIdx + 1}) — #${sec.secNo}</strong>
                <span style="padding:3px 10px; border-radius:99px; font-size:0.78rem; font-weight:900; background:${sec.isOver ? 'rgba(239,68,68,0.25)' : 'rgba(16,185,129,0.2)'}; color:${sec.isOver ? '#fecaca' : '#a7f3d0'};">
                    ${sec.isOver ? `🚨 تجاوز الحمولة (${sec.utilizationPct}%)` : `✅ حمولة آمنة (${sec.utilizationPct}%)`}
                </span>
            </div>
            <div style="font-size:0.84rem; color:#cbd5e1; margin-bottom:8px;">👤 السائق: <strong>${sec.driver}</strong> | 🚛 اللوحة: <strong>${sec.truck}</strong></div>
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                <div>⚖️ الوزن الفعلي: <strong style="font-family:monospace; font-size:1.05rem; color:#38bdf8;">${sec.weightTon} طن (${sec.weightKg.toLocaleString()} كجم)</strong></div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <label style="font-size:0.8rem; font-weight:900;">سعة الشاحنة (طن):</label>
                    <input type="number" step="0.5" min="0.5" value="${sec.maxCapTon}" onchange="window.updateSplitTruckCap(${sec.secIdx}, this.value)" style="width:82px; text-align:center; padding:4px 6px; border-radius:8px; font-weight:900;">
                </div>
            </div>
        </div>
    `).join('');

    modal.innerHTML = `
        <div class="ds-c1-modal-card" style="width:94vw; max-width:740px; max-height:90vh; overflow-y:auto; background:#111c33; border:2.4px solid #FFFFFF; border-radius:18px; padding:22px; color:#FFFFFF; box-shadow:0 24px 80px rgba(0,0,0,0.92);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(255,255,255,0.18); padding-bottom:10px; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
                <div>
                    <h3 style="margin:0; font-size:1.22rem; font-weight:900; color:#f87171;">🛡️ درع توازن الأوزان والحمولة القصوى للشاحنات</h3>
                    <div style="font-size:0.84rem; color:#cbd5e1; margin-top:3px;">إجمالي وزن الشحنة الكلي: <strong style="color:#38bdf8; font-family:monospace;">${rep.totalWeightTon} طن (${rep.totalWeightKg.toLocaleString()} كجم)</strong></div>
                </div>
                <button type="button" onclick="document.getElementById('split-weight-shield-modal').style.display='none'" style="background:rgba(239,68,68,0.25); border:1.5px solid #f87171; color:#fecaca; border-radius:8px; padding:5px 14px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
            </div>
            ${overCount > 0
                ? `<div style="background:rgba(239,68,68,0.2); border:1.5px solid #ef4444; border-radius:12px; padding:10px 14px; margin-bottom:12px; font-weight:900; color:#fecaca;">🚨 تنبيه رقابي: يوجد (${overCount}) قسم يتجاوز الحمولة القصوى المسموحة للشاحنة! اضغط "⚖️ موازنة الأوزان تلقائياً" لإعادة التوزيع الآمن فوراً.</div>`
                : `<div style="background:rgba(16,185,129,0.16); border:1.5px solid #10b981; border-radius:12px; padding:10px 14px; margin-bottom:12px; font-weight:900; color:#a7f3d0;">✅ جميع الأقسام ضمن الحمولة القانونية الآمنة للشاحنات المعينة.</div>`
            }
            <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:16px;">
                ${cardsHtml}
            </div>
            <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; border-top:1px solid rgba(255,255,255,0.14); padding-top:12px;">
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" onclick="window.autoBalanceSplitByWeight()" class="kinetic-glow-arm kinetic-arm-emerald" style="padding:9px 18px; border-radius:10px; font-weight:900; cursor:pointer;">⚖️ موازنة الأوزان على الشاحنات تلقائياً</button>
                    <button type="button" onclick="window.speakSplitWeightShieldReport()" class="kinetic-glow-arm kinetic-arm-cyan" style="padding:9px 16px; border-radius:10px; font-weight:900; cursor:pointer;">🔊 قراءة تقرير الأوزان صوتياً</button>
                </div>
                <button type="button" onclick="document.getElementById('split-weight-shield-modal').style.display='none'" style="background:rgba(255,255,255,0.1); border:1.5px solid rgba(255,255,255,0.3); color:#fff; padding:9px 18px; border-radius:10px; font-weight:900; cursor:pointer;">👍 تم</button>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

// 2. ✍️ Split Driver Digital Signature Pad
window.openSplitDriverSignatureModal = function(secIdx = 0) {
    if (!activeSplitInvoice && typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
        window.ensureDemoOrFirstSplitBoardLoaded();
    }
    const S = window.splitSectionsCount || 2;
    const safeSec = Math.max(0, Math.min(S - 1, parseInt(secIdx, 10) || 0));
    window._activeSplitSigSectionIdx = safeSec;
    const metaInfo = (window._splitSectionMeta && window._splitSectionMeta[safeSec]) || {};
    const driverName = metaInfo.receiverName || activeSplitInvoice?.receiverName || `سائق القسم (${safeSec + 1})`;
    const truckPlate = metaInfo.truckPlate || activeSplitInvoice?.truckPlate || '';

    let modal = document.getElementById('split-driver-signature-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'split-driver-signature-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }

    const secTabs = Array.from({ length: S }, (_, s) => `
        <button type="button" onclick="window.openSplitDriverSignatureModal(${s})" style="padding:6px 14px; border-radius:9px; font-weight:900; cursor:pointer; border:1.5px solid ${s === safeSec ? '#38bdf8' : 'rgba(255,255,255,0.2)'}; background:${s === safeSec ? 'rgba(56,189,248,0.25)' : 'rgba(15,23,42,0.7)'}; color:#fff;">
            ${window._splitSectionMeta?.[s]?.driverSignatureDataUrl ? '✅ ' : '✍️ '}القسم (${s + 1})
        </button>
    `).join('');

    modal.innerHTML = `
        <div class="ds-c1-modal-card" style="width:94vw; max-width:620px; background:#111c33; border:2.4px solid #FFFFFF; border-radius:18px; padding:20px; color:#FFFFFF; box-shadow:0 24px 80px rgba(0,0,0,0.92);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(255,255,255,0.16); padding-bottom:10px; margin-bottom:12px;">
                <h3 style="margin:0; font-size:1.18rem; font-weight:900; color:#38bdf8;">✍️ التوقيع الرقمي الميداني لسائق القسم (${safeSec + 1})</h3>
                <button type="button" onclick="document.getElementById('split-driver-signature-modal').style.display='none'" style="background:rgba(239,68,68,0.25); border:1.5px solid #f87171; color:#fecaca; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
            </div>
            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px;">${secTabs}</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
                <div>
                    <label style="display:block; font-size:0.78rem; margin-bottom:3px; font-weight:900;">👤 اسم السائق المستلم:</label>
                    <input type="text" id="split-sig-driver-name" value="${driverName.replace(/"/g, '&quot;')}" style="width:100%; padding:7px 10px; border-radius:8px; font-weight:800;">
                </div>
                <div>
                    <label style="display:block; font-size:0.78rem; margin-bottom:3px; font-weight:900;">🚛 رقم اللوحة / الشاحنة:</label>
                    <input type="text" id="split-sig-truck-plate" value="${truckPlate.replace(/"/g, '&quot;')}" style="width:100%; padding:7px 10px; border-radius:8px; font-weight:800;">
                </div>
            </div>
            <div style="background:#FFFFFF; border:2.4px solid #38bdf8; border-radius:12px; padding:6px; text-align:center; margin-bottom:12px;">
                <canvas id="split-driver-sig-canvas" width="540" height="175" style="width:100%; height:175px; background:#FFFFFF; cursor:crosshair; touch-action:none; border-radius:8px;"></canvas>
                <div style="font-size:0.76rem; color:#0f172a; font-weight:900; margin-top:2px;">وقّع هنا بالماوس أو الإصبع — سيُدرج التوقيع تلقائياً في سند التفويض وإذن الصرف المطبوع</div>
            </div>
            <div style="display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                <button type="button" onclick="window.clearSplitDriverSigCanvas()" style="background:rgba(245,158,11,0.22); border:1.5px solid #fbbf24; color:#fde68a; padding:8px 16px; border-radius:10px; font-weight:900; cursor:pointer;">🧹 مسح التوقيع</button>
                <button type="button" onclick="window.saveSplitDriverSignature()" class="kinetic-glow-arm kinetic-arm-emerald" style="padding:8px 22px; border-radius:10px; font-weight:900; cursor:pointer;">💾 حفظ واعتماد توقيع السائق</button>
            </div>
        </div>
    `;
    modal.style.display = 'flex';

    setTimeout(() => {
        const canvas = document.getElementById('split-driver-sig-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.8;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        window._splitDriverSigHasInk = false;

        if (metaInfo.driverSignatureDataUrl) {
            const img = new Image();
            img.onload = () => {
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                window._splitDriverSigHasInk = true;
            };
            img.src = metaInfo.driverSignatureDataUrl;
        }

        let drawing = false;
        const getPos = (e) => {
            const r = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - r.left) * (canvas.width / r.width),
                y: (clientY - r.top) * (canvas.height / r.height)
            };
        };
        const start = (e) => { e.preventDefault(); drawing = true; window._splitDriverSigHasInk = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); };
        const move = (e) => { if (!drawing) return; e.preventDefault(); const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); };
        const stop = () => { drawing = false; };
        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', stop, { once: false });
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', stop);
    }, 30);
};

window.clearSplitDriverSigCanvas = function() {
    const canvas = document.getElementById('split-driver-sig-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    window._splitDriverSigHasInk = false;
};

window.saveSplitDriverSignature = function() {
    const secIdx = window._activeSplitSigSectionIdx || 0;
    const canvas = document.getElementById('split-driver-sig-canvas');
    if (!canvas) return;
    const drvName = (document.getElementById('split-sig-driver-name')?.value || '').trim();
    const trkPlate = (document.getElementById('split-sig-truck-plate')?.value || '').trim();
    const dataUrl = window._splitDriverSigHasInk ? canvas.toDataURL('image/png') : '';

    window._splitSectionMeta = window._splitSectionMeta || {};
    window._splitSectionMeta[secIdx] = Object.assign(window._splitSectionMeta[secIdx] || {}, {
        ...(drvName && { receiverName: drvName }),
        ...(trkPlate && { truckPlate: trkPlate }),
        driverSignatureDataUrl: dataUrl
    });

    const recInp = document.getElementById(`split-sec-receiver-${secIdx}`);
    const trkInp = document.getElementById(`split-sec-truck-${secIdx}`);
    if (recInp && drvName) recInp.value = drvName;
    if (trkInp && trkPlate) trkInp.value = trkPlate;

    if (typeof updateSplitCalculations === 'function') updateSplitCalculations();
    const modal = document.getElementById('split-driver-signature-modal');
    if (modal) modal.style.display = 'none';
    if (typeof showToast === 'function') showToast(`✍️ تم حفظ التوقيع الرقمي لسائق القسم (${secIdx + 1}) وسيُطبع تلقائياً في السند`, 'success');
};

// 3. 📦 Saved Split Presets (القوالب الجاهزة للتجزئة المتكررة)
window.getSplitSavedPresetsList = function() {
    const defaults = [
        { id: 'preset_50_50', name: '⚖️ مناصفة 50% / 50% (شاحنتان متساويتان)', sectionsCount: 2, ratios: [0.5, 0.5], builtin: true },
        { id: 'preset_70_30', name: '🏗️ 70% الموقع الرئيسي / 30% المستودع الفرعي', sectionsCount: 2, ratios: [0.7, 0.3], builtin: true },
        { id: 'preset_3_equal', name: '🚛 توزيع متساوٍ على 3 شاحنات (33.3% لكل شاحنة)', sectionsCount: 3, ratios: [0.3333, 0.3333, 0.3334], builtin: true },
        { id: 'preset_heavy_light', name: '🏋️ فصل الأصناف الثقيلة في (أ) والخفيفة في (ب)', sectionsCount: 2, mode: 'heavy_light', builtin: true }
    ];
    let custom = [];
    try { custom = JSON.parse(localStorage.getItem('magaies_split_saved_presets_v1') || '[]'); } catch (_e) {}
    if (!Array.isArray(custom)) custom = [];
    return [...defaults, ...custom];
};

window.applySavedSplitPreset = function(presetId) {
    if (!activeSplitInvoice && typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
        window.ensureDemoOrFirstSplitBoardLoaded();
    }
    if (!activeSplitInvoice) return;
    const all = window.getSplitSavedPresetsList();
    const p = all.find(x => String(x.id) === String(presetId));
    if (!p) return;

    if (p.mode === 'heavy_light' && typeof window.separateHeavyItemsSplit === 'function') {
        window.separateHeavyItemsSplit();
        const m = document.getElementById('split-presets-modal');
        if (m) m.style.display = 'none';
        return;
    }

    const S = Math.max(2, Math.min(8, parseInt(p.sectionsCount, 10) || 2));
    window.splitSectionsCount = S;
    if (Array.isArray(p.sectionMeta)) {
        window._splitSectionMeta = {};
        p.sectionMeta.forEach((mObj, idx) => {
            if (idx < S && mObj) window._splitSectionMeta[idx] = { ...mObj };
        });
    }

    const ratios = Array.isArray(p.ratios) && p.ratios.length === S
        ? p.ratios
        : Array.from({ length: S }, () => 1 / S);

    activeSplitInvoice._splitDraftQtys = {};
    (activeSplitInvoice.items || []).forEach((item, idx) => {
        const meta = window.resolveSplitItemUnitMeta(item);
        const maxQty = meta.deliverableQty;
        let allocated = 0;
        const rowArr = [];
        for (let s = 0; s < S; s++) {
            const qVal = (s === S - 1)
                ? Math.max(0, +(maxQty - allocated).toFixed(4))
                : +(maxQty * (parseFloat(ratios[s]) || (1 / S))).toFixed(4);
            if (s < S - 1) allocated += qVal;
            rowArr.push(qVal);
        }
        activeSplitInvoice._splitDraftQtys[idx] = rowArr;
    });

    if (typeof renderWorldClassSplitBoard === 'function') renderWorldClassSplitBoard();
    const modal = document.getElementById('split-presets-modal');
    if (modal) modal.style.display = 'none';
    if (typeof showToast === 'function') showToast(`📦 تم تطبيق قالب التجزئة "${p.name}" بنجاح`, 'success');
};

window.saveCurrentSplitAsPreset = function() {
    if (!activeSplitInvoice && typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
        window.ensureDemoOrFirstSplitBoardLoaded();
    }
    const nameInp = document.getElementById('split-new-preset-name');
    const name = (nameInp?.value || '').trim();
    if (!name) {
        if (typeof showToast === 'function') showToast('⚠️ يرجى كتابة اسم واضح للقالب قبل الحفظ', 'warning');
        if (nameInp) nameInp.focus();
        return;
    }
    const S = window.splitSectionsCount || 2;
    const totalsPerSec = new Array(S).fill(0);
    let grandQty = 0;
    (activeSplitInvoice?.items || []).forEach((_, idx) => {
        for (let s = 0; s < S; s++) {
            const q = parseFloat(document.getElementById(`split-qty-s${s}-i${idx}`)?.value || 0) || 0;
            totalsPerSec[s] += q;
            grandQty += q;
        }
    });
    const ratios = totalsPerSec.map(q => grandQty > 0 ? +(q / grandQty).toFixed(4) : +(1 / S).toFixed(4));
    const sectionMeta = Array.from({ length: S }, (_, s) => ({
        receiverName: window._splitSectionMeta?.[s]?.receiverName || '',
        truckPlate: window._splitSectionMeta?.[s]?.truckPlate || '',
        deliveryLocation: window._splitSectionMeta?.[s]?.deliveryLocation || '',
        receiverPhone: window._splitSectionMeta?.[s]?.receiverPhone || '',
        maxCapacityTon: parseFloat(window._splitSectionMeta?.[s]?.maxCapacityTon) || 5.0
    }));

    let custom = [];
    try { custom = JSON.parse(localStorage.getItem('magaies_split_saved_presets_v1') || '[]'); } catch (_e) {}
    if (!Array.isArray(custom)) custom = [];
    custom.unshift({
        id: 'custom_' + Date.now(),
        name: `⭐ ${name}`,
        sectionsCount: S,
        ratios,
        sectionMeta,
        createdAt: new Date().toLocaleString('ar-EG')
    });
    localStorage.setItem('magaies_split_saved_presets_v1', JSON.stringify(custom));
    window.openSplitPresetsModal();
    if (typeof showToast === 'function') showToast(`✅ تم حفظ قالب التجزئة "${name}" للاستخدام السريع في جميع الفواتير القادمة`, 'success');
};

window.deleteSavedSplitPreset = function(presetId) {
    let custom = [];
    try { custom = JSON.parse(localStorage.getItem('magaies_split_saved_presets_v1') || '[]'); } catch (_e) {}
    custom = (custom || []).filter(x => String(x.id) !== String(presetId));
    localStorage.setItem('magaies_split_saved_presets_v1', JSON.stringify(custom));
    window.openSplitPresetsModal();
    if (typeof showToast === 'function') showToast('🗑️ تم حذف القالب المخصص بنجاح', 'info');
};

window.openSplitPresetsModal = function() {
    const presets = window.getSplitSavedPresetsList();
    let modal = document.getElementById('split-presets-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'split-presets-modal';
        modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; direction:rtl; font-family:"Tajawal",sans-serif; padding:16px;';
        document.body.appendChild(modal);
    }

    const rowsHtml = presets.map(p => {
        const ratioTxt = Array.isArray(p.ratios)
            ? p.ratios.map(r => `${Math.round(r * 100)}%`).join(' : ')
            : 'فصل ذكي حسب الوزن';
        return `
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; background:rgba(15,23,42,0.85); border:1.5px solid rgba(56,189,248,0.4); border-radius:12px; padding:10px 14px;">
                <div>
                    <div style="font-weight:900; font-size:0.95rem; color:#bae6fd;">${p.name}</div>
                    <div style="font-size:0.78rem; color:#94a3b8; margin-top:2px;">عدد الأقسام: <strong>${p.sectionsCount || 2}</strong> | نسب التوزيع: <strong style="color:#38bdf8; font-family:monospace;">${ratioTxt}</strong></div>
                </div>
                <div style="display:flex; gap:6px;">
                    <button type="button" onclick="window.applySavedSplitPreset('${p.id}')" class="kinetic-glow-arm kinetic-arm-cyan" style="padding:6px 14px; border-radius:8px; font-weight:900; cursor:pointer;">⚡ تطبيق فوري</button>
                    ${!p.builtin ? `<button type="button" onclick="window.deleteSavedSplitPreset('${p.id}')" style="background:rgba(239,68,68,0.2); border:1px solid #f87171; color:#fecaca; padding:6px 10px; border-radius:8px; font-weight:900; cursor:pointer;">🗑️</button>` : ''}
                </div>
            </div>
        `;
    }).join('');

    modal.innerHTML = `
        <div class="ds-c1-modal-card" style="width:94vw; max-width:680px; max-height:90vh; overflow-y:auto; background:#111c33; border:2.4px solid #FFFFFF; border-radius:18px; padding:22px; color:#FFFFFF; box-shadow:0 24px 80px rgba(0,0,0,0.92);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(255,255,255,0.16); padding-bottom:10px; margin-bottom:14px;">
                <h3 style="margin:0; font-size:1.2rem; font-weight:900; color:#38bdf8;">📦 القوالب الجاهزة للتجزئة المتكررة (Saved Split Presets)</h3>
                <button type="button" onclick="document.getElementById('split-presets-modal').style.display='none'" style="background:rgba(239,68,68,0.25); border:1.5px solid #f87171; color:#fecaca; border-radius:8px; padding:5px 14px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
            </div>
            <div style="background:rgba(15,23,42,0.75); border:1.5px solid rgba(16,185,129,0.4); border-radius:12px; padding:12px; margin-bottom:14px;">
                <label style="display:block; font-size:0.84rem; font-weight:900; color:#6ee7b7; margin-bottom:6px;">➕ حفظ التوزيعة الحالية والسائقين كقالب جديد متكرر:</label>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <input type="text" id="split-new-preset-name" placeholder="مثال: توزيع مشاريع أم درمان (3 شاحنات)..." style="flex:1; min-width:220px; padding:8px 12px; border-radius:9px; font-weight:800;">
                    <button type="button" onclick="window.saveCurrentSplitAsPreset()" class="kinetic-glow-arm kinetic-arm-emerald" style="padding:8px 18px; border-radius:9px; font-weight:900; cursor:pointer;">💾 حفظ كقالب</button>
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                ${rowsHtml}
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

// Keydown delegation for payment inputs to allow standard digit input without artificial limits
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
    
    // Only intercept non-digit and non-dot characters
    const isDigit = /^[0-9]$/.test(event.key);
    const isDot = event.key === '.';
    if (!isDigit && !isDot) {
        if (!event.ctrlKey && !event.metaKey && event.key.length === 1) {
            event.preventDefault();
        }
        return;
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

    if (window.isInvoiceLocked) {
        bar.style.display = 'none';
        return;
    }

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
    
    if (!window._dsConfirmedUnpost && typeof window.openDsConfirmModal === 'function') {
        window.openDsConfirmModal(
            '🔓 إلغاء ترحيل الفاتورة',
            'هل أنت متأكد من إلغاء ترحيل هذه الفاتورة وعكس التأثيرات المالية؟',
            () => {
                window._dsConfirmedUnpost = true;
                unpostSalesInvoice(invNo);
                setTimeout(() => { window._dsConfirmedUnpost = false; }, 300);
            }
        );
        return;
    }
    
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

// Deep Drill-down & ?action=new handler
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const invoiceId = urlParams.get('id');
    const actionParam = urlParams.get('action');
    if (invoiceId) {
        setTimeout(() => {
            if (typeof loadInvoiceFromStorage === 'function') loadInvoiceFromStorage(invoiceId);
        }, 500);
    } else if (actionParam === 'delivery' || actionParam === 'gdn') {
        setTimeout(() => {
            if (typeof openDeliveryHub === 'function') openDeliveryHub();
        }, 50);
    } else if (actionParam === 'new' || actionParam === 'direct' || actionParam === 'pending_delivery' || actionParam === 'preliminary' || actionParam === 'proforma') {
        setTimeout(() => {
            if (typeof openInvoiceEditor === 'function') {
                openInvoiceEditor(actionParam === 'new' || actionParam === 'direct' ? 'pending_delivery' : (actionParam === 'proforma' ? 'preliminary' : actionParam));
            }
        }, 100);
    }
});

// ══════════════════════════════════════════════════════════════════════════
// EXPORT INVOICE DATA FOR WHATSAPP SUITE (Sales)
// ══════════════════════════════════════════════════════════════════════════
window.getSalesInvoiceDataForWA = function(invoiceNo = null) {
    if (invoiceNo) {
        if (window._lastSubmittedSalesInvoice && String(window._lastSubmittedSalesInvoice.invoiceNo) === String(invoiceNo)) {
            return window._lastSubmittedSalesInvoice;
        }
        try {
            const raw = localStorage.getItem('erp_inv_' + invoiceNo);
            if (raw) return JSON.parse(raw);
        } catch(e) {}
        try {
            const list = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            const found = list.find(i => String(i.invoiceNo) === String(invoiceNo));
            if (found) return found;
        } catch(e) {}
    }

    if (currentViewedInvoiceNo) {
        try {
            const raw = localStorage.getItem('erp_inv_' + currentViewedInvoiceNo);
            if (raw) return JSON.parse(raw);
        } catch(e) {}
    }

    const invNoVal = document.getElementById('hdr-inv-no')?.value || document.getElementById('inv-number')?.textContent || 'SAL-NEW';
    const custId = document.getElementById('hdr-customer')?.value || '';
    const custName = document.getElementById('hdr-customer-search')?.value || 'عميل نقدي';

    let custPhone = '';
    if (typeof CUSTOMERS !== 'undefined' && Array.isArray(CUSTOMERS)) {
        const c = CUSTOMERS.find(x => String(x.id) === String(custId));
        if (c && c.phone) custPhone = c.phone;
    }
    if (!custPhone && custId && custId !== 'walk-in') {
        try {
            const custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const c = custs.find(x => String(x.id) === String(custId));
            if (c) custPhone = c.whatsapp || c.phone || c.mobile || '';
        } catch(e) {}
    }

    const itemsList = (rows || []).filter(r => r && r.itemId).map(r => {
        const it = (typeof ITEMS !== 'undefined' ? ITEMS : []).find(i => String(i.id) === String(r.itemId));
        const calcs = (typeof getRowCalcs === 'function') ? getRowCalcs(r) : { total: (parseFloat(r.quantity)||0) * (parseFloat(r.price)||0) };
        return {
            itemId: r.itemId,
            name: it ? it.name : (r.name || 'صنف'),
            itemName: it ? it.name : (r.name || 'صنف'),
            unit: r.selectedUnit || (it ? it.baseUnit : 'وحدة'),
            quantity: parseFloat(r.quantity) || 0,
            qty: parseFloat(r.quantity) || 0,
            price: parseFloat(r.price) || 0,
            unitPrice: parseFloat(r.price) || 0,
            total: parseFloat(calcs.total) || 0,
            notes: r.itemNotes || r.notes || '-'
        };
    });

    const grandTotal = document.getElementById('t-grand-dynamic-loc')?.textContent || 
                       document.getElementById('t-grand')?.textContent || 
                       document.getElementById('t-grand-local')?.textContent || '0.00';
    const cur = (typeof getCurrency === 'function') ? getCurrency() : (document.getElementById('hdr-currency')?.value || 'SDG');

    return {
        invoiceNo: invNoVal,
        date: document.getElementById('hdr-date')?.value || new Date().toISOString().split('T')[0],
        customer: custId,
        customerName: custName,
        customerPhone: custPhone,
        currency: cur,
        items: itemsList,
        grandTotal: grandTotal,
        paymentMethod: document.getElementById('hdr-payment-method')?.value || 'cash',
        notes: document.getElementById('inv-notes')?.value || ''
    };
};
