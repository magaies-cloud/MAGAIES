// js/posting-accounts.js

document.addEventListener('DBReady', async () => {
    await loadAllTransactions();
});

// Fallback
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        if (allTransactionsData.length === 0) {
            loadAllTransactions();
        }
    }, 1000);
});

let allTransactionsData = [];
let filteredData = []; 
let currentStatus = 'unposted';
let currentType = 'all';
let selectedRowIndex = -1;

const fmtCurrency = (num) => Number(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const paymentMethodMap = {
    'cash': 'نقدي',
    'credit': 'آجل',
    'cheque': 'شيك',
    'bank': 'تحويل بنكي'
};

function setStatus(status) {
    currentStatus = status;
    document.querySelectorAll('.status-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('status-' + status).classList.add('active');
    
    resetSelection();
    applyFiltersAndRender();
}

function setType(type) {
    currentType = type;
    document.querySelectorAll('.type-chip').forEach(chip => chip.classList.remove('active'));
    document.getElementById('type-' + type).classList.add('active');
    
    resetSelection();
    applyFiltersAndRender();
}

function resetSelection() {
    document.getElementById('check-all-rows').checked = false;
    selectedRowIndex = -1;
    updateStickyBar();
    hideDetailPane();
}

function applyFiltersAndRender() {
    filteredData = allTransactionsData.filter(item => {
        const statusMatch = currentStatus === 'unposted' ? !item.isPosted : item.isPosted;
        if (!statusMatch) return false;
        
        if (currentType !== 'all' && item._typeEn !== currentType) {
            return false;
        }
        
        return true;
    });

    renderMasterTable();
}

async function loadAllTransactions() {
    try {
        const getTenantKey = window.getTenantKey || ((k) => k);
        const keys = [
            { key: 'erp_sales_invoices_v1', type: 'مبيعات', typeEn: 'sales', class: 'type-sales' },
            { key: 'erp_purchase_invoices_v1', type: 'مشتريات', typeEn: 'purchases', class: 'type-purchases' },
            { key: 'erp_single_receipts_v1', type: 'سند قبض', typeEn: 'receipt', class: 'type-receipt' },
            { key: 'erp_receipts_v1', type: 'سند قبض مجمع', typeEn: 'receipt', class: 'type-receipt' },
            { key: 'erp_single_payments_v1', type: 'سند صرف', typeEn: 'payment', class: 'type-payment' },
            { key: 'erp_payments_v1', type: 'سند صرف مجمع', typeEn: 'payment', class: 'type-payment' },
            { key: 'erp_journal_vouchers_v1', type: 'قيد يومية', typeEn: 'journal', class: 'type-journal' }
        ];

        let allData = [];

        for (const meta of keys) {
            let data = [];
            const tKey = getTenantKey(meta.key);
            if (typeof localforage !== 'undefined') {
                data = await localforage.getItem(tKey);
            }
            if (!data) {
                data = JSON.parse(localStorage.getItem(tKey) || localStorage.getItem(meta.key) || '[]');
            }
            if (data && typeof data === 'object' && !Array.isArray(data)) {
                data = Object.values(data);
            }

            if (Array.isArray(data)) {
                const validItems = data.filter(item => item && !item.deleted);
                validItems.forEach(item => {
                    allData.push({
                        ...item,
                        _sourceKey: tKey,
                        _metaKey: meta.key,
                        _typeLabel: meta.type,
                        _typeClass: meta.class,
                        _typeEn: meta.typeEn,
                        _date: new Date(item.date || item.createdAt || 0)
                    });
                });
            }
        }

        allData.sort((a, b) => b._date.getTime() - a._date.getTime());
        allTransactionsData = allData;
        applyFiltersAndRender();

    } catch (e) {
        console.error('Error loading transactions:', e);
        showToast('خطأ في تحميل البيانات.', 'error');
    }
}

function renderMasterTable() {
    const table = document.getElementById('data-table');
    const tbody = document.getElementById('table-body');
    const noDataMsg = document.getElementById('no-data-msg');

    if (filteredData.length === 0) {
        table.style.display = 'none';
        noDataMsg.style.display = 'block';
        return;
    }

    table.style.display = 'table';
    noDataMsg.style.display = 'none';
    
    // Checkbox header visibility
    document.getElementById('check-all-rows').style.visibility = currentStatus === 'unposted' ? 'visible' : 'hidden';

    let html = '';
    filteredData.forEach((item, index) => {
        const amt = item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0;
        const ref = item.id || item.invoiceNo || item.voucherNo || '-';
        const dateStr = !isNaN(item._date.getTime()) ? item._date.toLocaleDateString('en-GB') : '-';
        const customer = item.customerName || item.supplierName || item.customer || item.supplier || item.description || 'بدون اسم';
        const dueDate = item.dueDate ? new Date(item.dueDate).toLocaleDateString('en-GB') : '-';
        const pMethod = paymentMethodMap[item.paymentMethod] || item.paymentMethod || 'نقدي';

        const checkHtml = currentStatus === 'unposted' 
            ? `<input type="checkbox" class="row-checkbox" value="${index}" onchange="onRowCheck(event)" onclick="event.stopPropagation()">` 
            : `<span style="color:#10b981;">✔️</span>`;

        html += `
            <tr class="main-row" id="row-${index}" onclick="selectRow(${index})">
                <td style="text-align: center;">${checkHtml}</td>
                <td><span class="type-badge ${item._typeClass}">${item._typeLabel}</span></td>
                <td class="date-col">${dateStr}</td>
                <td class="ref-col">${ref}</td>
                <td class="customer-col">${customer}</td>
                <td class="date-col">${dueDate}</td>
                <td style="font-size: 0.8rem; color:#cbd5e1;">${pMethod}</td>
                <td class="amount-col" style="text-align:left;">${fmtCurrency(amt)}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function selectRow(index) {
    // Highlight Row
    document.querySelectorAll('.main-row').forEach(r => r.classList.remove('selected'));
    const row = document.getElementById('row-' + index);
    if(row) row.classList.add('selected');
    
    selectedRowIndex = index;
    renderDetailPane(index);
}

function hideDetailPane() {
    document.getElementById('empty-detail').style.display = 'flex';
    document.getElementById('detail-content').style.display = 'none';
}

function renderDetailPane(index) {
    const item = filteredData[index];
    if (!item) return;

    document.getElementById('empty-detail').style.display = 'none';
    const detailContent = document.getElementById('detail-content');
    detailContent.style.display = 'flex';

    const amt = item.totalAmount || item.amountLocal || item.amount || item.netTotal || item.total || item.grandTotal || 0;
    const ref = item.id || item.invoiceNo || item.voucherNo || '-';
    const dateStr = !isNaN(item._date.getTime()) ? item._date.toLocaleDateString('en-GB') : '-';
    const dueDate = item.dueDate ? new Date(item.dueDate).toLocaleDateString('en-GB') : '-';
    const pMethod = paymentMethodMap[item.paymentMethod] || item.paymentMethod || 'نقدي';
    const customer = item.customerName || item.supplierName || item.customer || item.supplier || '-';

    // Build Header
    let html = `
        <div class="detail-header">
            <div class="detail-title">
                <span><span class="type-badge ${item._typeClass}">${item._typeLabel}</span> #${ref}</span>
                <span class="amount-col">${fmtCurrency(amt)}</span>
            </div>
            <div class="detail-meta-grid">
                <div class="meta-item"><span class="meta-label">التاريخ</span><span class="meta-val">${dateStr}</span></div>
                <div class="meta-item"><span class="meta-label">العميل / المورد</span><span class="meta-val">${customer}</span></div>
                <div class="meta-item"><span class="meta-label">طريقة الدفع</span><span class="meta-val">${pMethod}</span></div>
                <div class="meta-item"><span class="meta-label">الاستحقاق</span><span class="meta-val">${dueDate}</span></div>
            </div>
            ${item.description || item.notes || item.statement ? `<div style="margin-top:10px; font-size:0.85rem; color:#cbd5e1;"><span style="color:#94a3b8;">البيان:</span> ${item.description || item.notes || item.statement}</div>` : ''}
        </div>
    `;

    // 1. Items List (If applicable)
    if (item.items && item.items.length > 0) {
        html += `
            <div class="detail-section">
                <div class="section-title">📦 تفاصيل الأصناف والفاتورة</div>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th style="text-align:right;">الصنف</th>
                            <th style="width:50px;">الكمية</th>
                            <th style="width:80px;">السعر</th>
                            <th style="width:90px;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${item.items.map(i => `
                            <tr>
                                <td>${i.itemName || i.item || i.account || '-'}</td>
                                <td>${i.qty || i.quantity || '-'}</td>
                                <td>${i.price ? fmtCurrency(i.price) : '-'}</td>
                                <td><span class="dr-text">${fmtCurrency(i.total || i.amount || 0)}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    // 2. Journal Entries & Financial Flow
    const entries = getExpectedEntries(item, amt);
    
    if (entries.length === 2 && !item.items && !item.entries) {
        // Simple Flow for Receipts/Payments
        const dr = entries.find(e => e.debit > 0);
        const cr = entries.find(e => e.credit > 0);
        if (dr && cr) {
            html += `
                <div class="detail-section">
                    <div class="section-title">💸 التدفق المالي للعملية</div>
                    <div class="flow-diagram">
                        <div class="flow-box" style="border-bottom: 2px solid #f87171;">من حـ/ ${cr.acc}</div>
                        <div class="flow-arrow">
                            <span style="font-size:1.1rem;">⬅️</span>
                            <span style="font-family:'Inter', monospace; color:#10b981;">${fmtCurrency(dr.debit)}</span>
                        </div>
                        <div class="flow-box" style="border-bottom: 2px solid #34d399;">إلى حـ/ ${dr.acc}</div>
                    </div>
                </div>
            `;
        }
    } else if (entries && entries.length > 0) {
        // Complex Journal Entry Table
        html += `
            <div class="detail-section">
                <div class="section-title">📊 التوجيه المحاسبي والتأثير المالي</div>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">مدين</th>
                            <th style="width:80px;">دائن</th>
                            <th>اسم الحساب</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${entries.map(e => `
                            <tr>
                                <td><span class="dr-text">${e.debit > 0 ? fmtCurrency(e.debit) : '-'}</span></td>
                                <td><span class="cr-text">${e.credit > 0 ? fmtCurrency(e.credit) : '-'}</span></td>
                                <td>${e.acc || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    // Bottom Action Button
    if (currentStatus === 'unposted') {
        html += `
            <div style="margin-top: auto; padding-top: 15px;">
                <button class="btn-post-single" onclick="postSingleRow(${index})">اعتماد وترحيل هذه العملية ✔️</button>
            </div>
        `;
    } else {
        html += `
            <div style="margin-top: auto; padding-top: 15px; text-align: center; color: #10b981; font-weight: bold; font-size: 1.1rem; background: rgba(16,185,129,0.1); padding: 12px; border-radius: 8px;">
                ✅ هذه العملية معتمدة ومرحلة مسبقاً
            </div>
        `;
    }

    detailContent.innerHTML = html;
}

// Logic for calculating debits/credits based on meta
function getExpectedEntries(item, amt) {
    let expectedEntries = [];
    const parsedAmt = parseFloat(String(amt).replace(/,/g, '')) || 0;

    if (item._metaKey === 'erp_journal_vouchers_v1' && item.entries) {
        item.entries.forEach(e => {
            expectedEntries.push({ 
                acc: e.account || '-', 
                debit: parseFloat(String(e.debit).replace(/,/g,'')) || 0, 
                credit: parseFloat(String(e.credit).replace(/,/g,'')) || 0, 
                stmt: e.statement || '-' 
            });
        });
        return expectedEntries;
    }

    if (item._metaKey === 'erp_single_payments_v1' || item._metaKey === 'erp_payments_v1') {
        expectedEntries.push({ acc: item.toAccount, debit: parsedAmt, credit: 0, stmt: 'سند صرف' });
        expectedEntries.push({ acc: item.fromAccount || 'safe-001', debit: 0, credit: parsedAmt, stmt: 'سند صرف' });
    } else if (item._metaKey === 'erp_single_receipts_v1' || item._metaKey === 'erp_receipts_v1') {
        expectedEntries.push({ acc: item.toAccount || 'safe-001', debit: parsedAmt, credit: 0, stmt: 'سند قبض' });
        expectedEntries.push({ acc: item.fromAccount, debit: 0, credit: parsedAmt, stmt: 'سند قبض' });
    } else if (item._metaKey === 'erp_sales_invoices_v1') {
        expectedEntries.push({ acc: item.customerId || item.customer || 'عملاء نقدي', debit: parsedAmt, credit: 0, stmt: 'مبيعات' });
        expectedEntries.push({ acc: 'revenue-sales', debit: 0, credit: parsedAmt, stmt: 'إيرادات' });
        if (item.paymentMethod !== 'credit') {
            expectedEntries.push({ acc: item.paymentAccountId || 'safe-001', debit: parsedAmt, credit: 0, stmt: 'سداد' });
            expectedEntries.push({ acc: item.customerId || item.customer || 'عملاء نقدي', debit: 0, credit: parsedAmt, stmt: 'سداد' });
        }
    } else if (item._metaKey === 'erp_purchase_invoices_v1') {
        expectedEntries.push({ acc: 'exp-purchases', debit: parsedAmt, credit: 0, stmt: 'مشتريات' });
        expectedEntries.push({ acc: item.supplierId || item.supplier || item.customer, debit: 0, credit: parsedAmt, stmt: 'مستحقات' });
        if (item.paymentMethod !== 'credit') {
            expectedEntries.push({ acc: item.supplierId || item.supplier || item.customer, debit: parsedAmt, credit: 0, stmt: 'سداد' });
            expectedEntries.push({ acc: item.paymentAccountId || 'safe-001', debit: 0, credit: parsedAmt, stmt: 'سداد' });
        }
    }
    return expectedEntries;
}

function onRowCheck(event) {
    updateStickyBar();
}

function toggleAllRows(source) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => { cb.checked = source.checked; });
    updateStickyBar();
}

function updateStickyBar() {
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
    const bar = document.getElementById('sticky-bar');
    document.getElementById('selected-count').innerText = checkedCount;
    if (checkedCount > 0) {
        bar.classList.add('show');
    } else {
        bar.classList.remove('show');
    }
}

async function saveItemPosted(item) {
    try {
        const tKey = item._sourceKey;
        let data = [];
        if (typeof localforage !== 'undefined') {
            data = await localforage.getItem(tKey);
        }
        if (!data) {
            data = JSON.parse(localStorage.getItem(tKey) || '[]');
        }

        const isObj = data && typeof data === 'object' && !Array.isArray(data);
        const refId = item.id || item.invoiceNo || item.voucherNo;

        if (isObj) {
            if (data[refId]) data[refId].isPosted = true;
        } else if (Array.isArray(data)) {
            const idx = data.findIndex(x => (x.id || x.invoiceNo || x.voucherNo) === refId);
            if (idx > -1) data[idx].isPosted = true;
        }

        if (typeof localforage !== 'undefined') {
            await localforage.setItem(tKey, data);
        } else {
            localStorage.setItem(tKey, JSON.stringify(data));
        }
        return true;
    } catch(e) {
        console.error('Save error', e);
        return false;
    }
}

async function postSingleRow(index) {
    const item = filteredData[index];
    if (!item || item.isPosted) return;

    if (!confirm(`تأكيد ترحيل واعتماد العملية رقم: ${item.id || item.invoiceNo || item.voucherNo}؟`)) {
        return;
    }

    const ok = await saveItemPosted(item);
    if (ok) {
        item.isPosted = true;
        showToast('تم اعتماد العملية وترحيلها بنجاح!', 'success');
        resetSelection();
        applyFiltersAndRender();
    } else {
        showToast('خطأ أثناء عملية الترحيل.', 'error');
    }
}

async function postAllChecked() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkboxes.length === 0) return;

    if (!confirm(`هل أنت متأكد من ترحيل واعتماد ${checkboxes.length} عملية؟`)) return;

    const indices = Array.from(checkboxes).map(cb => parseInt(cb.value)).sort((a,b) => b - a);
    let successCount = 0;

    for (const idx of indices) {
        const item = filteredData[idx];
        if (item && !item.isPosted) {
            const ok = await saveItemPosted(item);
            if (ok) {
                item.isPosted = true;
                successCount++;
            }
        }
    }

    resetSelection();
    applyFiltersAndRender();
    
    if (successCount > 0) {
        showToast(`تم ترحيل ${successCount} عملية بنجاح!`, 'success');
    }
}

function showToast(msg, type='success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icons = { success:'✔️', error:'✖️', warning:'⚠️' };
    toast.innerHTML = `<span>${icons[type] || '🔔'}</span> <span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}
