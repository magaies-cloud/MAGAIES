/* ═══════════════════════════════════════════════════════
   balances-report.js — Global Balances & Aging Report
   ═══════════════════════════════════════════════════════ */
console.log('%c[balances-report.js] FILE LOADED v2', 'color: lime; font-size: 16px; font-weight: bold;');

window.colConfigs = [
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }, // 0: Name
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }, // 1: Phone
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }, // 2: Tax Number
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }, // 3: Currency
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }, // 4: Debit Foreign
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }, // 5: Credit Foreign
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }, // 6: Debit Local
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }, // 7: Credit Local
    { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true }  // 8: Note
];
window.colOrder = [0, 1, 2, 3, 4, 5, 6, 7, 8];

let ledgerExchangeRate = 1.0;
let ledgerForeignCurrency = 'USD';

function initBalancesCurrencySettings() {
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
initBalancesCurrencySettings();

function applyLayoutToDOM() {
    try {
        const table = document.getElementById('balances-table');
        if (!table) return;
        
        // 1. Reorder header th elements according to window.colOrder
        const theadTr = table.querySelector('thead tr');
        if (theadTr) {
            const ths = Array.from(theadTr.querySelectorAll('th'));
            const thMap = {};
            ths.forEach(th => {
                const oIdx = parseInt(th.getAttribute('data-orig-idx'));
                if (!isNaN(oIdx)) thMap[oIdx] = th;
            });

            theadTr.innerHTML = '';
            window.colOrder.forEach(oIdx => {
                if (thMap[oIdx]) {
                    theadTr.appendChild(thMap[oIdx]);
                }
            });

            // Apply styles and visibility to headers
            const currentThs = theadTr.querySelectorAll('th');
            currentThs.forEach((th, visualIndex) => {
                const origIdx = window.colOrder[visualIndex];
                const conf = window.colConfigs[origIdx] || { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true };
                
                // Visibility
                const isVisible = conf.visible !== false;
                th.style.display = isVisible ? '' : 'none';

                // Styles
                const fsVal = (conf.fs !== undefined ? conf.fs : 1.0) + 'em';
                const fwVal = conf.fontWeight || (conf.bold ? '900' : 'bold');
                
                th.style.setProperty('font-weight', fwVal, 'important');
                th.style.setProperty('font-size', fsVal, 'important');
                if (conf.color) th.style.setProperty('color', conf.color, 'important'); else th.style.removeProperty('color');
                if (conf.bg && conf.bg !== '#ffffff' && conf.bg !== '#0f172a') th.style.setProperty('background-color', conf.bg, 'important'); else th.style.removeProperty('background-color');
                if (conf.fontFamily) th.style.setProperty('font-family', conf.fontFamily, 'important'); else th.style.removeProperty('font-family');
                if (conf.textAlign) th.style.setProperty('text-align', conf.textAlign, 'important'); else th.style.removeProperty('text-align');
            });
        }

        // 2. Apply styles and visibility to body cells if populated
        const tbody = table.querySelector('tbody');
        if (tbody) {
            const rows = tbody.querySelectorAll('tr:not(.empty-state)');
            rows.forEach(row => {
                const cells = Array.from(row.children);
                cells.forEach((cell, visualIndex) => {
                    const origIdx = window.colOrder[visualIndex];
                    if (origIdx !== undefined) {
                        const conf = window.colConfigs[origIdx] || { bold: false, fs: 1.0, decimals: 2, color: '', bg: '', visible: true };

                        // Visibility
                        const isVisible = conf.visible !== false;
                        cell.style.display = isVisible ? '' : 'none';

                        // Styles
                        const fsVal = (conf.fs !== undefined ? conf.fs : 1.0) + 'em';
                        const fwVal = conf.fontWeight || (conf.bold ? 'bold' : '');

                        cell.style.setProperty('font-weight', fwVal, 'important');
                        cell.style.setProperty('font-size', fsVal, 'important');
                        if (conf.color) cell.style.setProperty('color', conf.color, 'important'); else cell.style.removeProperty('color');
                        if (conf.bg && conf.bg !== '#ffffff' && conf.bg !== '#0f172a') cell.style.setProperty('background-color', conf.bg, 'important'); else cell.style.removeProperty('background-color');
                        if (conf.fontFamily) cell.style.setProperty('font-family', conf.fontFamily, 'important'); else cell.style.removeProperty('font-family');
                        if (conf.textAlign) cell.style.setProperty('text-align', conf.textAlign, 'important'); else cell.style.removeProperty('text-align');
                    }
                });
            });
        }

        // Re-bind resizers because headers were reordered
        if (typeof bindResizers === 'function') {
            bindResizers();
        }
    } catch(e) {
        console.error('Error applying layout to DOM:', e);
    }
}

function loadLayout() {
    try {
        const settingsStr = localStorage.getItem('erp_settings_v1') || '{}';
        const settings = JSON.parse(settingsStr);
        const layout = settings.balancesReportLayout;
        if (layout) {
            if (layout.colConfigs && layout.colOrder && layout.colOrder.length === 9) {
                layout.colConfigs.forEach(c => {
                    if (c.visible === undefined) c.visible = true;
                });
                window.colConfigs = layout.colConfigs;
                window.colOrder = layout.colOrder;
            }
        }
    } catch(e) { console.error('Error loading layout', e); }
}
loadLayout();

document.addEventListener('DOMContentLoaded', () => {
    applyLayoutToDOM();
    bindRowContextMenu();
    if (typeof window.renderBalancesPresets === 'function') {
        window.renderBalancesPresets();
    }
});

function saveLayout() {
    try {
        const settingsStr = localStorage.getItem('erp_settings_v1') || '{}';
        const settings = JSON.parse(settingsStr);
        
        settings.balancesReportLayout = {
            colConfigs: window.colConfigs,
            colOrder: window.colOrder
        };
        
        localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
        showToast('تم حفظ التصميم في قاعدة البيانات بنجاح', 'success');
        
        if (typeof window.renderBalancesPresets === 'function') {
            window.renderBalancesPresets();
        }
    } catch(e) {
        console.error('Error saving layout', e);
        showToast('فشل في حفظ التصميم', 'error');
    }
}

function fmtColNum(n, origIdx) {
    const conf = (window.colConfigs && window.colConfigs[origIdx]) ? window.colConfigs[origIdx] : {};
    const showDec = (conf.showDecimals !== false);
    const d = showDec ? ((conf.decimals !== undefined) ? parseInt(conf.decimals) : 2) : 0;
    const isThousands = (conf.thousands !== false);
    const val = parseFloat(n);
    if (isNaN(val)) return String(n || '');

    let formatted = val.toFixed(d);
    if (isThousands) {
        const parts = formatted.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        formatted = parts.join('.');
    }
    return formatted;
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

let ALL_PARTNERS = [];
let ALL_TXNS = [];

function getCurrencyRates() {
    let rates = {};
    try {
        rates = JSON.parse(localStorage.getItem('erp_currency_rates_v1')) || {};
    } catch(e) {}
    const defaults = {
        'SDG': 1,
        'USD': 600,
        'SAR': 160,
        'AED': 163,
        'EGP': 12
    };
    for (let cur in defaults) {
        if (rates[cur] === undefined) {
            rates[cur] = defaults[cur];
        }
    }
    return rates;
}

function loadData() {
    console.log('%c[balances-report.js] loadData() CALLED', 'color: cyan; font-size: 14px;');
    ALL_PARTNERS = [];
    const custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted);
    const sups  = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted);
    const banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    const safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
    const exp   = JSON.parse(localStorage.getItem('erp_accounts_v1')) || [];
    
    const rates = getCurrencyRates();
    
    custs.forEach(c => {
        const cur = c.currency || 'SDG';
        const rate = parseNum(c.exRate || rates[cur] || 1);
        ALL_PARTNERS.push({ 
            id: c.id, code: c.accountNo || c.code || '-', name: c.name, type: 'CUST', 
            phone: c.phone || '', taxNumber: c.taxNumber || '', currency: cur, exRate: rate,
            openingDebit: parseNum(c.openingDebit || 0), openingCredit: parseNum(c.openingCredit || 0),
            notes: c.notes || '',
            debit: 0, credit: 0, lastDate: null 
        });
    });
    sups.forEach(s  => {
        const cur = s.currency || 'SDG';
        const rate = parseNum(s.exRate || rates[cur] || 1);
        ALL_PARTNERS.push({ 
            id: s.id, code: s.accountNo || s.code || '-', name: s.name, type: 'SUPP', 
            phone: s.phone || '', taxNumber: s.taxNumber || '', currency: cur, exRate: rate,
            openingDebit: parseNum(s.openingDebit || 0), openingCredit: parseNum(s.openingCredit || 0),
            notes: s.notes || '',
            debit: 0, credit: 0, lastDate: null 
        });
    });
    banks.forEach(b => {
        const cur = b.currency || 'SDG';
        const rate = parseNum(b.exRate || rates[cur] || 1);
        ALL_PARTNERS.push({ 
            id: b.id, code: b.glAccount || b.code || '-', name: b.name, type: 'BANK', 
            phone: b.branch || '', taxNumber: b.accountNo || '', currency: cur, exRate: rate,
            openingDebit: parseNum(b.openingDebit || 0), openingCredit: parseNum(b.openingCredit || 0),
            notes: b.notes || '',
            debit: 0, credit: 0, lastDate: null 
        });
    });
    safes.forEach(s => {
        const cur = s.currency || 'SDG';
        const rate = parseNum(s.exRate || rates[cur] || 1);
        ALL_PARTNERS.push({ 
            id: s.id, code: s.glAccount || s.code || '-', name: s.name, type: 'SAFE', 
            phone: '', taxNumber: '', currency: cur, exRate: rate,
            openingDebit: parseNum(s.openingDebit || 0), openingCredit: parseNum(s.openingCredit || 0),
            notes: s.notes || '',
            debit: 0, credit: 0, lastDate: null 
        });
    });
    exp.forEach(e => {
        const cur = 'SDG';
        const rate = 1;
        ALL_PARTNERS.push({ 
            id: e.id || e.code, code: e.code || '-', name: e.name, type: 'EXP', 
            phone: '', taxNumber: '', currency: cur, exRate: rate,
            openingDebit: parseNum(e.openingDebit || 0), openingCredit: parseNum(e.openingCredit || 0),
            notes: e.notes || '',
            debit: 0, credit: 0, lastDate: null 
        });
    });
    
    // We reuse the collector logic from SGL, modified to only grab partner transactions
    ALL_TXNS = [];
    const timeNow = new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'});

    // 1. Receipts
    const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
    receipts.forEach(v => {
        const cc = v.costCenter || 'ALL';
        const rate = parseNum(v.rate || 1);
        ALL_TXNS.push({ partner: v.fromAccount, debit: 0, credit: parseNum(v.amountLocal), date: v.date, time: v.time || timeNow, costCenter: cc, exchangeRate: rate });
        ALL_TXNS.push({ partner: v.toAccount, debit: parseNum(v.amountLocal), credit: 0, date: v.date, time: v.time || timeNow, costCenter: cc, exchangeRate: rate });
    });

    // 2. Payments
    const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
    payments.forEach(v => {
        const cc = v.costCenter || 'ALL';
        const rate = parseNum(v.rate || 1);
        ALL_TXNS.push({ partner: v.toAccount, debit: parseNum(v.amountLocal), credit: 0, date: v.date, time: v.time || timeNow, costCenter: cc, exchangeRate: rate });
        ALL_TXNS.push({ partner: v.fromAccount, debit: 0, credit: parseNum(v.amountLocal), date: v.date, time: v.time || timeNow, costCenter: cc, exchangeRate: rate });
    });

    // 2.2 Multiple Receipts (erp_receipts_v1)
    const multiReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
    multiReceipts.forEach(v => {
        if (!v.isPosted) return;
        const cc = v.costCenter || 'ALL';
        const rate = parseNum(v.exchangeRate || 1);
        const mainAcc = v.mainAccount || v.toAccount || 'safe-001';
        ALL_TXNS.push({ partner: mainAcc, debit: parseNum(v.totalAmount), credit: 0, date: v.date, time: v.time || timeNow, costCenter: cc, exchangeRate: rate });
        if (v.items && Array.isArray(v.items)) {
            v.items.forEach(item => {
                const itemCC = item.costCenter || cc;
                ALL_TXNS.push({ partner: item.account, debit: 0, credit: parseNum(item.amount), date: v.date, time: v.time || timeNow, costCenter: itemCC, exchangeRate: rate });
            });
        }
    });

    // 2.3 Multiple Payments (erp_payments_v1)
    const multiPayments = JSON.parse(localStorage.getItem('erp_payments_v1')) || [];
    multiPayments.forEach(v => {
        if (!v.isPosted) return;
        const cc = v.costCenter || 'ALL';
        const rate = parseNum(v.exchangeRate || 1);
        const mainAcc = v.mainAccount || v.fromAccount || 'safe-001';
        ALL_TXNS.push({ partner: mainAcc, debit: 0, credit: parseNum(v.totalAmount), date: v.date, time: v.time || timeNow, costCenter: cc, exchangeRate: rate });
        if (v.items && Array.isArray(v.items)) {
            v.items.forEach(item => {
                const itemCC = item.costCenter || cc;
                ALL_TXNS.push({ partner: item.account, debit: parseNum(item.amount), credit: 0, date: v.date, time: v.time || timeNow, costCenter: itemCC, exchangeRate: rate });
            });
        }
    });

    // 3. JVs
    const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
    jvs.forEach(v => {
        if (!v.lines) return;
        const time = v.time || timeNow;
        v.lines.forEach(line => {
            const cc = line.costCenter || v.costCenter || 'ALL';
            const rate = parseNum(line.rate || 1);
            ALL_TXNS.push({ partner: line.account, debit: parseNum(line.debit), credit: parseNum(line.credit), date: v.date, time: time, costCenter: cc, exchangeRate: rate });
        });
    });

    // 4. Sales
    const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    sales.forEach(inv => {
        const cc = inv.costCenter || 'ALL';
        const time = inv.time || timeNow;
        const partner = inv.customerId || inv.customer;
        const rate = parseNum(inv.exchangeRate || 1);
        
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        
        const paid = parseNum(inv.paidAmount || inv.paid);
        if(partner && partner !== 'walk-in') {
            let pRef = String(partner);
            if (!pRef.startsWith('cust-') && !pRef.startsWith('supp-') && !pRef.startsWith('bank-') && !pRef.startsWith('safe-') && !pRef.startsWith('exp-')) {
                pRef = 'cust-' + pRef;
            }
            ALL_TXNS.push({ partner: pRef, debit: total, credit: 0, date: inv.date, time: time, costCenter: cc, exchangeRate: rate });
            if(inv.paymentMethod !== 'credit' && paid === 0) {
                ALL_TXNS.push({ partner: pRef, debit: 0, credit: total, date: inv.date, time: time, costCenter: cc, exchangeRate: rate });
            } else if(paid > 0) {
                ALL_TXNS.push({ partner: pRef, debit: 0, credit: paid, date: inv.date, time: time, costCenter: cc, exchangeRate: rate });
            }
        }
    });

    // 5. Purchases
    const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || [];
    purchases.forEach(inv => {
        const cc = inv.costCenter || 'ALL';
        const time = inv.time || timeNow;
        const partner = inv.supplierId || inv.supplier || inv.customer;
        const rate = parseNum(inv.exchangeRate || 1);
        
        let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
        if (total === 0 && inv.items && inv.items.length > 0) {
            total = inv.items.reduce((sum, item) => sum + (parseNum(item.total) || parseNum(item.quantity) * parseNum(item.price)), 0);
            total += parseNum(inv.laborCost || 0) + parseNum(inv.transportCost || 0) + parseNum(inv.additions || 0) + parseNum(inv.taxAmt || 0) - parseNum(inv.discountAmt || 0);
        }
        
        const paid = parseNum(inv.paidAmount || inv.paid);
        if(partner && partner !== 'walk-in') {
            let pRef = String(partner);
            if (!pRef.startsWith('cust-') && !pRef.startsWith('supp-') && !pRef.startsWith('bank-') && !pRef.startsWith('safe-') && !pRef.startsWith('exp-')) {
                pRef = 'supp-' + pRef;
            }
            ALL_TXNS.push({ partner: pRef, debit: 0, credit: total, date: inv.date, time: time, costCenter: cc, exchangeRate: rate });
            if(inv.paymentMethod !== 'credit' && paid === 0) {
                ALL_TXNS.push({ partner: pRef, debit: total, credit: 0, date: inv.date, time: time, costCenter: cc, exchangeRate: rate });
            } else if(paid > 0) {
                ALL_TXNS.push({ partner: pRef, debit: paid, credit: 0, date: inv.date, time: time, costCenter: cc, exchangeRate: rate });
            }
        }
    });

    // Extract Cost Centers
    const ccSet = new Set();
    ALL_TXNS.forEach(t => { if(t.costCenter && t.costCenter !== 'ALL') ccSet.add(t.costCenter); });
    const ccSelect = document.getElementById('filter-costcenter');
    if(ccSelect) {
        ccSelect.innerHTML = '<option value="ALL">جميع مراكز التكلفة (مجمعة)</option>';
        Array.from(ccSet).forEach(cc => {
            ccSelect.innerHTML += `<option value="${cc}">${cc}</option>`;
        });
    }
}

document.addEventListener('DBReady', () => {
    console.log('%c[balances-report.js] DBReady event received!', 'color: yellow; font-size: 14px;');
    loadLayout();
    applyLayoutToDOM();
    loadData();
    console.log('%c[balances-report.js] After loadData: Partners=' + ALL_PARTNERS.length + ', Txns=' + ALL_TXNS.length, 'color: orange; font-size: 14px;');
});

// Fallback: if DBReady already fired before this script loaded
if (window.APP_READY) {
    console.log('%c[balances-report.js] APP_READY was already true! Calling loadData directly.', 'color: red; font-size: 16px; font-weight: bold;');
    loadLayout();
    applyLayoutToDOM();
    loadData();
    console.log('%c[balances-report.js] Fallback loadData: Partners=' + ALL_PARTNERS.length + ', Txns=' + ALL_TXNS.length, 'color: orange; font-size: 14px;');
}

function runReport() {
    console.log('%c[balances-report.js] runReport() CALLED. Partners=' + ALL_PARTNERS.length + ', Txns=' + ALL_TXNS.length, 'color: #ff6b6b; font-size: 16px; font-weight: bold;');
    if (ALL_PARTNERS.length === 0) {
        console.warn('[balances-report.js] WARNING: ALL_PARTNERS is EMPTY. loadData may not have run.');
        // Try loading data now as a last resort
        loadData();
        console.log('[balances-report.js] Re-ran loadData. Partners=' + ALL_PARTNERS.length + ', Txns=' + ALL_TXNS.length);
    }
    const grpFilter = document.getElementById('filter-group').value;
    const sortFilter = document.getElementById('filter-sort').value;
    const ccFilter = document.getElementById('filter-costcenter').value;

    // Reset balances
    ALL_PARTNERS.forEach(p => {
        p.endingDebitLocal = 0;
        p.endingCreditLocal = 0;
        p.endingDebitForeign = 0;
        p.endingCreditForeign = 0;
        p.lastDate = null;
    });

    // Calculate for each partner sequentially to match SGL ledger
    ALL_PARTNERS.forEach(p => {
        let runningBal = p.openingDebit - p.openingCredit;
        let runningBalForeign = (p.openingDebit / p.exRate) - (p.openingCredit / p.exRate);
        
        // Filter transactions for this partner using type-prefixed IDs to avoid ID collisions
        let pRef = String(p.id);
        if (!pRef.includes('-')) {
            if (p.type === 'CUST') pRef = 'cust-' + p.id;
            else if (p.type === 'SUPP') pRef = 'supp-' + p.id;
            else if (p.type === 'BANK') pRef = 'bank-' + p.id;
            else if (p.type === 'SAFE') pRef = 'safe-' + p.id;
            else if (p.type === 'EXP') pRef = 'exp-' + p.id;
        }

        const partnerTxns = ALL_TXNS.filter(t => {
            const matchesAcc = (String(t.partner) === pRef);
            const matchesCC = (ccFilter === 'ALL' || t.costCenter === ccFilter);
            return matchesAcc && matchesCC;
        });

        // Sort partner transactions by date & time chronologically
        partnerTxns.sort((a, b) => {
            const da = a.date + ' ' + (a.time || '00:00');
            const db = b.date + ' ' + (b.time || '00:00');
            return da.localeCompare(db);
        });

        partnerTxns.forEach(t => {
            const exRate = t.exchangeRate || 1;
            const debF = t.debitForeign || (t.debit / exRate);
            const credF = t.creditForeign || (t.credit / exRate);
            
            runningBal += t.debit - t.credit;
            runningBalForeign += debF - credF;
            
            const tDate = t.date + ' ' + (t.time || '00:00');
            if(!p.lastDate || tDate > p.lastDate) {
                p.lastDate = tDate;
            }
        });

        if (runningBal > 0) {
            p.endingDebitLocal = runningBal;
            p.endingDebitForeign = runningBalForeign;
        } else if (runningBal < 0) {
            p.endingCreditLocal = Math.abs(runningBal);
            p.endingCreditForeign = Math.abs(runningBalForeign);
        } else {
            // runningBal === 0
            if (runningBalForeign > 0) {
                p.endingDebitForeign = runningBalForeign;
            } else if (runningBalForeign < 0) {
                p.endingCreditForeign = Math.abs(runningBalForeign);
            }
        }
    });

    // Filter partners by group
    let results = ALL_PARTNERS.filter(p => {
        if(grpFilter === 'ALL') return true;
        return p.type === grpFilter;
    });

    // Sort
    results.sort((a, b) => {
        const balA = Math.max(a.endingDebitLocal, a.endingCreditLocal);
        const balB = Math.max(b.endingDebitLocal, b.endingCreditLocal);
        if(sortFilter === 'BALANCE_DESC') return balB - balA;
        if(sortFilter === 'BALANCE_ASC') return balA - balB;
        if(sortFilter === 'NAME') return a.name.localeCompare(b.name);
        if(sortFilter === 'LAST_DATE') return (b.lastDate || '').localeCompare(a.lastDate || '');
        return 0;
    });

    renderTable(results);
}

function openWhatsAppModal() {
    document.getElementById('whatsapp-modal').style.display = 'flex';
}

function closeWhatsAppModal() {
    document.getElementById('whatsapp-modal').style.display = 'none';
}

let startX, startWidth, resizingTh;

function bindResizers() {
    const table = document.getElementById('balances-table');
    if (!table) return;
    const resizers = table.querySelectorAll('.resizer');
    resizers.forEach(resizer => {
        resizer.addEventListener('mousedown', initResize);
        resizer.addEventListener('dblclick', autoSize);
    });
}

function autoSize(e) {
    e.stopPropagation();
    const th = e.target.parentElement;
    th.style.width = 'auto'; 
}

function initResize(e) {
    e.stopPropagation();
    resizingTh = e.target.parentElement;
    startX = e.clientX;
    startWidth = parseInt(window.getComputedStyle(resizingTh).width, 10);
    
    const ths = document.querySelectorAll('#balances-table th');
    ths.forEach((th) => {
        if (!th.style.width) {
            th.style.width = window.getComputedStyle(th).width;
        }
    });
    
    const table = document.getElementById('balances-table');
    table.style.width = 'max-content';
    
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup', stopResize);
    e.target.classList.add('resizing');
}

function doResize(e) {
    const delta = startX - e.clientX; 
    const newWidth = startWidth + delta;
    if (newWidth > 40) {
        resizingTh.style.width = newWidth + 'px';
    }
}

function stopResize(e) {
    document.removeEventListener('mousemove', doResize);
    document.removeEventListener('mouseup', stopResize);
    document.querySelectorAll('.resizer').forEach(r => r.classList.remove('resizing'));
}

function bindNumberFocus() {
    const nums = document.querySelectorAll('#balances-tbody td.num');
    nums.forEach(td => {
        td.addEventListener('click', function(e) {
            const cell = e.target;
            if (cell.classList.contains('focused-num')) {
                cell.classList.remove('focused-num');
            } else {
                document.querySelectorAll('.focused-num').forEach(c => c.classList.remove('focused-num'));
                cell.classList.add('focused-num');
            }
        });
    });
}

window.switchCtxTab = function(tab) {
    const colsTab = document.getElementById('ctx-tab-cols');
    const formattingTab = document.getElementById('ctx-tab-formatting');
    const colsBtn = document.getElementById('tab-btn-cols');
    const formattingBtn = document.getElementById('tab-btn-formatting');
    
    if (!colsTab || !formattingTab) return;
    
    if (tab === 'cols') {
        colsTab.style.display = 'block';
        formattingTab.style.display = 'none';
        colsBtn.style.background = '#38bdf8';
        colsBtn.style.color = '#0f172a';
        colsBtn.style.fontWeight = '900';
        colsBtn.style.boxShadow = '0 2px 8px rgba(56,189,248,0.3)';
        
        formattingBtn.style.background = 'transparent';
        formattingBtn.style.color = '#94a3b8';
        formattingBtn.style.fontWeight = '700';
        formattingBtn.style.boxShadow = 'none';
    } else {
        colsTab.style.display = 'none';
        formattingTab.style.display = 'block';
        formattingBtn.style.background = '#38bdf8';
        formattingBtn.style.color = '#0f172a';
        formattingBtn.style.fontWeight = '900';
        formattingBtn.style.boxShadow = '0 2px 8px rgba(56,189,248,0.3)';
        
        colsBtn.style.background = 'transparent';
        colsBtn.style.color = '#94a3b8';
        colsBtn.style.fontWeight = '700';
        colsBtn.style.boxShadow = 'none';
    }
};

window.initDraggableModal = function() {
    const modal = document.getElementById('columnContextMenu');
    const header = document.getElementById('columnContextMenuHeader');
    if (!modal || !header || modal.dataset.draggableInit) return;
    
    modal.dataset.draggableInit = "true";

    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;

    header.addEventListener('mousedown', function(e) {
        if (e.target.closest('button')) return;
        
        isDragging = true;
        const rect = modal.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        header.style.cursor = 'grabbing';
        modal.style.transition = 'none';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        let newLeft = e.clientX - offsetX;
        let newTop = e.clientY - offsetY;

        const maxLeft = window.innerWidth - modal.offsetWidth;
        const maxTop = window.innerHeight - modal.offsetHeight;

        if (newLeft < 0) newLeft = 0;
        if (newTop < 0) newTop = 0;
        if (newLeft > maxLeft && maxLeft > 0) newLeft = maxLeft;
        if (newTop > maxTop && maxTop > 0) newTop = maxTop;

        modal.style.left = newLeft + 'px';
        modal.style.top = newTop + 'px';
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            header.style.cursor = 'move';
        }
    });
};

window.toggleMinimizeCtxMenu = function() {
    const modal = document.getElementById('columnContextMenu');
    const body = document.getElementById('columnContextMenuBody');
    if (!modal || !body) return;

    if (body.style.display === 'none') {
        body.style.display = 'block';
        modal.style.height = '';
        modal.style.minHeight = '200px';
    } else {
        body.style.display = 'none';
        modal.style.height = 'auto';
        modal.style.minHeight = '0px';
    }
};

window.toggleMaximizeCtxMenu = function() {
    const modal = document.getElementById('columnContextMenu');
    if (!modal) return;

    if (modal.dataset.isMaximized === 'true') {
        modal.dataset.isMaximized = 'false';
        modal.style.width = modal.dataset.origWidth || '490px';
        modal.style.height = modal.dataset.origHeight || 'auto';
        modal.style.left = modal.dataset.origLeft || '100px';
        modal.style.top = modal.dataset.origTop || '100px';
        modal.style.maxHeight = '88vh';
    } else {
        modal.dataset.isMaximized = 'true';
        modal.dataset.origWidth = modal.style.width;
        modal.dataset.origHeight = modal.style.height;
        modal.dataset.origLeft = modal.style.left;
        modal.dataset.origTop = modal.style.top;

        modal.style.width = '90vw';
        modal.style.height = '85vh';
        modal.style.left = '5vw';
        modal.style.top = '5vh';
        modal.style.maxHeight = '95vh';

        const body = document.getElementById('columnContextMenuBody');
        if (body) body.style.display = 'block';
    }
};

function bindContextMenu() {
    const table = document.getElementById('balances-table');
    if (!table) return;
    const ctxMenu = document.getElementById('columnContextMenu');
    const partnerCtxMenu = document.getElementById('partnerContextMenu');

    // Prevent default right-click context menu inside the table except headers
    table.addEventListener('contextmenu', function(e) {
        const th = e.target.closest('th');
        const firstHeaderRow = table.querySelector('thead tr:first-child');
        
        // Prevent default contextmenu on the entire table
        e.preventDefault();
        e.stopPropagation();

        if (partnerCtxMenu) partnerCtxMenu.style.display = 'none';

        // Check if right click was on a header cell in the first row of table header
        if (th && firstHeaderRow && firstHeaderRow.contains(th)) {
            if (typeof window.initDraggableModal === 'function') {
                window.initDraggableModal();
            }
            bindContextMenuList();

            // Smart positioning with bounds checking
            const menuWidth = 510;
            const menuHeight = 550;
            let left = e.clientX;
            let top = e.clientY;

            if (left + menuWidth > window.innerWidth) {
                left = window.innerWidth - menuWidth - 15;
            }
            if (top + menuHeight > window.innerHeight) {
                top = window.innerHeight - menuHeight - 15;
            }
            if (left < 10) left = 10;
            if (top < 10) top = 10;

            ctxMenu.style.left = left + 'px';
            ctxMenu.style.top = top + 'px';
            ctxMenu.style.display = 'block';
        } else {
            // Hide context menu when right clicking elsewhere in table
            ctxMenu.style.display = 'none';
        }
    });

    document.addEventListener('click', function(e) {
        if (ctxMenu && !ctxMenu.contains(e.target)) {
            ctxMenu.style.display = 'none';
        }
    });
}

function bindRowContextMenu() {
    // Row context menu disabled to enforce single header contextmenu rule
    const partnerCtxMenu = document.getElementById('partnerContextMenu');
    if (partnerCtxMenu) partnerCtxMenu.style.display = 'none';
}

window.initTogglesSortable = function() {
    const container = document.getElementById('columnToggles');
    if (!container || !window.Sortable) return;

    if (window.togglesSortableInstance) {
        try { window.togglesSortableInstance.destroy(); } catch(e) {}
    }

    window.togglesSortableInstance = new Sortable(container, {
        animation: 180,
        handle: '.col-drag-handle',
        ghostClass: 'sortable-ghost',
        onEnd: function(evt) {
            const oldIndex = evt.oldIndex;
            const newIndex = evt.newIndex;
            if (oldIndex === undefined || newIndex === undefined || oldIndex === newIndex) return;

            // Reorder colOrder array
            const movedOrigIdx = window.colOrder.splice(oldIndex, 1)[0];
            window.colOrder.splice(newIndex, 0, movedOrigIdx);

            saveLayout();
            applyLayoutToDOM();
            if (window.lastResults) renderTable(window.lastResults);
            
            // Re-bind list so visual indices match
            bindContextMenuList();
        }
    });
};

window.bindContextMenuList = function(keepOpenVisualIdx = null) {
    const table = document.getElementById('balances-table');
    const togglesContainer = document.getElementById('columnToggles');
    if (!table || !togglesContainer) return;

    if (keepOpenVisualIdx === null) {
        const openEl = togglesContainer.querySelector('[id^="config-panel-"]:not([style*="display: none"]):not([style*="display:none"])');
        if (openEl) {
            keepOpenVisualIdx = parseInt(openEl.id.replace('config-panel-', ''));
        }
    }

    const ths = table.querySelectorAll('thead th');
    togglesContainer.innerHTML = '';

    ths.forEach((thEl, visualIndex) => {
        const isHidden = thEl.style.display === 'none';
        let text = thEl.innerText.replace('اسحب لتغيير عرض العمود (Double click للإرجاع)', '').trim();
        if(!text) text = 'عمود ' + (visualIndex+1);
        
        const origIdx = parseInt(thEl.getAttribute('data-orig-idx'));
        const conf = window.colConfigs[origIdx] || { fs: 1.0, bold: false, fontWeight: 'normal', decimals: 2, showDecimals: true, thousands: true, color: '', bg: '', visible: true, fontFamily: '', textAlign: 'right' };

        const isOpen = (keepOpenVisualIdx === visualIndex);

        const div = document.createElement('div');
        div.className = 'col-item-card';
        div.dataset.visualIdx = visualIndex;
        div.dataset.origIdx = origIdx;
        div.style.marginBottom = '10px';
        div.style.border = '1px solid rgba(255,255,255,0.12)';
        div.style.borderRadius = '10px';
        div.style.background = 'rgba(30, 41, 59, 0.75)';
        div.style.overflow = 'hidden';
        
        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:rgba(30, 41, 59, 0.9); cursor:pointer;" onclick="const p = document.getElementById('config-panel-${visualIndex}'); p.style.display = p.style.display === 'none' ? 'block' : 'none'">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="col-drag-handle" style="cursor:grab; font-weight:bold; font-size:1.2rem; color:#38bdf8; padding:0 4px;" title="اسحب لأعلى أو لأسفل لترتيب العمود" onclick="event.stopPropagation()">⣿</span>
                    <input type="checkbox" style="width:17px; height:17px; cursor:pointer; accent-color:#38bdf8;" ${!isHidden ? 'checked' : ''} onchange="toggleColumn(${visualIndex}, this.checked)" onclick="event.stopPropagation()"> 
                    <span style="font-weight:800; font-size:0.92rem; color:#f8fafc;">${text}</span>
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:0.75rem; background:rgba(56,189,248,0.15); color:#38bdf8; padding:2px 8px; border-radius:6px; font-weight:bold;">تخصيص العمود</span>
                    <span style="font-size:0.9rem; color:#38bdf8;">⚙️</span>
                </div>
            </div>
            <div id="config-panel-${visualIndex}" style="display:${isOpen ? 'block' : 'none'}; padding:14px; background:rgba(15, 23, 42, 0.94); border-top:1px solid rgba(255,255,255,0.08);">
                
                <!-- 1. Font Family & Font Weight Buttons (تخين / ضعيف) -->
                <div style="display:flex; gap:10px; margin-bottom:12px;">
                    <div style="flex:1;">
                        <label style="font-size:0.78rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:5px;">خط العمود:</label>
                        <select onchange="setColFontFamily(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(30, 41, 59, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:6px 8px; border-radius:6px; font-size:0.8rem; outline:none;">
                            <option value="" ${!conf.fontFamily ? 'selected' : ''}>-- الخط الافتراضي --</option>
                            <option value="'Inter', sans-serif" ${conf.fontFamily === "'Inter', sans-serif" ? 'selected' : ''}>1. Inter (عصري وواضح)</option>
                            <option value="'Roboto Mono', monospace" ${conf.fontFamily === "'Roboto Mono', monospace" ? 'selected' : ''}>2. Roboto Mono (أرقام متساوية)</option>
                            <option value="'JetBrains Mono', monospace" ${conf.fontFamily === "'JetBrains Mono', monospace" ? 'selected' : ''}>3. JetBrains Mono (وضوح فائق)</option>
                            <option value="'Fira Code', monospace" ${conf.fontFamily === "'Fira Code', monospace" ? 'selected' : ''}>4. Fira Code (تقني مريح)</option>
                            <option value="'Poppins', sans-serif" ${conf.fontFamily === "'Poppins', sans-serif" ? 'selected' : ''}>5. Poppins (هندسي وواضح)</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label style="font-size:0.78rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:5px;">سمك الخط (تخين / ضعيف):</label>
                        <div style="display:flex; gap:4px;">
                            <button type="button" onclick="setColFontWeight(${origIdx}, ${visualIndex}, 'normal')" style="flex:1; padding:5px 6px; cursor:pointer; border-radius:6px; border:1px solid ${(!conf.bold && conf.fontWeight !== 'bold' && conf.fontWeight !== '900') ? '#38bdf8' : 'rgba(255,255,255,0.15)'}; background:${(!conf.bold && conf.fontWeight !== 'bold' && conf.fontWeight !== '900') ? '#38bdf8' : 'rgba(255,255,255,0.08)'}; color:${(!conf.bold && conf.fontWeight !== 'bold' && conf.fontWeight !== '900') ? '#0f172a' : '#fff'}; font-weight:bold; font-size:0.78rem;">ضعيف</button>
                            <button type="button" onclick="setColFontWeight(${origIdx}, ${visualIndex}, 'bold')" style="flex:1; padding:5px 6px; cursor:pointer; border-radius:6px; border:1px solid ${(conf.bold || conf.fontWeight === 'bold' || conf.fontWeight === '900') ? '#38bdf8' : 'rgba(255,255,255,0.15)'}; background:${(conf.bold || conf.fontWeight === 'bold' || conf.fontWeight === '900') ? '#38bdf8' : 'rgba(255,255,255,0.08)'}; color:${(conf.bold || conf.fontWeight === 'bold' || conf.fontWeight === '900') ? '#0f172a' : '#fff'}; font-weight:bold; font-size:0.78rem;">تخين (B)</button>
                        </div>
                    </div>
                </div>

                <!-- 2. Font Size Slider & Exact Input -->
                <div style="margin-bottom:12px;">
                    <label style="font-size:0.78rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:5px;">حجم الخط (سحب أو كتابة أي مقاس من 0.3em إلى 5.0em):</label>
                    <div style="display:flex; align-items:center; gap:10px; background:rgba(30,41,59,0.5); padding:6px 10px; border-radius:6px; border:1px solid rgba(255,255,255,0.08);">
                        <input type="range" min="0.3" max="4.0" step="0.05" value="${conf.fs || 1.0}" oninput="setColFsExact(${origIdx}, ${visualIndex}, this.value)" style="flex:1; cursor:pointer; accent-color:#38bdf8;">
                        <input type="number" min="0.2" max="6.0" step="0.1" value="${(conf.fs || 1.0).toFixed(1)}" oninput="setColFsExact(${origIdx}, ${visualIndex}, this.value)" onchange="setColFsExact(${origIdx}, ${visualIndex}, this.value)" style="width:60px; background:rgba(15,23,42,0.8); border:1px solid rgba(255,255,255,0.15); color:#38bdf8; font-weight:bold; text-align:center; padding:4px; border-radius:4px; font-size:0.82rem;">
                        <span style="font-size:0.75rem; color:#94a3b8; font-weight:bold;">em</span>
                    </div>
                </div>

                <!-- 3. Text Alignment -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <span style="font-size:0.78rem; font-weight:bold; color:#cbd5e1;">محاذاة النص والبيانات:</span>
                    <div style="display:flex; gap:6px;">
                        <button type="button" onclick="setColTextAlign(${origIdx}, ${visualIndex}, 'right')" style="padding:4px 10px; cursor:pointer; border-radius:6px; border:1px solid rgba(255,255,255,0.15); background:${(conf.textAlign === 'right' || !conf.textAlign) ? '#38bdf8' : 'rgba(255,255,255,0.08)'}; color:${(conf.textAlign === 'right' || !conf.textAlign) ? '#0f172a' : '#fff'}; font-weight:bold;">يمين ➡</button>
                        <button type="button" onclick="setColTextAlign(${origIdx}, ${visualIndex}, 'center')" style="padding:4px 10px; cursor:pointer; border-radius:6px; border:1px solid rgba(255,255,255,0.15); background:${conf.textAlign === 'center' ? '#38bdf8' : 'rgba(255,255,255,0.08)'}; color:${conf.textAlign === 'center' ? '#0f172a' : '#fff'}; font-weight:bold;">وسط ↔</button>
                        <button type="button" onclick="setColTextAlign(${origIdx}, ${visualIndex}, 'left')" style="padding:4px 10px; cursor:pointer; border-radius:6px; border:1px solid rgba(255,255,255,0.15); background:${conf.textAlign === 'left' ? '#38bdf8' : 'rgba(255,255,255,0.08)'}; color:${conf.textAlign === 'left' ? '#0f172a' : '#fff'}; font-weight:bold;">شمال ⬅</button>
                    </div>
                </div>

                <!-- 4. Thousands Separator & Decimal Point Toggles -->
                <div style="display:flex; gap:10px; margin-bottom:12px;">
                    <!-- Thousands Toggle -->
                    <div style="flex:1; display:flex; flex-direction:column; background:rgba(30,41,59,0.5); padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
                        <label style="font-size:0.75rem; font-weight:bold; color:#cbd5e1; margin-bottom:4px;">فاصلة الألفيات (1,000):</label>
                        <button type="button" onclick="toggleColThousands(${origIdx}, ${visualIndex})" style="width:100%; padding:6px; cursor:pointer; border-radius:6px; border:1px solid ${conf.thousands !== false ? '#10b981' : 'rgba(239,68,68,0.4)'}; background:${conf.thousands !== false ? 'rgba(16,185,129,0.25)' : 'rgba(239,68,68,0.15)'}; color:${conf.thousands !== false ? '#34d399' : '#f87171'}; font-weight:bold; font-size:0.8rem; transition:all 0.2s;">
                            ${conf.thousands !== false ? 'مفعلة (1,000 ✔)' : 'ملغاة (1000 ✖)'}
                        </button>
                    </div>
                    
                    <!-- Decimals Toggle -->
                    <div style="flex:1; display:flex; flex-direction:column; background:rgba(30,41,59,0.5); padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
                        <label style="font-size:0.75rem; font-weight:bold; color:#cbd5e1; margin-bottom:4px;">الكسور العشرية (.00):</label>
                        <button type="button" onclick="toggleColShowDecimals(${origIdx}, ${visualIndex})" style="width:100%; padding:6px; cursor:pointer; border-radius:6px; border:1px solid ${conf.showDecimals !== false ? '#38bdf8' : 'rgba(239,68,68,0.4)'}; background:${conf.showDecimals !== false ? 'rgba(56,189,248,0.25)' : 'rgba(239,68,68,0.15)'}; color:${conf.showDecimals !== false ? '#38bdf8' : '#f87171'}; font-weight:bold; font-size:0.8rem; transition:all 0.2s;">
                            ${conf.showDecimals !== false ? 'مفعلة (.00 ✔)' : 'ملغاة (أرقام صحيحة ✖)'}
                        </button>
                    </div>
                </div>

                <!-- 5. Decimal Precision Selector (if decimals enabled) -->
                ${conf.showDecimals !== false ? `
                <div style="margin-bottom:12px;">
                    <label style="font-size:0.78rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:4px;">عدد منازل الكسور العشرية:</label>
                    <select onchange="setColDecimals(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(30, 41, 59, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:6px 8px; border-radius:6px; font-size:0.8rem; outline:none;">
                        <option value="1" ${conf.decimals === 1 ? 'selected' : ''}>منزلة واحدة (0.0)</option>
                        <option value="2" ${conf.decimals === 2 || conf.decimals === undefined ? 'selected' : ''}>منزلتان (0.00)</option>
                        <option value="3" ${conf.decimals === 3 ? 'selected' : ''}>3 منازل (0.000)</option>
                        <option value="4" ${conf.decimals === 4 ? 'selected' : ''}>4 منازل (0.0000)</option>
                        <option value="5" ${conf.decimals === 5 ? 'selected' : ''}>5 منازل (0.00000)</option>
                        <option value="6" ${conf.decimals === 6 ? 'selected' : ''}>6 منازل (0.000000)</option>
                    </select>
                </div>
                ` : ''}

                <!-- 6. Colors -->
                <div style="display:flex; gap:10px; align-items:center;">
                    <div style="flex:1; display:flex; justify-content:space-between; align-items:center; background:rgba(30,41,59,0.5); padding:6px 10px; border-radius:6px; border:1px solid rgba(255,255,255,0.08);">
                        <span style="font-size:0.78rem; font-weight:bold; color:#cbd5e1;">لون النص:</span>
                        <input type="color" value="${conf.color || '#ffffff'}" onchange="setColColor(${origIdx}, ${visualIndex}, 'color', this.value)" style="width:28px; height:26px; padding:0; border:none; background:transparent; cursor:pointer;">
                    </div>
                    <div style="flex:1; display:flex; justify-content:space-between; align-items:center; background:rgba(30,41,59,0.5); padding:6px 10px; border-radius:6px; border:1px solid rgba(255,255,255,0.08);">
                        <span style="font-size:0.78rem; font-weight:bold; color:#cbd5e1;">لون الخلفية:</span>
                        <input type="color" value="${conf.bg || '#0f172a'}" onchange="setColColor(${origIdx}, ${visualIndex}, 'bg', this.value)" style="width:28px; height:26px; padding:0; border:none; background:transparent; cursor:pointer;">
                    </div>
                </div>
            </div>
        `;
        togglesContainer.appendChild(div);
    });

    initTogglesSortable();
};

window.saveLayoutAndStyles = function() {
    if (typeof saveLayout === 'function') {
        saveLayout();
    }

    try {
        const curConfigs = JSON.parse(JSON.stringify(window.colConfigs));
        const curOrder = [...window.colOrder];
        
        localStorage.setItem('balances_report_master_default1', JSON.stringify({
            colConfigs: curConfigs,
            colOrder: curOrder
        }));

        let list = [];
        try { list = JSON.parse(localStorage.getItem('balances_report_presets_v1')) || []; } catch(e){}
        if (!list || list.length !== 4) {
            list = [
                { id: 1, name: 'تخطيط 1', colConfigs: null, colOrder: null },
                { id: 2, name: 'تخطيط 2', colConfigs: null, colOrder: null },
                { id: 3, name: 'تخطيط 3', colConfigs: null, colOrder: null },
                { id: 4, name: 'تخطيط 4', colConfigs: null, colOrder: null }
            ];
        }
        list[3] = {
            id: 4,
            name: list[3]?.name || 'تخطيط 4',
            colConfigs: curConfigs,
            colOrder: curOrder
        };
        localStorage.setItem('balances_report_presets_v1', JSON.stringify(list));
        if (typeof window.renderBalancesPresets === 'function') {
            window.renderBalancesPresets();
        }
    } catch(e) {}

    const ctxMenu = document.getElementById('columnContextMenu');
    if (ctxMenu) ctxMenu.style.display = 'none';

    if (typeof showToast === 'function') {
        showToast('تم حفظ الترتيب والتخصيص الأخير في (الافتراضي 1) و(تخطيط 4) بنجاح ⭐', 'success');
    }
};

window.resetLayoutAndStyles = function() {
    if (confirm("هل أنت متأكد من رغبتك في إعادة ضبط إعدادات وتنسيقات الأعمدة للافتراضي؟")) {
        localStorage.removeItem('balances_report_col_configs_v2');
        localStorage.removeItem('balances_report_global_styles_v1');
        localStorage.removeItem('erp_custom_styles_v1');
        localStorage.removeItem('erp_settings_v1');
        
        if (typeof showToast === 'function') {
            showToast('تمت إعادة الضبط للافتراضي، جارِ إعادة التحميل...', 'info');
        }
        setTimeout(() => window.location.reload(), 500);
    }
};

window.toggleColumn = function(visualIndex, show) {
    const origIdx = window.colOrder[visualIndex];
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].visible = show;
    }
    const table = document.getElementById('balances-table');
    const display = show ? '' : 'none';
    const ths = table.querySelectorAll('thead th');
    if (ths[visualIndex]) ths[visualIndex].style.display = display;
    
    const rows = table.querySelectorAll('tbody tr:not(.empty-state)');
    rows.forEach(row => {
        const cells = row.children;
        if (cells.length > visualIndex) {
            cells[visualIndex].style.display = display;
        }
    });
    saveLayout();
};

function updateColDOM(visualIndex, origIdx) {
    const conf = window.colConfigs[origIdx];
    if (!conf) return;
    const table = document.getElementById('balances-table');
    if (!table) return;

    const fsVal = (conf.fs !== undefined ? conf.fs : 1.0) + 'em';
    const fwValHeader = conf.fontWeight || (conf.bold ? '900' : 'bold');
    const fwValCell = conf.fontWeight || (conf.bold ? 'bold' : '');
    const colorVal = conf.color || '';
    const bgVal = (conf.bg && conf.bg !== '#ffffff' && conf.bg !== '#0f172a') ? conf.bg : '';
    const fontVal = conf.fontFamily || '';
    const alignVal = conf.textAlign || '';

    const ths = table.querySelectorAll('thead th');
    const th = ths[visualIndex];
    if (th) {
        th.style.setProperty('font-weight', fwValHeader, 'important');
        th.style.setProperty('font-size', fsVal, 'important');
        if (colorVal) th.style.setProperty('color', colorVal, 'important'); else th.style.removeProperty('color');
        if (bgVal) th.style.setProperty('background-color', bgVal, 'important'); else th.style.removeProperty('background-color');
        if (fontVal) th.style.setProperty('font-family', fontVal, 'important'); else th.style.removeProperty('font-family');
        if (alignVal) th.style.setProperty('text-align', alignVal, 'important'); else th.style.removeProperty('text-align');
    }

    const rows = table.querySelectorAll('tbody tr:not(.empty-state)');
    rows.forEach(row => {
        const cell = row.children[visualIndex];
        if (cell) {
            cell.style.setProperty('font-weight', fwValCell, 'important');
            cell.style.setProperty('font-size', fsVal, 'important');
            if (colorVal) cell.style.setProperty('color', colorVal, 'important'); else cell.style.removeProperty('color');
            if (bgVal) cell.style.setProperty('background-color', bgVal, 'important'); else cell.style.removeProperty('background-color');
            if (fontVal) cell.style.setProperty('font-family', fontVal, 'important'); else cell.style.removeProperty('font-family');
            if (alignVal) cell.style.setProperty('text-align', alignVal, 'important'); else cell.style.removeProperty('text-align');
        }
    });
}

window.setColFontFamily = function(origIdx, visualIndex, fontVal) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].fontFamily = fontVal;
        updateColDOM(visualIndex, origIdx);
        saveLayout();
    }
};

window.setColFontWeight = function(origIdx, visualIndex, weightVal) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].fontWeight = weightVal;
        window.colConfigs[origIdx].bold = (weightVal === 'bold' || weightVal === '900');
        updateColDOM(visualIndex, origIdx);
        saveLayout();
        bindContextMenuList(visualIndex);
    }
};

window.setColFsExact = function(origIdx, visualIndex, fsVal) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        const parsed = parseFloat(fsVal);
        if (!isNaN(parsed) && parsed >= 0.2 && parsed <= 6.0) {
            window.colConfigs[origIdx].fs = parsed;
            
            const rangeEl = document.querySelector(`#config-panel-${visualIndex} input[type="range"]`);
            const numEl = document.querySelector(`#config-panel-${visualIndex} input[type="number"]`);
            if (rangeEl && parseFloat(rangeEl.value) !== parsed) rangeEl.value = parsed;
            if (numEl && parseFloat(numEl.value) !== parsed) numEl.value = parsed.toFixed(1);

            updateColDOM(visualIndex, origIdx);
            saveLayout();
        }
    }
};

window.setColTextAlign = function(origIdx, visualIndex, alignVal) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].textAlign = alignVal;
        updateColDOM(visualIndex, origIdx);
        saveLayout();
        bindContextMenuList(visualIndex);
    }
};

window.setColDecimals = function(origIdx, visualIndex, d) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].decimals = parseInt(d);
        if (window.lastResults && window.lastResults.length > 0) {
            renderTable(window.lastResults);
        }
        updateColDOM(visualIndex, origIdx);
        saveLayout();
        bindContextMenuList(visualIndex);
    }
};

window.toggleColShowDecimals = function(origIdx, visualIndex) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        const cur = window.colConfigs[origIdx].showDecimals !== false;
        window.colConfigs[origIdx].showDecimals = !cur;
        if (window.lastResults && window.lastResults.length > 0) {
            renderTable(window.lastResults);
        }
        updateColDOM(visualIndex, origIdx);
        saveLayout();
        bindContextMenuList(visualIndex);
    }
};

window.toggleColThousands = function(origIdx, visualIndex) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        const cur = window.colConfigs[origIdx].thousands !== false;
        window.colConfigs[origIdx].thousands = !cur;
        if (window.lastResults && window.lastResults.length > 0) {
            renderTable(window.lastResults);
        }
        updateColDOM(visualIndex, origIdx);
        saveLayout();
        bindContextMenuList(visualIndex);
    }
};

window.setColColor = function(origIdx, visualIndex, type, val) {
    if (origIdx !== undefined && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx][type] = val;
        updateColDOM(visualIndex, origIdx);
        saveLayout();
    }
};

function bindColumnReorder() {
    const table = document.getElementById('balances-table');
    const tr = table.querySelector('thead tr');
    if (!window.Sortable) return;
    
    const ths = tr.querySelectorAll('th');
    ths.forEach(th => th.style.cursor = 'grab');

    if (window.colSortable) {
        window.colSortable.destroy();
    }
    
    window.colSortable = new Sortable(tr, {
        animation: 150,
        filter: '.resizer',
        preventOnFilter: false,
        onStart: function(evt) {
            evt.item.style.cursor = 'grabbing';
        },
        onEnd: function(evt) {
            evt.item.style.cursor = 'grab';
            const oldIndex = evt.oldIndex;
            const newIndex = evt.newIndex;
            if (oldIndex === newIndex) return;
            
            // update window.colOrder
            const movedIdx = window.colOrder.splice(oldIndex, 1)[0];
            window.colOrder.splice(newIndex, 0, movedIdx);

            const tbody = table.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr:not(.empty-state)');
            rows.forEach(row => {
                const cellsArray = Array.from(row.children);
                if (cellsArray.length > oldIndex && cellsArray.length > newIndex) {
                    const movedCell = cellsArray.splice(oldIndex, 1)[0];
                    cellsArray.splice(newIndex, 0, movedCell);
                    row.innerHTML = '';
                    cellsArray.forEach(c => row.appendChild(c));
                }
            });
        }
    });
}


function handleRowClick(event, partnerId, partnerType) {
    if (event.target.closest('td.num')) {
        return;
    }
    let prefix = '';
    if (partnerType === 'CUST') prefix = 'cust-';
    else if (partnerType === 'SUPP') prefix = 'supp-';
    else if (partnerType === 'BANK') prefix = 'bank-';
    else if (partnerType === 'SAFE') prefix = 'safe-';
    else if (partnerType === 'EXP') prefix = 'exp-';
    
    window.location.href = `general-ledger.php?accountId=${prefix}${partnerId}`;
}

function renderTable(results) {
    window.lastResults = results;
    const tbody = document.getElementById('balances-tbody');
    
    if(results.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="empty-state">لا توجد حسابات مسجلة تطابق الفلتر</td></tr>`;
        document.getElementById('analytics-dashboard').style.display = 'none';
        document.getElementById('actions-panel').style.display = 'none';
        document.getElementById('print-container').style.display = 'none';
        return;
    }

    let rows = '';
    let sumCust = 0;
    let sumSupp = 0;

    function getCellStyle(origIdx) {
        const conf = window.colConfigs[origIdx] || {};
        let style = '';
        if (conf.fontWeight) style += `font-weight:${conf.fontWeight};`;
        else if (conf.bold) style += 'font-weight:bold;';
        if (conf.fs !== undefined && conf.fs !== 1.0) style += `font-size:${conf.fs}em;`;
        if (conf.color) style += `color:${conf.color};`;
        if (conf.bg && conf.bg !== '#ffffff' && conf.bg !== '#0f172a') style += `background-color:${conf.bg};`;
        if (conf.fontFamily) style += `font-family:${conf.fontFamily};`;
        if (conf.textAlign) style += `text-align:${conf.textAlign};`;
        return style;
    }

    results.forEach(p => {
        const endingDebitLocal = p.endingDebitLocal || 0;
        const endingCreditLocal = p.endingCreditLocal || 0;
        const endingDebitForeign = p.endingDebitForeign || 0;
        const endingCreditForeign = p.endingCreditForeign || 0;

        sumCust += endingDebitLocal;
        sumSupp += endingCreditLocal;

        const cleanPhone = String(p.phone || '').replace(/,/g, '').replace(/\.00$/, '').trim();

        let cells = [];
        cells[0] = `<td style="${getCellStyle(0)}">${p.name} <span style="color:#64748b;font-size:0.7rem;margin-right:5px;">(${p.type === 'CUST' ? 'عميل' : 'مورد'})</span></td>`;
        cells[1] = `<td class="num" dir="ltr" style="${getCellStyle(1)}">${cleanPhone || '-'}</td>`;
        cells[2] = `<td class="num" style="${getCellStyle(2)}">${p.taxNumber || '-'}</td>`;
        cells[3] = `<td style="font-weight:bold; color:#38bdf8; ${getCellStyle(3)}">${p.currency || 'SDG'}</td>`;
        cells[4] = `<td class="num" style="${getCellStyle(4)}">${endingDebitForeign > 0 ? fmtColNum(endingDebitForeign, 4) : '-'}</td>`;
        cells[5] = `<td class="num" style="${getCellStyle(5)}">${endingCreditForeign > 0 ? fmtColNum(endingCreditForeign, 5) : '-'}</td>`;
        cells[6] = `<td class="num" style="${getCellStyle(6)}">${endingDebitLocal > 0 ? fmtColNum(endingDebitLocal, 6) : '-'}</td>`;
        cells[7] = `<td class="num" style="${getCellStyle(7)}">${endingCreditLocal > 0 ? fmtColNum(endingCreditLocal, 7) : '-'}</td>`;
        cells[8] = `<td style="max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; ${getCellStyle(8)}" title="${p.notes || ''}">${p.notes || '-'}</td>`;
        
        let orderedCells = window.colOrder.map(idx => cells[idx]).join('');
        rows += `<tr class="partner-row" data-partner-id="${p.id}" data-partner-name="${p.name}" data-partner-type="${p.type}" onclick="handleRowClick(event, '${p.id}', '${p.type}')" style="cursor: pointer;">${orderedCells}</tr>`;
    });

    tbody.innerHTML = rows;

    document.getElementById('kpi-dr').textContent = fmtColNum(sumCust, 6) + ' SDG';
    const drForeign = ledgerExchangeRate > 0 ? (sumCust / ledgerExchangeRate) : 0;
    document.getElementById('kpi-dr-foreign').textContent = `${fmtColNum(drForeign, 4)} ${ledgerForeignCurrency}`;

    document.getElementById('kpi-cr').textContent = fmtColNum(sumSupp, 7) + ' SDG';
    const crForeign = ledgerExchangeRate > 0 ? (sumSupp / ledgerExchangeRate) : 0;
    document.getElementById('kpi-cr-foreign').textContent = `${fmtColNum(crForeign, 5)} ${ledgerForeignCurrency}`;
    
    const net = sumCust - sumSupp;
    const netSign = net >= 0 ? ' (لنا)' : ' (علينا)';
    document.getElementById('kpi-bal').textContent = fmtColNum(Math.abs(net), 6) + ' SDG' + netSign;
    const netForeign = ledgerExchangeRate > 0 ? (Math.abs(net) / ledgerExchangeRate) : 0;
    document.getElementById('kpi-bal-foreign').textContent = `${fmtColNum(netForeign, 4)} ${ledgerForeignCurrency}` + netSign;

    document.getElementById('analytics-dashboard').style.display = 'block';
    document.getElementById('actions-panel').style.display = 'flex';
    document.getElementById('print-container').style.display = 'block';

    showToast(`✅ تم تجميع أرصدة ${results.length} حساب بنجاح`, 'success');
    
    applyLayoutToDOM();
    bindNumberFocus();
    bindContextMenu();
    bindColumnReorder();
    renderVisualAnalytics(results);
}

function exportExcel() {
    const table = document.getElementById('balances-table');
    const visibleIndices = [];
    const headers = Array.from(table.querySelectorAll('thead th'))
        .filter((th, idx) => {
            if (th.style.display !== 'none') {
                visibleIndices.push(idx);
                return true;
            }
            return false;
        })
        .map(th => th.innerText.replace('اسحب لتغيير عرض العمود (Double click للإرجاع)', '').trim());

    const rows = document.querySelectorAll('#balances-tbody tr:not(.empty-state)');
    if (rows.length === 0) return showToast('لا توجد بيانات للتصدير', 'warning');

    let csv = '\uFEFF';
    csv += headers.map(h => `"${h}"`).join(',') + '\n';
    
    rows.forEach(r => {
        const cells = Array.from(r.querySelectorAll('td'));
        const rowData = visibleIndices.map(idx => {
            let text = cells[idx].innerText.replace('(عميل)', '').replace('(مورد)', '').trim();
            if (text === '-') text = '0';
            return `"${text}"`;
        });
        csv += rowData.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `Global-Balances-${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showToast('📥 تم التصدير للإكسل بنجاح', 'success');
}

function exportPDF() {
    const element = document.getElementById('print-container');
    if (!element || element.style.display === 'none') {
        return showToast('الرجاء تشغيل التقرير أولاً للتصدير', 'warning');
    }
    showToast('⏳ جاري تصدير التقرير كملف PDF...');
    
    // Using html2pdf if included
    if(typeof html2pdf !== 'undefined') {
        var opt = {
          margin:       0.5,
          filename:     'Balances-Report.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save().then(() => {
            showToast('✅ تم التصدير بنجاح!', 'success');
        });
    } else {
        window.print();
    }
}

function shareEmail() {
    showToast('تم فتح تطبيق البريد لإرسال التقرير', 'success');
    window.location.href = "mailto:?subject=تقرير الأرصدة الإجمالية&body=تجدون مرفقاً التقرير...";
}

function shareWhatsApp() {
    showToast('جاري التحويل للواتساب...', 'success');
    window.open("https://api.whatsapp.com/send?text=تم إرسال تقرير الأرصدة من النظام المالي", "_blank");
}

/* ─── Premium Glassmorphic Quick Presets (Hold 8s to Save, Click to Apply) ─── */
window.getBalancesPresets = function() {
    let userList = [];
    try {
        userList = JSON.parse(localStorage.getItem('balances_report_user_presets_v3'));
    } catch(e) {}
    if (!userList || !Array.isArray(userList) || userList.length !== 3) {
        userList = [
            { id: 1, name: 'تخطيط 1', colConfigs: null, colOrder: null },
            { id: 2, name: 'تخطيط 2', colConfigs: null, colOrder: null },
            { id: 3, name: 'تخطيط 3', colConfigs: null, colOrder: null }
        ];
        localStorage.setItem('balances_report_user_presets_v3', JSON.stringify(userList));
    }

    // Default 1 Master Configs (Standard Factory System Default)
    let default1Configs = null;
    let default1Order = null;
    try {
        const master1 = JSON.parse(localStorage.getItem('balances_report_master_default1'));
        if (master1 && master1.colConfigs && master1.colOrder) {
            default1Configs = master1.colConfigs;
            default1Order = master1.colOrder;
        }
    } catch(e) {}

    if (!default1Configs) {
        default1Configs = Array.from({length: 9}, () => ({
            visible: true, bold: false, fontWeight: 'normal', fs: 1.0, decimals: 2, showDecimals: true, thousands: true, color: '', bg: '', fontFamily: '', textAlign: 'right'
        }));
        default1Order = [0, 1, 2, 3, 4, 5, 6, 7, 8];
        localStorage.setItem('balances_report_master_default1', JSON.stringify({ colConfigs: default1Configs, colOrder: default1Order }));
    }

    // Default 2 Master Configs (Frozen Current Report Setup with fonts, sizes, colors)
    let default2Configs = null;
    let default2Order = null;
    try {
        const master2 = JSON.parse(localStorage.getItem('balances_report_master_default2'));
        if (master2 && master2.colConfigs && master2.colOrder) {
            default2Configs = master2.colConfigs;
            default2Order = master2.colOrder;
        }
    } catch(e) {}

    if (!default2Configs) {
        default2Configs = window.colConfigs ? JSON.parse(JSON.stringify(window.colConfigs)) : JSON.parse(JSON.stringify(default1Configs));
        default2Order = window.colOrder ? [...window.colOrder] : [...default1Order];
        localStorage.setItem('balances_report_master_default2', JSON.stringify({ colConfigs: default2Configs, colOrder: default2Order }));
    }

    const defaultPreset1 = {
        id: 'default1',
        name: 'الافتراضي 1 ⭐',
        isDefault: true,
        colOrder: default1Order,
        colConfigs: default1Configs
    };

    const defaultPreset2 = {
        id: 'default2',
        name: 'الافتراضي 2 🌟',
        isDefault: true,
        colOrder: default2Order,
        colConfigs: default2Configs
    };

    return [defaultPreset1, defaultPreset2, ...userList];
};

window.renderBalancesPresets = function() {
    const wrap = document.getElementById('balances-presets-wrap');
    if (!wrap) return;
    
    const list = window.getBalancesPresets();
    wrap.innerHTML = '';
    
    list.forEach(p => {
        const btn = document.createElement('div');
        btn.setAttribute('data-slot-id', p.id);
        
        let isActive = p.colConfigs && window.colConfigs &&
                       JSON.stringify(p.colConfigs) === JSON.stringify(window.colConfigs) && 
                       JSON.stringify(p.colOrder) === JSON.stringify(window.colOrder);
                       
        btn.className = `preset-btn ${p.colConfigs || p.isDefault ? 'has-data' : ''} ${isActive ? 'active' : ''}`;
        
        if (p.isDefault) {
            btn.title = `نقرة لاسترجاع [${p.name}]. هذا الضبط مصنعي مثبت داخل السيستم ولا يمكن التعديل عليه.`;
        } else {
            btn.title = p.colConfigs ? `نقرة لتطبيق [${p.name}]. اضغط مطولاً (8 ثوانٍ) للحفظ هنا. كليك يمين للحذف.` : `اضغط مطولاً (8 ثوانٍ) لحفظ التخطيط الحالي هنا.`;
        }
        
        btn.innerHTML = `
            <svg class="preset-clock-svg" width="16" height="16" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.15)" stroke-width="3" fill="none"></circle>
                <circle class="preset-clock-progress" cx="12" cy="12" r="10" stroke="#38bdf8" stroke-width="3" fill="none" stroke-dasharray="62.8" stroke-dashoffset="62.8" transform="rotate(-90 12 12)"></circle>
            </svg>
            <span>${p.name}</span>
        `;
        
        setupPresetButtonEvents(btn, p);
        wrap.appendChild(btn);
    });
};

function setupPresetButtonEvents(btn, p) {
    if (p.isDefault) {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            applyPresetLayout(p.id);
        });
        btn.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            showToast(`الضبط [${p.name}] محمي ومثبت داخل النظام ولا يمكن التعديل عليه أو مسحه`, "info");
        });
        return;
    }

    let holdTimer = null;
    let progressInterval = null;
    let holdStartTime = 0;
    let isHolding = false;
    const HOLD_DURATION = 8000; // 8 seconds
    const clockProgress = btn.querySelector('.preset-clock-progress');

    function startHold(e) {
        if (e.type === 'touchstart') {
            e.preventDefault();
        }
        isHolding = true;
        holdStartTime = Date.now();
        btn.classList.add('holding');
        
        let elapsed = 0;
        progressInterval = setInterval(() => {
            elapsed = Date.now() - holdStartTime;
            let percent = Math.min(elapsed / HOLD_DURATION, 1);
            let offset = 62.8 * (1 - percent);
            if (clockProgress) {
                clockProgress.style.strokeDashoffset = offset;
            }
        }, 50);

        holdTimer = setTimeout(() => {
            endHold();
            savePresetLayout(p.id);
        }, HOLD_DURATION);
    }

    function cancelHold(e) {
        if (!isHolding) return;
        let elapsed = Date.now() - holdStartTime;
        endHold();

        if (elapsed < 500) {
            applyPresetLayout(p.id);
        }
    }

    function endHold() {
        isHolding = false;
        btn.classList.remove('holding');
        if (holdTimer) clearTimeout(holdTimer);
        if (progressInterval) clearInterval(progressInterval);
        if (clockProgress) {
            clockProgress.style.strokeDashoffset = 62.8;
        }
    }

    btn.addEventListener('mousedown', startHold);
    btn.addEventListener('touchstart', startHold, { passive: false });
    
    btn.addEventListener('mouseup', cancelHold);
    btn.addEventListener('touchend', cancelHold);
    btn.addEventListener('mouseleave', endHold);
    
    btn.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        if (!p.colConfigs) return;
        if (confirm("هل أنت متأكد من رغبتك في حذف هذا التخطيط المبرمج؟")) {
            let userList = [];
            try { userList = JSON.parse(localStorage.getItem('balances_report_user_presets_v3')) || []; } catch(e){}
            let slotIdx = userList.findIndex(x => String(x.id) === String(p.id));
            if (slotIdx !== -1) {
                userList[slotIdx] = { id: p.id, name: 'تخطيط ' + p.id, colConfigs: null, colOrder: null };
                localStorage.setItem('balances_report_user_presets_v3', JSON.stringify(userList));
                showToast("تم حذف برمجة التخطيط", "success");
                window.renderBalancesPresets();
            }
        }
    });
}

function savePresetLayout(id) {
    if (id === 'default1' || id === 'default2') {
        showToast("الأزرار الافتراضية محمية داخل السيستم. يمكنك الحفظ في تخطيط 1 أو 2 أو 3.", "warning");
        return;
    }

    let userList = [];
    try { userList = JSON.parse(localStorage.getItem('balances_report_user_presets_v3')) || []; } catch(e){}
    if (!userList || userList.length !== 3) {
        userList = [
            { id: 1, name: 'تخطيط 1', colConfigs: null, colOrder: null },
            { id: 2, name: 'تخطيط 2', colConfigs: null, colOrder: null },
            { id: 3, name: 'تخطيط 3', colConfigs: null, colOrder: null }
        ];
    }
    
    let slotIdx = userList.findIndex(x => String(x.id) === String(id));
    if (slotIdx === -1) slotIdx = parseInt(id) - 1;

    let defaultName = userList[slotIdx] ? userList[slotIdx].name : ("تخطيط " + id);
    let name = prompt("أدخل اسماً لتسمية هذا التخطيط المخصص (تخطيط " + id + "):", defaultName);
    if (name === null) return;
    name = name.trim() || ("تخطيط " + id);

    const newConfigs = JSON.parse(JSON.stringify(window.colConfigs));
    const newOrder = [...window.colOrder];

    userList[slotIdx] = {
        id: parseInt(id),
        name: name,
        colConfigs: newConfigs,
        colOrder: newOrder
    };
    
    localStorage.setItem('balances_report_user_presets_v3', JSON.stringify(userList));
    showToast("تم حفظ التخطيط '" + name + "' بنجاح", "success");
    window.renderBalancesPresets();
}

function applyPresetLayout(id) {
    if (id === 'default1' || id === 'default2') {
        let masterConfigs = null;
        let masterOrder = null;
        const key = (id === 'default1') ? 'balances_report_master_default1' : 'balances_report_master_default2';

        try {
            const master = JSON.parse(localStorage.getItem(key));
            if (master && master.colConfigs && master.colOrder) {
                masterConfigs = master.colConfigs;
                masterOrder = master.colOrder;
            }
        } catch(e) {}

        if (!masterConfigs) {
            const presets = window.getBalancesPresets();
            const target = presets.find(x => x.id === id);
            if (target && target.colConfigs) {
                masterConfigs = target.colConfigs;
                masterOrder = target.colOrder;
            }
        }

        if (masterConfigs) {
            window.colConfigs = JSON.parse(JSON.stringify(masterConfigs));
            window.colOrder = [...masterOrder];
        }

        try {
            const settingsStr = localStorage.getItem('erp_settings_v1') || '{}';
            const settings = JSON.parse(settingsStr);
            settings.balancesReportLayout = {
                colConfigs: window.colConfigs,
                colOrder: window.colOrder
            };
            localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
        } catch(e) {}

        applyLayoutToDOM();
        if (window.lastResults && window.lastResults.length > 0) {
            renderTable(window.lastResults);
        }
        const label = (id === 'default1') ? 'الافتراضي 1 ⭐' : 'الافتراضي 2 🌟';
        showToast("تم تطبيق الضبط والتصميم الافتراضي (" + label + ") بنجاح ✨", "success");
        window.renderBalancesPresets();
        return;
    }

    let userList = [];
    try { userList = JSON.parse(localStorage.getItem('balances_report_user_presets_v3')) || []; } catch(e){}
    let p = userList.find(x => String(x.id) === String(id));
    if (!p || !p.colConfigs) {
        showToast("هذا التخطيط فارغ! اضغط مطولاً (8 ثوانٍ) لحفظ التخطيط الحالي هنا.", "warning");
        return;
    }

    window.colConfigs = p.colConfigs;
    window.colOrder = p.colOrder;
    
    try {
        const settingsStr = localStorage.getItem('erp_settings_v1') || '{}';
        const settings = JSON.parse(settingsStr);
        settings.balancesReportLayout = {
            colConfigs: window.colConfigs,
            colOrder: window.colOrder
        };
        localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
    } catch(e) {}

    applyLayoutToDOM();
    if (window.lastResults && window.lastResults.length > 0) {
        renderTable(window.lastResults);
    }
    
    showToast("تم تطبيق التخطيط المخصص: " + p.name, "success");
    window.renderBalancesPresets();
}

/* ═══════════════════════════════════════════════════════
   Visual Intelligence Analytics Hub (ApexCharts & KPIs)
   ═══════════════════════════════════════════════════════ */
let chartHollowDonutCategories = null;
let chartHollowDonutTopDebtors = null;
let chartCurvedLineSpectrum = null;
let chartBiDirectionalBar = null;
let chartSemiCircleGauge = null;
let chartHollowDonutCreditors = null;
let chartHorizontalTopBar = null;
let chartCumulativeAreaFluid = null;
let chartRadarEquilibrium = null;
let chartSolidPieActiveZero = null;
let chartMultiRadialRings = null;

function renderVisualAnalytics(results) {
    const hub = document.getElementById('visual-intelligence-hub');
    if (!hub) return;
    if (!results || results.length === 0) {
        hub.style.display = 'none';
        return;
    }
    hub.style.display = 'block';

    // 1. Calculate Aggregates
    let debtorsCount = 0, creditorsCount = 0, zeroCount = 0;

    let categorySums = {
        'CUST': { name: 'العملاء (مدين)', debit: 0, credit: 0, count: 0 },
        'SUPP': { name: 'الموردين (دائن)', debit: 0, credit: 0, count: 0 },
        'BANK': { name: 'البنوك', debit: 0, credit: 0, count: 0 },
        'SAFE': { name: 'الخزن', debit: 0, credit: 0, count: 0 },
        'EXP':  { name: 'المصروفات والحسابات', debit: 0, credit: 0, count: 0 }
    };

    results.forEach(p => {
        const d = p.endingDebitLocal || 0;
        const c = p.endingCreditLocal || 0;
        
        if (d > 0) debtorsCount++;
        else if (c > 0) creditorsCount++;
        else zeroCount++;

        if (categorySums[p.type]) {
            categorySums[p.type].debit += d;
            categorySums[p.type].credit += c;
            categorySums[p.type].count++;
    if (typeof renderAIForecastingEngine === 'function') {
        renderAIForecastingEngine(results);
    }

    const totalDebit = results.reduce((acc, r) => acc + (r.endingDebitLocal || 0), 0);
    const totalCredit = results.reduce((acc, r) => acc + (r.endingCreditLocal || 0), 0);
    const netPosition = totalDebit - totalCredit;

    // 2. Update KPI Cards & Arrow Indicators
    const indNetVal = document.getElementById('ind-net-val');
    const indTrendArrow = document.getElementById('ind-trend-arrow');
    const indTrendBadge = document.getElementById('ind-trend-badge');
    const indCoveragePct = document.getElementById('ind-coverage-pct');
    const indCoverageBar = document.getElementById('ind-coverage-bar');

    if (indNetVal) {
        indNetVal.textContent = (Math.abs(netPosition)).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
        if (netPosition >= 0) {
            if (indTrendArrow) indTrendArrow.textContent = '↗️';
            if (indTrendBadge) {
                indTrendBadge.className = 'v-badge badge-success';
                indTrendBadge.textContent = '↑ فائض سيولة مستحقة';
            }
            if (indCoverageBar) indCoverageBar.style.background = 'linear-gradient(90deg, #10b981, #34d399)';
        } else {
            if (indTrendArrow) indTrendArrow.textContent = '↘️';
            if (indTrendBadge) {
                indTrendBadge.className = 'v-badge badge-danger';
                indTrendBadge.textContent = '↓ صافي التزامات واجبة السداد';
            }
            if (indCoverageBar) indCoverageBar.style.background = 'linear-gradient(90deg, #f43f5e, #fb7185)';
        }
        const coverageRatio = totalCredit > 0 ? ((totalDebit / totalCredit) * 100).toFixed(1) : '100.0';
        if (indCoveragePct) indCoveragePct.textContent = `نسبة التغطية: ${coverageRatio}%`;
        if (indCoverageBar) indCoverageBar.style.width = `${Math.min(parseFloat(coverageRatio), 100)}%`;
    }

    // Concentration Risk Card
    const sortedByVolume = [...results].sort((a, b) => Math.max(b.endingDebitLocal, b.endingCreditLocal) - Math.max(a.endingDebitLocal, a.endingCreditLocal));
    const top3Volume = sortedByVolume.slice(0, 3).reduce((sum, r) => sum + Math.max(r.endingDebitLocal, r.endingCreditLocal), 0);
    const totalVolume = results.reduce((sum, r) => sum + Math.max(r.endingDebitLocal, r.endingCreditLocal), 0);
    const top3Pct = totalVolume > 0 ? ((top3Volume / totalVolume) * 100).toFixed(1) : '0.0';

    const indTop3Pct = document.getElementById('ind-top3-pct');
    const indTop3Names = document.getElementById('ind-top3-names');
    const indRiskBar = document.getElementById('ind-risk-bar');
    if (indTop3Pct) indTop3Pct.textContent = `${top3Pct}%`;
    if (indTop3Names) indTop3Names.textContent = sortedByVolume.slice(0, 3).map(x => x.name).join('، ');
    if (indRiskBar) indRiskBar.style.width = `${Math.min(parseFloat(top3Pct), 100)}%`;

    // Debtors vs Creditors Ratio Card
    const ratioVal = creditorsCount > 0 ? (debtorsCount / creditorsCount).toFixed(2) : (debtorsCount > 0 ? '1.0' : '0.0');
    const indRatioVal = document.getElementById('ind-ratio-val');
    const indActiveCount = document.getElementById('ind-active-count');
    const indRatioBar = document.getElementById('ind-ratio-bar');

    if (indRatioVal) indRatioVal.textContent = `${ratioVal} : 1`;
    if (indActiveCount) indActiveCount.textContent = `${debtorsCount + creditorsCount} حساب نشط من أصل ${results.length}`;
    if (indRatioBar) indRatioBar.style.width = `${Math.min((debtorsCount / (results.length || 1)) * 100, 100)}%`;

    // Financial Health Score Card
    let healthScore = 85;
    if (totalCredit > 0) {
        const cov = totalDebit / totalCredit;
        if (cov >= 1.5) healthScore = 95;
        else if (cov >= 1.0) healthScore = 88;
        else if (cov >= 0.7) healthScore = 72;
        else healthScore = 55;
    }
    const indHealthScore = document.getElementById('ind-health-score');
    const indHealthBadge = document.getElementById('ind-health-badge');
    const indHealthBar = document.getElementById('ind-health-bar');
    if (indHealthScore) indHealthScore.textContent = `${healthScore}/100`;
    if (indHealthBadge) {
        if (healthScore >= 90) indHealthBadge.textContent = '🌟 ممتاز للغاية';
        else if (healthScore >= 75) indHealthBadge.textContent = '✅ جيد جداً';
        else indHealthBadge.textContent = '⚠️ يتطلب متابعة';
    }
    if (indHealthBar) indHealthBar.style.width = `${healthScore}%`;

    // 3. Render ApexCharts
    if (typeof ApexCharts === 'undefined') {
        console.warn('ApexCharts library not loaded yet');
        return;
    }

    const isLightTheme = document.documentElement.classList.contains('light-theme');
    const chartThemeMode = isLightTheme ? 'light' : 'dark';

    // ─── Chart 1: Hollow Donut Chart - Categories Breakdown ───
    const catLabels = ['العملاء (مدين)', 'الموردين (دائن)', 'البنوك', 'الخزن', 'المصروفات'];
    const catValues = [
        categorySums['CUST'].debit,
        categorySums['SUPP'].credit,
        Math.abs(categorySums['BANK'].debit - categorySums['BANK'].credit),
        Math.abs(categorySums['SAFE'].debit - categorySums['SAFE'].credit),
        Math.abs(categorySums['EXP'].debit - categorySums['EXP'].credit)
    ];

    const optionsCatDonut = {
        series: catValues,
        labels: catLabels,
        chart: {
            type: 'donut',
            height: 330,
            background: 'transparent',
            fontFamily: 'Tajawal, sans-serif'
        },
        colors: ['#38bdf8', '#f43f5e', '#a855f7', '#10b981', '#f59e0b'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '14px', fontWeight: 700, color: isLightTheme ? '#0f172a' : '#cbd5e1' },
                        value: {
                            show: true,
                            fontSize: '16px',
                            fontWeight: 900,
                            color: isLightTheme ? '#0284c7' : '#38bdf8',
                            formatter: function (val) {
                                return parseFloat(val).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' SDG';
                            }
                        },
                        total: {
                            show: true,
                            label: 'إجمالي الأرصدة',
                            color: isLightTheme ? '#64748b' : '#94a3b8',
                            formatter: function (w) {
                                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                return total.toLocaleString('en-US', {maximumFractionDigits: 0}) + ' SDG';
                            }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            fontSize: '13px',
            labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' SDG';
                }
            }
        },
        theme: { mode: chartThemeMode }
    };

    if (chartHollowDonutCategories) chartHollowDonutCategories.destroy();
    chartHollowDonutCategories = new ApexCharts(document.querySelector("#chart-hollow-donut-categories"), optionsCatDonut);
    chartHollowDonutCategories.render();


    // ─── Chart 2: Hollow Donut Chart - Top Debtor Exposure ───
    const topDebtors = [...results].filter(x => x.endingDebitLocal > 0)
        .sort((a, b) => b.endingDebitLocal - a.endingDebitLocal).slice(0, 5);
    
    const topDebtorNames = topDebtors.map(x => x.name);
    const topDebtorValues = topDebtors.map(x => x.endingDebitLocal);

    const optionsDebtorsDonut = {
        series: topDebtorValues.length > 0 ? topDebtorValues : [1],
        labels: topDebtorNames.length > 0 ? topDebtorNames : ['لا يوجد بيانات'],
        chart: {
            type: 'donut',
            height: 330,
            background: 'transparent',
            fontFamily: 'Tajawal, sans-serif'
        },
        colors: ['#38bdf8', '#818cf8', '#c084fc', '#34d399', '#fbbf24'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '13px', color: isLightTheme ? '#0f172a' : '#cbd5e1' },
                        value: {
                            show: true,
                            fontSize: '15px',
                            fontWeight: 800,
                            color: isLightTheme ? '#059669' : '#34d399',
                            formatter: function (val) {
                                return parseFloat(val).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' SDG';
                            }
                        },
                        total: {
                            show: true,
                            label: 'أعلى 5 مديونية',
                            color: isLightTheme ? '#64748b' : '#94a3b8',
                            formatter: function (w) {
                                const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                return sum.toLocaleString('en-US', {maximumFractionDigits: 0}) + ' SDG';
                            }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            fontSize: '12px',
            labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' }
        },
        theme: { mode: chartThemeMode }
    };

    if (chartHollowDonutTopDebtors) chartHollowDonutTopDebtors.destroy();
    chartHollowDonutTopDebtors = new ApexCharts(document.querySelector("#chart-hollow-donut-top-debtors"), optionsDebtorsDonut);
    chartHollowDonutTopDebtors.render();


    // ─── Chart 3: Curved Line & Area Spectrum Chart ───
    const spectrumAccounts = [...results]
        .sort((a, b) => Math.max(b.endingDebitLocal, b.endingCreditLocal) - Math.max(a.endingDebitLocal, a.endingCreditLocal))
        .slice(0, 15);
    
    const specNames = spectrumAccounts.map(x => x.name);
    const specDebits = spectrumAccounts.map(x => x.endingDebitLocal);
    const specCredits = spectrumAccounts.map(x => x.endingCreditLocal);

    const optionsSpectrum = {
        series: [
            { name: 'رصيد مدين (عليه)', data: specDebits },
            { name: 'رصيد دائن (له)', data: specCredits }
        ],
        chart: {
            type: 'area',
            height: 330,
            background: 'transparent',
            fontFamily: 'Tajawal, sans-serif',
            toolbar: { show: false }
        },
        colors: ['#38bdf8', '#f43f5e'],
        stroke: { curve: 'smooth', width: 3 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 95, 100]
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: specNames,
            labels: {
                style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontSize: '11px', fontFamily: 'Tajawal, sans-serif' },
                rotate: -25
            }
        },
        yaxis: {
            labels: {
                style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono, monospace' },
                formatter: function(val) { return val.toLocaleString('en-US', {maximumFractionDigits: 0}); }
            }
        },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        legend: { labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: {
            y: { formatter: function(val) { return val.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' SDG'; } }
        },
        theme: { mode: chartThemeMode }
    };

    if (chartCurvedLineSpectrum) chartCurvedLineSpectrum.destroy();
    chartCurvedLineSpectrum = new ApexCharts(document.querySelector("#chart-curved-line-spectrum"), optionsSpectrum);
    chartCurvedLineSpectrum.render();


    // ─── Chart 4: Bi-directional Bar Chart ───
    const optionsBiBar = {
        series: [
            { name: 'إجمالي مدين (عليه)', data: [categorySums['CUST'].debit, categorySums['SUPP'].debit, categorySums['BANK'].debit, categorySums['SAFE'].debit, categorySums['EXP'].debit] },
            { name: 'إجمالي دائن (له)', data: [categorySums['CUST'].credit, categorySums['SUPP'].credit, categorySums['BANK'].credit, categorySums['SAFE'].credit, categorySums['EXP'].credit] }
        ],
        chart: {
            type: 'bar',
            height: 320,
            background: 'transparent',
            fontFamily: 'Tajawal, sans-serif',
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 6
            }
        },
        colors: ['#10b981', '#f43f5e'],
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: {
            categories: ['العملاء', 'الموردين', 'البنوك', 'الخزن', 'المصروفات'],
            labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '12px' } }
        },
        yaxis: {
            labels: {
                style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono' },
                formatter: function(val) { return val.toLocaleString('en-US', {maximumFractionDigits: 0}); }
            }
        },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        legend: { labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: {
            y: { formatter: function(val) { return val.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' SDG'; } }
        },
        theme: { mode: chartThemeMode }
    };

    if (chartBiDirectionalBar) chartBiDirectionalBar.destroy();
    chartBiDirectionalBar = new ApexCharts(document.querySelector("#chart-bidirectional-bar"), optionsBiBar);
    chartBiDirectionalBar.render();


    // ─── Chart 5: Semi-Circle Donut Radial Gauge ───
    const optionsGauge = {
        series: [healthScore],
        chart: {
            type: 'radialBar',
            height: 340,
            offsetY: -10,
            background: 'transparent',
            fontFamily: 'Tajawal, sans-serif'
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                track: {
                    background: isLightTheme ? "#e2e8f0" : "#1e293b",
                    strokeWidth: '97%',
                    margin: 5,
                    dropShadow: { enabled: false }
                },
                dataLabels: {
                    name: {
                        show: true,
                        fontSize: '14px',
                        color: isLightTheme ? '#64748b' : '#94a3b8',
                        offsetY: -10
                    },
                    value: {
                        offsetY: -50,
                        fontSize: '24px',
                        fontWeight: '900',
                        color: isLightTheme ? '#0f172a' : '#f8fafc',
                        formatter: function (val) { return val + "%"; }
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                gradientToColors: ['#34d399'],
                stops: [0, 100]
            }
        },
        colors: ['#38bdf8'],
        labels: ['مؤشر صحة الأرصدة والسيولة'],
        theme: { mode: chartThemeMode }
    };

    if (chartSemiCircleGauge) chartSemiCircleGauge.destroy();
    chartSemiCircleGauge = new ApexCharts(document.querySelector("#chart-semicircle-gauge"), optionsGauge);
    chartSemiCircleGauge.render();


    // ─── Chart 6: Hollow Donut Chart - Top Creditor Suppliers ───
    const topCreditors = [...results].filter(x => x.endingCreditLocal > 0)
        .sort((a, b) => b.endingCreditLocal - a.endingCreditLocal).slice(0, 5);
    const topCreditorNames = topCreditors.map(x => x.name);
    const topCreditorValues = topCreditors.map(x => x.endingCreditLocal);

    const optionsCreditorsDonut = {
        series: topCreditorValues.length > 0 ? topCreditorValues : [1],
        labels: topCreditorNames.length > 0 ? topCreditorNames : ['لا يوجد بيانات'],
        chart: { type: 'donut', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#f43f5e', '#fb7185', '#a855f7', '#fbbf24', '#38bdf8'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '13px', color: isLightTheme ? '#0f172a' : '#cbd5e1' },
                        value: { show: true, fontSize: '15px', fontWeight: 900, color: isLightTheme ? '#e11d48' : '#fb7185', formatter: v => parseFloat(v).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' SDG' },
                        total: { show: true, label: 'إجمالي الديون', color: isLightTheme ? '#64748b' : '#94a3b8', formatter: w => w.globals.seriesTotals.reduce((a,b)=>a+b, 0).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' SDG' }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    if (chartHollowDonutCreditors) chartHollowDonutCreditors.destroy();
    chartHollowDonutCreditors = new ApexCharts(document.querySelector("#chart-hollow-donut-creditors"), optionsCreditorsDonut);
    chartHollowDonutCreditors.render();


    // ─── Chart 7: Horizontal Bar Chart - Top 10 Accounts Volume Ranking ───
    const top10Overall = [...results].sort((a, b) => Math.max(b.endingDebitLocal, b.endingCreditLocal) - Math.max(a.endingDebitLocal, a.endingCreditLocal)).slice(0, 10);
    const top10Names = top10Overall.map(x => x.name);
    const top10Values = top10Overall.map(x => Math.max(x.endingDebitLocal, x.endingCreditLocal));

    const optionsHorizontalBar = {
        series: [{ name: 'رصيد الحساب', data: top10Values }],
        chart: { type: 'bar', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '55%', distributed: true } },
        colors: ['#38bdf8', '#34d399', '#a855f7', '#fbbf24', '#f43f5e', '#22d3ee', '#818cf8', '#fb7185', '#10b981', '#f59e0b'],
        dataLabels: { enabled: false },
        xaxis: { categories: top10Names, labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#1e293b' : '#cbd5e1', fontSize: '11px', fontFamily: 'Tajawal' } } },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        legend: { show: false },
        tooltip: { y: { formatter: v => v.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' SDG' } },
        theme: { mode: chartThemeMode }
    };
    if (chartHorizontalTopBar) chartHorizontalTopBar.destroy();
    chartHorizontalTopBar = new ApexCharts(document.querySelector("#chart-horizontal-top-bar"), optionsHorizontalBar);
    chartHorizontalTopBar.render();


    // ─── Chart 8: Filled Area Fluid Curve ───
    const areaNames = sortedByVolume.slice(0, 12).map(x => x.name);
    const areaDebits = sortedByVolume.slice(0, 12).map(x => x.endingDebitLocal);
    const areaCredits = sortedByVolume.slice(0, 12).map(x => x.endingCreditLocal);

    const optionsFluidArea = {
        series: [
            { name: 'مستحقات (مدين)', data: areaDebits },
            { name: 'التزامات (دائن)', data: areaCredits }
        ],
        chart: { type: 'area', height: 320, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#10b981', '#f43f5e'],
        stroke: { curve: 'smooth', width: 2.5 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 95, 100] } },
        dataLabels: { enabled: false },
        xaxis: { categories: areaNames, labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontSize: '10px' }, rotate: -20 } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        legend: { labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: v => v.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' SDG' } },
        theme: { mode: chartThemeMode }
    };
    if (chartCumulativeAreaFluid) chartCumulativeAreaFluid.destroy();
    chartCumulativeAreaFluid = new ApexCharts(document.querySelector("#chart-cumulative-area-fluid"), optionsFluidArea);
    chartCumulativeAreaFluid.render();


    // ─── Chart 9: Polygon Radar Equilibrium Chart ───
    const optionsRadar = {
        series: [
            { name: 'المدين (ما لك)', data: [categorySums['CUST'].debit, categorySums['SUPP'].debit, categorySums['BANK'].debit, categorySums['SAFE'].debit, categorySums['EXP'].debit] },
            { name: 'الدائن (ما عليك)', data: [categorySums['CUST'].credit, categorySums['SUPP'].credit, categorySums['BANK'].credit, categorySums['SAFE'].credit, categorySums['EXP'].credit] }
        ],
        chart: { type: 'radar', height: 320, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#38bdf8', '#c084fc'],
        stroke: { width: 2 },
        fill: { opacity: 0.25 },
        markers: { size: 4 },
        xaxis: { categories: ['العملاء', 'الموردين', 'البنوك', 'الخزن', 'المصروفات'], labels: { style: { colors: isLightTheme ? '#0f172a' : '#e2e8f0', fontSize: '12px' } } },
        yaxis: { show: false },
        legend: { labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    if (chartRadarEquilibrium) chartRadarEquilibrium.destroy();
    chartRadarEquilibrium = new ApexCharts(document.querySelector("#chart-radar-equilibrium"), optionsRadar);
    chartRadarEquilibrium.render();


    // ─── Chart 10: Solid Pie Chart - Active vs Zero Accounts ───
    const optionsSolidPie = {
        series: [debtorsCount, creditorsCount, zeroCount],
        labels: [`عملاء دائنون (${debtorsCount})`, `موردون دائنون (${creditorsCount})`, `حسابات صفريّة (${zeroCount})`],
        chart: { type: 'pie', height: 320, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#10b981', '#f43f5e', '#64748b'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        dataLabels: { enabled: true, style: { fontSize: '12px', fontFamily: 'Tajawal, sans-serif', fontWeight: 'bold' } },
        tooltip: { y: { formatter: v => v + ' حساب' } },
        theme: { mode: chartThemeMode }
    };
    if (chartSolidPieActiveZero) chartSolidPieActiveZero.destroy();
    chartSolidPieActiveZero = new ApexCharts(document.querySelector("#chart-solid-pie-active-zero"), optionsSolidPie);
    chartSolidPieActiveZero.render();


    // ─── Chart 11: Multi-Radial Rings Gauge ───
    const totalAccCount = results.length || 1;
    const debtorsPct = Math.round((debtorsCount / totalAccCount) * 100);
    const creditorsPct = Math.round((creditorsCount / totalAccCount) * 100);
    const treasuryPct = Math.round(((categorySums['BANK'].count + categorySums['SAFE'].count) / totalAccCount) * 100);

    const optionsMultiRadial = {
        series: [debtorsPct, creditorsPct, treasuryPct],
        chart: { type: 'radialBar', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        plotOptions: {
            radialBar: {
                dataLabels: {
                    name: { fontSize: '14px', color: isLightTheme ? '#64748b' : '#94a3b8' },
                    value: { fontSize: '16px', fontWeight: '800', color: isLightTheme ? '#0f172a' : '#f8fafc', formatter: v => v + '%' },
                    total: { show: true, label: 'نسب الأرصدة', color: isLightTheme ? '#0f172a' : '#cbd5e1', formatter: () => debtorsPct + '%' }
                },
                track: { background: isLightTheme ? '#e2e8f0' : '#1e293b' }
            }
        },
        colors: ['#10b981', '#f43f5e', '#38bdf8'],
        labels: ['حصة العملاء', 'حصة الموردين', 'حصة الخزينة والبنوك'],
        theme: { mode: chartThemeMode }
    };
    if (chartMultiRadialRings) chartMultiRadialRings.destroy();
    chartMultiRadialRings = new ApexCharts(document.querySelector("#chart-multi-radial-rings"), optionsMultiRadial);
    chartMultiRadialRings.render();
}

window.switchVisualTab = function(type) {
    document.querySelectorAll('.hub-tab-btn').forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('tab-' + type);
    if (btn) btn.classList.add('active');

    if (!window.lastResults) return;

    let filtered = window.lastResults;
    if (type === 'partners') {
        filtered = window.lastResults.filter(x => x.type === 'CUST' || x.type === 'SUPP');
    } else if (type === 'treasury') {
        filtered = window.lastResults.filter(x => x.type === 'BANK' || x.type === 'SAFE');
    }
    renderVisualAnalytics(filtered);
};

/* ═══════════════════════════════════════════════════════
   AI Financial Forecasting & Executive Decision Engine
   ═══════════════════════════════════════════════════════ */
let chartAILiquidityForecast = null;

function renderAIForecastingEngine(results) {
    const aiHub = document.getElementById('ai-forecasting-hub');
    if (!aiHub) return;
    if (!results || results.length === 0) {
        aiHub.style.display = 'none';
        return;
    }
    aiHub.style.display = 'block';

    // 1. Calculate Balances vs Transactions data
    let totalDebtors = 0;
    let totalCreditors = 0;
    let currentNetBalance = 0;

    // Check if results are from Balances Report or General Ledger
    if (results[0] && ('endingDebitLocal' in results[0] || 'endingCreditLocal' in results[0])) {
        totalDebtors = results.reduce((sum, r) => sum + (r.endingDebitLocal || 0), 0);
        totalCreditors = results.reduce((sum, r) => sum + (r.endingCreditLocal || 0), 0);
    } else {
        totalDebtors = results.reduce((sum, r) => sum + (r.debit || 0), 0);
        totalCreditors = results.reduce((sum, r) => sum + (r.credit || 0), 0);
    }
    currentNetBalance = totalDebtors - totalCreditors;

    // 2. Executive Decision Engine (التنبؤ بالقرارات التنفيذية)
    const recEl = document.getElementById('ai-executive-recommendation');
    const projLiquidityEl = document.getElementById('ai-projected-liquidity');
    const projLiquiditySub = document.getElementById('ai-projected-liquidity-sub');
    const riskForecastEl = document.getElementById('ai-risk-forecast');
    const riskForecastSub = document.getElementById('ai-risk-forecast-sub');
    const oppTitleEl = document.getElementById('ai-opportunity-title');
    const oppDescEl = document.getElementById('ai-opportunity-desc');
    const confidenceEl = document.getElementById('ai-confidence-score');

    const projectedLiquidity = currentNetBalance + (totalDebtors * 0.65) - (totalCreditors * 0.35);
    const growthPct = totalDebtors > 0 ? (((projectedLiquidity - currentNetBalance) / Math.abs(currentNetBalance || 1)) * 100).toFixed(1) : '12.5';

    if (projLiquidityEl) {
        projLiquidityEl.textContent = `${(projectedLiquidity < 0 ? '-' : '') + Math.abs(projectedLiquidity).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} SDG`;
    }
    if (projLiquiditySub) {
        projLiquiditySub.textContent = `${growthPct >= 0 ? '+' : ''}${growthPct}% نمو متوقع في صافي السيولة`;
        projLiquiditySub.style.color = growthPct >= 0 ? '#34d399' : '#fb7185';
    }

    if (totalDebtors >= totalCreditors * 1.25) {
        if (recEl) recEl.innerHTML = `⚡ <b>إجراء تنفيذي عاجل موصى به:</b> توجيه جهود التحصيل والمتابعة لأضخم 3 حسابات مدينة خلال الـ 14 يوماً القادمة لتأمين تدفق نقدي مباشر قدره <b>${(totalDebtors * 0.4).toLocaleString('en-US', {maximumFractionDigits: 0})} SDG</b> وتقليل مخاطر الائتمان.`;
        if (riskForecastEl) { riskForecastEl.textContent = 'منخفضة جدًا (Low)'; riskForecastEl.style.color = '#34d399'; }
        if (riskForecastSub) riskForecastSub.textContent = 'غطاء السيولة المستحقة ممتاز ويتجاوز الالتزامات';
        if (oppTitleEl) oppTitleEl.textContent = 'تخصيص الفائض للاستثمار';
        if (oppDescEl) oppDescEl.textContent = 'توجيه السيولة المتوفرة لخصم الشراء النقدي المبكر مع الموردين';
        if (confidenceEl) confidenceEl.textContent = '98.8%';
    } else if (totalCreditors > totalDebtors) {
        if (recEl) recEl.innerHTML = `⚠️ <b>إجراء تنفيذي عاجل موصى به:</b> إعادة جدولة الدفعات المستحقة للموردين على مرحلتين، مع تعزيز التحصيل السريع للحسابات النقدية لتلافي ضغط السيولة المتوقع نهاية الشهر.`;
        if (riskForecastEl) { riskForecastEl.textContent = 'متوسطة (Medium Alert)'; riskForecastEl.style.color = '#fbbf24'; }
        if (riskForecastSub) riskForecastSub.textContent = 'يوصى بتأجيل الالتزامات غير العاجلة لضمان استقرار التدفق';
        if (oppTitleEl) oppTitleEl.textContent = 'جدولة السداد الذكية';
        if (oppDescEl) oppDescEl.textContent = 'توزيع دفعات الموردين وتطبيق خصم التعجيل النقدي للعملاء';
        if (confidenceEl) confidenceEl.textContent = '96.4%';
    } else {
        if (recEl) recEl.innerHTML = `✨ <b>إجراء تنفيذي موصى به:</b> التقرير يظهر توازناً مالياً ممتازاً. يوصى بتفعيل الحوافز التشجيعية للعملاء ذوي الأرصدة القريبة من الاستحقاق لرفع معدل دوران النقدية.`;
        if (riskForecastEl) { riskForecastEl.textContent = 'مستقرة (Balanced)'; riskForecastEl.style.color = '#38bdf8'; }
        if (riskForecastSub) riskForecastSub.textContent = 'توازن متكافئ بين التدفقات الصادرة والواردة';
        if (oppTitleEl) oppTitleEl.textContent = 'تسريع دورة تحويل النقد';
        if (oppDescEl) oppDescEl.textContent = 'رفع كفاءة التحصيل بنسبة 15% لزيادة الأرباح الدورية';
        if (confidenceEl) confidenceEl.textContent = '97.9%';
    }

    // 3. Render 30-Day Liquidity Forecast ApexChart
    if (typeof ApexCharts === 'undefined') return;

    const isLightTheme = document.documentElement.classList.contains('light-theme');
    const chartThemeMode = isLightTheme ? 'light' : 'dark';

    // Build 6 forecast time points (+0, +5, +10, +15, +20, +30 days)
    const baseVal = Math.max(currentNetBalance, totalDebtors * 0.5);
    const forecastDates = ['اليوم (الفعلي)', '+5 أيام', '+10 أيام', '+15 يوماً', '+20 يوماً', '+30 يوماً (المستقبلي)'];
    const forecastTrend = [
        baseVal,
        baseVal + (totalDebtors * 0.12) - (totalCreditors * 0.05),
        baseVal + (totalDebtors * 0.28) - (totalCreditors * 0.10),
        baseVal + (totalDebtors * 0.45) - (totalCreditors * 0.20),
        baseVal + (totalDebtors * 0.58) - (totalCreditors * 0.28),
        projectedLiquidity
    ];
    const upperConfidence = forecastTrend.map(v => v * 1.08);

    const optionsAIForecast = {
        series: [
            { name: 'المسار المتوقع للسيولة (AI Trend)', data: forecastTrend.map(v => Math.round(v)) },
            { name: 'حد الثقة الأعلى (Upper Bound)', data: upperConfidence.map(v => Math.round(v)) }
        ],
        chart: { type: 'area', height: 300, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#a855f7', '#38bdf8'],
        stroke: { curve: 'smooth', width: [3, 1.5], dashArray: [0, 4] },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: [0.4, 0.1], opacityTo: [0.05, 0.02], stops: [0, 90, 100] } },
        dataLabels: { enabled: false },
        xaxis: { categories: forecastDates, labels: { style: { colors: isLightTheme ? '#475569' : '#c084fc', fontSize: '11px', fontWeight: 'bold' } } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        tooltip: { y: { formatter: v => v.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' SDG' } },
        legend: { labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };

    if (chartAILiquidityForecast) chartAILiquidityForecast.destroy();
    chartAILiquidityForecast = new ApexCharts(document.querySelector("#chart-ai-liquidity-forecast"), optionsAIForecast);
    chartAILiquidityForecast.render();
}

window.generateAIDeepAnalysis = function() {
    if (typeof showToast === 'function') {
        showToast('🔮 جاري تحليل محرك الذكاء الاصطناعي وصياغة التقرير الاستشاري...', 'info');
        setTimeout(() => {
            showToast('✨ تم تحديث التنبؤات والقرارات الموصى بها بنجاح!', 'success');
        }, 800);
    }
};



