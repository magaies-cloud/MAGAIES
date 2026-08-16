/* ═══════════════════════════════════════════════════════
   sales-invoice.js — POS Engine v6.0
   Real-time Reactive Calculations · Sequential Invoices
   ═══════════════════════════════════════════════════════ */

// Global logout function (used by sidebar)
// Global logout is handled by app.js

// ════════════════════════════════════════
//  MOCK DATA (Replace with API calls)
// ════════════════════════════════════════

let WAREHOUSES = [];
const COST_CENTERS = [
    { id:'cc-001', name:'الفرع الرئيسي' },
    { id:'cc-002', name:'فرع بحري' },
    { id:'cc-003', name:'فرع أمدرمان' },
];

let VENDORS = [];
let UOMS = [];
let ITEMS = [];

// ════════════════════════════════════════
// Grid settings logic removed. Using standard fixed columns.

// ════════════════════════════════════════
//  STATE
// ════════════════════════════════════════
let rows = [];
let rowIdCounter = 0;
let currentSaleType = 'direct';
let currentViewedInvoiceNo = null;

window._cachedPurchaseInvoicesRaw = null;
window._cachedPurchaseList = null;

function getPurchaseInvoicesList() {
    const raw = localStorage.getItem('erp_purchase_invoices_v1');
    if (window._cachedPurchaseInvoicesRaw === raw && window._cachedPurchaseList) {
        return window._cachedPurchaseList;
    }
    let list = [];
    try { list = JSON.parse(raw) || []; } catch(e) {}
    window._cachedPurchaseInvoicesRaw = raw;
    window._cachedPurchaseList = list;
    return list;
}

// ════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════
function fmtNum(n, colId = null) {
    if (n === 0 || n === '0' || !n) {
        n = 0;
    }
    
    // Read preferences from localStorage
    const prefs = JSON.parse(localStorage.getItem('num_format_prefs') || '{}');
    let decimals = prefs.decimals !== undefined ? prefs.decimals : 2;
    let separator = prefs.thousandSep !== undefined ? prefs.thousandSep : ',';
    let decimalSep = prefs.decimalSep !== undefined ? prefs.decimalSep : '.';

    // OVERRIDE WITH PER COLUMN SETTINGS if available
    if (colId && window.CurrentGridConfig && window.CurrentGridConfig[colId]) {
        const colCfg = window.CurrentGridConfig[colId];
        if (colCfg.decimals !== undefined && colCfg.decimals !== '') decimals = parseInt(colCfg.decimals);
        if (colCfg.thousandSep !== undefined && colCfg.thousandSep !== '') separator = colCfg.thousandSep;
    }

    let val = parseFloat(n);
    if (isNaN(val)) val = 0;

    let numStr = val.toFixed(decimals);
    let parts = numStr.split('.');
    
    // Apply thousands separator
    if (separator !== 'none') {
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, separator);
    }
    
    // Apply decimal separator
    let decimalPart = parts[1] || '';
    if (decimalPart) {
        return parts[0] + decimalSep + decimalPart;
    }
    return parts[0];
}
function parseNum(str) {
    if (str === null || str === undefined) return 0;
    if (typeof str === 'number') return str;
    
    let s = String(str).trim();
    if (!s) return 0;

    // Get current formatting prefs
    const prefs = JSON.parse(localStorage.getItem('num_format_prefs') || '{}');
    const thousandSep = prefs.thousandSep !== undefined ? prefs.thousandSep : ',';
    const decimalSep = prefs.decimalSep !== undefined ? prefs.decimalSep : '.';

    // Remove thousands separators
    if (thousandSep !== 'none') {
        const escapedSep = thousandSep.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        s = s.replace(new RegExp(escapedSep, 'g'), '');
    }

    // Replace decimal separator with standard dot
    if (decimalSep !== '.') {
        const escapedDec = decimalSep.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        s = s.replace(new RegExp(escapedDec, 'g'), '.');
    }

    let n = parseFloat(s);
    return isNaN(n) ? 0 : n;
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
const SALE_PREFIX = { direct:'', split:'', delivery:'', proforma:'' };

// Peek: show the next number WITHOUT incrementing (safe for refresh)
function peekNextInvoiceNumber(type) {
    let list = getPurchaseInvoicesList();
    const now = new Date();
    const yy = String(now.getFullYear()).substring(2, 4);
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const isProforma = (type === 'proforma' || window.currentSaleType === 'proforma');
    const datePrefix = isProforma ? ('D' + yy + mm) : ('P' + yy + mm);
    
    let max = 0;
    list.forEach(inv => {
        const invNo = String(inv.invoiceNo || '');
        if (invNo.startsWith(datePrefix)) {
            const seqStr = invNo.substring(datePrefix.length);
            const seq = parseInt(seqStr, 10);
            if (!isNaN(seq) && seq > max) {
                max = seq;
            }
        } else if (!isProforma && invNo.startsWith('P260')) {
            const seqStr = invNo.substring(4);
            const seq = parseInt(seqStr, 10);
            if (!isNaN(seq) && seq > max) {
                max = seq;
            }
        } else if (!isProforma && (/^\d{8}$/.test(invNo) || /^C-\d+$/.test(invNo))) {
            let seq = parseInt(invNo.replace(/\D/g, ''), 10) % 10000;
            if (!isNaN(seq) && seq > max) {
                max = seq;
            }
        }
    });
    return `${datePrefix}${String(max + 1).padStart(4, '0')}`;
}

// Commit: actually increment the counter (only on real save/submit)
function commitInvoiceNumber(type) {
    return peekNextInvoiceNumber(type);
}

// ════════════════════════════════════════
//  INIT
// ════════════════════════════════════════
function initPurchaseApp() {
    window.refreshAllMasterData = function() {
        try {
            WAREHOUSES = JSON.parse(localStorage.getItem('erp_warehouses_v1'));
        } catch(e) { WAREHOUSES = null; }
        
        if (!WAREHOUSES || WAREHOUSES.length === 0) {
            WAREHOUSES = [{ id:'w-main', name:'المخزن الرئيسي' }];
        }
        WAREHOUSES.unshift({ id: 'PENDING_RECEIPT', name: '⏳ بضاعة قيد الاستلام (لم تحدد بعد)' });

        let supps = [];
        try {
            supps = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        } catch(e) { supps = []; }
        
        // For Purchase Invoices, we only want Suppliers, NOT Customers.
        const uniquePartners = Array.from(new Map(supps.map(item => [item.id, item])).values());
        
        VENDORS = uniquePartners.map(s => ({
            id: s.id, name: s.name,
            ledger: parseFloat(s.currentBalance) || 0, checks: 0,
            creditLimit: s.creditLimit || 0
        }));

        try {
            UOMS = JSON.parse(localStorage.getItem('erp_uoms_v1')) || [
                {code: 'TON', name: 'طن'},
                {code: 'KG', name: 'كيلو'},
                {code: 'PCS', name: 'حبة'},
                {code: 'BAG', name: 'كيس'},
                {code: 'REBAR', name: 'سيخة / عود'},
                {code: 'GAL', name: 'جالون'}
            ];
        } catch(e) {
            UOMS = [
                {code: 'TON', name: 'طن'},
                {code: 'KG', name: 'كيلو'},
                {code: 'PCS', name: 'حبة'},
                {code: 'BAG', name: 'كيس'},
                {code: 'REBAR', name: 'سيخة / عود'},
                {code: 'GAL', name: 'جالون'}
            ];
        }

        let rawItems = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
        const invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        
        if (rawItems.length === 0) {
            console.log('💡 تهيئة أصناف افتراضية للنظام...');
            rawItems = [
                { id: 'item-001', name: 'أسمنت بورتلاندي 50 كجم', unit: 'كيس', baseUnit: 'كيس', sellingPrice: 4500, barcode: '1001', unitsArray: [{ name: 'كيس', factor: 1 }, { name: 'طن', factor: 20 }] },
                { id: 'item-002', name: 'حديد تسليح 12 ملم', unit: 'سيخة / عود', baseUnit: 'سيخة / عود', sellingPrice: 12500, barcode: '1002', unitsArray: [{ name: 'سيخة / عود', factor: 1 }, { name: 'طن', factor: 120 }] },
                { id: 'item-003', name: 'طوب مفرغ 20×20×40', unit: 'حبة', baseUnit: 'حبة', sellingPrice: 650, barcode: '1003', unitsArray: [{ name: 'حبة', factor: 1 }, { name: 'ألف حبة', factor: 1000 }] },
                { id: 'item-004', name: 'رمل بناء ممتاز', unit: 'متر مكعب', baseUnit: 'متر مكعب', sellingPrice: 8500, barcode: '1004', unitsArray: [{ name: 'متر مكعب', factor: 1 }, { name: 'قلاب 12متر', factor: 12 }] },
                { id: 'item-005', name: 'دهان بلاستيك مط داخلي', unit: 'جالون', baseUnit: 'جالون', sellingPrice: 14000, barcode: '1005', unitsArray: [{ name: 'جالون', factor: 1 }, { name: 'كرتونة (4 جالون)', factor: 4 }] }
            ];
            localStorage.setItem('erp_items_v1', JSON.stringify(rawItems));
        }

        ITEMS = rawItems.map(item => {
            let stockMap = {};
            let itemInv = invData.filter(inv => inv.itemId === item.id);
            itemInv.forEach(inv => {
                let whId = WAREHOUSES.find(w => w.name === inv.warehouse)?.id || 'w-001';
                stockMap[whId] = (stockMap[whId] || 0) + (parseFloat(inv.qty) || parseFloat(inv.quantity) || 0);
            });

            let sp = item.sellingPrice || item.salePrice || item.price || 0;
            sp = sp.toString().replace(/,/g, '');

            const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'قطعة';

            let unitsArray = [];
            if (item.units && item.units.length > 0) {
                unitsArray = item.units.map(u => ({ name: u.unitName, factor: u.factor || 1 }));
            } else if (item.unitsArray && item.unitsArray.length > 0) {
                unitsArray = item.unitsArray;
            }
            if (unitsArray.length === 0 || !unitsArray.find(u => u.name === baseUnit)) {
                unitsArray = [{ name: baseUnit, factor: 1 }, ...unitsArray];
            }

            return {
                id: item.id,
                name: item.name,
                unit: baseUnit,
                baseUnit: baseUnit,
                conversionFactor: 1, 
                bundleFactor: parseFloat(item.bundleFactor) || parseFloat(item.shila) || parseFloat(item.bundleQty) || 1,
                price: parseFloat(sp) || 0,
                stock: stockMap,
                unitsArray: unitsArray,
                barcode: item.barcode || '',
                image: item.image || item.photo || item.img || item.thumbnail || ''
            };
        });

        populateDropdowns();
        initHeader();
        if (typeof GridCustomizer !== 'undefined') {
            GridCustomizer.init('purchase_grid', 'pos-grid', () => {
                renderGrid();
                recalcAll();
                initMultiPaymentSystem();
                if (typeof loadHubTransactions === 'function') loadHubTransactions();
            });
        } else {
            renderGrid();
            recalcAll();
            initMultiPaymentSystem();
            if (typeof loadHubTransactions === 'function') loadHubTransactions();
        }
    };

    window.refreshAllMasterData();

    // Auto-sync items across tabs dynamically
    const localSyncChannel = new BroadcastChannel('erp_db_sync');
    localSyncChannel.addEventListener('message', (ev) => {
        if (ev.data && ev.data.type === 'SYNC_KEY') {
            const key = ev.data.tenantKey || '';
            if (key.includes('erp_items_v1') || key.includes('erp_inventory_v1') || key.includes('erp_warehouses_v1') || key.includes('erp_suppliers_v1')) {
                window.refreshAllMasterData();
            }
        }
    });

    // Deep Drill-down: Check if URL has ?id= to load specific invoice
    const urlParams = new URLSearchParams(window.location.search);
    const invoiceId = urlParams.get('id');
    if (invoiceId) {
        // Try direct load by invoice number (or id)
        const loaded = loadInvoiceFromStorage(invoiceId);
        if (!loaded) {
            // Sometimes id is the UUID, we need to find the invoiceNo
            const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
            const inv = purchases.find(p => String(p.id) === String(invoiceId) || String(p.invoiceNo) === String(invoiceId));
            if (inv && inv.invoiceNo) {
                loadInvoiceFromStorage(inv.invoiceNo);
            }
        }
    } else {
        // Initialize an empty row by default if creating a new invoice
        if (rows.length === 0) {
            addRow(true);
        }
    }

    // Keyboard shortcuts & Grid Navigation
    document.addEventListener('keydown', e => {
        // Global Shortcuts
        if (e.ctrlKey && e.key === 's') { e.preventDefault(); saveDraft(); }
        if (e.ctrlKey && e.key === 'n') { e.preventDefault(); addRow(); }
        if (e.key === 'Escape') { document.querySelectorAll('.modal-overlay.visible').forEach(m => m.classList.remove('visible')); }
        
        if (e.key === 'F2') {
            e.preventDefault();
            submitInvoice();
        }
        if (e.key === 'F8') {
            e.preventDefault();
            generatePrintArea();
            window.print();
        }

        // Custom Dropdown Keyboard Navigation
        let activeDropdown = document.querySelector('.custom-dropdown-list.show');
        if (activeDropdown) {
            let items = Array.from(activeDropdown.querySelectorAll('.custom-dropdown-item'));
            let activeIdx = items.findIndex(i => i.classList.contains('selected'));
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (activeIdx < items.length - 1) {
                    if (activeIdx >= 0) items[activeIdx].classList.remove('selected');
                    items[activeIdx + 1].classList.add('selected');
                    items[activeIdx + 1].scrollIntoView({ block: 'nearest' });
                }
                return;
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (activeIdx > 0) {
                    items[activeIdx].classList.remove('selected');
                    items[activeIdx - 1].classList.add('selected');
                    items[activeIdx - 1].scrollIntoView({ block: 'nearest' });
                }
                return;
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                let selectedItem = activeIdx >= 0 ? items[activeIdx] : items[0];
                if (selectedItem) {
                    selectedItem.dispatchEvent(new MouseEvent('mousedown'));
                }
                return;
            }
        }

        // Enter Navigation in Grid
        if (e.key === 'Enter') {
            const target = e.target;
            
            // 1. Quick Search Box (Header)
            if (target.id === 'quick-item-search') {
                e.preventDefault();
                const firstItem = document.querySelector('#quick-item-dropdown-list .custom-dropdown-item');
                if (firstItem && document.getElementById('quick-item-dropdown-list').classList.contains('show')) {
                    firstItem.dispatchEvent(new MouseEvent('mousedown'));
                    setTimeout(() => {
                        if (rows.length > 0) {
                            const lastRowId = rows[rows.length - 1].id;
                            const qtyInput = document.querySelector(`[data-qty-input="${lastRowId}"]`);
                            if (qtyInput) { qtyInput.focus(); qtyInput.select(); }
                        }
                    }, 50);
                }
                return;
            }

            // 2. Grid Inputs (Item Search -> Qty -> Price -> New Row)
            if (target.classList.contains('g-input') || target.classList.contains('ctrl-input')) {
                e.preventDefault(); 
                
                if (target.id && target.id.startsWith('item-search-')) {
                    const rowId = target.id.replace('item-search-', '');
                    const listEl = document.getElementById(`item-dropdown-list-${rowId}`);
                    if (listEl && listEl.classList.contains('show')) {
                        const firstItem = listEl.querySelector('.custom-dropdown-item');
                        if (firstItem) {
                            firstItem.dispatchEvent(new MouseEvent('mousedown'));
                        }
                    }
                    setTimeout(() => {
                        const qtyInput = document.querySelector(`[data-qty-input="${rowId}"]`);
                        if (qtyInput) { qtyInput.focus(); qtyInput.select(); }
                    }, 50);
                } 
                else if (target.hasAttribute('data-qty-input')) {
                    const rowId = target.getAttribute('data-qty-input');
                    const priceInput = document.querySelector(`[data-price-input="${rowId}"]`);
                    if (priceInput) { priceInput.focus(); priceInput.select(); }
                }
                else if (target.hasAttribute('data-price-input')) {
                    addRow();
                    setTimeout(() => {
                        if (rows.length > 0) {
                            const newRowId = rows[rows.length - 1].id;
                            const nextSearch = document.getElementById(`item-search-${newRowId}`);
                            if (nextSearch) { nextSearch.focus(); }
                        }
                    }, 50);
                }
            }
        }
    });
}

if (window.APP_READY) {
    initPurchaseApp();
} else {
    document.addEventListener('DBReady', initPurchaseApp);
}

function populateDropdowns() {
    // Customers (Init custom dropdown)
    document.getElementById('hdr-customer').value = '';
    
    // Warehouses (Header)
    const whSel = document.getElementById('hdr-warehouse');
    whSel.innerHTML = '';
    WAREHOUSES.forEach(w => {
        const o = document.createElement('option');
        o.value = w.id; o.textContent = w.name;
        whSel.appendChild(o);
    });
    
    // Cost Centers
    const ccSel = document.getElementById('hdr-cost-center');
    if (ccSel) {
        ccSel.innerHTML = '<option value="">-- بدون مركز تكلفة --</option>';
        try {
            const costCenters = JSON.parse(localStorage.getItem('erp_jobs_v1')) || [];
            costCenters.forEach(cc => {
                const o = document.createElement('option');
                o.value = cc.id; o.textContent = cc.name;
                ccSel.appendChild(o);
            });
        } catch(e) {}
    }
    
    // Init Multi-Payment System
    initMultiPaymentSystem();
}

function initHeader() {
    // Invoice number — peek only (no increment)
    const invNo = peekNextInvoiceNumber(currentSaleType);
    document.getElementById('inv-number').textContent = invNo;
    document.getElementById('hdr-inv-no').value = invNo;

    // Date
    const __d = new Date();
    const __y = __d.getFullYear();
    const __m = String(__d.getMonth() + 1).padStart(2, '0');
    const __day = String(__d.getDate()).padStart(2, '0');
    const today = `${__y}-${__m}-${__day}`;
    document.getElementById('hdr-date').value = today;
}

// ════════════════════════════════════════
//  SALE TYPE TABS
// ════════════════════════════════════════
function setSaleType(type) {
    currentSaleType = type;
    
    // Refresh all master data when switching invoice types
    if (window.refreshAllMasterData) {
        window.refreshAllMasterData();
    }
    
    const badge = document.getElementById('current-sale-type-badge');
    if (badge) {
        if (type === 'direct') badge.innerHTML = '🛒 فاتورة مشتريات مباشرة';
        else if (type === 'split') badge.innerHTML = '🏭 مصنع';
        else if (type === 'delivery') badge.innerHTML = '🚚 استيراد';
        else if (type === 'proforma') badge.innerHTML = '📋 فاتورة مبدئية';
    }
    
    const invNo = peekNextInvoiceNumber(type);
    document.getElementById('inv-number').textContent = invNo;
    document.getElementById('hdr-inv-no').value = invNo;
}

// ════════════════════════════════════════
//  CUSTOMER CHANGE → Financial Panel
// ════════════════════════════════════════
function onCustomerChange() {
    updateLedgerButton();
    const id = document.getElementById('hdr-customer').value;
    const cust = VENDORS.find(c => String(c.id) === String(id));
    const cur = document.getElementById('hdr-currency').value;

    if (!cust || id === '') {
        const invBox = document.getElementById('invoice-info-box');
        const finCard = document.getElementById('customer-financial-card');
        if (invBox) invBox.style.flex = "1";
        if(finCard) {
            finCard.style.flex = "0";
            finCard.style.opacity = "0";
            setTimeout(() => { if(finCard.style.opacity === "0") finCard.style.display = 'none'; }, 400);
        }
        
        // Restore fonts
        const invLbl = document.getElementById('hdr-inv-label');
        const invVal = document.getElementById('inv-number');
        const dateLbl = document.getElementById('hdr-date-label');
        const dateVal = document.getElementById('hdr-date');
        if(invLbl) invLbl.style.fontSize = "0.9rem";
        if(invVal) invVal.style.fontSize = "0.9rem";
        if(dateLbl) dateLbl.style.fontSize = "0.9rem";
        if(dateVal) dateVal.style.fontSize = "0.95rem";
        
        ['fin-ledger','fin-checks','fin-total-debt','fin-credit-limit','fin-remaining'].forEach(el => {
            const dom = document.getElementById(el);
            if(dom) dom.textContent = '0';
        });
        const msg = document.getElementById('fin-status-msg');
        if(msg) msg.innerHTML = '<span class="status-dot neutral"></span> لم يتم اختيار مورد';
        return;
    }
    
    const invBox = document.getElementById('invoice-info-box');
    const finCard = document.getElementById('customer-financial-card');
    if (invBox && finCard) {
        finCard.style.display = 'flex';
        setTimeout(() => {
            invBox.style.flex = "0.7";
            finCard.style.flex = "1";
            finCard.style.opacity = "1";
            finCard.style.paddingLeft = "10px";
            
            // Shrink fonts
            const invLbl = document.getElementById('hdr-inv-label');
            const invVal = document.getElementById('inv-number');
            const dateLbl = document.getElementById('hdr-date-label');
            const dateVal = document.getElementById('hdr-date');
            if(invLbl) invLbl.style.fontSize = "0.75rem";
            if(invVal) invVal.style.fontSize = "0.75rem";
            if(dateLbl) dateLbl.style.fontSize = "0.75rem";
            if(dateVal) dateVal.style.fontSize = "0.8rem";
        }, 10);
    }

    const totalDebt = cust.ledger + cust.checks;
    const remaining = cust.creditLimit - totalDebt;

    const els = {
        'fin-ledger': fmtNum(cust.ledger) + ' ' + cur,
        'fin-checks': fmtNum(cust.checks) + ' ' + cur,
        'fin-total-debt': fmtNum(totalDebt) + ' ' + cur,
        'fin-credit-limit': fmtNum(cust.creditLimit) + ' ' + cur,
        'fin-remaining': fmtNum(remaining) + ' ' + cur
    };

    for(let key in els) {
        const dom = document.getElementById(key);
        if(dom) dom.textContent = els[key];
    }

    const statusEl = document.getElementById('fin-status-msg');
    if(statusEl) {
        if (cust.status === 'blacklisted') {
            statusEl.innerHTML = `<span class="status-dot danger"></span> ⛔ تنبيه حرج: هذا المورد في القائمة السوداء!`;
        } else {
            statusEl.innerHTML = `<span class="status-dot good"></span> مورد نشط`;
        }
    }
}

function addNewCustomer() {
    const name = document.getElementById('new-cust-name').value.trim();
    const phone = document.getElementById('new-cust-phone').value.trim();
    const credit = parseNum(document.getElementById('new-cust-credit').value);
    if (!name) { showToast('❌ يرجى إدخال اسم المورد', 'error'); return; }

    const newId = 'v-' + String(Date.now()).slice(-6);
    const newCust = { id:newId, name, phone, ledger:0, checks:0, creditLimit:credit };
    VENDORS.push(newCust);

    // Save to localStorage so it persists
    let supps = [];
    try {
        supps = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        if (!Array.isArray(supps)) supps = [];
    } catch(e) { supps = []; }
    
    supps.push({
        id: newId,
        name: name,
        phone: phone,
        creditLimit: credit,
        currentBalance: 0,
        status: 'active'
    });
    localStorage.setItem('erp_suppliers_v1', JSON.stringify(supps));

    selectCustomer(newId, name);

    closeModal('add-customer-modal');
    document.getElementById('new-cust-name').value = '';
    document.getElementById('new-cust-phone').value = '';
    document.getElementById('new-cust-credit').value = '';
    showToast(`✅ تم إضافة المورد: ${name}`);
}

function loadPreviousInvoice() {
    const currentNoStr = document.getElementById('hdr-inv-no').value;
    let list = getPurchaseInvoicesList();
    
    let previousInvoices = list
        .filter(inv => inv.invoiceNo && inv.invoiceNo < currentNoStr)
        .sort((a, b) => b.invoiceNo.localeCompare(a.invoiceNo));
        
    if (previousInvoices.length > 0) {
        const prevNo = previousInvoices[0].invoiceNo;
        if (loadInvoiceFromStorage(prevNo)) {
            showToast(`✅ تم استرجاع الفاتورة ${prevNo}`);
            return;
        }
    }
    showToast('ℹ️ لا توجد فواتير سابقة محفوظة', 'warning');
}

function loadNextInvoice() {
    const currentNoStr = document.getElementById('hdr-inv-no').value;
    let list = getPurchaseInvoicesList();
    
    let nextInvoices = list
        .filter(inv => inv.invoiceNo && inv.invoiceNo > currentNoStr)
        .sort((a, b) => a.invoiceNo.localeCompare(b.invoiceNo));
        
    if (nextInvoices.length > 0) {
        const nextNo = nextInvoices[0].invoiceNo;
        if (loadInvoiceFromStorage(nextNo)) {
            showToast(`✅ تم استرجاع الفاتورة ${nextNo}`);
            return;
        }
    }
    
    onSuccessNewInvoice();
    showToast('✨ فاتورة جديدة', 'success');
}

function getSinglePurchaseInvoice(invNo) {
    if (!invNo) return null;
    let str = localStorage.getItem('erp_pur_' + invNo) || localStorage.getItem('erp_draft_' + invNo);
    if (str) {
        try { return JSON.parse(str); } catch(e) {}
    }
    let allPurchases = getPurchaseInvoicesList();
    const data = allPurchases.find(inv => String(inv.invoiceNo) === String(invNo) || String(inv.id) === String(invNo));
    if (data) {
        try { localStorage.setItem('erp_pur_' + (data.invoiceNo || invNo), JSON.stringify(data)); } catch(e) {}
        return data;
    }
    return null;
}

function loadInvoiceFromStorage(invNo) {
    let data = getSinglePurchaseInvoice(invNo);
    if (!data) return false;
    
    try {
        // Trigger magical animation
        const formCard = document.getElementById('form-fields-card');
        const tableCard = document.querySelector('.table-container') || (document.getElementById('details-table') ? document.getElementById('details-table').closest('.neo-card') : null);
        if (formCard) {
            formCard.classList.remove('invoice-slide-up-anim');
            void formCard.offsetWidth; // trigger reflow
            formCard.classList.add('invoice-slide-up-anim');
        }
        if (tableCard) {
            tableCard.classList.remove('invoice-slide-up-anim');
            void tableCard.offsetWidth; // trigger reflow
            tableCard.style.animationDelay = '0.1s';
            tableCard.classList.add('invoice-slide-up-anim');
        }

        // If loading a confirmed or posted invoice, enter Edit Mode
        if (data.status === 'confirmed' || data.status === 'posted' || localStorage.getItem('erp_pur_' + invNo)) {
            currentViewedInvoiceNo = data.invoiceNo || data.id;
            showToast('✏️ أنت الآن في وضع التعديل للفاتورة الأصلية', 'warning', 4000);
        } else {
            currentViewedInvoiceNo = null;
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
        if(data.costCenter) { const ccEl = document.getElementById('hdr-cost-center'); if (ccEl) ccEl.value = data.costCenter; }
        if(data.currency) document.getElementById('hdr-currency').value = data.currency;
        if(data.exchangeRate !== undefined) {
            document.getElementById('hdr-exchange-rate').value = data.exchangeRate;
            document.getElementById('hdr-exchange-rate').disabled = (data.currency === 'SDG');
        }

        // Adjustments
        document.getElementById('t-labor').value = data.laborCost !== undefined ? fmtNum(data.laborCost) : '0';
        document.getElementById('t-labor-fc').value = data.laborCostFc !== undefined ? fmtNum(data.laborCostFc) : '0';
        document.getElementById('t-transport').value = data.transportCost !== undefined ? fmtNum(data.transportCost) : '0';
        document.getElementById('t-transport-fc').value = data.transportCostFc !== undefined ? fmtNum(data.transportCostFc) : '0';
        
        document.getElementById('t-additions-loc').value = data.additionsCost !== undefined ? fmtNum(data.additionsCost) : '0';
        document.getElementById('t-additions-fc').value = data.additionsCostFc !== undefined ? fmtNum(data.additionsCostFc) : '0';
        document.getElementById('t-tax-loc').value = data.taxCost !== undefined ? fmtNum(data.taxCost) : '0';
        document.getElementById('t-tax-fc').value = data.taxCostFc !== undefined ? fmtNum(data.taxCostFc) : '0';
        document.getElementById('t-discount-loc').value = data.discountCost !== undefined ? fmtNum(data.discountCost) : '0';
        document.getElementById('t-discount-fc').value = data.discountCostFc !== undefined ? fmtNum(data.discountCostFc) : '0';
        
        // Customer
        const custId = data.customer || data.customerId;
        if (custId) {
            const c = VENDORS.find(x => String(x.id) === String(custId));
            if (c) selectCustomer(c.id, c.name);
        }

        // Payments Loading
        document.querySelectorAll('.pay-toggle-btn').forEach(btn => btn.classList.remove('active'));
        if (document.getElementById('pay-cash-section')) document.getElementById('pay-cash-section').style.display = 'none';
        if (document.getElementById('pay-credit-section')) document.getElementById('pay-credit-section').style.display = 'none';
        if (document.getElementById('pay-bank-section')) document.getElementById('pay-bank-section').style.display = 'none';
        if (document.getElementById('pay-cheque-section')) document.getElementById('pay-cheque-section').style.display = 'none';
        
        if (document.getElementById('bank-transfers-list')) document.getElementById('bank-transfers-list').innerHTML = '';
        if (document.getElementById('cheques-list')) document.getElementById('cheques-list').innerHTML = '';

        if (data.cashAmount > 0) {
            const btn = document.getElementById('toggle-cash');
            if(btn) btn.classList.add('active');
            if (document.getElementById('pay-cash-section')) document.getElementById('pay-cash-section').style.display = 'flex';
            if (document.getElementById('pay-cash-amount')) document.getElementById('pay-cash-amount').value = data.cashAmount;
        }
        if (data.creditAmount > 0) {
            const btn = document.getElementById('toggle-credit');
            if(btn) btn.classList.add('active');
            if (document.getElementById('pay-credit-section')) document.getElementById('pay-credit-section').style.display = 'flex';
            if (document.getElementById('pay-credit-amount')) document.getElementById('pay-credit-amount').value = data.creditAmount;
            if(data.dueDate && document.getElementById('pay-credit-due-date')) document.getElementById('pay-credit-due-date').value = data.dueDate;
        }
        if (data.bankTransfers && data.bankTransfers.length > 0) {
            const btn = document.getElementById('toggle-bank');
            if(btn) btn.classList.add('active');
            if (document.getElementById('pay-bank-section')) document.getElementById('pay-bank-section').style.display = 'flex';
            data.bankTransfers.forEach(bt => addBankTransferRow(bt));
        }
        if (data.cheques && data.cheques.length > 0) {
            const btn = document.getElementById('toggle-cheque');
            if(btn) btn.classList.add('active');
            if (document.getElementById('pay-cheque-section')) document.getElementById('pay-cheque-section').style.display = 'flex';
            data.cheques.forEach(ch => addChequeRow(ch));
        }
        
        if (data.paymentAccountId) {
            const payAccEl = document.getElementById('hdr-payment-account');
            if (payAccEl) payAccEl.value = data.paymentAccountId;
        }
        
        // Rows — PERFORMANCE: assign properties directly instead of calling
        // updateRow() (which fires recalcAll on every call). We call renderGrid
        // and recalcAll exactly ONCE after all rows are populated.
        const itemsList = data.items || data.products || [];
        if(itemsList && itemsList.length) {
            itemsList.forEach(i => {
                addRow(true, true); // silent — no re-render per row
                const r = rows[rows.length-1];
                
                // Direct property assignment (no recalcAll per field)
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
        if (window.applyImmutabilityUI) window.applyImmutabilityUI(data.status);
        if (typeof updatePostingStatusBar === 'function') updatePostingStatusBar(data.status);
        if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
        return true;
    } catch(e) {
        console.error('Error loading invoice', e);
        return false;
    }
}

// ════════════════════════════════════════
//  CUSTOMERS DROPDOWN LOGIC
// ════════════════════════════════════════
function normalizeArabic(text) {
    if (!text) return '';
    return String(text).replace(/[أإآا]/g, 'ا')
               .replace(/ة/g, 'ه')
               .replace(/[يى]/g, 'ى')
               .toLowerCase();
}

async function renderCustomerDropdown(filter = '') {
    const listEl = document.getElementById('customer-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = normalizeArabic(term);
    
    try {
        // Sync VENDORS with localStorage live safely
        let supps = [];
        try {
            supps = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
            if (!Array.isArray(supps)) supps = [];
        } catch (e) {
            supps = [];
        }

        supps.forEach(s => {
            if (!s || !s.id) return;
            let existing = VENDORS.find(v => String(v.id) === String(s.id));
            if (existing) {
                existing.phone = s.phone || '';
                existing.code = s.code || '';
                existing.name = s.name || 'بدون اسم';
            } else {
                VENDORS.push({
                    id: s.id, name: s.name || 'بدون اسم', phone: s.phone || '', code: s.code || '',
                    ledger: parseFloat(s.currentBalance) || 0, checks: 0,
                    creditLimit: s.creditLimit || 0
                });
            }
        });

        let localMatches = VENDORS;
        if (normalizedTerm) {
            const searchWords = normalizedTerm.split(/\s+/).filter(w => w.length > 0);
            
            localMatches = VENDORS.filter(c => {
                const normName = normalizeArabic(c.name);
                const normPhone = String(c.phone || '');
                const normCode = String(c.code || '').toLowerCase();
                
                // Smart Filter: Every word typed must be present in EITHER name, phone, or code
                return searchWords.every(word => 
                    normName.includes(word) || 
                    normPhone.includes(word) || 
                    normCode.includes(word)
                );
            });
        }

        const finalMatches = Array.from(new Map(localMatches.map(m => [String(m.id), m])).values()).slice(0, 50);

        listEl.innerHTML = '';
        if (finalMatches.length === 0) {
            listEl.innerHTML = `<div class="custom-dropdown-empty">لا توجد نتائج مطابقة للبحث.<br><small style="color:#888;">(إجمالي الموردين: ${VENDORS.length})</small></div>`;
            return;
        }

        finalMatches.forEach(c => {
            const item = document.createElement('div');
            item.className = 'custom-dropdown-item';
            
            const nameH = window.highlightSearchMatches ? window.highlightSearchMatches(c.name, filter) : c.name;
            const phoneH = c.phone ? (window.highlightSearchMatches ? window.highlightSearchMatches(String(c.phone), filter) : c.phone) : '';
            const codeH = c.code ? (window.highlightSearchMatches ? window.highlightSearchMatches(String(c.code), filter) : c.code) : '';

            item.innerHTML = `<div style="display: flex; justify-content: space-between; align-items: center; width: 100%; text-align: right; dir: rtl;"><div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap;"><span style="font-weight: bold; color: #fff;">${nameH}</span>${phoneH ? `<span style="font-size:0.8rem; color:#94a3b8;">(${phoneH})</span>` : ''}${codeH ? `<span style="font-size:0.8rem; color:#64748b;">[${codeH}]</span>` : ''}</div></div>`;
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
        const wrap = listEl.closest('.custom-dropdown-input-wrap') || listEl.closest('.custom-dropdown') || listEl.closest('.h-form-group');
        if (wrap) {
            wrap.style.zIndex = '9999999';
            wrap.style.position = 'relative';
        }
    }
}

let hideDropdownTimeout;

function hideCustomerDropdownDelayed() {
    clearTimeout(hideDropdownTimeout);
    hideDropdownTimeout = setTimeout(() => {
        // If the user is still focused on the search input, do NOT close the dropdown
        if (document.activeElement && document.activeElement.id === 'hdr-customer-search') {
            return;
        }

        const listEl = document.getElementById('customer-dropdown-list');
        if(listEl) {
            listEl.classList.remove('show');
            const wrap = listEl.closest('.custom-dropdown-input-wrap') || listEl.closest('.custom-dropdown') || listEl.closest('.h-form-group');
            if (wrap) {
                wrap.style.zIndex = '';
            }
        }
        
        const idVal = document.getElementById('hdr-customer').value;
        const searchVal = document.getElementById('hdr-customer-search').value.trim();
        if (idVal) {
            const cust = VENDORS.find(c => String(c.id) === String(idVal));
            if (cust && cust.name !== searchVal) {
                document.getElementById('hdr-customer-search').value = cust.name;
            }
        } else if (searchVal) {
            const exactMatch = VENDORS.find(c => c.name === searchVal);
            if (exactMatch) {
                selectCustomer(exactMatch.id, exactMatch.name);
            }
        } else {
            document.getElementById('hdr-customer-search').value = '';
        }
    }, 250);
}

function filterCustomerDropdown() {
    document.getElementById('hdr-customer').value = '';
    updateLedgerButton();
    onCustomerChange();
    renderCustomerDropdown(document.getElementById('hdr-customer-search').value);
    document.getElementById('customer-dropdown-list').classList.add('show');
}

function selectCustomer(id, name) {
    document.getElementById('hdr-customer').value = id;
    document.getElementById('hdr-customer-search').value = name;
    document.getElementById('customer-dropdown-list').classList.remove('show');
    updateLedgerButton();
    onCustomerChange();
}

function updateLedgerButton() {
    const custVal = document.getElementById('hdr-customer') ? document.getElementById('hdr-customer').value : '';
    const btn = document.getElementById('hdr-customer-ledger-btn');
    if (btn) {
        btn.style.display = custVal ? 'inline-flex' : 'none';
    }
}

// ════════════════════════════════════════
//  ITEMS DROPDOWN LOGIC
// ════════════════════════════════════════
async function renderItemDropdown(rowId, isMobile = false, filter = '') {
    const prefix = isMobile ? 'm-' : '';
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    if (!listEl) return;
    
    const term = filter.toLowerCase().trim();
    
    try {
        // 1. Ensure local ITEMS array is populated from localStorage
        const invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        const WAREHOUSES = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
        const rawItems = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
        
        rawItems.forEach(item => {
            let existing = ITEMS.find(i => String(i.id) === String(item.id));
            if (!existing) {
                let stockMap = {};
                let itemInv = invData.filter(inv => String(inv.itemId) === String(item.id));
                itemInv.forEach(inv => {
                    let whId = WAREHOUSES.find(w => w.name === inv.warehouse)?.id || 'w-001';
                    stockMap[whId] = (stockMap[whId] || 0) + (parseFloat(inv.qty) || parseFloat(inv.quantity) || 0);
                });

                let sp = item.sellingPrice || item.salePrice || item.price || 0;
                sp = sp.toString().replace(/,/g, '');
                const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'قطعة';

                let unitsArray = [];
                if (item.units && item.units.length > 0) {
                    unitsArray = item.units.map(u => ({ name: u.unitName, factor: u.factor || 1 }));
                } else if (item.unitsArray && item.unitsArray.length > 0) {
                    unitsArray = item.unitsArray;
                }
                if (unitsArray.length === 0 || !unitsArray.find(u => u.name === baseUnit)) {
                    unitsArray = [{ name: baseUnit, factor: 1 }, ...unitsArray];
                }

                existing = {
                    id: item.id,
                    name: item.name,
                    unit: baseUnit,
                    baseUnit: baseUnit,
                    conversionFactor: 1, 
                    bundleFactor: parseFloat(item.bundleFactor) || parseFloat(item.shila) || parseFloat(item.bundleQty) || 1,
                    price: parseFloat(sp) || 0,
                    stock: stockMap,
                    unitsArray: unitsArray,
                    barcode: item.barcode || '',
                    image: item.image || item.photo || item.img || item.thumbnail || ''
                };
                ITEMS.push(existing);
            }
        });

        // Function to render items into listEl
        const renderList = (matches) => {
            const finalMatches = Array.from(new Map(matches.map(m => [String(m.id), m])).values());
            if (finalMatches.length === 0) {
                listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة.<br><small style="color:#888;">(حاول استخدام اسم آخر)</small></div>';
                return;
            }

            listEl.innerHTML = '';
            finalMatches.slice(0, 50).forEach(existing => {
                const div = document.createElement('div');
                div.className = 'custom-dropdown-item';
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                div.style.gap = '10px';
                
                let imgHtml = '';
                if (existing.image) {
                    imgHtml = `<img src="${existing.image}" style="width:28px; height:28px; border-radius:6px; object-fit:cover; border:1px solid rgba(56,189,248,0.3); flex-shrink:0;">`;
                } else {
                    imgHtml = `<div style="width:28px; height:28px; border-radius:6px; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.25); color:#38bdf8; display:flex; align-items:center; justify-content:center; font-size:0.8rem; flex-shrink:0;">📦</div>`;
                }

                const nameH = window.highlightSearchMatches ? window.highlightSearchMatches(existing.name, filter) : existing.name;
                const barcodeH = existing.barcode ? (window.highlightSearchMatches ? window.highlightSearchMatches(String(existing.barcode), filter) : existing.barcode) : '';
                let textHtml = `<div style="flex:1;"><span>${nameH}</span>`;
                if (isMobile && existing.unit) textHtml += ` <span style="font-size:0.75rem; color:#aaa;">— ${existing.unit}</span>`;
                if (barcodeH) textHtml += ` <span style="font-size:0.75rem; color:#888;">[${barcodeH}]</span>`;
                textHtml += `</div>`;

                div.innerHTML = imgHtml + textHtml;
                
                div.onmousedown = (e) => { e.preventDefault(); updateRow(rowId, 'itemId', existing.id); renderGrid(); };
                div.onpointerdown = (e) => { e.preventDefault(); updateRow(rowId, 'itemId', existing.id); renderGrid(); };
                listEl.appendChild(div);
            });
        };

        // Filter local matches
        let localMatches = ITEMS;
        if (term) {
            localMatches = ITEMS.filter(i => {
                const normName = (i.name || '').toLowerCase();
                const nameWords = normName.split(' ');
                const bcMatches = i.barcode && i.barcode.toLowerCase().startsWith(term);
                return nameWords.some(w => w.startsWith(term)) || normName.includes(term) || bcMatches;
            });
        }

        // Render local items instantly (0ms delay)
        renderList(localMatches);

        // Async API fetch for server items (non-blocking)
        try {
            const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
            const headers = token ? { 'Authorization': 'Bearer ' + token } : {};
            const res = await fetch('api/items_search.php?q=' + encodeURIComponent(term), { headers });
            if (res.ok) {
                const rawMatches = await res.json();
                if (Array.isArray(rawMatches) && rawMatches.length > 0) {
                    rawMatches.forEach(item => {
                        let existing = ITEMS.find(i => String(i.id) === String(item.id));
                        if (!existing) {
                            const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'قطعة';
                            existing = {
                                id: item.id,
                                name: item.name,
                                unit: baseUnit,
                                baseUnit: baseUnit,
                                conversionFactor: 1,
                                bundleFactor: 1,
                                price: parseFloat(item.sellingPrice || item.price || 0),
                                stock: {},
                                unitsArray: [{ name: baseUnit, factor: 1 }],
                                barcode: item.barcode || ''
                            };
                            ITEMS.push(existing);
                            localMatches.push(existing);
                        }
                    });
                    renderList(localMatches);
                }
            }
        } catch(err) {
            // Quietly ignore API errors to keep local UI responsive
        }
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">خطأ في تحويل البيانات</div>';
    }
}

function showItemDropdown(rowId, isMobile = false) {
    const prefix = isMobile ? 'm-' : '';
    // Always show all items when focusing to allow choosing a new item easily
    renderItemDropdown(rowId, isMobile, '');
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    if (listEl) {
        listEl.classList.add('show');
        listEl.style.display = 'flex';
        listEl.style.visibility = 'visible';
        listEl.style.opacity = '1';
        const tr = listEl.closest('tr') || listEl.closest('.m-card');
        if (tr) {
            tr.style.zIndex = '999999';
            tr.style.position = 'relative';
        }
    }
}

function hideItemDropdownDelayed(rowId, isMobile = false) {
    setTimeout(() => {
        const prefix = isMobile ? 'm-' : '';
        const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
        if(listEl) {
            listEl.classList.remove('show');
            const tr = listEl.closest('tr') || listEl.closest('.m-card');
            if (tr) {
                tr.style.zIndex = '';
            }
        }
        
        const row = rows.find(r => r.id === rowId);
        const searchEl = document.getElementById(`${prefix}item-search-${rowId}`);
        if (searchEl) {
            // Auto-select exact match (e.g. from Barcode Scanner or typing full name)
            const val = searchEl.value.trim().toLowerCase();
            if ((!row || !row.itemId) && val !== '') {
                const exactItem = ITEMS.find(i => i.name.toLowerCase() === val || (i.barcode && i.barcode.toLowerCase() === val));
                if (exactItem) {
                    updateRow(rowId, 'itemId', exactItem.id);
                    renderGrid();
                    return;
                }
                const partialItem = ITEMS.find(i => i.name.toLowerCase().includes(val));
                if (partialItem) {
                    updateRow(rowId, 'itemId', partialItem.id);
                    renderGrid();
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
            }
        }
    }, 250);
}

function filterItemDropdown(rowId, isMobile = false) {
    updateRow(rowId, 'itemId', ''); // clear id
    const prefix = isMobile ? 'm-' : '';
    const searchEl = document.getElementById(`${prefix}item-search-${rowId}`);
    renderItemDropdown(rowId, isMobile, searchEl.value);
    document.getElementById(`${prefix}item-dropdown-list-${rowId}`).classList.add('show');
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
                const normName = i.name.toLowerCase();
                const nameWords = normName.split(' ');
                const bcMatches = i.barcode && i.barcode.toLowerCase().startsWith(term);
                return nameWords.some(w => w.startsWith(term)) || normName.includes(term) || bcMatches;
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
        
        const invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        const WAREHOUSES = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
        const rawItems = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
        
        const allNew = [...rawItems, ...dbMatches];
        allNew.forEach(item => {
            let existing = ITEMS.find(i => String(i.id) === String(item.id));
            if (!existing) {
                let stockMap = {};
                let itemInv = invData.filter(inv => String(inv.itemId) === String(item.id));
                itemInv.forEach(inv => {
                    let whId = WAREHOUSES.find(w => w.name === inv.warehouse)?.id || 'w-001';
                    stockMap[whId] = (stockMap[whId] || 0) + (parseFloat(inv.qty) || parseFloat(inv.quantity) || 0);
                });

                let sp = item.sellingPrice || item.salePrice || item.price || 0;
                sp = sp.toString().replace(/,/g, '');
                const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'قطعة';

                let unitsArray = [];
                if (item.units && item.units.length > 0) {
                    unitsArray = item.units.map(u => ({ name: u.unitName, factor: u.factor || 1 }));
                } else if (item.unitsArray && item.unitsArray.length > 0) {
                    unitsArray = item.unitsArray;
                }
                if (unitsArray.length === 0 || !unitsArray.find(u => u.name === baseUnit)) {
                    unitsArray = [{ name: baseUnit, factor: 1 }, ...unitsArray];
                }

                existing = {
                    id: item.id,
                    name: item.name,
                    unit: baseUnit,
                    baseUnit: baseUnit,
                    conversionFactor: 1, 
                    bundleFactor: parseFloat(item.bundleFactor) || parseFloat(item.shila) || parseFloat(item.bundleQty) || 1,
                    price: parseFloat(sp) || 0,
                    stock: stockMap,
                    unitsArray: unitsArray
                };
                ITEMS.push(existing);
                localMatches.push(existing);
            }
        });

        const finalMatches = Array.from(new Map(localMatches.map(m => [String(m.id), m])).values());

        if (finalMatches.length === 0) {
            listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة.<br><small style="color:#888;">(حاول استخدام اسم آخر)</small></div>';
            return;
        }

        listEl.innerHTML = '';
        finalMatches.slice(0, 50).forEach(existing => {
            const div = document.createElement('div');
            div.className = 'custom-dropdown-item';
            
            const nameH = window.highlightSearchMatches ? window.highlightSearchMatches(existing.name, filter) : existing.name;
            const barcodeH = existing.barcode ? (window.highlightSearchMatches ? window.highlightSearchMatches(String(existing.barcode), filter) : existing.barcode) : '';
            let displayText = nameH;
            if (existing.unit) displayText += ' <span style="font-size:0.8rem; color:#aaa;">— ' + existing.unit + '</span>';
            if (barcodeH) displayText += ' <span style="font-size:0.8rem; color:#888;">[' + barcodeH + ']</span>';
            div.innerHTML = displayText;
            
            div.onmousedown = () => selectQuickItem(existing.id);
            div.onpointerdown = () => selectQuickItem(existing.id);
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
    if (listEl) listEl.classList.add('show');
}

function hideQuickItemDropdownDelayed() {
    setTimeout(() => {
        const listEl = document.getElementById('quick-item-dropdown-list');
        if(listEl) listEl.classList.remove('show');
    }, 150);
}

function filterQuickItemDropdown() {
    const searchEl = document.getElementById('quick-item-search');
    renderQuickItemDropdown(searchEl.value);
    document.getElementById('quick-item-dropdown-list').classList.add('show');
}

function selectQuickItem(itemId) {
    addRow();
    const lastRow = rows[rows.length - 1];
    updateRow(lastRow.id, 'itemId', itemId);
    
    const searchEl = document.getElementById('quick-item-search');
    if(searchEl) searchEl.value = '';
    const listEl = document.getElementById('quick-item-dropdown-list');
    if(listEl) listEl.classList.remove('show');
    
    renderGrid();
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
function addRow(quiet = false, skipRender = false) {
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
    if (!quiet) showToast('📦 تم إضافة صنف جديد', 'success', 1500);
}

function removeRow(id) {
    rows = rows.filter(r => r.id !== id);
    renderGrid();
    recalcAll();
    showToast('🗑️ تم حذف الصنف', 'warning', 1500);
}

function updateRow(id, field, value) {
    const row = rows.find(r => r.id === id);
    if (!row) return;

    if (field === 'quantity' || field === 'price' || field === 'customFactor' || field === 'customBaseQty' || field === 'fcPrice') {
        row[field] = parseNum(value);
        if (field === 'quantity' || field === 'customFactor') {
            row.customBaseQty = undefined; // reset so it auto-calculates
        }
        if (field === 'fcPrice') {
            const exRate = parseNum(document.getElementById('hdr-exchange-rate').value) || 1;
            const isBaseSDG = document.getElementById('hdr-currency').value === 'SDG';
            if (isBaseSDG) {
                row.price = row.fcPrice * exRate;
            } else {
                row.price = row.fcPrice / exRate;
            }
        }
        if (field === 'price') {
            const exRate = parseNum(document.getElementById('hdr-exchange-rate').value) || 1;
            const isBaseSDG = document.getElementById('hdr-currency').value === 'SDG';
            if (isBaseSDG) {
                row.fcPrice = row.price / exRate;
            } else {
                row.fcPrice = row.price * exRate;
            }
        }
        if (field === 'customBaseQty') {
            const item = ITEMS.find(i => String(i.id) === String(row.itemId));
            let factor = row.customFactor !== undefined ? row.customFactor : 1;
            if (row.customFactor === undefined && item && row.selectedUnit && item.unitsArray && item.unitsArray.length > 0) {
                const su = item.unitsArray.find(u => u.name === row.selectedUnit);
                if (su) factor = su.factor;
            }
            const conv = item ? item.conversionFactor : 1;
            if (factor * conv > 0) {
                row.quantity = row.customBaseQty / (factor * conv);
            }
        }
    } else {
        row[field] = value;
        if (field === 'itemId') {
            row.customBaseQty = undefined;
            // Use already-mapped ITEMS (which has proper unitsArray), do NOT reload raw localStorage
            const item = ITEMS.find(i => String(i.id) === String(value));
            if (item) {
                row.price = item.price;
                // Default to first unit in unitsArray (the base unit)
                if (item.unitsArray && item.unitsArray.length > 0) {
                    row.selectedUnit = item.unitsArray[0].name;
                    row.customFactor = item.unitsArray[0].factor;
                } else {
                    row.selectedUnit = item.unit || '';
                    row.customFactor = 1;
                }
            }
        } else if (field === 'selectedUnit') {
            row.customBaseQty = undefined;
            const item = ITEMS.find(i => String(i.id) === String(row.itemId));
            if (item && item.unitsArray) {
                const su = item.unitsArray.find(u => u.name === value);
                if (su) {
                    row.customFactor = su.factor;
                }
            }
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
    const availQty = item && item.stock[whId] !== undefined ? item.stock[whId] : '—';
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
    const bundleEl = document.querySelector(`[data-bundle="${id}"]`);
    const shilaEl = document.querySelector(`[data-shila="${id}"]`);
    const shilaRemEl = document.querySelector(`[data-shila-rem="${id}"]`);
    const priceEl = document.querySelector(`[data-price-input="${id}"]`);
    const fcPriceEl = document.querySelector(`[data-fcprice-input="${id}"]`);
    const totalFcEl = document.querySelector(`[data-total-fc="${id}"]`);

    if (totalEl) totalEl.textContent = fmtNum(calcs.total, 'total');
    if (baseEl && document.activeElement !== baseEl) baseEl.value = fmtNum(calcs.baseQty, 'baseqty');
    if (bundleEl) bundleEl.textContent = fmtNum(calcs.bundles, 'shila');
    if (shilaEl) shilaEl.textContent = fmtNum(calcs.shilaQty, 'shila');
    if (shilaRemEl) shilaRemEl.textContent = fmtNum(calcs.shilaRem, 'shila-rem');
    if (priceEl && document.activeElement !== priceEl) priceEl.value = fmtNum(row.price, 'price');

    const isBaseSDG = document.getElementById('hdr-currency').value === 'SDG';
    const exRate = parseNum(document.getElementById('hdr-exchange-rate').value) || 1;
    let computedFcPrice = row.fcPrice !== undefined ? row.fcPrice : (isBaseSDG ? row.price / exRate : row.price * exRate);
    
    if (fcPriceEl && document.activeElement !== fcPriceEl) fcPriceEl.value = fmtNum(computedFcPrice, 'fcprice');
    if (totalFcEl) totalFcEl.textContent = fmtNum(computedFcPrice * (row.quantity || 0), 'fctotal');

    if (availEl) {
        availEl.textContent = calcs.availQty === '—' ? '—' : fmtNum(calcs.availQty, 'available');
        availEl.className = 'g-readonly ' + (calcs.availQty !== '—' && calcs.availQty < row.quantity ? 'warn-val' : 'avail-val');
    }

    // Mobile
    const mTotal = document.querySelector(`[data-m-total="${id}"]`);
    if (mTotal) mTotal.textContent = fmtNum(calcs.total) + ' ' + getCurrency();

    const mFcPriceEl = document.querySelector(`[data-m-fcprice-input="${id}"]`);
    const mTotalFcEl = document.querySelector(`[data-m-total-fc="${id}"]`);
    if (mFcPriceEl && document.activeElement !== mFcPriceEl) mFcPriceEl.value = fmtNum(computedFcPrice);
    if (mTotalFcEl) mTotalFcEl.textContent = fmtNum(computedFcPrice * (row.quantity || 0));

    const mShilaEl = document.querySelector(`[data-m-shila="${id}"]`);
    const mShilaRemEl = document.querySelector(`[data-m-shila-rem="${id}"]`);
    if (mShilaEl) mShilaEl.textContent = fmtNum(calcs.shilaQty);
    if (mShilaRemEl) mShilaRemEl.textContent = fmtNum(calcs.shilaRem);
}

function getCurrency() {
    return document.getElementById('hdr-currency').value;
}

// ════════════════════════════════════════
//  ADVANCED COLUMN SETTINGS
// ════════════════════════════════════════
function openAdvancedColSettings(colId) {
    // Deprecated
}
function saveAdvancedColSettings() {
    // Deprecated
}

// ════════════════════════════════════════
//  RENDER GRID
// ════════════════════════════════════════
function renderGridHead() {
    const thead = document.getElementById('grid-head');
    if (!thead) return;
    
    const isBaseSDG = (getCurrency() === 'SDG');
    const priceHeader = isBaseSDG ? 'السعر' : 'السعر (دولار)';
    const totalHeader = isBaseSDG ? 'الجملة' : 'الجملة (دولار)';

    thead.innerHTML = `
        <tr>
            <th data-col-id="col_index" style="width: 40px; text-align: center; font-size: 1.05rem;">م</th>
            <th data-col-id="item" style="width: 25%; text-align: right; font-size: 1.25rem; font-weight: bold;">الصنف</th>
            <th data-col-id="unit" style="width: 110px; text-align: center; font-size: 1.25rem; font-weight: bold;">الوحدة</th>
            <th data-col-id="available" style="width: 130px; text-align: center; font-size: 1.25rem; font-weight: bold;" title="الكمية المتوفرة بالمخزن تلقائياً">المتاح</th>
            <th data-col-id="quantity" style="width: 100px; text-align: center; font-size: 1.25rem; font-weight: bold;">الكمية</th>
            <th data-col-id="price" style="width: 140px; text-align: center; font-size: 1.25rem; font-weight: bold;">${priceHeader}</th>
            ${!isBaseSDG ? `<th data-col-id="fcprice" style="width: 120px; text-align: center; font-size: 1.05rem;">سعر (جنيه)</th>` : ''}
            <th style="width: 140px; text-align: center; font-size: 1.05rem;">المخزن</th>
            <th data-col-id="total" style="width: 160px; text-align: center; font-size: 1.25rem; font-weight: bold;">${totalHeader}</th>
            ${!isBaseSDG ? `<th data-col-id="fctotal" style="width: 120px; text-align: center; font-size: 1.05rem;">جملة (جنيه)</th>` : ''}
            <th style="width: 90px; text-align: center; font-size: 1.05rem;">إجراء</th>
        </tr>
    `;
    if (typeof GridCustomizer !== 'undefined') {
        GridCustomizer.attachEvents();
        GridCustomizer.applyStyles();
    }
}

function saveAdvancedColSettings() {
    const colId = document.getElementById('edit-col-id').value;
    const applyToAll = document.getElementById('edit-col-apply-all') ? document.getElementById('edit-col-apply-all').checked : false;
    
    if (colId) {
        const newAlias = document.getElementById('edit-col-alias').value;
        const newColor = document.getElementById('edit-col-color').value;
        const newBgColor = document.getElementById('edit-col-bgcolor').value;
        const newFontSize = document.getElementById('edit-col-fontsize').value;
        const newFontWeight = document.getElementById('edit-col-fontweight').value;
        const newAlign = document.getElementById('edit-col-align').value;
        const newDecimals = parseInt(document.getElementById('edit-col-decimals').value) || 0;
        const newThousands = document.getElementById('edit-col-thousands').value;
        const newWidth = document.getElementById('edit-col-width').value;
        
        gridColumnsConfig[colId].alias = newAlias;
        gridColumnsConfig[colId].color = newColor;
        gridColumnsConfig[colId].backgroundColor = newBgColor;
        gridColumnsConfig[colId].fontSize = newFontSize;
        gridColumnsConfig[colId].fontWeight = newFontWeight;
        gridColumnsConfig[colId].align = newAlign;
        gridColumnsConfig[colId].decimals = newDecimals;
        gridColumnsConfig[colId].thousandsSeparator = newThousands;
        if (newWidth) gridColumnsConfig[colId].width = newWidth;
        
        if (applyToAll) {
            Object.keys(gridColumnsConfig).forEach(k => {
                gridColumnsConfig[k].color = newColor;
                gridColumnsConfig[k].backgroundColor = newBgColor;
                gridColumnsConfig[k].fontSize = newFontSize;
                gridColumnsConfig[k].fontWeight = newFontWeight;
                gridColumnsConfig[k].align = newAlign;
                gridColumnsConfig[k].decimals = newDecimals;
                gridColumnsConfig[k].thousandsSeparator = newThousands;
                if (newWidth) gridColumnsConfig[k].width = newWidth;
            });
        }
    }
    
    // Save global visibility
    Object.keys(gridColumnsConfig).forEach(k => {
        const chk = document.getElementById(`global-vis-${k}`);
        if (chk) {
            gridColumnsConfig[k].visible = chk.checked;
        }
    });
    
    
    window.previewBackupConfig = null; // Clear backup on save
    
    localStorage.setItem('erp_purchase_grid_config', JSON.stringify(gridColumnsConfig));
    closeModal('advanced-col-settings-modal');
    renderGrid();
    recalcAll(); 
}

function previewAdvancedColSettings() {
    const colId = document.getElementById('edit-col-id').value;
    const applyToAll = document.getElementById('edit-col-apply-all') ? document.getElementById('edit-col-apply-all').checked : false;
    
    if (!colId) return;
    
    // Deep copy from backup to reset other columns if applyToAll is toggled off
    if (window.previewBackupConfig) {
        gridColumnsConfig = JSON.parse(JSON.stringify(window.previewBackupConfig));
    }

    const newAlias = document.getElementById('edit-col-alias').value;
    const newColor = document.getElementById('edit-col-color').value;
    const newBgColor = document.getElementById('edit-col-bgcolor').value;
    const newFontSize = document.getElementById('edit-col-fontsize').value;
    const newFontWeight = document.getElementById('edit-col-fontweight').value;
    const newAlign = document.getElementById('edit-col-align').value;
    const newDecimals = parseInt(document.getElementById('edit-col-decimals').value) || 0;
    const newThousands = document.getElementById('edit-col-thousands').value;
    const newWidth = document.getElementById('edit-col-width').value;
    
    gridColumnsConfig[colId].alias = newAlias;
    gridColumnsConfig[colId].color = newColor;
    gridColumnsConfig[colId].backgroundColor = newBgColor;
    gridColumnsConfig[colId].fontSize = newFontSize;
    gridColumnsConfig[colId].fontWeight = newFontWeight;
    gridColumnsConfig[colId].align = newAlign;
    gridColumnsConfig[colId].decimals = newDecimals;
    gridColumnsConfig[colId].thousandsSeparator = newThousands;
    if (newWidth) gridColumnsConfig[colId].width = newWidth;
    
    if (applyToAll) {
        Object.keys(gridColumnsConfig).forEach(k => {
            gridColumnsConfig[k].color = newColor;
            gridColumnsConfig[k].backgroundColor = newBgColor;
            gridColumnsConfig[k].fontSize = newFontSize;
            gridColumnsConfig[k].fontWeight = newFontWeight;
            gridColumnsConfig[k].align = newAlign;
            gridColumnsConfig[k].decimals = newDecimals;
            gridColumnsConfig[k].thousandsSeparator = newThousands;
            if (newWidth) gridColumnsConfig[k].width = newWidth;
        });
    }
    
    renderGrid(); // Live preview render
}

function cancelAdvancedColSettings() {
    if (window.previewBackupConfig) {
        gridColumnsConfig = JSON.parse(JSON.stringify(window.previewBackupConfig));
        window.previewBackupConfig = null;
        renderGrid(); // Revert preview
    }
    closeModal('advanced-col-settings-modal');
}

// ════════════════════════════════════════
//  RENDER GRID
// ════════════════════════════════════════
function renderGrid() {
    renderGridHead();

    const tbody = document.getElementById('grid-body');
    const mCards = document.getElementById('mobile-cards');
    const empty = document.getElementById('empty-state');

    if (tbody) tbody.innerHTML = '';
    if (mCards) mCards.innerHTML = '';

    if (rows.length === 0) {
        addRow(true);
        return;
    }
    empty.style.display = 'none';

    rows.forEach((row, idx) => {
        const item = ITEMS.find(i => String(i.id) === String(row.itemId));
        const calcs = getRowCalcs(row);

        let itemUnitsHtml = '';
        if (item && item.unitsArray && item.unitsArray.length > 0) {
            itemUnitsHtml = item.unitsArray.map(u => `<option value="${u.name}" ${row.selectedUnit === u.name ? 'selected' : ''}>${u.name}</option>`).join('');
        } else {
            itemUnitsHtml = `<option value="${item ? item.unit : ''}">${item ? item.unit : '—'}</option>`;
        }

        const isBaseSDG = (getCurrency() === 'SDG');
        
        let trHtml = `
            <td style="text-align: center;"><div class="row-num">${idx + 1}</div></td>
            <td style="position:relative; text-align: right;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    ${item && item.image ? 
                        `<img src="${item.image}" style="width: 28px; height: 28px; border-radius: 6px; object-fit: cover; border: 1px solid rgba(56,189,248,0.4); flex-shrink: 0;" title="${item.name}">` : 
                        `<div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.25); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0;" title="رمز الصنف">📦</div>`
                    }
                    <div style="position:relative; flex: 1;">
                        <input type="text" class="g-input" id="item-search-${row.id}" placeholder="ابحث أو اختر الصنف..." 
                            value="${item ? item.name : ''}" autocomplete="one-time-code"
                            style="text-align: right; width: 100%; font-size: 1.4rem; font-weight: bold;"
                            onfocus="if(this.closest('tr')) { this.closest('tr').style.zIndex='999999'; this.closest('tr').style.position='relative'; } showItemDropdown(${row.id})" 
                            onblur="hideItemDropdownDelayed(${row.id})" 
                            oninput="filterItemDropdown(${row.id})">
                        <div class="custom-dropdown-list" id="item-dropdown-list-${row.id}" style="text-align:right; min-width: 260px; white-space: normal;">
                        </div>
                    </div>
                </div>
            </td>
            <td style="text-align: center;">
                <select class="g-select" style="width: 100%; font-size: 1.4rem; font-weight: bold;" onchange="updateRow(${row.id},'selectedUnit',this.value); renderGrid();" tabindex="-1">
                    ${itemUnitsHtml}
                </select>
            </td>
            <td style="text-align: center;">
                <span class="g-readonly avail-val" data-avail="${row.id}" style="display: inline-block; width: 100%; text-align: center; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 6px; padding: 5px 2px; font-size: 1.25rem; font-weight: bold; font-family: 'JetBrains Mono', monospace;" title="الكمية المتوفرة حالياً بالمخزن تلقائياً">
                    ${row.availableQty !== undefined ? fmtNum(row.availableQty, 'available') : '0'}
                </span>
            </td>
            <td style="text-align: center;">
                <input type="text" inputmode="decimal" class="g-input" data-qty-input="${row.id}" value="${Number.isInteger(row.quantity) ? row.quantity : parseFloat((row.quantity || 0).toFixed(4)) || ''}" placeholder="0"
                    onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)"
                    onblur="this.value=parseNum(this.value)||''" style="text-align: center; width: 95%; margin: 0 auto; display: block; font-size: 1.25rem; font-weight: bold; color: #facc15;">
            </td>
            <td style="text-align: center;">
                <input type="text" inputmode="decimal" class="g-input" data-price-input="${row.id}" value="${fmtNum(row.price, 'price')}"
                    onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                    onblur="this.value=fmtNum(parseNum(this.value), 'price')" 
                    style="text-align: center; width: 100%; font-size: 1.6rem; font-weight: bold; color: #fff;">
            </td>
            ${!isBaseSDG ? `
            <td style="text-align: center;">
                <input type="text" inputmode="decimal" class="g-input" data-fcprice-input="${row.id}" value="${fmtNum(row.fcPrice !== undefined ? row.fcPrice : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1), 'fcprice')}"
                    onfocus="this.select()" oninput="updateRow(${row.id},'fcPrice',this.value)"
                    onblur="this.value=fmtNum(parseNum(this.value), 'fcprice')" style="text-align: center; width: 100%; color:#10b981; font-size: 1.05rem;" tabindex="-1">
            </td>
            ` : ''}
            <td style="text-align: center;">
                <select class="g-select" onchange="updateRow(${row.id},'warehouseId',this.value)" tabindex="-1" style="text-align: center; width: 100%; font-size: 1.05rem;">
                    <option value="">نفس الفاتورة ↩</option>
                    ${WAREHOUSES.map(w => `<option value="${w.id}" ${w.id === row.warehouseId ? 'selected' : ''}>${w.name}</option>`).join('')}
                </select>
            </td>
            <td style="text-align: center;">
                <span class="g-readonly total-val" data-total="${row.id}" style="font-size: 1.6rem; font-weight: bold; color: #34d399;">${fmtNum(calcs.total, 'total')}</span>
            </td>
            ${!isBaseSDG ? `
            <td style="text-align: center;">
                <span class="g-readonly total-fc-val" style="color:#10b981; font-size: 1.1rem; font-weight: bold;" data-total-fc="${row.id}">${fmtNum((row.fcPrice !== undefined ? row.fcPrice : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1)) * (row.quantity || 0), 'fctotal')}</span>
            </td>
            ` : ''}
            <td style="text-align: center;">
                <div style="display: flex; gap: 5px; justify-content: center; align-items: center;">
                    <button type="button" class="add-row-btn" onclick="addRow()" title="إضافة سطر جديد" style="background: linear-gradient(135deg, rgba(16,185,129,0.25), rgba(5,150,105,0.35)); border: 1px solid rgba(16,185,129,0.5); color: #34d399; border-radius: 6px; padding: 6px 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <button class="del-btn" onclick="removeRow(${row.id})" title="حذف السطر" style="display: flex; align-items: center; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </td>
        `;
        const tr = document.createElement('tr');
        tr.innerHTML = trHtml;
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
                    <label class="label">الوحدة</label>
                    <select class="ctrl-input" onchange="updateRow(${row.id},'selectedUnit',this.value); renderGrid();" tabindex="-1">
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
                        onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)">
                </div>
                <div class="ctrl-group">
                    <label class="label" style="color:#6366f1;">الشيلة</label>
                    <div class="ctrl-input readonly" data-m-shila="${row.id}" style="color:#6366f1; font-weight:bold; text-align:center;">${fmtNum(calcs.shilaQty)}</div>
                </div>
                <div class="ctrl-group">
                    <label class="label" style="color:#ef4444;">باقي سيخ</label>
                    <div class="ctrl-input readonly" data-m-shila-rem="${row.id}" style="color:#ef4444; font-weight:bold; text-align:center;">${fmtNum(calcs.shilaRem)}</div>
                </div>
                <div class="ctrl-group">
                    <label class="label">السعر (محلي / أجنبي) <button class="btn-icon" onclick="showPriceHistory(${row.id}, 'purchase')" title="سجل أسعار الصنف للمورد" style="color:#38bdf8; background:none; border:none; cursor:pointer; padding:0; margin-right:4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></button></label>
                    <div style="display:flex; gap:4px;">
                        <input type="text" inputmode="decimal" class="ctrl-input" value="${fmtNum(row.price)}" placeholder="0"
                            onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                            onblur="this.value=fmtNum(parseNum(this.value))" style="flex:1;">
                        <input type="text" inputmode="decimal" class="ctrl-input" data-m-fcprice-input="${row.id}" value="${fmtNum(row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1)))}" placeholder="${document.getElementById('hdr-currency').value === 'SDG' ? 'أجنبي' : 'جنيه'}"
                            onfocus="this.select()" oninput="updateRow(${row.id},'fcPrice',this.value)"
                            onblur="this.value=fmtNum(parseNum(this.value))" style="flex:1; color:#10b981;">
                    </div>
                </div>
                <div class="ctrl-group">
                    <label class="label fc-total-label">${document.getElementById('hdr-currency').value === 'SDG' ? 'جملة الوحدة أجنبي' : 'جملة الوحدة جنيه'}</label>
                    <div class="ctrl-input readonly" data-m-total-fc="${row.id}" style="text-align:center;color:#10b981;">${fmtNum((row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1))) * (row.quantity || 0))}</div>
                </div>
                <div class="ctrl-group">
                    <label class="label">عدد ${item ? item.baseUnit : ''}</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" data-base-input="${row.id}" value="${fmtNum(calcs.baseQty)}" placeholder="0"
                onfocus="this.select()" oninput="updateRow(${row.id},'customBaseQty',this.value)"
                onblur="this.value=fmtNum(parseNum(this.value))" style="color:var(--primary-accent); font-weight:700;" tabindex="-1">
                </div>
                <div class="ctrl-group">
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
                <div class="ctrl-group">
                    <label class="label">سعر الشراء</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" placeholder="0" value="${row.buyPrice !== undefined ? fmtNum(row.buyPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'buyPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))">
                </div>
            </div>
            <div class="m-card-total" data-m-total="${row.id}">${fmtNum(calcs.total)} ${getCurrency()}</div>
        `;
        mCards.appendChild(card);
    });
}

// ════════════════════════════════════════
//  ADJUSTMENTS (Discount / Tax / Addition)
// ════════════════════════════════════════
function getInvoiceTotal() {
    return rows.reduce((s, r) => s + (r.quantity * r.price), 0);
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
    const isBaseSDG = (cur === 'SDG');
    const exRate = parseNum(document.getElementById('hdr-exchange-rate').value) || 1;
    
    // Update secondary price header text based on currency
    const thFcPrice = document.querySelector('.th-fcprice');
    if (thFcPrice) {
        thFcPrice.textContent = isBaseSDG ? 'سعر الوحدة أجنبي' : 'سعر الوحدة جنيه';
    }
    const thFcTotal = document.querySelector('.th-fctotal');
    if (thFcTotal) {
        thFcTotal.textContent = isBaseSDG ? 'جملة الوحدة أجنبي' : 'جملة الوحدة جنيه';
    }
    
    // Update mobile labels if any
    document.querySelectorAll('.fc-total-label').forEach(lbl => {
        lbl.textContent = isBaseSDG ? 'جملة الوحدة أجنبي' : 'جملة الوحدة جنيه';
    });

    let invTotalLocal = 0;
    let invTotalFc = 0;

    // Recompute fcPrice for all rows and update DOM
    rows.forEach(row => {
        if (isBaseSDG) {
            row.fcPrice = (row.price || 0) / exRate;
        } else {
            row.fcPrice = (row.price || 0) * exRate;
        }
        
        let rowQty = parseNum(row.quantity);
        let rowPriceLocal = parseNum(row.price);
        let rowPriceFc = parseNum(row.fcPrice);

        invTotalLocal += (rowQty * rowPriceLocal);
        invTotalFc += (rowQty * rowPriceFc);

        updateRowCalcs(row.id);
    });

    const laborLoc = parseNum(document.getElementById('t-labor')?.value || 0);
    const transportLoc = parseNum(document.getElementById('t-transport')?.value || 0);
    const additionsLoc = parseNum(document.getElementById('t-additions-loc')?.value || document.getElementById('t-additions')?.value || document.getElementById('addition-amt')?.value || 0);
    const taxLoc = parseNum(document.getElementById('t-tax-loc')?.value || document.getElementById('t-tax')?.value || document.getElementById('tax-amt')?.value || 0);
    const discountLoc = parseNum(document.getElementById('t-discount-loc')?.value || document.getElementById('t-discount')?.value || document.getElementById('discount-amt')?.value || 0);

    const laborFc = parseNum(document.getElementById('t-labor-fc')?.value || 0);
    const transportFc = parseNum(document.getElementById('t-transport-fc')?.value || 0);
    const additionsFc = parseNum(document.getElementById('t-additions-fc')?.value || 0);
    const taxFc = parseNum(document.getElementById('t-tax-fc')?.value || 0);
    const discountFc = parseNum(document.getElementById('t-discount-fc')?.value || 0);

    let grandLoc = 0;
    let grandFc = 0;
    if (isBaseSDG) {
        grandLoc = invTotalLocal + laborLoc + transportLoc + additionsLoc + taxLoc - discountLoc;
        grandFc = invTotalFc + laborFc + transportFc + additionsFc + taxFc - discountFc;
    } else {
        grandLoc = invTotalLocal + laborLoc + transportLoc + additionsLoc + taxLoc - discountLoc;
        grandFc = invTotalFc + laborFc + transportFc + additionsFc + taxFc - discountFc;
    }

    // Update totals display (Internal Hidden)
    const tInvoiceTotal = document.getElementById('t-invoice-total');
    if (tInvoiceTotal) tInvoiceTotal.textContent = fmtNum(invTotalLocal);
    const tInvoiceTotalFc = document.getElementById('t-invoice-total-fc');
    if (tInvoiceTotalFc) tInvoiceTotalFc.textContent = fmtNum(invTotalFc);
    
    // Update labels dynamically based on currency
    const lblLocal = document.getElementById('lbl-grand-local');
    const lblFc = document.getElementById('lbl-grand-fc');
    const thCol1 = document.getElementById('th-totals-col1');
    const thCol2 = document.getElementById('th-totals-col2');
    
    if (lblLocal && lblFc) {
        if (isBaseSDG) {
            lblLocal.textContent = 'صافي محلي';
            lblLocal.style.color = '#38bdf8';
            lblFc.textContent = 'صافي أجنبي';
            lblFc.style.color = '#10b981';
            if (thCol1) thCol1.textContent = 'محلي';
            if (thCol2) thCol2.textContent = 'أجنبي';
        } else {
            lblLocal.textContent = 'صافي أجنبي';
            lblLocal.style.color = '#10b981';
            lblFc.textContent = 'صافي محلي';
            lblFc.style.color = '#38bdf8';
            if (thCol1) thCol1.textContent = 'أجنبي';
            if (thCol2) thCol2.textContent = 'محلي';
        }
    }

    // Update dual grand totals (remove currency symbols per user request)
    const tGrandLocal = document.getElementById('t-grand-local');
    if (tGrandLocal) tGrandLocal.textContent = fmtNum(grandLoc);
    const tGrandFc = document.getElementById('t-grand-fc');
    if (tGrandFc) tGrandFc.textContent = fmtNum(grandFc);

    // Footer grand
    const footerGrand = document.getElementById('footer-grand');
    if (footerGrand) footerGrand.textContent = fmtNum(grandLoc);

    // Dynamic UI Animation — toggle CSS classes
    const totalsCard = document.getElementById('totals-calc-card');
    const formCard = document.getElementById('form-fields-card');
    if (totalsCard && formCard) {
        const hasItems = rows.some(r => (parseFloat(r.price) || 0) * (parseFloat(r.quantity) || 0) > 0);
        const topContainer = document.getElementById('purchase-top-container');

        if (hasItems) {
            // Remove hiding classes, add showing classes
            totalsCard.classList.remove('hidden-totals');
            formCard.classList.remove('expanded-form');
            if (topContainer) topContainer.classList.remove('form-expanded');
        } else {
            // Add hiding classes, add expanded class
            totalsCard.classList.add('hidden-totals');
            formCard.classList.add('expanded-form');
            if (topContainer) topContainer.classList.add('form-expanded');
        }
    }
    
    // Recalculate payment details summary if payment panel functions exist
    if (typeof recalcPaymentSummary === 'function') {
        recalcPaymentSummary();
    }
}

// ════════════════════════════════════════
//  SAVE / SUBMIT
// ════════════════════════════════════════
function saveDraft() {
    if (rows.length === 0) { showToast('⚠️ لا توجد أصناف لحفظها', 'warning'); return; }
    
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const creditActive = document.getElementById('toggle-credit').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    const cashAmt = cashActive ? parseNum(document.getElementById('pay-cash-amount').value) : 0;
    const creditAmt = creditActive ? parseNum(document.getElementById('pay-credit-amount').value) : 0;
    
    let bankTransfers = [];
    if (bankActive) {
        document.querySelectorAll('#bank-transfers-list .payment-row-item').forEach(row => {
            const bankAccountId = row.querySelector('.bt-bank-select') ? row.querySelector('.bt-bank-select').value : '';
            const refEl = row.querySelector('.bt-ref-input') || row.querySelector('.bt-no-input');
            const reference = refEl ? refEl.value : '';
            const amountEl = row.querySelector('.bt-amount-input');
            const amount = amountEl ? parseNum(amountEl.value) : 0;
            if (amount > 0) {
                bankTransfers.push({ bankAccountId, reference, amount });
            }
        });
    }
    
    let cheques = [];
    if (chequeActive) {
        document.querySelectorAll('#cheques-list .payment-row-item').forEach(row => {
            const chequeNo = row.querySelector('.ch-no-input') ? row.querySelector('.ch-no-input').value : '';
            const bankAccountId = row.querySelector('.ch-bank-select') ? row.querySelector('.ch-bank-select').value : '';
            const dueDate = row.querySelector('.ch-date-input') ? row.querySelector('.ch-date-input').value : '';
            const amountEl = row.querySelector('.ch-amount-input');
            const amount = amountEl ? parseNum(amountEl.value) : 0;
            if (amount > 0) {
                cheques.push({ chequeNo, bankName, dueDate, amount });
            }
        });
    }

    const data = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        invoiceNo: document.getElementById('hdr-inv-no').value,
        date: document.getElementById('hdr-date').value,
        customer: document.getElementById('hdr-customer').value,
        saleType: currentSaleType,
        currency: getCurrency(),
        exchangeRate: document.getElementById('hdr-exchange-rate').value,
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1,
        paymentMethod: getPaymentMethodFromUI(),
        paymentAccountId: document.getElementById('hdr-payment-account') ? document.getElementById('hdr-payment-account').value : '',
        cashAmount: cashAmt,
        creditAmount: creditAmt,
        bankTransfers: bankTransfers,
        cheques: cheques,
        dueDate: creditAmt > 0 ? document.getElementById('pay-credit-due-date').value : '',
        warehouse: document.getElementById('hdr-warehouse').value,
        costCenter: document.getElementById('hdr-cost-center')?.value || '',
        description: document.getElementById('hdr-description').value,
        items: rows,
        notes: document.getElementById('inv-notes').value,
        status: 'draft',
        grandTotal: parseNum(document.getElementById('t-grand-local')?.textContent || 0),
        totalAmount: parseNum(document.getElementById('t-grand-local')?.textContent || 0),
        laborCost: parseNum(document.getElementById('t-labor')?.value || 0),
        laborCostFc: parseNum(document.getElementById('t-labor-fc')?.value || 0),
        transportCost: parseNum(document.getElementById('t-transport')?.value || 0),
        transportCostFc: parseNum(document.getElementById('t-transport-fc')?.value || 0),
        additionsCost: parseNum(document.getElementById('t-additions-loc')?.value || 0),
        additionsCostFc: parseNum(document.getElementById('t-additions-fc')?.value || 0),
        taxCost: parseNum(document.getElementById('t-tax-loc')?.value || 0),
        taxCostFc: parseNum(document.getElementById('t-tax-fc')?.value || 0),
        discountCost: parseNum(document.getElementById('t-discount-loc')?.value || 0),
        discountCostFc: parseNum(document.getElementById('t-discount-fc')?.value || 0)
    };
    localStorage.setItem('erp_draft_' + data.invoiceNo, JSON.stringify(data));
    showToast('📑 تم حفظ المسودة بنجاح', 'success', 2500);
}

function submitInvoice(ignoreWarnings = false) {
    if (currentViewedInvoiceNo) {
        // Edit mode check
        const invDate = document.getElementById('hdr-date').value;
        if (!window.canEditRecord(invDate, 'purchase')) {
            return; // blocked by policy
        }
    }

    let custId = document.getElementById('hdr-customer').value;
    const searchVal = document.getElementById('hdr-customer-search').value.trim();
    if (!custId && searchVal) {
        let exactMatch = VENDORS.find(c => c.name === searchVal);
        if (!exactMatch) {
            exactMatch = VENDORS.find(c => c.name && c.name.trim().toLowerCase() === searchVal.toLowerCase());
        }
        if (!exactMatch) {
            exactMatch = VENDORS.find(c => c.name && c.name.toLowerCase().includes(searchVal.toLowerCase()));
        }
        if (exactMatch) {
            selectCustomer(exactMatch.id, exactMatch.name);
            custId = exactMatch.id;
        }
    }

    // Fallback: If still no vendor ID but searchVal exists or if empty, default to general supplier
    if (!custId) {
        let defaultSupp = VENDORS.find(c => c.id === 'walk-in' || c.id === 'SUP-GENERAL' || (c.name && c.name.includes('عام')));
        if (!defaultSupp && VENDORS.length > 0) defaultSupp = VENDORS[0];
        if (defaultSupp) {
            selectCustomer(defaultSupp.id, searchVal || defaultSupp.name);
            custId = defaultSupp.id;
        } else {
            custId = 'SUP-GENERAL';
            selectCustomer(custId, searchVal || 'مورد عام');
        }
    }

    if (rows.length === 0) { showToast('❌ لا يمكن تأكيد فاتورة فارغة', 'error'); return; }

    const hasEmptyItems = rows.some(r => !r.itemId);
    if (hasEmptyItems) { showToast('⚠️ يرجى اختيار الصنف لكل سطر', 'warning'); return; }

    const hasZeroQty = rows.some(r => r.quantity <= 0);
    if (hasZeroQty) { showToast('⚠️ الكمية يجب أن تكون أكبر من صفر', 'warning'); return; }

    let warnings = [];

    // Removed stock check and credit check because this is a Purchase (Adding to stock, increasing debt)

    
    const _docDateStr = document.getElementById('hdr-date') ? document.getElementById('hdr-date').value : new Date().toISOString().split('T')[0];
    if (!window.canCreateRecord(_docDateStr)) return;

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    try {

    // Commit the invoice number
    let committedNo;
    if (currentViewedInvoiceNo) {
        committedNo = currentViewedInvoiceNo; // keep old number in edit mode
    } else {
        committedNo = commitInvoiceNumber(currentSaleType); // increment
    }
    const cur = getCurrency();
    const isBase = (cur === 'SDG');
    const rateEl = document.getElementById('hdr-exchange-rate');
    const rate = rateEl ? (parseNum(rateEl.value) || 1) : 1;
    const grandTotal = document.getElementById('t-grand-local').textContent;
    const grandAmount = parseNum(grandTotal);
    
    // Explicitly compute Local vs FC for robust accounting
    const localGrandAmount = isBase ? grandAmount : grandAmount * rate;
    const fcGrandAmount = isBase ? grandAmount / rate : grandAmount;

    // --- 1. Multi-Payment Read & Validation ---
    if (typeof recalcPaymentSummary === 'function') {
        recalcPaymentSummary();
    }
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
    } else {
        cashAmt = cashActive ? parseNum(document.getElementById('pay-cash-amount').value) : 0;
        creditAmt = creditActive ? parseNum(document.getElementById('pay-credit-amount').value) : 0;
        
        if (bankActive) {
            let validationFailed = false;
            let existingPayments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
            
            document.querySelectorAll('#bank-transfers-list .payment-row-item').forEach(row => {
                const bankAccountId = row.querySelector('.bt-bank-select') ? row.querySelector('.bt-bank-select').value : '';
                const refEl = row.querySelector('.bt-ref-input') || row.querySelector('.bt-no-input');
                const reference = refEl ? refEl.value.trim() : '';
                const amountEl = row.querySelector('.bt-amount-input');
                const amount = amountEl ? parseNum(amountEl.value) : 0;
                if (amount > 0) {
                    if (reference.length < 6) {
                        showToast('❌ رقم التحويلة أقل من 6 أرقام! يرجى التأكد.', 'error-pink', 6000);
                        validationFailed = true;
                    } else {
                        const last6 = reference.slice(-6);
                        const isDuplicate = existingPayments.some(p => p.reference && String(p.reference).trim().slice(-6) === last6);
                        if (isDuplicate) {
                            showToast(`❌ رقم التحويل منتهي بـ (${last6}) مسجل مسبقاً في حوالة أخرى ولن يتكرر في النظام!`, 'error', 6000);
                            validationFailed = true;
                        }
                    }
                    bankTransfers.push({ bankAccountId, reference, amount });
                    transfersSum += amount;
                }
            });
            if (validationFailed) {
                window.isSubmitting = false;
                return;
            }
        }
        
        if (chequeActive) {
            let validationFailed = false;
            document.querySelectorAll('#cheques-list .payment-row-item').forEach(row => {
                const chequeNo = row.querySelector('.ch-no-input') ? row.querySelector('.ch-no-input').value.trim() : '';
                const bankAccountId = row.querySelector('.ch-bank-select') ? row.querySelector('.ch-bank-select').value : '';
                const dueDate = row.querySelector('.ch-date-input') ? row.querySelector('.ch-date-input').value : '';
                const amountEl = row.querySelector('.ch-amount-input');
                const amount = amountEl ? parseNum(amountEl.value) : 0;
                if (amount > 0) {
                    if (!chequeNo) {
                        showToast('❌ رقم الشيك فاضي! يجب إدخال رقم الشيك لإتمام العملية.', 'error', 6000);
                        validationFailed = true;
                    }
                    cheques.push({ chequeNo, bankAccountId, dueDate, amount });
                    chequesSum += amount;
                }
            });
            if (validationFailed) {
                window.isSubmitting = false;
                return;
            }
        }

        const totalPaid = cashAmt + creditAmt + transfersSum + chequesSum;
        const remaining = grandAmount - totalPaid;

        if (Math.abs(remaining) > 0.05) {
            showToast(`❌ مجموع المبالغ المدفوعة لا يتطابق مع إجمالي الفاتورة. المتبقي: ${fmtNum(remaining)} ${cur}`, 'error', 5000);
            return;
        }

        paymentMethod = getPaymentMethodFromUI();
    }

    // Save invoice data
    const supplierSearchEl = document.getElementById('hdr-customer-search');
    const data = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        invoiceNo: committedNo,
        timestamp: new Date().toISOString(),
        date: document.getElementById('hdr-date').value,
        time: new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
        customer: document.getElementById('hdr-customer').value,
        supplierName: supplierSearchEl ? supplierSearchEl.value : '',
        saleType: currentSaleType,
        currency: cur,
        exchangeRate: document.getElementById('hdr-exchange-rate').value,
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1,
        paymentMethod: paymentMethod,
        paymentAccountId: document.getElementById('hdr-payment-account') ? document.getElementById('hdr-payment-account').value : '',
        cashAmount: cashAmt,
        creditAmount: creditAmt,
        bankTransfers: bankTransfers,
        cheques: cheques,
        dueDate: creditAmt > 0 ? document.getElementById('pay-credit-due-date').value : '',
        warehouse: document.getElementById('hdr-warehouse').value,
        costCenter: document.getElementById('hdr-cost-center')?.value || '',
        description: document.getElementById('hdr-description') ? document.getElementById('hdr-description').value : '',
        items: rows.map(r => ({...r})),
        notes: document.getElementById('inv-notes') ? document.getElementById('inv-notes').value : '',
        status: 'confirmed',
        grandTotal: grandAmount,
        totalAmount: grandAmount,
        localTotal: localGrandAmount,
        fcTotal: fcGrandAmount,
        laborCost: parseNum(document.getElementById('t-labor')?.value || 0),
        laborCostFc: parseNum(document.getElementById('t-labor-fc')?.value || 0),
        transportCost: parseNum(document.getElementById('t-transport')?.value || 0),
        transportCostFc: parseNum(document.getElementById('t-transport-fc')?.value || 0),
        additionsCost: parseNum(document.getElementById('t-additions-loc')?.value || 0),
        additionsCostFc: parseNum(document.getElementById('t-additions-fc')?.value || 0),
        taxCost: parseNum(document.getElementById('t-tax-loc')?.value || 0),
        taxCostFc: parseNum(document.getElementById('t-tax-fc')?.value || 0),
        discountCost: parseNum(document.getElementById('t-discount-loc')?.value || 0),
        discountCostFc: parseNum(document.getElementById('t-discount-fc')?.value || 0)
    };

    const postingMode = window.getPostingMode ? window.getPostingMode() : 'auto';
    const shouldApplyEffects = (postingMode === 'auto' && currentSaleType !== 'proforma');

    // Set status based on posting mode
    if (shouldApplyEffects) {
        data.status = 'posted'; // Auto mode: mark as posted immediately
    } else {
        data.status = 'confirmed'; // Manual mode: confirmed but not posted
    }

    // If edit mode is active AND the old invoice was posted, revert effects first
    if (currentViewedInvoiceNo) {
        const oldStr = localStorage.getItem('erp_pur_' + currentViewedInvoiceNo);
        let oldInv = null;
        if (oldStr) try { oldInv = JSON.parse(oldStr); } catch(e) {}
        if (oldInv && oldInv.status === 'posted') {
            revertOldInvoiceEffects(currentViewedInvoiceNo);
        }
    }

    // ── Apply Accounting Effects (only in auto mode or re-posting) ──
    let usedAccountName = '';
    if (shouldApplyEffects) {
        // Update Bank/Safe balance based on new payment fields
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        let safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];

        if (paymentMethod === 'hybrid' || paymentMethod === 'multipay') {
            // Subtract cash portion (always in local base currency)
            const localCashAmt = isBase ? cashAmt : cashAmt * rate;
            if (cashAmt > 0) {
                const paymentAccountId = document.getElementById('hdr-payment-account') ? document.getElementById('hdr-payment-account').value : '';
                let bIdx = banks.findIndex(b => b.id === paymentAccountId);
                let sIdx = safes.findIndex(s => s.id === paymentAccountId);
                
                if (bIdx > -1) {
                    banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - localCashAmt;
                    data.paymentAccountId = paymentAccountId;
                    usedAccountName = banks[bIdx].name;
                    localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
                } else if (sIdx > -1) {
                    safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) - localCashAmt;
                    data.paymentAccountId = paymentAccountId;
                    usedAccountName = safes[sIdx].name;
                    localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
                }
            }
            // Subtract bank transfers (always in local base currency)
            if (bankTransfers && bankTransfers.length > 0) {
                bankTransfers.forEach(bt => {
                    const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
                    if (bIdx > -1) {
                        const localBtAmt = isBase ? bt.amount : bt.amount * rate;
                        banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - localBtAmt;
                    }
                });
            }
            // Subtract cheques immediately (always in local base currency)
            if (cheques && cheques.length > 0) {
                cheques.forEach(ch => {
                    const bIdx = banks.findIndex(b => b.id === ch.bankAccountId);
                    if (bIdx > -1) {
                        const localChAmt = isBase ? ch.amount : ch.amount * rate;
                        banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - localChAmt;
                    }
                });
            }
            if ((bankTransfers && bankTransfers.length > 0) || (cheques && cheques.length > 0)) {
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            }
        } else if (paymentMethod !== 'credit') {
            const paymentAccountId = document.getElementById('hdr-payment-account') ? document.getElementById('hdr-payment-account').value : '';
            let bIdx = banks.findIndex(b => b.id === paymentAccountId);
            let sIdx = safes.findIndex(s => s.id === paymentAccountId);
            
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - localGrandAmount;
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
                data.paymentAccountId = paymentAccountId;
                usedAccountName = banks[bIdx].name;
            } else if (sIdx > -1) {
                safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) - localGrandAmount;
                localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
                data.paymentAccountId = paymentAccountId;
                usedAccountName = safes[sIdx].name;
            }
        }

        // Update Supplier Balance (Debt)
        let creditBalanceToAdd = 0;
        if (paymentMethod === 'hybrid' || paymentMethod === 'multipay') {
            creditBalanceToAdd = creditAmt;
        } else if (paymentMethod === 'credit') {
            creditBalanceToAdd = grandAmount;
        }
        if (creditBalanceToAdd > 0 && custId && custId !== 'walk-in') {
            let suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
            const sIdx = suppliers.findIndex(s => s.id === custId);
            if (sIdx > -1) {
                const localCreditToAdd = isBase ? creditBalanceToAdd : creditBalanceToAdd * rate;
                let currentBalance = parseFloat(suppliers[sIdx].currentBalance) || 0;
                suppliers[sIdx].currentBalance = currentBalance + localCreditToAdd;
                localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
            }
        }

        // --- ADD BACKGROUND PAYMENT VOUCHERS FOR CASH/BANK ---
        let paymentsLog = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
        const dateStr = document.getElementById('hdr-date') ? document.getElementById('hdr-date').value : new Date().toISOString().split('T')[0];
        
        if (paymentMethod === 'hybrid' || paymentMethod === 'multipay') {
            if (cashAmt > 0 && data.paymentAccountId) {
                paymentsLog.unshift({
                    id: 'pay-' + Date.now() + '-c',
                    paymentNo: 'PMT-PUR-' + committedNo + '-C',
                    date: dateStr,
                    type: 'supplier',
                    partnerId: custId,
                    partnerName: data.supplierName || 'مورد عام',
                    amount: cashAmt,
                    currency: cur,
                    exchangeRate: rate,
                    paymentMethod: 'cash',
                    accountId: data.paymentAccountId,
                    reference: 'نقدي فاتورة ' + committedNo,
                    notes: 'سداد آلي من شاشة المشتريات فاتورة #' + committedNo,
                    refInvoice: committedNo
                });
            }
            if (bankTransfers && bankTransfers.length > 0) {
                bankTransfers.forEach((bt, idx) => {
                    paymentsLog.unshift({
                        id: 'pay-' + Date.now() + '-b' + idx,
                        paymentNo: 'PMT-PUR-' + committedNo + '-B' + idx,
                        date: dateStr,
                        type: 'supplier',
                        partnerId: custId,
                        partnerName: data.supplierName || 'مورد عام',
                        amount: bt.amount,
                        currency: cur,
                        exchangeRate: rate,
                        paymentMethod: 'bank',
                        accountId: bt.bankAccountId,
                        reference: bt.reference || 'حوالة فاتورة ' + committedNo,
                        notes: 'سداد آلي من شاشة المشتريات فاتورة #' + committedNo,
                        refInvoice: committedNo
                    });
                });
            }
        } else if (paymentMethod !== 'credit' && grandAmount > 0) {
            paymentsLog.unshift({
                id: 'pay-' + Date.now() + '-f',
                paymentNo: 'PMT-PUR-' + committedNo + '-F',
                date: dateStr,
                type: 'supplier',
                partnerId: custId,
                partnerName: data.supplierName || 'مورد عام',
                amount: grandAmount,
                currency: cur,
                exchangeRate: rate,
                paymentMethod: paymentMethod === 'bank' ? 'bank' : 'cash',
                accountId: data.paymentAccountId,
                reference: 'سداد فاتورة ' + committedNo,
                notes: 'سداد آلي من شاشة المشتريات فاتورة #' + committedNo,
                refInvoice: committedNo
            });
        }
        localStorage.setItem('erp_single_payments_v1', JSON.stringify(paymentsLog));

        // --- ADD CHEQUES TO ERP_CHECKS_V1 ---
        if (cheques && cheques.length > 0) {
            let checksLog = JSON.parse(localStorage.getItem('erp_checks_v1')) || [];
            let allBanksCache = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
            cheques.forEach((ch, idx) => {
                const bObj = allBanksCache.find(b => String(b.id) === String(ch.bankAccountId));
                const actualBankName = bObj ? bObj.name : 'بنك غير محدد';
                checksLog.unshift({
                    id: 'chk-' + Date.now() + '-' + idx,
                    checkNumber: ch.chequeNo,
                    type: 'payable',
                    bankName: actualBankName,
                    bankAccountId: ch.bankAccountId,
                    dueDate: ch.dueDate,
                    amount: ch.amount,
                    currency: cur,
                    exchangeRate: rate,
                    beneficiary: data.supplierName || 'مورد عام',
                    beneficiaryId: custId,
                    status: 'issued',
                    refInvoice: committedNo,
                    notes: 'شيك آلي من شاشة المشتريات فاتورة #' + committedNo
                });
            });
            localStorage.setItem('erp_checks_v1', JSON.stringify(checksLog));
        }
    }

    localStorage.setItem('erp_pur_' + committedNo, JSON.stringify(data));

    // Append or Update global purchase invoices array for inventory calculation
    let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    if (currentViewedInvoiceNo) {
        const idx = allPurchases.findIndex(inv => inv.invoiceNo === currentViewedInvoiceNo);
        if (idx !== -1) allPurchases[idx] = data;
        else allPurchases.push(data);
    } else {
        allPurchases.push(data);
    }
    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));
    if (window.AccountingEngine && typeof window.AccountingEngine.postPurchaseInvoice === 'function') {
        window.AccountingEngine.postPurchaseInvoice(data);
    }
    if (typeof loadHubTransactions === 'function') loadHubTransactions();

    // Add to local inventory cache and live objects (only in auto mode)
    if (shouldApplyEffects) {
        let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        const mainWh = document.getElementById('hdr-warehouse') ? document.getElementById('hdr-warehouse').value : '';
        
        // Landed Cost Calculation
        const localAdditions = (data.laborCost||0) + (data.transportCost||0) + (data.additionsCost||0) + (data.taxCost||0) - (data.discountCost||0);
        const fcAdditions = (data.laborCostFc||0) + (data.transportCostFc||0) + (data.additionsCostFc||0) + (data.taxCostFc||0) - (data.discountCostFc||0);
        let totalItemValueLocal = 0, totalItemValueFc = 0;
        data.items.forEach(r => {
            totalItemValueLocal += (parseFloat(r.price)||0) * (parseFloat(r.quantity)||0);
            totalItemValueFc += (parseFloat(r.fcPrice)||0) * (parseFloat(r.quantity)||0);
        });
        const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        const valuationMethod = cfg.inventoryValuation || 'moving_average';

        for (const row of data.items) {
            const item = ITEMS.find(i => String(i.id) === String(row.itemId));
            if (!item) continue;
            
            let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
            let baseQty = row.customBaseQty !== undefined ? row.customBaseQty : (item ? row.quantity * selectedUnitFactor * item.conversionFactor : 0);
            const whId = row.warehouseId || mainWh;
            const whName = WAREHOUSES.find(w => w.id === whId)?.name || 'المخزن الرئيسي';
            
            let itemInv = currentInvData.find(iv => String(iv.itemId) === String(row.itemId) && iv.warehouse === whName);
            if (itemInv) {
                itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) + baseQty;
            } else {
                currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: baseQty, minQty: 10 });
            }
            
            let oldQty = parseFloat(item.stock ? Object.values(item.stock).reduce((a,b)=>a+b,0) : 0) || 0;
            
            if (item.stock && item.stock[whId] !== undefined) item.stock[whId] += baseQty;
            else if (item.stock) item.stock[whId] = baseQty;
            
            // Costing Updates
            let itemTotalLocal = (parseFloat(row.price)||0) * (parseFloat(row.quantity)||0);
            let itemTotalFc = (parseFloat(row.fcPrice)||0) * (parseFloat(row.quantity)||0);
            
            let ratioLocal = totalItemValueLocal > 0 ? (itemTotalLocal / totalItemValueLocal) : 0;
            let ratioFc = totalItemValueFc > 0 ? (itemTotalFc / totalItemValueFc) : 0;
            
            let newCostBaseLocal = baseQty > 0 ? ((itemTotalLocal + (localAdditions * ratioLocal)) / baseQty) : 0;
            let newCostBaseFc = baseQty > 0 ? ((itemTotalFc + (fcAdditions * ratioFc)) / baseQty) : 0;
            
            row.costingRevertData = {
                oldAvgLocal: parseFloat(item.averageCost) || 0, oldAvgFc: parseFloat(item.averageCostFc) || 0,
                oldLastLocal: parseFloat(item.lastPurchaseCost) || 0, oldLastFc: parseFloat(item.lastPurchaseCostFc) || 0
            };
            
            let currentTotalQty = oldQty + baseQty;
            let oldAvgLocal = parseFloat(item.averageCost) || parseFloat(item.lastPurchaseCost) || 0;
            let oldAvgFc = parseFloat(item.averageCostFc) || parseFloat(item.lastPurchaseCostFc) || 0;
            
            let movingAvgLocal = currentTotalQty > 0 ? ((oldQty * oldAvgLocal) + (baseQty * newCostBaseLocal)) / currentTotalQty : newCostBaseLocal;
            let movingAvgFc = currentTotalQty > 0 ? ((oldQty * oldAvgFc) + (baseQty * newCostBaseFc)) / currentTotalQty : newCostBaseFc;
            
            if (valuationMethod === 'moving_average') {
                item.averageCost = movingAvgLocal; item.averageCostFc = movingAvgFc;
            } else {
                item.averageCost = newCostBaseLocal; item.averageCostFc = newCostBaseFc;
            }
            item.lastPurchaseCost = newCostBaseLocal; item.lastPurchaseCostFc = newCostBaseFc;
        }
        localStorage.setItem('erp_items_v1', JSON.stringify(ITEMS));
        localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));
        localStorage.setItem('erp_pur_' + committedNo, JSON.stringify(data));
    }

    window._cachedPurchaseInvoicesRaw = null;
    window._cachedPurchaseList = null;

    // Trigger immediate cloud push so changes are immediately persisted to MySQL
    if (typeof window.pushToCloud === 'function') {
        window.pushToCloud(true).catch(e => console.warn("Immediate cloud sync failed:", e));
    }

    // Show professional success modal
    let finalWarnings = [];

    if (usedAccountName) {
        showToast(`تم خصم ${fmtNum(grandAmount)} ${cur} من رصيد: ${usedAccountName}`, 'success', 5000);
    }

    if (!shouldApplyEffects) {
        showToast('📋 تم تأكيد الفاتورة — تحتاج ترحيل لتؤثر في الأرصدة والمخزون', 'warning', 5000);
    }

    onSuccessNewInvoice(false);
    showSuccessModal(committedNo, grandTotal, cur, finalWarnings);

    } catch (err) {
        console.error("Critical error in submitInvoice:", err);
        window.isSubmitting = false;
        showToast("❌ حدث خطأ أثناء حفظ الفاتورة: " + (err.message || err), "error", 6000);
    }
}


// ════════════════════════════════════════
//  POST / UNPOST INVOICE (Manual Posting)
// ════════════════════════════════════════
function postInvoice(invoiceNo) {
    if (!invoiceNo) { showToast('❌ لا يوجد رقم فاتورة', 'error'); return; }

    let invStr = localStorage.getItem('erp_pur_' + invoiceNo);
    let inv = null;
    if (invStr) try { inv = JSON.parse(invStr); } catch(e) {}
    if (!inv) {
        let allP = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
        inv = allP.find(i => String(i.invoiceNo) === String(invoiceNo));
    }
    if (!inv) { showToast('❌ لم يتم العثور على الفاتورة', 'error'); return; }
    if (inv.status === 'posted') { showToast('ℹ️ الفاتورة مرحلة بالفعل', 'warning'); return; }

    const grandAmount = parseFloat(inv.grandTotal) || 0;
    const paymentMethod = inv.paymentMethod || 'credit';
    const custId = inv.customer;
    
    const cur = inv.currency || 'SDG';
    const rate = parseFloat(inv.exchangeRate) || 1;
    const isBase = (cur === 'SDG');

    // 1. Apply Bank/Safe effects
    let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    if (paymentMethod === 'hybrid' || paymentMethod === 'multipay') {
        if (inv.cashAmount > 0 && inv.paymentAccountId) {
            const bIdx = banks.findIndex(b => b.id === inv.paymentAccountId);
            const amtToSubtract = isBase ? parseFloat(inv.cashAmount) : parseFloat(inv.cashAmount) * rate;
            if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - amtToSubtract;
        }
        if (inv.bankTransfers && inv.bankTransfers.length > 0) {
            inv.bankTransfers.forEach(bt => {
                const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
                const amtToSubtract = isBase ? parseFloat(bt.amount) : parseFloat(bt.amount) * rate;
                if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - amtToSubtract;
            });
        }
        localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
    } else if (paymentMethod !== 'credit' && inv.paymentAccountId) {
        const bIdx = banks.findIndex(b => b.id === inv.paymentAccountId);
        if (bIdx > -1) {
            const amtToSubtract = isBase ? grandAmount : grandAmount * rate;
            banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - amtToSubtract;
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        }
    }

    // 2. Apply Supplier Balance (Debt)
    let creditBalanceToAdd = 0;
    if (paymentMethod === 'hybrid' || paymentMethod === 'multipay') {
        creditBalanceToAdd = parseFloat(inv.creditAmount) || 0;
    } else if (paymentMethod === 'credit') {
        creditBalanceToAdd = grandAmount;
    }
    
    if (creditBalanceToAdd > 0 && custId && custId !== 'walk-in') {
        const localCreditToAdd = isBase ? creditBalanceToAdd : creditBalanceToAdd * rate;
        let suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        const sIdx = suppliers.findIndex(s => s.id === custId);
        if (sIdx > -1) {
            suppliers[sIdx].currentBalance = (parseFloat(suppliers[sIdx].currentBalance) || 0) + localCreditToAdd;
            localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
        }
    }

    // 3. Apply Inventory
    let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
    const mainWh = inv.warehouse || 'wh-001';
    if (inv.items && Array.isArray(inv.items)) {
        const localAdditions = (parseFloat(inv.laborCost)||0) + (parseFloat(inv.transportCost)||0) + (parseFloat(inv.additionsCost)||0) + (parseFloat(inv.taxCost)||0) - (parseFloat(inv.discountCost)||0);
        const fcAdditions = (parseFloat(inv.laborCostFc)||0) + (parseFloat(inv.transportCostFc)||0) + (parseFloat(inv.additionsCostFc)||0) + (parseFloat(inv.taxCostFc)||0) - (parseFloat(inv.discountCostFc)||0);
        let totalItemValueLocal = 0, totalItemValueFc = 0;
        inv.items.forEach(r => {
            totalItemValueLocal += (parseFloat(r.price)||0) * (parseFloat(r.quantity)||0);
            totalItemValueFc += (parseFloat(r.fcPrice)||0) * (parseFloat(r.quantity)||0);
        });
        const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        const valuationMethod = cfg.inventoryValuation || 'moving_average';

        for (const row of inv.items) {
            const item = ITEMS.find(i => String(i.id) === String(row.itemId));
            if (!item) continue;
            let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
            let baseQty = row.customBaseQty !== undefined ? row.customBaseQty : (item ? row.quantity * selectedUnitFactor * item.conversionFactor : 0);
            const whId = row.warehouseId || mainWh;
            const whName = WAREHOUSES.find(w => w.id === whId)?.name || 'المخزن الرئيسي';
            let itemInv = currentInvData.find(iv => String(iv.itemId) === String(row.itemId) && iv.warehouse === whName);
            if (itemInv) {
                itemInv.qty = (parseFloat(itemInv.qty) || 0) + baseQty;
            } else {
                currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: baseQty, minQty: 10 });
            }
            
            let oldQty = parseFloat(item.stock ? Object.values(item.stock).reduce((a,b)=>a+b,0) : 0) || 0;
            if (item.stock && item.stock[whId] !== undefined) item.stock[whId] += baseQty;
            else if (item.stock) item.stock[whId] = baseQty;
            
            // Costing Updates
            let itemTotalLocal = (parseFloat(row.price)||0) * (parseFloat(row.quantity)||0);
            let itemTotalFc = (parseFloat(row.fcPrice)||0) * (parseFloat(row.quantity)||0);
            
            let ratioLocal = totalItemValueLocal > 0 ? (itemTotalLocal / totalItemValueLocal) : 0;
            let ratioFc = totalItemValueFc > 0 ? (itemTotalFc / totalItemValueFc) : 0;
            
            let newCostBaseLocal = baseQty > 0 ? ((itemTotalLocal + (localAdditions * ratioLocal)) / baseQty) : 0;
            let newCostBaseFc = baseQty > 0 ? ((itemTotalFc + (fcAdditions * ratioFc)) / baseQty) : 0;
            
            row.costingRevertData = {
                oldAvgLocal: parseFloat(item.averageCost) || 0, oldAvgFc: parseFloat(item.averageCostFc) || 0,
                oldLastLocal: parseFloat(item.lastPurchaseCost) || 0, oldLastFc: parseFloat(item.lastPurchaseCostFc) || 0
            };
            
            let currentTotalQty = oldQty + baseQty;
            let oldAvgLocal = parseFloat(item.averageCost) || parseFloat(item.lastPurchaseCost) || 0;
            let oldAvgFc = parseFloat(item.averageCostFc) || parseFloat(item.lastPurchaseCostFc) || 0;
            
            let movingAvgLocal = currentTotalQty > 0 ? ((oldQty * oldAvgLocal) + (baseQty * newCostBaseLocal)) / currentTotalQty : newCostBaseLocal;
            let movingAvgFc = currentTotalQty > 0 ? ((oldQty * oldAvgFc) + (baseQty * newCostBaseFc)) / currentTotalQty : newCostBaseFc;
            
            if (valuationMethod === 'moving_average') {
                item.averageCost = movingAvgLocal; item.averageCostFc = movingAvgFc;
            } else {
                item.averageCost = newCostBaseLocal; item.averageCostFc = newCostBaseFc;
            }
            item.lastPurchaseCost = newCostBaseLocal; item.lastPurchaseCostFc = newCostBaseFc;
        }
        localStorage.setItem('erp_items_v1', JSON.stringify(ITEMS));
    }
    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

    // 4. Update invoice status to 'posted'
    inv.status = 'posted';
    inv.postedAt = new Date().toISOString();
    inv.postedBy = (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown';
    localStorage.setItem('erp_pur_' + invoiceNo, JSON.stringify(inv));

    let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    const idx = allPurchases.findIndex(i => String(i.invoiceNo) === String(invoiceNo));
    if (idx !== -1) allPurchases[idx] = inv;
    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));

    // 5. Apply immutability and update UI
    if (window.applyImmutabilityUI) window.applyImmutabilityUI('posted');
    updatePostingStatusBar('posted');
    showToast('📮 تم ترحيل الفاتورة بنجاح — أثرت في الأرصدة والمخزون', 'success', 4000);
}

function unpostInvoice(invoiceNo) {
    if (!invoiceNo) { showToast('❌ لا يوجد رقم فاتورة', 'error'); return; }

    let invStr = localStorage.getItem('erp_pur_' + invoiceNo);
    let inv = null;
    if (invStr) try { inv = JSON.parse(invStr); } catch(e) {}
    if (!inv) { showToast('❌ لم يتم العثور على الفاتورة', 'error'); return; }
    if (inv.status !== 'posted') { showToast('ℹ️ الفاتورة غير مرحلة', 'warning'); return; }

    // Revert all effects
    revertOldInvoiceEffects(invoiceNo);

    // Update status back to confirmed
    inv.status = 'confirmed';
    delete inv.postedAt;
    delete inv.postedBy;
    localStorage.setItem('erp_pur_' + invoiceNo, JSON.stringify(inv));

    let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    const idx = allPurchases.findIndex(i => String(i.invoiceNo) === String(invoiceNo));
    if (idx !== -1) allPurchases[idx] = inv;
    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));

    // Remove immutability and update UI
    if (window.applyImmutabilityUI) window.applyImmutabilityUI('confirmed');
    updatePostingStatusBar('confirmed');
    showToast('↩️ تم عكس ترحيل الفاتورة — يمكنك التعديل عليها الآن', 'success', 4000);
}

function updatePostingStatusBar(status) {
    const bar = document.getElementById('posting-status-bar');
    if (!bar) return;
    const postingMode = window.getPostingMode ? window.getPostingMode() : 'auto';
    
    if (postingMode === 'auto' || !currentViewedInvoiceNo) {
        bar.style.display = 'none';
        return;
    }

    bar.style.display = 'flex';
    const invNo = currentViewedInvoiceNo || document.getElementById('hdr-inv-no')?.value;

    if (status === 'posted') {
        bar.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; flex:1;">
                <span style="font-size:1.3rem;">🔒</span>
                <div>
                    <span style="font-weight:700; color:#10b981;">مرحلة</span>
                    <span style="color:#94a3b8; font-size:0.8rem; margin-right:8px;">— أثرت في الأرصدة والمخزون</span>
                </div>
            </div>
            <button type="button" onclick="unpostInvoice('${invNo}')" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; padding:8px 18px; border-radius:10px; font-weight:700; font-size:0.9rem; cursor:pointer; transition:all 0.2s; font-family:'Tajawal',sans-serif; display:flex; align-items:center; gap:6px;" onmouseover="this.style.background='rgba(239,68,68,0.25)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'">
                <span>↩️</span> عكس الترحيل
            </button>`;
        bar.style.background = 'linear-gradient(135deg, rgba(16,185,129,0.08), rgba(16,185,129,0.03))';
        bar.style.borderColor = 'rgba(16,185,129,0.3)';
    } else if (status === 'confirmed') {
        bar.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; flex:1;">
                <span style="font-size:1.3rem;">⏳</span>
                <div>
                    <span style="font-weight:700; color:#fbbf24;">مؤكدة — بانتظار الترحيل</span>
                    <span style="color:#94a3b8; font-size:0.8rem; margin-right:8px;">لم تؤثر في الأرصدة بعد</span>
                </div>
            </div>
            <button type="button" onclick="postInvoice('${invNo}')" style="background:linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.15)); border:1px solid rgba(16,185,129,0.4); color:#34d399; padding:8px 22px; border-radius:10px; font-weight:700; font-size:0.95rem; cursor:pointer; transition:all 0.2s; font-family:'Tajawal',sans-serif; display:flex; align-items:center; gap:6px;" onmouseover="this.style.background='linear-gradient(135deg, rgba(16,185,129,0.3), rgba(5,150,105,0.25))'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.15))'; this.style.transform='none'">
                <span>📮</span> ترحيل الفاتورة
            </button>`;
        bar.style.background = 'linear-gradient(135deg, rgba(251,191,36,0.08), rgba(245,158,11,0.03))';
        bar.style.borderColor = 'rgba(251,191,36,0.3)';
    } else {
        bar.style.display = 'none';
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

function revertOldInvoiceEffects(currentInvNo) {
    let oldInvStr = localStorage.getItem('erp_pur_' + currentInvNo);
    let oldInv = null;
    if (oldInvStr) {
        try { oldInv = JSON.parse(oldInvStr); } catch(e) {}
    }
    
    if (!oldInv) {
        let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
        oldInv = allPurchases.find(inv => String(inv.invoiceNo) === String(currentInvNo) || String(inv.id) === String(currentInvNo));
    }

    if (!oldInv) return;
    const grandAmount = parseFloat(oldInv.grandTotal) || 0;

    // 1. Revert Supplier Balance (Debt)
    const isOldBase = (oldInv.currency === 'SDG' || !oldInv.currency);
    const oldRate = parseNum(oldInv.exchangeRate) || 1;
    const oldGrandLoc = oldInv.localTotal !== undefined ? oldInv.localTotal : (isOldBase ? oldInv.grandTotal : oldInv.grandTotal * oldRate);

    let creditRevertAmount = 0;
    if (oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') {
        creditRevertAmount = parseFloat(oldInv.creditAmount) || 0;
    } else if (oldInv.paymentMethod === 'credit') {
        creditRevertAmount = oldInv.grandTotal;
    }
    
    if (creditRevertAmount > 0 && oldInv.customer && oldInv.customer !== 'walk-in') {
        let suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        const sIdx = suppliers.findIndex(s => s.id === oldInv.customer);
        if (sIdx > -1) {
            let currentBalance = parseFloat(suppliers[sIdx].currentBalance) || 0;
            const localCreditRevert = isOldBase ? creditRevertAmount : creditRevertAmount * oldRate;
            suppliers[sIdx].currentBalance = currentBalance - localCreditRevert; // Revert debt
            localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
        }
    }

    // 2. Revert Bank/Safe Balance
    if (oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') {
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        let safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
        
        // Revert cash portion
        if (oldInv.cashAmount > 0 && oldInv.paymentAccountId) {
            const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
            const sIdx = safes.findIndex(s => s.id === oldInv.paymentAccountId);
            
            const localCashAmt = isOldBase ? oldInv.cashAmount : oldInv.cashAmount * oldRate;
            if (bIdx > -1) {
                banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + localCashAmt;
            } else if (sIdx > -1) {
                safes[sIdx].currentBalance = (parseFloat(safes[sIdx].currentBalance) || 0) + localCashAmt;
            }
        }
        // Revert bank transfers
        if (oldInv.bankTransfers && oldInv.bankTransfers.length > 0) {
            oldInv.bankTransfers.forEach(bt => {
                const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
                if (bIdx > -1) {
                    const localBtAmt = isOldBase ? bt.amount : bt.amount * oldRate;
                    banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + localBtAmt;
                }
            });
        }
        // Revert cheques
        if (oldInv.cheques && oldInv.cheques.length > 0) {
            oldInv.cheques.forEach(ch => {
                const bIdx = banks.findIndex(b => b.id === ch.bankAccountId);
                if (bIdx > -1) {
                    const localChAmt = isOldBase ? ch.amount : ch.amount * oldRate;
                    banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + localChAmt;
                }
            });
        }
        localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
    } else if (oldInv.paymentMethod !== 'credit' && oldInv.paymentAccountId) {
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        let safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
        
        const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
        const sIdx = safes.findIndex(s => s.id === oldInv.paymentAccountId);
        
        if (bIdx > -1) {
            let currentBalance = parseFloat(banks[bIdx].currentBalance) || 0;
            banks[bIdx].currentBalance = currentBalance + oldGrandLoc; // Revert payment
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        } else if (sIdx > -1) {
            let currentBalance = parseFloat(safes[sIdx].currentBalance) || 0;
            safes[sIdx].currentBalance = currentBalance + oldGrandLoc; // Revert payment
            localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
        }
    }

    // 3. Revert Inventory Quantity and Costing
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
            
            let itemInv = currentInvData.find(inv => String(inv.itemId) === String(row.itemId) && inv.warehouse === whName);
            if (itemInv) {
                itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - baseQty;
            }
            
            if (item.stock && item.stock[whId] !== undefined) {
                item.stock[whId] -= baseQty;
            }
            if (item.actualStock && item.actualStock[whId] !== undefined) {
                item.actualStock[whId] -= baseQty;
            }
            
            // Revert Costing if data is available
            if (row.costingRevertData) {
                item.averageCost = row.costingRevertData.oldAvgLocal;
                item.averageCostFc = row.costingRevertData.oldAvgFc;
                item.lastPurchaseCost = row.costingRevertData.oldLastLocal;
                item.lastPurchaseCostFc = row.costingRevertData.oldLastFc;
            }
        }
        localStorage.setItem('erp_items_v1', JSON.stringify(ITEMS));
    }
    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

    // 4. Revert Journal Vouchers
    let jvs = JSON.parse(localStorage.getItem('erp_journal_entries_v1')) || [];
    jvs = jvs.filter(jv => jv.refInvoiceNo !== currentInvNo);
    localStorage.setItem('erp_journal_entries_v1', JSON.stringify(jvs));

    // 5. Revert Payment Vouchers
    let paymentsLog = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    paymentsLog = paymentsLog.filter(p => p.refInvoice !== currentInvNo);
    localStorage.setItem('erp_single_payments_v1', JSON.stringify(paymentsLog));

    // 6. Revert Cheques
    let checksLog = JSON.parse(localStorage.getItem('erp_checks_v1')) || [];
    checksLog = checksLog.filter(ch => ch.refInvoice !== currentInvNo);
    localStorage.setItem('erp_checks_v1', JSON.stringify(checksLog));
}

function executeDeleteTransaction() {
    closeDeleteConfirmModal();
    
    const currentInvNo = document.getElementById('hdr-inv-no').value;
    if (!currentInvNo) return;

    let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    const oldInv = allPurchases.find(inv => inv.invoiceNo === currentInvNo);
    
    // --- SAFEGUARDS ---
    if (oldInv && oldInv.status !== 'draft' && oldInv.saleType !== 'proforma') {
        // 1. Cheque Processing Lock
        let checksLog = JSON.parse(localStorage.getItem('erp_checks_v1')) || [];
        let processedCheque = checksLog.find(ch => ch.refInvoice === currentInvNo && ch.status && ch.status !== 'issued' && ch.status !== 'pending');
        if (processedCheque) {
            showToast('❌ عذراً، لا يمكن حذف الفاتورة لوجود شيكات مرتبطة بها تم تحصيلها أو صرفها.', 'error', 6000);
            return;
        }

        // 2. Negative Inventory Lock
        if (oldInv.items && Array.isArray(oldInv.items)) {
            const ITEMS = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            const mainWh = oldInv.warehouse || 'wh-001';
            for (const row of oldInv.items) {
                const item = ITEMS.find(i => String(i.id) === String(row.itemId));
                if (!item) continue;
                
                let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
                let baseQty = 0;
                if (row.customBaseQty !== undefined) {
                    baseQty = row.customBaseQty;
                } else {
                    baseQty = row.quantity * selectedUnitFactor * item.conversionFactor;
                }

                const whId = row.warehouseId || mainWh;
                const currentStock = (item.stock && item.stock[whId] !== undefined) ? item.stock[whId] : 0;
                
                if (currentStock - baseQty < 0) {
                    showToast(`❌ لا يمكن حذف الفاتورة. رصيد الصنف (${item.name}) لا يسمح بإرجاع الكمية المشتراة!`, 'error', 6000);
                    return;
                }
            }
        }
    }
    // --- END SAFEGUARDS ---

    revertOldInvoiceEffects(currentInvNo);

    allPurchases = allPurchases.filter(inv => inv.invoiceNo !== currentInvNo);
    localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));

    localStorage.removeItem('erp_pur_' + currentInvNo);
    localStorage.removeItem('erp_draft_' + currentInvNo);

    onSuccessNewInvoice(); 
    showToast('🗑️ تم حذف الفاتورة وإلغاء جميع تأثيراتها بنجاح', 'success');
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
            <div class="success-actions" style="margin-top: 20px;">
                <button class="btn btn-ghost success-btn" onclick="onSuccessNewInvoice()">استلام جديد</button>
                <button class="btn btn-success success-btn" onclick="onSuccessDone()">قائمة المشتريات</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);

    // Trigger animation
    requestAnimationFrame(() => overlay.classList.add('visible'));
}

function onSuccessNewInvoice(closeModalFlag = true) {
    currentViewedInvoiceNo = null; // Reset edit mode
    // Hide posting status bar
    if (typeof updatePostingStatusBar === 'function') updatePostingStatusBar(null);
    // Remove immutability lock
    if (window.applyImmutabilityUI) window.applyImmutabilityUI('draft');
    // Close modal
    if (closeModalFlag) {
        const overlay = document.getElementById('success-modal-overlay');
        if (overlay) { overlay.classList.remove('visible'); setTimeout(() => overlay.remove(), 400); }
    }

    // Reset form for new invoice
    rows = [];
    rowIdCounter = 0;
    document.getElementById('hdr-customer').value = '';
    document.getElementById('hdr-customer-search').value = '';
    document.getElementById('inv-notes').value = '';
    document.getElementById('discount-amt').value = '0';
    document.getElementById('discount-pct').value = '0';
    document.getElementById('tax-amt').value = '0';
    document.getElementById('tax-pct').value = '0';
    document.getElementById('addition-amt').value = '0';
    document.getElementById('addition-pct').value = '0';
    document.getElementById('t-labor').value = '0';
    document.getElementById('t-transport').value = '0';
    onCustomerChange();

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
    const nextNo = peekNextInvoiceNumber(currentSaleType);
    document.getElementById('inv-number').textContent = nextNo;
    document.getElementById('hdr-inv-no').value = nextNo;

    renderGrid();
    recalcAll();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
}

function onSuccessDone() {
    const overlay = document.getElementById('success-modal-overlay');
    if (overlay) { overlay.classList.remove('visible'); setTimeout(() => overlay.remove(), 400); }
    // Navigate back to purchase hub and refresh the transactions list
    if (typeof showPurchaseHub === 'function') {
        setTimeout(() => showPurchaseHub(), 500);
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
        showToast('⚠️ يرجى تحديد المورد أولاً لعرض سجله', 'warning');
        return;
    }
    
    const item = ITEMS.find(i => String(i.id) === String(row.itemId));
    const itemName = item ? item.name : 'صنف غير معروف';
    const custName = document.getElementById('hdr-customer-search').value || 'المورد';

    let historyKey = type === 'sales' ? 'erp_sales_invoices_v1' : 'erp_purchase_invoices_v1';
    let invoices = JSON.parse(localStorage.getItem(historyKey)) || [];
    
    // Filter invoices by this vendor
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
        tbodyHtml = `<tr><td colspan="5" style="text-align:center; color:#94a3b8; padding: 15px;">لا يوجد سجل أسعار سابق لهذا الصنف مع هذا المورد</td></tr>`;
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
                        <span style="color:var(--text-muted); font-size:0.85rem;">المورد:</span>
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
//  CUSTOM PRINT LOGIC
// ════════════════════════════════════════
function generatePrintArea(type) {
    let printArea = document.getElementById('print-area');
    if (!printArea) {
        printArea = document.createElement('div');
        printArea.id = 'print-area';
        document.body.appendChild(printArea);
    }
    
    // Group rows by warehouse
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
    const grandTotal = document.getElementById('t-grand-local')?.textContent || '0';
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
        const item = ITEMS.find(i => i.id === r.itemId);
        const itemName = item ? item.name : 'غير معروف';
        const calcs = getRowCalcs(r);
        html += `
            <tr>
                <td style="padding:8px; text-align:center;">${idx+1}</td>
                <td style="padding:8px;">${itemName}</td>
                <td style="padding:8px; text-align:center;">${r.selectedUnit || ''}</td>
                <td style="padding:8px; text-align:center;">${r.quantity}</td>
                <td style="padding:8px; text-align:center;">${fmtNum(r.price)}</td>
                <td style="padding:8px; text-align:center;">${fmtNum(calcs.total)}</td>
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
            const item = ITEMS.find(i => i.id === r.itemId);
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

//  PRINT LOGIC — uses InvoicePrinter engine
function printCurrentInvoice() {
    const WAREHOUSES_local = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || WAREHOUSES;
    const whId   = document.getElementById('hdr-warehouse').value;
    const whObj  = WAREHOUSES_local.find(w => w.id === whId);

    // Build items array for the print engine
    const printItems = rows
        .filter(r => r.itemId)
        .map(r => {
            const item   = ITEMS.find(i => i.id === r.itemId);
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

    const subtotalVal = parseNum(document.getElementById('t-sub')?.textContent || '0');
    const discAmt     = parseNum(document.getElementById('discount-amt')?.value || '0');
    const taxAmt      = parseNum(document.getElementById('tax-amt')?.value      || '0');
    const addAmt      = parseNum(document.getElementById('addition-amt')?.value || '0');
    const laborVal    = parseNum(document.getElementById('t-labor')?.value      || '0');
    const transportVal= parseNum(document.getElementById('t-transport')?.value  || '0');
    const grandVal    = parseNum(document.getElementById('t-grand-local')?.textContent || '0');

    let printBankTransfers = [];
    document.querySelectorAll('#bank-transfers-list .payment-row-item').forEach(row => {
        const amount = parseNum(row.querySelector('.bt-amount-input')?.value);
        if(amount > 0) printBankTransfers.push({ amount });
    });
    
    let printCheques = [];
    document.querySelectorAll('#cheques-list .payment-row-item').forEach(row => {
        const amount = parseNum(row.querySelector('.ch-amount-input')?.value);
        if(amount > 0) printCheques.push({ amount });
    });

    const data = {
        type:          'purchase',
        typeLabel:     'فاتورة مشتريات',
        invoiceNo:     document.getElementById('hdr-inv-no').value,
        date:          document.getElementById('hdr-date').value,
        customerName:  document.getElementById('hdr-customer-search').value || 'مورد نقدي',
        currency:      getCurrency(),
        exchangeRate:  parseNum(document.getElementById('hdr-exchange-rate')?.value || '1'),
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1,
        paymentMethod: getPaymentMethodFromUI(),
        bankTransfers: printBankTransfers,
        cheques:       printCheques,
        warehouseName: whObj ? whObj.name : '—',
        notes:         document.getElementById('inv-notes').value || '',
        items:         printItems,
        subtotal:      subtotalVal,
        discountAmt:   discAmt,
        taxAmt:        taxAmt,
        additionAmt:   addAmt,
        laborCost:     laborVal,
        transportCost: transportVal,
        grandTotal:    grandVal
    };

    if (window.InvoicePrinter) {
        window.InvoicePrinter.print(data);
    } else {
        window.print();
    }
}

let priceHoldTimer = null;
window.startPriceHold = function(rowId) {
    priceHoldTimer = setTimeout(() => {
        showPriceHistory(rowId, 'purchase');
        priceHoldTimer = null;
    }, 1500); 
};
window.clearPriceHold = function() {
    if (priceHoldTimer) {
        clearTimeout(priceHoldTimer);
        priceHoldTimer = null;
    }
};

// ════════════════════════════════════════
//  MULTI-PAYMENT (HYBRID) SYSTEM FOR PURCHASES
// ════════════════════════════════════════

function populatePaymentAccounts() {
    const select = document.getElementById('hdr-payment-account');
    if (!select) return;
    select.innerHTML = '';
    
    let banks = [];
    let safes = [];
    try {
        banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
    } catch(e) {}
    
    safes.forEach(s => {
        const o = document.createElement('option');
        o.value = s.id || s.code; 
        o.textContent = `💰 ${s.name}`;
        select.appendChild(o);
    });
    banks.forEach(b => {
        const o = document.createElement('option');
        o.value = b.id || b.code; 
        o.textContent = `🏦 ${b.name}`;
        select.appendChild(o);
    });
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
        
        // Reset toggle buttons active states (none active by default)
        document.getElementById('toggle-cash').classList.remove('active');
        document.getElementById('toggle-credit').classList.remove('active');
        document.getElementById('toggle-bank').classList.remove('active');
        document.getElementById('toggle-cheque').classList.remove('active');
        
        // Reset inputs
        document.getElementById('pay-cash-amount').value = '0';
        document.getElementById('pay-credit-amount').value = '0';
        document.getElementById('bank-transfers-list').innerHTML = '';
        document.getElementById('cheques-list').innerHTML = '';
        
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
        if (btn) btn.classList.add('active');
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
            document.getElementById('pay-cash-amount').value = '0';
        } else if (type === 'bank') {
            document.getElementById('bank-transfers-list').innerHTML = '';
        } else if (type === 'cheque') {
            document.getElementById('cheques-list').innerHTML = '';
        }
    } else {
        btn.classList.add('active');
    }
    
    updatePaymentSectionsVisibility();
    recalcPaymentSummary();
}

function updatePaymentSectionsVisibility() {
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    // Force credit toggle to be active
    const creditBtn = document.getElementById('toggle-credit');
    if (creditBtn) creditBtn.classList.add('active');
    
    document.getElementById('pay-cash-section').style.display = cashActive ? 'flex' : 'none';
    document.getElementById('pay-credit-section').style.display = 'flex'; // Always visible
    document.getElementById('pay-bank-section').style.display = bankActive ? 'flex' : 'none';
    document.getElementById('pay-cheque-section').style.display = chequeActive ? 'flex' : 'none';

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

    // The payment details panel is shown if cash, bank, cheque, or credit toggle was triggered
    const hasActive = cashActive || bankActive || chequeActive || (creditBtn && creditBtn.classList.contains('active'));
    const layout = document.getElementById('payment-layout');
    if (layout) {
        layout.style.display = hasActive ? 'flex' : 'none';
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
    
    let banksHtml = '<option value="" disabled selected>اسم البنك</option>';
    try {
        const banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        banks.forEach(b => {
            const selected = (existingData && existingData.bankAccountId === (b.id || b.code)) ? 'selected' : '';
            banksHtml += `<option value="${b.id || b.code}" ${selected}>${b.name}</option>`;
        });
    } catch(e) {}
    
    const isFirst = container.children.length === 0;
    
    const labelHtml = isFirst 
        ? `<div style="display: flex; align-items: center; justify-content: flex-start; color: #fff; font-weight: bold; font-size: 0.95rem; gap: 4px;"><span>🏦</span> بنكي</div>`
        : `<div></div>`;

    const actionBtn = isFirst
        ? `<button type="button" onclick="addBankTransferRow()" style="background: transparent; border: 1.5px solid #06b6d4; color: #06b6d4; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; padding: 0; outline: none; transition: 0.2s;" onmouseover="this.style.background='rgba(6,182,212,0.1)'" onmouseout="this.style.background='transparent'">+</button>`
        : `<button type="button" onclick="document.getElementById('${rowId}').remove(); onPaymentAmountInput();" style="background: transparent; border: 1.5px solid #ef4444; color: #ef4444; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; padding: 0; padding-bottom: 2px; outline: none; transition: 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='transparent'">×</button>`;

    const inputStyle = "background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; text-align: center; font-size: 2.25rem; font-weight: 800; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; transition: all 0.3s ease;";
    const amountStyle = "background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; text-align: center; font-size: 2.25rem; font-weight: 800; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; transition: all 0.3s ease;";

    const rowHtml = `
        <div class="payment-row-item" id="${rowId}" style="display: grid; grid-template-columns: 85px 1.5fr 1.2fr 1.2fr 2fr 30px; gap: 8px; align-items: center; width: 100%; margin-bottom: 6px;">
            ${labelHtml}
            <input type="number" class="v-input ctrl-input bt-amount-input dynamic-focus-input dynamic-focus-amount" value="${existingData ? Math.round(parseFloat(existingData.amount)) : ''}" step="1" oninput="onPaymentAmountInput()" placeholder="المبلغ" style="${amountStyle}">
            <select class="v-input ctrl-input bt-bank-select" style="${inputStyle}">
                ${banksHtml}
            </select>
            <input type="text" maxlength="14" class="v-input ctrl-input bt-ref-input dynamic-focus-input dynamic-focus-number" value="${existingData?.chequeNo || existingData?.reference || ''}" placeholder="رقم التحويلة" style="${inputStyle}">
            <input type="text" class="v-input ctrl-input bt-note-input" value="${existingData?.notes || ''}" placeholder="بيان التحويل" style="${inputStyle}">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                ${actionBtn}
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHtml);
    if (!existingData) {
        onPaymentAmountInput();
    }
}

function addChequeRow(existingData = null) {
    const container = document.getElementById('cheques-list');
    if (!container) return;
    
    const rowId = 'ch-row-' + Date.now() + '-' + Math.random().toString(36).substring(2,5);
    const today = new Date().toISOString().split('T')[0];
    
    let banksHtml = '<option value="" disabled selected>اسم البنك</option>';
    try {
        const banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        banks.forEach(b => {
            const selected = (existingData && existingData.bankAccountId === (b.id || b.code)) ? 'selected' : '';
            banksHtml += `<option value="${b.id || b.code}" ${selected}>${b.name}</option>`;
        });
    } catch(e) {}

    const isFirst = container.children.length === 0;

    const labelHtml = isFirst 
        ? `<div style="display: flex; align-items: center; justify-content: flex-start; color: #fff; font-weight: bold; font-size: 0.95rem; gap: 4px;"><span>✍️</span> شيك</div>`
        : `<div></div>`;

    const actionBtn = isFirst
        ? `<button type="button" onclick="addChequeRow()" style="background: transparent; border: 1.5px solid #a855f7; color: #a855f7; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; padding: 0; outline: none; transition: 0.2s;" onmouseover="this.style.background='rgba(168,85,247,0.1)'" onmouseout="this.style.background='transparent'">+</button>`
        : `<button type="button" onclick="document.getElementById('${rowId}').remove(); onPaymentAmountInput();" style="background: transparent; border: 1.5px solid #ef4444; color: #ef4444; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; padding: 0; padding-bottom: 2px; outline: none; transition: 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='transparent'">×</button>`;

    const inputStyle = "background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 6px; text-align: center; font-size: 2.25rem; font-weight: 800; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; transition: all 0.3s ease;";
    const amountStyle = "background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 6px; text-align: center; font-size: 2.25rem; font-weight: 800; color: #e2e8f0; height: 60px; outline: none; padding: 0 8px; font-family: 'Tajawal', sans-serif; width: 100%; transition: all 0.3s ease;";

    const rowHtml = `
        <div class="payment-row-item" id="${rowId}" style="display: grid; grid-template-columns: 85px 1.5fr 1.2fr 1.2fr 1fr 1fr 30px; gap: 8px; align-items: center; width: 100%; margin-bottom: 6px;">
            ${labelHtml}
            <input type="number" class="v-input ctrl-input ch-amount-input dynamic-focus-input dynamic-focus-amount" value="${existingData ? Math.round(parseFloat(existingData.amount)) : ''}" step="1" oninput="onPaymentAmountInput()" placeholder="المبلغ" style="${amountStyle}">
            <select class="v-input ctrl-input ch-bank-select" style="${inputStyle}">
                ${banksHtml}
            </select>
            <input type="text" maxlength="14" class="v-input ctrl-input ch-no-input dynamic-focus-input dynamic-focus-number" value="${existingData?.chequeNo || ''}" placeholder="رقم الشيك" style="${inputStyle}">
            <input type="date" class="v-input ctrl-input ch-date-input" value="${existingData?.dueDate || today}" placeholder="mm/dd/yyyy" style="${inputStyle}">
            <input type="text" class="v-input ctrl-input bt-note-input" value="${existingData?.notes || ''}" placeholder="بيان الشيك" style="${inputStyle}">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                ${actionBtn}
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHtml);
    if (!existingData) {
        onPaymentAmountInput();
    }
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
    const grand = parseNum(document.getElementById('t-grand-local')?.textContent || 0);
    
    // Show/hide entire payment details panel depending on invoice grand total
    const paymentPanel = document.getElementById('payment-panel');
    if (paymentPanel) {
        if (grand > 0.01 && currentSaleType !== 'proforma') {
            paymentPanel.style.display = 'block';
        } else {
            paymentPanel.style.display = 'none';
            
            // Deactivate toggles
            const cashBtn = document.getElementById('toggle-cash');
            const bankBtn = document.getElementById('toggle-bank');
            const chequeBtn = document.getElementById('toggle-cheque');
            if (cashBtn) cashBtn.classList.remove('active');
            if (bankBtn) bankBtn.classList.remove('active');
            if (chequeBtn) chequeBtn.classList.remove('active');
            
            // Clear inputs
            const cashAmtInput = document.getElementById('pay-cash-amount');
            if (cashAmtInput) cashAmtInput.value = '0';
            const btList = document.getElementById('bank-transfers-list');
            if (btList) btList.innerHTML = '';
            const chList = document.getElementById('cheques-list');
            if (chList) chList.innerHTML = '';
            
            // Update sections visibility
            updatePaymentSectionsVisibility();
        }
    }
    
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const creditActive = document.getElementById('toggle-credit').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    const cashAmt = cashActive ? parseNum(document.getElementById('pay-cash-amount').value) : 0;
    
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
    
    // Credit is always the remaining balance!
    const creditAmt = Math.max(0, grand - (cashAmt + transfersSum + chequesSum));
    const creditInput = document.getElementById('pay-credit-amount');
    if (creditInput) {
        creditInput.value = Math.round(creditAmt);
    }
    
    // Collapse credit section if it becomes zero, showing it again if > 0
    const creditSection = document.getElementById('pay-credit-section');
    if (creditSection) {
        if (creditAmt <= 0.005) {
            creditSection.classList.add('collapsed');
        } else {
            creditSection.classList.remove('collapsed');
        }
    }
    
    const totalPaid = cashAmt + creditAmt + transfersSum + chequesSum;
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
    const enteredPayments = cashAmt + transfersSum + chequesSum;
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
    
    const paidIndicator = document.getElementById('payment-paid-indicator');
    const totalIndicator = document.getElementById('payment-total-indicator');
    const remainingIndicator = document.getElementById('payment-remaining-indicator');
    
    if (paidIndicator) paidIndicator.textContent = `${fmtNum(totalPaid)} ${cur}`;
    if (totalIndicator) totalIndicator.textContent = `${fmtNum(grand)} ${cur}`;
    if (remainingIndicator) {
        remainingIndicator.textContent = `${fmtNum(remaining)} ${cur}`;
        if (Math.abs(remaining) > 0.05) {
            remainingIndicator.style.color = '#f87171'; // Red
        } else {
            remainingIndicator.style.color = '#34d399'; // Green
        }
    }

    // Update progress bars & status badge dynamically
    const totalPayments = cashAmt + transfersSum + chequesSum + creditAmt;
    if (totalPayments > 0) {
        const cashPct = (cashAmt / totalPayments) * 100;
        const bankPct = (transfersSum / totalPayments) * 100;
        const chequePct = (chequesSum / totalPayments) * 100;
        const creditPct = (creditAmt / totalPayments) * 100;
        
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
            if (creditAmt <= 0.01) {
                // Play sound if transitioned to fully paid
                if (statusBadge.dataset.status !== 'fully-paid') {
                    if (typeof PlaySound !== 'undefined') PlaySound.success();
                    statusBadge.dataset.status = 'fully-paid';
                }
                statusBadge.textContent = '💳 مدفوعة بالكامل';
                statusBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                statusBadge.style.color = '#34d399';
                statusBadge.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.2)';
            } else if (cashAmt > 0 || transfersSum > 0 || chequesSum > 0) {
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

function autofillRemaining(type) {
    const grand = parseNum(document.getElementById('t-grand-local')?.textContent || 0);
    
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    const cash = (cashActive && type !== 'cash') ? parseNum(document.getElementById('pay-cash-amount')?.value || 0) : 0;
    
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
    
    const currentTotalPaidExceptTarget = cash + transfersSum + chequesSum;
    const remainingToFill = grand - currentTotalPaidExceptTarget;
    
    if (type === 'cash') {
        document.getElementById('pay-cash-amount').value = Math.round(Math.max(0, remainingToFill));
    } else if (type === 'bank') {
        const firstAmtInput = document.querySelector('#bank-transfers-list .bt-amount-input');
        if (firstAmtInput) {
            firstAmtInput.value = Math.round(Math.max(0, remainingToFill));
        }
    } else if (type === 'cheque') {
        const firstAmtInput = document.querySelector('#cheques-list .ch-amount-input');
        if (firstAmtInput) {
            firstAmtInput.value = Math.round(Math.max(0, remainingToFill));
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
        return 'cash';
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

function previewCurrentPromissoryNote() {
    const custSearch = document.getElementById('hdr-customer-search').value;
    const creditAmt = parseNum(document.getElementById('pay-credit-amount').value);
    if (creditAmt <= 0) {
        showToast('⚠️ الرجاء إدخال مبلغ آجل أولاً لمعاينة سند إقرار الدين', 'warning');
        return;
    }
    
    const cashActive = document.getElementById('toggle-cash').classList.contains('active');
    const bankActive = document.getElementById('toggle-bank').classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque').classList.contains('active');
    
    const cashAmt = cashActive ? parseNum(document.getElementById('pay-cash-amount').value) : 0;
    
    let bankTransfers = [];
    if (bankActive) {
        document.querySelectorAll('#bank-transfers-list .payment-row-item').forEach(row => {
            const amountEl = row.querySelector('.bt-amount-input');
            const amount = amountEl ? parseNum(amountEl.value) : 0;
            if (amount > 0) {
                bankTransfers.push({ amount });
            }
        });
    }
    
    let cheques = [];
    if (chequeActive) {
        document.querySelectorAll('#cheques-list .payment-row-item').forEach(row => {
            const amountEl = row.querySelector('.ch-amount-input');
            const amount = amountEl ? parseNum(amountEl.value) : 0;
            if (amount > 0) {
                cheques.push({ amount });
            }
        });
    }
    
    const tempInv = {
        invoiceNo: document.getElementById('hdr-inv-no').value,
        date: document.getElementById('hdr-date').value,
        dueDate: document.getElementById('pay-credit-due-date').value,
        customerName: custSearch || 'المورد',
        currency: getCurrency(),
        creditAmount: creditAmt,
        cashAmount: cashAmt,
        bankTransfers: bankTransfers,
        cheques: cheques,
        grandTotal: parseNum(document.getElementById('t-grand-local').textContent)
    };
    
    if (window.InvoicePrinter && typeof window.InvoicePrinter.printPromissoryNote === 'function') {
        window.InvoicePrinter.printPromissoryNote(tempInv);
    }
}

function printCurrentPromissoryNote(invNo) {
    const str = localStorage.getItem('erp_pur_' + invNo) || localStorage.getItem('erp_draft_' + invNo);
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

function loadPaymentDataIntoUI(data) {
    try {
        initMultiPaymentSystem(); // Clear first
        
        if (!data) return;
        
        const cashInput = document.getElementById('pay-cash-amount');
        const creditInput = document.getElementById('pay-credit-amount');
        
        // Check if hybrid/multipay fields exist
        if (data.paymentMethod === 'hybrid' || data.paymentMethod === 'multipay' || data.cashAmount !== undefined || data.creditAmount !== undefined) {
            const hasCash = (data.cashAmount && parseFloat(data.cashAmount) > 0);
            const hasCredit = (data.creditAmount && parseFloat(data.creditAmount) > 0);
            const hasBank = (data.bankTransfers && data.bankTransfers.length > 0);
            const hasCheque = (data.cheques && data.cheques.length > 0);
            
            if (hasCash) {
                document.getElementById('toggle-cash').classList.add('active');
                if (cashInput) cashInput.value = data.cashAmount;
                if (data.paymentAccountId) {
                    document.getElementById('hdr-payment-account').value = data.paymentAccountId;
                }
            }
            if (hasCredit) {
                document.getElementById('toggle-credit').classList.add('active');
                if (creditInput) creditInput.value = data.creditAmount;
                if (data.dueDate) {
                    document.getElementById('pay-credit-due-date').value = data.dueDate;
                }
            }
            if (hasBank) {
                document.getElementById('toggle-bank').classList.add('active');
                data.bankTransfers.forEach(bt => {
                    addBankTransferRow(bt);
                });
            }
            if (hasCheque) {
                document.getElementById('toggle-cheque').classList.add('active');
                data.cheques.forEach(ch => {
                    addChequeRow(ch);
                });
            }
        } else {
            // Legacy/fallback: standard cash, credit, bank, cheque
            if (data.paymentMethod === 'credit') {
                document.getElementById('toggle-credit').classList.add('active');
                if (creditInput) creditInput.value = data.grandTotal || data.totalAmount;
                if (data.dueDate) {
                    document.getElementById('pay-credit-due-date').value = data.dueDate;
                }
            } else if (data.paymentMethod === 'bank') {
                document.getElementById('toggle-bank').classList.add('active');
                addBankTransferRow({ bankAccountId: data.paymentAccount || data.paymentAccountId, reference: data.payDetail1, amount: data.grandTotal || data.totalAmount });
            } else if (data.paymentMethod === 'cheque') {
                document.getElementById('toggle-cheque').classList.add('active');
                addChequeRow({ chequeNo: data.payDetail1, bankName: data.payDetail2, dueDate: data.payDetail3 || data.date, amount: data.grandTotal || data.totalAmount });
            } else {
                // Default to cash
                document.getElementById('toggle-cash').classList.add('active');
                if (cashInput) cashInput.value = data.grandTotal || data.totalAmount;
                if (data.paymentAccount || data.paymentAccountId) {
                    document.getElementById('hdr-payment-account').value = data.paymentAccount || data.paymentAccountId;
                }
            }
        }
        
        updatePaymentSectionsVisibility();
        recalcPaymentSummary();
    } catch(e) {
        console.error("Error in loadPaymentDataIntoUI:", e);
    }
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
    const grand = parseNum(document.getElementById('t-grand-local')?.textContent || 0);
    
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
        input.value = Math.round(val);
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
