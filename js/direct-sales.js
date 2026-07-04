/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   sales-invoice.js â€” POS Engine v6.0
   Real-time Reactive Calculations Â· Sequential Invoices
   â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */

// Global logout function (used by sidebar)
// Global logout is handled by app.js

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  MOCK DATA (Replace with API calls)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

let WAREHOUSES = JSON.parse(localStorage.getItem('erp_warehouses_v1'));
if (!WAREHOUSES || WAREHOUSES.length === 0) {
    WAREHOUSES = [{ id:'w-main', name:'Ø§Ù„Ù…Ø®Ø²Ù† Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ (Ø§Ù„Ø³Ø¬Ø§Ù†Ø©)' }];
}

const COST_CENTERS = [
    { id:'cc-001', name:'Ø§Ù„ÙØ±Ø¹ Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ' },
    { id:'cc-002', name:'ÙØ±Ø¹ Ø¨Ø­Ø±ÙŠ' },
    { id:'cc-003', name:'ÙØ±Ø¹ Ø£Ù…Ø¯Ø±Ù…Ø§Ù†' },
];

let CUSTOMERS = [
    { id:'walk-in', name:'Ø²Ø¨ÙˆÙ† Ù†Ù‚Ø¯ÙŠ (Walk-in)', ledger:0, checks:0, creditLimit:0, status:'active' },
];

// Load real customer data from master-data
// (Moved to DBReady event)

let UOMS = JSON.parse(localStorage.getItem('erp_uoms_v1')) || [
    {code: 'TON', name: 'Ø·Ù†'},
    {code: 'BAG', name: 'ÙƒÙŠØ³'},
    {code: 'REBAR', name: 'Ø³ÙŠØ®Ø© / Ø¹ÙˆØ¯'},
    {code: 'PCS', name: 'Ø­Ø¨Ø© / Ù‚Ø·Ø¹Ø©'},
    {code: 'GAL', name: 'Ø¬Ø§Ù„ÙˆÙ†'}
];

let ITEMS = [];

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  STATE
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
let rows = [];
let rowIdCounter = 0;
let currentSaleType = 'direct';
let currentViewedInvoiceNo = null;

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  HELPERS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  SEQUENTIAL INVOICE NUMBER
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  INIT
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
document.addEventListener('DBReady', () => {
    // Load real customer data from master-data
    const localCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
    if(localCusts.length > 0) {
        CUSTOMERS = localCusts.map(c => ({
            id: c.id,
            name: c.name,
            ledger: Math.abs(c.currentBalance < 0 ? c.currentBalance : 0),
            checks: 0,
            creditLimit: c.creditLimit || 0,
            status: c.status || 'active'
        }));
        CUSTOMERS.unshift({ id:'walk-in', name:'Ø²Ø¨ÙˆÙ† Ù†Ù‚Ø¯ÙŠ (Walk-in)', ledger:0, checks:0, creditLimit:0, status:'active' });
    }

    // Load Master Data Items
    const rawItems = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
    const invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
    
    if (rawItems.length === 0) {
        alert('ØªÙ†Ø¨ÙŠÙ‡: Ù„Ø§ ØªÙˆØ¬Ø¯ Ø£ØµÙ†Ø§Ù ÙÙŠ Ø§Ù„Ù†Ø¸Ø§Ù…! ÙŠØ±Ø¬Ù‰ Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ø£ØµÙ†Ø§Ù Ù…Ù† Ø´Ø§Ø´Ø© (Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø£Ø³Ø§Ø³ÙŠØ©) Ø£ÙˆÙ„Ø§Ù‹.');
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

        // Clean price â€” support new schema (sellingPrice) and old (salePrice/price)
        let sp = item.sellingPrice || item.salePrice || item.price || 0;
        sp = sp.toString().replace(/,/g, '');
        
        const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'Ù‚Ø·Ø¹Ø©';

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
    // Ø¥Ø¶Ø§ÙØ© ØµÙ ÙØ§Ø±Øº Ø§ÙØªØ±Ø§Ø¶ÙŠ Ø¹Ù†Ø¯ Ø§Ù„ØªØ­Ù…ÙŠÙ„
    addRow(true);
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
    const detailsRow = document.getElementById('payment-details-row');
    const lbl1 = document.getElementById('pay-detail-1-label');
    const lbl2 = document.getElementById('pay-detail-2-label');
    const lbl3 = document.getElementById('pay-detail-3-label');
    
    // Ø¥Ø®ÙØ§Ø¡ Ø£Ùˆ Ø¥Ø¸Ù‡Ø§Ø± Ù…Ù†Ø·Ù‚Ø© Ø§Ù„ØªÙØ§ØµÙŠÙ„
    if (method === 'cheque' || method === 'bank') {
        if (detailsRow) {
            detailsRow.style.display = 'flex';
            detailsRow.style.animation = 'none';
            requestAnimationFrame(() => {
                detailsRow.style.animation = 'fadeSlideIn 0.35s cubic-bezier(0.16,1,0.3,1) both';
            });
        }
        
        if (method === 'cheque') {
            if (lbl1) lbl1.innerText = 'Ø±Ù‚Ù… Ø§Ù„Ø´ÙŠÙƒ';
            if (lbl2) lbl2.innerText = 'Ø§Ø³Ù… Ø§Ù„Ø¨Ù†Ùƒ';
            if (lbl3) lbl3.innerText = 'Ø§Ù„ÙØ±Ø¹';
        } else if (method === 'bank') {
            if (lbl1) lbl1.innerText = 'Ø±Ù‚Ù… Ø§Ù„ØªØ­ÙˆÙŠÙ„';
            if (lbl2) lbl2.innerText = 'Ø§Ù„Ø¨Ù†Ùƒ Ø§Ù„Ù…Ø­ÙˆÙ„ Ø¥Ù„ÙŠÙ‡';
            if (lbl3) lbl3.innerText = 'Ø§Ù„ÙØ±Ø¹ / Ø§Ù„Ù…Ø±Ø¬Ø¹';
        }
    } else {
        if (detailsRow) detailsRow.style.display = 'none';
    }

    // ØªØ­Ø¯ÙŠØ« Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„ØµÙ†Ø¯ÙˆÙ‚/Ø§Ù„Ø®Ø²Ù†Ø© Ø¨Ù†Ø§Ø¡Ù‹ Ø¹Ù„Ù‰ Ø·Ø±ÙŠÙ‚Ø© Ø§Ù„Ø¯ÙØ¹
    // تحديث قائمة الصندوق/الخزنة بناءً على طريقة الدفع
    const select = document.getElementById('hdr-payment-account');
    if (select) {
        if (!window.paymentAccountChoices) {
            window.paymentAccountChoices = new Choices(select, { searchEnabled: true, itemSelectText: '', shouldSort: false });
        }
        
        if (method === 'cash') {
            window.paymentAccountChoices.enable();
            populateAccountSelect(select, 'safe');
        } else if (method === 'bank' || method === 'cheque') {
            window.paymentAccountChoices.enable();
            populateAccountSelect(select, 'bank');
        } else if (method === 'credit') {
            window.paymentAccountChoices.setChoices([{ value: '', label: '-- آجل (على الحساب) --' }], 'value', 'label', true);
            window.paymentAccountChoices.disable();
        }
    }
}

function populateAccountSelect(select, type) {
    let banks = [];
    try { banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
    let choicesArr = [];
    banks.filter(b => b.type === type).forEach(b => {
        choicesArr.push({ value: b.id, label: b.name });
    });
    if (choicesArr.length === 0) {
        choicesArr.push({ value: '', label: type === 'bank' ? '-- لا توجد حسابات بنكية --' : '-- لا توجد خزائن --' });
    }
    if (window.paymentAccountChoices) {
        window.paymentAccountChoices.setChoices(choicesArr, 'value', 'label', true);
    }
}

function syncWarehouse(el) {
    // Ù…Ø²Ø§Ù…Ù†Ø© Ø§Ù„Ù…Ø®Ø²Ù† Ø¨ÙŠÙ† Ø§Ù„Ø£Ø²Ø±Ø© Ø§Ù„Ù…Ø®ØªÙ„ÙØ©
    const val = el.value;
    ['hdr-warehouse','hdr-warehouse-credit','hdr-warehouse-bank','hdr-warehouse-cheque'].forEach(id => {
        const s = document.getElementById(id);
        if (s && s !== el) s.value = val;
    });
    onMainWarehouseChange();
}


function initHeader() {
    // Invoice number â€” peek only (no increment)
    const invNo = peekNextInvoiceNumber(currentSaleType);
    document.getElementById('inv-number').textContent = invNo;
    document.getElementById('hdr-inv-no').value = invNo;

    // Date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('hdr-date').value = today;
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  SALE TYPE TABS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function setSaleType(btn, type) {
    currentSaleType = type;
    document.querySelectorAll('.action-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    const invNo = peekNextInvoiceNumber(type);
    document.getElementById('inv-number').textContent = invNo;
    document.getElementById('hdr-inv-no').value = invNo;
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  CUSTOMER CHANGE â†’ Financial Panel
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function onCustomerChange() {
    const id = document.getElementById('hdr-customer').value;
    const cust = CUSTOMERS.find(c => c.id === id);
    const cur = document.getElementById('hdr-currency').value;
    const strip = document.getElementById('fin-strip');
    const badge = document.getElementById('fin-status-badge');

    if (!cust || id === '') {
        ['fin-ledger','fin-checks','fin-total-debt','fin-credit-limit','fin-remaining'].forEach(el =>
            document.getElementById(el).textContent = 'â€”'
        );
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

    // Ø¥Ø¸Ù‡Ø§Ø± Ø§Ù„Ø´Ø±ÙŠØ· Ø§Ù„Ù…Ø§Ù„ÙŠ Ø§Ù„Ø£ÙÙ‚ÙŠ Ø¨Ø£Ù†ÙŠÙ…ÙŠØ´Ù†
    if (strip) {
        // Ø£Ø¹Ø¯ ØªØ´ØºÙŠÙ„ Ø§Ù„Ø£Ù†ÙŠÙ…ÙŠØ´Ù† Ø¨Ø¥Ø²Ø§Ù„Ø© ÙˆØ¥Ø¹Ø§Ø¯Ø© Ø§Ù„ÙƒÙ„Ø§Ø³
        strip.classList.remove('visible');
        requestAnimationFrame(() => requestAnimationFrame(() => strip.classList.add('visible')));
    }

    // badge Ø§Ù„Ø­Ø§Ù„Ø© Ø¨Ø¬Ø§Ù†Ø¨ Ø¹Ù†ÙˆØ§Ù† Ø§Ù„Ù„ÙˆØ­Ø©
    if (badge) {
        const txtEl = document.getElementById('fin-status-txt');
        const dot = badge.querySelector('.status-dot');
        if (cust.status === 'blacklisted') {
            dot.className = 'status-dot danger';
            txtEl.textContent = 'â›” Ù‚Ø§Ø¦Ù…Ø© Ø³ÙˆØ¯Ø§Ø¡';
            showToast('ØªØ­Ø°ÙŠØ±: Ù‡Ø°Ø§ Ø§Ù„Ø¹Ù…ÙŠÙ„ ÙÙŠ Ø§Ù„Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ø³ÙˆØ¯Ø§Ø¡!', 'error', 5000);
        } else if (remaining > 20000) {
            dot.className = 'status-dot good';
            txtEl.textContent = 'âœ… Ø§Ø¦ØªÙ…Ø§Ù† Ø¬ÙŠØ¯';
        } else if (remaining > 0) {
            dot.className = 'status-dot warn';
            txtEl.textContent = 'âš ï¸ Ø§Ø¦ØªÙ…Ø§Ù† Ù…Ù†Ø®ÙØ¶';
        } else {
            dot.className = 'status-dot danger';
            txtEl.textContent = 'ðŸš« ØªØ¬Ø§ÙˆØ² Ø§Ù„Ø­Ø¯';
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

    if (!name) { showToast('âŒ ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø§Ø³Ù… Ø§Ù„Ø¹Ù…ÙŠÙ„', 'error'); return; }

    const newId = 'C-' + String(Date.now()).slice(-6);
    const newCust = { 
        id: newId, 
        name: name, 
        phone: phone,
        whatsapp: whatsapp,
        email: email,
        address: address,
        securityLevel: securityLevel,
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

    showToast('âœ… ØªÙ… Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ø¹Ù…ÙŠÙ„ Ø¨Ù†Ø¬Ø§Ø­');
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
            showToast(`âœ… ØªÙ… Ø§Ø³ØªØ±Ø¬Ø§Ø¹ Ø§Ù„ÙØ§ØªÙˆØ±Ø© ${testNo}`);
            found = true;
        }
    }
    if(!found) showToast('â„¹ï¸ Ù„Ø§ ØªÙˆØ¬Ø¯ ÙÙˆØ§ØªÙŠØ± Ø³Ø§Ø¨Ù‚Ø© Ù…Ø­ÙÙˆØ¸Ø©', 'warning');
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
            showToast(`âœ… ØªÙ… Ø§Ø³ØªØ±Ø¬Ø§Ø¹ Ø§Ù„ÙØ§ØªÙˆØ±Ø© ${testNo}`);
            found = true;
            break;
        }
    }
    
    if(!found) {
        // If no next invoice saved, jump to "New" mode
        onSuccessNewInvoice();
        showToast('âœ¨ ÙØ§ØªÙˆØ±Ø© Ø¬Ø¯ÙŠØ¯Ø©', 'success');
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
            showToast('âœï¸ Ø£Ù†Øª Ø§Ù„Ø¢Ù† ÙÙŠ ÙˆØ¶Ø¹ Ø§Ù„ØªØ¹Ø¯ÙŠÙ„ Ù„Ù„ÙØ§ØªÙˆØ±Ø© Ø§Ù„Ø£ØµÙ„ÙŠØ©', 'warning', 4000);
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
            const c = CUSTOMERS.find(x => x.id === custId);
            if (c) selectCustomer(c.id, c.name);
        }
        
        // Rows
        if(data.items && data.items.length) {
            data.items.forEach(i => {
                addRow();
                const r = rows[rows.length-1];
                if (i.itemId) updateRow(r.id, 'itemId', i.itemId);
                if (i.selectedUnit) updateRow(r.id, 'selectedUnit', i.selectedUnit);
                if (i.customFactor !== undefined) updateRow(r.id, 'customFactor', i.customFactor);
                if (i.quantity !== undefined) updateRow(r.id, 'quantity', i.quantity);
                if (i.customBaseQty !== undefined) updateRow(r.id, 'customBaseQty', i.customBaseQty);
                if (i.price !== undefined) updateRow(r.id, 'price', i.price);
                if (i.fcPrice !== undefined) updateRow(r.id, 'fcPrice', i.fcPrice);
                if (i.warehouseId) updateRow(r.id, 'warehouseId', i.warehouseId);
                if (i.costCenterId) updateRow(r.id, 'costCenterId', i.costCenterId);
                if (i.qrCode) updateRow(r.id, 'qrCode', i.qrCode);
            });
        }
        
        renderGrid();
        recalcAll();
        return true;
    } catch(e) {
        console.error('Error loading invoice', e);
        return false;
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  CUSTOMERS DROPDOWN LOGIC
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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
            listEl.innerHTML = '<div class="custom-dropdown-empty">لا توجد نتائج مطابقة Ù„Ù„Ø¨Ø­Ø«.<br><small style="color:#888;">(Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø¹Ù…Ù„Ø§Ø¡: ' + CUSTOMERS.length + ')</small></div>';
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
        listEl.innerHTML = '<div class="custom-dropdown-empty">Ø­Ø¯Ø« Ø®Ø·Ø£ Ø¯Ø§Ø®Ù„ÙŠ</div>';
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
        if(listEl) listEl.classList.remove('show'); listEl.style.display = 'none';
        
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
    const listEl = document.getElementById('customer-dropdown-list');
    if (listEl) {
        listEl.classList.remove('show');
        listEl.style.display = 'none';
    }
    if(typeof onCustomerChange === 'function') onCustomerChange();
}

function onCustomerChange() {
    const id = document.getElementById('hdr-customer').value;
    const cust = CUSTOMERS.find(c => c.id === id);
    const cur = document.getElementById('hdr-currency').value;
    const strip = document.getElementById('fin-strip');
    const badge = document.getElementById('fin-status-badge');

    if (!cust || id === '') {
        ['fin-ledger','fin-checks','fin-total-debt','fin-credit-limit','fin-remaining'].forEach(el =>
            document.getElementById(el).textContent = 'â€”'
        );
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

    // Ø¥Ø¸Ù‡Ø§Ø± Ø§Ù„Ø´Ø±ÙŠØ· Ø§Ù„Ù…Ø§Ù„ÙŠ Ø§Ù„Ø£ÙÙ‚ÙŠ Ø¨Ø£Ù†ÙŠÙ…ÙŠØ´Ù†
    if (strip) {
        // Ø£Ø¹Ø¯ ØªØ´ØºÙŠÙ„ Ø§Ù„Ø£Ù†ÙŠÙ…ÙŠØ´Ù† Ø¨Ø¥Ø²Ø§Ù„Ø© ÙˆØ¥Ø¹Ø§Ø¯Ø© Ø§Ù„ÙƒÙ„Ø§Ø³
        strip.classList.remove('visible');
        requestAnimationFrame(() => requestAnimationFrame(() => strip.classList.add('visible')));
    }

    // badge Ø§Ù„Ø­Ø§Ù„Ø© Ø¨Ø¬Ø§Ù†Ø¨ Ø¹Ù†ÙˆØ§Ù† Ø§Ù„Ù„ÙˆØ­Ø©
    if (badge) {
        const txtEl = document.getElementById('fin-status-txt');
        const dot = badge.querySelector('.status-dot');
        if (cust.status === 'blacklisted') {
            dot.className = 'status-dot danger';
            txtEl.textContent = 'â›” Ù‚Ø§Ø¦Ù…Ø© Ø³ÙˆØ¯Ø§Ø¡';
            showToast('ØªØ­Ø°ÙŠØ±: Ù‡Ø°Ø§ Ø§Ù„Ø¹Ù…ÙŠÙ„ ÙÙŠ Ø§Ù„Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ø³ÙˆØ¯Ø§Ø¡!', 'error', 5000);
        } else if (remaining > 20000) {
            dot.className = 'status-dot good';
            txtEl.textContent = 'âœ… Ø§Ø¦ØªÙ…Ø§Ù† Ø¬ÙŠØ¯';
        } else if (remaining > 0) {
            dot.className = 'status-dot warn';
            txtEl.textContent = 'âš ï¸ Ø§Ø¦ØªÙ…Ø§Ù† Ù…Ù†Ø®ÙØ¶';
        } else {
            dot.className = 'status-dot danger';
            txtEl.textContent = 'ðŸš« ØªØ¬Ø§ÙˆØ² Ø§Ù„Ø­Ø¯';
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

    if (!name) { showToast('âŒ ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø§Ø³Ù… Ø§Ù„Ø¹Ù…ÙŠÙ„', 'error'); return; }

    const newId = 'C-' + String(Date.now()).slice(-6);
    const newCust = { 
        id: newId, 
        name: name, 
        phone: phone,
        whatsapp: whatsapp,
        email: email,
        address: address,
        securityLevel: securityLevel,
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

    showToast('âœ… ØªÙ… Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ø¹Ù…ÙŠÙ„ Ø¨Ù†Ø¬Ø§Ø­');
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
            showToast(`âœ… ØªÙ… Ø§Ø³ØªØ±Ø¬Ø§Ø¹ Ø§Ù„ÙØ§ØªÙˆØ±Ø© ${testNo}`);
            found = true;
        }
    }
    if(!found) showToast('â„¹ï¸ Ù„Ø§ ØªÙˆØ¬Ø¯ ÙÙˆØ§ØªÙŠØ± Ø³Ø§Ø¨Ù‚Ø© Ù…Ø­ÙÙˆØ¸Ø©', 'warning');
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
            showToast(`âœ… ØªÙ… Ø§Ø³ØªØ±Ø¬Ø§Ø¹ Ø§Ù„ÙØ§ØªÙˆØ±Ø© ${testNo}`);
            found = true;
            break;
        }
    }
    
    if(!found) {
        // If no next invoice saved, jump to "New" mode
        onSuccessNewInvoice();
        showToast('âœ¨ ÙØ§ØªÙˆØ±Ø© Ø¬Ø¯ÙŠØ¯Ø©', 'success');
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
            showToast('âœï¸ Ø£Ù†Øª Ø§Ù„Ø¢Ù† ÙÙŠ ÙˆØ¶Ø¹ Ø§Ù„ØªØ¹Ø¯ÙŠÙ„ Ù„Ù„ÙØ§ØªÙˆØ±Ø© Ø§Ù„Ø£ØµÙ„ÙŠØ©', 'warning', 4000);
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
            const c = CUSTOMERS.find(x => x.id === custId);
            if (c) selectCustomer(c.id, c.name);
        }
        
        // Rows
        if(data.items && data.items.length) {
            data.items.forEach(i => {
                addRow();
                const r = rows[rows.length-1];
                if (i.itemId) updateRow(r.id, 'itemId', i.itemId);
                if (i.selectedUnit) updateRow(r.id, 'selectedUnit', i.selectedUnit);
                if (i.customFactor !== undefined) updateRow(r.id, 'customFactor', i.customFactor);
                if (i.quantity !== undefined) updateRow(r.id, 'quantity', i.quantity);
                if (i.customBaseQty !== undefined) updateRow(r.id, 'customBaseQty', i.customBaseQty);
                if (i.price !== undefined) updateRow(r.id, 'price', i.price);
                if (i.fcPrice !== undefined) updateRow(r.id, 'fcPrice', i.fcPrice);
                if (i.warehouseId) updateRow(r.id, 'warehouseId', i.warehouseId);
                if (i.costCenterId) updateRow(r.id, 'costCenterId', i.costCenterId);
                if (i.qrCode) updateRow(r.id, 'qrCode', i.qrCode);
            });
        }
        
        renderGrid();
        recalcAll();
        return true;
    } catch(e) {
        console.error('Error loading invoice', e);
        return false;
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  CUSTOMERS DROPDOWN LOGIC
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function renderCustomerDropdown(filter = '') {
    const listEl = document.getElementById('customer-dropdown-list');
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    
    let matches = CUSTOMERS;
    if (term) {
        matches = CUSTOMERS.filter(c => c.name.toLowerCase().includes(term));
    }

    if (matches.length === 0) {
        listEl.innerHTML = `<div class="custom-dropdown-empty">Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ø¹Ù…ÙŠÙ„ Ø¨Ù‡Ø°Ø§ Ø§Ù„Ø§Ø³Ù….<br><small style="color:#888;">(Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø¹Ù…Ù„Ø§Ø¡: ${CUSTOMERS.length})</small></div>`;
        return;
    }

    matches.slice(0, 50).forEach(c => {
        const item = document.createElement('div');
        item.className = 'custom-dropdown-item';
        
        let displayText = c.name;
        if (c.phone) displayText += ` <span style="font-size:0.8rem; color:#aaa;">(${c.phone})</span>`;
        if (c.email) displayText += ` <span style="font-size:0.8rem; color:#888;">[${c.email}]</span>`;
        
        item.innerHTML = displayText;
        item.onmousedown = () => selectCustomer(c.id, c.name); 
        item.onpointerdown = () => selectCustomer(c.id, c.name);
        listEl.appendChild(item);
    });
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
            const cust = CUSTOMERS.find(c => c.id === idVal);
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  ITEMS DROPDOWN LOGIC
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
async function renderItemDropdown(rowId, isMobile = false, filter = '') {
    const prefix = isMobile ? 'm-' : '';
    const listEl = document.getElementById(`${prefix}item-dropdown-list-${rowId}`);
    if (!listEl) return;
    
    listEl.innerHTML = '<div class="custom-dropdown-empty">جاري البحث...</div>';
    const term = filter.toLowerCase().trim();
    
    try {
        let localMatches = ITEMS;
        if (term) {
            // Smart filter: match from the beginning of ANY word in the item name
            localMatches = ITEMS.filter(i => {
                const nameWords = i.name.toLowerCase().split(' ');
                const bcMatches = i.barcode && i.barcode.toLowerCase().startsWith(term);
                return nameWords.some(w => w.startsWith(term)) || bcMatches;
            });
        }

        let dbMatches = [];
        try {
            const res = await fetch('/api/items_search.php?q=' + encodeURIComponent(term));
            const rawMatches = await res.json();
            if (Array.isArray(rawMatches)) {
                dbMatches = rawMatches;
            }
        } catch(err) {
            console.error('API Search Failed:', err);
        }
        
        const invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        const WAREHOUSES = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
        const rawItems = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
        
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
                const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'Ù‚Ø·Ø¹Ø©';

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
                    bundleFactor: 1,
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
            if (isMobile && existing.unit) displayText += ' <span style="font-size:0.8rem; color:#aaa;">â€” ' + existing.unit + '</span>';
            if (existing.barcode) displayText += ' <span style="font-size:0.8rem; color:#888;">[' + existing.barcode + ']</span>';
            div.innerHTML = displayText;
            div.onmousedown = () => { updateRow(rowId, 'itemId', existing.id); renderGrid(); };
            div.onpointerdown = () => { updateRow(rowId, 'itemId', existing.id); renderGrid(); };
            listEl.appendChild(div);
        });
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">Ø®Ø·Ø£ ÙÙŠ Ø§Ù„Ø§ØªØµØ§Ù„</div>';
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
        if(listEl) listEl.classList.remove('show'); listEl.style.display = 'none';
        
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
                    searchEl.value = isMobile ? `${item.name} â€” ${item.unit}` : item.name;
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  QUICK ITEM ADD LOGIC
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
async function renderQuickItemDropdown(filter = '') {
    const listEl = document.getElementById('quick-item-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '<div class="custom-dropdown-empty">جاري البحث...</div>';
    const term = filter.toLowerCase().trim();
    
    try {
        let localMatches = ITEMS;
        if (term) {
            // Smart filter: match from the beginning of ANY word in the item name
            localMatches = ITEMS.filter(i => {
                const nameWords = i.name.toLowerCase().split(' ');
                const bcMatches = i.barcode && i.barcode.toLowerCase().startsWith(term);
                return nameWords.some(w => w.startsWith(term)) || bcMatches;
            });
        }

        let dbMatches = [];
        try {
            const res = await fetch('/api/items_search.php?q=' + encodeURIComponent(term));
            const rawMatches = await res.json();
            if (Array.isArray(rawMatches)) {
                dbMatches = rawMatches;
            }
        } catch(err) {
            console.error('API Search Failed:', err);
        }
        
        const invData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        const WAREHOUSES = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
        const rawItems = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
        
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
                const baseUnit = item.baseUnit || item.mainUnit || item.unit || 'Ù‚Ø·Ø¹Ø©';

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
                    bundleFactor: 1,
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
        finalMatches.forEach(existing => {
            const div = document.createElement('div');
            div.className = 'custom-dropdown-item';
            div.textContent = `${existing.name} â€” ${existing.unit}`;
            div.onmousedown = () => { selectQuickItem(existing.id); };
            div.onpointerdown = () => { selectQuickItem(existing.id); };
            listEl.appendChild(div);
        });
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">Ø®Ø·Ø£ ÙÙŠ Ø§Ù„Ø§ØªØµØ§Ù„</div>';
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
        if(listEl) listEl.classList.remove('show'); listEl.style.display = 'none';
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
    if(listEl) listEl.classList.remove('show'); listEl.style.display = 'none';
    
    renderGrid();
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  WAREHOUSE CHANGE
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function onMainWarehouseChange() {
    // Recalc available qty for rows using fallback warehouse
    renderGrid();
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  ROW MANAGEMENT
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function addRow(silent = false) {
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
    renderGrid();
    if (!quiet) showToast('📦 تم إضافة صنف جديد', 'success', 1500);
}

function removeRow(id) {
    rows = rows.filter(r => r.id !== id);
    renderGrid();
    recalcAll();
    showToast('ðŸ—‘ï¸ ØªÙ… Ø­Ø°Ù Ø§Ù„ØµÙ†Ù', 'warning', 1500);
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
            const item = ITEMS.find(i => i.id === row.itemId);
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  ROW CALCULATIONS (Live, no re-render)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function getResolvedWarehouse(row) {
    return row.warehouseId || document.getElementById('hdr-warehouse').value;
}

function getRowCalcs(row) {
    const item = ITEMS.find(i => i.id === row.itemId);
    
    let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
    let selectedUnitName = item ? item.unit : 'â€”';

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
    const availQty = item && item.stock[whId] !== undefined ? item.stock[whId] : 'â€”';
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

    if (totalEl) totalEl.textContent = fmtNum(calcs.total);
    if (baseEl && document.activeElement !== baseEl) baseEl.value = fmtNum(calcs.baseQty);
    if (bundleEl) bundleEl.textContent = fmtNum(calcs.bundles);
    if (shilaEl) shilaEl.textContent = fmtNum(calcs.shilaQty);
    if (shilaRemEl) shilaRemEl.textContent = fmtNum(calcs.shilaRem);
    if (priceEl && document.activeElement !== priceEl) priceEl.value = fmtNum(row.price);

    const isBaseSDG = document.getElementById('hdr-currency').value === 'SDG';
    const exRate = parseNum(document.getElementById('hdr-exchange-rate').value) || 1;
    let computedFcPrice = row.fcPrice !== undefined ? row.fcPrice : (isBaseSDG ? row.price / exRate : row.price * exRate);
    
    if (fcPriceEl && document.activeElement !== fcPriceEl) fcPriceEl.value = fmtNum(computedFcPrice);
    if (totalFcEl) totalFcEl.textContent = fmtNum(computedFcPrice * (row.quantity || 0));

    if (availEl) {
        availEl.textContent = calcs.availQty === 'â€”' ? 'â€”' : fmtNum(calcs.availQty);
        availEl.className = 'g-readonly ' + (calcs.availQty !== 'â€”' && calcs.availQty < row.quantity ? 'warn-val' : 'avail-val');
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  RENDER GRID
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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
        const item = ITEMS.find(i => i.id === row.itemId);
        const calcs = getRowCalcs(row);

        let itemUnitsHtml = '';
        if (item && item.unitsArray && item.unitsArray.length > 0) {
            itemUnitsHtml = item.unitsArray.map(u => `<option value="${u.name}" ${row.selectedUnit === u.name ? 'selected' : ''}>${u.name}</option>`).join('');
        } else {
            itemUnitsHtml = `<option value="${item ? item.unit : ''}">${item ? item.unit : 'â€”'}</option>`;
        }

        // â”€â”€ Desktop Row â”€â”€
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><div class="row-num">${idx + 1}</div></td>
            <td style="position:relative;">
                <input type="text" class="g-input" id="item-search-${row.id}" placeholder="Ø§Ø¨Ø­Ø« Ø£Ùˆ Ø§Ø®ØªØ± Ø§Ù„ØµÙ†Ù..." 
                    value="${item ? item.name : ''}" autocomplete="new-password"
                    style="font-size: 1.15rem !important; font-weight: 800 !important;"
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
            <td><input type="text" inputmode="decimal" class="g-input" value="${row.quantity || ''}" placeholder="0"
                onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)"
                onblur="this.value=parseNum(this.value)||''"></td>
            <td>
                <div style="display:flex; align-items:center; gap:4px;">
                    <input type="text" inputmode="decimal" class="g-input" data-price-input="${row.id}" value="${fmtNum(row.price)}"
                        onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                        onblur="this.value=fmtNum(parseNum(this.value))" style="flex:1;">
                    <button class="btn-icon" onclick="showPriceHistory(${row.id}, 'sales')" title="Ø³Ø¬Ù„ Ø£Ø³Ø¹Ø§Ø± Ø§Ù„ØµÙ†Ù Ù„Ù„Ø¹Ù…ÙŠÙ„" style="color:#38bdf8; background:rgba(56,189,248,0.1); border-radius:4px; padding:4px; border:1px solid rgba(56,189,248,0.2); cursor:pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </button>
                </div>
            </td>
            <td><span class="g-readonly total-val" style="color:var(--gold-primary); font-weight:bold;" data-total="${row.id}">${fmtNum(calcs.total)}</span></td>
            <td>
                <div style="display:flex; align-items:center; gap:4px; justify-content:center;">
                    <input type="text" inputmode="decimal" class="g-input" data-base-input="${row.id}" value="${fmtNum(calcs.baseQty)}" placeholder="0"
                        onfocus="this.select()" oninput="updateRow(${row.id},'customBaseQty',this.value)"
                        onblur="this.value=fmtNum(parseNum(this.value))" style="color:var(--primary-accent); font-weight:700; width:100%;" tabindex="-1">
                    <span style="font-size:11px; color:var(--text-muted); white-space:nowrap;">${item ? item.baseUnit : ''}</span>
                </div>
            </td>
            <td><input type="text" inputmode="decimal" class="g-input" placeholder="0" value="${row.availableQty !== undefined ? fmtNum(row.availableQty) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'availableQty',this.value)" onblur="this.value=fmtNum(parseNum(this.value))" style="color:#0ea5e9; font-weight:bold;"></td>
            <td><input type="text" inputmode="decimal" class="g-input" placeholder="0" value="${row.remainingQty !== undefined ? fmtNum(row.remainingQty) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'remainingQty',this.value)" onblur="this.value=fmtNum(parseNum(this.value))" style="color:#f59e0b; font-weight:bold;"></td>
            <td>
                <input type="text" inputmode="decimal" class="g-input" data-fcprice-input="${row.id}" value="${fmtNum(row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1)))}"
                    onfocus="this.select()" oninput="updateRow(${row.id},'fcPrice',this.value)"
                    onblur="this.value=fmtNum(parseNum(this.value))" style="color:#10b981; font-weight:700;" tabindex="-1">
            </td>
            <td><span class="g-readonly total-fc-val" style="color:#10b981; font-weight:bold;" data-total-fc="${row.id}">${fmtNum((row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1))) * (row.quantity || 0))}</span></td>

            <td>
                <select class="g-select" onchange="updateRow(${row.id},'warehouseId',this.value)" tabindex="-1">
                    <option value="">Ù†ÙØ³ Ø§Ù„ÙØ§ØªÙˆØ±Ø© â†©</option>
                    ${WAREHOUSES.map(w => `<option value="${w.id}" ${w.id === row.warehouseId ? 'selected' : ''}>${w.name}</option>`).join('')}
                </select>
            </td>

            <td><span class="g-readonly" style="color:#6366f1; font-weight:bold;" data-shila="${row.id}">${fmtNum(calcs.shilaQty)}</span></td>
            <td><span class="g-readonly" style="color:#ef4444; font-weight:bold;" data-shila-rem="${row.id}">${fmtNum(calcs.shilaRem)}</span></td>

            <td><input type="text" class="g-input" placeholder="Ù…Ù„Ø­ÙˆØ¸Ø©..." value="${row.note || ''}" oninput="updateRow(${row.id},'note',this.value)"></td>
            <td><input type="text" inputmode="decimal" class="g-input" placeholder="0" value="${row.buyPrice !== undefined ? fmtNum(row.buyPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'buyPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))"></td>
            <td><input type="text" inputmode="decimal" class="g-input" placeholder="0" value="${row.sellPrice !== undefined ? fmtNum(row.sellPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'sellPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))"></td>

            <td>
                <div style="display: flex; gap: 5px; justify-content: center;">
                    <button class="edit-btn" onclick="document.getElementById('item-search-${row.id}').focus()" title="ØªØ¹Ø¯ÙŠÙ„ Ø§Ù„Ø³Ø·Ø±" style="background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.2); color: #38bdf8; border-radius: 6px; padding: 6px; cursor: pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="del-btn" onclick="removeRow(${row.id})" title="Ø­Ø°Ù Ø§Ù„Ø³Ø·Ø±">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </td>
        `;
        if (window.reorderTds) window.reorderTds(tr);
        tbody.appendChild(tr);

        // â”€â”€ Mobile Card â”€â”€
        const card = document.createElement('div');
        card.className = 'm-card';
        card.innerHTML = `
            <div class="m-card-num">${idx + 1}</div>
            <div style="position: absolute; left: 10px; top: 10px; display: flex; gap: 5px;">
                <button class="m-card-edit" onclick="document.getElementById('m-item-search-${row.id}').focus()" title="ØªØ¹Ø¯ÙŠÙ„" style="background: transparent; border: none; color: #38bdf8; cursor: pointer;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </button>
                <button class="m-card-del" onclick="removeRow(${row.id})" title="Ø­Ø°Ù">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </div>
            <div class="ctrl-group m-card-full" style="padding-top:4px; position:relative;">
                <label class="label">Ø§Ù„ØµÙ†Ù</label>
                <input type="text" class="ctrl-input" id="m-item-search-${row.id}" placeholder="Ø§Ø¨Ø­Ø« Ø£Ùˆ Ø§Ø®ØªØ± Ø§Ù„ØµÙ†Ù..." 
                    value="${item ? item.name : ''}" autocomplete="new-password"
                    onfocus="this.select(); showItemDropdown(${row.id}, true)" 
                    onblur="hideItemDropdownDelayed(${row.id}, true)" 
                    oninput="filterItemDropdown(${row.id}, true)">
                <div class="custom-dropdown-list" id="m-item-dropdown-list-${row.id}" style="text-align:right;">
                    <!-- populated by JS -->
                </div>
            </div>
            <div class="m-card-grid">
                <div class="ctrl-group">
                    <label class="label">Ø§Ù„ÙˆØ­Ø¯Ø©</label>
                    <select class="ctrl-input" onchange="updateRow(${row.id},'selectedUnit',this.value); renderGrid();" tabindex="-1">
                        ${itemUnitsHtml}
                    </select>
                </div>
                <div class="ctrl-group">
                    <label class="label">Ø§Ù„ØªØ¹Ø§Ø¯Ù„</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" value="${row.customFactor !== undefined ? row.customFactor : calcs.unitFactor}" placeholder="1"
                onfocus="this.select()" oninput="updateRow(${row.id},'customFactor',this.value)"
                onblur="this.value=parseNum(this.value)||1" tabindex="-1">
                </div>
                <div class="ctrl-group">
                    <label class="label">Ø§Ù„ÙƒÙ…ÙŠØ© Ø§Ù„ÙˆØ­Ø¯Ø©</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" data-qty-input="${row.id}" value="${Number.isInteger(row.quantity) ? row.quantity : parseFloat((row.quantity || 0).toFixed(4)) || ''}" placeholder="0"
                        onfocus="this.select()" oninput="updateRow(${row.id},'quantity',this.value)">
                </div>
                <div class="ctrl-group">
                    <label class="label" style="color:#6366f1;">Ø§Ù„Ø´ÙŠÙ„Ø©</label>
                    <div class="ctrl-input readonly" data-m-shila="${row.id}" style="color:#6366f1; font-weight:bold; text-align:center;">${fmtNum(calcs.shilaQty)}</div>
                </div>
                <div class="ctrl-group">
                    <label class="label" style="color:#ef4444;">Ø¨Ø§Ù‚ÙŠ Ø³ÙŠØ®</label>
                    <div class="ctrl-input readonly" data-m-shila-rem="${row.id}" style="color:#ef4444; font-weight:bold; text-align:center;">${fmtNum(calcs.shilaRem)}</div>
                </div>
                <div class="ctrl-group">
                    <label class="label">Ø§Ù„Ø³Ø¹Ø± (Ù…Ø­Ù„ÙŠ / Ø£Ø¬Ù†Ø¨ÙŠ) <button class="btn-icon" onclick="showPriceHistory(${row.id}, 'sales')" title="Ø³Ø¬Ù„ Ø£Ø³Ø¹Ø§Ø± Ø§Ù„ØµÙ†Ù Ù„Ù„Ø¹Ù…ÙŠÙ„" style="color:#38bdf8; background:none; border:none; cursor:pointer; padding:0; margin-right:4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></button></label>
                    <div style="display:flex; gap:4px;">
                        <input type="text" inputmode="decimal" class="ctrl-input" value="${fmtNum(row.price)}" placeholder="0"
                            onfocus="this.select()" oninput="updateRow(${row.id},'price',this.value)"
                            onblur="this.value=fmtNum(parseNum(this.value))" style="flex:1;">
                        <input type="text" inputmode="decimal" class="ctrl-input" data-m-fcprice-input="${row.id}" value="${fmtNum(row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1)))}" placeholder="${document.getElementById('hdr-currency').value === 'SDG' ? 'Ø£Ø¬Ù†Ø¨ÙŠ' : 'Ø¬Ù†ÙŠÙ‡'}"
                            onfocus="this.select()" oninput="updateRow(${row.id},'fcPrice',this.value)"
                            onblur="this.value=fmtNum(parseNum(this.value))" style="flex:1; color:#10b981;">
                    </div>
                </div>
                <div class="ctrl-group">
                    <label class="label fc-total-label">${document.getElementById('hdr-currency').value === 'SDG' ? 'Ø¬Ù…Ù„Ø© Ø§Ù„ÙˆØ­Ø¯Ø© Ø£Ø¬Ù†Ø¨ÙŠ' : 'Ø¬Ù…Ù„Ø© Ø§Ù„ÙˆØ­Ø¯Ø© Ø¬Ù†ÙŠÙ‡'}</label>
                    <div class="ctrl-input readonly" data-m-total-fc="${row.id}" style="text-align:center;color:#10b981;">${fmtNum((row.fcPrice !== undefined ? row.fcPrice : (document.getElementById('hdr-currency').value === 'SDG' ? row.price / (parseNum(document.getElementById('hdr-exchange-rate').value)||1) : row.price * (parseNum(document.getElementById('hdr-exchange-rate').value)||1))) * (row.quantity || 0))}</div>
                </div>
                <div class="ctrl-group">
                    <label class="label">Ø¹Ø¯Ø¯ ${item ? item.baseUnit : ''}</label>
                    <input type="text" inputmode="decimal" class="ctrl-input" data-base-input="${row.id}" value="${fmtNum(calcs.baseQty)}" placeholder="0"
                onfocus="this.select()" oninput="updateRow(${row.id},'customBaseQty',this.value)"
                onblur="this.value=fmtNum(parseNum(this.value))" style="color:var(--primary-accent); font-weight:700;" tabindex="-1">
                </div>
                <div class="ctrl-group">
                    <label class="label">Ø§Ù„Ù…Ø®Ø²Ù†</label>
                    <select class="ctrl-input" onchange="updateRow(${row.id},'warehouseId',this.value)" tabindex="-1">
                        <option value="">Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ â†©</option>
                        ${WAREHOUSES.map(w => `<option value="${w.id}" ${w.id === row.warehouseId ? 'selected' : ''}>${w.name}</option>`).join('')}
                    </select>
                </div>

            </div>
            <div style="padding: 10px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 8px;">
                <div class="ctrl-group">
                    <label class="label">Ù…Ù„Ø­ÙˆØ¸Ø©</label>
                    <input type="text" class="ctrl-input" placeholder="Ù…Ù„Ø­ÙˆØ¸Ø©..." value="${row.note || ''}" oninput="updateRow(${row.id},'note',this.value)">
                </div>
                <div style="display:flex; gap:8px;">
                    <div class="ctrl-group" style="flex:1;">
                        <label class="label">Ø³Ø¹Ø± Ø§Ù„Ø´Ø±Ø§Ø¡</label>
                        <input type="text" inputmode="decimal" class="ctrl-input" placeholder="0" value="${row.buyPrice !== undefined ? fmtNum(row.buyPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'buyPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                    <div class="ctrl-group" style="flex:1;">
                        <label class="label">Ø³Ø¹Ø± Ø§Ù„Ø¨ÙŠØ¹</label>
                        <input type="text" inputmode="decimal" class="ctrl-input" placeholder="0" value="${row.sellPrice !== undefined ? fmtNum(row.sellPrice) : ''}" onfocus="this.select()" oninput="updateRow(${row.id},'sellPrice',this.value)" onblur="this.value=fmtNum(parseNum(this.value))">
                    </div>
                </div>
            </div>
            <div class="m-card-total" data-m-total="${row.id}">${fmtNum(calcs.total)} ${getCurrency()}</div>
        `;

    });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  ADJUSTMENTS (Discount / Tax / Addition)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  GRAND TOTAL CALCULATION
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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
    document.getElementById('t-grand').innerHTML = fmtNum(grand) + ' <span style="color: #ef4444; font-size: 0.9em; text-shadow: none;">' + cur + '</span>';

    // Footer
    document.getElementById('footer-grand').textContent = fmtNum(grand) + ' ' + cur;

    // Dynamic UI Animation â€” toggle CSS classes
    const totalsCard = document.getElementById('totals-calc-card');
    const formCard = document.getElementById('form-fields-card');
    if (totalsCard && formCard) {
        const hasItems = rows.some(r => r.itemId !== '' || parseFloat(r.price) > 0 || parseFloat(r.quantity) > 0);

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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  SAVE / SUBMIT
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function saveDraft() {
    if (rows.length === 0) { showToast('âš ï¸ Ù„Ø§ ØªÙˆØ¬Ø¯ Ø£ØµÙ†Ø§Ù Ù„Ø­ÙØ¸Ù‡Ø§', 'warning'); return; }
    const data = {
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
    showToast('ðŸ“‘ ØªÙ… Ø­ÙØ¸ Ø§Ù„Ù…Ø³ÙˆØ¯Ø© Ø¨Ù†Ø¬Ø§Ø­', 'success', 2500);
}

function submitInvoice(ignoreWarnings = false) {
    if (currentViewedInvoiceNo) {
        // Edit mode check
        const invDate = document.getElementById('hdr-date').value;
        if (!window.canEditRecord(invDate, 'sales')) {
            return; // blocked by policy
        }
    }

    const custId = document.getElementById('hdr-customer').value;
    if (!custId) { showToast('âŒ ÙŠØ±Ø¬Ù‰ ØªØ­Ø¯ÙŠØ¯ Ø§Ù„Ø¹Ù…ÙŠÙ„ Ù„ØªØ£ÙƒÙŠØ¯ Ø§Ù„ÙØ§ØªÙˆØ±Ø©', 'error'); return; }

    if (rows.length === 0) { showToast('âŒ Ù„Ø§ ÙŠÙ…ÙƒÙ† ØªØ£ÙƒÙŠØ¯ ÙØ§ØªÙˆØ±Ø© ÙØ§Ø±ØºØ©', 'error'); return; }

    const hasEmptyItems = rows.some(r => !r.itemId);
    if (hasEmptyItems) { showToast('âš ï¸ ÙŠØ±Ø¬Ù‰ Ø§Ø®ØªÙŠØ§Ø± Ø§Ù„ØµÙ†Ù Ù„ÙƒÙ„ Ø³Ø·Ø±', 'warning'); return; }

    const hasZeroQty = rows.some(r => r.quantity <= 0);
    if (hasZeroQty) { showToast('âš ï¸ Ø§Ù„ÙƒÙ…ÙŠØ© ÙŠØ¬Ø¨ Ø£Ù† ØªÙƒÙˆÙ† Ø£ÙƒØ¨Ø± Ù…Ù† ØµÙØ±', 'warning'); return; }

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
                
                alert(`âŒ Ø­Ø¸Ø± Ø§Ø¦ØªÙ…Ø§Ù†ÙŠ ØµØ§Ø±Ù…!\nØ§Ù„Ø¹Ù…ÙŠÙ„ (${cust.name}) Ù„Ø¯ÙŠÙ‡ ${unpaidCount} ÙÙˆØ§ØªÙŠØ± Ø¢Ø¬Ù„Ø© Ø³Ø§Ø¨Ù‚Ø© Ù„Ù… ÙŠÙ‚Ù… Ø¨Ø³Ø¯Ø§Ø¯Ù‡Ø§ Ø¨Ø§Ù„ÙƒØ§Ù…Ù„.\nÙŠØ¬Ø¨ Ø³Ø¯Ø§Ø¯ Ø§Ù„Ø¯ÙŠÙˆÙ† Ø§Ù„Ù‚Ø¯ÙŠÙ…Ø© Ø£ÙˆÙ„Ø§Ù‹ Ù„Ø¥Ù†Ø´Ø§Ø¡ ÙØ§ØªÙˆØ±Ø© Ø¢Ø¬Ù„Ø© Ø¬Ø¯ÙŠØ¯Ø©.`);
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
            const item = ITEMS.find(i => i.id === row.itemId);
            if (!item) continue;
            const calcs = getRowCalcs(row);
            if (calcs.availQty !== 'â€”' && calcs.availQty !== undefined && calcs.baseQty > calcs.availQty) {
                const diff = calcs.baseQty - calcs.availQty;
                warnings.push(`Ø§Ù„ØµÙ†Ù "<strong>${item.name}</strong>" ØªØ¬Ø§ÙˆØ² Ø§Ù„Ù…Ø®Ø²ÙˆÙ† Ø§Ù„Ù…ØªØ§Ø­. (Ø³ÙˆÙ ÙŠØ¯Ø®Ù„ Ø¨Ø§Ù„Ø³Ø§Ù„Ø¨ Ø¨Ù…Ù‚Ø¯Ø§Ø± <strong>${fmtNum(diff)} ${item.baseUnit || 'ÙˆØ­Ø¯Ø© Ø£Ø³Ø§Ø³ÙŠØ©'}</strong>)`);
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
                warnings.push(`Ø§Ù„ÙØ§ØªÙˆØ±Ø© ØªØªØ¬Ø§ÙˆØ² Ø­Ø¯ Ø§Ù„Ø§Ø¦ØªÙ…Ø§Ù†. (Ø§Ù„Ù…Ø¨Ù„Øº Ø³ÙŠØªØ¬Ø§ÙˆØ² Ø§Ù„Ø­Ø¯ Ø¨Ù…Ù‚Ø¯Ø§Ø± <strong>${fmtNum(diff)} ${cur}</strong>)`);
            }
        }

        if (warnings.length > 0) {
            showConfirmWarningModal(warnings);
            return;
        }
    }

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
        invoiceNo: committedNo,
        date: document.getElementById('hdr-date').value,
        dueDate: dueDateValue,
        customer: custId,
        costCenter: document.getElementById('hdr-cost-center').value,
        gpsLocation: {
            lat: document.getElementById('hdr-gps-lat').value,
            lng: document.getElementById('hdr-gps-lng').value
        },
        saleType: currentSaleType,
        currency: cur,
        exchangeRate: document.getElementById('hdr-exchange-rate').value,
        paymentMethod: paymentMethod,
        warehouse: document.getElementById('hdr-warehouse').value,
        description: document.getElementById('hdr-description').value,
        items: rows.map(r => ({...r})),
        notes: document.getElementById('inv-notes').value,
        subTotal: parseNum(document.getElementById('t-subtotal').textContent),
        discount: parseNum(document.getElementById('t-discount').value || 0),
        vatAmount: parseNum(document.getElementById('t-vat').textContent),
        grandTotal: parseNum(grandTotal),
        status: currentSaleType === 'preliminary' ? 'preliminary' : 'pending_delivery',
        timestamp: new Date().toISOString(),
        customerName: cust ? cust.name : 'Ø¹Ù…ÙŠÙ„ Ù†Ù‚Ø¯ÙŠ'
    };

    // Update Bank/Safe balance ONLY if not preliminary
    const paymentAccountId = document.getElementById('hdr-payment-account').value;
    const grandAmount = parseNum(grandTotal);
    let usedAccountName = '';
    
    if (currentSaleType !== 'preliminary' && paymentMethod !== 'credit' && paymentAccountId) {
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

    // Update Customer Balance (Debt) ONLY if not preliminary
    if (currentSaleType !== 'preliminary' && paymentMethod === 'credit' && custId && custId !== 'walk-in') {
        let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        const cIdx = customers.findIndex(c => c.id === custId);
        if (cIdx > -1) {
            let currentBalance = parseFloat(customers[cIdx].currentBalance);
            if (isNaN(currentBalance)) currentBalance = 0;
            customers[cIdx].currentBalance = currentBalance + grandAmount;
            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
        }
    }

    // Notice: Inventory is NO LONGER DEDUCTED HERE for new invoices!
    // It will be deducted only upon Delivery.

    // Show professional success modal (we can pass the warnings array so it still shows them if desired)
    // To grab the actual warnings to display in success modal, we need to generate them again if ignoreWarnings is true.
    let finalWarnings = [];
    if (ignoreWarnings) {
        const mainWh = document.getElementById('hdr-warehouse').value;
        for (const row of rows) {
            const item = ITEMS.find(i => i.id === row.itemId);
            if (!item) continue;
            const calcs = getRowCalcs(row);
            if (calcs.availQty !== 'â€”' && calcs.availQty !== undefined && calcs.baseQty > calcs.availQty) {
                const diff = calcs.baseQty - calcs.availQty;
                finalWarnings.push(`Ø§Ù„ØµÙ†Ù "<strong>${item.name}</strong>" ØªØ¬Ø§ÙˆØ² Ø§Ù„Ù…Ø®Ø²ÙˆÙ† Ø¨Ù…Ù‚Ø¯Ø§Ø± <strong>${fmtNum(diff)} ${item.baseUnit || 'ÙˆØ­Ø¯Ø© Ø£Ø³Ø§Ø³ÙŠØ©'}</strong>`);
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
                finalWarnings.push(`Ø§Ù„ÙØ§ØªÙˆØ±Ø© ØªØ¬Ø§ÙˆØ²Øª Ø­Ø¯ Ø§Ù„Ø§Ø¦ØªÙ…Ø§Ù† Ø¨Ù…Ù‚Ø¯Ø§Ø± <strong>${fmtNum(diff)} ${cur}</strong>`);
            }
        }
    }

    if (usedAccountName) {
        showToast(`ØªÙ…Øª Ø¥Ø¶Ø§ÙØ© ${fmtNum(grandAmount)} ${cur} Ø¥Ù„Ù‰ Ø±ØµÙŠØ¯: ${usedAccountName}`, 'success', 5000);
    }

    onSuccessNewInvoice(false);
    showSuccessModal(committedNo, grandTotal, cur, finalWarnings);

    // In production: POST to API
    // fetch('/api/v1/sales/invoices', { method:'POST', body:JSON.stringify(data) })
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  CONFIRM WARNING MODAL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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
                    <span>âš ï¸</span> ØªÙ†Ø¨ÙŠÙ‡ Ø¨ØªØ¬Ø§ÙˆØ² Ø§Ù„Ø­Ø¯ÙˆØ¯
                </h3>
                <button class="modal-close" onclick="closeConfirmWarningModal()">âœ•</button>
            </div>
            <div class="modal-body" style="color: #f1f5f9; font-size: 0.9rem; line-height: 1.6;">
                <p style="margin-bottom: 12px; color: #cbd5e1;">Ù„Ù‚Ø¯ ØªØ¬Ø§ÙˆØ²Øª Ù‡Ø°Ù‡ Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø¨Ø¹Ø¶ Ø§Ù„Ø­Ø¯ÙˆØ¯ Ø§Ù„Ù…Ø³Ù…ÙˆØ­Ø©. ÙŠØ±Ø¬Ù‰ Ù…Ø±Ø§Ø¬Ø¹Ø© Ø§Ù„ØªÙ†Ø¨ÙŠÙ‡Ø§Øª Ø§Ù„ØªØ§Ù„ÙŠØ© Ù‚Ø¨Ù„ Ø§Ù„ØªØ£ÙƒÙŠØ¯:</p>
                <ul style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 12px 24px;">
                    ${warnings.map(w => `<li style="margin-bottom: 8px;">${w}</li>`).join('')}
                </ul>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; border-top-color: rgba(245,158,11,0.2);">
                <button class="btn btn-ghost" onclick="closeConfirmWarningModal()">Ø¥Ù„ØºØ§Ø¡ Ø§Ù„Ù…Ø±Ø§Ø¬Ø¹Ø©</button>
                <button class="btn btn-primary" style="background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(217,119,6,0.12)); border-color: rgba(245,158,11,0.4); color: #fbbf24;" onclick="executeConfirmWarning()">Ø§Ø³ØªÙ…Ø±Ø§Ø± ÙˆØªØ£ÙƒÙŠØ¯ Ø§Ù„ÙØ§ØªÙˆØ±Ø©</button>
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  DELETE TRANSACTION MODAL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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
                    <span>âš ï¸</span> ØªØ£ÙƒÙŠØ¯ Ø§Ù„Ø­Ø°Ù
                </h3>
                <button class="modal-close" onclick="closeDeleteConfirmModal()">âœ•</button>
            </div>
            <div class="modal-body" style="color: #f1f5f9; font-size: 0.95rem; line-height: 1.6; text-align: center;">
                <p>Ù‡Ù„ Ø£Ù†Øª Ù…ØªØ£ÙƒØ¯ Ø£Ù†Ùƒ ØªØ±ÙŠØ¯ Ø­Ø°Ù Ù‡Ø°Ù‡ Ø§Ù„Ø¹Ù…Ù„ÙŠØ© ÙˆÙ…Ø³Ø­ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§ØªØŸ</p>
                <p style="color: #cbd5e1; font-size: 0.85rem; margin-top: 8px;">Ù„Ø§ ÙŠÙ…ÙƒÙ† Ø§Ù„ØªØ±Ø§Ø¬Ø¹ Ø¹Ù† Ù‡Ø°Ø§ Ø§Ù„Ø¥Ø¬Ø±Ø§Ø¡.</p>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:center; gap:15px; border-top-color: rgba(239,68,68,0.2);">
                <button class="btn btn-ghost" onclick="closeDeleteConfirmModal()">Ø¥Ù„ØºØ§Ø¡</button>
                <button class="btn btn-primary" style="background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(185,28,28,0.12)); border-color: rgba(239,68,68,0.4); color: #f87171;" onclick="executeDeleteTransaction()">Ù†Ø¹Ù…ØŒ Ø§Ø­Ø°Ù Ø§Ù„Ø¹Ù…Ù„ÙŠØ©</button>
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
        showToast('ðŸ—‘ï¸ ØªÙ… Ù…Ø³Ø­ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„ÙØ§ØªÙˆØ±Ø©', 'success');
        return;
    }
    const grandAmount = parseFloat(oldInv.grandTotal) || 0;

    // 1. Revert Customer Balance (if credit)
    if (oldInv.paymentMethod === 'credit' && oldInv.customer && oldInv.customer !== 'walk-in') {
        let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        const cIdx = customers.findIndex(c => c.id === oldInv.customer);
        if (cIdx > -1) {
            let currentBalance = parseFloat(customers[cIdx].currentBalance) || 0;
            customers[cIdx].currentBalance = currentBalance - grandAmount; // Revert debt
            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
        }
    }

    // 2. Revert Bank/Safe Balance (if cash)
    if (oldInv.paymentMethod !== 'credit' && oldInv.paymentAccountId) {
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
        const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
        if (bIdx > -1) {
            let currentBalance = parseFloat(banks[bIdx].currentBalance) || 0;
            banks[bIdx].currentBalance = currentBalance - grandAmount; // Revert cash receipt
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        }
    }

    // 3. Revert Inventory
    let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
    const mainWh = oldInv.warehouse || 'wh-001';
    if (oldInv.items && Array.isArray(oldInv.items)) {
        for (const row of oldInv.items) {
            const item = ITEMS.find(i => i.id === row.itemId);
            if (!item) continue;
            
            let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
            let baseQty = 0;
            if (row.customBaseQty !== undefined) {
                baseQty = row.customBaseQty;
            } else {
                baseQty = item ? row.quantity * selectedUnitFactor * item.conversionFactor : 0;
            }

            const whId = row.warehouseId || mainWh;
            const whName = WAREHOUSES.find(w => w.id === whId)?.name || 'Ø§Ù„Ù…Ø®Ø²Ù† Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ (Ø§Ù„Ø³Ø¬Ø§Ù†Ø©)';
            
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

    // 4. Remove from sales invoices list
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    allSales = allSales.filter(inv => inv.invoiceNo !== currentInvNo);
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));

    // 5. Delete individual records
    localStorage.removeItem('erp_inv_' + currentInvNo);
    localStorage.removeItem('erp_draft_' + currentInvNo);

    // Reset Form
    onSuccessNewInvoice(); 
    showToast('ðŸ—‘ï¸ ØªÙ… Ø­Ø°Ù Ø§Ù„ÙØ§ØªÙˆØ±Ø© ÙˆØ¥Ù„ØºØ§Ø¡ ØªØ£Ø«ÙŠØ±Ø§ØªÙ‡Ø§ Ø§Ù„Ù…Ø§Ù„ÙŠØ© ÙˆØ§Ù„Ù…Ø®Ø²Ù†ÙŠØ© Ø¨Ù†Ø¬Ø§Ø­', 'success');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  SUCCESS MODAL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function showSuccessModal(invoiceNo, grandTotal, currency, warnings = []) {
    // Remove any existing success modal
    const existing = document.getElementById('success-modal-overlay');
    if (existing) existing.remove();

    let warningsHtml = '';
    if (warnings && warnings.length > 0) {
        warningsHtml = `
            <div style="margin-top: 15px; padding: 12px; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 8px; text-align: right;">
                <strong style="color: #fbbf24; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                    <span>âš ï¸</span> ØªÙ†Ø¨ÙŠÙ‡Ø§Øª Ø£Ø«Ù†Ø§Ø¡ Ø§Ù„Ø­ÙØ¸:
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
            <h2 class="success-title">ðŸŽ‰ ØªÙ… Ø§Ù„Ø­ÙØ¸ Ø¨Ù†Ø¬Ø§Ø­!</h2>
            <p class="success-inv-no">Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø©: <strong>#${invoiceNo}</strong></p>
            <div class="success-amount-box">
                <div class="success-amount-label">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ù…Ø¨Ù„Øº</div>
                <div class="success-amount-value">${grandTotal}</div>
            </div>
            ${warningsHtml}
            <div class="success-actions" style="margin-top: 20px; display:flex; flex-direction:column; gap:10px;">
                <button class="btn btn-success success-btn" style="background:linear-gradient(135deg,#38bdf8,#0284c7);" onclick="printCurrentInvoice()">ðŸ–¨ï¸ Ø·Ø¨Ø§Ø¹Ø© Ø§Ù„ÙØ§ØªÙˆØ±Ø©</button>
                <div style="display:flex; gap:10px;">
                    <button class="btn btn-ghost success-btn" style="flex:1;" onclick="onSuccessNewInvoice()">Ø¹Ù…Ù„ÙŠØ© Ø¬Ø¯ÙŠØ¯Ø©</button>
                    <button class="btn btn-success success-btn" style="flex:1;" onclick="onSuccessDone()">Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…Ø¨ÙŠØ¹Ø§Øª</button>
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
    // Close modal
    if (closeModalFlag) {
        const overlay = document.getElementById('success-modal-overlay');
        if (overlay) { overlay.classList.remove('visible'); setTimeout(() => overlay.remove(), 400); }
    }

    // Reset form for new invoice
    rows = [];
    rowIdCounter = 0;
    document.getElementById('hdr-customer').value = '';
    
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
    showToast('ðŸ“‹ Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…Ø¨ÙŠØ¹Ø§Øª (Ù‚ÙŠØ¯ Ø§Ù„ØªØ·ÙˆÙŠØ±)', 'warning');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  PRICE HISTORY MODAL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function showPriceHistory(rowId, type) {
    const row = rows.find(r => r.id === rowId);
    if (!row || !row.itemId) {
        showToast('âš ï¸ ÙŠØ±Ø¬Ù‰ Ø§Ø®ØªÙŠØ§Ø± Ø§Ù„ØµÙ†Ù Ø£ÙˆÙ„Ø§Ù‹', 'warning');
        return;
    }
    
    const custId = document.getElementById('hdr-customer').value;
    if (!custId) {
        showToast('âš ï¸ ÙŠØ±Ø¬Ù‰ ØªØ­Ø¯ÙŠØ¯ Ø§Ù„Ø¹Ù…ÙŠÙ„ Ø£ÙˆÙ„Ø§Ù‹ Ù„Ø¹Ø±Ø¶ Ø³Ø¬Ù„Ù‡', 'warning');
        return;
    }
    
    const item = ITEMS.find(i => i.id === row.itemId);
    const itemName = item ? item.name : 'ØµÙ†Ù ØºÙŠØ± Ù…Ø¹Ø±ÙˆÙ';
    const custName = document.getElementById('hdr-customer-search').value || 'Ø§Ù„Ø¹Ù…ÙŠÙ„';

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
        tbodyHtml = `<tr><td colspan="5" style="text-align:center; color:#94a3b8; padding: 15px;">Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ø³Ø¬Ù„ Ø£Ø³Ø¹Ø§Ø± Ø³Ø§Ø¨Ù‚ Ù„Ù‡Ø°Ø§ Ø§Ù„ØµÙ†Ù Ù…Ø¹ Ù‡Ø°Ø§ Ø§Ù„Ø¹Ù…ÙŠÙ„</td></tr>`;
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
                    Ø³Ø¬Ù„ Ø§Ù„Ø£Ø³Ø¹Ø§Ø± Ø§Ù„Ø³Ø§Ø¨Ù‚Ø©
                </h3>
                <button class="modal-close" onclick="closePriceHistoryModal()">âœ•</button>
            </div>
            <div class="modal-body">
                <div style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                        <span style="color:var(--text-muted); font-size:0.85rem;">Ø§Ù„ØµÙ†Ù:</span>
                        <strong style="color:var(--text-primary);">${itemName}</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted); font-size:0.85rem;">Ø§Ù„Ø¹Ù…ÙŠÙ„:</span>
                        <strong style="color:var(--text-primary);">${custName}</strong>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.9rem;">
                        <thead>
                            <tr style="background: rgba(0,0,0,0.2);">
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">Ø§Ù„ØªØ§Ø±ÙŠØ®</th>
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø©</th>
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">Ø§Ù„ÙƒÙ…ÙŠØ©</th>
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">Ø§Ù„Ø³Ø¹Ø±</th>
                                <th style="padding:10px; border-bottom:2px solid rgba(255,255,255,0.1); color:var(--text-muted);">Ù…Ø§ ÙŠØ¹Ø§Ø¯Ù„ Ø¨Ø§Ù„Ø¯ÙˆÙ„Ø§Ø±</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tbodyHtml}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end;">
                <button class="btn btn-ghost" onclick="closePriceHistoryModal()">Ø¥ØºÙ„Ø§Ù‚</button>
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  PRINT LOGIC â€” uses InvoicePrinter engine
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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
                itemName:     item ? item.name : 'â€”',
                unit:         r.selectedUnit || (item ? item.unit : 'â€”'),
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
        type:          'sales',
        typeLabel:     currentSaleType === 'direct' ? 'ÙØ§ØªÙˆØ±Ø© Ù…Ø¨ÙŠØ¹Ø§Øª Ù…Ø¨Ø§Ø´Ø±Ø©' :
                       currentSaleType === 'split'  ? 'ÙØ§ØªÙˆØ±Ø© Ø¨ÙŠØ¹ Ù…Ù‚Ø³Ù‘Ù…Ø©'    :
                       currentSaleType === 'proforma'? 'Ø¹Ø±Ø¶ Ø³Ø¹Ø± (Ø¨Ø±ÙˆÙÙˆØ±Ù…Ø§)'  : 'ÙØ§ØªÙˆØ±Ø© Ù…Ø¨ÙŠØ¹Ø§Øª',
        invoiceNo:     document.getElementById('hdr-inv-no').value,
        date:          document.getElementById('hdr-date').value,
        customerName:  document.getElementById('hdr-customer-search').value || 'Ø²Ø¨ÙˆÙ† Ù†Ù‚Ø¯ÙŠ',
        currency:      getCurrency(),
        exchangeRate:  parseNum(document.getElementById('hdr-exchange-rate')?.value || '1'),
        paymentMethod: document.getElementById('hdr-payment-method').value,
        warehouseName: whObj ? whObj.name : 'â€”',
        notes:         document.getElementById('inv-notes').value,
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

// Legacy alias kept for backward compatibility
function generatePrintArea(type) {
    printCurrentInvoice();
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  OLD generatePrintArea body (replaced)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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

    const custName = document.getElementById('hdr-customer-search').value || (type === 'sales' ? 'Ø¹Ù…ÙŠÙ„ Ù†Ù‚Ø¯ÙŠ' : 'Ù…ÙˆØ±Ø¯ Ù†Ù‚Ø¯ÙŠ');
    const invNo = document.getElementById('hdr-inv-no').value;
    const invDate = document.getElementById('hdr-date').value;
    const grandTotal = document.getElementById('t-grand').textContent;
    const currency = getCurrency();

    let html = '';
    
    // 1. The Main Invoice Copy
    html += `
    <div class="print-page" style="padding: 20px; direction: rtl; font-family: 'Cairo', Tahoma, Arial;">
        <div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
            <h2>${type === 'sales' ? 'ÙØ§ØªÙˆØ±Ø© Ù…Ø¨ÙŠØ¹Ø§Øª' : 'ÙØ§ØªÙˆØ±Ø© Ù…Ø´ØªØ±ÙŠØ§Øª'}</h2>
            <h3>Ø´Ø±ÙƒØ© Ø§Ù„Ø³Ø¬Ø§Ù†Ø©</h3>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom: 20px; font-size: 14px;">
            <div><strong>Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø©:</strong> ${invNo}</div>
            <div><strong>Ø§Ù„ØªØ§Ø±ÙŠØ®:</strong> ${invDate}</div>
            <div><strong>${type === 'sales' ? 'Ø§Ù„Ø¹Ù…ÙŠÙ„' : 'Ø§Ù„Ù…ÙˆØ±Ø¯'}:</strong> ${custName}</div>
        </div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;" border="1">
            <thead>
                <tr style="background:#eee; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    <th style="padding:8px;">Ù…</th>
                    <th style="padding:8px;">Ø§Ù„ØµÙ†Ù</th>
                    <th style="padding:8px;">Ø§Ù„ÙˆØ­Ø¯Ø©</th>
                    <th style="padding:8px;">Ø§Ù„ÙƒÙ…ÙŠØ©</th>
                    <th style="padding:8px;">Ø§Ù„Ø³Ø¹Ø±</th>
                    <th style="padding:8px;">Ø§Ù„Ø¥Ø¬Ù…Ø§Ù„ÙŠ</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    rows.forEach((r, idx) => {
        const item = ITEMS.find(i => i.id === r.itemId);
        const itemName = item ? item.name : 'ØºÙŠØ± Ù…Ø¹Ø±ÙˆÙ';
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
            <strong>Ø§Ù„Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ:</strong> ${grandTotal} ${currency}
        </div>
        <div style="margin-top: 50px; display:flex; justify-content:space-between;">
            <div><strong>${type === 'sales' ? 'Ø§Ù„Ø¨Ø§Ø¦Ø¹' : 'Ø§Ù„Ù…Ø³ØªÙ„Ù…'}:</strong> ......................</div>
            <div><strong>ØªÙˆÙ‚ÙŠØ¹ ${type === 'sales' ? 'Ø§Ù„Ø¹Ù…ÙŠÙ„' : 'Ø§Ù„Ù…ÙˆØ±Ø¯'}:</strong> ......................</div>
        </div>
    </div>
    `;

    // 2. The Warehouse Issue/Receipt Notes
    for (let whId in warehouseGroups) {
        let whName = "Ø§Ù„Ù…Ø³ØªÙˆØ¯Ø¹ Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ";
        if (whId) {
            let w = WAREHOUSES.find(x => x.id === whId);
            if (w) whName = w.name;
        }
        
        let noteTitle = type === 'sales' ? 'Ø¥Ø°Ù† ØµØ±Ù Ù…Ø®Ø²Ù†ÙŠ' : 'Ø¥Ø°Ù† Ø§Ø³ØªÙ„Ø§Ù… Ù…Ø®Ø²Ù†ÙŠ';

        html += `
        <div class="print-page" style="padding: 20px; direction: rtl; font-family: 'Cairo', Tahoma, Arial;">
            <div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
                <h2>${noteTitle}</h2>
                <h3>Ù…Ø³ØªÙˆØ¯Ø¹: ${whName}</h3>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom: 20px; font-size: 14px;">
                <div><strong>Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø§Ù„Ù…Ø±Ø¬Ø¹ÙŠ:</strong> ${invNo}</div>
                <div><strong>Ø§Ù„ØªØ§Ø±ÙŠØ®:</strong> ${invDate}</div>
                <div><strong>${type === 'sales' ? 'Ø§Ù„Ø¹Ù…ÙŠÙ„' : 'Ø§Ù„Ù…ÙˆØ±Ø¯'}:</strong> ${custName}</div>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;" border="1">
                <thead>
                    <tr style="background:#eee; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <th style="padding:8px;">Ù…</th>
                        <th style="padding:8px;">Ø§Ù„ØµÙ†Ù</th>
                        <th style="padding:8px;">Ø§Ù„ÙˆØ­Ø¯Ø©</th>
                        <th style="padding:8px;">Ø§Ù„ÙƒÙ…ÙŠØ©</th>
                        <th style="padding:8px;">Ù…Ù„Ø§Ø­Ø¸Ø§Øª Ø§Ù„Ù…Ø³ØªÙˆØ¯Ø¹</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        warehouseGroups[whId].forEach((r, idx) => {
            const item = ITEMS.find(i => i.id === r.itemId);
            const itemName = item ? item.name : 'ØºÙŠØ± Ù…Ø¹Ø±ÙˆÙ';
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
                <div><strong>Ø£Ù…ÙŠÙ† Ø§Ù„Ù…Ø³ØªÙˆØ¯Ø¹:</strong> ......................</div>
                <div><strong>Ø§Ù„Ù…Ø³ØªÙ„Ù…:</strong> ......................</div>
            </div>
        </div>
        `;
    }
    
    printArea.innerHTML = html;
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  GPS TOGGLE
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
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
        status.textContent = 'GPS Ø¥ÙŠÙ‚Ø§Ù';
        showToast('ØªÙ… Ø¥ÙŠÙ‚Ø§Ù Ø®ØªÙ… Ø§Ù„Ù…ÙˆÙ‚Ø¹ Ø§Ù„Ø¬ØºØ±Ø§ÙÙŠ', 'warning');
    } else {
        // Turn on
        if (navigator.geolocation) {
            btn.style.color = '#fbbf24';
            status.textContent = 'Ø¬Ø§Ø±ÙŠ Ø§Ù„ØªØ­Ø¯ÙŠØ¯...';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    latInp.value = pos.coords.latitude;
                    lngInp.value = pos.coords.longitude;
                    btn.style.color = '#10b981';
                    btn.style.background = 'rgba(16,185,129,0.15)';
                    status.textContent = 'GPS Ù†Ø´Ø·';
                    showToast('ØªÙ… Ø§Ù„ØªÙ‚Ø§Ø· Ø¥Ø­Ø¯Ø§Ø«ÙŠØ§Øª Ø§Ù„Ù…ÙˆÙ‚Ø¹ Ø¨Ù†Ø¬Ø§Ø­ âœ…', 'success');
                },
                (err) => {
                    btn.style.color = '#f87171';
                    status.textContent = 'Ù…Ø±ÙÙˆØ¶';
                    showToast('ØªÙ… Ø±ÙØ¶ ØµÙ„Ø§Ø­ÙŠØ© Ø§Ù„Ù…ÙˆÙ‚Ø¹ Ø£Ùˆ ÙØ´Ù„ Ø§Ù„ØªØ­Ø¯ÙŠØ¯', 'error');
                }
            );
        } else {
            showToast('Ù…ØªØµÙØ­Ùƒ Ù„Ø§ ÙŠØ¯Ø¹Ù… ØªØ­Ø¯ÙŠØ¯ Ø§Ù„Ù…ÙˆÙ‚Ø¹', 'error');
        }
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
//  DELIVERY HUB & PRELIMINARY LOGIC
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

function loadDeliveryTransactions() {
    const tbody = document.getElementById('delivery-transactions-body');
    const emptyState = document.getElementById('delivery-empty-state');
    
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let pendingInvoices = allSales.filter(inv => inv.status === 'pending_delivery');
    
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
    if (!whOptions) whOptions = `<option value='main'>Ø§Ù„Ù…Ø®Ø²Ù† Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ (Ø§Ù„Ø³Ø¬Ø§Ù†Ø©)</option>`;

    pendingInvoices.forEach(inv => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td style='text-align:right; font-weight:800; color:var(--gold-primary);'>${inv.invoiceNo}</td>
            <td style='text-align:right; color:var(--text-dim);'>${new Date(inv.timestamp).toLocaleString('ar-EG')}</td>
            <td style='text-align:right; font-weight:600;'>${inv.customerName || inv.customer || 'Ø¹Ù…ÙŠÙ„ Ù†Ù‚Ø¯ÙŠ'}</td>
            <td style='text-align:center;'>
                <select id='del-wh-${inv.invoiceNo}' class='v-input' style='padding: 4px; font-size: 0.8rem;'>
                    ${whOptions}
                </select>
            </td>
            <td style='text-align:center;'>
                <button onclick="executeDelivery('${inv.invoiceNo}')" style='background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#10b981; border-radius:6px; padding:4px 8px; cursor:pointer;'>ØªØ³Ù„ÙŠÙ… ÙˆØ§Ø¹ØªÙ…Ø§Ø¯</button>
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

function executeDelivery(invNo) {
    if (!confirm('Ù‡Ù„ Ø£Ù†Øª Ù…ØªØ£ÙƒØ¯ Ù…Ù† ØªØ³Ù„ÙŠÙ… Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø±Ù‚Ù… ' + invNo + ' ÙˆØ®ØµÙ… Ø§Ù„Ø¨Ø¶Ø§Ø¹Ø© Ù…Ù† Ø§Ù„Ù…Ø®Ø²ÙˆÙ†ØŸ')) return;

    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let invIndex = allSales.findIndex(i => i.invoiceNo === invNo);
    if (invIndex === -1) return;

    let inv = allSales[invIndex];
    let selectedWhId = document.getElementById('del-wh-' + invNo).value;
    
    let warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
    let whName = warehouses.find(w => w.id === selectedWhId)?.name || 'Ø§Ù„Ù…Ø®Ø²Ù† Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ (Ø§Ù„Ø³Ø¬Ø§Ù†Ø©)';

    // Deduct Inventory
    let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
    
    if (inv.items) {
        for (const row of inv.items) {
            let qtyToDeduct = row.baseQty || (row.quantity * (row.factor || 1));
            
            let itemInv = currentInvData.find(i => i.itemId === row.itemId && i.warehouse === whName);
            if (itemInv) {
                itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) - qtyToDeduct;
            } else {
                currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: -qtyToDeduct, minQty: 10 });
            }
            
            if (typeof ITEMS !== 'undefined') {
                const item = ITEMS.find(i => i.id === row.itemId);
                if (item) {
                    if (item.actualStock && item.actualStock[selectedWhId] !== undefined) {
                        item.actualStock[selectedWhId] -= qtyToDeduct;
                    } else if (item.actualStock) {
                        item.actualStock[selectedWhId] = -qtyToDeduct;
                    }
                }
            }
        }
    }
    
    localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));
    
    // Update Invoice Status
    inv.status = 'delivered';
    inv.deliveredAt = new Date().toISOString();
    inv.deliveryWarehouse = whName;
    
    allSales[invIndex] = inv;
    localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(inv));

    showToast('ØªÙ… Ø§Ù„ØªØ³Ù„ÙŠÙ… ÙˆØ®ØµÙ… Ø§Ù„Ù…Ø®Ø²ÙˆÙ† Ø¨Ù†Ø¬Ø§Ø­!', 'success');
    loadDeliveryTransactions();
}

function convertToOfficial(invNo) {
    if (!confirm('ØªØ­ÙˆÙŠÙ„ Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø§Ù„Ù…Ø¨Ø¯Ø¦ÙŠØ© Ø±Ù‚Ù… ' + invNo + ' Ø¥Ù„Ù‰ ÙØ§ØªÙˆØ±Ø© Ø±Ø³Ù…ÙŠØ© (ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø­Ø³Ø§Ø¨Ø§Øª)ØŸ')) return;

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

    showToast('ØªÙ… ØªØ­ÙˆÙŠÙ„ Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø§Ù„Ù…Ø¨Ø¯Ø¦ÙŠØ© Ø¨Ù†Ø¬Ø§Ø­!', 'success');
    if (typeof loadHubTransactions === 'function') loadHubTransactions();
}

async function sendInvoiceWA(invoiceNo) {
    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : localStorage.getItem('erp_wa_user_id');
        const res = await fetch(`http://localhost:3001/wa/status?userId=${userId}`);
        const data = await res.json();
        
        if (data.status !== 'CONNECTED' && data.status !== 'AUTHENTICATED') {
            const wantsToConnect = typeof showWAConnectPrompt === 'function' ? await showWAConnectPrompt() : confirm('Ø±Ù‚Ù… Ø§Ù„ÙˆØ§ØªØ³Ø§Ø¨ ØºÙŠØ± Ù…Ø±Ø¨ÙˆØ·. Ù‡Ù„ ØªØ±ÙŠØ¯ Ø§Ù„Ø±Ø¨Ø· Ø§Ù„Ø¢Ù†ØŸ');
            if (wantsToConnect) {
                if (typeof openWAModal === 'function') {
                    openWAModal();
                } else {
                    alert('ÙŠØ±Ø¬Ù‰ Ø§Ù„Ø°Ù‡Ø§Ø¨ Ø¥Ù„Ù‰ Ø´Ø§Ø´Ø© Ø§Ù„Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª Ù„Ø±Ø¨Ø· Ø§Ù„ÙˆØ§ØªØ³Ø§Ø¨.');
                }
            }
            return;
        }
    } catch (err) {
        alert('Ø®Ø¯Ù…Ø© Ø§Ù„ÙˆØ§ØªØ³Ø§Ø¨ ØºÙŠØ± Ù…ØªØ§Ø­Ø© Ø­Ø§Ù„ÙŠØ§Ù‹. ØªØ£ÙƒØ¯ Ù…Ù† ØªØ´ØºÙŠÙ„ Ø§Ù„Ø®Ø§Ø¯Ù….');
        return;
    }

    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    let inv = allSales.find(i => i.invoiceNo === invoiceNo);
    if (!inv) { alert('Ù„Ù… ÙŠØªÙ… Ø§Ù„Ø¹Ø«ÙˆØ± Ø¹Ù„Ù‰ Ø§Ù„ÙØ§ØªÙˆØ±Ø©'); return; }

    const phone = await new Promise((resolve) => {
        const overlayId = 'wa-phone-prompt-overlay';
        if (document.getElementById(overlayId)) document.getElementById(overlayId).remove();
        
        const html = `
        <div id="${overlayId}" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:999999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);">
            <div style="background:linear-gradient(145deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95)); border:1px solid rgba(37,211,102,0.4); border-radius:24px; padding:35px; width:90%; max-width:450px; text-align:center; transform:translateY(40px) scale(0.9); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow:0 25px 50px -12px rgba(37,211,102,0.2), inset 0 1px 0 rgba(255,255,255,0.1);">
                
                <div style="width:80px; height:80px; background:linear-gradient(135deg, #25D366, #128C7E); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#fff; box-shadow:0 10px 20px rgba(37,211,102,0.4); border:3px solid rgba(255,255,255,0.2);">
                    <svg viewBox="0 0 24 24" width="45" height="45" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                
                <h3 style="color:#f8fafc; font-size:1.6rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0; font-weight:800;">Ø¥Ø±Ø³Ø§Ù„ Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø¹Ø¨Ø± ÙˆØ§ØªØ³Ø§Ø¨</h3>
                <p style="color:#94a3b8; font-size:1rem; margin-bottom:25px; line-height:1.6; font-family:'Tajawal',sans-serif;">ÙŠØ±Ø¬Ù‰ Ø¥Ø¯Ø®Ø§Ù„ Ø±Ù‚Ù… Ù‡Ø§ØªÙ Ø§Ù„Ø¹Ù…ÙŠÙ„ Ù…Ø¯Ø¹ÙˆÙ…Ø§Ù‹ Ø¨Ø§Ù„Ù…ÙØªØ§Ø­ Ø§Ù„Ø¯ÙˆÙ„ÙŠ (Ù…Ø«Ø§Ù„: 249912345678)</p>
                
                <div style="margin-bottom:25px; position:relative;">
                    <input type="tel" id="wa-prompt-input" placeholder="Ø±Ù‚Ù… Ø§Ù„Ù‡Ø§ØªÙ..." style="width:100%; background:rgba(0,0,0,0.4); border:2px solid rgba(255,255,255,0.1); color:#fff; padding:15px; border-radius:12px; font-size:1.2rem; text-align:center; transition:all 0.3s; outline:none; font-family:'Inter',sans-serif;" onfocus="this.style.borderColor='#25d366'; this.style.boxShadow='0 0 15px rgba(37,211,102,0.3)';" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none';">
                </div>

                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-send" style="background:linear-gradient(135deg, #25d366, #1da851); color:#fff; border:none; padding:12px 30px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.3s; box-shadow:0 4px 15px rgba(37,211,102,0.4); display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(37,211,102,0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(37,211,102,0.4)';">
                        <span>Ø¥Ø±Ø³Ø§Ù„</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                    <button id="wa-prompt-cancel" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">Ø¥Ù„ØºØ§Ø¡</button>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
        
        const overlay = document.getElementById(overlayId);
        const modal = overlay.children[0];
        const input = document.getElementById('wa-prompt-input');
        
        // Setup existing customer phone if available
        let defaultPhone = '';
        if (inv.customer && inv.customer !== 'walk-in') {
            let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            let cust = customers.find(c => c.id === inv.customer);
            if(cust) defaultPhone = cust.whatsapp || cust.phone || '';
        }
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

    const htmlContent = window.InvoicePrinter.buildHTML(inv);
    
    const printContainer = document.createElement('div');
    printContainer.style.position = 'absolute';
    printContainer.style.left = '-9999px';
    printContainer.style.width = '800px';
    printContainer.style.background = '#fff';
    printContainer.style.color = '#000';
    printContainer.style.padding = '20px';
    
    const styleSheet = `
        <style>
            .inv-doc { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; direction: rtl; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
            th { background-color: #f8fafc; color: #1e293b; }
            .inv-total-row { display: flex; justify-content: space-between; padding: 5px 0; font-weight: bold; }
        </style>
    `;
    printContainer.innerHTML = styleSheet + htmlContent;
    document.body.appendChild(printContainer);

    const message = 'Ù…Ø±Ø­Ø¨Ø§Ù‹ØŒ Ù…Ø±ÙÙ‚ ÙØ§ØªÙˆØ±ØªÙƒÙ… Ø±Ù‚Ù… ' + invoiceNo + ' Ù…Ù† Ù…Ø¤Ø³Ø³Ø© Ø§Ù„Ø³Ø¬Ø§Ù†Ø© Ù„Ù„ØªØ¬Ø§Ø±Ø©. Ø´ÙƒØ±Ø§Ù‹ Ù„ØªØ¹Ø§Ù…Ù„ÙƒÙ… Ù…Ø¹Ù†Ø§!';
    
    try {
        await sendPDFViaWhatsApp(printContainer, phone, message);
    } catch (e) {
        console.error(e);
    } finally {
        printContainer.remove();
    }
}
