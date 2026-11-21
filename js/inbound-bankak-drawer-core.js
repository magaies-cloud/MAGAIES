// =============================================================
// InboundBankakManager - Client-Side Controller
// =============================================================
window.InboundBankakManager = {
    receipts: [],
    banks: [],
    policy: {},
    selectedBankId: '',
    knownReceiptIds: new Set(),
    syncedVoucherNos: new Set(),
    _hasInitialLoaded: false,
    activeFilter: 'all',
    currentReceiptForAlert: null,

    getActiveTenantId: function() {
        return (window.SERVER_TENANT && window.SERVER_TENANT.id) 
            || (JSON.parse(localStorage.getItem('user')||'{}').tenant_id) 
            || (window.INBOUND_ACTIVE_TENANT || '12') 
            || '12';
    },

    init: function() {
        this.syncMasterDataFromServer();
        this.refreshReceipts();
        
        // Real-Time Ultra-Fast Polling every 2.5 seconds for live detection
        setInterval(() => {
            if (document.hidden) return;
            const ov = document.getElementById('inbound-bankak-drawer-overlay');
            const isOpen = ov && ov.style.display && ov.style.display !== 'none';
            const conn = navigator.connection || {}; const slow = Boolean(conn.saveData || /2g/i.test(conn.effectiveType || ''));
            const bgWait = slow ? 120000 : 45000;
            if (navigator.onLine !== false && (isOpen || !this._lastBgPoll || (Date.now() - this._lastBgPoll > bgWait))) {
                this._lastBgPoll = Date.now();
                this.refreshReceipts(true);
            }
        }, 3000);

        // Check URL parameters for Deep Linking (e.g. ?action=inspect_receipt&receipt_id=123)
        const params = new URLSearchParams(window.location.search);
        if (params.get('open_platform') === '1') {
            setTimeout(() => this.openDrawer(), 220);
        }
        if (params.get('action') === 'inspect_receipt' || params.get('receipt_id')) {
            const rId = params.get('receipt_id');
            setTimeout(() => {
                this.inspectById(rId);
            }, 800);
        }

        // Check if a batch of WhatsApp receipts was queued to load into multi-cash-payment.php
        try {
            const pendingBatchJson = sessionStorage.getItem('erp_wa_batch_to_load');
            if (pendingBatchJson && document.getElementById('accounts-blocks-container')) {
                sessionStorage.removeItem('erp_wa_batch_to_load');
                const batchIds = JSON.parse(pendingBatchJson);
                if (Array.isArray(batchIds) && batchIds.length > 0) {
                    setTimeout(() => {
                        this._populateBatchIntoCurrentMultiVoucher(batchIds);
                    }, 900);
                }
            }
        } catch (e) {}
    },

    getAvailableBanks: function() {
        if (Array.isArray(this.banks) && this.banks.length > 0) return this.banks;
        try {
            const getTenantKey = window.getTenantKey || ((k) => k);
            const raw = JSON.parse(localStorage.getItem(getTenantKey('erp_banks_v1')) || localStorage.getItem('erp_banks_v1') || '[]');
            if (Array.isArray(raw)) {
                return raw.filter(b => b && !b.deleted && !b.isDeactivated && b.type !== 'safe');
            }
        } catch (e) {}
        return [];
    },

    getAllReceivingAccounts: function() {
        const getTK = window.getTenantKey || ((k) => k);
        const rd = (k) => { try { const a = JSON.parse(localStorage.getItem(getTK(k)) || localStorage.getItem(k) || '[]'); return Array.isArray(a) ? a.filter(x => x && !x.deleted && !x.isDeactivated) : []; } catch (_) { return []; } };
        const banks = this.getAvailableBanks().map(b => ({ val: 'bank-' + String(b.id).replace(/^bank-/, ''), rawId: String(b.id), name: b.name || b.bankName || '', grp: 'البنوك', icon: '' }));
        const sups  = rd('erp_suppliers_v1').map(s => ({ val: 'supp-' + String(s.id).replace(/^supp-/, ''), rawId: String(s.id), name: s.name || s.supplierName || '', grp: 'الموردين', icon: '' }));
        const custs = rd('erp_customers_v1').map(c => ({ val: 'cust-' + String(c.id).replace(/^cust-/, ''), rawId: String(c.id), name: c.name || c.customerName || '', grp: 'العملاء', icon: '' }));
        const safes = rd('erp_safes_v1').map(s => ({ val: 'safe-' + String(s.id).replace(/^safe-/, ''), rawId: String(s.id), name: s.name || s.safeName || '', grp: 'الخزن', icon: '' }));
        const whs   = rd('erp_warehouses_v1').map(w => ({ val: 'wh-' + String(w.id).replace(/^wh-/, ''), rawId: String(w.id), name: w.name || w.warehouseName || '', grp: 'المخازن', icon: '' }));
        const exps  = rd('erp_accounts_v1').map(e => ({ val: 'exp-' + String(e.id).replace(/^exp-/, ''), rawId: String(e.id), name: e.name || e.accountName || '', grp: 'المصاريف والحسابات', icon: '' }));
        const emps  = [...rd('erp_employees_v1'), ...rd('erp_users_v1')].map(u => ({ val: 'emp-' + String(u.id).replace(/^emp-/, ''), rawId: String(u.id), name: u.name || u.fullName || u.username || '', grp: 'الموظفين', icon: '' }));
        const seen = new Set();
        return [...banks, ...sups, ...custs, ...safes, ...whs, ...exps, ...emps].filter(x => x.name && !seen.has(x.val) && seen.add(x.val));
    },

    syncHeaderBankFromRows: function() {
        const hid = document.getElementById('drawer-target-bank-select'), inp = document.getElementById('drawer-target-acc-filter');
        if (!hid || !inp) return;
        const rows = (this.receipts || []).filter(r => r.status !== 'duplicate_rejected');
        if (!rows.length) return;
        const allAccs = this.getAllReceivingAccounts();
        const normRowAcc = (r) => {
            const c = String(r.bank_code || '').trim(), n = String(r.bank_name || '').trim();
            const hit = allAccs.find(x => (c && c !== '170' && (x.val === c || x.rawId === c.replace(/^(bank|supp|cust|safe|wh|exp|emp)-/, ''))) || (n && x.name === n));
            return hit ? { val: hit.val, lbl: hit.name } : { val: c || 'default-bok', lbl: n || 'بنك الخرطوم' };
        };
        const first = normRowAcc(rows[0]);
        const allSame = rows.every(r => normRowAcc(r).lbl === first.lbl);
        if (allSame) {
            hid.value = first.val === 'default-bok' ? '' : first.val;
            this.selectedBankId = hid.value;
            if (document.activeElement !== inp) { inp.value = first.lbl; inp.dataset.curLabel = first.lbl; }
        } else {
            hid.value = '';
            this.selectedBankId = '';
            if (document.activeElement !== inp) { inp.value = ''; inp.dataset.curLabel = ''; }
        }
    },

    populateHeaderBankDropdown: function() {
        this.syncHeaderBankFromRows();
    },
    openAccountSearchMenu: function(inputEl, target) {
        if (!inputEl) return;
        let m = document.getElementById('inbound-acc-live-menu');
        if (!m) {
            m = document.createElement('div'); m.id = 'inbound-acc-live-menu';
            m.style.cssText = 'position:fixed;z-index:2147483647;background:#0f172a;border:2px solid #38bdf8;border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,0.92);max-height:320px;overflow-y:auto;direction:rtl;font-family:Cairo,Tajawal,sans-serif;padding:6px;';
            document.body.appendChild(m);
            document.addEventListener('mousedown', (ev) => { if (m.style.display === 'block' && !m.contains(ev.target) && ev.target !== m._owner) m.style.display = 'none'; });
        }
        m._owner = inputEl;
        const raw = String(inputEl.value || '').replace(/^[🏦🚚👥🗄️🏬💸👔🔍]\s*/, '').trim().toLowerCase();
        const q = (inputEl.dataset.curLabel && String(inputEl.value || '').trim() === inputEl.dataset.curLabel) ? '' : raw;
        const all = this.getAllReceivingAccounts().filter(x => !q || x.name.toLowerCase().includes(q) || x.rawId.toLowerCase().includes(q) || x.grp.toLowerCase().includes(q));
        const r = inputEl.getBoundingClientRect(), w = Math.max(300, r.width);
        m.style.width = w + 'px'; m.style.top = Math.min(window.innerHeight - 330, r.bottom + 4) + 'px'; m.style.left = Math.max(8, Math.min(window.innerWidth - w - 8, r.left)) + 'px';
        m.innerHTML = (all.length ? all.slice(0, 180).map(x => `<div data-rc-account-id="${escapeHtml(x.val)}" data-rc-account-name="${escapeHtml(x.name)}" data-val="${escapeHtml(x.val)}" data-name="${escapeHtml(x.name)}" data-lbl="${escapeHtml(x.name)}" style="padding:8px 10px;border-radius:8px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:8px;border-bottom:1px solid rgba(148,163,184,0.12);"><span style="font-size:0.92rem;font-weight:900;color:#fff;">${escapeHtml(x.name)}</span><span style="font-size:0.72rem;font-weight:800;color:#7dd3fc;background:rgba(56,189,248,0.14);padding:2px 7px;border-radius:99px;white-space:nowrap;">${escapeHtml(x.grp)}</span></div>`).join('') : `<div style="padding:12px;text-align:center;color:#94a3b8;font-weight:800;">لا يوجد حساب مطابق</div>`);
        m.querySelectorAll('[data-val]').forEach(el => {
            el.onmouseenter = () => { el.style.background = 'rgba(56,189,248,0.22)'; }; el.onmouseleave = () => { el.style.background = 'transparent'; };
            el.onmousedown = (ev) => {
                ev.preventDefault();
                const v = el.getAttribute('data-val') || '', nm = el.getAttribute('data-name') || '', lbl = el.getAttribute('data-lbl') || '';
                inputEl.value = lbl; inputEl.dataset.curLabel = lbl; inputEl.dataset.accVal = v; inputEl.dataset.accName = nm; m.style.display = 'none';
                if (target === 'header') {
                    const h = document.getElementById('drawer-target-bank-select'); if (h) h.value = v;
                    this.onDefaultDrawerBankChange(v, nm, lbl);
                } else {
                    const rid = Number(target);
                    if (this._editingRows && this._editingRows[rid]) {
                        this._editingRows[rid].newRecId = v;
                        this._editingRows[rid].newRecName = nm;
                        this._editingRows[rid].dirty = (nm !== this._editingRows[rid].origRecName || v !== this._editingRows[rid].origRecId || !!this._editingRows[rid].newCustName);
                        this.renderList();
                    } else {
                        this.changeTargetBank(rid, v, nm);
                    }
                }
            };
        });
        m.style.display = 'block';
    },

    dockPlatform: function(pos) {
        const ov = document.getElementById('inbound-bankak-drawer-overlay'), pn = document.getElementById('inbound-bankak-drawer-panel');
        if (!ov || !pn) return;
        pn.style.transform = 'none'; pn.dataset.dx = '0'; pn.dataset.dy = '0';
        ov.style.justifyContent = pos === 'right' ? 'flex-start' : (pos === 'left' ? 'flex-end' : 'center');
    },

    startDragPlatform: function(ev) {
        if (ev.button !== 0 || ev.target.closest('button, a, input, select')) return;
        const pn = document.getElementById('inbound-bankak-drawer-panel');
        if (!pn) return;
        const sx = ev.clientX, sy = ev.clientY, ox = parseFloat(pn.dataset.dx || '0') || 0, oy = parseFloat(pn.dataset.dy || '0') || 0;
        const mv = (e) => { const nx = ox + (e.clientX - sx), ny = oy + (e.clientY - sy); pn.dataset.dx = String(nx); pn.dataset.dy = String(ny); pn.style.transform = `translate(${nx}px, ${ny}px)`; };
        const up = () => { window.removeEventListener('mousemove', mv); window.removeEventListener('mouseup', up); };
        window.addEventListener('mousemove', mv); window.addEventListener('mouseup', up);
    },

    onDefaultDrawerBankChange: async function(bankId, bankName = '', fullLabel = '') {
        this.selectedBankId = bankId || '';
        const hit = this.getAllReceivingAccounts().find(x => x.val === bankId);
        const resolvedName = bankName || (hit ? hit.name : '');
        const resolvedLbl = fullLabel || (hit ? `${hit.icon} ${hit.name}` : resolvedName);
        try {
            const pol = JSON.parse(localStorage.getItem('erp_whatsapp_statement_policy_v1') || '{}');
            pol.defaultReceiptBankId = this.selectedBankId;
            pol.defaultReceiptBankName = resolvedName;
            localStorage.setItem('erp_whatsapp_statement_policy_v1', JSON.stringify(pol));
            if (typeof window.syncToBackend === 'function') window.syncToBackend('whatsapp_statement_policy', pol);
        } catch (e) {}

        // تعميم الحساب المختار في الأعلى فوراً على جميع الأسطر بالأسفل
        const targetRows = (this.receipts || []).filter(r => r.status !== 'duplicate_rejected');
        targetRows.forEach(r => { r.bank_code = bankId; r.bank_name = resolvedName; });
        document.querySelectorAll('.inbound-row-rec-input').forEach(inp => {
            inp.value = resolvedLbl; inp.dataset.curLabel = resolvedLbl; inp.dataset.accVal = bankId; inp.dataset.accName = resolvedName;
        });

        const unpostedIds = targetRows.filter(r => !r.voucher_no || (r.status !== 'verified' && r.status !== 'reconciled')).map(r => Number(r.id));
        if (unpostedIds.length && bankId) {
            try {
                const tId = this.getActiveTenantId();
                await fetch('api/inbound_receipts.php?action=bulk_change_target_bank&tenant_id=' + encodeURIComponent(tId), {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'bulk_change_target_bank', tenant_id: tId, ids: unpostedIds, bank_id: bankId, bank_name: resolvedName })
                });
            } catch (_) {}
        }
        if (typeof showToast === 'function' && resolvedName) {
            showToast(`✅ تم تعميم الحساب المستلم (${resolvedName}) على جميع الأسطر`, 'success');
        }
    },

    openDrawer: function() {
        const overlay = document.getElementById('inbound-bankak-drawer-overlay');
        if (!overlay) return;
        if (overlay.parentElement !== document.body) document.body.appendChild(overlay);
        overlay.style.display = 'flex';
        this._lastRenderedSig = '';
        this.refreshReceipts();
    },

    toggleDrawer: function() {
        const overlay = document.getElementById('inbound-bankak-drawer-overlay');
        if (!overlay) return;
        const isShown = overlay.style.display === 'flex';
        if (isShown) {
            this.closeDrawer();
        } else {
            if (overlay.parentElement !== document.body) document.body.appendChild(overlay);
            overlay.style.display = 'flex';
            this._lastRenderedSig = '';
            this.refreshReceipts();
        }
    },

    closeDrawer: function() {
        const overlay = document.getElementById('inbound-bankak-drawer-overlay');
        if (overlay) overlay.style.display = 'none';
    },

    // Sync a posted or staged inbound receipt into localStorage (erp_receipts_v1, erp_customers_v1, erp_banks_v1) and General Ledger
    syncPostedReceiptToLocalErp: function(r, postingData = null, options = {}) {
        if (!r || (!r.voucher_no && !(postingData && postingData.voucher_no))) return false;
        const vNo = (postingData && postingData.voucher_no) ? postingData.voucher_no : r.voucher_no;
        if (!vNo) return false;

        const isAwaitingApproval = (postingData && postingData.approval_mode === 'review_first') ||
            (postingData && postingData.voucher && postingData.voucher.isPosted === false) ||
            (r.issue_reason === 'awaiting_posting_approval' && r.status === 'pending' && !(postingData && postingData.status === 'verified'));
        const isActuallyPosted = !isAwaitingApproval;

        if (this.syncedVoucherNos.has(vNo) && !postingData) {
            return false;
        }

        const getTenantKey = window.getTenantKey || ((k) => k);
        const rKey = getTenantKey('erp_receipts_v1');
        let storedReceipts = [];
        try {
            storedReceipts = JSON.parse(localStorage.getItem(rKey) || localStorage.getItem('erp_receipts_v1') || '[]');
            if (!Array.isArray(storedReceipts)) storedReceipts = [];
        } catch (e) { storedReceipts = []; }

        let rawCustId = (postingData && postingData.customer_id) ? postingData.customer_id : (r.customer_id || '');
        rawCustId = String(rawCustId).replace(/^cust-/, '');
        const custName = (postingData && postingData.customer_name) ? postingData.customer_name : (r.customer_name || 'عميل بنكك');
        const amt = parseFloat(r.amount || (postingData && postingData.voucher && postingData.voucher.totalAmount) || 0) || 0;

        const existingLocalVoucher = storedReceipts.find(v => v && (v.voucherNo === vNo || v.id === vNo));
        const alreadyInLocal = Boolean(existingLocalVoucher);
        const wasAlreadyPostedInLocal = Boolean(existingLocalVoucher && existingLocalVoucher.isPosted);
        if (alreadyInLocal && !postingData && wasAlreadyPostedInLocal === isActuallyPosted) {
            const existingItem = existingLocalVoucher.items && existingLocalVoucher.items[0];
            const existingCustMatch = !rawCustId || (existingItem && String(existingItem.accountId || existingItem.account || '').replace(/^cust-/, '') === rawCustId);
            const existingAmtMatch = Math.abs((parseFloat(existingLocalVoucher.totalAmount) || 0) - amt) < 0.01;
            if (existingCustMatch && existingAmtMatch) {
                this.syncedVoucherNos.add(vNo);
                return false;
            }
        }
        this.syncedVoucherNos.add(vNo);

        // 1. Sync Customer in erp_customers_v1
        const cKey = getTenantKey('erp_customers_v1');
        let custs = [];
        try {
            custs = JSON.parse(localStorage.getItem(cKey) || localStorage.getItem('erp_customers_v1') || '[]');
            if (!Array.isArray(custs)) custs = [];
        } catch (e) { custs = []; }

        let cIdx = custs.findIndex(c => c && (String(c.id) === rawCustId || (c.name && c.name.trim() === custName.trim())));
        if (cIdx > -1) {
            rawCustId = String(custs[cIdx].id);
            if (isActuallyPosted) {
                if (postingData && postingData.customer && postingData.customer.currentBalance !== undefined) {
                    custs[cIdx].currentBalance = parseFloat(postingData.customer.currentBalance) || 0;
                } else if (!wasAlreadyPostedInLocal && amt > 0) {
                    custs[cIdx].currentBalance = (parseFloat(custs[cIdx].currentBalance) || 0) - amt;
                }
            }
        } else if (postingData && postingData.customer) {
            rawCustId = String(postingData.customer.id || rawCustId || ('CUST-' + Date.now()));
            custs.push(postingData.customer);
        } else if (custName) {
            rawCustId = rawCustId || ('CUST-' + Date.now());
            custs.push({
                id: rawCustId,
                name: custName,
                phone: r.customer_phone || '',
                whatsapp: r.customer_phone || '',
                currentBalance: isActuallyPosted ? -amt : 0,
                status: 'active'
            });
        }
        try {
            localStorage.setItem(cKey, JSON.stringify(custs));
            localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
        } catch (e) {}

        // 2. Sync Bank in erp_banks_v1
        const bKey = getTenantKey('erp_banks_v1');
        let banks = [];
        try {
            banks = JSON.parse(localStorage.getItem(bKey) || localStorage.getItem('erp_banks_v1') || '[]');
            if (!Array.isArray(banks)) banks = [];
        } catch (e) { banks = []; }

        let rawBankId = (postingData && postingData.bank_id) ? postingData.bank_id : (r.bank_code || '');
        rawBankId = String(rawBankId).replace(/^bank-/, '');
        let bIdx = banks.findIndex(b => b && ((rawBankId && String(b.id) === rawBankId) || (b.type !== 'safe' && b.name && ((r.bank_name && r.bank_name.includes(b.name)) || b.name.includes('الخرطوم')))));
        if (bIdx === -1) {
            bIdx = banks.findIndex(b => b && b.type !== 'safe');
        }
        let resolvedBankName = (postingData && postingData.bank_name) || r.bank_name || 'بنك الخرطوم';
        if (bIdx > -1) {
            rawBankId = String(banks[bIdx].id);
            resolvedBankName = banks[bIdx].name || resolvedBankName;
            if (isActuallyPosted) {
                if (postingData && postingData.bank && postingData.bank.currentBalance !== undefined) {
                    banks[bIdx].currentBalance = parseFloat(postingData.bank.currentBalance) || 0;
                } else if (!wasAlreadyPostedInLocal && amt > 0) {
                    banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + amt;
                }
            }
            try {
                localStorage.setItem(bKey, JSON.stringify(banks));
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            } catch (e) {}
        } else if (postingData && postingData.bank) {
            rawBankId = String(postingData.bank.id || 'B-1700000000001');
            resolvedBankName = postingData.bank.name || resolvedBankName;
            banks.push(postingData.bank);
            try {
                localStorage.setItem(bKey, JSON.stringify(banks));
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            } catch (e) {}
        }

        // 3. Sync Voucher in erp_receipts_v1
        const sysRate = (typeof window.getSystemUsdRate === 'function' ? window.getSystemUsdRate() : 5999) || 5999;
        const voucherDesc = `سداد إشعار تحويل بنكي (${resolvedBankName}) رقم العملية: ${r.transfer_no || ''} - العميل: ${custName}`;
        const voucherObj = (postingData && postingData.voucher) ? postingData.voucher : {
            id: vNo,
            voucherNo: vNo,
            type: 'receipt',
            source: 'whatsapp_inbound',
            isGrouped: true,
            mainAccount: 'bank-' + (rawBankId || 'B-1700000000001'),
            toAccount: 'bank-' + (rawBankId || 'B-1700000000001'),
            fromAccount: 'حسابات فرعية متعددة',
            date: (r.created_at ? r.created_at.substring(0, 10) : new Date().toISOString().substring(0, 10)),
            time: new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }),
            description: voucherDesc,
            notes: voucherDesc,
            totalAmount: amt,
            amountLocal: amt,
            foreignAmount: +(amt / sysRate).toFixed(2),
            amountForeign: +(amt / sysRate).toFixed(2),
            currency: r.currency || 'SDG',
            exchangeRate: 1,
            systemExchangeRate: sysRate,
            paymentMethod: 'hybrid',
            inboundReceiptId: Number(r.id),
            receiptImage: r.receipt_image || '',
            items: [
                {
                    account: 'cust-' + rawCustId,
                    accountId: 'cust-' + rawCustId,
                    accountName: custName,
                    amount: amt,
                    statement: `إشعار تحويل ${resolvedBankName} رقم ${r.transfer_no || ''}`,
                    costCenter: '',
                    costCenterId: '',
                    costCenterName: '',
                    paymentMethods: ['bank'],
                    paymentMethod: 'bank',
                    cashAmount: 0,
                    cashSafe: '',
                    cashStatement: '',
                    bankAmount: amt,
                    bankId: rawBankId,
                    bankTransfers: [
                        {
                            amount: amt,
                            bankId: rawBankId,
                            bankName: resolvedBankName,
                            transferNo: r.transfer_no || '',
                            statement: `إشعار وارد #${r.id} - ${custName}`
                        }
                    ],
                    chequeAmount: 0,
                    cheques: [],
                    bankName: resolvedBankName,
                    transferNo: r.transfer_no || '',
                    chequeBank: '',
                    chequeNo: '',
                    chequeDate: '',
                    chequeDueDate: ''
                }
            ],
            isPosted: isActuallyPosted,
            createdAt: new Date().toISOString()
        };

        // Remove any older voucher that was previously created for the same inboundReceiptId before reassignment
        if (r.id) {
            for (let i = storedReceipts.length - 1; i >= 0; i--) {
                const sv = storedReceipts[i];
                if (sv && Number(sv.inboundReceiptId) === Number(r.id) && sv.id !== vNo && sv.voucherNo !== vNo) {
                    storedReceipts.splice(i, 1);
                }
            }
        }

        const vIdx = storedReceipts.findIndex(v => v && (v.voucherNo === vNo || v.id === vNo));
        if (vIdx > -1) {
            storedReceipts[vIdx] = voucherObj;
        } else {
            storedReceipts.push(voucherObj);
        }
        try {
            localStorage.setItem(rKey, JSON.stringify(storedReceipts));
            localStorage.setItem('erp_receipts_v1', JSON.stringify(storedReceipts));
        } catch (e) {}

        // 4. Rebuild Accounts Dropdown & General Ledger (unless batched via options.skipRebuild)
        if (!options || !options.skipRebuild) {
            if (typeof window.buildAccountsArray === 'function') {
                try { window.buildAccountsArray(); } catch (e) {}
            }
            if (isActuallyPosted && typeof window.rebuildGLFromAllTables === 'function') {
                try { window.rebuildGLFromAllTables(false); } catch (e) {}
            }
            if (typeof window.refreshVoucherNavigationList === 'function') {
                try { window.refreshVoucherNavigationList(); } catch (e) {}
            }
            if (typeof window.runReport === 'function' || typeof window.applyLedgerFilters === 'function') {
                try {
                    if (typeof window.buildAccountsList === 'function') window.buildAccountsList();
                    if (typeof window.extractFilters === 'function') window.extractFilters();
                    if (typeof window.runReport === 'function') window.runReport();
                    else if (typeof window.applyLedgerFilters === 'function') window.applyLedgerFilters();
                } catch (e) {}
            }
        }
        return true;
    },

    pruneOrphanedInboundVouchers: function(activeVoucherNos, deletedVoucherNo = '', deletedReceiptId = 0) {
        try {
            const getTenantKey = window.getTenantKey || ((k) => k);
            const rKey = getTenantKey('erp_receipts_v1');
            const gKey = getTenantKey('erp_gl_v1');
            const activeSet = Array.isArray(activeVoucherNos) ? new Set(activeVoucherNos.map(v => String(v).trim())) : null;

            let storedReceipts = JSON.parse(localStorage.getItem(rKey) || localStorage.getItem('erp_receipts_v1') || '[]');
            if (Array.isArray(storedReceipts)) {
                const beforeLen = storedReceipts.length;
                storedReceipts = storedReceipts.filter(v => {
                    if (!v) return false;
                    const vid = String(v.id || v.voucherNo || '').trim();
                    if (deletedVoucherNo && vid === String(deletedVoucherNo).trim()) return false;
                    if (deletedReceiptId && Number(v.inboundReceiptId) === Number(deletedReceiptId)) return false;
                    if (activeSet && (v.source === 'whatsapp_inbound' || v.inboundReceiptId)) {
                        return activeSet.has(vid);
                    }
                    return true;
                });
                if (storedReceipts.length !== beforeLen) {
                    localStorage.setItem(rKey, JSON.stringify(storedReceipts));
                    localStorage.setItem('erp_receipts_v1', JSON.stringify(storedReceipts));
                }
            }

            let storedGl = JSON.parse(localStorage.getItem(gKey) || localStorage.getItem('erp_gl_v1') || '[]');
            if (Array.isArray(storedGl)) {
                const beforeGlLen = storedGl.length;
                storedGl = storedGl.filter(g => {
                    if (!g) return false;
                    const gid = String(g.id || '').trim();
                    const vno = String(g.voucherNo || g.refNo || g.voucherId || '').trim();
                    if (deletedVoucherNo && (vno === String(deletedVoucherNo).trim() || gid.includes(String(deletedVoucherNo).trim()))) {
                        return false;
                    }
                    if (activeSet && /^GL-M\d+/i.test(gid) && vno) {
                        return activeSet.has(vno);
                    }
                    return true;
                });
                if (storedGl.length !== beforeGlLen) {
                    localStorage.setItem(gKey, JSON.stringify(storedGl));
                    localStorage.setItem('erp_gl_v1', JSON.stringify(storedGl));
                }
            }
        } catch (e) {}
    },

    _isBatchScanning: false,

    refreshReceipts: function(isSilent = false) {
        const tId = this.getActiveTenantId();
        fetch('api/inbound_receipts.php?action=list&tenant_id=' + encodeURIComponent(tId))
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    if (Array.isArray(res.banks) && res.banks.length > 0) {
                        this.banks = res.banks;
                    }
                    if (res.policy && typeof res.policy === 'object') {
                        this.policy = res.policy;
                        try {
                            const locPol = JSON.parse(localStorage.getItem('erp_whatsapp_statement_policy_v1') || '{}') || {};
                            const needStampSync = locPol.voucherStampImage && String(locPol.voucherStampImage).length >= 150 && (!res.policy.voucherStampImage || String(res.policy.voucherStampImage).length < 150);
                            const needLogoSync = locPol.voucherLogoImage && String(locPol.voucherLogoImage).length >= 100 && (!res.policy.voucherLogoImage || String(res.policy.voucherLogoImage).length < 100);
                            if (needStampSync || needLogoSync) {
                                const patchObj = {};
                                if (needStampSync) patchObj.voucherStampImage = locPol.voucherStampImage;
                                if (needLogoSync) patchObj.voucherLogoImage = locPol.voucherLogoImage;
                                const mergedSyncPol = Object.assign({}, res.policy, locPol, patchObj);
                                this.policy = mergedSyncPol;
                                localStorage.setItem('erp_whatsapp_statement_policy_v1', JSON.stringify(mergedSyncPol));
                                fetch('api/save_master_data.php?tenant_id=' + encodeURIComponent(tId), {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-Tenant-Id': String(tId) },
                                    body: JSON.stringify({ type: 'whatsapp_statement_policy', data: mergedSyncPol })
                                }).catch(() => {});
                            }
                        } catch (e) {}
                    }
                    this.populateHeaderBankDropdown();

                    if (Array.isArray(res.active_voucher_nos)) {
                        this.pruneOrphanedInboundVouchers(res.active_voucher_nos);
                    }

                    // Sync any backend-posted or staged receipts to local ERP storage & GL (batched rebuild once at end)
                    let anySynced = false;
                    res.data.forEach(r => {
                        if (r.voucher_no && (r.status === 'verified' || r.status === 'reconciled' || r.issue_reason === 'awaiting_posting_approval')) {
                            if (this.syncPostedReceiptToLocalErp(r, null, { skipRebuild: true })) {
                                anySynced = true;
                            }
                        }
                    });
                    if (anySynced) {
                        if (typeof window.buildAccountsArray === 'function') {
                            try { window.buildAccountsArray(); } catch (e) {}
                        }
                        if (typeof window.rebuildGLFromAllTables === 'function') {
                            try { window.rebuildGLFromAllTables(false); } catch (e) {}
                        }
                        if (typeof window.refreshVoucherNavigationList === 'function') {
                            try { window.refreshVoucherNavigationList(); } catch (e) {}
                        }
                        if (typeof window.runReport === 'function' || typeof window.applyLedgerFilters === 'function') {
                            try {
                                if (typeof window.buildAccountsList === 'function') window.buildAccountsList();
                                if (typeof window.extractFilters === 'function') window.extractFilters();
                                if (typeof window.runReport === 'function') window.runReport();
                                else if (typeof window.applyLedgerFilters === 'function') window.applyLedgerFilters();
                            } catch (e) {}
                        }
                    }

                    // Auto-scan any pending receipts in batch (excluding forgery check)
                    const pendingWithImg = res.data.filter(r => r.status === 'pending' && r.issue_reason !== 'awaiting_posting_approval' && r.receipt_image && (!r.amount || parseFloat(r.amount) <= 0 || !r.transfer_no));
                    if (pendingWithImg.length > 0 && !this._isBatchScanning) {
                        this._isBatchScanning = true;
                        fetch('api/inbound_receipts.php?action=scan_pending_batch&tenant_id=' + encodeURIComponent(tId))
                            .then(r2 => r2.json())
                            .then(batchRes => {
                                this._isBatchScanning = false;
                                if (batchRes && batchRes.success && Number(batchRes.processed_count || batchRes.scanned_count || 0) > 0) {
                                    this.refreshReceipts(true);
                                }
                            })
                            .catch(() => { this._isBatchScanning = false; });
                    }

                    // Check if new receipts arrived in real-time
                    if (this._hasInitialLoaded) {
                        const newReceipts = res.data.filter(r => !this.knownReceiptIds.has(Number(r.id)));
                        if (newReceipts.length > 0) {
                            newReceipts.forEach(r => this.knownReceiptIds.add(Number(r.id)));
                            this.playNotificationChime();
                            if (newReceipts.length === 1) {
                                this.showLiveInboundToast(newReceipts[0]);
                            } else {
                                this.showAdminBatchNotification(newReceipts);
                            }
                        }
                    } else {
                        res.data.forEach(r => this.knownReceiptIds.add(Number(r.id)));
                        this._hasInitialLoaded = true;
                    }

                    this.receipts = res.data;
                    this.updateStats(res.stats);
                    const newSig = this.activeFilter + '|' + JSON.stringify((res.data || []).map(x => [x.id, x.status, x.issue_reason, x.voucher_no, x.amount, x.transfer_no, x.customer_name, x.bank_code, x.bank_name]));
                    const activeEl = document.activeElement;
                    const userTypingInDrawer = activeEl && activeEl.closest && activeEl.closest('#inbound-bankak-drawer-overlay') && activeEl.tagName === 'INPUT';
                    if (!isSilent || newSig !== this._lastRenderedSig) {
                        if (!isSilent || !userTypingInDrawer) {
                            this._lastRenderedSig = newSig;
                            this.renderList();
                        }
                    }
                }
            })
            .catch(err => {
                if (!isSilent) console.error('Failed to load inbound receipts:', err);
            });
    },

    showAdminBatchNotification: function(batchReceipts) {
        if (!Array.isArray(batchReceipts) || batchReceipts.length === 0) return;
        const verifiedList = batchReceipts.filter(r => r.status === 'verified' || r.voucher_no);
        const awaitingList = batchReceipts.filter(r => r.issue_reason === 'awaiting_posting_approval');
        const custName = batchReceipts[0].customer_name || 'عميل واتساب';
        const custId = batchReceipts[0].customer_id || '';
        const bankName = batchReceipts[0].bank_name || 'بنك الخرطوم';
        const totalAmt = verifiedList.reduce((acc, r) => acc + (parseFloat(r.amount) || 0), 0);
        const toastId = 'admin-batch-toast-' + Date.now();
        const div = document.createElement('div');
        div.id = toastId;
        div.style.cssText = `
            position: fixed; top: 24px; right: 24px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            border: 2px solid ${awaitingList.length > 0 ? '#f59e0b' : '#10b981'}; border-radius: 16px; padding: 16px 20px; color: #fff;
            box-shadow: 0 15px 40px rgba(0,0,0,0.85), 0 0 25px rgba(16,185,129,0.5);
            z-index: 99999999; display: flex; flex-direction: column; gap: 8px;
            font-family: 'Tajawal', sans-serif; direction: rtl; max-width: 450px;
            animation: slideInRightToast 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        `;
        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong style="color:${awaitingList.length > 0 ? '#fcd34d' : '#34d399'}; font-size:1rem;">🔔 ${awaitingList.length > 0 ? 'إشعارات واتساب بانتظار المراجعة والتمرير' : 'إشعار مدير النظام — إضافة إشعارات بالحسابات'}</strong>
                <button type="button" onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:#94a3b8; font-size:1.1rem; cursor:pointer;">✕</button>
            </div>
            <div style="font-size:0.86rem; color:#f8fafc; line-height:1.5;">
                تم استلام وفحص <strong>(${verifiedList.length || batchReceipts.length}) إشعار بنكي</strong> من حساب:
                <strong style="color:#ffd700;">${escapeHtml(custName)}</strong> (${escapeHtml(bankName)})
                بإجمالي <strong style="color:#34d399;">${totalAmt.toLocaleString('en-US')} SDG</strong>.
            </div>
            <div style="display:flex; gap:8px; margin-top:4px;">
                ${awaitingList.length > 0
                    ? `<a href="posting-accounts.php?filter=whatsapp" style="flex:1; text-align:center; padding:6px 10px; border-radius:8px; background:#f59e0b; color:#0f172a; font-weight:900; font-size:0.8rem; text-decoration:none;">👁️ مراجعة وتمرير في الترحيل</a>`
                    : `<a href="general-ledger.php?accountId=${encodeURIComponent(custId ? ('cust-' + String(custId).replace(/^cust-/, '')) : '')}&name=${encodeURIComponent(custName)}" style="flex:1; text-align:center; padding:6px 10px; border-radius:8px; background:#10b981; color:#fff; font-weight:900; font-size:0.8rem; text-decoration:none;">📊 فتح كشف حساب ${escapeHtml(custName)}</a>`
                }
                <button type="button" onclick="InboundBankakManager.toggleDrawer(); this.parentElement.parentElement.remove();" style="padding:6px 12px; border-radius:8px; background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#e0f2fe; font-weight:800; font-size:0.8rem; cursor:pointer;">📥 صينية الاستلام</button>
            </div>
        `;
        document.body.appendChild(div);
        setTimeout(() => { if (div.parentElement) div.remove(); }, 18000);
    },

    playNotificationChime: function() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            // Two-tone cheerful bank chime: 880Hz then 1320Hz
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.setValueAtTime(1320, ctx.currentTime + 0.12);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.5);
        } catch(e) {}
    },

    showLiveInboundToast: function(receipt) {
        if (!receipt) return;
        const isAwaiting = receipt.issue_reason === 'awaiting_posting_approval';
        const toastId = 'inbound-toast-' + Date.now();
        const div = document.createElement('div');
        div.id = toastId;
        div.style.cssText = `
            position: fixed;
            top: 24px;
            right: 24px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            border: 2px solid ${isAwaiting ? '#f59e0b' : '#10b981'};
            border-radius: 16px;
            padding: 14px 18px;
            color: #fff;
            box-shadow: 0 15px 40px rgba(0,0,0,0.8), 0 0 25px rgba(16,185,129,0.5);
            z-index: 99999999;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            animation: slideInRightToast 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        `;
        const vBadge = isAwaiting
            ? `<span style="font-size:0.75rem; background:rgba(245,158,11,0.25); border:1px solid #f59e0b; color:#fde047; padding:1px 6px; border-radius:6px;">👁️ بانتظار التمرير (${escapeHtml(receipt.voucher_no || '')})</span>`
            : (receipt.voucher_no ? `<span style="font-size:0.75rem; background:rgba(16,185,129,0.25); border:1px solid #10b981; color:#6ee7b7; padding:1px 6px; border-radius:6px;">نزل بالحسابات (${escapeHtml(receipt.voucher_no)})</span>` : `<span style="font-size:0.75rem; background:rgba(56,189,248,0.2); color:#7dd3fc; padding:1px 6px; border-radius:6px;">بنكك</span>`);
        div.innerHTML = `
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(16,185,129,0.25); border:1.8px solid #34d399; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0;">
                📥
            </div>
            <div style="display:flex; flex-direction:column; gap:2px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <strong style="color:#34d399; font-size:0.95rem;">⚡ إشعار بنكك جديد تم فحصه!</strong>
                    ${vBadge}
                </div>
                <span style="font-size:0.86rem; color:#f8fafc; font-weight:800;">من: ${escapeHtml(receipt.customer_name)}</span>
                <span style="font-size:0.80rem; color:#cbd5e1;">المبلغ: <strong style="color:#fef08a;">${Number(receipt.amount||0).toLocaleString()} SDG</strong> | انقر للمراجعة 🚀</span>
            </div>
            <button type="button" onclick="event.stopPropagation(); this.parentElement.remove()" style="background:none; border:none; color:#94a3b8; font-size:1.2rem; cursor:pointer; padding:0 4px;">✕</button>
        `;
        div.onclick = () => {
            div.remove();
            this.toggleDrawer();
        };
        document.body.appendChild(div);
        setTimeout(() => { if (div.parentElement) div.remove(); }, 12000);
    },

    updateStats: function(stats) {
        if (!stats) return;
        const badReasons = ['non_financial_image', 'cropped_missing_ref', 'zero_amount', 'invalid_transfer_no_under_6_digits'];
        const cleanReceipts = (this.receipts || []).filter(r => r.status !== 'duplicate_rejected' && !badReasons.includes(r.issue_reason));
        const isPostedRec = (r) => (r.status === 'verified' || r.status === 'reconciled') && !!(r.voucher_no);
        const verified = cleanReceipts.filter(isPostedRec).length;
        const awaiting = cleanReceipts.filter(r => !isPostedRec(r)).length;
        const pending = 0;
        const total = cleanReceipts.length;
        const dup = 0;

        // Top bar badge
        const badge = document.getElementById('inbound-receipts-badge-count');
        if (badge) {
            badge.textContent = total;
            badge.style.display = total > 0 ? 'inline-block' : 'none';
            if (dup > 0) {
                badge.style.background = '#ef4444';
            } else if (awaiting > 0) {
                badge.style.background = '#f59e0b';
            } else {
                badge.style.background = '#10b981';
            }
        }

        // Header Drawer stats
        const elVer = document.getElementById('stat-verified-count');
        if (elVer) elVer.textContent = verified;
        const elAw = document.getElementById('stat-awaiting-count');
        if (elAw) elAw.textContent = awaiting;
        const elTabAw = document.getElementById('tab-awaiting-count');
        if (elTabAw) elTabAw.textContent = awaiting;
        const elDup = document.getElementById('stat-duplicate-count');
        if (elDup) elDup.textContent = dup;
        const elPen = document.getElementById('stat-pending-count');
        if (elPen) elPen.textContent = pending;
        const elAll = document.getElementById('tab-all-count');
        if (elAll) elAll.textContent = total;
    },

    filterTab: function(filter, btn) {
        this.activeFilter = filter;
        const tabPalette = {
            all: { bd: '#38bdf8', bg: 'rgba(2,132,199,0.22)', fg: '#e0f2fe', actBg: 'rgba(2,132,199,0.42)' },
            awaiting_approval: { bd: '#c084fc', bg: 'rgba(124,58,237,0.24)', fg: '#f3e8ff', actBg: 'rgba(124,58,237,0.48)' },
            pending_scan: { bd: '#facc15', bg: 'rgba(202,138,4,0.18)', fg: '#fef08a', actBg: 'rgba(202,138,4,0.40)' },
            verified: { bd: '#34d399', bg: 'rgba(5,150,105,0.20)', fg: '#a7f3d0', actBg: 'rgba(5,150,105,0.42)' },
            duplicate_rejected: { bd: '#f87171', bg: 'rgba(220,38,38,0.18)', fg: '#fecaca', actBg: 'rgba(220,38,38,0.40)' }
        };
        document.querySelectorAll('.drawer-tab-btn').forEach(b => {
            const f = b.getAttribute('data-filter') || 'all', c = tabPalette[f] || tabPalette.all;
            const isAct = (b === btn);
            b.classList.toggle('active', isAct);
            b.style.borderColor = c.bd;
            b.style.background = isAct ? c.actBg : c.bg;
            b.style.color = isAct ? '#ffffff' : c.fg;
            b.style.boxShadow = isAct ? `0 0 0 2px ${c.bd}55` : 'none';
        });
        this.renderList();
    },

    renderList: function() {
        const container = document.getElementById('inbound-receipts-list-container');
        if (!container) return;
        this.syncHeaderBankFromRows();

        const badReasons = ['non_financial_image', 'cropped_missing_ref', 'zero_amount', 'invalid_transfer_no_under_6_digits'];
        let filtered = (this.receipts || []).filter(r => r.status !== 'duplicate_rejected' && !badReasons.includes(r.issue_reason));
        const isPostedRec = (r) => (r.status === 'verified' || r.status === 'reconciled') && !!(r.voucher_no);
        if (this.activeFilter !== 'all') {
            if (this.activeFilter === 'verified') {
                filtered = filtered.filter(isPostedRec);
            } else if (this.activeFilter === 'awaiting_approval' || this.activeFilter === 'pending_scan') {
                filtered = filtered.filter(r => !isPostedRec(r));
            } else {
                filtered = filtered.filter(r => r.status === this.activeFilter);
            }
        } else {
            // Unposted ("انتظار") receipts ALWAYS appear first at the top, followed by posted ("مرحّل") receipts
            filtered.sort((a, b) => {
                const pa = isPostedRec(a) ? 1 : 0;
                const pb = isPostedRec(b) ? 1 : 0;
                if (pa !== pb) return pa - pb;
                return (Number(b.id) || 0) - (Number(a.id) || 0);
            });
        }

        if (filtered.length === 0) {
            container.innerHTML = `<div style="text-align:center; padding:40px 10px; color:#94a3b8;"><strong style="font-size:0.98rem; color:#f8fafc; display:block;">لا توجد إشعارات في هذا التصنيف</strong></div>`;
            return;
        }

        const allAccs = this.getAllReceivingAccounts();
        const resolveAcc = (curCode, curName) => {
            const c = String(curCode || '').trim(), n = String(curName || '').trim();
            const hit = allAccs.find(x => (c && c !== '170' && (x.val === c || x.rawId === c.replace(/^(bank|supp|cust|safe|wh|exp|emp)-/, ''))) || (n && x.name === n));
            return hit ? { val: hit.val, name: hit.name, lbl: `${hit.icon} ${hit.name}` } : { val: c || '', name: n || 'بنك الخرطوم', lbl: `🏦 ${n || 'بنك الخرطوم'}` };
        };
        const sep = 'border-left:1px solid rgba(148,163,184,0.25);';
        const rowsHtml = filtered.map((r, idx) => {
            const isDup = r.status === 'duplicate_rejected', isVer = r.status === 'verified' || r.status === 'reconciled';
            const isPosted = isVer && !!(r.voucher_no), amtNum = parseFloat(r.amount || 0);
            const editDraft = (this._editingRows && this._editingRows[Number(r.id)]) || null;
            const isUnlocked = !isPosted || !!editDraft;
            const canEditFrom = !!editDraft;
            const rowBg = isDup ? 'rgba(127,29,29,0.22)' : (isPosted ? 'rgba(6,78,59,0.18)' : 'rgba(88,28,135,0.22)');
            const isPrevActive = Number(this._previewingReceiptId || 0) === Number(r.id);
            const effRowBg = isPrevActive ? 'rgba(245,158,11,0.30)' : rowBg;
            const effRowOutline = isPrevActive ? 'outline:2px solid #fbbf24;outline-offset:-2px;' : '';
            const numBg = isDup ? 'background:#dc2626;border:1.5px solid #f87171;'
                : (isPosted ? 'background:#059669;border:1.5px solid #34d399;'
                : 'background:#7c3aed;border:1.5px solid #c084fc;');
            const numTitle = isDup ? 'مكرر' : (isPosted ? 'مرحّل' : 'انتظار');
            const fmtAmt = amtNum.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            let dtShort = String(r.receipt_date || r.transfer_date || '').split(' ')[0] || '';
            if (!dtShort && r.notes) { const dm = String(r.notes).match(/\b(20\d{2}[-/]\d{2}[-/]\d{2})\b/); if (dm) dtShort = dm[1].replace(/\//g, '-'); }
            if (!dtShort) dtShort = String(r.created_at || '').split(' ')[0] || '';
            const recAcc = editDraft && editDraft.newRecName ? { val: editDraft.newRecId, name: editDraft.newRecName } : resolveAcc(r.bank_code, r.bank_name);
            const curLbl = escapeHtml(recAcc.name || 'بنك الخرطوم');
            const phoneTxt = String((editDraft && editDraft.newCustPhone) || r.customer_phone || '').trim();
            const dispCustRaw = (editDraft && editDraft.newCustName) ? editDraft.newCustName : r.customer_name;
            const rawCustName = String(dispCustRaw || 'غير محدد').replace(/^[🏦🚚👥🗄️🏬💸👔👤]+\s*/, '').replace(/\s*[-•|]\s*تحويل.*$/i, '').trim() || 'غير محدد';
            const cleanName = escapeHtml(rawCustName);
            const dynFs = (len, s1, s2, s3, s4) => (len > 30 ? s4 : (len > 22 ? s3 : (len > 15 ? s2 : s1)));
            const nameFs = dynFs(rawCustName.length, '0.90rem', '0.80rem', '0.72rem', '0.64rem');
            const recFs = dynFs((recAcc.name || '').length, '0.85rem', '0.76rem', '0.68rem', '0.60rem');
            const refStr = String(r.transfer_no || '---');
            const refFs = refStr.length > 15 ? '0.76rem' : (refStr.length > 11 ? '0.86rem' : '0.95rem');
            const amtFs = fmtAmt.length > 11 ? '0.86rem' : (fmtAmt.length > 8 ? '0.96rem' : '1.06rem');
            const btnCss = 'height:22px;padding:0 4.5px;border-radius:5px;font-size:0.65rem;font-family:inherit;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;text-decoration:none;flex-shrink:0;';
            const rcCustId = r.customer_id ? ('cust-' + String(r.customer_id).replace(/^cust-/, '')) : '';
            const rawNotesStr = String(r.notes || '').replace(/\s*\|\s*/g, ' • ').trim();
            const commentClean = rawNotesStr
                .replace(/إشعار تحويل وارد من واتساب\s*/gi, '')
                .replace(/تم الترحيل تلقائياً[^\•\|]*/gi, '')
                .trim();
            const tipComment = escapeHtml(commentClean || 'لا يوجد تعليق مسجل في هذا الإشعار');
            const receiverRaw = String(r.receiver_name || r.receiver_account || r.beneficiary_name || r.to_account_name || r.bank_name || recAcc.name || 'غير محدد في الإشعار').trim();
            const receiverBankExtra = (r.bank_name && !receiverRaw.includes(r.bank_name)) ? (' — ' + r.bank_name) : '';
            const tipReceiver = escapeHtml(receiverRaw + receiverBankExtra);
            return `<tr data-row-id="${r.id}" data-orig-bg="${rowBg}" style="background:${effRowBg};border-bottom:1px solid rgba(148,163,184,0.22);${effRowOutline}" onmouseenter="if(Number(InboundBankakManager._previewingReceiptId||0)!==${r.id})this.style.background='rgba(56,189,248,0.13)'" onmouseleave="if(Number(InboundBankakManager._previewingReceiptId||0)!==${r.id})this.style.background='${rowBg}'">
                <td style="padding:5px 4px;box-sizing:border-box;text-align:center;overflow:hidden;${sep}" title="${numTitle}"><span style="display:inline-flex;align-items:center;justify-content:center;min-width:26px;height:22px;padding:0 4px;border-radius:6px;color:#ffffff;font-weight:900;font-size:0.78rem;font-variant-numeric:tabular-nums;${numBg}">${idx + 1}</span></td>
                <td class="inbound-name-cell" data-rc-account-id="${escapeHtml(rcCustId)}" data-rc-account-name="${cleanName}" data-rc-account-phone="${escapeHtml(phoneTxt)}" data-rc-account-type="customer" title="${cleanName} — كليك يمين للخيارات" style="padding:5px 5px;box-sizing:border-box;text-align:center;cursor:context-menu;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;${sep}">
                    <span style="font-family:'Cairo','Tajawal',sans-serif;font-weight:800;color:#ffffff;font-size:${nameFs};display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${cleanName}</span>
                </td>
                <td data-hover-label="الحساب المرسل إليه في الإشعار" data-hover-text="${tipReceiver}" onmouseenter="InboundBankakManager.showSmartHoverTip(this)" onmouseleave="InboundBankakManager.hideSmartHoverTip()" style="padding:5px 5px;box-sizing:border-box;overflow:hidden;${sep}"><div style="position:relative;width:100%;"><input type="text" class="inbound-row-rec-input" data-row-id="${r.id}" data-rc-account-id="${escapeHtml(recAcc.val)}" data-rc-account-name="${escapeHtml(recAcc.name)}" data-acc-val="${escapeHtml(recAcc.val)}" data-acc-name="${escapeHtml(recAcc.name)}" value="${curLbl}" data-cur-label="${curLbl}" autocomplete="off" ${isUnlocked ? `onfocus="this.select();InboundBankakManager.openAccountSearchMenu(this, ${r.id})" oninput="InboundBankakManager.openAccountSearchMenu(this, ${r.id})"` : 'readonly'} placeholder="ابحث عن إلى حساب..." style="width:100%;height:26px;box-sizing:border-box;background:${isUnlocked ? '#0f172a' : 'rgba(100,116,139,0.42)'};color:${isUnlocked ? '#fde047' : '#cbd5e1'};border:${isUnlocked ? '1.4px solid rgba(245,158,11,0.68)' : '1px solid rgba(148,163,184,0.48)'};border-radius:7px;padding:0 ${isUnlocked ? '5px' : '22px'} 0 5px;font-family:'Cairo','Tajawal',sans-serif;font-size:${recFs};font-weight:${isUnlocked ? '900' : '700'};text-align:center;outline:none;cursor:${isUnlocked ? 'text' : 'not-allowed'};overflow:hidden;text-overflow:ellipsis;">${!isUnlocked ? `<span style="position:absolute;left:5px;top:50%;transform:translateY(-50%);font-size:0.70rem;color:#94a3b8;pointer-events:none;">🔒</span>` : ''}</div></td>
                <td data-hover-label="التعليق في الإشعار" data-hover-text="${tipComment}" onmouseenter="InboundBankakManager.showSmartHoverTip(this)" onmouseleave="InboundBankakManager.hideSmartHoverTip()" style="padding:5px 5px;box-sizing:border-box;text-align:center;direction:ltr;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:help;${sep}"><div style="color:#7dd3fc;font-family:'Inter','Cairo',sans-serif;font-variant-numeric:tabular-nums;font-weight:900;font-size:${refFs};overflow:hidden;text-overflow:ellipsis;">${escapeHtml(refStr)}</div><div style="font-size:0.69rem;color:#cbd5e1;font-weight:800;font-variant-numeric:tabular-nums;">${escapeHtml(dtShort)}</div></td>
                <td style="padding:5px 5px;box-sizing:border-box;text-align:center;direction:ltr;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;${sep}"><strong style="color:#fde047;font-size:${amtFs};font-family:'Inter','Cairo',sans-serif;font-variant-numeric:tabular-nums;font-weight:900;">${fmtAmt}</strong></td>
                <td style="padding:5px 4px;box-sizing:border-box;overflow:visible;"><div style="display:flex;align-items:center;justify-content:center;gap:2.5px;flex-wrap:nowrap;overflow:visible;">
                    <button type="button" onclick="InboundBankakManager.openSidePreviewById(${r.id})" style="width:21px;height:22px;padding:0;border-radius:5px;background:rgba(14,165,233,0.28);border:1.2px solid #38bdf8;color:#ffffff;cursor:pointer;font-size:0.72rem;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;" title="معاينة فورية أقصى اليمين للمقارنة">👁</button>
                    ${(!isPosted && !isDup) ? `<button type="button" onclick="InboundBankakManager.postToAccountsDirect(${r.id}, this)" style="${btnCss}background:linear-gradient(135deg,#10b981,#059669);border:1.1px solid #6ee7b7;color:#fff;">ترحيل</button>` : ''}
                    ${isPosted ? ((editDraft && editDraft.dirty) ? `<button type="button" onclick="InboundBankakManager.confirmSaveEditedRow(${r.id}, this)" style="${btnCss}background:linear-gradient(135deg,#10b981,#059669);border:1.2px solid #6ee7b7;color:#fff;">حفظ</button>` : `<button type="button" onclick="InboundBankakManager.unlockOrEditRow(${r.id})" style="${btnCss}background:linear-gradient(135deg,#fbbf24,#d97706);border:1.1px solid #fef08a;color:#0f172a;">تعديل</button>`) : ''}
                    <button type="button" onclick="InboundBankakManager.inspectInVoucher(${r.id})" style="${btnCss}background:rgba(59,130,246,0.22);border:1px solid #60a5fa;color:#dbeafe;">السند</button>
                    <button type="button" onclick="InboundBankakManager.previewReceiptById(${r.id})" style="${btnCss}background:rgba(20,184,166,0.20);border:1px solid #2dd4bf;color:#ccfbf1;">صورة</button>
                    <button type="button" onclick="InboundBankakManager.checkForgeryOnDemand(${r.id}, this)" style="${btnCss}background:rgba(168,85,247,0.22);border:1px solid #c084fc;color:#f3e8ff;">تزوير</button>
                    <button type="button" onclick="InboundBankakManager.rescanReceiptById(${r.id}, this)" style="width:20px;height:22px;padding:0;border-radius:5px;background:rgba(250,204,21,0.18);border:1.1px solid #facc15;color:#fef08a;cursor:pointer;font-size:0.66rem;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;" title="إعادة قراءة الإشعار (تحديث البيانات)">🔄</button>
                    <button type="button" onclick="InboundBankakManager.dismissReceipt(${r.id})" style="width:20px;height:22px;padding:0;border-radius:5px;background:rgba(239,68,68,0.18);border:1px solid rgba(239,68,68,0.45);color:#fca5a5;cursor:pointer;font-weight:900;font-size:0.70rem;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;" title="حذف">✕</button>
                </div></td>
            </tr>`;
        }).join('');
        container.innerHTML = `<div style="border:1.5px solid rgba(56,189,248,0.38);border-radius:10px;overflow-y:auto;overflow-x:hidden;background:rgba(2,6,23,0.78);width:100%;box-sizing:border-box;">
            <table style="width:100%;table-layout:fixed;border-collapse:collapse;text-align:center;">
                <colgroup>
                    <col style="width:4%;">
                    <col style="width:19.5%;">
                    <col style="width:20.5%;">
                    <col style="width:14.5%;">
                    <col style="width:12%;">
                    <col style="width:29.5%;">
                </colgroup>
                <thead><tr style="background:linear-gradient(90deg,#0f172a,#1e293b);border-bottom:2px solid #38bdf8;color:#7dd3fc;font-family:'Cairo','Tajawal',sans-serif;font-size:0.85rem;font-weight:900;position:sticky;top:0;z-index:5;">
                    <th style="padding:8px 4px;text-align:center;${sep}">#</th>
                    <th style="padding:8px 6px;text-align:center;${sep}">من حساب</th>
                    <th style="padding:8px 6px;text-align:center;${sep}">إلى حساب</th>
                    <th style="padding:8px 6px;text-align:center;${sep}">رقم العملية والتاريخ</th>
                    <th style="padding:8px 6px;text-align:center;${sep}">المبلغ</th>
                    <th style="padding:8px 6px;text-align:center;">خيارات الإجراء</th>
                </tr></thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        </div>`;
    },

    showSmartHoverTip: function(cellEl) {
        if (!cellEl) return;
        const lbl = cellEl.getAttribute('data-hover-label') || '';
        const txt = cellEl.getAttribute('data-hover-text') || '';
        if (!txt) return;
        let tip = document.getElementById('inbound-smart-hover-tip');
        if (!tip) {
            tip = document.createElement('div');
            tip.id = 'inbound-smart-hover-tip';
            tip.style.cssText = 'position:fixed;z-index:2147483647;pointer-events:none;max-width:340px;padding:8px 12px;border-radius:10px;background:#0b1224;border:1.8px solid #38bdf8;color:#ffffff;font-family:Cairo,Tajawal,sans-serif;direction:rtl;text-align:right;box-shadow:0 12px 28px rgba(0,0,0,0.75);line-height:1.45;';
            document.body.appendChild(tip);
        }
        tip.innerHTML = `<div style="font-size:0.74rem;color:#7dd3fc;font-weight:900;margin-bottom:3px;">${lbl}</div><div style="font-size:0.88rem;color:#ffffff;font-weight:800;word-break:break-word;">${txt}</div>`;
        tip.style.display = 'block';
        const r = cellEl.getBoundingClientRect();
        const th = tip.offsetHeight || 58;
        const tw = tip.offsetWidth || 240;
        // Position strictly above or below so it never covers the date, transfer number, or target account box
        const topPos = (r.top - th - 10 > 8) ? (r.top - th - 10) : (r.bottom + 10);
        const leftPos = Math.max(10, Math.min(window.innerWidth - tw - 10, r.left + (r.width - tw) / 2));
        tip.style.top = topPos + 'px';
        tip.style.left = leftPos + 'px';
    },
    hideSmartHoverTip: function() {
        const tip = document.getElementById('inbound-smart-hover-tip');
        if (tip) tip.style.display = 'none';
    },

    openAssignCustomerById: function(receiptId) {
        const r = (this.receipts || []).find(x => Number(x.id) === Number(receiptId));
        if (!r) return;
        const m = document.getElementById('inbound-assign-customer-modal');
        if (m && m.parentElement !== document.body) document.body.appendChild(m);
        if (typeof this.openAssignCustomerModal === 'function') {
            this.openAssignCustomerModal(r.id, r.customer_name || '', r.customer_phone || '', r.amount || 0, r.transfer_no || '');
        }
    },

    unlockOrEditRow: function(receiptId) {
        const rid = Number(receiptId);
        if (!this._editingRows) this._editingRows = {};
        if (this._editingRows[rid] && !this._editingRows[rid].dirty) { delete this._editingRows[rid]; this.renderList(); return; }
        const r = (this.receipts || []).find(x => Number(x.id) === rid) || {};
        const fmtAmt = parseFloat(r.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        const origCust = String(r.customer_name || 'غير محدد').replace(/^[🏦🚚👥🗄️🏬💸👔👤]+\s*/, '').replace(/\s*[-•|]\s*تحويل.*$/i, '').trim() || 'غير محدد';
        const hit = this.getAllReceivingAccounts().find(x => (r.bank_code && r.bank_code !== '170' && x.val === r.bank_code) || (r.bank_name && x.name === r.bank_name));
        const origRecId = hit ? hit.val : (r.bank_code || '');
        const origRecName = hit ? hit.name : (r.bank_name || 'بنك الخرطوم');
        const mid = 'inbound-edit-warn-modal'; if (document.getElementById(mid)) document.getElementById(mid).remove();
        const d = document.createElement('div'); d.id = mid;
        d.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:rgba(2,6,23,0.88);display:flex;align-items:center;justify-content:center;padding:16px;font-family:Cairo,Tajawal,sans-serif;direction:rtl;';
        d.innerHTML = `<div style="width:min(490px,94vw);background:linear-gradient(155deg,#450a0a,#1a0508);border:2.2px solid #ef4444;border-radius:16px;padding:20px 22px;color:#fff;box-shadow:0 28px 70px rgba(0,0,0,0.95);text-align:center;"><h3 style="margin:0 0 10px;font-size:1.15rem;font-weight:900;color:#fca5a5;">تنبيه هام قبل التعديل</h3><div style="font-size:0.94rem;color:#fee2e2;line-height:1.75;margin-bottom:18px;font-weight:800;background:rgba(220,38,38,0.22);padding:12px;border-radius:10px;border:1.4px solid rgba(248,113,113,0.55);">تنبيه هام: سوف يتم حذف الرصيد (<strong style="color:#fde047;">${fmtAmt}</strong>) من حساب (<strong style="color:#ffffff;">${escapeHtml(origRecName)}</strong>) وإضافته في الحساب الجديد فور الحفظ.</div><div style="display:flex;justify-content:center;gap:10px;"><button type="button" id="inbound-warn-cancel" style="height:38px;padding:0 22px;border-radius:9px;background:rgba(148,163,184,0.18);border:1.4px solid rgba(148,163,184,0.45);color:#e2e8f0;font-family:inherit;font-weight:900;font-size:0.9rem;cursor:pointer;">إلغاء</button><button type="button" id="inbound-warn-ok" style="height:38px;padding:0 26px;border-radius:9px;background:linear-gradient(135deg,#dc2626,#991b1b);border:1.5px solid #fca5a5;color:#ffffff;font-family:inherit;font-weight:900;font-size:0.92rem;cursor:pointer;">موافق ومتابعة التعديل</button></div></div>`;
        document.body.appendChild(d);
        d.querySelector('#inbound-warn-cancel').onclick = () => d.remove();
        d.querySelector('#inbound-warn-ok').onclick = () => {
            d.remove();
            this._editingRows[rid] = { origCustName: origCust, origRecId, origRecName, newRecId: origRecId, newRecName: origRecName, dirty: false };
            this.renderList();
            setTimeout(() => { const inp = document.querySelector(`.inbound-row-rec-input[data-row-id="${rid}"]`); if (inp) { inp.focus(); inp.select(); } }, 30);
        };
    },

    confirmSaveEditedRow: async function(receiptId, btnEl) {
        const rid = Number(receiptId), ed = this._editingRows && this._editingRows[rid];
        const r = (this.receipts || []).find(x => Number(x.id) === rid);
        if (!ed || !r) return;
        const fmtAmt = parseFloat(r.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        const summaryLines = [];
        if (ed.newCustName && ed.newCustName !== ed.origCustName) summaryLines.push(`تم نقل (من حساب) من <strong>${escapeHtml(ed.origCustName)}</strong> إلى <strong>${escapeHtml(ed.newCustName)}</strong>`);
        if (ed.newRecName && ed.newRecName !== ed.origRecName) summaryLines.push(`تم نقل (إلى حساب) من <strong>${escapeHtml(ed.origRecName)}</strong> إلى <strong>${escapeHtml(ed.newRecName)}</strong>`);
        if (!summaryLines.length) { delete this._editingRows[rid]; this.renderList(); return; }
        if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = 'جاري...'; }
        if (ed.newCustPayload) await this._execAssignCustomerPayload(ed.newCustPayload);
        if (ed.newRecName && ed.newRecName !== ed.origRecName) await this.changeTargetBank(rid, ed.newRecId, ed.newRecName);
        delete this._editingRows[rid];
        await this.refreshReceipts();
        const sid = 'inbound-edit-done-modal'; if (document.getElementById(sid)) document.getElementById(sid).remove();
        const sd = document.createElement('div'); sd.id = sid;
        sd.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:rgba(2,6,23,0.82);display:flex;align-items:center;justify-content:center;padding:16px;font-family:Cairo,Tajawal,sans-serif;direction:rtl;';
        sd.innerHTML = `<div style="width:min(470px,94vw);background:linear-gradient(155deg,#064e3b,#022c22);border:2px solid #34d399;border-radius:16px;padding:20px 22px;color:#fff;box-shadow:0 28px 70px rgba(0,0,0,0.95);text-align:center;"><h3 style="margin:0 0 10px;font-size:1.12rem;font-weight:900;color:#6ee7b7;">تم حفظ ونقل الحساب بنجاح</h3><div style="font-size:0.93rem;color:#ecfdf5;line-height:1.75;margin-bottom:16px;font-weight:800;background:rgba(16,185,129,0.18);padding:12px;border-radius:10px;border:1px solid rgba(110,231,183,0.45);">المبلغ: <strong style="color:#fde047;">${fmtAmt}</strong><br>${summaryLines.join('<br>')}</div><button type="button" onclick="document.getElementById('${sid}').remove()" style="height:36px;padding:0 28px;border-radius:9px;background:#10b981;border:1.4px solid #a7f3d0;color:#fff;font-family:inherit;font-weight:900;font-size:0.92rem;cursor:pointer;">موافق</button></div>`;
        document.body.appendChild(sd);
    },

    _highlightPreviewRow: function(receiptId) {
        this._previewingReceiptId = receiptId ? Number(receiptId) : 0;
        document.querySelectorAll('#inbound-receipts-list-container tr[data-row-id]').forEach(tr => {
            const isHit = Number(tr.getAttribute('data-row-id')) === this._previewingReceiptId && this._previewingReceiptId > 0;
            tr.style.background = isHit ? 'rgba(245,158,11,0.32)' : (tr.getAttribute('data-orig-bg') || '');
            tr.style.outline = isHit ? '2px solid #fbbf24' : 'none';
            tr.style.outlineOffset = isHit ? '-2px' : '';
            if (isHit) tr.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    },

    openSidePreviewById: function(receiptId) {
        const list = (this.receipts || []).filter(r => r.status !== 'duplicate_rejected');
        const idx = list.findIndex(x => Number(x.id) === Number(receiptId));
        const r = idx >= 0 ? list[idx] : null;
        if (!r || !r.receipt_image) {
            if (typeof showToast === 'function') showToast('لا توجد صورة مرفقة لهذا الإشعار', 'warning');
            return;
        }
        this._highlightPreviewRow(r.id);
        const pid = 'inbound-side-preview-panel';
        if (document.getElementById(pid)) document.getElementById(pid).remove();
        const imgUrl = r.receipt_image;
        const cloudFallback = imgUrl.startsWith('http') ? imgUrl : ('https://c.elsajana.com/' + String(imgUrl).replace(/^\/+/, ''));
        const rawCust = String(r.customer_name || 'غير محدد').replace(/^[🏦🚚👥🗄️🏬💸👔👤]+\s*/, '').replace(/\s*[-•|]\s*تحويل.*$/i, '').trim() || 'غير محدد';
        const fmtAmt = parseFloat(r.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        const prevId = idx > 0 ? list[idx - 1].id : null;
        const nextId = (idx >= 0 && idx < list.length - 1) ? list[idx + 1].id : null;
        const panel = document.createElement('div');
        panel.id = pid;
        panel.style.cssText = 'position:fixed; right:6px; top:6px; bottom:6px; width:min(595px, 48vw); z-index:2147483646; background:#0b1222; border:2px solid #38bdf8; border-radius:12px; padding:8px 10px; display:flex; flex-direction:column; gap:6px; box-shadow:-12px 0 40px rgba(0,0,0,0.88); font-family:Cairo,Tajawal,sans-serif; direction:rtl; box-sizing:border-box;';
        panel.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; border-bottom:1px solid rgba(56,189,248,0.28); padding-bottom:5px; flex-shrink:0;">
                <div style="display:flex; flex-direction:column; min-width:0;">
                    <strong style="color:#f8fafc; font-size:0.86rem; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">👁 #${idx + 1} — ${escapeHtml(rawCust)}</strong>
                    <span style="color:#fde047; font-size:0.75rem; font-weight:900;">المبلغ: ${fmtAmt} | رقم العملية: ${escapeHtml(r.transfer_no || '---')}</span>
                </div>
                <div style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                    <button type="button" ${prevId ? `onclick="InboundBankakManager.openSidePreviewById(${prevId})"` : 'disabled'} style="height:26px; padding:0 8px; border-radius:6px; background:rgba(56,189,248,0.18); border:1px solid #38bdf8; color:#e0f2fe; font-weight:900; font-size:0.74rem; cursor:${prevId ? 'pointer' : 'not-allowed'}; opacity:${prevId ? '1' : '0.4'};">◀ السابق</button>
                    <button type="button" ${nextId ? `onclick="InboundBankakManager.openSidePreviewById(${nextId})"` : 'disabled'} style="height:26px; padding:0 8px; border-radius:6px; background:rgba(56,189,248,0.18); border:1px solid #38bdf8; color:#e0f2fe; font-weight:900; font-size:0.74rem; cursor:${nextId ? 'pointer' : 'not-allowed'}; opacity:${nextId ? '1' : '0.4'};">التالي ▶</button>
                    <button type="button" onclick="document.getElementById('${pid}').remove();InboundBankakManager._highlightPreviewRow(0)" style="width:26px; height:26px; border-radius:6px; background:rgba(239,68,68,0.26); border:1.2px solid #ef4444; color:#fecaca; font-weight:900; cursor:pointer;">✕</button>
                </div>
            </div>
            <div style="flex:1; min-height:0; overflow:hidden; background:#020617; border-radius:9px; padding:4px; display:flex; justify-content:center; align-items:center;">
                <img src="${escapeHtml(imgUrl)}" onerror="if(this.src!=='${escapeHtml(cloudFallback)}'){this.src='${escapeHtml(cloudFallback)}';}" alt="Receipt" style="max-width:91%; max-height:100%; width:auto; height:100%; border-radius:7px; object-fit:contain; display:block; transform:scaleX(1.10); transform-origin:center;">
            </div>
        `;
        document.body.appendChild(panel);
    },

    rescanReceiptById: async function(receiptId, btnEl) {
        const origHtml = btnEl ? btnEl.innerHTML : '🔄';
        if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = '⏳'; }
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch(`api/inbound_receipts.php?action=scan_receipt&id=${receiptId}&force_rescan=1&tenant_id=${encodeURIComponent(tId)}`, { method: 'POST' });
            const data = await res.json();
            if (data && data.success) {
                const newAmt = parseFloat(data.data?.amount || 0).toLocaleString('en-US');
                const newRef = data.data?.transfer_no || '---';
                if (typeof showToast === 'function') showToast(`✅ تم تحديث قراءة الإشعار: المبلغ ${newAmt} | رقم العملية ${newRef}`, 'success');
                await this.refreshReceipts(true);
            } else {
                if (typeof showToast === 'function') showToast(`⚠️ تعذر إعادة القراءة: ${data?.error || 'خطأ غير معروف'}`, 'warning');
            }
        } catch (e) {
            if (typeof showToast === 'function') showToast('❌ خطأ أثناء إعادة قراءة الإشعار', 'error');
        } finally {
            if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = origHtml; }
        }
    },

    previewReceiptById: function(receiptId) {
        const r = (this.receipts || []).find(x => Number(x.id) === Number(receiptId));
        if (!r || !r.receipt_image) {
            if (typeof showToast === 'function') showToast('لا توجد صورة مرفقة لهذا الإشعار', 'warning');
            return;
        }
        const rawCustName = String(r.customer_name || 'غير محدد').replace(/^[🏦🚚👥🗄️🏬💸👔👤]+\s*/, '').replace(/\s*[-•|]\s*تحويل.*$/i, '').trim() || 'غير محدد';
        const fmtAmt = parseFloat(r.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        this.previewReceiptImage(r.receipt_image, `إشعار #${r.id} — ${rawCustName} (${fmtAmt})`);
    },

    previewReceiptImage: function(imgUrl, title) {
        if (!imgUrl) return;
        const modalId = 'inbound-receipt-img-preview-modal';
        if (document.getElementById(modalId)) document.getElementById(modalId).remove();
        const cloudFallback = imgUrl.startsWith('http') ? imgUrl : ('https://c.elsajana.com/' + String(imgUrl).replace(/^\/+/, ''));
        const div = document.createElement('div');
        div.id = modalId;
        div.style.cssText = 'position:fixed; inset:0; background:rgba(2,6,23,0.90); z-index:2147483647; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:14px; font-family:Cairo,Tajawal,sans-serif; direction:rtl;';
        div.onclick = (e) => { if (e.target === div) div.remove(); };
        div.innerHTML = `
            <div style="background:#0f172a; border:2px solid #38bdf8; border-radius:16px; padding:14px 18px; width:min(968px, 96vw); max-height:94vh; display:flex; flex-direction:column; gap:10px; box-shadow:0 28px 70px rgba(0,0,0,0.95);">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; border-bottom:1px solid rgba(255,255,255,0.14); padding-bottom:8px;">
                    <strong style="color:#f8fafc; font-size:1.05rem; font-weight:900;">${escapeHtml(title || 'صورة الإشعار البنكي')}</strong>
                    <button type="button" onclick="document.getElementById('${modalId}').remove()" style="background:rgba(239,68,68,0.24); border:1.4px solid #ef4444; color:#fecaca; border-radius:8px; width:34px; height:34px; cursor:pointer; font-weight:900; font-size:1rem;">✕</button>
                </div>
                <div style="flex:1; min-height:0; overflow:hidden; display:flex; justify-content:center; align-items:center; background:#020617; border-radius:12px; padding:8px;">
                    <img src="${escapeHtml(imgUrl)}" onerror="if(this.src!=='${escapeHtml(cloudFallback)}'){this.src='${escapeHtml(cloudFallback)}';}" alt="Receipt" style="max-width:91%; max-height:84vh; width:auto; height:84vh; border-radius:10px; object-fit:contain; display:block; transform:scaleX(1.10); transform-origin:center;">
                </div>
            </div>
        `;
        document.body.appendChild(div);
    },

    changeTargetBank: async function(receiptId, bankId, bankName = '') {
        if (!bankId) return;
        const hit = this.getAllReceivingAccounts().find(x => x.val === bankId);
        const resolvedName = bankName || (hit ? hit.name : '');
        const rLocal = this.receipts.find(x => Number(x.id) === Number(receiptId));
        if (rLocal) { rLocal.bank_code = bankId; rLocal.bank_name = resolvedName; }
        this.syncHeaderBankFromRows();
        const tId = this.getActiveTenantId();
        try {
            const res = await fetch('api/inbound_receipts.php?action=change_target_bank&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'change_target_bank', tenant_id: tId, id: receiptId, bank_id: bankId, bank_name: resolvedName })
            });
            const data = await res.json();
            if (data && data.success) {
                if (rLocal) {
                    const prevVNo = rLocal.voucher_no || '';
                    rLocal.bank_code = data.bank_id || bankId;
                    rLocal.bank_name = data.bank_name || resolvedName;
                    if (data.posting && data.posting.voucher_no) {
                        rLocal.voucher_no = data.posting.voucher_no;
                        if (prevVNo && prevVNo !== rLocal.voucher_no) {
                            try {
                                const getTK = window.getTenantKey || ((k) => k);
                                ['erp_receipts_v1', getTK('erp_receipts_v1')].forEach(k => {
                                    const arr = JSON.parse(localStorage.getItem(k) || '[]');
                                    if (Array.isArray(arr)) localStorage.setItem(k, JSON.stringify(arr.filter(v => v && String(v.id) !== String(prevVNo) && String(v.receiptNo) !== String(prevVNo))));
                                });
                            } catch (_) {}
                        }
                    }
                    if (rLocal.voucher_no) this.syncPostedReceiptToLocalErp(rLocal, data.posting || null);
                }
                this.syncHeaderBankFromRows();
                if (typeof showToast === 'function') showToast(`✅ تم تحديد الحساب المستلم: ${data.bank_name || resolvedName}`, 'success');
            }
        } catch (e) {}
    },

    approveAllPendingReceipts: async function(btnEl) {
        const candidates = this.receipts.filter(r =>
            r.status !== 'duplicate_rejected' &&
            !( (r.status === 'verified' || r.status === 'reconciled') && !!r.voucher_no )
        );
        if (candidates.length === 0) {
            if (typeof showToast === 'function') showToast('✅ جميع الإشعارات السليمة مرحّلة بالفعل!', 'info');
            return;
        }
        const uniformBankId = document.getElementById('drawer-target-bank-select')?.value || '';
        const uniformBankName = (document.getElementById('drawer-target-acc-filter')?.value || '').replace(/^[🏦🚚👥🗄️🏬💸👔🔍]\s*/, '').trim();
        const perRowTargets = {};
        candidates.forEach(c => {
            const inp = document.querySelector(`.inbound-row-rec-input[data-row-id="${c.id}"]`);
            perRowTargets[c.id] = {
                bank_id: (inp && inp.dataset.accVal) || c.bank_code || uniformBankId || '',
                bank_name: (inp && inp.dataset.accName) || c.bank_name || uniformBankName || ''
            };
        });
        const origHtml = btnEl ? btnEl.innerHTML : '';
        if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = '<span>⏳</span> <span>جاري ترحيل الكل...</span>'; }
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch('api/inbound_receipts.php?action=approve_batch&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'approve_batch',
                    tenant_id: tId,
                    ids: candidates.map(c => Number(c.id)),
                    bank_id: uniformBankId,
                    bank_name: uniformBankName,
                    row_targets: perRowTargets
                })
            });
            const data = await res.json();
            if (data && data.success) {
                if (Array.isArray(data.results)) {
                    data.results.forEach(postRes => {
                        if (postRes && postRes.success && postRes.voucher_no) {
                            const rObj = this.receipts.find(x => Number(x.id) === Number(postRes.receipt_id)) || { id: postRes.receipt_id, voucher_no: postRes.voucher_no };
                            rObj.status = 'verified'; rObj.issue_reason = null; rObj.voucher_no = postRes.voucher_no;
                            this.syncPostedReceiptToLocalErp(rObj, postRes);
                        }
                    });
                }
                await this.refreshReceipts();
                if (typeof showToast === 'function') {
                    const skippedMsg = (data.skipped_count > 0) ? ` (تعذر ترحيل ${data.skipped_count} لعدم وجود مبلغ)` : '';
                    showToast(`✅ تم ترحيل (${data.approved_count || 0}) إشعار بنجاح${skippedMsg}`, 'success', 5000);
                }
            } else {
                alert(data?.error || 'تعذر ترحيل الإشعارات');
            }
        } catch (e) {
            alert('خطأ أثناء الترحيل: ' + e.message);
        } finally {
            if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = origHtml || '<span>✔️</span> <span>ترحيل الكل</span>'; }
        }
    },

    loadBatchIntoMultiVoucher: function() {
        let candidates = this.receipts.filter(r =>
            r.status !== 'duplicate_rejected' &&
            (parseFloat(r.amount || 0) > 0 || !!r.receipt_image) &&
            (this.activeFilter === 'all' ? (r.issue_reason === 'awaiting_posting_approval' || r.status === 'pending' || r.status === 'verified') : true)
        );
        const awaitingOnly = candidates.filter(r => r.issue_reason === 'awaiting_posting_approval' || r.status === 'pending');
        if (awaitingOnly.length > 0) {
            candidates = awaitingOnly;
        } else {
            candidates = candidates.slice(0, 20);
        }

        if (candidates.length === 0) {
            alert('لا توجد إشعارات صالحة لإدراجها في شاشة الحركة المالية المجمعة.');
            return;
        }

        const ids = candidates.map(r => Number(r.id));
        if (!document.getElementById('accounts-blocks-container')) {
            sessionStorage.setItem('erp_wa_batch_to_load', JSON.stringify(ids));
            window.location.href = 'multi-cash-payment.php?from_wa_batch=1';
            return;
        }

        this.closeDrawer();
        this._populateBatchIntoCurrentMultiVoucher(ids);
    },

    _populateBatchIntoCurrentMultiVoucher: function(receiptIds) {
        if (!Array.isArray(receiptIds) || receiptIds.length === 0) return;
        const list = receiptIds.map(id => this.receipts.find(r => Number(r.id) === Number(id))).filter(Boolean);
        if (list.length === 0) return;

        if (typeof window.setVoucherType === 'function') {
            window.setVoucherType('receipt');
        }
        if (typeof window.buildAccountsArray === 'function') {
            try { window.buildAccountsArray(); } catch (e) {}
        }

        // Set Main Receiving Bank
        const chosenBankId = document.getElementById('drawer-target-bank-select')?.value || this.selectedBankId || list[0].bank_code || '';
        const mainAccInput = document.getElementById('main-account');
        const mainAccSearch = document.getElementById('main-account-search');
        if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
            let bankAcc = ACCOUNTS.find(a => a.id && a.id.startsWith('bank-') && (
                (chosenBankId && a.id === ('bank-' + String(chosenBankId).replace(/^bank-/, ''))) ||
                (list[0].bank_name && a.name && list[0].bank_name.includes(a.name)) ||
                (a.name && a.name.includes('الخرطوم'))
            )) || ACCOUNTS.find(a => a.id && a.id.startsWith('bank-'));

            if (bankAcc) {
                if (mainAccInput) {
                    mainAccInput.value = bankAcc.id;
                    mainAccInput.setAttribute('data-id', bankAcc.id);
                }
                if (mainAccSearch) {
                    mainAccSearch.value = bankAcc.name;
                }
            }
        }

        const container = document.getElementById('accounts-blocks-container');
        if (!container) return;

        // Ensure we have enough account blocks for each receipt in the batch
        list.forEach((r, idx) => {
            let blocks = container.querySelectorAll('.account-block');
            if (idx >= blocks.length && typeof window.addAccountBlock === 'function') {
                window.addAccountBlock();
                blocks = container.querySelectorAll('.account-block');
            }
            const targetBlock = blocks[idx];
            if (!targetBlock) return;
            const blockId = targetBlock.getAttribute('data-block-id');

            const rawCustId = String(r.customer_id || '').replace(/^cust-/, '');
            let fullCustAccId = rawCustId ? ('cust-' + rawCustId) : '';
            if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
                const matchedCustAcc = ACCOUNTS.find(a =>
                    (fullCustAccId && String(a.id) === fullCustAccId) ||
                    (r.customer_name && a.name && a.name.trim() === r.customer_name.trim())
                );
                if (matchedCustAcc) fullCustAccId = matchedCustAcc.id;
            }

            const accSearch = document.getElementById(`account-${blockId}-search`);
            const accHidden = document.getElementById(`account-${blockId}`);
            const accId = document.getElementById(`account-${blockId}-id`);
            if (accSearch) accSearch.value = r.customer_name || '';
            if (accHidden) accHidden.value = fullCustAccId || r.customer_name || '';
            if (accId) accId.value = fullCustAccId || r.customer_name || '';

            const btnCash = document.getElementById(`pm-cash-${blockId}`);
            const btnBank = document.getElementById(`pm-bank-${blockId}`);
            if (btnCash && btnCash.classList.contains('active') && typeof window.toggleBlockPayment === 'function') {
                window.toggleBlockPayment(blockId, 'cash');
            }
            if (btnBank && !btnBank.classList.contains('active') && typeof window.toggleBlockPayment === 'function') {
                window.toggleBlockPayment(blockId, 'bank');
            }

            const subFields = document.getElementById(`sub-fields-${blockId}`);
            const subBank = document.getElementById(`sub-bank-${blockId}`);
            if (subFields) subFields.style.display = 'block';
            if (subBank) subBank.style.display = 'block';

            let bankRow = targetBlock.querySelector('.bank-row');
            if (!bankRow && typeof window.addBankRow === 'function') {
                window.addBankRow(blockId);
                bankRow = targetBlock.querySelector('.bank-row');
            }
            const cleanAmt = parseFloat(r.amount || 0) || 0;
            const formattedAmtStr = (typeof fmtNum === 'function') ? fmtNum(cleanAmt) : cleanAmt.toLocaleString('en-US');

            if (bankRow) {
                const bAmt = bankRow.querySelector('.block-bank-amount');
                if (bAmt) {
                    bAmt.value = formattedAmtStr;
                    bAmt.dispatchEvent(new Event('input', { bubbles: true }));
                }
                const bTransfer = bankRow.querySelector('.block-transfer-no-input');
                if (bTransfer) {
                    bTransfer.value = r.transfer_no || '';
                    bTransfer.dispatchEvent(new Event('input', { bubbles: true }));
                }
                const bName = bankRow.querySelector('.block-bank-name-input');
                if (bName) {
                    bName.value = r.bank_name || 'بنك الخرطوم (بنكك)';
                }
                const bDesc = bankRow.querySelector('.block-bank-desc-input');
                if (bDesc) {
                    bDesc.value = `إشعار واتساب #${r.id} - ${r.customer_name || ''}`;
                }
            }

            const mainBlockAmt = document.getElementById(`amount-${blockId}`);
            if (mainBlockAmt) mainBlockAmt.value = formattedAmtStr;

            const stmtInput = document.getElementById(`statement-${blockId}`);
            if (stmtInput) stmtInput.value = `إشعار واتساب (${r.bank_name || 'بنكك'}) رقم العملية ${r.transfer_no || ''}`;

            if (typeof window.recalcBlockTotal === 'function') window.recalcBlockTotal(blockId);
            if (typeof window.updateBlockTafqeetAndTier === 'function') window.updateBlockTafqeetAndTier(blockId);
        });

        const vDescInput = document.getElementById('voucher-desc');
        if (vDescInput) {
            vDescInput.value = `دفعة إشعارات واتساب مجمعة (${list.length} إشعار)`;
        }
        if (typeof window.calcTotal === 'function') window.calcTotal();
        if (typeof showToast === 'function') {
            showToast(`📥 تم إدراج (${list.length}) إشعار واتساب داخل شاشة الحركة المالية المجمعة بنجاح!`, 'success', 6000);
        }
    },

    syncMasterDataFromServer: async function() {
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch('api/get_master_data.php?tenant_id=' + encodeURIComponent(tId) + '&_t=' + Date.now());
            const data = await res.json();
            if (data && data.status === 'success') {
                const getTenantKey = window.getTenantKey || ((k) => k);
                if (Array.isArray(data.customers)) {
                    localStorage.setItem(getTenantKey('erp_customers_v1'), JSON.stringify(data.customers));
                    localStorage.setItem('erp_customers_v1', JSON.stringify(data.customers));
                }
                if (Array.isArray(data.suppliers)) {
                    localStorage.setItem(getTenantKey('erp_suppliers_v1'), JSON.stringify(data.suppliers));
                    localStorage.setItem('erp_suppliers_v1', JSON.stringify(data.suppliers));
                }
                if (typeof window.buildAccountsArray === 'function') {
                    try { window.buildAccountsArray(); } catch (e) {}
                }
                if (typeof window.buildAccountsList === 'function') {
                    try { window.buildAccountsList(); } catch (e) {}
                }
            }
        } catch (e) {
            console.warn('syncMasterDataFromServer warning:', e);
        }
    },

    normalizeDigitsAndArabic: function(str) {
        if (!str) return '';
        const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        let q = String(str).trim();
        for (let i = 0; i < 10; i++) {
            q = q.replace(new RegExp(ar[i], 'g'), String(i)).replace(new RegExp(fa[i], 'g'), String(i));
        }
        return q.toLowerCase()
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .replace(/^#/, '')
            .trim();
    },

    getAllActiveCustomersAndSuppliers: function() {
        const getTenantKey = window.getTenantKey || ((k) => k);
        let custs = [];
        try {
            custs = JSON.parse(localStorage.getItem(getTenantKey('erp_customers_v1')) || localStorage.getItem('erp_customers_v1') || '[]');
            if (!Array.isArray(custs)) custs = [];
        } catch(e) { custs = []; }
        let sups = [];
        try {
            sups = JSON.parse(localStorage.getItem(getTenantKey('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1') || '[]');
            if (!Array.isArray(sups)) sups = [];
        } catch(e) { sups = []; }
        const list = [];
        custs.filter(c => c && !c.deleted).forEach(c => list.push(Object.assign({}, c, { _entityType: 'customer' })));
        sups.filter(s => s && !s.deleted).forEach(s => list.push(Object.assign({}, s, { _entityType: 'supplier' })));
        return list;
    },

    searchCustomersByCodeOrName: function(rawQuery) {
        const qNorm = this.normalizeDigitsAndArabic(rawQuery);
        const all = this.getAllActiveCustomersAndSuppliers();
        if (!qNorm) return all.slice(0, 30);

        const qCleanCode = qNorm.replace(/^(كود|باسورد|رمز|الرمز|كلمه السر|كشف|رصيد)\s+/i, '').trim();
        const scored = [];

        for (const c of all) {
            const cPin = this.normalizeDigitsAndArabic(c.statementPin || '');
            const cNum = this.normalizeDigitsAndArabic(c.receiptCodeNum || '');
            const cWord = this.normalizeDigitsAndArabic(c.receiptCodeWord || '');
            const cName = this.normalizeDigitsAndArabic(c.name || '');
            const cPhone = String(c.whatsapp || c.phone || '').replace(/\D/g, '');
            const qDigits = qCleanCode.replace(/\D/g, '');

            let score = 0;
            if (cPin && (cPin === qCleanCode || cPin === qNorm)) score = 1000;
            else if (cNum && (cNum === qCleanCode || cNum === qNorm)) score = 950;
            else if (cWord && (cWord === qCleanCode || cWord === qNorm)) score = 920;
            else if (cName && cName === qNorm) score = 900;
            else if (cName && cName.startsWith(qNorm)) score = 800;
            else if (cPin && qCleanCode && cPin.startsWith(qCleanCode)) score = 750;
            else if (cNum && qCleanCode && cNum.startsWith(qCleanCode)) score = 700;
            else if (cName && qNorm.split(/\s+/).every(tok => cName.includes(tok))) score = 650;
            else if (qDigits.length >= 4 && cPhone.includes(qDigits)) score = 600;

            if (score > 0) {
                scored.push({ item: c, score });
            }
        }
        scored.sort((a, b) => b.score - a.score);
        return scored.map(x => x.item).slice(0, 25);
    },

    findCustomerByCodeOrName: function(rawQuery) {
        if (!rawQuery) return null;
        const matches = this.searchCustomersByCodeOrName(rawQuery);
        return matches.length > 0 ? matches[0] : null;
    },

    saveCustomerPhoneToBackend: async function(customerId, newPhone, customerName = '') {
        if (!newPhone) return;
        const cleanRaw = String(newPhone).trim();
        if (!cleanRaw) return;
        const getTenantKey = window.getTenantKey || ((k) => k);
        const cKey = getTenantKey('erp_customers_v1');
        let custs = [];
        try {
            custs = JSON.parse(localStorage.getItem(cKey) || localStorage.getItem('erp_customers_v1') || '[]');
            if (!Array.isArray(custs)) custs = [];
        } catch(e) { custs = []; }

        const cleanId = String(customerId || '').replace(/^cust-/, '').trim();
        const normName = this.normalizeDigitsAndArabic(customerName);
        let idx = custs.findIndex(c => c && !c.deleted && (
            (cleanId && String(c.id) === cleanId) ||
            (normName && this.normalizeDigitsAndArabic(c.name) === normName)
        ));

        if (idx > -1) {
            custs[idx].whatsapp = cleanRaw;
            if (!custs[idx].phone) custs[idx].phone = cleanRaw;
            try {
                localStorage.setItem(cKey, JSON.stringify(custs));
                localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
            } catch (e) {}

            try {
                const tId = this.getActiveTenantId();
                await fetch('api/save_master_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_customer',
                        tenant_id: tId,
                        data: custs[idx]
                    })
                });
            } catch (err) {
                console.warn('Auto-save customer WhatsApp phone warning:', err);
            }
        }
    },

    onManualCodeOrNameInput: function(val) {
        const hintEl = document.getElementById('manualCustCodeHint');
        if (!hintEl) return;
        const matched = this.findCustomerByCodeOrName(val);
        if (matched) {
            hintEl.style.display = 'block';
            const pinBadge = matched.statementPin ? ` | 🔐 الباسورد: ${escapeHtml(String(matched.statementPin))}` : '';
            hintEl.innerHTML = `✅ تم التعرف على الحساب: <strong>${escapeHtml(matched.name)}</strong> (كود #${matched.receiptCodeNum || '-'}${pinBadge})`;
            const phoneEl = document.getElementById('manualCustPhone');
            if (phoneEl && (matched.whatsapp || matched.phone)) {
                phoneEl.value = matched.whatsapp || matched.phone;
            }
        } else {
            hintEl.style.display = 'none';
            hintEl.innerHTML = '';
        }
    },

    lookupCustomerForAlertByCode: function(val) {
        const dropdown = document.getElementById('cust-alert-autocomplete');
        const q = String(val || '').trim();
        if (!q) {
            if (dropdown) dropdown.style.display = 'none';
            return;
        }
        const matches = this.searchCustomersByCodeOrName(q);
        if (dropdown) {
            if (matches.length > 0) {
                dropdown.innerHTML = matches.slice(0, 12).map(c => {
                    const pinText = c.statementPin ? `<span style="background:rgba(16,185,129,0.2); color:#34d399; padding:1px 6px; border-radius:4px; font-family:monospace; font-size:0.75rem;">🔐 ${escapeHtml(String(c.statementPin))}</span>` : '';
                    const codeText = c.receiptCodeNum ? `<span style="background:rgba(56,189,248,0.18); color:#38bdf8; padding:1px 6px; border-radius:4px; font-family:monospace; font-size:0.75rem;">#${escapeHtml(String(c.receiptCodeNum))}</span>` : '';
                    const phText = escapeHtml(c.whatsapp || c.phone || 'بدون رقم');
                    return `<div onclick="InboundBankakManager.selectAlertCustomer('${escapeHtml(String(c.id))}')" style="padding:8px 12px; cursor:pointer; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center; gap:8px;" onmouseover="this.style.background='rgba(16,185,129,0.15)'" onmouseout="this.style.background='transparent'">
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <strong style="color:#fff; font-size:0.86rem;">${escapeHtml(c.name || '')}</strong>
                            ${pinText}
                            ${codeText}
                        </div>
                        <span style="color:#94a3b8; font-size:0.75rem; font-family:monospace;" dir="ltr">${phText}</span>
                    </div>`;
                }).join('');
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }
        if (matches.length > 0) {
            this.applyAlertCustomerToFields(matches[0]);
        }
    },

    selectAlertCustomer: function(custId) {
        const all = this.getAllActiveCustomersAndSuppliers();
        const found = all.find(c => String(c.id) === String(custId));
        const dropdown = document.getElementById('cust-alert-autocomplete');
        if (dropdown) dropdown.style.display = 'none';
        if (found) {
            const input = document.getElementById('cust-alert-code-lookup');
            if (input) input.value = found.name;
            this.applyAlertCustomerToFields(found);
        }
    },

    applyAlertCustomerToFields: function(matched) {
        if (!matched) return;
        const targetPhone = matched.whatsapp || matched.phone || '';
        const nameEl = document.getElementById('cust-alert-name');
        const phoneInput = document.getElementById('cust-alert-phone');
        const phoneDisp = document.getElementById('cust-alert-phone-display');
        const custIdHidden = document.getElementById('cust-alert-customer-id');
        const pinLabel = matched.statementPin ? ` | باسورد: ${matched.statementPin}` : '';
        if (nameEl) nameEl.textContent = `${matched.name} (#${matched.receiptCodeNum || '-'}${pinLabel})`;
        if (phoneInput && targetPhone) phoneInput.value = targetPhone;
        if (phoneDisp) phoneDisp.textContent = targetPhone || 'يمكنك كتابته بالأسفل';
        if (custIdHidden) custIdHidden.value = matched.id || '';
        if (this.currentReceiptForAlert) {
            this.currentReceiptForAlert.customer_name = matched.name;
            this.currentReceiptForAlert.customer_id = matched.id;
            if (targetPhone) this.currentReceiptForAlert.customer_phone = targetPhone;
            const activeBtn = document.querySelector('.alert-type-btn.active');
            const activeType = activeBtn ? activeBtn.getAttribute('data-type') : 'reconciled';
            this.selectAlertTemplate(activeType, activeBtn);
        }
    },

    promptAssignByCode: function(receiptId) {
        const r = this.receipts.find(x => Number(x.id) === Number(receiptId));
        if (!r) return;
        this.syncMasterDataFromServer();

        const modal = document.getElementById('inbound-assign-customer-modal');
        if (!modal) return;

        document.getElementById('assign-cust-receipt-id').value = r.id;
        document.getElementById('assign-cust-selected-id').value = '';
        document.getElementById('assign-cust-selected-code').value = '';

        const subtitle = document.getElementById('assign-cust-receipt-subtitle');
        if (subtitle) {
            const amtTxt = r.amount ? `${parseFloat(r.amount).toLocaleString('en-US')} ${r.currency || 'SDG'}` : 'قيد الفحص';
            subtitle.textContent = `إشعار #${r.id} — رقم العملية: ${r.transfer_no || '---'} — المبلغ: ${amtTxt}`;
        }

        const searchInput = document.getElementById('assign-cust-search-input');
        if (searchInput) {
            searchInput.value = (r.customer_name && r.customer_name !== 'عميل واتساب' && r.customer_name !== 'عميل بنكك') ? r.customer_name : '';
        }

        const phoneInput = document.getElementById('assign-cust-phone-input');
        if (phoneInput) {
            phoneInput.value = r.customer_phone || '';
        }

        const matchedCard = document.getElementById('assign-cust-matched-card');
        if (matchedCard) matchedCard.style.display = 'none';

        modal.style.display = 'flex';
        this.onAssignCustomerSearch(searchInput ? searchInput.value : '');
        setTimeout(() => { if (searchInput) searchInput.focus(); }, 80);
    },

    closeAssignCustomerModal: function() {
        const modal = document.getElementById('inbound-assign-customer-modal');
        if (modal) modal.style.display = 'none';
    },

    onAssignCustomerSearch: function(val) {
        const dropdown = document.getElementById('assign-cust-autocomplete');
        if (!dropdown) return;
        const matches = this.searchCustomersByCodeOrName(val);
        if (matches.length === 0) {
            dropdown.innerHTML = `<div style="padding:10px 14px; color:#fca5a5; font-size:0.82rem; font-weight:700;">لا يوجد عميل مطابق في القائمة — يمكنك كتابة اسم العميل الجديد مباشرة وسيتم إنشاؤه وتوجيه الإشعار له فوراً.</div>`;
            dropdown.style.display = 'block';
            return;
        }
        dropdown.innerHTML = matches.map(c => {
            const pinBadge = c.statementPin ? `<span style="background:rgba(16,185,129,0.22); color:#34d399; border:1px solid rgba(16,185,129,0.4); padding:1px 7px; border-radius:5px; font-family:monospace; font-size:0.76rem; font-weight:900;">🔐 باسورد: ${escapeHtml(String(c.statementPin))}</span>` : '';
            const codeBadge = c.receiptCodeNum ? `<span style="background:rgba(56,189,248,0.18); color:#38bdf8; padding:1px 7px; border-radius:5px; font-family:monospace; font-size:0.75rem;">#${escapeHtml(String(c.receiptCodeNum))}</span>` : '';
            const ph = escapeHtml(c.whatsapp || c.phone || 'بدون رقم');
            return `<div onclick="InboundBankakManager.selectAssignCustomerItem('${escapeHtml(String(c.id))}')" style="padding:9px 12px; cursor:pointer; border-bottom:1px solid rgba(255,255,255,0.07); display:flex; justify-content:space-between; align-items:center; gap:8px;" onmouseover="this.style.background='rgba(56,189,248,0.15)'" onmouseout="this.style.background='transparent'">
                <div style="display:flex; align-items:center; gap:7px; flex-wrap:wrap;">
                    <span style="font-size:1rem;">👤</span>
                    <strong style="color:#fff; font-size:0.9rem;">${escapeHtml(c.name || '')}</strong>
                    ${pinBadge}
                    ${codeBadge}
                </div>
                <span style="color:#94a3b8; font-size:0.76rem; font-family:monospace;" dir="ltr">${ph}</span>
            </div>`;
        }).join('');
        dropdown.style.display = 'block';

        // If exact match on PIN or code or name, highlight in card immediately
        const qNorm = this.normalizeDigitsAndArabic(val);
        if (qNorm) {
            const top = matches[0];
            const topPin = this.normalizeDigitsAndArabic(top.statementPin || '');
            const topCode = this.normalizeDigitsAndArabic(top.receiptCodeNum || '');
            const topName = this.normalizeDigitsAndArabic(top.name || '');
            if (qNorm === topPin || qNorm === topCode || qNorm === topName) {
                this.applySelectedAssignCustomer(top, false);
            }
        }
    },

    selectAssignCustomerItem: function(custId) {
        const all = this.getAllActiveCustomersAndSuppliers();
        const found = all.find(c => String(c.id) === String(custId));
        if (!found) return;
        this.applySelectedAssignCustomer(found, true);
    },

    applySelectedAssignCustomer: function(cust, hideDropdown = true) {
        if (!cust) return;
        document.getElementById('assign-cust-selected-id').value = cust.id || '';
        document.getElementById('assign-cust-selected-code').value = cust.statementPin || cust.receiptCodeNum || cust.name || '';
        const searchInput = document.getElementById('assign-cust-search-input');
        if (searchInput && hideDropdown) {
            searchInput.value = cust.name || '';
        }
        const dropdown = document.getElementById('assign-cust-autocomplete');
        if (dropdown && hideDropdown) dropdown.style.display = 'none';

        const card = document.getElementById('assign-cust-matched-card');
        const nameEl = document.getElementById('assign-cust-matched-name');
        const metaEl = document.getElementById('assign-cust-matched-meta');
        if (card && nameEl && metaEl) {
            nameEl.textContent = `✅ ${cust.name}`;
            metaEl.textContent = `🔐 الباسورد: ${cust.statementPin || 'غير محدد'} | 🔢 الكود: #${cust.receiptCodeNum || '-'} | 📱 واتساب: ${cust.whatsapp || cust.phone || 'غير مسجل'}`;
            card.style.display = 'block';
        }
        const phoneInput = document.getElementById('assign-cust-phone-input');
        if (phoneInput && (cust.whatsapp || cust.phone)) {
            phoneInput.value = cust.whatsapp || cust.phone;
        }
    },

    confirmAssignCustomerModal: async function() {
        const receiptId = document.getElementById('assign-cust-receipt-id')?.value;
        const selectedId = document.getElementById('assign-cust-selected-id')?.value || '';
        const selectedCode = document.getElementById('assign-cust-selected-code')?.value || '';
        const rawSearch = document.getElementById('assign-cust-search-input')?.value?.trim() || '';
        const phoneVal = document.getElementById('assign-cust-phone-input')?.value?.trim() || '';
        const linkPhone = Boolean(document.getElementById('assign-cust-link-phone')?.checked);

        const codeOrName = selectedCode || rawSearch;
        if (!codeOrName && !selectedId) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار العميل أو كتابة اسمه أو الباسورد أولاً', 'warning');
            else alert('يرجى اختيار العميل أو كتابة اسمه أو الباسورد أولاً');
            return;
        }

        const btn = document.getElementById('btn-confirm-assign-cust');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>⏳ جاري التوجيه والتنزيل الفوري في كشف الحساب...</span>';
        }

        const tId = this.getActiveTenantId();
        const bankSel = document.getElementById('drawer-target-bank-select');
        const bankId = (bankSel && bankSel.value) ? bankSel.value : (this.selectedBankId || '');
        const payload = { action: 'assign_customer_by_code', tenant_id: tId, id: receiptId, customer_id: selectedId, customer_code: codeOrName, customer_phone: phoneVal, bank_id: bankId, link_sender_phone: linkPhone };
        const ridNum = Number(receiptId);
        if (this._editingRows && this._editingRows[ridNum]) {
            this._editingRows[ridNum].newCustName = rawSearch || codeOrName;
            this._editingRows[ridNum].newCustPhone = phoneVal;
            this._editingRows[ridNum].newCustPayload = payload;
            this._editingRows[ridNum].dirty = true;
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            this.closeAssignCustomerModal();
            this.renderList();
            return;
        }
        await this._execAssignCustomerPayload(payload, btn, origHtml);
    },

    _execAssignCustomerPayload: async function(payload, btn = null, origHtml = '') {
        const tId = payload.tenant_id || this.getActiveTenantId(), receiptId = payload.id, phoneVal = payload.customer_phone || '';
        try {
            const res = await fetch('api/inbound_receipts.php?action=assign_customer_by_code&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data && data.success) {
                const getTenantKey = window.getTenantKey || ((k) => k);
                if (data.customer && data.customer.id) {
                    try {
                        const cKey = getTenantKey('erp_customers_v1');
                        let custs = JSON.parse(localStorage.getItem(cKey) || localStorage.getItem('erp_customers_v1') || '[]');
                        if (!Array.isArray(custs)) custs = [];
                        const idx = custs.findIndex(c => c && String(c.id) === String(data.customer.id));
                        if (idx > -1) custs[idx] = data.customer;
                        else custs.push(data.customer);
                        localStorage.setItem(cKey, JSON.stringify(custs));
                        localStorage.setItem('erp_customers_v1', JSON.stringify(custs));
                    } catch(e) {}
                }
                const r = this.receipts.find(x => Number(x.id) === Number(receiptId)) || { id: receiptId };
                if (data.customer) {
                    r.customer_id = data.customer.id;
                    r.customer_name = data.customer.name;
                    if (phoneVal) r.customer_phone = phoneVal;
                }
                if (data.posting && data.posting.voucher_no) {
                    r.voucher_no = data.posting.voucher_no;
                    r.status = 'verified';
                    this.syncPostedReceiptToLocalErp(r, data.posting);
                }
                this.closeAssignCustomerModal();
                this.refreshReceipts();
                if (typeof window.applyLedgerFilters === 'function') {
                    try { window.buildAccountsList(); window.extractFilters(); window.applyLedgerFilters(); } catch (e) {}
                }
                if (typeof showToast === 'function') {
                    showToast(data.message || '✅ تم توجيه الإشعار وتنزيله في كشف حساب العميل بنجاح!', 'success', 6500);
                } else {
                    alert(data.message);
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast('⚠️ ' + (data?.error || 'تعذر العثور على العميل بهذا الكود أو الاسم'), 'error', 6000);
                } else {
                    alert('⚠️ ' + (data?.error || 'تعذر العثور على العميل بهذا الكود أو الاسم'));
                }
            }
        } catch (err) {
            alert('خطأ في الاتصال: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    },

    // ═══ فحص التزوير والتلاعب الجنائي عند الطلب فقط (On-Demand Forgery Inspection) ═══
    checkForgeryOnDemand: async function(receiptId, btnElement) {
        const origHtml = btnElement ? btnElement.innerHTML : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span>⏳</span> <span>جاري الفحص الجنائي...</span>';
        }
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch(`api/inbound_receipts.php?action=check_forgery&id=${receiptId}&tenant_id=${encodeURIComponent(tId)}`);
            const data = await res.json();
            if (data.success && data.forgery_check) {
                const fc = data.forgery_check;
                const isSusp = fc.is_suspicious || Number(fc.tampering_score || 0) >= 50;
                const reasonsHtml = (Array.isArray(fc.tampering_reasons) && fc.tampering_reasons.length > 0)
                    ? fc.tampering_reasons.map(x => `<li style="margin-bottom:4px;">${escapeHtml(x)}</li>`).join('')
                    : '<li>لم يتم رصد أي آثار تعديل برمجي (Photoshop / Canva) في بيانات الصورة.</li><li>بنية الخطوط والأرقام متطابقة مع القالب القياسي للبنك.</li>';

                const modalId = 'forgery-report-modal';
                if (document.getElementById(modalId)) document.getElementById(modalId).remove();
                const div = document.createElement('div');
                div.id = modalId;
                div.style.cssText = 'position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(8px); z-index:2147483647; display:flex; align-items:center; justify-content:center; padding:16px; font-family:Cairo,Tajawal,sans-serif; direction:rtl;';
                div.onclick = (ev) => { if (ev.target === div) div.remove(); };
                div.innerHTML = `
                    <div style="width:100%; max-width:480px; background:linear-gradient(145deg, #0f172a, #1e293b); border:2px solid ${isSusp ? '#ef4444' : '#10b981'}; border-radius:18px; padding:20px 22px; color:#fff; box-shadow:0 25px 60px rgba(0,0,0,0.9);">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.12); padding-bottom:10px; margin-bottom:12px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:1.6rem;">${isSusp ? '🚨' : '🛡️'}</span>
                                <div>
                                    <h3 style="margin:0; font-size:1.1rem; font-weight:900; color:${isSusp ? '#fca5a5' : '#34d399'};">تقرير فحص التزوير الجنائي (عند الطلب)</h3>
                                    <span style="font-size:0.75rem; color:#94a3b8;">إشعار رقم #${receiptId} — ${escapeHtml(data.data?.customer_name || '')}</span>
                                </div>
                            </div>
                            <button type="button" onclick="document.getElementById('${modalId}').remove()" style="background:none; border:none; color:#94a3b8; font-size:1.2rem; cursor:pointer;">✕</button>
                        </div>
                        <div style="background:rgba(0,0,0,0.35); border-radius:10px; padding:10px 14px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:0.85rem; color:#cbd5e1; font-weight:700;">درجة الاشتباه بالتلاعب:</span>
                            <strong style="font-size:1.15rem; color:${isSusp ? '#ef4444' : '#10b981'}; font-family:Consolas,monospace;">${Number(fc.tampering_score || 0)}% (${isSusp ? 'اشتباه تزوير' : 'سليم وأصلي'})</strong>
                        </div>
                        <div style="font-size:0.85rem; color:#e2e8f0; line-height:1.6; margin-bottom:16px;">
                            <strong style="color:#38bdf8; display:block; margin-bottom:4px;">نتائج الفحص الجنائي للصورة:</strong>
                            <ul style="margin:0; padding-right:18px;">${reasonsHtml}</ul>
                        </div>
                        <div style="display:flex; justify-content:flex-end;">
                            <button type="button" onclick="document.getElementById('${modalId}').remove()" style="height:36px; padding:0 20px; border-radius:8px; background:${isSusp ? '#ef4444' : '#10b981'}; border:none; color:#fff; font-weight:900; cursor:pointer;">إغلاق التقرير</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(div);
                this.refreshReceipts(true);
            } else {
                alert(data.error || 'تعذر إجراء فحص التزوير');
            }
        } catch (e) {
            alert('خطأ أثناء فحص التزوير: ' + e.message);
        } finally {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = origHtml || 'تزوير';
            }
        }
    },

    postToAccountsDirect: async function(receiptId, btnElement) {
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = 'جاري...';
        }
        try {
            const tId = this.getActiveTenantId();
            const rowObj = this.receipts.find(x => Number(x.id) === Number(receiptId));
            const rowInp = document.querySelector(`.inbound-row-rec-input[data-row-id="${receiptId}"]`);
            const bankSel = document.getElementById('drawer-target-bank-select');
            const bankId = (rowInp && rowInp.dataset.accVal) || (rowObj && rowObj.bank_code && rowObj.bank_code !== '170' ? rowObj.bank_code : '') || (bankSel && bankSel.value) || '';
            const bankName = (rowInp && rowInp.dataset.accName) || (rowObj && rowObj.bank_name) || '';
            let manualAmt = '';
            if (rowObj && parseFloat(rowObj.amount || 0) <= 0) {
                const entered = prompt('أدخل مبلغ الإشعار لإتمام الترحيل:', '');
                if (!entered || parseFloat(entered.replace(/,/g, '')) <= 0) {
                    if (btnElement) { btnElement.disabled = false; btnElement.innerHTML = 'ترحيل'; }
                    return;
                }
                manualAmt = String(parseFloat(entered.replace(/,/g, '')));
            }
            const url = `api/inbound_receipts.php?action=post_to_accounts&id=${receiptId}&force_post=1&bank_id=${encodeURIComponent(bankId)}&bank_name=${encodeURIComponent(bankName)}${manualAmt ? '&amount=' + encodeURIComponent(manualAmt) : ''}&tenant_id=${encodeURIComponent(tId)}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.success && data.voucher_no) {
                const r = this.receipts.find(x => x.id == receiptId) || { id: receiptId, voucher_no: data.voucher_no };
                r.voucher_no = data.voucher_no;
                if (data.voucher && data.voucher.totalAmount) r.amount = data.voucher.totalAmount;
                if (data.voucher && data.voucher.mobileTransferNo) r.transfer_no = data.voucher.mobileTransferNo;
                if (data.customer_id) r.customer_id = data.customer_id;
                if (data.customer_name) r.customer_name = data.customer_name;
                if (data.bank_name) r.bank_name = data.bank_name;
                r.status = 'verified';
                r.issue_reason = null;
                this.syncPostedReceiptToLocalErp(r, data);
                this.refreshReceipts();
                if (typeof window.applyLedgerFilters === 'function') {
                    try { window.buildAccountsList(); window.extractFilters(); window.applyLedgerFilters(); } catch (e) {}
                }
                if (typeof showToast === 'function') {
                    showToast(`✅ تم تمرير وتنزيل الإشعار في حساب (${data.customer_name}) وبنك (${data.bank_name || 'البنك'}) بالسند رقم (${data.voucher_no})!`, 'success');
                }
            } else {
                alert(data.error || 'تعذر تنزيل الإشعار في الحسابات');
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<span>✔️</span> <span>تمرير وترحيل للحساب</span>';
                }
            }
        } catch (e) {
            alert('خطأ أثناء التنزيل في الحسابات: ' + e.message);
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = '<span>✔️</span> <span>تمرير وترحيل للحساب</span>';
            }
        }
    },

    // ═══ فحص سريع بالذكاء الاصطناعي من داخل الصينية ═══
    triggerOCR: async function(receiptId, btnElement) {
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span>🔄</span> <span>جاري الفحص...</span>';
        }
        try {
            const tId = this.getActiveTenantId();
            const res = await fetch(`api/inbound_receipts.php?action=scan_receipt&id=${receiptId}&tenant_id=${encodeURIComponent(tId)}`);
            const data = await res.json();
            if (data.success && data.data) {
                if (data.data.posting && data.data.posting.voucher_no) {
                    this.syncPostedReceiptToLocalErp(data.data, data.data.posting);
                }
                if (typeof showToast === 'function') {
                    const vMsg = data.data.voucher_no ? ` وتم تنزيله في الحسابات بالسند (${data.data.voucher_no})` : '';
                    showToast(`✅ تم فحص الإشعار بالـ AI: المبلغ (${parseFloat(data.data.amount||0).toLocaleString()} SDG) برقم (${data.data.transfer_no})${vMsg}`, 'success');
                }
                this.refreshReceipts();
            } else {
                alert(data.error || 'فشل فحص الإشعار بالذكاء الاصطناعي');
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<span>⚡</span> <span>فحص AI</span>';
                }
            }
        } catch(e) {
            alert('خطأ في الاتصال بالذكاء الاصطناعي: ' + e.message);
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = '<span>⚡</span> <span>فحص AI</span>';
            }
        }
    },

    // ═══ الفحص الذكي والتنزيل في السند المتعدد والحسابات (Deep Link & Auto-Fill in Multi-Cash-Payment) ═══
    inspectInVoucher: async function(receiptId) {
        let r = this.receipts.find(x => x.id == receiptId);
        if (!r) return;

        const tId = this.getActiveTenantId();

        // 1. If amount is 0 or transfer_no is missing, scan it now via API
        if ((!r.amount || parseFloat(r.amount) <= 0 || !r.transfer_no) && r.receipt_image) {
            if (typeof showToast === 'function') {
                showToast('⏳ جاري فحص الإشعار بالذكاء الاصطناعي واستخراج المبلغ وتنزيله بالحسابات...', 'info');
            }
            try {
                const scanRes = await fetch(`api/inbound_receipts.php?action=scan_receipt&id=${r.id}&tenant_id=${encodeURIComponent(tId)}`);
                const scanData = await scanRes.json();
                if (scanData.success && scanData.data) {
                    r = Object.assign(r, scanData.data);
                    if (scanData.data.posting && scanData.data.posting.voucher_no) {
                        this.syncPostedReceiptToLocalErp(r, scanData.data.posting);
                    }
                    this.refreshReceipts(true);
                }
            } catch (e) {
                console.warn('Scan receipt failed:', e);
            }
        }

        // 2. If receipt is valid and not yet posted to accounts, post it now!
        let postDetails = null;
        if (r.status !== 'duplicate_rejected' && parseFloat(r.amount || 0) > 0 && !r.voucher_no) {
            try {
                const postRes = await fetch(`api/inbound_receipts.php?action=post_to_accounts&id=${r.id}&tenant_id=${encodeURIComponent(tId)}`);
                const postData = await postRes.json();
                if (postData && postData.success && postData.voucher_no) {
                    r.voucher_no = postData.voucher_no;
                    r.customer_id = postData.customer_id || r.customer_id;
                    r.customer_name = postData.customer_name || r.customer_name;
                    postDetails = postData;
                    this.syncPostedReceiptToLocalErp(r, postData);
                    this.refreshReceipts(true);
                }
            } catch (e) {
                console.warn('Auto post to accounts warning:', e);
            }
        } else if (r.voucher_no) {
            this.syncPostedReceiptToLocalErp(r);
        }

        // 3. Close drawer & remember active inbound receipt ID
        this.closeDrawer();
        window._activeInboundReceiptId = r.id;

        // 4. Set Voucher Type to Receipt (سند قبض / استلام من عميل)
        if (typeof window.setVoucherType === 'function') {
            window.setVoucherType('receipt');
        } else {
            const vTypeEl = document.getElementById('voucher-type');
            if (vTypeEl) vTypeEl.value = 'receipt';
        }

        // 5. Support BOTH single-cash-payment.php (حركة مالية) AND multi-cash-payment.php (سند متعدد)
        let syncDate = String(r.receipt_date || r.transfer_date || '').split(' ')[0] || '';
        if (!syncDate && r.notes) { const dm = String(r.notes).match(/\b(20\d{2}[-/]\d{2}[-/]\d{2})\b/); if (dm) syncDate = dm[1].replace(/\//g, '-'); }
        if (!syncDate) syncDate = String(r.created_at || '').split(' ')[0] || '';

        const scpFromSearch = document.getElementById('from-account-search');
        const scpToSearch = document.getElementById('to-account-search');
        if (scpFromSearch && scpToSearch && !document.getElementById('accounts-blocks-container')) {
            setTimeout(() => {
                const rawCustId = String(r.customer_id || '').replace(/^(cust|supp)-/, '');
                const custAccId = rawCustId ? ('cust-' + rawCustId) : '';
                const allAccs = this.getAllReceivingAccounts();
                const recHit = allAccs.find(x => (r.bank_code && r.bank_code !== '170' && (x.val === r.bank_code || x.rawId === String(r.bank_code).replace(/^(bank|supp|cust|safe|wh|exp|emp)-/, ''))) || (r.bank_name && x.name === r.bank_name));
                const toAccId = recHit ? recHit.val : (r.bank_code && r.bank_code !== '170' ? r.bank_code : '');
                const toAccName = recHit ? recHit.name : (r.bank_name || 'بنك الخرطوم');
                const fHid = document.getElementById('from-account');
                const tHid = document.getElementById('to-account');
                if (fHid) fHid.value = custAccId || (r.customer_name || '');
                scpFromSearch.value = String(r.customer_name || '').trim();
                scpFromSearch.dispatchEvent(new Event('change', { bubbles: true }));
                if (tHid) tHid.value = toAccId || toAccName;
                scpToSearch.value = toAccName;
                scpToSearch.dispatchEvent(new Event('change', { bubbles: true }));
                const vDateEl = document.getElementById('voucher-date');
                if (vDateEl && syncDate) vDateEl.value = syncDate;
                if (r.voucher_no && document.getElementById('voucher-no')) document.getElementById('voucher-no').textContent = r.voucher_no;
                const cleanAmt = parseFloat(r.amount || 0) || 0;
                const fmtStr = cleanAmt > 0 ? Math.round(cleanAmt).toLocaleString('en-US') : '';
                const amtLoc = document.getElementById('amount-local');
                if (amtLoc && cleanAmt > 0) { amtLoc.value = fmtStr; amtLoc.dispatchEvent(new Event('input', { bubbles: true })); }
                const btnBank = document.getElementById('toggle-bank');
                if (btnBank && !btnBank.classList.contains('active')) btnBank.click();
                setTimeout(() => {
                    const trCont = document.getElementById('transfers-container');
                    if (trCont) {
                        if (!trCont.querySelector('.transfer-row') && typeof window.addTransferRow === 'function') window.addTransferRow('', '', 0);
                        const trRow = trCont.querySelector('.transfer-row');
                        if (trRow) {
                            const tAmt = trRow.querySelector('.transfer-amount'), tNote = trRow.querySelector('.transfer-note'), tNo = trRow.querySelector('.transfer-no'), tSt = trRow.querySelector('.transfer-statement');
                            if (tAmt && cleanAmt > 0) { tAmt.value = fmtStr; tAmt.dispatchEvent(new Event('input', { bubbles: true })); }
                            if (tNote) tNote.value = toAccName;
                            if (tNo) tNo.value = r.transfer_no || '';
                            if (tSt) tSt.value = `إشعار وارد #${r.id} - ${r.customer_name || ''}`;
                        }
                    }
                    const vDesc = document.getElementById('voucher-desc') || document.getElementById('statement');
                    if (vDesc) vDesc.value = `سداد إشعار (${toAccName}) رقم العملية: ${r.transfer_no || ''} - ${r.customer_name || ''}`;
                    if (typeof window.recalcPaymentSummary === 'function') window.recalcPaymentSummary();
                }, 120);
            }, 80);
            return;
        }

        // Dynamically resolve the real .account-block data-block-id on multi-cash-payment.php
        setTimeout(() => {
            const vDateMulti = document.getElementById('voucher-date');
            if (vDateMulti && syncDate) vDateMulti.value = syncDate;
            const container = document.getElementById('accounts-blocks-container');
            let targetBlock = container ? container.querySelector('.account-block') : document.querySelector('.account-block');
            if (!targetBlock && typeof window.addAccountBlock === 'function') {
                window.addAccountBlock();
                targetBlock = container ? container.querySelector('.account-block') : document.querySelector('.account-block');
            }
            if (!targetBlock) return;

            const blockId = targetBlock.getAttribute('data-block-id');

            // Set voucher number if already assigned in DB so saving updates the same voucher
            if (r.voucher_no) {
                const vNoEl = document.getElementById('voucher-no-display');
                if (vNoEl) vNoEl.value = r.voucher_no;
            }

            // Rebuild ACCOUNTS so newly matched/created customer & bank are available
            if (typeof window.buildAccountsArray === 'function') {
                try { window.buildAccountsArray(); } catch (e) {}
            }

            // 6. Populate Main Account (Receiving Bank)
            const mainAccInput = document.getElementById('main-account');
            const mainAccSearch = document.getElementById('main-account-search');
            if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
                let bankAcc = ACCOUNTS.find(a => a.id && a.id.startsWith('bank-') && (
                    (r.bank_name && a.name && r.bank_name.includes(a.name)) ||
                    (a.name && a.name.includes('الخرطوم'))
                ));
                if (!bankAcc) {
                    bankAcc = ACCOUNTS.find(a => a.id && a.id.startsWith('bank-'));
                }
                if (bankAcc) {
                    if (mainAccInput) {
                        mainAccInput.value = bankAcc.id;
                        mainAccInput.setAttribute('data-id', bankAcc.id);
                    }
                    if (mainAccSearch) {
                        mainAccSearch.value = bankAcc.name;
                    }
                }
            }

            // 7. Populate Customer Account in Block
            const rawCustId = String(r.customer_id || '').replace(/^cust-/, '');
            let fullCustAccId = rawCustId ? ('cust-' + rawCustId) : '';
            if (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS)) {
                const matchedCustAcc = ACCOUNTS.find(a =>
                    (fullCustAccId && String(a.id) === fullCustAccId) ||
                    (r.customer_name && a.name && a.name.trim() === r.customer_name.trim())
                );
                if (matchedCustAcc) {
                    fullCustAccId = matchedCustAcc.id;
                }
            }

            const accSearch = document.getElementById(`account-${blockId}-search`);
            const accHidden = document.getElementById(`account-${blockId}`);
            const accId = document.getElementById(`account-${blockId}-id`);
            if (accSearch) {
                accSearch.value = r.customer_name || '';
            }
            if (accHidden) {
                accHidden.value = fullCustAccId || r.customer_name || '';
            }
            if (accId) {
                accId.value = fullCustAccId || r.customer_name || '';
            }

            // 8. Activate Bank Payment Method in targetBlock (and deactivate Cash if active)
            const btnCash = document.getElementById(`pm-cash-${blockId}`);
            const btnBank = document.getElementById(`pm-bank-${blockId}`);
            if (btnCash && btnCash.classList.contains('active') && typeof window.toggleBlockPayment === 'function') {
                window.toggleBlockPayment(blockId, 'cash');
            }
            if (btnBank && !btnBank.classList.contains('active')) {
                if (typeof window.toggleBlockPayment === 'function') {
                    window.toggleBlockPayment(blockId, 'bank');
                } else {
                    btnBank.click();
                }
            }

            // Ensure sub-fields and sub-bank containers are visible
            const subFields = document.getElementById(`sub-fields-${blockId}`);
            const subBank = document.getElementById(`sub-bank-${blockId}`);
            if (subFields) subFields.style.display = 'block';
            if (subBank) subBank.style.display = 'block';

            // 9. Fill Bank Transfer Row Details & Totals
            setTimeout(() => {
                let bankRow = targetBlock.querySelector('.bank-row');
                if (!bankRow && typeof window.addBankRow === 'function') {
                    window.addBankRow(blockId);
                    bankRow = targetBlock.querySelector('.bank-row');
                }

                const cleanAmt = parseFloat(r.amount || 0) || 0;
                const formattedAmtStr = (typeof fmtNum === 'function') ? fmtNum(cleanAmt) : cleanAmt.toLocaleString('en-US');

                if (bankRow) {
                    const bAmt = bankRow.querySelector('.block-bank-amount');
                    if (bAmt && cleanAmt > 0) {
                        bAmt.value = formattedAmtStr;
                        bAmt.dispatchEvent(new Event('input', { bubbles: true }));
                        bAmt.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const bTransfer = bankRow.querySelector('.block-transfer-no-input');
                    if (bTransfer) {
                        bTransfer.value = r.transfer_no || '';
                        bTransfer.dispatchEvent(new Event('input', { bubbles: true }));
                        bTransfer.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const bName = bankRow.querySelector('.block-bank-name-input');
                    if (bName) {
                        bName.value = r.bank_name || 'بنك الخرطوم (بنكك)';
                        bName.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    const bDesc = bankRow.querySelector('.block-bank-desc-input');
                    if (bDesc) {
                        bDesc.value = `إشعار وارد #${r.id} - ${r.customer_name || ''}`;
                    }
                }

                const mainBlockAmt = document.getElementById(`amount-${blockId}`);
                if (mainBlockAmt && cleanAmt > 0) {
                    mainBlockAmt.value = formattedAmtStr;
                }

                const stmtInput = document.getElementById(`statement-${blockId}`);
                if (stmtInput) {
                    stmtInput.value = `سداد إشعار ${r.bank_name || 'بنكك'} رقم العملية ${r.transfer_no || ''}`;
                }

                const vDescInput = document.getElementById('voucher-desc');
                if (vDescInput) {
                    vDescInput.value = `إشعار تحويل بنكي (${r.bank_name || 'بنكك'}) رقم العملية: ${r.transfer_no || ''} - العميل: ${r.customer_name || ''}`;
                }

                if (typeof window.recalcBlockTotal === 'function') {
                    window.recalcBlockTotal(blockId);
                }
                if (typeof window.updateBlockTafqeetAndTier === 'function') {
                    window.updateBlockTafqeetAndTier(blockId);
                }
                if (typeof window.calcTotal === 'function') {
                    window.calcTotal();
                }

                // If duplicate, show alert banner
                if (r.status === 'duplicate_rejected') {
                    if (typeof showCenterError === 'function') {
                        showCenterError(`⚠️ تنبيه رقابي صارم: إشعار التحويل برقم ( ${r.transfer_no} ) للعميل ( ${r.customer_name} ) تم صرفه مسبقاً!<br><br>استخدم زر (إشعار الزبون) لإبلاغه فورياً عبر واتساب.`, 'duplicate');
                    }
                } else if (typeof showToast === 'function') {
                    const vInfo = r.voucher_no ? ` وتم تنزيله في الحسابات بالسند (${r.voucher_no})` : '';
                    showToast(`✅ تم فحص إشعار (${r.customer_name}) بمبلغ (${cleanAmt.toLocaleString()} SDG) وتفريغه بالسند${vInfo}!`, 'success');
                }
            }, 200);
        }, 120);
    },

    inspectById: function(rId) {
        if (!rId) return;
        const tId = this.getActiveTenantId();
        fetch('api/inbound_receipts.php?action=list&tenant_id=' + encodeURIComponent(tId))
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    this.receipts = res.data;
                    this.inspectInVoucher(rId);
                }
            });
    },

    // ═══ نافذة إخطار الزبون عبر واتساب (Customer Feedback Loop) ═══
    openCustomerAlertModal: function(receiptId) {
        const r = this.receipts.find(x => x.id == receiptId);
        if (!r) return;
        this.syncMasterDataFromServer();
        this.currentReceiptForAlert = Object.assign({}, r);

        let phoneToUse = r.customer_phone || '';
        let matchedCust = null;
        if (r.customer_id || r.customer_name) {
            matchedCust = this.findCustomerByCodeOrName(r.customer_id || r.customer_name);
            if (matchedCust && !phoneToUse) {
                phoneToUse = matchedCust.whatsapp || matchedCust.phone || '';
            }
        }

        document.getElementById('cust-alert-receipt-id').value = r.id;
        const custIdEl = document.getElementById('cust-alert-customer-id');
        if (custIdEl) custIdEl.value = (matchedCust && matchedCust.id) ? matchedCust.id : (r.customer_id || '');
        const lookupEl = document.getElementById('cust-alert-code-lookup');
        if (lookupEl) lookupEl.value = '';
        const dropEl = document.getElementById('cust-alert-autocomplete');
        if (dropEl) dropEl.style.display = 'none';

        document.getElementById('cust-alert-phone').value = phoneToUse;
        document.getElementById('cust-alert-name').textContent = r.customer_name || (matchedCust ? matchedCust.name : 'عميل واتساب');
        document.getElementById('cust-alert-phone-display').textContent = phoneToUse || 'أدخل الرقم بالأسفل ليتم حفظه وإرساله';

        // Select initial template based on receipt status
        const initType = (r.status === 'duplicate_rejected') ? 'duplicate' : ((r.status === 'verified' || r.status === 'reconciled') ? 'reconciled' : 'unclear');
        const activeBtn = document.querySelector(`.alert-type-btn[data-type="${initType}"]`) || document.querySelector('.alert-type-btn');
        this.selectAlertTemplate(initType, activeBtn);

        const modal = document.getElementById('inbound-customer-alert-modal');
        if (modal) modal.style.display = 'flex';
    },

    closeCustomerAlertModal: function() {
        const modal = document.getElementById('inbound-customer-alert-modal');
        if (modal) modal.style.display = 'none';
        this.currentReceiptForAlert = null;
    },

    selectAlertTemplate: function(type, btn) {
        document.querySelectorAll('.alert-type-btn').forEach(b => {
            b.classList.remove('active');
            b.style.borderColor = 'rgba(255,255,255,0.15)';
            b.style.background = 'rgba(255,255,255,0.04)';
            b.style.color = '#94a3b8';
        });
        if (btn) {
            btn.classList.add('active');
            if (type === 'duplicate') {
                btn.style.borderColor = '#ef4444';
                btn.style.background = 'rgba(239,68,68,0.2)';
                btn.style.color = '#fca5a5';
            } else if (type === 'reconciled') {
                btn.style.borderColor = '#10b981';
                btn.style.background = 'rgba(16,185,129,0.2)';
                btn.style.color = '#a7f3d0';
            } else {
                btn.style.borderColor = '#f59e0b';
                btn.style.background = 'rgba(245,158,11,0.2)';
                btn.style.color = '#fde047';
            }
        }

        const r = this.currentReceiptForAlert || {};
        const custName = r.customer_name || 'عزيزنا العميل';
        const tNo = r.transfer_no || '---';
        const amt = r.amount ? parseFloat(r.amount).toLocaleString('en-US') : '0.00';
        const curr = r.currency || 'SDG';
        const vNoLine = r.voucher_no ? `\n• رقم سند القبض: *${r.voucher_no}*` : '';

        let msg = '';
        if (type === 'duplicate') {
            msg = `السلام عليكم ورحمة الله وبركاته،\nعزيزنا العميل: *${custName}*\n\nنود إحاطتكم بأن إشعار التحويل البنكي المرسل برقم العملية: *(${tNo})*\n⚠️ *مكرر وسبق تسجيله واعتماده في نظامنا المالي مسبقاً*.\n\nيرجى التكرم بمراجعة حسابك البنكي وإرسال إشعار التحويل الصحيح حتى نتمكن من قيد المبلغ في حسابكم.\n\nشاكرين ومقدرين حسن تعاونكم،\n*إدارة الحسابات والمالية* 🏢`;
        } else if (type === 'unclear') {
            msg = `السلام عليكم ورحمة الله وبركاته،\nعزيزنا العميل: *${custName}*\n\nبخصوص إشعار التحويل البنكي الأخير، تعذر على نظام الفحص المالي قراءة البيانات بدقة لعدم وضوح الصورة أو اقتصاص رقم العملية أو المبلغ.\n\nيرجى التكرم بإعادة إرسال لقطة شاشة واضحة وكاملة للإشعار ليتسنى لنا اعتمادها فوراً.\n\nشكراً لتعاملكم الراقي،\n*إدارة الحسابات والمالية* 🏢`;
        } else if (type === 'amount_mismatch') {
            msg = `السلام عليكم ورحمة الله وبركاته،\nعزيزنا العميل: *${custName}*\n\nنود إعلامكم بأن المبلغ الوارد في إشعار التحويل هو: *(${amt} ${curr})* برقم عملية *(${tNo})*، ويوجد تباين مع الرصيد المطلوب للمطابقة.\n\nيرجى مراجعة الإدارة المالية لتسوية الفاتورة.\n\nمع فائق الاحترام والتقدير،\n*إدارة الحسابات* 🏢`;
        } else if (type === 'reconciled') {
            msg = `السلام عليكم ورحمة الله وبركاته،\nعزيزنا العميل: *${custName}*\n\n✅ *تم بنجاح استلام وفحص وتنزيل تحويلكم البنكي في الحسابات*:\n• رقم العملية: *${tNo}*\n• المبلغ المقيد: *${amt} ${curr}*${vNoLine}\n• تم قيد المبلغ بحسابكم وإصدار سند القبض الرسمي.\n\nسعداء دائماً بخدمتكم،\n*إدارة الحسابات والمالية* 🏢✨`;
        }

        const txtArea = document.getElementById('cust-alert-message-text');
        if (txtArea) txtArea.value = msg;
    },

    dispatchWhatsAppAlert: async function() {
        const phone = document.getElementById('cust-alert-phone')?.value?.trim() || '';
        const msg = document.getElementById('cust-alert-message-text')?.value || '';
        const custId = document.getElementById('cust-alert-customer-id')?.value || (this.currentReceiptForAlert && this.currentReceiptForAlert.customer_id) || '';
        const custName = (this.currentReceiptForAlert && this.currentReceiptForAlert.customer_name) || '';

        if (!phone) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال رقم هاتف الواتساب للعميل أولاً!', 'warning');
            else alert('رقم هاتف العميل غير مسجل! يرجى إدخال رقم الهاتف أولاً.');
            const phEl = document.getElementById('cust-alert-phone');
            if (phEl) phEl.focus();
            return;
        }

        // Persist the entered/updated WhatsApp phone to the customer's record in MySQL & localStorage (non-blocking)
        this.saveCustomerPhoneToBackend(custId, phone, custName);

        // Clean and format phone for WhatsApp (preserve @lid if present, otherwise Sudan 249)
        let cleanPhone = phone.trim();
        if (!cleanPhone.includes('@')) {
            cleanPhone = cleanPhone.replace(/\D/g, '');
            if (cleanPhone.startsWith('00')) cleanPhone = cleanPhone.substring(2);
            if (cleanPhone.startsWith('0') && cleanPhone.length === 10) {
                cleanPhone = '249' + cleanPhone.substring(1);
            } else if (cleanPhone.length === 9 && (cleanPhone.startsWith('9') || cleanPhone.startsWith('1'))) {
                cleanPhone = '249' + cleanPhone;
            }
        }

        const sendBtn = document.getElementById('btn-cust-alert-send-wa');
        const origBtnText = sendBtn ? sendBtn.innerHTML : '';
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span>⏳ جاري حفظ الرقم والإرسال المباشر عبر الواتساب...</span>';
        }

        const userId = typeof getWAUserId === 'function' ? getWAUserId() : (window.getWAUserId ? window.getWAUserId() : (localStorage.getItem('erp_wa_user_id') || 'wa_tenant_12'));

        try {
            const formData = new FormData();
            formData.append('userId', userId);
            formData.append('phone', cleanPhone);
            formData.append('message', msg);

            const res = await fetch(`api_index.php?r=wa-tenant&endpoint=send&userId=${encodeURIComponent(userId)}`, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data && data.success) {
                this.closeCustomerAlertModal();
                if (typeof showToast === 'function') {
                    showToast(`⚡ تم حفظ الرقم وإرسال الإشعار مباشرة للعميل (${cleanPhone}) عبر واتساب بنجاح!`, 'success', 6500);
                }
            } else {
                throw new Error(data?.error || 'فشل إرسال الرسالة من خادم الواتساب');
            }
        } catch (err) {
            console.error('WA Direct Alert Error:', err);
            if (typeof showToast === 'function') {
                showToast(`⚠️ تعذر الإرسال المباشر (${err.message}) — جاري فتح واتساب ويب كبديل...`, 'warning', 5000);
            }
            const waUrl = `https://api.whatsapp.com/send?phone=${encodeURIComponent(cleanPhone.replace(/\D/g, ''))}&text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
            this.closeCustomerAlertModal();
        } finally {
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = origBtnText;
            }
        }
    },

    dismissReceipt: function(id) {
        const r = (this.receipts || []).find(x => Number(x.id) === Number(id)) || {};
        const cName = String(r.customer_name || 'غير محدد').trim();
        const amtTxt = Number(r.amount || 0).toLocaleString('en-US');
        const trNo = r.transfer_no || '---';
        let dlg = document.getElementById('inbound-luxury-dismiss-modal');
        if (dlg) dlg.remove();
        dlg = document.createElement('div');
        dlg.id = 'inbound-luxury-dismiss-modal';
        dlg.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:rgba(2,6,23,0.82);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
        dlg.innerHTML = `<div style="width:100%;max-width:430px;background:linear-gradient(160deg,#0f172a 0%,#1e1b4b 55%,#090d16 100%);border:2px solid #f43f5e;border-radius:18px;box-shadow:0 25px 60px rgba(0,0,0,0.92),0 0 30px rgba(244,63,94,0.28);padding:22px 24px;color:#fff;text-align:center;">
            <div style="width:54px;height:54px;margin:0 auto 12px;border-radius:50%;background:rgba(244,63,94,0.18);border:2px solid #fb7185;display:flex;align-items:center;justify-content:center;font-size:1.6rem;">🗑️</div>
            <h3 style="margin:0 0 8px;font-size:1.18rem;font-weight:900;color:#fecdd3;">تأكيد حذف وتجاهل الإشعار</h3>
            <p style="margin:0 0 14px;font-size:0.90rem;color:#cbd5e1;font-weight:700;line-height:1.6;">هل تريد بالتأكيد حذف وتجاهل هذا الإشعار من قائمة الانتظار؟</p>
            <div style="background:rgba(2,6,23,0.65);border:1px solid rgba(148,163,184,0.25);border-radius:11px;padding:10px 12px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;font-size:0.86rem;font-weight:900;">
                <span style="color:#f8fafc;">${escapeHtml(cName)}</span>
                <span style="color:#fde047;direction:ltr;">${escapeHtml(amtTxt)} SDG</span>
                <span style="color:#7dd3fc;direction:ltr;">#${escapeHtml(trNo)}</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <button type="button" id="inb-dismiss-cancel" style="height:40px;border-radius:10px;border:1.5px solid rgba(148,163,184,0.4);background:rgba(30,41,59,0.8);color:#e2e8f0;font-family:inherit;font-size:0.92rem;font-weight:900;cursor:pointer;">تراجع وإلغاء</button>
                <button type="button" id="inb-dismiss-confirm" style="height:40px;border-radius:10px;border:1.5px solid #fda4af;background:linear-gradient(135deg,#e11d48,#be123c);color:#ffffff;font-family:inherit;font-size:0.92rem;font-weight:900;cursor:pointer;box-shadow:0 6px 18px rgba(225,29,72,0.45);">🗑️ نعم، حذف وتجاهل</button>
            </div>
        </div>`;
        document.body.appendChild(dlg);
        const closeDlg = () => { if (dlg && dlg.parentNode) dlg.remove(); };
        dlg.onclick = (e) => { if (e.target === dlg) closeDlg(); };
        dlg.querySelector('#inb-dismiss-cancel').onclick = closeDlg;
        dlg.querySelector('#inb-dismiss-confirm').onclick = () => {
            const btn = dlg.querySelector('#inb-dismiss-confirm');
            btn.disabled = true; btn.textContent = '⏳ جاري الحذف...';
            const tId = this.getActiveTenantId();
            fetch('api/inbound_receipts.php?action=dismiss&tenant_id=' + encodeURIComponent(tId), {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, tenant_id: tId })
            })
            .then(res => res.json())
            .then(res => {
                closeDlg();
                if (res.success) {
                    this.pruneOrphanedInboundVouchers(null, res.deleted_voucher_no || '', id);
                    this._lastRenderedSig = '';
                    this.refreshReceipts();
                    if (typeof showToast === 'function') showToast('🗑️ تم حذف وتجاهل الإشعار بنجاح', 'info');
                }
            }).catch(() => closeDlg());
        };
    },

    openDirectUploadModal: function() {
        const modal = document.getElementById('inbound-manual-upload-modal');
        if (modal) modal.style.display = 'flex';
    },

    closeManualUploadModal: function() {
        const modal = document.getElementById('inbound-manual-upload-modal');
        if (modal) modal.style.display = 'none';
    },

    handleManualUploadSubmit: async function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('manualReceiptFile');
        const amtVal = document.getElementById('manualAmount').value.trim();
        const tnoVal = document.getElementById('manualTransferNo').value.trim();
        const hasFile = fileInput && fileInput.files && fileInput.files[0];

        if (!hasFile && (!amtVal || !tnoVal)) {
            alert('يرجى إما اختيار صورة الإشعار لفحصها بالذكاء الاصطناعي أو إدخال المبلغ ورقم العملية.');
            return;
        }

        const btn = document.getElementById('btnSubmitManualUpload');
        btn.disabled = true;
        btn.innerHTML = 'جاري الفحص والتنزيل في الحسابات... ⏳';

        const tId = this.getActiveTenantId();
        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('tenant_id', tId);
        formData.append('customer_name', document.getElementById('manualCustName').value.trim());
        formData.append('amount', amtVal);
        formData.append('transfer_no', tnoVal);
        formData.append('customer_phone', document.getElementById('manualCustPhone').value.trim());

        if (hasFile) {
            formData.append('receipt_file', fileInput.files[0]);
        }

        try {
            const res = await fetch('api/inbound_receipts.php?action=upload', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (data.posting && data.posting.voucher_no) {
                    this.syncPostedReceiptToLocalErp(data.receipt || data, data.posting);
                }
                this.closeManualUploadModal();
                this.refreshReceipts();
                this.playNotificationChime();
                if (typeof showToast === 'function') {
                    const msg = data.voucher_no
                        ? `✅ تم فحص الإشعار وتنزيله في الحسابات بنجاح بالسند رقم (${data.voucher_no})!`
                        : '✅ تم حفظ الإشعار بنجاح ونزل في صندوق العمليات!';
                    showToast(msg, 'success');
                }
                // Also populate into current voucher screen immediately
                if (data.id) {
                    setTimeout(() => this.inspectInVoucher(data.id), 300);
                }
            } else {
                alert('خطأ: ' + (data.error || 'فشل الحفظ'));
            }
        } catch(err) {
            alert('حدث خطأ في الاتصال: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'فحص وتنزيل في الحسابات 🚀';
        }
    },

    // ═══ محاكاة إشعار بنكك فوري للاختبار والتجربة الحية ═══
    simulateIncomingReceipt: async function() {
        const sampleNames = [
            'مهدي مكي',
            'همس مهدي شيريهان مكي',
            'دكتور شول',
            'مؤسسة التضامن الهندسية',
            'شركة الرواد للاستيراد والتصدير'
        ];
        const randomName = sampleNames[Math.floor(Math.random() * sampleNames.length)];
        const randomAmt = (Math.floor(Math.random() * 50) + 10) * 10000; // e.g. 100,000 to 600,000
        const randomNo = String(Math.floor(Math.random() * 89999999) + 10000000); // 8-digit transfer no
        const tId = this.getActiveTenantId();

        try {
            const res = await fetch('api/inbound_receipts.php?action=upload', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'upload',
                    tenant_id: tId,
                    customer_name: randomName,
                    customer_phone: '09' + Math.floor(Math.random() * 89999999 + 10000000),
                    amount: randomAmt,
                    transfer_no: randomNo,
                    bank_name: 'بنك الخرطوم (بنكك)',
                    bank_code: '170',
                    receipt_image: 'assets/sample_receipt_bok.png',
                    notes: 'تحويل بنكك تم استلامه وفحصه وتنزيله في الحسابات فورياً'
                })
            });
            const data = await res.json();
            if (data.success) {
                if (data.posting && data.posting.voucher_no) {
                    this.syncPostedReceiptToLocalErp(data.receipt || data, data.posting);
                }
                // Instantly refresh & trigger live sound and toast
                this.refreshReceipts();
            }
        } catch(err) {
            console.error('Simulation error:', err);
        }
    }
};

// Global Helper to toggle drawer
window.toggleInboundReceiptsDrawer = function() {
    window.InboundBankakManager.toggleDrawer();
};

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Auto-initialize when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.InboundBankakManager.init());
} else {
    window.InboundBankakManager.init();
}