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
    updateLabels();
    // Apply exchange rate permission on load
    setTimeout(() => applyExchangeRatePermission(), 500);
});

document.addEventListener('DBReady', async () => {
    await loadAllAccounts();
    applyExchangeRatePermission();
});

// ═══ Currency Change: Auto-fill exchange rate from settings ═══
window.onCurrencyChange = function() {
    const currencyEl = document.getElementById('currency');
    const rateEl = document.getElementById('exchange-rate');
    if (!currencyEl || !rateEl) return;
    
    const currency = currencyEl.value;
    
    if (currency === 'SDG') {
        rateEl.value = '1.0';
        rateEl.readOnly = true;
        rateEl.style.opacity = '0.6';
    } else {
        // Get rate from settings or API
        let rate = 0;
        if (typeof window.getExchangeRateForCurrency === 'function') {
            rate = window.getExchangeRateForCurrency(currency);
        }
        
        if (rate > 0) {
            rateEl.value = parseFloat(rate).toFixed(4);
        } else {
            rateEl.value = '';
        }
        
        // Check if user has permission to edit exchange rates
        const canEdit = checkExchangeRatePermission();
        rateEl.readOnly = !canEdit;
        rateEl.style.opacity = canEdit ? '1' : '0.6';
        
        if (!canEdit && rate > 0) {
            showToast('تم تعبئة سعر الصرف تلقائياً من الإعدادات (' + parseFloat(rate).toFixed(4) + ')', 'info');
        } else if (rate > 0) {
            showToast('سعر الصرف: ' + parseFloat(rate).toFixed(4) + ' - يمكنك تعديله يدوياً', 'info');
        } else {
            showToast('لم يتم العثور على سعر صرف. يرجى إدخاله يدوياً.', 'info');
            rateEl.focus();
        }
    }
    
    if (typeof calcTotal === 'function') calcTotal();
};

// ═══ Check exchange_rates permission ═══
function checkExchangeRatePermission() {
    try {
        const userStr = localStorage.getItem('erp_currentUser');
        if (!userStr) return true; // Fallback: allow
        const user = JSON.parse(userStr);
        
        // Admin always has access
        if (user.username === 'admin' || user.role === 'admin' || (user.securityLevel && user.securityLevel >= 9)) {
            return true;
        }
        
        let perms = {};
        if (typeof window.resolveUserPermissions === 'function') {
            perms = window.resolveUserPermissions(user);
        }
        
        // Check exchange_rates module edit permission
        if (perms.exchange_rates && perms.exchange_rates.edit) {
            return true;
        }
        
        return false;
    } catch(e) {
        return true; // Fallback: allow on error
    }
}

// ═══ Apply exchange rate permission on page load ═══
function applyExchangeRatePermission() {
    const rateEl = document.getElementById('exchange-rate');
    const currencyEl = document.getElementById('currency');
    if (!rateEl || !currencyEl) return;
    
    if (currencyEl.value !== 'SDG') {
        const canEdit = checkExchangeRatePermission();
        rateEl.readOnly = !canEdit;
        rateEl.style.opacity = canEdit ? '1' : '0.6';
    }
}

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
            pmRow.style.background = 'linear-gradient(45deg, #b91c1c, #ef4444, #991b1b, #dc2626)';
            pmRow.style.borderColor = '#f87171';
            pmRow.style.boxShadow = '0 8px 20px rgba(220, 38, 38, 0.4)';
        }
    } else {
        if (btnReceipt) btnReceipt.className = 'voucher-type-btn active-receipt';
        if (btnPayment) btnPayment.className = 'voucher-type-btn';
        if (pmRow) {
            pmRow.style.background = 'linear-gradient(45deg, #047857, #10b981, #065f46, #059669)';
            pmRow.style.borderColor = '#34d399';
            pmRow.style.boxShadow = '0 8px 20px rgba(16, 185, 129, 0.4)';
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
    const radioHybrid = document.getElementById('radio-hybrid');
    
    let method = 'cash';
    if (radioCheck && radioCheck.checked) method = 'cheque';
    else if (radioMobile && radioMobile.checked) method = 'bank';
    else if (radioHybrid && radioHybrid.checked) method = 'hybrid';
    
    togglePaymentSection(method);
}

function updateLabels() {
    const type = document.getElementById('voucher-type').value;
    const label = document.getElementById('main-account-label');
    const subHeader = document.getElementById('sub-account-header');
    const subTitle = document.getElementById('sub-accounts-title');
    const primaryContainer = document.getElementById('primary-inputs-container');
    const secondaryContainer = document.getElementById('secondary-table-container');
    
    if (type === 'payment') {
        if (primaryContainer) {
            primaryContainer.className = 'glass-container glass-red';
        }
        if (secondaryContainer) {
            secondaryContainer.className = 'glass-container glass-green';
        }
        if (label) {
            label.innerHTML = 'من حساب (صرف) <span class="animated-arrow-left">←</span>';
            label.style.setProperty('color', '#f87171', 'important');
            label.style.fontSize = '1.1rem';
        }
        if (subHeader) {
            subHeader.innerHTML = 'إلى حساب (استلام) <span class="animated-arrow-right">→</span>';
            subHeader.style.setProperty('color', '#34d399', 'important');
            subHeader.style.fontSize = '1.1rem';
        }
        if (subTitle) {
            subTitle.innerHTML = '<span>👥</span> إلى حساب (استلام) <span class="animated-arrow-right">→</span>';
            subTitle.style.setProperty('color', '#34d399', 'important');
            subTitle.style.fontSize = '1.1rem';
        }
    } else {
        if (primaryContainer) {
            primaryContainer.className = 'glass-container glass-green';
        }
        if (secondaryContainer) {
            secondaryContainer.className = 'glass-container glass-red';
        }
        if (label) {
            label.innerHTML = 'إلى حساب (استلام) <span class="animated-arrow-right">→</span>';
            label.style.setProperty('color', '#34d399', 'important');
            label.style.fontSize = '1.1rem';
        }
        if (subHeader) {
            subHeader.innerHTML = 'من حساب (دفع) <span class="animated-arrow-left">←</span>';
            subHeader.style.setProperty('color', '#f87171', 'important');
            subHeader.style.fontSize = '1.1rem';
        }
        if (subTitle) {
            subTitle.innerHTML = '<span>👥</span> من حساب (دفع) <span class="animated-arrow-left">←</span>';
            subTitle.style.setProperty('color', '#f87171', 'important');
            subTitle.style.fontSize = '1.1rem';
        }
    }
}

let multiRowIdCounter = 0;
function addRow() {
    multiRowIdCounter++;
    const rowId = multiRowIdCounter;
    const tbody = document.getElementById('entries-body');
    const isFirst = (tbody && tbody.children.length === 0);
    const tr = document.createElement('tr');
    tr.className = 'multi-row';
    const actionBtn = isFirst 
        ? `<button type="button" onclick="addRow()" class="btn-add-row-inline" style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #10b981; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin: auto;" title="إضافة حساب جديد">➕</button>`
        : `<button class="btn-del" onclick="removeRow(this)" title="حذف السطر">✖</button>`;
    tr.innerHTML = `
        <td>
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" id="acc-${rowId}" class="sub-account">
                <input type="text" autocomplete="off" class="multi-input" id="acc-${rowId}-search" placeholder="ابحث عن حساب..." onfocus="showAccountDropdown('acc-${rowId}')" onblur="hideAccountDropdownDelayed('acc-${rowId}')" oninput="filterAccountDropdown('acc-${rowId}')" style="width: 100%; box-sizing: border-box; background: transparent; border: none; color: #ffffff !important; padding: 10px;">
                <div class="custom-dropdown-list" id="acc-${rowId}-dropdown-list"></div>
            </div>
        </td>
        <td><input type="text" autocomplete="off" class="multi-input sub-amount" placeholder="0.00" onblur="formatInput(this)" oninput="restrictToNumbers(this); calcTotal();" style="font-family: monospace; font-weight: bold; font-size: 1.1rem; color: #10b981;"></td>
        <td class="col-desc"><input type="text" autocomplete="off" class="multi-input sub-desc" placeholder="بيان هذا الحساب..."></td>
        <td class="col-cost-center"><input type="text" autocomplete="off" class="multi-input sub-cost-center" placeholder="مركز التكلفة..."></td>
        <td class="col-note"><input type="text" autocomplete="off" class="multi-input sub-note" placeholder="ملحوظة..."></td>
        <td class="col-check-no"><input type="text" autocomplete="off" class="multi-input sub-check-no" placeholder="رقم الشيك..."></td>
        <td class="col-check-date"><input type="date" class="multi-input sub-check-date"></td>
        <td class="col-check-due"><input type="date" class="multi-input sub-check-due"></td>
        <td style="text-align: center;">${actionBtn}</td>
    `;
    if (tbody) tbody.appendChild(tr);
    calcTotal();
    if (typeof window.applySystemNumberFormatting === 'function') window.applySystemNumberFormatting();
}

function removeRow(btn) {
    const tr = btn.closest('tr');
    if (tr) {
        tr.classList.add('removing');
        setTimeout(() => { tr.remove(); calcTotal(); }, 300);
    } else {
        calcTotal();
    }
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

window.printVoucher = function() {
    // Extract Voucher Document info
    const voucherTypeSel = document.getElementById('voucher-type');
    let voucherType = 'حركة مالية';
    if (voucherTypeSel) {
        const val = voucherTypeSel.value;
        voucherType = (val === 'payment') ? 'حركة صرف' : (val === 'receipt' ? 'حركة قبض' : 'حركة مالية');
    }
    const voucherNo = (document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || '').trim();
    const voucherDate = document.getElementById('voucher-date') ? document.getElementById('voucher-date').value : '';
    
    // Extract Main Account
    const mainAccountSearchEl = document.getElementById('main-account-search');
    const mainAccount = mainAccountSearchEl ? mainAccountSearchEl.value : '';
    
    // Extract Description, Currency, Rate, Totals
    const voucherDesc = document.getElementById('voucher-desc') ? document.getElementById('voucher-desc').value : '';
    const currencySel = document.getElementById('currency');
    const currency = currencySel ? (currencySel.options[currencySel.selectedIndex]?.text || currencySel.value) : '';
    const exchangeRate = document.getElementById('exchange-rate') ? document.getElementById('exchange-rate').value : '1.0';
    const amountLocal = document.getElementById('amount-local-hdr') ? document.getElementById('amount-local-hdr').value : '0.00';
    const amountForeign = document.getElementById('amount-foreign') ? document.getElementById('amount-foreign').value : '0.00';

    // Retrieve User and Company Info
    let userName = 'مدير النظام';
    let tenantId = 'default';
    const userStr = localStorage.getItem('erp_currentUser') || localStorage.getItem('user');
    if (userStr) {
        try {
            const u = JSON.parse(userStr);
            userName = u.fullName || u.username || userName;
            if (u.tenant_id) tenantId = u.tenant_id;
        } catch(e) {}
    }
    
    const prefix = 'tenant_' + tenantId + '_';
    let cfg = {};
    try {
        cfg = JSON.parse(localStorage.getItem(prefix + 'erp_settings_v1') || '{}');
    } catch(e) {}
    
    const companyLogo = cfg.logoBase64 || '';
    const companyName = cfg.companyName || 'اسم الشركة';

    // Current print date and time
    const printDateTime = new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit'});

    // Build Table Rows
    const rows = document.querySelectorAll('.multi-row');
    let tableHtml = `
        <table class="print-table">
            <thead>
                <tr>
                    <th>م</th>
                    <th>الحساب / الجهة المستفيدة</th>
                    <th>المبلغ</th>
                    <th>البيان التفصيلي</th>
                </tr>
            </thead>
            <tbody>
    `;
    let totalAmt = 0;
    rows.forEach((row, idx) => {
        const searchInput = row.querySelector('input[id$="-search"]');
        const accName = searchInput ? searchInput.value : '';
        if (!accName && idx > 0) return; // skip empty rows except first if empty
        
        const amtEl = row.querySelector('.sub-amount');
        const amt = amtEl ? amtEl.value : '0';
        totalAmt += parseFloat(amt.replace(/,/g, '') || 0);
        
        const descEl = row.querySelector('.sub-desc');
        const note = descEl ? descEl.value : '';
        
        tableHtml += `
            <tr>
                <td>${idx + 1}</td>
                <td>${accName}</td>
                <td style="font-weight: bold; color: #1e293b;">${amt}</td>
                <td>${note}</td>
            </tr>
        `;
    });
    tableHtml += `
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: left; font-weight: bold;">الإجمالي:</td>
                    <td style="font-weight: bold; color: #059669; font-size: 1.1rem;">${totalAmt.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    `;

    // Build Payment Methods Summary
    const payMethodRadio = document.querySelector('input[name="payment_method"]:checked');
    const payMethodVal = payMethodRadio ? payMethodRadio.value : 'cash';
    
    let payMethodLabel = 'نقدي';
    if (payMethodVal === 'check') payMethodLabel = 'شيك';
    else if (payMethodVal === 'mobile') payMethodLabel = 'تحويل بنكي';
    else if (payMethodVal === 'hybrid') payMethodLabel = 'متعدد (تحويل وشيك)';
    
    let paymentHtml = `<div class="payment-section"><h3 class="section-title">طريقة الدفع: ${payMethodLabel}</h3>`;
    
    if (payMethodVal === 'cash') {
        const cashAmt = document.getElementById('cash-amount') ? document.getElementById('cash-amount').value : '';
        paymentHtml += `<p style="font-size: 1.1rem;"><strong>المبلغ النقدي:</strong> <span style="font-family: monospace; font-weight: bold; color: #059669;">${cashAmt}</span></p>`;
    } else if (payMethodVal === 'mobile') {
        paymentHtml += `<table class="print-table" style="margin-top:10px;">
            <thead><tr><th>المبلغ</th><th>رقم التحويل</th><th>البيان</th></tr></thead><tbody>`;
        const transfers = document.querySelectorAll('.transfer-row');
        transfers.forEach(tr => {
            const tAmt = tr.querySelector('.transfer-amount')?.value || '';
            const tNo = tr.querySelector('.transfer-no')?.value || '';
            const tNote = tr.querySelector('.transfer-note')?.value || '';
            paymentHtml += `<tr><td style="font-weight: bold;">${tAmt}</td><td>${tNo}</td><td>${tNote}</td></tr>`;
        });
        paymentHtml += `</tbody></table>`;
    } else if (payMethodVal === 'check') {
        paymentHtml += `<table class="print-table" style="margin-top:10px;">
            <thead><tr><th>المبلغ</th><th>اسم البنك</th><th>رقم الشيك</th><th>تاريخ الاستحقاق</th></tr></thead><tbody>`;
        const cheques = document.querySelectorAll('.cheque-row');
        cheques.forEach(cr => {
            const cAmt = cr.querySelector('.cheque-amount')?.value || '';
            const cBranch = cr.querySelector('.cheque-branch')?.value || '';
            const cNo = cr.querySelector('.cheque-no')?.value || '';
            const cDate = cr.querySelector('.cheque-date')?.value || '';
            paymentHtml += `<tr><td style="font-weight: bold;">${cAmt}</td><td>${cBranch}</td><td>${cNo}</td><td dir="ltr">${cDate}</td></tr>`;
        });
        paymentHtml += `</tbody></table>`;
    } else if (payMethodVal === 'hybrid') {
        paymentHtml += `<h4>التحويلات البنكية</h4>`;
        paymentHtml += `<table class="print-table" style="margin-top:10px; margin-bottom: 20px;">
            <thead><tr><th>المبلغ</th><th>رقم التحويل</th><th>البيان</th></tr></thead><tbody>`;
        const transfers = document.querySelectorAll('.transfer-row');
        transfers.forEach(tr => {
            const tAmt = tr.querySelector('.transfer-amount')?.value || '';
            const tNo = tr.querySelector('.transfer-no')?.value || '';
            const tNote = tr.querySelector('.transfer-note')?.value || '';
            paymentHtml += `<tr><td style="font-weight: bold;">${tAmt}</td><td>${tNo}</td><td>${tNote}</td></tr>`;
        });
        paymentHtml += `</tbody></table>`;
        
        paymentHtml += `<h4>الشيكات</h4>`;
        paymentHtml += `<table class="print-table" style="margin-top:10px;">
            <thead><tr><th>المبلغ</th><th>اسم البنك</th><th>رقم الشيك</th><th>تاريخ الاستحقاق</th></tr></thead><tbody>`;
        const cheques = document.querySelectorAll('.cheque-row');
        cheques.forEach(cr => {
            const cAmt = cr.querySelector('.cheque-amount')?.value || '';
            const cBranch = cr.querySelector('.cheque-branch')?.value || '';
            const cNo = cr.querySelector('.cheque-no')?.value || '';
            const cDate = cr.querySelector('.cheque-date')?.value || '';
            paymentHtml += `<tr><td style="font-weight: bold;">${cAmt}</td><td>${cBranch}</td><td>${cNo}</td><td dir="ltr">${cDate}</td></tr>`;
        });
        paymentHtml += `</tbody></table>`;
    }
    paymentHtml += `</div>`;

    const logoHtml = companyLogo ? `<img src="${companyLogo}" alt="Logo" style="max-height: 80px; max-width: 150px; object-fit: contain;">` : `<div style="width: 80px; height: 80px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-weight: bold;">شعار الشركة</div>`;

    const html = `
        <style>
            .print-container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                color: #1e293b;
            }
            .header-flex {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                border-bottom: 2px solid #0ea5e9;
                padding-bottom: 15px;
                margin-bottom: 25px;
            }
            .company-info h1 {
                margin: 0 0 5px 0;
                font-size: 1.5rem;
                color: #0ea5e9;
            }
            .voucher-title {
                text-align: center;
                background: rgba(14, 165, 233, 0.1);
                color: #0ea5e9;
                padding: 8px 18px;
                border-radius: 6px;
                font-size: 1.4rem;
                font-weight: 800;
                margin: 0;
            }
            .meta-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-bottom: 25px;
                background: #f1f5f9;
                padding: 15px;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
            }
            .meta-item {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .meta-label {
                font-size: 0.85rem;
                color: #64748b;
                font-weight: 700;
            }
            .meta-val {
                font-size: 1rem;
                font-weight: bold;
                color: #1e293b;
            }
            .highlight-val {
                color: #10b981;
                font-family: monospace;
                font-size: 1.15rem;
            }
            .print-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
            }
            .print-table th, .print-table td {
                padding: 10px 12px;
                border: 1px solid #cbd5e1;
                text-align: right;
                font-size: 0.95rem;
            }
            .print-table th {
                background: #f8fafc;
                font-weight: 800;
                color: #1e293b;
            }
            .print-table tbody tr:nth-child(even) {
                background: #fdfdfd;
            }
            .section-title {
                border-bottom: 2px solid #e2e8f0;
                padding-bottom: 6px;
                margin-bottom: 12px;
                color: #0ea5e9;
                font-size: 1.1rem;
            }
            .signatures {
                display: flex;
                justify-content: space-between;
                margin-top: 40px;
                padding-top: 20px;
            }
            .sig-box {
                text-align: center;
                width: 200px;
            }
            .sig-line {
                border-bottom: 1px dashed #64748b;
                margin-bottom: 8px;
                height: 35px;
            }
        </style>
        <div class="print-container">
            <div class="header-flex">
                <div class="company-info">
                    <h1>${companyName}</h1>
                    <div style="color: #64748b; font-size: 0.85rem;">تاريخ ووقت الطباعة: ${printDateTime}</div>
                </div>
                <div>${logoHtml}</div>
            </div>

            <div style="display: flex; justify-content: center; margin-top: -50px; margin-bottom: 25px;">
                <h2 class="voucher-title">${voucherType} مجمعة</h2>
            </div>

            <div class="meta-grid">
                <div class="meta-item">
                    <span class="meta-label">رقم المستند</span>
                    <span class="meta-val">${voucherNo || '-'}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">تاريخ المستند</span>
                    <span class="meta-val" dir="ltr">${voucherDate}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">حساب الاستلاف / الدفع (الرئيسي)</span>
                    <span class="meta-val">${mainAccount}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">المستخدم / الموظف</span>
                    <span class="meta-val">${userName}</span>
                </div>
                <div class="meta-item" style="grid-column: 1 / -1;">
                    <span class="meta-label">البيان العام / الوصف</span>
                    <span class="meta-val">${voucherDesc || '---'}</span>
                </div>
            </div>

            <div class="meta-grid" style="background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2);">
                <div class="meta-item">
                    <span class="meta-label">العملة والتعادل</span>
                    <span class="meta-val">${currency} (سعر الصرف: ${exchangeRate})</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label" style="color: #10b981;">الإجمالي بالمحلي</span>
                    <span class="meta-val highlight-val">${amountLocal}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label" style="color: #ef4444;">الإجمالي بالأجنبي</span>
                    <span class="meta-val highlight-val" style="color: #ef4444;">${amountForeign}</span>
                </div>
            </div>

            <h3 class="section-title">تفاصيل الحسابات المستفيدة</h3>
            ${tableHtml}

            ${paymentHtml}

            <div class="signatures">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="meta-label">توقيع المستلم</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="meta-label">توقيع المحاسب / ${userName}</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="meta-label">الاعتماد / المدير</div>
                </div>
            </div>
        </div>
    `;

    const bodyEl = document.getElementById('preview-modal-body');
    const modalEl = document.getElementById('print-preview-modal');
    if (bodyEl && modalEl) {
        bodyEl.innerHTML = html;
        modalEl.style.display = 'flex';
    } else {
        alert('حدث خطأ في عرض نافذة المعاينة المنبثقة.');
    }
}

window.closePrintPreviewModal = function() {
    const modalEl = document.getElementById('print-preview-modal');
    if (modalEl) {
        modalEl.style.display = 'none';
    }
};

async function confirmDelete() {
    const voucherNoEl = document.getElementById('voucher-no-display');
    const voucherNo = voucherNoEl ? voucherNoEl.value : '';
    if (!voucherNo) {
        showToast('لا يوجد مستند لحذفه.', 'error');
        return;
    }
    if (!confirm('⚠️ هل أنت متأكد من رغبتك في حذف هذا المستند نهائياً؟\nرقم المستند: ' + voucherNo + '\n\nلا يمكن التراجع عن هذا الإجراء!')) return;
    
    const type = document.getElementById('voucher-type').value;
    const storageKey = type === 'payment' ? 'erp_payments_v1' : 'erp_receipts_v1';
    const getTenantKeyFn = window.getTenantKey || ((k) => k);
    const tKey = getTenantKeyFn(storageKey);
    
    try {
        let existingData = [];
        if (typeof localforage !== 'undefined') {
            existingData = await localforage.getItem(tKey) || [];
        } else {
            existingData = JSON.parse(localStorage.getItem(tKey) || '[]');
        }
        if (!Array.isArray(existingData)) existingData = Object.values(existingData);
        
        const idx = existingData.findIndex(v => v.voucherNo === voucherNo && v.isGrouped === true);
        if (idx > -1) {
            // Revert balance effects before deleting
            if (typeof applyGroupedVoucherEffect === 'function') {
                applyGroupedVoucherEffect(existingData[idx], true);
            }
            existingData.splice(idx, 1);
            if (typeof localforage !== 'undefined') {
                await localforage.setItem(tKey, existingData);
            } else {
                localStorage.setItem(tKey, JSON.stringify(existingData));
            }
            showToast('تم حذف المستند بنجاح وعكس تأثيره المحاسبي!', 'success');
        } else {
            showToast('المستند غير محفوظ بعد، تم تفريغ الحقول.', 'info');
        }
        resetVoucher();
    } catch (e) {
        console.error('Delete error:', e);
        showToast('حدث خطأ أثناء الحذف!', 'error');
    }
}

async function saveVoucher() {
    // Double-click protection
    const saveBtn = document.getElementById('btn-save');
    if (saveBtn && saveBtn.disabled) return;
    if (saveBtn) { saveBtn.disabled = true; saveBtn.style.opacity = '0.6'; saveBtn.style.pointerEvents = 'none'; }

    // Helper to re-enable save button on validation errors
    function reEnableSave() {
        if (saveBtn) { saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.pointerEvents = 'auto'; }
    }
    // Helper to highlight field with red border and shake
    function highlightError(el) {
        if (!el) return;
        el.style.border = '2px solid #ef4444';
        el.style.boxShadow = '0 0 12px rgba(239,68,68,0.5)';
        if (typeof shakeElement === 'function') shakeElement(el);
        setTimeout(() => { el.style.border = ''; el.style.boxShadow = ''; }, 4000);
    }

    const type = document.getElementById('voucher-type').value;
    const date = document.getElementById('voucher-date').value;
    const mainAccount = document.getElementById('main-account').getAttribute('data-id') || document.getElementById('main-account').value;
    const desc = document.getElementById('voucher-desc').value.trim();
    const ref = document.getElementById('voucher-ref').value.trim();
    const currency = document.getElementById('currency').value;
    const exchangeRate = parseFloat(document.getElementById('exchange-rate').value) || 1.0;
    
    // Payment method and dynamic fields
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    let checkNo = '';
    let checkBranch = '';
    let checkDate = '';
    let checkDueDate = '';
    let mobileTransferNo = '';
    let mobileNote = '';
    let bankTransfers = [];
    let cheques = [];

    if (!date) { highlightError(document.getElementById('voucher-date')); reEnableSave(); return showToast('يرجى تحديد تاريخ العملية', 'error'); }
    if (!mainAccount) { highlightError(document.getElementById('main-account-search')); reEnableSave(); return showToast('يرجى اختيار الحساب الرئيسي', 'error'); }

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
            if (!acc) { showToast(`السطر ${index+1}: الرجاء إدخال اسم الحساب.`, 'error'); highlightError(searchInput); hasError = true; }
            if (amt <= 0) { showToast(`السطر ${index+1}: الرجاء إدخال مبلغ صحيح.`, 'error'); highlightError(row.querySelector('.sub-amount')); hasError = true; }
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

    if (hasError) { reEnableSave(); return; }
    if (items.length === 0) {
        showToast('يجب إدخال حساب فرعي واحد على الأقل.', 'error');
        reEnableSave(); return;
    }

    const finalTotal = items.reduce((sum, item) => sum + item.amount, 0);

    // Validate and read payment details
    if (paymentMethod === 'mobile') {
        const transferRows = document.querySelectorAll('.transfer-row');
        let totalTransfersAmount = 0;
        let hasInvalidTransfer = false;

        transferRows.forEach((row, idx) => {
            const trNo = row.querySelector('.transfer-no').value.trim();
            const trNote = row.querySelector('.transfer-note').value.trim();
            const trAmt = cleanNum(row.querySelector('.transfer-amount').value);

            if (!trNo) {
                showToast(`السطر ${idx + 1}: يرجى إدخال رقم حساب التحويل`, "error");
                highlightError(row.querySelector('.transfer-no'));
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (trNo.length < 6) {
                showToast(`السطر ${idx + 1}: يجب أن يكون رقم التحويل أكثر من 5 أرقام`, "error");
                highlightError(row.querySelector('.transfer-no'));
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

        if (hasInvalidTransfer) { reEnableSave(); return; }

        if (Math.abs(totalTransfersAmount - finalTotal) > 0.01) {
            showToast("مجموع مبالغ التحويلات لا يساوي إجمالي السند المجمع.", "error");
            reEnableSave(); return;
        }

        if (bankTransfers.length > 0) {
            mobileTransferNo = bankTransfers[0].transferNo;
            mobileNote = bankTransfers[0].note;
        }
    } else if (paymentMethod === 'check') {
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
                highlightError(row.querySelector('.cheque-no'));
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

        if (hasInvalidCheque) { reEnableSave(); return; }

        if (Math.abs(totalChequesAmount - finalTotal) > 0.01) {
            showToast("مجموع مبالغ الشيكات لا يساوي إجمالي السند المجمع.", "error");
            reEnableSave(); return;
        }

        if (cheques.length > 0) {
            checkNo = cheques[0].checkNo;
            checkBranch = cheques[0].checkBranch;
            checkDate = cheques[0].checkDate;
        }
    } else if (paymentMethod === 'hybrid') {
        const transferRows = document.querySelectorAll('.transfer-row');
        let totalTransfersAmount = 0;
        let hasInvalidTransfer = false;

        transferRows.forEach((row, idx) => {
            const trNo = row.querySelector('.transfer-no').value.trim();
            const trNote = row.querySelector('.transfer-note').value.trim();
            const trAmt = cleanNum(row.querySelector('.transfer-amount').value);

            if (!trNo) {
                showToast(`السطر ${idx + 1}: يرجى إدخال رقم حساب التحويل`, "error");
                highlightError(row.querySelector('.transfer-no'));
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (trNo.length < 6) {
                showToast(`السطر ${idx + 1}: يجب أن يكون رقم التحويل أكثر من 5 أرقام`, "error");
                highlightError(row.querySelector('.transfer-no'));
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

        if (hasInvalidTransfer) { reEnableSave(); return; }

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
                highlightError(row.querySelector('.cheque-no'));
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

        if (hasInvalidCheque) { reEnableSave(); return; }

        if (Math.abs((totalTransfersAmount + totalChequesAmount) - finalTotal) > 0.01) {
            showToast("مجموع مبالغ التحويلات والشيكات لا يساوي إجمالي السند المجمع.", "error");
            reEnableSave(); return;
        }

        if (bankTransfers.length > 0) {
            mobileTransferNo = bankTransfers[0].transferNo;
            mobileNote = bankTransfers[0].note;
        }
        if (cheques.length > 0) {
            checkNo = cheques[0].checkNo;
            checkBranch = cheques[0].checkBranch;
            checkDate = cheques[0].checkDate;
        }
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
        paymentMethod: paymentMethod,
        checkNo: checkNo,
        checkBranch: checkBranch,
        checkDate: checkDate,
        checkDueDate: checkDueDate,
        mobileTransferNo: mobileTransferNo,
        mobileNote: mobileNote,
        bankTransfers: bankTransfers,
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
        // Save success animation
        if (saveBtn) {
            saveBtn.innerHTML = '✅ تم الحفظ بنجاح!';
            saveBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #34d399 100%)';
            saveBtn.style.transform = 'scale(1.1)';
            saveBtn.style.boxShadow = '0 0 30px rgba(16,185,129,0.6)';
            setTimeout(() => {
                saveBtn.innerHTML = '✅ حفظ واعتماد السند';
                saveBtn.style.transform = 'scale(1)';
                saveBtn.style.boxShadow = '0 0 15px rgba(16,185,129,0.4)';
            }, 2000);
        }
        if (typeof playClickSound === 'function') playClickSound();
        
        // Reset Form after short delay
        setTimeout(() => {
            resetVoucher();
            if (saveBtn) { saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.pointerEvents = 'auto'; }
        }, 1500);

    } catch (e) {
        console.error('Save error:', e);
        showToast('حدث خطأ أثناء الحفظ!', 'error');
        if (saveBtn) { saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.pointerEvents = 'auto'; }
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
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <span style="font-weight: bold; color: #fff;">${a.name}</span>
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
    const isFirst = (tbody && tbody.children.length === 0);
    const tr = document.createElement('tr');
    tr.className = 'multi-row';
    
    const actionBtn = isFirst 
        ? `<button type="button" onclick="addRow()" class="btn-add-row-inline" style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #10b981; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin: auto;" title="إضافة حساب جديد">➕</button>`
        : `<button class="btn-del" onclick="removeRow(this)" title="حذف السطر">✖</button>`;
        
    const accName = getAccountName(item.account);

    tr.innerHTML = `
        <td>
            <div class="custom-dropdown-input-wrap" style="width: 100%; position: relative;">
                <input type="hidden" id="acc-${rowId}" class="sub-account" value="${accName}">
                <input type="text" autocomplete="off" class="multi-input" id="acc-${rowId}-search" placeholder="ابحث عن حساب..." onfocus="showAccountDropdown('acc-${rowId}')" onblur="hideAccountDropdownDelayed('acc-${rowId}')" oninput="filterAccountDropdown('acc-${rowId}')" style="width: 100%; box-sizing: border-box; background: transparent; border: none; color: #ffffff !important; padding: 10px;" value="${accName}">
                <div class="custom-dropdown-list" id="acc-${rowId}-dropdown-list"></div>
            </div>
        </td>
        <td><input type="text" autocomplete="off" class="multi-input sub-amount" placeholder="0.00" onblur="formatInput(this)" oninput="restrictToNumbers(this); calcTotal();" style="font-family: monospace; font-weight: bold; font-size: 1.1rem; color: #10b981;" value="${item.amount > 0 ? fmtNum(item.amount) : ''}"></td>
        <td class="col-desc"><input type="text" autocomplete="off" class="multi-input sub-desc" placeholder="بيان هذا الحساب..." value="${item.statement || ''}"></td>
        <td class="col-cost-center"><input type="text" autocomplete="off" class="multi-input sub-cost-center" placeholder="مركز التكلفة..." value="${item.costCenter || ''}"></td>
        <td class="col-note"><input type="text" autocomplete="off" class="multi-input sub-note" placeholder="ملحوظة..." value="${item.note || ''}"></td>
        <td class="col-check-no"><input type="text" autocomplete="off" class="multi-input sub-check-no" placeholder="رقم الشيك..." value="${item.checkNo || ''}"></td>
        <td class="col-check-date"><input type="date" class="multi-input sub-check-date" value="${item.checkDate || ''}"></td>
        <td class="col-check-due"><input type="date" class="multi-input sub-check-due" value="${item.checkDueDate || ''}"></td>
        <td style="text-align: center;">${actionBtn}</td>
    `;
    if (tbody) tbody.appendChild(tr);
}

function applyGroupedVoucherEffect(voucher, isRevert = false) {
    if (!voucher) return;
    const type = voucher.type; // 'payment' or 'receipt'
    const multiplier = isRevert ? -1 : 1;
    const getTenantKey = window.getTenantKey || ((k) => k);
    
    // 1. Update Main Account
    const mainAccId = voucher.mainAccount || (type === 'payment' ? voucher.fromAccount : voucher.toAccount);
    if (mainAccId) {
        const amt = voucher.totalAmount * multiplier;
        const amtEffect = (type === 'payment') ? -amt : amt;
        
        if (mainAccId.startsWith('safe-')) {
            let id = mainAccId.substring(5);
            let safes = [];
            try { safes = JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1'))) || []; } catch(e){}
            let idx = safes.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem(getTenantKey('erp_safes_v1'), JSON.stringify(safes));
            }
        } else if (mainAccId.startsWith('bank-')) {
            let id = mainAccId.substring(5);
            let banks = [];
            try { banks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1'))) || []; } catch(e){}
            let idx = banks.findIndex(b => String(b.id) === String(id));
            if (idx > -1) {
                banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtEffect;
                localStorage.setItem(getTenantKey('erp_banks_v1'), JSON.stringify(banks));
            }
        }
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
            } else { // receipt - opposite direction
                if (itemAccId.startsWith('cust-') || itemAccId.startsWith('supp-')) {
                    amtEffect = amt;
                } else {
                    amtEffect = -amt;
                }
            }

            if (itemAccId.startsWith('cust-')) {
                let id = itemAccId.substring(5);
                let custs = [];
                try { custs = JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1'))) || []; } catch(e){}
                let idx = custs.findIndex(c => String(c.id) === String(id));
                if (idx > -1) {
                    custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem(getTenantKey('erp_customers_v1'), JSON.stringify(custs));
                }
            } else if (itemAccId.startsWith('supp-')) {
                let id = itemAccId.substring(5);
                let sups = [];
                try { sups = JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1'))) || []; } catch(e){}
                let idx = sups.findIndex(s => String(s.id) === String(id));
                if (idx > -1) {
                    sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem(getTenantKey('erp_suppliers_v1'), JSON.stringify(sups));
                }
            } else if (itemAccId.startsWith('bank-')) {
                let id = itemAccId.substring(5);
                let banks = [];
                try { banks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1'))) || []; } catch(e){}
                let idx = banks.findIndex(b => String(b.id) === String(id));
                if (idx > -1) {
                    banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem(getTenantKey('erp_banks_v1'), JSON.stringify(banks));
                }
            } else if (itemAccId.startsWith('safe-')) {
                let id = itemAccId.substring(5);
                let safes = [];
                try { safes = JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1'))) || []; } catch(e){}
                let idx = safes.findIndex(s => String(s.id) === String(id));
                if (idx > -1) {
                    safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem(getTenantKey('erp_safes_v1'), JSON.stringify(safes));
                }
            } else if (itemAccId.startsWith('exp-')) {
                let id = itemAccId.substring(4);
                let accounts = [];
                try { accounts = JSON.parse(localStorage.getItem(getTenantKey('erp_accounts_v1'))) || []; } catch(e){}
                let idx = accounts.findIndex(a => String(a.id) === String(id));
                if (idx > -1) {
                    accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amtEffect;
                    localStorage.setItem(getTenantKey('erp_accounts_v1'), JSON.stringify(accounts));
                }
            }
        });
    }
}

// ═══════════════════════════════════════════════════════
// Inline Payment Panel & Formatting Logic (Grouped Vouchers)
// ═══════════════════════════════════════════════════════

window.togglePaymentSection = function(clickedMethod) {
    const btnCash = document.getElementById('toggle-cash');
    const btnBank = document.getElementById('toggle-bank');
    const btnCheque = document.getElementById('toggle-cheque');

    if (clickedMethod === 'cash') {
        if (btnCash) btnCash.classList.add('active');
        if (btnBank) btnBank.classList.remove('active');
        if (btnCheque) btnCheque.classList.remove('active');
    } else {
        if (btnCash) btnCash.classList.remove('active');
        
        const btn = document.getElementById('toggle-' + clickedMethod);
        if (btn) {
            btn.classList.toggle('active');
        }
        
        // If both bank and cheque are deactivated, fallback to cash
        const bankActive = btnBank?.classList.contains('active');
        const chequeActive = btnCheque?.classList.contains('active');
        if (!bankActive && !chequeActive) {
            if (btnCash) btnCash.classList.add('active');
        }
    }

    if (typeof playClickSound === 'function') {
        playClickSound();
    }

    // Sync with radio buttons
    const btnBankActive = btnBank?.classList.contains('active');
    const btnChequeActive = btnCheque?.classList.contains('active');

    const radioCash = document.getElementById('radio-cash');
    const radioCheck = document.getElementById('radio-check');
    const radioMobile = document.getElementById('radio-mobile');
    const radioHybrid = document.getElementById('radio-hybrid');

    if (radioCash) radioCash.checked = false;
    if (radioCheck) radioCheck.checked = false;
    if (radioMobile) radioMobile.checked = false;
    if (radioHybrid) radioHybrid.checked = false;

    if (btnBankActive && btnChequeActive) {
        if (radioHybrid) radioHybrid.checked = true;
    } else if (btnBankActive) {
        if (radioMobile) radioMobile.checked = true;
    } else if (btnChequeActive) {
        if (radioCheck) radioCheck.checked = true;
    } else {
        if (radioCash) radioCash.checked = true;
    }

    // Update payment method boxes visibility
    const isCash = !btnBankActive && !btnChequeActive;
    const secCash = document.getElementById('pay-cash-section');
    const secBank = document.getElementById('pay-bank-section');
    const secCheque = document.getElementById('pay-cheque-section');

    if (secCash) secCash.style.display = isCash ? 'flex' : 'none';
    if (secBank) secBank.style.display = btnBankActive ? 'flex' : 'none';
    if (secCheque) secCheque.style.display = btnChequeActive ? 'flex' : 'none';

    recalcPaymentSummary();
};

window.recalcPaymentSummary = function() {
    const totalEl = document.getElementById('total-amount') || document.getElementById('amount-local-hdr');
    const totalAmount = totalEl ? cleanNum(totalEl.value !== undefined ? totalEl.value : totalEl.innerText) : 0;
    const btnCash = document.getElementById('toggle-cash');
    const btnBank = document.getElementById('toggle-bank');
    const btnCheque = document.getElementById('toggle-cheque');

    const bankActive = btnBank?.classList.contains('active');
    const chequeActive = btnCheque?.classList.contains('active');
    const isCash = !bankActive && !chequeActive;
    const isHybrid = bankActive && chequeActive;
    const isMobile = bankActive && !chequeActive;
    const isCheck = chequeActive && !bankActive;
    
    const progCash = document.getElementById('prog-cash');
    const progBank = document.getElementById('prog-bank');
    const progCheque = document.getElementById('prog-cheque');
    
    if (progCash) progCash.style.width = '0%';
    if (progBank) progBank.style.width = '0%';
    if (progCheque) progCheque.style.width = '0%';

    const badge = document.getElementById('payment-status-badge');

    if (totalAmount > 0) {
        if (isCash) {
            if (progCash) progCash.style.width = '100%';
            if (badge) {
                badge.innerText = '💵 مدفوعة كاش بالكامل';
                badge.style.background = 'rgba(16, 185, 129, 0.15)';
                badge.style.color = '#34d399';
                badge.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.2)';
            }
        } else if (isMobile) {
            if (progBank) progBank.style.width = '100%';
            if (badge) {
                badge.innerText = '🏦 محولة بالكامل';
                badge.style.background = 'rgba(59, 130, 246, 0.15)';
                badge.style.color = '#60a5fa';
                badge.style.boxShadow = '0 0 10px rgba(59, 130, 246, 0.2)';
            }
        } else if (isCheck) {
            if (progCheque) progCheque.style.width = '100%';
            if (badge) {
                badge.innerText = '🧾 شيك بالكامل';
                badge.style.background = 'rgba(139, 92, 246, 0.15)';
                badge.style.color = '#c084fc';
                badge.style.boxShadow = '0 0 10px rgba(139, 92, 246, 0.2)';
            }
        } else if (isHybrid) {
            let tSum = 0;
            document.querySelectorAll('.transfer-row .transfer-amount').forEach(inp => tSum += cleanNum(inp.value));
            let cSum = 0;
            document.querySelectorAll('.cheque-row .cheque-amount').forEach(inp => cSum += cleanNum(inp.value));
            
            const bankPct = Math.min(100, (tSum / totalAmount) * 100);
            const chequePct = Math.min(100, (cSum / totalAmount) * 100);
            
            if (progBank) progBank.style.width = bankPct + '%';
            if (progCheque) progCheque.style.width = chequePct + '%';
            
            if (badge) {
                if (Math.abs((tSum + cSum) - totalAmount) < 0.01) {
                    badge.innerText = '💳 مدفوعة بالكامل (تحويل وشيك)';
                    badge.style.background = 'rgba(56, 189, 248, 0.15)';
                    badge.style.color = '#38bdf8';
                    badge.style.boxShadow = '0 0 10px rgba(56, 189, 248, 0.2)';
                } else {
                    badge.innerText = `⏳ متبقي: ${fmtNum(totalAmount - (tSum + cSum))}`;
                    badge.style.background = 'rgba(245, 158, 11, 0.15)';
                    badge.style.color = '#fbbf24';
                    badge.style.boxShadow = '0 0 10px rgba(245, 158, 11, 0.2)';
                }
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
               <span style="font-weight: 800; color: #fbbf24; font-size: 0.9rem;">تفاصيل التحويلة</span>
           </div>`
        : `<div style="display: flex; align-items: center; gap: 8px; min-width: 140px; width: 140px; flex: none;">
               <span style="font-size: 1.1rem; visibility: hidden;">🏦</span>
               <span style="font-weight: 800; color: #fbbf24; font-size: 0.9rem; opacity: 0.6;">تحويلة إضافية</span>
           </div>`;

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addTransferRow()" class="pay-toggle-btn" style="background: rgba(251, 191, 36, 0.2); border: 1px solid #fbbf24; color:#fbbf24; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeTransferRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div style="width: 220px; flex: none; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">المبلغ:</label>
            <input type="text" autocomplete="off" class="v-input ctrl-input transfer-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="0.00" onfocus="if(parseFloat(this.value.replace(/,/g, '') || 0) === 0) this.value = '';" oninput="restrictToNumbers(this); recalcPaymentSummary();" onblur="if(this.value) this.value = parseFloat(this.value.replace(/,/g, '') || 0).toFixed(2); recalcPaymentSummary();" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:1.35rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 170px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">اسم البنك:</label>
            <input type="text" autocomplete="off" class="v-input ctrl-input transfer-note" value="${note}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 190px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">رقم التحويلة:</label>
            <input type="text" autocomplete="off" class="v-input ctrl-input transfer-no" value="${no}" placeholder="رقم التحويلة..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
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
    const row = btn.closest('.transfer-row');
    if (row) {
        row.classList.add('removing');
        setTimeout(() => { row.remove(); syncFirstRowAmount(); }, 300);
    }
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
        <div style="width: 220px; flex: none; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">المبلغ:</label>
            <input type="text" class="v-input ctrl-input cheque-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="0.00" onfocus="if(parseFloat(this.value.replace(/,/g, '') || 0) === 0) this.value = '';" oninput="restrictToNumbers(this); recalcPaymentSummary();" onblur="if(this.value) this.value = parseFloat(this.value.replace(/,/g, '') || 0).toFixed(2); recalcPaymentSummary();" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:1.35rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#10b981; font-family: monospace; font-weight: bold; text-align: center;">
        </div>
        <div style="flex: 1.2; min-width: 170px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">اسم البنك:</label>
            <input type="text" class="v-input ctrl-input cheque-branch" value="${branch}" placeholder="اسم البنك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">رقم الشيك:</label>
            <input type="text" class="v-input ctrl-input cheque-no" value="${no}" placeholder="رقم الشيك..." style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 36px; font-size:0.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div style="flex: 0.9; min-width: 220px; display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.75rem; color: #ffffff; font-weight: bold; white-space: nowrap;">تاريخ الاستحقاق:</label>
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
    const row = btn.closest('.cheque-row');
    if (row) {
        row.classList.add('removing');
        setTimeout(() => { row.remove(); syncFirstRowAmount(); }, 300);
    }
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
    const totalEl = document.getElementById('total-amount') || document.getElementById('amount-local-hdr');
    const totalAmount = totalEl ? cleanNum(totalEl.value !== undefined ? totalEl.value : totalEl.innerText) : 0;
    const transferRows = document.querySelectorAll('.transfer-row');
    if (transferRows.length === 1) {
        transferRows[0].querySelector('.transfer-amount').value = fmtNum(totalAmount);
    }
    const chequeRows = document.querySelectorAll('.cheque-row');
    if (chequeRows.length === 1) {
        chequeRows[0].querySelector('.cheque-amount').value = fmtNum(totalAmount);
    }
};

window.loadVoucherPaymentDetails = function(v) {
    const method = v.paymentMethod || 'cash';
    const transfersContainer = document.getElementById('transfers-container');
    const chequesContainer = document.getElementById('cheques-container');
    if (transfersContainer) transfersContainer.innerHTML = '';
    if (chequesContainer) chequesContainer.innerHTML = '';

    if (method === 'mobile') {
        if (v.bankTransfers && Array.isArray(v.bankTransfers) && v.bankTransfers.length > 0) {
            v.bankTransfers.forEach(tr => {
                addTransferRow(tr.transferNo, tr.note, tr.amount);
            });
        } else {
            addTransferRow(v.mobileTransferNo || '', v.mobileNote || '', v.totalAmount || 0);
        }
    } else if (method === 'check') {
        if (v.cheques && Array.isArray(v.cheques) && v.cheques.length > 0) {
            v.cheques.forEach(ch => {
                addChequeRow(ch.checkBranch, ch.checkNo, ch.checkDate, ch.amount);
            });
        } else {
            addChequeRow(v.checkBranch || '', v.checkNo || '', v.checkDate || '', v.totalAmount || 0);
        }
    } else {
        initPaymentContainers();
    }
};

// Hook setup into document load
document.addEventListener('DOMContentLoaded', () => {
    initPaymentContainers();
});

// ═══════════════════════════════════════════════════════
// PHASE 2 & 3: Professional Enhancements
// ═══════════════════════════════════════════════════════

// ─── Direct Print (opens preview then auto-prints) ───
window.directPrint = function() {
    printVoucher(); // Opens the preview modal first
    setTimeout(() => { window.print(); }, 600); // Then trigger print dialog
};

// ─── Confetti Celebration Effect ───
window.launchConfetti = function() {
    const colors = ['#10b981', '#34d399', '#6ee7b7', '#fbbf24', '#38bdf8', '#c084fc', '#f87171'];
    for (let i = 0; i < 40; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.left = Math.random() * 100 + 'vw';
        piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        piece.style.animationDuration = (1.5 + Math.random() * 2) + 's';
        piece.style.animationDelay = Math.random() * 0.8 + 's';
        piece.style.width = (6 + Math.random() * 8) + 'px';
        piece.style.height = (6 + Math.random() * 8) + 'px';
        piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        document.body.appendChild(piece);
        setTimeout(() => piece.remove(), 4000);
    }
};

// ─── Shake Error Helper ───
window.shakeElement = function(el) {
    if (!el) return;
    el.classList.add('shake-error');
    setTimeout(() => el.classList.remove('shake-error'), 600);
};

// ─── Keyboard Shortcuts ───
document.addEventListener('keydown', function(e) {
    // Ctrl+S = Save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        if (typeof saveVoucher === 'function') saveVoucher();
    }
    // Ctrl+P = Preview
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        if (typeof printVoucher === 'function') printVoucher();
    }
    // Ctrl+N = New Document
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        if (typeof resetVoucher === 'function') resetVoucher();
    }
    // Escape = Close modal
    if (e.key === 'Escape') {
        const modal = document.getElementById('print-preview-modal');
        if (modal && modal.style.display !== 'none') {
            if (typeof closePrintPreviewModal === 'function') closePrintPreviewModal();
        }
    }
});

// ─── Unsaved Changes Warning ───
let formDirty = false;
document.addEventListener('input', function(e) {
    if (e.target.closest('#entries-body') || e.target.id === 'main-account-search' || e.target.id === 'voucher-desc') {
        formDirty = true;
    }
});
window.addEventListener('beforeunload', function(e) {
    if (formDirty) {
        e.preventDefault();
        e.returnValue = 'لديك تغييرات غير محفوظة. هل تريد المغادرة؟';
        return e.returnValue;
    }
});
// Clear dirty flag after save
const origResetVoucher = window.resetVoucher || resetVoucher;
if (typeof resetVoucher === 'function') {
    const _origReset = resetVoucher;
    // We can't reassign the function declaration, so we hook into save success
}
// Hook: mark clean after successful save toast
const origShowToast = window.showToast;
if (origShowToast) {
    window.showToast = function(msg, type) {
        if (type === 'success' && msg.includes('حفظ')) {
            formDirty = false;
            // Launch confetti on save success!
            if (typeof launchConfetti === 'function') launchConfetti();
        }
        return origShowToast.call(this, msg, type);
    };
}
