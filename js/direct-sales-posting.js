// ════════════════════════════════════════
//  SALES POSTING SYSTEM
// ════════════════════════════════════════

function revertSalesInvoiceEffects(invNo, revertInventory = true) {
    let oldInvStr = localStorage.getItem('erp_inv_' + invNo);
    let oldInv = null;
    if (oldInvStr) {
        try { oldInv = JSON.parse(oldInvStr); } catch(e) {}
    }
    if (!oldInv) {
        let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
        oldInv = allSales.find(inv => inv.invoiceNo === invNo || inv.id === invNo);
    }
    if (!oldInv) return;

    let hadEffects = oldInv.effectsApplied === true;
    if (oldInv.effectsApplied === undefined) {
        hadEffects = (oldInv.status !== 'preliminary' && oldInv.saleType !== 'preliminary');
    }

    const grandAmount = parseFloat(oldInv.grandTotal) || 0;

    if (hadEffects) {
        // 1. Revert Customer Balance
        let creditRevertAmount = 0;
        if (oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') {
            creditRevertAmount = parseFloat(oldInv.creditAmount) || 0;
        } else if (oldInv.paymentMethod === 'credit') {
            creditRevertAmount = grandAmount;
        }
        if (creditRevertAmount > 0 && oldInv.customer && oldInv.customer !== 'walk-in') {
            let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
            const cIdx = customers.findIndex(c => c.id === oldInv.customer);
            if (cIdx > -1) {
                let currentBalance = parseFloat(customers[cIdx].currentBalance) || 0;
                customers[cIdx].currentBalance = currentBalance - creditRevertAmount;
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            }
        }

        // 2. Revert Bank/Safe Balance
        if (oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') {
            let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
            if (oldInv.cashAmount > 0 && oldInv.paymentAccountId) {
                const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
                if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - parseFloat(oldInv.cashAmount);
            }
            if (oldInv.bankTransfers && oldInv.bankTransfers.length > 0) {
                oldInv.bankTransfers.forEach(bt => {
                    const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
                    if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) - parseFloat(bt.amount);
                });
            }
            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
        } else if (oldInv.paymentMethod !== 'credit' && oldInv.paymentAccountId) {
            let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
            const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
            if (bIdx > -1) {
                let currentBalance = parseFloat(banks[bIdx].currentBalance) || 0;
                banks[bIdx].currentBalance = currentBalance - grandAmount;
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            }
        }
    }

    if (revertInventory) {
        // 3. Revert Inventory (Only for backwards compatibility if needed, or if deleting entirely)
        let currentInvData = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
        const mainWh = oldInv.warehouse || 'wh-001';
        if (oldInv.items && Array.isArray(oldInv.items)) {
            for (const row of oldInv.items) {
                const item = ITEMS.find(i => String(i.id) === String(row.itemId));
                if (!item) continue;
                
                let selectedUnitFactor = row.customFactor !== undefined ? row.customFactor : 1;
                let baseQty = 0;
                if (row.customBaseQty !== undefined) {
                    baseQty = row.customBaseQty;
                } else {
                    baseQty = item ? row.quantity * selectedUnitFactor * item.conversionFactor : 0;
                }

                const whId = row.warehouseId || mainWh;
                const whName = WAREHOUSES.find(w => w.id === whId)?.name || 'المخزن الرئيسي';
                
                let itemInv = currentInvData.find(inv => inv.itemId === row.itemId && inv.warehouse === whName);
                if (itemInv) {
                    itemInv.qty = (parseFloat(itemInv.qty) || parseFloat(itemInv.quantity) || 0) + baseQty;
                } else {
                    currentInvData.push({ itemId: row.itemId, warehouse: whName, qty: baseQty, minQty: 10 });
                }
                
                if (item.actualStock && item.actualStock[whId] !== undefined) {
                    item.actualStock[whId] += baseQty;
                }
            }
        }
        localStorage.setItem('erp_inventory_v1', JSON.stringify(currentInvData));
    }
}

function updateSalesPostingStatusBar(status) {
    const bar = document.getElementById('posting-status-bar');
    const icon = document.getElementById('posting-status-icon');
    const text = document.getElementById('posting-status-text');
    const desc = document.getElementById('posting-status-desc');
    const actions = document.getElementById('posting-status-actions');
    
    if (!bar) return;

    const postingMode = window.getPostingMode ? window.getPostingMode() : 'auto';
    
    if (postingMode === 'auto' || status === 'new' || status === 'preliminary' || status === 'draft') {
        bar.style.display = 'none';
        
        // Remove immutability if it was set
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.hasAttribute('data-immutability-locked')) {
                el.disabled = false;
                el.removeAttribute('data-immutability-locked');
            }
        });
        const saveBtn = document.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.style.display = 'flex';
        }
        return;
    }

    bar.style.display = 'flex';
    
    const invNo = document.getElementById('hdr-inv-no').value;

    if (status === 'posted') {
        bar.style.background = 'rgba(16, 185, 129, 0.1)';
        bar.style.borderColor = 'rgba(16, 185, 129, 0.3)';
        icon.innerText = '✅';
        text.innerText = 'فاتورة مُرحّلة';
        text.style.color = '#10b981';
        desc.innerText = 'تم إثبات الأثر المالي لهذه الفاتورة على الأرصدة.';
        
        actions.innerHTML = `
            <button onclick="unpostSalesInvoice('${invNo}')" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                ↩️ إلغاء الترحيل
            </button>
        `;
        
        // Make form immutable
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (!el.disabled && el.id !== 'search-input') {
                el.disabled = true;
                el.setAttribute('data-immutability-locked', 'true');
            }
        });
        const saveBtn = document.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.style.display = 'none';
        }
        
    } else {
        // Confirmed but not posted (pending_delivery)
        bar.style.background = 'rgba(245, 158, 11, 0.1)';
        bar.style.borderColor = 'rgba(245, 158, 11, 0.3)';
        icon.innerText = '⏳';
        text.innerText = 'بانتظار الترحيل';
        text.style.color = '#f59e0b';
        desc.innerText = 'الفاتورة مؤكدة ولكن لم يتم ترحيلها لتؤثر في الأرصدة.';
        
        actions.innerHTML = `
            <button onclick="postSalesInvoice('${invNo}')" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                🚀 ترحيل الآن
            </button>
        `;
        
        // Ensure form is mutable
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.hasAttribute('data-immutability-locked')) {
                el.disabled = false;
                el.removeAttribute('data-immutability-locked');
            }
        });
        const saveBtn = document.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.style.display = 'flex';
        }
    }
}

function postSalesInvoice(invNo) {
    if (!invNo) return;
    
    let str = localStorage.getItem('erp_inv_' + invNo);
    if (!str) return;
    
    let oldInv = null;
    try { oldInv = JSON.parse(str); } catch(e) { return; }
    
    if (oldInv.status === 'posted') {
        showToast('الفاتورة مُرحلة مسبقاً', 'warning');
        return;
    }
    
    const grandAmount = parseFloat(oldInv.grandTotal) || 0;
    
    // Apply financial effects
    // A. Cash
    let banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
    if ((oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') && oldInv.cashAmount > 0 && oldInv.paymentAccountId) {
        const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
        if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + parseFloat(oldInv.cashAmount);
    } else if (oldInv.paymentMethod !== 'credit' && oldInv.paymentMethod !== 'hybrid' && oldInv.paymentMethod !== 'multipay' && oldInv.paymentAccountId) {
        const bIdx = banks.findIndex(b => b.id === oldInv.paymentAccountId);
        if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + grandAmount;
    }
    
    // B. Bank Transfers
    if (oldInv.bankTransfers && oldInv.bankTransfers.length > 0) {
        oldInv.bankTransfers.forEach(bt => {
            const bIdx = banks.findIndex(b => b.id === bt.bankAccountId);
            if (bIdx > -1) banks[bIdx].currentBalance = (parseFloat(banks[bIdx].currentBalance) || 0) + parseFloat(bt.amount);
        });
    }
    localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
    
    // C. Customer Balance
    let creditRevertAmount = 0;
    if (oldInv.paymentMethod === 'hybrid' || oldInv.paymentMethod === 'multipay') {
        creditRevertAmount = parseFloat(oldInv.creditAmount) || 0;
    } else if (oldInv.paymentMethod === 'credit') {
        creditRevertAmount = grandAmount;
    }
    if (creditRevertAmount > 0 && oldInv.customer && oldInv.customer !== 'walk-in') {
        let customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        const cIdx = customers.findIndex(c => c.id === oldInv.customer);
        if (cIdx > -1) {
            customers[cIdx].currentBalance = (parseFloat(customers[cIdx].currentBalance) || 0) + creditRevertAmount;
            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
        }
    }
    
    // Update invoice status
    oldInv.status = 'posted';
    oldInv.effectsApplied = true;
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(oldInv));
    
    // Update in global array
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const idx = allSales.findIndex(inv => inv.invoiceNo === invNo);
    if (idx !== -1) {
        allSales[idx].status = 'posted';
        allSales[idx].effectsApplied = true;
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    }
    
    updateSalesPostingStatusBar('posted');
    showToast('✅ تم ترحيل الفاتورة بنجاح', 'success');
}

function unpostSalesInvoice(invNo) {
    if (!invNo) return;
    
    if (!confirm('هل أنت متأكد من إلغاء ترحيل هذه الفاتورة؟ سيتم عكس التأثيرات المالية.')) return;
    
    revertSalesInvoiceEffects(invNo, false);
    
    let str = localStorage.getItem('erp_inv_' + invNo);
    if (!str) return;
    let oldInv = null;
    try { oldInv = JSON.parse(str); } catch(e) { return; }
    
    oldInv.status = 'pending_delivery';
    oldInv.effectsApplied = false;
    localStorage.setItem('erp_inv_' + invNo, JSON.stringify(oldInv));
    
    let allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
    const idx = allSales.findIndex(inv => inv.invoiceNo === invNo);
    if (idx !== -1) {
        allSales[idx].status = 'pending_delivery';
        allSales[idx].effectsApplied = false;
        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(allSales));
    }
    
    updateSalesPostingStatusBar('pending_delivery');
    showToast('↩️ تم إلغاء ترحيل الفاتورة بنجاح', 'success');
}
