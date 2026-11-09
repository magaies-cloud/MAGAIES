<?php
$page_title = 'السجل الرقابي';
$header_theme_class = 'header-theme-settings';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if (l === 'en') {
        document.documentElement.setAttribute('dir', 'ltr');
        document.documentElement.setAttribute('lang', 'en');
    }
})();
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>السجل الرقابي - مقاييس ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&family=Tajawal:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/theme.css?v=neon2">
    <link rel="stylesheet" href="css/components.css?v=neon2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        html, body {
            background: var(--bg-color, #0b1120);
            color: var(--text-primary, #f8fafc);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .audit-page-wrap {
            width: 100%;
            max-width: 1620px;
            margin: 0 auto;
            padding: 14px 18px 40px;
            box-sizing: border-box;
            direction: rtl;
        }

        /* Top Compact Bar */
        .audit-topbar {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92));
            border: 1.5px solid rgba(56, 189, 248, 0.3);
            border-right: 5px solid #38bdf8;
            border-radius: 14px;
            padding: 12px 18px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }

        .audit-title-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .audit-title-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            background: rgba(56, 189, 248, 0.16);
            border: 1.5px solid rgba(56, 189, 248, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .audit-title-text h1 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.2;
        }

        .audit-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .audit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-family: 'Cairo', 'Tajawal', sans-serif;
            font-size: 0.85rem;
            font-weight: 800;
            cursor: pointer;
            border: 1.5px solid rgba(255, 255, 255, 0.14);
            background: rgba(30, 41, 59, 0.9);
            color: #f8fafc;
            transition: all 0.18s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .audit-btn:hover {
            transform: translateY(-1px);
            border-color: #38bdf8;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .audit-btn-sky { background: rgba(2, 132, 199, 0.22); border-color: rgba(56, 189, 248, 0.5); color: #38bdf8; }
        .audit-btn-emerald { background: rgba(5, 150, 105, 0.22); border-color: rgba(16, 185, 129, 0.5); color: #34d399; }
        .audit-btn-amber { background: rgba(217, 119, 6, 0.22); border-color: rgba(245, 158, 11, 0.5); color: #fbbf24; }
        .audit-btn-rose { background: rgba(225, 29, 72, 0.2); border-color: rgba(244, 63, 94, 0.5); color: #fda4af; }

        /* KPI Strip */
        .audit-kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        @media (max-width: 1200px) { .audit-kpi-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 680px) { .audit-kpi-grid { grid-template-columns: repeat(2, 1fr); } }

        .audit-kpi-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 11px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            transition: all 0.18s ease;
            user-select: none;
        }

        .audit-kpi-card:hover, .audit-kpi-card.active-kpi {
            border-color: #38bdf8;
            background: rgba(30, 41, 59, 0.95);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(56, 189, 248, 0.15);
        }

        .audit-kpi-val { font-size: 1.45rem; font-weight: 900; color: #ffffff; line-height: 1.1; }
        .audit-kpi-lbl { font-size: 0.82rem; font-weight: 800; color: #cbd5e1; margin-top: 3px; }
        .audit-kpi-ico {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        /* Tabs Bar */
        .audit-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            background: rgba(15, 23, 42, 0.75);
            padding: 6px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.09);
            flex-wrap: wrap;
        }

        .audit-tab {
            flex: 1;
            min-width: 160px;
            padding: 9px 14px;
            border-radius: 9px;
            border: 1.5px solid transparent;
            background: transparent;
            color: #94a3b8;
            font-family: 'Cairo', 'Tajawal', sans-serif;
            font-size: 0.9rem;
            font-weight: 900;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.18s ease;
        }

        .audit-tab.active {
            background: rgba(2, 132, 199, 0.22);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.5);
        }

        .audit-panel { display: none; }
        .audit-panel.active { display: block; }

        /* Filter Bar */
        .audit-filters {
            background: rgba(15, 23, 42, 0.85);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 12px;
            display: grid;
            grid-template-columns: 1.8fr 1fr 1fr 1fr 1.5fr auto;
            gap: 10px;
            align-items: end;
        }

        @media (max-width: 1250px) { .audit-filters { grid-template-columns: 1fr 1fr 1fr; } }
        @media (max-width: 720px) { .audit-filters { grid-template-columns: 1fr; } }

        .audit-field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 900;
            color: #cbd5e1;
            margin-bottom: 5px;
        }

        .audit-input, .audit-select {
            width: 100%;
            height: 38px;
            padding: 0 11px;
            border-radius: 9px;
            border: 1.5px solid rgba(255, 255, 255, 0.16);
            background: #0b1120;
            color: #ffffff;
            font-family: 'Cairo', 'Tajawal', sans-serif;
            font-size: 0.86rem;
            font-weight: 800;
            box-sizing: border-box;
        }

        .audit-input:focus, .audit-select:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
        }

        /* Quick Date Pills */
        .audit-quick-dates {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
            padding: 0 4px;
        }

        .date-pills-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .date-pill {
            padding: 4px 11px;
            border-radius: 8px;
            font-family: 'Cairo', 'Tajawal', sans-serif;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(30, 41, 59, 0.75);
            color: #cbd5e1;
            transition: all 0.15s;
        }

        .date-pill.active, .date-pill:hover {
            background: rgba(56, 189, 248, 0.22);
            border-color: #38bdf8;
            color: #ffffff;
        }

        /* Table Card */
        .audit-card {
            background: rgba(15, 23, 42, 0.88);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.22);
        }

        .audit-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
        }

        .audit-table th {
            background: rgba(2, 6, 23, 0.92);
            color: #38bdf8;
            padding: 11px 12px;
            font-weight: 900;
            font-size: 0.84rem;
            border-bottom: 1.5px solid rgba(56, 189, 248, 0.25);
            white-space: nowrap;
        }

        .audit-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            color: #f1f5f9;
            font-size: 0.86rem;
            font-weight: 700;
            vertical-align: middle;
        }

        .audit-table tbody tr:hover {
            background: rgba(56, 189, 248, 0.07);
        }

        .badge-act {
            padding: 3px 9px;
            border-radius: 7px;
            font-size: 0.76rem;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .b-create { background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .b-update { background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
        .b-delete { background: rgba(239, 68, 68, 0.18); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
        .b-security { background: rgba(244, 63, 94, 0.2); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.45); }
        .b-print { background: rgba(56, 189, 248, 0.18); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); }

        .doc-chip {
            font-family: monospace;
            font-weight: 900;
            font-size: 0.84rem;
            color: #fbbf24;
            background: rgba(245, 158, 11, 0.14);
            border: 1px solid rgba(245, 158, 11, 0.35);
            padding: 2px 8px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-block;
            direction: ltr;
        }

        .doc-chip:hover {
            background: rgba(245, 158, 11, 0.28);
        }

        /* Documents Grid */
        .docs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 12px;
        }

        .doc-card {
            background: rgba(15, 23, 42, 0.88);
            border: 1.5px solid rgba(56, 189, 248, 0.22);
            border-radius: 14px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 9px;
            transition: all 0.18s ease;
        }

        .doc-card:hover {
            border-color: #38bdf8;
            transform: translateY(-2px);
        }

        /* Detail Modal */
        .audit-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.82);
            backdrop-filter: blur(5px);
            z-index: 999999;
            align-items: center;
            justify-content: center;
            padding: 16px;
            direction: rtl;
        }

        .audit-modal-box {
            background: #0f172a;
            border: 2px solid #38bdf8;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            padding: 20px;
            color: #f8fafc;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65);
        }

        /* Light Theme Compatibility */
        html.light-theme body,
        html[data-theme="warm_cream"] body,
        html[data-theme="ultra_white"] body,
        html[data-theme="exec_gray"] body {
            background: #f1f5f9 !important;
            color: #0f172a !important;
        }

        html.light-theme .audit-topbar,
        html.light-theme .audit-kpi-card,
        html.light-theme .audit-tabs,
        html.light-theme .audit-filters,
        html.light-theme .audit-card,
        html.light-theme .doc-card,
        html.light-theme .audit-modal-box {
            background: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06) !important;
        }

        html.light-theme .audit-title-text h1,
        html.light-theme .audit-kpi-val,
        html.light-theme .audit-table td {
            color: #0f172a !important;
        }

        html.light-theme .audit-kpi-lbl,
        html.light-theme .audit-field label {
            color: #334155 !important;
        }

        html.light-theme .audit-table th {
            background: #f8fafc !important;
            color: #0284c7 !important;
            border-bottom-color: #cbd5e1 !important;
        }

        html.light-theme .audit-table td {
            border-bottom-color: #e2e8f0 !important;
        }

        html.light-theme .audit-input,
        html.light-theme .audit-select {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }

        html.light-theme .audit-btn {
            background: #f8fafc !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }

        html.light-theme .date-pill {
            background: #f8fafc !important;
            color: #334155 !important;
            border-color: #cbd5e1 !important;
        }

        html.light-theme .date-pill.active {
            background: #0284c7 !important;
            color: #ffffff !important;
            border-color: #0284c7 !important;
        }

        html.light-theme .doc-chip {
            color: #b45309 !important;
            background: #fef3c7 !important;
            border-color: #f59e0b !important;
        }

        /* Print View */
        .print-only-header { display: none; }
        @media print {
            header.premium-header, .sidebar, .audit-topbar, .audit-tabs, .audit-filters, .audit-quick-dates, .no-print {
                display: none !important;
            }
            body, .audit-page-wrap, .audit-card {
                background: #ffffff !important;
                color: #000000 !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .print-only-header {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 14px;
                color: #000;
            }
            .audit-table th, .audit-table td {
                color: #000000 !important;
                border: 1px solid #94a3b8 !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="audit-page-wrap" id="main-content">

    <!-- Print Header -->
    <div class="print-only-header">
        <div>
            <h2 style="margin:0; font-size:1.3rem;">السجل الرقابي — مقاييس ERP</h2>
        </div>
        <div id="print-date-stamp" style="font-weight:800; font-size:0.9rem;"></div>
    </div>

    <!-- Clean Top Header -->
    <div class="audit-topbar">
        <div class="audit-title-group">
            <div class="audit-title-icon">🛡️</div>
            <div class="audit-title-text">
                <h1>السجل الرقابي</h1>
            </div>
        </div>

        <div class="audit-actions">
            <button type="button" onclick="syncAllAuditSources(true)" class="audit-btn audit-btn-emerald" title="تحديث ومزامنة السجل">
                🔄 تحديث
            </button>
            <button type="button" onclick="exportAuditToExcel()" class="audit-btn audit-btn-sky" title="تصدير إلى ملف إكسل">
                📥 تصدير Excel
            </button>
            <button type="button" onclick="printAuditReport()" class="audit-btn" title="طباعة السجل">
                🖨️ طباعة
            </button>
        </div>
    </div>

    <!-- 6 KPI Summary Cards -->
    <div class="audit-kpi-grid">
        <div class="audit-kpi-card active-kpi" id="kpi-card-all" onclick="quickFilterAction('')">
            <div>
                <div class="audit-kpi-val" id="kpi-total-events">0</div>
                <div class="audit-kpi-lbl">إجمالي الحركات</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(56,189,248,0.16); color:#38bdf8;">📊</div>
        </div>

        <div class="audit-kpi-card" id="kpi-card-create" onclick="quickFilterAction('create')">
            <div>
                <div class="audit-kpi-val" id="kpi-create-events" style="color:#10b981;">0</div>
                <div class="audit-kpi-lbl">إضافة وإصدار</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(16,185,129,0.16); color:#10b981;">➕</div>
        </div>

        <div class="audit-kpi-card" id="kpi-card-update" onclick="quickFilterAction('update')">
            <div>
                <div class="audit-kpi-val" id="kpi-update-events" style="color:#f59e0b;">0</div>
                <div class="audit-kpi-lbl">تعديل وتحديث</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(245,158,11,0.16); color:#f59e0b;">✏️</div>
        </div>

        <div class="audit-kpi-card" id="kpi-card-delete" onclick="quickFilterAction('delete')">
            <div>
                <div class="audit-kpi-val" id="kpi-delete-events" style="color:#ef4444;">0</div>
                <div class="audit-kpi-lbl">حذف ومرتجع</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(239,68,68,0.16); color:#ef4444;">🗑️</div>
        </div>

        <div class="audit-kpi-card" id="kpi-card-security" onclick="quickFilterAction('security')">
            <div>
                <div class="audit-kpi-val" id="kpi-security-events" style="color:#f43f5e;">0</div>
                <div class="audit-kpi-lbl">دخول وأمان</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(244,63,94,0.16); color:#f43f5e;">🚨</div>
        </div>

        <div class="audit-kpi-card" id="kpi-card-users" onclick="switchAuditTab('users')">
            <div>
                <div class="audit-kpi-val" id="kpi-active-users" style="color:#a855f7;">0</div>
                <div class="audit-kpi-lbl">الموظفون</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(168,85,247,0.16); color:#a855f7;">👥</div>
        </div>
    </div>

    <!-- 3 Concise Tabs -->
    <div class="audit-tabs">
        <button type="button" class="audit-tab active" id="tab-btn-stream" onclick="switchAuditTab('stream')">
            📋 سجل الحركات
        </button>
        <button type="button" class="audit-tab" id="tab-btn-forensics" onclick="switchAuditTab('forensics')">
            🔍 تتبع المستندات
        </button>
        <button type="button" class="audit-tab" id="tab-btn-users" onclick="switchAuditTab('users')">
            👥 نشاط الموظفين
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: AUDIT STREAM                        -->
    <!-- ========================================== -->
    <div class="audit-panel active" id="tab-panel-stream">
        <div class="audit-filters">
            <div class="audit-field">
                <label>🔍 بحث:</label>
                <input type="text" id="filter-search" class="audit-input" placeholder="رقم المستند، الموظف، الطرف، البيان..." oninput="renderAuditStream()">
            </div>

            <div class="audit-field">
                <label>👤 الموظف:</label>
                <select id="filter-user" class="audit-select" onchange="renderAuditStream()">
                    <option value="">الكل</option>
                </select>
            </div>

            <div class="audit-field">
                <label>📂 القسم:</label>
                <select id="filter-module" class="audit-select" onchange="renderAuditStream()">
                    <option value="">الكل</option>
                    <option value="المبيعات">المبيعات</option>
                    <option value="المشتريات">المشتريات</option>
                    <option value="المالية">السندات والمالية</option>
                    <option value="المخازن">المخازن</option>
                    <option value="القيود">القيود والأستاذ</option>
                    <option value="الشيكات">الشيكات والبنوك</option>
                    <option value="الأمان">الإعدادات والأمان</option>
                </select>
            </div>

            <div class="audit-field">
                <label>⚡ العملية:</label>
                <select id="filter-action" class="audit-select" onchange="renderAuditStream()">
                    <option value="">الكل</option>
                    <option value="create">➕ إضافة / إصدار</option>
                    <option value="update">✏️ تعديل</option>
                    <option value="delete">🗑️ حذف / مرتجع</option>
                    <option value="security">🚨 دخول وأمان</option>
                    <option value="print">🖨️ طباعة وتصدير</option>
                </select>
            </div>

            <div class="audit-field">
                <label>📅 التاريخ:</label>
                <div style="display:flex; gap:6px;">
                    <input type="date" id="filter-date-from" class="audit-input" onchange="clearDatePills(); renderAuditStream();" title="من تاريخ">
                    <input type="date" id="filter-date-to" class="audit-input" onchange="clearDatePills(); renderAuditStream();" title="إلى تاريخ">
                </div>
            </div>

            <div class="audit-field">
                <button type="button" onclick="resetAuditFilters()" class="audit-btn audit-btn-rose" style="height:38px;">✖ مسح</button>
            </div>
        </div>

        <!-- Quick Date Filter Pills & Row Limit -->
        <div class="audit-quick-dates">
            <div class="date-pills-group">
                <span style="font-size:0.8rem; font-weight:800; color:#94a3b8;">الفترة:</span>
                <button type="button" class="date-pill active" id="dpill-all" onclick="setQuickDateRange('all')">الكل</button>
                <button type="button" class="date-pill" id="dpill-today" onclick="setQuickDateRange('today')">اليوم</button>
                <button type="button" class="date-pill" id="dpill-yesterday" onclick="setQuickDateRange('yesterday')">أمس</button>
                <button type="button" class="date-pill" id="dpill-week" onclick="setQuickDateRange('week')">آخر 7 أيام</button>
                <button type="button" class="date-pill" id="dpill-month" onclick="setQuickDateRange('month')">هذا الشهر</button>
            </div>

            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:0.84rem; font-weight:900; color:#38bdf8;">
                    النتائج: <span id="stream-showing-count">0</span> حركة
                </span>
                <select id="filter-limit" class="audit-select" style="width:110px; height:32px; font-size:0.8rem;" onchange="renderAuditStream()">
                    <option value="100">عرض 100</option>
                    <option value="300" selected>عرض 300</option>
                    <option value="1000">عرض 1000</option>
                    <option value="999999">عرض الكل</option>
                </select>
            </div>
        </div>

        <div class="audit-card">
            <div style="overflow-x:auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th style="width:45px;">#</th>
                            <th>التاريخ والوقت</th>
                            <th>الموظف</th>
                            <th>العملية</th>
                            <th>القسم</th>
                            <th>رقم المستند</th>
                            <th>الطرف / المبلغ</th>
                            <th>البيان والتفاصيل</th>
                            <th class="no-print" style="width:130px;">إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="audit-stream-tbody">
                        <tr>
                            <td colspan="9" style="text-align:center; padding:32px; color:#94a3b8;">جاري تحميل السجل الرقابي...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: DOCUMENT TRACKER                    -->
    <!-- ========================================== -->
    <div class="audit-panel" id="tab-panel-forensics">
        <div class="audit-filters" style="grid-template-columns: 2fr 1fr 1fr auto;">
            <div class="audit-field">
                <label>🔍 بحث عن مستند:</label>
                <input type="text" id="forensics-doc-query" class="audit-input" placeholder="رقم الفاتورة أو السند أو اسم العميل أو المورد أو المبلغ..." oninput="renderForensicsCards()">
            </div>
            <div class="audit-field">
                <label>📂 نوع المستند:</label>
                <select id="forensics-doc-type" class="audit-select" onchange="renderForensicsCards()">
                    <option value="">الكل</option>
                    <option value="sales">فواتير المبيعات</option>
                    <option value="purchases">فواتير المشتريات</option>
                    <option value="vouchers">سندات القبض والصرف</option>
                    <option value="journal">قيود اليومية</option>
                    <option value="inventory">حركات المخازن</option>
                    <option value="checks">الشيكات والبنوك</option>
                </select>
            </div>
            <div class="audit-field">
                <label>👤 الموظف:</label>
                <select id="forensics-doc-user" class="audit-select" onchange="renderForensicsCards()">
                    <option value="">الكل</option>
                </select>
            </div>
            <div class="audit-field">
                <button type="button" onclick="resetDocTrackerFilters()" class="audit-btn audit-btn-rose" style="height:38px;">✖ مسح</button>
            </div>
        </div>

        <div id="forensics-cards-container" class="docs-grid"></div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: EMPLOYEE ACTIVITY                   -->
    <!-- ========================================== -->
    <div class="audit-panel" id="tab-panel-users">
        <div class="audit-card">
            <div style="padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div style="font-weight:900; color:#38bdf8; font-size:0.95rem;">
                    👥 ملخص عمليات الموظفين
                </div>
                <a href="users-roles.php" class="audit-btn audit-btn-sky" style="padding:6px 12px; font-size:0.8rem;">
                    ⚙️ إدارة الموظفين والصلاحيات
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>الموظف</th>
                            <th>الدور الوظيفي</th>
                            <th>إجمالي العمليات</th>
                            <th>إضافة (➕)</th>
                            <th>تعديل (✏️)</th>
                            <th>حذف ومرتجع (🗑️)</th>
                            <th>أمان ودخول (🚨)</th>
                            <th>آخر نشاط</th>
                            <th>الحالة</th>
                            <th class="no-print">عرض السجل</th>
                        </tr>
                    </thead>
                    <tbody id="audit-users-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<!-- Clean Entry Details Modal -->
<div id="audit-detail-modal" class="audit-modal-backdrop" onclick="if(event.target===this)this.style.display='none'">
    <div class="audit-modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.12); padding-bottom:10px; margin-bottom:14px;">
            <h3 style="margin:0; font-size:1.1rem; font-weight:900; color:#38bdf8;">
                📋 تفاصيل الحركة الرقابية
            </h3>
            <button type="button" onclick="document.getElementById('audit-detail-modal').style.display='none'" class="audit-btn audit-btn-rose" style="padding:4px 10px; font-size:0.8rem;">إغلاق ✖</button>
        </div>
        <div id="audit-detail-body"></div>
    </div>
</div>

<script src="js/localforage.min.js"></script>
<script src="js/app.js"></script>

<script>
    let ALL_UNIFIED_AUDIT_LOGS = [];
    let ALL_EXTRACTED_DOCS = [];
    let CURRENT_FILTERED_LOGS = [];

    function classifyActionCategory(actionType, desc) {
        const txt = ((actionType || '') + ' ' + (desc || '')).toLowerCase();
        if (txt.includes('حذف') || txt.includes('إلغاء') || txt.includes('مرتجع') || txt.includes('delete') || txt.includes('استرداد')) return 'delete';
        if (txt.includes('تعديل') || txt.includes('تحديث') || txt.includes('edit') || txt.includes('update')) return 'update';
        if (txt.includes('أمان') || txt.includes('دخول') || txt.includes('خروج') || txt.includes('صلاحية') || txt.includes('تجاوز') || txt.includes('إنذار') || txt.includes('login')) return 'security';
        if (txt.includes('طباعة') || txt.includes('تصدير') || txt.includes('فحص') || txt.includes('print') || txt.includes('export')) return 'print';
        return 'create';
    }

    function normalizeModuleCategory(mod, desc) {
        const txt = ((mod || '') + ' ' + (desc || '')).toLowerCase();
        if (txt.includes('مبيعات') || txt.includes('عميل') || txt.includes('عملاء') || txt.includes('sales')) return 'المبيعات';
        if (txt.includes('مشتريات') || txt.includes('مورد') || txt.includes('توريد') || txt.includes('purchase')) return 'المشتريات';
        if (txt.includes('سند') || txt.includes('صرف') || txt.includes('قبض') || txt.includes('خزينة') || txt.includes('مالية') || txt.includes('voucher') || txt.includes('cash')) return 'السندات والمالية';
        if (txt.includes('مخزن') || txt.includes('مخازن') || txt.includes('جرد') || txt.includes('تحويل') || txt.includes('بضاعة') || txt.includes('صنف')) return 'المخازن';
        if (txt.includes('قيد') || txt.includes('قيود') || txt.includes('أستاذ') || txt.includes('حسابات')) return 'القيود والأستاذ';
        if (txt.includes('شيك') || txt.includes('بنك') || txt.includes('check')) return 'الشيكات والبنوك';
        if (txt.includes('دخول') || txt.includes('أمان') || txt.includes('صلاحيات') || txt.includes('مستخدم') || txt.includes('حارس')) return 'الإعدادات والأمان';
        return mod || 'النظام العام';
    }

    function parseTimestampSafe(val) {
        if (!val) return Date.now();
        if (typeof val === 'number') return val > 1e11 ? val : val * 1000;
        const parsed = Date.parse(val);
        return isNaN(parsed) ? Date.now() : parsed;
    }

    function readTenantAwareArray(baseKey) {
        const results = [];
        const seenIds = new Set();
        const keysToRead = [baseKey];
        try {
            const u = JSON.parse(localStorage.getItem('user') || '{}');
            const tId = u.tenant_id || localStorage.getItem('currentTenantId') || '';
            if (tId) keysToRead.unshift(`tenant_${tId}_${baseKey}`);
        } catch (e) {}

        keysToRead.forEach(k => {
            try {
                const raw = localStorage.getItem(k);
                if (!raw) return;
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    parsed.forEach(item => {
                        if (!item) return;
                        const sig = item.id || item.invoiceNo || item.voucherNo || item.entryNo || JSON.stringify(item).slice(0, 80);
                        if (!seenIds.has(sig)) {
                            seenIds.add(sig);
                            results.push(item);
                        }
                    });
                }
            } catch (e) {}
        });
        return results;
    }

    function scanLiveERPDocuments() {
        const docs = [];
        const syntheticLogs = [];

        // 1. Sales Invoices
        readTenantAwareArray('erp_sales_invoices_v1').forEach(inv => {
            const docNo = inv.invoiceNo || inv.id || 'INV';
            const user = inv.createdByName || inv.createdBy || inv.salesman || inv.user || 'مدير النظام';
            const ts = parseTimestampSafe(inv.createdAt || inv.timestamp || inv.date);
            const amt = Number(inv.netTotal || inv.total || inv.amount || 0);
            const party = inv.customerName || inv.clientName || 'عميل نقدي';
            const cur = inv.currency || 'SDG';
            const isReturn = (inv.type === 'return' || inv.isReturn || String(docNo).includes('RET'));
            const url = isReturn ? `sales-return.php?id=${encodeURIComponent(docNo)}` : `sales.php?invoice=${encodeURIComponent(docNo)}`;

            docs.push({
                docNo: String(docNo),
                docType: 'sales',
                typeBadge: isReturn ? '🔄 مرتجع مبيعات' : '🧾 فاتورة مبيعات',
                module: 'المبيعات',
                creator: user,
                modifier: inv.updatedByName || inv.updatedBy || '',
                updatedAt: inv.updatedAt || '',
                party: party,
                amount: amt,
                currency: cur,
                dateStr: inv.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                url: url,
                statementUrl: `account-statement.php?name=${encodeURIComponent(party)}`
            });

            syntheticLogs.push({
                id: 'syn_sales_' + docNo,
                userName: user,
                actionType: isReturn ? 'مرتجع مبيعات' : 'فاتورة مبيعات',
                module: 'المبيعات',
                docId: String(docNo),
                description: `${isReturn ? 'مرتجع مبيعات' : 'فاتورة مبيعات'} رقم (${docNo}) — العميل: ${party}`,
                timestamp: ts,
                url: url,
                party: party,
                amount: amt,
                currency: cur
            });
        });

        // 2. Purchase Invoices
        const purList = [
            ...readTenantAwareArray('erp_purchase_invoices_v1'),
            ...readTenantAwareArray('erp_purchases_v1')
        ];
        const seenPur = new Set();
        purList.forEach(inv => {
            const docNo = String(inv.invoiceNo || inv.number || inv.id || 'PUR');
            if (seenPur.has(docNo)) return;
            seenPur.add(docNo);

            const user = inv.createdByName || inv.createdBy || inv.user || 'مدير النظام';
            const ts = parseTimestampSafe(inv.createdAt || inv.timestamp || inv.date);
            const amt = Number(inv.netTotal || inv.total || inv.amount || 0);
            const party = inv.supplierName || inv.vendorName || inv.supplier || 'مورد عام';
            const cur = inv.currency || 'SDG';
            const url = `purchase.php?invoice=${encodeURIComponent(docNo)}`;

            docs.push({
                docNo: docNo,
                docType: 'purchases',
                typeBadge: '📦 فاتورة مشتريات',
                module: 'المشتريات',
                creator: user,
                modifier: inv.updatedByName || inv.updatedBy || '',
                updatedAt: inv.updatedAt || '',
                party: party,
                amount: amt,
                currency: cur,
                dateStr: inv.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                url: url,
                statementUrl: `account-statement.php?name=${encodeURIComponent(party)}`
            });

            syntheticLogs.push({
                id: 'syn_pur_' + docNo,
                userName: user,
                actionType: 'فاتورة مشتريات',
                module: 'المشتريات',
                docId: docNo,
                description: `فاتورة مشتريات رقم (${docNo}) — المورد: ${party}`,
                timestamp: ts,
                url: url,
                party: party,
                amount: amt,
                currency: cur
            });
        });

        // 3. Vouchers (Payments & Receipts)
        const voucherSources = [
            { key: 'erp_single_payments_v1', url: 'single-cash-payment.php' },
            { key: 'erp_single_receipts_v1', url: 'single-cash-payment.php?mode=receipt' },
            { key: 'erp_payments_v1', url: 'multi-cash-payment.php?tab=payment' },
            { key: 'erp_receipts_v1', url: 'multi-cash-payment.php?tab=receipt' }
        ];

        voucherSources.forEach(src => {
            readTenantAwareArray(src.key).forEach(v => {
                const docNo = String(v.voucherNo || v.receiptNo || v.number || v.id || 'VCH');
                const isReceipt = (v.voucherType === 'receipt' || src.key.includes('receipt'));
                const badge = isReceipt ? '📥 سند قبض' : '📤 سند صرف';
                const user = v.createdByName || v.createdBy || v.user || 'مدير النظام';
                const ts = parseTimestampSafe(v.createdAt || v.timestamp || v.date);
                const amt = Number(v.totalAmount || v.amount || v.total || 0);
                const cur = v.currency || 'SDG';
                const party = v.accountName || v.beneficiary || v.customerName || v.partyName || (Array.isArray(v.items) && v.items[0] ? v.items[0].accountName : 'حساب مالي');
                const url = `${src.url}${src.url.includes('?') ? '&' : '?'}voucher=${encodeURIComponent(docNo)}`;

                docs.push({
                    docNo: docNo,
                    docType: 'vouchers',
                    typeBadge: badge,
                    module: 'السندات والمالية',
                    creator: user,
                    modifier: v.updatedByName || '',
                    updatedAt: v.updatedAt || '',
                    party: party,
                    amount: amt,
                    currency: cur,
                    dateStr: v.date || new Date(ts).toISOString().slice(0, 10),
                    timestamp: ts,
                    url: url,
                    statementUrl: `account-statement.php?name=${encodeURIComponent(party)}`
                });

                syntheticLogs.push({
                    id: 'syn_vch_' + src.key + '_' + docNo,
                    userName: user,
                    actionType: isReceipt ? 'سند قبض' : 'سند صرف',
                    module: 'السندات والمالية',
                    docId: docNo,
                    description: `${badge} رقم (${docNo}) — ${party} ${v.description || v.notes ? '— ' + (v.description || v.notes) : ''}`,
                    timestamp: ts,
                    url: url,
                    party: party,
                    amount: amt,
                    currency: cur
                });
            });
        });

        // 4. Journal Entries
        readTenantAwareArray('erp_journal_entries_v1').forEach(j => {
            const docNo = String(j.entryNo || j.voucherNo || j.id || 'JRN');
            const user = j.createdByName || j.createdBy || 'مدير النظام';
            const ts = parseTimestampSafe(j.createdAt || j.timestamp || j.date);
            const amt = Number(j.totalDebit || j.amount || j.total || 0);
            const cur = j.currency || 'SDG';
            const url = `journal.php?entry=${encodeURIComponent(docNo)}`;

            docs.push({
                docNo: docNo,
                docType: 'journal',
                typeBadge: '⚖️ قيد يومية',
                module: 'القيود والأستاذ',
                creator: user,
                modifier: j.updatedByName || '',
                updatedAt: j.updatedAt || '',
                party: j.description || j.notes || 'قيد يومية',
                amount: amt,
                currency: cur,
                dateStr: j.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                url: url,
                statementUrl: 'general-ledger.php'
            });

            syntheticLogs.push({
                id: 'syn_jrn_' + docNo,
                userName: user,
                actionType: 'قيد يومية',
                module: 'القيود والأستاذ',
                docId: docNo,
                description: `قيد يومية رقم (${docNo}) — ${j.description || 'قيد محاسبي'}`,
                timestamp: ts,
                url: url,
                party: j.description || 'قيد محاسبي',
                amount: amt,
                currency: cur
            });
        });

        // 5. Inventory Adjustments & Transfers
        readTenantAwareArray('erp_inventory_adjustments_v1').forEach(adj => {
            const docNo = String(adj.adjustmentNo || adj.id || 'ADJ');
            const user = adj.createdByName || adj.user || 'أمين المخزن';
            const ts = parseTimestampSafe(adj.createdAt || adj.timestamp || adj.date);
            const wh = adj.warehouseName || adj.warehouse || 'المخزن الرئيسي';
            const amt = Number(adj.totalDiffValue || adj.amount || 0);

            docs.push({
                docNo: docNo,
                docType: 'inventory',
                typeBadge: '📋 تسوية جرد',
                module: 'المخازن',
                creator: user,
                modifier: '',
                updatedAt: '',
                party: wh,
                amount: amt,
                currency: 'SDG',
                dateStr: adj.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                url: 'inventory-adjustments.php'
            });

            syntheticLogs.push({
                id: 'syn_adj_' + docNo,
                userName: user,
                actionType: 'تسوية جرد',
                module: 'المخازن',
                docId: docNo,
                description: `تسوية جرد رقم (${docNo}) — ${wh}`,
                timestamp: ts,
                url: 'inventory-adjustments.php',
                party: wh,
                amount: amt,
                currency: 'SDG'
            });
        });

        readTenantAwareArray('erp_warehouse_transfers_v1').forEach(tr => {
            const docNo = String(tr.transferNo || tr.id || 'TRF');
            const user = tr.createdByName || tr.user || 'أمين المخزن';
            const ts = parseTimestampSafe(tr.createdAt || tr.timestamp || tr.date);
            const party = `من ${tr.fromWarehouse || 'مخزن'} إلى ${tr.toWarehouse || 'مخزن'}`;
            const qty = Number(tr.totalQty || tr.itemsCount || 0);

            docs.push({
                docNo: docNo,
                docType: 'inventory',
                typeBadge: '🚚 تحويل مخزني',
                module: 'المخازن',
                creator: user,
                modifier: '',
                updatedAt: '',
                party: party,
                amount: qty,
                currency: 'وحدة',
                dateStr: tr.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                url: 'warehouse-transfers.php'
            });

            syntheticLogs.push({
                id: 'syn_trf_' + docNo,
                userName: user,
                actionType: 'تحويل مخزني',
                module: 'المخازن',
                docId: docNo,
                description: `تحويل مخزني رقم (${docNo}) — ${party}`,
                timestamp: ts,
                url: 'warehouse-transfers.php',
                party: party,
                amount: qty,
                currency: 'وحدة'
            });
        });

        // 6. Checks
        readTenantAwareArray('erp_checks_v1').forEach(chk => {
            const docNo = String(chk.checkNo || chk.number || chk.id || 'CHK');
            const user = chk.createdByName || 'مدير النظام';
            const ts = parseTimestampSafe(chk.createdAt || chk.dueDate || chk.date);
            const amt = Number(chk.amount || 0);
            const party = chk.drawerName || chk.beneficiary || chk.bankName || 'حافظة الشيكات';

            docs.push({
                docNo: docNo,
                docType: 'checks',
                typeBadge: '🏦 شيك مصرفي',
                module: 'الشيكات والبنوك',
                creator: user,
                modifier: '',
                updatedAt: '',
                party: party,
                amount: amt,
                currency: chk.currency || 'SDG',
                dateStr: chk.dueDate || chk.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                url: 'checks.php'
            });

            syntheticLogs.push({
                id: 'syn_chk_' + docNo,
                userName: user,
                actionType: 'تسجيل شيك',
                module: 'الشيكات والبنوك',
                docId: docNo,
                description: `شيك رقم (${docNo}) — ${party}`,
                timestamp: ts,
                url: 'checks.php',
                party: party,
                amount: amt,
                currency: chk.currency || 'SDG'
            });
        });

        return { docs, syntheticLogs };
    }

    async function syncAllAuditSources(showToast = false) {
        let rawLogs = [
            ...readTenantAwareArray('erp_audit_logs'),
            ...readTenantAwareArray('erp_audit_logs_v1')
        ];

        // Fetch server MySQL audit logs (supports both {status:'success', data:[...]} and {success:true, logs:[...]})
        try {
            const res = await fetch('api/log_audit.php?_t=' + Date.now());
            if (res.ok) {
                const payload = await res.json();
                const srvRows = (payload && Array.isArray(payload.data)) ? payload.data : ((payload && Array.isArray(payload.logs)) ? payload.logs : []);
                if (srvRows.length > 0) {
                    rawLogs.push(...srvRows);
                }
            }
        } catch (e) {}

        // Scan live ERP documents
        const { docs, syntheticLogs } = scanLiveERPDocuments();
        ALL_EXTRACTED_DOCS = docs.sort((a, b) => b.timestamp - a.timestamp);
        rawLogs.push(...syntheticLogs);

        const seen = new Set();
        const unified = [];

        rawLogs.forEach(item => {
            if (!item) return;
            const ts = parseTimestampSafe(item.timestamp || item._db_created_at || item.created_at || item.date);
            const actionType = item.actionType || item.action_type || 'عملية';
            const desc = item.description || '';
            const userName = item.userName || item.user_name || 'مدير النظام';
            const extractedDoc = desc.match(/([A-Z]+-[0-9A-Za-z-]+|#\d+|\b\d{3,}\b)/);
            const docId = item.docId || item.doc_id || (extractedDoc ? extractedDoc[1] : '');
            const dedupKey = `${actionType}|${docId}|${desc.slice(0, 50)}|${Math.floor(ts / 60000)}`;

            if (seen.has(dedupKey)) return;
            seen.add(dedupKey);

            const mod = normalizeModuleCategory(item.module, desc);
            const cat = classifyActionCategory(actionType, desc);

            let url = item.url || '';
            if (!url) {
                if (mod.includes('المبيعات')) url = docId ? `sales.php?invoice=${encodeURIComponent(docId)}` : 'sales.php';
                else if (mod.includes('المشتريات')) url = docId ? `purchase.php?invoice=${encodeURIComponent(docId)}` : 'purchase.php';
                else if (mod.includes('السندات')) url = docId ? `single-cash-payment.php?voucher=${encodeURIComponent(docId)}` : 'single-cash-payment.php';
                else if (mod.includes('المخازن')) url = 'inventory.php';
                else if (mod.includes('القيود')) url = 'general-ledger.php';
                else if (mod.includes('الشيكات')) url = 'checks.php';
                else if (mod.includes('الأمان')) url = 'users-roles.php';
            }

            unified.push({
                id: item.id || ('aud_' + ts + '_' + unified.length),
                timestamp: ts,
                userName: userName,
                userRole: item.userRole || 'موظف',
                actionType: actionType,
                actionCat: cat,
                module: mod,
                docId: String(docId || ''),
                description: desc,
                url: url,
                party: item.party || '',
                amount: Number(item.amount || 0),
                currency: item.currency || 'SDG'
            });
        });

        ALL_UNIFIED_AUDIT_LOGS = unified.sort((a, b) => b.timestamp - a.timestamp);

        populateUserDropdowns();
        updateAuditKPIs();
        renderAuditStream();
        renderForensicsCards();
        renderUsersMatrix();

        if (showToast && typeof window.showToast === 'function') {
            window.showToast('تم تحديث السجل الرقابي بنجاح', 'success');
        }
    }

    function populateUserDropdowns() {
        const usersSet = new Set();
        ALL_UNIFIED_AUDIT_LOGS.forEach(l => { if (l.userName) usersSet.add(l.userName); });
        ALL_EXTRACTED_DOCS.forEach(d => { if (d.creator) usersSet.add(d.creator); });

        const sel1 = document.getElementById('filter-user');
        const sel2 = document.getElementById('forensics-doc-user');
        const val1 = sel1 ? sel1.value : '';
        const val2 = sel2 ? sel2.value : '';

        const opts = '<option value="">الكل</option>' + Array.from(usersSet).map(u => `<option value="${u}">${u}</option>`).join('');
        if (sel1) { sel1.innerHTML = opts; sel1.value = val1; }
        if (sel2) { sel2.innerHTML = opts; sel2.value = val2; }
    }

    function updateAuditKPIs() {
        const total = ALL_UNIFIED_AUDIT_LOGS.length;
        const creates = ALL_UNIFIED_AUDIT_LOGS.filter(l => l.actionCat === 'create').length;
        const updates = ALL_UNIFIED_AUDIT_LOGS.filter(l => l.actionCat === 'update').length;
        const deletes = ALL_UNIFIED_AUDIT_LOGS.filter(l => l.actionCat === 'delete').length;
        const sec = ALL_UNIFIED_AUDIT_LOGS.filter(l => l.actionCat === 'security').length;
        const uniqueUsers = new Set(ALL_UNIFIED_AUDIT_LOGS.map(l => l.userName)).size;

        document.getElementById('kpi-total-events').textContent = total.toLocaleString();
        document.getElementById('kpi-create-events').textContent = creates.toLocaleString();
        document.getElementById('kpi-update-events').textContent = updates.toLocaleString();
        document.getElementById('kpi-delete-events').textContent = deletes.toLocaleString();
        document.getElementById('kpi-security-events').textContent = sec.toLocaleString();
        document.getElementById('kpi-active-users').textContent = uniqueUsers.toLocaleString();
    }

    function getFilteredStreamLogs() {
        const q = (document.getElementById('filter-search').value || '').trim().toLowerCase();
        const user = document.getElementById('filter-user').value;
        const mod = document.getElementById('filter-module').value;
        const act = document.getElementById('filter-action').value;
        const dFrom = document.getElementById('filter-date-from').value;
        const dTo = document.getElementById('filter-date-to').value;

        const fromTs = dFrom ? new Date(dFrom + 'T00:00:00').getTime() : 0;
        const toTs = dTo ? new Date(dTo + 'T23:59:59').getTime() : Infinity;

        return ALL_UNIFIED_AUDIT_LOGS.filter(l => {
            if (user && l.userName !== user) return false;
            if (mod && !l.module.includes(mod)) return false;
            if (act && l.actionCat !== act) return false;
            if (l.timestamp < fromTs || l.timestamp > toTs) return false;
            if (q) {
                const hay = `${l.userName} ${l.actionType} ${l.module} ${l.docId} ${l.party} ${l.amount || ''} ${l.description}`.toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
    }

    function renderAuditStream() {
        const tbody = document.getElementById('audit-stream-tbody');
        CURRENT_FILTERED_LOGS = getFilteredStreamLogs();
        document.getElementById('stream-showing-count').textContent = CURRENT_FILTERED_LOGS.length.toLocaleString();

        const limit = parseInt(document.getElementById('filter-limit')?.value || '300', 10) || 300;

        if (CURRENT_FILTERED_LOGS.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:34px; color:#94a3b8;">لا توجد حركات مطابقة للبحث.</td></tr>`;
            return;
        }

        const badgeMap = {
            create: { cls: 'b-create', ico: '➕' },
            update: { cls: 'b-update', ico: '✏️' },
            delete: { cls: 'b-delete', ico: '🗑️' },
            security: { cls: 'b-security', ico: '🚨' },
            print: { cls: 'b-print', ico: '🖨️' }
        };

        tbody.innerHTML = CURRENT_FILTERED_LOGS.slice(0, limit).map((log, idx) => {
            const b = badgeMap[log.actionCat] || badgeMap.create;
            const dObj = new Date(log.timestamp);
            const dateFormatted = dObj.toLocaleDateString('ar-EG', { year: 'numeric', month: 'short', day: 'numeric' });
            const timeFormatted = dObj.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });

            const partyOrAmt = [];
            if (log.party) partyOrAmt.push(`<span>${log.party}</span>`);
            if (log.amount > 0) partyOrAmt.push(`<strong style="color:#10b981;">${log.amount.toLocaleString()} ${log.currency}</strong>`);

            return `
                <tr>
                    <td style="color:#64748b; font-weight:900;">${idx + 1}</td>
                    <td style="white-space:nowrap;">
                        <div style="font-weight:900;">${dateFormatted}</div>
                        <div style="font-size:0.75rem; color:#38bdf8;">${timeFormatted}</div>
                    </td>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:5px; font-weight:800;">
                            👤 ${log.userName}
                        </span>
                    </td>
                    <td><span class="badge-act ${b.cls}">${b.ico} ${log.actionType}</span></td>
                    <td style="font-weight:800;">${log.module}</td>
                    <td>
                        ${log.docId ? `<span class="doc-chip" onclick="inspectAuditEntry(${idx})" title="عرض التفاصيل">${log.docId}</span>` : '<span style="color:#64748b;">—</span>'}
                    </td>
                    <td style="font-size:0.82rem;">
                        ${partyOrAmt.length ? partyOrAmt.join(' • ') : '<span style="color:#64748b;">—</span>'}
                    </td>
                    <td style="max-width:420px; line-height:1.45;">${log.description}</td>
                    <td class="no-print">
                        <div style="display:flex; gap:5px; align-items:center;">
                            <button type="button" onclick="inspectAuditEntry(${idx})" class="audit-btn" style="padding:4px 9px; font-size:0.76rem;">
                                📋 تفاصيل
                            </button>
                            ${log.url ? `<a href="${log.url}" class="audit-btn audit-btn-sky" style="padding:4px 9px; font-size:0.76rem;" title="فتح الشاشة">🔗 فتح</a>` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderForensicsCards() {
        const container = document.getElementById('forensics-cards-container');
        const q = (document.getElementById('forensics-doc-query').value || '').trim().toLowerCase();
        const type = document.getElementById('forensics-doc-type').value;
        const user = document.getElementById('forensics-doc-user').value;

        const filtered = ALL_EXTRACTED_DOCS.filter(d => {
            if (type && d.docType !== type) return false;
            if (user && d.creator !== user) return false;
            if (q) {
                const hay = `${d.docNo} ${d.typeBadge} ${d.module} ${d.creator} ${d.party} ${d.amount}`.toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });

        if (filtered.length === 0) {
            container.innerHTML = `
                <div style="grid-column:1/-1; text-align:center; padding:36px; border-radius:14px; border:1.5px dashed rgba(56,189,248,0.35); color:#94a3b8;">
                    <div style="font-size:1.8rem; margin-bottom:6px;">🔍</div>
                    <div style="font-weight:900; font-size:0.95rem;">لا توجد مستندات مطابقة للبحث</div>
                </div>
            `;
            return;
        }

        container.innerHTML = filtered.slice(0, 120).map(d => `
            <div class="doc-card">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(148,163,184,0.15); padding-bottom:7px;">
                    <span style="font-size:0.84rem; font-weight:900; color:#38bdf8;">${d.typeBadge}</span>
                    <span class="doc-chip">#${d.docNo}</span>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.82rem;">
                    <div><span style="color:#94a3b8;">الموظف:</span> <strong>👤 ${d.creator}</strong></div>
                    <div style="color:#38bdf8; font-weight:800;">📅 ${d.dateStr}</div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:0.82rem; background:rgba(148,163,184,0.07); padding:8px 10px; border-radius:9px;">
                    <div>
                        <div style="color:#94a3b8; font-size:0.73rem;">الطرف / الحساب:</div>
                        <div style="font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${d.party}">${d.party}</div>
                    </div>
                    <div>
                        <div style="color:#94a3b8; font-size:0.73rem;">المبلغ / الكمية:</div>
                        <div style="font-weight:900; color:#10b981;">${Number(d.amount || 0).toLocaleString()} ${d.currency}</div>
                    </div>
                </div>

                ${d.modifier ? `<div style="font-size:0.76rem; color:#f59e0b;">✏️ آخر تعديل: <strong>${d.modifier}</strong></div>` : ''}

                <div style="display:flex; gap:6px; margin-top:auto; padding-top:4px;">
                    <a href="${d.url}" class="audit-btn audit-btn-sky" style="flex:1; padding:6px 10px; font-size:0.8rem;">
                        🔗 فتح المستند
                    </a>
                    ${d.statementUrl ? `<a href="${d.statementUrl}" class="audit-btn" style="padding:6px 10px; font-size:0.8rem;">📑 كشف الحساب</a>` : ''}
                </div>
            </div>
        `).join('');
    }

    function renderUsersMatrix() {
        const tbody = document.getElementById('audit-users-tbody');
        const map = {};

        ALL_UNIFIED_AUDIT_LOGS.forEach(l => {
            const u = l.userName || 'مدير النظام';
            if (!map[u]) {
                map[u] = { name: u, role: l.userRole || 'موظف', total: 0, create: 0, update: 0, delete: 0, security: 0, lastTs: 0 };
            }
            map[u].total++;
            if (map[u][l.actionCat] !== undefined) map[u][l.actionCat]++;
            if (l.timestamp > map[u].lastTs) map[u].lastTs = l.timestamp;
        });

        const rows = Object.values(map).sort((a, b) => b.total - a.total);
        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:28px; color:#94a3b8;">لا توجد بيانات مسجلة حالياً.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(u => {
            const statusTxt = u.delete > 10 ? '⚠️ حذف مرتفع' : '✅ منتظم';
            const statusClr = u.delete > 10 ? '#f59e0b' : '#10b981';
            const lastStr = u.lastTs ? new Date(u.lastTs).toLocaleString('ar-EG') : '—';

            return `
                <tr>
                    <td style="font-weight:900; color:#38bdf8;">👤 ${u.name}</td>
                    <td>${u.role}</td>
                    <td style="font-weight:900;">${u.total.toLocaleString()}</td>
                    <td style="color:#10b981; font-weight:900;">${u.create.toLocaleString()}</td>
                    <td style="color:#f59e0b; font-weight:900;">${u.update.toLocaleString()}</td>
                    <td style="color:#ef4444; font-weight:900;">${u.delete.toLocaleString()}</td>
                    <td style="color:#f43f5e; font-weight:900;">${u.security.toLocaleString()}</td>
                    <td style="font-size:0.8rem;">${lastStr}</td>
                    <td><span style="color:${statusClr}; font-weight:900; font-size:0.82rem;">${statusTxt}</span></td>
                    <td class="no-print">
                        <button type="button" onclick="filterBySpecificUser('${u.name.replace(/'/g, "\\'")}')" class="audit-btn audit-btn-sky" style="padding:4px 10px; font-size:0.78rem;">
                            🔍 عرض حركاته
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function switchAuditTab(tabName) {
        ['stream', 'forensics', 'users'].forEach(t => {
            document.getElementById('tab-btn-' + t).classList.toggle('active', t === tabName);
            document.getElementById('tab-panel-' + t).classList.toggle('active', t === tabName);
        });
    }

    function quickFilterAction(act) {
        switchAuditTab('stream');
        document.getElementById('filter-action').value = act;
        ['all', 'create', 'update', 'delete', 'security'].forEach(k => {
            const el = document.getElementById('kpi-card-' + k);
            if (el) el.classList.toggle('active-kpi', (act === '' && k === 'all') || act === k);
        });
        renderAuditStream();
    }

    function filterBySpecificUser(userName) {
        switchAuditTab('stream');
        document.getElementById('filter-user').value = userName;
        renderAuditStream();
    }

    function clearDatePills() {
        document.querySelectorAll('.date-pill').forEach(b => b.classList.remove('active'));
    }

    function setQuickDateRange(mode) {
        clearDatePills();
        const btn = document.getElementById('dpill-' + mode);
        if (btn) btn.classList.add('active');

        const fromEl = document.getElementById('filter-date-from');
        const toEl = document.getElementById('filter-date-to');
        const now = new Date();
        const fmt = (d) => d.toISOString().slice(0, 10);

        if (mode === 'all') {
            fromEl.value = '';
            toEl.value = '';
        } else if (mode === 'today') {
            fromEl.value = fmt(now);
            toEl.value = fmt(now);
        } else if (mode === 'yesterday') {
            const y = new Date(now);
            y.setDate(y.getDate() - 1);
            fromEl.value = fmt(y);
            toEl.value = fmt(y);
        } else if (mode === 'week') {
            const w = new Date(now);
            w.setDate(w.getDate() - 7);
            fromEl.value = fmt(w);
            toEl.value = fmt(now);
        } else if (mode === 'month') {
            const m = new Date(now.getFullYear(), now.getMonth(), 1);
            fromEl.value = fmt(m);
            toEl.value = fmt(now);
        }
        renderAuditStream();
    }

    function resetAuditFilters() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-user').value = '';
        document.getElementById('filter-module').value = '';
        document.getElementById('filter-action').value = '';
        setQuickDateRange('all');
        quickFilterAction('');
    }

    function resetDocTrackerFilters() {
        document.getElementById('forensics-doc-query').value = '';
        document.getElementById('forensics-doc-type').value = '';
        document.getElementById('forensics-doc-user').value = '';
        renderForensicsCards();
    }

    function inspectAuditEntry(idx) {
        const log = CURRENT_FILTERED_LOGS[idx];
        if (!log) return;

        const modal = document.getElementById('audit-detail-modal');
        const body = document.getElementById('audit-detail-body');
        const dt = new Date(log.timestamp).toLocaleString('ar-EG');

        body.innerHTML = `
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                <div style="background:rgba(148,163,184,0.08); padding:10px 12px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">الموظف:</div>
                    <div style="font-size:0.95rem; font-weight:900; color:#38bdf8;">👤 ${log.userName}</div>
                </div>
                <div style="background:rgba(148,163,184,0.08); padding:10px 12px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">التاريخ والوقت:</div>
                    <div style="font-size:0.9rem; font-weight:800;">⏰ ${dt}</div>
                </div>
                <div style="background:rgba(148,163,184,0.08); padding:10px 12px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">القسم والعملية:</div>
                    <div style="font-size:0.9rem; font-weight:800; color:#10b981;">📂 ${log.module} — ${log.actionType}</div>
                </div>
                <div style="background:rgba(148,163,184,0.08); padding:10px 12px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">رقم المستند:</div>
                    <div style="font-size:0.95rem; font-weight:900; color:#f59e0b; font-family:monospace;">${log.docId || '—'}</div>
                </div>
            </div>

            ${(log.party || log.amount > 0) ? `
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                <div style="background:rgba(148,163,184,0.08); padding:10px 12px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">الطرف / الحساب:</div>
                    <div style="font-size:0.9rem; font-weight:900;">${log.party || '—'}</div>
                </div>
                <div style="background:rgba(148,163,184,0.08); padding:10px 12px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">المبلغ:</div>
                    <div style="font-size:0.95rem; font-weight:900; color:#10b981;">${log.amount > 0 ? log.amount.toLocaleString() + ' ' + log.currency : '—'}</div>
                </div>
            </div>` : ''}

            <div style="background:rgba(56,189,248,0.09); border:1px solid rgba(56,189,248,0.28); padding:12px 14px; border-radius:10px; margin-bottom:14px;">
                <div style="font-size:0.76rem; color:#38bdf8; font-weight:900; margin-bottom:4px;">البيان والتفاصيل:</div>
                <div style="font-size:0.92rem; font-weight:700; line-height:1.55;">${log.description}</div>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end;">
                ${log.url ? `<a href="${log.url}" class="audit-btn audit-btn-sky">🔗 فتح المستند / الشاشة</a>` : ''}
            </div>
        `;
        modal.style.display = 'flex';
    }

    function printAuditReport() {
        const stamp = document.getElementById('print-date-stamp');
        if (stamp) stamp.textContent = 'تاريخ الطباعة: ' + new Date().toLocaleString('ar-EG');
        window.print();
    }

    function exportAuditToExcel() {
        const rows = getFilteredStreamLogs();
        let csv = '\uFEFFالتاريخ والوقت,الموظف,العملية,القسم,رقم المستند,الطرف,المبلغ,البيان\n';
        rows.forEach(r => {
            const dt = new Date(r.timestamp).toLocaleString('ar-EG').replace(/,/g, ' ');
            const cleanDesc = String(r.description || '').replace(/"/g, '""').replace(/\n/g, ' ');
            const cleanParty = String(r.party || '').replace(/"/g, '""');
            csv += `"${dt}","${r.userName}","${r.actionType}","${r.module}","${r.docId}","${cleanParty}","${r.amount || 0}","${cleanDesc}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Audit_Log_${new Date().toISOString().slice(0, 10)}.csv`;
        link.click();
    }

    document.addEventListener('DOMContentLoaded', () => {
        syncAllAuditSources(false);

        const params = new URLSearchParams(window.location.search);
        if (params.get('tab') === 'forensics' || params.get('doc')) {
            switchAuditTab('forensics');
            if (params.get('doc')) {
                document.getElementById('forensics-doc-query').value = params.get('doc');
                renderForensicsCards();
            }
        }
    });

    window.addEventListener('ERP_AUDIT_LOGGED', () => syncAllAuditSources(false));
    window.addEventListener('AuditLogAdded', () => syncAllAuditSources(false));
</script>
</body>
</html>
