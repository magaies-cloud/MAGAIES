window.getAllSinglePayments = function() {
    let localInvs = [];
    try { localInvs = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []; } catch(e){}
    let serverInvs = window.SERVER_SINGLE_PAYMENTS || [];
    let map = new Map();
    serverInvs.forEach(inv => map.set(inv.id, inv));
    localInvs.forEach(inv => map.set(inv.id, inv));
    return Array.from(map.values());
};
/* ═══════════════════════════════════════════════════════
   single-cash-payment.js — Single Cash/Check Disbursement Module
   ═══════════════════════════════════════════════════════ */

let ACCOUNTS = [];

// Load real accounts from master-data
// (Moved to DBReady event)

let singlePaymentsData = [];
            try {
                singlePaymentsData = window.getAllSinglePayments();
                if (!Array.isArray(singlePaymentsData)) singlePaymentsData = [];
            } catch(e) {
                singlePaymentsData = [];
            }
let currentViewedIndex = -1; 

function fmtNum(n) {
    if (n === 0 || n === '0' || !n) return '0';
    return Number(n).toLocaleString('en-US');
}
function parseNum(str) {
    return parseFloat(String(str).replace(/,/g, '')) || 0;
}
function showToast(msg, type = 'success', ms = 3500) {
    let c = document.getElementById('toast-container');
    if (!c) {
        c = document.createElement('div');
        c.id = 'toast-container';
        c.className = 'toast-container';
        document.body.appendChild(c);
    }

    const t = document.createElement('div');
    t.className = `toast ${type}`;

    let icon = '✨';
    if (type === 'error' || type === 'danger') icon = '❌';
    else if (type === 'warning') icon = '⚠️';
    else if (type === 'info') icon = 'ℹ️';
    else if (type === 'success') icon = '✅';

    t.innerHTML = `<span class="toast-icon">${icon}</span><span class="toast-text">${msg}</span>`;

    // Click to dismiss immediately
    t.onclick = function() {
        t.classList.add('hide');
        setTimeout(() => { if (t.parentNode) t.remove(); }, 320);
    };

    c.appendChild(t);
    setTimeout(() => {
        if (t.parentNode) {
            t.classList.add('hide');
            setTimeout(() => { if (t.parentNode) t.remove(); }, 350);
        }
    }, ms);
}
window.showToast = showToast;

function showLengthWarningModal(transferNo) {
    const existing = document.getElementById('critical-alert-modal');
    if (existing) existing.remove();

    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes liquidPulsate {
            0% { transform: scale(1) translate(-50%, -50%); border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; box-shadow: 0 0 40px rgba(220, 20, 60, 0.4), inset 0 0 20px rgba(220, 20, 60, 0.6); }
            50% { transform: scale(1.05) translate(-50%, -50%); border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; box-shadow: 0 0 80px rgba(255, 0, 85, 0.8), inset 0 0 40px rgba(255, 0, 85, 0.9); }
            100% { transform: scale(1) translate(-50%, -50%); border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; box-shadow: 0 0 40px rgba(220, 20, 60, 0.4), inset 0 0 20px rgba(220, 20, 60, 0.6); }
        }
        @keyframes rayRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes bounceInLiquid {
            0% { opacity: 0; transform: translateY(100vh); }
            60% { opacity: 1; transform: translateY(-30px); }
            80% { transform: translateY(15px); }
            100% { transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);

    const modalHTML = `
        <div id="critical-alert-modal" style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(10,0,0,0.85); backdrop-filter:blur(10px); z-index:99999; display:flex; justify-content:center; align-items:center; overflow:hidden;">
            <div style="position:absolute; width:200vw; height:200vw; background: repeating-conic-gradient(from 0deg, transparent 0deg 15deg, rgba(255, 20, 60, 0.15) 15deg 30deg); animation: rayRotate 20s linear infinite; pointer-events:none;"></div>
            <div style="position:absolute; top:35%; left:50%; width:450px; height:450px; background: radial-gradient(circle, #8b0000 0%, #3e0000 100%); animation: liquidPulsate 4s ease-in-out infinite, bounceInLiquid 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; transform-origin: top left; display:flex; flex-direction:column; justify-content:center; align-items:center; border: 3px solid rgba(255,100,100,0.5); padding: 40px; text-align:center;">
                <div style="font-size: 5rem; text-shadow: 0 0 20px #ff0000; animation: bounceInLiquid 1s infinite;">⚠️</div>
                <h2 style="color: #ffcccc; font-family: 'Tajawal', sans-serif; font-size: 2rem; margin: 15px 0; text-shadow: 0 0 10px #ff0000;">تحذير أمني!</h2>
                <p style="color: #fff; font-size: 1.2rem; font-weight:bold; line-height: 1.6;">رقم التحويلة <span style="color:#ffeb3b; font-size:1.7rem;">${transferNo}</span><br>أقل من 6 أرقام!</p>
                <p style="color: #ff8888; font-size: 0.9rem; margin-bottom: 25px;">يجب أن يتكون رقم التحويلة من 6 أرقام على الأقل لضمان الحماية.</p>
                <button onclick="document.getElementById('critical-alert-modal').remove()" style="background: linear-gradient(45deg, #ff0000, #880000); color: white; border: 2px solid #ffaaaa; border-radius: 30px; padding: 12px 35px; font-size: 1.1rem; font-weight: bold; cursor: pointer; box-shadow: 0 0 15px #ff0000; transition: all 0.3s; font-family: 'Tajawal', sans-serif;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 0 30px #ff0000';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 0 15px #ff0000';">فهمت ذلك</button>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function showDuplicateWarningModal(transferNo) {
    const existing = document.getElementById('duplicate-alert-modal');
    if (existing) existing.remove();

    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes glitchZoom {
            0% { transform: scale(0.1) skewX(20deg); opacity: 0; filter: hue-rotate(90deg); }
            20% { transform: scale(1.2) skewX(-15deg); opacity: 1; }
            40% { transform: scale(0.9) skewX(10deg); filter: hue-rotate(-90deg); }
            60% { transform: scale(1.05) skewX(-5deg); }
            80% { transform: scale(0.98) skewX(2deg); filter: hue-rotate(0deg); }
            100% { transform: scale(1) skewX(0deg); }
        }
        @keyframes neonFlicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% { box-shadow: 0 0 10px #ff0044, 0 0 20px #ff0044, 0 0 40px #ff0044, inset 0 0 20px #ff0044; border-color: #ff0044; }
            20%, 24%, 55% { box-shadow: none; border-color: #330000; }
        }
        @keyframes warningScan {
            0% { top: -10%; opacity: 0; }
            50% { opacity: 1; }
            100% { top: 110%; opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    const modalHTML = `
        <div id="duplicate-alert-modal" style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:radial-gradient(circle at center, rgba(30,0,0,0.9) 0%, rgba(0,0,0,1) 100%); z-index:99999; display:flex; justify-content:center; align-items:center;">
            <div style="position:absolute; width:100%; height:15px; background:rgba(255,0,68,0.8); box-shadow:0 0 30px #ff0044; animation: warningScan 2s linear infinite; z-index:1;"></div>
            <div style="position:relative; width:500px; background: rgba(10,0,0,0.9); animation: glitchZoom 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards, neonFlicker 3s infinite; transform-origin: center; display:flex; flex-direction:column; justify-content:center; align-items:center; border: 4px solid #ff0044; padding: 40px; text-align:center; z-index:2; clip-path: polygon(0 15px, 15px 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%);">
                <div style="font-size: 6rem; line-height:1; color: #ff0044; text-shadow: 4px 4px 0px #00ffff, -4px -4px 0px #ffff00; font-family: monospace; font-weight:900;">ERROR!</div>
                <h2 style="color: #fff; font-family: 'Tajawal', sans-serif; font-size: 2.2rem; margin: 20px 0 10px; letter-spacing:2px; text-transform:uppercase;">تحويلة مكررة</h2>
                <div style="width:100%; height:2px; background:#ff0044; margin:10px 0;"></div>
                <p style="color: #ddd; font-size: 1.3rem; line-height: 1.6;">تم اكتشاف تطابق خطير!</p>
                <p style="color: #fff; font-size: 1.1rem; margin-bottom: 30px; line-height:1.8;">رقم التحويلة <strong style="background:#ff0044; color:#fff; padding:3px 8px; border-radius:4px; font-family:monospace; font-size:1.7rem;">${transferNo}</strong><br>تم تسجيله مسبقاً في النظام (آخر 6 أرقام متطابقة).</p>
                <button onclick="document.getElementById('duplicate-alert-modal').remove()" style="background: transparent; color: #ff0044; border: 2px solid #ff0044; padding: 12px 40px; font-size: 1.2rem; font-weight: 900; cursor: pointer; text-transform:uppercase; letter-spacing:2px; transition: 0.2s; font-family: 'Tajawal', sans-serif;" onmouseover="this.style.background='#ff0044'; this.style.color='#000'; this.style.boxShadow='0 0 20px #ff0044';" onmouseout="this.style.background='transparent'; this.style.color='#ff0044'; this.style.boxShadow='none';">تجاهل وإغلاق</button>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function onDBReady(fn) {
    if (window.APP_READY) {
        setTimeout(fn, 10);
    } else {
        document.addEventListener('DBReady', fn);
    }
}

// Immediate Drill-down check from sessionStorage
(function checkImmediateDrillDown() {
    const urlParams = new URLSearchParams(window.location.search);
    const voucherId = urlParams.get('id');
    const voucherType = urlParams.get('type');
    if (!voucherId) return;

    try {
        let cached = sessionStorage.getItem('_edit_voucher_data');
        if (cached) {
            let parsed = JSON.parse(cached);
            let parsedId = String(parsed.id || parsed.voucherNo || parsed.paymentNo || parsed.receiptNo || '');
            if (parsedId === String(voucherId)) {
                window.isVoucherLoadedFromDrilldown = true;
                sessionStorage.removeItem('_edit_voucher_data');
                
                let doRender = () => {
                    if (document.getElementById('edit-warning-banner')) {
                        document.getElementById('edit-warning-banner').style.display = 'flex';
                    }
                    if (voucherType !== 'receipt') {
                        let freshData = window.getAllSinglePayments ? window.getAllSinglePayments() : [];
                        let pIdx = freshData.findIndex(v => String(v.id) === String(voucherId) || String(v.voucherNo) === String(voucherId) || String(v.paymentNo) === String(voucherId));
                        if (pIdx !== -1) currentViewedIndex = pIdx;
                    }
                    renderVoucher(parsed);
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', doRender);
                } else {
                    doRender();
                }
            }
        }
    } catch(e) {}
})();

onDBReady(() => {
    // Load accounts dynamically here to ensure DB_STORE is populated

    const fromSelect = document.getElementById('from-account');
    const toSelect = document.getElementById('to-account');
    if (fromSelect && toSelect && window.DB_STORE) {
        let options = '<option value="">-- اختر الحساب --</option>';
        Object.keys(window.DB_STORE).forEach(key => {
            const acc = window.DB_STORE[key];
            if (acc.name && !acc.deleted) {
                options += `<option value="${acc.id}">${acc.name}</option>`;
            }
        });
        fromSelect.innerHTML = options;
        toSelect.innerHTML = options;
    }

    const getTenantKey = window.getTenantKey || ((k) => k);
    let localCusts = [], localSups = [], localBanks = [], localSafes = [], localExp = [], localJobs = [];
    try { localCusts = (JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1')) || localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    try { localSups = (JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    if (!localSups || localSups.length === 0) {
        localSups = [
        { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', currentBalance: 0, creditLimit: 50000000 },
        { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', currentBalance: 0, creditLimit: 30000000 },
        { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', currentBalance: 0, creditLimit: 20000000 },
        { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', currentBalance: 0, creditLimit: 100000000 },
        { id: 'SUP-005', code: 'SUP-005', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', currentBalance: 0, creditLimit: 15000000 },
        { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', currentBalance: 0, creditLimit: 25000000 },
        { id: 'SUP-007', code: 'SUP-007', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', currentBalance: 0, creditLimit: 40000000 }
    ];
        try { localStorage.setItem(getTenantKey('erp_suppliers_v1'), JSON.stringify(localSups)); localStorage.setItem('erp_suppliers_v1', JSON.stringify(localSups)); } catch(e){}
    }
    try { localBanks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
    try { localSafes = JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1')) || localStorage.getItem('erp_safes_v1')) || []; } catch(e) {}
    try { localExp = JSON.parse(localStorage.getItem(getTenantKey('erp_accounts_v1')) || localStorage.getItem('erp_accounts_v1')) || []; } catch(e) {}
    try { localJobs = JSON.parse(localStorage.getItem(getTenantKey('erp_jobs_v1')) || localStorage.getItem('erp_jobs_v1')) || []; } catch(e) {}
    
    ACCOUNTS = [];
    localCusts.forEach(c => ACCOUNTS.push({ id: `cust-${c.id}`, name: c.name || c.customerName, type: 'عميل', color: '#10b981', balances: { checks: 0, debt: parseFloat(c.currentBalance) || 0, loan: 0 } }));
    localSups.forEach(s => ACCOUNTS.push({ id: `supp-${s.id}`, name: s.name || s.supplierName, type: 'مورد', color: '#f59e0b', balances: { checks: 0, debt: 0, loan: parseFloat(s.currentBalance) || 0 } }));
    localBanks.forEach(b => ACCOUNTS.push({ id: `bank-${b.id}`, name: b.name || b.bankName, type: 'بنك', color: '#3b82f6', balances: { checks: 0, debt: parseFloat(b.currentBalance) || 0, loan: 0 } }));
    localSafes.forEach(s => ACCOUNTS.push({ id: `safe-${s.id}`, name: s.name || s.safeName, type: 'خزنة', color: '#8b5cf6', balances: { checks: 0, debt: parseFloat(s.currentBalance) || 0, loan: 0 } }));
    localExp.forEach(e => ACCOUNTS.push({ id: `exp-${e.id}`, name: e.name || e.accountName, type: 'عام', color: '#64748b', balances: { checks: 0, debt: parseFloat(e.currentBalance) || 0, loan: 0 } }));

    const dateInputEl = document.getElementById('hdr-date');
    if (dateInputEl) {
        const __d = new Date();
        const __y = __d.getFullYear();
        const __m = String(__d.getMonth() + 1).padStart(2, '0');
        const __day = String(__d.getDate()).padStart(2, '0');
        dateInputEl.value = `${__y}-${__m}-${__day}`;
        dateInputEl.addEventListener('change', () => {
            if (typeof currentViewedIndex !== 'undefined' && currentViewedIndex === -1) {
                setNextVoucherNumber();
            }
        });
    }
    setNextVoucherNumber();

    function buildOptions() {
        let html = '<option value="">-- اختر الحساب --</option>';
        if (localBanks.length || localSafes.length) {
            html += '<optgroup label="حسابات البنوك والخزن">';
            localSafes.forEach(s => html += `<option value="safe-${s.id}">${s.name}</option>`);
            localBanks.forEach(b => html += `<option value="bank-${b.id}">${b.name}</option>`);
            html += '</optgroup>';
        }
        if (localCusts.length) {
            html += '<optgroup label="العملاء والزبائن">';
            localCusts.forEach(c => html += `<option value="cust-${c.id}">${c.name}</option>`);
            html += '</optgroup>';
        }
        if (localSups.length) {
            html += '<optgroup label="الموردين">';
            localSups.forEach(s => html += `<option value="supp-${s.id}">${s.name}</option>`);
            html += '</optgroup>';
        }
        if (localExp.length) {
            html += '<optgroup label="المصاريف">';
            localExp.forEach(e => html += `<option value="exp-${e.id}">${e.name}</option>`);
            html += '</optgroup>';
        }
        return html;
    }

    const toAccEl = document.getElementById('to-account');
    const fromAccEl = document.getElementById('from-account');
    const costCenterEl = document.getElementById('cost-center');

    const opts = buildOptions();
    if(toAccEl) toAccEl.innerHTML = opts;
    if(fromAccEl) fromAccEl.innerHTML = opts;

    if (costCenterEl) {
        let jobOpts = '<option value="">-- بدون مركز تكلفة --</option>';
        localJobs.forEach(j => jobOpts += `<option value="${j.id}">${j.name}</option>`);
        costCenterEl.innerHTML = jobOpts;
    }

    // Auto-jump to next field on selection
        toAccEl.addEventListener('change', (e) => {
            if(e.detail && e.detail.value) document.getElementById('to-statement').focus();
        });
        fromAccEl.addEventListener('change', (e) => {
            if(e.detail && e.detail.value) {
                if (window.ccChoice) window.ccChoice.showDropdown();
                else document.getElementById('from-statement').focus();
            }
        });
        if (costCenterEl) costCenterEl.addEventListener('change', (e) => {
            if(e.detail && e.detail.value) document.getElementById('from-statement').focus();
        });


    toAccEl.addEventListener('change', updateBalances);
    
    // Initialize UI
    togglePaymentMethod();
});

// Deep Drill-down (Run after DBReady so localforage data is fully loaded)
onDBReady(() => {
    setTimeout(async () => {
        const urlParams = new URLSearchParams(window.location.search);
        const voucherId = urlParams.get('id');
        const voucherType = urlParams.get('type');
        if (!voucherId) return;
        
        if(document.getElementById('edit-warning-banner')) {
            document.getElementById('edit-warning-banner').style.display = 'flex';
        }
        
        let foundVoucher = null;
        
        // 1. PRIORITY: Check sessionStorage (set by vouchers-hub before navigation)
        try {
            let cached = sessionStorage.getItem('_edit_voucher_data');
            if (cached) {
                let parsed = JSON.parse(cached);
                if (parsed) {
                    let parsedId = String(parsed.id || parsed.voucherNo || parsed.paymentNo || parsed.receiptNo || '');
                    let targetId = String(voucherId || '');
                    if (!targetId || parsedId === targetId || parsedId.replace(/^[A-Z]+-?/, '') === targetId.replace(/^[A-Z]+-?/, '')) {
                        foundVoucher = parsed;
                        sessionStorage.removeItem('_edit_voucher_data'); // Clean up
                    }
                }
            }
        } catch(e) {}
        
        // 2. FALLBACK: Search through all data sources
        if (!foundVoucher) {
            const tkSR = window.getTenantKey ? window.getTenantKey('erp_single_receipts_v1') : 'erp_single_receipts_v1';
            const tkSP = window.getTenantKey ? window.getTenantKey('erp_single_payments_v1') : 'erp_single_payments_v1';
            
            let localReceipts = [];
            let localPayments = [];
            
            // Try localforage first
            if (typeof localforage !== 'undefined') {
                try {
                    localReceipts = (await localforage.getItem(tkSR)) || (await localforage.getItem('erp_single_receipts_v1')) || [];
                    localPayments = (await localforage.getItem(tkSP)) || (await localforage.getItem('erp_single_payments_v1')) || [];
                } catch(e) {}
            }
            
            // Fallback to localStorage
            if (!Array.isArray(localReceipts) || localReceipts.length === 0) {
                localReceipts = JSON.parse(localStorage.getItem(tkSR) || localStorage.getItem('erp_single_receipts_v1') || '[]');
            }
            if (!Array.isArray(localPayments) || localPayments.length === 0) {
                localPayments = window.getAllSinglePayments ? window.getAllSinglePayments() : JSON.parse(localStorage.getItem(tkSP) || localStorage.getItem('erp_single_payments_v1') || '[]');
            }

            if (!Array.isArray(localReceipts)) localReceipts = Object.values(localReceipts);
            if (!Array.isArray(localPayments)) localPayments = Object.values(localPayments);
            
            const serverReceipts = window.SERVER_SINGLE_RECEIPTS || [];
            const serverPayments = window.SERVER_SINGLE_PAYMENTS || [];
            
            const allData = [...serverPayments, ...localPayments, ...serverReceipts, ...localReceipts];
            
            const foundIdx = allData.findIndex(v => {
                if (!v) return false;
                const vid = String(v.id || '');
                const vno = String(v.voucherNo || v.paymentNo || v.receiptNo || '');
                const target = String(voucherId || '');
                if (vid === target || vno === target) return true;
                if (target && vid.replace(/^[A-Z]+-?/, '') === target.replace(/^[A-Z]+-?/, '')) return true;
                if (target && vno.replace(/^[A-Z]+-?/, '') === target.replace(/^[A-Z]+-?/, '')) return true;
                return false;
            });
            
            if (foundIdx !== -1) {
                foundVoucher = allData[foundIdx];
            }
        }
        
        // 3. If found, set currentViewedIndex and render
        if (foundVoucher) {
            if (voucherType !== 'receipt') {
                let freshData = window.getAllSinglePayments ? window.getAllSinglePayments() : [];
                if (freshData.length > 0) singlePaymentsData = freshData;
                let pIdx = singlePaymentsData.findIndex(v => String(v.id) === String(voucherId) || String(v.voucherNo) === String(voucherId) || String(v.paymentNo) === String(voucherId));
                if (pIdx !== -1) {
                    currentViewedIndex = pIdx;
                }
            }
            
            renderVoucher(foundVoucher);
        }
    }, 300); // 300ms after DBReady to ensure generateNewId doesn't override us
});

function updateBalances() {
    const toAccEl = document.getElementById('to-account');
    if (!toAccEl) return;
    const accId = toAccEl.value;
    if (!accId) {
        if (document.getElementById('bal-checks')) document.getElementById('bal-checks').innerText = '0';
        if (document.getElementById('bal-debt')) document.getElementById('bal-debt').innerText = '0';
        if (document.getElementById('bal-loan')) document.getElementById('bal-loan').innerText = '0';
        return;
    }

    // Rebuild GL if available to get freshest numbers
    if (typeof window.rebuildGLFromAllTables === 'function') {
        window.rebuildGLFromAllTables(true);
    }

    const getTK = window.getTenantKey || ((k) => k);
    let gl = [];
    try {
        gl = JSON.parse(localStorage.getItem(getTK('erp_gl_v1')) || localStorage.getItem('erp_gl_v1')) || [];
    } catch(e) {}

    // Find opening balance from master records
    let opBal = 0;
    let opBalFc = 0;
    const cleanId = String(accId).replace(/^(cust|supp|bank|safe|exp)-/, '');
    
    // Check customers, suppliers, safes, banks
    let masterList = [];
    try {
        if (accId.startsWith('cust-') || !accId.includes('-')) {
            masterList = JSON.parse(localStorage.getItem(getTK('erp_customers_v1')) || localStorage.getItem('erp_customers_v1')) || [];
        } else if (accId.startsWith('supp-')) {
            masterList = JSON.parse(localStorage.getItem(getTK('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1')) || [];
        } else if (accId.startsWith('bank-')) {
            masterList = JSON.parse(localStorage.getItem(getTK('erp_banks_v1')) || localStorage.getItem('erp_banks_v1')) || [];
        } else if (accId.startsWith('safe-')) {
            masterList = JSON.parse(localStorage.getItem(getTK('erp_safes_v1')) || localStorage.getItem('erp_safes_v1')) || [];
        }
    } catch(e) {}

    const master = masterList.find(m => String(m.id) === cleanId || String(m.id) === accId);
    if (master) {
        const opDr = parseFloat(master.openingDebit || master.debit || 0) || 0;
        const opCr = parseFloat(master.openingCredit || master.credit || 0) || 0;
        const rawB = parseFloat(master.openingBalance || master.opening_balance || master.balance || master.currentBalance || 0) || 0;
        if (opDr > 0 || opCr > 0) {
            opBal = opDr - opCr;
        } else if (rawB !== 0) {
            opBal = (master.balance_type === 'credit' || master.balanceType === 'credit') ? -Math.abs(rawB) : Math.abs(rawB);
        }
    }

    let runningLoc = opBal;
    let runningFc = 0;
    let runningChecks = 0;

    gl.forEach(t => {
        if (!t) return;
        const tAcc = String(t.account || '');
        if (tAcc === accId || tAcc === cleanId || tAcc.replace(/^(cust|supp|bank|safe|exp)-/, '') === cleanId) {
            const dr = parseFloat(t.debit) || 0;
            const cr = parseFloat(t.credit) || 0;
            const drFc = parseFloat(t.debitForeign) || 0;
            const crFc = parseFloat(t.creditForeign) || 0;
            runningLoc += (dr - cr);
            runningFc += (drFc - crFc);

            if (t.checkNo) {
                runningChecks += (dr - cr);
            }
        }
    });

    const formatB = (n) => {
        if (isNaN(n)) return '0';
        return Math.abs(n).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    };

    if (document.getElementById('bal-checks')) document.getElementById('bal-checks').innerText = formatB(runningLoc);
    if (document.getElementById('bal-debt')) document.getElementById('bal-debt').innerText = formatB(runningChecks);
    if (document.getElementById('bal-loan')) document.getElementById('bal-loan').innerText = formatB(runningFc);
}

function togglePaymentMethod() {
    const methodEl = document.querySelector('input[name="payment_method"]:checked');
    if (!methodEl) return;
    const method = methodEl.value;
    
    // Legacy elements
    const secMobile = document.getElementById('section-mobile');
    const secCheck = document.getElementById('section-check');
    if (secMobile) secMobile.classList.remove('active');
    if (secCheck) secCheck.classList.remove('active');

    // New-style elements
    const secCash = document.getElementById('pay-cash-section');
    const secBank = document.getElementById('pay-bank-section');
    const secCheque = document.getElementById('pay-cheque-section');
    if (secCash) secCash.style.display = 'none';
    if (secBank) secBank.style.display = 'none';
    if (secCheque) secCheque.style.display = 'none';

    if (method === 'cash') {
        if (secCash) secCash.style.display = 'flex';
    } else if (method === 'mobile') {
        if (secMobile) secMobile.classList.add('active');
        if (secBank) secBank.style.display = 'flex';
    } else if (method === 'check') {
        if (secCheck) secCheck.classList.add('active');
        if (secCheque) secCheque.style.display = 'flex';
    } else if (method === 'hybrid') {
        if (secMobile) secMobile.classList.add('active');
        if (secCheck) secCheck.classList.add('active');
        if (secBank) secBank.style.display = 'flex';
        if (secCheque) secCheque.style.display = 'flex';
    }
}

function setNextVoucherNumber() {
    if (window.isVoucherLoadedFromDrilldown || (typeof currentViewedIndex !== 'undefined' && currentViewedIndex !== -1)) {
        return;
    }
    let spData = [];
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        spData = window.getAllSinglePayments();
    } catch(e) {}

    // Base prefix on selected voucher date, not current system date
    const dateInput = document.getElementById('hdr-date');
    const selectedDate = (dateInput && dateInput.value) ? new Date(dateInput.value) : new Date();
    const validDate = isNaN(selectedDate.getTime()) ? new Date() : selectedDate;
    const yy = String(validDate.getFullYear()).substring(2, 4);
    const mm = String(validDate.getMonth() + 1).padStart(2, '0');
    const datePrefix = 'E' + yy + mm;

    let max = 0;
    spData.forEach(v => {
        const vNo = String(v.id || v.voucherNo || '');
        if (vNo.startsWith(datePrefix)) {
            const seqStr = vNo.substring(datePrefix.length);
            const seq = parseInt(seqStr, 10);
            if (!isNaN(seq) && seq > max) {
                max = seq;
            }
        }
    });

    const nextNo = `${datePrefix}${String(max + 1).padStart(4, '0')}`;
    const vNumEl = document.getElementById('voucher-number');
    if (vNumEl) {
        vNumEl.innerText = nextNo;
    }
}

window.adjustAmountFontSize = function(input) {
    if (input.getAttribute('data-manual-font') === 'true') return;
    const len = input.value.length;
    if (len > 14) {
        input.style.setProperty('font-size', '1.2rem', 'important');
        input.style.setProperty('letter-spacing', '0px', 'important');
    } else if (len > 11) {
        input.style.setProperty('font-size', '1.5rem', 'important');
        input.style.setProperty('letter-spacing', '1px', 'important');
    } else if (len > 8) {
        input.style.setProperty('font-size', '1.8rem', 'important');
        input.style.setProperty('letter-spacing', '2px', 'important');
    } else {
        input.style.setProperty('font-size', '2.5rem', 'important');
        input.style.setProperty('letter-spacing', '3px', 'important');
    }
};

function updateCurrency(preventRateOverwrite = false) {
    const curr = document.getElementById('currency').value;
    const rateEl = document.getElementById('exchange-rate');
    const symbolEl1 = document.getElementById('amount-currency-symbol');
    const symbolEl2 = document.getElementById('amount-foreign-currency-symbol');
    const label1 = document.getElementById('label-box-1');
    const label2 = document.getElementById('label-box-2');
    
    // Fetch local default currency from settings
    let localCurr = 'SDG';
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const settings = JSON.parse(localStorage.getItem(getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
        localCurr = settings.currency || 'SDG';
    } catch (e) {
        localCurr = 'SDG';
    }
    
    let foreignCurr = 'USD'; // Default fallback foreign currency
    let targetForeignCurr = (curr === localCurr || curr === 'SDG') ? foreignCurr : curr;
    
    // Set labels and symbols
    if (curr === localCurr || curr === 'SDG') {
        if (symbolEl1) symbolEl1.innerText = localCurr;
        if (symbolEl2) symbolEl2.innerText = foreignCurr;
        if (label1) label1.innerHTML = '<span class="vivid-icon vivid-green" style="width:24px; height:24px; font-size:1.3rem;">💰</span> المبلغ (محلي)';
        if (label2) label2.innerHTML = '💵 القيمة (أجنبي)';
    } else {
        if (symbolEl1) symbolEl1.innerText = curr;
        if (symbolEl2) symbolEl2.innerText = localCurr;
        if (label1) label1.innerHTML = '<span class="vivid-icon vivid-green" style="width:24px; height:24px; font-size:1.3rem;">💰</span> المبلغ (أجنبي)';
        if (label2) label2.innerHTML = '💵 القيمة (محلي)';
    }

    rateEl.readOnly = false;
    if (typeof window.getExchangeRateForCurrency === 'function' && !preventRateOverwrite) {
        const fetchedRate = window.getExchangeRateForCurrency(targetForeignCurr);
        if (fetchedRate && fetchedRate !== 1) {
            rateEl.value = fetchedRate;
        } else if(parseNum(rateEl.value) <= 1) {
            rateEl.value = ''; 
        }
    }

    calcEquiv('rate');
    if (typeof adjustAmountFontSize === 'function') {
        adjustAmountFontSize(rateEl);
    }
}

window.animateInputNumber = function(inputEl, endValueStr, duration = 300) {
    if (inputEl.animationInterval) {
        clearInterval(inputEl.animationInterval);
    }
    const endVal = parseNum(endValueStr);
    let startVal = parseNum(inputEl.value) || 0;
    
    if (Math.abs(endVal - startVal) < 0.01) {
        inputEl.value = endValueStr;
        if (typeof adjustAmountFontSize === 'function') adjustAmountFontSize(inputEl);
        return;
    }

    const startTime = performance.now();
    
    function addCommas(numStr) {
        let parts = numStr.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    inputEl.animationInterval = setInterval(() => {
        const elapsed = performance.now() - startTime;
        let progress = elapsed / duration;
        
        if (progress >= 1) {
            progress = 1;
            clearInterval(inputEl.animationInterval);
            inputEl.value = endValueStr;
        } else {
            const easeOut = progress * (2 - progress);
            const currentVal = startVal + (endVal - startVal) * easeOut;
            inputEl.value = addCommas(currentVal.toFixed(2));
        }
        if (typeof adjustAmountFontSize === 'function') adjustAmountFontSize(inputEl);
    }, 16);
};

function calcEquiv(source) {
    const amtEl = document.getElementById('amount-local');
    const rateEl = document.getElementById('exchange-rate');
    const eqEl = document.getElementById('amount-foreign');
    const curr = document.getElementById('currency').value;
    
    let localCurr = 'SDG';
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const settings = JSON.parse(localStorage.getItem(getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
        localCurr = settings.currency || 'SDG';
    } catch (e) {
        localCurr = 'SDG';
    }

    let val1 = parseNum(amtEl.value);
    let rate = parseNum(rateEl.value) || 1;
    let val2 = parseNum(eqEl.value);

    let actualRateForForeign = rate;
    if (rate === 1 && typeof window.getExchangeRateForCurrency === 'function') {
        const globalRate = window.getExchangeRateForCurrency('USD');
        if (globalRate && globalRate > 1) {
            actualRateForForeign = globalRate;
            rateEl.value = globalRate;
        } else if (typeof window.getTenantKey === 'function') {
            const settings = JSON.parse(localStorage.getItem(window.getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
            if (settings.exchangeRate && settings.exchangeRate > 1) {
                actualRateForForeign = settings.exchangeRate;
                rateEl.value = settings.exchangeRate;
            }
        }
    }

    function addCommas(numStr) {
        let parts = numStr.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    function setAnimatedValue(el, val) {
        if (typeof window.animateValue === 'function') {
            window.animateValue(el, val, 600, 2);
            if (typeof adjustAmountFontSize === 'function') adjustAmountFontSize(el);
        } else if (typeof animateInputNumber === 'function') {
            animateInputNumber(el, addCommas(val.toFixed(2)));
        } else {
            el.value = addCommas(val.toFixed(2));
            if (typeof adjustAmountFontSize === 'function') adjustAmountFontSize(el);
        }
    }

    if (curr === localCurr || curr === 'SDG') {
        // Box 1 = Local, Box 2 = Foreign
        if (source === 'local' || source === 'rate') {
            val2 = actualRateForForeign === 0 ? 0 : val1 / actualRateForForeign;
            setAnimatedValue(eqEl, val2);
        } else if (source === 'foreign') {
            val1 = val2 * actualRateForForeign;
            setAnimatedValue(amtEl, val1);
        }
    } else {
        // Box 1 = Foreign, Box 2 = Local
        if (source === 'local' || source === 'rate') {
            // "local" source is actually Box 1 which is Foreign now
            val2 = val1 * actualRateForForeign;
            setAnimatedValue(eqEl, val2);
        } else if (source === 'foreign') {
            // "foreign" source is actually Box 2 which is Local now
            val1 = actualRateForForeign === 0 ? 0 : val2 / actualRateForForeign;
            setAnimatedValue(amtEl, val1);
        }
    }

    if (typeof syncFirstRowAmount === 'function') syncFirstRowAmount();
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
}

function resetVoucher() {
    window.location.href = window.location.pathname;
}

function resetVoucherSilent() {
    currentViewedIndex = -1;
    document.getElementById('hdr-date').valueAsDate = new Date();
    
    document.getElementById('to-account').value = '';
    document.getElementById('to-account-search').value = '';
    
    let localCurr = 'SDG';
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const settings = JSON.parse(localStorage.getItem(getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
        localCurr = settings.currency || 'SDG';
    } catch (e) { }

    document.getElementById('amount-local').value = '';
    document.getElementById('currency').value = localCurr;
    document.getElementById('exchange-rate').value = '1.0';
    document.getElementById('exchange-rate').readOnly = true;
    document.getElementById('amount-foreign').value = '';
    document.getElementById('to-statement').value = '';
    document.getElementById('beneficiary-name').value = '';
    
    document.getElementById('from-account').value = '';
    updateCurrency();
    const defFromAcc = (ACCOUNTS || []).find(a => String(a.id) === 'safe-001');
    document.getElementById('from-account-search').value = defFromAcc ? defFromAcc.name : '';
    if (window.fromAccChoice) {
        window.fromAccChoice.removeActiveItems();
        window.fromAccChoice.setChoiceByValue('');
    }
    
    const costCenterEl = document.getElementById('cost-center');
    if (costCenterEl) {
        costCenterEl.value = '';
        if (window.ccChoice) {
            window.ccChoice.removeActiveItems();
            window.ccChoice.setChoiceByValue('');
        }
    }
    
    document.getElementById('from-statement').value = '';

    // Reset Radio to Bank Transfer (mobile)
    document.querySelector('input[name="payment_method"][value="mobile"]').checked = true;
    togglePaymentMethod();

    if (typeof initPaymentContainers === 'function') initPaymentContainers();

    updateBalances();
    // setNextVoucherNumber();
    updateLedgerButtons();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
}

function loadPreviousVoucher() {
    singlePaymentsData = window.getAllSinglePayments();
    if (singlePaymentsData.length === 0) return showToast("لا توجد سندات سابقة", "warning");
    
    if (currentViewedIndex === -1) {
        currentViewedIndex = singlePaymentsData.length - 1;
    } else if (currentViewedIndex > 0) {
        currentViewedIndex--;
    } else {
        return showToast("هذا هو أول سند تم حفظه", "warning");
    }
    renderVoucher(singlePaymentsData[currentViewedIndex]);
}

function loadNextVoucher() {
    singlePaymentsData = window.getAllSinglePayments();
    if (currentViewedIndex === -1) return; // already at new
    
    if (currentViewedIndex < singlePaymentsData.length - 1) {
        currentViewedIndex++;
        renderVoucher(singlePaymentsData[currentViewedIndex]);
    } else {
        resetVoucherSilent();
        showToast("سند جديد", "success");
    }
}

function renderVoucher(v) {
    if (!v) return;
    window.isVoucherLoadedFromDrilldown = true;
    window.isLoadingVoucher = true;

    let vid = v.id || v.voucherNo || v.paymentNo || v.receiptNo || v.invoiceNo || '';
    if (document.getElementById('voucher-number')) document.getElementById('voucher-number').innerText = vid;
    
    let dt = v.date || (v.createdAt ? String(v.createdAt).split('T')[0] : '');
    if (dt && document.getElementById('hdr-date')) document.getElementById('hdr-date').value = dt;
    
    let toAcc = v.toAccount || v.depositAccount || v.accountId || '';
    let fromAcc = v.fromAccount || v.withdrawalAccount || v.cashSafe || v.mainAccount || 'safe-001';
    
    if (document.getElementById('to-account')) document.getElementById('to-account').value = toAcc;
    if (document.getElementById('to-account-search')) document.getElementById('to-account-search').value = getAccountName(toAcc) || toAcc;

    let amt = (v.amountLocal !== undefined && v.amountLocal !== null) ? v.amountLocal : (v.amount !== undefined ? v.amount : (v.totalAmount || 0));
    if (document.getElementById('amount-local')) document.getElementById('amount-local').value = fmtNum(amt);
    if (document.getElementById('currency')) document.getElementById('currency').value = v.currency || 'SDG';
    
    let rate = v.rate || v.exchangeRate || 1.0;
    if (document.getElementById('exchange-rate')) document.getElementById('exchange-rate').value = rate;
    
    let amtForeign = (v.amountForeign !== undefined && v.amountForeign !== null) ? v.amountForeign : (parseNum(amt) / parseNum(rate));
    if (document.getElementById('amount-foreign')) document.getElementById('amount-foreign').value = fmtNum(amtForeign);
    
    if (document.getElementById('to-statement')) document.getElementById('to-statement').value = v.toStatement || v.statement || v.description || v.notes || '';
    if (document.getElementById('beneficiary-name')) document.getElementById('beneficiary-name').value = v.beneficiaryName || v.beneficiary || '';
    
    if (document.getElementById('from-account')) document.getElementById('from-account').value = fromAcc;
    if (document.getElementById('from-account-search')) document.getElementById('from-account-search').value = getAccountName(fromAcc) || fromAcc;
    if (window.fromAccChoice) window.fromAccChoice.setChoiceByValue(fromAcc);

    const costCenterEl = document.getElementById('cost-center');
    if (costCenterEl) {
        costCenterEl.value = v.costCenter || v.jobId || '';
        if (window.ccChoice) window.ccChoice.setChoiceByValue(v.costCenter || v.jobId || '');
    }

    if (document.getElementById('from-statement')) document.getElementById('from-statement').value = v.fromStatement || v.statement || v.description || '';

    // Set Payment Method
    const method = v.paymentMethod || v.method || (v.cheques && v.cheques.length > 0 ? 'check' : (v.bankTransfers && v.bankTransfers.length > 0 ? 'mobile' : 'cash'));
    const methodEl = document.querySelector(`input[name="payment_method"][value="${method}"]`); 
    if (methodEl) { methodEl.checked = true; } 
    else { const firstMethod = document.querySelector(`input[name="payment_method"]`); if(firstMethod) firstMethod.checked = true; }
    
    togglePaymentMethod();

    if (typeof loadVoucherPaymentDetails === 'function') {
        loadVoucherPaymentDetails(v);
    }
    
    updateCurrency(true); // pass true to prevent overwriting historical exchange rate
    updateBalances();
    updateLedgerButtons();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
    window.isLoadingVoucher = false;
}

function submitVoucher() {
    submitVoucherInner();
}

function showProfessionalSuccess(voucherId, fromName, toName, amountLocal, currency, title) {
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0'; overlay.style.left = '0';
    overlay.style.width = '100vw'; overlay.style.height = '100vh';
    overlay.style.background = 'rgba(5, 3, 8, 0.7)';
    overlay.style.backdropFilter = 'blur(12px)';
    overlay.style.zIndex = '999999';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.opacity = '0';
    overlay.style.transition = 'opacity 0.4s ease';

    const box = document.createElement('div');
    box.style.background = 'linear-gradient(145deg, #1e1b4b, #312e81)';
    box.style.padding = '40px 50px';
    box.style.borderRadius = '24px';
    box.style.boxShadow = '0 30px 60px rgba(0,0,0,0.8), 0 0 40px rgba(99, 102, 241, 0.5) inset';
    box.style.border = '1px solid rgba(167, 139, 250, 0.3)';
    box.style.textAlign = 'center';
    box.style.transform = 'scale(0.8) translateY(30px)';
    box.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
    box.style.color = '#fff';
    box.style.minWidth = '400px';

    box.innerHTML = `
        <style>
            @keyframes drawCheck {
                0% { stroke-dasharray: 0, 100; stroke-dashoffset: 0; opacity: 0; }
                50% { opacity: 1; }
                100% { stroke-dasharray: 100, 0; stroke-dashoffset: 0; opacity: 1; }
            }
            @keyframes pulseGlow {
                0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
                70% { box-shadow: 0 0 0 25px rgba(16, 185, 129, 0); }
                100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
            }
            @keyframes slideRight {
                0% { transform: translateX(-30px); opacity: 0; }
                100% { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideLeft {
                0% { transform: translateX(30px); opacity: 0; }
                100% { transform: translateX(0); opacity: 1; }
            }
            @keyframes arrowMove {
                0% { transform: translateX(0); }
                50% { transform: translateX(-15px); text-shadow: 0 0 15px rgba(99,102,241,1); }
                100% { transform: translateX(0); }
            }
        </style>
        <div style="width: 90px; height: 90px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; margin: 0 auto 25px auto; display: flex; align-items: center; justify-content: center; animation: pulseGlow 2s infinite; border: 4px solid #34d399;">
            <svg width="45" height="45" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray: 100; animation: drawCheck 0.8s ease-out forwards;">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h2 style="margin: 0 0 10px 0; font-size: 2rem; font-weight: 800; color: #34d399; text-shadow: 0 2px 8px rgba(0,0,0,0.5);">${title}</h2>
        <p style="margin: 0 0 30px 0; font-size: 1.2rem; color: #e2e8f0;">تم اعتماد السند المالي رقم <strong style="color: #fff; background: rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 6px;">${voucherId}</strong></p>
        
        <div style="background: rgba(0,0,0,0.4); padding: 20px; border-radius: 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 10px 25px rgba(0,0,0,0.5) inset;">
            <div style="flex: 1; text-align: right; animation: slideRight 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; animation-delay: 0.2s; opacity: 0;">
                <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 6px;">من حساب (الدافع)</div>
                <div style="font-weight: 900; color: #38bdf8; font-size: 1.1rem; line-height: 1.4;">${fromName}</div>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center; animation: arrowMove 1.5s ease-in-out infinite;">
                <div style="color: #34d399; font-weight: 900; font-size: 1.4rem; margin-bottom: 5px; text-shadow: 0 0 10px rgba(52,211,153,0.5);">${Number(amountLocal).toLocaleString()}</div>
                <div style="font-size: 0.8rem; font-weight: bold; color: #059669; background: #d1fae5; padding: 2px 10px; border-radius: 6px;">${currency}</div>
                <div style="font-size: 2rem; color: #818cf8; margin-top: 8px;">⬅️</div>
            </div>
            
            <div style="flex: 1; text-align: left; animation: slideLeft 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; animation-delay: 0.4s; opacity: 0;">
                <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 6px;">إلى حساب (المستفيد)</div>
                <div style="font-weight: 900; color: #f472b6; font-size: 1.1rem; line-height: 1.4;">${toName}</div>
            </div>
        </div>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        box.style.transform = 'scale(1) translateY(0)';
    });

    if (window.PlaySound && window.PlaySound.success) {
        window.PlaySound.success();
    }

    setTimeout(() => {
        overlay.style.opacity = '0';
        box.style.transform = 'scale(0.8) translateY(-40px)';
        setTimeout(() => {
            if (document.body.contains(overlay)) {
                document.body.removeChild(overlay);
            }
        }, 600);
    }, 2500);
}

function checkTransferDuplicate(tno, currentVoucherId) {
    if (!tno || String(tno).trim().length < 6) return false;
    const targetSuffix = String(tno).trim().slice(-6);
    const getTenantKey = window.getTenantKey || ((k) => k);
    const keys = [
        getTenantKey('erp_payments_v1'),
        getTenantKey('erp_receipts_v1'),
        getTenantKey('erp_comprehensive_payments_v1'),
        getTenantKey('erp_comprehensive_receipts_v1'),
        getTenantKey('erp_journal_v1')
    ];
    
    const isMatch = (no) => {
        let strNo = String(no || '').trim();
        return (strNo.length >= 6 && strNo.slice(-6) === targetSuffix);
    };

    for (let key of keys) {
        let vouchers = [];
        try {
            vouchers = JSON.parse(localStorage.getItem(key)) || [];
        } catch(e) { continue; }
        
        for (let v of vouchers) {
            if (!v || v.deleted) continue;
            if (currentVoucherId && (v.id === currentVoucherId || v.voucherNo === currentVoucherId)) continue;
            
            // 1. Check top-level bankTransfers
            if (v.bankTransfers && Array.isArray(v.bankTransfers)) {
                for (let bt of v.bankTransfers) {
                    if (isMatch(bt.transferNo)) return true;
                }
            } else if (v.mobileTransferNo && isMatch(v.mobileTransferNo)) {
                return true;
            }
            
            // 2. Check items array (Multi Payment)
            if (v.items && Array.isArray(v.items)) {
                for (let item of v.items) {
                    if (!item) continue;
                    if (item.bankTransfers && Array.isArray(item.bankTransfers)) {
                        for (let bt of item.bankTransfers) {
                            if (isMatch(bt.transferNo)) return true;
                        }
                    } else if (item.transferNo && isMatch(item.transferNo)) {
                        return true;
                    }
                }
            }
            
            // 3. Check details array (Bulk Payment)
            if (v.details && Array.isArray(v.details)) {
                for (let det of v.details) {
                    if (!det) continue;
                    if (det.bankTransfers && Array.isArray(det.bankTransfers)) {
                        for (let bt of det.bankTransfers) {
                            if (isMatch(bt.transferNo)) return true;
                        }
                    } else if (det.transferNo && isMatch(det.transferNo)) {
                        return true;
                    }
                }
            }
            
            // 4. Check subTransactions (Journal)
            if (v.subTransactions && Array.isArray(v.subTransactions)) {
                for (let sub of v.subTransactions) {
                    if (!sub) continue;
                    if (sub.transferNo && isMatch(sub.transferNo)) return true;
                }
            }
        }
    }
    return false; // Not duplicated
}

function submitVoucherInner() {
    try { // WRAPPED
    const amtLocal = parseNum(document.getElementById('amount-local').value);
    const toAcc = document.getElementById('to-account').value;
    const fromAcc = document.getElementById('from-account').value;

    if (amtLocal <= 0) {
        showToast("لا يمكن اعتماد سند بقيمة صفر", "error");
        return;
    }
    if (!toAcc) {
        showToast("يرجى اختيار الحساب المستفيد", "error");
        return;
    }
    if (!fromAcc) {
        showToast("يرجى اختيار حساب الدفع (الدافع)", "error");
        return;
    }

    const _docDateStr = document.getElementById('hdr-date') ? document.getElementById('hdr-date').value : new Date().toISOString().split('T')[0];
    if (!window.canCreateRecord(_docDateStr)) return;

    if (window.isSubmitting) return;
    window.isSubmitting = true;
    setTimeout(() => { window.isSubmitting = false; }, 2000);

    const method = document.querySelector('input[name="payment_method"]:checked').value;

    const voucherData = {
        createdBy: (JSON.parse(localStorage.getItem('user')) || {}).id || 'unknown',
        id: document.getElementById('voucher-number').innerText,
        date: document.getElementById('hdr-date').value,
        time: new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
        type: 'single_payment',
        
        toAccount: toAcc,
        amountLocal: amtLocal,
        currency: document.getElementById('currency').value,
        rate: parseNum(document.getElementById('exchange-rate').value),
        amountForeign: parseNum(document.getElementById('amount-foreign').value),
        toStatement: document.getElementById('to-statement').value,
        beneficiaryName: document.getElementById('beneficiary-name').value,
        
        fromAccount: document.getElementById('from-account').value,
        fromStatement: document.getElementById('from-statement').value,
        costCenter: document.getElementById('cost-center') ? document.getElementById('cost-center').value : '',
        
        paymentMethod: method,
        
        timestamp: new Date().toISOString(),
        systemExchangeRate: (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 1) || 1
    };

    if (method === 'mobile') {
        const trContainer = document.getElementById('transfers-container');
        if (trContainer) {
            const allTRows = trContainer.querySelectorAll('.transfer-row');
            if (allTRows.length > 1) {
                allTRows.forEach(r => {
                    const a = parseNum(r.querySelector('.transfer-amount')?.value);
                    if (a <= 0) r.remove();
                });
                refreshTransferRowButtons();
            }
        }
        const transferRows = document.querySelectorAll('.transfer-row');
        let transfers = [];
        let totalTransfersAmount = 0;
        let hasInvalidTransfer = false;

        transferRows.forEach((row, idx) => {
            const trNo = row.querySelector('.transfer-no').value.trim();
            const trNote = row.querySelector('.transfer-note').value.trim();
            const trAmt = parseNum(row.querySelector('.transfer-amount').value);
            const trStmt = row.querySelector('.transfer-statement')?.value.trim() || '';

            if (trAmt <= 0) {
                showToast(`السطر ${idx + 1}: يرجى إدخال مبلغ التحويل`, "error");
                row.querySelector('.transfer-amount').focus();
                hasInvalidTransfer = true;
                return;
            }

            if (!trNo) {
                showToast(`السطر ${idx + 1}: يرجى إدخال رقم حساب التحويل`, "error");
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (trNo.length < 6) {
                showLengthWarningModal(trNo);
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }
            if (checkTransferDuplicate(trNo, document.getElementById('voucher-number').innerText)) {
                showDuplicateWarningModal(trNo);
                row.querySelector('.transfer-no').focus();
                hasInvalidTransfer = true;
                return;
            }

            transfers.push({ transferNo: trNo, note: trNote, amount: trAmt, statement: trStmt });
            totalTransfersAmount += trAmt;
        });

        if (hasInvalidTransfer) return;

        if (Math.abs(totalTransfersAmount - amtLocal) > 0.01) {
            showToast("مجموع مبالغ التحويلات لا يساوي مبلغ السند الإجمالي.", "error");
            return;
        }

        voucherData.bankTransfers = transfers;
        if (transfers.length > 0) {
            voucherData.mobileTransferNo = transfers[0].transferNo;
            voucherData.mobileNote = transfers[0].note;
        }
    } else if (method === 'check') {
        const chContainer = document.getElementById('cheques-container');
        if (chContainer) {
            const allCRows = chContainer.querySelectorAll('.cheque-row');
            if (allCRows.length > 1) {
                allCRows.forEach(r => {
                    const a = parseNum(r.querySelector('.cheque-amount')?.value);
                    if (a <= 0) r.remove();
                });
                refreshChequeRowButtons();
            }
        }
        const chequeRows = document.querySelectorAll('.cheque-row');
        if (chequeRows.length === 0) {
            showToast("يرجى إضافة بيانات الشيك (المبلغ، اسم البنك، رقم الشيك)", "error");
            return;
        }

        let cheques = [];
        let totalChequesAmount = 0;
        let hasInvalidCheque = false;

        chequeRows.forEach((row, idx) => {
            if (hasInvalidCheque) return;
            const chNoInput = row.querySelector('.cheque-no');
            const chBranchInput = row.querySelector('.cheque-branch');
            const chAmtInput = row.querySelector('.cheque-amount');
            
            const chNo = (chNoInput?.value || '').trim();
            const chBranch = (chBranchInput?.value || '').trim();
            const chDate = row.querySelector('.cheque-date')?.value || '';
            const chAmt = parseNum(chAmtInput?.value);
            const chStmt = row.querySelector('.cheque-statement')?.value.trim() || '';

            if (chAmt <= 0) {
                showToast(`السطر ${idx + 1}: يرجى إدخال مبلغ الشيك`, "error");
                if (chAmtInput) {
                    chAmtInput.focus();
                    chAmtInput.style.borderColor = '#ef4444';
                    setTimeout(() => { chAmtInput.style.borderColor = 'rgba(255,255,255,0.1)'; }, 3500);
                }
                hasInvalidCheque = true;
                return;
            }

            if (!chBranch) {
                showToast(`السطر ${idx + 1}: اسم البنك إجباري، يرجى كتابة اسم البنك`, "error");
                if (chBranchInput) {
                    chBranchInput.focus();
                    chBranchInput.style.borderColor = '#ef4444';
                    setTimeout(() => { chBranchInput.style.borderColor = 'rgba(255,255,255,0.1)'; }, 3500);
                }
                hasInvalidCheque = true;
                return;
            }

            if (!chNo) {
                showToast(`السطر ${idx + 1}: رقم الشيك إجباري، لابد من كتابة رقم الشيك`, "error");
                if (chNoInput) {
                    chNoInput.focus();
                    chNoInput.style.borderColor = '#ef4444';
                    setTimeout(() => { chNoInput.style.borderColor = 'rgba(255,255,255,0.1)'; }, 3500);
                }
                hasInvalidCheque = true;
                return;
            }

            cheques.push({ checkNo: chNo, checkBranch: chBranch, checkDate: chDate, amount: chAmt, statement: chStmt });
            totalChequesAmount += chAmt;
        });

        if (hasInvalidCheque) return;

        if (Math.abs(totalChequesAmount - amtLocal) > 0.01) {
            showToast("مجموع مبالغ الشيكات لا يساوي مبلغ السند الإجمالي.", "error");
            return;
        }

        voucherData.cheques = cheques;
        if (cheques.length > 0) {
            voucherData.checkNo = cheques[0].checkNo;
            voucherData.checkBranch = cheques[0].checkBranch;
            voucherData.checkDate = cheques[0].checkDate;
            voucherData.checkDueDate = '';
            voucherData.checkRef = '';
            voucherData.checkRefNo = '';
            voucherData.checkSettleLocal = false;
        }
    } else if (method === 'hybrid') {
        const trContainer = document.getElementById('transfers-container');
        if (trContainer) {
            const allTRows = trContainer.querySelectorAll('.transfer-row');
            if (allTRows.length > 1) {
                allTRows.forEach(r => {
                    const a = parseNum(r.querySelector('.transfer-amount')?.value);
                    if (a <= 0) r.remove();
                });
                refreshTransferRowButtons();
            }
        }
        const chContainer = document.getElementById('cheques-container');
        if (chContainer) {
            const allCRows = chContainer.querySelectorAll('.cheque-row');
            if (allCRows.length > 1) {
                allCRows.forEach(r => {
                    const a = parseNum(r.querySelector('.cheque-amount')?.value);
                    if (a <= 0) r.remove();
                });
                refreshChequeRowButtons();
            }
        }

        const transferRows = document.querySelectorAll('.transfer-row');
        let transfers = [];
        let totalTransfersAmount = 0;
        let hasInvalidTransfer = false;

        transferRows.forEach((row, idx) => {
            if (hasInvalidTransfer) return;
            const trNoInput = row.querySelector('.transfer-no');
            const trAmtInput = row.querySelector('.transfer-amount');

            const trNo = (trNoInput?.value || '').trim();
            const trNote = (row.querySelector('.transfer-note')?.value || '').trim();
            const trAmt = parseNum(trAmtInput?.value);
            const trStmt = row.querySelector('.transfer-statement')?.value.trim() || '';

            if (trAmt <= 0) {
                // If amount is 0 or empty, skip this row in hybrid mode
                return;
            }

            if (!trNo) {
                showToast(`السطر ${idx + 1}: يرجى إدخال رقم حساب التحويل`, "error");
                if (trNoInput) trNoInput.focus();
                hasInvalidTransfer = true;
                return;
            }
            if (trNo.length < 6) {
                showLengthWarningModal(trNo);
                if (trNoInput) trNoInput.focus();
                hasInvalidTransfer = true;
                return;
            }
            if (checkTransferDuplicate(trNo, document.getElementById('voucher-number').innerText)) {
                showDuplicateWarningModal(trNo);
                if (trNoInput) trNoInput.focus();
                hasInvalidTransfer = true;
                return;
            }

            transfers.push({ transferNo: trNo, note: trNote, amount: trAmt, statement: trStmt });
            totalTransfersAmount += trAmt;
        });

        if (hasInvalidTransfer) return;

        const chequeRows = document.querySelectorAll('.cheque-row');
        let cheques = [];
        let totalChequesAmount = 0;
        let hasInvalidCheque = false;

        chequeRows.forEach((row, idx) => {
            if (hasInvalidCheque) return;
            const chNoInput = row.querySelector('.cheque-no');
            const chBranchInput = row.querySelector('.cheque-branch');
            const chAmtInput = row.querySelector('.cheque-amount');
            
            const chNo = (chNoInput?.value || '').trim();
            const chBranch = (chBranchInput?.value || '').trim();
            const chDate = row.querySelector('.cheque-date')?.value || '';
            const chAmt = parseNum(chAmtInput?.value);
            const chStmt = row.querySelector('.cheque-statement')?.value.trim() || '';

            if (chAmt <= 0) {
                // If amount is 0 or empty, skip this row in hybrid mode
                return;
            }

            if (!chBranch) {
                showToast(`السطر ${idx + 1}: اسم البنك إجباري، يرجى كتابة اسم البنك`, "error");
                if (chBranchInput) {
                    chBranchInput.focus();
                    chBranchInput.style.borderColor = '#ef4444';
                    setTimeout(() => { chBranchInput.style.borderColor = 'rgba(255,255,255,0.1)'; }, 3500);
                }
                hasInvalidCheque = true;
                return;
            }

            if (!chNo) {
                showToast(`السطر ${idx + 1}: رقم الشيك إجباري، لابد من كتابة رقم الشيك`, "error");
                if (chNoInput) {
                    chNoInput.focus();
                    chNoInput.style.borderColor = '#ef4444';
                    setTimeout(() => { chNoInput.style.borderColor = 'rgba(255,255,255,0.1)'; }, 3500);
                }
                hasInvalidCheque = true;
                return;
            }

            cheques.push({ checkNo: chNo, checkBranch: chBranch, checkDate: chDate, amount: chAmt, statement: chStmt });
            totalChequesAmount += chAmt;
        });

        if (hasInvalidCheque) return;

        if (Math.abs((totalTransfersAmount + totalChequesAmount) - amtLocal) > 0.01) {
            showToast("مجموع مبالغ التحويلات والشيكات لا يساوي مبلغ السند الإجمالي.", "error");
            return;
        }

        voucherData.bankTransfers = transfers;
        if (transfers.length > 0) {
            voucherData.mobileTransferNo = transfers[0].transferNo;
            voucherData.mobileNote = transfers[0].note;
        }
        voucherData.cheques = cheques;
        if (cheques.length > 0) {
            voucherData.checkNo = cheques[0].checkNo;
            voucherData.checkBranch = cheques[0].checkBranch;
            voucherData.checkDate = cheques[0].checkDate;
            voucherData.checkDueDate = '';
            voucherData.checkRef = '';
            voucherData.checkRefNo = '';
            voucherData.checkSettleLocal = false;
        }
    }

    const getTenantKey = window.getTenantKey || ((k) => k);
    singlePaymentsData = window.getAllSinglePayments();
    
    // Balance update logic
    function applyPaymentEffect(fromAccId, toAccId, amount, isRevert = false) {
        if (!fromAccId || !toAccId) return;
        let multiplier = isRevert ? -1 : 1;
        let amtFrom = -amount * multiplier; // source account decreases on payment
        let amtToPositive = amount * multiplier; // receiving account INCREASES on payment

        // Helper function for multi-tenant local storage
        const loadEntity = (key) => {
            try { return JSON.parse(localStorage.getItem(getTenantKey(key)) || localStorage.getItem(key)) || []; } catch(e) { return []; }
        };
        const saveEntity = (key, data) => {
            localStorage.setItem(key, JSON.stringify(data));
        };

        // 1. Update fromAccount (Safe/Bank) — balance DECREASES
        if (fromAccId.startsWith('safe-')) {
            let id = fromAccId.substring(5);
            let safes = loadEntity('erp_safes_v1');
            let idx = safes.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtFrom;
                saveEntity('erp_safes_v1', safes);
            }
        } else if (fromAccId.startsWith('bank-')) {
            let id = fromAccId.substring(5);
            let banks = loadEntity('erp_banks_v1');
            let idx = banks.findIndex(b => String(b.id) === String(id));
            if (idx > -1) {
                banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtFrom;
                saveEntity('erp_banks_v1', banks);
            }
        } else if (fromAccId.startsWith('cust-')) {
            let id = fromAccId.substring(5);
            let custs = loadEntity('erp_customers_v1');
            let idx = custs.findIndex(c => String(c.id) === String(id));
            if (idx > -1) {
                custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amtFrom;
                saveEntity('erp_customers_v1', custs);
            }
        } else if (fromAccId.startsWith('supp-')) {
            let id = fromAccId.substring(5);
            let sups = loadEntity('erp_suppliers_v1');
            let idx = sups.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtFrom;
                saveEntity('erp_suppliers_v1', sups);
            }
        } else if (fromAccId.startsWith('exp-')) {
            let id = fromAccId.substring(4);
            let accounts = loadEntity('erp_accounts_v1');
            let idx = accounts.findIndex(a => String(a.id) === String(id));
            if (idx > -1) {
                accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amtFrom;
                saveEntity('erp_accounts_v1', accounts);
            }
        }

        // 2. Update toAccount (Beneficiary) — balance INCREASES
        if (toAccId.startsWith('cust-')) {
            let id = toAccId.substring(5);
            let custs = loadEntity('erp_customers_v1');
            let idx = custs.findIndex(c => String(c.id) === String(id));
            if (idx > -1) {
                custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amtToPositive;
                saveEntity('erp_customers_v1', custs);
            }
        } else if (toAccId.startsWith('supp-')) {
            let id = toAccId.substring(5);
            let sups = loadEntity('erp_suppliers_v1');
            let idx = sups.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtToPositive;
                saveEntity('erp_suppliers_v1', sups);
            }
        } else if (toAccId.startsWith('bank-')) {
            let id = toAccId.substring(5);
            let banks = loadEntity('erp_banks_v1');
            let idx = banks.findIndex(b => String(b.id) === String(id));
            if (idx > -1) {
                banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtToPositive;
                saveEntity('erp_banks_v1', banks);
            }
        } else if (toAccId.startsWith('safe-')) {
            let id = toAccId.substring(5);
            let safes = loadEntity('erp_safes_v1');
            let idx = safes.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtToPositive;
                saveEntity('erp_safes_v1', safes);
            }
        } else if (toAccId.startsWith('exp-')) {
            let id = toAccId.substring(4);
            let accounts = loadEntity('erp_accounts_v1');
            let idx = accounts.findIndex(a => String(a.id) === String(id));
            if (idx > -1) {
                accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amtToPositive;
                saveEntity('erp_accounts_v1', accounts);
            }
        }
    }

        let fromSearch = document.getElementById('from-account-search');
        let fromName = fromSearch && fromSearch.value ? fromSearch.value : voucherData.fromAccount;
        let toSearch = document.getElementById('to-account-search');
        let toName = toSearch && toSearch.value ? toSearch.value : voucherData.toAccount;
        
        if (currentViewedIndex !== -1) {
            if (!window.canEditRecord(voucherData.date, 'finance')) return;
            
            // Revert old payment effect
            const oldVoucher = singlePaymentsData[currentViewedIndex];
            applyPaymentEffect(oldVoucher.fromAccount, oldVoucher.toAccount, oldVoucher.amountLocal, true);
            
            // Apply new payment effect
            applyPaymentEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
            
            singlePaymentsData[currentViewedIndex] = voucherData;
            const tKey = getTenantKey('erp_single_payments_v1');
            localStorage.setItem(tKey, JSON.stringify(singlePaymentsData));
            window.SERVER_SINGLE_PAYMENTS = singlePaymentsData;
            if (typeof window.rebuildGLFromAllTables === 'function') window.rebuildGLFromAllTables(false);
            showProfessionalSuccess(voucherData.id, fromName, toName, voucherData.amountLocal, voucherData.currency, 'تم التعديل بنجاح');
        } else {
            applyPaymentEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
            singlePaymentsData.push(voucherData);
            currentViewedIndex = singlePaymentsData.length - 1; // Keep the saved voucher on screen
            const tKey = getTenantKey('erp_single_payments_v1');
            localStorage.setItem(tKey, JSON.stringify(singlePaymentsData));
            window.SERVER_SINGLE_PAYMENTS = singlePaymentsData;
            const currentSeq = parseInt(localStorage.getItem(getTenantKey('erp_single_payment_seq')) || localStorage.getItem('erp_single_payment_seq') || '0');
            localStorage.setItem('erp_single_payment_seq', currentSeq + 1);
            if (typeof window.rebuildGLFromAllTables === 'function') window.rebuildGLFromAllTables(false);
            showProfessionalSuccess(voucherData.id, fromName, toName, voucherData.amountLocal, voucherData.currency, 'تم الحفظ بنجاح');
        }

        // setTimeout(() => { resetVoucherSilent(); }, 2500); // Do not erase the form after saving based on user feedback
    } catch (err) {
        console.error(err);
        alert("حدث خطأ أثناء الحفظ: " + err.message + "في السطر: " + err.stack);
    }
}

// ═══ Vector Barcode Generator (Code 128 Standard) ═══
window.generateBarcodeSVG = function(vNo) {
    const code128Patterns = [
        "212222", "222122", "222221", "121223", "121322", "131222", "122213", "122312", "132212", "221213",
        "221312", "231212", "112232", "122132", "122231", "113222", "123122", "123221", "223211", "221132",
        "221231", "213212", "223112", "312131", "311222", "321122", "321221", "312212", "322112", "322211",
        "212123", "212321", "232121", "111323", "131123", "131321", "112313", "132113", "132311", "211313",
        "231113", "231311", "112133", "112331", "132131", "113123", "113321", "133121", "313121", "211331",
        "231131", "213113", "213311", "213131", "311123", "311321", "331121", "312113", "312311", "332111",
        "314111", "221411", "431111", "111224", "111422", "121124", "121421", "141122", "141221", "112214",
        "112412", "122114", "122411", "142112", "142211", "241211", "221114", "413111", "241112", "134111",
        "111242", "121142", "121241", "114212", "124112", "124211", "411212", "421112", "421211", "212141",
        "214121", "412121", "111143", "111341", "131141", "114113", "114311", "411113", "411311", "113141",
        "114131", "311141", "411131", "211412", "211214", "211232", "2331112"
    ];
    let clean = (vNo || 'E26090001').replace(/[^\x20-\x7E]/g, '');
    if (!clean) clean = "E26090001";
    let indices = [104]; // Code 128B start
    for (let i = 0; i < clean.length; i++) {
        indices.push(clean.charCodeAt(i) - 32);
    }
    let checksum = 104;
    for (let i = 1; i < indices.length; i++) {
        checksum += indices[i] * i;
    }
    indices.push(checksum % 103);
    indices.push(106); // Stop symbol

    let modules = [];
    indices.forEach(idx => {
        let pattern = code128Patterns[idx];
        if (pattern) {
            for (let j = 0; j < pattern.length; j++) {
                let width = parseInt(pattern[j]);
                let isBar = (j % 2 === 0);
                for (let k = 0; k < width; k++) {
                    modules.push(isBar);
                }
            }
        }
    });

    let rects = '';
    const barWidth = 1.25;
    const quietZone = 6;
    for (let i = 0; i < modules.length; i++) {
        if (modules[i]) {
            rects += `<rect x="${(quietZone + i * barWidth).toFixed(1)}" y="0" width="${barWidth}" height="26" fill="#0f172a" />`;
        }
    }
    const totalW = (quietZone * 2 + modules.length * barWidth).toFixed(1);
    return `<svg width="${totalW}" height="26" viewBox="0 0 ${totalW} 26" xmlns="http://www.w3.org/2000/svg" style="display:inline-block; vertical-align:middle;">${rects}</svg>`;
};

// ═══ Arabic Tafqeet Generator ═══
window.formatArabicTafqeet = window.formatArabicTafqeet || function(amount, currencyCode = 'SDG') {
    const rawNum = typeof amount === 'number' ? amount : (parseFloat(String(amount).replace(/,/g, '')) || 0);
    const getCurrencyNames = (c) => {
        const norm = (c || 'SDG').toUpperCase();
        switch (norm) {
            case 'USD': return { unit: 'دولار أمريكي', sub: 'سنت' };
            case 'AED': return { unit: 'درهم إماراتي', sub: 'فلس' };
            case 'SAR': return { unit: 'ريال سعودي', sub: 'هللة' };
            case 'QAR': return { unit: 'ريال قطري', sub: 'درهم' };
            case 'EUR': return { unit: 'يورو', sub: 'سنت' };
            case 'GBP': return { unit: 'جنيه إسترليني', sub: 'بنس' };
            case 'SDG':
            default:
                return { unit: 'جنيه سوداني', sub: 'قرش' };
        }
    };
    const cInfo = getCurrencyNames(currencyCode);
    if (rawNum <= 0) return `فقط صفر ${cInfo.unit} لا غير`;
    const intPart = Math.floor(rawNum);
    const decPart = Math.round((rawNum - intPart) * 100);
    const ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة',
        'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
    const tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    const hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];
    function convertThreeDigits(num) {
        let str = '';
        const h = Math.floor(num / 100);
        const rem = num % 100;
        if (h > 0) str += hundreds[h];
        if (rem > 0) {
            if (str !== '') str += ' و';
            if (rem < 20) str += ones[rem];
            else {
                const t = Math.floor(rem / 10);
                const o = rem % 10;
                if (o > 0) str += ones[o] + ' و' + tens[t];
                else str += tens[t];
            }
        }
        return str;
    }
    const scales = [
        { val: 1000000000000, single: 'تريليون', two: 'تريليونان', plural: 'تريليونات' },
        { val: 1000000000, single: 'مليار', two: 'ملياران', plural: 'مليارات' },
        { val: 1000000, single: 'مليون', two: 'مليونان', plural: 'ملايين' },
        { val: 1000, single: 'ألف', two: 'ألفان', plural: 'آلاف' }
    ];
    let remaining = intPart;
    let parts = [];
    for (const scale of scales) {
        if (remaining >= scale.val) {
            const count = Math.floor(remaining / scale.val);
            remaining = remaining % scale.val;
            if (count === 1) parts.push(scale.single);
            else if (count === 2) parts.push(scale.two);
            else if (count >= 3 && count <= 10) parts.push(convertThreeDigits(count) + ' ' + scale.plural);
            else parts.push(convertThreeDigits(count) + ' ' + scale.single);
        }
    }
    if (remaining > 0) parts.push(convertThreeDigits(remaining));
    let result = parts.length > 0 ? parts.join(' و') : 'صفر';
    result = `فقط ${result} ${cInfo.unit}`;
    if (decPart > 0) result += ` و ${decPart} ${cInfo.sub}`;
    result += ' لا غير';
    return result;
};

// ═══ Account Display Name Resolver ═══
function resolveAccountDisplayName(accId, isFrom = false) {
    if (isFrom) {
        const fromSearchVal = document.getElementById('from-account-search')?.value?.trim();
        const fromIdVal = document.getElementById('from-account')?.value?.trim();
        if (fromSearchVal && (!accId || accId === fromIdVal)) return fromSearchVal;
    } else {
        const toSearchVal = document.getElementById('to-account-search')?.value?.trim();
        const toIdVal = document.getElementById('to-account')?.value?.trim();
        if (toSearchVal && (!accId || accId === toIdVal)) return toSearchVal;
    }
    const name = getAccountName(accId);
    if (name && name !== accId) return name;
    try {
        const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
        const c = custs.find(x => String(x.id) === String(accId) || `cust-${x.id}` === String(accId));
        if (c) return c.name;
        const sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
        const s = sups.find(x => String(x.id) === String(accId) || `supp-${x.id}` === String(accId));
        if (s) return s.name;
        const banks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
        const b = banks.find(x => String(x.id) === String(accId) || `bank-${x.id}` === String(accId));
        if (b) return b.name;
        const safes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
        const sf = safes.find(x => String(x.id) === String(accId) || `safe-${x.id}` === String(accId));
        if (sf) return sf.name;
        const accs = JSON.parse(localStorage.getItem('erp_accounts_v1') || '[]');
        const a = accs.find(x => String(x.id) === String(accId) || `exp-${x.id}` === String(accId));
        if (a) return a.name;
    } catch(e) {}
    return accId || '—';
}

// ═══ Build Voucher Object from Screen Inputs (Fallback) ═══
function buildVoucherFromCurrentForm(voucherId) {
    const vNo = voucherId || document.getElementById('voucher-number')?.innerText?.trim() || 'E26090001';
    const date = document.getElementById('hdr-date')?.value || new Date().toISOString().split('T')[0];
    const amtLocal = parseNum(document.getElementById('amount-local')?.value);
    const amtForeign = parseNum(document.getElementById('amount-foreign')?.value);
    const curSelect = document.getElementById('currency');
    const currency = (curSelect && curSelect.value) ? curSelect.value : 'SDG';
    const rate = parseNum(document.getElementById('exchange-rate')?.value) || 1;
    const fromAcc = document.getElementById('from-account')?.value || 'safe-001';
    const toAcc = document.getElementById('to-account')?.value || '';
    const toStatement = document.getElementById('to-statement')?.value || '';
    const fromStatement = document.getElementById('from-statement')?.value || '';
    const beneficiaryName = document.getElementById('beneficiary-name')?.value || '';
    const costCenter = document.getElementById('cost-center')?.value || '';

    let method = 'cash';
    const bankActive = document.getElementById('toggle-bank')?.classList.contains('active');
    const chequeActive = document.getElementById('toggle-cheque')?.classList.contains('active');
    if (bankActive && chequeActive) method = 'hybrid';
    else if (bankActive) method = 'mobile';
    else if (chequeActive) method = 'check';

    let bankTransfers = [];
    document.querySelectorAll('.transfer-row').forEach(row => {
        const trNo = (row.querySelector('.transfer-no')?.value || '').trim();
        const note = (row.querySelector('.transfer-note')?.value || '').trim();
        const amount = parseNum(row.querySelector('.transfer-amount')?.value);
        const statement = (row.querySelector('.transfer-statement')?.value || '').trim();
        if (amount > 0 || trNo) {
            bankTransfers.push({ transferNo: trNo, note, amount, statement });
        }
    });

    let cheques = [];
    document.querySelectorAll('.cheque-row').forEach(row => {
        const checkNo = (row.querySelector('.cheque-no')?.value || '').trim();
        const checkBranch = (row.querySelector('.cheque-branch')?.value || '').trim();
        const checkDate = row.querySelector('.cheque-date')?.value || '';
        const amount = parseNum(row.querySelector('.cheque-amount')?.value);
        const statement = (row.querySelector('.cheque-statement')?.value || '').trim();
        if (amount > 0 || checkNo) {
            cheques.push({ checkNo, checkBranch, checkDate, amount, statement });
        }
    });

    return {
        id: vNo,
        date: date,
        time: new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }),
        amountLocal: amtLocal,
        amountForeign: amtForeign,
        currency: currency,
        rate: rate,
        fromAccount: fromAcc,
        fromStatement: fromStatement,
        toAccount: toAcc,
        toStatement: toStatement,
        beneficiaryName: beneficiaryName,
        costCenter: costCenter,
        paymentMethod: method,
        bankTransfers: bankTransfers,
        cheques: cheques,
        mobileTransferNo: bankTransfers[0]?.transferNo || '',
        mobileNote: bankTransfers[0]?.note || '',
        checkNo: cheques[0]?.checkNo || '',
        checkBranch: cheques[0]?.checkBranch || '',
        checkDate: cheques[0]?.checkDate || ''
    };
}

// ═══ QR Code Fetcher with Multiple Fallbacks ═══
window._voucherQrCache = window._voucherQrCache || {};
async function getVoucherQRCode(text) {
    if (window._voucherQrCache[text]) return window._voucherQrCache[text];
    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : 'wa_tenant_12';
        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=generate-qr&text=${encodeURIComponent(text)}`);
        const data = await res.json();
        if (data && data.success && data.dataUrl) {
            window._voucherQrCache[text] = data.dataUrl;
            return data.dataUrl;
        }
    } catch(e) {}
    const fallbackUrl = `https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=1&data=${encodeURIComponent(text)}`;
    window._voucherQrCache[text] = fallbackUrl;
    return fallbackUrl;
}

// ═══ Master Voucher Document HTML Builder (Ultra Luxury & Complete Details) ═══
window.generateVoucherDocumentHTML = function(voucher, qrDataUrl) {
    if (!voucher) voucher = {};

    let cfg = {};
    try {
        cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
    } catch(e) {}

    const companyName = cfg.companyName || document.getElementById('header-company-name-top')?.innerText?.trim() || 'شركة السجانة التجارية';
    const companyPhone = cfg.phone || cfg.companyPhone || '';
    const companyAddress = cfg.address || cfg.companyAddress || '';

    let logoSrc = cfg.logoBase64 || cfg.logo || '';
    if (!logoSrc) {
        const headerLogo = document.querySelector('.company-logo, #hdr-c-logo, .brand-logo img');
        if (headerLogo && headerLogo.src && !headerLogo.src.includes('placeholder')) {
            logoSrc = headerLogo.src;
        }
    }
    const logoHtml = logoSrc ? `<img src="${logoSrc}" style="max-height: 70px; max-width: 170px; object-fit: contain; border-radius: 8px;">` : `<div style="font-size: 20px; font-weight: 900; color: #0f172a; line-height: 1.2;">${companyName}</div>`;

    const fromAccName = resolveAccountDisplayName(voucher.fromAccount, true);
    const toAccName = resolveAccountDisplayName(voucher.toAccount, false);
    const contact = getVoucherContactDetails(voucher.toAccount);
    const contactPhone = contact?.phone || contact?.whatsapp || '';

    const amtLocal = parseNum(voucher.amountLocal);
    const currency = voucher.currency || 'SDG';
    const tafqeetText = window.formatArabicTafqeet(amtLocal, currency);
    const isForeign = currency !== 'SDG' && parseNum(voucher.amountForeign) > 0;

    const barcodeSvg = window.generateBarcodeSVG(voucher.id);
    const qrPlaceholder = qrDataUrl || `https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=1&data=${encodeURIComponent('VOUCHER:' + voucher.id)}`;

    let methodLabel = 'نقداً عبر الخزينة';
    if (voucher.paymentMethod === 'mobile') methodLabel = 'تحويل بنكي / مصرفي (Bank Transfer)';
    else if (voucher.paymentMethod === 'check') methodLabel = 'شيك مصرفي (Bank Cheque)';
    else if (voucher.paymentMethod === 'hybrid') methodLabel = 'سداد هجين (تحويل مصرفي + شيك)';
    else if (voucher.paymentMethod === 'card') methodLabel = 'بطاقة بنكية (Bank Card)';

    // Bank Transfers Table
    let transfers = voucher.bankTransfers || [];
    if (transfers.length === 0 && voucher.mobileTransferNo) {
        transfers = [{ transferNo: voucher.mobileTransferNo, note: voucher.mobileNote || '', amount: amtLocal, statement: '' }];
    }
    let transfersHtml = '';
    if (transfers.length > 0) {
        let trRows = transfers.map((tr, i) => `
            <tr style="border-bottom: 1px solid #e2e8f0; font-size: 0.85rem;">
                <td style="padding: 6px 10px; text-align: center; color: #64748b; font-weight: bold;">${i + 1}</td>
                <td style="padding: 6px 10px; font-family: monospace; font-size: 0.95rem; font-weight: 900; color: #0f766e;" dir="ltr">${tr.transferNo || '—'}</td>
                <td style="padding: 6px 10px; font-weight: 800; color: #1e293b;">${tr.note || 'تحويل مصرفي'}</td>
                <td style="padding: 6px 10px; font-weight: 900; color: #047857; text-align: left;" dir="ltr">${fmtNum(tr.amount)} SDG</td>
                <td style="padding: 6px 10px; color: #475569;">${tr.statement || '—'}</td>
            </tr>
        `).join('');

        transfersHtml = `
            <div style="margin-bottom: 12px; border: 1.5px solid #0d9488; border-radius: 8px; overflow: hidden;">
                <div style="background: linear-gradient(90deg, #0f766e, #115e59); color: #fff; padding: 6px 12px; font-size: 0.85rem; font-weight: 900; display: flex; justify-content: space-between; align-items: center;">
                    <span>💳 تفاصيل التحويلات البنكية المصرفية:</span>
                    <span style="font-size: 0.72rem; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px;">عمليات معتمدة ✅</span>
                </div>
                <table style="width: 100%; border-collapse: collapse; background: #f0fdfa;">
                    <thead>
                        <tr style="background: #ccfbf1; color: #0f766e; font-size: 0.8rem; font-weight: 900; border-bottom: 1.5px solid #0d9488;">
                            <th style="padding: 5px 10px; width: 5%; text-align: center;">#</th>
                            <th style="padding: 5px 10px; width: 28%; text-align: right;">رقم التحويلة / الإشعار</th>
                            <th style="padding: 5px 10px; width: 25%; text-align: right;">البنك / المحفظة</th>
                            <th style="padding: 5px 10px; width: 22%; text-align: left;">المبلغ المحول</th>
                            <th style="padding: 5px 10px; width: 20%; text-align: right;">البيان</th>
                        </tr>
                    </thead>
                    <tbody>${trRows}</tbody>
                </table>
            </div>
        `;
    }

    // Cheques Table
    let cheques = voucher.cheques || [];
    if (cheques.length === 0 && voucher.checkNo) {
        cheques = [{ checkNo: voucher.checkNo, checkBranch: voucher.checkBranch || '', checkDate: voucher.checkDate || '', amount: amtLocal, statement: '' }];
    }
    let chequesHtml = '';
    if (cheques.length > 0) {
        let chRows = cheques.map((ch, i) => `
            <tr style="border-bottom: 1px solid #e2e8f0; font-size: 0.85rem;">
                <td style="padding: 6px 10px; text-align: center; color: #64748b; font-weight: bold;">${i + 1}</td>
                <td style="padding: 6px 10px; font-family: monospace; font-size: 0.95rem; font-weight: 900; color: #b45309;" dir="ltr">${ch.checkNo || '—'}</td>
                <td style="padding: 6px 10px; font-weight: 800; color: #1e293b;">${ch.checkBranch || '—'}</td>
                <td style="padding: 6px 10px; font-weight: 700; color: #0f172a;" dir="ltr">${ch.checkDate || '—'}</td>
                <td style="padding: 6px 10px; font-weight: 900; color: #b91c1c; text-align: left;" dir="ltr">${fmtNum(ch.amount)} SDG</td>
                <td style="padding: 6px 10px; color: #475569;">${ch.statement || '—'}</td>
            </tr>
        `).join('');

        chequesHtml = `
            <div style="margin-bottom: 12px; border: 1.5px solid #d97706; border-radius: 8px; overflow: hidden;">
                <div style="background: linear-gradient(90deg, #b45309, #92400e); color: #fff; padding: 6px 12px; font-size: 0.85rem; font-weight: 900; display: flex; justify-content: space-between; align-items: center;">
                    <span>📑 تفاصيل الشيكات المصرفية المسحوبة:</span>
                    <span style="font-size: 0.72rem; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px;">شيكات معتمدة ✅</span>
                </div>
                <table style="width: 100%; border-collapse: collapse; background: #fffbeb;">
                    <thead>
                        <tr style="background: #fef3c7; color: #92400e; font-size: 0.8rem; font-weight: 900; border-bottom: 1.5px solid #d97706;">
                            <th style="padding: 5px 10px; width: 5%; text-align: center;">#</th>
                            <th style="padding: 5px 10px; width: 22%; text-align: right;">رقم الشيك</th>
                            <th style="padding: 5px 10px; width: 26%; text-align: right;">البنك / الفرع المسحوب عليه</th>
                            <th style="padding: 5px 10px; width: 17%; text-align: right;">تاريخ الشيك</th>
                            <th style="padding: 5px 10px; width: 18%; text-align: left;">قيمة الشيك</th>
                            <th style="padding: 5px 10px; width: 12%; text-align: right;">البيان</th>
                        </tr>
                    </thead>
                    <tbody>${chRows}</tbody>
                </table>
            </div>
        `;
    }

    return `
    <div class="disbursement-voucher-card" style="background:#ffffff; color:#0f172a; font-family:'Tajawal', 'Cairo', 'Segoe UI', Tahoma, sans-serif; direction:rtl; border:3px double #b91c1c; outline:1.5px solid #1e293b; border-radius:12px; padding:20px 24px; position:relative; box-sizing:border-box; width:100%; margin:0 auto; box-shadow:0 8px 25px rgba(0,0,0,0.08);">
        
        <!-- Header Row -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2.5px solid #b91c1c; padding-bottom:12px; margin-bottom:14px; gap:10px;">
            <!-- Right: Company -->
            <div style="text-align:right; flex:1.2;">
                <div style="font-size:1.35rem; font-weight:900; color:#0f172a; line-height:1.2;">${companyName}</div>
                <div style="font-size:0.82rem; color:#b91c1c; font-weight:800; margin-top:2px;">الإدارة المالية والمصرفية • الحسابات المركزية</div>
                <div style="font-size:0.78rem; color:#64748b; margin-top:3px;">
                    ${companyAddress ? `<div>العنوان: ${companyAddress}</div>` : ''}
                    ${companyPhone ? `<div>الهاتف: ${companyPhone}</div>` : ''}
                </div>
            </div>

            <!-- Center: Title -->
            <div style="text-align:center; flex:1.4;">
                <div style="display:inline-block; margin:0 auto; min-width:280px; background:linear-gradient(135deg, #1e293b, #0f172a); border:2px solid #ef4444; border-radius:10px; padding:7px 20px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                    <div style="color:#f87171; font-size:1.25rem; font-weight:900; letter-spacing:0.5px;">🏛️ سند صرف نقدية معتمد</div>
                    <div style="color:#cbd5e1; font-size:0.76rem; font-weight:700; margin-top:2px;">PAYMENT & DISBURSEMENT VOUCHER</div>
                </div>
                <div style="font-size:0.74rem; color:#059669; font-weight:900; margin-top:4px;">وثيقة محاسبية رسمية معتمدة ومقيدة بالدفاتر المالية ✅</div>
            </div>

            <!-- Left: Voucher Meta, Barcode & QR -->
            <div style="text-align:left; flex:1.3; display:flex; flex-direction:column; align-items:flex-end;">
                <div style="display:flex; gap:10px; align-items:center;">
                    <div style="text-align:right;">
                        <div style="font-size:0.70rem; color:#64748b; font-weight:800;">رقم السند المالي</div>
                        <div style="font-family:monospace; font-size:1.20rem; font-weight:900; color:#b91c1c; direction:ltr;">#${voucher.id}</div>
                        <div style="margin-top:2px;">${barcodeSvg}</div>
                        <div style="font-size:0.76rem; color:#475569; font-weight:700; margin-top:2px;">
                            <span>تاريخ التحرير: </span><b dir="ltr">${voucher.date}</b>
                        </div>
                    </div>
                    <div style="width:64px; height:64px; border:1.5px solid #cbd5e1; border-radius:8px; padding:2px; background:#fff; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                        <img src="${qrPlaceholder}" style="width:100%; height:100%; object-fit:contain; border-radius:6px;" alt="QR" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Grid (صرف من / صرف إلى) -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
            <!-- الصارف (من حساب) -->
            <div style="background:linear-gradient(135deg, #fef2f2 0%, #fff 100%); border:1.5px solid #fca5a5; border-radius:8px; padding:10px 14px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.82rem; font-weight:900; color:#991b1b;">📤 صُرف من حساب (طرف الصرف / الخزينة أو البنك):</span>
                    <span style="background:#ef4444; color:#fff; font-size:0.68rem; font-weight:900; padding:1px 7px; border-radius:6px;">الجهة الصارفة</span>
                </div>
                <div style="font-size:1.15rem; font-weight:900; color:#0f172a; margin-top:4px;">
                    ${fromAccName}
                </div>
                ${voucher.fromStatement ? `<div style="font-size:0.78rem; color:#475569; margin-top:3px;">ملاحظات الصرف: ${voucher.fromStatement}</div>` : ''}
            </div>

            <!-- المستفيد (إلى حساب) -->
            <div style="background:linear-gradient(135deg, #ecfdf5 0%, #fff 100%); border:1.5px solid #10b981; border-radius:8px; padding:10px 14px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.82rem; font-weight:900; color:#065f46;">📥 صُرف وقُيد لحساب (المستفيد / المستلم):</span>
                    <span style="background:#10b981; color:#fff; font-size:0.68rem; font-weight:900; padding:1px 7px; border-radius:6px;">الجهة المستلمة</span>
                </div>
                <div style="font-size:1.15rem; font-weight:900; color:#0f172a; margin-top:4px;">
                    ${toAccName}
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:3px; font-size:0.78rem;">
                    ${voucher.beneficiaryName ? `<span style="color:#0369a1; font-weight:800;">المستفيد الفعلي: <b>${voucher.beneficiaryName}</b></span>` : '<span></span>'}
                    ${contactPhone ? `<span style="color:#059669; font-weight:800; font-family:monospace;" dir="ltr">📱 ${contactPhone}</span>` : ''}
                </div>
            </div>
        </div>

        <!-- Grand Financial Amount Card -->
        <div style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border:2px solid #ef4444; border-radius:10px; padding:12px 18px; margin-bottom:12px; color:#ffffff; box-shadow:0 4px 14px rgba(15,23,42,0.12);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <div style="color:#f87171; font-size:0.84rem; font-weight:800;">💰 المبلغ الإجمالي المعتمد والمنصرف:</div>
                    <div style="font-size:2.1rem; font-weight:900; color:#34d399; font-family:monospace; letter-spacing:1px; margin-top:2px;" dir="ltr">
                        ${fmtNum(amtLocal)} <span style="font-size:1.20rem; color:#fde047;">${currency}</span>
                    </div>
                    ${isForeign ? `
                    <div style="font-size:0.82rem; color:#94a3b8; font-weight:700; margin-top:2px;">
                        المبلغ الأجنبي: <b style="color:#38bdf8;">${fmtNum(voucher.amountForeign)} ${voucher.currency}</b> (سعر الصرف: ${voucher.rate || 1})
                    </div>` : ''}
                </div>
                <div style="text-align:right; flex:1; max-width:480px;">
                    <div style="color:#94a3b8; font-size:0.80rem; font-weight:800;">✍️ وقدره كتابةً بالحروف (تفقيط معتمد):</div>
                    <div style="color:#fef08a; font-size:0.96rem; font-weight:900; line-height:1.45; margin-top:3px; background:rgba(255,255,255,0.08); padding:6px 12px; border-radius:6px; border:1px dashed rgba(251,191,36,0.5);">
                        ${tafqeetText}
                    </div>
                </div>
            </div>
        </div>

        <!-- Statement & Cost Center -->
        <div style="background:#f8fafc; border:1.5px solid #cbd5e1; border-radius:8px; padding:8px 14px; margin-bottom:12px;">
            <div style="display:flex; align-items:baseline; gap:8px;">
                <span style="color:#334155; font-size:0.86rem; font-weight:900; white-space:nowrap;">📝 البيان والسبب:&nbsp;</span>
                <span style="color:#0f172a; font-size:0.94rem; font-weight:800; flex:1; line-height:1.4;">${voucher.toStatement || 'سداد مستحقات مالية معتمدة وفق القيود الحسابية'}</span>
            </div>
            ${voucher.costCenter ? `
            <div style="margin-top:4px; padding-top:4px; border-top:1px dashed #cbd5e1; font-size:0.80rem; color:#0369a1; font-weight:800;">
                <span>🏷️ مركز التكلفة / المشروع:&nbsp;</span><b style="color:#0f172a;">${voucher.costCenter}</b>
            </div>` : ''}
        </div>

        <!-- Payment Method Overview -->
        <div style="display:flex; justify-content:space-between; align-items:center; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:8px; padding:6px 14px; margin-bottom:12px; font-size:0.85rem;">
            <div>
                <span style="color:#475569; font-weight:800;">طريقة السداد المعتمدة:&nbsp;</span>
                <b style="color:#0f172a; font-weight:900;">${methodLabel}</b>
            </div>
            <div style="color:#059669; font-weight:800;">
                قيد مالي موثق ومسجل بنظام الحسابات ✅
            </div>
        </div>

        <!-- Transfers Table if any -->
        ${transfersHtml}

        <!-- Cheques Table if any -->
        ${chequesHtml}

        <!-- Legal Promissory Statement -->
        <div style="background:#fffbeb; border:1.5px solid #f59e0b; border-radius:8px; padding:8px 14px; margin-bottom:14px;">
            <div style="font-size:0.86rem; color:#78350f; font-weight:800; line-height:1.45;">
                📜 <b>صيغة الإقرار المالي:&nbsp;</b>أقر وأعترف بأننا قيدنا وصرفنا من حسابنا المبلغ المبيّن أعلاه لصالح المكرم <b>(${toAccName})</b>، وذلك بموجب القيود الحسابية الرسمية المعتمدة بنظام الحسابات العامة.
            </div>
        </div>

        <!-- Signatures & Stamp -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; padding-top:10px; border-top:2px solid #cbd5e1; margin-top:10px;">
            <!-- Recipient -->
            <div style="text-align:center; flex:1;">
                <div style="font-size:0.82rem; color:#475569; font-weight:900;">المستلم / صاحب الحساب</div>
                <div style="margin:8px auto 4px auto; width:90px; height:1.5px; background:#94a3b8;"></div>
                <div style="font-size:0.75rem; color:#0369a1; font-weight:800;">${voucher.beneficiaryName || toAccName}</div>
            </div>

            <!-- Official Stamp -->
            <div style="text-align:center; flex:1.2;">
                <div style="display:inline-block; width:76px; height:76px; border:2.5px solid #b91c1c; border-radius:50%; padding:2px; background:rgba(254,242,242,0.6); transform:rotate(-6deg); box-shadow:0 0 0 2px #fff, 0 0 0 4px #b91c1c;">
                    <div style="width:100%; height:100%; border:1px dashed #b91c1c; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;">
                        <div style="font-size:0.56rem; color:#b91c1c; font-weight:900;">🏛️ الإدارة المالية</div>
                        <div style="font-size:0.66rem; color:#991b1b; font-weight:900; margin:1px 0;">معتمد وصُرف</div>
                        <div style="font-size:0.56rem; color:#059669; font-weight:900;">إلكترونياً ✅</div>
                        <div style="font-size:0.50rem; color:#64748b; margin-top:1px;">${voucher.date}</div>
                    </div>
                </div>
            </div>

            <!-- Accountant -->
            <div style="text-align:center; flex:1;">
                <div style="font-size:0.82rem; color:#475569; font-weight:900;">المحاسب المالي (معد السند)</div>
                <div style="margin:8px auto 4px auto; width:90px; height:1.5px; background:#94a3b8;"></div>
                <div style="font-size:0.75rem; color:#059669; font-weight:800;">قيد آلي نظامي</div>
            </div>

            <!-- Manager -->
            <div style="text-align:center; flex:1;">
                <div style="font-size:0.82rem; color:#475569; font-weight:900;">المدير المالي / الاعتماد</div>
                <div style="margin:8px auto 4px auto; width:90px; height:1.5px; background:#94a3b8;"></div>
                <div style="font-size:0.75rem; color:#0f172a; font-weight:800;">إجازة وصرف معتمد</div>
            </div>
        </div>

        <!-- Security Stripe -->
        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px dashed #cbd5e1; padding-top:6px; margin-top:10px; font-size:0.70rem; color:#64748b; font-weight:800;">
            <div>VERIFICATION: <span style="font-family:monospace; color:#0f172a; font-weight:900;">#DOC-${voucher.id}-${String(voucher.date).replace(/-/g, '')}</span></div>
            <div>وثيقة مالية معتمدة ومقيدة بالنظام المحاسبي العام ELSAJANA ERP 🔒</div>
        </div>
    </div>
    `;
};

// ═══ Build Voucher Print HTML (Unified for Preview and Print) ═══
function buildVoucherPrintHTML() {
    let printWrapper = document.getElementById('voucher-print-wrapper');
    let printArea = document.getElementById('voucher-print-area');
    
    if (!printWrapper) {
        printWrapper = document.createElement('div');
        printWrapper.id = 'voucher-print-wrapper';
        printWrapper.style.display = 'none';
        document.body.appendChild(printWrapper);
        
        printArea = document.createElement('div');
        printArea.id = 'voucher-print-area';
        printWrapper.appendChild(printArea);
    }
    
    let voucher = null;
    if (currentViewedIndex !== -1 && singlePaymentsData && singlePaymentsData[currentViewedIndex]) {
        voucher = singlePaymentsData[currentViewedIndex];
    }
    if (!voucher) {
        voucher = buildVoucherFromCurrentForm();
    }
    
    const qrPayload = `DOC:VOUCHER|NO:${voucher.id}|DATE:${voucher.date}|AMT:${voucher.amountLocal}SDG`;
    const qrPlaceholder = `https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=1&data=${encodeURIComponent(qrPayload)}`;
    
    const premiumHtml = window.generateVoucherDocumentHTML(voucher, qrPlaceholder);
    
    printArea.innerHTML = premiumHtml;
    
    const previewPaper = document.getElementById('premium-voucher-paper');
    if (previewPaper) {
        previewPaper.innerHTML = premiumHtml;
    }
}

function previewVoucher() {
    buildVoucherPrintHTML();
    const modal = document.getElementById('premium-voucher-preview-modal');
    if(modal) {
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.querySelector('.glass-panel').style.transform = 'translateY(0)';
        }, 10);
    }
}

function closePremiumPreview() {
    const modal = document.getElementById('premium-voucher-preview-modal');
    if(modal) {
        modal.style.opacity = '0';
        modal.querySelector('.glass-panel').style.transform = 'translateY(30px)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

function printVoucher() {
    buildVoucherPrintHTML();
    
    if (!document.getElementById('voucher-print-css')) {
        const style = document.createElement('style');
        style.id = 'voucher-print-css';
        style.textContent = `
            @media print {
                body * {
                    visibility: hidden;
                }
                #voucher-print-wrapper, #voucher-print-wrapper * {
                    visibility: visible;
                }
                #voucher-print-wrapper {
                    display: block !important;
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    margin: 0;
                    padding: 0;
                }
                @page {
                    size: A4 portrait;
                    margin: 10mm;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    const printWrapper = document.getElementById('voucher-print-wrapper');
    if (printWrapper) printWrapper.style.display = 'block';

    setTimeout(() => {
        window.print();
        if (printWrapper) printWrapper.style.display = 'none';
    }, 200);
}

function confirmDelete() {
    if (currentViewedIndex === -1) {
        showToast("لا يوجد سند محفوظ لحذفه حالياً", "warning");
        return;
    }
    if (confirm("هل أنت متأكد من حذف هذا السند نهائياً؟")) {
        const oldVoucher = singlePaymentsData[currentViewedIndex];
        
        // Revert old payment effect (isRevert = true means we REVERSE the original effect)
        function applyPaymentEffectDel(fromAccId, toAccId, amount, isRevert = false) {
            if (!fromAccId || !toAccId) return;
            let multiplier = isRevert ? -1 : 1;
            let amtFrom = -amount * multiplier; // source was decreased, so reverting increases it
            let amtToPositive = amount * multiplier; // dest was increased, so reverting decreases it

            const getTenantKey = window.getTenantKey || ((k) => k);
            const loadEntity = (key) => {
                try { return JSON.parse(localStorage.getItem(getTenantKey(key)) || localStorage.getItem(key)) || []; } catch(e) { return []; }
            };
            const saveEntity = (key, data) => {
                localStorage.setItem(key, JSON.stringify(data));
            };

            // Revert fromAccount
            if (fromAccId.startsWith('safe-')) {
                let id = fromAccId.substring(5);
                let safes = loadEntity('erp_safes_v1');
                let idx = safes.findIndex(s => String(s.id) === String(id));
                if (idx > -1) { safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtFrom; saveEntity('erp_safes_v1', safes); }
            } else if (fromAccId.startsWith('bank-')) {
                let id = fromAccId.substring(5);
                let banks = loadEntity('erp_banks_v1');
                let idx = banks.findIndex(b => String(b.id) === String(id));
                if (idx > -1) { banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtFrom; saveEntity('erp_banks_v1', banks); }
            } else if (fromAccId.startsWith('cust-')) {
                let id = fromAccId.substring(5);
                let custs = loadEntity('erp_customers_v1');
                let idx = custs.findIndex(c => String(c.id) === String(id));
                if (idx > -1) { custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amtFrom; saveEntity('erp_customers_v1', custs); }
            } else if (fromAccId.startsWith('supp-')) {
                let id = fromAccId.substring(5);
                let sups = loadEntity('erp_suppliers_v1');
                let idx = sups.findIndex(s => String(s.id) === String(id));
                if (idx > -1) { sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtFrom; saveEntity('erp_suppliers_v1', sups); }
            } else if (fromAccId.startsWith('exp-')) {
                let id = fromAccId.substring(4);
                let accounts = loadEntity('erp_accounts_v1');
                let idx = accounts.findIndex(a => String(a.id) === String(id));
                if (idx > -1) { accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amtFrom; saveEntity('erp_accounts_v1', accounts); }
            }

            // Revert toAccount
            if (toAccId.startsWith('cust-')) {
                let id = toAccId.substring(5);
                let custs = loadEntity('erp_customers_v1');
                let idx = custs.findIndex(c => String(c.id) === String(id));
                if (idx > -1) { custs[idx].currentBalance = (parseFloat(custs[idx].currentBalance) || 0) + amtToPositive; saveEntity('erp_customers_v1', custs); }
            } else if (toAccId.startsWith('supp-')) {
                let id = toAccId.substring(5);
                let sups = loadEntity('erp_suppliers_v1');
                let idx = sups.findIndex(s => String(s.id) === String(id));
                if (idx > -1) { sups[idx].currentBalance = (parseFloat(sups[idx].currentBalance) || 0) + amtToPositive; saveEntity('erp_suppliers_v1', sups); }
            } else if (toAccId.startsWith('bank-')) {
                let id = toAccId.substring(5);
                let banks = loadEntity('erp_banks_v1');
                let idx = banks.findIndex(b => String(b.id) === String(id));
                if (idx > -1) { banks[idx].currentBalance = (parseFloat(banks[idx].currentBalance) || 0) + amtToPositive; saveEntity('erp_banks_v1', banks); }
            } else if (toAccId.startsWith('safe-')) {
                let id = toAccId.substring(5);
                let safes = loadEntity('erp_safes_v1');
                let idx = safes.findIndex(s => String(s.id) === String(id));
                if (idx > -1) { safes[idx].currentBalance = (parseFloat(safes[idx].currentBalance) || 0) + amtToPositive; saveEntity('erp_safes_v1', safes); }
            } else if (toAccId.startsWith('exp-')) {
                let id = toAccId.substring(4);
                let accounts = loadEntity('erp_accounts_v1');
                let idx = accounts.findIndex(a => String(a.id) === String(id));
                if (idx > -1) { accounts[idx].currentBalance = (parseFloat(accounts[idx].currentBalance) || 0) + amtToPositive; saveEntity('erp_accounts_v1', accounts); }
            }
        }
        
        applyPaymentEffectDel(oldVoucher.fromAccount, oldVoucher.toAccount, oldVoucher.amountLocal, true);

        singlePaymentsData.splice(currentViewedIndex, 1);
        const getTenantKey = window.getTenantKey || ((k) => k);
        const tKey = getTenantKey('erp_single_payments_v1');
        localStorage.setItem(tKey, JSON.stringify(singlePaymentsData));
        window.SERVER_SINGLE_PAYMENTS = singlePaymentsData;
        if (typeof window.rebuildGLFromAllTables === 'function') window.rebuildGLFromAllTables(false);
        showToast("تم حذف السند بنجاح", "success");
        resetVoucherSilent();
    }
}

// ================= UI/UX ENHANCEMENTS (ANIMATIONS & VALIDATION) =================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Strict Number Validation & Feedback
    const numberInputs = document.querySelectorAll('input[inputmode="numeric"], input[inputmode="decimal"], .amount');
    numberInputs.forEach(input => {
        input.addEventListener('keydown', (e) => {
            if (e.key.length === 1 && !/[0-9.]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                // Visual feedback for invalid input
                input.style.boxShadow = '0 0 15px rgba(239, 68, 68, 0.8)';
                input.style.borderColor = '#ef4444';
                input.style.transform = 'translateY(-2px) rotate(1deg)';
                setTimeout(() => {
                    input.style.boxShadow = '';
                    input.style.borderColor = '';
                    input.style.transform = '';
                }, 200);
            }
        });
    });

    // 2. Enhance Enter Navigation visually
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            setTimeout(() => {
                const active = document.activeElement;
                if(active && active.tagName === 'INPUT') {
                    active.style.boxShadow = '0 0 20px hsla(160, 70%, 45%, 0.4)';
                    setTimeout(() => active.style.boxShadow = '', 300);
                }
            }, 50);
        }
    });

    updateLedgerButtons();
});

window.buildAccountsArray = function() {
    const getTenantKey = window.getTenantKey || ((k) => k);
    let localCusts = [], localSups = [], localBanks = [], localSafes = [], localExp = [], localJobs = [];
    try { localCusts = (JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1')) || localStorage.getItem('erp_customers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    try { localSups = (JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1')) || []).filter(x => !x.deleted); } catch(e) {}
    if (!localSups || localSups.length === 0) {
        localSups = [
        { id: 'SUP-001', code: 'SUP-001', name: 'شركة مصنع الأسعد للحديد والصلب', phone: '0123456789', currentBalance: 0, creditLimit: 50000000 },
        { id: 'SUP-002', code: 'SUP-002', name: 'شركة أوميغا للصناعات والصلب', phone: '0912345678', currentBalance: 0, creditLimit: 30000000 },
        { id: 'SUP-003', code: 'SUP-003', name: 'شركة أسمنت عطبرة المحدودة', phone: '0112345678', currentBalance: 0, creditLimit: 20000000 },
        { id: 'SUP-004', code: 'SUP-004', name: 'مجمع جياد الصناعي', phone: '0901234567', currentBalance: 0, creditLimit: 100000000 },
        { id: 'SUP-005', code: 'SUP-005', name: 'مؤسسة الهدى للتجارة والتوريدات', phone: '0123987654', currentBalance: 0, creditLimit: 15000000 },
        { id: 'SUP-006', code: 'SUP-006', name: 'شركة أسمنت بربر المحدودة', phone: '0912987654', currentBalance: 0, creditLimit: 25000000 },
        { id: 'SUP-007', code: 'SUP-007', name: 'مصنع الراجحي للحديد والصلب', phone: '0123112233', currentBalance: 0, creditLimit: 40000000 }
    ];
        try { localStorage.setItem(getTenantKey('erp_suppliers_v1'), JSON.stringify(localSups)); localStorage.setItem('erp_suppliers_v1', JSON.stringify(localSups)); } catch(e){}
    }
    try { localBanks = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1')) || []; } catch(e) {}
    try { localSafes = JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1')) || localStorage.getItem('erp_safes_v1')) || []; } catch(e) {}
    try { localExp = JSON.parse(localStorage.getItem(getTenantKey('erp_accounts_v1')) || localStorage.getItem('erp_accounts_v1')) || []; } catch(e) {}
    try { localJobs = JSON.parse(localStorage.getItem(getTenantKey('erp_jobs_v1')) || localStorage.getItem('erp_jobs_v1')) || []; } catch(e) {}
    
    ACCOUNTS = [];
    localCusts.forEach(c => ACCOUNTS.push({ id: `cust-${c.id}`, name: c.name || c.customerName, type: 'عميل', color: '#10b981', balances: { checks: 0, debt: parseFloat(c.currentBalance) || 0, loan: 0 } }));
    localSups.forEach(s => ACCOUNTS.push({ id: `supp-${s.id}`, name: s.name || s.supplierName, type: 'مورد', color: '#f59e0b', balances: { checks: 0, debt: 0, loan: parseFloat(s.currentBalance) || 0 } }));
    localBanks.forEach(b => ACCOUNTS.push({ id: `bank-${b.id}`, name: b.name || b.bankName, type: 'بنك', color: '#3b82f6', balances: { checks: 0, debt: parseFloat(b.currentBalance) || 0, loan: 0 } }));
    localSafes.forEach(s => ACCOUNTS.push({ id: `safe-${s.id}`, name: s.name || s.safeName, type: 'خزنة', color: '#8b5cf6', balances: { checks: 0, debt: parseFloat(s.currentBalance) || 0, loan: 0 } }));
    localExp.forEach(e => ACCOUNTS.push({ id: `exp-${e.id}`, name: e.name || e.accountName, type: 'عام', color: '#64748b', balances: { checks: 0, debt: parseFloat(e.currentBalance) || 0, loan: 0 } }));
};

// ═══════════════════════════════════════════════════════
// Custom Dropdown Logic for Accounts
// ═══════════════════════════════════════════════════════
function renderAccountDropdown(type = 'from', filter = '') {
    if (!ACCOUNTS || ACCOUNTS.length === 0) {
        window.buildAccountsArray();
    }
    const listEl = document.getElementById(type + '-account-dropdown-list');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    const term = filter.toLowerCase().trim();
    const normalizedTerm = window.normalizeArabic ? window.normalizeArabic(term) : term;
    
    try {
        let localMatches = (ACCOUNTS || []);
        if (normalizedTerm) {
            const searchWords = normalizedTerm.split(/\s+/).filter(w => w.length > 0);
            
            localMatches = localMatches.filter(c => {
                const normName = window.normalizeArabic ? window.normalizeArabic(c.name) : c.name.toLowerCase();
                const normCode = String(c.code || '').toLowerCase();
                
                return searchWords.every(word => 
                    normName.includes(word) || normCode.includes(word)
                );
            });
        }

        const finalMatches = Array.from(new Map(localMatches.map(m => [String(m.id), m])).values()).slice(0, 50);

        if (finalMatches.length === 0) {
            listEl.innerHTML = `<div class="custom-dropdown-empty">لا توجد حسابات مطابقة.<br><small style="color:#888;">(إجمالي الحسابات: ${(ACCOUNTS||[]).length})</small></div>`;
            return;
        }

        finalMatches.forEach(c => {
            const item = document.createElement('div');
            item.className = 'custom-dropdown-item';
            item.style.padding = '12px 16px';
            item.style.cursor = 'pointer';
            item.style.borderBottom = '1px solid rgba(255, 255, 255, 0.05)';
            item.style.transition = 'background 0.2s';
            
            item.onmouseenter = () => item.style.background = 'rgba(255, 255, 255, 0.1)';
            item.onmouseleave = () => item.style.background = 'transparent';
            
            let typeBadge = '';
            if (c.type) {
                typeBadge = `<span style="font-size:0.8rem; background: ${c.color}20; color: ${c.color}; padding: 4px 12px; border-radius: 20px; font-weight: bold; min-width: 60px; text-align: center; box-shadow: inset 0 0 5px ${c.color}40;">${c.type}</span>`;
            }
            let codeBadge = '';
            if (c.code) {
                const highlightedCode = window.highlightSearchMatches ? window.highlightSearchMatches(String(c.code), filter) : String(c.code);
                codeBadge = `<span style="font-size:0.8rem; color:#888;">[${highlightedCode}]</span>`;
            }

            const highlightedName = window.highlightSearchMatches ? window.highlightSearchMatches(c.name, filter) : c.name;

            item.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: bold; color: #fff;">${highlightedName}</span>
                        ${codeBadge}
                    </div>
                    ${typeBadge}
                </div>
            `;
            
            item.onmousedown = () => selectAccount(type, c.id, c.name); 
            item.onpointerdown = () => selectAccount(type, c.id, c.name);
            listEl.appendChild(item);
        });
    } catch (e) {
        console.error(e);
        listEl.innerHTML = '<div class="custom-dropdown-empty">حدث خطأ داخلي</div>';
    }
}

function showAccountDropdown(type) {
    renderAccountDropdown(type, document.getElementById(type + '-account-search').value);
    const listEl = document.getElementById(type + '-account-dropdown-list');
    if (listEl) {
        listEl.classList.add('show');
        listEl.style.display = 'block';
    }
}

let hideAccountDropdownTimeout = {};

function hideAccountDropdownDelayed(type) {
    clearTimeout(hideAccountDropdownTimeout[type]);
    hideAccountDropdownTimeout[type] = setTimeout(() => {
        if (document.activeElement && document.activeElement.id === type + '-account-search') {
            return;
        }

        const listEl = document.getElementById(type + '-account-dropdown-list');
        if(listEl) listEl.classList.remove('show'); listEl.style.display = 'none';
        
        const idVal = document.getElementById(type + '-account').value;
        const searchVal = document.getElementById(type + '-account-search').value.trim();
        if (idVal) {
            const acc = (ACCOUNTS || []).find(c => String(c.id) === String(idVal));
            if (acc && acc.name !== searchVal) {
                document.getElementById(type + '-account-search').value = acc.name;
            }
        } else if (searchVal) {
            const exactMatch = (ACCOUNTS || []).find(c => c.name === searchVal);
            if (exactMatch) {
                selectAccount(type, exactMatch.id, exactMatch.name);
            }
        } else {
            document.getElementById(type + '-account-search').value = '';
        }
    }, 250);
}

function filterAccountDropdown(type) {
    document.getElementById(type + '-account').value = '';
    updateLedgerButtons();
    renderAccountDropdown(type, document.getElementById(type + '-account-search').value);
    document.getElementById(type + '-account-dropdown-list').classList.add('show');
}

function selectAccount(type, id, name) {
    document.getElementById(type + '-account').value = id;
    document.getElementById(type + '-account-search').value = name;
    document.getElementById(type + '-account-dropdown-list').classList.remove('show');
    updateLedgerButtons();
    
    if (type === 'from') {
        setTimeout(() => {
            const toSearch = document.getElementById('to-account-search');
            if (toSearch) {
                toSearch.focus();
                toSearch.select();
                if(typeof showAccountDropdown === 'function') showAccountDropdown('to');
            }
        }, 150);
    } else if (type === 'to') {
        if(typeof updateBalances === 'function') updateBalances();
        
        // Auto-trigger Bank Transfer and focus amount
        const btnBank = document.getElementById('toggle-bank');
        if (btnBank && !btnBank.classList.contains('active')) {
            if (typeof togglePaymentSection === 'function') togglePaymentSection('bank');
        }
        const bankSec = document.getElementById('pay-bank-section');
        if (bankSec) bankSec.style.display = 'flex';
        const container = document.getElementById('transfers-container');
        if (container && container.children.length === 0 && typeof addTransferRow === 'function') {
            addTransferRow('', '', 0);
        }
        
        setTimeout(() => {
            const amts = document.querySelectorAll('.transfer-amount');
            if (amts.length > 0) {
                const amtInput = amts[0];
                amtInput.focus();
                amtInput.select();
            }
        }, 150);
    }
}

window.selectAccount = selectAccount;
;

function updateLedgerButtons() {
    const fromVal = document.getElementById('from-account') ? document.getElementById('from-account').value : '';
    const toVal = document.getElementById('to-account') ? document.getElementById('to-account').value : '';
    
    const fromBtn = document.getElementById('from-account-ledger-btn');
    const toBtn = document.getElementById('to-account-ledger-btn');
    
    if (fromBtn) fromBtn.style.display = fromVal ? 'inline-flex' : 'none';
    if (toBtn) toBtn.style.display = toVal ? 'inline-flex' : 'none';
}

function swapAccounts() {
    const fromIdEl = document.getElementById('from-account');
    const fromSearchEl = document.getElementById('from-account-search');
    const toIdEl = document.getElementById('to-account');
    const toSearchEl = document.getElementById('to-account-search');

    if (!fromIdEl || !fromSearchEl || !toIdEl || !toSearchEl) return;

    // Get current values
    const fromId = fromIdEl.value;
    const fromSearch = fromSearchEl.value;
    const toId = toIdEl.value;
    const toSearch = toSearchEl.value;

    // Set swapped values
    fromIdEl.value = toId;
    fromSearchEl.value = toSearch;
    toIdEl.value = fromId;
    toSearchEl.value = fromSearch;

    // Add a visual rotation feedback to the arrow
    const container = document.querySelector('.epic-arrow-container');
    if (container) {
        container.style.transform = 'translate(-50%, -50%) scale(1.18) rotate(180deg)';
        container.style.boxShadow = '0 15px 45px rgba(251, 191, 36, 0.6), 0 0 35px rgba(251, 191, 36, 0.4)';
        setTimeout(() => {
            container.style.transform = '';
            container.style.boxShadow = '';
        }, 800);
    }

    // Update balances since "to" account changed
    if (typeof updateBalances === 'function') {
        updateBalances();
    }

    // Play a tiny toast notification
    if (typeof showToast === 'function') {
        showToast('🔄 تم عكس حساب الصرف والاستلام بنجاح!', 'success');
    }
    updateLedgerButtons();
}

function getVoucherContactDetails(accountId) {
    if (!accountId) return null;
    const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(x => !x.deleted);
    const customer = custs.find(c => String(c.id) === String(accountId) || String(c.id) === String(accountId).replace('cust-', ''));
    if (customer) {
        return {
            type: 'customer',
            name: customer.name,
            phone: customer.phone || '',
            whatsapp: customer.whatsapp || ''
        };
    }
    const sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]').filter(x => !x.deleted);
    const supplier = sups.find(s => String(s.id) === String(accountId) || String(s.id) === String(accountId).replace('supp-', ''));
    if (supplier) {
        return {
            type: 'supplier',
            name: supplier.name,
            phone: supplier.phone || '',
            whatsapp: supplier.whatsapp || ''
        };
    }
    return null;
}

async function sendVoucherWA(voucherId) {
    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=status&userId=${userId}`);
        const data = await res.json();
        
        if (data.status !== 'CONNECTED' && data.status !== 'AUTHENTICATED') {
            const wantsToConnect = typeof showWAConnectPrompt === 'function' ? await showWAConnectPrompt() : confirm('رقم الواتساب غير مربوط. هل تريد الربط الآن؟');
            if (wantsToConnect) {
                if (typeof openWAModal === 'function') {
                    openWAModal();
                } else {
                    alert('يرجى الذهاب إلى شاشة الإعدادات لربط الواتساب.');
                }
            }
            return;
        }
    } catch (err) {
        alert('خدمة الواتساب غير متاحة حالياً. تأكد من تشغيل الخادم.');
        return;
    }

    let voucher = (singlePaymentsData || []).find(x => String(x.id).trim().toLowerCase() === String(voucherId).trim().toLowerCase());
    if (!voucher && typeof window.getAllSinglePayments === 'function') {
        voucher = window.getAllSinglePayments().find(x => String(x.id).trim().toLowerCase() === String(voucherId).trim().toLowerCase());
    }
    if (!voucher && typeof buildVoucherFromCurrentForm === 'function') {
        voucher = buildVoucherFromCurrentForm(voucherId);
    }
    if (!voucher || !voucher.id) {
        showToast('لم يتم العثور على بيانات السند', 'error');
        return;
    }

    let defaultPhone = '';
    let accountName = voucher.beneficiaryName || '';
    let accountTypeLabel = 'المستفيد';

    const contact = getVoucherContactDetails(voucher.toAccount);
    if (contact) {
        defaultPhone = contact.whatsapp || contact.phone || '';
        accountName = contact.name || accountName;
        accountTypeLabel = contact.type === 'customer' ? 'العميل' : 'المورد';
    }
    if (!accountName) {
        accountName = resolveAccountDisplayName(voucher.toAccount, false) || 'الجهة المستلمة';
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
                
                <h3 style="color:#f8fafc; font-size:1.6rem; font-family:'Tajawal',sans-serif; margin-bottom:10px; margin-top:0; font-weight:800;">إرسال السند عبر واتساب</h3>
                <p style="color:#94a3b8; font-size:0.95rem; margin-bottom:20px; line-height:1.6; font-family:'Tajawal',sans-serif;">يرجى تأكيد إرسال السند:</p>
                
                <!-- Display card -->
                <div id="wa-phone-display-wrap" style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 15px 20px; margin-bottom: 25px; text-align: right;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">👤 ${accountTypeLabel}:</span>
                        <span style="font-size: 1.15rem; color: #25d366; font-weight: 800;">${accountName}</span>
                        <span style="font-size: 0.95rem; color: #38bdf8; font-weight: 700; direction: ltr; margin-top: 4px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>📞</span> <span id="wa-display-phone-text">${defaultPhone || 'لا يوجد رقم مسجل'}</span>
                        </span>
                    </div>
                    <button type="button" onclick="toggleEditPhone()" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); color: #38bdf8; border-radius: 12px; width: 42px; height: 48px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(56, 189, 248, 0.2)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(56, 189, 248, 0.1)'; this.style.transform='scale(1)';" title="إرسال لرقم آخر">
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
                        <input type="tel" id="wa-prompt-input" placeholder="رقم الهاتف الجديد..." value="${defaultPhone}" oninput="document.getElementById('wa-display-phone-text').textContent = this.value;" style="width:100%; background:rgba(0,0,0,0.4); border:2px solid rgba(255,255,255,0.1); color:#fff; padding:12px 15px; border-radius:12px; font-size:1.7rem; text-align:center; transition:all 0.3s; outline:none; font-family:'Inter',sans-serif;" onfocus="this.style.borderColor='#25d366'; this.style.boxShadow='0 0 12px rgba(37,211,102,0.2)';" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none';">
                    </div>
                </div>

                <div style="display:flex; gap:15px; justify-content:center;">
                    <button id="wa-prompt-send" style="background:linear-gradient(135deg, #25d366, #1da851); color:#fff; border:none; padding:12px 30px; border-radius:12px; font-weight:bold; font-size:1.3rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.3s; box-shadow:0 4px 15px rgba(37,211,102,0.4); display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(37,211,102,0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(37,211,102,0.4)';">
                        <span>إرسال</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                    <button id="wa-prompt-cancel" style="background:rgba(255,255,255,0.05); color:#cbd5e1; border:1px solid rgba(255,255,255,0.1); padding:12px 25px; border-radius:12px; font-weight:bold; font-size:1.3rem; cursor:pointer; font-family:'Tajawal',sans-serif; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">إلغاء</button>
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

    showToast('⏳ جاري تجهيز وتوليد سند الصرف بصيغة PDF للواتساب...', 'info', 3000);

    let cfg = {};
    try {
        cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
    } catch(e) {}
    const companyName = cfg.companyName || document.getElementById('header-company-name-top')?.innerText?.trim() || 'شركة السجانة التجارية';

    const toAccName = resolveAccountDisplayName(voucher.toAccount, false);
    const qrPayload = `DOC:VOUCHER|NO:${voucher.id}|DATE:${voucher.date}|AMT:${voucher.amountLocal}SDG|TO:${toAccName}`;
    const qrDataUrl = await getVoucherQRCode(qrPayload);

    const voucherHtml = window.generateVoucherDocumentHTML(voucher, qrDataUrl);

    const renderWrapper = document.createElement('div');
    renderWrapper.style.position = 'fixed';
    renderWrapper.style.top = '0';
    renderWrapper.style.left = '0';
    renderWrapper.style.width = '794px';
    renderWrapper.style.zIndex = '-99999';
    renderWrapper.style.overflow = 'visible';
    renderWrapper.style.pointerEvents = 'none';
    renderWrapper.style.opacity = '1'; // Must be 1 so html2canvas renders with full opacity!
    renderWrapper.style.background = '#ffffff';

    const innerDiv = document.createElement('div');
    innerDiv.style.width = '794px';
    innerDiv.style.background = '#ffffff';
    innerDiv.style.margin = '0';
    innerDiv.style.padding = '0';
    innerDiv.innerHTML = voucherHtml;

    renderWrapper.appendChild(innerDiv);
    document.body.appendChild(renderWrapper);

    let pdfBlob = null;
    try {
        await new Promise(r => setTimeout(r, 150));
        if (typeof html2pdf !== 'undefined') {
            const opt = {
                margin: [5, 5, 5, 5],
                filename: `سند_صرف_${voucher.id}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2, 
                    useCORS: true, 
                    letterRendering: false, 
                    logging: false,
                    scrollX: 0,
                    scrollY: 0
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            pdfBlob = await html2pdf().set(opt).from(innerDiv).output('blob');
        }
    } catch (e) {
        console.error("Error creating PDF blob:", e);
    } finally {
        if (renderWrapper.parentNode) {
            renderWrapper.parentNode.removeChild(renderWrapper);
        }
    }

    const message = `مرحباً، مرفق سند الصرف المعتمد رقم ${voucher.id} بقيمة ${fmtNum(voucher.amountLocal)} SDG الصادر من ${companyName}. شكراً لتعاملكم معنا!`;
    
    if (typeof sendPDFViaWhatsApp === 'function') {
        if (pdfBlob) {
            await sendPDFViaWhatsApp(pdfBlob, phone, message);
        } else {
            await sendPDFViaWhatsApp(innerDiv, phone, message);
        }
    } else {
        showToast('برنامج ربط الواتساب غير متوفر في هذه الصفحة', 'error');
    }
}

async function sendCurrentVoucherWA() {
    let voucherId = null;
    if (currentViewedIndex !== -1 && singlePaymentsData && singlePaymentsData[currentViewedIndex]) {
        voucherId = singlePaymentsData[currentViewedIndex].id;
    } else {
        const vNoEl = document.getElementById('voucher-number');
        if (vNoEl && vNoEl.innerText && vNoEl.innerText.trim()) {
            voucherId = vNoEl.innerText.trim();
        }
    }
    if (voucherId) {
        await sendVoucherWA(voucherId);
    } else {
        showToast('⚠️ يرجى إدخال أو حفظ السند أولاً قبل الإرسال', 'warning');
    }
}

function updateActionButtonsVisibility() {
    const previewBtn = document.getElementById('btn-preview-invoice');
    const waBtn = document.getElementById('btn-whatsapp-invoice');
    if (currentViewedIndex !== -1) {
        if (previewBtn) previewBtn.style.display = 'inline-flex';
        if (waBtn) waBtn.style.display = 'inline-flex';
    } else {
        if (previewBtn) previewBtn.style.display = 'none';
        if (waBtn) waBtn.style.display = 'none';
    }
}

function getAccountName(id) {
    if(!id) return '';
    if (!ACCOUNTS || ACCOUNTS.length === 0) {
        if (typeof window.buildAccountsArray === 'function') window.buildAccountsArray();
    }
    let idStr = String(id).trim();
    let found = (ACCOUNTS || []).find(a => String(a.id).toLowerCase() === idStr.toLowerCase());
    if (found) return found.name;
    
    let cleanId = idStr.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
    found = (ACCOUNTS || []).find(a => {
        const aId = String(a.id).toLowerCase();
        const aName = String(a.name).toLowerCase();
        const cleanAId = aId.replace(/^[a-zA-Z]+\s*[-_:/]\s*/, '').trim();
        return aId === cleanId.toLowerCase() || cleanAId === cleanId.toLowerCase() || aName === cleanId.toLowerCase();
    });
    if (found) return found.name;
    
    if (cleanId.length > 1) {
        found = (ACCOUNTS || []).find(a => {
            const aName = String(a.name).toLowerCase();
            const aId = String(a.id).toLowerCase();
            return aName.includes(cleanId.toLowerCase()) || cleanId.toLowerCase().includes(aName) || aId.includes(cleanId.toLowerCase()) || cleanId.toLowerCase().includes(aId);
        });
        if (found) return found.name;
    }
    if (cleanId === 'safe-001' || idStr === 'safe-001') return 'الخزينة الرئيسية';
    if (cleanId === 'bank-001' || idStr === 'bank-001') return 'البنك الرئيسي';
    if (cleanId === 'bankok' || idStr === 'bankok') return 'بنكك (Bankak)';
    if (cleanId === 'fawry' || idStr === 'fawry') return 'فوري (Fawry)';
    if (cleanId === 'o-cash' || idStr === 'o-cash') return 'أوكاش (O-Cash)';
    if (cleanId === 'other' || idStr === 'other') return 'أخرى';
    
    return cleanId || idStr;
}

// ═══════════════════════════════════════════════════════
// Multiple Bank Transfers & Cheques Dynamic Manipulation
// ═══════════════════════════════════════════════════════

window.refreshTransferRowButtons = function() {
    const container = document.getElementById('transfers-container');
    if (!container) return;
    const rows = container.querySelectorAll('.transfer-row');
    rows.forEach((row, idx) => {
        const btnBox = row.querySelector('div:last-child');
        if (!btnBox) return;
        if (idx === 0) {
            btnBox.innerHTML = `<button type="button" onclick="addTransferRow()" class="pay-toggle-btn" style="background: rgba(16,185,129,0.2); border: 1px solid #34d399; color:#34d399; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`;
        } else {
            btnBox.innerHTML = `<button type="button" onclick="removeTransferRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;
        }
    });
};

window.refreshChequeRowButtons = function() {
    const container = document.getElementById('cheques-container');
    if (!container) return;
    const rows = container.querySelectorAll('.cheque-row');
    rows.forEach((row, idx) => {
        const btnBox = row.querySelector('div:last-child');
        if (!btnBox) return;
        if (idx === 0) {
            btnBox.innerHTML = `<button type="button" onclick="addChequeRow()" class="pay-toggle-btn" style="background: rgba(147,51,234,0.22); border: 1px solid #d8b4fe; color:#d8b4fe; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`;
        } else {
            btnBox.innerHTML = `<button type="button" onclick="removeChequeRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;
        }
    });
};

window.handleTransferAmountBlur = function(input) {
    const container = document.getElementById('transfers-container');
    const val = parseNum(input.value);
    const row = input.closest('.transfer-row');
    if (!input.value || val <= 0) {
        if (container && container.querySelectorAll('.transfer-row').length > 1) {
            if (row) {
                row.remove();
                refreshTransferRowButtons();
                if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
                else if (typeof syncFirstRowAmount === 'function') syncFirstRowAmount();
            }
            return;
        } else {
            input.value = '';
        }
    } else {
        formatAmount(input);
    }
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
};

window.handleChequeAmountBlur = function(input) {
    const container = document.getElementById('cheques-container');
    const val = parseNum(input.value);
    const row = input.closest('.cheque-row');
    if (!input.value || val <= 0) {
        if (container && container.querySelectorAll('.cheque-row').length > 1) {
            if (row) {
                row.remove();
                refreshChequeRowButtons();
                if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
                else if (typeof syncFirstRowAmount === 'function') syncFirstRowAmount();
            }
            return;
        } else {
            input.value = '';
        }
    } else {
        formatAmount(input);
    }
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
};

window.addTransferRow = function(no = '', note = '', amt = 0, statement = '') {
    const container = document.getElementById('transfers-container');
    if (!container) return;

    const isFirst = (container.children.length === 0);
    const row = document.createElement('div');
    row.className = 'transfer-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; width: 100%;';
    
    const labelHTML = "";

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addTransferRow()" class="pay-toggle-btn" style="background: rgba(16,185,129,0.2); border: 1px solid #34d399; color:#34d399; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeTransferRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div class="dynamic-focus-container" style="width: 324px; flex: none; display: flex; align-items: center; gap: 5px;">
            
            <input type="text" maxlength="12" class="v-input ctrl-input transfer-amount dynamic-focus-input dynamic-focus-amount" value="${amt > 0 ? Math.round(amt).toLocaleString('en-US') : ''}" placeholder="المبلغ" oninput="restrictToNumbers(this); if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();" oncontextmenu="showNumberProperties(event, this)" onblur="handleTransferAmountBlur(this);" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:3.1rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fbbf24; font-family: 'Inter', system-ui, sans-serif; font-variant-numeric: tabular-nums; font-weight: 900; text-align: center;">
        </div>
        <div style="flex: none; width: 200px; display: flex; align-items: center; gap: 5px;">
            
            <input type="text" autocomplete="off" list="master-banks-datalist" class="v-input ctrl-input transfer-note" value="${note}" placeholder="اسم البنك" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:1.55rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div class="dynamic-focus-container transfer-no-container" style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 5px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
            
            <input type="text" maxlength="14" class="v-input ctrl-input transfer-no dynamic-focus-input dynamic-focus-number" value="${no}" placeholder="رقم التحويلة" onfocus="this.parentElement.style.flex='15'; this.parentElement.style.minWidth='480px'; this.style.letterSpacing='2px';" onblur="this.parentElement.style.flex='1'; this.parentElement.style.minWidth='200px'; this.style.letterSpacing='normal';" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:2.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#2dd4bf; text-align: center; font-family: 'Inter', system-ui, sans-serif; font-variant-numeric: tabular-nums; font-weight: 900; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
        </div>
        <div style="flex: 1.2; min-width: 100px; display: flex; align-items: center; gap: 5px;">
            
            <input type="text" class="v-input ctrl-input transfer-statement" value="${statement}" placeholder="البيان" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:1.55rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;">
        </div>
        <div style="display: flex; justify-content: flex-end; width: 40px; flex: none;">
            ${buttonHTML}
        </div>
    `;
    container.appendChild(row);
    
    // Play sound if manual addition
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
    btn.closest('.transfer-row').remove();
    refreshTransferRowButtons();
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
    else syncFirstRowAmount();
};

window.addChequeRow = function(branch = '', no = '', date = '', amt = 0, statement = '') {
    const container = document.getElementById('cheques-container');
    if (!container) return;

    const isFirst = (container.children.length === 0);
    const row = document.createElement('div');
    row.className = 'cheque-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 8px; width: 100%;';
    
    const labelHTML = "";

    const buttonHTML = isFirst
        ? `<button type="button" onclick="addChequeRow()" class="pay-toggle-btn" style="background: rgba(147,51,234,0.22); border: 1px solid #d8b4fe; color:#d8b4fe; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">➕</button>`
        : `<button type="button" onclick="removeChequeRow(this)" class="btn-del-row" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; border: none; font-size: 0.9rem;">✖</button>`;

    row.innerHTML = `
        ${labelHTML}
        <div class="dynamic-focus-container" style="width: 324px; flex: none; display: flex; align-items: center; gap: 5px;">
            <input type="text" maxlength="12" class="v-input ctrl-input cheque-amount dynamic-focus-input dynamic-focus-amount" value="${amt > 0 ? parseFloat(amt).toFixed(2) : ''}" placeholder="المبلغ *" required oninput="restrictToNumbers(this); if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();" oncontextmenu="showNumberProperties(event, this)" onblur="handleChequeAmountBlur(this);" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:3.1rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fbbf24; font-family: 'Inter', system-ui, sans-serif; font-variant-numeric: tabular-nums; font-weight: 900; text-align: center;">
        </div>
        <div style="flex: 1; min-width: 80px; display: flex; align-items: center; gap: 5px;">
            <input type="text" autocomplete="off" list="master-banks-datalist" class="v-input ctrl-input cheque-branch" value="${branch}" placeholder="اسم البنك * (إجباري)" required style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:1.55rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align:center;">
        </div>
        <div class="dynamic-focus-container cheque-no-container" style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 5px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);"><input type="text" maxlength="14" class="v-input ctrl-input cheque-no dynamic-focus-input dynamic-focus-number" value="${no}" placeholder="رقم الشيك * (إجباري)" required onfocus="this.parentElement.style.flex='15'; this.parentElement.style.minWidth='480px'; this.style.letterSpacing='2px';" onblur="this.parentElement.style.flex='1'; this.parentElement.style.minWidth='200px'; this.style.letterSpacing='normal';" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:2.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#2dd4bf; text-align: center; font-family: 'Inter', system-ui, sans-serif; font-variant-numeric: tabular-nums; font-weight: 900; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);"></div>
        <div style="flex: 0.8; min-width: 120px; display: flex; align-items: center; gap: 5px;"><input type="date" class="v-input ctrl-input cheque-date" value="${date}" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:1.45rem; padding: 0 4px; width: 100%; border-radius: 6px; color:#fff;"></div>
        <div style="flex: 1.2; min-width: 100px; display: flex; align-items: center; gap: 5px;"><input type="text" class="v-input ctrl-input cheque-statement" value="${statement}" placeholder="البيان" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:1.55rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#fff; text-align: center;"></div>
        <div style="display: flex; justify-content: flex-end; width: 40px; flex: none;">
            ${buttonHTML}
        </div>
    `;
    container.appendChild(row);
    
    // Play sound if manual addition
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
    btn.closest('.cheque-row').remove();
    refreshChequeRowButtons();
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
    else syncFirstRowAmount();
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
    if (window.isLoadingVoucher) return;
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
};

window.loadVoucherPaymentDetails = function(v) {
    const method = v.paymentMethod || 'cash';
    const transfersContainer = document.getElementById('transfers-container');
    const chequesContainer = document.getElementById('cheques-container');
    if (transfersContainer) transfersContainer.innerHTML = '';
    if (chequesContainer) chequesContainer.innerHTML = '';

    if (method === 'mobile' || method === 'hybrid') {
        if (v.bankTransfers && Array.isArray(v.bankTransfers) && v.bankTransfers.length > 0) {
            v.bankTransfers.forEach(tr => {
                addTransferRow(tr.transferNo, tr.note, tr.amount, tr.statement || '');
            });
        } else {
            addTransferRow(v.mobileTransferNo || '', v.mobileNote || '', v.amountLocal || 0, v.statement || '');
        }
    }
    if (method === 'check' || method === 'hybrid') {
        if (v.cheques && Array.isArray(v.cheques) && v.cheques.length > 0) {
            v.cheques.forEach(ch => {
                addChequeRow(ch.checkBranch, ch.checkNo, ch.checkDate, ch.amount, ch.statement || '');
            });
        } else {
            addChequeRow(v.checkBranch || '', v.checkNo || '', v.checkDate || '', v.amountLocal || 0, v.statement || '');
        }
    }
    if (method === 'cash') {
        initPaymentContainers();
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

// Hook setup into document load
document.addEventListener('DOMContentLoaded', () => {
    initPaymentContainers();
});

window.unifyVoucherSequences = function() {
    if (!confirm('هل أنت متأكد من رغبتك في توحيد أرقام جميع السندات القديمة لتتبع التسلسل الجديد (E+سنة+شهر+000X)؟\nسيتم ترتيب جميع السندات حسب تاريخ إنشائها وإعطاؤها تسلسلاً موحداً، ولا يمكن التراجع عن هذه العملية.')) return;
    
    let spData = [];
    try {
        spData = window.getAllSinglePayments();
    } catch(e) {}

    if (spData.length === 0) {
        showToast('لا توجد سندات قديمة لإعادة ترقيمها', 'warning');
        return;
    }

    // Sort by date oldest first
    spData.sort((a, b) => new Date(a.date) - new Date(b.date));

    let seqMap = {};

    spData.forEach(v => {
        const d = new Date(v.date || Date.now());
        const yy = String(d.getFullYear()).substring(2, 4);
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const prefix = 'E' + yy + mm;
        
        if (!seqMap[prefix]) seqMap[prefix] = 1;
        
        const newId = `${prefix}${String(seqMap[prefix]).padStart(4, '0')}`;
        v.id = newId;
        if(v.voucherNo !== undefined) v.voucherNo = newId;
        
        seqMap[prefix]++;
    });

    localStorage.setItem('erp_single_payments_v1', JSON.stringify(spData));
    showToast('تم ترتيب وتوحيد تسلسل جميع السندات بنجاح!', 'success');
    
    setTimeout(() => {
        window.location.href = window.location.pathname;
    }, 1500);
};








window.restrictToNumbers = function(el) { if(el) { el.value = el.value.replace(/[^0-9.]/g, ''); let parts = el.value.split('.'); if (parts.length > 2) { el.value = parts[0] + '.' + parts.slice(1).join(''); } } };













// --- Custom Context Menu for Number Properties ---
let currentTargetInput = null;

window.showNumberProperties = function(e, input) {
    e.preventDefault();
    currentTargetInput = input;
    
    const menu = document.getElementById('num-props-menu');
    if (!menu) return;
    
    // Read current state from data attributes (or set defaults)
    const hasComma = input.getAttribute('data-thousands') !== 'false';
    const decimals = parseInt(input.getAttribute('data-decimals') || '0');
    const font = input.getAttribute('data-font') || 'inter';
    
    // Update UI
    document.getElementById('prop-comma-status').textContent = hasComma ? 'مفعل' : 'معطل';
    document.getElementById('prop-comma-status').style.color = hasComma ? '#10b981' : '#ef4444';
    document.getElementById('prop-dec-val').textContent = decimals;
    document.getElementById('prop-font-status').textContent = font === 'inter' ? 'Inter' : 'Roboto Mono';
    
    // Position menu
    menu.style.display = 'block';
    
    // Adjust if it goes off screen
    let x = e.pageX;
    let y = e.pageY;
    if (x + menu.offsetWidth > window.innerWidth) x = window.innerWidth - menu.offsetWidth - 10;
    if (y + menu.offsetHeight > window.innerHeight) y = window.innerHeight - menu.offsetHeight - 10;
    
    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
};

window.closeNumberProperties = function() {
    const menu = document.getElementById('num-props-menu');
    if (menu) menu.style.display = 'none';
};

document.addEventListener('click', function(e) {
    const menu = document.getElementById('num-props-menu');
    if (menu && menu.style.display === 'block') {
        if (!menu.contains(e.target)) {
            closeNumberProperties();
        }
    }
});

window.applyNumProp = function(action) {
    if (!currentTargetInput) return;
    const input = currentTargetInput;
    
    if (action === 'font-inc') {
        let currentSize = parseFloat(window.getComputedStyle(input).fontSize);
        input.style.setProperty('font-size', (currentSize + 2) + 'px', 'important');
        input.setAttribute('data-manual-font', 'true');
    } else if (action === 'font-dec') {
        let currentSize = parseFloat(window.getComputedStyle(input).fontSize);
        input.style.setProperty('font-size', (currentSize - 2) + 'px', 'important');
        input.setAttribute('data-manual-font', 'true');
    } else if (action === 'toggle-comma') {
        const current = input.getAttribute('data-thousands') !== 'false';
        input.setAttribute('data-thousands', current ? 'false' : 'true');
        document.getElementById('prop-comma-status').textContent = !current ? 'مفعل' : 'معطل';
        document.getElementById('prop-comma-status').style.color = !current ? '#10b981' : '#ef4444';
    } else if (action === 'dec-inc') {
        let dec = parseInt(input.getAttribute('data-decimals') || '0');
        if (dec < 4) dec++;
        input.setAttribute('data-decimals', dec);
        document.getElementById('prop-dec-val').textContent = dec;
    } else if (action === 'dec-dec') {
        let dec = parseInt(input.getAttribute('data-decimals') || '0');
        if (dec > 0) dec--;
        input.setAttribute('data-decimals', dec);
        document.getElementById('prop-dec-val').textContent = dec;
    } else if (action === 'toggle-font') {
        const current = input.getAttribute('data-font') || 'inter';
        const newFont = current === 'inter' ? 'monospace' : 'inter';
        input.setAttribute('data-font', newFont);
        if (newFont === 'monospace') {
            input.style.setProperty('font-family', "'Roboto Mono', Consolas, monospace", 'important');
            document.getElementById('prop-font-status').textContent = 'Roboto Mono';
        } else {
            input.style.setProperty('font-family', "'Inter', system-ui, sans-serif", 'important');
            document.getElementById('prop-font-status').textContent = 'Inter';
        }
    }
    
    // Reformat
    formatAmount(input);
    
    // Trigger any dependent calculations if they exist
    if (typeof recalcPaymentSummary === 'function') recalcPaymentSummary();
    if (typeof calcEquiv === 'function') {
        if (input.id === 'amount-local') calcEquiv('local');
        if (input.id === 'amount-foreign') calcEquiv('foreign');
    }
};

window.formatAmount = function(input) {
    if (!input.value) return;
    
    let raw = input.value.toString().replace(/[^0-9.-]/g, '');
    let num = parseFloat(raw) || 0;
    
    const hasComma = input.getAttribute('data-thousands') !== 'false';
    const decimals = parseInt(input.getAttribute('data-decimals') || '0');
    
    let factor = Math.pow(10, decimals);
    num = Math.round(num * factor) / factor;
    
    let parts = num.toFixed(decimals).split('.');
    
    if (hasComma) {
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    let result = decimals > 0 ? parts.join('.') : parts[0];
    input.value = result;
};


// --- Persist Number Properties ---
function getNumPropKey(input) {
    if (input.id) return 'num_prop_' + input.id;
    
    if (input.classList.contains('transfer-amount')) {
        let idx = Array.from(document.querySelectorAll('.transfer-amount')).indexOf(input);
        input.id = 'transfer-amount-' + idx;
        return 'num_prop_' + input.id;
    }
    if (input.classList.contains('cheque-amount')) {
        let idx = Array.from(document.querySelectorAll('.cheque-amount')).indexOf(input);
        input.id = 'cheque-amount-' + idx;
        return 'num_prop_' + input.id;
    }
    return 'num_prop_default';
}

window.saveNumberProperties = function() {
    if (!currentTargetInput) return;
    const input = currentTargetInput;
    const fullKey = getNumPropKey(input);
    const rawKey = fullKey.replace('num_prop_', '');
    const props = {
        fontSize: input.style.fontSize,
        thousands: input.getAttribute('data-thousands') !== 'false',
        decimals: parseInt(input.getAttribute('data-decimals') || '0'),
        font: input.getAttribute('data-font') || 'inter'
    };
    
    // Save to local storage for immediate sync
    localStorage.setItem(fullKey, JSON.stringify(props));
    
    // Update memory cache
    if (!window.globalNumberPropsCache) window.globalNumberPropsCache = {};
    window.globalNumberPropsCache[fullKey] = props;
    
    // Save to DB
    fetch('api/save_num_props.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key: rawKey, props: props })
    })
    .then(res => res.json())
    .then(data => {
        const btn = document.getElementById('save-props-btn');
        if (btn) {
            const oldText = btn.innerHTML;
            btn.innerHTML = data.status === 'success' ? '✅ تم الحفظ في قاعدة البيانات' : '❌ خطأ في الحفظ';
            setTimeout(() => btn.innerHTML = '💾 حفظ الخصائص', 2000);
        }
    })
    .catch(err => {
        const btn = document.getElementById('save-props-btn');
        if (btn) {
            btn.innerHTML = '❌ فشل الاتصال';
            setTimeout(() => btn.innerHTML = '💾 حفظ الخصائص', 2000);
        }
    });
};

function applyPropsToElement(input, props) {
    if (!input || !props) return;
    if (props.fontSize) {
        input.style.setProperty('font-size', props.fontSize, 'important');
        input.setAttribute('data-manual-font', 'true');
    }
    input.setAttribute('data-thousands', props.thousands ? 'true' : 'false');
    input.setAttribute('data-decimals', props.decimals);
    input.setAttribute('data-font', props.font);
    
    if (props.font === 'monospace') {
        input.style.setProperty('font-family', "'Roboto Mono', Consolas, monospace", 'important');
    } else {
        input.style.setProperty('font-family', "'Inter', system-ui, sans-serif", 'important');
    }
    
    if(typeof formatAmount === 'function') formatAmount(input);
}

window.loadAndApplyNumberProperties = function(input) {
    if (!input) return;
    const fullKey = getNumPropKey(input);
    
    // Check DB cache first
    if (window.globalNumberPropsCache && window.globalNumberPropsCache[fullKey]) {
        applyPropsToElement(input, window.globalNumberPropsCache[fullKey]);
        localStorage.setItem(fullKey, JSON.stringify(window.globalNumberPropsCache[fullKey]));
        return;
    }
    
    // Fallback to localStorage
    const saved = localStorage.getItem(fullKey);
    if (saved) {
        try {
            applyPropsToElement(input, JSON.parse(saved));
        } catch(e) {}
    }
};

window.globalNumberPropsCache = null;

window.initNumberProperties = function() {
    // Fetch all props from DB once
    fetch('api/get_all_num_props.php')
        .then(res => res.json())
        .then(data => {
            window.globalNumberPropsCache = data;
            setTimeout(() => {
                ['amount-local', 'amount-foreign'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) loadAndApplyNumberProperties(el);
                });
                document.querySelectorAll('.transfer-amount, .cheque-amount').forEach(el => {
                    loadAndApplyNumberProperties(el);
                });
            }, 100);
        })
        .catch(err => {
            // Fallback if network fails
            setTimeout(() => {
                ['amount-local', 'amount-foreign'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) loadAndApplyNumberProperties(el);
                });
                document.querySelectorAll('.transfer-amount, .cheque-amount').forEach(el => {
                    loadAndApplyNumberProperties(el);
                });
            }, 100);
        });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initNumberProperties);
} else {
    window.initNumberProperties();
}

// Hook into dynamic row generation if they exist
if (typeof addTransferRow === 'function') {
    const originalAddTransfer = addTransferRow;
    window.addTransferRow = function(...args) {
        originalAddTransfer.apply(this, args);
        setTimeout(() => {
            const rows = document.querySelectorAll('.transfer-amount');
            if (rows.length > 0) loadAndApplyNumberProperties(rows[rows.length - 1]);
        }, 50);
    };
}

if (typeof addChequeRow === 'function') {
    const originalAddCheque = addChequeRow;
    window.addChequeRow = function(...args) {
        originalAddCheque.apply(this, args);
        setTimeout(() => {
            const rows = document.querySelectorAll('.cheque-amount');
            if (rows.length > 0) loadAndApplyNumberProperties(rows[rows.length - 1]);
        }, 50);
    };
}
