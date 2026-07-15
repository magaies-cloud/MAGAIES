/* ═══════════════════════════════════════════════════════
   general-ledger.js — Smart General Ledger (SGL)
   ═══════════════════════════════════════════════════════ */

function fmtNum(n) {
    const val = parseFloat(n) || 0;
    if (val < 0) {
        return `-(${Math.abs(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
    }
    return val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function parseNum(str) {
    return parseFloat(String(str || '0').replace(/,/g, '')) || 0;
}
function showToast(msg, type = 'success') {
    const c = document.getElementById('toast-container');
    if(!c) return;
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = msg;
    c.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

let ALL_ACCOUNTS = [{ id: 'ALL', name: 'جميع الحسابات', icon: '📊', type: 'عام' }];
let ALL_TXNS = []; // Cache to extract cost centers and currencies
let currentDateMode = 'all'; // 'all' or 'range'

let ledgerExchangeRate = 1.0;
let ledgerForeignCurrency = 'USD';

function initLedgerCurrencySettings() {
    try {
        const erpSettings = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        const systemBaseCurrency = erpSettings.currency || 'SDG';
        const activeCurrencies = erpSettings.activeCurrencies || [];
        const configuredForeignCurrency = activeCurrencies.find(c => c !== systemBaseCurrency);
        if (configuredForeignCurrency) {
            ledgerForeignCurrency = configuredForeignCurrency;
        }
    } catch(e) {
        console.error("Error loading foreign currency from settings:", e);
    }

    fetch('api_index.php/exchange-rate')
        .then(res => res.json())
        .then(data => {
            if (data && data.rate) {
                ledgerExchangeRate = parseFloat(data.rate) || 1.0;
            }
        })
        .catch(() => {});
}
initLedgerCurrencySettings();

window.setDateMode = function(mode) {
    currentDateMode = mode;
    const allBtn = document.getElementById('date-all-period-btn');
    const fromCont = document.getElementById('date-from-container');
    const toCont = document.getElementById('date-to-container');
    
    if (mode === 'all') {
        if (allBtn) allBtn.style.display = 'flex';
        if (fromCont) fromCont.style.display = 'none';
        if (toCont) toCont.style.display = 'none';
    } else {
        if (allBtn) allBtn.style.display = 'none';
        if (fromCont) fromCont.style.display = 'flex';
        if (toCont) toCont.style.display = 'flex';
    }
    
    // Auto run report when mode switches and we have transactions loaded
    if (typeof ALL_TXNS !== 'undefined' && ALL_TXNS.length > 0) {
        runReport();
    }
}

function buildAccountsList() {
    ALL_ACCOUNTS = [{ id: 'ALL', name: 'جميع الحسابات', icon: '📊', type: 'عام' }];
    
    const accounts = (JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => !x.deleted);
    accounts.forEach(a => ALL_ACCOUNTS.push({ id: `exp-${a.id}`, name: a.name, icon: '📝', type: 'عام' }));
    
    const safes = (JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => !x.deleted);
    safes.forEach(s => ALL_ACCOUNTS.push({ id: `safe-${s.id}`, name: s.safeName || s.name, icon: '💰', type: 'صندوق' }));
    
    const banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
    banks.forEach(b => ALL_ACCOUNTS.push({ id: `bank-${b.id}`, name: b.bankName || b.name, icon: '🏦', type: 'بنك' }));
    
    const custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    const sups  = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
    custs.forEach(c => ALL_ACCOUNTS.push({ id: `cust-${c.id}`, name: c.name, icon: '👤', type: 'عميل' }));
    sups.forEach(s  => ALL_ACCOUNTS.push({ id: `supp-${s.id}`, name: s.name, icon: '🏭', type: 'مورد' }));

    const accountSelect = document.getElementById('filter-account');
    if (accountSelect) {
        accountSelect.innerHTML = '<option value="ALL">جميع الحسابات</option>';
        ALL_ACCOUNTS.forEach(a => {
            if (a.id !== 'ALL') {
                accountSelect.innerHTML += `<option value="${a.id}">${a.icon} ${a.name} (${a.type})</option>`;
            }
        });
    }
}

/* ─── Account Search & Autocomplete ─── */
function onAccountSearch() {
    const input = document.getElementById('account-search-input');
    const list = document.getElementById('autocomplete-list');
    if (!input || !list) return;
    
    const q = input.value.trim().toLowerCase();
    list.innerHTML = '';
    
    let results = ALL_ACCOUNTS.filter(a => a.id !== 'ALL' && (a.name.toLowerCase().includes(q) || String(a.id).toLowerCase().includes(q)));
    
    if (results.length === 0) {
        list.innerHTML = '<li class="no-results" style="padding: 10px; color: #f87171; text-align: center;">لا يوجد حساب مطابق</li>';
        list.style.display = 'block';
        return;
    }

    results.slice(0, 50).forEach(a => {
        let li = document.createElement('li');
        
        let badgeColor = '#64748b'; // Default
        if (a.type === 'عميل') badgeColor = '#3b82f6';
        if (a.type === 'مورد') badgeColor = '#f59e0b';
        if (a.type === 'صندوق' || a.type === 'خزنة') badgeColor = '#10b981';
        if (a.type === 'بنك') badgeColor = '#8b5cf6';
        
        li.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                <span style="font-weight: 600;">${a.icon} ${a.name}</span>
                <span style="background:${badgeColor}; color:white; padding:3px 8px; border-radius:6px; font-size:0.75rem; font-weight: bold; min-width: 50px; text-align: center;">${a.type}</span>
            </div>
        `;
        li.onmousedown = () => selectAccount(a.id, a.name, a.icon);
        list.appendChild(li);
    });
    
    list.style.display = 'block';
}

function onAccountSearchKey(e) {
    if (e.key === 'Escape') {
        const list = document.getElementById('autocomplete-list');
        if (list) list.style.display = 'none';
    }
}

window.selectAccount = function selectAccount(id, name, icon) {
    document.getElementById('filter-account').value = id;
    const wrap = document.getElementById('account-search-wrap');
    const chip = document.getElementById('selected-chip');
    
    if (wrap) wrap.style.display = 'none';
    
    if (chip) {
        chip.style.display = 'flex';
        document.getElementById('chip-icon').innerText = icon || '🏷️';
        document.getElementById('chip-name').innerText = name;
    }
    
    const list = document.getElementById('autocomplete-list');
    if (list) list.style.display = 'none';
    
    runReport(); // Auto run report on selection
}

function clearAccountSearch() {
    document.getElementById('filter-account').value = 'ALL';
    
    const chip = document.getElementById('selected-chip');
    if (chip) chip.style.display = 'none';
    
    const wrap = document.getElementById('account-search-wrap');
    const input = document.getElementById('account-search-input');
    
    if (wrap) wrap.style.display = 'flex';
    if (input) {
        input.value = '';
        input.focus();
    }
    
    // Reset Report View
    const tbody = document.getElementById('ledger-tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="empty-state">
                <td colspan="14">
                    <div style="text-align: center; padding: 40px; color: #64748b;">
                        <div style="font-size: 3rem; margin-bottom: 10px; opacity: 0.5;">📊</div>
                        <h3>لا توجد بيانات للعرض</h3>
                        <p style="font-size: 0.9rem; margin-top: 5px;">الرجاء تحديد الحساب وتحديد الفترة ثم الضغط على "تشغيل التقرير"</p>
                    </div>
                </td>
            </tr>
        `;
    }
    
    const summaryCards = document.getElementById('summary-cards');
    if (summaryCards) summaryCards.style.display = 'none';
    
    const printHeader = document.getElementById('print-header');
    if (printHeader) printHeader.style.display = 'none';
    
    const auditStamp = document.getElementById('audit-stamp');
    if (auditStamp) auditStamp.style.display = 'none';
    
    const secActions = document.getElementById('secondary-actions');
    if (secActions) {
        secActions.style.display = 'none';
        secActions.style.opacity = '0';
        secActions.style.transform = 'translateY(10px)';
    }

    onAccountSearch(); // show list again
}

// Ensure autocomplete closes on outside click
document.addEventListener('mousedown', function(e) {
    if (!e.target.closest('.search-wrap')) {
        const list = document.getElementById('autocomplete-list');
        if (list) list.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('account-search-input');
    if (input) input.addEventListener('focus', onAccountSearch);
});

/* ─── Collect Transactions & Meta (Currency, Cost Center) ─── */
function collectAllTransactions() {
    let txns = [];
    const timeNow = new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}); // Fallback time

    // 1. Single Receipts
    const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    receipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        
        const rate = parseNum(v.rate || 1);
        const isBaseSDG = (currency === 'SDG');
        const amount = parseNum(v.amount);
        const amountLocal = parseNum(v.amountLocal || (amount * rate));
        const valLoc = amountLocal;
        const valFc = isBaseSDG ? 0 : amount;

        // Receiving side (Safe/Bank)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'receipt', typeLabel: 'سند قبض',
            account: v.toAccount || 'safe-001', counterAccount: v.fromAccount,
            statement: 'سند قبض #' + (v.voucherNo || v.id),
            additionalNote: v.fromStatement || 'سند قبض نقدي',
            debit: valLoc, credit: 0, 
            debitForeign: valFc, creditForeign: 0,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });
        // Disbursing side (Customer/Supplier pays us)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'receipt', typeLabel: 'سند صرف',
            account: v.fromAccount, counterAccount: v.toAccount || 'safe-001',
            statement: 'سند قبض #' + (v.voucherNo || v.id),
            additionalNote: v.fromStatement || 'سند صرف نقدي',
            debit: 0, credit: valLoc, 
            debitForeign: 0, creditForeign: valFc,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });
    });

    // 2. Single Payments
    const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    payments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;

        const rate = parseNum(v.rate || 1);
        const isBaseSDG = (currency === 'SDG');
        const amount = parseNum(v.amount);
        const amountLocal = parseNum(v.amountLocal || (amount * rate));
        const valLoc = amountLocal;
        const valFc = isBaseSDG ? 0 : amount;

        // Disbursing side (Safe/Bank)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
            account: v.fromAccount || 'safe-001', counterAccount: v.toAccount,
            statement: 'سند صرف #' + (v.voucherNo || v.id),
            additionalNote: v.toStatement || 'سند صرف نقدي',
            debit: 0, credit: valLoc, 
            debitForeign: 0, creditForeign: valFc,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });
        // Receiving side (Supplier/Customer receives from us)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند استلام',
            account: v.toAccount, counterAccount: v.fromAccount || 'safe-001',
            statement: 'سند صرف #' + (v.voucherNo || v.id),
            additionalNote: v.toStatement || 'سند استلام نقدي',
            debit: valLoc, credit: 0, 
            debitForeign: valFc, creditForeign: 0,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });
    });

    // 2b. Multi-Receipts
    const multiReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
    multiReceipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        const mainAcc = v.toAccount || 'safe-001';

        const rate = parseNum(v.exchangeRate || 1);
        const isBaseSDG = (currency === 'SDG');
        const totalAmount = parseNum(v.totalAmount);
        const valLoc = isBaseSDG ? totalAmount : totalAmount * rate;
        const valFc = isBaseSDG ? 0 : totalAmount;

        txns.push({
            date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
            account: mainAcc, counterAccount: 'حسابات فرعية متعددة',
            statement: v.description || 'قبض مجمع',
            additionalNote: v.description || 'سند قبض مجمع',
            debit: valLoc, credit: 0, 
            debitForeign: valFc, creditForeign: 0,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                const amt = parseNum(item.amount);
                const amtLoc = isBaseSDG ? amt : amt * rate;
                const amtFc = isBaseSDG ? 0 : amt;

                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                    account: item.account, counterAccount: mainAcc,
                    statement: item.statement || 'قبض مجمع فرعي',
                    additionalNote: item.note || v.description || 'سند قبض مجمع',
                    debit: 0, credit: amtLoc, 
                    debitForeign: 0, creditForeign: amtFc,
                    costCenter: item.costCenter || costCenter, 
                    currency,
                    exchangeRate: rate,
                    isPosted: isPosted
                });
            });
        }
    });

    // 2c. Multi-Payments
    const multiPayments = JSON.parse(localStorage.getItem('erp_payments_v1')) || [];
    multiPayments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        const mainAcc = v.fromAccount || 'safe-001';

        const rate = parseNum(v.exchangeRate || 1);
        const isBaseSDG = (currency === 'SDG');
        const totalAmount = parseNum(v.totalAmount);
        const valLoc = isBaseSDG ? totalAmount : totalAmount * rate;
        const valFc = isBaseSDG ? 0 : totalAmount;

        txns.push({
            date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
            account: mainAcc, counterAccount: 'حسابات فرعية متعددة',
            statement: v.description || 'صرف مجمع',
            additionalNote: v.description || 'سند صرف مجمع',
            debit: 0, credit: valLoc, 
            debitForeign: 0, creditForeign: valFc,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                const amt = parseNum(item.amount);
                const amtLoc = isBaseSDG ? amt : amt * rate;
                const amtFc = isBaseSDG ? 0 : amt;

                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                    account: item.account, counterAccount: mainAcc,
                    statement: item.statement || 'صرف مجمع فرعي',
                    additionalNote: item.note || v.description || 'سند صرف مجمع',
                    debit: amtLoc, credit: 0, 
                    debitForeign: amtFc, creditForeign: 0,
                    costCenter: item.costCenter || costCenter, 
                    currency,
                    exchangeRate: rate,
                    isPosted: isPosted
                });
            });
        }
    });

    // 3. Journal Vouchers
    const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
    jvs.forEach(v => {
        if (!v.lines) return;
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        v.lines.forEach(line => {
            const costCenter = line.costCenter || v.costCenter || 'بدون مركز';
            const currency = line.currency || v.currency || 'SDG';

            const rate = parseNum(line.rate || 1);
            const isBaseSDG = (currency === 'SDG');
            const deb = parseNum(line.debit);
            const cred = parseNum(line.credit);
            const debLoc = isBaseSDG ? deb : deb * rate;
            const debFc = isBaseSDG ? 0 : deb;
            const credLoc = isBaseSDG ? cred : cred * rate;
            const credFc = isBaseSDG ? 0 : cred;

            txns.push({
                date: v.date, time: time, ref: v.id, type: 'jv', typeLabel: 'قيد يومي',
                account: line.account, counterAccount: '-',
                statement: 'قيد يومي #' + (v.voucherNo || v.id),
                additionalNote: v.statement || line.note || 'قيد يومي',
                debit: debLoc, credit: credLoc, 
                debitForeign: debFc, creditForeign: credFc,
                costCenter, currency,
                exchangeRate: rate,
                isPosted: isPosted
            });
        });
    });

    // 4. Sales Invoices
    const sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && !x.isGDN);
    sales.forEach(inv => {
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        if (!total) return;
        const costCenter = inv.costCenter || 'بدون مركز';
        const currency = inv.currency || 'SDG';
        const time = inv.time || timeNow;
        const refNo = inv.invoiceNo || inv.id;
        const customerAcct = inv.customerId || inv.customer;
        const isPosted = inv.isPosted !== false;

        const rate = parseNum(inv.exchangeRate || 1);
        const isBaseSDG = (currency === 'SDG');
        const amtLoc = isBaseSDG ? total : total * rate;
        const amtFc = isBaseSDG ? 0 : total;

        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'invoice', typeLabel: 'مبيعات',
            account: customerAcct, counterAccount: 'revenue-sales',
            statement: 'فاتورة مبيعات #' + refNo,
            additionalNote: `مبيعات آجلة — ${inv.customerName || inv.customer || ''}`,
            debit: amtLoc, credit: 0, 
            debitForeign: amtFc, creditForeign: 0,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });
        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'invoice', typeLabel: 'مبيعات',
            account: 'revenue-sales', counterAccount: customerAcct,
            statement: 'فاتورة مبيعات #' + refNo,
            additionalNote: `إيرادات — ${inv.customerName || inv.customer || ''}`,
            debit: 0, credit: amtLoc, 
            debitForeign: 0, creditForeign: amtFc,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });

        if (inv.paymentMethod === 'hybrid' || inv.paymentMethod === 'multipay') {
            // A. Cash Safe Portion
            const cash = parseNum(inv.cashAmount || 0);
            if (cash > 0) {
                const cashLoc = isBaseSDG ? cash : cash * rate;
                const cashFc = isBaseSDG ? 0 : cash;
                const paymentAcct = inv.paymentAccountId || 'safe-001';
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'سداد فاتورة',
                    account: paymentAcct, counterAccount: customerAcct,
                    statement: 'استلام نقدية (نقداً)',
                    additionalNote: 'سداد نقدي مع الفاتورة',
                    debit: cashLoc, credit: 0, 
                    debitForeign: cashFc, creditForeign: 0,
                    costCenter, currency,
                    exchangeRate: rate,
                    isPosted: isPosted
                });
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'سداد فاتورة',
                    account: customerAcct, counterAccount: paymentAcct,
                    statement: 'استلام نقدية (نقداً)',
                    additionalNote: 'سداد نقدي مع الفاتورة',
                    debit: 0, credit: cashLoc, 
                    debitForeign: 0, creditForeign: cashFc,
                    costCenter, currency,
                    exchangeRate: rate,
                    isPosted: isPosted
                });
            }
            // B. Bank Transfer Portions
            if (inv.bankTransfers && inv.bankTransfers.length > 0) {
                inv.bankTransfers.forEach((bt, idx) => {
                    const amt = parseNum(bt.amount || 0);
                    if (amt > 0) {
                        const amtLocBt = isBaseSDG ? amt : amt * rate;
                        const amtFcBt = isBaseSDG ? 0 : amt;
                        const bankAcct = bt.bankAccountId || 'bank-001';
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-BT${idx+1}`, type: 'receipt', typeLabel: 'سداد فاتورة',
                            account: bankAcct, counterAccount: customerAcct,
                            statement: `تحويل بنكي مرجع #${bt.reference || ''}`,
                            additionalNote: 'سداد تحويل بنكي مع الفاتورة',
                            debit: amtLocBt, credit: 0, 
                            debitForeign: amtFcBt, creditForeign: 0,
                            costCenter, currency,
                            exchangeRate: rate,
                            isPosted: isPosted
                        });
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-BT${idx+1}`, type: 'receipt', typeLabel: 'سداد فاتورة',
                            account: customerAcct, counterAccount: bankAcct,
                            statement: `تحويل بنكي مرجع #${bt.reference || ''}`,
                            additionalNote: 'سداد تحويل بنكي مع الفاتورة',
                            debit: 0, credit: amtLocBt, 
                            debitForeign: 0, creditForeign: amtFcBt,
                            costCenter, currency,
                            exchangeRate: rate,
                            isPosted: isPosted
                        });
                    }
                });
            }
            // C. Cheque Portions
            if (inv.cheques && inv.cheques.length > 0) {
                inv.cheques.forEach((ch, idx) => {
                    const amt = parseNum(ch.amount || 0);
                    if (amt > 0) {
                        const amtLocCh = isBaseSDG ? amt : amt * rate;
                        const amtFcCh = isBaseSDG ? 0 : amt;
                        const chequeAcct = 'cheques-receivable';
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-CH${idx+1}`, type: 'receipt', typeLabel: 'سداد فاتورة',
                            account: chequeAcct, counterAccount: customerAcct,
                            statement: `شيك رقم #${ch.chequeNo || ''} استحقاق ${ch.dueDate || ''}`,
                            additionalNote: 'سداد شيك مع الفاتورة',
                            debit: amtLocCh, credit: 0, 
                            debitForeign: amtFcCh, creditForeign: 0,
                            costCenter, currency,
                            exchangeRate: rate,
                            isPosted: isPosted
                        });
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-CH${idx+1}`, type: 'receipt', typeLabel: 'سداد فاتورة',
                            account: customerAcct, counterAccount: chequeAcct,
                            statement: `شيك رقم #${ch.chequeNo || ''} استحقاق ${ch.dueDate || ''}`,
                            additionalNote: 'سداد شيك مع الفاتورة',
                            debit: 0, credit: amtLocCh, 
                            debitForeign: 0, creditForeign: amtFcCh,
                            costCenter, currency,
                            exchangeRate: rate,
                            isPosted: isPosted
                        });
                    }
                });
            }
        } else if (inv.paymentMethod !== 'credit') {
            const paymentAcct = inv.paymentAccountId || 'safe-001';
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'سداد فاتورة',
                account: paymentAcct, counterAccount: customerAcct,
                statement: 'استلام نقدية',
                additionalNote: 'سداد نقدي مع الفاتورة',
                debit: amtLoc, credit: 0, 
                debitForeign: amtFc, creditForeign: 0,
                costCenter, currency,
                exchangeRate: rate,
                isPosted: isPosted
            });
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'سداد فاتورة',
                account: customerAcct, counterAccount: paymentAcct,
                statement: 'استلام نقدية',
                additionalNote: 'سداد نقدي مع الفاتورة',
                debit: 0, credit: amtLoc, 
                debitForeign: 0, creditForeign: amtFc,
                costCenter, currency,
                exchangeRate: rate,
                isPosted: isPosted
            });
        }
    });

    // 5. Purchase Invoices
    const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN);
    purchases.forEach(inv => {
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        if (!total) return;
        const costCenter = inv.costCenter || 'بدون مركز';
        const currency = inv.currency || 'SDG';
        const time = inv.time || timeNow;
        const refNo = inv.invoiceNo || inv.id;
        const supplierAcct = inv.supplierId || inv.supplier || inv.customer;
        const isPosted = inv.isPosted !== false;

        const rate = parseNum(inv.exchangeRate || 1);
        const isBaseSDG = (currency === 'SDG');
        const amtLoc = isBaseSDG ? total : total * rate;
        const amtFc = isBaseSDG ? 0 : total;

        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'purchase', typeLabel: 'مشتريات',
            account: 'exp-purchases', counterAccount: supplierAcct,
            statement: 'فاتورة مشتريات #' + refNo,
            additionalNote: `مشتريات آجلة — ${inv.supplierName || inv.supplier || ''}`,
            debit: amtLoc, credit: 0, 
            debitForeign: amtFc, creditForeign: 0,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });
        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'purchase', typeLabel: 'مشتريات',
            account: supplierAcct, counterAccount: 'exp-purchases',
            statement: 'فاتورة مشتريات #' + refNo,
            additionalNote: `مستحقات مورد — ${inv.supplierName || inv.supplier || ''}`,
            debit: 0, credit: amtLoc, 
            debitForeign: 0, creditForeign: amtFc,
            costCenter, currency,
            exchangeRate: rate,
            isPosted: isPosted
        });

        if (inv.paymentMethod !== 'credit') {
            const paymentAcct = inv.paymentAccountId || 'safe-001';
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: 'payment', typeLabel: 'سداد مشتريات',
                account: paymentAcct, counterAccount: supplierAcct,
                statement: 'صرف نقدية',
                additionalNote: 'سداد نقدي مع الفاتورة',
                debit: 0, credit: amtLoc,
                debitForeign: 0, creditForeign: amtFc,
                costCenter, currency,
                exchangeRate: rate,
                isPosted: isPosted
            });
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: 'payment', typeLabel: 'سداد مشتريات',
                account: supplierAcct, counterAccount: paymentAcct,
                statement: 'صرف نقدية',
                additionalNote: 'سداد نقدي مع الفاتورة',
                debit: amtLoc, credit: 0,
                debitForeign: amtFc, creditForeign: 0,
                costCenter, currency,
                exchangeRate: rate,
                isPosted: isPosted
            });
        }
    });

    return txns;
}

function extractFilters() {
    ALL_TXNS = collectAllTransactions();
    
    // Currencies
    const curSet = new Set();
    // Cost Centers
    const ccSet = new Set();

    ALL_TXNS.forEach(t => {
        if(t.currency) curSet.add(t.currency);
        if(t.costCenter) ccSet.add(t.costCenter);
    });

    const curSelect = document.getElementById('filter-currency');
    if(curSelect) {
        curSelect.innerHTML = '<option value="ALL">جميع العملات</option>';
        Array.from(curSet).forEach(c => {
            curSelect.innerHTML += `<option value="${c}">${c}</option>`;
        });
    }

    const ccSelect = document.getElementById('filter-costcenter');
    if(ccSelect) {
        ccSelect.innerHTML = '<option value="ALL">جميع مراكز التكلفة</option>';
        Array.from(ccSet).forEach(cc => {
            ccSelect.innerHTML += `<option value="${cc}">${cc}</option>`;
        });
    }
}

/* ─── Initialization ─── */
let choicesInstances = [];
function initGeneralLedgerApp() {
    buildAccountsList();
    extractFilters(); // Build filters dynamically
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    document.getElementById('filter-from').value = firstDay.toISOString().slice(0, 10);
    document.getElementById('filter-to').value = now.toISOString().slice(0, 10);
    
    // Set default date mode to all period
    setDateMode('all');

    // Render custom quick presets slots
    if (typeof renderQuickPresets === 'function') {
        renderQuickPresets();
    }

    if (typeof Choices !== 'undefined') {
        document.querySelectorAll('.choices-select').forEach(el => {
            choicesInstances.push(new Choices(el, {
                searchEnabled: true,
                itemSelectText: '',
                noResultsText: 'لا توجد نتائج',
                noChoicesText: 'لا توجد خيارات',
                searchPlaceholderValue: 'بحث...',
                shouldSort: false
            }));
        });
    }

    // Auto load account from URL parameter if present
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const accountIdParam = urlParams.get('accountId') || urlParams.get('id');
        if (accountIdParam) {
            const found = ALL_ACCOUNTS.find(a => String(a.id) === String(accountIdParam));
            if (found) {
                selectAccount(found.id, found.name, found.icon);
            } else {
                const cleanParam = String(accountIdParam).replace(/^(cust|supp|bank|safe|exp|sub|sust)-/i, '');
                const foundClean = ALL_ACCOUNTS.find(a => String(a.id).replace(/^(cust|supp|bank|safe|exp|sub|sust)-/i, '') === cleanParam);
                if (foundClean) {
                    selectAccount(foundClean.id, foundClean.name, foundClean.icon);
                }
            }
        }
    } catch (e) {
        console.error("Error parsing URL accountId:", e);
    }
}

if (window.APP_READY) {
    initGeneralLedgerApp();
} else {
    document.addEventListener('DBReady', initGeneralLedgerApp);
}

function getAccountName(id) {
    if(id === 'ALL') return 'جميع الحسابات';
    if(id === '-') return '-';
    
    let idStr = String(id).trim();
    
    // 1. Direct ID match (case-insensitive)
    let found = ALL_ACCOUNTS.find(a => String(a.id).toLowerCase() === idStr.toLowerCase());
    if (found) return found.name;
    
    // 2. Clean prefix (any English letters followed by separator like -, _, :, /, or space)
    let cleanId = idStr.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
    
    // 3. Search by clean ID or clean Name match
    found = ALL_ACCOUNTS.find(a => {
        const aId = String(a.id).toLowerCase();
        const aName = String(a.name).toLowerCase();
        const cleanAId = aId.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
        
        return aId === cleanId.toLowerCase() || 
               cleanAId === cleanId.toLowerCase() ||
               aName === cleanId.toLowerCase();
    });
    
    if (found) return found.name;
    
    // 4. Try matching if the cleanId is contained in the account name or ID (or vice versa)
    if (cleanId.length > 1) {
        found = ALL_ACCOUNTS.find(a => {
            const aName = String(a.name).toLowerCase();
            const aId = String(a.id).toLowerCase();
            return aName.includes(cleanId.toLowerCase()) || 
                   cleanId.toLowerCase().includes(aName) ||
                   aId.includes(cleanId.toLowerCase()) ||
                   cleanId.toLowerCase().includes(aId);
        });
        if (found) return found.name;
    }
    
    // 5. If everything fails, return the cleaned ID instead of the raw ID if it contains non-English chars
    return cleanId || idStr;
}

/* ─── Drill Down & Attachments ─── */
function openDrillDown(type, ref) {
    let target = '';
    if (type === 'invoice') target = `sales-invoice.php?id=${ref}`;
    else if (type === 'purchase') target = `purchase.php?id=${ref}`;
    else if (type === 'jv') target = `journal-voucher.php?id=${ref}`;
    else if (type === 'receipt') target = `single-cash-receipt.php?id=${ref}`;
    else if (type === 'payment') target = `single-cash-payment.php?id=${ref}`;
    
    if(target) {
        window.location.href = target;
    }
}

/* ─── Sharing Logic ─── */
function getAccountNameForShare() {
    const chipName = document.getElementById('chip-name');
    if (chipName && chipName.innerText && chipName.innerText.trim()) {
        return chipName.innerText.trim();
    }
    const accSelect = document.getElementById('account-search-input');
    if (accSelect && accSelect.value) {
        return accSelect.value.trim();
    }
    return 'حساب عام';
}

function getReportSummaryText() {
    const accName = getAccountNameForShare();
    
    const debitEl = document.getElementById('sum-debit');
    const debitFEl = document.getElementById('sum-debit-foreign');
    const creditEl = document.getElementById('sum-credit');
    const creditFEl = document.getElementById('sum-credit-foreign');
    const balanceEl = document.getElementById('sum-balance');
    const balanceFEl = document.getElementById('sum-balance-foreign');
    
    const debit = debitEl ? debitEl.innerText : '0.00';
    const debitF = debitFEl ? debitFEl.innerText : '';
    const credit = creditEl ? creditEl.innerText : '0.00';
    const creditF = creditFEl ? creditFEl.innerText : '';
    const balance = balanceEl ? balanceEl.innerText : '0.00';
    const balanceF = balanceFEl ? balanceFEl.innerText : '';
    
    let text = `مرحباً،\nهذا ملخص كشف حساب: *${accName}*\n\n`;
    text += `إجمالي المدين: ${debit} ${debitF ? ` ${debitF}` : ''}\n`;
    text += `إجمالي الدائن: ${credit} ${creditF ? ` ${creditF}` : ''}\n`;
    text += `الرصيد النهائي: ${balance} ${balanceF ? ` ${balanceF}` : ''}\n\n`;
    text += `يرجى مراجعة التفاصيل والمرفقات إذا وجدت.`;
    
    return text;
}

function getSelectedAccountDetails() {
    const acctId = document.getElementById('filter-account').value;
    if (!acctId || acctId === 'ALL') return null;

    if (acctId.startsWith('cust-')) {
        const realId = acctId.replace('cust-', '');
        const custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
        const customer = custs.find(c => String(c.id) === String(realId));
        if (customer) {
            return {
                type: 'customer',
                phone: customer.phone ? customer.phone.trim() : '',
                whatsapp: customer.whatsapp ? customer.whatsapp.trim() : '',
                email: customer.email ? customer.email.trim() : '',
                name: customer.name
            };
        }
    } else if (acctId.startsWith('supp-')) {
        const realId = acctId.replace('supp-', '');
        const sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        const supplier = sups.find(s => String(s.id) === String(realId));
        if (supplier) {
            return {
                type: 'supplier',
                phone: supplier.phone ? supplier.phone.trim() : '',
                whatsapp: supplier.whatsapp ? supplier.whatsapp.trim() : '',
                email: supplier.email ? supplier.email.trim() : '',
                name: supplier.name
            };
        }
    }
    return null;
}

function cleanPhoneForWhatsApp(phone) {
    if (!phone) return '';
    let cleaned = phone.trim().replace(/\+/g, '');
    cleaned = cleaned.replace(/\D/g, '');
    if (cleaned.startsWith('00')) {
        cleaned = cleaned.slice(2);
    }
    let companyCountryCode = '249'; // default fallback (Sudan)
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        const companyPhone = cfg.phone || '';
        if (companyPhone) {
            let compClean = companyPhone.trim().replace(/\+/g, '').replace(/\D/g, '');
            if (compClean.startsWith('00')) compClean = compClean.slice(2);
            if (compClean.startsWith('966')) companyCountryCode = '966';
            else if (compClean.startsWith('249')) companyCountryCode = '249';
            else if (compClean.startsWith('20')) companyCountryCode = '20';
            else if (compClean.length > 3) {
                companyCountryCode = compClean.slice(0, 3);
            }
        }
    } catch(e) {}

    if (cleaned.startsWith('0') && !cleaned.startsWith('00')) {
        cleaned = companyCountryCode + cleaned.slice(1);
    }
    return cleaned;
}

window.shareViaWhatsApp = async function() {
    const tbody = document.getElementById('ledger-tbody');
    if (!tbody || tbody.querySelector('.empty-state')) {
        return showToast('لا توجد بيانات للإرسال، شغّل التقرير أولاً', 'warning');
    }

    let defaultPhone = '';
    let accountName = getAccountNameForShare();
    let accountTypeLabel = 'الحساب';

    const details = getSelectedAccountDetails();
    if (details) {
        defaultPhone = details.whatsapp || details.phone || '';
        accountName = details.name || accountName;
        accountTypeLabel = details.type === 'customer' ? 'العميل' : 'المورد';
    }

    const phone = await new Promise((resolve) => {
        const overlayId = 'wa-phone-prompt-overlay';
        if (document.getElementById(overlayId)) document.getElementById(overlayId).remove();

        window.toggleEditPhone = function() {
            const displayWrap = document.getElementById('wa-phone-display-wrap');
            const inputWrap = document.getElementById('wa-phone-input-wrap');
            if (displayWrap && inputWrap) {
                if (displayWrap.style.display !== 'none') {
                    displayWrap.style.display = 'none';
                    inputWrap.style.display = 'block';
                    const inp = document.getElementById('wa-prompt-input');
                    if (inp) {
                        inp.focus();
                        inp.select();
                    }
                } else {
                    displayWrap.style.display = 'flex';
                    inputWrap.style.display = 'none';
                }
            }
        };

        const html = `
        <div id="${overlayId}" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:999999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);">
            <div style="background:linear-gradient(145deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95)); border:1px solid rgba(37,211,102,0.4); border-radius:24px; padding:35px; width:90%; max-width:450px; text-align:center; transform:translateY(40px) scale(0.9); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow:0 25px 50px -12px rgba(37,211,102,0.2), inset 0 1px 0 rgba(255,255,255,0.1);">
                
                <div style="width:80px; height:80px; background:linear-gradient(135deg, #25D366, #128C7E); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#fff; box-shadow:0 10px 20px rgba(37,211,102,0.4); border:3px solid rgba(255,255,255,0.2);">
                    <svg viewBox="0 0 24 24" width="45" height="45" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                
                <h3 style="color:#f8fafc; font-size:1.6rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0; font-weight:800;">إرسال كشف الحساب عبر واتساب</h3>
                <p style="color:#94a3b8; font-size:0.95rem; margin-bottom:20px; line-height:1.6; font-family:'Tajawal',sans-serif;">يرجى تأكيد إرسال التقرير إلى المورد/العميل:</p>
                
                <!-- Display card -->
                <div id="wa-phone-display-wrap" style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 15px 20px; margin-bottom: 25px; text-align: right;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">👤 ${accountTypeLabel}:</span>
                        <span style="font-size: 1.15rem; color: #25d366; font-weight: 800;">${accountName}</span>
                        <span style="font-size: 0.95rem; color: #38bdf8; font-weight: 700; direction: ltr; margin-top: 4px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>📞</span> <span id="wa-display-phone-text">${defaultPhone || 'لا يوجد رقم مسجل'}</span>
                        </span>
                    </div>
                    <button type="button" onclick="toggleEditPhone()" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); color: #38bdf8; border-radius: 12px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(56, 189, 248, 0.2)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(56, 189, 248, 0.1)'; this.style.transform='scale(1)';" title="إرسال لرقم آخر">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Input container (Hidden by default) -->
                <div id="wa-phone-input-wrap" style="display: none; margin-bottom: 25px; animation: wa-scale-up 0.3s ease-out; text-align: right;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">✏️ إرسال إلى رقم هاتف آخر:</span>
                        <button type="button" onclick="toggleEditPhone()" style="background: transparent; border: none; color: #cbd5e1; font-size: 0.75rem; cursor: pointer; text-decoration: underline; outline: none;">رجوع</button>
                    </div>
                    <div style="position: relative;">
                        <input type="tel" id="wa-prompt-input" placeholder="رقم الهاتف الجديد..." value="${defaultPhone}" oninput="document.getElementById('wa-display-phone-text').textContent = this.value;" style="width:100%; background:rgba(0,0,0,0.4); border:2px solid rgba(255,255,255,0.1); color:#fff; padding:12px 15px; border-radius:12px; font-size:1.15rem; text-align:center; transition:all 0.3s; outline:none; font-family:'Inter',sans-serif;" onfocus="this.style.borderColor='#25d366'; this.style.boxShadow='0 0 12px rgba(37,211,102,0.2)';" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-send" style="background:linear-gradient(135deg, #25d366, #1da851); color:#fff; border:none; padding:12px 30px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.3s; box-shadow:0 4px 15px rgba(37,211,102,0.4); display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(37,211,102,0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(37,211,102,0.4)';">
                        <span>إرسال</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                    <button id="wa-prompt-cancel" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:12px; font-weight:bold; font-size:1.1rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">إلغاء</button>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);

        const overlay = document.getElementById(overlayId);
        const modal = overlay.children[0];
        const input = document.getElementById('wa-prompt-input');

        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0) scale(1)';
            setTimeout(() => input.focus(), 400);
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

    let cfg = {};
    try { cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}'); } catch(e) {}
    
    const companyName = cfg.companyName || 'اسم الشركة غير مسجل';
    const companyPhone = cfg.phone || '';
    const companyAddress = cfg.address || '';
    
    let logoSrc = cfg.logoBase64 || '';
    if (!logoSrc) {
        const headerLogo = document.querySelector('.company-logo, #hdr-c-logo');
        if (headerLogo && headerLogo.src && !headerLogo.src.includes('placeholder')) {
            logoSrc = headerLogo.src;
        }
    }
    const logoImg = logoSrc ? `<img src="${logoSrc}" style="max-height:80px; max-width:200px; object-fit:contain;">` : '';

    const companyHeaderHtml = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px;">
            <div style="text-align: right;">
                <h1 style="margin: 0; font-size: 1.5rem;">${companyName}</h1>
                <div style="font-size: 0.9rem; color: #555; margin-top: 5px;">
                    ${companyAddress ? `<div>العنوان: ${companyAddress}</div>` : ''}
                    ${companyPhone ? `<div>الهاتف: ${companyPhone}</div>` : ''}
                </div>
            </div>
            <div>
                ${logoImg}
            </div>
        </div>
    `;

    const printContainer = document.createElement('div');
    printContainer.style.position = 'fixed';
    printContainer.style.top = '0';
    printContainer.style.left = '0';
    printContainer.style.width = '1000px';
    printContainer.style.zIndex = '-99999';
    printContainer.style.opacity = '0.01';
    printContainer.style.pointerEvents = 'none';
    printContainer.style.background = '#fff';
    printContainer.style.color = '#000';
    printContainer.style.padding = '30px';
    
    const originalTable = document.querySelector('.ledger-table');
    const tableClone = originalTable.cloneNode(true);
    
    const htmlContent = `
        <html dir="rtl" lang="ar">
        <head>
            <style>
                body { font-family: 'Tajawal', sans-serif; background: #fff; color: #000; direction: rtl; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9rem; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
                th { background: #f3f4f6; color: #333; font-weight: bold; }
                .td-debit { color: #059669; font-weight: bold; }
                .td-credit { color: #dc2626; font-weight: bold; }
                .td-balance-dr { color: #059669; font-weight: bold; }
                .td-balance-cr { color: #dc2626; font-weight: bold; }
                h2 { text-align: center; margin: 10px 0 5px; }
                #print-meta { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; font-weight: bold; }
            </style>
        </head>
        <body>
            ${companyHeaderHtml}
            <h2>دفتر الأستاذ العام</h2>
            <div id="print-meta">
                <span>الحساب: <strong>${getAccountNameForShare()}</strong></span>
                <span>الفترة: <strong>${currentDateMode === 'all' ? 'كل الفترة' : `من ${document.getElementById('filter-from').value || '-'} إلى ${document.getElementById('filter-to').value || '-'}`}</strong></span>
                <span>تاريخ الطباعة: <strong>${new Date().toLocaleDateString('ar-SD')}</strong></span>
            </div>
            ${tableClone.outerHTML}
        </body>
        </html>
    `;
    printContainer.innerHTML = htmlContent;
    document.body.appendChild(printContainer);

    const periodText = currentDateMode === 'all' ? 'لكل الفترة' : 'لفتـرة من ' + document.getElementById('filter-from').value + ' إلى ' + document.getElementById('filter-to').value;
    const message = 'مرحباً، مرفق كشف حسابكم الخاص بـ ' + getAccountNameForShare() + ' ' + periodText;

    if (typeof sendPDFViaWhatsApp === 'function') {
        await sendPDFViaWhatsApp(printContainer, phone, message);
    } else {
        showToast('برنامج ربط الواتساب غير متوفر في هذه الصفحة', 'error');
    }
    
    document.body.removeChild(printContainer);
}

function shareViaEmail() {
    const accName = getAccountNameForShare();
    const details = getSelectedAccountDetails();
    const recipient = (details && details.email) ? details.email.trim() : '';
    
    const subject = encodeURIComponent(`كشف حساب - ${accName}`);
    const body = encodeURIComponent(getReportSummaryText());
    
    window.location.href = `mailto:${recipient}?subject=${subject}&body=${body}`;
}

function showAttachment(e, ref) {
    e.stopPropagation(); // prevent row click drill-down
    const m = document.getElementById('attach-modal');
    if(m) m.style.display = 'flex';
}

/* ─── Run Report ─── */
function runReport() {
    const acctFilter = document.getElementById('filter-account').value;
    const fromDate = document.getElementById('filter-from').value;
    const toDate = document.getElementById('filter-to').value;
    const typeFilter = document.getElementById('filter-type').value;
    const ccFilter = document.getElementById('filter-costcenter').value;
    const curFilter = document.getElementById('filter-currency').value;
    const postedFilter = document.getElementById('filter-posted') ? document.getElementById('filter-posted').value : 'ALL';

    if (currentDateMode === 'range' && (!fromDate || !toDate)) {
        return showToast('يرجى تحديد الفترة الزمنية', 'warning');
    }

    const btnIcon = document.getElementById('btn-run-icon');
    const btnSpinner = document.getElementById('btn-run-spinner');
    const btnText = document.getElementById('btn-run-text');
    if (btnIcon && btnSpinner) {
        btnIcon.style.display = 'none';
        btnSpinner.style.display = 'inline-block';
        btnText.innerText = 'جاري التحليل...';
    }

    setTimeout(() => {
        executeReportLogic(acctFilter, fromDate, toDate, typeFilter, ccFilter, curFilter, postedFilter);
        if (btnIcon && btnSpinner) {
            btnIcon.style.display = 'inline-block';
            btnSpinner.style.display = 'none';
            btnText.innerText = 'تشغيل التقرير الذكي';
        }
    }, 400);
}

function executeReportLogic(acctFilter, fromDate, toDate, typeFilter, ccFilter, curFilter, postedFilter) {
    let txns = [...ALL_TXNS];

    // Filter Account and Setup Opening Balance
    let intrinsicBal = 0;
    let accountCurrency = 'SDG';
    let accountExRate = 1;

    if (acctFilter !== 'ALL') {
        const cleanFilter = String(acctFilter).replace(/^(cust|supp|bank|safe|exp)-/, '');
        txns = txns.filter(t => {
            if (String(t.account) === String(acctFilter)) return true;
            const cleanAcc = String(t.account).replace(/^(cust|supp|bank|safe|exp)-/, '');
            return cleanAcc === cleanFilter;
        });
        
        let custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
        let sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        let banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
        let safes = (JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => !x.deleted);
        let exp = (JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => !x.deleted);
        
        let foundAccount = null;
        if (acctFilter.startsWith('cust-')) {
            foundAccount = custs.find(x => String(x.id) === cleanFilter);
        } else if (acctFilter.startsWith('supp-')) {
            foundAccount = sups.find(x => String(x.id) === cleanFilter);
        } else if (acctFilter.startsWith('bank-')) {
            foundAccount = banks.find(x => String(x.id) === cleanFilter);
        } else if (acctFilter.startsWith('safe-')) {
            foundAccount = safes.find(x => String(x.id) === cleanFilter);
        } else if (acctFilter.startsWith('exp-')) {
            foundAccount = exp.find(x => String(x.id || x.code) === cleanFilter);
        }
        
        if (!foundAccount) {
            foundAccount = custs.find(x => String(x.id) === cleanFilter) ||
                           sups.find(x => String(x.id) === cleanFilter) ||
                           banks.find(x => String(x.id) === cleanFilter) ||
                           safes.find(x => String(x.id) === cleanFilter) ||
                           exp.find(x => String(x.id || x.code) === cleanFilter);
        }
        
        if (foundAccount) {
            let opDeb = parseNum(foundAccount.openingDebit || 0);
            let opCred = parseNum(foundAccount.openingCredit || 0);
            let opBal = parseNum(foundAccount.openingBalance || foundAccount.opening_balance || 0);
            intrinsicBal = opBal !== 0 ? opBal : (opDeb - opCred);
            accountCurrency = foundAccount.currency || 'SDG';
            accountExRate = parseNum(foundAccount.exRate || 1);
        }
    }

    let runningBal = intrinsicBal;
    let priorTxns = [];
    let currentTxns = [];

    // Separate based on dates first
    if (currentDateMode === 'all') {
        currentTxns = [...txns];
    } else {
        priorTxns = txns.filter(t => t.date < fromDate);
        currentTxns = txns.filter(t => t.date >= fromDate && t.date <= toDate);
    }

    // Apply Posted status filter to both sets to keep opening balances correct
    if (postedFilter && postedFilter !== 'ALL') {
        const wantPosted = (postedFilter === 'posted');
        priorTxns = priorTxns.filter(t => t.isPosted === wantPosted);
        currentTxns = currentTxns.filter(t => t.isPosted === wantPosted);
    }

    // Calculate opening balance after filtering prior transactions
    priorTxns.forEach(t => {
        runningBal += t.debit;
        runningBal -= t.credit;
    });

    if (typeFilter !== 'ALL') currentTxns = currentTxns.filter(t => t.type === typeFilter);
    if (ccFilter !== 'ALL') currentTxns = currentTxns.filter(t => t.costCenter === ccFilter);
    if (curFilter !== 'ALL') currentTxns = currentTxns.filter(t => t.currency === curFilter);
    
    const searchFilter = document.getElementById('filter-search') ? document.getElementById('filter-search').value.trim().toLowerCase() : '';
    if (searchFilter) {
        currentTxns = currentTxns.filter(t => {
            return (t.statement && t.statement.toLowerCase().includes(searchFilter)) ||
                   (t.ref && String(t.ref).toLowerCase().includes(searchFilter)) ||
                   (t.additionalNote && t.additionalNote.toLowerCase().includes(searchFilter));
        });
    }

    currentTxns.sort((a, b) => {
        const da = a.date + ' ' + (a.time || '00:00');
        const db = b.date + ' ' + (b.time || '00:00');
        return da.localeCompare(db);
    });

    if (acctFilter !== 'ALL') {
        let hasOpening = (intrinsicBal !== 0);
        let hasPrior = (currentDateMode !== 'all' && priorTxns.length > 0);
        
        let priorSum = 0;
        priorTxns.forEach(t => {
            priorSum += t.debit;
            priorSum -= t.credit;
        });

        let opDate = '—';
        if (txns.length > 0) {
            opDate = txns.reduce((min, t) => t.date < min ? t.date : min, txns[0].date);
        }

        // 1. Prepend "رصيد افتتاحي" (Opening Balance)
        if (hasOpening || currentDateMode === 'all' || !hasPrior) {
            let opDebit = intrinsicBal > 0 ? intrinsicBal : 0;
            let opCredit = intrinsicBal < 0 ? Math.abs(intrinsicBal) : 0;
            
            currentTxns.unshift({
                date: opDate, time: '', ref: 'OPENING', type: 'opening', typeLabel: 'افتتاحي',
                account: acctFilter, counterAccount: '-',
                statement: 'رصيد افتتاحي',
                additionalNote: 'رصيد افتتاحي',
                debit: opDebit, credit: opCredit, costCenter: '', 
                currency: accountCurrency, exchangeRate: accountExRate,
                isOpening: true
            });
        }
        
        // 2. Prepend "رصيد ما قبل الفترة" if there are prior transactions
        if (hasPrior && priorSum !== 0) {
            let preDebit = priorSum > 0 ? priorSum : 0;
            let preCredit = priorSum < 0 ? Math.abs(priorSum) : 0;
            
            currentTxns.unshift({
                date: fromDate, time: '', ref: 'PRE-PERIOD', type: 'opening', typeLabel: 'افتتاحي',
                account: acctFilter, counterAccount: '-',
                statement: 'رصيد ما قبل الفترة',
                additionalNote: 'رصيد ما قبل الفترة',
                debit: preDebit, credit: preCredit, costCenter: '', 
                currency: accountCurrency, exchangeRate: accountExRate,
                isOpening: true
            });
        }
    }

    renderTable(currentTxns, acctFilter, fromDate, toDate);
}

function renderTable(txns, acctFilter, fromDate, toDate) {
    const tbody = document.getElementById('ledger-tbody');
    const summaryCards = document.getElementById('summary-cards');
    const auditStamp = document.getElementById('audit-stamp');
    const thead = document.querySelector('.ledger-table thead');

    // 1. Build Header Dynamically
    let ths = '';
    let activeColsCount = 0;
    colConfig.forEach(c => {
        if (!c.visible) return;
        activeColsCount++;
        let displayName = c.alias ? c.alias : c.name;
        let thAlign = c.align ? `text-align:${c.align} !important;` : '';
        ths += `<th style="width:${c.width}; ${thAlign}">${displayName}<div class="resize-handle"></div></th>`;
    });
    thead.innerHTML = `<tr>${ths}</tr>`;

    if (txns.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${activeColsCount}" class="empty-state"><span style="font-size:3rem;display:block;margin-bottom:15px;">🔍</span><div>لا توجد بيانات تطابق الفلاتر المحددة</div></td></tr>`;
        summaryCards.style.display = 'none';
        auditStamp.style.display = 'none';
        
        const secActions = document.getElementById('secondary-actions');
        if (secActions) {
            secActions.style.opacity = '0';
            secActions.style.transform = 'translateY(10px)';
            setTimeout(() => secActions.style.display = 'none', 500);
        }
        return;
    }

    let totalDebit = 0, totalCredit = 0, runningBalance = 0;
    let totalDebitForeign = 0, totalCreditForeign = 0, runningBalanceForeign = 0;
    let rows = '';

    txns.forEach((t, i) => {
        const exRate = t.exchangeRate || 1;
        const addNote = t.additionalNote || '-';
        
        let debF = 0;
        let credF = 0;
        if (t.currency && t.currency !== 'SDG' && t.currency !== '-') {
            debF = t.debitForeign || (t.debit / exRate);
            credF = t.creditForeign || (t.credit / exRate);
        } else {
            const rate = ledgerExchangeRate > 0 ? ledgerExchangeRate : (exRate > 1 ? exRate : 1.0);
            debF = t.debit / rate;
            credF = t.credit / rate;
        }

        if (!t.isOpening) {
            totalDebit += t.debit;
            totalCredit += t.credit;
            totalDebitForeign += debF;
            totalCreditForeign += credF;
        }
        runningBalance += t.debit - t.credit;
        runningBalanceForeign += debF - credF;

        const counterAccName = getAccountName(t.counterAccount) || '-';

        let rowHtml = `<tr data-type="${t.type}" data-ref="${t.ref}" data-is-opening="${t.isOpening ? 'true' : 'false'}" title="${t.isOpening ? '' : 'انقر لعرض المستند (Drill-down)'}" ${t.isOpening ? '' : `onclick="openDrillDown('${t.type}', '${t.ref}')"`} style="${t.isOpening ? 'background:rgba(255,255,255,0.03);' : ''}">`;

        // Map values to IDs
        const colValues = {
            'idx': i + 1,
            'date': t.date,
            'account': getAccountName(t.account) || '-',
            'counterAcc': counterAccName,
            'statement': t.statement,
            'note': addNote,
            'costCenter': t.costCenter || '-',
            'debit': t.debit > 0 ? t.debit : null,
            'credit': t.credit > 0 ? t.credit : null,
            'total': (t.debit > 0 ? t.debit : t.credit) || null,
            'balance': runningBalance,
            'currency': t.currency || '-',
            'exRate': exRate,
            'debitF': debF > 0 ? debF : null,
            'creditF': credF > 0 ? credF : null,
            'balanceF': runningBalanceForeign
        };

        colConfig.forEach(c => {
            if (!c.visible) return;
            
            let val = colValues[c.id];
            
            if (['debit', 'credit', 'total', 'balance', 'exRate', 'debitF', 'creditF', 'balanceF'].includes(c.id)) {
                if (val === null) {
                    val = '<span class="td-empty">—</span>';
                } else {
                    let formatted = fmtNumDec(val, c.dec);
                    val = `<div class="num-cell-wrapper ${currentNumFont}" onclick="showContextMenu(event, '${t.type}', '${t.ref}')" oncontextmenu="showContextMenu(event, '${t.type}', '${t.ref}')">${formatted}</div>`;
                }
            }

            let style = [];
            if (c.bg) style.push(`background-color:${c.bg} !important`);
            if (c.color) style.push(`color:${c.color} !important`);
            if (c.bold || t.isOpening) style.push(`font-weight:bold`);
            if (c.size) style.push(`font-size:${c.size}`);
            if (c.align) style.push(`text-align:${c.align} !important`);
            
            if (['statement', 'note'].includes(c.id)) {
                style.push('max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;');
            }
            if (['debit', 'debitF', 'credit', 'creditF'].includes(c.id) && (typeof val === 'string' && val.indexOf('<div') === -1)) style.push('font-family:monospace');

            let extraAttrs = '';
            if (c.id === 'counterAcc') {
                extraAttrs = ` class="counter-acc-cell" data-acc-id="${t.counterAccount || ''}" data-acc-name="${counterAccName}"`;
            }
            rowHtml += `<td${extraAttrs} style="${style.join('; ')}" title="${(typeof val === 'string' && val.includes('<')) ? '' : val}">${val}</td>`;
        });

        rowHtml += `</tr>`;
        rows += rowHtml;
    });

    tbody.innerHTML = rows;

    let activeCurrency = ledgerForeignCurrency;

    document.getElementById('sum-debit').textContent = fmtNum(totalDebit) + ' SDG';
    document.getElementById('sum-debit-foreign').textContent = `(${fmtNum(totalDebitForeign)} ${activeCurrency})`;

    document.getElementById('sum-credit').textContent = fmtNum(totalCredit) + ' SDG';
    document.getElementById('sum-credit-foreign').textContent = `(${fmtNum(totalCreditForeign)} ${activeCurrency})`;

    const balanceSign = runningBalance >= 0 ? ' (مدين)' : ' (دائن)';
    const balanceFSign = runningBalanceForeign >= 0 ? ' (مدين)' : ' (دائن)';
    document.getElementById('sum-balance').textContent = fmtNum(Math.abs(runningBalance)) + ' SDG' + balanceSign;
    document.getElementById('sum-balance-foreign').textContent = `(${fmtNum(Math.abs(runningBalanceForeign))} ${activeCurrency}) ${balanceFSign}`;
    summaryCards.style.display = 'grid';
    auditStamp.style.display = 'flex';

    document.getElementById('print-header').style.display = 'block';
    const accName = acctFilter === 'ALL' ? 'جميع الحسابات المتأثرة' : getAccountName(acctFilter);
    const printedBy = document.getElementById('header-user-name') ? document.getElementById('header-user-name').innerText : 'مدير النظام';
    document.getElementById('print-meta').innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #333; padding-bottom:10px; margin-bottom:5px; font-weight:bold; font-size:1.1rem; width:100%;">
            <div style="flex:1; text-align:right;">الحساب: <strong style="color:var(--text-primary, #000);">${accName}</strong></div>
            <div style="flex:1; text-align:center; font-size:1.4rem;">التقرير العام للحسابات</div>
            <div style="flex:1; text-align:left;">الفترة: <strong>${currentDateMode === 'all' ? 'كل الفترة' : `من ${fromDate} إلى ${toDate}`}</strong></div>
        </div>
        <div class="hide-on-screen" style="justify-content:space-between; font-size:0.85rem; color:var(--text-secondary, #555); margin-bottom:15px; width:100%;">
            <span>طبعت بواسطة: <strong>${printedBy}</strong></span>
            <span>تاريخ الطباعة: <strong>${new Date().toLocaleDateString('ar-SD')}</strong></span>
        </div>
    `;

    showToast(`✅ اكتمل التحليل: ${txns.length} حركة مسجلة`, 'success');
    
    const secActions = document.getElementById('secondary-actions');
    if (secActions) {
        secActions.style.display = 'flex';
        // force reflow for animation
        void secActions.offsetWidth;
        secActions.style.opacity = '1';
        secActions.style.transform = 'translateY(0)';
    }
}

/* ─── Export to CSV ─── */

function previewGL(autoPrint = false) {
    const tableWrap = document.querySelector('.ledger-table-wrap');
    if (!tableWrap) {
        return showToast('الرجاء تشغيل التقرير أولاً للمعاينة', 'warning');
    }
    
    const tbody = document.getElementById('ledger-tbody');
    if (!tbody || tbody.querySelector('.empty-state')) {
        return showToast('لا توجد بيانات للمعاينة، شغّل التقرير أولاً', 'warning');
    }
    
    const tableHtml = document.querySelector('.ledger-table').outerHTML;
    
    // Robust settings retrieval
    let cfg = {};
    try {
        cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
    } catch(e) {}
    
    if (!cfg || Object.keys(cfg).length === 0) {
        try { cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}'); } catch(e) {}
    }

    const companyName = cfg.companyName || 'اسم الشركة غير مسجل (يرجى حفظ الإعدادات)';
    const companyPhone = cfg.phone || '';
    const companyAddress = cfg.address || '';
    const companyReg = cfg.regNumber || '';
    const companyVat = cfg.taxNumber || '';
    
    // Logo: check logoBase64 in settings, or look for the header logo already on page
    let logoSrc = cfg.logoBase64 || '';
    if (!logoSrc) {
        const headerLogo = document.querySelector('.company-logo, #hdr-c-logo');
        if (headerLogo && headerLogo.src && !headerLogo.src.includes('placeholder')) {
            logoSrc = headerLogo.src;
        }
    }
    const logoImg = logoSrc ? `<img src="${logoSrc}" style="max-height:80px; max-width:200px; object-fit:contain;">` : '';

    const companyHeaderHtml = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px;">
            <div style="text-align: right;">
                <h1 style="margin: 0; font-size: 1.5rem;">${companyName}</h1>
                <div style="font-size: 0.9rem; color: #555; margin-top: 5px;">
                    ${companyAddress ? `<div>العنوان: ${companyAddress}</div>` : ''}
                    ${companyPhone ? `<div>الهاتف: ${companyPhone}</div>` : ''}
                    ${companyReg ? `<div>السجل التجاري: ${companyReg}</div>` : ''}
                    ${companyVat ? `<div>الرقم الضريبي: ${companyVat}</div>` : ''}
                </div>
            </div>
            <div>
                ${logoImg}
            </div>
        </div>
    `;

    const win = window.open('', '_blank', 'width=1000,height=700');
    if (!win) {
        showToast('يرجى السماح بالنوافذ المنبثقة (Popups) في متصفحك لمعاينة التقرير', 'error');
        return;
    }
    
    win.document.write(`
        <html dir="rtl" lang="ar">
        <head>
            <title>معاينة الأستاذ العام</title>
            <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Tajawal', sans-serif; padding: 30px; background: #fff; color: #000; direction: rtl; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9rem; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
                th { background: #f3f4f6; color: #333; font-weight: bold; }
                .td-debit { color: #059669; font-weight: bold; }
                .td-credit { color: #dc2626; font-weight: bold; }
                .td-balance-dr { color: #059669; font-weight: bold; }
                .td-balance-cr { color: #dc2626; font-weight: bold; }
                h2, h3 { text-align: center; margin: 5px 0; }
                #print-meta { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; font-weight: bold; }
                @media print { 
                    .no-print { display: none !important; }
                    @page { margin: 0; }
                    body { padding: 1.5cm; }
                }
            </style>
        </head>
        <body>
            ${companyHeaderHtml}
            <div id="print-meta" style="display:flex; flex-direction:column;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #333; padding-bottom:10px; margin-bottom:5px; font-weight:bold; font-size:1.1rem; width:100%;">
                    <div style="flex:1; text-align:right;">الحساب: <strong>${getAccountNameForShare()}</strong></div>
                    <div style="flex:1; text-align:center; font-size:1.5rem;">التقرير العام للحسابات</div>
                    <div style="flex:1; text-align:left;">الفترة: <strong>${currentDateMode === 'all' ? 'كل الفترة' : `من ${document.getElementById('filter-from').value} إلى ${document.getElementById('filter-to').value}`}</strong></div>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:#555; margin-bottom:15px; width:100%;">
                    <span>طبعت بواسطة: <strong>${document.getElementById('header-user-name') ? document.getElementById('header-user-name').innerText : 'مدير النظام'}</strong></span>
                    <span>تاريخ الطباعة: <strong>${new Date().toLocaleDateString('ar-SD')}</strong></span>
                </div>
            </div>
            ${tableHtml}
            <div style="margin-top: 30px; text-align: center;" class="no-print">
                <button onclick="window.print()" style="padding: 10px 20px; background: #38bdf8; border: none; border-radius: 5px; color: #fff; cursor: pointer; font-family: inherit; font-weight: bold; margin: 10px;">🖨️ طباعة</button>
                <button onclick="window.close()" style="padding: 10px 20px; background: #94a3b8; border: none; border-radius: 5px; color: #fff; cursor: pointer; font-family: inherit; font-weight: bold; margin: 10px;">❌ إغلاق</button>
            </div>
        </body>
        </html>
    `);
    win.document.close();

    if (autoPrint) {
        // Wait briefly for styles and fonts to render before printing
        setTimeout(() => {
            win.focus();
            win.print();
            win.close();
        }, 800);
    }
}

function exportCSV() {
    const rows = document.querySelectorAll('#ledger-tbody tr:not(.empty-state)');
    if (rows.length === 0) return showToast('لا توجد بيانات للتصدير', 'warning');

    let csv = '\uFEFF';
    let visibleCols = colConfig.filter(c => c.visible);
    let headers = visibleCols.map(c => `"${c.alias || c.name}"`).join(',');
    csv += headers + '\n';
    
    rows.forEach(r => {
        const cells = Array.from(r.querySelectorAll('td'));
        if (cells.length < visibleCols.length) return;
        let rowCsv = cells.map(cell => `"${cell.innerText.replace(/"/g, '""').replace(/\n/g, ' ')}"`).join(',');
        csv += rowCsv + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `SGL-Report-${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showToast('📥 تم التصدير بنجاح', 'success');
}

function exportGLPDF() {
    const tableWrap = document.querySelector('.ledger-table-wrap');
    if (!tableWrap) return showToast('الرجاء تشغيل التقرير أولاً للتصدير', 'warning');
    
    showToast('جاري تحضير ملف PDF...', 'info');

    // Re-use logic from previewGL to get the HTML
    const tableHtml = document.querySelector('.ledger-table').outerHTML;
    
    let cfg = {};
    try { cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}'); } catch(e) {}
    
    const companyName = cfg.companyName || 'اسم الشركة غير مسجل (يرجى حفظ الإعدادات)';
    const companyPhone = cfg.phone || '';
    const companyAddress = cfg.address || '';
    const companyReg = cfg.regNumber || '';
    const companyVat = cfg.taxNumber || '';
    
    let logoSrc = cfg.logoBase64 || '';
    if (!logoSrc) {
        const headerLogo = document.querySelector('.company-logo, #hdr-c-logo');
        if (headerLogo && headerLogo.src && !headerLogo.src.includes('placeholder')) logoSrc = headerLogo.src;
    }
    const logoImg = logoSrc ? `<img src="${logoSrc}" style="max-height:80px; max-width:200px; object-fit:contain;">` : '';

    const companyHeaderHtml = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px;">
            <div style="text-align: right;">
                <h1 style="margin: 0; font-size: 1.5rem;">${companyName}</h1>
                <div style="font-size: 0.9rem; color: #555; margin-top: 5px;">
                    ${companyAddress ? `<div>العنوان: ${companyAddress}</div>` : ''}
                    ${companyPhone ? `<div>الهاتف: ${companyPhone}</div>` : ''}
                    ${companyReg ? `<div>السجل التجاري: ${companyReg}</div>` : ''}
                    ${companyVat ? `<div>الرقم الضريبي: ${companyVat}</div>` : ''}
                </div>
            </div>
            <div>${logoImg}</div>
        </div>
    `;

    const container = document.createElement('div');
    container.style.padding = '30px';
    container.style.fontFamily = "'Tajawal', sans-serif";
    container.style.direction = 'rtl';
    container.style.background = '#fff';
    container.style.color = '#000';
    container.style.position = 'fixed';
    container.style.top = '0';
    container.style.left = '0';
    container.style.zIndex = '-99999';
    container.style.opacity = '0.01';
    container.style.pointerEvents = 'none';
    container.style.width = '1100px';

    container.innerHTML = `
        ${companyHeaderHtml}
        <div id="print-meta" style="display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #333; padding-bottom:10px; margin-bottom:5px; font-weight:bold; font-size:1.1rem; width:100%;">
                <div style="flex:1; text-align:right;">الحساب: <strong>${getAccountNameForShare()}</strong></div>
                <div style="flex:1; text-align:center; font-size:1.5rem;">التقرير العام للحسابات</div>
                <div style="flex:1; text-align:left;">الفترة: <strong>${currentDateMode === 'all' ? 'كل الفترة' : `من ${document.getElementById('filter-from').value} إلى ${document.getElementById('filter-to').value}`}</strong></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:#555; margin-bottom:15px; width:100%;">
                <span>طبعت بواسطة: <strong>${document.getElementById('header-user-name') ? document.getElementById('header-user-name').innerText : 'مدير النظام'}</strong></span>
                <span>تاريخ الطباعة: <strong>${new Date().toLocaleDateString('ar-SD')}</strong></span>
            </div>
        </div>
        <style>
            table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9rem; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
            th { background: #f3f4f6; color: #333; font-weight: bold; }
            .td-debit { color: #059669; font-weight: bold; }
            .td-credit { color: #dc2626; font-weight: bold; }
            .td-balance-dr { color: #059669; font-weight: bold; }
            .td-balance-cr { color: #dc2626; font-weight: bold; }
        </style>
        ${tableHtml}
    `;

    document.body.appendChild(container);
    
    const opt = {
        margin:       10,
        filename:     'General_Ledger.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };
    
    html2pdf().set(opt).from(container).save().then(() => {
        document.body.removeChild(container);
        showToast('✅ تم تحميل ملف PDF بنجاح', 'success');
    }).catch(err => {
        document.body.removeChild(container);
        showToast('❌ حدث خطأ أثناء التصدير', 'error');
    });
}

function printDirectly() {
    // Use the same robust layout as previewGL but trigger print automatically
    previewGL(true);
}

/* ─── Dynamic Column Configuration & Sidebar ─── */
const defaultColConfig = [
    { id: 'idx', name: '#', alias: '', visible: true, width: '50px', bg: '', color: '#64748b', bold: false, size: '0.8rem', dec: 0, align: 'center' },
    { id: 'date', name: 'التاريخ', alias: '', visible: true, width: 'auto', bg: '', color: '', bold: false, size: '0.85rem', dec: 0, align: 'center' },
    { id: 'account', name: 'الحساب الرئيسي', alias: '', visible: true, width: 'auto', bg: '', color: '#cbd5e1', bold: false, size: '0.85rem', dec: 0, align: 'center' },
    { id: 'counterAcc', name: 'الحساب المقابل', alias: '', visible: true, width: 'auto', bg: '', color: '#e2e8f0', bold: false, size: '0.85rem', dec: 0, align: 'center' },
    { id: 'statement', name: 'حالة السند', alias: '', visible: true, width: '150px', bg: '', color: '#e2e8f0', bold: false, size: '0.85rem', dec: 0, align: 'center' },
    { id: 'note', name: 'ملحوظة إضافية', alias: '', visible: true, width: '150px', bg: '', color: '#94a3b8', bold: false, size: '0.8rem', dec: 0, align: 'center' },
    { id: 'costCenter', name: 'مركز التكلفة', alias: '', visible: true, width: 'auto', bg: '', color: '#94a3b8', bold: false, size: '0.8rem', dec: 0, align: 'center' },
    { id: 'debit', name: 'مدين محلي (عليه)', alias: '', visible: true, width: 'auto', bg: '', color: '#34d399', bold: true, size: '0.9rem', dec: 2, align: 'center' },
    { id: 'credit', name: 'دائن محلي (له)', alias: '', visible: true, width: 'auto', bg: '', color: '#f87171', bold: true, size: '0.9rem', dec: 2, align: 'center' },
    { id: 'balance', name: 'الرصيد محلي', alias: '', visible: true, width: 'auto', bg: '', color: '#c084fc', bold: true, size: '0.9rem', dec: 2, align: 'center' },
    { id: 'currency', name: 'العملة', alias: '', visible: true, width: 'auto', bg: '', color: '#c084fc', bold: false, size: '0.85rem', dec: 0, align: 'center' },
    { id: 'exRate', name: 'سعر الصرف', alias: '', visible: true, width: 'auto', bg: '', color: '#94a3b8', bold: false, size: '0.8rem', dec: 2, align: 'center' },
    { id: 'debitF', name: 'مدين أجنبي (عليه)', alias: '', visible: true, width: 'auto', bg: '', color: '#34d399', bold: true, size: '0.9rem', dec: 2, align: 'center' },
    { id: 'creditF', name: 'دائن أجنبي (له)', alias: '', visible: true, width: 'auto', bg: '', color: '#f87171', bold: true, size: '0.9rem', dec: 2, align: 'center' },
    { id: 'balanceF', name: 'رصيد أجنبي', alias: '', visible: true, width: 'auto', bg: '', color: '#c084fc', bold: true, size: '0.9rem', dec: 2, align: 'center' }
];

let colConfig = [];
function loadColumnConfig() {
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        let loadedConfig = null;
        if (settingsStr) {
            let settings = JSON.parse(settingsStr);
            if (settings.sgl_col_config_v2) {
                loadedConfig = settings.sgl_col_config_v2;
            }
        }
        if (!loadedConfig) {
            const oldSaved = localStorage.getItem('sgl_col_config_v2');
            if (oldSaved) {
                loadedConfig = JSON.parse(oldSaved);
            }
        }

        if (loadedConfig) {
            const merged = [];
            defaultColConfig.forEach(defCol => {
                const found = loadedConfig.find(c => c.id === defCol.id);
                if (found) {
                    found.name = defCol.name; // Keep name aligned with the new default name
                    if (!found.align) found.align = defCol.align || 'center'; // Set default alignment
                    merged.push(found);
                } else {
                    merged.push(defCol);
                }
            });
            colConfig = merged;
            return;
        }
        
        colConfig = JSON.parse(JSON.stringify(defaultColConfig));
    } catch(e) { colConfig = JSON.parse(JSON.stringify(defaultColConfig)); }
}
loadColumnConfig();

// Table Theme Management
let currentTableTheme = '';
function loadTableTheme() {
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        if (settingsStr) {
            let settings = JSON.parse(settingsStr);
            if (settings.sgl_table_theme !== undefined) {
                currentTableTheme = settings.sgl_table_theme;
                applyTableTheme(currentTableTheme);
                updateCrystalButtons(currentTableTheme);
            }
        }
    } catch(e) {}
}

function updateCrystalButtons(themeClass) {
    const btns = document.querySelectorAll('.crystal-theme-container .crystal-btn');
    if (btns.length > 0) {
        btns.forEach(b => b.classList.remove('active'));
        let activeBtn = document.getElementById('btn-theme-' + (themeClass || ''));
        if (activeBtn) activeBtn.classList.add('active');
    }
}

function applyTableTheme(themeClass) {
    const table = document.querySelector('.ledger-table');
    if (!table) return;
    // Remove all existing theme classes
    table.classList.forEach(cls => {
        if (cls.startsWith('table-theme-')) {
            table.classList.remove(cls);
        }
    });
    // Add new theme
    if (themeClass) {
        table.classList.add(themeClass);
    }
}

window.changeTableTheme = function(themeClass) {
    currentTableTheme = themeClass;
    applyTableTheme(themeClass);
    updateCrystalButtons(themeClass);
};

let currentNumFont = 'num-font-roboto';

function loadNumberFont() {
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        if (settingsStr) {
            let settings = JSON.parse(settingsStr);
            if (settings.sgl_num_font) {
                currentNumFont = settings.sgl_num_font;
            }
        }
    } catch(e) {}
    
    document.querySelectorAll('.font-chip').forEach(el => el.classList.remove('active'));
    let btn = document.getElementById('btn-font-' + currentNumFont);
    if (btn) btn.classList.add('active');

    document.querySelectorAll('.num-cell-wrapper').forEach(el => {
        el.classList.remove('num-font-roboto', 'num-font-inter', 'num-font-jetbrains', 'num-font-poppins');
        el.classList.add(currentNumFont);
    });
}

window.changeNumberFont = function(fontClass) {
    currentNumFont = fontClass;
    document.querySelectorAll('.num-cell-wrapper').forEach(el => {
        el.classList.remove('num-font-roboto', 'num-font-inter', 'num-font-jetbrains', 'num-font-poppins');
        el.classList.add(fontClass);
    });
    
    document.querySelectorAll('.font-chip').forEach(el => el.classList.remove('active'));
    let btn = document.getElementById('btn-font-' + fontClass);
    if (btn) btn.classList.add('active');
};

window.toggleReviewMode = function(e) {
    e.stopPropagation();
    e.currentTarget.classList.toggle('reviewed-cell');
};

window.showContextMenu = function(e, type, ref) {
    if (type === 'opening') return; // no drill down for opening balances
    e.preventDefault();
    e.stopPropagation();
    
    if (window.openLedgerContextMenu) {
        window.openLedgerContextMenu(e, type, ref, e.currentTarget);
    }
};

function closeContextMenu() {
    const menu = document.getElementById('drilldown-context-menu');
    if (menu) menu.classList.remove('show');
    document.removeEventListener('click', closeContextMenu);
}

function initializeLedgerSettings() {
    loadColumnConfig();
    applyConfigToHeaders();
    loadTableTheme();
    loadNumberFont();
}

if (window.APP_READY) {
    initializeLedgerSettings();
} else {
    document.addEventListener('DBReady', initializeLedgerSettings);
}

function saveColumnConfig() {
    if (!window.APP_READY) return;
    try {
        let settingsStr = localStorage.getItem('erp_settings_v1');
        let settings = settingsStr ? JSON.parse(settingsStr) : {};
        settings.sgl_col_config_v2 = colConfig;
        
        // By setting it in erp_settings_v1, app.js will automatically push it to the SaaS DB!
        localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
    } catch(e) {
        console.error("Failed to save col config to DB", e);
    }
}

function toggleSettingsSidebar() {
    const sb = document.getElementById('settings-sidebar');
    const ov = document.getElementById('settings-overlay');
    if (sb.classList.contains('active')) {
        if (window.previewBackupConfigGL) {
            colConfig = JSON.parse(JSON.stringify(window.previewBackupConfigGL));
            applyConfigToHeaders();
            runReport();
            renderSidebarConfig();
        }
        if (window.previewBackupThemeGL !== undefined) {
            currentTableTheme = window.previewBackupThemeGL;
            applyTableTheme(currentTableTheme);
            updateCrystalButtons(currentTableTheme);
        }
        if (window.previewBackupFontGL !== undefined) {
            currentNumFont = window.previewBackupFontGL;
            document.querySelectorAll('.num-cell-wrapper').forEach(el => {
                el.classList.remove('num-font-roboto', 'num-font-inter', 'num-font-jetbrains', 'num-font-poppins');
                el.classList.add(currentNumFont);
            });
            document.querySelectorAll('.font-chip').forEach(el => el.classList.remove('active'));
            let btn = document.getElementById('btn-font-' + currentNumFont);
            if (btn) btn.classList.add('active');
        }
        sb.classList.remove('active');
        ov.classList.remove('active');
    } else {
        window.previewBackupConfigGL = JSON.parse(JSON.stringify(colConfig));
        window.previewBackupThemeGL = currentTableTheme;
        window.previewBackupFontGL = currentNumFont;
        // Load position and size if exists
        try {
            let settingsStr = localStorage.getItem('erp_settings_v1');
            if (settingsStr) {
                let settings = JSON.parse(settingsStr);
                if (settings.sgl_sidebar_ui) {
                    if (settings.sgl_sidebar_ui.left) sb.style.left = settings.sgl_sidebar_ui.left;
                    if (settings.sgl_sidebar_ui.top) sb.style.top = settings.sgl_sidebar_ui.top;
                    if (settings.sgl_sidebar_ui.width) sb.style.width = settings.sgl_sidebar_ui.width;
                    if (settings.sgl_sidebar_ui.height) sb.style.height = settings.sgl_sidebar_ui.height;
                }
            }
        } catch(e) {}
        
        renderSidebarConfig();
        if (typeof renderPresets === 'function') {
            renderPresets();
        }
        sb.classList.add('active');
        ov.classList.add('active');
    }
}

function renderSidebarConfig() {
    const list = document.getElementById('columns-config-list');
    list.innerHTML = '';
    colConfig.forEach((col, index) => {
        const li = document.createElement('li');
        li.className = 'col-config-item';
        li.draggable = true;
        li.dataset.index = index;
        
        li.innerHTML = `
            <div class="col-config-header">
                <div class="col-title-wrap">
                    <span class="col-drag-handle">☰</span>
                    <span class="col-title">${col.name}</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button class="col-settings-btn" onclick="toggleColSettings(${index})">تخصيص</button>
                    <div class="col-toggle ${col.visible ? 'on' : ''}" onclick="toggleColVisibility(${index}, this)"></div>
                </div>
            </div>
            <div class="col-settings-panel" id="col-settings-${index}">
                <div class="cfg-row">
                    <span>مسمى بديل:</span>
                    <input type="text" class="cfg-input" value="${col.alias}" onchange="updateColField(${index}, 'alias', this.value)" placeholder="مثلاً: ${col.name}">
                </div>
                <div class="cfg-row">
                    <span>الخط (حجم/سمك):</span>
                    <div class="cfg-btns">
                        <button class="cfg-btn" onclick="adjustFontSize(${index}, -1)">A-</button>
                        <button class="cfg-btn" onclick="adjustFontSize(${index}, 1)">A+</button>
                        <button class="cfg-btn ${col.bold ? 'active' : ''}" onclick="toggleColBold(${index}, this)">B</button>
                    </div>
                </div>
                <div class="cfg-row">
                    <span>محاذاة النص:</span>
                    <select class="cfg-input" onchange="updateColField(${index}, 'align', this.value)" style="width:120px;">
                        <option value="center" ${col.align === 'center' ? 'selected' : ''}>الوسط ↔️</option>
                        <option value="right" ${col.align === 'right' ? 'selected' : ''}>اليمين ➡️</option>
                        <option value="left" ${col.align === 'left' ? 'selected' : ''}>اليسار ⬅️</option>
                    </select>
                </div>
                <div class="cfg-row">
                    <span>الألوان (نص/خلفية):</span>
                    <div style="display:flex; gap:5px;">
                        <input type="color" class="cfg-input" value="${col.color || '#ffffff'}" onchange="updateColField(${index}, 'color', this.value)" title="لون النص">
                        <input type="color" class="cfg-input" value="${col.bg || '#000000'}" onchange="updateColField(${index}, 'bg', this.value)" title="لون الخلفية">
                    </div>
                </div>
                ${['debit', 'credit', 'total', 'balance', 'exRate', 'debitF', 'creditF', 'balanceF'].includes(col.id) ? `
                <div class="cfg-row">
                    <span>التقريب (العشري):</span>
                    <select class="cfg-input" onchange="updateColField(${index}, 'dec', parseInt(this.value))" style="width:120px;">
                        <option value="0" ${col.dec === 0 ? 'selected' : ''}>بدون خانات (0)</option>
                        <option value="1" ${col.dec === 1 ? 'selected' : ''}>خانة واحدة (1)</option>
                        <option value="2" ${col.dec === 2 ? 'selected' : ''}>خانتين (2)</option>
                        <option value="3" ${col.dec === 3 ? 'selected' : ''}>3 خانات (3)</option>
                        <option value="4" ${col.dec === 4 ? 'selected' : ''}>4 خانات (4)</option>
                        <option value="5" ${col.dec === 5 ? 'selected' : ''}>5 خانات (5)</option>
                        <option value="6" ${col.dec === 6 ? 'selected' : ''}>6 خانات (6)</option>
                    </select>
                </div>
                ` : ''}
            </div>
        `;
        
        li.addEventListener('dragstart', handleDragStart);
        li.addEventListener('dragover', handleDragOver);
        li.addEventListener('drop', handleDrop);
        li.addEventListener('dragenter', handleDragEnter);
        li.addEventListener('dragleave', handleDragLeave);
        
        list.appendChild(li);
    });
}

function previewColSettingsGL() {
    applyConfigToHeaders();
    runReport();
}

function toggleColSettings(index) {
    const p = document.getElementById('col-settings-' + index);
    p.classList.toggle('open');
}
function toggleColVisibility(index, el) {
    colConfig[index].visible = !colConfig[index].visible;
    el.classList.toggle('on');
    previewColSettingsGL();
}
function updateColField(index, field, val) {
    colConfig[index][field] = val;
    previewColSettingsGL();
}
function adjustFontSize(index, dir) {
    let size = parseFloat(colConfig[index].size) || 0.9;
    size += dir * 0.05;
    colConfig[index].size = size + 'rem';
    previewColSettingsGL();
}
function toggleColBold(index, el) {
    colConfig[index].bold = !colConfig[index].bold;
    el.classList.toggle('active');
    previewColSettingsGL();
}

let dragSrcEl = null;
function handleDragStart(e) {
    dragSrcEl = this;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
    this.classList.add('dragging');
}
function handleDragOver(e) {
    if (e.preventDefault) e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}
function handleDragEnter(e) { this.classList.add('over'); }
function handleDragLeave(e) { this.classList.remove('over'); }
function handleDrop(e) {
    if (e.stopPropagation) e.stopPropagation();
    if (dragSrcEl !== this) {
        let fromIdx = parseInt(dragSrcEl.dataset.index);
        let toIdx = parseInt(this.dataset.index);
        
        let moved = colConfig.splice(fromIdx, 1)[0];
        colConfig.splice(toIdx, 0, moved);
        
        renderSidebarConfig();
        previewColSettingsGL();
    }
    return false;
}

function applyAndSaveConfig() {
    saveColumnConfig();
    try {
        if (window.APP_READY) {
            let settingsStr = localStorage.getItem('erp_settings_v1');
            let settings = settingsStr ? JSON.parse(settingsStr) : {};
            settings.sgl_table_theme = currentTableTheme;
            settings.sgl_num_font = currentNumFont;
            localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
        }
    } catch(e) {}
    window.previewBackupConfigGL = JSON.parse(JSON.stringify(colConfig));
    window.previewBackupThemeGL = currentTableTheme;
    window.previewBackupFontGL = currentNumFont;
    runReport();
    toggleSettingsSidebar();
    showToast('💾 تم حفظ التخصيصات وتطبيقها بنجاح', 'success');
}
function resetColumnConfig() {
    colConfig = JSON.parse(JSON.stringify(defaultColConfig));
    saveColumnConfig();
    window.previewBackupConfigGL = JSON.parse(JSON.stringify(colConfig));
    renderSidebarConfig();
    runReport();
    showToast('🔄 تم استعادة الوضع الافتراضي', 'success');
}
function fmtNumDec(n, decimals) {
    const val = parseFloat(n) || 0;
    if (val < 0) {
        return `-(${Math.abs(val).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })})`;
    }
    return val.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

function applyConfigToHeaders() {
    const thead = document.querySelector('#ledger-thead');
    if (!thead) return;
    let ths = '';
    colConfig.forEach(c => {
        if (!c.visible) return;
        let displayName = c.alias ? c.alias : c.name;
        let thAlign = c.align ? `text-align:${c.align} !important;` : '';
        ths += `<th style="width:${c.width}; ${thAlign}">${displayName}<div class="resize-handle"></div></th>`;
    });
    thead.innerHTML = `<tr>${ths}</tr>`;
}

/* ─── Column Resizing Logic ─── */
let isResizing = false;
let currentTh = null;
let startX = 0;
let startWidth = 0;
let isLeftHandle = true;
let resizeColIndex = -1;

document.addEventListener('mousedown', function(e) {
    if (e.target.classList.contains('resize-handle-left') || e.target.classList.contains('resize-handle-right') || e.target.classList.contains('resize-handle')) {
        isResizing = true;
        currentTh = e.target.parentElement;
        startX = e.pageX;
        startWidth = currentTh.offsetWidth;
        isLeftHandle = e.target.classList.contains('resize-handle-left') || e.target.classList.contains('resize-handle');
        
        let thName = currentTh.textContent.trim();
        resizeColIndex = colConfig.findIndex(c => c.visible && (c.alias || c.name) === thName);
        e.preventDefault(); 
    }
});

document.addEventListener('mousemove', function(e) {
    if (!isResizing) return;
    let diff = 0;
    if (isLeftHandle) {
        diff = startX - e.pageX; // Drag left increases width
    } else {
        diff = e.pageX - startX; // Drag right increases width
    }
    let newWidth = startWidth + diff;
    if (newWidth > 30) {
        currentTh.style.width = newWidth + 'px';
    }
});

document.addEventListener('mouseup', function(e) {
    if (isResizing) {
        isResizing = false;
        if (resizeColIndex >= 0 && resizeColIndex < colConfig.length) {
            colConfig[resizeColIndex].width = currentTh.style.width;
            saveColumnConfig();
        }
        currentTh = null;
    }
});

/* ─── Sleek & Tactile Quick Presets (Hold to Save, Click to Apply) ─── */
const HOLD_TIME_MS = 1500; // Hold for 1.5s to trigger save

window.getQuickPresets = function() {
    let list = [];
    try {
        list = JSON.parse(localStorage.getItem('sgl_quick_presets_v3'));
    } catch(e) {}
    if (!list || list.length !== 4) {
        list = [
            { id: 1, name: 'النمط 1', active: false, colConfig: null },
            { id: 2, name: 'النمط 2', active: false, colConfig: null },
            { id: 3, name: 'النمط 3', active: false, colConfig: null },
            { id: 4, name: 'النمط 4', active: false, colConfig: null }
        ];
        localStorage.setItem('sgl_quick_presets_v3', JSON.stringify(list));
    }
    return list;
}

window.renderQuickPresets = function() {
    const wrap = document.getElementById('presets-slots-wrap');
    if (!wrap) return;
    
    const list = window.getQuickPresets();
    wrap.innerHTML = '';
    
    list.forEach((p) => {
        const btn = document.createElement('div');
        btn.className = `preset-slot-btn ${p.colConfig ? 'has-data' : ''}`;
        btn.setAttribute('data-id', p.id);
        
        btn.title = p.colConfig ? 'نقرة واحدة للتطبيق. اضغط مطولاً (ثانية ونصف) لحفظ التخطيط الحالي هنا. انقر بالزر الأيمن للحذف.' : 'اضغط مطولاً (ثانية ونصف) لحفظ التخطيط الحالي في هذا الموضع.';
        
        btn.innerHTML = `
            <span>${p.name}</span>
            <div class="hold-bar" id="hold-bar-${p.id}"></div>
        `;
        
        setupLongPress(btn, p);
        wrap.appendChild(btn);
    });
}

function setupLongPress(element, preset) {
    let pressTimer = null;
    let progressTimer = null;
    let holdStartTime = 0;
    let isHolding = false;
    const bar = element.querySelector('.hold-bar');

    function startPress(e) {
        if (e.type === 'touchstart') e.preventDefault();
        
        isHolding = true;
        holdStartTime = Date.now();
        bar.style.transition = 'none';
        bar.style.width = '0%';
        
        let progress = 0;
        progressTimer = setInterval(() => {
            const elapsed = Date.now() - holdStartTime;
            const pct = Math.min(100, (elapsed / HOLD_TIME_MS) * 100);
            bar.style.width = pct + '%';
            if (pct >= 100) {
                clearInterval(progressTimer);
            }
        }, 30);

        pressTimer = setTimeout(() => {
            triggerSave(preset);
            cancelPress();
        }, HOLD_TIME_MS);
    }

    function cancelPress() {
        isHolding = false;
        clearTimeout(pressTimer);
        clearInterval(progressTimer);
        bar.style.transition = 'width 0.2s ease-out';
        bar.style.width = '0%';
    }

    function endPress(e) {
        if (!isHolding) return;
        const elapsed = Date.now() - holdStartTime;
        cancelPress();
        
        if (elapsed < HOLD_TIME_MS) {
            triggerApply(preset);
        }
    }

    element.addEventListener('mousedown', startPress);
    element.addEventListener('mouseup', endPress);
    element.addEventListener('mouseleave', cancelPress);

    element.addEventListener('touchstart', startPress, { passive: false });
    element.addEventListener('touchend', endPress);
    element.addEventListener('touchcancel', cancelPress);

    // Right click clears preset
    element.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        if (!preset.colConfig) return;
        
        if (confirm(`هل تريد تصفير وحذف قالب النمط "${preset.name}"؟`)) {
            clearPresetSlot(preset.id);
        }
    });
}

function triggerSave(preset) {
    const oldModal = document.getElementById('save-preset-modal-wrap');
    if (oldModal) oldModal.remove();

    const overlay = document.createElement('div');
    overlay.id = 'save-preset-modal-wrap';
    overlay.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.85); z-index: 9999999;
        display: flex; align-items: center; justify-content: center;
        backdrop-filter: blur(12px);
        opacity: 0; transition: opacity 0.3s ease;
    `;
    
    overlay.innerHTML = `
        <div style="background: linear-gradient(145deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95)); border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 20px; padding: 25px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.5); transform: scale(0.9); transition: all 0.3s ease;">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">💾</div>
            <h3 style="color:#fff; font-family:'Tajawal', sans-serif; font-size:1.2rem; font-weight:800; margin-bottom:10px;">حفظ النمط المخصص</h3>
            <p style="color:#94a3b8; font-size:0.85rem; margin-bottom:20px; font-family:'Tajawal', sans-serif;">يرجى كتابة اسم مناسب لهذا النمط لحفظ التعديلات الحالية عليه:</p>
            <input type="text" id="preset-custom-name-input" value="${preset.colConfig ? preset.name : 'نمط مخصص'}" style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:12px; border-radius:10px; font-size:1rem; outline:none; text-align:center; font-family:inherit; font-weight:bold; margin-bottom:20px;" onfocus="this.style.borderColor='#a855f7'">
            <div style="display:flex; gap:10px; justify-content:center;">
                <button id="btn-save-preset-confirm" class="btn-run" style="padding:10px 25px; border-radius:10px; font-weight:bold; background: linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; cursor:pointer;">حفظ وتطبيق</button>
                <button id="btn-save-preset-cancel" class="btn-action" style="padding:10px 20px; border-radius:10px; cursor:pointer;">إلغاء</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);
    
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        overlay.children[0].style.transform = 'scale(1)';
    });

    const nameInput = document.getElementById('preset-custom-name-input');
    nameInput.focus();
    nameInput.select();

    const closeModal = () => {
        overlay.style.opacity = '0';
        overlay.children[0].style.transform = 'scale(0.9)';
        setTimeout(() => overlay.remove(), 300);
    };

    document.getElementById('btn-save-preset-cancel').onclick = closeModal;
    
    document.getElementById('btn-save-preset-confirm').onclick = () => {
        const customName = nameInput.value.trim() || `النمط ${preset.id}`;
        saveCurrentConfigToPreset(preset.id, customName);
        closeModal();
    };

    nameInput.onkeyup = (e) => {
        if (e.key === 'Enter') {
            const customName = nameInput.value.trim() || `النمط ${preset.id}`;
            saveCurrentConfigToPreset(preset.id, customName);
            closeModal();
        }
    };
}

function saveCurrentConfigToPreset(presetId, name) {
    const list = window.getQuickPresets();
    const idx = list.findIndex(p => p.id === presetId);
    if (idx === -1) return;
    
    const savedConfig = JSON.parse(JSON.stringify(colConfig));
    
    list[idx] = {
        id: presetId,
        name: name,
        active: true,
        colConfig: savedConfig
    };
    
    localStorage.setItem('sgl_quick_presets_v3', JSON.stringify(list));
    window.renderQuickPresets();
    showToast(`💾 تم حفظ التخطيط باسم: ${name}`, 'success');
}

function triggerApply(preset) {
    if (!preset.colConfig) {
        showToast('⚠️ هذا موضع نمط فارغ! اضغط مطولاً لحفظ التنسيق الحالي هنا.', 'warning');
        return;
    }
    
    colConfig = JSON.parse(JSON.stringify(preset.colConfig));
    saveColumnConfig();
    renderSidebarConfig();
    runReport();
    
    showToast(`✨ تم تطبيق نمط العرض: ${preset.name}`, 'success');
}

function clearPresetSlot(presetId) {
    const list = window.getQuickPresets();
    const idx = list.findIndex(p => p.id === presetId);
    if (idx === -1) return;
    
    list[idx] = {
        id: presetId,
        name: `النمط ${presetId}`,
        active: false,
        colConfig: null
    };
    
    localStorage.setItem('sgl_quick_presets_v3', JSON.stringify(list));
    window.renderQuickPresets();
    showToast(`🔄 تم حذف وتصفير موضع النمط ${presetId}`, 'success');
}


