// ============================================================================
// MAGAIEES ERP - Unified Permissions & Access Control Studio
// نظام إدارة الصلاحيات والتحكم الدقيق في الشاشات والأزرار والتقارير
// ============================================================================

(function(window) {
    'use strict';

    // 1. Definition of All System Screens Grouped by Category
    const SYSTEM_SCREENS_CATALOG = [
        {
            category: 'sales',
            categoryName: 'المبيعات ونقاط البيع',
            categoryIcon: 'fa-cart-shopping',
            color: '#38bdf8',
            screens: [
                {
                    id: 'direct_sales',
                    file: 'direct-sales.php',
                    title: 'المبيعات المباشرة ونقاط البيع (POS)',
                    icon: 'fa-cart-shopping',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'sales_invoice',
                    file: 'sales-invoice.php',
                    title: 'فواتير المبيعات الآجلة',
                    icon: 'fa-receipt',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'reserved_sales',
                    file: 'reserved-sales.php',
                    title: 'حجوزات المبيعات وعروض الأسعار',
                    icon: 'fa-bookmark',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'sales_delivery',
                    file: 'sales-delivery.php',
                    title: 'إذن تسليم وإخراج المبيعات',
                    icon: 'fa-truck-ramp-box',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'customer_portfolio',
                    file: 'customer-portfolio.php',
                    title: 'محفظة وتحليلات العملاء',
                    icon: 'fa-users',
                    supportedActions: ['print', 'share', 'export']
                }
            ]
        },
        {
            category: 'purchases',
            categoryName: 'المشتريات والموردين',
            categoryIcon: 'fa-bag-shopping',
            color: '#fbbf24',
            screens: [
                {
                    id: 'purchase',
                    file: 'purchase.php',
                    title: 'فواتير المشتريات العامة',
                    icon: 'fa-bag-shopping',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'purchase_receipt',
                    file: 'purchase-receipt.php',
                    title: 'استلام شحنات المشتريات',
                    icon: 'fa-box-open',
                    supportedActions: ['save', 'new', 'edit', 'print']
                },
                {
                    id: 'purchase_return',
                    file: 'purchase-return.php',
                    title: 'مرتجعات المشتريات',
                    icon: 'fa-rotate-left',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'supplier_reliability',
                    file: 'supplier_reliability.php',
                    title: 'اعتمادية وموثوقية الموردين',
                    icon: 'fa-award',
                    supportedActions: ['print', 'export']
                }
            ]
        },
        {
            category: 'treasury',
            categoryName: 'الخزينة والمعاملات المالية',
            categoryIcon: 'fa-vault',
            color: '#34d399',
            screens: [
                {
                    id: 'single_cash_payment',
                    file: 'single-cash-payment.php',
                    title: 'سند صرف نقدية مفرد',
                    icon: 'fa-file-invoice',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'single_cash_receipt',
                    file: 'single-cash-receipt.php',
                    title: 'سند قبض نقدية مفرد',
                    icon: 'fa-receipt',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'multi_cash_payment',
                    file: 'multi-cash-payment.php',
                    title: 'سند صرف ودفع مجمع (متعدد)',
                    icon: 'fa-money-bill-wave',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print', 'share']
                },
                {
                    id: 'vouchers_hub',
                    file: 'vouchers-hub.php',
                    title: 'مركز السندات المالي الموحد',
                    icon: 'fa-money-bill-transfer',
                    supportedActions: ['print', 'share', 'export']
                },
                {
                    id: 'safe_transfer',
                    file: 'safe-transfer.php',
                    title: 'تحويل نقدية بين الخزن والبنوك',
                    icon: 'fa-arrow-right-arrow-left',
                    supportedActions: ['save', 'new', 'print']
                },
                {
                    id: 'checks_cycle',
                    file: 'checks-cycle.php',
                    title: 'دورة إدارة الشيكات المصرفية',
                    icon: 'fa-money-check-dollar',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print']
                }
            ]
        },
        {
            category: 'accounting',
            categoryName: 'الحسابات العامة ودفتر الأستاذ',
            categoryIcon: 'fa-book-bookmark',
            color: '#60a5fa',
            screens: [
                {
                    id: 'general_ledger',
                    file: 'general-ledger.php',
                    title: 'دفتر الأستاذ العام',
                    icon: 'fa-book-bookmark',
                    supportedActions: ['print', 'share', 'export']
                },
                {
                    id: 'account_statement',
                    file: 'account-statement.php',
                    title: 'كشف حساب شريك / عميل / مورد',
                    icon: 'fa-file-invoice-dollar',
                    supportedActions: ['print', 'share', 'export']
                },
                {
                    id: 'trial_balance',
                    file: 'trial-balance.php',
                    title: 'ميزان المراجعة والأرصدة',
                    icon: 'fa-scale-balanced',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'pl_statement',
                    file: 'pl-statement.php',
                    title: 'قائمة الأرباح والخسائر',
                    icon: 'fa-chart-line',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'journal_voucher',
                    file: 'journal-voucher.php',
                    title: 'سند قيد اليومية والتسويات',
                    icon: 'fa-pen-to-square',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'print']
                },
                {
                    id: 'posting_accounts',
                    file: 'posting-accounts.php',
                    title: 'شجرة الحسابات واعتماد القيود',
                    icon: 'fa-sitemap',
                    supportedActions: ['save', 'edit', 'delete']
                }
            ]
        },
        {
            category: 'inventory',
            categoryName: 'المخزون والمستودعات',
            categoryIcon: 'fa-warehouse',
            color: '#a78bfa',
            screens: [
                {
                    id: 'smart_inventory_report',
                    file: 'smart_inventory_report.php',
                    title: 'تقرير المخزون الذكي والجرد',
                    icon: 'fa-brain',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'inventory_report',
                    file: 'inventory-report.php',
                    title: 'تقرير المخزون الشامل والتقييم',
                    icon: 'fa-boxes-stacked',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'item_ledger',
                    file: 'item-ledger.php',
                    title: 'دفتر كرت حركة الصنف',
                    icon: 'fa-barcode',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'warehouse_ledger',
                    file: 'warehouse-ledger.php',
                    title: 'دفتر أستاذ المستودع',
                    icon: 'fa-warehouse',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'warehouse_transfer',
                    file: 'warehouse-transfer.php',
                    title: 'التحويل بين المستودعات',
                    icon: 'fa-arrows-left-right',
                    supportedActions: ['save', 'new', 'print']
                },
                {
                    id: 'delivery_note',
                    file: 'delivery-note.php',
                    title: 'إذن التسليم المخزني',
                    icon: 'fa-clipboard-check',
                    supportedActions: ['save', 'new', 'print', 'share']
                }
            ]
        },
        {
            category: 'reports',
            categoryName: 'التقارير التحليلية والذكاء',
            categoryIcon: 'fa-chart-pie',
            color: '#f472b6',
            screens: [
                {
                    id: 'analytics',
                    file: 'analytics.php',
                    title: 'التحليلات والمؤشرات المالية',
                    icon: 'fa-chart-column',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'mainbalances_report',
                    file: 'mainbalances-report.php',
                    title: 'تقرير الأرصدة الأساسية',
                    icon: 'fa-scale-balanced',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'aging_report',
                    file: 'aging-report.php',
                    title: 'تقرير أعمار الديون والمطالبات',
                    icon: 'fa-clock-rotate-left',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'daily_master_report',
                    file: 'daily_master_report.php',
                    title: 'التقرير اليومي الشامل',
                    icon: 'fa-calendar-day',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'sales_forecasting',
                    file: 'sales_forecasting.php',
                    title: 'التنبؤ بالمبيعات وذكاء السوق',
                    icon: 'fa-chart-line',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'alarm',
                    file: 'alarm.php',
                    title: 'مركز التنبيهات والإنذارات الاستباقية',
                    icon: 'fa-bell',
                    supportedActions: ['save', 'edit']
                }
            ]
        },
        {
            category: 'system',
            categoryName: 'الإدارة والنظام والأمان',
            categoryIcon: 'fa-gear',
            color: '#94a3b8',
            screens: [
                {
                    id: 'master_data',
                    file: 'master-data.php',
                    title: 'البيانات الأساسية (عملاء، موردين، أصناف)',
                    icon: 'fa-database',
                    supportedActions: ['save', 'new', 'edit', 'delete', 'export']
                },
                {
                    id: 'users_roles',
                    file: 'users-roles.php',
                    title: 'المستخدمون والصلاحيات',
                    icon: 'fa-user-gear',
                    supportedActions: ['save', 'new', 'edit', 'delete']
                },
                {
                    id: 'settings',
                    file: 'settings.php',
                    title: 'إعدادات النظام والقواعد',
                    icon: 'fa-gear',
                    supportedActions: ['save', 'edit']
                },
                {
                    id: 'audit_log',
                    file: 'audit-log.php',
                    title: 'سجل التدقيق والعمليات (الصندوق الأسود)',
                    icon: 'fa-user-shield',
                    supportedActions: ['print', 'export']
                },
                {
                    id: 'backup',
                    file: 'backup.php',
                    title: 'النسخ الاحتياطي للنظام',
                    icon: 'fa-hard-drive',
                    supportedActions: ['save']
                }
            ]
        }
    ];

    // 2. Definition of Granular Reports
    const SYSTEM_REPORTS_CATALOG = [
        { id: 'rep_statement', title: 'كشف حساب تفصيلي للعميل / المورد', icon: 'fa-file-invoice-dollar' },
        { id: 'rep_general_ledger', title: 'دفتر الأستاذ العام وتفاصيل القيود', icon: 'fa-book-bookmark' },
        { id: 'rep_trial_balance', title: 'ميزان المراجعة بالأرصدة والمجاميع', icon: 'fa-scale-balanced' },
        { id: 'rep_pl', title: 'قائمة الأرباح والخسائر وهامش الربح', icon: 'fa-chart-line' },
        { id: 'rep_aging', title: 'تقرير أعمار الديون والمطالبات المتأخرة', icon: 'fa-clock-rotate-left' },
        { id: 'rep_inventory_val', title: 'تقرير جرد المخزون وتقييم البضاعة', icon: 'fa-boxes-stacked' },
        { id: 'rep_item_movement', title: 'كرت حركة الصنف والكميات الواردة والصادرة', icon: 'fa-barcode' },
        { id: 'rep_daily_cash', title: 'ملخص حركة النقدية اليومية والمقبوضات', icon: 'fa-calendar-day' },
        { id: 'rep_sales_analytics', title: 'تحليلات المبيعات ونقاط البيع', icon: 'fa-chart-pie' },
        { id: 'rep_audit', title: 'سجل التدقيق والعمليات الأمنية', icon: 'fa-shield-halved' }
    ];

    // Action Labels
    const ACTION_META = {
        save: { label: 'حفظ', icon: 'fa-floppy-disk', color: '#10b981' },
        new: { label: 'جديد', icon: 'fa-plus', color: '#38bdf8' },
        edit: { label: 'تعديل', icon: 'fa-pen-to-square', color: '#fbbf24' },
        delete: { label: 'حذف', icon: 'fa-trash-can', color: '#ef4444' },
        print: { label: 'طباعة', icon: 'fa-print', color: '#a855f7' },
        share: { label: 'مشاركة/واتساب', icon: 'fa-share-nodes', color: '#25d366' },
        export: { label: 'تصدير', icon: 'fa-file-export', color: '#ec4899' }
    };

    // Quick Templates
    const PRESET_TEMPLATES = {
        admin: {
            title: 'مدير نظام (كامل الصلاحيات)',
            userType: 'admin'
        },
        cashier: {
            title: 'كاشير / بائع مباشر',
            userType: 'user',
            allowedScreens: {
                'direct-sales.php': true,
                'single-cash-receipt.php': true,
                'customer-portfolio.php': true,
                'item-ledger.php': true
            },
            screenButtons: {
                'direct-sales.php': { save: true, new: true, print: true, share: true, edit: false, delete: false },
                'single-cash-receipt.php': { save: true, new: true, print: true, share: true, edit: false, delete: false },
                'customer-portfolio.php': { print: true, share: true }
            },
            allowedReports: {
                'rep_statement': true,
                'rep_daily_cash': true
            }
        },
        accountant: {
            title: 'محاسب مالي',
            userType: 'user',
            allowedScreens: {
                'direct-sales.php': true,
                'sales-invoice.php': true,
                'purchase.php': true,
                'single-cash-payment.php': true,
                'single-cash-receipt.php': true,
                'multi-cash-payment.php': true,
                'safe-transfer.php': true,
                'checks-cycle.php': true,
                'vouchers-hub.php': true,
                'general-ledger.php': true,
                'account-statement.php': true,
                'trial-balance.php': true,
                'pl-statement.php': true,
                'journal-voucher.php': true,
                'daily_master_report.php': true,
                'aging-report.php': true
            },
            screenButtons: {
                'direct-sales.php': { save: true, new: true, print: true, share: true, edit: true, delete: false },
                'sales-invoice.php': { save: true, new: true, print: true, share: true, edit: true, delete: false },
                'purchase.php': { save: true, new: true, print: true, share: true, edit: true, delete: false },
                'single-cash-payment.php': { save: true, new: true, print: true, share: true, edit: true, delete: false },
                'single-cash-receipt.php': { save: true, new: true, print: true, share: true, edit: true, delete: false },
                'multi-cash-payment.php': { save: true, new: true, print: true, share: true, edit: true, delete: false },
                'general-ledger.php': { print: true, share: true, export: true },
                'account-statement.php': { print: true, share: true, export: true },
                'trial-balance.php': { print: true, export: true }
            },
            allowedReports: {
                'rep_statement': true,
                'rep_general_ledger': true,
                'rep_trial_balance': true,
                'rep_pl': true,
                'rep_aging': true,
                'rep_daily_cash': true
            }
        },
        storekeeper: {
            title: 'أمين مستودع / مخازن',
            userType: 'user',
            allowedScreens: {
                'smart_inventory_report.php': true,
                'inventory-report.php': true,
                'item-ledger.php': true,
                'warehouse-ledger.php': true,
                'warehouse-transfer.php': true,
                'delivery-note.php': true,
                'purchase-receipt.php': true,
                'sales-delivery.php': true
            },
            screenButtons: {
                'warehouse-transfer.php': { save: true, new: true, print: true },
                'delivery-note.php': { save: true, new: true, print: true, share: true },
                'purchase-receipt.php': { save: true, new: true, print: true },
                'sales-delivery.php': { save: true, new: true, print: true, share: true }
            },
            allowedReports: {
                'rep_inventory_val': true,
                'rep_item_movement': true
            }
        }
    };

    // Helper: Get Current Active User
    function getCurrentUser() {
        try {
            const userStr = localStorage.getItem('user');
            if (userStr && userStr !== 'undefined') {
                return JSON.parse(userStr);
            }
        } catch(e) {}
        return null;
    }

    // Helper: Is Current User an Admin?
    function isCurrentUserAdmin(user) {
        const u = user || getCurrentUser();
        if (!u) return false;
        if (u.userType === 'admin') return true;
        if (u.role === 'admin' || u.roleId === 'role_admin_default' || u.roleId === 'admin_fallback') return true;
        if (u.isSystem === true && (u.username === 'admin' || u.username === 'c')) return true;
        if (u.securityLevel >= 9) return true;
        return false;
    }

    // Helper: Can Current User Access a Screen?
    function canUserAccessScreen(screenUrl, user) {
        const u = user || getCurrentUser();
        if (!u) return true; // fallback
        if (isCurrentUserAdmin(u)) return true;

        const baseFile = (screenUrl || '').split('?')[0].split('/').pop();
        if (!baseFile || baseFile === 'magaiees_unified_dashboard.php' || baseFile === 'dashboard-pro.php' || baseFile === 'index.php' || baseFile === 'login.php') {
            return true;
        }

        // Check explicit screen permission in user object
        if (u.allowedScreens && typeof u.allowedScreens === 'object') {
            if (u.allowedScreens[baseFile] === true) return true;
            if (u.allowedScreens[baseFile] === false) return false;
        }

        // Check fallback in legacy perms map if configured
        if (typeof window.resolveUserPermissions === 'function') {
            const p = window.resolveUserPermissions(u);
            if (p) {
                // If the screen belongs to a module that is allowed
                for (let cat of SYSTEM_SCREENS_CATALOG) {
                    const match = cat.screens.find(s => s.file === baseFile);
                    if (match) {
                        const modPerm = p[cat.category];
                        if (modPerm && modPerm.view === false) return false;
                        if (modPerm && modPerm.view === true) return true;
                    }
                }
            }
        }

        // Default for standard user if not explicitly allowed
        return false;
    }

    // Helper: Can Current User Do an Action (Save, New, Edit, Delete, Print, Share) on a Screen?
    function canUserDoAction(screenUrl, action, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        const baseFile = (screenUrl || '').split('?')[0].split('/').pop();

        // Check granular button permissions map
        if (u.screenButtons && u.screenButtons[baseFile] && typeof u.screenButtons[baseFile][action] === 'boolean') {
            return u.screenButtons[baseFile][action];
        }

        // Check module-level action fallback
        if (typeof window.resolveUserPermissions === 'function') {
            const p = window.resolveUserPermissions(u);
            if (p) {
                for (let cat of SYSTEM_SCREENS_CATALOG) {
                    const match = cat.screens.find(s => s.file === baseFile);
                    if (match) {
                        const modPerm = p[cat.category];
                        if (modPerm && typeof modPerm[action] === 'boolean') {
                            return modPerm[action];
                        }
                    }
                }
            }
        }

        return false;
    }

    // Helper: Can Current User View a Report?
    function canUserViewReport(reportId, user) {
        const u = user || getCurrentUser();
        if (!u) return true;
        if (isCurrentUserAdmin(u)) return true;

        if (u.allowedReports && typeof u.allowedReports[reportId] === 'boolean') {
            return u.allowedReports[reportId];
        }
        return false;
    }

    // ═══════════════════════════════════════════════════════════════════
    // RUNTIME SCREEN & BUTTON SECURITY ENFORCER
    // ═══════════════════════════════════════════════════════════════════
    function enforceScreenSecurity() {
        const user = getCurrentUser();
        if (!user) return;

        const currentFile = window.location.pathname.split('/').pop() || 'index.php';
        
        // Skip dashboard, login, settings for admin
        if (currentFile === 'login.php' || currentFile === 'magaiees_unified_dashboard.php' || currentFile === 'dashboard-pro.php' || currentFile === 'index.php') {
            return;
        }

        // 1. Check Screen-Level Access
        if (!canUserAccessScreen(currentFile, user)) {
            console.warn(`[Permissions] Access denied to screen: ${currentFile} for user: ${user.username}`);
            showAccessDeniedUI(currentFile);
            return;
        }

        // 2. Check and Apply Button-Level Permissions on this screen
        applyScreenButtonSecurity(currentFile, user);
    }

    function showAccessDeniedUI(currentFile) {
        document.body.innerHTML = `
            <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:#030712; font-family:'Tajawal', sans-serif; direction:rtl; padding:20px;">
                <div style="background:rgba(17,24,39,0.85); backdrop-filter:blur(20px); border:1px solid rgba(239,68,68,0.4); border-radius:24px; padding:40px; max-width:550px; text-align:center; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8);">
                    <div style="width:80px; height:80px; margin:0 auto 20px; background:rgba(239,68,68,0.1); border:2px solid #ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:#ef4444;">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <h2 style="color:#ffffff; font-size:1.8rem; margin-bottom:12px; font-weight:800;">غير مصرح لك بالدخول</h2>
                    <p style="color:#94a3b8; font-size:1.05rem; line-height:1.7; margin-bottom:25px;">
                        تم تقييد الوصول إلى هذه الشاشة (<span style="color:#38bdf8; direction:ltr; display:inline-block;">${currentFile}</span>) بحسب صلاحيات حسابك المسجلة لدى مدير النظام.
                    </p>
                    <div style="display:flex; gap:12px; justify-content:center;">
                        <a href="magaiees_unified_dashboard.php" style="background:#38bdf8; color:#030712; text-decoration:none; padding:12px 25px; border-radius:12px; font-weight:700; font-size:1rem; display:inline-flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-house"></i> العودة للرئيسية
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    function applyScreenButtonSecurity(currentFile, user) {
        if (isCurrentUserAdmin(user)) return; // Admin has full access

        const actionsToCheck = ['save', 'new', 'edit', 'delete', 'print', 'share'];

        actionsToCheck.forEach(action => {
            const allowed = canUserDoAction(currentFile, action, user);
            if (!allowed) {
                disableOrHideActionElements(action);
            }
        });
    }

    function disableOrHideActionElements(action) {
        let selectors = [];
        if (action === 'share') {
            selectors = [
                '.btn-whatsapp', '.btn-share', '.action-wa-btn', '.btn-wa',
                'button[onclick*="sendWA"]', 'button[onclick*="sendVoucherWA"]',
                'button[onclick*="share"]', 'button[onclick*="Share"]',
                'a[href*="whatsapp"]', 'a[href*="wa.me"]', '#btn-share-wa'
            ];
        } else if (action === 'new') {
            selectors = [
                '#btn-new', '.btn-new', '.btn-new-invoice', '.btn-add-new',
                'button[onclick*="newInvoice"]', 'button[onclick*="resetForm"]',
                'button[onclick*="resetFields"]', 'button[onclick*="clearForm"]'
            ];
        } else if (action === 'save') {
            selectors = [
                '#btn-save', '.btn-save', '.btn-submit', '.btn-save-invoice',
                'button[onclick*="save"]', 'button[onclick*="Save"]', 'button[type="submit"]',
                '#save-btn', '#btn-checkout', '#btn-save-voucher'
            ];
        } else if (action === 'print') {
            selectors = [
                '#btn-print', '.btn-print', '.print-btn',
                'button[onclick*="print"]', 'button[onclick*="Print"]',
                'button[onclick*="printInvoice"]', 'button[onclick*="printVoucher"]'
            ];
        } else if (action === 'delete') {
            selectors = [
                '.del-btn', '.btn-delete', '.btn-remove',
                'button[onclick*="delete"]', 'button[onclick*="remove"]'
            ];
        } else if (action === 'edit') {
            selectors = [
                '.edit-btn', '.btn-edit', 'button[onclick*="edit"]'
            ];
        }

        if (selectors.length > 0) {
            const query = selectors.join(', ');
            try {
                document.querySelectorAll(query).forEach(el => {
                    el.style.display = 'none';
                    el.disabled = true;
                    el.setAttribute('data-perm-restricted', action);
                });
            } catch(e) {}
        }
    }

    // Auto-run security on DOMContentLoaded and reactive lifecycles
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enforceScreenSecurity);
    } else {
        enforceScreenSecurity();
    }
    document.addEventListener('DBReady', enforceScreenSecurity);
    document.addEventListener('posReady', enforceScreenSecurity);
    document.addEventListener('strictDataReady', enforceScreenSecurity);
    setTimeout(enforceScreenSecurity, 500);
    setTimeout(enforceScreenSecurity, 1500);

    // ═══════════════════════════════════════════════════════════════════
    // VISUAL PERMISSIONS STUDIO BUILDER (FOR SETTINGS & USERS-ROLES)
    // ═══════════════════════════════════════════════════════════════════

    function renderPermissionsStudioUI(containerElement, userObj, options = {}) {
        if (!containerElement) return;

        const isUserAdmin = userObj ? (userObj.userType === 'admin' || userObj.role === 'admin') : false;
        const allowedScreens = (userObj && userObj.allowedScreens) ? userObj.allowedScreens : {};
        const screenButtons = (userObj && userObj.screenButtons) ? userObj.screenButtons : {};
        const allowedReports = (userObj && userObj.allowedReports) ? userObj.allowedReports : {};

        let html = `
            <div class="perm-studio-wrap" style="font-family:'Tajawal', sans-serif; direction:rtl; color:#f8fafc;">
                
                <!-- 1. Header & Role Type Selection -->
                <div style="background:rgba(15,23,42,0.6); border:1px solid rgba(56,189,248,0.25); border-radius:16px; padding:20px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
                        <div>
                            <h3 style="margin:0 0 6px 0; color:#38bdf8; font-size:1.3rem; display:flex; align-items:center; gap:10px;">
                                <i class="fa-solid fa-user-shield"></i> لوحة تحكم صلاحيات المستخدم والشاشات
                            </h3>
                            <p style="margin:0; color:#94a3b8; font-size:0.9rem;">
                                حدد نوع المستخدم (مدير نظام أو مستخدم عادي)، وتخصيص ظهور كل شاشة وأزرار العمليات والتقارير بدقة.
                            </p>
                        </div>

                        <!-- User Type Selection (Admin vs Standard) -->
                        <div style="display:flex; gap:10px; background:rgba(0,0,0,0.4); padding:6px; border-radius:12px; border:1px solid rgba(255,255,255,0.1);">
                            <label style="display:flex; align-items:center; gap:8px; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:700; font-size:0.9rem; transition:all 0.2s;" id="label-role-admin" class="${isUserAdmin ? 'active-role-admin' : ''}">
                                <input type="radio" name="studio_user_type" value="admin" ${isUserAdmin ? 'checked' : ''} onchange="window.onStudioUserTypeChange(this.value)" style="accent-color:#f59e0b;">
                                👑 مدير نظام (كامل الصلاحيات)
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:700; font-size:0.9rem; transition:all 0.2s;" id="label-role-user" class="${!isUserAdmin ? 'active-role-user' : ''}">
                                <input type="radio" name="studio_user_type" value="user" ${!isUserAdmin ? 'checked' : ''} onchange="window.onStudioUserTypeChange(this.value)" style="accent-color:#38bdf8;">
                                👤 مستخدم عادي (صلاحيات مخصصة)
                            </label>
                        </div>
                    </div>

                    <!-- Quick Preset Templates Bar -->
                    <div id="studio-presets-bar" style="display:${isUserAdmin ? 'none' : 'flex'}; align-items:center; gap:10px; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px; margin-top:10px;">
                        <span style="font-size:0.85rem; color:#94a3b8; font-weight:700;">⚡ نماذج سريعة جاهزة:</span>
                        <button type="button" onclick="window.applyStudioPreset('cashier')" style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8; padding:6px 14px; border-radius:8px; font-size:0.82rem; cursor:pointer; font-weight:700;">
                            💵 كاشير / بائع
                        </button>
                        <button type="button" onclick="window.applyStudioPreset('accountant')" style="background:rgba(52,211,153,0.15); border:1px solid #10b981; color:#34d399; padding:6px 14px; border-radius:8px; font-size:0.82rem; cursor:pointer; font-weight:700;">
                            💼 محاسب مالي
                        </button>
                        <button type="button" onclick="window.applyStudioPreset('storekeeper')" style="background:rgba(167,139,250,0.15); border:1px solid #a855f7; color:#c084fc; padding:6px 14px; border-radius:8px; font-size:0.82rem; cursor:pointer; font-weight:700;">
                            📦 أمين مخزن
                        </button>
                        <button type="button" onclick="window.selectAllStudioScreens(true)" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:6px 14px; border-radius:8px; font-size:0.82rem; cursor:pointer; margin-right:auto;">
                            ✅ تحديد الكل
                        </button>
                        <button type="button" onclick="window.selectAllStudioScreens(false)" style="background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#f87171; padding:6px 14px; border-radius:8px; font-size:0.82rem; cursor:pointer;">
                            ❌ إلغاء الكل
                        </button>
                    </div>
                </div>

                <!-- Admin Notice Box (Shown when Admin is selected) -->
                <div id="studio-admin-notice" style="display:${isUserAdmin ? 'block' : 'none'}; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.4); border-radius:14px; padding:25px; text-align:center; margin-bottom:20px;">
                    <div style="font-size:2.2rem; color:#f59e0b; margin-bottom:10px;">👑</div>
                    <h4 style="margin:0 0 8px 0; color:#fbbf24; font-size:1.2rem; font-weight:800;">حساب مدير نظام (Super Admin)</h4>
                    <p style="margin:0; color:#cbd5e1; font-size:0.95rem; max-width:600px; margin:0 auto;">
                        يمتلك هذا الحساب تلقائياً صلاحيات كاملة ومطلقة لجميع شاشات النظام، والأزرار التشغيلية (حفظ، جديد، تعديل، حذف، طباعة، مشاركة واتساب)، وكافة التقارير المالية والإدارية.
                    </p>
                </div>

                <!-- 2. Screens & Action Buttons Control Container -->
                <div id="studio-custom-permissions-container" style="display:${isUserAdmin ? 'none' : 'block'};">
                    
                    <!-- Search Input -->
                    <div style="margin-bottom:15px; position:relative;">
                        <input type="text" id="studio-screen-search" placeholder="🔍 ابحث عن شاشة (مثال: مشتريات، مبيعات، كشف حساب، سند صرف...)" oninput="window.filterStudioScreens(this.value)" style="width:100%; padding:12px 20px; border-radius:12px; background:rgba(15,23,42,0.7); border:1px solid rgba(56,189,248,0.3); color:#fff; font-size:0.95rem; outline:none; box-sizing:border-box;">
                    </div>

                    <!-- Categories & Screens Grid -->
                    <div style="display:flex; flex-direction:column; gap:20px;" id="studio-categories-list">
        `;

        // Render each category
        SYSTEM_SCREENS_CATALOG.forEach(cat => {
            html += `
                <div class="studio-cat-card" data-cat-id="${cat.category}" style="background:rgba(17,24,39,0.5); border:1px solid rgba(255,255,255,0.08); border-radius:16px; overflow:hidden;">
                    
                    <!-- Category Header -->
                    <div style="background:rgba(0,0,0,0.25); padding:14px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.08);">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span style="width:36px; height:36px; border-radius:10px; background:${cat.color}22; border:1px solid ${cat.color}55; color:${cat.color}; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
                                <i class="fa-solid ${cat.categoryIcon}"></i>
                            </span>
                            <h4 style="margin:0; color:#fff; font-size:1.1rem; font-weight:800;">${cat.categoryName}</h4>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <button type="button" onclick="window.toggleCatScreens('${cat.category}', true)" style="background:transparent; border:none; color:#38bdf8; font-size:0.8rem; cursor:pointer; font-weight:700;">تفعيل القسم</button>
                            <span style="color:#64748b;">|</span>
                            <button type="button" onclick="window.toggleCatScreens('${cat.category}', false)" style="background:transparent; border:none; color:#f87171; font-size:0.8rem; cursor:pointer; font-weight:700;">تعطيل القسم</button>
                        </div>
                    </div>

                    <!-- Screens in this category -->
                    <div style="padding:15px; display:flex; flex-direction:column; gap:12px;">
            `;

            cat.screens.forEach(screen => {
                const isScreenAllowed = allowedScreens[screen.file] !== false; // default true if not set or user has it
                const screenBtns = screenButtons[screen.file] || {};

                html += `
                    <div class="studio-screen-row" data-screen-file="${screen.file}" data-screen-title="${screen.title}" style="background:rgba(15,23,42,0.4); border:1px solid ${isScreenAllowed ? 'rgba(56,189,248,0.3)' : 'rgba(255,255,255,0.05)'}; border-radius:12px; padding:12px 16px; display:flex; flex-direction:column; gap:10px; transition:all 0.2s;">
                        
                        <!-- Screen Toggle & Title -->
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <!-- Switch for Screen Visibility -->
                                <label class="studio-toggle-switch">
                                    <input type="checkbox" class="screen-visibility-cb" data-file="${screen.file}" ${isScreenAllowed ? 'checked' : ''} onchange="window.onStudioScreenVisibilityChange('${screen.file}', this.checked)">
                                    <span class="studio-slider"></span>
                                </label>
                                
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <i class="fa-solid ${screen.icon}" style="color:${cat.color}; font-size:0.95rem;"></i>
                                    <span style="font-weight:700; color:#f8fafc; font-size:0.95rem;">${screen.title}</span>
                                    <span style="font-size:0.75rem; color:#64748b; direction:ltr;">(${screen.file})</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <span class="screen-status-badge" id="badge-${screen.file.replace('.', '_')}" style="padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; ${isScreenAllowed ? 'background:rgba(16,185,129,0.15); color:#34d399; border:1px solid #10b981;' : 'background:rgba(239,68,68,0.15); color:#f87171; border:1px solid #ef4444;'}">
                                ${isScreenAllowed ? 'ظاهرة للمستخدم' : 'مخفية ومحظورة'}
                            </span>
                        </div>

                        <!-- Action Buttons Controls (Only relevant if screen has actions) -->
                        <div class="screen-actions-panel" id="actions-${screen.file.replace('.', '_')}" style="display:${isScreenAllowed ? 'flex' : 'none'}; align-items:center; gap:12px; flex-wrap:wrap; background:rgba(0,0,0,0.25); padding:8px 12px; border-radius:8px; border:1px dashed rgba(255,255,255,0.08);">
                            <span style="font-size:0.78rem; color:#94a3b8; font-weight:700; margin-left:5px;">صلاحيات الأزرار:</span>
                `;

                screen.supportedActions.forEach(act => {
                    const actMeta = ACTION_META[act] || { label: act, icon: 'fa-check', color: '#fff' };
                    // Default to true if screen is allowed unless explicitly false
                    const isBtnAllowed = screenBtns[act] !== false;

                    html += `
                        <label style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.04); padding:4px 8px; border-radius:6px; font-size:0.8rem; cursor:pointer; user-select:none; border:1px solid rgba(255,255,255,0.06);">
                            <input type="checkbox" class="screen-btn-cb" data-file="${screen.file}" data-action="${act}" ${isBtnAllowed ? 'checked' : ''} style="accent-color:${actMeta.color};">
                            <i class="fa-solid ${actMeta.icon}" style="color:${actMeta.color}; font-size:0.75rem;"></i>
                            <span style="color:#e2e8f0;">${actMeta.label}</span>
                        </label>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        // 3. Granular Reports Section
        html += `
                    </div> <!-- End categories list -->

                    <!-- Reports Visibility Control Section -->
                    <div style="background:rgba(17,24,39,0.5); border:1px solid rgba(244,114,182,0.25); border-radius:16px; margin-top:20px; overflow:hidden;">
                        <div style="background:rgba(0,0,0,0.25); padding:14px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.08);">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span style="width:36px; height:36px; border-radius:10px; background:rgba(244,114,182,0.15); border:1px solid #f472b6; color:#f472b6; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
                                    <i class="fa-solid fa-file-lines"></i>
                                </span>
                                <div>
                                    <h4 style="margin:0; color:#fff; font-size:1.1rem; font-weight:800;">التقارير المسموح برؤيتها لهذا المستخدم</h4>
                                    <p style="margin:2px 0 0 0; font-size:0.8rem; color:#94a3b8;">حدد بالضبط أي التقارير المالية والإدارية يحق لهذا المستخدم الاطلاع عليها وطباعتها</p>
                                </div>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button type="button" onclick="window.toggleAllReports(true)" style="background:transparent; border:none; color:#38bdf8; font-size:0.8rem; cursor:pointer; font-weight:700;">تحديد كل التقارير</button>
                                <span style="color:#64748b;">|</span>
                                <button type="button" onclick="window.toggleAllReports(false)" style="background:transparent; border:none; color:#f87171; font-size:0.8rem; cursor:pointer; font-weight:700;">إلغاء كل التقارير</button>
                            </div>
                        </div>

                        <div style="padding:15px; display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px;">
        `;

        SYSTEM_REPORTS_CATALOG.forEach(rep => {
            const isRepAllowed = allowedReports[rep.id] !== false; // default true
            html += `
                <label style="display:flex; align-items:center; gap:10px; background:rgba(15,23,42,0.4); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:10px 14px; cursor:pointer; transition:all 0.2s;">
                    <input type="checkbox" class="report-perm-cb" data-report-id="${rep.id}" ${isRepAllowed ? 'checked' : ''} style="width:17px; height:17px; accent-color:#f472b6;">
                    <i class="fa-solid ${rep.icon}" style="color:#f472b6; font-size:0.9rem;"></i>
                    <span style="color:#e2e8f0; font-size:0.88rem; font-weight:700;">${rep.title}</span>
                </label>
            `;
        });

        html += `
                        </div>
                    </div>

                </div> <!-- End studio-custom-permissions-container -->

            </div> <!-- End perm-studio-wrap -->

            <!-- Studio Styles -->
            <style>
                .active-role-admin { background: rgba(245,158,11,0.2) !important; color: #fbbf24 !important; border: 1px solid #f59e0b !important; }
                .active-role-user { background: rgba(56,189,248,0.2) !important; color: #38bdf8 !important; border: 1px solid #38bdf8 !important; }
                
                .studio-toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
                .studio-toggle-switch input { opacity: 0; width: 0; height: 0; }
                .studio-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(239, 68, 68, 0.4); transition: .3s; border-radius: 24px; border: 1px solid rgba(239, 68, 68, 0.6); }
                .studio-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.3); }
                .studio-toggle-switch input:checked + .studio-slider { background-color: #10b981; border-color: #10b981; }
                .studio-toggle-switch input:checked + .studio-slider:before { transform: translateX(20px); }
            </style>
        `;

        containerElement.innerHTML = html;
    }

    // Window Global Studio Handlers
    window.onStudioUserTypeChange = function(type) {
        const isAdmin = (type === 'admin');
        const adminNotice = document.getElementById('studio-admin-notice');
        const customContainer = document.getElementById('studio-custom-permissions-container');
        const presetsBar = document.getElementById('studio-presets-bar');
        const lblAdmin = document.getElementById('label-role-admin');
        const lblUser = document.getElementById('label-role-user');

        if (adminNotice) adminNotice.style.display = isAdmin ? 'block' : 'none';
        if (customContainer) customContainer.style.display = isAdmin ? 'none' : 'block';
        if (presetsBar) presetsBar.style.display = isAdmin ? 'none' : 'flex';

        if (lblAdmin) lblAdmin.className = isAdmin ? 'active-role-admin' : '';
        if (lblUser) lblUser.className = !isAdmin ? 'active-role-user' : '';
    };

    window.onStudioScreenVisibilityChange = function(fileName, isChecked) {
        const sanitized = fileName.replace('.', '_');
        const actionsPanel = document.getElementById(`actions-${sanitized}`);
        const badge = document.getElementById(`badge-${sanitized}`);

        if (actionsPanel) actionsPanel.style.display = isChecked ? 'flex' : 'none';
        if (badge) {
            badge.innerText = isChecked ? 'ظاهرة للمستخدم' : 'مخفية ومحظورة';
            badge.style.background = isChecked ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)';
            badge.style.color = isChecked ? '#34d399' : '#f87171';
            badge.style.borderColor = isChecked ? '#10b981' : '#ef4444';
        }
    };

    window.applyStudioPreset = function(presetKey) {
        const preset = PRESET_TEMPLATES[presetKey];
        if (!preset) return;

        // Apply screens
        document.querySelectorAll('.screen-visibility-cb').forEach(cb => {
            const file = cb.dataset.file;
            const allowed = !!(preset.allowedScreens && preset.allowedScreens[file]);
            cb.checked = allowed;
            window.onStudioScreenVisibilityChange(file, allowed);
        });

        // Apply buttons
        document.querySelectorAll('.screen-btn-cb').forEach(cb => {
            const file = cb.dataset.file;
            const act = cb.dataset.action;
            let allowed = false;
            if (preset.screenButtons && preset.screenButtons[file] && typeof preset.screenButtons[file][act] === 'boolean') {
                allowed = preset.screenButtons[file][act];
            } else if (preset.allowedScreens && preset.allowedScreens[file]) {
                // If screen is allowed, default basic actions
                allowed = (act === 'save' || act === 'print' || act === 'new');
            }
            cb.checked = allowed;
        });

        // Apply reports
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            const repId = cb.dataset.reportId;
            cb.checked = !!(preset.allowedReports && preset.allowedReports[repId]);
        });

        if (typeof showToast === 'function') {
            showToast(`تم تطبيق نموذج: ${preset.title} بنجاح`, 'success');
        }
    };

    window.selectAllStudioScreens = function(selectAll) {
        document.querySelectorAll('.screen-visibility-cb').forEach(cb => {
            cb.checked = selectAll;
            window.onStudioScreenVisibilityChange(cb.dataset.file, selectAll);
        });
        document.querySelectorAll('.screen-btn-cb').forEach(cb => {
            cb.checked = selectAll;
        });
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleCatScreens = function(catId, selectAll) {
        const card = document.querySelector(`.studio-cat-card[data-cat-id="${catId}"]`);
        if (!card) return;
        card.querySelectorAll('.screen-visibility-cb').forEach(cb => {
            cb.checked = selectAll;
            window.onStudioScreenVisibilityChange(cb.dataset.file, selectAll);
        });
        card.querySelectorAll('.screen-btn-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.toggleAllReports = function(selectAll) {
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            cb.checked = selectAll;
        });
    };

    window.filterStudioScreens = function(query) {
        const q = (query || '').trim().toLowerCase();
        document.querySelectorAll('.studio-screen-row').forEach(row => {
            const title = (row.dataset.screenTitle || '').toLowerCase();
            const file = (row.dataset.screenFile || '').toLowerCase();
            if (!q || title.includes(q) || file.includes(q)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    };

    // Helper: Collect All Values from Studio Form
    function collectStudioPermissionsData() {
        const userTypeRadio = document.querySelector('input[name="studio_user_type"]:checked');
        const userType = userTypeRadio ? userTypeRadio.value : 'user';

        if (userType === 'admin') {
            return {
                userType: 'admin',
                allowedScreens: {},
                screenButtons: {},
                allowedReports: {}
            };
        }

        const allowedScreens = {};
        document.querySelectorAll('.screen-visibility-cb').forEach(cb => {
            allowedScreens[cb.dataset.file] = cb.checked;
        });

        const screenButtons = {};
        document.querySelectorAll('.screen-btn-cb').forEach(cb => {
            const file = cb.dataset.file;
            const act = cb.dataset.action;
            if (!screenButtons[file]) screenButtons[file] = {};
            screenButtons[file][act] = cb.checked;
        });

        const allowedReports = {};
        document.querySelectorAll('.report-perm-cb').forEach(cb => {
            allowedReports[cb.dataset.reportId] = cb.checked;
        });

        return {
            userType: 'user',
            allowedScreens,
            screenButtons,
            allowedReports
        };
    }

    // Export global API
    window.MagaieesPermissions = {
        SYSTEM_SCREENS_CATALOG,
        SYSTEM_REPORTS_CATALOG,
        PRESET_TEMPLATES,
        getCurrentUser,
        isCurrentUserAdmin,
        canUserAccessScreen,
        canUserDoAction,
        canUserViewReport,
        enforceScreenSecurity,
        applyScreenButtonSecurity,
        renderPermissionsStudioUI,
        collectStudioPermissionsData
    };

})(window);
