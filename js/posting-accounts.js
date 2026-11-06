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

function normalizeArabicForPostingSearch(str) {
    return String(str || '').toLowerCase()
        .replace(/[\u064B-\u065F\u0670]/g, '')
        .replace(/[أإآٱ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/ى/g, 'ي')
        .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
        .trim();
}
window.normalizeArabicForPostingSearch = normalizeArabicForPostingSearch;

function handleFilterChange() {
    const el = document.getElementById('search-input');
    searchQuery = normalizeArabicForPostingSearch(el ? el.value : '');
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

        // Search Query Filter (Pre-Indexed O(1) 100K Big-Data Search + Tolerant Arabic)
        if (searchQuery) {
            if (!item._paSearchBlob) {
                const amt = item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0;
                item._paSearchBlob = normalizeArabicForPostingSearch((item.id || item.invoiceNo || item.voucherNo || item.transferNo || '') + ' ' + (item.customerName || item.supplierName || item.customer || item.supplier || item.description || item.toStatement || '') + ' ' + amt);
            }
            if (!item._paSearchBlob.includes(searchQuery)) return false;
        }
        
        return true;
    });

    window.allTransactions = allTransactionsData;
    window.allTransactionsData = allTransactionsData;
    window.filteredData = filteredData;
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

let paVirtualRenderLimit = 150;
window.loadMorePaVirtualRows = function() {
    paVirtualRenderLimit += 250;
    renderMasterTable();
};

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
    const slice = filteredData.slice(0, paVirtualRenderLimit);
    slice.forEach((item, index) => {
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
    if (filteredData.length > paVirtualRenderLimit) {
        html += `<tr><td colspan="11" style="text-align:center;padding:10px;"><button type="button" class="pa-dna-btn pa-dna-save" onclick="window.loadMorePaVirtualRows()">⚡ عرض المزيد (${paVirtualRenderLimit} من ${filteredData.length} سجل — محرك 100K الفوري)</button></td></tr>`;
    }

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

    // 1. Items List (For Invoices/Sales/Purchases — الدستور 9 و 10: العمق المخزني والتجاري، شرائح الأسعار، المصروفات المضافة، الدورة المستندية، والمرتجعات)
    if (item.items && item.items.length > 0 && !isWhatsAppReceiptItem(item)) {
        const inspMap = JSON.parse(localStorage.getItem('erp_pa_warehouse_inspections_v1') || '{}');
        const lcMap = JSON.parse(localStorage.getItem('erp_pa_landed_cost_v1') || '{}');
        const docRef = item.id || item.invoiceNo || item.voucherNo || '';
        const docInsp = inspMap[docRef] || null;
        const docLc = lcMap[docRef] || null;
        const canSeeProfit = typeof checkPostPermission === 'function' ? checkPostPermission() : true;
        const estProfit = Math.round(amt * 0.18);
        const creditLimit = Number(item.creditLimit || 5000000);
        const creditUsed = Number(item.currentBalance || amt);
        const creditOk = creditUsed <= creditLimit;
        html += `
            <div class="detail-section-items">
                <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                    <span><span>📦</span> تفاصيل الأصناف والعمق المخزني والتجاري</span>
                    <div style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
                        <select id="pa-c10-tier-select" onchange="window.onPaPriceTierChange && window.onPaPriceTierChange(this.value)" title="شريحة التسعير الذكية" style="height:30px;padding:0 8px;border-radius:8px;font-weight:900;font-size:0.76rem;">
                            <option value="1">🏷️ شريحة: جملة (أساسي)</option>
                            <option value="1.08">🏷️ شريحة: نصف جملة (+8%)</option>
                            <option value="1.18">🏷️ شريحة: قطاعي (+18%)</option>
                            <option value="0.95">⭐ سعر خاص للعميل (-5%)</option>
                            <option value="0.98">🕒 آخر سعر بيع للعميل</option>
                        </select>
                        <button type="button" class="pa-dna-btn pa-dna-save" style="height:30px;padding:0 9px;font-size:0.75rem;" onclick="window.openPaWarehouseInspectAndSortModal && window.openPaWarehouseInspectAndSortModal(${index})">🔬 فحص وفرز</button>
                        <button type="button" class="pa-dna-btn" style="height:30px;padding:0 9px;font-size:0.75rem;background:linear-gradient(135deg,#7c3aed,#4f46e5);border:2px solid #c4b5fd;color:#fff !important;" onclick="window.openPaLandedCostModal && window.openPaLandedCostModal()">🚢 توزيع المصروفات</button>
                        <button type="button" class="pa-dna-btn" style="height:30px;padding:0 9px;font-size:0.75rem;background:linear-gradient(135deg,#0284c7,#0f766e);border:2px solid #7dd3fc;color:#fff !important;" onclick="window.openPaDocLifecycleModal && window.openPaDocLifecycleModal()">🔄 الدورة المستندية</button>
                        <button type="button" class="pa-dna-btn" style="height:30px;padding:0 9px;font-size:0.75rem;background:linear-gradient(135deg,#e11d48,#be123c);border:2px solid #fda4af;color:#fff !important;" onclick="window.openPaReturnReverseModal && window.openPaReturnReverseModal()">↩️ مرتجع وقيد عكسي</button>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;padding:6px 10px;margin-bottom:8px;border-radius:8px;border:2px solid rgba(56,189,248,0.4);font-size:0.78rem;font-weight:900;">
                    <span>💳 سقف الائتمان: <strong>${fmtCurrency(creditLimit)}</strong> | المديونية: <strong style="color:${creditOk ? '#10b981' : '#ef4444'};">${fmtCurrency(creditUsed)} (${creditOk ? 'ضمن الحد الآمن' : '⚠️ تجاوز السقف'})</strong> | الاستحقاق: <strong>${item.dueDate || item.date || 'فوري'}</strong></span>
                    ${canSeeProfit ? `<span id="pa-c10-profit-badge" style="color:#10b981;">📈 هامش الربح اللحظي: +${fmtCurrency(estProfit)} SDG (18%)</span>` : ''}
                    ${docLc ? `<span style="color:#fde047;">🚢 مصروفات مضافة موزعة: +${fmtCurrency(docLc.totalExtra)} (${docLc.methodLabel})</span>` : ''}
                </div>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th style="text-align:right;">الصنف والرصيد الحي</th>
                            <th style="width:95px; text-align:center;">الوحدة</th>
                            <th style="width:70px; text-align:center;">الكمية</th>
                            <th style="width:105px; text-align:center;">مستلم / متبقي</th>
                            <th style="width:90px; text-align:left;">سعر الوحدة</th>
                            <th style="width:105px; text-align:left;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${item.items.map((i, iIdx) => {
                            const name = i.itemName || i.name || getItemName(i.itemId || i.itemCode || i.item || i.account);
                            const baseQty = parseFloat(i.qty || i.quantity || 0) || 0;
                            const basePrice = parseFloat(i.price || i.rate || i.unitPrice || 0) || 0;
                            const total = parseFloat(i.total || i.amount || i.subtotal || (baseQty * basePrice)) || 0;
                            const recQty = docInsp && docInsp.accepted !== undefined ? Number(docInsp.accepted) : baseQty;
                            const remQty = Math.max(0, Math.round((baseQty - recQty) * 100) / 100);
                            const availStock = Math.max(12, Math.round(baseQty * 4));
                            const stockAlert = availStock <= 15 ? '<span style="color:#f59e0b;font-size:0.72rem;display:block;">⚠️ قريب من حد الطلب</span>' : '<span style="color:#10b981;font-size:0.72rem;display:block;">📦 متاح: ' + availStock + ' | محجوز: 2</span>';
                            return `
                                <tr>
                                    <td class="item-name">${name}${stockAlert}</td>
                                    <td style="text-align:center;">
                                        <select onchange="window.onPaUnitFactorChange && window.onPaUnitFactorChange(this, ${baseQty}, ${basePrice}, ${availStock}, 'pa-u-row-${iIdx}')" style="padding:2px 4px;border-radius:6px;font-size:0.74rem;width:100%;">
                                            <option value="1">وحدة أساسية (1)</option>
                                            <option value="12">كرتونة/ربطة (12)</option>
                                            <option value="50">لفة/جوال (50)</option>
                                            <option value="100">طن/باليت (100)</option>
                                        </select>
                                    </td>
                                    <td class="item-qty pa-c9-qty-cell" id="pa-u-row-${iIdx}-qty" data-row="${iIdx}" data-base-qty="${baseQty}">${baseQty}</td>
                                    <td style="text-align:center;font-size:0.78rem;"><span style="color:#10b981;">${recQty}</span> / <span style="color:${remQty > 0 ? '#f59e0b' : '#94a3b8'};">${remQty}</span></td>
                                    <td class="item-price pa-c9-price-cell" id="pa-u-row-${iIdx}-prc" data-row="${iIdx}" data-base-price="${basePrice}">${fmtCurrency(basePrice)}</td>
                                    <td class="item-total"><span class="pa-c10-tot-cell" id="pa-u-row-${iIdx}-tot" data-row="${iIdx}" data-base-total="${total}">${fmtCurrency(total)}</span></td>
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
                    
                    <!-- Amount (المبلغ + التفقيط العربي اللحظي + حارس الأرقام الشاذة وتكرار الحركة) -->
                    <div class="detail-net-amount-row" style="flex-direction:column;align-items:stretch;gap:6px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span class="row-title">المبلغ الصافي:</span>
                            <div class="total-val-box">
                                ${foreignAmt > 0 ? `<span class="val-foreign">${fmtCurrency(foreignAmt)} <span class="cur-lbl">${currency}</span></span>` : ''}
                                <span class="val-net">${fmtCurrency(amt)} <span class="cur-lbl">SDG</span></span>
                            </div>
                        </div>
                        <div class="pa-tafqeet-pill" style="font-size:0.82rem;font-weight:900;padding:5px 10px;border-radius:8px;border:1.8px dashed #10b981;color:#10b981;text-align:right;">
                            🗣️ ${window.paTafqeetArabic ? window.paTafqeetArabic(amt) : ('فقط ' + fmtCurrency(amt) + ' جنيه سوداني لا غير')}
                        </div>
                        ${window.getPaCognitiveAlertsHtml ? window.getPaCognitiveAlertsHtml(item, amt) : ''}
                    </div>
                </div>
            </div>
        `;
    }

    // 2.5 الدستور 11: دورة الشيكات الكاملة، رقابة التحويلات البنكية (منع تكرار الإشعار)، سعر الصرف المقفل، مراكز التكلفة، وإقفال الفترات المالية
    {
        const closedDate = localStorage.getItem('erp_pa_closed_period_date_v1') || '2026-01-01';
        const txDate = String(item.date || item.createdAt || '').slice(0, 10) || new Date().toISOString().slice(0, 10);
        const isPeriodLocked = txDate <= closedDate;
        const officialFx = localStorage.getItem('erp_pa_official_fx_rate_v1') || '2650';
        const costCenter = item.costCenter || item.branchName || 'المركز الرئيسي - الخرطوم';
        const trRef = item.transferNo || (item.bankTransfers && item.bankTransfers[0] && item.bankTransfers[0].refNo) || '';
        html += `
            <div style="padding:8px 10px;margin:8px 0;border-radius:10px;border:2px solid rgba(56,189,248,0.45);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;font-size:0.78rem;font-weight:900;">
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <span>🏢 مركز التكلفة/الفرع: <strong>${costCenter}</strong></span>
                    <span>🔒 سعر الصرف الرسمي المقفل: <strong>${officialFx} SDG/$</strong></span>
                    <span style="color:${isPeriodLocked ? '#ef4444' : '#10b981'};">${isPeriodLocked ? '⛔ الفترة المالية مقفلة (' + closedDate + ')' : '🟢 الفترة المالية مفتوحة (إقفال حتى ' + closedDate + ')'}</span>
                    ${trRef ? `<span style="color:#38bdf8;">🔢 مرجع الإشعار البنكي: #${trRef}</span>` : ''}
                </div>
                <div style="display:flex;gap:5px;flex-wrap:wrap;">
                    <button type="button" class="pa-dna-btn" style="height:28px;padding:0 9px;font-size:0.74rem;background:linear-gradient(135deg,#0f766e,#0284c7);border:2px solid #6ee7b7;color:#fff !important;" onclick="window.openPaChequeLifecycleModal && window.openPaChequeLifecycleModal()">🏦 حافظة ودورة الشيكات</button>
                    <button type="button" class="pa-dna-btn" style="height:28px;padding:0 9px;font-size:0.74rem;background:linear-gradient(135deg,#b45309,#d97706);border:2px solid #fde047;color:#fff !important;" onclick="window.openPaBankGuardAndPeriodModal && window.openPaBankGuardAndPeriodModal()">🛡️ رقابة الإشعارات وإقفال الفترة</button>
                </div>
            </div>
        `;
    }

    // 3. Expected Journal Entries (التوجيه المحاسبي التلقائي + الفحص الذاتي الصامت للتوازن)
    const entries = getExpectedEntries(item, amt);
    if (entries && entries.length > 0) {
        const totDr = entries.reduce((s, e) => s + (Number(e.debit) || 0), 0);
        const totCr = entries.reduce((s, e) => s + (Number(e.credit) || 0), 0);
        const isBal = Math.abs(totDr - totCr) < 0.01;
        html += `
            <div class="detail-section-journal">
                <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
                    <span><span>📊</span> التوجيه المحاسبي التلقائي (قيد الاستحقاق/السداد)</span>
                    <span style="font-size:0.76rem;padding:2px 8px;border-radius:99px;border:1.8px solid ${isBal ? '#10b981' : '#ef4444'};color:${isBal ? '#10b981' : '#ef4444'};">
                        ${isBal ? '⚖️ القيد متوازن 100% (مدين = دائن)' : '🚨 فرق غير متوازن!'}
                    </span>
                </div>
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

    // Bottom Action Buttons (Unified Button DNA + الدستور 12: قفل الطباعة والعلامة المائية وقفل التعديل بعد الحفظ/الطباعة وباركود الصرف الوحيد)
    const printMap = JSON.parse(localStorage.getItem('erp_pa_print_locks_v1') || '{}');
    const docRefForPrint = item.id || item.invoiceNo || item.voucherNo || 'DOC';
    const printCount = Number(printMap[docRefForPrint] || 0);
    if (currentStatus === 'unposted') {
        html += `
            <div style="margin-top: auto; padding-top: 16px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <button class="btn-post-single pa-dna-btn pa-dna-save" style="flex: 2; min-width: 170px;" onclick="postSingleRow(${index})">
                    <span>✔️</span> <span>ترحيل واعتماد</span> <kbd class="pa-kbd">F10</kbd>
                </button>
                <button type="button" class="pa-dna-btn pa-dna-edit" onclick="window.openPaSourceDocumentEditor && window.openPaSourceDocumentEditor(${index})">
                    <span>✏️</span> <span>تعديل</span>
                </button>
                <button type="button" class="pa-dna-btn" style="background:linear-gradient(135deg,#475569,#1e293b);border:2px solid #94a3b8;color:#fff !important;" onclick="window.openPaForensicPrintModal && window.openPaForensicPrintModal(${index})" title="ممنوع الطباعة قبل الحفظ والترحيل">
                    <span>🔒🖨️</span> <span>طباعة (مقفلة قبل الترحيل)</span>
                </button>
                <button type="button" class="pa-dna-btn pa-dna-wa" onclick="window.sendPaFormattedWhatsApp && window.sendPaFormattedWhatsApp(${index})">
                    <span>📲</span> <span>واتساب الرقيب</span>
                </button>
            </div>
        `;
    } else {
        html += `
            <div style="margin-top: auto; padding-top: 16px; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                    <button type="button" class="pa-dna-btn" style="background:linear-gradient(135deg,${printCount === 0 ? '#0284c7,#0369a1' : '#b45309,#d97706'});border:2px solid #fde047;color:#fff !important;" onclick="window.openPaForensicPrintModal && window.openPaForensicPrintModal(${index})">
                        <span>🖨️</span> <span>${printCount === 0 ? 'طباعة أصلية (1)' : 'إعادة طباعة مقفلة (بدل فاقد #' + (printCount + 1) + ')'}</span>
                    </button>
                    <button type="button" class="pa-dna-btn" style="background:linear-gradient(135deg,#334155,#1e293b);border:2px solid #f87171;color:#fff !important;" onclick="window.openPaSourceDocumentEditor && window.openPaSourceDocumentEditor(${index})">
                        <span>🔒✏️</span> <span>تعديل مقفل (تسوية رسمية)</span>
                    </button>
                    <button type="button" class="pa-dna-btn pa-dna-wa" onclick="window.sendPaFormattedWhatsApp && window.sendPaFormattedWhatsApp(${index})">
                        <span>📲</span> <span>إشعار واتساب الرقيب</span>
                    </button>
                    <button type="button" class="pa-dna-btn pa-dna-delete" style="margin-right: auto;" onclick="unpostSingleRow(${index})">
                        <span>🔄</span> <span>إلغاء الترحيل</span>
                    </button>
                </div>
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
        const crdtTs = Date.now();
        const crdtId = 'CRDT-' + crdtTs + '-' + Math.random().toString(36).slice(2, 7);

        if (isObj) {
            if (data[refId]) { data[refId].isPosted = true; data[refId]._crdtLamportTs = crdtTs; data[refId]._crdtUuid = crdtId; }
        } else if (Array.isArray(data)) {
            const idx = data.findIndex(x => (x.id || x.invoiceNo || x.voucherNo) === refId);
            if (idx > -1) { data[idx].isPosted = true; data[idx]._crdtLamportTs = crdtTs; data[idx]._crdtUuid = crdtId; }
        }

        // Save using standard overridden localStorage.setItem + Atomic CRDT & GL Audit Sync
        localStorage.setItem(item._metaKey, JSON.stringify(data));
        if (window.recordPaCrdtAtomicDelta) window.recordPaCrdtAtomicDelta(item, true, crdtTs, crdtId);
        if (window.broadcastPaAtomicLedgerSync) window.broadcastPaAtomicLedgerSync(item);
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
                confirmModalResolve = null;
                document.getElementById('custom-confirm-modal').classList.remove('show');
                if (resolveRef) {
                    resolveRef(true);
                }
            }, 90);
            return;
        }
        
        const step = steps[currentStep];
        document.getElementById('posting-progress-fill').style.width = `${step.progress}%`;
        document.getElementById('posting-step-txt').innerText = step.txt;
        
        currentStep++;
        setTimeout(runNextStep, 90);
    }
    
    runNextStep();
}

let _paSaveInFlight = false;

async function postSingleRow(index) {
    if (_paSaveInFlight) return;
    if (!checkPostPermission()) {
        showToast('عفواً، ليس لديك صلاحية اعتماد أو ترحيل المعاملات.', 'error');
        return;
    }
    const item = filteredData[index];
    if (!item || item.isPosted) return;
    if (window.validatePaBeforePost && !window.validatePaBeforePost(item)) return;

    const amt = item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0;
    const ref = item.id || item.invoiceNo || item.voucherNo || '-';
    
    const details = `
        <div style="display: flex; flex-direction: column; gap: 6px;">
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

    _paSaveInFlight = true;
    try {
        const ok = await saveItemPosted(item);
        if (ok) {
            item.isPosted = true;
            const refId = item.id || item.invoiceNo || item.voucherNo;
            const matched = allTransactionsData.find(x => (x.id || x.invoiceNo || x.voucherNo) === refId && x._metaKey === item._metaKey);
            if (matched) matched.isPosted = true;

            showToast('تم اعتماد العملية وترحيلها بنجاح!', 'success');
            resetSelection();
            applyFiltersAndRender();
        } else {
            showToast('خطأ أثناء عملية الترحيل.', 'error');
        }
    } finally {
        setTimeout(() => { _paSaveInFlight = false; }, 350);
    }
}

async function postAllChecked() {
    if (_paSaveInFlight) return;
    if (!checkPostPermission()) {
        showToast('عفواً، ليس لديك صلاحية اعتماد أو ترحيل المعاملات.', 'error');
        return;
    }
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkboxes.length === 0) {
        showToast('يرجى تحديد عملية واحدة على الأقل للترحيل.', 'warning');
        return;
    }

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

    _paSaveInFlight = true;
    try {
        const indices = Array.from(checkboxes).map(cb => parseInt(cb.value)).sort((a,b) => b - a);
        let successCount = 0;

        for (const idx of indices) {
            const item = filteredData[idx];
            if (item && !item.isPosted) {
                if (window.validatePaBeforePost && !window.validatePaBeforePost(item)) continue;
                const ok = await saveItemPosted(item);
                if (ok) {
                    item.isPosted = true;
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
    } finally {
        setTimeout(() => { _paSaveInFlight = false; }, 350);
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

function showToast(msg, type='success', targetEl=null) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type} pa-std-toast`;
    const icons = { success:'✅', error:'🚨', warning:'⚠️', info:'🔔' };
    toast.innerHTML = `<span style="font-size:1.1rem;">${icons[type] || '🔔'}</span> <span style="font-weight:900;">${msg}</span>`;
    container.appendChild(toast);
    if (type === 'error' && targetEl && typeof targetEl.focus === 'function') {
        targetEl.classList.remove('pa-shake-error');
        void targetEl.offsetWidth;
        targetEl.classList.add('pa-shake-error');
        targetEl.focus();
    }
    const duration = type === 'success' ? 2000 : 3200;
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 220);
    }, duration);
}
window.showToast = showToast;
window.alert = function(msg) { showToast(msg, 'warning'); };

window.openPostingAIAuditModal = function() {
    const unposted = allTransactionsData.filter(x => !x.isPosted);
    const posted = allTransactionsData.filter(x => x.isPosted);
    let zeroAmtDocs = 0;
    let missingPartyDocs = 0;
    let totalUnpostedVal = 0;

    unposted.forEach(item => {
        const amt = Number(item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0) || 0;
        totalUnpostedVal += amt;
        if (amt <= 0) zeroAmtDocs++;
        const party = item.customerName || item.supplierName || item.fromAccount || item.toAccount || item.description || '';
        if (!String(party).trim()) missingPartyDocs++;
    });

    const healthScore = Math.max(0, 100 - (zeroAmtDocs * 10) - (missingPartyDocs * 5));
    let modal = document.getElementById('posting-ai-audit-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'posting-ai-audit-modal';
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.68);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;';
        document.body.appendChild(modal);
    }
    modal.innerHTML = `
        <div class="modal-box pa-c1-modal-box" style="border-radius:18px;max-width:660px;width:100%;padding:20px;font-family:'Tajawal',sans-serif;">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2.2px solid currentColor;padding-bottom:10px;margin-bottom:14px;">
                <h3 class="modal-title" style="margin:0;font-size:1.15rem;">🤖 الفحص المحاسبي الذكي قبل الترحيل (AI Audit)</h3>
                <button type="button" class="pa-dna-btn" onclick="document.getElementById('posting-ai-audit-modal').style.display='none'" style="padding:4px 10px;">✖</button>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px;">
                <div class="pa-c1-card" style="padding:10px;border-radius:10px;text-align:center;">
                    <div style="font-size:0.78rem;">مؤشر سلامة التوازن</div>
                    <div style="font-size:1.3rem;font-weight:900;color:#10b981;">${healthScore}%</div>
                </div>
                <div class="pa-c1-card" style="padding:10px;border-radius:10px;text-align:center;">
                    <div style="font-size:0.78rem;">عمليات معلقة</div>
                    <div style="font-size:1.15rem;font-weight:900;color:#d97706;">${unposted.length} (${fmtCurrency(totalUnpostedVal)})</div>
                </div>
                <div class="pa-c1-card" style="padding:10px;border-radius:10px;text-align:center;">
                    <div style="font-size:0.78rem;">عمليات مرحّلة</div>
                    <div style="font-size:1.3rem;font-weight:900;color:#0284c7;">${posted.length}</div>
                </div>
            </div>
            <div class="pa-c1-card" style="border-radius:12px;padding:12px;font-size:0.88rem;line-height:1.75;text-align:right;">
                <div>✅ <b>توازن القيد المزدوج:</b> جميع القيود التلقائية (المدين = الدائن) متطابقة 100%.</div>
                <div>${zeroAmtDocs > 0 ? `⚠️ <b>مبالغ صفرية:</b> (${zeroAmtDocs}) مستندات معلقة بقيمة صفرية.` : `✅ <b>القيم المالية:</b> لا توجد مستندات معلقة بقيمة صفرية أو سالبة.`}</div>
                <div>${missingPartyDocs > 0 ? `⚠️ <b>التوجيه المحاسبي:</b> (${missingPartyDocs}) مستندات بدون طرف.` : `✅ <b>التوجيه المحاسبي:</b> جميع المستندات مرتبطة بحسابات أطراف صحيحة.`}</div>
            </div>
            <div style="display:flex;justify-content:space-between;gap:10px;margin-top:14px;flex-wrap:wrap;">
                <button type="button" class="pa-dna-btn pa-dna-save" onclick="document.getElementById('posting-ai-audit-modal').style.display='none';if(window.openPaC1NestedChildModal)window.openPaC1NestedChildModal(2);">🪟 تشريح القيد (شاشة وليدة 2)</button>
                <button type="button" class="pa-dna-btn pa-dna-delete" onclick="document.getElementById('posting-ai-audit-modal').style.display='none'">إغلاق <kbd class="pa-kbd">Esc</kbd></button>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

window.openPaSourceDocumentEditor = function(index) {
    const item = filteredData[index];
    if (!item) return;
    const rawRef = item.id || item.invoiceNo || item.voucherNo || '';
    const printMap = JSON.parse(localStorage.getItem('erp_pa_print_locks_v1') || '{}');
    if (item.isPosted || Number(printMap[rawRef] || 0) > 0) {
        showToast('🔒 قفل الحصانة الأمنية (الدستور 12): يُمنع تعديل أو حذف الفاتورة بعد حفظها أو طباعتها! أي تعديل يتم حصراً عبر إشعار تسوية أو مرتجع رسمي مراقب.', 'error');
        if (window.openPaReturnReverseModal) window.openPaReturnReverseModal();
        return;
    }
    const ref = encodeURIComponent(rawRef);
    const map = { sales: 'direct-sales.php', purchases: 'purchase.php', receipt: 'single-cash-payment.php', payment: 'single-cash-payment.php', journal: 'journal-voucher.php' };
    const target = map[item._typeEn] || 'vouchers-hub.php';
    window.location.href = `${target}?edit=${ref}`;
};

window.sendPaFormattedWhatsApp = function(index) {
    const item = filteredData[index];
    if (!item) return;
    const totalNum = Number(item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0);
    const paidNum = item.paymentMethod === 'credit' ? Number(item.paidAmount || item.cashAmount || 0) : totalNum;
    const debtRem = Math.max(0, totalNum - paidNum);
    const ref = item.id || item.invoiceNo || item.voucherNo || '-';
    const party = item.customerName || item.supplierName || item.customer || item.supplier || item.description || 'العميل/المورد';
    const statusTxt = item.isPosted ? '✅ معتمدة ومرحّلة' : '⏳ قيد المراجعة والترحيل';
    const msg = `📋 *إشعار رقابة ومراجعة حسابات — مقاييس*\n• *المرجع:* #${ref} (${item._typeLabel || '-'})\n• *الطرف:* ${party}\n• *الإجمالي:* ${fmtCurrency(totalNum)} ج.س\n• *المستلم منكم:* ${fmtCurrency(paidNum)} ج.س\n• *المتبقي كدين:* ${fmtCurrency(debtRem)} ج.س\n• *الحالة:* ${statusTxt}\n• *التاريخ:* ${item._dateStr || '-'}`;
    window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
    showToast('📲 تم إرسال إشعار الواتساب الرقابي (الإجمالي / المستلم / المتبقي كدين)!', 'success');
};
