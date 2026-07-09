/* ═══════════════════════════════════════════════════════
   sales-invoice.js — POS Engine v6.0
   Real-time Reactive Calculations · Sequential Invoices
   ═══════════════════════════════════════════════════════ */

// Global logout function (used by sidebar)
// Global logout is handled by app.js

// ════════════════════════════════════════
//  MOCK DATA (Replace with API calls)
// ════════════════════════════════════════

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

// ════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════
function fmtNum(n) {
    if (n === 0 || n === '0' || !n) return '0';
    return Number(n).toLocaleString('en-US');
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
const SALE_PREFIX = { direct:'', split:'', delivery:'', proforma:'', costcenter:'' };

// Peek: show the next number WITHOUT incrementing (safe for refresh)
function peekNextInvoiceNumber(type) {
    let list = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
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
document.addEventListener('DBReady', () => {
    // Load real customer data from master-data
    const localCusts = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    if(localCusts.length > 0) {
        CUSTOMERS = localCusts.map(c => ({
            id: c.id,
            name: c.name,
            ledger: Math.abs(c.currentBalance < 0 ? c.currentBalance : 0),
            checks: 0,
            creditLimit: c.creditLimit || 0,
            status: c.status || 'active'
        }));
        CUSTOMERS.unshift({ id:'walk-in', name:'زبون نقدي (Walk-in)', ledger:0, checks:0, creditLimit:0, status:'active' });
    }

    // Load Master Data Items
    const rawItems = (JSON.parse(localStorage.getItem('erp_items_v1')) || []).filter(x => !x.deleted);
    const invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
    
    if (rawItems.length === 0) {
        alert('تنبيه: لا توجد أصناف في النظام! يرجى إضافة الأصناف من شاشة (البيانات الأساسية) أولاً.');
    }

    // Map raw items to POS expected format
    ITEMS = rawItems.map(item => {
        let actualStockMap = {};
        let reservedStockMap = {};
        let itemInv = invData.filter(inv => inv.itemId === item.id);
        itemInv.forEach(inv => {
            let whId = WAREHOUSES.find(w => w.name === inv.warehouse)?.id || 'w-001';
            actualStockMap[whId] = (actualStockMap[whId] || 0) + (parseFloat(inv.qty) || parseFloat(inv.quantity) || 0);
            reservedStockMap[whId] = (reservedStockMap[whId] || 0) + (parseFloat(inv.reservedQuantity) || 0);
        });

        // Clean price — support new schema (sellingPrice) and old (salePrice/price)
        let sp = item.sellingPrice || item.salePrice || item.price || 0;
        sp = sp.toString().replace(/,/g, '');
        
        const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'قطعة';

        // Build unitsArray from new schema (item.units[]) or old (item.unitsArray[])
        let unitsArray = [];
        if (item.units && item.units.length > 0) {
            // New Item Master schema: units[{unitName, factor, cashPrice, ...}]
            unitsArray = item.units.map(u => ({ name: u.unitName, factor: u.factor || 1 }));
        } else if (item.unitsArray && item.unitsArray.length > 0) {
            // Old schema: unitsArray[{name, factor}]
            unitsArray = item.unitsArray;
        }
        // Ensure base unit is always first
        if (unitsArray.length === 0 || !unitsArray.find(u => u.name === baseUnit)) {
            unitsArray = [{ name: baseUnit, factor: 1 }, ...unitsArray];
        }

        return {
            id: item.id,
            name: item.name,
            unit: baseUnit,
            baseUnit: baseUnit,
            conversionFactor: 1, 
            bundleFactor: 1,
            price: parseFloat(sp) || 0,
            actualStock: actualStockMap,
            reservedStock: reservedStockMap,
            unitsArray: unitsArray,
            maxDiscount: item.maxDiscount || 0,
            status: item.status || 'active'
        };
    }).filter(item => item.status !== 'inactive');

    populateDropdowns();
    initHeader();
    renderGrid();
    recalcAll();
});

function populateDropdowns() {
    // Customers (Init custom dropdown)
    document.getElementById('hdr-customer').value = '';
    
    // Warehouses (Header)
    const whSel = document.getElementById('hdr-warehouse');
    WAREHOUSES.forEach(w => {
        const o = document.createElement('option');
        o.value = w.id; o.textContent = w.name;
        whSel.appendChild(o);
    });
    
    // Init Payment Account
    togglePaymentAccount();
}

function togglePaymentAccount() {
    const method = document.getElementById('hdr-payment-method').value;
    const group = document.getElementById('payment-account-group');
    const select = document.getElementById('hdr-payment-account');
    const dueDateGroup = document.getElementById('hdr-due-date-group');
    
    if (!group || !select) return;

    if (method === 'credit') {
        group.style.display = 'none';
        if (dueDateGroup) dueDateGroup.style.display = 'block';
        return;
    }
    
    if (dueDateGroup) dueDateGroup.style.display = 'none';
    group.style.display = 'flex';
    select.innerHTML = '';
    
    let banks = [];
    try {
        banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    } catch(e) {}
    
    const isBankMethod = (method === 'bank' || method === 'cheque');
    
    banks.forEach(b => {
        if ((isBankMethod && b.type === 'bank') || (!isBankMethod && b.type === 'safe')) {
            const o = document.createElement('option');
            o.value = b.id; 
            o.textContent = b.name;
            select.appendChild(o);
        }
    });
    
    if (select.options.length === 0) {
        const o = document.createElement('option');
        o.value = ''; o.textContent = isBankMethod ? '-- لا توجد حسابات بنكية --' : '-- لا توجد خزن --';
        select.appendChild(o);
    }
}

function initHeader() {
    // Invoice number — peek only (no increment)
    const invNo = peekNextInvoiceNumber(currentSaleType);
    document.getElementById('inv-number').textContent = invNo;
    document.getElementById('hdr-inv-no').value = invNo;

    // Date
    document.getElementById('hdr-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('hdr-ref-doc').value = 'RES-' + Date.now().toString(36).toUpperCase() + Math.random().toString(36).substr(2, 4).toUpperCase();
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
    const cust = CUSTOMERS.find(c => c.id === id);
    const cur = document.getElementById('hdr-currency').value;

    if (!cust || id === '') {
        ['fin-ledger','fin-checks','fin-total-debt','fin-credit-limit','fin-remaining'].forEach(el =>
            document.getElementById(el).textContent = '0'
        );
        document.getElementById('fin-status-msg').innerHTML =
            '<span class="status-dot neutral"></span> لم يتم اختيار عميل';
        return;
    }

    const totalDebt = cust.ledger + cust.checks;
    const remaining = cust.creditLimit - totalDebt;

    document.getElementById('fin-ledger').textContent = fmtNum(cust.ledger) + ' ' + cur;
    document.getElementById('fin-checks').textContent = fmtNum(cust.checks) + ' ' + cur;
    document.getElementById('fin-total-debt').textContent = fmtNum(totalDebt) + ' ' + cur;
    document.getElementById('fin-credit-limit').textContent = fmtNum(cust.creditLimit) + ' ' + cur;
    document.getElementById('fin-remaining').textContent = fmtNum(remaining) + ' ' + cur;

    // Status message
    const statusEl = document.getElementById('fin-status-msg');
    
    if (cust.status === 'blacklisted') {
        statusEl.innerHTML = `<span class="status-dot danger"></span> ⛔ تنبيه حرج: هذا العميل في القائمة السوداء!`;
        showToast('تحذير: هذا العميل في القائمة السوداء. تأكد من الإدارة قبل إتمام أي فاتورة آجلة له.', 'error', 5000);
    } else if (remaining > 20000) {
        statusEl.innerHTML = `<span class="status-dot good"></span> الائتمان جيد — متاح ${fmtNum(remaining)} ${cur}`;
    } else if (remaining > 0) {
        statusEl.innerHTML = `<span class="status-dot warn"></span> ⚠️ الائتمان منخفض — متاح ${fmtNum(remaining)} ${cur} فقط`;
    } else {
        statusEl.innerHTML = `<span class="status-dot danger"></span> 🚫 تم تجاوز حد الائتمان!`;
    }
}

function addNewCustomer() {
    const name = document.getElementById('new-cust-name').value.trim();
    const phone = document.getElementById('new-cust-phone').value.trim();
    const credit = parseNum(document.getElementById('new-cust-credit').value);
    if (!name) { showToast('❌ يرجى إدخال اسم العميل', 'error'); return; }

    const newId = 'c-' + String(Date.now()).slice(-6);
    const newCust = { id:newId, name, ledger:0, checks:0, creditLimit:credit };
    CUSTOMERS.push(newCust);

    selectCustomer(newId, name);

    closeModal('add-customer-modal');
    document.getElementById('new-cust-name').value = '';
    document.getElementById('new-cust-phone').value = '';
    document.getElementById('new-cust-credit').value = '';
    showToast(`✅ تم إضافة العميل: ${name}`);
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
        data = allSales.find(inv => inv.invoiceNo === invNo || inv.id === invNo);
    }
    
    if (!data) return false;
    
    try {
        // If loading a confirmed invoice, enter Edit Mode
        if (data.status === 'confirmed' || localStorage.getItem('erp_inv_' + invNo)) {
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
            const c = CUSTOMERS.find(x => String(x.id) === String(custId));
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
        return true;
    } catch(e) {
        console.error('Error loading invoice', e);
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
            
            let displayText = c.name;
            if (c.phone) displayText += ' <span style="font-size:0.8rem; color:#aaa;">(' + c.phone + ')</span>';
            if (c.email) displayText += ' <span style="font-size:0.8rem; color:#888;">[' + c.email + ']</span>';
            
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
        if (document.activeElement && document.activeElement.id === 'hdr-customer-search') {
            return;
        }

        const listEl = document.getElementById('customer-dropdown-list');
        if(listEl) listEl.classList.remove('show');
        
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
    }, 250);
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

function onCustomerChange() {
    const id = document.getElementById('hdr-customer').value;
    const cust = CUSTOMERS.find(c => c.id === id);
    const cur = document.getElementById('hdr-currency').value;

    if (!cust || id === '') {
        ['fin-ledger','fin-checks','fin-total-debt','fin-credit-limit','fin-remaining'].forEach(el =>
            document.getElementById(el).textContent = '0'
        );
        document.getElementById('fin-status-msg').innerHTML =
            '<span class="status-dot neutral"></span> لم يتم اختيار عميل';
        return;
    }

    const totalDebt = cust.ledger + cust.checks;
    const remaining = cust.creditLimit - totalDebt;

    document.getElementById('fin-ledger').textContent = fmtNum(cust.ledger) + ' ' + cur;
    document.getElementById('fin-checks').textContent = fmtNum(cust.checks) + ' ' + cur;
    document.getElementById('fin-total-debt').textContent = fmtNum(totalDebt) + ' ' + cur;
    document.getElementById('fin-credit-limit').textContent = fmtNum(cust.creditLimit) + ' ' + cur;
    document.getElementById('fin-remaining').textContent = fmtNum(remaining) + ' ' + cur;

    // Status message
    const statusEl = document.getElementById('fin-status-msg');
    
    if (cust.status === 'blacklisted') {
        statusEl.innerHTML = `<span class="status-dot danger"></span> ⛔ تنبيه حرج: هذا العميل في القائمة السوداء!`;
        showToast('تحذير: هذا العميل في القائمة السوداء. تأكد من الإدارة قبل إتمام أي فاتورة آجلة له.', 'error', 5000);
    } else if (remaining > 20000) {
        statusEl.innerHTML = `<span class="status-dot good"></span> الائتمان جيد — متاح ${fmtNum(remaining)} ${cur}`;
    } else if (remaining > 0) {
        statusEl.innerHTML = `<span class="status-dot warn"></span> ⚠️ الائتمان منخفض — متاح ${fmtNum(remaining)} ${cur} فقط`;
    } else {
        statusEl.innerHTML = `<span class="status-dot danger"></span> 🚫 تم تجاوز حد الائتمان!`;
    }
}

function addNewCustomer() {
    const name = document.getElementById('new-cust-name').value.trim();
    const phone = document.getElementById('new-cust-phone').value.trim();
    const credit = parseNum(document.getElementById('new-cust-credit').value);
    if (!name) { showToast('❌ يرجى إدخال اسم العميل', 'error'); return; }

    const newId = 'c-' + String(Date.now()).slice(-6);
    const newCust = { id:newId, name, ledger:0, checks:0, creditLimit:credit };
    CUSTOMERS.push(newCust);

    selectCustomer(newId, name);

    closeModal('add-customer-modal');
    document.getElementById('new-cust-name').value = '';
    document.getElementById('new-cust-phone').value = '';
    document.getElementById('new-cust-credit').value = '';
    showToast(`✅ تم إضافة العميل: ${name}`);
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
        data = allSales.find(inv => inv.invoiceNo === invNo || inv.id === invNo);
    }
    
    if (!data) return false;
    
    try {
        // If loading a confirmed invoice, enter Edit Mode
        if (data.status === 'confirmed' || localStorage.getItem('erp_inv_' + invNo)) {
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
            const c = CUSTOMERS.find(x => String(x.id) === String(custId));
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
        return true;
    } catch(e) {
        console.error('Error loading invoice', e);
        return false;
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
        matches = CUSTOMERS.filter(c => {
            const searchStr = window.normalizeArabic ? window.normalizeArabic(c.name + ' ' + (c.phone || '') + ' ' + (c.email || '')) : (c.name + ' ' + (c.phone || '') + ' ' + (c.email || '')).toLowerCase();
            const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
            return normalizedTerm.split(' ').every(word => searchStr.includes(word));
        });
    }

    if (matches.length === 0) {
        listEl.innerHTML = '<div class="custom-dropdown-empty">لا يوجد عميل بهذا الاسم</div>';
        return;
    }

    matches.forEach(c => {
        const item = document.createElement('div');
        item.className = 'custom-dropdown-item';
        item.textContent = c.name;
        item.onmousedown = () => selectCustomer(c.id, c.name); // mousedown to trigger before blur
        listEl.appendChild(item);
    });
}

function showCustomerDropdown() {
    renderCustomerDropdown(document.getElementById('hdr-customer-search').value);
    document.getElementById('customer-dropdown-list').classList.add('show');
}

function hideCustomerDropdownDelayed() {
    setTimeout(() => {
        const listEl = document.getElementById('customer-dropdown-list');
        if(listEl) listEl.classList.remove('show');
        
        const idVal = document.getElementById('hdr-customer').value;
        const searchVal = document.getElementById('hdr-customer-search').value.trim();
        if (idVal) {
            const cust = CUSTOMERS.find(c => c.id === idVal);
            if (cust && cust.name !== searchVal) {
                document.getElementById('hdr-customer-search').value = cust.name;
            }
        } else {
            // Allow walk-in customers or custom names without disappearing
            // document.getElementById('hdr-customer-search').value = '';
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
function renderItemDropdown(rowId, isMobile = false, filter = '') {
    const prefix = isMobile ? 'm-' : '';
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    
    let matches = ITEMS;
    if (term) {
        matches = ITEMS.filter(i => {
            const searchStr = window.normalizeArabic ? window.normalizeArabic(i.name + ' ' + (i.barcode || '') + ' ' + (i.code || '')) : (i.name + ' ' + (i.barcode || '') + ' ' + (i.code || '')).toLowerCase();
            const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
            return normalizedTerm.split(' ').every(word => searchStr.includes(word));
        });
    }

    if (matches.length === 0) {
        listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة.<br><small style="color:#888;">(حاول استخدام اسم آخر)</small></div>';
        return;
    }

    matches.forEach(i => {
        const div = document.createElement('div');
        div.className = 'custom-dropdown-item';
        div.textContent = isMobile ? `${i.name} — ${i.unit}` : i.name;
        div.onmousedown = () => {
            updateRow(rowId, 'itemId', i.id);
            renderGrid();
        };
        listEl.appendChild(div);
    });
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
                    searchEl.value = isMobile ? `${item.name} — ${item.unit}` : item.name;
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
function renderQuickItemDropdown(filter = '') {
    const listEl = document.getElementById('quick-item-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    
    let matches = ITEMS;
    if (term) {
        matches = ITEMS.filter(i => {
            const searchStr = window.normalizeArabic ? window.normalizeArabic(i.name + ' ' + (i.barcode || '') + ' ' + (i.code || '')) : (i.name + ' ' + (i.barcode || '') + ' ' + (i.code || '')).toLowerCase();
            const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
            return normalizedTerm.split(' ').every(word => searchStr.includes(word));
        });
    }

    if (matches.length === 0) {
        listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة.<br><small style="color:#888;">(حاول استخدام اسم آخر)</small></div>';
        return;
    }

    matches.forEach(i => {
        const div = document.createElement('div');
        div.className = 'custom-dropdown-item';
        div.textContent = `${i.name} — ${i.unit}`;
        div.onmousedown = () => {
            selectQuickItem(i.id);
        };
        listEl.appendChild(div);
    });
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
function addRow() {
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
    showToast('📦 تم إضافة صنف جديد', 'success', 1500);
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
            row.price = row.fcPrice * exRate;
        }
        if (field === 'price') {
            const exRate = parseNum(document.getElementById('hdr-exchange-rate').value) || 1;
            row.fcPrice = row.price / exRate;
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
    
    // Check stock warning
    if (field === 'quantity' || field === 'customBaseQty' || field === 'itemId' || field === 'selectedUnit') {
        const calcs = getRowCalcs(row);
        if (calcs.availQty !== '—' && calcs.availQty !== undefined && calcs.baseQty > calcs.availQty) {
            showToast(`⚠️ تحذير: الكمية المطلوبة تتجاوز المخزون المتاح للصنف`, 'warning', 3500);
        }
    }

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
    const shilaQty = row.quantity ? (row.quantity / shilaFactor) : 0;
    const shilaRem = row.quantity ? (row.quantity % shilaFactor) : 0;
    const whId = getResolvedWarehouse(row);
    const actualQty = item && item.actualStock && item.actualStock[whId] !== undefined ? item.actualStock[whId] : 0;
    const reservedQty = item && item.reservedStock && item.reservedStock[whId] !== undefined ? item.reservedStock[whId] : 0;
    const availQty = actualQty - reservedQty;
    
    return { total, baseQty, bundles, shilaQty, shilaRem, actualQty, reservedQty, availQty, unit: selectedUnitName, unitFactor: selectedUnitFactor };
}

function updateRowCalcs(id) {
    const row = rows.find(r => r.id === id);
    if (!row) return;
    const calcs = getRowCalcs(row);

    // Desktop
    const totalEl = document.querySelector(`[data-total="${id}"]`);
    const fcTotalEl = document.querySelector(`[data-fc-total="${id}"]`);
    const baseEl = document.querySelector(`[data-base-input="${id}"]`);
    const availEl = document.querySelector(`[data-avail="${id}"]`);
    const bundleEl = document.querySelector(`[data-bundle="${id}"]`);
    const shilaEl = document.querySelector(`[data-shila="${id}"]`);
    
    const priceEl = document.querySelector(`[data-price-input="${id}"]`);
    const qtyEl = document.querySelector(`[data-qty-input="${id}"]`);

    if (totalEl) totalEl.textContent = fmtNum(calcs.total);
    if (fcTotalEl) {
        const exRate = parseNum(document.getElementById('hdr-exchange-rate').value) || 1;
        fcTotalEl.textContent = fmtNum(calcs.total / exRate);
    }
    if (baseEl && document.activeElement !== baseEl) baseEl.value = fmtNum(calcs.baseQty);
    if (bundleEl) bundleEl.textContent = fmtNum(calcs.bundles);
    if (shilaEl) shilaEl.textContent = fmtNum(calcs.shilaQty);
    
    if (priceEl && document.activeElement !== priceEl) priceEl.value = fmtNum(row.price);
    if (qtyEl && document.activeElement !== qtyEl) qtyEl.value = Number.isInteger(row.quantity) ? row.quantity : parseFloat((row.quantity || 0).toFixed(4));

    const actualEl = document.querySelector(`[data-actual="${id}"]`);
    const reservedEl = document.querySelector(`[data-reserved="${id}"]`);

    if (actualEl) actualEl.textContent = calcs.actualQty === '—' ? '—' : fmtNum(calcs.actualQty);
    if (reservedEl) reservedEl.textContent = calcs.reservedQty === '—' ? '—' : fmtNum(calcs.reservedQty);

    if (availEl) {
        availEl.textContent = calcs.availQty === '—' ? '—' : fmtNum(calcs.availQty);
        availEl.className = 'g-readonly ' + (calcs.availQty !== '—' && calcs.availQty !== undefined && calcs.availQty < calcs.baseQty ? 'warn-val' : 'avail-val');
    }

    // Mobile
    const mTotal = document.querySelector(`[data-m-total="${id}"]`);
    if (mTotal) mTotal.textContent = fmtNum(calcs.total) + ' ' + getCurrency();
    const mShilaEl = document.querySelector(`[data-m-shila="${id}"]`);
        if (mShilaEl) mShilaEl.textContent = fmtNum(calcs.shilaQty);
    
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
        empty.style.display = 'block';
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

        // ── Desktop Row ──
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><div class="row-num">${idx + 1}</div></td>
            <td style="font-size: 0.75rem; font-weight: bold; color: var(--text-muted); text-align: center;">${item ? item.id : ''}</td>
            <td style="position:relative;">
                <input type="text" class="g-input" id="item-search-${row.id}" placeholder="ابحث أو اختر الصنف..." 
                    value="${item ? item.name : ''}" autocomplete="new-password"
                    onfocus="this.select(); showItemDropdown(${row.id})" 
                    onblur="hideItemDropdownDelayed(${row.id})" 
                    oninput="filterItemDropdown(${row.id})">
                <div class="custom-dropdown-list" id="item-dropdown-list-${row.id}" style="text-align:right; min-width: 220px; white-space: normal;">
                    <!-- populated by JS -->
                </div>
            </td>
            <td>
                <select class="g-select" style="min-width: 80px;" onchange="updateRow(${row.id},'selectedUnit',this.value); renderGrid();" tabindex="-1">
                    ${itemUnitsHtml}
                </select>
            </td>
            <td><input type="text" inputmode="decimal" class="g-input" value="${row.customFactor !== undefined ? row.customFactor : calcs.unitFactor}" placeholder="1"
                onfocus="this.select()" oninput="updateRow(${row.id},'customFactor',this.value)"
                onblur="this.value=parseNum(this.value)||1" tabindex="-1"></td>
            <td><input type="text" inputmode="decimal" class="g-input" data-qty-input="${row.id}" value="${Number.isInteger(row.quantity) ? row.quantity : parseFloat((row.quantity || 0).toFixed(4)) || ''}" placeholder="0"
                onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)"
                onblur="this.value=parseNum(this.value)||''"></td>
            <td>
                <input type="text" inputmode="decimal" class="g-input" value="${fmtNum(row.fcPrice !== undefined ? row.fcPrice : (row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1)))}"
                    onfocus="this.select()" oninput="updateRow(${row.id},'fcPrice',this.value)"
                    onblur="this.value=fmtNum(parseNum(this.value))" style="color:#38bdf8; font-weight:bold; min-width: 80px;">
            </td>
            <td>
                <div style="display:flex; align-items:center; gap:4px; min-width: 100px;">
                    <input type="text" inputmode="decimal" class="g-input" data-price-input="${row.id}" value="${fmtNum(row.price)}"
                        onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                        onblur="this.value=fmtNum(parseNum(this.value))" style="flex:1;">
                    <button class="btn-icon" onclick="showPriceHistory(${row.id}, 'sales')" title="سجل أسعار الصنف" style="color:#38bdf8; background:rgba(56,189,248,0.1); border-radius:4px; padding:4px; border:1px solid rgba(56,189,248,0.2); cursor:pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </button>
                </div>
            </td>
            <td><span class="g-readonly" style="color:#a855f7; font-weight:bold;" data-actual="${row.id}">${calcs.actualQty === '—' ? '—' : fmtNum(calcs.actualQty)}</span></td>
            <td><span class="g-readonly" style="color:#f59e0b; font-weight:bold; font-size: 0.8rem;" data-reserved="${row.id}">${calcs.reservedQty === '—' ? '—' : fmtNum(calcs.reservedQty)}</span></td>
            <td><span class="g-readonly ${calcs.availQty !== '—' && calcs.availQty !== undefined && calcs.availQty < calcs.baseQty ? 'warn-val' : 'avail-val'}" data-avail="${row.id}">${calcs.availQty === '—' ? '—' : fmtNum(calcs.availQty)}</span></td>
            <td>
                <select class="g-select" style="min-width: 90px;" onchange="updateRow(${row.id},'costCenterId',this.value)">
                    <option value="">لا يوجد</option>
                    <option value="cc-001" ${row.costCenterId === 'cc-001' ? 'selected' : ''}>الفرع الرئيسي</option>
                    <option value="cc-002" ${row.costCenterId === 'cc-002' ? 'selected' : ''}>فرع بحري</option>
                </select>
            </td>
            <td>
                <select class="g-select" onchange="updateRow(${row.id},'warehouseId',this.value)" tabindex="-1">
                    <option value="">نفس الفاتورة ↩</option>
                    ${WAREHOUSES.map(w => `<option value="${w.id}" ${w.id === row.warehouseId ? 'selected' : ''}>${w.name}</option>`).join('')}
                </select>
            </td>
            <td><span class="g-readonly total-val" style="color:#38bdf8;" data-fc-total="${row.id}">${fmtNum(calcs.total / (parseNum(document.getElementById('hdr-exchange-rate').value)||1))}</span></td>
            <td><span class="g-readonly total-val" data-total="${row.id}">${fmtNum(calcs.total)}</span></td>
            <td><button class="del-btn" onclick="removeRow(${row.id})" title="حذف السطر">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button></td>
        `;
        tbody.appendChild(tr);

        // ── Mobile Card ──
        const card = document.createElement('div');
        card.className = 'm-card';
        card.innerHTML = `
            <div class="m-card-num">${idx + 1}</div>
            <button class="m-card-del" onclick="removeRow(${row.id})" title="حذف">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
            <div class="ctrl-group m-card-full" style="padding-top:4px; position:relative;">
                <label class="label">الصنف</label>
                <input type="text" class="ctrl-input" id="m-item-search-${row.id}" placeholder="ابحث أو اختر الصنف..." 
                    value="${item ? item.name + ' — ' + item.unit : ''}" autocomplete="new-password"
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
                    <select class="ctrl-input" onchange="updateRow(${row.id},'selectedUnit',this.value); renderGrid();">
                        ${itemUnitsHtml}
                    </select>
                </div>
                <div class="ctrl-group">
                    <label class="label">التعادل</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" value="${row.customFactor !== undefined ? row.customFactor : calcs.unitFactor}" placeholder="1"
                        onfocus="this.select()" oninput="updateRow(${row.id},'customFactor',this.value)"
                        onblur="this.value=parseNum(this.value)||1">
                </div>
                <div class="ctrl-group">
                    <label class="label">الكمية</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" data-qty-input="${row.id}" value="${Number.isInteger(row.quantity) ? row.quantity : parseFloat((row.quantity || 0).toFixed(4)) || ''}" placeholder="0"
                        onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)">
                </div>
                <div class="ctrl-group">
                    <label class="label">السعر (محلي / أجنبي) <button class="btn-icon" onclick="showPriceHistory(${row.id}, 'sales')" title="سجل أسعار الصنف للعميل" style="color:#38bdf8; background:none; border:none; cursor:pointer; padding:0; margin-right:4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></button></label>
                    <div style="display:flex; gap:4px;">
                        <input type="text" inputmode="decimal" class="ctrl-input" value="${fmtNum(row.price)}" placeholder="0"
                            onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                            onblur="this.value=fmtNum(parseNum(this.value))" style="flex:1;">
                        <input type="text" inputmode="decimal" class="ctrl-input" value="${fmtNum(row.fcPrice !== undefined ? row.fcPrice : (row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1)))}" placeholder="أجنبي"
                            onfocus="this.select()" oninput="updateRow(${row.id},'fcPrice',this.value)"
                            onblur="this.value=fmtNum(parseNum(this.value))" style="flex:1; color:#10b981;">
                    </div>
                </div>
                <div class="ctrl-group">
                    <label class="label">الكمية بالعدد</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" data-base-input="${row.id}" value="${fmtNum(calcs.baseQty)}" placeholder="0"
                        onfocus="this.select()" oninput="updateRow(${row.id},'customBaseQty',this.value)"
                        onblur="this.value=fmtNum(parseNum(this.value))" style="color:var(--primary-accent); font-weight:700;">
                </div>
                <div class="ctrl-group">
                    <label class="label">المخزن</label>
                    <select class="ctrl-input" onchange="updateRow(${row.id},'warehouseId',this.value)">
                        <option value="">الرئيسي ↩</option>
                        ${WAREHOUSES.map(w => `<option value="${w.id}" ${w.id === row.warehouseId ? 'selected' : ''}>${w.name}</option>`).join('')}
                    </select>
                </div>
                <div class="ctrl-group">
                    <label class="label">المتاح</label>
                    <div class="ctrl-input readonly" style="text-align:center;color:#38bdf8;">${calcs.availQty === '—' ? '—' : fmtNum(calcs.availQty)}</div>
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
    document.getElementById('t-grand').textContent = fmtNum(grand) + ' ' + cur;

    // Footer
    document.getElementById('footer-grand').textContent = fmtNum(grand) + ' ' + cur;
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
        warehouse: document.getElementById('hdr-warehouse').value,
        description: document.getElementById('hdr-description').value,
        items: rows,
        notes: document.getElementById('inv-notes').value,
        status: 'draft'
    };
    localStorage.setItem('erp_draft_' + data.invoiceNo, JSON.stringify(data));
    showToast('📑 تم حفظ المسودة بنجاح', 'success', 2500);
}

function submitReservedInvoice(ignoreWarnings = false) {
    if (currentViewedInvoiceNo) {
        // Edit mode check
        const invDate = document.getElementById('hdr-date').value;
        if (typeof window.canEditRecord === 'function' && !window.canEditRecord(invDate, 'sales')) {
            return; // blocked by policy
        }
    }

    const custId = document.getElementById('hdr-customer').value;
    if (!custId) { showToast('❌ يرجى تحديد العميل لتأكيد الفاتورة', 'error'); return; }

    if (rows.length === 0) { showToast('❌ لا يمكن تأكيد فاتورة فارغة', 'error'); return; }

    const hasEmptyItems = rows.some(r => !r.itemId);
    if (hasEmptyItems) { showToast('⚠️ يرجى اختيار الصنف لكل سطر', 'warning'); return; }

    // GPS & Security Check
    let userSecLevel = 1;
    try {
        const u = JSON.parse(localStorage.getItem('user'));
        if (u && u.securityLevel) userSecLevel = parseInt(u.securityLevel);
    } catch(e) {}

    const gpsLat = document.getElementById('hdr-gps-lat').value;
    if (!gpsLat && userSecLevel < 5) {
        showToast('📍 يتطلب حفظ الفاتورة تفعيل ختم الموقع (GPS) من أعلى الشاشة!', 'error');
        return;
    }

    const hasZeroQty = rows.some(r => r.quantity <= 0);
    if (hasZeroQty) { showToast('⚠️ الكمية يجب أن تكون أكبر من صفر', 'warning'); return; }

    const paymentMethod = document.getElementById('hdr-payment-method').value;
    const cust = CUSTOMERS.find(c => c.id === custId);

    // --- Hard Block: 2 Unpaid Credit Invoices ---
    if (paymentMethod === 'credit' && cust && cust.id !== 'walk-in') {
        const debt = cust.ledger || 0;
        if (debt > 0) {
            let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
            const creditInvs = allSales.filter(inv => (inv.customer === cust.id || inv.customerId === cust.id) && inv.paymentMethod === 'credit');
            creditInvs.reverse(); // Newest first

            let remainingDebt = debt;
            let unpaidCount = 0;

            for (const inv of creditInvs) {
                let invTotal = inv.grandTotal;
                if (invTotal === undefined) {
                    invTotal = inv.items ? inv.items.reduce((sum, item) => sum + ((item.quantity||0) * (item.price||0)), 0) : 0;
                }
                remainingDebt -= invTotal;
                unpaidCount++;
                if (remainingDebt <= 0) break;
            }

            if (unpaidCount >= 2) {
                // Hard block!
                const existing = document.getElementById('confirm-warning-overlay');
                if (existing) existing.remove();
                
                alert(`❌ حظر ائتماني صارم!\nالعميل (${cust.name}) لديه ${unpaidCount} فواتير آجلة سابقة لم يقم بسدادها بالكامل.\nيجب سداد الديون القديمة أولاً لإنشاء فاتورة آجلة جديدة.`);
                return;
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
        const custId = document.getElementById('hdr-customer').value;
        const cust = CUSTOMERS.find(c => c.id === custId);
        if (cust && cust.id !== 'walk-in' && cust.creditLimit > 0) {
            const totalDebt = cust.ledger + cust.checks;
            const remaining = cust.creditLimit - totalDebt;
            const grand = parseNum(document.getElementById('t-grand').textContent);
            if (grand > remaining) {
                const diff = grand - remaining;
                const cur = getCurrency();
                warnings.push(`الفاتورة تتجاوز حد الائتمان. (المبلغ سيتجاوز الحد بمقدار <strong>${fmtNum(diff)} ${cur}</strong>)`);
            }
        }

        if (warnings.length > 0) {
            showConfirmWarningModal(warnings);
            return;
        }
    }

    
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
    let dueDateValue = null;
    if (document.getElementById('hdr-payment-method').value === 'credit') {
        dueDateValue = document.getElementById('hdr-due-date').value;
    }

    const data = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        invoiceNo: committedNo,
        refDoc: document.getElementById('hdr-ref-doc').value,
        date: document.getElementById('hdr-date').value,
        dueDate: dueDateValue,
        customer: custId,
        gpsLocation: {
            lat: document.getElementById('hdr-gps-lat').value,
            lng: document.getElementById('hdr-gps-lng').value
        },
        saleType: 'reserved', // Forces this to be a reservation
        currency: cur,
        exchangeRate: document.getElementById('hdr-exchange-rate').value,
        paymentMethod: paymentMethod,
        warehouse: document.getElementById('hdr-warehouse').value,
        costCenter: document.getElementById('hdr-cost-center') ? document.getElementById('hdr-cost-center').value : '',
        description: document.getElementById('hdr-description').value,
        items: rows.map(r => ({...r})),
        notes: document.getElementById('inv-notes').value,
        subTotal: parseNum(document.getElementById('t-subtotal') ? document.getElementById('t-subtotal').textContent : 0),
        discount: parseNum(document.getElementById('t-discount').value || 0),
        vatAmount: parseNum(document.getElementById('t-vat') ? document.getElementById('t-vat').textContent : 0),
        grandTotal: parseNum(grandTotal),
        status: 'reserved',
        securityHash: generateHash(committedNo + document.getElementById('hdr-ref-doc').value + grandTotal)
    };

    // Update Bank/Safe balance
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
            banks[bIdx].currentBalance = currentBalance + grandAmount;
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            data.paymentAccountId = paymentAccountId;
            usedAccountName = banks[bIdx].name;
        }
    }

    localStorage.setItem('erp_inv_' + committedNo, JSON.stringify(data));
    if (window.logSystemAction) window.logSystemAction(currentViewedInvoiceNo ? 'تعديل فاتورة' : 'إنشاء فاتورة', 'SALES', 'رقم الفاتورة: ' + committedNo);

    // Append or Update global sales invoices array for inventory calculation
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    if (currentViewedInvoiceNo) {
        const idx = allSales.findIndex(inv => inv.invoiceNo === currentViewedInvoiceNo);
        if (idx !== -1) allSales[idx] = data;
        else allSales.push(data);
    } else {
        allSales.push(data);
    }
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));

    // Update Customer Balance (Debt)
    if (paymentMethod === 'credit' && custId && custId !== 'walk-in') {
        let customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
        const cIdx = customers.findIndex(c => c.id === custId);
        if (cIdx > -1) {
            let currentBalance = parseFloat(customers[cIdx].currentBalance);
            if (isNaN(currentBalance)) currentBalance = 0;
            customers[cIdx].currentBalance = currentBalance + grandAmount;
            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
        }
    }

    // Add to reserved stock in local inventory cache and live objects
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
            itemInv.reservedQuantity = (parseFloat(itemInv.reservedQuantity) || 0) + calcs.baseQty;
        } else {
            currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: 0, reservedQuantity: calcs.baseQty, minQty: 10 });
        }
        
        // Update live ITEMS array
        if (item.reservedStock && item.reservedStock[whId] !== undefined) {
            item.reservedStock[whId] += calcs.baseQty;
        } else if (item.reservedStock) {
            item.reservedStock[whId] = calcs.baseQty;
        }
    }
    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

    // RECORD AUDIT TRAIL
    if (typeof window.app === 'object' && typeof window.app.logAudit === 'function') {
        window.app.logAudit('RESERVED_SALE_CREATED', `تم إنشاء حجز مخزني رقم ${committedNo} بقيمة ${grandTotal} ${cur}`, 'sales');
    } else {
        // Fallback local audit
        let logs = JSON.parse(localStorage.getItem('erp_audit_logs')) || [];
        logs.push({
            action: 'RESERVED_SALE_CREATED',
            details: `تم إنشاء حجز مخزني رقم ${committedNo} بقيمة ${grandTotal} ${cur}`,
            module: 'sales',
            timestamp: new Date().toISOString(),
            user: 'System'
        });
        localStorage.setItem('erp_audit_logs', JSON.stringify(logs));
    }

    // Show professional success modal (we can pass the warnings array so it still shows them if desired)
    // To grab the actual warnings to display in success modal, we need to generate them again if ignoreWarnings is true.
    let finalWarnings = [];
    if (ignoreWarnings) {
        const mainWh = document.getElementById('hdr-warehouse').value;
        for (const row of rows) {
            const item = ITEMS.find(i => String(i.id) === String(row.itemId));
            if (!item) continue;
            const calcs = getRowCalcs(row);
            if (calcs.availQty !== '—' && calcs.availQty !== undefined && calcs.baseQty > calcs.availQty) {
                const diff = calcs.baseQty - calcs.availQty;
                finalWarnings.push(`الصنف "<strong>${item.name}</strong>" تجاوز المخزون بمقدار <strong>${fmtNum(diff)} ${item.baseUnit || 'وحدة أساسية'}</strong>`);
            }
        }
        const custId = document.getElementById('hdr-customer').value;
        const cust = CUSTOMERS.find(c => c.id === custId);
        if (cust && cust.id !== 'walk-in' && cust.creditLimit > 0) {
            const totalDebt = cust.ledger + cust.checks;
            const remaining = cust.creditLimit - totalDebt;
            const grand = parseNum(document.getElementById('t-grand').textContent);
            if (grand > remaining) {
                const diff = grand - remaining;
                const cur = getCurrency();
                finalWarnings.push(`الفاتورة تجاوزت حد الائتمان بمقدار <strong>${fmtNum(diff)} ${cur}</strong>`);
            }
        }
    }

    if (usedAccountName) {
        showToast(`تمت إضافة ${fmtNum(grandAmount)} ${cur} إلى رصيد: ${usedAccountName}`, 'success', 5000);
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
    submitReservedInvoice(true);
}

// ════════════════════════════════════════
//  SECURITY: DIGITAL HASH
// ════════════════════════════════════════
function generateHash(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return Math.abs(hash).toString(16).toUpperCase() + '-' + Date.now().toString(36).toUpperCase();
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
    const grandAmount = parseFloat(oldInv.grandTotal) || 0;

    if (oldInv.paymentMethod === 'credit' && oldInv.customer && oldInv.customer !== 'walk-in') {
        let customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
        const cIdx = customers.findIndex(c => c.id === oldInv.customer);
        if (cIdx > -1) {
            let currentBalance = parseFloat(customers[cIdx].currentBalance) || 0;
            customers[cIdx].currentBalance = currentBalance - grandAmount;
            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
        }
    }

    if (oldInv.paymentMethod !== 'credit' && oldInv.paymentAccountId) {
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
        if (bIdx > -1) {
            let currentBalance = parseFloat(banks[bIdx].currentBalance) || 0;
            banks[bIdx].currentBalance = currentBalance - grandAmount;
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
            
            let itemInv = currentInvData.find(inv => inv.itemId === row.itemId && inv.warehouse === whName);
            if (itemInv) {
                itemInv.reservedQuantity = (parseFloat(itemInv.reservedQuantity) || 0) - baseQty;
                if (itemInv.reservedQuantity < 0) itemInv.reservedQuantity = 0;
            }
            
            if (item.reservedStock && item.reservedStock[whId] !== undefined) {
                item.reservedStock[whId] -= baseQty;
                if (item.reservedStock[whId] < 0) item.reservedStock[whId] = 0;
            }
        }
    }
    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));

    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    allSales = allSales.filter(inv => inv.invoiceNo !== currentInvNo);
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));

    localStorage.removeItem('erp_inv_' + currentInvNo);
    localStorage.removeItem('erp_draft_' + currentInvNo);

    onSuccessNewInvoice(); 
    showToast('🗑️ تم حذف الحجز وإلغاء جميع التأثيرات بنجاح', 'success');
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
                <button class="btn btn-ghost success-btn" onclick="onSuccessNewInvoice()">عملية جديدة</button>
                <button class="btn btn-success success-btn" onclick="onSuccessDone()">قائمة المبيعات</button>
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

function generateHash(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    return Math.abs(hash).toString(16).toUpperCase() + Date.now().toString(16).toUpperCase();
}

function submitReservedInvoice(ignoreWarnings = false) {
    // Wrapper to act as the primary submit function for the reserved button
    submitInvoice(ignoreWarnings);
}

function printReservation() {
    if (rows.length === 0) { showToast('لا يوجد شيء لطباعته', 'error'); return; }
    showToast('جاري تحضير إشعار الحجز...', 'success');
    setTimeout(() => {
        window.print();
    }, 500);
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

