/* ═════════════════════════════════════════════════════════════════════
   ⚡ MAGAIEES ERP 2026 - AUTOMATED ACCOUNTING ENGINE & CHART OF ACCOUNTS
   ═════════════════════════════════════════════════════════════════════ */

window.AccountingEngine = {
    // Standard Chart of Accounts Master Map
    CHART_OF_ACCOUNTS: {
        '110101': { code: '110101', name: 'الصندوق الرئيسي / الخزينة', type: 'asset', category: 'cash' },
        '110201': { code: '110201', name: 'البنك / الحسابات المصرفية', type: 'asset', category: 'bank' },
        '110301': { code: '110301', name: 'حسابات العملاء (المدينون)', type: 'asset', category: 'receivables' },
        '110401': { code: '110401', name: 'مخزون البضائع والمنتجات', type: 'asset', category: 'inventory' },
        '210101': { code: '210101', name: 'حسابات الموردين (الدائنون)', type: 'liability', category: 'payables' },
        '410101': { code: '410101', name: 'إيرادات المبيعات', type: 'revenue', category: 'sales' },
        '510101': { code: '510101', name: 'تكلفة المشتريات / البضاعة المباعة', type: 'expense', category: 'cost' }
    },

    // Post Double-Entry Journal Voucher for Purchase Invoices
    postPurchaseInvoice: function(invoiceData) {
        try {
            if (!invoiceData || !invoiceData.invoiceNo) return null;

            const isBase = (!invoiceData.currency || invoiceData.currency === 'SDG');
            const rate = parseFloat(invoiceData.exchangeRate) || 1;
            
            // Use explicitly calculated local total for accounting balance, fallback to old total if missing
            const localTotal = invoiceData.localTotal !== undefined ? invoiceData.localTotal : (isBase ? parseFloat(invoiceData.grandTotal) : parseFloat(invoiceData.grandTotal) * rate);
            
            // Calculate total paid explicitly from invoice data
            let paidForeign = 0;
            paidForeign += parseFloat(invoiceData.cashAmount) || 0;
            if (invoiceData.bankTransfers && invoiceData.bankTransfers.length > 0) {
                paidForeign += invoiceData.bankTransfers.reduce((sum, bt) => sum + (parseFloat(bt.amount) || 0), 0);
            }
            if (invoiceData.cheques && invoiceData.cheques.length > 0) {
                paidForeign += invoiceData.cheques.reduce((sum, ch) => sum + (parseFloat(ch.amount) || 0), 0);
            }
            const paidLocal = isBase ? paidForeign : paidForeign * rate;
            const remainingLocal = localTotal - paidLocal;

            const date = invoiceData.date || new Date().toISOString().split('T')[0];
            const supplierName = invoiceData.supplierName || 'مورد عام';
            
            const currencyDesc = !isBase ? ` (${invoiceData.grandTotal} ${invoiceData.currency} @ ${rate})` : '';

            // Entries Array
            let entries = [];

            // 1. Debit Inventory Account (110401)
            entries.push({
                accountCode: '110401',
                accountName: 'مخزون البضائع والمنتجات',
                debit: localTotal,
                credit: 0,
                fcAmount: invoiceData.fcTotal,
                currency: invoiceData.currency,
                exchangeRate: rate,
                description: `استلام بضاعة بموجب فاتورة مشتريات رقم #${invoiceData.invoiceNo}${currencyDesc}`
            });

            // 2. Credit Paid Cash/Bank if any
            if (paidLocal > 0) {
                const paymentAccountCode = invoiceData.paymentMethod === 'bank' || invoiceData.paymentMethod === 'multipay' ? '110201' : '110101';
                const paymentAccountName = invoiceData.paymentMethod === 'bank' || invoiceData.paymentMethod === 'multipay' ? 'البنك / الحسابات المصرفية' : 'الصندوق الرئيسي / الخزينة';
                entries.push({
                    accountCode: paymentAccountCode,
                    accountName: paymentAccountName,
                    debit: 0,
                    credit: paidLocal,
                    fcAmount: paidForeign,
                    currency: invoiceData.currency,
                    exchangeRate: rate,
                    description: `سداد نقدي/بنكي لفاتورة مشتريات #${invoiceData.invoiceNo} (${supplierName})${currencyDesc}`
                });
            }

            // 3. Credit Supplier Payable Account (210101) for remaining balance
            if (remainingLocal > 0 || paidLocal === 0) {
                entries.push({
                    accountCode: '210101',
                    accountName: `حسابات الموردين [${supplierName}]`,
                    debit: 0,
                    credit: remainingLocal,
                    fcAmount: invoiceData.fcTotal - paidForeign,
                    currency: invoiceData.currency,
                    exchangeRate: rate,
                    description: `استحقاق آجل لفاتورة مشتريات #${invoiceData.invoiceNo} (${supplierName})${currencyDesc}`
                });
            }

            const journalVoucher = {
                id: 'jv-' + Date.now(),
                voucherNo: 'JV-PUR-' + invoiceData.invoiceNo,
                refInvoiceNo: invoiceData.invoiceNo,
                type: 'PURCHASE',
                date: date,
                entries: entries,
                totalDebit: localTotal,
                totalCredit: localTotal,
                supplierName: supplierName,
                createdAt: new Date().toISOString()
            };

            // Save Journal Entries to LocalStorage
            let existingVouchers = JSON.parse(localStorage.getItem('erp_journal_entries_v1')) || [];
            // Replace if invoice JV already exists
            existingVouchers = existingVouchers.filter(v => v.refInvoiceNo !== invoiceData.invoiceNo);
            existingVouchers.unshift(journalVoucher);
            localStorage.setItem('erp_journal_entries_v1', JSON.stringify(existingVouchers));

            console.log('✅ [AccountingEngine] Auto Journal Voucher Posted:', journalVoucher);
            return journalVoucher;
        } catch (e) {
            console.error('❌ [AccountingEngine] Error posting journal voucher:', e);
            return null;
        }
    }
};
