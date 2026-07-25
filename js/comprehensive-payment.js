// Comprehensive Voucher Logic (dynamic)

let currentType = 'cash';
buildAccountsArray();

document.addEventListener('DBReady', () => {
    // Initialize date and serial
    const now = new Date();
    document.getElementById('current-date').innerText = now.toISOString().split('T')[0];
    
    // Auto Generate Serial
    const existingVouchers = JSON.parse(localStorage.getItem('erp_comprehensive_payments_v1')) || [];
    const nextSerial = 1000 + existingVouchers.length + 1;
    document.getElementById('serial-number').innerText = 'CP-' + nextSerial;

    // Load Banks/Safes for Depository/Withdrawal
    const banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    // Old deposit-account populate logic removed

    // Load Customers, GL Accounts, AND Banks/Safes for Smart Filter
    const customers = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    const glAccounts = JSON.parse(localStorage.getItem('erp_gl_v1')) || [];

    buildAccountsArray();

    // Smart Search Logic
    // Old custom search logic removed
});

// Toggle Payment Type
function setType(type) {
    currentType = type;
    document.querySelectorAll('.type-btn').forEach(btn => {
        if(btn.dataset.type === type) btn.classList.add('active');
        else btn.classList.remove('active');
    });
    document.querySelectorAll('.dynamic-section').forEach(sec => sec.classList.remove('active'));
    const sec = document.getElementById('section-' + type);
    if(sec) sec.classList.add('active');
}

// Save Voucher
function saveVoucher() {
    const accountId = document.getElementById('account-id').value;
    const amount = Number(document.getElementById('amount').value);
    const depositAccount = document.getElementById('deposit-account').value;

    if (!accountId) {
        alert('الرجاء تحديد الحساب الذكي الموجه له/منه.');
        return;
    }
    if (!amount || amount <= 0) {
        alert('الرجاء إدخال مبلغ صحيح.');
        return;
    }
    if (!depositAccount) {
        alert('الرجاء اختيار الخزينة/البنك الأساسي.');
        return;
    }

    const voucher = {
        id: document.getElementById('serial-number').innerText,
        date: document.getElementById('current-date').innerText,
        time: new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
        accountId: accountId,
        accountName: document.getElementById('from-account-search').value,
        costCenter: document.getElementById('cost-center').value,
        amount: amount,
        description: document.getElementById('main-desc').value,
        currency: document.getElementById('currency').value,
        exchangeRate: document.getElementById('exchange-rate').value,
        beneficiary: document.getElementById('beneficiary').value,
        depositAccount: depositAccount,
        depositDesc: document.getElementById('deposit-desc').value,
        paymentType: currentType,
        timestamp: new Date().toISOString()
    };

    if (currentType === 'check') {
        const checkNumber = document.getElementById('check-number').value.trim();
        const checkBank = document.getElementById('check-bank').value.trim();
        
        if (!checkBank) {
            alert('الرجاء إدخال اسم البنك للشيك.');
            return;
        }
        if (!checkNumber) {
            alert('الرجاء إدخال رقم الشيك.');
            return;
        }
        if (checkNumber.length < 6) {
            alert('يجب أن يكون رقم الشيك 6 أرقام على الأقل.');
            return;
        }
        const currentVoucherNo = document.getElementById('serial-number').innerText;
        if (!window.isBankReferenceUnique(checkNumber, 'check', currentVoucherNo)) {
            window.showDuplicateWarningModal(checkNumber, 'check');
            return;
        }
        
        voucher.checkNumber = checkNumber;
        voucher.checkDate = document.getElementById('check-date').value;
        voucher.checkDueDate = document.getElementById('check-due-date').value;
        voucher.checkBank = checkBank;
    } else if (currentType === 'bank') {
        const bankRef = document.getElementById('bank-ref').value.trim();
        if (!bankRef) {
            alert('الرجاء إدخال رقم التحويلة البنكية.');
            return;
        }
        if (bankRef.length < 6) {
            alert('يجب أن يكون رقم التحويلة 6 أرقام على الأقل.');
            return;
        }
        const currentVoucherNo = document.getElementById('serial-number').innerText;
        if (!window.isBankReferenceUnique(bankRef, 'mobile', currentVoucherNo)) {
            window.showDuplicateWarningModal(bankRef, 'mobile');
            return;
        }
        
        voucher.bankRef = bankRef;
        voucher.bankSourceName = document.getElementById('bank-source-name').value;
        voucher.bankDate = document.getElementById('bank-date').value;
    } else if (currentType === 'mobile') {
        const mobileRef = document.getElementById('mobile-ref').value.trim();
        if (!mobileRef) {
            alert('الرجاء إدخال رقم المرجع للتحويل.');
            return;
        }
        if (mobileRef.length < 6) {
            alert('يجب أن يكون رقم المرجع للتحويل 6 أرقام على الأقل.');
            return;
        }
        const currentVoucherNo = document.getElementById('serial-number').innerText;
        if (!window.isBankReferenceUnique(mobileRef, 'mobile', currentVoucherNo)) {
            window.showDuplicateWarningModal(mobileRef, 'mobile');
            return;
        }
        
        voucher.mobileWallet = document.getElementById('mobile-wallet').value;
        voucher.mobileNumber = document.getElementById('mobile-number').value;
        voucher.mobileRef = mobileRef;
    }

    let existingVouchers = JSON.parse(localStorage.getItem('erp_comprehensive_payments_v1')) || [];
    existingVouchers.push(voucher);
    localStorage.setItem('erp_comprehensive_payments_v1', JSON.stringify(existingVouchers));

    alert('تم حفظ السند الشامل بنجاح!');
    window.location.href = 'vouchers-hub.php';
}


// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Accounts
// ═══════════════════════════════════════════════════════
let ACCOUNTS = [];
function buildAccountsArray() {
    ACCOUNTS = [];
    const stores = [
        { key: 'erp_customers_v1', prefix: 'cust-', label: 'عميل' },
        { key: 'erp_suppliers_v1', prefix: 'supp-', label: 'مورد' },
        { key: 'erp_banks_v1', prefix: 'bank-', label: 'بنك' },
        { key: 'erp_safes_v1', prefix: 'safe-', label: 'خزينة' },
        { key: 'erp_gl_v1', prefix: 'gl-', label: 'حساب عام' } // Note: Comprehensive uses erp_gl_v1 instead of erp_accounts_v1 for GL!
    ];

    for (const store of stores) {
        let data = JSON.parse(localStorage.getItem(store.key) || '[]');
        if (!Array.isArray(data)) data = Object.values(data);
        
        data.forEach(item => {
            if(item.deleted) return;
            const name = item.name || item.accountName || item.customerName || item.supplierName || item.bankName || item.safeName;
            const id = item.id || item.code || '';
            if (name) {
                ACCOUNTS.push({ id: store.prefix + id, name: name, rawId: id, storeType: store.prefix.replace('-','') });
            }
        });
    }
}

function renderAccountDropdown(type, filter = '') {
    if (ACCOUNTS.length === 0) buildAccountsArray();

    const listEl = document.getElementById(type + '-account-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
    
    try {
        let filtered = ACCOUNTS;
        
        // Comprehensive specific filtering: Deposit account should only show banks and safes
        if (type === 'deposit') {
            filtered = ACCOUNTS.filter(a => a.id.startsWith('bank-') || a.id.startsWith('safe-'));
        }

        filtered = filtered.filter(a => {
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
            if (a.storeType === 'cust') typeTag = '<span style="font-size:0.75rem; color:#60a5fa; background:rgba(96,165,250,0.1); padding:2px 6px; border-radius:4px;">عميل</span>';
            else if (a.storeType === 'supp') typeTag = '<span style="font-size:0.75rem; color:#f472b6; background:rgba(244,114,182,0.1); padding:2px 6px; border-radius:4px;">مورد</span>';
            else if (a.storeType === 'bank') typeTag = '<span style="font-size:0.75rem; color:#a78bfa; background:rgba(167,139,250,0.1); padding:2px 6px; border-radius:4px;">بنك</span>';
            else if (a.storeType === 'safe') typeTag = '<span style="font-size:0.75rem; color:#34d399; background:rgba(52,211,153,0.1); padding:2px 6px; border-radius:4px;">خزينة</span>';
            else if (a.storeType === 'gl') typeTag = '<span style="font-size:0.75rem; color:#fbbf24; background:rgba(251,191,36,0.1); padding:2px 6px; border-radius:4px;">حساب عام</span>';

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
    const listEl = document.getElementById(type + '-account-dropdown-list');
    if (listEl) {
        renderAccountDropdown(type, document.getElementById(type + '-account-search').value);
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

window.hideAccountDropdownDelayed = function(type) {
    setTimeout(() => {
        const listEl = document.getElementById(type + '-account-dropdown-list');
        if (listEl) {
            listEl.classList.remove('show');
            listEl.style.display = 'none';
        }
    }, 200);
}

window.filterAccountDropdown = function(type) {
    const val = document.getElementById(type + '-account-search').value;
    renderAccountDropdown(type, val);
}

window.selectAccount = function(type, id, name) {
    document.getElementById(type + '-account').value = id;
    document.getElementById(type + '-account-search').value = name;
    
    // In comprehensive payment, there is a specific ID used for save logic which was 'account-id'
    if (type === 'from') {
        const accIdEl = document.getElementById('account-id');
        if (accIdEl) accIdEl.value = id;
    }
    
    const listEl = document.getElementById(type + '-account-dropdown-list');
    if(listEl) {
        listEl.classList.remove('show');
        listEl.style.display = 'none';
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
