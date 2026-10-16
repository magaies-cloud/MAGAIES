window.getAllSinglePayments = function() {
    const getTK = window.getTenantKey || ((k) => k);
    let localInvs = [];
    let tenantInvs = [];
    let mobInvs = [];
    let cacheInvs = [];
    let offQueue = [];
    let uniQueue = [];
    try { localInvs = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || []; } catch(e){}
    try {
        const tk = getTK('erp_single_payments_v1');
        if (tk !== 'erp_single_payments_v1') tenantInvs = JSON.parse(localStorage.getItem(tk)) || [];
    } catch(e){}
    try { mobInvs = JSON.parse(localStorage.getItem('magaiees_vouchers_history')) || []; } catch(e){}
    try { cacheInvs = JSON.parse(localStorage.getItem('magaies_vouchers_cache')) || []; } catch(e){}
    try { offQueue = JSON.parse(localStorage.getItem('magaies_mobile_offline_queue')) || []; } catch(e){}
    try {
        if (window.MagaiesOffline && typeof window.MagaiesOffline.getQueue === 'function') {
            uniQueue = window.MagaiesOffline.getQueue()
                .filter(q => q && (q.type === 'finance' || q.type === 'voucher' || q.type === 'single_payment') && q.payload)
                .map(q => Object.assign({}, q.payload, { id: q.offline_id || q.tempId || q.payload.voucherNo || q.payload.id, isOffline: true }));
        }
    } catch(e){}
    let serverInvs = window.SERVER_SINGLE_PAYMENTS || [];
    let map = new Map();
    const mergeSmart = (oldObj, newObj) => {
        const res = Object.assign({}, oldObj);
        Object.keys(newObj).forEach(k => {
            const val = newObj[k];
            if (val !== undefined && val !== null && val !== '') {
                if (Array.isArray(val) && val.length === 0 && Array.isArray(res[k]) && res[k].length > 0) return;
                if ((val === 0 || val === '0') && (typeof res[k] === 'number' && res[k] > 0)) return;
                res[k] = val;
            } else if (res[k] === undefined) {
                res[k] = val;
            }
        });
        return res;
    };
    const putVoucher = (inv) => {
        if (!inv || typeof inv !== 'object') return;
        const vid = String(inv.voucherNo || inv.id || inv.paymentNo || inv.receiptNo || '').trim();
        if (!vid) return;
        const normalized = Object.assign({}, inv, { id: vid, voucherNo: vid });
        if (!map.has(vid)) {
            map.set(vid, normalized);
        } else {
            map.set(vid, mergeSmart(map.get(vid), normalized));
        }
    };
    serverInvs.forEach(putVoucher);
    mobInvs.forEach(putVoucher);
    cacheInvs.forEach(putVoucher);
    offQueue.forEach(putVoucher);
    uniQueue.forEach(putVoucher);
    localInvs.forEach(putVoucher);
    tenantInvs.forEach(putVoucher);
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

// ════════════════════════════════════════════════════════════════
// شروط التحويل المالي لنسخة اللابتوب:
// 1. عدم حفظ أي تحويلة أقل من 6 أرقام نهائياً.
// 2. منع تكرار آخر ستة أرقام على اليمين نهائياً مع أي تحويلة أخرى.
// ════════════════════════════════════════════════════════════════

function showShortTransferBlockingModal(transferNo) {
    const existing = document.getElementById('critical-alert-modal');
    if (existing) existing.remove();

    const closeModal = () => {
        const m = document.getElementById('critical-alert-modal');
        if (m) m.remove();
        document.removeEventListener('keydown', handleEsc);
    };
    const handleEsc = (e) => { if (e.key === 'Escape') closeModal(); };
    document.addEventListener('keydown', handleEsc);

    const cleanDigits = String(transferNo || '').replace(/\D/g, '');
    const count = cleanDigits.length;

    const modalHTML = `
        <div id="critical-alert-modal" onclick="if(event.target===this) document.getElementById('critical-alert-modal').remove()" style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(10,0,0,0.88); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:99999; display:flex; justify-content:center; align-items:center; padding:15px; box-sizing:border-box;">
            <div style="position:relative; width:min(460px, 94vw); max-width:460px; background: radial-gradient(circle, #8b0000 0%, #2b0000 100%); border-radius:24px; display:flex; flex-direction:column; justify-content:center; align-items:center; border: 3px solid rgba(255,50,50,0.7); padding: 35px 25px; text-align:center; box-sizing:border-box; box-shadow:0 0 50px rgba(220, 20, 60, 0.5);">
                <button type="button" onclick="document.getElementById('critical-alert-modal').remove()" style="position:absolute; top:12px; left:12px; width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:#fff; font-size:16px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;" onmouseover="this.style.background='rgba(255,0,0,0.5)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
                <div style="font-size: 4rem; text-shadow: 0 0 20px #ff0000; margin-bottom: 5px;">🚫</div>
                <h2 style="color: #ffcccc; font-family: 'Tajawal', sans-serif; font-size: 1.8rem; margin: 8px 0; text-shadow: 0 0 10px #ff0000;">ممنوع الحفظ: رقم التحويلة أقل من 6 أرقام</h2>
                <div style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,100,100,0.4); border-radius: 12px; padding: 12px 18px; margin: 12px 0; width: 100%; box-sizing: border-box;">
                    <div style="color: #cbd5e1; font-size: 0.95rem; margin-bottom: 4px;">رقم التحويلة المدخل:</div>
                    <div style="color: #fbbf24; font-size: 2rem; font-family: monospace; font-weight: 900; letter-spacing: 2px;">${transferNo || 'فارغ'}</div>
                    <div style="color: #f87171; font-size: 0.9rem; margin-top: 4px; font-weight: bold;">(يحتوي على ${count} خانات فقط - الحد الأدنى 6 أرقام)</div>
                </div>
                <p style="color: #f1f5f9; font-size: 1.05rem; line-height: 1.6; margin: 10px 0 22px;">
                    ⚠️ <strong>شروط التحويل المالي:</strong> يمنع النظام منعاً باتاً حفظ أي تحويلة يقل عدد أرقامها عن 6 أرقام.
                </p>
                <div style="display:flex; gap:10px; width:100%;">
                    <button type="button" onclick="document.getElementById('critical-alert-modal').remove()" style="flex:1; background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; border: 2px solid #fca5a5; border-radius: 20px; padding: 12px 20px; font-size: 1.05rem; font-weight: 900; cursor: pointer; box-shadow: 0 0 20px rgba(239,68,68,0.5); font-family: 'Tajawal', sans-serif;">تعديل رقم التحويلة الآن</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}
window.showLengthWarningModal = showShortTransferBlockingModal; // backwards compatibility

function showDuplicateWarningModal(transferNo, dup = null) {
    const existing = document.getElementById('duplicate-alert-modal');
    if (existing) existing.remove();

    const closeModal = () => {
        const m = document.getElementById('duplicate-alert-modal');
        if (m) m.remove();
        document.removeEventListener('keydown', handleEsc);
    };
    const handleEsc = (e) => { if (e.key === 'Escape') closeModal(); };
    document.addEventListener('keydown', handleEsc);

    const cleanDigits = String(transferNo || '').replace(/\D/g, '');
    const candSuffix = cleanDigits.length >= 6 ? cleanDigits.slice(-6) : cleanDigits;
    const vNo = dup?.voucherNo || 'سند سابق';
    const date = dup?.date ? `بتاريخ: ${dup.date}` : '';
    const bank = dup?.bank ? `البنك: ${dup.bank}` : '';
    const amount = dup?.amount ? `المبلغ: ${Number(dup.amount).toLocaleString('en-US')} ج.س` : '';
    const matchType = dup?.matchType || 'تطابق آخر 6 أرقام';
    const matchedRef = dup?.ref || '';

    const modalHTML = `
        <div id="duplicate-alert-modal" onclick="if(event.target===this) document.getElementById('duplicate-alert-modal').remove()" style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:radial-gradient(circle at center, rgba(30,0,0,0.92) 0%, rgba(0,0,0,1) 100%); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:99999; display:flex; justify-content:center; align-items:center; padding:15px; box-sizing:border-box;">
            <div style="position:relative; width:min(500px, 94vw); background: rgba(15,5,10,0.96); transform-origin: center; display:flex; flex-direction:column; justify-content:center; align-items:center; border: 3px solid #ff0044; border-radius:24px; padding: 30px 25px; text-align:center; z-index:2; box-shadow:0 0 50px rgba(255,0,68,0.4); box-sizing:border-box;">
                <button type="button" onclick="document.getElementById('duplicate-alert-modal').remove()" style="position:absolute; top:12px; left:12px; width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:#fff; font-size:16px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;" onmouseover="this.style.background='rgba(255,0,68,0.5)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
                <div style="font-size: 4rem; line-height:1; color: #ff0044; text-shadow: 0 0 20px #ff0044; font-family: monospace; font-weight:900;">⚠️ تكرار محظور!</div>
                <h2 style="color: #fff; font-family: 'Tajawal', sans-serif; font-size: 1.8rem; margin: 12px 0 8px;">رقم التحويلة المالية مكرر</h2>
                <div style="width:100%; height:2px; background:#ff0044; margin:8px 0 14px;"></div>
                
                <div style="background: rgba(255,0,68,0.1); border: 1px solid rgba(255,0,68,0.4); border-radius: 12px; padding: 12px 16px; width: 100%; box-sizing: border-box; margin-bottom: 12px; text-align: right;">
                    <div style="color: #cbd5e1; font-size: 0.9rem; margin-bottom: 4px;">رقم التحويلة المطلوب حفظها:</div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:#fff; font-family:monospace; font-size:1.4rem; font-weight:bold;">${transferNo}</span>
                        <span style="background:#ff0044; color:#fff; padding:2px 8px; border-radius:6px; font-size:0.85rem; font-weight:bold;">آخر 6 أرقام: ${candSuffix}</span>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 12px 16px; width: 100%; box-sizing: border-box; margin-bottom: 16px; text-align: right;">
                    <div style="color: #fca5a5; font-size: 0.9rem; margin-bottom: 6px; font-weight:bold;">بيانات التحويلة المسجلة مسبقاً (${matchType}):</div>
                    <div style="color: #e2e8f0; font-size: 0.95rem; line-height: 1.6;">
                        <div>📌 <strong>السند:</strong> <span style="color:#38bdf8; font-weight:bold;">${vNo}</span> ${matchedRef ? `(الرقم المسجل: ${matchedRef})` : ''}</div>
                        ${date ? `<div>📅 ${date}</div>` : ''}
                        ${bank ? `<div>🏦 ${bank}</div>` : ''}
                        ${amount ? `<div>💰 ${amount}</div>` : ''}
                    </div>
                </div>

                <p style="color: #fca5a5; font-size: 0.95rem; margin-bottom: 20px; line-height:1.5;">
                    ⛔ <strong>قاعدة منع التكرار:</strong> يجب ألا تتطابق آخر ستة أرقام مع أي تحويلة أخرى نهائياً. لا يمكن حفظ هذا السند إلا بعد تصحيح رقم التحويلة.
                </p>

                <div style="display:flex; gap:10px; width:100%;">
                    <button type="button" onclick="document.getElementById('duplicate-alert-modal').remove()" style="flex:1; background: linear-gradient(135deg, #ff0044, #990029); color: white; border: 2px solid #ff6699; border-radius:20px; padding: 10px 20px; font-size: 1.05rem; font-weight: 900; cursor: pointer; transition: 0.2s; font-family: 'Tajawal', sans-serif;">تعديل رقم التحويلة</button>
                </div>
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
    
    if (window.MagaieesPermissions && typeof window.MagaieesPermissions.filterEntitiesByClearance === 'function') {
        localCusts = window.MagaieesPermissions.filterEntitiesByClearance(localCusts);
        localSups = window.MagaieesPermissions.filterEntitiesByClearance(localSups);
        localBanks = window.MagaieesPermissions.filterEntitiesByClearance(localBanks);
        localSafes = window.MagaieesPermissions.filterEntitiesByClearance(localSafes);
        localExp = window.MagaieesPermissions.filterEntitiesByClearance(localExp);
    }

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
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('voucher-user-audit-container', {}, true);
    }
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

    // Base prefix on current date (or selected date) and extract universal sequence across all E-vouchers
    const dateInput = document.getElementById('hdr-date');
    const selectedDate = (dateInput && dateInput.value) ? new Date(dateInput.value) : new Date();
    const validDate = isNaN(selectedDate.getTime()) ? new Date() : selectedDate;
    const yy = String(validDate.getFullYear()).substring(2, 4);
    const mm = String(validDate.getMonth() + 1).padStart(2, '0');
    const datePrefix = 'E' + yy + mm;

    const extractVoucherSeq = (raw) => {
        const s = String(raw || '').trim();
        if (!s || s.startsWith('PMT-')) return 0;
        if (s.startsWith(datePrefix)) {
            const n = parseInt(s.substring(datePrefix.length), 10);
            if (!isNaN(n) && n > 0 && n < 1000000) return n;
        }
        const m = s.match(/^E\d{4}(\d{3,6})$/i);
        if (m) {
            const n = parseInt(m[1], 10);
            if (!isNaN(n) && n > 0 && n < 1000000) return n;
        }
        return 0;
    };

    let max = 0;
    spData.forEach(v => {
        const seq = extractVoucherSeq(v.id || v.voucherNo);
        if (seq > max) max = seq;
    });
    if (window.DB_NEXT_VOUCHER_NO) {
        const dbSeq = extractVoucherSeq(window.DB_NEXT_VOUCHER_NO);
        if (dbSeq - 1 > max) max = dbSeq - 1;
    }
    if (window.MagaiesOfflineSync && typeof window.MagaiesOfflineSync.getHighestKnownSeq === 'function') {
        const offMax = window.MagaiesOfflineSync.getHighestKnownSeq('save_voucher');
        if (offMax > max) max = offMax;
    }

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
    document.getElementById('exchange-rate').value = String(window.SYSTEM_USD_RATE || (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 5999) || 5999);
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
    if (window.DB_NEXT_VOUCHER_NO) {
        const vEl = document.getElementById('voucher-number');
        if (vEl) vEl.innerText = window.DB_NEXT_VOUCHER_NO;
    } else {
        setNextVoucherNumber();
    }
    updateLedgerButtons();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('voucher-user-audit-container', {}, true);
    }
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

    let vid = v.voucherNo || v.id || v.paymentNo || v.receiptNo || v.invoiceNo || '';
    if (document.getElementById('voucher-number')) document.getElementById('voucher-number').innerText = vid;

    let dt = String(v.date || v.createdAt || v.savedAt || '').split('T')[0].split(' ')[0];
    if (dt && document.getElementById('hdr-date')) document.getElementById('hdr-date').value = dt;

    let rawTo = v.toAccount || v.toAccountName || v.to || v.toName || v.depositAccount || v.accountId || v.beneficiaryName || '';
    let rawToName = v.toAccountName || v.toName || v.to || v.beneficiaryName || rawTo;
    let rawFrom = v.fromAccount || v.fromAccountName || v.from || v.fromName || v.withdrawalAccount || v.cashSafe || v.mainAccount || 'safe-001';
    let rawFromName = v.fromAccountName || v.fromName || v.from || rawFrom;

    // مطابقة الحساب بالـ ID أو الاسم العربي في قائمة ACCOUNTS
    const matchToObj = (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS))
        ? ACCOUNTS.find(a => a && (String(a.id) === String(rawTo) || a.name === rawTo || a.name === rawToName))
        : null;
    const matchFromObj = (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS))
        ? ACCOUNTS.find(a => a && (String(a.id) === String(rawFrom) || a.name === rawFrom || a.name === rawFromName))
        : null;

    let toAcc = matchToObj ? matchToObj.id : rawTo;
    let toAccDisplay = matchToObj ? matchToObj.name : (rawToName || getAccountName(toAcc) || toAcc);

    let fromAcc = matchFromObj ? matchFromObj.id : rawFrom;
    let fromAccDisplay = matchFromObj ? matchFromObj.name : (rawFromName || getAccountName(fromAcc) || fromAcc);

    if (document.getElementById('to-account')) document.getElementById('to-account').value = toAcc;
    if (document.getElementById('to-account-search')) document.getElementById('to-account-search').value = toAccDisplay;

    let amt = parseFloat(v.amountLocal ?? v.sdg ?? v.amountSdg ?? v.amount ?? v.totalAmount ?? 0) || 0;
    let rawRate = parseFloat(v.rate ?? v.exchangeRate ?? v.usdRate ?? 0) || 0;
    let rawForeign = parseFloat(v.amountForeign ?? v.usd ?? v.amountUsd ?? v.foreignAmt ?? 0) || 0;

    let rate = rawRate;
    if (rate <= 1 && amt > 0 && rawForeign > 0) {
        rate = Math.round((amt / rawForeign) * 100) / 100;
    }
    if (rate <= 0) {
        rate = parseFloat(window.SYSTEM_USD_RATE) || (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency('USD') : 5999) || 5999;
    }

    let amtForeign = rawForeign > 0 ? rawForeign : (rate > 0 ? (amt / rate) : 0);
    if (amt <= 0 && amtForeign > 0 && rate > 0) {
        amt = amtForeign * rate;
    }

    if (document.getElementById('amount-local')) document.getElementById('amount-local').value = fmtNum(amt);
    if (document.getElementById('currency')) document.getElementById('currency').value = v.currency || 'SDG';
    if (document.getElementById('exchange-rate')) document.getElementById('exchange-rate').value = rate;
    if (document.getElementById('amount-foreign')) document.getElementById('amount-foreign').value = fmtNum(Number(amtForeign.toFixed(2)));

    const firstTr = (v.bankTransfers && Array.isArray(v.bankTransfers) && v.bankTransfers[0]) ? v.bankTransfers[0] : {};
    const stmtText = String(v.toStatement || v.notes || v.statement || v.description || firstTr.statement || '');
    const fromStmtText = String(v.fromStatement || v.notes || v.statement || v.description || firstTr.statement || '');
    if (document.getElementById('to-statement')) document.getElementById('to-statement').value = stmtText;
    if (document.getElementById('beneficiary-name')) document.getElementById('beneficiary-name').value = v.beneficiaryName || v.beneficiary || toAccDisplay || '';

    if (document.getElementById('from-account')) document.getElementById('from-account').value = fromAcc;
    if (document.getElementById('from-account-search')) document.getElementById('from-account-search').value = fromAccDisplay;
    if (window.fromAccChoice) {
        try { window.fromAccChoice.setChoiceByValue(fromAcc); } catch(e) {}
    }

    const costCenterEl = document.getElementById('cost-center');
    if (costCenterEl) {
        const ccVal = v.costCenter || v.jobId || '';
        costCenterEl.value = ccVal;
        if (window.ccChoice) {
            try { window.ccChoice.setChoiceByValue(ccVal); } catch(e) {}
        }
    }

    if (document.getElementById('from-statement')) document.getElementById('from-statement').value = fromStmtText;

    const refNo = String(v.refNo ?? v.mobileTransferNo ?? v.transferNo ?? v.transferRef ?? firstTr.transferNo ?? v.checkNo ?? '').trim();
    const bankName = String(v.bankName ?? v.bank ?? v.mobileNote ?? firstTr.note ?? firstTr.bankName ?? '').trim();

    // Set Payment Method (يدعم صيغ الجوال وسطح المكتب والأوفلاين)
    let rawMethod = v.paymentMethod || v.method || v.payType || (v.cheques && v.cheques.length > 0 ? 'check' : ((v.bankTransfers && v.bankTransfers.length > 0) || refNo || bankName ? 'mobile' : 'cash'));
    if (rawMethod === 'transfer' || rawMethod === 'bank') rawMethod = 'mobile';
    if (rawMethod === 'normal_cheque' || rawMethod === 'bank_cheque' || rawMethod === 'cheque') rawMethod = 'check';
    if (rawMethod === 'cash' && (refNo || bankName || (v.bankTransfers && v.bankTransfers.length > 0))) {
        rawMethod = 'mobile';
    }

    const methodEl = document.querySelector(`input[name="payment_method"][value="${rawMethod}"]`);
    if (methodEl) {
        methodEl.checked = true;
    } else {
        const firstMethod = document.querySelector(`input[name="payment_method"]`);
        if (firstMethod) firstMethod.checked = true;
    }

    togglePaymentMethod();

    if (typeof loadVoucherPaymentDetails === 'function') {
        loadVoucherPaymentDetails(v);
    }

    if (refNo && document.getElementById('mobile-transfer-no') && !document.getElementById('mobile-transfer-no').value) {
        document.getElementById('mobile-transfer-no').value = refNo;
    }
    if (bankName && document.getElementById('mobile-bank-name') && !document.getElementById('mobile-bank-name').value) {
        document.getElementById('mobile-bank-name').value = bankName;
    }

    updateCurrency(true); // pass true to prevent overwriting historical exchange rate
    // تثبيت قيم السعر والدولار والمبلغ المحلي المحفوظة في السند بدقة تامة
    const amtEl = document.getElementById('amount-local');
    const rateEl = document.getElementById('exchange-rate');
    const eqEl = document.getElementById('amount-foreign');
    if (amtEl) {
        if (amtEl.animationInterval) clearInterval(amtEl.animationInterval);
        amtEl.value = fmtNum(amt);
    }
    if (rateEl) {
        if (rateEl.animationInterval) clearInterval(rateEl.animationInterval);
        rateEl.value = rate;
    }
    if (eqEl) {
        if (eqEl.animationInterval) clearInterval(eqEl.animationInterval);
        eqEl.value = fmtNum(Number(amtForeign.toFixed(2)));
    }

    updateBalances();
    updateLedgerButtons();
    if (typeof updateActionButtonsVisibility === 'function') updateActionButtonsVisibility();
    if (window.renderDocumentUserBadge) {
        window.renderDocumentUserBadge('voucher-user-audit-container', v, false);
    }
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

function extractLast6Digits(str) {
    if (!str) return '';
    const digitsOnly = String(str).replace(/\D/g, '');
    if (digitsOnly.length >= 6) {
        return digitsOnly.slice(-6);
    }
    return '';
}

function checkTransferDuplicate(tno, currentVoucherId) {
    if (!tno) return null;
    const rawTarget = String(tno).trim();
    const cleanDigits = rawTarget.replace(/\D/g, '');

    // الشرط الأول: لا يمكن الحفظ إذا كان أقل من 6 أرقام
    if (cleanDigits.length < 6) {
        return {
            duplicate: false,
            isShort: true,
            length: cleanDigits.length,
            ref: rawTarget
        };
    }

    // استخراج آخر ستة أرقام على اليمين للمقارنة
    const candSuffix = cleanDigits.slice(-6);

    // 1. فحص التكرار مع أسطر التحويل الأخرى المدخلة حالياً في نفس الشاشة
    const transferRows = document.querySelectorAll('.transfer-row');
    let screenMatches = 0;
    for (const row of transferRows) {
        const otherNo = (row.querySelector('.transfer-no')?.value || '').trim();
        const otherDigits = otherNo.replace(/\D/g, '');
        if (otherDigits.length >= 6 && otherDigits.slice(-6) === candSuffix) {
            screenMatches++;
            if (screenMatches > 1) {
                return {
                    duplicate: true,
                    isShort: false,
                    voucherNo: 'نفس السند (سطر آخر على الشاشة)',
                    ref: otherNo,
                    matchedSuffix: candSuffix,
                    matchType: 'تطابق آخر 6 أرقام مع سطر تحويل آخر مدخل حالياً'
                };
            }
        }
    }

    // Helper to evaluate a reference against candSuffix
    const evaluateRef = (ref, voucher) => {
        if (!ref) return null;
        const refStr = String(ref).trim();
        const refDigits = refStr.replace(/\D/g, '');
        if (!refDigits) return null;

        let isMatch = false;
        let matchedSuffix = '';
        let matchType = '';

        if (refDigits.length >= 6) {
            const refSuffix = refDigits.slice(-6);
            if (refSuffix === candSuffix) {
                isMatch = true;
                matchedSuffix = candSuffix;
                matchType = 'تطابق آخر 6 أرقام على اليمين';
            }
        } else if (refDigits.length > 0) {
            if (cleanDigits === refDigits || cleanDigits.slice(-refDigits.length) === refDigits) {
                isMatch = true;
                matchedSuffix = refDigits;
                matchType = 'تطابق مع تحويلة قديمة';
            }
        }

        if (isMatch) {
            return {
                duplicate: true,
                isShort: false,
                voucherNo: voucher.voucherNo || voucher.id || 'سابق',
                ref: refStr,
                date: voucher.date || '',
                amount: voucher.amountLocal || voucher.amount || 0,
                bank: voucher.bankName || voucher.bank || voucher.note || '',
                from: voucher.fromAccount || voucher.from || '',
                to: voucher.toAccount || voucher.to || voucher.beneficiaryName || '',
                matchedSuffix: matchedSuffix,
                matchType: matchType
            };
        }
        return null;
    };

    // 2. فحص مصفوفة التحويلات الحية من قاعدة بيانات MariaDB (0ms)
    if (Array.isArray(window.DB_ALL_TRANSFER_REFS)) {
        for (const item of window.DB_ALL_TRANSFER_REFS) {
            if (!item) continue;
            if (currentVoucherId && (String(item.voucherNo) === String(currentVoucherId))) continue;
            const res = evaluateRef(item.ref || item.cleanRef, item);
            if (res) return res;
        }
    }

    // 3. فحص سندات السيرفر المحملة مسبقاً SERVER_SINGLE_PAYMENTS
    if (Array.isArray(window.SERVER_SINGLE_PAYMENTS)) {
        for (const v of window.SERVER_SINGLE_PAYMENTS) {
            if (!v || v.deleted) continue;
            if (currentVoucherId && (String(v.id) === String(currentVoucherId) || String(v.voucherNo) === String(currentVoucherId))) continue;

            const refs = [];
            if (v.mobileTransferNo) refs.push(v.mobileTransferNo);
            if (v.transferRef) refs.push(v.transferRef);
            if (v.refNo) refs.push(v.refNo);
            if (Array.isArray(v.bankTransfers)) {
                v.bankTransfers.forEach(bt => { if (bt.transferNo) refs.push(bt.transferNo); });
            }
            for (const r of refs) {
                const res = evaluateRef(r, v);
                if (res) return res;
            }
        }
    }

    // 4. فحص كافة مخازن التخزين المحلي (LocalStorage) في النظام
    const getTenantKey = window.getTenantKey || ((k) => k);
    const stores = [
        getTenantKey('erp_single_payments_v1'),
        getTenantKey('erp_payments_v1'),
        getTenantKey('erp_receipts_v1'),
        getTenantKey('erp_single_receipts_v1'),
        getTenantKey('erp_comprehensive_payments_v1'),
        getTenantKey('erp_comprehensive_receipts_v1'),
        getTenantKey('erp_journal_v1'),
        getTenantKey('erp_journal_vouchers_v1'),
        getTenantKey('erp_bank_transfers_v1')
    ];

    for (const key of stores) {
        let list = [];
        try {
            list = JSON.parse(localStorage.getItem(key)) || [];
        } catch(e) { continue; }

        for (const v of list) {
            if (!v || v.deleted) continue;
            if (currentVoucherId && (String(v.id) === String(currentVoucherId) || String(v.voucherNo) === String(currentVoucherId))) continue;

            const refs = [];
            if (v.mobileTransferNo) refs.push(v.mobileTransferNo);
            if (v.transferRef) refs.push(v.transferRef);
            if (v.refNo) refs.push(v.refNo);
            if (Array.isArray(v.bankTransfers)) {
                v.bankTransfers.forEach(bt => { if (bt.transferNo) refs.push(bt.transferNo); });
            }
            if (Array.isArray(v.items)) {
                v.items.forEach(item => {
                    if (item.transferNo) refs.push(item.transferNo);
                    if (Array.isArray(item.bankTransfers)) {
                        item.bankTransfers.forEach(bt => { if (bt.transferNo) refs.push(bt.transferNo); });
                    }
                });
            }
            if (Array.isArray(v.details)) {
                v.details.forEach(det => {
                    if (det.transferNo) refs.push(det.transferNo);
                    if (Array.isArray(det.bankTransfers)) {
                        det.bankTransfers.forEach(bt => { if (bt.transferNo) refs.push(bt.transferNo); });
                    }
                });
            }
            if (Array.isArray(v.subTransactions)) {
                v.subTransactions.forEach(sub => {
                    if (sub.transferNo) refs.push(sub.transferNo);
                });
            }

            for (const r of refs) {
                const res = evaluateRef(r, v);
                if (res) return res;
            }
        }
    }

    return null; // سليم وغير مكرر
}

// دالة فحص وتأكيد رقم التحويلة عند مغادرة الحقل (Blur) فورياً
window.handleTransferNoBlur = async function(input) {
    if (!input) return;
    const val = (input.value || '').trim();
    if (!val) {
        input.style.borderColor = 'rgba(255,255,255,0.1)';
        return;
    }
    const cleanDigits = val.replace(/\D/g, '');
    const currentVNo = (document.getElementById('voucher-number')?.innerText || '').trim();

    // 1. فحص طول رقم التحويلة (يجب ألا يقل عن 6 أرقام)
    if (cleanDigits.length < 6) {
        input.style.borderColor = '#ef4444';
        showToast(`رقم التحويلة (${val}) أقل من 6 أرقام! يمنع النظام حفظ أي تحويلة أقل من 6 أرقام.`, 'error');
        return;
    }

    // 2. فحص منع تكرار آخر 6 أرقام محلياً في الذاكرة
    const localDup = checkTransferDuplicate(val, currentVNo);
    if (localDup && localDup.duplicate) {
        input.style.borderColor = '#ef4444';
        showDuplicateWarningModal(val, localDup);
        return;
    }

    // 3. فحص حي ومباشر عبر الـ API مع قاعدة بيانات MariaDB
    try {
        const res = await fetch(`single-cash-payment.php?api=check_duplicate&ref=${encodeURIComponent(val)}&voucherNo=${encodeURIComponent(currentVNo)}`);
        if (res.ok) {
            const data = await res.json();
            if (data && !data.valid && data.duplicate) {
                input.style.borderColor = '#ef4444';
                showDuplicateWarningModal(val, data);
                return;
            }
        }
    } catch(e) {}

    input.style.borderColor = '#10b981';
    setTimeout(() => { input.style.borderColor = 'rgba(255,255,255,0.1)'; }, 2000);
};


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

    const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };
    singlePaymentsData = (typeof window.getAllSinglePayments === 'function') ? window.getAllSinglePayments() : (singlePaymentsData || []);
    const oldVoucher = (currentViewedIndex !== -1 && singlePaymentsData && singlePaymentsData[currentViewedIndex]) ? singlePaymentsData[currentViewedIndex] : null;

    if (!oldVoucher) {
        const vSig = `${fromAcc}|${toAcc}|${amtLocal}|${document.getElementById('to-statement')?.value || ''}|${document.getElementById('hdr-date')?.value || ''}`;
        if (window._lastSubmittedVoucherSig === vSig && (Date.now() - (window._lastSubmittedVoucherTime || 0)) < 15000) {
            showToast(`✅ تم حفظ هذا السند مسبقاً برقم (${window._lastSubmittedVoucherId || ''}) — لن يتم تكرار الحفظ`, 'info', 3500);
            window.isSubmitting = false;
            return;
        }
        window._lastSubmittedVoucherSig = vSig;
        window._lastSubmittedVoucherTime = Date.now();
        window._lastSubmittedVoucherId = document.getElementById('voucher-number')?.innerText || '';
    }

    const vIdVal = (document.getElementById('voucher-number')?.innerText || '').trim();
    const rateVal = parseNum(document.getElementById('exchange-rate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 5999;
    const foreignVal = parseNum(document.getElementById('amount-foreign')?.value) || (rateVal > 0 ? Number((amtLocal / rateVal).toFixed(2)) : 0);
    const toStmtVal = (document.getElementById('to-statement')?.value || '').trim();
    const fromStmtVal = (document.getElementById('from-statement')?.value || '').trim();
    const fromSearchVal = (document.getElementById('from-account-search')?.value || '').trim();
    const toSearchVal = (document.getElementById('to-account-search')?.value || '').trim();

    const voucherData = {
        createdBy: oldVoucher?.createdBy || curUser.id,
        createdByName: oldVoucher?.createdByName || curUser.fullName || curUser.name,
        createdByUsername: oldVoucher?.createdByUsername || curUser.username,
        updatedBy: oldVoucher ? curUser.id : (oldVoucher?.updatedBy || null),
        updatedByName: oldVoucher ? (curUser.fullName || curUser.name) : (oldVoucher?.updatedByName || null),
        updatedAt: oldVoucher ? new Date().toISOString() : (oldVoucher?.updatedAt || null),
        id: vIdVal,
        voucherNo: vIdVal,
        idempotencyKey: window._lastSubmittedVoucherSig ? ('LAP-E-' + btoa(unescape(encodeURIComponent(window._lastSubmittedVoucherSig))).substring(0, 32)) : ('LAP-E-' + vIdVal),
        date: document.getElementById('hdr-date').value,
        time: new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}),
        type: 'single_payment',
        
        toAccount: toAcc,
        toAccountName: toSearchVal || getAccountName(toAcc) || toAcc,
        to: toSearchVal || getAccountName(toAcc) || toAcc,
        toName: toSearchVal || getAccountName(toAcc) || toAcc,
        amountLocal: amtLocal,
        sdg: amtLocal,
        amountSdg: amtLocal,
        amount: amtLocal,
        currency: document.getElementById('currency').value,
        rate: rateVal,
        exchangeRate: rateVal,
        amountForeign: foreignVal,
        usd: foreignVal,
        amountUsd: foreignVal,
        foreignAmt: foreignVal,
        toStatement: toStmtVal,
        beneficiaryName: document.getElementById('beneficiary-name').value || toSearchVal || toAcc,
        
        fromAccount: document.getElementById('from-account').value,
        fromAccountName: fromSearchVal || getAccountName(document.getElementById('from-account').value) || document.getElementById('from-account').value,
        from: fromSearchVal || getAccountName(document.getElementById('from-account').value) || document.getElementById('from-account').value,
        fromName: fromSearchVal || getAccountName(document.getElementById('from-account').value) || document.getElementById('from-account').value,
        fromStatement: fromStmtVal,
        notes: toStmtVal || fromStmtVal || '',
        statement: toStmtVal || fromStmtVal || '',
        description: toStmtVal || fromStmtVal || '',
        costCenter: document.getElementById('cost-center') ? document.getElementById('cost-center').value : '',
        
        paymentMethod: method,
        payType: method === 'mobile' ? 'transfer' : method,
        
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
            const cleanDigits = trNo.replace(/\D/g, '');
            if (cleanDigits.length < 6) {
                showToast(`السطر ${idx + 1}: لا يمكن الحفظ! رقم التحويلة (${trNo}) أقل من 6 أرقام.`, "error");
                showShortTransferBlockingModal(trNo);
                row.querySelector('.transfer-no').focus();
                row.querySelector('.transfer-no').style.borderColor = '#ef4444';
                hasInvalidTransfer = true;
                return;
            }

            const currentVNo = (document.getElementById('voucher-number')?.innerText || '').trim();
            const dup = checkTransferDuplicate(trNo, currentVNo);
            if (dup && dup.duplicate) {
                showDuplicateWarningModal(trNo, dup);
                row.querySelector('.transfer-no').focus();
                row.querySelector('.transfer-no').style.borderColor = '#ef4444';
                hasInvalidTransfer = true;
                return;
            }

            transfers.push({ transferNo: trNo, note: trNote, bankName: trNote, amount: trAmt, statement: trStmt });
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
            voucherData.refNo = transfers[0].transferNo;
            voucherData.transferNo = transfers[0].transferNo;
            voucherData.mobileNote = transfers[0].note;
            voucherData.bankName = transfers[0].note;
            voucherData.bank = transfers[0].note;
            if (!voucherData.notes && transfers[0].statement) {
                voucherData.notes = transfers[0].statement;
                voucherData.statement = transfers[0].statement;
                voucherData.toStatement = transfers[0].statement;
            }
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
            voucherData.refNo = cheques[0].checkNo;
            voucherData.checkBranch = cheques[0].checkBranch;
            voucherData.bankName = cheques[0].checkBranch;
            voucherData.bank = cheques[0].checkBranch;
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
            const cleanDigits = trNo.replace(/\D/g, '');
            if (cleanDigits.length < 6) {
                showToast(`السطر ${idx + 1}: لا يمكن الحفظ! رقم التحويلة (${trNo}) أقل من 6 أرقام.`, "error");
                showShortTransferBlockingModal(trNo);
                if (trNoInput) {
                    trNoInput.focus();
                    trNoInput.style.borderColor = '#ef4444';
                }
                hasInvalidTransfer = true;
                return;
            }

            const currentVNo = (document.getElementById('voucher-number')?.innerText || '').trim();
            const dup = checkTransferDuplicate(trNo, currentVNo);
            if (dup && dup.duplicate) {
                showDuplicateWarningModal(trNo, dup);
                if (trNoInput) {
                    trNoInput.focus();
                    trNoInput.style.borderColor = '#ef4444';
                }
                hasInvalidTransfer = true;
                return;
            }

            transfers.push({ transferNo: trNo, note: trNote, bankName: trNote, amount: trAmt, statement: trStmt });
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
            voucherData.refNo = transfers[0].transferNo;
            voucherData.transferNo = transfers[0].transferNo;
            voucherData.mobileNote = transfers[0].note;
            voucherData.bankName = transfers[0].note;
            voucherData.bank = transfers[0].note;
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
        let fromName = fromSearch && fromSearch.value ? fromSearch.value : voucherData.fromAccountName;
        let toSearch = document.getElementById('to-account-search');
        let toName = toSearch && toSearch.value ? toSearch.value : voucherData.toAccountName;
        voucherData.fromAccountName = fromName;
        voucherData.from = fromName;
        voucherData.toAccountName = toName;
        voucherData.to = toName;
        
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
            if (tKey !== 'erp_single_payments_v1') localStorage.setItem('erp_single_payments_v1', JSON.stringify(singlePaymentsData));
            localStorage.setItem('magaies_vouchers_cache', JSON.stringify(singlePaymentsData));
            localStorage.setItem('magaiees_vouchers_history', JSON.stringify(singlePaymentsData));
            window.SERVER_SINGLE_PAYMENTS = singlePaymentsData;
            if (typeof window.rebuildGLFromAllTables === 'function') window.rebuildGLFromAllTables(false);
            if (window.logSystemAction) {
                window.logSystemAction('تعديل سند صرف', 'المعاملات المالية', voucherData.id, `تعديل سند صرف رقم ${voucherData.id} بمبلغ ${voucherData.amountLocal} ${voucherData.currency} من ${fromName} إلى ${toName}`);
            }
            if (window.renderDocumentUserBadge) {
                window.renderDocumentUserBadge('voucher-user-audit-container', voucherData, false);
            }
            showProfessionalSuccess(voucherData.id, fromName, toName, voucherData.amountLocal, voucherData.currency, 'تم التعديل بنجاح');
            if (typeof window.triggerAutoWADispatchOnSave === 'function') {
                window.triggerAutoWADispatchOnSave(voucherData, voucherData.id);
            }
            // مزامنة فورية مباشرة مع قاعدة البيانات MariaDB
            try {
                if (!navigator.onLine) {
                    voucherData.isOffline = true;
                    if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                        window.MagaiesOffline.enqueue('finance', voucherData, voucherData.id);
                    }
                } else {
                    fetch('api/mirror_sync.php?action=save_voucher', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(Object.assign({}, voucherData, { isEdit: true }))
                    }).catch(() => {});
                    fetch('single-cash-payment.php?api=save_voucher', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(voucherData)
                    }).catch(e => {
                        console.warn('[MariaDB Sync] Edit sync failed:', e);
                        if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                            window.MagaiesOffline.enqueue('finance', voucherData, voucherData.id);
                        }
                    });
                }
            } catch(e) {}
        } else {
            applyPaymentEffect(voucherData.fromAccount, voucherData.toAccount, voucherData.amountLocal, false);
            singlePaymentsData.push(voucherData);
            currentViewedIndex = singlePaymentsData.length - 1; // Keep the saved voucher on screen
            const tKey = getTenantKey('erp_single_payments_v1');
            localStorage.setItem(tKey, JSON.stringify(singlePaymentsData));
            if (tKey !== 'erp_single_payments_v1') localStorage.setItem('erp_single_payments_v1', JSON.stringify(singlePaymentsData));
            localStorage.setItem('magaies_vouchers_cache', JSON.stringify(singlePaymentsData));
            localStorage.setItem('magaiees_vouchers_history', JSON.stringify(singlePaymentsData));
            window.SERVER_SINGLE_PAYMENTS = singlePaymentsData;
            const currentSeq = parseInt(localStorage.getItem(getTenantKey('erp_single_payment_seq')) || localStorage.getItem('erp_single_payment_seq') || '0');
            localStorage.setItem('erp_single_payment_seq', currentSeq + 1);
            if (typeof window.rebuildGLFromAllTables === 'function') window.rebuildGLFromAllTables(false);
            if (window.logSystemAction) {
                window.logSystemAction('حفظ سند صرف', 'المعاملات المالية', voucherData.id, `إصدار سند صرف رقم ${voucherData.id} بمبلغ ${voucherData.amountLocal} ${voucherData.currency} من ${fromName} إلى ${toName}`);
            }
            if (window.renderDocumentUserBadge) {
                window.renderDocumentUserBadge('voucher-user-audit-container', voucherData, false);
            }
            showProfessionalSuccess(voucherData.id, fromName, toName, voucherData.amountLocal, voucherData.currency, 'تم الحفظ بنجاح');
            if (typeof window.triggerAutoWADispatchOnSave === 'function') {
                window.triggerAutoWADispatchOnSave(voucherData, voucherData.id);
            }
            // مزامنة فورية مباشرة مع قاعدة البيانات MariaDB + طابور الأوفلاين الموحد
            try {
                if (!navigator.onLine) {
                    voucherData.isOffline = true;
                    if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                        window.MagaiesOffline.enqueue('finance', voucherData, voucherData.id);
                    }
                } else {
                    fetch('api/mirror_sync.php?action=save_voucher', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(voucherData)
                    }).catch(() => {});
                    fetch('single-cash-payment.php?api=save_voucher', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(voucherData)
                    }).catch(e => {
                        console.warn('[MariaDB Sync] Save sync failed, enqueuing offline:', e);
                        if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                            window.MagaiesOffline.enqueue('finance', voucherData, voucherData.id);
                        }
                    });
                }
            } catch(e) {}
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

        <!-- Security Stripe & User Attribution Audit Footer -->
        <div style="margin-top: 8px; border-top: 1px dashed #cbd5e1; padding-top: 6px;">
            <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.70rem; color:#64748b; font-weight:800; margin-bottom: 4px;">
                <div>VERIFICATION: <span style="font-family:monospace; color:#0f172a; font-weight:900;">#DOC-${voucher.id}-${String(voucher.date).replace(/-/g, '')}</span></div>
                <div>وثيقة مالية معتمدة ومقيدة بالنظام المحاسبي العام ELSAJANA ERP 🔒</div>
            </div>
            <div style="font-size: 0.72rem; color: #475569; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; background: #f8fafc; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                <span>👤 المستخدم (معد السند): <strong>${voucher.createdByName || voucher.createdByUsername || voucher.createdBy || '—'}</strong>${voucher.updatedByName ? ' | عُدلت بواسطة: <strong>' + voucher.updatedByName + '</strong>' : ''} | طُبع بواسطة: <strong>${((typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo().fullName : 'المسؤول')}</strong> | وقت الطباعة: ${new Date().toLocaleString('ar-EG', { dateStyle: 'short', timeStyle: 'short' })}</span>
                <span style="font-weight: 800; color: #0284c7;">نظام مقاييس المحاسبي الموحد ⚡</span>
            </div>
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
        if (window.logSystemAction) {
            window.logSystemAction('حذف سند صرف', 'المعاملات المالية', oldVoucher.id, `تم حذف سند الصرف رقم ${oldVoucher.id} بمبلغ ${oldVoucher.amountLocal} ${oldVoucher.currency}`);
        }
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
    
    if (window.MagaieesPermissions && typeof window.MagaieesPermissions.filterEntitiesByClearance === 'function') {
        localCusts = window.MagaieesPermissions.filterEntitiesByClearance(localCusts);
        localSups = window.MagaieesPermissions.filterEntitiesByClearance(localSups);
        localBanks = window.MagaieesPermissions.filterEntitiesByClearance(localBanks);
        localSafes = window.MagaieesPermissions.filterEntitiesByClearance(localSafes);
        localExp = window.MagaieesPermissions.filterEntitiesByClearance(localExp);
    }

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

function getVoucherContactDetails(accountId, accountName) {
    if (!accountId && !accountName) return null;
    const cleanId = String(accountId || '').trim();
    const cleanName = String(accountName || '').trim();
    
    // 0. Check localStorage cached phone for this account
    try {
        const cached = (cleanId ? localStorage.getItem(`erp_wa_phone_${cleanId}`) : null) || 
                       (cleanName ? localStorage.getItem(`erp_wa_phone_${cleanName}`) : null);
        if (cached) {
            return {
                name: cleanName,
                phone: cached,
                type: 'cached'
            };
        }
    } catch(e) {}

    // 1. Check customers in localStorage
    try {
        const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(x => !x.deleted);
        const customer = custs.find(c => 
            (cleanId && (String(c.id) === cleanId || String(c.id) === cleanId.replace('cust-', ''))) ||
            (cleanName && c.name && c.name.trim() === cleanName)
        );
        if (customer && (customer.phone || customer.whatsapp)) {
            return {
                name: customer.name,
                phone: customer.whatsapp || customer.phone || '',
                type: 'customer'
            };
        }
    } catch(e) {}
    
    // 2. Check suppliers in localStorage
    try {
        const sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]').filter(x => !x.deleted);
        const supplier = sups.find(s => 
            (cleanId && (String(s.id) === cleanId || String(s.id) === cleanId.replace('supp-', ''))) ||
            (cleanName && s.name && s.name.trim() === cleanName)
        );
        if (supplier && (supplier.phone || supplier.whatsapp)) {
            return {
                name: supplier.name,
                phone: supplier.whatsapp || supplier.phone || '',
                type: 'supplier'
            };
        }
    } catch(e) {}
    
    return null;
}
window.getVoucherContactDetails = getVoucherContactDetails;

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
    renderWrapper.style.width = '100vw';
    renderWrapper.style.height = '100vh';
    renderWrapper.style.zIndex = '999999999';
    renderWrapper.style.background = 'rgba(15, 23, 42, 0.85)';
    renderWrapper.style.backdropFilter = 'blur(10px)';
    renderWrapper.style.display = 'flex';
    renderWrapper.style.flexDirection = 'column';
    renderWrapper.style.alignItems = 'center';
    renderWrapper.style.justifyContent = 'center';
    renderWrapper.style.overflowY = 'auto';
    renderWrapper.style.padding = '40px 20px';

    const statusHeader = document.createElement('div');
    statusHeader.innerHTML = `
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
            <div style="width:30px; height:30px; border:3px solid #25d366; border-top-color:transparent; border-radius:50%; animation:spin 1s linear infinite;"></div>
            <h3 style="color:#25d366; font-family:'Tajawal', sans-serif; font-size:1.6rem; margin:0; font-weight:bold;">جاري إنشاء السند والإرسال عبر واتساب...</h3>
        </div>
    `;
    renderWrapper.appendChild(statusHeader);

    const innerDiv = document.createElement('div');
    innerDiv.style.width = '794px';
    innerDiv.style.maxWidth = '100%';
    innerDiv.style.background = '#ffffff';
    innerDiv.style.padding = '24px';
    innerDiv.style.borderRadius = '16px';
    innerDiv.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5)';
    innerDiv.style.transform = 'scale(0.85)';
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
            
            <input type="text" maxlength="30" class="v-input ctrl-input transfer-no dynamic-focus-input dynamic-focus-number" value="${no}" placeholder="رقم التحويلة" onfocus="this.parentElement.style.flex='15'; this.parentElement.style.minWidth='480px'; this.style.letterSpacing='2px';" onblur="this.parentElement.style.flex='1'; this.parentElement.style.minWidth='200px'; this.style.letterSpacing='normal'; if (typeof handleTransferNoBlur === 'function') handleTransferNoBlur(this);" oninput="if(this.value.replace(/\D/g,'').length>=6) this.style.borderColor='rgba(255,255,255,0.1)';" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 62px; font-size:2.8rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#2dd4bf; text-align: center; font-family: 'Inter', system-ui, sans-serif; font-variant-numeric: tabular-nums; font-weight: 900; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
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
    const firstTr = (v.bankTransfers && Array.isArray(v.bankTransfers) && v.bankTransfers[0]) ? v.bankTransfers[0] : {};
    const refNo = String(v.mobileTransferNo ?? v.refNo ?? v.transferNo ?? v.transferRef ?? firstTr.transferNo ?? '').trim();
    const bankName = String(v.mobileNote ?? v.bankName ?? v.bank ?? firstTr.note ?? firstTr.bankName ?? '').trim();
    const amt = parseFloat(v.amountLocal ?? v.sdg ?? v.amountSdg ?? v.amount ?? v.totalAmount ?? 0) || 0;
    const stmt = String(v.notes || v.toStatement || v.fromStatement || v.statement || v.description || firstTr.statement || '');

    let method = v.paymentMethod || v.method || v.payType || ((v.cheques && v.cheques.length > 0) ? 'check' : (((v.bankTransfers && v.bankTransfers.length > 0) || refNo || bankName) ? 'mobile' : 'cash'));
    if (method === 'transfer' || method === 'bank') method = 'mobile';
    if (method === 'normal_cheque' || method === 'bank_cheque' || method === 'cheque') method = 'check';
    if (method === 'cash' && (refNo || bankName || (v.bankTransfers && v.bankTransfers.length > 0))) {
        method = 'mobile';
    }

    const transfersContainer = document.getElementById('transfers-container');
    const chequesContainer = document.getElementById('cheques-container');
    if (transfersContainer) transfersContainer.innerHTML = '';
    if (chequesContainer) chequesContainer.innerHTML = '';

    if (method === 'mobile' || method === 'hybrid') {
        if (v.bankTransfers && Array.isArray(v.bankTransfers) && v.bankTransfers.length > 0) {
            v.bankTransfers.forEach(tr => {
                addTransferRow(
                    tr.transferNo || refNo || '',
                    tr.note || tr.bankName || bankName || '',
                    tr.amount || amt || 0,
                    tr.statement || stmt || ''
                );
            });
        } else {
            addTransferRow(refNo, bankName, amt, stmt);
        }
    }
    if (method === 'check' || method === 'hybrid') {
        if (v.cheques && Array.isArray(v.cheques) && v.cheques.length > 0) {
            v.cheques.forEach(ch => {
                addChequeRow(ch.checkBranch || bankName || '', ch.checkNo || refNo || '', ch.checkDate || v.date || '', ch.amount || amt || 0, ch.statement || stmt || '');
            });
        } else {
            addChequeRow(v.checkBranch || bankName || '', v.checkNo || refNo || '', v.checkDate || v.date || '', amt, stmt);
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

// ══════════════════════════════════════════════════════════════════════════════════════
// 📱 WhatsApp Enterprise Automation Suite (منظومة تكامل الواتساب الذكي والمراسلات المصرفية)
// ══════════════════════════════════════════════════════════════════════════════════════

window.fmtNum = window.fmtNum || ((n) => Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
window.cleanNum = window.cleanNum || ((n) => parseFloat(String(n || 0).replace(/[^0-9.-]/g, '')) || 0);

window.normalizePhoneNumber = function(rawPhone) {
    if (!rawPhone) return '';
    let p = String(rawPhone).replace(/[^0-9+]/g, '');
    if (p.startsWith('+')) p = p.substring(1);
    if (p.startsWith('00')) p = p.substring(2);
    // Sudan local numbers starting with 09 or 01 (e.g. 0912345678 or 0123456789)
    if (p.startsWith('0') && p.length === 10) {
        p = '249' + p.substring(1);
    } else if (p.length === 9 && (p.startsWith('9') || p.startsWith('1'))) {
        p = '249' + p;
    }
    return p;
};

// ═══ دالة حفظ كاش أرقام هواتف الأطراف اللحظي ═══
window.cachePartyPhone = function(partyType, phoneVal) {
    const parties = window.getVoucherParties();
    if (!parties) return;
    const party = (partyType === 'debtor') ? parties.debtor : parties.creditor;
    const cleanPhone = (phoneVal || '').trim();
    if (cleanPhone && party) {
        try {
            if (party.id) localStorage.setItem(`erp_wa_phone_${party.id}`, cleanPhone);
            if (party.name) localStorage.setItem(`erp_wa_phone_${party.name}`, cleanPhone);
        } catch(e) {}
    }
};

// ═══ توحيد وإدارة عملة السند والأرصدة في كروت (من وإلى) والكمبيالة المعتمدة ═══
window.getWACurrencySelectOptions = function(selectedCurr) {
    const list = [
        { code: 'SDG', name: 'SDG' },
        { code: 'USD', name: 'USD' },
        { code: 'SAR', name: 'SAR' },
        { code: 'AED', name: 'AED' },
        { code: 'EGP', name: 'EGP' }
    ];
    const curr = (selectedCurr || window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG').toUpperCase();
    return list.map(c => `<option value="${c.code}" ${c.code === curr ? 'selected' : ''}>${c.code}</option>`).join('');
};

window.getVoucherAmountForCurrency = function(targetCurrency) {
    const voucherCurrency = (document.getElementById('currency')?.value || 'SDG').toUpperCase();
    const localAmt = (typeof parseNum === 'function' ? parseNum(document.getElementById('amount-local')?.value) : window.cleanNum(document.getElementById('amount-local')?.value)) || 0;
    const foreignAmt = (typeof parseNum === 'function' ? parseNum(document.getElementById('amount-foreign')?.value) : window.cleanNum(document.getElementById('amount-foreign')?.value)) || 0;

    let baseAmtSDG = 0;
    if (voucherCurrency === 'SDG') {
        baseAmtSDG = localAmt;
    } else {
        const vRate = (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(voucherCurrency) : (voucherCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1)) || 2600;
        baseAmtSDG = localAmt * vRate;
    }

    const target = (targetCurrency || 'SDG').toUpperCase();
    if (target === 'SDG') {
        return baseAmtSDG;
    } else if (target === voucherCurrency && foreignAmt > 0) {
        return foreignAmt;
    } else {
        const targetRate = (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(target) : (target === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1)) || 1;
        return targetRate > 0 ? (baseAmtSDG / targetRate) : baseAmtSDG;
    }
};

window.setWAModalCurrency = function(newCurr) {
    if (!newCurr) return;
    window._waCurrentModalCurrency = newCurr;

    const topSelect = document.getElementById('wa-modal-currency-select');
    if (topSelect && topSelect.value !== newCurr) {
        topSelect.value = newCurr;
    }

    document.querySelectorAll('.wa-currency-chip').forEach(el => {
        if (el.value !== newCurr) el.value = newCurr;
    });

    const parties = window.getVoucherParties();
    parties.currency = newCurr;
    parties.amount = window.getVoucherAmountForCurrency(newCurr);

    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    window.setWATargetMode(targetMode);

    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    try { window.renderPromissoryNotePreview(activeTab); } catch(e) {}
    try { window.renderWAVoucherImageCard(activeTab, parties); } catch(e) {}

    const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
    window._waBothPartyMessages = {
        debtor: window.buildWATailoredMessage('debtor', parties, fmt),
        creditor: window.buildWATailoredMessage('creditor', parties, fmt)
    };

    const curPreviewText = document.getElementById('wa-both-preview-message');
    if (curPreviewText && window._waBothPartyMessages[activeTab]) {
        curPreviewText.value = window._waBothPartyMessages[activeTab];
    }

    if (typeof showToast === 'function') {
        showToast(`تم توحيد العملة في (من وإلى) إلى: ${newCurr}`, 'info');
    }
};

// ═══ منظومة حساب واستعلام رصيد دفتر الأستاذ العام اللحظي ═══
window.getPartyAccountBalance = function(partyId, partyName, targetRole = 'debtor', voucherAmt = 0, isPayment = true, forcedCurrency = null) {
    const getTenantKey = window.getTenantKey || ((k) => k);
    const cleanId = String(partyId || '').trim();
    const bareId = cleanId.replace(/^(cust|supp|bank|safe|exp|sub|sust)-/i, '').trim();
    const cleanName = String(partyName || '').trim();

    let masterRec = null;
    let entityType = 'account';
    let nativeCurrency = 'SDG';
    const activeModalCurrency = window._waCurrentModalCurrency;
    const voucherCurrency = document.getElementById('currency')?.value || 'SDG';
    const targetCurrency = forcedCurrency || activeModalCurrency || voucherCurrency;

    // 1. العملاء
    try {
        const custs = (JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1')) || localStorage.getItem('erp_customers_v1')) || []).filter(x => x && !x.deleted);
        masterRec = custs.find(c => 
            (cleanId && (String(c.id) === cleanId || String(c.id) === bareId)) ||
            (cleanName && c.name && c.name.trim() === cleanName)
        );
        if (masterRec) entityType = 'customer';
    } catch(e) {}

    // 2. الموردين
    if (!masterRec) {
        try {
            const sups = (JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1')) || []).filter(x => x && !x.deleted);
            masterRec = sups.find(s => 
                (cleanId && (String(s.id) === cleanId || String(s.id) === bareId)) ||
                (cleanName && s.name && s.name.trim() === cleanName)
            );
            if (masterRec) entityType = 'supplier';
        } catch(e) {}
    }

    // 3. البنوك
    if (!masterRec) {
        try {
            const banks = (JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1')) || []).filter(x => x && !x.deleted);
            masterRec = banks.find(b => 
                (cleanId && (String(b.id) === cleanId || String(b.id) === bareId)) ||
                (cleanName && b.name && b.name.trim() === cleanName)
            );
            if (masterRec) entityType = 'bank';
        } catch(e) {}
    }

    // 4. الخزن
    if (!masterRec) {
        try {
            const safes = (JSON.parse(localStorage.getItem(getTenantKey('erp_safes_v1')) || localStorage.getItem('erp_safes_v1')) || []).filter(x => x && !x.deleted);
            masterRec = safes.find(s => 
                (cleanId && (String(s.id) === cleanId || String(s.id) === bareId)) ||
                (cleanName && s.name && s.name.trim() === cleanName)
            );
            if (masterRec) entityType = 'safe';
        } catch(e) {}
    }

    // 5. شجرة الحسابات العامة
    if (!masterRec) {
        try {
            const accounts = (JSON.parse(localStorage.getItem(getTenantKey('erp_accounts_v1')) || localStorage.getItem('erp_accounts_v1')) || []).filter(x => x && !x.deleted);
            masterRec = accounts.find(a => 
                (cleanId && (String(a.id) === cleanId || String(a.id) === bareId || String(a.code) === cleanId || String(a.code) === bareId)) ||
                (cleanName && a.name && a.name.trim() === cleanName)
            );
            if (masterRec) entityType = 'account';
        } catch(e) {}
    }

    if (masterRec && masterRec.currency) {
        nativeCurrency = masterRec.currency;
    } else {
        nativeCurrency = voucherCurrency;
    }

    let intrinsicBal = 0;
    if (masterRec) {
        const opDebit = parseFloat(masterRec.openingDebit || masterRec.debit || 0) || 0;
        const opCredit = parseFloat(masterRec.openingCredit || masterRec.credit || 0) || 0;
        const rawBal = parseFloat(masterRec.openingBalance || masterRec.opening_balance || masterRec.balance || 0) || 0;
        if (opDebit > 0 || opCredit > 0) {
            intrinsicBal = opDebit - opCredit;
        } else if (rawBal !== 0) {
            if (masterRec.balance_type === 'credit' || masterRec.balanceType === 'credit') {
                intrinsicBal = -Math.abs(rawBal);
            } else {
                intrinsicBal = Math.abs(rawBal);
            }
        }
    }

    let glTxns = [];
    try {
        glTxns = JSON.parse(localStorage.getItem(getTenantKey('erp_gl_v1')) || localStorage.getItem('erp_gl_v1') || '[]');
    } catch(e) { glTxns = []; }
    if (!Array.isArray(glTxns)) glTxns = Object.values(glTxns || {});

    let runningBal = intrinsicBal;
    let hasGLTxns = false;
    let isVoucherAlreadyInGL = false;
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || '').trim();

    if (glTxns.length > 0) {
        const pRefPrefixed = (entityType === 'customer' ? 'cust-' : entityType === 'supplier' ? 'supp-' : entityType === 'bank' ? 'bank-' : entityType === 'safe' ? 'safe-' : 'exp-') + bareId;
        const normName = cleanName ? cleanName.replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').replace(/\s+/g, ' ').toLowerCase() : '';

        glTxns.forEach(t => {
            if (!t) return;
            const tAcc = String(t.account || '').trim();
            const cleanTAcc = tAcc.replace(/^(cust|supp|bank|safe|exp|sub|sust)-/i, '').trim();
            let matches = false;

            if (cleanId && (tAcc === cleanId || cleanTAcc === cleanId)) matches = true;
            else if (bareId && (cleanTAcc === bareId || tAcc === pRefPrefixed)) matches = true;
            else if (cleanName && (tAcc === cleanName || t.accountName === cleanName || t.partner === cleanName)) matches = true;
            else if (normName) {
                const normT = tAcc.replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').replace(/\s+/g, ' ').toLowerCase();
                const normP = String(t.partner || t.accountName || '').replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي').replace(/\s+/g, ' ').toLowerCase();
                if (normT === normName || normP === normName) matches = true;
            }

            if (matches) {
                hasGLTxns = true;
                const deb = parseFloat(t.debit || t.dr || 0) || 0;
                const cred = parseFloat(t.credit || t.cr || 0) || 0;
                runningBal += (deb - cred);

                if (vNo && (t.ref === vNo || t.voucherNo === vNo || t.id === vNo)) {
                    isVoucherAlreadyInGL = true;
                }
            }
        });
    }

    if (!hasGLTxns && masterRec && masterRec.currentBalance !== undefined && masterRec.currentBalance !== null) {
        const cb = parseFloat(masterRec.currentBalance) || 0;
        if (entityType === 'supplier') {
            runningBal = -cb;
        } else {
            runningBal = cb;
        }
    }

    let finalBal = runningBal;
    if (nativeCurrency !== targetCurrency) {
        const rateToSDG_Native = (nativeCurrency === 'SDG') ? 1 : (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(nativeCurrency) : (nativeCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1));
        const rateToSDG_Target = (targetCurrency === 'SDG') ? 1 : (typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(targetCurrency) : (targetCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1));

        const balInSDG = runningBal * (rateToSDG_Native > 0 ? rateToSDG_Native : 1);
        finalBal = (rateToSDG_Target > 0) ? (balInSDG / rateToSDG_Target) : balInSDG;
    }

    if (!isVoucherAlreadyInGL && voucherAmt > 0) {
        const isTargetDebtor = (targetRole === 'debtor');
        const effect = isTargetDebtor ? voucherAmt : -voucherAmt;
        finalBal += effect;
    }

    const absBal = Math.abs(finalBal);
    let status = 'balanced';
    let statusText = 'متوازن';
    let balanceDescription = '';
    let shortTag = '';

    if (absBal < 0.01) {
        status = 'balanced';
        statusText = 'متوازن';
        balanceDescription = `*0.00* ${targetCurrency} (متوازن ✅)`;
        shortTag = 'متوازن';
    } else if (finalBal > 0.01) {
        status = 'debit';
        statusText = 'عليه';
        balanceDescription = `*${window.fmtNum(absBal)}* ${targetCurrency} (عليه 🔴)`;
        shortTag = 'عليه';
    } else {
        status = 'credit';
        statusText = 'له';
        balanceDescription = `*${window.fmtNum(absBal)}* ${targetCurrency} (له 🟢)`;
        shortTag = 'له';
    }

    let foreignCurrency = 'USD';
    if (targetCurrency === 'USD') {
        foreignCurrency = 'SDG';
    } else if (nativeCurrency && nativeCurrency !== targetCurrency && nativeCurrency !== 'SDG') {
        foreignCurrency = nativeCurrency;
    }

    const rateToSDG_TargetForForeign = (targetCurrency === 'SDG') ? 1 : ((typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(targetCurrency) : (targetCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1)) || 1);
    const rateToSDG_Foreign = (foreignCurrency === 'SDG') ? 1 : ((typeof window.getExchangeRateForCurrency === 'function' ? window.getExchangeRateForCurrency(foreignCurrency) : (foreignCurrency === 'USD' ? (window.getSystemUsdRate ? window.getSystemUsdRate() : 2600) : 1)) || 2600);

    const balInSDG_Foreign = finalBal * (rateToSDG_TargetForForeign > 0 ? rateToSDG_TargetForForeign : 1);
    const rawForeignBal = (rateToSDG_Foreign > 0) ? (balInSDG_Foreign / rateToSDG_Foreign) : balInSDG_Foreign;
    const absForeignBal = Math.abs(rawForeignBal);

    let foreignBalanceDescription = '';
    if (absForeignBal < 0.01) {
        foreignBalanceDescription = `*0.00* ${foreignCurrency} (متوازن ✅)`;
    } else if (finalBal > 0.01) {
        foreignBalanceDescription = `*${window.fmtNum(absForeignBal)}* ${foreignCurrency} (عليه 🔴)`;
    } else {
        foreignBalanceDescription = `*${window.fmtNum(absForeignBal)}* ${foreignCurrency} (له 🟢)`;
    }

    return {
        amount: absBal,
        rawBalance: finalBal,
        currency: targetCurrency,
        nativeCurrency: nativeCurrency,
        foreignAmount: absForeignBal,
        foreignCurrency: foreignCurrency,
        foreignBalanceDescription: foreignBalanceDescription,
        entityType,
        status,
        statusText,
        balanceDescription,
        shortTag,
        partyName: cleanName
    };
};

// ═══ استخراج أطراف السند وبيانات الدفع لسند الصرف المفرد ═══
window.getVoucherParties = function(blockId) {
    const isPayment = true;

    // Creditor: From Account (حساب الصرف / الدافع)
    const fromSearch = document.getElementById('from-account-search');
    const fromIdEl = document.getElementById('from-account');
    const fromName = (fromSearch ? fromSearch.value.trim() : '') || 'حساب الصرف';
    const fromId = fromIdEl ? fromIdEl.value.trim() : '';
    const fromContact = window.getVoucherContactDetails(fromId, fromName);
    let fromPhone = fromContact ? (fromContact.phone || fromContact.whatsapp || '') : '';
    if (!fromPhone && fromId) {
        try { fromPhone = localStorage.getItem(`erp_wa_phone_${fromId}`) || ''; } catch(e) {}
    }
    if (!fromPhone && fromName) {
        try { fromPhone = localStorage.getItem(`erp_wa_phone_${fromName}`) || ''; } catch(e) {}
    }

    // Debtor: To Account (المستفيد / قيد وصرف لحساب)
    const toSearch = document.getElementById('to-account-search');
    const toIdEl = document.getElementById('to-account');
    const beneInput = document.getElementById('beneficiary-name');
    const toName = (toSearch ? toSearch.value.trim() : '') || (beneInput ? beneInput.value.trim() : '') || 'المستفيد';
    const toId = toIdEl ? toIdEl.value.trim() : '';
    const toContact = window.getVoucherContactDetails(toId, toName);
    let toPhone = toContact ? (toContact.phone || toContact.whatsapp || '') : '';
    if (!toPhone && toId) {
        try { toPhone = localStorage.getItem(`erp_wa_phone_${toId}`) || ''; } catch(e) {}
    }
    if (!toPhone && toName) {
        try { toPhone = localStorage.getItem(`erp_wa_phone_${toName}`) || ''; } catch(e) {}
    }

    // Amount & Currency
    const activeCurrency = window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG';
    const grandAmount = (typeof window.getVoucherAmountForCurrency === 'function')
        ? window.getVoucherAmountForCurrency(activeCurrency)
        : (typeof parseNum === 'function' ? parseNum(document.getElementById('amount-local')?.value) : window.cleanNum(document.getElementById('amount-local')?.value)) || 0;

    // Statements
    const toStmt = document.getElementById('to-statement')?.value?.trim() || '';
    const fromStmt = document.getElementById('from-statement')?.value?.trim() || '';
    const statement = toStmt || fromStmt || 'سداد مستحقات مالية معتمدة وفق القيود الحسابية';

    // Cost center
    const ccEl = document.getElementById('cost-center');
    const ccName = ccEl ? (ccEl.options ? ccEl.options[ccEl.selectedIndex]?.text : ccEl.value) : '';

    // Structured Payments
    const pmDetails = [];
    const structuredPayments = [];
    const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';

    if (method === 'mobile' || method === 'hybrid') {
        const transferRows = document.querySelectorAll('.transfer-row');
        transferRows.forEach(row => {
            const trAmt = (typeof parseNum === 'function' ? parseNum(row.querySelector('.transfer-amount')?.value) : window.cleanNum(row.querySelector('.transfer-amount')?.value)) || 0;
            const trNo = row.querySelector('.transfer-no')?.value?.trim() || '';
            const trNote = row.querySelector('.transfer-note')?.value?.trim() || '';
            const trStmt = row.querySelector('.transfer-statement')?.value?.trim() || '';
            if (trAmt > 0 || trNo) {
                pmDetails.push(`🏦 تحويل بنكي: ${window.fmtNum(trAmt)} (${trNote || 'بنك'}) | رقم التحويلة: ${trNo || 'غير محدد'}`);
                structuredPayments.push({
                    type: 'bank',
                    amount: trAmt,
                    bankName: trNote || 'تحويل بنكي',
                    transferNo: trNo,
                    desc: trStmt
                });
            }
        });
    }

    if (method === 'check' || method === 'hybrid') {
        const chequeRows = document.querySelectorAll('.cheque-row');
        chequeRows.forEach(row => {
            const chAmt = (typeof parseNum === 'function' ? parseNum(row.querySelector('.cheque-amount')?.value) : window.cleanNum(row.querySelector('.cheque-amount')?.value)) || 0;
            const chNo = row.querySelector('.cheque-no')?.value?.trim() || '';
            const chBranch = row.querySelector('.cheque-branch')?.value?.trim() || '';
            const chDate = row.querySelector('.cheque-date')?.value?.trim() || '';
            const chStmt = row.querySelector('.cheque-statement')?.value?.trim() || '';
            if (chAmt > 0 || chNo) {
                pmDetails.push(`✍️ شيك: ${window.fmtNum(chAmt)} (${chBranch || 'بنك'}) | رقم الشيك: ${chNo || ''} ${chDate ? `(استحقاق: ${chDate})` : ''}`);
                structuredPayments.push({
                    type: 'cheque',
                    amount: chAmt,
                    bankName: chBranch || 'شيك مصرفي',
                    chequeNo: chNo,
                    dueDate: chDate,
                    desc: chStmt
                });
            }
        });
    }

    if (method === 'cash' || structuredPayments.length === 0) {
        pmDetails.push(`💵 نقداً: ${window.fmtNum(grandAmount)} (${fromName || 'الخزينة'})`);
        structuredPayments.push({
            type: 'cash',
            amount: grandAmount,
            safeName: fromName || 'الخزينة',
            desc: statement
        });
    }

    const debtorParty = {
        roleKey: 'debtor',
        roleTitle: 'إلى',
        name: toName,
        id: toId,
        phone: toPhone,
        isMain: false,
        blockId: null
    };

    const creditorParty = {
        roleKey: 'creditor',
        roleTitle: 'من',
        name: fromName,
        id: fromId,
        phone: fromPhone,
        isMain: true,
        blockId: null
    };

    return {
        debtor: debtorParty,
        creditor: creditorParty,
        amount: grandAmount,
        currency: activeCurrency,
        isPayment: true,
        blockId: null,
        statement,
        ccName: ccName && ccName !== 'اختر مركز التكلفة...' ? ccName : '',
        pmDetails,
        structuredPayments
    };
};

// ═══ توليد باركود التحقق الثنائي QR Code عبر خادم الواتساب المدمج ═══
window._waQrCache = window._waQrCache || {};
window.getVoucherQRCode = async function(text) {
    if (window._waQrCache[text]) return window._waQrCache[text];
    try {
        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=generate-qr&text=${encodeURIComponent(text)}`);
        const data = await res.json();
        if (data && data.success && data.dataUrl) {
            window._waQrCache[text] = data.dataUrl;
            return data.dataUrl;
        }
    } catch(e) {}
    const fallbackUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(text)}`;
    window._waQrCache[text] = fallbackUrl;
    return fallbackUrl;
};

// ═══ صياغة الكابشن المرفق بكمبيالة السند PDF ═══
window.buildWAPromissoryPDFCaption = function(targetType = 'debtor', parties) {
    if (!parties) parties = window.getVoucherParties();
    const isPayment = parties?.isPayment !== false;
    const vTitle = isPayment ? 'سند صرف مالي رسمي معتمد' : 'سند قبض مالي رسمي معتمد';
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = (document.getElementById('hdr-date')?.value || document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0]).trim();
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties?.debtor : parties?.creditor;
    const amount = parties?.amount || 0;
    const currency = parties?.currency || 'SDG';

    let caption = `🏛️ *${vTitle}* (كمبيالة مالية رسمية)\n`;
    caption += `📋 *رقم السند:* #${vNo}\n`;
    caption += `📅 *تاريخ التحرير:* ${vDate}\n`;
    caption += `👤 *المعني:* ${activeParty?.name || '—'}\n`;
    caption += `💰 *المبلغ الصافي:* *${window.fmtNum(amount)}* ${currency}\n`;
    if (parties?.statement) caption += `📝 *البيان:* ${parties.statement}\n`;
    caption += `📎 *مرفق أعلاه:* ورقة كمبيالة السند المعتمدة رسمياً والمقيدة إلكترونياً بنظام الحسابات ELSAJANA ERP (ملف PDF عالي الجودة) 📄✅`;
    return caption;
};

// ═══ صياغة وتصميم ورقة كمبيالة السند المعتمدة بتصاميم وألوان وباركود احترافي (HTML Promissory Note) ═══
window.generatePromissoryNoteHTML = function(targetType = 'debtor', parties, qrDataUrl = '') {
    if (!parties) parties = window.getVoucherParties();
    const isPayment = parties.isPayment !== false;
    const vTitle = isPayment ? 'سند صرف مالي رسمي معتمد' : 'سند قبض مالي رسمي معتمد';
    const vSubTitle = isPayment ? 'كمبيالة صرف رسمية • إشعار خصم وقيد مالي' : 'كمبيالة قبض رسمية • إشعار إضافة وقيد مالي';
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'E26090001').trim();
    const vDate = (document.getElementById('hdr-date')?.value || document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0]).trim();
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const tafqeetText = typeof window.formatArabicTafqeet === 'function' ? window.formatArabicTafqeet(amount, currency) : '';

    let companyName = 'الإدارة المالية';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const otherParty = isDebtor ? parties.creditor : parties.debtor;
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment);
    const isCredit = balInfo.status === 'credit';

    let acknowledgmentText = '';
    if (isPayment) {
        if (isDebtor) {
            acknowledgmentText = 'أقر وأعترف بأننا قيدنا وصرفنا لحساب المكرم';
        } else {
            acknowledgmentText = 'نحيطكم علماً بأنه تم قيد وخصم السند من حسابكم لصالح المكرم';
        }
    } else {
        if (isDebtor) {
            acknowledgmentText = 'أقر وأعترف بأننا استلمنا وقيدنا لحسابكم';
        } else {
            acknowledgmentText = 'وصلنا واستلمنا من المكرم';
        }
    }

    const barcodeSvg = typeof window.generateBarcodeSVG === 'function' ? window.generateBarcodeSVG(vNo) : '';
    const qrPlaceholder = qrDataUrl || `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=VERIFIED_${vNo}`;

    // بيانات السداد
    let paymentMethodText = 'نقداً عبر الخزينة';
    let transferNoText = '—';
    let bankNameText = '';
    if (parties.structuredPayments && parties.structuredPayments.length > 0) {
        const p = parties.structuredPayments[0];
        if (p.type === 'bank') {
            paymentMethodText = 'تحويل مصرفي (Bank Transfer)';
            bankNameText = p.bankName || 'تحويل مصرفي';
            transferNoText = p.transferNo || '—';
        } else if (p.type === 'cheque') {
            paymentMethodText = 'شيك مصرفي (Bank Cheque)';
            bankNameText = p.bankName || 'شيك';
            transferNoText = p.chequeNo || '—';
        } else if (p.type === 'cash') {
            paymentMethodText = 'نقداً (Cash)';
            bankNameText = p.safeName || 'الخزينة';
            transferNoText = 'نقدي مباشر';
        }
    }

    return `
    <div class="promissory-note-card" style="background:#ffffff; color:#0f172a; font-family:'Tajawal', 'Segoe UI', Arial, sans-serif; direction:rtl; border:3.5px double #d97706; outline:2px solid #1e293b; border-radius:10px; padding:18px 22px; position:relative; box-sizing:border-box; width:100%; min-height:980px; height:990px; display:flex; flex-direction:column; justify-content:space-between; margin:0 auto; box-shadow:0 8px 25px rgba(0,0,0,0.12);">
        <!-- Watermark -->
        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; opacity:0.035; pointer-events:none; z-index:0; user-select:none;">
            <svg width="380" height="380" viewBox="0 0 24 24" fill="#0f172a"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
        </div>

        <!-- Top Header & Details Section -->
        <div style="position:relative; z-index:1;">
            <!-- 1. Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2.5px solid #b45309; padding-bottom:10px; margin-bottom:10px; gap:10px;">
                <!-- Right -->
                <div style="text-align:right; flex:1.2;">
                    <div style="font-size:1.30rem; font-weight:900; color:#0f172a; line-height:1.2;">${companyName}</div>
                    <div style="font-size:0.82rem; color:#b45309; font-weight:800; margin-top:2px;">الإدارة المالية والمصرفية • الحسابات المركزية</div>
                    <div style="font-size:0.78rem; color:#64748b; margin-top:3px;">
                        <span>تاريخ التحرير:&nbsp;</span><b dir="ltr">${vDate}</b>
                    </div>
                </div>

                <!-- Center -->
                <div style="text-align:center; flex:1.4;">
                    <div style="display:block; margin:0 auto; max-width:340px; background:linear-gradient(135deg, #1e293b, #0f172a); border:2px solid #d97706; border-radius:8px; padding:6px 14px; box-shadow:0 3px 10px rgba(0,0,0,0.15);">
                        <div style="color:#fbbf24; font-size:1.10rem; font-weight:900;">🏛️ ${vTitle}</div>
                        <div style="color:#cbd5e1; font-size:0.74rem; font-weight:800; margin-top:2px;">${vSubTitle}</div>
                    </div>
                    <div style="font-size:0.72rem; color:#059669; font-weight:900; margin-top:3px;">سند مالي معتمد وموثق رقمياً وفق المعايير المحاسبية</div>
                </div>

                <!-- Left -->
                <div style="text-align:left; flex:1.2; display:flex; flex-direction:column; align-items:flex-end;">
                    <div style="display:flex; gap:8px; align-items:center;">
                        <div style="text-align:right;">
                            <div style="font-size:0.70rem; color:#64748b; font-weight:800;">رقم السند المالي</div>
                            <div style="font-family:monospace; font-size:1.15rem; font-weight:900; color:#b45309; direction:ltr;">#${vNo}</div>
                            <div style="margin-top:2px;">${barcodeSvg}</div>
                        </div>
                        <div style="width:58px; height:58px; border:1.5px solid #cbd5e1; border-radius:6px; padding:2px; background:#fff; flex-shrink:0;">
                            <img id="wa-promissory-qr-img" src="${qrPlaceholder}" style="width:100%; height:100%; object-fit:contain;" alt="QR" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Two Parties Grid (من حساب / إلى حساب) -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                <!-- Party 1: الصارف (من حساب) -->
                <div style="background:linear-gradient(135deg, #fef2f2 0%, #fff 100%); border:1.5px solid #fca5a5; border-radius:8px; padding:8px 12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.82rem; font-weight:900; color:#991b1b;">📤 صُرف من حساب (طرف الصرف):</span>
                        ${!isDebtor ? '<span style="background:#ef4444; color:#fff; font-size:0.70rem; font-weight:900; padding:1px 7px; border-radius:8px;">نسخة الصارف المعتمدة</span>' : ''}
                    </div>
                    <div style="font-size:1.10rem; font-weight:900; color:#0f172a; margin-top:3px;">
                        ${parties.creditor?.name || 'حساب الصرف / الخزينة'}
                    </div>
                    ${parties.creditor?.phone ? `<div style="font-size:0.78rem; color:#dc2626; font-weight:800; font-family:monospace; direction:ltr; margin-top:2px;">📱 ${parties.creditor.phone}</div>` : ''}
                </div>

                <!-- Party 2: المستفيد / المستلم (إلى حساب) -->
                <div style="background:linear-gradient(135deg, #ecfdf5 0%, #fff 100%); border:1.5px solid #10b981; border-radius:8px; padding:8px 12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.82rem; font-weight:900; color:#065f46;">📥 قُيد وصُرف لحساب (المستفيد / المستلم):</span>
                        ${isDebtor ? '<span style="background:#10b981; color:#fff; font-size:0.70rem; font-weight:900; padding:1px 7px; border-radius:8px;">نسخة المستلم المعتمدة</span>' : ''}
                    </div>
                    <div style="font-size:1.10rem; font-weight:900; color:#0f172a; margin-top:3px;">
                        ${parties.debtor?.name || 'المستفيد'}
                    </div>
                    ${parties.debtor?.phone ? `<div style="font-size:0.78rem; color:#059669; font-weight:800; font-family:monospace; direction:ltr; margin-top:2px;">📱 ${parties.debtor.phone}</div>` : ''}
                </div>
            </div>

            <!-- 3. Legal Promissory Clause -->
            <div style="background:#fffbeb; border:1.5px solid #f59e0b; border-radius:8px; padding:8px 14px; margin-bottom:10px;">
                <div style="font-size:0.90rem; color:#78350f; font-weight:900; line-height:1.5;">
                    📜 <b>صيغة الإقرار المالي:&nbsp;</b>${acknowledgmentText} <b>(${activeParty?.name || 'المعني'})</b> مبلغاً وقدره الصافي المبيّن أدناه، وذلك بموجب القيود الحسابية الرسمية المعتمدة بنظام الحسابات.
                </div>
            </div>

            <!-- 4. Grand Financial Amount Box -->
            <div style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border:2px solid #d97706; border-radius:9px; padding:12px 18px; margin-bottom:10px; color:#ffffff; box-shadow:0 4px 14px rgba(15,23,42,0.12);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <div>
                        <div style="color:#fbbf24; font-size:0.86rem; font-weight:800;">💰 المبلغ المعتمد والمنصرف:</div>
                        <div style="font-size:2.05rem; font-weight:900; color:#34d399; font-family:monospace; letter-spacing:1px; margin-top:2px;" dir="ltr">
                            ${window.fmtNum(amount)} <span style="font-size:1.20rem; color:#fde047;">${currency}</span>
                        </div>
                    </div>
                    <div style="text-align:right; flex:1; max-width:460px;">
                        <div style="color:#94a3b8; font-size:0.80rem; font-weight:800;">✍️ وقدره كتابةً بالحروف (تفقيط معتمد):</div>
                        <div style="color:#fef08a; font-size:0.96rem; font-weight:900; line-height:1.45; margin-top:3px; background:rgba(255,255,255,0.08); padding:6px 10px; border-radius:6px; border:1px dashed rgba(251,191,36,0.5);">
                            فقط ${tafqeetText || '—'}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Statement & Purpose -->
            <div style="background:#f8fafc; border:1.5px solid #cbd5e1; border-radius:8px; padding:8px 12px; margin-bottom:10px;">
                <div style="display:flex; align-items:baseline; gap:8px;">
                    <span style="color:#334155; font-size:0.86rem; font-weight:900; white-space:nowrap;">📝 البيان والسبب:&nbsp;</span>
                    <span style="color:#0f172a; font-size:0.92rem; font-weight:900; flex:1; line-height:1.45;">${parties.statement || 'سداد مستحقات مالية معتمدة وفق القيود الحسابية'}</span>
                </div>
                ${parties.ccName ? `<div style="margin-top:4px; padding-top:4px; border-top:1px dashed #cbd5e1; font-size:0.80rem; color:#0369a1; font-weight:900;"><span>🏷️ المشروع أو مركز التكلفة:&nbsp;</span><b style="color:#0f172a;">${parties.ccName}</b></div>` : ''}
            </div>

            <!-- 6. Banking Settlement & Transfer Details -->
            <div style="border:1.5px solid #0d9488; border-radius:8px; overflow:hidden; margin-bottom:10px;">
                <div style="background:linear-gradient(90deg, #0f766e, #115e59); color:#ffffff; padding:5px 12px; font-size:0.85rem; font-weight:900; display:flex; align-items:center; justify-content:space-between;">
                    <span>💳 بيانات السداد والتحويل المالي المعتمدة:</span>
                    <span style="font-size:0.75rem; color:#a7f3d0; background:rgba(0,0,0,0.2); padding:2px 7px; border-radius:4px;">قيد مالي موثق ✅</span>
                </div>
                <div style="background:#f0fdfa; padding:8px 12px;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                        <tbody>
                            <tr>
                                <td style="padding:3px 6px; color:#475569; font-weight:800; width:18%;">طريقة السداد:</td>
                                <td style="padding:3px 6px; color:#0f172a; font-weight:900; width:32%;">${paymentMethodText}</td>
                                <td style="padding:3px 6px; color:#475569; font-weight:800; width:22%;">رقم التحويلة أو الإشعار:</td>
                                <td style="padding:3px 6px; color:#0f766e; font-weight:900; font-family:monospace; font-size:1.0rem; width:28%;" dir="ltr">${transferNoText}</td>
                            </tr>
                            ${bankNameText ? `
                            <tr>
                                <td style="padding:3px 6px; color:#475569; font-weight:800;">البنك أو الخزينة:</td>
                                <td style="padding:3px 6px; color:#0f172a; font-weight:900;">${bankNameText}</td>
                                <td style="padding:3px 6px; color:#475569; font-weight:800;">المبلغ المسدد:</td>
                                <td style="padding:3px 6px; color:#059669; font-weight:900; font-family:monospace;" dir="ltr">${window.fmtNum(amount)} ${currency}</td>
                            </tr>` : ''}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 7. General Ledger Account Balance -->
            <div style="background:#f8fafc; border:1.5px solid ${isCredit ? '#10b981' : '#ef4444'}; border-radius:8px; padding:8px 14px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div>
                    <div style="font-size:0.85rem; color:#334155; font-weight:900;">📊 الموقف المالي الختامي لحساب (${activeParty?.name || 'المعني'}):</div>
                    <div style="font-size:0.74rem; color:#64748b; margin-top:2px;">رصيد الحساب المالي في دفتر الأستاذ العام بعد قيد هذا السند مباشرة</div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="font-size:1.20rem; font-weight:900; color:#0f172a; font-family:monospace; background:#ffffff; padding:3px 10px; border-radius:6px; border:1.5px solid #cbd5e1;" dir="ltr">
                        ${window.fmtNum(balInfo.amount)} <span style="font-size:0.90rem; color:#64748b;">${balInfo.currency}</span>
                    </div>
                    <div style="background:${isCredit ? '#10b981' : '#ef4444'}; color:#ffffff; padding:4px 10px; border-radius:6px; font-weight:900; font-size:0.86rem;">
                        ${balInfo.shortTag} • ${isCredit ? 'رصيد دائن لصالحكم' : 'رصيد مدين طرفكم'}
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Signatures, Stamp & Security Stripe -->
        <div style="position:relative; z-index:1; margin-top:auto;">
            <!-- 8. Signatures & Official Stamp -->
            <div style="display:flex; justify-content:space-between; align-items:flex-end; padding-top:10px; border-top:2px solid #cbd5e1;">
                <!-- Accountant -->
                <div style="text-align:center; flex:1;">
                    <div style="font-size:0.82rem; color:#475569; font-weight:900;">المحاسب المالي (معد السند)</div>
                    <div style="margin:6px auto 3px auto; width:80px; height:1.5px; background:#94a3b8;"></div>
                    <div style="font-size:0.75rem; color:#059669; font-weight:900;">قيد آلي نظامي</div>
                </div>

                <!-- Official Stamp -->
                <div style="text-align:center; flex:1.2;">
                    <div style="display:inline-block; width:80px; height:80px; border:2.5px solid #b45309; border-radius:50%; padding:2px; background:rgba(254,243,199,0.3); transform:rotate(-5deg); box-shadow:0 0 0 2px #fff, 0 0 0 4px #b45309;">
                        <div style="width:100%; height:100%; border:1px dashed #b45309; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;">
                            <div style="font-size:0.60rem; color:#b45309; font-weight:900;">🏛️ الإدارة المالية</div>
                            <div style="font-size:0.70rem; color:#78350f; font-weight:900; margin:1px 0;">معتمد ومقيد</div>
                            <div style="font-size:0.60rem; color:#059669; font-weight:900;">إلكترونياً ✅</div>
                            <div style="font-size:0.54rem; color:#64748b; margin-top:1px;">${vDate}</div>
                        </div>
                    </div>
                </div>

                <!-- Controller -->
                <div style="text-align:center; flex:1;">
                    <div style="font-size:0.82rem; color:#475569; font-weight:900;">المدير المالي / الاعتماد</div>
                    <div style="margin:6px auto 3px auto; width:80px; height:1.5px; background:#94a3b8;"></div>
                    <div style="font-size:0.75rem; color:#0f172a; font-weight:900;">إجازة وصرف معتمد</div>
                </div>

                <!-- Recipient -->
                <div style="text-align:center; flex:1;">
                    <div style="font-size:0.82rem; color:#475569; font-weight:900;">المستلم / صاحب الحساب</div>
                    <div style="margin:6px auto 3px auto; width:80px; height:1.5px; background:#94a3b8;"></div>
                    <div style="font-size:0.75rem; color:#0369a1; font-weight:900;">${activeParty?.name || '—'}</div>
                </div>
            </div>

            <!-- 9. Document Bottom Security Stripe -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px dashed #cbd5e1; padding-top:6px; margin-top:8px; font-size:0.72rem; color:#64748b; font-weight:800;">
                <div>VERIFICATION: <span style="font-family:monospace; color:#0f172a; font-weight:900;">#DOC-${vNo}-${vDate.replace(/-/g, '')}</span></div>
                <div>وثيقة مالية معتمدة ومقيدة بالنظام المحاسبي العام ELSAJANA ERP 🔒</div>
            </div>
        </div>
    </div>
    `;
};

// ═══ عرض كمبيالة السند المعتمدة في نافذة المعاينة الحية ═══
window.renderPromissoryNotePreview = function(targetType = 'debtor') {
    const container = document.getElementById('wa-promissory-note-container');
    if (!container) return;

    const parties = window.getVoucherParties();
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'E26090001').trim();
    const vDate = (document.getElementById('hdr-date')?.value || document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0]).trim();

    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;

    const qrText = `VERIFIED_VOUCHER|NO:${vNo}|DATE:${vDate}|AMT:${parties.amount}|PARTY:${activeParty.name}`;

    container.innerHTML = window.generatePromissoryNoteHTML(targetType, parties, window._waQrCache[qrText] || '');

    window.getVoucherQRCode(qrText).then(dataUrl => {
        const qrImg = document.getElementById('wa-promissory-qr-img');
        if (qrImg) qrImg.src = dataUrl;
    });
};

// ═══ توليد ملف PDF عالي الجودة للكمبيالة عبر html2pdf ═══
window.generatePromissoryNotePDFBlob = async function(targetType = 'debtor', parties) {
    if (!parties) parties = window.getVoucherParties();
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'E26090001').trim();
    const vDate = (document.getElementById('hdr-date')?.value || document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0]).trim();
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;

    const qrText = `VERIFIED_VOUCHER|NO:${vNo}|DATE:${vDate}|AMT:${parties.amount}|PARTY:${activeParty.name}`;
    const qrDataUrl = await window.getVoucherQRCode(qrText);

    if (typeof html2pdf === 'undefined') {
        await new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = 'js/html2pdf.bundle.min.js';
            s.onload = resolve;
            s.onerror = () => reject(new Error('مكتبة html2pdf غير متوفرة'));
            document.head.appendChild(s);
        });
    }

    const renderWrapper = document.createElement('div');
    renderWrapper.style.position = 'fixed';
    renderWrapper.style.top = '0';
    renderWrapper.style.left = '0';
    renderWrapper.style.width = '780px';
    renderWrapper.style.zIndex = '-99999';
    renderWrapper.style.overflow = 'visible';
    renderWrapper.style.pointerEvents = 'none';

    const innerDiv = document.createElement('div');
    innerDiv.style.width = '780px';
    innerDiv.style.background = '#ffffff';
    innerDiv.style.margin = '0';
    innerDiv.style.padding = '0';
    innerDiv.innerHTML = window.generatePromissoryNoteHTML(targetType, parties, qrDataUrl);

    renderWrapper.appendChild(innerDiv);
    document.body.appendChild(renderWrapper);

    try {
        await new Promise(r => setTimeout(r, 60));
        const opt = {
            margin: [5, 5, 5, 5],
            filename: `كمبيالة_سند_${vNo}_${activeParty.name}.pdf`,
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
        const pdfBlob = await html2pdf().set(opt).from(innerDiv).output('blob');
        return pdfBlob;
    } finally {
        if (renderWrapper.parentNode) {
            renderWrapper.parentNode.removeChild(renderWrapper);
        }
    }
};

// ═══ تنزيل ملف كمبيالة السند PDF ═══
window.downloadPromissoryPDF = async function(targetType) {
    if (!targetType) {
        targetType = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    }
    const parties = window.getVoucherParties();
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'E26090001').trim();
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;

    showToast('⏳ جاري تجهيز وتنزيل ملف PDF المعتمد...', 'info', 2000);
    try {
        const blob = await window.generatePromissoryNotePDFBlob(targetType, parties);
        if (blob) {
            const safeName = (activeParty.name || targetType).replace(/[/\\?%*:|"<>]/g, '_');
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
            a.click();
            setTimeout(() => URL.revokeObjectURL(url), 5000);
            showToast('📥 تم تنزيل كمبيالة السند المعتمدة بنجاح!', 'success');
        }
    } catch(err) {
        showToast('تعذر تنزيل ملف PDF: ' + err.message, 'error');
    }
};

// ═══ طباعة كمبيالة السند المعتمدة ═══
window.printPromissoryNote = function(targetType) {
    if (!targetType) {
        targetType = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    }
    const container = document.getElementById('wa-promissory-note-container');
    if (!container) return;

    const printWin = window.open('', '_blank', 'width=900,height=700');
    if (!printWin) {
        window.print();
        return;
    }
    printWin.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="utf-8">
            <title>طباعة كمبيالة السند المعتمد</title>
            <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;900&display=swap" rel="stylesheet">
            <style>
                body { margin: 20px; font-family: 'Tajawal', sans-serif; background: #fff; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            ${container.innerHTML}
            <script>
                window.onload = function() {
                    window.focus();
                    window.print();
                    setTimeout(function() { window.close(); }, 1000);
                };
            <\/script>
        </body>
        </html>
    `);
    printWin.document.close();
};

// ═══ تبديل وضع العرض بين كمبيالة PDF والنص ═══
window.toggleWAPreviewMode = function() {
    const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
    const bothBox = document.getElementById('wa-preview-both-box');
    const singleBox = document.getElementById('wa-preview-single-box');
    const toggleBtn = document.getElementById('btn-wa-toggle-preview-mode');
    const isBoth = (document.getElementById('wa-modal-target-mode')?.value || 'both') === 'both';

    if (promissoryWrapper && (promissoryWrapper.style.display !== 'none')) {
        promissoryWrapper.style.display = 'none';
        if (isBoth) {
            if (bothBox) bothBox.style.display = 'flex';
        } else {
            if (singleBox) singleBox.style.display = 'flex';
        }
        if (toggleBtn) toggleBtn.innerHTML = '📄 عرض كمبيالة PDF';
        showToast('📝 تم التبديل إلى محاذاة النص', 'info');
    } else {
        if (bothBox) bothBox.style.display = 'none';
        if (singleBox) singleBox.style.display = 'none';
        if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
        const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
        window.renderPromissoryNotePreview(activeTab);
        if (toggleBtn) toggleBtn.innerHTML = '📝 عرض النص';
        showToast('📄 تم التبديل إلى كمبيالة السند المعتمدة', 'info');
    }
};

// ═══ إدارة نموذج الإشعار المعتمد ═══
window.setWAFormatType = function(formatType = 'promissory_note') {
    const fmtInput = document.getElementById('wa-modal-format-type');
    if (fmtInput) fmtInput.value = 'promissory_note';

    const parties = window.getVoucherParties();
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';

    const msgInput = document.getElementById('wa-modal-message');
    if (msgInput) {
        const msg = window.buildWATailoredMessage(targetMode === 'both' ? 'debtor' : targetMode, parties, 'promissory_note');
        msgInput.value = msg;
        msgInput.dataset.defaultText = msg;
    }

    window._waBothPartyMessages = {
        debtor: window.buildWATailoredMessage('debtor', parties, 'promissory_note'),
        creditor: window.buildWATailoredMessage('creditor', parties, 'promissory_note')
    };

    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    const bothPreview = document.getElementById('wa-both-preview-message');
    if (bothPreview) {
        bothPreview.value = window._waBothPartyMessages[activeTab] || '';
    }

    window.renderPromissoryNotePreview(activeTab);
};

// ═══ التبديل بين تبويب المستلم والصارف ═══
window._waBothPartyMessages = { debtor: '', creditor: '' };

window.switchWABothPreviewTab = function(tab = 'debtor') {
    const hiddenTab = document.getElementById('wa-both-active-tab');
    if (hiddenTab) hiddenTab.value = tab;

    const btnDebtor = document.getElementById('btn-wa-both-tab-debtor');
    const btnCreditor = document.getElementById('btn-wa-both-tab-creditor');
    const textarea = document.getElementById('wa-both-preview-message');

    if (btnDebtor && btnCreditor) {
        if (tab === 'debtor') {
            btnDebtor.style.background = 'rgba(16, 185, 129, 0.25)';
            btnDebtor.style.borderColor = '#10b981';
            btnDebtor.style.color = '#34d399';

            btnCreditor.style.background = 'rgba(255, 255, 255, 0.06)';
            btnCreditor.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            btnCreditor.style.color = '#cbd5e1';
        } else {
            btnCreditor.style.background = 'rgba(239, 68, 68, 0.25)';
            btnCreditor.style.borderColor = '#ef4444';
            btnCreditor.style.color = '#fca5a5';

            btnDebtor.style.background = 'rgba(255, 255, 255, 0.06)';
            btnDebtor.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            btnDebtor.style.color = '#cbd5e1';
        }
    }

    if (textarea) {
        textarea.value = window._waBothPartyMessages[tab] || '';
    }

    window.renderPromissoryNotePreview(tab);
};

window.saveWABothPreviewEdit = function(text) {
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    window._waBothPartyMessages[activeTab] = text;
};

window.resetWABothPreviewText = function() {
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    const parties = window.getVoucherParties();
    const fmtType = document.getElementById('wa-modal-format-type')?.value || 'text_line';

    const newMsg = window.buildWATailoredMessage(activeTab, parties, fmtType);
    window._waBothPartyMessages[activeTab] = newMsg;

    const textarea = document.getElementById('wa-both-preview-message');
    if (textarea) textarea.value = newMsg;
    showToast('تمت استعادة القالب الأصلي لهذه الرسالة', 'info');
};

// ═══ صياغة رسائل الواتساب المعتمدة ═══
window.buildWATextLineMessage = function(targetType, parties) {
    if (!parties) parties = window.getVoucherParties();
    const isPayment = parties.isPayment;
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = (document.getElementById('hdr-date')?.value || document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0]).trim();
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const tafqeetText = typeof window.formatArabicTafqeet === 'function' ? window.formatArabicTafqeet(amount, currency) : '';

    let companyName = 'الإدارة المالية';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const otherParty = isDebtor ? parties.creditor : parties.debtor;
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment, currency);

    let msg = `السلام عليكم ورحمة الله وبركاته\n`;
    msg += `عزيزنا: *${activeParty.name}* المحترم\n`;

    if (isDebtor) {
        msg += `نحيطكم علماً بأنه تم اضافه لحسابكم بالتفاصيل التالية:\n`;
    } else {
        if (otherParty && otherParty.name && otherParty.name !== 'المستفيد' && otherParty.name !== 'حساب غير محدد') {
            msg += `نحيطكم علماً بأنه تم خصم من حسابكم لصالح (*${otherParty.name}*) بالتفاصيل التالية:\n`;
        } else {
            msg += `نحيطكم علماً بأنه تم خصم من حسابكم بالتفاصيل التالية:\n`;
        }
    }

    msg += `━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    const cleanVNo = vNo.startsWith('#') ? vNo : '#' + vNo;
    msg += `📄  ${cleanVNo}\n`;
    msg += `📅 ${vDate}\n`;
    msg += `💰 *المبلغ:* *${window.fmtNum(amount)}* ${currency}\n`;
    if (tafqeetText) {
        msg += `✍️ *المبلغ كتابةً:* فقط ${tafqeetText}\n`;
    }
    if (parties.statement) {
        msg += `📝 *البيان:* ${parties.statement}\n`;
    }
    if (parties.ccName) {
        msg += `🏷️ *المشروع:* ${parties.ccName}\n`;
    }
    msg += `━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;

    if (parties.structuredPayments && parties.structuredPayments.length > 0) {
        msg += `💳 *طريقة السداد والتحويل:* \n`;
        parties.structuredPayments.forEach((p, idx) => {
            if (p.type === 'bank') {
                msg += `  🏦 *البنك:* ${p.bankName || 'تحويل بنكي'}\n`;
                if (p.transferNo) msg += `  🔢 *رقم التحويلة / الإشعار:* ${p.transferNo}\n`;
                msg += `  💵 *مبلغ التحويل:* *${window.fmtNum(p.amount)}* ${currency}\n`;
                if (p.desc) msg += `  📝 *بيان التحويل:* ${p.desc}\n`;
            } else if (p.type === 'cheque') {
                msg += `  ✍️ *شيك بنكي:* ${p.bankName ? `مسحوب على (${p.bankName})` : 'شيك بنكي'}\n`;
                if (p.chequeNo) msg += `  🔢 *رقم الشيك:* ${p.chequeNo}\n`;
                if (p.dueDate) msg += `  📅 *تاريخ الاستحقاق:* ${p.dueDate}\n`;
                msg += `  💵 *مبلغ الشيك:* *${window.fmtNum(p.amount)}* ${currency}\n`;
                if (p.desc) msg += `  📝 *بيان الشيك:* ${p.desc}\n`;
            } else if (p.type === 'cash') {
                msg += `  💵 *طريقة الدفع:* نقداً\n`;
                if (p.safeName) msg += `  🏛️ *الخزينة:* ${p.safeName}\n`;
                msg += `  💰 *المبلغ النقدي:* *${window.fmtNum(p.amount)}* ${currency}\n`;
                if (p.desc) msg += `  📝 *البيان:* ${p.desc}\n`;
            }
            if (idx < parties.structuredPayments.length - 1) {
                msg += `  ──────────────────────\n`;
            }
        });
        msg += `━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    } else if (parties.pmDetails && parties.pmDetails.length > 0) {
        msg += `💳 *وسيلة الدفع:*\n${parties.pmDetails.join('\n')}\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    }

    msg += `📊  ${balInfo.balanceDescription}\n`;
    if (balInfo.foreignBalanceDescription) {
        msg += `${balInfo.foreignBalanceDescription}\n`;
    }
    msg += `━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;

    msg += `شاكرين لكم\n`;
    msg += `🏢 *${companyName}* - إدارة الحسابات العامة`;
    return msg;
};

window.buildWAPromissoryNoteMessage = function(targetType, parties) {
    if (!parties) parties = window.getVoucherParties();
    const isPayment = parties.isPayment;
    const vTitle = isPayment ? 'سند صرف مالي رسمي معتمد' : 'سند قبض مالي رسمي معتمد';
    const vSubTitle = isPayment ? '(كمبيالة صرف / إشعار خصم رسمي)' : '(كمبيالة قبض / إشعار توريد رسمي)';
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = (document.getElementById('hdr-date')?.value || document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0]).trim();
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const tafqeetText = typeof window.formatArabicTafqeet === 'function' ? window.formatArabicTafqeet(amount, currency) : '';

    let companyName = 'الإدارة المالية';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const otherParty = isDebtor ? parties.creditor : parties.debtor;
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment);

    let msg = `╔══════════════════════════════════════╗\n`;
    msg += `║      🏛️ ${vTitle}       ║\n`;
    msg += `║       ${vSubTitle}       ║\n`;
    msg += `╚══════════════════════════════════════╝\n`;
    msg += `📋 *رقم السند:* #${vNo}\n`;
    msg += `📅 *تاريخ التحرير:* ${vDate}\n`;
    msg += `══════════════════════════════════════\n`;

    if (isPayment) {
        if (isDebtor) {
            msg += `أقر وأعترف بأننا قيدنا وصرفنا لحساب المكرم:\n`;
            msg += `👤 *المستفيد:* ${activeParty.name}\n`;
        } else {
            msg += `نحيطكم علماً بأنه تم قيد وخصم السند من حسابكم:\n`;
            msg += `👤 *الحساب المخصوم منه:* ${activeParty.name}\n`;
            msg += `👤 *لصالح المستفيد:* ${otherParty.name}\n`;
        }
    } else {
        if (isDebtor) {
            msg += `أقر وأعترف بأننا استلمنا وقيدنا لحسابكم:\n`;
            msg += `👤 *حساب القبض:* ${activeParty.name}\n`;
            msg += `👤 *من المكرم:* ${otherParty.name}\n`;
        } else {
            msg += `وصلنا واستلمنا من المكرم:\n`;
            msg += `👤 *الدافع / المسدد:* ${activeParty.name}\n`;
        }
    }

    msg += `💰 *مبلغاً وقدره:* *${window.fmtNum(amount)}* ${currency}\n`;
    if (tafqeetText) {
        msg += `✍️ *وقدره كتابةً:* فقط ${tafqeetText}\n`;
    }
    if (parties.statement) {
        msg += `📝 *وذلك عن:* ${parties.statement}\n`;
    }
    if (parties.ccName) {
        msg += `🏷️ *المشروع / مركز التكلفة:* ${parties.ccName}\n`;
    }
    msg += `══════════════════════════════════════\n`;

    if (parties.structuredPayments && parties.structuredPayments.length > 0) {
        msg += `💳 *بيانات السداد المعتمدة:*\n`;
        parties.structuredPayments.forEach((p, idx) => {
            if (p.type === 'bank') {
                msg += `  🏦 *البنك:* ${p.bankName || 'تحويل مصرفي'}\n`;
                if (p.transferNo) msg += `  🔢 *رقم التحويلة / الإشعار:* ${p.transferNo}\n`;
                msg += `  💵 *المبلغ:* *${window.fmtNum(p.amount)}* ${currency}\n`;
            } else if (p.type === 'cheque') {
                msg += `  ✍️ *شيك بنكي:* ${p.bankName ? `مسحوب على (${p.bankName})` : 'شيك'}\n`;
                if (p.chequeNo) msg += `  🔢 *رقم الشيك:* ${p.chequeNo}\n`;
                if (p.dueDate) msg += `  📅 *الاستحقاق:* ${p.dueDate}\n`;
                msg += `  💵 *المبلغ:* *${window.fmtNum(p.amount)}* ${currency}\n`;
            } else if (p.type === 'cash') {
                msg += `  💵 *طريقة الدفع:* نقداً (${p.safeName || 'الخزينة'})\n`;
                msg += `  💰 *المبلغ:* *${window.fmtNum(p.amount)}* ${currency}\n`;
            }
            if (idx < parties.structuredPayments.length - 1) msg += `  ──────────────────────\n`;
        });
        msg += `══════════════════════════════════════\n`;
    } else if (parties.pmDetails && parties.pmDetails.length > 0) {
        msg += `💳 *بيانات السداد:*\n${parties.pmDetails.join('\n')}\n══════════════════════════════════════\n`;
    }

    msg += `📊 *الموقف المالي الختامي (دفتر الأستاذ العام):*\n`;
    msg += `رصيد حسابكم الحالي طرفنا:\n`;
    msg += `${balInfo.balanceDescription}\n`;
    if (balInfo.foreignBalanceDescription) {
        msg += `${balInfo.foreignBalanceDescription}\n`;
    }
    msg += `══════════════════════════════════════\n`;
    msg += `          *ختم واعتماد الإدارة المالية*\n`;
    msg += `          [ معتمد ومقيد إلكترونياً ]\n`;
    msg += `🏢 *${companyName}* - إدارة الحسابات`;
    return msg;
};

window.buildWAImageCardTextMessage = function(targetType, parties) {
    if (!parties) parties = window.getVoucherParties();
    const isPayment = parties.isPayment;
    const vTypeName = isPayment ? 'صرف مالي' : 'قبض مالي';
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = (document.getElementById('hdr-date')?.value || document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0]).trim();
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment, currency);

    let companyName = 'الإدارة المالية';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    let msg = `السلام عليكم ورحمة الله وبركاته 🌸\n`;
    msg += `عزيزنا: *${activeParty.name}* المحترم\n\n`;
    msg += `مرفق لكم كارت إشعار سند *${vTypeName}* رقم (#${vNo}) بتاريخ ${vDate} بمبلغ (*${window.fmtNum(amount)}* ${currency}).\n`;
    if (parties.statement) msg += `📝 *البيان:* ${parties.statement}\n`;
    msg += `📊 *الموقف المالي بدفتر الأستاذ:* ${balInfo.balanceDescription}\n`;
    if (balInfo.foreignBalanceDescription) msg += `   ${balInfo.foreignBalanceDescription}\n`;
    msg += `\n(كارت السند المصور مرفق أعلاه 👆)\n`;
    msg += `🏢 *${companyName}* - إدارة الحسابات العامة`;
    return msg;
};

window.buildWATailoredMessage = function(targetType, parties, formatType) {
    if (formatType === 'promissory_note_box') {
        return window.buildWAPromissoryNoteMessage(targetType, parties);
    }
    return window.buildWATextLineMessage(targetType, parties);
};

// ═══ رسم كارت السند المصور اللحظي على Canvas ═══
window.renderWAVoucherImageCard = function(targetType = 'debtor', parties) {
    if (!parties) parties = window.getVoucherParties();
    let canvas = document.getElementById('wa-voucher-canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'wa-voucher-canvas';
        canvas.style.display = 'none';
        document.body.appendChild(canvas);
    }
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const width = 900;
    const height = 540;
    canvas.width = width;
    canvas.height = height;

    const isPayment = parties.isPayment;
    const isDebtor = (targetType === 'debtor');
    const activeParty = isDebtor ? parties.debtor : parties.creditor;
    const amount = parties.amount || 0;
    const currency = parties.currency || 'SDG';
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'جديد').trim();
    const vDate = (document.getElementById('hdr-date')?.value || document.getElementById('voucher-date')?.value || new Date().toISOString().split('T')[0]).trim();
    const tafqeetText = typeof window.formatArabicTafqeet === 'function' ? window.formatArabicTafqeet(amount, currency) : '';
    const balInfo = window.getPartyAccountBalance(activeParty.id, activeParty.name, targetType, amount, isPayment);

    let companyName = 'الإدارة المالية - الحسابات العامة';
    try {
        const cfg = JSON.parse(localStorage.getItem('erp_company_settings') || localStorage.getItem('erp_settings_v1') || '{}');
        if (cfg.companyName) companyName = cfg.companyName;
    } catch(e) {}

    // 1. Background
    const bgGrad = ctx.createLinearGradient(0, 0, width, height);
    bgGrad.addColorStop(0, '#0a0f1d');
    bgGrad.addColorStop(1, '#1e293b');
    ctx.fillStyle = bgGrad;
    ctx.fillRect(0, 0, width, height);

    ctx.strokeStyle = '#334155';
    ctx.lineWidth = 3;
    ctx.strokeRect(4, 4, width - 8, height - 8);

    // 2. Header Banner
    const hdrGrad = ctx.createLinearGradient(0, 0, width, 0);
    hdrGrad.addColorStop(0, '#881337');
    hdrGrad.addColorStop(0.5, '#991b1b');
    hdrGrad.addColorStop(1, '#7f1d1d');
    ctx.fillStyle = hdrGrad;
    ctx.fillRect(6, 6, width - 12, 90);

    ctx.strokeStyle = '#fbbf24';
    ctx.lineWidth = 2.5;
    ctx.beginPath();
    ctx.moveTo(6, 96);
    ctx.lineTo(width - 6, 96);
    ctx.stroke();

    ctx.textAlign = 'right';
    ctx.direction = 'rtl';
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 26px "Tajawal", "Segoe UI", Arial';
    ctx.fillText('سند صرف مالي رسمي معتمد', width - 35, 45);

    ctx.fillStyle = '#fde047';
    ctx.font = '15px "Tajawal", "Segoe UI", Arial';
    ctx.fillText(`🏛️ ${companyName}`, width - 35, 75);

    // Header Voucher # & Date Box (Left side)
    ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
    ctx.fillRect(25, 20, 240, 60);
    ctx.strokeStyle = 'rgba(251, 191, 36, 0.4)';
    ctx.lineWidth = 1;
    ctx.strokeRect(25, 20, 240, 60);

    ctx.textAlign = 'left';
    ctx.direction = 'ltr';
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 16px monospace';
    ctx.fillText(`NO: #${vNo}`, 40, 45);
    ctx.fillStyle = '#cbd5e1';
    ctx.font = '13px "Tajawal", Arial';
    ctx.fillText(`DATE: ${vDate}`, 40, 68);

    // 3. Body: Right Side
    ctx.direction = 'rtl';
    ctx.textAlign = 'right';

    ctx.fillStyle = 'rgba(255, 255, 255, 0.04)';
    ctx.fillRect(width - 440, 115, 410, 80);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.12)';
    ctx.strokeRect(width - 440, 115, 410, 80);

    const roleLabel = isDebtor ? 'إلى حساب (المستفيد / المستلم):' : 'الصرف خصماً من حساب:';
    ctx.fillStyle = isDebtor ? '#34d399' : '#fca5a5';
    ctx.font = 'bold 14px "Tajawal", Arial';
    ctx.fillText(roleLabel, width - 455, 140);

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 20px "Tajawal", Arial';
    ctx.fillText(activeParty.name || 'حساب غير محدد', width - 455, 175);

    // Statement Box
    ctx.fillStyle = 'rgba(255, 255, 255, 0.03)';
    ctx.fillRect(width - 440, 205, 410, 75);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
    ctx.strokeRect(width - 440, 205, 410, 75);

    ctx.fillStyle = '#94a3b8';
    ctx.font = 'bold 13px "Tajawal", Arial';
    ctx.fillText('📝 البيان والسبب:', width - 455, 228);

    ctx.fillStyle = '#e2e8f0';
    ctx.font = '15px "Tajawal", Arial';
    const stmt = parties.statement || 'سداد مستحقات مالية معتمدة';
    ctx.fillText(stmt.length > 45 ? stmt.substring(0, 45) + '...' : stmt, width - 455, 258);

    // Cost Center / Payment Method Box
    ctx.fillStyle = 'rgba(255, 255, 255, 0.03)';
    ctx.fillRect(width - 440, 290, 410, 105);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
    ctx.strokeRect(width - 440, 290, 410, 105);

    ctx.fillStyle = '#38bdf8';
    ctx.font = 'bold 13px "Tajawal", Arial';
    ctx.fillText('💳 طريقة السداد والتحويل المالي:', width - 455, 314);

    ctx.fillStyle = '#cbd5e1';
    ctx.font = '14px "Tajawal", Arial';
    let pmStr = 'نقداً عبر الخزينة';
    if (parties.structuredPayments && parties.structuredPayments.length > 0) {
        const p = parties.structuredPayments[0];
        if (p.type === 'bank') pmStr = `🏦 تحويل: ${p.bankName || 'بنك'} | رقم: ${p.transferNo || '-'}`;
        else if (p.type === 'cheque') pmStr = `✍️ شيك: ${p.bankName || 'بنك'} | رقم: ${p.chequeNo || '-'}`;
        else if (p.type === 'cash') pmStr = `💵 نقداً: ${p.safeName || 'الخزينة'}`;
    }
    ctx.fillText(pmStr, width - 455, 342);

    if (parties.ccName) {
        ctx.fillStyle = '#94a3b8';
        ctx.font = '13px "Tajawal", Arial';
        ctx.fillText(`🏷️ المشروع: ${parties.ccName}`, width - 455, 372);
    }

    // 4. Body: Left Side (Grand Amount & GL Balance)
    ctx.fillStyle = 'rgba(245, 158, 11, 0.07)';
    ctx.fillRect(30, 115, 390, 130);
    ctx.strokeStyle = '#f59e0b';
    ctx.lineWidth = 1.8;
    ctx.strokeRect(30, 115, 390, 130);

    ctx.fillStyle = '#fbbf24';
    ctx.font = 'bold 14px "Tajawal", Arial';
    ctx.fillText('💰 المبلغ المعتمد الصافي:', 405, 142);

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 30px monospace';
    ctx.fillText(`${window.fmtNum(amount)} ${currency}`, 405, 185);

    if (tafqeetText) {
        ctx.fillStyle = '#fde68a';
        ctx.font = '13px "Tajawal", Arial';
        const taf = `فقط ${tafqeetText}`;
        ctx.fillText(taf.length > 38 ? taf.substring(0, 38) + '...' : taf, 405, 222);
    }

    // General Ledger Balance Pill
    const isCreditBal = balInfo.status === 'credit';
    const isDebitBal = balInfo.status === 'debit';
    ctx.fillStyle = isCreditBal ? 'rgba(16, 185, 129, 0.12)' : (isDebitBal ? 'rgba(239, 68, 68, 0.12)' : 'rgba(148, 163, 184, 0.12)');
    ctx.fillRect(30, 260, 390, 65);
    ctx.strokeStyle = isCreditBal ? '#10b981' : (isDebitBal ? '#ef4444' : '#64748b');
    ctx.lineWidth = 1.2;
    ctx.strokeRect(30, 260, 390, 65);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '12px "Tajawal", Arial';
    ctx.fillText('📊 رصيد الحساب الختامي في دفتر الأستاذ العام:', 405, 284);

    ctx.fillStyle = isCreditBal ? '#86efac' : (isDebitBal ? '#fca5a5' : '#cbd5e1');
    ctx.font = 'bold 15px "Tajawal", Arial';
    ctx.fillText(`${window.fmtNum(balInfo.amount)} ${balInfo.currency} (${balInfo.shortTag})`, 405, 310);

    // Official Stamp
    ctx.save();
    ctx.translate(140, 425);
    ctx.beginPath();
    ctx.arc(0, 0, 42, 0, Math.PI * 2);
    ctx.strokeStyle = '#fbbf24';
    ctx.lineWidth = 2.5;
    ctx.stroke();

    ctx.beginPath();
    ctx.arc(0, 0, 36, 0, Math.PI * 2);
    ctx.strokeStyle = 'rgba(251, 191, 36, 0.5)';
    ctx.setLineDash([4, 3]);
    ctx.lineWidth = 1.2;
    ctx.stroke();
    ctx.setLineDash([]);

    ctx.textAlign = 'center';
    ctx.fillStyle = '#fde047';
    ctx.font = 'bold 11px "Tajawal", Arial';
    ctx.fillText('معتمد ومقيد', 0, -8);
    ctx.fillText('نظامياً ✅', 0, 10);
    ctx.font = '9px "Tajawal", Arial';
    ctx.fillStyle = '#cbd5e1';
    ctx.fillText('إدارة الحسابات', 0, 24);
    ctx.restore();

    ctx.textAlign = 'right';
    ctx.fillStyle = '#64748b';
    ctx.font = '12px "Tajawal", Arial';
    ctx.fillText('🛡️ سند مالي إلكتروني معتمد رسمياً ومقيد بدفتر الأستاذ العام', width - 210, 435);

    // 5. Footer Bottom Bar
    ctx.fillStyle = '#070b14';
    ctx.fillRect(6, height - 38, width - 12, 32);
    ctx.fillStyle = '#475569';
    ctx.font = '11px "Tajawal", monospace';
    ctx.fillText(`ELSAJANA ERP • FINANCIAL VOUCHER SYSTEM • SECURE DISPATCH`, width - 35, height - 18);

    const previewImg = document.getElementById('wa-modal-image-preview');
    if (previewImg) {
        previewImg.src = canvas.toDataURL('image/png');
    }
};

window.copyWAVoucherImageToClipboard = function() {
    let canvas = document.getElementById('wa-voucher-canvas');
    const parties = window.getVoucherParties();
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    const targetType = (targetMode === 'both') ? activeTab : targetMode;

    window.renderWAVoucherImageCard(targetType, parties);
    canvas = document.getElementById('wa-voucher-canvas');

    if (!canvas) {
        showToast('تعذر العثور على كارت السند للتصوير', 'error');
        return;
    }
    canvas.toBlob(blob => {
        if (!blob) {
            showToast('تعذر تجهيز الصورة للنسخ', 'error');
            return;
        }
        if (navigator.clipboard && window.ClipboardItem) {
            const item = new ClipboardItem({ 'image/png': blob });
            navigator.clipboard.write([item]).then(() => {
                showToast('📋 تم نسخ صورة السند المصور للحافظة! يمكنك الآن لصقها بـ Ctrl+V في محادثة واتساب', 'success', 6000);
            }).catch(err => {
                console.warn('Clipboard write error:', err);
                showToast('💡 تم تجهيز الصورة، يمكنك حفظها بزر (حفظ صورة السند)', 'info', 6000);
            });
        } else {
            showToast('💡 يمكنك حفظ الصورة بزر (حفظ صورة السند) ثم إرسالها في محادثة واتساب', 'info', 6000);
        }
    }, 'image/png');
};

window.downloadWAVoucherImage = function() {
    let canvas = document.getElementById('wa-voucher-canvas');
    const parties = window.getVoucherParties();
    const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    const targetType = (targetMode === 'both') ? activeTab : targetMode;

    window.renderWAVoucherImageCard(targetType, parties);
    canvas = document.getElementById('wa-voucher-canvas');

    if (!canvas) {
        showToast('تعذر تجهيز كارت السند للتصوير', 'error');
        return;
    }

    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'voucher').trim();
    const a = document.createElement('a');
    a.download = `سند_مالي_${vNo}_${targetType}.png`;
    a.href = canvas.toDataURL('image/png');
    a.click();
    showToast('💾 تم تنزيل صورة كمبيالة السند المصورة كملف PNG بجودة فائقة', 'success');
};

window.copyFullTextMessage = function() {
    const isBoth = (document.getElementById('wa-modal-target-mode')?.value || 'both') === 'both';
    let text = '';
    if (isBoth) {
        text = document.getElementById('wa-both-preview-message')?.value || '';
    } else {
        text = document.getElementById('wa-modal-message')?.value || '';
    }
    if (!text) {
        showToast('لا يوجد نص للنسخ', 'warning');
        return;
    }
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('📋 تم نسخ نص كمبيالة السند بالكامل إلى الحافظة بنجاح!', 'success');
        }).catch(() => {
            showToast('تم تحديد النص، يمكنك نسخه بـ Ctrl+C', 'info');
        });
    }
};

// ═══ التبديل المرن بين أطراف السند (المدين / الدائن / كليهما) وتحديث الرصيد اللحظي ═══
window.setWATargetMode = function(mode = 'both') {
    const modeInput = document.getElementById('wa-modal-target-mode');
    if (modeInput) modeInput.value = mode;

    const parties = window.getVoucherParties();

    const btnDebtor = document.getElementById('btn-wa-target-debtor');
    const btnCreditor = document.getElementById('btn-wa-target-creditor');
    const btnBoth = document.getElementById('btn-wa-target-both');
    const statusBadge = document.getElementById('wa-target-status-badge');

    const singleView = document.getElementById('wa-single-party-view');
    const bothView = document.getElementById('wa-both-parties-view');
    const desktopBtnText = document.getElementById('btn-wa-send-desktop-text');
    const webBtnText = document.getElementById('btn-wa-send-web-text') || document.getElementById('btn-text-wa-web');
    const gatewayBtnText = document.getElementById('btn-wa-send-gateway-text') || document.getElementById('btn-text-wa-gateway');

    [btnDebtor, btnCreditor, btnBoth].forEach(btn => {
        if (btn) {
            btn.classList.remove('active');
            btn.style.boxShadow = 'none';
        }
    });

    if (btnDebtor) {
        btnDebtor.style.background = 'rgba(16, 185, 129, 0.08)';
        btnDebtor.style.borderColor = 'rgba(16, 185, 129, 0.25)';
        btnDebtor.style.color = '#34d399';
    }
    if (btnCreditor) {
        btnCreditor.style.background = 'rgba(239, 68, 68, 0.08)';
        btnCreditor.style.borderColor = 'rgba(239, 68, 68, 0.25)';
        btnCreditor.style.color = '#fca5a5';
    }
    if (btnBoth) {
        btnBoth.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(239, 68, 68, 0.12) 100%)';
        btnBoth.style.borderColor = 'rgba(255, 255, 255, 0.2)';
        btnBoth.style.color = '#cbd5e1';
    }

    if (mode === 'debtor') {
        if (btnDebtor) {
            btnDebtor.classList.add('active');
            btnDebtor.style.background = 'rgba(16, 185, 129, 0.28)';
            btnDebtor.style.borderColor = '#10b981';
            btnDebtor.style.color = '#34d399';
            btnDebtor.style.boxShadow = '0 0 14px rgba(16, 185, 129, 0.4)';
        }
        if (statusBadge) {
            statusBadge.style.background = 'rgba(16, 185, 129, 0.18)';
            statusBadge.style.color = '#86efac';
            statusBadge.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            statusBadge.textContent = `محدد: إلى حساب (${parties.debtor.name})`;
        }
        if (singleView) singleView.style.display = 'flex';
        if (bothView) bothView.style.display = 'none';

        const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
        const singlePreviewBox = document.getElementById('wa-preview-single-box');
        const bothPreviewBox = document.getElementById('wa-preview-both-box');
        const bothTabs = document.getElementById('wa-both-tabs-toolbar');
        const imgContainer = document.getElementById('wa-modal-image-preview-container');

        const isTextMode = promissoryWrapper && promissoryWrapper.style.display === 'none';
        if (!isTextMode) {
            if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
            if (singlePreviewBox) singlePreviewBox.style.display = 'none';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        } else {
            if (singlePreviewBox) singlePreviewBox.style.display = 'flex';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        }
        if (bothTabs) bothTabs.style.display = 'none';
        if (imgContainer) imgContainer.style.display = 'none';
        try { window.renderPromissoryNotePreview('debtor'); } catch(e) {}
        try { window.renderWAVoucherImageCard('debtor', parties); } catch(e) {}

        const labelName = document.getElementById('wa-label-recipient-name');
        if (labelName) {
            labelName.textContent = `👤 إلى:`;
            labelName.style.color = '#34d399';
        }

        const nameInput = document.getElementById('wa-modal-recipient-name');
        if (nameInput) nameInput.value = parties.debtor.name;

        const phoneInput = document.getElementById('wa-modal-phone');
        if (phoneInput) phoneInput.value = parties.debtor.phone || '';

        const curCurrencyDebtor = window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG';
        const debtorBal = window.getPartyAccountBalance(parties.debtor.id, parties.debtor.name, 'debtor', parties.amount, parties.isPayment, curCurrencyDebtor);
        const balEl = document.getElementById('wa-modal-live-balance');
        const balTag = document.getElementById('wa-modal-live-balance-tag');
        if (balEl) {
            balEl.textContent = `${window.fmtNum(debtorBal.amount)}`;
            const singleChip = document.getElementById('wa-modal-single-currency-chip');
            if (singleChip) singleChip.value = debtorBal.currency;
        }
        if (balTag) {
            balTag.textContent = debtorBal.shortTag;
            if (debtorBal.status === 'debit') {
                balTag.style.background = '#ef4444';
                balTag.style.color = '#ffffff';
                balTag.style.borderColor = '#f87171';
            } else if (debtorBal.status === 'credit') {
                balTag.style.background = '#10b981';
                balTag.style.color = '#022c22';
                balTag.style.borderColor = '#34d399';
            } else {
                balTag.style.background = '#475569';
                balTag.style.color = '#ffffff';
                balTag.style.borderColor = '#64748b';
            }
        }

        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const msgInput = document.getElementById('wa-modal-message');
        if (msgInput) {
            const msg = window.buildWATailoredMessage('debtor', parties, fmt);
            msgInput.value = msg;
            msgInput.dataset.defaultText = msg;
        }
        if (desktopBtnText) desktopBtnText.textContent = 'تطبيق واتساب (المستلم)';
        if (webBtnText) webBtnText.textContent = 'واتساب ويب (المستلم)';
        if (gatewayBtnText) gatewayBtnText.textContent = 'عبر الخادم (المستلم)';

    } else if (mode === 'creditor') {
        if (btnCreditor) {
            btnCreditor.classList.add('active');
            btnCreditor.style.background = 'rgba(239, 68, 68, 0.28)';
            btnCreditor.style.borderColor = '#ef4444';
            btnCreditor.style.color = '#fca5a5';
            btnCreditor.style.boxShadow = '0 0 14px rgba(239, 68, 68, 0.4)';
        }
        if (statusBadge) {
            statusBadge.style.background = 'rgba(239, 68, 68, 0.18)';
            statusBadge.style.color = '#fca5a5';
            statusBadge.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            statusBadge.textContent = `محدد: من (${parties.creditor.name})`;
        }
        if (singleView) singleView.style.display = 'flex';
        if (bothView) bothView.style.display = 'none';

        const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
        const singlePreviewBox = document.getElementById('wa-preview-single-box');
        const bothPreviewBox = document.getElementById('wa-preview-both-box');
        const bothTabs = document.getElementById('wa-both-tabs-toolbar');
        const imgContainer = document.getElementById('wa-modal-image-preview-container');

        const isTextMode = promissoryWrapper && promissoryWrapper.style.display === 'none';
        if (!isTextMode) {
            if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
            if (singlePreviewBox) singlePreviewBox.style.display = 'none';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        } else {
            if (singlePreviewBox) singlePreviewBox.style.display = 'flex';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        }
        if (bothTabs) bothTabs.style.display = 'none';
        if (imgContainer) imgContainer.style.display = 'none';

        try { window.renderPromissoryNotePreview('creditor'); } catch(e) {}
        try { window.renderWAVoucherImageCard('creditor', parties); } catch(e) {}

        const labelName = document.getElementById('wa-label-recipient-name');
        if (labelName) {
            labelName.textContent = `🏛️ من:`;
            labelName.style.color = '#f87171';
        }

        const nameInput = document.getElementById('wa-modal-recipient-name');
        if (nameInput) nameInput.value = parties.creditor.name;

        const phoneInput = document.getElementById('wa-modal-phone');
        if (phoneInput) phoneInput.value = parties.creditor.phone || '';

        const curCurrencyCreditor = window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG';
        const creditorBal = window.getPartyAccountBalance(parties.creditor.id, parties.creditor.name, 'creditor', parties.amount, parties.isPayment, curCurrencyCreditor);
        const balEl = document.getElementById('wa-modal-live-balance');
        const balTag = document.getElementById('wa-modal-live-balance-tag');
        if (balEl) {
            balEl.textContent = `${window.fmtNum(creditorBal.amount)}`;
            const singleChip = document.getElementById('wa-modal-single-currency-chip');
            if (singleChip) singleChip.value = creditorBal.currency;
        }
        if (balTag) {
            balTag.textContent = creditorBal.shortTag;
            if (creditorBal.status === 'debit') {
                balTag.style.background = '#ef4444';
                balTag.style.color = '#ffffff';
                balTag.style.borderColor = '#f87171';
            } else if (creditorBal.status === 'credit') {
                balTag.style.background = '#10b981';
                balTag.style.color = '#022c22';
                balTag.style.borderColor = '#34d399';
            } else {
                balTag.style.background = '#475569';
                balTag.style.color = '#ffffff';
                balTag.style.borderColor = '#64748b';
            }
        }

        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const msgInput = document.getElementById('wa-modal-message');
        if (msgInput) {
            const msg = window.buildWATailoredMessage('creditor', parties, fmt);
            msgInput.value = msg;
            msgInput.dataset.defaultText = msg;
        }
        if (desktopBtnText) desktopBtnText.textContent = 'تطبيق واتساب (الصارف)';
        if (webBtnText) webBtnText.textContent = 'واتساب ويب (الصارف)';
        if (gatewayBtnText) gatewayBtnText.textContent = 'عبر الخادم (الصارف)';

    } else if (mode === 'both') {
        if (btnBoth) {
            btnBoth.classList.add('active');
            btnBoth.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.3) 0%, rgba(239, 68, 68, 0.3) 100%)';
            btnBoth.style.borderColor = '#fbbf24';
            btnBoth.style.color = '#fef08a';
            btnBoth.style.boxShadow = '0 0 14px rgba(251, 191, 36, 0.4)';
        }
        if (statusBadge) {
            statusBadge.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(239, 68, 68, 0.2) 100%)';
            statusBadge.style.color = '#fef08a';
            statusBadge.style.borderColor = 'rgba(251, 191, 36, 0.5)';
            statusBadge.textContent = 'محدد: إرسال لكليهما معاً (إلى + من) 👥';
        }
        if (singleView) singleView.style.display = 'none';
        if (bothView) bothView.style.display = 'flex';

        const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
        const singlePreviewBox = document.getElementById('wa-preview-single-box');
        const bothPreviewBox = document.getElementById('wa-preview-both-box');
        const bothTabs = document.getElementById('wa-both-tabs-toolbar');
        const imgContainer = document.getElementById('wa-modal-image-preview-container');

        const isTextMode = promissoryWrapper && promissoryWrapper.style.display === 'none';
        if (!isTextMode) {
            if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
            if (singlePreviewBox) singlePreviewBox.style.display = 'none';
            if (bothPreviewBox) bothPreviewBox.style.display = 'none';
        } else {
            if (bothPreviewBox) bothPreviewBox.style.display = 'flex';
            if (singlePreviewBox) singlePreviewBox.style.display = 'none';
        }
        if (bothTabs) bothTabs.style.display = 'flex';
        if (imgContainer) imgContainer.style.display = 'none';

        const curCurrency = window._waCurrentModalCurrency || document.getElementById('currency')?.value || 'SDG';
        const debtorBal = window.getPartyAccountBalance(parties.debtor.id, parties.debtor.name, 'debtor', parties.amount, parties.isPayment, curCurrency);
        const creditorBal = window.getPartyAccountBalance(parties.creditor.id, parties.creditor.name, 'creditor', parties.amount, parties.isPayment, curCurrency);

        const debtorRole = document.getElementById('wa-both-debtor-role-label');
        if (debtorRole) debtorRole.textContent = parties.debtor.roleTitle;
        const debtorName = document.getElementById('wa-both-debtor-name');
        if (debtorName) debtorName.textContent = parties.debtor.name;
        const debtorPhone = document.getElementById('wa-both-debtor-phone');
        if (debtorPhone) {
            let dVal = parties.debtor.phone || '';
            if (!dVal) {
                try {
                    dVal = (parties.debtor.id ? localStorage.getItem(`erp_wa_phone_${parties.debtor.id}`) : '') || 
                           (parties.debtor.name ? localStorage.getItem(`erp_wa_phone_${parties.debtor.name}`) : '') || '';
                } catch(e) {}
            }
            debtorPhone.value = dVal;
        }
        const dBalEl = document.getElementById('wa-both-debtor-balance');
        if (dBalEl) {
            const dTagBg = debtorBal.shortTag === 'له' ? '#10b981' : (debtorBal.shortTag === 'عليه' ? '#ef4444' : '#0284c7');
            const dCurrSelect = window.getWACurrencySelectOptions(debtorBal.currency);
            dBalEl.innerHTML = `<span style="color: #6ee7b7; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد المستلم:</span> <div style="display: inline-flex; align-items: center; gap: 6px;"><b style="color: #ffffff; background: #064e3b; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #10b981; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">${window.fmtNum(debtorBal.amount)}</b><select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">${dCurrSelect}</select><span style="background: ${dTagBg}; color: #ffffff; border: 1.5px solid rgba(255,255,255,0.4); padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">${debtorBal.shortTag}</span></div>`;
        }

        const creditorRole = document.getElementById('wa-both-creditor-role-label');
        if (creditorRole) creditorRole.textContent = parties.creditor.roleTitle;
        const creditorName = document.getElementById('wa-both-creditor-name');
        if (creditorName) creditorName.textContent = parties.creditor.name;
        const creditorPhone = document.getElementById('wa-both-creditor-phone');
        if (creditorPhone) {
            let cVal = parties.creditor.phone || '';
            if (!cVal) {
                try {
                    cVal = (parties.creditor.id ? localStorage.getItem(`erp_wa_phone_${parties.creditor.id}`) : '') || 
                           (parties.creditor.name ? localStorage.getItem(`erp_wa_phone_${parties.creditor.name}`) : '') || '';
                } catch(e) {}
            }
            creditorPhone.value = cVal;
        }
        const cBalEl = document.getElementById('wa-both-creditor-balance');
        if (cBalEl) {
            const cTagBg = creditorBal.shortTag === 'له' ? '#10b981' : (creditorBal.shortTag === 'عليه' ? '#ef4444' : '#0284c7');
            const cCurrSelect = window.getWACurrencySelectOptions(creditorBal.currency);
            cBalEl.innerHTML = `<span style="color: #fca5a5; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد الصرف:</span> <div style="display: inline-flex; align-items: center; gap: 6px;"><b style="color: #ffffff; background: #7f1d1d; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #ef4444; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">${window.fmtNum(creditorBal.amount)}</b><select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">${cCurrSelect}</select><span style="background: ${cTagBg}; color: #ffffff; border: 1.5px solid rgba(255,255,255,0.4); padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">${creditorBal.shortTag}</span></div>`;
        }

        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        window._waBothPartyMessages = {
            debtor: window.buildWATailoredMessage('debtor', parties, fmt),
            creditor: window.buildWATailoredMessage('creditor', parties, fmt)
        };
        const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
        window.switchWABothPreviewTab(activeTab);
        try { window.renderPromissoryNotePreview(activeTab); } catch(e) {}
        try { window.renderWAVoucherImageCard(activeTab, parties); } catch(e) {}

        if (desktopBtnText) desktopBtnText.textContent = 'تطبيق واتساب (الطرفين)';
        if (webBtnText) webBtnText.textContent = 'واتساب ويب (الطرفين)';
        if (gatewayBtnText) gatewayBtnText.textContent = 'عبر الخادم (الطرفين)';
    } else {
        if (desktopBtnText) desktopBtnText.textContent = 'تطبيق واتساب';
    }
};

window.swapWATargetParties = function() {
    const swapBtn = document.getElementById('btn-wa-swap-parties');
    if (swapBtn) {
        swapBtn.style.transform = 'rotate(180deg) scale(1.15)';
        setTimeout(() => {
            swapBtn.style.transform = 'rotate(0deg) scale(1)';
        }, 300);
    }

    const currentMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    if (currentMode === 'debtor') {
        window.setWATargetMode('creditor');
        showToast('⇄ تم التبديل إلى: من', 'info');
    } else if (currentMode === 'creditor') {
        window.setWATargetMode('both');
        showToast('⇄ تم التبديل إلى: كليهما معاً (إلى + من)', 'info');
    } else {
        window.setWATargetMode('debtor');
        showToast('⇄ تم التبديل إلى: إلى', 'info');
    }
};

// ═══ تحكم النافذة: التكبير والاستعادة والسحب الحر بالماوس واللمس ═══
window.toggleWAModalMaximize = function() {
    const card = document.getElementById('wa-voucher-modal-card');
    const btn = document.getElementById('btn-wa-modal-maximize');
    if (!card) return;

    if (card.classList.contains('wa-maximized')) {
        card.classList.remove('wa-maximized');
        card.style.width = card.dataset.origW || '1400px';
        card.style.height = card.dataset.origH || '92vh';
        card.style.maxWidth = '98vw';
        card.style.maxHeight = '940px';
        card.style.position = 'relative';
        card.style.left = '';
        card.style.top = '';
        card.style.margin = 'auto';
        if (btn) {
            btn.innerHTML = '🗖';
            btn.title = 'تكبير النافذة';
        }
    } else {
        card.dataset.origW = card.style.width || '1400px';
        card.dataset.origH = card.style.height || '92vh';
        card.classList.add('wa-maximized');
        card.style.position = 'fixed';
        card.style.left = '1vw';
        card.style.top = '1vh';
        card.style.width = '98vw';
        card.style.height = '98vh';
        card.style.maxWidth = '98vw';
        card.style.maxHeight = '98vh';
        card.style.margin = '0';
        if (btn) {
            btn.innerHTML = '🗗';
            btn.title = 'استعادة الحجم الطبيعي';
        }
    }
};

window.makeModalDraggable = function(cardId, headerId) {
    const card = document.getElementById(cardId);
    const header = document.getElementById(headerId);
    if (!card || !header || header.dataset.dragInit === 'true') return;
    header.dataset.dragInit = 'true';

    let isDragging = false;
    let startX, startY;
    let initialLeft, initialTop;

    const beginDrag = (clientX, clientY) => {
        if (card.classList.contains('wa-maximized')) return false;
        isDragging = true;
        startX = clientX;
        startY = clientY;

        const rect = card.getBoundingClientRect();
        initialLeft = rect.left;
        initialTop = rect.top;

        card.style.position = 'fixed';
        card.style.left = initialLeft + 'px';
        card.style.top = initialTop + 'px';
        card.style.margin = '0';
        card.style.transform = 'none';

        document.body.style.userSelect = 'none';
        return true;
    };

    header.addEventListener('mousedown', (e) => {
        if (e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) return;
        if (!beginDrag(e.clientX, e.clientY)) return;

        const onMouseMove = (ev) => {
            if (!isDragging) return;
            ev.preventDefault();
            const dx = ev.clientX - startX;
            const dy = ev.clientY - startY;

            let newLeft = initialLeft + dx;
            let newTop = initialTop + dy;

            const maxLeft = Math.max(10, window.innerWidth - 80);
            const maxTop = Math.max(10, window.innerHeight - 50);
            newLeft = Math.max(10, Math.min(maxLeft, newLeft));
            newTop = Math.max(10, Math.min(maxTop, newTop));

            card.style.left = newLeft + 'px';
            card.style.top = newTop + 'px';
        };

        const onMouseUp = () => {
            isDragging = false;
            document.body.style.userSelect = '';
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });

    header.addEventListener('touchstart', (e) => {
        if (e.target.closest('button') || e.target.closest('input')) return;
        const touch = e.touches[0];
        if (!touch) return;
        if (!beginDrag(touch.clientX, touch.clientY)) return;

        const onTouchMove = (ev) => {
            if (!isDragging) return;
            const t = ev.touches[0];
            if (!t) return;
            const dx = t.clientX - startX;
            const dy = t.clientY - startY;

            let newLeft = initialLeft + dx;
            let newTop = initialTop + dy;
            newLeft = Math.max(10, Math.min(window.innerWidth - 80, newLeft));
            newTop = Math.max(10, Math.min(window.innerHeight - 50, newTop));

            card.style.left = newLeft + 'px';
            card.style.top = newTop + 'px';
        };

        const onTouchEnd = () => {
            isDragging = false;
            document.body.style.userSelect = '';
            document.removeEventListener('touchmove', onTouchMove);
            document.removeEventListener('touchend', onTouchEnd);
        };

        document.addEventListener('touchmove', onTouchMove, { passive: true });
        document.addEventListener('touchend', onTouchEnd);
    }, { passive: true });
};

window.initWAVoucherModalDrag = function() {
    window.makeModalDraggable('wa-voucher-modal-card', 'wa-voucher-modal-header');
};

// ═══ فتح نافذة الواتساب الرئيسية لسند الصرف المفرد ═══
window.openMasterWhatsAppDispatchModal = function() {
    const modal = document.getElementById('wa-voucher-modal');
    if (!modal) return;

    if (typeof window.cancelHandoverTimer === 'function') {
        window.cancelHandoverTimer();
    }
    const handoverPanel = document.getElementById('wa-modal-handover-panel');
    if (handoverPanel) handoverPanel.style.display = 'none';

    const parties = window.getVoucherParties();
    if (!parties) return;
    if (parties.amount <= 0 && (!parties.debtor.name || parties.debtor.name === 'المستفيد')) {
        showToast('يرجى تحديد الحساب والمبلغ أولاً قبل إرسال الواتساب', 'warning');
        return;
    }

    const blockIdInput = document.getElementById('wa-modal-block-id');
    if (blockIdInput) blockIdInput.value = '';

    const debtorAccName = document.getElementById('wa-debtor-acc-name');
    if (debtorAccName) debtorAccName.textContent = parties.debtor.name;
    const debtorTitle = document.getElementById('wa-debtor-role-title');
    if (debtorTitle) debtorTitle.textContent = parties.debtor.roleTitle;

    const creditorAccName = document.getElementById('wa-creditor-acc-name');
    if (creditorAccName) creditorAccName.textContent = parties.creditor.name;
    const creditorTitle = document.getElementById('wa-creditor-role-title');
    if (creditorTitle) creditorTitle.textContent = parties.creditor.roleTitle;

    const defaultCurr = document.getElementById('currency')?.value || 'SDG';
    window._waCurrentModalCurrency = defaultCurr;
    const topCurrSelect = document.getElementById('wa-modal-currency-select');
    if (topCurrSelect) topCurrSelect.value = defaultCurr;

    window.setWATargetMode('both');
    window.setWAFormatType('promissory_note');

    if (typeof window.updatePartyCardStatus === 'function') {
        window.updatePartyCardStatus('debtor', false);
        window.updatePartyCardStatus('creditor', false);
    }

    const mainActionBtn = document.getElementById('btn-wa-send-both-action');
    if (mainActionBtn) {
        mainActionBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%)';
        mainActionBtn.style.borderColor = '#34d399';
        mainActionBtn.style.boxShadow = '0 0 18px rgba(16, 185, 129, 0.4)';
        mainActionBtn.style.animation = 'none';
        mainActionBtn.innerHTML = `
            <span style="font-size: 1.45rem;">🚀</span>
            <span id="btn-wa-send-both-action-text">إرسال إلى كل الأطراف عبر الواتساب ⚡</span>
        `;
        mainActionBtn.onclick = function() {
            window.executeWASend('gateway');
        };
    }

    const alertEl = document.getElementById('wa-modal-status-alert');
    if (alertEl) alertEl.style.display = 'none';

    const card = document.getElementById('wa-voucher-modal-card');
    if (card && !card.classList.contains('wa-maximized')) {
        card.style.position = 'relative';
        card.style.left = '';
        card.style.top = '';
        card.style.margin = 'auto';
    }

    modal.style.display = 'flex';
    try {
        const activeTab = document.getElementById('wa-both-active-tab')?.value || 'debtor';
        const promissoryWrapper = document.getElementById('wa-promissory-preview-wrapper');
        const bothBox = document.getElementById('wa-preview-both-box');
        const singleBox = document.getElementById('wa-preview-single-box');
        const imgContainer = document.getElementById('wa-modal-image-preview-container');
        if (promissoryWrapper) promissoryWrapper.style.display = 'flex';
        if (bothBox) bothBox.style.display = 'none';
        if (singleBox) singleBox.style.display = 'none';
        if (imgContainer) imgContainer.style.display = 'none';
        const toggleBtn = document.getElementById('btn-wa-toggle-preview-mode');
        if (toggleBtn) toggleBtn.innerHTML = '📄 PDF / 📝 نص';

        window.renderPromissoryNotePreview(activeTab);
        window.renderWAVoucherImageCard(activeTab, parties);
    } catch(e) {}

    if (typeof window.initWAVoucherModalDrag === 'function') {
        window.initWAVoucherModalDrag();
    }
};

window.openBlockWhatsAppDispatchModal = window.openMasterWhatsAppDispatchModal;

window.closeWAVoucherModal = function() {
    if (window._waHandoverTimer) {
        clearInterval(window._waHandoverTimer);
        window._waHandoverTimer = null;
    }
    window._pendingHandoverData = null;
    const handoverPanel = document.getElementById('wa-modal-handover-panel');
    if (handoverPanel) handoverPanel.style.display = 'none';
    const modal = document.getElementById('wa-voucher-modal');
    if (modal) modal.style.display = 'none';
};

window.markBlockAsSentWA = function(blockId, phone) {
    if (!blockId) return;
    const badge = document.getElementById(`wa-dispatch-badge-${blockId}`);
    if (badge) {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
        badge.innerHTML = `<span>✅ تم الإرسال واتساب (${timeStr})</span>`;
        badge.style.display = 'inline-flex';
        badge.title = `أُرسل للرقم: ${phone || 'مسجل'} في ${timeStr}`;
    }
};

// ═══════════════════════════════════════════════════════════════════
// WhatsApp Instant & Auto-Dispatch on Voucher Save Engine
// ═══════════════════════════════════════════════════════════════════
const WA_AUTO_SETTINGS_KEY = 'erp_wa_auto_dispatch_settings';

window.getWAAutoDispatchSettings = function() {
    try {
        const raw = localStorage.getItem(WA_AUTO_SETTINGS_KEY);
        if (raw) return JSON.parse(raw);
    } catch(e) {}
    return {
        enabled: false,
        target: 'both',
        method: 'gateway'
    };
};

window.saveWAAutoDispatchSettings = function(settings) {
    try {
        localStorage.setItem(WA_AUTO_SETTINGS_KEY, JSON.stringify(settings));
    } catch(e) {}
    window.syncWAAutoDispatchUI();
};

window.syncWAAutoDispatchUI = function() {
    const s = window.getWAAutoDispatchSettings();
    const toggleMain = document.getElementById('wa-auto-send-toggle');
    const targetSelect = document.getElementById('wa-auto-send-target');
    const methodSelect = document.getElementById('wa-auto-send-method');
    const toggleModal = document.getElementById('wa-modal-auto-dispatch-sync');
    const bar = document.getElementById('wa-auto-dispatch-bar');

    if (toggleMain) toggleMain.checked = !!s.enabled;
    if (toggleModal) toggleModal.checked = !!s.enabled;
    if (targetSelect) targetSelect.value = s.target || 'both';
    if (methodSelect) methodSelect.value = s.method || 'desktop';

    if (bar) {
        if (s.enabled) {
            bar.style.borderColor = '#25d366';
            bar.style.boxShadow = '0 0 16px rgba(37, 211, 102, 0.45)';
            bar.style.background = 'rgba(15, 23, 42, 0.92)';
        } else {
            bar.style.borderColor = 'rgba(37, 211, 102, 0.3)';
            bar.style.boxShadow = 'none';
            bar.style.background = 'rgba(15, 23, 42, 0.75)';
        }
    }

    // ═══ Sync Unified Footer Bar Auto-Send Control Button ═══
    const toggleBtn = document.getElementById('btn-toggle-auto-send');
    const optBtn = document.getElementById('btn-auto-send-options');
    const autoSendIcon = document.getElementById('auto-send-icon');
    const autoSendLabel = document.getElementById('auto-send-label');
    const autoSendPill = document.getElementById('auto-send-status-pill');
    const saveBtn = document.getElementById('btn-save');

    if (toggleBtn) {
        if (s.enabled) {
            toggleBtn.className = 'btn crystal-btn btn-auto-send-toggle active';
            if (optBtn) optBtn.className = 'btn crystal-btn btn-auto-send-options active';
            
            const targetDesc = s.target === 'both' ? 'لكليهما' : (s.target === 'debtor' ? 'المستلم' : 'الصارف');
            toggleBtn.title = `الوضع الحالي: إرسال تلقائي مفعّل (${targetDesc}) ⚡ - سيتم إرسال الكمبيالة/السند فور الحفظ. انقر للتحويل إلى: حفظ فقط بدون إرسال`;
            if (optBtn) optBtn.title = `خيارات وجهة الإرسال التلقائي (الحالي: ${targetDesc}) - انقر لتغيير الوجهة`;
            
            if (autoSendIcon) autoSendIcon.innerHTML = '⚡';
            if (autoSendLabel) autoSendLabel.textContent = 'إرسال تلقائي:';
            if (autoSendPill) {
                autoSendPill.textContent = `مفعّل (${targetDesc})`;
                autoSendPill.style.background = '#10b981';
                autoSendPill.style.color = '#ffffff';
                autoSendPill.style.boxShadow = '0 0 10px rgba(16,185,129,0.5)';
            }
        } else {
            toggleBtn.className = 'btn crystal-btn btn-auto-send-toggle inactive';
            if (optBtn) optBtn.className = 'btn crystal-btn btn-auto-send-options inactive';
            
            toggleBtn.title = 'الوضع الحالي: حفظ فقط 💾 - لن يتم إرسال أي كمبيالة أو إشعار. انقر لتفعيل الإرسال التلقائي مع الحفظ';
            if (optBtn) optBtn.title = 'خيارات وجهة الإرسال التلقائي - انقر للاختيار والتفعيل';
            
            if (autoSendIcon) autoSendIcon.innerHTML = '💾';
            if (autoSendLabel) autoSendLabel.textContent = 'إرسال تلقائي:';
            if (autoSendPill) {
                autoSendPill.textContent = 'معطّل (حفظ فقط)';
                autoSendPill.style.background = 'rgba(148, 163, 184, 0.25)';
                autoSendPill.style.color = '#cbd5e1';
                autoSendPill.style.boxShadow = 'none';
            }
        }
    }

    if (saveBtn && !window.IdempotencyGuard?.isSaving && !saveBtn.disabled) {
        if (s.enabled) {
            saveBtn.title = 'حفظ واعتماد السند وإرسال الكمبيالة مباشرة عبر الواتساب ⚡';
            saveBtn.innerHTML = `
                <span>✅</span>
                <span>حفظ وإرسال السند</span>
                <span style="font-size: 0.8rem; background: rgba(0,0,0,0.28); padding: 1px 7px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.3); font-weight: 800;">⚡ إرسال</span>
            `;
        } else {
            saveBtn.title = 'حفظ واعتماد وترحيل السند المالي فقط في النظام (بدون إرسال)';
            saveBtn.innerHTML = `
                <span>✅</span>
                <span>حفظ واعتماد السند</span>
            `;
        }
    }
};

window.toggleAutoSendMode = function() {
    const s = window.getWAAutoDispatchSettings();
    const newEnabled = !s.enabled;
    window.toggleWAAutoDispatch(newEnabled);
};

window.toggleAutoSendDropdown = function(e) {
    if (e) {
        e.stopPropagation();
        e.preventDefault();
    }
    const menu = document.getElementById('auto-send-dropdown-menu');
    if (!menu) return;
    const isShowing = (menu.style.display === 'block');
    menu.style.display = isShowing ? 'none' : 'block';
};

window.selectAutoSendOption = function(enabled, target = null) {
    const menu = document.getElementById('auto-send-dropdown-menu');
    if (menu) menu.style.display = 'none';

    const s = window.getWAAutoDispatchSettings();
    s.enabled = !!enabled;
    if (target) {
        s.target = target;
    }
    window.saveWAAutoDispatchSettings(s);

    if (s.enabled) {
        const targetTitle = s.target === 'both' ? 'لكلا الطرفين (المستلم + الصارف)' : (s.target === 'debtor' ? 'للطرف المستلم فقط' : 'للطرف الصارف فقط');
        showToast(`⚡ تم تفعيل الإرسال التلقائي: سيتم إرسال الكمبيالة/السند مباشرة فور الحفظ (${targetTitle})`, 'success');
    } else {
        showToast('💾 تم ضبط الحفظ فقط: لن يتم إرسال أي كمبيالة أو سند تلقائياً عند الحفظ', 'info');
    }
};

// Close auto-send dropdown on click outside
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('auto-send-control-wrapper');
    const menu = document.getElementById('auto-send-dropdown-menu');
    if (menu && menu.style.display === 'block' && wrap && !wrap.contains(e.target)) {
        menu.style.display = 'none';
    }
});

window.toggleWAAutoDispatch = function(checked) {
    const s = window.getWAAutoDispatchSettings();
    s.enabled = !!checked;
    window.saveWAAutoDispatchSettings(s);
    if (s.enabled) {
        const targetTitle = s.target === 'both' ? 'لكلا الطرفين (المستلم + الصارف)' : (s.target === 'debtor' ? 'للمستلم فقط' : 'للصارف فقط');
        showToast(`⚡ تم تفعيل الإرسال التلقائي للواتساب عند الاعتماد (${targetTitle})`, 'success');
    } else {
        showToast('⏸️ تم إيقاف الإرسال التلقائي للواتساب (حفظ فقط بدون إرسال)', 'info');
    }
};

window.changeWAAutoDispatchTarget = function(val) {
    const s = window.getWAAutoDispatchSettings();
    s.target = val;
    window.saveWAAutoDispatchSettings(s);
    showToast(`🎯 الوجهة التلقائية: ${val === 'both' ? 'كليهما معاً (إلى + من)' : (val === 'debtor' ? 'إلى' : 'من')}`, 'info');
};

window.changeWAAutoDispatchMethod = function(val) {
    const s = window.getWAAutoDispatchSettings();
    s.method = val;
    window.saveWAAutoDispatchSettings(s);
    const methodDesc = val === 'desktop' ? 'تطبيق واتساب الخاطف (صفر استهلاك رامات)' : (val === 'web' ? 'واتساب ويب الموحد' : 'خادم الواتساب بالخلفية');
    showToast(`⚡ أسلوب الإرسال: ${methodDesc}`, 'info');
};

// Auto WhatsApp Dispatch trigger when voucher is successfully saved and posted
window.triggerAutoWADispatchOnSave = function(voucher, vNo) {
    const s = window.getWAAutoDispatchSettings();
    if (!s || !s.enabled) return;

    const parties = window.getVoucherParties();
    if (!parties) return;

    const targetMode = s.target || 'both';
    const method = s.method || 'gateway';

    setTimeout(() => {
        if (targetMode === 'debtor') {
            const cleanPhone = window.normalizePhoneNumber(parties.debtor.phone);
            const msg = window.buildWATailoredMessage('debtor', parties);
            window.sendDirectWABackground(method, cleanPhone, msg, parties.debtor.name);
        } else if (targetMode === 'creditor') {
            const cleanPhone = window.normalizePhoneNumber(parties.creditor.phone);
            const msg = window.buildWATailoredMessage('creditor', parties);
            window.sendDirectWABackground(method, cleanPhone, msg, parties.creditor.name);
        } else if (targetMode === 'both') {
            if (method === 'gateway') {
                const cleanDebtorPhone = window.normalizePhoneNumber(parties.debtor.phone);
                const debtorMsg = window.buildWATailoredMessage('debtor', parties);
                if (cleanDebtorPhone) window.sendDirectWABackground('gateway', cleanDebtorPhone, debtorMsg, parties.debtor.name);

                const cleanCreditorPhone = window.normalizePhoneNumber(parties.creditor.phone);
                const creditorMsg = window.buildWATailoredMessage('creditor', parties);
                if (cleanCreditorPhone) window.sendDirectWABackground('gateway', cleanCreditorPhone, creditorMsg, parties.creditor.name);
            } else {
                if (typeof window.openMasterWhatsAppDispatchModal === 'function') {
                    window.openMasterWhatsAppDispatchModal();
                    window.executeWASend(method);
                }
            }
        }
    }, 450);
};

window.sendDirectWABackground = function(method = 'gateway', cleanPhone, message, recipientName = '') {
    if (!message) return;
    if (!cleanPhone) {
        console.warn('Auto WA Gateway: Phone is empty for', recipientName);
        return;
    }
    const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
    const formData = new FormData();
    formData.append('userId', userId);
    formData.append('phone', cleanPhone);
    formData.append('message', message);
    fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(`✅ تم إرسال إشعار واتساب تلقائياً إلى: ${recipientName || cleanPhone}`, 'success');
        } else {
            console.warn('WA send error:', data.error);
        }
    })
    .catch(err => console.error('WA send error:', err));
};

// ═══ تحديث الحالة البصرية لبطاقة الطرف (المستلم / الصارف) ═══
window.updatePartyCardStatus = function(partyType, isSent) {
    const isDebtor = (partyType === 'debtor');
    const tagEl = document.getElementById(isDebtor ? 'wa-debtor-status-tag' : 'wa-creditor-status-tag');
    const cardEl = document.getElementById(isDebtor ? 'wa-both-debtor-card' : 'wa-both-creditor-card');

    if (tagEl) {
        if (isSent) {
            tagEl.innerHTML = `<span>✅ تم الفتح</span>`;
            tagEl.style.background = 'rgba(16, 185, 129, 0.25)';
            tagEl.style.border = '1px solid #10b981';
            tagEl.style.color = '#86efac';
            tagEl.style.boxShadow = '0 0 8px rgba(16, 185, 129, 0.4)';
        } else {
            tagEl.innerHTML = `<span>⏳ بانتظار الإرسال</span>`;
            tagEl.style.background = 'rgba(148, 163, 184, 0.15)';
            tagEl.style.border = '1px solid rgba(148, 163, 184, 0.3)';
            tagEl.style.color = '#94a3b8';
            tagEl.style.boxShadow = 'none';
        }
    }

    if (cardEl) {
        if (isSent) {
            cardEl.style.borderColor = '#10b981';
            cardEl.style.boxShadow = '0 0 14px rgba(16, 185, 129, 0.35)';
            cardEl.style.background = 'rgba(16, 185, 129, 0.14)';
        } else {
            cardEl.style.borderColor = isDebtor ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.4)';
            cardEl.style.boxShadow = 'none';
            cardEl.style.background = isDebtor ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)';
        }
    }
};

// ═══ تحويل الزر الرئيسي لحالة الاحتفال والنجاح المكتمل ═══
window.setBothActionCompleteState = function() {
    const mainActionBtn = document.getElementById('btn-wa-send-both-action');
    if (mainActionBtn) {
        mainActionBtn.style.background = 'linear-gradient(135deg, #059669 0%, #10b981 100%)';
        mainActionBtn.style.borderColor = '#34d399';
        mainActionBtn.style.boxShadow = '0 0 20px rgba(16, 185, 129, 0.6)';
        mainActionBtn.style.animation = 'none';
        mainActionBtn.innerHTML = `
            <span style="font-size: 1.45rem;">🎉</span>
            <span id="btn-wa-send-both-action-text">اكتمل إرسال السند لكلا الطرفين بنجاح (المستلم + الصارف) ✅</span>
        `;
        mainActionBtn.onclick = function() {
            window.closeWAVoucherModal();
        };
    }
};

window._waHandoverTimer = null;
window._pendingHandoverData = null;

window.cancelHandoverTimer = function() {
    if (window._waHandoverTimer) {
        clearInterval(window._waHandoverTimer);
        window._waHandoverTimer = null;
    }
    window._pendingHandoverData = null;
    const panel = document.getElementById('wa-modal-handover-panel');
    if (panel) panel.style.display = 'none';
};

// ═══ تسليم وإرسال الطرف الثاني (الصارف) بنقرة مستخدم مباشرة ═══
window.triggerHandoverNextParty = async function() {
    if (window._waHandoverTimer) {
        clearInterval(window._waHandoverTimer);
        window._waHandoverTimer = null;
    }

    const parties = window.getVoucherParties();

    if (!window._pendingHandoverData) {
        const credPhone = document.getElementById('wa-both-creditor-phone')?.value?.trim() || parties?.creditor?.phone || '';
        const cleanCredPhone = window.normalizePhoneNumber(credPhone);
        if (!cleanCredPhone) {
            const credInput = document.getElementById('wa-both-creditor-phone');
            if (credInput) {
                credInput.focus();
                credInput.style.borderColor = '#ef4444';
                credInput.style.boxShadow = '0 0 12px rgba(239, 68, 68, 0.7)';
                setTimeout(() => { if (credInput) { credInput.style.borderColor = 'rgba(239,68,68,0.5)'; credInput.style.boxShadow = 'none'; } }, 3000);
            }
            showToast('⚠️ يرجى إدخال هاتف الصارف (واتساب) أولاً لإتمام الإرسال!', 'warning');
            return;
        }
        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const credMsg = (window._waBothPartyMessages && window._waBothPartyMessages.creditor)
            ? window._waBothPartyMessages.creditor
            : window.buildWATailoredMessage('creditor', parties, fmt);
        window._pendingHandoverData = {
            method: 'gateway',
            phone: cleanCredPhone,
            message: credMsg,
            name: parties?.creditor?.name || 'الصارف',
            blockId: null
        };
    }

    const { phone, message, name, blockId: bId } = window._pendingHandoverData;
    window._pendingHandoverData = null;

    const alertEl = document.getElementById('wa-modal-status-alert');
    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();

    const flightAnim = (typeof window.startPromissoryFlightAnimation === 'function')
        ? window.startPromissoryFlightAnimation({
            recipientName: name || phone,
            phone: phone,
            voucherNo: vNo,
            amount: (document.getElementById('amount-local')?.value || parties?.amount || '0'),
            currency: (document.getElementById('currency')?.value || 'SDG'),
            partyType: 'طرف الصرف'
        })
        : null;

    if (alertEl) {
        alertEl.style.display = 'block';
        alertEl.style.background = 'rgba(56, 189, 248, 0.15)';
        alertEl.style.color = '#38bdf8';
        alertEl.innerHTML = `⏳ جاري إرسال الإشعار لـ (${name}) عبر خادم الواتساب المربوط...`;
    }

    try {
        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
        const fd = new FormData();
        fd.append('userId', userId);
        fd.append('phone', phone);

        try {
            const pdfBlob = await window.generatePromissoryNotePDFBlob('creditor', parties);
            if (pdfBlob) {
                const safeName = (name || 'الصارف').replace(/[/\\?%*:|"<>]/g, '_');
                const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                fd.append('file', new File([pdfBlob], docName, { type: 'application/pdf' }));
                fd.append('documentName', docName);
                fd.append('message', window.buildWAPromissoryPDFCaption('creditor', parties));
            } else {
                fd.append('message', message);
            }
        } catch(e) {
            fd.append('message', message);
        }

        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, { method: 'POST', body: fd });
        const data = await res.json();

        if (data && data.success) {
            if (flightAnim) flightAnim.complete({ recipientName: name, phone: phone });
            window.updatePartyCardStatus('creditor', true);
            if (bId) window.markBlockAsSentWA(bId, phone);
            window.setBothActionCompleteState();

            const statusText = document.getElementById('wa-handover-status-text');
            const subText = document.getElementById('wa-handover-subtext');
            const btnHandover = document.getElementById('btn-wa-handover-next');
            if (statusText) {
                statusText.innerHTML = `<span>🎉</span> تم إرسال كمبيالة السند لكلا الطرفين (المستلم + الصارف) بنجاح عبر الواتساب!`;
                statusText.style.color = '#34d399';
            }
            if (subText) {
                subText.textContent = `تم تأكيد إرسال الإشعار بنجاح لكل من المستلم والصارف عبر خادم الواتساب.`;
            }
            if (btnHandover) {
                btnHandover.style.background = 'linear-gradient(135deg, #059669, #10b981)';
                btnHandover.style.borderColor = '#34d399';
                btnHandover.innerHTML = `<span>✅</span> <span>تم الإرسال للطرفين بنجاح (إغلاق)</span>`;
                btnHandover.onclick = function() { window.closeWAVoucherModal(); };
            }
            setTimeout(() => {
                const modal = document.getElementById('wa-voucher-modal');
                if (modal && modal.style.display !== 'none') {
                    window.closeWAVoucherModal();
                }
            }, 2500);
        } else {
            if (flightAnim) flightAnim.error(data?.error || 'تعذر الإرسال');
            else showToast('❌ تعذر إرسال الإشعار: ' + (data?.error || 'خطأ غير معروف'), 'error');
        }
    } catch(err) {
        console.error('WA Handover error:', err);
        if (flightAnim) flightAnim.error('تعذر الاتصال بخادم الواتساب');
        else showToast('⚠️ تعذر الاتصال بخادم الواتساب', 'error');
    }
};

// ═══ إرسال مباشر لأحد الطرفين بشكل فردي من بطاقته الخاصة ═══
window.sendSinglePartyDirectly = async function(partyType) {
    const parties = window.getVoucherParties();
    if (!parties) return;

    const isDebtor = (partyType === 'debtor');
    const partyObj = isDebtor ? parties.debtor : parties.creditor;
    const inputPhone = isDebtor 
        ? document.getElementById('wa-both-debtor-phone')?.value?.trim()
        : document.getElementById('wa-both-creditor-phone')?.value?.trim();
    
    const phone = inputPhone || partyObj.phone || '';
    const cleanPhone = window.normalizePhoneNumber(phone);

    if (!cleanPhone) {
        const inputEl = isDebtor ? document.getElementById('wa-both-debtor-phone') : document.getElementById('wa-both-creditor-phone');
        if (inputEl) {
            inputEl.focus();
            inputEl.style.borderColor = '#ef4444';
            inputEl.style.boxShadow = '0 0 12px rgba(239, 68, 68, 0.7)';
            setTimeout(() => {
                if (inputEl) {
                    inputEl.style.borderColor = isDebtor ? 'rgba(16,185,129,0.5)' : 'rgba(239,68,68,0.5)';
                    inputEl.style.boxShadow = 'none';
                }
            }, 3000);
        }
        showToast(`⚠️ يرجى إدخال رقم هاتف (${partyObj.name}) أولاً لإرسال الإشعار!`, 'warning');
        return;
    }

    window.cachePartyPhone(partyType, cleanPhone);

    const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();

    const flightAnim = (typeof window.startPromissoryFlightAnimation === 'function')
        ? window.startPromissoryFlightAnimation({
            recipientName: partyObj.name || cleanPhone,
            phone: cleanPhone,
            voucherNo: vNo,
            amount: (document.getElementById('amount-local')?.value || parties.amount || '0'),
            currency: (document.getElementById('currency')?.value || 'SDG'),
            partyType: isDebtor ? 'المستفيد / المستلم' : 'طرف الصرف'
        })
        : null;

    const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
    const fd = new FormData();
    fd.append('userId', userId);
    fd.append('phone', cleanPhone);

    try {
        const pdfBlob = await window.generatePromissoryNotePDFBlob(partyType, parties);
        if (pdfBlob) {
            const safeName = (partyObj.name || partyType).replace(/[/\\?%*:|"<>]/g, '_');
            const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
            fd.append('file', new File([pdfBlob], docName, { type: 'application/pdf' }));
            fd.append('documentName', docName);
            fd.append('message', window.buildWAPromissoryPDFCaption(partyType, parties));
        } else {
            const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
            const msg = (window._waBothPartyMessages && window._waBothPartyMessages[partyType])
                ? window._waBothPartyMessages[partyType]
                : window.buildWATailoredMessage(partyType, parties, fmt);
            fd.append('message', msg);
        }
    } catch (e) {
        console.warn('PDF blob generation fallback:', e);
        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const msg = (window._waBothPartyMessages && window._waBothPartyMessages[partyType])
            ? window._waBothPartyMessages[partyType]
            : window.buildWATailoredMessage(partyType, parties, fmt);
        fd.append('message', msg);
    }

    try {
        const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data && data.success) {
            if (flightAnim) flightAnim.complete({ recipientName: partyObj.name || cleanPhone, phone: cleanPhone });
            window.updatePartyCardStatus(partyType, true);
        } else {
            if (flightAnim) flightAnim.error(`تعذر الإرسال: ${data?.error || 'خطأ'}`);
            else showToast(`❌ تعذر الإرسال: ${data?.error || 'خطأ'}`, 'error');
        }
    } catch(err) {
        if (flightAnim) flightAnim.error('تعذر الاتصال بخادم الواتساب');
        else showToast('⚠️ تعذر الاتصال بخادم الواتساب', 'error');
    }

    window.updatePartyCardStatus(partyType, true);

    const otherType = isDebtor ? 'creditor' : 'debtor';
    const otherTag = document.getElementById(isDebtor ? 'wa-creditor-status-tag' : 'wa-debtor-status-tag');
    const isOtherSent = otherTag && otherTag.textContent.includes('تم الفتح');

    if (isOtherSent) {
        window.setBothActionCompleteState();
    } else {
        const otherPartyObj = isDebtor ? parties.creditor : parties.debtor;
        const mainActionBtn = document.getElementById('btn-wa-send-both-action');
        if (mainActionBtn) {
            mainActionBtn.style.background = isDebtor ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #10b981, #059669)';
            mainActionBtn.style.borderColor = '#fca5a5';
            mainActionBtn.style.boxShadow = '0 0 20px rgba(239, 68, 68, 0.6)';
            mainActionBtn.style.animation = 'pulse 1.3s infinite ease-in-out';
            mainActionBtn.innerHTML = `
                <span style="font-size: 1.45rem;">📤</span>
                <span id="btn-wa-send-both-action-text">اضغط هنا الآن لإرسال الطرف الآخر (${otherPartyObj.name}) ➔</span>
            `;
            mainActionBtn.onclick = function() {
                window.sendSinglePartyDirectly(otherType);
            };
        }
    }
};

// ═══ المحرك المركزي لإرسال السند (مفرد أو لكليهما) ═══
window.executeWASend = async function(method = 'gateway') {
    const targetMode = document.getElementById('wa-modal-target-mode')?.value || 'both';
    const alertEl = document.getElementById('wa-modal-status-alert');
    const parties = window.getVoucherParties();

    if (targetMode === 'debtor' || targetMode === 'creditor') {
        const phone = document.getElementById('wa-modal-phone')?.value?.trim();
        const message = document.getElementById('wa-modal-message')?.value?.trim();

        if (!message) {
            showToast('نص الرسالة فارغ!', 'warning');
            return;
        }

        const cleanPhone = window.normalizePhoneNumber(phone);
        if (!cleanPhone) {
            const pInput = document.getElementById('wa-modal-phone');
            if (pInput) { pInput.focus(); pInput.style.borderColor = '#ef4444'; setTimeout(() => { if (pInput) pInput.style.borderColor = ''; }, 2500); }
            showToast('⚠️ يرجى إدخال رقم الهاتف للمستلم أولاً!', 'warning');
            return;
        }

        const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();
        const activePartyObj = (targetMode === 'debtor') ? parties.debtor : parties.creditor;

        const flightAnim = (typeof window.startPromissoryFlightAnimation === 'function')
            ? window.startPromissoryFlightAnimation({
                recipientName: activePartyObj.name || cleanPhone,
                phone: cleanPhone,
                voucherNo: vNo,
                amount: (document.getElementById('amount-local')?.value || parties.amount || '0'),
                currency: (document.getElementById('currency')?.value || 'SDG'),
                partyType: (targetMode === 'debtor') ? 'المستفيد / المستلم' : 'طرف الصرف'
            })
            : null;

        if (alertEl) {
            alertEl.style.display = 'block';
            alertEl.style.background = 'rgba(56, 189, 248, 0.15)';
            alertEl.style.color = '#38bdf8';
            alertEl.innerHTML = '⏳ جاري تجهيز كمبيالة السند PDF وإرسالها فوراً عبر الواتساب...';
        }

        try {
            const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));
            const statusRes = await fetch(`api_index.php?r=wa-tenant&endpoint=status&userId=${userId}`);
            const statusData = await statusRes.json();

            if (statusData.status !== 'CONNECTED' && statusData.status !== 'AUTHENTICATED') {
                if (flightAnim) flightAnim.error('خادم الواتساب غير متصل. يرجى مسح رمز QR أولاً.');
                if (alertEl) {
                    alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    alertEl.style.color = '#f87171';
                    alertEl.innerHTML = '⚠️ خادم الواتساب غير مربوط حالياً. يرجى مسح رمز الاستجابة (QR) أولاً.';
                }
                const wantsQR = confirm('خادم الواتساب غير مربوط حالياً. هل تريد فتح نافذة الربط (QR) الآن؟');
                if (wantsQR && typeof openWAModal === 'function') {
                    openWAModal();
                }
                return;
            }

            const formData = new FormData();
            formData.append('userId', userId);
            formData.append('phone', cleanPhone);

            try {
                const pdfBlob = await window.generatePromissoryNotePDFBlob(targetMode, parties);
                if (pdfBlob) {
                    const safeName = (activePartyObj.name || targetMode).replace(/[/\\?%*:|"<>]/g, '_');
                    const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                    formData.append('file', new File([pdfBlob], docName, { type: 'application/pdf' }));
                    formData.append('documentName', docName);
                    formData.append('message', window.buildWAPromissoryPDFCaption(targetMode, parties));
                } else {
                    formData.append('message', message);
                }
            } catch(e) {
                console.warn('PDF blob generation fallback:', e);
                formData.append('message', message);
            }

            const sendRes = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, {
                method: 'POST',
                body: formData
            });
            const sendData = await sendRes.json();

            if (sendData && sendData.success) {
                if (flightAnim) {
                    flightAnim.complete({ recipientName: activePartyObj.name || cleanPhone, phone: cleanPhone });
                }

                const isTargetDebtor = (targetMode === 'debtor');
                const otherPartyObj = isTargetDebtor ? parties.creditor : parties.debtor;
                const otherPartyType = isTargetDebtor ? 'creditor' : 'debtor';
                const otherPhone = otherPartyObj.phone ? window.normalizePhoneNumber(otherPartyObj.phone) : '';

                if (otherPartyObj.name && otherPartyObj.name !== 'حساب الصرف/الخزينة') {
                    const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
                    const otherMsg = window.buildWATailoredMessage(otherPartyType, parties, fmt);
                    window._pendingHandoverData = {
                        method: 'gateway',
                        phone: otherPhone,
                        message: otherMsg,
                        name: otherPartyObj.name,
                        blockId: null
                    };

                    const handoverPanel = document.getElementById('wa-modal-handover-panel');
                    const statusText = document.getElementById('wa-handover-status-text');
                    const subText = document.getElementById('wa-handover-subtext');
                    const btnLabel = document.getElementById('btn-wa-handover-label');

                    if (handoverPanel) {
                        handoverPanel.style.display = 'block';
                        if (statusText) statusText.textContent = `تم إرسال إشعار (${isTargetDebtor ? parties.debtor.name : parties.creditor.name}) بنجاح عبر الواتساب!`;
                        if (subText) subText.textContent = `اضغط الزر أدناه لإرسال إشعار الطرف الآخر (${otherPartyObj.name}) فوراً عبر الخادم:`;
                        if (btnLabel) btnLabel.textContent = `إرسال إشعار (${otherPartyObj.name}) عبر الخادم الآن ➔`;
                    }
                    return;
                }

                setTimeout(() => window.closeWAVoucherModal(), 2200);
                return;
            } else {
                if (flightAnim) {
                    flightAnim.error(sendData?.error || 'فشل الإرسال عبر خادم الواتساب');
                }
                if (alertEl) {
                    alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    alertEl.style.color = '#f87171';
                    alertEl.innerHTML = '❌ فشل الإرسال عبر الخادم: ' + (sendData?.error || 'خطأ غير معروف');
                }
            }
        } catch (err) {
            console.error('WA Gateway error:', err);
            if (flightAnim) flightAnim.error('تعذر الإرسال عبر خادم الواتساب');
            if (alertEl) {
                alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                alertEl.style.color = '#f87171';
                alertEl.innerHTML = '⚠️ تعذر الوصول لخادم الواتساب. تأكد من تشغيل الخدمة.';
            }
        }
        return;
    }

    if (targetMode === 'both') {
        const debtorInput = document.getElementById('wa-both-debtor-phone');
        const creditorInput = document.getElementById('wa-both-creditor-phone');

        const debtorPhone = debtorInput?.value?.trim() || parties.debtor.phone || '';
        const creditorPhone = creditorInput?.value?.trim() || parties.creditor.phone || '';

        const cleanDebtorPhone = window.normalizePhoneNumber(debtorPhone);
        const cleanCreditorPhone = window.normalizePhoneNumber(creditorPhone);

        if (!cleanDebtorPhone) {
            if (debtorInput) {
                debtorInput.focus();
                debtorInput.style.borderColor = '#ef4444';
                debtorInput.style.boxShadow = '0 0 14px rgba(239, 68, 68, 0.8)';
                setTimeout(() => {
                    if (debtorInput) {
                        debtorInput.style.borderColor = 'rgba(16,185,129,0.5)';
                        debtorInput.style.boxShadow = 'none';
                    }
                }, 3500);
            }
            showToast(`⚠️ يرجى إدخال رقم هاتف المستلم (${parties.debtor.name}) لإتمام الإرسال للطرفين!`, 'warning', 5000);
            return;
        }

        if (!cleanCreditorPhone) {
            if (creditorInput) {
                creditorInput.focus();
                creditorInput.style.borderColor = '#ef4444';
                creditorInput.style.boxShadow = '0 0 14px rgba(239, 68, 68, 0.8)';
                setTimeout(() => {
                    if (creditorInput) {
                        creditorInput.style.borderColor = 'rgba(239,68,68,0.5)';
                        creditorInput.style.boxShadow = 'none';
                    }
                }, 3500);
            }
            showToast(`⚠️ يرجى إدخال رقم هاتف الصارف (${parties.creditor.name}) لإتمام الإرسال للطرفين!`, 'warning', 5000);
            return;
        }

        if (cleanDebtorPhone && parties.debtor) window.cachePartyPhone('debtor', cleanDebtorPhone);
        if (cleanCreditorPhone && parties.creditor) window.cachePartyPhone('creditor', cleanCreditorPhone);

        const fmt = document.getElementById('wa-modal-format-type')?.value || 'promissory_note';
        const debtorMsg = (window._waBothPartyMessages && window._waBothPartyMessages.debtor)
            ? window._waBothPartyMessages.debtor
            : window.buildWATailoredMessage('debtor', parties, fmt);
        const creditorMsg = (window._waBothPartyMessages && window._waBothPartyMessages.creditor)
            ? window._waBothPartyMessages.creditor
            : window.buildWATailoredMessage('creditor', parties, fmt);

        const vNo = (document.getElementById('voucher-number')?.innerText || document.getElementById('voucher-no-display')?.value || document.getElementById('voucher-no')?.value || 'VOUCHER').trim();
        const dualName = `${parties.debtor.name || 'المستلم'} و ${parties.creditor.name || 'الصارف'}`;

        const flightAnim = (typeof window.startPromissoryFlightAnimation === 'function')
            ? window.startPromissoryFlightAnimation({
                recipientName: dualName,
                phone: `${cleanDebtorPhone} / ${cleanCreditorPhone}`,
                voucherNo: vNo,
                amount: (document.getElementById('amount-local')?.value || parties.amount || '0'),
                currency: (document.getElementById('currency')?.value || 'SDG'),
                partyType: 'المستلم والصارف معاً'
            })
            : null;

        if (alertEl) {
            alertEl.style.display = 'block';
            alertEl.style.background = 'rgba(56, 189, 248, 0.15)';
            alertEl.style.color = '#38bdf8';
            alertEl.innerHTML = '⏳ جاري تجهيز كمبيالات السند PDF وإرسالها لكلا الطرفين عبر الواتساب...';
        }

        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));

        try {
            const statusRes = await fetch(`api_index.php?r=wa-tenant&endpoint=status&userId=${userId}`);
            const statusData = await statusRes.json();

            if (statusData.status !== 'CONNECTED' && statusData.status !== 'AUTHENTICATED') {
                if (flightAnim) flightAnim.error('خادم الواتساب غير متصل. يرجى ربط الحساب أولاً.');
                if (alertEl) {
                    alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    alertEl.style.color = '#f87171';
                    alertEl.innerHTML = '⚠️ خادم الواتساب غير متصل. يرجى ربط الحساب بالباركود أولاً.';
                }
                const wantsQR = confirm('خادم الواتساب غير مربوط حالياً. هل تريد فتح نافذة الربط (QR) الآن؟');
                if (wantsQR && typeof openWAModal === 'function') {
                    openWAModal();
                }
                return;
            }

            const debtorPdf = await window.generatePromissoryNotePDFBlob('debtor', parties).catch(err => {
                console.warn('Debtor PDF err:', err);
                return null;
            });
            const creditorPdf = await window.generatePromissoryNotePDFBlob('creditor', parties).catch(err => {
                console.warn('Creditor PDF err:', err);
                return null;
            });

            const promises = [];

            const fd1 = new FormData();
            fd1.append('userId', userId);
            fd1.append('phone', cleanDebtorPhone);
            if (debtorPdf) {
                const safeName = (parties.debtor.name || 'المستلم').replace(/[/\\?%*:|"<>]/g, '_');
                const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                fd1.append('file', new File([debtorPdf], docName, { type: 'application/pdf' }));
                fd1.append('documentName', docName);
                fd1.append('message', window.buildWAPromissoryPDFCaption('debtor', parties));
            } else {
                fd1.append('message', debtorMsg);
            }
            promises.push(fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, { method: 'POST', body: fd1 }).then(r => r.json()));

            const fd2 = new FormData();
            fd2.append('userId', userId);
            fd2.append('phone', cleanCreditorPhone);
            if (creditorPdf) {
                const safeName = (parties.creditor.name || 'الصارف').replace(/[/\\?%*:|"<>]/g, '_');
                const docName = `كمبيالة_سند_${vNo}_${safeName}.pdf`;
                fd2.append('file', new File([creditorPdf], docName, { type: 'application/pdf' }));
                fd2.append('documentName', docName);
                fd2.append('message', window.buildWAPromissoryPDFCaption('creditor', parties));
            } else {
                fd2.append('message', creditorMsg);
            }
            promises.push(fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${userId}`, { method: 'POST', body: fd2 }).then(r => r.json()));

            const results = await Promise.all(promises);
            const successful = results.filter(res => res && res.success).length;
            if (successful > 0) {
                if (flightAnim) {
                    flightAnim.complete({ recipientName: dualName, phone: `${cleanDebtorPhone} / ${cleanCreditorPhone}` });
                }
                window.updatePartyCardStatus('debtor', true);
                window.updatePartyCardStatus('creditor', true);
                window.setBothActionCompleteState();
                setTimeout(() => window.closeWAVoucherModal(), 2500);
            } else {
                if (flightAnim) {
                    flightAnim.error('فشل الإرسال لكلا الطرفين عبر الخادم');
                }
                if (alertEl) {
                    alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    alertEl.style.color = '#f87171';
                    alertEl.innerHTML = '❌ فشل الإرسال عبر الخادم. تحقق من أرقام الهواتف أو حالة الخادم.';
                }
            }
        } catch (err) {
            console.error('WA Dual Gateway error:', err);
            if (flightAnim) flightAnim.error('تعذر الإرسال لكلا الطرفين');
            if (alertEl) {
                alertEl.style.background = 'rgba(239, 68, 68, 0.15)';
                alertEl.style.color = '#f87171';
                alertEl.innerHTML = '⚠️ تعذر الإرسال عبر الخادم. تأكد من تشغيل خدمة الواتساب.';
            }
        }
    }
};

// Initialize Auto-Dispatch UI on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.syncWAAutoDispatchUI());
} else {
    window.syncWAAutoDispatchUI();
}



// ═════════════════════════════════════════════════════════════════════
// 🪞 REAL-TIME MIRROR SYNC ENGINE (نظام المرآة الفوري بين اللابتوب والجوال)
// ═════════════════════════════════════════════════════════════════════
window.syncMirrorData = async function() {
    try {
        const res = await fetch('api/mirror_sync.php?action=get_recent_vouchers&_t=' + Date.now());
        const data = await res.json();
        if (data && data.success) {
            if (data.nextVoucherNo) {
                window.DB_NEXT_VOUCHER_NO = data.nextVoucherNo;
                if (typeof currentViewedIndex !== 'undefined' && currentViewedIndex === -1) {
                    const vEl = document.getElementById('voucher-number');
                    if (vEl) vEl.innerText = data.nextVoucherNo;
                }
            }
            if (Array.isArray(data.vouchers) && data.vouchers.length > 0) {
                let currentList = (typeof window.getAllSinglePayments === 'function') ? window.getAllSinglePayments() : [];
                let updated = false;
                const existingMap = new Set(currentList.map(x => String(x.id || x.voucherNo)));
                for (let v of data.vouchers) {
                    const vKey = String(v.id || v.voucherNo);
                    if (vKey && !existingMap.has(vKey)) {
                        currentList.push(v);
                        existingMap.add(vKey);
                        updated = true;
                    }
                }
                if (updated) {
                    const getTenantKey = window.getTenantKey || ((k) => k);
                    localStorage.setItem(getTenantKey('erp_single_payments_v1'), JSON.stringify(currentList));
                    window.SERVER_SINGLE_PAYMENTS = currentList;
                    if (typeof singlePaymentsData !== 'undefined') singlePaymentsData = currentList;
                    if (typeof updateLedgerButtons === 'function') updateLedgerButtons();
                }
            }
        }
    } catch(e) {}
};

window.addEventListener('focus', () => {
    if (typeof window.syncMirrorData === 'function') window.syncMirrorData();
});
setInterval(() => {
    if (typeof window.syncMirrorData === 'function') window.syncMirrorData();
}, 5000);

// Auto-trigger sync on script ready
setTimeout(() => {
    if (typeof window.syncMirrorData === 'function') window.syncMirrorData();
}, 600);
