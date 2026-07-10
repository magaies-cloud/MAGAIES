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
//  GRID COLUMN CONFIGURATION (STATE)
// ════════════════════════════════════════
const DEFAULT_COLUMNS_CONFIG = {
    'num': { order: 0, visible: true, alias: '#', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 0, thousandsSeparator: '' },
    'item': { order: 1, visible: true, alias: 'الصنف المورد', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'right', decimals: 0, thousandsSeparator: '' },
    'unit': { order: 2, visible: true, alias: 'الوحدة', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 0, thousandsSeparator: '' },
    'factor': { order: 3, visible: true, alias: 'معادل', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 0, thousandsSeparator: '' },
    'qty': { order: 4, visible: true, alias: 'الكمية الوحدة', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'price': { order: 5, visible: true, alias: 'سعر الوحدة أجنبي', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'total': { order: 6, visible: true, alias: 'جملة أجنبي', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'baseqty': { order: 7, visible: true, alias: 'العدد', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'available': { order: 8, visible: true, alias: 'الكمية المتاحة', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'remaining': { order: 9, visible: true, alias: 'الكمية المتبقية', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'fcprice': { order: 10, visible: true, alias: 'سعر الوحدة', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'fctotal': { order: 11, visible: true, alias: 'الجملة', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'warehouse': { order: 12, visible: true, alias: 'المخزن', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 0, thousandsSeparator: '' },
    'shila': { order: 13, visible: true, alias: 'الشيلة', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 0, thousandsSeparator: ',' },
    'shila-rem': { order: 14, visible: true, alias: 'باقي سيخ', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 0, thousandsSeparator: ',' },
    'note': { order: 15, visible: true, alias: 'ملحوظة', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 0, thousandsSeparator: '' },
    'buy-price': { order: 16, visible: true, alias: 'سعر الشراء', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'sell-price': { order: 17, visible: true, alias: 'سعر البيع', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 2, thousandsSeparator: ',' },
    'actions': { order: 18, visible: true, alias: 'إجراء', color: '#cbd5e1', fontSize: '0.95rem', fontWeight: '900', align: 'center', decimals: 0, thousandsSeparator: '' }
};

let gridColumnsConfig = JSON.parse(localStorage.getItem('erp_purchase_grid_config')) || JSON.parse(JSON.stringify(DEFAULT_COLUMNS_CONFIG));

// Migration for existing saved configs
if (gridColumnsConfig['price'] && gridColumnsConfig['price'].alias === 'سعر الوحدة') gridColumnsConfig['price'].alias = 'سعر الوحدة أجنبي';
if (gridColumnsConfig['total'] && gridColumnsConfig['total'].alias === 'الجملة') gridColumnsConfig['total'].alias = 'جملة أجنبي';
if (gridColumnsConfig['fcprice'] && gridColumnsConfig['fcprice'].alias === 'سعر الوحدة أجنبي') gridColumnsConfig['fcprice'].alias = 'سعر الوحدة';
if (gridColumnsConfig['fctotal'] && gridColumnsConfig['fctotal'].alias === 'جملة الوحدة أجنبي') gridColumnsConfig['fctotal'].alias = 'الجملة';

for (let key in DEFAULT_COLUMNS_CONFIG) {
    if (!gridColumnsConfig[key]) gridColumnsConfig[key] = JSON.parse(JSON.stringify(DEFAULT_COLUMNS_CONFIG[key]));
}

// ════════════════════════════════════════
//  STATE
// ════════════════════════════════════════
let rows = [];
let rowIdCounter = 0;
let currentSaleType = 'direct';
let currentViewedInvoiceNo = null;

// ════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════
function fmtNum(n, colId = null) {
    if (n === 0 || n === '0' || !n) return '0';
    let decimals = 2; // Default
    let separator = ','; // Default
    if (colId && gridColumnsConfig[colId]) {
        decimals = parseInt(gridColumnsConfig[colId].decimals);
        if (isNaN(decimals)) decimals = 2;
        separator = gridColumnsConfig[colId].thousandsSeparator;
    }
    
    // If no decimals needed and it's a whole number, format without decimals
    if (decimals === 0) {
        let numStr = Math.round(Number(n)).toString();
        if (separator === ',') {
            return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        } else if (separator === '.') {
            return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        return numStr;
    }
    
    let numStr = Number(n).toFixed(decimals);
    if (separator === ',') {
        let parts = numStr.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join('.');
    } else if (separator === '.') {
        let parts = numStr.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        return parts.length > 1 ? parts.join(',') : parts[0];
    }
    return numStr;
}
function parseNum(str) {
    return parseFloat(String(str).replace(/,/g, '')) || 0;
}
function showToast(msg, type = 'success', ms = 3000) {
    const c = document.getElementById('toast-container');
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
    let list = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    let prefix = '';
    try {
        const userStr = localStorage.getItem('user');
        if(userStr) {
            const userObj = JSON.parse(userStr);
            if(userObj && userObj.username) {
                prefix = userObj.username.substring(0, 3).toUpperCase() + '-';
            }
        }
    } catch(e) {}
    let max = 0;
    list.forEach(inv => {
        let parts = (inv.invoiceNo || '').split('-');
        let noStr = parts.length > 1 ? parts[1] : parts[0];
        let no = parseInt(noStr || '0', 10);
        if (!isNaN(no) && no > max) {
            max = no;
        }
    });
    return `${prefix}${String(max + 1).padStart(6, '0')}`;
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

        const rawItems = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
        const invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        
        if (rawItems.length === 0) {
            console.warn('تنبيه: لا توجد أصناف في النظام! يرجى إضافة الأصناف من شاشة (البيانات الأساسية) أولاً.');
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
                unitsArray: unitsArray
            };
        });

        populateDropdowns();
        initHeader();
        renderGrid();
        recalcAll();
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
            const inv = purchases.find(p => p.id === invoiceId || p.invoiceNo === invoiceId);
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
    
    // Init Payment Account
    togglePaymentAccount();
}

function togglePaymentAccount() {
    const method = document.getElementById('hdr-payment-method').value;
    const group = document.getElementById('payment-account-group');
    const select = document.getElementById('hdr-payment-account');
    
    // Toggle Payment Details Row
    const payDetailsRow = document.getElementById('payment-details-row');
    const label1 = document.getElementById('pay-detail-1-label');
    const label2 = document.getElementById('pay-detail-2-label');
    const label3 = document.getElementById('pay-detail-3-label');

    if (payDetailsRow) {
        if (method === 'bank') {
            payDetailsRow.style.display = 'flex';
            label1.textContent = 'رقم التحويلة';
            label2.textContent = 'البنك المحول منه';
            label3.textContent = 'فرع البنك';
        } else if (method === 'cheque') {
            payDetailsRow.style.display = 'flex';
            label1.textContent = 'رقم الشيك';
            label2.textContent = 'اسم البنك';
            label3.textContent = 'الفرع';
        } else {
            payDetailsRow.style.display = 'none';
        }
    }

    if (!group || !select) return;

    if (method === 'credit') {
        group.style.display = 'none';
        return;
    }
    
    group.style.display = 'flex';
    select.innerHTML = '';
    
    let banks = [];
    let safes = [];
    try {
        banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
    } catch(e) {}
    
    // Add Safes first
    safes.forEach(s => {
        const o = document.createElement('option');
        o.value = s.id || s.code; 
        o.textContent = `💰 ${s.name}`;
        select.appendChild(o);
    });

    // Add Banks next
    banks.forEach(b => {
        const o = document.createElement('option');
        o.value = b.id || b.code; 
        o.textContent = `🏦 ${b.name}`;
        select.appendChild(o);
    });
    
    if (select.options.length === 0) {
        const o = document.createElement('option');
        o.value = ''; o.textContent = '-- لا توجد حسابات مالية --';
        select.appendChild(o);
    }
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
    let parts = currentNoStr.split('-');
    let numStr = parts.length > 1 ? parts[1] : parts[0];
    let prefix = parts.length > 1 ? parts[0] + '-' : '';
    let num = parseInt(numStr, 10);
    if (isNaN(num)) return;
    
    let found = false;
    while(num > 1 && !found) {
        num--;
        const testNo = prefix + String(num).padStart(6, '0');
        if(loadInvoiceFromStorage(testNo)) {
            showToast(`✅ تم استرجاع الفاتورة ${testNo}`);
            found = true;
        }
    }
    if(!found) showToast('ℹ️ لا توجد فواتير سابقة محفوظة', 'warning');
}

function loadNextInvoice() {
    const currentNoStr = document.getElementById('hdr-inv-no').value;
    let parts = currentNoStr.split('-');
    let numStr = parts.length > 1 ? parts[1] : parts[0];
    let prefix = parts.length > 1 ? parts[0] + '-' : '';
    let num = parseInt(numStr, 10);
    if (isNaN(num)) return;
    
    let found = false;
    // max check next 10 numbers
    for(let i=1; i<=10; i++) {
        const testNo = prefix + String(num + i).padStart(6, '0');
        if(loadInvoiceFromStorage(testNo)) {
            showToast(`✅ تم استرجاع الفاتورة ${testNo}`);
            found = true;
            break;
        }
    }
    
    if(!found) {
        // If no next invoice saved, jump to "New" mode
        onSuccessNewInvoice();
        showToast('✨ فاتورة جديدة', 'success');
    }
}

function loadInvoiceFromStorage(invNo) {
    let str = localStorage.getItem('erp_pur_' + invNo);
    if (!str) {
        str = localStorage.getItem('erp_draft_' + invNo);
    }
    let data;
    if (str) {
        try { data = JSON.parse(str); } catch(e) {}
    }
    
    if (!data) {
        let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
        data = allPurchases.find(inv => inv.invoiceNo === invNo || inv.id === invNo);
    }
    
    if (!data) return false;
    
    try {
        // If loading a confirmed invoice, enter Edit Mode
        if (data.status === 'confirmed' || localStorage.getItem('erp_pur_' + invNo)) {
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
        if(data.currency) document.getElementById('hdr-currency').value = data.currency;
        
        // Customer
        const custId = data.customer || data.customerId;
        if (custId) {
            const c = VENDORS.find(x => String(x.id) === String(custId));
            if (c) selectCustomer(c.id, c.name);
        }
        
        // Rows
        const itemsList = data.items || data.products || [];
        if(itemsList && itemsList.length) {
            itemsList.forEach(i => {
                addRow();
                const r = rows[rows.length-1];
                const itemId = i.itemId || i.id || i.item_id;
                const selectedUnit = i.selectedUnit || i.unit || i.unitName;
                const customFactor = i.customFactor !== undefined ? i.customFactor : i.factor;
                const quantity = i.quantity !== undefined ? i.quantity : (i.qty !== undefined ? i.qty : 0);
                const customBaseQty = i.customBaseQty;
                const price = i.price !== undefined ? i.price : (i.rate !== undefined ? i.rate : 0);
                const fcPrice = i.fcPrice;
                const warehouseId = i.warehouseId || i.warehouse;
                const costCenterId = i.costCenterId || i.costCenter;

                if (itemId) updateRow(r.id, 'itemId', itemId);
                if (selectedUnit) updateRow(r.id, 'selectedUnit', selectedUnit);
                if (customFactor !== undefined) updateRow(r.id, 'customFactor', customFactor);
                if (quantity !== undefined) updateRow(r.id, 'quantity', quantity);
                if (customBaseQty !== undefined) updateRow(r.id, 'customBaseQty', customBaseQty);
                if (price !== undefined) updateRow(r.id, 'price', price);
                if (fcPrice !== undefined) updateRow(r.id, 'fcPrice', fcPrice);
                if (warehouseId) updateRow(r.id, 'warehouseId', warehouseId);
                if (costCenterId) updateRow(r.id, 'costCenterId', costCenterId);
                if (i.qrCode) updateRow(r.id, 'qrCode', i.qrCode);
            });
        }
        
        renderGrid();
        recalcAll();
        if (window.applyImmutabilityUI) window.applyImmutabilityUI(data.status);
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
            
            let displayText = c.name;
            if (c.phone) displayText += ` <span style="font-size:0.8rem; color:#aaa;">(${c.phone})</span>`;
            if (c.code) displayText += ` <span style="font-size:0.8rem; color:#888;">[${c.code}]</span>`;
            
            item.innerHTML = displayText;
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
    document.getElementById('customer-dropdown-list').classList.add('show');
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
        if(listEl) listEl.classList.remove('show');
        
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
        
        // Merge dbMatches and newly added rawItems
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
                localMatches.push(existing); // Add to display list
            }
        });

        // Deduplicate localMatches by id
        const finalMatches = Array.from(new Map(localMatches.map(m => [String(m.id), m])).values());

        if (finalMatches.length === 0) {
            listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة.<br><small style="color:#888;">(حاول استخدام اسم آخر)</small></div>';
            return;
        }

        listEl.innerHTML = '';
        finalMatches.slice(0, 50).forEach(existing => {
            const div = document.createElement('div');
            div.className = 'custom-dropdown-item';
            
            let displayText = existing.name;
            if (isMobile && existing.unit) displayText += ' <span style="font-size:0.8rem; color:#aaa;">— ' + existing.unit + '</span>';
            if (existing.barcode) displayText += ' <span style="font-size:0.8rem; color:#888;">[' + existing.barcode + ']</span>';
            div.innerHTML = displayText;
            
            div.onmousedown = () => { updateRow(rowId, 'itemId', existing.id); renderGrid(); };
            div.onpointerdown = () => { updateRow(rowId, 'itemId', existing.id); renderGrid(); };
            listEl.appendChild(div);
        });
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">خطأ في الاتصال</div>';
    }
}

function showItemDropdown(rowId, isMobile = false) {
    const prefix = isMobile ? 'm-' : '';
    // Always show all items when focusing to allow choosing a new item easily
    renderItemDropdown(rowId, isMobile, '');
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    if (listEl) listEl.classList.add('show');
}

function hideItemDropdownDelayed(rowId, isMobile = false) {
    setTimeout(() => {
        const prefix = isMobile ? 'm-' : '';
        const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
        if(listEl) listEl.classList.remove('show');
        
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
            
            let displayText = existing.name;
            if (existing.unit) displayText += ' <span style="font-size:0.8rem; color:#aaa;">— ' + existing.unit + '</span>';
            if (existing.barcode) displayText += ' <span style="font-size:0.8rem; color:#888;">[' + existing.barcode + ']</span>';
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
function addRow(quiet = false) {
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
    renderGrid();
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
//  ADVANCED COLUMN SETTINGS & DRAG-DROP
// ════════════════════════════════════════
let draggedColId = null;

let isColResizing = false;
let currentResizingTh = null;
let colResizeStartX = 0;
let colResizeStartWidth = 0;

document.addEventListener('mousemove', (e) => {
    if (!isColResizing || !currentResizingTh) return;
    // RTL direction: moving mouse left (decreasing X) increases width
    const dx = colResizeStartX - e.clientX; 
    let newWidth = colResizeStartWidth + dx;
    if (newWidth < 40) newWidth = 40;
    
    currentResizingTh.style.width = newWidth + 'px';
    currentResizingTh.style.minWidth = newWidth + 'px';
});

document.addEventListener('mouseup', (e) => {
    if (isColResizing && currentResizingTh) {
        isColResizing = false;
        const colId = currentResizingTh.getAttribute('data-col-id');
        if (colId && gridColumnsConfig[colId]) {
            gridColumnsConfig[colId].width = currentResizingTh.style.width;
            localStorage.setItem('erp_purchase_grid_config', JSON.stringify(gridColumnsConfig));
        }
        currentResizingTh.setAttribute('draggable', 'true');
        currentResizingTh = null;
    }
});

function renderGridHead() {
    const thead = document.getElementById('grid-head');
    if (!thead) return;
    thead.innerHTML = '';
    
    const tr = document.createElement('tr');
    
    const cols = Object.keys(gridColumnsConfig)
        .filter(k => gridColumnsConfig[k].visible !== false)
        .sort((a, b) => gridColumnsConfig[a].order - gridColumnsConfig[b].order);
        
    cols.forEach(colId => {
        const conf = gridColumnsConfig[colId];
        const th = document.createElement('th');
        const currentAlias = conf.alias || DEFAULT_COLUMNS_CONFIG[colId].alias;
        
        let headerHtml = `<div style="line-height: 1.2;">${currentAlias}</div>`;
        th.innerHTML = headerHtml;
        th.title = `الاسم الأصلي: ${DEFAULT_COLUMNS_CONFIG[colId].alias}`;
        
        th.style.color = conf.color;
        if(conf.backgroundColor) th.style.backgroundColor = conf.backgroundColor;
        th.style.fontSize = conf.fontSize;
        th.style.fontWeight = conf.fontWeight;
        th.style.textAlign = conf.align || 'center';
        th.style.position = 'relative';
        
        // Enforce width
        if (conf.width) {
            th.style.width = conf.width;
            th.style.minWidth = conf.width;
        } else {
            if (colId === 'item') th.style.minWidth = '180px';
            else if (['price', 'total', 'fcprice', 'fctotal', 'note'].includes(colId)) th.style.minWidth = '100px';
            else th.style.minWidth = '70px';
        }
        
        th.draggable = true;
        th.setAttribute('data-col-id', colId);
        
        // Resizer Handle
        const resizer = document.createElement('div');
        resizer.style.width = '8px';
        resizer.style.height = '100%';
        resizer.style.position = 'absolute';
        resizer.style.left = '0'; // For RTL
        resizer.style.top = '0';
        resizer.style.cursor = 'col-resize';
        resizer.style.userSelect = 'none';
        resizer.style.zIndex = '10';
        resizer.addEventListener('mouseenter', () => { resizer.style.backgroundColor = 'rgba(56, 189, 248, 0.3)'; });
        resizer.addEventListener('mouseleave', () => { resizer.style.backgroundColor = 'transparent'; });
        
        resizer.addEventListener('mousedown', (e) => {
            isColResizing = true;
            currentResizingTh = th;
            colResizeStartX = e.clientX;
            colResizeStartWidth = th.offsetWidth;
            th.removeAttribute('draggable'); // Prevent drag and drop
            e.stopPropagation();
            e.preventDefault();
        });
        
        resizer.addEventListener('click', (e) => e.stopPropagation());
        resizer.addEventListener('contextmenu', (e) => e.stopPropagation());
        
        th.appendChild(resizer);
        
        th.addEventListener('dragstart', (e) => {
            if (isColResizing) { e.preventDefault(); return; }
            draggedColId = colId;
            e.dataTransfer.effectAllowed = 'move';
            th.style.opacity = '0.5';
        });
        
        th.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            th.style.backgroundColor = 'rgba(255, 215, 0, 0.2)'; 
        });
        
        th.addEventListener('dragleave', (e) => {
            th.style.backgroundColor = '';
        });
        
        th.addEventListener('drop', (e) => {
            e.preventDefault();
            th.style.backgroundColor = '';
            if (draggedColId && draggedColId !== colId) {
                const oldOrder = gridColumnsConfig[draggedColId].order;
                const newOrder = gridColumnsConfig[colId].order;
                
                gridColumnsConfig[draggedColId].order = newOrder;
                gridColumnsConfig[colId].order = oldOrder;
                
                localStorage.setItem('erp_purchase_grid_config', JSON.stringify(gridColumnsConfig));
                renderGrid();
            }
        });
        
        th.addEventListener('dragend', (e) => {
            th.style.opacity = '1';
        });
        
        th.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            openAdvancedColSettings(colId);
        });
        
        tr.appendChild(th);
    });
    
    thead.appendChild(tr);
}

function openAdvancedColSettings(colId) {
    // Save backup for live preview cancellation
    window.previewBackupConfig = JSON.parse(JSON.stringify(gridColumnsConfig));
    
    const conf = gridColumnsConfig[colId];
    document.getElementById('edit-col-id').value = colId;
    document.getElementById('edit-col-alias').value = conf.alias || '';
    document.getElementById('edit-col-alias').placeholder = `الاسم المخصص... (الأصلي: ${DEFAULT_COLUMNS_CONFIG[colId].alias})`;
    document.getElementById('edit-col-color').value = conf.color || '#cbd5e1';
    document.getElementById('edit-col-bgcolor').value = conf.backgroundColor || '#1e293b';
    document.getElementById('edit-col-fontsize').value = conf.fontSize || '0.95rem';
    document.getElementById('edit-col-fontweight').value = conf.fontWeight || '900';
    document.getElementById('edit-col-align').value = conf.align || 'center';
    document.getElementById('edit-col-decimals').value = conf.decimals !== undefined ? conf.decimals : 2;
    document.getElementById('edit-col-thousands').value = conf.thousandsSeparator !== undefined ? conf.thousandsSeparator : ',';
    document.getElementById('edit-col-width').value = conf.width || '';
    if(document.getElementById('edit-col-apply-all')) document.getElementById('edit-col-apply-all').checked = false;
    
    // Populate the global visibility list
    const visibilityList = document.getElementById('global-col-visibility-list');
    if (visibilityList) {
        visibilityList.innerHTML = '';
        Object.keys(gridColumnsConfig)
            .sort((a, b) => gridColumnsConfig[a].order - gridColumnsConfig[b].order)
            .forEach(k => {
                const cConf = gridColumnsConfig[k];
                const cAlias = cConf.alias || DEFAULT_COLUMNS_CONFIG[k].alias;
                const isVisible = cConf.visible !== false;
                
                const div = document.createElement('div');
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                div.style.gap = '8px';
                
                const chk = document.createElement('input');
                chk.type = 'checkbox';
                chk.id = `global-vis-${k}`;
                chk.checked = isVisible;
                chk.style.width = '18px';
                chk.style.height = '18px';
                chk.style.cursor = 'pointer';
                
                const lbl = document.createElement('label');
                lbl.htmlFor = `global-vis-${k}`;
                lbl.textContent = cAlias;
                lbl.style.color = '#fff';
                lbl.style.cursor = 'pointer';
                lbl.style.fontSize = '0.9rem';
                lbl.style.whiteSpace = 'nowrap';
                lbl.style.overflow = 'hidden';
                lbl.style.textOverflow = 'ellipsis';
                
                div.appendChild(chk);
                div.appendChild(lbl);
                visibilityList.appendChild(div);
            });
    }
    
    document.getElementById('advanced-col-settings-modal').classList.add('visible');
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

        // Get sorted visible columns
        const cols = Object.keys(gridColumnsConfig)
            .filter(k => gridColumnsConfig[k].visible !== false)
            .sort((a, b) => gridColumnsConfig[a].order - gridColumnsConfig[b].order);

        const cellsMap = {
            'num': `<td style="text-align:${gridColumnsConfig['num'].align};"><div class="row-num" style="color:${gridColumnsConfig['num'].color}; font-size:${gridColumnsConfig['num'].fontSize}; font-weight:${gridColumnsConfig['num'].fontWeight};">${idx + 1}</div></td>`,
            
            'item': `<td style="position:relative; text-align:${gridColumnsConfig['item'].align};">
                <input type="text" class="g-input" id="item-search-${row.id}" placeholder="ابحث أو اختر الصنف..." 
                    value="${item ? item.name : ''}" autocomplete="one-time-code"
                    style="font-size: ${gridColumnsConfig['item'].fontSize} !important; font-weight: ${gridColumnsConfig['item'].fontWeight} !important; color: ${gridColumnsConfig['item'].color} !important; text-align:${gridColumnsConfig['item'].align};"
                    onfocus="this.select(); showItemDropdown(${row.id})" 
                    onblur="hideItemDropdownDelayed(${row.id})" 
                    oninput="filterItemDropdown(${row.id})">
                <div class="custom-dropdown-list" id="item-dropdown-list-${row.id}" style="text-align:right; min-width: 220px; white-space: normal;">
                </div>
            </td>`,
            
            'unit': `<td style="text-align:${gridColumnsConfig['unit'].align};">
                <select class="g-select" style="min-width: 80px; font-size: ${gridColumnsConfig['unit'].fontSize}; font-weight: ${gridColumnsConfig['unit'].fontWeight}; color: ${gridColumnsConfig['unit'].color}; text-align:${gridColumnsConfig['unit'].align};" onchange="updateRow(${row.id},'selectedUnit',this.value); renderGrid();" tabindex="-1">
                    ${itemUnitsHtml}
                </select>
            </td>`,
            
            'factor': `<td style="text-align:${gridColumnsConfig['factor'].align};"><input type="text" inputmode="decimal" class="g-input" value="${row.customFactor !== undefined ? row.customFactor : calcs.unitFactor}" placeholder="1"
                onfocus="this.select()" oninput="updateRow(${row.id},'customFactor',this.value)"
                onblur="this.value=parseNum(this.value)||1" tabindex="-1" style="font-size: ${gridColumnsConfig['factor'].fontSize}; font-weight: ${gridColumnsConfig['factor'].fontWeight}; color: ${gridColumnsConfig['factor'].color}; text-align:${gridColumnsConfig['factor'].align};"></td>`,
                
            'qty': `<td style="text-align:${gridColumnsConfig['qty'].align};"><input type="text" inputmode="decimal" class="g-input" data-qty-input="${row.id}" value="${Number.isInteger(row.quantity) ? row.quantity : parseFloat((row.quantity || 0).toFixed(4)) || ''}" placeholder="0"
                onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)"
                onblur="this.value=parseNum(this.value)||''" style="font-size: ${gridColumnsConfig['qty'].fontSize} !important; font-weight: ${gridColumnsConfig['qty'].fontWeight} !important; color: ${gridColumnsConfig['qty'].color} !important; text-align:${gridColumnsConfig['qty'].align};"></td>`,
                
            'price': `<td style="text-align:${gridColumnsConfig['price'].align};">
                <input type="text" inputmode="decimal" class="g-input" data-price-input="${row.id}" value="${fmtNum(row.price, 'price')}"
                    onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                    onblur="this.value=fmtNum(parseNum(this.value), 'price')" 
                    onmousedown="startPriceHold(${row.id})" onmouseup="clearPriceHold()" onmouseleave="clearPriceHold()"
                    ontouchstart="startPriceHold(${row.id})" ontouchend="clearPriceHold()"
                    style="width:100%; font-size: ${gridColumnsConfig['price'].fontSize} !important; font-weight: ${gridColumnsConfig['price'].fontWeight} !important; color: ${gridColumnsConfig['price'].color} !important; text-align:${gridColumnsConfig['price'].align};">
            </td>`,
            
            'total': `<td style="text-align:${gridColumnsConfig['total'].align};"><span class="g-readonly total-val" style="font-size: ${gridColumnsConfig['total'].fontSize} !important; font-weight: ${gridColumnsConfig['total'].fontWeight} !important; color: ${gridColumnsConfig['total'].color} !important;" data-total="${row.id}">${fmtNum(calcs.total, 'total')}</span></td>`,
            
            'baseqty': `<td style="text-align:${gridColumnsConfig['baseqty'].align};">
                <div style="display:flex; flex-direction:column; align-items:${gridColumnsConfig['baseqty'].align === 'right' ? 'flex-start' : (gridColumnsConfig['baseqty'].align === 'left' ? 'flex-end' : 'center')}; justify-content:center;">
                    <input type="text" inputmode="decimal" class="g-input" data-base-input="${row.id}" value="${fmtNum(calcs.baseQty, 'baseqty')}" placeholder="0"
                        onfocus="this.select()" oninput="updateRow(${row.id},'customBaseQty',this.value)"
                        onblur="this.value=fmtNum(parseNum(this.value), 'baseqty')" style="font-size: ${gridColumnsConfig['baseqty'].fontSize}; font-weight: ${gridColumnsConfig['baseqty'].fontWeight}; color: ${gridColumnsConfig['baseqty'].color}; text-align:${gridColumnsConfig['baseqty'].align}; width:100%;" tabindex="-1">
                    <span style="display:block; font-size:10px; line-height:1; color:var(--text-muted); margin-top:2px;">${item ? item.baseUnit : ''}</span>
                </div>
            </td>`,
            
            'available': `<td style="text-align:${gridColumnsConfig['available'].align};"><input type="text" inputmode="decimal" class="g-input" placeholder="0" value="${row.availableQty !== undefined ? fmtNum(row.availableQty, 'available') : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'availableQty',this.value)" onblur="this.value=fmtNum(parseNum(this.value), 'available')" style="font-size: ${gridColumnsConfig['available'].fontSize}; font-weight: ${gridColumnsConfig['available'].fontWeight}; color: ${gridColumnsConfig['available'].color}; text-align:${gridColumnsConfig['available'].align};"></td>`,
            
            'remaining': `<td style="text-align:${gridColumnsConfig['remaining'].align};"><input type="text" inputmode="decimal" class="g-input" placeholder="0" value="${row.remainingQty !== undefined ? fmtNum(row.remainingQty, 'remaining') : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'remainingQty',this.value)" onblur="this.value=fmtNum(parseNum(this.value), 'remaining')" style="font-size: ${gridColumnsConfig['remaining'].fontSize}; font-weight: ${gridColumnsConfig['remaining'].fontWeight}; color: ${gridColumnsConfig['remaining'].color}; text-align:${gridColumnsConfig['remaining'].align};"></td>`,
            
            'fcprice': `<td style="text-align:${gridColumnsConfig['fcprice'].align};">
                <input type="text" inputmode="decimal" class="g-input" data-fcprice-input="${row.id}" value="${fmtNum(row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1)), 'fcprice')}"
                    onfocus="this.select()" oninput="updateRow(${row.id},'fcPrice',this.value)"
                    onblur="this.value=fmtNum(parseNum(this.value), 'fcprice')" style="font-size: ${gridColumnsConfig['fcprice'].fontSize}; font-weight: ${gridColumnsConfig['fcprice'].fontWeight}; color: ${gridColumnsConfig['fcprice'].color}; text-align:${gridColumnsConfig['fcprice'].align};" tabindex="-1">
            </td>`,
            
            'fctotal': `<td style="text-align:${gridColumnsConfig['fctotal'].align};"><span class="g-readonly total-fc-val" style="font-size: ${gridColumnsConfig['fctotal'].fontSize}; font-weight: ${gridColumnsConfig['fctotal'].fontWeight}; color: ${gridColumnsConfig['fctotal'].color};" data-total-fc="${row.id}">${fmtNum((row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1))) * (row.quantity || 0), 'fctotal')}</span></td>`,
            
            'warehouse': `<td style="text-align:${gridColumnsConfig['warehouse'].align};">
                <select class="g-select" onchange="updateRow(${row.id},'warehouseId',this.value)" tabindex="-1" style="font-size: ${gridColumnsConfig['warehouse'].fontSize}; font-weight: ${gridColumnsConfig['warehouse'].fontWeight}; color: ${gridColumnsConfig['warehouse'].color}; text-align:${gridColumnsConfig['warehouse'].align};">
                    <option value="">نفس الفاتورة ↩</option>
                    ${WAREHOUSES.map(w => `<option value="${w.id}" ${w.id === row.warehouseId ? 'selected' : ''}>${w.name}</option>`).join('')}
                </select>
            </td>`,
            
            'shila': `<td style="text-align:${gridColumnsConfig['shila'].align};"><span class="g-readonly" style="font-size: ${gridColumnsConfig['shila'].fontSize}; font-weight: ${gridColumnsConfig['shila'].fontWeight}; color: ${gridColumnsConfig['shila'].color};" data-shila="${row.id}">${fmtNum(calcs.shilaQty, 'shila')}</span></td>`,
            
            'shila-rem': `<td style="text-align:${gridColumnsConfig['shila-rem'].align};"><span class="g-readonly" style="font-size: ${gridColumnsConfig['shila-rem'].fontSize}; font-weight: ${gridColumnsConfig['shila-rem'].fontWeight}; color: ${gridColumnsConfig['shila-rem'].color};" data-shila-rem="${row.id}">${fmtNum(calcs.shilaRem, 'shila-rem')}</span></td>`,
            
            'note': `<td style="text-align:${gridColumnsConfig['note'].align};"><input type="text" class="g-input" placeholder="ملحوظة..." value="${row.note || ''}" oninput="updateRow(${row.id},'note',this.value)" style="font-size: ${gridColumnsConfig['note'].fontSize}; font-weight: ${gridColumnsConfig['note'].fontWeight}; color: ${gridColumnsConfig['note'].color}; text-align:${gridColumnsConfig['note'].align};"></td>`,
            
            'buy-price': `<td style="text-align:${gridColumnsConfig['buy-price'].align};"><input type="text" inputmode="decimal" class="g-input" placeholder="0" value="${row.buyPrice !== undefined ? fmtNum(row.buyPrice, 'buy-price') : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'buyPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value), 'buy-price')" style="font-size: ${gridColumnsConfig['buy-price'].fontSize}; font-weight: ${gridColumnsConfig['buy-price'].fontWeight}; color: ${gridColumnsConfig['buy-price'].color}; text-align:${gridColumnsConfig['buy-price'].align};"></td>`,
            
            'sell-price': `<td style="text-align:${gridColumnsConfig['sell-price'].align};"><input type="text" inputmode="decimal" class="g-input" placeholder="0" value="${row.sellPrice !== undefined ? fmtNum(row.sellPrice, 'sell-price') : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'sellPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value), 'sell-price')" style="font-size: ${gridColumnsConfig['sell-price'].fontSize}; font-weight: ${gridColumnsConfig['sell-price'].fontWeight}; color: ${gridColumnsConfig['sell-price'].color}; text-align:${gridColumnsConfig['sell-price'].align};"></td>`,
            
            'actions': `<td style="text-align:${gridColumnsConfig['actions'].align};">
                <div style="display: flex; gap: 5px; justify-content: ${gridColumnsConfig['actions'].align === 'right' ? 'flex-start' : (gridColumnsConfig['actions'].align === 'left' ? 'flex-end' : 'center')};">
                    <button class="edit-btn" onclick="document.getElementById('item-search-${row.id}').focus()" title="تعديل السطر" style="background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.2); color: #38bdf8; border-radius: 6px; padding: 6px; cursor: pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="del-btn" onclick="removeRow(${row.id})" title="حذف السطر">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </td>`
        };

        // ── Desktop Row ──
        const tr = document.createElement('tr');
        
        let rowHtml = '';
        cols.forEach(colId => {
            rowHtml += cellsMap[colId];
        });
        
        tr.innerHTML = rowHtml;
        
        // Apply background colors dynamically
        Array.from(tr.children).forEach((td, index) => {
            const colId = cols[index];
            const conf = gridColumnsConfig[colId];
            if (conf && conf.backgroundColor) {
                td.style.backgroundColor = conf.backgroundColor;
                const inputs = td.querySelectorAll('input, select');
                inputs.forEach(input => {
                    input.style.backgroundColor = conf.backgroundColor;
                });
            }
        });
        
        tbody.appendChild(tr);

        // ── Mobile Card ──
        const card = document.createElement('div');
        card.className = 'm-card';
        card.innerHTML = `
            <div class="m-card-num">${idx + 1}</div>
            <div style="position: absolute; left: 10px; top: 10px; display: flex; gap: 5px;">
                <button class="m-card-edit" onclick="document.getElementById('m-item-search-${row.id}').focus()" title="تعديل" style="background: transparent; border: none; color: #38bdf8; cursor: pointer;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
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
                <div style="display:flex; gap:8px;">
                    <div class="ctrl-group" style="flex:1;">
                        <label class="label">سعر الشراء</label>
                        <input type="text" inputmode="decimal" class="ctrl-input" placeholder="0" value="${row.buyPrice !== undefined ? fmtNum(row.buyPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'buyPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                    <div class="ctrl-group" style="flex:1;">
                        <label class="label">سعر البيع</label>
                        <input type="text" inputmode="decimal" class="ctrl-input" placeholder="0" value="${row.sellPrice !== undefined ? fmtNum(row.sellPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'sellPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
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

    // Recompute fcPrice for all rows and update DOM
    rows.forEach(row => {
        if (isBaseSDG) {
            row.fcPrice = row.price / exRate;
        } else {
            row.fcPrice = row.price * exRate;
        }
        updateRowCalcs(row.id);
    });

    const invTotal = getInvoiceTotal();
    const labor = parseNum(document.getElementById('t-labor').value);
    const transport = parseNum(document.getElementById('t-transport').value);
    const discountAmt = parseNum(document.getElementById('discount-amt').value);
    const taxAmt = parseNum(document.getElementById('tax-amt').value);
    const additionAmt = parseNum(document.getElementById('addition-amt').value);

    const grand = invTotal + labor + transport + additionAmt + taxAmt - discountAmt;

    // Update totals display
    document.getElementById('t-invoice-total').textContent = fmtNum(invTotal) + ' ' + cur;
    document.getElementById('t-additions').textContent = '+ ' + fmtNum(additionAmt) + ' ' + cur;
    document.getElementById('t-tax').textContent = '+ ' + fmtNum(taxAmt) + ' ' + cur;
    document.getElementById('t-discount').textContent = '- ' + fmtNum(discountAmt) + ' ' + cur;
    document.getElementById('t-grand').innerHTML = fmtNum(grand) + ' <span style="color: #ef4444; font-size: 0.9em; text-shadow: none;">' + cur + '</span>';

    // Footer
    document.getElementById('footer-grand').textContent = fmtNum(grand) + ' ' + cur;

    // Dynamic UI Animation — toggle CSS classes
    const totalsCard = document.getElementById('totals-calc-card');
    const formCard = document.getElementById('form-fields-card');
    if (totalsCard && formCard) {
        const hasItems = rows.some(r => (parseFloat(r.price) || 0) * (parseFloat(r.quantity) || 0) > 0);

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
}

// ════════════════════════════════════════
//  SAVE / SUBMIT
// ════════════════════════════════════════
function saveDraft() {
    if (rows.length === 0) { showToast('⚠️ لا توجد أصناف لحفظها', 'warning'); return; }
    const data = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        invoiceNo: document.getElementById('hdr-inv-no').value,
        date: document.getElementById('hdr-date').value,
        customer: document.getElementById('hdr-customer').value,
        saleType: currentSaleType,
        currency: getCurrency(),
        exchangeRate: document.getElementById('hdr-exchange-rate').value,
        paymentMethod: document.getElementById('hdr-payment-method').value,
        paymentAccount: document.getElementById('hdr-payment-account') ? document.getElementById('hdr-payment-account').value : '',
        payDetail1: document.getElementById('pay-detail-1') ? document.getElementById('pay-detail-1').value : '',
        payDetail2: document.getElementById('pay-detail-2') ? document.getElementById('pay-detail-2').value : '',
        payDetail3: document.getElementById('pay-detail-3') ? document.getElementById('pay-detail-3').value : '',
        warehouse: document.getElementById('hdr-warehouse').value,
        description: document.getElementById('hdr-description').value,
        items: rows,
        notes: document.getElementById('inv-notes').value,
        status: 'draft',
        grandTotal: parseNum(document.getElementById('t-grand').textContent),
        totalAmount: parseNum(document.getElementById('t-grand').textContent),
        laborCost: parseNum(document.getElementById('t-labor').value),
        transportCost: parseNum(document.getElementById('t-transport').value)
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
        const exactMatch = VENDORS.find(c => c.name === searchVal);
        if (exactMatch) {
            selectCustomer(exactMatch.id, exactMatch.name);
            custId = exactMatch.id;
        }
    }

    if (!custId) { 
        showToast('❌ يرجى تحديد المورد من القائمة المنسدلة لتأكيد الفاتورة', 'error'); 
        return; 
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

    // Commit the invoice number
    let committedNo;
    if (currentViewedInvoiceNo) {
        committedNo = currentViewedInvoiceNo; // keep old number in edit mode
    } else {
        committedNo = commitInvoiceNumber(currentSaleType); // increment
    }
    const cur = getCurrency();
    const grandTotal = document.getElementById('t-grand').textContent;

    // Save invoice data
    const data = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        invoiceNo: committedNo,
        date: document.getElementById('hdr-date').value,
        customer: document.getElementById('hdr-customer').value,
        saleType: currentSaleType,
        currency: cur,
        exchangeRate: document.getElementById('hdr-exchange-rate').value,
        paymentMethod: document.getElementById('hdr-payment-method').value,
        paymentAccount: document.getElementById('hdr-payment-account') ? document.getElementById('hdr-payment-account').value : '',
        payDetail1: document.getElementById('pay-detail-1') ? document.getElementById('pay-detail-1').value : '',
        payDetail2: document.getElementById('pay-detail-2') ? document.getElementById('pay-detail-2').value : '',
        payDetail3: document.getElementById('pay-detail-3') ? document.getElementById('pay-detail-3').value : '',
        warehouse: document.getElementById('hdr-warehouse').value,
        description: document.getElementById('hdr-description').value,
        items: rows.map(r => ({...r})),
        notes: document.getElementById('inv-notes').value,
        status: 'confirmed',
        grandTotal: parseNum(document.getElementById('t-grand').textContent),
        totalAmount: parseNum(document.getElementById('t-grand').textContent),
        laborCost: parseNum(document.getElementById('t-labor').value),
        transportCost: parseNum(document.getElementById('t-transport').value)
    };

    // Update Bank/Safe balance
    const paymentMethod = document.getElementById('hdr-payment-method').value;
    const paymentAccountId = document.getElementById('hdr-payment-account').value;
    const grandAmount = parseNum(grandTotal);
    let usedAccountName = '';
    
    if (paymentMethod !== 'credit' && paymentAccountId) {
        let banks = [];
        try { banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
        const bIdx = banks.findIndex(b => b.id === paymentAccountId);
        if (bIdx > -1) {
            let currentBalance = parseFloat(banks[bIdx].currentBalance);
            if (isNaN(currentBalance)) {
                currentBalance = parseFloat(banks[bIdx].openingBalance) || 0;
            }
            banks[bIdx].currentBalance = currentBalance - grandAmount; // Subtract for purchases
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            data.paymentAccountId = paymentAccountId;
            usedAccountName = banks[bIdx].name;
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

    // Update Supplier Balance (Debt)
    if (paymentMethod === 'credit' && custId && custId !== 'walk-in') {
        let suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        const sIdx = suppliers.findIndex(s => s.id === custId);
        if (sIdx > -1) {
            let currentBalance = parseFloat(suppliers[sIdx].currentBalance);
            if (isNaN(currentBalance)) currentBalance = 0;
            suppliers[sIdx].currentBalance = currentBalance + grandAmount;
            localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
        }
    }

    // Add to local inventory cache and live objects
    let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
    const mainWh = document.getElementById('hdr-warehouse').value;
    for (const row of rows) {
        const item = ITEMS.find(i => String(i.id) === String(row.itemId));
        if (!item) continue;
        const calcs = getRowCalcs(row);
        const whId = row.warehouseId || mainWh;
        const whName = WAREHOUSES.find(w => w.id === whId)?.name || 'المخزن الرئيسي';
        
        let itemInv = currentInvData.find(inv => String(inv.itemId) === String(row.itemId) && inv.warehouse === whName);
        if (itemInv) {
            itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) + calcs.baseQty;
        } else {
            currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: calcs.baseQty, minQty: 10 });
        }
        
        // Update live ITEMS array
        if (item.stock && item.stock[whId] !== undefined) {
            item.stock[whId] += calcs.baseQty;
        } else if (item.stock) {
            item.stock[whId] = calcs.baseQty;
        }
    }
    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

    // Show professional success modal (we can pass the warnings array so it still shows them if desired)
    // To grab the actual warnings to display in success modal, we need to generate them again if ignoreWarnings is true.
    let finalWarnings = [];

    if (usedAccountName) {
        showToast(`تم خصم ${fmtNum(grandAmount)} ${cur} من رصيد: ${usedAccountName}`, 'success', 5000);
    }

    onSuccessNewInvoice(false);
    showSuccessModal(committedNo, grandTotal, cur, finalWarnings);

    // In production: POST to API
    // fetch('/api/v1/sales/invoices', { method:'POST', body:JSON.stringify(data) })
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

    let oldInvStr = localStorage.getItem('erp_pur_' + currentInvNo);
    let oldInv = null;
    if (oldInvStr) {
        try { oldInv = JSON.parse(oldInvStr); } catch(e) {}
    }
    
    if (!oldInv) {
        let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
        oldInv = allPurchases.find(inv => inv.invoiceNo === currentInvNo || inv.id === currentInvNo);
    }

    if (!oldInv) {
        localStorage.removeItem('erp_draft_' + currentInvNo);
        onSuccessNewInvoice(); 
        showToast('🗑️ تم مسح بيانات الفاتورة', 'success');
        return;
    }
    const grandAmount = parseFloat(oldInv.grandTotal) || 0;

    if (oldInv.paymentMethod === 'credit' && oldInv.customer && oldInv.customer !== 'walk-in') {
        let suppliers = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        const sIdx = suppliers.findIndex(s => s.id === oldInv.customer);
        if (sIdx > -1) {
            let currentBalance = parseFloat(suppliers[sIdx].currentBalance) || 0;
            suppliers[sIdx].currentBalance = currentBalance - grandAmount; // Revert debt
            localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
        }
    }

    if (oldInv.paymentMethod !== 'credit' && oldInv.paymentAccountId) {
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
        if (bIdx > -1) {
            let currentBalance = parseFloat(banks[bIdx].currentBalance) || 0;
            banks[bIdx].currentBalance = currentBalance + grandAmount; // Revert payment
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        }
    }

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
            } else {
                currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: -baseQty, minQty: 10 });
            }
            
            if (item.stock && item.stock[whId] !== undefined) {
                item.stock[whId] -= baseQty;
            }
            if (item.actualStock && item.actualStock[whId] !== undefined) {
                item.actualStock[whId] -= baseQty;
            }
        }
    }
    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

    let allPurchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
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
    // In production: navigate to sales list
    // window.location.href = 'sales-list.html';
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
    const grandVal    = parseNum(document.getElementById('t-grand')?.textContent || '0');

    const data = {
        type:          'purchase',
        typeLabel:     'فاتورة مشتريات',
        invoiceNo:     document.getElementById('hdr-inv-no').value,
        date:          document.getElementById('hdr-date').value,
        customerName:  document.getElementById('hdr-customer-search').value || 'مورد نقدي',
        currency:      getCurrency(),
        exchangeRate:  parseNum(document.getElementById('hdr-exchange-rate')?.value || '1'),
        paymentMethod: document.getElementById('hdr-payment-method').value,
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
