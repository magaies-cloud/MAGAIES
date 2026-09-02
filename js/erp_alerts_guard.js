/**
 * ====================================================================
 * Al-Sajana Smart ERP 2026 - Universal Live Alert Guard Engine
 * حارس الرقابة والتنبيهات المباشر لجميع شاشات النظام
 * ====================================================================
 */
(function() {
    'use strict';

    window.ERPAlertsGuard = {
        rules: {},
        isLoaded: false,

        init: function() {
            this.loadRules();
            this.setupDOMObserver();
            this.injectCSS();
        },

        loadRules: function() {
            const self = this;
            try {
                const cached = localStorage.getItem('erp_alert_rules_cache');
                if (cached) {
                    const parsed = JSON.parse(cached);
                    parsed.forEach(r => { self.rules[r.rule_key] = r; });
                    self.isLoaded = true;
                }
            } catch(e) {}

            fetch('alerts_manager.php?action=get_rules_json')
            .then(r => r.json())
            .then(data => {
                if (data.success && Array.isArray(data.rules)) {
                    data.rules.forEach(r => {
                        self.rules[r.rule_key] = r;
                    });
                    localStorage.setItem('erp_alert_rules_cache', JSON.stringify(data.rules));
                    self.isLoaded = true;
                }
            })
            .catch(() => {});
        },

        isRuleEnabled: function(ruleKey) {
            return this.rules[ruleKey] && (this.rules[ruleKey].is_enabled == 1 || this.rules[ruleKey].is_enabled === true);
        },

        getRule: function(ruleKey) {
            return this.rules[ruleKey] || null;
        },

        showAlertBanner: function(msg, severity = 'warning', duration = 6000) {
            let container = document.getElementById('erp-alerts-guard-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'erp-alerts-guard-container';
                container.style.cssText = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:999999; display:flex; flex-direction:column; gap:10px; width:90%; max-width:550px; pointer-events:none; font-family:"Cairo","Tajawal",sans-serif;';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'erp-alert-toast ' + (severity === 'danger' ? 'danger' : 'warning');
            toast.style.pointerEvents = 'auto';
            
            const isDanger = severity === 'danger';
            const bg = isDanger ? 'linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(153, 27, 27, 0.98))' : 'linear-gradient(135deg, rgba(245, 158, 11, 0.95), rgba(180, 83, 9, 0.98))';
            const border = isDanger ? '#ef4444' : '#f59e0b';
            const icon = isDanger ? '🚨' : '⚠️';

            toast.style.cssText = `background:${bg}; border:2px solid ${border}; color:#ffffff; padding:12px 18px; border-radius:16px; box-shadow:0 15px 35px rgba(0,0,0,0.5), 0 0 20px ${border}40; display:flex; align-items:center; justify-content:space-between; gap:12px; font-weight:800; font-size:0.9rem; line-height:1.5; animation:slideDownToast 0.3s cubic-bezier(0.16, 1, 0.3, 1);`;

            toast.innerHTML = `
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:1.4rem;">${icon}</span>
                    <span>${msg}</span>
                </div>
                <button type="button" style="background:rgba(255,255,255,0.2); border:none; color:#fff; border-radius:8px; padding:4px 8px; font-weight:900; font-size:0.8rem; cursor:pointer;" onclick="this.parentElement.remove()">✕</button>
            `;

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
            style.textContent = `
                @keyframes slideDownToast {
                    from { opacity: 0; transform: translateY(-30px) scale(0.95); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }
                .erp-alert-highlight-danger {
                    border-color: #ef4444 !important;
                    box-shadow: 0 0 15px rgba(239, 68, 68, 0.6) !important;
                }
                .erp-alert-highlight-warning {
                    border-color: #f59e0b !important;
                    box-shadow: 0 0 15px rgba(245, 158, 11, 0.6) !important;
                }
            `;
            document.head.appendChild(style);
        },

        setupDOMObserver: function() {
            const self = this;
            const page = window.location.pathname.split('/').pop().toLowerCase();

            // 1. Sales screens listeners
            if (page.includes('sales') || page.includes('invoice')) {
                document.addEventListener('input', function(e) {
                    const target = e.target;
                    if (!target) return;

                    // Price input check
                    if (target.classList.contains('item-price-input') || target.id === 'item-price' || target.name === 'price' || (target.placeholder && target.placeholder.includes('سعر'))) {
                        self.checkSalesPrice(target);
                    }

                    // Quantity input check
                    if (target.classList.contains('item-qty-input') || target.id === 'item-qty' || target.name === 'qty' || (target.placeholder && target.placeholder.includes('كمية'))) {
                        self.checkSalesQty(target);
                    }

                    // Discount input check
                    if (target.id === 'inv-discount' || target.classList.contains('item-discount-input') || target.name === 'discount') {
                        self.checkSalesDiscount(target);
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

                    // Check large cash amount
                    if (target.id === 'block-total-amount' || target.id === 'amount' || target.classList.contains('block-total-amount')) {
                        self.checkLargePayment(target);
                    }
                }, true);
            }
        },

        checkSalesPrice: function(input) {
            if (!this.isRuleEnabled('sales_below_cost')) return;
            const price = parseFloat(input.value) || 0;
            if (price <= 0) return;

            let cost = parseFloat(input.getAttribute('data-cost')) || 0;
            const row = input.closest('tr') || input.closest('.item-row') || input.closest('.grid-row');
            if (!cost && row) {
                const itemSelect = row.querySelector('select, [data-item-id]');
                const itemId = itemSelect ? (itemSelect.value || itemSelect.getAttribute('data-item-id')) : null;
                if (itemId && window._PRELOADED_ITEMS) {
                    const found = window._PRELOADED_ITEMS.find(it => String(it.id) === String(itemId));
                    if (found) cost = parseFloat(found.purchasePrice || found.costPrice || found.avgCost || 0);
                }
            }

            if (cost > 0 && price < cost) {
                input.classList.add('erp-alert-highlight-warning');
                this.showAlertBanner(`⚠️ تنبيه رقابي: سعر البيع (${price}) أقل من سعر التكلفة/الشراء (${cost}) لهذا الصنف!`, 'warning', 5000);
            } else {
                input.classList.remove('erp-alert-highlight-warning');
            }
        },

        checkSalesQty: function(input) {
            const qty = parseFloat(input.value) || 0;
            if (qty <= 0) return;

            let stock = parseFloat(input.getAttribute('data-stock'));
            const row = input.closest('tr') || input.closest('.item-row') || input.closest('.grid-row');
            if (isNaN(stock) && row) {
                const itemSelect = row.querySelector('select, [data-item-id]');
                const itemId = itemSelect ? (itemSelect.value || itemSelect.getAttribute('data-item-id')) : null;
                if (itemId && window._PRELOADED_ITEMS) {
                    const found = window._PRELOADED_ITEMS.find(it => String(it.id) === String(itemId));
                    if (found) stock = parseFloat(found.stockQuantity || found.quantity || found.qty || 0);
                }
            }

            if (!isNaN(stock)) {
                if (this.isRuleEnabled('sales_negative_stock') && stock <= 0) {
                    input.classList.add('erp-alert-highlight-danger');
                    this.showAlertBanner(`🚨 خطر مخزني: رصيد الصنف بالمستودع صفر أو سالب (${stock} وحدة)!`, 'danger', 5000);
                } else if (this.isRuleEnabled('sales_qty_exceeds_stock') && qty > stock) {
                    input.classList.add('erp-alert-highlight-warning');
                    this.showAlertBanner(`⚠️ تنبيه مخزني: الكمية المدخلة (${qty}) تتجاوز الرصيد المتوفر بالمستودع (${stock} وحدة)!`, 'warning', 5000);
                } else {
                    input.classList.remove('erp-alert-highlight-warning', 'erp-alert-highlight-danger');
                }
            }
        },

        checkSalesDiscount: function(input) {
            if (!this.isRuleEnabled('sales_discount_limit_breach')) return;
            const discount = parseFloat(input.value) || 0;
            const rule = this.getRule('sales_discount_limit_breach');
            const limit = rule ? parseFloat(rule.warning_threshold || 5) : 5;

            if (discount > limit) {
                this.showAlertBanner(`⚠️ تنبيه الخصم: نسبة الخصم المدخلة (${discount}%) تتجاوز الحد المسموح به (${limit}%)!`, 'warning', 4000);
            }
        },

        checkDuplicateTransfer: function(input) {
            if (!this.isRuleEnabled('fin_duplicate_transfer_no')) return;
            const val = (input.value || '').trim();
            if (!val || val === '0') return;

            const vouchers = JSON.parse(localStorage.getItem('erp_vouchers_v1') || '[]');
            let count = 0;
            vouchers.forEach(v => {
                if (v.mobileTransferNo === val) count++;
                if (v.transfers && v.transfers.some(t => t.transferNo === val)) count++;
            });

            if (count > 0) {
                input.classList.add('erp-alert-highlight-danger');
                this.showAlertBanner(`🚨 تحذير مالي: رقم التحويلة البنكية [${val}] مسجل مسبقاً في سند آخر لمنع الازدواجية!`, 'danger', 6000);
            } else {
                input.classList.remove('erp-alert-highlight-danger');
            }
        },

        checkDuplicateCheque: function(input) {
            if (!this.isRuleEnabled('fin_duplicate_cheque_no')) return;
            const val = (input.value || '').trim();
            if (!val || val === '0') return;

            const checks = JSON.parse(localStorage.getItem('erp_checks_v1') || '[]');
            let count = 0;
            checks.forEach(c => {
                if (c.checkNo === val || c.no === val) count++;
            });

            if (count > 0) {
                input.classList.add('erp-alert-highlight-danger');
                this.showAlertBanner(`🚨 تحذير مصرفي: رقم الشيك [${val}] تم استخدامه مسبقاً في معاملة أخرى!`, 'danger', 6000);
            } else {
                input.classList.remove('erp-alert-highlight-danger');
            }
        },

        checkLargePayment: function(input) {
            if (!this.isRuleEnabled('fin_large_cash_outflow')) return;
            const amt = parseFloat(input.value) || 0;
            const rule = this.getRule('fin_large_cash_outflow');
            const limit = rule ? parseFloat(rule.warning_threshold || 500000) : 500000;

            if (amt >= limit) {
                this.showAlertBanner(`ℹ️ إشعار تدقيق: مبلغ السند (${amt.toLocaleString()} SDG) كبير ويتطلب اعتماد المدير المالي.`, 'warning', 5000);
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.ERPAlertsGuard.init());
    } else {
        window.ERPAlertsGuard.init();
    }
})();