// js/preview-balances.js - Pro Studio Edition

const PRESETS_STORAGE_KEY = 'balances_preview_presets_v7';
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

window.currentSelectedPartnerId = null;
window.currentSelectedPartnerType = null;
window.currentSelectedPartnerName = '';

window.goToPartnerLedgerFromPreview = function() {
    if (!window.currentSelectedPartnerId && !window.currentSelectedPartnerName) return;
    const partnerCtxMenu = document.getElementById('partnerContextMenu');
    if (partnerCtxMenu) partnerCtxMenu.style.display = 'none';

    let accId = window.currentSelectedPartnerId || '';
    if (accId && !accId.startsWith('cust-') && !accId.startsWith('supp-') && !accId.startsWith('acc-')) {
        const type = (window.currentSelectedPartnerType || 'CUST').toLowerCase();
        accId = (type === 'supp' ? 'supp-' : 'cust-') + accId;
    }

    const url = `general-ledger.php?accountId=${encodeURIComponent(accId)}&partner_id=${encodeURIComponent(window.currentSelectedPartnerId)}&name=${encodeURIComponent(window.currentSelectedPartnerName)}&autoRun=1`;
    if (window.parent && window.parent !== window) {
        window.parent.location.href = url;
    } else {
        window.location.href = url;
    }
};

document.addEventListener('click', function(e) {
    const partnerCtxMenu = document.getElementById('partnerContextMenu');
    if (partnerCtxMenu && !partnerCtxMenu.contains(e.target)) {
        partnerCtxMenu.style.display = 'none';
    }
});

// Default column definitions with exact field mapping
const baseColumns = [
    { idx: 0, defaultLabel: "اسم الحساب", field: "name", isNum: false },
    { idx: 1, defaultLabel: "رقم الهاتف", field: "phone", isNum: false },
    { idx: 2, defaultLabel: "الرقم الضريبي", field: "taxNumber", isNum: false },
    { idx: 3, defaultLabel: "العملة", field: "currency", isNum: false },
    { idx: 4, defaultLabel: "مدين (أجنبي)", field: "endingDebitForeign", isNum: true },
    { idx: 5, defaultLabel: "دائن (أجنبي)", field: "endingCreditForeign", isNum: true },
    { idx: 6, defaultLabel: "مدين (محلي)", field: "endingDebitLocal", isNum: true },
    { idx: 7, defaultLabel: "دائن (محلي)", field: "endingCreditLocal", isNum: true },
    { idx: 8, defaultLabel: "رصيد (محلي)", field: "balanceLocal", isNum: true },
    { idx: 9, defaultLabel: "رصيد (أجنبي)", field: "balanceForeign", isNum: true },
    { idx: 10, defaultLabel: "ملاحظات / حالة", field: "notes", isNum: false }
];

function getRawFieldValue(row, field) {
    if (!row) return '';
    if (field === 'name') return row.name || row.account_name || row.accountName || '';
    if (field === 'phone') return String(row.phone || '').replace(/,/g, '').replace(/\.00$/, '').trim();
    if (field === 'taxNumber') return row.taxNumber || row.tax_number || row.taxNo || '';
    if (field === 'currency') return row.currency || 'SDG';
    
    if (field === 'endingDebitForeign') return row.endingDebitForeign !== undefined ? row.endingDebitForeign : row.total_dr_foreign;
    if (field === 'endingCreditForeign') return row.endingCreditForeign !== undefined ? row.endingCreditForeign : row.total_cr_foreign;
    if (field === 'endingDebitLocal') return row.endingDebitLocal !== undefined ? row.endingDebitLocal : row.total_dr;
    if (field === 'endingCreditLocal') return row.endingCreditLocal !== undefined ? row.endingCreditLocal : row.total_cr;
    
    if (field === 'balanceLocal') {
        const dr = (row.endingDebitLocal !== undefined ? row.endingDebitLocal : row.total_dr) || 0;
        const cr = (row.endingCreditLocal !== undefined ? row.endingCreditLocal : row.total_cr) || 0;
        return dr - cr;
    }
    if (field === 'balanceForeign') {
        const drF = (row.endingDebitForeign !== undefined ? row.endingDebitForeign : row.total_dr_foreign) || 0;
        const crF = (row.endingCreditForeign !== undefined ? row.endingCreditForeign : row.total_cr_foreign) || 0;
        return drF - crF;
    }
    if (field === 'notes') return row.notes || row.status || (row.type === 'CUST' ? 'عميل' : (row.type === 'SUPP' ? 'مورد' : ''));
    
    return row[field] !== undefined ? row[field] : '';
}

let columnOrder = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
let columnConfigs = {};

function loadCompanyHeader() {
    try {
        let cfg = {};
        try { cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}'); } catch(e){}
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

document.addEventListener('DOMContentLoaded', async () => {
    loadCompanyHeader();
    
    let attempts = 0;
    const pollTimer = setInterval(() => {
        attempts++;
        loadCompanyHeader();
        if (attempts > 15) clearInterval(pollTimer);
    }, 300);

    window.addEventListener('DBReady', loadCompanyHeader);

    // Load Table Data
    const rawData = localStorage.getItem('preview_table_data');
    let parsedData = null;
    
    if (rawData) {
        try {
            parsedData = JSON.parse(rawData);
        } catch(e) { console.error('Error parsing localStorage data', e); }
    } else if (typeof localforage !== 'undefined') {
        try {
            localforage.config({
                driver: [localforage.INDEXEDDB, localforage.WEBSQL, localforage.LOCALSTORAGE],
                name: 'MagaeesApp',
                storeName: 'preview_data'
            });
            parsedData = await localforage.getItem('preview_table_data');
        } catch(e) { console.error('Error getting localforage data', e); }
    }
    
    if (Array.isArray(parsedData) && parsedData.length > 0) {
        currentData = parsedData;
    } else {
        currentData = [
            { name: 'شركة تجريبية 1', phone: '0123456789', taxNumber: '100200300', currency: 'SDG', endingDebitForeign: 100, endingCreditForeign: 0, endingDebitLocal: 5000, endingCreditLocal: 0, notes: 'نشط' },
            { name: 'مؤسسة السلام', phone: '0987654321', taxNumber: '300400500', currency: 'USD', endingDebitForeign: 0, endingCreditForeign: 1250.75, endingDebitLocal: 0, endingCreditLocal: 1250.75, notes: 'نشط' }
        ];
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
    const canPrint = isAdmin || (perms.reports?.print !== false && perms.balances_report?.print !== false && perms.print !== false);

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
            const name = String(getRawFieldValue(row, 'name')).toLowerCase();
            const phone = String(getRawFieldValue(row, 'phone')).toLowerCase();
            const tax = String(getRawFieldValue(row, 'taxNumber')).toLowerCase();
            return name.includes(q) || phone.includes(q) || tax.includes(q);
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

function getFactoryPreset1Configs() {
    let conf = getDefaultConfigs();
    conf[0] = { ...conf[0], label: "اسم الحساب", visible: true, align: 'center', fontWeight: 'bold', color: '#000000' };
    conf[1] = { ...conf[1], label: "رقم الهاتف", visible: true, align: 'center', fontWeight: 'bold', color: '#008000' };
    conf[2] = { ...conf[2], visible: false };
    conf[3] = { ...conf[3], visible: false };
    conf[4] = { ...conf[4], label: "عليه (أجنبي)", visible: true, align: 'center', fontWeight: 'bold', color: '#0044cc' };
    conf[5] = { ...conf[5], label: "له (أجنبي)", visible: true, align: 'center', fontWeight: 'bold', color: '#dc2626' };
    conf[6] = { ...conf[6], label: "عليه (محلي)", visible: true, align: 'center', fontWeight: 'bold', color: '#0044cc' };
    conf[7] = { ...conf[7], label: "له (محلي)", visible: true, align: 'center', fontWeight: 'bold', color: '#dc2626' };
    conf[8] = { ...conf[8], visible: false };
    conf[9] = { ...conf[9], visible: false };
    conf[10] = { ...conf[10], visible: false };
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
                needsMigration = false; // Already populated and migrated!
            }
        } catch(e){}
    }
    
    if (needsMigration) {
        // Migrate from most-recent legacy key first
        const allKeys = [
            'balances_preview_presets_v4',
            'balances_preview_presets_v3',
            'balances_preview_presets_v2',
            'balances_preview_presets_v1',
            'balances_preview_presets'
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

    // Populate 7 defaults with pre-configured themes & fonts if completely empty
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
                configs: getDefaultConfigs(),
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
    // Always enforce Factory Default Presets (slots 0..3) across ALL companies (old and new)
    const FACTORY_DEFAULTS_SEED = [
        {
            name: '💎 افتراضي 1',
            configs: getFactoryPreset1Configs(),
            order: [0, 6, 7, 4, 5, 1, 2, 3, 8, 9, 10],
            theme: 'theme-gold',
            font: 'font-ultra',
            paperSize: 'A4',
            orientation: 'landscape',
            showLogo: true,
            showInfo: true,
            showInRibbon: true,
            isLocked: true,
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
            isLocked: true,
            isFactoryDefault: true
        },
        {
            name: '🎨 افتراضي 3',
            configs: getDefaultConfigs(),
            order: [0, 3, 4, 5, 6, 7, 8, 9, 1, 2, 10],
            theme: 'theme-modern-slate',
            font: 'font-tajawal',
            paperSize: 'A4',
            orientation: 'landscape',
            showLogo: true,
            showInfo: true,
            showInRibbon: true,
            isLocked: true,
            isFactoryDefault: true
        },
        {
            name: '🔥 افتراضي 4',
            configs: getDefaultConfigs(),
            order: [0, 1, 3, 6, 7, 8, 9, 2, 4, 5, 10],
            theme: 'theme-luxury-gold',
            font: 'font-cairo',
            paperSize: 'A4',
            orientation: 'landscape',
            showLogo: true,
            showInfo: true,
            showInRibbon: true,
            isLocked: true,
            isFactoryDefault: true
        }
    ];

    for (let i = 0; i < 4; i++) {
        if (!list[i] || !list[i].isLocked) {
            list[i] = Object.assign({}, FACTORY_DEFAULTS_SEED[i]);
            list[i].isLocked = false;
        }
    }

    // Ensure list has at least 7 slots
    while (list.length < MAX_PRESETS_LIMIT) {
        let idx = list.length;
        list.push({
            name: `✨ نموذج ${idx - 3}`,
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
        });
    }

    // Do not lock automatically, let the user save to lock it once

    // Enforce max limit of 7
    if (list.length > MAX_PRESETS_LIMIT) {
        list = list.slice(0, MAX_PRESETS_LIMIT);
    }

    savePresets(list);
    renderPresetButtons();

    // Async sync with DB
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
    container.innerHTML = '';
    const presets = getSavedPresets();
    
    presets.forEach((p, idx) => {
        if (p.showInRibbon === false) return; // Skip if hidden by user
        const btn = document.createElement('button');
        btn.className = 'preset-btn' + (idx === currentPresetIndex ? ' active' : '');
        
        const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));

        if (isDefaultGroup && p.isLocked) {
            btn.innerText = p.name + ' 🔒';
            btn.title = '🔒 ضبط مصنع نهائي مثبت في قاعدة البيانات لا يتغير نهائياً (مُطبق على كافة المنشآت والشركات)';
            btn.onclick = (e) => {
                if (e) e.preventDefault();
                loadPreset(idx);
                if (typeof showToast === 'function') {
                    showToast('🔒 الضبط [' + p.name + '] مثبت نهائياً في قاعدة البيانات والنظام كضبط مصنع لا يتغير نهائياً ومُطبق على كافة الشركات.', 'success');
                } else if (typeof toast === 'function') {
                    toast('🔒 الضبط [' + p.name + '] مثبت نهائياً كضبط مصنع.');
                }
            };
        } else {
            btn.innerText = p.name + (isDefaultGroup ? ' 🔓' : ' 🔄');
            btn.title = 'اضغط مطولاً (8 ثوانٍ) لحفظ وتحديث نموذج التنسيق ' + (isDefaultGroup ? 'مرة واحدة 🔓' : '🔄');
            bindPresetButtonLongPress(btn, idx);
        }

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

    if (p.isLocked) {
        if (typeof showToast === 'function') {
            showToast("🔒 هذا الضبط تم حفظه نهائياً ولا يمكن تعديله.", "warning");
        } else {
            alert("🔒 هذا الضبط تم حفظه نهائياً ولا يمكن تعديله.");
        }
        return;
    }

    // Overwritable preset
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
    if (isDefaultGroup) {
        p.isLocked = true;
    } else {
        p.isLocked = false;
    }
    p.updatedAt = new Date().toISOString();
    
    const saveBtn = document.getElementById('save-settings-btn-main');
    if (saveBtn && p.isLocked) {
        saveBtn.style.display = 'none';
    }

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

    const saveBtn = document.getElementById('save-settings-btn-main');
    if (saveBtn) {
        if (preset && preset.isLocked) {
            saveBtn.style.display = 'none';
        } else {
            saveBtn.style.display = 'flex';
        }
    }

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
    
    if (p.isLocked) {
        alert("🔒 هذا الضبط تم حفظه نهائياً ولا يمكن تعديله.");
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
    p.showUser = document.getElementById('hdr-show-user-chk')?.checked;
    p.userPos = document.getElementById('meta-user-pos-select')?.value;
    p.showDateTime = document.getElementById('hdr-show-datetime-chk')?.checked;
    p.showPageNums = document.getElementById('hdr-show-pagenums-chk')?.checked;
    const isDefaultGroup = currentPresetIndex < 4 || (p.name && p.name.includes('افتراضي'));
    if (isDefaultGroup) {
        p.isLocked = true;
    } else {
        p.isLocked = false;
    }
    p.updatedAt = new Date().toISOString();

    savePresets(presets);
    
    const saveBtn = document.getElementById('save-settings-btn-main');
    if (saveBtn && p.isLocked) {
        saveBtn.style.display = 'none';
    }
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
            
            // Cell Magnification on Click (Independent Cumulative Zoom)
            td.style.cursor = 'pointer';
            td.onclick = function(e) {
                e.stopPropagation();
                this.classList.toggle('focused-num');
            };

            tr.appendChild(td);
        });

        // Row Right-Click Context Menu for General Ledger (GL)
        tr.oncontextmenu = function(e) {
            e.preventDefault();
            e.stopPropagation();

            window.currentSelectedPartnerId = row.id || row.partner_id || '';
            window.currentSelectedPartnerName = row.name || row.account_name || row.accountName || '';
            window.currentSelectedPartnerType = row.type || 'CUST';

            const partnerCtxMenu = document.getElementById('partnerContextMenu');
            const headerEl = document.getElementById('partnerMenuHeader');
            if (headerEl) headerEl.innerHTML = `👤 <strong>${window.currentSelectedPartnerName}</strong>`;

            if (partnerCtxMenu) {
                let posX = e.clientX || e.pageX;
                let posY = e.clientY || e.pageY;
                const menuWidth = 260;
                const menuHeight = 120;
                if (posX + menuWidth > window.innerWidth) posX = window.innerWidth - menuWidth - 10;
                if (posY + menuHeight > window.innerHeight) posY = window.innerHeight - menuHeight - 10;

                partnerCtxMenu.style.left = posX + 'px';
                partnerCtxMenu.style.top = posY + 'px';
                partnerCtxMenu.style.display = 'block';
            }
        };

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
                        إعادة التقرير كلياً إلى التصميم الذهبي الافتراضي الذي تم برمجه ورفعه مع البرنامج.
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
        configs: getFactoryPreset1Configs(),
        order: [0, 6, 7, 4, 5, 1, 2, 3, 8, 9, 10],
        theme: 'theme-gold',
        font: 'font-ultra',
        paperSize: 'A4',
        orientation: 'landscape',
        showLogo: true,
        showInfo: true
    };
    applyDesignState(factoryState);
    showToast('🏭 تم استرجاع الضبط الافتراضي الأصلي للنظام بنجاح!', 'info');
}
