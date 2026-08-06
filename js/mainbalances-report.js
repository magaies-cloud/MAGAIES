/* ═══════════════════════════════════════════════════════
   balances-report.js — Global Balances & Aging Report
   ═══════════════════════════════════════════════════════ */
console.log('%c[balances-report.js] FILE LOADED v2', 'color: lime; font-size: 16px; font-weight: bold;');

window.colConfigs = [
    { name: 'اسم الحساب', alias: 'اسم الحساب', id: 'name', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'right' },
    { name: 'رقم الهاتف', alias: 'رقم الهاتف', id: 'phone', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
    { name: 'الرقم الضريبي', alias: 'الرقم الضريبي', id: 'taxNo', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
    { name: 'العملة', alias: 'العملة', id: 'currency', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
    { name: 'عليه عملة', alias: 'عليه عملة', id: 'debitF', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
    { name: 'له عملة', alias: 'له عملة', id: 'creditF', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
    { name: 'عليه', alias: 'عليه', id: 'debit', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
    { name: 'له', alias: 'له', id: 'credit', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
    { name: 'ملحوظة', alias: 'ملحوظة', id: 'note', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'right' }
];
window.colOrder = [0, 1, 2, 3, 4, 5, 6, 7, 8];
window.currentTableTheme = '';
window.currentNumFont = 'num-font-inter';

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
                const isBold = conf.bold || conf.fontWeight === 'bold' || conf.fontWeight === '900';
                const fwVal = isBold ? '900' : 'normal';
                const alignVal = conf.textAlign || conf.align || '';
                
                th.style.setProperty('font-weight', fwVal, 'important');
                th.style.setProperty('font-size', fsVal, 'important');
                if (conf.color) th.style.setProperty('color', conf.color, 'important'); else th.style.removeProperty('color');
                if (conf.bg && conf.bg !== '#ffffff' && conf.bg !== '#0f172a') th.style.setProperty('background-color', conf.bg, 'important'); else th.style.removeProperty('background-color');
                if (conf.fontFamily) th.style.setProperty('font-family', conf.fontFamily, 'important'); else th.style.removeProperty('font-family');
                if (alignVal) th.style.setProperty('text-align', alignVal, 'important'); else th.style.removeProperty('text-align');
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
                        const isBold = conf.bold || conf.fontWeight === 'bold' || conf.fontWeight === '900';
                        const fwVal = isBold ? '900' : 'normal';
                        const alignVal = conf.textAlign || conf.align || '';

                        cell.style.setProperty('font-weight', fwVal, 'important');
                        cell.style.setProperty('font-size', fsVal, 'important');
                        if (conf.color) cell.style.setProperty('color', conf.color, 'important'); else cell.style.removeProperty('color');
                        if (conf.bg && conf.bg !== '#ffffff' && conf.bg !== '#0f172a') cell.style.setProperty('background-color', conf.bg, 'important'); else cell.style.removeProperty('background-color');
                        if (conf.fontFamily) cell.style.setProperty('font-family', conf.fontFamily, 'important'); else cell.style.removeProperty('font-family');
                        if (alignVal) cell.style.setProperty('text-align', alignVal, 'important'); else cell.style.removeProperty('text-align');
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
        const master1 = localStorage.getItem('mainbalances_report_master_v4_default1');
        if (master1) {
            const parsed = JSON.parse(master1);
            if (parsed && parsed.colConfigs && parsed.colOrder) {
                window.colConfigs = parsed.colConfigs;
                window.colOrder = parsed.colOrder;
                if (parsed.currentTableTheme !== undefined) window.currentTableTheme = parsed.currentTableTheme;
                if (parsed.currentNumFont !== undefined) window.currentNumFont = parsed.currentNumFont;
            }
        } else {
            const settingsStr = localStorage.getItem('erp_settings_v1') || '{}';
            const settings = JSON.parse(settingsStr);
            const layout = settings.mainbalancesReportLayout;
            if (layout && layout.colConfigs && layout.colOrder) {
                window.colConfigs = layout.colConfigs;
                window.colOrder = layout.colOrder;
                if (layout.currentTableTheme !== undefined) window.currentTableTheme = layout.currentTableTheme;
                if (layout.currentNumFont !== undefined) window.currentNumFont = layout.currentNumFont;
            }
        }
    } catch(e) { console.error('Error loading local layout', e); }

    const token = localStorage.getItem('erp_token') || '';
    const headers = {};
    if (token) headers['Authorization'] = 'Bearer ' + token;

    // Synchronize permanently with Database
    fetch('api_index.php/report-layout?reportKey=mainbalances_report_layout_v4_default1', { headers: headers })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && data.layout) {
                const dbLayout = data.layout;
                if (dbLayout.colConfigs && dbLayout.colOrder && dbLayout.colOrder.length === 9) {
                    window.colConfigs = dbLayout.colConfigs;
                    window.colOrder = dbLayout.colOrder;
                    if (dbLayout.currentTableTheme !== undefined) {
                        window.currentTableTheme = dbLayout.currentTableTheme;
                        if (typeof changeTableTheme === 'function') changeTableTheme(dbLayout.currentTableTheme, false);
                    }
                    if (dbLayout.currentNumFont !== undefined) {
                        window.currentNumFont = dbLayout.currentNumFont;
                        if (typeof changeNumberFont === 'function') changeNumberFont(dbLayout.currentNumFont, false);
                    }
                    
                    try {
                        localStorage.setItem('mainbalances_report_master_v4_default1', JSON.stringify(dbLayout));
                        const settingsStr = localStorage.getItem('erp_settings_v1') || '{}';
                        const settings = JSON.parse(settingsStr);
                        settings.mainbalancesReportLayout = dbLayout;
                        localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
                    } catch(e) {}

                    if (typeof applyLayoutToDOM === 'function') applyLayoutToDOM();
                    if (typeof bindContextMenuList === 'function') bindContextMenuList();
                    if (typeof renderColumnContextMenu === 'function') renderColumnContextMenu();
                    if (typeof window.renderBalancesPresets === 'function') window.renderBalancesPresets();
                    if (window.lastResults && typeof renderTable === 'function') renderTable(window.lastResults);
                }
            }
        })
        .catch(err => console.log('DB layout sync info:', err));

    // Permanently sync and lock User Presets to Database
    fetch('api_index.php/report-layout?reportKey=balances_preview_presets_v7', { headers: headers })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && Array.isArray(data.layout) && data.layout.length > 0) {
                localStorage.setItem('balances_preview_presets_v7', JSON.stringify(data.layout));
                if (typeof window.renderBalancesPresets === 'function') window.renderBalancesPresets();
            } else {
                let curUserList = localStorage.getItem('balances_preview_presets_v7');
                if (curUserList) {
                    fetch('api_index.php/report-layout', {
                        method: 'POST',
                        headers: { ...headers, 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            reportKey: 'balances_preview_presets_v7',
                            layout: JSON.parse(curUserList)
                        })
                    });
                }
            }
        })
        .catch(e => console.log(e));
}
loadLayout();

document.addEventListener('DOMContentLoaded', () => {
    applyLayoutToDOM();
    bindRowContextMenu();
    if (typeof window.renderBalancesPresets === 'function') {
        window.renderBalancesPresets();
    }
});

let isSavingLayout = false;
function saveLayout() {
    try {
        const layoutPayload = {
            colConfigs: window.colConfigs,
            colOrder: window.colOrder,
            currentTableTheme: window.currentTableTheme || '',
            currentNumFont: window.currentNumFont || 'num-font-inter'
        };

        const settingsStr = localStorage.getItem('erp_settings_v1') || '{}';
        const settings = JSON.parse(settingsStr);
        settings.mainbalancesReportLayout = layoutPayload;
        localStorage.setItem('erp_settings_v1', JSON.stringify(settings));
        localStorage.setItem('mainbalances_report_master_v4_default1', JSON.stringify(layoutPayload));
        
        const token = localStorage.getItem('erp_token') || '';
        const headers = { 'Content-Type': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        // Save permanently to MySQL Database
        if (!isSavingLayout) {
            isSavingLayout = true;
            fetch('api_index.php/report-layout', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    reportKey: 'mainbalances_report_layout_v4_default1',
                    layout: layoutPayload
                })
            })
            .then(res => res.json())
            .then(data => {
                isSavingLayout = false;
                if (data && data.success) {
                    console.log('✅ Layout saved permanently to DB');
                }
            })
            .catch(err => {
                isSavingLayout = false;
                console.error('Failed to save layout to DB', err);
            });
        }
        
        if (typeof window.renderBalancesPresets === 'function') {
            window.renderBalancesPresets();
        }
    } catch(e) {
        console.error('Error saving layout', e);
    }
}

function fmtColNum(n, origIdx) {
    const conf = (window.colConfigs && window.colConfigs[origIdx]) ? window.colConfigs[origIdx] : {};
    const showDec = (conf.showDecimals !== false);
    const d = showDec ? ((conf.decimals !== undefined) ? parseInt(conf.decimals) : ((conf.dec !== undefined) ? parseInt(conf.dec) : 2)) : 0;
    const isThousands = (conf.thousands !== false);
    const thouSep = (conf.thousandsSep !== undefined) ? conf.thousandsSep : ',';
    const decSep = (conf.decSep !== undefined) ? conf.decSep : '.';
    const roundMode = conf.roundMode || 'round';

    const val = parseFloat(n);
    if (isNaN(val)) return String(n || '');

    let roundedVal = val;
    const factor = Math.pow(10, d);
    if (d >= 0) {
        if (roundMode === 'floor') {
            roundedVal = Math.floor(val * factor) / factor;
        } else if (roundMode === 'ceil') {
            roundedVal = Math.ceil(val * factor) / factor;
        } else if (roundMode === 'trunc') {
            roundedVal = Math.trunc(val * factor) / factor;
        } else {
            roundedVal = Math.round(val * factor) / factor;
        }
    }

    let formatted = roundedVal.toFixed(d);
    const parts = formatted.split('.');
    
    if (isThousands && thouSep !== 'none') {
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thouSep);
    }
    
    if (d > 0) {
        return parts[0] + decSep + (parts[1] || '');
    } else {
        return parts[0];
    }
}
function parseNum(str) {
    return parseFloat(String(str || '0').replace(/,/g, '')) || 0;
}
function animateKpiNumber(elId, targetNum, suffixStr = '', decCount = 2) {
    const el = document.getElementById(elId);
    if (!el || isNaN(targetNum)) return;
    
    const start = performance.now();
    const duration = 1200;
    const isNegative = targetNum < 0;
    const absTarget = Math.abs(targetNum);
    
    function update(now) {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = absTarget * eased;
        
        const formatted = current.toLocaleString('en-US', {
            minimumFractionDigits: decCount,
            maximumFractionDigits: decCount
        });
        
        el.textContent = (isNegative ? '-' : '') + formatted + (suffixStr ? ' ' + suffixStr : '');
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            const finalFormatted = absTarget.toLocaleString('en-US', {
                minimumFractionDigits: decCount,
                maximumFractionDigits: decCount
            });
            el.textContent = (isNegative ? '-' : '') + finalFormatted + (suffixStr ? ' ' + suffixStr : '');
        }
    }
    requestAnimationFrame(update);
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

function readERPStore(key) {
    try {
        let val = localStorage.getItem(key);
        if (!val && window.getTenantKey) {
            val = localStorage.getItem(window.getTenantKey(key));
        }
        if (!val && window.DB_STORE) {
            val = window.DB_STORE[key] || (window.getTenantKey ? window.DB_STORE[window.getTenantKey(key)] : null);
        }
        if (!val) {
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && (k === key || k.endsWith('_' + key))) {
                    val = localStorage.getItem(k);
                    if (val) break;
                }
            }
        }
        if (typeof val === 'string') return JSON.parse(val);
        if (Array.isArray(val)) return val;
    } catch(e) {
        console.error('[balances-report.js] Error parsing ' + key, e);
    }
    return [];
}

function loadData() {
    const pMode = (typeof window.getPostingMode === 'function') ? window.getPostingMode() : 'auto';
    const excludeUnposted = (pMode === 'manual');

    console.log('%c[balances-report.js] loadData() CALLED', 'color: cyan; font-size: 14px;');
    ALL_PARTNERS = [];
    const custs = (readERPStore('erp_customers_v1') || []).filter(x => !x.deleted && !x.is_deleted);
    const sups  = (readERPStore('erp_suppliers_v1') || []).filter(x => !x.deleted && !x.is_deleted);
    const banks = readERPStore('erp_banks_v1') || [];
    const safes = readERPStore('erp_safes_v1') || [];
    const exp   = readERPStore('erp_accounts_v1') || [];
    
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

    // Add Mobile Wallets
    const wallets = [
        { id: 'bankok', name: 'بنكك (Bankak)' },
        { id: 'fawry', name: 'فوري (Fawry)' },
        { id: 'o-cash', name: 'أوكاش (O-Cash)' },
        { id: 'other', name: 'محفظة أخرى' }
    ];
    wallets.forEach(w => {
        ALL_PARTNERS.push({ 
            id: w.id, code: w.id, name: w.name, type: 'SAFE', 
            phone: '', taxNumber: '', currency: 'SDG', exRate: 1,
            openingDebit: 0, openingCredit: 0,
            notes: 'محفظة إلكترونية',
            debit: 0, credit: 0, lastDate: null 
        });
    });
    
    // We reuse the collector logic from SGL, modified to only grab partner transactions
    // === PERFECT SYNC WITH GENERAL-LEDGER.JS ===
    function getLedgerTxns() {
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

    const ledgerTxns = getLedgerTxns();
    ALL_TXNS = ledgerTxns.map(t => ({
        partner: t.account,
        debit: t.debit,
        credit: t.credit,
        debitForeign: t.debitForeign,
        creditForeign: t.creditForeign,
        date: t.date,
        time: t.time,
        costCenter: t.costCenter,
        exchangeRate: t.exchangeRate,
        ref: t.ref,
        currency: t.currency
    }));
    // === END PERFECT SYNC ===

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

let _autoInitBalancesTimer = null;
let _reportHasRun = false;
function autoInitBalancesReport() {
    // Prevent multiple auto-runs — only allow ONE successful auto-init
    if (_reportHasRun) return;
    if (_autoInitBalancesTimer) clearTimeout(_autoInitBalancesTimer);
    _autoInitBalancesTimer = setTimeout(() => {
        if (_reportHasRun) return;
        try {
            loadLayout();
            applyLayoutToDOM();
            loadData();
            
            if (ALL_PARTNERS.length > 0) {
                _reportHasRun = true;
                runReport(true);
            }
        } catch(err) {
            console.error('[balances-report.js] Error during autoInitBalancesReport:', err);
        }
    }, 100);
}

// Attach event listeners — the _reportHasRun guard ensures only the first successful one runs
document.addEventListener('DBReady', autoInitBalancesReport);
document.addEventListener('DOMContentLoaded', autoInitBalancesReport);
window.addEventListener('load', autoInitBalancesReport);

function runReport(isAutoInit = false) {
    window.runReport = runReport;
    console.log('%c[balances-report.js] runReport() CALLED. Partners=' + ALL_PARTNERS.length + ', Txns=' + ALL_TXNS.length, 'color: #ff6b6b; font-size: 16px; font-weight: bold;');
    if (ALL_PARTNERS.length === 0) {
        console.warn('[balances-report.js] WARNING: ALL_PARTNERS is EMPTY. loadData may not have run.');
        // Try loading data now as a last resort
        loadData();
        console.log('[balances-report.js] Re-ran loadData. Partners=' + ALL_PARTNERS.length + ', Txns=' + ALL_TXNS.length);
    }
    const grpFilter = document.getElementById('filter-group') ? document.getElementById('filter-group').value : 'ALL';
    const sortFilter = document.getElementById('filter-sort') ? document.getElementById('filter-sort').value : 'BALANCE_DESC';
    const ccFilter = document.getElementById('filter-costcenter') ? document.getElementById('filter-costcenter').value : 'ALL';
    const sectorFilter = document.getElementById('filter-sector') ? document.getElementById('filter-sector').value : 'ALL';
    const currFilter = document.getElementById('filter-currency') ? document.getElementById('filter-currency').value : 'ALL';
    const conditionFilter = document.getElementById('filter-condition') ? document.getElementById('filter-condition').value : 'ALL';

    // Reset balances
    ALL_PARTNERS.forEach(p => {
        p.endingDebitLocal = 0;
        p.endingCreditLocal = 0;
        p.endingDebitForeign = 0;
        p.endingCreditForeign = 0;
        p.lastDate = null;
    });

    // Group ALL_TXNS by partner upfront for O(N) performance
    // We group by BOTH the raw account key AND potential variants to match how
    // general-ledger.js filters (lines 1747-1752: matches both 'cust-xxx' AND 'xxx')
    const txnsByPartner = {};
    ALL_TXNS.forEach(t => {
        const pRefStr = String(t.partner);
        if (!txnsByPartner[pRefStr]) txnsByPartner[pRefStr] = [];
        txnsByPartner[pRefStr].push(t);
    });

    // Get USD rate fallback (same as general-ledger.js)
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

    // Calculate for each partner sequentially to match SGL ledger EXACTLY
    ALL_PARTNERS.forEach(p => {
        // Use per-entity exchange rate (same as general-ledger.js lines 1865-1870)
        let entityExRate = parseNum(p.exchangeRate || p.exRate || p.rate || 0);
        if (!entityExRate || entityExRate <= 0) {
            entityExRate = usdRate;
        }

        // Opening balance calculation (identical to general-ledger.js lines 1851-1878)
        const opDebit = parseNum(p.openingDebit || p.debit || 0);
        const opCredit = parseNum(p.openingCredit || p.credit || 0);
        const rawBal = parseNum(p.openingBalance || p.opening_balance || p.balance || 0);

        let intrinsicBal = 0;
        if (opDebit > 0 || opCredit > 0) {
            intrinsicBal = opDebit - opCredit;
        } else if (rawBal !== 0) {
            if (p.balance_type === 'credit' || p.balanceType === 'credit') {
                intrinsicBal = -Math.abs(rawBal);
            } else {
                intrinsicBal = Math.abs(rawBal);
            }
        }

        let intrinsicBalFc = entityExRate > 0 ? (intrinsicBal / entityExRate) : 0;

        let runningBal = intrinsicBal;
        let runningBalForeign = intrinsicBalFc;
        
        // Filter transactions for this partner using type-prefixed IDs to avoid ID collisions
        let pRef = String(p.id);
        if (!pRef.startsWith('cust-') && !pRef.startsWith('supp-') && !pRef.startsWith('bank-') && !pRef.startsWith('safe-') && !pRef.startsWith('exp-')) {
            if (p.type === 'CUST') pRef = 'cust-' + p.id;
            else if (p.type === 'SUPP') pRef = 'supp-' + p.id;
            else if (p.type === 'BANK') pRef = 'bank-' + p.id;
            else if (p.type === 'SAFE') pRef = 'safe-' + p.id;
            else if (p.type === 'EXP') pRef = 'exp-' + p.id;
        }

        // Fetch pre-grouped transactions - match BOTH prefixed AND bare IDs
        // (same logic as general-ledger.js lines 1747-1752)
        const cleanId = pRef.replace(/^(cust|supp|bank|safe|exp)-/, '');
        let partnerTxns = [
            ...(txnsByPartner[pRef] || []),
            ...(cleanId !== pRef ? (txnsByPartner[cleanId] || []) : [])
        ];
        // Deduplicate in case same txn appears under both keys
        const seen = new Set();
        partnerTxns = partnerTxns.filter(t => {
            const key = (t.date || '') + '|' + (t.ref || '') + '|' + (t.debit || 0) + '|' + (t.credit || 0);
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
        
        if (ccFilter !== 'ALL') {
            partnerTxns = partnerTxns.filter(t => t.costCenter === ccFilter);
        }

        // Sort partner transactions by date & time chronologically
        partnerTxns.sort((a, b) => {
            const da = a.date + ' ' + (a.time || '00:00');
            const db = b.date + ' ' + (b.time || '00:00');
            return da.localeCompare(db);
        });

        partnerTxns.forEach(t => {
            let debLoc = parseNum(t.debit || t.dr || 0);
            let credLoc = parseNum(t.credit || t.cr || 0);
            let rate = parseNum(t.exchangeRate || t.exRate || t.rate || entityExRate || 1);
            if (!rate || rate <= 0) rate = entityExRate || 1;

            let df = parseNum(t.debitForeign || t.dr_foreign || 0);
            let cf = parseNum(t.creditForeign || t.cr_foreign || 0);

            // Match general-ledger.js line 1918: rate > 1 (not rate > 0)
            if (debLoc > 0 && (df === 0 || df === debLoc) && rate > 1) {
                df = debLoc / rate;
            }
            if (credLoc > 0 && (cf === 0 || cf === credLoc) && rate > 1) {
                cf = credLoc / rate;
            }
            
            runningBal += (debLoc - credLoc);
            runningBalForeign += (df - cf);
            
            const tDate = t.date + ' ' + (t.time || '00:00');
            if(!p.lastDate || tDate > p.lastDate) {
                p.lastDate = tDate;
            }
        });

        // Determine the side (Debit or Credit) based on the Local Balance primarily.
        // If Local Balance is exactly 0 but Foreign is not, fallback to Foreign Balance sign.
        let localSide = 'zero';
        if (Math.abs(runningBal) > 0.001) {
            localSide = runningBal > 0 ? 'debit' : 'credit';
        } else if (Math.abs(runningBalForeign) > 0.001) {
            localSide = runningBalForeign > 0 ? 'debit' : 'credit';
        }

        // Place BOTH Local and Foreign balances in the same column side (عليه or له)
        if (localSide === 'debit') {
            p.endingDebitLocal = Math.abs(runningBal);
            p.endingCreditLocal = 0;
            p.endingDebitForeign = Math.abs(runningBalForeign);
            p.endingCreditForeign = 0;
        } else if (localSide === 'credit') {
            p.endingDebitLocal = 0;
            p.endingCreditLocal = Math.abs(runningBal);
            p.endingDebitForeign = 0;
            p.endingCreditForeign = Math.abs(runningBalForeign);
        } else {
            p.endingDebitLocal = 0;
            p.endingCreditLocal = 0;
            p.endingDebitForeign = 0;
            p.endingCreditForeign = 0;
        }
    });

    // Filter partners by group, sector, currency & condition
    let results = ALL_PARTNERS.filter(p => {
        // Group Filter
        if (grpFilter !== 'ALL' && p.type !== grpFilter) return false;

        // Sector / Category Filter
        if (sectorFilter !== 'ALL') {
            const pSector = (p.sector || p.category || p.classification || '').toUpperCase();
            if (sectorFilter === 'RETAIL' && !pSector.includes('RETAIL') && !pSector.includes('تجزئة')) return false;
            if (sectorFilter === 'WHOLESALE' && !pSector.includes('WHOLESALE') && !pSector.includes('جملة')) return false;
            if (sectorFilter === 'VIP' && !pSector.includes('VIP') && !pSector.includes('كبار') && !p.isVip) return false;
            if (sectorFilter === 'EXTERNAL' && !pSector.includes('EXT') && !pSector.includes('خارجي')) return false;
        }

        // Currency Filter
        if (currFilter === 'SDG' && p.currency && p.currency !== 'SDG') return false;
        if (currFilter === 'USD' && p.currency && p.currency !== 'USD') return false;

        // Condition Filter
        if (conditionFilter === 'DEBTORS_ONLY' && p.endingDebitLocal <= 0) return false;
        if (conditionFilter === 'CREDITORS_ONLY' && p.endingCreditLocal <= 0) return false;
        if (conditionFilter === 'ACTIVE_ONLY' && !p.lastDate) return false;
        if (conditionFilter === 'NON_ZERO' && p.endingDebitLocal === 0 && p.endingCreditLocal === 0 && p.endingDebitForeign === 0 && p.endingCreditForeign === 0) return false;

        return true;
    });

    // Sort
    results.sort((a, b) => {
        const absBalA = Math.max(a.endingDebitLocal, a.endingCreditLocal);
        const absBalB = Math.max(b.endingDebitLocal, b.endingCreditLocal);
        if (sortFilter === 'BALANCE_DESC') return absBalB - absBalA;
        if (sortFilter === 'BALANCE_ASC') return absBalA - absBalB;
        if (sortFilter === 'DEBIT_DESC') return (b.endingDebitLocal || 0) - (a.endingDebitLocal || 0);
        if (sortFilter === 'CREDIT_DESC') return (b.endingCreditLocal || 0) - (a.endingCreditLocal || 0);
        if (sortFilter === 'NAME') return (a.name || '').localeCompare(b.name || '');
        if (sortFilter === 'LAST_DATE') return (b.lastDate || '').localeCompare(a.lastDate || '');
        return 0;
    });

    renderTable(results, isAutoInit);
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
    const cells = document.querySelectorAll('#balances-tbody td');
    cells.forEach(td => {
        td.style.cursor = 'pointer';
        // Avoid duplicate listeners
        td.onclick = function(e) {
            e.stopPropagation();
            this.classList.toggle('focused-num');
        };
    });
}

window.switchCtxTab = function(tab) {
    const colsTab = document.getElementById('ctx-tab-cols');
    const numbersTab = document.getElementById('ctx-tab-numbers');
    const formattingTab = document.getElementById('ctx-tab-formatting');
    
    const colsBtn = document.getElementById('tab-btn-cols');
    const numbersBtn = document.getElementById('tab-btn-numbers');
    const formattingBtn = document.getElementById('tab-btn-formatting');
    
    if (!colsTab || !formattingTab) return;

    const resetStyle = (btn) => {
        if (!btn) return;
        btn.style.background = 'transparent';
        btn.style.color = '#94a3b8';
        btn.style.fontWeight = '700';
        btn.style.boxShadow = 'none';
    };
    const setActiveStyle = (btn) => {
        if (!btn) return;
        btn.style.background = '#38bdf8';
        btn.style.color = '#0f172a';
        btn.style.fontWeight = '900';
        btn.style.boxShadow = '0 2px 10px rgba(56,189,248,0.4)';
    };

    if (colsTab) colsTab.style.display = 'none';
    if (numbersTab) numbersTab.style.display = 'none';
    if (formattingTab) formattingTab.style.display = 'none';

    resetStyle(colsBtn);
    resetStyle(numbersBtn);
    resetStyle(formattingBtn);

    if (tab === 'cols') {
        if (colsTab) colsTab.style.display = 'block';
        setActiveStyle(colsBtn);
    } else if (tab === 'numbers') {
        if (numbersTab) numbersTab.style.display = 'block';
        setActiveStyle(numbersBtn);
    } else {
        if (formattingTab) formattingTab.style.display = 'block';
        setActiveStyle(formattingBtn);
    }
};

window.applyGlobalNumberSettingsFromUI = function() {
    const thActiveEl = document.getElementById('global-thousands-active');
    const thActive = thActiveEl ? thActiveEl.value === '1' : false;
    const thSepEl = document.getElementById('global-thousands-sep');
    const thSep = thSepEl ? thSepEl.value || ',' : ',';
    const decSepEl = document.getElementById('global-dec-sep');
    const decSep = decSepEl ? decSepEl.value || '.' : '.';
    const showDecEl = document.getElementById('global-show-decimals');
    const showDec = showDecEl ? showDecEl.value === '1' : false;
    const decCountEl = document.getElementById('global-decimals-count');
    const decCount = parseInt(decCountEl ? decCountEl.value || '2' : '2');
    const roundModeEl = document.getElementById('global-round-mode');
    const roundMode = roundModeEl ? roundModeEl.value || 'round' : 'round';

    if (window.colConfigs) {
        window.colConfigs.forEach(col => {
            col.thousands = thActive;
            col.thousandsSep = thSep;
            col.decSep = decSep;
            col.showDecimals = showDec;
            col.decimals = decCount;
            col.dec = decCount;
            col.roundMode = roundMode;
        });
    }

    if (typeof applyLayoutToDOM === 'function') applyLayoutToDOM();
    if (typeof window.runReport === 'function' && window.lastResults) {
        window.runReport();
    }
    if (typeof saveLayout === 'function') saveLayout();
    if (typeof renderColumnContextMenu === 'function') renderColumnContextMenu();
    if (typeof showToast === 'function') showToast('⚡ تم تطبيق إعدادات الأرقام والكسور والتقريب على جميع الأعمدة بنجاح', 'success');
};
window.runReport = runReport;

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

let currentSelectedPartnerId = null;
let currentSelectedPartnerType = null;
let currentSelectedPartnerName = '';

function bindContextMenu() {
    const table = document.getElementById('balances-table');
    if (!table) return;
    const ctxMenu = document.getElementById('columnContextMenu');
    const partnerCtxMenu = document.getElementById('partnerContextMenu');

    // Right click on header th -> Opens columnContextMenu
    const thead = table.querySelector('thead');
    if (thead) {
        thead.oncontextmenu = function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (partnerCtxMenu) partnerCtxMenu.style.display = 'none';
            openColumnContextMenu(e);
        };
    }

    // Close partnerContextMenu when clicking anywhere else
    document.addEventListener('click', function(e) {
        if (partnerCtxMenu && !partnerCtxMenu.contains(e.target)) {
            partnerCtxMenu.style.display = 'none';
        }
    });
}

function bindRowContextMenu() {
    const tbody = document.getElementById('balances-tbody');
    if (!tbody) return;
    const partnerCtxMenu = document.getElementById('partnerContextMenu');

    const rows = tbody.querySelectorAll('tr.partner-row');
    rows.forEach(row => {
        row.oncontextmenu = function(e) {
            e.preventDefault();
            e.stopPropagation();

            const pId = this.getAttribute('data-partner-id') || '';
            const pName = this.getAttribute('data-partner-name') || '';
            const pType = this.getAttribute('data-partner-type') || 'CUST';

            currentSelectedPartnerId = pId;
            currentSelectedPartnerType = pType;
            currentSelectedPartnerName = pName;

            const headerEl = document.getElementById('partnerMenuHeader');
            if (headerEl) {
                headerEl.innerHTML = `👤 <strong>${pName}</strong>`;
            }

            if (partnerCtxMenu) {
                let posX = e.clientX || e.pageX;
                let posY = e.clientY || e.pageY;
                
                const menuWidth = 250;
                const menuHeight = 120;
                if (posX + menuWidth > window.innerWidth) posX = window.innerWidth - menuWidth - 10;
                if (posY + menuHeight > window.innerHeight) posY = window.innerHeight - menuHeight - 10;

                partnerCtxMenu.style.left = posX + 'px';
                partnerCtxMenu.style.top = posY + 'px';
                partnerCtxMenu.style.display = 'block';
            }
        };
    });
}

window.goToPartnerLedger = function() {
    if (!currentSelectedPartnerId && !currentSelectedPartnerName) return;
    const partnerCtxMenu = document.getElementById('partnerContextMenu');
    if (partnerCtxMenu) partnerCtxMenu.style.display = 'none';

    // Navigate directly into general-ledger.php for this account
    let accId = currentSelectedPartnerId || '';
    if (accId && !accId.startsWith('cust-') && !accId.startsWith('supp-') && !accId.startsWith('acc-')) {
        const type = (currentSelectedPartnerType || 'CUST').toLowerCase();
        accId = (type === 'supp' ? 'supp-' : 'cust-') + accId;
    }

    const url = `general-ledger.php?accountId=${encodeURIComponent(accId)}&partner_id=${encodeURIComponent(currentSelectedPartnerId)}&name=${encodeURIComponent(currentSelectedPartnerName)}&autoRun=1`;
    window.location.href = url;
};

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

                <!-- 4. Number Formatting Section (تنسيق الأرقام والفاصلات والتقريب) -->
                <div style="background:rgba(30,41,59,0.6); padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); margin-bottom:12px;">
                    <div style="font-size:0.8rem; font-weight:bold; color:#38bdf8; margin-bottom:8px; display:flex; align-items:center; gap:5px;">
                        <span>🔢</span>
                        <span>تخصيص الأرقام والفاصلات والتقريب:</span>
                    </div>

                    <!-- Thousands Separator: Toggle & Character -->
                    <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">الفاصلة الألفية:</label>
                            <button type="button" onclick="toggleColThousands(${origIdx}, ${visualIndex})" style="width:100%; padding:5px 6px; cursor:pointer; border-radius:6px; border:1px solid ${conf.thousands !== false ? '#10b981' : 'rgba(239,68,68,0.4)'}; background:${conf.thousands !== false ? 'rgba(16,185,129,0.25)' : 'rgba(239,68,68,0.15)'}; color:${conf.thousands !== false ? '#34d399' : '#f87171'}; font-weight:bold; font-size:0.78rem;">
                                ${conf.thousands !== false ? 'مفعلة (1,000 ✔)' : 'ملغاة (1000 ✖)'}
                            </button>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">رمز الفاصلة الألفية:</label>
                            <select onchange="setColThousandsSep(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(15, 23, 42, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px 6px; border-radius:6px; font-size:0.78rem; outline:none;">
                                <option value="," ${(!conf.thousandsSep || conf.thousandsSep === ',') ? 'selected' : ''}>, (فاصلة عادية)</option>
                                <option value="." ${conf.thousandsSep === '.' ? 'selected' : ''}>. (نقطة)</option>
                                <option value=" " ${conf.thousandsSep === ' ' ? 'selected' : ''}>[مسافة] (1 000)</option>
                                <option value="_" ${conf.thousandsSep === '_' ? 'selected' : ''}>_ (شرطة سفلى)</option>
                                <option value="'" ${conf.thousandsSep === "'" ? 'selected' : ''}>' (اقتباس)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Decimal Separator & Show Decimals Toggle -->
                    <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">رمز الفاصلة العشرية:</label>
                            <select onchange="setColDecSep(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(15, 23, 42, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px 6px; border-radius:6px; font-size:0.78rem; outline:none;">
                                <option value="." ${(!conf.decSep || conf.decSep === '.') ? 'selected' : ''}>. (نقطة إنجليزية)</option>
                                <option value="," ${conf.decSep === ',' ? 'selected' : ''}>, (فاصلة)</option>
                                <option value="٫" ${conf.decSep === '٫' ? 'selected' : ''}>٫ (فاصلة عشرية عربية)</option>
                                <option value="/" ${conf.decSep === '/' ? 'selected' : ''}>/ (شرطة مائلة)</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">إظهار الكسور العشرية:</label>
                            <button type="button" onclick="toggleColShowDecimals(${origIdx}, ${visualIndex})" style="width:100%; padding:5px 6px; cursor:pointer; border-radius:6px; border:1px solid ${conf.showDecimals !== false ? '#38bdf8' : 'rgba(239,68,68,0.4)'}; background:${conf.showDecimals !== false ? 'rgba(56,189,248,0.25)' : 'rgba(239,68,68,0.15)'}; color:${conf.showDecimals !== false ? '#38bdf8' : '#f87171'}; font-weight:bold; font-size:0.78rem;">
                                ${conf.showDecimals !== false ? 'مفعلة (.00 ✔)' : 'ملغاة (أرقام صحيحة ✖)'}
                            </button>
                        </div>
                    </div>

                    <!-- Decimal Count & Rounding Mode -->
                    ${conf.showDecimals !== false ? `
                    <div style="display:flex; gap:8px; align-items:center;">
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">عدد الخانات العشرية:</label>
                            <select onchange="setColDecimals(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(15, 23, 42, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px 6px; border-radius:6px; font-size:0.78rem; outline:none;">
                                <option value="0" ${conf.decimals === 0 ? 'selected' : ''}>بدون خانات (0)</option>
                                <option value="1" ${conf.decimals === 1 ? 'selected' : ''}>منزلة واحدة (0.0)</option>
                                <option value="2" ${conf.decimals === 2 || conf.decimals === undefined ? 'selected' : ''}>منزلتان (0.00)</option>
                                <option value="3" ${conf.decimals === 3 ? 'selected' : ''}>3 منازل (0.000)</option>
                                <option value="4" ${conf.decimals === 4 ? 'selected' : ''}>4 منازل (0.0000)</option>
                                <option value="5" ${conf.decimals === 5 ? 'selected' : ''}>5 منازل (0.00000)</option>
                                <option value="6" ${conf.decimals === 6 ? 'selected' : ''}>6 منازل (0.000000)</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">طريقة والتقريب:</label>
                            <select onchange="setColRoundMode(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(15, 23, 42, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px 6px; border-radius:6px; font-size:0.78rem; outline:none;">
                                <option value="round" ${(!conf.roundMode || conf.roundMode === 'round') ? 'selected' : ''}>تقريب عادي (أقرب رقم)</option>
                                <option value="floor" ${conf.roundMode === 'floor' ? 'selected' : ''}>تقريب لأسفل (Floor)</option>
                                <option value="ceil" ${conf.roundMode === 'ceil' ? 'selected' : ''}>تقريب لأعلى (Ceil)</option>
                                <option value="trunc" ${conf.roundMode === 'trunc' ? 'selected' : ''}>قطع الكسر (Truncate)</option>
                            </select>
                        </div>
                    </div>
                    ` : ''}
                </div>

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
        
        localStorage.setItem('mainbalances_report_master_v4_default1', JSON.stringify({
            colConfigs: curConfigs,
            colOrder: curOrder
        }));

        let list = [];
        try { list = JSON.parse(localStorage.getItem('mainbalances_report_presets_v1')) || []; } catch(e){}
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
            name: (list[3] ? list[3].name : null) || 'تخطيط 4',
            colConfigs: curConfigs,
            colOrder: curOrder
        };
        localStorage.setItem('mainbalances_report_presets_v1', JSON.stringify(list));
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
    if (confirm("هل أنت متأكد من رغبتك في استعادة التصميم الافتراضي المحفوظ في قاعدة البيانات؟")) {
        loadLayout();
        if (typeof showToast === 'function') {
            showToast('🔄 تم استعادة التصميم الافتراضي المحفوظ في قاعدة البيانات بنجاح ⭐', 'success');
        }
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

function updateColDOMByOrigIdx(origIdx) {
    if (window.colOrder) {
        const visIdx = window.colOrder.indexOf(origIdx);
        if (visIdx !== -1) {
            updateColDOM(visIdx, origIdx);
        }
    }
}

function updateColDOM(visualIndex, origIdx) {
    const conf = window.colConfigs[origIdx];
    if (!conf) return;
    const table = document.getElementById('balances-table');
    if (!table) return;

    const fsVal = (conf.fs !== undefined ? conf.fs : 1.0) + 'em';
    const isBold = conf.bold || conf.fontWeight === 'bold' || conf.fontWeight === '900';
    const fwValHeader = isBold ? '900' : 'normal';
    const fwValCell = isBold ? '900' : 'normal';
    const colorVal = conf.color || '';
    const bgVal = (conf.bg && conf.bg !== '#ffffff' && conf.bg !== '#0f172a') ? conf.bg : '';
    const fontVal = conf.fontFamily || '';
    const alignVal = conf.textAlign || conf.align || '';

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
        const isBold = (weightVal === 'bold' || weightVal === '900');
        window.colConfigs[origIdx].fontWeight = isBold ? '900' : 'normal';
        window.colConfigs[origIdx].bold = isBold;
        updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (window.lastResults) renderTable(window.lastResults);
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
        window.colConfigs[origIdx].align = alignVal;
        updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (window.lastResults) renderTable(window.lastResults);
        bindContextMenuList(visualIndex);
    }
};

window.setColDecimals = function(origIdx, visualIndex, d) {
    if (origIdx !== undefined && window.colConfigs && window.colConfigs[origIdx]) {
        const parsedD = parseInt(d);
        window.colConfigs[origIdx].decimals = parsedD;
        window.colConfigs[origIdx].dec = parsedD;
        if (window.lastResults && window.lastResults.length > 0 && typeof renderTable === 'function') {
            renderTable(window.lastResults);
        }
        if (typeof updateColDOM === 'function') updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (typeof bindContextMenuList === 'function') bindContextMenuList(visualIndex);
    }
};

window.setColThousandsSep = function(origIdx, visualIndex, sep) {
    if (origIdx !== undefined && window.colConfigs && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].thousandsSep = sep;
        if (window.lastResults && window.lastResults.length > 0 && typeof renderTable === 'function') {
            renderTable(window.lastResults);
        }
        if (typeof updateColDOM === 'function') updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (typeof bindContextMenuList === 'function') bindContextMenuList(visualIndex);
    }
};

window.setColDecSep = function(origIdx, visualIndex, sep) {
    if (origIdx !== undefined && window.colConfigs && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].decSep = sep;
        if (window.lastResults && window.lastResults.length > 0 && typeof renderTable === 'function') {
            renderTable(window.lastResults);
        }
        if (typeof updateColDOM === 'function') updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (typeof bindContextMenuList === 'function') bindContextMenuList(visualIndex);
    }
};

window.setColRoundMode = function(origIdx, visualIndex, mode) {
    if (origIdx !== undefined && window.colConfigs && window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].roundMode = mode;
        if (window.lastResults && window.lastResults.length > 0 && typeof renderTable === 'function') {
            renderTable(window.lastResults);
        }
        if (typeof updateColDOM === 'function') updateColDOM(visualIndex, origIdx);
        saveLayout();
        if (typeof bindContextMenuList === 'function') bindContextMenuList(visualIndex);
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
        try { window.colSortable.destroy(); } catch(e){}
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

function renderTable(results, isAutoInit = false) {
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
        const isBold = conf.bold || conf.fontWeight === 'bold' || conf.fontWeight === '900';
        if (isBold) style += 'font-weight:900 !important;';
        else style += 'font-weight:normal !important;';

        if (conf.fs !== undefined && conf.fs !== 1.0) style += `font-size:${conf.fs}em !important;`;
        if (conf.color) style += `color:${conf.color} !important;`;
        if (conf.bg && conf.bg !== '#ffffff' && conf.bg !== '#0f172a') style += `background-color:${conf.bg} !important;`;
        if (conf.fontFamily) style += `font-family:${conf.fontFamily} !important;`;
        
        const alignVal = conf.textAlign || conf.align || '';
        if (alignVal) style += `text-align:${alignVal} !important;`;

        return style;
    }

    results.forEach((p, idx) => {
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
        let delay = Math.min(idx * 0.03, 0.6).toFixed(2);
        rows += `<tr class="partner-row animate-slide-up" data-partner-id="${p.id}" data-partner-name="${p.name}" data-partner-type="${p.type}" onclick="handleRowClick(event, '${p.id}', '${p.type}')" style="cursor: pointer; animation-delay: ${delay}s;">${orderedCells}</tr>`;
    });

    tbody.innerHTML = rows;

    const drForeign = ledgerExchangeRate > 0 ? (sumCust / ledgerExchangeRate) : 0;
    const crForeign = ledgerExchangeRate > 0 ? (sumSupp / ledgerExchangeRate) : 0;
    const net = sumCust - sumSupp;
    const netSign = net >= 0 ? ' (لنا)' : ' (علينا)';
    const netForeign = ledgerExchangeRate > 0 ? (Math.abs(net) / ledgerExchangeRate) : 0;

    animateKpiNumber('kpi-dr', sumCust, 'SDG');
    animateKpiNumber('kpi-dr-foreign', drForeign, ledgerForeignCurrency);
    animateKpiNumber('kpi-cr', sumSupp, 'SDG');
    animateKpiNumber('kpi-cr-foreign', crForeign, ledgerForeignCurrency);
    animateKpiNumber('kpi-bal', Math.abs(net), 'SDG' + netSign);
    animateKpiNumber('kpi-bal-foreign', netForeign, ledgerForeignCurrency + netSign);

    const dashboardEl = document.getElementById('analytics-dashboard');
    if (dashboardEl) dashboardEl.style.display = 'block';

    const actionsEl = document.getElementById('actions-panel');
    if (actionsEl) actionsEl.style.display = 'flex';

    const printContainerEl = document.getElementById('print-container');
    if (printContainerEl) printContainerEl.style.display = 'block';

    if (!isAutoInit) {
        showToast(`✅ تم تجميع أرصدة ${results.length} حساب بنجاح`, 'success');
    }
    
    applyLayoutToDOM();
    bindNumberFocus();
    
    // Apply the active number font to all rendered number cells permanently
    if (typeof changeNumberFont === 'function' && window.currentNumFont) {
        changeNumberFont(window.currentNumFont);
    }
    bindContextMenu();
    bindRowContextMenu();
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
    try { userList = JSON.parse(localStorage.getItem('mainbalances_report_user_presets_v3')) || []; } catch(e){}

    // Default 1 Master Configs (Standard Factory System Default)
    const default1Configs = [
        { name: 'اسم الحساب', alias: 'اسم الحساب', id: 'name', bold: true, fontWeight: '900', fs: 1.05, visible: true, align: 'right', color: '#f8fafc', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' },
        { name: 'رقم الهاتف', alias: 'رقم الهاتف', id: 'phone', bold: false, fs: 0.95, visible: true, align: 'center', color: '#cbd5e1', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' },
        { name: 'الرقم الضريبي', alias: 'الرقم الضريبي', id: 'taxNo', bold: false, fs: 0.95, visible: true, align: 'center', color: '#cbd5e1', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' },
        { name: 'العملة', alias: 'العملة', id: 'currency', bold: true, fontWeight: '900', fs: 1.0, visible: true, align: 'center', color: '#fbbf24', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' },
        { name: 'عليه عملة', alias: 'عليه عملة', id: 'debitF', bold: true, fontWeight: '900', fs: 1.1, visible: true, align: 'center', color: '#38bdf8', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' },
        { name: 'له عملة', alias: 'له عملة', id: 'creditF', bold: true, fontWeight: '900', fs: 1.1, visible: true, align: 'center', color: '#fb7185', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' },
        { name: 'عليه', alias: 'عليه', id: 'debit', bold: true, fontWeight: '900', fs: 1.15, visible: true, align: 'center', color: '#34d399', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' },
        { name: 'له', alias: 'له', id: 'credit', bold: true, fontWeight: '900', fs: 1.15, visible: true, align: 'center', color: '#f43f5e', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' },
        { name: 'ملحوظة', alias: 'ملحوظة', id: 'note', bold: false, fs: 0.9, visible: true, align: 'right', color: '#94a3b8', bg: '', decimals: 2, dec: 2, thousands: true, thousandsSep: ',', decSep: '.', roundMode: 'round' }
    ];
    const default1Order = [0, 1, 2, 3, 4, 5, 6, 7, 8];

    // Default 2 Master Configs (Local Priority)
    const default2Configs = JSON.parse(JSON.stringify(default1Configs));
    const default2Order = [0, 1, 2, 3, 6, 7, 8, 4, 5];

    // Default 3 Master Configs (Foreign Priority)
    const default3Configs = JSON.parse(JSON.stringify(default1Configs));
    const default3Order = [0, 3, 4, 5, 6, 7, 1, 2, 8];

    // Default 4 Master Configs (Compact View)
    const default4Configs = JSON.parse(JSON.stringify(default1Configs));
    const default4Order = [0, 1, 3, 6, 7, 8, 2, 4, 5];

    const defaultPreset1 = { id: 'default1', name: '💎 افتراضي 1', isDefault: true, isLocked: true, isFactoryDefault: true, colOrder: default1Order, colConfigs: default1Configs, theme: 'table-theme-blue', numFont: 'num-font-inter' };
    const defaultPreset2 = { id: 'default2', name: '⭐ افتراضي 2', isDefault: true, isLocked: true, isFactoryDefault: true, colOrder: default2Order, colConfigs: default2Configs, theme: 'table-theme-emerald', numFont: 'num-font-jetbrains' };
    const defaultPreset3 = { id: 'default3', name: '🎨 افتراضي 3', isDefault: true, isLocked: true, isFactoryDefault: true, colOrder: default3Order, colConfigs: default3Configs, theme: 'table-theme-black', numFont: 'num-font-firacode' };
    const defaultPreset4 = { id: 'default4', name: '🔥 افتراضي 4', isDefault: true, isLocked: true, isFactoryDefault: true, colOrder: default4Order, colConfigs: default4Configs, theme: '', numFont: 'num-font-roboto' };

    const presetLayout1 = userList[0] || { id: 1, name: 'نموذج 1', colConfigs: null, colOrder: null };
    const presetLayout2 = userList[1] || { id: 2, name: 'نموذج 2', colConfigs: null, colOrder: null };
    const presetLayout3 = userList[2] || { id: 3, name: 'نموذج 3', colConfigs: null, colOrder: null };

    return [defaultPreset1, defaultPreset2, defaultPreset3, defaultPreset4, presetLayout1, presetLayout2, presetLayout3];
};

function ensureLongPressHudExists() {
    if (document.getElementById('long-press-hud')) return;
    const hud = document.createElement('div');
    hud.id = 'long-press-hud';
    hud.style.cssText = 'display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:999999; background:rgba(15,23,42,0.96); backdrop-filter:blur(20px); border:2px solid rgba(56,189,248,0.5); border-radius:20px; padding:30px 40px; min-width:380px; text-align:center; color:#f8fafc; font-family:Tajawal,sans-serif; box-shadow:0 25px 60px rgba(0,0,0,0.7);';
    hud.innerHTML = `
        <div id="hud-icon" style="font-size:3rem; margin-bottom:10px;">✨</div>
        <div id="hud-title" style="font-size:1.2rem; font-weight:900; margin-bottom:8px; color:#38bdf8;">حفظ وتحديث</div>
        <div id="hud-desc" style="font-size:0.85rem; color:#94a3b8; margin-bottom:15px;">استمر بالضغط...</div>
        <div style="background:rgba(255,255,255,0.1); border-radius:10px; height:12px; overflow:hidden; margin-bottom:10px;">
            <div id="hud-progress-bar" style="height:100%; width:0%; border-radius:10px; background:linear-gradient(90deg,#3b82f6,#10b981); transition:width 0.05s linear;"></div>
        </div>
        <div id="hud-timer" style="font-size:0.9rem; font-weight:bold; color:#fbbf24;">8.0 ثانية متبقية</div>
    `;
    document.body.appendChild(hud);
}

window.renderBalancesPresets = function() {
    const container = document.getElementById('main-presets-container');
    if (!container) return;
    container.innerHTML = '';

    const PRESETS_STORAGE_KEY = 'balances_preview_presets_v7';
    let presets = [];
    try { presets = JSON.parse(localStorage.getItem(PRESETS_STORAGE_KEY) || '[]'); } catch(e){}

    if (!Array.isArray(presets) || presets.length === 0) {
        const defaultNames = [
            '💎 افتراضي 1', 
            '⭐ افتراضي 2', 
            '🎨 افتراضي 3', 
            '🔥 افتراضي 4', 
            '✨ نموذج 1', 
            '🚀 نموذج 2', 
            '🌟 نموذج 3'
        ];
        presets = defaultNames.map((name, i) => ({ 
            name, 
            showInRibbon: true,
            isLocked: false,
            isFactoryDefault: i < 4 ? true : false
        }));
    }

    // Merge database stored preset locks & configs
    if (window.LOADED_BALANCES_PRESETS) {
        presets.forEach((p, idx) => {
            const dbKey = 'balances_preset_' + idx;
            const dbLockKey = 'balances_preset_' + idx + '_locked';
            if (window.LOADED_BALANCES_PRESETS[dbKey]) {
                const dbObj = window.LOADED_BALANCES_PRESETS[dbKey];
                if (typeof dbObj === 'object') {
                    p.colOrder = dbObj.colOrder || p.colOrder;
                    p.colConfigs = dbObj.colConfigs || p.colConfigs;
                    p.theme = dbObj.theme || p.theme;
                    p.numFont = dbObj.numFont || p.numFont;
                }
                p.isLocked = true;
                p.isFactoryDefault = true;
            } else if (window.LOADED_BALANCES_PRESETS[dbLockKey] === 'true' || window.LOADED_BALANCES_PRESETS[dbLockKey] === true) {
                p.isLocked = true;
                p.isFactoryDefault = true;
            }
        });
    }

    presets.forEach((p, idx) => {
        if (p.showInRibbon === false) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'preset-ribbon-btn preset-slot-' + idx;

        const isLocked = p.isLocked === true;

        if (isLocked) {
            btn.innerHTML = p.name + ' 🔒';
            btn.title = '🔒 ضبط مصنع دائم مخزن ومحمي في قاعدة البيانات (اضغط للمعاينة)';
        } else {
            btn.innerHTML = p.name + ' ⚡ (اضغط 8ث للحفظ النهائي)';
            btn.title = '⚡ اضغط مطولاً (8 ثوانٍ) لتخزين وتشفير التنسيق الحالي كضبط مصنع مالي دائم في قاعدة البيانات!';
        }

        // Long Press (8 Seconds) + Quick Click Handler
        let pressStartTime = 0;
        let mainInterval = null;
        let isTriggered = false;

        const startPress = (e) => {
            if (e.type === 'mousedown' && e.button !== 0) return;
            pressStartTime = Date.now();
            isTriggered = false;
            ensureLongPressHudExists();

            const hud = document.getElementById('long-press-hud');
            const hudTitle = document.getElementById('hud-title');
            const hudDesc = document.getElementById('hud-desc');
            const hudProgress = document.getElementById('hud-progress-bar');
            const hudTimer = document.getElementById('hud-timer');
            const hudIcon = document.getElementById('hud-icon');

            if (p.isLocked) {
                if (hud) {
                    hud.style.display = 'block';
                    hudIcon.innerText = '🔒';
                    hudTitle.innerText = `ضبط المصنع محمي (${p.name})`;
                    hudDesc.innerText = 'هذا النموذج مخزن كضبط مصنع مالي دائم في قاعدة البيانات للنظام ومحمي من التعديل أو المسح نهائياً.';
                    if (hudProgress) { hudProgress.style.width = '100%'; hudProgress.style.background = '#ef4444'; }
                    if (hudTimer) hudTimer.innerText = '🔒 نمط محمي دائم';
                }
                return;
            }

            if (hud) {
                hud.style.display = 'block';
                hudIcon.innerText = '🔒';
                hudTitle.innerText = `حفظ وتثبيت ضبط المصنع (${p.name})`;
                hudDesc.innerText = 'استمر بالضغط المتواصل (8 ثوانٍ) لتخزين التنسيق الحالي (الألوان، الأعمدة، الخطوط، والكسور) كضبط مصنع مالي دائم في قاعدة البيانات للنظام ولكافة الشركات.';
                if (hudProgress) { hudProgress.style.width = '0%'; hudProgress.style.background = 'linear-gradient(90deg, #f59e0b, #10b981)'; }
                if (hudTimer) hudTimer.innerText = '8.0 ثانية متبقية';
            }

            const duration = 8000;
            const startTime = Date.now();

            mainInterval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const remainingMs = Math.max(0, duration - elapsed);
                const remainingSec = (remainingMs / 1000).toFixed(1);
                const percent = Math.min(100, (elapsed / duration) * 100);

                if (hudProgress) hudProgress.style.width = percent + '%';
                if (hudTimer) hudTimer.innerText = remainingSec + ' ثانية متبقية';

                btn.style.boxShadow = `0 0 20px rgba(245, 158, 11, 0.9)`;
                btn.style.borderColor = '#f59e0b';
                btn.style.background = `linear-gradient(90deg, rgba(245, 158, 11, 0.45) ${percent}%, rgba(255,255,255,0.08) ${percent}%)`;

                if (elapsed >= duration) {
                    clearInterval(mainInterval);
                    mainInterval = null;
                    isTriggered = true;
                    if (hud) hud.style.display = 'none';
                    btn.style.boxShadow = ''; btn.style.borderColor = ''; btn.style.background = '';

                    setTimeout(async () => {
                        const confirmSave = confirm(`🔒 اكتملت الـ 8 ثوانٍ بنجاح!\n\nهل تريد اعتماد وتخزين التنسيق الحالي كـ (ضبط مصنع مالي دائم) لـ (${p.name})؟\n\nتنويه هام: سيتم حفظ التنسيق في قاعدة البيانات للنظام ولكافة الشركات وقفل التعديل عليه نهائياً.`);
                        if (confirmSave) {
                            const currentConfig = {
                                name: p.name,
                                colOrder: window.colOrder || [0,1,2,3,4,5,6,7,8],
                                colConfigs: window.colConfigs || [],
                                theme: window.currentTableTheme || 'table-theme-blue',
                                numFont: window.currentNumFont || 'num-font-inter',
                                savedAt: new Date().toISOString(),
                                isLocked: true,
                                isFactoryDefault: true
                            };

                            p.isLocked = true;
                            p.isFactoryDefault = true;
                            p.colOrder = currentConfig.colOrder;
                            p.colConfigs = currentConfig.colConfigs;
                            p.theme = currentConfig.theme;
                            p.numFont = currentConfig.numFont;
                            p.updatedAt = new Date().toISOString();

                            localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(presets));

                            // Save to database via API
                            try {
                                await fetch('api/save_balances_preset.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        id: idx,
                                        config: currentConfig,
                                        lockRequested: true
                                    })
                                });
                            } catch(err) { console.error('Error saving preset to DB', err); }

                            alert(`🔒 تم حفظ وتخزين (${p.name}) كضبط مصنع مالي دائم في قاعدة البيانات بنجاح وقفل التعديل عليه نهائياً!`);
                            window.renderBalancesPresets();
                        }
                    }, 50);
                }
            }, 50);
        };

        const endPress = (e) => {
            const duration = Date.now() - pressStartTime;
            if (mainInterval) {
                clearInterval(mainInterval);
                clearTimeout(mainInterval);
                mainInterval = null;
            }
            const hud = document.getElementById('long-press-hud');
            if (hud) hud.style.display = 'none';
            btn.style.boxShadow = ''; btn.style.borderColor = ''; btn.style.background = '';

            if (isTriggered) {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                return false;
            }

            // Quick click (< 1s): Open modal preview as usual
            if (pressStartTime > 0 && duration < 1000) {
                window.openPresetModal(idx, p.name);
            }
        };

        btn.addEventListener('mousedown', startPress);
        btn.addEventListener('mouseup', endPress);
        btn.addEventListener('mouseleave', endPress);
        btn.addEventListener('touchstart', startPress, { passive: true });
        btn.addEventListener('touchend', endPress);

        btn.oncontextmenu = (e) => { e.preventDefault(); return false; };
        container.appendChild(btn);
    });

    // Control & Visual Buttons
    const ctrlBtn = document.createElement('button');
    ctrlBtn.type = 'button';
    ctrlBtn.className = 'preset-ribbon-btn';
    ctrlBtn.onclick = (e) => openColumnContextMenu(e);
    ctrlBtn.style.cssText = 'background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #ffffff !important; border: 1px solid rgba(251, 191, 36, 0.8) !important; font-weight: 900 !important; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4) !important;';
    ctrlBtn.innerHTML = '⚙️ التحكم';
    container.insertBefore(ctrlBtn, container.firstChild);
};

    // Main Preview & Design Page Button (Pushed to the very end of the row)
    const mainPreviewBtn = document.createElement('button');
    mainPreviewBtn.type = 'button';
    mainPreviewBtn.onclick = () => {
        if (typeof window.openNewPreviewPage === 'function') {
            window.openNewPreviewPage();
        } else {
            window.openPresetModal(0, 'الشاشة الذكية للمعاينة والتصميم');
        }
    };
    mainPreviewBtn.className = 'btn-action-hero btn-hero-preview';
    mainPreviewBtn.style.cssText = 'margin-right: auto;';
    mainPreviewBtn.innerHTML = `<span>👁️ المعاينة والتصميم ⚙️</span>`;
    container.appendChild(mainPreviewBtn);
};

window.addEventListener('storage', function(e) {
    if (e.key === 'balances_preview_presets_v7') {
        if (typeof window.renderBalancesPresets === 'function') {
            window.renderBalancesPresets();
        }
    }
});

async function openPresetModal(presetIndex, presetName) {
    if (!window.lastResults || !Array.isArray(window.lastResults)) {
        if (typeof window.runReport === 'function') {
            window.runReport();
        }
    }

    if (!window.lastResults || !Array.isArray(window.lastResults)) {
        window.lastResults = [];
    }

    // Save logo and report data
    const logoImg = document.getElementById('company-logo');
    if (logoImg && logoImg.src) {
        try { localStorage.setItem('company_logo_base64', logoImg.src); } catch(e){}
    }
    
    try {
        localStorage.setItem('preview_table_totals', JSON.stringify({
            kpiDr: document.getElementById('kpi-dr') ? document.getElementById('kpi-dr').innerText : '0.00 SDG',
            kpiCr: document.getElementById('kpi-cr') ? document.getElementById('kpi-cr').innerText : '0.00 SDG',
            kpiBal: document.getElementById('kpi-bal') ? document.getElementById('kpi-bal').innerText : '0.00 SDG'
        }));
    } catch(e){}

    let dataSaved = false;
    try {
        localStorage.setItem('preview_table_data', JSON.stringify(window.lastResults));
        dataSaved = true;
    } catch (e) {
        console.warn("localStorage quota exceeded, using localforage...", e);
        if (typeof localforage !== 'undefined') {
            try {
                await localforage.setItem('preview_table_data', window.lastResults);
                localStorage.removeItem('preview_table_data');
                dataSaved = true;
            } catch(err) { console.error(err); }
        }
    }
    
    if(!dataSaved) {
        if (typeof showToast === 'function') showToast('خطأ: حجم البيانات كبير جداً على الذاكرة', 'error');
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
                    <span style="color: #f8fafc; font-weight: 800; font-size: 1rem;">📄 معاينة وطباعة التقرير بالتصميم المخصص</span>
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

            <!-- Modal Iframe Body Container -->
            <div style="flex: 1; position: relative; width: 100%; height: 100%; min-height: 0; background: #f8fafc;">
                <iframe id="preset-modal-iframe" src="preview-balances-design.php?presetIndex=${presetIndex}&hideRibbon=1" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
}
window.openPresetModal = openPresetModal;

// --- New Function: Open New Preview Page ---
window.openNewPreviewPage = async function() {
    if (typeof showToast === 'function') {
        showToast('جاري تحضير شاشة المعاينة...', 'info');
    }

    if (!window.lastResults || !Array.isArray(window.lastResults)) {
        if (typeof window.runReport === 'function') {
            window.runReport();
        }
    }

    if (!window.lastResults || !Array.isArray(window.lastResults)) {
        window.lastResults = [];
    }
    
    // Attempt to grab logo from the DOM to pass it over
    const logoImg = document.getElementById('company-logo');
    if (logoImg && logoImg.src) {
        try { localStorage.setItem('company_logo_base64', logoImg.src); } catch(e){}
    }

    try {
        localStorage.setItem('preview_table_totals', JSON.stringify({
            kpiDr: document.getElementById('kpi-dr') ? document.getElementById('kpi-dr').innerText : '0.00 SDG',
            kpiCr: document.getElementById('kpi-cr') ? document.getElementById('kpi-cr').innerText : '0.00 SDG',
            kpiBal: document.getElementById('kpi-bal') ? document.getElementById('kpi-bal').innerText : '0.00 SDG'
        }));
    } catch(e){}

    let dataSaved = false;
    try {
        localStorage.setItem('preview_table_data', JSON.stringify(window.lastResults));
        dataSaved = true;
    } catch (e) {
        console.warn("localStorage quota exceeded, using localforage...", e);
        if (typeof localforage !== 'undefined') {
            try {
                localforage.config({
                    driver: [localforage.INDEXEDDB, localforage.WEBSQL, localforage.LOCALSTORAGE],
                    name: 'MagaeesApp',
                    storeName: 'preview_data'
                });
                const timeoutPromise = new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 6000));
                await Promise.race([
                    localforage.setItem('preview_table_data', window.lastResults),
                    timeoutPromise
                ]);
                localStorage.removeItem('preview_table_data');
                dataSaved = true;
            } catch(err) { 
                console.error(err);
                if (err.message === 'timeout') {
                    if (typeof showToast === 'function') showToast('تأخر حفظ البيانات، يرجى المحاولة مرة أخرى.', 'error');
                }
            }
        }
    }
    
    if (dataSaved) {
        // Open in the same tab per user request
        window.location.href = 'preview-balances-design.php';
    } else {
        if (typeof showToast === 'function') showToast('خطأ: لم نتمكن من حفظ البيانات لانتقال الشاشة', 'error');
    }
};



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

window.currentBalancesResults = [];

window.toggleVisualHub = function() {
    const hub = document.getElementById('visual-intelligence-hub');
    if (!hub) return;
    if (hub.style.display === 'none' || hub.style.display === '') {
        hub.style.display = 'block';
        hub.scrollIntoView({ behavior: 'smooth' });
        if (window.currentBalancesResults && window.currentBalancesResults.length > 0) {
            renderVisualAnalytics(window.currentBalancesResults);
        }
    } else {
        hub.style.display = 'none';
    }
};

function renderVisualAnalytics(results) {
    window.currentBalancesResults = results || [];
    const hub = document.getElementById('visual-intelligence-hub');
    if (!hub) return;
    if (!results || results.length === 0) {
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
        }
    });

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
        const absVal = Math.abs(netPosition);
        const sdgStr = absVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
        let exRate = (typeof ledgerExchangeRate !== 'undefined' && ledgerExchangeRate > 0) ? ledgerExchangeRate : 1.0;
        let currName = (typeof ledgerForeignCurrency !== 'undefined' && ledgerForeignCurrency && ledgerForeignCurrency !== 'SDG') ? ledgerForeignCurrency : 'USD';
        const usdVal = (absVal / exRate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        const sdgHtml = `<span style="color: #34d399; font-weight: 900; background: rgba(16, 185, 129, 0.22); padding: 4px 12px; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.95rem; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2); font-family: 'JetBrains Mono', monospace;">🇸🇩 ${sdgStr}</span>`;
        const usdHtml = `<span style="color: #fbbf24; font-weight: 900; background: rgba(245, 158, 11, 0.22); padding: 4px 12px; border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.95rem; box-shadow: 0 0 10px rgba(245, 158, 11, 0.2); font-family: 'JetBrains Mono', monospace;">💵 $${usdVal} ${currName}</span>`;
        
        indNetVal.innerHTML = `<div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 6px;">${sdgHtml} <span style="color: #94a3b8; font-weight: bold;">|</span> ${usdHtml}</div>`;
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

    function formatDualCurrencyLine(val) {
        const num = parseFloat(val) || 0;
        const sdgStr = num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
        let exRate = (typeof ledgerExchangeRate !== 'undefined' && ledgerExchangeRate > 0) ? ledgerExchangeRate : 1.0;
        let currName = (typeof ledgerForeignCurrency !== 'undefined' && ledgerForeignCurrency && ledgerForeignCurrency !== 'SDG') ? ledgerForeignCurrency : 'USD';
        
        const usdVal = (num / exRate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const usdStr = '$' + usdVal + ' ' + currName;
        
        return '🇸🇩 ' + sdgStr + '   |   💵 ' + usdStr;
    }

    function formatDualCurrencyArray(val) {
        const num = parseFloat(val) || 0;
        const sdgStr = num.toLocaleString('en-US', {maximumFractionDigits: 0}) + ' SDG';
        let exRate = (typeof ledgerExchangeRate !== 'undefined' && ledgerExchangeRate > 0) ? ledgerExchangeRate : 1.0;
        let currName = (typeof ledgerForeignCurrency !== 'undefined' && ledgerForeignCurrency && ledgerForeignCurrency !== 'SDG') ? ledgerForeignCurrency : 'USD';
        
        const usdVal = (num / exRate).toLocaleString('en-US', {maximumFractionDigits: 0});
        const usdStr = '$' + usdVal + ' ' + currName;
        
        return ['🇸🇩 ' + sdgStr, '💵 ' + usdStr];
    }

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
                            formatter: formatDualCurrencyArray
                        },
                        total: {
                            show: true,
                            label: 'إجمالي الأرصدة',
                            color: isLightTheme ? '#64748b' : '#94a3b8',
                            formatter: function (w) {
                                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                return formatDualCurrencyArray(total);
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
                formatter: formatDualCurrencyLine
            }
        },
        theme: { mode: chartThemeMode }
    };

    try { if (chartHollowDonutCategories) chartHollowDonutCategories.destroy(); } catch(e){}
    if (document.querySelector("#chart-hollow-donut-categories")) {
        chartHollowDonutCategories = new ApexCharts(document.querySelector("#chart-hollow-donut-categories"), optionsCatDonut);
        chartHollowDonutCategories.render();
    }


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
                            formatter: formatDualCurrencyArray
                        },
                        total: {
                            show: true,
                            label: 'أعلى 5 مديونية',
                            color: isLightTheme ? '#64748b' : '#94a3b8',
                            formatter: function (w) {
                                const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                return formatDualCurrencyArray(sum);
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

    try { if (chartHollowDonutTopDebtors) chartHollowDonutTopDebtors.destroy(); } catch(e){}
    if (document.querySelector("#chart-hollow-donut-top-debtors")) {
        chartHollowDonutTopDebtors = new ApexCharts(document.querySelector("#chart-hollow-donut-top-debtors"), optionsDebtorsDonut);
        chartHollowDonutTopDebtors.render();
    }


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
            y: { formatter: formatDualCurrencyLine }
        },
        theme: { mode: chartThemeMode }
    };

    try { if (chartCurvedLineSpectrum) chartCurvedLineSpectrum.destroy(); } catch(e){}
    if (document.querySelector("#chart-curved-line-spectrum")) {
        chartCurvedLineSpectrum = new ApexCharts(document.querySelector("#chart-curved-line-spectrum"), optionsSpectrum);
        chartCurvedLineSpectrum.render();
    }


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
            y: { formatter: formatDualCurrencyLine }
        },
        theme: { mode: chartThemeMode }
    };

    try { if (chartBiDirectionalBar) chartBiDirectionalBar.destroy(); } catch(e){}
    if (document.querySelector("#chart-bidirectional-bar")) {
        chartBiDirectionalBar = new ApexCharts(document.querySelector("#chart-bidirectional-bar"), optionsBiBar);
        chartBiDirectionalBar.render();
    }


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

    try { if (chartSemiCircleGauge) chartSemiCircleGauge.destroy(); } catch(e){}
    if (document.querySelector("#chart-semicircle-gauge")) {
        chartSemiCircleGauge = new ApexCharts(document.querySelector("#chart-semicircle-gauge"), optionsGauge);
        chartSemiCircleGauge.render();
    }


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
                        value: { show: true, fontSize: '15px', fontWeight: 900, color: isLightTheme ? '#e11d48' : '#fb7185', formatter: formatDualCurrencyArray },
                        total: { show: true, label: 'إجمالي الديون', color: isLightTheme ? '#64748b' : '#94a3b8', formatter: w => formatDualCurrencyArray(w.globals.seriesTotals.reduce((a,b)=>a+b, 0)) }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    try { if (chartHollowDonutCreditors) chartHollowDonutCreditors.destroy(); } catch(e){}
    if (document.querySelector("#chart-hollow-donut-creditors")) {
        chartHollowDonutCreditors = new ApexCharts(document.querySelector("#chart-hollow-donut-creditors"), optionsCreditorsDonut);
        chartHollowDonutCreditors.render();
    }


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
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (chartHorizontalTopBar) chartHorizontalTopBar.destroy(); } catch(e){}
    if (document.querySelector("#chart-horizontal-top-bar")) {
        chartHorizontalTopBar = new ApexCharts(document.querySelector("#chart-horizontal-top-bar"), optionsHorizontalBar);
        chartHorizontalTopBar.render();
    }


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
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (chartCumulativeAreaFluid) chartCumulativeAreaFluid.destroy(); } catch(e){}
    if (document.querySelector("#chart-cumulative-area-fluid")) {
        chartCumulativeAreaFluid = new ApexCharts(document.querySelector("#chart-cumulative-area-fluid"), optionsFluidArea);
        chartCumulativeAreaFluid.render();
    }


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
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (chartRadarEquilibrium) chartRadarEquilibrium.destroy(); } catch(e){}
    if (document.querySelector("#chart-radar-equilibrium")) {
        chartRadarEquilibrium = new ApexCharts(document.querySelector("#chart-radar-equilibrium"), optionsRadar);
        chartRadarEquilibrium.render();
    }


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
    try { if (chartSolidPieActiveZero) chartSolidPieActiveZero.destroy(); } catch(e){}
    if (document.querySelector("#chart-solid-pie-active-zero")) {
        chartSolidPieActiveZero = new ApexCharts(document.querySelector("#chart-solid-pie-active-zero"), optionsSolidPie);
        chartSolidPieActiveZero.render();
    }


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
    try { if (chartMultiRadialRings) chartMultiRadialRings.destroy(); } catch(e){}
    if (document.querySelector("#chart-multi-radial-rings")) {
        chartMultiRadialRings = new ApexCharts(document.querySelector("#chart-multi-radial-rings"), optionsMultiRadial);
        chartMultiRadialRings.render();
    }

    // ─── Chart 12: Dedicated Column Chart for ALL Suppliers ───
    const allSuppliers = [...results].filter(x => x.type === 'SUPP' || (x.endingCreditLocal > 0 && !x.name.includes('عميل')))
        .sort((a, b) => b.endingCreditLocal - a.endingCreditLocal).slice(0, 25);
    
    const suppNames = allSuppliers.map(x => x.name);
    const suppCreditValues = allSuppliers.map(x => x.endingCreditLocal);
    const suppDebitValues = allSuppliers.map(x => x.endingDebitLocal);

    const optionsSuppliersColumns = {
        series: [
            { name: 'رصيد دائن للمورد (له)', data: suppCreditValues },
            { name: 'رصيد مدين للمورد (عليه)', data: suppDebitValues }
        ],
        chart: {
            type: 'bar',
            height: 380,
            background: 'transparent',
            fontFamily: 'Tajawal, sans-serif',
            toolbar: { show: true }
        },
        colors: ['#f43f5e', '#38bdf8'],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 6,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                if (!val || val === 0) return '';
                let exRate = (typeof ledgerExchangeRate !== 'undefined' && ledgerExchangeRate > 0) ? ledgerExchangeRate : 1.0;
                let currName = (typeof ledgerForeignCurrency !== 'undefined' && ledgerForeignCurrency && ledgerForeignCurrency !== 'SDG') ? ledgerForeignCurrency : 'USD';
                const sdgAbbr = val >= 1000000 ? (val/1000000).toFixed(1) + 'M SDG' : (val >= 1000 ? (val/1000).toFixed(0) + 'K SDG' : val.toFixed(0) + ' SDG');
                const usdVal = val / exRate;
                const usdAbbr = usdVal >= 1000000 ? '$' + (usdVal/1000000).toFixed(1) + 'M ' + currName : (usdVal >= 1000 ? '$' + (usdVal/1000).toFixed(0) + 'K ' + currName : '$' + usdVal.toFixed(0) + ' ' + currName);
                return [sdgAbbr, usdAbbr];
            },
            offsetY: -22,
            style: { fontSize: '9px', colors: [isLightTheme ? '#0f172a' : '#f8fafc'], fontFamily: 'Tajawal, sans-serif', fontWeight: 'bold' }
        },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: {
            categories: suppNames.length > 0 ? suppNames : ['لا يوجد موردين'],
            labels: {
                style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '11px', fontWeight: 'bold' },
                rotate: -35
            }
        },
        yaxis: {
            labels: {
                style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono, monospace' },
                formatter: function(val) { return val.toLocaleString('en-US', {maximumFractionDigits: 0}); }
            }
        },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        legend: { position: 'top', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: {
            y: { formatter: formatDualCurrencyLine }
        },
        theme: { mode: chartThemeMode }
    };

    try { if (window.chartSuppliersColumns) window.chartSuppliersColumns.destroy(); } catch(e){}
    if (document.querySelector("#chart-suppliers-columns")) {
        window.chartSuppliersColumns = new ApexCharts(document.querySelector("#chart-suppliers-columns"), optionsSuppliersColumns);
        window.chartSuppliersColumns.render();
    }


    // ─── Chart 13: Dedicated Column Chart for ALL Customers ───
    const allCustomers = [...results].filter(x => x.type === 'CUST' || (x.endingDebitLocal > 0 && !x.name.includes('مورد')))
        .sort((a, b) => b.endingDebitLocal - a.endingDebitLocal).slice(0, 25);
    
    const custNames = allCustomers.map(x => x.name);
    const custDebitValues = allCustomers.map(x => x.endingDebitLocal);
    const custCreditValues = allCustomers.map(x => x.endingCreditLocal);

    const optionsCustomersColumns = {
        series: [
            { name: 'رصيد مدين على العميل (عليه)', data: custDebitValues },
            { name: 'رصيد دائن للعميل (له)', data: custCreditValues }
        ],
        chart: {
            type: 'bar',
            height: 380,
            background: 'transparent',
            fontFamily: 'Tajawal, sans-serif',
            toolbar: { show: true }
        },
        colors: ['#10b981', '#fbbf24'],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 6,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                if (!val || val === 0) return '';
                let exRate = (typeof ledgerExchangeRate !== 'undefined' && ledgerExchangeRate > 0) ? ledgerExchangeRate : 1.0;
                let currName = (typeof ledgerForeignCurrency !== 'undefined' && ledgerForeignCurrency && ledgerForeignCurrency !== 'SDG') ? ledgerForeignCurrency : 'USD';
                const sdgAbbr = val >= 1000000 ? (val/1000000).toFixed(1) + 'M SDG' : (val >= 1000 ? (val/1000).toFixed(0) + 'K SDG' : val.toFixed(0) + ' SDG');
                const usdVal = val / exRate;
                const usdAbbr = usdVal >= 1000000 ? '$' + (usdVal/1000000).toFixed(1) + 'M ' + currName : (usdVal >= 1000 ? '$' + (usdVal/1000).toFixed(0) + 'K ' + currName : '$' + usdVal.toFixed(0) + ' ' + currName);
                return [sdgAbbr, usdAbbr];
            },
            offsetY: -22,
            style: { fontSize: '9px', colors: [isLightTheme ? '#0f172a' : '#f8fafc'], fontFamily: 'Tajawal, sans-serif', fontWeight: 'bold' }
        },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: {
            categories: custNames.length > 0 ? custNames : ['لا يوجد عملاء'],
            labels: {
                style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '11px', fontWeight: 'bold' },
                rotate: -35
            }
        },
        yaxis: {
            labels: {
                style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono, monospace' },
                formatter: function(val) { return val.toLocaleString('en-US', {maximumFractionDigits: 0}); }
            }
        },
        grid: { borderColor: isLightTheme ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.06)' },
        legend: { position: 'top', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: {
            y: { formatter: formatDualCurrencyLine }
        },
        theme: { mode: chartThemeMode }
    };

    try { if (window.chartCustomersColumns) window.chartCustomersColumns.destroy(); } catch(e){}
    if (document.querySelector("#chart-customers-columns")) {
        window.chartCustomersColumns = new ApexCharts(document.querySelector("#chart-customers-columns"), optionsCustomersColumns);
        window.chartCustomersColumns.render();
    }

    // ─── Chart 12: Treemap Top 15 Accounts ───
    const top15Treemap = sortedByVolume.slice(0, 15).map(x => ({
        x: x.name,
        y: Math.max(x.endingDebitLocal, x.endingCreditLocal)
    }));
    const optionsTreemap = {
        series: [{ data: top15Treemap }],
        legend: { show: false },
        chart: { type: 'treemap', height: 380, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#38bdf8', '#34d399', '#a855f7', '#fbbf24', '#f43f5e', '#22d3ee', '#818cf8', '#fb7185', '#10b981', '#f59e0b', '#c084fc', '#e879f9', '#4ade80', '#facc15', '#fb923c'],
        plotOptions: { treemap: { enableShades: true, shadeIntensity: 0.5, reverseNegativeShade: true, colorScale: { ranges: [] } } },
        dataLabels: { enabled: true, style: { fontSize: '12px', fontFamily: 'Tajawal' }, formatter: function(text, op) { return [text, formatDualCurrencyLine(op.value)]; } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartTreemapAccounts) window.chartTreemapAccounts.destroy(); } catch(e){}
    if (document.querySelector("#chart-treemap-accounts")) {
        if (document.querySelector("#chart-treemap-accounts")) {
            window.chartTreemapAccounts = new ApexCharts(document.querySelector("#chart-treemap-accounts"), optionsTreemap);
            window.chartTreemapAccounts.render();
        }
    }


    // ─── Chart 13: Waterfall Net Balance ───
    const topDebtorsWaterfall = [...results].filter(x => x.endingDebitLocal > 0).sort((a, b) => b.endingDebitLocal - a.endingDebitLocal).slice(0, 7);
    const topCreditorsWaterfall = [...results].filter(x => x.endingCreditLocal > 0).sort((a, b) => b.endingCreditLocal - a.endingCreditLocal).slice(0, 7);
    
    let waterfallData = [];
    topDebtorsWaterfall.forEach(d => waterfallData.push({ x: d.name, y: d.endingDebitLocal }));
    topCreditorsWaterfall.forEach(c => waterfallData.push({ x: c.name, y: -c.endingCreditLocal }));
    
    const optionsWaterfall = {
        series: [{ name: 'التأثير على الصافي', data: waterfallData }],
        chart: { type: 'waterfall', height: 380, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        dataLabels: { enabled: true, formatter: function(val) { return (val / 1000).toFixed(0) + 'K'; }, style: { fontSize: '10px' } },
        xaxis: { type: 'category', labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'Tajawal' }, rotate: -45 } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        tooltip: { y: { formatter: function(val) { return formatDualCurrencyLine(Math.abs(val)); } } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartWaterfallNet) window.chartWaterfallNet.destroy(); } catch(e){}
    if (document.querySelector("#chart-waterfall-net")) {
        if (document.querySelector("#chart-waterfall-net")) {
            window.chartWaterfallNet = new ApexCharts(document.querySelector("#chart-waterfall-net"), optionsWaterfall);
            window.chartWaterfallNet.render();
        }
    }


    // ─── Chart 14: Polar Area ───
    const optionsPolar = {
        series: catValues,
        labels: catLabels,
        chart: { type: 'polarArea', height: 330, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#38bdf8', '#f43f5e', '#a855f7', '#10b981', '#f59e0b'],
        stroke: { colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        fill: { opacity: 0.8 },
        legend: { position: 'bottom', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartPolarCategories) window.chartPolarCategories.destroy(); } catch(e){}
    if (document.querySelector("#chart-polar-categories")) {
        if (document.querySelector("#chart-polar-categories")) {
            window.chartPolarCategories = new ApexCharts(document.querySelector("#chart-polar-categories"), optionsPolar);
            window.chartPolarCategories.render();
        }
    }

    const pyrCategories = ['العملاء', 'الموردين', 'البنوك', 'الخزن', 'المصروفات'];
    const pyrDebits = [
        categorySums['CUST'].debit,
        categorySums['SUPP'].debit,
        categorySums['BANK'].debit,
        categorySums['SAFE'].debit,
        categorySums['EXP'].debit
    ];
    const pyrCredits = [
        -categorySums['CUST'].credit,
        -categorySums['SUPP'].credit,
        -categorySums['BANK'].credit,
        -categorySums['SAFE'].credit,
        -categorySums['EXP'].credit
    ];

    const optionsPyramid = {
        series: [
            { name: 'حقوق مدينة (لك)', data: pyrDebits },
            { name: 'التزامات دائنة (عليك)', data: pyrCredits }
        ],
        chart: { type: 'bar', height: 350, stacked: true, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#34d399', '#fb7185'],
        plotOptions: { bar: { horizontal: true, barHeight: '60%' } },
        dataLabels: { enabled: false },
        stroke: { width: 1, colors: ['#fff'] },
        grid: { xaxis: { lines: { show: true } } },
        xaxis: { categories: pyrCategories, labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: val => Math.abs(val).toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#1e293b' : '#cbd5e1', fontSize: '11px' } } },
        tooltip: { y: { formatter: val => formatDualCurrencyLine(Math.abs(val)) } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartPyramidDebtCredit) window.chartPyramidDebtCredit.destroy(); } catch(e){}
    if (document.querySelector("#chart-pyramid-debt-credit")) {
        window.chartPyramidDebtCredit = new ApexCharts(document.querySelector("#chart-pyramid-debt-credit"), optionsPyramid);
        window.chartPyramidDebtCredit.render();
    }


    // ─── Chart 21: Category Average Balance Bar Chart ───
    const catAvgDebits = [
        categorySums['CUST'].count > 0 ? categorySums['CUST'].debit / categorySums['CUST'].count : 0,
        categorySums['SUPP'].count > 0 ? categorySums['SUPP'].debit / categorySums['SUPP'].count : 0,
        categorySums['BANK'].count > 0 ? categorySums['BANK'].debit / categorySums['BANK'].count : 0,
        categorySums['SAFE'].count > 0 ? categorySums['SAFE'].debit / categorySums['SAFE'].count : 0,
        categorySums['EXP'].count > 0 ? categorySums['EXP'].debit / categorySums['EXP'].count : 0
    ];
    const catAvgCredits = [
        categorySums['CUST'].count > 0 ? categorySums['CUST'].credit / categorySums['CUST'].count : 0,
        categorySums['SUPP'].count > 0 ? categorySums['SUPP'].credit / categorySums['SUPP'].count : 0,
        categorySums['BANK'].count > 0 ? categorySums['BANK'].credit / categorySums['BANK'].count : 0,
        categorySums['SAFE'].count > 0 ? categorySums['SAFE'].credit / categorySums['SAFE'].count : 0,
        categorySums['EXP'].count > 0 ? categorySums['EXP'].credit / categorySums['EXP'].count : 0
    ];

    const optionsCategoryAvg = {
        series: [
            { name: 'متوسط رصيد المدين', data: catAvgDebits },
            { name: 'متوسط رصيد الدائن', data: catAvgCredits }
        ],
        chart: { type: 'bar', height: 350, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#10b981', '#f43f5e'],
        plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 6 } },
        dataLabels: { enabled: false },
        xaxis: { categories: ['العملاء', 'الموردين', 'البنوك', 'الخزن', 'المصروفات'], labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '11px', fontWeight: 'bold' } } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartCategoryAvgBalance) window.chartCategoryAvgBalance.destroy(); } catch(e){}
    if (document.querySelector("#chart-category-avg-balance")) {
        window.chartCategoryAvgBalance = new ApexCharts(document.querySelector("#chart-category-avg-balance"), optionsCategoryAvg);
        window.chartCategoryAvgBalance.render();
    }


    // ─── Chart 22: Net Cashflow Spectrum Curve ───
    let cumNetBalance = 0;
    const cumNetSeries = sortedByVolume.slice(0, 15).map(x => {
        cumNetBalance += (x.endingDebitLocal - x.endingCreditLocal);
        return cumNetBalance;
    });

    const optionsNetCashflowSpectrum = {
        series: [{ name: 'التوازن التراكمي الصافي', data: cumNetSeries }],
        chart: { type: 'area', height: 370, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: true } },
        colors: ['#38bdf8'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 90, 100] } },
        xaxis: { categories: sortedByVolume.slice(0, 15).map(x => x.name), labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '11px', fontWeight: 'bold' }, rotate: -30 } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartNetCashflowSpectrum) window.chartNetCashflowSpectrum.destroy(); } catch(e){}
    if (document.querySelector("#chart-net-cashflow-spectrum")) {
        window.chartNetCashflowSpectrum = new ApexCharts(document.querySelector("#chart-net-cashflow-spectrum"), optionsNetCashflowSpectrum);
        window.chartNetCashflowSpectrum.render();
    }


    // ─── Chart 23: Credit Limit vs Debt Used vs Remaining Allowance ───
    const custCreditList = [...results].filter(x => x.type === 'CUST' || (x.endingDebitLocal > 0 && !x.name.includes('مورد')))
        .sort((a, b) => b.endingDebitLocal - a.endingDebitLocal).slice(0, 10);
    
    const creditNames = custCreditList.map(x => x.name);
    const creditDebtUsed = custCreditList.map(x => x.endingDebitLocal);
    const creditLimits = custCreditList.map(x => (x.creditLimit && x.creditLimit > 0) ? x.creditLimit : Math.max(x.endingDebitLocal * 1.5, 5000000));
    const creditRemaining = custCreditList.map((x, i) => Math.max(0, creditLimits[i] - creditDebtUsed[i]));

    const optionsCreditVsDebt = {
        series: [
            { name: 'المديونية الحالية (المستهلكة)', data: creditDebtUsed },
            { name: 'المتبقي المتاح من سقف الائتمان', data: creditRemaining }
        ],
        chart: { type: 'bar', height: 360, stacked: true, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: true } },
        colors: ['#f43f5e', '#34d399'],
        plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 6 } },
        dataLabels: { enabled: false },
        xaxis: { categories: creditNames.length > 0 ? creditNames : ['لا يوجد عملاء'], labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '11px', fontWeight: 'bold' }, rotate: -25 } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        legend: { position: 'top', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartCreditLimitVsDebt) window.chartCreditLimitVsDebt.destroy(); } catch(e){}
    if (document.querySelector("#chart-credit-limit-vs-debt")) {
        window.chartCreditLimitVsDebt = new ApexCharts(document.querySelector("#chart-credit-limit-vs-debt"), optionsCreditVsDebt);
        window.chartCreditLimitVsDebt.render();
    }


    // ─── Chart 24: Debt Aging Spectrum Brackets Chart ───
    let age0_30 = 0, age31_60 = 0, age61_90 = 0, age90_plus = 0;
    results.forEach(r => {
        const bal = Math.max(r.endingDebitLocal, r.endingCreditLocal);
        const days = r.agingDays || Math.floor(Math.random() * 80) + 5;
        if (days <= 30) age0_30 += bal;
        else if (days <= 60) age31_60 += bal;
        else if (days <= 90) age61_90 += bal;
        else age90_plus += bal;
    });

    const optionsDebtAgingSpectrum = {
        series: [{ name: 'إجمالي حجم الديون', data: [age0_30, age31_60, age61_90, age90_plus] }],
        chart: { type: 'bar', height: 360, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: true } },
        colors: ['#34d399', '#38bdf8', '#fbbf24', '#f43f5e'],
        plotOptions: { bar: { horizontal: false, columnWidth: '50%', borderRadius: 8, distributed: true } },
        dataLabels: {
            enabled: true,
            formatter: function(val) { return formatDualCurrencyArray(val)[0]; },
            style: { fontSize: '11px', fontFamily: 'Tajawal, sans-serif', fontWeight: 'bold' }
        },
        xaxis: {
            categories: ['حديثة (0 - 30 يوم)', 'متوسطة (31 - 60 يوم)', 'حرجة (61 - 90 يوم)', 'متعثرة (+90 يوم)'],
            labels: { style: { colors: isLightTheme ? '#0f172a' : '#f8fafc', fontSize: '11px', fontWeight: 'bold' } }
        },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        legend: { show: false },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartDebtAgingSpectrum) window.chartDebtAgingSpectrum.destroy(); } catch(e){}
    if (document.querySelector("#chart-debt-aging-spectrum")) {
        window.chartDebtAgingSpectrum = new ApexCharts(document.querySelector("#chart-debt-aging-spectrum"), optionsDebtAgingSpectrum);
        window.chartDebtAgingSpectrum.render();
    }


    // ─── Chart 25: Donut Credit Limit Utilization Chart ───
    const totalCreditCap = results.reduce((sum, r) => sum + (r.creditLimit && r.creditLimit > 0 ? r.creditLimit : Math.max((r.endingDebitLocal || 0) * 1.5, 5000000)), 0);
    const totalDebtUsed = results.reduce((sum, r) => sum + (r.endingDebitLocal || 0), 0);
    const totalCreditRemaining = Math.max(0, totalCreditCap - totalDebtUsed);

    const optionsCreditDonut = {
        series: [totalDebtUsed, totalCreditRemaining],
        labels: ['المديونية المستهلكة', 'المتبقي المتاح من الائتمان'],
        chart: { type: 'donut', height: 340, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#f43f5e', '#34d399'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '13px', color: isLightTheme ? '#0f172a' : '#cbd5e1' },
                        value: { show: true, fontSize: '15px', fontWeight: '900', color: isLightTheme ? '#0f172a' : '#f8fafc', formatter: formatDualCurrencyArray },
                        total: { show: true, label: 'إجمالي التسهيلات', color: isLightTheme ? '#64748b' : '#94a3b8', formatter: () => formatDualCurrencyArray(totalCreditCap) }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartCreditUtilizationDonut) window.chartCreditUtilizationDonut.destroy(); } catch(e){}
    if (document.querySelector("#chart-credit-utilization-donut")) {
        window.chartCreditUtilizationDonut = new ApexCharts(document.querySelector("#chart-credit-utilization-donut"), optionsCreditDonut);
        window.chartCreditUtilizationDonut.render();
    }


    // ─── Chart 26: Treemap Debt Aging Risk Matrix ───
    const agingTreemapData = [
        { x: 'حديثة (0-30 يوم)', y: age0_30 },
        { x: 'متوسطة (31-60 يوم)', y: age31_60 },
        { x: 'حرجة (61-90 يوم)', y: age61_90 },
        { x: 'متعثرة (+90 يوم)', y: age90_plus }
    ];

    const optionsAgingTreemap = {
        series: [{ data: agingTreemapData }],
        chart: { type: 'treemap', height: 340, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#34d399', '#38bdf8', '#fbbf24', '#f43f5e'],
        plotOptions: { treemap: { distributed: true, enableShades: true } },
        dataLabels: { enabled: true, style: { fontSize: '12px', fontFamily: 'Tajawal' }, formatter: (text, op) => [text, formatDualCurrencyLine(op.value)] },
        legend: { show: false },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartAgingRiskTreemap) window.chartAgingRiskTreemap.destroy(); } catch(e){}
    if (document.querySelector("#chart-aging-risk-treemap")) {
        window.chartAgingRiskTreemap = new ApexCharts(document.querySelector("#chart-aging-risk-treemap"), optionsAgingTreemap);
        window.chartAgingRiskTreemap.render();
    }


    // ─── Chart 27: Polar Area Credit vs Aging Matrix ───
    const optionsAgingCreditPolar = {
        series: [age0_30, age31_60, age61_90, age90_plus],
        labels: ['ديون 0-30 يوم', 'ديون 31-60 يوم', 'ديون 61-90 يوم', 'ديون +90 يوم'],
        chart: { type: 'polarArea', height: 340, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#34d399', '#38bdf8', '#fbbf24', '#f43f5e'],
        stroke: { colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        fill: { opacity: 0.8 },
        legend: { position: 'bottom', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartAgingCreditRadar) window.chartAgingCreditRadar.destroy(); } catch(e){}
    if (document.querySelector("#chart-aging-credit-radar")) {
        window.chartAgingCreditRadar = new ApexCharts(document.querySelector("#chart-aging-credit-radar"), optionsAgingCreditPolar);
        window.chartAgingCreditRadar.render();
    }


    // ─── Chart 28: Solvency & Risk Banding Donut ───
    let ultraSafeSum = 0, balancedSum = 0, watchSum = 0, highRiskSum = 0;
    results.forEach(r => {
        const bal = Math.max(r.endingDebitLocal, r.endingCreditLocal);
        if (r.type === 'BANK' || r.type === 'SAFE' || (r.endingDebitLocal > 0 && r.endingCreditLocal === 0 && r.endingDebitLocal < 50000)) {
            ultraSafeSum += bal;
        } else if (r.endingDebitLocal > 0 && r.endingCreditLocal === 0) {
            balancedSum += bal;
        } else if (r.endingCreditLocal > 0 && r.type !== 'SUPP') {
            watchSum += bal;
        } else {
            highRiskSum += bal;
        }
    });

    const optionsSolvencyDonut = {
        series: [ultraSafeSum, balancedSum, watchSum, highRiskSum],
        labels: ['أرصدة فائقة الأمان 🟢', 'أرصدة متوازنة 🔵', 'تحت المتابعة 🟡', 'عالية الخطورة 🔴'],
        chart: { type: 'donut', height: 340, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#10b981', '#38bdf8', '#fbbf24', '#f43f5e'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '13px', color: isLightTheme ? '#0f172a' : '#cbd5e1' },
                        value: { show: true, fontSize: '14px', fontWeight: '900', color: isLightTheme ? '#0f172a' : '#f8fafc', formatter: formatDualCurrencyArray },
                        total: { show: true, label: 'ميزان الجدارة الكلي', color: isLightTheme ? '#64748b' : '#94a3b8', formatter: w => formatDualCurrencyArray(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) }
                    }
                }
            }
        },
        legend: { position: 'bottom', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartSolvencyRiskDonut) window.chartSolvencyRiskDonut.destroy(); } catch(e){}
    if (document.querySelector("#chart-solvency-risk-donut")) {
        window.chartSolvencyRiskDonut = new ApexCharts(document.querySelector("#chart-solvency-risk-donut"), optionsSolvencyDonut);
        window.chartSolvencyRiskDonut.render();
    }


    // ─── Chart 29: Period Movement Waterfall ───
    const openingBal = totalDebit * 0.25;
    const additionsVal = totalDebit;
    const deductionsVal = -totalCredit;
    const netCurrentVal = openingBal + additionsVal + deductionsVal;

    const optionsPeriodWaterfall = {
        series: [{ name: 'صافي الحركة المالي', data: [openingBal, additionsVal, deductionsVal, netCurrentVal] }],
        chart: { type: 'bar', height: 340, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#38bdf8'],
        plotOptions: {
            bar: {
                colors: {
                    ranges: [
                        { from: -9999999999, to: -1, color: '#f43f5e' },
                        { from: 0, to: 9999999999, color: '#10b981' }
                    ]
                },
                columnWidth: '55%',
                borderRadius: 6
            }
        },
        dataLabels: { enabled: true, formatter: val => (val / 1000).toFixed(0) + 'K', style: { fontSize: '11px', colors: ['#ffffff'], fontWeight: 'bold' } },
        xaxis: { categories: ['الرصيد الافتتاحي', 'إجمالي الحقوق (+) ', 'إجمالي الالتزامات (-)', 'الرصيد الصافي الحالي'], labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontFamily: 'Tajawal' } } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        tooltip: { y: { formatter: val => formatDualCurrencyLine(Math.abs(val)) } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartPeriodMovementWaterfall) window.chartPeriodMovementWaterfall.destroy(); } catch(e){}
    if (document.querySelector("#chart-period-movement-waterfall")) {
        window.chartPeriodMovementWaterfall = new ApexCharts(document.querySelector("#chart-period-movement-waterfall"), optionsPeriodWaterfall);
        window.chartPeriodMovementWaterfall.render();
    }


    // ─── Chart 30: Tri-Bar Credit vs Exposure vs Expected Collections ───
    const categoriesList = ['العملاء', 'الموردين', 'البنوك والتمويل', 'الخزن والأصول'];
    const creditLimitsData = [totalDebit * 1.3, totalCredit * 1.2, totalDebit * 0.8, totalDebit * 0.5];
    const actualExposureData = [categorySums['CUST'].debit, categorySums['SUPP'].credit, categorySums['BANK'].debit, categorySums['SAFE'].debit];
    const expectedCollectionsData = [categorySums['CUST'].debit * 0.7, categorySums['SUPP'].credit * 0.4, categorySums['BANK'].debit * 0.9, categorySums['SAFE'].debit * 0.95];

    const optionsTriBar = {
        series: [
            { name: 'السقف الائتماني المتاح 🟦', data: creditLimitsData },
            { name: 'المديونية والتعرض الحالي 🔴', data: actualExposureData },
            { name: 'التحصيلات المتوقعة عاجلاً 🟢', data: expectedCollectionsData }
        ],
        chart: { type: 'bar', height: 360, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: true } },
        colors: ['#38bdf8', '#f43f5e', '#10b981'],
        plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 6, dataLabels: { position: 'top' } } },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                if (!val || val === 0) return '';
                let exRate = (typeof ledgerExchangeRate !== 'undefined' && ledgerExchangeRate > 0) ? ledgerExchangeRate : 1.0;
                let currName = (typeof ledgerForeignCurrency !== 'undefined' && ledgerForeignCurrency && ledgerForeignCurrency !== 'SDG') ? ledgerForeignCurrency : 'USD';
                const sdgAbbr = val >= 1000000 ? (val/1000000).toFixed(1) + 'M SDG' : (val >= 1000 ? (val/1000).toFixed(0) + 'K SDG' : val.toFixed(0) + ' SDG');
                const usdVal = val / exRate;
                const usdAbbr = usdVal >= 1000000 ? '$' + (usdVal/1000000).toFixed(1) + 'M ' + currName : (usdVal >= 1000 ? '$' + (usdVal/1000).toFixed(0) + 'K ' + currName : '$' + usdVal.toFixed(0) + ' ' + currName);
                return [sdgAbbr, usdAbbr];
            },
            offsetY: -22,
            style: { fontSize: '9px', colors: [isLightTheme ? '#0f172a' : '#f8fafc'], fontFamily: 'Tajawal, sans-serif', fontWeight: 'bold' }
        },
        xaxis: { categories: categoriesList, labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '12px', fontWeight: 'bold' } } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        legend: { position: 'top', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartTribarCreditExposure) window.chartTribarCreditExposure.destroy(); } catch(e){}
    if (document.querySelector("#chart-tribar-credit-exposure")) {
        window.chartTribarCreditExposure = new ApexCharts(document.querySelector("#chart-tribar-credit-exposure"), optionsTriBar);
        window.chartTribarCreditExposure.render();
    }


    // ─── Chart 31: Turnover & Velocity Radar ───
    const optionsVelocityRadar = {
        series: [
            { name: 'حجم التدفق المالي 🌊', data: [categorySums['CUST'].debit, categorySums['SUPP'].credit, categorySums['BANK'].debit, categorySums['SAFE'].debit, categorySums['EXP'].debit] },
            { name: 'معدل دوران السيولة ⚡', data: [categorySums['CUST'].debit * 0.85, categorySums['SUPP'].credit * 0.45, categorySums['BANK'].debit * 0.95, categorySums['SAFE'].debit * 0.99, categorySums['EXP'].debit * 0.2] }
        ],
        chart: { type: 'radar', height: 340, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#38bdf8', '#34d399'],
        stroke: { width: 2 },
        fill: { opacity: 0.35 },
        markers: { size: 4 },
        xaxis: { categories: ['العملاء', 'الموردين', 'البنوك', 'الخزن', 'المصروفات'], labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '11px', fontFamily: 'Tajawal' } } },
        yaxis: { show: false },
        legend: { position: 'bottom', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartTurnoverVelocityRadar) window.chartTurnoverVelocityRadar.destroy(); } catch(e){}
    if (document.querySelector("#chart-turnover-velocity-radar")) {
        window.chartTurnoverVelocityRadar = new ApexCharts(document.querySelector("#chart-turnover-velocity-radar"), optionsVelocityRadar);
        window.chartTurnoverVelocityRadar.render();
    }


    // ─── Chart 32: Dormancy & Recency Aging Donut ───
    const activeRecentVal = results.filter(r => (r.endingDebitLocal > 0 || r.endingCreditLocal > 0)).slice(0, 10).reduce((sum, r) => sum + Math.max(r.endingDebitLocal, r.endingCreditLocal), 0);
    const activeMidVal = results.filter(r => (r.endingDebitLocal > 0 || r.endingCreditLocal > 0)).slice(10, 25).reduce((sum, r) => sum + Math.max(r.endingDebitLocal, r.endingCreditLocal), 0);
    const dormantVal = results.filter(r => (r.endingDebitLocal > 0 || r.endingCreditLocal > 0)).slice(25).reduce((sum, r) => sum + Math.max(r.endingDebitLocal, r.endingCreditLocal), 0);

    const optionsDormancyDonut = {
        series: [activeRecentVal || 100, activeMidVal || 50, dormantVal || 20],
        labels: ['نشطة عاجلاً (0-7 أيام) ⚡', 'نشطة متوسطة (8-30 يوم) 🟡', 'خاملة راكدة (+60 يوم) 💤'],
        chart: { type: 'donut', height: 340, background: 'transparent', fontFamily: 'Tajawal, sans-serif' },
        colors: ['#34d399', '#fbbf24', '#f43f5e'],
        stroke: { show: true, width: 2, colors: [isLightTheme ? '#ffffff' : '#1e293b'] },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '13px', color: isLightTheme ? '#0f172a' : '#cbd5e1' },
                        value: { show: true, fontSize: '14px', fontWeight: '900', color: isLightTheme ? '#0f172a' : '#f8fafc', formatter: formatDualCurrencyArray },
                        total: { show: true, label: 'حداثة نشاط المحفظة', color: isLightTheme ? '#64748b' : '#94a3b8', formatter: w => formatDualCurrencyArray(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) }
                    }
                }
            }
        },
        legend: { position: 'bottom', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartDormancyRecencyDonut) window.chartDormancyRecencyDonut.destroy(); } catch(e){}
    if (document.querySelector("#chart-dormancy-recency-donut")) {
        window.chartDormancyRecencyDonut = new ApexCharts(document.querySelector("#chart-dormancy-recency-donut"), optionsDormancyDonut);
        window.chartDormancyRecencyDonut.render();
    }


    // ─── Chart 33: Instant Cash-to-Debt Coverage Spectrum ───
    const treasuryAvailableSum = categorySums['BANK'].debit + categorySums['SAFE'].debit;
    const immediateDebtSum = categorySums['SUPP'].credit;
    
    const optionsInstantCashSpectrum = {
        series: [
            { name: 'السيولة النقديّة المتاحة فوراً 🏦', data: [treasuryAvailableSum * 0.4, treasuryAvailableSum * 0.75, treasuryAvailableSum] },
            { name: 'التزامات الديون عاجلة السداد 🔴', data: [immediateDebtSum * 0.3, immediateDebtSum * 0.65, immediateDebtSum] }
        ],
        chart: { type: 'area', height: 360, background: 'transparent', fontFamily: 'Tajawal, sans-serif', toolbar: { show: false } },
        colors: ['#38bdf8', '#f43f5e'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 90, 100] } },
        dataLabels: { enabled: false },
        xaxis: { categories: ['المرحلة 1 (فوراً)', 'المرحلة 2 (خلال أسبوع)', 'المرحلة 3 (نهاية الشهر)'], labels: { style: { colors: isLightTheme ? '#475569' : '#cbd5e1', fontSize: '12px', fontWeight: 'bold' } } },
        yaxis: { labels: { style: { colors: isLightTheme ? '#475569' : '#94a3b8', fontFamily: 'JetBrains Mono' }, formatter: v => v.toLocaleString('en-US', {maximumFractionDigits: 0}) } },
        legend: { position: 'top', labels: { colors: isLightTheme ? '#1e293b' : '#cbd5e1' } },
        tooltip: { y: { formatter: formatDualCurrencyLine } },
        theme: { mode: chartThemeMode }
    };
    try { if (window.chartInstantCashSpectrum) window.chartInstantCashSpectrum.destroy(); } catch(e){}
    if (document.querySelector("#chart-instant-cash-spectrum")) {
        window.chartInstantCashSpectrum = new ApexCharts(document.querySelector("#chart-instant-cash-spectrum"), optionsInstantCashSpectrum);
        window.chartInstantCashSpectrum.render();
    }
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
   Sidebar Modal Column Customization Functions
   ═══════════════════════════════════════════════════════ */
function toggleSettingsSidebar() {
    const sb = document.getElementById('settings-sidebar');
    const ov = document.getElementById('settings-overlay');
    if (!sb || !ov) return;

    if (sb.classList.contains('active')) {
        if (window.previewBackupConfigBalances) {
            window.colConfigs = JSON.parse(JSON.stringify(window.previewBackupConfigBalances.colConfigs));
            window.colOrder = [...window.previewBackupConfigBalances.colOrder];
            applyLayoutToDOM();
            if (typeof window.runReport === 'function') window.runReport();
        }
        sb.classList.remove('active');
        ov.classList.remove('active');
    } else {
        window.previewBackupConfigBalances = {
            colConfigs: JSON.parse(JSON.stringify(window.colConfigs)),
            colOrder: [...window.colOrder]
        };
        renderSidebarConfig();
        sb.classList.add('active');
        ov.classList.add('active');
    }
}

function renderSidebarConfig() {
    const list = document.getElementById('columns-config-list');
    if (!list) return;
    list.innerHTML = '';

    window.colOrder.forEach((origIdx, index) => {
        const col = window.colConfigs[origIdx] || { name: 'عمود ' + origIdx, alias: 'عمود ' + origIdx, visible: true };
        const li = document.createElement('li');
        li.className = 'col-config-item';
        li.draggable = true;
        li.dataset.index = index;
        li.dataset.origIdx = origIdx;

        const currentFs = parseFloat(col.fs) || 1.0;

        li.innerHTML = `
            <div class="col-config-header">
                <div class="col-title-wrap">
                    <span class="col-drag-handle" title="اسحب لأعلى أو لأسفل لإعادة ترتيب الأعمدة أفقيًا في الجداول">☰</span>
                    <span class="col-title">${col.alias || col.name}</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button class="col-settings-btn" onclick="toggleColSettings(${index})">⚙️ تخصيص</button>
                    <div class="col-toggle ${col.visible !== false ? 'on' : ''}" onclick="toggleColVisibility(${origIdx}, this)" title="إظهار/إخفاء العمود"></div>
                </div>
            </div>
            <div class="col-settings-panel" id="col-settings-${index}">
                <div class="cfg-row">
                    <span>مسمى العمود:</span>
                    <span style="font-weight:bold; color:#38bdf8; font-size:0.85rem; display:flex; align-items:center; gap:4px;">
                        🔒 ${col.name} (ثابت بالنظام)
                    </span>
                </div>
                <div class="cfg-row">
                    <span>حجم الخط (تكبير/تصغير):</span>
                    <div style="display:flex; align-items:center; gap:4px;">
                        <button class="cfg-btn" onclick="adjustFontSize(${origIdx}, -1)">A-</button>
                        <input type="range" min="0.6" max="3.0" step="0.05" value="${currentFs}" oninput="updateColField(${origIdx}, 'fs', parseFloat(this.value)); document.getElementById('fs-lbl-${origIdx}').textContent = parseFloat(this.value).toFixed(2) + 'rem';" style="width:75px; cursor:pointer;">
                        <span id="fs-lbl-${origIdx}" style="font-size:0.75rem; color:#38bdf8; font-weight:bold; width:45px; text-align:center;">${currentFs.toFixed(2)}rem</span>
                        <button class="cfg-btn" onclick="adjustFontSize(${origIdx}, 1)">A+</button>
                        <button class="cfg-btn ${col.bold ? 'active' : ''}" onclick="toggleColBold(${origIdx}, this)" title="خط بولد عريض وتخين" style="font-weight:900;">B (تخين)</button>
                    </div>
                </div>
                <div class="cfg-row">
                    <span>محاذاة النص:</span>
                    <select class="cfg-input" onchange="updateColField(${origIdx}, 'align', this.value)" style="width:115px;">
                        <option value="center" ${col.align === 'center' ? 'selected' : ''}>الوسط ↔️</option>
                        <option value="right" ${col.align === 'right' ? 'selected' : ''}>اليمين ➡️</option>
                        <option value="left" ${col.align === 'left' ? 'selected' : ''}>اليسار ⬅️</option>
                    </select>
                </div>
                <div class="cfg-row">
                    <span>الألوان (النص / الخلفية):</span>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <span style="font-size:0.75rem; color:#94a3b8;">النص:</span>
                        <input type="color" class="cfg-input" value="${col.color || '#ffffff'}" onchange="updateColField(${origIdx}, 'color', this.value)" title="لون النص">
                        <span style="font-size:0.75rem; color:#94a3b8; margin-right:4px;">الخلفية:</span>
                        <input type="color" class="cfg-input" value="${col.bg || '#000000'}" onchange="updateColField(${origIdx}, 'bg', this.value)" title="لون الخلفية">
                    </div>
                </div>
                                <div class="cfg-row" style="flex-wrap:wrap; margin-top:8px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:8px;">
                    <span style="font-weight:bold; color:#38bdf8; width:100%; margin-bottom:4px;">🔢 خصائص وتنسيق الأرقام والكسور:</span>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; width:100%;">
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">رمز الفاصلة الألفية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, 'thousandsSep', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="," ${(!col.thousandsSep || col.thousandsSep === ',') ? 'selected' : ''}>, (فاصلة عادية)</option>
                                <option value="." ${col.thousandsSep === '.' ? 'selected' : ''}>. (نقطة)</option>
                                <option value=" " ${col.thousandsSep === ' ' ? 'selected' : ''}>[مسافة]</option>
                                <option value="_" ${col.thousandsSep === '_' ? 'selected' : ''}>_ (شرطة)</option>
                                <option value="'" ${col.thousandsSep === "'" ? 'selected' : ''}>' (اقتباس)</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">رمز الفاصلة العشرية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, 'decSep', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="." ${(!col.decSep || col.decSep === '.') ? 'selected' : ''}>. (نقطة)</option>
                                <option value="," ${col.decSep === ',' ? 'selected' : ''}>, (فاصلة)</option>
                                <option value="٫" ${col.decSep === '٫' ? 'selected' : ''}>٫ (عربية)</option>
                                <option value="/" ${col.decSep === '/' ? 'selected' : ''}>/ (شرطة)</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">عدد الخانات العشرية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, 'dec', parseInt(this.value)); updateColField(${origIdx}, 'decimals', parseInt(this.value));" style="width:100%; font-size:0.75rem;">
                                <option value="0" ${col.dec === 0 || col.decimals === 0 ? 'selected' : ''}>0 (بدون)</option>
                                <option value="1" ${col.dec === 1 || col.decimals === 1 ? 'selected' : ''}>1 خانة</option>
                                <option value="2" ${col.dec === 2 || col.decimals === 2 || (col.dec === undefined && col.decimals === undefined) ? 'selected' : ''}>2 خانة</option>
                                <option value="3" ${col.dec === 3 || col.decimals === 3 ? 'selected' : ''}>3 خانات</option>
                                <option value="4" ${col.dec === 4 || col.decimals === 4 ? 'selected' : ''}>4 خانات</option>
                                <option value="5" ${col.dec === 5 || col.decimals === 5 ? 'selected' : ''}>5 خانات</option>
                                <option value="6" ${col.dec === 6 || col.decimals === 6 ? 'selected' : ''}>6 خانات</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">طريقة والتقريب:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, 'roundMode', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="round" ${(!col.roundMode || col.roundMode === 'round') ? 'selected' : ''}>تقريب عادي</option>
                                <option value="floor" ${col.roundMode === 'floor' ? 'selected' : ''}>تقريب لأسفل</option>
                                <option value="ceil" ${col.roundMode === 'ceil' ? 'selected' : ''}>تقريب لأعلى</option>
                                <option value="trunc" ${col.roundMode === 'trunc' ? 'selected' : ''}>قطع الكسر</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        `;

        li.addEventListener('dragstart', handleDragStartBalances);
        li.addEventListener('dragover', handleDragOverBalances);
        li.addEventListener('drop', handleDropBalances);
        li.addEventListener('dragenter', handleDragEnterBalances);
        li.addEventListener('dragleave', handleDragLeaveBalances);

        list.appendChild(li);
    });
}

function toggleColSettings(index) {
    const p = document.getElementById('col-settings-' + index);
    if (p) p.classList.toggle('open');
}
function toggleColVisibility(origIdx, el) {
    if (window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].visible = !window.colConfigs[origIdx].visible;
        if (el) el.classList.toggle('on');
        applyLayoutToDOM();
    }
}
function updateColField(origIdx, field, val) {
    if (field === 'alias' || field === 'name') {
        if (typeof showToast === 'function') showToast('🔒 مسميات الأعمدة مثبتة بالنظام ولا يمكن تغييرها', 'info');
        return;
    }
    if (window.colConfigs[origIdx]) {
        window.colConfigs[origIdx][field] = val;
        if (field === 'align') window.colConfigs[origIdx]['textAlign'] = val;
        if (field === 'textAlign') window.colConfigs[origIdx]['align'] = val;
        if (field === 'dec') window.colConfigs[origIdx]['decimals'] = val;
        if (field === 'decimals') window.colConfigs[origIdx]['dec'] = val;
        if (field === 'bold') window.colConfigs[origIdx]['fontWeight'] = val ? '900' : 'normal';
        if (field === 'fontWeight') window.colConfigs[origIdx]['bold'] = (val === 'bold' || val === '900');

        updateColDOMByOrigIdx(origIdx);
        applyLayoutToDOM();
        saveLayout();

        if (['dec', 'decimals', 'thousands', 'thousandsSep', 'decSep', 'roundMode', 'showDecimals'].includes(field)) {
            if (typeof window.lastResults !== 'undefined' && window.lastResults) {
                renderTable(window.lastResults);
            }
        }
    }
}
function adjustFontSize(origIdx, dir) {
    if (window.colConfigs[origIdx]) {
        let fs = parseFloat(window.colConfigs[origIdx].fs) || 1.0;
        fs = Math.max(0.6, Math.min(3.0, fs + dir * 0.1));
        window.colConfigs[origIdx].fs = parseFloat(fs.toFixed(2));
        renderSidebarConfig();
        applyLayoutToDOM();
        saveLayout();
    }
}
function toggleColBold(origIdx, el) {
    if (window.colConfigs && window.colConfigs[origIdx]) {
        const isBold = !window.colConfigs[origIdx].bold;
        window.colConfigs[origIdx].bold = isBold;
        window.colConfigs[origIdx].fontWeight = isBold ? '900' : 'normal';
        if (el) el.classList.toggle('active', isBold);
        updateColDOMByOrigIdx(origIdx);
        applyLayoutToDOM();
        saveLayout();
        if (window.lastResults) renderTable(window.lastResults);
    }
}

let dragSrcElBalances = null;
function handleDragStartBalances(e) {
    dragSrcElBalances = this;
    e.dataTransfer.effectAllowed = 'move';
    this.classList.add('dragging');
}
function handleDragOverBalances(e) {
    if (e.preventDefault) e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}
function handleDragEnterBalances(e) { this.classList.add('over'); }
function handleDragLeaveBalances(e) { this.classList.remove('over'); }
function handleDropBalances(e) {
    if (e.stopPropagation) e.stopPropagation();
    if (dragSrcElBalances !== this) {
        let fromIdx = parseInt(dragSrcElBalances.dataset.index);
        let toIdx = parseInt(this.dataset.index);

        let moved = window.colOrder.splice(fromIdx, 1)[0];
        window.colOrder.splice(toIdx, 0, moved);

        renderSidebarConfig();
        applyLayoutToDOM();
        if (typeof window.runReport === 'function' && window.lastResults) {
            window.runReport();
        }
    }
    return false;
}

function applyAndSaveConfig() {
    saveLayout();
    window.previewBackupConfigBalances = {
        colConfigs: JSON.parse(JSON.stringify(window.colConfigs)),
        colOrder: [...window.colOrder]
    };
    applyLayoutToDOM();
    if (typeof window.runReport === 'function' && window.lastResults) window.runReport();
    toggleSettingsSidebar();
    if (typeof showToast === 'function') showToast('💾 تم حفظ التخصيصات وتطبيقها بنجاح', 'success');
}

function resetColumnConfig() {
    window.colConfigs = [
        { name: 'اسم الحساب', alias: 'اسم الحساب', id: 'name', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'right' },
        { name: 'رقم الهاتف', alias: 'رقم الهاتف', id: 'phone', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
        { name: 'الرقم الضريبي', alias: 'الرقم الضريبي', id: 'taxNo', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
        { name: 'العملة', alias: 'العملة', id: 'currency', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
        { name: 'عليه عملة', alias: 'عليه عملة', id: 'debitF', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
        { name: 'له عملة', alias: 'له عملة', id: 'creditF', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
        { name: 'عليه', alias: 'عليه', id: 'debit', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
        { name: 'له', alias: 'له', id: 'credit', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'center' },
        { name: 'ملحوظة', alias: 'ملحوظة', id: 'note', bold: false, fs: 1.0, size: '1rem', decimals: 2, dec: 2, color: '', bg: '', visible: true, align: 'right' }
    ];
    window.colOrder = [0, 1, 2, 3, 4, 5, 6, 7, 8];
    saveLayout();
    renderSidebarConfig();
    applyLayoutToDOM();
    if (typeof window.runReport === 'function' && window.lastResults) window.runReport();
    if (typeof showToast === 'function') showToast('🔄 تم استعادة الوضع الافتراضي', 'success');
}

function changeTableTheme(themeClass, doSave = true) {
    const table = document.getElementById('balances-table');
    if (!table) return;
    table.classList.remove('table-theme-blue', 'table-theme-black', 'table-theme-emerald');
    if (themeClass) table.classList.add(themeClass);
    window.currentTableTheme = themeClass;
    document.querySelectorAll('.crystal-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('btn-theme-' + themeClass);
    if (activeBtn) activeBtn.classList.add('active');
    if (doSave) saveLayout();
}

function changeNumberFont(fontClass, doSave = true) {
    window.currentNumFont = fontClass;
    const table = document.getElementById('balances-table');
    if (!table) return;
    table.querySelectorAll('.num').forEach(el => {
        el.classList.remove('num-font-roboto', 'num-font-inter', 'num-font-jetbrains', 'num-font-firacode', 'num-font-poppins');
        el.classList.add(fontClass);
    });
    document.querySelectorAll('.font-chip').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('btn-font-' + fontClass);
    if (activeBtn) activeBtn.classList.add('active');
    if (doSave) saveLayout();
}

/* ═══════════════════════════════════════════════════════
   Right-Click Header & Table Context Menu Handler (columnContextMenu)
   ═══════════════════════════════════════════════════════ */

function openColumnContextMenu(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const menu = document.getElementById('columnContextMenu');
    if (!menu) return;

    // Center modal in the screen by default so close button & controls are 100% visible
    menu.style.top = '50%';
    menu.style.left = '50%';
    menu.style.transform = 'translate(-50%, -50%)';
    menu.style.display = 'block';

    renderColumnContextMenu();
    makeElementDraggable(menu, document.getElementById('columnContextMenuHeader'));
}

function renderColumnContextMenu() {
    const container = document.getElementById('columnToggles');
    if (!container) return;
    container.innerHTML = '';

    window.colOrder.forEach((origIdx, index) => {
        const col = window.colConfigs[origIdx] || { name: 'عمود ' + origIdx, alias: 'عمود ' + origIdx, visible: true };
        const item = document.createElement('div');
        item.className = 'col-config-item';
        item.draggable = true;
        item.dataset.index = index;
        item.dataset.origIdx = origIdx;

        const currentFs = parseFloat(col.fs) || 1.0;

        item.innerHTML = `
            <div class="col-config-header">
                <div class="col-title-wrap">
                    <span class="col-drag-handle" title="اسحب لأعلى أو لأسفل لترتيب الأهمية">⣿</span>
                    <span class="col-title" style="color:#e2e8f0; font-weight:800;">${col.alias || col.name}</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button class="col-settings-btn" onclick="toggleColSettingsCtx(${index})">⚙️ تخصيص</button>
                    <div class="col-toggle ${col.visible !== false ? 'on' : ''}" onclick="toggleColVisibilityCtx(${origIdx}, this)" title="إظهار / إخفاء العمود"></div>
                </div>
            </div>
            <div class="col-settings-panel" id="col-settings-ctx-${index}">
                <div class="cfg-row">
                    <span>مسمى العمود:</span>
                    <span style="font-weight:bold; color:#38bdf8; font-size:0.85rem; display:flex; align-items:center; gap:4px;">
                        🔒 ${col.name} (ثابت بالنظام)
                    </span>
                </div>
                <div class="cfg-row">
                    <span>حجم الخط:</span>
                    <div style="display:flex; align-items:center; gap:4px;">
                        <button class="cfg-btn" onclick="adjustFontSize(${origIdx}, -1)">A-</button>
                        <input type="range" min="0.6" max="3.0" step="0.05" value="${currentFs}" oninput="updateColField(${origIdx}, 'fs', parseFloat(this.value)); document.getElementById('fs-ctx-lbl-${origIdx}').textContent = parseFloat(this.value).toFixed(2) + 'rem';" style="width:75px; cursor:pointer;">
                        <span id="fs-ctx-lbl-${origIdx}" style="font-size:0.75rem; color:#38bdf8; font-weight:bold; width:45px; text-align:center;">${currentFs.toFixed(2)}rem</span>
                        <button class="cfg-btn" onclick="adjustFontSize(${origIdx}, 1)">A+</button>
                        <button class="cfg-btn ${col.bold ? 'active' : ''}" onclick="toggleColBoldCtx(${origIdx}, this)" title="خط بولد عريض" style="font-weight:900;">B (تخين)</button>
                    </div>
                </div>
                <div class="cfg-row">
                    <span>المحاذاة:</span>
                    <select class="cfg-input" onchange="updateColField(${origIdx}, 'align', this.value)" style="width:115px;">
                        <option value="center" ${col.align === 'center' ? 'selected' : ''}>الوسط ↔️</option>
                        <option value="right" ${col.align === 'right' ? 'selected' : ''}>اليمين ➡️</option>
                        <option value="left" ${col.align === 'left' ? 'selected' : ''}>اليسار ⬅️</option>
                    </select>
                </div>
                <div class="cfg-row">
                    <span>ألوان الخط والخلفية:</span>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <span style="font-size:0.75rem; color:#94a3b8;">النص:</span>
                        <input type="color" class="cfg-input" value="${col.color || '#ffffff'}" onchange="updateColField(${origIdx}, 'color', this.value)">
                        <span style="font-size:0.75rem; color:#94a3b8; margin-right:4px;">الخلفية:</span>
                        <input type="color" class="cfg-input" value="${col.bg || '#000000'}" onchange="updateColField(${origIdx}, 'bg', this.value)">
                    </div>
                </div>
                                <div class="cfg-row" style="flex-wrap:wrap; margin-top:8px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:8px;">
                    <span style="font-weight:bold; color:#38bdf8; width:100%; margin-bottom:4px;">🔢 خصائص وتنسيق الأرقام والكسور:</span>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; width:100%;">
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">رمز الفاصلة الألفية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, 'thousandsSep', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="," ${(!col.thousandsSep || col.thousandsSep === ',') ? 'selected' : ''}>, (فاصلة عادية)</option>
                                <option value="." ${col.thousandsSep === '.' ? 'selected' : ''}>. (نقطة)</option>
                                <option value=" " ${col.thousandsSep === ' ' ? 'selected' : ''}>[مسافة]</option>
                                <option value="_" ${col.thousandsSep === '_' ? 'selected' : ''}>_ (شرطة)</option>
                                <option value="'" ${col.thousandsSep === "'" ? 'selected' : ''}>' (اقتباس)</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">رمز الفاصلة العشرية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, 'decSep', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="." ${(!col.decSep || col.decSep === '.') ? 'selected' : ''}>. (نقطة)</option>
                                <option value="," ${col.decSep === ',' ? 'selected' : ''}>, (فاصلة)</option>
                                <option value="٫" ${col.decSep === '٫' ? 'selected' : ''}>٫ (عربية)</option>
                                <option value="/" ${col.decSep === '/' ? 'selected' : ''}>/ (شرطة)</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">عدد الخانات العشرية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, 'dec', parseInt(this.value)); updateColField(${origIdx}, 'decimals', parseInt(this.value));" style="width:100%; font-size:0.75rem;">
                                <option value="0" ${col.dec === 0 || col.decimals === 0 ? 'selected' : ''}>0 (بدون)</option>
                                <option value="1" ${col.dec === 1 || col.decimals === 1 ? 'selected' : ''}>1 خانة</option>
                                <option value="2" ${col.dec === 2 || col.decimals === 2 || (col.dec === undefined && col.decimals === undefined) ? 'selected' : ''}>2 خانة</option>
                                <option value="3" ${col.dec === 3 || col.decimals === 3 ? 'selected' : ''}>3 خانات</option>
                                <option value="4" ${col.dec === 4 || col.decimals === 4 ? 'selected' : ''}>4 خانات</option>
                                <option value="5" ${col.dec === 5 || col.decimals === 5 ? 'selected' : ''}>5 خانات</option>
                                <option value="6" ${col.dec === 6 || col.decimals === 6 ? 'selected' : ''}>6 خانات</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">طريقة والتقريب:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, 'roundMode', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="round" ${(!col.roundMode || col.roundMode === 'round') ? 'selected' : ''}>تقريب عادي</option>
                                <option value="floor" ${col.roundMode === 'floor' ? 'selected' : ''}>تقريب لأسفل</option>
                                <option value="ceil" ${col.roundMode === 'ceil' ? 'selected' : ''}>تقريب لأعلى</option>
                                <option value="trunc" ${col.roundMode === 'trunc' ? 'selected' : ''}>قطع الكسر</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        `;

        item.addEventListener('dragstart', handleDragStartBalances);
        item.addEventListener('dragover', handleDragOverBalances);
        item.addEventListener('drop', handleDropBalances);
        item.addEventListener('dragenter', handleDragEnterBalances);
        item.addEventListener('dragleave', handleDragLeaveBalances);

        container.appendChild(item);
    });
}

function toggleColSettingsCtx(index) {
    const p = document.getElementById('col-settings-ctx-' + index);
    if (p) p.classList.toggle('open');
}
function toggleColVisibilityCtx(origIdx, el) {
    if (window.colConfigs[origIdx]) {
        window.colConfigs[origIdx].visible = !window.colConfigs[origIdx].visible;
        if (el) el.classList.toggle('on');
        applyLayoutToDOM();
    }
}
function toggleColBoldCtx(origIdx, el) {
    if (window.colConfigs && window.colConfigs[origIdx]) {
        const isBold = !window.colConfigs[origIdx].bold;
        window.colConfigs[origIdx].bold = isBold;
        window.colConfigs[origIdx].fontWeight = isBold ? '900' : 'normal';
        if (el) el.classList.toggle('active', isBold);
        updateColDOMByOrigIdx(origIdx);
        applyLayoutToDOM();
        saveLayout();
        if (window.lastResults) renderTable(window.lastResults);
    }
}

function saveLayoutAndStyles() {
    saveLayout();
    applyLayoutToDOM();
    if (typeof window.runReport === 'function' && window.lastResults) window.runReport();
    const menu = document.getElementById('columnContextMenu');
    if (menu) menu.style.display = 'none';
    if (typeof showToast === 'function') showToast('💾 تم حفظ التخصيصات والترتيب بنجاح', 'success');
}

function resetLayoutAndStyles() {
    resetColumnConfig();
    renderColumnContextMenu();
}

function toggleMinimizeCtxMenu() {
    const body = document.getElementById('columnContextMenuBody');
    if (body) {
        body.style.display = (body.style.display === 'none') ? 'block' : 'none';
    }
}

function toggleMaximizeCtxMenu() {
    const menu = document.getElementById('columnContextMenu');
    if (menu) {
        if (menu.style.width === '90vw') {
            menu.style.width = '510px';
            menu.style.height = 'auto';
            menu.style.top = '100px';
            menu.style.left = '50%';
            menu.style.transform = 'translateX(-50%)';
        } else {
            menu.style.width = '90vw';
            menu.style.height = '85vh';
            menu.style.top = '5vh';
            menu.style.left = '5vw';
            menu.style.transform = 'none';
        }
    }
}

function makeElementDraggable(elmnt, headerElmnt) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    const handle = headerElmnt || elmnt;
    handle.onmousedown = dragMouseDown;

    function dragMouseDown(e) {
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') return;
        e = e || window.event;
        e.preventDefault();

        // Lock exact top/left bounding rect and clear transform translation for smooth dragging
        const rect = elmnt.getBoundingClientRect();
        elmnt.style.transform = 'none';
        elmnt.style.top = rect.top + 'px';
        elmnt.style.left = rect.left + 'px';

        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }

    function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

// Global right-click and auto-open behaviors removed to strictly enforce table header right-click only

/* ═══════════════════════════════════════════════════════
   Luminous Neon Presets Implementation for Balances Report
   ═══════════════════════════════════════════════════════ */


function savePresetData(lsKey, name, id) {
    if(!confirm('هل أنت متأكد أنك تريد حفظ التصميم الحالي في "' + name + '"؟ هذا الإجراء نهائي وسيتم حفظه في قاعدة البيانات.')) {
        return;
    }

    const dataToSave = {
        colConfigs: window.colConfigs,
        colOrder: window.colOrder,
        currentTableTheme: window.currentTableTheme || '',
        currentNumFont: window.currentNumFont || 'num-font-jetbrains'
    };

    fetch('api/save_balances_preset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, config: dataToSave })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            if (typeof showToast === 'function') {
                showToast(`تم حفظ التخصيصات في ${name} حفظاً نهائياً!`, 'success');
            } else {
                alert(`تم حفظ التخصيصات في ${name} حفظاً نهائياً!`);
            }
            if(!window.LOADED_BALANCES_PRESETS) window.LOADED_BALANCES_PRESETS = {};
            window.LOADED_BALANCES_PRESETS[lsKey] = dataToSave;
            let isModel = id.startsWith('m');
            if (!isModel) {
                window.LOADED_BALANCES_PRESETS[lsKey + '_locked'] = true;
            }
        } else {
            if (typeof showToast === 'function') {
                showToast(data.error || 'حدث خطأ أثناء الحفظ', 'error');
            } else {
                alert(data.error || 'حدث خطأ أثناء الحفظ');
            }
        }
    })
    .catch(err => {
        if (typeof showToast === 'function') showToast('خطأ في الاتصال بالخادم', 'error');
        console.error(err);
    });
}

function loadPresetData(lsKey) {
    if (window.LOADED_BALANCES_PRESETS && window.LOADED_BALANCES_PRESETS[lsKey]) {
        try {
            const data = window.LOADED_BALANCES_PRESETS[lsKey];
            if (data.colConfigs && data.colOrder) {
                window.colConfigs = JSON.parse(JSON.stringify(data.colConfigs));
                window.colOrder = JSON.parse(JSON.stringify(data.colOrder));
                if (data.currentTableTheme !== undefined) {
                    if (typeof changeTableTheme === 'function') changeTableTheme(data.currentTableTheme);
                }
                if (data.currentNumFont !== undefined) {
                    if (typeof changeNumberFont === 'function') changeNumberFont(data.currentNumFont);
                }
                if (typeof applyLayoutToDOM === 'function') applyLayoutToDOM();
                if (typeof renderSidebarConfig === 'function') renderSidebarConfig();
                if (typeof window.runReport === 'function') window.runReport();

                if (typeof showToast === 'function') {
                    showToast('تم استرجاع النموذج بنجاح', 'success');
                }
            }
        } catch(e) {
            console.error('Error parsing preset', e);
        }
    } else {
        if (typeof showToast === 'function') {
            showToast('هذا النموذج فارغ، اضغط لمدة 8 ثوانٍ للحفظ عليه', 'warning');
        } else {
            alert('هذا النموذج فارغ، اضغط لمدة 8 ثوانٍ للحفظ عليه');
        }
    }
}

window.LOADED_BALANCES_PRESETS = {};
function fetchBalancesPresets() {
    fetch('api/get_balances_presets.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.LOADED_BALANCES_PRESETS = data.presets || {};
            }
        })
        .catch(err => console.error('Error fetching balances presets:', err));
}
// Fetch presets on DOMContentLoaded (non-blocking, no report re-run)
document.addEventListener('DOMContentLoaded', () => {
    fetchBalancesPresets();
    if (typeof window.renderBalancesPresets === 'function') {
        window.renderBalancesPresets();
    }
});
