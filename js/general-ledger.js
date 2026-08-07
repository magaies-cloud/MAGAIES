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

var ALL_ACCOUNTS = [{ id: 'ALL', name: 'جميع الحسابات', icon: '📊', type: 'عام' }];
let ALL_TXNS = []; // Cache to extract cost centers and currencies
var ACCOUNT_NAME_CACHE = new Map(); // O(1) lookup cache for getAccountName
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

    if (typeof window.getExchangeRateForCurrency === 'function') {
        const r = window.getExchangeRateForCurrency('USD');
        if (r && r > 0) ledgerExchangeRate = r;
    }

    fetch('api_index.php/exchange-rate')
        .then(res => res.json())
        .then(data => {
            if (data && data.rate) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.exchangeMode !== 'manual') {
                        ledgerExchangeRate = parseFloat(data.rate) || 1.0;
                    }
                } catch(e) {
                    ledgerExchangeRate = parseFloat(data.rate) || 1.0;
                }
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
    
    if (typeof ALL_TXNS !== 'undefined' && ALL_TXNS.length > 0) {
        runReport();
    }
};

function buildAccountsList() {
    ALL_ACCOUNTS = [{ id: 'ALL', name: 'جميع الحسابات', icon: '📊', type: 'عام' }];
    
    try {
        const accounts = (JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => !x.deleted);
        accounts.forEach(a => ALL_ACCOUNTS.push({ id: `exp-${a.id}`, name: a.name, icon: '📝', type: 'عام' }));
    } catch(e) { console.warn('Error loading accounts:', e); }
    
    try {
        const safes = (JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => !x.deleted);
        safes.forEach(s => ALL_ACCOUNTS.push({ id: `safe-${s.id}`, name: s.safeName || s.name, icon: '💰', type: 'صندوق' }));
    } catch(e) { console.warn('Error loading safes:', e); }
    
    try {
        const banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => !x.deleted);
        banks.forEach(b => ALL_ACCOUNTS.push({ id: `bank-${b.id}`, name: b.bankName || b.name, icon: '🏦', type: 'بنك' }));
    } catch(e) { console.warn('Error loading banks:', e); }
    
    try {
        const custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
        custs.forEach(c => ALL_ACCOUNTS.push({ id: `cust-${c.id}`, name: c.name, icon: '👤', type: 'عميل' }));
    } catch(e) { console.warn('Error loading customers:', e); }

    try {
        const sups  = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
        sups.forEach(s  => ALL_ACCOUNTS.push({ id: `supp-${s.id}`, name: s.name, icon: '🏭', type: 'مورد' }));
    } catch(e) { console.warn('Error loading suppliers:', e); }

    // Build O(1) lookup cache
    ACCOUNT_NAME_CACHE = new Map();
    ALL_ACCOUNTS.forEach(a => {
        const idLower = String(a.id).toLowerCase();
        ACCOUNT_NAME_CACHE.set(idLower, a.name);
        const nameLower = String(a.name).toLowerCase();
        if (nameLower && !ACCOUNT_NAME_CACHE.has(nameLower)) {
            ACCOUNT_NAME_CACHE.set(nameLower, a.name);
        }
        // Also cache without prefix for fallback lookups
        const cleanId = idLower.replace(/^[a-zA-Z]+\s*[-_:\/]\s*/, '').trim();
        if (cleanId && !ACCOUNT_NAME_CACHE.has(cleanId)) {
            ACCOUNT_NAME_CACHE.set(cleanId, a.name);
        }
    });

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
    if (typeof window.renderLedgerPresets === 'function') window.renderLedgerPresets();
    if (typeof window.renderLedgerPresets === 'function') window.renderLedgerPresets();
    if (typeof window.renderLedgerPresets === 'function') window.renderLedgerPresets();
    const input = document.getElementById('account-search-input');
    if (input) {
        input.addEventListener('focus', onAccountSearch);
        input.addEventListener('input', onAccountSearch);
    }
});

/* ─── Collect Transactions & Meta (Currency, Cost Center) ─── */
function collectAllTransactions() {
    let txns = [];
    const timeNow = new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}); // Fallback time

    // Helper: resolve the effective exchange rate for a transaction
    // Priority: 1) systemExchangeRate saved at creation, 2) rate if foreign, 3) current ledgerExchangeRate
    function getEffectiveRate(item, txnRate) {
        const saved = parseNum(item.systemExchangeRate || 0);
        if (saved > 1) return saved;
        if (txnRate && txnRate > 1) return txnRate;
        return (typeof ledgerExchangeRate !== "undefined" && ledgerExchangeRate > 0 ? ledgerExchangeRate : 1);
    }

    // 1. Single Receipts
    const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    receipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        
        const rate = parseNum(v.rate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');
        const amount = parseNum(v.amount !== undefined ? v.amount : (isBaseSDG ? v.amountLocal : v.amountForeign));
        const amountLocal = parseNum(v.amountLocal || (amount * rate));
        const valLoc = amountLocal;
        const valFc = isBaseSDG ? (amountLocal / effRate) : amount;

        // Receiving side (Safe/Bank)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'receipt', typeLabel: 'سند قبض',
            account: v.toAccount || 'safe-001', counterAccount: v.fromAccount,
            statement: 'سند قبض #' + (v.voucherNo || v.id || 'بدون_رقم'),
            additionalNote: v.fromStatement || 'سند قبض نقدي',
            debit: valLoc, credit: 0, 
            debitForeign: valFc, creditForeign: 0,
            costCenter, currency,
            exchangeRate: effRate,
            isPosted: isPosted
        });
        // Disbursing side (Customer/Supplier pays us)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'receipt', typeLabel: 'سند صرف',
            account: v.fromAccount, counterAccount: v.toAccount || 'safe-001',
            statement: 'سند قبض #' + (v.voucherNo || v.id || 'بدون_رقم'),
            additionalNote: v.fromStatement || 'سند صرف نقدي',
            debit: 0, credit: valLoc, 
            debitForeign: 0, creditForeign: valFc,
            costCenter, currency,
            exchangeRate: effRate,
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
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');
        const amount = parseNum(v.amount !== undefined ? v.amount : (isBaseSDG ? v.amountLocal : v.amountForeign));
        const amountLocal = parseNum(v.amountLocal || (amount * rate));
        const valLoc = amountLocal;
        const valFc = isBaseSDG ? (amountLocal / effRate) : amount;

        const method = v.paymentMethod || 'cash';
        if (method === 'mobile' && v.bankTransfers && v.bankTransfers.length > 0) {
            v.bankTransfers.forEach(bt => {
                const amt = parseNum(bt.amount);
                const amtLoc = isBaseSDG ? amt : amt * rate;
                const amtFc = isBaseSDG ? (amt / effRate) : amt;
                const stmt = bt.statement || v.statement || v.toStatement || ('تحويل بنكي - سند صرف #' + (v.voucherNo || v.id || 'بدون_رقم'));

                // Disbursing side (Safe/Bank)
                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
                    account: v.fromAccount || 'safe-001', counterAccount: v.toAccount,
                    statement: stmt,
                    additionalNote: stmt,
                    debit: 0, credit: amtLoc, 
                    debitForeign: 0, creditForeign: amtFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                // Receiving side (Supplier/Customer receives from us)
                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند استلام',
                    account: v.toAccount, counterAccount: v.fromAccount || 'safe-001',
                    statement: stmt,
                    additionalNote: stmt,
                    debit: amtLoc, credit: 0, 
                    debitForeign: amtFc, creditForeign: 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            });
        } else if (method === 'check' && v.cheques && v.cheques.length > 0) {
            v.cheques.forEach(ch => {
                const amt = parseNum(ch.amount);
                const amtLoc = isBaseSDG ? amt : amt * rate;
                const amtFc = isBaseSDG ? (amt / effRate) : amt;
                const stmt = ch.statement || v.statement || v.toStatement || ('شيك - سند صرف #' + (v.voucherNo || v.id || 'بدون_رقم'));

                // Disbursing side (Safe/Bank)
                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
                    account: v.fromAccount || 'safe-001', counterAccount: v.toAccount,
                    statement: stmt,
                    additionalNote: stmt,
                    debit: 0, credit: amtLoc, 
                    debitForeign: 0, creditForeign: amtFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                // Receiving side (Supplier/Customer receives from us)
                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند استلام',
                    account: v.toAccount, counterAccount: v.fromAccount || 'safe-001',
                    statement: stmt,
                    additionalNote: stmt,
                    debit: amtLoc, credit: 0, 
                    debitForeign: amtFc, creditForeign: 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            });
        } else {
            // Disbursing side (Safe/Bank)
            txns.push({
                date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
                account: v.fromAccount || 'safe-001', counterAccount: v.toAccount,
                statement: v.statement || 'سند صرف #' + (v.voucherNo || v.id || 'بدون_رقم'),
                additionalNote: v.toStatement || 'سند صرف نقدي',
                debit: 0, credit: valLoc, 
                debitForeign: 0, creditForeign: valFc,
                costCenter, currency,
                exchangeRate: effRate,
                isPosted: isPosted
            });
            // Receiving side (Supplier/Customer receives from us)
            txns.push({
                date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند استلام',
                account: v.toAccount, counterAccount: v.fromAccount || 'safe-001',
                statement: v.statement || 'سند صرف #' + (v.voucherNo || v.id || 'بدون_رقم'),
                additionalNote: v.toStatement || 'سند استلام نقدي',
                debit: valLoc, credit: 0, 
                debitForeign: valFc, creditForeign: 0,
                costCenter, currency,
                exchangeRate: effRate,
                isPosted: isPosted
            });
        }
    });

    // Helper to resolve safe/bank account name/id from ACCOUNTS list
    const resolveAccount = (accVal, fallback) => {
        if (!accVal) return fallback;
        if (/^(safe|bank|cust|supp|gl)-/.test(accVal)) return accVal;
        if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
            const found = ACCOUNTS.find(a => a.name === accVal || a.id === accVal);
            if (found) return found.id;
        }
        return accVal;
    };

    // 2b. Multi-Receipts (erp_receipts_v1)
    const multiReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
    multiReceipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        const mainAcc = v.toAccount || 'safe-001';

        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                const pms = item.paymentMethods || [item.paymentMethod || 'cash'];
                
                // Cash Portion
                if (pms.includes('cash') && parseNum(item.cashAmount) > 0) {
                    const amt = parseNum(item.cashAmount);
                    const amtLoc = isBaseSDG ? amt : amt * rate;
                    const amtFc = isBaseSDG ? (amt / effRate) : amt;
                    const safeAcct = resolveAccount(item.cashSafe, mainAcc);
                    const stmt = item.cashStatement || item.statement || v.description || 'قبض نقدية';

                    // Safe Account (Debit)
                    txns.push({
                        date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                        account: safeAcct, counterAccount: item.account,
                        statement: v.description || stmt,
                        additionalNote: v.description || stmt,
                        debit: amtLoc, credit: 0, 
                        debitForeign: amtFc, creditForeign: 0,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    
                    // Recipient/Payer Account (Credit)
                    txns.push({
                        date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                        account: item.account, counterAccount: safeAcct,
                        statement: stmt,
                        additionalNote: stmt,
                        debit: 0, credit: amtLoc, 
                        debitForeign: 0, creditForeign: amtFc,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                }

                // Bank Transfer Portion
                if (pms.includes('bank') && parseNum(item.bankAmount) > 0) {
                    const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: item.bankAmount, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                    transfers.forEach(bt => {
                        const amt = parseNum(bt.amount);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * rate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const bankAcct = resolveAccount(bt.bankName, mainAcc);
                        const stmt = bt.statement || item.statement || v.description || 'قبض تحويل بنكي';

                        // Bank Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: bankAcct, counterAccount: item.account,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Recipient/Payer Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: item.account, counterAccount: bankAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }

                // Cheque Portion
                if (pms.includes('cheque') && parseNum(item.chequeAmount) > 0) {
                    const cheques = (item.cheques && item.cheques.length > 0) ? item.cheques : [{ amount: item.chequeAmount, chequeBank: item.chequeBank, chequeNo: item.chequeNo || item.checkNo, chequeDate: item.chequeDate || item.checkDate, statement: item.chequeStatement || '' }];
                    cheques.forEach(ch => {
                        const amt = parseNum(ch.amount);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * rate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const chequeAcct = 'cheques-receivable';
                        const stmt = ch.statement || item.statement || v.description || 'قبض شيك';

                        // Cheque Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: chequeAcct, counterAccount: item.account,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Recipient/Payer Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: item.account, counterAccount: chequeAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }
            });
        } else {
            // Fallback for old/empty items vouchers
            const totalAmount = parseNum(v.totalAmount);
            const valLoc = isBaseSDG ? totalAmount : totalAmount * rate;
            const valFc = isBaseSDG ? (totalAmount / effRate) : totalAmount;
            txns.push({
                date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                account: mainAcc, counterAccount: 'حسابات فرعية متعددة',
                statement: v.description || 'قبض مجمع',
                additionalNote: v.description || 'سند قبض مجمع',
                debit: valLoc, credit: 0, 
                debitForeign: valFc, creditForeign: 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }
    });

    // 2b2. Comprehensive Receipts (erp_comprehensive_receipts_v1)
    const compReceipts = JSON.parse(localStorage.getItem('erp_comprehensive_receipts_v1')) || [];
    compReceipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        
        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');
        const amt = parseNum(v.amount);
        const valLoc = isBaseSDG ? amt : amt * rate;
        const valFc = isBaseSDG ? (amt / effRate) : amt;
        
        let safeAcct = v.depositAccount || 'safe-001';
        if (v.paymentType === 'check') safeAcct = v.checkBank || 'cheques-receivable';
        else if (v.paymentType === 'bank') safeAcct = v.bankSourceName || safeAcct;
        else if (v.paymentType === 'mobile') safeAcct = v.mobileWallet || safeAcct;
        
        const stmt = v.description || v.depositDesc || 'سند قبض شامل';

        // Safe/Bank Account (Debit)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'comprehensive-receipt', typeLabel: 'سند قبض شامل',
            account: safeAcct, counterAccount: v.accountId,
            statement: stmt, additionalNote: stmt,
            debit: valLoc, credit: 0, 
            debitForeign: valFc, creditForeign: 0,
            costCenter, currency, exchangeRate: effRate, isPosted
        });
        
        // Payer Account (Credit)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'comprehensive-receipt', typeLabel: 'سند قبض شامل',
            account: v.accountId, counterAccount: safeAcct,
            statement: stmt, additionalNote: stmt,
            debit: 0, credit: valLoc, 
            debitForeign: 0, creditForeign: valFc,
            costCenter, currency, exchangeRate: effRate, isPosted
        });
    });

    // 2c. Multi-Payments (erp_payments_v1)
    const multiPayments = JSON.parse(localStorage.getItem('erp_payments_v1')) || [];
    multiPayments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        const mainAcc = v.fromAccount || 'safe-001';

        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                const pms = item.paymentMethods || [item.paymentMethod || 'cash'];
                
                // Cash Portion
                if (pms.includes('cash') && parseNum(item.cashAmount) > 0) {
                    const amt = parseNum(item.cashAmount);
                    const amtLoc = isBaseSDG ? amt : amt * rate;
                    const amtFc = isBaseSDG ? (amt / effRate) : amt;
                    const safeAcct = resolveAccount(item.cashSafe, mainAcc);
                    const stmt = item.cashStatement || item.statement || v.description || 'صرف نقدية';

                    // Recipient Account (Debit)
                    txns.push({
                        date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                        account: item.account, counterAccount: safeAcct,
                        statement: stmt,
                        additionalNote: stmt,
                        debit: amtLoc, credit: 0, 
                        debitForeign: amtFc, creditForeign: 0,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    
                    // Safe Account (Credit)
                    txns.push({
                        date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                        account: safeAcct, counterAccount: item.account,
                        statement: v.description || stmt,
                        additionalNote: v.description || stmt,
                        debit: 0, credit: amtLoc, 
                        debitForeign: 0, creditForeign: amtFc,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                }

                // Bank Transfer Portion
                if (pms.includes('bank') && parseNum(item.bankAmount) > 0) {
                    const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: item.bankAmount, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                    transfers.forEach(bt => {
                        const amt = parseNum(bt.amount);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * rate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const bankAcct = resolveAccount(bt.bankName, mainAcc);
                        const stmt = bt.statement || item.statement || v.description || 'صرف تحويل بنكي';

                        // Recipient Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: item.account, counterAccount: bankAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Bank Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: bankAcct, counterAccount: item.account,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }

                // Cheque Portion
                if (pms.includes('cheque') && parseNum(item.chequeAmount) > 0) {
                    const cheques = (item.cheques && item.cheques.length > 0) ? item.cheques : [{ amount: item.chequeAmount, chequeBank: item.chequeBank, chequeNo: item.chequeNo || item.checkNo, chequeDate: item.chequeDate || item.checkDate, statement: item.chequeStatement || '' }];
                    cheques.forEach(ch => {
                        const amt = parseNum(ch.amount);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * rate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const chequeAcct = resolveAccount(ch.chequeBank, 'banks-cheques');
                        const stmt = ch.statement || item.statement || v.description || 'صرف شيك';

                        // Recipient Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: item.account, counterAccount: chequeAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Cheque Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: chequeAcct, counterAccount: item.account,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }
            });
        } else {
            // Fallback for old/empty items vouchers
            const totalAmount = parseNum(v.totalAmount);
            const valLoc = isBaseSDG ? totalAmount : totalAmount * rate;
            const valFc = isBaseSDG ? (totalAmount / effRate) : totalAmount;
            txns.push({
                date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                account: mainAcc, counterAccount: 'حسابات فرعية متعددة',
                statement: v.description || 'صرف مجمع',
                additionalNote: v.description || 'سند صرف مجمع',
                debit: 0, credit: valLoc, 
                debitForeign: 0, creditForeign: valFc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }
    });
    // 2c2. Comprehensive Payments (erp_comprehensive_payments_v1)
    const compPayments = JSON.parse(localStorage.getItem('erp_comprehensive_payments_v1')) || [];
    compPayments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        
        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');
        const amt = parseNum(v.amount);
        const valLoc = isBaseSDG ? amt : amt * rate;
        const valFc = isBaseSDG ? (amt / effRate) : amt;
        
        let safeAcct = v.withdrawalAccount || 'safe-001';
        if (v.paymentType === 'check') safeAcct = v.checkBank || 'banks-cheques';
        else if (v.paymentType === 'bank') safeAcct = v.bankSourceName || safeAcct;
        else if (v.paymentType === 'mobile') safeAcct = v.mobileWallet || safeAcct;
        
        const stmt = v.description || v.withdrawalDesc || 'سند صرف شامل';

        // Safe/Bank Account (Credit)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'comprehensive-payment', typeLabel: 'سند صرف شامل',
            account: safeAcct, counterAccount: v.accountId,
            statement: stmt, additionalNote: stmt,
            debit: 0, credit: valLoc, 
            debitForeign: 0, creditForeign: valFc,
            costCenter, currency, exchangeRate: effRate, isPosted
        });
        
        // Payee Account (Debit)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'comprehensive-payment', typeLabel: 'سند صرف شامل',
            account: v.accountId, counterAccount: safeAcct,
            statement: stmt, additionalNote: stmt,
            debit: valLoc, credit: 0, 
            debitForeign: valFc, creditForeign: 0,
            costCenter, currency, exchangeRate: effRate, isPosted
        });
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
            const effRate = getEffectiveRate(v, rate);
            const isBaseSDG = (currency === 'SDG');
            const deb = parseNum(line.debit);
            const cred = parseNum(line.credit);
            const debLoc = isBaseSDG ? deb : deb * rate;
            const debFc = isBaseSDG ? (deb / effRate) : deb;
            const credLoc = isBaseSDG ? cred : cred * rate;
            const credFc = isBaseSDG ? (cred / effRate) : cred;

            txns.push({
                date: v.date, time: time, ref: v.id, type: 'jv', typeLabel: 'قيد يومي',
                account: line.account, counterAccount: '-',
                statement: 'قيد يومي #' + (v.voucherNo || v.id || 'بدون_رقم'),
                additionalNote: v.statement || line.note || 'قيد يومي',
                debit: debLoc, credit: credLoc, 
                debitForeign: debFc, creditForeign: credFc,
                costCenter, currency,
                exchangeRate: effRate,
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
        const effRate = getEffectiveRate(inv, rate);
        const isBaseSDG = (currency === 'SDG');
        const amtLoc = isBaseSDG ? total : total * rate;
        const amtFc = isBaseSDG ? (total / effRate) : total;

        const isReturn = (inv.type === 'return' || inv.isReturn === true);
        const debBase = isReturn ? 0 : amtLoc;
        const credBase = isReturn ? amtLoc : 0;
        const debBaseFc = isReturn ? 0 : amtFc;
        const credBaseFc = isReturn ? amtFc : 0;

        txns.push({
            date: inv.date, time: time, ref: refNo, type: isReturn ? 'sales-return' : 'invoice', typeLabel: isReturn ? 'مرتجع مبيعات' : 'مبيعات',
            account: customerAcct, counterAccount: 'revenue-sales',
            statement: (isReturn ? 'مرتجع مبيعات #' : 'فاتورة مبيعات #') + refNo,
            additionalNote: (isReturn ? `مردودات مبيعات — ` : `مبيعات آجلة — `) + (inv.customerName || inv.customer || ''),
            debit: debBase, credit: credBase, 
            debitForeign: debBaseFc, creditForeign: credBaseFc,
            costCenter, currency,
            exchangeRate: effRate,
            isPosted: isPosted
        });
        txns.push({
            date: inv.date, time: time, ref: refNo, type: isReturn ? 'sales-return' : 'invoice', typeLabel: isReturn ? 'مرتجع مبيعات' : 'مبيعات',
            account: 'revenue-sales', counterAccount: customerAcct,
            statement: (isReturn ? 'مرتجع مبيعات #' : 'فاتورة مبيعات #') + refNo,
            additionalNote: (isReturn ? `خصم إيرادات — ` : `إيرادات — `) + (inv.customerName || inv.customer || ''),
            debit: credBase, credit: debBase, 
            debitForeign: credBaseFc, creditForeign: debBaseFc,
            costCenter, currency,
            exchangeRate: effRate,
            isPosted: isPosted
        });

        if (inv.paymentMethod === 'hybrid' || inv.paymentMethod === 'multipay') {
            // A. Cash Safe Portion
            const cash = parseNum(inv.cashAmount || 0);
            if (cash > 0) {
                const cashLoc = isBaseSDG ? cash : cash * rate;
                const cashFc = isBaseSDG ? (cash / effRate) : cash;
                const paymentAcct = inv.paymentAccountId || 'safe-001';
                const debCash = isReturn ? 0 : cashLoc;
                const credCash = isReturn ? cashLoc : 0;
                const debCashFc = isReturn ? 0 : cashFc;
                const credCashFc = isReturn ? cashFc : 0;
                
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PAID', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    account: paymentAcct, counterAccount: customerAcct,
                    statement: isReturn ? 'سداد نقدي لمرتجع' : 'استلام نقدية (نقداً)',
                    additionalNote: isReturn ? 'صرف نقدي للمرتجع' : 'سداد نقدي مع الفاتورة',
                    debit: debCash, credit: credCash, 
                    debitForeign: debCashFc, creditForeign: credCashFc,
                    costCenter, currency,
                    exchangeRate: effRate,
                    isPosted: isPosted
                });
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PAID', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    account: customerAcct, counterAccount: paymentAcct,
                    statement: isReturn ? 'سداد نقدي لمرتجع' : 'استلام نقدية (نقداً)',
                    additionalNote: isReturn ? 'صرف نقدي للمرتجع' : 'سداد نقدي مع الفاتورة',
                    debit: credCash, credit: debCash, 
                    debitForeign: credCashFc, creditForeign: debCashFc,
                    costCenter, currency,
                    exchangeRate: effRate,
                    isPosted: isPosted
                });
            }
            // B. Bank Transfer Portions
            if (inv.bankTransfers && inv.bankTransfers.length > 0) {
                inv.bankTransfers.forEach((bt, idx) => {
                    const amt = parseNum(bt.amount || 0);
                    if (amt > 0) {
                        const amtLocBt = isBaseSDG ? amt : amt * rate;
                        const amtFcBt = isBaseSDG ? (amt / effRate) : amt;
                        const bankAcct = bt.bankAccountId || 'bank-001';
                        
                        const debBt = isReturn ? 0 : amtLocBt;
                        const credBt = isReturn ? amtLocBt : 0;
                        const debBtFc = isReturn ? 0 : amtFcBt;
                        const credBtFc = isReturn ? amtFcBt : 0;
                        
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-BT${idx+1}`, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            account: bankAcct, counterAccount: customerAcct,
                            statement: `تحويل بنكي مرجع #${bt.reference || ''}`,
                            additionalNote: isReturn ? 'إرجاع تحويل بنكي' : 'سداد تحويل بنكي مع الفاتورة',
                            debit: debBt, credit: credBt, 
                            debitForeign: debBtFc, creditForeign: credBtFc,
                            costCenter, currency,
                            exchangeRate: effRate,
                            isPosted: isPosted
                        });
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-BT${idx+1}`, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            account: customerAcct, counterAccount: bankAcct,
                            statement: `تحويل بنكي مرجع #${bt.reference || ''}`,
                            additionalNote: isReturn ? 'إرجاع تحويل بنكي' : 'سداد تحويل بنكي مع الفاتورة',
                            debit: credBt, credit: debBt, 
                            debitForeign: credBtFc, creditForeign: debBtFc,
                            costCenter, currency,
                            exchangeRate: effRate,
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
                        const amtFcCh = isBaseSDG ? (amt / effRate) : amt;
                        const chequeAcct = 'cheques-receivable';
                        
                        const debCh = isReturn ? 0 : amtLocCh;
                        const credCh = isReturn ? amtLocCh : 0;
                        const debChFc = isReturn ? 0 : amtFcCh;
                        const credChFc = isReturn ? amtFcCh : 0;
                        
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-CH${idx+1}`, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            account: chequeAcct, counterAccount: customerAcct,
                            statement: `شيك رقم #${ch.chequeNo || ''} استحقاق ${ch.dueDate || ''}`,
                            additionalNote: isReturn ? 'إرجاع شيك' : 'سداد شيك مع الفاتورة',
                            debit: debCh, credit: credCh, 
                            debitForeign: debChFc, creditForeign: credChFc,
                            costCenter, currency,
                            exchangeRate: effRate,
                            isPosted: isPosted
                        });
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-CH${idx+1}`, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                            account: customerAcct, counterAccount: chequeAcct,
                            statement: `شيك رقم #${ch.chequeNo || ''} استحقاق ${ch.dueDate || ''}`,
                            additionalNote: isReturn ? 'إرجاع شيك' : 'سداد شيك مع الفاتورة',
                            debit: credCh, credit: debCh, 
                            debitForeign: credChFc, creditForeign: debChFc,
                            costCenter, currency,
                            exchangeRate: effRate,
                            isPosted: isPosted
                        });
                    }
                });
            }
        } else if (inv.paymentMethod !== 'credit') {
            const paymentAcct = inv.paymentAccountId || 'safe-001';
            
            const debFull = isReturn ? 0 : amtLoc;
            const credFull = isReturn ? amtLoc : 0;
            const debFullFc = isReturn ? 0 : amtFc;
            const credFullFc = isReturn ? amtFc : 0;
            
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                account: paymentAcct, counterAccount: customerAcct,
                statement: isReturn ? 'سداد مرتجع نقدي/بنكي' : 'استلام نقدية',
                additionalNote: isReturn ? 'صرف مبالغ المرتجع' : 'سداد نقدي مع الفاتورة',
                debit: debFull, credit: credFull, 
                debitForeign: debFullFc, creditForeign: credFullFc,
                costCenter, currency,
                exchangeRate: effRate,
                isPosted: isPosted
            });
            txns.push({
                date: inv.date, time: time, ref: refNo + '-PAID', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                account: customerAcct, counterAccount: paymentAcct,
                statement: isReturn ? 'سداد مرتجع نقدي/بنكي' : 'استلام نقدية',
                additionalNote: isReturn ? 'صرف مبالغ المرتجع' : 'سداد نقدي مع الفاتورة',
                debit: credFull, credit: debFull, 
                debitForeign: credFullFc, creditForeign: debFullFc,
                costCenter, currency,
                exchangeRate: effRate,
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
        const effRate = getEffectiveRate(inv, rate);
        const isBaseSDG = (currency === 'SDG');
        const amtLoc = isBaseSDG ? total : total * rate;
        const amtFc = isBaseSDG ? (total / effRate) : total;

        if (inv.isAdjustment) {
            const absAmtLoc = Math.abs(amtLoc);
            const absAmtFc = Math.abs(amtFc);
            const isSurplus = (amtLoc >= 0);

            txns.push({
                date: inv.date, time: time, ref: refNo, type: 'adjustment', typeLabel: 'تسوية مخزنية',
                account: isSurplus ? 'act-inventory' : 'exp-inventory-adjustments',
                counterAccount: isSurplus ? 'exp-inventory-adjustments' : 'act-inventory',
                statement: 'تسوية مخزنية للفروقات #' + refNo,
                additionalNote: inv.notes || 'تسوية مخزنية للفروقات',
                debit: absAmtLoc, credit: 0, 
                debitForeign: absAmtFc, creditForeign: 0,
                costCenter, currency,
                exchangeRate: effRate,
                isPosted: isPosted
            });
            txns.push({
                date: inv.date, time: time, ref: refNo, type: 'adjustment', typeLabel: 'تسوية مخزنية',
                account: isSurplus ? 'exp-inventory-adjustments' : 'act-inventory',
                counterAccount: isSurplus ? 'act-inventory' : 'exp-inventory-adjustments',
                statement: 'تسوية مخزنية للفروقات #' + refNo,
                additionalNote: inv.notes || 'تسوية مخزنية للفروقات',
                debit: 0, credit: absAmtLoc, 
                debitForeign: 0, creditForeign: absAmtFc,
                costCenter, currency,
                exchangeRate: effRate,
                isPosted: isPosted
            });
            return;
        }


        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'purchase', typeLabel: 'مشتريات',
            account: 'exp-purchases', counterAccount: supplierAcct,
            statement: 'فاتورة مشتريات #' + refNo,
            additionalNote: `مشتريات آجلة — ${inv.supplierName || inv.supplier || ''}`,
            debit: amtLoc, credit: 0, 
            debitForeign: amtFc, creditForeign: 0,
            costCenter, currency,
            exchangeRate: effRate,
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
            exchangeRate: effRate,
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
                exchangeRate: effRate,
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
                exchangeRate: effRate,
                isPosted: isPosted
            });
        }
    });

    // 6. Map source data for checkNo and transferNo extraction
    const bankTransfersData = JSON.parse(localStorage.getItem('erp_bank_transfers_v1')) || [];
    const checksData = JSON.parse(localStorage.getItem('erp_checks_v1')) || [];
    const allData = [
        ...receipts, ...payments, ...multiReceipts, ...multiPayments, ...compReceipts, ...jvs, ...sales, ...purchases, ...bankTransfersData, ...checksData
    ];
    const dataMap = new Map();
    allData.forEach(d => {
        if(d.id) dataMap.set(String(d.id), d);
        if(d.invoiceNo) dataMap.set(String(d.invoiceNo), d);
        if(d.voucherNo) dataMap.set(String(d.voucherNo), d);
        if(d.refNo) dataMap.set(String(d.refNo), d);
    });

    txns.forEach(t => {
        let baseRef = String(t.ref).replace(/-PAID$/, '').replace(/-BT\d+$/, '').replace(/-CH\d+$/, '');
        let source = dataMap.get(baseRef);
        if (source) {
            t._source = source;
            const isBaseSDG = (t.currency === 'SDG');
            const amt = t.debit || t.credit || 0;
            const matchAmt = (val) => {
                let v = parseFloat(val) || 0;
                return Math.abs(v - amt) < 0.1 || Math.abs((isBaseSDG ? v : v * (t.exchangeRate || 1)) - amt) < 0.1;
            };

            // Process cheques
            if (!t.checkNo) {
                if (source.cheques && source.cheques.length > 0) {
                    let ch = source.cheques.find(c => matchAmt(c.amount)) || source.cheques[0];
                    if (ch) t.checkNo = (ch.checkNo || ch.chequeNo || ch.check_number || '') + (ch.checkBranch || ch.chequeBank ? ` (${ch.checkBranch || ch.chequeBank})` : '');
                } else if (source.checkNo || source.checkNumber || source.check_number || source.chequeNo) {
                    let cNo = source.checkNo || source.checkNumber || source.check_number || source.chequeNo;
                    let cBank = source.checkBranch || source.chequeBank || source.bankName || '';
                    t.checkNo = cBank ? `${cNo} (${cBank})` : cNo;
                }
            }

            // Process bank transfers
            if (!t.transferNo) {
                if (source.bankTransfers && source.bankTransfers.length > 0) {
                    let bt = source.bankTransfers.find(b => matchAmt(b.amount)) || source.bankTransfers[0];
                    if (bt) t.transferNo = (bt.transferNo || bt.transfer_no || bt.referenceNo || '') + (bt.bankName ? ` (${bt.bankName})` : (bt.note ? ` (${bt.note})` : ''));
                } else if (source.transferNo || source.transfer_no || source.transferNumber || source.mobileTransferNo) {
                    let tr = source.transferNo || source.transfer_no || source.transferNumber || source.mobileTransferNo;
                    let note = source.mobileNote || source.bankName || source.note || '';
                    t.transferNo = note ? `${tr} (${note})` : tr;
                }
            }

            // Process multi-items
            if (source.items && source.items.length > 0) {
                let item = source.items.find(it => it.account === t.account || it.account === t.counterAccount);
                if (item) {
                    if (!t.checkNo) {
                        if (item.cheques && item.cheques.length > 0) {
                            let ch = item.cheques.find(c => matchAmt(c.amount)) || item.cheques[0];
                            if (ch) t.checkNo = (ch.chequeNo || ch.checkNo || ch.check_number || '') + (ch.chequeBank || ch.checkBranch ? ` (${ch.chequeBank || ch.checkBranch})` : '');
                        } else if (item.checkNo || item.chequeNo || item.check_number) {
                            t.checkNo = (item.checkNo || item.chequeNo || item.check_number) + (item.chequeBank || item.checkBranch ? ` (${item.chequeBank || item.checkBranch})` : '');
                        }
                    }

                    if (!t.transferNo) {
                        if (item.bankTransfers && item.bankTransfers.length > 0) {
                            let bt = item.bankTransfers.find(b => matchAmt(b.amount)) || item.bankTransfers[0];
                            if (bt) t.transferNo = (bt.transferNo || bt.transfer_no || '') + (bt.bankName ? ` (${bt.bankName})` : (bt.note ? ` (${bt.note})` : ''));
                        } else if (item.transferNo || item.transfer_no) {
                            t.transferNo = (item.transferNo || item.transfer_no) + (item.bankName || item.note ? ` (${item.bankName || item.note})` : '');
                        }
                    }
                }
            }
        }
    });

    // Recalibrate true exchange rate if both debit/credit exist in local and foreign
    txns.forEach(t => {
        const d = parseFloat(t.debit) || 0;
        const c = parseFloat(t.credit) || 0;
        const df = parseFloat(t.debitForeign || t.dr_foreign) || 0;
        const cf = parseFloat(t.creditForeign || t.cr_foreign) || 0;
        
        const loc = d > 0 ? d : c;
        const forgn = df > 0 ? df : cf;
        
        if (loc > 0 && forgn > 0) {
            t.exchangeRate = loc / forgn;
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
    if (document.getElementById('filter-date-from')) document.getElementById('filter-date-from').value = firstDay.toISOString().slice(0, 10);
    if (document.getElementById('filter-date-to')) document.getElementById('filter-date-to').value = now.toISOString().slice(0, 10);
    
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
        const accountIdParam = urlParams.get('accountId') || urlParams.get('partner_id') || urlParams.get('id');
        const nameParam = urlParams.get('name') || urlParams.get('partner_name');

        if (accountIdParam || nameParam) {
            let found = null;
            if (accountIdParam) {
                found = ALL_ACCOUNTS.find(a => String(a.id) === String(accountIdParam));
                if (!found) {
                    const cleanParam = String(accountIdParam).replace(/^(cust|supp|bank|safe|exp|sub|sust)-/i, '');
                    found = ALL_ACCOUNTS.find(a => String(a.id).replace(/^(cust|supp|bank|safe|exp|sub|sust)-/i, '') === cleanParam);
                }
            }
            if (!found && nameParam) {
                const normName = String(nameParam).trim().toLowerCase();
                found = ALL_ACCOUNTS.find(a => String(a.name).trim().toLowerCase() === normName || String(a.name).includes(nameParam));
            }

            if (found) {
                selectAccount(found.id, found.name, found.icon);
            } else if (accountIdParam || nameParam) {
                const targetId = accountIdParam || nameParam;
                const targetName = nameParam || accountIdParam;
                selectAccount(targetId, targetName, '👤');
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

// Explicitly expose functions to window for inline onclick handlers
window.openPreviewDesign = openPreviewDesign;
window.openPresetModal = openPresetModal;
window.prepareLedgerPreviewData = prepareLedgerPreviewData;

function getAccountName(id) {
    if(id === 'ALL') return 'جميع الحسابات';
    if(id === '-' || !id) return '-';
    
    let idStr = String(id).trim();
    let idLower = idStr.toLowerCase();
    
    // 1. Fast O(1) cache lookup
    if (ACCOUNT_NAME_CACHE.has(idLower)) return ACCOUNT_NAME_CACHE.get(idLower);
    
    // 2. Try without prefix
    let cleanId = idLower.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
    if (cleanId && ACCOUNT_NAME_CACHE.has(cleanId)) return ACCOUNT_NAME_CACHE.get(cleanId);
    
    // 3. Fallback: return cleaned ID
    if (cleanId === 'safe-001' || idStr === 'safe-001') return 'الخزينة الرئيسية';
    if (cleanId === 'bank-001' || idStr === 'bank-001') return 'البنك الرئيسي';
    if (cleanId === 'bankok' || idStr === 'bankok') return 'بنكك (Bankak)';
    if (cleanId === 'fawry' || idStr === 'fawry') return 'فوري (Fawry)';
    if (cleanId === 'o-cash' || idStr === 'o-cash') return 'أوكاش (O-Cash)';
    if (cleanId === 'other' || idStr === 'other') return 'أخرى';
    
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
                <span>الفترة: <strong>${currentDateMode === 'all' ? 'كل الفترة' : `من ${document.getElementById('filter-date-from').value || '-'} إلى ${document.getElementById('filter-date-to').value || '-'}`}</strong></span>
                <span>تاريخ الطباعة: <strong>${new Date().toLocaleDateString('ar-SD')}</strong></span>
            </div>
            ${tableClone.outerHTML}
        </body>
        </html>
    `;
    printContainer.innerHTML = htmlContent;
    document.body.appendChild(printContainer);

    const periodText = currentDateMode === 'all' ? 'لكل الفترة' : 'لفتـرة من ' + document.getElementById('filter-date-from').value + ' إلى ' + document.getElementById('filter-date-to').value;
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

// ==========================================
// LAUNCHER SPECIFIC LOGIC (Rebuilt)
// ==========================================

let currentTxns = [];
const LEDGER_PAGE_SIZE = 50;
let ledgerCurrentPage = 1;

window.nextLedgerPage = function() {
    if (ledgerCurrentPage * LEDGER_PAGE_SIZE < currentTxns.length) {
        ledgerCurrentPage++;
        renderLedgerPage();
    }
};

window.prevLedgerPage = function() {
    if (ledgerCurrentPage > 1) {
        ledgerCurrentPage--;
        renderLedgerPage();
    }
};

function renderLedgerPage() {
    const start = (ledgerCurrentPage - 1) * LEDGER_PAGE_SIZE;
    const end = start + LEDGER_PAGE_SIZE;
    renderRecentTable(currentTxns.slice(start, end), start);
}

function runReport() {
    try {
        ALL_TXNS = collectAllTransactions();
        
        const accEl = document.getElementById('filter-account');
        const fromEl = document.getElementById('filter-date-from');
        const toEl = document.getElementById('filter-date-to');
        const curEl = document.getElementById('filter-currency');
        const typeEl = document.getElementById('filter-type');
        const ccEl = document.getElementById('filter-costcenter');
        const postedEl = document.getElementById('filter-posted');
        const smartEl = document.getElementById('filter-smart');

        const accVal = accEl ? accEl.value : 'ALL';
        const fromDate = fromEl ? fromEl.value : '';
        const toDate = toEl ? toEl.value : '';
        const currencyFilter = curEl ? curEl.value : 'ALL';
        const typeFilter = typeEl ? typeEl.value : 'ALL';
        const ccFilter = ccEl ? ccEl.value : 'ALL';
        const postedFilter = postedEl ? postedEl.value : 'ALL';
        const smartSearch = (smartEl ? smartEl.value : '').trim().toLowerCase();
        
        let filtered = ALL_TXNS || [];

        // Account Filter
        if (accVal !== 'ALL') {
            const cleanFilter = accVal.replace(/^(cust|supp|bank|safe|exp)-/, '');
            filtered = filtered.filter(t => {
                if (!t) return false;
                if (String(t.account) === String(accVal)) return true;
                if (String(t.account) === cleanFilter) return true;
                return false;
            });
        }

        // Date Filter
        if (currentDateMode === 'range') {
            if (fromDate) filtered = filtered.filter(t => t && t.date >= fromDate);
            if (toDate) filtered = filtered.filter(t => t && t.date <= toDate);
        }

        // Currency Filter
        if (currencyFilter === 'LOCAL') {
            filtered = filtered.filter(t => t && t.currency === 'SDG');
        } else if (currencyFilter === 'FOREIGN') {
            filtered = filtered.filter(t => t && t.currency !== 'SDG');
        }

        // Type Filter
        if (typeFilter !== 'ALL') {
            filtered = filtered.filter(t => {
                if (!t) return false;
                if (typeFilter === 'sales' && t.type === 'invoice' && t.account !== 'revenue-sales') return true;
                if (typeFilter === 'purchases' && t.type === 'purchase' && t.account !== 'cost-purchases') return true;
                if (typeFilter === 'receipt' && (t.type === 'receipt' || t.type === 'multi-receipt') && t.debit > 0) return true;
                if (typeFilter === 'payment' && (t.type === 'payment' || t.type === 'multi-payment') && t.credit > 0) return true;
                if (typeFilter === 'journal' && t.type === 'jv') return true;
                return false;
            });
        }

        // Cost Center
        if (ccFilter !== 'ALL') {
            filtered = filtered.filter(t => t && t.costCenter === ccFilter);
        }

        // Posted Status
        if (postedFilter === 'POSTED') {
            filtered = filtered.filter(t => t && t.isPosted === true);
        } else if (postedFilter === 'UNPOSTED') {
            filtered = filtered.filter(t => t && t.isPosted === false);
        }

        // Smart Search
        if (smartSearch) {
            filtered = filtered.filter(t => {
                if (!t) return false;
                return (t.statement || '').toLowerCase().includes(smartSearch) ||
                       (t.additionalNote || '').toLowerCase().includes(smartSearch) ||
                       (String(t.ref) || '').toLowerCase().includes(smartSearch) ||
                       (t.checkNo || '').toLowerCase().includes(smartSearch) ||
                       (t.transferNo || '').toLowerCase().includes(smartSearch);
            });
        }

        const parseTimeTo24h = (timeStr) => {
            if (!timeStr) return '00:00:00';
            timeStr = String(timeStr).trim();
            let isPM = /pm|مساء/i.test(timeStr);
            let isAM = /am|صباحا|صباحاً/i.test(timeStr);
            let clean = timeStr.replace(/[^0-9:]/g, '');
            let parts = clean.split(':').map(p => parseInt(p, 10) || 0);
            let h = parts[0] || 0, m = parts[1] || 0, s = parts[2] || 0;
            if (isPM && h < 12) h += 12;
            if (isAM && h === 12) h = 0;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        };
        // ── Load Opening Balance (Intrinsic Balance) & Foreign Dollar Rates from Master Data ──
        let intrinsicBal = 0;
        let intrinsicBalFc = 0;
        let entityCurrency = 'SDG';
        let entityExRate = 0;

        let usdRate = 600;
        if (typeof window.getExchangeRateForCurrency === 'function') {
            const r = window.getExchangeRateForCurrency('USD');
            if (r && r > 0) usdRate = parseFloat(r);
        } else {
            try {
                const rates = JSON.parse(localStorage.getItem('erp_currency_rates_v1')) || {};
                if (rates['USD'] && rates['USD'] > 0) usdRate = parseFloat(rates['USD']);
            } catch(e){}
        }

        if (accVal !== 'ALL') {
            const cleanId = accVal.replace(/^(cust|supp|bank|safe|exp)-/, '');
            
            const custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => x && !x.deleted);
            const sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => x && !x.deleted);
            const banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => x && !x.deleted);
            const safes = (JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => x && !x.deleted);
            const accounts = (JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => x && !x.deleted);
            
            let masterRec = custs.find(x => x && (x.id === accVal || x.id === cleanId || x.name === accVal)) 
                         || sups.find(x => x && (x.id === accVal || x.id === cleanId || x.name === accVal)) 
                         || banks.find(x => x && (x.id === accVal || x.id === cleanId || x.name === accVal)) 
                         || safes.find(x => x && (x.id === accVal || x.id === cleanId || x.name === accVal))
                         || accounts.find(x => x && (x.id === accVal || x.id === cleanId || x.code === cleanId || x.name === accVal));
            
            if (masterRec) {
                const opDebit = parseNum(masterRec.openingDebit || masterRec.debit || 0);
                const opCredit = parseNum(masterRec.openingCredit || masterRec.credit || 0);
                const rawBal = parseNum(masterRec.openingBalance || masterRec.opening_balance || masterRec.balance || 0);

                if (opDebit > 0 || opCredit > 0) {
                    intrinsicBal = opDebit - opCredit;
                } else if (rawBal !== 0) {
                    if (masterRec.balance_type === 'credit' || masterRec.balanceType === 'credit') {
                        intrinsicBal = -Math.abs(rawBal);
                    } else {
                        intrinsicBal = Math.abs(rawBal);
                    }
                }
                entityCurrency = masterRec.currency || 'SDG';
                entityExRate = parseNum(masterRec.exchangeRate || masterRec.exRate || masterRec.rate || 0);
            }
        }

        if (!entityExRate || entityExRate <= 0) {
            entityExRate = usdRate;
        }

        let opDebitLoc = intrinsicBal > 0 ? intrinsicBal : 0;
        let opCreditLoc = intrinsicBal < 0 ? Math.abs(intrinsicBal) : 0;

        let opDebitFc = entityExRate > 0 ? (opDebitLoc / entityExRate) : 0;
        let opCreditFc = entityExRate > 0 ? (opCreditLoc / entityExRate) : 0;
        intrinsicBalFc = entityExRate > 0 ? (intrinsicBal / entityExRate) : 0;

        // Compute running balance in chronological order (oldest first)
        let runningBal = intrinsicBal;
        let runningBalFc = intrinsicBalFc;
        let chronological = filtered.slice().sort((a, b) => {
            const da = ((a && a.date) || '') + ' ' + parseTimeTo24h(a && a.time);
            const db = ((b && b.date) || '') + ' ' + parseTimeTo24h(b && b.time);
            let cmp = da.localeCompare(db);
            if (cmp === 0) {
                cmp = String((a && a.ref) || '').localeCompare(String((b && b.ref) || ''), undefined, { numeric: true });
            }
            return cmp;
        });

        // Insert opening balance row if account-specific view
        if (accVal !== 'ALL' && (intrinsicBal !== 0 || chronological.length > 0)) {
            chronological.unshift({
                date: fromDate || '0000-00-00', time: '', ref: 'OPENING', type: 'opening',
                typeLabel: 'رصيد افتتاحي', account: accVal, counterAccount: '-',
                statement: 'الرصيد الافتتاحي',
                debit: opDebitLoc, credit: opCreditLoc,
                debitForeign: opDebitFc, creditForeign: opCreditFc,
                exchangeRate: entityExRate,
                balance: intrinsicBal, balanceForeign: intrinsicBalFc,
                costCenter: '', currency: entityCurrency, isOpening: true, isPosted: true
            });
        }

        chronological.forEach(t => {
            if (!t) return;
            let debLoc = parseNum(t.debit || t.dr || 0);
            let credLoc = parseNum(t.credit || t.cr || 0);
            let rate = parseNum(t.exchangeRate || t.exRate || t.rate || entityExRate || 1);
            if (!rate || rate <= 0) rate = entityExRate || 1;
            t.exchangeRate = rate;

            let df = parseNum(t.debitForeign || t.dr_foreign || 0);
            let cf = parseNum(t.creditForeign || t.cr_foreign || 0);

            if (debLoc > 0 && (df === 0 || df === debLoc) && rate > 1) {
                df = debLoc / rate;
            }
            if (credLoc > 0 && (cf === 0 || cf === credLoc) && rate > 1) {
                cf = credLoc / rate;
            }

            t.debitForeign = df;
            t.creditForeign = cf;

            if (accVal !== 'ALL') {
                if (t.isOpening) {
                    t.balance = t.balance !== undefined ? t.balance : runningBal;
                    t.balanceForeign = t.balanceForeign !== undefined ? t.balanceForeign : runningBalFc;
                } else {
                    runningBal += (debLoc - credLoc);
                    runningBalFc += (df - cf);
                    t.balance = runningBal;
                    t.balanceForeign = runningBalFc;
                }
            } else {
                t.balance = undefined;
                t.balanceForeign = undefined;
            }
        });

        // Set display order to Chronological (Oldest first at top, newest created at bottom)
        currentTxns = chronological;

        let totalDebit = 0;
        let totalCredit = 0;
        currentTxns.forEach(t => {
            if (!t || t.isOpening) return;
            totalDebit += (t.debit || 0);
            totalCredit += (t.credit || 0);
        });

        const tdDebit = document.getElementById('launcher-total-debit');
        const tdCredit = document.getElementById('launcher-total-credit');
        const tdBalance = document.getElementById('launcher-total-balance');

        if (tdDebit) tdDebit.innerText = typeof fmtNum === 'function' ? fmtNum(totalDebit) : totalDebit.toLocaleString();
        if (tdCredit) tdCredit.innerText = typeof fmtNum === 'function' ? fmtNum(totalCredit) : totalCredit.toLocaleString();
        if (tdBalance) {
            let bal = totalDebit - totalCredit;
            tdBalance.innerText = typeof fmtNum === 'function' ? fmtNum(bal) : bal.toLocaleString();
        }

        ledgerCurrentPage = 1;
        renderLedgerPage();
    } catch (e) {
        console.error('Error running report:', e);
        // Show the error on screen so we know exactly why it fails instead of silently hiding it
        const tbody = document.getElementById('recent-tbody');
        if (tbody) tbody.innerHTML = `<tr><td colspan="14" style="text-align:center; padding: 20px; color:red;">حدث خطأ أثناء تحميل التقرير: ${e.message}</td></tr>`;
    }
}

/* ═══════════════════════════════════════════════════════
   Smart General Ledger (SGL) Column Customizer & Drilldown
   ═══════════════════════════════════════════════════════ */

const DEFAULT_GL_COLUMNS_CONFIG = [
    { id: 'seq', label: 'م', isNum: false, align: 'center', width: '40px' },
    { id: 'date', label: 'التاريخ', isNum: false, align: 'center', width: '90px' },
    { id: 'ref', label: 'الرقم المرجعي', isNum: false, align: 'center', width: '90px' },
    { id: 'account', label: 'الحساب', isNum: false, align: 'right', width: '140px' },
    { id: 'typeLabel', label: 'نوع الحركة', isNum: false, align: 'center', width: '90px' },
    { id: 'statement', label: 'البيان', isNum: false, align: 'right', width: 'auto' },
    { id: 'counterAccount', label: 'الحساب المقابل', isNum: false, align: 'right', width: '140px' },
    { id: 'debit', label: 'مدين', isNum: true, align: 'right', width: '90px', numColor: '#34d399' },
    { id: 'credit', label: 'دائن', isNum: true, align: 'right', width: '90px', numColor: '#f87171' },
    { id: 'balance', label: 'الإجمالي المحلي', isNum: true, align: 'right', width: '100px' },
    { id: 'exchangeRate', label: 'سعر الصرف', isNum: true, align: 'center', width: '90px', numColor: '#eab308' },
    { id: 'debitForeign', label: 'مدين أجنبي', isNum: true, align: 'right', width: '90px', numColor: '#34d399' },
    { id: 'creditForeign', label: 'دائن أجنبي', isNum: true, align: 'right', width: '90px', numColor: '#f87171' },
    { id: 'balanceForeign', label: 'الإجمالي الأجنبي', isNum: true, align: 'right', width: '120px' }
];

let activeGLColumnSettings = loadGLColumnSettings();

function loadGLColumnSettings() {
    try {
        const stored = localStorage.getItem('gl_column_settings_v4');
        if (stored) {
            const parsed = JSON.parse(stored);
            if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        }
    } catch(e){}
    return JSON.parse(JSON.stringify(DEFAULT_GL_COLUMNS_CONFIG));
}

function saveGLColumnSettings() {
    try {
        localStorage.setItem('gl_column_settings_v4', JSON.stringify(activeGLColumnSettings));
    } catch(e){}
}

window.resetGLColumnsToDefault = function() {
    activeGLColumnSettings = JSON.parse(JSON.stringify(DEFAULT_GL_COLUMNS_CONFIG));
    saveGLColumnSettings();
    if (typeof runReport === 'function') runReport();
    showToast('تمت إعادة ضبط الأعمدة للوضع الافتراضي', 'info');
};

window.resetMagnifiedNumbers = function() {
    const magnified = document.querySelectorAll('.pos-grid td.num-magnified');
    magnified.forEach(el => el.classList.remove('num-magnified'));
    showToast('تم تصغير جميع الأرقام لحجمها الطبيعي', 'info');
};

function fmtNumCustom(n, decimals = 2, thousandsStyle = 'comma') {
    const val = parseFloat(n) || 0;
    if (val === 0 && (n === '' || n === undefined || n === null)) return '';
    
    let formatted = val.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });

    if (thousandsStyle === 'none') {
        formatted = val.toFixed(decimals);
    } else if (thousandsStyle === 'space') {
        formatted = formatted.replace(/,/g, ' ');
    }

    if (val < 0) {
        let absVal = Math.abs(val);
        let absFormatted = absVal.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        if (thousandsStyle === 'none') absFormatted = absVal.toFixed(decimals);
        else if (thousandsStyle === 'space') absFormatted = absFormatted.replace(/,/g, ' ');
        return `-(${absFormatted})`;
    }
    return formatted;
}

function renderRecentTable(txns, startIndex = 0) {
    const table = document.getElementById('recent-table');
    const tbody = document.getElementById('recent-tbody');
    if (!tbody || !table) return;

    const visibleCols = activeGLColumnSettings.filter(c => !c.hidden);
    
    // 1. Render Table Header dynamically
    let thead = table.querySelector('thead');
    if (!thead) {
        thead = document.createElement('thead');
        table.insertBefore(thead, tbody);
    }
    
    let headHtml = '<tr>';
    visibleCols.forEach(col => {
        const hStyle = `
            text-align: ${col.align || 'right'};
            ${col.width ? 'width:' + col.width + ';' : ''}
            ${col.headerBg ? 'background:' + col.headerBg + '!important;' : ''}
            ${col.headerColor ? 'color:' + col.headerColor + '!important;' : ''}
            ${col.fontFamily ? 'font-family:' + col.fontFamily + ', sans-serif!important;' : ''}
        `;
        headHtml += `<th data-col-id="${col.id}" style="${hStyle}">${col.label}</th>`;
    });
    headHtml += '</tr>';
    thead.innerHTML = headHtml;

    if (txns.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${visibleCols.length}" style="text-align:center; padding: 20px;">لا توجد عمليات تطابق الفلتر...</td></tr>`;
        return;
    }

    // 2. Render Table Body Rows
    let html = '';
    txns.forEach((t, i) => {
        const counterAccName = getAccountName(t.counterAccount) || '-';
        const mainAccName = getAccountName(t.account) || '-';
        const isPosBal = (t.balance || 0) >= 0;
        const isPosBalFc = (t.balanceForeign || 0) >= 0;

        let trStyle = t.isOpening ? 'background:rgba(59,130,246,0.08); border-right:3px solid #3b82f6;' : '';

        html += `<tr style="${trStyle}" data-txn-type="${t.type || ''}" data-txn-ref="${t.ref || ''}">`;
        
        visibleCols.forEach(col => {
            const dec = col.decimals !== undefined ? col.decimals : 2;
            const thSep = col.thousandsStyle || 'comma';
            const cStyle = `
                text-align: ${col.align || 'right'};
                ${col.cellBg ? 'background:' + col.cellBg + '!important;' : ''}
                ${col.cellColor ? 'color:' + col.cellColor + '!important;' : ''}
                ${col.fontFamily ? 'font-family:\'' + col.fontFamily + '\', sans-serif!important;' : ''}
                ${col.fontWeight ? 'font-weight:' + col.fontWeight + '!important;' : ''}
                ${col.fontSize ? 'font-size:' + col.fontSize + '!important;' : ''}
            `;

            if (col.id === 'seq') {
                html += `<td data-col-id="seq" data-is-num="false" style="${cStyle}">${startIndex + i + 1}</td>`;
            } else if (col.id === 'date') {
                html += `<td data-col-id="date" data-is-num="false" title="${t.time || ''}" style="${cStyle}">${t.date || '-'}</td>`;
            } else if (col.id === 'ref') {
                html += `<td data-col-id="ref" data-is-num="true" style="${cStyle}">${t.isOpening ? '<span style="color:#3b82f6; font-weight:900;">📋 افتتاحي</span>' : '<strong style="color:#38bdf8; cursor:pointer;" onclick="navigateToOriginalVoucher(\'' + (t.type || '') + '\', \'' + (t.ref || '') + '\')" title="انقر للذهاب لأصل السند">' + (t.ref || '-') + '</strong>'}</td>`;
            } else if (col.id === 'account') {
                html += `<td data-col-id="account" data-is-num="false" style="${cStyle}"><span style="font-weight:700;">${mainAccName}</span></td>`;
            } else if (col.id === 'typeLabel') {
                html += `<td data-col-id="typeLabel" data-is-num="false" style="${cStyle}"><span style="background:${t.isOpening ? 'rgba(59,130,246,0.15)' : 'rgba(168,85,247,0.15)'}; color:${t.isOpening ? '#3b82f6' : '#c084fc'}; padding:2px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;">${t.typeLabel || '-'}</span></td>`;
            } else if (col.id === 'statement') {
                html += `<td data-col-id="statement" data-is-num="false" style="white-space:normal; min-width:180px; ${cStyle} ${t.isOpening ? 'font-weight:bold; color:#3b82f6;' : ''}">
                    ${(t.statement === 'undefined' ? '-' : t.statement) || '-'}
                    ${!t.isOpening && t.additionalNote && t.additionalNote !== t.statement && t.additionalNote !== 'undefined' ? '<br><small style="color:#94a3b8;">' + t.additionalNote + '</small>' : ''}
                </td>`;
            } else if (col.id === 'counterAccount') {
                html += `<td data-col-id="counterAccount" data-is-num="false" style="${cStyle}">${t.isOpening ? '-' : counterAccName}</td>`;
            } else if (col.id === 'debit') {
                const color = col.numColor || '#34d399';
                html += `<td data-col-id="debit" data-is-num="true" class="num-cell" style="color:${color}; font-weight:900; ${cStyle}">${t.debit > 0 ? fmtNumCustom(t.debit, dec, thSep) : ''}</td>`;
            } else if (col.id === 'credit') {
                const color = col.numColor || '#f87171';
                html += `<td data-col-id="credit" data-is-num="true" class="num-cell" style="color:${color}; font-weight:900; ${cStyle}">${t.credit > 0 ? fmtNumCustom(t.credit, dec, thSep) : ''}</td>`;
            } else if (col.id === 'balance') {
                const color = col.numColor || (isPosBal ? '#34d399' : '#f87171');
                html += `<td data-col-id="balance" data-is-num="true" class="num-cell" style="color:${color}; font-weight:900; font-family:'JetBrains Mono',monospace; ${cStyle}">${t.balance !== undefined ? fmtNumCustom(t.balance, dec, thSep) : '-'}</td>`;
            } else if (col.id === 'exchangeRate') {
                const color = col.numColor || '#eab308';
                html += `<td data-col-id="exchangeRate" data-is-num="true" class="num-cell" style="color:${color}; font-weight:900; ${cStyle}">${fmtNumCustom(t.exchangeRate || 1, dec, thSep)}</td>`;
            } else if (col.id === 'debitForeign') {
                const color = col.numColor || '#34d399';
                html += `<td data-col-id="debitForeign" data-is-num="true" class="num-cell" style="color:${color}; font-weight:900; ${cStyle}">${t.debitForeign > 0 ? fmtNumCustom(t.debitForeign, dec, thSep) : ''}</td>`;
            } else if (col.id === 'creditForeign') {
                const color = col.numColor || '#f87171';
                html += `<td data-col-id="creditForeign" data-is-num="true" class="num-cell" style="color:${color}; font-weight:900; ${cStyle}">${t.creditForeign > 0 ? fmtNumCustom(t.creditForeign, dec, thSep) : ''}</td>`;
            } else if (col.id === 'balanceForeign') {
                const color = col.numColor || (isPosBalFc ? '#34d399' : '#f87171');
                html += `<td data-col-id="balanceForeign" data-is-num="true" class="num-cell" style="color:${color}; font-weight:900; font-family:'JetBrains Mono',monospace; ${cStyle}" dir="ltr">${t.balanceForeign !== undefined ? fmtNumCustom(t.balanceForeign, dec, thSep) : '-'}</td>`;
            }
        });

        html += '</tr>';
    });

    if (currentTxns && currentTxns.length > LEDGER_PAGE_SIZE) {
        const totalPages = Math.ceil(currentTxns.length / LEDGER_PAGE_SIZE);
        const hasNext = ledgerCurrentPage < totalPages;
        const hasPrev = ledgerCurrentPage > 1;
        
        html += `
            <tr class="pagination-row">
                <td colspan="${visibleCols.length}" style="text-align:center; padding: 25px 0;">
                    <div style="display:inline-flex; align-items:center; gap: 15px; background: rgba(125,125,125,0.08); padding: 8px 16px; border-radius: 20px; border: 1px solid rgba(125,125,125,0.15); box-shadow: 0 4px 12px rgba(0,0,0,0.05); backdrop-filter: blur(4px);">
                        <button onclick="prevLedgerPage()" style="background: ${hasPrev ? 'linear-gradient(135deg, #3b82f6, #2563eb)' : 'rgba(125,125,125,0.15)'}; color: ${hasPrev ? '#fff' : 'inherit'}; border: none; padding: 8px 18px; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: bold; cursor: ${hasPrev ? 'pointer' : 'not-allowed'}; opacity: ${hasPrev ? '1' : '0.5'}; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); transform: scale(1);" onmouseover="if(${hasPrev}) { this.style.transform='scale(1.05) translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(59,130,246,0.4)'; }" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';" ${hasPrev ? '' : 'disabled'}>
                            السابق ◀
                        </button>
                        <span style="font-size: 0.9rem; font-weight: 700; opacity: 0.8; padding: 0 10px;">
                            صفحة <strong style="color: #38bdf8;">${ledgerCurrentPage}</strong> من ${totalPages}
                        </span>
                        <button onclick="nextLedgerPage()" style="background: ${hasNext ? 'linear-gradient(135deg, #3b82f6, #2563eb)' : 'rgba(125,125,125,0.15)'}; color: ${hasNext ? '#fff' : 'inherit'}; border: none; padding: 8px 18px; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: bold; cursor: ${hasNext ? 'pointer' : 'not-allowed'}; opacity: ${hasNext ? '1' : '0.5'}; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); transform: scale(1);" onmouseover="if(${hasNext}) { this.style.transform='scale(1.05) translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(59,130,246,0.4)'; }" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';" ${hasNext ? '' : 'disabled'}>
                            ▶ التالي
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    tbody.innerHTML = html;
}

/* ── Navigate to Original Voucher ── */
window.navigateToOriginalVoucher = function(type, ref) {
    if (!ref || ref === '-' || ref === 'OPENING') {
        showToast('لا يوجد سند مرتبط بـ هذه الحركة', 'warning');
        return;
    }
    let cleanRef = String(ref).replace(/-PAID|-BT\d+|-CH\d+$/i, '').trim();
    let targetUrl = '';
    type = String(type || '').toLowerCase();

    if (type === 'sales' || type === 'invoice' || type === 'direct-sales') targetUrl = `direct-sales.php?id=${cleanRef}`;
    else if (type === 'purchase' || type === 'purchases') targetUrl = `purchase.php?id=${cleanRef}`;
    else if (type === 'receipt' || type === 'single-receipt') targetUrl = `single-cash-receipt.php?id=${cleanRef}`;
    else if (type === 'payment' || type === 'single-payment') targetUrl = `single-cash-payment.php?id=${cleanRef}`;
    else if (type === 'multi-receipt') targetUrl = `single-cash-receipt.php?id=${cleanRef}`;
    else if (type === 'multi-payment') targetUrl = `single-cash-payment.php?id=${cleanRef}`;
    else if (type === 'comprehensive-receipt') targetUrl = `comprehensive-receipt.php?id=${cleanRef}`;
    else if (type === 'comprehensive-payment') targetUrl = `comprehensive-payment.php?id=${cleanRef}`;
    else if (type === 'jv' || type === 'journal' || type === 'adjustment') targetUrl = `journal-voucher.php?id=${cleanRef}`;
    else if (type === 'bank-transfer') targetUrl = `bank-transfer.php?id=${cleanRef}`;
    else if (type === 'check' || type === 'cheque') targetUrl = `checks-cycle.php?id=${cleanRef}`;
    else {
        if (cleanRef.startsWith('INV-') || cleanRef.startsWith('SAL-')) targetUrl = `direct-sales.php?id=${cleanRef}`;
        else if (cleanRef.startsWith('PUR-')) targetUrl = `purchase.php?id=${cleanRef}`;
        else if (cleanRef.startsWith('JV-')) targetUrl = `journal-voucher.php?id=${cleanRef}`;
        else targetUrl = `single-cash-receipt.php?id=${cleanRef}`;
    }

    if (targetUrl) {
        showToast('جاري فتح أصل السند: ' + cleanRef, 'info');
        window.open(targetUrl, '_blank');
    }
};

/* ── Single Click Number Magnification ── */
document.addEventListener('click', function(e) {
    const td = e.target.closest('#recent-table td');
    if (!td) return;
    const isNum = td.dataset.isNum === 'true' || td.classList.contains('num-cell') || /^[-+]?[\d,\.]+\%?$/.test(td.innerText.trim());
    if (isNum) {
        td.classList.toggle('num-magnified');
    }
});

/* ── Split Right-Click Logic (TH: Customizer, TD: Source Voucher Menu) ── */
let currentCtxColId = null;
let currentCtxTxn = null;
let currentCtxTd = null;

document.addEventListener('contextmenu', function(e) {
    const table = e.target.closest('#recent-table');
    if (!table) return;

    e.preventDefault();

    const th = e.target.closest('th');
    const td = e.target.closest('td');
    currentCtxTd = td;
    currentCtxColId = th ? th.dataset.colId : (td ? td.dataset.colId : 'ref');

    let txnType = td ? td.dataset.txnType : null;
    let txnRef = td ? td.dataset.txnRef : null;

    if (!txnType && td && td.parentElement) {
        txnType = td.parentElement.dataset.txnType;
        txnRef = td.parentElement.dataset.txnRef;
    }

    currentCtxTxn = (txnRef && txnRef !== '-') ? { type: txnType, ref: txnRef } : null;

    // 1. IF Right-Clicking on Header Row (th in top row): Open Customizer Window!
    if (th) {
        const menu = document.getElementById('gl-context-menu');
        if (menu) menu.style.display = 'none';
        if (currentCtxColId) openGLColumnModal(currentCtxColId);
        return;
    }

    // 2. IF Right-Clicking on Data Cell / Number (td): Open Source Voucher Menu!
    closeGLColumnModal(); // Close customization window if open

    const menu = document.getElementById('gl-context-menu');
    if (!menu) return;

    // Configure Drilldown button
    const drillEl = document.getElementById('gl-ctx-drilldown');
    if (drillEl) {
        if (currentCtxTxn) {
            drillEl.classList.remove('disabled');
            drillEl.innerHTML = `<span>🔗 الذهاب إلى أصل السند: <strong style="color:#38bdf8;">${currentCtxTxn.ref}</strong></span><span style="font-size:0.75rem; color:#38bdf8;">◀</span>`;
            drillEl.onclick = () => {
                menu.style.display = 'none';
                navigateToOriginalVoucher(currentCtxTxn.type, currentCtxTxn.ref);
            };
        } else {
            drillEl.classList.add('disabled');
            drillEl.innerHTML = `<span>🔗 الذهاب إلى أصل السند (غير محدد)</span><span style="font-size:0.75rem; color:#38bdf8;">◀</span>`;
            drillEl.onclick = null;
        }
    }

    // Configure Zoom toggle
    const zoomEl = document.getElementById('gl-ctx-toggle-zoom');
    if (zoomEl) {
        zoomEl.onclick = () => {
            menu.style.display = 'none';
            if (currentCtxTd) currentCtxTd.classList.toggle('num-magnified');
        };
    }

    // Configure Custom col shortcut
    const customColBtn = document.getElementById('gl-ctx-custom-col');
    if (customColBtn) {
        customColBtn.onclick = () => {
            menu.style.display = 'none';
            if (currentCtxColId) openGLColumnModal(currentCtxColId);
        };
    }

    // Hide col shortcut
    const hideColBtn = document.getElementById('gl-ctx-hide-col');
    if (hideColBtn) {
        hideColBtn.onclick = () => {
            menu.style.display = 'none';
            if (currentCtxColId) hideGLColumn(currentCtxColId);
        };
    }

    let posX = e.clientX;
    let posY = e.clientY;
    const menuWidth = 260;
    const menuHeight = 220;
    if (posX + menuWidth > window.innerWidth) posX = window.innerWidth - menuWidth - 10;
    if (posY + menuHeight > window.innerHeight) posY = window.innerHeight - menuHeight - 10;

    menu.style.left = posX + 'px';
    menu.style.top = posY + 'px';
    menu.style.display = 'block';
});

/* ── Draggable Modal Logic ── */
function initGLDraggableModal() {
    const header = document.getElementById('gl-draggable-header');
    const win = document.getElementById('gl-modal-window');
    if (!header || !win) return;

    let isDragging = false;
    let startX = 0, startY = 0;
    let initialLeft = 0, initialTop = 0;

    header.onmousedown = function(e) {
        if (e.target.closest('.gl-modal-close-btn') || e.target.closest('.gl-modal-ctrl-btn')) return;
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        
        const rect = win.getBoundingClientRect();
        initialLeft = rect.left;
        initialTop = rect.top;

        win.style.transform = 'none';
        win.style.left = initialLeft + 'px';
        win.style.top = initialTop + 'px';

        document.onmousemove = function(ev) {
            if (!isDragging) return;
            const dx = ev.clientX - startX;
            const dy = ev.clientY - startY;

            let newLeft = initialLeft + dx;
            let newTop = initialTop + dy;

            if (newLeft < 10) newLeft = 10;
            if (newTop < 10) newTop = 10;
            if (newLeft + rect.width > window.innerWidth - 10) newLeft = window.innerWidth - rect.width - 10;
            if (newTop + rect.height > window.innerHeight - 10) newTop = window.innerHeight - rect.height - 10;

            win.style.left = newLeft + 'px';
            win.style.top = newTop + 'px';
        };

        document.onmouseup = function() {
            isDragging = false;
            document.onmousemove = null;
            document.onmouseup = null;
        };
    };
}

/* ── Live Preview Generator ── */
window.updateGLModalLivePreview = function() {
    const colId = document.getElementById('gl-modal-col-id').value;
    const col = activeGLColumnSettings.find(c => c.id === colId) || {};
    
    const hBg = document.getElementById('gl-cfg-header-bg').value;
    const hColor = document.getElementById('gl-cfg-header-color').value;
    const cBg = document.getElementById('gl-cfg-cell-bg').value;
    const cColor = document.getElementById('gl-cfg-cell-color').value;
    const font = document.getElementById('gl-cfg-font').value;
    const weight = document.getElementById('gl-cfg-weight').value;
    const size = document.getElementById('gl-cfg-size').value || '0.85rem';
    const align = document.getElementById('gl-cfg-align').value;
    const decimals = parseInt(document.getElementById('gl-cfg-decimals').value, 10);
    const thSep = document.getElementById('gl-cfg-thousands').value;
    const numColor = document.getElementById('gl-cfg-num-color').value;

    const prevTh = document.getElementById('gl-prev-th');
    const prevTd = document.getElementById('gl-prev-td');

    if (prevTh) {
        prevTh.innerText = col.label || 'اسم العمود';
        prevTh.style.background = hBg;
        prevTh.style.color = hColor;
        prevTh.style.fontFamily = font + ', sans-serif';
        prevTh.style.fontWeight = weight;
        prevTh.style.textAlign = align;
    }

    if (prevTd) {
        prevTd.innerText = col.isNum ? fmtNumCustom(12500, decimals, thSep) : (col.label + ' نموذج');
        prevTd.style.background = cBg;
        prevTd.style.color = col.isNum ? numColor : cColor;
        prevTd.style.fontFamily = font + ', sans-serif';
        prevTd.style.fontWeight = weight;
        prevTd.style.fontSize = size;
        prevTd.style.textAlign = align;
    }
};

/* ── Modal Customizer Functions & Window Controls ── */
window.toggleMinimizeGLModal = function() {
    const win = document.getElementById('gl-modal-window');
    if (!win) return;
    win.classList.remove('maximized');
    win.classList.toggle('minimized');
    if (win.classList.contains('minimized')) {
        showToast('تم تصغير النافذة (انقر 🗕 للفتح)', 'info');
    }
};

window.toggleMaximizeGLModal = function() {
    const win = document.getElementById('gl-modal-window');
    if (!win) return;
    win.classList.remove('minimized');
    win.classList.toggle('maximized');
    if (win.classList.contains('maximized')) {
        win.style.left = '50%';
        win.style.top = '50%';
        win.style.transform = 'translate(-50%, -50%)';
    }
};

window.positionGLModal = function(pos) {
    const win = document.getElementById('gl-modal-window');
    if (!win) return;

    win.classList.remove('minimized');
    win.classList.remove('maximized');
    win.style.transform = 'none';

    const rect = win.getBoundingClientRect();
    const margin = 20;

    if (pos === 'left') {
        win.style.left = margin + 'px';
        win.style.top = Math.max(10, (window.innerHeight / 2 - rect.height / 2)) + 'px';
        showToast('تم نقل النافذة للجهة اليسرى', 'info');
    } else if (pos === 'right') {
        win.style.left = Math.max(10, (window.innerWidth - rect.width - margin)) + 'px';
        win.style.top = Math.max(10, (window.innerHeight / 2 - rect.height / 2)) + 'px';
        showToast('تم نقل النافذة للجهة اليمنى', 'info');
    } else if (pos === 'center') {
        win.style.left = '50%';
        win.style.top = '50%';
        win.style.transform = 'translate(-50%, -50%)';
        showToast('تم تمركز النافذة في المنتصف', 'info');
    }
};

/* ── Render Master Column Selector List ── */
window.renderGLModalColumnsList = function(selectedColId) {
    const container = document.getElementById('gl-modal-columns-list');
    if (!container) return;

    let html = '';
    activeGLColumnSettings.forEach(c => {
        const isSelected = c.id === selectedColId;
        const isVisible = !c.hidden;
        
        html += `
            <div class="gl-col-chip ${isSelected ? 'active' : ''}" onclick="selectGLColumnInModal('${c.id}')" title="انقر لتنسيق هذا العمود">
                <input type="checkbox" ${isVisible ? 'checked' : ''} onclick="event.stopPropagation(); toggleGLColumnVisibility('${c.id}', this.checked);" title="إظهار / إخفاء العمود">
                <span>${c.label}</span>
            </div>
        `;
    });

    container.innerHTML = html;
};

window.toggleGLColumnVisibility = function(colId, isVisible) {
    const col = activeGLColumnSettings.find(c => c.id === colId);
    if (col) {
        col.hidden = !isVisible;
        saveGLColumnSettings();
        if (typeof runReport === 'function') runReport();
        renderGLModalColumnsList(document.getElementById('gl-modal-col-id').value);
        showToast(isVisible ? `تم إظهار العمود: ${col.label}` : `تم إخفاء العمود: ${col.label}`, isVisible ? 'success' : 'info');
    }
};

window.selectGLColumnInModal = function(colId) {
    const col = activeGLColumnSettings.find(c => c.id === colId);
    if (!col) return;

    document.getElementById('gl-modal-col-id').value = col.id;
    document.getElementById('gl-modal-col-name').innerText = col.label;

    document.getElementById('gl-cfg-header-bg').value = col.headerBg || '#0f172a';
    document.getElementById('gl-cfg-header-color').value = col.headerColor || '#c084fc';
    document.getElementById('gl-cfg-cell-bg').value = col.cellBg || '#0f172a';
    document.getElementById('gl-cfg-cell-color').value = col.cellColor || '#f8fafc';
    document.getElementById('gl-cfg-font').value = col.fontFamily || 'Tajawal';
    document.getElementById('gl-cfg-weight').value = col.fontWeight || '700';
    document.getElementById('gl-cfg-size').value = col.fontSize || '0.85rem';
    if (document.getElementById('gl-cfg-size-select')) document.getElementById('gl-cfg-size-select').value = col.fontSize || '0.85rem';
    document.getElementById('gl-cfg-align').value = col.align || 'right';
    document.getElementById('gl-cfg-decimals').value = col.decimals !== undefined ? col.decimals : 2;
    if (document.getElementById('gl-cfg-thousands')) document.getElementById('gl-cfg-thousands').value = col.thousandsStyle || 'comma';
    document.getElementById('gl-cfg-num-color').value = col.numColor || (col.id.includes('debit') ? '#34d399' : col.id.includes('credit') ? '#f87171' : '#34d399');

    renderGLModalColumnsList(col.id);
    updateGLModalLivePreview();
};

window.openGLColumnModal = function(colId) {
    const col = activeGLColumnSettings.find(c => c.id === colId) || activeGLColumnSettings[0];
    if (!col) return;

    const win = document.getElementById('gl-modal-window');
    if (win) {
        win.classList.remove('minimized');
        win.classList.remove('maximized');
        win.style.left = '50%';
        win.style.top = '50%';
        win.style.transform = 'translate(-50%, -50%)';
    }

    selectGLColumnInModal(col.id);

    const zoomBtn = document.getElementById('gl-modal-action-zoom');
    if (zoomBtn) {
        zoomBtn.onclick = () => {
            if (currentCtxTd) currentCtxTd.classList.toggle('num-magnified');
            else showToast('انقر على أي خلية لتكبير رقمها للمراجعة', 'info');
        };
    }

    const hideBtn = document.getElementById('gl-modal-action-hide');
    if (hideBtn) {
        hideBtn.onclick = () => {
            const activeId = document.getElementById('gl-modal-col-id').value;
            hideGLColumn(activeId);
            renderGLModalColumnsList(activeId);
        };
    }

    const moveLBtn = document.getElementById('gl-modal-action-move-left');
    if (moveLBtn) {
        moveLBtn.onclick = () => {
            const activeId = document.getElementById('gl-modal-col-id').value;
            moveGLColumn(activeId, 1);
            renderGLModalColumnsList(activeId);
        };
    }

    const moveRBtn = document.getElementById('gl-modal-action-move-right');
    if (moveRBtn) {
        moveRBtn.onclick = () => {
            const activeId = document.getElementById('gl-modal-col-id').value;
            moveGLColumn(activeId, -1);
            renderGLModalColumnsList(activeId);
        };
    }

    const overlay = document.getElementById('gl-column-modal');
    if (overlay) overlay.style.display = 'block';
    
    initGLDraggableModal();
};

window.closeGLColumnModal = function() {
    const overlay = document.getElementById('gl-column-modal');
    if (overlay) overlay.style.display = 'none';
    const win = document.getElementById('gl-modal-window');
    if (win) {
        win.classList.remove('minimized');
        win.classList.remove('maximized');
    }
};

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('gl-column-modal');
        if (modal && modal.style.display !== 'none') {
            closeGLColumnModal();
        }
    }
});

window.saveGLColumnCustomization = function() {
    const colId = document.getElementById('gl-modal-col-id').value;
    const col = activeGLColumnSettings.find(c => c.id === colId);
    if (!col) return;

    col.headerBg = document.getElementById('gl-cfg-header-bg').value;
    col.headerColor = document.getElementById('gl-cfg-header-color').value;
    col.cellBg = document.getElementById('gl-cfg-cell-bg').value;
    col.cellColor = document.getElementById('gl-cfg-cell-color').value;
    col.fontFamily = document.getElementById('gl-cfg-font').value;
    col.fontWeight = document.getElementById('gl-cfg-weight').value;
    col.fontSize = document.getElementById('gl-cfg-size').value;
    col.align = document.getElementById('gl-cfg-align').value;
    col.decimals = parseInt(document.getElementById('gl-cfg-decimals').value, 10);
    if (document.getElementById('gl-cfg-thousands')) col.thousandsStyle = document.getElementById('gl-cfg-thousands').value;
    col.numColor = document.getElementById('gl-cfg-num-color').value;

    saveGLColumnSettings();
    closeGLColumnModal();
    if (typeof runReport === 'function') runReport();
    showToast(`تم حفظ تخصيص العمود: ${col.label}`, 'success');
};

window.hideGLColumn = function(colId) {
    const col = activeGLColumnSettings.find(c => c.id === colId);
    if (col) {
        col.hidden = true;
        saveGLColumnSettings();
        if (typeof runReport === 'function') runReport();
        showToast(`تم إخفاء العمود: ${col.label}`, 'info');
    }
};

window.unhideGLColumn = function(colId) {
    const col = activeGLColumnSettings.find(c => c.id === colId);
    if (col) {
        col.hidden = false;
        saveGLColumnSettings();
        if (typeof runReport === 'function') runReport();
        showToast(`تم إظهار العمود: ${col.label}`, 'success');
    }
};

window.moveGLColumn = function(colId, dir) {
    const idx = activeGLColumnSettings.findIndex(c => c.id === colId);
    if (idx < 0) return;
    const newIdx = idx + dir;
    if (newIdx >= 0 && newIdx < activeGLColumnSettings.length) {
        const item = activeGLColumnSettings.splice(idx, 1)[0];
        activeGLColumnSettings.splice(newIdx, 0, item);
        saveGLColumnSettings();
        if (typeof runReport === 'function') runReport();
        showToast('تم تغيير ترتيب العمود', 'info');
    }
};

function getAccountName(accVal) {
    if (!accVal) return '';
    const idLower = String(accVal).toLowerCase();
    if (ACCOUNT_NAME_CACHE.has(idLower)) return ACCOUNT_NAME_CACHE.get(idLower);
    const cleanId = idLower.replace(/^[a-zA-Z]+\s*[-_:\/]\s*/, '').trim();
    if (ACCOUNT_NAME_CACHE.has(cleanId)) return ACCOUNT_NAME_CACHE.get(cleanId);
    
    // Hardcoded fallbacks
    if (idLower === 'safe-001' || accVal === 'safe-001') return 'الخزينة الرئيسية';
    if (idLower === 'bank-001' || accVal === 'bank-001') return 'البنك الرئيسي';
    if (idLower === 'bankok' || accVal === 'bankok') return 'بنكك (Bankak)';
    if (idLower === 'fawry' || accVal === 'fawry') return 'فوري (Fawry)';
    if (idLower === 'o-cash' || accVal === 'o-cash') return 'أوكاش (O-Cash)';
    if (idLower === 'other' || accVal === 'other') return 'أخرى';
    if (idLower === 'exp-purchases' || accVal === 'exp-purchases') return 'المشتريات';
    if (idLower === 'revenue-sales' || accVal === 'revenue-sales') return 'إيرادات المبيعات';
    if (idLower === 'cost-purchases' || accVal === 'cost-purchases') return 'تكلفة المشتريات (مخزون)';
    if (idLower === 'act-inventory' || accVal === 'act-inventory') return 'حساب المخزون';
    if (idLower === 'exp-inventory-adjustments' || accVal === 'exp-inventory-adjustments') return 'تسويات المخزون';
    if (idLower === 'discount-permitted' || accVal === 'discount-permitted') return 'خصم مسموح به';
    if (idLower === 'discount-received' || accVal === 'discount-received') return 'خصم مكتسب';
    if (idLower === 'vat-collected' || accVal === 'vat-collected') return 'ضريبة مبيعات';
    if (idLower === 'vat-paid' || accVal === 'vat-paid') return 'ضريبة مشتريات';
    if (idLower === 'cheques-receivable' || accVal === 'cheques-receivable') return 'شيكات برسم التحصيل';
    
    return accVal; // return ID if name not found
}

async function prepareLedgerPreviewData() {
    if (!currentTxns || currentTxns.length === 0) {
        if (typeof runReport === 'function') {
            runReport();
        }
    }

    let sourceTxns = currentTxns || [];

    // Clone and sort chronologically (Oldest first) for Ledger Preview & Print
    let previewTxns = sourceTxns.slice().sort((a, b) => {
        const da = (a.date || '') + ' ' + (a.time || '00:00');
        const db = (b.date || '') + ' ' + (b.time || '00:00');
        return da.localeCompare(db);
    });

    // Calculate Running Cumulative Balance for each transaction
    let runningBalance = 0;
    let runningBalanceFc = 0;
    
    // Check if we are filtering by a specific account (assuming filterAccount is in scope or we can check the DOM)
    const accFilter = document.getElementById('filter-account') ? document.getElementById('filter-account').value : 'ALL';

    previewTxns = previewTxns.map(t => {
        const dr = parseFloat(t.debit || t.dr || 0);
        const cr = parseFloat(t.credit || t.cr || 0);
        const drF = parseFloat(t.debitForeign || t.dr_foreign || 0);
        const crF = parseFloat(t.creditForeign || t.cr_foreign || 0);
        
        if (accFilter !== 'ALL') {
            runningBalance += (dr - cr);
            runningBalanceFc += (drF - crF);
        } else {
            runningBalance = undefined;
            runningBalanceFc = undefined;
        }

        return Object.assign({}, t, {
            debit: dr,
            credit: cr,
            debitForeign: drF,
            creditForeign: crF,
            balance: runningBalance,
            balanceForeign: runningBalanceFc,
            statement: t.statement || t.description || t.desc || t.notes || '-',
            ref: t.ref || t.doc_no || t.voucher_no || t.id || '-',
            counterAccount: getAccountName(t.counterAccount || t.accountName || t.account) || '-',
            typeLabel: t.typeLabel || t.type || '-',
            exchangeRate: t.exchangeRate || 1,
            additionalNote: t.additionalNote || t.notes || t.costCenter || ''
        });
    });

    window.currentTxns = currentTxns;

    const config = {
        filterAccount: document.getElementById('filter-account') ? document.getElementById('filter-account').value : 'ALL',
        dateMode: typeof currentDateMode !== 'undefined' ? currentDateMode : 'all',
        dateFrom: document.getElementById('filter-date-from') ? document.getElementById('filter-date-from').value : '',
        dateTo: document.getElementById('filter-date-to') ? document.getElementById('filter-date-to').value : '',
        filterCurrency: document.getElementById('filter-currency') ? document.getElementById('filter-currency').value : 'ALL',
        filterType: document.getElementById('filter-type') ? document.getElementById('filter-type').value : 'ALL',
        filterCostCenter: document.getElementById('filter-costcenter') ? document.getElementById('filter-costcenter').value : 'ALL',
        filterPosted: document.getElementById('filter-posted') ? document.getElementById('filter-posted').value : 'ALL',
        filterSmart: document.getElementById('filter-smart') ? document.getElementById('filter-smart').value : '',
        accountName: document.getElementById('chip-name') ? document.getElementById('chip-name').innerText : 'دفتر الأستاذ العام'
    };

    const totals = {
        openingBalance: window.currentOpeningBalance || 0,
        totalDebit: document.getElementById('launcher-total-debit') ? document.getElementById('launcher-total-debit').innerText : '0.00',
        totalCredit: document.getElementById('launcher-total-credit') ? document.getElementById('launcher-total-credit').innerText : '0.00',
        totalBalance: document.getElementById('launcher-total-balance') ? document.getElementById('launcher-total-balance').innerText : '0.00'
    };

    const logoImg = document.getElementById('company-logo');
    if (logoImg && logoImg.src) {
        localStorage.setItem('company_logo_base64', logoImg.src);
    }

    try {
        localStorage.setItem('preview_ledger_config', JSON.stringify(config));
        localStorage.setItem('preview_ledger_totals', JSON.stringify(totals));
        
        if (typeof localforage !== 'undefined') {
            await localforage.setItem('preview_ledger_data', previewTxns);
            await localforage.setItem('preview_table_data', previewTxns);
        }
        
        try {
            localStorage.setItem('preview_ledger_data', JSON.stringify(previewTxns));
            localStorage.setItem('preview_table_data', JSON.stringify(previewTxns));
        } catch(quotaErr) {
            console.warn('LocalStorage quota limit reached, stored in IndexedDB/localforage:', quotaErr);
        }
    } catch(e) {
        console.error('Storage Error:', e);
    }
}

async function openPreviewDesign() {
    try {
        await prepareLedgerPreviewData();
        window.location.href = 'preview-ledger.php';
    } catch (e) {
        console.error(e);
        alert('حدث خطأ غير متوقع: ' + e.message);
    }
}

function renderLedgerPresets() {
    const container = document.getElementById('presets-container');
    if (!container) return;
    container.innerHTML = '';

    const PRESETS_STORAGE_KEY = 'ledger_preview_presets_v7';
    let presets = [];
    try { presets = JSON.parse(localStorage.getItem(PRESETS_STORAGE_KEY) || '[]'); } catch(e){}

    if (!Array.isArray(presets) || presets.length === 0) {
        const defaultIcons = ['💎 افتراضي 1', '⭐ افتراضي 2', '🎨 افتراضي 3', '🔥 افتراضي 4', '✨ نموذج 1'];
        presets = defaultIcons.map(name => ({ name, showInRibbon: true }));
    }

    presets.slice(0, 5).forEach((p, idx) => {
        if (p.showInRibbon === false) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'preset-ribbon-btn';
        btn.style.cssText = `
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25);
            color: #f1f5f9; padding: 5px 14px; border-radius: 18px;
            font-family: inherit; font-size: 0.85rem; font-weight: 800;
            cursor: pointer; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 6px rgba(0,0,0,0.18);
        `;
        btn.innerHTML = p.name || ('افتراضي ' + (idx + 1));
        btn.onmouseover = () => {
            btn.style.background = 'linear-gradient(135deg, rgba(56,189,248,0.3), rgba(2,132,199,0.4))';
            btn.style.borderColor = '#38bdf8';
            btn.style.transform = 'translateY(-2px) scale(1.03)';
            btn.style.boxShadow = '0 6px 16px rgba(56,189,248,0.4)';
        };
        btn.onmouseout = () => {
            btn.style.background = 'rgba(255,255,255,0.08)';
            btn.style.borderColor = 'rgba(255,255,255,0.25)';
            btn.style.transform = 'translateY(0) scale(1)';
            btn.style.boxShadow = '0 2px 6px rgba(0,0,0,0.18)';
        };
        btn.onclick = () => openPresetModal(idx, p.name || ('افتراضي ' + (idx + 1)));
        container.appendChild(btn);
    });
}
window.renderLedgerPresets = renderLedgerPresets;

async function openPresetModal(presetIndex, presetName) {
    try {
        await prepareLedgerPreviewData();
    } catch (e) {
        console.error(e);
        alert('حدث خطأ غير متوقع: ' + e.message);
        return;
    }

    // Create Modal Backdrop if not present
    let modal = document.getElementById('preset-preview-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'preset-preview-modal';
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(12px);
            z-index: 999999; display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 10px; animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        `;
        document.body.appendChild(modal);
    }

    modal.innerHTML = `
        <div style="background: #1e293b; border: 1px solid rgba(56,189,248,0.3); border-radius: 16px; width: 99%; max-width: 100%; height: 98vh; display: flex; flex-direction: column; box-shadow: 0 25px 60px rgba(0,0,0,0.5); overflow: hidden;">
            <!-- Modal Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; background: linear-gradient(135deg, #0f172a, #1e293b); border-bottom: 2px solid #ca8a04;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="background: linear-gradient(135deg, #ca8a04, #854d0e); color: #fff; padding: 4px 14px; border-radius: 16px; font-weight: 900; font-size: 0.95rem;">${presetName}</span>
                    <span style="color: #f8fafc; font-weight: 800; font-size: 1rem;">📄 معاينة وطباعة التقرير بالتصميم المخصص (دفتر الأستاذ)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" onclick="document.getElementById('preset-modal-iframe').contentWindow.executePrintWithPermission()" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; border: none; padding: 7px 18px; border-radius: 20px; font-weight: 900; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 3px 10px rgba(2,132,199,0.4);">
                        🖨️ طباعة
                    </button>

                    <button type="button" onclick="document.getElementById('preset-preview-modal').style.display='none'" style="background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.4); padding: 7px 16px; border-radius: 20px; font-weight: 900; font-size: 0.9rem; cursor: pointer;">
                        ❌ Close
                    </button>
                </div>
            </div>
            <!-- Iframe Container -->
            <div style="flex: 1; background: #fff; position: relative;">
                <iframe id="preset-modal-iframe" src="preview-ledger.php?presetIndex=${presetIndex}&hideRibbon=1" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};
window.openPresetModal = openPresetModal;

// Attach event listeners to filters to auto-run
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.filter-input');
    inputs.forEach(inp => {
        inp.addEventListener('change', runReport);
        if (inp.id === 'filter-smart') {
            inp.addEventListener('keyup', runReport);
        }
    });
    
    renderLedgerPresets();

    const token = localStorage.getItem('erp_token') || '';
    const headers = {};
    if (token) headers['Authorization'] = 'Bearer ' + token;
    fetch('api_index.php/report-layout?reportKey=ledger_preview_presets_v7', { headers: headers })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && Array.isArray(data.layout) && data.layout.length > 0) {
                localStorage.setItem('ledger_preview_presets_v7', JSON.stringify(data.layout));
                renderLedgerPresets();
            }
        })
        .catch(e => {});

    buildAccountsList();
    runReport();
    setTimeout(() => {
        runReport();
    }, 300);
});

window.addEventListener('storage', function(e) {
    if (e.key === 'ledger_preview_presets_v7' || e.key === 'ledger_preview_presets_v1') {
        if (typeof renderLedgerPresets === 'function') {
            renderLedgerPresets();
        }
    }
});

function initGeneralLedgerResizers() {
    const table = document.getElementById('recent-table');
    if (!table) return;
    const resizers = table.querySelectorAll('.resizer');

    resizers.forEach(resizer => {
        let startX = 0;
        let startWidth = 0;
        let th = null;

        const mouseDownHandler = function(e) {
            e.stopPropagation();
            e.preventDefault();
            th = resizer.closest('th');
            if (!th) return;
            startX = e.clientX;
            const styles = window.getComputedStyle(th);
            startWidth = parseInt(styles.width, 10) || 120;

            resizer.classList.add('resizing');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';

            const mouseMoveHandler = function(ev) {
                const dx = (startX - ev.clientX);
                const newWidth = Math.max(50, startWidth + dx);
                th.style.width = newWidth + 'px';
                th.style.minWidth = newWidth + 'px';
                th.style.maxWidth = newWidth + 'px';
            };

            const mouseUpHandler = function() {
                resizer.classList.remove('resizing');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
            };

            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
        };

        const dblClickHandler = function(e) {
            e.stopPropagation();
            th = resizer.closest('th');
            if (!th) return;
            th.style.width = 'auto';
            th.style.minWidth = 'max-content';
            th.style.maxWidth = 'none';
        };

        resizer.removeEventListener('mousedown', resizer._mouseDown);
        resizer.removeEventListener('dblclick', resizer._dblClick);
        resizer._mouseDown = mouseDownHandler;
        resizer._dblClick = dblClickHandler;
        resizer.addEventListener('mousedown', mouseDownHandler);
        resizer.addEventListener('dblclick', dblClickHandler);
    });
}
document.addEventListener('DOMContentLoaded', initGeneralLedgerResizers);
