// js/posting-accounts.js

let isLoaded = false;
let defaultUsdRate = 600;

async function fetchExchangeRate() {
    try {
        const res = await fetch('api_index.php/exchange-rate');
        const data = await res.json();
        if (data && data.rate) {
            defaultUsdRate = parseFloat(data.rate) || 600;
        }
    } catch(e) {
        console.error('Failed to fetch default usd rate:', e);
    }
}

async function initApp() {
    if (isLoaded) return;
    isLoaded = true;
    try {
        const params = new URLSearchParams(window.location.search);
        if (params.get('filter') === 'whatsapp') {
            currentType = 'whatsapp';
            document.querySelectorAll('.type-chip').forEach(chip => chip.classList.remove('active'));
            const waChip = document.getElementById('type-whatsapp');
            if (waChip) waChip.classList.add('active');
        }
    } catch (e) {}
    await fetchExchangeRate();
    await loadAllTransactions();
}

document.addEventListener('DBReady', initApp);
document.addEventListener('DOMContentLoaded', initApp);

// Immediate fallback in case events already fired
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initApp();
} else {
    setTimeout(initApp, 400);
}

let allTransactionsData = [];
let filteredData = []; 
let currentStatus = 'unposted';
let currentType = 'all';
let selectedRowIndex = -1;
let searchQuery = '';
let dateFrom = '';
let dateTo = '';

function isWhatsAppReceiptItem(item) {
    if (!item) return false;
    return Boolean(
        item.isWhatsAppInbound ||
        item.source === 'whatsapp_inbound' ||
        item.inboundReceiptId ||
        String(item.voucherNo || item.id || '').startsWith('BNK-WA-')
    );
}

const fmtCurrency = (num) => Number(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const paymentMethodMap = {
    'cash': 'نقدي',
    'credit': 'آجل',
    'cheque': 'شيك',
    'bank': 'تحويل بنكي',
    'hybrid': 'دفع مشترك',
    'multipay': 'دفع مشترك'
};

let accountsList = [];
let safesList = [];
let banksList = [];
let suppliersList = [];
let customersList = [];
let itemsList = [];

async function loadEntities() {
    const getTenantKey = window.getTenantKey || ((k) => k);
    
    async function getList(key) {
        let data = [];
        try {
            let val = null;
            if (typeof localforage !== 'undefined') {
                val = await localforage.getItem(getTenantKey(key)) || await localforage.getItem(key);
            }
            if (val === null) {
                val = localStorage.getItem(getTenantKey(key)) || localStorage.getItem(key);
            }
            
            if (val) {
                if (typeof val === 'string') {
                    try {
                        data = JSON.parse(val);
                    } catch(e) {
                        data = [];
                    }
                } else {
                    data = val;
                }
            }
        } catch(e) {
            data = [];
        }
        
        if (!Array.isArray(data)) {
            if (data && typeof data === 'object') {
                data = Object.values(data);
            } else {
                data = [];
            }
        }
        return data.filter(x => !x.deleted);
    }

    accountsList = await getList('erp_accounts_v1');
    safesList = await getList('erp_safes_v1');
    banksList = await getList('erp_banks_v1');
    suppliersList = await getList('erp_suppliers_v1');
    customersList = await getList('erp_customers_v1');
    itemsList = await getList('erp_items_v1');
    buildAccountNamesMap();
}

const accountNamesMap = new Map();

function buildAccountNamesMap() {
    accountNamesMap.clear();
    const prefixRegex = /(customer|supplier|cust|cus|supp|sup|bank|safe|exp|sub|sust)[-\s_:]*/gi;
    
    const cleanStr = (str) => {
        if (!str) return '';
        return String(str).replace(/(customer|supplier|cust|cus|supp|sup|bank|safe|exp|sub|sust)[-\s_:]*/gi, '').trim().toLowerCase();
    };

    const addMapping = (key, name) => {
        if (!key || !name) return;
        const skey = String(key).trim();
        const skeyLower = skey.toLowerCase();
        
        if (!accountNamesMap.has(skeyLower)) {
            accountNamesMap.set(skeyLower, name);
        }
        
        const cleaned = cleanStr(skey);
        if (cleaned && !accountNamesMap.has(cleaned)) {
            accountNamesMap.set(cleaned, name);
        }
    };

    // 1. Add static map keys
    const staticMap = {
        'safe-001': 'الخزينة الرئيسية (نقداً)',
        'safe-002': 'خزينة مبيعات التجزئة',
        'bank-001': 'بنك الخرطوم',
        'bank-002': 'بنك أم درمان الوطني',
        'exp-rent': 'مصروف الإيجار',
        'exp-salaries': 'مصروف الرواتب والأجور',
        'exp-electricity': 'مصروف الكهرباء والمياه',
        'revenue-sales': 'إيرادات المبيعات',
        'purchases': 'المشتريات'
    };
    for (const [k, v] of Object.entries(staticMap)) {
        addMapping(k, v);
    }

    // 2. Add customers
    if (Array.isArray(customersList)) {
        customersList.forEach(c => {
            addMapping(c.id, c.name);
            addMapping(c.name, c.name);
            addMapping(c.glAccount, c.name);
            addMapping(c.code, c.name);
        });
    }

    // 3. Add suppliers
    if (Array.isArray(suppliersList)) {
        suppliersList.forEach(s => {
            addMapping(s.id, s.name);
            addMapping(s.name, s.name);
            addMapping(s.glAccount, s.name);
            addMapping(s.code, s.name);
        });
    }

    // 4. Add safes
    if (Array.isArray(safesList)) {
        safesList.forEach(s => {
            addMapping(s.id, s.name);
            addMapping(s.name, s.name);
            addMapping(s.glAccount, s.name);
            addMapping(s.code, s.name);
        });
    }

    // 5. Add banks
    if (Array.isArray(banksList)) {
        banksList.forEach(b => {
            addMapping(b.id, b.name);
            addMapping(b.name, b.name);
            addMapping(b.glAccount, b.name);
            addMapping(b.code, b.name);
        });
    }

    // 6. Add accounts
    if (Array.isArray(accountsList)) {
        accountsList.forEach(a => {
            addMapping(a.id, a.name);
            addMapping(a.accountNo, a.name);
            addMapping(a.name, a.name);
        });
    }
}

function getAccName(accCode, returnNullIfNotFound = false) {
    if (!accCode) return returnNullIfNotFound ? null : '-';
    const code = String(accCode).trim();
    const codeLower = code.toLowerCase();
    
    // Check direct map
    if (accountNamesMap.has(codeLower)) {
        return accountNamesMap.get(codeLower);
    }
    
    // Clean and check
    const cleanCode = code.replace(/(customer|supplier|cust|cus|supp|sup|bank|safe|exp|sub|sust)[-\s_:]*/gi, '').trim().toLowerCase();
    if (accountNamesMap.has(cleanCode)) {
        return accountNamesMap.get(cleanCode);
    }
    
    return returnNullIfNotFound ? null : cleanCode;
}

function getTransactionAmounts(item) {
    let localAmt = 0;
    let foreignAmt = 0;
    let currency = 'USD';

    const cur = item.currency || 'SDG';
    const rate = parseFloat(item.exchangeRate || item.rate) || 1;

    if (item._metaKey === 'erp_single_receipts_v1' || item._metaKey === 'erp_single_payments_v1') {
        localAmt = parseFloat(item.amountLocal) || 0;
        if (cur !== 'SDG') {
            foreignAmt = parseFloat(item.amountForeign) || 0;
            currency = cur;
        } else {
            foreignAmt = defaultUsdRate > 0 ? (localAmt / defaultUsdRate) : 0;
            currency = 'USD';
        }
    } else if (item._metaKey === 'erp_receipts_v1' || item._metaKey === 'erp_payments_v1') {
        localAmt = parseFloat(item.totalAmount || item.amountLocal) || 0;
        let hasForeign = false;
        if (item.details && Array.isArray(item.details)) {
            let fAmt = 0;
            let fCur = '';
            item.details.forEach(d => {
                if (d.currency && d.currency !== 'SDG') {
                    fAmt += parseFloat(d.amountForeign) || 0;
                    fCur = d.currency;
                    hasForeign = true;
                }
            });
            if (fAmt > 0) {
                foreignAmt = fAmt;
                currency = fCur;
            }
        }
        if (!hasForeign) {
            foreignAmt = defaultUsdRate > 0 ? (localAmt / defaultUsdRate) : 0;
            currency = 'USD';
        }
    } else if (item._metaKey === 'erp_sales_invoices_v1' || item._metaKey === 'erp_purchase_invoices_v1') {
        const rawAmt = parseFloat(item.grandTotal || item.totalAmount || item.netTotal || item.total || 0);
        if (cur === 'SDG') {
            localAmt = rawAmt;
            foreignAmt = defaultUsdRate > 0 ? (localAmt / defaultUsdRate) : 0;
            currency = 'USD';
        } else {
            foreignAmt = rawAmt;
            localAmt = rawAmt * rate;
            currency = cur;
        }
    } else {
        localAmt = parseFloat(item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0);
        if (cur !== 'SDG') {
            foreignAmt = parseFloat(item.amountForeign) || 0;
            if (!foreignAmt && rate > 1) {
                foreignAmt = localAmt / rate;
            }
            currency = cur;
        } else {
            foreignAmt = defaultUsdRate > 0 ? (localAmt / defaultUsdRate) : 0;
            currency = 'USD';
        }
    }

    // Rounding to 2 decimal places to look nice
    foreignAmt = Math.round(foreignAmt * 100) / 100;
    localAmt = Math.round(localAmt * 100) / 100;

    return { localAmt, foreignAmt, currency };
}

function getItemName(itemCode) {
    if (!itemCode) return 'صنف غير معروف';
    const code = String(itemCode).trim();
    const foundItem = itemsList.find(i => i.id === code || i.itemCode === code || i.name === code);
    if (foundItem) return foundItem.name;
    return code;
}

function checkPostPermission() {
    try {
        const userStr = localStorage.getItem('user');
        if (!userStr) return false;
        const user = JSON.parse(userStr);

        // Super Admin or admin fallback has bypass
        if (user.username === 'admin' || user.role === 'admin' || user.roleId === 'admin_fallback' || user.securityLevel >= 9) {
            return true;
        }

        // Check if posting_accounts has 'approve' permission
        const perms = window.resolveUserPermissions ? window.resolveUserPermissions(user) : (user.permissions || {});
        if (perms.posting_accounts && perms.posting_accounts.approve) {
            return true;
        }
    } catch(e) {
        console.error('Permission check error', e);
    }
    return false;
}

function handleFilterChange() {
    searchQuery = document.getElementById('search-input').value.toLowerCase().trim();
    dateFrom = document.getElementById('date-from').value;
    dateTo = document.getElementById('date-to').value;
    resetSelection();
    applyFiltersAndRender();
}

function setStatus(status) {
    currentStatus = status;
    document.querySelectorAll('.status-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('status-' + status).classList.add('active');
    
    resetSelection();
    applyFiltersAndRender();
}

function setType(type) {
    currentType = type;
    document.querySelectorAll('.type-chip').forEach(chip => chip.classList.remove('active'));
    document.getElementById('type-' + type).classList.add('active');
    
    resetSelection();
    applyFiltersAndRender();
}

function resetSelection() {
    const checkAll = document.getElementById('check-all-rows');
    if (checkAll) checkAll.checked = false;
    
    // Explicitly uncheck all checkboxes
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = false;
    });

    selectedRowIndex = -1;
    updateStickyBar();
    hideDetailPane();
}

function updateFilterChipsCounts() {
    // Filter transactions matching the current status (unposted vs posted)
    const statusData = allTransactionsData.filter(item => {
        const isPosted = item.isPosted || false;
        return currentStatus === 'posted' ? isPosted : !isPosted;
    });

    const counts = {
        all: statusData.length,
        whatsapp: statusData.filter(item => isWhatsAppReceiptItem(item)).length,
        sales: statusData.filter(item => item._typeEn === 'sales').length,
        purchases: statusData.filter(item => item._typeEn === 'purchases').length,
        receipt: statusData.filter(item => item._typeEn === 'receipt').length,
        payment: statusData.filter(item => item._typeEn === 'payment').length,
        journal: statusData.filter(item => item._typeEn === 'journal').length
    };

    const labels = {
        all: 'الكل',
        whatsapp: 'إشعارات واتساب',
        sales: 'مبيعات',
        purchases: 'مشتريات',
        receipt: 'سندات قبض',
        payment: 'سندات صرف',
        journal: 'قيود يومية'
    };

    const icons = {
        all: '📂',
        whatsapp: '📲',
        sales: '🛍️',
        purchases: '📥',
        receipt: '💰',
        payment: '💸',
        journal: '📊'
    };

    // If the currently active type filter count becomes 0, reset currentType to 'all'
    if (currentType !== 'all' && counts[currentType] === 0) {
        currentType = 'all';
        document.querySelectorAll('.type-chip').forEach(chip => chip.classList.remove('active'));
        const allBtn = document.getElementById('type-all');
        if (allBtn) allBtn.classList.add('active');
    }

    for (const type in counts) {
        const btn = document.getElementById(`type-${type}`);
        if (btn) {
            if (counts[type] === 0 && type !== 'whatsapp') {
                btn.style.display = 'none';
            } else {
                btn.style.display = 'flex';
                btn.innerHTML = `
                    <span class="chip-icon">${icons[type]}</span>
                    <span class="chip-text">${labels[type]}</span>
                    <span class="chip-count">${counts[type]}</span>
                `;
            }
        }
    }
}

function applyFiltersAndRender() {
    updateFilterChipsCounts();

    let fromTime = dateFrom ? new Date(dateFrom).getTime() : 0;
    let toTime = dateTo ? new Date(dateTo).getTime() : Infinity;

    filteredData = allTransactionsData.filter(item => {
        // Status Filter
        const statusMatch = currentStatus === 'unposted' ? !item.isPosted : item.isPosted;
        if (!statusMatch) return false;
        
        // Type Filter
        if (currentType === 'whatsapp') {
            if (!isWhatsAppReceiptItem(item)) return false;
        } else if (currentType !== 'all' && item._typeEn !== currentType) {
            return false;
        }
        
        // Date Filter
        const itemTime = item._date.getTime();
        if (itemTime < fromTime || itemTime > toTime) return false;

        // Search Query Filter
        if (searchQuery) {
            const amt = item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0;
            const ref = (item.id || item.invoiceNo || item.voucherNo || item.transferNo || '').toLowerCase();
            const customer = (item.customerName || item.supplierName || item.customer || item.supplier || item.description || '').toLowerCase();
            
            if (!ref.includes(searchQuery) && !customer.includes(searchQuery) && !String(amt).includes(searchQuery)) {
                return false;
            }
        }
        
        return true;
    });

    renderMasterTable();
}

async function syncWhatsAppReceiptsIntoLocalBeforeLoad() {
    try {
        const tId = localStorage.getItem('magaies_active_tenant') || 'default';
        const res = await fetch('api/inbound_receipts.php?action=list&tenant_id=' + encodeURIComponent(tId));
        const data = await res.json();
        if (!data || !data.success) return;

        if (Array.isArray(data.banks) && data.banks.length > 0 && (!Array.isArray(banksList) || banksList.length === 0)) {
            banksList = data.banks;
            buildAccountNamesMap();
        }

        const waReceipts = Array.isArray(data.data) ? data.data : [];
        if (waReceipts.length === 0) return;

        let localReceipts = [];
        try {
            localReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1') || '[]');
            if (!Array.isArray(localReceipts)) localReceipts = [];
        } catch (e) { localReceipts = []; }

        let changed = false;
        waReceipts.forEach(r => {
            if (!r || !r.voucher_no) return;
            const amt = parseFloat(r.amount || 0);
            if (amt <= 0) return;
            const isPostedFlag = (r.status === 'verified' && r.issue_reason !== 'awaiting_posting_approval');
            const vNo = String(r.voucher_no);
            const bankName = r.bank_name || 'بنك الخرطوم (بنكك)';
            const bankAccId = (Array.isArray(banksList) && banksList.find(b => b.name === bankName)?.id) || 'BANK-BOK-001';
            const existingIdx = localReceipts.findIndex(v => v && (v.voucherNo === vNo || v.id === vNo));
            const voucherObj = {
                id: vNo,
                voucherNo: vNo,
                type: 'receipt',
                date: r.transfer_date || (r.created_at ? r.created_at.split(' ')[0] : new Date().toISOString().split('T')[0]),
                mainAccount: bankAccId,
                toAccount: bankAccId,
                fromAccount: r.matched_customer_id || r.customer_name || '',
                customerId: r.matched_customer_id || '',
                customerName: r.customer_name || '',
                totalAmount: amt,
                amount: amt,
                currency: 'SDG',
                exchangeRate: 1,
                paymentMethod: 'bank',
                description: `إشعار واتساب وارد رقم #${r.transfer_no || r.id} - ${r.customer_name || ''}`,
                source: 'whatsapp_inbound',
                isWhatsAppInbound: true,
                inboundReceiptId: r.id,
                receiptImage: r.receipt_image || '',
                transferNo: r.transfer_no || '',
                senderPhone: r.customer_phone || '',
                bankAccountId: bankAccId,
                bankName: bankName,
                isPosted: isPostedFlag,
                items: [{
                    account: r.matched_customer_id || r.customer_name || '',
                    accountName: r.customer_name || '',
                    amount: amt,
                    currency: 'SDG',
                    exchangeRate: 1,
                    amountForeign: amt,
                    paymentMethod: 'bank',
                    paymentMethods: ['bank'],
                    bankAmount: amt,
                    bankName: bankName,
                    bankAccountId: bankAccId,
                    transferNo: r.transfer_no || '',
                    receiptImage: r.receipt_image || '',
                    bankTransfers: [{
                        amount: amt,
                        bankName: bankName,
                        bankAccountId: bankAccId,
                        refNo: r.transfer_no || '',
                        transferNo: r.transfer_no || '',
                        image: r.receipt_image || ''
                    }],
                    statement: `إشعار واتساب وارد رقم #${r.transfer_no || r.id}`
                }]
            };
            if (existingIdx >= 0) {
                localReceipts[existingIdx] = { ...localReceipts[existingIdx], ...voucherObj };
            } else {
                localReceipts.unshift(voucherObj);
                changed = true;
            }
        });
        if (changed || waReceipts.length > 0) {
            localStorage.setItem('erp_receipts_v1', JSON.stringify(localReceipts));
        }
    } catch (e) {
        console.warn('syncWhatsAppReceiptsIntoLocalBeforeLoad warn:', e);
    }
}

async function loadAllTransactions() {
    try {
        await loadEntities();
        await syncWhatsAppReceiptsIntoLocalBeforeLoad();
        const getTenantKey = window.getTenantKey || ((k) => k);
        const keys = [
            { key: 'erp_sales_invoices_v1', type: 'مبيعات', typeEn: 'sales', class: 'type-sales' },
            { key: 'erp_purchase_invoices_v1', type: 'مشتريات', typeEn: 'purchases', class: 'type-purchases' },
            { key: 'erp_single_receipts_v1', type: 'سند قبض', typeEn: 'receipt', class: 'type-receipt' },
            { key: 'erp_receipts_v1', type: 'سند قبض مجمع', typeEn: 'receipt', class: 'type-receipt' },
            { key: 'erp_single_payments_v1', type: 'سند صرف', typeEn: 'payment', class: 'type-payment' },
            { key: 'erp_payments_v1', type: 'سند صرف مجمع', typeEn: 'payment', class: 'type-payment' },
            { key: 'erp_journal_vouchers_v1', type: 'قيد يومية', typeEn: 'journal', class: 'type-journal' }
        ];

        let allData = [];

        for (const meta of keys) {
            const tKey = getTenantKey(meta.key);
            let list1 = [];
            let list2 = [];
            
            if (typeof localforage !== 'undefined') {
                try { list1 = await localforage.getItem(tKey) || []; } catch(e){}
                try { list2 = await localforage.getItem(meta.key) || []; } catch(e){}
            }
            if (!Array.isArray(list1) || list1.length === 0) {
                try { list1 = JSON.parse(localStorage.getItem(tKey) || '[]'); } catch(e){}
            }
            if (!Array.isArray(list2) || list2.length === 0) {
                try { list2 = JSON.parse(localStorage.getItem(meta.key) || '[]'); } catch(e){}
            }
            if (list1 && typeof list1 === 'object' && !Array.isArray(list1)) list1 = Object.values(list1);
            if (list2 && typeof list2 === 'object' && !Array.isArray(list2)) list2 = Object.values(list2);

            const itemMap = new Map();
            [...(Array.isArray(list2) ? list2 : []), ...(Array.isArray(list1) ? list1 : [])].forEach(item => {
                if (!item) return;
                const k = String(item.voucherNo || item.id || item.paymentNo || item.receiptNo || item.invoiceNo || Math.random());
                itemMap.set(k, item);
            });
            let data = Array.from(itemMap.values());

            if (Array.isArray(data)) {
                let validItems = data.filter(item => item && !item.deleted);
                if (meta.key === 'erp_sales_invoices_v1') {
                    validItems = validItems.filter(item => item.saleType !== 'preliminary' && item.status !== 'preliminary');
                } else if (meta.key === 'erp_purchase_invoices_v1') {
                    validItems = validItems.filter(item => !item.isGRN);
                }
                validItems.forEach(item => {
                    const dateVal = item.date || item.createdAt;
                    if (!dateVal) return; // Skip records with missing dates
                    const parsedDate = new Date(dateVal);
                    if (isNaN(parsedDate.getTime()) || parsedDate.getFullYear() <= 1970) {
                        return; // Skip invalid or epoch 1970 dates
                    }

                    const isWA = isWhatsAppReceiptItem(item);
                    allData.push({
                        ...item,
                        _sourceKey: tKey,
                        _metaKey: meta.key,
                        _typeLabel: isWA ? '📲 إشعار واتساب' : meta.type,
                        _typeClass: meta.class,
                        _typeEn: meta.typeEn,
                        _date: parsedDate
                    });
                });
            }
        }

        allData.sort((a, b) => b._date.getTime() - a._date.getTime());
        allTransactionsData = allData;
        applyFiltersAndRender();

    } catch (e) {
        console.error('Error loading transactions:', e);
        showToast('خطأ في تحميل البيانات.', 'error');
    }
}

function renderMasterTable() {
    const table = document.getElementById('data-table');
    const tbody = document.getElementById('table-body');
    const noDataMsg = document.getElementById('no-data-msg');

    if (filteredData.length === 0) {
        table.style.display = 'none';
        noDataMsg.style.display = 'flex';
        return;
    }

    table.style.display = 'table';
    noDataMsg.style.display = 'none';
    
    // Checkbox header visibility
    document.getElementById('check-all-rows').style.visibility = currentStatus === 'unposted' ? 'visible' : 'hidden';

    let html = '';
    filteredData.forEach((item, index) => {
        const amt = item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0;
        const ref = item.id || item.invoiceNo || item.voucherNo || '-';
        const dateStr = !isNaN(item._date.getTime()) ? item._date.toLocaleDateString('en-GB') : '-';
        let customerId = item.customerId || item.supplierId || item.customer || item.supplier || '';
        if (!customerId) {
            if (item._metaKey === 'erp_single_receipts_v1') {
                customerId = item.fromAccount || '';
            } else if (item._metaKey === 'erp_single_payments_v1') {
                customerId = item.toAccount || '';
            } else if (item.items && item.items.length > 0) {
                customerId = item.items[0].account || item.items[0].accountId || '';
            }
        }
        
        let customer = getAccName(customerId, true);
        if (!customer) customer = getAccName(item.customerName || item.supplierName, true);
        if (!customer) customer = item.customerName || item.supplierName || item.description || 'بدون اسم';
        
        const dueDate = item.dueDate ? new Date(item.dueDate).toLocaleDateString('en-GB') : '-';
        const pMethod = paymentMethodMap[item.paymentMethod] || item.paymentMethod || 'نقدي';

        const checkHtml = currentStatus === 'unposted' 
            ? `<input type="checkbox" class="row-checkbox" value="${index}" onchange="onRowCheck(event)" onclick="event.stopPropagation()">` 
            : `<span style="color:var(--success); font-size:1.2rem;">✔️</span>`;

        // Determine item data for expanded view
        let itemName = '-';
        let itemQty = '-';
        let itemPrice = '-';

        if (item.items && item.items.length > 0) {
            const first = item.items[0];
            let name = first.itemName || first.name || first.item || '';
            if (!name && (first.account || first.accountId)) {
                name = getAccName(first.account || first.accountId);
            }
            if (!name) name = '-';
            
            itemName = name;
            itemQty = first.qty || first.quantity || 0;
            
            if (item._metaKey === 'erp_payments_v1') {
                itemName = getAccName(item.fromAccount || 'safe-001'); // مكان الصنف يظهر الحساب الذي دفع منه
                itemQty = `<span style="font-family:'Tajawal', sans-serif; font-size:0.82rem; white-space:nowrap; background:rgba(255,255,255,0.07); padding:4px 8px; border-radius:6px; color:#cbd5e1;">${getAccName(item.fromAccount || 'safe-001')}</span>`; // مكان الكمية الخزنة
            } else if (item._metaKey === 'erp_receipts_v1') {
                // For multi receipt
                itemQty = `<span style="font-family:'Tajawal', sans-serif; font-size:0.82rem; white-space:nowrap; background:rgba(255,255,255,0.07); padding:4px 8px; border-radius:6px; color:#cbd5e1;">${getAccName(item.toAccount || 'safe-001')}</span>`;
            }
            
            itemPrice = fmtCurrency(first.price || first.rate || first.unitPrice || 0);
            if (item.items.length > 1) {
                itemName += ` <span style="font-size:0.75rem; color:#38bdf8; font-weight:normal;">(+${item.items.length - 1} حسابات/أصناف)</span>`;
            }
        } else {
            // For simple vouchers, display the direct account and total amount
            if (item._metaKey === 'erp_single_payments_v1') {
                itemName = getAccName(item.fromAccount || 'safe-001'); // مكان الصنف يظهر الحساب الذي دفع منه
                itemQty = `<span style="font-family:'Tajawal', sans-serif; font-size:0.82rem; white-space:nowrap; background:rgba(255,255,255,0.07); padding:4px 8px; border-radius:6px; color:#cbd5e1;">${getAccName(item.fromAccount || 'safe-001')}</span>`; // مكان الكمية الخزنة
                itemPrice = fmtCurrency(amt);
            } else if (item._metaKey === 'erp_single_receipts_v1') {
                itemName = getAccName(item.fromAccount); // الحساب المدفوع منه يكون في الصنف
                itemQty = `<span style="font-family:'Tajawal', sans-serif; font-size:0.82rem; white-space:nowrap; background:rgba(255,255,255,0.07); padding:4px 8px; border-radius:6px; color:#cbd5e1;">${getAccName(item.toAccount || 'safe-001')}</span>`; // ومكان الكمية يظهر الخزنة
                itemPrice = fmtCurrency(amt);
            } else if (item.toAccount || item.fromAccount) {
                itemName = getAccName(item.toAccount || item.fromAccount);
                itemPrice = fmtCurrency(amt);
                itemQty = '-';
            }
        }

        html += `
            <tr class="main-row" id="row-${index}" onclick="selectRow(${index})">
                <td style="text-align: center;">${checkHtml}</td>
                <td><span class="type-badge ${item._typeClass}">${item._typeLabel}</span></td>
                <td class="date-col">${dateStr}</td>
                <td class="ref-col">${ref}</td>
                <td class="customer-col">${customer}</td>
                <td class="extra-column" style="font-weight: bold; color: #fff;">${itemName}</td>
                <td class="extra-column" style="text-align: center; font-family: monospace; font-weight: bold; color: #cbd5e1;">${itemQty}</td>
                <td class="extra-column" style="text-align: left; font-family: monospace; color: #94a3b8;">${itemPrice}</td>
                <td class="date-col">${dueDate}</td>
                <td style="font-size: 0.85rem; color:#cbd5e1;">${pMethod}</td>
                <td class="amount-col" style="text-align:left;">${fmtCurrency(amt)}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function selectRow(index) {
    document.querySelectorAll('.main-row').forEach(r => r.classList.remove('selected'));
    const row = document.getElementById('row-' + index);
    if(row) row.classList.add('selected');
    
    selectedRowIndex = index;
    renderDetailPane(index);
}

function hideDetailPane() {
    const master = document.querySelector('.master-pane');
    const detail = document.querySelector('.detail-pane');
    if (master) master.classList.remove('shrunk');
    if (detail) detail.classList.remove('expanded');
    
    document.getElementById('empty-detail').style.display = 'flex';
    document.getElementById('detail-content').style.display = 'none';
}

function renderDetailPane(index) {
    const item = filteredData[index];
    if (!item) return;

    // Expand/Shrink Panels with transition animation
    const master = document.querySelector('.master-pane');
    const detail = document.querySelector('.detail-pane');
    if (master) master.classList.add('shrunk');
    if (detail) detail.classList.add('expanded');

    document.getElementById('empty-detail').style.display = 'none';
    const detailContent = document.getElementById('detail-content');
    
    // Quick re-trigger of animation
    detailContent.style.display = 'none';
    setTimeout(() => { detailContent.style.display = 'flex'; }, 10);

    const { localAmt, foreignAmt, currency } = getTransactionAmounts(item);
    const amt = localAmt;
    const ref = item.id || item.invoiceNo || item.voucherNo || '-';
    const dateStr = !isNaN(item._date.getTime()) ? item._date.toLocaleDateString('en-GB') : '-';
    const dueDate = item.dueDate ? new Date(item.dueDate).toLocaleDateString('en-GB') : '-';
    const pMethod = paymentMethodMap[item.paymentMethod] || item.paymentMethod || 'نقدي';
    let customerId = item.customerId || item.supplierId || item.customer || item.supplier || '';
    if (!customerId) {
        if (item._metaKey === 'erp_single_receipts_v1') {
            customerId = item.fromAccount || '';
        } else if (item._metaKey === 'erp_single_payments_v1') {
            customerId = item.toAccount || '';
        } else if (item.items && item.items.length > 0) {
            customerId = item.items[0].account || item.items[0].accountId || '';
        }
    }
    
    let customer = getAccName(customerId, true);
    if (!customer) customer = getAccName(item.customerName || item.supplierName, true);
    if (!customer) customer = item.customerName || item.supplierName || item.description || 'بدون اسم';

    const labelEntity = item._typeEn === 'sales' ? 'العميل' : (item._typeEn === 'purchases' ? 'المورد' : 'الجهة');

    // Build Header & Metadata Info Card inside a single unified card container
    let html = `
        <div class="unified-details-card">
            <div class="detail-header">
                <div class="detail-header-top">
                    <span class="type-badge ${item._typeClass}">${item._typeLabel}</span>
                    <button class="btn-action close-details-btn" onclick="resetSelection()">✖️ إغلاق التفاصيل</button>
                </div>
                
                <!-- Unified Horizontal Metadata Bar (Reference, Date, Entity, Payment Method) -->
                <div class="detail-meta-bar">
                    <div><span class="meta-label">المرجع:</span><strong class="meta-ref">#${ref}</strong></div>
                    <div><span class="meta-label">التاريخ:</span><strong class="meta-val">${dateStr}</strong></div>
                    <div><span class="meta-label">${labelEntity}:</span><strong class="meta-val">${customer}</strong></div>
                    <div><span class="meta-label">الدفع:</span><strong class="meta-pay">${pMethod}</strong></div>
                </div>

                <!-- Description / Statement line -->
                ${item.description || item.notes || item.statement ? `
                    <div class="detail-desc-box">
                        <span class="desc-label">البيان/الوصف:</span>
                        ${item.description || item.notes || item.statement}
                    </div>
                ` : ''}
            </div>
    `;

    // 0. WhatsApp Inbound Receipt Review & Target Bank Selection Card
    if (isWhatsAppReceiptItem(item)) {
        const waImg = item.receiptImage || (item.items && item.items[0] && item.items[0].receiptImage) || '';
        const waTransferNo = item.transferNo || (item.items && item.items[0] && item.items[0].transferNo) || ref;
        const currentBankId = item.bankAccountId || item.toAccount || item.mainAccount || 'BANK-BOK-001';
        const currentBankName = item.bankName || (item.items && item.items[0] && item.items[0].bankName) || getAccName(currentBankId) || 'بنك الخرطوم (بنكك)';
        const availableBanks = (Array.isArray(banksList) && banksList.length > 0)
            ? banksList
            : [{ id: 'BANK-BOK-001', name: 'بنك الخرطوم (بنكك)' }, { id: 'BANK-FAISAL', name: 'بنك فيصل الإسلامي (فوري)' }, { id: 'BANK-OMD', name: 'بنك أم درمان الوطني (أوكاش)' }];

        const bankOptionsHtml = availableBanks.map(b => {
            const bId = String(b.id || b.name);
            const bName = String(b.name || b.id);
            const isSel = (bId === String(currentBankId) || bName === String(currentBankName)) ? 'selected' : '';
            return `<option value="${bId}" style="background:#0f172a; color:#fff;" ${isSel}>🏦 ${bName}</option>`;
        }).join('');

        html += `
            <div style="background:linear-gradient(145deg, rgba(16,185,129,0.12), rgba(15,23,42,0.85)); border:1px solid rgba(16,185,129,0.4); border-radius:14px; padding:14px; margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:1.3rem;">📲</span>
                        <div>
                            <div style="font-size:0.95rem; font-weight:900; color:#34d399;">مراجعة وتمرير إشعار واتساب وارد</div>
                            <div style="font-size:0.78rem; color:#94a3b8;">رقم العملية البنكية: <strong style="color:#38bdf8; font-family:monospace;">#${waTransferNo}</strong> ${item.senderPhone ? `| هاتف المرسل: <span style="font-family:monospace; color:#cbd5e1;">${item.senderPhone}</span>` : ''}</div>
                        </div>
                    </div>
                    ${item.inboundReceiptId ? `
                        <button type="button" onclick="if(window.InboundBankakManager){InboundBankakManager.checkForgeryOnDemand(${Number(item.inboundReceiptId)}, this);}" style="height:30px; padding:0 12px; border-radius:8px; background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.45); color:#fbbf24; font-family:'Tajawal',sans-serif; font-size:0.78rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                            <span>🕵️</span> <span>فحص التزوير الجنائي</span>
                        </button>
                    ` : ''}
                </div>

                <div style="display:grid; grid-template-columns:${waImg ? '110px 1fr' : '1fr'}; gap:12px; align-items:center;">
                    ${waImg ? `
                        <div onclick="if(window.InboundBankakManager){InboundBankakManager.previewReceiptImage('${waImg}', 'إشعار #${waTransferNo}', '${amt}', '${customer}', ${Number(item.inboundReceiptId || 0)});}else{window.open('${waImg}','_blank');}" style="cursor:pointer; position:relative; border-radius:10px; overflow:hidden; border:2px solid rgba(56,189,248,0.45); height:110px; background:#020617;" title="اضغط لتكبير صورة الإشعار">
                            <img src="${waImg}" style="width:100%; height:100%; object-fit:cover;">
                            <div style="position:absolute; bottom:0; inset-inline:0; background:rgba(2,6,23,0.82); color:#38bdf8; font-size:0.7rem; font-weight:800; text-align:center; padding:2px 0;">🔍 تكبير الإشعار</div>
                        </div>
                    ` : ''}
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <label style="font-size:0.82rem; font-weight:900; color:#e2e8f0; display:flex; align-items:center; gap:6px;">
                            <span>🏦</span> <span>تحديد الحساب البنكي المستلم لهذا الإشعار:</span>
                        </label>
                        <select id="posting-wa-bank-select" onchange="onChangeWhatsAppTargetBank(${index}, this.value)" style="width:100%; height:38px; border-radius:10px; background:rgba(15,23,42,0.95); border:1px solid rgba(56,189,248,0.5); color:#38bdf8; font-family:'Tajawal',sans-serif; font-size:0.88rem; font-weight:900; padding:0 10px; cursor:pointer; outline:none;">
                            ${bankOptionsHtml}
                        </select>
                        <div style="font-size:0.75rem; color:#94a3b8;">
                            💡 عند اعتماد وترحيل هذا الإشعار، سينزل المبلغ (<strong style="color:#10b981;">${fmtCurrency(amt)} SDG</strong>) في حساب العميل (<strong style="color:#fff;">${customer}</strong>) وفي حساب البنك المختار أعلاه مباشرةً.
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // 1. Items List (For Invoices/Sales/Purchases)
    if (item.items && item.items.length > 0 && !isWhatsAppReceiptItem(item)) {
        html += `
            <div class="detail-section-items">
                <div class="section-title"><span>📦</span> تفاصيل الأصناف والفاتورة</div>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th style="text-align:right;">الصنف</th>
                            <th style="width:60px; text-align:center;">الكمية</th>
                            <th style="width:90px; text-align:left;">سعر الوحدة</th>
                            <th style="width:110px; text-align:left;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${item.items.map(i => {
                            const name = i.itemName || i.name || getItemName(i.itemId || i.itemCode || i.item || i.account);
                            const qty = parseFloat(i.qty || i.quantity || 0);
                            const price = parseFloat(i.price || i.rate || i.unitPrice || 0);
                            const total = parseFloat(i.total || i.amount || i.subtotal || (qty * price));
                            return `
                                <tr>
                                    <td class="item-name">${name}</td>
                                    <td class="item-qty">${qty}</td>
                                    <td class="item-price">${fmtCurrency(price)}</td>
                                    <td class="item-total"><span>${fmtCurrency(total)}</span></td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
                <div class="detail-total-bar">
                    <span class="total-lbl">إجمالي قيمة الفاتورة:</span>
                    <div class="total-val-box">
                        ${foreignAmt > 0 ? `<span class="val-foreign">${fmtCurrency(foreignAmt)} <span class="cur-lbl">${currency}</span></span>` : ''}
                        <span class="val-local">${fmtCurrency(amt)} <span class="cur-lbl">SDG</span></span>
                    </div>
                </div>
            </div>
        `;
    }

    // 2. Receipt or Payment Voucher Details (اسم الدائن واسم المدين والمبلغ)
    let drAccount = '';
    let crAccount = '';
    let isVoucher = false;

    if (item._metaKey === 'erp_single_payments_v1' || item._metaKey === 'erp_payments_v1') {
        isVoucher = true;
        drAccount = item.toAccount;
        crAccount = item.fromAccount || 'safe-001';
    } else if (item._metaKey === 'erp_single_receipts_v1' || item._metaKey === 'erp_receipts_v1') {
        isVoucher = true;
        drAccount = item.bankName || item.toAccount || 'safe-001';
        crAccount = item.fromAccount || (item.items && item.items[0] && (item.items[0].accountName || item.items[0].account)) || customer;
    }

    if (isVoucher) {
        html += `
            <div class="detail-section-voucher">
                <div class="section-title"><span>💸</span> أطراف المعاملة المالية</div>
                
                <div class="voucher-flow-wrapper">
                    <!-- Debtor (المدين - الآخذ) -->
                    <div class="detail-debtor-row">
                        <span class="row-title">
                            <span class="sign-plus">+</span>
                            المدين (الآخذ):
                        </span>
                        <span class="row-val">${getAccName(drAccount)}</span>
                    </div>

                    <!-- Creditor (الدائن - المعطي) -->
                    <div class="detail-creditor-row">
                        <span class="row-title">
                            <span class="sign-minus">-</span>
                            الدائن (المعطي):
                        </span>
                        <span class="row-val">${getAccName(crAccount)}</span>
                    </div>
                    
                    <!-- Amount (المبلغ) -->
                    <div class="detail-net-amount-row">
                        <span class="row-title">المبلغ الصافي:</span>
                        <div class="total-val-box">
                            ${foreignAmt > 0 ? `<span class="val-foreign">${fmtCurrency(foreignAmt)} <span class="cur-lbl">${currency}</span></span>` : ''}
                            <span class="val-net">${fmtCurrency(amt)} <span class="cur-lbl">SDG</span></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // 3. Expected Journal Entries (التوجيه المحاسبي التلقائي)
    const entries = getExpectedEntries(item, amt);
    if (entries && entries.length > 0) {
        html += `
            <div class="detail-section-journal">
                <div class="section-title"><span>📊</span> التوجيه المحاسبي التلقائي (قيد الاستحقاق/السداد)</div>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th style="width:110px;">مدين (+)</th>
                            <th style="width:110px;">دائن (-)</th>
                            <th>اسم الحساب</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${entries.map(e => `
                            <tr>
                                <td><span class="${e.debit > 0 ? 'dr-text' : 'cr-inactive'}">${e.debit > 0 ? fmtCurrency(e.debit) : '-'}</span></td>
                                <td><span class="${e.credit > 0 ? 'cr-text' : 'cr-inactive'}">${e.credit > 0 ? fmtCurrency(e.credit) : '-'}</span></td>
                                <td class="acc-name">${getAccName(e.acc)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    // Close the unified details card wrapper
    html += `</div>`;

    // Bottom Action Button
    if (currentStatus === 'unposted') {
        html += `
            <div style="margin-top: auto; padding-top: 20px;">
                <button class="btn-post-single" onclick="postSingleRow(${index})">
                    <span>✔️</span> اعتماد وترحيل هذه العملية بالكامل
                </button>
            </div>
        `;
    } else {
        html += `
            <div style="margin-top: auto; padding-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                <div style="text-align: center; color: var(--success); font-weight: 900; font-size: 1.1rem; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); padding: 12px; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 15px rgba(16,185,129,0.1);">
                    <span>✅</span> هذه العملية معتمدة ومرحلة مسبقاً
                </div>
                <button class="btn-action" onclick="unpostSingleRow(${index})" style="width: 100%; padding: 12px; font-size: 1rem; font-weight: 900; border-radius: 12px; background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(239,68,68,0.25); transition: 0.2s; font-family:'Tajawal', sans-serif;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span>🔄</span> إلغاء اعتماد وترحيل العملية (إرجاع لمعلقة)
                </button>
            </div>
        `;
    }

    detailContent.innerHTML = html;
}

async function onChangeWhatsAppTargetBank(index, bankId) {
    const item = filteredData[index];
    if (!item || !bankId) return;
    const matchedBank = (Array.isArray(banksList) && banksList.find(b => String(b.id) === String(bankId) || String(b.name) === String(bankId))) || { id: bankId, name: bankId };
    const bankName = matchedBank.name || bankId;
    const bankAccId = matchedBank.id || bankId;

    item.bankAccountId = bankAccId;
    item.bankName = bankName;
    item.toAccount = bankAccId;
    item.mainAccount = bankAccId;
    if (Array.isArray(item.items)) {
        item.items.forEach(sub => {
            sub.bankName = bankName;
            sub.bankAccountId = bankAccId;
            if (Array.isArray(sub.bankTransfers)) {
                sub.bankTransfers.forEach(bt => {
                    bt.bankName = bankName;
                    bt.bankAccountId = bankAccId;
                });
            }
        });
    }
    accountNamesMap.set(String(bankAccId).toLowerCase(), bankName);
    accountNamesMap.set(String(bankName).toLowerCase(), bankName);

    try {
        let localReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1') || '[]');
        if (Array.isArray(localReceipts)) {
            const refId = item.id || item.voucherNo;
            const rIdx = localReceipts.findIndex(x => x && (x.id === refId || x.voucherNo === refId));
            if (rIdx >= 0) {
                localReceipts[rIdx] = { ...localReceipts[rIdx], bankAccountId: bankAccId, bankName: bankName, toAccount: bankAccId, mainAccount: bankAccId, items: item.items };
                localStorage.setItem('erp_receipts_v1', JSON.stringify(localReceipts));
            }
        }
    } catch (e) {}

    if (item.inboundReceiptId) {
        try {
            const tId = localStorage.getItem('magaies_active_tenant') || 'default';
            await fetch('api/inbound_receipts.php?action=change_target_bank&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'change_target_bank',
                    tenant_id: tId,
                    id: item.inboundReceiptId,
                    bank_id: bankAccId,
                    bank_name: bankName,
                    post_now: false
                })
            });
        } catch (e) {}
    }

    renderMasterTable();
    renderDetailPane(index);
    showToast(`🏦 تم توجيه الإشعار إلى بنك (${bankName}) بنجاح`, 'success');
}

// Logic for calculating debits/credits based on meta
function getExpectedEntries(item, amt) {
    let expectedEntries = [];
    const parsedAmt = parseFloat(String(amt).replace(/,/g, '')) || 0;

    if (item._metaKey === 'erp_journal_vouchers_v1' && item.entries) {
        item.entries.forEach(e => {
            expectedEntries.push({ 
                acc: e.account || '-', 
                debit: parseFloat(String(e.debit).replace(/,/g,'')) || 0, 
                credit: parseFloat(String(e.credit).replace(/,/g,'')) || 0, 
                stmt: e.statement || '-' 
            });
        });
        return expectedEntries;
    }

    if (item._metaKey === 'erp_payments_v1') {
        if (item.items && Array.isArray(item.items) && item.items.length > 0) {
            item.items.forEach(sub => {
                const amt = parseFloat(sub.amount) || 0;
                // Debit: Recipient
                expectedEntries.push({ acc: sub.account, debit: amt, credit: 0, stmt: sub.statement || item.description || 'صرف فرعي' });
                
                // Credit: Payer (Safe/Bank/Cheque)
                const pms = sub.paymentMethods || [sub.paymentMethod || 'cash'];
                let hasHybrid = false;
                
                if (pms.includes('cash') && parseFloat(sub.cashAmount) > 0) {
                    hasHybrid = true;
                    expectedEntries.push({ acc: sub.cashSafe || item.mainAccount || item.fromAccount || 'safe-001', debit: 0, credit: parseFloat(sub.cashAmount), stmt: sub.cashStatement || sub.statement || item.description || 'صرف نقدي' });
                }
                if (pms.includes('bank') && parseFloat(sub.bankAmount) > 0) {
                    hasHybrid = true;
                    const transfers = (sub.bankTransfers && sub.bankTransfers.length > 0) ? sub.bankTransfers : [{ amount: sub.bankAmount, bankName: sub.bankName }];
                    transfers.forEach(bt => {
                        expectedEntries.push({ acc: bt.bankName || item.mainAccount || item.fromAccount || 'safe-001', debit: 0, credit: parseFloat(bt.amount) || 0, stmt: bt.statement || sub.statement || item.description || 'تحويل بنكي' });
                    });
                }
                if (pms.includes('cheque') && parseFloat(sub.chequeAmount) > 0) {
                    hasHybrid = true;
                    const cheques = (sub.cheques && sub.cheques.length > 0) ? sub.cheques : [{ amount: sub.chequeAmount, chequeBank: sub.chequeBank, chequeNo: sub.chequeNo }];
                    cheques.forEach(ch => {
                        expectedEntries.push({ acc: ch.chequeBank || 'cheques-receivable', debit: 0, credit: parseFloat(ch.amount) || 0, stmt: ch.statement || `صرف شيك رقم ${ch.chequeNo || ''}` });
                    });
                }
                
                if (!hasHybrid) {
                    expectedEntries.push({ acc: item.mainAccount || item.fromAccount || 'safe-001', debit: 0, credit: amt, stmt: sub.statement || item.description || 'صرف مجمع' });
                }
            });
        } else {
            // Fallback for empty items
            expectedEntries.push({ acc: item.mainAccount || item.fromAccount || 'safe-001', debit: 0, credit: parsedAmt, stmt: item.description || 'سند صرف مجمع' });
            expectedEntries.push({ acc: 'حسابات متعددة', debit: parsedAmt, credit: 0, stmt: item.description || 'سند صرف مجمع' });
        }
        return expectedEntries;
    } else if (item._metaKey === 'erp_receipts_v1') {
        if (item.items && Array.isArray(item.items) && item.items.length > 0) {
            item.items.forEach(sub => {
                const amt = parseFloat(sub.amount) || 0;
                // Credit: Payer
                expectedEntries.push({ acc: sub.account, debit: 0, credit: amt, stmt: sub.statement || item.description || 'قبض فرعي' });
                
                // Debit: Receiver (Safe/Bank/Cheque)
                const pms = sub.paymentMethods || [sub.paymentMethod || 'cash'];
                let hasHybrid = false;
                
                if (pms.includes('cash') && parseFloat(sub.cashAmount) > 0) {
                    hasHybrid = true;
                    expectedEntries.push({ acc: sub.cashSafe || item.mainAccount || item.toAccount || 'safe-001', debit: parseFloat(sub.cashAmount), credit: 0, stmt: sub.cashStatement || sub.statement || item.description || 'قبض نقدي' });
                }
                if (pms.includes('bank') && parseFloat(sub.bankAmount) > 0) {
                    hasHybrid = true;
                    const transfers = (sub.bankTransfers && sub.bankTransfers.length > 0) ? sub.bankTransfers : [{ amount: sub.bankAmount, bankName: sub.bankName }];
                    transfers.forEach(bt => {
                        expectedEntries.push({ acc: bt.bankName || item.mainAccount || item.toAccount || 'safe-001', debit: parseFloat(bt.amount) || 0, credit: 0, stmt: bt.statement || sub.statement || item.description || 'تحويل بنكي' });
                    });
                }
                if (pms.includes('cheque') && parseFloat(sub.chequeAmount) > 0) {
                    hasHybrid = true;
                    const cheques = (sub.cheques && sub.cheques.length > 0) ? sub.cheques : [{ amount: sub.chequeAmount, chequeBank: sub.chequeBank, chequeNo: sub.chequeNo }];
                    cheques.forEach(ch => {
                        expectedEntries.push({ acc: ch.chequeBank || 'cheques-receivable', debit: parseFloat(ch.amount) || 0, credit: 0, stmt: ch.statement || `قبض شيك رقم ${ch.chequeNo || ''}` });
                    });
                }
                
                if (!hasHybrid) {
                    expectedEntries.push({ acc: item.mainAccount || item.toAccount || 'safe-001', debit: amt, credit: 0, stmt: sub.statement || item.description || 'قبض مجمع' });
                }
            });
        } else {
            // Fallback for empty items
            expectedEntries.push({ acc: item.mainAccount || item.toAccount || 'safe-001', debit: parsedAmt, credit: 0, stmt: item.description || 'سند قبض مجمع' });
            expectedEntries.push({ acc: 'حسابات متعددة', debit: 0, credit: parsedAmt, stmt: item.description || 'سند قبض مجمع' });
        }
        return expectedEntries;
    } else if (item._metaKey === 'erp_single_payments_v1') {
        expectedEntries.push({ acc: item.toAccount, debit: parsedAmt, credit: 0, stmt: 'سند صرف' });
        expectedEntries.push({ acc: item.fromAccount || 'safe-001', debit: 0, credit: parsedAmt, stmt: 'سند صرف' });
    } else if (item._metaKey === 'erp_single_receipts_v1') {
        expectedEntries.push({ acc: item.toAccount || 'safe-001', debit: parsedAmt, credit: 0, stmt: 'سند قبض' });
        expectedEntries.push({ acc: item.fromAccount, debit: 0, credit: parsedAmt, stmt: 'سند قبض' });
    } else if (item._metaKey === 'erp_sales_invoices_v1') {
        expectedEntries.push({ acc: item.customerId || item.customer || 'عملاء نقدي', debit: parsedAmt, credit: 0, stmt: 'مبيعات' });
        expectedEntries.push({ acc: 'revenue-sales', debit: 0, credit: parsedAmt, stmt: 'إيرادات' });
        if (item.paymentMethod === 'hybrid' || item.paymentMethod === 'multipay') {
            // Post cash portion if any
            if (item.cashAmount > 0) {
                expectedEntries.push({ acc: item.paymentAccountId || 'safe-001', debit: parseFloat(item.cashAmount), credit: 0, stmt: 'سداد نقدي مع الفاتورة' });
                expectedEntries.push({ acc: item.customerId || item.customer || 'عملاء نقدي', debit: 0, credit: parseFloat(item.cashAmount), stmt: 'سداد نقدي مع الفاتورة' });
            }
            // Post bank transfers if any
            if (item.bankTransfers && item.bankTransfers.length > 0) {
                item.bankTransfers.forEach(bt => {
                    expectedEntries.push({ acc: bt.bankAccountId || 'safe-001', debit: parseFloat(bt.amount), credit: 0, stmt: 'سداد تحويل بنكي' });
                    expectedEntries.push({ acc: item.customerId || item.customer || 'عملاء نقدي', debit: 0, credit: parseFloat(bt.amount), stmt: 'سداد تحويل بنكي' });
                });
            }
            // Post cheques if any
            if (item.cheques && item.cheques.length > 0) {
                item.cheques.forEach(ch => {
                    expectedEntries.push({ acc: 'cheques-receivable', debit: parseFloat(ch.amount), credit: 0, stmt: `شيك رقم ${ch.chequeNo}` });
                    expectedEntries.push({ acc: item.customerId || item.customer || 'عملاء نقدي', debit: 0, credit: parseFloat(ch.amount), stmt: `شيك رقم ${ch.chequeNo}` });
                });
            }
        } else if (item.paymentMethod !== 'credit') {
            expectedEntries.push({ acc: item.paymentAccountId || 'safe-001', debit: parsedAmt, credit: 0, stmt: 'سداد' });
            expectedEntries.push({ acc: item.customerId || item.customer || 'عملاء نقدي', debit: 0, credit: parsedAmt, stmt: 'سداد' });
        }
    } else if (item._metaKey === 'erp_purchase_invoices_v1') {
        const suppAcc = item.supplierId || item.supplier || item.customer || 'موردين نقدي';
        expectedEntries.push({ acc: 'exp-purchases', debit: parsedAmt, credit: 0, stmt: 'مشتريات' });
        expectedEntries.push({ acc: suppAcc, debit: 0, credit: parsedAmt, stmt: 'مستحقات' });
        
        if (item.paymentMethod === 'hybrid' || item.paymentMethod === 'multipay') {
            // Post cash portion if any
            if (item.cashAmount > 0) {
                expectedEntries.push({ acc: suppAcc, debit: parseFloat(item.cashAmount), credit: 0, stmt: 'سداد نقدي مع الفاتورة' });
                expectedEntries.push({ acc: item.paymentAccountId || 'safe-001', debit: 0, credit: parseFloat(item.cashAmount), stmt: 'سداد نقدي مع الفاتورة' });
            }
            // Post bank transfers if any
            if (item.bankTransfers && item.bankTransfers.length > 0) {
                item.bankTransfers.forEach(bt => {
                    expectedEntries.push({ acc: suppAcc, debit: parseFloat(bt.amount), credit: 0, stmt: 'سداد تحويل بنكي' });
                    expectedEntries.push({ acc: bt.bankAccountId || 'safe-001', debit: 0, credit: parseFloat(bt.amount), stmt: 'سداد تحويل بنكي' });
                });
            }
            // Post cheques if any
            if (item.cheques && item.cheques.length > 0) {
                item.cheques.forEach(ch => {
                    expectedEntries.push({ acc: suppAcc, debit: parseFloat(ch.amount), credit: 0, stmt: `شيك صادرة رقم ${ch.chequeNo}` });
                    expectedEntries.push({ acc: 'cheques-payable', debit: 0, credit: parseFloat(ch.amount), stmt: `شيك صادرة رقم ${ch.chequeNo}` });
                });
            }
        } else if (item.paymentMethod !== 'credit') {
            expectedEntries.push({ acc: suppAcc, debit: parsedAmt, credit: 0, stmt: 'سداد' });
            expectedEntries.push({ acc: item.paymentAccountId || 'safe-001', debit: 0, credit: parsedAmt, stmt: 'سداد' });
        }
    }
    return expectedEntries;
}

function onRowCheck(event) {
    updateStickyBar();
}

function toggleAllRows(source) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => { cb.checked = source.checked; });
    updateStickyBar();
}

function updateStickyBar() {
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
    const bar = document.getElementById('sticky-bar');
    document.getElementById('selected-count').innerText = checkedCount;
    if (checkedCount > 0) {
        bar.classList.add('show');
    } else {
        bar.classList.remove('show');
    }
}

async function saveItemPosted(item) {
    try {
        if (isWhatsAppReceiptItem(item) && item.inboundReceiptId) {
            try {
                const tId = localStorage.getItem('magaies_active_tenant') || 'default';
                const bankId = item.bankAccountId || item.toAccount || '';
                const res = await fetch(`api/inbound_receipts.php?action=post_to_accounts&id=${encodeURIComponent(item.inboundReceiptId)}&force_post=1&bank_id=${encodeURIComponent(bankId)}&tenant_id=${encodeURIComponent(tId)}`);
                const bData = await res.json();
                if (bData && bData.success && window.InboundBankakManager && typeof window.InboundBankakManager.syncPostedReceiptToLocalErp === 'function') {
                    window.InboundBankakManager.syncPostedReceiptToLocalErp({
                        id: item.inboundReceiptId,
                        voucher_no: bData.voucher_no || item.voucherNo,
                        amount: item.totalAmount || item.amount,
                        transfer_no: item.transferNo || '',
                        receipt_image: item.receiptImage || '',
                        status: 'verified',
                        issue_reason: null
                    }, bData);
                }
            } catch (waErr) {
                console.warn('Backend WhatsApp receipt post sync warning:', waErr);
            }
        }

        const tKey = item._sourceKey;
        let data = [];
        if (typeof localforage !== 'undefined') {
            data = await localforage.getItem(tKey);
        }
        if (!data) {
            data = localStorage.getItem(tKey) || localStorage.getItem(item._metaKey) || '[]';
        }

        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch(e) {
                data = [];
            }
        }

        const isObj = data && typeof data === 'object' && !Array.isArray(data);
        const refId = item.id || item.invoiceNo || item.voucherNo;

        if (isObj) {
            if (data[refId]) data[refId].isPosted = true;
        } else if (Array.isArray(data)) {
            const idx = data.findIndex(x => (x.id || x.invoiceNo || x.voucherNo) === refId);
            if (idx > -1) data[idx].isPosted = true;
        }

        // Save using standard overridden localStorage.setItem
        localStorage.setItem(item._metaKey, JSON.stringify(data));
        return true;
    } catch(e) {
        console.error('Save error', e);
        return false;
    }
}

let confirmModalResolve = null;

function showConfirmModal(title, msg, detailsHtml, confirmBtnText = 'تأكيد واعتماد الترحيل ⚡', isUnposting = false) {
    return new Promise((resolve) => {
        confirmModalResolve = resolve;
        
        document.getElementById('modal-title').innerText = title;
        document.getElementById('modal-body').innerHTML = `
            <p style="margin: 0 0 10px 0; font-size: 1.05rem; font-weight: bold; color: #fff;">${msg}</p>
            ${detailsHtml ? `<div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.06); padding: 12px; border-radius: 10px; font-size: 0.95rem; margin-top: 10px; border-right: 3px solid ${isUnposting ? '#ef4444' : 'var(--primary-accent)'};">${detailsHtml}</div>` : ''}
        `;
        
        const confirmBtn = document.getElementById('modal-btn-confirm');
        confirmBtn.innerText = confirmBtnText;
        confirmBtn.style.display = 'block';
        
        if (isUnposting) {
            confirmBtn.style.background = 'linear-gradient(135deg, #ef4444, #b91c1c)';
            confirmBtn.style.boxShadow = '0 4px 15px rgba(239,68,68,0.4)';
        } else {
            confirmBtn.style.background = 'linear-gradient(135deg, var(--primary-accent), #2563eb)';
            confirmBtn.style.boxShadow = '0 4px 15px rgba(59,130,246,0.4)';
        }
        
        // Show modal actions & close button
        document.getElementById('modal-actions').style.display = 'flex';
        document.querySelector('.modal-close').style.display = 'block';
        
        document.getElementById('custom-confirm-modal').classList.add('show');
        
        confirmBtn.onclick = function() {
            startPostingAnimation(isUnposting);
        };
    });
}

function closeConfirmModal() {
    document.getElementById('custom-confirm-modal').classList.remove('show');
    if (confirmModalResolve) {
        confirmModalResolve(false);
        confirmModalResolve = null;
    }
}

function startPostingAnimation(isUnposting = false) {
    // Hide actions & close button to make it non-interruptible
    document.getElementById('modal-actions').style.display = 'none';
    document.querySelector('.modal-close').style.display = 'none';
    
    // Set posting layout
    document.getElementById('modal-body').innerHTML = `
        <div class="posting-anim-container">
            <div class="posting-scene ${isUnposting ? 'unposting' : ''}">
                <div class="scene-doc">📄</div>
                <div class="scene-doc-2">📊</div>
                <div class="scene-ledger">🗄️</div>
            </div>
            <div class="posting-step-txt" id="posting-step-txt">${isUnposting ? 'جاري إلغاء ترحيل القيود...' : 'جاري تجميع البيانات والتحقق منها...'}</div>
            <div class="posting-progress-bar">
                <div class="posting-progress-fill" id="posting-progress-fill"></div>
            </div>
        </div>
    `;
    
    const steps = isUnposting ? [
        { progress: 30, txt: 'جاري مراجعة وإلغاء قيود الأستاذ العام...' },
        { progress: 65, txt: 'جاري إبطال السندات والترحيلات التلقائية...' },
        { progress: 100, txt: 'اكتمل إلغاء الترحيل وإعادة المعاملة كمعلقة! 🔄' }
    ] : [
        { progress: 25, txt: 'جاري تجميع البيانات والتحقق منها...' },
        { progress: 55, txt: 'جاري توليد القيود المحاسبية التلقائية...' },
        { progress: 80, txt: 'جاري ترحيل وتحديث أرصدة الأستاذ العام...' },
        { progress: 100, txt: 'اكتمل ترحيل واعتماد العملية بنجاح! 🎉' }
    ];
    
    let currentStep = 0;
    
    function runNextStep() {
        if (currentStep >= steps.length) {
            setTimeout(() => {
                const resolveRef = confirmModalResolve;
                confirmModalResolve = null; // Clear first to prevent cancelling
                document.getElementById('custom-confirm-modal').classList.remove('show');
                if (resolveRef) {
                    resolveRef(true);
                }
            }, 600);
            return;
        }
        
        const step = steps[currentStep];
        document.getElementById('posting-progress-fill').style.width = `${step.progress}%`;
        document.getElementById('posting-step-txt').innerText = step.txt;
        
        currentStep++;
        setTimeout(runNextStep, 600);
    }
    
    runNextStep();
}

async function postSingleRow(index) {
    if (!checkPostPermission()) {
        showToast('عفواً، ليس لديك صلاحية اعتماد أو ترحيل المعاملات.', 'error');
        return;
    }
    const item = filteredData[index];
    if (!item || item.isPosted) return;

    const amt = item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0;
    const ref = item.id || item.invoiceNo || item.voucherNo || '-';
    
    const details = `
        <div style="display: flex; flex-direction: column; gap: 6px; color: #cbd5e1;">
            <div><strong>نوع العملية:</strong> <span class="type-badge ${item._typeClass}" style="padding:2px 8px; font-size:0.8rem;">${item._typeLabel}</span></div>
            <div><strong>رقم المرجع:</strong> <span style="font-family: monospace; color:#38bdf8;">#${ref}</span></div>
            <div><strong>المبلغ الإجمالي:</strong> <span style="font-family: monospace; color:#10b981; font-weight:bold;">${fmtCurrency(amt)} SDG</span></div>
        </div>
    `;
    
    const confirmed = await showConfirmModal(
        'تأكيد ترحيل واعتماد المعاملة',
        'هل أنت متأكد من رغبتك في ترحيل هذه العملية واعتماد قيودها الدفترية؟',
        details
    );
    
    if (!confirmed) return;

    const ok = await saveItemPosted(item);
    if (ok) {
        item.isPosted = true;
        // Sync with master list to ensure it's filtered out
        const refId = item.id || item.invoiceNo || item.voucherNo;
        const matched = allTransactionsData.find(x => (x.id || x.invoiceNo || x.voucherNo) === refId && x._metaKey === item._metaKey);
        if (matched) matched.isPosted = true;

        showToast('تم اعتماد العملية وترحيلها بنجاح!', 'success');
        resetSelection();
        applyFiltersAndRender();
    } else {
        showToast('خطأ أثناء عملية الترحيل.', 'error');
    }
}

async function postAllChecked() {
    if (!checkPostPermission()) {
        showToast('عفواً، ليس لديك صلاحية اعتماد أو ترحيل المعاملات.', 'error');
        return;
    }
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkboxes.length === 0) return;

    const details = `
        <div style="font-weight: bold; color: var(--warning); display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span> سيتم تحديث حالة جميع العمليات المحددة وترحيل أرصدتها فورياً إلى الحسابات المعنية.
        </div>
    `;

    const confirmed = await showConfirmModal(
        'ترحيل واعتماد دفعة معاملات',
        `هل أنت متأكد من ترحيل واعتماد ${checkboxes.length} عمليات بالكامل دفعة واحدة؟`,
        details
    );
    
    if (!confirmed) return;

    const indices = Array.from(checkboxes).map(cb => parseInt(cb.value)).sort((a,b) => b - a);
    let successCount = 0;

    for (const idx of indices) {
        const item = filteredData[idx];
        if (item && !item.isPosted) {
            const ok = await saveItemPosted(item);
            if (ok) {
                item.isPosted = true;
                // Sync with master list to ensure it's filtered out
                const refId = item.id || item.invoiceNo || item.voucherNo;
                const matched = allTransactionsData.find(x => (x.id || x.invoiceNo || x.voucherNo) === refId && x._metaKey === item._metaKey);
                if (matched) matched.isPosted = true;
                successCount++;
            }
        }
    }

    resetSelection();
    applyFiltersAndRender();
    
    if (successCount > 0) {
        showToast(`تم ترحيل ${successCount} عمليات بنجاح!`, 'success');
    }
}

async function saveItemUnposted(item) {
    try {
        const tKey = item._sourceKey;
        let data = [];
        if (typeof localforage !== 'undefined') {
            data = await localforage.getItem(tKey);
        }
        if (!data) {
            data = localStorage.getItem(tKey) || localStorage.getItem(item._metaKey) || '[]';
        }

        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch(e) {
                data = [];
            }
        }

        const isObj = data && typeof data === 'object' && !Array.isArray(data);
        const refId = item.id || item.invoiceNo || item.voucherNo;

        if (isObj) {
            if (data[refId]) data[refId].isPosted = false;
        } else if (Array.isArray(data)) {
            const idx = data.findIndex(x => (x.id || x.invoiceNo || x.voucherNo) === refId);
            if (idx > -1) data[idx].isPosted = false;
        }

        // Save using standard overridden localStorage.setItem
        localStorage.setItem(item._metaKey, JSON.stringify(data));
        return true;
    } catch(e) {
        console.error('Unposting save error', e);
        return false;
    }
}

async function unpostSingleRow(index) {
    if (!checkPostPermission()) {
        showToast('عفواً، ليس لديك صلاحية إلغاء ترحيل أو اعتماد المعاملات.', 'error');
        return;
    }
    const item = filteredData[index];
    if (!item || !item.isPosted) return;

    const amt = item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0;
    const ref = item.id || item.invoiceNo || item.voucherNo || '-';
    
    const details = `
        <div style="display: flex; flex-direction: column; gap: 6px; color: #cbd5e1;">
            <div><strong>نوع العملية:</strong> <span class="type-badge ${item._typeClass}" style="padding:2px 8px; font-size:0.8rem;">${item._typeLabel}</span></div>
            <div><strong>رقم المرجع:</strong> <span style="font-family: monospace; color:#38bdf8;">#${ref}</span></div>
            <div><strong>المبلغ الإجمالي:</strong> <span style="font-family: monospace; color:#10b981; font-weight:bold;">${fmtCurrency(amt)} SDG</span></div>
        </div>
    `;
    
    const confirmed = await showConfirmModal(
        'إلغاء ترحيل واعتماد المعاملة',
        'هل أنت متأكد من رغبتك في إلغاء ترحيل هذه العملية وإعادتها كعملية معلقة؟ سيتم عكس قيودها الدفترية محاسبياً.',
        details,
        'تأكيد إلغاء الترحيل وإرجاعها معلقة ⚡',
        true
    );
    
    if (!confirmed) return;

    const ok = await saveItemUnposted(item);
    if (ok) {
        item.isPosted = false;
        // Sync with master list to ensure it's filtered out
        const refId = item.id || item.invoiceNo || item.voucherNo;
        const matched = allTransactionsData.find(x => (x.id || x.invoiceNo || x.voucherNo) === refId && x._metaKey === item._metaKey);
        if (matched) matched.isPosted = false;

        showToast('تم إلغاء الترحيل وإعادة المعاملة كمعلقة بنجاح!', 'warning');
        resetSelection();
        applyFiltersAndRender();
    } else {
        showToast('خطأ أثناء إلغاء الترحيل.', 'error');
    }
}

function showToast(msg, type='success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icons = { success:'✔️', error:'✖️', warning:'⚠️' };
    toast.innerHTML = `<span>${icons[type] || '🔔'}</span> <span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'toastIn 0.3s cubic-bezier(0.16,1,0.3,1) reverse both';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
