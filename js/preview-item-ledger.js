// js/preview-ledger.js - General Ledger Pro Studio Edition

const PRESETS_STORAGE_KEY = 'item_ledger_preview_presets_v20';
const MAX_PRESETS_LIMIT = 6;
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

// Default column definitions with exact field mapping for Item Ledger (4 Units)
const baseColumns = [
    { idx: 0, defaultLabel: "التاريخ", field: "date", isNum: false, group: "general" },
    { idx: 1, defaultLabel: "نوع الحركة", field: "type", isNum: false, group: "general" },
    { idx: 2, defaultLabel: "رقم الإذن", field: "permitNo", isNum: false, group: "general" },
    { idx: 3, defaultLabel: "رقم الاستلام", field: "receiptNo", isNum: false, group: "general" },
    { idx: 17, defaultLabel: "وحدة وكمية الوارد والصادر", field: "origQtyStr", isNum: false, group: "general" },
    { idx: 4, defaultLabel: "الوارد (الأساسية)", field: "in1", isNum: true, group: "u1" },
    { idx: 5, defaultLabel: "المنصرف (الأساسية)", field: "out1", isNum: true, group: "u1" },
    { idx: 6, defaultLabel: "الرصيد (الأساسية)", field: "bal1", isNum: true, group: "u1" },
    { idx: 7, defaultLabel: "الوارد (الوحدة 2)", field: "in2", isNum: true, group: "u2" },
    { idx: 8, defaultLabel: "المنصرف (الوحدة 2)", field: "out2", isNum: true, group: "u2" },
    { idx: 9, defaultLabel: "الرصيد (الوحدة 2)", field: "bal2", isNum: true, group: "u2" },
    { idx: 10, defaultLabel: "الوارد (الوحدة 3)", field: "in3", isNum: true, group: "u3" },
    { idx: 11, defaultLabel: "المنصرف (الوحدة 3)", field: "out3", isNum: true, group: "u3" },
    { idx: 12, defaultLabel: "الرصيد (الوحدة 3)", field: "bal3", isNum: true, group: "u3" },
    { idx: 13, defaultLabel: "الوارد (الوحدة 4)", field: "in4", isNum: true, group: "u4" },
    { idx: 14, defaultLabel: "المنصرف (الوحدة 4)", field: "out4", isNum: true, group: "u4" },
    { idx: 15, defaultLabel: "الرصيد (الوحدة 4)", field: "bal4", isNum: true, group: "u4" },
    { idx: 16, defaultLabel: "ملحوظة الحركة", field: "notes", isNum: false, group: "general" }
];

window.activeUnitsConfig = [];

function getRawFieldValue(row, field) {
    if (!row) return "";
    if (field === "date") {
        let rawDate = String(row.date || "").trim();
        if (rawDate.includes("T")) rawDate = rawDate.split("T")[0];
        return rawDate;
    }
    if (field === "type") return row.typeName || row.typeCode || "-";
    if (field === "permitNo") return row.permitNo || "-";
    if (field === "receiptNo") return row.ref || row.receiptNo || "-";
    if (field === "origQtyStr") {
        const origQtyAbs = Math.abs(row.origQty || (row.in > 0 ? row.in : row.out) || 0);
        const origUnitStr = row.origUnit || (window.activeUnitsConfig[0] ? window.activeUnitsConfig[0].name : "");
        return origQtyAbs > 0 ? `${origQtyAbs} ${origUnitStr}` : "-";
    }
    if (field === "notes") return row.notes || "-";
    
    if (field === "in1") return row.in1 || 0;
    if (field === "out1") return row.out1 || 0;
    if (field === "bal1") return row.bal1 || 0;
    
    if (field === "in2") return row.in2 || 0;
    if (field === "out2") return row.out2 || 0;
    if (field === "bal2") return row.bal2 || 0;
    
    if (field === "in3") return row.in3 || 0;
    if (field === "out3") return row.out3 || 0;
    if (field === "bal3") return row.bal3 || 0;

    if (field === "in4") return row.in4 || 0;
    if (field === "out4") return row.out4 || 0;
    if (field === "bal4") return row.bal4 || 0;

    return "";
}

function getDefaultConfigs() {
    let conf = {};
    baseColumns.forEach(c => {
        let label = c.defaultLabel;
        if (c.group === 'u1' && window.activeUnitsConfig && window.activeUnitsConfig[0]) {
            label = label.replace('(الأساسية)', `(بـ ${window.activeUnitsConfig[0].name})`);
        }
        columnConfigs[c.field] = {
            visible: columnOrder.includes(c.idx),
            label: label,
            width: null,
            align: c.isNum ? 'right' : 'left'
        };
    });
    return conf;
}

let columnOrder = [0, 1, 2, 3, 16, 17, 4, 5, 6];
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


// Override DOMContentLoaded for Item Ledger
document.addEventListener('DOMContentLoaded', async () => {
    const payloadStr = localStorage.getItem('preview_item_ledger_payload');
    if (!payloadStr) {
        alert("لا توجد بيانات للمعاينة!");
        return;
    }
    const payload = JSON.parse(payloadStr);
    window.activeUnitsConfig = payload.activeUnits || [];
    
    currentData = payload.history.map(row => {
        let newRow = { ...row };
        const formatQtyInUnit = (baseQty, unitObj) => {
            if (baseQty === undefined || baseQty === null || isNaN(baseQty) || baseQty === 0) return 0;
            const factor = (unitObj && unitObj.factor > 0) ? unitObj.factor : 1;
            return baseQty / factor;
        };

        if (window.activeUnitsConfig[0]) {
            newRow.in1 = formatQtyInUnit(row.in, window.activeUnitsConfig[0]);
            newRow.out1 = formatQtyInUnit(row.out, window.activeUnitsConfig[0]);
            newRow.bal1 = formatQtyInUnit(row.runningBalance, window.activeUnitsConfig[0]);
        }
        if (window.activeUnitsConfig[1]) {
            newRow.in2 = formatQtyInUnit(row.in, window.activeUnitsConfig[1]);
            newRow.out2 = formatQtyInUnit(row.out, window.activeUnitsConfig[1]);
            newRow.bal2 = formatQtyInUnit(row.runningBalance, window.activeUnitsConfig[1]);
        }
        if (window.activeUnitsConfig[2]) {
            newRow.in3 = formatQtyInUnit(row.in, window.activeUnitsConfig[2]);
            newRow.out3 = formatQtyInUnit(row.out, window.activeUnitsConfig[2]);
            newRow.bal3 = formatQtyInUnit(row.runningBalance, window.activeUnitsConfig[2]);
        }
        if (window.activeUnitsConfig[3]) {
            newRow.in4 = formatQtyInUnit(row.in, window.activeUnitsConfig[3]);
            newRow.out4 = formatQtyInUnit(row.out, window.activeUnitsConfig[3]);
            newRow.bal4 = formatQtyInUnit(row.runningBalance, window.activeUnitsConfig[3]);
        }
        return newRow;
    });

    const reportTitle = document.getElementById('report-main-title');
    if(reportTitle) reportTitle.innerText = 'كشف حركة الصنف التفصيلي';
    
    const itemNameStr = payload.item ? (payload.item.name + ' (' + payload.item.id + ')') : '-';
    const custLbl = document.getElementById('report-customer-name-lbl');
    if(custLbl) custLbl.innerHTML = '<span style="font-size:0.8rem; color:#64748b;">الصنف:</span> ' + itemNameStr;
    
    const whStr = payload.whName || '-';
    const dateLbl = document.getElementById('report-date-lbl');
    if(dateLbl) dateLbl.innerHTML = '<span style="font-size:0.8rem; color:#64748b;">المستودع:</span> ' + whStr;
    
    const periodStr = (payload.fromDateStr || payload.toDateStr) ? 'من ' + (payload.fromDateStr||'البداية') + ' إلى ' + (payload.toDateStr||'اليوم') : 'كلي';
    const periodLbl = document.getElementById('report-period-lbl');
    if(periodLbl) periodLbl.innerHTML = '<span style="font-size:0.8rem; color:#64748b;">الفترة:</span> ' + periodStr;
    
    if (typeof loadCompanyHeader === 'function') loadCompanyHeader();

    // Unit 1 (Base Unit)
    if (window.activeUnitsConfig[0]) {
        baseColumns.find(c => c.field === 'in1').defaultLabel = 'الوارد (' + window.activeUnitsConfig[0].name + ')';
        baseColumns.find(c => c.field === 'out1').defaultLabel = 'المنصرف (' + window.activeUnitsConfig[0].name + ')';
        baseColumns.find(c => c.field === 'bal1').defaultLabel = 'الرصيد (' + window.activeUnitsConfig[0].name + ')';
    } else {
        baseColumns.find(c => c.field === 'in1').visible = false;
        baseColumns.find(c => c.field === 'out1').visible = false;
        baseColumns.find(c => c.field === 'bal1').visible = false;
    }
    
    // Unit 2
    if (window.activeUnitsConfig[1]) {
        baseColumns.find(c => c.field === 'in2').defaultLabel = 'الوارد (' + window.activeUnitsConfig[1].name + ')';
        baseColumns.find(c => c.field === 'out2').defaultLabel = 'المنصرف (' + window.activeUnitsConfig[1].name + ')';
        baseColumns.find(c => c.field === 'bal2').defaultLabel = 'الرصيد (' + window.activeUnitsConfig[1].name + ')';
    } else {
        baseColumns.find(c => c.field === 'in2').visible = false;
        baseColumns.find(c => c.field === 'out2').visible = false;
        baseColumns.find(c => c.field === 'bal2').visible = false;
    }
    
    // Unit 3
    if (window.activeUnitsConfig[2]) {
        baseColumns.find(c => c.field === 'in3').defaultLabel = 'الوارد (' + window.activeUnitsConfig[2].name + ')';
        baseColumns.find(c => c.field === 'out3').defaultLabel = 'المنصرف (' + window.activeUnitsConfig[2].name + ')';
        baseColumns.find(c => c.field === 'bal3').defaultLabel = 'الرصيد (' + window.activeUnitsConfig[2].name + ')';
    } else {
        baseColumns.find(c => c.field === 'in3').visible = false;
        baseColumns.find(c => c.field === 'out3').visible = false;
        baseColumns.find(c => c.field === 'bal3').visible = false;
    }

    // Unit 4
    if (window.activeUnitsConfig[3]) {
        baseColumns.find(c => c.field === 'in4').defaultLabel = 'الوارد (' + window.activeUnitsConfig[3].name + ')';
        baseColumns.find(c => c.field === 'out4').defaultLabel = 'المنصرف (' + window.activeUnitsConfig[3].name + ')';
        baseColumns.find(c => c.field === 'bal4').defaultLabel = 'الرصيد (' + window.activeUnitsConfig[3].name + ')';
    } else {
        baseColumns.find(c => c.field === 'in4').visible = false;
        baseColumns.find(c => c.field === 'out4').visible = false;
        baseColumns.find(c => c.field === 'bal4').visible = false;
    }
    
    // Initial Column Order containing all active/visible columns
    const newOrder = [];
    baseColumns.forEach(c => {
        if (c.visible !== false) newOrder.push(c.idx);
    });
    // Add remaining columns at end so they can be re-enabled
    baseColumns.forEach(c => {
        if (!newOrder.includes(c.idx)) newOrder.push(c.idx);
    });
    
    columnOrder = newOrder;

    if (typeof initPresets === 'function') initPresets();
    filteredData = currentData;
    if (typeof renderTable === 'function') renderTable();
    if (typeof generateChartData === 'function') generateChartData();
    renderColumnManagerChips();
});

// ─── VISUAL DRAG & DROP COLUMN MANAGER ───
let draggedColIdx = null;

function renderColumnManagerChips() {
    const container = document.getElementById('studio-columns-chips-container');
    if (!container) return;

    container.innerHTML = '';
    
    const groupColors = {
        general: { bg: 'rgba(56, 189, 248, 0.12)', border: 'rgba(56, 189, 248, 0.3)', text: '#38bdf8' },
        u1:      { bg: 'rgba(16, 185, 129, 0.15)', border: 'rgba(16, 185, 129, 0.35)', text: '#34d399' },
        u2:      { bg: 'rgba(6, 182, 212, 0.15)',  border: 'rgba(6, 182, 212, 0.35)',  text: '#22d3ee' },
        u3:      { bg: 'rgba(168, 85, 247, 0.15)', border: 'rgba(168, 85, 247, 0.35)', text: '#c084fc' },
        u4:      { bg: 'rgba(245, 158, 11, 0.15)', border: 'rgba(245, 158, 11, 0.35)', text: '#fbbf24' }
    };

    columnOrder.forEach((idx, pos) => {
        const c = baseColumns.find(x => x.idx === idx);
        if (!c) return;

        const conf = columnConfigs[idx] || getDefaultConfigs()[idx];
        const isVisible = conf.visible !== false;
        const isSelected = (selectedColIndex === idx);
        const gColor = groupColors[c.group] || groupColors.general;

        const chip = document.createElement('div');
        chip.className = 'studio-col-chip' + (isSelected ? ' active-chip' : '') + (!isVisible ? ' hidden-chip' : '');
        chip.draggable = true;
        chip.dataset.colIdx = idx;
        chip.dataset.colPos = pos;

        chip.style.cssText = `
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 0.76rem;
            font-weight: 700;
            background: ${isSelected ? '#1e293b' : (isVisible ? gColor.bg : 'rgba(255,255,255,0.03)')};
            border: 1.5px solid ${isSelected ? '#38bdf8' : (isVisible ? gColor.border : 'rgba(255,255,255,0.1)')};
            color: ${isVisible ? (isSelected ? '#fff' : gColor.text) : '#64748b'};
            cursor: grab;
            user-select: none;
            transition: all 0.2s ease;
            box-shadow: ${isSelected ? '0 0 10px rgba(56,189,248,0.4)' : 'none'};
        `;

        // Checkbox
        const chk = document.createElement('input');
        chk.type = 'checkbox';
        chk.checked = isVisible;
        chk.title = isVisible ? 'إخفاء العمود' : 'إظهار العمود';
        chk.style.cursor = 'pointer';
        chk.onchange = (e) => {
            e.stopPropagation();
            toggleColumnVisibility(idx, chk.checked);
        };

        // Drag handle icon
        const handle = document.createElement('span');
        handle.innerText = '⠿';
        handle.style.opacity = '0.5';
        handle.style.cursor = 'grab';

        // Title Label
        const titleSpan = document.createElement('span');
        titleSpan.innerText = conf.label || c.defaultLabel;
        titleSpan.style.cursor = 'pointer';
        titleSpan.onclick = () => selectColumn(idx);

        // Move Left / Right buttons
        const btnPrev = document.createElement('button');
        btnPrev.type = 'button';
        btnPrev.innerText = '➡️';
        btnPrev.title = 'تحريك للأمام (يمين)';
        btnPrev.style.cssText = 'background:none; border:none; color:#94a3b8; font-size:0.65rem; cursor:pointer; padding:0 2px;';
        btnPrev.onclick = (e) => { e.stopPropagation(); moveColumnOrderPos(pos, -1); };

        const btnNext = document.createElement('button');
        btnNext.type = 'button';
        btnNext.innerText = '⬅️';
        btnNext.title = 'تحريك للخلف (يسار)';
        btnNext.style.cssText = 'background:none; border:none; color:#94a3b8; font-size:0.65rem; cursor:pointer; padding:0 2px;';
        btnNext.onclick = (e) => { e.stopPropagation(); moveColumnOrderPos(pos, 1); };

        chip.appendChild(handle);
        chip.appendChild(chk);
        chip.appendChild(titleSpan);
        chip.appendChild(btnPrev);
        chip.appendChild(btnNext);

        // HTML5 Drag Events
        chip.ondragstart = (e) => {
            draggedColIdx = idx;
            chip.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
        };
        chip.ondragend = () => {
            chip.style.opacity = '1';
            draggedColIdx = null;
        };
        chip.ondragover = (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            chip.style.transform = 'scale(1.05)';
        };
        chip.ondragleave = () => {
            chip.style.transform = 'none';
        };
        chip.ondrop = (e) => {
            e.preventDefault();
            chip.style.transform = 'none';
            if (draggedColIdx !== null && draggedColIdx !== idx) {
                swapColumnOrderIndices(draggedColIdx, idx);
            }
        };

        container.appendChild(chip);
    });
}

function toggleColumnVisibility(idx, isVisible) {
    if (!columnConfigs[idx]) {
        columnConfigs[idx] = getDefaultConfigs()[idx];
    }
    columnConfigs[idx].visible = isVisible;
    
    if (selectedColIndex === idx) {
        const chk = document.getElementById('col-visible-chk');
        if (chk) chk.checked = isVisible;
    }
    
    renderTable();
    renderColumnManagerChips();
}

function setAllColumnsVisibility(isVisible) {
    baseColumns.forEach(c => {
        if (!columnConfigs[c.idx]) columnConfigs[c.idx] = getDefaultConfigs()[c.idx];
        columnConfigs[c.idx].visible = isVisible;
    });
    renderTable();
    renderColumnManagerChips();
}

function resetDefaultColumnOrder() {
    columnOrder = [0, 1, 2, 3, 16, 17, 4, 5, 6];
    baseColumns.forEach(c => {
        if (!columnConfigs[c.idx]) columnConfigs[c.idx] = getDefaultConfigs()[c.idx];
        columnConfigs[c.idx].visible = (c.visible !== false);
    });
    renderTable();
    renderColumnManagerChips();
}

function moveColumnOrderPos(currentPos, direction) {
    const newPos = currentPos + direction;
    if (newPos < 0 || newPos >= columnOrder.length) return;
    
    const temp = columnOrder[currentPos];
    columnOrder[currentPos] = columnOrder[newPos];
    columnOrder[newPos] = temp;
    
    renderTable();
    renderColumnManagerChips();
}

function swapColumnOrderIndices(idxA, idxB) {
    const posA = columnOrder.indexOf(idxA);
    const posB = columnOrder.indexOf(idxB);
    if (posA === -1 || posB === -1) return;
    
    columnOrder.splice(posA, 1);
    columnOrder.splice(posB, 0, idxA);
    
    renderTable();
    renderColumnManagerChips();
}


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
    if (typeof applyPaperSimulation === 'function') {
        applyPaperSimulation(size, null, null);
    } else {
        updatePageCss();
    }
}

function setPageOrientation(orient, updateSelect = true) {
    currentOrientation = orient;
    if (typeof applyPaperSimulation === 'function') {
        applyPaperSimulation(null, orient, null);
    } else {
        const wrapper = document.getElementById('print-area-wrapper');
        if (wrapper) {
            wrapper.classList.remove('paper-portrait', 'paper-landscape');
            wrapper.classList.add('paper-' + orient);
        }
        updatePageCss();
    }
}

function updatePageCss() {
    if (typeof applyPaperSimulation === 'function') {
        applyPaperSimulation(currentPaperSize, currentOrientation, currentMargins);
    } else {
        const styleEl = document.getElementById('dynamic-paper-style');
        if (styleEl) {
            styleEl.innerHTML = `@page { size: ${currentPaperSize} ${currentOrientation}; margin: ${typeof currentMargins !== 'undefined' ? currentMargins : '15mm'}; }`;
        }
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
        th.setAttribute('data-col-idx', idx);
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
        if (conf.width) {
            const wNum = parseInt(conf.width, 10);
            th.style.width = conf.width;
            th.style.minWidth = conf.width;
            th.style.maxWidth = conf.width;
            if (!isNaN(wNum)) {
                if (wNum <= 10) {
                    th.style.padding = '0px';
                    th.style.fontSize = '0px';
                    th.style.color = 'transparent';
                } else if (wNum < 45) {
                    th.style.paddingLeft = '2px';
                    th.style.paddingRight = '2px';
                }
            }
        } else {
            th.style.width = '';
            th.style.minWidth = '';
            th.style.maxWidth = '';
        }
        
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
            td.setAttribute('data-col-idx', idx);
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
        if (conf.width) {
            const wNum = parseInt(conf.width, 10);
            td.style.width = conf.width;
            td.style.minWidth = conf.width;
            td.style.maxWidth = conf.width;
            if (!isNaN(wNum)) {
                if (wNum <= 10) {
                    td.style.padding = '0px';
                    td.style.fontSize = '0px';
                    td.style.color = 'transparent';
                } else if (wNum < 45) {
                    td.style.paddingLeft = '2px';
                    td.style.paddingRight = '2px';
                }
            }
        } else {
            td.style.width = '';
            td.style.minWidth = '';
            td.style.maxWidth = '';
        }
            
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
            td.setAttribute('data-col-idx', idx);
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
                if (c.field.startsWith('bal')) {
                    footerVal = filteredData.length > 0 ? parseFloat(getRawFieldValue(filteredData[filteredData.length - 1], c.field) || 0) : 0;
                } else if (c.field === 'exchangeRate') {
                    footerVal = filteredData.length > 0 ? (footerVal / filteredData.length) : 0;
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
    
    
    const widthRng = document.getElementById('col-width-range');
    const widthLbl = document.getElementById('sr-col-w-lbl');
    let currentW = 120;
    if (conf.width) {
        currentW = parseInt(conf.width, 10) || 120;
    } else {
        currentW = (c && c.isNum) ? 110 : 180;
    }
    if (widthRng) {
        widthRng.disabled = false;
        widthRng.value = currentW;
    }
    if (widthLbl) {
        widthLbl.textContent = currentW + 'px';
    }
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


function applyLiveColumnWidth(colIdx, widthVal) {
    if (colIdx === null || colIdx === undefined) return;
    const wNum = parseInt(widthVal, 10);
    const widthStr = typeof widthVal === 'number' ? widthVal + 'px' : widthVal;
    
    if (!columnConfigs[colIdx]) {
        columnConfigs[colIdx] = getDefaultConfigs()[colIdx] || { visible: true, fs: 0.9 };
    }
    columnConfigs[colIdx].width = widthStr;

    // Direct DOM fast-path mutation without rebuilding table
    const cells = document.querySelectorAll('[data-col-idx="' + colIdx + '"]');
    cells.forEach(cell => {
        cell.style.width = widthStr;
        cell.style.minWidth = widthStr;
        cell.style.maxWidth = widthStr;
        cell.style.overflow = 'hidden';
        cell.style.boxSizing = 'border-box';
        
        if (!isNaN(wNum) && wNum <= 15) {
            cell.style.paddingLeft = '0px';
            cell.style.paddingRight = '0px';
            cell.style.paddingTop = '2px';
            cell.style.paddingBottom = '2px';
            cell.style.fontSize = '0px';
            cell.style.color = 'transparent';
        } else if (!isNaN(wNum) && wNum < 40) {
            cell.style.paddingLeft = '2px';
            cell.style.paddingRight = '2px';
            cell.style.fontSize = '';
            cell.style.color = '';
        } else {
            cell.style.paddingLeft = '';
            cell.style.paddingRight = '';
            cell.style.fontSize = '';
            cell.style.color = '';
        }
    });

    const lbl = document.getElementById('sr-col-w-lbl');
    if (lbl) lbl.textContent = widthStr;
    const rng = document.getElementById('col-width-range');
    if (rng && parseInt(rng.value, 10) !== wNum) rng.value = wNum;
}
window.applyLiveColumnWidth = applyLiveColumnWidth;

function updateSelectedCol(type, val) {
    if (type === 'width') {
        if (selectedColIndex !== null) {
            applyLiveColumnWidth(selectedColIndex, val);
        } else {
            baseColumns.forEach(c => applyLiveColumnWidth(c.idx, val));
        }
        return;
    }

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
    columnOrder = state.order || [0, 1, 2, 3, 16, 17, 4, 5, 6];

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
        order: [0, 1, 2, 3, 17, 4, 5, 6, 7, 8, 9, 10, 11, 12],
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
        } else 
        if (inv.paymentMethod !== 'credit') {
            const pms = inv.paymentMethods || [inv.paymentMethod];
            let cashAmt = parseFloat(inv.cashAmount) || 0;
            let bankAmt = parseFloat(inv.bankTransfers ? inv.bankTransfers.reduce((s, b) => s + (parseFloat(b.amount) || 0), 0) : inv.bankAmount) || 0;
            let chequeAmt = parseFloat(inv.cheques ? inv.cheques.reduce((s, c) => s + (parseFloat(c.amount) || 0), 0) : inv.chequeAmount) || 0;
            
            const totalForFallback = typeof totalAmt !== 'undefined' ? totalAmt.fc : (typeof amtFc !== 'undefined' ? amtFc : 0);
            
            if (totalForFallback > 0 && cashAmt <= 0 && bankAmt <= 0 && chequeAmt <= 0) {
                if (pms.includes('bank')) bankAmt = totalForFallback;
                else if (pms.includes('cheque')) chequeAmt = totalForFallback;
                else cashAmt = totalForFallback;
            }

            // Cash
            if (cashAmt > 0) {
                const cLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? cashAmt : cashAmt * effRate;
                const cFc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? (cashAmt / effRate) : cashAmt;
                const paymentAcct = inv.paymentAccountId || 'safe-001';
                
                let safeName = 'الخزينة';
                try {
                    const allSafes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
                    const sObj = allSafes.find(s => String(s.id) === String(paymentAcct));
                    if (sObj) safeName = sObj.name;
                } catch(e) {}
                
                const stmt = isReturn ? 'سداد مرتجع نقدي' : 'استلام نقدية';
                const addNote = isReturn ? `صرف مبالغ المرتجع من [${safeName}]` : `سداد نقدي مبيعات (${refNo})`;

                txns.push({
                    date: inv.date, time: time, ref: refNo + '-SAL-PAID-C', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    account: paymentAcct, counterAccount: customerAcct,
                    statement: stmt, additionalNote: addNote,
                    transferNo: safeName,
                    debit: isReturn ? 0 : cLoc, credit: isReturn ? cLoc : 0,
                    debitForeign: isReturn ? 0 : cFc, creditForeign: isReturn ? cFc : 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-SAL-PAID-C', type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                    account: customerAcct, counterAccount: paymentAcct,
                    statement: stmt, additionalNote: addNote,
                    transferNo: safeName,
                    debit: isReturn ? cLoc : 0, credit: isReturn ? 0 : cLoc,
                    debitForeign: isReturn ? cFc : 0, creditForeign: isReturn ? 0 : cFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            }

            // Bank
            if (bankAmt > 0 && inv.bankTransfers && inv.bankTransfers.length > 0) {
                let allBanks = [];
                try { allBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]'); } catch(e) {}
                inv.bankTransfers.forEach((bt, idx) => {
                    const btFc = parseFloat(bt.amount) || 0;
                    if (btFc <= 0) return;
                    const btLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? btFc : btFc * effRate;
                    
                    let bankName = 'البنك';
                    const bObj = allBanks.find(b => String(b.id) === String(bt.bankAccountId));
                    if (bObj) bankName = bObj.name;
                    
                    const stmt = isReturn ? 'سداد مرتجع تحويل بنكي' : 'استلام تحويل بنكي';
                    const addNote = `سداد تحويلة مبيعات (${refNo})`;
                    const trNum = bt.reference || bt.transferNo || '';

                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-SAL-PAID-B' + idx, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        account: bt.bankAccountId || 'bank-001', counterAccount: customerAcct,
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: isReturn ? 0 : btLoc, credit: isReturn ? btLoc : 0,
                        debitForeign: isReturn ? 0 : btFc, creditForeign: isReturn ? btFc : 0,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-SAL-PAID-B' + idx, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        account: customerAcct, counterAccount: bt.bankAccountId || 'bank-001',
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: isReturn ? btLoc : 0, credit: isReturn ? 0 : btLoc,
                        debitForeign: isReturn ? btFc : 0, creditForeign: isReturn ? 0 : btFc,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                });
            }

            // Cheque
            if (chequeAmt > 0 && inv.cheques && inv.cheques.length > 0) {
                let allBanks = [];
                try { allBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]'); } catch(e) {}
                inv.cheques.forEach((ch, idx) => {
                    const chFc = parseFloat(ch.amount) || 0;
                    if (chFc <= 0) return;
                    const chLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? chFc : chFc * effRate;
                    
                    let bankName = ch.bankName || 'البنك';
                    if (!ch.bankName || ch.bankName === 'بنك غير محدد') {
                        const bObj = allBanks.find(b => String(b.id) === String(ch.bankAccountId));
                        if (bObj) bankName = bObj.name;
                    }
                    
                    const stmt = isReturn ? 'سداد مرتجع شيك' : 'استلام شيك';
                    const addNote = `سداد شيك مبيعات (${refNo})`;
                    const trNum = `شيك (${ch.chequeNo || ''})`;

                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-SAL-PAID-Q' + idx, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        account: ch.bankAccountId || 'cheques-receivable', counterAccount: customerAcct,
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: isReturn ? 0 : chLoc, credit: isReturn ? chLoc : 0,
                        debitForeign: isReturn ? 0 : chFc, creditForeign: isReturn ? chFc : 0,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-SAL-PAID-Q' + idx, type: isReturn ? 'payment' : 'receipt', typeLabel: isReturn ? 'سداد مرتجع' : 'سداد فاتورة',
                        account: customerAcct, counterAccount: ch.bankAccountId || 'cheques-receivable',
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: isReturn ? chLoc : 0, credit: isReturn ? 0 : chLoc,
                        debitForeign: isReturn ? chFc : 0, creditForeign: isReturn ? 0 : chFc,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                });
            }
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
            const pms = inv.paymentMethods || [inv.paymentMethod];
            let cashAmt = parseFloat(inv.cashAmount) || 0;
            let bankAmt = parseFloat(inv.bankTransfers ? inv.bankTransfers.reduce((s, b) => s + (parseFloat(b.amount) || 0), 0) : inv.bankAmount) || 0;
            let chequeAmt = parseFloat(inv.cheques ? inv.cheques.reduce((s, c) => s + (parseFloat(c.amount) || 0), 0) : inv.chequeAmount) || 0;

            const totalForFallback = typeof totalAmt !== 'undefined' ? totalAmt.fc : (typeof amtFc !== 'undefined' ? amtFc : 0);
            
            if (totalForFallback > 0 && cashAmt <= 0 && bankAmt <= 0 && chequeAmt <= 0) {
                if (pms.includes('bank')) bankAmt = totalForFallback;
                else if (pms.includes('cheque')) chequeAmt = totalForFallback;
                else cashAmt = totalForFallback;
            }

            // Cash Portion
            if (cashAmt > 0) {
                const cLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? cashAmt : cashAmt * effRate;
                const cFc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? (cashAmt / effRate) : cashAmt;
                const paymentAcct = inv.paymentAccountId || 'safe-001';
                
                let safeName = 'الخزينة';
                try {
                    const allSafes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
                    const sObj = allSafes.find(s => String(s.id) === String(paymentAcct));
                    if (sObj) safeName = sObj.name;
                } catch(e) {}
                
                const stmt = (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد نقدية' : 'صرف نقدية';
                const addNote = (typeof isReturn !== 'undefined' && isReturn) ? `استرداد نقدي مع مرتجع الفاتورة من [${safeName}]` : `سداد نقدي مشتريات (${refNo})`;

                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PUR-PAID-C', type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                    account: paymentAcct, counterAccount: supplierAcct,
                    statement: stmt, additionalNote: addNote,
                    transferNo: safeName,
                    debit: (typeof isReturn !== 'undefined' && isReturn) ? cLoc : 0, credit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : cLoc,
                    debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? cFc : 0, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : cFc,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
                txns.push({
                    date: inv.date, time: time, ref: refNo + '-PUR-PAID-C', type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                    account: supplierAcct, counterAccount: paymentAcct,
                    statement: stmt, additionalNote: addNote,
                    transferNo: safeName,
                    debit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : cLoc, credit: (typeof isReturn !== 'undefined' && isReturn) ? cLoc : 0,
                    debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : cFc, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? cFc : 0,
                    costCenter, currency, exchangeRate: effRate, isPosted
                });
            }

            // Bank Transfers
            if (bankAmt > 0 && inv.bankTransfers && inv.bankTransfers.length > 0) {
                let allBanks = [];
                try { allBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]'); } catch(e) {}
                inv.bankTransfers.forEach((bt, idx) => {
                    const btFc = parseFloat(bt.amount) || 0;
                    if (btFc <= 0) return;
                    const btLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? btFc : btFc * effRate;
                    
                    let bankName = 'البنك';
                    const bObj = allBanks.find(b => String(b.id) === String(bt.bankAccountId));
                    if (bObj) bankName = bObj.name;
                    
                    const stmt = (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد تحويل بنكي' : 'صرف تحويل بنكي';
                    const addNote = `سداد تحويلة مشتريات (${refNo})`;
                    const trNum = bt.reference || bt.transferNo || '';

                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-PUR-PAID-B' + idx, type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                        account: bt.bankAccountId || 'bank-001', counterAccount: supplierAcct,
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: (typeof isReturn !== 'undefined' && isReturn) ? btLoc : 0, credit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : btLoc,
                        debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? btFc : 0, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : btFc,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-PUR-PAID-B' + idx, type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                        account: supplierAcct, counterAccount: bt.bankAccountId || 'bank-001',
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : btLoc, credit: (typeof isReturn !== 'undefined' && isReturn) ? btLoc : 0,
                        debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : btFc, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? btFc : 0,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                });
            }

            // Cheques
            if (chequeAmt > 0 && inv.cheques && inv.cheques.length > 0) {
                let allBanks = [];
                try { allBanks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]'); } catch(e) {}
                inv.cheques.forEach((ch, idx) => {
                    const chFc = parseFloat(ch.amount) || 0;
                    if (chFc <= 0) return;
                    const chLoc = typeof isBaseSDG !== 'undefined' && isBaseSDG ? chFc : chFc * effRate;
                    
                    let bankName = ch.bankName || 'البنك';
                    if (!ch.bankName || ch.bankName === 'بنك غير محدد') {
                        const bObj = allBanks.find(b => String(b.id) === String(ch.bankAccountId));
                        if (bObj) bankName = bObj.name;
                    }
                    
                    const stmt = (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد شيك' : 'صرف شيك';
                    const addNote = `سداد شيك مشتريات (${refNo})`;
                    const trNum = `شيك (${ch.chequeNo || ''})`;

                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-PUR-PAID-Q' + idx, type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                        account: ch.bankAccountId || 'cheques-payable', counterAccount: supplierAcct,
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: (typeof isReturn !== 'undefined' && isReturn) ? chLoc : 0, credit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : chLoc,
                        debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? chFc : 0, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : chFc,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                    txns.push({
                        date: inv.date, time: time, ref: refNo + '-PUR-PAID-Q' + idx, type: (typeof isReturn !== 'undefined' && isReturn) ? 'receipt' : 'payment', typeLabel: (typeof isReturn !== 'undefined' && isReturn) ? 'استرداد مشتريات' : 'سداد مشتريات',
                        account: supplierAcct, counterAccount: ch.bankAccountId || 'cheques-payable',
                        statement: stmt, additionalNote: addNote,
                        transferNo: trNum, bankName: bankName,
                        debit: (typeof isReturn !== 'undefined' && isReturn) ? 0 : chLoc, credit: (typeof isReturn !== 'undefined' && isReturn) ? chLoc : 0,
                        debitForeign: (typeof isReturn !== 'undefined' && isReturn) ? 0 : chFc, creditForeign: (typeof isReturn !== 'undefined' && isReturn) ? chFc : 0,
                        costCenter, currency, exchangeRate: effRate, isPosted
                    });
                });
            }
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
            if (!t.checkNo && !t.transferNo) {
                if (source.cheques && source.cheques.length > 0) {
                    let ch = source.cheques.find(c => matchAmt(c.amount));
                    if (ch) t.checkNo = (ch.checkNo || ch.chequeNo || '') + (ch.checkBranch || ch.chequeBank ? ` (${ch.checkBranch || ch.chequeBank})` : '');
                } else if (!source.bankTransfers || source.bankTransfers.length === 0) {
                    if (source.checkNo || source.checkNumber || source.chequeNo) {
                        let cNo = source.checkNo || source.checkNumber || source.chequeNo;
                        let cBank = source.checkBranch || source.chequeBank || '';
                        t.checkNo = cBank ? `${cNo} (${cBank})` : cNo;
                    }
                }
            }

            // Process bank transfers
            if (!t.transferNo && !t.checkNo) {
                if (source.bankTransfers && source.bankTransfers.length > 0) {
                    let bt = source.bankTransfers.find(b => matchAmt(b.amount));
                    if (bt) {
                        t.transferNo = (bt.transferNo || '');
                        t.bankName = (bt.bankName || bt.note || '');
                    }
                } else if (!source.cheques || source.cheques.length === 0) {
                    if (source.transferNo || source.mobileTransferNo) {
                        let tr = source.transferNo || source.mobileTransferNo;
                        let note = source.mobileNote || source.bankName || source.note || '';
                        t.transferNo = tr;
                        t.bankName = note;
                    }
                }
            }


            // Process multi-items
            if (source.items && source.items.length > 0) {
                let item = source.items.find(it => it.account === t.account || it.account === t.counterAccount);
                if (item) {
                    if (!t.checkNo && !t.transferNo) {
                        if (item.cheques && item.cheques.length > 0) {
                            let ch = item.cheques.find(c => matchAmt(c.amount));
                            if (ch) t.checkNo = (ch.chequeNo || ch.checkNo || '') + (ch.chequeBank || ch.checkBranch ? ` (${ch.chequeBank || ch.checkBranch})` : '');
                        } else if (!item.bankTransfers || item.bankTransfers.length === 0) {
                            if (item.checkNo || item.chequeNo) {
                                t.checkNo = (item.checkNo || item.chequeNo) + (item.chequeBank || item.checkBranch ? ` (${item.chequeBank || item.checkBranch})` : '');
                            }
                        }
                    }

                    if (!t.transferNo && !t.checkNo) {
                        if (item.bankTransfers && item.bankTransfers.length > 0) {
                            let bt = item.bankTransfers.find(b => matchAmt(b.amount));
                            if (bt) {
                                t.transferNo = (bt.transferNo || '');
                                t.bankName = (bt.bankName || bt.note || '');
                            }
                        } else if (!item.cheques || item.cheques.length === 0) {
                            if (item.transferNo) {
                                t.transferNo = item.transferNo;
                                t.bankName = item.bankName || item.note || '';
                            }
                        }
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


let currentMargins = 'normal';
function setPageMargins(mode) {
    currentMargins = mode;
    if (typeof applyPaperSimulation === 'function') {
        applyPaperSimulation(null, null, mode);
    } else {
        updatePageCss();
    }
}


// ════════════════ TWO-TIER PRESET & FACTORY DEFAULTS ENGINE (ITEM LEDGER) ════════════════

const FACTORY_PRESETS_ITEM_LEDGER = [
    {
        name: '💎 افتراضي 1',
        configs: getDefaultConfigs(),
        order: [0, 1, 2, 3, 16, 17, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        theme: 'theme-gold',
        font: 'font-ultra',
        paperSize: 'A4',
        orientation: 'landscape',
        margins: 'normal',
        showLogo: true,
        showInfo: true,
        showInRibbon: true
    },
    {
        name: '⭐ افتراضي 2',
        configs: getDefaultConfigs(),
        order: [0, 1, 2, 3, 16, 17, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        theme: 'theme-classic-navy',
        font: 'font-newsons',
        paperSize: 'A4',
        orientation: 'landscape',
        margins: 'compact',
        showLogo: true,
        showInfo: true,
        showInRibbon: true
    },
    {
        name: '🎨 افتراضي 3',
        configs: getDefaultConfigs(),
        order: [0, 1, 2, 3, 16, 17, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        theme: 'theme-emerald-pro',
        font: 'font-tajawal',
        paperSize: 'A4',
        orientation: 'landscape',
        margins: 'normal',
        showLogo: true,
        showInfo: true,
        showInRibbon: true
    },
    {
        name: '🔥 افتراضي 4',
        configs: getDefaultConfigs(),
        order: [0, 1, 2, 3, 16, 17, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        theme: 'theme-modern-slate',
        font: 'font-cairo',
        paperSize: 'A4',
        orientation: 'landscape',
        margins: 'compact',
        showLogo: true,
        showInfo: true,
        showInRibbon: true
    },
    {
        name: '✨ افتراضي 5',
        configs: getDefaultConfigs(),
        order: [0, 1, 2, 3, 16, 17, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        theme: 'theme-luxury-gold',
        font: 'font-ultra',
        paperSize: 'A4',
        orientation: 'landscape',
        margins: 'normal',
        showLogo: true,
        showInfo: true,
        showInRibbon: true
    },
    {
        name: '🚀 افتراضي 6',
        configs: getDefaultConfigs(),
        order: [0, 1, 2, 3, 16, 17, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        theme: '',
        font: 'font-ultra',
        paperSize: 'A4',
        orientation: 'landscape',
        margins: 'normal',
        showLogo: true,
        showInfo: true,
        showInRibbon: true
    }
];

function getSavedPresets() {
    let raw = localStorage.getItem(PRESETS_STORAGE_KEY);
    if (!raw) return [];
    try {
        let list = JSON.parse(raw);
        if (Array.isArray(list)) {
            if (list.length > MAX_PRESETS_LIMIT) {
                list = list.slice(0, MAX_PRESETS_LIMIT);
                localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(list));
            }
            return list;
        }
        return [];
    } catch(e) {
        return [];
    }
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

function syncPresetsToDB(reportKey, presets) {
    if (!Array.isArray(presets)) return;
    try {
        const token = localStorage.getItem('erp_token') || '';
        const headers = { 'Content-Type': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        fetch('api_index.php/report-layout', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ reportKey: reportKey, layout: presets })
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

function initPresets() {
    let raw = localStorage.getItem(PRESETS_STORAGE_KEY);
    let list = [];
    if (raw) {
        try {
            list = JSON.parse(raw);
        } catch(e) {}
    }
    if (!Array.isArray(list) || list.length === 0) {
        list = JSON.parse(JSON.stringify(FACTORY_PRESETS_ITEM_LEDGER));
        savePresets(list);
    }
    
    renderPresetButtons();
    renderPresetsManagementList();
    if (list.length > 0) {
        loadPreset(currentPresetIndex || 0);
    }

    loadPresetsFromDB(PRESETS_STORAGE_KEY, PRESETS_STORAGE_KEY, function(dbLayout) {
        if (Array.isArray(dbLayout) && dbLayout.length > 0) {
            renderPresetButtons();
            renderPresetsManagementList();
            if (typeof currentPresetIndex !== 'undefined') loadPreset(currentPresetIndex);
        }
    });
}

function loadPreset(idx) {
    const presets = getSavedPresets();
    if (!presets || !presets[idx]) return;
    currentPresetIndex = idx;
    const p = presets[idx];
    applyDesignState(p);
    renderPresetButtons();
    renderPresetsManagementList();
}

function applyPreset(idx) {
    loadPreset(idx);
    const presets = getSavedPresets();
    const name = presets[idx] ? presets[idx].name : '';
    if (typeof showToast === 'function') {
        showToast('✨ تم تطبيق القالب [' + name + '] بنجاح!', 'info');
    }
}

function saveCurrentPreset(targetIdx) {
    const idx = (typeof targetIdx === 'number') ? targetIdx : currentPresetIndex;
    const presets = getSavedPresets();
    if (!presets || !presets[idx]) return;
    const p = presets[idx];

    p.configs = JSON.parse(JSON.stringify(columnConfigs));
    p.order = [...columnOrder];
    p.theme = currentTheme;
    p.font = currentFont;
    p.paperSize = window.currentPaperSize || currentPaperSize || 'A4';
    p.orientation = window.currentOrientation || currentOrientation || 'landscape';
    p.margins = window.currentMargins || currentMargins || 'normal';
    p.showLogo = showHeaderLogo;
    p.showInfo = showHeaderInfo;
    
    const uChk = document.getElementById('hdr-show-user-chk');
    const uPos = document.getElementById('meta-user-pos-select');
    const dChk = document.getElementById('hdr-show-datetime-chk');
    const pChk = document.getElementById('hdr-show-pagenums-chk');
    
    p.showUser = uChk ? uChk.checked : true;
    p.userPos = uPos ? uPos.value : 'bottom-right';
    p.showDateTime = dChk ? dChk.checked : true;
    p.showPageNums = pChk ? pChk.checked : true;
    p.updatedAt = new Date().toISOString();
    p.isCustomized = true;

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

    if (typeof showToast === 'function') {
        showToast('💾 تم حفظ التعديلات في "' + p.name + '" بنجاح!', 'success');
    }
}

function resetPresetToFactory(idx) {
    const factory = FACTORY_PRESETS_ITEM_LEDGER[idx] || FACTORY_PRESETS_ITEM_LEDGER[0];
    const presets = getSavedPresets();
    if (!presets || !presets[idx]) return;
    
    presets[idx] = JSON.parse(JSON.stringify(factory));
    savePresets(presets);
    
    if (idx === currentPresetIndex) {
        loadPreset(idx);
    } else {
        renderPresetButtons();
        renderPresetsManagementList();
    }
    if (typeof showToast === 'function') {
        showToast('🏭 تم استرجاع ضبط المصنع الأصلي لـ "' + factory.name + '" بنجاح!', 'info');
    }
}

function resetAllPresetsToFactory() {
    if (!confirm('⚠️ هل أنت متأكد من استرجاع كافة القوالب والتصميمات إلى ضبط المصنع الأصلي للنظام؟')) return;
    const presets = JSON.parse(JSON.stringify(FACTORY_PRESETS_ITEM_LEDGER));
    savePresets(presets);
    currentPresetIndex = 0;
    loadPreset(0);
    renderPresetButtons();
    renderPresetsManagementList();
    if (typeof showToast === 'function') {
        showToast('🏭 تم استرجاع كافة القوالب لضبط المصنع الأصلي بنجاح!', 'success');
    }
}

function renderPresetButtons() {
    const container = document.getElementById('presets-container');
    if (!container) return;
    container.innerHTML = '';
    const presets = getSavedPresets();
    
    presets.forEach((p, idx) => {
        if (p.showInRibbon === false) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'preset-btn' + (idx === currentPresetIndex ? ' active' : '');
        btn.innerHTML = p.name + (idx === currentPresetIndex ? ' 🟢' : '');
        btn.title = 'تطبيق ' + p.name;
        btn.onclick = function(e) {
            if (e) e.preventDefault();
            loadPreset(idx);
            if (typeof showToast === 'function') showToast('✨ تم تطبيق [' + p.name + ']', 'info');
        };
        container.appendChild(btn);
    });
}

function renderPresetsManagementList() {
    const list = document.getElementById('presets-management-list');
    if (!list) return;
    list.innerHTML = '';
    const presets = getSavedPresets();

    presets.forEach((p, idx) => {
        const card = document.createElement('div');
        const isCurrent = idx === currentPresetIndex;
        card.className = 'sr-preset-card' + (isCurrent ? ' active' : '');
        
        const hasFactory = idx < FACTORY_PRESETS_ITEM_LEDGER.length;
        const visibleCols = (p.configs ? Object.values(p.configs) : []).filter(c => c && c.visible !== false).length;
        const themeLabel = p.theme ? p.theme.replace('theme-', '') : 'افتراضي';
        const orientLabel = p.orientation === 'portrait' ? 'طولي' : 'عرضي';

        card.innerHTML = `
            <div class="sr-preset-header">
                <span style="font-weight:900; font-size:11.5px; background:${isCurrent ? '#0284c7' : '#64748b'}; color:#fff; padding:1px 5px; border-radius:3px;">${idx + 1}</span>
                <input type="text" class="sr-preset-name-input" value="${p.name}" onchange="renamePreset(${idx}, this.value)" style="font-size:11px; padding:1px 4px;">
                ${isCurrent ? '<span style="color:#0284c7; font-weight:900; font-size:10.5px;">🟢 النشط</span>' : ''}
            </div>
            
            <div class="sr-preset-meta" style="gap:2px; margin:2px 0;">
                <span class="sr-preset-badge" style="font-size:9.5px; padding:1px 4px;">🎨 ${themeLabel}</span>
                <span class="sr-preset-badge" style="font-size:9.5px; padding:1px 4px;">📄 ${p.paperSize || 'A4'} ${orientLabel}</span>
            </div>
            <label style="display:flex; align-items:center; gap:5px; font-size:10.5px; font-weight:800; color:#334155; cursor:pointer; background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; padding:2px 6px; margin:2px 0;" title="إظهار أو إخفاء هذا الزر من الشريط العلوي">
                <input type="checkbox" ${p.showInRibbon !== false ? 'checked' : ''} onchange="togglePresetRibbonVisibility(${idx}, this.checked)" style="width:13px; height:13px; accent-color:#0284c7; cursor:pointer;">
                <span>👁️ إظهار في الشريط</span>
            </label>

            <div class="sr-preset-actions" style="gap:2px; margin-top:3px; padding-top:3px;">
                <button type="button" class="sr-preset-btn sr-preset-btn-apply" onclick="loadPreset(${idx}); switchStudioTab('cols');" title="تطبيق هذا القالب" style="font-size:10px; padding:2px 4px;">
                    ✨ تطبيق
                </button>
                <button type="button" class="sr-preset-btn sr-preset-btn-save" onclick="saveCurrentPreset(${idx})" title="حفظ التصميم المفتوح حالياً في هذا القالب" style="font-size:10px; padding:2px 4px;">
                    💾 حفظ
                </button>
                ${hasFactory ? `
                <button type="button" class="sr-preset-btn sr-preset-btn-reset" onclick="resetPresetToFactory(${idx})" title="استرجاع ضبط المصنع الأصلي لهذا القالب" style="font-size:10px; padding:2px 4px;">
                    🔄 مصنع
                </button>` : `
                <button type="button" class="sr-preset-btn sr-preset-btn-del" onclick="deletePreset(${idx})" title="حذف هذا القالب" style="font-size:10px; padding:2px 4px;">
                    🗑️ حذف
                </button>`}
            </div>
        `;
        list.appendChild(card);
    });
}

function renamePreset(idx, newName) {
    const presets = getSavedPresets();
    if (!presets[idx]) return;
    if (newName && newName.trim()) {
        presets[idx].name = newName.trim();
        savePresets(presets);
        renderPresetButtons();
        renderPresetsManagementList();
    }
}

function deletePreset(idx) {
    const presets = getSavedPresets();
    if (!presets[idx]) return;
    if (confirm('⚠️ هل أنت متأكد من حذف التخصيص "' + presets[idx].name + '"؟')) {
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
    }
}

function addNewPresetPrompt() {
    const presets = getSavedPresets();
    if (presets.length >= MAX_PRESETS_LIMIT) {
        alert('⚠️ لقد وصلت إلى الحد الأقصى للتخصيصات (' + MAX_PRESETS_LIMIT + ' تخصيصات كحد أقصى).');
        return;
    }

    const name = prompt("أدخل اسماً للتخصيص الجديد:", "🎨 تصميم " + (presets.length + 1));
    if (name && name.trim()) {
        presets.push({
            name: name.trim(),
            configs: JSON.parse(JSON.stringify(columnConfigs)),
            order: [...columnOrder],
            theme: currentTheme,
            font: currentFont,
            paperSize: window.currentPaperSize || currentPaperSize || 'A4',
            orientation: window.currentOrientation || currentOrientation || 'landscape',
            margins: window.currentMargins || currentMargins || 'normal',
            showLogo: showHeaderLogo,
            showInfo: showHeaderInfo,
            showUser: document.getElementById('hdr-show-user-chk')?.checked ?? true,
            userPos: document.getElementById('meta-user-pos-select')?.value || 'bottom-right',
            showDateTime: document.getElementById('hdr-show-datetime-chk')?.checked ?? true,
            showPageNums: document.getElementById('hdr-show-pagenums-chk')?.checked ?? true,
            showInRibbon: true
        });
        savePresets(presets);
        currentPresetIndex = presets.length - 1;
        renderPresetButtons();
        renderPresetsManagementList();
        loadPreset(currentPresetIndex);
        if (typeof showToast === 'function') showToast('➕ تمت إضافة التخصيص الجديد بنجاح!', 'success');
    }
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

let currentTableWidth = 100;
function setTableWidth(percent) {
    currentTableWidth = parseInt(percent) || 100;
    const tbl = document.getElementById('preview-table') || document.querySelector('.excel-table') || document.querySelector('.report-table') || document.querySelector('table');
    const rng = document.getElementById('table-width-range');
    const lbl = document.getElementById('sr-tbl-w-lbl');
    if (rng) rng.value = currentTableWidth;
    if (lbl) lbl.textContent = currentTableWidth + '%';
    if (tbl) {
        tbl.style.width = currentTableWidth + '%';
        tbl.style.maxWidth = currentTableWidth + '%';
        if (currentTableWidth < 100) {
            tbl.style.margin = '0 auto';
        } else {
            tbl.style.margin = '';
        }
    }
}

let currentRowHeight = 'normal';
function setRowHeight(mode) {
    currentRowHeight = mode;
    const sel = document.getElementById('row-height-select');
    if (sel && sel.value !== mode) sel.value = mode;
    const tbl = document.getElementById('preview-table') || document.querySelector('.excel-table') || document.querySelector('.report-table') || document.querySelector('table');
    if (!tbl) return;
    let pad = '6px 10px';
    if (mode === 'compact') pad = '3px 6px';
    else if (mode === 'normal') pad = '6px 10px';
    else if (mode === 'comfortable') pad = '12px 14px';
    else if (mode === 'spacious') pad = '18px 16px';
    
    const cells = tbl.querySelectorAll('th, td');
    cells.forEach(c => { c.style.padding = pad; });
}

window.saveCurrentPreset = saveCurrentPreset;
window.applyPreset = applyPreset;
window.loadPreset = loadPreset;
window.resetPresetToFactory = resetPresetToFactory;
window.resetAllPresetsToFactory = resetAllPresetsToFactory;
window.addNewPresetPrompt = addNewPresetPrompt;
window.renderPresetsManagementList = renderPresetsManagementList;
window.setTableWidth = setTableWidth;
window.setRowHeight = setRowHeight;
