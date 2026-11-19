        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                container.style.cssText = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none;';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            let bg = 'rgba(52,211,153,0.15)';
            let border = '1px solid rgba(52,211,153,0.4)';
            let color = '#34d399';
            if (type === 'error') {
                bg = 'rgba(239,68,68,0.15)';
                border = '1px solid rgba(239,68,68,0.4)';
                color = '#f87171';
            } else if (type === 'warning') {
                bg = 'rgba(251,191,36,0.15)';
                border = '1px solid rgba(251,191,36,0.4)';
                color = '#fbbf24';
            }
            toast.style.cssText = `padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); color:${color}; background:${bg}; border:${border}; font-family:'Tajawal',sans-serif; animation: toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both;`;
            toast.innerHTML = `<span>${type === 'error' ? '🔴' : (type === 'warning' ? '🟡' : '🟢')}</span> ${message}`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'toastOut 0.4s cubic-bezier(0.16,1,0.3,1) both';
                setTimeout(() => toast.remove(), 400);
            }, 5000);
        }

        function runAICFO() {
            document.getElementById('aiCFO-modal').style.display = 'flex';
            document.getElementById('aiCFO-content').innerHTML = 'جاري تحليل بيانات الشركة واستخراج التقرير... يرجى الانتظار ⏳';
            
            // Gather KPI Data from existing DOM elements
            let sales = document.getElementById('kpi-sales') ? document.getElementById('kpi-sales').innerText : 'غير متاح';
            let liquidity = document.getElementById('kpi-liquidity') ? document.getElementById('kpi-liquidity').innerText : 'غير متاح';
            let inventory = document.getElementById('kpi-inventory') ? document.getElementById('kpi-inventory').innerText : 'غير متاح';
            let alerts = document.getElementById('kpi-alerts') ? document.getElementById('kpi-alerts').innerText : 'غير متاح';
            
            let context = `مبيعات اليوم: ${sales} | السيولة المتوفرة: ${liquidity} | الأصناف في المخزون: ${inventory} | نواقص المخزون: ${alerts}`;

            // Try to extract some customer debt info safely
            try {
                let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                let debtSum = 0;
                custs.forEach(c => { if(c.currentBalance > 0) debtSum += c.currentBalance; });
                context += ` | إجمالي ديون العملاء علينا أو لنا: ${debtSum}`;
            } catch(e) {}

            let promptMsg = "أنت المدير المالي (CFO) للشركة. بناءً على هذه المؤشرات، اكتب تقريراً إدارياً قصيراً جداً (3 فقرات كحد أقصى) يشرح لصاحب العمل وضع شركته اليوم (الإيجابيات، السلبيات أو المخاطر إن وجدت، توصية سريعة).";

            fetch('api/ai-assistant.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: promptMsg, context: context })
            })
            .then(res => res.json())
            .then(data => {
                if (data.reply) {
                    document.getElementById('aiCFO-content').innerHTML = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong style="color:#38bdf8">$1</strong>').replace(/\n/g, '<br>');
                } else {
                    document.getElementById('aiCFO-content').innerHTML = '<span style="color:#f43f5e">حدث خطأ أثناء استخراج التقرير.</span>';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('aiCFO-content').innerHTML = '<span style="color:#f43f5e">تعذر الاتصال بخادم الذكاء الاصطناعي.</span>';
            });
        }
        window.addEventListener('themeChanged', (e) => {
            // تطبيق الثيم بدون إعادة تحميل الصفحة مع الحفاظ على السمة المختارة من السمات السبع
            const activeKey = (e && e.detail && e.detail.themeKey) || localStorage.getItem('magaies_active_theme');
            if (activeKey) {
                document.documentElement.setAttribute('data-theme', activeKey);
                if (document.body) document.body.setAttribute('data-theme', activeKey);
            }
        });

        // ═════════════════════════════════════════════════════════════════════
        //  CATEGORIZED DOMAIN HUBS ENGINE ("تجميع حسب الاختصاصات")
        // ═════════════════════════════════════════════════════════════════════
        const DOMAIN_CATEGORIES = [
            {
                id: 'cat_purchases',
                title: 'المشتريات والموردين',
                icon: 'fa-bag-shopping',
                color: '#fbbf24',
                bg: 'rgba(251, 191, 36, 0.2)',
                border: '#fbbf24',
                screens: [
                    { href: 'purchase.php', label: 'فواتير المشتريات', icon: 'fa-bag-shopping', color: '#fbbf24', border: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'smart-logistics.php?mode=purchases', label: 'بوابة استلام المشتريات الذكية', icon: 'fa-truck-Moving', color: '#22d3ee', border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.2)' },
                    { href: 'purchase-receipt.php', label: 'استلام شحنات المشتريات', icon: 'fa-box-open', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'purchase-return.php', label: 'مرتجعات المشتريات', icon: 'fa-rotate-left', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'supplier_reliability.php', label: 'اعتمادية وموثوقية الموردين 360°', icon: 'fa-handshake-angle', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                ]
            },
            {
                id: 'cat_sales',
                title: 'المبيعات والفواتير',
                icon: 'fa-cart-shopping',
                color: '#34d399',
                bg: 'rgba(16, 185, 129, 0.2)',
                border: '#10b981',
                screens: [
                    { href: 'direct-sales.php', label: 'المبيعات المباشرة ونقاط البيع', icon: 'fa-cart-shopping', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'sales-invoice.php', label: 'مرتجع المبيعات واسترداد البضائع', icon: 'fa-receipt', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'sales-delivery.php', label: 'بوابة تسليم المبيعات', icon: 'fa-truck-ramp-box', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'reserved-sales.php', label: 'حجوزات المبيعات', icon: 'fa-bookmark', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' }
                ]
            },
            {
                id: 'cat_inventory',
                title: 'المخزون والمستودعات',
                icon: 'fa-warehouse',
                color: '#a78bfa',
                bg: 'rgba(167, 139, 250, 0.2)',
                border: '#a78bfa',
                screens: [
                    { href: 'inventory-summary.php', label: 'ملخص حركة المخزون', icon: 'fa-boxes-stacked', color: '#a78bfa', border: '#a78bfa', bg: 'rgba(167, 139, 250, 0.2)' },
                    { href: 'warehouse-ledger.php', label: 'حركة المخازن والمستودعات', icon: 'fa-dolly', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'inventory-movement.php', label: 'حركات البضائع وأرصدة المستودعات', icon: 'fa-warehouse', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'inventory-adjustment.php?tab=transfer', label: 'التحويل والمناقلة بين المستودعات', icon: 'fa-arrows-left-right', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'item-ledger.php', label: 'دفتر حركة الصنف', icon: 'fa-barcode', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'commercial-reports-suite.php?rep=items_catalog', label: 'قائمة الأصناف الشاملة', icon: 'fa-list', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'commercial-reports-suite.php?rep=items_detailed_movement', label: 'الحركة التفصيلية للصنف', icon: 'fa-magnifying-glass-chart', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'commercial-reports-suite.php?rep=items_commercial_movement', label: 'الحركة التجارية للصنف', icon: 'fa-chart-simple', color: '#22d3ee', border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.2)' },
                    { href: 'commercial-reports-suite.php?rep=items_profitability', label: 'أرباح الأصناف وهوامشها', icon: 'fa-sack-dollar', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'commercial-reports-suite.php?rep=historical_expiry_inventory', label: 'جرد تاريخي للصلاحية', icon: 'fa-hourglass-half', color: '#fb7185', border: '#f43f5e', bg: 'rgba(244, 63, 94, 0.2)' },
                    { href: 'commercial-reports-suite.php?rep=linked_references_audit', label: 'حركة المراجع المرتبطة', icon: 'fa-link', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' }
                ]
            },
            {
                id: 'cat_treasury',
                title: 'المعاملات المالية والخزن والبنوك',
                icon: 'fa-vault',
                color: '#34d399',
                bg: 'rgba(52, 211, 153, 0.2)',
                border: '#34d399',
                screens: [
                    { href: 'single-cash-payment.php', label: 'حركة مالية (صرف وقبض مفرد)', icon: 'fa-money-bill-transfer', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'multi-cash-payment.php', label: 'حركة مالية مجمعة', icon: 'fa-money-bill-wave', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'journal-voucher.php', label: 'قيد يومية', icon: 'fa-file-signature', color: '#a855f7', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'posting-accounts.php', label: 'ترحيل الحسابات', icon: 'fa-arrows-rotate', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'checks-cycle.php', label: 'دورة إدارة الشيكات', icon: 'fa-money-check-dollar', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'vouchers-hub.php', label: 'بوابة المعاملات المالية الشاملة', icon: 'fa-vault', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' }
                ]
            },
            {
                id: 'cat_accounting',
                title: 'الحسابات والمالية',
                icon: 'fa-file-invoice-dollar',
                color: '#38bdf8',
                bg: 'rgba(56, 189, 248, 0.2)',
                border: '#38bdf8',
                screens: [
                    { href: 'general-ledger.php', label: 'دفتر الأستاذ العام (كشف عام)', icon: 'fa-book-bookmark', color: '#60a5fa', border: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' },
                    { href: 'journal.php', label: 'دفتر اليومية العامة', icon: 'fa-book', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'account-statement.php', label: 'كشف حساب تاريخي ومختصر', icon: 'fa-file-invoice-dollar', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'trial-balance.php', label: 'ميزان المراجعة', icon: 'fa-scale-balanced', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'pl-statement.php', label: 'قائمة الدخل والأرباح والخسائر', icon: 'fa-chart-line', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'balance-sheet.php', label: 'الميزانية العمومية (المركز المالي)', icon: 'fa-landmark', color: '#10b981', border: '#059669', bg: 'rgba(16, 185, 129, 0.25)' },
                    { href: 'balance-sheet.php?tab=cost_centers', label: 'مراكز الكلفة والدخل التشغيلي', icon: 'fa-bullseye', color: '#22d3ee', border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.2)' },
                    { href: 'distributive-reports.php', label: 'الدفاتر والقوائم التوزيعية ومولد التقارير', icon: 'fa-table-columns', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.22)' },
                    { href: 'commercial-reports-suite.php', label: 'الحركة التجارية وأعمار الفواتير والأقساط والصلاحية', icon: 'fa-boxes-stacked', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.22)' },
                    { href: 'aging-report.php', label: 'أعمار الديون وسقوف الائتمان', icon: 'fa-clock-rotate-left', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'financial-reports.php', label: 'بوابة القوائم والتقارير المحاسبية والجرد', icon: 'fa-chart-pie', color: '#f472b6', border: '#ec4899', bg: 'rgba(236, 72, 153, 0.2)' },
                    { href: 'posting-accounts.php', label: 'دليل وشجرة الحسابات', icon: 'fa-sitemap', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                ]
            },
            {
                id: 'cat_balances',
                title: 'اللسيطرة والأرصدة والتقارير',
                icon: 'fa-scale-balanced',
                color: '#f472b6',
                bg: 'rgba(244, 114, 182, 0.2)',
                border: '#f472b6',
                screens: [
                    { href: 'general-ledger.php', label: 'دفتر الأستاذ العام (GL)', icon: 'fa-book-bookmark', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'mainbalances-report.php', label: 'حساب الأرصدة الأساسية', icon: 'fa-scale-balanced', color: '#10b981', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'daily_master_report.php', label: 'التقرير اليومي الشامل', icon: 'fa-calendar-day', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'combined_receivables.php', label: 'حساب مديونية الأرصدة الشامل', icon: 'fa-hand-holding-dollar', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'balances_modern_hub.php', label: 'مركز الأرصدة ومقاصة الحسابات', icon: 'fa-chart-pie', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'aging-report.php', label: 'أعمار الديون والحقوق وسقوف الائتمان', icon: 'fa-clock-rotate-left', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' }
                ]
            },
            {
                id: 'cat_system',
                title: 'الذكاء الاصطناعي والإدارة',
                icon: 'fa-robot',
                color: '#c084fc',
                bg: 'rgba(192, 132, 252, 0.2)',
                border: '#c084fc',
                screens: [
                    { href: 'ai_advisor.php', label: 'الحارس والمستشار الذكي الشامل 360°', icon: 'fa-shield-halved', color: '#22d3ee', border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.2)' },
                    { href: 'ai_goods_advisor.php', label: 'مستشار AI للبضائع والمخازن', icon: 'fa-robot', color: '#34d399', border: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' },
                    { href: 'ai_money_advisor.php', label: 'مستشار AI للأموال والسيولة', icon: 'fa-lightbulb', color: '#c084fc', border: '#a855f7', bg: 'rgba(168, 85, 247, 0.2)' },
                    { href: 'sales_forecasting.php', label: 'التنبؤ المالي الذكي (AI Forecasting)', icon: 'fa-chart-line', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(245, 158, 11, 0.2)' },
                    { href: 'smart_inventory_report.php', label: 'تقارير الذكاء الاصطناعي', icon: 'fa-brain', color: '#38bdf8', border: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'master-data.php', label: 'إدارة البيانات الأساسية', icon: 'fa-database', color: '#60a5fa', border: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' },
                    { href: 'users-roles.php', label: 'المستخدمون والصلاحيات', icon: 'fa-user-gear', color: '#fbbf24', border: '#f59e0b', bg: 'rgba(251, 191, 36, 0.2)' },
                    { href: 'settings.php', label: 'إعدادات النظام والقواعد', icon: 'fa-gear', color: '#94a3b8', border: '#64748b', bg: 'rgba(100, 116, 139, 0.2)' },
                    { href: 'settings.php?tab=fonts', label: 'الخطوط وحجم العرض', icon: 'fa-font', color: '#38bdf8', border: '#0284c7', bg: 'rgba(56, 189, 248, 0.2)' },
                    { href: 'backup.php', label: 'النسخ الاحتياطي', icon: 'fa-hard-drive', color: '#a78bfa', border: '#a78bfa', bg: 'rgba(167, 139, 250, 0.2)' },
                    { href: 'audit-log.php', label: 'سجل الرقابة والتدقيق وتتبع العمليات', icon: 'fa-user-shield', color: '#f87171', border: '#ef4444', bg: 'rgba(248, 113, 113, 0.2)' },
                    { href: 'system-self-audit.php', label: 'درع السيادة', icon: 'fa-shield-halved', color: '#10b981', border: '#059669', bg: 'rgba(16, 185, 129, 0.22)' }
                ]
            }
        ];

        let activeCategoryDropdownId = null;

        function renderCategoryHubs() {
            const grid = document.getElementById('category-hubs-grid');
            if (!grid) return;

            grid.innerHTML = DOMAIN_CATEGORIES.map(cat => `
                <button onclick="toggleCategoryMenu('${cat.id}', event)" id="btn-${cat.id}" class="qa-master-btn cat-hub-btn" style="--btn-border-color: ${cat.border}; --btn-glow-color: ${cat.bg}; border-bottom: 3.5px solid ${cat.border} !important; width: 100%;">
                    <span class="qa-icon" style="background: ${cat.bg}; border: 1px solid ${cat.border}; color: ${cat.color};">
                        <i class="fa-solid ${cat.icon}"></i>
                    </span>
                    <span class="btn-text" style="color: #ffffff; font-size: 0.85rem !important;">${cat.title}</span>
                    <i class="fa-solid fa-chevron-down cat-chevron" style="font-size: 0.72rem; color: ${cat.color}; margin-right: auto; transition: transform 0.3s ease;"></i>
                </button>
            `).join('');
        }

        function toggleCategoryMenu(catId, e) {
            if (e) e.stopPropagation();
            const popover = document.getElementById('category-dropdown-popover');
            const targetBtn = document.getElementById(`btn-${catId}`);
            if (!popover || !targetBtn) return;

            if (activeCategoryDropdownId === catId && popover.style.display === 'block') {
                closeCategoryDropdown();
                return;
            }

            const cat = DOMAIN_CATEGORIES.find(c => c.id === catId);
            if (!cat) return;

            activeCategoryDropdownId = catId;

            // Reset all chevrons
            document.querySelectorAll('.cat-chevron').forEach(ch => ch.style.transform = 'rotate(0deg)');

            const chev = targetBtn.querySelector('.cat-chevron');
            if (chev) chev.style.transform = 'rotate(180deg)';

            // Update title and border color
            popover.style.borderColor = cat.border;
            popover.style.boxShadow = `0 25px 95px rgba(0,0,0,0.99), 0 0 45px ${cat.bg}`;

            const popoverTitle = document.getElementById('cat-popover-title');
            if (popoverTitle) {
                popoverTitle.style.color = cat.color;
                popoverTitle.innerHTML = `<i class="fa-solid ${cat.icon}" style="color: ${cat.color}; font-size: 1.1rem;"></i> 📜 قائمة اختصاص (${cat.title})`;
            }

            const popoverList = document.getElementById('cat-popover-list');
            if (popoverList) {
                const visibleScreens = (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function')
                    ? cat.screens.filter(scr => window.MagaieesPermissions.canUserAccessScreen(scr.href))
                    : cat.screens;

                if (visibleScreens.length === 0) {
                    popoverList.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8; font-size:0.9rem;">🚫 لا تملك صلاحية للوصول إلى أي شاشة في هذا القسم.</div>';
                } else {
                    popoverList.innerHTML = visibleScreens.map(scr => `
                        <a href="${scr.href}" onclick="window.location.href='${scr.href}'" class="screen-popover-item" style="--item-border-color: ${scr.border}; --item-glow-color: ${scr.bg}; border-bottom: 2.5px solid ${scr.border} !important; background: var(--surface, rgba(15, 23, 42, 0.95)) !important;">
                            <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                                <span class="qa-icon" style="width: 32px; height: 32px; min-width: 32px; font-size: 0.88rem; border-radius: 8px; background: ${scr.bg}; border: 1px solid ${scr.border}; color: ${scr.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa-solid ${scr.icon}"></i>
                                </span>
                                <span class="screen-title" style="font-size: 0.86rem; font-weight: 800; color: var(--text-primary, #ffffff);">${scr.label}</span>
                            </div>
                            <i class="fa-solid fa-arrow-left screen-arrow" style="font-size: 0.8rem; color: ${scr.color}; opacity: 0.9;"></i>
                        </a>
                    `).join('');
                }
            }

            // Calculate Position relative to targetBtn in RTL layout
            popover.style.position = 'fixed';
            popover.style.display = 'block';

            const rect = targetBtn.getBoundingClientRect();
            const popoverWidth = Math.min(380, window.innerWidth - 24);
            popover.style.width = `${popoverWidth}px`;

            let topPos = rect.bottom + 8;
            if (topPos + 400 > window.innerHeight) {
                topPos = Math.max(12, rect.top - 420);
            }
            popover.style.top = `${topPos}px`;

            let rightPos = window.innerWidth - rect.right;
            if (rightPos + popoverWidth > window.innerWidth - 12) {
                rightPos = Math.max(12, window.innerWidth - popoverWidth - 12);
            }
            popover.style.right = `${rightPos}px`;
            popover.style.left = 'auto';
            popover.style.zIndex = '999999';
        }

        function closeCategoryDropdown() {
            const popover = document.getElementById('category-dropdown-popover');
            if (popover) popover.style.display = 'none';
            activeCategoryDropdownId = null;
            document.querySelectorAll('.cat-chevron').forEach(ch => ch.style.transform = 'rotate(0deg)');
        }

        document.addEventListener('click', function(e) {
            const popover = document.getElementById('category-dropdown-popover');
            if (popover && popover.style.display === 'block') {
                const targetBtn = activeCategoryDropdownId ? document.getElementById(`btn-${activeCategoryDropdownId}`) : null;
                if (!popover.contains(e.target) && (!targetBtn || !targetBtn.contains(e.target))) {
                    closeCategoryDropdown();
                }
            }
        });

        // ═════════════════════════════════════════════════════════════════════
        //  SINGLE-ROW 7-ITEM QUICK ACTIONS ENGINE WITH RIGHT-CLICK CUSTOMIZER
        // ═════════════════════════════════════════════════════════════════════
        const ALL_QUICK_ACTIONS = [
            { id: 'self_audit', href: 'system-self-audit.php', label: 'درع السيادة', category: 'الإدارة والصلاحيات', icon: 'fa-shield-halved', color: '#10b981', bg: 'rgba(16, 185, 129, 0.24)', border: '#059669' },
            { id: 'audit_log', href: 'audit-log.php', label: 'السجل الرقابي', category: 'الإدارة والصلاحيات', icon: 'fa-user-shield', color: '#fbbf24', bg: 'rgba(245, 158, 11, 0.25)', border: '#f59e0b', isAudit: true },
            { id: 'ai_guardian', href: 'ai_advisor.php', label: 'الحارس الذكي', category: 'الذكاء الاصطناعي', icon: 'fa-shield-halved', color: '#22d3ee', bg: 'rgba(6, 182, 212, 0.2)', border: '#06b6d4' },
            { id: 'ai_goods', href: 'ai_goods_advisor.php', label: 'مستشار AI للبضائع', category: 'الذكاء الاصطناعي', icon: 'fa-robot', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'ai_money', href: 'ai_money_advisor.php', label: 'مستشار AI للأموال', category: 'الذكاء الاصطناعي', icon: 'fa-lightbulb', color: '#c084fc', bg: 'rgba(168, 85, 247, 0.2)', border: '#a855f7' },
            { id: 'alerts_mgr', href: 'alarm.php', label: 'التنبيهات', category: 'التقارير والتنبيهات', icon: 'fa-triangle-exclamation', color: '#fb7185', bg: 'rgba(244, 63, 94, 0.2)', border: '#f43f5e' },
            { id: 'settings', href: 'settings.php', label: 'الإعدادات', category: 'الإدارة والصلاحيات', icon: 'fa-gear', color: '#94a3b8', bg: 'rgba(100, 116, 139, 0.2)', border: '#64748b' },
            { id: 'tools', href: 'engineering_tools.php', label: 'أدوات مساعدة', category: 'أدوات مساعدة', icon: 'fa-toolbox', color: '#f472b6', bg: 'rgba(236, 72, 153, 0.2)', border: '#ec4899' },
            { id: 'inheritance', href: 'engineering_tools.php?tab=inheritance', label: 'الميراث', category: 'أدوات مساعدة', icon: 'fa-scale-balanced', color: '#d97706', bg: 'rgba(217, 119, 6, 0.2)', border: '#d97706' },
            { id: 'smart_inv_rep', href: 'smart_inventory_report.php', label: 'تقارير الذكاء', category: 'التقارير والتنبيهات', icon: 'fa-chart-line', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'sales', href: 'direct-sales.php', label: 'مبيعات', category: 'المبيعات والمشتريات', icon: 'fa-cart-shopping', color: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)', border: '#38bdf8' },
            { id: 'purchase', href: 'purchase.php', label: 'مشتريات', category: 'المبيعات والمشتريات', icon: 'fa-bag-shopping', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#fbbf24' },
            { id: 'vouchers', href: 'vouchers-hub.php', label: 'المعاملات المالية', category: 'المالية والحسابات', icon: 'fa-money-bill-transfer', color: '#f87171', bg: 'rgba(248, 113, 113, 0.2)', border: '#ef4444' },
            { id: 'checks', href: 'checks-cycle.php', label: 'شيكات', category: 'المالية والحسابات', icon: 'fa-money-check-dollar', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#f59e0b' },
            { id: 'analytics', href: 'analytics.php', label: 'تقارير مالية', category: 'المالية والحسابات', icon: 'fa-chart-column', color: '#c084fc', bg: 'rgba(192, 132, 252, 0.2)', border: '#c084fc' },
            { id: 'smart_reports', href: 'smart_reports_hub.php', label: 'التقرير الذكي', category: 'المالية والحسابات', icon: 'fa-brain', color: '#c084fc', bg: 'rgba(168, 85, 247, 0.2)', border: '#a855f7' },
            { id: 'master_data', href: 'master-data.php', label: 'البيانات الأساسية', category: 'البيانات الأساسية', icon: 'fa-database', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.2)', border: '#3b82f6' },
            { id: 'jobs', href: 'jobs.php', label: 'المهام', category: 'الإدارة والصلاحيات', icon: 'fa-list-check', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'account_stmt', href: 'account-statement.php', label: 'كشف حساب', category: 'المالية والحسابات', icon: 'fa-file-invoice-dollar', color: '#38bdf8', bg: 'rgba(56, 189, 248, 0.2)', border: '#38bdf8' },
            { id: 'ledger', href: 'general-ledger.php', label: 'دفتر الأستاذ', category: 'المالية والحسابات', icon: 'fa-book-bookmark', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.2)', border: '#3b82f6' },
            { id: 'customer_portfolio', href: 'customer-portfolio.php', label: 'محفظة العملاء', category: 'المبيعات والمشتريات', icon: 'fa-users', color: '#34d399', bg: 'rgba(16, 185, 129, 0.2)', border: '#10b981' },
            { id: 'inv_report', href: 'inventory-report.php', label: 'تقرير المخزون', category: 'المخزون والمستودعات', icon: 'fa-warehouse', color: '#fbbf24', bg: 'rgba(251, 191, 36, 0.2)', border: '#fbbf24' },
            { id: 'sales_delivery', href: 'sales-delivery.php', label: 'تسليم المبيعات', category: 'المبيعات والمشتريات', icon: 'fa-truck-ramp-box', color: '#f87171', bg: 'rgba(248, 113, 113, 0.2)', border: '#ef4444' },
            { id: 'users_roles', href: 'users-roles.php', label: 'الصلاحيات', category: 'الإدارة والصلاحيات', icon: 'fa-user-gear', color: '#c084fc', bg: 'rgba(192, 132, 252, 0.2)', border: '#c084fc' },
            { id: 'partners', href: 'partners_reports_hub.php', label: 'الشركاء', category: 'التقارير والتنبيهات', icon: 'fa-handshake', color: '#22d3ee', bg: 'rgba(6, 182, 212, 0.2)', border: '#06b6d4' },
            { id: 'backup', href: 'backup.php', label: 'النسخ الاحتياطي', category: 'الإدارة والصلاحيات', icon: 'fa-hard-drive', color: '#94a3b8', bg: 'rgba(100, 116, 139, 0.2)', border: '#64748b' }
        ];

        const DEFAULT_7_IDS = ['audit_log', 'ai_guardian', 'ai_goods', 'ai_money', 'alerts_mgr', 'jobs', 'settings', 'smart_inv_rep', 'sales', 'purchase', 'vouchers', 'checks'];
        let currentScreenSortMode = 'default';

        function sortScreensDropdown(mode) {
            currentScreenSortMode = mode;
            
            const btnDefault = document.getElementById('sort-btn-default');
            const btnAlphabetical = document.getElementById('sort-btn-alphabetical');
            const btnCategory = document.getElementById('sort-btn-category');
            
            [btnDefault, btnAlphabetical, btnCategory].forEach(btn => {
                if (!btn) return;
                btn.style.background = 'rgba(255, 255, 255, 0.08)';
                btn.style.borderColor = 'rgba(255, 255, 255, 0.15)';
                btn.style.color = '#94a3b8';
            });

            const activeBtn = document.getElementById(`sort-btn-${mode}`);
            if (activeBtn) {
                activeBtn.style.background = 'rgba(168, 85, 247, 0.35)';
                activeBtn.style.borderColor = '#c084fc';
                activeBtn.style.color = '#ffffff';
            }

            renderScreensDropdown();
        }

        function fitQARibbonText() {
            document.querySelectorAll('#qa-bar-container .qa-ribbon-btn').forEach(btn => {
                const txt = btn.querySelector('.btn-text');
                if (!txt) return;
                const isAiAdvisor = (txt.textContent || '').includes('مستشار AI');
                let size = isAiAdvisor ? 15.5 : 17.5;
                txt.style.setProperty('font-size', size + 'px', 'important');
                const maxW = btn.clientWidth - (btn.id === 'screens-dropdown-btn' ? 28 : 12);
                while (txt.scrollWidth > maxW && size > 11) {
                    size -= 0.5;
                    txt.style.setProperty('font-size', size + 'px', 'important');
                }
            });
        }

        function renderSingleRowQA() {
            let saved = localStorage.getItem('dashboard_quick_actions_12');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try {
                    let parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) selectedIds = parsed.slice(0, 12);
                } catch(e){}
            }

            const grid = document.getElementById('qa-single-row-grid');
            if (!grid) return;

            let activeItems = selectedIds.map(id => ALL_QUICK_ACTIONS.find(a => a.id === id)).filter(Boolean);
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function') {
                activeItems = activeItems.filter(item => window.MagaieesPermissions.canUserAccessScreen(item.href));
            }
            const custLbl = document.getElementById('qa-customizer-btn-label');
            if (custLbl) custLbl.textContent = `تخصيص (${activeItems.length})`;
            
            grid.innerHTML = activeItems.map(item => `
                <a href="${item.href}" data-href="${item.href}" onclick="window.location.href='${item.href}'" ${item.isAudit ? 'oncontextmenu="if(window.openAuditTrackerModal){event.preventDefault(); window.openAuditTrackerModal();} return false;" title="فتح السجل الرقابي وتتبع العمليات (كليك يمين للبحث الفوري السريع Alt+T)"' : ''} class="qa-master-btn qa-ribbon-btn" style="--btn-border-color: ${item.border}; --btn-glow-color: ${item.bg}; border-bottom: 3.5px solid ${item.border} !important;">
                    <span class="btn-text">${item.label}</span>
                </a>
            `).join('');
            
            requestAnimationFrame(fitQARibbonText);
            renderScreensDropdown();
        }
        window.addEventListener('resize', () => requestAnimationFrame(fitQARibbonText), { passive: true });

        function renderScreensDropdown() {
            let saved = localStorage.getItem('dashboard_quick_actions_12');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try {
                    let parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) selectedIds = parsed.slice(0, 12);
                } catch(e){}
            }

            let unselected = ALL_QUICK_ACTIONS.filter(item => !selectedIds.includes(item.id));
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessScreen === 'function') {
                unselected = unselected.filter(item => window.MagaieesPermissions.canUserAccessScreen(item.href));
            }

            if (currentScreenSortMode === 'alphabetical') {
                unselected.sort((a, b) => a.label.localeCompare(b.label, 'ar'));
            } else if (currentScreenSortMode === 'category') {
                unselected.sort((a, b) => (a.category || '').localeCompare((b.category || ''), 'ar') || a.label.localeCompare(b.label, 'ar'));
            }
            const badge = document.getElementById('screens-count-badge');
            if (badge) badge.innerText = unselected.length;

            const listContainer = document.getElementById('screens-dropdown-list');
            if (!listContainer) return;

            if (unselected.length === 0) {
                listContainer.innerHTML = '<div style="grid-column: 1 / -1; text-align:center; padding:20px; color:#94a3b8; font-size:0.85rem; font-weight: 700;">جميع الشاشات مخصصة في الأزرار السبعة 🎉</div>';
                return;
            }

            listContainer.innerHTML = unselected.map(item => `
                <a href="${item.href}" onclick="window.location.href='${item.href}'" class="screen-popover-item screen-dropdown-item" style="--item-border-color: ${item.border}; --item-glow-color: ${item.bg}; --item-color: ${item.color}; border-bottom: 2.5px solid ${item.border} !important;">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                        <span class="qa-icon" style="width: 28px; height: 28px; min-width: 28px; font-size: 0.8rem; border-radius: 8px; background: ${item.bg}; border: 1px solid ${item.border}; color: ${item.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid ${item.icon}"></i>
                        </span>
                        <span class="screen-title" style="font-size: 0.86rem; font-weight: 800; color: var(--text-primary, #ffffff); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.label}</span>
                    </div>
                    <i class="fa-solid fa-arrow-left screen-arrow" style="font-size: 0.75rem; color: ${item.color}; opacity: 0.85;"></i>
                </a>
            `).join('');
        }

        function positionScreensDropdown() {
            const menu = document.getElementById('screens-dropdown-menu');
            const btn = document.getElementById('screens-dropdown-btn');
            if (!menu || !btn) return;
            
            menu.style.position = 'fixed';
            // Start at the very top of the screen (12px), covering top clock/user bar
            menu.style.top = '12px';
            
            const menuWidth = Math.min(500, window.innerWidth - 24);
            menu.style.width = `${menuWidth}px`;
            
            // Align right side near the button in RTL layout
            const rect = btn.getBoundingClientRect();
            let rightPos = window.innerWidth - rect.right;
            if (rightPos + menuWidth > window.innerWidth - 12) {
                rightPos = Math.max(12, window.innerWidth - menuWidth - 12);
            }
            menu.style.right = `${rightPos}px`;
            menu.style.left = 'auto';
            menu.style.zIndex = '999999';
        }

        function toggleScreensDropdown(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('screens-dropdown-menu');
            const chev = document.getElementById('screens-chevron');
            if (!menu) return;

            const isHidden = menu.style.display === 'none' || menu.style.display === '';
            if (isHidden) {
                renderScreensDropdown();
                positionScreensDropdown();
                menu.style.display = 'block';
                if (chev) chev.style.transform = 'rotate(180deg)';
                const searchInp = document.getElementById('screens-search-input');
                if (searchInp) {
                    searchInp.value = '';
                    setTimeout(() => searchInp.focus(), 50);
                }
            } else {
                menu.style.display = 'none';
                if (chev) chev.style.transform = 'rotate(0deg)';
            }
        }

        function filterScreensDropdown() {
            const query = (document.getElementById('screens-search-input')?.value || '').toLowerCase().trim();
            const items = document.querySelectorAll('#screens-dropdown-list .screen-dropdown-item');
            items.forEach(el => {
                const text = el.textContent.toLowerCase();
                el.style.display = text.includes(query) ? 'flex' : 'none';
            });
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('screens-dropdown-menu');
            const btn = document.getElementById('screens-dropdown-btn');
            if (menu && menu.style.display === 'block') {
                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                    menu.style.display = 'none';
                    const chev = document.getElementById('screens-chevron');
                    if (chev) chev.style.transform = 'rotate(0deg)';
                }
            }
        });

        window.addEventListener('scroll', function() {
            const menu = document.getElementById('screens-dropdown-menu');
            if (menu && menu.style.display === 'block') {
                positionScreensDropdown();
            }
        }, { passive: true });

        window.addEventListener('resize', function() {
            const menu = document.getElementById('screens-dropdown-menu');
            if (menu && menu.style.display === 'block') {
                positionScreensDropdown();
            }
        }, { passive: true });

        function openQACustomizer(e) {
            if (e && e.preventDefault) e.preventDefault();
            let modal = document.getElementById('qa-customizer-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'qa-customizer-modal';
                modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:99999; display:flex; align-items:center; justify-content:center; direction:rtl;';
                document.body.appendChild(modal);
            }
            
            let saved = localStorage.getItem('dashboard_quick_actions_12');
            let selectedIds = DEFAULT_7_IDS;
            if (saved) {
                try { selectedIds = JSON.parse(saved); } catch(e){}
            }
            
            let listHTML = ALL_QUICK_ACTIONS.map(item => {
                const isChecked = selectedIds.includes(item.id) ? 'checked' : '';
                return `
                    <label style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(15,23,42,0.7); border:1px solid rgba(255,255,255,0.08); border-radius:10px; cursor:pointer; transition:0.2s;">
                        <input type="checkbox" class="qa-chk" value="${item.id}" ${isChecked} onchange="limitQAChecks(this)" style="width:18px; height:18px; accent-color:#38bdf8; cursor:pointer;">
                        <span style="width:24px; height:24px; border-radius:6px; background:${item.bg}; border:1px solid ${item.border}; color:${item.color}; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem;">
                            <i class="fa-solid ${item.icon}"></i>
                        </span>
                        <span style="font-weight:700; font-size:0.85rem; color:#fff;">${item.label}</span>
                    </label>
                `;
            }).join('');

            modal.innerHTML = `
                <div class="glass-panel" style="width:90%; max-width:560px; max-height:85vh; padding:25px; border-radius:20px; border:1px solid rgba(56,189,248,0.3); background:#0f172a; box-shadow:0 20px 60px rgba(0,0,0,0.9); overflow-y:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
                        <h3 style="margin:0; font-weight:800; font-size:1.1rem; color:#38bdf8; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-sliders"></i> تخصيص أزرار الوصول السريع (12 زر في صفين متساويين)
                        </h3>
                        <button onclick="document.getElementById('qa-customizer-modal').style.display='none'" style="background:none; border:none; color:#f87171; font-size:1.4rem; cursor:pointer;">&times;</button>
                    </div>
                    <p style="font-size:0.8rem; color:#94a3b8; margin-bottom:15px;">اختر 12 زر ليتم عرضها في الصفين العلويين (5 في الصف الأول + 7 في الصف الثاني):</p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:10px; margin-bottom:20px;">
                        ${listHTML}
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button onclick="document.getElementById('qa-customizer-modal').style.display='none'" style="padding:8px 16px; border-radius:10px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; cursor:pointer;">إلغاء</button>
                        <button onclick="saveQACustomization()" style="padding:8px 20px; border-radius:10px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:#fff; font-weight:800; cursor:pointer; box-shadow:0 4px 15px rgba(56,189,248,0.4);">حفظ التبديل</button>
                    </div>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function limitQAChecks(chk) {
            const checked = document.querySelectorAll('.qa-chk:checked');
            if (checked.length > 12) {
                chk.checked = false;
                if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار 12 زر كحد أقصى للصفين', 'warning');
            }
        }

        function saveQACustomization() {
            const checked = Array.from(document.querySelectorAll('.qa-chk:checked')).map(c => c.value);
            if (checked.length === 0) {
                if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار زر واحد على الأقل', 'warning');
                return;
            }
            localStorage.setItem('dashboard_quick_actions_12', JSON.stringify(checked.slice(0, 12)));
            renderSingleRowQA();
            renderScreensDropdown();
            document.getElementById('qa-customizer-modal').style.display = 'none';
            if (typeof showToast === 'function') showToast('✅ تم حفظ التبديل وعرض الأزرار الـ 12 بنجاح', 'success');
        }

        // Initialize Quick Actions, Category Hubs & Screens Dropdown immediately on DOM ready
        function initDashboardNav() {
            renderCategoryHubs();
            renderSingleRowQA();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDashboardNav);
        } else {
            initDashboardNav();
        }
