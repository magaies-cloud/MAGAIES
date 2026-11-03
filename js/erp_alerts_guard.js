/**
 * ====================================================================
 * Al-Sajana Smart ERP 2026 - Universal Live Alert Guard Engine Titanium V8
 * حارس الرقابة الذكية والتنبيهات المباشرة ومنع التلاعب لجميع الشاشات
 * ====================================================================
 */
(function() {
    'use strict';

    window.ERPAlertsGuard = {
        rules: {},
        isLoaded: false,
        printedInvoices: new Set(),
        reprintCounters: {},
        deletedLineCount: 0,
        lastInvoiceTimestamp: 0,
        lastInvoiceCustomer: null,

        init: function() {
            this.loadRules();
            this.setupDOMObserver();
            this.injectCSS();
        },

        loadRules: function() {
            const self = this;
            try {
                const cached = localStorage.getItem('erp_alert_rules_cache_v8');
                if (cached) {
                    const parsed = JSON.parse(cached);
                    parsed.forEach(r => { self.rules[r.rule_key] = r; });
                    self.isLoaded = true;
                }
            } catch(e) {}

            fetch('alarm.php?action=get_rules_json')
            .then(r => r.json())
            .then(data => {
                if (data.success && Array.isArray(data.rules)) {
                    data.rules.forEach(r => {
                        self.rules[r.rule_key] = r;
                    });
                    localStorage.setItem('erp_alert_rules_cache_v8', JSON.stringify(data.rules));
                    self.isLoaded = true;
                }
            })
            .catch(() => {});
        },

        isRuleEnabled: function(ruleKey) {
            if (!this.rules[ruleKey]) return false;
            const r = this.rules[ruleKey];
            return (r.is_enabled == 1 || r.is_enabled === true || r.is_enabled === '1');
        },

        getRule: function(ruleKey) {
            return this.rules[ruleKey] || null;
        },

        showAlertBanner: function(msg, severity = 'warning', duration = 6000) {
            let container = document.getElementById('erp-alerts-guard-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'erp-alerts-guard-container';
                container.style.cssText = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:999999; display:flex; flex-direction:column; gap:10px; width:90%; max-width:560px; pointer-events:none; font-family:"Cairo","Tajawal",sans-serif;';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'erp-alert-toast ' + (severity === 'danger' ? 'danger' : 'warning');
            toast.style.pointerEvents = 'auto';
            
            const isDanger = severity === 'danger';
            const bg = isDanger ? 'linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(153, 27, 27, 0.98))' : 'linear-gradient(135deg, rgba(245, 158, 11, 0.95), rgba(180, 83, 9, 0.98))';
            const border = isDanger ? '#ef4444' : '#f59e0b';
            const icon = isDanger ? '🚨' : '⚠️';

            toast.style.cssText = 'background:' + bg + '; border:2px solid ' + border + '; color:#ffffff; padding:13px 18px; border-radius:18px; box-shadow:0 15px 35px rgba(0,0,0,0.5), 0 0 25px ' + border + '50; display:flex; align-items:center; justify-content:space-between; gap:12px; font-weight:800; font-size:0.92rem; line-height:1.5; animation:slideDownToast 0.3s cubic-bezier(0.16, 1, 0.3, 1); backdrop-filter:blur(15px);';

            toast.innerHTML = '<div style="display:flex; align-items:center; gap:10px;"><span style="font-size:1.5rem;">' + icon + '</span><span>' + msg + '</span></div><button type="button" style="background:rgba(255,255,255,0.25); border:none; color:#fff; border-radius:8px; padding:4px 9px; font-weight:900; font-size:0.8rem; cursor:pointer;" onclick="this.parentElement.remove()">✕</button>';

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        },

        injectCSS: function() {
            const style = document.createElement('style');
            style.textContent = '@keyframes slideDownToast { from { opacity: 0; transform: translateY(-30px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } } .erp-alert-highlight-danger { border-color: #ef4444 !important; box-shadow: 0 0 15px rgba(239, 68, 68, 0.6) !important; } .erp-alert-highlight-warning { border-color: #f59e0b !important; box-shadow: 0 0 15px rgba(245, 158, 11, 0.6) !important; }';
            document.head.appendChild(style);
        },

        setupDOMObserver: function() {
            const self = this;
            const page = window.location.pathname.split('/').pop().toLowerCase();

            // 1. Sales & POS Screens Listeners
            if (page.includes('sales') || page.includes('invoice') || page.includes('pos')) {
                
                // Input Listener (Prices, Quantities, Discounts, AML Big Cash)
                document.addEventListener('input', function(e) {
                    const target = e.target;
                    if (!target) return;

                    // 1. Price checks (Below Cost / Price Override / Wholesale)
                    if (target.classList.contains('item-price-input') || target.id === 'item-price' || target.name === 'price' || (target.placeholder && target.placeholder.includes('سعر'))) {
                        self.checkSalesPrice(target);
                    }

                    // 2. Quantity checks (Exceeds stock / Negative stock)
                    if (target.classList.contains('item-qty-input') || target.id === 'item-qty' || target.name === 'qty' || (target.placeholder && target.placeholder.includes('كمية'))) {
                        self.checkSalesQty(target);
                    }

                    // 3. Discount limit check
                    if (target.id === 'inv-discount' || target.classList.contains('item-discount-input') || target.name === 'discount') {
                        self.checkSalesDiscount(target);
                    }

                    // 4. AML Large Cash Limit check
                    if (target.id === 'paid-amount' || target.id === 'cash-amount' || target.name === 'paid' || target.name === 'cash_received') {
                        self.checkAMLCashLimit(target);
                    }
                }, true);

                // Change Listener (Customer Selection -> Credit limit / Blocked / NSF / Overdue)
                document.addEventListener('change', function(e) {
                    const target = e.target;
                    if (!target) return;

                    if (target.id === 'customer-select' || target.id === 'cust-id' || target.name === 'customer_id' || target.classList.contains('customer-select')) {
                        self.checkCustomerCreditAndStatus(target);
                    }
                }, true);

                // Click Listeners (Print, Void, Delete, Drawer Kick, Delete Lines, Save Invoice)
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('button, a');
                    if (!btn) return;

                    // 1. Track Drawer Kick without sale (No-Sale Drawer)
                    if (btn.id === 'btn-open-drawer' || btn.classList.contains('btn-drawer') || (btn.textContent && btn.textContent.includes('فتح الدرج'))) {
                        if (self.isRuleEnabled('sales_no_sale_drawer_open')) {
                            self.showAlertBanner('🚨 إنذار كاشير: تم فتح درج النقدية يدوياً بدون تسجيل فاتورة بيع!', 'warning');
                        }
                    }

                    // 2. Track Line Voids / Deletions
                    if (btn.classList.contains('btn-delete-row') || btn.classList.contains('delete-item-btn') || btn.classList.contains('fa-trash') || (btn.textContent && btn.textContent.includes('حذف البند'))) {
                        self.deletedLineCount++;
                        if (self.isRuleEnabled('sales_high_line_void_ratio') && self.deletedLineCount >= 3) {
                            self.showAlertBanner('⚠️ تنبيه تدقيق: تكرار حذف أصناف من الفاتورة (' + self.deletedLineCount + ' بنود محذوفة)!', 'warning');
                        }
                    }

                    // 3. Track Duplicate Reprint
                    if (btn.classList.contains('btn-print') || btn.id === 'btn-print' || (btn.textContent && btn.textContent.includes('طباعة'))) {
                        const invNo = self.getCurrentInvoiceNumber();
                        if (invNo) {
                            self.printedInvoices.add(invNo);
                            self.reprintCounters[invNo] = (self.reprintCounters[invNo] || 0) + 1;
                            if (self.reprintCounters[invNo] > 1 && self.isRuleEnabled('audit_reprint_invoice_repetition')) {
                                self.showAlertBanner('⚠️ تنبيه تدقيق: تم تكرار طباعة الفاتورة (' + invNo + ') للمرة (' + self.reprintCounters[invNo] + ')!', 'warning');
                            }
                        }
                    }

                    // 4. Track Void/Delete after Print
                    if (btn.classList.contains('btn-void') || btn.classList.contains('btn-delete-inv') || (btn.textContent && (btn.textContent.includes('إلغاء الفاتورة') || btn.textContent.includes('حذف الفاتورة')))) {
                        const invNo = self.getCurrentInvoiceNumber();
                        if (invNo && self.printedInvoices.has(invNo) && self.isRuleEnabled('audit_void_after_print')) {
                            self.showAlertBanner('🚨 إنذار احتيال حرج: محاولة إلغاء/حذف الفاتورة (' + invNo + ') بعد طباعتها للعميل!', 'danger');
                        }
                    }

                    // 5. Block deletion of delivered linked documents
                    if (btn.classList.contains('btn-delete-invoice') || btn.classList.contains('delete-btn')) {
                        const row = btn.closest('tr') || btn.closest('.invoice-row');
                        if (row && (row.textContent.includes('تم التسليم') || row.textContent.includes('مكتمل') || row.getAttribute('data-delivered') === '1')) {
                            if (self.isRuleEnabled('audit_linked_delivered_doc_block')) {
                                e.preventDefault();
                                e.stopPropagation();
                                self.showAlertBanner('🚫 حظر أمني صارم: لا يمكن حذف هذه الفاتورة نظراً لتسليم بضاعتها مخزنياً! يرجى عمل مرتجع رسمي.', 'danger', 8000);
                                return false;
                            }
                        }
                    }

                    // 6. Save Invoice Validations (Missing Rep / Rapid Duplicate)
                    if (btn.id === 'save-invoice-btn' || btn.classList.contains('btn-save-invoice') || (btn.textContent && btn.textContent.includes('حفظ الفاتورة'))) {
                        self.validateBeforeInvoiceSave(e);
                    }
                }, true);
            }

            // 2. Voucher & Payment screens listeners
            if (page.includes('cash-payment') || page.includes('cash-receipt') || page.includes('vouchers')) {
                document.addEventListener('change', function(e) {
                    const target = e.target;
                    if (!target) return;

                    // Check duplicate transfer number
                    if (target.id === 'block-transfer-no-input' || target.id === 'transfer-no-input' || target.classList.contains('block-transfer-no-input')) {
                        self.checkDuplicateTransfer(target);
                    }

                    // Check duplicate cheque number
                    if (target.id === 'block-cheque-no-input' || target.id === 'cheque-no-input' || target.classList.contains('block-cheque-no-input')) {
                        self.checkDuplicateCheque(target);
                    }
                }, true);
            }

            // 3. Master Data screens listeners
            if (page.includes('master-data') || page.includes('items')) {
                document.addEventListener('input', function(e) {
                    const target = e.target;
                    if (!target) return;

                    if (target.id === 'selling-price' || target.id === 'cost-price' || target.id === 'item-selling-price' || target.id === 'item-cost-price') {
                        self.checkMasterItemPrice();
                    }
                }, true);
            }

            // 4. Purchases & GRN screens listeners
            if (page.includes('purchase') || page.includes('smart-logistics')) {
                document.addEventListener('change', function(e) {
                    const target = e.target;
                    if (!target) return;
                    if (target.classList.contains('pur-price-input') || target.name === 'purchase_price' || (target.id && target.id.includes('cost'))) {
                        const row = target.closest('tr') || target.parentElement;
                        const prevCostEl = row ? row.querySelector('[data-prev-cost]') : null;
                        const prevCost = prevCostEl ? parseFloat(prevCostEl.getAttribute('data-prev-cost')) : 0;
                        const newCost = parseFloat(target.value) || 0;
                        if (prevCost > 0 && newCost > prevCost * 1.25) {
                            self.showAlertBanner('⚠️ تنبيه مشتريات: سعر الشراء المدخل (' + newCost.toLocaleString() + ') يرتفع بأكثر من 25% عن آخر تكلفة معتمدة (' + prevCost.toLocaleString() + ')!', 'warning');
                        }
                    }
                }, true);
            }

            // 5. Inventory Stocktake & Adjustment listeners
            if (page.includes('inventory') || page.includes('stocktake') || page.includes('warehouse')) {
                document.addEventListener('change', function(e) {
                    const target = e.target;
                    if (!target) return;
                    if (target.classList.contains('actual-qty-input') || (target.id && target.id.includes('actual'))) {
                        const val = parseFloat(target.value);
                        if (!isNaN(val) && val < 0) {
                            target.classList.add('erp-alert-highlight-danger');
                            self.showAlertBanner('🚨 تنبيه جرد مخزني: لا يمكن إدخال كمية جرد فعلي سالبة (' + val + ')!', 'danger');
                        }
                    }
                }, true);
            }

            // 6. Journal Voucher & Accounting Balance Guard
            if (page.includes('journal') || page.includes('posting')) {
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('button');
                    if (!btn) return;
                    if (btn.textContent && (btn.textContent.includes('حفظ القيد') || btn.textContent.includes('ترحيل'))) {
                        const debEl = document.getElementById('total-debit') || document.getElementById('jv-total-debit');
                        const credEl = document.getElementById('total-credit') || document.getElementById('jv-total-credit');
                        if (debEl && credEl) {
                            const d = parseFloat(String(debEl.textContent || debEl.value || '0').replace(/,/g, '')) || 0;
                            const c = parseFloat(String(credEl.textContent || credEl.value || '0').replace(/,/g, '')) || 0;
                            if (Math.abs(d - c) > 0.05) {
                                self.showAlertBanner('🚨 حارس التوازن المحاسبي: القيد غير متوازن! إجمالي المدين (' + d.toLocaleString() + ') لا يطابق الدائن (' + c.toLocaleString() + ').', 'danger');
                            }
                        }
                    }
                }, true);
            }

            // 7. Universal Keyboard Shortcut (Alt+A) & Sidebar Guardian Live Badge across ALL screens
            document.addEventListener('keydown', function(e) {
                if (e.altKey && (e.key === 'a' || e.key === 'A' || e.key === 'ش')) {
                    e.preventDefault();
                    self.openQuickGuardianModal();
                }
            });
            setTimeout(() => self.syncGlobalSidebarAlertBadge(), 350);
            window.addEventListener('storage', () => self.syncGlobalSidebarAlertBadge());
        },

        computeUniversalLiveRisks: function() {
            const risks = [];
            try {
                const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]').filter(i => i && !i.deleted);
                let negStock = 0, zeroStock = 0, belowCost = 0;
                items.forEach(it => {
                    let q = 0;
                    if (typeof it.stock === 'object' && it.stock !== null) {
                        q = Object.values(it.stock).reduce((a, b) => a + (parseFloat(b) || 0), 0);
                    } else {
                        q = parseFloat(it.stock ?? it.quantity ?? it.stockQuantity ?? it.openingQty ?? 0) || 0;
                    }
                    const cost = parseFloat(it.costPrice ?? it.avgCost ?? it.purchasePrice ?? it.cost ?? 0) || 0;
                    const price = parseFloat(it.sellingPrice ?? it.price ?? 0) || 0;
                    if (q < 0) negStock++;
                    else if (q === 0) zeroStock++;
                    if (cost > 0 && price > 0 && price <= cost) belowCost++;
                });
                if (negStock > 0) risks.push({ sev: 'RED', msg: `🚨 يوجد (${negStock}) صنف برصيد مخزني سالب يتطلب تسوية جردية`, href: 'inventory-stocktake.php' });
                if (belowCost > 0) risks.push({ sev: 'RED', msg: `🚨 يوجد (${belowCost}) صنف سعر بيعه أقل أو يساوي التكلفة`, href: 'master-data.php?tab=items' });
                if (zeroStock > 0) risks.push({ sev: 'YELLOW', msg: `⚠️ يوجد (${zeroStock}) صنف نافد (رصيد 0) في المخازن`, href: 'inventory-report.php' });

                const p1 = JSON.parse(localStorage.getItem('erp_purchases_v1') || '[]');
                const p2 = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
                const unrec = [...p1, ...p2].filter(p => p && !p.deleted && p.status === 'PENDING_RECEIPT');
                if (unrec.length > 0) risks.push({ sev: 'YELLOW', msg: `🚛 يوجد (${unrec.length}) فاتورة مشتريات بانتظار الاستلام المخزني (GRN)`, href: 'smart-logistics.php?mode=purchases' });

                const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(c => c && !c.deleted);
                let overLim = 0;
                custs.forEach(c => {
                    const b = parseFloat(c.currentBalance ?? c.balance ?? 0) || 0;
                    const l = parseFloat(c.creditLimit ?? 0) || 0;
                    if (l > 0 && b > l) overLim++;
                });
                if (overLim > 0) risks.push({ sev: 'RED', msg: `🚨 يوجد (${overLim}) عميل تجاوزوا السقف الائتماني المسموح`, href: 'credit_limits_dashboard.php' });

                const chks = JSON.parse(localStorage.getItem('erp_checks_v1') || '[]').filter(c => c && !c.deleted);
                const today = new Date().toISOString().slice(0, 10);
                let bounced = 0, overdue = 0;
                chks.forEach(c => {
                    const st = String(c.status || '');
                    const due = String(c.dueDate || '').slice(0, 10);
                    if (st.includes('مرتد')) bounced++;
                    else if ((st.includes('خزينة') || st.includes('تحصيل')) && due && due < today) overdue++;
                });
                if (bounced > 0) risks.push({ sev: 'RED', msg: `🚨 يوجد (${bounced}) شيك مرتد في حافظة الشيكات`, href: 'checks-cycle.php' });
                if (overdue > 0) risks.push({ sev: 'YELLOW', msg: `⏰ يوجد (${overdue}) شيك متأخر تجاوز تاريخ الاستحقاق`, href: 'checks-cycle.php' });
            } catch (e) {}
            return risks;
        },

        syncGlobalSidebarAlertBadge: function() {
            try {
                const risks = this.computeUniversalLiveRisks();
                const total = risks.length;
                const red = risks.filter(r => r.sev === 'RED').length;
                const alarmLinks = document.querySelectorAll('a[href*="alarm.php"], [data-href*="alarm.php"]');
                alarmLinks.forEach(a => {
                    let badge = a.querySelector('.erp-guard-live-badge');
                    if (total > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'erp-guard-live-badge';
                            badge.style.cssText = 'margin-right:auto; background:' + (red > 0 ? '#ef4444' : '#f59e0b') + '; color:#fff; font-size:0.68rem; font-weight:900; padding:1px 6px; border-radius:999px; box-shadow:0 0 10px rgba(239,68,68,0.6);';
                            a.appendChild(badge);
                        }
                        badge.textContent = total;
                    } else if (badge) {
                        badge.remove();
                    }
                });
            } catch (e) {}
        },

        openQuickGuardianModal: function() {
            const risks = this.computeUniversalLiveRisks();
            let modal = document.getElementById('erp-universal-guardian-modal');
            if (!modal) {
                document.body.insertAdjacentHTML('beforeend', `
                    <div id="erp-universal-guardian-modal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(2,6,23,0.85); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; direction:rtl; font-family:'Cairo','Tajawal',sans-serif;">
                        <div style="width:100%; max-width:680px; background:#0f172a; border:2px solid #f43f5e; border-radius:18px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.85);">
                            <div style="padding:14px 18px; background:linear-gradient(135deg,rgba(244,63,94,0.22),rgba(15,23,42,0.9)); border-bottom:1px solid rgba(244,63,94,0.4); display:flex; justify-content:space-between; align-items:center;">
                                <h3 style="margin:0; font-size:1rem; font-weight:900; color:#fda4af;">🛡️ رادار الحارس الأمين الفوري (مربوط بكافة الشاشات والتقارير)</h3>
                                <button type="button" onclick="document.getElementById('erp-universal-guardian-modal').style.display='none'" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:4px 10px; font-weight:800; cursor:pointer;">✖</button>
                            </div>
                            <div id="erp-universal-guardian-body" style="padding:16px 18px; max-height:65vh; overflow-y:auto; display:flex; flex-direction:column; gap:10px;"></div>
                            <div style="padding:12px 18px; background:rgba(15,23,42,0.95); border-top:1px solid rgba(148,163,184,0.2); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                <span style="font-size:0.75rem; color:#94a3b8; font-weight:700;">💡 اختصار الفتح السريع من أي شاشة: <kbd style="background:#1e293b; padding:2px 6px; border-radius:4px; color:#38bdf8;">Alt + A</kbd></span>
                                <div style="display:flex; gap:8px;">
                                    <a href="alarm.php?pillar=AI" style="padding:6px 12px; border-radius:8px; background:linear-gradient(135deg,#8b5cf6,#6d28d9); color:#fff; text-decoration:none; font-weight:800; font-size:0.78rem;">🤖 مركز التنبؤ بالمخاطر AI</a>
                                    <a href="alarm.php" style="padding:6px 12px; border-radius:8px; background:#f43f5e; color:#fff; text-decoration:none; font-weight:800; font-size:0.78rem;">🔔 فتح مصفوفة الـ 88 قاعدة</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                modal = document.getElementById('erp-universal-guardian-modal');
            }
            const body = document.getElementById('erp-universal-guardian-body');
            if (risks.length === 0) {
                body.innerHTML = `<div style="padding:20px; text-align:center; color:#34d399; font-weight:800;">✅ جميع الشاشات والأرصدة والمخازن والشيكات آمنة ومطابقة 100% حالياً.</div>`;
            } else {
                body.innerHTML = risks.map(r => `
                    <div style="padding:10px 14px; border-radius:12px; background:${r.sev === 'RED' ? 'rgba(244,63,94,0.14)' : 'rgba(245,158,11,0.14)'}; border:1px solid ${r.sev === 'RED' ? 'rgba(244,63,94,0.4)' : 'rgba(245,158,11,0.4)'}; display:flex; justify-content:space-between; align-items:center; gap:10px;">
                        <span style="color:#f8fafc; font-size:0.84rem; font-weight:800;">${r.msg}</span>
                        <a href="${r.href}" style="padding:5px 10px; border-radius:8px; background:rgba(15,23,42,0.9); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); text-decoration:none; font-size:0.75rem; font-weight:800; white-space:nowrap;">معالجة ↗</a>
                    </div>
                `).join('');
            }
            modal.style.display = 'flex';
        },

        getCurrentInvoiceNumber: function() {
            const el = document.getElementById('inv-number') || document.querySelector('.invoice-no, #invoice-no');
            return el ? (el.value || el.textContent || '').trim() : 'INV-CURRENT';
        },

        checkMasterItemPrice: function() {
            if (!this.isRuleEnabled('master_selling_below_cost')) return;
            const costEl = document.getElementById('cost-price') || document.querySelector('[name="costPrice"]');
            const sellEl = document.getElementById('selling-price') || document.querySelector('[name="sellingPrice"]');
            if (costEl && sellEl) {
                const cost = parseFloat(costEl.value) || 0;
                const sell = parseFloat(sellEl.value) || 0;
                if (cost > 0 && sell > 0 && sell < cost) {
                    sellEl.classList.add('erp-alert-highlight-warning');
                    this.showAlertBanner('⚠️ تنبيه تسعير: سعر البيع المسجل أقل من سعر التكلفة المعتمد!', 'warning');
                } else {
                    sellEl.classList.remove('erp-alert-highlight-warning');
                }
            }
        },

        checkSalesPrice: function(inputEl) {
            const row = inputEl.closest('tr') || inputEl.closest('.invoice-item-row') || inputEl.parentElement;
            if (!row) return;

            const costEl = row.querySelector('.item-cost, [data-cost], .hidden-cost');
            const cost = costEl ? parseFloat(costEl.getAttribute('data-cost') || costEl.value || costEl.textContent) : 0;
            const enteredPrice = parseFloat(inputEl.value) || 0;

            // 1. Below cost
            if (cost > 0 && enteredPrice > 0 && enteredPrice < cost && this.isRuleEnabled('sales_below_cost')) {
                inputEl.classList.add('erp-alert-highlight-danger');
                this.showAlertBanner('⚠️ سعر البيع المدخل (' + enteredPrice + ') أقل من سعر التكلفة/الشراء (' + cost + ')!', 'danger');
            } else {
                inputEl.classList.remove('erp-alert-highlight-danger');
            }

            // 2. Below wholesale
            if (this.isRuleEnabled('sales_below_wholesale')) {
                const qtyEl = row.querySelector('.item-qty-input, [name="qty"]');
                const qty = qtyEl ? (parseFloat(qtyEl.value) || 0) : 0;
                const wholesaleThreshold = 50;
                if (qty >= wholesaleThreshold && enteredPrice > 0 && cost > 0 && (enteredPrice - cost) < (cost * 0.02)) {
                    this.showAlertBanner('⚠️ تنبيه جملة: السعر المدخل يقل عن هامش الجملة المعتمد للكميات الكبيرة!', 'warning');
                }
            }
        },

        checkSalesQty: function(inputEl) {
            const row = inputEl.closest('tr') || inputEl.closest('.invoice-item-row') || inputEl.parentElement;
            if (!row) return;

            const stockEl = row.querySelector('.item-stock, [data-stock], .stock-display');
            const stock = stockEl ? parseFloat(stockEl.getAttribute('data-stock') || stockEl.value || stockEl.textContent) : null;
            const enteredQty = parseFloat(inputEl.value) || 0;

            if (stock !== null) {
                if (stock <= 0 && enteredQty > 0 && this.isRuleEnabled('sales_negative_stock')) {
                    inputEl.classList.add('erp-alert-highlight-danger');
                    this.showAlertBanner('🚨 رصيد هذا الصنف صفر أو سالب بالمستودع! لا يمكن إتمام البيع.', 'danger');
                } else if (enteredQty > stock && this.isRuleEnabled('sales_qty_exceeds_stock')) {
                    inputEl.classList.add('erp-alert-highlight-warning');
                    this.showAlertBanner('⚠️ الكمية المطلوبة (' + enteredQty + ') تتجاوز الرصيد المتوفر بالمستودع (' + stock + ')!', 'warning');
                } else {
                    inputEl.classList.remove('erp-alert-highlight-danger', 'erp-alert-highlight-warning');
                }
            }
        },

        checkSalesDiscount: function(inputEl) {
            if (!this.isRuleEnabled('sales_discount_limit_breach')) return;

            const rule = this.getRule('sales_discount_limit_breach');
            const maxAllowed = rule ? parseFloat(rule.warning_threshold) : 5;
            const enteredDiscount = parseFloat(inputEl.value) || 0;

            if (enteredDiscount > maxAllowed) {
                inputEl.classList.add('erp-alert-highlight-warning');
                this.showAlertBanner('⚠️ نسبة الخصم (' + enteredDiscount + '%) تتجاوز الحد الأقصى المسموح (' + maxAllowed + '%)!', 'warning');
            } else {
                inputEl.classList.remove('erp-alert-highlight-warning');
            }
        },

        checkAMLCashLimit: function(inputEl) {
            if (!this.isRuleEnabled('sales_aml_large_cash_limit')) return;
            const rule = this.getRule('sales_aml_large_cash_limit');
            const limit = rule ? parseFloat(rule.warning_threshold) : 1000000;
            const amt = parseFloat(inputEl.value) || 0;

            if (amt >= limit) {
                inputEl.classList.add('erp-alert-highlight-warning');
                this.showAlertBanner('⚠️ تنبيه امتثال مالي: المبلغ النقدي المستلم كبير جداً (' + amt.toLocaleString() + ') ويتطلب تحصيلاً بنكياً!', 'warning');
            } else {
                inputEl.classList.remove('erp-alert-highlight-warning');
            }
        },

        checkCustomerCreditAndStatus: function(selectEl) {
            const opt = selectEl.options ? selectEl.options[selectEl.selectedIndex] : null;
            if (!opt) return;

            const isBlocked = opt.getAttribute('data-blocked') === '1' || (opt.textContent && opt.textContent.includes('محظور'));
            if (isBlocked && this.isRuleEnabled('sales_blocked_customer')) {
                selectEl.classList.add('erp-alert-highlight-danger');
                this.showAlertBanner('🚫 حظر مبيعات: هذا العميل موقوف ومحظور التعامل معه بموجب قرار الإدارة المالية!', 'danger', 8000);
            }

            const hasNSF = opt.getAttribute('data-nsf') === '1' || (opt.textContent && opt.textContent.includes('شيك مرتد'));
            if (hasNSF && this.isRuleEnabled('credit_customer_nsf_history')) {
                this.showAlertBanner('🚨 تحذير مخاطر شيكات: هذا العميل لديه سوابق شيكات بدون رصيد!', 'danger');
            }

            const bal = parseFloat(opt.getAttribute('data-balance') || 0);
            const limit = parseFloat(opt.getAttribute('data-limit') || 0);
            if (limit > 0 && bal >= limit && this.isRuleEnabled('sales_credit_limit_exceeded')) {
                this.showAlertBanner('🚨 تنبيه ائتمان: العميل تجاوز السقف الائتماني المسموح به (' + limit.toLocaleString() + ')!', 'danger');
            }
        },

        validateBeforeInvoiceSave: function(e) {
            // Check missing sales rep
            if (this.isRuleEnabled('sales_missing_rep')) {
                const repSelect = document.getElementById('rep-select') || document.getElementById('sales-rep') || document.querySelector('[name="sales_rep_id"]');
                if (repSelect && (!repSelect.value || repSelect.value === '0' || repSelect.value === '')) {
                    this.showAlertBanner('⚠️ تنبيه إداري: يرجى تحديد مندوب المبيعات المسؤول عن الفاتورة!', 'warning');
                }
            }

            // Check rapid duplicate invoice
            if (this.isRuleEnabled('sales_rapid_duplicate_invoice')) {
                const now = Date.now();
                const cust = document.getElementById('customer-select') ? document.getElementById('customer-select').value : '1';
                if (this.lastInvoiceCustomer === cust && (now - this.lastInvoiceTimestamp) < 60000) {
                    this.showAlertBanner('⚠️ تنبيه تكرار سريع: تم إصدار فاتورة سابقة لنفس العميل خلال أقل من دقيقة!', 'warning');
                }
                this.lastInvoiceTimestamp = now;
                this.lastInvoiceCustomer = cust;
            }
        },

        checkDuplicateTransfer: function(inputEl) {
            if (!this.isRuleEnabled('fin_duplicate_transfer_no')) return;
            const val = inputEl.value.trim();
            if (!val || val === '0') return;

            const allTransfers = document.querySelectorAll('.block-transfer-no-input, #transfer-no-input, #block-transfer-no-input');
            let count = 0;
            allTransfers.forEach(el => {
                if (el.value.trim() === val) count++;
            });

            if (count > 1) {
                inputEl.classList.add('erp-alert-highlight-danger');
                this.showAlertBanner('🚨 تكرار رقم التحويلة البنكية (' + val + ') في أكثر من بند بالسند!', 'danger');
            } else {
                inputEl.classList.remove('erp-alert-highlight-danger');
            }
        },

        checkDuplicateCheque: function(inputEl) {
            if (!this.isRuleEnabled('fin_duplicate_cheque_no')) return;
            const val = inputEl.value.trim();
            if (!val || val === '0') return;

            const allCheques = document.querySelectorAll('.block-cheque-no-input, #cheque-no-input, #block-cheque-no-input');
            let count = 0;
            allCheques.forEach(el => {
                if (el.value.trim() === val) count++;
            });

            if (count > 1) {
                inputEl.classList.add('erp-alert-highlight-danger');
                this.showAlertBanner('🚨 تكرار رقم الشيك البنكي (' + val + ') في المعاملات المالية!', 'danger');
            } else {
                inputEl.classList.remove('erp-alert-highlight-danger');
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.ERPAlertsGuard.init());
    } else {
        window.ERPAlertsGuard.init();
    }
})();
