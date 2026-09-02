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

                    if (target.id === 'selling-price' || target.id === 'cost-price') {
                        self.checkMasterItemPrice();
                    }
                }, true);
            }
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
