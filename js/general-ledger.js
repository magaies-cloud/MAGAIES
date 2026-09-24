/* ══════════════════════════════════════════════════
   general-ledger.js — Smart General Ledger (SGL)
   /* ═══════════════════════════════════════════════════════ */

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

    // Fallback default accounts if local storage has no accounts
    if (ALL_ACCOUNTS.length <= 1) {
        ALL_ACCOUNTS.push(
            { id: 'cust-demo-1', name: 'محمد عمار', icon: '👤', type: 'عميل' },
            { id: 'cust-demo-2', name: 'محمد أحمد', icon: '👤', type: 'عميل' },
            { id: 'safe-demo-1', name: 'الخزينة الرئيسية', icon: '💰', type: 'صندوق' },
            { id: 'bank-demo-1', name: 'بنك الخرطوم', icon: '🏦', type: 'بنك' },
            { id: 'supp-demo-1', name: 'شركة السجانة للتوريدات', icon: '🏭', type: 'مورد' }
        );
    }

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
function highlightSearchMatches(text, query) {
    if (!text || !query) return text || '';
    const textStr = String(text);
    const rawQuery = String(query).trim();
    if (!rawQuery) return textStr;

    function normalizeArabic(s) {
        return String(s || '').toLowerCase()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي');
    }

    const normText = normalizeArabic(textStr);
    const normQuery = normalizeArabic(rawQuery);

    const tokens = normQuery.split(/\s+/).filter(Boolean);
    if (tokens.length === 0) return textStr;

    let matches = [];
    tokens.forEach(tok => {
        let pos = 0;
        while ((pos = normText.indexOf(tok, pos)) !== -1) {
            matches.push({ start: pos, end: pos + tok.length });
            pos += Math.max(1, tok.length);
        }
    });

    if (matches.length === 0) return textStr;

    matches.sort((a, b) => a.start - b.start);
    let merged = [matches[0]];
    for (let i = 1; i < matches.length; i++) {
        let last = merged[merged.length - 1];
        let curr = matches[i];
        if (curr.start <= last.end) {
            last.end = Math.max(last.end, curr.end);
        } else {
            merged.push(curr);
        }
    }

    let result = '';
    let lastIndex = 0;
    merged.forEach(m => {
        result += textStr.substring(lastIndex, m.start);
        const matchText = textStr.substring(m.start, m.end);
        result += `<span class="search-highlight-match" style="color: #ff6600 !important; font-weight: 900 !important; font-size: 1.05em !important; background: transparent !important; box-shadow: none !important; border: none !important;">${matchText}</span>`;
        lastIndex = m.end;
    });
    result += textStr.substring(lastIndex);
    return result;
}

function onAccountSearch() {
    if (!ALL_ACCOUNTS || ALL_ACCOUNTS.length <= 1) {
        buildAccountsList();
    }
    const input = document.getElementById('account-search-input');
    const list = document.getElementById('autocomplete-list');
    if (!input || !list) return;
    
    const q = input.value.trim();
    list.innerHTML = '';
    
    function normStr(s) {
        return String(s || '').toLowerCase()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي');
    }
    
    const normQ = normStr(q);
    const qTokens = normQ.split(/\s+/).filter(Boolean);
    
    let results = ALL_ACCOUNTS.filter(a => {
        if (a.id === 'ALL') return false;
        if (qTokens.length === 0) return true;
        const cleanName = String(a.name || '').replace(/^\[\d+\]\s*/, '').replace(/^\d+\s*[-–]\s*/, '');
        const nameNorm = normStr(cleanName);
        const idNorm = normStr(a.id);
        return qTokens.every(tok => nameNorm.includes(tok) || idNorm.includes(tok));
    });
    
    // Live Bank Transfer Lookup in Autocomplete
    if (q.length >= 3 || /\d/.test(q)) {
        const qDig = q.replace(/\D/g, '');
        const matchingTransfers = [];
        const seenTrs = new Set();
        (ALL_TXNS || []).forEach(t => {
            if (!t || !t.transferNo) return;
            const trStr = String(t.transferNo).trim();
            if (seenTrs.has(trStr)) return;
            let m = trStr.toLowerCase().includes(normQ);
            if (!m && qDig.length >= 4) {
                m = trStr.replace(/\D/g, '').includes(qDig);
            }
            if (m) {
                seenTrs.add(trStr);
                matchingTransfers.push(t);
            }
        });

        matchingTransfers.slice(0, 3).forEach(t => {
            const trLi = document.createElement('li');
            trLi.className = 'transfer-autocomplete-item';
            trLi.style.cssText = 'background: rgba(168,85,247,0.25); border: 1.5px solid #a855f7; border-radius: 8px; margin-bottom: 6px; padding: 10px 14px; cursor: pointer;';
            const benefName = getAccountName(t.counterAccount) || getAccountName(t.account);
            trLi.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; width:100%; gap:8px;">
                    <span style="font-weight:900; font-size:1.15rem; color:#ffd700; display:flex; align-items:center; gap:8px;">
                        <span style="font-size:1.3rem;">💳</span>
                        <span>تحويلة رقم: <strong style="color:#38bdf8; text-decoration:underline;">${t.transferNo}</strong></span>
                        <span style="color:#f8fafc; font-size:0.95rem; font-weight:normal;">(مضافة لـ: <strong style="color:#ffd700;">${benefName}</strong>)</span>
                    </span>
                    <span style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; padding:4px 10px; border-radius:6px; font-size:0.82rem; font-weight:bold;">فحص ومطابقة ⏎</span>
                </div>
            `;
            trLi.onmousedown = (ev) => {
                ev.preventDefault();
                inspectBankTransfer(t.transferNo);
            };
            list.appendChild(trLi);
        });
    }

    if (results.length === 0 && list.children.length === 0) {
        list.innerHTML = '<li class="no-results" style="padding: 14px; color: #f87171; text-align: center; font-size: 1.2rem; font-weight: bold;">لا يوجد حساب مطابق (اضغط Enter للبحث كرقم تحويلة)</li>';
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
        
        const cleanName = String(a.name || '').replace(/^\[\d+\]\s*/, '').replace(/^\d+\s*[-–]\s*/, '');
        const highlightedName = highlightSearchMatches(cleanName, q);

        li.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; width:100%; gap:12px;">
                <span class="acc-name-text" style="font-weight: 800; font-size: 1.25rem; color: #ffd700; display: inline-flex; align-items: center; gap: 8px; text-shadow: 0 0 10px rgba(250, 204, 21, 0.35);">
                    <span style="font-size: 1.25rem;">${a.icon}</span>
                    <span>${highlightedName}</span>
                </span>
                <span style="background:${badgeColor}; color:white; padding:4px 10px; border-radius:6px; font-size:0.85rem; font-weight: bold; min-width: 52px; text-align: center;">${a.type}</span>
            </div>
        `;
        li.onmousedown = () => selectAccount(a.id, cleanName, a.icon);
        list.appendChild(li);
    });
    
    list.style.display = 'block';
}

function onAccountSearchKey(e) {
    if (e.key === 'Escape') {
        const list = document.getElementById('autocomplete-list');
        if (list) list.style.display = 'none';
    } else if (e.key === 'Enter') {
        e.preventDefault();
        e.stopPropagation();
        triggerTransferOrAccountSearch();
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
        const cleanName = String(name || '').replace(/^\[\d+\]\s*/, '').replace(/^\d+\s*[-–]\s*/, '');
        document.getElementById('chip-name').innerText = cleanName;
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
    
    // Close any transfer inquiry card
    if (typeof closeTransferInquiryCard === 'function') closeTransferInquiryCard();

    // Reset Report View
    const tbody = document.getElementById('recent-tbody');
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

/* ══════════════════════════════════════════════════════════
   BANK TRANSFER INSPECTOR & VERIFICATION ENGINE (كشف الحساب)
   ══════════════════════════════════════════════════════════ */
let currentSearchedTransferNo = '';

window.closeTransferInquiryCard = function() {
    const container = document.getElementById('transfer-search-result-container');
    if (container) {
        container.style.display = 'none';
        container.innerHTML = '';
    }
    currentSearchedTransferNo = '';
    document.querySelectorAll('.transfer-matched-row').forEach(tr => tr.classList.remove('transfer-matched-row'));
};

window.openBeneficiaryStatement = function(accId, accName, transferNo) {
    if (!accId) return;
    currentSearchedTransferNo = transferNo || '';
    
    // Select the account in the ledger
    selectAccount(accId, accName, '👤');
    
    // Smoothly scroll down to the table and highlight
    setTimeout(() => {
        highlightTransferRows(transferNo);
        const table = document.getElementById('recent-table');
        if (table) {
            table.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 400);
};

window.filterLedgerByTransfer = function(transferNo) {
    if (!transferNo) return;
    currentSearchedTransferNo = transferNo;
    const smartEl = document.getElementById('filter-smart');
    if (smartEl) {
        smartEl.value = transferNo;
    }
    document.getElementById('filter-account').value = 'ALL';
    const wrap = document.getElementById('account-search-wrap');
    const chip = document.getElementById('selected-chip');
    if (wrap) wrap.style.display = 'flex';
    if (chip) chip.style.display = 'none';
    
    runReport();
    
    setTimeout(() => {
        highlightTransferRows(transferNo);
        const table = document.getElementById('recent-table');
        if (table) table.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 350);
};

window.triggerTransferOrAccountSearch = function(customQuery) {
    const input = document.getElementById('account-search-input');
    const smartInput = document.getElementById('filter-smart');
    const q = (typeof customQuery === 'string' ? customQuery : ((input && input.value) ? input.value : (smartInput ? smartInput.value : ''))).trim();
    if (!q) {
        if (typeof showToast === 'function') showToast('يرجى كتابة رقم التحويلة أو اسم الحساب للبحث', 'info');
        return;
    }
    
    const list = document.getElementById('autocomplete-list');
    if (list) list.style.display = 'none';
    
    inspectBankTransfer(q);
};

window.inspectBankTransfer = function(query) {
    const container = document.getElementById('transfer-search-result-container');
    if (!container) return;
    
    const cleanQ = String(query || '').trim();
    if (!cleanQ) return;
    
    currentSearchedTransferNo = cleanQ;
    const qLower = cleanQ.toLowerCase();
    const qDigits = cleanQ.replace(/\D/g, '');
    
    ALL_TXNS = collectAllTransactions();
    
    function isTransferMatch(val) {
        if (!val) return false;
        const s = String(val).trim().toLowerCase();
        if (s === qLower) return true;
        if (cleanQ.length >= 3 && s.includes(qLower)) return true;
        if (qDigits.length >= 4) {
            const valDigits = s.replace(/\D/g, '');
            if (valDigits && (valDigits === qDigits || valDigits.endsWith(qDigits) || valDigits.includes(qDigits))) return true;
        }
        return false;
    }

    const matchedTxns = [];
    (ALL_TXNS || []).forEach(t => {
        if (!t) return;
        const trNo = t.transferNo || '';
        const chkNo = t.checkNo || '';
        const stmt = t.statement || '';
        const note = t.additionalNote || '';
        
        let matched = false;
        let matchedNo = '';
        
        if (isTransferMatch(trNo)) {
            matched = true;
            matchedNo = trNo;
        } else if (isTransferMatch(chkNo)) {
            matched = true;
            matchedNo = chkNo;
        } else if (cleanQ.length >= 4 && (isTransferMatch(stmt) || isTransferMatch(note))) {
            matched = true;
            matchedNo = cleanQ;
        }
        
        if (matched) {
            matchedTxns.push({ txn: t, matchedTransferNo: matchedNo || cleanQ });
        }
    });

    // Also scan raw source tables directly to ensure zero omission
    const rawMatches = [];
    const getTK = window.getTenantKey || ((k) => k);

    function scanSource(key, typeLabel, typeKey) {
        let list = [];
        try { list = JSON.parse(localStorage.getItem(getTK(key)) || localStorage.getItem(key) || '[]'); } catch(e) {}
        if (!Array.isArray(list)) list = Object.values(list || {});
        list.forEach(v => {
            if (!v || v.deleted) return;
            const refNo = v.voucherNo || v.id || v.invoiceNo || '';
            
            if (Array.isArray(v.bankTransfers)) {
                v.bankTransfers.forEach(bt => {
                    if (isTransferMatch(bt.transferNo || bt.reference)) {
                        rawMatches.push({
                            source: key,
                            voucherRef: refNo,
                            voucherType: typeKey,
                            voucherTypeLabel: typeLabel,
                            transferNo: bt.transferNo || bt.reference,
                            amount: parseNum(bt.amount || v.amount || v.totalAmount),
                            currency: v.currency || 'SDG',
                            date: v.date,
                            time: v.time,
                            bankName: bt.bankName || bt.note || v.bankName || 'البنك',
                            statement: bt.statement || v.description || v.statement || '',
                            raw: v
                        });
                    }
                });
            } else if (isTransferMatch(v.transferNo || v.mobileTransferNo)) {
                rawMatches.push({
                    source: key,
                    voucherRef: refNo,
                    voucherType: typeKey,
                    voucherTypeLabel: typeLabel,
                    transferNo: v.transferNo || v.mobileTransferNo,
                    amount: parseNum(v.amount || v.totalAmount || v.amountLocal),
                    currency: v.currency || 'SDG',
                    date: v.date,
                    time: v.time,
                    bankName: v.bankName || v.mobileNote || 'البنك',
                    statement: v.statement || v.description || '',
                    raw: v
                });
            }
            
            if (Array.isArray(v.items)) {
                v.items.forEach(item => {
                    if (!item) return;
                    if (Array.isArray(item.bankTransfers)) {
                        item.bankTransfers.forEach(bt => {
                            if (isTransferMatch(bt.transferNo || bt.reference)) {
                                rawMatches.push({
                                    source: key,
                                    voucherRef: refNo,
                                    voucherType: typeKey,
                                    voucherTypeLabel: typeLabel,
                                    transferNo: bt.transferNo || bt.reference,
                                    amount: parseNum(bt.amount || item.amount),
                                    currency: v.currency || 'SDG',
                                    date: v.date,
                                    time: v.time,
                                    bankName: bt.bankName || item.bankName || 'البنك',
                                    statement: bt.statement || item.statement || v.description || '',
                                    item: item,
                                    raw: v
                                });
                            }
                        });
                    } else if (isTransferMatch(item.transferNo)) {
                        rawMatches.push({
                            source: key,
                            voucherRef: refNo,
                            voucherType: typeKey,
                            voucherTypeLabel: typeLabel,
                            transferNo: item.transferNo,
                            amount: parseNum(item.amount),
                            currency: v.currency || 'SDG',
                            date: v.date,
                            time: v.time,
                            bankName: item.bankName || 'البنك',
                            statement: item.statement || v.description || '',
                            item: item,
                            raw: v
                        });
                    }
                });
            }
        });
    }

    scanSource('erp_payments_v1', 'سند صرف مجمع', 'multi-payment');
    scanSource('erp_receipts_v1', 'سند قبض مجمع', 'multi-receipt');
    scanSource('erp_single_payments_v1', 'سند صرف نقدية', 'payment');
    scanSource('erp_single_receipts_v1', 'سند قبض نقدية', 'receipt');
    scanSource('erp_sales_invoices_v1', 'فاتورة مبيعات', 'invoice');
    scanSource('erp_purchase_invoices_v1', 'فاتورة مشتريات', 'purchase');

    const operations = [];
    const opKeys = new Set();

    matchedTxns.forEach(({ txn, matchedTransferNo }) => {
        const ref = txn.ref || '';
        const opKey = `${txn.type}_${ref}_${matchedTransferNo}`;
        if (opKeys.has(opKey)) return;
        opKeys.add(opKey);
        
        const legs = (ALL_TXNS || []).filter(x => x && x.ref === ref && (x.transferNo === txn.transferNo || x.type === txn.type));
        
        let beneficiaryId = '';
        let beneficiaryName = '';
        let bankAccountId = '';
        let bankAccountName = txn.bankName || '';
        let directionLabel = '';
        let addedToText = '';
        
        const isPayment = (txn.type === 'payment' || txn.type === 'multi-payment' || txn.type === 'comprehensive-payment');
        const isReceipt = (txn.type === 'receipt' || txn.type === 'multi-receipt' || txn.type === 'comprehensive-receipt');
        const isSales = (txn.type === 'invoice' || txn.type === 'sales');
        const isPurchase = (txn.type === 'purchase');

        if (isPayment) {
            directionLabel = 'سند صرف (تحويل بنكي صادر)';
            const debLeg = legs.find(l => parseNum(l.debit) > 0) || txn;
            const credLeg = legs.find(l => parseNum(l.credit) > 0) || txn;
            beneficiaryId = debLeg.account;
            beneficiaryName = getAccountName(beneficiaryId);
            bankAccountId = credLeg.account;
            if (!bankAccountName) bankAccountName = getAccountName(bankAccountId);
            
            addedToText = `مضافة ومسددة لحساب المستفيد: <strong style="color:#ffd700; font-size:1.4rem;">${beneficiaryName}</strong> <span style="font-size:0.95rem; color:#94a3b8; font-weight:normal;">(مدفوعة من حساب: ${bankAccountName})</span>`;
        } else if (isReceipt) {
            directionLabel = 'سند قبض (تحويل بنكي وارد)';
            const credLeg = legs.find(l => parseNum(l.credit) > 0) || txn;
            const debLeg = legs.find(l => parseNum(l.debit) > 0) || txn;
            beneficiaryId = credLeg.account;
            beneficiaryName = getAccountName(beneficiaryId);
            bankAccountId = debLeg.account;
            if (!bankAccountName) bankAccountName = getAccountName(bankAccountId);
            
            addedToText = `مقيدة لحساب العميل/الدافع: <strong style="color:#ffd700; font-size:1.4rem;">${beneficiaryName}</strong> <span style="font-size:0.95rem; color:#34d399; font-weight:normal;">(تم إيداعها في: ${bankAccountName})</span>`;
        } else if (isSales) {
            directionLabel = 'فاتورة مبيعات';
            beneficiaryId = txn.counterAccount && !txn.counterAccount.startsWith('revenue') ? txn.counterAccount : txn.account;
            beneficiaryName = getAccountName(beneficiaryId);
            if (!bankAccountName) bankAccountName = 'البنك';
            
            addedToText = `مقيدة لحساب العميل: <strong style="color:#ffd700; font-size:1.4rem;">${beneficiaryName}</strong> <span style="font-size:0.95rem; color:#94a3b8; font-weight:normal;">(سداد فاتورة مبيعات #${ref} وأودعت في: ${bankAccountName})</span>`;
        } else if (isPurchase) {
            directionLabel = 'فاتورة مشتريات';
            beneficiaryId = txn.account && !txn.account.startsWith('exp') ? txn.account : txn.counterAccount;
            beneficiaryName = getAccountName(beneficiaryId);
            if (!bankAccountName) bankAccountName = 'البنك';
            
            addedToText = `مضافة ومسددة لحساب المورد: <strong style="color:#ffd700; font-size:1.4rem;">${beneficiaryName}</strong> <span style="font-size:0.95rem; color:#94a3b8; font-weight:normal;">(سداد فاتورة مشتريات #${ref} من: ${bankAccountName})</span>`;
        } else {
            beneficiaryId = txn.account;
            beneficiaryName = getAccountName(beneficiaryId);
            addedToText = `مقيدة في الحساب: <strong style="color:#ffd700; font-size:1.4rem;">${beneficiaryName}</strong> <span style="font-size:0.95rem; color:#94a3b8; font-weight:normal;">(الحساب المقابل: ${getAccountName(txn.counterAccount)})</span>`;
        }

        operations.push({
            transferNo: matchedTransferNo || txn.transferNo || cleanQ,
            voucherRef: ref,
            voucherType: txn.type,
            voucherTypeLabel: txn.typeLabel || directionLabel,
            date: txn.date,
            time: txn.time || '',
            amount: parseNum(txn.debit || txn.credit || 0),
            currency: txn.currency || 'SDG',
            bankName: bankAccountName || 'البنك',
            counterAccountName: getAccountName(txn.counterAccount),
            beneficiaryId: beneficiaryId,
            beneficiaryName: beneficiaryName,
            addedToText: addedToText,
            statement: txn.statement || txn.additionalNote || '',
            txn: txn
        });
    });

    rawMatches.forEach(rm => {
        const opKey = `${rm.voucherType}_${rm.voucherRef}_${rm.transferNo}`;
        if (opKeys.has(opKey)) return;
        opKeys.add(opKey);
        
        let beneficiaryId = '';
        let beneficiaryName = '';
        let addedToText = '';
        
        if (rm.item) {
            beneficiaryId = rm.item.accountId || rm.item.account || rm.item.accountName;
            beneficiaryName = getAccountName(beneficiaryId) || rm.item.accountName || beneficiaryId;
        } else if (rm.raw) {
            if (rm.voucherType === 'multi-payment' || rm.voucherType === 'payment') {
                beneficiaryId = rm.raw.toAccount || rm.raw.accountId || rm.raw.supplierId || rm.raw.customerId;
            } else {
                beneficiaryId = rm.raw.fromAccount || rm.raw.accountId || rm.raw.customerId || rm.raw.supplierId;
            }
            beneficiaryName = getAccountName(beneficiaryId);
        }
        
        if (rm.voucherType === 'multi-payment' || rm.voucherType === 'payment') {
            addedToText = `مضافة ومسددة لحساب المستفيد: <strong style="color:#ffd700; font-size:1.4rem;">${beneficiaryName}</strong> <span style="font-size:0.95rem; color:#94a3b8; font-weight:normal;">(مدفوعة من حساب: ${rm.bankName})</span>`;
        } else {
            addedToText = `مقيدة لحساب العميل/الدافع: <strong style="color:#ffd700; font-size:1.4rem;">${beneficiaryName}</strong> <span style="font-size:0.95rem; color:#34d399; font-weight:normal;">(تم إيداعها في: ${rm.bankName})</span>`;
        }
        
        operations.push({
            transferNo: rm.transferNo,
            voucherRef: rm.voucherRef,
            voucherType: rm.voucherType,
            voucherTypeLabel: rm.voucherTypeLabel,
            date: rm.date,
            time: rm.time || '',
            amount: rm.amount,
            currency: rm.currency,
            bankName: rm.bankName,
            counterAccountName: beneficiaryName,
            beneficiaryId: beneficiaryId,
            beneficiaryName: beneficiaryName,
            addedToText: addedToText,
            statement: rm.statement,
            raw: rm.raw
        });
    });

    if (operations.length === 0) {
        let foundAccount = ALL_ACCOUNTS.find(a => {
            if (!a || a.id === 'ALL') return false;
            const cleanName = String(a.name || '').replace(/^\[\d+\]\s*/, '').replace(/^\d+\s*[-–]\s*/, '').trim().toLowerCase();
            return cleanName === qLower || a.id.toLowerCase() === qLower;
        });

        if (foundAccount) {
            container.style.display = 'none';
            selectAccount(foundAccount.id, foundAccount.name, foundAccount.icon);
            return;
        }

        renderTransferNotFoundCard(cleanQ);
        return;
    }

    renderTransferSuccessCard(cleanQ, operations);
};

function renderTransferSuccessCard(query, operations) {
    const container = document.getElementById('transfer-search-result-container');
    if (!container) return;

    const isMultiple = operations.length > 1;
    
    let html = `
    <div class="transfer-inquiry-card">
        <!-- Top Status Bar -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; border-bottom:1px solid rgba(255,255,255,0.12); padding-bottom:10px; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:2.2rem; filter:drop-shadow(0 0 8px rgba(52,211,153,0.6));">✅</span>
                <div>
                    <div style="font-size:1.25rem; font-weight:900; color:#34d399; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span>تم العثور على التحويلة بنجاح ومطابقتها في النظام</span>
                        ${isMultiple ? `<span style="background:#f59e0b; color:#000; font-size:0.75rem; padding:2px 8px; border-radius:10px; font-weight:900;">⚠️ ${operations.length} عمليات مطابقة بنفس الرقم</span>` : ''}
                    </div>
                    <div style="font-size:0.85rem; color:#94a3b8; margin-top:2px;">
                        بيانات التحويلة البنكية المؤكدة ومطابقة الطرف المستفيد في كشف الحساب
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeTransferInquiryCard()" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.35); color:#f87171; border-radius:8px; padding:6px 14px; cursor:pointer; font-weight:bold; font-size:0.9rem; transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.3)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'">
                ✕ إغلاق النتيجة
            </button>
        </div>
    `;

    operations.forEach((op, idx) => {
        html += `
        <div style="background: rgba(15, 23, 42, 0.65); border: 1.5px solid ${idx === 0 ? '#eab308' : 'rgba(255,255,255,0.15)'}; border-radius: 14px; padding: 16px 20px; margin-bottom: 14px; box-shadow: 0 6px 20px rgba(0,0,0,0.35);">
            ${isMultiple ? `<div style="font-size:0.88rem; font-weight:bold; color:#38bdf8; margin-bottom:8px;">📌 العملية رقم (${idx + 1}):</div>` : ''}
            
            <!-- Crucial Section: أضيفت لمن؟ -->
            <div style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(0, 0, 0, 0.4)); border: 2px solid #eab308; border-radius: 12px; padding: 14px 18px; margin-bottom: 14px; box-shadow: 0 4px 15px rgba(234,179,8,0.2);">
                <div style="font-size: 0.88rem; font-weight: 800; color: #cbd5e1; margin-bottom: 6px;">👤 أضيفت لمن؟ (الحساب المستفيد / المسجل لصالحه):</div>
                <div style="font-size: 1.35rem; font-weight: 900; color: #ffd700; text-shadow: 0 0 10px rgba(250,204,21,0.4);">
                    ${op.addedToText}
                </div>
            </div>

            <!-- Details Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin-bottom: 12px;">
                <div class="transfer-info-box">
                    <span class="info-label">🔢 رقم التحويلة:</span>
                    <span class="info-val" style="color:#ffd700; font-family:monospace; letter-spacing:1px;">${op.transferNo}</span>
                </div>
                <div class="transfer-info-box">
                    <span class="info-label">💰 المبلغ:</span>
                    <span class="info-val" style="color:#34d399; font-family:monospace;">${fmtNum(op.amount)} ${op.currency}</span>
                </div>
                <div class="transfer-info-box">
                    <span class="info-label">📅 تاريخ ووقت الحركة:</span>
                    <span class="info-val">${op.date} ${op.time ? '<small style="color:#94a3b8;">(' + op.time + ')</small>' : ''}</span>
                </div>
                <div class="transfer-info-box">
                    <span class="info-label">📑 نوع ورقم السند:</span>
                    <span class="info-val" style="color:#38bdf8;">${op.voucherTypeLabel} #${op.voucherRef}</span>
                </div>
                <div class="transfer-info-box">
                    <span class="info-label">🏦 البنك المستخدم:</span>
                    <span class="info-val" style="color:#e879f9;">${op.bankName || 'البنك'}</span>
                </div>
            </div>

            <!-- Notes -->
            <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 8px 14px; margin-bottom: 14px;">
                <span style="font-size: 0.82rem; font-weight: bold; color: #94a3b8;">📝 البيان المسجل بالسند: </span>
                <span style="font-size: 0.92rem; font-weight: 700; color: #f8fafc;">${op.statement || 'لا يوجد بيان إضافي'}</span>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                ${op.beneficiaryId ? `
                <button type="button" class="btn-run" onclick="openBeneficiaryStatement('${op.beneficiaryId}', '${op.beneficiaryName}', '${op.transferNo}')" style="background: linear-gradient(135deg, #10b981, #059669); font-size:0.9rem; height:38px; box-shadow: 0 4px 14px rgba(16,185,129,0.4);">
                    📋 فتح كشف حساب الطرف (${op.beneficiaryName})
                </button>
                ` : ''}
                <button type="button" class="btn-run" onclick="navigateToOriginalVoucher('${op.voucherType}', '${op.voucherRef}')" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); font-size:0.9rem; height:38px; box-shadow: 0 4px 14px rgba(59,130,246,0.4);">
                    🔗 الذهاب إلى أصل السند #${op.voucherRef}
                </button>
                <button type="button" class="btn-action-secondary" onclick="filterLedgerByTransfer('${op.transferNo}')" style="font-size:0.88rem; height:38px;">
                    🔍 إظهار قيود هذه التحويلة في الجدول
                </button>
            </div>
        </div>
        `;
    });

    html += `</div>`;
    container.innerHTML = html;
    container.style.display = 'block';

    highlightTransferRows(query);
}

function renderTransferNotFoundCard(query) {
    const container = document.getElementById('transfer-search-result-container');
    if (!container) return;

    container.innerHTML = `
    <div class="transfer-inquiry-card" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(15, 23, 42, 0.96)); border-color: #ef4444; box-shadow: 0 10px 30px rgba(239,68,68,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:2.4rem;">❌</span>
                <div>
                    <div style="font-size:1.22rem; font-weight:900; color:#f87171;">
                        لم يتم العثور على أي تحويلة بنكية برقم: <span style="color:#ffd700; font-family:monospace; background:rgba(0,0,0,0.4); padding:2px 10px; border-radius:6px; border:1px solid rgba(250,204,21,0.3);">${query}</span>
                    </div>
                    <div style="font-size:0.88rem; color:#cbd5e1; margin-top:4px;">
                        تم فحص جميع سندات القبض والصرف، فواتير المبيعات، فواتير المشتريات، والقيود اليومية.
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeTransferInquiryCard()" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; border-radius:8px; padding:6px 14px; cursor:pointer; font-weight:bold;">
                ✕ إغلاق
            </button>
        </div>
        <div style="background:rgba(0,0,0,0.35); border-radius:10px; padding:12px 18px; border-right:4px solid #ef4444; color:#fca5a5; font-size:0.95rem; font-weight:700;">
            ⚠️ <strong>النتيجة المؤكدة:</strong> هذه التحويلة <strong>غير مضافة وغير مسجلة بالنظام نهائياً</strong>، ولم يتم تقييدها في حساب أي عميل أو مورد أو حساب بنكي.
        </div>
    </div>
    `;
    container.style.display = 'block';
}

function highlightTransferRows(transferNo) {
    if (!transferNo) return;
    const cleanNo = String(transferNo).trim().toLowerCase();
    const cleanDigits = cleanNo.replace(/\D/g, '');
    
    const table = document.getElementById('recent-table');
    if (!table) return;
    
    let firstMatchedRow = null;
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(tr => {
        const tdTr = tr.querySelector('td[data-col-id="transferNo"]');
        const text = (tdTr ? tdTr.textContent : tr.textContent).trim().toLowerCase();
        let isMatch = text.includes(cleanNo);
        if (!isMatch && cleanDigits.length >= 4) {
            isMatch = text.replace(/\D/g, '').includes(cleanDigits);
        }
        if (isMatch) {
            tr.classList.add('transfer-matched-row');
            if (!firstMatchedRow) firstMatchedRow = tr;
        } else {
            tr.classList.remove('transfer-matched-row');
        }
    });
    
    if (firstMatchedRow) {
        firstMatchedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.renderLedgerPresets === 'function') window.renderLedgerPresets();
    const input = document.getElementById('account-search-input');
    if (input) {
        input.addEventListener('focus', onAccountSearch);
        input.addEventListener('click', onAccountSearch);
        input.addEventListener('input', onAccountSearch);
        input.addEventListener('keydown', onAccountSearchKey);
    }
    const smartInput = document.getElementById('filter-smart');
    if (smartInput) {
        smartInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                triggerTransferOrAccountSearch(smartInput.value);
            }
        });
    }
});

function collectAllTransactions() {
    // Always rebuild GL from source tables to ensure fresh data
    if (typeof window.rebuildGLFromAllTables === 'function') {
        try {
            window.rebuildGLFromAllTables(false);
        } catch(e) {
            console.warn('GL rebuild error:', e);
        }
    }
    const getTenantKey = window.getTenantKey || ((k) => k);
    let txns = [];
    try {
        txns = JSON.parse(localStorage.getItem(getTenantKey('erp_gl_v1')) || localStorage.getItem('erp_gl_v1') || '[]');
    } catch(e) {
        txns = [];
    }
    return Array.isArray(txns) ? txns : Object.values(txns || {});
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
    
    const debitEl = document.getElementById('launcher-total-debit');
    const debitFEl = document.getElementById('launcher-total-debit-foreign');
    const creditEl = document.getElementById('launcher-total-credit');
    const creditFEl = document.getElementById('launcher-total-credit-foreign');
    const balanceEl = document.getElementById('launcher-total-balance');
    const balanceFEl = document.getElementById('launcher-total-balance-foreign');
    
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
    const tbody = document.getElementById('recent-tbody');
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
    
    const originalTable = document.querySelector('#recent-table');
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
    if (window.location.pathname.includes('preview-ledger')) return;
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
            const selectedAccName = (typeof getAccountName === 'function') ? getAccountName(accVal) : '';
            filtered = filtered.filter(t => {
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

        // Global Overview Deduplication: When viewing ALL accounts, show each voucher once (primary side)
        if (accVal === 'ALL') {
            const seenVouchers = new Set();
            filtered = filtered.filter(t => {
                if (!t) return false;
                const refKey = (t.type || '') + '_' + (t.ref || '') + '_' + (t.currency || '') + '_' + (t.transferNo || t.checkNo || '');
                if (t.ref && t.ref !== '-' && t.type !== 'jv') {
                    if (seenVouchers.has(refKey)) return false;
                    seenVouchers.add(refKey);
                }
                return true;
            });
        }

        // Type Filter
        if (typeFilter !== 'ALL') {
            filtered = filtered.filter(t => {
                if (!t) return false;
                if (typeFilter === 'sales' && t.type === 'invoice' && t.account !== 'revenue-sales') return true;
                if (typeFilter === 'purchases' && t.type === 'purchase' && t.account !== 'cost-purchases') return true;
                if (typeFilter === 'receipt' && (t.type === 'receipt' || t.type === 'multi-receipt')) return true;
                if (typeFilter === 'payment' && (t.type === 'payment' || t.type === 'multi-payment')) return true;
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
        let entityDate = '';

        // Read system exchange rate from ALL possible sources
        let systemUsdRate = 1;
        
        // Source 1: getExchangeRateForCurrency (centralized)
        if (typeof window.getExchangeRateForCurrency === 'function') {
            const r = window.getExchangeRateForCurrency('USD');
            if (r && r > 1) systemUsdRate = parseFloat(r);
        }
        
        // Source 2: ledgerExchangeRate (set by initLedgerCurrencySettings)
        if (systemUsdRate <= 1 && typeof ledgerExchangeRate !== 'undefined' && ledgerExchangeRate > 1) {
            systemUsdRate = ledgerExchangeRate;
        }
        
        // Source 3: window._gemInitialRate (set by app.js on load)
        if (systemUsdRate <= 1 && window._gemInitialRate && parseFloat(window._gemInitialRate) > 1) {
            systemUsdRate = parseFloat(window._gemInitialRate);
        }
        
        // Source 4: erp_settings_v1.rateUSD
        if (systemUsdRate <= 1) {
            try {
                const settings = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (settings.rateUSD && parseFloat(settings.rateUSD) > 1) {
                    systemUsdRate = parseFloat(settings.rateUSD);
                }
            } catch(e){}
        }
        
        // Source 5: erp_rate_usd
        if (systemUsdRate <= 1) {
            try {
                const dedicated = localStorage.getItem('erp_rate_usd');
                if (dedicated && parseFloat(dedicated) > 1) systemUsdRate = parseFloat(dedicated);
            } catch(e){}
        }
        
        // Source 6: erp_cached_global_rate
        if (systemUsdRate <= 1) {
            try {
                const cached = localStorage.getItem('erp_cached_global_rate');
                if (cached && parseFloat(cached) > 1) systemUsdRate = parseFloat(cached);
            } catch(e){}
        }
        
        // Source 7: erp_currency_rates_v1
        if (systemUsdRate <= 1) {
            try {
                const rates = JSON.parse(localStorage.getItem('erp_currency_rates_v1') || '{}');
                if (rates['USD'] && parseFloat(rates['USD']) > 1) systemUsdRate = parseFloat(rates['USD']);
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
                let rawDate = String(masterRec.date || masterRec.createdAt || masterRec.created_at || masterRec.timestamp || masterRec.addedDate || '').trim();
                if (rawDate.includes('T')) rawDate = rawDate.split('T')[0];
                if (rawDate.includes(' ')) rawDate = rawDate.split(' ')[0];
                entityDate = rawDate;

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
                // Read account-specific rate (only meaningful for foreign currency accounts)
                const savedAccountRate = parseNum(masterRec.exchangeRate || masterRec.exRate || masterRec.rate || 0);
                if (savedAccountRate > 1) {
                    entityExRate = savedAccountRate;
                }
            }
        }

        // Resolve final exchange rate based on currency type
        if (entityCurrency === 'SDG') {
            // SDG account: always use system USD rate for the foreign currency column
            entityExRate = systemUsdRate > 1 ? systemUsdRate : 1;
        } else if (!entityExRate || entityExRate <= 1) {
            // Foreign currency account without a valid rate: resolve from system
            if (typeof window.getExchangeRateForCurrency === 'function') {
                const specificRate = window.getExchangeRateForCurrency(entityCurrency);
                entityExRate = (specificRate && specificRate > 1) ? parseFloat(specificRate) : (systemUsdRate > 1 ? systemUsdRate : 1);
            } else {
                entityExRate = systemUsdRate > 1 ? systemUsdRate : 1;
            }
        }

        let opDebitLoc = intrinsicBal > 0 ? intrinsicBal : 0;
        let opCreditLoc = intrinsicBal < 0 ? Math.abs(intrinsicBal) : 0;

        let opDebitFc = entityExRate > 1 ? (opDebitLoc / entityExRate) : 0;
        let opCreditFc = entityExRate > 1 ? (opCreditLoc / entityExRate) : 0;
        intrinsicBalFc = entityExRate > 1 ? (intrinsicBal / entityExRate) : 0;

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
            let opDate = entityDate || (chronological.length > 0 ? chronological[0].date : new Date().toISOString().split('T')[0]);
            chronological.unshift({
                date: opDate, time: '', ref: 'OPENING', type: 'opening',
                typeLabel: 'رصيد افتتاحي', account: accVal, counterAccount: '-',
                statement: 'الرصيد الافتتاحي',
                transferNo: 'رصيد افتتاحي',
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

            if (debLoc > 0 && (df === 0 || df === debLoc) && rate > 0) {
                df = debLoc / rate;
            }
            if (credLoc > 0 && (cf === 0 || cf === credLoc) && rate > 0) {
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
                
                if (t.balance !== undefined && t.balanceForeign !== undefined) {
                    if (t.balance < 0) {
                        t.balanceForeign = -Math.abs(t.balanceForeign);
                    } else if (t.balance > 0) {
                        t.balanceForeign = Math.abs(t.balanceForeign);
                    }
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
        let totalDebitFc = 0;
        let totalCreditFc = 0;
        let openingDebit = 0;
        let openingCredit = 0;
        let openingDebitFc = 0;
        let openingCreditFc = 0;

        currentTxns.forEach(t => {
            if (!t) return;
            if (t.isOpening) {
                openingDebit += (t.debit || 0);
                openingCredit += (t.credit || 0);
                openingDebitFc += (t.debitForeign || 0);
                openingCreditFc += (t.creditForeign || 0);
                return;
            }
            totalDebit += (t.debit || 0);
            totalCredit += (t.credit || 0);
            totalDebitFc += (t.debitForeign || 0);
            totalCreditFc += (t.creditForeign || 0);
        });

        const tdDebit = document.getElementById('launcher-total-debit');
        const tdCredit = document.getElementById('launcher-total-credit');
        const tdBalance = document.getElementById('launcher-total-balance');

        const tdDebitFc = document.getElementById('launcher-total-debit-foreign');
        const tdCreditFc = document.getElementById('launcher-total-credit-foreign');
        const tdBalanceFc = document.getElementById('launcher-total-balance-foreign');

        if (tdDebit) tdDebit.innerText = typeof fmtNum === 'function' ? fmtNum(totalDebit) : totalDebit.toLocaleString();
        if (tdCredit) tdCredit.innerText = typeof fmtNum === 'function' ? fmtNum(totalCredit) : totalCredit.toLocaleString();
        
        if (tdDebitFc) tdDebitFc.innerText = typeof fmtNum === 'function' ? fmtNum(totalDebitFc) : totalDebitFc.toLocaleString();
        if (tdCreditFc) tdCreditFc.innerText = typeof fmtNum === 'function' ? fmtNum(totalCreditFc) : totalCreditFc.toLocaleString();

        if (tdBalance) {
            let bal = (totalDebit + openingDebit) - (totalCredit + openingCredit);
            tdBalance.innerText = typeof fmtNum === 'function' ? fmtNum(bal) : bal.toLocaleString();
        }
        
        if (tdBalanceFc) {
            let bal = (totalDebit + openingDebit) - (totalCredit + openingCredit);
            let balFc = (totalDebitFc + openingDebitFc) - (totalCreditFc + openingCreditFc);
            if (bal < 0) {
                balFc = -Math.abs(balFc);
            } else if (bal > 0) {
                balFc = Math.abs(balFc);
            }
            tdBalanceFc.innerText = typeof fmtNum === 'function' ? fmtNum(balFc) : balFc.toLocaleString();
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

/* /* ═══════════════════════════════════════════════════════
   Smart General Ledger (SGL) Column Customizer & Drilldown
   /* ═══════════════════════════════════════════════════════ */

const DEFAULT_GL_COLUMNS_CONFIG = [
    { id: 'seq', label: 'م', isNum: false, align: 'center', width: '40px' },
    { id: 'date', label: 'التاريخ', isNum: false, align: 'center', width: '90px' },
    { id: 'ref', label: 'الرقم المرجعي', isNum: false, align: 'center', width: '90px' },
    { id: 'account', label: 'الحساب', isNum: false, align: 'right', width: '140px' },
    { id: 'typeLabel', label: 'نوع الحركة', isNum: false, align: 'center', width: '90px' },
    { id: 'statement', label: 'البيان', isNum: false, align: 'right', width: 'auto' },
    { id: 'transferNo', label: 'رقم التحويلة', isNum: false, align: 'center', width: '100px' },
    { id: 'bankName', label: 'اسم البنك', isNum: false, align: 'center', width: '120px' },
    { id: 'counterAccount', label: 'الحساب المقابل', isNum: false, align: 'right', width: '140px' },
    { id: 'debit', label: 'مدين (عليه)', isNum: true, align: 'right', width: '90px', numColor: '#34d399' },
    { id: 'credit', label: 'دائن (له)', isNum: true, align: 'right', width: '90px', numColor: '#f87171' },
    { id: 'balance', label: 'الرصيد المحلي', isNum: true, align: 'right', width: '100px' },
    { id: 'exchangeRate', label: 'سعر الصرف', isNum: true, align: 'center', width: '90px', numColor: '#eab308' },
    { id: 'debitForeign', label: 'مدين (عليه) أجنبي', isNum: true, align: 'right', width: '90px', numColor: '#34d399' },
    { id: 'creditForeign', label: 'دائن (له) أجنبي', isNum: true, align: 'right', width: '90px', numColor: '#f87171' },
    { id: 'balanceForeign', label: 'الرصيد الأجنبي', isNum: true, align: 'right', width: '120px' }
];

let activeGLColumnSettings = loadGLColumnSettings();

function loadGLColumnSettings() {
    try {
        const stored = localStorage.getItem('gl_column_settings_v5');
        if (stored) {
            let parsed = JSON.parse(stored);
            if (Array.isArray(parsed) && parsed.length > 0) {
                // Ensure any newly added default columns (like transferNo) are included
                DEFAULT_GL_COLUMNS_CONFIG.forEach(defaultCol => {
                    const existing = parsed.find(c => c.id === defaultCol.id);
                    if (!existing) {
                        parsed.push(JSON.parse(JSON.stringify(defaultCol)));
                    } else {
                        existing.label = defaultCol.label;
                    }
                });
                return parsed;
            }
        }
    } catch(e){}
    return JSON.parse(JSON.stringify(DEFAULT_GL_COLUMNS_CONFIG));
}

function saveGLColumnSettings() {
    try {
        localStorage.setItem('gl_column_settings_v5', JSON.stringify(activeGLColumnSettings));
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
        headHtml += `<th draggable="true" data-col-id="${col.id}" ondragstart="window.glHandleDragStart(event)" ondragover="window.glHandleDragOver(event)" ondragenter="window.glHandleDragEnter(event)" ondragleave="window.glHandleDragLeave(event)" ondrop="window.glHandleDrop(event)" ondragend="window.glHandleDragEnd(event)" style="position: relative; white-space: nowrap; cursor: grab; padding-top: 20px; ${hStyle}"><div style="position:absolute; top:2px; left:0; right:0; display:flex; justify-content:space-between; padding:0 5px; font-size:11px; opacity:0.5; z-index:10;"><span style="cursor:pointer; padding:2px;" onclick="window.glHandleArrowMove(event, '${col.id}', 1)" title=" ">?</span><span style="cursor:pointer; padding:2px;" onclick="window.glHandleArrowMove(event, '${col.id}', -1)" title=" ">?</span></div>${col.label}<div class="resizer" title="Double click to auto-size"></div></th>`;
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

        let isMatchedTransfer = false;
        if (currentSearchedTransferNo) {
            const qStr = String(currentSearchedTransferNo).trim().toLowerCase();
            const qDig = qStr.replace(/\D/g, '');
            const trStr = String(t.transferNo || '').trim().toLowerCase();
            const chkStr = String(t.checkNo || '').trim().toLowerCase();
            if (trStr && (trStr === qStr || trStr.includes(qStr))) isMatchedTransfer = true;
            else if (qDig.length >= 4 && trStr.replace(/\D/g, '').includes(qDig)) isMatchedTransfer = true;
            else if (chkStr && (chkStr === qStr || chkStr.includes(qStr))) isMatchedTransfer = true;
            else if (qDig.length >= 4 && chkStr.replace(/\D/g, '').includes(qDig)) isMatchedTransfer = true;
        }
        let trClass = isMatchedTransfer ? 'transfer-matched-row' : '';

        html += `<tr class="${trClass}" style="${trStyle}" data-txn-type="${t.type || ''}" data-txn-ref="${t.ref || ''}" data-row-idx="${i}" title="انقر كليك يمين للذهاب إلى أصل السند أو تكبير الأرقام">`;
        
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
                html += `<td data-col-id="seq" data-is-num="false" data-txn-type="${t.type || ''}" data-txn-ref="${t.ref || ''}" style="${cStyle}">${startIndex + i + 1}</td>`;
            } else if (col.id === 'date') {
                let displayDate = String(t.date || '-').trim();
                if (displayDate.includes('T')) displayDate = displayDate.split('T')[0];
                if (displayDate.includes(' ')) displayDate = displayDate.split(' ')[0];
                html += `<td data-col-id="date" data-is-num="false" data-txn-type="${t.type || ''}" data-txn-ref="${t.ref || ''}" title="${t.time || ''}" style="${cStyle}">${displayDate}</td>`;
            } else if (col.id === 'ref') {
                  let displayRef = t.ref ? String(t.ref).replace(/-(PUR|SAL)-PAID.*|-PAID.*|-BT\d+|-CH\d+$/i, '').trim() : '-';
                  html += `<td data-col-id="ref" data-is-num="true" data-txn-type="${t.type || ''}" data-txn-ref="${t.ref || ''}" style="${cStyle}">${t.isOpening ? '<span style="color:#3b82f6; font-weight:900;">الرصيد الافتتاحي</span>' : '<strong style="color:#2563eb; text-decoration:underline; cursor:pointer;" onclick="navigateToOriginalVoucher(\'' + (t.type || '') + '\', \'' + (t.ref || '') + '\')" title="انقر لفتح أصل السند">' + displayRef + '</strong>'}</td>`;
            } else if (col.id === 'account') {
                html += `<td data-col-id="account" data-is-num="false" data-txn-type="${t.type || ''}" data-txn-ref="${t.ref || ''}" style="${cStyle}"><span style="font-weight:700;">${mainAccName}</span></td>`;
            } else if (col.id === 'typeLabel') {
                html += `<td data-col-id="typeLabel" data-is-num="false" data-txn-type="${t.type || ''}" data-txn-ref="${t.ref || ''}" style="${cStyle}"><span style="background:${t.isOpening ? 'rgba(59,130,246,0.15)' : 'rgba(168,85,247,0.15)'}; color:${t.isOpening ? '#3b82f6' : '#c084fc'}; padding:2px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;">${t.typeLabel || '-'}</span></td>`;
            } else if (col.id === 'statement') {
                let stmtText = (t.statement === 'undefined' ? '-' : t.statement) || '-';
                stmtText = stmtText.replace(/(?:رقم\s*الشيك|شيك\s*رقم|الشيك\s*رقم|رقم\s*الشيط|الشيك|شيك|شيط)\s*(\d+)/gi, 'الشيك $1');
                
                let addNote = (!t.isOpening && t.additionalNote && t.additionalNote !== t.statement && t.additionalNote !== 'undefined') ? t.additionalNote : '';
                if (addNote) {
                    addNote = addNote.replace(/(?:رقم\s*الشيك|شيك\s*رقم|الشيك\s*رقم|رقم\s*الشيط|الشيك|شيك|شيط)\s*(\d+)/gi, 'الشيك $1');
                }

                html += `<td data-col-id="statement" data-is-num="false" style="white-space:normal; min-width:180px; ${cStyle} ${t.isOpening ? 'font-weight:bold; color:#3b82f6;' : ''}">
                    ${stmtText}
                    ${addNote ? '<br><small style="color:#94a3b8;">' + addNote + '</small>' : ''}
                </td>`;
            } else if (col.id === 'transferNo') {
                let disp = '';
                if (t.isOpening) {
                    disp = 'رصيد افتتاحي';
                } else if (t.checkNo) {
                    let cleanNo = String(t.checkNo).replace(/^(?:الشيك|شيك|شيط|رقم\s*الشيك|رقم\s*الشيط|شيك\s*رقم|الشيك\s*رقم|رقم)\s*/gi, '').trim();
                    disp = cleanNo ? ('الشيك ' + cleanNo) : 'الشيك';
                } else if (t.transferNo) {
                    disp = String(t.transferNo).trim();
                    if (/شيك|شيط|رقم\s*الشيك|رقم\s*الشيط|شيك\s*رقم|الشيك/i.test(disp)) {
                        disp = disp.replace(/(?:رقم\s*الشيط|رقم\s*الشيك|شيك\s*رقم|الشيك\s*رقم|الشيك|شيك|شيط)\s*(\d+)/gi, 'الشيك $1');
                    }
                } else if (t.statement && /شيك|شيط|الشيك/i.test(t.statement)) {
                    const chkMatch = String(t.statement).match(/(?:الشيك|شيك|شيط|رقم\s*الشيك|رقم\s*الشيط|شيك\s*رقم|الشيك\s*رقم)\s*(\d+)/i);
                    if (chkMatch && chkMatch[1]) {
                        disp = 'الشيك ' + chkMatch[1];
                    }
                }

                // Final Clean: Strictly format any "رقم الشيك 5" or "شيك رقم 5" or "شيك 5" to "الشيك 5"
                if (disp) {
                    disp = disp.replace(/(?:رقم\s*الشيك|شيك\s*رقم|الشيك\s*رقم|رقم\s*الشيط|الشيك|شيك|شيط)\s*(\d+)/gi, 'الشيك $1');
                }

                html += `<td data-col-id="transferNo" data-is-num="false" style="${cStyle} font-weight:bold; color:#38bdf8;">${disp || '-'}</td>`;
            } else if (col.id === 'bankName') {
                html += `<td data-col-id="bankName" data-is-num="false" style="${cStyle} color:#e879f9; font-weight:bold;">${t.bankName || '-'}</td>`;
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
    
    // Initialize resizers after table is fully rendered
    if (typeof initGeneralLedgerResizers === 'function') {
        initGeneralLedgerResizers();
    }
}

/* ── Navigate to Original Voucher ── */
window.navigateToOriginalVoucher = function(type, ref) {
    if (!ref || ref === '-' || ref === 'OPENING' || ref === 'الرصيد الافتتاحي' || ref === 'رصيد افتتاحي') {
        if (typeof showToast === 'function') {
            showToast('لا يوجد سند مرتبط بهذا السطر (رصيد افتتاحي)', 'warning');
        } else {
            alert('لا يوجد سند مرتبط بهذا السطر');
        }
        return;
    }
    
    let strRef = String(ref).trim();
    let isPurchasePayment = strRef.includes('-PUR-PAID');
    let isSalesPayment = strRef.includes('-SAL-PAID');
    
    // Clean all types of payment suffixes
    let cleanRef = strRef.replace(/-(PUR|SAL)-PAID.*|-PAID.*|-BT\d+|-CH\d+$/i, '').trim();
    let targetUrl = '';
    type = String(type || '').toLowerCase().trim();

    if (isPurchasePayment) {
        targetUrl = `purchase.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (isSalesPayment) {
        targetUrl = `direct-sales.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'sales' || type === 'invoice' || type === 'direct-sales' || type === 'sales-invoice' || cleanRef.startsWith('INV-') || cleanRef.startsWith('SAL-') || cleanRef.startsWith('DS-')) {
        targetUrl = `direct-sales.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'sales-return' || type === 'sales_return' || cleanRef.startsWith('RET-') || cleanRef.startsWith('SR-')) {
        targetUrl = `sales-return.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'purchase' || type === 'purchases' || type === 'purchase-invoice' || cleanRef.startsWith('PUR-') || cleanRef.startsWith('PINV-')) {
        targetUrl = `purchase.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'purchase-return' || type === 'purchase_return' || cleanRef.startsWith('PRET-') || cleanRef.startsWith('PR-')) {
        targetUrl = `purchase-return.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'receipt' || type === 'single-receipt' || type === 'single_receipt' || type === 'single-cash-receipt' || cleanRef.startsWith('REC-') || cleanRef.startsWith('SREC-') || cleanRef.startsWith('RV-')) {
        targetUrl = `single-cash-payment.php?type=receipt&id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'payment' || type === 'single-payment' || type === 'single_payment' || type === 'single-cash-payment' || cleanRef.startsWith('PAY-') || cleanRef.startsWith('SPAY-') || cleanRef.startsWith('PV-')) {
        targetUrl = `single-cash-payment.php?type=payment&id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'multi-receipt' || type === 'multi_receipt' || cleanRef.startsWith('MREC-')) {
        targetUrl = `multi-cash-payment.php?type=receipt&id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'multi-payment' || type === 'multi_payment' || cleanRef.startsWith('MPAY-')) {
        targetUrl = `multi-cash-payment.php?type=payment&id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'comprehensive-receipt') {
        targetUrl = `comprehensive-receipt.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'comprehensive-payment') {
        targetUrl = `comprehensive-payment.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'jv' || type === 'journal' || type === 'adjustment' || type === 'journal-voucher' || cleanRef.startsWith('JV-') || cleanRef.startsWith('JE-')) {
        targetUrl = `journal-voucher.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'bank-transfer' || type === 'transfer' || cleanRef.startsWith('TR-') || cleanRef.startsWith('BT-')) {
        targetUrl = `bank-transfer.php?id=${encodeURIComponent(cleanRef)}`;
    } else if (type === 'check' || type === 'cheque' || cleanRef.startsWith('CHK-') || cleanRef.startsWith('CHQ-')) {
        targetUrl = `checks-cycle.php?id=${encodeURIComponent(cleanRef)}`;
    } else {
        if (cleanRef.startsWith('INV') || cleanRef.startsWith('SAL')) targetUrl = `direct-sales.php?id=${encodeURIComponent(cleanRef)}`;
        else if (cleanRef.startsWith('PUR')) targetUrl = `purchase.php?id=${encodeURIComponent(cleanRef)}`;
        else if (cleanRef.startsWith('JV')) targetUrl = `journal-voucher.php?id=${encodeURIComponent(cleanRef)}`;
        else if (cleanRef.startsWith('REC')) targetUrl = `single-cash-payment.php?type=receipt&id=${encodeURIComponent(cleanRef)}`;
        else targetUrl = `single-cash-payment.php?id=${encodeURIComponent(cleanRef)}`;
    }

    if (targetUrl) {
        if (typeof showToast === 'function') {
            showToast(`🚀 جاري فتح أصل السند: ${cleanRef}`, 'info', 3000);
        }
        window.open(targetUrl, '_blank');
    }
};

/* ── Single Click Number Magnification & Global Click Handler ── */
document.addEventListener('click', function(e) {
    // Hide GL Context Menu when clicking outside
    const menu = document.getElementById('gl-context-menu');
    if (menu && menu.style.display !== 'none' && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }

    const td = e.target.closest('#recent-table td, #preview-table td, .pos-grid td, .excel-table td');
    if (!td) return;
    const isNum = td.dataset.isNum === 'true' || td.classList.contains('num-cell') || /^[-+]?[\d,\.]+\%?$/.test(td.innerText.trim());
    if (isNum) {
        td.classList.toggle('num-magnified');
    }
});

/* ── Split Right-Click Logic (TH: Customizer, TD/TR: Source Voucher Menu) ── */
let currentCtxColId = null;
let currentCtxTxn = null;
let currentCtxTd = null;

document.addEventListener('contextmenu', function(e) {
    const table = e.target.closest('#recent-table, #preview-table, .pos-grid, .excel-table, table');
    if (!table) return;

    const tr = e.target.closest('tr');
    const th = e.target.closest('th');
    const td = e.target.closest('td');
    if (!tr && !th && !td) return;

    e.preventDefault();

    currentCtxTd = td;
    currentCtxColId = th ? th.dataset.colId : (td ? td.dataset.colId : 'ref');

    // 1. IF Right-Clicking on Header Row (th in top row): Open Customizer Window!
    if (th) {
        const menu = document.getElementById('gl-context-menu');
        if (menu) menu.style.display = 'none';
        if (currentCtxColId && typeof openGLColumnModal === 'function') openGLColumnModal(currentCtxColId);
        return;
    }

    // 2. IF Right-Clicking on Data Cell / Number / Middle of line (td/tr): Open Source Voucher Menu!
    if (typeof closeGLColumnModal === 'function') closeGLColumnModal();

    let txnType = (tr ? (tr.dataset.txnType || tr.dataset.type) : '') || (td ? (td.dataset.txnType || td.dataset.type) : '') || '';
    let txnRef = (tr ? (tr.dataset.txnRef || tr.dataset.ref) : '') || (td ? (td.dataset.txnRef || td.dataset.ref) : '') || '';

    // If not found from dataset, try to extract from ref cell in this row
    if ((!txnRef || txnRef === '-') && tr) {
        const refEl = tr.querySelector('[data-col-id="ref"], strong[onclick*="navigateToOriginalVoucher"], .ref-cell');
        if (refEl) {
            txnRef = refEl.innerText.trim();
        }
    }

    currentCtxTxn = (txnRef && txnRef !== '-' && txnRef !== 'OPENING' && txnRef !== 'الرصيد الافتتاحي') ? { type: txnType, ref: txnRef } : null;

    const menu = document.getElementById('gl-context-menu');
    if (!menu) return;

    // Configure Drilldown button
    const drillEl = document.getElementById('gl-ctx-drilldown');
    if (drillEl) {
        if (currentCtxTxn && currentCtxTxn.ref) {
            drillEl.classList.remove('disabled');
            let cleanDispRef = String(currentCtxTxn.ref).replace(/-(PUR|SAL)-PAID.*|-PAID.*|-BT\d+|-CH\d+$/i, '').trim();
            drillEl.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:1.15rem;">🔗</span>
                    <span>الذهاب إلى أصل السند: <strong style="color:#38bdf8; background:rgba(56,189,248,0.18); padding:2px 8px; border-radius:6px; letter-spacing:0.5px;">${cleanDispRef}</strong></span>
                </div>
                <span style="font-size:0.85rem; color:#38bdf8;">◀</span>`;
            drillEl.onclick = (ev) => {
                ev.stopPropagation();
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
    if (accVal === 'ALL') return 'جميع الحسابات';
    if (accVal === '-' || !accVal) return '-';
    let name = '';
    const idLower = String(accVal).toLowerCase();
    if (ACCOUNT_NAME_CACHE.has(idLower)) name = ACCOUNT_NAME_CACHE.get(idLower);
    else {
        const cleanId = idLower.replace(/^[a-zA-Z]+\s*[-_:\/]\s*/, '').trim();
        if (ACCOUNT_NAME_CACHE.has(cleanId)) name = ACCOUNT_NAME_CACHE.get(cleanId);
    }
    
    // Hardcoded fallbacks
    if (!name) {
        if (idLower === 'safe-001' || accVal === 'safe-001') name = 'الخزينة الرئيسية';
        else if (idLower === 'bank-001' || accVal === 'bank-001') name = 'البنك الرئيسي';
        else if (idLower === 'bankok' || accVal === 'bankok') name = 'بنكك (Bankak)';
        else if (idLower === 'fawry' || accVal === 'fawry') name = 'فوري (Fawry)';
        else if (idLower === 'o-cash' || accVal === 'o-cash') name = 'أوكاش (O-Cash)';
        else if (idLower === 'other' || accVal === 'other') name = 'أخرى';
        else if (idLower === 'exp-purchases' || accVal === 'exp-purchases') name = 'المشتريات';
        else if (idLower === 'revenue-sales' || accVal === 'revenue-sales') name = 'إيرادات المبيعات';
        else if (idLower === 'cost-purchases' || accVal === 'cost-purchases') name = 'تكلفة المشتريات (مخزون)';
        else if (idLower === 'act-inventory' || accVal === 'act-inventory') name = 'حساب المخزون';
        else if (idLower === 'exp-inventory-adjustments' || accVal === 'exp-inventory-adjustments') name = 'تسويات المخزون';
        else if (idLower === 'discount-permitted' || accVal === 'discount-permitted') name = 'خصم مسموح به';
        else if (idLower === 'discount-received' || accVal === 'discount-received') name = 'خصم مكتسب';
        else if (idLower === 'vat-collected' || accVal === 'vat-collected') name = 'ضريبة مبيعات';
        else if (idLower === 'vat-paid' || accVal === 'vat-paid') name = 'ضريبة مشتريات';
        else if (idLower === 'cheques-receivable' || accVal === 'cheques-receivable') name = 'شيكات برسم التحصيل';
        else name = accVal;
    }
    
    return String(name || '').replace(/^\[\d+\]\s*/, '').replace(/^\d+\s*[-–]\s*/, '').trim();
}

async function prepareLedgerPreviewData() {
    if (!currentTxns || currentTxns.length === 0) {
        if (typeof runReport === 'function') {
            runReport();
        }
    }

    let sourceTxns = currentTxns || [];

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

    // Clone and sort chronologically (Oldest first) for Ledger Preview & Print
    let previewTxns = sourceTxns.slice().sort((a, b) => {
        const da = (a.date || '') + ' ' + parseTimeTo24h(a.time);
        const db = (b.date || '') + ' ' + parseTimeTo24h(b.time);
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

        let finalBalanceFc = runningBalanceFc;
        if (runningBalance !== undefined && finalBalanceFc !== undefined) {
            if (runningBalance < 0) {
                finalBalanceFc = -Math.abs(finalBalanceFc);
            } else if (runningBalance > 0) {
                finalBalanceFc = Math.abs(finalBalanceFc);
            }
        }

        return Object.assign({}, t, {
            debit: dr,
            credit: cr,
            debitForeign: drF,
            creditForeign: crF,
            balance: runningBalance,
            balanceForeign: finalBalanceFc,
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

    const PRESETS_STORAGE_KEY = 'ledger_preview_presets_v20';
    let presets = [];
    try { presets = JSON.parse(localStorage.getItem(PRESETS_STORAGE_KEY) || '[]'); } catch(e){}

    const defaultPresets = [
        { name: '💎 افتراضي 1', showInRibbon: true, isLocked: true },
        { name: '⭐ افتراضي 2', showInRibbon: true, isLocked: true },
        { name: '🎨 افتراضي 3', showInRibbon: true, isLocked: true },
        { name: '🔥 افتراضي 4', showInRibbon: true, isLocked: true },
        { name: '✨ افتراضي 5', showInRibbon: true, isLocked: true },
        { name: '🚀 افتراضي 6', showInRibbon: true, isLocked: true }
    ];

    if (!Array.isArray(presets) || presets.length === 0) {
        presets = defaultPresets;
    }

    // Lock all default presets permanently
    presets.forEach(p => { p.isLocked = true; });
    try { localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(presets)); } catch(e){}

    presets.slice(0, 6).forEach((p, idx) => {
        if (p.showInRibbon === false) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'luxury-action-btn';
        
        let color = '#38bdf8';
        if (idx === 0) color = '#ca8a04';
        else if (idx === 1) color = '#10b981';
        else if (idx === 2) color = '#0284c7';
        else if (idx === 3) color = '#f97316';
        else if (idx === 4) color = '#a855f7';
        else if (idx === 5) color = '#64748b';
        
        btn.style.cssText = `--btn-color: ${color};`;
        btn.innerHTML = p.name || ('نموذج ' + (idx + 1));
        
        btn.onclick = (e) => {
            if (e) e.preventDefault();
            openPresetModal(idx, p.name || ('نموذج ' + (idx + 1)), true);
        };
        container.appendChild(btn);
    });
}
window.renderLedgerPresets = renderLedgerPresets;

async function openPresetModal(presetIndex, presetName, isLocked = true) {
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
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" onclick="window.shareCurrentLedgerReport(${presetIndex}, '${presetName}')" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border: none; padding: 7px 16px; border-radius: 20px; font-weight: 900; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 3px 10px rgba(59,130,246,0.4);" title="مشاركة كشف الحساب عبر التطبيقات">
                        🔗 مشاركة
                    </button>

                    <button type="button" onclick="window.openLedgerWhatsAppModal()" style="background: linear-gradient(135deg, #25d366, #128c7e); color: #fff; border: none; padding: 7px 16px; border-radius: 20px; font-weight: 900; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 3px 10px rgba(37,211,102,0.4);" title="إرسال كشف الحساب للشخص المعني عبر واتساب">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:inline-block; vertical-align:middle;">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 0C5.373 0 0 5.373 0 12c0 2.115.552 4.103 1.516 5.832L.057 24l6.34-1.662A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z" fill="#ffffff"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="#ffffff"/>
                        </svg>
                        <span>واتساب</span>
                    </button>

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
}
window.openPresetModal = openPresetModal;

window.shareCurrentLedgerReport = async function(presetIndex = 0, presetName = 'افتراضي') {
    let accountName = document.getElementById('chip-name')?.innerText || '';
    if (!accountName || accountName === 'جميع الحسابات') {
        const inputVal = document.getElementById('account-search-input')?.value || '';
        if (inputVal && !inputVal.includes('Enter')) accountName = inputVal.trim();
    }
    if (!accountName) accountName = 'كشف حساب عام';

    const dateFrom = document.getElementById('filter-date-from')?.value || '';
    const dateTo = document.getElementById('filter-date-to')?.value || '';
    const totalDebit = document.getElementById('launcher-total-debit')?.innerText || '0.00';
    const totalCredit = document.getElementById('launcher-total-credit')?.innerText || '0.00';
    const totalBalance = document.getElementById('launcher-total-balance')?.innerText || '0.00';

    const balNum = parseFloat(String(totalBalance).replace(/,/g, '')) || 0;
    const balType = (balNum > 0) ? 'مدين (عليه)' : ((balNum < 0) ? 'دائن (له)' : 'متزن (0.00)');

    const title = `كشف حساب معتمد - ${accountName}`;
    const shareText = `*كشف حساب معتمد* 📊\n` +
        `*الحساب:* ${accountName}\n` +
        (dateFrom && dateTo ? `*الفترة:* من ${dateFrom} إلى ${dateTo}\n` : '') +
        `• إجمالي المدين: ${totalDebit} SDG\n` +
        `• إجمالي الدائن: ${totalCredit} SDG\n` +
        `• *الرصيد النهائي الحالي:* ${Math.abs(balNum).toLocaleString()} SDG (${balType})\n` +
        `──────────────────\n` +
        `نظام السجانة للحلول المحاسبية 🏢`;

    const shareUrl = window.location.href.split('?')[0].replace('general-ledger.php', 'preview-ledger.php') + `?presetIndex=${presetIndex || 0}`;

    if (navigator.share) {
        try {
            await navigator.share({
                title: title,
                text: shareText,
                url: shareUrl
            });
            if (typeof showToast === 'function') {
                showToast('✅ تمت مشاركة كشف الحساب بنجاح', 'success');
            }
            return;
        } catch(err) {
            if (err.name === 'AbortError') return;
            console.warn('Navigator share error, falling back:', err);
        }
    }

    if (typeof window.openLedgerWhatsAppModal === 'function') {
        window.openLedgerWhatsAppModal(accountName);
    } else {
        navigator.clipboard.writeText(shareText + '\n' + shareUrl);
        if (typeof showToast === 'function') {
            showToast('✅ تم نسخ كشف الحساب ورابط المعاينة إلى الحافظة', 'success');
        } else {
            alert('✅ تم نسخ كشف الحساب ورابط المعاينة إلى الحافظة بنجاح.');
        }
    }
};

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
    fetch('api_index.php/report-layout?reportKey=ledger_preview_presets_v20', { headers: headers })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && Array.isArray(data.layout) && data.layout.length > 0) {
                localStorage.setItem('ledger_preview_presets_v20', JSON.stringify(data.layout));
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
    if (e.key === 'ledger_preview_presets_v20' || e.key === 'ledger_preview_presets_v20') {
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
                
                const colIndex = Array.from(th.parentElement.children).indexOf(th);
                const rows = document.querySelectorAll('#recent-tbody tr');
                rows.forEach(tr => {
                    const cell = tr.children[colIndex];
                    if (cell) {
                        cell.style.width = newWidth + 'px';
                        cell.style.minWidth = newWidth + 'px';
                    }
                });
            };

            const mouseUpHandler = function() {
                resizer.classList.remove('resizing');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                
                const colId = th.getAttribute('data-col-id');
                if (colId) {
                    const colConfig = activeGLColumnSettings.find(c => c.id === colId);
                    if (colConfig) {
                        colConfig.width = th.style.width;
                        if (typeof saveGLColumnSettings === 'function') saveGLColumnSettings();
                    }
                }

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
            
            const colIndex = Array.from(th.parentElement.children).indexOf(th);
            const rows = document.querySelectorAll('#recent-tbody tr');
            rows.forEach(tr => {
                const cell = tr.children[colIndex];
                if (cell) {
                    cell.style.width = 'auto';
                    cell.style.minWidth = 'max-content';
                }
            });
            
            const colId = th.getAttribute('data-col-id');
            if (colId) {
                const colConfig = activeGLColumnSettings.find(c => c.id === colId);
                if (colConfig) {
                    colConfig.width = 'auto';
                    if (typeof saveGLColumnSettings === 'function') saveGLColumnSettings();
                }
            }
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

window.glHandleDragStart = function(e) {
    const th = e.target.closest('th');
    if (!th) {
        e.preventDefault();
        return;
    }
    if (e.target.classList && e.target.classList.contains('resizer')) {
        e.preventDefault();
        return;
    }
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', th.getAttribute('data-col-id'));
    setTimeout(() => { th.style.opacity = '0.4'; }, 10);
};

window.glHandleDragEnter = function(e) {
    e.preventDefault();
    const th = e.target.closest('th');
    if (th) {
        th.style.borderLeft = '3px solid #0ea5e9';
    }
};

window.glHandleDragOver = function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
};

window.glHandleDragLeave = function(e) {
    const th = e.target.closest('th');
    if (th) {
        th.style.borderLeft = '';
    }
};

window.glHandleDrop = function(e) {
    e.preventDefault();
    const draggedColId = e.dataTransfer.getData('text/plain');
    let targetTh = e.target.closest('th');
    if (!targetTh) return;
    
    document.querySelectorAll('#recent-table th').forEach(el => {
        el.style.opacity = '1';
        el.style.borderLeft = '';
    });
    
    const targetColId = targetTh.getAttribute('data-col-id');
    if (draggedColId && targetColId && draggedColId !== targetColId) {
        const arr = typeof activeGLColumnSettings !== 'undefined' ? activeGLColumnSettings : window.activeGLColumnSettings;
        if (!arr) return;

        const fromIdx = arr.findIndex(c => c.id === draggedColId);
        const toIdx = arr.findIndex(c => c.id === targetColId);
        
        if (fromIdx > -1 && toIdx > -1) {
            const movedItem = arr.splice(fromIdx, 1)[0];
            arr.splice(toIdx, 0, movedItem);
            if (typeof saveGLColumnSettings === 'function') saveGLColumnSettings();
            if (typeof runReport === 'function') runReport();
            if (typeof showToast === 'function') showToast('�� ������� �����', 'success');
        }
    }
};

window.glHandleDragEnd = function(e) {
    const th = e.target.closest ? e.target.closest('th') : null;
    if (th) th.style.opacity = '1';
    document.querySelectorAll('#recent-table th').forEach(el => {
        el.style.opacity = '1';
        el.style.borderLeft = '';
    });
};




window.glHandleArrowMove = function(e, colId, dir) {
    e.preventDefault();
    e.stopPropagation();
    
    const arr = typeof activeGLColumnSettings !== 'undefined' ? activeGLColumnSettings : window.activeGLColumnSettings;
    if (!arr) return;

    const idx = arr.findIndex(c => c.id === colId);
    if (idx < 0) return;
    
    const newIdx = idx + dir;
    if (newIdx >= 0 && newIdx < arr.length) {
        const item = arr.splice(idx, 1)[0];
        arr.splice(newIdx, 0, item);
        if (typeof saveGLColumnSettings === 'function') saveGLColumnSettings();
        if (typeof runReport === 'function') runReport();
        if (typeof showToast === 'function') showToast('�� ����� ������', 'info');
    }
};
