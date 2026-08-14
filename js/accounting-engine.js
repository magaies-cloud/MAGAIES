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
        '210201': { code: '210201', name: 'أوراق دفع / شيكات آجلة', type: 'liability', category: 'payables' },
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
            const date = invoiceData.date || new Date().toISOString().split('T')[0];
            const supplierName = invoiceData.supplierName || 'مورد عام';
            const currencyDesc = !isBase ? ` (${invoiceData.grandTotal} ${invoiceData.currency} @ ${rate})` : '';

            // Entries Array
            let entries = [];
            let totalDebitAcc = 0;
            let totalCreditAcc = 0;

            // 1. Full Invoice Value
            // Debit Inventory (110401)
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
            totalDebitAcc += localTotal;

            // Credit Supplier (210101) - FULL AMOUNT
            entries.push({
                accountCode: '210101',
                accountName: `حسابات الموردين [${supplierName}]`,
                debit: 0,
                credit: localTotal,
                fcAmount: invoiceData.fcTotal,
                currency: invoiceData.currency,
                exchangeRate: rate,
                description: `استحقاق فاتورة مشتريات #${invoiceData.invoiceNo} (${supplierName})${currencyDesc}`
            });
            totalCreditAcc += localTotal;

            let allSafes = [];
            let allBanks = [];
            try {
                allSafes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                allBanks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
            } catch(e) {}

            // 2. Process Payments individually (Debit Supplier, Credit Asset)
            // 2.a Cash Payment
            let cashAmtForeign = parseFloat(invoiceData.cashAmount) || 0;
            if (cashAmtForeign > 0) {
                let cashAmtLocal = isBase ? cashAmtForeign : cashAmtForeign * rate;
                
                let safeName = 'الخزينة العامة';
                if (invoiceData.paymentAccountId) {
                    const sObj = allSafes.find(s => String(s.id) === String(invoiceData.paymentAccountId));
                    if (sObj) safeName = sObj.name;
                }

                // Debit Supplier
                entries.push({
                    accountCode: '210101',
                    accountName: `حسابات الموردين [${supplierName}]`,
                    debit: cashAmtLocal,
                    credit: 0,
                    fcAmount: cashAmtForeign,
                    currency: invoiceData.currency,
                    exchangeRate: rate,
                    description: `سداد نقدي من [${safeName}] للفاتورة #${invoiceData.invoiceNo}`
                });
                totalDebitAcc += cashAmtLocal;
                
                // Credit Safe
                entries.push({
                    accountCode: '110101',
                    accountName: `الخزينة [${safeName}]`,
                    debit: 0,
                    credit: cashAmtLocal,
                    fcAmount: cashAmtForeign,
                    currency: invoiceData.currency,
                    exchangeRate: rate,
                    description: `سداد نقدي من [${safeName}] لفاتورة مشتريات #${invoiceData.invoiceNo} (${supplierName})`
                });
                totalCreditAcc += cashAmtLocal;
            }

            // 2.b Bank Transfers
            if (invoiceData.bankTransfers && invoiceData.bankTransfers.length > 0) {
                invoiceData.bankTransfers.forEach(bt => {
                    let btAmtForeign = parseFloat(bt.amount) || 0;
                    if (btAmtForeign > 0) {
                        let btAmtLocal = isBase ? btAmtForeign : btAmtForeign * rate;
                        
                        let bankName = 'البنك';
                        const bObj = allBanks.find(b => String(b.id) === String(bt.bankAccountId));
                        if (bObj) bankName = bObj.name;

                        // Debit Supplier
                        entries.push({
                            accountCode: '210101',
                            accountName: `حسابات الموردين [${supplierName}]`,
                            debit: btAmtLocal,
                            credit: 0,
                            fcAmount: btAmtForeign,
                            currency: invoiceData.currency,
                            exchangeRate: rate,
                            description: `سداد بتحويل بنكي رقم [${bt.reference || ''}] من [${bankName}] - فاتورة #${invoiceData.invoiceNo}`
                        });
                        totalDebitAcc += btAmtLocal;
                        
                        // Credit Bank
                        entries.push({
                            accountCode: '110201',
                            accountName: `البنك [${bankName}]`,
                            debit: 0,
                            credit: btAmtLocal,
                            fcAmount: btAmtForeign,
                            currency: invoiceData.currency,
                            exchangeRate: rate,
                            description: `تحويل بنكي رقم [${bt.reference || ''}] مسحوب من [${bankName}] - فاتورة #${invoiceData.invoiceNo} (${supplierName})`
                        });
                        totalCreditAcc += btAmtLocal;
                    }
                });
            }

            // 2.c Cheques
            if (invoiceData.cheques && invoiceData.cheques.length > 0) {
                invoiceData.cheques.forEach(ch => {
                    let chAmtForeign = parseFloat(ch.amount) || 0;
                    if (chAmtForeign > 0) {
                        let chAmtLocal = isBase ? chAmtForeign : chAmtForeign * rate;
                        
                        let chBankName = ch.bankName || 'البنك';
                        if (!ch.bankName || ch.bankName === 'بنك غير محدد') {
                            const bObj = allBanks.find(b => String(b.id) === String(ch.bankAccountId));
                            if (bObj) chBankName = bObj.name;
                        }

                        // Debit Supplier
                        entries.push({
                            accountCode: '210101',
                            accountName: `حسابات الموردين [${supplierName}]`,
                            debit: chAmtLocal,
                            credit: 0,
                            fcAmount: chAmtForeign,
                            currency: invoiceData.currency,
                            exchangeRate: rate,
                            description: `سداد بشيك رقم [${ch.chequeNo || ''}] من [${chBankName}] - فاتورة #${invoiceData.invoiceNo}`
                        });
                        totalDebitAcc += chAmtLocal;
                        
                        // Credit Bank (Immediate deduction as requested)
                        entries.push({
                            accountCode: '110201',
                            accountName: `البنك [${chBankName}]`,
                            debit: 0,
                            credit: chAmtLocal,
                            fcAmount: chAmtForeign,
                            currency: invoiceData.currency,
                            exchangeRate: rate,
                            description: `شيك رقم [${ch.chequeNo || ''}] مسحوب من [${chBankName}] - فاتورة #${invoiceData.invoiceNo}`
                        });
                        totalCreditAcc += chAmtLocal;
                    }
                });
            }

            const journalVoucher = {
                id: 'jv-' + Date.now(),
                voucherNo: 'JV-PUR-' + invoiceData.invoiceNo,
                refInvoiceNo: invoiceData.invoiceNo,
                type: 'PURCHASE',
                date: date,
                entries: entries,
                totalDebit: totalDebitAcc,
                totalCredit: totalCreditAcc,
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
