// js/checks-cycle.js — دورة الشيكات والأوراق التجارية الذكية 360° (متصلة محاسبياً وبالذكاء الاصطناعي)

const CHECKS_STORAGE_KEY = 'erp_checks_v1';
let currentTab = 'incoming';
let checksDB = [];

const STATUS_CANONICAL_MAP = {
    'في الخزنة': 'في الخزينة',
    'في الخزينة': 'في الخزينة',
    'برسم التحصيل': 'تحت التحصيل',
    'تحت التحصيل': 'تحت التحصيل',
    'محصل': 'محصل',
    'مرتجع': 'مرتجع',
    'مظهر': 'مجير',
    'مجيّر': 'مجير',
    'مجير': 'مجير'
};

function normalizeCheckStatus(st) {
    const raw = String(st || '').trim();
    return STATUS_CANONICAL_MAP[raw] || 'في الخزينة';
}

function getTenantStorageKey(baseKey) {
    return (typeof window.getTenantKey === 'function') ? window.getTenantKey(baseKey) : baseKey;
}

function getDefaultSeedChecks() {
    return [
        { id: 101, checkNo: 'CHK-908812', partner: 'مؤسسة النور للتجارة', bank: 'بنك الخرطوم (بنكك)', amount: 450000, dueDate: '2026-11-15', status: 'في الخزينة', type: 'incoming', notes: 'دفعة من حساب مبيعات آجلة' },
        { id: 102, checkNo: 'CHK-443120', partner: 'شركة الأفق للمقاولات', bank: 'بنك فيصل الإسلامي', amount: 1285000, dueDate: '2026-11-10', status: 'تحت التحصيل', type: 'incoming', notes: 'شيك برسم التحصيل البنكي' },
        { id: 103, checkNo: 'CHK-774110', partner: 'محلات البركة التجارية', bank: 'بنك أم درمان الوطني', amount: 320000, dueDate: '2026-10-25', status: 'محصل', type: 'incoming', notes: 'تم إيداعه وتحصيله في الحساب البنكي' },
        { id: 104, checkNo: 'CHK-112098', partner: 'سوبرماركت الهدى', bank: 'بنك الخرطوم (بنكك)', amount: 150000, dueDate: '2026-10-18', status: 'مرتجع', type: 'incoming', notes: 'عدم كفاية الرصيد - تمت إعادة المديونية' },
        { id: 201, checkNo: 'CHK-500192', partner: 'مصنع الصفا للبلاستيك', bank: 'بنك الخرطوم (بنكك)', amount: 620000, dueDate: '2026-11-20', status: 'تحت التحصيل', type: 'outgoing', notes: 'سداد فاتورة مشتريات' },
        { id: 202, checkNo: 'CHK-500195', partner: 'الشركة العالمية للتوريدات', bank: 'بنك فيصل الإسلامي', amount: 940000, dueDate: '2026-10-28', status: 'محصل', type: 'outgoing', notes: 'تم صرفه من الحساب البنكي' }
    ];
}

function loadAndHarvestChecks() {
    const tk = getTenantStorageKey(CHECKS_STORAGE_KEY);
    let stored = [];
    try {
        stored = JSON.parse(localStorage.getItem(tk) || localStorage.getItem(CHECKS_STORAGE_KEY) || '[]');
        if (!Array.isArray(stored)) stored = Object.values(stored || {});
    } catch (e) { stored = []; }

    if (stored.length === 0) {
        stored = getDefaultSeedChecks();
    }

    stored.forEach(c => {
        if (c) c.status = normalizeCheckStatus(c.status);
    });

    const seenCheckNos = new Set(stored.map(c => String(c?.checkNo || '').trim().toUpperCase()));

    // Harvest checks from Sales, Purchases, Single Receipts, Single Payments
    const harvestSources = [
        { key: 'erp_sales_invoices_v1', type: 'incoming', partnerField: 'customerName', fallbackPartner: 'عميل مبيعات' },
        { key: 'erp_single_receipts_v1', type: 'incoming', partnerField: 'fromAccount', fallbackPartner: 'سند قبض شيك' },
        { key: 'erp_purchase_invoices_v1', type: 'outgoing', partnerField: 'supplierName', fallbackPartner: 'مورد مشتريات' },
        { key: 'erp_single_payments_v1', type: 'outgoing', partnerField: 'toAccount', fallbackPartner: 'سند صرف شيك' }
    ];

    let harvestedNew = false;
    harvestSources.forEach(src => {
        try {
            const k = getTenantStorageKey(src.key);
            const list = JSON.parse(localStorage.getItem(k) || localStorage.getItem(src.key) || '[]');
            if (!Array.isArray(list)) return;
            list.forEach(doc => {
                if (!doc || doc.deleted) return;
                const pm = String(doc.paymentMethod || doc.payMethod || '').toLowerCase();
                const chkNo = String(doc.checkNo || doc.chequeNo || '').trim();
                if (pm === 'cheque' || pm === 'check' || chkNo !== '') {
                    const finalNo = chkNo || ('CHK-' + String(doc.voucherNo || doc.invoiceNo || doc.id || Date.now()).slice(-6));
                    if (!seenCheckNos.has(finalNo.toUpperCase())) {
                        seenCheckNos.add(finalNo.toUpperCase());
                        stored.unshift({
                            id: Date.now() + Math.floor(Math.random() * 1000),
                            checkNo: finalNo,
                            partner: doc[src.partnerField] || doc.customer || doc.supplier || src.fallbackPartner,
                            bank: doc.bankName || doc.bankSourceName || 'بنك الخرطوم (بنكك)',
                            amount: Number(doc.totalAmount || doc.amountLocal || doc.amount || doc.netTotal || 0) || 0,
                            dueDate: doc.dueDate || doc.checkDueDate || (doc.date || new Date().toISOString().slice(0, 10)),
                            status: doc.isPosted ? 'محصل' : (src.type === 'incoming' ? 'في الخزينة' : 'تحت التحصيل'),
                            type: src.type,
                            sourceRef: doc.voucherNo || doc.invoiceNo || doc.id || '',
                            notes: doc.description || doc.notes || ''
                        });
                        harvestedNew = true;
                    }
                }
            });
        } catch (e) {}
    });

    checksDB = stored.filter(c => c && !c.deleted);
    if (harvestedNew || !localStorage.getItem(CHECKS_STORAGE_KEY)) {
        saveChecksDB();
    }
}

function saveChecksDB() {
    const tk = getTenantStorageKey(CHECKS_STORAGE_KEY);
    try {
        localStorage.setItem(tk, JSON.stringify(checksDB));
        localStorage.setItem(CHECKS_STORAGE_KEY, JSON.stringify(checksDB));
        if (typeof localforage !== 'undefined') {
            localforage.setItem(tk, checksDB);
        }
    } catch (e) { console.error(e); }
    updateChecksKPIs();
}

function updateChecksKPIs() {
    const fxRate = Number(localStorage.getItem('erp_exchange_rate_usd') || 600) || 600;
    let inPendingAmt = 0, inPendingCnt = 0;
    let outPendingAmt = 0, outPendingCnt = 0;
    let clearedAmt = 0, clearedCnt = 0;
    let bouncedAmt = 0, bouncedCnt = 0;
    const todayStr = new Date().toISOString().slice(0, 10);

    checksDB.forEach(c => {
        if (!c || c.deleted) return;
        const amt = Number(c.amount || 0) || 0;
        const st = normalizeCheckStatus(c.status);
        const isOverdue = (st === 'في الخزينة' || st === 'تحت التحصيل') && c.dueDate && c.dueDate < todayStr;

        if (st === 'محصل' || st === 'مجير') {
            clearedAmt += amt;
            clearedCnt++;
        } else if (st === 'مرتجع' || isOverdue) {
            bouncedAmt += amt;
            bouncedCnt++;
        }

        if (c.type === 'incoming' && (st === 'في الخزينة' || st === 'تحت التحصيل')) {
            inPendingAmt += amt;
            inPendingCnt++;
        } else if (c.type === 'outgoing' && (st === 'في الخزينة' || st === 'تحت التحصيل')) {
            outPendingAmt += amt;
            outPendingCnt++;
        }
    });

    const setEl = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
    setEl('chk-kpi-in-pending', `${inPendingAmt.toLocaleString()} SDG`);
    setEl('chk-kpi-in-pending-sub', `${inPendingCnt} شيك مقبوض (≈ $${Math.round(inPendingAmt / fxRate).toLocaleString()})`);
    setEl('chk-kpi-out-pending', `${outPendingAmt.toLocaleString()} SDG`);
    setEl('chk-kpi-out-pending-sub', `${outPendingCnt} شيك صادر (≈ $${Math.round(outPendingAmt / fxRate).toLocaleString()})`);
    setEl('chk-kpi-cleared', `${clearedAmt.toLocaleString()} SDG`);
    setEl('chk-kpi-cleared-sub', `${clearedCnt} شيك محصل/مجيّر`);
    setEl('chk-kpi-bounced', `${bouncedAmt.toLocaleString()} SDG`);
    setEl('chk-kpi-bounced-sub', `${bouncedCnt} شيك مرتجع أو متأخر الاستحقاق`);
}

function switchTab(tab) {
    currentTab = tab;
    const tabIn = document.getElementById('tab-incoming');
    const tabOut = document.getElementById('tab-outgoing');
    const actIn = document.getElementById('incoming-actions');
    const actOut = document.getElementById('outgoing-actions');
    const thPartner = document.getElementById('th-partner');

    if (tabIn) tabIn.classList.toggle('active', tab === 'incoming');
    if (tabOut) tabOut.classList.toggle('active', tab === 'outgoing');
    if (actIn) actIn.style.display = tab === 'incoming' ? 'flex' : 'none';
    if (actOut) actOut.style.display = tab === 'outgoing' ? 'flex' : 'none';
    if (thPartner) thPartner.textContent = tab === 'incoming' ? 'العميل (Customer)' : 'المورد / المستفيد (Payee)';

    const fStatus = document.getElementById('filter-status');
    const fDate = document.getElementById('filter-date');
    const fSearch = document.getElementById('filter-search');
    const selAll = document.getElementById('selectAll');
    if (fStatus) fStatus.value = 'all';
    if (fDate) fDate.value = '';
    if (fSearch) fSearch.value = '';
    if (selAll) selAll.checked = false;

    renderTable();
}

function getStatusBadge(status, dueDate) {
    const st = normalizeCheckStatus(status);
    const todayStr = new Date().toISOString().slice(0, 10);
    const isOverdue = (st === 'في الخزينة' || st === 'تحت التحصيل') && dueDate && dueDate < todayStr;
    const overdueTag = isOverdue ? ` <span style="background:rgba(239,68,68,0.2);color:#f87171;padding:2px 6px;border-radius:6px;font-size:0.72rem;font-weight:800;">⚠️ مستحق</span>` : '';

    switch (st) {
        case 'في الخزينة': return `<span class="badge badge-safe">📦 في الخزينة</span>` + overdueTag;
        case 'تحت التحصيل': return `<span class="badge badge-bank">🏦 تحت التحصيل</span>` + overdueTag;
        case 'محصل': return `<span class="badge badge-cleared">✅ محصل</span>`;
        case 'مرتجع': return `<span class="badge badge-bounced">🚨 مرتجع</span>`;
        case 'مجير': return `<span class="badge badge-endorsed">🔄 مجيّر</span>${overdueTag}`;
        default: return `<span class="badge">${st}</span>`;
    }
}

function renderTable() {
    const tbody = document.getElementById('checks-tbody');
    if (!tbody) return;
    const statusFilter = document.getElementById('filter-status')?.value || 'all';
    const dateFilter = document.getElementById('filter-date')?.value || '';
    const searchFilter = String(document.getElementById('filter-search')?.value || '').trim().toLowerCase();

    tbody.innerHTML = '';

    const filteredData = checksDB.filter(item => {
        if (!item || item.deleted) return false;
        if (item.type !== currentTab) return false;
        const st = normalizeCheckStatus(item.status);
        if (statusFilter !== 'all' && st !== normalizeCheckStatus(statusFilter)) return false;
        if (dateFilter && item.dueDate > dateFilter) return false;
        if (searchFilter) {
            const hay = `${item.checkNo || ''} ${item.partner || ''} ${item.bank || ''} ${item.notes || ''} ${item.endorsedTo || ''}`.toLowerCase();
            if (!hay.includes(searchFilter)) return false;
        }
        return true;
    });

    if (filteredData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--text-secondary);">لا توجد شيكات مطابقة للبحث في هذا القسم</td></tr>`;
        updateChecksKPIs();
        return;
    }

    filteredData.forEach(item => {
        const tr = document.createElement('tr');
        const endorsedNote = item.endorsedTo ? `<div style="font-size:0.75rem;color:#c084fc;margin-top:2px;">↪ مجيّر إلى: ${item.endorsedTo}</div>` : '';
        tr.innerHTML = `
            <td><input type="checkbox" class="row-checkbox item-checkbox" value="${item.id}"></td>
            <td style="font-family: 'Inter', sans-serif; font-weight: 800; color: var(--primary-accent);">${item.checkNo}</td>
            <td style="font-weight: 700;">${item.partner}${endorsedNote}</td>
            <td>${item.bank}</td>
            <td class="amount-cell">${Number(item.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })} SDG</td>
            <td style="font-family: 'Inter', sans-serif; color: var(--text-secondary); font-weight:700;">${item.dueDate || '-'}</td>
            <td>${getStatusBadge(item.status, item.dueDate)}</td>
            <td style="white-space:nowrap;">
                <button type="button" onclick="sendCheckWhatsAppAlert(${item.id})" title="تذكير/إشعار واتساب" style="background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.35);border-radius:6px;padding:4px 8px;cursor:pointer;font-family:'Tajawal',sans-serif;font-weight:800;font-size:0.78rem;margin-left:4px;">💬 واتساب</button>
                <button type="button" onclick="printSingleCheckSlip(${item.id})" title="طباعة حافظة الشيك QR" style="background:rgba(56,189,248,0.15);color:#38bdf8;border:1px solid rgba(56,189,248,0.35);border-radius:6px;padding:4px 8px;cursor:pointer;font-family:'Tajawal',sans-serif;font-weight:800;font-size:0.78rem;margin-left:4px;">🖨️ QR</button>
                <button type="button" onclick="deleteCheckRecord(${item.id})" title="حذف الشيك" style="background:rgba(239,68,68,0.14);color:#f87171;border:1px solid rgba(239,68,68,0.35);border-radius:6px;padding:4px 8px;cursor:pointer;font-family:'Tajawal',sans-serif;font-weight:800;font-size:0.78rem;">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    updateChecksKPIs();
}

function toggleSelectAll() {
    const isChecked = document.getElementById('selectAll')?.checked || false;
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = isChecked);
}

function recordCheckAccountingEffect(check, oldStatus, newStatus) {
    try {
        const amt = Number(check.amount || 0) || 0;
        if (amt <= 0 || oldStatus === newStatus) return;

        // 1. Update Bank Balance when cleared
        if (newStatus === 'محصل') {
            const bKey = getTenantStorageKey('erp_banks_v1');
            let banks = JSON.parse(localStorage.getItem(bKey) || localStorage.getItem('erp_banks_v1') || '[]');
            if (Array.isArray(banks) && banks.length > 0) {
                let targetBank = banks.find(b => b && String(b.name || '').includes(check.bank)) || banks[0];
                if (targetBank) {
                    const delta = check.type === 'incoming' ? amt : -amt;
                    targetBank.balance = (Number(targetBank.balance || 0) || 0) + delta;
                    localStorage.setItem(bKey, JSON.stringify(banks));
                }
            }
        }

        // 2. Post Auto Journal Voucher into erp_journal_vouchers_v1
        const jvKey = getTenantStorageKey('erp_journal_vouchers_v1');
        let jvs = JSON.parse(localStorage.getItem(jvKey) || localStorage.getItem('erp_journal_vouchers_v1') || '[]');
        if (!Array.isArray(jvs)) jvs = Object.values(jvs || {});

        let debitAcc = 'حساب البنك - ' + (check.bank || 'بنكك');
        let creditAcc = 'أوراق القبض تحت التحصيل - ' + (check.partner || '');
        if (check.type === 'incoming') {
            if (newStatus === 'تحت التحصيل') {
                debitAcc = 'شيكات برسم التحصيل - ' + (check.bank || '');
                creditAcc = 'حافظة أوراق القبض بالخزينة';
            } else if (newStatus === 'محصل') {
                debitAcc = 'حساب البنك - ' + (check.bank || '');
                creditAcc = 'أوراق القبض / ' + (check.partner || '');
            } else if (newStatus === 'مرتجع') {
                debitAcc = 'ذمم العملاء (شيك مرتجع) - ' + (check.partner || '');
                creditAcc = 'أوراق القبض / البنك';
            } else if (newStatus === 'مجير') {
                debitAcc = 'ذمم الموردين - ' + (check.endorsedTo || 'مورد مجيّر له');
                creditAcc = 'أوراق القبض بالخزينة - ' + (check.partner || '');
            }
        } else {
            if (newStatus === 'محصل') {
                debitAcc = 'أوراق الدفع - ' + (check.partner || '');
                creditAcc = 'حساب البنك - ' + (check.bank || '');
            } else if (newStatus === 'مرتجع') {
                debitAcc = 'أوراق الدفع الملغاة';
                creditAcc = 'ذمم الموردين - ' + (check.partner || '');
            }
        }

        const jvNo = 'JV-CHK-' + String(check.checkNo || Date.now()).replace(/[^A-Z0-9]/gi, '').slice(-6);
        jvs.unshift({
            id: jvNo + '-' + Date.now().toString().slice(-3),
            voucherNo: jvNo,
            date: new Date().toISOString().slice(0, 10),
            description: `حركة شيك رقم ${check.checkNo} (${check.partner}) — تحديث الحالة إلى [${newStatus}]`,
            totalDebit: amt,
            totalCredit: amt,
            isPosted: true,
            source: 'checks_cycle',
            lines: [
                { accountName: debitAcc, debit: amt, credit: 0, note: `شيك ${check.checkNo} - ${newStatus}` },
                { accountName: creditAcc, debit: 0, credit: amt, note: `شيك ${check.checkNo} - ${check.partner}` }
            ],
            createdAt: new Date().toISOString()
        });
        localStorage.setItem(jvKey, JSON.stringify(jvs));
        localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvs));

        if (typeof window.logSystemAction === 'function') {
            window.logSystemAction('تحديث حالة شيك', `تم تغيير حالة الشيك ${check.checkNo} (${check.partner}) بمبلغ ${amt.toLocaleString()} SDG من [${oldStatus}] إلى [${newStatus}] وتوليد قيد يومية ${jvNo}`);
        }
    } catch (e) { console.error('Check GL sync error:', e); }
}

function changeSelectedStatus(newStatus) {
    const selectedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (selectedBoxes.length === 0) {
        if (typeof showToast === 'function') showToast('الرجاء تحديد شيك واحد على الأقل لتنفيذ الإجراء', 'warning');
        else alert('الرجاء تحديد شيك واحد على الأقل');
        return;
    }

    const canonStatus = normalizeCheckStatus(newStatus);
    let endorsedTarget = '';
    if (canonStatus === 'مجير') {
        endorsedTarget = prompt('أدخل اسم المورد أو الجهة التي سيتم تجيير (تظهير) الشيك لصالحها:', 'شركة التوريدات الهندسية');
        if (endorsedTarget === null) return;
    }

    selectedBoxes.forEach(box => {
        const id = parseInt(box.value, 10);
        const check = checksDB.find(c => Number(c.id) === id);
        if (check) {
            const oldSt = normalizeCheckStatus(check.status);
            check.status = canonStatus;
            if (canonStatus === 'مجير' && endorsedTarget) {
                check.endorsedTo = endorsedTarget.trim();
            }
            recordCheckAccountingEffect(check, oldSt, canonStatus);
        }
    });

    saveChecksDB();
    const selAll = document.getElementById('selectAll');
    if (selAll) selAll.checked = false;
    renderTable();

    if (typeof showToast === 'function') {
        showToast(`تم تحديث حالة (${selectedBoxes.length}) شيك إلى [${canonStatus}] وترحيل القيد المحاسبي بنجاح`, 'success');
    }
}

window.deleteCheckRecord = function(id) {
    if (!confirm('هل أنت متأكد من حذف هذا الشيك من السجل؟')) return;
    checksDB = checksDB.filter(c => Number(c.id) !== Number(id));
    saveChecksDB();
    renderTable();
    if (typeof showToast === 'function') showToast('تم حذف الشيك بنجاح', 'success');
};

window.sendCheckWhatsAppAlert = function(id) {
    const c = checksDB.find(x => Number(x.id) === Number(id));
    if (!c) return;
    const msg = `🏦 *إشعار شيك تجاري — نظام السجانة ERP*\n` +
        `━━━━━━━━━━━━━━\n` +
        `🔢 *رقم الشيك:* ${c.checkNo}\n` +
        `👤 *الطرف:* ${c.partner}\n` +
        `🏛️ *البنك المسحوب عليه:* ${c.bank}\n` +
        `💰 *المبلغ:* ${Number(c.amount || 0).toLocaleString()} SDG\n` +
        `📅 *تاريخ الاستحقاق:* ${c.dueDate}\n` +
        `📌 *الحالة الحالية:* ${normalizeCheckStatus(c.status)}\n` +
        `━━━━━━━━━━━━━━\n` +
        `يرجى المتابعة والتأكد من توفر الرصيد في تاريخ الاستحقاق.`;
    window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
};

window.printSingleCheckSlip = function(id) {
    const c = checksDB.find(x => Number(x.id) === Number(id));
    if (!c) return;
    printCheckBordereau([c]);
};

window.printCheckBordereau = function(customList) {
    const list = Array.isArray(customList) && customList.length > 0
        ? customList
        : checksDB.filter(c => c && !c.deleted && c.type === currentTab);
    const title = currentTab === 'incoming' ? 'حافظة شيكات مقبوضة (أوراق قبض)' : 'حافظة شيكات صادرة (أوراق دفع)';
    const totalAmt = list.reduce((s, c) => s + (Number(c.amount || 0) || 0), 0);
    const qrPayload = encodeURIComponent(`ELSAJANA-CHECKS|${currentTab}|CNT:${list.length}|SUM:${totalAmt}`);
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${qrPayload}`;

    const win = window.open('', '_blank', 'width=920,height=720');
    if (!win) return;
    win.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>${title}</title>
    <style>
        body{font-family:'Tajawal',sans-serif;padding:24px;color:#0f172a;}
        .hdr{display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #1e293b;padding-bottom:14px;margin-bottom:18px;}
        table{width:100%;border-collapse:collapse;margin-top:12px;}
        th,td{border:1px solid #cbd5e1;padding:9px 10px;text-align:right;font-size:0.9rem;}
        th{background:#f1f5f9;font-weight:800;}
        .tot{margin-top:16px;font-size:1.1rem;font-weight:900;text-align:left;}
    </style></head><body>
    <div class="hdr">
        <div>
            <h2 style="margin:0;">🏛️ نظام السجانة ومقاييس ERP — ${title}</h2>
            <p style="margin:5px 0 0;color:#475569;">تاريخ الإصدار: ${new Date().toLocaleString('ar-EG')} | عدد الشيكات: ${list.length}</p>
        </div>
        <img src="${qrUrl}" alt="QR" width="95" height="95" />
    </div>
    <table>
        <thead><tr><th>#</th><th>رقم الشيك</th><th>العميل / المستفيد</th><th>البنك</th><th>تاريخ الاستحقاق</th><th>الحالة</th><th>المبلغ (SDG)</th></tr></thead>
        <tbody>
            ${list.map((c, i) => `<tr><td>${i + 1}</td><td><b>${c.checkNo}</b></td><td>${c.partner}${c.endorsedTo ? ' (مجيّر لـ ' + c.endorsedTo + ')' : ''}</td><td>${c.bank}</td><td>${c.dueDate}</td><td>${normalizeCheckStatus(c.status)}</td><td><b>${Number(c.amount || 0).toLocaleString()}</b></td></tr>`).join('')}
        </tbody>
    </table>
    <div class="tot">إجمالي قيمة الحافظة: ${totalAmt.toLocaleString()} SDG</div>
    <script>window.onload=function(){setTimeout(function(){window.print();},400);};<\/script>
    </body></html>`);
    win.document.close();
};

window.openAddCheckModal = function() {
    let modal = document.getElementById('add-check-modal-overlay');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'add-check-modal-overlay';
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;';
        modal.innerHTML = `
            <div style="background:var(--surface-color,#0f172a);border:1px solid rgba(59,130,246,0.4);border-radius:18px;max-width:540px;width:100%;padding:22px;color:var(--text-primary,#f8fafc);box-shadow:0 20px 50px rgba(0,0,0,0.5);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:10px;">
                    <h3 style="margin:0;font-size:1.15rem;color:#38bdf8;">➕ تسجيل شيك تجاري جديد</h3>
                    <button type="button" onclick="document.getElementById('add-check-modal-overlay').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:1.2rem;cursor:pointer;">✖</button>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.8rem;font-weight:700;display:block;margin-bottom:4px;">نوع الشيك</label>
                        <select id="new-chk-type" class="filter-input" style="width:100%;">
                            <option value="incoming">📥 شيك مقبوض (أوراق قبض)</option>
                            <option value="outgoing">📤 شيك صادر (أوراق دفع)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:700;display:block;margin-bottom:4px;">رقم الشيك</label>
                        <input type="text" id="new-chk-no" class="filter-input" style="width:100%;" placeholder="CHK-884920">
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:700;display:block;margin-bottom:4px;">اسم العميل / المستفيد</label>
                        <input type="text" id="new-chk-partner" class="filter-input" style="width:100%;" placeholder="اسم العميل أو المورد">
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:700;display:block;margin-bottom:4px;">البنك المسحوب عليه</label>
                        <input type="text" id="new-chk-bank" class="filter-input" style="width:100%;" value="بنك الخرطوم (بنكك)">
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:700;display:block;margin-bottom:4px;">المبلغ (SDG)</label>
                        <input type="number" id="new-chk-amount" class="filter-input" style="width:100%;" placeholder="0.00">
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:700;display:block;margin-bottom:4px;">تاريخ الاستحقاق</label>
                        <input type="date" id="new-chk-due" class="filter-input" style="width:100%;" value="${new Date().toISOString().slice(0, 10)}">
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px;">
                    <button type="button" onclick="document.getElementById('add-check-modal-overlay').style.display='none'" class="btn" style="background:rgba(148,163,184,0.2);color:#cbd5e1;">إلغاء</button>
                    <button type="button" onclick="saveNewCheckFromModal()" class="btn btn-primary">💾 حفظ الشيك وترحيله للحافظة</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    document.getElementById('new-chk-type').value = currentTab;
    document.getElementById('new-chk-no').value = 'CHK-' + Math.floor(100000 + Math.random() * 900000);
    modal.style.display = 'flex';
};

window.saveNewCheckFromModal = function() {
    const type = document.getElementById('new-chk-type').value;
    const checkNo = document.getElementById('new-chk-no').value.trim();
    const partner = document.getElementById('new-chk-partner').value.trim();
    const bank = document.getElementById('new-chk-bank').value.trim() || 'بنك الخرطوم';
    const amount = Number(document.getElementById('new-chk-amount').value || 0);
    const dueDate = document.getElementById('new-chk-due').value || new Date().toISOString().slice(0, 10);

    if (!checkNo || !partner || amount <= 0) {
        if (typeof showToast === 'function') showToast('يرجى إدخال رقم الشيك واسم الطرف والمبلغ بشكل صحيح', 'error');
        return;
    }

    const newItem = {
        id: Date.now(),
        checkNo,
        partner,
        bank,
        amount,
        dueDate,
        status: type === 'incoming' ? 'في الخزينة' : 'تحت التحصيل',
        type
    };
    checksDB.unshift(newItem);
    saveChecksDB();
    document.getElementById('add-check-modal-overlay').style.display = 'none';
    if (currentTab !== type) switchTab(type);
    else renderTable();

    if (typeof showToast === 'function') showToast(`تم تسجيل الشيك ${checkNo} بنجاح في الحافظة`, 'success');
};

window.openChecksAIRadarModal = function() {
    const todayStr = new Date().toISOString().slice(0, 10);
    const overdueIn = checksDB.filter(c => c.type === 'incoming' && (c.status === 'في الخزينة' || c.status === 'تحت التحصيل') && c.dueDate < todayStr);
    const bounced = checksDB.filter(c => normalizeCheckStatus(c.status) === 'مرتجع');
    const outDueSoon = checksDB.filter(c => c.type === 'outgoing' && normalizeCheckStatus(c.status) !== 'محصل');

    const totalInPending = checksDB.filter(c => c.type === 'incoming' && (c.status === 'في الخزينة' || c.status === 'تحت التحصيل')).reduce((s, c) => s + Number(c.amount || 0), 0);
    const totalOutPending = outDueSoon.reduce((s, c) => s + Number(c.amount || 0), 0);
    const coverageRatio = totalOutPending > 0 ? Math.round((totalInPending / totalOutPending) * 100) : 100;

    let modal = document.getElementById('checks-ai-radar-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'checks-ai-radar-modal';
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.78);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;';
        document.body.appendChild(modal);
    }
    modal.innerHTML = `
        <div style="background:var(--surface-color,#0f172a);border:1.5px solid rgba(16,185,129,0.5);border-radius:18px;max-width:650px;width:100%;padding:22px;color:var(--text-primary,#f8fafc);box-shadow:0 20px 60px rgba(0,0,0,0.6);">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:12px;margin-bottom:14px;">
                <h3 style="margin:0;color:#34d399;font-size:1.2rem;">🤖 رادار استحقاق ومخاطر الشيكات والسيولة AI</h3>
                <button type="button" onclick="document.getElementById('checks-ai-radar-modal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:1.2rem;cursor:pointer;">✖</button>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px;">
                <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);padding:10px;border-radius:10px;text-align:center;">
                    <div style="font-size:0.76rem;color:#94a3b8;">نسبة تغطية أوراق القبض للدفع</div>
                    <div style="font-size:1.35rem;font-weight:900;color:${coverageRatio >= 100 ? '#10b981' : '#f59e0b'};">${coverageRatio}%</div>
                </div>
                <div style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);padding:10px;border-radius:10px;text-align:center;">
                    <div style="font-size:0.76rem;color:#94a3b8;">شيكات مقبوضة حان استحقاقها</div>
                    <div style="font-size:1.35rem;font-weight:900;color:#fbbf24;">${overdueIn.length} شيك</div>
                </div>
                <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);padding:10px;border-radius:10px;text-align:center;">
                    <div style="font-size:0.76rem;color:#94a3b8;">شيكات مرتجعة تتطلب ملاحقة</div>
                    <div style="font-size:1.35rem;font-weight:900;color:#f87171;">${bounced.length} شيك</div>
                </div>
            </div>
            <div style="background:rgba(15,23,42,0.65);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:14px;font-size:0.88rem;line-height:1.7;">
                <div>✅ <b>توصية التحصيل الفوري:</b> يوجد (${overdueIn.length}) شيك في الخزينة أو تحت التحصيل حلّ تاريخ استحقاقها ويجب إيداعها أو تحصيلها فوراً لتعزيز سيولة البنك.</div>
                <div>⚠️ <b>توصية التجيير الذكي:</b> يمكنك تجيير أوراق القبض بالخزينة مباشرة لسداد التزامات الموردين (${totalOutPending.toLocaleString()} SDG) دون سحب سيولة نقدية من الخزينة.</div>
                <div>🚨 <b>مخاطر الارتجاع:</b> الشيكات المرتجعة (${bounced.length}) تم عكسها تلقائياً على ذمم العملاء في القيود اليومية وأعمار الديون.</div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px;">
                <a href="ai_money_advisor.php" class="btn btn-primary" style="text-decoration:none;">فتح مستشار الأموال الشامل AI ←</a>
                <button type="button" onclick="document.getElementById('checks-ai-radar-modal').style.display='none'" class="btn" style="background:rgba(148,163,184,0.2);color:#cbd5e1;">إغلاق</button>
            </div>
        </div>
    `;
    modal.style.display = 'flex';
};

document.addEventListener('DOMContentLoaded', () => {
    loadAndHarvestChecks();
    renderTable();
});
