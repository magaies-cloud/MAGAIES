// js/multi-cash-payment.js

document.addEventListener('DOMContentLoaded', () => {
    // Set today's date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('voucher-date').value = today;
    
    // Add first empty row
    addRow();
    addRow(); // Start with 2 rows for convenience

    // Live date display in header
    setInterval(() => {
        const now = new Date();
        document.getElementById('header-date-mini').innerText = now.toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }, 1000);

    // If app is already ready
    if (window.APP_READY) {
        loadAllAccounts();
    }
});

document.addEventListener('DBReady', async () => {
    await loadAllAccounts();
});

let mainAccountChoices = null;
let allAccountsList = [];

async function loadAllAccounts() {
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const stores = [
            { key: 'erp_customers_v1', label: 'عميل' },
            { key: 'erp_suppliers_v1', label: 'مورد' },
            { key: 'erp_banks_v1', label: 'بنك' },
            { key: 'erp_safes_v1', label: 'خزينة' },
            { key: 'erp_accounts_v1', label: 'حساب عام/مصروف' }
        ];

        let combined = [];

        for (const store of stores) {
            let data = JSON.parse(localStorage.getItem(store.key) || '[]');
            if (!Array.isArray(data)) data = Object.values(data);
            
            data.forEach(item => {
                const name = item.name || item.accountName || item.customerName || item.supplierName || item.bankName || item.safeName;
                if (name) {
                    combined.push({
                        value: name,
                        label: `${name} (${store.label})`
                    });
                }
            });
        }

        allAccountsList = combined;

        // Initialize Choices for Main Account
        const selectEl = document.getElementById('main-account');
        if (selectEl) {
            mainAccountChoices = new Choices(selectEl, {
                searchEnabled: true,
                itemSelectText: '',
                noResultsText: 'لا توجد نتائج',
                shouldSort: false
            });
            mainAccountChoices.setChoices(combined, 'value', 'label', true);
        }

        // Update existing dynamic rows if they were already created
        const subSelects = document.querySelectorAll('.sub-account');
        subSelects.forEach(selectEl => {
            if (selectEl.choicesInstance) {
                selectEl.choicesInstance.setChoices(combined, 'value', 'label', true);
            } else {
                const choiceInstance = new Choices(selectEl, {
                    searchEnabled: true,
                    itemSelectText: '',
                    noResultsText: 'لا توجد نتائج',
                    shouldSort: false
                });
                choiceInstance.setChoices(combined, 'value', 'label', true);
                selectEl.choicesInstance = choiceInstance; // cache it
            }
        });

    } catch (e) {
        console.error('Error loading accounts:', e);
    }
}

// Helper: Format number with commas
const fmtNum = (num) => Number(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
// Helper: Clean commas for calculation
const cleanNum = (str) => Number(String(str).replace(/,/g, '')) || 0;

// Helper: Restrict input to numbers and decimal point only
function restrictToNumbers(el) {
    // Only allow numbers, dots, and commas
    let val = el.value.replace(/[^0-9.,]/g, '');
    // Prevent multiple dots
    const parts = val.split('.');
    if (parts.length > 2) {
        val = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, '');
    }
    if (el.value !== val) {
        el.value = val;
    }
}

function setVoucherType(type) {
    document.getElementById('voucher-type').value = type;
    const btnPayment = document.getElementById('btn-payment');
    const btnReceipt = document.getElementById('btn-receipt');
    
    if (type === 'payment') {
        btnPayment.classList.add('active-payment');
        btnReceipt.classList.remove('active-receipt');
    } else {
        btnReceipt.classList.add('active-receipt');
        btnPayment.classList.remove('active-payment');
    }
    
    updateLabels();
}

function togglePaymentMethod() {
    const pm = document.querySelector('input[name="payment_method"]:checked').value;
    const sidePanel = document.getElementById('payment-details-panel');
    const layoutWrapper = document.getElementById('main-layout-wrapper');
    const secCheck = document.getElementById('section-check');
    const secMobile = document.getElementById('section-mobile');
    const panelTitle = document.getElementById('panel-title-type');

    if (pm === 'check') {
        layoutWrapper.classList.add('has-sidebar');
        secCheck.style.display = 'block';
        secMobile.style.display = 'none';
        panelTitle.innerText = 'الشيك';
    } else if (pm === 'mobile') {
        layoutWrapper.classList.add('has-sidebar');
        secCheck.style.display = 'none';
        secMobile.style.display = 'block';
        panelTitle.innerText = 'التحويل';
    } else {
        layoutWrapper.classList.remove('has-sidebar');
        setTimeout(() => {
            secCheck.style.display = 'none';
            secMobile.style.display = 'none';
        }, 500); // Wait for transition
    }
}

function updateLabels() {
    const type = document.getElementById('voucher-type').value;
    const label = document.getElementById('main-account-label');
    const subHeader = document.getElementById('sub-account-header');
    const subTitle = document.getElementById('sub-accounts-title');
    
    if (type === 'payment') {
        label.innerText = 'من حساب (صرف)';
        if(subHeader) subHeader.innerText = 'إلى حساب (استلام)';
        if(subTitle) subTitle.innerHTML = '<span>👥</span> إلى حساب (استلام)';
    } else {
        label.innerText = 'إلى حساب (استلام)';
        if(subHeader) subHeader.innerText = 'من حساب (دفع)';
        if(subTitle) subTitle.innerHTML = '<span>👥</span> من حساب (دفع)';
    }
}

let multiRowIdCounter = 0;
function addRow() {
    multiRowIdCounter++;
    const rowId = multiRowIdCounter;
    const tbody = document.getElementById('entries-body');
    const tr = document.createElement('tr');
    tr.className = 'multi-row';
    tr.innerHTML = `
        <td>
            
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" id="acc-${rowId}" class="sub-account">
                <input type="text" class="multi-input" id="acc-${rowId}-search" placeholder="ابحث عن حساب..." onfocus="showAccountDropdown('acc-${rowId}')" onblur="hideAccountDropdownDelayed('acc-${rowId}')" oninput="filterAccountDropdown('acc-${rowId}')" style="width: 100%; box-sizing: border-box; background: transparent; border: none; color: inherit; padding: 10px;">
                <div class="custom-dropdown-list" id="acc-${rowId}-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5);"></div>
            </div>
        </td>
        <td><input type="text" class="multi-input sub-amount" placeholder="0.00" onblur="formatInput(this)" oninput="restrictToNumbers(this); calcTotal();" style="font-family: monospace; font-weight: bold; font-size: 1.1rem; color: #10b981;"></td>
        <td class="col-desc"><input type="text" class="multi-input sub-desc" placeholder="بيان هذا الحساب..."></td>
        <td class="col-cost-center"><input type="text" class="multi-input sub-cost-center" placeholder="مركز التكلفة..."></td>
        <td class="col-note"><input type="text" class="multi-input sub-note" placeholder="ملحوظة..."></td>
        <td class="col-check-no"><input type="text" class="multi-input sub-check-no" placeholder="رقم الشيك..."></td>
        <td class="col-check-date"><input type="date" class="multi-input sub-check-date"></td>
        <td class="col-check-due"><input type="date" class="multi-input sub-check-due"></td>
        <td style="text-align: center;"><button class="btn-del" onclick="removeRow(this)" title="حذف السطر">✖</button></td>
    `;
    tbody.appendChild(tr);
    
    // Initialize Choices for the newly added select
    const selectEl = tr.querySelector('.sub-account');
    const choiceInstance = new Choices(selectEl, {
        searchEnabled: true,
        itemSelectText: '',
        noResultsText: 'لا توجد نتائج',
        shouldSort: false
    });
    selectEl.choicesInstance = choiceInstance; // cache it

    // Populate with loaded accounts if available
    if (allAccountsList && allAccountsList.length > 0) {
        choiceInstance.setChoices(allAccountsList, 'value', 'label', true);
    }

    calcTotal();
    if (typeof window.applySystemNumberFormatting === 'function') window.applySystemNumberFormatting();
}

function removeRow(btn) {
    const tr = btn.closest('tr');
    tr.remove();
    calcTotal();
}

function formatInput(input) {
    const val = cleanNum(input.value);
    input.value = val === 0 ? '' : fmtNum(val);
    calcTotal();
}

function calcTotal() {
    const amounts = document.querySelectorAll('.sub-amount');
    let total = 0;
    amounts.forEach(input => {
        total += cleanNum(input.value);
    });

    document.getElementById('total-amount').innerText = fmtNum(total);
    document.getElementById('total-rows').innerText = amounts.length;
    
    // Update Foreign Amount (القيمة المعادل)
    const rateEl = document.getElementById('exchange-rate');
    const eqEl = document.getElementById('amount-foreign');
    if (rateEl && eqEl) {
        let rate = cleanNum(rateEl.value) || 1;
        let eq = rate === 0 ? 0 : total / rate;
        eqEl.value = fmtNum(eq);
    }
    
    if (typeof window.applySystemNumberFormatting === 'function') {
        window.applySystemNumberFormatting();
    }
}

function showToast(msg, type='success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icons = { success:'✔️', error:'✖️' };
    toast.innerHTML = `<span>${icons[type] || '🔔'}</span> <span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

/* --- Context Menu and Dynamic Columns Logic --- */
function toggleColumn(colClass, isVisible) {
    const table = document.getElementById('voucher-table');
    if (isVisible) {
        table.classList.add('show-col-' + colClass);
    } else {
        table.classList.remove('show-col-' + colClass);
    }
    // Save preference
    const prefs = JSON.parse(localStorage.getItem('multiVoucherColPrefs') || '{}');
    prefs[colClass] = isVisible;
    localStorage.setItem('multiVoucherColPrefs', JSON.stringify(prefs));
}

function loadColumnPrefs() {
    const prefs = JSON.parse(localStorage.getItem('multiVoucherColPrefs') || '{}');
    const menu = document.getElementById('column-context-menu');
    const checkboxes = menu.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
        if (prefs[cb.value] === true) {
            cb.checked = true;
            toggleColumn(cb.value, true);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadColumnPrefs();
    
    // Right click on table header
    const tableHeader = document.getElementById('table-header');
    const contextMenu = document.getElementById('column-context-menu');
    
    if (tableHeader) {
        tableHeader.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.classList.add('active');
        });
    }

    // Click anywhere else to hide context menu
    document.addEventListener('click', (e) => {
        if (!contextMenu.contains(e.target) && e.target !== tableHeader && !tableHeader.contains(e.target)) {
            contextMenu.classList.remove('active');
        }
    });
});
/* --------------------------------------------- */

/* --- Footer Actions --- */
function resetVoucher() {
    document.getElementById('main-account').value = '';
    document.getElementById('voucher-ref').value = '';
    document.getElementById('voucher-desc').value = '';
    
    // Clear dynamic rows
    const tbody = document.getElementById('entries-body');
    tbody.innerHTML = '';
    
    // Re-initialize choices for main account to empty state if needed
    if (mainAccountChoices) {
        mainAccountChoices.setChoiceByValue('');
    }

    addRow(); 
    addRow();
    calcTotal();
}

function printVoucher() {
    // Basic print, could be expanded to print a specific receipt template
    window.print();
}

function confirmDelete() {
    if (confirm('هل أنت متأكد من رغبتك في حذف البيانات الحالية وإلغاء المستند المجمع؟')) {
        resetVoucher();
        showToast('تم إلغاء المستند وتفريغ الحقول.', 'info');
    }
}

async function saveVoucher() {
    const type = document.getElementById('voucher-type').value;
    const date = document.getElementById('voucher-date').value;
    const mainAccount = document.getElementById('main-account').value;
    const desc = document.getElementById('voucher-desc').value.trim();
    const ref = document.getElementById('voucher-ref').value.trim();
    const currency = document.getElementById('currency').value;
    const exchangeRate = parseFloat(document.getElementById('exchange-rate').value) || 1.0;
    
    // Payment method and dynamic fields
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const checkNo = document.getElementById('check-no') ? document.getElementById('check-no').value.trim() : '';
    const checkBranch = document.getElementById('check-branch') ? document.getElementById('check-branch').value.trim() : '';
    const checkDate = document.getElementById('check-date') ? document.getElementById('check-date').value : '';
    const checkDueDate = document.getElementById('check-due-date') ? document.getElementById('check-due-date').value : '';
    const mobileTransferNo = document.getElementById('mobile-transfer-no') ? document.getElementById('mobile-transfer-no').value.trim() : '';
    const mobileNote = document.getElementById('mobile-note') ? document.getElementById('mobile-note').value.trim() : '';

    if (!date) return showToast('يرجى تحديد تاريخ العملية', 'error');
    if (!mainAccount) return showToast('يرجى اختيار الحساب الرئيسي', 'error');
    
    // Strict validations for bank references
    if (paymentMethod === 'check') {
        if (!checkNo || checkNo.length < 6) return showToast('رقم الشيك يجب أن يكون أكثر من 5 أرقام.', 'error');
        if (!window.isBankReferenceUnique(checkNo, 'check')) return showToast('رقم الشيك هذا مسجل مسبقاً في النظام ولا يمكن تكراره.', 'error');
        if (!checkBranch) return showToast('يرجى إدخال اسم البنك', 'error');
    } else if (paymentMethod === 'mobile') {
        if (!mobileTransferNo || mobileTransferNo.length < 6) return showToast('رقم التحويل يجب أن يكون أكثر من 5 أرقام.', 'error');
        if (!window.isBankReferenceUnique(mobileTransferNo, 'mobile')) return showToast('رقم التحويل هذا مسجل مسبقاً في النظام ولا يمكن تكراره.', 'error');
    }
    
    const rows = document.querySelectorAll('.multi-row');
    const items = [];
    let hasError = false;
    let totalAmount = 0;

    rows.forEach((row, index) => {
        const acc = row.querySelector('.sub-account').value.trim();
        const amtStr = row.querySelector('.sub-amount').value;
        const amt = cleanNum(amtStr);
        const subDesc = row.querySelector('.sub-desc') ? row.querySelector('.sub-desc').value.trim() : '';
        const subCostCenter = row.querySelector('.sub-cost-center') ? row.querySelector('.sub-cost-center').value.trim() : '';
        const subNote = row.querySelector('.sub-note') ? row.querySelector('.sub-note').value.trim() : '';
        const subCheckNo = row.querySelector('.sub-check-no') ? row.querySelector('.sub-check-no').value.trim() : '';
        const subCheckDate = row.querySelector('.sub-check-date') ? row.querySelector('.sub-check-date').value : '';
        const subCheckDue = row.querySelector('.sub-check-due') ? row.querySelector('.sub-check-due').value : '';

        if (acc || amt > 0) {
            if (!acc) { showToast(`السطر ${index+1}: الرجاء إدخال اسم الحساب.`, 'error'); hasError = true; }
            if (amt <= 0) { showToast(`السطر ${index+1}: الرجاء إدخال مبلغ صحيح.`, 'error'); hasError = true; }
            items.push({
                account: acc,
                amount: amt,
                statement: subDesc,
                costCenter: subCostCenter,
                note: subNote,
                checkNo: subCheckNo,
                checkDate: subCheckDate,
                checkDueDate: subCheckDue
            });
        }
    });

    if (hasError) return;
    if (items.length === 0) {
        showToast('يجب إدخال حساب فرعي واحد على الأقل.', 'error');
        return;
    }

    const finalTotal = items.reduce((sum, item) => sum + item.amount, 0);

    // Build the voucher object
    const voucher = {
        id: 'MV-' + Date.now(), // Generate a unique ID
        voucherNo: ref || ('MV-' + Math.floor(Math.random() * 10000)),
        type: type, // 'payment' or 'receipt'
        isGrouped: true,
        mainAccount: mainAccount,
        date: date,
        description: desc,
        totalAmount: finalTotal,
        currency: currency,
        exchangeRate: exchangeRate,
        foreignAmount: finalTotal / exchangeRate,
        paymentMethod: paymentMethod,
        checkNo: checkNo,
        checkBranch: checkBranch,
        checkDate: checkDate,
        checkDueDate: checkDueDate,
        mobileTransferNo: mobileTransferNo,
        mobileNote: mobileNote,
        items: items, // Contains {account, amount, statement}
        isPosted: false, // Sent to posting screen
        createdAt: new Date().toISOString()
    };

    // To make it compatible with the auditing screen (ترحيل الحسابات),
    // we set fromAccount and toAccount properly.
    if (type === 'payment') {
        voucher.fromAccount = mainAccount; // Source of funds
        voucher.toAccount = 'حسابات فرعية متعددة';
        voucher.notes = desc;
    } else {
        voucher.toAccount = mainAccount; // Destination of funds
        voucher.fromAccount = 'حسابات فرعية متعددة';
        voucher.notes = desc;
    }

    // Determine storage key based on type
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const tKey = getTenantKey(storageKey);
        
        let existingData = [];
        if (typeof localforage !== 'undefined') {
            existingData = await localforage.getItem(tKey) || [];
        } else {
            existingData = JSON.parse(localStorage.getItem(tKey) || '[]');
        }

        if (!Array.isArray(existingData)) existingData = Object.values(existingData);

        existingData.push(voucher);

        if (typeof localforage !== 'undefined') {
            await localforage.setItem(tKey, existingData);
        } else {
            localStorage.setItem(tKey, JSON.stringify(existingData));
        }

        showToast('تم حفظ السند المجمع بنجاح وإرساله لشاشة الترحيل!', 'success');
        
        // Reset Form after short delay
        setTimeout(() => {
            resetVoucher();
        }, 1500);

    } catch (e) {
        console.error('Save error:', e);
        showToast('حدث خطأ أثناء الحفظ!', 'error');
    }
}


// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Accounts
// ═══════════════════════════════════════════════════════

// Populate ACCOUNTS based on allAccountsList format, but keeping IDs properly
let ACCOUNTS = [];
function buildAccountsArray() {
    ACCOUNTS = [];
    const stores = [
        { key: 'erp_customers_v1', prefix: 'cust-' },
        { key: 'erp_suppliers_v1', prefix: 'supp-' },
        { key: 'erp_banks_v1', prefix: 'bank-' },
        { key: 'erp_safes_v1', prefix: 'safe-' },
        { key: 'erp_accounts_v1', prefix: 'exp-' }
    ];

    for (const store of stores) {
        let data = JSON.parse(localStorage.getItem(store.key) || '[]');
        if (!Array.isArray(data)) data = Object.values(data);
        
        data.forEach(item => {
            const name = item.name || item.accountName || item.customerName || item.supplierName || item.bankName || item.safeName;
            const id = item.id || item.code || '';
            if (name) {
                ACCOUNTS.push({ id: store.prefix + id, name: name, rawId: id });
            }
        });
    }
}

// Hook into DBReady to build ACCOUNTS
document.addEventListener('DBReady', () => {
    buildAccountsArray();
});

function renderAccountDropdown(type, filter = '') {
    if (ACCOUNTS.length === 0) buildAccountsArray();

    const listEl = document.getElementById(type + '-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
    
    try {
        let filtered = ACCOUNTS.filter(a => {
            if (!term) return true;
            const normalizedName = window.normalizeArabic ? window.normalizeArabic(a.name) : a.name.toLowerCase();
            return normalizedName.includes(normalizedTerm) || String(a.id).includes(term);
        });

        const totalFound = filtered.length;
        filtered = filtered.slice(0, 50);

        if (filtered.length === 0) {
            listEl.innerHTML = `<div style="padding: 15px; color: var(--text-secondary); text-align: center;">لا توجد حسابات مطابقة</div>`;
            return;
        }

        filtered.forEach(a => {
            const div = document.createElement('div');
            div.className = 'custom-dropdown-item';
            div.style.cssText = 'padding: 10px 15px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;';
            div.onmouseover = () => div.style.background = 'rgba(255,255,255,0.1)';
            div.onmouseout = () => div.style.background = 'transparent';
            
            let typeTag = '';
            if (String(a.id).startsWith('cust-')) typeTag = '<span style="font-size:0.75rem; color:#60a5fa; background:rgba(96,165,250,0.1); padding:2px 6px; border-radius:4px;">عميل</span>';
            else if (String(a.id).startsWith('supp-')) typeTag = '<span style="font-size:0.75rem; color:#f472b6; background:rgba(244,114,182,0.1); padding:2px 6px; border-radius:4px;">مورد</span>';
            else if (String(a.id).startsWith('bank-')) typeTag = '<span style="font-size:0.75rem; color:#a78bfa; background:rgba(167,139,250,0.1); padding:2px 6px; border-radius:4px;">بنك</span>';
            else if (String(a.id).startsWith('safe-')) typeTag = '<span style="font-size:0.75rem; color:#34d399; background:rgba(52,211,153,0.1); padding:2px 6px; border-radius:4px;">خزينة</span>';
            else if (String(a.id).startsWith('exp-')) typeTag = '<span style="font-size:0.75rem; color:#fbbf24; background:rgba(251,191,36,0.1); padding:2px 6px; border-radius:4px;">مصروف</span>';

            div.innerHTML = `
                <div style="font-weight: bold; color: #fff;">${a.name}</div>
                <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 3px; display: flex; gap: 8px; align-items: center;">
                    <span>[${a.rawId}]</span>
                    ${typeTag}
                </div>
            `;
            div.onclick = (e) => {
                e.stopPropagation();
                selectAccount(type, a.id, a.name);
            };
            listEl.appendChild(div);
        });

        if (totalFound > 50) {
            const note = document.createElement('div');
            note.style.cssText = 'padding: 8px; font-size: 0.8rem; color: #fbbf24; text-align: center; background: rgba(251,191,36,0.1);';
            note.innerText = `عرض أول 50 حساب من أصل ${totalFound}. يرجى تضييق نطاق البحث.`;
            listEl.appendChild(note);
        }

    } catch (e) {
        console.error('Error rendering accounts:', e);
    }
}

window.showAccountDropdown = function(type) {
    const listEl = document.getElementById(type + '-dropdown-list');
    if (listEl) {
        renderAccountDropdown(type, document.getElementById(type + '-search').value);
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

window.hideAccountDropdownDelayed = function(type) {
    setTimeout(() => {
        const listEl = document.getElementById(type + '-dropdown-list');
        if (listEl) {
            listEl.classList.remove('show');
            listEl.style.display = 'none';
        }
    }, 200);
}

window.filterAccountDropdown = function(type) {
    const val = document.getElementById(type + '-search').value;
    renderAccountDropdown(type, val);
}

window.selectAccount = function(type, id, name) {
    document.getElementById(type).value = id; // Store ID
    document.getElementById(type + '-search').value = name; // Store Name
    
    // In multi-cash-payment, the original logic saved the NAME as the value for accounts!
    // To preserve backwards compatibility with saveVoucher(), we update the .value of the hidden input
    // to the name, but we add a data-id attribute for the real ID.
    const hiddenInp = document.getElementById(type);
    if(hiddenInp) {
        hiddenInp.value = name;
        hiddenInp.setAttribute('data-id', id);
    }

    const listEl = document.getElementById(type + '-dropdown-list');
    if(listEl) {
        listEl.classList.remove('show');
        listEl.style.display = 'none';
    }
    
    // Jump logic
    if (type.startsWith('acc-')) {
        const rowId = type.split('-')[1];
        const rowTr = document.getElementById(type).closest('tr');
        if(rowTr) {
            const amtInp = rowTr.querySelector('.sub-amount');
            if(amtInp) amtInp.focus();
        }
    }
};

if (!window.normalizeArabic) {
    window.normalizeArabic = function(text) {
        if (!text) return '';
        return text.toString()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .toLowerCase();
    };
}
