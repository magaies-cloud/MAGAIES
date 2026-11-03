/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 🌟 MAGAIEES COMPACT SIDEBAR SHORTCUTS FLYOUT ENGINE (Theme-Adaptive v4)
 * قائمة الاختصارات الجانبية المهذبة والمتكيفة مع جميع سمات النظام السبعة
 * ═══════════════════════════════════════════════════════════════════════════
 */
(function () {
    'use strict';

    if (window.__MagaieesPanoramicSidebarLoaded) return;
    window.__MagaieesPanoramicSidebarLoaded = true;

    // ── Master Configuration of Compact Sub-Menus for Every Sidebar Item ──
    const PANORAMIC_MENUS = {
        'magaiees_unified_dashboard.php': {
            title: 'لوحة التحكم',
            icon: '🎛️',
            accent: '#22d3ee',
            accentRgb: '34, 211, 238',
            items: [
                {
                    title: 'لوحة التحكم التنفيذية الشاملة',
                    icon: '🎛️',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'magaiees_unified_dashboard.php'
                },
                {
                    title: 'الحارس والمستشار الذكي الشامل 360°',
                    icon: '🛡️',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'ai_advisor.php'
                },
                {
                    title: 'التنبؤ المالي الذكي (AI)',
                    icon: '🔮',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'sales_forecasting.php'
                },
                {
                    title: 'إدارة المهام والأعمال التنفيذية 360°',
                    icon: '💼',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'jobs.php'
                },
                {
                    title: 'سجل الرقابة والتدقيق الجنائي 360°',
                    icon: '🕵️‍♂️',
                    color: '#f87171',
                    rgb: '248, 113, 113',
                    href: 'audit-log.php'
                },
                {
                    title: 'بورصة ومحول سعر الصرف والعملات',
                    icon: '💱',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'magaiees_unified_dashboard.php?modal=fx',
                    samePageAction: () => { if (typeof window.openDashboardFXModal === 'function') window.openDashboardFXModal(); }
                }
            ]
        },

        'direct-sales.php': {
            title: 'المبيعات',
            icon: '🛒',
            accent: '#34d399',
            accentRgb: '52, 211, 153',
            items: [
                {
                    title: 'فاتورة مبيعات',
                    icon: '➕',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'direct-sales.php?action=new',
                    samePageAction: () => { if (typeof window.openInvoiceEditor === 'function') window.openInvoiceEditor('pending_delivery'); }
                },
                {
                    title: 'بوابة تسليم المبيعات',
                    icon: '📤',
                    color: '#10b981',
                    rgb: '16, 185, 129',
                    href: 'sales-delivery.php'
                },
                {
                    title: 'فاتورة تقسيم وتسليم المبيعات',
                    icon: '✂️',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'direct-sales.php?action=split',
                    samePageAction: () => { if (typeof window.openDeliveryHub === 'function') window.openDeliveryHub(); }
                },
                {
                    title: 'فاتورة مبدئية',
                    icon: '📋',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'direct-sales.php?action=preliminary',
                    samePageAction: () => { if (typeof window.openInvoiceEditor === 'function') window.openInvoiceEditor('preliminary'); }
                },
                {
                    title: 'مرتجع المبيعات واسترداد البضائع',
                    icon: '↩️',
                    color: '#f43f5e',
                    rgb: '244, 63, 94',
                    href: 'sales-invoice.php'
                },
                {
                    title: 'حجوزات المبيعات',
                    icon: '🔖',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'reserved-sales.php'
                }
            ]
        },

        'purchase.php': {
            title: 'المشتريات',
            icon: '🛍️',
            accent: '#fb923c',
            accentRgb: '251, 146, 60',
            items: [
                {
                    title: 'فاتورة مشتريات',
                    icon: '💵',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'purchase.php?action=direct',
                    samePageAction: () => { if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('direct'); }
                },
                {
                    title: 'بوابة استلام المشتريات',
                    icon: '📥',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'smart-logistics.php?mode=purchases'
                },
                {
                    title: 'استلام بضاعة',
                    icon: '🚛',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'purchase-receipt.php'
                },
                {
                    title: 'مرتجع المشتريات واسترداد البضائع',
                    icon: '↩️',
                    color: '#f43f5e',
                    rgb: '244, 63, 94',
                    href: 'purchase-return.php'
                },
                {
                    title: 'اعتمادية وموثوقية الموردين 360°',
                    icon: '🤝',
                    color: '#a855f7',
                    rgb: '168, 85, 247',
                    href: 'supplier_reliability.php'
                },
                {
                    title: 'فاتورة مبدئية',
                    icon: '📋',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'purchase.php?action=proforma',
                    samePageAction: () => { if (typeof window.openPurchaseEditor === 'function') window.openPurchaseEditor('proforma'); }
                },
                {
                    title: 'مسح فاتورة مشتريات',
                    icon: '📷',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'purchase.php?action=ocr',
                    samePageAction: () => { if (typeof window.triggerInvoiceOCR === 'function') window.triggerInvoiceOCR(); }
                }
            ]
        },

        'vouchers-hub.php': {
            title: 'المعاملات المالية',
            icon: '💰',
            accent: '#fbbf24',
            accentRgb: '251, 191, 36',
            items: [
                {
                    title: 'حركة مالية',
                    icon: '💸',
                    color: '#f43f5e',
                    rgb: '244, 63, 94',
                    href: 'single-cash-payment.php'
                },
                {
                    title: 'صرف وقبض مجمع',
                    icon: '🧾',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'multi-cash-payment.php'
                },
                {
                    title: 'قيد يومية',
                    icon: '📝',
                    color: '#a855f7',
                    rgb: '168, 85, 247',
                    href: 'journal-voucher.php'
                },
                {
                    title: 'دورة الشيكات',
                    icon: '💳',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'checks-cycle.php'
                },
                {
                    title: 'ترحيل الحسابات',
                    icon: '🔄',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'posting-accounts.php'
                },
                {
                    title: 'مستشار AI للأموال والسيولة',
                    icon: '💰',
                    color: '#10b981',
                    rgb: '16, 185, 129',
                    href: 'ai_money_advisor.php'
                }
            ]
        },

        'inventory-report.php': {
            title: 'المخازن',
            icon: '📦',
            accent: '#a78bfa',
            accentRgb: '167, 139, 250',
            items: [
                {
                    title: 'حركات البضائع',
                    icon: '📦',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'inventory-movement.php'
                },
                {
                    title: 'حركة الأصناف',
                    icon: '🏷️',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'item-ledger.php'
                },
                {
                    title: 'حركة المخازن',
                    icon: '🏢',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'warehouse-ledger.php'
                },
                {
                    title: 'ملخص الحركات',
                    icon: '📊',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'inventory-summary.php'
                },
                {
                    title: 'التحليلات المرئية',
                    icon: '📈',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'inventory-analytics.php'
                },
                {
                    title: 'الجرد الشامل',
                    icon: '🔍',
                    color: '#f87171',
                    rgb: '248, 113, 113',
                    href: 'inventory-stocktake.php'
                },
                {
                    title: 'تسوية جردية',
                    icon: '⚖️',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'inventory-adjustment.php'
                },
                {
                    title: 'تقارير المخزن الذكية',
                    icon: '🤖',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'smart_inventory_report.php'
                },
                {
                    title: 'مستشار AI للبضائع والمخازن',
                    icon: '📦',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'ai_goods_advisor.php'
                }
            ]
        },

        'analytics.php': {
            title: 'تقارير مالية',
            icon: '📊',
            accent: '#38bdf8',
            accentRgb: '56, 189, 248',
            items: [
                {
                    title: 'دفتر الأستاذ العام',
                    icon: '📓',
                    color: '#a855f7',
                    rgb: '168, 85, 247',
                    href: 'general-ledger.php'
                },
                {
                    title: 'حساب الأرصدة الأساسية',
                    icon: '🧮',
                    color: '#10b981',
                    rgb: '16, 185, 129',
                    href: 'mainbalances-report.php'
                },
                {
                    title: 'تقرير الحسابات العامة',
                    icon: '📂',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'general_accounts_hub.php'
                },
                {
                    title: 'القوائم الختامية والمحاسبية',
                    icon: '🏛️',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'financial-reports.php'
                },
                {
                    title: 'كشف حساب تفصيلي',
                    icon: '📑',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'account-statement.php'
                },
                {
                    title: 'حساب مديونية الأرصدة الشامل',
                    icon: '⚖️',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'combined_receivables.php'
                },
                {
                    title: 'أعمار الديون والحقوق وسقوف الائتمان',
                    icon: '⏳',
                    color: '#f87171',
                    rgb: '248, 113, 113',
                    href: 'aging-report.php'
                },
                {
                    title: 'مركز الأرصدة ومقاصة الحسابات',
                    icon: '🔄',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'balances_modern_hub.php'
                },
                {
                    title: 'التقرير اليومي الشامل',
                    icon: '📅',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'daily_master_report.php'
                },
                {
                    title: 'تقارير العملاء والموردين',
                    icon: '🤝',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'partners_reports_hub.php'
                },
                {
                    title: 'بوابة التقارير الذكية (IAS 7)',
                    icon: '🚀',
                    color: '#60a5fa',
                    rgb: '96, 165, 250',
                    href: 'smart_reports_hub.php'
                }
            ]
        },
        'master-data.php': {
            title: 'البيانات الأساسية',
            icon: '🏛️',
            accent: '#f472b6',
            accentRgb: '244, 114, 182',
            items: [
                {
                    title: 'الخزن والنقدية',
                    icon: '🏦',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'master-data.php?tab=safes',
                    samePageTab: 'tab-safes'
                },
                {
                    title: 'البنوك والمحافظ',
                    icon: '🏛️',
                    color: '#60a5fa',
                    rgb: '96, 165, 250',
                    href: 'master-data.php?tab=banks',
                    samePageTab: 'tab-banks'
                },
                {
                    title: 'العملاء',
                    icon: '👥',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'master-data.php?tab=customers',
                    samePageTab: 'tab-customers'
                },
                {
                    title: 'الموردين',
                    icon: '🤝',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'master-data.php?tab=suppliers',
                    samePageTab: 'tab-suppliers'
                },
                {
                    title: 'المخازن والمستودعات',
                    icon: '🏢',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'master-data.php?tab=warehouses',
                    samePageTab: 'tab-warehouses'
                },
                {
                    title: 'الأصناف والمنتجات',
                    icon: '📦',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'master-data.php?tab=items',
                    samePageTab: 'tab-items'
                },
                {
                    title: 'الموظفين',
                    icon: '👨‍💼',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'master-data.php?tab=employees',
                    samePageTab: 'tab-employees'
                },
                {
                    title: 'مركز التكلفة',
                    icon: '🎯',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'master-data.php?tab=costcenters',
                    samePageTab: 'tab-costcenters'
                }
            ]
        },

        'alarm.php': {
            title: 'التنبيهات',
            icon: '🔔',
            accent: '#f87171',
            accentRgb: '248, 113, 113',
            items: [
                {
                    title: 'الإنذارات الحية النشطة',
                    icon: '🚨',
                    color: '#f87171',
                    rgb: '248, 113, 113',
                    href: 'alarm.php?pillar=LIVE',
                    samePageAction: () => { if (typeof window.filterAlarmPillarByCode === 'function') return window.filterAlarmPillarByCode('LIVE'); return false; }
                },
                {
                    title: 'مصفوفة التنبيهات (88 قاعدة)',
                    icon: '🔔',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'alarm.php?pillar=ALL',
                    samePageAction: () => { if (typeof window.filterAlarmPillarByCode === 'function') return window.filterAlarmPillarByCode('ALL'); return false; }
                },
                {
                    title: 'تنبيهات المبيعات والأسعار',
                    icon: '🛒',
                    color: '#22d3ee',
                    rgb: '34, 211, 238',
                    href: 'alarm.php?pillar=SALES',
                    samePageAction: () => { if (typeof window.filterAlarmPillarByCode === 'function') return window.filterAlarmPillarByCode('SALES'); return false; }
                },
                {
                    title: 'تنبيهات المخازن والرواكد',
                    icon: '📦',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'alarm.php?pillar=INVENTORY',
                    samePageAction: () => { if (typeof window.filterAlarmPillarByCode === 'function') return window.filterAlarmPillarByCode('INVENTORY'); return false; }
                },
                {
                    title: 'تنبيهات الخزينة والسيولة',
                    icon: '💰',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'alarm.php?pillar=FINANCE',
                    samePageAction: () => { if (typeof window.filterAlarmPillarByCode === 'function') return window.filterAlarmPillarByCode('FINANCE'); return false; }
                },
                {
                    title: 'تنبيهات المشتريات والمطابقة',
                    icon: '🚛',
                    color: '#fb923c',
                    rgb: '251, 146, 60',
                    href: 'alarm.php?pillar=PURCHASE',
                    samePageAction: () => { if (typeof window.filterAlarmPillarByCode === 'function') return window.filterAlarmPillarByCode('PURCHASE'); return false; }
                },
                {
                    title: 'تنبيهات ائتمان العملاء',
                    icon: '👥',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'alarm.php?pillar=CREDIT',
                    samePageAction: () => { if (typeof window.filterAlarmPillarByCode === 'function') return window.filterAlarmPillarByCode('CREDIT'); return false; }
                },
                {
                    title: 'تنبيهات الأمان والرقابة',
                    icon: '🛡️',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'alarm.php?pillar=SECURITY',
                    samePageAction: () => { if (typeof window.filterAlarmPillarByCode === 'function') return window.filterAlarmPillarByCode('SECURITY'); return false; }
                },
                {
                    title: 'التنبؤ بالمخاطر والذكاء الاصطناعي',
                    icon: '🤖',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'alarm.php?pillar=AI',
                    samePageAction: () => { if (typeof window.openAlarmAIPredictiveCenter === 'function') { window.openAlarmAIPredictiveCenter(); return true; } return false; }
                }
            ]
        },

        'engineering_tools.php': {
            title: 'أدوات مساعدة',
            icon: '🤖',
            accent: '#ec4899',
            accentRgb: '236, 72, 153',
            items: [
                {
                    title: 'المساعد الذكي التفاعلي',
                    icon: '🤖',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'engineering_tools.php?action=chatbot',
                    samePageAction: () => {
                        const chatToggle = document.getElementById('chatbot-toggle-btn') || document.querySelector('.chatbot-fab, #ai-assistant-btn, [onclick*="toggleChatbot"]');
                        if (chatToggle) { chatToggle.click(); return true; }
                        return false;
                    }
                },
                {
                    title: 'استوديو القرآن الكريم MP3',
                    icon: '🎧',
                    color: '#06b6d4',
                    rgb: '6, 182, 212',
                    href: 'engineering_tools.php?tab=quran_mp3',
                    samePageAction: () => {
                        if (typeof window.openQuranMp3Studio === 'function') { window.openQuranMp3Studio(); return true; }
                        return false;
                    }
                },
                {
                    title: 'البث الحي للقنوات والإذاعات',
                    icon: '📺',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'engineering_tools.php?tab=tv_makkah',
                    samePageAction: () => {
                        if (typeof window.switchSuite === 'function') { window.switchSuite('tv_makkah', null); return true; }
                        return false;
                    }
                },
                {
                    title: 'الدعم النفسي والذهني والترفيهي',
                    icon: '🧠',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'engineering_tools.php?tab=wellness',
                    samePageAction: () => {
                        if (typeof window.switchSuite === 'function') { window.switchSuite('wellness', document.getElementById('tab-pill-wellness')); return true; }
                        return false;
                    }
                },
                {
                    title: 'حاسبة الميراث والزكاة',
                    icon: '⚖️',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'engineering_tools.php?tab=inheritance',
                    samePageAction: () => {
                        if (typeof window.switchSuite === 'function') { window.switchSuite('inheritance', null); return true; }
                        return false;
                    }
                },
                {
                    title: 'الموسوعة الفقهية والفتاوى',
                    icon: '📜',
                    color: '#a78bfa',
                    rgb: '167, 139, 250',
                    href: 'engineering_tools.php?tab=fiqh',
                    samePageAction: () => {
                        if (typeof window.switchSuite === 'function') { window.switchSuite('fiqh', null); return true; }
                        return false;
                    }
                },
                {
                    title: 'الأدوات الهندسية والحاسبات',
                    icon: '🧰',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'engineering_tools.php',
                    samePageAction: () => {
                        if (typeof window.switchSuite === 'function') { window.switchSuite('steel', null); return true; }
                        return false;
                    }
                }
            ]
        },

        'settings.php': {
            title: 'الإعدادات',
            icon: '⚙️',
            accent: '#a3e635',
            accentRgb: '163, 230, 53',
            items: [
                {
                    title: 'إعدادات النظام العامة',
                    icon: '⚙️',
                    color: '#a3e635',
                    rgb: '163, 230, 53',
                    href: 'settings.php'
                },
                {
                    title: 'الخطوط وحجم العرض',
                    icon: '🔤',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'settings.php?tab=fonts',
                    samePageAction: () => {
                        if (typeof window.switchTab === 'function' && document.getElementById('tab-fonts')) {
                            window.switchTab('fonts');
                            return true;
                        }
                        return false;
                    }
                },
                {
                    title: 'المستخدمون والصلاحيات',
                    icon: '🔐',
                    color: '#fbbf24',
                    rgb: '251, 191, 36',
                    href: 'users-roles.php'
                },
                {
                    title: 'السمات السبعة المعتمدة',
                    icon: '🎨',
                    color: '#f472b6',
                    rgb: '244, 114, 182',
                    href: 'settings.php?action=themes',
                    samePageAction: () => {
                        if (typeof window.openMaster7ThemesModal === 'function') {
                            window.openMaster7ThemesModal();
                            return true;
                        }
                        return false;
                    }
                },
                {
                    title: 'النسخ الاحتياطي والاستعادة',
                    icon: '💾',
                    color: '#38bdf8',
                    rgb: '56, 189, 248',
                    href: 'backup.php'
                },
                {
                    title: 'سجل الرقابة والتدقيق وتتبع العمليات',
                    icon: '🛡️',
                    color: '#34d399',
                    rgb: '52, 211, 153',
                    href: 'audit-log.php'
                },
                {
                    title: 'تصميم وطباعة الباركود',
                    icon: '🖨️',
                    color: '#c084fc',
                    rgb: '192, 132, 252',
                    href: 'barcode-printer.php'
                }
            ]
        }
    };

    // Alias dashboard-pro.php to unified dashboard
    PANORAMIC_MENUS['dashboard-pro.php'] = PANORAMIC_MENUS['magaiees_unified_dashboard.php'];

    // ── Inject Compact, Theme-Adaptive CSS ──
    function injectPanoramicStyles() {
        if (document.getElementById('magaiees-panoramic-sidebar-css')) return;
        const style = document.createElement('style');
        style.id = 'magaiees-panoramic-sidebar-css';
        style.textContent = `
            /* ── Keep Sidebar Expanded While Flyout is Open ── */
            .global-sidebar.panoramic-flyout-open {
                width: var(--sb-expanded, 240px) !important;
                border-left-color: rgba(56, 189, 248, 0.55) !important;
            }
            .global-sidebar.panoramic-flyout-open .sidebar-logo-text,
            .global-sidebar.panoramic-flyout-open .sidebar-text {
                max-width: 180px !important;
                opacity: 1 !important;
            }

            /* ── Full Theme Adaptation for Sidebar & Flyout Across All 7 Themes ── */
            html[data-theme] .global-sidebar {
                background: var(--bg-secondary, var(--bg-color, #0f172a)) !important;
                border-left: 1.5px solid var(--border, rgba(255, 255, 255, 0.12)) !important;
            }
            html[data-theme="warm_cream"] .global-sidebar,
            html[data-theme="warm-alabaster"] .global-sidebar,
            html[data-theme="beige"] .global-sidebar {
                background: #fbf7ee !important;
                border-left: 2px solid #d6c7b2 !important;
                box-shadow: -4px 0 22px rgba(28, 25, 23, 0.08) !important;
            }
            html[data-theme="ultra_white"] .global-sidebar,
            html[data-theme="white"] .global-sidebar {
                background: #ffffff !important;
                border-left: 2px solid #cbd5e1 !important;
                box-shadow: -4px 0 22px rgba(15, 23, 42, 0.06) !important;
            }
            html[data-theme="exec_gray"] .global-sidebar {
                background: #f1f5f9 !important;
                border-left: 2px solid #cbd5e1 !important;
            }

            /* ── Sidebar Item Hover & Active Enhancement ── */
            .global-sidebar .sidebar-item {
                position: relative;
                transition: background 0.22s ease, color 0.22s ease !important;
            }
            .global-sidebar .sidebar-item.panoramic-hovered {
                background: linear-gradient(90deg, rgba(var(--sb-hover-rgb, 56, 189, 248), 0.18) 0%, rgba(var(--sb-hover-rgb, 56, 189, 248), 0.05) 100%) !important;
                color: var(--text-primary, #ffffff) !important;
            }
            .global-sidebar .sidebar-item.panoramic-hovered::after {
                content: '';
                position: absolute;
                left: 0;
                top: 16%;
                height: 68%;
                width: 3.5px;
                border-radius: 0 5px 5px 0;
                background: var(--sb-hover-color, #38bdf8);
            }
            .global-sidebar .sidebar-item .sb-panoramic-chevron {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%) translateX(6px);
                font-size: 11px;
                color: var(--text-muted, rgba(255, 255, 255, 0.65));
                opacity: 0;
                transition: all 0.25s ease;
                pointer-events: none;
            }
            .global-sidebar:hover .sidebar-item .sb-panoramic-chevron,
            .global-sidebar.panoramic-flyout-open .sidebar-item .sb-panoramic-chevron {
                opacity: 0.7;
                transform: translateY(-50%) translateX(0);
            }
            .global-sidebar .sidebar-item.panoramic-hovered .sb-panoramic-chevron {
                opacity: 1 !important;
                color: var(--sb-hover-color, #38bdf8) !important;
                transform: translateY(-50%) translateX(-4px) scale(1.15) !important;
            }

            /* ═══════════════════════════════════════════════════════════════════
               🌟 COMPACT THEME-AWARE SHORTCUTS FLYOUT CONTAINER
               ═══════════════════════════════════════════════════════════════════ */
            #magaiees-panoramic-flyout {
                position: fixed;
                right: calc(var(--sb-expanded, 240px) + 10px);
                top: 18px;
                width: min(470px, calc(100vw - 260px));
                z-index: 999998;
                direction: rtl;
                font-family: 'Tajawal', 'Cairo', sans-serif;
                border-radius: 18px;
                padding: 12px 14px;
                box-sizing: border-box;
                background: var(--bg-color, #0f172a) !important;
                border: 1.8px solid var(--pf-accent, #38bdf8) !important;
                border-right: 4px solid var(--pf-accent, #38bdf8) !important;
                box-shadow:
                    -18px 20px 55px rgba(0, 0, 0, 0.38),
                    0 0 25px rgba(var(--pf-accent-rgb, 56, 189, 248), 0.16) !important;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateX(14px) scale(0.97);
                transform-origin: right center;
                transition:
                    opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.26s cubic-bezier(0.16, 1, 0.3, 1),
                    top 0.26s cubic-bezier(0.16, 1, 0.3, 1),
                    background 0.2s ease,
                    border-color 0.2s ease,
                    visibility 0.2s;
            }

            /* Explicit Light Theme Surfaces (Beige, White, Gray) */
            html[data-theme="warm_cream"] #magaiees-panoramic-flyout,
            html[data-theme="warm-alabaster"] #magaiees-panoramic-flyout,
            html[data-theme="beige"] #magaiees-panoramic-flyout {
                background: #fbf7ee !important;
                border-color: #c8b69e !important;
                border-right: 4px solid var(--pf-accent, #b45309) !important;
                box-shadow: -14px 18px 45px rgba(28, 25, 23, 0.16) !important;
            }
            html[data-theme="ultra_white"] #magaiees-panoramic-flyout,
            html[data-theme="white"] #magaiees-panoramic-flyout {
                background: #ffffff !important;
                border-color: #cbd5e1 !important;
                border-right: 4px solid var(--pf-accent, #2563eb) !important;
                box-shadow: -14px 18px 45px rgba(15, 23, 42, 0.13) !important;
            }
            html[data-theme="exec_gray"] #magaiees-panoramic-flyout {
                background: #f1f5f9 !important;
                border-color: #cbd5e1 !important;
                border-right: 4px solid var(--pf-accent, #1d4ed8) !important;
                box-shadow: -14px 18px 45px rgba(15, 23, 42, 0.14) !important;
            }

            #magaiees-panoramic-flyout.is-visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(0) scale(1);
            }

            /* Invisible Safe-Hover Bridge between Sidebar and Flyout */
            #magaiees-panoramic-flyout::before {
                content: '';
                position: absolute;
                top: -20px;
                bottom: -20px;
                right: -24px;
                width: 28px;
                background: transparent;
                pointer-events: auto;
            }

            /* Pointer Notch aligned with the hovered sidebar item */
            #magaiees-panoramic-flyout .pf-laser-pointer {
                position: absolute;
                right: -7px;
                top: var(--pf-pointer-y, 36px);
                width: 12px;
                height: 12px;
                background: var(--pf-accent, #38bdf8);
                border-radius: 3px;
                transform: translateY(-50%) rotate(45deg);
                transition: top 0.24s ease, background 0.2s ease;
                pointer-events: none;
                z-index: 2;
            }

            /* ── Compact Top Header (Small Icon + Short Title Only) ── */
            .pf-header {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 10px;
                margin-bottom: 10px;
                border-radius: 11px;
                background: var(--bg-secondary, rgba(255, 255, 255, 0.05)) !important;
                border: 1px solid var(--border, rgba(255, 255, 255, 0.12)) !important;
            }
            html[data-theme="warm_cream"] .pf-header,
            html[data-theme="warm-alabaster"] .pf-header,
            html[data-theme="beige"] .pf-header {
                background: #f5eedd !important;
                border-color: #dcd1be !important;
            }
            html[data-theme="ultra_white"] .pf-header,
            html[data-theme="white"] .pf-header {
                background: #f8fafc !important;
                border-color: #e2e8f0 !important;
            }
            html[data-theme="exec_gray"] .pf-header {
                background: #e2e8f0 !important;
                border-color: #cbd5e1 !important;
            }

            .pf-header-emblem {
                width: 28px;
                height: 28px;
                min-width: 28px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.98rem;
                background: rgba(var(--pf-accent-rgb, 56, 189, 248), 0.18);
                border: 1px solid var(--pf-accent, #38bdf8);
            }
            .pf-header-title {
                margin: 0;
                font-size: 0.92rem;
                font-weight: 800;
                color: var(--text-primary, #ffffff) !important;
                letter-spacing: 0.1px;
            }
            html.light-theme .pf-header-title {
                color: var(--text-primary, #0f172a) !important;
                text-shadow: none !important;
            }

            /* ── Compact 2-Column Shortcuts Grid ── */
            .pf-bento-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                max-height: calc(100vh - 110px);
                overflow-y: auto;
                padding: 2px;
            }
            .pf-bento-grid::-webkit-scrollbar {
                width: 4px;
            }
            .pf-bento-grid::-webkit-scrollbar-thumb {
                background: rgba(148, 163, 184, 0.35);
                border-radius: 4px;
            }

            /* ── Compact Shortcut Button Card (Theme-Adaptive, Small Font) ── */
            .pf-bento-card {
                position: relative;
                display: flex;
                align-items: center;
                gap: 9px;
                padding: 8px 11px;
                min-height: 44px;
                border-radius: 11px;
                text-decoration: none !important;
                background: var(--surface, rgba(20, 29, 52, 0.94)) !important;
                border: 1.5px solid var(--border, rgba(var(--card-rgb, 56, 189, 248), 0.32)) !important;
                border-bottom: 2.5px solid var(--card-color, #38bdf8) !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12) !important;
                cursor: pointer;
                overflow: hidden;
                transition:
                    transform 0.16s ease,
                    border-color 0.16s ease,
                    background 0.16s ease,
                    box-shadow 0.16s ease !important;
            }
            html[data-theme="warm_cream"] .pf-bento-card,
            html[data-theme="warm-alabaster"] .pf-bento-card,
            html[data-theme="beige"] .pf-bento-card {
                background: #ffffff !important;
                border: 1.5px solid #dcd1be !important;
                border-bottom: 2.5px solid var(--card-color, #b45309) !important;
                box-shadow: 0 2px 6px rgba(28, 25, 23, 0.06) !important;
            }
            html[data-theme="ultra_white"] .pf-bento-card,
            html[data-theme="white"] .pf-bento-card {
                background: #f8fafc !important;
                border: 1.5px solid #cbd5e1 !important;
                border-bottom: 2.5px solid var(--card-color, #2563eb) !important;
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05) !important;
            }
            html[data-theme="exec_gray"] .pf-bento-card {
                background: #ffffff !important;
                border: 1.5px solid #cbd5e1 !important;
                border-bottom: 2.5px solid var(--card-color, #1d4ed8) !important;
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05) !important;
            }

            .pf-bento-card:hover {
                transform: translateY(-2px) !important;
                background: var(--surface-hover, rgba(30, 41, 59, 0.98)) !important;
                border-color: var(--card-color, #38bdf8) !important;
                box-shadow: 0 5px 14px rgba(var(--card-rgb, 56, 189, 248), 0.24) !important;
            }
            html[data-theme="warm_cream"] .pf-bento-card:hover,
            html[data-theme="warm-alabaster"] .pf-bento-card:hover,
            html[data-theme="beige"] .pf-bento-card:hover {
                background: #f5eedd !important;
                border-color: var(--card-color, #b45309) !important;
            }
            html[data-theme="ultra_white"] .pf-bento-card:hover,
            html[data-theme="white"] .pf-bento-card:hover {
                background: #f1f5f9 !important;
                border-color: var(--card-color, #2563eb) !important;
            }
            html[data-theme="exec_gray"] .pf-bento-card:hover {
                background: #e2e8f0 !important;
                border-color: var(--card-color, #1d4ed8) !important;
            }

            /* Small Icon Box */
            .pf-card-icon-wrap {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .pf-card-icon-box {
                width: 30px;
                height: 30px;
                min-width: 30px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
                background: rgba(var(--card-rgb, 56, 189, 248), 0.15);
                border: 1px solid rgba(var(--card-rgb, 56, 189, 248), 0.45);
                color: var(--card-color, #38bdf8);
            }

            /* Card Title — Small, Clean Font matching Dashboard Buttons */
            .pf-card-body {
                min-width: 0;
                flex: 1;
                display: flex;
                align-items: center;
            }
            .pf-card-title {
                display: block;
                width: 100%;
                font-size: 0.84rem !important;
                font-weight: 800 !important;
                color: var(--text-primary, #ffffff) !important;
                line-height: 1.25;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                text-shadow: none !important;
            }
            html.light-theme .pf-card-title,
            html[data-theme="warm_cream"] .pf-card-title,
            html[data-theme="ultra_white"] .pf-card-title,
            html[data-theme="exec_gray"] .pf-card-title {
                color: var(--text-primary, #1c1917) !important;
                text-shadow: none !important;
            }

            @media print {
                #magaiees-panoramic-flyout { display: none !important; }
            }
            @media (max-width: 820px) {
                #magaiees-panoramic-flyout {
                    width: calc(100vw - 82px);
                    right: 70px;
                }
                .pf-bento-grid {
                    grid-template-columns: 1fr;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // ── Handle Same-Page Instant Navigation or Cross-Page Navigation ──
    function handleSubItemClick(e, itemConfig, parentHref) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        const currentPage = (window.location.pathname.split('/').pop() || '').toLowerCase();
        const targetPage = (parentHref || '').split('?')[0].toLowerCase();

        // 1. Check if it has a custom action (e.g., Themes Modal or Chatbot or Sales/Purchase Editor)
        if (typeof itemConfig.samePageAction === 'function') {
            if (currentPage === targetPage || itemConfig.href.includes('action=themes') || itemConfig.href.includes('action=chatbot')) {
                const handled = itemConfig.samePageAction();
                if (handled !== false) {
                    hidePanoramicFlyout(true);
                    return;
                }
            }
        }

        // 2. Check if we are already on master-data.php and switching tabs
        if (itemConfig.samePageTab && currentPage === 'master-data.php') {
            const targetBtn = document.querySelector(`button[onclick*="'${itemConfig.samePageTab}'"]`);
            if (targetBtn) {
                targetBtn.click();
                try {
                    const tabSlug = itemConfig.samePageTab.replace(/^tab-/, '');
                    window.history.replaceState(null, '', `master-data.php?tab=${tabSlug}`);
                } catch (err) {}
                hidePanoramicFlyout(true);
                return;
            }
        }

        // 3. Otherwise navigate to the target URL
        hidePanoramicFlyout(true);
        window.location.href = itemConfig.href;
    }

    // ── State Variables ──
    let flyoutEl = null;
    let activeSidebarLink = null;
    let activeHrefKey = null;
    let closeTimer = null;

    function ensureFlyoutDOM() {
        if (flyoutEl && document.body.contains(flyoutEl)) return flyoutEl;
        flyoutEl = document.createElement('div');
        flyoutEl.id = 'magaiees-panoramic-flyout';
        flyoutEl.setAttribute('role', 'menu');
        flyoutEl.setAttribute('aria-label', 'قائمة الاختصارات');
        flyoutEl.innerHTML = `
            <div class="pf-laser-pointer"></div>
            <div class="pf-header">
                <div class="pf-header-emblem" id="pf-emblem">🎛️</div>
                <h3 class="pf-header-title" id="pf-title">لوحة التحكم</h3>
            </div>
            <div class="pf-bento-grid" id="pf-grid"></div>
        `;

        // Keep flyout open when cursor is inside it
        flyoutEl.addEventListener('mouseenter', () => {
            clearTimeout(closeTimer);
            const sb = document.querySelector('.global-sidebar');
            if (sb) sb.classList.add('panoramic-flyout-open');
        });

        flyoutEl.addEventListener('mouseleave', (e) => {
            const sb = document.querySelector('.global-sidebar');
            if (sb && e.relatedTarget && sb.contains(e.relatedTarget)) {
                return;
            }
            scheduleCloseFlyout(150);
        });

        document.body.appendChild(flyoutEl);
        return flyoutEl;
    }

    function renderFlyoutForLink(linkEl) {
        const rawHref = (linkEl.getAttribute('data-href') || linkEl.getAttribute('href') || '').split('?')[0].trim();
        const menuConfig = PANORAMIC_MENUS[rawHref];
        if (!menuConfig) {
            hidePanoramicFlyout(false);
            return;
        }

        clearTimeout(closeTimer);
        const panel = ensureFlyoutDOM();
        const sb = document.querySelector('.global-sidebar');
        if (sb) sb.classList.add('panoramic-flyout-open');

        // Highlight active hovered sidebar item
        document.querySelectorAll('.global-sidebar .sidebar-item').forEach(el => {
            el.classList.remove('panoramic-hovered');
        });
        linkEl.style.setProperty('--sb-hover-color', menuConfig.accent);
        linkEl.style.setProperty('--sb-hover-rgb', menuConfig.accentRgb);
        linkEl.classList.add('panoramic-hovered');
        activeSidebarLink = linkEl;

        // Rebuild shortcut buttons when switching to a different sidebar category
        if (activeHrefKey !== rawHref) {
            activeHrefKey = rawHref;

            panel.style.setProperty('--pf-accent', menuConfig.accent);
            panel.style.setProperty('--pf-accent-rgb', menuConfig.accentRgb);

            document.getElementById('pf-emblem').textContent = menuConfig.icon;
            document.getElementById('pf-title').textContent = menuConfig.title;

            const grid = document.getElementById('pf-grid');
            grid.innerHTML = '';

            menuConfig.items.forEach((item, idx) => {
                const card = document.createElement('a');
                card.href = item.href;
                card.className = 'pf-bento-card';
                card.style.setProperty('--card-color', item.color || menuConfig.accent);
                card.style.setProperty('--card-rgb', item.rgb || menuConfig.accentRgb);
                card.style.setProperty('--card-idx', idx);

                card.innerHTML = `
                    <div class="pf-card-icon-wrap">
                        <div class="pf-card-icon-box">${item.icon}</div>
                    </div>
                    <div class="pf-card-body">
                        <span class="pf-card-title">${item.title}</span>
                    </div>
                `;

                card.addEventListener('click', (ev) => handleSubItemClick(ev, item, rawHref));
                grid.appendChild(card);
            });
        }

        // Show & Position smoothly relative to the hovered sidebar item
        panel.classList.add('is-visible');
        positionFlyoutNextToItem(linkEl, panel);
    }

    function positionFlyoutNextToItem(linkEl, panel) {
        const itemRect = linkEl.getBoundingClientRect();
        const itemCenterY = itemRect.top + itemRect.height / 2;
        const panelHeight = panel.offsetHeight || 280;
        const viewportH = window.innerHeight;

        let targetTop = itemCenterY - (panelHeight * 0.35);
        const minTop = 12;
        const maxTop = Math.max(minTop, viewportH - panelHeight - 12);
        targetTop = Math.max(minTop, Math.min(targetTop, maxTop));

        panel.style.top = `${Math.round(targetTop)}px`;

        const pointerY = Math.max(24, Math.min(panelHeight - 24, itemCenterY - targetTop));
        panel.style.setProperty('--pf-pointer-y', `${Math.round(pointerY)}px`);
    }

    function scheduleCloseFlyout(delayMs = 150) {
        clearTimeout(closeTimer);
        closeTimer = setTimeout(() => {
            hidePanoramicFlyout(false);
        }, delayMs);
    }

    function hidePanoramicFlyout(immediate = false) {
        clearTimeout(closeTimer);
        if (flyoutEl) {
            flyoutEl.classList.remove('is-visible');
        }
        const sb = document.querySelector('.global-sidebar');
        if (sb) {
            sb.classList.remove('panoramic-flyout-open');
        }
        document.querySelectorAll('.global-sidebar .sidebar-item.panoramic-hovered').forEach(el => {
            el.classList.remove('panoramic-hovered');
        });
        if (immediate) {
            activeHrefKey = null;
        }
        activeSidebarLink = null;
    }

    // ── Bind Events to Sidebar ──
    function attachSidebarListeners() {
        const sb = document.querySelector('.global-sidebar');
        if (!sb || sb.dataset.panoramicBound === '1') return;
        sb.dataset.panoramicBound = '1';

        injectPanoramicStyles();

        const items = sb.querySelectorAll('.sidebar-nav .sidebar-item');
        items.forEach(item => {
            const rawHref = (item.getAttribute('data-href') || item.getAttribute('href') || '').split('?')[0].trim();
            if (PANORAMIC_MENUS[rawHref] && !item.querySelector('.sb-panoramic-chevron')) {
                const chev = document.createElement('span');
                chev.className = 'sb-panoramic-chevron';
                chev.innerHTML = '❮';
                item.appendChild(chev);
            }

            item.addEventListener('mouseenter', () => {
                clearTimeout(closeTimer);
                renderFlyoutForLink(item);
            });
        });

        sb.addEventListener('mouseleave', (e) => {
            if (flyoutEl && e.relatedTarget && flyoutEl.contains(e.relatedTarget)) {
                return;
            }
            scheduleCloseFlyout(160);
        });

        // Close on Escape or outside click
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hidePanoramicFlyout(true);
        });
        document.addEventListener('click', (e) => {
            if (!flyoutEl || !flyoutEl.classList.contains('is-visible')) return;
            if (!flyoutEl.contains(e.target) && !sb.contains(e.target)) {
                hidePanoramicFlyout(true);
            }
        });
    }

    // ── Auto-Execute URL Actions on Target Screens (Sales / Purchase / Tools / Settings) ──
    function handleDeepLinkActionsOnLoad() {
        try {
            const params = new URLSearchParams(window.location.search);
            const action = params.get('action');
            if (!action) return;

            const currentPage = (window.location.pathname.split('/').pop() || '').toLowerCase();

            if (currentPage === 'direct-sales.php') {
                setTimeout(() => {
                    if (action === 'new' && typeof window.openInvoiceEditor === 'function') {
                        window.openInvoiceEditor('pending_delivery');
                    } else if (action === 'split' && typeof window.openSplitInvoiceView === 'function') {
                        window.openSplitInvoiceView();
                    } else if (action === 'delivery' && typeof window.openDeliveryHub === 'function') {
                        window.openDeliveryHub();
                    } else if (action === 'preliminary' && typeof window.openInvoiceEditor === 'function') {
                        window.openInvoiceEditor('preliminary');
                    }
                }, 120);
            } else if (currentPage === 'purchase.php') {
                setTimeout(() => {
                    if (action === 'direct' && typeof window.openPurchaseEditor === 'function') {
                        window.openPurchaseEditor('direct');
                    } else if (action === 'proforma' && typeof window.openPurchaseEditor === 'function') {
                        window.openPurchaseEditor('proforma');
                    } else if (action === 'ocr' && typeof window.triggerInvoiceOCR === 'function') {
                        window.triggerInvoiceOCR();
                    }
                }, 120);
            } else if (currentPage === 'settings.php' && action === 'themes') {
                setTimeout(() => {
                    if (typeof window.openMaster7ThemesModal === 'function') {
                        window.openMaster7ThemesModal();
                    }
                }, 250);
            }
        } catch (e) {}
    }

    // Initialize immediately & observe if sidebar is injected slightly later
    function ensureUniversalAlertsGuardLoaded() {
        try {
            if (!window.MagaieesPermissions && !document.querySelector('script[src*="permissions-manager.js"]')) {
                const pm = document.createElement('script');
                pm.src = 'js/permissions-manager.js?v=20261103_v10';
                pm.defer = true;
                document.head.appendChild(pm);
            }
            if (window.ERPAlertsGuard || document.querySelector('script[src*="erp_alerts_guard.js"]')) return;
            const s = document.createElement('script');
            s.src = 'js/erp_alerts_guard.js?v=20261103_v9';
            s.defer = true;
            document.head.appendChild(s);
        } catch (e) {}
    }

    function init() {
        ensureUniversalAlertsGuardLoaded();
        attachSidebarListeners();
        if (!document.querySelector('.global-sidebar')) {
            const obs = new MutationObserver(() => {
                if (document.querySelector('.global-sidebar')) {
                    attachSidebarListeners();
                    obs.disconnect();
                }
            });
            obs.observe(document.documentElement, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            init();
            handleDeepLinkActionsOnLoad();
        });
    } else {
        init();
        handleDeepLinkActionsOnLoad();
    }
})();
