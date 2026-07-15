// js/multi-cash-payment.js

let currentVoucherIndex = -1;
let currentVoucherList = [];

document.addEventListener('DOMContentLoaded', () => {
    // Set today's date
    const today = new Date().toISOString().split('T')[0];
    const dateEl = document.getElementById('voucher-date');
    if (dateEl) dateEl.value = today;
    
    // Add first empty row
    addRow();
    addRow(); // Start with 2 rows for convenience

    // Live date display in header
    const dateMini = document.getElementById('header-date-mini');
    if (dateMini) {
        setInterval(() => {
            const now = new Date();
            dateMini.innerText = now.toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }, 1000);
    }

    // If app is already ready
    if (window.APP_READY) {
        loadAllAccounts();
    }
    loadNextMultiVoucherNumber();
});

document.addEventListener('DBReady', async () => {
    await loadAllAccounts();
});

let mainAccountChoices = null;
let allAccountsList = [];

async function loadAllAccounts() {
    try {
        buildAccountsArray();
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
    let val = el.value.replace(/[^0-9.,]/g, '');
    const parts = val.split('.');
    if (parts.length > 2) {
        val = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, '');
    }
    if (el.value !== val) {
        el.value = val;
    }
}

function setVoucherType(type) {
    const btnPayment = document.getElementById('btn-payment');
    const btnReceipt = document.getElementById('btn-receipt');
    const typeInput = document.getElementById('voucher-type');
    if (typeInput) typeInput.value = type;
    
    const pmRow = document.getElementById('payment-methods-row');
    
    if (type === 'payment') {
        if (btnPayment) btnPayment.className = 'voucher-type-btn active-payment';
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'rgba(239, 68, 68, 0.08)';
            pmRow.style.borderColor = 'rgba(239, 68, 68, 0.2)';
        }
    } else {
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn active-receipt';
        if (btnPayment) btnPayment.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'rgba(16, 185, 129, 0.08)';
            pmRow.style.borderColor = 'rgba(16, 185, 129, 0.2)';
        }
    }
    
    updateLabels();
    currentVoucherIndex = -1;
    loadNextMultiVoucherNumber();
}

function togglePaymentMethod() {
    const radioCash = document.getElementById('radio-cash');
    const radioCheck = document.getElementById('radio-check');
    const radioMobile = document.getElementById('radio-mobile');
    
    let method = 'cash';
    if (radioCheck && radioCheck.checked) method = 'cheque';
    else if (radioMobile && radioMobile.checked) method = 'bank';
    
    togglePaymentSection(method);
}

function updateLabels() {
    const type = document.getElementById('voucher-type').value;
    const label = document.getElementById('main-account-label');
    const subHeader = document.getElementById('sub-account-header');
    const subTitle = document.getElementById('sub-accounts-title');
    
    if (type === 'payment') {
        if (label) {
            label.innerText = 'من حساب (صرف)';
            label.style.setProperty('color', '#f87171', 'important');
            label.style.fontSize = '1.1rem';
        }
        if (subHeader) subHeader.innerText = 'إلى حساب (استلام)';
        if (subTitle) subTitle.innerHTML = '<span>👥</span> إلى حساب (استلام)';
    } else {
        if (label) {
            label.innerText = 'إلى حساب (استلام)';
            label.style.setProperty('color', '#34d399', 'important');
            label.style.fontSize = '1.1rem';
        }
        if (subHeader) subHeader.innerText = 'من حساب (دفع)';
        if (subTitle) subTitle.innerHTML = '<span>👥</span> من حساب (دفع)';
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
                <div class="custom-dropdown-list" id="acc-${rowId}-dropdown-list"></div>
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
    if (tbody) tbody.appendChild(tr);
    calcTotal();
    if (typeof window.applySystemNumberFormatting === 'function') window.applySystemNumberFormatting();
}

function removeRow(btn) {
    const tr = btn.closest('tr');
    if (tr) tr.remove();
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

    const totalAmtEl = document.getElementById('total-amount');
    if (totalAmtEl) totalAmtEl.innerText = fmtNum(total);

    const totalRowsEl = document.getElementById('total-rows');
    if (totalRowsEl) totalRowsEl.innerText = amounts.length;
    
    // Update Foreign Amount (القيمة المعادل)
    const rateEl = document.getElementById('exchange-rate');
    const eqEl = document.getElementById('amount-foreign');
    const localHdrEl = document.getElementById('amount-local-hdr');
    if (localHdrEl) {
        localHdrEl.value = fmtNum(total);
    }
    if (rateEl && eqEl) {
        let rate = cleanNum(rateEl.value) || 1;
        let eq = rate === 0 ? 0 : total / rate;
        eqEl.value = fmtNum(eq);
    }
    
    if (typeof window.applySystemNumberFormatting === 'function') {
        window.applySystemNumberFormatting();
    }

    const paymentPanel = document.getElementById('payment-panel');
    if (paymentPanel) {
        if (total > 0) {
            paymentPanel.style.display = 'block';
            if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
        } else {
            paymentPanel.style.display = 'none';
        }
    }
}

function showToast(msg, type='success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icons = { success:'✔️', error:'✖️', info:'ℹ️' };
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
    if (!table) return;
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
    if (menu) {
        const checkboxes = menu.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => {
            if (prefs[cb.value] === true) {
                cb.checked = true;
                toggleColumn(cb.value, true);
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadColumnPrefs();
    
    // Right click on table header
    const tableHeader = document.getElementById('table-header');
    const contextMenu = document.getElementById('column-context-menu');
    
    if (tableHeader && contextMenu) {
        tableHeader.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.classList.add('active');
        });
    }

    // Click anywhere else to hide context menu
    document.addEventListener('click', (e) => {
        if (contextMenu && !contextMenu.contains(e.target) && e.target !== tableHeader && (!tableHeader || !tableHeader.contains(e.target))) {
            contextMenu.classList.remove('active');
        }
    });
});

/* --- Footer Actions --- */
function resetVoucher() {
    const mainAcc = document.getElementById('main-account');
    if (mainAcc) mainAcc.value = '';
    const mainSearch = document.getElementById('main-account-search');
    if (mainSearch) mainSearch.value = '';
    const refEl = document.getElementById('voucher-ref');
    if (refEl) refEl.value = '';
    const descEl = document.getElementById('voucher-desc');
    if (descEl) descEl.value = '';
    
    // Clear dynamic rows
    const tbody = document.getElementById('entries-body');
    if (tbody) tbody.innerHTML = '';
    
    addRow(); 
    addRow();
    if (typeof initPaymentContainers === 'function') initPaymentContainers();
    calcTotal();
    loadNextMultiVoucherNumber();
}

function printVoucher() {
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
    const mainAccount = document.getElementById('main-account').getAttribute('data-id') || document.getElementById('main-account').value;
    const desc = document.getElementById('voucher-desc').value.trim();
    const ref = document.getElementById('voucher-ref').value.trim();
    const currency = document.getElementById('currency').value;
    const exchangeRate = parseFloat(document.getElementById('exchange-rate').value) || 1.0;
    
    const isCashActive = document.getElementById('toggle-cash')?.classList.contains('active');
    const isBankActive = document.getElementById('toggle-bank')?.classList.contains('active');
    const isChequeActive = document.getElementById('toggle-cheque')?.classList.contains('active');

    if (!isCashActive && !isBankActive && !isChequeActive) {
        return showToast('يرجى تحديد طريقة دفع واحدة على الأقل', 'error');
    }
    
    let cashAmount = 0;
    let cashAccount = '';
    let bankAmount = 0;
    let bankAccount = '';
    let bankTransfers = [];
    let chequeAmount = 0;
    let chequeAccount = '';
    let cheques = [];
    let calculatedTotal = 0;

    if (!date) return showToast('يرجى تحديد تاريخ العملية', 'error');
    if (!mainAccount) return showToast('يرجى اختيار الحساب الرئيسي', 'error');

    const rows = document.querySelectorAll('.multi-row');
    const items = [];
    let hasError = false;

    rows.forEach((row, index) => {
        const subAccInput = row.querySelector('.sub-account');
        const searchInput = row.querySelector('input[type="text"][id$="-search"]') || row.querySelector('.multi-input');
        const accName = searchInput ? searchInput.value.trim() : '';
        const acc = subAccInput.getAttribute('data-id') || accName;
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

    // 1. Validate Cash if active
    if (isCashActive) {
        cashAccount = document.getElementById('pay-cash-safe').value;
        cashAmount = parseFloat(document.getElementById('pay-cash-amount').value.replace(/,/g, '')) || 0;
        if (!cashAccount) {
            return showToast("يرجى اختيار صندوق/خزنة الدفع النقدي", "error");
        }
        if (cashAmount <= 0) {
            return showToast("يرجى إدخال مبلغ دفع نقدي صالح", "error");
        }
        calculatedTotal += cashAmount;
    }

    // 2. Validate Bank if active
    if (isBankActive) {
        bankAccount = document.getElementById('pay-bank-account').value;
        if (!bankAccount) {
            return showToast("يرجى اختيار حساب البنك للدفع", "error");
        }
        const transferRows = document.querySelectorAll('.transfer-row');
        let totalTransfersAmount = 0;
        let hasInvalidTransfer = false;

        transferRows.forEach((row, idx) => {
            const trNo = row.querySelector('.transfer-no').value.trim();
            const trNote = row.querySelector('.transfer-note').value.trim();
            const trAmt = cleanNum(row.querySelector('.transfer-amount').value);

            if (!trNo) {
                showToast(`السطر ${idx + 1}: يرجى إدخال رقم حساب التحويل`, "error");
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (trNo.length < 6) {
                showToast(`السطر ${idx + 1}: يجب أن يكون رقم التحويل أكثر من 5 أرقام`, "error");
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (window.isBankReferenceUnique && !window.isBankReferenceUnique(trNo, 'mobile')) {
                showToast(`السطر ${idx + 1}: رقم التحويل مكرر.`, "error");
                hasInvalidTransfer = true;
                return;
            }

            bankTransfers.push({ transferNo: trNo, note: trNote, amount: trAmt });
            totalTransfersAmount += trAmt;
        });

        if (hasInvalidTransfer) return;
        if (totalTransfersAmount <= 0) {
            return showToast("يرجى إدخال مبالغ تحويل بنكي صالحة", "error");
        }
        bankAmount = totalTransfersAmount;
        calculatedTotal += totalTransfersAmount;
    }

    // 3. Validate Cheque if active
    if (isChequeActive) {
        chequeAccount = document.getElementById('pay-cheque-account').value;
        if (!chequeAccount) {
            return showToast("يرجى اختيار حساب صرف الشيكات", "error");
        }
        const chequeRows = document.querySelectorAll('.cheque-row');
        let totalChequesAmount = 0;
        let hasInvalidCheque = false;

        chequeRows.forEach((row, idx) => {
            const chNo = row.querySelector('.cheque-no').value.trim();
            const chBranch = 'البنك الرئيسي';
            const chDate = row.querySelector('.cheque-date').value;
            const chAmt = cleanNum(row.querySelector('.cheque-amount').value);

            if (!chNo || chNo.length < 6) {
                showToast(`السطر ${idx + 1}: يجب أن يكون رقم الشيك أكثر من 5 أرقام`, "error");
                row.querySelector('.cheque-no').focus();
                hasInvalidCheque = true;
                return;
            }
            if (window.isBankReferenceUnique && !window.isBankReferenceUnique(chNo, 'check')) {
                showToast(`السطر ${idx + 1}: رقم الشيك مكرر.`, "error");
                hasInvalidCheque = true;
                return;
            }

            cheques.push({ checkNo: chNo, checkBranch: chBranch, checkDate: chDate, amount: chAmt });
            totalChequesAmount += chAmt;
        });

        if (hasInvalidCheque) return;
        if (totalChequesAmount <= 0) {
            return showToast("يرجى إدخال مبالغ شيكات صالحة", "error");
        }
        chequeAmount = totalChequesAmount;
        calculatedTotal += totalChequesAmount;
    }

    if (Math.abs(calculatedTotal - finalTotal) > 0.02) {
        return showToast(`مجموع مبالغ الدفع تفصيلياً (${calculatedTotal.toFixed(2)}) لا يساوي إجمالي السند المجمع (${finalTotal.toFixed(2)}).`, "error");
    }

    // Build the voucher object
    const voucher = {
        id: 'MV-' + Date.now(), // Generate a unique ID
        voucherNo: (document.getElementById('voucher-no-display') ? document.getElementById('voucher-no-display').value : '') || ref || ('MV-' + Math.floor(Math.random() * 10000)),
        type: type, // 'payment' or 'receipt'
        isGrouped: true,
        mainAccount: mainAccount,
        date: date,
        description: desc,
        totalAmount: finalTotal,
        currency: currency,
        exchangeRate: exchangeRate,
        foreignAmount: finalTotal / exchangeRate,
        paymentMethod: 'hybrid',
        cashAmount: cashAmount,
        cashAccount: cashAccount,
        bankAmount: bankAmount,
        bankAccount: bankAccount,
        bankTransfers: bankTransfers,
        chequeAmount: chequeAmount,
        chequeAccount: chequeAccount,
        cheques: cheques,
        items: items, // Contains {account, amount, statement}
        isPosted: true, // Auto-post to reflect in general ledger immediately
        createdAt: new Date().toISOString()
    };

    // Set fromAccount and toAccount properly for compatibility
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

        // Apply dynamic account balance effects
        if (typeof applyGroupedVoucherEffect === 'function') {
            applyGroupedVoucherEffect(voucher, false);
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

let ACCOUNTS = [];
function buildAccountsArray() {
    ACCOUNTS = [];
    const getTenantKey = window.getTenantKey || ((k) => k);
    const stores = [
        { key: 'erp_customers_v1', prefix: 'cust-' },
        { key: 'erp_suppliers_v1', prefix: 'supp-' },
        { key: 'erp_banks_v1', prefix: 'bank-' },
        { key: 'erp_safes_v1', prefix: 'safe-' },
        { key: 'erp_accounts_v1', prefix: 'exp-' }
    ];

    for (const store of stores) {
        const tKey = getTenantKey(store.key);
        let data = JSON.parse(localStorage.getItem(tKey) || localStorage.getItem(store.key) || '[]');
        if (!Array.isArray(data)) data = Object.values(data);
        
        data.forEach(item => {
            if (item.deleted) return;
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
    
    const getTenantKey = window.getTenantKey || ((k) => k);
    let localSafes = [];
    try { localSafes = JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1')) || localStorage.getItem('erp_safes_v1') || '[]'); } catch(e){}
    let localBanks = [];
    try { localBanks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1') || '[]'); } catch(e){}
    
    const payCashSafe = document.getElementById('pay-cash-safe');
    if (payCashSafe) {
        let safeOpts = '';
        localSafes.forEach(s => {
            const name = s.name || s.safeName || '';
            if (name) safeOpts += `<option value="safe-${s.id}">${name}</option>`;
        });
        payCashSafe.innerHTML = safeOpts;
    }
    const payBankAcc = document.getElementById('pay-bank-account');
    if (payBankAcc) {
        let bankOpts = '';
        localBanks.forEach(b => {
            const name = b.name || b.bankName || '';
            if (name) bankOpts += `<option value="bank-${b.id}">${name}</option>`;
        });
        payBankAcc.innerHTML = bankOpts;
    }
    const payChequeAcc = document.getElementById('pay-cheque-account');
    if (payChequeAcc) {
        let chequeOpts = '';
        localBanks.forEach(b => {
            const name = b.name || b.bankName || '';
            if (name) chequeOpts += `<option value="bank-${b.id}">${name}</option>`;
        });
        localSafes.forEach(s => {
            const name = s.name || s.safeName || '';
            if (name) chequeOpts += `<option value="safe-${s.id}">${name}</option>`;
        });
        payChequeAcc.innerHTML = chequeOpts;
    }
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
        const searchEl = document.getElementById(type + '-search');
        const filterVal = searchEl ? searchEl.value : '';
        renderAccountDropdown(type, filterVal);
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
    const searchEl = document.getElementById(type + '-search');
    const val = searchEl ? searchEl.value : '';
    renderAccountDropdown(type, val);
}

window.selectAccount = function(type, id, name) {
    const hiddenInp = document.getElementById(type);
    if(hiddenInp) {
        hiddenInp.value = name;
        hiddenInp.setAttribute('data-id', id);
    }
    const searchEl = document.getElementById(type + '-search');
    if (searchEl) searchEl.value = name;

    const listEl = document.getElementById(type + '-dropdown-list');
    if(listEl) {
        listEl.classList.remove('show');
        listEl.style.display = 'none';
    }
    
    // Jump logic
    if (type.startsWith('acc-')) {
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

async function loadNextMultiVoucherNumber() {
    const type = document.getElementById('voucher-type') ? document.getElementById('voucher-type').value : 'payment';
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    const getTenantKey = window.getTenantKey || ((k) => k);
    const tKey = getTenantKey(storageKey);
    
    let existingData = [];
    try {
        if (typeof localforage !== 'undefined') {
            existingData = await localforage.getItem(tKey) || [];
        } else {
            existingData = JSON.parse(localStorage.getItem(tKey) || '[]');
        }
    } catch(e) {}
    if (!Array.isArray(existingData)) existingData = Object.values(existingData);
    
    const now = new Date();
    const yy = String(now.getFullYear()).substring(2, 4);
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const datePrefix = yy + mm;
    const prefix = 'M';
    
    let max = 0;
    const groupedVouchers = existingData.filter(v => v.isGrouped);
    groupedVouchers.forEach(v => {
        const vNo = String(v.voucherNo || '');
        const matchPrefix = prefix + datePrefix;
        if (vNo.startsWith(matchPrefix)) {
            const seqStr = vNo.substring(matchPrefix.length);
            const seq = parseInt(seqStr, 10);
            if (!isNaN(seq) && seq > max) {
                max = seq;
            }
        }
    });
    
    const padNum = String(max + 1).padStart(4, '0');
    const displayEl = document.getElementById('voucher-no-display');
    if (displayEl) {
        displayEl.value = `${prefix}${datePrefix}${padNum}`;
    }
}

async function refreshVoucherNavigationList() {
    const type = document.getElementById('voucher-type') ? document.getElementById('voucher-type').value : 'payment';
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    const getTenantKey = window.getTenantKey || ((k) => k);
    const tKey = getTenantKey(storageKey);
    
    let existingData = [];
    try {
        if (typeof localforage !== 'undefined') {
            existingData = await localforage.getItem(tKey) || [];
        } else {
            existingData = JSON.parse(localStorage.getItem(tKey) || '[]');
        }
    } catch(e) {}
    if (!Array.isArray(existingData)) existingData = Object.values(existingData);
    
    currentVoucherList = existingData.filter(v => v.isGrouped);
    currentVoucherList.sort((a, b) => {
        const da = a.date + ' ' + (a.createdAt || '');
        const db = b.date + ' ' + (b.createdAt || '');
        return da.localeCompare(db);
    });
}

async function navigateVoucher(direction) {
    await refreshVoucherNavigationList();
    if (currentVoucherList.length === 0) {
        showToast('لا توجد سندات محفوظة للملاحة.', 'info');
        return;
    }
    
    if (currentVoucherIndex === -1) {
        if (direction === -1) {
            currentVoucherIndex = currentVoucherList.length - 1;
        } else {
            showToast('لا توجد سندات تالية، أنت على مسودة جديدة.', 'info');
            return;
        }
    } else {
        currentVoucherIndex += direction;
        if (currentVoucherIndex < 0) {
            currentVoucherIndex = 0;
            showToast('هذا هو أول سند محفوظ.', 'info');
            return;
        }
        if (currentVoucherIndex >= currentVoucherList.length) {
            currentVoucherIndex = -1;
            resetVoucher();
            return;
        }
    }
    
    const v = currentVoucherList[currentVoucherIndex];
    if (v) {
        loadVoucherIntoForm(v);
    }
}

function loadVoucherIntoForm(v) {
    // 1. Set type (but avoid infinite loop/index resets)
    const btnPayment = document.getElementById('btn-payment');
    const btnReceipt = document.getElementById('btn-receipt');
    const typeInput = document.getElementById('voucher-type');
    if (typeInput) typeInput.value = v.type;
    
    const pmRow = document.getElementById('payment-methods-row');
    if (v.type === 'payment') {
        if (btnPayment) btnPayment.className = 'voucher-type-btn active-payment';
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'rgba(239, 68, 68, 0.08)';
            pmRow.style.borderColor = 'rgba(239, 68, 68, 0.2)';
        }
    } else {
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn active-receipt';
        if (btnPayment) btnPayment.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'rgba(16, 185, 129, 0.08)';
            pmRow.style.borderColor = 'rgba(16, 185, 129, 0.2)';
        }
    }
    updateLabels();

    // 2. Set fields
    document.getElementById('voucher-no-display').value = v.voucherNo || '';
    document.getElementById('voucher-date').value = v.date || '';
    document.getElementById('voucher-ref').value = v.voucherNo || v.ref || '';
    document.getElementById('voucher-desc').value = v.description || '';
    
    const currencyEl = document.getElementById('currency');
    if (currencyEl) currencyEl.value = v.currency || 'SDG';
    
    const rateEl = document.getElementById('exchange-rate');
    if (rateEl) rateEl.value = v.exchangeRate || '1.0';
    
    // 3. Set main account
    const mainAccHidden = document.getElementById('main-account');
    if (mainAccHidden) {
        mainAccHidden.value = getAccountName(v.mainAccount) || '';
        mainAccHidden.setAttribute('data-id', v.mainAccount || '');
    }
    const mainAccSearch = document.getElementById('main-account-search');
    if (mainAccSearch) {
        mainAccSearch.value = getAccountName(v.mainAccount) || '';
    }
    
    // 4. Set payment method radio
    const radio = document.querySelector(`input[name="payment_method"][value="${v.paymentMethod || 'cash'}"]`);
    if (radio) {
        radio.checked = true;
        togglePaymentMethod();
    }
    
    if (typeof loadVoucherPaymentDetails === 'function') {
        loadVoucherPaymentDetails(v);
    }
    
    // 5. Populate items table
    const tbody = document.getElementById('entries-body');
    if (tbody) tbody.innerHTML = '';
    
    if (v.items && Array.isArray(v.items)) {
        v.items.forEach(item => {
            addRowWithData(item);
        });
    }
    
    calcTotal();
    showToast(`تم عرض السند ${v.voucherNo} بنجاح`, 'success');
}

function getAccountName(id) {
    if (!id) return '';
    const cleanId = String(id).replace(/^(cust|supp|bank|safe|exp)-/, '');
    const matched = ACCOUNTS.find(a => String(a.id) === String(id) || String(a.rawId) === String(cleanId) || String(a.name) === String(id));
    return matched ? matched.name : id;
}

function addRowWithData(item) {
    multiRowIdCounter++;
    const rowId = multiRowIdCounter;
    const tbody = document.getElementById('entries-body');
    const tr = document.createElement('tr');
    tr.className = 'multi-row';
    
    const accName = getAccountName(item.account);

    tr.innerHTML = `
        <td>
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" id="acc-${rowId}" class="sub-account" value="${accName}">
                <input type="text" class="multi-input" id="acc-${rowId}-search" placeholder="ابحث عن حساب..." onfocus="showAccountDropdown('acc-${rowId}')" onblur="hideAccountDropdownDelayed('acc-${rowId}')" oninput="filterAccountDropdown('acc-${rowId}')" style="width: 100%; box-sizing: border-box; background: transparent; border: none; color: inherit; padding: 10px;" value="${accName}">
                <div class="custom-dropdown-list" id="acc-${rowId}-dropdown-list"></div>
            </div>
        </td>
        <td><input type="text" class="multi-input sub-amount" placeholder="0.00" onblur="formatInput(this)" oninput="restrictToNumbers(this); calcTotal();" style="font-family: monospace; font-weight: bold; font-size: 1.1rem; color: #10b981;" value="${item.amount > 0 ? fmtNum(item.amount) : ''}"></td>
        <td class="col-desc"><input type="text" class="multi-input sub-desc" placeholder="بيان هذا الحساب..." value="${item.statement || ''}"></td>
        <td class="col-cost-center"><input type="text" class="multi-input sub-cost-center" placeholder="مركز التكلفة..." value="${item.costCenter || ''}"></td>
        <td class="col-note"><input type="text" class="multi-input sub-note" placeholder="ملحوظة..." value="${item.note || ''}"></td>
        <td class="col-check-no"><input type="text" class="multi-input sub-check-no" placeholder="رقم الشيك..." value="${item.checkNo || ''}"></td>
        <td class="col-check-date"><input type="date" class="multi-input sub-check-date" value="${item.checkDate || ''}"></td>
        <td class="col-check-due"><input type="date" class="multi-input sub-check-due" value="${item.checkDueDate || ''}"></td>
        <td style="text-align: center;"><button class="btn-del" onclick="removeRow(this)" title="حذف السطر">✖</button></td>
    `;
    if (tbody) tbody.appendChild(tr);
}

function applyGroupedVoucherEffect(voucher, isRevert = false) {
    if (!voucher) return;
    const type = voucher.type; // 'payment' or 'receipt'
    const multiplier = isRevert ? -1 : 1;
    
    // 1. Update Main Account (with split payment support)
    function updateMainAccBalance(accId, amt) {
        if (!accId || amt <= 0) return;
        const amtEffect = (type === 'payment') ? -(amt * multiplier) : (amt * multiplier);
        if (accId.startsWith('safe-')) {
            let id = accId.substring(5);
            let safes = [];
            try { safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || []; } catch(e){}
            let idx = safes.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
            }
        } else if (accId.startsWith('bank-')) {
            let id = accId.substring(5);
            let banks = [];
            try { banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || []; } catch(e){}
            let idx = banks.findIndex(b => String(b.id) === String(id));
            if (idx > -1) {
                banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            }
        }
    }

    if (voucher.paymentMethod === 'hybrid') {
        if (voucher.cashAmount > 0 && voucher.cashAccount) {
            updateMainAccBalance(voucher.cashAccount, voucher.cashAmount);
        }
        if (voucher.bankAmount > 0 && voucher.bankAccount) {
            updateMainAccBalance(voucher.bankAccount, voucher.bankAmount);
        }
        if (voucher.chequeAmount > 0 && voucher.chequeAccount) {
            updateMainAccBalance(voucher.chequeAccount, voucher.chequeAmount);
        }
    } else {
        updateMainAccBalance(mainAccId, voucher.totalAmount);
    }

    // 2. Update Items Accounts
    if (voucher.items && Array.isArray(voucher.items)) {
        voucher.items.forEach(item => {
            const itemAccId = item.account;
            if (!itemAccId) return;
            const amt = item.amount * multiplier;
            
            let amtEffect = 0;
            if (type === 'payment') {
                if (itemAccId.startsWith('cust-') || itemAccId.startsWith('supp-')) {
                    amtEffect = -amt;
                } else {
                    amtEffect = amt;
                }
            } else { // receipt
                if (itemAccId.startsWith('cust-') || itemAccId.startsWith('supp-')) {
                    amtEffect = -amt;
                } else {
                    amtEffect = amt;
                }
            }

            if (itemAccId.startsWith('cust-')) {
                let id = itemAccId.substring(5);
                let custs = [];
                try { custs = JSON.parse(localStorage.getItem('erp_customers_v1')) || []; } catch(e){}
                let idx = custs.findIndex(c => String(c.id) === String(id));
                if (idx > -1) {
                    custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
                }
            } else if (itemAccId.startsWith('supp-')) {
                let id = itemAccId.substring(5);
                let sups = [];
                try { sups = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []; } catch(e){}
                let idx = sups.findIndex(s => String(s.id) === String(id));
                if (idx > -1) {
                    sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem('erp_suppliers_v1', JSON.stringify(sups));
                }
            } else if (itemAccId.startsWith('bank-')) {
                let id = itemAccId.substring(5);
                let banks = [];
                try { banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || []; } catch(e){}
                let idx = banks.findIndex(b => String(b.id) === String(id));
                if (idx > -1) {
                    banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
                }
            } else if (itemAccId.startsWith('safe-')) {
                let id = itemAccId.substring(5);
                let safes = [];
                try { safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || []; } catch(e){}
                let idx = safes.findIndex(s => String(s.id) === String(id));
                if (idx > -1) {
                    safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
                }
            } else if (itemAccId.startsWith('exp-')) {
                let id = itemAccId.substring(4);
                let accounts = [];
                try { accounts = JSON.parse(localStorage.getItem('erp_accounts_v1')) || []; } catch(e){}
                let idx = accounts.findIndex(a => String(a.id) === String(id));
                if (idx > -1) {
                    accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem('erp_accounts_v1', JSON.stringify(accounts));
                }
            }
        });
    }
}

// ═══════════════════════════════════════════════════════
// Inline Payment Panel & Formatting Logic (Grouped Vouchers)
// ═══════════════════════════════════════════════════════

window.togglePaymentSection = function(method) {
    if (typeof playClickSound === 'function') {
        playClickSound();
    }

    const activeBtn = document.getElementById(`toggle-${method}`);
    if (activeBtn) {
        activeBtn.classList.toggle('active');
    }

    const isSectionActive = activeBtn && activeBtn.classList.contains('active');
    const targetSecMap = {
        'cash': 'pay-cash-section',
        'bank': 'pay-bank-section',
        'cheque': 'pay-cheque-section'
    };
    
    const secId = targetSecMap[method];
    if (secId) {
        document.getElementById(secId).style.display = isSectionActive ? 'flex' : 'none';
    }

    // Auto fill remaining amount if we just enabled it
    if (isSectionActive) {
        const totalAmount = cleanNum(document.getElementById('total-amount').innerText);
        let cashAmt = 0;
        if (document.getElementById('toggle-cash')?.classList.contains('active') && method !== 'cash') {
            cashAmt = parseFloat(document.getElementById('pay-cash-amount').value.replace(/,/g, '')) || 0;
        }
        let bankAmt = 0;
        if (document.getElementById('toggle-bank')?.classList.contains('active') && method !== 'bank') {
            document.querySelectorAll('.transfer-row .transfer-amount').forEach(el => bankAmt += parseFloat(el.value.replace(/,/g, '')) || 0);
        }
        let chequeAmt = 0;
        if (document.getElementById('toggle-cheque')?.classList.contains('active') && method !== 'cheque') {
            document.querySelectorAll('.cheque-row .cheque-amount').forEach(el => chequeAmt += parseFloat(el.value.replace(/,/g, '')) || 0);
        }
        const currentUnpaid = Math.max(0, totalAmount - (cashAmt + bankAmt + chequeAmt));
        
        if (method === 'cash') {
            document.getElementById('pay-cash-amount').value = currentUnpaid.toFixed(2);
        } else if (method === 'bank') {
            const row = document.querySelector('.transfer-row .transfer-amount');
            if (row) row.value = currentUnpaid.toFixed(2);
        } else if (method === 'cheque') {
            const row = document.querySelector('.cheque-row .cheque-amount');
            if (row) row.value = currentUnpaid.toFixed(2);
        }
    }

    recalcPaymentSummary();
};

window.recalcPaymentSummary = function() {
    const totalAmount = cleanNum(document.getElementById('total-amount').innerText);
    
    let cashAmt = 0;
    if (document.getElementById('toggle-cash')?.classList.contains('active')) {
        cashAmt = parseFloat(document.getElementById('pay-cash-amount')?.value.replace(/,/g, '')) || 0;
    }
    
    let bankAmt = 0;
    if (document.getElementById('toggle-bank')?.classList.contains('active')) {
        const transferRows = document.querySelectorAll('.transfer-row');
        transferRows.forEach(row => {
            bankAmt += parseFloat(row.querySelector('.transfer-amount')?.value.replace(/,/g, '')) || 0;
        });
    }
    
    let chequeAmt = 0;
    if (document.getElementById('toggle-cheque')?.classList.contains('active')) {
        const chequeRows = document.querySelectorAll('.cheque-row');
        chequeRows.forEach(row => {
            chequeAmt += parseFloat(row.querySelector('.cheque-amount')?.value.replace(/,/g, '')) || 0;
        });
    }
    
    const paidSum = cashAmt + bankAmt + chequeAmt;
    const remaining = totalAmount - paidSum;
    
    const progCash = document.getElementById('prog-cash');
    const progBank = document.getElementById('prog-bank');
    const progCheque = document.getElementById('prog-cheque');
    
    if (progCash) progCash.style.width = totalAmount > 0 ? (cashAmt / totalAmount * 100) + '%' : '0%';
    if (progBank) progBank.style.width = totalAmount > 0 ? (bankAmt / totalAmount * 100) + '%' : '0%';
    if (progCheque) progCheque.style.width = totalAmount > 0 ? (chequeAmt / totalAmount * 100) + '%' : '0%';

    const badge = document.getElementById('payment-status-badge');

    if (totalAmount > 0) {
        if (Math.abs(remaining) < 0.02) {
            if (badge) {
                badge.innerText = '💳 مدفوعة بالكامل';
                badge.style.background = 'rgba(16, 185, 129, 0.15)';
                badge.style.color = '#34d399';
                badge.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.2)';
            }
        } else if (remaining > 0) {
            if (badge) {
                badge.innerText = `⚠️ غير مكتملة (متبقي: ${remaining.toFixed(2)})`;
                badge.style.background = 'rgba(245, 158, 11, 0.15)';
                badge.style.color = '#f59e0b';
                badge.style.boxShadow = '0 0 10px rgba(245, 158, 11, 0.2)';
            }
        } else {
            if (badge) {
                badge.innerText = `⚠️ زيادة في الدفع (فارق: ${Math.abs(remaining).toFixed(2)})`;
                badge.style.background = 'rgba(239, 68, 68, 0.15)';
                badge.style.color = '#f87171';
                badge.style.boxShadow = '0 0 10px rgba(239, 68, 68, 0.2)';
            }
        }
    } else {
        if (badge) {
            badge.innerText = '⚠️ في انتظار إدخال المبالغ';
            badge.style.background = 'rgba(239, 68, 68, 0.15)';
            badge.style.color = '#f87171';
            badge.style.boxShadow = '0 0 10px rgba(239, 68, 68, 0.2)';
        }
    }
};

function playClickSound() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(800, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.08);
        gain.gain.setValueAtTime(0.08, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.08);
    } catch(e) {}
}

// Event Delegation for dynamically added sub-amount input fields
document.addEventListener('focus', function(event) {
    const input = event.target;
    if (input.classList.contains('sub-amount')) {
        const val = parseFloat(input.value.replace(/,/g, '')) || 0;
        if (val === 0) {
            input.value = '';
        }
    }
}, true);

document.addEventListener('blur', function(event) {
    const input = event.target;
    if (input.classList.contains('sub-amount')) {
        const val = parseFloat(input.value.replace(/,/g, '')) || 0;
        if (input.value.trim() === '') {
            input.value = '';
        } else {
            let parts = val.toFixed(2).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            input.value = parts.join('.');
        }
        calcTotal();
    }
}, true);

// ═══════════════════════════════════════════════════════
// Multiple Bank Transfers & Cheques Dynamic Manipulation
// ═══════════════════════════════════════════════════════

window.addTransferRow = function(no = '', note = '', amt = 0) {
    const container = document.getElementById('transfers-container');
    if (!container) return;

    const isFirst = (container.children.length === 0);
    const row = document.createElement('div');
    row.className = 'transfer-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; width: 100%;';
    
    const labelHTML = isFirst 
        ? `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem;">🏦</span>
               <span style="font-weight: 800; color: #60a5fa; font-size: 0.9rem;">تفاصيل التحويلة</span>
           </div>`
        : `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem; visibility: hidden;">🏦</span>
               <span style="font-weight: 800; color: #60a5fa; font-size: 0.9rem; opacity: 0.6;">تحويلة إضافية</span>
           </div>`;

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addTransferRow()" class="pay-toggle-btn" style="background: rgba(59,130,246,0.2); border: 1px solid #60a5fa; color:#60a5fa; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeTransferRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div style="flex: 1.2; min-width: 150px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">رقم الحساب:</label>
            <input type="text" class="v-input ctrl-input transfer-no" value="${no}" placeholder="رقم حساب التحويل..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff;">
        </div>
        <div style="flex: 1.5; min-width: 180px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">ملاحظات:</label>
            <input type="text" class="v-input ctrl-input transfer-note" value="${note}" placeholder="ملاحظات التحويل..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff;">
        </div>
        <div style="flex: 0.8; min-width: 110px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">المبلغ:</label>
            <input type="text" class="v-input ctrl-input transfer-amount" value="${amt > 0 ? fmtNum(amt) : ''}" placeholder="0.00" oninput="restrictToNumbers(this)" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="display: flex; justify-content: flex-end; width: 40px; flex: none;">
            ${buttonHTML}
        </div>
    `;
    container.appendChild(row);
    
    if (no === '' && typeof playClickSound === 'function') playClickSound();
    syncFirstRowAmount();
};

window.removeTransferRow = function(btn) {
    const container = document.getElementById('transfers-container');
    const rows = container.querySelectorAll('.transfer-row');
    if (rows.length <= 1) {
        showToast('يجب إدخال تحويل واحد على الأقل', 'error');
        return;
    }
    btn.closest('.transfer-row').remove();
    syncFirstRowAmount();
};

window.addChequeRow = function(branch = '', no = '', date = '', amt = 0) {
    const container = document.getElementById('cheques-container');
    if (!container) return;

    const isFirst = (container.children.length === 0);
    const row = document.createElement('div');
    row.className = 'cheque-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; width: 100%;';
    
    const labelHTML = isFirst 
        ? `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem;">✍️</span>
               <span style="font-weight: 800; color: #c084fc; font-size: 0.9rem;">تفاصيل الشيك</span>
           </div>`
        : `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem; visibility: hidden;">✍️</span>
               <span style="font-weight: 800; color: #c084fc; font-size: 0.9rem; opacity: 0.6;">شيك إضافي</span>
           </div>`;

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addChequeRow()" class="pay-toggle-btn" style="background: rgba(139,92,246,0.2); border: 1px solid #c084fc; color:#c084fc; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeChequeRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div style="flex: 0.8; min-width: 110px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">المبلغ:</label>
            <input type="text" class="v-input ctrl-input cheque-amount" value="${amt > 0 ? fmtNum(amt) : ''}" placeholder="0.00" oninput="restrictToNumbers(this)" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 140px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">اسم البنك:</label>
            <input type="text" class="v-input ctrl-input cheque-branch" value="${branch}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div style="flex: 1; min-width: 110px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">رقم الشيك:</label>
            <input type="text" class="v-input ctrl-input cheque-no" value="${no}" placeholder="رقم الشيك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div style="flex: 1.2; min-width: 160px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">تاريخ الاستحقاق:</label>
            <input type="date" class="v-input ctrl-input cheque-date" value="${date}" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 4px; width: 100%; border-radius: 6px; color:#fff;">
        </div>
        <div style="display: flex; justify-content: flex-end; width: 40px; flex: none;">
            ${buttonHTML}
        </div>
    `;
    container.appendChild(row);
    
    if (no === '' && typeof playClickSound === 'function') playClickSound();
    syncFirstRowAmount();
};

window.removeChequeRow = function(btn) {
    const container = document.getElementById('cheques-container');
    const rows = container.querySelectorAll('.cheque-row');
    if (rows.length <= 1) {
        showToast('يجب إدخال شيك واحد على الأقل', 'error');
        return;
    }
    btn.closest('.cheque-row').remove();
    syncFirstRowAmount();
};

window.initPaymentContainers = function() {
    const transfersContainer = document.getElementById('transfers-container');
    const chequesContainer = document.getElementById('cheques-container');
    if (transfersContainer) {
        transfersContainer.innerHTML = '';
        addTransferRow('', '', 0);
    }
    if (chequesContainer) {
        chequesContainer.innerHTML = '';
        addChequeRow('', '', '', 0);
    }
};

window.syncFirstRowAmount = function() {
    const totalAmount = cleanNum(document.getElementById('total-amount').innerText);
    
    const isCashActive = document.getElementById('toggle-cash')?.classList.contains('active');
    const isBankActive = document.getElementById('toggle-bank')?.classList.contains('active');
    const isChequeActive = document.getElementById('toggle-cheque')?.classList.contains('active');
    
    const transferRows = document.querySelectorAll('.transfer-row');
    const chequeRows = document.querySelectorAll('.cheque-row');
    
    const activeCount = (isCashActive ? 1 : 0) + (isBankActive ? 1 : 0) + (isChequeActive ? 1 : 0);
    if (activeCount === 1) {
        if (isCashActive) {
            const el = document.getElementById('pay-cash-amount');
            if (el) el.value = fmtNum(totalAmount);
        }
        if (isBankActive && transferRows.length === 1) {
            transferRows[0].querySelector('.transfer-amount').value = fmtNum(totalAmount);
        }
        if (isChequeActive && chequeRows.length === 1) {
            chequeRows[0].querySelector('.cheque-amount').value = fmtNum(totalAmount);
        }
    }
};

window.loadVoucherPaymentDetails = function(v) {
    const transfersContainer = document.getElementById('transfers-container');
    const chequesContainer = document.getElementById('cheques-container');
    if (transfersContainer) transfersContainer.innerHTML = '';
    if (chequesContainer) chequesContainer.innerHTML = '';

    // Clear active states
    document.querySelectorAll('.pay-toggle-btn').forEach(btn => btn.classList.remove('active'));

    const secCash = document.getElementById('pay-cash-section');
    const secBank = document.getElementById('pay-bank-section');
    const secCheque = document.getElementById('pay-cheque-section');

    if (secCash) secCash.style.display = 'none';
    if (secBank) secBank.style.display = 'none';
    if (secCheque) secCheque.style.display = 'none';

    if (v.paymentMethod === 'hybrid') {
        if (v.cashAmount > 0) {
            document.getElementById('toggle-cash')?.classList.add('active');
            if (secCash) secCash.style.display = 'flex';
            if (document.getElementById('pay-cash-safe')) document.getElementById('pay-cash-safe').value = v.cashAccount || '';
            if (document.getElementById('pay-cash-amount')) document.getElementById('pay-cash-amount').value = fmtNum(v.cashAmount);
        }
        if (v.bankAmount > 0) {
            document.getElementById('toggle-bank')?.classList.add('active');
            if (secBank) secBank.style.display = 'flex';
            if (document.getElementById('pay-bank-account')) document.getElementById('pay-bank-account').value = v.bankAccount || '';
            if (v.bankTransfers && Array.isArray(v.bankTransfers)) {
                v.bankTransfers.forEach(tr => {
                    addTransferRow(tr.transferNo, tr.note, tr.amount);
                });
            }
        }
        if (v.chequeAmount > 0) {
            document.getElementById('toggle-cheque')?.classList.add('active');
            if (secCheque) secCheque.style.display = 'flex';
            if (document.getElementById('pay-cheque-account')) document.getElementById('pay-cheque-account').value = v.chequeAccount || '';
            if (v.cheques && Array.isArray(v.cheques)) {
                v.cheques.forEach(ch => {
                    addChequeRow(ch.checkBranch, ch.checkNo, ch.checkDate, ch.amount);
                });
            }
        }
    } else {
        const method = v.paymentMethod || 'cash';
        if (method === 'cash') {
            document.getElementById('toggle-cash')?.classList.add('active');
            if (secCash) secCash.style.display = 'flex';
            if (document.getElementById('pay-cash-safe')) document.getElementById('pay-cash-safe').value = v.fromAccount || '';
            if (document.getElementById('pay-cash-amount')) document.getElementById('pay-cash-amount').value = fmtNum(v.totalAmount);
        } else if (method === 'mobile') {
            document.getElementById('toggle-bank')?.classList.add('active');
            if (secBank) secBank.style.display = 'flex';
            if (document.getElementById('pay-bank-account')) document.getElementById('pay-bank-account').value = v.fromAccount || '';
            if (v.bankTransfers && Array.isArray(v.bankTransfers) && v.bankTransfers.length > 0) {
                v.bankTransfers.forEach(tr => {
                    addTransferRow(tr.transferNo, tr.note, tr.amount);
                });
            } else {
                addTransferRow(v.mobileTransferNo || '', v.mobileNote || '', v.totalAmount || 0);
            }
        } else if (method === 'check') {
            document.getElementById('toggle-cheque')?.classList.add('active');
            if (secCheque) secCheque.style.display = 'flex';
            if (document.getElementById('pay-cheque-account')) document.getElementById('pay-cheque-account').value = v.fromAccount || '';
            if (v.cheques && Array.isArray(v.cheques) && v.cheques.length > 0) {
                v.cheques.forEach(ch => {
                    addChequeRow(ch.checkBranch, ch.checkNo, ch.checkDate, ch.amount);
                });
            } else {
                addChequeRow(v.checkBranch || '', v.checkNo || '', v.checkDate || '', v.totalAmount || 0);
            }
        }
    }
    recalcPaymentSummary();
};

// Hook setup into document load
document.addEventListener('DOMContentLoaded', () => {
    initPaymentContainers();
});
