// js/preview-ledger.js - General Ledger Pro Studio Edition

const PRESETS_STORAGE_KEY = 'ledger_preview_presets_v7';
const MAX_PRESETS_LIMIT = 7;
let currentData = [];
let filteredData = [];
let currentPresetIndex = 0;
let selectedColIndex = null;
let currentTheme = '';
let currentFont = 'font-ultra';
let currentPaperSize = 'A4';
let currentOrientation = 'landscape';
let showHeaderLogo = true;
let showHeaderInfo = true;
let isLongPressTriggered = false;
let longPressInterval = null;

// Default column definitions with exact field mapping for General Ledger
const baseColumns = [
    { idx: 0, defaultLabel: "التاريخ", field: "date", isNum: false },
    { idx: 1, defaultLabel: "الرقم المرجعي", field: "ref", isNum: false },
    { idx: 2, defaultLabel: "البيان / الوصف", field: "statement", isNum: false },
    { idx: 3, defaultLabel: "الحساب المقابل", field: "counterAccount", isNum: false },
    { idx: 4, defaultLabel: "نوع الحركة", field: "typeLabel", isNum: false },
    { idx: 13, defaultLabel: "رقم التحويلة", field: "transferNo", isNum: false },
    { idx: 15, defaultLabel: "اسم البنك", field: "bankName", isNum: false },
    { idx: 14, defaultLabel: "رقم الشيك", field: "checkNo", isNum: false },
    { idx: 5, defaultLabel: "مدين (محلي)", field: "debit", isNum: true },
    { idx: 6, defaultLabel: "دائن (محلي)", field: "credit", isNum: true },
    { idx: 7, defaultLabel: "مدين (أجنبي)", field: "debitForeign", isNum: true },
    { idx: 8, defaultLabel: "دائن (أجنبي)", field: "creditForeign", isNum: true },
    { idx: 9, defaultLabel: "سعر الصرف", field: "exchangeRate", isNum: true },
    { idx: 10, defaultLabel: "الإجمالي المحلي", field: "balance", isNum: true },
    { idx: 11, defaultLabel: "الإجمالي الأجنبي", field: "balanceForeign", isNum: true },
    { idx: 12, defaultLabel: "ملاحظات", field: "additionalNote", isNum: false }
];

function getRawFieldValue(row, field) {
    if (!row) return '';
    if (field === 'date') {
        let rawDate = String(row.date || row.created_at || row.time || '').trim();
        if (rawDate.includes('T')) rawDate = rawDate.split('T')[0];
        if (rawDate.includes(' ')) rawDate = rawDate.split(' ')[0];
        return rawDate;
    }
    if (field === 'ref') return row.ref || row.doc_no || row.voucher_no || row.id || '';
    if (field === 'statement') return row.statement || row.description || row.desc || row.notes || '';
    if (field === 'counterAccount') return row.counterAccount || row.accountName || row.account || '-';
    if (field === 'typeLabel') return row.typeLabel || row.type || '-';
    if (field === 'transferNo') return row.transferNo || row.transfer_no || row.transferNumber || row.transfer_number || row.mobileTransferNo || '-';
    if (field === 'bankName') return row.bankName || row.bank_name || '-';
    if (field === 'checkNo') return row.checkNo || row.check_no || row.checkNumber || row.check_number || row.chequeNo || '-';
    
    let debLoc = parseFloat(row.debit || row.dr || 0);
    let credLoc = parseFloat(row.credit || row.cr || 0);
    let exRate = parseFloat(row.exchangeRate || row.exRate || row.rate || 0);
    
    if (field === 'debit') return debLoc;
    if (field === 'credit') return credLoc;
    if (field === 'exchangeRate') return exRate > 0 ? exRate : '-';

    if (field === 'debitForeign') {
        let df = parseFloat(row.debitForeign || row.dr_foreign || 0);
        if ((df === 0 || df === debLoc) && debLoc > 0 && exRate > 1) {
            df = debLoc / exRate;
        }
        return df;
    }

    if (field === 'creditForeign') {
        let cf = parseFloat(row.creditForeign || row.cr_foreign || 0);
        if ((cf === 0 || cf === credLoc) && credLoc > 0 && exRate > 1) {
            cf = credLoc / exRate;
        }
        return cf;
    }

    if (field === 'balance') {
        return parseFloat(row.balance !== undefined ? row.balance : (debLoc - credLoc));
    }

    if (field === 'balanceForeign') {
        let bf = parseFloat(row.balanceForeign !== undefined ? row.balanceForeign : ((row.debitForeign || 0) - (row.creditForeign || 0)));
        if ((bf === 0 || Math.abs(bf) === Math.abs(row.balance || 0)) && row.balance && exRate > 1) {
            bf = row.balance / exRate;
        }
        return bf;
    }

    if (field === 'additionalNote') return row.additionalNote || row.notes || row.costCenter || '';
    return row[field] !== undefined ? row[field] : '';
}

let columnOrder = [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12];
let columnConfigs = {};

function loadCompanyHeader() {
    try {
        let cfg = {};
        let user = {};
        try { user = JSON.parse(localStorage.getItem('user') || '{}'); } catch(e){}
        
        if ((!cfg || !cfg.companyName) && window.DB_STORE && window.getTenantKey) {
            const tk = window.getTenantKey('erp_settings_v1');
            if (window.DB_STORE[tk]) {
                try { cfg = typeof window.DB_STORE[tk] === 'string' ? JSON.parse(window.DB_STORE[tk]) : window.DB_STORE[tk]; } catch(e){}
            }
        }
        
        const cName = document.getElementById('hdr-c-name');
        const cReg = document.getElementById('hdr-c-reg');
        const cVat = document.getElementById('hdr-c-vat');
        const cPhone = document.getElementById('hdr-c-phone');
        const cAddr = document.getElementById('hdr-c-addr');
        const cLogo = document.getElementById('company-logo');
        const logoBlock = document.getElementById('company-logo-block');

        const finalName = cfg.companyName || user.company_name || localStorage.getItem('companyName') || '';
        if (cName && finalName) {
            cName.innerText = finalName;
        }
        
        const regVal = cfg.regNumber || user.reg_number;
        if (cReg) {
            if (regVal) {
                cReg.innerText = 'سجل تجاري: ' + regVal;
                cReg.style.display = 'block';
            } else {
                cReg.style.display = 'none';
            }
        }
        
        const taxVal = cfg.taxNumber || user.tax_number || user.vat_number;
        if (cVat) {
            if (taxVal) {
                cVat.innerText = 'الرقم الضريبي: ' + taxVal;
                cVat.style.display = 'block';
            } else {
                cVat.style.display = 'none';
            }
        }

        const phoneVal = cfg.phone || cfg.whatsapp || user.phone;
        if (cPhone) {
            if (phoneVal) {
                cPhone.innerText = 'الهاتف: ' + phoneVal;
                cPhone.style.display = 'block';
            } else {
                cPhone.style.display = 'none';
            }
        }
        
        const addrVal = cfg.address || user.address;
        if (cAddr) {
            if (addrVal) {
                cAddr.innerText = addrVal;
                cAddr.style.display = 'block';
            } else {
                cAddr.style.display = 'none';
            }
        }
        
        const logoUrl = cfg.logoBase64 || localStorage.getItem('company_logo_base64') || user.logo || user.logo_url;
        if (cLogo && logoBlock) {
            if (logoUrl && logoUrl !== 'null' && logoUrl.trim() !== '') {
                cLogo.src = logoUrl;
                cLogo.style.display = 'block';
                logoBlock.style.display = showHeaderLogo ? 'flex' : 'none';
            } else {
                logoBlock.style.display = 'none';
            }
        }
        
        updateSubheaderLabels();
    } catch(e) { console.error('Error loading company header', e); }
}

function updateSubheaderLabels() {
    try {
        let configStr = localStorage.getItem('preview_ledger_config');
        let config = { filterAccount: 'ALL', dateMode: 'all' };
        if (configStr) {
            try { config = JSON.parse(configStr); } catch(e){}
        }

        const accVal = config.filterAccount || 'ALL';
        const fromDate = config.dateFrom || '';
        const toDate = config.dateTo || '';
        const currentDateMode = config.dateMode || 'all';

        // 1. Customer / Account Name Label
        const custLbl = document.getElementById('report-customer-name-lbl');
        if (custLbl) {
            if (accVal !== 'ALL') {
                let accName = typeof getAccountName === 'function' ? getAccountName(accVal) : accVal;
                custLbl.innerText = 'السيد / ' + accName;
            } else {
                custLbl.innerText = 'السيد / جميع الحسابات';
            }
        }

        // 2. Report Date Label
        const dateLbl = document.getElementById('report-date-lbl');
        if (dateLbl) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            dateLbl.innerText = 'تاريخ التقرير: ' + yyyy + '-' + mm + '-' + dd;
        }

        // 3. Period Label
        const periodLbl = document.getElementById('report-period-lbl');
        if (periodLbl) {
            if (currentDateMode !== 'all' && fromDate && toDate) {
                periodLbl.innerText = 'الفترة: من ' + fromDate + ' إلى ' + toDate;
            } else {
                periodLbl.innerText = 'الفترة: كلي (جميع الفترات)';
            }
        }
    } catch(e) {
        console.error('Error updating subheader labels:', e);
    }
}


function calculateLedgerTxns() {
    ALL_TXNS = typeof collectAllTransactions === 'function' ? collectAllTransactions() : [];
    
    let configStr = localStorage.getItem('preview_ledger_config');
    let config = { filterAccount: 'ALL', dateMode: 'all' };
    if (configStr) {
        try { config = JSON.parse(configStr); } catch(e){}
    }

    const accVal = config.filterAccount || 'ALL';
    const fromDate = config.dateFrom || '';
    const toDate = config.dateTo || '';
    const typeFilter = config.filterType || 'ALL';
    const ccFilter = config.filterCostCenter || 'ALL';
    const curFilter = config.filterCurrency || 'ALL';
    const postedFilter = config.filterPosted || 'ALL';
    const smartSearch = (config.filterSmart || '').trim().toLowerCase();
    const currentDateMode = config.dateMode || 'all';

    let txns = ALL_TXNS;
    let priorTxns = [];
    let currentTxns = [];

    // Account Filter & Opening Balance Resolver
    let opInfo = { intrinsicBal: 0, opDebitLoc: 0, opCreditLoc: 0, opDebitFc: 0, opCreditFc: 0, opBalFc: 0, exchangeRate: 1, currency: 'SDG' };
    
    if (accVal !== 'ALL') {
        const cleanFilter = accVal.replace(/^(cust|supp|bank|safe|exp)-/, '');
        const selectedAccName = (typeof getAccountName === 'function') ? getAccountName(accVal) : '';
        txns = txns.filter(t => {
            if (!t) return false;
            const tAcc = String(t.account || '').trim();
            if (tAcc === String(accVal)) return true;
            if (tAcc === cleanFilter) return true;
            
            const cleanTAcc = tAcc.replace(/^(cust|supp|bank|safe|exp)-/, '');
            if (cleanTAcc === cleanFilter) return true;

            if (selectedAccName && selectedAccName !== accVal) {
                const tAccName = (typeof getAccountName === 'function') ? getAccountName(tAcc) : '';
                if (tAccName && tAccName === selectedAccName) return true;
                if (tAcc === selectedAccName) return true;
            }
            return false;
        });

        // Resolve opening balance from Master Data records (Customers, Suppliers, Banks, Safes, Accounts)
        // Read system USD exchange rate from ALL possible sources
        let systemUsdRate = 1;
        
        // Source 1: getExchangeRateForCurrency (centralized, reads settings + API)
        if (typeof window.getExchangeRateForCurrency === 'function') {
            const r = window.getExchangeRateForCurrency('USD');
            if (r && r > 1) systemUsdRate = parseFloat(r);
        }
        
        // Source 2: ledgerExchangeRate (set by general-ledger.js from settings/API)
        if (systemUsdRate <= 1 && typeof ledgerExchangeRate !== 'undefined' && ledgerExchangeRate > 1) {
            systemUsdRate = ledgerExchangeRate;
        }
        
        // Source 3: window._gemInitialRate (set by app.js on load)
        if (systemUsdRate <= 1 && window._gemInitialRate && parseFloat(window._gemInitialRate) > 1) {
            systemUsdRate = parseFloat(window._gemInitialRate);
        }
        
        // Source 4: erp_settings_v1.rateUSD (manual entry in settings)
        if (systemUsdRate <= 1) {
            try {
                const settings = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (settings.rateUSD && parseFloat(settings.rateUSD) > 1) {
                    systemUsdRate = parseFloat(settings.rateUSD);
                }
            } catch(e){}
        }
        
        // Source 5: erp_rate_usd (dedicated localStorage key)
        if (systemUsdRate <= 1) {
            try {
                const dedicated = localStorage.getItem('erp_rate_usd');
                if (dedicated && parseFloat(dedicated) > 1) systemUsdRate = parseFloat(dedicated);
            } catch(e){}
        }
        
        // Source 6: erp_cached_global_rate (cached from subscribers panel)
        if (systemUsdRate <= 1) {
            try {
                const cached = localStorage.getItem('erp_cached_global_rate');
                if (cached && parseFloat(cached) > 1) systemUsdRate = parseFloat(cached);
            } catch(e){}
        }
        
        // Source 7: erp_currency_rates_v1 (currency rates table)
        if (systemUsdRate <= 1) {
            try {
                const rates = JSON.parse(localStorage.getItem('erp_currency_rates_v1') || '{}');
                if (rates['USD'] && parseFloat(rates['USD']) > 1) systemUsdRate = parseFloat(rates['USD']);
            } catch(e){}
        }

        let custs = []; try { custs = (JSON.parse(localStorage.getItem('erp_customers_v1')) || []).filter(x => x && !x.deleted); } catch(e){}
        let sups = []; try { sups = (JSON.parse(localStorage.getItem('erp_suppliers_v1')) || []).filter(x => x && !x.deleted); } catch(e){}
        let banks = []; try { banks = (JSON.parse(localStorage.getItem('erp_banks_v1')) || []).filter(x => x && !x.deleted); } catch(e){}
        let safes = []; try { safes = (JSON.parse(localStorage.getItem('erp_safes_v1')) || []).filter(x => x && !x.deleted); } catch(e){}
        let accounts = []; try { accounts = (JSON.parse(localStorage.getItem('erp_accounts_v1')) || []).filter(x => x && !x.deleted); } catch(e){}
        
        if (typeof ALL_ACCOUNTS !== 'undefined' && Array.isArray(ALL_ACCOUNTS)) {
            accounts = accounts.concat(ALL_ACCOUNTS);
        }

        let masterRec = custs.find(x => x && (x.id === accVal || x.id === cleanFilter || x.name === accVal)) 
                     || sups.find(x => x && (x.id === accVal || x.id === cleanFilter || x.name === accVal)) 
                     || banks.find(x => x && (x.id === accVal || x.id === cleanFilter || x.name === accVal)) 
                     || safes.find(x => x && (x.id === accVal || x.id === cleanFilter || x.name === accVal))
                     || accounts.find(x => x && (x.id === accVal || x.id === cleanFilter || x.code === cleanFilter || x.name === accVal));
        
        let intrinsicBal = 0;
        let currency = 'SDG';
        let exchangeRate = 0;

        let entityDate = '';
        if (masterRec) {
            let rawDate = String(masterRec.date || masterRec.createdAt || masterRec.created_at || masterRec.timestamp || masterRec.addedDate || '').trim();
            if (rawDate.includes('T')) rawDate = rawDate.split('T')[0];
            if (rawDate.includes(' ')) rawDate = rawDate.split(' ')[0];
            entityDate = rawDate;
            let opDebit = parseFloat(masterRec.openingDebit || masterRec.debit || 0);
            let opCredit = parseFloat(masterRec.openingCredit || masterRec.credit || 0);
            let rawBal = parseFloat(masterRec.openingBalance || masterRec.opening_balance || masterRec.balance || 0);
            
            if (opDebit > 0 || opCredit > 0) {
                intrinsicBal = opDebit - opCredit;
            } else if (rawBal !== 0) {
                if (masterRec.balance_type === 'credit' || masterRec.balanceType === 'credit') {
                    intrinsicBal = -Math.abs(rawBal);
                } else {
                    intrinsicBal = Math.abs(rawBal);
                }
            }
            currency = masterRec.currency || 'SDG';
            // Read account-specific rate (only meaningful for foreign currency accounts)
            const savedAccountRate = parseFloat(masterRec.exchangeRate || masterRec.exRate || masterRec.rate || 0);
            if (savedAccountRate > 1) {
                exchangeRate = savedAccountRate;
            }
        }

        // Resolve final exchange rate based on currency type
        if (currency === 'SDG') {
            // SDG account: always use system USD rate for foreign column
            exchangeRate = systemUsdRate > 1 ? systemUsdRate : 1;
        } else if (!exchangeRate || exchangeRate <= 1) {
            // Foreign currency account without a valid rate
            if (typeof window.getExchangeRateForCurrency === 'function') {
                const specificRate = window.getExchangeRateForCurrency(currency);
                exchangeRate = (specificRate && specificRate > 1) ? parseFloat(specificRate) : (systemUsdRate > 1 ? systemUsdRate : 1);
            } else {
                exchangeRate = systemUsdRate > 1 ? systemUsdRate : 1;
            }
        }

        let opDebitLoc = intrinsicBal > 0 ? intrinsicBal : 0;
        let opCreditLoc = intrinsicBal < 0 ? Math.abs(intrinsicBal) : 0;

        let opDebitFc = exchangeRate > 1 ? (opDebitLoc / exchangeRate) : 0;
        let opCreditFc = exchangeRate > 1 ? (opCreditLoc / exchangeRate) : 0;
        let opBalFc = exchangeRate > 1 ? (intrinsicBal / exchangeRate) : 0;

        opInfo = {
            intrinsicBal,
            opDebitLoc,
            opCreditLoc,
            opDebitFc,
            opCreditFc,
            opBalFc,
            exchangeRate,
            currency,
            entityDate
        };
    }

    // Separate based on dates first
    if (currentDateMode === 'all' || !fromDate) {
        currentTxns = [...txns];
    } else {
        priorTxns = txns.filter(t => t.date < fromDate);
        currentTxns = txns.filter(t => t.date >= fromDate && t.date <= toDate);
    }

    // Apply Posted status filter
    if (postedFilter && postedFilter !== 'ALL') {
        const wantPosted = (postedFilter === 'POSTED');
        priorTxns = priorTxns.filter(t => t.isPosted === wantPosted);
        currentTxns = currentTxns.filter(t => t.isPosted === wantPosted);
    }

    if (typeFilter !== 'ALL') {
        currentTxns = currentTxns.filter(t => {
            if (typeFilter === 'sales' && t.type === 'invoice' && t.account !== 'revenue-sales') return true;
            if (typeFilter === 'purchases' && t.type === 'purchase' && t.account !== 'cost-purchases') return true;
            if (typeFilter === 'receipt' && (t.type === 'receipt' || t.type === 'multi-receipt') && t.debit > 0) return true;
            if (typeFilter === 'payment' && (t.type === 'payment' || t.type === 'multi-payment') && t.credit > 0) return true;
            if (typeFilter === 'journal' && t.type === 'jv') return true;
            return false;
        });
    }

    if (ccFilter !== 'ALL') currentTxns = currentTxns.filter(t => t.costCenter === ccFilter);
    if (curFilter === 'LOCAL') currentTxns = currentTxns.filter(t => t.currency === 'SDG');
    else if (curFilter === 'FOREIGN') currentTxns = currentTxns.filter(t => t.currency !== 'SDG');

    if (smartSearch) {
        currentTxns = currentTxns.filter(t => {
            return (t.statement && t.statement.toLowerCase().includes(smartSearch)) ||
                   (t.ref && String(t.ref).toLowerCase().includes(smartSearch)) ||
                   (t.additionalNote && t.additionalNote.toLowerCase().includes(smartSearch));
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

    currentTxns.sort((a, b) => {
        const da = (a.date || '') + ' ' + parseTimeTo24h(a.time);
        const db = (b.date || '') + ' ' + parseTimeTo24h(b.time);
        let cmp = da.localeCompare(db);
        if (cmp === 0) {
            cmp = String(a.ref || '').localeCompare(String(b.ref || ''), undefined, { numeric: true });
        }
        return cmp;
    });

    let runningBal = 0;
    let runningBalFc = 0;

    // Handle Opening Balance
    if (accVal !== 'ALL') {
        let hasOpening = (opInfo.intrinsicBal !== 0);
        let hasPrior = (currentDateMode !== 'all' && priorTxns.length > 0 && fromDate);
        
        let priorSum = 0;
        priorTxns.forEach(t => { priorSum += t.debit; priorSum -= t.credit; });

        let opDate = opInfo.entityDate || (txns.length > 0 ? txns.reduce((min, t) => t.date < min ? t.date : min, txns[0].date) : new Date().toISOString().split('T')[0]);

        if (hasOpening || currentDateMode === 'all' || !hasPrior) {
            currentTxns.unshift({
                date: opDate, time: '', ref: 'OPENING', type: 'opening', typeLabel: 'افتتاحي',
                account: accVal, counterAccount: '-', statement: 'رصيد افتتاحي', additionalNote: 'رصيد افتتاحي',
                debit: opInfo.opDebitLoc, 
                credit: opInfo.opCreditLoc, 
                debitForeign: opInfo.opDebitFc, 
                creditForeign: opInfo.opCreditFc, 
                exchangeRate: opInfo.exchangeRate, 
                costCenter: '', 
                currency: opInfo.currency, 
                balance: opInfo.intrinsicBal,
                balanceForeign: opInfo.opBalFc,
                isOpening: true
            });
        }
        
        if (hasPrior && priorSum !== 0) {
            let preDebit = priorSum > 0 ? priorSum : 0;
            let preCredit = priorSum < 0 ? Math.abs(priorSum) : 0;
            let preDebitFc = opInfo.exchangeRate > 0 ? (preDebit / opInfo.exchangeRate) : 0;
            let preCreditFc = opInfo.exchangeRate > 0 ? (preCredit / opInfo.exchangeRate) : 0;
            
            currentTxns.unshift({
                date: fromDate, time: '', ref: 'PRE-PERIOD', type: 'opening', typeLabel: 'افتتاحي',
                account: accVal, counterAccount: '-', statement: 'رصيد ما قبل الفترة', additionalNote: 'رصيد ما قبل الفترة',
                debit: preDebit, credit: preCredit, costCenter: '', 
                debitForeign: preDebitFc, creditForeign: preCreditFc, exchangeRate: opInfo.exchangeRate,
                currency: 'SDG', balance: priorSum, balanceForeign: opInfo.exchangeRate > 0 ? (priorSum / opInfo.exchangeRate) : 0,
                isOpening: true
            });
        }
    }

    currentTxns.forEach(t => {
        t.debit = parseFloat(t.debit || 0);
        t.credit = parseFloat(t.credit || 0);

        let rate = parseFloat(t.exchangeRate || t.exRate || t.rate || opInfo.exchangeRate || 1);
        if (!rate || rate <= 0) rate = opInfo.exchangeRate || 1;
        t.exchangeRate = rate;

        let df = parseFloat(t.debitForeign || t.dr_foreign || 0);
        let cf = parseFloat(t.creditForeign || t.cr_foreign || 0);

        // If local debit/credit is set and foreign amount is missing or equals local amount, calculate local / exchangeRate
        const isForeignTxn = t.currency !== 'SDG' || opInfo.currency !== 'SDG';
        if (t.debit > 0 && (df === 0 || df === t.debit) && rate > 1 && isForeignTxn) {
            df = t.debit / rate;
        }
        if (t.credit > 0 && (cf === 0 || cf === t.credit) && rate > 1 && isForeignTxn) {
            cf = t.credit / rate;
        }

        t.debitForeign = df;
        t.creditForeign = cf;

        if (t.isOpening) {
            runningBal += (t.debit - t.credit);
            runningBalFc += (t.debitForeign - t.creditForeign);
            t.balance = runningBal;
            t.balanceForeign = runningBalFc;
        } else {
            runningBal += (t.debit - t.credit);
            runningBalFc += (t.debitForeign - t.creditForeign);
            t.balance = runningBal;
            t.balanceForeign = runningBalFc;
        }

        if (t.balance !== undefined && t.balanceForeign !== undefined) {
            if (t.balance < 0) {
                t.balanceForeign = -Math.abs(t.balanceForeign);
            } else if (t.balance > 0) {
                t.balanceForeign = Math.abs(t.balanceForeign);
            }
        }
    });

    return currentTxns;
}

document.addEventListener('DOMContentLoaded', async () => {
    loadCompanyHeader();
    if (typeof buildAccountsList === 'function') buildAccountsList();
    
    let attempts = 0;
    const pollTimer = setInterval(() => {
        attempts++;
        loadCompanyHeader();
        if (attempts > 15) clearInterval(pollTimer);
    }, 300);

    window.addEventListener('DBReady', () => {
        loadCompanyHeader();
        if (typeof buildAccountsList === 'function') buildAccountsList();
    });

    // Load Ledger Table Data & Config (Try IndexedDB via localforage first for large datasets)
    if (typeof localforage !== 'undefined') {
        try {
            const lfData = await localforage.getItem('preview_ledger_data');
            if (Array.isArray(lfData) && lfData.length > 0) {
                currentData = lfData;
            }
        } catch(e) {
            console.error('Error reading from localforage:', e);
        }
    }

    if (!currentData || currentData.length === 0) {
        const rawData = localStorage.getItem('preview_ledger_data') || localStorage.getItem('preview_table_data');
        if (rawData) {
            try {
                const parsed = JSON.parse(rawData);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    currentData = parsed;
                }
            } catch(e) {
                console.error('Error parsing ledger data', e);
            }
        }
    }

    const rawConfig = localStorage.getItem('preview_ledger_config');
    if (rawConfig) {
        try {
            const cfg = JSON.parse(rawConfig);
            if (cfg.accountName) {
                const mainTitle = document.getElementById('report-main-title');
                if (mainTitle) {
                    mainTitle.innerText = 'دفتر الأستاذ العام';
                }
            }
        } catch(e){}
    }

    if (!currentData || currentData.length === 0) {
        if (typeof collectAllTransactions === 'function') {
            currentData = collectAllTransactions();
        }
    }

    filteredData = [...currentData];
    initPresets();

    const urlParams = new URLSearchParams(window.location.search);
    const presetParam = urlParams.get('presetIndex');
    if (presetParam !== null) {
        const pIdx = parseInt(presetParam, 10);
        if (!isNaN(pIdx) && pIdx >= 0) {
            loadPreset(pIdx);
        } else {
            loadPreset(0);
        }
    } else {
        loadPreset(0);
    }

    if (urlParams.get('hideRibbon') === '1') {
        const ribbon = document.querySelector('.ribbon');
        if (ribbon) ribbon.style.display = 'none';
        const panel = document.querySelector('.settings-panel');
        if (panel) panel.style.display = 'none';
        document.body.style.padding = '0';
        const printArea = document.querySelector('.print-area');
        if (printArea) {
            printArea.style.margin = '10px auto';
        }
    }
    initResetButton();
    initConfirmModal();
});

window.addEventListener('storage', function(e) {
    if (e.key === PRESETS_STORAGE_KEY) {
        renderPresetButtons();
        renderPresetsManagementList();
    }
});

function executePrintWithPermission() {
    let user = {};
    try { user = JSON.parse(localStorage.getItem('user') || '{}'); } catch(e){}
    
    let perms = {};
    if (typeof window.resolveUserPermissions === 'function') {
        perms = window.resolveUserPermissions(user);
    } else {
        perms = user.permissions || {};
    }

    const isAdmin = user.is_admin == 1 || user.role === 'admin' || user.role === 'super_admin' || user.is_super == 1 || user.username === 'admin' || user.role === 'مدير النظام';
    const canPrint = isAdmin || (perms.reports?.print !== false && perms.ledger_report?.print !== false && perms.print !== false);

    if (canPrint) {
        if (typeof window.rawNativePrint === 'function') {
            window.rawNativePrint();
        } else {
            window.print();
        }
    } else {
        alert('⚠️ ليس لديك صلاحية الطباعة المباشرة لهذه الشاشة. يرجى مراجعة إدارة المستخدمين وتخصيصات الصلاحيات.');
    }
}

function filterTableRows(query) {
    const q = (query || '').toLowerCase().trim();
    if (!q) {
        filteredData = [...currentData];
    } else {
        filteredData = currentData.filter(row => {
            const stmt = String(getRawFieldValue(row, 'statement')).toLowerCase();
            const ref = String(getRawFieldValue(row, 'ref')).toLowerCase();
            const counter = String(getRawFieldValue(row, 'counterAccount')).toLowerCase();
            const type = String(getRawFieldValue(row, 'typeLabel')).toLowerCase();
            const transferNo = String(getRawFieldValue(row, 'transferNo')).toLowerCase();
            const checkNo = String(getRawFieldValue(row, 'checkNo')).toLowerCase();
            const note = String(getRawFieldValue(row, 'additionalNote')).toLowerCase();
            return stmt.includes(q) || ref.includes(q) || counter.includes(q) || type.includes(q) || transferNo.includes(q) || checkNo.includes(q) || note.includes(q);
        });
    }
    renderTable();
}

function getDefaultConfigs() {
    let conf = {};
    baseColumns.forEach(c => {
        conf[c.idx] = {
            label: c.defaultLabel,
            visible: true,
            fs: 0.9,
            fontWeight: '',
            fontFamily: 'inherit',
            color: '',
            bgColor: '',
            align: 'center',
            thousandSep: ',',
            decimals: 2,
            showDecSep: true,
            rounding: 'nearest'
        };
    });
    return conf;
}

function initPresets() {
    let raw = localStorage.getItem(PRESETS_STORAGE_KEY);
    let list = [];
    let needsMigration = true;

    if (raw) {
        try { 
            list = JSON.parse(raw); 
            if (Array.isArray(list) && list.length > 0) {
                needsMigration = false;
            }
        } catch(e){}
    }
    
    if (needsMigration) {
        const allKeys = [
            'ledger_preview_presets_v4',
            'ledger_preview_presets_v3',
            'ledger_preview_presets_v2',
            'ledger_preview_presets_v1',
            'ledger_preview_presets'
        ];
        
        allKeys.forEach(k => {
            let oldRaw = localStorage.getItem(k);
            if (oldRaw) {
                try {
                    let items = JSON.parse(oldRaw);
                    if (Array.isArray(items)) {
                        items.forEach(item => {
                            if (item && item.name && !list.some(x => x.name.trim() === item.name.trim())) {
                                item.showInRibbon = item.showInRibbon !== false;
                                list.push(item);
                            }
                        });
                    }
                } catch(e){}
            }
        });
    }

    if (list.length === 0) {
        list = [
            {
                name: '💎 افتراضي 1',
                configs: getDefaultConfigs(),
                order: [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12],
                theme: 'theme-classic-navy',
                font: 'font-ultra',
                paperSize: 'A4',
                orientation: 'landscape',
                showLogo: true,
                showInfo: true,
                showInRibbon: true,
                isLocked: false,
                isFactoryDefault: true
            },
            {
                name: '⭐ افتراضي 2',
                configs: getDefaultConfigs(),
                order: [0, 1, 2, 3, 4, 13, 14, 6, 7, 8, 9, 10, 11, 5, 12],
                theme: 'theme-emerald-pro',
                font: 'font-newsons',
                paperSize: 'A4',
                orientation: 'landscape',
                showLogo: true,
                showInfo: true,
                showInRibbon: true,
                isLocked: false,
                isFactoryDefault: true
            },
            {
                name: '🎨 افتراضي 3',
                configs: getDefaultConfigs(),
                order: [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12],
                theme: 'theme-modern-slate',
                font: 'font-tajawal',
                paperSize: 'A4',
                orientation: 'landscape',
                showLogo: true,
                showInfo: true,
                showInRibbon: true,
                isLocked: false,
                isFactoryDefault: true
            },
            {
                name: '🔥 افتراضي 4',
                order: [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12],
                theme: 'theme-luxury-gold',
                font: 'font-cairo',
                paperSize: 'A4',
                orientation: 'landscape',
                showLogo: true,
                showInfo: true,
                showInRibbon: true,
                isLocked: false,
                isFactoryDefault: true
            },
            {
                name: '✨ نموذج 1',
                configs: getDefaultConfigs(),
                order: [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12],
                theme: '',
                font: 'font-ultra',
                paperSize: 'A4',
                orientation: 'landscape',
                showLogo: true,
                showInfo: true,
                showInRibbon: true,
                isLocked: false,
                isFactoryDefault: false
            },
            {
                name: '🚀 نموذج 2',
                configs: getDefaultConfigs(),
                order: [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12],
                theme: '',
                font: 'font-ultra',
                paperSize: 'A4',
                orientation: 'landscape',
                showLogo: true,
                showInfo: true,
                showInRibbon: true,
                isLocked: false,
                isFactoryDefault: false
            },
            {
                name: '🌟 نموذج 3',
                configs: getDefaultConfigs(),
                order: [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12],
                theme: '',
                font: 'font-ultra',
                paperSize: 'A4',
                orientation: 'landscape',
                showLogo: true,
                showInfo: true,
                showInRibbon: true,
                isLocked: false,
                isFactoryDefault: false
            }
        ];
    }

    savePresets(list);
    renderPresetButtons();

    // Async sync with database
    loadPresetsFromDB(PRESETS_STORAGE_KEY, PRESETS_STORAGE_KEY, function() {
        renderPresetButtons();
        if (typeof renderPresetsManagementList === 'function') renderPresetsManagementList();
        if (typeof currentPresetIndex !== 'undefined' && currentPresetIndex !== null) {
            loadPreset(currentPresetIndex);
        }
    });
}

function syncPresetsToDB(reportKey, presets) {
    if (!Array.isArray(presets)) return;
    try {
        const token = localStorage.getItem('erp_token') || '';
        const headers = { 'Content-Type': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        fetch('api_index.php/report-layout', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                reportKey: reportKey,
                layout: presets
            })
        }).catch(err => console.warn('[PresetDBSync] POST failed:', err));
    } catch(e) { console.warn('[PresetDBSync] Error:', e); }
}

function loadPresetsFromDB(reportKey, localKey, onComplete) {
    try {
        const token = localStorage.getItem('erp_token') || '';
        const headers = {};
        if (token) headers['Authorization'] = 'Bearer ' + token;
        fetch('api_index.php/report-layout?reportKey=' + reportKey, { headers })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && Array.isArray(data.layout) && data.layout.length > 0) {
                    localStorage.setItem(localKey, JSON.stringify(data.layout));
                    if (typeof onComplete === 'function') onComplete(data.layout);
                } else {
                    let rawLocal = localStorage.getItem(localKey);
                    if (rawLocal) {
                        try {
                            let localPresets = JSON.parse(rawLocal);
                            if (Array.isArray(localPresets) && localPresets.length > 0) {
                                syncPresetsToDB(reportKey, localPresets);
                            }
                        } catch(e){}
                    }
                }
            })
            .catch(err => console.warn('[PresetDBSync] GET failed:', err));
    } catch(e) { console.warn('[PresetDBSync] Error:', e); }
}
window.FACTORY_PRESETS = [];
async function loadFactoryPresets() {
    try {
        const res = await fetch('api/factory_config/ledger_factory_presets.json?t=' + Date.now());
        if (res.ok) {
            const data = await res.json();
            if (Array.isArray(data) && data.length > 0) {
                window.FACTORY_PRESETS = data;
                renderPresetButtons();
                if (typeof renderPresetsManagementList === 'function') renderPresetsManagementList();
            }
        }
    } catch (e) {
        // No factory presets found, ignore
    }
}
setTimeout(loadFactoryPresets, 50);

// Developer Secret Shortcut: Ctrl + Shift + S to lock ONLY the current preset as factory default
document.addEventListener('keydown', async function(e) {
    if (e.ctrlKey && e.shiftKey && (e.key === 'S' || e.key === 's' || e.key === 'س')) {
        e.preventDefault();
        
        const idx = typeof currentPresetIndex !== 'undefined' ? currentPresetIndex : 0;
        if (idx > 3) {
            alert('يمكنك قفل الأزرار الأربعة الأولى فقط كـ ضبط مصنع (افتراضي 1 إلى 4)!');
            return;
        }
        
        let presets = getSavedPresets();
        let activePreset = presets[idx];
        
        if (!activePreset) {
            alert('لا يوجد تصميم محفوظ في هذا الزر! يرجى تصميمه وحفظه أولاً ثم الضغط على الاختصار.');
            return;
        }
        
        if (activePreset.isFactoryDefault) {
            alert('هذا الزر مقفل مسبقاً كـ "ضبط مصنع" ولا يحتاج إلى قفل مرة أخرى!');
            return;
        }

        if (confirm(`⚠️ تحذير المطورين: هل أنت متأكد من رغبتك في قفل التصميم الحالي المفتوح في الزر رقم (${idx + 1}) كـ "ضبط مصنع نهائي"؟ لا يمكن التراجع عن هذا الإجراء من داخل النظام!`)) {
            let pToSave = JSON.parse(JSON.stringify(activePreset));
            pToSave.isFactoryDefault = true;
            pToSave.isLocked = true;
            try {
                const res = await fetch('api/save_factory_presets.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ preset: pToSave, index: idx })
                });
                const data = await res.json();
                if (data.success) {
                    alert('🎉 ' + data.message);
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            } catch(err) {
                alert('حدث خطأ في الاتصال بالخادم!');
            }
        }
    }
});

function applyFactoryLocks(list) {
    if (window.FACTORY_PRESETS && window.FACTORY_PRESETS.length > 0) {
        for(let i=0; i < window.FACTORY_PRESETS.length; i++) {
            list[i] = JSON.parse(JSON.stringify(window.FACTORY_PRESETS[i])); // deep clone
            list[i].isLocked = true;
            list[i].isFactoryDefault = true;
        }
    }
    return list;
}

function getSavedPresets() {
    let list = [];
    let raw = localStorage.getItem(PRESETS_STORAGE_KEY);
    if (raw) {
        try { list = JSON.parse(raw); } catch(e) {}
    }
    if (!Array.isArray(list)) list = [];
    
    // Sanitize local storage: remove any accidental locks that were saved locally
    list = list.map(p => {
        if (p) {
            delete p.isFactoryDefault;
            delete p.isLocked;
        }
        return p;
    });

    return applyFactoryLocks(list);
}

function savePresets(presets) {
    if (Array.isArray(presets)) {
        if (presets.length > MAX_PRESETS_LIMIT) {
            presets = presets.slice(0, MAX_PRESETS_LIMIT);
        }
        localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(presets));
        syncPresetsToDB(PRESETS_STORAGE_KEY, presets);
    }
}

function renderPresetButtons() {
    const container = document.getElementById('presets-container');
    if (!container) return;
    container.innerHTML = ''; // Prevent duplicates if general-ledger.js also rendered
    let presets = getSavedPresets();
    
    const defaultIcons = ['💎 افتراضي 1', '⭐ افتراضي 2', '🎨 افتراضي 3', '🔥 افتراضي 4', '🌺 افتراضي 5', '✨ نموذج 1', '✨ نموذج 2'];

    if (!Array.isArray(presets) || presets.length === 0) {
        presets = defaultIcons.map(name => ({ name, showInRibbon: true }));
    } else {
        // Fix legacy arrays where index 4 was '✨ نموذج 1'
        if (presets.length >= 5 && presets[4].name && presets[4].name.includes('نموذج 1')) {
            // Insert 'افتراضي 5' at index 4 and shift the rest
            presets.splice(4, 0, { name: defaultIcons[4], showInRibbon: true });
        }
        
        // Append any remaining missing presets up to 7
        if (presets.length < defaultIcons.length) {
            for (let i = presets.length; i < defaultIcons.length; i++) {
                presets.push({ name: defaultIcons[i], showInRibbon: true });
            }
        }
        savePresets(presets);
    }

    presets.slice(0, 7).forEach((p, idx) => {
        if (p.showInRibbon === false) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'preset-btn' + (idx === currentPresetIndex ? ' active' : '');
        
        let color = '#38bdf8'; // default blue
        if (idx === 1) color = '#eab308'; // yellow
        else if (idx === 2) color = '#a855f7'; // purple
        else if (idx === 3) color = '#ef4444'; // red
        else if (idx === 4) color = '#f43f5e'; // rose
        else if (idx === 5) color = '#10b981'; // green
        else if (idx === 6) color = '#10b981'; // green
        
        btn.style.cssText = `--btn-color: ${color};`;
        btn.innerHTML = p.name || ('افتراضي ' + (idx + 1));
        btn.onclick = () => {
            loadPreset(idx);
            setTimeout(renderPresetButtons, 50); // re-render to update active styling
        };
        container.appendChild(btn);
    });
}

function startLongPressCount(idx, btn) {
    cancelLongPressCount();
    isLongPressTriggered = false;
    currentPressingBtn = btn;

    const presets = getSavedPresets();
    const p = presets[idx];
    if (!p) return;

    const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));
    const isLockedDefault = isDefaultGroup && p.isLocked === true;

    const hud = document.getElementById('long-press-hud');
    const hudTitle = document.getElementById('hud-title');
    const hudDesc = document.getElementById('hud-desc');
    const hudProgress = document.getElementById('hud-progress-bar');
    const hudTimer = document.getElementById('hud-timer');
    const hudIcon = document.getElementById('hud-icon');

    if (hud) {
        hud.style.display = 'block';
        if (isDefaultGroup) {
            hudIcon.innerText = '🔒';
            hudTitle.innerText = `حفظ وتثبيت ضبط المصنع (${p.name})`;
            hudDesc.innerText = 'استمر بالضغط المتواصل (8 ثوانٍ) لحفظ وتثبيت التعديلات الجديدة كـ "ضبط مصنع نهائي".';
            if (hudProgress) {
                hudProgress.style.width = '0%';
                hudProgress.style.background = 'linear-gradient(90deg, #eab308, #10b981)';
            }
            if (hudTimer) hudTimer.innerText = '8.0 ثانية متبقية';
        } else {
            hudIcon.innerText = '✨';
            hudTitle.innerText = `حفظ وتحديث (${p.name})`;
            hudDesc.innerText = 'استمر بالضغط المتواصل (8 ثوانٍ) حتى يمتلئ الشريط، وسيطلب منك النظام تأكيد الحفظ والتحديث.';
            if (hudProgress) {
                hudProgress.style.width = '0%';
                hudProgress.style.background = 'linear-gradient(90deg, #3b82f6, #10b981)';
            }
            if (hudTimer) hudTimer.innerText = '8.0 ثانية متبقية';
        }
    }

    const duration = 8000;
    const startTime = Date.now();

    longPressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const remainingMs = Math.max(0, duration - elapsed);
        const remainingSec = (remainingMs / 1000).toFixed(1);
        const percent = Math.min(100, (elapsed / duration) * 100);

        if (hudProgress) hudProgress.style.width = percent + '%';
        if (hudTimer) hudTimer.innerText = remainingSec + ' ثانية متبقية';

        if (btn) {
            btn.style.boxShadow = `0 0 15px rgba(16,185,129,0.8)`;
            btn.style.borderColor = '#10b981';
            btn.style.background = `linear-gradient(90deg, rgba(16,185,129,0.45) ${percent}%, rgba(255,255,255,0.08) ${percent}%)`;
        }

        if (elapsed >= duration) {
            cancelLongPressCount();
            isLongPressTriggered = true;
            
            setTimeout(() => {
                promptConfirmationAndSave(idx);
            }, 50);
        }
    }, 50);
}

function cancelLongPressCount() {
    if (longPressInterval) {
        clearInterval(longPressInterval);
        clearTimeout(longPressInterval);
        longPressInterval = null;
    }
    const hud = document.getElementById('long-press-hud');
    if (hud) hud.style.display = 'none';

    if (currentPressingBtn) {
        currentPressingBtn.style.boxShadow = '';
        currentPressingBtn.style.borderColor = '';
        currentPressingBtn.style.background = '';
        currentPressingBtn = null;
    }
}

function promptConfirmationAndSave(idx) {
    const presets = getSavedPresets();
    const p = presets[idx];
    if (!p) return;

    const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));

    const modal = document.getElementById('save-confirm-modal');
    const title = document.getElementById('confirm-modal-title');
    const body = document.getElementById('confirm-modal-body');
    const icon = document.getElementById('confirm-modal-icon');
    const okBtn = document.getElementById('confirm-modal-ok-btn');
    const cancelBtn = document.getElementById('confirm-modal-cancel-btn');

    if (modal && title && body && okBtn && cancelBtn) {
        modal.style.display = 'flex';

        if (isDefaultGroup) {
            if (icon) icon.innerText = '🔒';
            title.innerText = `حفظ وتثبيت ضبط المصنع (${p.name})`;
            body.innerHTML = `اكتملت الـ 8 ثوانٍ بنجاح! <br><br>هل تريد حفظ وتثبيت التعديلات الجديدة في (<b>${p.name}</b>) كـ <b>"ضبط مصنع نهائي"</b>؟<br><br><span style="color:#eab308; font-size:0.85rem;">(ملاحظة: سيتم تثبيت وتقييد جميع الخطوط والألوان والأعمدة والتنسيقات الجديدة).</span>`;
        } else {
            if (icon) icon.innerText = '✨';
            title.innerText = `حفظ وتحديث التقرير (${p.name})`;
            body.innerHTML = `اكتملت الـ 8 ثوانٍ بنجاح! <br><br>هل تريد حفظ وتحديث تصميم التقرير الحالي في (<b>${p.name}</b>)؟<br><br><span style="color:#38bdf8; font-size:0.85rem;">(ملاحظة: يمكنك إعادة تعديله وتجديده لاحقاً في أي وقت بالضغط المطول 8 ثوانٍ مجدداً).</span>`;
        }

        okBtn.onclick = () => {
            modal.style.display = 'none';
            executeLongPressSave(idx);
        };

        cancelBtn.onclick = () => {
            modal.style.display = 'none';
        };
    } else {
        if (isDefaultGroup) {
            const confirmSave = confirm(`🔒 اكتملت الـ 8 ثوانٍ بنجاح!\n\nهل تريد حفظ وتثبيت التعديلات الجديدة في (${p.name}) كـ "ضبط مصنع نهائي"؟`);
            if (confirmSave) executeLongPressSave(idx);
        } else {
            const confirmSave = confirm(`✨ اكتملت الـ 8 ثوانٍ بنجاح!\n\nهل تريد حفظ وتحديث تصميم التقرير الحالي في (${p.name})؟`);
            if (confirmSave) executeLongPressSave(idx);
        }
    }
}

function executeLongPressSave(idx) {
    const presets = getSavedPresets();
    const p = presets[idx];
    if (!p) return;

    const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));

    if (isDefaultGroup) {
        // Save updated Factory Reset settings without hard-locking future edits
        p.configs = JSON.parse(JSON.stringify(columnConfigs));
        p.order = [...columnOrder];
        p.theme = currentTheme;
        p.font = currentFont;
        p.paperSize = currentPaperSize;
        p.orientation = currentOrientation;
        p.showLogo = showHeaderLogo;
        p.showInfo = showHeaderInfo;
        p.showUser = document.getElementById('hdr-show-user-chk')?.checked;
        p.userPos = document.getElementById('meta-user-pos-select')?.value;
        p.showDateTime = document.getElementById('hdr-show-datetime-chk')?.checked;
        p.showPageNums = document.getElementById('hdr-show-pagenums-chk')?.checked;
        p.isLocked = true;
        p.isFactoryDefault = true;
        p.updatedAt = new Date().toISOString();

        savePresets(presets);
        renderPresetButtons();
        renderPresetsManagementList();

        try {
            if (window.DB_STORE && window.getTenantKey) {
                const tk = window.getTenantKey(PRESETS_STORAGE_KEY);
                window.DB_STORE[tk] = JSON.stringify(presets);
            }
            if (typeof localforage !== 'undefined') {
                localforage.setItem(PRESETS_STORAGE_KEY, presets);
            }
        } catch(e){}

        alert(`⭐ تم حفظ وتثبيت التعديلات الجديدة في "${p.name}" كـ "ضبط المصنع" بنجاح!\n\n(ملاحظة: جميع التنسيقات الجديدة محفوظة الآن، وتظل مجهزة لإعادة التعديل والتحديث مجدداً في أي وقت تعود فيه إلي هذا الخيار).`);
    } else {
        // Model preset (نموذج 1, 2, 3) - Overwritable
        p.configs = JSON.parse(JSON.stringify(columnConfigs));
        p.order = [...columnOrder];
        p.theme = currentTheme;
        p.font = currentFont;
        p.paperSize = currentPaperSize;
        p.orientation = currentOrientation;
        p.showLogo = showHeaderLogo;
        p.showInfo = showHeaderInfo;
        p.showUser = document.getElementById('hdr-show-user-chk')?.checked;
        p.userPos = document.getElementById('meta-user-pos-select')?.value;
        p.showDateTime = document.getElementById('hdr-show-datetime-chk')?.checked;
        p.showPageNums = document.getElementById('hdr-show-pagenums-chk')?.checked;
        p.isLocked = false;
        p.updatedAt = new Date().toISOString();

        savePresets(presets);
        renderPresetButtons();
        renderPresetsManagementList();

        try {
            if (window.DB_STORE && window.getTenantKey) {
                const tk = window.getTenantKey(PRESETS_STORAGE_KEY);
                window.DB_STORE[tk] = JSON.stringify(presets);
            }
            if (typeof localforage !== 'undefined') {
                localforage.setItem(PRESETS_STORAGE_KEY, presets);
            }
        } catch(e){}

        alert(`✨ تم حفظ وتحديث تصميم التقرير في "${p.name}" بنجاح!\n\nتمت أرشفة ونقل التعديلات الحالية إلى هذا النموذج، ويمكنك إعادة تعديله وتحديثه مرة أخرى في أي وقت عن طريق الضغط المطول (8 ثوانٍ).`);
    }
}

function renderPresetsManagementList() {
    const list = document.getElementById('presets-management-list');
    if (!list) return;
    list.innerHTML = '';
    const presets = getSavedPresets();

    // Update the header counter
    const headerSpan = document.querySelector('#studio-tab-presets .panel-card span');
    if (headerSpan) {
        headerSpan.innerHTML = `💎 إدارة التخصيصات الجاهزة: <span style="background:#ca8a04; color:#fff; padding:2px 10px; border-radius:12px; margin-right:6px;">${presets.length} / ${MAX_PRESETS_LIMIT}</span>`;
    }

    if (presets.length === 0) {
        list.innerHTML = '<div style="text-align:center; padding:20px; opacity:0.7; font-size:1rem;">لا توجد تخصيصات محفوظة حالياً. اضغط زر إضافة تخصيص جديد!</div>';
        return;
    }

    presets.forEach((p, idx) => {
        const card = document.createElement('div');
        card.className = 'panel-card';
        card.style.marginBottom = '4px';
        
        const isCurrent = idx === currentPresetIndex;
        const isVisible = p.showInRibbon !== false;
        const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));
        
        let statusBadge = '';
        if (isDefaultGroup && p.isLocked) {
            statusBadge = '<span style="color:#eab308; font-weight:900; background:rgba(234,179,8,0.15); padding:3px 10px; border-radius:12px; font-size:0.85rem; border:1px solid rgba(234,179,8,0.4);">🔒 ضبط مصنع نهائي محمي</span>';
        } else if (isDefaultGroup) {
            statusBadge = '<span style="color:#64748b; font-weight:800; background:rgba(100,116,139,0.15); padding:3px 10px; border-radius:12px; font-size:0.85rem;">⏳ افتراضي (اضغط 8 ثوانٍ للقفل)</span>';
        } else {
            statusBadge = '<span style="color:#38bdf8; font-weight:900; background:rgba(56,189,248,0.15); padding:3px 10px; border-radius:12px; font-size:0.85rem; border:1px solid rgba(56,189,248,0.3);">🔄 نموذج قابل للتحديث</span>';
        }

        card.innerHTML = `
            <div class="panel-group" style="gap:10px;">
                <span style="font-weight:900; font-size:1rem; background:${isCurrent ? 'linear-gradient(135deg,#10b981,#059669)' : '#ca8a04'}; color:#fff; padding:3px 10px; border-radius:10px; min-width:28px; text-align:center;">${idx + 1}</span>
                <input type="text" class="panel-input" value="${p.name}" ${p.isLocked ? 'disabled style="opacity:0.8;"' : ''} onchange="renamePreset(${idx}, this.value)" style="width:180px; font-weight:800; font-size:0.95rem;">
                ${statusBadge}
                ${isCurrent ? '<span style="color:#10b981; font-weight:900; background:rgba(16,185,129,0.15); padding:3px 10px; border-radius:12px; font-size:0.88rem;">🟢 النشط حالياً</span>' : ''}
            </div>

            <div class="panel-group" style="gap:8px;">
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer; background:rgba(255,255,255,0.6); padding:5px 12px; border-radius:8px; border:1px solid #ca8a04; font-size:0.88rem;">
                    <input type="checkbox" ${isVisible ? 'checked' : ''} onchange="togglePresetRibbonVisibility(${idx}, this.checked)">
                    👁️ إظهار
                </label>

                <button class="ribbon-btn" style="padding:5px 14px; background:linear-gradient(135deg,#0284c7,#0369a1); border:none; font-size:0.85rem;" onclick="loadPreset(${idx})">
                    ▶️ تطبيق
                </button>

                <button class="ribbon-btn" style="padding:5px 14px; background:linear-gradient(135deg,#ef4444,#dc2626); border:none; font-size:0.85rem;" onclick="deletePreset(${idx})">
                    🗑️ حذف
                </button>
            </div>
        `;
        list.appendChild(card);
    });
}

function togglePresetRibbonVisibility(idx, isChecked) {
    const presets = getSavedPresets();
    if (presets[idx]) {
        presets[idx].showInRibbon = isChecked;
        savePresets(presets);
        renderPresetButtons();
        renderPresetsManagementList();
    }
}

function renamePreset(idx, newName) {
    const presets = getSavedPresets();
    if (!presets[idx]) return;
    if (presets[idx].isLocked) {
        alert(`🛑 الخيار "${presets[idx].name}" محمي ومقفل كـ "ضبط مصنع نهائي"، ولا يمكن تغيير اسمه نهائياً 🔒.`);
        renderPresetsManagementList();
        return;
    }
    if (newName.trim()) {
        presets[idx].name = newName.trim();
        savePresets(presets);
        renderPresetButtons();
        renderPresetsManagementList();
    }
}

function deletePreset(idx) {
    const presets = getSavedPresets();
    if (!presets[idx]) return;
    if (presets[idx].isLocked) {
        alert(`🛑 الخيار "${presets[idx].name}" محمي ومقفل كـ "ضبط مصنع نهائي"، ولا يمكن حذفه نهائياً 🔒.`);
        return;
    }
    
    if (confirm(`⚠️ هل أنت تأكد من رغبتك في حذف التخصيص "${presets[idx].name}"؟`)) {
        presets.splice(idx, 1);
        savePresets(presets);
        
        if (currentPresetIndex >= presets.length) {
            currentPresetIndex = Math.max(0, presets.length - 1);
        }
        
        renderPresetButtons();
        renderPresetsManagementList();
        if (presets.length > 0) {
            loadPreset(currentPresetIndex);
        }
        alert('تم حذف التخصيص بنجاح!');
    }
}

function addNewPresetPrompt() {
    const presets = getSavedPresets();
    if (presets.length >= MAX_PRESETS_LIMIT) {
        alert(`⚠️ لقد وصلت إلى الحد الأقصى للتخصيصات (7 تخصيصات كحد أقصى).\n\nيمكنك الحذف أو التعديل على التخصيصات الحالية من تبويب "💎 التخصيصات الجاهزة".`);
        return;
    }

    const name = prompt("أدخل اسماً للتخصيص الجديد (الحد الأقصى 7 تخصيصات):", "🎨 تصميم " + (presets.length + 1));
    if (name && name.trim()) {
        presets.push({
            name: name.trim(),
            configs: JSON.parse(JSON.stringify(columnConfigs)),
            order: [...columnOrder],
            theme: currentTheme,
            font: currentFont,
            paperSize: currentPaperSize,
            orientation: currentOrientation,
            showLogo: showHeaderLogo,
            showInfo: showHeaderInfo,
            showUser: document.getElementById('hdr-show-user-chk')?.checked,
            userPos: document.getElementById('meta-user-pos-select')?.value,
            showDateTime: document.getElementById('hdr-show-datetime-chk')?.checked,
            showPageNums: document.getElementById('hdr-show-pagenums-chk')?.checked,
            showInRibbon: true
        });
        savePresets(presets);
        currentPresetIndex = presets.length - 1;
        renderPresetButtons();
        renderPresetsManagementList();
        loadPreset(currentPresetIndex);
        alert('✨ تمت إضافة التخصيص الجديد وظهوره في الشريط العلوي بنجاح!');
    }
}

function loadPreset(idx) {
    currentPresetIndex = idx;
    const presets = getSavedPresets();
    const preset = presets[idx];
    
    const saveBtn = document.querySelector('button[onclick="saveCurrentPreset()"]');
    if (saveBtn) {
        if (idx < 5 || (preset && preset.isFactoryDefault)) {
            saveBtn.style.display = 'none';
        } else {
            saveBtn.style.display = 'inline-flex';
            saveBtn.style.opacity = '1';
            saveBtn.style.pointerEvents = 'auto';
            saveBtn.title = "";
        }
    }

    if (preset) {
        columnConfigs = preset.configs || getDefaultConfigs();
        columnOrder = preset.order || [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12];
        
        // Migration for old configs
        if (!columnOrder.includes(12)) {
            if(!columnOrder.includes(11)) columnOrder.push(11);
            if(!columnOrder.includes(12)) columnOrder.push(12);
        }
        if (!columnOrder.includes(13)) {
            const pos4 = columnOrder.indexOf(4);
            if (pos4 !== -1) {
                columnOrder.splice(pos4 + 1, 0, 13, 14);
            } else {
                columnOrder.push(13, 14);
            }
        } else if (!columnOrder.includes(14)) {
            const pos13 = columnOrder.indexOf(13);
            columnOrder.splice(pos13 + 1, 0, 14);
        }
        const defaults = getDefaultConfigs();
        [9, 10, 11, 12, 13, 14].forEach(i => {
            if (columnConfigs[i]) {
                if (!columnConfigs[i].label) columnConfigs[i].label = defaults[i].label;
            } else {
                columnConfigs[i] = defaults[i];
            }
        });
        currentTheme = preset.theme || '';
        currentFont = preset.font || 'font-newsons';
        currentPaperSize = preset.paperSize || 'A4';
        currentOrientation = preset.orientation || 'landscape';
        showHeaderLogo = preset.showLogo !== undefined ? preset.showLogo : true;
        showHeaderInfo = preset.showInfo !== undefined ? preset.showInfo : true;
    } else {
        columnConfigs = getDefaultConfigs();
        columnOrder = [0, 1, 2, 3, 4, 13, 14, 5, 6, 7, 8, 9, 10, 11, 12];
        currentTheme = '';
        currentFont = 'font-newsons';
        currentPaperSize = 'A4';
        currentOrientation = 'landscape';
        showHeaderLogo = true;
        showHeaderInfo = true;
    }
    
    setTableTheme(currentTheme, false);
    setNumberFont(currentFont, false);
    setPaperSize(currentPaperSize, true);
    setPageOrientation(currentOrientation, true);
    
    document.getElementById('hdr-show-logo-chk').checked = showHeaderLogo;
    document.getElementById('hdr-show-info-chk').checked = showHeaderInfo;
    toggleHeaderElement('logo', showHeaderLogo);
    toggleHeaderElement('info', showHeaderInfo);
    
    // Load advanced print options if available
    const showUserChk = document.getElementById('hdr-show-user-chk');
    if (showUserChk) {
        showUserChk.checked = preset ? (preset.showUser !== undefined ? preset.showUser : true) : true;
    }
    const userPosSel = document.getElementById('meta-user-pos-select');
    if (userPosSel) {
        userPosSel.value = preset ? (preset.userPos || 'bottom-right') : 'bottom-right';
    }
    const showDateTimeChk = document.getElementById('hdr-show-datetime-chk');
    if (showDateTimeChk) {
        showDateTimeChk.checked = preset ? (preset.showDateTime !== undefined ? preset.showDateTime : true) : true;
    }
    const showPageNumsChk = document.getElementById('hdr-show-pagenums-chk');
    if (showPageNumsChk) {
        showPageNumsChk.checked = preset ? (preset.showPageNums !== undefined ? preset.showPageNums : true) : true;
    }
    if (typeof window.updatePrintMeta === 'function') window.updatePrintMeta();

    renderPresetButtons();
    renderTable();
    updateHiddenColumnsDropdown();
    
    if (selectedColIndex !== null) {
        updateSettingsPanelForCol(selectedColIndex);
    }
}

function saveCurrentPreset() {
    const presets = getSavedPresets();
    const p = presets[currentPresetIndex];
    if (!p) return;

    if (p.isFactoryDefault) {
        alert('🛑 هذا التصميم محمي كـ "ضبط مصنع" ولا يمكن حفظ التعديلات عليه!');
        return;
    }

    // Direct save - no long press needed, just save immediately
    p.configs = JSON.parse(JSON.stringify(columnConfigs));
    p.order = [...columnOrder];
    p.theme = currentTheme;
    p.font = currentFont;
    p.paperSize = currentPaperSize;
    p.orientation = currentOrientation;
    p.showLogo = showHeaderLogo;
    p.showInfo = showHeaderInfo;
    p.updatedAt = new Date().toISOString();

    // Automatically lock default presets upon first save
    const isDefaultGroup = currentPresetIndex < 4 || (p.name && p.name.includes('افتراضي'));
    if (isDefaultGroup) {
        p.isFactoryDefault = true;
        p.isLocked = true;
        try {
            fetch('api/save_factory_presets.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ preset: p, index: currentPresetIndex })
            });
        } catch(err) {}
    }

    savePresets(presets);
    renderPresetButtons();
    if (typeof renderPresetsManagementList === 'function') renderPresetsManagementList();
    
    // Refresh loadPreset to immediately hide the save button
    loadPreset(currentPresetIndex);

    try {
        if (window.DB_STORE && window.getTenantKey) {
            const tk = window.getTenantKey(PRESETS_STORAGE_KEY);
            window.DB_STORE[tk] = JSON.stringify(presets);
        }
        if (typeof localforage !== 'undefined') {
            localforage.setItem(PRESETS_STORAGE_KEY, presets);
        }
    } catch(e){}

    if (isDefaultGroup) {
        showToast(`🔒 تم حفظ "${p.name}" كـ ضبط مصنع نهائي بنجاح ولن يمكن تعديله بعد الآن!`, 'success');
    } else {
        showToast(`💾 تم حفظ تصميم الجدول في "${p.name}" بنجاح!`, 'success');
    }
}

function updateReportTableTheme(theme) {
    const tbl = document.getElementById('preview-table');
    if (!tbl) return;
    
    // Remove old themes
    tbl.classList.remove(
        'theme-classic-navy', 'theme-emerald-pro', 'theme-modern-slate',
        'theme-luxury-gold', 'theme-luxury-black'
    );
    
    // Add new theme
    if (theme !== 'default' && theme) {
        tbl.classList.add(theme);
    }
}

/* ── Custom Context Menu for Preview Ledger ── */
let previewCtxType = '';
let previewCtxRef = '';

document.addEventListener('contextmenu', function(e) {
    const table = e.target.closest('#preview-table');
    if (!table) return;

    const td = e.target.closest('td');
    if (!td) return;
    
    const tr = td.closest('tr');
    if (!tr) return;

    previewCtxType = tr.dataset.type || '';
    previewCtxRef = tr.dataset.ref || '';

    if (!previewCtxRef || previewCtxRef === '-' || previewCtxRef === 'PRE-PERIOD') {
        return;
    }

    e.preventDefault();
    const menu = document.getElementById('preview-context-menu');
    if (!menu) return;

    menu.style.display = 'block';
    
    let x = e.clientX;
    let y = e.clientY;

    if (x + menu.offsetWidth > window.innerWidth) x -= menu.offsetWidth;
    if (y + menu.offsetHeight > window.innerHeight) y -= menu.offsetHeight;

    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
});

document.addEventListener('click', function(e) {
    const menu = document.getElementById('preview-context-menu');
    if (menu && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const drillBtn = document.getElementById('preview-ctx-drilldown');
    if (drillBtn) {
        drillBtn.onclick = function() {
            document.getElementById('preview-context-menu').style.display = 'none';
            if (typeof window.navigateToOriginalVoucher === 'function') {
                window.navigateToOriginalVoucher(previewCtxType, previewCtxRef);
            }
        };
        drillBtn.onmouseover = function() { this.style.background = 'linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(56, 189, 248, 0.3))'; this.style.color = '#38bdf8'; this.style.paddingRight = '22px'; };
        drillBtn.onmouseout = function() { this.style.background = 'transparent'; this.style.color = '#fff'; this.style.paddingRight = '18px'; };
    }
});

function toggleSettingsPanel() {
    const p = document.getElementById('settings-panel');
    const isOpening = (p.style.display === 'none' || p.style.display === '');
    p.style.display = isOpening ? 'flex' : 'none';

    if (isOpening && selectedColIndex === null) {
        selectColumn(6); // Default select "مدين محلي"
    }
}

function switchStudioTab(tab) {
    const btnCols = document.getElementById('tab-btn-cols');
    const btnPaper = document.getElementById('tab-btn-paper');
    const btnPresets = document.getElementById('tab-btn-presets');
    
    const contentCols = document.getElementById('studio-tab-cols');
    const contentPaper = document.getElementById('studio-tab-paper');
    const contentPresets = document.getElementById('studio-tab-presets');

    btnCols.classList.remove('active');
    btnPaper.classList.remove('active');
    if (btnPresets) btnPresets.classList.remove('active');

    contentCols.style.display = 'none';
    contentPaper.style.display = 'none';
    if (contentPresets) contentPresets.style.display = 'none';

    if (tab === 'cols') {
        btnCols.classList.add('active');
        contentCols.style.display = 'block';
    } else if (tab === 'paper') {
        btnPaper.classList.add('active');
        contentPaper.style.display = 'block';
    } else if (tab === 'presets') {
        if (btnPresets) btnPresets.classList.add('active');
        if (contentPresets) contentPresets.style.display = 'block';
        renderPresetsManagementList();
    }
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
}

function setTableTheme(themeName) {
    currentTheme = themeName;
    const tbl = document.getElementById('preview-table');
    const sel = document.getElementById('table-theme-select');
    if (sel) sel.value = themeName || '';
    if (tbl) {
        tbl.classList.remove('theme-gold', 'theme-lightblue', 'theme-slate', 'theme-classic-navy', 'theme-emerald-pro', 'theme-modern-slate', 'theme-luxury-gold', 'theme-luxury-black');
        if (themeName) tbl.classList.add(themeName);
    }
}

function setNumberFont(fontClass) {
    currentFont = fontClass;
    const tbl = document.getElementById('preview-table');
    const select = document.getElementById('num-font-select');
    if (select) select.value = fontClass;
    if (tbl) {
        tbl.classList.remove('font-newsons', 'font-ribot', 'font-firacode', 'font-ultra', 'font-bolt', 'font-jakarta', 'font-roboto', 'font-outfit', 'font-tajawal', 'font-cairo');
        if (fontClass) tbl.classList.add(fontClass);
    }
}

function setPaperSize(size, updateSelect = true) {
    currentPaperSize = size;
    if (updateSelect) {
        const sel = document.getElementById('paper-size-select');
        if (sel) sel.value = size;
    }
    updatePageCss();
}

function setPageOrientation(orient, updateSelect = true) {
    currentOrientation = orient;
    if (updateSelect) {
        const sel = document.getElementById('paper-orient-select');
        if (sel) sel.value = orient;
    }
    
    const wrapper = document.getElementById('print-area-wrapper');
    if (wrapper) {
        wrapper.classList.remove('paper-portrait', 'paper-landscape');
        wrapper.classList.add('paper-' + orient);
    }
    updatePageCss();
}

function updatePageCss() {
    const styleEl = document.getElementById('dynamic-paper-style');
    if (styleEl) {
        styleEl.innerHTML = `@page { size: ${currentPaperSize} ${currentOrientation}; margin: 10mm; }`;
    }
}

function toggleHeaderElement(type, show) {
    if (type === 'logo') {
        showHeaderLogo = show;
        const logoBlock = document.getElementById('company-logo-block');
        if (logoBlock) logoBlock.style.display = show ? 'flex' : 'none';
    } else if (type === 'info') {
        showHeaderInfo = show;
        const infoBlock = document.getElementById('company-info-block');
        if (infoBlock) infoBlock.style.display = show ? 'block' : 'none';
    }
}

function updateHiddenColumnsDropdown() {
    const select = document.getElementById('restore-hidden-col-select');
    if (!select) return;

    let hiddenCols = [];
    columnOrder.forEach(idx => {
        const c = baseColumns.find(x => x.idx === idx);
        const conf = columnConfigs[idx] || getDefaultConfigs()[idx];
        if (c && conf.visible === false) {
            hiddenCols.push({ idx: c.idx, label: conf.label || c.defaultLabel });
        }
    });

    select.innerHTML = `<option value="">👁️ الأعمدة المخفية (${hiddenCols.length})</option>`;
    hiddenCols.forEach(hc => {
        const opt = document.createElement('option');
        opt.value = hc.idx;
        opt.innerText = '➕ إرجاع: ' + hc.label;
        select.appendChild(opt);
    });
}

function restoreHiddenColumn(idxStr) {
    if (idxStr === '' || idxStr === null) return;
    const idx = parseInt(idxStr);
    
    if (!columnConfigs[idx]) {
        columnConfigs[idx] = getDefaultConfigs()[idx];
    }
    columnConfigs[idx].visible = true;
    
    selectColumn(idx);
    renderTable();
    updateHiddenColumnsDropdown();
}

function moveSelectedCol(direction) {
    if (selectedColIndex === null) return;
    const pos = columnOrder.indexOf(selectedColIndex);
    if (pos === -1) return;
    
    // Find all visible columns in their current order
    const visibleIndices = columnOrder.filter(idx => {
        const conf = columnConfigs[idx];
        return !conf || conf.visible !== false;
    });
    
    const visiblePos = visibleIndices.indexOf(selectedColIndex);
    if (visiblePos === -1) return; // Selected column is hidden, can't move
    
    const newVisiblePos = visiblePos + direction;
    if (newVisiblePos < 0 || newVisiblePos >= visibleIndices.length) return; // Out of bounds
    
    // The target column index we want to swap with in the entire columnOrder array
    const targetColIndex = visibleIndices[newVisiblePos];
    const targetPos = columnOrder.indexOf(targetColIndex);
    
    if (targetPos === -1) return;
    
    // Swap their positions in the full columnOrder array
    const temp = columnOrder[pos];
    columnOrder[pos] = columnOrder[targetPos];
    columnOrder[targetPos] = temp;
    
    renderTable();
}

function renderTable() {
    updateSubheaderLabels();
    const thead = document.getElementById('preview-thead');
    const tbody = document.getElementById('preview-tbody');
    const tfoot = document.getElementById('preview-tfoot');
    
    // Build Header
    let trHead = document.createElement('tr');
    columnOrder.forEach(idx => {
        const c = baseColumns.find(x => x.idx === idx);
        if (!c) return;
        
        const conf = columnConfigs[idx] || { visible: true, fs: 0.9, label: c.defaultLabel, align: 'center' };
        if (!conf.visible) return;
        
        let th = document.createElement('th');
        th.innerText = conf.label || c.defaultLabel;
        if (selectedColIndex === idx) {
            th.classList.add('selected-col');
        }
        
        th.style.fontSize = (conf.fs || 0.9) + 'rem';
        th.style.textAlign = conf.align || 'center';
        if (conf.fontWeight) th.style.fontWeight = conf.fontWeight;
        if (conf.fontFamily && conf.fontFamily !== 'inherit') th.style.fontFamily = conf.fontFamily;
        if (conf.color) th.style.color = conf.color;
        if (conf.bgColor) th.style.backgroundColor = conf.bgColor;
        
        th.onclick = () => selectColumn(idx);
        trHead.appendChild(th);
    });
    thead.innerHTML = '';
    thead.appendChild(trHead);
    
    // Track Column Totals
    let colTotals = {};
    columnOrder.forEach(idx => { colTotals[idx] = 0; });

    // Build Body
    tbody.innerHTML = '';
    filteredData.forEach(row => {
        let tr = document.createElement('tr');
        if (row.ref) tr.dataset.ref = row.ref;
        if (row.type) tr.dataset.type = row.type;
        columnOrder.forEach(idx => {
            const c = baseColumns.find(x => x.idx === idx);
            if (!c) return;
            
            const conf = columnConfigs[idx] || { visible: true, fs: 0.9, align: 'center' };
            if (!conf.visible) return;
            
            let td = document.createElement('td');
            if (c.isNum) td.classList.add('num-cell');
            
            const rawVal = getRawFieldValue(row, c.field);
            if (c.isNum && typeof rawVal === 'number') {
                colTotals[idx] += rawVal;
            } else if (c.isNum && !isNaN(parseFloat(rawVal))) {
                colTotals[idx] += parseFloat(rawVal);
            }

            td.innerText = c.isNum ? formatCustomNumber(rawVal, conf) : (rawVal || '-');
            
            td.style.fontSize = (conf.fs || 0.9) + 'rem';
            td.style.textAlign = conf.align || 'center';
            if (conf.fontWeight) td.style.fontWeight = conf.fontWeight;
            if (conf.fontFamily && conf.fontFamily !== 'inherit') td.style.fontFamily = conf.fontFamily;
            if (conf.color) td.style.color = conf.color;
            if (conf.bgColor) td.style.backgroundColor = conf.bgColor;
            
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });

    // Build Summary Footer Row (tfoot)
    if (tfoot) {
        tfoot.innerHTML = '';
        let trFoot = document.createElement('tr');
        let firstVisible = true;

        columnOrder.forEach(idx => {
            const c = baseColumns.find(x => x.idx === idx);
            if (!c) return;
            
            const conf = columnConfigs[idx] || { visible: true, fs: 0.9, align: 'center' };
            if (!conf.visible) return;
            
            let td = document.createElement('td');
            td.style.fontSize = (conf.fs || 0.9) + 'rem';
            td.style.textAlign = conf.align || 'center';
            if (conf.fontWeight) td.style.fontWeight = conf.fontWeight;
            if (conf.fontFamily && conf.fontFamily !== 'inherit') td.style.fontFamily = conf.fontFamily;

            if (firstVisible) {
                td.innerText = 'الإجمالي الكلي (' + filteredData.length + ')';
                td.style.fontWeight = '900';
                firstVisible = false;
            } else if (c.isNum) {
                td.classList.add('num-cell');
                let footerVal = colTotals[idx];
                if (c.field === 'exchangeRate') {
                    footerVal = filteredData.length > 0 ? (footerVal / filteredData.length) : 0;
                } else if (c.field === 'balance') {
                    footerVal = filteredData.length > 0 ? parseFloat(getRawFieldValue(filteredData[filteredData.length - 1], 'balance') || 0) : 0;
                } else if (c.field === 'balanceForeign') {
                    footerVal = filteredData.length > 0 ? parseFloat(getRawFieldValue(filteredData[filteredData.length - 1], 'balanceForeign') || 0) : 0;
                }
                td.innerText = formatCustomNumber(footerVal, conf);
                td.style.fontWeight = '900';
            } else {
                td.innerText = '-';
            }
            trFoot.appendChild(td);
        });
        tfoot.appendChild(trFoot);
    }

    updateHiddenColumnsDropdown();
}

function formatCustomNumber(val, conf) {
    if (val === null || val === undefined || val === '') return '-';
    let num = parseFloat(val);
    if (isNaN(num)) return '-';

    const dec = conf.decimals !== undefined ? conf.decimals : 2;
    const rounding = conf.rounding || 'nearest';
    const factor = Math.pow(10, dec);

    if (rounding === 'up') {
        num = Math.ceil(num * factor) / factor;
    } else if (rounding === 'down') {
        num = Math.floor(num * factor) / factor;
    } else if (rounding === 'cut') {
        num = Math.trunc(num * factor) / factor;
    } else {
        num = Math.round(num * factor) / factor;
    }

    let parts = num.toFixed(dec).split('.');
    let intPart = parts[0];
    let decPart = parts[1] || '';

    const sep = conf.thousandSep !== undefined ? conf.thousandSep : ',';
    if (sep && sep !== 'none') {
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, sep);
    }

    if (dec > 0 && conf.showDecSep !== false) {
        return intPart + '.' + decPart;
    } else {
        return intPart;
    }
}

function populateSelectedColDropdown() {
    const sel = document.getElementById('selected-col-select');
    if (!sel) return;
    const currentVal = selectedColIndex;
    sel.innerHTML = '';
    
    baseColumns.forEach(c => {
        const conf = columnConfigs[c.idx] || getDefaultConfigs()[c.idx];
        const opt = document.createElement('option');
        opt.value = c.idx;
        opt.innerText = (conf.visible === false ? '👁️ (مخفي) ' : '') + (conf.label || c.defaultLabel);
        if (c.idx === currentVal) opt.selected = true;
        sel.appendChild(opt);
    });
    if (currentVal !== null) sel.value = currentVal;
}

function selectColumn(idx) {
    selectedColIndex = idx;
    const p = document.getElementById('settings-panel');
    if (p.style.display === 'none' || p.style.display === '') {
        p.style.display = 'flex';
    }
    
    switchStudioTab('cols');
    updateSettingsPanelForCol(idx);
    renderTable();
}

function updateSettingsPanelForCol(idx) {
    const c = baseColumns.find(x => x.idx === idx);
    if (!c) return;
    
    const conf = columnConfigs[idx] || getDefaultConfigs()[idx];
    
    populateSelectedColDropdown();
    const sel = document.getElementById('selected-col-select');
    if (sel) sel.value = idx;
    
    const labelInp = document.getElementById('col-label-input');
    labelInp.disabled = false;
    labelInp.value = conf.label || c.defaultLabel;

    const chk = document.getElementById('col-visible-chk');
    chk.disabled = false;
    chk.checked = conf.visible !== false;

    const alignSel = document.getElementById('col-align-select');
    alignSel.disabled = false;
    alignSel.value = conf.align || 'center';
    
    const rng = document.getElementById('col-fs-range');
    rng.disabled = false;
    rng.value = conf.fs || 0.9;
    document.getElementById('col-fs-val').innerText = (conf.fs || 0.9) + 'rem';
    
    const fwSel = document.getElementById('col-fw-select');
    if (fwSel) {
        fwSel.disabled = false;
        fwSel.value = conf.fontWeight || '700';
    }

    const fontSel = document.getElementById('col-font-select');
    if (fontSel) {
        fontSel.disabled = false;
        fontSel.value = conf.fontFamily || 'inherit';
    }
    
    const cp = document.getElementById('col-color-picker');
    cp.disabled = false;
    cp.value = conf.color || '#000000';

    const bgp = document.getElementById('col-bg-picker');
    bgp.disabled = false;
    bgp.value = conf.bgColor || '#ffffff';

    const sepSel = document.getElementById('col-sep-select');
    sepSel.disabled = false;
    sepSel.value = conf.thousandSep || ',';

    const decSel = document.getElementById('col-dec-select');
    decSel.disabled = false;
    decSel.value = conf.decimals !== undefined ? conf.decimals : 2;

    const decSepChk = document.getElementById('col-dec-sep-chk');
    decSepChk.disabled = false;
    decSepChk.checked = conf.showDecSep !== false;

    const roundSel = document.getElementById('col-rounding-select');
    roundSel.disabled = false;
    roundSel.value = conf.rounding || 'nearest';
}

function updateSelectedCol(type, val) {
    if (selectedColIndex === null) {
        // Apply globally across all columns
        baseColumns.forEach(c => {
            if (!columnConfigs[c.idx]) columnConfigs[c.idx] = getDefaultConfigs()[c.idx];
            columnConfigs[c.idx][type] = val;
        });
    } else {
        if (!columnConfigs[selectedColIndex]) {
            const c = baseColumns.find(x => x.idx === selectedColIndex);
            columnConfigs[selectedColIndex] = {
                label: c ? c.defaultLabel : '',
                visible: true,
                fs: 0.9,
                color: '',
                bgColor: '',
                align: 'center',
                thousandSep: ',',
                decimals: 2,
                showDecSep: true,
                rounding: 'nearest'
            };
        }
        columnConfigs[selectedColIndex][type] = val;
    }
    
    if (type === 'fs') {
        document.getElementById('col-fs-val').innerText = val + 'rem';
    } else if (type === 'label' || type === 'visible') {
        populateSelectedColDropdown();
    }
    
    renderTable();
}

// ==========================================
// Smart Custom Reset & Toast Notifications System
// ==========================================

function showToast(message, type = 'success') {
    let container = document.getElementById('studio-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'studio-toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `studio-toast ${type}`;
    
    let icon = '✨';
    if (type === 'error') icon = '⚠️';
    if (type === 'info') icon = 'ℹ️';
    
    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 400);
    }, 3500);
}

function applyDesignState(state) {
    columnConfigs = state.configs || getDefaultConfigs();
    columnOrder = state.order || [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    // Migration for old configs that didn't have exchange rate (column 11 missing)
    if (!columnOrder.includes(12)) {
        if(!columnOrder.includes(11)) columnOrder.push(11);
        if(!columnOrder.includes(12)) columnOrder.push(12);
        const defaults = getDefaultConfigs();
        [9, 10, 11, 12].forEach(i => {
            if (columnConfigs[i]) columnConfigs[i].label = defaults[i].label;
            else columnConfigs[i] = defaults[i];
        });
    }
    currentTheme = state.theme || '';
    currentFont = state.font || 'font-newsons';
    currentPaperSize = state.paperSize || 'A4';
    currentOrientation = state.orientation || 'landscape';
    showHeaderLogo = state.showLogo !== undefined ? state.showLogo : true;
    showHeaderInfo = state.showInfo !== undefined ? state.showInfo : true;

    setTableTheme(currentTheme);
    setNumberFont(currentFont);
    setPaperSize(currentPaperSize, true);
    setPageOrientation(currentOrientation, true);
    
    const logoChk = document.getElementById('hdr-show-logo-chk');
    const infoChk = document.getElementById('hdr-show-info-chk');
    if (logoChk) logoChk.checked = showHeaderLogo;
    if (infoChk) infoChk.checked = showHeaderInfo;
    toggleHeaderElement('logo', showHeaderLogo);
    toggleHeaderElement('info', showHeaderInfo);

    renderTable();
    updateHiddenColumnsDropdown();
    populateSelectedColDropdown();
    
    if (selectedColIndex !== null) {
        updateSettingsPanelForCol(selectedColIndex);
    }
}

function initResetButton() {
    const btn = document.getElementById('reset-design-btn');
    if (!btn) return;

    const progress = btn.querySelector('.btn-progress');
    let timer = null;
    let isHolding = false;
    let holdCompleted = false;

    const startHold = (e) => {
        if (e.type === 'mousedown' && e.button !== 0) return;
        
        isHolding = true;
        holdCompleted = false;
        
        if (progress) {
            progress.style.transition = 'width 8s linear';
            progress.style.width = '100%';
        }

        timer = setTimeout(() => {
            holdCompleted = true;
            cancelHold();
            // Long press 8 seconds = RESTORE factory reset
            restoreFactoryReset();
        }, 8000);
    };

    const cancelHold = () => {
        if (!isHolding) return;
        isHolding = false;
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
        if (progress) {
            progress.style.transition = 'none';
            progress.style.width = '0%';
        }
    };

    // Desktop mouse events
    btn.addEventListener('mousedown', startHold);
    btn.addEventListener('mouseup', (e) => {
        if (e.button !== 0) return;
        if (isHolding) {
            cancelHold();
            if (!holdCompleted) {
                // Short click = open modal to SAVE factory reset
                saveFactoryResetViaModal();
            }
        }
    });
    btn.addEventListener('mouseleave', cancelHold);

    // Mobile touch events
    btn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        startHold(e);
    });
    btn.addEventListener('touchend', (e) => {
        e.preventDefault();
        if (isHolding) {
            cancelHold();
            if (!holdCompleted) {
                saveFactoryResetViaModal();
            }
        }
    });
    btn.addEventListener('touchmove', cancelHold);
    btn.addEventListener('touchcancel', cancelHold);
}

function saveCustomResetState() {
    const state = {
        configs: JSON.parse(JSON.stringify(columnConfigs)),
        order: [...columnOrder],
        theme: currentTheme,
        font: currentFont,
        paperSize: currentPaperSize,
        orientation: currentOrientation,
        showLogo: showHeaderLogo,
        showInfo: showHeaderInfo
    };
    localStorage.setItem('balances_custom_reset_v1', JSON.stringify(state));
}

let confirmCallback = null;

function showConfirmModal(onConfirm) {
    confirmCallback = onConfirm;
    const modal = document.getElementById('reset-confirm-modal');
    if (modal) {
        modal.classList.add('show');
    }
    
    const progress = document.querySelector('.modal-ok-progress');
    if (progress) {
        progress.style.transition = 'none';
        progress.style.width = '0%';
    }
}

function hideConfirmModal() {
    const modal = document.getElementById('reset-confirm-modal');
    if (modal) {
        modal.classList.remove('show');
    }
    confirmCallback = null;
}

function initConfirmModal() {
    const modal = document.getElementById('reset-confirm-modal');
    const cancelBtn = document.getElementById('modal-cancel-btn');
    const okBtn = document.getElementById('modal-ok-btn');
    if (!modal || !cancelBtn || !okBtn) return;

    const progress = modal.querySelector('.modal-ok-progress');
    let timer = null;
    let isHolding = false;
    let holdCompleted = false;

    const startOkHold = (e) => {
        if (e.type === 'mousedown' && e.button !== 0) return;
        
        isHolding = true;
        holdCompleted = false;

        if (progress) {
            progress.style.transition = 'width 3s linear';
            progress.style.width = '100%';
        }

        timer = setTimeout(() => {
            holdCompleted = true;
            cancelOkHold();
            hideConfirmModal();
            if (confirmCallback) {
                confirmCallback();
            }
        }, 3000);
    };

    const cancelOkHold = () => {
        if (!isHolding) return;
        isHolding = false;
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
        if (progress) {
            progress.style.transition = 'none';
            progress.style.width = '0%';
        }
    };

    cancelBtn.addEventListener('click', hideConfirmModal);
    
    okBtn.addEventListener('mousedown', startOkHold);
    okBtn.addEventListener('mouseup', cancelOkHold);
    okBtn.addEventListener('mouseleave', cancelOkHold);

    okBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        startOkHold(e);
    });
    okBtn.addEventListener('touchend', (e) => {
        e.preventDefault();
        cancelOkHold();
    });
    okBtn.addEventListener('touchmove', cancelOkHold);
    okBtn.addEventListener('touchcancel', cancelOkHold);
}

// Short click on reset button: opens modal → 3s hold on OK → SAVE current design as factory reset
function saveFactoryResetViaModal() {
    showConfirmModal(() => {
        saveCustomResetState();
        showToast('✅ تم حفظ التصميم الحالي كـ ضبط مصنع بنجاح!', 'success');
    });
}


// Long press 8 seconds on reset button: Open modal giving 2 options (User Custom Reset vs System Factory Default)
function showResetOptionsModal() {
    let modal = document.getElementById('reset-options-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'reset-options-modal';
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(12px);
            z-index: 9999999; display: flex; align-items: center; justify-content: center;
            padding: 15px; animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        `;
        document.body.appendChild(modal);
    }

    const hasUserCustomState = !!localStorage.getItem('balances_custom_reset_v1');

    modal.innerHTML = `
        <div style="background: linear-gradient(135deg, #0f172a, #1e293b); border: 2px solid #ca8a04; border-radius: 20px; width: 100%; max-width: 520px; box-shadow: 0 25px 60px rgba(0,0,0,0.7); overflow: hidden; padding: 24px; color: #f8fafc; text-align: center; position: relative;">
            <div style="width: 60px; height: 60px; margin: 0 auto 16px; background: rgba(202, 138, 4, 0.15); border: 2px solid #ca8a04; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; box-shadow: 0 0 20px rgba(202, 138, 4, 0.3);">
                ⚙️
            </div>
            
            <h3 style="margin: 0 0 10px 0; font-size: 1.3rem; font-weight: 900; background: linear-gradient(to right, #fef08a, #ca8a04); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                إعادة ضبط إعدادات التقرير
            </h3>
            
            <p style="margin: 0 0 22px 0; font-size: 0.95rem; color: #cbd5e1; line-height: 1.6;">
                اكتمل الضغط المطول (8 ثوانٍ)! يرجى اختيار نوع إعادة الضبط المطلوب تطبيقه على التقرير:
            </p>

            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                <!-- Option 1: User's saved custom factory reset -->
                <button type="button" onclick="window.handleResetOptionChoice('custom')" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.4)); border: 1.5px solid #10b981; color: #ffffff; padding: 14px 18px; border-radius: 14px; font-weight: 800; font-size: 0.95rem; cursor: pointer; text-align: right; display: flex; flex-direction: column; gap: 4px; transition: all 0.2s; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 1rem; font-weight: 900; color: #6ee7b7;">💾 1. استرجاع ضبط المصنع المخصص للمستخدم</span>
                        ${hasUserCustomState ? '<span style="background: #10b981; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px;">محفوظ</span>' : '<span style="background: #64748b; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px;">غير محفوظ</span>'}
                    </div>
                    <span style="font-size: 0.82rem; color: #e2e8f0; opacity: 0.9; font-weight: 500;">
                        استرجاع آخر تنسيق قام المستخدم بحفظه كضبط مصنع خاص به (بعد ضغط حفظ موافق 3 ثوانٍ).
                    </span>
                </button>

                <!-- Option 2: Original System Factory Reset -->
                <button type="button" onclick="window.handleResetOptionChoice('factory')" style="background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(3, 105, 161, 0.4)); border: 1.5px solid #38bdf8; color: #ffffff; padding: 14px 18px; border-radius: 14px; font-weight: 800; font-size: 0.95rem; cursor: pointer; text-align: right; display: flex; flex-direction: column; gap: 4px; transition: all 0.2s; box-shadow: 0 4px 15px rgba(56, 189, 248, 0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 1rem; font-weight: 900; color: #7dd3fc;">🏭 2. استرجاع الضبط الافتراضي الأصلي للنظام (المصنع)</span>
                        <span style="background: #0284c7; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px;">مثبت بالنظام</span>
                    </div>
                    <span style="font-size: 0.82rem; color: #e2e8f0; opacity: 0.9; font-weight: 500;">
                        إعادة التقرير كلياً إلى التصميم الافتراضي الذي تم برمجه ورفعه مع البرنامج.
                    </span>
                </button>
            </div>

            <!-- Cancel Button -->
            <button type="button" onclick="document.getElementById('reset-options-modal').style.display='none'" style="background: rgba(255, 255, 255, 0.08); color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.15); padding: 8px 24px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: 0.2s;">
                إلغاء
            </button>
        </div>
    `;
    modal.style.display = 'flex';
}

window.handleResetOptionChoice = function(choice) {
    const modal = document.getElementById('reset-options-modal');
    if (modal) modal.style.display = 'none';

    if (choice === 'custom') {
        restoreUserCustomResetState();
    } else if (choice === 'factory') {
        restoreToFactoryDefault();
    }
};

function restoreUserCustomResetState() {
    const raw = localStorage.getItem('balances_custom_reset_v1');
    if (raw) {
        try {
            const state = JSON.parse(raw);
            applyDesignState(state);
            showToast('💾 تم استرجاع ضبط المصنع المخصص للمستخدم بنجاح!', 'success');
        } catch(e) {
            console.error('Error parsing custom reset state', e);
            restoreToFactoryDefault();
        }
    } else {
        if (typeof showToast === 'function') {
            showToast('⚠️ لا يوجد ضبط مخصص محفوظ سابقاً، تم استرجاع الضبط الافتراضي الأصلي.', 'warning');
        }
        restoreToFactoryDefault();
    }
}

function restoreFactoryReset() {
    showResetOptionsModal();
}

function restoreToFactoryDefault() {
    const factoryState = {
        configs: getDefaultConfigs(),
        order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        theme: '',
        font: 'font-newsons',
        paperSize: 'A4',
        orientation: 'landscape',
        showLogo: true,
        showInfo: true
    };
    applyDesignState(factoryState);
    showToast('🏭 تم استرجاع الضبط الافتراضي الأصلي للنظام بنجاح!', 'info');
}


/* ─── INJECTED DATA COLLECTION ─── */
function collectAllTransactions() {
    // Always rebuild GL from source tables to ensure fresh data
    if (typeof window.rebuildGLFromAllTables === 'function') {
        try {
            window.rebuildGLFromAllTables(false);
        } catch(e) {
            console.warn('GL rebuild error:', e);
        }
    }

    // Try reading from saved GL first
    const getTenantKey = window.getTenantKey || ((k) => k);
    let savedTxns = [];
    try {
        savedTxns = JSON.parse(localStorage.getItem(getTenantKey('erp_gl_v1')) || localStorage.getItem('erp_gl_v1') || '[]');
    } catch(e) {}
    if (Array.isArray(savedTxns) && savedTxns.length > 0) {
        return savedTxns;
    }

    // Fallback: build from source tables manually
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
        const amount = parseNum(v.amount);
        const amountLocal = parseNum(v.amountLocal || (amount * rate));
        const valLoc = amountLocal;
        const valFc = isBaseSDG ? (amountLocal / effRate) : amount;

        // Receiving side (Safe/Bank)
        txns.push({
            date: v.date, time: time, ref: v.id, type: 'receipt', typeLabel: 'سند قبض',
            account: v.toAccount || 'safe-001', counterAccount: v.fromAccount,
            statement: 'سند قبض #' + (v.voucherNo || v.id),
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
            statement: 'سند قبض #' + (v.voucherNo || v.id),
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
        const amount = parseNum(v.amount);
        const amountLocal = parseNum(v.amountLocal || (amount * rate));
        const valLoc = amountLocal;
        const valFc = isBaseSDG ? (amountLocal / effRate) : amount;

        const method = v.paymentMethod || 'cash';
        if (method === 'mobile' && v.bankTransfers && v.bankTransfers.length > 0) {
            v.bankTransfers.forEach(bt => {
                const amt = parseNum(bt.amount);
                const amtLoc = isBaseSDG ? amt : amt * rate;
                const amtFc = isBaseSDG ? (amtLoc / effRate) : amt;
                const stmt = bt.statement || v.statement || v.toStatement || ('تحويل بنكي - سند صرف #' + (v.voucherNo || v.id));

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
                const amtFc = isBaseSDG ? (amtLoc / effRate) : amt;
                const stmt = ch.statement || v.statement || v.toStatement || ('شيك - سند صرف #' + (v.voucherNo || v.id));

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
                statement: v.statement || 'سند صرف #' + (v.voucherNo || v.id),
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
                statement: v.statement || 'سند صرف #' + (v.voucherNo || v.id),
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

    // Helper: merge data across tenant-prefixed key and base key for zero data loss
    function loadSourceTable(baseKey) {
        const getTK = window.getTenantKey || ((k) => k);
        const tk = getTK(baseKey);
        let list1 = [];
        let list2 = [];
        try { list1 = JSON.parse(localStorage.getItem(tk) || '[]'); } catch(e){}
        try { list2 = JSON.parse(localStorage.getItem(baseKey) || '[]'); } catch(e){}
        if (!Array.isArray(list1)) list1 = Object.values(list1);
        if (!Array.isArray(list2)) list2 = Object.values(list2);
        
        const map = new Map();
        [...list2, ...list1].forEach(v => {
            if (!v) return;
            const key = String(v.voucherNo || v.id || v.paymentNo || v.receiptNo || v.invoiceNo || Math.random()).trim();
            map.set(key, v);
        });
        return Array.from(map.values()).filter(v => v && !v.deleted);
    }

    // 2b. Multi-Receipts
    const rawMultiReceipts = loadSourceTable('erp_receipts_v1');
    rawMultiReceipts.forEach(v => {
        if (v.voucherNo && !String(v.voucherNo).startsWith('MV-') && !String(v.voucherNo).startsWith('MV17')) {
            v.id = v.voucherNo;
        } else if (v.id && !String(v.id).startsWith('MV-') && !String(v.id).startsWith('MV17')) {
            v.voucherNo = v.id;
        }
    });
    const multiReceipts = rawMultiReceipts;
    multiReceipts.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        const mainAcc = resolveAccount(v.toAccount || v.mainAccount || v.depositAccount || 'safe-001', 'safe-001');
        const refNo = (v.voucherNo && !String(v.voucherNo).startsWith('MV-')) ? v.voucherNo : ((v.id && !String(v.id).startsWith('MV-')) ? v.id : (v.voucherNo || v.id));

        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                if (!item) return;
                const pms = (item.paymentMethods && item.paymentMethods.length > 0) ? item.paymentMethods : [item.paymentMethod || 'cash'];
                const itemAcct = resolveAccount(item.accountId || item.account || item.accountName, 'unknown');
                const itemTotal = parseNum(item.amount);

                let cashAmt = parseNum(item.cashAmount);
                let bankAmt = parseNum(item.bankAmount);
                let chequeAmt = parseNum(item.chequeAmount);

                if (itemTotal > 0 && cashAmt <= 0 && bankAmt <= 0 && chequeAmt <= 0) {
                    if (pms.includes('bank')) bankAmt = itemTotal;
                    else if (pms.includes('cheque')) chequeAmt = itemTotal;
                    else cashAmt = itemTotal;
                }

                // Cash Portion
                if ((pms.includes('cash') || cashAmt > 0) && cashAmt > 0) {
                    const amtLoc = isBaseSDG ? cashAmt : cashAmt * effRate;
                    const amtFc = isBaseSDG ? (cashAmt / effRate) : cashAmt;
                    const safeAcct = resolveAccount(item.cashSafe || mainAcc, mainAcc);
                    const stmt = item.cashStatement || item.statement || v.description || 'قبض نقدية';

                    // Safe Account (Debit)
                    txns.push({
                        date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                        account: safeAcct, counterAccount: itemAcct,
                        statement: v.description || stmt,
                        additionalNote: v.description || stmt,
                        debit: amtLoc, credit: 0, 
                        debitForeign: amtFc, creditForeign: 0,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    
                    // Recipient/Payer Account (Credit)
                    txns.push({
                        date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                        account: itemAcct, counterAccount: safeAcct,
                        statement: stmt,
                        additionalNote: stmt,
                        debit: 0, credit: amtLoc, 
                        debitForeign: 0, creditForeign: amtFc,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                }

                // Bank Transfer Portion
                if ((pms.includes('bank') || bankAmt > 0) && bankAmt > 0) {
                    const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: bankAmt, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                    transfers.forEach(bt => {
                        const amt = parseNum(bt.amount || bankAmt);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * effRate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const bankAcct = resolveAccount(bt.bankName || mainAcc, mainAcc);
                        const stmt = bt.statement || item.statement || v.description || 'قبض تحويل بنكي';

                        // Bank Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: bankAcct, counterAccount: itemAcct,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Recipient/Payer Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: itemAcct, counterAccount: bankAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }

                // Cheque Portion
                if ((pms.includes('cheque') || chequeAmt > 0) && chequeAmt > 0) {
                    const cheques = (item.cheques && item.cheques.length > 0) ? item.cheques : [{ amount: chequeAmt, chequeBank: item.chequeBank, chequeNo: item.chequeNo || item.checkNo, chequeDate: item.chequeDate || item.checkDate, statement: item.chequeStatement || '' }];
                    cheques.forEach(ch => {
                        const amt = parseNum(ch.amount || chequeAmt);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * effRate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const chequeAcct = 'cheques-receivable';
                        const stmt = ch.statement || item.statement || v.description || 'قبض شيك';

                        // Cheque Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: chequeAcct, counterAccount: itemAcct,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Recipient/Payer Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: itemAcct, counterAccount: chequeAcct,
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
            const valLoc = isBaseSDG ? totalAmount : totalAmount * effRate;
            const valFc = isBaseSDG ? (totalAmount / effRate) : totalAmount;
            txns.push({
                date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                account: mainAcc, counterAccount: 'حسابات فرعية متعددة',
                statement: v.description || 'قبض مجمع',
                additionalNote: v.description || 'سند قبض مجمع',
                debit: valLoc, credit: 0, 
                debitForeign: valFc, creditForeign: 0,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
            txns.push({
                date: v.date, time: time, ref: refNo, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                account: 'حسابات فرعية متعددة', counterAccount: mainAcc,
                statement: v.description || 'قبض مجمع',
                additionalNote: v.description || 'سند قبض مجمع',
                debit: 0, credit: valLoc, 
                debitForeign: 0, creditForeign: valFc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
        }
    });

    // 2c. Multi-Payments
    const rawMultiPayments = loadSourceTable('erp_payments_v1');
    rawMultiPayments.forEach(v => {
        if (v.voucherNo && !String(v.voucherNo).startsWith('MV-') && !String(v.voucherNo).startsWith('MV17')) {
            v.id = v.voucherNo;
        } else if (v.id && !String(v.id).startsWith('MV-') && !String(v.id).startsWith('MV17')) {
            v.voucherNo = v.id;
        }
    });
    const multiPayments = rawMultiPayments;
    multiPayments.forEach(v => {
        const costCenter = v.costCenter || 'بدون مركز';
        const currency = v.currency || 'SDG';
        const time = v.time || timeNow;
        const isPosted = v.isPosted !== false;
        const mainAcc = resolveAccount(v.fromAccount || v.mainAccount || v.withdrawalAccount || 'safe-001', 'safe-001');
        const refNo = (v.voucherNo && !String(v.voucherNo).startsWith('MV-')) ? v.voucherNo : ((v.id && !String(v.id).startsWith('MV-')) ? v.id : (v.voucherNo || v.id));

        const rate = parseNum(v.exchangeRate || 1);
        const effRate = getEffectiveRate(v, rate);
        const isBaseSDG = (currency === 'SDG');

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                if (!item) return;
                const pms = (item.paymentMethods && item.paymentMethods.length > 0) ? item.paymentMethods : [item.paymentMethod || 'cash'];
                const itemAcct = resolveAccount(item.accountId || item.account || item.accountName, 'unknown');
                const itemTotal = parseNum(item.amount);

                let cashAmt = parseNum(item.cashAmount);
                let bankAmt = parseNum(item.bankAmount);
                let chequeAmt = parseNum(item.chequeAmount);

                if (itemTotal > 0 && cashAmt <= 0 && bankAmt <= 0 && chequeAmt <= 0) {
                    if (pms.includes('bank')) bankAmt = itemTotal;
                    else if (pms.includes('cheque')) chequeAmt = itemTotal;
                    else cashAmt = itemTotal;
                }
                
                // Cash Portion
                if ((pms.includes('cash') || cashAmt > 0) && cashAmt > 0) {
                    const amtLoc = isBaseSDG ? cashAmt : cashAmt * effRate;
                    const amtFc = isBaseSDG ? (cashAmt / effRate) : cashAmt;
                    const safeAcct = resolveAccount(item.cashSafe || mainAcc, mainAcc);
                    const stmt = item.cashStatement || item.statement || v.description || 'صرف نقدية';

                    // Recipient Account (Debit)
                    txns.push({
                        date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                        account: itemAcct, counterAccount: safeAcct,
                        statement: stmt,
                        additionalNote: stmt,
                        debit: amtLoc, credit: 0, 
                        debitForeign: amtFc, creditForeign: 0,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    
                    // Safe Account (Credit)
                    txns.push({
                        date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                        account: safeAcct, counterAccount: itemAcct,
                        statement: v.description || stmt,
                        additionalNote: v.description || stmt,
                        debit: 0, credit: amtLoc, 
                        debitForeign: 0, creditForeign: amtFc,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                    });
                }

                // Bank Transfer Portion
                if ((pms.includes('bank') || bankAmt > 0) && bankAmt > 0) {
                    const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: bankAmt, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                    transfers.forEach(bt => {
                        const amt = parseNum(bt.amount || bankAmt);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * effRate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const bankAcct = resolveAccount(bt.bankName || mainAcc, mainAcc);
                        const stmt = bt.statement || item.statement || v.description || 'صرف تحويل بنكي';

                        // Recipient Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: itemAcct, counterAccount: bankAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Bank Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: bankAcct, counterAccount: itemAcct,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                    });
                }

                // Cheque Portion
                if ((pms.includes('cheque') || chequeAmt > 0) && chequeAmt > 0) {
                    const cheques = (item.cheques && item.cheques.length > 0) ? item.cheques : [{ amount: chequeAmt, chequeBank: item.chequeBank, chequeNo: item.chequeNo || item.checkNo, chequeDate: item.chequeDate || item.checkDate, statement: item.chequeStatement || '' }];
                    cheques.forEach(ch => {
                        const amt = parseNum(ch.amount || chequeAmt);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * effRate;
                        const amtFc = isBaseSDG ? (amt / effRate) : amt;
                        const chequeAcct = resolveAccount(ch.chequeBank || 'banks-cheques', 'banks-cheques');
                        const stmt = ch.statement || item.statement || v.description || 'صرف شيك';

                        // Recipient Account (Debit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: itemAcct, counterAccount: chequeAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: amtLoc, credit: 0, 
                            debitForeign: amtFc, creditForeign: 0,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: effRate, isPosted
                        });
                        
                        // Cheque Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: chequeAcct, counterAccount: itemAcct,
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
            const valLoc = isBaseSDG ? totalAmount : totalAmount * effRate;
            const valFc = isBaseSDG ? (totalAmount / effRate) : totalAmount;
            txns.push({
                date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                account: mainAcc, counterAccount: 'حسابات فرعية متعددة',
                statement: v.description || 'صرف مجمع',
                additionalNote: v.description || 'سند صرف مجمع',
                debit: 0, credit: valLoc, 
                debitForeign: 0, creditForeign: valFc,
                costCenter, currency, exchangeRate: effRate, isPosted
            });
            txns.push({
                date: v.date, time: time, ref: refNo, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                account: 'حسابات فرعية متعددة', counterAccount: mainAcc,
                statement: v.description || 'صرف مجمع',
                additionalNote: v.description || 'سند صرف مجمع',
                debit: valLoc, credit: 0, 
                debitForeign: valFc, creditForeign: 0,
                costCenter, currency, exchangeRate: effRate, isPosted
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
            const effRate = getEffectiveRate(v, rate);
            const isBaseSDG = (currency === 'SDG');
            const deb = parseNum(line.debit);
            const cred = parseNum(line.credit);
            const debLoc = isBaseSDG ? deb : deb * rate;
            const debFc = isBaseSDG ? (debLoc / effRate) : deb;
            const credLoc = isBaseSDG ? cred : cred * rate;
            const credFc = isBaseSDG ? (credLoc / effRate) : cred;

            txns.push({
                date: v.date, time: time, ref: v.id, type: 'jv', typeLabel: 'قيد يومي',
                account: line.account, counterAccount: '-',
                statement: 'قيد يومي #' + (v.voucherNo || v.id),
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

        txns.push({
            date: inv.date, time: time, ref: refNo, type: 'invoice', typeLabel: 'مبيعات',
            account: customerAcct, counterAccount: 'revenue-sales',
            statement: 'فاتورة مبيعات #' + refNo,
            additionalNote: `مبيعات آجلة — ${inv.customerName || inv.customer || ''}`,
            debit: amtLoc, credit: 0, 
            debitForeign: amtFc, creditForeign: 0,
            costCenter, currency,
            exchangeRate: effRate,
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
            exchangeRate: effRate,
            isPosted: isPosted
        });

        if (inv.paymentMethod === 'hybrid' || inv.paymentMethod === 'multipay') {
            // A. Cash Safe Portion
            const cash = parseNum(inv.cashAmount || 0);
            if (cash > 0) {
                const cashLoc = isBaseSDG ? cash : cash * rate;
                const cashFc = isBaseSDG ? (cashLoc / effRate) : cash;
                const paymentAcct = inv.paymentAccountId || 'safe-001';
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PAID', type: 'receipt', typeLabel: 'سداد فاتورة',
                    account: paymentAcct, counterAccount: customerAcct,
                    statement: 'استلام نقدية (نقداً)',
                    additionalNote: 'سداد نقدي مع الفاتورة',
                    debit: cashLoc, credit: 0, 
                    debitForeign: cashFc, creditForeign: 0,
                    costCenter, currency,
                    exchangeRate: effRate,
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
                        const amtFcBt = isBaseSDG ? (amtLocBt / effRate) : amt;
                        const bankAcct = bt.bankAccountId || 'bank-001';
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-BT${idx+1}`, type: 'receipt', typeLabel: 'سداد فاتورة',
                            account: bankAcct, counterAccount: customerAcct,
                            statement: `تحويل بنكي مرجع #${bt.reference || ''}`,
                            additionalNote: 'سداد تحويل بنكي مع الفاتورة',
                            debit: amtLocBt, credit: 0, 
                            debitForeign: amtFcBt, creditForeign: 0,
                            costCenter, currency,
                            exchangeRate: effRate,
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
                        const amtFcCh = isBaseSDG ? (amtLocCh / effRate) : amt;
                        const chequeAcct = 'cheques-receivable';
                        txns.push({
                            date: inv.date, time: time, ref: `${refNo}-CH${idx+1}`, type: 'receipt', typeLabel: 'سداد فاتورة',
                            account: chequeAcct, counterAccount: customerAcct,
                            statement: `شيك رقم #${ch.chequeNo || ''} استحقاق ${ch.dueDate || ''}`,
                            additionalNote: 'سداد شيك مع الفاتورة',
                            debit: amtLocCh, credit: 0, 
                            debitForeign: amtFcCh, creditForeign: 0,
                            costCenter, currency,
                            exchangeRate: effRate,
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
                            exchangeRate: effRate,
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
                exchangeRate: effRate,
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
    const allData = [
        ...receipts, ...payments, ...multiReceipts, ...multiPayments, ...jvs, ...sales, ...purchases
    ];
    const dataMap = new Map();
    allData.forEach(d => {
        if(d.id) dataMap.set(d.id, d);
        if(d.invoiceNo) dataMap.set(d.invoiceNo, d);
        if(d.voucherNo) dataMap.set(d.voucherNo, d);
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
                    if (ch) t.checkNo = (ch.checkNo || ch.chequeNo || '') + (ch.checkBranch || ch.chequeBank ? ` (${ch.checkBranch || ch.chequeBank})` : '');
                } else if (source.checkNo || source.checkNumber || source.chequeNo) {
                    let cNo = source.checkNo || source.checkNumber || source.chequeNo;
                    let cBank = source.checkBranch || source.chequeBank || '';
                    t.checkNo = cBank ? `${cNo} (${cBank})` : cNo;
                }
            }

            // Process bank transfers
            if (!t.transferNo) {
                if (source.bankTransfers && source.bankTransfers.length > 0) {
                    let bt = source.bankTransfers.find(b => matchAmt(b.amount)) || source.bankTransfers[0];
                    if (bt) {
                        t.transferNo = (bt.transferNo || '');
                        t.bankName = (bt.bankName || bt.note || '');
                    }
                } else if (source.transferNo || source.mobileTransferNo) {
                    let tr = source.transferNo || source.mobileTransferNo;
                    let note = source.mobileNote || source.bankName || source.note || '';
                    t.transferNo = tr;
                    t.bankName = note;
                }
            }


            // Process multi-items
            if (source.items && source.items.length > 0) {
                let item = source.items.find(it => it.account === t.account || it.account === t.counterAccount);
                if (item) {
                    if (item.cheques && item.cheques.length > 0) {
                        let ch = item.cheques.find(c => matchAmt(c.amount)) || item.cheques[0];
                        if (ch) t.checkNo = (ch.chequeNo || ch.checkNo || '') + (ch.chequeBank || ch.checkBranch ? ` (${ch.chequeBank || ch.checkBranch})` : '');
                    } else if (item.checkNo || item.chequeNo) {
                        t.checkNo = (item.checkNo || item.chequeNo) + (item.chequeBank || item.checkBranch ? ` (${item.chequeBank || item.checkBranch})` : '');
                    }

                    if (item.bankTransfers && item.bankTransfers.length > 0) {
                        let bt = item.bankTransfers.find(b => matchAmt(b.amount)) || item.bankTransfers[0];
                        if (bt) {
                            t.transferNo = (bt.transferNo || '');
                            t.bankName = (bt.bankName || bt.note || '');
                        }
                    } else if (item.transferNo) {
                        t.transferNo = item.transferNo;
                        t.bankName = item.bankName || item.note || '';
                    }
                }
            }
        }
    });

    return txns;
}

function getAccountName(id) {
    if(id === 'ALL') return 'جميع الحسابات';
    if(id === '-' || !id) return '-';
    
    let idStr = String(id).trim();
    let idLower = idStr.toLowerCase();
    
    const sysMap = {
        'exp-purchases': 'المشتريات',
        'revenue-sales': 'إيرادات المبيعات',
        'cost-purchases': 'تكلفة المشتريات (مخزون)',
        'act-inventory': 'حساب المخزون',
        'exp-inventory-adjustments': 'تسويات المخزون',
        'cheques-receivable': 'شيكات برسم التحصيل',
        'discount-permitted': 'خصم مسموح به',
        'discount-received': 'خصم مكتسب',
        'vat-collected': 'ضريبة مبيعات',
        'vat-paid': 'ضريبة مشتريات'
    };
    if (sysMap[idLower]) return sysMap[idLower];
    
    // 1. Fast O(1) cache lookup
    if (ACCOUNT_NAME_CACHE.has(idLower)) return ACCOUNT_NAME_CACHE.get(idLower);
    
    // 2. Try without prefix
    let cleanId = idLower.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
    if (cleanId && ACCOUNT_NAME_CACHE.has(cleanId)) return ACCOUNT_NAME_CACHE.get(cleanId);
    
    // 3. Fallback: return cleaned ID
    return cleanId || idStr;
}
var ACCOUNT_NAME_CACHE = new Map();


function parseNum(val) { const n = parseFloat(val); return isNaN(n) ? 0 : n; }


/* ─── INJECTED ACCOUNTS BUILDER ─── */
var ALL_ACCOUNTS = [];
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

    // Build O(1) lookup cache
    ACCOUNT_NAME_CACHE = new Map();
    ALL_ACCOUNTS.forEach(a => {
        const idLower = String(a.id).toLowerCase();
        ACCOUNT_NAME_CACHE.set(idLower, a.name);
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

