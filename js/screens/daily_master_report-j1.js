        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('report-date-input');
            const todayStr = new Date().toISOString().split('T')[0];
            dateInput.value = todayStr;

            dateInput.addEventListener('change', function () {
                renderMasterDailyReport(this.value);
            });

            renderMasterDailyReport(todayStr);
        });

        function renderMasterDailyReport(targetDate) {
            try {
                // Fetch ERP Collections from Local Storage & DB Bridge (exclude archived split parents)
                const sales = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []).filter(x => !x.deleted && !x.wasSplit && x.saleType !== 'preliminary' && x.status !== 'preliminary');
                const purchases = (JSON.parse(localStorage.getItem('erp_purchase_invoices_v1')) || []).filter(x => !x.deleted && !x.isGRN && !x.isReturn && !x.isAdjustment);
                const salesReturns = (JSON.parse(localStorage.getItem('erp_sales_returns_v1')) || []).filter(x => !x.deleted);
                const purchaseReturns = (JSON.parse(localStorage.getItem('erp_purchase_returns_v1')) || []).filter(x => !x.deleted);
                
                const items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1')) || [];
                const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                const expenses = JSON.parse(localStorage.getItem('erp_expenses_v1')) || [];
                const multiPayments = JSON.parse(localStorage.getItem('erp_payments_v1')) || [];
                const multiReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
                const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
                const customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                const suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                const warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                const checks = JSON.parse(localStorage.getItem('erp_checks_v1')) || [];

                // Filter Target Date
                const matchDmr = (d) => window.matchDmrPeriod ? window.matchDmrPeriod(d, targetDate) : (d === targetDate);
                const todaySales = sales.filter(x => matchDmr(x.date));
                const todayPurchases = purchases.filter(x => matchDmr(x.date));
                const todaySalesReturns = salesReturns.filter(x => matchDmr(x.date));
                const todayPurchaseReturns = purchaseReturns.filter(x => matchDmr(x.date));

                const todayPayments = payments.filter(x => matchDmr(x.date) && !x.deleted);
                const todayReceipts = receipts.filter(x => matchDmr(x.date) && !x.deleted);
                const todayExpenses = expenses.filter(x => matchDmr(x.date) && !x.deleted);
                const todayMultiPayments = multiPayments.filter(x => matchDmr(x.date) && !x.deleted);
                const todayMultiReceipts = multiReceipts.filter(x => matchDmr(x.date) && !x.deleted);
                const todayJvs = jvs.filter(x => matchDmr(x.date) && !x.deleted);
                const todayChecks = checks.filter(x => (matchDmr(x.date) || matchDmr(x.dueDate)) && !x.deleted);

                // Calculate KPIs
                const totalSalesAmt = todaySales.reduce((sum, x) => sum + (parseFloat(x.grandTotal || x.totalAmount || x.total) || 0), 0) - todaySalesReturns.reduce((sum, x) => sum + (parseFloat(x.grandTotal || x.totalAmount || x.total) || 0), 0);
                const totalPurchasesAmt = todayPurchases.reduce((sum, x) => sum + (parseFloat(x.grandTotal || x.totalAmount || x.total) || 0), 0) - todayPurchaseReturns.reduce((sum, x) => sum + (parseFloat(x.grandTotal || x.totalAmount || x.total) || 0), 0);
                
                const totalReceiptsAmt = todayReceipts.reduce((sum, x) => sum + (parseFloat(x.amount || x.amountLocal || x.totalAmount) || 0), 0) + todayMultiReceipts.reduce((sum, x) => sum + (parseFloat(x.totalAmount || x.amount) || 0), 0);
                const totalPaymentsAmt = todayPayments.reduce((sum, x) => sum + (parseFloat(x.amount || x.amountLocal || x.totalAmount) || 0), 0) + todayMultiPayments.reduce((sum, x) => sum + (parseFloat(x.totalAmount || x.amount) || 0), 0) + todayExpenses.reduce((sum, x) => sum + (parseFloat(x.amount || x.amountLocal) || 0), 0);
                
                const netFlow = totalReceiptsAmt - totalPaymentsAmt;
                const transfers = (JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1')) || []).filter(x => x && !x.deleted && (window.matchDmrPeriod ? window.matchDmrPeriod(x.date, targetDate) : x.date === targetDate));
                const adjustments = (JSON.parse(localStorage.getItem('erp_inventory_adjustments_v1')) || []).filter(x => x && !x.deleted && (window.matchDmrPeriod ? window.matchDmrPeriod(x.date, targetDate) : x.date === targetDate));

                let stockMoveCount = 0;
                todaySales.forEach(inv => { stockMoveCount += (inv.items || []).length; });
                todayPurchases.forEach(inv => { stockMoveCount += (inv.items || inv.lines || []).length; });
                todaySalesReturns.forEach(inv => { stockMoveCount += (inv.items || []).length; });
                todayPurchaseReturns.forEach(inv => { stockMoveCount += (inv.items || inv.lines || []).length; });
                transfers.forEach(tr => { stockMoveCount += (tr.items || []).length; });
                adjustments.forEach(ad => { stockMoveCount += (ad.items || []).length; });

                window._lastDailyStats = { targetDate, totalSalesAmt, totalPurchasesAmt, totalReceiptsAmt, totalPaymentsAmt, netFlow, stockMoveCount, jvCount: todayJvs.length, chkCount: todayChecks.length };

                // Update KPI Labels
                document.getElementById('kpi-sales').textContent = totalSalesAmt.toLocaleString() + ' SDG';
                document.getElementById('kpi-purchases').textContent = totalPurchasesAmt.toLocaleString() + ' SDG';
                document.getElementById('kpi-receipts').textContent = totalReceiptsAmt.toLocaleString() + ' SDG';
                document.getElementById('kpi-payments').textContent = totalPaymentsAmt.toLocaleString() + ' SDG';
                document.getElementById('kpi-netflow').textContent = netFlow.toLocaleString() + ' SDG';
                document.getElementById('kpi-stock-moves').textContent = stockMoveCount + ' حركة';

                // Helper Name Resolvers
                const resolveClientName = (id) => {
                    if(!id) return '-';
                    const c = customers.find(x => x.id == id || x.code == id);
                    if(c) return c.name;
                    const s = suppliers.find(x => x.id == id || x.code == id);
                    if(s) return s.name;
                    return id;
                };

                const resolveWhName = (id) => {
                    if(!id) return 'المستودع الرئيسي';
                    const w = warehouses.find(x => x.id == id || x.code == id);
                    return w ? w.name : id;
                };

                // 1. Render Financial Vouchers & Expenses Table
                let finHtml = '';
                todayReceipts.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند قبض مفرد</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.description || x.notes || 'قبض نقدية'}</td>
                            <td>${resolveClientName(x.customer || x.clientId)}</td>
                            <td>${x.cashBox || x.bank || 'الخزينة الرئيسية'}</td>
                            <td class="text-emerald-400 font-black">+${(parseFloat(x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayMultiReceipts.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند قبض مجمع</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.notes || 'قبض مجمع'}</td>
                            <td>-</td>
                            <td>${x.cashBox || 'الخزينة الرئيسية'}</td>
                            <td class="text-emerald-400 font-black">+${(parseFloat(x.totalAmount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayPayments.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-rose-500/20 text-rose-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند صرف مفرد</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.description || x.notes || 'صرف نقدية'}</td>
                            <td>${resolveClientName(x.customer || x.clientId)}</td>
                            <td>${x.cashBox || x.bank || 'الخزينة الرئيسية'}</td>
                            <td class="text-rose-400 font-black">-${(parseFloat(x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayMultiPayments.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-rose-500/20 text-rose-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند صرف مجمع</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.notes || 'صرف مجمع'}</td>
                            <td>-</td>
                            <td>${x.cashBox || 'الخزينة الرئيسية'}</td>
                            <td class="text-rose-400 font-black">-${(parseFloat(x.totalAmount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayExpenses.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-amber-500/20 text-amber-400 px-2.5 py-1 rounded-md text-xs font-extrabold">سند منصرفات</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${x.description || x.category || 'مصروف عمومي'}</td>
                            <td>حساب المصروفات</td>
                            <td>${x.cashBox || 'الصندوق الرئيسي'}</td>
                            <td class="text-rose-400 font-black">-${(parseFloat(x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayJvs.forEach(x => {
                    finHtml += `
                        <tr>
                            <td><span class="bg-purple-500/20 text-purple-400 px-2.5 py-1 rounded-md text-xs font-extrabold">قيد محاسبي اليومية</span></td>
                            <td>#${x.voucherNo || x.id || '-'}</td>
                            <td>${x.description || x.statement || 'قيد تسوية مزدوج'}</td>
                            <td>-</td>
                            <td>شجرة الحسابات العامة</td>
                            <td class="font-black">${(parseFloat(x.totalDebit || x.totalAmount || x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                document.getElementById('financial-vouchers-tbody').innerHTML = finHtml || `<tr><td colspan="6" class="text-center py-5 text-slate-400 font-bold">لا توجد حركات أو سندات مالية مسجلة في هذا التاريخ.</td></tr>`;

                // 2. Render Invoices Table
                let invHtml = '';
                todaySales.forEach(x => {
                    let itemsList = (x.items || []).map(item => `${item.name} (${item.quantity || item.qty} وحدات)`).join('، ');
                    let splitBadge = x.isSplit ? `<div class="text-[11px] text-pink-400 font-bold mt-0.5">✂️ أصل #${x.rootInvoice || x.parentInvoice} • تقسيم #${x.splitBatchNo || 1}</div>` : '';
                    invHtml += `
                        <tr>
                            <td><span class="bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md text-xs font-extrabold">${x.isSplit ? 'فاتورة مقسمة ✂️' : 'فاتورة مبيعات'}</span></td>
                            <td>#${x.invoiceNo || '-'}${splitBadge}</td>
                            <td>${resolveClientName(x.customer || x.customerId)}</td>
                            <td>${x.paymentMethod === 'credit' ? 'آجل 💳' : 'نقداً 💵'}</td>
                            <td class="text-xs text-slate-300">${itemsList || '-'}</td>
                            <td class="text-emerald-400 font-black">${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayPurchases.forEach(x => {
                    let itemsList = (x.items || x.lines || []).map(item => `${item.name || item.itemId} (${item.quantity || item.qty} وحدات)`).join('، ');
                    invHtml += `
                        <tr>
                            <td><span class="bg-amber-500/20 text-amber-400 px-2.5 py-1 rounded-md text-xs font-extrabold">فاتورة مشتريات</span></td>
                            <td>#${x.invoiceNo || '-'}</td>
                            <td>${resolveClientName(x.supplier || x.supplierId || x.customer)}</td>
                            <td>${x.paymentMethod === 'credit' ? 'آجل 💳' : 'نقداً 💵'}</td>
                            <td class="text-xs text-slate-300">${itemsList || '-'}</td>
                            <td class="text-amber-400 font-black">${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todaySalesReturns.forEach(x => {
                    invHtml += `
                        <tr>
                            <td><span class="bg-rose-500/20 text-rose-400 px-2.5 py-1 rounded-md text-xs font-extrabold">مردود مبيعات</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${resolveClientName(x.customer || x.customerId)}</td>
                            <td>مسترد</td>
                            <td class="text-xs text-slate-300">إرجاع صنف مبيعات</td>
                            <td class="text-rose-400 font-black">-${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                todayPurchaseReturns.forEach(x => {
                    invHtml += `
                        <tr>
                            <td><span class="bg-purple-500/20 text-purple-400 px-2.5 py-1 rounded-md text-xs font-extrabold">مردود مشتريات</span></td>
                            <td>#${x.id || '-'}</td>
                            <td>${resolveClientName(x.supplier || x.supplierId)}</td>
                            <td>مسترد</td>
                            <td class="text-xs text-slate-300">إرجاع صنف مشتريات</td>
                            <td class="text-purple-400 font-black">+${(parseFloat(x.grandTotal) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                document.getElementById('invoices-tbody').innerHTML = invHtml || `<tr><td colspan="6" class="text-center py-5 text-slate-400 font-bold">لا توجد فواتير مبيعات أو مشتريات مسجلة في هذا التاريخ.</td></tr>`;

                // 3. Render Checks & Bank Transactions Table
                let checkHtml = '';
                todayChecks.forEach(x => {
                    checkHtml += `
                        <tr>
                            <td><span class="bg-purple-500/20 text-purple-400 px-2.5 py-1 rounded-md text-xs font-extrabold">${x.type === 'incoming' ? 'شيك مقبوض' : 'شيك صادر'}</span></td>
                            <td>#${x.checkNo || x.checkNumber || x.id || '-'}</td>
                            <td>${x.bank || x.bankName || 'البنك'}</td>
                            <td>${x.dueDate || x.date || '-'}</td>
                            <td>${resolveClientName(x.partner || x.payee || x.drawer)} (${x.status || ''})</td>
                            <td class="text-purple-400 font-black">${(parseFloat(x.amount) || 0).toLocaleString()} SDG</td>
                        </tr>`;
                });
                document.getElementById('checks-tbody').innerHTML = checkHtml || `<tr><td colspan="6" class="text-center py-5 text-slate-400 font-bold">لا توجد حركات شيكات أو تحويلات بنكية في هذا التاريخ.</td></tr>`;

                // 4. Render Stock Movements Table
                let stockHtml = '';
                todaySales.forEach(inv => {
                    (inv.items || []).forEach(line => {
                        stockHtml += `
                            <tr>
                                <td>${line.name}</td>
                                <td><span class="bg-rose-500/20 text-rose-400 px-2.5 py-1 rounded-md text-xs font-extrabold">صرف (بيع)</span></td>
                                <td class="text-rose-400 font-black">-${line.quantity || line.qty}</td>
                                <td>${resolveWhName(inv.deliveryWarehouse || line.warehouseId)}</td>
                                <td>فاتورة مبيعات #${inv.invoiceNo}</td>
                            </tr>`;
                    });
                });
                todayPurchases.forEach(inv => {
                    (inv.items || inv.lines || []).forEach(line => {
                        stockHtml += `
                            <tr>
                                <td>${line.name || line.itemId}</td>
                                <td><span class="bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md text-xs font-extrabold">إدخال (شراء)</span></td>
                                <td class="text-emerald-400 font-black">+${line.quantity || line.qty}</td>
                                <td>${resolveWhName(line.warehouseId || inv.warehouseId)}</td>
                                <td>فاتورة مشتريات #${inv.invoiceNo}</td>
                            </tr>`;
                    });
                });
                transfers.forEach(tr => {
                    (tr.items || []).forEach(line => {
                        stockHtml += `
                            <tr>
                                <td>${line.itemName || line.name || line.itemId}</td>
                                <td><span class="bg-sky-500/20 text-sky-400 px-2.5 py-1 rounded-md text-xs font-extrabold">مناقلة مخزنية 🔄</span></td>
                                <td class="text-sky-400 font-black">${line.quantity}</td>
                                <td>${tr.fromWarehouse} ⬅️ ${tr.toWarehouse}</td>
                                <td>سند تحويل #${tr.id || tr.transferNo}</td>
                            </tr>`;
                    });
                });
                adjustments.forEach(ad => {
                    (ad.items || []).forEach(line => {
                        const dQty = parseFloat(line.diffQty ?? line.quantity ?? 0);
                        stockHtml += `
                            <tr>
                                <td>${line.name || line.itemId}</td>
                                <td><span class="bg-amber-500/20 text-amber-400 px-2.5 py-1 rounded-md text-xs font-extrabold">تسوية جردية ⚖️</span></td>
                                <td class="font-black" style="color:${dQty>=0?'#34d399':'#fb7185'}">${dQty>0?'+':''}${dQty}</td>
                                <td>${ad.warehouse}</td>
                                <td>إذن تسوية #${ad.id}</td>
                            </tr>`;
                    });
                });
                document.getElementById('stock-tbody').innerHTML = stockHtml || `<tr><td colspan="5" class="text-center py-5 text-slate-400 font-bold">لا توجد حركات أصناف أو مستودعات مسجلة في هذا التاريخ.</td></tr>`;

            } catch (err) {
                console.error(err);
            }
        }

        window.shareDailyReportWhatsApp = function() {
            const st = window._lastDailyStats || {};
            const msg = `📅 *التقرير اليومي الشامل — نظام السجانة ERP*\n` +
                `📆 *التاريخ:* ${st.targetDate || ''}\n` +
                `━━━━━━━━━━━━━━\n` +
                `🛒 *صافي المبيعات:* ${(st.totalSalesAmt || 0).toLocaleString()} SDG\n` +
                `🛍️ *صافي المشتريات:* ${(st.totalPurchasesAmt || 0).toLocaleString()} SDG\n` +
                `💵 *إجمالي المقبوضات:* ${(st.totalReceiptsAmt || 0).toLocaleString()} SDG\n` +
                `💸 *إجمالي المدفوعات:* ${(st.totalPaymentsAmt || 0).toLocaleString()} SDG\n` +
                `⚖️ *صافي التدفق المالي:* ${(st.netFlow || 0).toLocaleString()} SDG\n` +
                `📦 *حركات المستودعات:* ${st.stockMoveCount || 0} حركة | 📝 القيود: ${st.jvCount || 0}`;
            window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
        };

        window.openDailyAIAuditModal = function() {
            if (window.ElsajanaAccountingBrain && typeof window.ElsajanaAccountingBrain.openPurchaseAiAnalystModal === 'function') {
                window.ElsajanaAccountingBrain.openPurchaseAiAnalystModal();
                return;
            }
            if (window.DailyMasterSupremeEngine && typeof window.DailyMasterSupremeEngine.openAiExecutiveResultInterpreterModal === 'function') {
                window.DailyMasterSupremeEngine.openAiExecutiveResultInterpreterModal();
            }
        };
