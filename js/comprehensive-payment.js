// Comprehensive Voucher Logic (dynamic)

let currentType = 'cash';
let allAccounts = [];

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
    const depSelect = document.getElementById('deposit-account');
    depSelect.innerHTML = '<option value="">-- اختر الحساب المعني --</option>';
    banks.filter(b => !b.deleted).forEach(b => {
        depSelect.innerHTML += `<option value="${b.id}">${b.name} (${b.type === 'safe' ? 'خزينة' : 'بنك'})</option>`;
    });

    // Load Customers, GL Accounts, AND Banks/Safes for Smart Filter
    const customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
    const glAccounts = JSON.parse(localStorage.getItem('erp_gl_v1')) || [];

    allAccounts = [
        ...customers.filter(c => !c.deleted).map(c => ({ id: c.id, name: c.name, type: 'عميل' })),
        ...glAccounts.filter(g => !g.deleted).map(g => ({ id: g.id, name: g.name, type: 'حساب عام' })),
        ...banks.filter(b => !b.deleted).map(b => ({ id: b.id, name: b.name, type: b.type === 'safe' ? 'خزينة' : 'بنك' }))
    ];

    // Smart Search Logic
    const searchInput = document.getElementById('account-search');
    const searchResults = document.getElementById('account-results');
    const idInput = document.getElementById('account-id');

    searchInput.addEventListener('input', (e) => {
        const val = e.target.value.toLowerCase().trim();
        searchResults.innerHTML = '';
        if (val.length < 1) {
            searchResults.style.display = 'none';
            return;
        }

        const matches = allAccounts.filter(acc => 
            acc.name.toLowerCase().includes(val) || acc.id.toLowerCase().includes(val)
        ).slice(0, 10);

        if (matches.length > 0) {
            searchResults.style.display = 'block';
            matches.forEach(match => {
                const div = document.createElement('div');
                div.className = 'search-item';
                div.innerHTML = `<span>${match.name}</span> <span class="search-item-type">${match.type}</span>`;
                div.onclick = () => {
                    searchInput.value = match.name;
                    idInput.value = match.id;
                    searchResults.style.display = 'none';
                    // Auto-fill description
                    document.getElementById('main-desc').value = 'دفعة إلى الحساب: ' + match.name;
                };
                searchResults.appendChild(div);
            });
        } else {
            searchResults.style.display = 'block';
            searchResults.innerHTML = '<div style="padding:10px; color:#94a3b8; text-align:center;">لا توجد نتائج</div>';
        }
    });

    document.addEventListener('click', (e) => {
        if(!e.target.closest('.search-wrapper')) {
            searchResults.style.display = 'none';
        }
    });
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
        accountId: accountId,
        accountName: document.getElementById('account-search').value,
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
        voucher.checkNumber = document.getElementById('check-number').value;
        voucher.checkDate = document.getElementById('check-date').value;
        voucher.checkDueDate = document.getElementById('check-due-date').value;
        voucher.checkBank = document.getElementById('check-bank').value;
    } else if (currentType === 'bank') {
        voucher.bankRef = document.getElementById('bank-ref').value;
        voucher.bankSourceName = document.getElementById('bank-source-name').value;
        voucher.bankDate = document.getElementById('bank-date').value;
    } else if (currentType === 'mobile') {
        voucher.mobileWallet = document.getElementById('mobile-wallet').value;
        voucher.mobileNumber = document.getElementById('mobile-number').value;
        voucher.mobileRef = document.getElementById('mobile-ref').value;
    }

    let existingVouchers = JSON.parse(localStorage.getItem('erp_comprehensive_payments_v1')) || [];
    existingVouchers.push(voucher);
    localStorage.setItem('erp_comprehensive_payments_v1', JSON.stringify(existingVouchers));

    alert('تم حفظ السند الشامل بنجاح!');
    window.location.href = 'vouchers-hub.html';
}
