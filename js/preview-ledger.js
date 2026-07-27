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
    { idx: 5, defaultLabel: "مدين (محلي)", field: "debit", isNum: true },
    { idx: 6, defaultLabel: "دائن (محلي)", field: "credit", isNum: true },
    { idx: 7, defaultLabel: "مدين (أجنبي)", field: "debitForeign", isNum: true },
    { idx: 8, defaultLabel: "دائن (أجنبي)", field: "creditForeign", isNum: true },
    { idx: 9, defaultLabel: "الرصيد التراكمي", field: "balance", isNum: true },
    { idx: 10, defaultLabel: "ملاحظات", field: "additionalNote", isNum: false }
];

function getRawFieldValue(row, field) {
    if (!row) return '';
    if (field === 'date') return row.date || row.created_at || row.time || '';
    if (field === 'ref') return row.ref || row.doc_no || row.voucher_no || row.id || '';
    if (field === 'statement') return row.statement || row.description || row.desc || row.notes || '';
    if (field === 'counterAccount') return row.counterAccount || row.accountName || row.account || '-';
    if (field === 'typeLabel') return row.typeLabel || row.type || '-';
    if (field === 'debit') return parseFloat(row.debit || row.dr || 0);
    if (field === 'credit') return parseFloat(row.credit || row.cr || 0);
    if (field === 'debitForeign') return parseFloat(row.debitForeign || row.dr_foreign || 0);
    if (field === 'creditForeign') return parseFloat(row.creditForeign || row.cr_foreign || 0);
    if (field === 'balance') return parseFloat(row.balance !== undefined ? row.balance : ((row.debit || 0) - (row.credit || 0)));
    if (field === 'additionalNote') return row.additionalNote || row.notes || row.costCenter || '';
    return row[field] !== undefined ? row[field] : '';
}

let columnOrder = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
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
        
        const dateLbl = document.getElementById('report-date-lbl');
        if (dateLbl) {
            const today = new Date();
            dateLbl.innerText = 'تاريخ التقرير: ' + today.toLocaleDateString('ar-EG');
        }
    } catch(e) { console.error('Error loading company header', e); }
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

    // Account Filter
    let intrinsicBal = 0;
    if (accVal !== 'ALL') {
        const cleanFilter = accVal.replace(/^(cust|supp|bank|safe|exp)-/, '');
        txns = txns.filter(t => {
            if (String(t.account) === String(accVal)) return true;
            if (String(t.account) === cleanFilter) return true;
            return false;
        });

        // Compute intrinsic balance from ACCOUNTS
        if (typeof ALL_ACCOUNTS !== 'undefined' && Array.isArray(ALL_ACCOUNTS)) {
            const found = ALL_ACCOUNTS.find(a => a.name === accVal || a.id === accVal);
            if (found) {
                const bal = parseFloat(found.opening_balance || found.openingBalance || 0);
                if (found.balance_type === 'credit' || found.balanceType === 'credit') {
                    intrinsicBal = -bal;
                } else {
                    intrinsicBal = bal;
                }
            }
        }
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

    currentTxns.sort((a, b) => {
        const da = a.date + ' ' + (a.time || '00:00');
        const db = b.date + ' ' + (b.time || '00:00');
        return da.localeCompare(db);
    });

    let runningBal = 0;

    // Handle Opening Balance
    if (accVal !== 'ALL') {
        let hasOpening = (intrinsicBal !== 0);
        let hasPrior = (currentDateMode !== 'all' && priorTxns.length > 0 && fromDate);
        
        let priorSum = 0;
        priorTxns.forEach(t => { priorSum += t.debit; priorSum -= t.credit; });

        let opDate = '—';
        if (txns.length > 0) opDate = txns.reduce((min, t) => t.date < min ? t.date : min, txns[0].date);

        if (hasOpening || currentDateMode === 'all' || !hasPrior) {
            let opDebit = intrinsicBal > 0 ? intrinsicBal : 0;
            let opCredit = intrinsicBal < 0 ? Math.abs(intrinsicBal) : 0;
            
            currentTxns.unshift({
                date: opDate, time: '', ref: 'OPENING', type: 'opening', typeLabel: 'افتتاحي',
                account: accVal, counterAccount: '-', statement: 'رصيد افتتاحي', additionalNote: 'رصيد افتتاحي',
                debit: opDebit, credit: opCredit, costCenter: '', 
                currency: 'SDG', debitForeign: 0, creditForeign: 0, balance: 0,
                isOpening: true
            });
        }
        
        if (hasPrior && priorSum !== 0) {
            let preDebit = priorSum > 0 ? priorSum : 0;
            let preCredit = priorSum < 0 ? Math.abs(priorSum) : 0;
            
            currentTxns.unshift({
                date: fromDate, time: '', ref: 'PRE-PERIOD', type: 'opening', typeLabel: 'افتتاحي',
                account: accVal, counterAccount: '-', statement: 'رصيد ما قبل الفترة', additionalNote: 'رصيد ما قبل الفترة',
                debit: preDebit, credit: preCredit, costCenter: '', 
                currency: 'SDG', debitForeign: 0, creditForeign: 0, balance: 0,
                isOpening: true
            });
        }
    }

    currentTxns.forEach(t => {
        t.debit = t.debit || 0;
        t.credit = t.credit || 0;
        runningBal += t.debit;
        runningBal -= t.credit;
        t.balance = runningBal;
    });

    return currentTxns;
}

document.addEventListener('DOMContentLoaded', async () => {
    loadCompanyHeader();
    
    let attempts = 0;
    const pollTimer = setInterval(() => {
        attempts++;
        loadCompanyHeader();
        if (attempts > 15) clearInterval(pollTimer);
    }, 300);

    window.addEventListener('DBReady', loadCompanyHeader);

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
                const subTitle = document.getElementById('hdr-c-subtitle') || document.querySelector('.company-header h2') || document.querySelector('h2');
                if (subTitle) {
                    subTitle.innerText = 'تقرير دفتر الأستاذ العام - ' + cfg.accountName;
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
            const note = String(getRawFieldValue(row, 'additionalNote')).toLowerCase();
            return stmt.includes(q) || ref.includes(q) || counter.includes(q) || type.includes(q) || note.includes(q);
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
                order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                order: [0, 1, 2, 3, 6, 7, 8, 9, 4, 5, 10],
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
                order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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

function getSavedPresets() {
    let raw = localStorage.getItem(PRESETS_STORAGE_KEY);
    if (!raw) return [];
    try {
        let list = JSON.parse(raw);
        return Array.isArray(list) ? list : [];
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

function renderPresetButtons() {
    const container = document.getElementById('presets-container');
    if (!container) return;
    const presets = getSavedPresets();
    
    presets.forEach((p, idx) => {
        if (p.showInRibbon === false) return; // Skip if hidden by user
        const btn = document.createElement('button');
        btn.className = 'preset-btn' + (idx === currentPresetIndex ? ' active' : '');
        
        const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));
        let badge = '';
        if (isDefaultGroup && p.isLocked) {
            badge = ' 🔒';
        } else if (!isDefaultGroup) {
            badge = ' 🔄';
        }

        btn.innerText = p.name + badge;
        btn.title = isDefaultGroup 
            ? (p.isLocked ? 'ضبط مصنع نهائي محمي 🔒' : 'اضغط مطولاً (8 ثوانٍ) للحفظ كضبط مصنع نهائي 🔒')
            : 'اضغط مطولاً (8 ثوانٍ) لحفظ وتحديث النموذج 🔄';

        bindPresetButtonLongPress(btn, idx);
        container.appendChild(btn);
    });
}

let currentPressingBtn = null;

function bindPresetButtonLongPress(btn, idx) {
    let pressStartTime = 0;

    const startPress = (e) => {
        if (e.type === 'mousedown' && e.button !== 0) return;
        pressStartTime = Date.now();
        isLongPressTriggered = false;
        currentPressingBtn = btn;
        btn.classList.add('is-pressing');
        startLongPressCount(idx, btn);
    };

    const cancelPress = () => {
        btn.classList.remove('is-pressing');
        cancelLongPressCount();
    };

    const endPress = (e) => {
        btn.classList.remove('is-pressing');
        const duration = Date.now() - pressStartTime;
        const wasTriggered = isLongPressTriggered;
        cancelLongPressCount();

        if (wasTriggered) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            return false;
        }

        if (pressStartTime > 0 && duration < 7800) {
            loadPreset(idx);
        }
    };

    btn.addEventListener('mousedown', startPress);
    btn.addEventListener('mouseup', endPress);
    btn.addEventListener('mouseleave', cancelPress);

    btn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        startPress(e);
    }, { passive: false });
    btn.addEventListener('touchend', (e) => {
        e.preventDefault();
        endPress(e);
    });
    btn.addEventListener('touchcancel', cancelPress);

    btn.oncontextmenu = (e) => {
        e.preventDefault();
        return false;
    };
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
        p.isLocked = false;
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
    
    if (preset) {
        columnConfigs = preset.configs || getDefaultConfigs();
        columnOrder = preset.order || [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        currentTheme = preset.theme || '';
        currentFont = preset.font || 'font-newsons';
        currentPaperSize = preset.paperSize || 'A4';
        currentOrientation = preset.orientation || 'landscape';
        showHeaderLogo = preset.showLogo !== undefined ? preset.showLogo : true;
        showHeaderInfo = preset.showInfo !== undefined ? preset.showInfo : true;
    } else {
        columnConfigs = getDefaultConfigs();
        columnOrder = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
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

    savePresets(presets);
    renderPresetButtons();
    if (typeof renderPresetsManagementList === 'function') renderPresetsManagementList();

    try {
        if (window.DB_STORE && window.getTenantKey) {
            const tk = window.getTenantKey(PRESETS_STORAGE_KEY);
            window.DB_STORE[tk] = JSON.stringify(presets);
        }
        if (typeof localforage !== 'undefined') {
            localforage.setItem(PRESETS_STORAGE_KEY, presets);
        }
    } catch(e){}

    showToast(`💾 تم حفظ تصميم الجدول في "${p.name}" بنجاح!`, 'success');
}

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
                td.innerText = formatCustomNumber(colTotals[idx], conf);
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
    columnOrder = state.order || [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
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

// Long press 8 seconds on reset button: RESTORE factory reset settings
function restoreFactoryReset() {
    const raw = localStorage.getItem('balances_custom_reset_v1');
    if (raw) {
        try {
            const state = JSON.parse(raw);
            applyDesignState(state);
            showToast('🔄 تم استرجاع ضبط المصنع المحفوظ بنجاح!', 'success');
        } catch(e) {
            console.error('Error parsing custom reset state', e);
            restoreToFactoryDefault();
        }
    } else {
        restoreToFactoryDefault();
    }
}

function restoreToFactoryDefault() {
    const factoryState = {
        configs: getDefaultConfigs(),
        order: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        theme: '',
        font: 'font-newsons',
        paperSize: 'A4',
        orientation: 'landscape',
        showLogo: true,
        showInfo: true
    };
    applyDesignState(factoryState);
    showToast('🔄 تم استعادة ضبط المصنع الافتراضي!', 'info');
}


/* ─── INJECTED DATA COLLECTION ─── */
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

        const method = v.paymentMethod || 'cash';
        if (method === 'mobile' && v.bankTransfers && v.bankTransfers.length > 0) {
            v.bankTransfers.forEach(bt => {
                const amt = parseNum(bt.amount);
                const amtLoc = isBaseSDG ? amt : amt * rate;
                const amtFc = isBaseSDG ? 0 : amt;
                const stmt = bt.statement || v.statement || v.toStatement || ('تحويل بنكي - سند صرف #' + (v.voucherNo || v.id));

                // Disbursing side (Safe/Bank)
                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
                    account: v.fromAccount || 'safe-001', counterAccount: v.toAccount,
                    statement: stmt,
                    additionalNote: stmt,
                    debit: 0, credit: amtLoc, 
                    debitForeign: 0, creditForeign: amtFc,
                    costCenter, currency, exchangeRate: rate, isPosted
                });
                // Receiving side (Supplier/Customer receives from us)
                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند استلام',
                    account: v.toAccount, counterAccount: v.fromAccount || 'safe-001',
                    statement: stmt,
                    additionalNote: stmt,
                    debit: amtLoc, credit: 0, 
                    debitForeign: amtFc, creditForeign: 0,
                    costCenter, currency, exchangeRate: rate, isPosted
                });
            });
        } else if (method === 'check' && v.cheques && v.cheques.length > 0) {
            v.cheques.forEach(ch => {
                const amt = parseNum(ch.amount);
                const amtLoc = isBaseSDG ? amt : amt * rate;
                const amtFc = isBaseSDG ? 0 : amt;
                const stmt = ch.statement || v.statement || v.toStatement || ('شيك - سند صرف #' + (v.voucherNo || v.id));

                // Disbursing side (Safe/Bank)
                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند صرف',
                    account: v.fromAccount || 'safe-001', counterAccount: v.toAccount,
                    statement: stmt,
                    additionalNote: stmt,
                    debit: 0, credit: amtLoc, 
                    debitForeign: 0, creditForeign: amtFc,
                    costCenter, currency, exchangeRate: rate, isPosted
                });
                // Receiving side (Supplier/Customer receives from us)
                txns.push({
                    date: v.date, time: time, ref: v.id, type: 'payment', typeLabel: 'سند استلام',
                    account: v.toAccount, counterAccount: v.fromAccount || 'safe-001',
                    statement: stmt,
                    additionalNote: stmt,
                    debit: amtLoc, credit: 0, 
                    debitForeign: amtFc, creditForeign: 0,
                    costCenter, currency, exchangeRate: rate, isPosted
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
                exchangeRate: rate,
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
                exchangeRate: rate,
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

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                const pms = item.paymentMethods || [item.paymentMethod || 'cash'];
                
                // Cash Portion
                if (pms.includes('cash') && parseNum(item.cashAmount) > 0) {
                    const amt = parseNum(item.cashAmount);
                    const amtLoc = isBaseSDG ? amt : amt * rate;
                    const amtFc = isBaseSDG ? 0 : amt;
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
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                    });
                    
                    // Recipient/Payer Account (Credit)
                    txns.push({
                        date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                        account: item.account, counterAccount: safeAcct,
                        statement: stmt,
                        additionalNote: stmt,
                        debit: 0, credit: amtLoc, 
                        debitForeign: 0, creditForeign: amtFc,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                    });
                }

                // Bank Transfer Portion
                if (pms.includes('bank') && parseNum(item.bankAmount) > 0) {
                    const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: item.bankAmount, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                    transfers.forEach(bt => {
                        const amt = parseNum(bt.amount);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * rate;
                        const amtFc = isBaseSDG ? 0 : amt;
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
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                        });
                        
                        // Recipient/Payer Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: item.account, counterAccount: bankAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
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
                        const amtFc = isBaseSDG ? 0 : amt;
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
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                        });
                        
                        // Recipient/Payer Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-receipt', typeLabel: 'سند قبض مجمع',
                            account: item.account, counterAccount: chequeAcct,
                            statement: stmt,
                            additionalNote: stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                        });
                    });
                }
            });
        } else {
            // Fallback for old/empty items vouchers
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
                costCenter, currency, exchangeRate: rate, isPosted
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

        if (v.items && v.items.length > 0) {
            v.items.forEach(item => {
                const pms = item.paymentMethods || [item.paymentMethod || 'cash'];
                
                // Cash Portion
                if (pms.includes('cash') && parseNum(item.cashAmount) > 0) {
                    const amt = parseNum(item.cashAmount);
                    const amtLoc = isBaseSDG ? amt : amt * rate;
                    const amtFc = isBaseSDG ? 0 : amt;
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
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                    });
                    
                    // Safe Account (Credit)
                    txns.push({
                        date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                        account: safeAcct, counterAccount: item.account,
                        statement: v.description || stmt,
                        additionalNote: v.description || stmt,
                        debit: 0, credit: amtLoc, 
                        debitForeign: 0, creditForeign: amtFc,
                        costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                    });
                }

                // Bank Transfer Portion
                if (pms.includes('bank') && parseNum(item.bankAmount) > 0) {
                    const transfers = (item.bankTransfers && item.bankTransfers.length > 0) ? item.bankTransfers : [{ amount: item.bankAmount, bankName: item.bankName, transferNo: item.transferNo, statement: item.bankStatement || '' }];
                    transfers.forEach(bt => {
                        const amt = parseNum(bt.amount);
                        if (amt <= 0) return;
                        const amtLoc = isBaseSDG ? amt : amt * rate;
                        const amtFc = isBaseSDG ? 0 : amt;
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
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                        });
                        
                        // Bank Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: bankAcct, counterAccount: item.account,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
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
                        const amtFc = isBaseSDG ? 0 : amt;
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
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                        });
                        
                        // Cheque Account (Credit)
                        txns.push({
                            date: v.date, time: time, ref: v.id, type: 'multi-payment', typeLabel: 'سند صرف مجمع',
                            account: chequeAcct, counterAccount: item.account,
                            statement: v.description || stmt,
                            additionalNote: v.description || stmt,
                            debit: 0, credit: amtLoc, 
                            debitForeign: 0, creditForeign: amtFc,
                            costCenter: item.costCenter || costCenter, currency, exchangeRate: rate, isPosted
                        });
                    });
                }
            });
        } else {
            // Fallback for old/empty items vouchers
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
                costCenter, currency, exchangeRate: rate, isPosted
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
                exchangeRate: rate,
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
                exchangeRate: rate,
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
            if (source.cheques && source.cheques.length > 0) {
                let ch = source.cheques.find(c => matchAmt(c.amount)) || source.cheques[0];
                if (ch) t.checkNo = (ch.checkNo || ch.chequeNo || '') + (ch.checkBranch || ch.chequeBank ? ` (${ch.checkBranch || ch.chequeBank})` : '');
            } else if (source.checkNo || source.checkNumber || source.chequeNo) {
                let cNo = source.checkNo || source.checkNumber || source.chequeNo;
                let cBank = source.checkBranch || source.chequeBank || '';
                t.checkNo = cBank ? `${cNo} (${cBank})` : cNo;
            }

            // Process bank transfers
            if (source.bankTransfers && source.bankTransfers.length > 0) {
                let bt = source.bankTransfers.find(b => matchAmt(b.amount)) || source.bankTransfers[0];
                if (bt) t.transferNo = (bt.transferNo || '') + (bt.bankName ? ` (${bt.bankName})` : (bt.note ? ` (${bt.note})` : ''));
            } else if (source.transferNo || source.mobileTransferNo) {
                let tr = source.transferNo || source.mobileTransferNo;
                let note = source.mobileNote || source.bankName || '';
                t.transferNo = note ? `${tr} (${note})` : tr;
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
                        if (bt) t.transferNo = (bt.transferNo || '') + (bt.bankName ? ` (${bt.bankName})` : (bt.note ? ` (${bt.note})` : ''));
                    } else if (item.transferNo) {
                        t.transferNo = item.transferNo + (item.bankName || item.note ? ` (${item.bankName || item.note})` : '');
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
